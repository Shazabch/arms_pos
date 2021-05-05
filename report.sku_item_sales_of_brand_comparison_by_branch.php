<?php
/*
1/24/2011 11:15:24 AM Alex
- change use report_server

6/24/2011 6:29:18 PM Andy
- Make all branch default sort by sequence, code.

5/9/2012 10:11:10 AM Andy
- Fix invalid branch group SQL.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/19/2020 10:39 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
//$con = new sql_db('agrofresh-hq.dyndns.org','arms_slave','arms_slave','armshq');
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");
if (BRANCH_CODE != 'HQ') js_redirect($LANG['REPORT_IS_HQ_ONLY'], "/index.php");

class BrandSales extends Report
{
	function run_report($bid, $tbl_name)
	{
		global $con_multi;
        
        $group_by_sku = $this->group_by_sku;
        $one_more_level = $this->one_more_level;
        $filter = $this->filter;

		$category2 = $this->category2;
		$table = $this->table;
		$label = $this->label;
		$sku = $this->sku;
		
		if($one_more_level)
		{
        $str_category = " left join category_cache using (category_id)
left join category on category_cache.p$one_more_level = category.id ";
        $str_p = "p$one_more_level as p,category.description as cname,";
        $order_p = " order by p";
    }else
    {
        $str_category = " left join category_cache using (category_id)
left join category on category_cache.p2 = category.id ";
        $str_p = "p2 as p,category.description as cname,";
        $order_p = " order by p";
    }

		$tbl = $tbl_name['sku_items_sales_cache'];
		
		if($group_by_sku){
            $sql = "select sku_items.sku_id,sum(pos.qty) as qty,sum(pos.amount) as amount,sku_items.description,".$str_p."brand_id,brand.description as bname
from $tbl pos
left join sku_items on sku_item_id = sku_items.id
left join sku on sku_id = sku.id".$str_category."
left join brand on brand_id=brand.id
where $filter group by sku_items.sku_id".$order_p;
 
        }else{
            $sql = "select sku_item_code,sku_item_id as sku_item_id,sum(pos.qty) as qty,sum(pos.amount) as amount,sku_items.description,".$str_p."brand_id,brand.description as bname
from $tbl pos
left join sku_items on sku_item_id = sku_items.id
left join sku on sku_id = sku.id".$str_category."
left join brand on brand_id=brand.id
where $filter group by sku_item_id".$order_p;
  
		}
		
        $lbl = $bid;
                
		$con_multi->sql_query($sql) or die(sql_error());//print "$sql<br /><br />";//xx
        $category2['root']['description'] = $description;
		if($con_multi->sql_numrows()>0){
		    foreach($con_multi->sql_fetchrowset() as $t){
                if($group_by_sku){
                    $key = $t['sku_id'];
                    
                    $sku[$key]['sku_id']=$t['sku_id'];
                }else{
					$key = $t['sku_item_code'];
					$sku[$key]['sku_item_code']=$t['sku_item_code'];
					$sku[$key]['sku_item_id']=$t['sku_item_id'];
					$sku[$key]['artno']=$t['artno'];
				}
				
				$category2[$t['p']]['description']=$t['cname'];
	            $category2[$t['p']]['qty'][$lbl]+=$t['qty'];
	            $category2[$t['p']]['amount'][$lbl]+=$t['amount'];
	            $category2[$t['p']]['qty']['total']+=$t['qty'];
	            $category2[$t['p']]['amount']['total']+=$t['amount'];
	            $category2['total']['qty'][$lbl]+=$t['qty'];
	            $category2['total']['amount'][$lbl]+=$t['amount'];
	            $category2['total']['qty']['total']+=$t['qty'];
	            $category2['total']['amount']['total']+=$t['amount'];

				$sku[$key]['description']=$t['description'];

				$table[$t['p']][$key]['qty'][$lbl]+=$t['qty'];
				$table[$t['p']][$key]['amount'][$lbl]+=$t['amount'];
				$table[$t['p']][$key]['qty']['total']+=$t['qty'];
				$table[$t['p']][$key]['amount']['total']+=$t['amount'];
			}
		}
		$con_multi->sql_freeresult();
		
		$this->category2 = $category2;
		$this->table = $table;
		$this->label = $label;
		$this->sku = $sku;
	}
	
	function generate_report()
	{
		global $con, $smarty,$start_date,$end_date,$con_multi;
		
		$branch_group = $this->branch_group;
		$selected_bg_id = intval($_REQUEST['branch_group']);

		if($selected_bg_id==0){ // all
            // run single branch
			$rs = $con_multi->sql_query("select * from branch where active=1 order by sequence,code");
			$branches = $con_multi->sql_fetchrowset($rs);
			$con_multi->sql_freeresult($rs);
			if($branches){
                foreach($branches as $branch){
                    $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".mi($branch['id']);
                    $this->run_report($branch['id'],$tbl_name);
                    $this->label[$branch['id']] = $branch['code'];
				}
			}

			// run branches group
			/*if($branch_group['header']){
				foreach($branch_group['header'] as $bg_id=>$bg){
				    $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_bg".mi($bg['id']);
                    $this->run_report($bg_id+10000,$tbl_name);
                    $this->label[$bg_id+10000] = $bg['code'];
				}
			}*/
		}else{  // selected branch group
			foreach($branch_group['items'][$selected_bg_id] as $bid=>$b){
                $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".mi($bid);
                $this->run_report($bid,$tbl_name);
                $this->label[$bid] = $b['code'];
			}
		}
		
		$category2 = $this->category2;
		$table = $this->table;
		$label = $this->label;
		$sku = $this->sku;
			
		$brand_id = $this->brand_id;
		if($brand_id!=0){
			/*
            $con->sql_query("select description from brand where id=".mi($brand_id))or die(mysql_error());
            $bn = $con->sql_fetchrow();
            $brand_name = $bn['description'];
			*/
			$brand_name = get_brand_title($brand_id);
		}else{
			$brand_name = "UNBRANDED";
		}
		
		$report_title = "Start Date: ".$start_date."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End Date: ".$end_date."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Brand: ".$brand_name;
		
		if($_REQUEST['branch_group'])
		{
		    $report_title.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Branch Group: ".$branch_group['header'][$_REQUEST['branch_group']]['code'];
    }
    		
		$smarty->assign('report_title',$report_title);
		
		$smarty->assign('brand_name',$brand_name);
		
		$smarty->assign('label',$label);
		$smarty->assign('table',$table);
		$smarty->assign('category2',$category2);
		$smarty->assign('sku',$sku);
	}
	
	function process_form()
	{
	    global $config,$con,$smarty,$start_date,$end_date,$con_multi;
	    
		// do my own form process
		
		// call parent
		parent::process_form();
		
		$date = $_REQUEST['date'];
		
		if($_REQUEST['filter_date']=='mtd'){
			$start_date = date("Y",strtotime($date))."-".date("m",strtotime($date))."-1";
			$end_date = $date;
		}else{
            $year = date("Y",strtotime($date));
			$financial_start_date = $year.$config['financial_start_date'];
			if(strtotime($date)<strtotime($financial_start_date)){
				$year--;
				$financial_start_date = $year.$config['financial_start_date'];
			}
			$start_date = $financial_start_date;
			$end_date = $date;
		}

		$filter_date = "date between ".ms($start_date)." and ".ms($end_date);
		
		$this->group_by_sku = $_REQUEST['group_sku'];

	    $category_id = $_REQUEST['category_id'];
	    $brand_id = $_REQUEST['brand_id'];
	    $this->brand_id = $brand_id;
      
		if($category_id)
		{
		    $con_multi->sql_query("select level,description from category where id=".mi($category_id)) or die(mysql_error());
			$temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$level = $temp['level'];
			$description = $temp['description'];
		
			// check one more level for grouping
			$con_multi->sql_query("select max(level) from category") or die(mysql_error());
			$max_level = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
			if($level<$max_level)	$this->one_more_level = $level+1;
			else    $this->one_more_level = $level;
		
			$filter[] = "p$level=".mi($category_id);
		}
	    
		$filter[] = $filter_date;
		$filter[] = "brand_id in (".join(',',process_brand_id($brand_id)).")";
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';

		$filter = join(" and ",$filter);
		$this->filter = $filter;
		
		$smarty->assign('start_date',$start_date);
		$smarty->assign('end_date',$end_date);
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
	
	function sort_table($a,$b)
	{
	    if(isset($_REQUEST['quantity_amount_type'])){
	        $arrange_type = $_REQUEST['quantity_amount_type'];
	    }else{
            $arrange_type = 'amount';
		}

	    if ($a[$arrange_type]['total']==$b[$arrange_type]['total']) return 0;
	    if($this->order_type=="bottom"){
            return ($a[$arrange_type]['total']<$b[$arrange_type]['total']) ? -1 : 1;
		}else{
            return ($a[$arrange_type]['total']>$b[$arrange_type]['total']) ? -1 : 1;
		}

	}
	
}
//$con_multi = new mysql_multi();
$report = new BrandSales('SKU Items Sales of Brand Comparison by Branch');
//$con_multi->close_connection();
?>
