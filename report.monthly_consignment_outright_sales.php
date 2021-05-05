<?php
/*
8/9/2010 11:51:02 AM Andy
- Add department control.

1/25/2011 4:46:11 PM Alex
- change use report server
- fix date bugs
- add department privilege filter

2/16/2011 3:13:23 PM Alex
- fix bugs on unable to display data if 2011-01-01 fall in 52nd week

6/24/2011 6:17:39 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:41:38 PM Andy
- Change split() to use explode()

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)
- report not to use branch group table anymore, change to individual branch

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/20/2020 2:38 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
//$con = new sql_db('agrofresh-hq.dyndns.org','arms_slave','arms_slave','armshq');
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

class MonthlyConsignmentOutrightSales extends Report
{
	var $where;
	var $groupby;
	var $select;
	
	function run_report($bid, $tbl_name){
        global $con, $smarty, $sessioninfo, $con_multi;
		$where = $this->where;

		$data = $this->data;
		$data_by_sku_type = $this->data_by_sku_type;
		
		$str_week = array(1=> "Mon","Tue","Wed","Thu", "Fri","Sat", "Sun");
		$int_week = array_flip($str_week);

		//$con_multi = new mysql_multi();
		$tbl = $tbl_name['category_sales_cache'];
		$con_multi->sql_query("select date, date_format(date,'%v') as week, date_format(date,'%a') as weekday, sku_type,  sum(amount) as amount from category_cache left join $tbl using (category_id) where $where[date] and $where[category] group by week, weekday, sku_type order by date");
		while($r = $con_multi->sql_fetchrow())
		{
			$data[intval($r['week'])][$int_week[$r['weekday']]] += $r['amount'];
			$data_by_sku_type[$r['sku_type']][intval($r['week'])][$int_week[$r['weekday']]] += $r['amount'];
		}
		$con_multi->sql_freeresult();
		
		$this->data = $data;
		$this->data_by_sku_type = $data_by_sku_type;
	}
	
	function generate_report()
	{
		global $con, $smarty, $sessioninfo, $config, $con_multi;
		$where = $this->where;
		$branch_group = $this->branch_group;
		
		$str_week = array(1=> "Mon","Tue","Wed","Thu", "Fri","Sat", "Sun");
		$int_week = array_flip($str_week);
	
		$con_multi->sql_query("select id, description from category");
		while($r = $con_multi->sql_fetchrow())
		{
			$cat[$r['id']] = $r['description'];
		}
		$con_multi->sql_freeresult();
		
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			
			$con_multi->sql_query("select code from branch_group where id = $bg_id");
			$branch_code = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
			
			//$tbl_name['category_sales_cache'] = "category_sales_cache_bg".$bg_id;
			$branch_ids = $this->get_branch_ids_in_group($bg_id);
			foreach ($branch_ids as $b) {
			
				if ($config['sales_report_branches_exclude']) {
					$curr_branch_code = get_branch_code($b);
					if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
						//print "$curr_branch_code skipped<br />";
						continue;
					}
				}
			
				$tbl_name['category_sales_cache'] = "category_sales_cache_b".$b;
				$this->run_report($b,$tbl_name);
			}
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $tbl_name['category_sales_cache'] = "category_sales_cache_b".$bid;
	            $this->run_report($bid,$tbl_name);
	            $branch_code = BRANCH_CODE;
			}else{
				if($bid==0){
	                $branch_code = "All";
	                $q_b = $con_multi->sql_query("select * from branch where 1 order by sequence,code");
	                while($r = $con_multi->sql_fetchrow($q_b)){
					
						if ($config['sales_report_branches_exclude']) {
							$curr_branch_code = $r['code'];
							if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
								//print "$curr_branch_code skipped<br />";
								continue;
							}
						}
					
                        $tbl_name['category_sales_cache'] = "category_sales_cache_b".$r['id'];
			            $this->run_report($r['id'],$tbl_name);
					}
					$con_multi->sql_freeresult($q_b);
					/*
					if($branch_group['header']){
						foreach($branch_group['header'] as $bg_id=>$bg){
                            $tbl_name['category_sales_cache'] = "category_sales_cache_bg".$bg_id;
				            $this->run_report($bg_id+10000,$tbl_name);
						}
					}
					*/
				}else{
	                $tbl_name['category_sales_cache'] = "category_sales_cache_b".$bid;
		            $this->run_report($bid,$tbl_name);
					$branch_code = get_branch_code($bid);
				}
			}
		}
		
		$data = $this->data;
		$data_by_sku_type = $this->data_by_sku_type;
		
		list($start_year, $start_month, $start_day) = explode("-",$this->date_from);
