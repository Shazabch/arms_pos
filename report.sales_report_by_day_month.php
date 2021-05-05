<?php
/*
5/14/2010 11:47:54 AM Andy
- Modified some words

5/19/2010 5:13:17 PM Alex
- Add department filter

1/24/2011 3:51:53 PM Alex
- change use report_server

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

5:06 PM 1/29/2014 Fithri
- add column mix match discount amt

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

1/29/2015 10:24 AM Andy
- Fix report wrongly get the cancelled receipt to calculate mix & match discount.

2/20/2020 5:41 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

class SALES_REPORT_BY_DAY_MONTH extends Report{
	private function run(){
        global $con, $smarty,$sessioninfo,$con_multi;
        
        $from_date = $this->from_date;
		$to_date = $this->to_date;
		$bid = $this->bid;
		$view_type = $this->view_type;

		$y = $this->year;
		$m = $this->month;

		$filter = "c.department_id in ($sessioninfo[department_ids]) and ";
//$where=$filter;
		if ($view_type=='day') $filter1 = $filter." s.year = ".$y." and s.month = ".$m;
		else $filter1 = $filter." s.year = ".$y;

		$active_sku = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';
		
		//$con_multi = new mysql_multi();
		// sales cache
		$tbl = "sku_items_sales_cache_b".$bid;
		$sql = "select s.date,sum(s.amount) as selling,sum(cost) as cost
				from $tbl s
				left join sku_items on sku_items.id = s.sku_item_id
				left join sku on sku.id = sku_items.sku_id
				left join category c on c.id = sku.category_id
				where $filter1 and $active_sku
				group by s.date";//print "<br /><br /><h3>$sql</h3>";
//print $sql;exit;


		$q_cs = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchrow($q_cs)){
		    if($view_type=='day')	$date_key = date("Ymd", strtotime($r['date']));
		    else    $date_key = date("Ym", strtotime($r['date']));

			$table[$date_key]['selling'] += $r['selling'];
			$table[$date_key]['cost'] += $r['cost'];
			$table[$date_key]['date'] = $r['date'];
//			$table[$date_key]['transaction_count'] += $r['transaction_count'];
		}
		$con_multi->sql_freeresult($q_cs);
		
		// transaction count
/*		$tbl = "member_sales_cache_b".$bid;
		$sql = "select m.date, sum(m.transaction_count) as transaction_count
				from $tbl m
				where m.date between ".ms($from_date)." and ".ms($to_date)."
				group by m.date";

        $q_ms = $con_multi->sql_query($sql);
        while($r = $con_multi->sql_fetchrow($q_ms)){
		    if($view_type=='day')	$date_key = date("Ymd", strtotime($r['date']));
		    else    $date_key = date("Ym", strtotime($r['date']));

			$table[$date_key]['transaction_count'] += $r['transaction_count'];
		}
		$con_multi->sql_freeresult($q_ms);
*/

		if ($view_type=='day') $filter2 = $filter." year = ".$y." and month = ".$m;
		else $filter2 = $filter." year = ".$y;

		$sql = "select date, count(distinct counter_id,pos_id, date) as transaction_count
				from dept_trans_cache_b".$bid." c
				where $filter2
				group by date";
//print $sql;
        $q_ms = $con_multi->sql_query($sql);
        while($r = $con_multi->sql_fetchrow($q_ms)){
		    if($view_type=='day')	$date_key = date("Ymd", strtotime($r['date']));
		    else    $date_key = date("Ym", strtotime($r['date']));

			$table[$date_key]['transaction_count'] += $r['transaction_count'];
		}
		$con_multi->sql_freeresult($q_ms);

//print_r($table);

		if($table){
			foreach($table as $date_key=>&$r){
			
				$r['gp'] = $r['selling'] - $r['cost'];
				if($r['selling'])   $r['gp_per'] = ($r['gp'] / $r['selling']) * 100;
				
				// get mix & match discount
				$mm_filter = array();
				$mm_filter[] = "pp.branch_id=$bid and pp.type='Mix & Match Total Disc' and pp.changed <> 1 and pp.adjust = 0 and pf.finalized = 1";
				$mm_filter[] = "p.cancel_status=0";
				
				if ($this->view_type == 'day') {
					// by day
					$mm_filter[] = "pp.date = ".ms($r['date']);
				}
				else {
					// by month
					$y = substr($date_key, 0, 4);
					$m = substr($date_key, 4, 2);
					$date_from = $y.'-'.$m.'-1';
					$date_to = $y.'-'.$m.'-'.days_of_month($m, $y);
					$mm_filter[] = "pp.date between ".ms($date_from)." and ".ms($date_to);
				}
				
				$mm_filter = "where ".join(' and ', $mm_filter);
				$sql1 = "select ifnull(sum(pp.amount),0) as mm 
				from pos_payment pp 
				join pos_finalized pf on pf.branch_id=pp.branch_id and pf.date=pp.date 
				join pos p on p.branch_id=pp.branch_id and p.date=pp.date and p.counter_id=pp.counter_id and p.id=pp.pos_id
				".$mm_filter;
				
				//print "<br /><br />$sql1<br />";
				$q1 = $con_multi->sql_query($sql1);
				$r1 = $con_multi->sql_fetchassoc($q1);
				$r['mix_match'] = $r1['mm'];
				$con_multi->sql_freeresult($q1);
			}
		}
		//print_r($table);
		$this->table = $table;
		//$con_multi->close_connection();
	}
	
    function generate_report(){
		global $con, $smarty;

		$this->run();
		
		$smarty->assign('table', $this->table);
		/*
		print '<pre>';
		print_r($this->table);
		print '</pre>';
		var_dump($this->view_type);
		*/
	}
	
	function process_form(){
	    global $con, $smarty, $con_multi;

        $this->bid  = get_request_branch();

        $this->view_type = $_REQUEST['view_type'] == 'day' ? 'day' : 'month';
        $this->year = mi($_REQUEST['year']);

		if($this->view_type=='day'){
        	$this->month = mi($_REQUEST['month']);
        	$this->from_date = $this->year.'-'.$this->month.'-1';
        	$this->to_date = $this->year.'-'.$this->month.'-'.days_of_month($this->month, $this->year);
        	$date_label = $this->generate_dates($this->from_date, $this->to_date, 'Ymd', 'Y-m-d');
        	if($date_label){
				foreach($date_label as $date_key=>$d){
					$this->date_label[$date_key]['date'] = $d;
					$this->date_label[$date_key]['day'] = date('w', strtotime($d));
				}
			}
		}else{
            $this->from_date = $this->year.'-01-01';
        	$this->to_date = $this->year.'-12-31';
        	for($i = 1; $i<=12; $i++){
        	    $date_key = $this->year.sprintf('%02d', $i);
                $this->date_label[$date_key]['date'] = str_month($i)." ".$this->year;
			}
		}
		
		$con_multi->sql_query("select code from branch where id=".mi($this->bid));
        $report_title[] = "Branch: ".$con_multi->sql_fetchfield(0);
		$con_multi->sql_freeresult();
        $report_title[] = "Year: ".$this->year;
        if($this->view_type=='day')	$report_title[] = "Month: ".$this->months[$this->month];
        $report_title[] = "View By: ".ucwords($this->view_type);

        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
        //print_r($this->date_label);
        $smarty->assign('date_label', $this->date_label);
        
		// call parent
		parent::process_form();
	}
}

$SALES_REPORT_BY_DAY_MONTH = new SALES_REPORT_BY_DAY_MONTH('Sales Report by Day / Month');
?>
