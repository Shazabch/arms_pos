<?php
/*
5/3/2010 5:30:26 PM Alex
- fix report price not same with category sales report 

1/17/2011 5:06:49 PM Alex
- change use report_server

6/24/2011 5:54:34 PM Andy
- Make all branch default sort by sequence, code.

7/7/2011 3:29:57 PM Andy
- Fix report din't filter by user department when choose 'all'.

8/3/2012 10:24:34 AM Justin
- Bug fixed for branch group when selected as all, system shows zero amount for each branch group.

10/10/2012 2:50 PM Justin
- Bug fixed on system create duplicate rows when show by week.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

4:10 PM 11/27/2014 Andy
- Fix report checking branch group warning.

2/20/2020 9:20 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");
if (BRANCH_CODE != 'HQ') js_redirect($LANG['REPORT_IS_HQ_ONLY'], "/index.php");
//$con = new sql_db('gmark-hq.arms.com.my:4001','arms','4383659','armshq');

class BranchesSalesByDay extends Report
{
	var $where;
	var $groupby;
	var $select;
	
	function run_report($bid,$tbl_name){
	    global $con, $con_multi, $smarty, $sessioninfo;
	    $where = $this->where;
	    $tbl = $tbl_name['category_sales_cache'];
	    
        // Start of Financial YTD
		$con_multi->sql_query("select ".$this->select." sum(amount) as amount from category left join $tbl tbl on category.id = tbl.category_id where $where[financial_date] and $where[department] group by ".$this->groupby, false, false);

		while ($r = $con_multi->sql_fetchrow())
		{
			if ($r[0] != 0)
			{
				$this->ytd_amount += $r['amount'];
			}
		}
		$con_multi->sql_freeresult();

        // End of Financial YTD
        $cross_year = 0;
		$con_multi->sql_query("select ".$this->select." sum(amount) as amount from $tbl tbl left join category on category.id = tbl.category_id where $where[date] and $where[department] group by ".$this->groupby, false,false);
				
		while ($r = $con_multi->sql_fetchrow())
		{
		    //print $r[0];
			if ($r[0])
			{
				$this->totalbybranch[$bid] += $r['amount'];
				$this->totalbybranch['total'] += $r['amount'];
				$this->ytd[$bid] += $r['amount'];

				if ($_REQUEST['view_type'] == 'week')
				{
					$this->data[$r['year2']][$r[0]][$bid] += $r['amount'];
					$this->data[$r['year2']][$r[0]]['total'] += $r['amount'];
					$this->wk[$r['year2']][$r[0]] = $this->getFirstDayOfWeek($r['year2'], $r['week2']);
				}
				else
				{
					$this->data[$r[0]][$bid] += $r['amount'];
					$this->data[$r[0]]['total']+= $r['amount'];
				}
			}
		}
		$con_multi->sql_freeresult();
	}
	
	function generate_report()
	{
		global $con, $smarty, $sessioninfo, $config, $con_multi;
		$branch_group = $this->branch_group;
		$selected_bg_id = intval($_REQUEST['branch_group']);
		
		if($selected_bg_id==0){ // all
            // run single branch
			$rs = $con_multi->sql_query("select * from branch where code <> 'HQ' order by sequence,code");
			$branches = $con_multi->sql_fetchrowset($rs);
			$con_multi->sql_freeresult($rs);
			if($branches){
                foreach($branches as $branch){
                    if($branch_group['have_group'] && in_array($branch['id'],$branch_group['have_group'])) continue;
					if ($config['sales_report_branches_exclude']) {
						$branch_code = $branch['code'];
						if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
							//print "$branch_code skipped<br />";
							continue;
						}
					}
                    $tbl_name['category_sales_cache'] = "category_sales_cache_b".mi($branch['id']);
                    $this->run_report($branch['id'],$tbl_name);
				}
			}
			
			// run branches group
			if($branch_group['header']){
				foreach($branch_group['header'] as $bg_id=>$bg){
					if($branch_group['items'][$bg_id]){
						foreach($branch_group['items'][$bg_id] as $bid=>$b){
							if ($config['sales_report_branches_exclude']) {
								$branch_code = $b['code'];
								if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
									//print "$branch_code skipped<br />";
									continue;
								}
							}
							$tbl_name['category_sales_cache'] = "category_sales_cache_b".mi($bid);
							$this->run_report($bg_id+10000,$tbl_name);
						}
					}
				}
			}
		}else{  // selected branch group
            foreach($branch_group['items'][$selected_bg_id] as $bid=>$b){
				if ($config['sales_report_branches_exclude']) {
					$branch_code = $b['code'];
					if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
						//print "$branch_code skipped<br />";
						continue;
					}
				}
                $tbl_name['category_sales_cache'] = "category_sales_cache_b".mi($bid);
                $this->run_report($bid,$tbl_name);
			}
		}
		
		if($this->data){
            ksort($this->data);
			if ($_REQUEST['view_type'] == 'week')
			{
				foreach($this->data as $y=>$d)
				{
					ksort($d);
					$data2[$y] = $d;
				}
				$this->data = $data2;
			}
		}
		
		$smarty->assign("totalbybranch",$this->totalbybranch);
		$smarty->assign("data", $this->data);
		$smarty->assign("wk", $this->wk);
        $smarty->assign("ytd_amount", $this->ytd_amount);
	}
	
	function process_form()
	{
		global $config,$smarty,$con,$sessioninfo,$con_multi;
		
//		$start_year = $config['financial_start_date'];
		// do my own form process
		
		$mtest =strtotime("+1 year",strtotime($this->date_from));
		if (strtotime($this->date_to) > $mtest){
           $this->date_to = date("Y-m-d",$mtest);
           $_REQUEST['date_to'] = $this->date_to;
		}
		$where = array();
		
		// call parent
		parent::process_form();
		
		// YTD
		$year = date("Y",strtotime($this->date_from));

		$financial_start_date = $year.$config['financial_start_date'];
		if(strtotime($this->date_from)<strtotime($financial_start_date)){
			$year--;
			$financial_start_date = $year.$config['financial_start_date'];
		}
		$start_date = $financial_start_date;
		$financial_end_date = date("Y-m-d",strtotime("+1 year",strtotime($financial_start_date)));
		$end_date = date("Y-m-d",strtotime("-1 day",strtotime($this->date_from)));
		$where['financial_date'] = "date between ".ms($start_date)." and ".ms($end_date);
		
		$smarty->assign('financial_start_date',$financial_start_date);
		$smarty->assign('financial_end_date',$financial_end_date);
		// End of YTD
		
		if(intval($_REQUEST['department_id'])==0){
			$dept_desc = "All";
			$where['department'] = 'category.department_id in ('.$sessioninfo['department_ids'].')';
		}else{
            $con_multi->sql_query("select description from category where id=".mi(($_REQUEST['department_id']))) or die(mysql_error());
			$dept_desc = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
			$where['department'] = 'category.department_id = '.mi($_REQUEST['department_id']);
		}
		
		$where['date'] = "date between ".ms($this->date_from)." and ".ms($this->date_to);
		
		if ($_REQUEST['view_type'] == 'day')
		{
			$this->select = 'date,';
			$this->groupby = "year, month, date having date <> 'null'";
		}
		else
		{
			$this->select = "DATE_FORMAT(date, 'Y%x W%v') as week,year,date,DATE_FORMAT(date, '%x') as year2,DATE_FORMAT(date, '%v') as week2,  ";
			$this->groupby = "week order by date";
		}
		$this->where = $where;
		
		// load header
		$con_multi->sql_query("select * from branch_group $where",false,false);
		if($con_multi->sql_numrows()<=0) return;
		while($r = $con_multi->sql_fetchrow()){
            $branch_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		
		$report_title = "Financial Start Year : ".$financial_start_date."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date:".$_REQUEST['date_from']." to ".$_REQUEST['date_to']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Department: ".$dept_desc."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Type:".$_REQUEST['view_type'];
		
		if($_REQUEST['branch_group'])
		{
        $report_title = $report_title."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Branch Group: ".$branch_group['header'][$_REQUEST['branch_group']]['code'];
    }
		
		$smarty->assign('report_title',$report_title);
		$smarty->assign('dept_desc',$dept_desc);
	}	
	function default_values()
	{
	    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 year"));
	    $_REQUEST['date_to'] = date("Y-m-d");
	}
}

/*$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}*/

$report = new BranchesSalesByDay('Branches Sales Comparison by Day / Week');

/*$con_multi->close_connection();*/
?>
