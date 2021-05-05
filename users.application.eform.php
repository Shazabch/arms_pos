<?
/*
3/05/2021 11:09 AM Rayleen
- New Module "User EForm Application"
- Additional user checking before verification
- Change code to use class 'Module'

03/09/2021 2:37 PM Rayleen
- Remove login requirement when accessing user application form
- Add 'Address', 'Mobile Number', 'Photo', 'Resume' in User Eform

03/12/2021 10:31 AM Rayleen
- Fix branch code not displaying in email

04/26/2021 5:53 PM Rayleen
- Update "activated_by" column if user activates account
*/
include("include/common.php");

class USER_EFORM extends Module{
	function __construct($title){
		$this->init();
 		parent::__construct($title);
	}

	function _default() {
		$this->display();
	}

	function init(){
		if (!is_dir("attch"))	check_and_create_dir("attch");
		if (!is_dir("attch/eform_user"))	check_and_create_dir("attch/eform_user");
	}

	function eform()
	{
		global $con, $smarty;
		
		$con->sql_query("select * from branch where id=".$_REQUEST['branch_id']);
		$smarty->assign("branch", $con->sql_fetchrowset());
		$con->sql_freeresult();
		$smarty->assign("is_submitted", $_REQUEST['submit']);
		$this->_default();
	}

