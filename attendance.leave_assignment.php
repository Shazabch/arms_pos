<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ATTENDANCE_LEAVE_ASSIGN')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ATTENDANCE_LEAVE_ASSIGN', BRANCH_CODE), "/index.php");
$maintenance->check(439);

class ATTENDANCE_LEAVE_ASSIGN extends Module{
	var $branch_list = array();
	
	function __construct($title)
	{
		global $sessioninfo;
		
		// load all initial data
		$this->init_load();
		
		if(!isset($_REQUEST['branch_id']))	$_REQUEST['branch_id'] = $sessioninfo['branch_id'];
		//if(!isset($_REQUEST['date_to']))	$_REQUEST['date_to'] = date("Y-m-d");
		//if(!isset($_REQUEST['date_from']))	$_REQUEST['date_from'] = date("Y-m-d", strtotime("-7 day"));
		
		parent::__construct($title);
	}
	
	private function init_load(){
		global $con, $smarty, $appCore;
		
		$this->branch_list = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign('branch_list', $this->branch_list);
	}
	
	function _default()
	{
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;

		$this->display();
	}
	
	function ajax_load_user_leave(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		//print_r($_REQUEST);
		
		if(BRANCH_CODE == 'HQ'){
			$bid = mi($_REQUEST['branch_id']);
		}else{
			$bid = mi($sessioninfo['branch_id']);
		}
		
		$user_id = mi($_REQUEST['user_id']);
		//$date_from = trim($_REQUEST['date_from']);
		//$date_to = trim($_REQUEST['date_to']);
		
		//if($bid <= 0)	die("Invalid Branch ID");
		if($user_id <= 0)	die("Invalid User ID");
		//if(!$appCore->isValidDateFormat($date_from))	die("Invalid Date From");
		//if(!$appCore->isValidDateFormat($date_to))	die("Invalid Date To");
		//if(strtotime($date_to) < strtotime($date_from))	die("Date To cannot ealier than Date From.");
		
		// Get Records
		$record_list = $appCore->attendanceManager->getUserAttendanceLeaveRecord($user_id, $bid);
		
		//print_r($record_list);
		
		$smarty->assign('record_list', $record_list);
		$smarty->assign('user_id', $user_id);
			
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('attendance.leave_assignment.list.tpl');
		
		print json_encode($ret);
	}
	
	function ajax_show_user_leave_record(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		if(BRANCH_CODE == 'HQ'){
			$bid = mi($_REQUEST['branch_id']);
		}else{
			$bid = mi($sessioninfo['branch_id']);
		}
		
		$is_new = mi($_REQUEST['is_new']);
		$user_id = mi($_REQUEST['user_id']);
		$guid = trim($_REQUEST['guid']);
		
		//if($bid <= 0)	die("Invalid Branch ID");
		if($user_id <= 0)	die("Invalid User ID");
		
		if(!$is_new){
			// Get Records
			$leave_record = $appCore->attendanceManager->getUserAttendanceLeaveRecord($user_id, $bid, $guid);
			//print_r($leave_record);
			
			if(!$leave_record)	die('Data Not Found.');
		}else{
			$leave_record = array();
			$leave_record['branch_id'] = $bid;
			$leave_record['user_id'] = $user_id;
		}
		
		
		// Get User
		$user = $appCore->userManager->getUser($user_id);
		
		// Get Leave List
		$leave_list = $appCore->attendanceManager->getLeaveList(array('active'=>1));
		
		$smarty->assign('form', $leave_record);
		$smarty->assign('user', $user);
		$smarty->assign('leave_list', $leave_list);
		$smarty->assign('is_new', $is_new);
			
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('attendance.leave_assignment.open.tpl');
		
		print json_encode($ret);
	}
	
	function ajax_save_user_leave_record(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		//print_r($_REQUEST);exit;
		
		$guid = trim($_REQUEST['guid']);
		$is_new = mi($_REQUEST['is_new']);
		$bid = mi($_REQUEST['branch_id']);
		$user_id = mi($_REQUEST['user_id']);
		$leave_id = mi($_REQUEST['leave_id']);
		$date_from = trim($_REQUEST['date_from']);
		$date_to = trim($_REQUEST['date_to']);
		
		if($bid <= 0)	die("Invalid Branch ID");
		if(BRANCH_CODE != 'HQ' && $bid != $sessioninfo['branch_id'])	die(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Leave"));
		if($user_id <= 0)	die("Invalid User ID");
		if($leave_id <= 0)	die("Invalid Leave ID");
		if(!$appCore->isValidDateFormat($date_from))	die("Invalid Date From");
		if(!$appCore->isValidDateFormat($date_to))	die("Invalid Date To");
		if(strtotime($date_to) < strtotime($date_from))	die("Date To cannot ealier than Date From.");
		if($is_new && $guid)	die("System Error: GUID already existed.");
		
		// Check if data exists
		$con->sql_query("select * from attendance_user_leave_record where user_id=$user_id and (".ms($date_from)." between date_from and date_to or ".ms($date_to)." between date_from and date_to) and guid<>".ms($guid));
		$existed_data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Already applied other leave in the selected date range
		if($existed_data)	die($LANG['LEAVE_DATE_HAVE_DATA']);
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		$upd = array();
		$upd['leave_id'] = $leave_id;
		$upd['date_from'] = $date_from;
		$upd['date_to'] = $date_to;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		if($is_new){
			// Only create if not yet add
			$guid = $appCore->newGUID();
			$upd['guid'] = $guid;
			$upd['user_id'] = $user_id;
			$upd['branch_id'] = $bid;
			$upd['active'] = 1;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into attendance_user_leave_record ".mysql_insert_by_field($upd));
		}else{
			$con->sql_query("update attendance_user_leave_record set ".mysql_update_by_field($upd)." where guid=".ms($guid));
		}
		
		// Get User
		$user = $appCore->userManager->getUser($user_id);
		
		log_br($sessioninfo['id'], 'ATTENDANCE', $user_id, ($is_new ? 'Add' : 'Update')." User Leave Record, Branch: ".$this->branch_list[$bid]['code'].", User: ".$user['u'].", Date From: ".$date_from.", Date To: ".$date_to);
		
		// Commit Transaction
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_delete_leave_record(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		//print_r($_REQUEST);exit;
		
		$guid = trim($_REQUEST['guid']);
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		// Check if data exists
		$con->sql_query("select * from attendance_user_leave_record where guid=".ms($guid)." for update");
		$curr_data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$curr_data)	die("Data Not Found");
		
		if(BRANCH_CODE != 'HQ'){
			// Diff branch
			if($curr_data['branch_id'] != $sessioninfo['branch_id']){
				die(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Leave"));
			}
		}
		
		// Delete Daily Record
		$success = $appCore->attendanceManager->deleteUserAttendanceLeaveRecord($guid);
		if(!$success)	die("Delete Failed");
		
		// Get User
		$user = $appCore->userManager->getUser($curr_data['user_id']);
		
		log_br($sessioninfo['id'], 'ATTENDANCE', $user_id, "Delete User Leave Record, Branch: ".$this->branch_list[$curr_data['branch_id']]['code'].", User: ".$user['u'].", Date From: ".$curr_data['date_from'].", Date To: ".$curr_data['date_to']);
		
		// Commit Transaction
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
}

$ATTENDANCE_LEAVE_ASSIGN = new ATTENDANCE_LEAVE_ASSIGN('Leave Assignments');
?>