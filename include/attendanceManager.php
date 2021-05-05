<?php
/*
12/20/2019 4:47 PM Andy
- Added attendanceManager function for "Public Holiday" and "Leave".
- Enhanced "insertUserAttendanceRecord" to accept parameter "ip".
- Enhanced "getUserAttendanceDailyRecord" to accept params.check_got_modify.
*/

class attendanceManager {
	function __construct(){
		
	}
	
	public function generateDefaultShiftTable($params = array()){
		global $con, $LANG, $sessioninfo;
		
		$con->sql_query("select count(*) as c from attendance_shift");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$shift_count = mi($tmp['c']);
		if($shift_count>0){
			// Already got shift
			return array('error' =>$LANG['SHIFT_TABLE_GOT_DATA']);
		}
		
		$user_id = mi($params['user_id']);
		if(!$user_id){
			$user_id = $sessioninfo ? $sessioninfo['id'] : 1;
		}
		
		// Full Day
		$upd = array();
		$upd['code'] = 'F';
		$upd['description'] = 'Full Day';
		$upd['shift_color'] = '48ff00';
		$upd['start_time'] = '09:00';
		$upd['end_time'] = '22:00';
		$upd['break_1_start_time'] = '12:00';
		$upd['break_1_end_time'] = '13:00';
		$upd['break_2_start_time'] = '18:00';
		$upd['break_2_end_time'] = '19:00';
		$upd['active'] = 1;
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into attendance_shift ".mysql_insert_by_field($upd));
		
		// Morning
		$upd = array();
		$upd['code'] = 'M';
		$upd['description'] = 'Morning';
		$upd['shift_color'] = '0000ff';
		$upd['start_time'] = '09:00';
		$upd['end_time'] = '18:00';
		$upd['break_1_start_time'] = '12:00';
		$upd['break_1_end_time'] = '13:00';
		$upd['active'] = 1;
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into attendance_shift ".mysql_insert_by_field($upd));
		
		// Night
		$upd = array();
		$upd['code'] = 'N';
		$upd['description'] = 'Night';
		$upd['shift_color'] = 'ffcc00';
		$upd['start_time'] = '12:00';
		$upd['end_time'] = '22:00';
		$upd['break_1_start_time'] = '18:00';
		$upd['break_1_end_time'] = '19:00';
		$upd['active'] = 1;
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into attendance_shift ".mysql_insert_by_field($upd));
		
		log_br($user_id, 'ATTENDANCE', 0, "Generated Default Shift Table");
		
		$ret = array();
		$ret['ok'] = 1;
		return $ret;
	}
	
	public function generateDefaultLeaveTable($params = array()){
		global $con, $LANG, $sessioninfo;
		
		$con->sql_query("select count(*) as c from attendance_leave");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$data_count = mi($tmp['c']);
		if($data_count>0){
			// Already got shift
			return array('error' =>$LANG['LEAVE_TABLE_GOT_DATA']);
		}
		
		$user_id = mi($params['user_id']);
		if(!$user_id){
			$user_id = $sessioninfo ? $sessioninfo['id'] : 1;
		}
		
		// Full Day
		$upd = array();
		$upd['code'] = 'ANL';
		$upd['description'] = 'Annual Leave';
		$upd['active'] = 1;
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into attendance_leave ".mysql_insert_by_field($upd));
		
		// Morning
		$upd = array();
		$upd['code'] = 'MC';
		$upd['description'] = 'Medical Leave';
		$upd['active'] = 1;
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into attendance_leave ".mysql_insert_by_field($upd));
		
		log_br($user_id, 'ATTENDANCE', 0, "Generated Default Leave Table");
		
		$ret = array();
		$ret['ok'] = 1;
		return $ret;
	}
	
	public function getShiftList($params = array()){
		global $con, $LANG;
		
		$filter = array();
		if(isset($params['active']))	$filter[] = "active=".mi($params['active']);
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		$sql = "select * from attendance_shift 
		$str_filter
		order by id";
		$shift_list = array();
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
			$shift_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		return $shift_list;
	}
	
