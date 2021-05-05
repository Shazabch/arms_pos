<?php
/*
11/12/2010 12:40:44 PM Alex
- Add filter on departments privilege and fix date bugs

1/10/2011 5:27:42 PM Justin
- Fixed the wrong info show while filter by selected date.

1/19/2011 6:08:44 PM alex
- fix date bugs

5/25/2011 1:10:29 PM Alex
- fix hours time from 10 AM to 10 PM
- Change view by day or month to match input date

6/24/2011 6:13:23 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:37:33 PM Andy
- Change split() to use explode()

8/9/2012 3:01 PM Andy
- Fix report cannot show 12am sales.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

4:22 PM 11/27/2014 Andy
- Enhance report to show the sales amount using the amount-discount-discount2-tax_amount.(discount2 is receipt and mix & match discount, tax_amount is gst)

03/10/2016 15:40 Edwin
- Enhanced to enable select child branch group in branch selection

4/18/2016 11:04 AM Andy
- Fix category filter.

2/20/2020 2:30 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
//$con = new sql_db('agrofresh-hq.dyndns.org','arms_slave','arms_slave','armshq');
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

class HourlySalesbyDay extends Report
{
	var $where;
	var $groupby;
	var $select;
	
	function generate_report()
	{
		global $con, $smarty, $sessioninfo, $con_multi;
		$where = $this->where;

		//minimum time stamp 10AM to 10 PM
		//$min_hour=10;	//10 AM
		$max_hour=22;	//10 PM

		$sql = "select year(pos.date) as year, month(pos.date) as month, day(pos.date) as day, hour(pos.pos_time) as hour, sum(price-discount-discount2-tax_amount) as amount
from pos_items pi
left join pos on pos.id=pi.pos_id and pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id
left join sku_items on pi.sku_item_id = sku_items.id
left join sku on sku_items.sku_id = sku.id
left join category_cache using (category_id)
where pos.cancel_status=0 and $where ".$this->groupby."
order by year,month,day,hour
";
		//print $sql;
		//$con_multi = new mysql_multi();

		$con_multi->sql_query($sql);

		while ($r = $con_multi->sql_fetchrow())
		{
		  //testing
		  //if(rand(1, 2)==1) $r['hour'] =8;
			if(!isset($min_hour) || $min_hour > $r['hour']){
          		$min_hour = $r['hour'];
      		}
      		
      		if (!$max_hour || $max_hour < $r['hour']){
				$max_hour = $r['hour'];
			}		

			if ($_REQUEST['view_type'] == 'day'){
        		$r['date'] = $r['year']."-".sprintf("%02d", $r['month'])."-".sprintf("%02d", $r['day']);
			}else{
				$year_arr[$r['year']]=$r['year'];
	      		$month_arr[$r['year']][$r['month']]=$r['month'];

				$r['date'] = $r['year']."-".sprintf("%02d", $r['month']);
			}
        
  			$data[$r['date']][$r['hour']] += $r['amount'];
  			$day_total[$r['date']] += $r['amount'];
  			$hour_total[$r['hour']] += $r['amount'];
  			$grand_total += $r['amount'];
		}
		$con_multi->sql_freeresult();
		if(!isset($min_hour))	$min_hour=10;
/*		print "<pre>";
		print_r($data);
		print "</pre>";
*/	
   
		$startdate = $this->date_from;
		
		if ($_REQUEST['view_type'] == 'day')
		{
			$period_day = strtotime($this->date_to) - strtotime($this->date_from);
			$days_remain = intval(date("z", $period_day));
		
			for($i=0;$i<=$days_remain;$i++)
			{
				$days[] = date("Y-m-d", strtotime("+$i day", strtotime($this->date_from)));
			}
		}
		else
		{
			if ($year_arr){
				foreach ($year_arr as $y){
					foreach ($month_arr[$y] as $m){
						$days[] = $y."-".sprintf("%02d", $m);
						$years[$y."-".sprintf("%02d", $m)] =$y;
						$months[$y."-".sprintf("%02d", $m)] =$m;					 
					}
				}
			}
			
		}
		$con_multi->sql_query("select id, description from category");
		while($r = $con_multi->sql_fetchrow())
		{
			$cat[$r['id']] = $r['description'];
		}
		$con_multi->sql_freeresult();
		
		for($i=$min_hour;$i<=$max_hour;$i++){
		    if($i<12){
		        $hour[$i]=$i.":00 AM";
	        }elseif($i==12){
				$hour[$i]=$i.":00 PM";
			}else{
	            $h = $i-12;
	            if($i=='24')
	            {
	                $hour[$i]=$h.":00 AM";
	            }else
	            {
	                $hour[$i]=$h.":00 PM";
	            }
	        }
	    }
	 
