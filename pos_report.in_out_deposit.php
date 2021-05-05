<?php
/*
8/26/2013 10:51 AM Andy
- Enhance report sql filter.
- Fix wrong report title.
- Fix Deposit Report cannot show data in sub branch.
- Fix wrong branch code show in report.
- Fix report show wrong in/out data.

2/14/2017 10:49 AM Andy
- Fixed item details wrongly group same receipt_no amount together.
- Change group by receipt_no to group by receipt_ref_no.

2/25/2020 9:17 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
$maintenance->check(209);

class IN_OUT_DEPOSIT extends Module{
   function __construct($title){
		global $con, $smarty, $sessioninfo, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
		if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
		
		// load branches
		$con_multi->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group
		$this->branches_group = $this->load_branch_group();

		$con_multi->sql_query("select * from user where active=1 order by u");
		$smarty->assign("user_list", $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();

		$con_multi->sql_query("select * from counter_settings where active=1 order by network_name");
		$smarty->assign("counter_list", $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();

		$transaction_status_list = array(1=>'Active',2=>"Cancelled");
		$smarty->assign('transaction_status', $transaction_status_list);
		
    	parent::__construct($title);
    }
	
	function _default(){
		$this->display();
		exit;
	}

	function show_report(){
		$this->process_form();
		$this->generate_report();
		$this->display();
	}

	private function run_report($bid){
        global $con, $smarty,$sessioninfo,$con_multi;

		/*$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}*/

		$sql = array();

		////////// OLD /////////
		/*$sql = $con->sql_query("select pd.date, pd.deposit_amount as rcv_amt, pds.deposit_branch_id as from_branch_id,
								rcv_b.code as rcv_branch_code, rcv_b.description as rcv_branch_desc
								from pos_deposit pd
								join pos_deposit_status pds on pds.deposit_branch_id = pd.branch_id and pds.deposit_counter_id = pd.counter_id and pds.deposit_date = pd.date and pds.deposit_pos_id = pd.pos_id
								join pos p on p.id = pd.pos_id and p.branch_id = pd.branch_id and p.date = pd.date and p.counter_id = pd.counter_id
								left join branch rcv_b on rcv_b.id = pds.deposit_branch_id
								where pds.status = 0 and pd.branch_id = ".mi($bid)." and ".join(" and ", $this->rcv_filter)."
								order by rcv_b.sequence, rcv_b.code, pd.date");

		while($r1 = $con->sql_fetchassoc($sql)){
			$this->branch_list[$r1['from_branch_id']]['branch_code'] = $r1['rcv_branch_code'];
			$this->branch_list[$r1['from_branch_id']]['description'] = $r1['rcv_branch_desc'];
			$this->table[$r1['from_branch_id']][$r1['date']]['rcv_amt'] += $r1['rcv_amt'];
		}
		$con_multi->sql_freeresult($q1);

		// check if having deposit used amt
		$q2 = $con->sql_query("select pp.date, pp.amount as used_amt, pds.branch_id as to_branch_id,
							   used_b.code as used_branch_code, used_b.description as used_branch_desc
							   from pos_deposit_status pds
							   join pos_payment pp on pp.pos_id = pds.pos_id and pp.branch_id = pds.branch_id and pp.counter_id = pds.counter_id and pp.date = pds.date and pp.type = 'Deposit'
							   join pos p on p.id = pds.pos_id and p.branch_id = pds.branch_id and p.date = pds.date and p.counter_id = pds.counter_id and p.id = pds.pos_id
							   left join branch used_b on used_b.id = pds.branch_id
							   where pds.status = 2 and pds.branch_id = ".mi($bid)." and ".join(" and ", $this->used_filter)."
							   order by used_b.sequence, used_b.code, pds.date");

		while($r2 = $con->sql_fetchrow($q2)){
			$this->branch_list[$r2['to_branch_id']]['branch_code'] = $r2['used_branch_code'];
			$this->branch_list[$r2['to_branch_id']]['description'] = $r2['used_branch_desc'];
			$this->table[$r2['to_branch_id']][$r2['date']]['used_amt'] += $r2['used_amt'];
		}
		$con->sql_freeresult($q2);
		
		$con_multi->close_connection();*/
		
		/////// NEW /////////
		
		$bid = mi($bid);
		
		// receive
		$sql = "select pd.*
