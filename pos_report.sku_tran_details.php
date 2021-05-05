<?php
/*
10/24/2012 11:45 AM Andy
- Add "Group by SKU" feature. When group by sku only show qty in/out/balance.

4:45 PM 11/27/2014 Andy
- Enhance report to show the sales amount using the amount-discount-discount2-tax_amount.(discount2 is receipt and mix & match discount, tax_amount is gst)

4/20/2017 11:58 AM Justin
- Enhanced to replace the receipt_no into receipt_ref_no.

6/4/2019 5:26 PM William
- Enhance GRA,GRN to use report_prefix.

2/24/2020 4:16 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

7/14/2020 10:19 AM William
- Enhanced to added new column Member No, Member Name, IC No, Phone Number.

7/30/2020 5:21 PM William
- Bug fixed SKU Out Qty not correct issue.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
include_once('pos_report.include.php');

class SKU_TRANS_DETAILS extends Module{
	var $bid = 0;
	var $branches = array();
	
	function __construct($title){
		global $con, $smarty, $sessioninfo, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		$this->branches = load_branches();
		
		if(BRANCH_CODE == 'HQ' && isset($_REQUEST['branch_id'])){
			$this->bid = mi($_REQUEST['branch_id']);
		}else{
			$this->bid = $sessioninfo['branch_id'];
		}
		
		parent::__construct($title);
	}
	
	function _default(){
		// make default value
		if(!isset($_REQUEST['date_from']) && !isset($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date("Y-m-d");
			$_REQUEST['date_from'] = date("Y-m-d", strtotime("-7 day", strtotime($_REQUEST['date_to'])));
		}
		
		if($_REQUEST['load_report']){
			$this->load_report();
		}
		$this->display();
	}
	
	private function load_report(){
		global $con, $smarty, $sessioninfo, $config, $con_multi;
		
		//print_r($_REQUEST);
		
		$bid = mi($this->bid);
		$date_from = $_REQUEST['date_from'];
		$date_to = $_REQUEST['date_to'];
		$sid = mi($_REQUEST['sku_item_id']);
		$this->group_by_sku = mi($_REQUEST['group_by_sku']);

		$this->data = array();
		$err = array();
		
		if(!$bid)	$err[] = "Invalid branch.";
		if(!$sid)	$err[] = "Please select SKU first.";
		if(!$date_from || !strtotime($date_from))	$err[] = "Invalid date from.";
		if(!$date_to || !strtotime($date_to))	$err[] = "Invalid date to.";
		if(!$err && strtotime($date_from) > strtotime($date_from))	$err[] = "Date to cannot early then date from.";
		if(!$err && date("Y", strtotime($date_from))<2007)	$err[] = "Report cannot show data early then year 2007.";
		
		$time1 = strtotime($date_from);
		$time2 = strtotime($date_to);
		$time_diff = $time2 - $time1;
		$date_diff = mi($time_diff/86400);
		if(!$err && $date_diff>90)	$err[] = "Report maximum show 90 days of transaction.";
		
		$sid_list = array();
		
		// get sku items info
		if(!$err){
			$con_multi->sql_query("select si.id as sid, si.sku_item_code, si.mcode, si.artno, si.link_code,si.description, si.cost_price as master_cost_price, si.selling_price as master_selling_price, if(sic.changed is null or sic.changed=1, 0, 1) is_up_to_date, si.sku_id, si.is_parent, uom.fraction as packing_uom_fraction
			from sku_items si
			left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
			left join uom on uom.id=si.packing_uom_id
			where si.id=$sid");
			$this->data['si_info'][$sid] = $con_multi->sql_fetchassoc();
			$con_multi->sql_freeresult();
			
			if(!$this->data['si_info'][$sid])	$err[] = "Invalid SKU.";
			$sku_id = mi($this->data['si_info'][$sid]['sku_id']);
			
			if($this->group_by_sku && !$err){
				// get group sku
				$con_multi->sql_query("select si.id as sid, si.sku_item_code, si.mcode, si.artno, si.link_code,si.description, si.cost_price as master_cost_price, si.selling_price as master_selling_price, if(sic.changed is null or sic.changed=1, 0, 1) is_up_to_date, si.sku_id, si.is_parent, uom.fraction as packing_uom_fraction
				from sku_items si
				left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
				left join uom on uom.id=si.packing_uom_id
				where si.sku_id=$sku_id");
				while($r = $con_multi->sql_fetchassoc()){
					if($r['is_parent']){
						$this->data['group_info']['parent_si'] = $r;
					}
					$this->data['si_info'][$r['sid']] = $r;
				}
				$con_multi->sql_freeresult();
			}
		}
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$bcode = get_branch_code($bid);
		$one_day_b4_date_from = date("Y-m-d", strtotime("-1 day", $time1));
		$sb_tbl = "stock_balance_b".$bid."_".date("Y", strtotime($one_day_b4_date_from));
			
		
		// get data by each sku
		foreach($this->data['si_info'] as $tmp_sid => $si){
			// starting variable
			$curr_qty = 0;
			$avg_cost = $cost = $this->data['si_info'][$tmp_sid]['master_cost_price'];
			$selling_price = $this->data['si_info'][$tmp_sid]['master_selling_price'];
		
			// get opening cost
			$con_multi->sql_query("select * from $sb_tbl where sku_item_id=$tmp_sid and ".ms($one_day_b4_date_from)." between from_date and to_date limit 1");
			$tmp = $con_multi->sql_fetchassoc();
			$con_multi->sql_freeresult();
			
			// got cost history
			if($tmp){
				$curr_qty = $tmp['qty'];
				$cost = $tmp['cost'];
				$avg_cost = $tmp['avg_cost'];
			}
			
			// get opening selling price
			$con_multi->sql_query("select * from sku_items_price_history where branch_id=$bid and sku_item_id=$tmp_sid and added<".ms($date_from)." order by added desc limit 1");
			$tmp = $con_multi->sql_fetchassoc();
			$con_multi->sql_freeresult();
			
			// got selling price history
			if($tmp){
				$selling_price = $tmp['price'];
			}
			
			// set cost, avg cost, selling
			$this->data['si_info'][$tmp_sid]['curr_qty'] = $curr_qty;
			$this->data['si_info'][$tmp_sid]['cost'] = $cost;
			$this->data['si_info'][$tmp_sid]['avg_cost'] = $avg_cost;
			$this->data['si_info'][$tmp_sid]['selling_price'] = $selling_price;
			
			$this->data['si_info'][$tmp_sid]['tmp']['curr_qty'] = $curr_qty;
			$this->data['si_info'][$tmp_sid]['tmp']['cost'] = $cost;
			$this->data['si_info'][$tmp_sid]['tmp']['avg_cost'] = $avg_cost;
			$this->data['si_info'][$tmp_sid]['tmp']['selling_price'] = $selling_price;
		}
		
		// get data by group sku
		$sid_str = join(',', array_keys($this->data['si_info']));
		
		// get selling price change
		$con_multi->sql_query("select sku_item_id as sid, added,price,date(added) as dt from sku_items_price_history where branch_id=$bid and sku_item_id in ($sid_str) and added between ".ms($date_from)." and ".ms($date_to.' 23:59:59')." order by added");
		while($r = $con_multi->sql_fetchassoc()){
			$dt = $r['dt'];
			
			$this->data['tran_info'][$dt][$r['sid']]['sp_changed'][] = $r;
		}
		$con_multi->sql_freeresult();
			
		// stock check
		$con_multi->sql_query("select si.id as sid, sum(sc.qty) as qty, sum(sc.qty*sc.cost) as cost, date as dt
		from stock_check sc
		join sku_items si using (sku_item_code) 
		where si.id in ($sid_str) and branch_id = $bid and sc.date between ".ms($date_from)." and ".ms($date_to)." group by sc.date,si.id order by date");
		while($r = $con_multi->sql_fetchassoc()){
			$this->data['tran_info'][$r['dt']][$r['sid']]['stock_check']['qty'] = $r['qty'];
			$this->data['tran_info'][$r['dt']][$r['sid']]['stock_check']['cost'] = $r['cost'];
		}
		$con_multi->sql_freeresult();
	
		// grn
		$sql = "select grn_items.sku_item_id as sid, (if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
		(
		  if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost)
		  *
		  if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
		  	grn_items.ctn + grn_items.pcs / rcv_uom.fraction,
		  	grn_items.acc_ctn + grn_items.acc_pcs / rcv_uom.fraction
		  )
		) as cost, grr. rcv_date as dt, grn.grr_id, grn.is_future, grn.id as grn_id,
		gi.type, do.do_type, do.branch_id as do_from_branch_id,grr.vendor_id
		from grn_items
		left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
		left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
		left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
		left join grr_items gi on gi.id=grn.grr_item_id and gi.branch_id=grn.branch_id
		left join do on do.do_no=gi.doc_no and gi.type='DO'
		left join sku_items on grn_items.sku_item_id = sku_items.id
		where grn_items.sku_item_id in ($sid_str) and grn_items.branch_id = $bid and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1 and grr.rcv_date between ".ms($date_from)." and ".ms($date_to)." order by grr.rcv_date, grr.id";

		$sql1 = $con_multi->sql_query($sql);
		while($r=$con_multi->sql_fetchassoc($sql1)){
			if($r['is_future']){	// is future grn
				$sql2 = $con_multi->sql_query("select type, 
									 case when type = 'PO' then 1 when type = 'INVOICE' then 2 when type = 'DO' then 3 else 4 end as type_asc, do.do_type, do.branch_id as do_from_branch_id
									 from grr_items gi
									 left join grr on grr.id = gi.grr_id and grr.branch_id = gi.branch_id
									 left join do on do.do_no=gi.doc_no and gi.type='DO'
									 where gi.grr_id = $r[grr_id] and gi.branch_id = $bid
									 group by type_asc
									 order by type_asc asc
									 limit 1");

				$gi_info = $con_multi->sql_fetchassoc($sql2);
				$con_multi->sql_freeresult($sql2);
				
				$r['type'] = $gi_info['type'];
				$r['do_type'] = $gi_info['do_type'];
				$r['do_from_branch_id'] = $gi_info['do_from_branch_id'];
			}

			$grn = array();
			$grn['qty'] = $r['qty'];
			$grn['grn_id'] = $r['grn_id'];
			$grn['grn_cost'] = $r['cost'];
		    $grn['vendor_id'] = $r['vendor_id'];
		    
		    // whether cost will be effected by this grn
		    $count_this_grn = false;

		    if($r['type']!='DO'){
	            $count_this_grn = true;
			}else{  // document type = DO
			    if(!$r['do_type']||!$r['do_from_branch_id'])  $count_this_grn = true; // DO from outside
			    else{   // inter transfer DO
	                if($config['grn_do_hq2branch_update_cost'] && $bid >1&&$r['do_from_branch_id']==1)   $count_this_grn = true;
	                if($config['grn_do_branch2branch_update_cost'] && $bid >1&&$r['do_from_branch_id']>1)   $count_this_grn = true;
	                if($config['grn_do_branch2hq_update_cost'] && $bid ==1&&$r['do_from_branch_id']>1)   $count_this_grn = true;
				}
			}

		    if($count_this_grn){
		      	$grn['need_update_cost'] = 1;
		    }
		    
		    $this->data['tran_info'][$r['dt']][$r['sid']]['grn'][] = $grn;
		}
		$con_multi->sql_freeresult($sql1);
		
		// ADJUSTMENT
		$q_adj = $con_multi->sql_query("select adjustment_items.sku_item_id as sid, qty, adjustment_date as dt, adjustment_items.adjustment_id 
	from adjustment_items
	left join adjustment on adjustment.id=adjustment_items.adjustment_id and adjustment.branch_id=adjustment_items.branch_id
	where adjustment_items.sku_item_id in ($sid_str) and adjustment_items.branch_id = $bid and adjustment.approved=1 and adjustment.status=1 and adjustment.active=1 and adjustment_date between ".ms($date_from)." and ".ms($date_to)."
	group by adjustment.branch_id,adjustment.id,adjustment_items.sku_item_id");
		while($r=$con_multi->sql_fetchassoc($q_adj)){
			$tmp = array();
			$tmp['qty'] = $r['qty'];
			$tmp['adjustment_id'] = $r['adjustment_id'];
			
			$this->data['tran_info'][$r['dt']][$r['sid']]['adj'][] = $tmp;
		}
		$con_multi->sql_freeresult($q_adj);
		
		// DO
		$q_do = $con_multi->sql_query("select do_items.sku_item_id as sid, sum(do_items.ctn *uom.fraction + do_items.pcs) as qty, do.do_date as dt, do.do_no, do.do_type, do.do_branch_id, do.debtor_id,do.open_info
	from do_items
	left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
	left join uom on do_items.uom_id=uom.id
	where do_items.sku_item_id in ($sid_str) and do_items.branch_id = $bid and do.approved=1 and do.checkout=1 and do.status=1 and do.active=1 and do_date between ".ms($date_from)." and ".ms($date_to)." 
	group by do.branch_id,do.id,do_items.sku_item_id");
		while($r=$con_multi->sql_fetchassoc($q_do)){
			$tmp = array();
			$tmp['qty'] = $r['qty'];
			$tmp['do_no'] = $r['do_no'];
			$tmp['do_type'] = $r['do_type'];
			
			if($r['do_type']=='transfer'){
				$tmp['do_branch_id'] = $r['do_branch_id'];
			}elseif($r['do_type']=='credit_sales'){
				$tmp['debtor_id'] = $r['debtor_id'];
			}else{
				$r['open_info'] = unserialize($r['open_info']);
				$tmp['open_info'] = $r['open_info'];
			}
			$this->data['tran_info'][$r['dt']][$r['sid']]['do'][] = $tmp;
		}
		$con_multi->sql_freeresult($q_do);
		
		// GRA
		$q_gra = $con_multi->sql_query("select gra_items.sku_item_id as sid, qty, return_timestamp, date(return_timestamp) as dt, gra.vendor_id, gra.id as gra_id
		from gra_items 
		left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id 
		where gra_items.sku_item_id in ($sid_str) and gra_items.branch_id = $bid and gra.status=0 and gra.returned=1 and return_timestamp between ".ms($date_from)." and ".ms($date_to.' 23:59:59')." group by dt, gra_items.sku_item_id");
		while($r=$con_multi->sql_fetchassoc($q_gra)){
			$tmp = array();
			$tmp['qty'] = $r['qty'];
			$tmp['vendor_id'] = $r['vendor_id'];
			$tmp['return_timestamp'] = $r['return_timestamp'];
			$tmp['gra_id'] = $r['gra_id'];
			
			$this->data['tran_info'][$r['dt']][$r['sid']]['gra'][]= $tmp;
		}
		$con_multi->sql_freeresult($q_gra);
		
		// POS
		$con_multi->sql_query("select pi.sku_item_id as sid, sum(pi.qty) as qty, sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) as amt,pi.date as dt,pi.counter_id,pos.cashier_id,pos.pos_time,pos.receipt_no, pos.receipt_ref_no, pos.member_no as member_no, member.name as member_name, member.nric as nric, if(pos.member_no ='', '', if(member.phone_1='' or member.phone_1 is null, if(member.phone_2='' or member.phone_2 is null, member.phone_3, member.phone_2),member.phone_1)) as phone, member.address as address, pf.finalized as finalized
		from pos_items pi
		join pos on pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id and pos.id=pi.pos_id
		left join membership member on member.membership_guid=pos.membership_guid and pos.membership_guid <>''
		join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date
		where pi.sku_item_id in ($sid_str) and pi.branch_id=$bid and pi.date between ".ms($date_from)." and ".ms($date_to)." and pos.cancel_status=0
		group by pos.branch_id,pos.date,pos.counter_id,pos.id,pi.sku_item_id");
		while($r=$con_multi->sql_fetchrow())
		{
			$tmp = array();
			$tmp['qty'] = $r['qty'];
			$tmp['amt'] = $r['amt'];
			$tmp['counter_id'] = $r['counter_id'];
			$tmp['cashier_id'] = $r['cashier_id'];
			$tmp['receipt_no'] = $r['receipt_no'];
			$tmp['receipt_ref_no'] = $r['receipt_ref_no'];
			$tmp['time'] = date("H:i", strtotime($r['pos_time']));
			$tmp['member_no'] = $r['member_no'];
			$tmp['member_name'] = $r['member_name'];
			$tmp['nric'] = $r['nric'];
			$tmp['phone'] = $r['phone'];
			$tmp['address'] = $r['address'];
			$tmp['finalized'] = $r['finalized'];
			
			$this->data['tran_info'][$r['dt']][$r['sid']]['pos'][]= $tmp;
		}
		$con_multi->sql_freeresult();
		
		if($config['consignment_modules']){
			//FROM Credit Note
			$con_multi->sql_query("select cn_items.sku_item_id as sid, sum(cn_items.ctn *uom.fraction + cn_items.pcs) as qty, cn.date as dt, cn.inv_no
	from cn_items
	left join cn on cn.id=cn_items.cn_id and cn.branch_id=cn_items.branch_id
	left join sku_items on sku_item_id = sku_items.id
	left join uom on cn_items.uom_id=uom.id
	where cn_items.sku_item_id in ($sid_str) and cn.to_branch_id = $bid and cn.active=1 and cn.approved=1 and cn.status=1 and cn.date between ".ms($date_from)." and ".ms($date_to)." 
	group by cn.branch_id,cn.id,cn_items.sku_item_id");
			while($r=$con_multi->sql_fetchassoc()){
				$tmp = array();
				$tmp['qty'] = $r['qty'];
				$tmp['inv_no'] = $r['inv_no'];
				
				$this->data['tran_info'][$r['dt']][$r['sid']]['cn'][] = $tmp;
			}
			$con_multi->sql_freeresult();

			//FROM Debit Note
			$con_multi->sql_query("select dn_items.sku_item_id as sid, sum(dn_items.ctn *uom.fraction + dn_items.pcs) as qty, dn.date as dt, dn.inv_no
	from dn_items
	left join dn on dn.id=dn_items.dn_id and dn.branch_id=dn_items.branch_id
	left join sku_items on sku_item_id = sku_items.id
	left join uom on dn_items.uom_id=uom.id
	where dn_items.sku_item_id in ($sid_str) and dn.to_branch_id = $bid and dn.active=1 and dn.approved=1 and dn.status=1 and dn.date ".ms($date_from)." and ".ms($date_to)."
	group by dn.branch_id, dn.id, dn_items.sku_item_id");
			while($r=$con_multi->sql_fetchassoc()){
				$tmp = array();
				$tmp['qty'] = $r['qty'];
				$tmp['inv_no'] = $r['inv_no'];
				
				$this->data['tran_info'][$r['dt']][$r['sid']]['dn'][] = $tmp;
			}
			$con_multi->sql_freeresult();
		}
		
		$this->main_use_sid = $sid;
		if($this->group_by_sku)	$this->main_use_sid = $this->data['group_info']['parent_si']['sid'];
		
		if($this->data['tran_info'] && $this->data['si_info']){
			ksort($this->data['tran_info']);
			
			// row opening
			foreach($this->data['si_info'] as $tmp_sid => $si){	
				$curr_qty = $si['tmp']['curr_qty'];
				$avg_cost = $si['tmp']['avg_cost'];
				$cost = $si['tmp']['cost'];
				$selling_price = $si['tmp']['selling_price'];
				$total_avg_cost = $curr_qty*$avg_cost;
				
				$tmp = array();
				$tmp['date'] = $date_from;
				$tmp['doc_type'] = 'OB';
				$tmp['source_label'] = 'Opening Balance';
				$tmp['bal']['qty'] = $curr_qty;
				$tmp['bal']['selling_price'] = $selling_price;
				$tmp['bal']['cost'] = $cost;
				$tmp['avg']['cost'] = $avg_cost;
				//$this->data['si_data'][$tmp_sid][] = $tmp;
				
				if($this->group_by_sku || $tmp_sid == $this->main_use_sid)	$this->data['si_date_data'][$date_from][$tmp_sid][] = $tmp;
			}
			
			$group_info = $this->get_group_curr_info();
			
			$tmp['bal']['qty'] = $group_info['bal']['qty'];
			$tmp['bal']['selling_price'] = $group_info['bal']['selling_price'];
			$tmp['bal']['cost'] = $group_info['bal']['cost'];
			$tmp['avg']['cost'] = $group_info['avg']['cost'];;
			$this->data['data'][] = $tmp;
			
			foreach($this->data['tran_info'] as $dt => $sku_daily_tran_list){	// loop for date
				// loop the thing need to be group
				
				// stock check
				$tmp_stock_check = array();
				foreach($this->data['si_info'] as $tmp_sid => $si){
					if(!$sku_daily_tran_list[$tmp_sid]['stock_check'])	continue;	// this date no data for this sku
					
					$curr_qty = $si['tmp']['curr_qty'];
					$avg_cost = $si['tmp']['avg_cost'];
					$cost = $si['tmp']['cost'];
					$selling_price = $si['tmp']['selling_price'];
					$total_avg_cost = $curr_qty*$avg_cost;

					$daily_tran_list = $sku_daily_tran_list[$tmp_sid];
					// stock take
					if($daily_tran_list['stock_check']){
						// overwrite cost & avg cost
						if($daily_tran_list['stock_check']['cost'] && $daily_tran_list['stock_check']['qty']){
							$avg_cost = $cost = round($daily_tran_list['stock_check']['cost'] / $daily_tran_list['stock_check']['qty'], 5);
						}	
						
						// overwrite qty
						$curr_qty = $daily_tran_list['stock_check']['qty'];
						
						// overwrite total avg cost
						$total_avg_cost = $curr_qty * $avg_cost;
						
						$tmp = array();
						$tmp['date'] = $tmp_stock_check['date'] = $dt;
						$tmp['doc_type'] = $tmp_stock_check['doc_type'] = 'Stock Check';
						$tmp['source_label'] = $tmp_stock_check['source_label'] = 'Stock Check';
						$tmp['bal']['qty'] = $curr_qty;
						$tmp['bal']['selling_price'] = $selling_price;
						$tmp['bal']['cost'] = $cost;
						$tmp['avg']['cost'] = $avg_cost;
						
						//$this->data['si_data'][$tmp_sid][] = $tmp;
						$this->data['si_date_data'][$dt][$tmp_sid][] = $tmp;
						
						$this->data['si_info'][$tmp_sid]['tmp']['curr_qty'] = $curr_qty;
						$this->data['si_info'][$tmp_sid]['tmp']['avg_cost'] = $avg_cost;
						$this->data['si_info'][$tmp_sid]['tmp']['cost'] = $cost;
						$this->data['si_info'][$tmp_sid]['tmp']['selling_price'] = $selling_price;
				
						$group_info = $this->get_group_curr_info($tmp_sid, $tmp);
		
						if(isset($group_info['in']['qty']))	$tmp_stock_check['in']['qty'] = $group_info['in']['qty'];
						if(isset($group_info['out']['qty']))	$tmp_stock_check['out']['qty'] = $group_info['out']['qty'];
						$tmp_stock_check['bal']['qty'] = $group_info['bal']['qty'];
						$tmp_stock_check['bal']['selling_price'] = $group_info['bal']['selling_price'];
						$tmp_stock_check['bal']['cost'] = $group_info['bal']['cost'];
						$tmp_stock_check['avg']['cost'] = $group_info['avg']['cost'];;
					}
				}
				if($tmp_stock_check)	$this->data['data'][] = $tmp_stock_check;
				
				$q2 = $con_multi->sql_query("select report_prefix from branch where id='$bid'");
				$r2 = $con_multi->sql_fetchassoc($q2);
				$report_prefix = $r2['report_prefix'];
				$con_multi->sql_freeresult($q2);
				
				foreach($this->data['si_info'] as $tmp_sid => $si){
					if(!$sku_daily_tran_list[$tmp_sid])	continue;	// this date no data for this sku	
					$daily_tran_list = $sku_daily_tran_list[$tmp_sid];
					
					$curr_qty = $si['tmp']['curr_qty'];
					$avg_cost = $si['tmp']['avg_cost'];
					$cost = $si['tmp']['cost'];
					$selling_price = $si['tmp']['selling_price'];
					
					// GRN
					if($daily_tran_list['grn']){
						// loop for each grn
						foreach($daily_tran_list['grn'] as $grn){
							$tmp = array();
							$tmp['date'] = $dt;
							$tmp['doc_type'] = 'GRN';
							$tmp['doc_no'] = sprintf($report_prefix."%05d", $grn['grn_id']);
							
							$vendor_info = $this->get_vendor_info($grn['vendor_id']);
							$tmp['source_label'] = $vendor_info['description'];
							
							$tmp['in']['qty'] = $grn['qty'];
							$tmp['in']['selling_price'] = $selling_price;
							
							// add qty
							$curr_qty += $grn['qty'];
							
							// this grn will effect cost
							if($grn['need_update_cost']){
								// overwrite cost
								$cost = round($grn['grn_cost']/$grn['qty'], 5);
								
								// add new cost into total average cost
								$total_avg_cost += $grn['qty']*$cost;
								
								$new_added_cost = $grn['grn_cost'];
								$new_added_qty = $grn['qty'];
							}else{
								// this grn wont effect cost
								
								// use the last avg cost
								$total_avg_cost += $grn['qty']*$avg_cost;
							}
							
							$tmp['in']['cost'] = $cost;
							$avg_cost = $total_avg_cost/$curr_qty;
							
							// balance
							$tmp['bal']['qty'] = $curr_qty;
							$tmp['bal']['selling_price'] = $selling_price;
							$tmp['bal']['cost'] = $cost;
							$tmp['avg']['cost'] = $avg_cost;
						
							//$this->data['si_data'][$tmp_sid][] = $tmp;
							$this->data['si_date_data'][$dt][$tmp_sid][] = $tmp;
							
							$this->data['si_info'][$tmp_sid]['tmp']['curr_qty'] = $curr_qty;
							$this->data['si_info'][$tmp_sid]['tmp']['avg_cost'] = $avg_cost;
							$this->data['si_info'][$tmp_sid]['tmp']['cost'] = $cost;
							$this->data['si_info'][$tmp_sid]['tmp']['selling_price'] = $selling_price;

							$group_info = $this->get_group_curr_info($tmp_sid, $tmp);
			
							if(isset($group_info['in']['qty']))	$tmp['in']['qty'] = $group_info['in']['qty'];
							if(isset($group_info['out']['qty']))	$tmp['out']['qty'] = $group_info['out']['qty'];
							$tmp['bal']['qty'] = $group_info['bal']['qty'];
							$tmp['bal']['selling_price'] = $group_info['bal']['selling_price'];
							$tmp['bal']['cost'] = $group_info['bal']['cost'];
							$tmp['avg']['cost'] = $group_info['avg']['cost'];;
							$this->data['data'][] = $tmp;
						}
					}
					
					// ADJUSTMENT
					if($daily_tran_list['adj']){
						// loop for each adjustment
						foreach($daily_tran_list['adj'] as $adj){
							$tmp = array();
							$tmp['date'] = $dt;
							$tmp['doc_type'] = 'ADJ';
							$tmp['doc_no'] = $bcode.sprintf("%05d", $adj['adjustment_id']);
							
							$tmp['source_label'] = 'Adjustment';
							
							$adj_type =  $adj['qty'] >= 0 ? 'in' : 'out';
							
							// add qty
							$curr_qty += $adj['qty'];
							
							// update total avg cost
							$total_avg_cost = $curr_qty * $avg_cost;
							
							$tmp[$adj_type]['qty'] = $adj['qty'];
							$tmp[$adj_type]['selling_price'] = $selling_price;
							$tmp[$adj_type]['cost'] = $cost;
							
							// balance
							$tmp['bal']['qty'] = $curr_qty;
							$tmp['bal']['selling_price'] = $selling_price;
							$tmp['bal']['cost'] = $cost;
							$tmp['avg']['cost'] = $avg_cost;
							
							//$this->data['si_data'][$tmp_sid][] = $tmp;
							$this->data['si_date_data'][$dt][$tmp_sid][] = $tmp;
							
							$this->data['si_info'][$tmp_sid]['tmp']['curr_qty'] = $curr_qty;
							$this->data['si_info'][$tmp_sid]['tmp']['avg_cost'] = $avg_cost;
							$this->data['si_info'][$tmp_sid]['tmp']['cost'] = $cost;
							$this->data['si_info'][$tmp_sid]['tmp']['selling_price'] = $selling_price;
					
							$group_info = $this->get_group_curr_info($tmp_sid, $tmp);
			
							if(isset($group_info['in']['qty']))	$tmp['in']['qty'] = $group_info['in']['qty'];
							if(isset($group_info['out']['qty']))	$tmp['out']['qty'] = $group_info['out']['qty'];
							$tmp['bal']['qty'] = $group_info['bal']['qty'];
							$tmp['bal']['selling_price'] = $group_info['bal']['selling_price'];
							$tmp['bal']['cost'] = $group_info['bal']['cost'];
							$tmp['avg']['cost'] = $group_info['avg']['cost'];;
							$this->data['data'][] = $tmp;
						}
					}
					
					// DO
					if($daily_tran_list['do']){
						// loop for each do
						foreach($daily_tran_list['do'] as $do){
							$tmp = array();
							$tmp['date'] = $dt;
							$tmp['doc_type'] = 'DO';
							$tmp['doc_no'] = $do['do_no'];
							
							if($do['do_type']=='transfer'){
								$tmp['source_label'] = 'DO to '.get_branch_code($do['do_branch_id']);
							}elseif($do['do_type']=='credit_sales'){
								$debtor_info = $this->get_debtor_info($do['debtor_id']);
								$tmp['source_label'] = 'DO to '.$debtor_info['code']." ".$debtor_info['description'];
							}else{
								$tmp['source_label'] = 'DO to '.$do['open_info']['name'];
							}
							
							
							// minus qty
							$curr_qty -= $do['qty'];
							
							// update total avg cost
							$total_avg_cost = $curr_qty * $avg_cost;
							
							$tmp['out']['qty'] = $do['qty'];
							$tmp['out']['selling_price'] = $selling_price;
							$tmp['out']['cost'] = $cost;
							
							// balance
							$tmp['bal']['qty'] = $curr_qty;
							$tmp['bal']['selling_price'] = $selling_price;
							$tmp['bal']['cost'] = $cost;
							$tmp['avg']['cost'] = $avg_cost;
							
							//$this->data['si_data'][$tmp_sid][] = $tmp;
							$this->data['si_date_data'][$dt][$tmp_sid][] = $tmp;
							
							$this->data['si_info'][$tmp_sid]['tmp']['curr_qty'] = $curr_qty;
							$this->data['si_info'][$tmp_sid]['tmp']['avg_cost'] = $avg_cost;
							$this->data['si_info'][$tmp_sid]['tmp']['cost'] = $cost;
							$this->data['si_info'][$tmp_sid]['tmp']['selling_price'] = $selling_price;
					
							$group_info = $this->get_group_curr_info($tmp_sid, $tmp);
			
							if(isset($group_info['in']['qty']))	$tmp['in']['qty'] = $group_info['in']['qty'];
							if(isset($group_info['out']['qty']))	$tmp['out']['qty'] = $group_info['out']['qty'];
							$tmp['bal']['qty'] = $group_info['bal']['qty'];
							$tmp['bal']['selling_price'] = $group_info['bal']['selling_price'];
							$tmp['bal']['cost'] = $group_info['bal']['cost'];
							$tmp['avg']['cost'] = $group_info['avg']['cost'];;
							$this->data['data'][] = $tmp;
						}
					}
					
					// GRA
					if($daily_tran_list['gra']){
						// loop for each gra
						foreach($daily_tran_list['gra'] as $gra){
							$tmp = array();
							$tmp['date'] = $dt;
							$tmp['doc_type'] = 'GRA';
							$tmp['doc_no'] = $report_prefix.sprintf("%05d", $gra['gra_id']);
							
							$vendor_info = $this->get_vendor_info($gra['vendor_id']);
							$tmp['source_label'] = $vendor_info['description'];
							
							// minus qty
							$curr_qty -= $gra['qty'];
							
							// update total avg cost
							$total_avg_cost = $curr_qty * $avg_cost;
							
							$tmp['out']['qty'] = $gra['qty'];
							$tmp['out']['selling_price'] = $selling_price;
							$tmp['out']['cost'] = $cost;
							
							// balance
							$tmp['bal']['qty'] = $curr_qty;
							$tmp['bal']['selling_price'] = $selling_price;
							$tmp['bal']['cost'] = $cost;
							$tmp['avg']['cost'] = $avg_cost;
							
							//$this->data['si_data'][$tmp_sid][] = $tmp;
							$this->data['si_date_data'][$dt][$tmp_sid][] = $tmp;
							
							$this->data['si_info'][$tmp_sid]['tmp']['curr_qty'] = $curr_qty;
							$this->data['si_info'][$tmp_sid]['tmp']['avg_cost'] = $avg_cost;
							$this->data['si_info'][$tmp_sid]['tmp']['cost'] = $cost;
							$this->data['si_info'][$tmp_sid]['tmp']['selling_price'] = $selling_price;
					
							$group_info = $this->get_group_curr_info($tmp_sid, $tmp);
			
							if(isset($group_info['in']['qty']))	$tmp['in']['qty'] = $group_info['in']['qty'];
							if(isset($group_info['out']['qty']))	$tmp['out']['qty'] = $group_info['out']['qty'];
							$tmp['bal']['qty'] = $group_info['bal']['qty'];
							$tmp['bal']['selling_price'] = $group_info['bal']['selling_price'];
							$tmp['bal']['cost'] = $group_info['bal']['cost'];
							$tmp['avg']['cost'] = $group_info['avg']['cost'];;
							$this->data['data'][] = $tmp;
						}
					}

					// POS
					if($daily_tran_list['pos']){						
						// loop for each gra
						foreach($daily_tran_list['pos'] as $pos){
							$tmp = array();
							$tmp['date'] = $dt;
							$tmp['doc_type'] = 'POS';
							$tmp['doc_no'] = $pos['receipt_ref_no'];
							
							$counter_info = $this->get_counter_info($bid, $pos['counter_id']);
							$cashier_info = $this->get_cashier_info($pos['cashier_id']);
							
							$tmp['source_label'] = $counter_info['network_name'].'-'.$cashier_info['u'].'-'.$pos['time'];
							
							
							// minus qty
							$curr_qty -= $pos['qty'];
							
							// update total avg cost
							$total_avg_cost = $curr_qty * $avg_cost;
							
							if($pos['qty']<0){ // is return
								$tmp['in']['qty'] = $pos['qty']*-1;
								//$tmp['out']['selling_price'] = $selling_price;
								$tmp['in']['amt'] = $pos['amt']*-1;	// pos use own selling price
								$tmp['in']['cost'] = $cost;
								
								$tmp['source_label'] .= " (return)";
							}else{
								$tmp['out']['qty'] = $pos['qty'];
								//$tmp['out']['selling_price'] = $selling_price;
								$tmp['out']['amt'] = $pos['amt'];	// pos use own selling price
								$tmp['out']['cost'] = $cost;
							}
							
							
							// balance
							$tmp['bal']['qty'] = $curr_qty;
							$tmp['bal']['selling_price'] = $selling_price;
							$tmp['bal']['cost'] = $cost;
							$tmp['avg']['cost'] = $avg_cost;
							
							//member
							$tmp['member_no'] = $pos['member_no'];
							$tmp['member_name'] = $pos['member_name'];
							$tmp['nric'] = $pos['nric'];
							$tmp['phone'] = $pos['phone'];
							$tmp['address'] = $pos['address'];
							$tmp['finalized'] = $pos['finalized'];
							
							//$this->data['si_data'][$tmp_sid][] = $tmp;
							$this->data['si_date_data'][$dt][$tmp_sid][] = $tmp;
							
							$this->data['si_info'][$tmp_sid]['tmp']['curr_qty'] = $curr_qty;
							$this->data['si_info'][$tmp_sid]['tmp']['avg_cost'] = $avg_cost;
							$this->data['si_info'][$tmp_sid]['tmp']['cost'] = $cost;
							$this->data['si_info'][$tmp_sid]['tmp']['selling_price'] = $selling_price;
					
							$group_info = $this->get_group_curr_info($tmp_sid, $tmp);
			
							if(isset($group_info['in']['qty']))	$tmp['in']['qty'] = $group_info['in']['qty'];
							if(isset($group_info['out']['qty']))	$tmp['out']['qty'] = $group_info['out']['qty'];
							$tmp['bal']['qty'] = $group_info['bal']['qty'];
							$tmp['bal']['selling_price'] = $group_info['bal']['selling_price'];
							$tmp['bal']['cost'] = $group_info['bal']['cost'];
							$tmp['avg']['cost'] = $group_info['avg']['cost'];;
							$this->data['data'][] = $tmp;
						}
					} // end of pos				
				}	// end loop of sku items
			}
			
			foreach($this->data['si_date_data'] as $dt => $si_tran_list){
				foreach($si_tran_list as $tmp_sid => $tran_list){
				
				}	// end loop $si_tran_list
			}	// end loop si_date_data
		}
		
		//print_r($this->data);
		
		$report_title = array();
		$report_title[] = "Branch: ".$bcode;
		$report_title[] = "Date: ".$date_from." to ".$date_to;
		
		$smarty->assign('data', $this->data);
		$smarty->assign('main_use_sid', $this->main_use_sid);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
	
	private function get_vendor_info($vid){
		global $con, $con_multi;
		
		if(isset($this->data['vendor_info'][$vid]))	return $this->data['vendor_info'][$vid];
		
		$con_multi->sql_query("select code,description from vendor where id=".mi($vid));
		$this->data['vendor_info'][$vid] = $con_multi->sql_fetchassoc();
		$con_multi->sql_freeresult();
		
		return $this->data['vendor_info'][$vid];
	}
	
	private function get_debtor_info($debtor_id){
		global $con, $con_multi;
		
		if(isset($this->data['debtor_info'][$debtor_id]))	return $this->data['debtor_info'][$debtor_id];
		
		$con_multi->sql_query("select code,description from debtor where id=".mi($debtor_id));
		$this->data['debtor_info'][$debtor_id] = $con_multi->sql_fetchassoc();
		$con_multi->sql_freeresult();
		
		return $this->data['debtor_info'][$debtor_id];
	}
	
	private function get_counter_info($bid, $cid){
		global $con, $con_multi;
		
		$key = $bid.'_'.$cid;
		if(isset($this->data['counter_info'][$key]))	return $this->data['counter_info'][$key];
		
		$con_multi->sql_query("select network_name from counter_settings where branch_id=".mi($bid)." and id=".mi($cid));
		$this->data['counter_info'][$key] = $con_multi->sql_fetchassoc();
		$con_multi->sql_freeresult();
		
		return $this->data['counter_info'][$key];
	}
	
	private function get_cashier_info($uid){
		global $con, $con_multi;
		
		if(isset($this->data['cashier_info'][$uid]))	return $this->data['cashier_info'][$uid];
		
		$con_multi->sql_query("select u from user where id=".mi($uid));
		$this->data['cashier_info'][$uid] = $con_multi->sql_fetchassoc();
		$con_multi->sql_freeresult();
		
		return $this->data['cashier_info'][$uid];
	}
	
	private function get_group_curr_info($sid = 0, $tmp = array()){
		$group_curr_qty = 0;
		
		foreach($this->data['si_info'] as $tmp_sid => $si){	
			$curr_qty = $si['tmp']['curr_qty'];

			// calculate group curr qty
			$group_curr_qty += ($curr_qty * $si['packing_uom_fraction']);
		}
		
		$ret = array();
		
		// calculate group in/out qty
		if($sid && $tmp && $this->group_by_sku){
			if(isset($tmp['in']['qty'])){
				$ret['in']['qty'] = ($tmp['in']['qty'] * $this->data['si_info'][$sid]['packing_uom_fraction']);
			}
			if(isset($tmp['out']['qty'])){
				$ret['out']['qty'] = ($tmp['out']['qty'] * $this->data['si_info'][$sid]['packing_uom_fraction']);
			}
		}

		if($this->group_by_sku){
			$ret['bal']['qty'] = $group_curr_qty;
		}else{
			$ret['bal']['qty'] = $this->data['si_info'][$this->main_use_sid]['tmp']['curr_qty'];			
		}
		
		$ret['bal']['selling_price'] = $this->data['si_info'][$this->main_use_sid]['tmp']['selling_price'];
		$ret['bal']['cost'] = $this->data['si_info'][$this->main_use_sid]['tmp']['cost'];
		$ret['avg']['cost'] = $this->data['si_info'][$this->main_use_sid]['tmp']['avg_cost'];
		
		return $ret;
	}
}

$SKU_TRANS_DETAILS = new SKU_TRANS_DETAILS('SKU Transaction Details Report');
?>
