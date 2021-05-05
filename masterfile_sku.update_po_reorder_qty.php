<?php
/*
5/31/2019 4:58 PM William
- Added new moq by csv.
- Enhanced to disable Moq value larger than Max value.
- Enhanced to disable Min value equal Max value.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if($sessioninfo['level'] < 9999 && !privilege('MST_SKU_UPDATE')){
	js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE', BRANCH_CODE), "/index.php");
}

class UPDATE_SKU_PO_REORDER_QTY extends Module{
	var $headers = array(
		1 => array("si_code" => "Code",
						 "min_qty" => "Min Qty",
						 "max_qty" => "Max Qty",
						 "moq_qty" => "MOQ Qty",
						 "notify_person" => "Notify Person")
	); 
	
	var $sample = array(
		1 => array(
			'sample_1' => array("955887311465", 10, 20, 15,"kctan"),
			'sample_2' => array("955887311466", 10, 30, 20,"ali")
		),
	);
	
	var $upd_type_list = array("sku"=>"SKU", "sku_item"=>"SKU Item", "branch"=>"Branch");
	
	function __construct($title){
		$this->init();
 		parent::__construct($title);
	}

	function _default(){	
		$this->display();
	}
	
	function init() {
		global $smarty;
		if (!is_dir("attachments"))	check_and_create_dir("attachments");
		if (!is_dir("attachments/update_sku_po_reorder_qty"))	check_and_create_dir("attachments/update_sku_po_reorder_qty");
		
		// pre-load
		$this->load_branch_list();
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
		$smarty->assign("upd_type_list", $this->upd_type_list);
	}
	
	function download_sample(){
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_update_po_reorder_qty.csv");
		
		print join(", ", array_values($this->headers[$_REQUEST['method']])) . "\n\r";
		foreach($this->sample[$_REQUEST['method']] as $sample) {
			$data = array();
			foreach($sample as $d) {
				$data[] = $d;
			}
			print join(",", $data) . "\n\r";
		}
	}
	
	function show_result(){
		global $con, $smarty;
		
		$item_lists = $error_list = array();
		
		$form = $_REQUEST;
		$file = $_FILES['update_csv'];
		$f = fopen($file['tmp_name'], "rt");
		$line = fgetcsv($f);
		
		$this->load_branch_list();
		
		if(count($line) == count($this->headers[$form['method']])) {
			while($r = fgetcsv($f)){
				$error = array();
				
				foreach($r as $tmp_row => $val){
					$r[$tmp_row] = utf8_encode(trim($val));
				}
				
				$ins = array();
				$ins['si_code'] = trim($r[0]);
				$ins['min_qty'] = mi(trim($r[1]));
				$ins['max_qty'] = mi(trim($r[2]));
				$ins['moq_qty'] = mi(trim($r[3]));
				$ins['notify_person'] = strtolower(trim($r[4]));
				if(!$ins['si_code']) continue;
				
				// min & max qty validation
				if(!$ins['min_qty'] && !$ins['max_qty'] && $ins['moq_qty']){
					$error[] = "reorder qty max and min must fill if exist MOQ";
				}elseif($ins['min_qty'] && $ins['max_qty'] || $ins['moq_qty']){
					if($ins['min_qty'] >= $ins['max_qty']) $error[] = "Min Qty cannot higher than Max Qty or equal Max Qty";
					if($ins['moq_qty'] > $ins['max_qty']) $error[] = "MOQ Qty cannot higher than Max Qty";
					elseif(!$ins['min_qty'] && !$ins['max_qty']) $error[] = "Cannot left empty for both Min & Max Qty";
				}
				if($ins['min_qty'] || $ins['max_qty']){
					if(count($error)> 0 && in_array("Min Qty cannot higher than Max Qty or equal Max Qty",$error)){

					}else{
						if($ins['min_qty'] >= $ins['max_qty']) $error[] = "Min Qty cannot higher than Max Qty or equal Max Qty";
					}
				}
				
				// notify person validation
				if($ins['notify_person']){
					$user_info = $this->is_user_exists($ins['notify_person']);
					
					// found matches multiple users
					if($user_info['count'] > 1){
						$error[] = "Notify Person ".$ins['notify_person']." matches more than one users";
					}elseif($user_info['count'] == 0){
						$error[] = "Notify Person ".$ins['notify_person']." does not exists.";
					}elseif(!$user_info['info']['have_privilege']){
						$error[] = "Notify Person ".$ins['notify_person']." does not have privilege 'NT_STOCK_REORDER'";
					}
				}
				
				$result['ttl_row']++;
				$sku = $con->sql_query("select si.id, si.sku_item_code, si.selling_price, si.cost_price, sku.default_trade_discount_code, 
										sku.sku_type, trade_discount_type, c.department_id, sku.brand_id, sku.vendor_id, sku.po_reorder_by_child
										from sku_items si
										left join sku on sku.id=si.sku_id
										left join category c on c.id = sku.category_id
										where si.sku_item_code = ".ms($ins['si_code'])." or 
										si.mcode = ".ms($ins['si_code'])." or 
										si.artno = ".ms($ins['si_code'])." or 
										si.link_code = ".ms($ins['si_code']));
								
				if($con->sql_numrows($sku) == 0) $error[] = $ins['si_code']." is an invalid SKU item";
				elseif($form['update_type'] != "sku_item"){
					while($si = $con->sql_fetchassoc($sku)){
						if($si['po_reorder_by_child']) $error[] = $si['sku_item_code']." is currently using PO Reorder Qty by Child";
					}
				}
				$con->sql_freeresult($sku);
				
				if($error)	$ins['error'] = join('<br />', $error);
				
				$item_lists[] = $ins;
				unset($si, $err_bcode_list);
				
				if($ins['error']){
					$error_list[] = $ins;
					$result['error_row']++;
				}else $result['updated_row']++;
			}
			
			$ret = array();
			if($item_lists){
				$header = $this->headers[$form['method']];
				if($result['error_row'] > 0)	$header[] = 'Error';
				
				$file_name = "po_reorder_qty_".time().".csv";
				
				$fp = fopen("attachments/update_sku_po_reorder_qty/".$file_name, 'w');
				fputcsv($fp, array_values($header));
				foreach($item_lists as $r){
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/update_sku_po_reorder_qty/".$file_name, 0777);
				
				$smarty->assign("result", $result);
				$smarty->assign("file_name", $file_name);
				$smarty->assign("item_header", array_values($header));
				$smarty->assign("item_lists", $item_lists);
				
				// generate error list into CSV
				if($error_list) {
					$fp = fopen("attachments/update_sku_po_reorder_qty/invalid_".$file_name, 'w');
					$line[] = "Error";
					fputcsv($fp, array_values($line));
					
					foreach($error_list as $r){
						if($r['error']){
							$r['error'] = str_replace("<br />", "\r\n", $r['error']);
						}
						fputcsv($fp, $r);
					}
					fclose($fp);
					
					chmod("attachments/update_sku_po_reorder_qty/invalid_".$file_name, 0777);
				}
			}else{
				$err[] = $LANG['UPDATE_SKU_BRAND_VENDOR_NO_DATA'];
				$smarty->assign("errm", $err);
			}
		}else {
			$smarty->assign("errm", "Column not match. Please re-check the file format.");
		}
		
		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		$this->display();
	}
	
	function ajax_update_sku(){
		global $con, $sessioninfo;
		
		$form = $_REQUEST;
		
		if(!$form['file_name'] || !file_exists("attachments/update_sku_po_reorder_qty/".$form['file_name'])){
			die("File no found.");
			exit;
		}
		
		if($form['update_type'] == "branch" && !$form['branch_list']){
			die("You must choose at least one branch to update");
			exit;
		}
		
		$this->load_branch_list();
		
		$f = fopen("attachments/update_sku_po_reorder_qty/".$form['file_name'], "rt");
		$line = fgetcsv($f);
		
		if(in_array('Error', $line)) {
			$error_index = array_search("Error", $line);
		}else{
			$error_index = count($line);
		}
		
		$ret = $error_list = array();
		$num_row = 0;
		while($r = fgetcsv($f)){
			$is_updated = false;
			foreach($r as $tmp_row => $val){
				$r[$tmp_row] = utf8_encode(trim($val));
			}
			
			if(!$r[$error_index]){
				$sku_list = array();
				$si_code = trim($r[0]);
				$min_qty = mi(trim($r[1]));
				$max_qty = mi(trim($r[2]));
				$moq_qty = mi(trim($r[3]));
				$notify_person = strtolower(trim($r[4]));
				
				// load user ID
				$notify_person_info = array();
				if($notify_person) $notify_person_info = $this->is_user_exists($notify_person);
				
				$sku = $con->sql_query("select si.id, si.sku_item_code, si.selling_price, si.cost_price, sku.default_trade_discount_code, 
										sku.sku_type, trade_discount_type, c.department_id, sku.brand_id, sku.vendor_id, sku.id as sku_id, sku.po_reorder_qty_by_branch
										from sku_items si
										left join sku on sku.id=si.sku_id
										left join category c on c.id = sku.category_id
										where si.sku_item_code = ".ms($si_code)." or 
										si.mcode = ".ms($si_code)." or 
										si.artno = ".ms($si_code)." or 
										si.link_code = ".ms($si_code));
				
				while($si = $con->sql_fetchassoc($sku)){
					$sid = mi($si['id']);
					$sku_id = mi($si['sku_id']);
					$upd = $po_reorder_qty_by_branch = array();
					$org_po_reorder_qty_by_branch = unserialize($si['po_reorder_qty_by_branch']);
					
					$tbl = "sku";
					if($form['update_type'] == "branch"){ // update into PO Reorder qty by branch
						foreach($this->branch_list as $bid=>$bcode){
							if($form['branch_list'][$bid]){
								$po_reorder_qty_by_branch['min'][$bid] = $min_qty;
								$po_reorder_qty_by_branch['max'][$bid] = $max_qty;
								$po_reorder_qty_by_branch['moq'][$bid] = $moq_qty;
								
								if($notify_person_info['info']['id'] > 0) $po_reorder_qty_by_branch['notify_user_id'][$bid] = $notify_person_info['info']['id'];
							}elseif($org_po_reorder_qty_by_branch['min'][$bid] || $org_po_reorder_qty_by_branch['max'][$bid] || $org_po_reorder_qty_by_branch['notify_user_id'][$bid]){
								$po_reorder_qty_by_branch['min'][$bid] = $org_po_reorder_qty_by_branch['min'][$bid];
								$po_reorder_qty_by_branch['max'][$bid] = $org_po_reorder_qty_by_branch['max'][$bid];
								$po_reorder_qty_by_branch['moq'][$bid] = $org_po_reorder_qty_by_branch['moq'][$bid];
								$po_reorder_qty_by_branch['notify_user_id'][$bid] = $org_po_reorder_qty_by_branch['notify_user_id'][$bid];
							}
						}
						
						//$upd['po_reorder_by_child'] = 0;
						$upd['po_reorder_qty_by_branch'] = serialize($po_reorder_qty_by_branch);
						
						$sid = $sku_id;
					}else{ // update into SKU or SKU Item
						$upd = array();
						$upd['po_reorder_qty_min'] = $min_qty;
						$upd['po_reorder_qty_max'] = $max_qty;
						$upd['po_reorder_moq'] = $moq_qty;
						if($notify_person_info['info']['id'] > 0) $upd['po_reorder_notify_user_id'] = $notify_person_info['info']['id'];
						
						if($form['update_type'] == "sku"){
							$upd['timestamp'] = "CURRENT_TIMESTAMP";
							$sid = $sku_id;
						}else{
							$tbl = "sku_items";
							$upd['lastupdate'] = "CURRENT_TIMESTAMP";
							$sid = $sid;
							
							// need to update sku to use reorder by child
							$con->sql_query("update sku set po_reorder_by_child = 1 where id = ".mi($sku_id));
						}
					}
					
					$q1 = $con->sql_query("update $tbl set ".mysql_update_by_field($upd)." where id = ".mi($sid));
					$num = $con->sql_affectedrows($q1);
					if ($num > 0) $is_updated = true;
					unset($upd);
				}
				$con->sql_freeresult($q1);
				
				if($is_updated) $num_row++;
			}else{
				$error_list[] = $r;
			}
		}
						
		if ($num_row > 0) {
			if($error_list)	$ret['partial_ok'] = 1;
			else	$ret['ok'] = 1;
		}else $ret['fail'] = 1;
		unset($error_list);

		print json_encode($ret);
		log_br($sessioninfo['id'], "UPDATE_SKU", 0, "Update SKU Stock Reorder Min & Max Qty Successfully, Files Reference: ".$form['file_name'].$xtra_info);
	}
	
	function load_price_type_list(){
		global $con;
		
		$this->price_type_list = array();
		$q1 = $con->sql_query("select code from trade_discount_type");
		while($r = $con->sql_fetchassoc($q1)){
			$this->price_type_list[$r['code']] = 1;
		}
		$con->sql_freeresult($q1);
	}
	
	function load_branch_list(){
		global $con, $smarty;

		$this->branch_list = array();
		$q1 = $con->sql_query("select id, code from branch where active=1 order by sequence, code");
		while($r = $con->sql_fetchassoc($q1)){
			$this->branch_list[$r['id']] = $r['code'];
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("branch_list", $this->branch_list);
	}
	
	function is_user_exists($username){
		global $con;
		
		if(!$username) return false;
		
		// check user existance
		$q1 = $con->sql_query("select u.* 
							   from user u
							   where u.active = 1 and u.is_arms_user=0 and
							   (u.u = ".ms($username)." or u.fullname = ".ms($username).")");
		
		$ret = array();
		$ret['info'] = $con->sql_fetchassoc($q1);
		$ret['count'] = $con->sql_numrows($q1);
		$con->sql_freeresult($q1);
		
		// check privilege if matches only one user
		if($ret['info']['id'] > 0 && $ret['count'] == 1){
			$q1 = $con->sql_query("select * from user_privilege where privilege_code = 'NT_STOCK_REORDER' and user_id = ".mi($ret['info']['id']));
			$ret['info']['have_privilege'] = $con->sql_numrows($q1);
			$con->sql_freeresult($q1);
		}
		
		if($ret['count'] > 0) return $ret;
		else return false;
	}
}

$UPDATE_SKU_PO_REORDER_QTY = new UPDATE_SKU_PO_REORDER_QTY("Update SKU Stock Reorder Min & Max Qty by CSV");
?>