<?php
/*
1/20/2011 4:50:16 PM Justin
- Redirect the report to use con_multi instead of con.

7/6/2011 12:39:21 PM Andy
- Change split() to use explode()

7/22/2011 7:50:44 PM Alex
- fix sql query bugs

4/2/2014 11:02 AM Fithri
- add option to select all SKU / branches for some reports, require config 'allow_all_sku_branch_for_selected_reports' to be turned on

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

11/28/2014 4:27 PM Andy
- Enhance report to show the sales amount using the amount-discount-discount2-tax_amount.(discount2 is receipt and mix & match discount, tax_amount is gst)

2/19/2020 11:54 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");

class HourlySkuSalesByRace_Category extends Report
{
	function run_report($b_str)
	{
	    global $con,$smarty,$con_multi;
	   
		$filter = $this->filter;

		$table = $this->table;
		$label = $this->label;
		$race = $this->race;

		/*$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/

		$sql = "select hour(pos.pos_time) as hour,sku_item_code,sum(qty) as qty,sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) as amount,pos.branch_id,pos.race,sku_items.description
from pos_items pi
left join pos on pos.id=pi.pos_id and pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id
left join sku_items on sku_items.id=pi.sku_item_id
where $filter $b_str
group by hour,race,sku_item_code order by hour ";
		//print $sql;
		$con_multi->sql_query($sql) or die(sql_error());

		if($con_multi->sql_numrows()>0){
		    foreach($con_multi->sql_fetchrowset() as $t){
		    
		    //testing
		    //if(rand(1, 2)==1) $t['hour'] =8;
		    
		        $lbl = sprintf("%02d", $t['hour']);
                $label[$lbl] = $lbl;
				
				if($t['race']==''){
                    $race_lbl = 'NONE';
				}else{
                    $race_lbl = strtoupper(substr($t['race'], 0, 1));
				}
				
				$race[$race_lbl] = $race_lbl;
				
				$table[$lbl][$t['sku_item_code']][$race_lbl]+=$t['amount'];
				$table[$lbl][$t['sku_item_code']]['total']+=$t['amount'];
				$table['total'][$t['sku_item_code']]['total']+=$t['amount'];
				$table['total'][$t['sku_item_code']][$race_lbl]+=$t['amount'];
				$table[$lbl]['total']['total']+=$t['amount'];
				$table['total']['total']['total']+=$t['amount'];
				
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
	        else{
	            $h = $i-12;
	            $hour[$i]=$h."pm";
	        }
   		}
   
		$smarty->assign("hour",$hour);
		$this->table = $table;
		$this->label = $label;
		$this->race = $race;
		//$con_multi->close_connection();
	}
	
	function load_race_lbl(){
		global $con,$con_multi;
		
		$con_multi->sql_query("select * from pos_settings where setting_name='race'") or die(mysql_error());
		$temp = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		$all_race = $temp['setting_value'];
		
		$temp_race = preg_split("//",$all_race);
		$race = array();
		
		foreach($temp_race as $r){
			if($r!=''){
				$race[$r] = $r;
			}
		}
		
		return $race;
	}
	
	function generate_report()
	{
		global $con, $smarty, $con_multi;
		$branch_group = $this->branch_group;
		
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
            list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
            foreach($branch_group['items'][$bg_id] as $bid=>$b){
				$ids[] = $bid;
			}
			$b_str = "and pos.branch_id in (".join(',',$ids).")";
			$this->run_report($b_str);
			$branch_name = $branch_group['header'][$bg_id]['code'];
        }else{
			if ($_REQUEST['branch_id'] == 'all') {
				$branch_name =  'All';
				$ids = array();
				$q1 = $con_multi->sql_query("select id from branch where active=1 order by sequence, code");
				while ($r1 = $con_multi->sql_fetchassoc($q1)) {
					$ids[] = mi($r1['id']);
				}
				$con_multi->sql_freeresult($q1);
				$b_str = "and pos.branch_id in (".join(',',$ids).")";
				$this->run_report($b_str);
			}
			else {
				$bid  = get_request_branch(true);
				if($bid==0){
					die("no selected branch.");
				}else{
					$branch_name =  get_branch_code($bid);
					$b_str = "and pos.branch_id=".$bid;
					$this->run_report($b_str);
				}
			}
		}
			
        $table = $this->table;
		$label = $this->label;
		$race = $this->race;
		$category = $this->category;
		
		@ksort($label);

		$report_title = "Branch: ".$branch_name."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date: ".$_REQUEST['date'];
    
	    $smarty->assign('report_title',$report_title);
		
		$smarty->assign('label',$label);
		$smarty->assign('table',$table);
		$smarty->assign('category',$category);
		$smarty->assign('race',$race);
		$smarty->assign('branch_name',$branch_name);
	}
	
	function process_form()
	{
	    global $con,$smarty,$sessioninfo,$con_multi;
		// do my own form process
		
		// call parent
		parent::process_form();
		
		$code_list = $_REQUEST['sku_code_list_2'];
		$date = $_REQUEST['date'];
		$filter[] = "pos.date=".ms($date);

	    $list = explode(",",$code_list);
	    for($i=0; $i<count($list); $i++){
	        $con_multi->sql_query("select description from sku_items where sku_item_code=".ms($list[$i])) or die(sql_error());
	        $temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
	        $category[$list[$i]]['sku_item_code']=$list[$i];
	        $category[$list[$i]]['description']=$temp['description'];
			$list[$i]="'".$list[$i]."'";
		}
	    $list = join(",",$list);

	    $filter[] = "sku_item_code in($list)";
	    $filter[] = "pos.cancel_status=0";
		$filter = join(" and ",$filter);
		
		$this->filter = $filter;
		$this->category = $category;
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

$report = new HourlySkuSalesByRace_Category('Hourly SKU Items Sales by Race and Category');

?>
