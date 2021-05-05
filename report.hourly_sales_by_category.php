<?php
/*
5/25/2011 1:10:29 PM Alex
- fix hours time from 10 AM to 10 PM

7/6/2011 12:34:01 PM Andy
- Change split() to use explode()

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

4:17 PM 11/27/2014 Andy
- Enhance report to show the sales amount using the amount-discount-discount2-tax_amount.(discount2 is receipt and mix & match discount, tax_amount is gst)

11/27/2015 9:17 AM Qiu Ying
- Make it same as select Branch filter from "Sales report>Daily Category Sales Report" 

11/30/2015 10:51 AM Qiu Ying
- Fixed report title cannot show branch group

2/20/2020 2:24 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

class HourlySalesbyCategory extends Report
{
	var $where;
	var $groupby;
	var $select;

	function generate_report()
	{
		global $con, $smarty, $sessioninfo, $con_multi;
		$where = $this->where;
		
		//minimum time stamp 10AM to 10 PM
		$min_hour=10;	//10 AM
		$max_hour=22;	//10 PM
		
		//$hr = array(9 => "9:00 AM","10:00 AM","11:00 AM","12:00 PM","1:00 PM","2:00 PM","3:00 PM","4:00 PM","5:00 PM","6:00 PM","7:00 PM","8:00 PM","9:00 PM","10:00 PM","11:00 PM","12:00 AM");
		
		$con_multi->sql_query("select id, description from category");
		while($r = $con_multi->sql_fetchrow())
		{
			$cat[$r['id']] = $r['description'];
		}
		$con_multi->sql_freeresult();
		$cat['Un-categorized'] = 'Un-categorized';

		/*$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/

		$sql = "select p1, p2, hour(pos_time) as hour, sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) as amount
from pos_items pi
left join pos on pos.id=pi.pos_id and pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id
left join sku_items on pi.sku_item_id = sku_items.id
left join sku on sku_items.sku_id = sku.id
left join category_cache using (category_id)
where $where[date] and $where[branch] and pos.cancel_status=0 and $where[department] and $where[active_sku] group by p2, hour order by hour";
		//print $sql;
		$con_multi->sql_query($sql);
		
		while ($r = $con_multi->sql_fetchrow())
		{
		  //testing
		  //if(rand(1, 2)==1) $r['hour'] =8;
		  
			if ($r['p1'] == '') $r['p1'] = 'Un-categorized';
			if ($r['p2'] == '') $r['p2'] = 'Un-categorized';

			$key1 = $cat[$r['p1']];
			$key2 = $cat[$r['p2']];
			
			$p1_data[$key1][$r['hour']] += $r['amount'];
			$data[$key1][$key2][$r['hour']] += $r['amount'];
			$total_p1[$key1] += $r['amount'];
			$total_p2[$key2] += $r['amount'];
			
			/*$p1_data[$r['p1']][$r['hour']] += $r['amount'];
			$data[$r['p1']][$r['p2']][$r['hour']] += $r['amount'];
			$total_p1[$r['p1']] += $r['amount'];
			$total_p2[$r['p2']] += $r['amount'];*/
			$total_by_hour[$r['hour']] += $r['amount'];
			$total_data += $r['amount'];
			
			if(!$min_hour || $min_hour > $r['hour']){
          		$min_hour = $r['hour'];
      		}
      		
      		if (!$max_hour || $max_hour < $r['hour']){
				$max_hour = $r['hour'];
			}
      		
		}
		$con_multi->sql_freeresult();

		if ($data) ksort($data);
		if(count($data)>0){
            foreach($data as $k=>$tb){
			    ksort($tb);
				$data[$k] = $tb;
			}
		}
		
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

		$smarty->assign("hour",$hour);
		$smarty->assign("total_data", $total_data);
		$smarty->assign("total_by_hour", $total_by_hour);
		$smarty->assign("p1_data", $p1_data);
		$smarty->assign("total_p1", $total_p1);
		$smarty->assign("total_p2", $total_p2);
		$smarty->assign("cat", $cat);
		$smarty->assign("hr", $hr);
		$smarty->assign("data", $data);
		//$con_multi->close_connection();
	}
	
	function process_form()
	{
		global $config,$con,$smarty,$con_multi;
		
		/*
		print '<pre>';
		print_r($_REQUEST);
		print '</pre>';
		*/
		
		$where = array();
		
		global $sessioninfo;
		// call parent
		
		$bid  = get_request_branch(true);
		parent::process_form();

		list($year, $month, $day) = explode("-",$this->date_from);
		
		if($_REQUEST['branch_id'] < 0){   // is branch group
			$bg_id = abs($_REQUEST['branch_id']);
			$branch_group = $this->branch_group;
			foreach($branch_group['items'][$bg_id] as $bid=>$r){
				if ($config['sales_report_branches_exclude']) {
					$branch_code = $r['code'];
					if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
						continue;
					}
				}
				$ids[] = $bid;
			}
			$where['branch'] = "pos.branch_id in (".join(',',$ids).")";
			$branch_code = $branch_group['header'][$bg_id]['code'];
			$grp = 1;
		}else{
            $bid  = get_request_branch(true);

			if (BRANCH_CODE != 'HQ'){
	            $_REQUEST['branch_id'] = $bid;
	            $branch_code = BRANCH_CODE;
			}else{
				if($bid==0){
	                $branch_code = "All";
				}else{
	                $con_multi->sql_query("select code from branch where id=".mi($bid)) or die(mysql_error());
					$branch_code = $con_multi->sql_fetchfield(0);
					$con_multi->sql_freeresult();
				}
			}

   			$where['branch'] = $_REQUEST['branch_id']!=''?'pos.branch_id = '.mi($_REQUEST['branch_id']):1;
			
			if ($bid == 0 && $config['sales_report_branches_exclude']) {
				$code_list = array();
				foreach ($config['sales_report_branches_exclude'] as $code) $code_list[] = ms($code);
				$con_multi->sql_query("select id from branch where code in (".join(',',$code_list).")");
				while($r = $con_multi->sql_fetchassoc()) $excl_branch[] = $r['id'];
				$con_multi->sql_freeresult();
				$where['branch'] = "pos.branch_id not in (".join(',',$excl_branch).")";
			}
			
		}
		//$where['date'] = "year = $year and month = $month and day = $day";
		$where['date'] = "pos.date=".ms($this->date_from);
		$where['department'] = "p2 in ($sessioninfo[department_ids])";
		$where['active_sku'] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';
		$this->where = $where;
		
		if($grp)	$str_branch = "Branch Group: ".$branch_code;
    else
        $str_branch = "Branch: ".$branch_code;
		
		$report_title = "Date: ".$_REQUEST['date_from']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$str_branch;
		$smarty->assign('report_title',$report_title);
		
		$smarty->assign('branch_code',$branch_code);
	}	

	function default_values()
	{
//	    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 year"));
	    $_REQUEST['date_from'] = date("Y-m-d");
	}
}

$report = new HourlySalesbyCategory('Hourly Sales by Category');

?>
