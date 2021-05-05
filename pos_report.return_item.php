<?php
/*
1/25/2019 3:59 PM Andy
- Fixed $sku_code_list index issue.

2/24/2020 4:19 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
include_once('pos_report.include.php');

$maintenance->check(92);

ini_set('memory_limit', '512M');
set_time_limit(0);

class POS_REPORT_ITEM_REPORT extends Module{
	
	var $transaction_status_list = array('-1'=>'Cancelled','1'=>'Valid','2'=>'Pruned');
	var $transaction_type_list = array('-1'=>'Non-member','1'=>'Member');

	function __construct($title){
		global $con, $config, $smarty, $sessioninfo, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
	
		load_branches();
		load_counter();
		
		if(!isset($_REQUEST['date_from'])&&!isset($_REQUEST['date_to'])){	// date not set, first time access
			$_REQUEST['date_from'] = date('Y-m-d',strtotime('-7 day',time()));
			$_REQUEST['date_to'] = date('Y-m-d');
		}elseif(strtotime($_REQUEST['date_to']) > strtotime("+ 30 day", strtotime($_REQUEST['date_from']))){
			$_REQUEST['date_to'] = date('Y-m-d',strtotime('+30 day',strtotime($_REQUEST['date_from'])));
		}
		
		$smarty->assign('transaction_status_list', $this->transaction_status_list);
		$smarty->assign('transaction_type_list', $this->transaction_type_list);
		
		parent::__construct($title);
	}
	
	function _default(){
		global $con, $config, $smarty, $sessioninfo;
		
		if($_REQUEST['load_data']){
			$this->load_data();
		}
		$this->display();
	}
	
	private function load_data(){
		global $con,$smarty,$sessioninfo,$con_multi,$config;
		
		//if(!$con_multi)	$con_multi= new mysql_multi();
		
		$date_from = $_REQUEST['date_from'];
		$date_to = $_REQUEST['date_to'];
		$counters = $_REQUEST['counters'];
		$tran_status = $_REQUEST['tran_status'];
		$tran_type = $_REQUEST['tran_type'];
		$receipt_no = trim($_REQUEST['receipt_no']);

		$filter = $err = $report_title = array();
		$filter[] = "pos.date between ".ms($date_from)." and ".ms($date_to);
		$filter[] = "pi.qty<0";
		
		$report_title[] = "Date: $date_from to $date_to";
		
		list($filter_branch_id,$filter_counter_id) = explode("|",$counters);
		
		if(BRANCH_CODE!='HQ' && $sessioninfo['branch_id'] != $filter_branch_id){
			$err[] = "Invalid Branch.";
		}
			
		$filter[] = "pos.branch_id=".mi($filter_branch_id);
		$report_title[] = "Branch: ".get_branch_code($filter_branch_id);
		
		if($filter_counter_id!='all'){
			$filter[] = "pos.counter_id=".mi($filter_counter_id);
			$report_title[] = "Counter: ".get_branch_counter_name($filter_branch_id, $filter_counter_id);
			
		}
		
		// transaction status
		if($tran_status!='all'){
			if($tran_status==1){
				$report_title[] = "Transaction Status: Active";
				$filter[] = "pos.cancel_status=0";
			}elseif($tran_status==2){
				$report_title[] = "Transaction Status: Pruned";
	            $filter[] = "pos.prune_status=1 and pos.cancel_status=1";
			}else{
				$report_title[] = "Transaction Status: Cancelled";
	            $filter[] = "pos.prune_status=0 and pos.cancel_status=1";
			}
		}else	$report_title[] = "Transaction Status: All";
		
		// transaction type
		if($tran_type!='all'){
			if($tran_type==1){
				$report_title[] = "Transaction Type: Member";
				$filter[] = "(pos.member_no<>'0' and pos.member_no is not null and pos.member_no<>'')";
			}else{
				$report_title[] = "Transaction Type: Non-member";
				$filter[] = "(pos.member_no='0' or pos.member_no is null or pos.member_no='')";
			}
		}else	$report_title[] = "Transaction Type: All";
		
		if($receipt_no!=''){
			$filter[] = "pos.receipt_no=".ms($receipt_no);
			$report_title[] = "Receipt No: $receipt_no";
		}
		
		$filter_sid_list = array();
		if(isset($_REQUEST['sku_code_list'])){
			$sku_code_list = join(",", array_map("ms", $_REQUEST['sku_code_list']));
		    // select sku item id list
		    
	     	$con_multi->sql_query("select * from sku_items where sku_item_code in ($sku_code_list)") or die(mysql_error());
			while($r = $con_multi->sql_fetchassoc()){
				$filter_sid_list[] = mi($r['id']);
				$group_item[] = $r;
			}
			$con_multi->sql_freeresult();
			$report_title[] = "Filter SKU Selected";
		}
		$smarty->assign('group_item',$group_item);
		
		if($filter_sid_list)	$filter[] = "pi.sku_item_id in (".join(',', $filter_sid_list).")";
		
		$filter = join(' and ',$filter);
		//$err[] = "some error";
		if($err){
			$smarty->assign("err", $err);
			return;
		}
		
		$sql = "select pos.branch_id,pos.counter_id,pos.date,pos.id as pos_id,pi.sku_item_id,pi.qty,pi.price,pi.discount,pos.member_no,pos.cancel_status,si.sku_item_code,si.mcode,si.artno,si.description,cs.network_name,pos.receipt_no, user.u as cashier_u, (pi.price-pi.discount) as amt,pos.pos_time, (select pu.u 
	    	from pos_receipt_cancel prc
	    	left join user pu on pu.id=prc.cancelled_by
	    	where prc.branch_id=pos.branch_id and prc.counter_id=pos.counter_id and prc.date=pos.date and prc.receipt_no=pos.receipt_no
	    	order by prc.cancelled_time desc limit 1) as cancelled_by_u, pos.prune_status
from pos_items pi
join pos on pos.branch_id=pi.branch_id and pos.counter_id=pi.counter_id and pos.date=pi.date and pos.id=pi.pos_id
left join counter_settings cs on cs.branch_id=pos.branch_id and cs.id=pos.counter_id
left join sku_items si on si.id=pi.sku_item_id
left join user on user.id=pos.cashier_id
where $filter order by pi.date desc,si.sku_item_code";
		//print $sql;
		$this->data = array();
		$q_pi = $con_multi->sql_query($sql);
		while($pi = $con_multi->sql_fetchassoc($q_pi)){
			$this->data['pi_list'][] = $pi;
			
			$this->data['total']['qty'] += $pi['qty'];
			$this->data['total']['amt'] += $pi['amt'];
		}
		$con_multi->sql_freeresult($q_pi);
		
		//print_r($this->data);
		
		$smarty->assign('data', $this->data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
}

$POS_REPORT_ITEM_REPORT = new POS_REPORT_ITEM_REPORT('POS Return Item Report');

?>
