<?php
/*
12/27/2019 11:07 AM Andy
- Rename "Monthly Shift Assignments" to "Shift Assignments".
- Enhanced to highlight the day if it is Holiday.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ATTENDANCE_SHIFT_ASSIGN')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ATTENDANCE_SHIFT_ASSIGN', BRANCH_CODE), "/index.php");
$maintenance->check(439);

class SHIFT_ASSIGNMENT extends Module{
	var $branch_list = array();
	
	function __construct($title)
	{
		// load all initial data
		$this->init_load();
		
		parent::__construct($title);
	}
	
	private function init_load(){
		global $smarty, $appCore;
		
		$this->branch_list = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign('branch_list', $this->branch_list);
	}
	
	function _default()
	{
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;

		if(!isset($_REQUEST['branch_id']))	$_REQUEST['branch_id'] = $sessioninfo['branch_id'];
		
		// Year List
		$year_list = array();
		$con->sql_query("select distinct y from attendance_shift_user order by y desc");
		while($r = $con->sql_fetchassoc()){
			$year_list[] = $r['y'];
		}
		$con->sql_freeresult();
		$curr_y = date("Y");
		if(!in_array($curr_y, $year_list))	$year_list[] = date("Y");
		if(!isset($_REQUEST['y']))	$_REQUEST['y'] = $curr_y;
		$smarty->assign('year_list', $year_list);
		
		$this->display();
	}
	
	function ajax_load_branch_shift(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		if(BRANCH_CODE == 'HQ'){
			$bid = mi($_REQUEST['branch_id']);
		}else{
			$bid = $sessioninfo['branch_id'];
		}
		if(!$bid)	die($LANG['SHIFT_BRANCH_INVALID']);
		
		$y = mi($_REQUEST['y']);
		if($y < 2010)	die($LANG['SHIFT_YEAR_INVALID']);
		
		$data = array();
		// Get Branch Shift
		$sql = "select atss.*, user.u as user_u
			from attendance_shift_user atss
			join user on user.id=atss.user_id
			left join attendance_shift ats on ats.id=atss.shift_id
			where branch_id=$bid and y=$y
			order by user.u, ats.code";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			if(!isset($data['user_list'][$r['user_id']])){
				$data['user_list'][$r['user_id']]['u'] = $r['user_u'];
			}
			
			$data['user_list'][$r['user_id']]['month_list'][$r['m']]['shift_list'][$r['shift_id']]++;
		}
		$con->sql_freeresult($q1);
		
		//print_r($data);
		$shift_list = $appCore->attendanceManager->getShiftList(array('active'=>1));
		$smarty->assign('shift_list', $shift_list);
		$smarty->assign('bid', $bid);
		$smarty->assign('y', $y);
		$smarty->assign('data', $data);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('attendance.shift_assignment.branch.tpl');
		print json_encode($ret);
	}
	
	function open_shift_user(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		if(BRANCH_CODE == 'HQ'){
			$bid = mi($_REQUEST['branch_id']);
		}else{
			$bid = $sessioninfo['branch_id'];
		}
		$bid = mi($_REQUEST['branch_id']);
		$y = mi($_REQUEST['y']);
		$user_id = mi($_REQUEST['user_id']);
		
		if(!$bid)	die($LANG['SHIFT_BRANCH_INVALID']);
		if($y < 2010)	die($LANG['SHIFT_YEAR_INVALID']);
		if(!$user_id)	die($LANG['SHIFT_USER_INVALID']);
		
		// Get User
		$user = $appCore->userManager->getUser($user_id);
		
		// Generate Date List
		$start_date = $y.'-1-1';
		$end_date = $y.'-12-31';
		
		$start_time = strtotime($start_date);
		$end_time = strtotime($end_date);
		
		$data = array();
		$data['date_list'] = array();
		for($time = $start_time; $time <= $end_time; $time+=86400){
			$m = mi(date("m", $time));
			$ymd = date("Ymd", $time);
			$w = mi(date("W", $time));
			$day = mi(date("N", $time));
			
			// Month -> Week -> Day
			$data['date_list'][$m][$w][$day]['date'] = date("Y-m-d", $time);
		}
		// Get User Shift
		$q1 = $con->sql_query("select asu.*, alr.leave_id 
			from attendance_shift_user asu
			left join attendance_user_leave_record alr on alr.user_id=asu.user_id and asu.branch_id=alr.branch_id and asu.date between alr.date_from and alr.date_to
			where asu.branch_id=$bid and asu.y=$y and asu.user_id=$user_id
			order by asu.date");
		while($r = $con->sql_fetchassoc()){
			$time = strtotime($r['date']);
			$m = mi(date("m", $time));
			$w = mi(date("W", $time));
			$day = mi(date("N", $time));
			
			// Month -> Week -> Day
			$data['date_list'][$m][$w][$day]['shift_id'] = $r['shift_id'];
			if($r['leave_id']){
				$data['date_list'][$m][$w][$day]['leave_id'] = $r['leave_id'];
			}
		}
		$con->sql_freeresult();
		
		// Get Shift List
		$shift_list = $appCore->attendanceManager->getShiftList(array('active'=>1));
		
		// Get Leave List
		$leave_list = $appCore->attendanceManager->getLeaveList();
		//print_r($leave_list);
		
		// Get Holiday List
		$ph_data = $appCore->attendanceManager->getPublicHolidayYearData(0, $y);
		//print_r($ph_data);
		
		if($ph_data){
			foreach($ph_data['ph_list'] as $ph_id => $ph){
				$time1 = strtotime($ph['date_from']);
				$time2 = strtotime($ph['date_to']);
				
				for($time = $time1; $time <= $time2; $time+=86400){
					$m = mi(date("m", $time));
					$w = mi(date("W", $time));
					$day = mi(date("N", $time));
					
					// Month -> Week -> Day
					$data['date_list'][$m][$w][$day]['ph_list'][$ph_id] = $ph_id;
				}
			}
		}
		//print_r($data);
		
		$smarty->assign('shift_list', $shift_list);
		$smarty->assign('ph_data', $ph_data);
		$smarty->assign('leave_list', $leave_list);
		$smarty->assign('bid', $bid);
		$smarty->assign('user_id', $user_id);
		$smarty->assign('y', $y);
		$smarty->assign('user', $user);
		$smarty->assign('data', $data);
		$smarty->display('attendance.shift_assignment.user.tpl');
		
	}
	
	function ajax_save_user_shift(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		
		$bid = mi($_REQUEST['branch_id']);
		$y = mi($_REQUEST['y']);
		$user_id = mi($_REQUEST['user_id']);
		$save_m = mi($_REQUEST['save_m']);
		
		if(BRANCH_CODE != 'HQ' && $bid != $sessioninfo['branch_id'])	die(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], 'Shift'));
		if($y < 2010)	die($LANG['SHIFT_YEAR_INVALID']);
		if(!$user_id)	die($LANG['SHIFT_USER_INVALID']);
		if($save_m<=0 || $save_m>12)	die($LANG['SHIFT_MONTH_INVALID']);
		
		// Get User
		$user = $appCore->userManager->getUser($user_id);
		
		// Generate Date List
		$start_date = $y.'-'.$save_m.'-1';
		$end_date = $y.'-'.$save_m.'-'.days_of_month($save_m, $y);
		
		$start_time = strtotime($start_date);
		$end_time = strtotime($end_date);
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		for($time = $start_time; $time <= $end_time; $time+=86400){			
			$date = date("Y-m-d", $time);
			
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['user_id'] = $user_id;
			$upd['date'] = $date;
			$upd['y'] = $y;
			$upd['m'] = $save_m;
			$upd['shift_id'] = mi($_REQUEST['user_shift'][$date]);
			$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into attendance_shift_user ".mysql_insert_by_field($upd)." on duplicate key update
			shift_id=".$upd['shift_id'].",
			last_update=CURRENT_TIMESTAMP");
			
			// Update user time attendance shift if their shift is empty
			$appCore->attendanceManager->updateUserAttendanceDailyRecordShift($bid, $user_id, $date);
		}
		
		log_br($sessioninfo['id'], 'ATTENDANCE', $user_id, "Update User Shift, User : ".$user['u'].", Year: ".$y.", Month: ".$save_m);
		
		// Commit Transaction
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
}

$SHIFT_ASSIGNMENT = new SHIFT_ASSIGNMENT('Shift Assignments');
?>