from pos_deposit pd
left join pos p on p.branch_id=pd.branch_id and p.date=pd.date and p.counter_id=pd.counter_id and p.id=pd.pos_id
where p.branch_id=$bid and p.cancel_status=0 and p.date between ".ms($this->date_from)." and ".ms($this->date_to);
 		$q1 = $con_multi->sql_query($sql);
 		while($r = $con_multi->sql_fetchassoc($q1)){
 			$this->table[$bid][$r['date']]['rcv_amt'] += round($r['deposit_amount'], 2);	// rcv amt
 		}
 		$con_multi->sql_freeresult($q1);
 		
 		// used
 		$sql = "select sum(pd.deposit_amount) as deposit_amount, p.amount, p.amount_change, p.date
from pos_deposit pd
left join pos_deposit_status_history pdsh on pdsh.deposit_branch_id=pd.branch_id and pdsh.deposit_pos_date=pd.date and pdsh.deposit_counter_id=pd.counter_id and pdsh.deposit_pos_id=pd.pos_id
left join pos p on p.branch_id=pdsh.branch_id and p.date=pdsh.pos_date and p.counter_id=pdsh.counter_id and p.id=pdsh.pos_id
where p.branch_id=$bid and p.cancel_status=0 and p.date between ".ms($this->date_from)." and ".ms($this->date_to)." and pdsh.type='USED'
group by p.branch_id,p.date,p.counter_id,p.id";
		$q2 = $con_multi->sql_query($sql);
 		while($r = $con_multi->sql_fetchassoc($q2)){
 			$used_amt = $r['deposit_amount'];
 			$refund = 0;
 			if($r['amount'] < $r['deposit_amount'] && $r['amount_change'] > 0){
 				$refund = $r['amount_change'];
 				$used_amt -= $refund;
 			}
 			$this->table[$bid][$r['date']]['used_amt'] += round($used_amt, 2);	// used amt
 			if($refund)	$this->table[$bid][$r['date']]['refund'] += round($refund, 2);	// refund amt
 		}
 		$con_multi->sql_freeresult($q2);
 		
 		// cancel previous
 		$sql = "select p.amount, p.date
