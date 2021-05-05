<?php
/*
1/25/2011 4:49:04 PM Alex
- change use report server
- add department privilege filter
- fix date bugs

6/24/2011 6:16:57 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:40:58 PM Andy
- Change split() to use explode()

5/9/2012 10:25:14 AM Andy
- Fix invalid branch group SQL.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/19/2020 3:32 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
//$con = new sql_db('agrofresh-hq.dyndns.org','arms_slave','arms_slave','armshq');
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");

class MonthlySalesReportByCategory extends Report
{
	function run_report($bid,$tbl_name)
	{
	    global $con_multi;

		$filter = $this->filter;
		$table = $this->table;
		$category = $this->category;
		$label = $this->label;
		
		$tbl = $tbl_name['sku_items_sales_cache'];
		$sql="select year,month,sku_item_code,sku_item_id,sku_items.description,sum(pos.qty) as quantity,sum(pos.amount) as amount,p3,category.description as cname from
$tbl pos
left join sku_items on sku_item_id = sku_items.id
left join sku on sku_id = sku.id
left join category_cache using (category_id)
left join category on category_cache.p3 = category.id
where $filter group by sku_item_id,year,month,p3
order by year,month";

        $con_multi->sql_query($sql) or die(sql_error());//print "$sql<br /><br />";//xx
		if($con_multi->sql_numrows()>0){
            foreach($con_multi->sql_fetchrowset() as $r){
	            $lbl = sprintf("%04d%02d", $r['year'], $r['month']);
			    $label[$lbl] = $this->months[$r['month']] ." " . $r['year'];
                
                $category[$r['p3']]['name']=$r['cname'];
                $category[$r['p3']]['quantity'][$lbl]+=$r['quantity'];
                $category[$r['p3']]['quantity']['total']+=$r['quantity'];
                $category[$r['p3']]['amount'][$lbl]+=$r['amount'];
                $category[$r['p3']]['amount']['total']+=$r['amount'];
                
                $category['total']['quantity'][$lbl]+=$r['quantity'];
                $category['total']['quantity']['total']+=$r['quantity'];
                $category['total']['amount'][$lbl]+=$r['amount'];
                $category['total']['amount']['total']+=$r['amount'];
                
				$table[$r['p3']][$r['sku_item_id']]['quantity'][$lbl]+=$r['quantity'];
				$table[$r['p3']][$r['sku_item_id']]['amount'][$lbl]+=$r['amount'];
				$table[$r['p3']][$r['sku_item_id']]['sku_item_id'] = $r['sku_item_id'];
			    $table[$r['p3']][$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];
			    $table[$r['p3']][$r['sku_item_id']]['description'] = $r['description'];
			    $table[$r['p3']][$r['sku_item_id']]['quantity']['total'] += $r['quantity'];
			    $table[$r['p3']][$r['sku_item_id']]['amount']['total'] += $r['amount'];
	        }
		}
		$con_multi->sql_freeresult();
		$this->table = $table;
		$this->category = $category;
		$this->label = $label;
	}
	
	function sort_table($a,$b)
	{
	    if(isset($_REQUEST['quantity_amount_type'])){
	        $arrange_type = $this->quantity_amount_type;
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

	function generate_report()
	{
		global $con, $smarty, $con_multi;
		$branch_group = $this->branch_group;
	    
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			foreach($branch_group['items'][$bg_id] as $tmp_bid=>$b){
				$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$tmp_bid;
			    $this->run_report($tmp_bid,$tbl_name);
			}
			$branch_name = $branch_group['header'][$bg_id]['code'];
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
	            $this->run_report($bid,$tbl_name);
	            $branch_name = BRANCH_CODE;
			}else{
				if($bid==0){
	                $branch_name = "All";
	                $q_b = $con_multi->sql_query("select * from branch where active=1 order by sequence,code");
	                while($r = $con_multi->sql_fetchrow($q_b)){
                        $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$r['id'];
			            $this->run_report($r['id'],$tbl_name);
					}
					$con_multi->sql_freeresult($q_b);
					/*if($branch_group['header']){
						foreach($branch_group['header'] as $bg_id=>$bg){
                            $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_bg".$bg_id;
				            $this->run_report($bg_id+10000,$tbl_name);
						}
					}*/
				}else{
	                $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
		            $this->run_report($bid,$tbl_name);
					$branch_name = get_branch_code($bid);
				}
			}
		}
        $table = $this->table;
		$category = $this->category;
		$label = $this->label;
		
		$brand_id = $this->brand_id;
		$brand_name = get_brand_title($brand_id);
		
		@ksort($label);

        $rpt_title[] = "Branch: $branch_name";
        $rpt_title[] = "Date: From $this->date_from to $this->date_to";
        $rpt_title[] = "Category: $this->cat_desc";
        $rpt_title[] = "Brand: $brand_name";
    
		$report_title = join("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $rpt_title);
    
    	$smarty->assign('report_title',$report_title);
    
        $smarty->assign('brand_name',$brand_name);
		$smarty->assign('label',$label);
		$smarty->assign('table',$table);
		$smarty->assign('category',$category);
		$smarty->assign('branch_name',$branch_name);
	}
	
	function process_form()
	{
		// do my own form process
		global $con,$smarty,$sessioninfo,$con_multi;

		// call parent
		parent::process_form();
		
		$department_id = $_REQUEST['department_id'];
	    $brand_id = $_REQUEST['brand_id'];

		$this->date_from=$date_f=$_REQUEST['date_from'];
		$this->date_to=$date_t=$_REQUEST['date_to'];

		$mtest =strtotime("+1 year",strtotime($this->date_from));
		if (strtotime($this->date_to) > $mtest || (strtotime($date_t)< strtotime($date_f))){
        	$this->date_to = date("Y-m-d",$mtest);
        	$_REQUEST['date_to'] = $this->date_to;
		}
		
		$category_id = $_REQUEST['category_id'];
	    $filter = array();
	    if($category_id)
	    {
	        $con_multi->sql_query("select level, description from category where id=".mi($category_id)) or die(mysql_error());
    		$temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
    		$level = $temp['level'];
			$this->cat_desc = $temp['description'];
    		$filter[] = "p$level=".mi($category_id);
	    }else{
            $filter[] = "p2 in ($sessioninfo[department_ids])";
            $this->cat_desc = "All";
		}
		
		$filter[] = "pos.date between ".ms($this->date_from)." and ".ms($this->date_to);
		$filter[] = "brand_id in (".join(',',process_brand_id($brand_id)).")";
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';
		$filter = join(' and ', $filter);
		
		$this->filter = $filter;
		$this->department_id = $department_id;
		$this->brand_id = $brand_id;
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
$report = new MonthlySalesReportByCategory('Monthly Brand Sales by Category');
//$con_multi->close_connection();
?>
