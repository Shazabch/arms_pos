<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ATTENDANCE_PH_SETUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ATTENDANCE_PH_SETUP', BRANCH_CODE), "/index.php");
$maintenance->check(439);

class PH_SETUP extends Module{
	
	function __construct($title)
	{
		// load all initial data
		//$this->init_load();
		
		parent::__construct($title);
	}
	
	function _default()
	{
		global $smarty, $sessioninfo, $config, $LANG, $appCore;

		// Get Holiday List
		$ph_list = $appCore->attendanceManager->getPublicHolidayList();
		$smarty->assign('ph_list', $ph_list);
		
		$this->display();
	}
	
	function ajax_show_ph(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		$ph_id = mi($_REQUEST['ph_id']);
		
		if($ph_id>0){
			// Edit
			$ph = $appCore->attendanceManager->getPublicHoliday($ph_id);
			if(!$ph){
				die(sprintf($LANG['PH_ID_NOT_FOUND'], $ph_id));
			}
		}else{
			// New
			
		}
		
		$smarty->assign('form', $ph);
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('attendance.ph_setup.open.tpl');
		print json_encode($ret);
	}
	
	function ajax_save(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$ph_id = mi($form['id']);
		
		$upd = array();
		$upd['code'] = trim($form['code']);
		$upd['description'] = trim($form['description']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		if(!$upd['code'])	die($LANG['PH_CODE_INVALID']);
		if(!$upd['description'])	die($LANG['PH_DESC_INVALID']);
		
		// Check duplicate code
		$con->sql_query("select id from attendance_ph where code=".ms($upd['code'])." and id<>$ph_id");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if($tmp)	die($LANG['PH_CODE_USED']);
		
		if($ph_id>0){
			// Edit
			$con->sql_query("update attendance_ph set ".mysql_update_by_field($upd)." where id=$ph_id");
			log_br($sessioninfo['id'], 'ATTENDANCE', $ph_id, "Update Public Holiday, ID: $ph_id, Code: ".$upd['code']);
		}else{
			// New
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into attendance_ph ".mysql_insert_by_field($upd));
			$ph_id = $con->sql_nextid();
			log_br($sessioninfo['id'], 'ATTENDANCE', $ph_id, "Add New Public Holiday, Code: ".$upd['code']);
		}
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_toggle_ph_active(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		//print_r($_REQUEST);
		$ph_id = mi($_REQUEST['ph_id']);
		$is_active = mi($_REQUEST['is_active']);
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		// Active / Inactive Shift
		$result = $appCore->attendanceManager->updatePublicHolidayActive($ph_id, $is_active);
		if(!$result['ok']){
			die($result['error']);
		}
		
		// Commit Transaction
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
}

$PH_SETUP = new PH_SETUP('Holiday Setup');
?>