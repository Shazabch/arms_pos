<?php
/*
2/25/2015 4:19 PM Justin
- Enhanced to auto insert "enable_gst" config while activated.
- Enhanced to take off login checking.

2/26/2015 6:06 PM Justin
- Enhanced to change email send to sales@arms.my become zaelo@arms.my.
- Enhanced to attach 2 alternative email to use BCC.

3/4/2015 Yinsee
- remove unnecessary file_put_contents 

3/9/2015 9:38 AM Yinsee
- remove auto add config.
- add a reset link just in case

12/19/2019 10:40 AM Justin
- Bug fixed send email function couldn't send out the email.

1/7/2020 5:41 PM Justin
- Removed the IsMail since it causes customers who are using smtp couldn't send out email.
*/

include("include/common.php");

class ACTIVATE_GST extends Module{
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;

 		parent::__construct($title);
	}

	function _default(){
		if(file_exists("include/gst_activation.html")) header("Location: include/gst_activation.html");
		else $this->display();
	}

	function resetitplease() {
		@unlink("include/gst_activation.html");
		print "done.";
	}

	function activate(){
		global $con, $smarty, $config;

		$form = $_REQUEST;
		$err = $this->validate_data();
		
		if($err){
			$smarty->assign("err_msg", $err);
			$smarty->assign("form", $form);
			$this->display();
			exit;
		}
		
		include_once("include/class.phpmailer.php");
		$mailer = new PHPMailer(true);
		
		// set ARMS as sender
		$mailer->From = "noreply@arms.com.my";
		$mailer->FromName = "ARMS Software International Sdn Bhd";

		$mailer->Subject = "ARMS - GST Agreement";
		$mailer->IsHTML(true);
		//$mailer->SMTPDebug=1;

		if($mailer->ValidateAddress($form['email'])){
			// add email for sender
			$mailer->AddAddress($form['email']);
			$mailer->AddBCC("info@arms.my", "ARMS Info Section");
			$mailer->AddBCC("zaelo@arms.my", "Zae Lo");
			
			// create file
			$file_path = "include/gst_activation.html";
			$file_name = "gst_agreement.html";
			//file_put_contents($file_path, "");
			//chmod($file_path, 0777);
			
			// assign file contents
			$smarty->assign("form", $form);
			$smarty->assign("is_generate", true);
			file_put_contents($file_path, $smarty->fetch("activate_gst.tpl"));
			chmod($file_path, 0777);

			// add the html as attachment
			$mailer->AddAttachment($file_path, $file_name);
			
			$mailer->Body = "Please refer to the attachment for your reference.";
			
			// send email
			$send_success = phpmailer_send($mailer, $mailer_info);
			
			// insert config "enable_gst"
			$ins = array();
			$ins['config_name'] = "enable_gst";
			$ins['active'] = 1;
			$ins['type'] = "radio";
			$ins['value'] = 1;
			
			// boss say dont do this
			// $con->sql_query("replace into config_master ".mysql_insert_by_field($ins));
			
			header("Location: include/gst_activation.html");
		}else{
			$err[] = "Email Address not found.";
			$smarty->assign("err_msg", $err);
			$smarty->assign("form", $form);
			$this->display();
		}

	}
	
	function validate_data(){
		global $smarty, $LANG;
		
		$form = $_REQUEST;
		
		if(!trim($form['register_gst'])){
			$err[] = "Please tick to agree for activate GST.";
		}
		
		if(!trim($form['full_name'])){
			$err[] = "Please enter your Full Name.";
		}
		
		if(!trim($form['nric'])){
			$err[] = "Please enter your I/C.";
		}
		
		if(!trim($form['company_name'])){
			$err[] = "Please enter your Company Name.";
		}
		
		if(!trim($form['email'])){
			$err[] = "Please enter your Email Address.";
		}
		
		if ($form['email'] && !preg_match(EMAIL_REGEX, $form['email'])) $err[] = $LANG['SA_EMAIL_PATTERN_INVALID'];
		
		return $err;
	}
}

$ACTIVATE_GST=new ACTIVATE_GST("GST Activation");

?>
