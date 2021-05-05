<?php
include("include/common.php");
$maintenance->check(183);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!$config['membership_enable_staff_card']) js_redirect($LANG['NEED_CONFIG'], "/index.php");
if (!privilege('MEMBERSHIP_STAFF')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_STAFF', BRANCH_CODE), "/index.php");

class MEMBERSHIP_X_QUOTA_USAGE_REPORT extends Module{
	var $month_list = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
	var $year_list = array();
	var $branches_list = array();
	var $bid = 0;
	
	function __construct($title, $template=''){
		global $config, $con, $smarty, $sessioninfo;
		
		// get branches list
		$con->sql_query("select id,code from branch where active=1 order by sequence, code");
		while($r = $con->sql_fetchassoc()){
			$this->branches_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches_list', $this->branches_list);
		
		if(BRANCH_CODE == 'HQ'){
			$this->bid = mi($_REQUEST['branch_id']);
		}
		if(!$this->bid)	$this->bid = mi($sessioninfo['branch_id']);
		
		$smarty->assign('month_list', $this->month_list);
		parent::__construct($title, $template);
	}
	
	function _default(){
		global $con, $sessioninfo, $config, $smarty;
		
		// construct year list
		$this->construct_year_list();
		
		if($_REQUEST['show_report']){
			$this->generate_report();
			if($_REQUEST['output_excel']){
			    include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title);

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		}
		
		$this->display();
	}
	
	private function construct_year_list(){
		global $con, $sessioninfo, $config, $smarty;
		
		$this->year_list = array();
		
		// construct year list
		$con->sql_query("select year(min(date)) as min_year, year(max(date)) as max_year from pos where date>0");
		$r = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
        $min_year = $r['min_year'];
        $max_year = $r['max_year'];
		
		
		$count_year = $max_year - $min_year;
		
		for($i=$count_year; $i>=0; $i--){
			$this->year_list[] = $min_year+$i;
		}
		$smarty->assign('year_list', $this->year_list);
	}
	
	private function generate_report(){
		global $con, $sessioninfo, $config, $smarty;
		
		//print_r($_REQUEST);
		
		$bid = mi($_REQUEST['branch_id']);
		$y = mi($_REQUEST['year']);
		$m = mi($_REQUEST['month']);
		$staff_type = trim($_REQUEST['staff_type']);
		
		$err = array();
		if($bid != $this->bid)	$err[] = "Invalid Branch ID.";
		if(!in_array($y, $this->year_list))	$err[] = "Invalid Year.";
		if(!$this->month_list[$m])	$err[] = "Invalid Month.";
		
		if($err){
			$smarty->assign('err', $err);
			return false;
		}
		
		$this->data = array();
		
		$date_from = $y.'-'.$m.'-01';
		$date_to = $y.'-'.$m.'-'.days_of_month($m, $y);
		
		$filter = array();
		$filter[] = "qh.branch_id=$bid";
		$filter[] = "qh.quota_date between ".ms($date_from)." and ".ms($date_to);
		if($staff_type)	$filter[] = "m.staff_type=".ms($staff_type);
		
		$filter = 'where '.join(' and ', $filter);
		
		$sql = "select qh.*, m.staff_type, m.name,m.card_no as curr_card_no
from staff_quota_used_history qh
join membership m on m.nric=qh.nric
$filter";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			// member info
			if(!isset($this->data['member_info'][$r['nric']])){
				$tmp_info = array();
				$tmp_info['staff_type'] = $r['staff_type'];
				$tmp_info['name'] = $r['name'];
				$tmp_info['curr_card_no'] = $r['curr_card_no'];
				$this->data['member_info'][$r['nric']] = $tmp_info;
			}
			
			$this->data['data']['by_nric'][$r['nric']][$r['quota_date']]['quota_value'] += $r['quota_amount'];
			
			// total by nric
			$this->data['total']['by_nric'][$r['nric']]['quota_value'] += $r['quota_amount'];
			
			// total by date
			$this->data['total']['by_date'][$r['quota_date']]['quota_value'] += $r['quota_amount'];
			
			// total of total
			$this->data['total']['total']['quota_value'] += $r['quota_amount'];
		}
		$con->sql_freeresult($q1);

		if($this->data){
			$this->data['date_list'] = $this->generate_date($date_from, $date_to);
		}
		//print_r($this->data);
		
		$report_title = array();
		$report_title[] = "Branch: ".get_branch_code($bid);
		$report_title[] = "Year: $y";
		$report_title[] = "Month: ".$this->month_list[$m];
		$report_title[] = "Staff Type: ".($staff_type ? $config['membership_staff_type'][$staff_type] : 'All');
		
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		$smarty->assign('data', $this->data);
	}
	
	private function generate_date($date_from, $date_to){
		$date_list = array();
		
		$from = strtotime($date_from);
		$to = strtotime($date_to);
		
		while($from <= $to){
			$date_list[] = date("Y-m-d", $from);
			
			$from += 86400;
		}
		
		return $date_list;
	}
}

$MEMBERSHIP_X_QUOTA_USAGE_REPORT = new MEMBERSHIP_X_QUOTA_USAGE_REPORT('Quota Usage Report');
?>
