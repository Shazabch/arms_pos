<?php
/*
11/21/2019 10:47 AM Andy
- Added maintenance checking v423.

3/16/2020 5:48 PM Andy
- Added Marketplace DO Owner Settings.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MARKETPLACE_SETTINGS')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MARKETPLACE_SETTINGS', BRANCH_CODE), "/index.php");
$maintenance->check(423);

class MARKETPLACE_SETTING extends Module {
	var $branch_info = array();
	var $branch_id = 0;
	
	function __construct($title)
	{	
		global $config, $con;
		
		// select the branch, must active
		$branch_code = trim(strtoupper($config['arms_marketplace_settings']['branch_code']));
		if(!$branch_code)	die("Invalid Marketplace Config");
		
		$q1 = $con->sql_query("select * from branch where code = ".ms($branch_code));
		$this->branch_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		if(!$this->branch_info){ // return error msg if branch couldn't be found
			die("Invalid Marketplace Branch [$branch_code]");
		}elseif(!$this->branch_info['active']){ // return error msg if it is inactive branch
			die("Marketplace Branch is inactiva");
		}else{ // store the branch id
			$this->branch_id = mi($this->branch_info['id']);
		}	
			
		parent::__construct($title);
	}
	
	function _default()
	{
		global $smarty, $sessioninfo, $config;

		$this->load_settings();
		$this->display();
	}
	
	private function load_settings(){
		global $smarty, $sessioninfo, $config, $con;
		
		// Normal Settings
		$con->sql_query("select * from marketplace_settings");
		while($r = $con->sql_fetchassoc()){
			$data['normal_settings'][$r['setting_name']] = $r['setting_value'];
		}
		$con->sql_freeresult();
		
		// Load Available User for DO Owner
		$do_user_list = array();
		$con->sql_query("select u.id, u.u
					from user u
					join user_privilege up on up.user_id=u.id and up.branch_id=$this->branch_id and up.privilege_code='DO' and up.allowed=1
					where u.id>0 and u.active=1 and u.locked=0 and u.is_arms_user=0
					order by u,u");
		while($r = $con->sql_fetchassoc()){
			$do_user_list[$r['id']] = $r;
		}
		$con->sql_freeresult();	
		
		$smarty->assign('data', $data);
		$smarty->assign('do_user_list', $do_user_list);
	}
	
	function ajax_save_settings(){
		global $con, $sessioninfo, $config;
		
		$form = $_REQUEST;
		
		//print_r($form);
		
		$con->sql_begin_transaction();
		
		if($form['data']['normal_settings']){
			// Normal Settings
			foreach($form['data']['normal_settings'] as $setting_name => $setting_value){
				$upd = array();
				$upd['setting_name'] = trim($setting_name);
				$upd['setting_value'] = trim($setting_value);
				$upd['last_update'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("replace into marketplace_settings ".mysql_insert_by_field($upd));
			}
		}
		
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
		exit;
	}
}

$MARKETPLACE_SETTING = new MARKETPLACE_SETTING('Marketplace Settings');
?>