<?php
/*
1/21/2011 9:57:24 AM Alex
- change use report_server

12/22/2011 9:42:55 AM Andy
- Fix got sql error when choose all branches.

2/4/2013 5:34 PM Justin
- Enhanced to show and filter branches from regions or branch group base on user's regions.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/3/2020 2:52 PM Andy
- Enhanced all connection to use report server.

2/21/2020 9:13 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (BRANCH_CODE != 'HQ') js_redirect($LANG['REPORT_IS_HQ_ONLY'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

class YearlySalesByBranch extends Report
{
	var $where;
	
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		// load branch group items
		$q1 = $con_multi->sql_query("select bgi.*,branch.code,branch.description
							   from branch_group_items bgi
							   left join branch on bgi.branch_id=branch.id
							   where branch.active=1
							   order by branch.sequence, branch.code");
		while($r = $con_multi->sql_fetchassoc($q1)){
			if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['branch_id'])) continue;
			$this->branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
			$this->branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con_multi->sql_freeresult($q1);
		
		// load branch group header
		$q1 = $con_multi->sql_query("select * from branch_group",false,false);
		while($r = $con_multi->sql_fetchassoc($q1)){
			if(!$this->branch_group['items'][$r['id']]) continue;
            $this->branch_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);
		
		$smarty->assign("branch_group", $this->branch_group);
		
		parent::__construct($title);
	}
	
	function run_report($bid,&$data,&$target_var,&$var,&$target,$tbl_name){
	    global $con,$smarty,$sessioninfo, $con_multi;
        $where = $this->where;
	
		$tbl = $tbl_name['category_sales_cache'];
		$con_multi->sql_query("select year, month, sum(amount) as amount from category left join $tbl tbl on category.id = tbl.category_id where  $where[year] and $where[month] and $where[department] and department_id in ($sessioninfo[department_ids]) group by year, month") or die(mysql_error());

		while ($r = $con_multi->sql_fetchrow())
		{
			$data[$bid][$r['year']][$r['month']] = $r['amount'];
			$yy[$r['year']] = 1;
		}
		$con_multi->sql_freeresult();

        $tbl = $tbl_name['sales_target'];
	
		$con_multi->sql_query("select year, month, sum(target) as target from category left join $tbl tbl on category.id = tbl.department_id where $where[year2] and $where[month] and $where[department] and category.department_id in ($sessioninfo[department_ids]) group by year, month",false,false);
		while ($r = $con_multi->sql_fetchrow())
		{
			$target[$bid][$r['month']] = $r['target'];
		}
		$con_multi->sql_freeresult();
	}
	
	function generate_report()
	{
		global $con, $smarty, $sessioninfo, $config, $con_multi;
		
		//$branch_group = $this->branch_group;
		
		$selected_bg_id = $_REQUEST['branch_group'];
		//print "selected_bg_id = $selected_bg_id<br />";
		
		if(!$selected_bg_id){ // all
            // run single branch
			$rs = $con_multi->sql_query("select * from branch order by sequence, code");
			$branches = $con_multi->sql_fetchrowset($rs);
			$con_multi->sql_freeresult($rs);
			if($branches){
                foreach($branches as $branch){
					if ($config['sales_report_branches_exclude']) {
						$branch_code = $branch['code'];
						if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
							//print "$branch_code skipped<br />";
							continue;
						}
					}
                    //if($branch_group && in_array($branch['id'], $branch_group['have_group'])) continue;
                    if($config['masterfile_branch_region'] && $branch['code'] != "HQ" && !check_user_regions($branch['id'])) continue;
                    // initial cache table
                    update_sales_cache($branch['id'], -1);
                    
                    $tbl_name['category_sales_cache'] = "category_sales_cache_b".mi($branch['id']);
                    $tbl_name['sales_target'] = "sales_target_b".mi($branch['id']);
                    $this->run_report($branch['id'],$data,$target_var,$var,$target,$tbl_name);
				}
			}
			
			// run branches group
			/*if($branch_group['header']){
				foreach($branch_group['header'] as $bg_id=>$bg){
				    $tbl_name['category_sales_cache'] = "category_sales_cache_bg".mi($bg['id']);
                    $tbl_name['sales_target'] = "sales_target_bg".mi($bg['id']);
                    $this->run_report($bg_id+10000,$data,$target_var,$var,$target,$tbl_name);
				}
			}*/
		}else{  // selected branch group
			if(preg_match("/^REGION_/", $selected_bg_id)){
				$region = str_replace("REGION_", "", $selected_bg_id);
				$q1 = $con_multi->sql_query("select b.* from branch b where b.active = 1 and b.region = ".ms($region));

				while($r = $con_multi->sql_fetchassoc($q1)){
					if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
					
					if ($config['sales_report_branches_exclude']) {
						$branch_code = $r['code'];
						if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
							//print "$branch_code skipped<br />";
							continue;
						}
					}
	
					// initial cache table
					update_sales_cache($r['id'], -1);
					
					$tbl_name['category_sales_cache'] = "category_sales_cache_b".mi($r['id']);
					$tbl_name['sales_target'] = "sales_target_b".mi($r['id']);
					$this->run_report($r['id'],$data,$target_var,$var,$target,$tbl_name);
				}
				$con_multi->sql_freeresult($q1);
			}else{
				foreach($this->branch_group['items'][$selected_bg_id] as $bid=>$b){
				
					if ($config['sales_report_branches_exclude']) {
						$branch_code = $b['code'];
						if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
							//print "$branch_code skipped<br />";
							continue;
						}
					}
				
					// initial cache table
					update_sales_cache($bid, -1);
					
					$tbl_name['category_sales_cache'] = "category_sales_cache_b".mi($bid);
					$tbl_name['sales_target'] = "sales_target_b".mi($bid);
					$this->run_report($bid,$data,$target_var,$var,$target,$tbl_name);
				}
			}
		}
		
		// single branch
		if ($data)
		{
			$str_month = array();
			for($i=1;$i<=12;$i++)
			{
				$str_month[$i] = str_month($i);
				foreach ($data as $branch => $year)
				{
					$var[$branch][$i] = floatval($data[$branch][$_REQUEST['year']][$i]) - floatval($data[$branch][$_REQUEST['year']-1][$i]);
				}
				
				if($target)
				{
            foreach ($target as $branch => $t)
    				{
    					if ($data[$branch][$_REQUEST['year']][$i] > 0)
    					$target_var[$branch][$i] = floatval($data[$branch][$_REQUEST['year']][$i]) - floatval($t[$i]);
    				}
        }
				
			}
	   
			$smarty->assign("target_var",$target_var);
			$smarty->assign("var",$var);
			$smarty->assign("target",$target);
			$smarty->assign("data", $data);
		}
	}
	
	function process_form()
	{
	    global $con,$smarty,$sessioninfo, $con_multi;

		// do my own form process
		$where = array();
		$where['department'] = $_REQUEST['department_id']?'category.department_id = '.mi($_REQUEST['department_id']):1;
		if(intval($_REQUEST['department_id'])==0){
			$dept_desc = "All";
		}else{
            $con_multi->sql_query("select description from category where id=".mi(($_REQUEST['department_id']))) or die(mysql_error());
			$dept_desc = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
		}
		
		$where['year'] = "(year between ".mi($_REQUEST['year']-1)." and ".mi($_REQUEST['year']).")";
		$where['year2'] = "year = ".mi($_REQUEST['year']);
		$where['month'] = "(month between ".mi($_REQUEST['month_from'])." and ".mi($_REQUEST['month_to']).")";
    
		$this->where = $where;
		
		$months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
		
    	//$this->load_branch_group;

		$report_title = "Year: ".$_REQUEST['year']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Month:".$months[$_REQUEST['month_from']]." to ".$months[$_REQUEST['month_to']]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Department:".$dept_desc;
		// load header
	    if($_REQUEST['branch_group']){
			if(preg_match("/^REGION_/", $_REQUEST['branch_group'])){
				$b_title = "Region";
				$b_name = str_replace("REGION_", "", $_REQUEST['branch_group']);
			}else{
				$b_title = "Branch Group";
				$con_multi->sql_query("select * from branch_group where id = ".mi($_REQUEST['branch_group']),false,false);

				while($r = $con_multi->sql_fetchrow()){
					$branch_group['header'][$r['id']] = $r;
				}
				$con_multi->sql_freeresult();
				$b_name = $branch_group['header'][$_REQUEST['branch_group']]['code'];
			}
			$report_title = $report_title."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$b_title.": ".$b_name;
	    }

    	$smarty->assign('report_title',$report_title);
		$smarty->assign('dept_desc',$dept_desc);
	
		// call parent
		parent::process_form();
	}	
}

//$con_multi = new mysql_multi();
$report = new YearlySalesByBranch('Yearly Sales Comparison by Branch');
//$con_multi->close_connection();
?>
