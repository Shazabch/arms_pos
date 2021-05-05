<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ATTENDANCE_PH_ASSIGN')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ATTENDANCE_PH_ASSIGN', BRANCH_CODE), "/index.php");
$maintenance->check(439);

class PH_ASSIGNMENT extends Module{
	
	function __construct($title)
	{
		// load all initial data
		//$this->init_load();
		
		parent::__construct($title);
	}
	
	function _default()
	{
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;

		// Get Yearly Holiday List
		$this->load_yearly_ph_list();
		
		
		
		$this->display();
	}
	
	private function load_yearly_ph_list(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		$y_ph_list = array();
		
		$q1 = $con->sql_query("select * from attendance_ph_year order by y desc");
		while($ph_year = $con->sql_fetchassoc($q1)){
			$ph_year['ph_by_month'] = array();
			
			// Get Items
			$q2 = $con->sql_query("select phyi.*, ph.code as ph_code
				from attendance_ph_year_items phyi 
				join attendance_ph ph on ph.id=phyi.ph_id and ph.active=1
				where phyi.ph_year_id=".mi($ph_year['id']));
			while($ph_items = $con->sql_fetchassoc($q2)){
				$ph_id = mi($ph_items['ph_id']);
				$m1 = mi(date("m", strtotime($ph_items['date_from'])));
				$m2 = mi(date("m", strtotime($ph_items['date_to'])));
				
				for($m = $m1; $m <= $m2; $m++){
					$ph_year['ph_by_month'][$m][$ph_id]['ph_code'] = $ph_items['ph_code'];
				}
				
			}
			$con->sql_freeresult($q2);
			
			$y_ph_list[$ph_year['id']] = $ph_year;
		}
		$con->sql_freeresult($q1);
		
		//print_r($y_ph_list);
		$smarty->assign('y_ph_list', $y_ph_list);
	}
	
	function open(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		
		// Get Holiday List
		$ph_list = $appCore->attendanceManager->getPublicHolidayList(array('active'=>1));
		$smarty->assign('ph_list', $ph_list);
		//print_r($ph_list);
		
		$id = mi($_REQUEST['id']);
		
		if($id > 0){
			$ph_year = $appCore->attendanceManager->getPublicHolidayYearData($id);
			//print_r($ph_year);
		}
		
		$smarty->assign('form', $ph_year);
		$smarty->display('attendance.ph_assignment.open.tpl');
	}
	
	private function validate_data($form){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		$id = mi($form['id']);
		$err = array();
		
		$y = mi($form['y']);
		// Year
		if($y < 2000 || $y > 2999){
			$err[] = $LANG['PH_YEAR_INVALID'];
		}else{
			// Check Duplicate
			$con->sql_query("select id from attendance_ph_year where y=$y and id<>$id");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			// Already have
			if($tmp)	$err[] = sprintf($LANG['PH_YEAR_EXISTS'], $y);
		}
		
		if($form['ph_list']){
			// Loop Holiday
			foreach($form['ph_list'] as $ph_id => $r){
				// Date From
				if(!$r['date_from'] || !$appCore->isValidDateFormat($r['date_from'])){
					$err[] = sprintf($LANG['PH_DATE_FROM_INVALID'], $r['code']);
				}
				
				// Date To
				if(!$r['date_to'] || !$appCore->isValidDateFormat($r['date_to'])){
					$err[] = sprintf($LANG['PH_DATE_TO_INVALID'], $r['code']);
				}
				
				// Date to earlier
				if(strtotime($r['date_from']) > strtotime($r['date_to'])){
					$err[] = sprintf($LANG['PH_DATE_FROM_TO_ERROR'], $r['code']);
				}
				
				if(!$err){
					$y1 = date("Y", strtotime($r['date_from']));
					$y2 = date("Y", strtotime($r['date_to']));
					
					if($y1 != $y)	$err[] = sprintf($LANG['PH_YEAR_TO_DIFF'], $r['code'], $r['date_from'], $y);
					if($y2 != $y)	$err[] = sprintf($LANG['PH_YEAR_TO_DIFF'], $r['code'], $r['date_to'], $y);
					
				}
				
			}
		}else{
			$err[] = $LANG['PH_LIST_EMPTY'];
		}
		
		return $err;
	}
	
	function ajax_save(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
		
		$form = $_REQUEST;
		$id = mi($form['id']);
		
		//print_r($form);
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		// Check Form Data
		$err = $this->validate_data($form);
		
		// Got Error
		if($err){
			print "Found error(s)\n";
			foreach($err as $e){
				print "- $e\n";
			}
			exit;
		}
		
		$upd = array();
		$upd['y'] = $form['y'];
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		if($id > 0){
			// Edit
			$con->sql_query("update attendance_ph_year set ".mysql_update_by_field($upd)." where id=$id");
			
			// Delete old items
			$con->sql_query("delete from attendance_ph_year_items where ph_year_id=$id");
		}else{
			// New
			$is_new = true;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into attendance_ph_year ".mysql_insert_by_field($upd));
			$id = $con->sql_nextid();
		}
		
		if($form['ph_list']){
			// Loop Holiday
			foreach($form['ph_list'] as $ph_id => $r){
				$upd2 = array();
				$upd2['ph_year_id'] = $id;
				$upd2['ph_id'] = $ph_id;
				$upd2['date_from'] = $r['date_from'];
				$upd2['date_to'] = $r['date_to'];
				$upd2['show_in_report'] = mi($r['show_in_report']);
				
				$con->sql_query("insert into attendance_ph_year_items ".mysql_insert_by_field($upd2));
			}
		}
		
		// Commit Transaction
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['id'] = $id;
		print json_encode($ret);
	}
}

$PH_ASSIGNMENT = new PH_ASSIGNMENT('Holiday Assignment');
?>