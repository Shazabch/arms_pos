<?php
/*
7/31/2012 11:41 AM Andy
- Add sorting feature for report.

8/2/2012 2:40 PM Andy
- Add cost and gp.

8/11/2012 yinsee
- add sales_report_profit

8/14/2012 11:37 AM Andy
- Enhance Sales Report by Week/Month to have expand/collapse control.

8/16/2012 11:43 AM Andy
- Fix sorting bugs.

10/3/2012 12:06 PM Justin
- Enhanced to lookup sales report profit percentage by branch.

11/15/2012 12:03 PM Andy
- Enhanced report to calculate cost by percentage sku or category.
- Enhanced report to check SKU Group Date Control Settings.

1/25/2013 10:33 AM Andy
- Change the report to only check the vendor portal report profit for cost and profit.
- Modified report profit percent to use override format instead of additional format, override from lowest to highest. (sku > lower cat > higher cat > normal %)
- Modified bonus percent to use override format instead of additional format, override from lowest to highest. (lower cat > higher cat > normal %)
*/
include('include/common.php');
$maintenance->check(169);

if(!$vp_session)	js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class SALES_REPORT_BY_MONTH_WEEK extends Module{
	var $rpt_type = 'm';
	var $months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
	
	var $sort_list = array(
		'description' => array(
			'label' => 'Description',
			'col' => 'si.description'
		),
		'mcode' => array(
			'label' => 'Mcode',
			'col' => 'si.mcode'
		),
		'link_code' => array(
			'label' => "",
			'col' => 'si.link_code'
		),
		'qty' => array(
			'label' => 'Qty',
			'col' => ''
		),
		'amt' => array(
			'label' => 'Amount',
			'col' => ''
		)
	);
	
	function __construct($title){
		global $con, $smarty, $vp_session, $config;
		
		$this->sort_list['link_code']['label'] = $config['link_code_name'];	// fix cant put config name when define
		
		$this->bid = $vp_session['branch_id'];
		if($_REQUEST['type']=='w')	$this->rpt_type = 'w';	// m = monthly, w = weekly
		
		$smarty->assign('rpt_type', $this->rpt_type);
		$smarty->assign('sort_list', $this->sort_list);
			
		parent::__construct($title);
	}
	
	function _default(){
		global $vp_session, $smarty;
		
		if(!isset($_REQUEST['date_from']) && !isset($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date("Y-m-d");
			$_REQUEST['date_from'] = date("Y-m-d", strtotime("-7 day", strtotime($_REQUEST['date_to'])));
		}
		
		if($_REQUEST['load_report']){
			if($_REQUEST['submit_type']=='excel'){	// export excel
				include_once("include/excelwriter.php");
				log_vp($vp_session['id'], "VENDOR REPORT", 0, "Export ".$this->title);
	
				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
			$this->load_report();
		}
		$this->display();
	}
	
	private function load_report(){
		global $con, $smarty, $vp_session, $config, $con_multi;
		
		//print_r($vp_session);
		
		$bid = mi($this->bid);
		$date_from = $_REQUEST['date_from'];
		$date_to = $_REQUEST['date_to'];
		
		$this->order_by = $_REQUEST['order_by'];
		$this->order_seq = $_REQUEST['order_seq'];
				
		if(!$this->order_by)	$this->order_by = 'description';
		if(!$this->order_seq)	$this->order_seq = 'asc';
		
		$dt1 = strtotime($date_from);
		$dt2 = strtotime($date_to);
		
		$err = array();
		if(!$bid)	$err[] = "Invalid Branch.";
		if(!$date_from || !$dt1)	$err[] = "Invalid Date From.";
		if(!$date_to || !$dt2)	$err[] = "Invalid Date To.";
		
		if(!$err && $dt1 > $dt2)	$err[] = "Date to cannot early then date from.";
		if(!$err && date("Y", strtotime($date_from))<2007)	$err[] = "Report cannot show data early then year 2007.";
		
		$time_diff = $dt2 - $dt1;
		$date_diff = mi($time_diff/86400);
		if(!$err && $date_diff>90)	$err[] = "Report maximum show 90 days of transaction.";
		
		$sku_group_bid = mi($vp_session['sku_group_bid']);
		$sku_group_id = mi($vp_session['sku_group_id']);
        //$sales_report_profit = doubleval($vp_session['vp']['sales_report_profit'][$bid]);
		
		if(!$sku_group_bid || !$sku_group_id)	$err[] = "Error on SKU Group setup, please contact admin to solve this.";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$tbl = "sku_items_sales_cache_b".$bid;
		
		$sql = "select tbl.sku_item_id, tbl.date, tbl.amount, tbl.qty, si.sku_item_code,si.mcode, si.artno, si.link_code,si.description, c.department_id,sku.vendor_id, sku.brand_id, sku.trade_discount_type,sku.default_trade_discount_code
		from sku_group_item sgi
		join sku_items si on si.sku_item_code=sgi.sku_item_code
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		join $tbl tbl on tbl.sku_item_id=si.id
		join sku_group_vp_date_control vpdc on vpdc.branch_id=sgi.branch_id and vpdc.sku_group_id=sgi.sku_group_id and vpdc.sku_item_id=si.id and tbl.date between vpdc.from_date and vpdc.to_date
		where sgi.branch_id=$sku_group_bid and sgi.sku_group_id=$sku_group_id and tbl.date between ".ms($date_from)." and ".ms($date_to)."
		order by tbl.date";
		
		if($_REQUEST['show_q'])
			print $sql;
		if(!$con_multi)	$con_multi= new mysql_multi();
		
		$this->data = array();
		$last_date = '';
		$tmp_item_trade_discount_code = array();
		
		$q1 = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q1)){
			$sid = mi($r['sku_item_id']);
			$time = strtotime($r['date']);
			
			$y = mi(date("Y", $time));
			$m = mi(date("m", $time));
			
			$key = date("Ym", $time);
			
			// get srp by date
			$params = array();
			$params['other_type_info'] = array('type'=>'SKU', 'value'=>$sid);
			$profit_info = get_vp_sales_report_profit_by_date($r['date'], array(), $params);
			$sales_report_profit_by_date = $profit_info['per'];

			if($r['date'] != $last_date){
				$last_date = $r['date'];
				$tmp_item_trade_discount_code = array();
			}
			
			
			if($this->rpt_type == 'w')	$key = date("W", $time);
			
			// si info
			if(!isset($this->data['si_info'][$sid])){
				$this->data['si_info'][$sid]['id'] = $sid;
				$this->data['si_info'][$sid]['sku_item_code'] = $r['sku_item_code'];
				$this->data['si_info'][$sid]['mcode'] = $r['mcode'];
				$this->data['si_info'][$sid]['artno'] = $r['artno'];
				$this->data['si_info'][$sid]['link_code'] = $r['link_code'];
				$this->data['si_info'][$sid]['description'] = $r['description'];
			}
			$discount_rate = 0;
			if ($sales_report_profit_by_date > 0){
				$discount_rate = $sales_report_profit_by_date;
			}/*else{
				if(!isset($tmp_item_trade_discount_code[$sid])){	// get trade discount code by date
					// get discount rate
					if($r['trade_discount_type'] == 1){
						$brand_vendor_id = $r['brand_id'];
						$brand_vendor_commission = 'brand';
					}else{
						$brand_vendor_id = $r['vendor_id'];
						$brand_vendor_commission = 'vendor';
					}
				
					// get trade discount code
					$tmp = get_sku_item_cost_selling($bid, $sid, $r['date'], array('trade_discount_code'));
					$tmp_item_trade_discount_code[$sid]['trade_discount_code'] = $tmp['trade_discount_code'];
					
					// if no discount code, get master 
					if(!$tmp_item_trade_discount_code[$sid]['trade_discount_code'])	$tmp_item_trade_discount_code[$sid]['trade_discount_code'] = $r['default_trade_discount_code'];
					
					// get discount rate at that time
					$tmp_discount_rate = get_consignment_discount_rate($bid, $r['date'], $tmp_item_trade_discount_code[$sid]['trade_discount_code'], $r['department_id'], $brand_vendor_commission, $brand_vendor_id);
					$tmp_item_trade_discount_code[$sid]['discount_rate'] = $tmp_discount_rate;
				}
				
				//$trade_discount_code = $tmp_item_trade_discount_code[$sid]['trade_discount_code'];
				$discount_rate = mf($tmp_item_trade_discount_code[$sid]['discount_rate']);
			}*/
			
			$amt = round($r['amount'], 2);
			$cost = $amt-($amt*$discount_rate/100);
			
			if(!isset($this->data['data'][$key])){
				$this->data['data'][$key]['label'] = $this->months[$m].' '.$y;
				if($this->rpt_type == 'w')	$this->data['data'][$key]['label'] = 'Week '.$key;
			}
			
			$this->data['data'][$key]['item_list'][$sid]['sid'] = $sid;
			$this->data['data'][$key]['item_list'][$sid]['amt'] += $amt;
			$this->data['data'][$key]['item_list'][$sid]['qty'] += $r['qty'];
			$this->data['data'][$key]['item_list'][$sid]['cost'] += $cost;
			
			$this->data['data'][$key]['total']['amt'] += $amt;
			$this->data['data'][$key]['total']['qty'] += $r['qty'];
			$this->data['data'][$key]['total']['cost'] += $cost;
			
			$this->data['total']['amt'] += $amt;
			$this->data['total']['qty'] += $r['qty'];
			$this->data['total']['cost'] += $cost;
		}
		$con_multi->sql_freeresult($q1);
		
		$con_multi->close_connection();
		
		if($this->data['data']){
			foreach($this->data['data'] as $key => $data_info){
				uasort($this->data['data'][$key]['item_list'], array($this, "sort_data"));
			}
		}
		//print_r($this->data);
		
		$smarty->assign('data', $this->data);
		
		$report_title = array();
		$report_title[] = "Branch: ".get_branch_code($bid);
		$report_title[] = "Date: ".$date_from." to ".$date_to;
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
	
	private function sort_data($a, $b){
		$order_by = $this->order_by;
		$order_seq = $this->order_seq;
		
		if(in_array($order_by, array('mcode', 'link_code', 'description'))){
			if(strcasecmp($this->data['si_info'][$a['sid']][$order_by], $this->data['si_info'][$b['sid']][$order_by])==0)	return 0;
			if($order_seq == 'desc'){
				return strcasecmp($this->data['si_info'][$a['sid']][$order_by], $this->data['si_info'][$b['sid']][$order_by]) > 0 ? 0 : 1;
			}else	return strcasecmp($this->data['si_info'][$a['sid']][$order_by], $this->data['si_info'][$b['sid']][$order_by]) > 0 ? 1 : 0;
		}else{	// sort by qty or amt
			if($a[$order_by] == $b[$order_by])	return 0;
			if($order_seq == 'desc'){
				return $a[$order_by] > $b[$order_by] ? 0 : 1;
			}else	return $a[$order_by] > $b[$order_by] ? 1 : 0;
		}
	}
}

$SALES_REPORT_BY_MONTH_WEEK = new SALES_REPORT_BY_MONTH_WEEK('Sales Report by '.($_REQUEST['type']=='w' ? 'Week' : 'Month'));
?>
