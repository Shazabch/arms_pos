<?php
/*
2/6/2009 6:56:00 PM Jeff
- fixed cashier sales date bugs.

3/2/2009 3:30:00 PM Jeff
- fixed $start and $end timestamp for cash_history in generate_data

11/11/2009 6:20:27 PM edward
- fixed history point filter by branch

11/17/2009 12:27:16 PM edward
- clear drawer/close counter checking

12/9/2009 2:48:48 PM edward
- fix close counter checking select id and network_name

6/16/2010 11:34:20 AM yinsee
- besides update pos_finalized, still neeed to update pos_tracking

6/21/2010 11:01:52 AM Andy
- Fix a bugs which happen if got payment type adjustment will cause "Sales Details" to display duplicate entry.

6/24/2010 10:21:26 AM Andy
- Fix counter collection cannot cancel receipt bugs.

7/20/2010 10:59:54 AM Andy
- Add privilege to check whether user can do un-finalize counter collection or not. (System admin always can)

7/22/2010 3:39:33 PM Andy
- Date format change from d/m/y to Y-m-d

7/29/2010 3:51:28 PM Andy
- Change finalized permission to use privilege.

7/30/2010 11:49:06 AM Andy
- Fix counter collection sometime may occur sql error when doing finalize.

8/4/2010 1:40:50 PM Alex
- Fix showing receipt details by adding branch_id

11/10/2010 4:02:54 PM Justin
- Fixed the bugs where getting empty sales detail from membership history.

12/17/2010 5:33:31 PM Andy
- Add can change payment type 'others'.

12/21/2010 12:00:27 PM Alex
- check $config['csa_generate_report'] to create CSA report flag file

1/6/2011 10:48:28 AM Justin
- Fixed the bugs for calculating payment amount called from membership history.

2/10/2011 3:29:47 PM Andy
- Fix unfinalize will not trigger system to recalculate stock balance.

3/28/2011 4:24:32 PM Justin
- Added to retrieve extra info when view receipt info in item details.

3/29/2011 10:51:59 AM Justin
- Added member no information for receipt detail.

4/11/2011 12:28:59 PM Andy
- Check if counter version is later than v109 then no need to +8 hour to cash domination timestamp.

4/7/2011 6:10:43 PM Alex
- Add transaction details to group payment by pos receipt.

4/13/2011 5:29:33 PM Andy
- Add popup details for over amount transaction.

4/18/2011 5:57:15 PM Andy
- Fix if create new cash domination will also check counter revision to decide whether timestamp need to -8 hours.

4/29/2011 5:20:09 PM Andy
- Add checking if counter revision is later than v109, then it will check if last cash domination early than last pos, it will +8 hours.

4/29/2011 5:39:05 PM Alex
- add get return item, item discount and open discount user from item_details() 

5/4/2011 10:32:43 AM Andy
- Fix wrong timestamp checking.

6/10/2011 10:59:18 AM Andy
- Add print transaction details.

6/21/2011 5:17:59 PM Andy
- Fix "over" popup cannot show those receipt only got goods return but without pos payment.

6/21/2011 6:03:55 PM Alex
- add payment type approve by which user

6/24/2011 3:57:10 PM Andy
- Make all branch default sort by sequence, code.

7/4/2011 4:20:00 PM Andy
- Add config to counter collection to check whether need to +8 hours to latest cash denomination.

7/7/2011 3:13:43 PM Andy
- Fix a bugs cause counter collection change payment type may update wrong POS.

9/5/2011 9:58:04 AM Andy
- Fix cancel receipt bugs, the log store empty receipt number and din't no indicate it is activate or cancel receipt. 

9/6/2011 6:21:34 PM Alex
- add show multiple type of credit cards

9/20/2011 2:17:38 PM Andy
- Fix when change payment type will make rounding amount missing.

11/11/2011 11:09:37 AM Andy
- Fix counter collection to also show those mix and match discount which does not have discount amount. (eg: Free Voucher)

11/23/2011 5:46:41 PM Alex
- store counter collection data into database

11/24/2011 4:42:56 PM Alex
- fix calculation while storing 

12/20/2011 9:56:34 AM Andy
- Add show "Receipt Remark" at Receipt Item Details.

2/13/2012 6:22:53 PM Justin
- Added to pick up Deposit info by counter.
- Modified to capture deposit info while view in item details.

2/29/2012 4:50:43 PM Justin
- Fixed the bugs when loading sales detail for member point history, it did not filter by branch.

3/9/2012 1:00:56 PM Andy
- Change finalize counter collection to submit by ajax, also add popup to show progress percentage.

4/12/2012 3:33:12 PM Justin
- Added to store deposit data into pos_counter_finalize.

5/3/2012 4:48:37 PM Andy
- Add to store trade in and top up data when finalize.
- Add "Top Up" information.
- Add "Trade In" information.

5/29/2012 4:06:00 PM Andy
- Fix when add new cash advance it wont record original amount.

6/27/2012 4:37:32 PM Justin
- Fixed the sales detail that shouldn't sum up rounding amount.

8/3/2012 11:10 AM Andy
- Add mix & match discount into collection.

8/13/2012 3:02 PM Andy
- Fix Transaction Details popup bug (sometime it group different receipt into 1 row).

9/3/2012 11:47 AM Fithri
- Item details - show barcode

9/14/2012 4:34:00 PM Fithri
- add config to check must hv cash denom before finalize.
- add config to able to show multiple cashier name instead of only last cashier name

10/8/2012 10:30 AM Andy
- Add checking user privilege for cancel receipt (need config counter_collection_need_privilege_cancel_bill).

10/15/2012 2:10:00 PM Fithri
- Enhance log for Counter Collection to include counter_id and date (and other details)

2/5/2013 3:18 PM Fithri
- add adjusted payment receipt can revert to old payment type

2/25/2013 3:58 PM Fithri
- add button close.
- add checking if is add new cash denom, no key in any amount then cannot save
- can delete the cash denom create from backend

07/31/2013 11:49 AM Justin
- Enhanced to generate/delete pos_cashier_finalize while doing finalize/unfrinalize.

11/5/2013 11:45 AM Fithri
- change all term "Cash Domination" to "Cash Denomination"

12/30/2013 11:09 AM Andy
- Change to do not delete pos_finalized when unfinalize counter collection.
- Add auto check and pregen pos_finalized row if not found.

1/6/2014 5:13 PM Fithri
- make cash-advance value always negative
- make top-up value always positive
- show positive (absolute) value on screen

1/14/2014 3:44 PM Andy
- Remove clear_drawer=1 checking for pos_cash_domination.

1/22/2014 5:30 PM Justin
- Enhanced to show name of cashier and collected by instead of ID.

2/13/2014 10:17 AM Fithri
- when cancel receipt at counter collection, it will also deletes records on membership promotion items

3/24/2014 5:56 PM Justin
- Modified the wording from "Finalize" to "Finalise".
*/

//$con = new sql_db('hq.12shoppkt.com','arms_slave','arms_slave','arms_pkt');
//$con = new sql_db('jwt-uni.dyndns.org','arms','4383659','armshq');
//$con = new sql_db('ws-hq.arms.com.my:4001','arms_slave','arms_slave','armshq');
//$con = new sql_db('cwmhq.no-ip.org:4001','arms','sc440','armshq');

// Total sales = sum(amount_tender - amount_changed)
// Total actual sales = sum(price-discount)

if ($_SERVER['SERVER_NAME'] == 'maximus')
{
	if (BRANCH_CODE == 'JITRA')
	{
		$con = new sql_db("hq.aneka.com.my", "arms_slave", "arms_slave", "armshq");
	}
}
class CounterCollection extends Module
{
	var $branch_id;
	var $pagesize = 25;
	var $notify_users;
	var $approvals;
	var $is_approval = false;
	var $data = array();
	var $cash_advance = array();
	var $cash_domination = array();
	var $odata = array();
	var $over =array();
	var $adjustment = array();
	var $variance = array();
	var $cd_info = array();
	var $counter_sales = array();
	var $grandtotal = array();
	var $type_adjustment = array();
	var $xtra = array();
	var $trans = array();
	var $counter_data = array();
	var $got_mm_discount = false;
	var $got_top_up = false;
	
	function __construct($title, $template='')
	{
		global $con, $smarty, $v109_time;
		
		//$this->is_approval = is_approval('COUNTER_COLLECTION', $this->aprovals, $this->notify_users);
		$this->is_approval = privilege('CC_FINALIZE');
		$this->branch_id = $this->get_branch_id();
		$this->v109_time = $v109_time;
		
		$branch_id = $this->branch_id;
		
		$con->sql_query("select * from counter_settings where branch_id = ".mi($branch_id));
		
		while($r = $con->sql_fetchrow())
		{
			$counters[$r['id']] = $r;
		}
		
		$smarty->assign("counters",$counters);
		
		$con->sql_query("select u.id, u.u, up.privilege_code from user u left join user_privilege up on u.id = up.user_id and u.default_branch_id = up.branch_id ");
		while($r = $con->sql_fetchrow())
		{
			$username[$r['id']] = $r['u'];
		}
		
		$smarty->assign("username", $username);

		if (BRANCH_CODE != 'HQ')
		{
		 	 $branch_sql = ' and pcct.branch_id = '.mi($branch_id);
		}

		// get counter errors
		$con->sql_query("select pcct.*, cs.network_name, branch.code from pos_counter_collection_tracking pcct left join counter_settings cs on cs.id = pcct.counter_id and cs.branch_id = pcct.branch_id left join branch on branch.id = pcct.branch_id where error <> '' $branch_sql order by branch.sequence, branch.code");
		$collection_error = $con->sql_fetchrowset();
		$smarty->assign("collection_error", $collection_error);

		if (!isset($_REQUEST['no_display']))	parent::__construct($title, $template);

	}
	
	function view_by_date()
	{
		$this->_default();
	}
	
	function _default()
	{
		global $smarty;
		if ((!isset($_REQUEST['fsearch_submit']) && !isset($_REQUEST['date_select'])) || $_REQUEST['date_select'] == '')
		{
			$_REQUEST['date_select'] = date('Y-m-d');
		}

		//check invalid SKU ====================
		if (isset($_REQUEST['branch_id']))
			$branch_id = mi($_REQUEST['branch_id']);
		else
			$branch_id = mi($this->branch_id);
		
		check_invalid_code($branch_id,$_REQUEST['date_select']);
		//=======================end here
		
		
		$this->list_data($_REQUEST['date_select']);
		$smarty->assign("allow_edit",$this->is_approval);
		if (!isset($_REQUEST['no_display']))	$this->display();
	}
	
