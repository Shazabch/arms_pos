<?php
/*
06/01/2016 17:00 Edwin
- Bug fixed on transaction type does not load based on branch's pos_settings.

7/13/2018 4:08 PM Justin
- Enhanced payment type filter to have foreign currency selection.

1/25/2019 3:59 PM Andy
- Fixed $sku_code_list index issue.

3/15/2019 2:18 PM Andy
- Enhanced to load eWallet payment type into payment type selection.

2/18/2020 3:13 PM Andy
- Fixed to only replace "_" to " " for credit card payment.

2/24/2020 4:05 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

10/8/2020 11:01 AM William
- Enhanced to check "is_tax_registered" or "is_gst", when got tax.
*/
include("include/common.php");
if(!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if(!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
include_once('pos_report.include.php');

$maintenance->check(92);
ini_set('memory_limit', '512M');
set_time_limit(0);

class TRAN_DETAILS_ITEM_LISTING extends MODULE {
    function __construct($title) {
		global $con_multi,$appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
        $this->init_selection();
        parent::__construct($title);
    }
    
    function init_selection() {
        global $smarty, $con, $pos_config, $sessioninfo, $con_multi;
        $this->counter = load_counter();
        
		if(BRANCH_CODE == "HQ")	$bid_list = load_branches();
		else $bid_list[$sessioninfo['branch_id']] = BRANCH_CODE;
        
        //transaction status and type
        $transaction_status_list = array();
        $transaction_status_list = array();
        
        $transaction_status_list = array('0'=>'Cancelled', '1'=>'Valid', '2'=>'Pruned');
        $transaction_type_list = array('0'=>'Non-member','1'=>'Member');
        $smarty->assign('transaction_status', $transaction_status_list);
        $smarty->assign('transaction_type', $transaction_type_list);
		
		$payment_type = array();
		foreach($bid_list as $bid=>$bcode) {
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
		
		if($payment_type) {
			$payment_type['Cash'] = "Cash";
			$pos_config['payment_type'] = $payment_type;
			asort($pos_config['payment_type']);
		}
		$smarty->assign('pos_config', $pos_config);
    }
    
    function _default() {
        global $smarty;
        
        $form = $_REQUEST;
        
        if(!isset($form['date_from']) && !isset($form['date_to'])) {
			$form['date_from'] = date('Y-m-d',strtotime('-7 day',time()));
			$form['date_to'] = date('Y-m-d');
		}elseif(strtotime($form['date_to']) > strtotime("+ 30 day", strtotime($form['date_from']))){
			$form['date_to'] = date('Y-m-d',strtotime('+30 day',strtotime($form['date_from'])));
		}
        
        if(!isset($form['from_time_Hour']) && !isset($form['to_time_Hour'])) {
            $form['from_time'] = strtotime('0000');
            $form['to_time'] = strtotime('2359');
        }else {
            $form['from_time'] = strtotime($form['from_time_Hour'].$form['from_time_Minute']);
            $form['to_time'] = strtotime($form['to_time_Hour'].$form['to_time_Minute']);
        }
        
        $this->form = $form;
		
		if($form['form_submit']) {
			$this->generate_report();
			if ($form['export_excel']) {
				include_once("include/excelwriter.php");
				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=tran_details_item_listing_'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		}
        $smarty->assign('form',$this->form);
        $this->display();
    }
	
	function generate_report() {
		global $con, $smarty, $sessioninfo, $config, $con_multi;
		
		if(strtotime($this->form['date_from']) > strtotime($this->form['date_to'])) {
			$err[] = "The To Date must be greater than the From date.";
			$smarty->assign("err", $err);
			return;
		}
		
		$data = $summary = $filter = array();
		$tran_status = $tran_type = "";
		
		$filter[] = "pos.date between ".ms($this->form['date_from'])." and ".ms($this->form['date_to']);
		$filter[] = "(pos_payment.type not in ('Rounding', 'Mix & Match Total Disc') or pos_payment.type is null)";
		
		list($branch_id, $counter_id) = explode("|", $this->form['counter']);
		$filter[] = "pos.branch_id=".mi($branch_id);
		if($counter_id != 'all')	$filter[] = "pos.counter_id=".mi($counter_id);
		
		if($this->form['receipt_num'] != "") $filter[] = "pos.receipt_no=".ms($this->form['receipt_num']);
		
		if($this->form['time_filter'] != "") {
			$time_from = $this->form['from_time_Hour'].":".$this->form['from_time_Minute'];
			$time_to = $this->form['to_time_Hour'].":".$this->form['to_time_Minute'];
			$filter[] = "time(pos.pos_time) between ".ms($time_from)." and ".ms($time_to);
		}
		
		if($this->form['payment_type'] != "") {
			if($this->form['payment_type'] == 'Credit Cards') {
				foreach($pos_config['credit_card'] as $p) {
					$cc[] = ms($p);
				}
				$filter[] = "pos_payment.type in (".join(',',$cc).")";
			}elseif($this->form['payment_type']=='Foreign Currency'){
				foreach($config['foreign_currency'] as $curr_code=>$curr_info){
					$cc[] = ms($curr_code);
				}
				$filter[] = "pos_payment.type in (".join(',',$cc).")";
			}else{
				$filter[] = "pos_payment.type=".ms($this->form['payment_type']);
			}
		}
		
		if($this->form['tran_status'] != "") {
			switch($this->form['tran_status']) {
				case 0:		//cancelled
					$filter[] = "pos.prune_status=0 and pos.cancel_status=1";
					$tran_status = "Cancelled";
					break;
				case 1:		//valid 
					$filter[] = "pos.cancel_status=0";
					$tran_status = "Valid";
					break;
				case 2:		//pruned
					$filter[] = "pos.prune_status=1 and pos.cancel_status=1";
					$tran_status = "Pruned";
					break;
			}
		}else	$tran_status = "All";
		
		if($this->form['tran_type'] != "") {
			switch($this->form['tran_type']) {
				case 0:		//non-member
					$filter[] = "(pos.member_no='0' or pos.member_no is null or pos.member_no='')";
					$tran_type = "Non-member";
					break;
				case 1:		//member
					$filter[] = "(pos.member_no<>'0' and pos.member_no is not null and pos.member_no<>'')";
					$tran_type = "Member";
					break;
			}
		}else	$tran_type = "All";

		$filter = join(" and ", $filter);
		
		if(isset($this->form['sku_code_list'])) {
			$sku_code_list = join(",", array_map("ms", $_REQUEST['sku_code_list']));
			
			// select sku item id list
			$con_multi->sql_query("select * from sku_items where sku_item_code in ($sku_code_list)") or die(mysql_error());
			while($r = $con_multi->sql_fetchassoc()){
				$sid_list[] = mi($r['id']);
				$group_item[] = $r;
			}
			$sid_list = join(', ', $sid_list);
			$con_multi->sql_freeresult();
		}
		
		$sql = "select pos.id, pos.counter_id, pos.receipt_no, pos.date, pos.receipt_ref_no, pos.service_charges, pos.prune_status, pos.cancel_status, counter_settings.network_name, pos.is_gst, pos.is_tax_registered
				from pos
				left join pos_payment on pos.branch_id=pos_payment.branch_id and pos.counter_id=pos_payment.counter_id and pos.id=pos_payment.pos_id and pos.date=pos_payment.date and pos_payment.adjust!=1
				left join counter_settings on pos.counter_id=counter_settings.id and pos.branch_id=counter_settings.branch_id
				left join user on pos.cashier_id=user.id
				where $filter
				group by pos.branch_id, pos.id, pos.counter_id, pos.date
				order by pos.date, pos.pos_time";
		
		$query = $con_multi->sql_query($sql);
		while ($q = $con_multi->sql_fetchassoc($query)) {
			$item_filter = array();
			
			$item_filter[] = "branch_id=".mi($branch_id);
			$item_filter[] = "counter_id=".mi($q['counter_id']);
			$item_filter[] = "date=".ms($q['date']);
			$item_filter[] = "pos_id=".mi(intval($q['id']));
			
			$item_filter = join(' and ', $item_filter);
				
			//filter receipt which match with sku id list
			if($sid_list) {
				$check_sql = $con_multi->sql_query("select count(*) as c
											 from pos_items
											 left join sku_items on pos_items.sku_item_id=sku_items.id
											 where $item_filter and sku_items.id in ($sid_list)");
				$count = $con_multi->sql_fetchassoc($check_query);
				$con_multi->sql_freeresult($check_query);
				if($count['c'] == 0)	continue;
			}
			
			//construct receipt number and receipt status
			$data[$q['date']][$q['receipt_ref_no']]['header']['branch_id'] = $branch_id;
			$data[$q['date']][$q['receipt_ref_no']]['header']['counter_id'] = $q['counter_id'];
			$data[$q['date']][$q['receipt_ref_no']]['header']['receipt_no'] = $q['receipt_no'];
			$data[$q['date']][$q['receipt_ref_no']]['header']['network_name'] = $q['network_name'];
			
			if($q['prune_status'] == 1 && $q['cancel_status'] == 1)
				$data[$q['date']][$q['receipt_ref_no']]['header']['status'] = "(Pruned)";
			elseif ($q['prune_status'] == 0 && $q['cancel_status'] == 1)
				$data[$q['date']][$q['receipt_ref_no']]['header']['status'] = "(Cancelled)";
			
			// retrieve transaction item details
			$item_sql = "select pos_items.branch_id, pos_items.counter_id, sku_items.sku_item_code, sku_items.mcode, sku_items.artno, sku_items.description, uom.code as uom, pos_items.qty, pos_items.price as amt, pos_items.discount, pos_items.discount2, pos_items.tax_indicator
						from pos_items
						left join sku_items on pos_items.sku_item_id=sku_items.id
						left join uom on uom.id=sku_items.packing_uom_id
						where $item_filter";
				
			$item_query = $con_multi->sql_query($item_sql);
			while ($iq = $con_multi->sql_fetchassoc($item_query)) {
				$data[$q['date']][$q['receipt_ref_no']]['items'][] = $iq;
				$data[$q['date']][$q['receipt_ref_no']]['total']['qty'] += $iq['qty'];
				$data[$q['date']][$q['receipt_ref_no']]['total']['amt'] += $iq['amt'];
				$data[$q['date']][$q['receipt_ref_no']]['total']['discount'] += $iq['discount'];
				$data[$q['date']][$q['receipt_ref_no']]['total']['total_amt'] += ($iq['amt'] - $iq['discount']);
				
				$summary['item'][$iq['sku_item_code']]['qty'] += $iq['qty'];
				$summary['item'][$iq['sku_item_code']]['amt'] += $iq['amt'];
				$summary['item'][$iq['sku_item_code']]['uom'] = $iq['uom'];
				$summary['total']['qty'] += $iq['qty'];
				$summary['total']['amt'] += $iq['amt'];
				$summary['total']['item_disc'] += $iq['discount'];
			}
			$con_multi->sql_freeresult($item_query);
			
			// retrieve transaction discount and rounding details
			$discount = $rounding = 0;
			$pos_pay_sql = "select type, amount,
							case when type = 'Mix & Match Total Disc' then 1 when type = 'Discount' then 2 else 3 end as sequence
							from pos_payment
							where $item_filter and adjust <> 1 
							order by sequence, id";
			
			$pos_pay_query = $con_multi->sql_query($pos_pay_sql);
			while ($pq = $con_multi->sql_fetchassoc($pos_pay_query)) {
				if($pq['type'] == 'Discount' || $pq['type'] == 'Mix & Match Total Disc') {
					$data[$q['date']][$q['receipt_ref_no']]['discount_list'][] = $pq;
					$discount += $pq['amount'];
					if($pq['type'] == 'Discount')	$type = "disc";
					else	$type = "mix_match_disc";
					$summary['total'][$type] += $pq['amount'];
				}
				elseif($pq['type'] == 'Rounding'){
					$data[$q['date']][$q['receipt_ref_no']]['rounding'] = $pq;
					$rounding = $pq['amount'];
					$summary['total']['rounding'] += $pq['amount'];
				}else continue;
			}
			$con_multi->sql_freeresult($pos_pay_query);
			
			//reteive transaction details service charge
			if($q['service_charges'] > 0){
				$data[$q['date']][$q['receipt_ref_no']]['service_charge'] = $q['service_charges'];
				$summary['total']['service_charge'] += $q['service_charges'];
			}
			
			if($q['is_gst'] || $q['is_tax_registered']) {
				if($q['is_gst']){
					$tax = $q['is_gst'];
				}elseif($q['is_tax_registered']){
					$tax = $q['is_tax_registered'];
				}
				$data[$q['date']][$q['receipt_ref_no']]['is_under_gst'] = $tax;
				$smarty->assign("is_under_gst", $tax);
			}
			
			$data[$q['date']][$q['receipt_ref_no']]['total']['total_amt'] += (-$discount + $rounding + $q['service_charges']);			
		}
		$con_multi->sql_freeresult($query);
		if($summary['item']) ksort($summary['item']);
		
		//contruct report title
		$report_title = array();
		$report_title[] = "Date: From ".$this->form['date_from']." to ".$this->form['date_to'];
		if($time_from != "" && $time_to  != "")	$report_title[] = "Time: From $time_from to $time_to";
		foreach ($this->counter as $ci) {
			if( $ci['branch_id'] == $branch_id){
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
		$report_title[] = "Payment Type: ".($this->form['payment_type']?$this->form['payment_type']:"All");
		$report_title[] = "Transaction Status: $tran_status";
		$report_title[] = "Transaction Type: $tran_type";
		$report_title = join(" &nbsp;&nbsp;&nbsp; ", $report_title);
		//print_r($data);
		//print_r($summary);
		$smarty->assign("report_title", $report_title);
		$smarty->assign("group_item", $group_item);
		$smarty->assign("data", $data);
		$smarty->assign("summary", $summary);
	}
}
$TRAN_DETAILS_ITEM_LISTING = new TRAN_DETAILS_ITEM_LISTING('Transaction Details with Item Listing');
?>
