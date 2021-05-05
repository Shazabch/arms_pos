<?php
/*
6/25/2019 5:43 PM Andy
- New Module: Membership eForm

7/12/2019 4:12 PM Andy
- Enhanced to have android apk file in email.

7/17/2019 2:20 PM Andy
- Added Address, City and State.

7/18/2019 10:55 AM Andy
- Enhanced to have IOS Test Flight download link.

7/19/2019 11:20 AM Andy
- Remove postcode to become not mandatory, hide all optional fields.

8/13/2019 9:36 AM Andy
- Enhanced to have Android Playstore link and IOS AppStore link.

8/14/2019 11:24 AM Andy
- Fixed after verify the renewal history expiry date error.
- Fixed multiple verify bug.

8/19/2019 3:45 PM Andy
- Enhanced to use memberManager->getMobileAppNewCardNo() to get new card_no.

10/1/2019 11:10 AM Andy
- Enhanced eform new member to check config.membership_mobile_new_register_expiry_duration_year to control new member expiry_date.

10/13/2020 3:13 PM Andy
- Rework the module to become Self Registration.
*/

if (isset($_REQUEST['branch'])){
	setcookie('arms_login_branch', $_REQUEST['branch'], strtotime('+1 year'));
	print "<script>parent.window.location = '".$_SERVER['PHP_SELF']."';</script>";exit;
}

include("include/common.php");
$maintenance->check(183);
if(!$config['membership_eform_settings']){
	die("Module is not enabled");
}

class MEMBERSHIP_EFORM extends Module{
	var $default_member_type = '';
	var $card_no_prefix = '';
	var $card_no_running_no_length = 0;
	var $card_no_running_no_min = 0;
	
	function __construct($title){
		global $con, $smarty;
		
		$this->check_settings();
		
		parent::__construct($title);
	}
	
	function _default(){
		$this->display();
	}
	
	private function check_settings(){
		global $config;
		
		// Member Type
		if($config['membership_eform_settings']['default_member_type']){
			$this->default_member_type = trim($config['membership_eform_settings']['default_member_type']);
			if(!$config['membership_type'][$this->default_member_type]){
				die("Invalid Default Member Type");
			}
		}
		
		// Card No
		$this->card_no_prefix = trim($config['membership_eform_settings']['card_no_prefix']);
		$this->card_no_running_no_length = mi($config['membership_eform_settings']['card_no_running_no_length']);
		$this->card_no_running_no_min = mi($config['membership_eform_settings']['card_no_running_no_min']);
		
		// Default 1 Year
		$this->card_expiry_duration_year = trim($config['membership_eform_settings']['card_expiry_duration_year']);
		if($this->card_expiry_duration_year <= 0 && $this->card_expiry_duration_year != 'life')	$this->card_expiry_duration_year = 1;
		
		if($this->card_no_running_no_length<=0){
			die("Invalid Card No Running Number Length");
		}
	}
	
