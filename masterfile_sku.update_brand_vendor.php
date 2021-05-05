<?php
/*
11/2/2018 9:21 AM Justin
- Bug fixed on mysql filtering issue.

11/5/2018 11:37 AM Justin
- Bug fixed on Brand ID checking issue.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if($sessioninfo['level'] < 9999 && !privilege('MST_SKU_UPDATE')){
	js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE', BRANCH_CODE), "/index.php");
}

class UPDATE_SKU extends Module{
	var $headers = array(
		'brand' => array("si_code" => "Code",
						 "brand_code" => "Brand Code",
						 "price_type" => "Price Type"),
		'vendor' => array("si_code" => "Code",
						  "vendor_code" => "Vendor Code",
						  "price_type" => "Price Type")
	); 
	
	var $sample = array(
		'brand' => array(
			'sample_1' => array("955887311464", "ANAKKU", "B1")
		),
		'vendor' => array(
			'sample_1' =>array("285095570000 ", "4080/P11", "B2")
		)
	);
	
	var $method_list = array("brand"=>1, "vendor"=>1);
	
	function __construct($title){
		$this->init();
		
		if(!$_REQUEST['method'] || !$this->method_list[$_REQUEST['method']]) $_REQUEST['method'] = "brand";
		$title = "Update SKU by ".ucwords($_REQUEST['method']);
		
 		parent::__construct($title);
	}

	function _default(){	
		$this->display();
	}
	
	function init() {
		global $smarty;
		if (!is_dir("attachments"))	check_and_create_dir("attachments");
		if (!is_dir("attachments/update_sku_brand"))	check_and_create_dir("attachments/update_sku_brand");
		if (!is_dir("attachments/update_sku_vendor"))	check_and_create_dir("attachments/update_sku_vendor");
		
		// default as "Brand" if user removed the method or provided the wrong method
		if(!$_REQUEST['method'] || !$this->method_list[$_REQUEST['method']]) $_REQUEST['method'] = "brand";
		
		$smarty->assign("method", $_REQUEST['method']);
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
	}
	
	function download_sample(){
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_update_".$_REQUEST['method'].".csv");
		
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
		
		$this->load_price_type_list();
		$this->load_branch_list();
		
		if($this->method_list[$form['method']] && count($line) == count($this->headers[$form['method']])) {
			while($r = fgetcsv($f)){
				$error = array();
				
				foreach($r as $tmp_row => $val){
					$r[$tmp_row] = utf8_encode(trim($val));
				}
				
				$ins = array();
				$ins['si_code'] = $si_code = trim($r[0]);
				$price_type = strtoupper(trim($r[2]));
				if(!$si_code) continue;
				
				// check the existance of brand or vendor
				switch($form['method']) {
					case 'brand':
						$ins['brand_code'] = strtoupper(trim($r[1]));
						if($ins['brand_code']){
							$brand_info = $this->is_brand_exists($ins['brand_code']);
						}
						
						if(!$ins['brand_code'] && !$price_type) $error[] = "Nothing to update";
						elseif($ins['brand_code'] && !$brand_info['info']['id']) $error[] = "Brand $ins[brand_code] is not existed";
						elseif($brand_info['count'] > 1) $error[] = "Found multiple matches for Brand $ins[brand_code]";
						
						break;
					case 'vendor':
						$ins['vendor_code'] = strtoupper(trim($r[1]));
						if($ins['vendor_code']){
							$vendor_id = $this->is_vendor_exists($ins['vendor_code']);
						}
						
						if(!$ins['vendor_code'] && !$price_type) $error[] = "Nothing to update";
						elseif($ins['vendor_code'] && !$vendor_id) $error[] = "Vendor $ins[vendor_code] is not existed";
						
						break;
				}
				
				$result['ttl_row']++;
				$sku = $con->sql_query("select si.id, si.sku_item_code, si.selling_price, si.cost_price, sku.default_trade_discount_code, 
									 sku.sku_type, trade_discount_type, c.department_id, sku.brand_id, sku.vendor_id
									 from sku_items si
									 left join sku on sku.id=si.sku_id
									 left join category c on c.id = sku.category_id
									 where si.sku_item_code = ".ms($si_code)." or 
									 si.mcode = ".ms($si_code)." or 
									 si.artno = ".ms($si_code)." or 
									 si.link_code = ".ms($si_code));
								
				if($con->sql_numrows($sku) > 0){
					$ins['price_type'] = $price_type; // putting at here is to align the sequence
					
					while($si = $con->sql_fetchassoc($sku)){
						$sid = mi($si['id']);

						// check if user did provide price type but sku is not consignment
						if($si['sku_type'] != "CONSIGN" && $price_type){
							$error[] = "$si[sku_item_code] is not a Consignment Item";
						}elseif($si['sku_type'] == "CONSIGN" && $price_type){
							// couldn't find price type from database
							if(!isset($this->price_type_list[$price_type])) $error[] = "$si[sku_item_code] is having invalid price type";
							
							// it is an incomplete CONSIGNMENT item, since no Vendor or Brand trade discount were selected
							if($si['trade_discount_type'] == 0 || $si['trade_discount_type'] > 2) $error[] = "$si[sku_item_code] are not using Vendor or Brand Trade Discount table";
						}
						
						
						if($si['sku_type'] == "CONSIGN"){
							$err_bcode_list = array();
							$is_wrong_tdt = false;
							
							switch($form['method']) {
								case 'brand':
									// use latest brand ID for vendor commission checking purpose
									if($brand_info['info']['id']) $si['brand_id'] = $brand_info['info']['id'];
									
									// found user trying to update price type for brand into vendor
									if($price_type && $si['trade_discount_type'] == 2){
										$is_wrong_tdt = true;
										$error[] = "$si[sku_item_code] is currently not using Brand Trade Discount table";
									}
									
									break;
								case 'vendor':							
									// use latest vendor ID for vendor commission checking purpose
									if($vendor_id) $si['vendor_id'] = $vendor_id;
									
									// found user trying to update price type for vendor into brand
									if($price_type && $si['trade_discount_type'] == 1){
										$is_wrong_tdt = true;
										$error[] = "$si[sku_item_code] is currently not using Vendor Trade Discount table";
									}
									
									break;
							}
							
							if($price_type && !$is_wrong_tdt){ // will not check trade discount rate as if user trying to update from wrong module
								foreach($this->branch_list as $bid=>$bcode){
									// check if either it is valid in vendor or brand commission or not (brand = 1, vendor = 2)
									if($si['trade_discount_type'] == 1){
										$q1 = $con->sql_query("select rate from brand_commission where department_id=".mi($si['department_id'])." and branch_id = ".mi($bid)." and brand_id = ".mi($si['brand_id'])." and skutype_code = ".ms($ins['price_type'])) or die(mysql_error());
									}elseif($si['trade_discount_type'] == 2){
										$q1 = $con->sql_query("select rate from vendor_commission where department_id=".mi($si['department_id'])." and branch_id = ".mi($bid)." and vendor_id = ".mi($si['vendor_id'])." and skutype_code = ".ms($ins['price_type'])) or die(mysql_error());
									}
									$rate_info = $con->sql_fetchassoc($q1);
									$con->sql_freeresult($q1);

									if($rate_info['rate'] == 0) $err_bcode_list[$bid] = $bcode;
									unset($rate_info);
								}
								
								if(count($err_bcode_list) > 0){
									$error[] = "$si[sku_item_code] - Price Type [$ins[price_type]] for branch ".join(", ", $err_bcode_list)." is 0 rate";
								}
							}
						}
					}
				}else{
					switch($form['method']) {
						case 'brand':
							$ins['brand_code'] = strtoupper(trim($r[1]));
							break;
						case 'vendor':
							$ins['vendor_code'] = strtoupper(trim($r[1]));
							break;
					}
					
					$ins['price_type'] = $price_type;
					$error[] = "$si_code is an invalid SKU item";
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
				
				$file_name = $form['method']."_".time().".csv";
				
				$fp = fopen("attachments/update_sku_".$form['method']."/".$file_name, 'w');
				fputcsv($fp, array_values($header));
				foreach($item_lists as $r){
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/update_sku_".$form['method']."/".$file_name, 0777);
				
				$smarty->assign("result", $result);
				$smarty->assign("file_name", $file_name);
				$smarty->assign("item_header", array_values($header));
				$smarty->assign("item_lists", $item_lists);
				
				// generate error list into CSV
				if($error_list) {
					$fp = fopen("attachments/update_sku_".$form['method']."/invalid_".$file_name, 'w');
					$line[] = "Error";
					fputcsv($fp, array_values($line));
					
					foreach($error_list as $r){
						if($r['error']){
							$r['error'] = str_replace("<br />", "\r\n", $r['error']);
						}
						fputcsv($fp, $r);
					}
					fclose($fp);
					
					chmod("attachments/update_sku_".$form['method']."/invalid_".$file_name, 0777);
				}
			}else{
				$err[] = $LANG['UPDATE_SKU_BRAND_VENDOR_NO_DATA'];
				$smarty->assign("errm", $err);
			}
		}elseif(!$this->method_list[$form['method']]){ // always check if user got provide the correct method
			$smarty->assign("errm", "Invalid Update Type.");
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
		
		// always check if user got provide the correct method
		if(!$this->method_list[$form['method']]) die("Invalid Update Type.");
		
		if(!$form['file_name'] || !file_exists("attachments/update_sku_".$form['method']."/".$form['file_name'])){
			die("File no found.");
			exit;
		}
		
		$f = fopen("attachments/update_sku_".$form['method']."/".$form['file_name'], "rt");
		$line = fgetcsv($f);
		
		if(in_array('Error', $line)) {
			$error_index = array_search("Error", $line);
		}else{
			$error_index = count($line);
		}
		
		$this->load_price_type_list();
		$this->load_branch_list();
		
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
				$sku = $con->sql_query("select si.id, si.sku_item_code, si.selling_price, si.cost_price, sku.default_trade_discount_code, 
										sku.sku_type, trade_discount_type, c.department_id, sku.brand_id, sku.vendor_id, sku.id as sku_id
										from sku_items si
										left join sku on sku.id=si.sku_id
										left join category c on c.id = sku.category_id
										where si.sku_item_code = ".ms($si_code)." or 
										si.mcode = ".ms($si_code)." or 
										si.artno = ".ms($si_code)." or 
										si.link_code = ".ms($si_code));
				
				while($si = $con->sql_fetchassoc($sku)){
					$sid = mi($si['id']);
					
					$upd = array();
					$price_type = strtoupper(trim($r[2]));
					switch($form['method']) {
						case 'brand':
							$brand_code = strtoupper(trim($r[1]));
							
							// check and update SKU brand if user did provide
							$brand_info = $this->is_brand_exists($brand_code);
							if($brand_info['info']['id']) $upd['brand_id'] = $brand_info['info']['id'];
							
							break;
						case 'vendor':
							$vendor_code = strtoupper(trim($r[1]));
							
							// check and update SKU vendor if user did provide
							$vendor_id = $this->is_vendor_exists($vendor_code);
							if($vendor_id) $upd['vendor_id'] = $vendor_id;
							
							break;
					}
					
					if($si['sku_type'] == "CONSIGN" && $this->price_type_list[$price_type]) $upd['default_trade_discount_code'] = $price_type;
					
					// ensure it got something to update and it is not repeated SKU 
					if($upd && !$sku_list[$si['sku_id']]){
						$upd['timestamp'] = "CURRENT_TIMESTAMP";
						$q1 = $con->sql_query("update sku set ".mysql_update_by_field($upd)." where id = ".mi($si['sku_id']));
						$num = $con->sql_affectedrows($q1);
						if ($num > 0) $is_updated = true;
						$sku_list[$si['sku_id']] = 1;
					}
					unset($upd);
					
					// force update to latest change price module
					if($form['force_upd_price_type']){
						foreach($this->branch_list as $bid=>$bcode){
							// normal selling price
							$con->sql_query("select * from sku_items_price where branch_id=".mi($bid)." and sku_item_id=".mi($sid));
							$sip = $con->sql_fetchassoc();
							$con->sql_freeresult();
							
							if($sip){
								$new_sip = array();
								$new_sip['branch_id'] = $bid;
								$new_sip['sku_item_id'] = $sid;
								$new_sip['last_update'] = 'CURRENT_TIMESTAMP';
								$new_sip['price'] = $sip['price'] ? $sip['price'] : $si['selling_price'];
								$new_sip['cost'] = $sip['cost'] ? $sip['cost'] : $si['cost_price'];
								$new_sip['trade_discount_code'] = $price_type;
								// sku items price
								$q1 = $con->sql_query("replace into sku_items_price ".mysql_insert_by_field($new_sip));
								$num = $con->sql_affectedrows($q1);
								if ($num > 0) $is_updated = true;
								
								// sku items price history
								unset($new_sip['last_update']);
								$new_sip['added'] = 'CURRENT_TIMESTAMP';
								$new_sip['source'] = 'UPDATE';
								$new_sip['user_id'] = $sessioninfo['id'];
								$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($new_sip));
							}
						}
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
		log_br($sessioninfo['id'], "UPDATE_SKU", 0, "Update SKU by ".ucwords($form['method'])." Successfully, Files Reference: ".$form['file_name'].$xtra_info);
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
		global $con;

		$this->branch_list = array();
		$q1 = $con->sql_query("select id, code from branch where active=1");
		while($r = $con->sql_fetchassoc($q1)){
			$this->branch_list[$r['id']] = $r['code'];
		}
		$con->sql_freeresult($q1);
	}
	
	function is_brand_exists($brd_code){
		global $con;
		
		if(!$brd_code) return false;
		
		$q1 = $con->sql_query("select * from brand where code = ".ms($brd_code));
		$ret['info'] = $con->sql_fetchassoc($q1);
		$ret['count'] = $con->sql_numrows($q1);
		$con->sql_freeresult($q1);
		
		if($ret['count'] > 0) return $ret;
		else return false;
	}
	
	function is_vendor_exists($vd_code){
		global $con;
		
		if(!$vd_code) return false;
		
		$q1 = $con->sql_query("select * from vendor where code = ".ms($vd_code));
		$vd_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($vd_info['id'] > 0) return $vd_info['id'];
		else return false;
	}
}

$UPDATE_SKU = new UPDATE_SKU("");
?>