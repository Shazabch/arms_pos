<?php
/*
1/8/2020 5:38 PM Andy
- Enhanced to store old scan record when user modified data.

2/18/2020 4:24 PM Andy
- Fixed to record ocounter_id when save / delete user record.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ATTENDANCE_USER_MODIFY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ATTENDANCE_USER_MODIFY', BRANCH_CODE), "/index.php");
$maintenance->check(439);

class ATTENDANCE_USER_RECORDS extends Module{
	var $branch_list = array();
	
	function __construct($title)
	{
		global $sessioninfo;
		
		// load all initial data
		$this->init_load();
		
		if(!isset($_REQUEST['branch_id']))	$_REQUEST['branch_id'] = $sessioninfo['branch_id'];
		if(!isset($_REQUEST['date_to']))	$_REQUEST['date_to'] = date("Y-m-d");
		if(!isset($_REQUEST['date_from']))	$_REQUEST['date_from'] = date("Y-m-d", strtotime("-7 day"));
		
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
	
	function ajax_load_user_record(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		//print_r($_REQUEST);
		
		if(BRANCH_CODE == 'HQ'){
			$bid = mi($_REQUEST['branch_id']);
		}else{
			$bid = mi($sessioninfo['branch_id']);
		}
		
		$user_id = mi($_REQUEST['user_id']);
		$date_from = trim($_REQUEST['date_from']);
		$date_to = trim($_REQUEST['date_to']);
		
		if($bid <= 0)	die("Invalid Branch ID");
		if($user_id <= 0)	die("Invalid User ID");
		if(!$appCore->isValidDateFormat($date_from))	die("Invalid Date From");
		if(!$appCore->isValidDateFormat($date_to))	die("Invalid Date To");
		if(strtotime($date_to) < strtotime($date_from))	die("Date To cannot ealier than Date From.");
		
		// Get Records
		$record_list = $appCore->attendanceManager->getUserAttendanceDailyRecord($bid, $user_id, $date_from, $date_to, array('get_scan_records'=>1, 'check_got_modify'=>1));
		
		//print_r($record_list);
		
		$smarty->assign('record_list', $record_list);
		$smarty->assign('bid', $bid);
		$smarty->assign('user_id', $user_id);
			
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('attendance.user_records.list.tpl');
		
		print json_encode($ret);
	}
	
	function ajax_show_user_daily_record(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		if(BRANCH_CODE == 'HQ'){
			$bid = mi($_REQUEST['branch_id']);
		}else{
			$bid = mi($sessioninfo['branch_id']);
		}
		
		$is_new = mi($_REQUEST['is_new']);
		$user_id = mi($_REQUEST['user_id']);
		$date = trim($_REQUEST['date']);
		
		if($bid <= 0)	die("Invalid Branch ID");
		if($user_id <= 0)	die("Invalid User ID");
		
		if(!$is_new){
			if(!$appCore->isValidDateFormat($date))	die("Invalid Date");
		
			// Get Records
			$daily_record = $appCore->attendanceManager->getUserAttendanceDailyRecord($bid, $user_id, $date, '', array('get_scan_records'=>1));
			//print_r($daily_record);
			
			if(!$daily_record)	die('Data Not Found.');
		}else{
			$daily_record = array();
			$daily_record['branch_id'] = $bid;
			$daily_record['user_id'] = $user_id;
		}
		
		
		// Get User
		$user = $appCore->userManager->getUser($user_id);
		
		// Get Shift List
		$shift_list = $appCore->attendanceManager->getShiftList(array('active'=>1));
		
		$smarty->assign('form', $daily_record);
		$smarty->assign('user', $user);
		$smarty->assign('shift_list', $shift_list);
		$smarty->assign('is_new', $is_new);
			
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('attendance.user_records.open.tpl');
		
		print json_encode($ret);
	}
	
	function ajax_save_user_daily_record(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore, $client_ip;
		
		//print_r($_REQUEST);exit;
		
		$is_new = mi($_REQUEST['is_new']);
		$bid = mi($_REQUEST['branch_id']);
		$user_id = mi($_REQUEST['user_id']);
		$date = trim($_REQUEST['date']);
		
		if($bid <= 0)	die("Invalid Branch ID");
		if(BRANCH_CODE != 'HQ' && $bid != $sessioninfo['branch_id'])	die(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "User Record"));
		if($user_id <= 0)	die("Invalid User ID");
		if(!$appCore->isValidDateFormat($date))	die("Invalid Date");
		if ($is_new && !privilege('ATTENDANCE_USER_MODIFY_ADD')) die(sprintf($LANG['NO_PRIVILEGE'], 'ATTENDANCE_USER_MODIFY_ADD', BRANCH_CODE));
		
		// Check if data exists
		$con->sql_query("select * from attendance_user_daily_record where branch_id=$bid and user_id=$user_id and date=".ms($date));
		$org_data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($is_new && $org_data)	die("Add User Record Failed (Data Already Existed)");
		if(!$is_new && !$org_data)	die("Update User Record Failed (Data Not Found)");
		
		$shift_id = mi($_REQUEST['shift_id']);
		$shift_code = trim($_REQUEST['shift_code']);
		$shift_description = trim($_REQUEST['shift_description']);
		$shift_color = trim($_REQUEST['shift_color']);
		
		$start_time = trim($_REQUEST['start_time']);
		$break_1_start_time = trim($_REQUEST['break_1_start_time']);
		$break_1_end_time = trim($_REQUEST['break_1_end_time']);
		$break_2_start_time = trim($_REQUEST['break_2_start_time']);
		$break_2_end_time = trim($_REQUEST['break_2_end_time']);
		$end_time = trim($_REQUEST['end_time']);
		
		// Check main info
		if(!$shift_id)	die("No Shift Assigned");
		if(!$shift_code)	die($LANG['SHIFT_CODE_INVALID']);
		if(!$shift_description)	die($LANG['SHIFT_DESC_INVALID']);
		
		// Check Time
		if(!$appCore->isValidDateFormat($start_time, "H:i"))	die($LANG['SHIFT_START_INVALID']);
		if(!$appCore->isValidDateFormat($end_time, "H:i"))	die($LANG['SHIFT_END_INVALID']);
		if($break_1_start_time || $break_1_end_time){
			if(!$appCore->isValidDateFormat($break_1_start_time, "H:i"))	die($LANG['SHIFT_BREAK_1_INVALID']);
			if(!$appCore->isValidDateFormat($break_1_end_time, "H:i"))	die($LANG['SHIFT_BREAK_1_INVALID']);
		}
		if($break_2_start_time || $break_2_end_time){
			if(!$appCore->isValidDateFormat($break_2_start_time, "H:i"))	die($LANG['SHIFT_BREAK_2_INVALID']);
			if(!$appCore->isValidDateFormat($break_2_end_time, "H:i"))	die($LANG['SHIFT_BREAK_2_INVALID']);
		}
		
		// Check Scan Record
		foreach($_REQUEST['new_scan_time'] as $row_no => $new_scan_time){
			$new_scan_time = trim($new_scan_time);
			if(!$new_scan_time)	continue;	// No change
			
			if(isset($_REQUEST['delete_scan'][$row_no]))	continue;	// this record need delete
			
			// Check Time
			if(!$appCore->isValidDateFormat($new_scan_time, "H:i:s"))	die("Row: $row_no [".$LANG['SHIFT_CLOCK_TIME_INVALID']."]");	// time incorrect format
			
			// Check Date
			$new_scan_date = trim($_REQUEST['new_scan_date'][$row_no]);
			if($new_scan_date){
				if(!$appCore->isValidDateFormat($new_scan_date))	die("Row: $row_no [".$LANG['SHIFT_CLOCK_DATE_INVALID']."]");	// date incorrect format
			}
		}
		
		// New Record
		$user_shift = array();
		$user_shift['shift_id'] = $shift_id;
		$user_shift['shift_code'] = $shift_code;
		$user_shift['shift_description'] = $shift_description;
		$user_shift['shift_color'] = $shift_color;
		$user_shift['start_time'] = $start_time;
		$user_shift['end_time'] = $end_time;
		$user_shift['break_1_start_time'] = $break_1_start_time;
		$user_shift['break_1_end_time'] = $break_1_end_time;
		$user_shift['break_2_start_time'] = $break_2_start_time;
		$user_shift['break_2_end_time'] = $break_2_end_time;
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		// Backup Old Record
		$odata_guid = $appCore->newGUID();
		$upd_old = array();
		$upd_old['guid'] = $odata_guid;
		$upd_old['branch_id'] = $bid;
		$upd_old['user_id'] = $user_id;
		$upd_old['date'] = $date;
		$upd_old['edit_by_user_id'] = $sessioninfo['id'];
		$upd_old['added'] = 'CURRENT_TIMESTAMP';
		$upd_old['odata'] = serialize($org_data);
		$upd_old['ndata'] = serialize($user_shift);
		if($is_new)	$upd_old['is_new'] = 1;
		$con->sql_query("insert into attendance_user_daily_record_modify_history ".mysql_insert_by_field($upd_old));
		
		if($is_new){
			// Only create if not yet add
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['user_id'] = $user_id;
			$upd['date'] = $date;
			$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into attendance_user_daily_record ".mysql_insert_by_field($upd));
		}
		
		$params = array();
		$params['user_shift'] = $user_shift;
		$params['force_update'] = 1;
		
		// Update Daily Record
		$success = $appCore->attendanceManager->updateUserAttendanceDailyRecordShift($bid, $user_id, $date, $params);
		if(!$success)	die("Update Failed");
		
		// Update Scan Record
		// Check Scan Record
		foreach($_REQUEST['org_scan_time'] as $row_no => $org_scan_time){
			$new_full_scan_time = '';
			$is_deleted_scan = false;
			$is_new_scan = false;
			$is_update_scan = false;
			$odata = array();
			
			// New Scan Date
			$new_scan_date = trim($_REQUEST['new_scan_date'][$row_no]);
			// New Scan Time
			$new_scan_time = trim($_REQUEST['new_scan_time'][$row_no]);
			if($new_scan_time){
				$new_full_scan_time = ($new_scan_date ? $new_scan_date : $date)." ".$new_scan_time;
			}
			
			if($org_scan_time){
				if(isset($_REQUEST['delete_scan'][$row_no])){
					$is_deleted_scan = true;
				}elseif($new_full_scan_time){
					$is_update_scan = true;
				}
			}else{
				if($new_full_scan_time){
					$is_new_scan = true;
				}
			}
			
			if(!$is_deleted_scan && !$is_new_scan && !$is_update_scan)	continue;	// no change
			
			if($is_deleted_scan || $is_update_scan){
				// Get Old Scan Record
				$con->sql_query("select * from attendance_user_scan_record where branch_id=$bid and user_id=$user_id and date=".ms($date)." and scan_time=".ms($org_scan_time));
				$odata = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$odata){
					// Original Data Not Found
					die(sprintf($LANG['SHIFT_SCAN_DATA_NOT_FOUND'], $org_scan_time));
				}
			}
			
			// Backup Old Scan Record
			$upd2 = array();
			$upd2['guid'] = $appCore->newGUID();
			$upd2['odata_guid'] = $odata_guid;
			$upd2['branch_id'] = $bid;
			$upd2['user_id'] = $user_id;
			$upd2['date'] = $date;
			if($is_deleted_scan || $is_update_scan){
				// delete or update
				$upd2['oscan_time'] = $org_scan_time;
				$upd2['oip'] = $odata['ip'];
				$upd2['ocounter_id'] = $odata['counter_id'];
			}
			if($is_update_scan || $is_new_scan){
				// new or update
				$upd2['nscan_time'] = $new_full_scan_time;
				$upd2['nip'] = $client_ip;
			}
			if($is_new_scan){
				$upd2['is_new'] = 1;
			}elseif($is_deleted_scan){
				$upd2['is_deleted'] = 1;
			}
			$upd2['edit_by_user_id'] = $sessioninfo['id'];
			$upd2['added'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into attendance_user_scan_record_modify_history ".mysql_insert_by_field($upd2));
			
			if($org_scan_time){
				if($is_deleted_scan){
					// Delete
					$con->sql_query("delete from attendance_user_scan_record where branch_id=$bid and user_id=$user_id and date=".ms($date)." and scan_time=".ms($org_scan_time));
				}elseif($is_update_scan){
					// Update 
					$upd = array();
					$upd['scan_time'] = $new_full_scan_time;
					$upd['ip'] = $client_ip;
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("update attendance_user_scan_record set ".mysql_update_by_field($upd)." where branch_id=$bid and user_id=$user_id and date=".ms($date)." and scan_time=".ms($org_scan_time));
				}
			}else{
				if($is_new_scan){
					// Add New Record
					$upd = array();
					$upd['branch_id'] = $bid;
					$upd['user_id'] = $user_id;
					$upd['date'] = $date;
					$upd['counter_id'] = 0;
					$upd['scan_time'] = $new_full_scan_time;
					$upd['ip'] = $client_ip;
					$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("insert into attendance_user_scan_record ".mysql_insert_by_field($upd));
				}
			}
		}
		
		// Get User
		$user = $appCore->userManager->getUser($user_id);
		
		log_br($sessioninfo['id'], 'ATTENDANCE', $user_id, ($is_new ? 'Add' : 'Update')." User Daily Record, Branch: ".$this->branch_list[$bid]['code'].", User: ".$user['u'].", Date: ".$date);
		
		// Commit Transaction
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_delete_daily_record(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		//print_r($_REQUEST);exit;
		
		$bid = mi($_REQUEST['branch_id']);
		$user_id = mi($_REQUEST['user_id']);
		$date = trim($_REQUEST['date']);
		
		if($bid <= 0)	die("Invalid Branch ID");
		if(BRANCH_CODE != 'HQ' && $bid != $sessioninfo['branch_id'])	die(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "User Record"));
		if($user_id <= 0)	die("Invalid User ID");
		if(!$appCore->isValidDateFormat($date))	die("Invalid Date");
		
		// Check if data exists
		$con->sql_query("select * from attendance_user_daily_record where branch_id=$bid and user_id=$user_id and date=".ms($date));
		$org_data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$org_data)	die("Delete User Record Failed (Data Not Found)");
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		// Backup Old Record
		$odata_guid = $appCore->newGUID();
		$upd_old = array();
		$upd_old['guid'] = $odata_guid;
		$upd_old['branch_id'] = $bid;
		$upd_old['user_id'] = $user_id;
		$upd_old['date'] = $date;
		$upd_old['edit_by_user_id'] = $sessioninfo['id'];
		$upd_old['added'] = 'CURRENT_TIMESTAMP';
		$upd_old['odata'] = serialize($org_data);
		$upd_old['is_deleted'] = 1;
		$con->sql_query("insert into attendance_user_daily_record_modify_history ".mysql_insert_by_field($upd_old));
		
		// Get Old Scan Record
		$q1 = $con->sql_query("select * from attendance_user_scan_record where branch_id=$bid and user_id=$user_id and date=".ms($date)." order by scan_time");
		while($r = $con->sql_fetchassoc($q1)){
			// Backup Old Scan Record
			$upd2 = array();
			$upd2['guid'] = $appCore->newGUID();
			$upd2['odata_guid'] = $odata_guid;
			$upd2['branch_id'] = $bid;
			$upd2['user_id'] = $user_id;
			$upd2['date'] = $date;
			$upd2['oscan_time'] = $r['scan_time'];
			$upd2['oip'] = $r['ip'];
			$upd2['ocounter_id'] = $r['counter_id'];
			$upd2['is_deleted'] = 1;
			$upd2['edit_by_user_id'] = $sessioninfo['id'];
			$upd2['added'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into attendance_user_scan_record_modify_history ".mysql_insert_by_field($upd2));
		}
		$con->sql_freeresult($q1);
		
		// Delete Daily Record
		$success = $appCore->attendanceManager->deleteUserAttendanceDailyRecord($bid, $user_id, $date);
		if(!$success)	die("Delete Failed");
		
		// Get User
		$user = $appCore->userManager->getUser($user_id);
		
		log_br($sessioninfo['id'], 'ATTENDANCE', $user_id, "Delete User Daily Record, Branch: ".$this->branch_list[$bid]['code'].", User: ".$user['u'].", Date: ".$date);
		
		// Commit Transaction
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function view_history(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		if(BRANCH_CODE == 'HQ'){
			$bid = mi($_REQUEST['branch_id']);
		}else{
			$bid = mi($sessioninfo['branch_id']);
		}
		
		$user_id = mi($_REQUEST['user_id']);
		$date_from = trim($_REQUEST['date_from']);
		$date_to = trim($_REQUEST['date_to']);
		$date = trim($_REQUEST['date']);
		if($date){
			// only view single date
			$date_from = $date_to = $date;
		}
		
		$result = $appCore->attendanceManager->getUserAttendanceDailyRecordModifiedData($bid, $user_id, $date_from, $date_to);
		if(!$result['ok']){
			display_redir($_SERVER['PHP_SELF'], $this->title, $result['error']);
		}
		$data = $result['data'];
				
		//print_r($data);
		$smarty->assign('data', $data);
		
		// Get User
		$user = $appCore->userManager->getUser($user_id);
		$smarty->assign('user', $user);
		
		$report_title = array();
		$report_title[] = "Branch: ".$this->branch_list[$bid]['code'];
		$report_title[] = "User: ".$user['u'];
		if($date_from){
			$report_title[] = "Date From: ".$date_from;
		}
		if($date_to){
			$report_title[] = "Date To: ".$date_to;
		}
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		
		$smarty->display('attendance.user_records.view_history.tpl');
	}
}

$ATTENDANCE_USER_RECORDS = new ATTENDANCE_USER_RECORDS('User Attendance Records');
?>