	function ajax_add_member(){
		global $con, $smarty, $appCore, $config;
		
		$form = $_REQUEST;
		$err = $this->validate_data($form);
		if($err){
			print "Some Error Found:\n";
			foreach($err as $e){
				print "- $e\n";
			}
			exit;
		}
		
		
		$con->sql_begin_transaction();
		
		//print_r($form);
		
		$upd = array();
		$upd['membership_guid'] = $appCore->newGUID();
		//$upd['nric'] = $form['nric'];
		$upd['name'] = $form['name'];
		$upd['gender'] = $form['gender'];
		$upd['member_type'] = $this->default_member_type;
		$upd['dob'] = $form['dob'];
		$upd['postcode'] = trim($form['postcode']);
		$upd['address'] = trim($form['address']);
		$upd['city'] = trim($form['city']);
		$upd['state'] = trim($form['state']);
		$upd['phone_3'] = $form['phone_3'];
		$upd['email'] = $form['email'];
		
		// Get New Card No
		$result = $appCore->memberManager->getEFormNewCardNo();
		if(!$result['ok']){
			$errmsg = $result['error'] ? $result['error'] : 'Unknown Error';
			die($errmsg);
		}
		$new_card_no = $result['new_card_no'];
		
		// Card No same with NRIC
		$upd['nric'] = $upd['card_no'] = $new_card_no;
		$upd['issue_date'] = date("Y-m-d");
		
		//$membership_mobile_new_register_expiry_duration_year = 1;
		if($this->card_expiry_duration_year){
			// life = no expiry date
			if($this->card_expiry_duration_year == 'life'){
				$upd['next_expiry_date'] = '2037-12-31';
			}else{
				/*$membership_mobile_new_register_expiry_duration_year = mi($config['membership_mobile_new_register_expiry_duration_year']);
				if($membership_mobile_new_register_expiry_duration_year <= 0){
					$membership_mobile_new_register_expiry_duration_year = 1;
				}*/
				$upd['next_expiry_date'] = date("Y-m-d", strtotime("+".$this->card_expiry_duration_year." year"));
			}
		}else{
			$upd['next_expiry_date'] = date("Y-m-d", strtotime("+1 year"));
		}
		
		$apply_branch_id = get_branch_id(BRANCH_CODE);
		if($apply_branch_id <= 0)	$apply_branch_id = 1;
		$upd['apply_branch_id'] = $apply_branch_id;
		
		$upd['eform_registered'] = 1;
		$upd['eform_registered_time'] = 'CURRENT_TIMESTAMP';
		//$password = $appCore->generateRandomCode(10);
		//$upd['memb_password'] = md5($password);
		$upd['eform_registered_verify_code'] = $appCore->generateRandomCode(64);
		
		//print_r($upd);
		
		$con->sql_query("insert into membership ".mysql_insert_by_field($upd));
		log_br(1, 'MEMBERSHIP', 0, 'Add Membership ' . $upd['nric']);

		$con->sql_commit();
		
		// Generate QR Code
		$qr_img_name = tempnam("/tmp", "mqr");
		$appCore->generateQRCodeImage($qr_img_name, $new_card_no);

		// Send Email
		include_once("include/class.phpmailer.php");
	
		$mailer = new PHPMailer(true);
		//$mailer->From = "noreply@arms.com.my";
		$mailer->FromName = "ARMS Notification";
		$mailer->Subject = "New Member Registered";
		$mailer->IsHTML(true);
		$mailer->IsMail();
		//$mailer->AddCustomHeader("Content-Transfer-Encoding: base64");
	
		$mailer->AddAddress($upd['email']);
		//$mailer->AddAddress("nava@arms.my");
		
		// Add QRCode as Attachment
		$mailer->AddAttachment($qr_img_name, $new_card_no.".png");
		
		$email_body = "<h2><u>New Member Registered</u></h2>\r\n";
		$email_body .= "Congratulation, you have successfully registered as our membership, below is your membership details.<br />\r\n";
		//$email_body .= "<b>NRIC / Passport</b>: ".$form['nric']."<br />\r\n";
		$email_body .= "<b>Card No</b>: ".$upd['card_no']."<br />\r\n";
		$email_body .= "<b>Full Name</b>: ".$form['name']."<br /><br />\r\n";
		//$email_body .= "<b>Password</b>: ".$password."<br />\r\n";
		
		$email_body .= "Please click on below link to verify your membership.<br />\r\n";
		$url = "http://".$_SERVER['HTTP_HOST']."/".$_SERVER['PHP_SELF']."?a=verify&code=".$upd['eform_registered_verify_code'];
		$email_body .= "<a href=\"".$url."\" target='_blank'>Verify Membership</a><br />\r\n";
		$email_body .= "(Copy the below link if the above link is not working)<br />\r\n";
		$email_body .= $url."<br /><br />\r\n";
		$email_body .= "Please take note your membership is not active until you verify it.<br /><br />\r\n";
		
		// Adroid
		/*if($config['membership_mobile_playstore_path']){
			$email_body .= "Android: <a href=\"".$config['membership_mobile_playstore_path']."\" target='_blank'>Go to Play Store</a><br />\r\n";
			$email_body .= "(Copy the below link if the above link is not working)<br />".$config['membership_mobile_playstore_path']."<br /><br />\r\n";
		}else{
			$apk_path = "membership_mobile.apk";
			if($config['membership_mobile_apk_path']){
				$apk_path = $config['membership_mobile_apk_path'];
			}
			if(file_exists($apk_path)){
				$url_apk = "http://".$_SERVER['HTTP_HOST']."/".$apk_path;
				$email_body .= "Android: <a href=\"".$url_apk."\" target='_blank'>Donwload Here</a><br />\r\n";
				$email_body .= "(Copy the below link if the above link is not working)<br />\r\n".$url_apk."<br /><br />\r\n";
			}
		}
		
		// IOS
		if($config['membership_mobile_ios_appstore_path']){
			$email_body .= "IOS: <a href=\"".$config['membership_mobile_ios_appstore_path']."\" target='_blank'>Go to App Store</a><br />\r\n";
			$email_body .= "(Copy the below link if the above link is not working)<br />\r\n".$config['membership_mobile_ios_appstore_path']."<br /><br />\r\n";
		}else{
			if($config['membership_mobile_ios_testflight_path']){
				$email_body .= "IOS: <a href=\"".$config['membership_mobile_ios_testflight_path']."\" target='_blank'>Donwload Here</a><br />\r\n
				(Required to install TestFlight)<br />\r\n";
				$email_body .= "(Copy the below link if the above link is not working)<br />\r\n".$config['membership_mobile_ios_testflight_path']."<br />\r\n";
			}
		}*/
		
		//$email_body .= "Android: Get it from Google Play<br />";
		//$email_body .= "IOS: Get it from App Store<br />";
		$mailer->Body = $email_body;
		
		// send the mail
		//print_r($mailer);
		if($send_success = phpmailer_send($mailer, $mailer_info)){
			//print ": OK";
		}else{
			//print ": Failed";
			//print "> ".$mailer_info['err'];
		}

		$mailer->ClearAddresses();
	
		$ret = array();
		$ret['ok'] = 1;
		$ret['card_no'] = $upd['card_no'];
		print json_encode($ret);
	}
	