from pos_deposit pd
left join pos_deposit_status_history pdsh on pdsh.deposit_branch_id=pd.branch_id and pdsh.deposit_pos_date=pd.date and pdsh.deposit_counter_id=pd.counter_id and pdsh.deposit_pos_id=pd.pos_id
left join pos p on p.branch_id=pdsh.branch_id and p.date=pdsh.pos_date and p.counter_id=pdsh.counter_id and p.id=pdsh.pos_id
where p.branch_id=$bid and p.cancel_status=0 and p.date between ".ms($this->date_from)." and ".ms($this->date_to)." and pdsh.type='CANCEL_RCV'";
 		$q3 = $con_multi->sql_query($sql);
 		while($r = $con_multi->sql_fetchassoc($q3)){
 			$this->table[$bid][$r['date']]['cancel_amt'] += round($r['amount'], 2);	// cancel amt
 		}
 		$con_multi->sql_freeresult($q3);
	}

	function output_excel(){
	    global $smarty, $sessioninfo;
		
		$this->process_form();
		$this->generate_report();

        include_once("include/excelwriter.php");
    	$smarty->assign('no_header_footer', true);
    	$filename = "sac_calculation_".time().".xls";
    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Voucher Activation Report To Excel($filename)");
    	Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename='.$filename);

		print ExcelWriter::GetHeader();
		$this->display();
		print ExcelWriter::GetFooter();
	    exit;
	}

    function generate_report(){
		global $con, $smarty, $con_multi;

		$this->table = array();
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$this->run_report($bid);
			}
		}

		// set report fixed row display
		$smarty->assign('report_row', 25);
		
		$this->report_title[] = "Date From ".$this->date_from." to ".$this->date_to;

		/*if($this->counter_id){
			$con->sql_query("select network_name from counter_settings where id = ".mi($this->counter_id));
			$ct_desc = $con->sql_fetchfield(0);
			$con->sql_freeresult();
		}else $ct_desc = "All";
		
		$this->report_title[] = "Counter: ".$ct_desc;*/
		
		if($this->table){
			foreach($this->table as $bid => $b_list){
				uksort($this->table[$bid], array($this, "sort_date"));
			}
			
		}
		
		if($this->cashier_id){
			$con_multi->sql_query("select u from user where id = ".mi($this->cashier_id));
			$ch_desc = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
		}else $ch_desc = "All";
		
		$this->report_title[] = "Cashier: ".$ch_desc;

		if($this->trans_status == 1) $trans_desc = "Active";
		elseif($this->trans_status == 2) $trans_desc = "Cancelled";
		else $trans_desc = "All";
		
		$this->report_title[] = "Status: ".$trans_desc;
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		$smarty->assign('table', $this->table);
		$smarty->assign('branch_list', $this->branch_list);
	}
	
	function process_form(){
	    global $con, $sessioninfo, $smarty;

		if(!$_REQUEST['date_from']){
			if($_REQUEST['date_to']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month",strtotime($_REQUEST['date_to'])));
			else{
				$_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
				$_REQUEST['date_to'] = date('Y-m-d');
			}
		}
		if(!$_REQUEST['date_to'] || strtotime($_REQUEST['date_from']) > strtotime($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date('Y-m-d', strtotime("+1 month", strtotime($_REQUEST['date_from'])));
		}

		// check if the date is more than 1 month
		$end_date =date("Y-m-d",strtotime("+1 month",strtotime($_REQUEST['date_from'])));
    	if(strtotime($_REQUEST['date_to'])>strtotime($end_date)) $_REQUEST['date_to'] = $end_date;
		
		$this->date_from = $_REQUEST['date_from'];
		$this->date_to = $_REQUEST['date_to'];
		$this->counter_id = $_REQUEST['counter_id'];
		$this->cashier_id = $_REQUEST['cashier_id'];
		$this->tran_status = $_REQUEST['tran_status'];
		
		if(BRANCH_CODE == 'HQ'){    // HQ mode
			$branch_id = mi($_REQUEST['branch_id']);
			$bgid = explode(",",$_REQUEST['branch_id']);
			if($bgid[1] || $branch_id<0){ // branch group selected
				if($this->branches_group){
					foreach($this->branches_group['items'][$bgid[1]] as $bid=>$b){
						$this->branch_id_list[] = $bid;
					}
				}
				$this->report_title[] = "Branch Group: ".$this->branches_group['header'][$bgid[1]]['code'];
			}elseif($branch_id){  // single branch selected
			    $this->branch_id_list[] = $branch_id;
                $this->report_title[] = "Branch: ".get_branch_code($branch_id);
			}   
			else{   // all branches selected
				foreach($this->branches as $bid=>$b){
                    $this->branch_id_list[] = $bid;
				}
				$this->report_title[] = "Branch: All";
			}
		}else{  // Branches mode
            //$branch_id = mi($sessioninfo['branch_id']);
            $this->branch_id_list[] = mi($sessioninfo['branch_id']);
            $this->report_title[] = "Branch: ".BRANCH_CODE;
		}

		//$this->filter = array();
		//$this->filter[] = "pdsh.added between ".ms($this->date_from)." and ".ms($this->date_to.' 23:59:59');
		
		//$this->rcv_filter[] = "pd.date between ".ms($this->date_from)." and ".ms($this->date_to);
		//$this->used_filter[] = "pds.date between ".ms($this->date_from)." and ".ms($this->date_to);

		/*if($this->counter_id){
			$this->rcv_filter[] = "pd.counter_id = ".ms($this->counter_id);
			$this->used_filter[] = "pds.counter_id = ".ms($this->counter_id);
		}*/
		if($this->cashier_id){
			//$this->filter[] = "pdsh.cashier_id=".mi($this->cashier_id);
			//$this->rcv_filter[] = "pd.cashier_id = ".mi($this->cashier_id);
			//$this->used_filter[] = "pds.cashier_id = ".mi($this->cashier_id);
		}
		/*if($this->tran_status){
			$status = $this->tran_status - 1;
			$this->rcv_filter[] = "p.cancel_status = ".mi($status);
			$this->used_filter[] = "p.cancel_status = ".mi($status);
		}*/
		//$this->filter[] = "p.cancel_status=0";
		//parent::process_form();
	}

	function load_branch_group($id=0){
		global $con,$smarty,$con_multi;
	    if(isset($this->branch_group))  return $this->branch_group;
		$branch_group = array();
		
		// check whether select all or specified group
		if($id>0){
			$where = "where id=".mi($id);
			$where2 = "and bgi.branch_group_id=".mi($id);
		}
		// load header
		$con_multi->sql_query("select * from branch_group $where",false,false);
		if($con_multi->sql_numrows()<=0) return;
		while($r = $con_multi->sql_fetchrow()){
            $branch_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();

		// load items
		$con_multi->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 $where2 order by branch.sequence, branch.code",false,false);
		while($r = $con_multi->sql_fetchrow()){
	        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con_multi->sql_freeresult();
		
		$this->branch_group = $branch_group;
		//print_r($this->branch_group);
		$smarty->assign('branch_group',$branch_group);
		$smarty->assign('branches_group',$branch_group);
		return $branch_group;
	}
	
	function ajax_load_details_by_date(){
       global $con, $smarty,$sessioninfo,$con_multi;

		/*$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}*/

		$form = $_REQUEST;
		
		/////// OLD /////////
		/*$rcv_filter = $used_filter = $data = array();

		$rcv_filter[] = "pd.date = ".ms($form['date']);
		$used_filter[] = "pds.date = ".ms($form['date']);

		if($form['cashier_id']){
			$rcv_filter[] = "pd.cashier_id = ".mi($form['cashier_id']);
			$used_filter[] = "pds.cashier_id = ".mi($form['cashier_id']);
		}
		if($form['tran_status']){
			$status = $form['tran_status'] - 1;
			$rcv_filter[] = "p.cancel_status = ".mi($status);
			$used_filter[] = "p.cancel_status = ".mi($status);
		}
		
		// join all the filters
		//if($this->filter) $filter = " and ".join(" and ", $this->filter);

		$sql = $con->sql_query("select pd.receipt_no, c.u as cashier_name, ab.u as approved_name, pd.deposit_amount as rcv_amt, pd.item_list, pd.counter_id, pd.cashier_id, pd.date, pd.pos_id, pd.branch_id
								from pos_deposit pd
								join pos_deposit_status pds on pds.deposit_branch_id = pd.branch_id and pds.deposit_counter_id = pd.counter_id and pds.deposit_date = pd.date and pds.deposit_receipt_no = pd.receipt_no
								join pos p on p.id = pd.pos_id and p.branch_id = pd.branch_id and p.date = pd.date and p.counter_id = pd.counter_id and p.receipt_no = pd.receipt_no
								left join user c on c.id = pd.cashier_id
								left join user ab on ab.id = pd.approved_by
								where pds.status = 0 and pd.branch_id = ".mi($form['branch_id'])." and ".join(" and ", $rcv_filter)."
								order by pd.receipt_no");

		while($r1 = $con->sql_fetchassoc($sql)){
			$item_list = unserialize($r1['item_list']);
			if($item_list){
				$data[$r1['receipt_no']]['have_item_list'] = 1;
				$data[$r1['receipt_no']]['counter_id'] = $r1['counter_id'];
				$data[$r1['receipt_no']]['cashier_id'] = $r1['cashier_id'];
				$data[$r1['receipt_no']]['date'] = $r1['date'];
				$data[$r1['receipt_no']]['pos_id'] = $r1['pos_id'];
				$data[$r1['receipt_no']]['rcv_branch_id'] = $r1['branch_id'];
			}
			$data[$r1['receipt_no']]['cashier_name'] = $r1['cashier_name'];
			$data[$r1['receipt_no']]['approved_name'] = $r1['approved_name'];
			$data[$r1['receipt_no']]['rcv_amt'] += $r1['rcv_amt'];
		}
		$con_multi->sql_freeresult($q1);

		// check if having deposit used amt
		$q2 = $con->sql_query("select pds.receipt_no, c.u as cashier_name, ab.u as approved_name, pp.amount as used_amt
							   from pos_deposit_status pds
							   join pos_payment pp on pp.pos_id = pds.pos_id and pp.branch_id = pds.branch_id and pp.counter_id = pds.counter_id and pp.date = pds.date and pp.type = 'Deposit'
							   join pos p on p.id = pds.pos_id and p.branch_id = pds.branch_id and p.date = pds.date and p.counter_id = pds.counter_id and p.receipt_no = pds.receipt_no
							   left join user c on c.id = p.cashier_id
							   left join user ab on ab.id = pp.approved_by
							   where pds.status = 2 and pds.branch_id = ".mi($form['branch_id'])." and ".join(" and ", $used_filter)."
							   order by pds.receipt_no");

		while($r2 = $con->sql_fetchrow($q2)){
			$data[$r2['receipt_no']]['cashier_name'] = $r2['cashier_name'];
			$data[$r2['receipt_no']]['approved_name'] = $r2['approved_name'];
			$data[$r2['receipt_no']]['used_amt'] += $r2['used_amt'];
		}
		$con->sql_freeresult($q2);*/
		
		/////// NEW /////////
		$data = array();
		$bid = mi($form['branch_id']);
		if($form['cashier_id']){
			$casher_filter = " and p.cashier_id=".mi($form['cashier_id']);
		}
		// receive
		$sql = "select pd.*, c.u as cashier_name, ab.u as approved_name,p.receipt_ref_no
from pos_deposit pd
left join pos p on p.branch_id=pd.branch_id and p.date=pd.date and p.counter_id=pd.counter_id and p.id=pd.pos_id
left join user c on c.id = pd.cashier_id
left join user ab on ab.id = pd.approved_by
where p.branch_id=$bid and p.cancel_status=0 and p.date =".ms($form['date'])." $casher_filter";
 		$q1 = $con_multi->sql_query($sql);
 		while($r = $con_multi->sql_fetchassoc($q1)){
 			$this->table[$bid][$r['date']]['rcv_amt'] += round($r['deposit_amount'], 2);	// rcv amt
 			
 			$data[$r['receipt_ref_no']]['counter_id'] = $r['counter_id'];
			$data[$r['receipt_ref_no']]['cashier_id'] = $r['cashier_id'];
			$data[$r['receipt_ref_no']]['date'] = $r['date'];
			$data[$r['receipt_ref_no']]['pos_id'] = $r['pos_id'];
			$data[$r['receipt_ref_no']]['rcv_branch_id'] = $r['branch_id'];
			$data[$r['receipt_ref_no']]['cashier_name'] = $r['cashier_name'];
			$data[$r['receipt_ref_no']]['approved_name'] = $r['approved_name'];
			
			$data[$r['receipt_ref_no']]['rcv_amt'] += $r['deposit_amount'];
 		}
 		$con_multi->sql_freeresult($q1);
 		
 		// used
 		$sql = "select sum(pd.deposit_amount) as deposit_amount, p.amount, p.amount_change, p.branch_id,p.date,p.counter_id,p.id as pos_id,p.receipt_no,p.cashier_id,c.u as cashier_name, ab.u as approved_name,p.receipt_ref_no
from pos_deposit pd
left join pos_deposit_status_history pdsh on pdsh.deposit_branch_id=pd.branch_id and pdsh.deposit_pos_date=pd.date and pdsh.deposit_counter_id=pd.counter_id and pdsh.deposit_pos_id=pd.pos_id
left join pos p on p.branch_id=pdsh.branch_id and p.date=pdsh.pos_date and p.counter_id=pdsh.counter_id and p.id=pdsh.pos_id
left join user c on c.id = p.cashier_id
left join user ab on ab.id = pdsh.approved_by
where p.branch_id=$bid and p.cancel_status=0 and p.date =".ms($form['date'])." and pdsh.type='USED' $casher_filter
group by p.branch_id,p.date,p.counter_id,p.id";
		$q2 = $con_multi->sql_query($sql);
 		while($r = $con_multi->sql_fetchassoc($q2)){ 			
 			$used_amt = $r['deposit_amount'];
 			$refund = 0;
 			if($r['amount'] < $r['deposit_amount'] && $r['amount_change'] > 0){
 				$refund = $r['amount_change'];
 				$used_amt -= $refund;
 			}
 			
 			$data[$r['receipt_ref_no']]['counter_id'] = $r['counter_id'];
			$data[$r['receipt_ref_no']]['cashier_id'] = $r['cashier_id'];
			$data[$r['receipt_ref_no']]['date'] = $r['date'];
			$data[$r['receipt_ref_no']]['pos_id'] = $r['pos_id'];
			$data[$r['receipt_ref_no']]['rcv_branch_id'] = $r['branch_id'];
			$data[$r['receipt_ref_no']]['cashier_name'] = $r['cashier_name'];
			$data[$r['receipt_ref_no']]['approved_name'] = $r['approved_name'];
			
 			$data[$r['receipt_ref_no']]['used_amt'] += round($used_amt, 2);	// used amt
 			$data[$r['receipt_ref_no']]['refund_amt'] += round($refund, 2);	// refund amt
 		}
 		$con_multi->sql_freeresult($q2);
 		
 		// cancel previous
 		$sql = "select p.amount, p.amount_change, p.cancel_status, p.branch_id,p.date,p.counter_id,p.id as pos_id,p.receipt_no,p.cashier_id,c.u as cashier_name, ab.u as approved_name,p.receipt_ref_no
from pos_deposit pd
left join pos_deposit_status_history pdsh on pdsh.deposit_branch_id=pd.branch_id and pdsh.deposit_pos_date=pd.date and pdsh.deposit_counter_id=pd.counter_id and pdsh.deposit_pos_id=pd.pos_id
left join pos p on p.branch_id=pdsh.branch_id and p.date=pdsh.pos_date and p.counter_id=pdsh.counter_id and p.id=pdsh.pos_id
left join user c on c.id = p.cashier_id
left join user ab on ab.id = pdsh.approved_by
where p.branch_id=$bid and p.cancel_status=0 and p.date =".ms($form['date'])." and pdsh.type='CANCEL_RCV' $casher_filter";
 		$q3 = $con_multi->sql_query($sql);
 		while($r = $con_multi->sql_fetchassoc($q3)){
 			$data[$r['receipt_ref_no']]['counter_id'] = $r['counter_id'];
			$data[$r['receipt_ref_no']]['cashier_id'] = $r['cashier_id'];
			$data[$r['receipt_ref_no']]['date'] = $r['date'];
			$data[$r['receipt_ref_no']]['pos_id'] = $r['pos_id'];
			$data[$r['receipt_ref_no']]['rcv_branch_id'] = $r['branch_id'];
			$data[$r['receipt_ref_no']]['cashier_name'] = $r['cashier_name'];
			$data[$r['receipt_ref_no']]['approved_name'] = $r['approved_name'];
			
 			$data[$r['receipt_ref_no']]['cancel_amt'] += round($r['amount'], 2);	// cancel amt
 		}
 		$con_multi->sql_freeresult($q3);
 		
		//$con_multi->close_connection();
		
		$smarty->assign("data", $data);
		$smarty->assign("bid", $form['branch_id']);
		$smarty->assign("date", $form['date']);
		$smarty->display("pos_report.in_out_deposit.detail.tpl");
	}
	
	private function sort_date($a, $b){
		if($a == $b)	return 0;
		return ($a > $b) ? 1 : -1;
	}
}

$IN_OUT_DEPOSIT = new IN_OUT_DEPOSIT('Daily Deposit In/Out Report');
?>
