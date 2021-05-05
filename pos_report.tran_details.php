<?php

/*
7/3/2009 5:50 PM jeff
- change Amount to Receipt Amount
- add payment amount and change item_details to get payment items detail from counter collection

1/5/2011 3:35:43 PM Andy
- Change get pos_items method, reduce the loading time.

6/24/2011 5:17:08 PM Andy
- Make all branch default sort by sequence, code.

6/27/2011 5:41:42 PM Andy
- Fix "transaction type" duplicate type description.

7/6/2011 12:12:54 PM Andy
- Change split() to use explode()

8/16/2011 3:22:33 PM Justin
- Added filter for payment type to take the valid type from pos payment.

11/11/2011 11:30:17 AM Andy
- Transaction Details Add mix and match info.

11/28/2011 5:43:34 PM Justin
- Added new filter "FOC".

9/12/2012 4:41 PM Justin
- Enhanced to query to exclude Rounding and Mix & Match payment types.
- Enhanced to show the remark next to payment type.

9/21/2012 3:12 PM Andy
- Fix cancelled receipt cannot show if no payment is made.
- Add checking to limit to only load 30 days of data.

9/24/2012 10:29 AM Justin
- Bug fixed on date arrangement that always return 1 month records while only select 1 day.

10/8/2012 9:59 AM Justin
- Enhnanced to have filter of "Pruned" status.

10/8/2012 4:22 PM Andy
- Add to show transaction cancelled/pruned by.

10/9/2012 4:12 PM Andy
- Reduce memory usage.
- Change default date from is -7 days instead of -30 days.
- Limit report to only show 1000 pos at a time, need user to click "show more" to load additional pos.
- Fix mix and match and foc filter bug.

10/11/2012 10:53 Am Andy
- Add legend to tell user report maximumn show 1000 transaction at a time.

10/18/2012 2:33 PM Justin
- Enhanced to have filter for refund receipt.

10/19/2012 2:16 PM Andy
- Fix prune status wrong.

11/9/2012 10:06:00 PM Fithri
- add filter by discount

12/19/2012 10:00 AM Justin
- Enhanced to use custom payment type from POS Settings.

1/17/2013 12:25 PM Andy
- Change cancel by to who approve the cancel instead of the cashier.

2/1/2013 3:56 PM Fithri
- mix and match promotion change to no need config, always have for all customer

7/4/2013 2:36 PM Andy
- Enhance to show cancel at backend in transaction list.

3/21/2014 4:14 PM Justin
- Enhanced to show custom payment type label if found it is set.

8/22/2014 3:31 PM Justin
- Bug fixed on some of the remarks can't trigger and display properly (mostly happens when remark is text).

1/7/2016 9:57 AM Andy
- Enhance check discount to check discount2.

1/8/2016 10:47 AM Qiu Ying
- Total amount deduct receipt discount and service charges

05/06/2016 13:20 Edwin
- Bug fixed on missing value of maximum report shown.

05/06/2016 17:00 Edwin
- Add new table column "Receipt Remark" at Transaction Details.
- Show member name and card number when transaction type is "Member".

3/2/2017 9:59 AM Justin
- Enhanced to trigger deposit information.

4/19/2017 4:33 PM Justin
- Enhanced to pickup receipt_ref_no.

7/13/2018 4:08 PM Justin
- Enhanced payment type filter to have foreign currency selection.

1/25/2019 3:59 PM Andy
- Fixed $sku_code_list index issue.

3/15/2019 2:18 PM Andy
- Enhanced to load eWallet payment type into payment type selection.

5/3/2019 9:42 PM William
- Add new filter by cashier. 

2/18/2020 3:13 PM Andy
- Fixed to only replace "_" to " " for credit card payment.

2/24/2020 3:58 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

5/5/2020 5:15 PM Justin
- Enhanced to have receipt amount filter.

6/10/2020 9:38 AM William
- Enhanced to change "Receipt No" to dropdown and able filter by receipt no or receipt ref no.

10/7/2020 4:48 PM Andy
- Changed to exclude service charge from pos amount calculation.
- Moved exclude pos_payment rounding & discount to left join.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
include_once('pos_report.include.php');

$maintenance->check(92);

ini_set('memory_limit', '512M');
set_time_limit(0);

$transaction_status_list = array('-1'=>'Cancelled','1'=>'Valid','2'=>'Pruned');
$transaction_type_list = array('-1'=>'Non-member','1'=>'Member');
$goods_return_list = array('1'=>'Yes','-1'=>'No');
$trans_filter = array('goods_return'=>'Goods Return','open_price'=>'Open Price','refund'=>'Refund Only','discount'=>'Discount Only');
$receipt_amt_type_list = array('equal_to'=>'Equal To','between'=>'Between','higher_than'=>'Higher Than','less_than'=>'Less Than');
/*if($config['enable_mix_and_match_promotion'])*/ $trans_filter['got_mm_discount'] = "Mix and Match Promotion Only";
$sheet_size = 1000;

