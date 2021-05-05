<?php
/*
12/31/2013 2:57 PM Andy
- Change to check finalized=0 for those sales not yet finalized.

3/24/2014 5:56 PM Justin
- Modified the wording from "Finalize" to "Finalise".

2/25/2020 9:22 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
$maintenance->check(130);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
require_once('counter_collection.include.php');

class TRADE_IN_REPORT extends Module{
	var $branch_id = 0;
	var $branch_list = array();
	var $status_list = array(
		'all' => 'All',
		'new' => ' New',
		'verified' => 'Verified',
		'writeoff' => 'Write-Off'
	);
	
	function __construct($title){
		global $con, $smarty, $con_multi, $appCore, $sessioninfo;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		if(!$_REQUEST['skip_init_load'])    $this->init_load();
		
		if(!isset($_REQUEST['date_from']))	$_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month", time()));
		if(!isset($_REQUEST['date_to']))	$_REQUEST['date_to'] = date("Y-m-d");
		
		if(BRANCH_CODE == 'HQ'){
			$this->branch_id = mi($_REQUEST['branch_id']);
		}else{
			$this->branch_id = mi($sessioninfo['branch_id']);
		}
		
		$smarty->assign('status_list', $this->status_list);
		
		parent::__construct($title);
	}
	
	function _default(){
     	if($_REQUEST['show_report']){
			$this->generate_data(); 
		}
        $this->display();
	}
	
	private function init_load(){
		global $con, $smarty, $con_multi;
		
		$this->branch_list = array();
		$con_multi->sql_query("select * from branch where active=1 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc()){
			$this->branch_list[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branches', $this->branch_list);
	}
	
	private function generate_data(){
		global $con, $smarty, $sessioninfo,$con_multi;
		
		$bid = mi($this->branch_id);
		$date_from = trim($_REQUEST['date_from']);
		$date_to = trim($_REQUEST['date_to']);
		$status = trim($_REQUEST['status']);
		$finalized_status = trim($_REQUEST['finalized_status']);
		
		$err = array();
		if(!$bid)	$err[] = "Invalid Branch.";
		if(!strtotime($date_from))	$err[] = "Invalid Date From.";
		if(!strtotime($date_to))	$err[] = "Invalid Date To.";
		if(!$err && strtotime($date_from)>strtotime($date_to))	$err[] = "Date To cannot early than date from.";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$filter = array();
		$filter[] = "p.branch_id=$bid and p.cancel_status=0";
		$filter[] = "p.date between ".ms($date_from)." and ".ms($date_to);
		$filter[] = "pi.trade_in_by>0";
		
		if($status=='new')	$filter[] = "pi.sku_item_id=0 and pi.writeoff_by=0";
		elseif($status=='verified')	$filter[] = "pi.sku_item_id>0 and pi.verify_code_by>0";
		elseif($status=='writeoff')	$filter[] = "pi.sku_item_id=0 and pi.writeoff_by>0";
		
		if($finalized_status=='yes')	$filter[] = "pf.finalized=1";
		elseif($finalized_status=='no')	$filter[] = "(pf.finalized=0)";
		
		$filter = "where ".join(' and ', $filter);
		
		$report_title = array();
		$report_title[] = "Branch: ".get_branch_code($bid);
		$report_title[] = "Date from ".$date_from." to ".$date_to;
		$report_title[] = "Finalised: ".($finalized_status == 'all'? 'All' : strtoupper($finalized_status));
		$report_title[] = "Status: ".$this->status_list[$status];
		
		$sql = "select p.receipt_no,cs.network_name, pi.*,tu.u as trade_in_by_u,u_writeoff.u as writeoff_by_u,u_verify.u as verify_code_by_u,si.sku_item_code, si.mcode, si.link_code, si.description, pf.finalized
		from pos_items pi
		left join pos p on p.branch_id=pi.branch_id and p.date=pi.date and p.counter_id=pi.counter_id and p.id=pi.pos_id
		left join counter_settings cs on cs.branch_id=p.branch_id and cs.id=p.counter_id
		left join sku_items si on si.id=pi.sku_item_id
		left join user tu on tu.id = pi.trade_in_by 
		left join user u_writeoff on u_writeoff.id = pi.writeoff_by
		left join user u_verify on u_verify.id = pi.verify_code_by
		left join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date
		$filter
		order by p.date,cs.network_name,p.receipt_no";
		
		$this->data = array();
		$q1 = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q1)){
			$date = $r['date'];
			$cid = $r['counter_id'];
			$r['more_info'] = unserialize($r['more_info']);
			
			$serial_no = trim($r['more_info']['trade_in']['serial_no']);
			
			// got serial no
			if($serial_no && $r['sku_item_id']){
				$con_multi->sql_query("select branch_id,id from pos_items_sn where sku_item_id=".mi($r['sku_item_id'])." and serial_no=".ms($serial_no));
				$r['pos_items_sn'] = $con_multi->sql_fetchassoc();
				$con_multi->sql_freeresult();
			}
			
			$this->data[$date][$cid][] = $r;
		}
		$con_multi->sql_freeresult($q1);
		
		//print_r($this->data);
		
		$smarty->assign('data', $this->data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
}

$TRADE_IN_REPORT = new TRADE_IN_REPORT('Trade In Report');
?>
