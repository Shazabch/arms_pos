<?php
/*
9/8/2011 11:34:13 AM Andy
- Add copy privilege feature.

10/06/2011 11:32:35 AM Kee Kee
- Add Mprice privilege for each user

2/14/2012 5:46:33 PM Alex
- change check privilege group name from 'POS' => 'FRONTEND'

5/14/2013 3:31 PM Andy
- Fix if no config to turn on IC, it will generate error when user try to approve new cashier.

6/21/2013 2:00 PM Andy
- Add control to limit user can only use discount by percent for item discount. (need config user_profile_show_item_discount_only_allow_percent).

5/27/2014 11:19 AM Justin
- Bug fixed on showing wrong error message while user provides invalid username.

9/7/2016 6:05 PM Andy
- Hide "POS_RETURN_POLICY" privilege

4/18/2017 11:33 AM Qiu Ying
- Enhanced to add a remark in Cashier Setup

7/25/2017 4:46 PM Justin
- Enhanced to use email regular expression checking from global settings.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

10/29/2019 4:25 PM William
- Enhanced to block username and login id begin word same as config "reserve_login_id" value.

11/4/2019 3:52 PM William
- Fix bug username, ic, login_id not check duplicate value when save as draft.

2/11/2020 11:38 AM William
- Enhanced to added new column "User Department".

2/11/2020 3:13 PM Andy
- Increased maintenance check to v442.

9/4/2020 10:34 AM Andy
- Added sql_begin_transaction adn sql_commit for add / update user.

4/8/2021 2:01 PM Shane
- Hide "POS_BACKEND_PROCESS" privilege if "pos_settings_pos_backend_tab" is off. 
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('FRONTEND_SET_CASHIER')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'FRONTEND_SET_CASHIER', BRANCH_CODE), "/index.php");
$maintenance->check(442);

class CASHIER_SETUP extends Module{
	var $branches = array();
	var $users = array();
	var $tmp_users = array();
	var $approve_draft_cashier_lv = 500;
	var $mprice_list = array();
	function __construct($title){
		global $con, $smarty, $sessioninfo,$config;

		
		$this->init_selection();   
	    $smarty->assign('approve_draft_cashier_lv', $this->approve_draft_cashier_lv);
		parent::__construct($title);
	}
	
	
	function _default(){
		// load cashier list
		$this->load_cashier_list(true);
		$this->display();
	}
	
	private function init_selection(){
	    global $con, $smarty, $config, $sessioninfo;
	    	
		$con->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		foreach($config['sku_multiple_selling_price'] as $mprice)
		{
			if(preg_match("/^(member)|(member[0-9])$/i",$mprice)==false)
			{
				$this->mprice_list[] = $mprice;
			}
		}
		$uid = mi($_REQUEST['uid']);
		$bid = mi($_REQUEST['branch_id']);
		$is_tmp = mi($_REQUEST['is_tmp']);
		
		if($uid > 0){
			$tbl = $is_tmp ? 'user_draft' : 'user'; 
			$filter = array();
			$filter[] = "id=$uid and level=1 and template=0";
			if(BRANCH_CODE != 'HQ')	$filter[] = "default_branch_id=".mi($sessioninfo['branch_id']);
			if($is_tmp)	$filter[] = "branch_id=$bid";
			
			$filter = "where ".join(' and ', $filter);
			$sql = "select l from $tbl $filter limit 1";
			
			$con->sql_query($sql);
			$form = $con->sql_fetchassoc();
			$con->sql_freeresult();
		}
		$reserve_val = array();
		if($config['reserve_login_id'] && $sessioninfo['id']!= 1){
			foreach($config['reserve_login_id'] as $keys=>$reserve_word){
				$len = strlen($reserve_word);
				if(substr(strtolower($form['l']), 0, $len) != strtolower($reserve_word)){
					$reserve_val[] = $reserve_word;
				}
			}
		}
		$smarty->assign("reserve_val",json_encode($reserve_val));
		$smarty->assign("mprice_list",$this->mprice_list);
		$smarty->assign("total_mprice",count($this->mprice_list));
	}
	
	function check_and_connect_hq(){
		global $hqcon;
		
		if(!$hqcon)	$hqcon = connect_hq();
	}
	
	function load_cashier_list($sqlonly = false){
		global $con, $smarty, $sessioninfo, $hqcon, $config;
		
		$t = mi($_REQUEST['t']);
		$str = replace_special_char(trim($_REQUEST['search_str']));
		
		if(BRANCH_CODE != 'HQ')	$bid = mi($sessioninfo['branch_id']);
		else{
			if($_REQUEST['branch_id'])	$bid = mi($_REQUEST['branch_id']);
		}
		
		if($t == -1){	// search
			if(!$str){
				if($sqlonly)	return false;
				
				die('Cannot search empty string.');
			}
		}
		
		// load current cashier
		if(!$t || $t == 1 || $t == -1){
			$filter = array();
			if($bid)	$filter[] = "user.default_branch_id=$bid";
			$filter[] = "user.level=1 and user.template=0";
			if($t==-1 && $str){
				$filter_or = array();
				$filter_or[] = "user.u like ".ms('%'.$str.'%')." or user.l like ".ms('%'.$str.'%')." or user.fullname like ".ms('%'.$str.'%')." or user.email like ".ms('%'.$str.'%');
				
				if($config['user_profile_need_ic']){
					$filter_or[] = "user.ic_no like ".ms('%'.$str.'%');
				}
				$filter[] = "(".join(' or ', $filter_or).")";
			}
			
			$filter = "where ".join(' and ', $filter);
			
			$sql = "select user.* from user $filter order by user.u";
			
			$q1 = $con->sql_query($sql);
			while($r = $con->sql_fetchassoc($q1)){
				if($r['allow_mprice']=="")
				{
					foreach($this->mprice_list as $mpl)
					{
						$r['allow_mprice'][$mpl] = 1;
					}
				}
				else
				{
					$r['allow_mprice'] = unserialize($r['allow_mprice']);
				}
				$this->users[$r['id']] = $r;
			}
			$con->sql_freeresult($q1);
			$smarty->assign('users', $this->users);
			if(BRANCH_CODE=='HQ')	$smarty->assign('show_filter_branch', 1);
		}
		
		// load tmp cashier
		if($t == -1 || $t == 2 || $t == 3){
			$filter = array();
			$filter[] = "ud.level=1";
			if($t==3){
				$filter[] = "ud.active=1";	// waiting approval
				$smarty->assign("status", "waiting approval");
			}elseif($t == 2)	$filter[] = "ud.active=0";	// draft
			if(BRANCH_CODE != 'HQ')	$filter[] = "ud.default_branch_id=".mi($sessioninfo['branch_id']);
			
			if($t==-1 && $str){
				$filter_or = array();
				$filter_or[] = "ud.u like ".ms('%'.$str.'%')." or ud.l like ".ms('%'.$str.'%')." or ud.fullname like ".ms('%'.$str.'%')." or ud.email like ".ms('%'.$str.'%');
				
				if($config['user_profile_need_ic']){
					$filter_or[] = "ud.ic_no like ".ms('%'.$str.'%');
				}
				$filter[] = "(".join(' or ', $filter_or).")";
			}
			
			$filter = "where ".join(' and ', $filter);
			$sql = "select ud.* from user_draft ud $filter order by ud.id";
			//print $sql;
			$q1 = $con->sql_query($sql);
			while($r = $con->sql_fetchassoc($q1)){
				$r['is_tmp'] = 1;
				if($r['allow_mprice']=="")
				{
					foreach($this->mprice_list as $mpl)
					{
						$r['allow_mprice'][$mpl] = 1;
					}
				}
				else
				{
					$r['allow_mprice'] = unserialize($r['allow_mprice']);
				}
				$this->tmp_users[$r['id']] = $r;
			}
			$con->sql_freeresult($q1);
			$smarty->assign('tmp_users', $this->tmp_users);
		}
		
		
		if(!$sqlonly){
			$this->display('front_end.cashier_setup.list.tpl');
		}
	}
	
	function ajax_update_user_status(){
		global $con, $smarty, $sessioninfo, $LANG;
			
		//print_r($_REQUEST);
		
		$uid = mi($_REQUEST['uid']);
		$status = mi($_REQUEST['status']);
		$is_tmp = mi($_REQUEST['is_tmp']);
		$tbl = $is_tmp ? 'user_draft' : 'user'; 
		$bid = mi($_REQUEST['branch_id']);
		
		$filter = array();
		$filter[] = "id=$uid and level=1 and template=0";
		if(BRANCH_CODE != 'HQ'){
			$filter[] = "default_branch_id=".mi($sessioninfo['branch_id']);
		}
		if($is_tmp){
			$filter[] = "branch_id=$bid";
		}
		
		$filter = "where ".join(' and ', $filter);
		
		$con->sql_query("select * from $tbl $filter limit 1");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$ret = array();
		if($form){
			$con->sql_query("update $tbl set active=$status $filter limit 1");

			if(!$con->sql_affectedrows()){
				$ret['failed_reason'] = $LANG['NO_CHANGES_MADE'];
			}else{
				$ret['ok'] = 1;
				log_br($sessioninfo['id'], 'Cashier Setup', $uid, ($status?'Activate':'Deactivate')." ".($is_tmp?'Draft':'')." Cashier: ".$form['u']);
			}
		}else{
			$ret['failed_reason'] = $LANG['USERS_NOT_FOUND'];
		}
		
		
		print json_encode($ret);
	}
	
	function view(){
		$this->open(true);	// open by readonly mode
	}
	
	function open($readonly = false){
		global $con, $smarty, $sessioninfo, $hqcon, $config, $LANG, $privileges;
		
		$uid = mi($_REQUEST['uid']);
		$bid = mi($_REQUEST['branch_id']);
		$is_tmp = mi($_REQUEST['is_tmp']);
		
		if($uid > 0){
			$tbl = $is_tmp ? 'user_draft' : 'user'; 
			$filter = array();
			$filter[] = "id=$uid and level=1 and template=0";
			if(BRANCH_CODE != 'HQ')	$filter[] = "default_branch_id=".mi($sessioninfo['branch_id']);
			if($is_tmp)	$filter[] = "branch_id=$bid";
			
			$filter = "where ".join(' and ', $filter);
			$sql = "select * from $tbl $filter limit 1";
			
			$con->sql_query($sql);
			$form = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if (!$form) js_redirect($LANG['USERS_NOT_FOUND'], $_SERVER['PHP_SELF']);
			
			if($is_tmp){
				$form['user_privilege'] = unserialize($form['tmp_user_privilege']);
			}else{
				$con->sql_query("select * from user_privilege where user_id = $uid and privilege_code like 'POS%'");
				$form['user_privilege'] = array();
				while ($r = $con->sql_fetchassoc())
				{
					$form['user_privilege'][$r['branch_id']][$r['privilege_code']] = $r['allowed'];
				}
				$con->sql_freeresult();
			}
			
			if($form['allow_mprice']=="")
			{
				foreach($this->mprice_list as $mp)
				{
					$form['allow_mprice'][$mp]=1;
				}
			}
			else
			{
				$form['allow_mprice'] = unserialize($form['allow_mprice']);
			}
			
		}
		
		if($is_tmp || $uid<=0)	$form['is_tmp'] = 1;
		
		$this->assign_privileges();
		
		if($is_tmp && $form['active'] && !$readonly){	// already confirm draft, cannot edit
			header("Location: $_SERVER[PHP_SELF]?a=view&uid=$uid&is_tmp=1&branch_id=$bid");
			exit;
		}
		
		// load template/user
		if(!$readonly){
			$this->load_template_user();
		}
		$smarty->assign('readonly', $readonly);
		$smarty->assign('form', $form);
		$this->display('front_end.cashier_setup.open.tpl');
	}
	
	private function load_template_user(){
		global $con, $smarty;
		
		$con->sql_query("select id,u,template from user where template=1 or (level=1 ".(BRANCH_CODE != 'HQ'?' and default_branch_id='.mi($sessioninfo['branch_id']):'').") order by template desc, u");
		$templates_user = array();
		while($r = $con->sql_fetchassoc()){
			$templates_user[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('templates_user', $templates_user);
	}
	
	private function assign_privileges(){
		global $con, $smarty, $privilege_groupname, $privilege_prefix_group, $appCore, $config;
		
		$con->sql_query("select code, description, hq_only from privilege where code like 'POS%' order by code");
		while($r=$con->sql_fetchassoc())
		{
			if(BRANCH_CODE != 'HQ' && $r['hq_only'])	continue;	// skip hq_only privilege for branches
			
			if((!isset($config['pos_settings_pos_backend_tab']) || !$config['pos_settings_pos_backend_tab']) && $r['code'] == 'POS_BACKEND_PROCESS'){
				continue;
			}

			//check privilege group
			if (preg_match("/^(".join("|",array_keys($privilege_prefix_group)).")/", $r['code'],$match))
			{
				$grp = $privilege_prefix_group[$match[1]];
			}
			else
			{
				$grp = "Others";
			}
			
			if($grp!='FRONTEND')	continue;	// only get POS privilege
			
			if(in_array($r['code'], $appCore->userManager->removedPrivilegeList))	continue;	// this code already removed
			
			$privilege_list[] = $r;
		}
		$con->sql_freeresult();
		
		//print_r($privilege_list);
		$smarty->assign("privilege_list", $privilege_list);
		$smarty->assign('privilege_groupname', $privilege_groupname);
		
	}
	
	private function validate_data(&$data){
		global $con, $hqcon, $LANG, $MIN_USERNAME_LENGTH, $MIN_PASSWORD_LENGTH, $MAX_ACTIVE_USER, $config, $sessioninfo;
		$err = array();
		
		$this->check_and_connect_hq();	// check hq connection	
		
		$uid = mi($data['id']);
		$bid = mi($data['branch_id']);
		$is_tmp = mi($data['is_tmp']);
		$tbl = $is_tmp ? 'user_draft' : 'user'; 
		$check_p = false;
		
		$data['newpassword'] = trim($data['newpassword']);
		$data['newpassword2'] = trim($data['newpassword2']);
		$data['email'] = trim($data['email']);
		$data['l'] = trim($data['l']);
		$data['ic_no'] = trim($data['ic_no']);
		
		if($uid){	// got user id
			// check this user is exists or not, or can update or not
			$filter = array();
			$filter[] = "id=$uid and level=1 and template=0";
			if(BRANCH_CODE != 'HQ'){
				$filter[] = "default_branch_id=".mi($sessioninfo['branch_id']);
			}
			if($is_tmp)	$filter[] = "branch_id=$bid";
			
			$filter = 'where '.join(' and ', $filter);
			
			$sql = "select * from $tbl $filter limit 1";
			$con->sql_query($sql);
			$user = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$user){
				$err[] = $LANG['USERS_NOT_FOUND'];
				return $err;
			}
			
			if($data['newpassword'] || $data['newpassword2'])	$check_p = true;
			
		}else{	// dont hv user id
			$data['u'] = trim($data['u']);
			if(!$data['u'])	$err[] = $LANG['USERS_INVALID_NEW_USERNAME_EMPTY'];
			elseif (strlen($data['u']) < $MIN_USERNAME_LENGTH){
				$err[] = "Username length must be minimum $MIN_USERNAME_LENGTH";
			}elseif(!preg_match("/^[a-z0-9_]+$/i", $data['u'])){
				$err[] = sprintf($LANG['USERS_INVALID_NEW_USERNAME_PATTERN'], $data['u']);
			}elseif($config['reserve_login_id'] && $sessioninfo['id']!= 1){
				foreach($config['reserve_login_id'] as $keys=>$reserve_word){
					$len = strlen($reserve_word);
					if(substr(strtolower($data['u']), 0, $len) == strtolower($reserve_word)){
						$err[] = 'The Username is not allow to start with "'.$reserve_word.'".';
					}
				}
			}else{
				$hqcon->sql_query("select u from $tbl where u = ".ms($data['u']));
				if ($hqcon->sql_numrows() > 0)
				{
					$err[] = sprintf($LANG['USERS_INVALID_NEW_USERNAME_USED'], $data['u']);
				}
				$hqcon->sql_freeresult();
			}
			
			$check_p = true;
		}
		
		// check password
		if($check_p){
			if(!$data['newpassword'] || !$data['newpassword2'])	$err[] = $LANG['USERS_INVALID_NEW_PASSWORD_EMPTY'];
			else{
				if($data['newpassword'] != $data['newpassword2'])	$err[] = $LANG['USERS_PASSWORD_DIFF'];
				elseif (strlen($data['newpassword']) < $MIN_PASSWORD_LENGTH){
					$err[] = "password length must be minimum $MIN_PASSWORD_LENGTH";
				}elseif(!(preg_match("/[0-9]/i", $data['newpassword'])) && preg_match("/[a-z]/i", $data['newpassword'])){
					$err[] = $LANG['USERS_INVALID_NEW_PASSWORD_PATTERN'];
				}
			}
			
		}
		
		// location
		if(BRANCH_CODE == 'HQ'){
			if(!$data['default_branch_id'])	$err[] = "Please select location.";
		}
		
		// check email
		if (!$data['email'])
		{
			$err[] = $LANG['USERS_INVALID_NEW_EMAIL_EMPTY'];
		}
		elseif(!preg_match(EMAIL_REGEX, $data['email']))
		{
			$err[] = sprintf($LANG['USERS_INVALID_NEW_EMAIL_PATTERN'], $data['email']);
		}
	
		// check login ID
		if(!$data['l']){
			$err[] = "Please key in Login ID.";
		}else{
			$hqcon->sql_query("select * from $tbl where l=".ms($data['l'])." and id<>$uid");
			if ($hqcon->sql_numrows()>0){
				$err[] = sprintf($LANG['USERS_INVALID_LOGIN_ID'], $data['l']);
			}
			$hqcon->sql_freeresult();
			
			//check begin word of login id from config, skip checking saved login id begin word.
			if($config['reserve_login_id'] && $sessioninfo['id']!= 1){
				$q1 = $con->sql_query("select l from $tbl where id=$uid");
				$u_info = $con->sql_fetchassoc($q1);
				$con->sql_fetchassoc($q1);
				$reserve_val = $config['reserve_login_id'];
				foreach($config['reserve_login_id'] as $keys=>$reserve_word){
					$len = strlen($reserve_word);
					if($u_info['l'] && substr(strtolower($u_info['l']), 0, $len) != strtolower($reserve_word)){
						if(substr(strtolower($data['l']), 0, $len) == strtolower($reserve_word)){
							$err[] = 'The Login ID is not allow to start with "'.$reserve_word.'".';
						}
					}
				}
			}
		}
		
		// got config to check nric
	    if($config['user_profile_need_ic']){
	        if(!$data['ic_no']){    // user no key in ic
                    $err[] = $LANG['USERS_INVALID_IC_EMPTY'];
			}else{
                $hqcon->sql_query("select id, u from $tbl where ic_no=".ms($data['ic_no'])." and id<>$uid");
                $tmp = $hqcon->sql_fetchassoc();
				if($tmp){  // IC already used
                      $err[] = sprintf($LANG['USERS_IC_ALREADY_USED'], $data['ic_no'], $tmp['u']);
				}
				$hqcon->sql_freeresult();
			}
		}	
		
		if(!$err){
			// check user limit
			if(!$uid){	// only new user need to check
				if(!$this->check_allow_add_user()){
					$err[] = sprintf($LANG['USERS_INVALID_CANT_ADD_MAX_USER_ALLOWED'], $MAX_ACTIVE_USER);
				}
			}
		}
	
		return $err;
	}
	
	private function check_allow_add_user(){
		global $hqcon, $MAX_ACTIVE_USER;
		
		$this->check_and_connect_hq();	// check hq connection
		
		$hqcon->sql_query("select count(id) as count from user where template=0 and active=1");
		$r = $hqcon->sql_fetchassoc();
		$hqcon->sql_freeresult();
		
		if ($r['count'] >= $MAX_ACTIVE_USER)
		{
			return false;
		}
		return true;
	}
	
	function save_cashier(){
		global $con, $smarty, $sessioninfo, $hqcon, $config, $LANG;
		
		//print_r($_REQUEST);
		
		$form = $_REQUEST;
		$uid = mi($form['id']);
		$upd = array();
		$err = $this->validate_data($form);
		
		if($err){
			$this->assign_privileges();
			$this->load_template_user();
			$smarty->assign('err', $err);
			$smarty->assign('form', $form);
			$this->display('front_end.cashier_setup.open.tpl');
			return;
		}
		//$this->check_and_connect_hq();	// check hq connection	
		
		$upd = array();
		if($config['user_profile_need_ic'])	$upd['ic_no'] = $form['ic_no'];
		$upd['barcode'] = $form['barcode'];
		$upd['fullname'] = $form['fullname'];
		if(BRANCH_CODE == 'HQ')	$upd['default_branch_id'] = $form['default_branch_id'];
		else	$upd['default_branch_id'] = $sessioninfo['branch_id'];
		$upd['position'] = $form['position'];
		$upd['user_dept'] = $form['user_dept'];
		$upd['l'] = $form['l'];
		$upd['email'] = $form['email'];
		$upd['discount_limit'] = $form['discount_limit'];
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['allow_mprice'] = serialize($form['allow_mprice']);
		if($config['user_profile_show_item_discount_only_allow_percent'])	$upd['item_disc_only_allow_percent'] = mi($form['item_disc_only_allow_percent']);
		
		$con->sql_begin_transaction();
		
		// update user
		$con->sql_query("update user set ".mysql_update_by_field($upd)." where id=$uid");
		
		// update password
		if($form['newpassword']){
			$con->sql_query("update user set p = md5(".ms($form['newpassword']).") where id=$uid");
		}
		
		// update privilege
		if (BRANCH_CODE == 'HQ')
			$con->sql_query("delete from user_privilege where user_id = $uid and privilege_code like 'POS%'");
		else
			$con->sql_query("delete from user_privilege where user_id = $uid and branch_id = $sessioninfo[branch_id] and privilege_code like 'POS%'");
			
		if ($form['user_privilege'])
		{
			$up = array();
			$up['user_id'] = $uid;
			
			foreach ($form['user_privilege'] as $k=>$v)
			{
				if(BRANCH_CODE != 'HQ' && $k != $sessioninfo['branch_id'])	continue;
				$up['branch_id'] = $k;
				
				foreach ($v as $k2=>$v2)
				{
					$up['privilege_code'] = $k2;
					$up['allowed'] = $v2;
					
					$con->sql_query("replace into user_privilege ".mysql_insert_by_field($up));
				}
			}
		}
		
		log_br($sessioninfo['id'], 'Cashier Setup', $uid, "Update Cashier: ".get_user_info_by_colname($uid, 'u'));
				
		$con->sql_commit();
				
		$redirect_url = $_SERVER['PHP_SELF'];
		$title = "Cashier Update";
		$subject = "Cashier ID#$uid Update Successfully";
		display_redir($redirect_url, $title, $subject);
	}
	
	function save_tmp_cashier($is_confirm = false){
		global $con, $smarty, $sessioninfo, $hqcon, $config, $LANG;
		
		//print_r($_REQUEST);exit;	
		
		$form = $_REQUEST;
		$uid = mi($form['id']);
		$bid = mi($form['branch_id']);
		$upd = array();
		$err = $this->validate_data($form);
		
		if(!$err && $uid){	// cannot edit if already confirm
			$old_form = $this->check_tmp_user_exists($bid, $uid);	// check cashier exists or not
		
			if($old_form['active']){
				$redirect_url = $_SERVER['PHP_SELF'];
				$subject= "Cashier already confirm by other people.";
				$title = "Save Draft Cashier $upd[u] Failed";
				display_redir($redirect_url, $title, $subject);
			}
		}
		
		if($err){
			$this->assign_privileges();
			$this->load_template_user();
			$smarty->assign('err', $err);
			$smarty->assign('form', $form);
			$this->display('front_end.cashier_setup.open.tpl');
			return;
		}
		
		$upd = array();
		if($config['user_profile_need_ic'])	$upd['ic_no'] = $form['ic_no'];
		$upd['barcode'] = $form['barcode'];
		$upd['fullname'] = $form['fullname'];
		if(BRANCH_CODE == 'HQ')	$upd['default_branch_id'] = $form['default_branch_id'];
		else	$upd['default_branch_id'] = $sessioninfo['branch_id'];
		$upd['position'] = $form['position'];
		$upd['user_dept'] = $form['user_dept'];
		$upd['l'] = $form['l'];
		$upd['email'] = $form['email'];
		$upd['discount_limit'] = $form['discount_limit'];
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['active'] = $is_confirm ? 1 : 0;
		$upd['allow_mprice'] = serialize($form['allow_mprice']);
		// get back old privilege
		$upd['tmp_user_privilege'] = $old_form['tmp_user_privilege'];
		if($config['user_profile_show_item_discount_only_allow_percent'])	$upd['item_disc_only_allow_percent'] = mi($form['item_disc_only_allow_percent']);
		
		if(BRANCH_CODE == 'HQ')	$upd['tmp_user_privilege'] = $form['user_privilege'];	// replace all privilege
		else{
			// only update own branch privilege
			$upd['tmp_user_privilege'][$sessioninfo['branch_id']] = $form['user_privilege'][$sessioninfo['branch_id']];
		}
		$upd['tmp_user_privilege'] = serialize($upd['tmp_user_privilege']);
		$upd['level'] = 1;
		
		if($uid>0){
			$filter = array();
			$filter[] = "id=$uid and branch_id=$bid";
			if(BRANCH_CODE != 'HQ')	$filter[] = "default_branch_id=".mi($sessioninfo['branch_id']);
			
			$filter = 'where '.join(' and ', $filter);
			// update user
			$con->sql_query("update user_draft set ".mysql_update_by_field($upd)." $filter");
		}else{
			$is_new_user = true;
			// insert user
			$upd['u'] = $form['u'];
			$bid = $upd['branch_id'] = $sessioninfo['branch_id'];
			$con->sql_query("insert into user_draft ".mysql_insert_by_field($upd));
			$uid = $con->sql_nextid();
		}
		
		// update password
		if($form['newpassword']){
			$con->sql_query("update user_draft set p = md5(".ms($form['newpassword']).") where id=$uid and branch_id=$bid");
		}
		
		if($is_new_user){
			log_br($sessioninfo['id'], 'Cashier Setup', $uid, "Add Draft Cashier: ".$form['u']);
		}else{
			log_br($sessioninfo['id'], 'Cashier Setup', $uid, ($is_confirm?'Confirm':'Update')." Draft Cashier: ".$old_form['u']);
		}
		
		
		$redirect_url = $_SERVER['PHP_SELF'];
		$title = "Draft Cashier ".($is_confirm ? 'Confirmed' : 'Saved');
		$subject = "Draft Cashier $upd[u] Update Successfully";
		display_redir($redirect_url, $title, $subject);
	}
	
	function confirm_tmp_cashier(){
		$this->save_tmp_cashier(true);
	}
	
	private function check_tmp_user_exists($bid, $uid, $redirect = true){
		global $con, $sessioninfo, $LANG;
		
		$filter = array();
		$filter[] = "id=".mi($uid)." and branch_id=".mi($bid)." and template=0 and level=1";
		if(BRANCH_CODE != 'HQ')	$filter[] = "default_branch_id=".mi($sessioninfo['branch_id']);
		
		$filter = 'where '.join(' and ', $filter);
		
		$con->sql_query("select * from user_draft $filter");
		$old_form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$old_form && $redirect){
			js_redirect($LANG['USERS_NOT_FOUND'], $_SERVER['PHP_SELF']);
		}
		
		$old_form['tmp_user_privilege'] = unserialize($old_form['tmp_user_privilege']);
		return $old_form;
	}
	
	function delete_tmp_user(){
		global $con, $smarty, $sessioninfo, $hqcon, $config, $LANG;
		
		//print_r($_REQUEST);
		
		$uid = mi($_REQUEST['id']);
		$bid = mi($_REQUEST['branch_id']);
		
		$old_form = $this->check_tmp_user_exists($bid, $uid);	// check cashier exists or not
		
		$con->sql_query("delete from user_draft where id=$uid and branch_id=$bid");
		
		$redirect_url = $_SERVER['PHP_SELF'];
		$subject= "Draft Cashier ($old_form[u]) has been deleted.";
		$title = "Draft Cashier Deleted";
		
		log_br($sessioninfo['id'], 'Cashier Setup', $uid, "Delete Draft Cashier: $old_form[u]");
		
		display_redir($redirect_url, $title, $subject);
	}
	
	function tmp_user_approval(){
		global $con, $smarty, $sessioninfo, $hqcon, $config, $LANG;
		
		//print_r($_REQUEST);
		
		$uid = mi($_REQUEST['id']);
		$bid = mi($_REQUEST['branch_id']);
		$act = trim($_REQUEST['act']);
		$reject_reason = trim($_REQUEST['reject_reason']);
				
		$old_form = $this->check_tmp_user_exists($bid, $uid);	// check cashier exists or not
		
		if($sessioninfo['level'] < $this->approve_draft_cashier_lv)	js_redirect($LANG['USER_LEVEL_NO_REACH'], $_SERVER['PHP_SELF']);
		
		if($act == 'approve'){	// approve
			$this->check_and_connect_hq();	// connect hq
			
			// check again to make sure no duplicate username
			$hqcon->sql_query("select * from user where u=".ms($old_form['u']));
			$tmp = $hqcon->sql_fetchassoc();
			$hqcon->sql_freeresult();
			
			if($tmp)	js_redirect(sprintf($LANG['USERS_INVALID_NEW_USERNAME_USED'],$old_form['u']) , $_SERVER['PHP_SELF']);
			
			$upd = array();
			$upd = $old_form;
			unset($upd['branch_id'], $upd['id'], $upd['tmp_user_privilege'], $upd['reject_reason']);
			if(!$config['user_profile_need_ic'])	unset($upd['ic_no']);
			
			$hqcon->sql_begin_transaction();
			
			// create new user
			$hqcon->sql_query("insert into user ".mysql_insert_by_field($upd));
			$new_uid = $hqcon->sql_nextid();
			
			// insert privilege
			if($old_form['tmp_user_privilege']){
				foreach($old_form['tmp_user_privilege'] as $tmp_bid=>$p_list){
					foreach($p_list as $privilege_code=>$allowed){
						$upd2 = array();
						$upd2['branch_id'] = $tmp_bid;
						$upd2['user_id'] = $new_uid;
						$upd2['privilege_code'] = $privilege_code;
						$upd2['allowed'] = $allowed;
						$hqcon->sql_query("replace into user_privilege ".mysql_insert_by_field($upd2));	
					}
				}
			}
			
			$con->sql_query("delete from user_draft where id=$uid and branch_id=$bid");
			
			log_br($sessioninfo['id'], 'Cashier Setup', $uid, "Approve Draft Cashier: $old_form[u]");
			
			$hqcon->sql_commit();
			
			$subject= "Draft Cashier ($old_form[u]) has been approved.";
			$title = "Draft Cashier Approved";
			
			if(BRANCH_CODE != 'HQ' && !$config['single_server_mode']){
				$subject .= "<br />You are using multi server mode, the real cashier data may need to wait some time to sync";
			}
		}else{	// reject
			$upd = array();
			$upd['active'] = 0;
			$upd['reject_reason'] = $reject_reason;
			
			$con->sql_query("update user_draft set ".mysql_update_by_field($upd)." where id=$uid and branch_id=$bid");
			
			log_br($sessioninfo['id'], 'Cashier Setup', $uid, "Reject Draft Cashier: $old_form[u]");
			
			$subject= "Draft Cashier ($old_form[u]) has been rejected.";
			$title = "Draft Cashier Rejected";
		}
		
		$redirect_url = $_SERVER['PHP_SELF'];
		display_redir($redirect_url, $title, $subject);
	}
	
	function ajax_copy_privilege(){
		global $con, $LANG, $sessioninfo;
		
		$uid = mi($_REQUEST['uid']);
		$ret = array();
		
		// check this user can copy or not
		$con->sql_query("select * from user where id=$uid and (template=1 or (level=1))");
		$user = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$user){
			$ret['failed_reason'] = $LANG['USERS_NOT_FOUND'];
		}else{
			// get user privilege
			$filter = array();
			$filter[] = "user_id=$uid and allowed=1 and privilege_code like 'POS%'";
			if(BRANCH_CODE != 'HQ')	$filter[] = "branch_id=".mi($sessioninfo['branch_id']);
			
			$filter = "where ".join(' and ', $filter);
			$con->sql_query("select * from user_privilege $filter");
			$pv_list = array();
			while($r = $con->sql_fetchassoc()){
				$pv_list[$r['branch_id']][] = $r['privilege_code'];
			}
			$con->sql_freeresult();
			$ret['pv_list'] = $pv_list;
			$ret['ok'] = 1;
		}
		
		print json_encode($ret);
	}
}

$CASHIER_SETUP = new CASHIER_SETUP('Cashier Setup');
?>
