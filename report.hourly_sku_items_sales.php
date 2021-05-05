<?php
/*
1/19/2011 6:12:04 PM Alex
- change use report_server

7/6/2011 12:38:14 PM Andy
- Change split() to use explode()

7/17/2012 4:21:34 PM Justin
- Enhanced to pickup mcode.

9/10/2012 5:03:00 PM Fithri
- Fix total does not tally - bug

11/2/2012 5:30 PM Justin
- Bug fixed on the row and grand total not tally.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

11/28/2014 3:27 PM Andy
- Enhance report to show the sales amount using the amount-discount-discount2-tax_amount.(discount2 is receipt and mix & match discount, tax_amount is gst)

03/10/2016 15:40 Edwin
- Enhanced to enable select child branch group in branch selection

2/19/2020 3:36 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");

class HourlySkuSalesByRace_Category extends Report
{
	function generate_report()
	{
		global $con, $smarty, $config, $con_multi;
		
		$branch_group = $this->branch_group;
		
	    $table = array();$label=array();$category=array();

		$code_list = $_REQUEST['sku_code_list_2'];
	    /*$date = strtotime($_REQUEST['date']);
	    $year = date("Y",$date);
	    $month = date("m",$date);
	    $day = date("d",$date);*/
        $date = $_REQUEST['date'];
    
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
		
	    $filter[]= "pos.date=".ms($date);
	    $filter[] = "pos.cancel_status=0";
	    /*$filter[] = "year=".mi($year);
	    $filter[] = "month=".mi($month);
	    $filter[] = "day=".mi($day);*/

	    $list = explode(",",$code_list);
	    for($i=0; $i<count($list); $i++){
	        $con_multi->sql_query("select mcode, description from sku_items where sku_item_code=".ms($list[$i])) or die(sql_error());
	        $temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
	        $category[$list[$i]]['sku_item_code']=$list[$i];
	        $category[$list[$i]]['mcode']=$temp['mcode'];
	        $category[$list[$i]]['description']=$temp['description'];
			$list[$i]="'".$list[$i]."'";
		}
	    $list = join(",",$list);

	    $filter[] = "sku_item_code in($list)";
		$filter = join(" and ",$filter);

		/*$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/

		$sql = "select hour(pos.pos_time) as hour,sku_item_code,sum(qty) as qty, sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) as amount,
				pos.branch_id, pos.race, sku_items.mcode, sku_items.description
				from pos_items pi
				left join pos on pos.id=pi.pos_id and pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id
				left join sku_items on sku_items.id=pi.sku_item_id
				where $filter
				group by hour,race,sku_item_code order by hour";

		$con_multi->sql_query($sql) or die(sql_error());

		if($con_multi->sql_numrows()>0){
		    foreach($con_multi->sql_fetchrowset() as $t){
				//testing
				//if(rand(1, 2)==1) $t['hour'] =8;

		        //$lbl = sprintf("%02d", $t['hour']);
		        $lbl = $t['hour'];
                $label[$lbl] = $lbl;

				$table[$lbl][$t['sku_item_code']]['total']+=$t['amount'];
				$table[$lbl]['total']['total']+=$t['amount'];
				
				if(!$min_hour || $min_hour > $t['hour'])
	  			{
	            $min_hour = $t['hour'];
	        	}
			}
		}
		$con_multi->sql_freeresult();
		
		for($i=$min_hour;$i<=24;$i++)
		{
		    if($i<13)
		        $hour[$i]=$i."am";
	        else
	        {
	            $h = $i-12;
	            if($i=='24')
	            {
	                $hour[$i]=$h."am";
	            }else
	            {
	                $hour[$i]=$h."pm";
	            }
	        }
	    }
		
		ksort($label);
	 
		//$hours = array('09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24');
		
		$report_title[] = "Date: ".$_REQUEST['date'];
		
		$smarty->assign('report_title',join("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$report_title));
		$smarty->assign("hour", $hour);
		$smarty->assign("hours", $hours);
		$smarty->assign('label',$label);
		$smarty->assign('table',$table);
		$smarty->assign('category',$category);
		/*
		echo '<pre>';
		print_r($table);
		print_r($hour);
		echo '</pre>';
		*/
		//$con_multi->close_connection();
	}
	
	function process_form()
	{
		// do my own form process

		// call parent
		parent::process_form();
	}	

	function default_values()
	{
	    $view_type = $_REQUEST['view_type'];
	    if($view_type=="day"){
                $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month"));
		}else{
            $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 year"));
		}
		$_REQUEST['date_to'] = date("Y-m-d");
	}
}

$report = new HourlySkuSalesByRace_Category('Hourly SKU Items Sales');

?>