	public function getShift($shift_id, $shift_code=''){
		global $con;
		
		$shift_id = mi($shift_id);
		$shift_code = trim($shift_code);
		
		$filter = array();
		if($shift_id)	$filter[] = "id=$shift_id";
		if($shift_code)	$filter[] = "code=".ms($shift_code);
		
		if(!$filter)	return false;
		
		$str_filter = "where ".join(' and ', $filter);
		$con->sql_query("select * from attendance_shift $str_filter");
		$shift = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$shift)	return false;
		
		return $shift;
	}
	
	public function updateShiftActive($shift_id, $is_active, $params = array()){
		global $con, $LANG, $sessioninfo;
		
		$shift_id = mi($shift_id);
		$is_active = mi($is_active);
		$user_id = mi($params['user_id']);
		if(!$user_id){
			$user_id = $sessioninfo ? $sessioninfo['id'] : 1;
		}
		
		if(!$shift_id)	return array('error' => $LANG['SHIFT_ID_INVALID']);
		
		// Get Shift
		$shift = $this->getShift($shift_id);
		if(!$shift)		return array('error' => sprintf($LANG['SHIFT_ID_NOT_FOUND'], $shift_id));
		
		$upd = array();
		$upd['active'] = $is_active;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update attendance_shift set ".mysql_update_by_field($upd)." where id=$shift_id");
		
		$str_log = ($is_active ? 'Activated':'Deactivated')." Shift, Code: ".$shift['code'];
		log_br($user_id, 'ATTENDANCE', $shift_id, $str_log);
		
		$ret = array();
		$ret['ok'] = 1;
		return $ret;
	}
	
	public function getUserAttendanceShift($bid, $user_id, $date){
		global $con, $LANG, $sessioninfo, $appCore;
		
		// Get User Daily Record
		$con->sql_query("select atsu.*, ats.code as shift_code, ats.description as shift_description, ats.shift_color, ats.start_time, ats.end_time, ats.break_1_start_time, ats.break_1_end_time, ats.break_2_start_time, ats.break_2_end_time
			from attendance_shift_user atsu
			left join attendance_shift ats on ats.id=atsu.shift_id
			where branch_id=".mi($bid)." and user_id=".mi($user_id)." and date=".ms($date));
		$user_shift = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $user_shift;
	}
	
	public function getUserAttendanceDailyRecord($bid, $user_id, $date_from, $date_to = '', $params = array()){
		global $con, $LANG, $sessioninfo, $appCore;
		
		$filter = array();
		$filter[] = "branch_id=".mi($bid)." and user_id=".mi($user_id);
		if(!$date_to){	// select only single date
			$filter[] = "date=".ms($date_from);
		}else{
			$filter[] = " date between ".ms($date_from)." and ".ms($date_to);
		}
		
		$str_filter = "where ".join(' and ', $filter);
		
		$get_scan_records = isset($params['get_scan_records']) ? mi($params['get_scan_records']) : 0;
		$check_got_modify = isset($params['check_got_modify']) ? mi($params['check_got_modify']) : 0;
		
		// Get User Daily Record
		$q1 = $con->sql_query("select * from attendance_user_daily_record $str_filter order by date");
		$record_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			// Need to Get Scan Records
			if($get_scan_records){
				$r['scan_record_list'] = array();
				$q2 = $con->sql_query("select * 
					from attendance_user_scan_record
					where branch_id=$bid and date=".ms($r['date'])." and user_id=$user_id order by scan_time");
				while($r2 = $con->sql_fetchassoc($q2)){
					$r['scan_record_list'][] = $r2;
				}
				$con->sql_freeresult($q2);
			}
			
			// Need to check if got modify
			if($check_got_modify){
				$con->sql_query("select count(*) as c
					from attendance_user_daily_record_modify_history
					where branch_id=$bid and date=".ms($r['date'])." and user_id=$user_id");
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				$r['modify_count'] = mi($tmp['c']);
			}
			
			$record_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		// No Data
		if(!$record_list)	return false;
		
		if(!$date_to){	// select only single date
			return $record_list[0];
		}
		
		return $record_list;
	}
	
	public function createUserAttendanceDailyRecord($bid, $user_id, $date){
		global $con, $LANG, $sessioninfo, $appCore;
		
		// Get User Daily Record
		$daily_record = $this->getUserAttendanceDailyRecord($bid, $user_id, $date);
		if(!$daily_record){
			// Only create if not yet add
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['user_id'] = $user_id;
			$upd['date'] = $date;
			$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into attendance_user_daily_record ".mysql_insert_by_field($upd));
			
			$this->updateUserAttendanceDailyRecordShift($bid, $user_id, $date);
			return true;
		}
		
		return false;
	}
	
	public function updateUserAttendanceDailyRecordShift($bid, $user_id, $date, $params = array()){
		global $con, $LANG, $sessioninfo, $appCore;
		
		$force_update = isset($params['force_update']) ? mi($params['force_update']) : 0;
		$user_shift = isset($params['user_shift']) ? $params['user_shift'] : array();
		
		// Get User Daily Record
		$daily_record = $this->getUserAttendanceDailyRecord($bid, $user_id, $date);
		//print_r($daily_record);exit;
		if(!$daily_record)	return false;	// Record Not Found
		
		if(!$daily_record['shift_id'] || $force_update){
			// Get User Shift
			if(!$user_shift){
				$user_shift = $this->getUserAttendanceShift($bid, $user_id, $date);
			}			
			//print "attendance_user_daily_record ";exit;
			
			// Got Assigned Shift
			if($user_shift['shift_id']){
				$upd = array();
				$upd['shift_id'] = $user_shift['shift_id'];
				$upd['shift_code'] = $user_shift['shift_code'];
				$upd['shift_description'] = $user_shift['shift_description'];
				$upd['shift_color'] = $user_shift['shift_color'];
				
				// Start Time
				$latest_time = $upd['start_time'] = $start_time = date("Y-m-d H:i:s", strtotime($date.' '.$user_shift['start_time']));
				
				// Break 1
				if($user_shift['break_1_start_time'] && $user_shift['break_1_end_time'] && $user_shift['break_1_start_time'] != '00:00:00' && $user_shift['break_1_end_time'] != '00:00:00'){
					// break 1 start_time
					$break_1_start_time = date("Y-m-d H:i:s", strtotime($date.' '.$user_shift['break_1_start_time']));
					if(strtotime($break_1_start_time) < strtotime($latest_time)){
						$break_1_start_time = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($break_1_start_time)));
					}
					$latest_time = $break_1_start_time;
					$upd['break_1_start_time'] = $break_1_start_time;
					
					// break 1 end_time
					$break_1_end_time = date("Y-m-d H:i:s", strtotime($date.' '.$user_shift['break_1_end_time']));
					if(strtotime($break_1_end_time) < strtotime($latest_time)){
						$break_1_end_time = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($break_1_end_time)));
					}
					$latest_time = $break_1_end_time;
					$upd['break_1_end_time'] = $break_1_end_time;
				}else{
					$upd['break_1_start_time'] = '';
					$upd['break_1_end_time'] = '';
				}
				
				// Break 2
				if($user_shift['break_2_start_time'] && $user_shift['break_2_end_time'] && $user_shift['break_2_start_time'] != '00:00:00' && $user_shift['break_2_end_time'] != '00:00:00'){
					// break 2 start_time
					$break_2_start_time = date("Y-m-d H:i:s", strtotime($date.' '.$user_shift['break_2_start_time']));
					if(strtotime($break_2_start_time) < strtotime($latest_time)){
						$break_2_start_time = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($break_2_start_time)));
					}
					$latest_time = $break_2_start_time;
					$upd['break_2_start_time'] = $break_2_start_time;
					
					// break 2 end_time
					$break_2_end_time = date("Y-m-d H:i:s", strtotime($date.' '.$user_shift['break_2_end_time']));
					if(strtotime($break_2_end_time) < strtotime($latest_time)){
						$break_2_end_time = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($break_2_end_time)));
					}
					$latest_time = $break_2_end_time;
					$upd['break_2_end_time'] = $break_2_end_time;
				}else{
					$upd['break_2_start_time'] = '';
					$upd['break_2_end_time'] = '';
				}
				
				// End Time
				$end_time = date("Y-m-d H:i:s", strtotime($date.' '.$user_shift['end_time']));
				if(strtotime($end_time) < strtotime($latest_time)){
					$end_time = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($end_time)));
				}
				$latest_time = $end_time;
				$upd['end_time'] = $end_time;
				
				$upd['last_update'] = 'CURRENT_TIMESTAMP';
				$con->sql_query($sql = "update attendance_user_daily_record set ".mysql_update_by_field($upd)." where branch_id=".mi($bid)." and user_id=".mi($user_id)." and date=".ms($date));
				//print $sql;exit;
				return true;
			}
		}
		return false;
	}
	
	
	public function insertUserAttendanceRecord($bid, $user_id, $date, $unix_time, $counter_id = 0, $ip = ''){
		global $con, $LANG, $sessioninfo, $appCore;
		
		//print "bid = $bid";exit;
		
		$bid = mi($bid);
		$user_id = mi($user_id);
		$counter_id = mi($counter_id);
		$date = trim($date);
		$unix_time = mi($unix_time);
		
		if($bid<=0)	return array('error' => $LANG['SHIFT_BRANCH_INVALID']);
		if($user_id<=0)	return array('error' => $LANG['SHIFT_CLOCK_USER_ID_INVALID']);
		if(!$appCore->isValidDateFormat($date))	return array('error' => $LANG['INVALID_DATE_FORMAT']);
		if($unix_time<=0)	return array('error' => $LANG['SHIFT_CLOCK_TIME_INVALID']);
		
		// Create Daily Record
		$daily_record = $this->createUserAttendanceDailyRecord($bid, $user_id, $date);
		
		// Get User Daily Record
		$daily_record = $this->getUserAttendanceDailyRecord($bid, $user_id, $date);
		
		if(!$daily_record){
			// Failed to insert daily record
			die("Failed to create record");
		}else{
			// insert scan record
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['user_id'] = $user_id;
			$upd['date'] = $date;
			$upd['counter_id'] = $counter_id;
			$upd['scan_time'] = date("Y-m-d H:i:s", $unix_time);
			$upd['ip'] = $ip;
			$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into attendance_user_scan_record ".mysql_insert_by_field($upd));
		}
		
		$ret = array();
		$ret['ok'] = 1;
		return $ret;
	}
	
	public function loadAttendanceUserList($bid, $date_from, $date_to='', $params = array()){
		global $con;
		
		$bid = mi($bid);
		$date_from = trim($date_from);
		$date_to = trim($date_to);
		if(!$date_to)	$date_to = $date_from;	// same date if date_to no provided
		
		if($bid<=0 || !$date_from || !$date_to)	return false;
		
		$str_order_by = 'order by user.u';
		if(isset($params['order_by']))	$str_order_by = trim($params['order_by']);
		
		$sql = "select distinct(atudr.user_id) , user.u, user.fullname
			from attendance_user_daily_record atudr
			join user on user.id=atudr.user_id
			where atudr.branch_id=$bid and atudr.date between ".ms($date_from)." and ".ms($date_to)."
			$str_order_by";
		$q1 = $con->sql_query($sql);
		$user_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$user_list[$r['user_id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		return $user_list;
	}
	
	public function loadShiftUserList($bid, $date_from, $date_to='', $params = array()){
		global $con;
		
		$bid = mi($bid);
		$date_from = trim($date_from);
		$date_to = trim($date_to);
		if(!$date_to)	$date_to = $date_from;	// same date if date_to no provided
		
		if($bid<=0 || !$date_from || !$date_to)	return false;
		
		$str_order_by = 'order by user.u';
		if(isset($params['order_by']))	$str_order_by = trim($params['order_by']);
		
		$filter = array();
		$filter[] = "atts.shift_id>0";
		$filter[] = "atts.branch_id=$bid and atts.date between ".ms($date_from)." and ".ms($date_to);
		
		$str_filter = "where ".join(' and ', $filter);
		$sql = "select distinct(atts.user_id) , user.u, user.fullname
			from attendance_shift_user atts
			join user on user.id=atts.user_id
			$str_filter
			$str_order_by";
		$q1 = $con->sql_query($sql);
		$user_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$user_list[$r['user_id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		return $user_list;
	}
	
	public function deleteUserAttendanceDailyRecord($bid, $user_id, $date, $params = array()){
		global $con, $sessioninfo;
		
		$bid = mi($bid);
		$user_id = mi($user_id);
		$date = trim($date);
		
		if($bid<=0 || $user_id <=0 || !$date)	return false;
				
		// Get User Record
		$daily_record = $this->getUserAttendanceDailyRecord($bid, $user_id, $date);
		if(!$daily_record)	return false;
		
		// Delete Daily Record
		$con->sql_query("delete from attendance_user_daily_record where branch_id=$bid and user_id=$user_id and date=".ms($date));
		
		// Delete Scan Record
		$con->sql_query("delete from attendance_user_scan_record where branch_id=$bid and user_id=$user_id and date=".ms($date));
				
		return true;
	}
	
	public function getPublicHolidayList($params = array()){
		global $con, $LANG;
		
		$filter = array();
		if(isset($params['active']))	$filter[] = "active=".mi($params['active']);
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		$sql = "select * from attendance_ph
		$str_filter
		order by code";
		$ph_list = array();
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
			$ph_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		return $ph_list;
	}
	
	public function getPublicHoliday($ph_id, $ph_code=''){
		global $con;
		
		$ph_id = mi($ph_id);
		$ph_code = trim($ph_code);
		
		$filter = array();
		if($ph_id)	$filter[] = "id=$ph_id";
		if($ph_code)	$filter[] = "code=".ms($ph_code);
		
		if(!$filter)	return false;
		
		$str_filter = "where ".join(' and ', $filter);
		$con->sql_query("select * from attendance_ph $str_filter");
		$ph = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$ph)	return false;
		
		return $ph;
	}
	
	public function updatePublicHolidayActive($ph_id, $is_active, $params = array()){
		global $con, $LANG, $sessioninfo;
		
		$ph_id = mi($ph_id);
		$is_active = mi($is_active);
		$user_id = mi($params['user_id']);
		if(!$user_id){
			$user_id = $sessioninfo ? $sessioninfo['id'] : 1;
		}
		
		if(!$ph_id)	return array('error' => $LANG['PH_ID_INVALID']);
		
		// Get Holiday
		$ph = $this->getPublicHoliday($ph_id);
		if(!$ph)		return array('error' => sprintf($LANG['PH_ID_NOT_FOUND'], $ph_id));
		
		$upd = array();
		$upd['active'] = $is_active;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update attendance_ph set ".mysql_update_by_field($upd)." where id=$ph_id");
		
		$str_log = ($is_active ? 'Activated':'Deactivated')." Public Holiday, Code: ".$ph['code'];
		log_br($user_id, 'ATTENDANCE', $ph_id, $str_log);
		
		$ret = array();
		$ret['ok'] = 1;
		return $ret;
	}
	
	public function getPublicHolidayYearData($id = 0, $y = 0){
		global $con;
		
		$id = mi($id);
		$y = mi($y);
		
		if($id<0 || $y<0)	return false;
		
		$filter = array();
		if($id > 0)	$filter[] = "id=$id";
		if($y > 0)	$filter[] = "y=$y";
		
		$str_filter = "where ".join(' and ', $filter);
		
		// Get Year Data
		$con->sql_query("select * from attendance_ph_year $str_filter");
		$ph_year = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$ph_year)	return false;
		
		$ph_year_id = mi($ph_year['id']);
		$ph_year['ph_list'] = array();
		
		
		$q1 = $con->sql_query("select phyi.*, ph.code as ph_code, ph.description as ph_description
			from attendance_ph_year_items phyi 
			join attendance_ph ph on ph.id=phyi.ph_id and ph.active=1
			where phyi.ph_year_id=$ph_year_id
			order by phyi.date_from");
		while($r = $con->sql_fetchassoc($q1)){
			$ph_year['ph_list'][$r['ph_id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		return $ph_year;
		
	}
	
	public function getPublicHolidayDataByDateRange($date_from, $date_to, $params = array()){
		global $con;
		
		if(!$date_from || !$date_to)	return false;
		
		$filter = $filter_or = array();
		//$filter[] = "ph.active=1";
		$filter_or[] = "phyi.date_from <= ".ms($date_from)." and ".ms($date_from)." <= phyi.date_to";
		$filter_or[] = "phyi.date_to >= ".ms($date_to)." and ".ms($date_to)." >= phyi.date_from";
		$filter_or[] = "phyi.date_from between ".ms($date_from)." and ".ms($date_to);
		$filter_or[] = "phyi.date_to between ".ms($date_from)." and ".ms($date_to);
		
		$filter[] = "(".join(' or ', $filter_or).")";
		
		$str_filter = "where ".join(' and ', $filter);
		
		$data['ph_list'] = array();
		
		$sql = "select phyi.*, ph.code as ph_code, ph.description as ph_description
			from attendance_ph_year_items phyi 
			join attendance_ph ph on ph.id=phyi.ph_id and ph.active=1
			$str_filter
			order by phyi.date_from";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$data['ph_list'][$r['ph_id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		return $data;		
	}
	
	function getLeaveList($params = array()){
		global $con, $LANG;
		
		$filter = array();
		if(isset($params['active']))	$filter[] = "active=".mi($params['active']);
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		$sql = "select * from attendance_leave
		$str_filter
		order by code";
		$leave_list = array();
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
			$leave_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		return $leave_list;
	}
	
	function getLeave($leave_id, $leave_code=''){
		global $con;
		
		$leave_id = mi($leave_id);
		$leave_code = trim($leave_code);
		
		$filter = array();
		if($leave_id)	$filter[] = "id=$leave_id";
		if($leave_code)	$filter[] = "code=".ms($leave_code);
		
		if(!$filter)	return false;
		
		$str_filter = "where ".join(' and ', $filter);
		$con->sql_query("select * from attendance_leave $str_filter");
		$leave = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$leave)	return false;
		
		return $leave;
	}
	
	function updateLeaveActive($leave_id, $is_active, $params = array()){
		global $con, $LANG, $sessioninfo;
		
		$leave_id = mi($leave_id);
		$is_active = mi($is_active);
		$user_id = mi($params['user_id']);
		if(!$user_id){
			$user_id = $sessioninfo ? $sessioninfo['id'] : 1;
		}
		
		if(!$leave_id)	return array('error' => $LANG['LEAVE_ID_INVALID']);
		
		// Get Holiday
		$form = $this->getLeave($leave_id);
		if(!$form)		return array('error' => sprintf($LANG['LEAVE_ID_NOT_FOUND'], $leave_id));
		
		$upd = array();
		$upd['active'] = $is_active;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update attendance_leave set ".mysql_update_by_field($upd)." where id=$leave_id");
		
		$str_log = ($is_active ? 'Activated':'Deactivated')." Leave, Code: ".$form['code'];
		log_br($user_id, 'ATTENDANCE', $leave_id, $str_log);
		
		$ret = array();
		$ret['ok'] = 1;
		return $ret;
	}
	
	public function getUserAttendanceLeaveRecord($user_id, $bid = 0, $guid = '', $params = array()){
		global $con, $LANG, $sessioninfo, $appCore;
		
		$date_from = trim($params['date_from']);
		$date_to = trim($params['date_to']);
		
		$filter = $filter_or = array();
		$filter[] = "user_id=".mi($user_id);
		if($bid>0)	$filter[] = "branch_id=".mi($bid);
		if($guid)	$filter[] = "guid=".ms($guid);
		
		if($date_from){
			if(!$date_to)	$date_to = $date_from;
			$filter_or[] = "alr.date_from <= ".ms($date_from)." and ".ms($date_from)." <= alr.date_to";
			$filter_or[] = "alr.date_to >= ".ms($date_to)." and ".ms($date_to)." >= alr.date_from";
			$filter_or[] = "alr.date_from between ".ms($date_from)." and ".ms($date_to);
			$filter_or[] = "alr.date_to between ".ms($date_from)." and ".ms($date_to);
			if($filter_or)	$filter[] = "(".join(' or ', $filter_or).")";	
		}	
				
		$str_filter = "where ".join(' and ', $filter);
		
		// Get User Leave Record
		$q1 = $con->sql_query("select alr.*, al.code as leave_code, al.description as leave_desc
		from attendance_user_leave_record alr
		left join attendance_leave al on al.id=alr.leave_id
		$str_filter 
		order by date_to desc");
		$record_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$record_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		// No Data
		if(!$record_list)	return false;
		
		if($guid){
			// Only select single data
			return $record_list[0];
		}
				
		return $record_list;
	}
	
	public function deleteUserAttendanceLeaveRecord($guid, $params = array()){
		global $con, $sessioninfo;
		
		$guid = trim($guid);
		
		if(!$guid)	return false;

		// Delete Leave Record
		$con->sql_query("delete from attendance_user_leave_record where guid=".ms($guid));
		$success = $con->sql_affectedrows();
				
		return $success;
	}
	
	public function getUserAttendanceDailyRecordModifiedData($bid, $user_id, $date_from='', $date_to=''){
		global $con, $LANG, $sessioninfo, $appCore;
		
		$bid = mi($bid);
		$user_id = mi($user_id);
		$date_from = trim($date_from);
		$date_to = trim($date_to);
		
		if($bid <= 0)	return array('error' => sprintf($LANG['INVALID_BRANCH_ID'], $bid));
		if($user_id <= 0)	return array('error' => sprintf($LANG['INVALID_DATA'], 'User ID', $user_id));
		if($date_from && !$appCore->isValidDateFormat($date_from))	return array('error' => sprintf($LANG['INVALID_DATA'], 'Date From', $date_from));
		if($date_to && !$appCore->isValidDateFormat($date_to))	return array('error' => sprintf($LANG['INVALID_DATA'], 'Date To', $date_to));
		if($date_from && $date_to && strtotime($date_to) < strtotime($date_from))	return array('error' => sprintf($LANG['DATE_TO_FROM_ERROR'], 'Date To', 'Date From'));
		
		$filter = array();
		$filter[] = "od.branch_id=$bid and od.user_id=$user_id";
		if($date_from)	$filter[] = "od.date>=".ms($date_from);
		if($date_to)	$filter[] = "od.date<=".ms($date_to);
		
		$str_filter = "where ".join(' and ', $filter);
		$sql = "select od.*, user.u as edit_by_user_u
			from attendance_user_daily_record_modify_history od
			left join user on user.id=od.edit_by_user_id
			$str_filter
			order by od.added desc";
		//print $sql;exit;
		$data = array();
		$q1 = $con->sql_query($sql);
		while($od = $con->sql_fetchassoc($q1)){
			$odata_guid = trim($od['guid']);
			$od['odata'] = unserialize($od['odata']);
			$od['ndata'] = unserialize($od['ndata']);
			
			$od['changed_items'] = array();
			$q2 = $con->sql_query("select odi.*
				from attendance_user_scan_record_modify_history odi
				where odi.odata_guid=".ms($odata_guid)."
				order by oscan_time");
			while($odi = $con->sql_fetchassoc($q2)){
				$od['changed_items'][$odi['guid']] = $odi;
			}
			$con->sql_freeresult($q2);
			
			$data[$odata_guid] = $od;
		}
		$con->sql_freeresult($q1);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['data'] = $data;
		return $ret;
	}
}
?>