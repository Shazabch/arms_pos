<?php
/*
8/20/2013 10:15 AM Andy
- Change deposit cancel module to use deposit listing.
*/

header("Location: pos.deposit_listing.php");
exit;

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('CC_DEPOSIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CC_DEPOSIT', BRANCH_CODE), "/index.php");

$maintenance->check(130);

class DEPOSIT_CANCELLATION extends Module{

    function __construct($title){
		global $con,$smarty,$sessioninfo;    

   		$q1 = $con->sql_query("select * from branch where active=1 order by sequence,code");

		while($r = $con->sql_fetchassoc($q1)){
			$branches[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);

		$smarty->assign("branches", $branches);

   		$q2 = $con->sql_query("select * from counter_settings where branch_id = ".mi($sessioninfo['branch_id'])." order by network_name");

		while($r = $con->sql_fetchassoc($q2)){
			$counters[$r['id']] = $r;
		}
		$con->sql_freeresult($q2);

		$smarty->assign("counters", $counters);

		$this->branch_id=get_request_branch();
		if (!$_REQUEST['date_select'])	$_REQUEST['date_select'] = date("Y-m-d");		
		$this->date=$_REQUEST['date_select'];
		$this->dd =date("Y-m-d",strtotime("+1 day", strtotime($this->date)));
		
		$q1 = $con->sql_query("select * from pos_finalized where date = ".ms(date('Y-m-d'))." and branch_id = ".mi($sessioninfo['branch_id']));
		
		$finalize_info = $con->sql_fetchassoc($q1);
		if($finalize_info['finalized']) $smarty->assign("today_finalized", $finalize_info['finalized']);
      	parent::__construct($title);
    }

	function _default(){
		$this->display();
		exit;
	}
	
	function search(){
        global $con, $smarty;

		$total_rows=0;
        $form = $_REQUEST;

		$q1 = $con->sql_query("select * from pos_finalized where date = ".ms($form['date'])." and branch_id = ".mi($form['branch_id']));
		
		$finalize_info = $con->sql_fetchassoc($q1);
		
		if($finalize_info['finalized']){ // following date has been finalized, disallow user to do cancellation
			$smarty->assign("finalized", 1);
		}else{ // show deposit item base on current tab user accessed
			if(!$form['tab'] || $form['tab'] == 1){
				$filter[] = "(pds.status is null or pds.status = 0)"; // is active deposit
				$form['tab'] = 1;
			}elseif($form['tab'] == 2){
				$filter[] = "pds.status = 1"; // is cancelled deposit
			}elseif($form['tab'] == 3 && $form['str_search']){ // search for receipt no
				$filter[] = "(pd.receipt_no like ".ms("%".strtoupper($form['str_search'])."%")." or pds.receipt_no like ".ms("%".strtoupper($form['str_search'])."%").")";
			}elseif($form['tab'] == 4){
				$filter[] = "pds.status = 2"; // used deposit
			}
			
			$filter[] = "(pd.branch_id = ".mi($form['branch_id'])." or pds.branch_id = ".mi($form['branch_id']).")";

			$q1 = $con->sql_query($q = "select b.code as branch_code, c.u as cashier_name, ab.u as approved_name, pd.item_list,
									pds.status, pd.branch_id, pd.counter_id, pd.receipt_no, pd.pos_id, pd.date, pds.pos_id as used_pos_id, pds.branch_id as used_branch_id, pds.date as used_date, pds.counter_id as used_counter_id,
									b2.code as used_branch_code
									from pos_deposit pd
									left join pos_deposit_status pds on pds.deposit_branch_id = pd.branch_id and pds.deposit_counter_id = pd.counter_id and pds.deposit_date = pd.date and pds.deposit_pos_id = pd.pos_id
									left join branch b on b.id = pd.branch_id
									left join branch b2 on b2.id = pds.branch_id
									left join user c on c.id = pd.cashier_id
									left join user ab on ab.id = pd.approved_by
									where (pd.date = ".ms($form['date'])." or pds.date = ".ms($form['date']).")
									and ".join(" and ", $filter)."
									order by pd.pos_time");
			//print $q;
			while($r = $con->sql_fetchassoc($q1)){
				//$r1 = array();
				// check if this is Used deposit
				if(!$r['status'] && ($r['branch_id'] != $form['branch_id'] || $r['date'] != $form['date'])) continue;
				elseif($r['status'] == 2 || $r['used_date'] == $form['date']){
					if($r['used_branch_id'] != $form['branch_id'] || $r['used_date'] != $form['date']) continue;
					
					$q_pdused = $con->sql_query("select p.amount as used_amt, p.receipt_no, c.u as cashier_name, ab.u as approved_name, pdsh.branch_id, pdsh.pos_date, pdsh.counter_id, pdsh.pos_id, p.cashier_id, pp.approved_by
												from pos_deposit_status_history pdsh
												join pos_deposit_status pds on pds.pos_id = pdsh.pos_id and pds.branch_id = pdsh.branch_id and pds.counter_id = pdsh.counter_id and pds.date = pdsh.pos_date
												join pos_payment pp on pp.pos_id = pds.pos_id and pp.branch_id = pds.branch_id and pp.counter_id = pds.counter_id and pp.date = pds.date and pp.type = 'Deposit'
												join pos p on p.id = pp.pos_id and p.branch_id = pp.branch_id and p.date = pp.date and p.counter_id = pp.counter_id
												left join user c on c.id = p.cashier_id
												left join user ab on ab.id = pp.approved_by
												where pp.type = 'Deposit' and pdsh.type = 'USED' and
												pdsh.pos_id = ".mi($r['used_pos_id'])." and 
												pdsh.pos_date = ".ms($form['date'])." and 
												pdsh.branch_id = ".mi($r['used_branch_id'])." and 
												pdsh.counter_id = ".mi($r['used_counter_id']));

					$ac_info = $con->sql_fetchrow($q_pdused);
					if($ac_info){
						$r['used_info'] = $ac_info;
						
						//$r['receipt_no'] = $ac_info['receipt_no'];
						//$r['cashier_name'] = $ac_info['cashier_name'];
						//$r['approved_name'] = $ac_info['approved_by'] ? $ac_info['approved_name'] : $ac_info['cashier_name'];
						//$r['deposit_amount'] = $ac_info['used_amt'];
					}else	$r['not_allow_cancel'] = true; // means it is not able to do cancel ofr RCV deposit since user need to cancel the USED 1st
					
					$con->sql_freeresult($q_pdused);
				}
				
				$item_list = unserialize($r['item_list']);
				if($item_list) $r['have_item_list'] = 1;
				$items[] = $r;
			}
			$con->sql_freeresult($q1);
		}

		//print_r($items);
		$smarty->assign('exception_list',$form['exc_list']);
		$smarty->assign('sku_items',$form['sku_item_code']);
		$smarty->assign('items',$items);
		$smarty->assign('tab',$form['tab']);
		$smarty->assign('str_search',$form['str_search']);
		$smarty->display('pos.deposit_cancellation.table.tpl');
	}

	function cancel_deposit(){
		global $con, $sessioninfo;
		$form = $_REQUEST;
		//print_r($form);exit;
		$q1 = $con->sql_query("select pd.*, pds.date as used_pos_date, pds.branch_id as used_branch_id, pds.pos_id as used_pos_id, pds.counter_id as used_counter_id
							   from pos_deposit pd
							   left join pos_deposit_status pds on pds.deposit_branch_id = pd.branch_id and pds.deposit_counter_id = pd.counter_id and pds.deposit_date = pd.date and pds.deposit_pos_id = pd.pos_id
							   where pd.branch_id = ".mi($form['branch_id'])." and pd.pos_id = ".mi($form['pos_id'])." and pd.counter_id = ".mi($form['counter_id'])." and pd.date = ".ms($form['date']));

		if($con->sql_numrows($q1) > 0){
			$pd_info = $con->sql_fetchassoc($q1);
			$upd = array();

			$curr_date = date("Y-m-d");
			$start_time = strtotime($curr_date);
			$cancel_type = "CANCEL_RCV";
			$amount_change = $form['deposit_amount'];
			
			/*if($form['status'] == 2 && $form['date'] != $curr_date){
				$cancel_type = "CANCEL_USED";
				$upd['status'] = 0;
			}else $upd['status'] = 1;*/
			$upd['status'] = 0;
			
			$curr_date = date("Y-m-d");
			$start_time = strtotime($curr_date);
			
			//////// THERE IS NO CANCEL USED, use cancel receipt at counter collection /////////
			/*if($form['status'] == 2){	// is cancel used
				// get used info
				$con->sql_query("select * from pos p
				where p.branch_id=".mi($pd_info['used_branch_id'])." and p.date=".ms($pd_info['used_pos_date'])." and p.counter_id=".mi($pd_info['used_counter_id'])." and p.id=".mi($pd_info['used_pos_id']));
				$used_pos = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$used_pos)	die('Cannot find used POS');
				
				if($used_pos['amount_change'] && $used_pos['amount']<$form['deposit_amount']){
					$amount_change = $used_pos['amount'];	// the pos got refund, so now should only refund the used amt
				}

				$cancel_type = "CANCEL_USED";
				$upd['status'] = 0;	// set to become available
			}else{
				$upd['status'] = 1;	// cancel rcv
			}*/

			if($form['date'] != $curr_date){	// diff date		
				// insert pos & pos_payment
				$receipt_no = $this->get_day_start_time(time()) - $start_time;
				
				$q2 = $con->sql_query("select max(id) as max_id from pos where date = ".ms($curr_date)." and branch_id = ".mi($form['branch_id'])." and counter_id = ".mi($form['new_counter_id']));
				
				if($con->sql_numrows($q2) > 0){
					$max_pos_id = $con->sql_fetchrow($q2);
					$pos_id = $max_pos_id['max_id'];
				}
				$con->sql_freeresult($q2);
				
				if(!$pos_id || $pos_id < 10000) $pos_id = 10000;
				else $pos_id++;
				
				$pos_ins = array();
				$pos_ins['branch_id'] = $form['branch_id'];
				$pos_ins['counter_id'] = $form['new_counter_id'];
				$pos_ins['id'] = $pos_id;
				$pos_ins['cashier_id'] = $sessioninfo['id'];
				$pos_ins['start_time'] = "CURRENT_TIMESTAMP";
				$pos_ins['end_time'] = "CURRENT_TIMESTAMP";
				$pos_ins['date'] = $curr_date;
				$pos_ins['pos_time'] = "CURRENT_TIMESTAMP";
				$pos_ins['amount'] = $amount_change*-1;
				$pos_ins['receipt_no'] = $receipt_no;
				$pos_ins['amount_change'] = $amount_change;
				$pos_ins['receipt_remark'] = $form['cancel_reason'];
				if(!$form['status']) $pos_ins['deposit'] = 1;
				
				$con->sql_query("insert into pos ".mysql_insert_by_field($pos_ins));

				$q3 = $con->sql_query("select max(id) as max_id from pos_payment where date = ".ms($curr_date)." and branch_id = ".mi($form['branch_id'])." and counter_id = ".mi($form['new_counter_id']));

				if($con->sql_numrows($q3) > 0){
					$max_pp_id = $con->sql_fetchrow($q2);
					$pp_id = $max_pp_id['max_id'];
				}
				$con->sql_freeresult($q3);
				
				if(!$pp_id || $pp_id < 10000) $pp_id = 10000;
				else $pp_id++;
				
				$pp_ins = array();
				$pp_ins['branch_id'] = $form['branch_id'];
				$pp_ins['counter_id'] = $form['new_counter_id'];
				$pp_ins['id'] = $pp_id;
				$pp_ins['pos_id'] = $pos_id;
				$pp_ins['type'] = 'Cash';
				$pp_ins['date'] = $curr_date;
				$pp_ins['amount'] = 0;
				$pp_ins['approved_by'] = $sessioninfo['id'];
				
				$con->sql_query("insert into pos_payment ".mysql_insert_by_field($pp_ins));
				
				$pdsh_ins = array();
				$pdsh_ins['branch_id'] = $form['branch_id'];
				$pdsh_ins['counter_id'] = $form['new_counter_id'];
				$pdsh_ins['pos_id'] = $pos_id;
				$pdsh_ins['pos_date'] = $curr_date;
				$pdsh_ins['receipt_no'] = $receipt_no;
				$pdsh_ins['deposit_branch_id'] = $pd_info['branch_id'];
				$pdsh_ins['deposit_counter_id'] = $pd_info['counter_id'];
				$pdsh_ins['deposit_pos_id'] = $pd_info['pos_id'];
				$pdsh_ins['deposit_pos_date'] = $pd_info['date'];
				$pdsh_ins['deposit_receipt_no'] = $pd_info['receipt_no'];
				$pdsh_ins['user_id'] = $sessioninfo['id'];
				$pdsh_ins['type'] = $cancel_type;
				$pdsh_ins['remark'] = $form['cancel_reason'];
				$pdsh_ins['added'] = "CURRENT_TIMESTAMP";

				$con->sql_query("insert into pos_deposit_status_history ".mysql_insert_by_field($pdsh_ins));
			}else{	// same date			
				$con->sql_query("update pos set pos.cancel_status = 1 where pos.branch_id = ".mi($form['branch_id'])." and pos.id = ".mi($form['pos_id'])." and pos.counter_id = ".mi($form['counter_id'])." and pos.date = ".ms($form['date']));
			
				if($con->sql_affectedrows() > 0){
					log_br($sessioninfo['id'], 'Deposit', $form['deposit_id'], "Receipt Cancelled (Receipt No: $pd_info[receipt_no], Date: $form[date], Counter ID: $form[counter_id])");
				}
			}
			
			$upd['cancel_reason'] = $form['cancel_reason'];
			$con->sql_query("update pos_deposit pd
							 join pos_deposit_status pds on pds.deposit_branch_id = pd.branch_id and pds.deposit_counter_id = pd.counter_id and pds.deposit_date = pd.date and pds.deposit_receipt_no = pd.receipt_no
							 set ".mysql_update_by_field($upd)."
							 where pd.branch_id = ".mi($form['branch_id'])." and pd.pos_id = ".ms($form['pos_id'])." and pd.counter_id = ".mi($form['counter_id'])." and pd.date = ".ms($form['date']));

			if($con->sql_affectedrows() > 0){
				log_br($sessioninfo['id'], 'Deposit', $form['deposit_id'], "Deposit (Receipt#$form[receipt_no]) was cancelled by $sessioninfo[u] for (ID#$form[deposit_id])");
			}

			print "Cancel Successfully.\n";
		}else{
			die('Deposit Not Found.');
		}
		$con->sql_freeresult($q1);
	}
	
	function get_day_start_time($t){ //get time at 12.00am based on $t
		$today_time = getdate($t);
		//mktime(hour,minute,second,month,day,year,is_dst)
		return mktime($today_time['hours'], $today_time['minutes'], $today_time['seconds'], $today_time['mon'], $today_time['mday'], $today_time['year']);
	}
}

$DEPOSIT_CANCELLATION= new DEPOSIT_CANCELLATION("Deposit Cancellation")

?>