	// show transactions by payment type	
	function sales_details()
	{
		global $con, $smarty, $pos_config, $mm_discount_col_value;

		if (isset($_REQUEST['type']))
		{
		    if(strpos($_REQUEST['type'], 'special-')!==false){  // is special type
    			if($_REQUEST['type']=='special-over')	$special_over_only = true;
    			if($_REQUEST['type']=='special-'.$mm_discount_col_value)	$got_mm_discount = true;
				if($_REQUEST['type']=='special-deposit_r')	$only_deposit_rcv = true;
				if($_REQUEST['type']=='special-deposit_u')	$only_deposit_used = true;
				if($_REQUEST['type']=='special-deposit_cr')	$only_deposit_cancel_rcv = true;
				if($_REQUEST['type']=='special-deposit_cu')	$only_deposit_cancel_used = true;
				if($_REQUEST['type']=='special-trade_in')	$got_trade_in = true;
				if($_REQUEST['type']=='special-trade_in_writeoff')	$got_trade_in_writeoff = true;
			}else{
				if (strtolower($_REQUEST['type']) == 'credit cards')
				{
					foreach($pos_config['credit_card'] as $k)
					{
						$types[] = ms($k);
					}
					$types = join(",", $types);
				}
				else
					$types = ms($_REQUEST['type']);
				$where[] = "pp.type in ($types)";
			}
		}
		
		if (isset($_REQUEST['counter_id']))	$where[] = " p.counter_id = ".mi($_REQUEST['counter_id']);
		if (isset($_REQUEST['e'])) $where[] = " p.pos_time <= ".ms($_REQUEST['e']);
		if (isset($_REQUEST['s'])) $where[] = "p.pos_time >= ".ms($_REQUEST['s']);
		
		if (isset($_REQUEST['card_no'])) 
		{
			//$where[] = "p.member_no = (select m.card_no from membership_history m where m.card_no = ".ms($_REQUEST['card_no'])." group by m.card_no) and p.branch_id = ".mi($_REQUEST['branch_id']);
			$where[] = "p.member_no = ".ms($_REQUEST['card_no'])." and p.branch_id = ".mi($_REQUEST['branch_id']);
		}
		else
		{
			//$select = "round(if(type='Cash',pp.amount-p.amount_change,pp.amount),2) as payment_amount";
	        if (isset($_REQUEST['branch_id']))
			$where[] = "p.branch_id = ".mi($_REQUEST['branch_id']);
			else
			$where[] = "p.branch_id = ".mi($this->branch_id);
		}
		
		$select = "round(sum(if(type='Cash',pp.amount-p.amount_change,pp.amount)),2) as payment_amount";
		$groupby = 'group by p.branch_id,p.date,p.counter_id, p.id';
		
		// 6/21/2010 10:49:33 AM Andy - to hide those pos payment already got adjustment
		$where[] = "(pp.adjust <> 1 or pp.id is null)";
		$where = implode(" and ", $where);
		
		$q1 = $con->sql_query("select p.*, pp.type, pp.adjust, pp.changed, user.u ,$select 
			from pos p
			left join pos_payment pp on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date= pp.date and p.id = pp.pos_id			  
			left join user on p.cashier_id = user.id 
			where p.date = ".ms($_REQUEST['date'])." and pp.type != 'Rounding' and $where $groupby order by p.pos_time");

        while($r = $con->sql_fetchassoc($q1)){
            // cc2 don hv currency
		    /*$currency_arr = $this->pp_is_currency($r['remark'], $r['payment_amount']);
            if($currency_arr['is_currency']){   // it is foreign currency
				$r['payment_amount'] = $currency_arr['rm_amt'];
			}*/

			$r['over_amt'] = round($r['amount_tender'] - $r['amount'] - $r['amount_change'], 2);

			// only show over receipt
			if($special_over_only && !$r['over_amt'])   continue;
			
			// check only show got mix and match
			if($got_mm_discount){
				$q_pmm = $con->sql_query("select id from pos_mix_match_usage where branch_id=$r[branch_id] and counter_id=$r[counter_id] and date=".ms($r['date'])." and pos_id=$r[id] limit 1");
				$passed = $con->sql_numrows($q_pmm)>0 ? 1 : 0;
				$con->sql_freeresult($q_pmm);
				if(!$passed)	continue;
			}elseif($only_deposit_rcv || $only_deposit_used || $only_deposit_cancel_rcv || $only_deposit_cancel_used){
				if($only_deposit_rcv) $filter_type = "RECEIVED";
				elseif($only_deposit_used) $filter_type = "USED";
				elseif($only_deposit_cancel_rcv) $filter_type = "CANCEL_RCV";
				else $filter_type = "CANCEL_USED";
				$q_deposit = $con->sql_query("select * from pos_deposit_status_history where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and pos_date=".ms($r['date'])." and pos_id=".mi($r['id'])." and type = ".ms($filter_type));

				$passed = $con->sql_numrows($q_deposit)>0 ? 1 : 0;
				if(!$passed)	continue;
			}elseif($got_trade_in || $got_trade_in_writeoff){
				$q_ti =$con->sql_query("select id from pos_items pi where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and date=".ms($r['date'])." and pos_id=".mi($r['id'])." and trade_in_by>0".( $got_trade_in_writeoff ? " and writeoff_by>0" : '')." limit 1");
				$passed = $con->sql_numrows($q_ti)>0 ? 1 : 0;
				$con->sql_freeresult($q_ti);
				if(!$passed)	continue;
			}
			
			$items[] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign('items', $items);

		if (isset($_REQUEST['type']) && $_REQUEST['type']!="Cash" && strpos($_REQUEST['type'], 'special-')===false){
			//payment summary except Cash
			$p_sql = "select p.*, pp.remark, pp.type, sum(pp.amount) as payment_amount from
				pos_payment pp
				left join pos p on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date= pp.date and p.id = pp.pos_id
				where p.date = ".ms($_REQUEST['date'])." and p.cancel_status=0 and $where group by p.receipt_no order by pp.remark";
//print $p_sql."<br />";
			$con->sql_query($p_sql);
			while($p = $con->sql_fetchassoc()){
				$payment_type[$p['type']][$p['remark']]['payment_amount'] += $p['payment_amount'];
				$receipt_no[$p['remark']][$p['receipt_no']]['pos_id']=$p['id'];
				$receipt_no[$p['remark']][$p['receipt_no']]['date']=$p['date'];
				$receipt_no[$p['remark']][$p['receipt_no']]['cashier_id']=$p['cashier_id'];
				$receipt_no[$p['remark']][$p['receipt_no']]['counter_id']=$p['counter_id'];
				$receipt_no[$p['remark']][$p['receipt_no']]['branch_id']=$p['branch_id'];
				$receipt_no[$p['remark']][$p['receipt_no']]['payment_amount']+=$p['payment_amount'];
			}
			$con->sql_freeresult();

			if ($payment_type) {
				foreach ($payment_type as $ptype => $other){
					foreach ($other as $remark => $dummy){ 
						$payment_type[$ptype][$remark]['rowspan'] = count($receipt_no[$remark]);
					}
				}
			}

	        $smarty->assign('payment_type', $payment_type);
	        $smarty->assign('receipt_no', $receipt_no);
		}

		$smarty->display('counter_collection.sales_details.tpl');
	}
	
	// show transaction details?
	function item_details()
	{
		global $con,$smarty;

		if (isset($_REQUEST['branch_id']))
			$branch_id = $_REQUEST['branch_id'];
		else
			$branch_id = $this->branch_id;
			
		$filter = "where p.branch_id = ".mi($branch_id)." and p.date = ".ms($_REQUEST['date'])." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.id = ".mi($_REQUEST['pos_id']);
		
		$con->sql_query("select * from pos p $filter");
		$pos = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$pos['receipt_remark'] = unserialize($pos['receipt_remark']);

		/*$con->sql_query("select p.counter_id, u.u as cashier_name,op_u.u as open_price_user, id_u.u as item_discount_user, r_u.u as return_user, p.receipt_no, p.pos_time, p.member_no, pi.pos_id, 
						 amount_change, pi.qty, pi.price, pi.discount, si.mcode, si.sku_item_code, si.description
						 from pos p
						 left join pos_items pi on p.branch_id = pi.branch_id and p.counter_id = pi.counter_id and p.date= pi.date and p.id = pi.pos_id
						 left join sku_items si on pi.sku_item_id = si.id
						 left join user u on u.id = p.cashier_id
						 left join user op_u on pi.open_price_by=op_u.id
						 left join user id_u on pi.item_discount_by=id_u.id
						 left join user r_u on pi.return_by=r_u.id
						 $filter");
		$items = $con->sql_fetchrowset();*/

		$q1 = $con->sql_query("select cs.network_name, p.counter_id, u.u as cashier_name,op_u.u as open_price_user, 
							   id_u.u as item_discount_user, r_u.u as return_user, p.receipt_no, p.pos_time, p.member_no, 
							   pi.pos_id, amount_change, pi.qty, pi.price, pi.discount, pi.more_info, si.mcode, si.sku_item_code, 
							   si.description, p.deposit,pi.remark,pi.trade_in_by,pi.writeoff_by, u_ti.u as trade_in_by_u, u_tiw.u as writeoff_by_u,pi.verify_code_by,pi.barcode,u_verify.u as verify_code_by_u
							   from pos p
							   left join pos_items pi on p.branch_id = pi.branch_id and p.counter_id = pi.counter_id and p.date= pi.date and p.id = pi.pos_id
							   left join sku_items si on pi.sku_item_id = si.id
							   left join user u on u.id = p.cashier_id
							   left join user op_u on pi.open_price_by=op_u.id
							   left join user id_u on pi.item_discount_by=id_u.id
							   left join user r_u on pi.return_by=r_u.id
							   left join user u_ti on u_ti.id=pi.trade_in_by
							   left join user u_tiw on u_tiw.id=pi.writeoff_by
							   left join user u_verify on u_verify.id=pi.verify_code_by
							   left join counter_settings cs on cs.id = p.counter_id and cs.branch_id = p.branch_id
							   $filter");

		while($r = $con->sql_fetchassoc($q1)){
			if($r['deposit']){
				$q2 = $con->sql_query("select pdsh.*, p.amount, pp.amount as payment_amount, p.cancel_status, 
									   p.prune_status, pp.type as payment_type
									   from pos_deposit_status_history pdsh
									   left join pos_payment pp on pp.pos_id = pdsh.pos_id and pp.branch_id = pdsh.branch_id and pp.counter_id = pdsh.counter_id and pp.date = pdsh.pos_date
									   left join pos p on p.id = pp.pos_id and p.branch_id = pp.branch_id and p.date = pp.date and p.counter_id = pp.counter_id
									   where pdsh.branch_id = ".mi($branch_id)." and pdsh.pos_date = ".ms($_REQUEST['date'])." and pdsh.counter_id = ".mi($_REQUEST['counter_id'])." and pdsh.pos_id = ".mi($_REQUEST['pos_id']));
	
				$items_info = $con->sql_fetchassoc($q2);
				$con->sql_freeresult($q2);

				$q3 = $con->sql_query("select pd.item_list, pd.receipt_no, pd.pos_time, cs.network_name, u.u as cashier_name
									   from pos_deposit pd
									   left join counter_settings cs on cs.id = pd.counter_id and cs.branch_id = pd.branch_id
									   left join user u on u.id = pd.cashier_id
									   where pd.pos_id = ".mi($items_info['deposit_pos_id'])." and pd.date = ".ms($items_info['deposit_pos_date'])." and pd.branch_id = ".mi($items_info['deposit_branch_id'])." and pd.counter_id = ".mi($items_info['deposit_counter_id']));

				if($con->sql_numrows($q3) > 0){
					$tmp_deposit_info = $con->sql_fetchassoc($q3);
					$tmp_deposit_info['pos_time'] = date("Y-m-d H:i:s", $tmp_deposit_info['pos_time']);
					//print_r($tmp_deposit_info);
					$items_info['item_list'] = $tmp_deposit_info['item_list'];

					if($items_info['type'] == "CANCEL_RCV" || $items_info['type'] == "CANCEL_USED"){
						$cancel_deposit_info = $tmp_deposit_info;
					}
				}
				$con->sql_freeresult($q3);
				
				if($items_info['item_list']){
					$item_list = unserialize($items_info['item_list']);
					//print_r($item_list);
					foreach($item_list as $row=>$f){
						$di['barcode'] = $f['barcode'];
						$di['qty'] = $f['quantity'];
						$di['sku_item_code'] = $f['item']['sku_item_code'];
						$di['description'] = $f['item']['receipt_description'];
						$di['uom_code'] = $f['item']['uom_code'];
						$di['selling_price'] = $f['fixed_price'];
						$deposit_items[] = $di;
					}
				}

				if($item_info['type'] == "USED") $deposit_amt = $items_info['payment_amount'];
				else $deposit_amt = abs($items_info['amount']);

				$r['deposit_amount'] = $deposit_amt;
				$deposit_info = $r;
				
				$smarty->assign("cancel_deposit_info", $cancel_deposit_info);
				$smarty->assign("deposit_info", $deposit_info);
				$smarty->assign("is_deposit", true);
			}else {
				$more = unserialize($r['more_info']);
				if(isset($more['discount_str']))
					$r['discount_str'] = $more['discount_str'];
				$items[] = $r;
			}
		}
		$con->sql_freeresult($q1);
		$smarty->assign('deposit_items',$deposit_items);
		$smarty->assign('items',$items);

		$smarty->assign("amount_change", $items[0]['amount_change']);
		
		$con->sql_query("select pp.*, ap_u.u as approved_by from pos_payment pp
							left join user ap_u on pp.approved_by=ap_u.id
							where pp.branch_id = ".mi($branch_id)." and pp.date = ".ms($_REQUEST['date'])." and pp.counter_id = ".mi($_REQUEST['counter_id'])." and pp.pos_id=".mi($_REQUEST['pos_id'])." and pp.adjust <> 1");
		while($r = $con->sql_fetchassoc()){
			$payment[] = $r;
		}
		$con->sql_freeresult();
		
		// check mix and match info
		$con->sql_query("select * from pos_mix_match_usage where branch_id=".mi($branch_id)." and date=".ms($_REQUEST['date'])." and counter_id=".mi($_REQUEST['counter_id'])." and pos_id=".mi($_REQUEST['pos_id'])." order by id");
		while($r = $con->sql_fetchassoc()){
			$r['more_info'] = unserialize($r['more_info']);
			$pos_mix_match_usage_list[] = $r;
		}
		$con->sql_freeresult();
		
        $smarty->assign('payment',$payment);
        
        //print_r($pos_mix_match_usage_list);
        $smarty->assign('pos_mix_match_usage_list',$pos_mix_match_usage_list);
        
        $smarty->assign('pos', $pos);
        $smarty->assign('payment',$payment);
		$smarty->display('counter_collection.item_details.tpl');
	}
	
	function ajax_change_cash_credit()
	{
		global $con,$pos_config;
		
		$is_cc = 0;
		
		//$pos_config['credit_card'];
		
		$con->sql_query("select p.amount_change, pp.* from pos p left join pos_payment pp on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date = pp.date and p.id = pp.pos_id where p.branch_id = ".mi($this->branch_id)." and p.date = ".ms($_REQUEST['date'])." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.cashier_id = ".mi($_REQUEST['cashier_id'])." and p.receipt_no = ".mi($_REQUEST['receipt_no'])." and pp.id = ".mi($_REQUEST['id']));

		$item = $con->sql_fetchrow();

//		print_r($con->sql_fetchrowset());
//		print_r($_REQUEST);
		if (in_array($_REQUEST['type'],$pos_config['credit_card'])) $is_cc = 1;
//		$con->sql_query("select * from pos_payment where branch_id = ".mi($this->branch_id)." and date = ".ms($_REQUEST['date'])." and counter_id = ".mi($_REQUEST['counter_id'])." and cashier_id = ".mi($_REQUEST['cashier_id'])." and receipt_no = ".mi($_REQUEST['receipt_no'])." and pp.id = ".mi($_REQUEST['id']));
		
		if ($item['type'] == 'Cash' && $is_cc && !$item['changed'])
		{
			print round($item['amount']-$item['amount_change'],2);
		}
		else
			print round($item['amount'],2);
				
	}
	
	function save_change_advance()
	{
		global $sessioninfo,$con,$LANG;
		
		$update = 0;
		
		$form = $_REQUEST;
		$form['date'] = $form['date'];
		$form['branch_id'] = $this->branch_id;
		$form['user_id'] = $sessioninfo['id'];
		$form['collected_by'] = $sessioninfo['id'];
		$form['type'] = $_REQUEST['history_type'];
		if(!$form['type'])	$form['type'] = 'ADVANCE';
		$form['timestamp'] = $_REQUEST['e'];
		
		foreach($_REQUEST['amount'] as $n => $amt)
		{
			$id = intval($_REQUEST['id'][$n]);
			
			if ($form['type'] == 'ADVANCE') {
				$amt = mf($amt) * -1; //cash advance must always negative (form data is positive)
			}
			
			$form['amount'] = $amt;
			if ($id>0)
			{
				$con->sql_query("update pos_cash_history set oamount = amount, amount = ".mf($amt)." where id = ".mi($_REQUEST['id'][$n])." and date = ".ms($form['date'])." and branch_id = ".$this->branch_id." and counter_id = ".$_REQUEST['counter_id']);
				$con->sql_query("select * from pos_cash_history where date = ".ms($form['date'])." and branch_id = ".$this->branch_id." and counter_id = ".mi($_REQUEST['counter_id'])." and id = ".$id);
				$r = $con->sql_fetchrow();
				$form['oamount'] = $r['oamount'];

			}
			else
			{
				$form['oamount'] = $amt;
				$form['remark'] = $_REQUEST['remark'][$n];
				
				$con->sql_query("insert into pos_cash_history ".mysql_insert_by_field($form,array('branch_id','counter_id','date','user_id','collected_by','type','amount','oamount','timestamp','remark'))) or die(mysql_error());
				$form['oamount'] = 0;
			}
			if ($con->sql_affectedrows()>0) 
			{
				if ($id <= 0) $id = $con->sql_nextid();
				$msg[] = 'RM '.$form['oamount']." to RM $amt";
				$update++;
			}	
		}
		
		$msg = join(",",$msg);
		$msg .= " (ID: ".$id.", Branch : ".$this->branch_id.", Date: ".$_REQUEST['date'].", Counter ID: ".$_REQUEST['counter_id'].")";
		
		if ($update > 0)
		{
			log_br($sessioninfo['id'], 'Counter Collection', "", "Change Advance $msg");
			header("Location: /counter_collection.php?date_select=".date("Y-m-d",strtotime($form['date']))."&msg=".urlencode($LANG['COUNTER_COLLECTION_UPDATED']));
		}
		else
		header("Location: /counter_collection.php?date_select=".date("Y-m-d",strtotime($form['date']))."&msg=".urlencode($LANG['COUNTER_COLLECTION_NOT_UPDATED']));

		
	}
	
	function save_change_payment()
	{
		global $con,$smarty,$LANG, $sessioninfo;
		
		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");
		
		$form = $_REQUEST;
		$update = 0;
		if ($form['type'])
		{
	

			foreach($form['type'] as $receipt_no => $t)
			{
				
				$items = array();
				$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and p.receipt_no = ".mi($receipt_no)." and changed <> 1 and pt.type <> 'Rounding'");
				
				while ($r = $con->sql_fetchrow())
				{
					$pos_id = mi($r['id']);
					$oitems += $r['payment_amount'];
					
				}
				$all_trans[$pos_id] = 1;

				foreach($t as $idx => $ty)
				{
					$item['type'] = $ty;
					$item['remark'] = $_REQUEST['remark'][$receipt_no][$idx];
					$item['amount'] = $_REQUEST['amount'][$receipt_no][$idx];
					$ind_ty[] = "$ty(".$item['amount'].")";
					
					$con->sql_query("update pos_payment set amount = ".mf($item['amount'])." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($idx)." and pos_id=$pos_id");
					
					$nitem += $item['amount'];
					$items[] = $item;
				}
				$ty_all[] = "#$receipt_no - ".join(', ',$ind_ty);
				$ind_ty = array();
//check is amount more than original amount	
		
				if (floatval($nitem) > floatval($oitems))
				{
					header("Location: /counter_collection.php?a=change_payment_type&cashier_id=".$form['cashier_id']."&counter_id=".$form['counter_id']."&date=".date('Y-m-d',strtotime($form['date']))."&msg=Total amount cannot more than original amount");
					exit;
				}
				$con->sql_query("delete from pos_payment where pos_id = ".mi($pos_id)." and branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and changed = 1 and type <> 'Cancel'");
				$con->sql_query("update pos_payment set adjust = 1 where pos_id = ".mi($pos_id)." and branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and type <> 'Cancel' and type<>'Rounding'");
				foreach ($items as $item)
				{
					$item['branch_id'] = $this->branch_id;
					$item['counter_id'] = $_REQUEST['counter_id'];
					$item['pos_id'] = $pos_id;
					$item['date'] = $_REQUEST['date'];
					$item['changed'] = 1;

					
					$con->sql_query("select max(id) as id from pos_payment where branch_id = ".mi($item['branch_id'])." and counter_id = ".mi($item['counter_id'])." and date = ".ms($item['date'])." group by date");
					$r = $con->sql_fetchrow();
					$item['id'] = $r['id']+1;
					$con->sql_query("insert into pos_payment ".mysql_insert_by_field($item,array('branch_id','counter_id','id','pos_id','date','type','remark','amount','changed')));
					$update++;
				}
				
				$amt = 0;
				
				$con->sql_query("select * from pos_payment where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and pos_id = ".mi($pos_id)." and adjust = 1 and type <> 'Rounding'");
				
				while($r = $con->sql_fetchrow())
				{
					$amt += $r['amount'];
				}
				$con->sql_query("select * from pos where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($pos_id));
				$pos_header = $con->sql_fetchrow();
				
				$con->sql_query("update pos set amount_tender = ".mf($amt).", amount_change = ".floatval($amt-$pos_header['amount'])." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($pos_id));
			}
			
		}
		$all_trans = join(",", array_keys($all_trans));
		$all_trans .= ', Branch ID: '.$this->branch_id.', Date: '.$_REQUEST['date'].', Counter ID: '.$_REQUEST['counter_id'].', Change To: '.join(', ',$ty_all);
		log_br($sessioninfo['id'], 'Counter Collection', "", "Change Payment Type (Pos ID: $all_trans)");
		
		if ($update > 0)
		header("Location: /counter_collection.php?date_select=".date("Y-m-d",strtotime($form['date']))."&msg=".urlencode($LANG['COUNTER_COLLECTION_UPDATED']));
		else
		header("Location: /counter_collection.php?date_select=".date("Y-m-d",strtotime($form['date']))."&msg=".urlencode($LANG['COUNTER_COLLECTION_NOT_UPDATED']));
 	}
 	
 	function change_advance()
 	{
		global $con, $smarty, $config, $LANG, $pos_config;
		
		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");
		
		$type = trim($_REQUEST['type']);
		if(!$type)	$type = 'ADVANCE';

		$filter = array();
		$filter[] = "p.branch_id=$this->branch_id";
		$filter[] = "p.counter_id=".mi($_REQUEST['counter_id']);
		$filter[] = "p.date=".ms($_REQUEST['date']);
		$filter[] = "p.timestamp between ".ms($_REQUEST['s'])." and ".ms($_REQUEST['e']);
		$filter[] = "p.type=".ms($type);
		$filter = "where ".join(' and ', $filter);
		
		$sql = "select p.*, u1.u as cashier, u2.u as collected_by 
				from pos_cash_history p 
				left join user u1 on u1.id = p.user_id
				left join user u2 on u2.id = p.collected_by
				$filter";
		//print $sql;
		$q1 = $con->sql_query($sql);
		$items = array();
		while($r = $con->sql_fetchassoc()){
			$items[] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign('history_type', $type);
		$smarty->assign("items", $items);
		$smarty->display('counter_collection.change_advance.tpl');
	}
 	
	function change_payment_type()
	{	
		global $con,$smarty, $config,$LANG, $pos_config;
		
		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");
	
		foreach($pos_config['issuer_identifier'] as $ii)
		{
			$cc[$ii[0]] = 1;
		}
	
		$cash_credit = array_keys($cc);
		$cash_credit[] = 'Cash';
		$cash_credit[] = 'Check';
		$cash_credit[] = 'Others';
		
		$coupon_voucher[] = 'Coupon';
		$coupon_voucher[] = 'Voucher';
		
		$rs = $con->sql_query("select p.receipt_no from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and pt.adjust = 1  and p.pos_time >= ".ms($_REQUEST['s'])." and p.pos_time <= ".ms($_REQUEST['e'])." group by receipt_no");
		while ($r = $con->sql_fetchrow($rs))
		{
			$receipt_no[] = $r['receipt_no'];
		}
		
		if ($receipt_no)
		{
			foreach($receipt_no as $rno)
			{
				$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and p.receipt_no = ".mi($rno)." and changed = 1");
		
				while($r = $con->sql_fetchrow())
				{
					$items[$r['receipt_no']][] = $r;
				}
				$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and p.receipt_no = ".mi($rno)." and adjust = 1");
		
				while($r = $con->sql_fetchrow())
				{
					$oitems[$r['receipt_no']][] = $r;
				}
			}
		}
		
		$smarty->assign('PAGE_TITLE','Change Payment Type');
		$smarty->assign("all_items",$items);
		$smarty->assign("oitems",$oitems);
		
		foreach ($pos_config['payment_type'] as $pt)
		{
			if ($pt != 'Credit Cards')
			$payment_type[] = $pt;
		}
		
		$ptcheck = array_keys($cc);
		$ptcheck[] = 'Others';
		$ptcheck[] = 'Coupon';
		$ptcheck[] = 'Voucher';
		
		$payment_type = array_merge($payment_type,array_keys($cc));
		$payment_type[] = 'Others';

		$smarty->assign("credit_cards", $ptcheck);
		$smarty->assign("coupon_voucher",$coupon_voucher);
		$smarty->assign("cc", $cash_credit);
		$smarty->assign("payment_type",$payment_type);
		
		$smarty->display('counter_collection.change_payment.tpl');
	}
	
	function ajax_add_coupon()
	{
		global $con, $smarty, $pos_config,$LANG;
	
		foreach ($pos_config['payment_type'] as $pt)
		{
			if ($pt != 'Credit Cards')
			$payment_type[] = $pt;
		}
		
		foreach($pos_config['issuer_identifier'] as $ii)
		{
			$cc[$ii[0]] = 1;
		}
		$ptcheck = array_keys($cc);
		$ptcheck[] = 'Others';
		$ptcheck[] = 'Coupon';
		$ptcheck[] = 'Voucher';
		$payment_type = array_merge($payment_type,array_keys($cc));
		
		$payment_type[] = 'Others';
		$cash_credit = array_keys($cc);
		$cash_credit[] = 'Cash';
		
		$coupon_voucher[] = 'Coupon';
		$coupon_voucher[] = 'Voucher';
		
		$con->sql_query("select count(pt.adjust) as count from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where pt.type <> 'Cancel' and p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cashier_id = ".mi($_REQUEST['cashier_id'])." and pt.adjust = 1 group by adjust");
		$count = $con->sql_fetchrow();
		
		$smarty->assign('i',$count['count']+1);
		$item['receipt_no'] = $_REQUEST['receipt_no'];
		$item['type'] = 'Coupon';
		$smarty->assign("item", $item);
		$smarty->assign("coupon_voucher",$coupon_voucher);
		$smarty->assign("cc", $cash_credit);
		$smarty->assign("payment_type",$payment_type);
		$smarty->assign("credit_cards", $ptcheck);
		
		print $smarty->fetch('counter_collection.change_payment.row2.tpl');

	}
	
	function ajax_add_receipt_row()
	{
		global $con, $smarty, $pos_config,$LANG;

		foreach ($pos_config['payment_type'] as $pt)
		{
			if ($pt != 'Credit Cards')
			$payment_type[] = $pt;
		}
		
		foreach($pos_config['issuer_identifier'] as $ii)
		{
			$cc[$ii[0]] = 1;
		}
		$ptcheck = array_keys($cc);
		$ptcheck[] = 'Others';
		$ptcheck[] = 'Coupon';
		$ptcheck[] = 'Voucher';
		$payment_type = array_merge($payment_type,array_keys($cc));
		
		$payment_type[] = 'Others';
		$cash_credit = array_keys($cc);
		$cash_credit[] = 'Cash';
		$cash_credit[] = 'Check';
		$cash_credit[] = 'Others';
		
		$coupon_voucher[] = 'Coupon';
		$coupon_voucher[] = 'Voucher';
		
		$smarty->assign("coupon_voucher",$coupon_voucher);
		$smarty->assign("cc", $cash_credit);
		$smarty->assign("payment_type",$payment_type);
		$smarty->assign("credit_cards", $ptcheck);
		
		$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where pt.type <> 'Cancel' and pt.type <> 'Rounding' and p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.receipt_no = ".mi($_REQUEST['receipt_no'])." and pt.adjust = 0 and p.pos_time >= ".ms($_REQUEST['s'])." and p.pos_time <= ".ms($_REQUEST['e'])." order by p.pos_time");
		if ($con->sql_numrows()>0)
		{
			while($item = $con->sql_fetchrow())
			{
				if ($item['cancel_status'] == 0)
				{
					$items[] = $item;
					$oitems[$item['receipt_no']][]=$item;
				}
			}
			$smarty->assign("receipt_no",$_REQUEST['receipt_no']);
			$smarty->assign("items",$items);
			$smarty->assign("oitems",$oitems);
			print $smarty->fetch('counter_collection.change_payment.row.tpl');
/*			else
			{
				print $LANG['COUNTER_COLLECTION_RECEIPT_CANCELLED'];
			}
*/		}
		else
		print $LANG['COUNTER_COLLECTION_INVAILD_RECEIPT'];
	}
	
	function finalize()
	{
		global $con, $sessioninfo, $smarty, $LANG, $config;
		
		// check approval
 		if (!$this->is_approval){
			if($this->is_ajax_finalize){
				die(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE));
			}else{
				js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");	
			} 
		} 

		$form = $_REQUEST;

		//clear drawer/close counter checking
   		$con->sql_query("select counter_id from pos where branch_id=".mi($this->branch_id)." and cancel_status= 0 and date=".ms($form['date_select'])." group by counter_id");
		while($r1= $con->sql_fetchrow()){
		    $counter[$r1['counter_id']] = 1;
		}
   		$con->sql_query("select counter_id from pos_cash_history where branch_id=".mi($this->branch_id)." and date=".ms($form['date_select'])." group by counter_id");
		while($r1= $con->sql_fetchrow()){
		    $counter[$r1['counter_id']] = 1;
		}

		$no_clear_drawer=0;
		foreach ($counter as $cid => $dummy)
		{
		$rs=$con->sql_query("select * from pos_cash_domination where branch_id=".mi($this->branch_id)." and date=".ms($form['date_select'])." and counter_id=".mi($cid)." and clear_drawer=1");
			if ($con->sql_numrows($rs)==0)
			{
    			$con->sql_query("select network_name from counter_settings where id=$cid and branch_id=".mi($this->branch_id));
    			$r=$con->sql_fetchrow();
    			$arr[]=$r['network_name'];
				//$no_clear_drawer++;

			}
		}
		
		if($no_clear_drawer>0)
		{
			if($this->is_ajax_finalize){
				die("Counter: ".implode(" , ", $arr)." ".$LANG['COUNTER_COLLECTION_NO_CASH_DOMINATION']);
			}else{
				header("Location: /counter_collection.php?date_select=".$form['date_select']."&msg=Counter: ".implode(" , ", $arr)." ".urlencode($LANG['COUNTER_COLLECTION_NO_CASH_DOMINATION']));	
			}
			exit;
		}
		//
		
		// insert finalized
		$con->sql_query("delete from pos_counter_collection_tracking where branch_id=$this->branch_id and date=".ms($form['date_select']));
		$rs = $con->sql_query("select branch_id, date, counter_id from pos where branch_id = ".mi($this->branch_id)." and date = ".ms($form['date_select'])." group by counter_id");
		while ($r = $con->sql_fetchrow($rs))
		{
			$r['finalized'] = 1;
			$con->sql_query("insert into pos_counter_collection_tracking ".mysql_insert_by_field($r,array('branch_id','counter_id','date','finalized')));
		}
		$con->sql_query("replace into pos_finalized ".mysql_insert_by_field(array('branch_id'=>$this->branch_id, 'date'=>$form['date_select'],'finalized'=>1,'finalize_timestamp'=>'CURRENT_TIMESTAMP')));

		$params = array();
		if($this->is_ajax_finalize)$params['write_process_status'] = 1;
		
        // generate pos_cashier_finalize
        foreach($counter as $cid=>$dummy){
            generate_pos_cashier_finalize($date, $cid, $this->branch_id);
        }
        
        $date = $form['date_select'];
        update_sales_cache($this->branch_id, $date, '', '' , $params);
        
		$this->send_notification($form['date_select']);

		// create flag file for cron csa report
		if ($config['csa_generate_report']){
			if (!is_dir("csa_report_cache")) mkdir("csa_report_cache",0777); //check folder
			list($year,$month,$day)=explode("-", $form['date_select']);

			$check_file="csa_report_cache/".BRANCH_CODE.".$year.".str_pad($month, 2, "0", STR_PAD_LEFT).".csa_cache";
			file_put_contents($check_file,"r");
			@chmod($check_file,0777);
		}

		log_br($sessioninfo['id'], 'Counter Collection', "", "Finalise Collection (Branch : ".BRANCH_CODE.", Date: $form[date_select])");

		if(!$this->is_ajax_finalize){
			header("Location: /counter_collection.php?date_select=".$form['date_select']."&finalize=1&msg=".urlencode($LANG['COUNTER_COLLECTION_FINALIZED']));
		}
/*
		print "<pre>";
		print_r($_REQUEST);
		print "</pre>";
*/		

	}

	function unfinalize()
	{
		global $con, $sessioninfo, $smarty, $LANG, $config;
		
		if (!privilege('CC_UNFINALIZE')&&$sessioninfo['level']<9999) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CC_UNFINALIZE', BRANCH_CODE), "/index.php");
		
		$form = $_REQUEST;
		
		// delete finalized
		$con->sql_query("delete from pos_counter_collection_tracking where branch_id = ".mi($this->branch_id)." and date = ".ms($form['date_select']));
		//$con->sql_query("delete from pos_finalized where branch_id = ".mi($this->branch_id)." and date = ".ms($form['date_select']));
		$upd = array();
		$upd['branch_id'] = $this->branch_id;
		$upd['date'] = $form['date_select'];
		$upd['finalized'] = 0;
		$upd['finalize_timestamp'] = 0;
		$con->sql_query("replace into pos_finalized ".mysql_insert_by_field($upd));
		
		log_br($sessioninfo['id'], 'Counter Collection', "", "Un-Finalise Collection (Branch : ".BRANCH_CODE.", Date: $form[date_select])");
		
		// delete cache
		$date = $form['date_select'];
		// update sku items cost changed
		$con->sql_query("select distinct(sku_item_id) from sku_items_sales_cache_b1 where date=".ms($date));
		$sid_list = array();
		while($r = $con->sql_fetchrow()){
			$sid_list[] = mi($r[0]);
			if(count($sid_list)>1000){
				$con->sql_query("update sku_items_cost set changed=1 where branch_id=".mi($this->branch_id)." and sku_item_id in (".join(',',$sid_list).")");
                $sid_list = array();
			}
		}
		$con->sql_freeresult();
		if($sid_list){
			$con->sql_query("update sku_items_cost set changed=1 where branch_id=".mi($this->branch_id)." and sku_item_id in (".join(',',$sid_list).")");
            $sid_list = array();
		}
		$con->sql_query("delete from sku_items_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
	    $con->sql_query("delete from category_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
	    $con->sql_query("delete from member_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
	    $con->sql_query("delete from pwp_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
	  	$con->sql_query("delete from dept_trans_cache_b".mi($this->branch_id)." where date=".ms($date));
		$con->sql_query("delete from pos_counter_finalize where branch_id=".mi($this->branch_id)." and date=".ms($date));
        $con->sql_query("delete from pos_cashier_finalize where branch_id=".mi($this->branch_id)." and date=".ms($date));
		
		// create flag file for cron csa report
		if ($config['csa_generate_report']){
			if (!is_dir("csa_report_cache")) mkdir("csa_report_cache",0777); //check folder
			list($year,$month,$day)=explode("-", $form['date_select']);

			$check_file="csa_report_cache/".BRANCH_CODE.".$year.".str_pad($month, 2, "0", STR_PAD_LEFT).".csa_cache";
			file_put_contents($check_file,"r");
			chmod($check_file,0777);
		}
		
		header("Location: /counter_collection.php?date_select=".$form['date_select']."&unfinalize=1&msg=".urlencode($LANG['COUNTER_COLLECTION_UNFINALIZED']));
/*		
		print "<pre>";
		print_r($_REQUEST);
		print "</pre>";
*/		

	}
	
	function send_notification($date){
		global $con;
		
		$notify_users = array();
		foreach (preg_split("/\|/", $this->notify_users) as $kk)
		{
		    if ($kk) $notify_users[] = $kk;
		}
		
		foreach ($notify_users as $user)
		{
			send_pm($user, "Counter Collection Finalised for Branch:".BRANCH_CODE." Date: $date", "/counter_collection.php?a=view&date_select=$date&branch_id=".$this->branch_id);
		}
		
	}	
	
	function check_finalized($date)
	{
		global $con;
		//$con->sql_query("select count(*) as count from pos_counter_collection_tracking where branch_id = ".mi($this->branch_id)." and date = ".ms(dmy_to_sqldate($date))." and finalized = 1 group by date");
		$con->sql_query("select * from pos_finalized where branch_id=".mi($this->branch_id)." and date=".ms($date)." and finalized=1");
		
		if ($con->sql_numrows()>0)
			return true;
		else
			return false;
	}
	
	function view()
	{
		$branch_id = intval($_REQUEST['branch_id']);
	
		$this->list_data($_REQUEST['date_select'],1,$branch_id);
		
		$this->display();
	}
	
	function save_cancel_receipt()
	{
		global $con, $smarty, $LANG, $sessioninfo;
		
		$con->sql_query("select * from pos where branch_id = ".$this->branch_id." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and receipt_no = ".mi($_REQUEST['receipt_no']));
		if ($con->sql_numrows()>0)
		{
			$con->sql_query("update pos set cancel_status = !cancel_status where branch_id = ".$this->branch_id." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and receipt_no = ".mi($_REQUEST['receipt_no']));
			if ($_REQUEST['cancel_status'] == 1)
				$msg = $LANG['COUNTER_COLLECTION_RECEIPT_UNCANCELLED'];
			else
				$msg = $LANG['COUNTER_COLLECTION_RECEIPT_CANCELLED'];
			log_br($sessioninfo['id'], 'Counter Collection', $_REQUEST['receipt_no'], "$msg (Date: $_REQUEST[date], Counter ID: $_REQUEST[counter_id], Receipt No: $_REQUEST[receipt_no])");
			header("Location: /counter_collection.php?a=cancel_receipt&counter_id=$_REQUEST[counter_id]&date=$_REQUEST[date]&msg=".urlencode($msg));
			exit;		
		}
		
		header("Location: /counter_collection.php?a=cancel_receipt&counter_id=$_REQUEST[counter_id]&date=$_REQUEST[date]&msg=".urlencode($LANG['COUNTER_COLLECTION_RECEIPT_NOT_CANCELLED']));
	}
	
	function ajax_cancel_receipt()
	{
		global $con, $smarty, $LANG, $sessioninfo;
		
		/*$con->sql_query("select * from pos_counter_collection_tracking where branch_id = ".mi($this->branch_id)." and date = ".ms(dmy_to_sqldate($_REQUEST['date']))." and finalized = 1 ");
		if ($con->sql_numrows()>0)
		{
			print "Error: ".$LANG['COUNTER_COLLECTION_FINALIZED_CANNOT_UPDATE'];
			exit;		
		}*/
		if($this->check_finalized($_REQUEST['date'])){
            print "Error: ".$LANG['COUNTER_COLLECTION_FINALIZED_CANNOT_UPDATE'];
			exit;
		}
		
		$con->sql_query("update pos set cancel_status = ".mb($_REQUEST['v'])." where branch_id = ".mi($this->branch_id)." and date = ".ms($_REQUEST['date'])."  and counter_id = ".mi($_REQUEST['counter_id'])." and id = ".mi($_REQUEST['pos_id']));
		
		if ($con->sql_affectedrows()>0)
		{
			$receipt_no = get_pos_receipt_no($this->branch_id, $_REQUEST['date'], $_REQUEST['counter_id'], $_REQUEST['pos_id']);
			
			log_br($sessioninfo['id'], 'Counter Collection '.($_REQUEST['v'] ? 'Cancel' : 'Active').' Receipt', $_REQUEST['receipt_no'], "$msg (Date: $_REQUEST[date], Counter ID: $_REQUEST[counter_id], Receipt No: $receipt_no)");
			
			if ($_REQUEST['v'] == 1)
			{
				$mp_sqls = "update membership_promotion_items set cancelled = 1 where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and pos_id = ".mi($_REQUEST['pos_id']);
				$con->sql_query($mp_sqls);
				
				print $LANG['COUNTER_COLLECTION_RECEIPT_CANCELLED'];
			}
			else
			{
				$mp_sqls = "update membership_promotion_items set cancelled = 0 where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and pos_id = ".mi($_REQUEST['pos_id']);
				$con->sql_query($mp_sqls);
				
				print $LANG['COUNTER_COLLECTION_RECEIPT_UNCANCELLED'];
			}
			
		}
		else
		{
			print $LANG['COUNTER_COLLECTION_RECEIPT_NO_CHANGES'];
		}
	}
	
	function cancel_receipt()
	{
		global $con, $smarty, $config, $LANG;

		if($config['counter_collection_need_privilege_cancel_bill'] && !privilege('CC_CANCEL_BILL')){
			js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CC_CANCEL_BILL', BRANCH_CODE), "/counter_collection.php");
		}
		
		if ($_REQUEST['fsubmit'])
		{
			$con->sql_query("select * from pos where branch_id = ".mi($this->branch_id)." and date = ".ms($_REQUEST['date'])." and counter_id = ".mi($_REQUEST['counter_id'])." and receipt_no = ".mi($_REQUEST['receipt_no']));
			while ($r = $con->sql_fetchrow())
			{
				$items[] = $r;
			}
			$smarty->assign("items",$items);
		}
		$this->display('counter_collection.cancel_receipt.tpl');
	}
	
	function change_x()
	{
		global $con,$smarty;
		
		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");

		$con->sql_query("select * from pos_cash_domination where date = ".ms($_REQUEST['date'])." and branch_id =".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and id = ".mi($_REQUEST['id']));

		$item = $con->sql_fetchrow();
		$item['data'] = unserialize($item['data']);
		$item['odata'] = unserialize($item['odata']);
	
		if (isset($item['data']['Cheque'])) 
		{
			$item['data']['Check'] = $item['data']['Cheque'];
			unset($item['data']['Cheque']);
		}
		
		if (isset($item['odata']['Cheque'])) 
		{
			$item['odata']['Check'] = $item['odata']['Cheque'];
			unset($item['odata']['Cheque']);
		}
		
		$smarty->assign('PAGE_TITLE','Counter Collection Change X-Figure');
		$smarty->assign('item',$item);
		$smarty->display('counter_collection.change_x.tpl');
		
	}
	
	function save_cash_domination()
	{
		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");

		global $con, $sessioninfo, $config;
		
		$changes = $_REQUEST['data'];
		$_REQUEST['data'] = serialize($_REQUEST['data']);
		$date_time = strtotime($_REQUEST['date']);
		
		$str_changes = array();
		foreach ($changes as $key => $value) {
			if (!empty($value)) $str_changes[] = "$key($value)";
		}
		
		if (intval($_REQUEST['id'])>0)
		{
			$con->sql_query("select * from pos_cash_domination where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($_REQUEST['id']));
			
			if ($con->sql_numrows()>0)
			{
				$r = $con->sql_fetchrow();
				if ($r['odata'] == '') $con->sql_query("update pos_cash_domination set odata = ".ms($r['data'])." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and user_id = ".mi($_REQUEST['cashier_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($_REQUEST['id']));
				$con->sql_query("update pos_cash_domination set data = ".ms($_REQUEST['data']).", clear_drawer = ".mi($_REQUEST['clear_drawer'])." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($_REQUEST['id']));
				log_br($sessioninfo['id'], 'Counter Collection', mi($_REQUEST['id']), "Change Cash Denomination (ID: ".mi($_REQUEST['id']).",Branch ID: ".$this->branch_id.", Counter ID: ".$_REQUEST['counter_id'].", Date: ".$_REQUEST['date'].", Data: ".join(', ',$str_changes).", Total: ".number_format($_REQUEST['total_amount'],2).")");
				header("Location: /counter_collection.php?date_select=".$_REQUEST['date']."&msg=".urlencode("X Updated"));
			}
			else
			header("Location: /counter_collection.php?date_select=".$_REQUEST['date']."&msg=".urlencode("Invalid X"));
		}
		else
		{
		    $counter_version = get_counter_version($this->branch_id, $_REQUEST['counter_id']);
		    
			$con->sql_query("select  max(id) as id from pos_cash_domination where branch_id = ".$this->branch_id." and date = ".ms($_REQUEST['date'])." and counter_id = ".mi($_REQUEST['counter_id']));
			$r = $con->sql_fetchrow();
			$id = $r['id']+1;
			
			if($config['counter_collection_check_old_cash_domination_time'] && ($counter_version<109 || $date_time < $this->v109_time)){
                $timestamp = date("Y-m-d H:i:s",1+strtotime($_REQUEST['e'])-(8*60*60));
			}else{
                $timestamp = date("Y-m-d H:i:s",1+strtotime($_REQUEST['e']));
			}
			
			$con->sql_query("insert into pos_cash_domination (branch_id,id,counter_id,user_id,data,timestamp,date,clear_drawer,is_from_backend) values (".$this->branch_id.",".mi($id).",".mi($_REQUEST['counter_id']).",".mi($_REQUEST['cashier_id']).",".ms($_REQUEST['data']).",".ms($timestamp).",".ms($_REQUEST['date']).",".mi($_REQUEST['clear_drawer']).",1)");
			header("Location: /counter_collection.php?date_select=".$_REQUEST['date']."&msg=".urlencode("X Updated"));
		}
	}
	
	function delete_cash_domination(){
        global $con;
		$con->sql_query("delete from pos_cash_domination where is_from_backend = 1 and branch_id = ".$this->branch_id." and id = ".mi($_REQUEST['id'])." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." limit 1");
		header("Location: /counter_collection.php?date_select=".$_REQUEST['date']."&msg=".urlencode("Record deleted"));
		
	}

	function list_data($date,$is_view = 0,$branch_id = 0)
	{
		global $con, $smarty,$config, $pos_config;
		//print_r($pos_config);
		if (!$is_view)	$branch_id = $this->branch_id;
		
		if ($this->check_finalized($date))
		{
			$smarty->assign("is_finalized",1);
		}
		
		
		if ($date)
			$sql = "p.date = ".ms($date)." and p.branch_id = ".mi($branch_id);
		else
			die("No date, please contact system admin");
			
        $date_time = strtotime($date);
        
		$con->sql_query("select distinct counter_id, cs.network_name from pos p left join counter_settings cs on p.counter_id = cs.id and p.branch_id = cs.branch_id where cancel_status = 0 and $sql");
		while($r = $con->sql_fetchrow())
		{
			$counters[$r['counter_id']] = $r['network_name']; 
		}
		
		$con->sql_query("select distinct counter_id, cs.network_name from pos_cash_domination p left join counter_settings cs on p.counter_id = cs.id and p.branch_id = cs.branch_id where $sql");
		while($r = $con->sql_fetchrow())
		{
			$counters[$r['counter_id']] = $r['network_name']; 
		}
		if (isset($counters)) asort($counters);
		
//		$counters = array_keys($counters);
		if (isset($counters))
		{
			foreach($counters as $counter_id => $name)
			{	
			    $counter_version = get_counter_version($this->branch_id, $counter_id);
			    
				$con->sql_query("select min(p.pos_time) as s , max(p.pos_time) as e from pos p where p.branch_id = ".$this->branch_id." and p.date = ".ms($date)." and p.counter_id = ".mi($counter_id)) or die(mysql_error());
				$r = $con->sql_fetchrow();
	
				$start = $r['s'];
				$end = $r['e'];


				$con->sql_query("select * from pos_cash_history where branch_id = ".mi($this->branch_id)." and date = ".ms($date)." and counter_id = ".mi($counter_id)." order by timestamp") or die(mysql_error());
				$ch = $con->sql_fetchrowset();
				
				if ($ch)
				{
					foreach($ch as $r)
					{
						if ($r['timestamp'] < $start)
						$start = date("Y-m-d H:i:s",strtotime($r['timestamp'])-1);
						if ($r['timestamp'] > $end)
						$end = date("Y-m-d H:i:s",strtotime($r['timestamp'])+1);
					}
				}
				

				//$con->sql_query("select * from pos_cash_domination p where $sql and clear_drawer = 1 and counter_id = ".mi($counter_id));
				$con->sql_query("select * from pos_cash_domination p where $sql and counter_id = ".mi($counter_id));
				
				// if no clear drawer record, use the last X
				if ($con->sql_numrows()<=0)
				{
					$con->sql_query("select * from pos_cash_domination p where $sql and counter_id = ".mi($counter_id)." order by timestamp desc limit 1");
				}
				
				if ($con->sql_numrows()>0) 
				{
					$dom = $con->sql_fetchrowset();
					$dom_count = 0;
						
					foreach($dom as $d)
					{	
					    if($config['counter_collection_check_old_cash_domination_time'] && ($counter_version<109 || $date_time < $this->v109_time || ($counter_version>=109 && !is_latest_than_last_pos($this->branch_id, $counter_id, $date)))){
							$d['timestamp'] = date("Y-m-d H:i:s",strtotime("+8 hour", strtotime($d['timestamp'])));
						}
						$cdtimestamp[$counter_id][$d['id']]['timestamp'] = $d['timestamp'];
						$this->generate_data($start, $d['timestamp'], $counter_id, $date, $d);
						$start = $d['timestamp'];
						$this->cd_info[$d['counter_id']][$d['id']] = $d;
					}
				}

				$this->generate_data($start, $end, $counter_id, $date);
			}
		}
		
		foreach ($pos_config['payment_type'] as $type)
		{
			$this->counter_sales[$type] += $this->type_adjustment[$type];	
		}
		
		$con->sql_query("select sum(pi.price-discount) from pos p left join pos_items pi on p.branch_id = pi.branch_id and p.date = pi.date and p.counter_id = pi.counter_id and p.id = pi.pos_id where $sql and p.cancel_status = 0 ");
		$this->grandtotal['total_sales'] = $con->sql_fetchfield(0);
		
		$con->sql_query("select sum(p.amount_tender-amount_change) from pos p where $sql and p.cancel_status = 0 ");
		$this->grandtotal['total_actual_sales'] = $con->sql_fetchfield(0);

		if($this->data){
			check_and_pregen_pos_finalized($branch_id, $date);
		}
/*
print "<pre>";
//print_r($this->data);
print_r($this->counter_data);
print "</pre>";
*/
		
		//print_r($this->cash_domination);

		$smarty->assign('trans', $this->trans);
		$smarty->assign('over', $this->over);
		$smarty->assign('cd_info', $this->cd_info);
		$smarty->assign('variance', $this->variance);
		$smarty->assign("clear_drawer", $this->clear_drawer);
		$smarty->assign("deposit_data",$this->deposit_data);
		$smarty->assign("data",$this->data);
		
		$smarty->assign("counter_sales",$this->counter_sales);
		$smarty->assign("counter_data",$this->counter_data);
		$smarty->assign("odata",$this->odata);
		$smarty->assign("cash_advance",$this->cash_advance);
		$smarty->assign("top_up_data",$this->top_up_data);
		$smarty->assign("cash_domination",$this->cash_domination);
		$smarty->assign("counters",$counters);
		$smarty->assign("adjustment",$this->adjustment);
		$smarty->assign("grandtotal",$this->grandtotal);
		$smarty->assign("xtra",$this->xtra);
		$smarty->assign('got_mm_discount', $this->got_mm_discount);
		$smarty->assign('got_top_up', $this->got_top_up);
		$smarty->assign('trade_in_data', $this->trade_in_data);
		
		unset($this->trans, $this->over, $this->cd_info, $this->variance, $this->clear_drawer,  $this->data, $this->counter_sales, $this->counter_data, $this->odata, $this->cash_advance, $counters, $this->adjustment, $this->grandtotal, $this->xtra, $this->got_mm_discount, $this->type_adjustment);
		if ($_REQUEST['finalize'] == 1){
			$this->recalculate_data();
			store_counter_collection_data($branch_id,$date,$this->store_data);
		}
		unset($this->store_data);

	}
	
	function generate_data($s,$e,$counter_id, $date, $d = '')
	{
		global $con, $pos_config, $mm_discount_col_value;
		
		if ($d)
			$cd_id = $d['id'];
		else
		{
			$d['user_id'] = 0;
			$cd_id = 'no_cd';
		}	
	//	if ($s > $e) return;
		
		preg_match('/\d+-\d+-\d+/',$date,$dt);
		
		$date = $dt[0];
		$d['data'] = unserialize($d['data']);
		$d['odata'] = unserialize($d['odata']);	
		
		$con->sql_query("select p.pos_time, p.date, p.counter_id, p.cashier_id, type, round(if(type='Cash',pp.amount-p.amount_change,pp.amount),2) as amount from pos p left join pos_payment pp on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date = pp.date and p.id = pp.pos_id where p.branch_id = ".mi($this->branch_id)." and p.date = ".ms($date)." and p.counter_id = ".mi($counter_id)." and pp.changed <> 1 and p.pos_time >= ".ms($s)." and p.pos_time <= ".ms($e)." order by p.pos_time") or die(mysql_error());
		if($con->sql_numrows()>0)
		{
			while($r = $con->sql_fetchrow())
			{
				if (in_array($r['type'],$pos_config['credit_card'])) $r['type'] = 'Credit Cards';
				if (!isset($this->trans[$counter_id][$cd_id][$r['cashier_id']]['start_time'])) $this->trans[$counter_id][$cd_id][$r['cashier_id']]['start_time'] = $r['pos_time'];
				$this->trans[$counter_id][$cd_id][$r['cashier_id']]['end_time'] = $r['pos_time'];
				
				if(!isset($this->counter_data[$counter_id][$cd_id]['start_time'])) $this->counter_data[$counter_id][$cd_id]['start_time'] = $r['pos_time'];
				$this->counter_data[$counter_id][$cd_id]['end_time'] = $r['pos_time'];
			}
		}
		$con->sql_freeresult();
	
		$con->sql_query("select p.pos_time, p.date, p.counter_id, p.cashier_id, pp.type, round(if(type='Cash',pp.amount-p.amount_change,pp.amount),2) as amount from pos p left join pos_payment pp on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date = pp.date and p.id = pp.pos_id where p.branch_id = ".mi($this->branch_id)." and p.date = ".ms($date)." and p.counter_id = ".mi($counter_id)." and p.cancel_status = 0 and pp.changed <> 1 and p.pos_time >= ".ms($s)." and p.pos_time <= ".ms($e)." order by p.pos_time") or die(mysql_error());
//		print "CD Id: $cd_id, Start Time: $s, End Time: $e<br>";
		if($con->sql_numrows()>0)
		{
			$uid = 0;
			while($r = $con->sql_fetchrow())
			{
				if (in_array($r['type'],$pos_config['credit_card'])) $r['type'] = 'Credit Cards';
				$this->data[$r['counter_id']][$cd_id][$r['cashier_id']][$r['type']] += round($r['amount'],2);
				$this->counter_data[$r['counter_id']][$cd_id][$r['type']]['sales'] += round($r['amount'],2);
				
				if($r['type']==$mm_discount_col_value){
					$this->cash_domination[$r['counter_id']][$cd_id][$r['cashier_id']][$mm_discount_col_value] += round($r['amount'],2);;
					$this->counter_data[$r['counter_id']][$cd_id][$mm_discount_col_value]['cash_domination'] += round($r['amount'],2);
					$this->grandtotal['collection'] += round($r['amount'],2);

				}
								
				if ($_REQUEST['finalize'] == 1){
					if ($r['type'] == 'Discount' || $r['type'] == $mm_discount_col_value)	$amount=$r['amount']*-1;
					else	$amount=$r['amount'];
					$this->store_data[$r['counter_id']]['cashier_sales'][$r['type']]['amt'] += round($amount,2);
				}
				$this->counter_sales[$r['type']] += $r['amount'];

				if ($r['type'] == 'Discount') $this->grandtotal['discount'] = $r['amount'];
				
				// got mix and match discount
				if ($r['type'] == $mm_discount_col_value){
					$this->got_mm_discount = true;
					$this->grandtotal[$mm_discount_col_value] = $r['amount'];
				}
				
				$this->grandtotal['sales'] += $r['amount'];
				if ($r['type'] == 'Rounding')$this->grandtotal['rounding'] += $r['amount'];
				if (!isset($this->trans[$counter_id][$cd_id][$r['cashier_id']]['start_time'])) $this->trans[$counter_id][$cd_id][$r['cashier_id']]['start_time'] = $r['pos_time'];
				$this->trans[$counter_id][$cd_id][$r['cashier_id']]['end_time'] = $r['pos_time'];
				
				if(!isset($this->counter_data[$counter_id][$cd_id]['start_time'])) $this->counter_data[$counter_id][$cd_id]['start_time'] = $r['pos_time'];
				$this->counter_data[$counter_id][$cd_id]['end_time'] = $r['pos_time'];
				
				$ccid = $r['cashier_id'];
				
				unset($amount);
			}
		}
		else
		{
			if ($d['id'] > 0) $this->data[$counter_id][$cd_id][$d['user_id']] = 0;
		}
		$con->sql_freeresult();
		
		$q_p = $con->sql_query("select p.*, round(p.amount_tender-p.amount_change,2)-round(p.amount,2) as over from pos p where p.counter_id = ".mi($counter_id)." and p.branch_id = ".mi($this->branch_id)." and p.date = ".ms($date)." and p.pos_time >= ".ms($s)." and p.pos_time <= ".ms($e)." and p.cancel_status=0") or die(mysql_error());
		while($r = $con->sql_fetchrow($q_p))
		{
			// check over
			if(round($r['amount_tender']-$r['amount_change'],2) != round($r['amount'],2)){
				$this->counter_data[$r['counter_id']][$cd_id]['over']['amt'] += round($r['over'],2);
				if ($_REQUEST['finalize'] == 1)	$this->store_data[$r['counter_id']]['cashier_sales']['Over']['amt']+= round($r['over'],2);
				$this->over[$r['counter_id']][$cd_id][$r['cashier_id']] += round($r['over'],2);
				$this->grandtotal['over'] += $r['over'];
			}
			
			// check trade in items
			$q_ti = $con->sql_query("select pi.*
			from pos_items pi
			where pi.branch_id=$this->branch_id and pi.date=".ms($date)." and pi.counter_id=$counter_id and pi.pos_id=$r[id] and pi.trade_in_by>0");

			while($pi_ti = $con->sql_fetchassoc($q_ti)){
				$this->counter_data[$r['counter_id']][$cd_id]['trade_in']['qty'] += $pi_ti['qty'];
				$this->counter_data[$r['counter_id']][$cd_id]['trade_in']['amt'] += $pi_ti['price'];
				
				$this->trade_in_data['qty'] += $pi_ti['qty'];
				$this->trade_in_data['amt'] += $pi_ti['price'];
				
				if($pi_ti['writeoff_by']){
					$this->counter_data[$r['counter_id']][$cd_id]['trade_in']['writeoff_qty']+=$pi_ti['qty'];
					$this->counter_data[$r['counter_id']][$cd_id]['trade_in']['writeoff_amt']+=$pi_ti['price'];
					
					$this->trade_in_data['writeoff_qty'] += $pi_ti['qty'];
					$this->trade_in_data['writeoff_amt'] += $pi_ti['price'];
				}				
				
				if ($_REQUEST['finalize'] == 1){
					$this->store_data[$counter_id]['trade_in']['qty'] += $pi_ti['qty'];
					$this->store_data[$counter_id]['trade_in']['amt'] += $pi_ti['price'];
					
					if($pi_ti['writeoff_by']){
						$this->store_data[$counter_id]['trade_in']['writeoff_qty'] += $pi_ti['qty'];
						$this->store_data[$counter_id]['trade_in']['writeoff_amt'] += $pi_ti['price'];
					}
				}
			}
			$con->sql_freeresult($q_ti);
		}
		$con->sql_freeresult($q_p);
		
		$con->sql_query("select * from pos_cash_history where branch_id = ".mi($this->branch_id)." and date = ".ms($date)." and counter_id = ".mi($counter_id)." and timestamp > ".ms($s)." and timestamp <= ".ms($e)." order by timestamp") or die(mysql_error());
		while($r = $con->sql_fetchrow())
		{
			switch($r['type']){
				case 'ADVANCE':
					$type = 'cash_advance';
					break;
				case 'TOP_UP':
					$type = 'top_up';
					$this->got_top_up = true;
					break;
				default:
					continue 2;		
			}
			
			if ($r['timestamp'] < $this->trans[$r['counter_id']][$cd_id][$r['user_id']]['start_time'] || !isset($this->trans[$r['counter_id']][$cd_id][$r['user_id']]['start_time'])) $this->trans[$r['counter_id']][$cd_id][$r['user_id']]['start_time'] = $r['timestamp'];
			if ($r['timestamp'] > $this->trans[$r['counter_id']][$cd_id][$r['user_id']]['end_time']) $this->trans[$r['counter_id']][$cd_id][$r['user_id']]['end_time'] = $r['timestamp'];
			
			if($r['timestamp'] < $this->counter_data[$r['counter_id']][$cd_id]['start_time'] || !isset($this->counter_data[$r['counter_id']][$cd_id]['start_time'])) $this->counter_data[$r['counter_id']][$cd_id]['start_time'] = $r['timestamp'];
			if ($r['timestamp'] > $this->counter_data[$r['counter_id']][$cd_id]['end_time']) $this->counter_data[$r['counter_id']][$cd_id]['end_time'] = $r['timestamp'];

			$this->data[$r['counter_id']][$cd_id][$r['user_id']]['Cash'] += 0; // incase :)
			
			if ($_REQUEST['finalize'] == 1)	$this->store_data[$r['counter_id']][$type]['Cash']['amt']+= round($r['amount'],2);
			
			if($type=='cash_advance'){
				$this->cash_advance[$r['counter_id']][$cd_id][$r['user_id']] += $r['amount'];
				$this->counter_data[$r['counter_id']][$cd_id]['advance'] += round($r['amount'],2);
				$this->grandtotal['advance'] += $r['amount'];
			}elseif($type=='top_up'){
				$this->top_up_data[$r['counter_id']][$cd_id][$r['user_id']] += $r['amount'];
				$this->counter_data[$r['counter_id']][$cd_id]['top_up'] += round($r['amount'],2);
				$this->grandtotal['top_up'] += $r['amount'];
			}
		}
		$con->sql_freeresult();
		
		$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.adjust from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where p.branch_id = ".mi($this->branch_id)." and p.date = ".ms($date)." and p.counter_id = ".mi($counter_id)." and p.cancel_status <> 1 and (pt.changed = 1 or pt.adjust = 1) and p.pos_time >= ".ms($s)." and p.pos_time <= ".ms($e)." order by p.pos_time");
		while($r=$con->sql_fetchrow($rs))
		{
			if (in_array($r['type'],$pos_config['credit_card'])) $r['type'] = 'Credit Cards';
			if ($r['adjust'] == 1) $r['payment_amount'] = round($r['payment_amount'],2)*-1;
			$this->adjustment[$r['counter_id']][$cd_id][$r['cashier_id']][$r['type']] += round($r['payment_amount'],2);
			$this->counter_data[$r['counter_id']][$cd_id][$r['type']]['adjustment'] += round($r['payment_amount'],2);
			if ($_REQUEST['finalize'] == 1)	$this->store_data[$r['counter_id']]['adjustment'][$r['type']]['amt']+= round($r['payment_amount'],2);
			$this->type_adjustment[$r['type']] += round($r['payment_amount'],2);
		}
		$con->sql_freeresult();
		
		// check whether got mix and match
		if(!$this->counter_data[$counter_id][$cd_id]['others']['got_mm_discount']){
			$q_pmm = $con->sql_query("select pmm.*
	from pos_mix_match_usage pmm
	left join pos on pos.branch_id=pmm.branch_id and pos.counter_id=pmm.counter_id and pos.date=pmm.date and pos.id=pmm.pos_id
	where pos.branch_id=".mi($this->branch_id)." and pos.counter_id=".mi($counter_id)." and pos.date=".ms($date)." and pos.pos_time between ".ms($s)." and ".ms($e)."
	limit 1");
			if($con->sql_numrows($q_pmm)){
				$this->counter_data[$counter_id][$cd_id]['others']['got_mm_discount']=1;
				$this->got_mm_discount = true;
			}
			$con->sql_freeresult($q_pmm);
		}

		// check if having deposit amt
		$q_pd = $con->sql_query("select p.amount, pp.amount as payment_amount, pdsh.type, pp.type as payment_type, 
								p.cancel_status, p.prune_status
								from pos_deposit_status_history pdsh
								join pos_payment pp on pp.pos_id = pdsh.pos_id and pp.branch_id = pdsh.branch_id and pp.counter_id = pdsh.counter_id and pp.date = pdsh.pos_date
								join pos p on p.id = pp.pos_id and p.branch_id = pp.branch_id and p.date = pp.date and p.counter_id = pp.counter_id
								where 
								pdsh.pos_date = ".ms($date)." and 
								pdsh.branch_id = ".mi($this->branch_id)." and 
								pdsh.counter_id = ".mi($counter_id)."
								and p.pos_time between ".ms($s)." and ".ms($e));

		if($con->sql_numrows($q_pd) > 0){
			while($pos_deposit = $con->sql_fetchrow($q_pd)){
				if($pos_deposit['type'] == "RECEIVED" && !$pos_deposit['cancel_status'] && !$pos_deposit['prune_status'] && $pos_deposit['payment_type'] != "Deposit")
					$this->counter_data[$counter_id][$cd_id]['deposit']['rcv']+=$pos_deposit['amount'];
				elseif($pos_deposit['type'] == "USED" && !$pos_deposit['cancel_status'] && !$pos_deposit['prune_status'] && $pos_deposit['payment_type'] == "Deposit")
					$this->counter_data[$counter_id][$cd_id]['deposit']['used']+=$pos_deposit['payment_amount'];
				elseif($pos_deposit['type'] == "CANCEL_RCV" && $pos_deposit['payment_type'] == "Cash")
					$this->counter_data[$counter_id][$cd_id]['deposit']['cancel_rcv']+=$pos_deposit['amount'];
				elseif($pos_deposit['type'] == "CANCEL_USED" && $pos_deposit['payment_type'] == "Cash")
					$this->counter_data[$counter_id][$cd_id]['deposit']['cancel_used']+=$pos_deposit['amount'];
			}
			$con->sql_freeresult($q_pd);
			
			if(isset($this->counter_data[$counter_id][$cd_id]['deposit'])){
				$this->deposit_data['rcv'] += $this->counter_data[$counter_id][$cd_id]['deposit']['rcv'];
				$this->deposit_data['used'] += $this->counter_data[$counter_id][$cd_id]['deposit']['used'];
				$this->deposit_data['cancel_rcv'] += $this->counter_data[$counter_id][$cd_id]['deposit']['cancel_rcv'];
				$this->deposit_data['cancel_used'] += $this->counter_data[$counter_id][$cd_id]['deposit']['cancel_used'];
				
				if ($_REQUEST['finalize'] == 1){
					$this->store_data[$counter_id]['deposit']['rcv'] += $this->counter_data[$counter_id][$cd_id]['deposit']['rcv'];
					$this->store_data[$counter_id]['deposit']['used'] += $this->counter_data[$counter_id][$cd_id]['deposit']['used'];
					$this->store_data[$counter_id]['deposit']['cancel_rcv'] += $this->counter_data[$counter_id][$cd_id]['deposit']['cancel_rcv'];
					$this->store_data[$counter_id]['deposit']['cancel_used'] += $this->counter_data[$counter_id][$cd_id]['deposit']['cancel_used'];
				}
			}
			
		}
		$pos_cash_domination_notes = array_flip($pos_config['cash_domination_notes']);
		
		if (!$this->data[$r['counter_id']][$cd_id][$d['user_id']] && !$d['user_id']) $d['user_id'] = $ccid;

		if (is_array($d['data']))
		{
			foreach ($d['data'] as $type => $d2)
			{
				if ($type == 'Cheque') $type = 'Check';
				$otype = $type;
				if (in_array($type,$pos_config['credit_card'])) 
					$type = 'Credit Cards';
				elseif (in_array($type,$pos_cash_domination_notes))
				{
					$d2 = $d2 * $pos_config['cash_domination_notes'][$type];
					$type = 'Cash';
				}
				elseif ($type == 'Float')
				{
					$type = 'Cash';
					$d2 *= -1;
				}
				
				if ($otype == 'Float') 
					$this->xtra[$counter_id][$d['id']][$d['user_id']][$otype] += $d2;
				else
				{
					$this->xtra[$counter_id][$d['id']][$d['user_id']][$type] += $d2;
				}								
				$this->cash_domination[$counter_id][$d['id']][$d['user_id']][$type] += $d2;
				$this->counter_data[$counter_id][$cd_id][$type]['cash_domination'] += round($d2,2);
				if ($_REQUEST['finalize'] == 1)	$this->store_data[$counter_id]['cash_domination'][$type]['amt']+= round($d2,2);
				$this->grandtotal['collection'] += $d2;
			}	
		}
		else
		{
//			foreach ($pos_config['payment_type'] as $type)
//			$this->cash_domination[$counter_id][$cd_id][$d['user_id']][$type] += 0;
		}

		if (is_array($d['odata']))
		{
			foreach ($d['odata'] as $type => $d2)
			{
				if ($type == 'Cheque') $type = 'Check';
				if (in_array($type,$pos_config['credit_card'])) 
					$type = 'Credit Cards';
				elseif (in_array($type,$pos_cash_domination_notes))
				{
					$d2 = $d2 * $pos_config['cash_domination_notes'][$type];
					$type = 'Cash';
				}
				elseif ($type == 'Float')
				{
					$type = 'Cash';
					$d2 *= -1;
				}
												
				$this->odata[$counter_id][$d['id']][$d['user_id']][$type] += round($d2,2);
			}
		}

		if (!in_array($mm_discount_col_value,$pos_config['payment_type']))	$payment_type = array_merge($pos_config['payment_type'], array($mm_discount_col_value));	
			
		foreach ($payment_type as $type)
		{
			if ($this->data[$counter_id][$cd_id])
			{
				foreach ($this->data[$counter_id][$cd_id] as $cash_id => $r)
				{
					$bal = $r[$type] + $this->adjustment[$counter_id][$cd_id][$cash_id][$type];
					if ($type == 'Cash'){
						$bal += $this->cash_advance[$counter_id][$cd_id][$cash_id];
						$bal += $this->top_up_data[$counter_id][$cd_id][$cash_id];
					} 
			
					$variance_amt = round($this->cash_domination[$counter_id][$cd_id][$cash_id][$type] - $bal,2);
			
					$this->variance[$counter_id][$cd_id][$cash_id][$type] += $variance_amt;
					$this->counter_data[$counter_id][$cd_id][$type]['variance'] += $variance_amt;
					
					if ($_REQUEST['finalize'] == 1){
						$this->store_data[$counter_id]['variance'][$type]['amt']+= $variance_amt;
					}	
					
					$test += round($this->cash_domination[$counter_id][$cd_id][$cash_id][$type] - $bal,2);
				}
			}
		}
//		print "$test<br>";
		
		return true;
	}

	function recalculate_data(){
		global $mm_discount_col_value;
		if (!$this->store_data)	return;

		foreach ($this->store_data as $counter_id => &$data){
			foreach ($data as $type => $other){
				foreach ($other as $pp_type => $d){
					if ($type == 'cash_advance'){
						$calc['cash_advance']+=$d['amt'];
					}elseif($type == 'cash_domination'){
						$calc['cash_domination']+=$d['amt'];
					}elseif ($type == 'cashier_sales'){
						if ($pp_type=='Discount' || $pp_type==$mm_discount_col_value || $pp_type=='Rounding' || $pp_type=='Over')
							$calc['cashier_sales']['gross']+=$d['amt'];
						else
							$calc['cashier_sales']['nett']+=$d['amt'];
					}
				}
			}

			$variance=$calc['cash_domination']-$calc['cash_advance']-$calc['cashier_sales']['nett'];
			$data['cash_advance']['nett_sales']['amt']=$calc['cash_advance'];
			$data['cash_domination']['nett_sales']['amt']=$calc['cash_domination'];

			$data['cashier_sales']['nett_sales']['amt']=$calc['cashier_sales']['nett'];
			$data['cashier_sales']['gross_sales']['amt']=$calc['cashier_sales']['nett']-$calc['cashier_sales']['gross'];
			$data['variance']['nett_sales']['amt']=$variance;
			unset($variance,$calc);
		}
	}

	
	function get_cashier_id($entry)
	{
	
		global $con, $config;
		
		$counter_id = $entry['counter_id'];
		$date = $entry['date'];
		$user_id = $entry['user_id'];
		$timestamp = $entry['timestmap'];
		
		$counter_version = get_counter_version($this->branch_id, $counter_id);
		$date_time = strtotime($date);
		
		// cashier is from previous sales
		$con->sql_query("select cashier_id from pos where branch_id = ".mi($this->branch_id)." and date = ".ms($date)." and counter_id = ".mi($counter_id)." and pos_time <= ".ms($timestamp)." order by pos_time desc limit 1");
		if ($con->sql_numrows()>0)
			return $con->sql_fetchfield(0);
		// if no previous sales, use next sales
		$con->sql_query("select cashier_id from pos where branch_id = ".mi($this->branch_id)." and date = ".ms($date)." and counter_id = ".mi($counter_id)." and pos_time >= ".ms($timestamp)." order by pos_time asc limit 1");
		if ($con->sql_numrows()>0)
			return $con->sql_fetchfield(0);
		// if no sales! use cash domination
		$time_add = 0;
        if($config['counter_collection_check_old_cash_domination_time'] && ($counter_version<109 || $date_time < $this->v109_time)){
            $time_add = "-8";
        }
		$con->sql_query("select user_id from pos_cash_domination where branch_id = ".mi($this->branch_id)." and date = ".ms($date)." and counter_id = ".mi($counter_id)." and timestamp >= timestampadd(HOUR, $time_add,".ms($timestamp).") order by timestamp asc limit 1");
		if ($con->sql_numrows()>0)
			return $con->sql_fetchfield(0);
		// all also tak ada.. 
		return $user_id;
	}
	
	
	function get_user()
	{
		global $con;
		
		$con->sql_query("select * from user where id = ".mi($_REQUEST['id']));
		if ($con->sql_numrows()>0)
		{
			$user = $con->sql_fetchrow();
			print $user['u'];
		}
		else
			print "Invalid ID";
	}
	
	function get_branch_id()
	{
		global $con;
		$con->sql_query("select * from branch where code = ".ms(BRANCH_CODE));
		
		$branch = $con->sql_fetchrow();
		
		return $branch['id'];
	}
	
	function get_card_type($card_no)
	{
		global $pos_config;
		$identifier_len = 0;
		$card_type = "Others";
		
		if ($this->is_valid_card($card_no))
		{
			foreach($pos_config['issuer_identifier'] as $issuer_identifier)
			{
				$identifier_len = strlen($issuer_identifier[1]);
				$identifier = substr($card_no, 0, $identifier_len);
				if (($identifier >= $issuer_identifier[1]) && ($identifier <= $issuer_identifier[2]) && (strlen($card_no) == $issuer_identifier[3]))
					$card_type = $issuer_identifier[0];
			}
		}
		else $card_type = "Invalid";
		return $card_type;
	}
	
	function is_valid_card($card_no)
	{
		$is_valid = false;
		
		if ((is_numeric($card_no)) && (strpos($card_no, ".") === false))
		{
			if (strlen($card_no) > 0)
			{
				for($str_start = 0; $str_start < strlen($card_no); $str_start++)
				{
					$digits[] = substr($card_no, $str_start, 1);
				}
				
				if (strlen($card_no)%2 == 0)
				{
					foreach($digits as $idx=>$digit)
					{
						if ($idx%2 == 0)
						{
							if ($digit*2 >= 10) $digits[$idx] = $digit * 2 - 9;
							else $digits[$idx] = $digit*2;
						}
					}
				}
				elseif (strlen($card_no)%2 == 1)
				{
					foreach($digits as $idx=>$digit)
					{
						if ($idx%2 == 1)
						{
							if ($digit*2 >= 10) $digits[$idx] = $digit * 2 - 9;
							else $digits[$idx] = $digit*2;
						}
					}
				}
				
				if (array_sum($digits)%10 == 0) $is_valid = true;
			}
		}
		return $is_valid;
	}

	function print_tran_details(){
		global $con, $smarty;
		$smarty->assign('is_print', 1);
		$this->item_details();
	}
	
	function ajax_finalize(){
		global $con;
		
		$this->is_ajax_finalize = true;
		$this->finalize();

		write_process_status('counter_collection','','', false, true);
		print "OK";
	}
	
	function ajax_revert_to_original() {
		global $con;
		$con->sql_query("delete from pos_payment where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and pos_id = ".mi($_REQUEST['revert_pos_id'])." and date = ".ms($_REQUEST['date'])." and changed = 1");
		$con->sql_query("update pos_payment set adjust = 0 where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and pos_id = ".mi($_REQUEST['revert_pos_id'])." and date = ".ms($_REQUEST['date'])." and adjust = 1");
		print 'OK';
		exit;
	}
	
}

$CounterCollection = new CounterCollection ('Counter Collection','counter_collection2.tpl');
?>
