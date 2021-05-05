<?php
/*
12/30/2020 5:49 PM Rayleen
- New module "Update SKU Info by CSV"
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class UPDATE_SKU_INFO extends Module{

	var $options = array(
		"sku_item_code" => "ARMS Code",
		"mcode" => "MCode",
		"link_code" => "Link Code",
		"artno" => "Art No",
	);

	var $fields = array(
		"additional_description"=>"Additional Description",
		"weight_kg"=>"Weight in KG",
		"weight"=>"Weight Desc",
		"size"=>"Size",
		"color"=>"Color",
		"flavor"=>"Flavour",
		"misc"=>"Misc",
		"rsp_price"=>"RSP",
		"rsp_discount"=>"RSP Discount",
		"model"=>"Model",
		"width"=>"Width",
		"height"=>"Height",
		"length"=>"Length",
		"sn_we"=>"Warranty Period",
		"internal_description"=>"Internal Desc",
		"marketplace_description"=>"Marketplace Desc",
	);

	var $hidden = 'rsp_discount';
	
	function __construct($title){
		$this->init();
 		parent::__construct($title);
	}

	function _default(){	
		$this->display();
	}
	
	function init() {
		global $smarty, $config;
		if (!is_dir("attachments"))	check_and_create_dir("attachments");
		if (!is_dir("attachments/update_sku_info"))	check_and_create_dir("attachments/update_sku_info");
		
		$options = $this->options;
		$options['link_code'] = $config['link_code_name'];

		$smarty->assign("hidden", $this->hidden);
		$smarty->assign("fields", $this->fields);
		$smarty->assign("options", $options);
	}
	
	function upload_csv(){
		global $con, $smarty;
		
		$item_lists = $error_list = array();
		
		$form = $_REQUEST;
		$file = $_FILES['update_csv'];
		$f = fopen($file['tmp_name'], "rt");
		$line = fgetcsv($f);
		$item_headers = array();

		$headers = array();
		foreach($line as $l){
			$headers[] = utf8_encode(trim($l));
		}
		if($line[0]=='SKU Code'){
			$item_headers['sku_code'] = $line[0];
			$columns[] = $line[0];
		}

		foreach ($this->fields as $keyx => $field) {	
			if(in_array($field, $headers)){
				$columns[] = $keyx;
				$item_headers[$keyx] = $field;
			}
		}

		$item_headers['selling_price'] = 'Selling Price';
		$columns[] = 'selling_price';

		$item_lists = $error_list = array();

		$counter = 0;
		$sp_header = 0;
		while($r = fgetcsv($f)){
			$error = array();
		
			foreach($r as $tmp_row => $val){
				$r[$tmp_row] = utf8_encode(trim($val));
			}
			
			$code = trim($r[0]);
			$ins = array();
			$sku = $con->sql_query("select *
		 								from sku_items si
		 								where si.sku_item_code = ".ms($code)." or 
		 								si.mcode = ".ms($code)." or 
		 								si.artno = ".ms($code)." or 
										si.link_code = ".ms($code));
							
			if($con->sql_numrows($sku) == 0) $error[] = $code." is an invalid SKU item";
			$res = $con->sql_fetchassoc($sku);
			$sku_item_id = $res['id'];

			// checking
			// If branches already do change price then it will not be allowed to update.
			$sku_price = $con->sql_query("select sip.* 
							from sku_items_price sip
							join branch b on b.id=sip.branch_id
							where b.active=1 and sip.sku_item_id=".mi($sku_item_id)."
							limit 1");
			if($con->sql_numrows($sku_price) > 0) $error[] = $code." has already been changed price";

			$rows = array();
			foreach ($columns as $index=>$col) {
				$rows[$col] = $r[$index];
				$ins[$counter][$col] = $r[$index];
			}

			// checking
			if($rows['weight_kg']){
				if(!is_numeric($rows['weight_kg'])){
					$error[] = $code." Weight in KG should be number.";
				}
			}

			// check rsp
			if(!$rows['rsp_price'] && $rows['rsp_discount']){    
				$error[] = "Empty RSP";
			}
			if($rows['rsp_price'] && ($rows['rsp_discount'] || trim($rows['rsp_discount']) == 0)){
				$ins[$counter]['selling_price'] = round(floatval($rows['rsp_price']), 2);
				
				if(trim($rows['rsp_discount']) != 0){
					$discount_list = explode("+", $rows['rsp_discount']);
					$invalid_discount = 0;
					if(count($discount_list) > 0){
						$selling_price = $rows['rsp_price'];
						foreach($discount_list as $key=>$discount_val){
							if(!preg_match("/^[0-9]+(\.?[0-9]{1,2})?%?$/", $discount_val))  $invalid_discount+= 1;
							$discount_value = strpos($discount_val, "%") ? ($selling_price*mf($discount_val)) / 100 : $discount_val;
							$selling_price -= $discount_value;
						}
						$ins[$counter]['selling_price'] = round(floatval($selling_price), 2);
					}
					if($invalid_discount > 0)   $error[] = "Invalid RSP discount format";
				}
				$sp_header++;
			}else{
				unset($ins[$counter]['selling_price']);
			}

			$result['ttl_row']++;
			if($error)	$ins[$counter]['error'] = join('<br />', $error);

			$item_lists[] = $ins[$counter];
			unset($error);

			if($ins[$counter]['error']){
				$error_list[] = $ins[$counter];
				$result['error_row']++;
			}else $result['updated_row']++;

			$counter++;
		}

		$ret = array();

		if($item_lists){
			if(!$sp_header){
				unset($item_headers['selling_price']);
			}
			$header = $item_headers;

			if($result['error_row'] > 0)	$header[] = 'Error';

			$file_name = "sku_item_info_".time().".csv";
			
			$fp = fopen("attachments/update_sku_info/".$file_name, 'w');
			fputcsv($fp, array_values($header));
			foreach($item_lists as $item){
				fputcsv($fp, $item);
			}

			fclose($fp);
			chmod("attachments/update_sku_info/".$file_name, 0777);
			
			$smarty->assign("result", $result);
			$smarty->assign("file_name", $file_name);
			$smarty->assign("item_header", array_values($header));
			$smarty->assign("item_lists", $item_lists);
			
			// generate error list into CSV
			if($error_list) {
				$fp = fopen("attachments/update_sku_info/invalid_".$file_name, 'w');
				$line[] = "Error";
				fputcsv($fp, array_values($line));
				
				foreach($error_list as $r){
					if($r['error']){
						$r['error'] = str_replace("<br />", "\r\n", $r['error']);
					}
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/update_sku_info/invalid_".$file_name, 0777);
			}
		}else{
			$err[] = "No Data";
			$smarty->assign("errm", $err);
		}
		
		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		$this->display();
	}
	
	function ajax_update_sku(){
		global $con, $sessioninfo;
		
		$form = $_REQUEST;

		if(!$form['file_name'] || !file_exists("attachments/update_sku_info/".$form['file_name'])){
			die("File no found.");
			exit;
		}
		
		$f = fopen("attachments/update_sku_info/".$form['file_name'], "rt");
		$line = fgetcsv($f);
		
		if(in_array('Error', $line)) {
			$error_index = array_search("Error", $line);
		}else{
			$error_index = count($line);
		}

		$headers = array();
		foreach($line as $l){
			$headers[] = utf8_encode(trim($l));
		}
		$columns = array();
		foreach ($this->fields as $keyx => $field) {	
			if(in_array($field, $headers)){
				$columns[] = $keyx;
			}
		}
		$columns[] = 'selling_price';

		$ret = $error_list = array();
		$num_row = 0;
		while($r = fgetcsv($f)){
			$is_updated = false;
			foreach($r as $tmp_row => $val){
				$r[$tmp_row] = utf8_encode(trim($val));
			}
			
			if(!$r[$error_index]){
				$code = trim($r[0]);
							
				$sku = $con->sql_query("select si.id
										from sku_items si
										where si.sku_item_code = ".ms($code)." or 
										si.mcode = ".ms($code)." or 
										si.artno = ".ms($code)." or 
										si.link_code = ".ms($code));
				
				while($si = $con->sql_fetchassoc($sku)){
					$sku_id = mi($si['id']);

					$upd = array();
					$index = 1;
					$rsp = array();
					foreach($columns as $col){
						if($r[$index]!=''){
							$upd[$col] = $r[$index];
							if($col=='additional_description'){
								$additional_description = array();
								$additional_description_list = explode("\n", trim($r[$index]));
								foreach($additional_description_list as $tmp_r=>$add_desc){
									$additional_description[] = trim($add_desc);
								}
								$upd[$col] = serialize($additional_description);
							}
							if($col=='rsp_price'&&$r[$index]!=''){
								$upd['use_rsp'] = 1;
							}
						}
						$index++;
					}

					$q1 = $con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id = ".mi($sku_id));
					$num = $con->sql_affectedrows($q1);
					if ($num > 0){
						$is_updated = true;
					}
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
		log_br($sessioninfo['id'], "UPDATE_SKU", 0, "Update SKU Info Successfully, Files Reference: ".$form['file_name']);
	}

	function download_csv()
	{
		global $con;

		$fields = $_REQUEST['fields'];

		$header = array();
		$header[] = 'SKU Code';
		foreach ($fields as $desc) {
			$header[] = $this->fields[$desc];
		}

		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=download_update_sku_info.csv");

		// header
		print join(", ", $header) . "\n";
	}
}

$UPDATE_SKU_INFO = new UPDATE_SKU_INFO("Update SKU Info by CSV");
?>