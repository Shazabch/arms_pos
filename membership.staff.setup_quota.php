<?php
include("include/common.php");
$maintenance->check(183);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!$config['membership_enable_staff_card']) js_redirect($LANG['NEED_CONFIG'], "/index.php");
if (!privilege('MEMBERSHIP_STAFF')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_STAFF', BRANCH_CODE), "/index.php");
if (!privilege('MEMBERSHIP_STAFF_SET_QUOTA')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_STAFF_SET_QUOTA', BRANCH_CODE), "/index.php");

class STAFF_SETUP_QUOTA extends Module{
	var $data = array();
	
	function __construct($title, $template=''){
		global $config, $con, $smarty, $sessioninfo;
		
		parent::__construct($title, $template);
	}
	
	function _default(){
		global $con, $sessioninfo, $config, $smarty;
		
		//print_r($this->memberx_list);
		
		// load the list
		$con->sql_query("select * from mst_staff_quota");
		while($r = $con->sql_fetchassoc()){
			$this->data['data'][$r['staff_type']] = $r;
		}
		$con->sql_freeresult();
		
		$smarty->assign('data', $this->data);
		$this->display();
	}
	
	function ajax_update_staff_quota(){
		global $con, $smarty, $sessioninfo, $config;
		
		$form = $_REQUEST;
		
		//print_r($form);
		
		$con->sql_query("select * from mst_staff_quota");
		$curr_data = array();
		while($r = $con->sql_fetchassoc()){
			$curr_data[$r['staff_type']] = $r;
		}
		$con->sql_freeresult();
		
		if($form['quota_value']){
			foreach($form['quota_value'] as $staff_type => $quota_value){
				if($curr_data[$staff_type]['quota_value'] != $quota_value){	// need update
					$upd = array();
					$upd['staff_type'] = $staff_type;
					$upd['quota_value'] = $quota_value;
					$upd['user_id'] = $sessioninfo['id'];
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$upd['changed'] = 1;
					$con->sql_query("replace into mst_staff_quota ".mysql_insert_by_field($upd));
					
					$upd2 = array();
					$upd2['staff_type'] = $staff_type;
					$upd2['quota_value'] = $quota_value;
					$upd2['user_id'] = $sessioninfo['id'];
					$upd2['added_timestamp'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("replace into mst_staff_quota_history ".mysql_insert_by_field($upd2));
				}
			}
		}
		
		$ret = array();
		$ret['ok'] = 1;
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function ajax_show_quota_history(){
		global $con, $smarty, $sessioninfo, $config;
		
		$staff_type = trim($_REQUEST['staff_type']);
		
		if(!$staff_type || !$config['membership_staff_type'][$staff_type])	die("Invalid Staff Type");
		
		$quota_history_list = array();
		
		$q1 = $con->sql_query("select qh.* , user.u as update_by
from mst_staff_quota_history qh 
left join user on user.id=qh.user_id
where qh.staff_type=".ms($staff_type)." 
order by qh.added_timestamp desc");
		while($r = $con->sql_fetchassoc($q1)){
			$quota_history_list[] = $r;
		}
		$con->sql_freeresult($q1);

		$ret = array();
		$ret['ok'] = 1;
		
		$smarty->assign('quota_history_list', $quota_history_list);
		$ret['html'] = $smarty->fetch('membership.staff.setup_quota.quota_history.tpl');
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
}

$STAFF_SETUP_QUOTA = new STAFF_SETUP_QUOTA('Setup Quota');
?>
