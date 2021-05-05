<?
/*
3/05/2021 11:09 AM Rayleen
- New Module "User EForm Application"
- Send email to user with USERS_EFORM_APPROVAL privilege when a new application comes in
- Check user privilege before accessing User Eform Module
- If no 'config.single_server_mode' HQ can only generate QR and approve application and if with 'config.single_server_mode' own branch can only generate QR and approve of own branch 
- Update verify and generate QR code link  (checking for http/https and http port)
- Change code to use class 'Module'
- Change verify link to add "?a=verify"
- Add validation before user approval (avoid duplication)

03/10/2021 10:25 AM Rayleen
- Add 'Address', 'Mobile Number', 'Photo', 'Resume' in User Eform Application
- Remove Template option in Generate QR Page 
- Update Department, Remarks and template when approving/rejecting user application
- Add 'actual_user_id' column in user eform

04/26/2021 5:53 PM Rayleen
- Add "activated_by" column in user application list
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('USERS_EFORM')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'USERS_EFORM', BRANCH_CODE), "/index.php");
$maintenance->check(491);

class USER_EFORM_APPLICATION extends Module
{
	function __construct($title){
 		parent::__construct($title);
	}

	function _default() {
		$this->display();
	}

	function get_branch($branch_id='') {
		global $con, $smarty, $appCore, $config, $sessioninfo;	
		$can_generate_qr = true;
		$b = '';
		if(!$branch_id){
			if(!$config['single_server_mode']){
				$b = ' and id=1';//HQ branch
				if($sessioninfo['branch_id']!=1){//not hq
					$can_generate_qr = false;
				}
			}else{
				if($sessioninfo['branch_id']!=1){//not hq
					$b = ' and id='.$sessioninfo['branch_id'];
				}
			}
		}else{
			$b = ' and id='.$branch_id;
		}
		$con->sql_query("select id, code from branch where active=1 $b");
		$smarty->assign("branch", $con->sql_fetchrowset());
		$con->sql_freeresult();
		$smarty->assign("can_generate_qr", $can_generate_qr);
	}

	function server_host()
	{	
		$url = '';
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
			$url .= "https://";   
		}else{
			$url .= "http://";   
		}
		$url .= $_SERVER['HTTP_HOST'];
		list($_SERVER['HTTP_HOST'], $port) = explode(":", $_SERVER['HTTP_HOST']);
		if(!$port && $_SERVER['SERVER_PORT']){
			$url .= ":".$_SERVER['SERVER_PORT'];
		}
		return $url;
	}

	function generate_code()
	{
		global $con, $smarty, $appCore, $config, $sessioninfo;	
		$this->get_branch();
		
		if(isset($_REQUEST['default_branch_id'])){
			$brach_id = $_REQUEST['default_branch_id'];

			$qr_img_name = tempnam("tmp", "user_eform_qr");
			if(file_exists($qr_img_name)){
				$qr_img_name = $qr_img_name.'.png';
				$qr_code = $this->server_host()."/users.application.eform.php?a=eform&branch_id=".$brach_id;
				$appCore->generateQRCodeImage($qr_img_name, $qr_code);
				$smarty->assign("qr_code", $qr_img_name);
				$smarty->assign("qr_content", $qr_code);
			}
		}
		$smarty->display("users.generate_code.tpl");
	}
	
	function application_list()
	{
		$this->_default();
	}

	function ajax_user_list()
	{
		global $con, $smarty, $sessioninfo, $config;
		$status = ($_REQUEST['status'])?$_REQUEST['status']:0;

		$users = array();
		$user_branch_id = $sessioninfo['branch_id'];
		$can_approve_user = true;
		if(!$config['single_server_mode']){
			$q = ' and u.branch_id=1';//can only approve HQ
			if($user_branch_id!=1){//not hq
				$can_approve_user = false;
			}
		}else{
			if($user_branch_id!=1){//if not HQ can only approve own branch
				$q = ' and u.branch_id='.$user_branch_id;
			}
		}
		$q1 = $con->sql_query("
					select u.id, username, login_id, u.email, u.position, u.fullname, branch.code, u.template, user.u as template_code, u.added, activator.u as user_activator, u.activated_by, u.actual_user_id
					from eform_user u
					left join branch on branch_id=branch.id	
					left join user on user.id=u.template 
					left join user activator on activator.id=u.activated_by 		
					where u.status = $status $q order by u.last_update desc");
		while ($r1=$con->sql_fetchassoc($q1)){
			if(!$r1['activated_by']){
				$activated_by = '-';
			}else{
				if($r1['activated_by']==$r1['actual_user_id']){
					$activated_by = 'email';
				}else{
					$activated_by = $r1['user_activator'];
				}
			}
			$users[] = array(
				'user_id'  => $r1['id'],
				'username' => $r1['username'],
				'login_id' => $r1['login_id'],
				'email'	   => $r1['email'],
				'position' => $r1['position'],
				'fullname' => $r1['fullname'],
				'code'	   => $r1['code'],
				'email'	   => $r1['email'],
				'template' => $r1['template_code'],
				'added'    => $r1['added'],
				'activated_by' => $activated_by,
			);
		}
		$con->sql_freeresult($q1);

		$smarty->assign("status", $status);
		$smarty->assign("can_approve_user", $can_approve_user);
		$smarty->assign("application_list", $users);
		$smarty->display("users.application_list.tpl");
	}

	function show_profile()
	{
		global $con, $smarty, $mprice_list, $config, $sessioninfo, $user_level;

		$id = $_REQUEST['user_id'];	
		$q1 = $con->sql_query("
					select u.*, branch.code, user.u as template_code
					from eform_user u
					left join branch on u.branch_id=branch.id	
					left join user on user.id=u.template
					where u.id=".mi($id));
		$user = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		if($user['approved_by']){
			$q2 = $con->sql_query("
					select u
					from user where id=".mi($user['approved_by']));
			$r2 = $con->sql_fetchassoc($q2);
			$smarty->assign("approved_by", $r2['u']);
			$con->sql_freeresult();
		}
		$can_approve_user = true;
		if(!$config['single_server_mode'] && $sessioninfo['branch_id']!=1){
			$can_approve_user = false;
		}else{
			if($sessioninfo['branch_id']!=1 && ($sessioninfo['branch_id'] != $user['branch_id'])){
				$can_approve_user = false;
			}
		}
		$q3 = $con->sql_query("select u,id from user where template=1");
		$r3 = $con->sql_fetchrowset($q3);
		$con->sql_freeresult();

		$smarty->assign("templates", $r3);
		$smarty->assign("approved_by", $r2['u']);
		$smarty->assign("can_approve_user", $can_approve_user);
		$smarty->assign("user", $user);
		$smarty->display("users.application_profile.tpl");
	}

	function update_user()
	{
		global $con, $smarty, $config, $sessioninfo, $LANG, $MAX_ACTIVE_USER;

		$id = $_REQUEST['user_id'];
		$status = $_REQUEST['status'];
		$template = ($_REQUEST['template']) ? $_REQUEST['template'] : 0;
		$remarks = ($_REQUEST['remarks']) ? $_REQUEST['remarks'] : "";
		$department = ($_REQUEST['department']) ? $_REQUEST['department'] : "";

		$q1 = $con->sql_query("select u.*, b.code as branch_code from eform_user u left join branch b on b.id=u.branch_id where u.id=".mi($id));
		$u = $con->sql_fetchassoc($q1);
		$con->sql_freeresult();

		$newid = 0;
		$is_approved = 0;
		$errmsg = array();

		// 1 = approved
		// 2 = rejected
		if($status == 1){
			// duplication validation
			$con->sql_query("select * from user 
								where
									u =  ".ms($u['username'])." or 
									l =  ".ms($u['login_id'])." or 
									ic_no = ".ms($u['ic_no'])." or
									email = ".ms($u['email']));
			while($r = $con->sql_fetchassoc()){
				if(strtolower($r['u']) == strtolower($u['username'])){
					$errmsg['a']['username'] = sprintf($LANG['USERS_INVALID_NEW_USERNAME_USED'], $u['username']);
				}
				if(strtolower($r['l']) == strtolower($u['login_id'])){
					$errmsg['a']['l'] = sprintf($LANG['USERS_INVALID_LOGIN_ID'], $u['login_id']);
				}
				if($config['user_profile_need_ic']){
					if(strtolower($r['ic_no']) == strtolower($u['ic_no'])){
						$errmsg['a']['ic_no'] = sprintf($LANG['USERS_IC_ALREADY_USED'], $u['ic_no'], $r['u']);
					}
				}
				if(strtolower($r['email']) == strtolower($u['email'])){
					$errmsg['a']['email'] = 'The Email '.$u['email'].' is already used.';
				}
			}
			$con->sql_freeresult();

			$max_active_user = isset($MAX_ACTIVE_USER) ? $MAX_ACTIVE_USER : 0;
			if($max_active_user>0){
				$con->sql_query("select count(id) as count from user where template=0 and active=1");
				$r2 = $con->sql_fetchassoc();
				$con->sql_freeresult();
				if ($r2['count'] >= $max_active_user)
				{
					$msg['a'][] = sprintf($LANG['USERS_INVALID_CANT_ADD_MAX_USER_ALLOWED'], $max_active_user);
				}
			}

			$smarty->assign("errmsg", $errmsg);

			if (!$errmsg['a'])
			{
				// add to user table
				$user = array();
				$user['fullname'] = $u['fullname'];
				$user['email'] = $u['email'];
				$user['l'] = $u['login_id'];
				$user['p'] = $u['password'];
				$user['u'] = $u['username'];
				$user['position'] = $u['position'];
				if($config['user_profile_need_ic']) $user['ic_no'] =  $u['ic_no'];
				$user['template'] = 0;
				$user['last_update'] = 'CURRENT_TIMESTAMP';
				$user['default_branch_id'] = $u['branch_id'];
				$user['user_dept'] = $department;

				$con->sql_begin_transaction();

				// add user
				$con->sql_query("insert into user ".mysql_insert_by_field($user));
				$newid = $con->sql_nextid();

				// create user status row
				$user_status = array();
				$user_status['user_id'] = $newid;
				$user_status['lastlogin'] = '';
				$con->sql_query("insert into user_status ".mysql_insert_by_field($user_status));
				
				// copy privilege
				if ($template)
				{
					$con->sql_query("insert into user_privilege select $newid, branch_id, privilege_code, allowed from user_privilege where user_id = " . mi($template));
				}

				$con->sql_query("update eform_user set 
									last_update = CURRENT_TIMESTAMP,
									template='".$template."',
									department='".$department."',
									remarks='".$remarks."',
									status=1,
									approved_date = CURRENT_TIMESTAMP,
									approved_by = ".$sessioninfo['id'].",
									actual_user_id = ".$newid."
								where id=$id");
				$con->sql_freeresult();

				$con->sql_commit();
				$is_approved = 1;
			}else{
				$smarty->assign("errmsg", $errmsg);
				$this->show_profile();
			}

		}else{//rejected
			$con->sql_begin_transaction();

			$con->sql_query("update eform_user set 
								last_update = CURRENT_TIMESTAMP,
								template='".$template."',
								department='".$department."',
								remarks='".$remarks."',
								status=2,
								approved_date = CURRENT_TIMESTAMP,
								approved_by = ".$sessioninfo['id']."
							where id=$id");
			$con->sql_freeresult();
			$con->sql_commit();		
		}

		if(empty($errmsg)){
			$email = $u['email'];
			if($email){
				$this->send_email($u, $email, $status, $newid);
			}

			if($is_approved){
				// redirect to user profile to update user leve/department, etc
				$user_update = $this->server_host()."/users.php?t=update&eform=".$newid;
				header("Location: $user_update");
			}else{
				header("Location: $_SERVER[PHP_SELF]?a=show_profile&user_id=".$id);	
			}
		}
	}

	function send_email($data, $email, $status, $user_id)
	{
		// Send Email
		include_once("include/class.phpmailer.php");

		$mailer = new PHPMailer(true);
		//$mailer->From = "noreply@arms.com.my";
		$mailer->FromName = "ARMS Notification";
		$mailer->Subject = "User EForm Application";
		$mailer->IsHTML(true);
		$mailer->IsMail();

		$mailer->AddAddress($email);

		if($status==1) {
			$email_body = "<h2><u>User EForm Application APPROVED</u></h2>\r\n";
			$email_body .= "Your account has been successfully approved:<br />\r\n";
		}else{
			$email_body = "<h2><u>User EForm Application Application REJECTED</u></h2>\r\n";
			$email_body .= "Unfortunately, your account has been rejected:<br />\r\n";
		}

		$email_body .= "<b>Full Name</b>: ".$data['fullname']."<br /><br />\r\n";
		$email_body .= "<b>User Name</b>: ".$data['username']."<br /><br />\r\n";
		$email_body .= "<b>Position</b>: ".$data['position']."<br /><br />\r\n";
		$email_body .= "<b>Branch</b>: ".$data['branch_code']."<br /><br />\r\n";
		if($data['remarks']){
			$email_body .= "<b>Remarks</b>: ".$data['remarks']."<br /><br />\r\n";
		}

		if($status == 1){
			$verify_id = base64_encode($user_id);
			$eform_id = base64_encode($data['id']);
			$verify_link = "";
			$verify_link = $this->server_host()."/users.application.eform.php?a=verify&user_id=".$verify_id.'&eform='.$eform_id;

			$email_body .= "Please click on below link to verify and activate your account.<br />\r\n";
			$email_body .= "<a href=\"".$verify_link."\" target='_blank'>Verify and Activate</a><br />\r\n";
			$email_body .= "(Copy the below link if the above link is not working)<br />\r\n";
			$email_body .= $verify_link."<br /><br />\r\n";
			$email_body .= "Please take note the you will not be activated until you verify it.<br /><br />\r\n";
			$email_body .= "After verification, you can login ".$this->server_host()."\r\n";

		}

		$mailer->Body = $email_body;

		// send the mail
		if($send_success = phpmailer_send($mailer, $mailer_info)){
			print ": OK";
		}else{
			print ": Failed";
		}
		$mailer->ClearAddresses();
	}

	function return_profile()
	{
		global $con;

		$user_id = $_REQUEST['user_id'];
		$q2 = $con->sql_query("select id from eform_user where actual_user_id='".$user_id."'");
		$r1 = $con->sql_fetchassoc($q2);
		$con->sql_freeresult();

		header("Location: $_SERVER[PHP_SELF]?a=show_profile&user_id=".$r1['id']);	
	}

}
$USER_EFORM_VERIFY = new USER_EFORM_APPLICATION('User EForm Application');


?>
