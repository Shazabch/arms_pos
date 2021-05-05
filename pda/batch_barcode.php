<?php
/*
10/22/2020 5:50 PM William
- Enhanced to let batch barcode able to import batch by csv file.
*/
include("common.php");
include("class.scan_product.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/pda/index.php");
//if (!privilege('GRN')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRN', BRANCH_CODE), "/pda");

class Batch_Barcode_Module extends Scan_Product{
	var $headers = array(
		'1' => array("sku_item_code" => "Item Code", "qty" => "Qty",)
	);
	
	var $sample = array(
		'1' => array(
			'sample_1' => array("283398116110", "8"),
			'sample_2' => array("283398116111", "1")
		)
	);
	
    function __construct($title){
        global $sessioninfo, $smarty;
        
        $_SESSION['scan_product']['type'] = 'BATCH_BARCODE';
		$_SESSION['scan_product']['name'] = isset($_SESSION['batch_barcode']['id']) ? '#'.$_SESSION['batch_barcode']['id'] : '';
		
	    if(isset($_REQUEST['branch_id'])){
			if($_REQUEST['branch_id'] != $sessioninfo['branch_id']){    // prevent edit other branch
				header("Location: $_SERVER[PHP_SELF]");
				exit;
			}
		}
		
		$smarty->assign('is_grn_module', 1);
		parent::__construct($title);
	}
	
    function init_module(){
	    global $con, $smarty;
		// alter any default value, such as $this->scan_templates and $this->result_templates
		//$this->scan_templates = 'abc.tpl';
		//$this->result_templates = 'abc.tpl';

		$smarty->assign('PAGE_TITLE', $this->title);
		$smarty->assign('module_name', $this->title);
		$smarty->assign('top_include','batch_barcode.top_include.tpl');
		$smarty->assign('btm_include','batch_barcode.btm_include.tpl');
	}
	
	function default_(){
		global $con, $smarty;

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if($id && $branch_id){
			$this->reset_module_session($id,$branch_id);
		}else{
            $id = mi($_SESSION['batch_barcode']['id']);
            $branch_id = mi($_SESSION['batch_barcode']['branch_id']);
		}

		$this->show_setting();
	}
	
