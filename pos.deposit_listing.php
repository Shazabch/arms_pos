<?php
/*
8/27/2013 4:54 PM Andy
- New module Deposit Listing to replace deposit cancellation.

3/24/2014 5:56 PM Justin
- Modified the wording from "Finalize" to "Finalise".

03/24/2016 17:45 Edwin
- Enchanced on showing Receipt Reference Number in tables and details pop out

2/14/2017 11:16 AM Andy
- Enhanced to put default Date From and To.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('CC_DEPOSIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CC_DEPOSIT', BRANCH_CODE), "/index.php");

$maintenance->check(209);

class DEPOSIT_LISTING extends Module{
	var $branch_list = array();
	var $deposit_status_list = array('rcv'=>'Active', 'used'=>'Used', 'cancel'=>'Cancelled');
	
	function __construct($title){
		global $con,$smarty,$sessioninfo;    

		if(!isset($_REQUEST['date_from']))	$_REQUEST['date_from'] = date("Y-m-d",strtotime("-7 day"));
		if(!isset($_REQUEST['date_to']))	$_REQUEST['date_to'] = date("Y-m-d");
		
		// get branches
   		$q1 = $con->sql_query("select * from branch where active=1 order by sequence,code");
		while($r = $con->sql_fetchassoc($q1)){
			$this->branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("branch_list", $this->branch_list);


		$smarty->assign('deposit_status_list', $this->deposit_status_list);
		
      	parent::__construct($title);
    }

	function _default(){
		if($_REQUEST['show_data']){
			$this->generate_data();
		}
		$this->display();
	}
	
	private function generate_data(){
		global $con, $smarty, $sessioninfo, $config;
		
		$deposit_branch_id = BRANCH_CODE == 'HQ' ? mi($_REQUEST['deposit_branch_id']) : $sessioninfo['branch_id'];
		$deposit_status = trim($_REQUEST['deposit_status']);
		$date_from = trim($_REQUEST['date_from']);
		$date_to = trim($_REQUEST['date_to']);
		$used_branch_id = BRANCH_CODE == 'HQ' ? mi($_REQUEST['used_branch_id']) : $sessioninfo['branch_id'];
		$used_filter_deposit_branch = mi($_REQUEST['used_filter_deposit_branch']);
		$filter_receipt = mi($_REQUEST['filter_receipt']);
		
		// checking
		$err = array();
		if($date_from && $date_to){
			if(strtotime($date_to) < strtotime($date_from))	$err[] = "Date To cannot earlier than Date From.";
		}
		
		$filter = array();
		$xtra_select = '';
		$xtra_join = '';
		
		switch($deposit_status){
			case 'rcv':	// show NEW deposit
				if($deposit_branch_id){	// got select deposit branch id
					$filter[] = "pd.branch_id=$deposit_branch_id";
				}
				$filter[] = "pds.status=0";	// status 0 = new
				if($date_from)	$filter[] = "pd.date>=".ms($date_from);
				if($date_to)	$filter[] = "pd.date<=".ms($date_to);
				
				if($filter_receipt)	$filter[] = "pd.receipt_no=$filter_receipt";	// filter receipt
				break;
			case 'used':	// show used deposit
				$filter[] = "pds.status=2";	// status 2 = used
				if($used_branch_id)	$filter[] = "pds.branch_id=$used_branch_id";	// filter used at which branch
				if($used_filter_deposit_branch){
					if($deposit_branch_id){	// got select deposit branch id
						$filter[] = "pd.branch_id=$deposit_branch_id";
					}
				}
				if($date_from)	$filter[] = "pds.date>=".ms($date_from);
				if($date_to)	$filter[] = "pds.date<=".ms($date_to);
				
				$xtra_select = ', b2.code as used_branch_code, used_p.amount as used_amount, used_p.amount_change, used_p.receipt_ref_no as used_receipt_ref_no, used_cs.network_name as used_counter, pds.branch_id as used_branch_id, pds.date as used_date, pds.counter_id as used_counter_id, pds.pos_id as used_pos_id';
				$xtra_join = 'left join branch b2 on b2.id=pds.branch_id
				left join pos used_p on used_p.branch_id=pds.branch_id and used_p.date=pds.date and used_p.counter_id=pds.counter_id and used_p.id=pds.pos_id
				left join counter_settings used_cs on used_cs.branch_id=pds.branch_id and used_cs.id=pds.counter_id';
				
				if($filter_receipt)	$filter[] = "(pd.receipt_no=$filter_receipt or pds.receipt_no=$filter_receipt)";	// filter receipt
				break;
			case 'cancel':	// show cancelled deposit
				if($deposit_branch_id){	// got select deposit branch id
					$filter[] = "pd.branch_id=$deposit_branch_id";
				}
				$filter[] = "pds.status=1";	// status 1 = cancelled
				if($date_from)	$filter[] = "pds.cancel_date>=".ms($date_from);
				if($date_to)	$filter[] = "pds.cancel_date<=".ms($date_to);
				
				$xtra_select = ', pds.receipt_no as cancel_receipt_no, cancel_p.receipt_ref_no as cancel_receipt_ref_no, pds.branch_id as cancel_branch_id, pds.date as cancel_pos_date, pds.counter_id as cancel_counter_id, pds.pos_id as cancel_pos_id';
				$xtra_join = 'left join pos cancel_p on cancel_p.branch_id=pds.branch_id and cancel_p.date=pds.date and cancel_p.counter_id=pds.counter_id and cancel_p.id=pds.pos_id';
				if($filter_receipt)	$filter[] = "(pd.receipt_no=$filter_receipt or pds.receipt_no=$filter_receipt)";	// filter receipt
				break;
			default:
				$err[] = "Invalid Status.";
				break;
		}
		
		// got error
		if($err){
			$smarty->assign('err', $err);
			return false;
		}
		
		$filter = "where ".join(' and ', $filter);
		
		$sql = "select pd.*, b.code as deposit_branch_code, cs.network_name as deposit_counter, u1.u as deposit_cashier_name, p.amount as deposit_amount, p.receipt_ref_no, pds.receipt_no as used_receipt_no, pds.status, pds.date as used_date, u2.u as status_verified_u, pds.cancel_date, pds.last_update
		$xtra_select
		from pos_deposit pd
		left join pos_deposit_status pds on pds.deposit_branch_id=pd.branch_id and pds.deposit_date=pd.date and pds.deposit_counter_id=pd.counter_id and pds.deposit_pos_id=pd.pos_id
		left join branch b on b.id=pd.branch_id
		left join counter_settings cs on cs.branch_id=pd.branch_id and cs.id=pd.counter_id
		left join user u1 on u1.id=pd.cashier_id
		left join user u2 on u2.id=pds.verified_by
		left join pos p on p.branch_id=pd.branch_id and p.date=pd.date and p.counter_id=pd.counter_id and p.id=pd.pos_id
		$xtra_join
		$filter
		order by pds.last_update desc, pd.pos_time desc";
		
		$q1 = $con->sql_query($sql);
		$deposit_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			if($r['status'] == 2){
				if($r['deposit_amount'] > $r['used_amount'] && $r['amount_change']){
					$r['real_used_amt'] = round($r['deposit_amount'] - $r['amount_change'], 2);
				}else{
					$r['real_used_amt'] = $r['deposit_amount'];
				}
			}
			
			$deposit_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('deposit_list', $deposit_list);
		
	}
	
	function ajax_show_deposit_history(){
		global $con, $smarty, $sessioninfo, $config;
		
		$form = $_REQUEST;
		
		
		$q1 = $con->sql_query("select pdsh.*
		from pos_deposit_status_history pdsh
		where deposit_branch_id=".mi($form['bid'])." and deposit_pos_date=".ms($form['date'])." and deposit_counter_id=".mi($form['counter_id'])." and deposit_pos_id=".mi($form['pos_id'])."
order by pdsh.added desc");
		$his_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$his_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('his_list', $his_list);
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('pos.deposit_listing.his_list.tpl');
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function ajax_show_cancel_deposit(){
		global $con, $smarty, $sessioninfo, $config;
		
		$form = $_REQUEST;
		
		if($form['bid'] != $sessioninfo['branch_id'])	die('You cannot cancel other branch deposit.');
		
		$q1 = $con->sql_query("select pd.*, pds.status
		from pos_deposit pd
		left join pos_deposit_status pds on pds.deposit_branch_id=pd.branch_id and pds.deposit_date=pd.date and pds.deposit_counter_id=pd.counter_id and pds.deposit_pos_id=pd.pos_id
		where pd.branch_id=".mi($form['bid'])." and pd.date=".ms($form['date'])." and pd.counter_id=".mi($form['counter_id'])." and pd.pos_id=".mi($form['pos_id']));
		$deposit = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$deposit)	die('Invalid Deposit.');
		if($deposit['status'] == 1)	die('This deposit already cancel.');
		if($deposit['status'] == 2)	die('This deposit already used.');
		if($deposit['status'] != 0)	die('This deposit cannot be cancel.');
		
		// select counter list
		$counter_list = array();
		$con->sql_query("select * from counter_settings where branch_id=".mi($sessioninfo['branch_id'])." and active=1 order by network_name");
		while($r = $con->sql_fetchassoc()){
			$counter_list[] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('counter_list', $counter_list);
		
		$smarty->assign('deposit', $deposit);
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('pos.deposit_listing.open_cancel_deposit.tpl');
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function ajax_cancel_deposit(){
		global $con, $smarty, $sessioninfo, $config;
		
		$form = $_REQUEST;
		
		//print_r($form);
		
		if($form['bid'] != $sessioninfo['branch_id'])	die('You cannot cancel other branch deposit.');
		
		$q1 = $con->sql_query("select pd.*, pds.status
		from pos_deposit pd
		left join pos_deposit_status pds on pds.deposit_branch_id=pd.branch_id and pds.deposit_date=pd.date and pds.deposit_counter_id=pd.counter_id and pds.deposit_pos_id=pd.pos_id
		where pd.branch_id=".mi($form['bid'])." and pd.date=".ms($form['date'])." and pd.counter_id=".mi($form['counter_id'])." and pd.pos_id=".mi($form['pos_id']));
		$deposit = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$deposit)	die('Invalid Deposit.');
		if($deposit['status'] != 0)	die('This deposit cannot be cancel.');
		
		if($form['cancel_type'] != 1 && $form['cancel_type'] != 2)	die('Please choose cancellation method.');
		
		$CURRENT_TIMESTAMP = date("Y-m-d H:i:s");
		
		$pds = array();
		$pds['verified_by'] = $sessioninfo['id'];
		$pds['status'] = 1;
		$pds['cancel_reason'] = $form['cancel_reason'];
		$pds['last_update'] = $CURRENT_TIMESTAMP;
		
		$pdsh = array();
		$pdsh['deposit_branch_id'] = $form['bid'];
		$pdsh['deposit_pos_date'] = $form['date'];
		$pdsh['deposit_counter_id'] = $form['counter_id'];
		$pdsh['deposit_pos_id'] = $form['pos_id'];
		$pdsh['deposit_receipt_no'] = $deposit['receipt_no'];
		$pdsh['approved_by'] = $pdsh['user_id'] = $sessioninfo['id'];
		$pdsh['type'] = 'CANCEL_RCV';
		$pdsh['remark'] = $form['cancel_reason'];
		$pdsh['added'] = $CURRENT_TIMESTAMP;
		
		if($form['cancel_type'] == 1){	// direct cancel receipt
			if($this->check_finalized($form['date']))	die('Please make sure the date '.$form['date'].' is not yet finalised.');	// alrdy finalized
			
			$pdsh['cancel_date'] = $pds['cancel_date'] = $form['date'];	

			$con->sql_query("update pos set cancel_status=1 where branch_id=".mi($form['bid'])." and date=".ms($form['date'])." and counter_id=".mi($form['counter_id'])." and id=".mi($form['pos_id']));
			
			$pds['branch_id'] = $pdsh['branch_id'] = $form['bid'];
			$pds['counter_id'] = $pdsh['counter_id'] = $form['counter_id'];
			$pds['pos_id'] = $pdsh['pos_id'] = $form['pos_id'];
			$pds['date'] = $pdsh['pos_date'] = $form['date'];
			$pds['receipt_no'] = $pdsh['receipt_no'] = $deposit['receipt_no'];
					
		}elseif($form['cancel_type'] == 2){	// cancel by date
			if(!$form['cancel_date'])	die('Please key in cancellation date.');	// no date is key in
			if(date("Y", strtotime($form['cancel_date']))<2010)	die('Cancellation date cannot less than 2010.');	// date < 2010
			if(!$form['cancel_counter_id'])	die('Please select counter.');	// no counter is selected
			if($this->check_finalized($form['cancel_date']))	die('Please make sure the date '.$form['cancel_date'].' is not yet finalised.');	// alrdy finalized
			
			$pdsh['cancel_date'] = $pds['cancel_date'] = $form['cancel_date'];
			
			// get current max pos_id and max receipt_no
			$con->sql_query("select max(id)  as max_id, max(receipt_no) as max_receipt_no from pos where date = ".ms($form['cancel_date'])." and branch_id = ".mi($form['bid'])." and counter_id = ".mi($form['cancel_counter_id']));
			$max_info = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$pos_id = mi($max_info['max_id']);
			$receipt_no = mi($max_info['max_receipt_no']);
			
			if(!$pos_id || $pos_id < 10000) $pos_id = 10000;
			else $pos_id++;
			
			if(!$receipt_no || $receipt_no < 100000) $receipt_no = 100000;
			else $receipt_no++;
			
			// insert pos
			$pos_ins = array();
			$pos_ins['branch_id'] = $form['bid'];
			$pos_ins['counter_id'] = $form['cancel_counter_id'];
			$pos_ins['id'] = $pos_id;
			$pos_ins['cashier_id'] = $sessioninfo['id'];
			$pos_ins['start_time'] = $CURRENT_TIMESTAMP;
			$pos_ins['end_time'] = $CURRENT_TIMESTAMP;
			$pos_ins['date'] = $form['cancel_date'];
			$pos_ins['pos_time'] = $CURRENT_TIMESTAMP;
			$pos_ins['amount'] = $deposit['deposit_amount']*-1;
			$pos_ins['receipt_no'] = $receipt_no;
			$pos_ins['amount_change'] = $deposit['deposit_amount'];
			$pos_ins['receipt_remark'] = $form['cancel_reason'];
			$pos_ins['deposit'] = 1;
			
			$con->sql_query("insert into pos ".mysql_insert_by_field($pos_ins));
			
			// get max pos_payment id			
			$q3 = $con->sql_query("select max(id) as max_id from pos_payment where date = ".ms($form['cancel_date'])." and branch_id = ".mi($form['bid'])." and counter_id = ".mi($form['cancel_counter_id']));

			if($con->sql_numrows($q3) > 0){
				$max_pp_id = $con->sql_fetchrow($q2);
				$pp_id = mi($max_pp_id['max_id']);
			}
			$con->sql_freeresult($q3);
			
			if(!$pp_id || $pp_id < 10000) $pp_id = 10000;
			else $pp_id++;
			
			// insert pos_payment
			$pp_ins = array();
			$pp_ins['branch_id'] = $form['bid'];
			$pp_ins['counter_id'] = $form['cancel_counter_id'];
			$pp_ins['date'] = $form['cancel_date'];
			$pp_ins['id'] = $pp_id;
			$pp_ins['pos_id'] = $pos_id;
			$pp_ins['type'] = 'Cash';
			$pp_ins['amount'] = 0;
			$pp_ins['approved_by'] = $sessioninfo['id'];
			
			$con->sql_query("insert into pos_payment ".mysql_insert_by_field($pp_ins));
			
			$pds['branch_id'] = $pdsh['branch_id'] = $form['bid'];
			$pds['counter_id'] = $pdsh['counter_id'] = $form['cancel_counter_id'];
			$pds['pos_id'] = $pdsh['pos_id'] = $pos_id;
			$pds['date'] = $pdsh['pos_date'] = $form['cancel_date'];
			$pds['receipt_no'] = $pdsh['receipt_no'] = $receipt_no;
		}else{
			die('Invalid Cancellation Method.');
		}

		// update pos_deposit_status
		$con->sql_query("update pos_deposit_status set ".mysql_update_by_field($pds)." where deposit_branch_id=".mi($form['bid'])." and deposit_date=".ms($form['date'])." and deposit_counter_id=".mi($form['counter_id'])." and deposit_pos_id=".mi($form['pos_id']));
		$con->sql_query("insert into pos_deposit_status_history ".mysql_insert_by_field($pdsh));
		
		$ret = array();
		$ret['ok'] = 1;

		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	private function check_finalized($date){
		global $con;
		//$con->sql_query("select count(*) as count from pos_counter_collection_tracking where branch_id = ".mi($this->branch_id)." and date = ".ms(dmy_to_sqldate($date))." and finalized = 1 group by date");
		$con->sql_query("select * from pos_finalized where branch_id=".mi($this->branch_id)." and date=".ms($date)." and finalized=1");
		
		$finalized = $con->sql_numrows()>0 ? true : false;
		$con->sql_freeresult();
		
		return $finalized;
	}
	
}

$DEPOSIT_LISTING = new DEPOSIT_LISTING('Deposit Listing');
?>
