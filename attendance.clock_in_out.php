<?php
/*
12/3/2019 5:18 PM Andy
- Added branch checking to show error if user logged in to other branch.

12/31/2019 3:21 PM Andy
- Enhanced to capture ip when users clock in or out.

2/18/2020 11:21 AM Andy
- Fixed Time Attendance Clock need to filter not locked user.

11/11/2020 2:07 PM Andy
- Fixed login to other branch checking.

3/15/2021 11:38 AM Shane
- Added trim_barcode() function to trim barcode for speed99 card swipe format.

4/7/2021 20:19 PM Shane
- Enhanced system if is speed99, user only can login with swipe card format (;$barcode?;$barcode?), if login with ($barcode), system will reject.
*/
include("include/common.php");

$maintenance->check(439);

class CLOCK_IN_OUT extends Module{
	var $safe_key = 'asi20';
	var $branch_list = array();
	
	var $login_bid;
	var $counter_id;
	var $login_user_id;
	var $sign;
	
	function __construct($title)
	{
		// load all initial data
		$this->contruct_machine_info();
		
		// Check Branch
		$this->check_branch();
		
		parent::__construct($title);
	}
	
	/*private function init_load(){
		global $smarty, $appCore;
		
		$this->branch_list = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign('branch_list', $this->branch_list);
	}*/
	
	function _default(){
		// Validate Access
		if(!$this->validate_access()){
			log_br($this->login_user_id, 'ATTENDANCE', 0, "Open Time Attendance Clock Failed.", $this->login_bid);
			die("Invalid Access");
		}
		$this->load_default_data();
		
		$this->display();
	}
	
	private function check_branch(){
		global $sessioninfo;
		
		if($this->login_bid > 0 && $sessioninfo && $sessioninfo['branch_id'] != $this->login_bid){
			print_r($sessioninfo);
			die("Time Attendance Failed to proceed due to you already login to other branch.");
		}
	}
	
	private function load_default_data(){
		global $con, $smarty, $appCore;
		
		// Cutoff Hour
		$con->sql_query("select setting_value from pos_settings where branch_id=".$this->login_bid." and setting_name ='hour_start'");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$cutoff_h = mi($tmp['setting_value']);
		
		// Cutoff Minute
		$con->sql_query("select setting_value from pos_settings where branch_id=".$this->login_bid." and setting_name ='minute_start'");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$cutoff_m = mi($tmp['setting_value']);
		
		$cutoff_total_min = ($cutoff_h*60)+$cutoff_m;
		//print "cutoff_total_min = $cutoff_total_min";
		
		$smarty->assign('cutoff_total_min', $cutoff_total_min);
	}
		
	private function validate_access(){
		global $con, $appCore;
		
		if(isset($_SESSION['time_attendance']) && $this->login_bid){
			log_br($user_id, 'ATTENDANCE', 0, "Open Time Attendance Clock Success.");
			return true;
		}else{
			// Check Access Key
			$this->login_bid = $bid = mi($_REQUEST['branch_id']);
			$counter_id = mi($_REQUEST['counter_id']);
			$this->login_user_id = $user_id = mi($_REQUEST['user_id']);
			$time = trim($_REQUEST['time']);
			$sign = trim($_REQUEST['sign']);
			
			if(!$bid || !$counter_id || !$user_id || !$time || !$sign)	return false;
			
			$sign2 = md5($bid.$counter_id.$user_id.$time.$this->safe_key);
			//print "sign = $sign<br>";
			//print "sign2 = $sign2<br>";
			if($sign != $sign2)	return false;
			
			// Check Counter Exists
			$counter = $appCore->posManager->getCounter($bid, $counter_id, array('active'=>1));
			//print "counter = ";
			//print_r($counter);
			if(!$counter)	return false;
			
			$branch = $appCore->branchManager->getBranchInfo($bid);
			if(!$branch['active'])	return false;
			
			// Check Last Sign
			$con->sql_query("select * from attendance_clock_sign where branch_id=$bid and counter_id=$counter_id");
			$data = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($data){
				// Current time cannot less than last time
				if($time <= strtotime($data['time'])){
					return false;
				}
			}
			
			// Add Record
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['counter_id'] = $counter_id;
			$upd['sign'] = $sign;
			$upd['time'] = date("Y-m-d H:i:s", $time);
			$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into attendance_clock_sign ".mysql_insert_by_field($upd)." on duplicate key update
			sign=".ms($upd['sign']).",
			time=".ms($upd['time']).",
			last_update=CURRENT_TIMESTAMP");
			
			$_SESSION['time_attendance']['branch_id'] = $bid;
			$_SESSION['time_attendance']['counter_id'] = $counter_id;
			$_SESSION['time_attendance']['user_id'] = $user_id;
			$_SESSION['time_attendance']['sign'] = $sign;
			
			// change the login branch and redirect
			setcookie('arms_login_branch', $branch['code'], 0, "/");
			$_COOKIE['arms_login_branch'] = $branch['code'];
				
			//log_br($user_id, 'ATTENDANCE', 0, "Open Time Attendance Clock Success.");
			
			// Redirect without params
			header("Location: ".$_SERVER['PHP_SELF']);
			exit;
		}	
		
		
		return false;
	}
	
