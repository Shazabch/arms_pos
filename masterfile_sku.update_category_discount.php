<?php
/*
1/21/2019 03:12 PM Justin
- Bug fixed on system accepting non-numeric value for discount.
- Bug fixed on system accepting discount which contains more than 2 decimal points.
- Bug fixed on system show HQ selection for discount, by right it shouldn't show out.
- Bug fixed on the discount value capture as "0" when put it as empty.
- Bug fixed on discount value for different member types will always override regardless put empty or not.
- Enhanced to have "clear" feature where system will clean up the discount value if user provide it.

*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if($sessioninfo['level'] < 9999 && !privilege('MST_SKU_UPDATE')){
	js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE', BRANCH_CODE), "/index.php");
}
if($sessioninfo['level'] < 9999 && !privilege('CATEGORY_DISCOUNT_EDIT')){
	js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CATEGORY_DISCOUNT_EDIT', BRANCH_CODE), "/index.php");
}

class UPDATE_SKU_CATEGORY_DISCOUNT extends Module{
	var $headers = array(
		1 => array("si_code" => "Code",
				   "cat_disc_inherit" => "Category Discount",
				   "nonmember" => "Non-Member",
				   "member" => "Member")
	);
	
	var $sample = array(
		1 => array(
			'sample_1' => array("955887311465", "Inherit"),
			'sample_2' => array("955887311466", "No"),
			'sample_3' => array("955887311467", "Override", "clear", 10)
		),
	);
	
	var $cat_disc_inherit_list = array("override"=>"set", "no"=>"none", "none"=>"none", "inherit"=>"inherit");
	
	var $upd_method_list = array("by_branch"=>"By Branch", "all_branch"=>"All Branch");
	
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
		if (!is_dir("attachments/update_sku_category_discount"))	check_and_create_dir("attachments/update_sku_category_discount");
		
		// pre-load branch list
		$this->load_branch_list();
		
		if($config['membership_module'] && $config['membership_type']){			
			$sample_val = 5; // ensure it is higher than "member"
			$default_sample_val_list = $sample_val_list = $new_sample_val_list = array();
			
			// construct the member list into sample header
			foreach($config['membership_type'] as $member_type=>$member_type_desc){
				$sample_val += rand(1,5);
				if(is_numeric($member_type)) $member_type=$member_type_desc;
				$this->headers[1][$member_type] = $member_type_desc;
				$sample_val_list[] = $sample_val;
			}
			
			// construct the sample value
			$max_sample_key = max(array_keys($this->sample[1]));
			$next_sample_key = "sample_".(str_replace("sample_", "", $max_sample_key)+1);
			$default_sample_val_list = array("955887311468", "Override", 5, "");
			$new_sample_val_list = array_merge($default_sample_val_list, $sample_val_list);
			$this->sample[1][$next_sample_key] = $new_sample_val_list;
			unset($max_sample_key, $next_sample_key, $default_sample_val_list, $sample_val_list, $new_sample_val_list);
		}
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
	}
	
	function download_sample(){
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_update_sku_category_discount.csv");
		
		print join(",", array_values($this->headers[$_REQUEST['method']]))."\n\r";
		foreach($this->sample[$_REQUEST['method']] as $sample) {
			$data = array();
			foreach($sample as $d) {
				$data[] = $d;
			}
			print join(",", $data) . "\n\r";
		}
	}
	
	function show_result(){
		global $con, $smarty, $config, $LANG;
		
		$item_lists = $error_list = array();
		
		$form = $_REQUEST;
		$file = $_FILES['update_csv'];
		$f = fopen($file['tmp_name'], "rt");
		$line = fgetcsv($f);
		
		$this->load_branch_list();
		
		if(count($line) == count($this->headers[$form['method']])) {
			if($config['membership_type']){
				// check if the user is providing the correct membership type
				$multiple_member_index = array_search("Member", $line)+1; // next field
				$invalid_member_type_list = array();
				for($i = $multiple_member_index; $i < count($line); $i++){
					// search the header and prompt errors if not match with config on both key and values
					if(!in_array($line[$i], array_keys($config['membership_type'])) && !in_array($line[$i], $config['membership_type'])){
						$invalid_member_type_list[$line[$i]] = $line[$i];
					}
				}

				if($invalid_member_type_list){
					$err[] = sprintf($LANG['UPDATE_SKU_CAT_DISC_INVALID_MEMBER_TYPE'], join(",", $invalid_member_type_list));
					
					$smarty->assign("errm", $err);
					$smarty->assign("form", $form);
					$this->display();
					exit;
				}
			}
			
			while($r = fgetcsv($f)){
				$error = array();
				
				foreach($r as $tmp_row => $val){
					$r[$tmp_row] = utf8_encode(trim($val));
				}
				
				$ins = array();
				$ins['si_code'] = trim($r[0]);
				$ins['cat_disc_inherit'] = strtolower(trim($r[1]));
				$r[2] = strtolower(trim($r[2]));
				if($r[2] && $r[2] != "clear") $r[2] = round($r[2], 2);
				if($r[2] > 100) $r[2] = 100;
				elseif($r[2] < 0) $r[2] = 0;
				$ins['nonmember'] = $r[2];
				$r[3] = strtolower(trim($r[3]));
				if($r[3] && $r[2] != "clear") $r[3] = round($r[3], 2);
				if($r[3] > 100) $r[3] = 100;
				elseif($r[3] < 0) $r[3] = 0;
				$ins['member'] = $r[3];
				
				if($config['membership_type']){
					for($i = $multiple_member_index; $i < count($line); $i++){
						$curr_member_type = array_search($line[$i], $config['membership_type']);
						if(!$curr_member_type) $curr_member_type = array_search($line[$i], array_keys($config['membership_type']));
						
						$r[$i] = strtolower(trim($r[$i]));
						if($r[$i] && $r[$i] != "clear") $r[$i] = round($r[$i], 2);
						if($r[$i] > 100) $r[$i] = 100;
						elseif($r[$i] < 0) $r[$i] = 0;
						$ins[$curr_member_type] = $r[$i];
					}
				}
				
				// check if user provided the wrong variables from the acceptable list
				$invalid_cat_disc_inherit = false;
				if(!isset($this->cat_disc_inherit_list[$ins['cat_disc_inherit']])){
					$error[] = sprintf($LANG['UPDATE_SKU_CAT_DISC_INVALID_TYPE'], $ins['cat_disc_inherit']);
				}
				
				$result['ttl_row']++;
				
				if(!$error){
					$sku = $con->sql_query("select si.*
											from sku_items si
											left join sku on sku.id=si.sku_id
											left join category c on c.id = sku.category_id
											where si.sku_item_code = ".ms($ins['si_code'])." or 
											si.mcode = ".ms($ins['si_code'])." or 
											si.artno = ".ms($ins['si_code'])." or 
											si.link_code = ".ms($ins['si_code']));
									
					if($con->sql_numrows($sku) == 0) $error[] = $ins['si_code']." is an invalid SKU item";
					$con->sql_freeresult($sku);
				}
				
				if($error)	$ins['error'] = join('<br />', $error);
				
				$item_lists[] = $ins;
				unset($invalid_cat_disc_inherit, $error);
				
				if($ins['error']){
					$error_list[] = $ins;
					$result['error_row']++;
				}else $result['updated_row']++;
			}
			
			$ret = array();
			if($item_lists){
				$header = $this->headers[$form['method']];
				if($result['error_row'] > 0)	$header[] = 'Error';
				
				$file_name = "sku_category_discount_".time().".csv";
				
				$fp = fopen("attachments/update_sku_category_discount/".$file_name, 'w');
				fputcsv($fp, array_values($header));
				foreach($item_lists as $r){
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/update_sku_category_discount/".$file_name, 0777);
				
				$smarty->assign("result", $result);
				$smarty->assign("file_name", $file_name);
				$smarty->assign("item_header", array_values($header));
				$smarty->assign("item_lists", $item_lists);
				
				// generate error list into CSV
				if($error_list) {
					$fp = fopen("attachments/update_sku_category_discount/invalid_".$file_name, 'w');
					$line[] = "Error";
					fputcsv($fp, array_values($line));
					
					foreach($error_list as $r){
						if($r['error']){
							$r['error'] = str_replace("<br />", "\r\n", $r['error']);
						}
						fputcsv($fp, $r);
					}
					fclose($fp);
					
					chmod("attachments/update_sku_category_discount/invalid_".$file_name, 0777);
				}
			}else{
				$err[] = $LANG['UPDATE_SKU_BRAND_VENDOR_NO_DATA'];
				$smarty->assign("errm", $err);
			}
		}else{
			$err[] = "Column not match. Please re-check the file format.";
			$smarty->assign("errm", $err);
		}
		
		$smarty->assign("form", $form);
		$smarty->assign("upd_method_list", $this->upd_method_list);
		$this->display();
	}
	
	function ajax_update_sku(){
		global $con, $sessioninfo, $config;
		
		$form = $_REQUEST;
		
		if(!$form['file_name'] || !file_exists("attachments/update_sku_category_discount/".$form['file_name'])){
			die("File no found.");
			exit;
		}
		
		$this->load_branch_list();
		
		$f = fopen("attachments/update_sku_category_discount/".$form['file_name'], "rt");
		$line = fgetcsv($f);
		$multiple_member_index = array_search("Member", $line)+1; // next field
		
		// loop and store the actual member type used by the system
		if($config['membership_type']){
			for($i = $multiple_member_index; $i < count($line); $i++){
				foreach($config['membership_type'] as $key=>$desc){
					if($line[$i] == $desc && !is_numeric($key)){
						$member_type_list[$i] = $key;
					}elseif($line[$i] == $desc){
						$member_type_list[$i] = $desc;
					}
				}
			}
		}
		
		$branch_list = array();
		if($form['update_method'] == "all_branch"){ // update into all branch
			$branch_list[0] = 0; // all branch are using zero as branch key
		}else{ // update data base on selected branches
			$branch_list = $form['branch_list'];
		}
		
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
				$cat_disc_inherit_desc = strtolower(trim($r[1]));
				$cat_disc_inherit = $this->cat_disc_inherit_list[$cat_disc_inherit_desc];
				$nonmember = trim($r[2]);
				$member = trim($r[3]);
				
				// load user ID
				if($notify_person) $notify_person_info = $this->is_user_exists($notify_person);
				
				$sku = $con->sql_query("select si.id, si.sku_item_code, si.category_disc_by_branch_inherit
										from sku_items si
										left join sku on sku.id=si.sku_id
										where si.sku_item_code = ".ms($si_code)." or 
										si.mcode = ".ms($si_code)." or 
										si.artno = ".ms($si_code)." or 
										si.link_code = ".ms($si_code));
				
				while($si = $con->sql_fetchassoc($sku)){
					$sid = mi($si['id']);
					$upd = $category_disc_by_branch_inherit = array();
					if($si['category_disc_by_branch_inherit']) $category_disc_by_branch_inherit = unserialize($si['category_disc_by_branch_inherit']);
					
					$upd['cat_disc_inherit'] = $cat_disc_inherit;
					$upd['lastupdate'] = "CURRENT_TIMESTAMP";
					
					if($cat_disc_inherit == "set"){
						foreach($branch_list as $bid=>$dummy){
							$category_disc_by_branch_inherit[$bid]['set_override'] = 1;
							if($nonmember !== ""){
								if($nonmember == "clear") $category_disc_by_branch_inherit[$bid]['nonmember']['global'] = "";
								else $category_disc_by_branch_inherit[$bid]['nonmember']['global'] = $nonmember;
							}
							if($member !== ""){
								if($member == "clear") $category_disc_by_branch_inherit[$bid]['member']['global'] = "";
								else $category_disc_by_branch_inherit[$bid]['member']['global'] = $member;
							}
							
							if($config['membership_type']){
								// check if the user is providing the correct membership type
								$invalid_member_type_list = array();
								for($i = $multiple_member_index; $i < count($line); $i++){
									if($r[$i] !== ""){ // found got data need to update for multiple member types
										$r[$i] = trim($r[$i]);
										$curr_member_type = $member_type_list[$i];
										if($r[$i] == "clear") $category_disc_by_branch_inherit[$bid]['member'][$curr_member_type] = "";
										else $category_disc_by_branch_inherit[$bid]['member'][$curr_member_type] = $r[$i];
									}
								}
							}
						}
						if($category_disc_by_branch_inherit) $upd['category_disc_by_branch_inherit'] = serialize($category_disc_by_branch_inherit);
					}
								
					$q1 = $con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id = ".mi($sid));
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
		unset($error_list, $member_type_list, $multiple_member_index);

		print json_encode($ret);
		log_br($sessioninfo['id'], "UPDATE_SKU", 0, "Update SKU Category Discount Successfully, Files Reference: ".$form['file_name']);
	}
	
	function load_branch_list(){
		global $con, $smarty;

		$this->branch_list = array();
		$q1 = $con->sql_query("select id, code from branch where active=1 and code != 'HQ' order by sequence, code");
		while($r = $con->sql_fetchassoc($q1)){
			$this->branch_list[$r['id']] = $r['code'];
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("branch_list", $this->branch_list);
	}
}

$UPDATE_SKU_CATEGORY_DISCOUNT = new UPDATE_SKU_CATEGORY_DISCOUNT("Update SKU Category Discount by CSV");
?>