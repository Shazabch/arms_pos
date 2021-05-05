<?php
/*
1/19/2011 10:28:46 AM Alex
- change use report_server

6/24/2011 6:07:41 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:30:22 PM Andy
- Change split() to use explode()

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/20/2020 11:53 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

class DailySalesBySKU extends Report
{
	var $where;
	var $groupby;
	var $select;
	var $datasub;
	var $data;
	var $data2;
	
	function ajax_load_detail()
	{
		global $con, $smarty, $sessioninfo, $con_multi, $config;
		$where = $this->where;
		$branch_group = $this->load_branch_group();

		$con_multi->sql_query("select level,description from category where id = ".mi($_REQUEST['category_id']));
		$lv = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		$level = $lv['level'];
		$cat_desc = $lv['description'];
		
		$where['date'] = 'pi.date between '.ms($_REQUEST['date_from'])." and ".ms($_REQUEST['date_to']);
		if($_REQUEST['all_category']=='on'){
			$where['category'] = "p2 in ($sessioninfo[department_ids])";
			$cat_desc = "All";
		}else{
            $where['category'] = $_REQUEST['category_id']?"p$level = ".mi($_REQUEST['category_id']):1;
		}
		$where['active_sku'] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';
		
		$this->where = $where;

		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			foreach ($branch_group['items'][$bg_id] as $br_id)
			{
				//print($br_id['code']);
				if ($config['sales_report_branches_exclude']) {
					$curr_branch_code = $br_id['code'];
					if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
						//print "$curr_branch_code skipped<br />";
						continue;
					}
				}
				$this->run_report($br_id['branch_id']);
			}
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $this->run_report($bid);
	            $branch_code = BRANCH_CODE;
			}else{
				if($bid==0){
					$branch_code = "All";
	                $q_b = $con_multi->sql_query("select * from branch where id not in (select branch_id from branch_group_items) order by sequence,code");
	                while($r = $con_multi->sql_fetchrow($q_b)){
						if ($config['sales_report_branches_exclude']) {
							$curr_branch_code = $r['code'];
							if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
								//print "$curr_branch_code skipped56<br />";
								continue;
							}
						}
                        $this->run_report($r['id']);
					}
					$con_multi->sql_freeresult($q_b);
					if($branch_group['header']){
						
						foreach($branch_group['items'] as $b_id=>$bg){
							foreach ($bg as $br_id => $bg2)
							{
								if ($config['sales_report_branches_exclude']) {
									$curr_branch_code = $bg2['code'];
									if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
										//print "$curr_branch_code skipped77<br />";
										continue;
									}
								}
                            	$this->run_report($br_id);
                            }
						}
					}
				}else{
		            $this->run_report($bid);
					$branch_code = get_branch_code($bid);
				}
			}
		}
		
		$data2 = $this->data2;
		$datasub = $this->datasub[$_REQUEST['id']];

		$enddate = strtotime($_REQUEST['date_to']);
		$startdate = strtotime($_REQUEST['date_from']);
		while ($enddate >= $startdate)
		{
			$alldate[] = date("Y-m-d", $startdate);
			$startdate = strtotime("+1 day",$startdate);
		}
		
		$smarty->assign("alldate",$alldate);
		
		$smarty->assign("datasub", $datasub);
		$smarty->assign("data2", $data2);
		$smarty->display('report.daily_sales_by_sku.detail.tpl');
	}
	
	function run_report($bid){
	    global $con, $smarty, $sessioninfo, $con_multi;
	    
		$where = $this->where;
		
		if ($_REQUEST['view_type'] == 'price')
		{
			$con_multi->sql_query("select * from pos_settings where branch_id = ".mi($bid)." and setting_name = 'barcode_price_code_prefix'") or die(mysql_error());
			$setting = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			if (!$setting['setting_value']) $setting['setting_value'] = 29;
			
			if ($_REQUEST['a'] == 'ajax_load_detail')
			$barcode_sql = "barcode like '".mi($_REQUEST['id'])."%' and";
			else
			$barcode_sql = "barcode like '".mi($setting['setting_value'])."%' and";
			
/*			$total_amount_sku_type = $this->total_amount_sku_type;
			$total_sku_type = $this->total_sku_type;
*/			$data = $this->data;
			$total_data = $this->total_data;
			
			$tbl = $tbl_name['category_sales_cache'];
	        $con_multi->sql_query("select barcode, pi.date, sum(pi.price) as price, sku_items.description from pos_items pi
	        	left join pos on pi.branch_id = pos.branch_id and pi.counter_id = pos.counter_id and pi.date = pos.date and pi.pos_id = pos.id
				left join sku_items on sku_items.id = pi.sku_item_id
				left join sku on sku_items.sku_id = sku.id 
				left join category_cache c on sku.category_id = c.category_id 
			 	where $where[date] and $where[category] and $where[active_sku] and pi.branch_id = ".mi($bid)." and $barcode_sql pos.cancel_status = 0 group by p1, barcode, pi.date");

/*print "select barcode, pi.date, sum(pi.price) as price, sku_items.description from pos_items pi
	        	left join pos on pi.branch_id = pos.branch_id and pi.counter_id = pos.counter_id and pi.date = pos.date and pi.pos_id = pos.id
				left join sku_items on sku_items.id = pi.sku_item_id
				left join sku on sku_items.sku_id = sku.id
				left join category_cache c on sku.category_id = c.category_id
			 	where $where[date] and $where[category] and pi.branch_id = ".mi($bid)." and $barcode_sql pos.cancel_status = 0 group by p1, barcode, pi.date";
*/

			while($r = $con_multi->sql_fetchrow())
			{
				$unit_price = floatval(substr($r['barcode'],7,5))/100;
				$barcode = substr($r['barcode'],0,7);
				$qty = $r['price']/$unit_price;
				$this->datasub[$barcode] = $r;
				$this->data[$barcode][$r['date']] += $qty;
//				print "barcode:$r[barcode]|qty:$qty|unit price:$unit_price<br>";
				$this->data[$barcode]['total_price'] += ($qty*$unit_price);
				$this->data2[$r['barcode']][$r['date']] += $qty;
				$this->data2[$r['barcode']]['price'] = $unit_price;
				$this->totalqtybydate[$r['date']] += $qty;
				//$data2[$r['barcode']]['total_price'] += ($qty*$unit_price);
			}
			$con_multi->sql_freeresult();
		}
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
		
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			
			$con_multi->sql_query("select code from branch_group where id = $bg_id");
			$branch_code = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
			
			foreach ($branch_group['items'][$bg_id] as $br_id)
			{
				if ($config['sales_report_branches_exclude']) {
					$curr_branch_code = $br_id['code'];
					if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
						//print "$curr_branch_code skipped<br />";
						continue;
					}
				}
				$this->run_report($br_id['branch_id']);
			}
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $this->run_report($bid);
	            $branch_code = BRANCH_CODE;
			}else{
				if($bid==0){
					$branch_code = "All";
	                $q_b = $con_multi->sql_query("select * from branch where id not in (select branch_id from branch_group_items) order by sequence,code");
	                while($r = $con_multi->sql_fetchrow($q_b)){
                        $this->run_report($r['id']);
					}
					$con_multi->sql_freeresult($q_b);
					if($branch_group['header']){
						
						foreach($branch_group['items'] as $b_id=>$bg){
							foreach ($bg as $br_id => $bg2)
							{
								if ($config['sales_report_branches_exclude']) {
									$curr_branch_code = $bg2['code'];
									if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
										//print "$curr_branch_code skipped<br />";
										continue;
									}
								}
								$this->run_report($br_id);
                            }
						}
					}
				}else{
		            $this->run_report($bid);
					$branch_code = get_branch_code($bid);
				}
			}
		}
		
		$data = $this->data;
		$data2 = $this->data2;
		$datasub = $this->datasub;
		$totalqtybydate = $this->totalqtybydate;
		
		$enddate = strtotime($this->date_to);
		$startdate = strtotime($this->date_from);
		while ($enddate >= $startdate)
		{
			$alldate[] = date("Y-m-d", $startdate);
			$startdate = strtotime("+1 day",$startdate);
		}
		
		$s_day = date("d",strtotime("-1 day",strtotime($this->date_from)));
		if ($s_day > $this->lastdayofmonth) $s_day = 0;
		$smarty->assign("alldate",$alldate);
