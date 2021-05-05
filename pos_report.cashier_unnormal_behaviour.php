<?php
/*
5/10/2010 4:11:18 PM Andy
- Change report name from "Cashier Un-normal Behaviour Report" to "Cashier Abnormal Behaviour Report"

8/9/2010 11:11:57 AM Andy
- Change "Behaviour" to "Behavior".
- Add "Open Price" and "Item Discount" Info.

10/20/2011 5:37:56 PM Alex
- change use report server

10/24/2011 1:34:27 PM Andy
- Add show Receipt Discount info.

10/28/2011 11:32:42 AM Andy
- Fix user filter not working.

1/12/2012 10:07:32 AM Justin
- Added to count prune status.

1/8/2014 2:05 PM Fithri
- check for type (POS) when getting data for "drawer open" count

3/25/2014 4:28 PM Justin
- Modified the wording from "Behavior" to "Behaviour".

8/7/2014 5:02 PM Fithri
- add two new columns, Deleted Items Count and Over 30 Minutes Transaction
- figures in some columns is made clickable, clicking on them will show transaction details (receipt).

8/21/2014 4:41 PM Fithri
- fix bug wrong way to calculate for cancelled bill & prune count

12/11/2015 3:58 PM DingRen
- Add Special Exempt
- delete service charge
- Open drawer show by type
- show/highlight the info of like how many denom did vs open drawer of denom

2017-09-15 13:47 Qiu Ying
- Bug fixed cashier name is not shown when there are only open drawer records

11/19/2018 11:38 AM Justin
- Enhanced to have Allow Cancelled Bills, Deleted Items and Prune Bills columns.

7/15/2019 1:36 PM Andy
- Fixed when show details info the data not tally.

2/24/2020 4:42 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
$maintenance->check(24);
set_time_limit(0);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	    case 'load_table':
	        load_branches();
	        load_table();
			break;
	    case 'show_info':
	        show_info();
			break;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;

	}
}
if(!isset($_REQUEST['date_from'])&&!isset($_REQUEST['date_to'])){
	$_REQUEST['date_from'] = date('Y-m-d',strtotime('-1 month',time()));
	$_REQUEST['date_to'] = date('Y-m-d');
}

load_cashier();
$smarty->assign('PAGE_TITLE', 'Cashier Abnormal Behaviour Report');
$smarty->display("pos_report.cashier_unnormal_behaviour.tpl");
exit;

function load_cashier(){
	global $con,$smarty,$sessioninfo,$con_multi;

	if(BRANCH_CODE!='HQ'){
		$filter[] = "user_privilege.branch_id=".mi($sessioninfo['branch_id']);
	}

	$filter[] = "user_privilege.privilege_code='POS_LOGIN'";
	$filter[] = "user_privilege.allowed=1";
	$filter[] = "user.active=1";

	$filter = join(' and ',$filter);

	$sql = "select user_privilege.*,user.u from user_privilege left join user on user.id=user_privilege.user_id where $filter group by user_id";
	$q_u = $con_multi->sql_query($sql) or die(mysql_error());
	while($r = $con_multi->sql_fetchassoc($q_u)){
		$cashier[$r['user_id']] = $r;
	}
	$con_multi->sql_freeresult($q_u);
	$smarty->assign('cashier',$cashier);
}

function load_table(){
    global $con,$smarty,$sessioninfo,$con_multi;
    $cashier_id = mi($_REQUEST['cashier_id']);
    $date_from = $_REQUEST['date_from'];
    $date_to = $_REQUEST['date_to'];
    $cancelled_bill = intval($_REQUEST['cancelled_bill']);
    $goods_return = intval($_REQUEST['goods_return']);
    $goods_return2 = $goods_return*-1;
    $diff_open_tran = intval($_REQUEST['diff_open_tran']);
    $diff_type = $_REQUEST['diff_type'];
    
    if(BRANCH_CODE!='HQ'){
		$filter[] = "p.branch_id=".mi($sessioninfo['branch_id']);
	}
	/*if($cashier_id!='all'){
        $cashier_id = intval($cashier_id);
        $filter[] = "p.cashier_id=$cashier_id";
	}*/
	$filter[] = "p.date between ".ms($date_from)." and ".ms($date_to);
	$filter = join(' and ',$filter);

	/*$con_multi= new mysql_multi();
	if(!$con_multi){
		die("Error: Fail to connect report server");
	}*/


	/*$sql = "select pos.* ,
(select count(*) from pos_drawer pd where pd.counter_id=pos.counter_id and pd.branch_id=pos.branch_id and pd.user_id=pos.cashier_id and pd.date=pos.date) as drawer_open_count,
(select sum(qty) from pos_items pi where pi.pos_id=pos.id and pi.branch_id=pos.branch_id and pi.counter_id=pos.counter_id and pi.date=pos.date and qty<0) as total_goods_return,
user.u
from pos left join user on pos.cashier_id=user.id where $filter order by date desc";
print $sql;*/

	$sql = "select p.*,UNIX_TIMESTAMP(end_time)-UNIX_TIMESTAMP(start_time) as trans_duration, user.u
	from pos p
	left join user on p.cashier_id=user.id
	where $filter";
	//print $sql."<br>";
	
	$q1 = $con_multi->sql_query($sql);
	while($r = $con_multi->sql_fetchassoc($q1)){

		if(!$cashier_id || ($cashier_id && $cashier_id==$r['cashier_id'])){
			$table[$r['branch_id']][$r['cashier_id']]['user_id'] = $r['cashier_id'];
		    $table[$r['branch_id']][$r['cashier_id']]['u'] = $r['u'];
		    $table[$r['branch_id']][$r['cashier_id']]['branch_id'] = $r['branch_id'];
		    $table[$r['branch_id']][$r['cashier_id']]['day_work'][$r['date']] = $r['date'];
			$table[$r['branch_id']][$r['cashier_id']]['tran_count']++;
			$table[$r['branch_id']][$r['cashier_id']]['counter_id'] = mi($r['counter_id']);
		}
		
		// calculate Count & Allow for Prune & Cancel Status
		$q2 = $con_multi->sql_query("select prc.*, p.prune_status, u1.u as cancelled_by_user, u2.u as verified_by_user
									 from pos_receipt_cancel prc
									 left join pos p on p.branch_id=prc.branch_id and p.date=prc.date and p.counter_id=prc.counter_id and p.receipt_no=prc.receipt_no
									 left join user u1 on u1.id = prc.cancelled_by
									 left join user u2 on u2.id = prc.verified_by
									 where p.branch_id=".mi($r['branch_id'])." and p.counter_id=".mi($r['counter_id'])." and p.date=".ms($r['date'])." and p.id=".mi($r['id']));
		
		while($prc = $con_multi->sql_fetchassoc($q2)){
			// if it is cashier itself do prune or cancel bill
			if(!$cashier_id || ($cashier_id && $cashier_id == $prc['cancelled_by'])){
				$table[$prc['branch_id']][$prc['cancelled_by']]['user_id'] = $prc['cancelled_by'];
				$table[$prc['branch_id']][$prc['cancelled_by']]['u'] = $prc['cancelled_by_user'];
				$table[$prc['branch_id']][$prc['cancelled_by']]['cancelled_bill']++;
				if($prc['prune_status']) $table[$prc['branch_id']][$prc['cancelled_by']]['prune_bill']++;
			}
			
			// prune or cancel bill by
			if(!$cashier_id || ($cashier_id && $cashier_id == $prc['verified_by'])){
				$table[$prc['branch_id']][$prc['verified_by']]['user_id'] = $prc['verified_by'];
				$table[$prc['branch_id']][$prc['verified_by']]['u'] = $prc['verified_by_user'];
				$table[$prc['branch_id']][$prc['verified_by']]['allow_cancelled_bill']++;
				if($prc['prune_status']) $table[$prc['branch_id']][$prc['verified_by']]['allow_prune_bill']++;
			}
		}
		$con_multi->sql_freeresult($q2);
		
		if($r['is_special_exemption'])  $table[$r['branch_id']][$r['cashier_id']]['special_exemption_count']++;
		
		$pos_more_info=unserialize($r['pos_more_info']);
		if($pos_more_info['service_charges']['remove_service_charges']){
			$table[$r['branch_id']][$r['cashier_id']]['remove_service_charges_count']++;
		}

		$pos_id = mi($r['id']);
		$bid = mi($r['branch_id']);
		$counter_id = mi($r['counter_id']);
		$date = $r['date'];
		
		// receipt discount
		$q_rd = $con_multi->sql_query("select pp.*,user.u as approved_by_u
									   from pos_payment pp
									   left join user on user.id=pp.approved_by
									   where pp.branch_id=$bid and pp.counter_id=$counter_id and pp.date=".ms($date)." and pp.pos_id=$pos_id and pp.type='discount' and pp.adjust=0 limit 1");
		$rd = $con_multi->sql_fetchassoc($q_rd);
		$con_multi->sql_freeresult($q_rd);
		
		if($rd){
			if(!$cashier_id || ($cashier_id && $cashier_id==$r['cashier_id'])){
				$table[$r['branch_id']][$r['cashier_id']]['receipt_discount']++;
				$table[$r['branch_id']][$r['cashier_id']]['receipt_discount_amt']+=round($rd['amount'],2);
			}
			
			if(!$cashier_id || ($cashier_id && $cashier_id==$rd['approved_by'])){
				if(!isset($table[$r['branch_id']][$rd['approved_by']])){
					$table[$r['branch_id']][$rd['approved_by']]['user_id'] = $rd['approved_by'];
					$table[$r['branch_id']][$rd['approved_by']]['u'] = $rd['approved_by_u'];
				}
				
			    $table[$r['branch_id']][$rd['approved_by']]['allow_receipt_discount']++;
			}
		}
		
		// pos items
		$q2 = $con_multi->sql_query($abc="select pi.*, u1.u as open_price_user, u2.u as item_discount_user
									from pos_items pi
									left join user u1 on u1.id=pi.open_price_by
									left join user u2 on u2.id=pi.item_discount_by
									where pi.branch_id=$bid and pi.counter_id=$counter_id and pi.date=".ms($date)." and pi.pos_id=$pos_id");
		while($pi = $con_multi->sql_fetchassoc($q2)){
			if(!$cashier_id || ($cashier_id && $cashier_id==$r['cashier_id'])){
				if($pi['qty']<0){    // goods return
	                $table[$r['branch_id']][$r['cashier_id']]['total_goods_return'] += abs($pi['qty']);
					//print $abc.'<br />';
				}
			}
			
			
			if($pi['open_price_by']>0){  // got open price
				if(!$cashier_id || ($cashier_id && $cashier_id==$r['cashier_id'])){
                	$table[$r['branch_id']][$r['cashier_id']]['open_price']++;
                }
                
                // open price by
                if(!$cashier_id || ($cashier_id && $cashier_id==$pi['open_price_by'])){
	                $table[$r['branch_id']][$pi['open_price_by']]['user_id'] = $pi['open_price_by'];
				    $table[$r['branch_id']][$pi['open_price_by']]['u'] = $pi['open_price_user'];
				    $table[$r['branch_id']][$pi['open_price_by']]['branch_id'] = $r['branch_id'];
				    
				    $table[$r['branch_id']][$pi['open_price_by']]['allow_open_price']++;
			    }
			}
			
			if($pi['item_discount_by']>0){  // got item discount
				if(!$cashier_id || ($cashier_id && $cashier_id==$r['cashier_id'])){
				    $table[$r['branch_id']][$r['cashier_id']]['item_discount']++;
	                $table[$r['branch_id']][$r['cashier_id']]['item_discount_amt']+=$pi['discount'];
                }

                // item_discount_by
                if(!$cashier_id || ($cashier_id && $cashier_id==$pi['item_discount_by'])){
	                $table[$r['branch_id']][$pi['item_discount_by']]['user_id'] = $pi['item_discount_by'];
				    $table[$r['branch_id']][$pi['item_discount_by']]['u'] = $pi['item_discount_user'];
				    $table[$r['branch_id']][$pi['item_discount_by']]['branch_id'] = $r['branch_id'];
	
				    $table[$r['branch_id']][$pi['item_discount_by']]['allow_item_discount']++;
			    }
			}
		}
		$con_multi->sql_freeresult($q2);
		
		//pos_delete_items
		//$q4 = $con_multi->sql_query($abc="select /*count(*)*/ sum(qty) as ct from pos_delete_items pdi left join pos on pdi.branch_id=pos.branch_id and pdi.counter_id=pos.counter_id and pdi.pos_id=pos.id and pdi.date=pos.date where pdi.branch_id = $bid and pdi.counter_id = $counter_id and pdi.date = ".ms($date)." and pdi.pos_id = $pos_id and pos.cashier_id=".mi($r['cashier_id']));
		/*$r4 = $con_multi->sql_fetchassoc($q4);
		if(!$cashier_id || ($cashier_id && $cashier_id==$r['cashier_id'])){
			if ($r4['ct']) {
				$table[$r['branch_id']][$r['cashier_id']]['deleted_items'] += mi($r4['ct']);
				//print $abc.'<br />';
			}
		}*/
		
		// calculate Count & Allow for delete items
		$q2 = $con_multi->sql_query("select pdi.*, p.cashier_id, u1.u as deleted_by_user, u2.u as verified_by_user
									 from pos_delete_items pdi
									 left join pos p on p.branch_id=pdi.branch_id and p.date=pdi.date and p.counter_id=pdi.counter_id and p.id=pdi.pos_id
									 left join user u1 on u1.id = p.cashier_id
									 left join user u2 on u2.id = pdi.delete_by
									 where pdi.branch_id=".mi($bid)." and pdi.counter_id=".mi($counter_id)." and pdi.date=".ms($date)." and pdi.pos_id=".mi($pos_id));
		
		while($pdi = $con_multi->sql_fetchassoc($q2)){
			// this table doesn't have the history of who delete the items at first place
			// so need to add the delete item count to the cashier
			if(!$cashier_id || ($cashier_id && $cashier_id == $pdi['cashier_id'])){
				$table[$pdi['branch_id']][$pdi['cashier_id']]['user_id'] = $pdi['cashier_id'];
				$table[$pdi['branch_id']][$pdi['cashier_id']]['u'] = $pdi['deleted_by_user'];
				$table[$pdi['branch_id']][$pdi['cashier_id']]['deleted_items']+=$pdi['qty'];
			}
			
			// person who authorised to delete the items
			if(!$cashier_id || ($cashier_id && $cashier_id == $pdi['delete_by'])){
				$table[$pdi['branch_id']][$pdi['delete_by']]['user_id'] = $pdi['delete_by'];
				$table[$pdi['branch_id']][$pdi['delete_by']]['u'] = $pdi['verified_by_user'];
				$table[$pdi['branch_id']][$pdi['delete_by']]['allow_deleted_items']+=$pdi['qty'];
			}
		}
		$con_multi->sql_freeresult($q2);
		
		//more-than-30-minutes transactions
		if(!$cashier_id || ($cashier_id && $cashier_id==$r['cashier_id'])){
			if (mi($r['trans_duration']) > (30*60)) $table[$r['branch_id']][$r['cashier_id']]['over_30min']++;
		}
	}
	$con_multi->sql_freeresult($q1);
	
	//$filter2 = str_replace("cashier_id", 'user_id', $filter);
	$filter2 = $filter;
	if($cashier_id)	$filter2 .= " and p.user_id=$cashier_id";
	$sql2 = "select p.branch_id,p.date,p.user_id,p.type,user.u,count(*) as drawer_open_count
			 from pos_drawer p
			 left join user on user.id=p.user_id
			 where $filter2
			 group by p.branch_id,p.date,p.user_id,p.type";
	//print $sql2;
	$q3 = $con_multi->sql_query($sql2);
	while($r = $con_multi->sql_fetchassoc($q3)){
		$table[$r['branch_id']][$r['user_id']]['user_id'] = $r['user_id'];
		$table[$r['branch_id']][$r['user_id']]['u'] = $r['u'];
        $table[$r['branch_id']][$r['user_id']]['drawer_open_count'] += $r['drawer_open_count'];

		$table[$r['branch_id']][$r['user_id']]['drawer_open'][$r['type']]['count']+= $r['drawer_open_count'];
	}
	$con_multi->sql_freeresult($q3);
	
    //$con_multi->close_connection();
	if($table){
        // filter and checking
		foreach($table as $bid=>$cashier){
			foreach($cashier as $uid=>$r){
			    $need_drop = false;
				if($cancelled_bill&&!$need_drop){
					if($r['cancelled_bill']<$cancelled_bill)	$need_drop = true;
				}

				if($goods_return&&!$need_drop){
					if($r['total_goods_return']<$goods_return)	$need_drop = true;
				}
				$table[$bid][$uid]['diff_open_tran'] = $r['drawer_open_count'] - $r['tran_count'];

				if($diff_type!='not_set'&&!$need_drop){
                    if($diff_type=='more'){
                        if($r['diff_open_tran']<$diff_open_tran)	$need_drop = true;
                    }else{
                        if($r['diff_open_tran']>$diff_open_tran)	$need_drop = true;
					}
				}
				if($need_drop){
					unset($table[$bid][$uid]);
					continue;
				}

				if($r['drawer_open']){
					foreach($r['drawer_open'] as $type=>$v){
						switch($type){
							case 'ADVANCE':
								$sql="select count(*) as count from pos_cash_history p where type='ADVANCE' and branch_id=".$bid." and user_id=".$uid." and ".$filter;
								$q = $con_multi->sql_query($sql);
								$c = $con_multi->sql_fetchassoc($q);
								$con_multi->sql_freeresult($q);

								$table[$bid][$uid]['drawer_open'][$type]['actual_count']=$c['count'];
							break;
							case 'TOPUP':
								$sql="select count(*) as count from pos_cash_history p where type='TOP_UP' and branch_id=".$bid." and user_id=".$uid." and ".$filter;
								$q = $con_multi->sql_query($sql);
								$c = $con_multi->sql_fetchassoc($q);
								$con_multi->sql_freeresult($q);

								$table[$bid][$uid]['drawer_open'][$type]['actual_count']=$c['count'];
							break;
							case 'DENOM':
								$sql="select count(*) as count from pos_cash_domination p where branch_id=".$bid." and user_id=".$uid." and ".$filter;
								$q = $con_multi->sql_query($sql);
								$c = $con_multi->sql_fetchassoc($q);
								$con_multi->sql_freeresult($q);

								$table[$bid][$uid]['drawer_open'][$type]['actual_count']=$c['count'];
							break;
						}
					}
				}
			}
		}
	}
	
	/*
	print '<pre>';
	//print_r($table);
	print '</pre>';
	*/
	$smarty->assign('table',$table);
}

function show_info(){
	
	global $con,$smarty,$con_multi;
	//print_r($_REQUEST);
	
	$type = $_REQUEST['type'];
	$bid = mi($_REQUEST['bid']);
	$uid = mi($_REQUEST['uid']);
	$date_from = $_REQUEST['date_from'];
	$date_to = $_REQUEST['date_to'];
	
	if ($type == 'deleted_items') {
		//get the related pos row
		$con_multi->sql_query("select pdi.branch_id, pdi.counter_id, pdi.pos_id, pdi.date from pos_delete_items pdi
		left join pos p1 on pdi.branch_id=p1.branch_id and pdi.counter_id=p1.counter_id and pdi.date=p1.date and pdi.pos_id=p1.id
		where pdi.branch_id=$bid and pdi.date between ".ms($date_from)." and ".ms($date_to)." and p1.cashier_id=$uid");
		while ($r = $con_multi->sql_fetchassoc()) {
			$pos_array[$r['branch_id']][$r['counter_id']][$r['pos_id']][$r['date']] = true;
		}
		$con_multi->sql_freeresult();
		
		foreach ($pos_array as $pa_branch_id => $pa_counter) {
			foreach ($pa_counter as $pa_counter_id => $pos) {
				foreach ($pos as $pa_pos_id => $pa_date) {
					foreach ($pa_date as $pa_date_1 => $dummy) {
						//print "$pa_branch_id - $pa_counter_id - $pa_pos_id - $pa_date_1 <br />";
						$qr = $con_multi->sql_query($abc="select pos.*, pos.amount as payment_amount, user.u from pos left join user on pos.cashier_id=user.id
						where pos.branch_id=$pa_branch_id and pos.counter_id=$pa_counter_id and pos.id=$pa_pos_id and pos.date='$pa_date_1'");//print $abc.'<br /><br />';
						$r_qr = $con_multi->sql_fetchassoc($qr);
						$con_multi->sql_freeresult($qr);
						$items[] = $r_qr;
					}
				}
			}
		}
	}
	elseif ($type == 'over_30min') {
		$qr = $con_multi->sql_query($abc="select pos.*, pos.amount as payment_amount, user.u from pos
		left join user on pos.cashier_id=user.id
		where pos.branch_id=$bid and pos.date between ".ms($date_from)." and ".ms($date_to)." and pos.cashier_id=$uid
		and UNIX_TIMESTAMP(pos.end_time)-UNIX_TIMESTAMP(pos.start_time) > 1800 order by end_time");//print $abc;
		$items = $con_multi->sql_fetchrowset($qr);
		$con_multi->sql_freeresult($qr);
	}
	elseif ($type == 'cancelled_bill') {
		$qr = $con_multi->sql_query($abc="select pos.*, pos.amount as payment_amount, user.u from pos
		left join user on pos.cashier_id=user.id
		where pos.branch_id=$bid and pos.date between ".ms($date_from)." and ".ms($date_to)." and pos.cashier_id=$uid
		and pos.cancel_status=1 order by end_time");//print $abc;
		$items = $con_multi->sql_fetchrowset($qr);
		$con_multi->sql_freeresult($qr);
	}
	elseif ($type == 'prune_bill') {
		$qr = $con_multi->sql_query($abc="select pos.*, pos.amount as payment_amount, user.u from pos
		left join user on pos.cashier_id=user.id
		where pos.branch_id=$bid and pos.date between ".ms($date_from)." and ".ms($date_to)." and pos.cashier_id=$uid
		and pos.prune_status=1 order by end_time");//print $abc;
		$items = $con_multi->sql_fetchrowset($qr);
		$con_multi->sql_freeresult($qr);
	}
	elseif ($type == 'total_goods_return') {
	
		//get the related pos row
		$pos_array = array();
		$con_multi->sql_query("select pi.branch_id, pi.counter_id, pi.pos_id, pi.date from pos_items pi
		left join pos p1 on pi.branch_id=p1.branch_id and pi.counter_id=p1.counter_id and pi.date=p1.date and pi.pos_id=p1.id
		where pi.branch_id=$bid and pi.date between ".ms($date_from)." and ".ms($date_to)." and pi.qty<0 and p1.cashier_id=$uid");
		while ($r = $con_multi->sql_fetchassoc()) {
			$pos_array[$r['branch_id']][$r['counter_id']][$r['pos_id']][$r['date']] = true;
		}
		$con_multi->sql_freeresult();
		
		/*
		print '<pre>';
		print_r($pos_array);
		print '</pre>';
		*/
		
		foreach ($pos_array as $pa_branch_id => $pa_counter) {
			foreach ($pa_counter as $pa_counter_id => $pos) {
				foreach ($pos as $pa_pos_id => $pa_date) {
					foreach ($pa_date as $pa_date_1 => $dummy) {
						//print "$pa_branch_id - $pa_counter_id - $pa_pos_id - $pa_date_1 <br />";
						$qr = $con_multi->sql_query($abc="select pos.*, pos.amount as payment_amount, user.u from pos left join user on pos.cashier_id=user.id
						where pos.branch_id=$pa_branch_id and pos.counter_id=$pa_counter_id and pos.id=$pa_pos_id and pos.date='$pa_date_1'");//print $abc.'<br /><br />';
						$r_qr = $con_multi->sql_fetchassoc($qr);
						$con_multi->sql_freeresult($qr);
						$items[] = $r_qr;
					}
				}
			}
		}
	}
	elseif($type == 'special_exemption'){
		$qr = $con_multi->sql_query($abc="select p.*, round(sum(if(type='Cash',pp.amount-p.amount_change,pp.amount)),2) as payment_amount, user.u from pos p
		left join pos_payment pp on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date= pp.date and p.id = pp.pos_id
		left join user on p.cashier_id=user.id
		where p.branch_id=$bid and p.date between ".ms($date_from)." and ".ms($date_to)." and p.cashier_id=$uid
		and p.is_special_exemption=1 and pp.type != 'Rounding' and pp.adjust=0 group by p.branch_id,p.date,p.counter_id, p.id order by end_time");//print $abc;
		$items = $con_multi->sql_fetchrowset($qr);
		$con_multi->sql_freeresult($qr);
	}
	elseif($type == 'remove_service_charges'){
		$qr = $con_multi->sql_query($abc="select p.*, round(sum(if(type='Cash',pp.amount-p.amount_change,pp.amount)),2) as payment_amount, user.u from pos p
		left join pos_payment pp on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date= pp.date and p.id = pp.pos_id
		left join user on p.cashier_id=user.id
		where p.branch_id=$bid and p.date between ".ms($date_from)." and ".ms($date_to)." and p.cashier_id=$uid
		and pos_more_info like '%remove_service_charges%' and pp.type != 'Rounding' and pp.adjust=0 group by p.branch_id,p.date,p.counter_id, p.id order by end_time");//print $abc;
		$items = $con_multi->sql_fetchrowset($qr);
		$con_multi->sql_freeresult($qr);
	}
	else {
		die($type);
	}
	
	$smarty->assign('items',$items);
	$smarty->assign('not_cc', 1);
	$smarty->display('counter_collection.sales_details.tpl');
	
	exit;
}

function load_branches(){
	global $con,$smarty,$con_multi;

	$q_b = $con_multi->sql_query("select id,code from branch") or die(mysql_error());
	while($r = $con_multi->sql_fetchassoc($q_b)){
		$branches[$r['id']] = $r['code'];
	}
	$con_multi->sql_freeresult($q_b);
	$smarty->assign('branches',$branches);
}

?>
