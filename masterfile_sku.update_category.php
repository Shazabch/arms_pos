<?php
/*
12/24/2018 11:03 AM Andy
- Enhanced to check inactive category.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if($sessioninfo['level'] < 9999 && !privilege('MST_SKU_UPDATE')){
	js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE', BRANCH_CODE), "/index.php");
}

class UPDATE_SKU_CATEGORY extends Module{
	var $headers = array(
		1 => array(
			"si_code" => "Code",
			"category_desc" => "Category"
		),
	); 
	
	var $sample = array(
		1 => array(
			'sample_1' => array("955887311465", "DRINKS"),
			'sample_2' => array("955887311466", "FOODS")
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
		if (!is_dir("attachments/update_sku_category"))	check_and_create_dir("attachments/update_sku_category");
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
	}
	
	function download_sample(){
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_update_sku_category.csv");
		
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
		
		if(count($line) == count($this->headers[$form['method']])) {
			while($r = fgetcsv($f)){
				$error = array();
				
				foreach($r as $tmp_row => $val){
					$r[$tmp_row] = utf8_encode(trim($val));
				}
				
				$ins = array();
				$ins['si_code'] = trim($r[0]);
				$ins['category_desc'] = trim($r[1]);
				if(!$ins['si_code']) continue;
				
				// min & max qty validation
				if(!$ins['category_desc']) $error[] = "Category is empty";
				
				$result['ttl_row']++;
				
				// check sku item
				$sku = $con->sql_query("select si.*
										from sku_items si
										where si.sku_item_code = ".ms($ins['si_code'])." or 
										si.mcode = ".ms($ins['si_code'])." or 
										si.artno = ".ms($ins['si_code'])." or 
										si.link_code = ".ms($ins['si_code']));
								
				if($con->sql_numrows($sku) == 0) $error[] = $ins['si_code']." is an invalid SKU item";
				$con->sql_freeresult($sku);
				
				// check category
				$cat_info = $this->load_category($ins['category_desc']);
				
				// 1. invalid categoty
				// 2. matches more than 1 category
				// 3. category is root, sku item must use level 2 or further category
				// 4. contains sub category (sku can only use the last category)
				if($cat_info['info']['count'] == 0) $error[] = $ins['category_desc']." is an invalid Category";
				elseif($cat_info['info']['count'] > 1) $error[] = $ins['category_desc']." matches more than one category";
				elseif($cat_info['info']['level'] == 1) $error[] = "SKU item cannot be set under Root Category ".$ins['category_desc'];
				elseif($cat_info['info']['has_child_cat'] == 1) $error[] = $ins['category_desc']." contains Sub Category";
				elseif(!$cat_info['info']['active']) $error[] = $ins['category_desc']." is inactive.";
				
				if($error)	$ins['error'] = join('<br />', $error);
				
				$item_lists[] = $ins;
				unset($si, $error);
				
				if($ins['error']){
					$error_list[] = $ins;
					$result['error_row']++;
				}else $result['updated_row']++;
			}
			
			$ret = array();
			if($item_lists){
				$header = $this->headers[$form['method']];
				if($result['error_row'] > 0)	$header[] = 'Error';
				
				$file_name = "sku_category_".time().".csv";
				
				$fp = fopen("attachments/update_sku_category/".$file_name, 'w');
				fputcsv($fp, array_values($header));
				foreach($item_lists as $r){
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/update_sku_category/".$file_name, 0777);
				
				$smarty->assign("result", $result);
				$smarty->assign("file_name", $file_name);
				$smarty->assign("item_header", array_values($header));
				$smarty->assign("item_lists", $item_lists);
				
				// generate error list into CSV
				if($error_list) {
					$fp = fopen("attachments/update_sku_category/invalid_".$file_name, 'w');
					$line[] = "Error";
					fputcsv($fp, array_values($line));
					
					foreach($error_list as $r){
						if($r['error']){
							$r['error'] = str_replace("<br />", "\r\n", $r['error']);
						}
						fputcsv($fp, $r);
					}
					fclose($fp);
					
					chmod("attachments/update_sku_category/invalid_".$file_name, 0777);
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
		
		if(!$form['file_name'] || !file_exists("attachments/update_sku_category/".$form['file_name'])){
			die("File no found.");
			exit;
		}
		
		$f = fopen("attachments/update_sku_category/".$form['file_name'], "rt");
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
				$si_code = trim($r[0]);
				$category_desc = trim($r[1]);
				
				// get category id
				$cat_info = $this->load_category($category_desc);
				
				$sku = $con->sql_query("select si.id, sku.id as sku_id, sku.category_id
										from sku_items si
										left join sku on sku.id=si.sku_id
										where si.sku_item_code = ".ms($si_code)." or 
										si.mcode = ".ms($si_code)." or 
										si.artno = ".ms($si_code)." or 
										si.link_code = ".ms($si_code));
				
				while($si = $con->sql_fetchassoc($sku)){
					// skip this sku if same category
					if($si['category_id'] == $cat_info['info']['id']) continue;
					$sku_id = mi($si['sku_id']);
					
					$upd = array();
					$upd['category_id'] = $cat_info['info']['id'];
					$upd['timestamp'] = "CURRENT_TIMESTAMP";
					$q1 = $con->sql_query("update sku set ".mysql_update_by_field($upd)." where id = ".mi($sku_id));
					$num = $con->sql_affectedrows($q1);
					if ($num > 0){
						$is_updated = true;
						
						// mark the existing and new category for sales cache recalculation
						update_category_changed($si['category_id']);
						update_category_changed($cat_info['info']['id']);
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
		log_br($sessioninfo['id'], "UPDATE_SKU", 0, "Update SKU Category Successfully, Files Reference: ".$form['file_name']);
	}
	
	function load_category($category_desc){
		global $con;
		
		if(!$category_desc) return false;
		
		// load category
		$q1 = $con->sql_query("select c.* 
							   from category c
							   where c.description = ".ms($category_desc));
		
		$ret = array();
		$ret['info'] = $con->sql_fetchassoc($q1);
		$ret['info']['count'] = $con->sql_numrows($q1);
		$ret['info']['has_child_cat'] = 0;
		$con->sql_freeresult($q1);
		
		// get category cache to check if the category still have further levels
		if($ret['info']['count'] == 1){
			$cid = $ret['info']['id'];
			$clv = $ret['info']['level'];
			$q1 = $con->sql_query("select *
								   from category_cache cc
								   where p$clv = ".mi($cid)." and cc.category_id != ".mi($cid));
			
			if($con->sql_numrows($q1) > 0){
				$ret['info']['has_child_cat'] = 1;
			}
		}
		
		return $ret;
	}
}

$UPDATE_SKU_CATEGORY = new UPDATE_SKU_CATEGORY("Update SKU Category by CSV");
?>