	function add_eform_user()
	{
		global $con, $smarty, $sessioninfo, $config, $LANG, $MAX_ACTIVE_USER;

		$username = strtolower(strval($_REQUEST['username']));
		$password = strval($_REQUEST['password']);
		$fullname = strval($_REQUEST['fullname']);
		$email = strtolower(strval($_REQUEST['email']));
		$login = strval($_REQUEST['login']);
		$branch_id = intval($_REQUEST['branch_id']);
	    $ic_no = $_REQUEST['ic_no'];
	    $photo = $_FILES['photo'];
	    $address = $_REQUEST['address'];
	    $mobile_number = $_REQUEST['mobile_number'];
	    $resume = $_FILES['resume'];

		// check inputs
		if ($username == '')
		{
			$errmsg['a'][] = $LANG['USERS_INVALID_NEW_USERNAME_EMPTY'];
		}
		elseif (strlen($username) < $MIN_USERNAME_LENGTH || !preg_match("/^[a-z0-9_]+$/i", $username))
		{
			$errmsg['a'][] = sprintf($LANG['USERS_INVALID_NEW_USERNAME_PATTERN'], $username);
		}

		if ($password == '')
		{
			$errmsg['a'][] = $LANG['USERS_INVALID_NEW_PASSWORD_EMPTY'];
		}
		elseif (strlen($password) < $MIN_PASSWORD_LENGTH || !(preg_match("/[0-9]/i", $password) && preg_match("/[a-z]/i", $password)))
		{
			$errmsg['a'][] = $LANG['USERS_INVALID_NEW_PASSWORD_PATTERN'];
		}
		
		//check config "reserve_login_id"
		if($config['reserve_login_id']){
			foreach($config['reserve_login_id'] as $keys=>$reserve_word){
				$len = strlen($reserve_word);
				if(substr(strtolower($username), 0, $len) == strtolower($reserve_word)){
					$errmsg['a'][] = 'The Username is not allow to start with "'.$reserve_word.'".';
				}
				if(substr(strtolower($login), 0, $len) == strtolower($reserve_word)){
					$errmsg['a'][] = 'The Login ID is not allow to start with "'.$reserve_word.'".';
				}
			}
		}

		if ($email == '')
		{
			$errmsg['a'][] = $LANG['USERS_INVALID_NEW_EMAIL_EMPTY'];
		}
		elseif (!preg_match(EMAIL_REGEX, $email))
		{
			$errmsg['a'][] = sprintf($LANG['USERS_INVALID_NEW_EMAIL_PATTERN'], $e);
		}

		if($config['user_profile_need_ic'] && !$ic_no){
	         $errmsg['a'][] = $LANG['USERS_INVALID_IC_EMPTY'];
	    }

		// check duplicated users
		// from user table
		$con->sql_query("select * from user 
							where
								u =  ".ms($username)." or 
								l =  ".ms($login)." or 
								ic_no = ".ms($ic_no)." or
								email = ".ms($email));
		while($u = $con->sql_fetchassoc()){
			if(strtolower($u['u']) == strtolower($username)){
				$errmsg['a']['username'] = sprintf($LANG['USERS_INVALID_NEW_USERNAME_USED'], $username);
			}
			if(strtolower($u['l']) == strtolower($login)){
				$errmsg['a']['l'] = sprintf($LANG['USERS_INVALID_LOGIN_ID'], $u['l']);
			}
			if($config['user_profile_need_ic']){
				if(strtolower($u['ic_no']) == strtolower($ic_no)){
					$errmsg['a']['ic_no'] = sprintf($LANG['USERS_IC_ALREADY_USED'], $ic_no, $u['u']);
				}
			}
			if(strtolower($u['email']) == strtolower($email)){
				$errmsg['a']['email'] = 'The Email '.$email.' is already used.';
			}
		}
		$con->sql_freeresult();

		// from eformuser table
		$con->sql_query("select * from eform_user 
							where status != 2 
							and (
								username = ".ms($username)." or
								login_id = ".ms($login)." or
								ic_no = ".ms($ic_no)." or
								email = ".ms($email)."
							)");
		while($d = $con->sql_fetchassoc()){
			if(strtolower($d['username']) == strtolower($username)){
				$errmsg['a']['username'] = sprintf($LANG['USERS_INVALID_NEW_USERNAME_USED'], $username);
			}
			if(strtolower($d['login_id']) == strtolower($login)){
				$errmsg['a']['login_id'] = sprintf($LANG['USERS_INVALID_LOGIN_ID'], $login);
			}
			if(strtolower($d['email']) == strtolower($email)){
				$errmsg['a']['email'] = 'The Email '.$email.' is already used.';
			}
			if($config['user_profile_need_ic']){
				if(strtolower($d['ic_no']) == strtolower($ic_no)){
					$errmsg['a']['ic_no'] = sprintf($LANG['USERS_IC_ALREADY_USED'], $ic_no, $d['username']);
				}
			}
		}
		$con->sql_freeresult();
		
		// check if max user allowed
		$max_active_user = isset($MAX_ACTIVE_USER) ? $MAX_ACTIVE_USER : 0;
		if($max_active_user>0){
			$con->sql_query("select count(id) as count from user where template=0 and active=1");
			$r = $con->sql_fetchassoc();
			$con->sql_freeresult();
			if ($r['count'] >= $max_active_user)
			{
				$errmsg['a'][] = sprintf($LANG['USERS_INVALID_CANT_ADD_MAX_USER_ALLOWED'], $max_active_user);
			}
		}

		// check if image is valid
		if($photo['tmp_name']){
			$format = array('jpg','jpeg','png');
			$ext = pathinfo($photo['name'],PATHINFO_EXTENSION);
			if (!in_array($ext,$format)) $errmsg['a'][] = 'Invalid Photo Format.';

			if($photo['size'] > 1048576){ // check logo file size (1mb)
				$errmsg['a'][] = 'Invalid Photo file size. Should only be 1MB.';
			}
		}

		if($resume['tmp_name']){
			$ext = pathinfo($resume['name'],PATHINFO_EXTENSION);
			if ($ext!='pdf') $errmsg['a'][] = 'Invalid Resume Format.';

			if($resume['size'] > 1048576){ // check logo file size (1mb)
				$errmsg['a'][] = 'Invalid Resume file size. Should only be 1MB.';
			}
		}

		$mobile_num = preg_replace("/[^0-9]/", "", trim($mobile_number));
		if($mobile_num != $mobile_number){
			$errmsg['a'][] = 'Invalid Mobile Number.';
		}

		$smarty->assign("errmsg", $errmsg);
		$smarty->assign("msg", $msg);

		$this->eform();

		if (!$errmsg['a'])
		{
			$user = array();
			$user['fullname'] = $fullname;
			$user['email'] = $email;
			$user['login_id'] = $login;
			$user['password'] = md5($password);
			if($config['user_profile_need_ic']) $user['ic_no'] = $ic_no;
			$user['position'] = $_REQUEST['position'];
			$user['username'] = $username;
			$user['template'] = $_REQUEST['template'];
			$user['branch_id'] = $branch_id;
			$user['status'] = 0;
			$user['approved_by'] = 0;
			$user['last_update'] = 'CURRENT_TIMESTAMP';
			$user['added'] = 'CURRENT_TIMESTAMP';
			$user['added_by'] = 0;
			$user['address'] = $address;
			$user['mobile_number'] = $mobile_number;

			$dir = 'attch/eform_user/';
			if($photo['tmp_name']){
				$ext = pathinfo($photo['name'],PATHINFO_EXTENSION);
				$filepath = tempnam($dir, "photo");
				unlink($filepath);
				$filepath = $filepath.'.'.$ext;
				move_uploaded_file($photo['tmp_name'], $filepath);
				chmod($filepath,0777);
				$user['photo'] = $dir.basename($filepath);
			}
			if($resume['tmp_name']){
				$filepath_pdf = tempnam($dir, "resume");
				unlink($filepath_pdf);
				$filepath_pdf = $filepath_pdf.'.pdf';
				move_uploaded_file($resume['tmp_name'], $filepath_pdf);
				chmod($filepath_pdf,0777);
				$user['resume'] = $dir.basename($filepath_pdf);
			}
			
			$con->sql_begin_transaction();

			// add eform user
			$con->sql_query("insert into eform_user ".mysql_insert_by_field($user));
			$newid = $con->sql_nextid();
			
			log_br($newid, 'USER PROFILE', $sessioninfo['id'], 'EForm Account created via QR');
			log_br($sessioninfo['id'], 'USER PROFILE', $newid, 'Create account ' . ms($u));
			
			$con->sql_commit();
			$con->sql_freeresult();

			$con->sql_query("select code from branch where id=".$branch_id);
			$b = $con->sql_fetchassoc();
			$con->sql_freeresult();
			$user['branch_code'] = $b['code'];

			$email = array();
			$q1 = $con->sql_query("select user.email
							   from user_privilege 
							   left join user on user.id=user_privilege.user_id
							   where privilege_code = 'USERS_EFORM_APPROVAL' and allowed=1
							   and user.active = 1 group by user.email");
			while($e = $con->sql_fetchassoc()){
				$email[] = $e['email'];
			}
			$con->sql_freeresult();
			if(!empty($email)){
				$this->send_email($user, $email);
			}

			header("Location: $_SERVER[PHP_SELF]?a=eform&branch_id=".$branch_id."&submit=1");	
		}

	}


	function verify() {
		global $con, $smarty, $sessioninfo, $config;
	 	$id = mi(base64_decode($_REQUEST['user_id']));
		$eform_id = mi(base64_decode($_REQUEST['eform']));
		
		$msg = '';
		
		$q3 = $con->sql_query("select status from eform_user where id=".mi($eform_id));
		$d = $con->sql_fetchassoc($q3);
		if($d){
			if($d['status'] == 2){
				$msg = 'User has been rejected and cannot be activated.';
			}
			if($d['status'] != 1){//should be 1 = approved
				$msg = 'Invalid User. Cannot be activated.';
			}
		}else{
			$msg = 'No record found.';
		}
		$con->sql_freeresult();

		$q2 = $con->sql_query("select * from user where active=1 and id=".mi($id));
		if($con->sql_numrows()>0)
		{
			$msg = 'User has already been activated.';
		}
		$con->sql_freeresult();

		if($msg==''){
			$con->sql_begin_transaction();

			$con->sql_query("update eform_user set 
								last_update = CURRENT_TIMESTAMP,
								status=3,
								activated_by=".mi($id)."
							where id=$eform_id");

			$con->sql_query("update user set 
								last_update = CURRENT_TIMESTAMP,
								active = 1
							where id=$id");
			$con->sql_commit();
			
			$con->sql_freeresult();
			$msg = 'User has been activated succcessfully.';
		}
		
		$smarty->assign("msg", $msg);
		$smarty->display("users.application.verify.tpl");

	}

	function send_email($data, $email)
	{
		// Send Email
		include_once("include/class.phpmailer.php");

		$mailer = new PHPMailer(true);
		//$mailer->From = "noreply@arms.com.my";
		$mailer->FromName = "ARMS Notification";
		$mailer->Subject = "New User EForm Application";
		$mailer->IsHTML(true);
		$mailer->IsMail();

		foreach ($email as $e) {
			if($e!='') $mailer->AddAddress($e);
		}

		$email_body = "<h2><u>New User EForm Application</u></h2>\r\n";
		$email_body .= "Your account has been successfully added and it is pending for processing.<br />\r\n";
		$email_body .= "<b>Full Name</b>: ".$data['fullname']."<br /><br />\r\n";
		$email_body .= "<b>User Name</b>: ".$data['username']."<br /><br />\r\n";
		$email_body .= "<b>Position</b>: ".$data['position']."<br /><br />\r\n";
		$email_body .= "<b>Branch</b>: ".$data['branch_code']."<br /><br />\r\n";

		$mailer->Body = $email_body;

		// send the mail
		if($send_success = phpmailer_send($mailer, $mailer_info)){
			print ": OK";
		}else{
			print ": Failed";
			//print "> ".$mailer_info['err'];
		}
		$mailer->ClearAddresses();
	}


}
$USER_EFORM_VERIFY = new USER_EFORM('User Eform');


?>