/*		$smarty->assign("offset_day", date("N",strtotime($this->date_from)));
		$smarty->assign("s_day", $s_day);
		$smarty->assign("max_month", $this->lastdayofmonth);
		$smarty->assign("start_day",date("j", strtotime($this->date_from)));
		$smarty->assign("total_sku_type", $total_sku_type);
		$smarty->assign("total_amount_sku_type", $total_amount_sku_type);
		$smarty->assign("cat", $cat);
*/
    $con_multi->sql_query("select description from category where id = ".mi($_REQUEST['category_id']));
		$lv = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		$cat_desc = $lv['description'];
	
    if(!$cat_desc)
        $cat_desc = "All";
      
    $report_title = "Branch: ".$branch_code."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date: ".$_REQUEST['date_from']." to ".$_REQUEST['date_to']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Category: ".$cat_desc;
    
    $smarty->assign('report_title',$report_title);
   
		$smarty->assign("totalqtybydate", $totalqtybydate);
		$smarty->assign("total_data", $total_data);
		$smarty->assign("data", $data);
		$smarty->assign("data2", $data2);
		$smarty->assign("datasub", $datasub);
        $smarty->assign('branch_code',$branch_code);
	}
	
	function process_form()
	{
		global $con,$sessioninfo,$smarty,$con_multi;
		$where = array();
		
		// call parent
		parent::process_form();
		
//		$mtest =strtotime("+1 month",strtotime($this->date_from));
		$year = date("Y",strtotime($this->date_from));
		$month = date("m",strtotime($this->date_from));
//		$mtest = strtotime("$year-$month-".$this->daysinmonth($year,$month));
		
/*		if (strtotime($this->date_to) > $mtest){
       		$this->date_to = date("Y-m-d",$mtest);
       		$_REQUEST['date_to'] = $this->date_to;
		}
*/		
		$con_multi->sql_query("select level,description from category where id = ".mi($_REQUEST['category_id']));
		$lv = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		$level = $lv['level'];
		$cat_desc = $lv['description'];

		$where['date'] = 'pi.date between '.ms($_REQUEST['date_from'])." and ".ms($_REQUEST['date_to']);
		if($_REQUEST['all_category']=='on'){
			$where['category'] = "p2 in ($sessioninfo[department_ids])";
			$cat_desc = "All";
		}else{
            $where['category'] = $_REQUEST['category_id']?"p$level = ".mi($_REQUEST['category_id']):1;
		}
		$where['active_sku'] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';
		
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
}


/*$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}*/

$report = new DailySalesBySKU('Daily Price Code Sales');
//$con_multi->close_connection();
?>
