<?php
/*
5/3/2010 6:51:25 PM Alex
- fix report columns

1/25/2011 3:25:58 PM Alex
- change use report_server

6/24/2011 6:19:44 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:42:57 PM Andy
- Change split() to use explode()

8/1/2012 3:03 PM Justin
- Fixed bug of showing sql error while filter by branch group.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

11/27/2015 9:17 AM Qiu Ying
- Make it same as select Branch filter from "Sales report>Daily Category Sales Report" 

11/30/2015 10:51 AM Qiu Ying
- Fixed report title cannot show branch group

12/1/2016 10:23 AM Andy
- Fixed total amount not tally with monthly amount, fixed by always display all the months.

2/20/2020 4:05 PM William 
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
include("include/class.report.php");
ini_set("display_errors",0);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

class MonthlySalesByCategory extends Report
{
	var $where;
	var $groupby;
	var $select;

	function run_report($bid, $tbl_name){
        global $con_multi, $smarty, $sessioninfo;
		$where = $this->where;
		
		$data = $this->data;
		$ttl_month = $this->ttl_month;
		$ttl_sku_type = $this->ttl_sku_type;
		$total_sales = $this->total_sales;
		$sales_target = $this->sales_target;
		$sales_target_sku_type = $this->sales_target_sku_type;
		$total_sales_target = $this->total_sales_target;
		
		$tbl = $tbl_name['category_sales_cache'];
		$con_multi->sql_query("select year, month, sku_type, sum(amount) as amount from category_cache left join $tbl using (category_id) where $where[date] and $where[department] group by year, month, sku_type") or die(mysql_error());
  
    
  		while($r = $con_multi->sql_fetchrow())
		{
			$data[$r['year']][$r['month']] = 1;
			$ttl_month[$r['year']][$r['month']] += $r['amount'];
			$ttl_sku_type[$r['sku_type']][$r['year']][$r['month']] += $r['amount'];
			$total_sales[$r['year']] += $r['amount'];
		}
		$con_multi->sql_freeresult();

    	$tbl = $tbl_name['sales_target'];
		$con_multi->sql_query("select year, month, sku_type, sum(target) as amount from category_cache left join $tbl tbl on category_cache.category_id = tbl.department_id where $where[date] and $where[department] group by year, month, sku_type",false,false);
		while($r = $con_multi->sql_fetchrow())
		{
			$sales_target[$r['year']][$r['month']] += $r['amount'];
			$sales_target_sku_type[$r['sku_type']][$r['year']][$r['month']] += $r['amount'];
			$total_sales_target[$r['year']] += $r['amount'];
		}
		$con_multi->sql_freeresult();
		
		$this->data = $data;
		$this->ttl_month = $ttl_month;
		$this->ttl_sku_type = $ttl_sku_type;
		$this->total_sales = $total_sales;
		$this->sales_target = $sales_target;
		$this->sales_target_sku_type = $sales_target_sku_type;
		$this->total_sales_target = $total_sales_target;
		
	}
	
	function generate_report()
	{
		global $con, $smarty, $sessioninfo, $config, $con_multi;
		$where = $this->where;
		$branch_group = $this->branch_group;
		$con_multi->sql_query("select id, description from category");
		while($r = $con_multi->sql_fetchrow())
		{
			$cat[$r['id']] = $r['description'];
		}
		$con_multi->sql_freeresult();
		
		$this->data[$_REQUEST['year']][$_REQUEST['month']] = 1;
		$this->data[$_REQUEST['year']+1][$_REQUEST['month']] = 1;
		if ($_REQUEST['branch_id'] < 0){
			$bgid = abs($_REQUEST['branch_id']);
			if($branch_group['items'][$bgid]){
				foreach($branch_group['items'][$bgid] as $bid=>$b){
					if ($config['sales_report_branches_exclude']) {
						$branch_code = $b['code'];
						if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
							//print "$branch_code skipped<br />";
							continue;
						}
					}
					$tbl_name['category_sales_cache'] = "category_sales_cache_b".$bid;
					$tbl_name['sales_target'] = "sales_target_b".$bid;
					$this->run_report($bid,$tbl_name);
				}
			}
			$report_title[] = "Branch Group: " . $branch_group['header'][$bgid]['code'];
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $tbl_name['category_sales_cache'] = "category_sales_cache_b".$bid;
				$tbl_name['sales_target'] = "sales_target_b".$bid;
	            $this->run_report($bid,$tbl_name);
	            $branch_code = BRANCH_CODE;
				$report_title[] = "Branch: ".BRANCH_CODE;
			}else{
				if($bid==0){
	                $branch_code = "All";
	                $q_b = $con_multi->sql_query("select * from branch where active=1 order by sequence,code");
	                while($r = $con_multi->sql_fetchrow($q_b)){
						if ($config['sales_report_branches_exclude']) {
							$branch_code = $r['code'];
							if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
								//print "$branch_code skipped<br />";
								continue;
							}
						}
                        $tbl_name['category_sales_cache'] = "category_sales_cache_b".$r['id'];
						$tbl_name['sales_target'] = "sales_target_b".$r['id'];
			            $this->run_report($r['id'],$tbl_name);
					}
					$con_multi->sql_freeresult($q_b);
					$report_title[] = "Branch: All";
				}else{
	                $tbl_name['category_sales_cache'] = "category_sales_cache_b".$bid;
					$tbl_name['sales_target'] = "sales_target_b".$bid;
		            $this->run_report($bid,$tbl_name);
					$branch_code = get_branch_code($bid);
					$report_title[] = "Branch: ".get_branch_code($bid);
			
				}
			}
		}
			
        $data = $this->data;
		$ttl_month = $this->ttl_month;
		$ttl_sku_type = $this->ttl_sku_type;
		$total_sales = $this->total_sales;
		$sales_target = $this->sales_target;
		$sales_target_sku_type = $this->sales_target_sku_type;
		$total_sales_target = $this->total_sales_target;
		
		/*for($i=$_REQUEST['month'];$i<=12;$i++)
		{
			$mth[] = $i;
		}*/
		$mth = array(1,2,3,4,5,6,7,8,9,10,11,12);
	   
    	$months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
		
		$con_multi->sql_query("select id,description from category where id in ($sessioninfo[department_ids]) order by description");

	    while($r = $con_multi->sql_fetchrow())
	    {
	        $rs[$r['id']] = $r['description'];
	    }
		$con_multi->sql_freeresult();

	    foreach($_REQUEST['department_id'] as $dept_id)
	    {
	        if($str_dept) $str_dept.=" , ";
	        $str_dept .= $rs[$dept_id];
	    }
		if(!$str_dept) $str_dept = "All";
		$report_title[] = "Year: ".$_REQUEST['year'];
		$report_title[] = "Month: ".$months[$_REQUEST['month']];
		$report_title[] = "Department: ".$str_dept;
		
		$smarty->assign('report_title',join("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $report_title));
     
		$smarty->assign("total_sales", $total_sales);
		$smarty->assign("total_sales_target", $total_sales_target);
		$smarty->assign("sales_target", $sales_target);
		$smarty->assign("ttl_month", $ttl_month);
		$smarty->assign("sales_target_sku_type", $sales_target_sku_type);
		$smarty->assign("ttl_sku_type", $ttl_sku_type);
		$smarty->assign("mth", $mth);
		$smarty->assign("cat", $cat);
		$smarty->assign("data", $data);
		//$smarty->assign('branch_code',$branch_code);
	}

	
	function process_form()
	{
	    global $con,$smarty;
	    
		$where = array();
		
		// call parent
		parent::process_form();
		
		$date_from = date("Y-m-d", strtotime("$_REQUEST[year]-$_REQUEST[month]-1"));
		$date_to = date("Y-m-d", strtotime("-1 day", strtotime("+2 year",strtotime("$_REQUEST[year]-$_REQUEST[month]-1"))));
		$where['date'] = "date between ".ms($date_from)." and ".ms($date_to);

		if (isset($_REQUEST['department_id']))
		{
			if (is_array($_REQUEST['department_id']))
			{
				if (trim(join("",$_REQUEST['department_id']) != ''))
				$where['department'] = "p2 in (".join(",", $_REQUEST['department_id']).")";
				else
				$where['department'] = 1;
			}
			else
			{
				$where['department'] = "p2 = ".mi($_REQUEST['department_id']);
			}
		}
		else
			$where['department'] = 1;

		$this->where = $where;
	}	
	
	function run_child_report($bid, $tbl_name){
	    global $con_multi;
	    $where = $this->where;
	    $extsel = $this->extsel;
	    $extwhere = $this->extwhere;
	    $sku_types = $this->sku_types;
	    $ttl_data = $this->ttl_data;
	    $ttl_data_type = $this->ttl_data_type;
	    $sales_target = $this->sales_target;
	    $sales_target_type = $this->sales_target_type;
							
		$tbl = $tbl_name['category_sales_cache'];
        $con_multi->sql_query("select year, month, sku_type $extsel, p".($_REQUEST['qn']-1).", sum(amount) as amount from category_cache left join $tbl using (category_id) where month = ".mi($_REQUEST['m'])." and $where[department] and $extwhere group by year, month, sku_type $extsel") or die(mysql_error());

		while($r = $con_multi->sql_fetchrow())
		{
			if ($r['p'.$_REQUEST['qn']] == 0) $r['p'.$_REQUEST['qn']] = $r['p'.($_REQUEST['qn']-1)];
			$sku_types[$r['sku_type']] = 1;
			$ttl_data[$r['p'.$_REQUEST['qn']]][$r['year']] += $r['amount'];
			$havechild = $r['p'.($_REQUEST['qn']+1)];
			$ttl_data_type[$r['sku_type']][$r['p'.$_REQUEST['qn']]][$r['year']] += $r['amount'];
		}
		$con_multi->sql_freeresult();

        $tbl = $tbl_name['sales_target'];
		$con_multi->sql_query("select year, month, sku_type $extsel, sum(target) as amount from category_cache left join $tbl tbl on category_cache.category_id = tbl.department_id where month = ".mi($_REQUEST['m'])." and $where[department] and $extwhere group by year, month, sku_type $extsel",false,false);
		while($r = $con_multi->sql_fetchrow())
		{
			$sales_target[$r['p'.$_REQUEST['qn']]][$r['year']] += $r['amount'];
			$sales_target_type[$r['sku_type']][$r['p'.$_REQUEST['qn']]][$r['year']] += $r['amount'];
		}
		$con_multi->sql_freeresult();
		
		$this->sku_types = $sku_types;
	    $this->ttl_data = $ttl_data;
	    $this->ttl_data_type = $ttl_data_type;
	    $this->sales_target = $sales_target;
	    $this->sales_target_type = $sales_target_type;

	}
	
	function load_child()
	{
		global $con, $con_multi;
		$this->process_form();
		$branch_group = $this->branch_group;
		
		$parent_id = $_REQUEST['parent_id'];
		//$new_id = $parent_id.'_'.mi($_REQUEST['q']);
		
		$extsel = ", p".$_REQUEST['qn'].", p".($_REQUEST['qn']+1);
		
		if ($_REQUEST['q'] != '') 
		{
			
			$extwhere = "`p".($_REQUEST['qn']-1)."` = ".mi($_REQUEST['q']);
		}
		else
		$extwhere = 1;
		
		$this->extsel = $extsel;
		$this->extwhere = $extwhere;
		
		$con_multi->sql_query("select id, description from category");
		while($r = $con_multi->sql_fetchrow())
		{
			$cat[$r['id']] = $r['description'];
		}
		$con_multi->sql_freeresult();
		
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			if($branch_group['items'][$bg_id]){
				foreach($branch_group['items'][$bg_id] as $bid=>$b){
					if ($config['sales_report_branches_exclude']) {
						$branch_code = $b['code'];
						if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
							//print "$branch_code skipped99<br />";
							continue;
						}
					}
					$tbl_name['category_sales_cache'] = "category_sales_cache_b".$bid;
					$tbl_name['sales_target'] = "sales_target_b".$bid;
					$this->run_child_report($bid,$tbl_name);
				}
			}
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $tbl_name['category_sales_cache'] = "category_sales_cache_b".$bid;
				$tbl_name['sales_target'] = "sales_target_b".$bid;
	            $this->run_child_report($bid,$tbl_name);
	            $branch_code = BRANCH_CODE;
			}else{
				if($bid==0){
	                $branch_code = "All";
	                $q_b = $con_multi->sql_query("select * from branch where active=1 order by sequence,code");
	                while($r = $con_multi->sql_fetchrow($q_b)){
						if ($config['sales_report_branches_exclude']) {
							$branch_code = $r['code'];
							if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
								//print "$branch_code skipped44<br />";
								continue;
							}
						}
                        $tbl_name['category_sales_cache'] = "category_sales_cache_b".$r['id'];
						$tbl_name['sales_target'] = "sales_target_b".$r['id'];
			            $this->run_child_report($r['id'],$tbl_name);
					}
					$con_multi->sql_freeresult($q_b);
				}else{
	                $tbl_name['category_sales_cache'] = "category_sales_cache_b".$bid;
					$tbl_name['sales_target'] = "sales_target_b".$bid;
		            $this->run_child_report($bid,$tbl_name);
					$branch_code = get_branch_code($bid);
				}
			}
		}
		
		$sku_types = $this->sku_types;
	    $ttl_data = $this->ttl_data;
	    $ttl_data_type = $this->ttl_data_type;
	    $sales_target = $this->sales_target;
	    $sales_target_type = $this->sales_target_type;
		
		foreach ($ttl_data as $p1 => $dp1)
		{
		    $new_id = $parent_id.'_'.$p1;
			print "<tbody id=$new_id>";
			print "<tr class=ccategory>";
			print "<td> ";			
			print "<img style='width:".(($_REQUEST['qn'])*10)."px;height:10px' src='/ui/pixel.gif'>";
			if($havechild > 0)
			print "<img src='/ui/expand.gif' align='absmiddle' onclick='load_child($_REQUEST[m], this, ".($_REQUEST['qn']+1).", $p1);'> ";

			print $cat[$p1]."</td>";
			for($i=$_REQUEST['year'];$i<=$_REQUEST['year']+1;$i++)
			{
				print "<td align=right>".($dp1[$i]?number_format($dp1[$i],2):"-")."</td>";
				
				print "<td align=right>".($sales_target[$p1][$i]?number_format($sales_target[$p1][$i],2):"-")."</td>";
				
				print "<td align=right>".($dp1[$i]?number_format($dp1[$i]-$sales_target[$p1][$i],2):"-")."</td>";
				
				print "<td align=right>".($dp1[$i]?number_format(($dp1[$i]-$sales_target[$p1][$i])/$sales_target[$p1][$i]*100,2):"-")."</td>";
			}
			print "</tr>";
			
			foreach($sku_types as $kst => $st)
			{	
				print "<tr class=\"c".strtolower($kst)."\">";
				print "<td>";
				print "<img style='width:".(($_REQUEST['qn'])*10)."px;height:10px' src='ui/pixel.gif'>";
				print $kst;
				print "</td>";	
				for($i=$_REQUEST['year'];$i<=$_REQUEST['year']+1;$i++)
				{
					print "<td align=right>".($ttl_data_type[$kst][$p1][$i]?number_format($ttl_data_type[$kst][$p1][$i],2):"-")."</td>";
			
					print "<td align=right>".($sales_target_type[$kst][$p1][$i]?number_format($sales_target_type[$kst][$p1][$i],2):"-")."</td>";
			
					print "<td align=right>".($ttl_data_type[$kst][$p1][$i]?number_format($ttl_data_type[$kst][$p1][$i]-$sales_target_type[$kst][$p1][$i],2):"-")."</td>";
			
					print "<td align=right>".($ttl_data_type[$kst][$p1][$i]?number_format(($ttl_data_type[$kst][$p1][$i]-$sales_target_type[$kst][$p1][$i])/$sales_target_type[$kst][$p1][$i]*100,2):"-")."</td>";
				}  
				print "</tr>";			
			}
			
			print "</tbody>";
			
		}
	} 

	function default_values()
	{
	    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 year"));
	    $_REQUEST['date_to'] = date("Y-m-d");
	}
}
//$con_multi = new mysql_multi();
$report = new MonthlySalesByCategory('Monthly Sales Comparison By Category');
//$con_multi->close_connection();
?>
