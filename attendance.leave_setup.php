<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ATTENDANCE_LEAVE_SETUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ATTENDANCE_LEAVE_SETUP', BRANCH_CODE), "/index.php");
$maintenance->check(439);

class LEAVE_SETUP extends Module{
	
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
		$leave_list = $appCore->attendanceManager->getLeaveList();
		$smarty->assign('leave_list', $leave_list);
		
		$this->display();
	}
	
	function ajax_show_leave(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		$leave_id = mi($_REQUEST['leave_id']);
		
		if($leave_id>0){
			// Edit
			$form = $appCore->attendanceManager->getLeave($leave_id);
			if(!$form){
				die(sprintf($LANG['LEAVE_ID_NOT_FOUND'], $leave_id));
			}
		}else{
			// New
			
		}
		
		$smarty->assign('form', $form);
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('attendance.leave_setup.open.tpl');
		print json_encode($ret);
	}
	
	function generate_default_leave(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		$err = array();
		// Generate Default Shift Table
		$result = $appCore->attendanceManager->generateDefaultLeaveTable();
		if(!$result['ok']){
			$err[] = $result['error'];
		}
		
		if($err){
			$smarty->assign('err', $err);
			$this->_default();
			return;
		}
		
		// Commit Transaction
		$con->sql_commit();
		
		header("Location: ".$_SERVER['PHP_SELF']);
	}
	
	function ajax_save(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$leave_id = mi($form['id']);
		
		$upd = array();
		$upd['code'] = trim($form['code']);
		$upd['description'] = trim($form['description']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		if(!$upd['code'])	die($LANG['LEAVE_CODE_INVALID']);
		if(!$upd['description'])	die($LANG['LEAVE_DESC_INVALID']);
		
		// Check duplicate code
		$con->sql_query("select id from attendance_leave where code=".ms($upd['code'])." and id<>$leave_id");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if($tmp)	die($LANG['LEAVE_CODE_USED']);
		
		if($leave_id>0){
			// Edit
			$con->sql_query("update attendance_leave set ".mysql_update_by_field($upd)." where id=$leave_id");
			log_br($sessioninfo['id'], 'ATTENDANCE', $leave_id, "Update Leave, ID: $leave_id, Code: ".$upd['code']);
		}else{
			// New
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into attendance_leave ".mysql_insert_by_field($upd));
			$leave_id = $con->sql_nextid();
			log_br($sessioninfo['id'], 'ATTENDANCE', $leave_id, "Add New Leave, Code: ".$upd['code']);
		}
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_toggle_leave_active(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		//print_r($_REQUEST);
		$leave_id = mi($_REQUEST['leave_id']);
		$is_active = mi($_REQUEST['is_active']);
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		// Active / Inactive Shift
		$result = $appCore->attendanceManager->updateLeaveActive($leave_id, $is_active);
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

$LEAVE_SETUP = new LEAVE_SETUP('Leave Setup');
?>