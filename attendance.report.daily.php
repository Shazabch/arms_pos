<?php
/*
1/21/2020 3:03 PM Andy
- Enhanced the report to always show the users once they got shift assigned.
- Enhanced to show users as "Absent" if got shift but no scan and no take leave.
- Enhanced to show "Holiday" and "Leave".

2/5/2020 1:58 PM Andy
- Fixed shift start time error.

2/5/2020 2:02 PM William
- Enhanced to get "in_early", "in_late", "out_early", "out_late" from system setting table.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ATTENDANCE_CLOCK_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ATTENDANCE_CLOCK_REPORT', BRANCH_CODE), "/index.php");
$maintenance->check(439);

class DAILY_ATTENDANCE_REPORT extends Module{
	var $branch_list = array();
	
	var $setting_list = array("in_early", "in_late", "out_early", "out_late");
	
	var $status_code_list = array(
		'no_shift' => 'No Shift Assigned',
		'single_scan' => 'Only One Scan',
		'scan_not_match_shift' => 'Scan Not Match with Shift',
		'in_late' => 'Late In',
		'in_early' => 'Early In',
		'out_late' => 'Late Exit',
		'out_early' => 'Early Exit',
		'prompt' => 'Prompt',
		'absent' => 'Absent',
		'onleave' => 'On Leave',
	);
	
	function __construct($title)
	{
		global $sessioninfo;
		
		// load all initial data
		$this->init_load();
		
		if(!isset($_REQUEST['branch_id']))	$_REQUEST['branch_id'] = $sessioninfo['branch_id'];
		if(!isset($_REQUEST['date']))	$_REQUEST['date'] = date("Y-m-d");
		
		parent::__construct($title);
	}
	
	private function init_load(){
		global $smarty, $appCore, $con;
		
		$this->branch_list = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign('branch_list', $this->branch_list);
		
		$smarty->assign('status_code_list', $this->status_code_list);

		$system_settings = array();
		$has_settings_val = 0;
		foreach($this->setting_list as $setting_name){
			$q1 = $con->sql_query("select setting_value from system_settings where setting_name=".ms($setting_name));
			$r = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			$system_settings[$setting_name] = $r['setting_value'];
			if($r['setting_value']!='') $has_settings_val = 1;
		}
		$smarty->assign("has_settings_val",$has_settings_val);
		$smarty->assign("system_settings",$system_settings);
	}
	
	function _default()
	{
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;

		$this->load_attendance_user_list();
		
		if($_REQUEST['load_report']){
			
			$this->load_report();
			if($_REQUEST['export_excel']){
				include_once("include/excelwriter.php");
				log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." To Excel");

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		}
		
		$this->display();
	}
	
	function load_attendance_user_list(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		$bid = mi($_REQUEST['branch_id']);
		$date = trim($_REQUEST['date']);
		
		// Load User List from Shift
		$tmp_user_list1 = $appCore->attendanceManager->loadShiftUserList($bid, $date);
		if(!$tmp_user_list1)	$tmp_user_list1 = array();
		
		// Load User List from Daily Record
		$tmp_user_list2 = $appCore->attendanceManager->loadAttendanceUserList($bid, $date);
		if(!$tmp_user_list2)	$tmp_user_list2 = array();
		
		$user_list = $tmp_user_list1 + $tmp_user_list2;
		//print_r($user_list);
		
		$smarty->assign('user_list', $user_list);
	}
	
	function ajax_reload_userlist(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		$this->load_attendance_user_list();
		unset($_REQUEST['user_id_list']);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('attendance.report.user_list.tpl');
		
		print json_encode($ret);
	}
	
	private function load_report(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		if(BRANCH_CODE == 'HQ'){
			$bid = mi($_REQUEST['branch_id']);
		}
		if(!$bid)	$bid = $sessioninfo['branch_id'];
		
		$date = trim($_REQUEST['date']);
		$user_id_list = $_REQUEST['user_id_list'];
		$filter_status_code = trim($_REQUEST['filter_status_code']);
		
		// Store as minute
		$in_early = mi($_REQUEST['in_early'])*60*-1;	// Early need to be negative
		$in_late = mi($_REQUEST['in_late'])*60;
		$out_early = mi($_REQUEST['out_early'])*60*-1;	// Early need to be negative
		$out_late = mi($_REQUEST['out_late'])*60;
	
		$err = array();
		if($bid<=0)	$err[] = "Invalid Branch";
		if(!$appCore->isValidDateFormat($date))	$err[] = "Invalid Date";
		if(!$user_id_list || !is_array($user_id_list))	$err[] = "Please Select User";
		
		$report_title = array();
		$report_title[] = "Branch: ".$this->branch_list[$bid]['code'];
		$report_title[] = "Date: ".$date;
		if($filter_status_code)	$report_title[] = "Filter by Status: ".$this->status_code_list[$filter_status_code];
				
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$data = array();
		$filter = array();
		$filter[] = "branch_id=$bid and date=".ms($date);
		$filter[] = "user_id in (".join(',', $user_id_list).")";
		
		$str_filter = "where ".join(' and ', $filter);
		
		// Get Holiday
		$ph_data = $appCore->attendanceManager->getPublicHolidayDataByDateRange($date, $date);
		//print_r($ph_data);
		
		// Get User Shift List
		$sql = "select atts.*, user.u, user.fullname, ats.code as shift_code, ats.description as shift_description
			from attendance_shift_user atts
			join user on user.id=atts.user_id
			left join attendance_shift ats on ats.id=atts.shift_id
			$str_filter and atts.shift_id>0";
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
			$user_id = mi($r['user_id']);
			
			// Original Shift Info
			$data['user_list'][$user_id]['shift'] = $r;
			
			// This maybe replaced later by scan history
			$data['user_list'][$user_id]['info']['shift_id'] = $r['shift_id'];
			$data['user_list'][$user_id]['info']['shift_code'] = $r['shift_code'];
			$data['user_list'][$user_id]['info']['shift_description'] = $r['shift_description'];
			$data['user_list'][$user_id]['info']['start_time'] = $r['start_time'];
			$data['user_list'][$user_id]['info']['break_1_start_time'] = $r['break_1_start_time'];
			$data['user_list'][$user_id]['info']['break_1_end_time'] = $r['break_1_end_time'];
			$data['user_list'][$user_id]['info']['break_2_start_time'] = $r['break_2_start_time'];
			$data['user_list'][$user_id]['info']['break_2_end_time'] = $r['break_2_end_time'];
			$data['user_list'][$user_id]['info']['end_time'] = $r['end_time'];
			
			// User Info
			$data['user_list'][$user_id]['user_info']['u'] = $r['u'];
			$data['user_list'][$user_id]['user_info']['fullname'] = $r['fullname'];
			
		}
		$con->sql_freeresult();
		
		// Daily Attendance Recird
		$sql = "select atudr.*, user.u, user.fullname
			from attendance_user_daily_record atudr
			join user on user.id=atudr.user_id
			$str_filter";
			
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$user_id = mi($r['user_id']);
			
			// Daily Info - Overwrite the original data
			$data['user_list'][$user_id]['info']['shift_id'] = $r['shift_id'];
			$data['user_list'][$user_id]['info']['shift_code'] = $r['shift_code'];
			$data['user_list'][$user_id]['info']['shift_description'] = $r['shift_description'];
			$data['user_list'][$user_id]['info']['start_time'] = $r['start_time'];
			$data['user_list'][$user_id]['info']['break_1_start_time'] = $r['break_1_start_time'];
			$data['user_list'][$user_id]['info']['break_1_end_time'] = $r['break_1_end_time'];
			$data['user_list'][$user_id]['info']['break_2_start_time'] = $r['break_2_start_time'];
			$data['user_list'][$user_id]['info']['break_2_end_time'] = $r['break_2_end_time'];
			$data['user_list'][$user_id]['info']['end_time'] = $r['end_time'];
			
			// User Info
			if(!isset($data['user_list'][$user_id]['user_info'])){
				$data['user_list'][$user_id]['user_info']['u'] = $r['u'];
				$data['user_list'][$user_id]['user_info']['fullname'] = $r['fullname'];
			}
			
			// Scan Records
			$data['user_list'][$user_id]['scan_records_pair'] = array();
			
			// Scan Record
			$q2 = $con->sql_query("select * 
				from attendance_user_scan_record
				where branch_id=$bid and date=".ms($r['date'])." and user_id=$user_id order by scan_time");
			$pair_no = 0;
			while($r2 = $con->sql_fetchassoc($q2)){
				if(isset($data['user_list'][$user_id]['scan_records_pair'][$pair_no])){
					if(count($data['user_list'][$user_id]['scan_records_pair'][$pair_no]['scan_record'])>=2){
						$pair_no++;
					}
				}
				$data['user_list'][$user_id]['scan_records_pair'][$pair_no]['scan_record'][] = $r2;
			}
			$con->sql_freeresult($q2);
		}
		$con->sql_freeresult($q1);
		
		// Calculate Result
		if($data){
			foreach($data['user_list'] as $user_id => $user_data){
				if(!$data['user_list'][$user_id]['scan_records_pair'])	continue;
				
				$status_code = '';
				
				if(!$user_data['info']['shift_id']){
					// No Shift Assigned
					$status_code = 'no_shift';
				}else{				
					// Check How many pair needed
					$pair_needed = 1;
					if(strtotime($user_data['info']['break_1_start_time'])>0){
						$pair_needed++;
						if(strtotime($user_data['info']['break_2_start_time'])>0)	$pair_needed++;
					}
					
					
					// Pair not match
					//print "pair_needed = $pair_needed, found = ".count($user_data['scan_records_pair'])."<br>";
					if(count($user_data['scan_records_pair']) != $pair_needed){
							$status_code = 'scan_not_match_shift';
					}
				}
				
				$data['user_list'][$user_id]['status_code'] = array();
				
				if($status_code){
					// Got Error
					$data['user_list'][$user_id]['status_code'][] = $status_code;
				}else{
					// Check Each Scan
					$stage = 1;
					foreach($user_data['scan_records_pair'] as $pair_no => $pair_data){
						$pair_status_list = array();
						$pair_status = '';
						if(count($pair_data['scan_record'])<=1){
							$pair_status = 'single_scan';
							$pair_status_list[] = $pair_status;
						}
						
						if(!$pair_status){
							switch($stage){
								case 1:
									$from_time = $user_data['info']['start_time'];
									if($pair_needed>1){
										$to_time = $user_data['info']['break_1_start_time'];
									}else{
										$to_time = $user_data['info']['end_time'];
									}
									$stage++;
									break;
								case 2:
									$from_time = $user_data['info']['break_1_end_time'];
									if($pair_needed>2){
										$to_time = $user_data['info']['break_2_start_time'];
									}else{
										$to_time = $user_data['info']['end_time'];
									}
									$stage++;
									break;
								case 3:
									$from_time = $user_data['info']['break_2_end_time'];
									$to_time = $user_data['info']['end_time'];
									break;
							}
							$data['user_list'][$user_id]['scan_records_pair'][$pair_no]['from_time'] = $from_time;
							$data['user_list'][$user_id]['scan_records_pair'][$pair_no]['to_time'] = $to_time;
							
							// In
							$diff_in = strtotime($pair_data['scan_record'][0]['scan_time']) - strtotime($from_time);
							$data['user_list'][$user_id]['scan_records_pair'][$pair_no]['diff_in'] = $diff_in;
							
							if($diff_in >= $in_late){	// Check Late
								// Late In
								$pair_status = 'in_late';
							}elseif($diff_in <= $in_early){	// Check Come Early
								// Early In
								$pair_status = 'in_early';
							}
							
							// Got in issue
							if($pair_status){
								$pair_status_list[] = $pair_status;
								$pair_status = '';
							}
							
							
							// Out
							$diff_out = strtotime($pair_data['scan_record'][1]['scan_time']) - strtotime($to_time);
							$data['user_list'][$user_id]['scan_records_pair'][$pair_no]['diff_out'] = $diff_out;							
							if($diff_out >= $out_late){	// Check Leave Late
								// Late In
								$pair_status = 'out_late';
							}elseif($diff_out <= $out_early){	// Check Leave Early
								// Early In
								$pair_status = 'out_early';
							}
							
							// Got Out issue
							if($pair_status){
								$pair_status_list[] = $pair_status;
							}
							
							
							if(!$pair_status_list){
								$pair_status = 'prompt';
								$pair_status_list[] = $pair_status;
							}
							
							// Total Time
							$total_time = strtotime($pair_data['scan_record'][1]['scan_time']) - strtotime($pair_data['scan_record'][0]['scan_time']);
							$data['user_list'][$user_id]['scan_records_pair'][$pair_no]['total_time'] = $total_time;
							
							$data['user_list'][$user_id]['total_time'] += $total_time;
						}
						
						if($pair_status_list){
							$data['user_list'][$user_id]['scan_records_pair'][$pair_no]['status_code'] = $pair_status_list;
							
							// Set the first error to use status
							foreach($pair_status_list as $pair_status){
								if($pair_status != 'prompt' && !in_array($pair_status, $data['user_list'][$user_id]['status_code'])){
									$data['user_list'][$user_id]['status_code'][] = $pair_status;
								}
							}
							
						}
					}
					
					// No Status, default prompt
					if(!$data['user_list'][$user_id]['status_code']){
						$data['user_list'][$user_id]['status_code'][] = 'prompt';
					}
				}
			}
			
			if($data['user_list']){
				// Check Leave
				foreach($data['user_list'] as $user_id => $user_data){
					$leave_data = $appCore->attendanceManager->getUserAttendanceLeaveRecord($user_id, $bid, '', array('date_from'=>$date));
					if($leave_data){
						$data['user_list'][$user_id]['leave_data'] = $leave_data[0];
						if(!$user_data['scan_records_pair']){
							$data['user_list'][$user_id]['status_code'][] = 'onleave';
						}
					}
					
				}
			}
			
			if($data['user_list']){
				// Check Absent
				foreach($data['user_list'] as $user_id => $user_data){
					if(!$user_data['scan_records_pair'] && !$user_data['leave_data'] && !$user_data['status_code']){
						// No Scan at All
						$data['user_list'][$user_id]['status_code'][] = 'absent';
					}
				}
			}
			
			if($data['user_list'] && $filter_status_code){
				// filter status_code
				foreach($data['user_list'] as $user_id => $user_data){
					$qualified = false;
					if($user_data['status_code'] && in_array($filter_status_code, $user_data['status_code'])){
						$qualified = true;
					}
					if(!$qualified){
						unset($data['user_list'][$user_id]);
					}
				}
				if(!$data['user_list'])	$data = array();
			}
			
			// Construct Summary
			if($data['user_list']){
				$data['summary'] = array();
				foreach($data['user_list'] as $user_id => $user_data){
					if($user_data['status_code']){
						foreach($user_data['status_code'] as $status_code){
							$data['summary']['by_status_code'][$status_code]['user_id_list'][$user_id] = $user_id;
						}
					}
				}
			}
		}
		
		//print_r($data);
		$smarty->assign('data', $data);
		$smarty->assign('ph_data', $ph_data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
}

$DAILY_ATTENDANCE_REPORT = new DAILY_ATTENDANCE_REPORT('Daily Attendance Report');
?>