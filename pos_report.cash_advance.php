<?php
/*
3/16/2018 1:50 PM HockLee
- Bugs fixed. Filtered by type = 'ADVANCE'.

2/24/2020 5:48 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
$maintenance->check(130);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
include_once('pos_report.include.php');

class CASH_ADVANCE_REPORT extends Module{
	function __construct($title){
		global $con, $smarty, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		if(!$_REQUEST['skip_init_load'])    $this->init_load();
		
		parent::__construct($title);
	}
	
	function _default(){
		global $smarty, $sessioninfo;
		
		$form = $_REQUEST;
		if(!isset($_REQUEST['date_to']))	$_REQUEST['date_to'] = date("Y-m-d");
		if(!isset($_REQUEST['date_from']))	$_REQUEST['date_from'] = date("Y-m-d", strtotime($_REQUEST['date_to']." -1 month"));
		
		if(strtotime($form['date_to']) > strtotime("+30 day", strtotime($form['date_from']))){
			$_REQUEST['date_to'] = date('Y-m-d',strtotime('+30 day',strtotime($form['date_from'])));
		}
		
		if($form['show_report']) {
			$this->generate_report();
			if($form['export_excel']){
				$filename = 'cash_advance_report_'.time().'.xls';
				include_once("include/excelwriter.php");
				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename='.$filename);
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
				log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Cash Advance Report to Excel ($filename)");
			}
		}
		
        $this->display();
	}
	
	private function init_load(){
		global $con, $smarty, $con_multi;
		
		// load branches + counters
		$this->counters = load_counter();
		$smarty->assign("counters", $this->counters);
		
		// load cashier
		$q1 = $con_multi->sql_query("select * from user where active=1 order by u");
		while($r = $con_multi->sql_fetchassoc($q1)){
			$this->cashiers[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);
		$smarty->assign("cashiers", $this->cashiers);
		
		// load branches (for result purpose)
		$this->branches = load_branches();
		$smarty->assign("branches", $this->branches);
		
		//$con->sql_freeresult();
	}
	
	private function generate_report(){
		global $con, $smarty, $sessioninfo, $con_multi;
		
		$counters = trim($_REQUEST['counters']);
		$date_from = trim($_REQUEST['date_from']);
		$date_to = trim($_REQUEST['date_to']);
		$reason = trim($_REQUEST['reason']);
		$cashier = mi($_REQUEST['cashier']);
		$approved_by = mi($_REQUEST['approved_by']);
		$remark = trim($_REQUEST['remark']);
		
		$err = array();
		if(!strtotime($date_from))	$err[] = "Invalid Date From.";
		if(!strtotime($date_to))	$err[] = "Invalid Date To.";
		if(!$err && strtotime($date_from)>strtotime($date_to))	$err[] = "Date To cannot early than date from.";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$filter = $report_title = array();
		if($counters!='all'){
			list($branch_id,$counter_id) = explode("|", $counters);
			$filter[] = "pch.branch_id=".mi($branch_id);
			if($counter_id!='all'){
				$filter[] = "pch.counter_id=".mi($counter_id);
			}
		}elseif(BRANCH_CODE!='HQ'){
			$filter[] = "pch.branch_id=".mi($sessioninfo['branch_id']);
		}
		
		$filter[] = "pch.date between ".ms($date_from)." and ".ms($date_to);
		
		if($reason) $filter[] = "pch.reason = ".ms($reason);
		if($cashier > 0) $filter[] = "pch.user_id = ".mi($cashier);
		if($approved_by > 0) $filter[] = "pch.collected_by = ".mi($approved_by);
		if($remark) $filter[] = "pch.remark like ".ms("%".$remark."%");
		
		$filter[] = "pch.type = 'ADVANCE'";
		$filter = "where ".join(' and ', $filter);
		
		foreach($this->counters as $ci) {
			if($ci['branch_id'] == $branch_id){
				if($counter_id != 'all' && $ci['id'] == $counter_id) {
					$report_title[] = "Counter: ".$ci['network_name']." (".$ci['code'].")";
					break;
				}
				elseif($counter_id == 'all') {
					$report_title[] = "Counter: All (".$ci['code'].")";
					break;
				}
			}
		}

		$report_title[] = "Date from ".$date_from." to ".$date_to;
		if($reason) $report_title[] = "Reason: ".$reason;
		if($cashier > 0) $report_title[] = "Cashier: ".strtoupper($this->cashiers[$cashier]['u']);
		if($approved_by > 0) $report_title[] = "Approved By: ".strtoupper($this->cashiers[$approved_by]['u']);
		if($remark) $report_title[] = "Remark: ".$remark;
		
		$sql = "select pch.*, c.u as cashier, ab.u as approved_by, b.code as bcode, cs.network_name as counter_name
				from pos_cash_history pch
				left join user c on c.id = pch.user_id
				left join user ab on ab.id = pch.collected_by
				left join branch b on b.id = pch.branch_id
				left join counter_settings cs on cs.id = pch.counter_id and cs.branch_id = pch.branch_id
				left join pos_finalized pf on pf.branch_id=pch.branch_id and pf.date=pch.date and pf.finalized=1
				$filter
				order by bcode, pch.timestamp, counter_name";
		
		$this->data = array();
		$q1 = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q1)){
			if($r['amount']) $r['ca_amount'] = $r['amount'];
			else $r['ca_amount'] = $r['oamount'];
			
			$r['ca_amount'] = abs($r['ca_amount']);
			$this->data[$r['branch_id']]['details'][] = $r;
		}
		$con_multi->sql_freeresult($q1);
		
		//print_r($this->data);
		
		$smarty->assign('data', $this->data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
}

$CASH_ADVANCE_REPORT = new CASH_ADVANCE_REPORT('Cash Advance Report');
?>
