<?php
/*
07/21/2016 10:00 Edwin
- Add new method to import vendor
- Bug fixed on default date does not assign if vendor has 'gst number' and 'gst start date' is not set.

08/03/2016 09:30 Edwin
- Privilege bug fixed.

9/1/2016 11:00 AM Andy
- Enhanced format 2 to have credit limit.

5/31/2017 10:44 AM Qiu Ying
- Bug fixed on "gst_register" not updated

5/6/2019 11:17 AM William
- Enhanced column company no, sample change to "1234567-W" and "567890-K".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level'] < 9999 && !privilege('ALLOW_IMPORT_VENDOR')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ALLOW_IMPORT_VENDOR', BRANCH_CODE), "/index.php");

class IMPORT_VENDOR extends Module{
	var $headers = array(
		'1' => array("code" => "Code",
					 "description" => "Company Name",
					 "company_no" => "Company No",
					 "gst_register_no" => "GST No",
					 "address" => "Address",
					 "phone_1" => "Phone #1",
					 "phone_2" => "Phone #2",
					 "phone_3" => "Fax",
					 "contact_person" => "Contact Person",
					 "contact_email" => "Email"),
		'2' => array("code" => "Code",
					 "description" => "Company Name",
					 "address_1" => "Address #1",
					 "address_2" => "Address #2",
					 "address_3" => "Address #3",
					 "address_4" => "Address #4",
					 "phone_1" => "Phone #1",
					 "phone_2" => "Phone #2",
					 "phone_3" => "Fax",
					 "contact_person" => "Contact Person",
					 "contact_email" => "Email",
					 "term" => "Term",
					 "gst_register_no" => "GST No",
					 "gst_start_date" => "GST Start Date",
					 "bank_account" => "Bank Acc",
					 "company_no" => "Company No",
					 "credit_limit" => "Credit Limit")
	); 
	
	var $sample = array(
		'1' => array(
			'sample_1' => array("5000/P06", "ABBAZ TRADING", "1234567-W", "1245620745", "NO 5 JALAN 2/38G TAMAN SRI SINAR SEGAMBUT 51200 KUALA LUMPUR", "03-78043763", "016-2019902", "03-78043763", "MR M. P. YONG", "mpyong@gmail.com"),
			'sample_2' => array("6298/K12", "HAMIZ TRADING", "567890-K", "1365981066", "NO 962 JALAN BERJAYA INDUSTRISRI MAJU JAYA 51200 KUALA LUMPUR", "03-95336145", "019-2469852", "03-95336150", "MR HAMIZAN", "hamizan@gmail.com")
		),
		'2' => array(
			'sample_1' => array("5000/P06", "ABBAZ TRADING", "NO 5 JALAN 2/38G", "TAMAN SRI SINAR SEGAMBUT", "51200 KUALA LUMPUR", "MALAYSIA", "03-78043763", "016-2019902", "03-78043763", "MR M. P. YONG", "mpyong@gmail.com", "23", "1245620745", "2015-04-01", "42536987855", "1234567-W","30"),
			'sample_2' => array("6298/K12", "HAMIZ TRADING", "NO 962 JALAN BERJAYA", "INDUSTRISRI MAJU JAYA", "51200 KUALA LUMPUR", "MALAYSIA", "03-95336145", "019-2469852", "03-95336150", "MR HAMIZAN", "hamizan@gmail.com", "15", "1365981066", "2015-04-01", "36982151253", "567890-K","")			
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
		if (!is_dir("attachments/vendor_import"))	check_and_create_dir("attachments/vendor_import");
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
	}
	
	function download_sample_vendor(){				
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_vendor.csv");
		
		print join(",", array_values($this->headers[$_REQUEST['method']])) . "\n\r";
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
		
		$form = $_REQUEST;
		$file = $_FILES['import_csv'];	
		$f = fopen($file['tmp_name'], "rt");
		$line = fgetcsv($f);
		
		$item_lists = $code_list = array();
		
		if(count($line) == count($this->headers[$form['method']])) {
			while($r = fgetcsv($f)){
				$error = array();
				$result['ttl_row']++;
				
				// fix all text that contains special character to convert into utf8
				foreach($r as $tmp_row => $val){
					$r[$tmp_row] = utf8_encode(trim($val));
				}
				
				$ins = array();
				switch($form['method']) {
					case '1':
						$ins['code'] = strtoupper(trim($r[0]));
						$ins['description'] = strtoupper(trim($r[1]));
						$ins['company_no'] = strtoupper(trim($r[2]));
						$ins['gst_register_no'] = trim($r[3]);
						$ins['address'] = strtoupper(trim($r[4]));
						$ins['phone_1'] = trim($r[5]);
						$ins['phone_2'] = trim($r[6]);
						$ins['phone_3'] = trim($r[7]);
						$ins['contact_person'] = strtoupper(trim($r[8]));
						$ins['contact_email'] = trim($r[9]);
						break;
					case '2':
						$ins['code'] = strtoupper(trim($r[0]));
						$ins['description'] = strtoupper(trim($r[1]));
						$ins['address_1'] = strtoupper(trim($r[2]));
						$ins['address_2'] = strtoupper(trim($r[3]));
						$ins['address_3'] = strtoupper(trim($r[4]));
						$ins['address_4'] = strtoupper(trim($r[5]));
						$ins['phone_1'] = trim($r[6]);
						$ins['phone_2'] = trim($r[7]);
						$ins['phone_3'] = trim($r[8]);
						$ins['contact_person'] = strtoupper(trim($r[9]));
						$ins['contact_email'] = trim($r[10]);
						$ins['term'] = trim($r[11]);
						$ins['gst_register_no'] = trim($r[12]);
						$ins['gst_start_date'] = trim($r[13]);
						$ins['bank_acc'] = trim($r[14]);
						$ins['company_no'] = strtoupper(trim($r[15]));
						$ins['credit_limit'] = mf($r[16]);
						
						if($ins['gst_register_no'] && !$ins['gst_start_date'])	$ins['gst_start_date'] = '2015-04-01';
						break;
					default:
						break;
				}
				
				if($ins['code']) {
					// check code from db for duplication
					$q1 = $con->sql_query("select * from vendor where code = ".ms($ins['code'])." limit 1");
					if($con->sql_numrows($q1) > 0) $error[] = 'Duplicate Code';
					$con->sql_freeresult($q1);
					
					// check code from import file for duplication
					if(!in_array($ins['code'], $code_list))	$code_list[] = $ins['code'];
					else {
						if(!in_array('Duplicate Code', $error))	$error[] = 'Duplicate Code';
					}
				}else {
					$error[] = 'Empty Code';
				}
				
				if(!$ins['description'])	$error[] = 'Empty Company Name';
				
				if(!$ins['gst_register_no'] && $ins['gst_start_date'])	$error[] = 'Empty GST Registration Number';
				
				if($ins['gst_start_date']) {
					if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $ins['gst_start_date'])) {
						$sdate = explode('-', $ins['gst_start_date']);
						if(!checkdate($sdate[1], $sdate[2], $sdate[0]))	$error[] = 'Date Invalid';
					}else	$error[] = 'Incorrect Date Format';
				}
				
				if($error)	$ins['error'] = join(', ', $error);
				
				$item_lists[] = $ins;
				
				// count the total of successful and fail items to be inserted
				if($ins['error'])	$result['error_row']++;
				else	 			$result['import_row']++;
			}
			
			$ret = array();
			if($item_lists){
				$header = $this->headers[$form['method']];
				if($result['error_row'] > 0)	$header[] = 'Error';
				
				$file_name = "vendor_".time().".csv";
				
				$fp = fopen("attachments/vendor_import/".$file_name, 'w');
				fputcsv($fp, array_values($header));
				foreach($item_lists as $r){
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/vendor_import/".$file_name, 0777);
				
				$smarty->assign("result", $result);
				$smarty->assign("file_name", $file_name);
				$smarty->assign("item_header", array_values($header));
				$smarty->assign("item_lists", $item_lists);
			}else{
				$err[] = $LANG['ADMIN_VENDOR_NO_DATA'];
				$smarty->assign("errm", $err);
			}
		}else {
			$smarty->assign("errm", "Column not match. Please re-check import file.");
		}
		
		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		$this->display();
	}
	
	function ajax_import_vendor(){
		global $con, $sessioninfo;
		
		$form = $_REQUEST;
		if(!$form['file_name'] || !file_exists("attachments/vendor_import/".$form['file_name'])){
			die("File no found.");
			exit;
		}
		
		$f = fopen("attachments/vendor_import/".$form['file_name'], "rt");
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
		
			$vendor_ins = array();
			switch ($form['method']) {
				case '1':
					if(!$r[$error_index]) {
						$vendor_ins['code'] = $r[0];
						$vendor_ins['description'] = $r[1];
						$vendor_ins['company_no'] = $r[2];
						$vendor_ins['gst_register_no'] = $r[3];
						$vendor_ins['address'] = $r[4];
						$vendor_ins['phone_1'] = $r[5];
						$vendor_ins['phone_2'] = $r[6];
						$vendor_ins['phone_3'] = $r[7];
						$vendor_ins['contact_person'] = $r[8];
						$vendor_ins['contact_email'] = $r[9];
						if($r[3]) {
							$vendor_ins['gst_register'] = -1;
							$vendor_ins['gst_start_date'] = '2015-04-01';
						}else		$vendor_ins['gst_register'] = 0;
						$vendor_ins['active'] = 1;
						$con->sql_query("insert into vendor ".mysql_insert_by_field($vendor_ins));
						$num = $con->sql_affectedrows();
						
						if ($num > 0)	$num_row++;
					}else {
						$error_list[] = $r;
					}
					break;
				case '2':
					if(!$r[$error_index]) {
						$vendor_ins['code'] = $r[0];
						$vendor_ins['description'] = $r[1];
						$vendor_ins['address'] = $r[2];
						if($r[3])	$vendor_ins['address'] .= "\n".$r[3];
						if($r[4])	$vendor_ins['address'] .= "\n".$r[4];
						if($r[5])	$vendor_ins['address'] .= "\n".$r[5];
						$vendor_ins['phone_1'] = $r[6];
						$vendor_ins['phone_2'] = $r[7];
						$vendor_ins['phone_3'] = $r[8];
						$vendor_ins['contact_person'] = $r[9];
						$vendor_ins['contact_email'] = $r[10];
						$vendor_ins['term'] = $r[11];
						$vendor_ins['gst_register_no'] = $r[12];
						$vendor_ins['gst_start_date'] = $r[13];
						$vendor_ins['bank_account'] = $r[14];
						$vendor_ins['company_no'] = $r[15];
						$vendor_ins['credit_limit'] = $r[16];
						if($r[12])	$vendor_ins['gst_register'] = -1;
						else		$vendor_ins['gst_register'] = 0;
						$vendor_ins['active'] = 1;
						$con->sql_query("insert into vendor ".mysql_insert_by_field($vendor_ins));
						$num = $con->sql_affectedrows();
						
						if ($num > 0)	$num_row++;
					}else {
						$error_list[] = $r;
					}
					break;
			}	
		}
		
		if($error_list) {
			$fp = fopen("attachments/vendor_import/invalid_".$form['file_name'], 'w');
			fputcsv($fp, array_values($line));
			
			foreach($error_list as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			
			chmod("attachments/vendor_import/invalid_".$form['file_name'], 0777);
		}
						
		if ($num_row > 0) {
			if($error_list)	$ret['partial_ok'] = 1;
			else	$ret['ok'] = 1;
		}else $ret['fail'] = 1;

		print json_encode($ret);
		log_br($sessioninfo['id'], "IMPORT_VENDOR", 0, "Import Vendor Successfully, Files Reference: ".$form['file_name']);
	}
}
	
$IMPORT_VENDOR = new IMPORT_VENDOR("Import Vendor");
?>