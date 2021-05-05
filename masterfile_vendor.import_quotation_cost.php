<?php
/*
12/7/2018 3:54 PM Justin
- Bug fixed on negative quotation cost will still allow user to import.

1/24/2019 9:10 AM Justin
- Bug fixed on system did not check against vendor_id while importing.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if($sessioninfo['level'] < 9999 && !privilege('MST_VENDOR_QUOTATION_COST')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VENDOR_QUOTATION_COST', BRANCH_CODE), "/index.php");
if($sessioninfo['level'] < 9999 && !privilege('MST_VENDOR_IMPORT_QUOTATION_COST')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VENDOR_IMPORT_QUOTATION_COST', BRANCH_CODE), "/index.php");

class IMPORT_QUOTATION_COST extends Module{
	var $headers = array(
		1 => array(
			"vendor_code" => "Vendor Code",
		    "si_code" => "SKU Code",
		    "cost" => "Quotation Cost"
		),
	); 
	
	var $sample = array(
		1 => array(
			'sample_1' => array("4080/P11", "955887311465", 15.50),
			'sample_2' => array("4181/A20", "955887311466", 12.10)
		),
	);
	
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
		if (!is_dir("attachments/import_vendor_quotation_cost"))	check_and_create_dir("attachments/import_vendor_quotation_cost");
		
		// pre-load
		$this->load_branch_list();
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
		$smarty->assign("upd_type_list", $this->upd_type_list);
	}
	
	function download_sample(){
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_vendor_quotation_cost.csv");
		
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
		global $con, $smarty, $LANG;
		
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
				$ins['vendor_code'] = trim($r[0]);
				$ins['si_code'] = trim($r[1]);
				$ins['cost'] = mf(trim($r[2]));
				if(!$ins['vendor_code'] || !$ins['si_code']) continue;
				
				// vendor validation
				$vendor_info = $this->is_vendor_exists($ins['vendor_code']);
				if($vendor_info['count'] == 0){
					$error[] = sprintf($LANG['IMPORT_VENDOR_QC_INVALID_VD'], $ins['vendor_code']);
				}
				
				if($ins['cost'] <= 0){
					$error[] = $LANG['IMPORT_VENDOR_QC_INVALID_COST'];
				}
				
				$result['ttl_row']++;
				$sku = $con->sql_query("select si.*
										from sku_items si
										where si.sku_item_code = ".ms($ins['si_code'])." or 
										si.mcode = ".ms($ins['si_code'])." or 
										si.artno = ".ms($ins['si_code'])." or 
										si.link_code = ".ms($ins['si_code']));
				
				// item not found
				if($con->sql_numrows($sku) == 0) $error[] = $ins['si_code']." is an invalid SKU item";
				else{ // have to check if can update the cost for at least one SKU Item
					$can_update = false;
					while($si = $con->sql_fetchassoc($sku)){
						$sid = $si['id'];
						foreach($this->branch_list as $bid=>$bcode){
							if(!$form['branch_list'][$bid]) continue;
							
							// check if the price is different with database
							$sivqc = $con->sql_query("select * from sku_items_vendor_quotation_cost where branch_id = ".mi($bid)." and sku_item_id = ".mi($sid)." and cost = ".mf($ins['cost'])." and vendor_id = ".mi($vendor_info['info']['id']));
							if($con->sql_numrows($sivqc) == 0){
								$can_update = true;
							}
							$con->sql_freeresult($sivqc);
						}
					}
				}
				$con->sql_freeresult($sku);
				
				if(!$can_update) $error[] = sprintf($LANG['IMPORT_VENDOR_QC_NO_UPDATE'], $ins['si_code']);
				
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
				
				$file_name = "import_vendor_quotation_cost_".time().".csv";
				
				$fp = fopen("attachments/import_vendor_quotation_cost/".$file_name, 'w');
				fputcsv($fp, array_values($header));
				foreach($item_lists as $r){
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/import_vendor_quotation_cost/".$file_name, 0777);
				
				$smarty->assign("result", $result);
				$smarty->assign("file_name", $file_name);
				$smarty->assign("item_header", array_values($header));
				$smarty->assign("item_lists", $item_lists);
				
				// generate error list into CSV
				if($error_list) {
					$fp = fopen("attachments/import_vendor_quotation_cost/invalid_".$file_name, 'w');
					$line[] = "Error";
					fputcsv($fp, array_values($line));
					
					foreach($error_list as $r){
						if($r['error']){
							$r['error'] = str_replace("<br />", "\r\n", $r['error']);
						}
						fputcsv($fp, $r);
					}
					fclose($fp);
					
					chmod("attachments/import_vendor_quotation_cost/invalid_".$file_name, 0777);
				}
			}else{
				$err[] = $LANG['IMPORT_VENDOR_QC_NO_DATA'];
				$smarty->assign("errm", $err);
			}
		}else {
			$smarty->assign("errm", "Column not match. Please re-check the file format.");
		}
		
		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		$this->display();
	}
	
	function ajax_update_quotation_cost(){
		global $con, $sessioninfo;
		
		$form = $_REQUEST;
		
		if(!$form['file_name'] || !file_exists("attachments/import_vendor_quotation_cost/".$form['file_name'])){
			die("File no found.");
			exit;
		}
		
		if(!$form['branch_list']){
			die("You must choose at least one branch to update");
			exit;
		}
		
		$this->load_branch_list();
		
		$f = fopen("attachments/import_vendor_quotation_cost/".$form['file_name'], "rt");
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
				$vendor_code = trim($r[0]);
				$si_code = trim($r[1]);
				$cost = mf(trim($r[2]));
				
				// load vendor ID
				$vendor_info = array();
				$vendor_info = $this->is_vendor_exists($vendor_code);
				
				$sku = $con->sql_query("select si.*
										from sku_items si
										where si.sku_item_code = ".ms($si_code)." or 
										si.mcode = ".ms($si_code)." or 
										si.artno = ".ms($si_code)." or 
										si.link_code = ".ms($si_code));
				
				while($si = $con->sql_fetchassoc($sku)){
					$sid = mi($si['id']);
					
					foreach($this->branch_list as $bid=>$bcode){
						if(!$form['branch_list'][$bid]) continue;
						
						// check if the price is different with database
						$sivqc = $con->sql_query("select * from sku_items_vendor_quotation_cost where branch_id = ".mi($bid)." and sku_item_id = ".mi($sid)." and cost = ".mf($cost)." and vendor_id = ".mi($vendor_info['info']['id']));

						// update into quotation cost only if cost not same
						if($con->sql_numrows($sivqc) == 0){
							$ins = array();
							$ins['branch_id'] = $bid;
							$ins['sku_item_id'] = $sid;
							$ins['vendor_id'] = $vendor_info['info']['id'];
							$ins['cost'] = $cost;
							$ins['user_id'] = $sessioninfo['id'];
							$ins['last_update'] = "CURRENT_TIMESTAMP";
							$q1 = $con->sql_query("replace into sku_items_vendor_quotation_cost ".mysql_insert_by_field($ins));
							
							if($con->sql_affectedrows($q1) > 0){
								unset($ins['last_update']);
								$ins['added'] = "CURRENT_TIMESTAMP";
								
								$con->sql_query("insert into sku_items_vendor_quotation_cost_history ".mysql_insert_by_field($ins));
								$is_updated = true;
							}
							unset($ins);
						}
						$con->sql_freeresult($sivqc);
					}
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
		log_br($sessioninfo['id'], "IMPORT_VENDOR_QC", 0, "Import Vendor Quotation Cost Successfully, Files Reference: ".$form['file_name'].$xtra_info);
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
	
	function is_vendor_exists($code){
		global $con;
		
		if(!$code) return false;
		
		// check user existance
		$q1 = $con->sql_query("select v.* 
							   from vendor v
							   where v.code = ".ms($code));
		
		$ret = array();
		$ret['info'] = $con->sql_fetchassoc($q1);
		$ret['count'] = $con->sql_numrows($q1);
		$con->sql_freeresult($q1);
		
		if($ret['count'] > 0) return $ret;
		else return false;
	}
}

$IMPORT_QUOTATION_COST = new IMPORT_QUOTATION_COST("Import Quotation Cost");
?>