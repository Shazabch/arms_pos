<?php
/*
11/9/2016 1:55 PM Andy
- Fixed download sample bug.
- Fixed when got empty code, error cannot display properly.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level'] < 9999 && !privilege('ALLOW_IMPORT_UOM')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ALLOW_IMPORT_UOM', BRANCH_CODE), "/index.php");

class IMPORT_UOM extends Module{
    var $headers = array(
		'1' => array("code" => "Code",
                     "description" => "Description",
                     "fraction" => "Fraction")
    );
    
    var $sample = array(
		'1' => array(
			'sample_1' => array("CTN4", "CTN 4", "4")
        )
    );
            
    function __construct($title){
		$this->init();
 		parent::__construct($title);
	}
    
    function _default(){
		$this->display();
	}
	
	function init(){
		global $smarty;
		if (!is_dir("attachments"))	check_and_create_dir("attachments");
		if (!is_dir("attachments/uom_import"))	check_and_create_dir("attachments/uom_import");
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
	}
	
	function download_sample_uom(){				
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_uom.csv");
		
		print join(",", array_values($this->headers[$_REQUEST['method']]));
		foreach($this->sample[$_REQUEST['method']] as $sample) {
			$data = array();
			foreach($sample as $d) {
				$data[] = $d;
			}
			print "\n\r".join(",", $data);
		}
	}
	
	function show_result(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		$file = $_FILES['import_csv'];	
		$f = fopen($file['tmp_name'], "rt");
		$line = fgetcsv($f);
		
		$item_lists = $code_list = array();
		
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
						$ins['description'] = trim($r[1]);
						$ins['fraction'] = trim($r[2]);
						break;
				}
				
				if($ins['code']) {
                    // check code from db for duplication
					$con->sql_query("select * from uom where code = ".ms($ins['code'])." limit 1");
					if($con->sql_numrows() > 0) $error[] = 'Duplicate Code';
					$con->sql_freeresult();
                    
                    // check code from import file for duplication
					if(!in_array($ins['code'], $code_list))	$code_list[] = $ins['code'];
					else {
						if(!in_array('Duplicate Code', $error))	$error[] = 'Duplicate Code';
					}
                }else {
                    $error[] = "Empty Code";
                }
				
				if(strlen($ins['code']) > 6)	$error[] = "Code Exceed 6 Characters";
				if(!$ins['description'])	$error[] = 'Empty Description';
				if($ins['fraction'] <= 0)	$error[] = "Incorrect Fraction";
				
				if($error)	$ins['error'] = join(', ', $error);
                
                $item_lists[] = $ins;
				
				if($ins['error'])	$result['error_row']++;
				else				$result['import_row']++;
			}
			
			$ret = array();
			if($item_lists){
				$header = $this->headers[$form['method']];
				if($result['error_row'] > 0)	$header[] = 'Error';
				
				$file_name = "uom_".time().".csv";
				
				$fp = fopen("attachments/uom_import/".$file_name, 'w');
				fputcsv($fp, array_values($header));
				foreach($item_lists as $r){
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/uom_import/".$file_name, 0777);
				
				$smarty->assign("result", $result);
				$smarty->assign("file_name", $file_name);
				$smarty->assign("item_header", array_values($header));
				$smarty->assign("item_lists", $item_lists);
			}else{
				$smarty->assign("errm", "No data found on the file.");
			}
		}else {
			$smarty->assign("errm", "Column not match. Please re-check import file.");
		}
		
		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		$this->display();
	}
	
	function ajax_import_uom(){
		global $con, $sessioninfo;
		
		$form = $_REQUEST;
		if(!$form['file_name'] || !file_exists("attachments/uom_import/".$form['file_name'])){
			die("File no found.");
			exit;
		}
		
		$f = fopen("attachments/uom_import/".$form['file_name'], "rt");
		$line = fgetcsv($f);
		
		if(in_array('Error', $line)) {
			$error_index = array_search("Error", $line);
		}else{
			$error_index = count($line);
		}
		
		$ret = $error_list = array();
		$num_row = 0;
		while($r = fgetcsv($f)){
			// fix all text that contains special character to convert into utf8
			foreach($r as $tmp_row => $val){
				$r[$tmp_row] = utf8_encode(trim($val));
			}
			
			$uom_ins = array();
			switch ($form['method']) {
				case '1':
					if(!$r[$error_index]) {
						$uom_ins['code'] = $r[0];
						$uom_ins['description'] = $r[1];
						$uom_ins['fraction'] = $r[2];
						$uom_ins['active'] = 1;
						$con->sql_query("insert into uom ".mysql_insert_by_field($uom_ins));
						$num = $con->sql_affectedrows();
						
						if ($num > 0)	$num_row++;
					}else {
						$error_list[] = $r;
					}
					break;
			}
		}
		
		if($error_list) {
			$fp = fopen("attachments/uom_import/invalid_".$form['file_name'], 'w');
			fputcsv($fp, array_values($line));
			
			foreach($error_list as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			
			chmod("attachments/uom_import/invalid_".$form['file_name'], 0777);
		}
						
		if ($num_row > 0) {
			if($error_list)	$ret['partial_ok'] = 1;
			else	$ret['ok'] = 1;
		}else $ret['fail'] = 1;

		print json_encode($ret);
	}
}

$IMPORT_UOM = new IMPORT_UOM("Import UOM");
?>