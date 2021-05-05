<?php
$maintenance->check(422);

// pre-load the sales agent performance
class SALES_AGENT_PORTAL extends Module{	
	function __construct($title){
		$this->init();
 		parent::__construct($title);
	}

	function _default(){
		//$this->display();
	}
	
	function init() {
		global $smarty;
		
		// set year and month
		if(!$_REQUEST['year'] || !$_REQUEST['month']){
			$_REQUEST['year'] = date("Y");
			$_REQUEST['month'] = date("m");
		}
		
		// pre-load
		$this->get_sa_details();
		
		// construct the year and month range from past 2 months
		$prev_3_mth_date = date("Y-m-d", strtotime("-2 month", strtotime(date("Y-m-d"))));
		
		$start = new DateTime($prev_3_mth_date);
		$start->modify('first day of this month');
		$end = new DateTime(date("Y-m-d"));
		$end->modify('first day of next month');
		$interval = DateInterval::createFromDateString('1 month');
		$period = new DatePeriod($start, $interval, $end);

		foreach($period as $dt){
			$curr_yr = $dt->format("Y");
			$curr_mth = $dt->format("m");
			$yr_list[$curr_yr] = $curr_yr;
			$mth_list[$curr_mth] = $curr_mth;
		}
		$smarty->assign("yr_list", $yr_list);
		$smarty->assign("mth_list", $mth_list);
		unset($yr_list, $start, $end, $mth_list, $interval, $period, $prev_3_mth_date);
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
		$smarty->assign("upd_type_list", $this->upd_type_list);
	}
	
	function get_sa_details(){
		global $con, $smarty, $LANG;
		
		// preset the date from first day of current month to present day
		$form = $_REQUEST;
		$date_from = date($form['year']."-".$form['month']."-01");
		$date_to = date("Y-m-d", strtotime("-1 day", strtotime("+1 month", strtotime($date_from))));
		
		// load branches
		$this->branches = array();
		$con->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		// check whether the S/A match all the conditions set from commission module
		foreach($this->branches as $bid=>$binfo){
			$sql = array();
			$sql[] = "select sa.id as sa_id, ssc.date, ssc.year, lpad(ssc.month,2,0) as month, ssc.amount, ssc.qty, sa.code as sa_code,
					  b.code as branch_code, sast.value as st_list, ssc.commission_amt
					  from sa_sales_cache_b$bid ssc
					  left join sa on sa.id = ssc.sa_id
					  left join sa_sales_target sast on sast.sa_id = sa.id and sast.year = ssc.year and sast.branch_id = ".mi($bid)."
					  left join branch b on b.id = ".mi($bid)."
					  where ssc.sa_id = ".mi($_SESSION['sa_ticket']['id'])." and ssc.year = ".mi($form['year'])." and ssc.month = ".mi($form['month']);

			// commission by qty/sales range
			$sql[] = "select sa.id as sa_id, 0 as date, srsc.year, lpad(srsc.month,2,0) as month, srsc.amount, srsc.qty, sa.code as sa_code,
					  b.code as branch_code, sast.value as st_list, srsc.commission_amt
					  from sa_range_sales_cache_b$bid srsc
					  left join sa on sa.id = srsc.sa_id
					  left join sa_sales_target sast on sast.sa_id = sa.id and sast.year = srsc.year and sast.branch_id = ".mi($bid)."
					  left join branch b on b.id = ".mi($bid)."
					  where srsc.sa_id = ".mi($_SESSION['sa_ticket']['id'])." and srsc.year = ".mi($form['year'])." and srsc.month = ".mi($form['month']);
			
			$all_sql = join(" UNION ALL ", $sql)." order by branch_code";
			$q1 = $con->sql_query($all_sql);

			while($r1 = $con->sql_fetchassoc($q1)){
				$ym = $r1['year'].$r1['month'];
				$this->table[$bid]['sa_id'] = $r1['sa_id'];
				$this->table[$bid]['sa_code'] = $r1['sa_code'];
				$this->table[$bid]['sa_name'] = $r1['sa_name'];
				$this->table[$bid]['month'] = $r1['month'];
				$this->table[$bid]['year'] = $r1['year'];
				$this->table[$bid]['branch_code'] = $r1['branch_code'];
				$this->table[$bid]['sales_amt'] += $r1['amount'];
				$this->table[$bid]['commission_amt'] += $r1['commission_amt'];

				$sales_target_list = unserialize($r1['st_list']);
				$this->table[$bid]['target_sales_amt'] = $sales_target_list[mi($r1['month'])];

				$date = $r1['year'].'-'.$r1['month'].'-01';
				$remaining_times = strtotime("-1 day", strtotime("+1 month", strtotime($date))) - strtotime(date("Y-m-d"));
				$remaining_days = mi(($remaining_times)/86400);
				
				$this->table[$bid]['remaining_days'] = $remaining_days;
				$this->total['commission_amt'] += $r1['commission_amt'];
			}
			$con->sql_freeresult($q1);
		}
		
		$smarty->assign("branches", $this->branches);
		$smarty->assign("table", $this->table);
		$smarty->assign("total", $this->total);
	}
	
	function ajax_refresh_sa_details(){
		global $smarty;
		
		//$this->get_sa_details();
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch("sa.home.commission_details.tpl");

		die(json_encode($ret));
	}
}

$SALES_AGENT_PORTAL = new SALES_AGENT_PORTAL("Sales Agent Portal");
?>