if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

if(!isset($_REQUEST['date_from'])&&!isset($_REQUEST['date_to'])){
	$_REQUEST['date_from'] = date('Y-m-d',strtotime('-7 day',time()));
	$_REQUEST['date_to'] = date('Y-m-d');
}elseif(strtotime($_REQUEST['date_to']) > strtotime("+ 30 day", strtotime($_REQUEST['date_from']))){
	$_REQUEST['date_to'] = date('Y-m-d',strtotime('+30 day',strtotime($_REQUEST['date_from'])));
}
if(!isset($_REQUEST['from_time_Hour'])&&!isset($_REQUEST['to_time_Hour'])){
	$_REQUEST['from_time'] = strtotime('0000');
	$_REQUEST['to_time'] = strtotime('2359');
}else{
    $_REQUEST['from_time'] = strtotime($_REQUEST['from_time_Hour'].$_REQUEST['from_time_Minute']);
    $_REQUEST['to_time'] = strtotime($_REQUEST['to_time_Hour'].$_REQUEST['to_time_Minute']);
}

if (isset($_REQUEST['a'])){
	//$con_multi= new mysql_multi();
	switch($_REQUEST['a']){
	    case 'load_table':
	        load_branches();
			load_table();
			break;
		case 'item_details':
		    item_details();
		    exit;
		case 'ajax_show_more_data':
			ajax_show_more_data();
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;

	}
    //$con_multi->close_connection();
}

$counters = load_counter();

if(BRANCH_CODE == "HQ"){
	$bid_list = load_branches();
}if(!$_REQUEST['counters']){
	$bid_list[$counters[0]['branch_id']] = $counters[0]['branch_id'];
}else{
	$selected_counter = $_REQUEST['counters'];
	list($tmp_bid,$tmp_cid) = explode("|",$_REQUEST['counters']);
	$bid_list[$tmp_bid] = $tmp_bid;
}

$payment_type = array();
foreach($bid_list as $bid=>$bcode){
	// load payment type from pos settings
	$q1 = $con_multi->sql_query("select * from pos_settings where setting_name = 'payment_type' and branch_id = ".mi($bid));

	$ps_info = $con_multi->sql_fetchrow($q1);
	$ps_payment_type = unserialize($ps_info['setting_value']);
	$con_multi->sql_freeresult($q1);

	if($ps_payment_type){
		foreach($ps_payment_type as $ptype=>$val){
			if(!$val) continue;
			//$ptype = ucwords(str_replace("_", " ",$ptype));
			if(strpos(strtolower($ptype), "credit_card")===0){
				$ptype = str_replace("_", " ",$ptype);	// only replace "_" to " " if it is credit card
			}
			$ptype = ucwords($ptype);
			if($ptype == "Credit Card") $ptype = "Credit Cards";
			
			if($payment_type[$ptype])  continue;
			$payment_type[$ptype] = $ptype;
		}
	}
	
	// eWallet
	$q2 = $con_multi->sql_query("select * from pos_settings where setting_name = 'ewallet_type' and branch_id = ".mi($bid));
	$ps_info = $con_multi->sql_fetchrow($q2);
	$ps_payment_type = unserialize($ps_info['setting_value']);
	$con_multi->sql_freeresult($q2);
	
	if($ps_payment_type){
		foreach($ps_payment_type as $ptype=>$val){
			if(!$val) continue;
			
			$ptype = 'ewallet_'.$ptype;
			if($payment_type[$ptype])  continue;
			$payment_type[$ptype] = $ptype;
		}
	}
}

