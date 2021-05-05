<?php
/*
12/12/2017 11:35 AM Andy
- Enhanced to able to select up to last 30 days sales.
*/

include('include/common.php');
$maintenance->check(169);

if(!$vp_session) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class SALES_REPORT_BY_DAY_BY_RECEIPT extends Module{
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
		for($i=0 ; $i<30; $i++){
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
		
		$filter = array();
		$filter[] = "pos.branch_id=$bid";
		$filter[] = "pos.date between ".ms($date_from)." and ".ms($date_to);
		$filter[] = "pos.cancel_status=0";
		
		$filter = "where ".join(' and ', $filter);
		
		$sql = "select pi.branch_id,pi.date,pi.counter_id,pi.pos_id,pi.sku_item_id,(pi.price-pi.discount-pi.discount2-pi.tax_amount) as amt, pi.qty, pos.receipt_ref_no, 
				si.sku_item_code,si.mcode, si.artno, si.link_code,si.description, sku.vendor_id as master_vendor_id, c.department_id,sku.vendor_id, sku.brand_id, 
				sku.trade_discount_type, sku.default_trade_discount_code
				from pos_items pi
				join pos on pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id and pos.id=pi.pos_id
				join sku_items si on si.id=pi.sku_item_id
				left join sku on sku.id=si.sku_id
				left join category c on c.id=sku.category_id
				join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date and pf.finalized=1
				join sku_group_item sgi on sgi.branch_id=$sku_group_bid and sgi.sku_group_id=$sku_group_id and sgi.sku_item_code=si.sku_item_code
				join sku_group_vp_date_control vpdc on vpdc.branch_id=sgi.branch_id and vpdc.sku_group_id=sgi.sku_group_id and vpdc.sku_item_id=si.id and pi.date between vpdc.from_date and vpdc.to_date
				$xtra_join
				$filter
				order by pi.date,pos.receipt_ref_no asc, si.sku_item_code";
		
		if($_REQUEST['show_q']) print $sql;
		
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
			$ref_no_key = $r['receipt_ref_no'];
			
			if($r['date'] != $last_date){
				$last_date = $r['date'];
				$tmp_item_trade_discount_code = array();
			}
			
			$discount_rate = 0;
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
			
			$discount_code = $tmp_item_trade_discount_code[$sid]['trade_discount_code'];
			$discount_rate = mf($tmp_item_trade_discount_code[$sid]['discount_rate']);
			
			if(!isset($this->data['si_info'][$sid])){
				$this->data['si_info'][$sid]['id'] = $sid;
				$this->data['si_info'][$sid]['sku_item_code'] = $r['sku_item_code'];
				$this->data['si_info'][$sid]['mcode'] = $r['mcode'];
				$this->data['si_info'][$sid]['artno'] = $r['artno'];
				$this->data['si_info'][$sid]['link_code'] = $r['link_code'];
				$this->data['si_info'][$sid]['description'] = $r['description'];
			}
			
			$amt = round($r['amt'], 2);
			$commission_amt = round($amt*$discount_rate/100, 2);
			$nett_amt = round($amt - $commission_amt, 2);
			
			// sales by date
			$this->data['date_sales'][$date_key]['amt'] += $amt;
			$this->data['date_sales'][$date_key]['qty'] += $r['qty'];
			$this->data['date_sales'][$date_key]['commission_amt'] += $commission_amt;
			$this->data['date_sales'][$date_key]['nett_amt'] += $nett_amt;
			
			// sales by sku (for sorting purpose)
			$this->data['sales_data'][$date_key][$sid]['sid'] = $sid;
			$this->data['sales_data'][$date_key][$sid]['amt'] += $amt;
			$this->data['sales_data'][$date_key][$sid]['qty'] += $r['qty'];
			$this->data['sales_data'][$date_key][$sid]['commission_amt'] += $commission_amt;
			$this->data['sales_data'][$date_key][$sid]['nett_amt'] += $nett_amt;
			
			// sales by date by sku by receipt
			$this->data['sales_data'][$date_key][$sid]['details'][$ref_no_key]['sid'] = $sid;
			$this->data['sales_data'][$date_key][$sid]['details'][$ref_no_key]['amt'] += $amt;
			$this->data['sales_data'][$date_key][$sid]['details'][$ref_no_key]['qty'] += $r['qty'];
			$this->data['sales_data'][$date_key][$sid]['details'][$ref_no_key]['discount_code'] = $discount_code;
			$this->data['sales_data'][$date_key][$sid]['details'][$ref_no_key]['discount_rate'] = $discount_rate;
			$this->data['sales_data'][$date_key][$sid]['details'][$ref_no_key]['commission_amt'] += $commission_amt;
			$this->data['sales_data'][$date_key][$sid]['details'][$ref_no_key]['nett_amt'] += $nett_amt;
			
			// sales grand total
			$this->data['total']['qty'] += $r['qty'];
			$this->data['total']['amt'] += $amt;
			$this->data['total']['commission_amt'] += $commission_amt;
			$this->data['total']['nett_amt'] += $nett_amt;
		}
		$con_multi->sql_freeresult($q1);
		
		$con_multi->close_connection();
		
		if($this->data['sales_data']){
			foreach($this->data['sales_data'] as $date => $item_list){
				uasort($this->data['sales_data'][$date], array($this, 'sort_date_data'));			
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

$SALES_REPORT_BY_DAY_BY_RECEIPT = new SALES_REPORT_BY_DAY_BY_RECEIPT('Sales Report by Day by Receipt');
?>
