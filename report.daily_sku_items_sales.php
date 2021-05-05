<?php
/*
1/19/2011 12:07:16 PM Alex
- change use report_server and fix date bugs

6/16/2011 3:36:25 PM Andy
- Fix report show wrong initial from date.

7/6/2011 12:31:01 PM Andy
- Change split() to use explode()

8/1/2012 3:03 PM Justin
- Fixed bug of showing sql error while filter by branch group.
- Fixed bug of report title.

4/2/2014 11:02 AM Fithri
- add option to select all SKU / branches for some reports, require config 'allow_all_sku_branch_for_selected_reports' to be turned on

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

11/27/2015 9:17 AM Qiu Ying
- Make it same as select Branch filter from "Sales report>Daily Category Sales Report" 

11/30/2015 10:51 AM Qiu Ying
- Fixed report title cannot show branch group

2/19/2020 11:41 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
//$con = new sql_db('agrofresh-hq.dyndns.org','arms_slave','arms_slave','armshq');
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");

class DailySkuItemsSales extends Report
{
	function run_report($bid, $tbl_name)
	{
	    global $con,$con_multi;

		$filter = $this->filter;
		
		$table = $this->table;
		$category2 = $this->category2;
		$sku = $this->sku;
		$label = $this->label;
		
		/*$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/

		$tbl = $tbl_name['sku_items_sales_cache'];
		$sql = "select date,year(date) as year,month(date) as month,day(date) as day,sku_item_code,sku_item_id,sku_items.description,sum(qty) as qty,sum(amount) as amount,p3 as p,category.description as cname,mcode,artno
from $tbl pos
left join sku_items on sku_item_id = sku_items.id
left join sku on sku_id = sku.id
left join category_cache using (category_id)
left join category on category_cache.p3 = category.id
where $filter group by date,sku_item_code order by date";

		$con_multi->sql_query($sql) or die(sql_error());

		if($con_multi->sql_numrows()>0){
		    foreach($con_multi->sql_fetchrowset() as $t){
		        $lbl = sprintf("%04d%02d%02d", $t['year'],$t['month'],$t['day']);
                
                $category2[$t['p']]['description']=$t['cname'];
                $category2[$t['p']]['id']=$t['p'];
                $category2[$t['p']]['qty'][$lbl]+=$t['qty'];
                $category2[$t['p']]['amount'][$lbl]+=$t['amount'];
                $category2[$t['p']]['qty']['total']+=$t['qty'];
                $category2[$t['p']]['amount']['total']+=$t['amount'];
                $category2['total']['qty'][$lbl]+=$t['qty'];
                $category2['total']['amount'][$lbl]+=$t['amount'];
                $category2['total']['qty']['total']+=$t['qty'];
                $category2['total']['amount']['total']+=$t['amount'];

				$sku[$t['sku_item_code']]['sku_item_code']=$t['sku_item_code'];
				$sku[$t['sku_item_code']]['mcode']=$t['mcode'];
				$sku[$t['sku_item_code']]['artno']=$t['artno'];
				$sku[$t['sku_item_code']]['description']=$t['description'];
				
				$table[$t['p']][$t['sku_item_code']]['qty'][$lbl]+=$t['qty'];
				$table[$t['p']][$t['sku_item_code']]['amount'][$lbl]+=$t['amount'];
				$table[$t['p']][$t['sku_item_code']]['qty']['total']+=$t['qty'];
				$table[$t['p']][$t['sku_item_code']]['amount']['total']+=$t['amount'];

			}
		}
		$con_multi->sql_freeresult();
		$this->table = $table;
		$this->category2 = $category2;
		$this->sku = $sku;
		$this->label = $label;
		
		//$con_multi->close_connection();
	}
	
	function generate_report()
	{
		global $con, $smarty, $config, $con_multi;
		$this->load_branch_group();
		$branch_group = $this->branch_group;
		
		if($_REQUEST['branch_id'] < 0){   // is branch group
			$bg_id = abs($_REQUEST['branch_id']);
			if($branch_group['items'][$bg_id]){
				foreach($branch_group['items'][$bg_id] as $bid=>$b){
					if ($config['sales_report_branches_exclude']) {
						$branch_code = $b['code'];
						if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
							continue;
						}
					}
					$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
					$this->run_report($bid,$tbl_name);
				}
				$report_title[] = "Branch Group: ". $branch_group['header'][$bg_id]['code'];
			}
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
	            $this->run_report($bid,$tbl_name);
	            $report_title[] = "Branch: ".BRANCH_CODE;
			}else{
				if($bid==0){
	                if (!$config['allow_all_sku_branch_for_selected_reports']) die("No Branch Selected");
	                $q1 = $con_multi->sql_query("select id from branch where active=1 order by sequence, code");
					while ($r1 = $con_multi->sql_fetchassoc($q1)) {
						$bid = mi($r1['id']);
						$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
						$this->run_report($bid,$tbl_name);
					}
					$con_multi->sql_freeresult($q1);
				}else{
	                $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
		            $this->run_report($bid,$tbl_name);
					$report_title[] = "Branch: ".get_branch_code($bid);
				}
			}
		}
			
        $table = $this->table;
		$category2 = $this->category2;
		$sku = $this->sku;
		$label = $this->label;
		$category = $this->category;
		
		ksort($label);
		
		$report_title[] = "Date From: ".$_REQUEST['date_from']." To ".$_REQUEST['date_to'];
    
		$smarty->assign('report_title',join("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$report_title));
		$smarty->assign('label',$label);
		$smarty->assign('table',$table);
		$smarty->assign('category',$category);
		$smarty->assign('category2',$category2);
		$smarty->assign('sku',$sku);
		$smarty->assign('branch_name',$branch_name);
	}
	
	function process_form()
	{
	    global $con,$smarty,$con_multi;
		// do my own form process
		
		// call parent
		//parent::process_form();
		$this->date_from=$_REQUEST['date_from'];
		$this->date_to=$_REQUEST['date_to'];

		$mtest =strtotime("+30 day",strtotime($this->date_from));
		if (strtotime($this->date_to) > $mtest || strtotime($this->date_to)< strtotime($this->date_from)){
        	$this->date_to = date("Y-m-d",$mtest);
        	$_REQUEST['date_to'] = $this->date_to;
		}
		
		$code_list = $_REQUEST['sku_code_list_2'];

	    $filter[] = "date between ".ms($this->date_from)." and ".ms($this->date_to);

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
		$filter = join(" and ",$filter);
		$label = $this->generate_dates($this->date_from, $this->date_to, 'Ymd', 'd');
		
		$this->filter = $filter;
		$this->category = $category;
		$this->label = $label;
	}	

	function default_values()
	{
	    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month"));
		$_REQUEST['date_to'] = date("Y-m-d");
	}
}

$report = new DailySkuItemsSales('Daily SKU Items Sales');

?>
