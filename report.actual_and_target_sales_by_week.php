<?php
/*
1/17/2011 2:52:16 PM Alex
- change use report_server

5/24/2011 12:30:35 PM Alex
- fix YTD total actual sales bugs

6/24/2011 5:52:36 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:16:26 PM Andy
- Change split() to use explode()

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)
- report not to use branch group table anymore, change to individual branch

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/3/2020 1:56 PM Andy
- Enhanced all connection to use report server.
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

class ActualandTargetSalesbyWeek extends Report
{
	var $where;
	var $groupby;
	var $select;
	
	function run_report($bid,$tbl_name){
        global $con, $smarty, $sessioninfo, $con_multi;

		$where = $this->where;

		$totalbybranch = $this->totalbybranch;
		$data = $this->data;
		$pretotalbybranch = $this->pretotalbybranch;
		$predata = $this->predata;
		$totaltargetbybranch = $this->totaltargetbybranch;
		$target = $this->target;
		$branch_list = $this->branch_list;

		$tbl = $tbl_name['category_sales_cache'];
		$con_multi->sql_query("select ".$this->select." year, sum(amount) as amount from category left join $tbl tbl on category.id = tbl.category_id where $where[date] $where[department] group by ".$this->groupby) or die(mysql_error());

		while ($r = $con_multi->sql_fetchrow())
		{
			if ($r[0] != 0)
			{
				$totalbybranch[$bid] += $r['amount'];
				if ($_REQUEST['view_type'] == 'week')
				{
					$dt = date("Y-m-d", $this->start_date_of_week($r['year'], date("W", strtotime($r[0]))));
					$data[$dt][$bid] += $r['amount'];
				}
				else
				$data[$r[0]][$bid] = $r['amount'];
			}
		}
		$con_multi->sql_freeresult();

        $tbl = $tbl_name['category_sales_cache'];
		$con_multi->sql_query("select ".$this->select." year, sum(amount) as amount from category left join $tbl tbl on category.id = tbl.category_id where $where[date2] $where[department] group by ".$this->groupby) or die(mysql_error());
		
		while ($r = $con_multi->sql_fetchrow())
		{
			if ($r[0] != 0)
			{
				$pretotalbybranch[$bid] += $r['amount'];
				if ($_REQUEST['view_type'] == 'week')
				{
					$r[0] = date("Y-m-d", strtotime("+1 year", strtotime($r[0])));
					$r['year'] = date("Y", strtotime($r[0]));
					$dt = date("Y-m-d", $this->start_date_of_week($r['year'], date("W", strtotime($r[0]))));

					$predata[$dt][$bid] += $r['amount'];
				}
				else
				$predata[date("Y-m-d", strtotime("+1 year", strtotime($r[0])))][$bid] = $r['amount'];
			}
		}
		$con_multi->sql_freeresult();

		$tbl = $tbl_name['sales_target'];
		$con_multi->sql_query("select ".$this->select." year, sum(target) as amount from category left join $tbl tbl on category.id = tbl.department_id where $where[date] $where[department] group by ".$this->groupby,false,false);
		while ($r = $con_multi->sql_fetchrow())
		{
			if ($r[0] != 0)
			{
				$totaltargetbybranch[$bid] += $r['amount'];
				if ($_REQUEST['view_type'] == 'week')
				{
					$dt = date("Y-m-d", $this->start_date_of_week($r['year'], date("W", strtotime($r[0]))));
					$target[$dt][$bid] += $r['amount'];
				}
				else
				$target[$r[0]][$bid] = $r['amount'];
			}
		}
		$con_multi->sql_freeresult();
		$branch_list[$bid] = $bid;
		
		if($data)		ksort($data);
		
		$this->totalbybranch = $totalbybranch;
		$this->data = $data;
		$this->pretotalbybranch = $pretotalbybranch;
		$this->predata = $predata;
		$this->totaltargetbybranch = $totaltargetbybranch;
		$this->target = $target;
		$this->branch_list = $branch_list;
	}
	
	function generate_report()
	{
		global $con, $smarty, $sessioninfo, $config, $con_multi;
		$branch_group = $this->branch_group;
		
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			foreach($branch_group['items'][$bg_id] as $bid=>$r){
			
				if ($config['sales_report_branches_exclude']) {
					$curr_branch_code = $r['code'];
					if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
						// print "$curr_branch_code skipped<br />";
						continue;
					}
				}
			
				$tbl_name['category_sales_cache'] = "category_sales_cache_b".mi($bid);
			    $tbl_name['sales_target'] = "sales_target_b".mi($bid);
				$this->run_report($bid,$tbl_name);
			}
			
		}else{
            $bid  = get_request_branch(true);
			
			if($bid>0){ // selected single branch
			    $tbl_name['category_sales_cache'] = "category_sales_cache_b".mi($bid);
			    $tbl_name['sales_target'] = "sales_target_b".mi($bid);
				$this->run_report($bid,$tbl_name);
			}else{  // all
                $rs = $con_multi->sql_query("select * from branch where /*code <> 'HQ' and*/ active=1 order by sequence,code");
				$branches = $con_multi->sql_fetchrowset($rs);
				$con_multi->sql_freeresult();
				
				if($branches){
	                foreach($branches as $branch){
					
						if ($config['sales_report_branches_exclude']) {
							$curr_branch_code = $branch['code'];
							if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
								// print "$curr_branch_code skipped<br />";
								continue;
							}
						}
					
	                    //if(in_array($branch['id'],$branch_group['have_group'])) continue;
	                    $tbl_name['category_sales_cache'] = "category_sales_cache_b".mi($branch['id']);
	                    $tbl_name['sales_target'] = "sales_target_b".mi($branch['id']);
	                    $this->run_report($branch['id'],$tbl_name);
					}
				}
				
				/*
				print '<pre>';
				print_r($branch_group);
				print '</pre>';
				
				// run branches group
				if($branch_group['header']){
					foreach($branch_group['header'] as $bg_id=>$bg){
					    $tbl_name['category_sales_cache'] = "category_sales_cache_bg".mi($bg['id']);
					    $tbl_name['sales_target'] = "sales_target_b".mi($bg['id']);
	                    $this->run_report($bg_id+10000,$tbl_name);
					}
				}
				*/
			}
		}
		
		$totalbybranch = $this->totalbybranch;
		$data = $this->data;
		$pretotalbybranch = $this->pretotalbybranch;
		$predata = $this->predata;
		$totaltargetbybranch = $this->totaltargetbybranch;
		$target = $this->target;
		$branch_list = $this->branch_list;
		
  		
		foreach ($branch_list as $bid)
		{
			if ($_REQUEST['view_type'] == 'week')
			{
				for($i=0;$i<366;$i=$i+7)
				{
					$week2 = date("W", strtotime($this->date_from));
					$dt = date("Y-m-d",strtotime("+$i day", $this->start_date_of_week(date("Y", strtotime($this->date_from)),$week2)));
					if($dt>$this->date_to)  break;
					$y_axis[$dt] = $dt;
					$actual_var[$dt][$bid] = $data[$dt][$bid]-$predata[$dt][$bid];
					$total_actual[$bid] += $actual_var[$dt][$bid];
					if ($predata[$dt][$bid]>0) $actual_var_p[$dt][$bid] = $actual_var[$dt][$bid] / $predata[$dt][$bid] * 100;
					$target_var[$dt][$bid] = $data[$dt][$bid]-$target[$dt][$bid];
					$total_target[$bid] += $target_var[$dt][$bid];
					if ($target[$dt][$bid]>0) $target_var_p[$dt][$bid] = $target_var[$dt][$bid] / $target[$dt][$bid] * 100;
					$ytd_total[$dt][$bid] = $ytd_ttl[$bid] + $data[$dt][$bid];
					$ytd_target[$dt][$bid] = $ytd_tgt[$bid] + $target[$dt][$bid];
					$ytd_var[$dt][$bid] = $ytd_total[$dt][$bid] - $ytd_target[$dt][$bid];
					if ($ytd_target[$dt][$bid]>0) $ytd_var_p[$dt][$bid] = $ytd_var[$dt][$bid]/$ytd_target[$dt][$bid]*100;
					$ytd_ttl[$bid] += $data[$dt][$bid];
					$ytd_tgt[$bid] += $target[$dt][$bid];
				}		
			}
			else
			{
				for($i=0;$i<366;$i++)
				{
					$dt = date("Y-m-d", strtotime("+$i day", strtotime($this->date_from)));
					if($dt>$this->date_to)  break;
					$y_axis[$dt] = $dt;
					$actual_var[$dt][$bid] = $data[$dt][$bid]-$predata[$dt][$bid];
					$total_actual[$bid] += $actual_var[$dt][$bid];
					if ($predata[$dt][$bid]>0) $actual_var_p[$dt][$bid] = $actual_var[$dt][$bid] / $predata[$dt][$bid] * 100;
					$target_var[$dt][$bid] = $data[$dt][$bid]-$target[$dt][$bid];
					$total_target[$bid] += $target_var[$dt][$bid];
					if ($target[$dt][$bid]>0) $target_var_p[$dt][$bid] = $target_var[$dt][$bid] / $target[$dt][$bid] * 100;
					$ytd_total[$dt][$bid] = $ytd_ttl[$bid] + $data[$dt][$bid];
					$ytd_target[$dt][$bid] = $ytd_tgt[$bid] + $target[$dt][$bid];
					$ytd_var[$dt][$bid] = $ytd_total[$dt][$bid] - $ytd_target[$dt][$bid];
					if ($ytd_target[$dt][$bid]>0) $ytd_var_p[$dt][$bid] = $ytd_var[$dt][$bid]/$ytd_target[$dt][$bid]*100;
					$ytd_ttl[$bid] += $data[$dt][$bid];
					$ytd_tgt[$bid] += $target[$dt][$bid];
				}
			}
		}
		
		//print_r($predata);
		$smarty->assign("ytd_total", $ytd_total);
		$smarty->assign("ytd_target", $ytd_target);
		$smarty->assign("ytd_var", $ytd_var);
		$smarty->assign("ytd_var_p", $ytd_var_p);
		$smarty->assign("total_actual", $total_actual);
		$smarty->assign("total_actual_p", $total_actual_p);
		$smarty->assign("total_target", $total_target);
		$smarty->assign("total_target_p", $total_target_p);
		$smarty->assign("y_axis",$y_axis);
		$smarty->assign("actual_var", $actual_var);
		$smarty->assign("actual_var_p", $actual_var_p);
		$smarty->assign("target_var", $target_var);
		$smarty->assign("target_var_p", $target_var_p);
		$smarty->assign("branch_list", $branch_list);
		$smarty->assign("totalbybranch",$totalbybranch);
		$smarty->assign("data", $data);
		$smarty->assign("wk", $wk);
		$smarty->assign("pretotalbybranch",$pretotalbybranch);
		$smarty->assign("predata", $predata);
		$smarty->assign("prewk", $prewk);
		$smarty->assign("totaltargetbybranch",$totaltargetbybranch);
		$smarty->assign("target", $target);

	}
	
	function process_form()
	{
		global $config,$sessioninfo,$con,$smarty,$con_multi;
		
//		$start_year = $config['financial_start_date'];
//		do my own form process
		

		$where = array();
		
		// call parent
		parent::process_form();

		$mtest =strtotime("+1 year",strtotime($this->date_from));
		if (strtotime($this->date_to) > $mtest){
           $this->date_to = date("Y-m-d",$mtest);
           $_REQUEST['date_to'] = $this->date_to;
		}
		
		$where['department'] = '';
		if(intval($_REQUEST['department_id'])==0){
            if ($sessioninfo['level']<9999) $where['department'] = "and category.department_id in ($sessioninfo[department_ids])";
            $dept_desc = "All";
		}else{
            $where['department'] = 'and category.department_id = '.mi($_REQUEST['department_id']);
            $con_multi->sql_query("select description from category where id=".mi(($_REQUEST['department_id']))) or die(mysql_error());
			$dept_desc = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
		}
		
		//$where['department'] = $_REQUEST['department_id']?'category.department_id = '.mi($_REQUEST['department_id']):1;
		$where['date'] = "date between ".ms($this->date_from)." and ".ms($this->date_to);
		$where['date2'] = "date between ".ms(date("Y-m-d",strtotime("-1 year",strtotime($this->date_from))))." and ".ms(date("Y-m-d",strtotime("-1 year",strtotime($this->date_to))));
		
		$this->select = 'date,';
		$this->groupby = "year, month, date having date <> 'null'";

		$this->where = $where;
		
		$report_title = "Date: ".$_REQUEST['date_from']." to ".$_REQUEST['date_to']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Department: ".$dept_desc."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Type: ".ucwords($_REQUEST['view_type']);
		
		$smarty->assign('report_title',$report_title);
		
		$smarty->assign('dept_desc',$dept_desc);
	}	
	
	function start_date_of_week($year, $week)
	{
		$Jan1 = mktime(1,1,1,1,1,$year);
		$MondayOffset = (11-date('w',$Jan1))%7-3;
		$desiredMonday = strtotime(($week-1) . ' weeks '.$MondayOffset.' days', $Jan1);
		return $desiredMonday;
	}

	function default_values()
	{
	    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 year"));
	    $_REQUEST['date_to'] = date("Y-m-d");
	}
}

/*$con_multi = new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}*/

$report = new ActualandTargetSalesbyWeek('Actual and Target Sales by Day / Week');
//$con_multi->close_connection();
?>