	function show_scan_product(){
		global $con, $smarty, $config;

		$id = mi($_SESSION['batch_barcode']['id']);
        $branch_id = mi($_SESSION['batch_barcode']['branch_id']);
        
		if($_SESSION['batch_barcode']['find_batch_barcode']) $form['search'] = $_SESSION['batch_barcode']['find_batch_barcode'];
		elseif($_REQUEST['find_batch_barcode']) $form['search'] = $_REQUEST['find_batch_barcode'];
		
		if($form['search']){
			$form['search_var'] = $form['search'];
			$smarty->assign("form", $form);
		}

		/*$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
		$report_prefix = $con->sql_fetchfield(0);
		$so_no = $report_prefix.sprintf('%05d',$id);*/

		$_SESSION['scan_product']['type'] = 'batch_barcode';
		$_SESSION['scan_product']['name'] = '#'.mi($id);

		$product_code = strtoupper($_REQUEST['product_code']);
		// cut last digit
		$product_code2 = strtoupper(substr($product_code,0,strlen($product_code)-1));
		
		$filter[] = "(si.mcode=".ms($product_code)." or si.mcode=".ms($product_code2).")";
		$filter[] = "(si.link_code=".ms($product_code)." or si.link_code=".ms($product_code2).")";
		$filter[] = "(si.sku_item_code=".ms($product_code)." or si.sku_item_code=".ms($product_code2).")";
		$filter[] = "(si.artno=".ms($product_code)." or si.artno=".ms($product_code2).")";
		$filter = join(' or ',$filter);

		$sql = $con->sql_query("select si.* from sku_items si where $filter");

		if($con->sql_numrows($sql) == 1 && $_REQUEST['auto_add_item']){
			$sku_info = $con->sql_fetchassoc($sql);
			$con->sql_freeresult($sql);
			$_REQUEST['item_check'][$sku_info['id']] = 1;
			$ret = $this->add_items();
			if($ret['error']) $this->err = array_merge($this->err, $ret['error']);
			$smarty->assign("auto_add", 1);
			$id = mi($_SESSION['batch_barcode']['id']);
			$branch_id = mi($_SESSION['batch_barcode']['branch_id']);
			$_SESSION['scan_product']['name'] = isset($_SESSION['batch_barcode']['id']) ? '#'.$_SESSION['batch_barcode']['id'] : '';
		}

		$q1 = $con->sql_query("select count(*) as total_item
							   from batch_barcode_items bbi 
							   where bbi.batch_barcode_id = ".mi($id)." and bbi.branch_id = ".mi($branch_id));
		
		$items = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$smarty->assign('items_details',$items);
		
		if($_SESSION['batch_barcode']['err']){
			$this->err = array_merge($this->err, $_SESSION['batch_barcode']['err']);
			unset($_SESSION['batch_barcode']['err']);
		}
		
		$smarty->assign('err',$this->err);
		$smarty->assign('is_item_check',true);
		
		$_REQUEST['grn_barcode_type'] = 1;

		$this->search_product();
	}

	function new_batch_barcode(){
		unset($_SESSION['batch_barcode']);
		header("Location: $_SERVER[PHP_SELF]");
	}
	
	function show_setting(){
		global $con, $smarty, $config;

		//$this->default_load();
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		//$find_grr = mi();
		
		if(!$id || !$branch_id){
            $id = mi($_SESSION['batch_barcode']['id']);
            $branch_id = mi($_SESSION['batch_barcode']['branch_id']);
		}

		if($id>0 && $branch_id>0){ // load from existing
			if($_SESSION['batch_barcode']['find_batch_barcode']) $search = $_SESSION['batch_barcode']['find_batch_barcode'];
			elseif($_REQUEST['find_batch_barcode']) $search = $_REQUEST['find_batch_barcode'];
		    //$this->reset_module_session($id, $branch_id);
			$q1 = $con->sql_query("select * from batch_barcode where id = ".$id." and branch_id = ".$branch_id);
			$form = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$form['search_var'] = $search;
			
			$q1 = $con->sql_query("select count(*) as total_item
								   from batch_barcode_items bbi 
								   where bbi.batch_barcode_id = ".mi($id)." and bbi.branch_id = ".mi($branch_id));
			
			$items = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$smarty->assign('items_details',$items);
		}
		
		$smarty->assign('is_item_check',true);
		$smarty->assign('form', $form);
		$smarty->assign('tab', 'scan_item');
		$smarty->display('scan_product.tpl');
	}

	private function reset_module_session($id, $branch_id){
	    global $con, $config;

	    $q1 = $con->sql_query("select id, branch_id from batch_barcode where id = ".mi($id)." and branch_id = ".mi($branch_id));
		$form = $con->sql_fetchassoc($q1);
		if($_REQUEST['find_batch_barcode']) $form['find_batch_barcode'] = $_REQUEST['find_batch_barcode'];
		$con->sql_freeresult($q1);
		if(!$form)	js_redirect('Invalid Batch Barcode', "index.php");

        $_SESSION['batch_barcode'] = $form;
	}
	
	function view_items(){
		global $con, $smarty;
		$id = mi($_SESSION['batch_barcode']['id']);
        $branch_id = mi($_SESSION['batch_barcode']['branch_id']);
		
        if(!$id || !$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}

		// load item list
        $q1 = $con->sql_query("select bbi.*, si.sku_item_code, si.description as sku_description
							 from batch_barcode_items bbi
							 left join sku_items si on si.id = bbi.sku_item_id
							 where bbi.batch_barcode_id = ".mi($id)." and bbi.branch_id = ".mi($branch_id)."
							 order by bbi.id");
		
		while($r = $con->sql_fetchassoc($q1)){
			$items[] = $r;
		}
		$con->sql_freeresult($q1);

        $smarty->assign('items',$items);
		$smarty->assign('tab', 'view_items');
		$smarty->display('batch_barcode.view_items.tpl');
	}
	
	function add_items(){
		global $con,$smarty,$config,$sessioninfo,$LANG;

		$ret = array();
	    $id = $_SESSION['batch_barcode']['id'];
        $branch_id = $_SESSION['batch_barcode']['branch_id'];
		
		//print_r($_REQUEST);
		$items = $_REQUEST['item_check'];
		if($items){
			if(!$id && !$branch_id){
				$ins = array();
				$ins['branch_id'] = $sessioninfo['branch_id'];
				$ins['user_id'] = $sessioninfo['id'];
				$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";

				$con->sql_query("insert into batch_barcode ".mysql_insert_by_field($ins));
				$_SESSION['batch_barcode']['id'] = $id = $con->sql_nextid();
				$_SESSION['batch_barcode']['branch_id'] = $branch_id = $sessioninfo['branch_id'];
			}
			foreach($items as $sid => $val){
				$q1 = $con->sql_query("select * from batch_barcode_items where sku_item_id = ".mi($sid)." and batch_barcode_id = ".mi($id)." and branch_id = ".mi($branch_id));
				$duplicate_row = $con->sql_numrows($q1);
				$con->sql_freeresult($q1);
				
				$qty = mi($_REQUEST['qty'][$sid]);
				if($qty <= 0 && !$_REQUEST['auto_add_item']){
					$con->sql_query("select * from sku_items where id = ".mi($sid));
					$si_info = $con->sql_fetchassoc();
					$con->sql_freeresult();
					$ret['error'][] = "SKU Item [".$si_info['sku_item_code']."] Invalid Qty.";
				}
					
				if($duplicate_row > 0 && !$_REQUEST['auto_add_item'] && !$_REQUEST['allow_duplicate']){
					$q1 = $con->sql_query("select * from sku_items where id = ".mi($sid));
					$si_info = $con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);
					$ret['error'][] = "SKU Item [".$si_info['sku_item_code']."] is duplicated.";
				}
				
				if(!$ret['error']){
					if($duplicate_row == 0){
						if($_REQUEST['auto_add_item']) $qty =1;
						$ins = array();
						$ins['batch_barcode_id'] = $id;
						$ins['branch_id'] = $branch_id;
						$ins['sku_item_id'] = $sid;
						$ins['qty'] = $qty;

						$con->sql_query("insert into batch_barcode_items ".mysql_insert_by_field($ins));
					}else{
						if($_REQUEST['allow_duplicate']){
							$con->sql_query("update batch_barcode_items set qty=qty+$qty where batch_barcode_id=$id and branch_id=$branch_id and sku_item_id=$sid");
						}elseif($_REQUEST['auto_add_item']){
							$con->sql_query("update batch_barcode_items set qty=qty+1 where batch_barcode_id=$id and branch_id=$branch_id and sku_item_id=$sid");
						}
					}
				}

				if($ret['error']) $ret['success'] = false;
				else $ret['success'] = true;
			}
		}else{
            $ret['error'][] = "No items found";
		}

		if($ret['error']){
			$q1 = $con->sql_query("select count(*) as total_item
								   from batch_barcode_items bbi 
								   where bbi.batch_barcode_id = ".mi($id)." and bbi.branch_id = ".mi($branch_id));
			
			$items_count = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$smarty->assign('items_details',$items_count);
			$smarty->assign('is_item_check',true);
		}
		
		return $ret;
	}
	
	function save_items(){
        global $con, $smarty;
		$id = $_SESSION['batch_barcode']['id'];
        $branch_id = $_SESSION['batch_barcode']['branch_id'];

        if(!$id || !$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}

        if($_REQUEST['uom']){ // from valid items
			foreach($_REQUEST['uom'] as $gi_id=>$uom){
				$ctn = $_REQUEST['ctn'][$gi_id];
				$pcs = $_REQUEST['pcs'][$gi_id];
			    /*if(!$_REQUEST['pcs'][$gi_id] && !$_REQUEST['pcs'][$gi_id]){  // remove item
					$con->sql_query("delete from sales_order_items where branch_id=$branch_id and sales_order_id=$id and id=".mi($gi_id));
				}else{*/
				$con->sql_query("update grn_items set uom_id = ".mi($uom).", ctn=".mf($ctn).", pcs=".mf($pcs)." where branch_id = ".mi($branch_id)." and grn_id = ".mi($id)." and id=".mi($gi_id));
				//}
			}
			
			update_total_selling($id, $branch_id); // update total selling
			update_total_amount($id, $branch_id); // update total amount
			update_total_variance($id, $branch_id); // update have variance
		}
		
		if($_REQUEST['isi_code']){ // from invalid items
			foreach($_REQUEST['isi_code'] as $row=>$code){
				$nsi['code'][] = $code;
				$nsi['description'][] = $_REQUEST['isi_desc'][$row];
				$nsi['qty'][] = $_REQUEST['isi_qty'][$row];
				$nsi['cost'][] = 0;
				$nsi['i_c'][] = 0;
			}

			if(count($nsi) > 0){
				$non_si = serialize($nsi);
				$con->sql_query("update grn set non_sku_items = ".ms($non_si)." where id = ".mi($id)." and branch_id = ".mi($branch_id));
			}
		}

		header("Location: $_SERVER[PHP_SELF]?a=view_items&find_batch_barcode=".$_REQUEST['find_batch_barcode']);
	}
	
	function delete_items(){
		global $con, $smarty;
		$id = $_SESSION['batch_barcode']['id'];
        $branch_id = $_SESSION['batch_barcode']['branch_id'];

        if(!$id || !$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}

		if($_REQUEST['item_chx']){
            $con->sql_query("delete from batch_barcode_items
			where batch_barcode_id = ".mi($id)." and branch_id = ".mi($branch_id)." and id in (".join(',',array_keys($_REQUEST['item_chx'])).")");
		}
		
		header("Location: $_SERVER[PHP_SELF]?a=view_items");
	}
	
	function open(){
		global $con, $smarty, $sessioninfo, $config;

		unset($_SESSION['batch_barcode']);
		$filter = array();
		
		if($_REQUEST['find_batch_barcode']){
			$str = $_REQUEST['find_batch_barcode'];
			$filter[] = "bb.id = ".mi($str);
		}

		$branch_id = $sessioninfo['branch_id'];
		
		if($sessioninfo['level']<9999) $filter[] = "bb.user_id = ".mi($sessioninfo['id']);
		else $filter[] = "bb.user_id = ".mi($sessioninfo['id']);
		
		$sql = "select bb.*
				from batch_barcode bb
				where bb.branch_id = ".mi($branch_id)." and bb.active=1 and ".join(" and ", $filter);

		$q1 = $con->sql_query($sql);
		if($con->sql_numrows($q1)<=0 && $str){
			$err[] = "No Batch Barcode found with $str.";
		}else{
			while($r = $con->sql_fetchassoc($q1)){
				$q2 = $con->sql_query("select count(*) as total_items from batch_barcode_items where batch_barcode_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
				$item_info = $con->sql_fetchassoc($q2);
				$con->sql_freeresult($q2);
			
				$r['total_items'] = $item_info['total_items'];
				$bb_list[] = $r;
			}

			$smarty->assign('bb_list',$bb_list);
		}
		$con->sql_freeresult($q1);

		$smarty->assign('err',$err);
		$smarty->display('batch_barcode.search.tpl');
	}
	
	function change_batch_barcode(){
	    global $con, $smarty;

        $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(!$id || !$branch_id){
           js_redirect('Invalid Batch Barcode', "index.php");
           exit;
		}else{
			$this->reset_module_session($id,$branch_id);
		}

		header("Location: $_SERVER[PHP_SELF]");
	}
	
	function delete_batch_barcode(){
		global $con, $smarty;

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		
		if(!$id || !$branch_id){
           js_redirect('Invalid Batch Barcode', "index.php");
           exit;
		}else{
			// set inactive
			$con->sql_query("update batch_barcode set active=0, last_update = CURRENT_TIMESTAMP where id = ".mi($id)." and branch_id = ".mi($branch_id));
			
			$this->open();
		}
	}
	
	function import_csv(){
		global $smarty;
		
		//create folder if folder no exist
		if (!is_dir("../attachments"))	check_and_create_dir("attachments");
		if (!is_dir("../attachments/import_batch_barcode"))  check_and_create_dir("../attachments/import_batch_barcode");
		
		$smarty->assign("form", $form);
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
		
		$smarty->display('batch_barcode.import_csv.tpl');
	}
	
	function show_result(){
		global $con, $smarty, $appCore;
		
	    $id= mi($_SESSION['batch_barcode']['id']);
        $branch_id = mi($_SESSION['batch_barcode']['branch_id']);
		
		$form = $_REQUEST;
		$file = $_FILES['import_csv'];	
		$f = fopen($file['tmp_name'], "rt");
		$line = fgetcsv($f);
		
		if(count($line) == count($this->headers[$form['method']])) {
			$item_list = array();
			while($r = fgetcsv($f)){
				$error = array();
				$result['ttl_row']++;
				$line_index++;
				
				foreach($r as $tmp_row => $val){
					$val = replace_ms_quotes(trim($val));
					
					// Remove Line Break
					if(!in_array($tmp_row, $column_skip_line_break)){
						$val = trim($appCore->removeLineBreak($val));
					}
					$r[$tmp_row] = $val;
				}
				
				$ins = array();
				switch($form['method']) {
					case '1':
						$ins['item_code'] = trim($r[0]);
						$ins['qty'] = mi($r[1]);
						break;
					default:
						break;
				}
				
				 // check code from db for duplication
                if($ins['item_code']) {
					$con->sql_query("select id from sku_items where active=1 and (sku_item_code = ".ms($ins['item_code'])." or mcode = ".ms($ins['item_code'])." or link_code = ".ms($ins['item_code'])." or artno = ".ms($ins['item_code']).")");
					$sku_item_info = $con->sql_fetchassoc();
					$count = $con->sql_numrows();
					$con->sql_freeresult();
					
					if($count <= 0 && strlen($ins['item_code']) == 13){
						$ins['item_code'] = substr($ins['item_code'], 0, 12);
						$con->sql_query("select id from sku_items where active=1 and (sku_item_code = ".ms($ins['item_code'])." or mcode = ".ms($ins['item_code'])." or link_code = ".ms($ins['item_code'])." or artno = ".ms($ins['item_code']).")");
						$sku_item_info = $con->sql_fetchassoc();
						$count = $con->sql_numrows();
						$con->sql_freeresult();
					}
					
					$sku_item_id = mi($sku_item_info['id']);
					if(!$sku_item_id) $error[] = 'Item Code('.$ins['item_code'].') not found.';
					if($count > 1)  $error[] = 'Item Code('.$ins['item_code'].') match SKU Item more than 1 result.';
					if(!$form['allow_duplicate']){
						if(!in_array($ins['item_code'], $item_list))	$item_list[] = $ins['item_code'];
						else	$error[] = "Item Code(".$ins['item_code'].") is duplicated.";
					}
					if($id && $branch_id && !$form['allow_duplicate']){
						$q1 = $con->sql_query("select * from batch_barcode_items where sku_item_id = ".mi($sku_item_id)." and batch_barcode_id = ".mi($id)." and branch_id = ".mi($branch_id));
						$duplicate_row = $con->sql_numrows($q1);
						$con->sql_freeresult($q1);
						
						if($duplicate_row > 0)  $error[] = "Item Code(".$ins['item_code'].") is duplicated.";
					}
				}else {
                    $error[] = "Empty Item Code.";
                }
				
				if($ins['qty'] <= 0){
					$error[] = "Invalid Qty.";
				}
				$error = array_unique($error);
				if($error)	$ins['error'] = join(', ', $error);
				
				$item_lists[] = $ins;
				
				if($ins['error'])	$result['error_row']++;
				else				$result['import_row']++;
			}
		}
		
		$ret = $err = array();
		if($item_lists){
			$header = $this->headers[$form['method']];
			if($result['error_row'] > 0)	$header[] = 'Error';
			
			$file_name = "batch_barcode_".time().".csv";
			
			$fp = fopen("../attachments/import_batch_barcode/".$file_name, 'w');

			fputcsv($fp, array_values($header));
			foreach($item_lists as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			
			chmod("../attachments/import_batch_barcode/".$file_name, 0777);
			
			$smarty->assign("result", $result);
			$smarty->assign("file_name", $file_name);
			$smarty->assign("item_header", array_values($header));
			$smarty->assign("item_lists", $item_lists);
		}else{
			if(!$err)	$err[] = "No Data List Found.";
			$smarty->assign("errm", $err);
		}
		
		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
		
		$smarty->display('batch_barcode.import_csv.tpl');
	}
	
	function import_batch(){
		global $con, $smarty, $sessioninfo;
		
		$id= mi($_SESSION['batch_barcode']['id']);
		$branch_id = mi($_SESSION['batch_barcode']['branch_id']);
		
		$form = $_REQUEST;
		if(!$form['file_name'] || !file_exists("../attachments/import_batch_barcode/".$form['file_name'])){
			print "<script>alert('File no found.');</script>";
			$smarty->assign("sample_headers", $this->headers);
			$smarty->assign("sample", $this->sample);
			
			$smarty->display('batch_barcode.import_csv.tpl');
		}
        
        $f = fopen("../attachments/import_batch_barcode/".$form['file_name'], "rt");
		$line = fgetcsv($f);
        
        if(in_array('Error', $line)) {
			$error_index = array_search("Error", $line);
		}else{
			$error_index = count($line);
		}
		
        $error_list = array();
		$num_row = 0;
		while($r = fgetcsv($f)){
			foreach($r as $tmp_row => $val){
				$r[$tmp_row] = utf8_encode(trim($val));
			}
            
            $ins = array();
            switch ($form['method']) {
				case '1':
					if(!$r[$error_index]) {
						if(!$id && !$branch_id){
							$ins =array();
							$ins['branch_id'] = $sessioninfo['branch_id'];
							$ins['user_id'] = $sessioninfo['id'];
							$ins['added'] = "CURRENT_TIMESTAMP";
							$ins['last_update'] = "CURRENT_TIMESTAMP";

							$con->sql_query("insert into batch_barcode ".mysql_insert_by_field($ins));
							$_SESSION['batch_barcode']['id'] = $id = $con->sql_nextid();
							$_SESSION['batch_barcode']['branch_id'] = $branch_id = $sessioninfo['branch_id'];
						}else{
							$con->sql_query("update batch_barcode set last_update='CURRENT_TIMESTAMP' where branch_id=$branch_id and id=$id");
						}
						
						$con->sql_query("select id from sku_items where active=1 and (sku_item_code = ".ms($r[0])." or mcode = ".ms($r[0])." or link_code = ".ms($r[0])." or artno = ".ms($r[0]).") limit 1");
						$sku_item_info = $con->sql_fetchassoc();
						$con->sql_freeresult();
						
						if($sku_item_info){
							$q1 = $con->sql_query("select * from batch_barcode_items where sku_item_id = ".mi($sku_item_info['id'])." and batch_barcode_id = ".mi($id)." and branch_id = ".mi($branch_id));
							$duplicate_row = $con->sql_numrows($q1);
							$con->sql_freeresult($q1);
							
							if($duplicate_row > 0){
								$con->sql_query("update batch_barcode_items set qty=qty+".mi($r[1])." where batch_barcode_id=$id and branch_id=$branch_id and sku_item_id=".mi($sku_item_info['id']));
								$num = $con->sql_affectedrows();
							}else{
								$ins = array();
								$ins['batch_barcode_id'] = $id;
								$ins['branch_id'] = $branch_id;
								$ins['sku_item_id'] = mi($sku_item_info['id']);
								$ins['qty'] = mi($r[1]);
								$con->sql_query("insert into batch_barcode_items ".mysql_insert_by_field($ins));
								$num = $con->sql_affectedrows();
							}
						}
						if ($num > 0)	$num_row++;
                    }else{
						$err = array();
						$err['sku_item_code'] = $r[0];
						$err['qty'] = $r[1];
						$err['error'] = $r[2];
						
						$error_list[] = $err;
                    }
                    break;
            }
        }
						
		if ($num_row > 0) {
			if($error_list){
				$header = $this->headers[$form['method']];
				$header[] = 'Error';
				$smarty->assign("item_header", array_values($header));
				$smarty->assign("partial_ok", 1);
				$smarty->assign("item_lists", $error_list);
				$smarty->assign("num_row", $num_row);
			}
			print "<script>alert('Import Success $num_row item(s).');</script>";
		}else{
			print "<script>alert('Import Failed.');</script>";
		}
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
		
		$smarty->display('batch_barcode.import_csv.tpl');
	}
	
    function download_sample_batch(){				
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_batch.csv");
		
		print join(",", array_values($this->headers[$_REQUEST['method']]));
		foreach($this->sample[$_REQUEST['method']] as $sample) {
			$data = array();
			foreach($sample as $d) {
				$data[] = $d;
			}
			print "\n\r".join(",", $data);
		}
	}
	
	function save(){
		global $con, $smarty;
		$id = $_SESSION['batch_barcode']['id'];
        $branch_id = $_SESSION['batch_barcode']['branch_id'];

        if(!$id || !$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
		
		if($_REQUEST['qty']){
			foreach($_REQUEST['qty'] as $batch_barcode_items_id=>$qty){
				$con->sql_query("update batch_barcode_items set qty=".mi($qty)." where batch_barcode_id = ".mi($id)." and branch_id = ".mi($branch_id)." and id=".mi($batch_barcode_items_id));
			}
			$con->sql_query("update batch_barcode set last_update = CURRENT_TIMESTAMP where id = ".mi($id)." and branch_id = ".mi($branch_id));
		}
		
		header("Location: $_SERVER[PHP_SELF]?a=view_items");
	}
}

$Batch_Barcode_Module = new Batch_Barcode_Module('Batch Barcode');
?>
