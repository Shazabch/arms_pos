<?php
class API_ARMS_INTERNAL_USER {
	var $main_api = false;
	
	var $err_list = array(
		"time_attendance_cross_date" => "Time Attendance Cross Date, Please choose date.",
		"time_attendance_wrong_cross_date" => "The Selected Time Attendance Cross Date is invalid.",
		"user_id_invalid" => "User ID [%s] Not Found.",
		"user_id_inactive" => "User ID [%s] Not Active.",
		"user_id_locked" => "User ID [%s] is Locked.",
		"invalid_override_user" => 'Cannot Use Current User To Override User.',
		"invalid_data" => 'Invalid %s.'
	);
	
	var $sync_server_compatible_api = array('check_user_can_override');
	
	function __construct($main_api){
		$this->main_api = $main_api;
	}
	
	function is_api_support_sync_server($api_name){
		if(in_array($api_name, $this->sync_server_compatible_api)){
			return true;
		}
		return false;
	}
	
	function get_user_count(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		//if(!$this->main_api->user){
		//	$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		//}
				
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		
		$filter = array();
		//$filter[] = "si.active=1";
		
		if($min_changes_row_index > 0){
			$xtra_join = "left join tmp_trigger_log tmp on tmp.tablename='user' and tmp.id=user.id";
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Count all sku
		$this->main_api->put_log("Checking User Count.");	
		$con->sql_query("select count(*) as c 
			from user
			$xtra_join
			$str_filter");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		$ret['count'] = $tmp['c'];
				
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	public function get_user_list(){
		global $con;
				
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		//if(!$this->main_api->user){
		//	$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		//}
		
		$start_from = mi($_REQUEST['start_from']);
		$limit_count = mi($_REQUEST['limit']);
		$user_id = mi($_REQUEST['user_id']);
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		$with_finger_print_data = mi($_REQUEST['with_finger_print_data']);
		
		$filter = array();
		
		if($user_id>0){
			$filter[] = "user.id = ".mi($user_id);
		}
		
		if(!$user_id && $start_from >= 0 && $limit_count > 0){
			$limit = "limit $start_from, $limit_count";
		}
		
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}

		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		$sql = "select user.*, ifnull(tmp.row_index,0) as changes_row_index, ufp.fingerprint1, ufp.fingerprint2, ufp.fingerprint3, ufp.fingerprint4, ufp.allowed_time_attendance_branch

			from user
			left join tmp_trigger_log tmp on tmp.tablename='user' and tmp.id=user.id
			left join user_finger_print ufp on ufp.user_id=user.id
			$str_filter
			order by changes_row_index
			$limit";
			
		//print $sql;exit;
		$q1 = $con->sql_query($sql);
		$user_data = array();
		while($r = $con->sql_fetchassoc($q1)){
			$tmp = array();
			$tmp['user_id'] = $r['id'];
			$tmp['u'] = $r['u'];
			$tmp['l'] = $r['l'];
			$tmp['fullname'] = $r['fullname'];
			$tmp['active'] = mi($r['active']);
			$tmp['locked'] = mi($r['locked']);
			$tmp['phone_1'] = trim($r['phone_1']);
			$tmp['profile_photo_url'] = trim($r['profile_photo_url']);
			if($with_finger_print_data){
				$tmp['is_arms_user'] = mi($r['is_arms_user']);
				$tmp['fingerprint1'] = trim($r['fingerprint1']);
				$tmp['fingerprint2'] = trim($r['fingerprint2']);
				$tmp['fingerprint3'] = trim($r['fingerprint3']);
				$tmp['fingerprint4'] = trim($r['fingerprint4']);
				$tmp['allowed_time_attendance_branch'] = unserialize($r['allowed_time_attendance_branch']);
			}
			$tmp['changes_row_index'] = $r['changes_row_index'];
			
			$user_data[] = $tmp;
		}
		$con->sql_freeresult($q1);
		
		$ret = array();
		$ret['result'] = 1;
		$ret['user_data'] = $user_data;
		unset($user_data);
		
		//print_r($ret);exit;
		// Return Data				
		$this->main_api->respond_data($ret);
	}
	
	function upload_user_profile_photo(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$user_id = mi($_REQUEST['user_id']);
		if($user_id<=0)	$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'user_id'), "invalid_data");
		
		// No File was Uploaded
		if(!isset($_FILES['profile_photo']))	$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'profile_photo'), "invalid_data");
		
		// Begin
		$con->sql_begin_transaction();
		
		$params = array();
		if($this->main_api->user){
			$params['log_user_id'] = $this->main_api->user['id'];
		}
		//print_r($params);exit;
		
		// Set Image
		$result = $appCore->userManager->setUserProfilePhoto($user_id, $_FILES['profile_photo'], $params);
		if(!$result['ok']){
			if($result['error_code'] && $result['error']){
				$this->main_api->error_die($result['error'], $result['error_code']);
			}else{
				$this->main_api->error_die($this->main_api->err_list["unknown_error"], "unknown_error");
			}
		}
		
		// Commit
		$con->sql_commit();
		
		$profile_photo_url = $result['profile_photo_url'];
	
		$ret = array();
		$ret['result'] = 1;
		$ret['profile_photo_url'] = $profile_photo_url;
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function update_user_finger_print(){
		global $con, $config, $appCore;
		
		$ret = array();
		$ret['result'] = 1;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$user_id = mi($_REQUEST['user_id']);
		if($user_id<=0)	$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'user_id'), "invalid_data");
		
		$fingerprint1 = trim($_REQUEST['fingerprint1']);
		$fingerprint2 = trim($_REQUEST['fingerprint2']);
		$fingerprint3 = trim($_REQUEST['fingerprint3']);
		$fingerprint4 = trim($_REQUEST['fingerprint4']);
		//$allowed_time_attendance_branch = json_decode($_REQUEST['allowed_time_attendance_branch'], true);
		//print_r($allowed_time_attendance_branch);exit;
		
		if(!$fingerprint1)	$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'fingerprint1'), "invalid_data");
		if(!$fingerprint2)	$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'fingerprint2'), "invalid_data");
		if(!$fingerprint3)	$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'fingerprint3'), "invalid_data");
		if(!$fingerprint4)	$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'fingerprint4'), "invalid_data");
		
		/*if($allowed_time_attendance_branch){
			foreach($allowed_time_attendance_branch as $r){
				
				if(!isset($r['branch_id']) || !isset($r['allowed'])){
					$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'allowed_time_attendance_branch'), "invalid_data");
				}
			}
		}*/
		
		// Begin
		$con->sql_begin_transaction();
		
		// Update User Finger Print
		$params = array();
		$params['log_user_id'] = $this->main_api->user['id'];
		$params['fingerprint1'] = $fingerprint1;
		$params['fingerprint2'] = $fingerprint2;
		$params['fingerprint3'] = $fingerprint3;
		$params['fingerprint4'] = $fingerprint4;
		
		/*if($allowed_time_attendance_branch){
			foreach($allowed_time_attendance_branch as $r){
				$params['allowed_time_attendance_branch'][$r['branch_id']] = mi($r['allowed']);
			}
		}*/
		$result = $appCore->userManager->setUserFingerPrint($user_id, $params);
		if(!$result['ok']){
			if($result['error_code'] && $result['error']){
				$this->main_api->error_die($result['error'], $result['error_code']);
			}else{
				$this->main_api->error_die($this->main_api->err_list["unknown_error"], "unknown_error");
			}
		}
		// Commit
		$con->sql_commit();
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function update_user_finger_print_time_attendance_branch(){
		global $con, $config, $appCore;
		
		$ret = array();
		$ret['result'] = 1;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$user_id = mi($_REQUEST['user_id']);
		if($user_id<=0)	$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'user_id'), "invalid_data");
		
		$allowed_time_attendance_branch = json_decode($_REQUEST['allowed_time_attendance_branch'], true);
		//print_r($allowed_time_attendance_branch);exit;
		
		if(!$allowed_time_attendance_branch || !is_array($allowed_time_attendance_branch)){
			$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'allowed_time_attendance_branch'), "invalid_data");
		}
				
		foreach($allowed_time_attendance_branch as $r){
			
			if(!isset($r['branch_id']) || !isset($r['allowed'])){
				$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'allowed_time_attendance_branch'), "invalid_data");
			}
		}
		
		
		// Begin
		$con->sql_begin_transaction();
		
		// Update User Finger Print
		$params = array();
		$params['log_user_id'] = $this->main_api->user['id'];		
		foreach($allowed_time_attendance_branch as $r){
			$params['allowed_time_attendance_branch'][$r['branch_id']] = mi($r['allowed']);
		}
		
		$result = $appCore->userManager->setUserFingerPrintTimeAttendanceBranch($user_id, $params);
		if(!$result['ok']){
			if($result['error_code'] && $result['error']){
				$this->main_api->error_die($result['error'], $result['error_code']);
			}else{
				$this->main_api->error_die($this->main_api->err_list["unknown_error"], "unknown_error");
			}
		}
		// Commit
		$con->sql_commit();
		
		// Return Data
		$this->main_api->respond_data($ret);
		
	}
	
	function time_attendance_clock_in_out(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		if(!$this->main_api->device_guid){
			$this->main_api->error_die($this->main_api->err_list["suite_device_invalid"], "suite_device_invalid");
		}
		
		// User
		$user_id = mi($_REQUEST['user_id']);
		if($user_id<=0)	$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'user_id'), "invalid_data");
		// Get User
		$user = $appCore->userManager->getUser($user_id);//, array('active'=>1, 'lock'=>0));
		if(!$user)	$this->main_api->error_die(sprintf($this->err_list["user_id_invalid"], $user_id), "user_id_invalid");
		if(!$user['active'])	$this->main_api->error_die(sprintf($this->err_list["user_id_inactive"], $user_id), "user_id_inactive");
		if($user['locked'])	$this->main_api->error_die(sprintf($this->err_list["user_id_locked"], $user_id), "user_id_locked");
			
		
		// Get Time Attendance Branch ID
		$app_branch_id = $this->main_api->app_branch_id;
		
		// Cutoff Hour
		$con->sql_query("select setting_value from pos_settings where branch_id=".$app_branch_id." and setting_name ='hour_start'");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$cutoff_h = mi($tmp['setting_value']);
		
		// Cutoff Minute
		$con->sql_query("select setting_value from pos_settings where branch_id=".$app_branch_id." and setting_name ='minute_start'");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$cutoff_m = mi($tmp['setting_value']);
		
		$cutoff_total_min = ($cutoff_h*60)+$cutoff_m;
		$grace_min = 180;
		$total_grace_min = $cutoff_total_min + $grace_min;
		
		$today_total_grace_min = strtotime(date("Y-m-d"))+($total_grace_min*60);
		$today_total_min = strtotime(date("Y-m-d H:i:00"));
		
		if($today_total_min <= $today_total_grace_min){
			// Need to Select which Cross Date
			$selected_date = trim($_REQUEST['selected_date']);
			$date_list = array();
			$date_list[] = date("Y-m-d");
			$date_list[] = date("Y-m-d", strtotime("-1 day"));
			
			if($selected_date){	// Got Select Cross Date
				// Wrong Format
				if(!$appCore->isValidDateFormat($selected_date))	$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'selected_date'), "invalid_data");
				
				// The selected date is not allow to choose
				if(!in_array($selected_date, $date_list))	$this->main_api->error_die($this->err_list["time_attendance_wrong_cross_date"], "time_attendance_wrong_cross_date");
			}else{
				// Need Select Cross Date
				$extra_error = array();
				$extra_error['select_date'] = $date_list;
				
				// Return error and ask user to select
				$this->main_api->error_die($this->err_list["time_attendance_cross_date"], "time_attendance_cross_date", $extra_error);
			}
		}else{
			$selected_date = date("Y-m-d");
		}
				
		// Begin Transaction
		$con->sql_begin_transaction();
		
		// Insert Attendance Record
		$time = time();
		//print "selected_date = $selected_date";
		$params = array();
		$params['suite_device_guid'] = $this->main_api->device_guid;
		$result = $appCore->attendanceManager->insertUserAttendanceRecord($app_branch_id, $user['id'], $selected_date, $time, 0, $_SERVER['SERVER_ADDR'], $params);
		if(!$result['ok']){
			if($result['error_code'] && $result['error']){
				$this->main_api->error_die($result['error'], $result['error_code']);
			}else{
				$this->main_api->error_die($this->main_api->err_list["unknown_error"], "unknown_error");
			}
		}
		
		// Commit Transaction
		$con->sql_commit();
		
		// Get info Screen
		// Get All Scan Record
		$all_scan = array();
		$con->sql_query("select * from attendance_user_scan_record 
			where branch_id=$app_branch_id and user_id=".mi($user_id)." and date=".ms($selected_date)." order by scan_time desc");
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
		
		$ret = array();
		$ret['result'] = 1;
		$ret['user_id'] = $user['id'];
		$ret['clock_time'] = date("Y-m-d H:i:s", $time);
		$ret['status'] = $status;
		$ret['last_work_sec'] = mi($last_work_sec);
		$ret['total_work_sec'] = mi($total_work_sec);
		$ret['break_duration'] = mi($break_duration);
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function check_user_can_override(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Check Branch
		$branch_id = mi($this->main_api->app_branch_id);
		if(!$branch_id)  $this->main_api->error_die(sprintf($this->err_list["invalid_data"], "Branch Id"), "invalid_data");
		
		$current_user_id = mi($_REQUEST['current_user_id']);
		$override_user_name = trim($_REQUEST['override_user_name']);
		$override_user_pass = trim($_REQUEST['override_user_pass']);
		$override_user_barcode = trim($_REQUEST['override_user_barcode']);
		$privilege_code = trim($_REQUEST['privilege_code']);
		
		if(!$privilege_code)	$this->main_api->error_die(sprintf($this->err_list["invalid_data"], 'Privilege Code'), "invalid_data");
		
		$filter = array();
		$filter[] = "user.active=1 and user.locked=0 and user.template=0";
		
		if($override_user_barcode){
			$filter[] = "md5(user.barcode)=".ms($override_user_barcode);
		}else{
			if(!$override_user_name || !$override_user_pass){
				$this->main_api->error_die($this->main_api->err_list["invalid_login"], "invalid_login");
			}
			$filter[] = "user.l=".ms($override_user_name)." and user.p=".ms($override_user_pass);
		}
		
		$str_filter = join(' and ', $filter);
		
		// Check User
		$con->sql_query($qry="select user.*
			from user where 
			$str_filter
			order by id limit 1");
		$user = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$user){	// User Not Found
			$this->main_api->error_die($this->main_api->err_list["invalid_login"], "invalid_login");
		}
		
		if($current_user_id && ($current_user_id == mi($user['id']))){
			$this->main_api->error_die($this->err_list["invalid_override_user"], "invalid_override_user", $extra_error);
		}
		
		//Check Privilege
		$filter2 = array();
		$filter2[] = "up.branch_id =".mi($branch_id);
		$filter2[] = "up.user_id=".mi($user['id']);
		$filter2[] = "up.privilege_code=".ms($privilege_code);
		
		$str_filter2 = join(' and ', $filter2);	
		
		$con->sql_query("select up.allowed as allowed
			from user_privilege up 
			left join privilege p on p.code = up.privilege_code
			where $str_filter2");
		$r = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$ret = array();
		$ret['result'] = 1;
		$ret['override_user_id'] = $user['id'];
		$ret['override_username'] = $user['u'];
		$ret['allowed'] = mi($r['allowed']);
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
}
?>