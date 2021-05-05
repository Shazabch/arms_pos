<?php
/*
07/21/2016 15:30 Edwin
- Add new method to import brand

08/03/2016 09:30 Edwin
- Privilege bug fixed.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level'] < 9999 && !privilege('ALLOW_IMPORT_BRAND')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ALLOW_IMPORT_BRAND', BRANCH_CODE), "/index.php");

class IMPORT_BRAND extends Module{
	var $headers = array(
		'1' => array("code" => "Code",
					 "description" => "Description"),
		'2' => array("code" => "Code",
					 "description" => "Description",
					 "group_code" => "Group Code",
					 "group_description" => "Group Description")
	); 
	
	var $sample = array(
		'1' => array(
			'sample_1' => array("NIKE", "NIKE RUN")
		),
		'2' => array(
			'sample_1' =>array("NIKE", "NIKE RUN", "NIKE_G", "NIKE GROUP")
		)
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
		if (!is_dir("attachments/brand_import"))	check_and_create_dir("attachments/brand_import");
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
	}
	
	function download_sample_brand(){
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_brand.csv");
		
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
		
		$item_lists = $code_list = $description_list = array();
		
		$form = $_REQUEST;
		$file = $_FILES['import_csv'];
		$f = fopen($file['tmp_name'], "rt");
		$line = fgetcsv($f);
		
		if(count($line) == count($this->headers[$form['method']])) {
			while($r = fgetcsv($f)){
				$error = array();
				$result['ttl_row']++;
				
				foreach($r as $tmp_row => $val){
					$r[$tmp_row] = utf8_encode(trim($val));
				}
				
				$ins = array();
				switch($form['method']) {
					case '1':
						$ins['code'] = strtoupper(trim($r[0]));
						$ins['description'] = strtoupper(trim($r[1]));
						break;
					case '2':
						$ins['code'] = strtoupper(trim($r[0]));
						$ins['description'] = strtoupper(trim($r[1]));
						$ins['group_code'] = strtoupper(trim($r[2]));
						$ins['group_description'] = strtoupper(trim($r[3]));
						break;
				}
				
				if($ins['code']) {
					// check code from db for duplication
					$q1 = $con->sql_query("select * from brand where code = ".ms($ins['code'])." limit 1");
					if($con->sql_numrows($q1) > 0) $error[] = 'Duplicate Code';
					$con->sql_freeresult($q1);
					
					// check code from import file for duplication
					if(!in_array($ins['code'], $code_list))	$code_list[] = $ins['code'];
					else {
						if(!in_array('Duplicate Code', $error))	$error[] = 'Duplicate Code';
					}
					
					if(strlen($ins['code']) > 6)	$error[] = 'Code Excced 6 Characters';
				}
				
				if($ins['description']) {
					// check description from db for duplication
					$q1 = $con->sql_query("select * from brand where description = ".ms($ins['description'])." limit 1");
					if($con->sql_numrows($q1) > 0) $error[] = 'Duplicate Description';
					$con->sql_freeresult($q1);
					
					// check description from import file for duplication
					if(!in_array($ins['description'], $description_list))	$description_list[] = $ins['description'];
					else {
						if(!in_array('Duplicate Description', $error))	$error[] = 'Duplicate Description';
					}
				}
				else {
					$error[] = 'Empty Description';
				}
				
				if($ins['group_code']) {
					if(strlen($ins['group_code']) > 6)	$error[] = 'Group Code Excced 6 Characters';
				}
				
				if($error)	$ins['error'] = join(', ', $error);
				
				$item_lists[] = $ins;
				
				if($ins['error'])	$result['error_row']++;
				else				$result['import_row']++;
			}
			
			$ret = array();
			if($item_lists){
				$header = $this->headers[$form['method']];
				if($result['error_row'] > 0)	$header[] = 'Error';
				
				$file_name = "brand_".time().".csv";
				
				$fp = fopen("attachments/brand_import/".$file_name, 'w');
				fputcsv($fp, array_values($header));
				foreach($item_lists as $r){
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/brand_import/".$file_name, 0777);
				
				$smarty->assign("result", $result);
				$smarty->assign("file_name", $file_name);
				$smarty->assign("item_header", array_values($header));
				$smarty->assign("item_lists", $item_lists);
			}else{
				$err[] = $LANG['ADMIN_BRAND_NO_DATA'];
				$smarty->assign("errm", $err);
			}
		}else {
			$smarty->assign("errm", "Column not match. Please re-check import file.");
		}
		
		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		$this->display();
	}
	
	function ajax_import_brand(){
		global $con, $sessioninfo;
		
		$form = $_REQUEST;
		
		if(!$form['file_name'] || !file_exists("attachments/brand_import/".$form['file_name'])){
			die("File no found.");
			exit;
		}
		
		$f = fopen("attachments/brand_import/".$form['file_name'], "rt");
		$line = fgetcsv($f);
		
		if(in_array('Error', $line)) {
			$error_index = array_search("Error", $line);
		}else{
			$error_index = count($line);
		}
		
		$ret = $error_list = array();
		$num_row = 0;
		while($r = fgetcsv($f)){
			foreach($r as $tmp_row => $val){
				$r[$tmp_row] = utf8_encode(trim($val));
			}
		
			$brand_ins = array();
			switch ($form['method']) {
				case '1':
					if(!$r[$error_index]) {
						$brand_ins['code'] = $r[0];
						$brand_ins['description'] = $r[1];
						$brand_ins["active"] = 1;
						
						$con->sql_query("insert into brand ".mysql_insert_by_field($brand_ins));
						$num = $con->sql_affectedrows();
						
						if ($num > 0)	$num_row++;
					}else {
						$error_list[] = $r;
					}
					break;
				case '2':
					if(!$r[$error_index]) {
						$brand_ins['code'] = $r[0];
						$brand_ins['description'] = $r[1];
						$brand_ins["active"] = 1;
						
						$con->sql_query("insert into brand ".mysql_insert_by_field($brand_ins));
						$brand_id = $con->sql_nextid();
						$num = $con->sql_affectedrows();
						
						$brgroup['code'] = $r[2];
						$brgroup['description'] = $r[3];
						
						$con->sql_query("select * from brgroup where code=".ms($brgroup['code']));
						$bq = $con->sql_fetchassoc();
						$con->sql_freeresult();
						
						if(!$bq['id']) {
							$brgroup['active'] = 1;
							$con->sql_query("insert into brgroup ".mysql_insert_by_field($brgroup));
							$brgroup_id = $con->sql_nextid();
						}else{
							$brgroup_id = $bq['id'];
						}
						
						$brand_brgroup = array();
						$brand_brgroup['brand_id'] = $brand_id;
						$brand_brgroup['brgroup_id'] = $brgroup_id;
						$con->sql_query("replace into brand_brgroup ".mysql_insert_by_field($brand_brgroup));
						
						if ($num > 0)	$num_row++;
					}else {
						$error_list[] = $r;
					}
					break;
			}
		}
		
		if($error_list) {
			$fp = fopen("attachments/brand_import/invalid_".$form['file_name'], 'w');
			fputcsv($fp, array_values($line));
			
			foreach($error_list as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			
			chmod("attachments/brand_import/invalid_".$form['file_name'], 0777);
		}
						
		if ($num_row > 0) {
			if($error_list)	$ret['partial_ok'] = 1;
			else	$ret['ok'] = 1;
		}else $ret['fail'] = 1;

		print json_encode($ret);
		log_br($sessioninfo['id'], "IMPORT_BRAND", 0, "Import Brand Successfully, Files Reference: ".$form['file_name'].$xtra_info);
	}
}
	
$IMPORT_BRAND = new IMPORT_BRAND("Import Brand");
?>