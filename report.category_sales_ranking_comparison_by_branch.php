<?php
/*
1/18/2011 9:31:03 AM Alex
- change use report_server

6/24/2011 5:57:58 PM Andy
- Make all branch default sort by sequence, code.

9/21/2011 12:06:55 PM Alex
- fix category level control

5/9/2012 10:10:47 AM Andy
- Fix invalid branch group SQL.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/26/2019 5:48 PM Andy
- Enhanced the report to show item Old Code.

2/18/2020 5:04 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
//$con = new sql_db('agrofresh-hq.dyndns.org','arms_slave','arms_slave','armshq');
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");
if (BRANCH_CODE != 'HQ') js_redirect($LANG['REPORT_IS_HQ_ONLY'], "/index.php");

class DailyPwpSkuSales extends Report
{	
	function run_report($bid,$tbl_name)
	{
		global $con,$con_multi;
		
        $table = $this->table;
        $label = $this->label;
        $group_by_sku = $this->group_by_sku;
        $filter = $this->filter;
        $one_more_level = $this->one_more_level;
        
        if($one_more_level)
        {
            $str_category = " left join category_cache using (category_id)
left join category on category_cache.p$one_more_level = category.id ";
            
            $str_p = "p$one_more_level as p,category.description as cname,";
        }
    
        $tbl = $tbl_name['sku_items_sales_cache'];
        if ($group_by_sku){
            $sql = "select sku_items.sku_id,sum(pos.qty) as qty,sum(pos.amount) as amount,sku_items.description,".$str_p."cost, sku_items.mcode, sku_items.artno, sku_items.link_code
from $tbl pos
left join sku_items on sku_item_id = sku_items.id
left join sku on sku_id = sku.id".$str_category."
where $filter group by sku_items.sku_id";
          
        }else{
            $sql = "select sku_items.sku_item_code,sku_item_id as sku_item_id,sum(pos.qty) as qty,sum(pos.amount) as amount,sku_items.description,".$str_p."cost, sku_items.mcode, sku_items.artno, sku_items.link_code
from $tbl pos
left join sku_items on sku_item_id = sku_items.id
left join sku on sku_id = sku.id".$str_category."
where $filter group by sku_item_code";
            
		}
        $lbl = $bid;
    
		$con_multi->sql_query($sql) or die(sql_error());//print "$sql<br /><br />";

		if($con_multi->sql_numrows()>0){
		    foreach($con_multi->sql_fetchrowset() as $t){
                if($group_by_sku){
                    $key = $t['sku_id'];
                    $table[$key]['sku_id']=$t['sku_id'];
                }else{
					$key = $t['sku_item_code'];
					$table[$key]['sku_item_code']=$t['sku_item_code'];
				}
				$table[$key]['mcode']=$t['mcode'];
				$table[$key]['artno']=$t['artno'];
				$table[$key]['link_code']=$t['link_code'];
				
				$gp="";

                $table[$key]['description']=$t['description'];
                $table[$key]['sku_item_id']=$t['sku_item_id'];
                
				$table[$key]['qty'][$lbl]+=$t['qty'];
				$table[$key]['amount'][$lbl]+=$t['amount'];
				$table[$key]['cost'][$lbl]+=$t['cost'];
				$gp = $table[$key]['amount'][$lbl]-$table[$$key]['cost'][$lbl];
				$table[$key]['gp'][$lbl]+=$gp;
				
				$table[$key]['qty']['total']+=$t['qty'];
				$table[$key]['amount']['total']+=$t['amount'];
				$table[$key]['cost']['total']+=$t['cost'];
				$gp = $table[$key]['amount']['total']-$table[$key]['cost']['total'];
				$table[$key]['gp']['total']=$gp;
				
				if($table[$key]['min_qty']==''){
                    $table[$key]['min_qty'] = $t['qty'];
				}else{
					if($t['qty'] < $table[$key]['min_qty']){
                        $table[$key]['min_qty'] = $t['qty'];
					}
				}
				
				if($table[$key]['min_amount']==''){
                    $table[$key]['min_amount'] = $t['amount'];
				}else{
					if($t['amount'] < $table[$key]['min_amount']){
                        $table[$key]['min_amount'] = $t['amount'];
					}
				}
			}
		}
		$con_multi->sql_freeresult();
		$this->table = $table;
        $this->label = $label;
	}
	
	function generate_report()
	{
		global $con, $smarty,$start_date,$end_date,$con_multi;
		
		$filter_number = intval($_REQUEST['filter_number']);
		$minimum_transaction = intval($_REQUEST['min_tran']);
	    $minimum_amount = doubleval($_REQUEST['min_amount']);
		
		if($filter_number > 1000 || $filter_number < 1){
			$filter_number = 1000;
		}
		
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
		  if($branch_group['items'][$selected_bg_id])
		  {
          foreach($branch_group['items'][$selected_bg_id] as $bid=>$b){
                $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".mi($bid);
                $this->run_report($bid,$tbl_name);
                $this->label[$bid] = $b['code'];
			   }
      }
		}
		
		$table = $this->table;
        $label = $this->label;
    
    if($table)
    {
        foreach(array_keys($table) as $k)
    		{
    			$a1=array();$a2=array();
    			foreach($label as $lbl=>$dummy)
    			{
    				$a1[$lbl] = doubleval($table[$k]['amount'][$lbl]);
    			}
    			if (min($a1)<$minimum_amount) { unset($table[$k]); continue; }
    			foreach($label as $lbl=>$dummy)
    			{
    				$a2[$lbl] = doubleval($table[$k]['qty'][$lbl]);
    			}
    			if (min($a2)<$minimum_transaction) unset($table[$k]);
    		}
    }
		
        
        @usort($table, array($this,"sort_table"));
        
        for($i=0; $i<$filter_number; $i++){
			foreach($label as $idx=>$dummy){
				$table2['amount'][$idx]+=$table[$i]['amount'][$idx];
				$table2['qty'][$idx]+=$table[$i]['qty'][$idx];
				$table2['cost'][$idx]+=$table[$i]['cost'][$idx];
				$table2['gp'][$idx]+=$table[$i]['gp'][$idx];

				$table2['amount']['total']+=$table[$i]['amount'][$idx];
				$table2['qty']['total']+=$table[$i]['qty'][$idx];
				$table2['cost']['total']+=$table[$i]['cost'][$idx];
				$table2['gp']['total']+=$table[$i]['gp'][$idx];
			}
		}
		
		$report_title = "Start Date: ".$start_date."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End Date: ".$end_date;
	
		if($_REQUEST['branch_group'])
		{
        $report_title.= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Branch Group: ".$branch_group['header'][$_REQUEST['branch_group']]['code'];
    }
		$report_title.= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;List by: ".$_REQUEST['quantity_amount_type'];
		$smarty->assign('report_title',$report_title);
		
		$smarty->assign('label',$label);
		$smarty->assign('table',$table);
		$smarty->assign('table2',$table2);
		$smarty->assign('filter_number',$filter_number);
	}
	
	function process_form()
	{
	    global $con,$smarty,$config,$start_date,$end_date,$con_multi;
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

		if($category_id)
		{
          	$con_multi->sql_query("select level from category where id=".mi($category_id)) or die(mysql_error());
			$temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$level = $temp['level'];
			// check one more level for grouping
			$con_multi->sql_query("select max(level) from category") or die(mysql_error());
			$max_level = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
			if($level<$max_level)	$this->one_more_level = $level+1;
			else    $this->one_more_level = $level;
			
			$filter[] = "p$level=".mi($category_id);
      	}
	    
		$filter[] = $filter_date;
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

/*$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}*/

$report = new DailyPwpSkuSales('Category Sales Ranking Comparison by Branch');
//$con_multi->close_connection();
?>
