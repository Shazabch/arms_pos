<?php
/*
11/20/2012 2:39 PM Andy
- Enhanced report to check SKU Group Date Control Settings.

3/28/2015 10:08 AM Andy
- Enhance the report to deduct the discount2 and tax amount to get the nett sales amount.
*/
include('include/common.php');
$maintenance->check(169);

if(!$vp_session)	js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class SALES_REPORT_BY_DAY extends Module {
	
	var $allowed_date = array();
	var $bid = 0;
	
	function __construct($title){
		global $con, $smarty, $vp_session;
		
		// allowed date
		for($i=0 ; $i<7; $i++){
			$this->allowed_date[] = date("Y-m-d", strtotime("-$i day", time()));
		}
		$smarty->assign('allowed_date', $this->allowed_date);
		
		$this->bid = $vp_session['branch_id'];
		
		parent::__construct($title);
	}
	
	function _default(){
		global $vp_session, $smarty;
		
		if($_REQUEST['load_report']){
			if($_REQUEST['submit_type']=='excel'){	// export excel
				include_once("include/excelwriter.php");
				log_vp($vp_session['id'], "MASTERFILE_SKU", 0, "Export ".$this->title);
				
				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
			
			$this->load_report();
		}
		
		$this->display();
	}
	
	private function load_report() {
		
		global $con, $smarty, $vp_session;
		
		//print_r($vp_session);
		
		$bid = $this->bid;
		$date_from = $_REQUEST['date_from'];
		$date_to = $_REQUEST['date_to'];
		
		$err = array();
		if(!$bid)	$err[] = "Invalid Branch.";
		if(!$date_from || !in_array($date_from, $this->allowed_date))	$err[] = "Invalid Date From.";
		if(!$date_to || !in_array($date_to, $this->allowed_date))	$err[] = "Invalid Date To.";
		
		$sku_group_bid = mi($vp_session['sku_group_bid']);
		$sku_group_id = mi($vp_session['sku_group_id']);
		if(!$sku_group_bid || !$sku_group_id)	$err[] = "Error on SKU Group setup, please contact admin to solve this.";
		
		if($err) {
			$smarty->assign('err', $err);
			return;
		}
		
		/*
		$date_from = '2012-07-01';
		$date_to = '2012-07-31';
		$bid = 1;
		$sku_group_id = 8;
		*/
		
		$days = array();
		$period_day = strtotime($date_to) - strtotime($date_from);
		$days_remain = intval(date("z", $period_day));
		for($i=0;$i<=$days_remain;$i++) $days[] = date("Y-m-d", strtotime("+$i day", strtotime($date_from)));
		$smarty->assign("days", $days);
		
		//$min_hour = 1;
		$max_hour = 22;
		
		$sql = "select
		year(pos.date) as year,
		month(pos.date) as month,
		day(pos.date) as day,
		hour(pos.pos_time) as hour,
		sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) as amount
		from pos_items pi
		left join pos on pos.id=pi.pos_id and pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id
		left join sku_items si on pi.sku_item_id = si.id
		left join sku on si.sku_id = sku.id
		join sku_group_item sgi on sgi.branch_id = ".mi($sku_group_bid)." and sgi.sku_item_code=si.sku_item_code and sgi.sku_group_id=".mi($sku_group_id)."
		join sku_group_vp_date_control vpdc on vpdc.branch_id=sgi.branch_id and vpdc.sku_group_id=sgi.sku_group_id and vpdc.sku_item_id=si.id and pi.date between vpdc.from_date and vpdc.to_date
		where
		pos.date between ".ms($date_from)." and ".ms($date_to)."
		and pos.cancel_status=0
		and pos.branch_id = ".mi($bid)."
		group by year, month, day, hour
		order by year,month,day,hour";
		
		if($_REQUEST['show_q'])
			print $sql;
		
		$con->sql_query($sql);
		
		while ($r = $con->sql_fetchrow()) {
			$year = $r['year'];
			$month = ($r['month'] < 10) ? '0'.$r['month'] : $r['month'];
			$day = ($r['day'] < 10) ? '0'.$r['day'] : $r['day'];
			$date = "$year-$month-$day";
			$data[$date][$r['hour']] = $r['amount'];
			$day_total[$date] += $r['amount'];
			$hour_total[$r['hour']] += $r['amount'];
			$grand_total += $r['amount'];
			
			if(!isset($min_hour) || $min_hour > $r['hour']) {
				$min_hour = $r['hour'];
			}
			
			if (!$max_hour || $max_hour < $r['hour']) {
				$max_hour = $r['hour'];
			}
		}
		
		$hour = array();
		for($i=$min_hour;$i<=$max_hour;$i++) {
			if ($i<12) $hour[$i]=$i.":00 AM";
			elseif ($i==12) $hour[$i]=$i.":00 PM";
			else {
				$h = $i-12;
				if($i==24) $hour[$i] = $h.":00 AM";
				else $hour[$i] = $h.":00 PM";
			}
		}
		$smarty->assign("hour", $hour);
		
		//print_r($day_total);die;
		$smarty->assign('data',$data);
		$smarty->assign('day_total',$day_total);
		$smarty->assign('hour_total',$hour_total);
		$smarty->assign('grand_total',$grand_total);
		$smarty->assign("hour_count", count($hour));
		
		$report_title = array();
		$report_title[] = "Branch: ".get_branch_code($bid);
		$report_title[] = "Date: ".$date_from." to ".$date_to;
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		
	}

}

$SALES_REPORT_BY_DAY = new SALES_REPORT_BY_DAY('Hourly Sales by Day');

?>