//		$start_week = intval(date("W",strtotime("$start_year-$start_month-01")));
//		$end_week = intval(date("W",strtotime("$start_year-$start_month-".$this->lastdayofmonth)));

		for($i=1;$i<=$this->lastdayofmonth;$i++)
		{
			$c_label[intval(date("W",strtotime("$start_year-$start_month-$i")))][intval(date("N",strtotime("$start_year-$start_month-$i")))] = $i;
		}

		foreach ($c_label as $week_of_day => $other){
			$total_weeks[$week_of_day]=$week_of_day;
		}

		$s_day = date("d",strtotime("-1 day",strtotime($this->date_from)));
		if ($s_day > $this->lastdayofmonth) $s_day = 0;
		
		$report_title = "Branch: ".$branch_code."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date: ".$_REQUEST['date_from']." to ".$_REQUEST['date_to']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Category: ".$this->cat_desc;
		
		$smarty->assign('report_title',$report_title);
		
		$smarty->assign("start_day" ,date("d", $this->start_date_of_week($start_year,$start_week)));
		$smarty->assign("month_start_day", date("N",strtotime("$year-$month-01")));
		$smarty->assign("cat", $cat);
		$smarty->assign("data", $data);
		$smarty->assign("total_weeks",$total_weeks);
//		$smarty->assign("start_week", $start_week);
//		$smarty->assign("end_week", $end_week);
		$smarty->assign("str_week", $str_week);
		$smarty->assign("c_label", $c_label);
		$smarty->assign("data_by_sku_type",$data_by_sku_type);
        $smarty->assign('branch_code',$branch_code);
	}

	function process_form()
	{
		global $con,$smarty, $sessioninfo ,$LANG, $con_multi;
		$where = array();
		
		// call parent
		//parent::process_form();
		
		$this->date_from=$date_f=$_REQUEST['date_from'];
		$this->date_to=$date_t=$_REQUEST['date_to'];

//		$mtest = strtotime("-1 day", strtotime("+1 month",strtotime($date_f)));
		$year = date("Y",strtotime($date_f));
		$month = date("m",strtotime($date_f));
		$this->lastdayofmonth = $this->daysinmonth($year,$month);
		$mtest = strtotime("$year-$month-".$this->lastdayofmonth);

		if ((strtotime($date_t) > $mtest) || (strtotime($date_t)< strtotime($date_f))){
       		$this->date_to = date("Y-m-d",$mtest);
       		$_REQUEST['date_to'] = $this->date_to;
		}

		$where['date'] = 'date between '.ms($_REQUEST['date_from'])." and ".ms($_REQUEST['date_to']);

		if($_REQUEST['all_category']=='on'){
			$where['category'] = "p2 in ($sessioninfo[department_ids])";
			$this->cat_desc = "All";
		}elseif ($_REQUEST['category_id']){
		    $con_multi->sql_query("select level,description from category where id = ".mi($_REQUEST['category_id']));
			$lv = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$level = $lv['level'];
			$this->cat_desc = $lv['description'];
            $where['category'] = "p$level = ".mi($_REQUEST['category_id']);
		}else{
		    $err[]= $LANG['REPORT_NO_CATEGORY'];
            $smarty->assign('err',$err);
            $this->display();
            exit;

		}
		
		$this->where = $where;

		$smarty->assign('cat_desc',$this->cat_desc);
	}	

	function default_values()
	{
	    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month"));
	    $_REQUEST['date_to'] = date("Y-m-d");
	}
	
	function start_date_of_week($year, $week)
	{
		$Jan1 = mktime(1,1,1,1,1,$year);
		$MondayOffset = (11-date('w',$Jan1))%7-3;
		$desiredMonday = strtotime(($week-1) . ' weeks '.$MondayOffset.' days', $Jan1);
		return $desiredMonday;
	} 
	
	function daysinmonth($year,$month)
	{
		return date("j",mktime(0,0,0,$month+1,0, $year));	
	}
	
	function get_branch_ids_in_group($group_id) {
		global $con, $con_multi;
		
		$br_array = array();
		$q1 = $con_multi->sql_query("select branch_id from branch_group_items where branch_group_id = ". mi($group_id));
		while($r1 = $con_multi->sql_fetchassoc($q1)){
			$br_array[] = $r1['branch_id'];
		}
		$con_multi->sql_freeresult($q1);
		return $br_array;
	}
	
}

$report = new MonthlyConsignmentOutrightSales('Monthly Consignment / Outright Sales');

?>
