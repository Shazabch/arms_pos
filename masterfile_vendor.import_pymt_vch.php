<?php
/*
3/29/2017 5:11 PM Justin
- Bug fixed on "last_update" from vendor table doesn't exists on customer database.

4/13/2017 4:30 PM Justin
- Enhanced to have export feature.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level'] < 9999 || BRANCH_CODE != "HQ") js_redirect("Do not have permission to access.", "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");

class IMPORT_VENDOR_PYMT_VCH extends Module{
    var $headers = array(
		'1' => array("code" => "Vendor Code",
                     "description" => "Vendor Description",
                     "branch_code" => "Branch Code",
                     "acct_code1" => "Acct Code 1",
                     "acct_code2" => "Acct Code 2",
                     "acct_code3" => "Acct Code 3",
                     "acct_code4" => "Acct Code 4",
                     "acct_code5" => "Acct Code 5",
                     "acct_code6" => "Acct Code 6",
                     "acct_code7" => "Acct Code 7",
                     "acct_code8" => "Acct Code 8",
                     "acct_code9" => "Acct Code 9",
                     "acct_code10" => "Acct Code 10"
					 )
    );
    
    var $sample = array(
		'1' => array(
			'sample_1' => array(" 4080/A01", "TEST Vendor Sdn Bhd", "HQ", "", "4000/U01", "4000/U02", "", "4000/U04", "4000/U05", "4000/U06", "", "4000/U10", "4000/U11")
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
		global $smarty, $con;
		if (!is_dir("attachments"))	check_and_create_dir("attachments");
		if (!is_dir("attachments/vendor_pymt_vch_import"))	check_and_create_dir("attachments/vendor_pymt_vch_import");
		
		// load branch list
		$this->branch_list = array();
		$q1 = $con->sql_query("select * from branch where active=1 order by sequence, code");
		while($r = $con->sql_fetchassoc($q1)){
			$this->branch_list[$r['code']] = $r;
		}
		$con->sql_freeresult($q1);
		
		// construct account code list
		$acct_code_list = array();
		for($i=1; $i<=10; $i++){
			$acct_code_list[$i] = $i;
		}
		
		// load vendor list
		$this->vd_list = array();
		$q1 = $con->sql_query("select * from vendor where active=1 order by code, description");
		while($r = $con->sql_fetchassoc($q1)){
			$vd_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("branch_list", $this->branch_list);
		$smarty->assign("acct_code_list", $acct_code_list);
		$smarty->assign("vd_list", $vd_list);
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
	}
	
	function download_sample_pymt_vch(){				
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_vendor_pymt_vch.csv");
		
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
						$ins['branch_code'] = trim($r[2]);
						
						for($i=1; $i<=10; $i++){
							$val_arr = $i+2; // because account code starts from 3
							$ins['acct_code'.$i] = trim($r[$val_arr]);
						}
						break;
				}
				
				if($ins['code']) {
                    // check vendor code from db whether existed or not
					$q1 = $con->sql_query("select * from vendor where code = ".ms($ins['code']));
					if($con->sql_numrows($q1) > 1) $error[] = 'Multiple vendors matched';
					elseif($con->sql_numrows($q1) == 0) $error[] = 'Invalid Vendor Code';
					$con->sql_freeresult($q1);
                }elseif($ins['description']){
					// check vendor description from db if have more than one vendor with similar vendors match
					$q1 = $con->sql_query("select * from vendor where description = ".ms($ins['description']));
					if($con->sql_numrows($q1) > 1) $error[] = 'Multiple vendors matched';
					elseif($con->sql_numrows($q1) == 0) $error[] = 'Invalid Description';
					$con->sql_freeresult($q1);
				}else{
                    $error[] = "Empty Code and Description";
                }
				
				if($ins['branch_code'] && !$this->branch_list[$ins['branch_code']]['id']){
					$error[] = 'Invalid Branch Code';
				}elseif(!$ins['branch_code']) $error[] = "Empty Branch Code";
				
				if($error)	$ins['error'] = join(', ', $error);
                
                $item_lists[] = $ins;
				
				if($ins['error'])	$result['error_row']++;
				else				$result['import_row']++;
			}
			
			$ret = array();
			if($item_lists){
				$header = $this->headers[$form['method']];
				if($result['error_row'] > 0)	$header[] = 'Error';
				
				$file_name = "pymt_vch_".time().".csv";
				
				$fp = fopen("attachments/vendor_pymt_vch_import/".$file_name, 'w');
				fputcsv($fp, array_values($header));
				foreach($item_lists as $r){
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/vendor_pymt_vch_import/".$file_name, 0777);
				
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
	
	function ajax_import_pymt_vch(){
		global $con, $sessioninfo;
		
		$form = $_REQUEST;
		if(!$form['file_name'] || !file_exists("attachments/vendor_pymt_vch_import/".$form['file_name'])){
			die("File no found.");
			exit;
		}
		
		$f = fopen("attachments/vendor_pymt_vch_import/".$form['file_name'], "rt");
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
			
			$upd = array();
			switch ($form['method']) {
				case '1':
					if(!$r[$error_index]) {
						$code = $r[0];
						$description = $r[1];
						if($code) $filter = "code = ".ms($code);
						elseif($description) $filter = "description = ".ms($description);
						
						// get current vendor
						$q1 = $con->sql_query("select * from vendor where ".$filter);
						$vendor_info = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
						
						$acct_code = array();
						$acct_code = unserialize($vendor_info['acct_code']);
						
						// get branch ID
						$bid = $this->branch_list[$r[2]]['id'];
						
						if(!$bid) continue;
						
						for($i=0; $i<=9; $i++){
							$val_arr = $i+3; // because account code starts from 3
							if($r[$val_arr]) $acct_code[$bid][$i] = $r[$val_arr];
						}

						$upd['acct_code'] = serialize($acct_code);
						//$upd['last_update'] = "CURRENT_TIMESTAMP";

						$q2 = $con->sql_query("update vendor set ".mysql_update_by_field($upd)." where id = ".mi($vendor_info['id']));
						$num = $con->sql_affectedrows($q2);
						
						if ($num > 0)	$num_row++;
					}else {
						$error_list[] = $r;
					}
					break;
			}
		}
		
		if($error_list) {
			$fp = fopen("attachments/vendor_pymt_vch_import/invalid_".$form['file_name'], 'w');
			fputcsv($fp, array_values($line));
			
			foreach($error_list as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			
			chmod("attachments/vendor_pymt_vch_import/invalid_".$form['file_name'], 0777);
		}
						
		if ($num_row > 0) {
			if($error_list)	$ret['partial_ok'] = 1;
			else	$ret['ok'] = 1;
			
			$log_contents = "attachments/vendor_pymt_vch_import/".$form['file_name'];
			log_br($sessioninfo['id'], 'MASTERFILE', 0, "Imported Vendor Payment Voucher Code: ".$log_contents);
		}else $ret['fail'] = 1;

		print json_encode($ret);
	}
	
	function export_data(){
		global $con, $smarty;
		
		$errm = array();
		$errm = $this->export_data_validate();
		
		if($errm){
			$smarty->assign("errm", $errm);
			$smarty->assign("is_export", 1);
			$this->display();
			exit;
		}
		
		$form = $_REQUEST;
		if($form['vendor_id'] > 0) $vd_filter = "where v.id = ".mi($form['vendor_id']);
		
		$blist = array();
		$q1 = $con->sql_query("select v.* from vendor v $vd_filter order by v.code, v.description");

		if($con->sql_numrows($q1) > 0){
			//$contents[] = "Code,Company Name,Address 1,Address 2,Address 3,Address 4,Tel,Fax,Contact Person,Terms\r\n";
			$contents[] = join(",", $this->headers[$form['method']])."\r\n"; // load from pre-set header

			while($r=$con->sql_fetchassoc($q1)){
				$acct_code_list = unserialize($r['acct_code']);
				foreach($form['branch'] as $bid=>$checked){
					if(!$checked) continue;
					
					if(!$blist[$bid]) $blist[$bid] = get_branch_code($bid);
					$r['branch_code'] = $blist[$bid];
					
					foreach($form['acct_code'] as $id=>$checked){
						if(!$checked) continue;
						
						$db_key = $id-1; // database starts with 0
						$r['acct_code'.$id] = $acct_code_list[$bid][$db_key];
					}
					
					$contents[] = "\"$r[code]\",\"$r[description]\",\"$r[branch_code]\",\"$r[acct_code1]\",\"$r[acct_code2]\",\"$r[acct_code3]\",\"$r[acct_code4]\",\"$r[acct_code5]\",\"$r[acct_code6]\",\"$r[acct_code7]\",\"$r[acct_code8]\",\"$r[acct_code9]\",\"$r[acct_code10]\"\r\n";
				}
			}
			unset($blist);
		}
		$con->sql_freeresult($q1);

		$content = join("", $contents);
		header("Content-type: text/plain");
		header('Content-Disposition: attachment;filename=vendor_acct_list.csv');
		print $content;
	}
	
	function export_data_validate(){
		global $LANG;
		
		$form = $_REQUEST;
		
		$errm = array();
		if(!$form['branch']) $errm[] = $LANG['PVC_EXPORT_NO_BRANCH_SELECTED'];
		if(!$form['acct_code']) $errm[] = $LANG['PVC_EXPORT_NO_ACCT_CODE_SELECTED'];
		
		return $errm;
	}
}

$IMPORT_VENDOR_PYMT_VCH = new IMPORT_VENDOR_PYMT_VCH("Import / Export Payment Voucher Code by CSV");
?>