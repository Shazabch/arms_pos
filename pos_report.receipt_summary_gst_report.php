<?php
/*
5/5/2015 4:43 PM Andy
- Enhanced to show non-gst item.

5/11/2015 9:49 AM Andy
- Fix Non GST problem.

3/1/2017 11:56 AM Justin
- Enhanced to trigger deposit data.

2/25/2020 9:44 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
include_once('pos_report.include.php');

$maintenance->check(92);

ini_set('memory_limit', '512M');
set_time_limit(0);

class RECEIPT_SUMMARY_GST_REPORT extends Module{
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
		
		$this->data = array();
		
		// loop for each branch
		foreach($bid_list as $bid){
			$filter = array();
			$filter[] = "p.branch_id=$bid";
			$filter[] = "p.date between ".ms($date_from)." and ".ms($date_to);
			$filter[] = "p.cancel_status=0";
			
			$filter = "where ".join(' and ', $filter);
			
			$sql = "select p.branch_id,p.counter_id,p.date,p.id as pos_id,p.receipt_no,p.receipt_ref_no, pi.tax_code, pi.tax_rate, 
					sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) as before_tax_price, sum(pi.tax_amount) as tax_amount,
					(select pp.amount
					from pos_payment pp
					where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type='Rounding' and pp.adjust <> 1 
					limit 1) as rounding_amt,
					(select count(*)
					from pos_payment pp
					where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type='Deposit' and pp.adjust <> 1 
					limit 1) as have_deposit
					from pos_items pi
					join pos p on p.branch_id=pi.branch_id and p.counter_id=pi.counter_id and p.date=pi.date and p.id=pi.pos_id
					$filter
					group by p.branch_id,p.counter_id,p.date,p.id,pi.tax_code,pi.tax_rate
					order by p.date, p.branch_id, p.counter_id, p.id";
			
			//print "$sql<br>";
			
			$q1 = $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchassoc($q1)){
				$is_new_receipt = false;
				$tax_code = trim($r['tax_code']);
				$date = $r['date'];
				$receipt_ref_no = trim($r['receipt_ref_no']);
				
				if($tax_code){
					$gst_key = $tax_code.'-'.$r['tax_rate'];
					// gst list
					$this->data['gst_list'][$gst_key]['tax_code'] = $tax_code;
					$this->data['gst_list'][$gst_key]['tax_rate'] = $r['tax_rate'];
				}else{
					$gst_key = 'non_gst';
					$this->data['got_non_gst'] = 1;
				}
				
				// date list
				$this->data['date_list'][$date] = $date;
				
				// item
				$item = array();
				$item['receipt_no'] = $r['receipt_no'];
				$item['receipt_ref_no'] = $receipt_ref_no;
				$item['before_tax_price'] = round($r['before_tax_price'],2) - $used_net_deposit_amt;
				$item['tax_amount'] = round($r['tax_amount'],2) - $used_deposit_gst_amt;
				$item['amt_included_gst'] = round($r['before_tax_price']+$r['tax_amount'],2) - $used_deposit_amt;
				
				// item row
				$this->data['data']['gst_list'][$gst_key]['date_list'][$date][$receipt_ref_no] = $item;
				
				// item total
				$this->data['data']['gst_list'][$gst_key]['total']['before_tax_price'] += $item['before_tax_price'];
				$this->data['data']['gst_list'][$gst_key]['total']['tax_amount'] += $item['tax_amount'];
				$this->data['data']['gst_list'][$gst_key]['total']['amt_included_gst'] += $item['amt_included_gst'];
				
				// total by receipt
				if(!isset($this->data['total']['date_list'][$date][$receipt_ref_no])){
					$this->data['total']['date_list'][$date][$receipt_ref_no]['receipt_no'] = $r['receipt_no'];
					$this->data['total']['date_list'][$date][$receipt_ref_no]['receipt_ref_no'] = $r['receipt_ref_no'];
					$is_new_receipt = true;
				}
				$this->data['total']['date_list'][$date][$receipt_ref_no]['before_tax_price'] += $item['before_tax_price'];
				$this->data['total']['date_list'][$date][$receipt_ref_no]['tax_amount'] += $item['tax_amount'];
				$this->data['total']['date_list'][$date][$receipt_ref_no]['amt_included_gst'] += $item['amt_included_gst'];
				
				if($is_new_receipt && $r['rounding_amt']){
					$this->data['total']['date_list'][$date][$receipt_ref_no]['rounding_amt'] += round($r['rounding_amt'], 2);
				}
				$this->data['total']['date_list'][$date][$receipt_ref_no]['amt_collected'] = round($this->data['total']['date_list'][$date][$receipt_ref_no]['amt_included_gst']+$this->data['total']['date_list'][$date][$receipt_ref_no]['rounding_amt'], 2);
				
				// receipt total
				$this->data['total']['total']['before_tax_price'] += $item['before_tax_price'];
				$this->data['total']['total']['tax_amount'] += $item['tax_amount'];
				$this->data['total']['total']['amt_included_gst'] += $item['amt_included_gst'];
				if($is_new_receipt && $r['rounding_amt']){
					$this->data['total']['total']['rounding_amt'] += round($r['rounding_amt'], 2);
				}
				
				// total by gst
				$this->data['total']['gst_list'][$gst_key]['before_tax_price'] += $item['before_tax_price'];
				$this->data['total']['gst_list'][$gst_key]['tax_amount'] += $item['tax_amount'];
				$this->data['total']['gst_list'][$gst_key]['amt_included_gst'] += $item['amt_included_gst'];
				
				if($is_new_receipt){
					$this->data['total']['rounding_amt'] += round($r['rounding_amt'], 2);
				}
			}
			$con_multi->sql_freeresult($q1);
			
			// deposit received
			$sql = "select pd.*, p.receipt_no, p.receipt_ref_no
					from pos_deposit pd
					left join pos p on p.branch_id=pd.branch_id and p.date=pd.date and p.counter_id=pd.counter_id and p.id=pd.pos_id
					$filter 
					order by p.date";
	 
			$q1 = $con_multi->sql_query($sql);
			$last_date = array();
			while($r = $con_multi->sql_fetchassoc($q1)){
				$date = $r['date'];
				$net_deposit_amt = round($r['deposit_amount'], 2) - round($r['gst_amount'], 2);
				$gst_amt = round($r['gst_amount'], 2);	// gst amt
				$deposit_amt = round($r['deposit_amount'], 2);	// rcv amt
				$gst_info = unserialize($r['gst_info']);
				$gst_info['rate'] = round($gst_info['rate'], 2);
				$tax_code = trim($gst_info['code']);
				$receipt_ref_no = trim($r['receipt_ref_no']);
				
				if($tax_code){
					$gst_key = $tax_code.'-'.$gst_info['rate'];
					// gst list
					$this->data['gst_list'][$gst_key]['tax_code'] = $tax_code;
					$this->data['gst_list'][$gst_key]['tax_rate'] = $gst_info['rate'];
				}else{
					$gst_key = 'non_gst';
					$this->data['got_non_gst'] = 1;
				}
				
				// date list
				$this->data['date_list'][$date] = $date;
				
				// item
				$item = array();
				$item['receipt_no'] = $r['receipt_no'];
				$item['receipt_ref_no'] = $receipt_ref_no;
				$item['before_tax_price'] = $net_deposit_amt;
				$item['tax_amount'] = $gst_amt;
				$item['amt_included_gst'] = $deposit_amt;
				
				// item row
				$this->data['data']['gst_list'][$gst_key]['date_list'][$date][$receipt_ref_no] = $item;
				
				// item total
				$this->data['data']['gst_list'][$gst_key]['total']['before_tax_price'] += $item['before_tax_price'];
				$this->data['data']['gst_list'][$gst_key]['total']['tax_amount'] += $item['tax_amount'];
				$this->data['data']['gst_list'][$gst_key]['total']['amt_included_gst'] += $item['amt_included_gst'];
				
				// total by receipt
				if(!isset($this->data['total']['date_list'][$date][$receipt_ref_no])){
					$this->data['total']['date_list'][$date][$receipt_ref_no]['receipt_no'] = $r['receipt_no'];
					$this->data['total']['date_list'][$date][$receipt_ref_no]['receipt_ref_no'] = $r['receipt_ref_no'];
					$is_new_receipt = true;
				}
				$this->data['total']['date_list'][$date][$receipt_ref_no]['before_tax_price'] += $item['before_tax_price'];
				$this->data['total']['date_list'][$date][$receipt_ref_no]['tax_amount'] += $item['tax_amount'];
				$this->data['total']['date_list'][$date][$receipt_ref_no]['amt_included_gst'] += $item['amt_included_gst'];
				
				$this->data['total']['date_list'][$date][$receipt_ref_no]['amt_collected'] = round($this->data['total']['date_list'][$date][$receipt_ref_no]['amt_included_gst'], 2);
				
				// receipt total
				$this->data['total']['total']['before_tax_price'] += $item['before_tax_price'];
				$this->data['total']['total']['tax_amount'] += $item['tax_amount'];
				$this->data['total']['total']['amt_included_gst'] += $item['amt_included_gst'];
				
				// total by gst
				$this->data['total']['gst_list'][$gst_key]['before_tax_price'] += $item['before_tax_price'];
				$this->data['total']['gst_list'][$gst_key]['tax_amount'] += $item['tax_amount'];
				$this->data['total']['gst_list'][$gst_key]['amt_included_gst'] += $item['amt_included_gst'];
				
				if($last_date[$gst_key] != $date) ksort($this->data['data']['gst_list'][$gst_key]['date_list'][$date]);
				
				$last_date[$gst_key] = $date;
			}
			$con_multi->sql_freeresult($q1);

			// deposit used
			$sql = "select pd.deposit_amount, p.amount, p.amount_change, p.date, pd.gst_info, pd.gst_amount, p.receipt_ref_no, p.receipt_no
					from pos_deposit pd
					left join pos_deposit_status_history pdsh on pdsh.deposit_branch_id=pd.branch_id and pdsh.deposit_pos_date=pd.date and pdsh.deposit_counter_id=pd.counter_id and pdsh.deposit_pos_id=pd.pos_id
					left join pos p on p.branch_id=pdsh.branch_id and p.date=pdsh.pos_date and p.counter_id=pdsh.counter_id and p.id=pdsh.pos_id
					$filter and pdsh.type='USED'
					order by p.date";
			
			$q1 = $con_multi->sql_query($sql);
			$last_date = array();
			while($r = $con_multi->sql_fetchassoc($q1)){
				$date = $r['date'];
				$net_deposit_amt = (round($r['deposit_amount'], 2) - round($r['gst_amount'], 2)) * -1;
				$gst_amt = round($r['gst_amount'], 2) * -1;	// gst amt
				$deposit_amt = round($r['deposit_amount'], 2) * -1;	// rcv amt
				$gst_info = unserialize($r['gst_info']);
				$gst_info['rate'] = round($gst_info['rate'], 2);
				$tax_code = trim($gst_info['code']);
				$receipt_ref_no = trim($r['receipt_ref_no']);
				
				if($tax_code){
					$gst_key = $tax_code.'-'.$gst_info['rate'];
					// gst list
					$this->data['gst_list'][$gst_key]['tax_code'] = $tax_code;
					$this->data['gst_list'][$gst_key]['tax_rate'] = $gst_info['rate'];
				}else{
					$gst_key = 'non_gst';
					$this->data['got_non_gst'] = 1;
				}
				
				// date list
				$this->data['date_list'][$date] = $date;
				
				// item
				$item = array();
				$item['receipt_no'] = $r['receipt_no'];
				$item['receipt_ref_no'] = $receipt_ref_no;
				$item['before_tax_price'] = $net_deposit_amt;
				$item['tax_amount'] = $gst_amt;
				$item['amt_included_gst'] = $deposit_amt;
				
				// item row
				if(isset($this->data['data']['gst_list'][$gst_key]['date_list'][$date][$receipt_ref_no])){
					$this->data['data']['gst_list'][$gst_key]['date_list'][$date][$receipt_ref_no]['before_tax_price'] += $item['before_tax_price'];
					$this->data['data']['gst_list'][$gst_key]['date_list'][$date][$receipt_ref_no]['tax_amount'] += $item['tax_amount'];
					$this->data['data']['gst_list'][$gst_key]['date_list'][$date][$receipt_ref_no]['amt_included_gst'] += $item['amt_included_gst'];
				}else $this->data['data']['gst_list'][$gst_key]['date_list'][$date][$receipt_ref_no] = $item;
				
				// item total
				$this->data['data']['gst_list'][$gst_key]['total']['before_tax_price'] += $item['before_tax_price'];
				$this->data['data']['gst_list'][$gst_key]['total']['tax_amount'] += $item['tax_amount'];
				$this->data['data']['gst_list'][$gst_key]['total']['amt_included_gst'] += $item['amt_included_gst'];
				
				// total by receipt
				if(!isset($this->data['total']['date_list'][$date][$receipt_ref_no])){
					$this->data['total']['date_list'][$date][$receipt_ref_no]['receipt_no'] = $r['receipt_no'];
					$this->data['total']['date_list'][$date][$receipt_ref_no]['receipt_ref_no'] = $r['receipt_ref_no'];
					$is_new_receipt = true;
				}
				$this->data['total']['date_list'][$date][$receipt_ref_no]['before_tax_price'] += $item['before_tax_price'];
				$this->data['total']['date_list'][$date][$receipt_ref_no]['tax_amount'] += $item['tax_amount'];
				$this->data['total']['date_list'][$date][$receipt_ref_no]['amt_included_gst'] += $item['amt_included_gst'];
				
				$this->data['total']['date_list'][$date][$receipt_ref_no]['amt_collected'] = round($this->data['total']['date_list'][$date][$receipt_ref_no]['amt_included_gst'], 2);
				
				// receipt total
				$this->data['total']['total']['before_tax_price'] += $item['before_tax_price'];
				$this->data['total']['total']['tax_amount'] += $item['tax_amount'];
				$this->data['total']['total']['amt_included_gst'] += $item['amt_included_gst'];
				
				// total by gst
				$this->data['total']['gst_list'][$gst_key]['before_tax_price'] += $item['before_tax_price'];
				$this->data['total']['gst_list'][$gst_key]['tax_amount'] += $item['tax_amount'];
				$this->data['total']['gst_list'][$gst_key]['amt_included_gst'] += $item['amt_included_gst'];
				
				if($last_date[$gst_key] != $date) ksort($this->data['data']['gst_list'][$gst_key]['date_list'][$date]);
				
				$last_date[$gst_key] = $date;
			}
			$con_multi->sql_freeresult($q1);
			
			$this->data['total']['total']['amt_collected'] = round($this->data['total']['total']['amt_included_gst'] + $this->data['total']['total']['rounding_amt'], 2);
		}
		
		if($this->data){
			// sort the date to asc
			if($this->data['date_list']) asort($this->data['date_list']);
			// sort gst list
			if($this->data['gst_list']) ksort($this->data['gst_list']);
		}
		
		//print_r($this->data);
		$smarty->assign('data', $this->data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
}

$RECEIPT_SUMMARY_GST_REPORT = new RECEIPT_SUMMARY_GST_REPORT('Receipt Summary GST Report');
?>