<?php
/*
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ATTENDANCE_TIME_OVERVIEW')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ATTENDANCE_TIME_OVERVIEW', BRANCH_CODE), "/index.php");

class ATTENDANCE_OVERVIEW extends Module{		
	var $status_list = array("total_employee"=>"Total Employee", "clock_in"=>"Clock in", "late_entry"=>"Late Entry", "absent"=>"Absent", "on_leave"=>"On Leave", "no_shift"=>"No Shift");

	function __construct($title){
		global $sessioninfo;
		
		if(!isset($_REQUEST['date']))	$_REQUEST['date'] = date("Y-m-d");
		if(!isset($_REQUEST['branch_id']))    $_REQUEST['branch_id'] = $sessioninfo['branch_id'];
		parent::__construct($title);
	}
	
	private function init_load(){
		global $smarty, $appCore;

		$bid = mi($_REQUEST['branch_id']);
		$date = trim($_REQUEST['date']);
		
		//load branch
		$this->branch_list = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign('branch_list', $this->branch_list);
		
		//load dashboard data
		$attendance_status = array();
		$attendance_status = $this->load_attendance_status($bid, $date);
		$smarty->assign("attendance_status", $attendance_status['status']);
	}
	
	//get user attendance list
	function load_attendance_user_list($bid, $date){
		global $con, $smarty, $sessioninfo, $config, $appCore;
		
		// Load User List from Shift
		$tmp_user_list1 = $appCore->attendanceManager->loadShiftUserList($bid, $date);
		if(!$tmp_user_list1)	$tmp_user_list1 = array();
		
		// Load User List from Daily Record
		$tmp_user_list2 = $appCore->attendanceManager->loadAttendanceUserList($bid, $date);
		if(!$tmp_user_list2)	$tmp_user_list2 = array();
		$user_list = $tmp_user_list1 + $tmp_user_list2;
		
		return $user_list;
	}
	
	//get attendance data
	function load_attendance_status($bid, $date){
		global $con, $sessioninfo, $appCore;
		
		//get attendance user list
		$attendance_user_list = $this->load_attendance_user_list($bid, $date);

		//attendance status list
		$total_employee = count($attendance_user_list);
		$clock_in = 0;
		$late_entry = 0;
		$absent=0;
		$on_leave = 0;
		$no_shift = 0;
		
		$dept_clockin_list = $dept_absent_list = array();
		if($attendance_user_list){
			$time_attendance_setting = $user_list = $filter =  $data = array();
			
			//get time attendance setting list 
			$setting_list = array("in_early", "in_late");
			foreach($setting_list as $setting_name){
				$q_s = $con->sql_query("select setting_value from system_settings where setting_name=".ms($setting_name));
				$r = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				if($r['setting_value']) $time_attendance_setting[$setting_name] = $r['setting_value'];
				else $time_attendance_setting[$setting_name] = 0;
			}
		
			// Store as minute
			$in_early = mi($time_attendance_setting['in_early'])*60*-1;	// Early need to be negative
			$in_late = mi($time_attendance_setting['in_late'])*60;
			
			//get user id from attendance user list
			foreach($attendance_user_list as $user_id=>$user_info){
				$user_list[] = $user_info['user_id'];
			}
			
			//filter
			$filter[] = "branch_id=$bid and date=".ms($date);
			$filter[] = "user_id in (".join(',', $user_list).")";
			$str_filter = "where ".join(' and ', $filter);
		
			//Get User Shift List
			$q1 = $con->sql_query("select atts.*, ats.start_time, u.user_dept
				from attendance_shift_user atts
				left join attendance_shift ats on ats.id=atts.shift_id
				left join user u on atts.user_id = u.id
				$str_filter and atts.shift_id>0");
			while($r = $con->sql_fetchassoc($q1)){
				$user_id = mi($r['user_id']);
				$data[$user_id]['shift_id'] = $r['shift_id'];
				$data[$user_id]['user_dept'] = $r['user_dept'];
				$data[$user_id]['start_time'] = $r['start_time'];
			}
			$con->sql_freeresult($q1);
			
			
			// Daily Attendance Recird
			$q1 = $con->sql_query($sql="select atudr.*, u.user_dept
				from attendance_user_daily_record atudr
				left join user u on atudr.user_id = u.id
				$str_filter");
			while($r = $con->sql_fetchassoc($q1)){
				$user_id = mi($r['user_id']);
				$data[$user_id]['shift_id'] = $r['shift_id'];
				$data[$user_id]['user_dept'] = $r['user_dept'];
				$data[$user_id]['start_time'] = $r['start_time'];
				// Scan Record
				$q2 = $con->sql_query("select * 
					from attendance_user_scan_record
					where branch_id=$bid and date=".ms($r['date'])." and user_id=$user_id order by scan_time");
				$pair_no = 0;
				while($r2 = $con->sql_fetchassoc($q2)){
					if(isset($data[$user_id]['scan_records_pair'][$pair_no])){
						if(count($data[$user_id]['scan_records_pair'][$pair_no]['scan_record'])>=2){
							$pair_no++;
						}
					}
					$data[$user_id]['scan_records_pair'][$pair_no]['scan_record'][] = $r2;
				}
				$con->sql_freeresult($q2);
			}
			$con->sql_freeresult($q1);
			

			// Calculate Result
			if($data){
				foreach($data as $user_id => $user_data){
					if(!$data[$user_id]['scan_records_pair'])	continue;
					
					//get user department
					$user_dept = $user_data['user_dept'];
					if($user_data['user_dept'] == '') $user_dept = "No Department";

					//no shift
					if(!$user_data['shift_id']){
						$no_shift += 1;
						$clock_in += 1;
						$dept_clockin_list[$user_dept] += 1;
						continue;
					}

					// Check First Scan
					foreach($user_data['scan_records_pair'] as $pair_no => $pair_data){
						$diff_in = strtotime($pair_data['scan_record'][0]['scan_time']) - strtotime($data[$user_id]['start_time']);
						if($diff_in >= $in_late){	// Check Late in and clock in
							$clock_in += 1;
							$dept_clockin_list[$user_dept] += 1;
							$late_entry +=1;
							continue 2;
						}elseif($diff_in <= $in_early){	// Check clock in
							$clock_in += 1;
							$dept_clockin_list[$user_dept] += 1;
							continue 2;
						}elseif(count($pair_data['scan_record'])<=1){
							//check clock in
							$clock_in += 1;
							$dept_clockin_list[$user_dept] += 1;
							continue 2;
						}
					}
				}
		
				// Check attendance leave
				foreach($data as $user_id => $user_data){
					$leave_data = $appCore->attendanceManager->getUserAttendanceLeaveRecord($user_id, $bid, '', array('date_from'=>$date));
					if($leave_data){
						$data[$user_id]['leave_data'] = $leave_data[0];
						if(!$user_data['scan_records_pair']){
							$on_leave += 1;
						}
					}
				}
				
				// Check attendance absent
				foreach($data as $user_id => $user_data){
					if(!$user_data['scan_records_pair'] && !$user_data['leave_data']){
						$absent += 1;
						$user_dept = $user_data['user_dept'];
						
						//assign no department if not set department
						if(!$user_data['user_dept'] || $user_data['user_dept'] == '') $user_dept = "No Department";
						$dept_absent_list[$user_dept] += 1;
					}
				}
			}
		}
		
		$attendance_status = array();
		$attendance_status['department']['absent'] = $dept_absent_list;
		$attendance_status['department']['clock_in'] = $dept_clockin_list;
		$attendance_status['status']['total_employee'] = $total_employee;
		$attendance_status['status']['clock_in'] = $clock_in;
		$attendance_status['status']['late_entry'] = $late_entry;
		$attendance_status['status']['absent'] = $absent;
		$attendance_status['status']['on_leave'] = $on_leave;
		$attendance_status['status']['no_shift'] = $no_shift;
		
		return $attendance_status;
	}
	
	function _default(){
		$this->init_load();
		$this->display();
	}
	
	function ajax_dept_attendance_ratio(){
		$data = $ret = array();
		$date = trim($_REQUEST['date']);
		$bid = mi($_REQUEST['branch_id']);
		$data = $this->load_attendance_status($bid, $date);
		
		if($data['department']){
			foreach($data['department'] as $status=>$dept_list){
				$ret['department_attendance_ratio']['status'][] = $this->status_list[$status];
				foreach($dept_list as $dept=>$val){
					$ret['department_attendance_ratio']['dept'][$dept][$this->status_list[$status]] = $val;
				}
			}
			ksort($ret['department_attendance_ratio']);
		}
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	
	function ajax_employee_daily_working_ratio(){
		$data = $ret = array();
		$bid = mi($_REQUEST['branch_id']);
		$date = trim($_REQUEST['date']);
		$data = $this->load_attendance_status($bid, $date);
		
		unset($this->status_list['total_employee']);
		
		$has_data = 0;
		if($data['status']){
			foreach($data['status'] as $status=>$val){
				if($val > 0){
					$has_data = 1;
					$data[$status] = $val;
				}
			}
			
			if($has_data ){
				foreach($this->status_list as $status=>$status_name){
					$ret['employee_daily_ratio'][$status]['status'] = $status_name;
					$ret['employee_daily_ratio'][$status]['count'] = $data['status'][$status];
				}
			}
		}
		$ret['ok'] = 1;
		print json_encode($ret);
	}

	function ajax_recent_employee_daily_working_ratio(){
		$data = $ret = array();
		$date = trim($_REQUEST['date']);
		$bid = mi($_REQUEST['branch_id']);
		$recent_date = 7;  //to get 7 day data
		
		$has_data = 0;
		for($i=0; $i < $recent_date;$i++){
			$rct_date = date('Y-m-d', strtotime('-'.$i.' day', strtotime($date)));
			$data = $this->load_attendance_status($bid, $rct_date);
			foreach($data['status'] as $status=>$val){
				if($val > 0) $has_data = 1; 
				$ret['recent_employee_daily_ratio'][$status]['status'] = $this->status_list[$status];
				$ret['recent_employee_daily_ratio'][$status]['date'][$rct_date]['count'] = $val;
				$ret['recent_employee_daily_ratio'][$status]['date'][$rct_date]['year'] = date("Y", strtotime($rct_date));
				$ret['recent_employee_daily_ratio'][$status]['date'][$rct_date]['month'] = date("m", strtotime($rct_date));
				$ret['recent_employee_daily_ratio'][$status]['date'][$rct_date]['day'] = date("d", strtotime($rct_date));
			}
		}
		if($has_data <= 0) unset($ret); 
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
}
$ATTENDANCE_OVERVIEW = new ATTENDANCE_OVERVIEW('Time Management Dashboard ');
?>