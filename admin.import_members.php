<?php
/*
9/27/2016 16:04 Qiu Ying
- Enhance import membership need to check member type and card no

2/8/2017 5:32 PM Andy
- Enhanced to validate Verify Date and Expiry Date.

7/12/2018 1:50 PM Andy
- Fixed NRIC to check 20 chars only and only accept alphanumeric.

1/3/2020 11:05 AM William
- Enhanced to insert "membership_guid" field for membership, membership_history and membership_points table.

1/30/2020 5:17 Andy
- Increased maintenance checking to v438.

8/5/2020 9:41 AM William
- Enhanced to filter the special char when import member

8/19/2020 1:25 PM Andy
- Enhanced to have member type sample.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level'] < 9999) js_redirect("Do not have permission to access.", "/index.php");
$maintenance->check(438);

class IMPORT_MEMBERS extends Module{
	var $headers = array(
		'1' => array("card_no" => "Card No",
					 "points" => "Points",
					 "name" => "Name",
					 "nric" => "NRIC",
					 "gender" => "Gender",
					 "race" => "Race",
					 "dob" => "DOB",
					 "national" => "Nationality",
					 "mobile" => "Mobile",
					 "phone" => "Phone",
					 "member_type" => "Member Type",
					 "verify_date" => "Verify Date",
					 "expiry_date" => "Expiry Date",
					 "fax" => "Fax",
					 "email" => "Email",
					 "address1" => "Address #1",
					 "address2" => "Address #2",
					 "address3" => "Address #3",
					 "address4" => "Address #4",
					 "city" => "City",
					 "state" => "State",
					 "postcode" => "Postcode")
	);
	
	var $sample = array(
		'1' => array(
			"sample_1" => array("13698502563", "235", "SHAMEE SHAMIMI", "730612156328", "F", "Malay", "19730612", "Malaysia", "03-78043763", "016-2019902", "member1", "2016-01-15", "2018-01-14", "03-78043763", "shamee@gmail.com", "NO 5 JALAN 2/38G", "TAMAN SRI SINAR SEGAMBUT", "", "", "KUALA LUMPUR", "WILAYAH PERSEKUTUAN KUALA LUMPUR", "51200")
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
		if (!is_dir("attachments/members_import"))	check_and_create_dir("attachments/members_import");
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
	}
	
	function download_sample_members(){
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_members.csv");
		
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
		global $con, $smarty, $config;
		
		$form = $_REQUEST;
		$file = $_FILES['import_csv'];	
		$f = fopen($file['tmp_name'], "rt");
		$line = fgetcsv($f);
		
		$item_lists = $nric_list = array();
		
		if(count($line) == count($this->headers[$form['method']])) {
			while($r = fgetcsv($f)){
				$error = array();
				$result['ttl_row']++;
				
				foreach($r as $tmp_row => $val){
					$r[$tmp_row] = utf8_encode(trim($val));
				}
				
				$pattern = "/[^A-Za-z0-9]/";
				$ins = array();
				switch($form['method']) {
					case '1':
					$ins['card_no'] = strtoupper(preg_replace($pattern, "", strtoupper(trim($r[0]))));
					$ins['points'] = preg_replace("/[^0-9]/", "", trim($r[1]));
					$ins['name'] = strtoupper(preg_replace($pattern, "", trim($r[2])));
					$ins['nric'] = strtoupper(substr(preg_replace("/[^A-Za-z0-9]/", "", trim($r[3])),0,20));
					if(strtoupper(trim($r[4])) == "MALE" || strtoupper(trim($r[4])) == 'M') $r[4] = "M";
					else $r[4] = "F";
					$ins['gender'] = $r[4];
					$ins['race'] = preg_replace("/[^A-Za-z]/", "", trim($r[5]));
					$ins['dob'] = preg_replace("/[^0-9]/", "", trim($r[6]));
					$ins['national'] = preg_replace("/[^A-Za-z]/", "", trim($r[7]));
					$ins['mobile'] = preg_replace("/[^0-9]/", "", trim($r[8]));
					$ins['phone'] = preg_replace("/[^0-9]/", "", trim($r[9]));
					$ins['member_type'] = preg_replace($pattern, "", trim($r[10]));
					$ins['verify_date'] = trim($r[11]);
					$ins['expiry_date'] = trim($r[12]);
					$ins['fax'] = preg_replace("/[^0-9]/", "", trim($r[13]));
					$ins['email'] = trim($r[14]);
					$ins['address_1'] = strtoupper(trim($r[15]));
					$ins['address_2'] = strtoupper(trim($r[16]));
					$ins['address_3'] = strtoupper(trim($r[17]));
					$ins['address_4'] = strtoupper(trim($r[18]));
					$ins['city'] = strtoupper(preg_replace($pattern, "", trim($r[19])));
					$ins['state'] = strtoupper(preg_replace($pattern, "", trim($r[20])));
					$ins['postcode'] = preg_replace($pattern, "", trim($r[21]));
					break;
				}
				
				if($ins['nric']) {
					// check nric from db for duplication
					$q1 = $con->sql_query("select * from membership where nric = ".ms($ins['nric'])." limit 1");
					if($con->sql_numrows($q1) > 0) $error[] = 'Duplicate NRIC';
					$con->sql_freeresult($q1);
					
					// check nric from import file for duplication
					if(!in_array($ins['nric'], $nric_list))	$nric_list[] = $ins['nric'];
					else {
						if(!in_array('Duplicate NRIC', $error))	$error[] = 'Duplicate NRIC';
					}
				}else {
					$error[] = 'Empty NRIC';
				}
				
				if(!$ins['nric'])	$error[] = 'Empty Name';
				
				$q2 = $con->sql_query("select * from membership where card_no = ".ms($ins['card_no'])." limit 1");
				if($con->sql_numrows($q2) > 0) $error[] = 'Duplicate Card No';
				$con->sql_freeresult($q2);
				
				if($ins['verify_date']) {
					if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $ins['verify_date'])) {
						$sdate = explode('-', $ins['verify_date']);
						if(!checkdate($sdate[1], $sdate[2], $sdate[0]))	$error[] = 'Verify Date Invalid';
						else{
							if($date_error = is_exceed_max_mysql_timestamp(strtotime($ins['verify_date']))){
								$error[] = 'Verify Date cannot over '.$date_error['max_date'];
							}
						}
					}else	$error[] = 'Incorrect Verify Date Format';
				}else{
					$error[] = 'Empty Verify Date';
				}
				
				if($ins['expiry_date']) {
					if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $ins['expiry_date'])) {
						$sdate = explode('-', $ins['expiry_date']);
						if(!checkdate($sdate[1], $sdate[2], $sdate[0]))	$error[] = 'Expiry Date Invalid';
						else{
							if($date_error = is_exceed_max_mysql_timestamp(strtotime($ins['expiry_date']))){
								$error[] = 'Expiry Date cannot over '.$date_error['max_date'];
							}
						}
					}else	$error[] = 'Incorrect Expiry Date Format';
				}else{
					$error[] = 'Empty Expiry Date';
				}
				if(!$error){
					if(strtotime($ins['verify_date']) > strtotime($ins['expiry_date'])){
						$error[] = "Verify Date cannot over Expiry Date";
					}
				}
			
				if ($ins['member_type']){
					if (!array_key_exists($ins['member_type'],$config['membership_type']))
						$error[] = "Invalid Member Type";
				}else	$error[] = 'Empty Member Type';
				
				if($error)	$ins['error'] = join(', ', $error);
				
				$item_lists[] = $ins;
				
				if($ins['error'])	$result['error_row']++;
				else				$result['import_row']++;
			}
			
			$ret = array();
			if($item_lists){
				$header = $this->headers[$form['method']];
				if($result['error_row'] > 0)	$header[] = 'Error';
				
				$file_name = "members_".time().".csv";
				
				$fp = fopen("attachments/members_import/".$file_name, 'w');
				fputcsv($fp, array_values($header));
				foreach($item_lists as $r){
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/members_import/".$file_name, 0777);
				
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
	
	function ajax_import_members(){
		global $con, $appCore;
		
		$form = $_REQUEST;
		if(!$form['file_name'] || !file_exists("attachments/members_import/".$form['file_name'])){
			die("File no found.");
			exit;
		}
		
		$f = fopen("attachments/members_import/".$form['file_name'], "rt");
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
			
			$members_ins = array();
			switch ($form['method']) {
				case '1':
					if(!$r[$error_index]) {
						$membership_guid = $appCore->newGUID();
						$members_ins['membership_guid'] = $membership_guid;
						$members_ins['apply_branch_id'] = 1;
						$members_ins['card_no'] = $r[0];
						$members_ins['points'] = $r[1];
						$members_ins['name'] = $r[2];
						$members_ins['nric'] = $r[3];
						$members_ins['gender'] = $r[4];
						$members_ins['race'] = $r[5];
						$members_ins['dob'] = $r[6];
						$members_ins['national'] = $r[7];
						$members_ins['phone_3'] = $r[8];
						$members_ins['phone_1'] = $r[9];
						$members_ins['member_type'] = $r[10];
						$members_ins['issue_date'] = $r[11];
						$members_ins['verified_date'] = $r[11];
						$members_ins['verified_by'] = 1;
						$members_ins['next_expiry_date'] = $r[12];
						$members_ins['phone_2'] = $r[13];
						$members_ins['email'] = $r[14];
						$members_ins['address'] = $r[15];
						$members_ins['address'] .= "\n".$r[16];
						$members_ins['address'] .= "\n".$r[17];
						$members_ins['address'] .= "\n".$r[18];
						$members_ins['city'] = $r[19];
						$members_ins['state'] = $r[20];
						$members_ins['postcode'] = $r[21];
						
						$con->sql_query("insert into membership ".mysql_insert_by_field($members_ins));
						$num = $con->sql_affectedrows();
						
						$mem_his_ins = array();
						$mem_his_ins['membership_guid'] = $membership_guid;
						$mem_his_ins['nric'] = $members_ins['nric'];
						$mem_his_ins['card_no'] = $members_ins['card_no'];
						$mem_his_ins['branch_id'] = $members_ins['apply_branch_id'];
						$mem_his_ins['card_type'] = "N";
						$mem_his_ins['issue_date'] = $members_ins['issue_date'];
						$mem_his_ins['expiry_date'] = $members_ins['next_expiry_date'];
						$mem_his_ins['remark'] = "N";
						$mem_his_ins['user_id'] = 1;
						$mem_his_ins['added'] = "CURRENT_TIMESTAMP";
						
						$con->sql_query("insert into membership_history ".mysql_insert_by_field($mem_his_ins, false, true));
						
						if($members_ins['points'] && $members_ins['card_no']){
							$mp = array();
							$mp['membership_guid'] = $membership_guid;
							$mp['nric'] = $members_ins['nric'];
							$mp['card_no'] = $members_ins['card_no'];
							$mp['branch_id'] = $members_ins['apply_branch_id'];
							$mp['date'] = "CURRENT_TIMESTAMP";
							$mp['points'] = $members_ins['points'];
							$mp['type'] = "ADJUST";
							$mp['user_id'] = 1;
							$mp['remark'] = $mp['point_source'] = "MIGRATION";
							
							$con->sql_query("insert into membership_points ".mysql_insert_by_field($mp));
						}
						if ($num > 0)	$num_row++;
					}else {
						$error_list[] = $r;
					}
					break;
			}
		}
		
		if($error_list) {
			$fp = fopen("attachments/members_import/invalid_".$form['file_name'], 'w');
			fputcsv($fp, array_values($line));
			
			foreach($error_list as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			
			chmod("attachments/members_import/invalid_".$form['file_name'], 0777);
		}
		
		if ($num_row > 0) {
			if($error_list)	$ret['partial_ok'] = 1;
			else	$ret['ok'] = 1;
		}else $ret['fail'] = 1;
		
		print json_encode($ret);
	}
}

$IMPORT_MEMBERS = new IMPORT_MEMBERS("Import Members");
?>