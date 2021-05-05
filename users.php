<?
/*
9/28/2007 3:01:54 PM yinsee
- branches only can update privilege for current branch

10/1/2007 2:38:22 PM gary
- add user right for users_activate to update profile function.

12/17/2007 4:54:35 PM yinsee
- MIS Assistant and above can update privilege for other branches (while not in HQ)

9/5/2008 11:17:42 AM yinsee
- fix branch filter

8/10/2009 1:30:55 PM Andy
$con->sql_query("insert into privilege values('MST_DEBTOR','Debtor Master File',1,0)",false,false);
9/29/2009 2:42 PM Andy
$con->sql_query("insert into privilege values('DO_REQUEST','Allow access to DO Request Module',0,0)",false,false);
10/1/2009 9:40 AM Andy
$con->sql_query("insert into privilege values('DO_REQUEST_PROCESS','Allow access to DO Request Process Module',0,0)",false,false);

11/11/2009 9:43:57 AM edward
- fix log

10/09/2009 11:15:10 AM edward
- fix updated profile view log

11/18/2009 4:03:12 PM edward
- alter table user add discount_limit
- allow to add and update discount limit

9/3/2010 3:26:11 PM Andy
- Add privilege group "FM" = "Fresh Market".

12/10/2010 3:03:09 PM Andy
- Add NRIC field at user profile, must be enter and unique. (need config)

1/12/2011 5:43:09 PM Andy
- Add create a user_status row once add a new user.

3/4/2011 5:37:17 PM Andy
- Move privilege group name variable to common.php

3/30/2011 11:31:37 AM Alex
- remove saving nric, level, department, vendor, brand when tick create as template

5/11/2011 4:30:41 PM Alex
- add departments root id information

7/29/2011 5:39:35 PM Andy
- Add user activation  will also reset user login retry to 0.

10/06/2011 11:32:35 AM Kee Kee
- Add Mprice privilege for each user

12/6/2011 10:41:26 AM Andy
- Fix privilege group sorting sequence bugs.

3/29/2012 3:38:23 PM Justin
- Added to store phone_1 and sms_notification (based on config "notification_send_sms").

4/5/2012 3:28:49 PM alex
- remove unused prefix group name

10/12/2012 3:20 PM Justin
- Enhanced to allow user key in "-" on email address.

2/4/2013 4:27 PM Justin
- Enhanced to capture regions.

3/14/2013 10:53 AM Justin
- Enhanced Admin profile from user list can only visible by themself.

3/18/2013 4:56 PM Justin
- Bug Fixed loading empty user list.

4/3/2013 5:15 PM Fithri
- fix bug where low-level user can update profile & change user level

6/21/2013 2:00 PM Andy
- Add control to limit user can only use discount by percent for item discount. (need config user_profile_show_item_discount_only_allow_percent).

10/1/2013 6:01 PM Fithri
- make all email field not compulsary
- when sending email, check to trigger send function only when email is set

11/14/2013 3:45 PM Fithri
- when create/update user, Login barcode must be unique

11/22/2013 11:52 AM Fithri
- fix bug duplicate Login Barcode when update user

06/30/2016 16:30 Edwin
- Add lock and unlock user feature in user update.

8/18/2016 10:46 AM Andy
- Fixed user retry counter no reset after un-lock.

9/5/2016 17:29 Qiu Ying
- Hide "POS_RETURN_POLICY" privilege

9/8/2016 11:46 PM Andy
- Enhanced to filter out arms user.

12/6/2016 9:39 AM Andy
- Hide ARMS user from user list.

7/25/2017 4:46 PM Justin
- Enhanced to use email regular expression checking from global settings.

3/28/2018 5:32 PM Andy
- Enhanced user profile to skip foreign currency privileges if no config.foreign_currency

12/10/2018 4:25 PM Andy
- Enhanced user profile to skip suite related privileges if no config.enable_suite_device

4/16/2019 9:57 AM Andy
- Enhanced user profile to skip ARMS Accounting related privileges if no config.arms_accounting_api_setting

5/8/2019 3:56 PM Andy
- Enhanced user profile to skip OSTRIO Accounting related privileges if no config.os_trio_settings

6/28/2019 1:34 PM Andy
- Enhanced user profile to skip Membership Mobile related privileges if no config.membership_mobile_settings

10/29/2019 2:26 PM William
- Enhanced to block username and login id begin word same as config "reserve_login_id" value.

1/30/2020 11:00 AM William
- Enhanced to add new column "user department".

2/11/2020 3:13 PM Andy
- Increased maintenance check to v442.

6/1/2020 11:09 AM William
- Added checking for config array value.

8/21/2020 4:27 PM Andy
- Added sql_begin_transaction adn sql_commit for add / update user.

10/5/2020 12:23 PM William
- Added fnb_username for fnb cashier.

10/23/2020 4:11 PM William
- Bug fixed fnb username cannot work when create user.

1/13/2021 11:42 AM Andy
- Added checking for speed99 privilege.

3/02/2021 9:47 AM Rayleen
- Add 'eform' identifier to able to view user added from eform application

04/05/2021 11:37AM Rayleen
- Activate eform user if user has been activated in user profile page

4/8/2021 2:01 PM Shane
- Hide "POS_BACKEND_PROCESS" privilege if "pos_settings_pos_backend_tab" is off.

04/26/2021 5:53 PM Rayleen
- Log the user who activated the eform application in "activated_by" column
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('USERS') && !privilege('USERS_ACTIVATE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'USERS', BRANCH_CODE), "/index.php");
$maintenance->check(490);
$mprice_list = array();

if($config['sku_multiple_selling_price']){
	foreach($config['sku_multiple_selling_price'] as $mprice)
	{
		if(preg_match("/^(member)|(member[0-9])$/i",$mprice)==false) {
			$mprice_list[] = $mprice;
		}
	}
}
$smarty->assign("mprice_list",$mprice_list);
if($config['reserve_login_id'] && $sessioninfo['id']!=1){
	$smarty->assign("reserve_val",json_encode($config['reserve_login_id']));
}
if (isset($_REQUEST['a']))
{
	$errmsg = array();
	switch ($_REQUEST['a'])
	{
		// add user

		case 'a' :
			$upd = array();
			if ($_REQUEST['template'])
			{
				$u = strval($_REQUEST['newuser']);
				// check inputs
				if ($u == '')
				{
					$errmsg['a'][] = $LANG['USERS_INVALID_NEW_USERNAME_EMPTY'];
				}
				else
				{
					$con->sql_query("select u from user where u = " . ms($u));
					if ($con->sql_numrows() > 0)
					{
						$errmsg['a'][] = sprintf($LANG['USERS_INVALID_NEW_USERNAME_USED'], $u);
					}
				}
			}
                                //update profile
			else
			{
				$u = strtolower(strval($_REQUEST['newuser']));
				$fnb_username = strval($_REQUEST['fnb_username']);
				$barcode = strval($_REQUEST['barcode']);
				$p = strval($_REQUEST['newpassword']);
				$fn = strval($_REQUEST['fullname']);
				$e = strtolower(strval($_REQUEST['newemail']));
				$l = strval($_REQUEST['newlogin']);
				$bid= intval($_REQUEST['default_branch_id']);
				$lvl = intval($_REQUEST['level']);
				$dept = $_REQUEST['departments'];
				$vendors = $_REQUEST['vendors'];
				$brands = $_REQUEST['brands'];
				$disc_limit = $_REQUEST['disc_limit'];
                $ic_no = $_REQUEST['ic_no'];
                $allow_mprice = serialize($_REQUEST['allow_price']);
                if($config['consignment_modules'] && $config['masterfile_branch_region']) $regions = serialize($_REQUEST['regions']);
				// check inputs
				if ($u == '')
				{
					$errmsg['a'][] = $LANG['USERS_INVALID_NEW_USERNAME_EMPTY'];
				}
				elseif (strlen($u) < $MIN_USERNAME_LENGTH || !preg_match("/^[a-z0-9_]+$/i", $u))
				{
					$errmsg['a'][] = sprintf($LANG['USERS_INVALID_NEW_USERNAME_PATTERN'], $u);
				}
				else
				{
					$con->sql_query("select u from user where u = " . ms($u));
					if ($con->sql_numrows() > 0)
					{
						$errmsg['a'][] = sprintf($LANG['USERS_INVALID_NEW_USERNAME_USED'], $u);
					}
				}

				if ($p == '')
				{
					$errmsg['a'][] = $LANG['USERS_INVALID_NEW_PASSWORD_EMPTY'];
				}
				elseif (strlen($p) < $MIN_PASSWORD_LENGTH || !(preg_match("/[0-9]/i", $p) && preg_match("/[a-z]/i", $p)))
				{
					$errmsg['a'][] = $LANG['USERS_INVALID_NEW_PASSWORD_PATTERN'];
				}
				
				//check config "reserve_login_id"
				if($config['reserve_login_id'] && $sessioninfo['id']!=1){
					foreach($config['reserve_login_id'] as $keys=>$reserve_word){
						$len = strlen($reserve_word);
						if(substr(strtolower($u), 0, $len) == strtolower($reserve_word)){
							$errmsg['a'][] = 'The Username is not allow to start with "'.$reserve_word.'".';
						}
						if(substr(strtolower($l), 0, $len) == strtolower($reserve_word)){
							$errmsg['a'][] = 'The Login ID is not allow to start with "'.$reserve_word.'".';
						}
					}
				}

				if ($e == '')
				{
					//$errmsg['a'][] = $LANG['USERS_INVALID_NEW_EMAIL_EMPTY'];
				}
				elseif (!preg_match(EMAIL_REGEX, $e))
				{
					$errmsg['a'][] = sprintf($LANG['USERS_INVALID_NEW_EMAIL_PATTERN'], $e);
				}
				
				if($fnb_username != ""  && $config['enable_suite_device']){
					$con->sql_query("select fnb_username from user where fnb_username=".ms($fnb_username));
					if ($con->sql_numrows()>0){
						$errmsg['a'][] = "Fnb Username $fnb_username already existed";
					}
					$con->sql_freeresult();
				}
				
				$con->sql_query("select * from user where l=".ms($l));
				if ($con->sql_numrows()>0)
				{
					$errmsg['a'][] = sprintf($LANG['USERS_INVALID_LOGIN_ID'], $l);
				}
				
				if ($barcode) {
					$con->sql_query("select id from user where barcode=".ms($barcode)." limit 1");
					if ($con->sql_numrows()>0)
					{
						$errmsg['a'][] = "Login Barcode $barcode already existed";
					}
				}
				
				// got config to check nric
			    if($config['user_profile_need_ic']){
			        if(!$ic_no){    // user no key in ic
                        $errmsg['a'][] = $LANG['USERS_INVALID_IC_EMPTY'];
					}else{
                        $con->sql_query("select id, u from user where ic_no=".ms($ic_no));
                        $tmp = $con->sql_fetchrow();
						if($tmp){  // IC already used
	                        $errmsg['a'][] = sprintf($LANG['USERS_IC_ALREADY_USED'], $ic_no, $tmp['u']);
						}
						$con->sql_freeresult();
					}
				}

			    //data need to be saved into database
				if($config['enable_suite_device'])  $upd['fnb_username'] = $_REQUEST['fnb_username'];
				$upd['fullname'] = $fn;
				$upd['email'] = $e;
				$upd['barcode'] = $barcode;
				$upd['l'] = $l;
				$upd['p'] = md5($p);
				$upd['discount_limit'] = $disc_limit;
				if($config['user_profile_need_ic']) $upd['ic_no'] = $ic_no;
				$upd['position'] = $_REQUEST['position'];
				$upd['user_dept'] = $_REQUEST['user_dept'];
				$upd['level'] = $lvl;
				$upd['departments'] = serialize($dept);
				$upd['vendors'] = serialize($vendors);
				$upd['brands'] = serialize($brands);
				$upd['allow_mprice'] = $allow_mprice;
				$upd['phone_1'] = trim($_REQUEST['phone_1']);
				if($config['notification_send_sms']) $upd['sms_notification'] = $_REQUEST['sms_notification'];
				if($config['consignment_modules'] && $config['masterfile_branch_region']) $upd['regions'] = $regions;
				if($config['user_profile_show_item_discount_only_allow_percent'])	$upd['item_disc_only_allow_percent'] = mi($_REQUEST['item_disc_only_allow_percent']);
			}

			if (!$errmsg['a'])
			{
				// check if max user allowed
				$con->sql_query("select count(id) as count from user where NOT template and active");
				$r = $con->sql_fetchrow();
				if ($r['count'] >= $MAX_ACTIVE_USER)
				{
					$msg['a'][] = sprintf($LANG['USERS_INVALID_CANT_ADD_MAX_USER_ALLOWED'], $MAX_ACTIVE_USER);
				}
				else
				{
					$con->sql_begin_transaction();
					
				    // update the rest of info

					$upd['u'] = $u;
					$upd['template'] = $_REQUEST['template'];
					$upd['lastlogin'] = 0;
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$upd['default_branch_id'] = $bid;
					// add user
					$con->sql_query("insert into user ".mysql_insert_by_field($upd));
					$newid = $con->sql_nextid();
					
					// create user status row
					$user_status = array();
					$user_status['user_id'] = $newid;
					$user_status['lastlogin'] = '';
					$con->sql_query("insert into user_status ".mysql_insert_by_field($user_status));
					
                    // just incase there are existing privilege, remove them
					$con->sql_query("delete from user_privilege where user_id = $newid");
					
					// copy privilege
					if ($_REQUEST['use_template'])
					{
						$con->sql_query("insert into user_privilege select $newid, branch_id, privilege_code, allowed from user_privilege where user_id = " . mi($_REQUEST['template_id']));
					}
					else
					{
						// apply priv from form
						if ($_REQUEST['user_privilege'])
						{
							foreach ($_REQUEST['user_privilege'] as $k=>$v)
							{
								foreach ($_REQUEST['user_privilege'][$k] as $k2=>$v)
								{
									$con->sql_query("insert into user_privilege values ($newid, " . ms($k) . ", " . ms($k2) . ", " . mb($_REQUEST['user_privilege'][$k][$k2]) . ")");
								}
							}
						}
					}

					$msg['a'][] = sprintf($LANG['USERS_VALID_NEW_USER_ADDED'], $u);

     				$_REQUEST['newuser'] = '';
					$_REQUEST['fnb_username'] = '';
     				$_REQUEST['barcode'] = '';
					$_REQUEST['newpassword'] = '';
					$_REQUEST['newpassword2'] = '';
					$_REQUEST['newemail'] = '';
					$_REQUEST['fullname'] = '';
					$_REQUEST['departments'] = '';
					$_REQUEST['newlogin'] = '';
					$_REQUEST['template'] = '';
					$_REQUEST['default_branch_id'] = '';
					$_REQUEST['level'] = '';
					$_REQUEST['position'] = '';
					$_REQUEST['user_dept'] = '';
					$_REQUEST['disc_limit']='';
					$_REQUEST["allow_mprice"] = '';
					$_REQUEST['ic_no'] = '';
					log_br($newid, 'USER PROFILE', $sessioninfo['id'], 'Account created by ' . $sessioninfo['u']);
					log_br($sessioninfo['id'], 'USER PROFILE', $newid, 'Create account ' . ms($u));
					// todo: add approval request
					
					$con->sql_commit();
				}
			}
			break;


		// user id selection
		case 'u' :
			$id = intval($_REQUEST['user_id']);
			show_profile($id);
			exit;

		// user activation
		case 'k' :
			$id = intval($_REQUEST['user_id']);
			$con->sql_query("update user set last_update = CURRENT_TIMESTAMP, active=1 where id=$id");
			if ($con->sql_affectedrows())
			{
				$con->sql_query("select u from user where id=$id") or die(mysql_error());
				$u = $con->sql_fetchfield(0);
				log_br($id, 'USER PROFILE', $sessioninfo['id'], 'Account activated by ' . $sessioninfo['u']);
			        log_br($sessioninfo['id'], 'USER PROFILE', $id, 'Activate account ' . ms($u));
				$alert = $LANG['USERS_PROFILE_ACTIVATED'];
				
				// reset retry counter, but don update lastlogin
				$con->sql_query("update user_status set retry=0,lastlogin=lastlogin where user_id=$id");
				$con->sql_freeresult();
				
				// if user is from eform and is approve, activate account
				$con->sql_query("select id from eform_user where status=1 and actual_user_id=$id");
				$eform_id = $con->sql_fetchfield(0);
				if ($eform_id)
				{
					$con->sql_query("update eform_user set status=3,last_update = CURRENT_TIMESTAMP, activated_by=".$sessioninfo['id']." where id=$eform_id");
				}
				$con->sql_freeresult();
			}
			show_profile($id, $alert);
			exit;

		// psw reset
		case 'r' :
			$id = intval($_REQUEST['user_id']);
			$con->sql_query("update user set last_update = CURRENT_TIMESTAMP, l=u, active=1, p=md5(concat(u,'123')) where id=$id");
			if ($con->sql_affectedrows())
			{
				
				$con->sql_query("select u from user where id=$id") or die(mysql_error());
				$u = $con->sql_fetchfield(0);
				log_br($id, 'USER PROFILE', $sessioninfo['id'], 'Password reset by ' . $sessioninfo['u']);
				log_br($sessioninfo['id'], 'USER PROFILE', $id, 'Reset password ' . ms($u));
				$alert = $LANG['USERS_PASSWORD_RESET'];
			}
			show_profile($id, $alert);
			exit;

		// user deactivation
		case 'd' :
			$id = intval($_REQUEST['user_id']);
			$con->sql_query("update user set last_update = CURRENT_TIMESTAMP, active=0 where id=$id");
			if ($con->sql_affectedrows())
			{

				$con->sql_query("select u from user where id=$id") or die(mysql_error());
				$u = $con->sql_fetchfield(0);
				log_br($id, 'USER PROFILE', $sessioninfo['id'], 'Account de-activated by ' . $sessioninfo['u']);
				log_br($sessioninfo['id'], 'USER PROFILE', $id, 'De-activate account ' . ms($u));
				$alert = $LANG['USERS_PROFILE_DEACTIVATED'];
			}
			show_profile($id, $alert);
			exit;

		// update profile
		case 'm' :
			$id = intval($_REQUEST['user_id']);
			if (!$_REQUEST['template'])
			{
				// save personal info
				$fnb_username = strval($_REQUEST['fnb_username']);
				$barcode = strval($_REQUEST['barcode']);
				$p = strval($_REQUEST['newpassword']);
				$l = strval($_REQUEST['newlogin']);
				$fn = strval($_REQUEST['fullname']);
				$e = strtolower(strval($_REQUEST['newemail']));
				$bid= intval($_REQUEST['default_branch_id']);
				$lvl = intval($_REQUEST['level']);
				$dept = $_REQUEST['departments'];
				$vendors = $_REQUEST['vendors'];
				$brands = $_REQUEST['brands'];
				$disc_limit = $_REQUEST['disc_limit'];
				$ic_no = trim($_REQUEST['ic_no']);
				if($config['consignment_modules'] && $config['masterfile_branch_region']) $regions = $_REQUEST['regions'];
				
				// check inputs
				if ($p != '' && (strlen($p) < $MIN_PASSWORD_LENGTH || !(preg_match("/[0-9]/i", $p) && preg_match("/[a-z]/i", $p))))
				{
					$errmsg['a'][] = $LANG['USERS_INVALID_NEW_PASSWORD_PATTERN'];
				}

				if ($e == '')
				{
					//$errmsg['a'][] = $LANG['USERS_INVALID_NEW_EMAIL_EMPTY'];
				}
				elseif (!preg_match(EMAIL_REGEX, $e))
				{
					$errmsg['a'][] = sprintf($LANG['USERS_INVALID_NEW_EMAIL_PATTERN'], $e);
				}
				
				$con->sql_query("select * from user where l=".ms($l)." and id <> $id");
				if ($con->sql_numrows()>0)
				{
					$errmsg['a'][] = sprintf($LANG['USERS_INVALID_LOGIN_ID'], $l);
				}
				
				if ($barcode) {
					$con->sql_query("select id from user where barcode=".ms($barcode)." and id <> $id limit 1");
					if ($con->sql_numrows()>0)
					{
						$errmsg['a'][] = "Login Barcode $barcode already existed";
					}
				}
				
				//check duplicate fnb_username
				if($fnb_username !='' && $config['enable_suite_device']){
					$con->sql_query("select fnb_username from user where fnb_username=".ms($fnb_username)." and id <> $id limit 1");
					if ($con->sql_numrows()>0){
						$errmsg['a'][] = "Fnb Username $fnb_username already existed";
					}
					$con->sql_freeresult();
				}
			
			    // got config to check nric
			    if($config['user_profile_need_ic']){
			        if(!$ic_no){    // user no key in ic
                        $errmsg['a'][] = $LANG['USERS_INVALID_IC_EMPTY'];
					}else{
                        $con->sql_query("select id, u from user where ic_no=".ms($ic_no)." and id<>$id");
                        $tmp = $con->sql_fetchrow();
						if($tmp){  // IC already used
	                        $errmsg['a'][] = sprintf($LANG['USERS_IC_ALREADY_USED'], $ic_no, $tmp['u']);
						}
						$con->sql_freeresult();
					}
				}
				
				if($config['reserve_login_id'] && $sessioninfo['id']!= 1){
					$q1 = $con->sql_query("select l from user where id=$id");
					$u = $con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);
					foreach($config['reserve_login_id'] as $keys=>$reserve_word){
						$len = strlen($reserve_word);
						if(substr(strtolower($u['l']), 0, $len) != strtolower($reserve_word)){
							if(substr(strtolower($l), 0, $len) == strtolower($reserve_word)){
								$errmsg['a'][] = 'The Login ID is not allow to start with "'.$reserve_word.'".';
							}
						}
					}
				}
			}

			if ($errmsg['a'])
			{
				header('Temporary-Header: True', true, 404);
				die(implode("\n",$errmsg['a']));
			}
			else
			{
				$con->sql_begin_transaction();
				
				// update password if changed
				if (!$_REQUEST['template'])
				{
					if ($p != '') $con->sql_query("update user set last_update = CURRENT_TIMESTAMP, p = md5(".ms($p).") where id = $id");

					// update the rest of info
					$upd = array();
					if($config['enable_suite_device']) $upd['fnb_username'] = $_REQUEST['fnb_username'];
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$upd['barcode'] = $barcode;
					$upd['l'] = $l;
					$upd['fullname'] = $fn;
					$upd['email'] = $e;
					$upd['default_branch_id'] = $bid;
					$upd['level'] = $lvl;
					$upd['departments'] = serialize($dept);
					$upd['vendors'] = serialize($vendors);
					$upd['brands'] = serialize($brands);
					$upd['position'] = $_REQUEST['position'];
					$upd['user_dept'] = $_REQUEST['user_dept'];
					$upd['discount_limit'] = $disc_limit;
					$upd['allow_mprice'] = serialize($_REQUEST['allow_mprice']);
					$upd['phone_1'] = trim($_REQUEST['phone_1']);
					if($config['notification_send_sms']) $upd['sms_notification'] = $_REQUEST['sms_notification'];
					if($config['user_profile_need_ic']) $upd['ic_no'] = $ic_no;
					if($config['consignment_modules']  && $config['masterfile_branch_region']) $upd['regions'] = serialize($regions);
					if($config['user_profile_show_item_discount_only_allow_percent'])	$upd['item_disc_only_allow_percent'] = mi($_REQUEST['item_disc_only_allow_percent']);
					
					$con->sql_query("update user set ".mysql_update_by_field($upd)." where id = $id");
				}
				// save privileges
				if (BRANCH_CODE == 'HQ')
					$con->sql_query("delete from user_privilege where user_id = $id");
				else
					$con->sql_query("delete from user_privilege where user_id = $id and branch_id = $sessioninfo[branch_id]");
				if ($_REQUEST['user_privilege'])
				{
					foreach ($_REQUEST['user_privilege'] as $k=>$v)
					{
						foreach ($_REQUEST['user_privilege'][$k] as $k2=>$v)
						{
							$con->sql_query("insert into user_privilege values ($id, " . ms($k) . ", " . ms($k2) . ", " . mb($_REQUEST['user_privilege'][$k][$k2]) . ")");
						}
					}
				}

				$changes = "";
				foreach (preg_split("/\|/", $_REQUEST["changed_fields"]) as $ff)
				{
					// keep array
					if ($ff != "") $uqf[$ff] = 1;
				}
				if ($uqf) $changes .= "\nEdited fields: (" . join(", ", array_keys($uqf)) . ")";
				$con->sql_query("select u from user where id=$id") or die(mysql_error());
				$u = $con->sql_fetchfield(0);
				
			    log_br($id, 'USER PROFILE', $sessioninfo['id'], 'Account profile updated by ' . $sessioninfo[u] . $changes);
				log_br($sessioninfo['id'], 'USER PROFILE', $id, 'Update Profile ' . ms($u). $changes);
				
				$con->sql_commit();
				
				$alert = $LANG['USERS_PROFILE_UPDATED'];
			}

			show_profile($id, $alert);
			exit;

		// copy profile
		case 'c' :
			$id = intval($_REQUEST['user_id']);
			$con->sql_query("delete from user_privilege where user_id = $id");
			// copy privilege
			$con->sql_query("insert into user_privilege select $id, branch_id, privilege_code, allowed from user_privilege where user_id = " . mi($_REQUEST['template_id']));
			$con->sql_query("update user set last_update = CURRENT_TIMESTAMP where id = $id");
			$changes .= "\nCopy from user #$_REQUEST[template_id]";
		/*	$changes = "";
			foreach (preg_split("/\|/", $_REQUEST["changed_fields"]) as $ff)
			{
				// keep array
				if ($ff != "") $uqf[$ff] = 1;
			}
			
		*/
			if ($con->sql_affectedrows())
			{
				log_br($id, 'USER PROFILE', $sessioninfo['id'], 'Account profile updated by ' . $sessioninfo[u] . $changes);
				log_br($sessioninfo['id'], 'USER PROFILE', $id, 'Update Profile ' . ms($u). $changes);
				$alert = $LANG['USERS_PROFILE_UPDATED'];
			}
			show_profile($id, $alert);
			exit;
			
		//lock user
		case 'l':
			$id = intval($_REQUEST['user_id']);
			$con->sql_query("update user set last_update = CURRENT_TIMESTAMP, locked=1 where id=$id");
			if ($con->sql_affectedrows())
			{
				$con->sql_query("select u from user where id=$id") or die(mysql_error());
				$u = $con->sql_fetchfield(0);
				log_br($id, 'USER PROFILE', $sessioninfo['id'], 'Account lock by ' . $sessioninfo['u']);
				log_br($sessioninfo['id'], 'USER PROFILE', $id, 'Locked account ' . ms($u));
				$alert = $LANG['USERS_PROFILE_LOCKED'];
			}
			show_profile($id, $alert);
			exit;
			
		//unlock user
		case 'j':
			$id = intval($_REQUEST['user_id']);
			$con->sql_query("update user set last_update = CURRENT_TIMESTAMP, locked=0 where id=$id");
			if ($con->sql_affectedrows())
			{
				// reset retry counter, but don update lastlogin
				$con->sql_query("update user_status set retry=0,lastlogin=lastlogin where user_id=$id");
				
				$con->sql_query("select u from user where id=$id") or die(mysql_error());
				$u = $con->sql_fetchfield(0);
				log_br($id, 'USER PROFILE', $sessioninfo['id'], 'Account unlock by ' . $sessioninfo['u']);
				log_br($sessioninfo['id'], 'USER PROFILE', $id, 'Unlocked account ' . ms($u));
				$alert = $LANG['USERS_PROFILE_UNLOCKED'];
			}
			show_profile($id, $alert);
			exit;
			
		case 'refresh':
			break;
			
		default:
			print "<h3>Unhandled Request</h3>";
			print_r($_REQUEST);
			exit;

	}
	$smarty->assign("errmsg", $errmsg);
	$smarty->assign("msg", $msg);
}