//		if ($data) asort($data);
		$smarty->assign("hour", $hour);
		$smarty->assign("day_total", $day_total);
		$smarty->assign("hour_total", $hour_total);
		$smarty->assign("years_arr", $years);
		$smarty->assign("months_arr", $months);
		$smarty->assign("days", $days);
		$smarty->assign("cat", $cat);
		$smarty->assign("hr", $hr);
		$smarty->assign("hr_cnt", count($hour));
		$smarty->assign("data", $data);
		$smarty->assign("grand_total", $grand_total);
		//$con_multi->close_connection();
	}
	
	function process_form()
	{
		global $config, $sessioninfo, $con, $smarty, $con_multi;
		
		$this->load_branch_group();
		$branch_group = $this->branch_group;
				
		$where = array();
				
		// call parent
		$bid  = get_request_branch(true);
		parent::process_form();

		$this->date_from=$_REQUEST['date_from'];
		$this->date_to=$_REQUEST['date_to'];

		if ($_REQUEST['view_type'] == 'day')
		{
			$this->groupby = 'group by year, month, day, hour';
			$mtest =strtotime("-1 day",strtotime("+1 month",strtotime($this->date_from)));
		}
		else
		{
			$this->groupby = 'group by year,month, hour';
			$mtest = strtotime("+1 year",strtotime($this->date_from));
		}
		
		if (strtotime($this->date_to) > $mtest || strtotime($this->date_to)< strtotime($this->date_from)){
        	$this->date_to = date("Y-m-d",$mtest);
        	$_REQUEST['date_to'] = $this->date_to;
		}
		
		if($_REQUEST['all_category']=='on'){
			$filter[] = "p2 in ($sessioninfo[department_ids])";
			$cat_desc = "All";
		}else{
			$con_multi->sql_query("select level,description from category where id = ".mi($_REQUEST['category_id']));
			$lv = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$level = $lv['level'];
			$cat_desc = $lv['description'];
			$filter[] = " p$level = ".mi($_REQUEST['category_id']);
		}
		$con_multi->sql_query("select id,code from branch order by sequence,code");
		while ($r = $con_multi->sql_fetchrow())
		{
			$branches[] = $r['id'];
			if($_REQUEST['branch_id']==$r['id']){
                $branch_name = $r['code'];
			}
		}
		$con_multi->sql_freeresult();

		if($_REQUEST['branch_id'] < 0){   // is branch group
			$bg_id = abs($_REQUEST['branch_id']);
			if($branch_group['items'][$bg_id]) {
				foreach($branch_group['items'][$bg_id] as $bid=>$b) {
					if ($config['sales_report_branches_exclude'] && in_array($b['code'], $config['sales_report_branches_exclude'])) {
						continue;
					}
					$ids[] = $bid;
				}
				$filter[] = "pos.branch_id in (".join(',',$ids).")";	
				$branch_name =  $branch_group['header'][$bg_id]['code'];
				$report_title[] = "Branch Group: ".$branch_name;
			}
		}else {
			$bid = get_request_branch(true);
            if($bid>0){ // selected single branch
			    $filter[] = "pos.branch_id =".mi($bid);
			    $branch_name =  get_branch_code($bid);
				$report_title[] = "Branch: ".$branch_name;
			}else{  // all
                // no filter for all
                $branch_name = "All";
				$report_title[] = "Branch: ".$branch_name;
			}
		}
				
		//$this->date_to = date('Y-m-d', strtotime('+1 day', strtotime($this->date_to)));
        $filter[] = "pos.date between ".ms($this->date_from)." and ".ms($this->date_to);
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';
		$filter = join(" and ",$filter);
		$this->where = $filter;
		
		$report_title[] = "Category: ".$cat_desc."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date: ".$_REQUEST['date_from']." to ".$_REQUEST['date_to'];
		
		$smarty->assign('report_title',join("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$report_title));
		$smarty->assign('cat_desc',$cat_desc);
	}	

	function default_values()
	{
		if ($_REQUEST['view_type'] == 'day')
	    	$_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month"));
	    else
	    	$_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 year"));
	    $_REQUEST['date_to'] = date("Y-m-d");
	}
	
	function daysinmonth($year,$month)
	{
		return date("j",mktime(0,0,0,$month+1,0, $year));	
	}
}

$report = new HourlySalesbyDay('Hourly Sales by '.ucfirst($_REQUEST['view_type']));

?>
