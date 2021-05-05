<?php
/*
2/12/2019 2:31 PM Andy
- Added new emailManager function sendEmailToARMS().
*/
class emailManager{
	// public var
	var $execTerminalSendEmailLogFile = "cron.send_email.log";
	var $attachmentFolder = "emailManagerAttachment";
	
	// private var
	
	function __construct(){
		global $smarty, $con, $appCore;
		
		// reconstuct the full path for the logfile
		$this->execTerminalSendEmailLogFile = dirname(__FILE__)."/../".$this->execTerminalSendEmailLogFile;
		
		// reconstuct the full path for the attachment folder
		$this->attachmentFolder = dirname(__FILE__)."/../attachments/".$this->attachmentFolder;
		if (!is_dir($this->attachmentFolder))	check_and_create_dir($this->attachmentFolder);

		// check and execute the terminal program
		$this->checkAndExecTerminalSendEmail();
	}
	
	// function to add new email into email_list
	// return array
	public function addEmail($mailer, $params = array()){
		global $con, $LANG, $appCore;
		
		if(!$mailer)	return array('err' => $LANG['EMAIL_OBJ_CANNOT_EMPTY']);
		
		if(@get_class($mailer) != 'PHPMailer')	return array('err' => $LANG['EMAIL_OBJ_INVALID']);
		
		$bid = mi($params['branch_id']);
		if($bid <= 0)	return array('err' => $LANG['EMAIL_BRANCH_ID_INVALID']);
		
		$upd = array();
		$upd['branch_id'] = $bid;
		$upd['user_id'] = mi($params['user_id']);
		$upd['added'] = 'CURRENT_TIMESTAMP';
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['subject'] = $mailer->Subject;
		
		//$upd['active'] = 1;
		//$upd['sent'] = 0;
		
		// Get New GUID
		$guid = $appCore->newGUID();
		$upd['guid'] = $guid;
		
		/////// Get all attachments and copy into attachments folder, need to rename the attachment path
		// Get All Attachment
		$attachments = $mailer->GetAttachments();
		if($attachments){
			// Clear all attachment from mailer object
			$mailer->ClearAttachments();
			
			// Create a folder by Current Year
			$y = mi(date("Y"));
			$folder = $this->attachmentFolder."/".$y;
			if (!is_dir($folder))	check_and_create_dir($folder);
			
			// Append a folder by Email GUID
			$folder .= "/".$guid;
			if (!is_dir($folder))	check_and_create_dir($folder);
			
			// loop attachment
			foreach($attachments as $r){
				// positon 0 = path
				$ori_path = $r[0];
				// position 1 = filename
				$new_path = $folder."/".$r[1];
				// position 2 = name
				$name = $r[2];
				// 3 = encoding
				$encoding = $r[3];
				// 4 = type
				$type = $r[4];
				
				// copy the original attachment to new folder
				@copy($ori_path, $new_path);
				
				// Add back attachment using new path
				$mailer->AddAttachment($new_path, $name, $encoding, $type);
			}
		}
		///////////////
		
		$upd['mailer_data'] = serialize($mailer);
		
		// Begin Transaction
		$con->sql_begin_transaction();
				
		// Add Email
		$con->sql_query("insert into email_list ".mysql_insert_by_field($upd));
		
		// Add Email Log
		$upd2 = array();
		$upd2['email_guid'] = $guid;
		$upd2['user_id'] = $upd['user_id'];
		$upd2['type'] = 'add';
		$upd2['row_sequence'] = 1;
		$upd2['log_time'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into email_list_log ".mysql_insert_by_field($upd2));
		
		// Commit Transaction
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['guid'] = $guid;
		
		return $ret;
	}
	
	// function to get all email using different filter
	// return array
	public function getEmails($params = array()){
		global $con;
		
		$filter = array();
		
		// branch_id
		$bid = mi($params['branch_id']);
		if($bid > 0)	$filter[] = "e.branch_id=$bid";
		
		// user_id
		$user_id = mi($params['user_id']);
		if($user_id > 0)	$filter[] = "e.user_id=$user_id";
		
		// active
		if(isset($params['active'])){
			$filter[] = "e.active=".mi($params['active']);
		}
		
		// sent
		if(isset($params['sent'])){
			$filter[] = "e.sent=".mi($params['sent']);
		}
		
		$str_order_by = '';
		// order by
		if($params['order_by']){
			foreach($params['order_by'] as $sort_field => $sort_order){
				if(!$str_order_by)	$str_order_by = 'order by ';
				else	$str_order_by .= ', ';
				
				$str_order_by .=  "$sort_field $sort_order";
			}
		}
		
		$str_filter ='';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['email_list'] = array();
		$con->sql_query("select e.*
			from email_list e
			$str_filter
			$str_order_by");
		while($r = $con->sql_fetchassoc()){
			$ret['email_list'][$r['guid']] = $r;
		}
		$con->sql_freeresult();
		
		return $ret;
	}
	
	// function to get single email by emailGUID
	// return array
	public function getEmailByGUID($emailGUID){
		global $con, $LANG;
		
		$emailGUID = trim($emailGUID);
		if(!$emailGUID)	return array('err' => $LANG['EMAIL_ID_INVALID']);
		
		$con->sql_query("select * from email_list where guid=".ms($emailGUID));
		$email = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$email)	return array('err' => $LANG['EMAIL_NOT_FOUND']);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['email'] = $email;
		
		return $ret;
	}
	
	// function to get all unsend emails
	// return array
	public function getUnSendEmails($params = array()){
		$params['active'] = 1;
		$params['sent'] = 0;
		return $this->getEmails($params);
	}
	
	// function to send email
	// return array
	public function sendEmail($emailGUID, $params = array()){
		global $con, $LANG;
		
		$data = $this->getEmailByGUID($emailGUID);
		
		// Got Error
		if($data['err'])	return $data;
		if(!$data['ok'] || !$data['email'])	return array('err' => $LANG['EMAIL_NOT_FOUND']);
		
		$email = $data['email'];
		$user_id = mi($params['user_id']);	// user_id may zero if this function call from cron
		
		// Force Resend
		$resend = 0;
		if(isset($params['resend']))	$resend = mi($params['resend']);
		
		// Email already sent before
		if(!$resend && $email['sent'])	return array('err' => $LANG['EMAIL_ALREADY_SENT_BEFORE']);
		
		// Include PHP Mailer
		include_once(dirname(__FILE__)."/class.phpmailer.php");
		
		$mailer = unserialize($email['mailer_data']);
		
		$ret = array();
		try {
			if($mailer->Send()){
				$ret['ok'] = 1;
				
				// Begin Transaction
				$con->sql_begin_transaction();
		
				// Update Email as Sent
				$upd = array();
				$upd['sent'] = 1;
				$upd['last_update'] = $upd['sent_time'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("update email_list set ".mysql_update_by_field($upd)." where guid=".ms($emailGUID));
				
				// Get Email Log Max Sequence Row
				$con->sql_query("select max(row_sequence) as row_sequence from email_list_log where email_guid=".ms($emailGUID));
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				// Add Email Log
				$upd2 = array();
				$upd2['email_guid'] = $emailGUID;
				$upd2['user_id'] = $user_id;
				$upd2['type'] = 'send';
				$upd2['row_sequence'] = $tmp['row_sequence']+1;
				$upd2['log_time'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("insert into email_list_log ".mysql_insert_by_field($upd2));
		
				// Commit Transaction
				$con->sql_commit();
			}else{
				$ret['err'] = $LANG['EMAIL_SENDING_UNKNOWN_ERROR'];
			}
		} catch (phpmailerException $e) {
			$ret['err'] = $e->errorMessage(); //Pretty error messages from PHPMailer
		} catch (Exception $e) {
			$ret['err'] = $e->getMessage(); //Boring error messages from anything else!
		}
		
		return $ret;
	}
	
	// function to run cron.send_email.php in terminal
	// return null
	public function execTerminalSendEmail(){
		global $config;
		
		$command = "php cron.send_email.php";
		if($config['single_server_mode']){
			$command .= " -branch=all";
		}else{
			$command .= " -branch=".BRANCH_CODE;
		}
		
		$command .= " -send";
		$command .= " > ".$this->execTerminalSendEmailLogFile." &";	// using character '&' will make this job run in background
		
		$str = shell_exec($command);
	}
	
	// function to check when the cron last time run, and execute it if more than 1 min
	// return null
	public function checkAndExecTerminalSendEmail(){
		// Get different in second
		$diff = time() - filemtime($this->execTerminalSendEmailLogFile);
		
		if($diff >= 60){	// last run is more than 1 minute ago
			$this->execTerminalSendEmail();			
		}
	}
	
	// function to send email to ARMS
	// return null
	public function sendEmailToARMS($subject, $email_body){
		global $config;
		
		$ret = array();
		if(!$config['arms_pic_email']){
			$ret['err'] = "ARMS Person In Charge Email is not set.";
			return $ret;
		}
		
		// Include PHP Mailer
		include_once(dirname(__FILE__)."/class.phpmailer.php");
		
		$mailer = new PHPMailer(true);
		//$mailer->From = "noreply@arms.com.my";
		$mailer->FromName = php_uname('n');
		$mailer->Subject = '('.php_uname('n').') '.$subject;
		$mailer->IsHTML(true);
		$mailer->IsMail();
		$mailer->AddAddress($config['arms_pic_email']);
		$mailer->Body = $email_body;
		// send the mail
		
		//print "send email to $email_address ";
		//print_r($mailer);
		if($send_success = phpmailer_send($mailer, $mailer_info)){
			$ret['ok'] = 1;
		}else{
			$ret['err'] = $mailer_info['err'];
		}

		return $ret;
	}
}
?>