/*$con->sql_query("select count(id) as count from user where NOT template and active");
$r = $con->sql_fetchrow();
$smarty->assign("active_count", $r['count']);*/
$smarty->assign("MAX_ACTIVE_USER", $MAX_ACTIVE_USER);
if (BRANCH_CODE == 'HQ')
{
		global $config;
		if (!$config['consignment_modules'])
			$con->sql_query("select id, code, description from branch where active");
		else
			$con->sql_query("select id, code, description from branch where code='HQ'");
}
else
	$con->sql_query("select id, code, description from branch where id = $sessioninfo[branch_id]");

$smarty->assign("branches", $con->sql_fetchrowset());
assign_privileges();
$con->sql_query("select c.id, c.description, c.root_id, r.description as root from category c left join category r on c.root_id = r.id where c.level = 2 order by r.description, c.description ");
$smarty->assign("departments", $con->sql_fetchrowset());

$smarty->assign("eform_user", $_REQUEST['eform']);

if ($_REQUEST['t'] == 'create')
{
	if (!privilege('USERS_ADD')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'USERS_ADD',BRANCH_CODE), "/index.php");

	$con->sql_query("select id,description from vendor order by description");
	$smarty->assign("vendors", $con->sql_fetchrowset());

	$con->sql_query("select id,description from brand order by description");
	$smarty->assign("brands", $con->sql_fetchrowset());


	$con->sql_query("select id, u, template from user where template order by template, u");
	$smarty->assign("templates", $con->sql_fetchrowset());

	$smarty->assign("show_add_user", ($r['count'] < $MAX_ACTIVE_USER));

	$smarty->assign("PAGE_TITLE", "Create Profile");
	$smarty->display("users_create.tpl");

}
elseif ($_REQUEST['t'] == 'update')
{
	if (!privilege('USERV PROFILE') && !privilege('USERS_ACTIVATE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'USER PROFILE', BRANCH_CODE), "/index.php");
	
	$where = array();
	if (isset($_REQUEST['status']) && $_REQUEST['status']>=0) $where[] = "user.active=".mi($_REQUEST['status']);
	
	if (!isset($_REQUEST['branch_id'])) $_REQUEST['branch_id']=0;
	
	$bid = get_request_branch(true);
	if ($bid>0)
	{
		$where[] = "user.default_branch_id = $bid";
	}
	
	if($sessioninfo['id'] != 1){
		$where[] = "user.id != 1";
		$where[] = "(user.is_arms_user=0 or user.id=".mi($sessioninfo['id']).")";
	}
		
	if ($where)
		$where = "where ".join(" and ", $where);
	else
		$where = '';

	$con->sql_query("select user.id, u, template, user.active, level, branch.code as branch_code, allow_mprice from (user left join branch on user.default_branch_id = branch.id) $where order by template desc, user.active desc, u");
	$users = $con->sql_fetchrowset();
	foreach($users as $idx=>$u)
	{
		if($u['allow_mprice']=="")
		{
			if($mprice_list)
			{
				foreach($mprice_list as $mpl)
				{
					$user[$idx]['allow_mprice'][$mpl] = 1;
				}
			}
		}
		else
		{
			$user[$idx]['allow_mprice'] = unserialize($u['allow_mprice']);
		}
	}
	$smarty->assign("users", $users);


/*
	$con->sql_query("select id, u, template from user where template");
	$smarty->assign("templates", $con->sql_fetchrowset());
	$con->sql_query("select id, u, template, active from user order by template desc, active desc");
	$smarty->assign("users", $con->sql_fetchrowset());
	$con->sql_query("select code, description from branch where active");
	$smarty->assign("branches", $con->sql_fetchrowset());
	$con->sql_query("select code, description, hq_only from privilege");
	$smarty->assign("privileges", $con->sql_fetchrowset());

	$con->sql_query("select count(id) as count from user where NOT template and active");
	$r = $con->sql_fetchrow();
	$smarty->assign("active_count", $r['count']);
	$smarty->assign("MAX_ACTIVE_USER", $MAX_ACTIVE_USER);
	$smarty->assign("show_add_user", ($r['count'] < $MAX_ACTIVE_USER));
*/
	$smarty->assign("PAGE_TITLE", "Update Profile");
	$smarty->display("users_update.tpl");

}
else
{
	header("Location: /index.php");
}

// render user profile and privilege table
function show_profile($id, $alert = '')
{
	global $con, $smarty, $mprice_list, $config, $sessioninfo, $user_level;

	$con->sql_query("select id, u, template from user where id != $id and is_arms_user=0 order by u");
	$smarty->assign("templates", $con->sql_fetchrowset());

	$con->sql_query("select *,us.lastlogin
	from user
	left join user_status us on us.user_id=user.id
	where user.id = $id order by u");
	$u = $con->sql_fetchrow();
	$u['departments'] = unserialize($u['departments']);
	$u['vendors'] = unserialize($u['vendors']);
	$u['brands'] = unserialize($u['brands']);
	if($config['consignment_modules'] && $config['masterfile_branch_region'] && $u['regions']) $u['regions'] = unserialize($u['regions']);
	if($u['allow_mprice']=="")
	{
		foreach($mprice_list as $mpl)
		{
			$u['allow_mprice'][$mpl] = 1;
		}
	}
	else
	{
		$u['allow_mprice'] = unserialize($u['allow_mprice']);
	}

	$smarty->assign("user", $u);
	if (BRANCH_CODE == 'HQ')
	{
		global $config;
		if (!$config['consignment_modules'])
			$con->sql_query("select id, code, description from branch where active");
		else
			$con->sql_query("select id, code, description from branch where code='HQ'");
	}
	else
		$con->sql_query("select id, code, description from branch where code = ".ms(BRANCH_CODE));
	$smarty->assign("branches", $con->sql_fetchrowset());
	assign_privileges();
	$con->sql_query("select c.id, c.description, c.root_id, r.description as root from category c left join category r on c.root_id = r.id where c.level = 2 order by r.description, c.description ");
	$smarty->assign("departments", $con->sql_fetchrowset());

	$con->sql_query("select id,description from vendor order by description");
	$smarty->assign("vendors", $con->sql_fetchrowset());

	$con->sql_query("select id,description from brand order by description");
	$smarty->assign("brands", $con->sql_fetchrowset());

	$con->sql_query("select * from user_privilege where user_id = $id");
	$user_privilege = array();
	while ($r = $con->sql_fetchrow())
	{
		$user_privilege[$r['branch_id']][$r['privilege_code']] = $r['allowed'];
	}
	$smarty->assign("user_privilege", $user_privilege);
	$smarty->assign("can_edit_level", ($sessioninfo['level']>=1000));
	$user_level_label = array_search($u['level'],$user_level);
	$smarty->assign("user_level_label", $user_level_label);
	$smarty->assign("eform_user", $_REQUEST['eform']);

	$smarty->display("user_profile.tpl");
	if ($alert)
	{
		print "<script>alert('$alert');window.scrollTo(0,0);</script>";
	}
}

function assign_privileges()
{
	global $con, $smarty, $privilege_groupname, $privilege_prefix_group, $appCore, $config;

	/*$groupname = array(
		'ADJ' => 'Adjustment',
		'CI' => 'Consignment',
		'CON' => 'Consignment',
		'DO' => 'Delivery Order',
		'GRA' => 'Goods Return Advice',
		'GRN' => 'Goods Receiving',
		'GRR' => 'Goods Receiving',
		'MEMBERSHIP' => 'Membership',
		'RPT_MEMBERSHIP' => 'Membership',
		'POS' => 'POS / Front-end / Promotions',
		'PROMOTION' => 'POS / Front-end / Promotions',
		'PO' => 'Purchase Order',
		'FRONTEND' => 'POS / Front-end / Promotions',
		'USERS'=>'User Management',
		'MST'=>'Masterfile',
		'MASTERFILE'=>'Masterfile',
		'PIVOT'=>'Reporting',
		'REPORTS'=>'Reporting',
		'MKT'=>'Marketing Tool',
		'PAYMENT_VOUCHER'=>'Payment Voucher / Shift Record',
		'SHIFT_RECORD'=>'Payment Voucher / Shift Record',
		'CC'=>'Counter Collection',
		'SOP'=>'SOP',
		'SO'=>'Sales Order',
		'FM' => 'Fresh Market'
	);*/
	
	$tmp_privilege["Others"] = array();
	$con->sql_query("select code, description, hq_only from privilege order by code");
	while($r=$con->sql_fetchrow())
	{
		if((!isset($config['pos_settings_pos_backend_tab']) || !$config['pos_settings_pos_backend_tab']) && $r['code'] == 'POS_BACKEND_PROCESS'){
			continue;
		}
		// config.foreign_currency
		if(preg_match("/^ADMIN_FOREIGN_CURRENCY/", $r['code']) && !$config['foreign_currency'])	continue;	// skip foreign currency privilege if no config
		
		if(preg_match("/^SUITE_/", $r['code']) && !$config['enable_suite_device'])	continue;	// skip SUITE privilege if no config
		
		if(preg_match("/^ARMS_ACCOUNTING_/", $r['code']) && !$config['arms_accounting_api_setting'])	continue;	// skip ARMS Accounting privilege if no config
		
		if(preg_match("/^OSTRIO_/", $r['code']) && !$config['os_trio_settings'])	continue;	// skip OS Trio Accounting privilege if no config
		
		if(preg_match("/^PROMOTION_MEMBER_MOBILE/", $r['code']) && !$config['membership_mobile_settings'])	continue;	// skip Membership Mobile Privilege if no config
		
		if(preg_match("/^MEMBERSHIP_MOBILE/", $r['code']) && !$config['membership_mobile_settings'])	continue;	// skip Membership Mobile Privilege if no config
		
		if(preg_match("/^SPEED99/", $r['code']) && !$config['speed99_settings'])	continue;	// skip Speed99 Privilege if no config
		
		if (preg_match("/^(".join("|",array_keys($privilege_prefix_group)).")/", $r['code'],$match))
		{
			
			$grp = $privilege_prefix_group[$match[1]];
		}
		else
		{
			$grp = "Others";
		}
		if(in_array($r['code'], $appCore->userManager->removedPrivilegeList))	continue;	// this code already removed

		$tmp_privilege[$grp][] = $r;
	}
	
	// reconstruct to follow privilege group sequence
	$privileges = array();
	$privileges['Others'] = $tmp_privilege['Others'];	// first row for others
	foreach($privilege_groupname as $grp=>$grp_name){
		if (count($tmp_privilege[$grp])>0)	$privileges[$grp] = $tmp_privilege[$grp];
		else	unset($privilege_groupname[$grp]);
	}
	unset($tmp_privilege);
	$smarty->assign("privileges", $privileges);
	$smarty->assign('privilege_prefix_group', $privilege_prefix_group);
	$smarty->assign('privilege_groupname', $privilege_groupname);
	//print_r($privileges);
}
?>
