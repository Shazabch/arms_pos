<?php
/*
1/25/2011 10:42:02 AM Alex
- change use report_server

5/25/2011 11:56:57 AM Alex
- fix total cost bugs

6/24/2011 6:24:11 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:33:48 PM Andy
- Change split() to use explode()

8/2/2011 11:07:22 AM Andy
- Fix a bugs if user select the lowest level category will cause sql error.

8/1/2012 4:19:13 PM Justin
- Fixed bug of showing sql error while filter by branch group.
- Fixed bug of report title.

4/2/2014 11:02 AM Fithri
- add option to select all SKU / branches for some reports, require config 'allow_all_sku_branch_for_selected_reports' to be turned on

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/19/2020 9:44 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");

class HourlySkuSalesByRace_Category extends Report
{
	function run_report($bid, $tbl_name)
	{
	    global $con_multi;

	  	$filter = $this->filter;
		$category = $this->category;
		$one_more_level = $this->one_more_level;
		$having = $this->having;
		$filter_type = $this->filter_type;

		$category2 = $this->category2;
		$table = $this->table;
		$total = $this->total;
		
		$tbl = $tbl_name['sku_items_sales_cache'];
	    if($filter_type=='category'){
	    	if($this->hit_max_cat_lv){
				$one_more_level = $this->curr_cat_lv;	// already hit max category level, use back current level as root
			}
        	$sql = "select sku_item_code,sku_item_id,sku_items.description,sum(qty) as qty,sum(cost) as cost,sum(amount) as selling_price,round((sum(amount)-sum(cost))/sum(amount)*100,2) as gp,p$one_more_level as p,category.description as cname
from $tbl pos
left join sku_items on sku_item_id = sku_items.id
left join sku on sku_id = sku.id
left join category_cache using (category_id)
left join category on category_cache.p$one_more_level = category.id
where $filter group by sku_item_id $having order by p";
		}else{
		    $sql = "select sku_item_code,sku_item_id,sku_items.description,sum(qty) as qty,sum(cost) as cost,sum(amount) as selling_price,round((sum(amount)-sum(cost))/sum(amount)*100,2) as gp,p3 as p,category.description as cname
from $tbl pos
left join sku_items on sku_item_id = sku_items.id
left join sku on sku_id = sku.id
left join category_cache using (category_id)
left join category on category_cache.p3 = category.id
where $filter group by sku_item_id $having order by p";
		}
		//print $sql.'<br /><br />';
		$con_multi->sql_query($sql) or die(sql_error());
   
		if($con_multi->sql_numrows()>0){
		    foreach($con_multi->sql_fetchrowset() as $t){
		        
                $category2[$t['p']]['category']=$t['p'];
                $category2[$t['p']]['description']=$t['cname'];
                $category2[$t['p']]['qty']+=$t['qty'];
                $category2[$t['p']]['cost_price']+=$t['cost'];
                $category2[$t['p']]['selling_price']+=$t['selling_price'];
				
				$table[$t['p']][$t['sku_item_code']]['sku_item_code']=$t['sku_item_code'];
				$table[$t['p']][$t['sku_item_code']]['description']=$t['description'];
				$table[$t['p']][$t['sku_item_code']]['qty']+=$t['qty'];
				//$table[$t['p']][$t['sku_item_code']]['amount']=$t['amount'];
				$table[$t['p']][$t['sku_item_code']]['cost_price']+=$t['cost'];
				$table[$t['p']][$t['sku_item_code']]['selling_price']+=$t['selling_price'];
				$table[$t['p']][$t['sku_item_code']]['gp']+=$t['gp'];
				
				$total['qty']+=$t['qty'];
				$total['cost_price']+=$t['cost'];
				$total['selling_price']+=$t['selling_price'];
			}
		}
		$con_multi->sql_freeresult();
		
		$this->category2 = $category2;
		$this->table = $table;
		$this->total = $total;
	}
	
	function generate_report()
	{
		global $con, $smarty, $con_multi;

		$branch_group = $this->branch_group;
	    if($_REQUEST['branch_id']=="")
	    {
	        $con_multi->sql_query("select * from branch where active=1 order by sequence,code");
	    	while($r = $con_multi->sql_fetchrow())
	        {
	    	    $brn_id[]= $r['id'];
	    	}
			$con_multi->sql_freeresult();
	    	foreach($brn_id as $bid)
	    	{
				$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
		    	$this->run_report($bid,$tbl_name);
	        }
	        $report_title[] = "Branch: All";
	    }
		elseif(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			
			if($branch_group['items'][$bg_id]){
				foreach($branch_group['items'][$bg_id] as $bid=>$b){
					$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
					$this->run_report($bid,$tbl_name);
				}
			}
			$report_title[] = "Branch Group: ".$branch_group['header'][$bg_id]['code'];
			
		}else{
	        $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
	            $this->run_report($bid,$tbl_name);
	            $report_title[] = "Branch: ".BRANCH_CODE;
			}else{
				if($bid==0){
	                die("No Branch Selected");
				}else{
	                $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
		            $this->run_report($bid,$tbl_name);
					$report_title[] = "Branch: ".get_branch_code($bid);
				}
			}
		}

	    $category = $this->category;
	    $category2 = $this->category2;
		$table = $this->table;
		$total = $this->total;

		$report_title[] = "Date From: ".$_REQUEST['date_from']." To ".$_REQUEST['date_to'];
		
		if($_REQUEST['min_gp']) $m_gp = $_REQUEST['min_gp'];
	    else $m_gp = '0';

		$report_title[] = "Minimum GP: ".$m_gp."%";

		if($_REQUEST['filter_type']=='category') $str_cat = 'Category';
		else $str_cat = 'Multiple SKU';
		
		$report_title[] = "Filter by: ".$str_cat;

	    $smarty->assign('report_title', join("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $report_title));
		$smarty->assign('table',$table);
		$smarty->assign('category',$category);
		$smarty->assign('category2',$category2);
		$smarty->assign('total',$total);
		$smarty->assign('branch_name',$branch_name);
	}
	
	function process_form()
	{
	    global $con,$smarty,$sessioninfo,$con_multi;
		// do my own form process
		
		/*
		print '<pre>';
		print_r($_REQUEST);
		print '</pre>';
		*/
		
		//temporarily bypass category checking in main parent class
		if ($_REQUEST['filter_type'] == 'category' && $_REQUEST['all_category']) $_REQUEST['filter_type'] = 'all';
		
		// call parent
		parent::process_form();
		
		//set it back to original value
		if ($_REQUEST['filter_type'] == 'all' && $_REQUEST['all_category']) $_REQUEST['filter_type'] = 'category';
		
		$this->date_from=$_REQUEST['date_from'];
		$this->date_to=$_REQUEST['date_to'];
		
		$mtest =strtotime("+1 year",strtotime($this->date_from));
		if (strtotime($this->date_to) > $mtest || strtotime($this->date_to)< strtotime($this->date_from)){
        	$this->date_to = date("Y-m-d",$mtest);
        	$_REQUEST['date_to'] = $this->date_to;
		}
		
		$filter_type = $_REQUEST['filter_type'];
	    $min_gp = $_REQUEST['min_gp'];

	    $filter[] = "pos.date between ".ms($this->date_from)." and ".ms($this->date_to);
	    $having = "having(round((sum(amount)-sum(cost))/sum(amount)*100,2)>=".mi($min_gp).")";
	    
	    if($filter_type=='category'){
          	$category_id = $_REQUEST['category_id'];
	        $con_multi->sql_query("select level from category where id=".mi($category_id)) or die(mysql_error());
  			$temp = $con_multi->sql_fetchrow();
  			$con_multi->sql_freeresult();
  			
  			$level = $temp['level'];
  			$one_more_level = $level+1;
  			
  			// check max level
  			$con_multi->sql_query("select max(level) from category");
  			$max_cat_level = mi($con_multi->sql_fetchfield(0));
  			$con_multi->sql_freeresult();
  			
  			if($level>=$max_cat_level){
				$this->hit_max_cat_lv = true;  
			}
			
			if (!$_REQUEST['all_category']) $filter[] = "p$level=".mi($category_id);
		}else{
		    $code_list = $_REQUEST['sku_code_list_2'];

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
		}
		
		$filter = join(" and ",$filter);
		
		$this->filter = $filter;
		$this->category = $category;
		$this->one_more_level = $one_more_level;
		$this->curr_cat_lv = $level;
		$this->having = $having;
		$this->filter_type = $filter_type;
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

//$con_multi = new mysql_multi();
$report = new HourlySkuSalesByRace_Category('Profit and Loss Report by SKU Items');
//$con_multi->close_connection();
?>