	private function validate_data(&$form){
		global $LANG, $config, $con;

		$errm = array();

		// NRIC
		/*$form['nric'] = preg_replace("/[^A-Z0-9]/", "", strtoupper(strval($form['nric'])));
		if($form['nric'] == ''){
			$errm[] = $LANG['MEMBERSHIP_NRIC_EMPTY'];
		}*/
		
		// Name
		$form['name'] = trim($form['name']);
		if(!$form['name']){
			$errm[] = $LANG['MEMBERSHIP_NAME_EMPTY'];
		}
		
		// DOB
		if(($form['dob_d'] && (!$form['dob_m'] || !$form['dob_y'])) || 
		($form['dob_m'] && (!$form['dob_d'] || !$form['dob_y'])) || 
		($form['dob_y'] && (!$form['dob_d'] || !$form['dob_m']))){
			$errm[] = $LANG['MEMBERSHIP_DOB_EMPTY'];
		}
		if($form['dob_d'] && $form['dob_m'] && $form['dob_y']){
			if ($form['dob_d'] > 31)
			{
				$errm[] = sprintf($LANG['MEMBERSHIP_DOB_INVALID'], "$form[dob_d]-$form[dob_m]-$form[dob_y]");
			}
			elseif ($form['dob_m'] > 12)
			{
				$errm[] = sprintf($LANG['MEMBERSHIP_DOB_INVALID'], "$form[dob_d]-$form[dob_m]-$form[dob_y]");
			}
			elseif ($form['dob_m'] == 2)
			{
				if ($form['dob_d'] > 29)
					$errm[] = sprintf($LANG['MEMBERSHIP_DOB_INVALID'], "$form[dob_d]-$form[dob_m]-$form[dob_y]");
				elseif ($form['dob_d'] > 28 && $form['dob_y']%4 > 0)
					$errm[] = sprintf($LANG['MEMBERSHIP_DOB_INVALID'], "$form[dob_d]-$form[dob_m]-$form[dob_y]");
			}
			
			if (($form['dob_d'] > 30 && ($form['dob_m'] == 4 || $form['dob_m'] == 6 || $form['dob_m'] == 9 || $form['dob_m'] == 11)) || strlen($form['dob_y']) < 4)
			{
				$errm[] = sprintf($LANG['MEMBERSHIP_DOB_INVALID'], "$form[dob_d]-$form[dob_m]-$form[dob_y]");
			}			
		}
		$form['dob'] = sprintf("%04d%02d%02d", $form['dob_y'], $form['dob_m'], $form['dob_d']);
		
		// Post Code
		$form['postcode'] = trim($form['postcode']);
		//if(!$form['postcode']){
		//	$errm[] = $LANG['MEMBERSHIP_POSTCODE_EMPTY'];
		//}
		
		// Phone
		$form['phone_3'] = trim($form['phone_3']);
		if(!$form['phone_3']){
			$errm[] = $LANG['MEMBERSHIP_PHONE_MOBILE_EMPTY'];
		}
		
		// Email
		$form['email'] = trim($form['email']);
		if(!$form['email']){
			$errm[] = $LANG['MEMBERSHIP_EMAIL_EMPTY'];
		}else{
			if(!preg_match(EMAIL_REGEX, $form['email'])){
				$errm[] = $LANG['MEMBERSHIP_EMAIL_PATTERN_INVALID'];
			}
		}
		
		if(!$errm){
			// Check Duplicate NRIC
			/*$con->sql_query("select nric from membership where nric=".ms($form['nric']));
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($tmp){
				$errm[] = "NRIC / Passwod no. already used";
			}*/
			
			// Check Duplicate Email
			$con->sql_query("select email from membership where email=".ms($form['email']));
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($tmp){
				$errm[] = "Email already used";
			}
		}
		
		return $errm;
	}
	
