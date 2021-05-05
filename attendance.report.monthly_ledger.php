<?php
/*
1/21/2020 3:03 PM Andy
- Enhanced the report to always show the users once they got shift assigned.
- Enhanced to show users as "Absent" if got shift but no scan and no take leave.
- Enhanced to show "Holiday" and "Leave".

2/5/2020 1:58 PM Andy
- Fixed shift start time error.

2/7/2020 2:39 PM William
- Enhanced to get "in_early", "in_late", "out_early", "out_late" from system setting table.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ATTENDANCE_CLOCK_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ATTENDANCE_CLOCK_REPORT', BRANCH_CODE), "/index.php");
$maintenance->check(439);

class MONTHLY_ATTENDANCE_LEDGER extends Module{
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
		//if(!isset($_REQUEST['date']))	$_REQUEST['date'] = date("Y-m-d");
		
		parent::__construct($title);
	}
	
	private function init_load(){
		global $con, $smarty, $appCore;
		
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
			if($r['setting_value']!= '') $has_settings_val = 1;
		}
		$smarty->assign("has_settings_val",$has_settings_val);
		$smarty->assign("system_settings",$system_settings);
	}
	
	private function load_year_list(){
		global $con, $smarty, $appCore;
		
		$year_list = array();
		
		// Load Year List from Scan Record and User Shift
		$con->sql_query("select distinct(year(date)) as y from attendance_user_daily_record
			union
			select distinct(y) as y from attendance_shift_user order by y desc");
		while($r = $con->sql_fetchassoc()){
			$year_list[] = $r['y'];
		}
		$con->sql_freeresult();
		
		$smarty->assign('year_list', $year_list);
	}
	
	function _default()
	{
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;

		$this->load_year_list();
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
		$y = trim($_REQUEST['y']);
		$m = trim($_REQUEST['m']);
		
		if($bid && $y && $m){
			$date_from = $y.'-'.$m.'-1';
			$date_to = $y.'-'.$m.'-'.days_of_month($m, $y);
			
			// Load User List from Shift
			$tmp_user_list1 = $appCore->attendanceManager->loadShiftUserList($bid, $date_from, $date_to);
			if(!$tmp_user_list1)	$tmp_user_list1 = array();
		
			// Load User List from Daily Record
			$tmp_user_list2 = $appCore->attendanceManager->loadAttendanceUserList($bid, $date_from, $date_to);
			if(!$tmp_user_list2)	$tmp_user_list2 = array();
			
			$user_list = $tmp_user_list1 + $tmp_user_list2;
			//print_r($user_list);
		}
		
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
		
		$y = mi($_REQUEST['y']);
		$m = mi($_REQUEST['m']);
		$user_id_list = $_REQUEST['user_id_list'];
		$filter_status_code = trim($_REQUEST['filter_status_code']);
		
		// Store as minute
		$in_early = mi($_REQUEST['in_early'])*60*-1;	// Early need to be negative
		$in_late = mi($_REQUEST['in_late'])*60;
		$out_early = mi($_REQUEST['out_early'])*60*-1;	// Early need to be negative
		$out_late = mi($_REQUEST['out_late'])*60;
	
		$err = array();
		if($bid<=0)	$err[] = "Invalid Branch";
		if($y <= 2010)	$err[] = "Invalid Year";
		if($m <= 0 || $m > 12)	$err[] = "Invalid Month";
		if(!$user_id_list || !is_array($user_id_list))	$err[] = "Please Select User";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$date_from = $y.'-'.$m.'-1';
		$date_to = $y.'-'.$m.'-'.days_of_month($m, $y);
		
		$report_title = array();
		$report_title[] = "Branch: ".$this->branch_list[$bid]['code'];
		$report_title[] = "Year: ".$y;
		$report_title[] = "Month: ".$appCore->monthsList[$m];
		if($filter_status_code)	$report_title[] = "Filter by Status: ".$this->status_code_list[$filter_status_code];
		
		$data = array();
		$filter = array();
		$filter[] = "branch_id=$bid and date between ".ms($date_from)." and ".ms($date_to);
		$filter[] = "user_id in (".join(',', $user_id_list).")";
		
		$str_filter = "where ".join(' and ', $filter);
		
		// Get Holiday
		$ph_data = $appCore->attendanceManager->getPublicHolidayDataByDateRange($date_from, $date_to);
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
			$data['user_list'][$user_id]['date_list'][$r['date']]['shift'] = $r;
			
			// This maybe replaced later by scan history
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['shift_id'] = $r['shift_id'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['shift_code'] = $r['shift_code'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['shift_description'] = $r['shift_description'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['start_time'] = $r['start_time'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['break_1_start_time'] = $r['break_1_start_time'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['break_1_end_time'] = $r['break_1_end_time'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['break_2_start_time'] = $r['break_2_start_time'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['break_2_end_time'] = $r['break_2_end_time'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['end_time'] = $r['end_time'];
			
			// User Info
			$data['user_list'][$user_id]['user_info']['u'] = $r['u'];
			$data['user_list'][$user_id]['user_info']['fullname'] = $r['fullname'];
			
			$data['user_list'][$user_id]['summary']['by_status_code'] = array();
		}
		$con->sql_freeresult();
		
		// Get Daily Record
		$sql = "select atudr.*, user.u, user.fullname
			from attendance_user_daily_record atudr
			join user on user.id=atudr.user_id
			$str_filter
			order by user.u, atudr.date";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$user_id = $r['user_id'];
			
			// User Info
			if(!isset($data['user_list'][$user_id]['user_info'])){
				$data['user_list'][$user_id]['user_info']['u'] = $r['u'];
				$data['user_list'][$user_id]['user_info']['fullname'] = $r['fullname'];
			}
			
			// Daily Info - Overwrite the original data
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['shift_id'] = $r['shift_id'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['shift_code'] = $r['shift_code'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['shift_description'] = $r['shift_description'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['shift_color'] = $r['shift_color'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['start_time'] = $r['start_time'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['break_1_start_time'] = $r['break_1_start_time'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['break_1_end_time'] = $r['break_1_end_time'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['break_2_start_time'] = $r['break_2_start_time'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['break_2_end_time'] = $r['break_2_end_time'];
			$data['user_list'][$user_id]['date_list'][$r['date']]['info']['end_time'] = $r['end_time'];
			
			// Scan Record
			$q2 = $con->sql_query("select * 
				from attendance_user_scan_record
				where branch_id=$bid and date=".ms($r['date'])." and user_id=$user_id order by scan_time");
			$pair_no = 0;
			while($r2 = $con->sql_fetchassoc($q2)){
				if(isset($data['user_list'][$user_id]['date_list'][$r['date']]['scan_records_pair'][$pair_no])){
					if(count($data['user_list'][$user_id]['date_list'][$r['date']]['scan_records_pair'][$pair_no]['scan_record'])>=2){
						$pair_no++;
					}
				}
				$tmp = array();
				$tmp['scan_time'] = $r2['scan_time'];
				$data['user_list'][$user_id]['date_list'][$r['date']]['scan_records_pair'][$pair_no]['scan_record'][] = $tmp;
			}
			$con->sql_freeresult($q2);
			
			// Calculate Daily Result
			if($data['user_list'][$user_id]['date_list'][$r['date']]['scan_records_pair']){
				$status_code = '';
				$data['user_list'][$user_id]['date_list'][$r['date']]['status_code'] = array();
				$user_data = $data['user_list'][$user_id]['date_list'][$r['date']];
				
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
				
				if($status_code){
					// Got Error
					$data['user_list'][$user_id]['date_list'][$r['date']]['status_code'][] = $status_code;
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
							$data['user_list'][$user_id]['date_list'][$r['date']]['scan_records_pair'][$pair_no]['from_time'] = $from_time;
							$data['user_list'][$user_id]['date_list'][$r['date']]['scan_records_pair'][$pair_no]['to_time'] = $to_time;
							
							// In
							$diff_in = strtotime($pair_data['scan_record'][0]['scan_time']) - strtotime($from_time);
							$data['user_list'][$user_id]['date_list'][$r['date']]['scan_records_pair'][$pair_no]['diff_in'] = $diff_in;
							
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
							$data['user_list'][$user_id]['date_list'][$r['date']]['scan_records_pair'][$pair_no]['diff_out'] = $diff_out;							
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
							$data['user_list'][$user_id]['date_list'][$r['date']]['scan_records_pair'][$pair_no]['total_time'] = $total_time;
							
							$data['user_list'][$user_id]['date_list'][$r['date']]['total_time'] += $total_time;
							$data['user_list'][$user_id]['total_time'] += $total_time;
						}
						
						if($pair_status_list){
							$data['user_list'][$user_id]['date_list'][$r['date']]['scan_records_pair'][$pair_no]['status_code'] = $pair_status_list;
							
							// Set the first error to use status
							foreach($pair_status_list as $pair_status){
								if($pair_status != 'prompt' && !in_array($pair_status, $data['user_list'][$user_id]['date_list'][$r['date']]['status_code'])){
									$data['user_list'][$user_id]['date_list'][$r['date']]['status_code'][] = $pair_status;
								}
							}
							
						}
					}
					
					// No Status, default prompt
					if(!$data['user_list'][$user_id]['date_list'][$r['date']]['status_code']){
						$data['user_list'][$user_id]['date_list'][$r['date']]['status_code'][] = 'prompt';
					}
				}
				
				/*if($data['user_list'][$user_id]['date_list'][$r['date']]['status_code'] && $filter_status_code){
					// filter status_code					
					$qualified = false;
					if(in_array($filter_status_code, $data['user_list'][$user_id]['date_list'][$r['date']]['status_code'])){
						$qualified = true;
					}
					if(!$qualified){
						// Remove this date from the list
						unset($data['user_list'][$user_id]['date_list'][$r['date']]);
					}
				}
				
				// User Summary
				if($data['user_list'][$user_id]['date_list'][$r['date']]['status_code']){
					foreach($data['user_list'][$user_id]['date_list'][$r['date']]['status_code'] as $status_code){
						$data['user_list'][$user_id]['summary']['by_status_code'][$status_code] ++;
					}
				}*/
			}
			
			// No Data
			/*if(!$data['user_list'][$user_id]['date_list']){
				unset($data['user_list'][$user_id]);
			}*/
		}
		$con->sql_freeresult($q1);
		
		if($data['user_list']){
			foreach($data['user_list'] as $user_id => $user_data){
				$leave_data = $appCore->attendanceManager->getUserAttendanceLeaveRecord($user_id, $bid, '', array('date_from'=>$date_from, 'date_to'=>$date_to));
				//print_r($leave_data);
				if($leave_data){
					foreach($leave_data as $tmp_leave){
						for($tmp_date = $tmp_leave['date_from']; $tmp_date <= $tmp_leave['date_to']; $tmp_date = date("Y-m-d", strtotime($tmp_date)+86400)){
							$data['user_list'][$user_id]['date_list'][$tmp_date]['leave_data'] = $tmp_leave;
							if(!$data['user_list'][$user_id]['date_list'][$tmp_date]['status_code']){
								$data['user_list'][$user_id]['date_list'][$tmp_date]['status_code'][] = 'onleave';
							}
						}						
					}
				}
				
				// Check Absent
				foreach($data['user_list'][$user_id]['date_list'] as $tmp_date => $date_data){
					if(!$date_data['status_code'] && !$date_data['leave_data']){
						// No Scan at All
						$data['user_list'][$user_id]['date_list'][$tmp_date]['status_code'][] = 'absent';
					}
				}				
			}
			
			// Filter Status
			if($filter_status_code){
				foreach($data['user_list'] as $user_id => $user_data){
					foreach($user_data['date_list'] as $tmp_date => $date_data){
						// filter status_code					
						$qualified = false;
						if($date_data['status_code'] && in_array($filter_status_code, $date_data['status_code'])){
							$qualified = true;
						}
						if(!$qualified){
							// Remove this date from the list
							unset($data['user_list'][$user_id]['date_list'][$tmp_date]);
						}
					}
					
					// This user no data after filter status
					if(!$data['user_list'][$user_id]['date_list']){
						unset($data['user_list'][$user_id]);
					}
				}
			}else{
				// Add Holiday
				if($ph_data){
					foreach($ph_data['ph_list'] as $ph_id => $ph){
						for($tmp_date = $ph['date_from']; $tmp_date <= $ph['date_to']; $tmp_date = date("Y-m-d", strtotime($tmp_date)+86400)){
							foreach($data['user_list'] as $user_id => $user_data){
								if($ph['show_in_report'] || isset($data['user_list'][$user_id]['date_list'][$tmp_date])){
									$data['user_list'][$user_id]['date_list'][$tmp_date]['ph_id_list'][$ph_id] = $ph_id;
								}
							}
						}
					}
				}
			}
			
			// Compile User Summary
			foreach($data['user_list'] as $user_id => $user_data){
				foreach($user_data['date_list'] as $tmp_date => $date_data){
					if($date_data['status_code']){
						foreach($date_data['status_code'] as $status_code){
							$data['user_list'][$user_id]['summary']['by_status_code'][$status_code] ++;
						}
					}
				}
				
				// Sort Date by Asc
				ksort($data['user_list'][$user_id]['date_list']);
			}
		}
		
		if(!$data['user_list']){
			$data = array();
		}
		
		//print_r($data);
		$smarty->assign('ph_data', $ph_data);
		$smarty->assign('data', $data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
}

$MONTHLY_ATTENDANCE_LEDGER = new MONTHLY_ATTENDANCE_LEDGER('Monthly Attendance Ledger');
?>