if($payment_type){
	$payment_type['Cash'] = "Cash";
	$pos_config['payment_type'] = $payment_type;
	asort($pos_config['payment_type']);
}

/*cashier_filter*/
$cashier_filter = array();
$sql = "select * from user where active=1 order by u";
$q_u = $con_multi->sql_query($sql);
while($r = $con_multi->sql_fetchassoc($q_u)){
	$cashier[$r['id']] = $r;
}
$con_multi->sql_freeresult($q_u);

//print_r($pos_config);

$smarty->assign('transaction_status', $transaction_status_list);
$smarty->assign('transaction_type', $transaction_type_list);
$smarty->assign('goods_return', $goods_return_list);
$smarty->assign('trans_filter', $trans_filter);
$smarty->assign('pos_config', $pos_config);
$smarty->assign('PAGE_TITLE', 'Transaction Details');
$smarty->assign('sheet_size', $sheet_size);
$smarty->assign('cashier',$cashier);
$smarty->assign('receipt_amt_type_list',$receipt_amt_type_list);
$smarty->display("pos_report.tran_details.tpl");

exit;

function load_table($sqlonly = true){
	global $con,$smarty,$pos_config,$sessioninfo,$con_multi,$config, $sheet_size;
	
	$date_from = $_REQUEST['date_from'];
	$date_to = $_REQUEST['date_to'];
	$counters = $_REQUEST['counters'];
	$payment_type = $_REQUEST['payment_type'];
	$tran_status = $_REQUEST['tran_status'];
	$cashier_id = mi($_REQUEST['cashier_id']);
	$tran_type = $_REQUEST['tran_type'];
	$receipt_no = trim($_REQUEST['receipt_no']);
	$foc = $_REQUEST['foc'];
	$receipt_type = $_REQUEST['receipt_type'];
	
	// these are the param passed by "show more"
	$filter_last_date = $_REQUEST['filter_last_date'];
	$filter_last_time = $_REQUEST['filter_last_time'];
	$filter_bid = mi($_REQUEST['filter_bid']);
	$max_row_no = mi($_REQUEST['max_row_no']);	// use to render row no for template
	
	$filter[] = "pos.date between ".ms($date_from)." and ".ms($date_to);//." and (pos_payment.type not in ('Rounding', 'Mix & Match Total Disc', 'Currency_Adjust') or pos_payment.type is null)";
	
	if($filter_last_date && $filter_last_time)	$filter[] = "pos.date<=".ms($filter_last_date)." and pos.pos_time<".ms($filter_last_time);
	
	//print_r($_REQUEST);
	
	if($counters!='all'){
		list($branch_id,$counter_id) = explode("|",$counters);
		$filter[] = "pos.branch_id=".mi($branch_id);
		if($counter_id!='all'){
			$filter[] = "pos.counter_id=".mi($counter_id);
		}
	}elseif(BRANCH_CODE!='HQ'){
		$filter[] = "pos.branch_id=".mi($sessioninfo['branch_id']);
	}
	if($filter_bid)	$filter[] = "pos.branch_id=$filter_bid";	// additional branch filter for show more data
	
	if($_REQUEST['filter_time']){
		$time_from = $_REQUEST['from_time_Hour'].":".$_REQUEST['from_time_Minute'];
		$time_to = $_REQUEST['to_time_Hour'].":".$_REQUEST['to_time_Minute'];
		$filter[] = "time(pos.pos_time) between ".ms($time_from)." and ".ms($time_to);
	}
	
	if($payment_type!='all'){
		if($payment_type=='Credit Cards'){
		    foreach($pos_config['credit_card'] as $p){
				$cc[] = ms($p);
			}
			$filter[] = "pos_payment.type in (".join(',',$cc).")";
		}elseif($payment_type=='Foreign Currency'){
		    foreach($config['foreign_currency'] as $curr_code=>$curr_info){
				$cc[] = ms($curr_code);
			}
			$filter[] = "pos_payment.type in (".join(',',$cc).")";
		}else{
			$filter[] = "pos_payment.type=".ms($payment_type);
		}
	}

	if($cashier_id > 0 ){
		$filter[] = "pos.cashier_id=".$cashier_id;
	}

	
	if($tran_status!='all'){
		if($tran_status==1){
			$filter[] = "pos.cancel_status=0";
		}elseif($tran_status==2){
            $filter[] = "pos.prune_status=1 and pos.cancel_status=1";
		}else{
            $filter[] = "pos.prune_status=0 and pos.cancel_status=1";
		}
	}
	
	if($tran_type!='all'){
		if($tran_type==1){
			$filter[] = "(pos.member_no<>'0' and pos.member_no is not null and pos.member_no<>'')";
		}else{
			$filter[] = "(pos.member_no='0' or pos.member_no is null or pos.member_no='')";
		}
	}
	
	if($_REQUEST['other_filter']=='goods_return'){
        $goods_return_only = true;
	}elseif($_REQUEST['other_filter']=='open_price'){
        $open_price_only = true;
	}elseif($_REQUEST['other_filter']=='got_mm_discount'){
		$got_mm_discount_only = true;
	}elseif($_REQUEST['other_filter']=='refund'){
		$filter[] = "pos.amount_tender-pos.amount_change < 0";
	}
	if($receipt_no!=''){
		if($receipt_type == 'receipt_no')  $filter[] = "pos.receipt_no=".ms($receipt_no);
		else	$filter[] = "pos.receipt_ref_no=".ms($receipt_no);
	}
	
	if(isset($_REQUEST['sku_code_list'])){
		$sku_code_list = join(",", array_map("ms", $_REQUEST['sku_code_list']));
	    // select sku item id list
     	$con_multi->sql_query("select * from sku_items where sku_item_code in ($sku_code_list)") or die(mysql_error());
		while($r = $con_multi->sql_fetchassoc()){
			$sid_list[] = mi($r['id']);
			$group_item[] = $r;
		}
		$con_multi->sql_freeresult();
	}
	$filter = join(' and ',$filter);
	
	$sql = "select pos.branch_id,pos.id,pos.date,pos.counter_id,pos.receipt_no,pos.member_no,pos.cancel_status,pos.pos_time,pos.amount,pos_payment.id as pos_payment_id,
            group_concat(DISTINCT concat(pos_payment.type, if(pos_payment.remark is not null and pos_payment.remark != '', concat(' (', pos_payment.remark,')'), '')) order by type SEPARATOR ', ') as type, sum(if (pos_payment.type = 'Cash', pos_payment.amount-pos.amount_change,pos_payment.amount)) as payment_amount,
            counter_settings.network_name, user.u, pos.prune_status,pos.pos_more_info, pos.service_charges, pos.receipt_remark,
			sum(if (pos_payment.type = 'Deposit',pos_payment.amount, 0)) as deposit_amount, pos.receipt_ref_no
            from pos
            left join pos_payment on pos.branch_id=pos_payment.branch_id and pos.counter_id=pos_payment.counter_id and pos.id=pos_payment.pos_id and pos.date=pos_payment.date and pos_payment.adjust!=1 and (pos_payment.type not in ('Rounding', 'Mix & Match Total Disc', 'Currency_Adjust') or pos_payment.type is null)
            left join counter_settings on pos.counter_id=counter_settings.id and pos.branch_id=counter_settings.branch_id
            left join user on pos.cashier_id=user.id
            where $filter
            group by pos.branch_id,pos.id,pos.counter_id,pos.date
            order by pos.date desc,pos.pos_time desc";
	if($sessioninfo['u']=='admin'){
		//print $sql;exit;
	}
	
	//print $sql;
	
	$q_p = $con_multi->sql_query($sql);
	$total_row = $con_multi->sql_numrows($q_p);
	$curr_row = 0;
	
	$last_time = 0;
	$last_date = 0;
	$item_count = 0;
	
	while($r = $con_multi->sql_fetchassoc($q_p)){
		$curr_row++;
		if ($_REQUEST['other_filter']=='discount') $receipt_has_discount = false;
		
		$r['pos_more_info'] = unserialize($r['pos_more_info']);
        $r['receipt_remark'] = unserialize($r['receipt_remark']);
		if($r['cancel_status'] && $r['pos_more_info']['cancel_at_backend']){
			$r['cancel_at_backend'] = 1;
		}
		
        //get membership card name and number
        if($r['member_no'] != '') {
            $filter_date = date('Y-m-d', strtotime('+1 day', strtotime($r['date'])));
            $q_m = $con_multi->sql_query("select m.name
                                    from membership_history mh
                                    join membership m on m.nric=mh.nric
                                    where mh.card_no=".ms($r['member_no'])." and mh.added < ".ms($filter_date)."
                                    order by mh.added desc
                                    limit 1");
            $member_info = $con_multi->sql_fetchrow($q_m);
            $r['member_name'] = $member_info['name'];
			$con_multi->sql_freeresult($q_m);
        }
        
		//print "$curr_row / $total_row<br>";
		
		foreach($pos_config['payment_type_label'] as $type=>$type_label){
			if(preg_match("/".$type."/", $r['type'])){
				$r['type'] = preg_replace("/".$type."/", $type_label, $r['type']);
			}
		}
		
		$r['type'] = str_replace("),", "), <br />", $r['type']);
	    $bid = mi($r['branch_id']);
	    $pos_id = mi($r['id']);
	    $date = trim($r['date']);
	    $counter_id = mi($r['counter_id']);
	    
	    $total_qty = 0;
	    $got_goods_return = false;
	    $open_price_amt = 0;
	    $found_sku = false;
	    $got_foc = false;
	    	    
	    // get items
	    $q_pi = $con_multi->sql_query($a="select pi.*
		from pos_items pi
		where pi.branch_id=$bid and pi.pos_id=$pos_id and pi.counter_id=$counter_id and pi.date=".ms($date));
		$total_discount = 0;
		while($pi = $con_multi->sql_fetchassoc($q_pi)){
			if ($_REQUEST['other_filter']=='discount') {
				if ($pi['discount'] || $pi['discount2']) {
					$receipt_has_discount = true;
				}
			}
		
			$total_discount += $pi['discount2'];
			
            $total_qty += $pi['qty'];
            $sales_amt = mf($pi['price']-$pi['discount']);
            
            // this is goods return item
            if($pi['qty']<0) $got_goods_return = true;
            
            // this is open price item
            if($pi['open_price_by']>0)   $open_price_amt += $sales_amt;
            
            if($sid_list && !$found_sku){  // got filter sku
				if(in_array($pi['sku_item_id'], $sid_list)){
                    $found_sku = true;
				}
			}
			
			if($pi['qty']>0 && $sales_amt<=0)	$got_foc = true;
		}
		$con_multi->sql_freeresult($q_pi);
		if ($_REQUEST['other_filter']=='discount' && !$receipt_has_discount) {
			// final check mix match discount
			$q_pmm = $con_multi->sql_query("select id from pos_mix_match_usage where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and pos_id=$pos_id limit 1");
			$mm_disc_count = $con_multi->sql_numrows($q_pmm);
			$con_multi->sql_freeresult($q_pmm);
			
			if(!$mm_disc_count){
				$curr_row--;
				$total_row--;
				continue;
			}			
		}
		
		// got foc filter
		if($foc){
			if($foc == 'yes' && !$got_foc)	continue;
			if($foc == 'no' && $got_foc)	continue;	
		}
	    
	    // checking condition
	    if($goods_return_only && !$got_goods_return)    continue;   // only show got goods return
	    if($open_price_only && !$open_price_amt)    continue;   // only show open price
	    if($sid_list && !$found_sku)    continue;   // got sku filter
	    
	    $r['got_goods_return'] = $got_goods_return;
	    $r['total_qty'] += $total_qty;
	    $r['open_price'] += $open_price_amt;
	    
	    // check whether got mix and match
	    //if($config['enable_mix_and_match_promotion']){ //ignore this config
	    if(true){
	    	$q_pmm = $con_multi->sql_query("select id from pos_mix_match_usage where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and pos_id=$pos_id limit 1");
			$r['got_mm_discount'] = $con_multi->sql_numrows($q_pmm)>0 ? 1 : 0;
			$con_multi->sql_freeresult($q_pmm);
			
			if($got_mm_discount_only && !$r['got_mm_discount'])	continue;	// only show got mix and match discount		
	    }
	    
	    // this pos need to show
	    $item_count++;
	    if($sheet_size && ($last_date != $r['date'] || $last_time != $r['pos_time'])){
			if($item_count > $sheet_size){
				break;
			}
		}
		$last_time = $r['pos_time'];
	    $last_date = $r['date'];
		
	    // check cancel by who
	    if($r['cancel_status']){
	    	$q_cb = $con_multi->sql_query("select prc.cancelled_by, user.u as cancelled_by_u 
	    	from pos_receipt_cancel prc
	    	left join user on user.id=prc.verified_by
	    	where prc.branch_id=$bid and prc.counter_id=$counter_id and prc.date=".ms($date)." and prc.receipt_no=".ms($r['receipt_no'])." 
	    	order by prc.cancelled_time desc limit 1");
	    	$tmp = $con_multi->sql_fetchassoc($q_cb);
	    	$con_multi->sql_freeresult($q_cb);

	    	if(is_array($tmp) && $tmp)	$r = array_merge($r, $tmp);
	    	unset($tmp);
	    }
	    if($sessioninfo['u']=='admin'){
	    	/*print "$curr_row / $total_row : ".memory_get_usage()."<br>";
	    	if(memory_get_usage()>400000000){
	    		print(memory_get_usage());exit;
	    	}*/
	    }
		
		$r['amount'] = $r['amount'] - $total_discount;// + $r["service_charges"];
		
		// if found user got put figures on "receipt amount" filter
		if(($_REQUEST['receipt_amt_type'] != "between" && $_REQUEST['default_receipt_amt_val']) || ($_REQUEST['receipt_amt_type'] == "between" && ($_REQUEST['min_receipt_amt_val'] || $_REQUEST['max_receipt_amt_val']))){
			if($_REQUEST['receipt_amt_type'] == "equal_to"){ // user filter with "equal to"
				if(mf($r['amount']) != mf($_REQUEST['default_receipt_amt_val'])) continue;
			}elseif($_REQUEST['receipt_amt_type'] == "between"){ // user filter with "between"
				if(mf($r['amount']) < mf($_REQUEST['min_receipt_amt_val']) || mf($r['amount']) > mf($_REQUEST['max_receipt_amt_val'])) continue;
			}elseif($_REQUEST['receipt_amt_type'] == "less_than"){ // user filter with "less than"
				if(mf($r['amount']) > mf($_REQUEST['default_receipt_amt_val'])) continue;
			}elseif($_REQUEST['receipt_amt_type'] == "higher_than"){ // user filter with "higher than" 
				if(mf($r['amount']) < mf($_REQUEST['default_receipt_amt_val'])) continue;
			}
		}
		
		$table[$r['branch_id']][] = $r;
		$total[$r['branch_id']]['payment_amount'] += $r['payment_amount'];
		$total[$r['branch_id']]['total_qty']+= $total_qty;
		$total[$r['branch_id']]['amount']+= $r['amount'];
		$total[$r['branch_id']]['deposit_amount']+= $r['deposit_amount'];
	}
	$con_multi->sql_freeresult($q_p);
	//print_r($table);
	
	$can_show_more = $total_row > $curr_row ? 1 :0;
	
	$smarty->assign('branch_id',$branch_id);
	$smarty->assign('counter_id',$counter_id);
	$smarty->assign('table',$table);
	$smarty->assign('total',$total);
	$smarty->assign('group_item',$group_item);
	$smarty->assign('filter_last_date', $last_date);
	$smarty->assign('filter_last_time', $last_time);
	$smarty->assign('can_show_more', $can_show_more);
	
	if(!$sqlonly){
		//print_r($table);
		$ret = array();
		if($table){
			foreach($table as $tmp_bid => $p_list){
				foreach($p_list as $p){
					$max_row_no++;
					$smarty->assign('row_no', $max_row_no);
					$smarty->assign('p', $p);
					$ret['html'] .= $smarty->fetch('pos_report.tran_details.row.tpl');
				}			
			}
		}
		$ret['ok'] = 1;
		$ret['max_row_no'] = $max_row_no;
		$ret['filter_last_date'] = $last_date;
		$ret['filter_last_time'] = $last_time;
		
		print json_encode($ret);
	}
}

function item_details(){
    global $con,$smarty,$bid,$counter_id,$f_date,$con_multi;

	$bid = intval($_REQUEST['branch_id']);
	$counter_id = intval($_REQUEST['counter_id']);
	
	$filter[] = 'branch_id='.mi($bid);
    $filter[] = 'counter_id='.mi($counter_id);
    $filter[] = 'date='.ms($_REQUEST['date']);
    $filter[] = 'pos_id='.mi(intval($_REQUEST['id']));

    $filter = join(' and ', $filter);

	$sql = "select pos_items.*,sku_items.description,sku_items.sku_item_code,sku_items.mcode from pos_items left join sku_items on pos_items.sku_item_id=sku_items.id where $filter";
	$con_multi->sql_query($sql) or die(mysql_error());
	$temp = $con_multi->sql_fetchrowset();
	$con_multi->sql_freeresult();
	foreach($temp as $r){
	    $r['selling_price'] = $r['price']-$r['discount'];
	    if($r['price']!=0){
            $r['discount_per'] = ($r['discount']/$r['price'])*100;
		}
	    
		$table[] = $r;
		
		$total['qty'] += $r['qty'];
		$total['price'] += $r['price'];
		$total['discount'] += $r['discount'];
		$total['selling_price'] = $total['price']-$total['discount'];
		if($total['price']!=0){
            $total['discount_per'] = ($total['discount']/$total['price'])*100;
		}
	}
    // get receipt details
	$filter = array();
	$filter[] = 'pos.branch_id='.mi($bid);
    $filter[] = 'pos.counter_id='.mi($counter_id);
    $filter[] = 'pos.date='.ms($_REQUEST['date']);
    $filter[] = 'pos.id='.mi(intval($_REQUEST['id']));
    $filter = join(' and ', $filter);
    $sql = "select pos.*,user.u from pos left join user on pos.cashier_id=user.id where $filter";
    $q_rec = $con_multi->sql_query($sql) or die(mysql_error());
    $receipt_detail = $con_multi->sql_fetchrow($q_rec);
	$con_multi->sql_freeresult($q_rec);
    
	$smarty->assign('item_details',$table);
	$smarty->assign('total',$total);
	$smarty->assign('receipt_detail',$receipt_detail);
	$smarty->display('pos_live.item_details.tpl');
}

function ajax_show_more_data(){
	global $con, $smarty;
	
	//print_r($_REQUEST);
	
	load_table(false);
}
?>