	function verify(){
		global $con, $config, $smarty;
		
		$code = trim($_REQUEST['code']);
		
		if(!$code)	exit;
		
		$con->sql_query("select * from membership where eform_registered_verify_code=".ms($code));
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(date("Y", strtotime($form['verified_date']))>2010){
			die("Member already Verified.");
		}
		if(!$form)	die("Verification Failed.");
		
		$upd = array();
		$upd['nric'] = $form['nric'];
		$upd['card_no'] = $form['card_no'];
		$upd['branch_id'] = $form['apply_branch_id'];
		$upd['user_id'] = 1;
		
		$card_type = '';
		foreach($config['membership_cardtype'] as $type=>$ct)
		{
			if (preg_match($ct['pattern'], $upd['card_no']))
			{
				$card_type = $type;
				break;
			}
		}
		
		$upd['card_type'] = $card_type;
		$upd['issue_date'] = $form['issue_date'];
		$upd['expiry_date'] = $form['next_expiry_date'];
		$upd['remark'] = 'N';
		$upd['added'] = 'CURRENT_TIMESTAMP';
		$upd['m_type'] = $form['member_type'];
		$con->sql_query("insert into membership_history ".mysql_insert_by_field($upd));
		
		$upd2 = array();
		$upd2['verified_by'] = 1;
		$upd2['verified_date'] = "CURRENT_TIMESTAMP";
		$con->sql_query("update membership set ".mysql_update_by_field($upd2)." where nric=".ms($form['nric']));
		
		log_br(1, 'MEMBERSHIP', 0, 'Member Verified: ' . $upd['nric']);
		
		$smarty->display('membership.eform.verified.tpl');
	}
}

$MEMBERSHIP_EFORM = new MEMBERSHIP_EFORM('Membership e-Form');
?>