<?php
/*
7/12/2012 11:09 AM Andy
- Add to show total Amount.
- Remove Branch dropdown from report. Always show the login branch sales only.

7/15/2012 5:26 PM Andy
- Add show report will filter by vendor sku group.

7/19/2012 9:44 AM Andy
- Enhance report to show open price, scale type and sales in date range.

7/31/2012 11:41 AM Andy
- Add sorting feature for report.

8/1/2012 3:50 PM Andy
- Add print and export excel.

8/2/2012 2:40 PM Andy
- Add cost and gp.

8/11/2012  yinsee
- use sales_report_profit if set

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

3/28/2015 10:08 AM Andy
- Enhance the report to deduct the discount2 and tax amount to get the nett sales amount.
*/
include('include/common.php');
$maintenance->check(169);

if(!$vp_session)	js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class SALES_REPORT_BY_DAY extends Module{
	var $allowed_date = array();
	var $bid = 0;
	
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
		
		// allowed date
		for($i=0 ; $i<7; $i++){
			$this->allowed_date[] = date("Y-m-d", strtotime("-$i day", time()));
		}
		$smarty->assign('allowed_date', $this->allowed_date);
		
		$this->bid = $vp_session['branch_id'];
		
		$smarty->assign('sort_list', $this->sort_list);
		
		parent::__construct($title);
	}
	
	function _default(){
		global $vp_session, $smarty;
		
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
		
		$bid = $this->bid;
		$date_from = $_REQUEST['date_from'];
		$date_to = $_REQUEST['date_to'];
		$this->order_by = $_REQUEST['order_by'];
		$this->order_seq = $_REQUEST['order_seq'];
				
		if(!$this->order_by)	$this->order_by = 'description';
		if(!$this->order_seq)	$this->order_seq = 'asc';
		
		$err = array();
		if(!$bid)	$err[] = "Invalid Branch.";
		if(!$date_from || !in_array($date_from, $this->allowed_date))	$err[] = "Invalid Date From.";
		if(!$date_to || !in_array($date_to, $this->allowed_date))	$err[] = "Invalid Date To.";
		
		//$y = date("Y", strtotime($date));
		//if(!$err && $y<2000)	$err[] = "Invalid Year.";
		
		$sku_group_bid = mi($vp_session['sku_group_bid']);
		$sku_group_id = mi($vp_session['sku_group_id']);
		//$sales_report_profit = doubleval($vp_session['vp']['sales_report_profit'][$bid]);
		if(!$sku_group_bid || !$sku_group_id)	$err[] = "Error on SKU Group setup, please contact admin to solve this.";
		
		// select sku id list
		$con->sql_query("select si.id as sid, si.sku_id
from sku_group_item sgi
join sku_items si on si.sku_item_code=sgi.sku_item_code
where branch_id=".mi($sku_group_bid)." and sku_group_id=".mi($sku_group_id));
		$sku_id_list = array();
		$si_id_list = array();
		while($r = $con->sql_fetchassoc()){
			$si_id_list[] = mi($r['sid']);
			if(!in_array($r['sku_id'], $sku_id_list))	$sku_id_list[] = mi($r['sku_id']);
		}
		$con->sql_freeresult();
		
		if(!$si_id_list)	$err[] = "There is no item in your sku group.";

		if($err){
			$smarty->assign('err', $err);
			return;
		}
		

		$use_last_grn = mi($vp_session['vp']['use_last_grn']);
		
		$filter = array();

		//if(!$use_last_grn)	
		//$filter[] = "sku.vendor_id=".mi($vp_session['id']);	// use master vendor
		$filter[] = "pos.branch_id=$bid";
		$filter[] = "pos.date between ".ms($date_from)." and ".ms($date_to);
		//$filter[] = "si.sku_id in (".join(',', $sku_id_list).")";
		//$filter[] = "si.id in (".join(',', $si_id_list).")";
		$filter[] = "pos.cancel_status=0";
		

		//$sb_tbl = 'stock_balance_b'.$bid.'_'.$y;
		//$vsh_tbl = 'vendor_sku_history_b'.$bid;
		
		/*if($use_last_grn){
			$xtra_col = ",vsh.vendor_id as last_vendor_id";
			$xtra_join = "left join $vsh_tbl vsh on vsh.sku_item_id=pi.sku_item_id and pi.date between vsh.from_date and vsh.to_date";
			
			$filter[] = $vp_session['id']." in (sku.vendor_id, vsh.vendor_id)";
		}*/
		
		$filter = "where ".join(' and ', $filter);
		
		$sql = "select pi.branch_id,pi.date,pi.counter_id,pi.pos_id,pi.sku_item_id,(pi.price-pi.discount-pi.discount2-pi.tax_amount) as amt,pi.qty, si.sku_item_code,si.mcode, si.artno, si.link_code,si.description, sku.vendor_id as master_vendor_id, pi.open_price_by,
			if(si.scale_type=-1, sku.scale_type, si.scale_type) as scale_type, c.department_id,sku.vendor_id, sku.brand_id, sku.trade_discount_type,sku.default_trade_discount_code
		 $xtra_col
		from pos_items pi
		join pos on pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id and pos.id=pi.pos_id
		join sku_items si on si.id=pi.sku_item_id
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		join sku_group_item sgi on sgi.branch_id=$sku_group_bid and sgi.sku_group_id=$sku_group_id and sgi.sku_item_code=si.sku_item_code
		join sku_group_vp_date_control vpdc on vpdc.branch_id=sgi.branch_id and vpdc.sku_group_id=sgi.sku_group_id and vpdc.sku_item_id=si.id and pi.date between vpdc.from_date and vpdc.to_date
		$xtra_join
		$filter
		order by pi.date,si.mcode";
		
		if($_REQUEST['show_q'])
			print $sql;
		
		if(!$con_multi)	$con_multi= new mysql_multi();
		
		$this->data = array();
		$q1 = $con_multi->sql_query_false($sql, true);
		$sid_to_last_vid = array();
		
		$receipt_list = array();
		$last_date = '';
		$tmp_item_trade_discount_code = array();
		
		while($r = $con_multi->sql_fetchassoc($q1)){
			$sid = mi($r['sku_item_id']);
			$date_key = $r['date'];
			$is_open_price = $r['open_price_by'] ? 1 : 0;
			$scale_type = mi($r['scale_type']);
			$open_scale_key = $is_open_price.'_'.$scale_type;
			$receipt_key = $r['branch_id'].'_'.$r['date'].'_'.$r['counter_id'].'_'.$r['pos_id'];
			
			// get srp by date
			$params = array();
			$params['other_type_info'] = array('type'=>'SKU', 'value'=>$sid);
			$profit_info = get_vp_sales_report_profit_by_date($r['date'], array(), $params);
			$sales_report_profit_by_date = $profit_info['per'];
			
			if($r['date'] != $last_date){
				$last_date = $r['date'];
				$tmp_item_trade_discount_code = array();
			}
			
			/*if(!in_array($receipt_key, $receipt_list)){
				$receipt_list[] = $receipt_key;
				$this->data['total']['receipt_count']++;
			}*/
			//if($use_last_grn){
			//	if($r['last_vendor_id'] && $r['last_vendor_id'] != $vp_session['id']) continue;	// last vendor not
			//	elseif($r['master_vendor_id'] != $vp_session['id'])	continue;	// master vendor not
			//}
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
					$tmp = get_sku_item_cost_selling($r['branch_id'], $sid, $r['date'], array('trade_discount_code'));
					$tmp_item_trade_discount_code[$sid]['trade_discount_code'] = $tmp['trade_discount_code'];
					
					// if no discount code, get master 
					if(!$tmp_item_trade_discount_code[$sid]['trade_discount_code'])	$tmp_item_trade_discount_code[$sid]['trade_discount_code'] = $r['default_trade_discount_code'];
					
					// get discount rate at that time
					$tmp_discount_rate = get_consignment_discount_rate($r['branch_id'], $r['date'], $tmp_item_trade_discount_code[$sid]['trade_discount_code'], $r['department_id'], $brand_vendor_commission, $brand_vendor_id);
					$tmp_item_trade_discount_code[$sid]['discount_rate'] = $tmp_discount_rate;
				}
				
				//$trade_discount_code = $tmp_item_trade_discount_code[$sid]['trade_discount_code'];
				$discount_rate = mf($tmp_item_trade_discount_code[$sid]['discount_rate']);
			}*/
			
			if(!isset($this->data['si_info'][$sid])){
				$this->data['si_info'][$sid]['id'] = $sid;
				$this->data['si_info'][$sid]['sku_item_code'] = $r['sku_item_code'];
				$this->data['si_info'][$sid]['mcode'] = $r['mcode'];
				$this->data['si_info'][$sid]['artno'] = $r['artno'];
				$this->data['si_info'][$sid]['link_code'] = $r['link_code'];
				$this->data['si_info'][$sid]['description'] = $r['description'];
				//$this->data['si_info'][$sid]['stock_balance'] = $r['stock_balance'];
			}
			
			//$amt = round($r['price'] - $r['discount'], 2);
			$amt = round($r['amt'], 2);
			$cost = $amt-($amt*$discount_rate/100);
			
			// sales by items
			$this->data['item_sales'][$sid]['amt'] += $amt;
			$this->data['item_sales'][$sid]['qty'] += $r['qty'];
			$this->data['item_sales'][$sid]['cost'] += $cost;
			
			// sales by date
			$this->data['date_sales'][$date_key]['amt'] += $amt;
			$this->data['date_sales'][$date_key]['qty'] += $r['qty'];
			$this->data['date_sales'][$date_key]['cost'] += $cost;
			if(!is_array($this->data['date_sales'][$date_key]['receipt_list']) || !$this->data['date_sales'][$date_key]['receipt_list']){
				$this->data['date_sales'][$date_key]['receipt_list'] = array();
			}
			if(!in_array($receipt_key, $this->data['date_sales'][$date_key]['receipt_list'])){
				$this->data['date_sales'][$date_key]['receipt_list'][] = $receipt_key;
				$this->data['date_sales'][$date_key]['receipt_count']++;
			}
			// sales by date by sku
			$this->data['date_item_sales'][$date_key][$sid]['sid'] = $sid;
			$this->data['date_item_sales'][$date_key][$sid]['amt'] += $amt;
			$this->data['date_item_sales'][$date_key][$sid]['qty'] += $r['qty'];
			$this->data['date_item_sales'][$date_key][$sid]['cost'] += $cost;
			
			$this->data['date_item_sales'][$date_key][$sid]['details'][$open_scale_key]['qty'] += $r['qty'];
			$this->data['date_item_sales'][$date_key][$sid]['details'][$open_scale_key]['amt'] += $amt;
			$this->data['date_item_sales'][$date_key][$sid]['details'][$open_scale_key]['cost'] += $cost;
			
			// open price label
			$this->data['date_item_sales'][$date_key][$sid]['details'][$open_scale_key]['open_price_label'] = $is_open_price ? 'Open' : 'Fixed';
			
			// scale type label
			$scale_type_label = 'No';
			if($scale_type==1)	$scale_type_label = 'Fix Price';
			elseif($scale_type==2)	$scale_type_label = 'Weighted';
			$this->data['date_item_sales'][$date_key][$sid]['details'][$open_scale_key]['scale_type_label'] = $scale_type_label;
			
			// receipt by date by sku
			if(!is_array($this->data['date_item_sales'][$date_key][$sid]['details'][$open_scale_key]['receipt_list']) || !$this->data['date_item_sales'][$date_key][$sid]['details'][$open_scale_key]['receipt_list']){
				$this->data['date_item_sales'][$date_key][$sid]['details'][$open_scale_key]['receipt_list'] = array();
			}
			if(!in_array($receipt_key, $this->data['date_item_sales'][$date_key][$sid]['details'][$open_scale_key]['receipt_list'])){
				$this->data['date_item_sales'][$date_key][$sid]['details'][$open_scale_key]['receipt_list'][] = $receipt_key;
				$this->data['date_item_sales'][$date_key][$sid]['details'][$open_scale_key]['receipt_count'] ++;		
			}

			
			// sales by total
			$this->data['total']['qty'] += $r['qty'];
			$this->data['total']['amt'] += $amt;
			$this->data['total']['cost'] += $cost;
			
			if(!is_array($this->data['total']['receipt_list']) || !$this->data['total']['receipt_list'])	$this->data['total']['receipt_list'] = array();
			
			if(!in_array($receipt_key, $this->data['total']['receipt_list'])){
				$this->data['total']['receipt_list'][] = $receipt_key;
				$this->data['total']['receipt_count']++;
			}
		}
		$con_multi->sql_freeresult($q1);
		
		$con_multi->close_connection();
		
		if($this->data['date_item_sales']){
			foreach($this->data['date_item_sales'] as $date => $item_list){
				uasort($this->data['date_item_sales'][$date], array($this, 'sort_date_data'));			
			}
		}

		
		//print_r($this->data);
		
		$smarty->assign('data', $this->data);
		
		$report_title = array();
		$report_title[] = "Branch: ".get_branch_code($bid);
		$report_title[] = "Date: ".$date_from." to ".$date_to;
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
	
	private function sort_date_data($a, $b){
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

$SALES_REPORT_BY_DAY = new SALES_REPORT_BY_DAY('Sales Report by Day');
?>
