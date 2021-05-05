<?php
/*
12/19/2018 5:14 PM Andy
- Enhanced to can add new stock take by csv.

3/27/2020 9:39 AM William
- Enhanced to insert id manually for stock_take_pre table that use auto increment.
*/
ini_set("display_errors",0);
set_time_limit(0);
include("include/common.php");
ini_set('memory_limit', '1024M');

session_start();
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('STOCK_TAKE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_TAKE', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
$maintenance->check(1);

class STOCK_TAKE_CSV extends Module{
	var $sample_data = array(
		'header' => array("Item Code", "Stock Take Quantity"),
		'items' => array(
				array("280000010000", "15"),
				array("280000020000", "0"),
			)
		); 
	
	var $can_select_branch = false;
	var $branch_id = 0;
	var $branches_list = array();
	var $folder_name = 'stock_take_import';
	var $sid_list = array();
	
	function __construct($title, $template=''){
        global $config, $smarty,$con, $sessioninfo;
        
		$this->init_load();
		
        if(BRANCH_CODE=='HQ' && $config['single_server_mode']){
            $this->can_select_branch = true;
            $smarty->assign('can_select_branch', 1);
		}
		
		parent::__construct($title, $template);
	}
	
	function _default(){
		global $smarty;
		
		if($_REQUEST['show_result']){
			$this->show_result();
		}
        $this->display();
  	}
	
	private function init_load(){
		global $con, $smarty;
		
		$this->branches_list = array();
        //if($config['consignment_module']) $filter = "and code <> 'HQ'";
        $con->sql_query("select id,code from branch where active=1 order by sequence, code");
        while($r = $con->sql_fetchassoc()) {
            $this->branches_list[$r['id']] = $r;
        }
        $con->sql_freeresult();
		
		$smarty->assign('branches_list', $this->branches_list);
		
		if (!is_dir("attachments"))	check_and_create_dir("attachments");
		if (!is_dir("attachments/".$this->folder_name))	check_and_create_dir("attachments/".$this->folder_name."/");
		if (!is_dir("attachments/".$this->folder_name."/invalid"))	check_and_create_dir("attachments/".$this->folder_name."/invalid");
		
		$smarty->assign('sample_data', $this->sample_data);
		$smarty->assign('folder_name', $this->folder_name);
	}
	
	function download_sample(){
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_stock_take.csv");
		
		$f = fopen('php://output', 'w');
		fputcsv($f, $this->sample_data['header']);
		foreach($this->sample_data['items'] as $r) {
			fputcsv($f, $r);
		}
		fclose($f);
	}
	
	private function show_result(){
		global $con, $smarty, $sessioninfo, $config;
		
		$item_lists = $sid_list = array();
		
		$form = $_REQUEST;

		if($this->can_select_branch){
            $this->branch_id = isset($form['branch_id']) ? mi($form['branch_id']) : $sessioninfo['branch_id'];
		}else{
            $this->branch_id = $sessioninfo['branch_id'];
		}
		
		$this->sum_duplicate = mi($form['sum_duplicate']);
		$this->stock_date = trim($form['date']);
		$this->location = trim($form['location']);
		$this->shelf_no = trim($form['shelf_no']);
		
		$file = $_FILES['import_csv'];
		$f = fopen($file['tmp_name'], "rt");
		$header_line = fgetcsv($f);	// header
		
		// Column Not Match
		if(count($header_line) != count($this->sample_data['header'])) {
			$err[] = "Column not match. Please check your csv file.";
		}
		
		// Branch
		if($this->can_select_branch && !$form['branch_id']){
			$err[] = "Please Select Branch.";
		}
		
		// Date
		if(!$this->stock_date){
			$err[] = "Please Select Date.";
		}
		
		// location
		if(!$this->location){
			$err[] = "Please Select Location.";
		}
		
		// Shelf No
		if(!$this->shelf_no){
			$err[] = "Please Select Shelf No.";
		}
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$this->sid_list = array();
		$result = array();
		
		while($r = fgetcsv($f)){
			$raw = $info = $error = array();
			
			$is_item_row = $this->check_csv_row($r, $raw, $info, $error);
			
			if(!$is_item_row)	continue;	// skip empty row
			
			$result['ttl_row']++;
			
			if($error){
				// Got Error
				$info['error'] = join(', ', $error);
				$result['error_row']++;
			}else{
				// No Error
				$result['import_row']++;
				
				if($info['is_fresh_market']=='yes'){
					$result['fresh_market_sku']++;
				}
			}
			
			$data = array('raw'=>$raw, 'info'=>$info);
			$item_lists[] = $data;
		}
		
		//print_r($item_lists);
		
		$ret = array();
		if($item_lists){
			// Clone to backup
			$file_name = "stock_take_".time().".csv";
			copy($file['tmp_name'], "attachments/".$this->folder_name."/".$file_name);
			chmod("attachments/stock_take_import/".$file_name, 0777);
			
			// Create Invalid File
			if($result['error_row'] > 0)	$header_line[] = 'Error';
	
			$fp = fopen("attachments/".$this->folder_name."/invalid/".$file_name, 'w');
			fputcsv($fp, $header_line);
			foreach($item_lists as $r){
				if(isset($r['info']['error'])){
					$r['raw'][] = $r['info']['error'];	// clone error into last column
					fputcsv($fp, $r['raw']);
				}				
			}
			fclose($fp);
			
			chmod("attachments/".$this->folder_name."/invalid/".$file_name, 0777);
			
			$smarty->assign("result", $result);
			$smarty->assign("file_name", $file_name);
			$smarty->assign("item_lists", $item_lists);
		}else{
			$err[] = "No Data";
			$smarty->assign("err", $err);
		}
	}
	
	private function find_sku_items($code) {
        global $con;
				
        $con->sql_query("select si.id, si.active, si.is_parent, si.doc_allow_decimal, if(sku.is_fresh_market='inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market
			from sku_items si
			left join sku on si.sku_id=sku.id
			left join category_cache cc on cc.category_id=sku.category_id
			where (si.sku_item_code = ".ms($code)." or si.mcode = ".ms($code)." or si.artno = ".ms($code)." or si.link_code = ".ms($code).") 
			limit 1");
        if($con->sql_numrows() > 0){
            $si = $con->sql_fetchassoc();
        }
        $con->sql_freeresult();
        if($si)	return $si;
        
        if (strlen($code) == 13)      $code = substr($code, 0, 12);
        elseif (strlen($code) == 8)   $code = substr($code, 0, 7);
        else return false;
      
        return $this->find_sku_items($code);
    }
	
	private function check_csv_row($r, &$raw, &$info, &$error){
		global $con, $config;
		
		if(!$r)	return false;
		
		foreach($r as $tmp_row => $val){
			$r[$tmp_row] = utf8_encode(trim($val));
		}
		
		$raw = array();
		$info = array();
		$error = array();
		
		$raw['code'] = trim($r[0]);
		$raw['qty'] = trim($r[1]);
		$raw['qty'] = round($raw['qty'], $config['global_qty_decimal_points']);
		
		if(!$raw['code'] && !$raw['qty'])	return false;	// skip empty row
		
		// check item code
		$si = $this->find_sku_items($raw['code']);
							
		if(!$si){
			// Item Not Found
			$error[] = "Item Not Found.";
		}else{
			if(!$si['active']){
				$error[] = "sku is inactive.";
			}
			if($si['is_fresh_market']=='yes'){
				//$error[] = "Not allow fresh market sku.";
				if(!$si['is_parent']){
					$error[] = "Cannot Stock Take Fresh Market Child SKU";
				}
			}
			
			$info['sku_item_id'] = $si['id'];
			$info['is_fresh_market'] = $si['is_fresh_market'];
			
			if(!$this->sum_duplicate){
				if(!in_array($si['id'], $this->sid_list)){
					$this->sid_list[] = $si['id'];
					
					// Check system already have this sku stock take
					$con->sql_query("select id from stock_take_pre where branch_id=$this->branch_id and date=".ms($this->stock_date)." and location=".ms($this->location)." and shelf=".ms($this->shelf_no)." and sku_item_id=".mi($si['id'])." limit 1");
					$tmp = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($tmp){
						$error[] = "This item already have stock taked.";
					}
				}	
				else {
					// Item Exists more than one time
					$error[] = 'Duplicate Item Code';
				}
			}
			
			// Not Allow decimal qty
			if(!$si['doc_allow_decimal']){
				if(mi($raw['qty']) != $raw['qty']){
					$error[] = "SKU Not Allow Decimal Stock.";
				}
			}
		}
		
		// check stock take quantity
		if(is_numeric($raw['qty'])){
			if($raw['qty']<0){
				$error[] = "Stock Take Quantity cannot less than zero.";
			}
		}else{
			$error[] = "Stock Take Quantity must be number.";
		}
		
		return true;
	}
	
	function ajax_import_stock_take(){
		global $con, $smarty, $config, $sessioninfo, $appCore;
		
		$form = $_REQUEST;
		//print_r($form);exit;
		
		if($this->can_select_branch){
            $this->branch_id = isset($form['branch_id']) ? mi($form['branch_id']) : $sessioninfo['branch_id'];
		}else{
            $this->branch_id = $sessioninfo['branch_id'];
		}
		
		$this->sum_duplicate = mi($form['sum_duplicate']);
		$this->stock_date = trim($form['date']);
		$this->location = trim($form['location']);
		$this->shelf_no = trim($form['shelf_no']);
		$file_name = trim($form['file_name']);
		
		$file_path = "attachments/".$this->folder_name."/".$file_name;
		
		if(!file_exists($file_path)){
			die("Import File Not Found.");
		}
		
		if(date("Y", strtotime($this->stock_date))<2000){
			die("Invalid Import Date.");
		}
		
		if(!$this->location){
			die("Invalid Location.");
		}
		
		if(!$this->shelf_no){
			die("Invalid Shelf No.");
		}
		
		$f = fopen($file_path, "rt");
		$header_line = fgetcsv($f);	// header
		
		$this->sid_list = array();
		$result = array();
		
		$imported = false;
		$got_error = false;
		
		while($r = fgetcsv($f)){
			$raw = $info = $error = array();
			
			$is_item_row = $this->check_csv_row($r, $raw, $info, $error);
			
			if(!$is_item_row)	continue;	// skip empty row
			
			if($error){
				$got_error = true;
				continue;	// Skip error row
			}
						
			$upd = array();
			$upd['id'] = $appCore->generateNewID("stock_take_pre", "branch_id=".mi($this->branch_id));
			$upd['branch_id'] = $this->branch_id;
			$upd['date'] = $this->stock_date;
			$upd['location'] = $this->location;
			$upd['shelf'] = $this->shelf_no;
			$upd['qty'] =$raw['qty'];
			$upd['user_id'] = mi($sessioninfo['id']);
			$upd['sku_item_id'] = mi($info['sku_item_id']);
			$upd['is_fresh_market'] = $info['is_fresh_market']=='yes' ? 1 : 0;
			$upd['imported'] = 0;
			
			$con->sql_query("insert into stock_take_pre ".mysql_insert_by_field($upd));
			if($con->sql_affectedrows())	$imported = true;
		}
		
		$ret = array();
		
		if($imported){
			$ret['ok'] = 1;
			$str = "Branch: ".$this->branches_list[$this->branch_id]['code'].", Date: $this->stock_date, Location: $this->location, Shelf No: $this->shelf_no, File: $file_name";
			log_br($sessioninfo['id'], 'Stock Take', 0, "Import Stock Take Pre by CSV [$str]");
			
			if($got_error){
				$ret['partial_ok'] = 1;
			}
		}else{
			$ret['fail'] = 1;
		}
		
		print json_encode($ret);
	}
}

$STOCK_TAKE_CSV = new STOCK_TAKE_CSV('Create New Stock Take by CSV')
?>