<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ATTENDANCE_SHIFT_SETUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ATTENDANCE_SHIFT_SETUP', BRANCH_CODE), "/index.php");
$maintenance->check(424);

class SHIFT_TABLE_SETUP extends Module{
	
	function __construct($title)
	{
		// load all initial data
		//$this->init_load();
		
		parent::__construct($title);
	}
	
	function _default()
	{
		global $smarty, $sessioninfo, $config, $LANG, $appCore;

		// Get Shift List
		$shift_list = $appCore->attendanceManager->getShiftList();
		$smarty->assign('shift_list', $shift_list);
		
		$this->display();
	}
	
	
	function generate_default_shift(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		$err = array();
		// Generate Default Shift Table
		$result = $appCore->attendanceManager->generateDefaultShiftTable();
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
	
	function ajax_toggle_shift_active(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		//print_r($_REQUEST);
		$shift_id = mi($_REQUEST['shift_id']);
		$is_active = mi($_REQUEST['is_active']);
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		// Active / Inactive Shift
		$result = $appCore->attendanceManager->updateShiftActive($shift_id, $is_active);
		if(!$result['ok']){
			die($result['error']);
		}
		
		// Commit Transaction
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_show_shift(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		$shift_id = mi($_REQUEST['shift_id']);
		
		if($shift_id>0){
			// Edit
			$shift = $appCore->attendanceManager->getShift($shift_id);
			if(!$shift){
				die(sprintf($LANG['SHIFT_ID_NOT_FOUND'], $shift_id));
			}
		}else{
			// New
			$shift = array();
			$shift['start_time'] = '00:00';
			$shift['end_time'] = '23:59';
		}
		
		$smarty->assign('form', $shift);
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('attendance.shift_table_setup.open.tpl');
		print json_encode($ret);
	}
	function ajax_save(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$shift_id = mi($form['id']);
		
		$upd = array();
		$upd['code'] = trim($form['code']);
		$upd['description'] = trim($form['description']);
		$upd['shift_color'] = trim($form['shift_color']);
		$upd['start_time'] = trim($form['start_time']);
		$upd['end_time'] = trim($form['end_time']);
		$upd['break_1_start_time'] = trim($form['break_1_start_time']);
		$upd['break_1_end_time'] = trim($form['break_1_end_time']);
		$upd['break_2_start_time'] = trim($form['break_2_start_time']);
		$upd['break_2_end_time'] = trim($form['break_2_end_time']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		if(!$upd['code'])	die($LANG['SHIFT_CODE_INVALID']);
		if(!$upd['description'])	die($LANG['SHIFT_DESC_INVALID']);
		if(!$appCore->isValidDateFormat($upd['start_time'], "H:i"))	die($LANG['SHIFT_START_INVALID']);
		if(!$appCore->isValidDateFormat($upd['end_time'], "H:i"))	die($LANG['SHIFT_END_INVALID']);
		if($upd['break_1_start_time'] || $upd['break_1_end_time']){
			if(!$appCore->isValidDateFormat($upd['break_1_start_time'], "H:i"))	die($LANG['SHIFT_BREAK_1_INVALID']);
			if(!$appCore->isValidDateFormat($upd['break_1_end_time'], "H:i"))	die($LANG['SHIFT_BREAK_1_INVALID']);
		}
		if($upd['break_2_start_time'] || $upd['break_2_end_time']){
			if(!$appCore->isValidDateFormat($upd['break_2_start_time'], "H:i"))	die($LANG['SHIFT_BREAK_2_INVALID']);
			if(!$appCore->isValidDateFormat($upd['break_2_end_time'], "H:i"))	die($LANG['SHIFT_BREAK_2_INVALID']);
		}
		// Check duplicate code
		$con->sql_query("select id from attendance_shift where code=".ms($upd['code'])." and id<>$shift_id");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if($tmp)	die($LANG['SHIFT_CODE_USED']);
		
		if($shift_id>0){
			// Edit
			$con->sql_query("update attendance_shift set ".mysql_update_by_field($upd)." where id=$shift_id");
			log_br($sessioninfo['id'], 'ATTENDANCE', $shift_id, "Update Shift, ID: $shift_id, Code: ".$upd['code']);
		}else{
			// New
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into attendance_shift ".mysql_insert_by_field($upd));
			$shift_id = $con->sql_nextid();
			log_br($sessioninfo['id'], 'ATTENDANCE', $shift_id, "Add New Shift, Code: ".$upd['code']);
		}
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
}

$SHIFT_TABLE_SETUP = new SHIFT_TABLE_SETUP('Shift Table Setup');
?>