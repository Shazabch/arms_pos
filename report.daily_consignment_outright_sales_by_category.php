<?php
/*
5/4/2010 10:08:17 AM Alex
- Fix report of miscalculation of sales by categories

10/8/2010 12:39:13 PM Alex
- Add con_multi, filter on departments privilege and fix date bugs

1/18/2011 6:36:41 PM Alex
- change use report_server

6/24/2011 6:05:41 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:28:10 PM Andy
- Change split() to use explode()

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)
- report not to use branch group table anymore, change to individual branch

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/20/2020 11:28 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
include("include/class.report.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

class DailyConsignmentOutrightSalesByCategory extends Report
{
	var $where;
	var $groupby;
	var $select;

	function run_report($bid, $tbl_name){
	    global $con, $smarty, $sessioninfo, $con_multi;
		$where = $this->where;
		
		$tbl = $tbl_name['category_sales_cache'];
		
		/*$con_multi = new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/
		
        $con_multi->sql_query($abc="select p1, day(date) as day, month, sku_type, year, sum(amount) as amount from category_cache left join $tbl using (category_id) where $where[date] and $where[category] group by p1, day, sku_type");
		//print $abc.'<br /><br />';
		while($r = $con_multi->sql_fetchrow())
		{
			$this->total_amount_sku_type[$r['p1']][$r['sku_type']] += $r['amount'];
			$this->total_sku_type[$r['p1']][$r['sku_type']][$r['day']] += $r['amount'];
			$this->data[$r['p1']][$r['day']] += $r['amount'];
			$this->total_data[$r['p1']] += $r['amount'];
		}
		$con_multi->sql_freeresult();

		/*
		print '<pre>';
		var_dump($data);
		print '</pre>';
		print "for $tbl<br /><br /><br />";
		*/
		
	}
	
	function generate_report()
	{
		global $con, $smarty, $sessioninfo, $config, $con_multi;
		$where = $this->where;
		$branch_group = $this->load_branch_group();

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
						// print "$curr_branch_code skipped<br />";
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
								// print "$curr_branch_code skipped<br />";
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
		
	  	$con_multi->sql_query("select description from category where id = ".mi($_REQUEST['category_id']));
		$lv = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		$cat_desc = $lv['description'];
	
	    if(!$cat_desc)	$cat_desc = "All";
  
		$report_title = "Branch: ".$branch_code."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date: ".$_REQUEST['date_from']." to ".$_REQUEST['date_to']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Category: ".$cat_desc;
		
		$smarty->assign('report_title',$report_title);
		
		$s_day = date("d",strtotime($this->date_from));
		if ($s_day > $this->lastdayofmonth) $s_day = 0;
		$smarty->assign("offset_day", date("N",strtotime($this->date_from)));
		$smarty->assign("s_day", $s_day);
		$smarty->assign("max_month", $this->lastdayofmonth);
		$smarty->assign("start_day",date("j", strtotime($this->date_from)));
		$smarty->assign("total_sku_type", $this->total_sku_type);
		$smarty->assign("total_amount_sku_type", $this->total_amount_sku_type);
		$smarty->assign("cat", $cat);
		$smarty->assign("total_data", $this->total_data);
		$smarty->assign("data", $this->data);
    	$smarty->assign('branch_code',$branch_code);
	}
	
	function process_form()
	{
		global $con,$sessioninfo,$smarty,$LANG,$con_multi;
		$where = array();
		
		// call parent
		//parent::process_form();

		$this->date_from=$date_f=$_REQUEST['date_from'];
		$this->date_to=$date_t=$_REQUEST['date_to'];
		
		$mtest = strtotime("-1 day", strtotime("+1 month",strtotime($date_f)));
		$year = date("Y",strtotime($date_f));
		$month = date("m",strtotime($date_f));
//		$mtest = strtotime("$year-$month-".$this->daysinmonth($year,$month));
		
		if ((strtotime($date_t) > $mtest) || (strtotime($date_t)< strtotime($date_f))){
       		$this->date_to = date("Y-m-d",$mtest);
       		$_REQUEST['date_to'] = $this->date_to;
		}

		$where['date'] = 'date between '.ms($this->date_from)." and ".ms($this->date_to);

		if($_REQUEST['all_category']=='on'){
			$where['category'] = "p2 in ($sessioninfo[department_ids])";
			$cat_desc = "All";
		}elseif ($_REQUEST['category_id']) {
			$con_multi->sql_query("select level,description from category where id = ".mi($_REQUEST['category_id']));
			$lv = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$level = $lv['level'];
			$cat_desc = $lv['description'];
            $where['category'] = $_REQUEST['category_id']?"p$level = ".mi($_REQUEST['category_id']) : 1 ;
		}else{
		    $err[]= $LANG['REPORT_NO_CATEGORY'];
            $smarty->assign('err',$err);
            $this->display();
            exit;
		}
		$this->lastdayofmonth = $this->daysinmonth($year,$month);
		$this->where = $where;
		$smarty->assign('cat_desc',$cat_desc);
	}	

	function default_values()
	{
	    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month"));
	    $_REQUEST['date_to'] = date("Y-m-d");
	}
	
	function daysinmonth($year,$month)
	{
		return date("j",mktime(0,0,0,$month+1,0, $year));	
	}
	
	function get_branch_ids_in_group($group_id) {
		global $con,$con_multi;
		
		$br_array = array();
		$q1 = $con_multi->sql_query("select branch_id from branch_group_items where branch_group_id = ". mi($group_id));
		while($r1 = $con_multi->sql_fetchassoc($q1)){
			$br_array[] = $r1['branch_id'];
		}
		$con_multi->sql_freeresult($q1);
		return $br_array;
	}
	
}

$report = new DailyConsignmentOutrightSalesByCategory('Daily Consignment / Outright Sales by Category');

?>