	private function contruct_machine_info(){
		if(isset($_SESSION['time_attendance'])){
			//print_r($_SESSION['time_attendance']);
			$this->login_bid = mi($_SESSION['time_attendance']['branch_id']);
			$this->counter_id = mi($_SESSION['time_attendance']['counter_id']);
			$this->login_user_id = mi($_SESSION['time_attendance']['user_id']);
			$this->sign = trim($_SESSION['time_attendance']['sign']);
			return true;
		}
		
		return false;
	}
	
	function generate_url(){
		unset($_SESSION['time_attendance']);
		
		$bid = mi($_REQUEST['branch_id']);
		$counter_id = mi($_REQUEST['counter_id']);
		$user_id = mi($_REQUEST['user_id']);
		$time = time();
		$sign = md5($bid.$counter_id.$user_id.$time.$this->safe_key);
		$auto_redirect = mi($_REQUEST['auto_redirect']);
		
		$params = "?branch_id=$bid&counter_id=$counter_id&user_id=$user_id&time=$time&sign=$sign";
		if($auto_redirect){
			header("Location: ".$_SERVER['PHP_SELF'].$params);
		}else{
			print "<a href=\"$params\">Link</a>";
		}
	}
	
	function get_server_time(){
		$ret = array();
		$ret['ok'] = 1;
		$ret['time'] = time();
		print json_encode($ret);
	}

	function trim_barcode($scanned_code){
		global $config;
		//99 Speedmart format (;$barcode?;$barcode?)
		$barcode = $scanned_code;
		if($config['speed99_settings']){
			//First char == ';', Last char == '?'
			if(substr($scanned_code,0,1) != ';' || substr($scanned_code,-1) != '?'){
				return false;
			}

			$tmp = explode('?;',$scanned_code);
			$barcode1 = str_replace(';','',$tmp[0]);
			$barcode2 = str_replace('?','',$tmp[1]);
			
			//Check if both part are same, else return original string.
			if($barcode1 == $barcode2){
				$barcode = $barcode1;
			}

			//Do not allow to manual key in $barcode
			if($barcode == $scanned_code){
				return false;
			}
		}

		return $barcode;
	}
	
