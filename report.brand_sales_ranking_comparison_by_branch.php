<?php
/*
REVISION HISTORY
================

6/09/2010 4:10:46 PM Justin
- Allow branch to view, but only show their own branch (now only HQ can view)
- Add filter "SKU type" -> consignment / outright / all

6/15/2010 4:11:20 PM Justin
- Fixed the bugs of unable to display total for column and row.

1/17/2011 5:12:34 PM Alex
- change use report_server

6/24/2011 5:55:29 PM Andy
- Make all branch default sort by sequence, code.

9/21/2011 12:06:55 PM Alex
- fix category level control
 
5/9/2012 10:09:44 AM Andy
- Fix invalid branch group SQL.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

8/20/2014 11:35 AM Fithri
- fix problem cannot load report when select some category/brand

12/04/2015 1:30PM DingRen
- fix brand group wrong title

2/19/2020 11:16 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
include("include/class.report.php");

ini_set('memory_limit', '1024M');
set_time_limit(0);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");

class BrandSales extends Report
{
	function run_report($bid,$tbl_name)
	{
		global $con,$con_multi,$sessioninfo;

		$filter = $this->filter;
		$table = $this->table;
		$label = $this->label;
		$one_more_level = $this->one_more_level;
		
		$tbl = $tbl_name['sku_items_sales_cache'];
		$sql = "select sum(pos.qty) as qty,sum(pos.amount) as amount,brand_id,brand.description as bname
from $tbl pos
straight_join sku_items on sku_item_id = sku_items.id
straight_join sku on sku_id = sku.id
straight_join category_cache on sku.category_id=category_cache.category_id
left join brand on brand_id=brand.id
where $filter group by brand_id";
  //echo $sql."<br/>";
  
        $lbl = $bid;
        //$label[$lbl] = $branch_code;
                
		$con_multi->sql_query($sql) or die(sql_error());//print "$sql<br /><br />";die;

		if($con_multi->sql_numrows()>0){
		    foreach($con_multi->sql_fetchrowset() as $t){
                $table[$t['brand_id']]['brand_id']=$t['brand_id'];
                $table[$t['brand_id']]['description']=$t['bname'];
                
                
				$table[$t['brand_id']]['qty'][$lbl]+=$t['qty'];
				$table[$t['brand_id']]['amount'][$lbl]+=$t['amount'];
				
				$table[$t['brand_id']]['qty']['total']+=$t['qty'];
				$table[$t['brand_id']]['amount']['total']+=$t['amount'];
			}
		}
		$con_multi->sql_freeresult();
		
		$this->table = $table;
		$this->label = $label;
	}
	
	function generate_report()
	{
		global $con, $smarty,$start_date,$end_date, $con_multi;

		$filter_number = intval($_REQUEST['filter_number']);
		
		if($filter_number > 1000 || $filter_number < 1){
			$filter_number = 1000;
		}
		
		$branch_group = $this->branch_group;
		$selected_bg_id = intval($_REQUEST['branch_group']);
		
		if(BRANCH_CODE == 'HQ'){
			if($selected_bg_id==0){ // all
	            // run single branch
				$rs = $con_multi->sql_query("select * from branch where active=1 order by sequence,code");
				$branches = $con_multi->sql_fetchrowset($rs);
				if($branches){
	                foreach($branches as $branch){
	                    $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".mi($branch['id']);
	                    $this->run_report($branch['id'],$tbl_name);
	                    $this->label[$branch['id']] = $branch['code'];
					}
				}
				$con_multi->sql_freeresult($rs);
	
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
		}else{
			$bid = get_request_branch(true);
			$bcode = get_branch_code($bid);
            $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".mi($bid);
            $this->run_report($bid,$tbl_name);
            $this->label[$bid] = $bcode;
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
			usort($table, array($this,"sort_table"));
    	}

        for($i=0; $i<$filter_number; $i++){
			foreach($label as $idx=>$dummy){
				$table2['amount'][$idx]+=$table[$i]['amount'][$idx];
				$table2['qty'][$idx]+=$table[$i]['qty'][$idx];

				$table2['amount']['total']+=$table[$i]['amount'][$idx];
				$table2['qty']['total']+=$table[$i]['qty'][$idx];
			}
		}
		
		$sku_type = ($_REQUEST['sku_type']) ? $_REQUEST['sku_type'] : "All";
		
		$brand_id = $_REQUEST['brand_id'];
		if($brand_id=='All'){
            $brand_name = 'All';
		}else{
            if($brand_id!=0 || stripos($brand_id, 'brandgroup') !== false){
				/*
	            $con->sql_query("select description from brand where id=".mi($brand_id))or die(mysql_error());
	            $bn = $con->sql_fetchrow();
	            $brand_name = $bn['description'];
				*/
	            $brand_name = get_brand_title($brand_id);
			}else{
				$brand_name = "UNBRANDED";
			}
		}
		
		$report_title[] = "Start Date: ".$start_date;
		$report_title[] = "End Date: ".$end_date;
		$report_title[] = "SKU Type: ".$sku_type;
		$report_title[] = "Brand: ".$brand_name;
		
		if($_REQUEST['branch_group']){
		    $report_title[] = "Branch Group: ".$branch_group['header'][$_REQUEST['branch_group']]['code'];
    	}
    	
		$smarty->assign('report_title',join("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $report_title));
		$smarty->assign('brand_name',$brand_name);
		$smarty->assign('label',$label);
		$smarty->assign('table',$table);
		$smarty->assign('table2',$table2);
		$smarty->assign('filter_number',$filter_number);
	}
	
	function process_form()
	{
	    global $config,$con,$smarty,$sessioninfo,$start_date,$end_date,$con_multi;
		// do my own form process

		// call parent
		parent::process_form();
		
		$date = $_REQUEST['date'];
		$sku_type = $_REQUEST['sku_type'];
		
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
		
		$category_id = $_REQUEST['category_id'];
	    $brand_id = $_REQUEST['brand_id'];
	    $filter_type = $_REQUEST['filter_type'];

	    if($filter_type=='category'){
	        $con_multi->sql_query("select level from category where id=".mi($category_id)) or die(mysql_error());
			$temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$level = $temp['level'];

    		// check one more level for grouping
    		$con_multi->sql_query("select max(level) from category") or die(mysql_error());
    		$max_level = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
    		if($level<$max_level)	$one_more_level = $level+1;
    		else    $one_more_level = $level;

			$filter[] = "p$level=".mi($category_id);
	    }else{
            $one_more_level = 2;
		}

		$filter[] = $filter_date;

		if($sku_type){
			$filter[] = "sku.sku_type = '".$sku_type."'";
		}

		if($brand_id!='All'){
			$filter[] = "brand_id in (".join(',',process_brand_id($brand_id)).")";
		}else{
			if($sessioninfo['brand_ids']){
				$filter[] = "brand_id in ($sessioninfo[brand_ids])";
			}
		}

		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';
		
		$filter = join(" and ",$filter);
		
		$this->filter = $filter;
		$this->one_more_level = $one_more_level;
		
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

$report = new BrandSales('Brand Sales Ranking Comparison by Branch');
/*$con_multi->close_connection();*/
?>
