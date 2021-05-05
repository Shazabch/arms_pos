<?php
/*
4/13/2015 4:03 PM Andy
- Enhanced to have total amount inclusive gst.
- Enhanced to can select all branches.

4/21/2015 4:46 PM Andy
- Fix data not shown in report when there is no GST sales.

4/29/2015 4:43 PM Andy
- Enhanced to can export excel.
- Enhanced to can view item details.

4/30/2015 2:42 PM Andy
- Enhanced to sort item details by ARMS Code.

03-Mar-2016 15:08 Edwin
- Add features to showing "Rounding" & "Amt After Rounding" into "Counter Sales GST Report"

4/6/2016 10:08 AM Andy
- Fix wrong rounding amount.

11/24/2016 3:48 PM Andy
- Enhanced to show item details by receipt.
- Enhanced to able to export item details.

3/1/2017 11:56 AM Justin
- Enhanced to trigger deposit data.

4/11/2017 1:32 PM Justin
- Enhanced to have sum up for Goods Return Amt for "Sales Exclude Goods Return" checkbox.

4/12/2017 2:14 PM Justin
- Bug fixed on rounding is not sum up properly on total section.
- Bug fixed on the goods return amount should be positive instead of negative figures. 

2/25/2020 9:33 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
include_once('pos_report.include.php');

$maintenance->check(92);

ini_set('memory_limit', '512M');
set_time_limit(0);

class COUNTER_SALES_GST_REPORT extends Module{
	
	function __construct($title){
		global $con, $config, $smarty, $sessioninfo, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
	
		load_branches();
		
		if(!isset($_REQUEST['date_from'])&&!isset($_REQUEST['date_to'])){	// date not set, first time access
			$_REQUEST['date_from'] = date('Y-m-d',strtotime('-7 day',time()));
			$_REQUEST['date_to'] = date('Y-m-d');
		}elseif(strtotime($_REQUEST['date_to']) > strtotime("+ 30 day", strtotime($_REQUEST['date_from']))){	// max 30 days
			$_REQUEST['date_to'] = date('Y-m-d',strtotime('+30 day',strtotime($_REQUEST['date_from'])));
		}elseif(strtotime($_REQUEST['date_from']) > strtotime($_REQUEST['date_to'])){	// date from > date to
			$_REQUEST['date_from'] = $_REQUEST['date_to'];
		}
		
		parent::__construct($title);
	}
	
	function _default(){
		global $con, $config, $smarty, $sessioninfo;
		
		if($_REQUEST['load_data']){
			$this->load_data();
			if($_REQUEST['is_export']){
				include_once("include/excelwriter.php");
				log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Daily ".$this->PAGE_TITLE." To Excel");

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		}
		
		$this->display();
	}
	
	private function load_data(){
		global $con, $smarty, $sessioninfo, $con_multi;
		
		$bid_list = array();
		$branch_title = '';
		if(BRANCH_CODE == 'HQ'){
			$bid = mi($_REQUEST['branch_id']);
			if($bid){
				$bid_list[]= $bid;
				$branch_title = get_branch_code($bid);
			}else{
				$branches = $smarty->get_template_vars('branches');
				$bid_list = array_keys($branches);
				$branch_title = "All";
			}
		}else{
			$bid = mi($sessioninfo['branch_id']);
			$bid_list[]= $bid;
			$branch_title = get_branch_code($bid);
		}
		
		$date_from = $_REQUEST['date_from'];
		$date_to = $_REQUEST['date_to'];
		$show_by_tax_code = mi($_REQUEST['show_by_tax_code']);
		$sales_exclude_gr = mi($_REQUEST['sales_exclude_gr']);
		
		$err = array();
		if(count($bid_list)<=0)	$err[] = "Please select branch";
		if(!$date_from)	$err[] = "Please select date from";
		if(!$date_to)	$err[] = "Please select date to";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$report_title = array();
		$report_title[] = "Branch: ".$branch_title;
		$report_title[] = "Date: $date_from to $date_to";
		$report_title[] = "Show by Tax Code: ".(($show_by_tax_code) ? "Yes" : "No");
		$report_title[] = "Sales Exclude Goods Return: ".(($sales_exclude_gr) ? "Yes" : "No");
		
		$this->data = array();
		
		$extend_group_by = ", pi.tax_indicator";
		if($show_by_tax_code)	$extend_group_by = ", pi.tax_code";
		foreach($bid_list as $bid){
			$round_amt_dupl_check = array();
			$date_check = "";
			$filter = array();
			$filter[] = "p.branch_id=$bid";
			$filter[] = "p.date between ".ms($date_from)." and ".ms($date_to);
			$filter[] = "p.cancel_status=0";
			
			$filter = "where ".join(' and ', $filter);
			
			$sql = "select pi.date, p.counter_id, pi.pos_id, pi.tax_indicator, pi.tax_code, pi.tax_rate , sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) as before_tax_price, sum(pi.tax_amount) as tax_amount, sum(if(pi.qty < 0, pi.price-pi.discount, 0)) as goods_return_amt,
					(select pp.amount from pos_payment pp where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type='Rounding' and pp.adjust <> 1 limit 1) as rounding_amt 
					from pos_items pi
					join pos p on p.branch_id=pi.branch_id and p.counter_id=pi.counter_id and p.date=pi.date and p.id=pi.pos_id
					$filter
					group by pi.date $extend_group_by, pi.tax_rate, pi.counter_id, pi.pos_id
					order by pi.date";
			$q1 = $con_multi->sql_query($sql);
			
			while($r = $con_multi->sql_fetchassoc($q1)){
				$date = $r['date'];
				$r['tax_rate'] = round($r['tax_rate'], 2);
				$r['goods_return_amt'] = abs($r['goods_return_amt']);
				
				if($date_check != $date) {
					$round_amt_dupl_check = array();
					$date_check = $date;
				}
					
				$this->data['date_list'][$date] = $date;
				
				$tax_code = $show_by_tax_code ? $r['tax_code'] : $r['tax_indicator'];
				
				if($tax_code){
					$gst_key = $tax_code.'-'.$r['tax_rate'];
					$this->data['gst_list'][$gst_key]['tax_indicator'] = $tax_code;
					$this->data['gst_list'][$gst_key]['tax_rate'] = $r['tax_rate'];
				}else{
					$gst_key = 'non_gst';
					$this->data['got_non_gst'] = 1;
				}
				
				// by date
				//$this->data['data'][$date]['gst_list'][$gst_key]['tax_indicator'] = $r['tax_indicator'];
				//$this->data['data'][$date]['gst_list'][$gst_key]['tax_rate'] = $r['tax_rate'];
				$this->data['data'][$date]['gst_list'][$gst_key]['before_tax_price'] += round($r['before_tax_price'], 2);
				$this->data['data'][$date]['gst_list'][$gst_key]['tax_amount'] += round($r['tax_amount'], 2);
				$this->data['data'][$date]['gst_list'][$gst_key]['goods_return_amt'] += round($r['goods_return_amt'], 2);
				
				// date total
				$this->data['data'][$date]['total']['before_tax_price'] += round($r['before_tax_price'], 2);
				$this->data['data'][$date]['total']['tax_amount'] += round($r['tax_amount'], 2);
				$this->data['data'][$date]['total']['amt_included_gst'] = round($this->data['data'][$date]['total']['before_tax_price']+$this->data['data'][$date]['total']['tax_amount'], 2);
				$this->data['data'][$date]['total']['goods_return_amt'] += round($r['goods_return_amt'], 2);
				if(isset($r['rounding_amt']) && $round_amt_dupl_check[$r['counter_id']][$r['pos_id']] != $r['rounding_amt']){
					$this->data['data'][$date]['total']['rounding'] += round($r['rounding_amt'], 2);
				}
					
				$this->data['data'][$date]['total']['amt_after_rounding'] = $this->data['data'][$date]['total']['amt_included_gst']+$this->data['data'][$date]['total']['rounding'];
				
				// gst total
				$this->data['total']['gst_list'][$gst_key]['before_tax_price'] += round($r['before_tax_price'], 2);
				$this->data['total']['gst_list'][$gst_key]['tax_amount'] += round($r['tax_amount'], 2);
				$this->data['total']['gst_list'][$gst_key]['goods_return_amt'] += round($r['goods_return_amt'], 2);
				
				// total
				$this->data['total']['total']['before_tax_price'] += round($r['before_tax_price'], 2);
				$this->data['total']['total']['tax_amount'] += round($r['tax_amount'], 2);
				$this->data['total']['total']['amt_included_gst'] = round($this->data['total']['total']['before_tax_price']+$this->data['total']['total']['tax_amount'], 2);
				$this->data['total']['total']['goods_return_amt'] += round($r['goods_return_amt'], 2);
				if(isset($r['rounding_amt']) && $round_amt_dupl_check[$r['counter_id']][$r['pos_id']] != $r['rounding_amt']) {
					$this->data['total']['total']['rounding'] += round($r['rounding_amt'], 2);
					$round_amt_dupl_check[$r['counter_id']][$r['pos_id']] = $r['rounding_amt'];
				}
			}
			$con_multi->sql_freeresult($q1);
		
			// deposit
			$sql = "select pd.*
					from pos_deposit pd
					left join pos p on p.branch_id=pd.branch_id and p.date=pd.date and p.counter_id=pd.counter_id and p.id=pd.pos_id
					$filter 
					order by p.date";
	 
			$q1 = $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchassoc($q1)){
				$date = $r['date'];
				$net_deposit_amt = round($r['deposit_amount'], 2) - round($r['gst_amount'], 2);
				$gst_amt = round($r['gst_amount'], 2);	// gst amt
				$deposit_amt = round($r['deposit_amount'], 2);	// rcv amt
				$gst_info = unserialize($r['gst_info']);
				$gst_info['rate'] = round($gst_info['rate'], 2);
				
				if($date_check != $date) {
					$round_amt_dupl_check = array();
					$date_check = $date;
				}
					
				$this->data['date_list'][$date] = $date;
				
				$tax_code = $show_by_tax_code ? $gst_info['code'] : $gst_info['indicator_receipt'];
				
				if($tax_code){
					$gst_key = $tax_code.'-'.$gst_info['rate'];
					$this->data['gst_list'][$gst_key]['tax_indicator'] = $tax_code;
					$this->data['gst_list'][$gst_key]['tax_rate'] = $gst_info['rate'];
				}else{
					$gst_key = 'non_gst';
					$this->data['got_non_gst'] = 1;
				}
				
				// by date
				$this->data['data'][$date]['gst_list'][$gst_key]['before_tax_price'] += $net_deposit_amt;
				$this->data['data'][$date]['gst_list'][$gst_key]['tax_amount'] += $gst_amt;
				
				// date total
				$this->data['data'][$date]['total']['before_tax_price'] += $net_deposit_amt;
				$this->data['data'][$date]['total']['tax_amount'] += $gst_amt;
				$this->data['data'][$date]['total']['amt_included_gst'] += $deposit_amt;
				
				$this->data['data'][$date]['total']['amt_after_rounding'] = $this->data['data'][$date]['total']['amt_included_gst']+$this->data['data'][$date]['total']['rounding'];
				
				// gst total
				$this->data['total']['gst_list'][$gst_key]['before_tax_price'] += $net_deposit_amt;
				$this->data['total']['gst_list'][$gst_key]['tax_amount'] += $gst_amt;
				
				// total
				$this->data['total']['total']['before_tax_price'] += $net_deposit_amt;
				$this->data['total']['total']['tax_amount'] += $gst_amt;
				$this->data['total']['total']['amt_included_gst'] += $deposit_amt;
			}
			$con_multi->sql_freeresult($q1);
			
			// deposit used
			$sql = "select pd.deposit_amount, p.amount, p.amount_change, p.date, pd.gst_info, pd.gst_amount, p.receipt_ref_no
					from pos_deposit pd
					left join pos_deposit_status_history pdsh on pdsh.deposit_branch_id=pd.branch_id and pdsh.deposit_pos_date=pd.date and pdsh.deposit_counter_id=pd.counter_id and pdsh.deposit_pos_id=pd.pos_id
					left join pos p on p.branch_id=pdsh.branch_id and p.date=pdsh.pos_date and p.counter_id=pdsh.counter_id and p.id=pdsh.pos_id
					$filter and pdsh.type='USED'
					order by p.date";
			
			$q1 = $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchassoc($q1)){
				$date = $r['date'];
				$net_deposit_amt = (round($r['deposit_amount'], 2) - round($r['gst_amount'], 2)) * -1;
				$gst_amt = round($r['gst_amount'], 2) * -1;	// gst amt
				$deposit_amt = round($r['deposit_amount'], 2) * -1;	// rcv amt
				$gst_info = unserialize($r['gst_info']);
				$gst_info['rate'] = round($gst_info['rate'], 2);
				
				if($date_check != $date) {
					$round_amt_dupl_check = array();
					$date_check = $date;
				}
					
				$this->data['date_list'][$date] = $date;
				
				$tax_code = $show_by_tax_code ? $gst_info['code'] : $gst_info['indicator_receipt'];
				
				if($tax_code){
					$gst_key = $tax_code.'-'.$gst_info['rate'];
					$this->data['gst_list'][$gst_key]['tax_indicator'] = $tax_code;
					$this->data['gst_list'][$gst_key]['tax_rate'] = $gst_info['rate'];
				}else{
					$gst_key = 'non_gst';
					$this->data['got_non_gst'] = 1;
				}
				
				// by date
				$this->data['data'][$date]['gst_list'][$gst_key]['before_tax_price'] += $net_deposit_amt;
				$this->data['data'][$date]['gst_list'][$gst_key]['tax_amount'] += $gst_amt;
				
				// date total
				$this->data['data'][$date]['total']['before_tax_price'] += $net_deposit_amt;
				$this->data['data'][$date]['total']['tax_amount'] += $gst_amt;
				$this->data['data'][$date]['total']['amt_included_gst'] += $deposit_amt;
				
				$this->data['data'][$date]['total']['amt_after_rounding'] = $this->data['data'][$date]['total']['amt_included_gst']+$this->data['data'][$date]['total']['rounding'];
				
				// gst total
				$this->data['total']['gst_list'][$gst_key]['before_tax_price'] += $net_deposit_amt;
				$this->data['total']['gst_list'][$gst_key]['tax_amount'] += $gst_amt;
				
				// total
				$this->data['total']['total']['before_tax_price'] += $net_deposit_amt;
				$this->data['total']['total']['tax_amount'] += $gst_amt;
				$this->data['total']['total']['amt_included_gst'] += $deposit_amt;
			}
			$this->data['total']['total']['amt_after_rounding'] = $this->data['total']['total']['amt_included_gst']+$this->data['total']['total']['rounding'];

			$con_multi->sql_freeresult($q1);
		}
		
		if($this->data){
			// sort the date to asc
			if($this->data['date_list'])
				asort($this->data['date_list']);
			// sort gst list
			if($this->data['gst_list'])
				ksort($this->data['gst_list']);
		}
		
		
		//print_r($this->data);
		$smarty->assign('data', $this->data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
	
	function show_items(){
		global $con, $smarty, $sessioninfo, $con_multi;
		//print_r($_REQUEST);
		
		if(isset($_REQUEST['output_excel'])){
			include_once("include/excelwriter.php");
			log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title."  - by Items");

			Header('Content-Type: application/msexcel');
			Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
			print ExcelWriter::GetHeader();
			$smarty->assign('no_header_footer', 1);
		}
				
		$bid_list = array();
		$branch_title = '';
		if(BRANCH_CODE == 'HQ'){
			$bid = mi($_REQUEST['branch_id']);
			if($bid){
				$bid_list[]= $bid;
				$branch_title = get_branch_code($bid);
			}else{
				$branches = $smarty->get_template_vars('branches');
				$bid_list = array_keys($branches);
				$branch_title = "All";
			}
		}else{
			$bid = mi($sessioninfo['branch_id']);
			$bid_list[]= $bid;
			$branch_title = get_branch_code($bid);
		}
		
		$date = trim($_REQUEST['date']);
		$gst_indicator = trim($_REQUEST['gst_indicator']);
		$show_by_tax_code = mi($_REQUEST['show_by_tax_code']);
		$show_non_gst = $gst_indicator == 'non_gst' ? 1 : 0;
		
		if(!$date)	die("Invalid Date.");
		if(!$gst_indicator)	die("Invalid GST Type.");
		
		
		$report_title = array();
		$report_title[] = "Branch: ".$branch_title;
		$report_title[] = "Date: $date";
		$report_title[] = "GST Type: ".($show_non_gst ? 'Non GST' : $gst_indicator);
		
		$this->data = $this->deposit_data = array();
		foreach($bid_list as $bid){
			$filter = array();
			$filter[] = "p.branch_id=$bid";
			$filter[] = "p.date=".ms($date);
			$filter[] = "p.cancel_status=0";
			
			if($show_non_gst){
				$filter[] = "pi.tax_indicator=''";
			}else{
				if($show_by_tax_code){
					$filter[] = "pi.tax_code=".ms($gst_indicator);
				}else{
					$filter[] = "pi.tax_indicator=".ms($gst_indicator);
				}
			}
			
			
			$filter = "where ".join(' and ', $filter);
			
			$sql = "select p.receipt_ref_no, pi.sku_item_id,si.mcode,si.sku_item_code,si.artno,si.description,
			pi.tax_indicator, pi.tax_code, pi.tax_rate , (pi.price-pi.discount-pi.discount2-pi.tax_amount) as before_tax_price, pi.tax_amount, pi.qty, pi.id
			from pos_items pi
			join pos p on p.branch_id=pi.branch_id and p.counter_id=pi.counter_id and p.date=pi.date and p.id=pi.pos_id
			join sku_items si on si.id=pi.sku_item_id
			$filter			
			order by p.receipt_ref_no, pi.item_id";
			//print $sql;
			$q1 = $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchassoc($q1)){
				$sid = mi($r['sku_item_id']);
				
				// sku items info
				if(!isset($this->data['si_info'][$sid])){
					$this->data['si_info'][$sid]['id'] = $sid;
					$this->data['si_info'][$sid]['mcode'] = $r['mcode'];
					$this->data['si_info'][$sid]['artno'] = $r['artno'];
					$this->data['si_info'][$sid]['sku_item_code'] = $r['sku_item_code'];
					$this->data['si_info'][$sid]['description'] = $r['description'];
				}
				
				$this->data['data'][$r['receipt_ref_no']][$r['id']]['sid'] = $sid;
				$this->data['data'][$r['receipt_ref_no']][$r['id']]['qty'] = $r['qty'];
				$this->data['data'][$r['receipt_ref_no']][$r['id']]['before_tax_price'] = round($r['before_tax_price'],2);
				$this->data['data'][$r['receipt_ref_no']][$r['id']]['tax_amount'] = round($r['tax_amount'],2);
				$this->data['data'][$r['receipt_ref_no']][$r['id']]['amt_included_gst'] = round($r['before_tax_price'] + $r['tax_amount'],2);
				
				
				$this->data['total']['qty'] += $r['qty'];
				$this->data['total']['before_tax_price'] += round($r['before_tax_price'],2);
				$this->data['total']['tax_amount'] += round($r['tax_amount'],2);
				$this->data['total']['amt_included_gst'] += round($r['before_tax_price'] + $r['tax_amount'],2);
			}
			$con_multi->sql_freeresult($q1);
			
			$filter = array();
			$filter[] = "p.branch_id=$bid";
			$filter[] = "p.date=".ms($date);
			$filter[] = "p.cancel_status=0";
			
			$filter = "where ".join(' and ', $filter);
			
			// deposit received
			$sql = "select pd.*, p.receipt_ref_no
					from pos_deposit pd
					left join pos p on p.branch_id=pd.branch_id and p.date=pd.date and p.counter_id=pd.counter_id and p.id=pd.pos_id
					$filter 
					order by p.receipt_ref_no";
			
			$q1 = $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchassoc($q1)){
				$gst_info = unserialize($r['gst_info']);
				$net_deposit_amt = round($r['deposit_amount'], 2) - round($r['gst_amount'], 2);
				$gst_amt = round($r['gst_amount'], 2);	// gst amt
				$deposit_amt = round($r['deposit_amount'], 2);	// rcv amt
				
				// tax indicator filtering
				if($show_non_gst){
					if($gst_info['indicator_receipt'] != '') continue;
				}else{
					if($show_by_tax_code){
						if($gst_info['code'] != $gst_indicator) continue;
					}else{
						if($gst_info['indicator_receipt'] != $gst_indicator) continue;
					}
				}
				
				/*$sid = mi($r['sku_item_id']);
				
				// sku items info
				if(!isset($this->data['si_info'][$sid])){
					$this->data['si_info'][$sid]['id'] = $sid;
					$this->data['si_info'][$sid]['mcode'] = $r['mcode'];
					$this->data['si_info'][$sid]['artno'] = $r['artno'];
					$this->data['si_info'][$sid]['sku_item_code'] = $r['sku_item_code'];
					$this->data['si_info'][$sid]['description'] = $r['description'];
				}*/
				
				//$this->data['data'][$r['receipt_ref_no']][$r['id']]['sid'] = $sid;
				//$this->data['data'][$r['receipt_ref_no']][$r['id']]['qty'] = $r['qty'];
				$this->deposit_data['data'][$r['receipt_ref_no']]['status'] = "Received";
				$this->deposit_data['data'][$r['receipt_ref_no']]['before_tax_price'] = $net_deposit_amt;
				$this->deposit_data['data'][$r['receipt_ref_no']]['tax_amount'] = $gst_amt;
				$this->deposit_data['data'][$r['receipt_ref_no']]['amt_included_gst'] = $deposit_amt;
				
				
				//$this->data['total']['qty'] += $r['qty'];
				$this->deposit_data['total']['before_tax_price'] += $net_deposit_amt;
				$this->deposit_data['total']['tax_amount'] += $gst_amt;
				$this->deposit_data['total']['amt_included_gst'] += $deposit_amt;
			}
			$con_multi->sql_freeresult($q1);

			// deposit used
			$sql = "select pd.deposit_amount, p.amount, p.amount_change, p.date, pd.gst_info, pd.gst_amount, p.receipt_ref_no
					from pos_deposit pd
					left join pos_deposit_status_history pdsh on pdsh.deposit_branch_id=pd.branch_id and pdsh.deposit_pos_date=pd.date and pdsh.deposit_counter_id=pd.counter_id and pdsh.deposit_pos_id=pd.pos_id
					left join pos p on p.branch_id=pdsh.branch_id and p.date=pdsh.pos_date and p.counter_id=pdsh.counter_id and p.id=pdsh.pos_id
					$filter and pdsh.type='USED'
					order by p.receipt_ref_no";

			$q1 = $con_multi->sql_query($sql);
			
			while($r = $con_multi->sql_fetchassoc($q1)){
				$gst_info = unserialize($r['gst_info']);
				$net_deposit_amt = (round($r['deposit_amount'], 2) - round($r['gst_amount'], 2)) * -1;
				$gst_amt = round($r['gst_amount'], 2) * -1;	// gst amt
				$deposit_amt = round($r['deposit_amount'], 2) * -1;	// rcv amt
				
				// tax indicator filtering
				if($show_non_gst){
					if($gst_info['indicator_receipt'] != '') continue;
				}else{
					if($show_by_tax_code){
						if($gst_info['code'] != $gst_indicator) continue;
					}else{
						if($gst_info['indicator_receipt'] != $gst_indicator) continue;
					}
				}
				
				$this->deposit_data['data'][$r['receipt_ref_no']]['status'] = "Used";
				$this->deposit_data['data'][$r['receipt_ref_no']]['before_tax_price'] += $net_deposit_amt;
				$this->deposit_data['data'][$r['receipt_ref_no']]['tax_amount'] += $gst_amt;
				$this->deposit_data['data'][$r['receipt_ref_no']]['amt_included_gst'] += $deposit_amt;
				
				
				//$this->data['total']['qty'] += $r['qty'];
				$this->deposit_data['total']['before_tax_price'] += $net_deposit_amt;
				$this->deposit_data['total']['tax_amount'] += $gst_amt;
				$this->deposit_data['total']['amt_included_gst'] += $deposit_amt;
			}
			$con_multi->sql_freeresult($q1);
		}
		
		
		if($this->data['si_info']){
			uasort($this->data['si_info'], array($this, "sort_item"));
		}
		//print_r($this->data);
		$smarty->assign('data', $this->data);
		$smarty->assign('deposit_data', $this->deposit_data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		
		$this->display('pos_report.counter_sales_gst_report.show_items.tpl');
	}
	
	private function sort_item($a, $b){
		if($a['sku_item_code'] == $b['sku_item_code'])	return 0;
		return $a['sku_item_code'] > $b['sku_item_code'] ? 1 : -1;
	}
}

$COUNTER_SALES_GST_REPORT = new COUNTER_SALES_GST_REPORT('Counter Sales GST Report');