	function ajax_submit_clock(){
		global $con, $appCore, $LANG, $config;
		
		// Check Barcode
		$barcode = trim($_REQUEST['barcode']);
		if(!$barcode)	die($LANG['SHIFT_CLOCK_BARCODE_EMPTY']);
		
		//Trim Barcode due to some integration has special format
		$barcode = $this->trim_barcode($barcode);
		if($barcode === false){
			die("Invalid format.");
		}
		// Get User
		$user = $appCore->userManager->getUserByBarcode($barcode, array('active'=>1, 'locked'=>0));
		if(!$user)	die($LANG['SHIFT_CLOCK_BARCODE_INVALID']);
		
		// Cross Day
		$selected_date = trim($_REQUEST['selected_date']);
		if(!$selected_date)	$selected_date = date("Y-m-d");
		
		// Not allow to scan more than 6 time per day
		$con->sql_query("select count(*) as c from attendance_user_scan_record 
			where branch_id=".mi($this->login_bid)." and user_id=".mi($user['id'])." and date=".ms($selected_date));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if($tmp['c']>=6){
			die($LANG['SHIFT_CLOCK_MAX_6_SCAN']);
		}
			
		// Begin Transaction
		$con->sql_begin_transaction();
		
		// Insert Attendance Record
		$time = time();
		//print "selected_date = $selected_date";
		$result = $appCore->attendanceManager->insertUserAttendanceRecord($this->login_bid, $user['id'], $selected_date, $time, $this->counter_id, $_SERVER['SERVER_ADDR']);
		if(!$result['ok']){
			die($result['error']);
		}
		
		// Commit Transaction
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['user_id'] = $user['id'];
		$ret['date'] = $selected_date;
		print json_encode($ret);
	}
	
	function show_info(){
		global $con, $appCore, $LANG, $smarty;
		
		$user_id = mi($_REQUEST['user_id']);
		$date = trim($_REQUEST['date']);
		
		if($user_id<=0 || !$date){
			die("Invalid Info");
		}
		
		// Get User
		$user = $appCore->userManager->getUser($user_id);
		if(!$user)	die($LANG['SHIFT_CLOCK_BARCODE_INVALID']);
		
		// Get info Screen
		// Get All Scan Record
		$all_scan = array();
		$con->sql_query("select * from attendance_user_scan_record 
			where branch_id=".mi($this->login_bid)." and user_id=".mi($user_id)." and date=".ms($date)." order by scan_time desc");
		while($r = $con->sql_fetchassoc()){
			$all_scan[] = $r;
		}
		$con->sql_freeresult();
		
		if($all_scan){
			$count = count($all_scan);
			if($count == 1){	// only 1 row - start working
				$status = 'start_work';
				
				$record_1 = $all_scan[0];	// take first record
			}elseif($count % 2 == 0){	// can be devided by 2 - leave work
				$status = 'leave_work';
				
				$record_1 = array();
				$last_record = array();
				$last_work_sec = false;
				$total_work_sec = false;
				foreach($all_scan as $row => $scan_record){
					if(!$record_1){
						$record_1 = $scan_record;
					}
					
					if(!$last_record){
						$last_record = $scan_record;
					}else{
						if($last_work_sec === false){
							$last_work_sec = strtotime($last_record['scan_time']) - strtotime($scan_record['scan_time']);
						}
						$total_work_sec += strtotime($last_record['scan_time']) - strtotime($scan_record['scan_time']);
						$last_record = array();
					}
				}
			}elseif($count % 2 == 1){	// cannot be devided by 2 - come back work
				$status = 'end_break';
				
				$record_1 = $record_2 = array();
				foreach($all_scan as $row => $scan_record){
					if(!$record_1){
						$record_1 = $scan_record;
					}elseif(!$record_2){
						$record_2 = $scan_record;
					}else{
						break;	// only take last two records
					}
				}
				$break_duration = strtotime($record_1['scan_time']) - strtotime($record_2['scan_time']);
			}
			
			
			
		}else{
			// something wrong here
		}
		
		//print_r($record_1);
		$smarty->assign('status', $status);
		$smarty->assign('record_1', $record_1);
		$smarty->assign('last_work_sec', $last_work_sec);
		$smarty->assign('total_work_sec', $total_work_sec);
		$smarty->assign('break_duration', $break_duration);
		$smarty->assign('user', $user);
		
		$smarty->display('attendance.clock_in_out.info.tpl');
	}
}

$CLOCK_IN_OUT = new CLOCK_IN_OUT('Attendance Clock');
?>