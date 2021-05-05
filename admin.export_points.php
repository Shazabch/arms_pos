<?php
/*
6/24/2011 2:53:21 PM Andy
- Make all branch default sort by sequence, code.

9/12/2018 11:29 AM Andy
- Rewrite to use class Module.
- Enhanced to get points from membership_points

2/25/2020 3:35 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level']<9999 && !privilege('POS_IMPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_IMPORT', BRANCH_CODE), "/index.php");

class EXPORT_MEMBER_POINTS extends Module{
	var $branch_list = array();
	
	function __construct($title){
		global $con_multi, $appCore;
		if (!$con_multi)  $con_multi = $appCore->reportManager->connectReportServer();
		parent::__construct($title);
	}
	
	function _default(){
		$this->init_load();
		
		if($_REQUEST['export_point']){
			$this->export_point();
		}
		$this->display();
	}
	
	private function init_load(){
		global $con, $smarty, $sessioninfo, $con_multi;
		
		// Branch List
		if(BRANCH_CODE == 'HQ'){
			// Select those branch which got points
			$bid_list = array();
			$con_multi->sql_query("select distinct branch_id as bid from membership_points");
			while($r = $con_multi->sql_fetchassoc()){
				$bid_list[] = mi($r['bid']);
			}
			$con_multi->sql_freeresult();
			
			$this->branch_list = array();
			
			if($bid_list){
				$con_multi->sql_query("select id,code from branch where id in (".join(',', $bid_list).") order by sequence,code");
				while($r = $con_multi->sql_fetchassoc()){
					$this->branch_list[$r['id']] = $r;
				}
				$con_multi->sql_freeresult();
			}
			$smarty->assign('branch_list', $this->branch_list);
		}
		
		// Date List
		$date_list = array();
		$filter = array();
		if(BRANCH_CODE != 'HQ'){
			$filter[] = "mp.branch_id=".mi($sessioninfo['branch_id']);
		}

		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		$con_multi->sql_query("select distinct date(date) as d 
			from membership_points mp
			$str_filter
			order by d desc");
		while($r = $con_multi->sql_fetchassoc()){
			$date_list[] = $r['d'];
		}
		$con_multi->sql_freeresult();
		$smarty->assign('date_list', $date_list);
		if(!isset($_REQUEST['from']) && !isset($_REQUEST['to'])){
			$_REQUEST['to'] = date("Y-m-d");
			$_REQUEST['from'] = date("Y-m-d", strtotime("-30 day"));
		}
	}
	
	private function export_point(){
		global $con, $smarty, $sessioninfo, $con_multi;
		
		$filter = array();
	
		$date_filter_type = mi($_REQUEST['date_filter_type']);
		$dt = $_REQUEST['date'];
		$from = $_REQUEST['from'];
		$to = $_REQUEST['to'];		
		$show_branch = mi($_REQUEST['show_branch']);
		$show_date = mi($_REQUEST['show_date']);
		$err = array();
		$arr_log = array();

		switch($date_filter_type){
			case 2:	// Single Day
				if (strtotime($dt)<=0) {
					$err []= "Invalid date.";
				}
				$filter[] = "mp.date between ".ms($dt)." and ".ms($dt." 23:59:59");
				$arr_log[] = "Date: $dt";
				break;
			case 3:	// From / To
				$filter[] = "mp.date between ".ms($from)." and ".ms($to." 23:59:59");
				$arr_log[] = "Date: $from to $to";
				break;
			default:
				$arr_log[] = "Date: All";
				break;
		}
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		if(BRANCH_CODE != 'HQ'){
			$filter[] = "mp.branch_id=".mi($sessioninfo['branch_id']);
			$arr_log[] = "Branch: ".BRANCH_CODE;
		}else{
			$bid = mi($_REQUEST['branch_id']);
			if ($bid>0) {
				$filter[] = "mp.branch_id=$bid";
				$arr_log[] = "Branch: ".$this->branch_list[$bid]['code'];
			}else{
				$arr_log[] = "Branch: All";
			}
		}
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		$str_group_by = "group by nric";
		$str_col = '';
		$str_order = "order by m.nric";
		if($show_branch){
			$str_group_by .= ", mp.branch_id";
			$str_col .= ", b.code as bcode";
			$str_order .= ", bcode";
			$arr_log[] = "Show Branch";
		}	
		if($show_date){
			$str_group_by .= ", dt";
			$str_col .= ", date(mp.date) as dt";
			$str_order .= ", dt desc";
			$arr_log[] = "Show Date";
		}	
		
		$$q1 = $con_multi->sql_query($sql = "select m.nric, m.card_no, sum(mp.points) as points $str_col
			from membership m
			join membership_points mp on mp.nric=m.nric
			join branch b on b.id=mp.branch_id
			$str_filter
			$str_group_by
			$str_order");
		//die($sql);
		if ($con_multi->sql_numrows($q1)<=0)
		{
			$msg = 'No Data';
			$smarty->assign('msg', $msg);
			return;
		}

		$time = time();
		$folder_path = "/tmp";
		$filename = "membership_points_".$time.".csv";
		
		// Create file
		$fp = fopen($folder_path."/".$filename, 'w');
		$header = array();
		if($show_branch)	$header[] = "BRANCH";
		if($show_date)	$header[] = "DATE";
		$header = array_merge($header, array('NRIC', 'CARD NO', 'POINTS'));
		$success = fputcsv_eol($fp, $header);
		
		while($r = $con_multi->sql_fetchassoc($q1)){
			$data = array();
			if($show_branch)	$data[] = $r['bcode'];
			if($show_date)	$data[] = $r['dt'];
			$data = array_merge($data, array($r['nric'], $r['card_no'], $r['points']));
			$success = fputcsv_eol($fp, $data);
		}
		$con_multi->sql_freeresult($q1);
		fclose($fp);
		
		log_br($sessioninfo['id'], 'MEMBER_POINT_EXPORT', 0, "Export Membership Points (".join(', ', $arr_log).")");
		
		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename='.$filename);
		print file_get_contents($folder_path."/".$filename);
		exit;
	}
}

$EXPORT_MEMBER_POINTS = new EXPORT_MEMBER_POINTS('Export Member Points');
?>
