<?php
/*
1/19/2011 10:28:46 AM Alex
- change use report_server

6/16/2011 3:36:25 PM Andy
- Fix report show wrong initial from date.

6/24/2011 6:06:50 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:29:44 PM Andy
- Change split() to use explode()

10/14/2011 5:18:42 PM Alex
- add assign page title

8/1/2012 3:03 PM Justin
- Fixed bug of showing sql error while filter by branch group.
- Fixed bug of report title.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/18/2020 5:47 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
//$con = new sql_db('agrofresh-hq.dyndns.org','arms_slave','arms_slave','armshq');

include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");

class DailyPwpSkuSales extends Report
{
	function run_report($bid, $tbl_name)
	{
	    global $con,$smarty,$branch_name,$con_multi;
	    $smarty->assign('PAGE_TITLE', 'Daily PWP SKU Sales');

	    $category_id = $_REQUEST['category_id'];
		$filter = $this->filter;
		$one_more_level = $this->one_more_level;
		$table = $this->table;
		$category2 = $this->category2;
		$sku = $this->sku;
		$label = $this->label;
		$tbl = $tbl_name['pwp_sales_cache'];
		
		if($one_more_level)
		{
        $str_category = " left join category_cache using (category_id)
						left join category on category_cache.p$one_more_level = category.id ";
        $str_p = "p$one_more_level as p,category.description as cname,";
        $order_p = ",p";
	    }

		if(!$tbl)
	    {
	        $this->do_multiple_branch();

	        exit;
    	}

		/*$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/

	    $sql = "select pos.date,pos.year,pos.month,pos.day,pos.sku_item_code,sku_items.id as sku_item_id,sum(pos.qty) as qty,sum(pos.amount) as amount,sku_items.description,".$str_p."sku_items.artno
				from $tbl pos
				left join sku_items using(sku_item_code)
				left join sku on sku_items.sku_id = sku.id".$str_category."
				where $filter group by date,sku_item_code order by date".$order_p;

		$con_multi->sql_query($sql) or die(sql_error());//print "$sql<br /><br />";//xx
		if($con_multi->sql_numrows()>0)
		{
		    foreach($con_multi->sql_fetchrowset() as $t){
		        $lbl = sprintf("%04d%02d%02d", $t['year'],$t['month'],$t['day']);

                $category2[$t['p']]['description']=$t['cname'];
	            $category2[$t['p']]['qty'][$lbl]+=$t['qty'];
	            $category2[$t['p']]['amount'][$lbl]+=$t['amount'];
	            $category2[$t['p']]['qty']['total']+=$t['qty'];
	            $category2[$t['p']]['amount']['total']+=$t['amount'];
	            $category2['total']['qty'][$lbl]+=$t['qty'];
	            $category2['total']['amount'][$lbl]+=$t['amount'];
	            $category2['total']['qty']['total']+=$t['qty'];
	            $category2['total']['amount']['total']+=$t['amount'];

      				$sku[$t['sku_item_code']]['sku_item_code']=$t['sku_item_code'];
      				$sku[$t['sku_item_code']]['sku_item_id']=$t['sku_item_id'];
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
		global $con, $smarty,$branch_name,$con_multi;
		
		$branch_group = $this->branch_group;

		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			if($branch_group['items'][$bg_id]){
				foreach($branch_group['items'][$bg_id] as $bid=>$b){
					$tbl_name['pwp_sales_cache'] = "pwp_sales_cache_b".$bid;
					$this->run_report($bid,$tbl_name);
				}
			}
			$report_title[] = "Branch Group: ".$branch_group['header'][$bg_id]['code'];			
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $tbl_name['pwp_sales_cache'] = "pwp_sales_cache_b".$bid;
	            $report_title[] = "Branch: ".BRANCH_CODE;
	            $this->run_report($bid,$tbl_name);
	            
			}else{
				if($bid==0){
	                //die("No Branch Selected");
	                $report_title[] = "Branch: All";
					$this->run_report($bid,$tbl_name);   
				}else{
	                $tbl_name['pwp_sales_cache'] = "pwp_sales_cache_b".$bid;
	                $report_title[] = "Branch: ".get_branch_code($bid);
		            $this->run_report($bid,$tbl_name);
					
				}
			}
		}
		
		$table = $this->table;
		$category2 = $this->category2;
		$sku = $this->sku;
		$label = $this->label;
	
		$report_title[] = "Date From: ".$_REQUEST['date_from']." to ".$_REQUEST['date_to'];
	
		$con_multi->sql_query("select level,description from category where id=".mi($_REQUEST['category_id'])) or die(mysql_error());
		$temp = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		$cname = $temp['description'];

		if($cname) $c_name = $cname;
		else $c_name = "All";
		$report_title[] = "Category: ".$c_name;
	
    	$smarty->assign('report_title', join("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $report_title));
		$smarty->assign('label',$label);
		$smarty->assign('table',$table);
		$smarty->assign('category2',$category2);
		$smarty->assign('sku',$sku);
		$smarty->assign('branch_name',$branch_name);
	}
	
	function process_form()
	{
	    global $con,$smarty,$sessioninfo,$con_multi;
		// do my own form process
		
		// call parent
		parent::process_form();

		$this->date_from=$_REQUEST['date_from'];
		$this->date_to=$_REQUEST['date_to'];
		
		$mtest =strtotime("+1 months",strtotime($this->date_from));
		
		if (strtotime($this->date_to) > $mtest || strtotime($this->date_to) < strtotime($this->date_from)){
        	$this->date_to = date("Y-m-d",$mtest);
        	$_REQUEST['date_to'] = $this->date_to;
		}
		
		$category_id = $_REQUEST['category_id'];
  
	    if($category_id!="")
	    {
	        $con_multi->sql_query("select level,description from category where id=".mi($category_id)) or die(mysql_error());
    		$temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
    		$level = $temp['level'];
    		$cname = $temp['description'];

    		// check one more level for grouping
    		$con_multi->sql_query("select max(level) from category") or die(mysql_error());
    		$max_level = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
    		if($level<$max_level)	$one_more_level = $level+1;
    		else    $one_more_level = $level;

    		$filter[] = "p$level=".mi($category_id);
	    }
	    
		$filter[] = "pos.date between ".ms($this->date_from)." and ".ms($this->date_to);
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';

		$filter = join(" and ",$filter);
		$label = $this->generate_dates($this->date_from, $this->date_to, 'Ymd', 'd');
		
		$this->filter = $filter;
		$this->one_more_level = $one_more_level;
		
		$smarty->assign('cname',$cname);
	}	

	function default_values()
	{
	    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month"));
		$_REQUEST['date_to'] = date("Y-m-d");
	}	
	function do_multiple_branch()
	{
	    global $con,$smarty,$con_multi;
	    
	    $filter = $this->filter;
	    $category_id = $_REQUEST['category_id'];
	    
	    $con_multi->sql_query("select * from branch where active=1 order by sequence,code");
  		while($r = $con_multi->sql_fetchrow())
      {
  			$branches[$r['id']] = $r;
  		}
		$con_multi->sql_freeresult();
  	  if($one_more_level)
  		{
          $str_category = " left join category_cache using (category_id)
  left join category on category_cache.p$one_more_level = category.id ";
          $str_p = "p$one_more_level as p,category.description as cname,";
          $order_p = ",p";
      }
	    
		/*$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/

	    foreach($branches as $bid=>$v)
	    {
          $tbl = "pwp_sales_cache_b".$v['id'];
          $sql = "select pos.date,pos.year,pos.month,pos.day,pos.sku_item_code,sku_items.id as sku_item_id,sum(pos.qty) as qty,sum(pos.amount) as amount,sku_items.description,".$str_p."sku_items.artno,sku.category_id as sku_cat_id
				from $tbl pos
				left join sku_items using(sku_item_code)
				left join sku on sku_items.sku_id = sku.id".$str_category."
				where $filter group by date,sku_item_code order by date".$order_p;

        
      		$con_multi->sql_query($sql,false,false);//print "$sql<br /><br />";//xx
      		if($con_multi->sql_numrows()>0)
          {
      		    foreach($con_multi->sql_fetchrowset() as $t){
      		        $lbl = sprintf("%04d%02d%02d", $t['year'],$t['month'],$t['day']);
                      
                    $category2[$t['p']]['description']=$t['cname'];
      	            $category2[$t['p']]['qty'][$lbl]+=$t['qty'];
      	            $category2[$t['p']]['amount'][$lbl]+=$t['amount'];
      	            $category2[$t['p']]['qty']['total']+=$t['qty'];
      	            $category2[$t['p']]['amount']['total']+=$t['amount'];
      	            $category2['total']['qty'][$lbl]+=$t['qty'];
      	            $category2['total']['amount'][$lbl]+=$t['amount'];
      	            $category2['total']['qty']['total']+=$t['qty'];
      	            $category2['total']['amount']['total']+=$t['amount'];

            				$sku[$t['sku_item_code']]['sku_item_code']=$t['sku_item_code'];
            				$sku[$t['sku_item_code']]['sku_item_id']=$t['sku_item_id'];
            				$sku[$t['sku_item_code']]['artno']=$t['artno'];
            				$sku[$t['sku_item_code']]['description']=$t['description'];
            
            				$table[$t['p']][$t['sku_item_code']]['qty'][$lbl]+=$t['qty'];
            				$table[$t['p']][$t['sku_item_code']]['amount'][$lbl]+=$t['amount'];
            				$table[$t['p']][$t['sku_item_code']]['qty']['total']+=$t['qty'];
            				$table[$t['p']][$t['sku_item_code']]['amount']['total']+=$t['amount'];
      			}
      		}
			$con_multi->sql_freeresult();
      }
    
  		$this->table = $table;
  		$this->category2 = $category2;
  		$this->sku = $sku;
  		$this->label = $label;
  	  
  	  $con_multi->sql_query("select level,description from category where id=".mi($category_id)) or die(mysql_error());
  		$temp = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
  		$cname = $temp['description'];
  
  	  if($cname)
  	     $c_name = $cname;
      else
  	     $c_name = "All";
  	     
  	  $report_title = "Branch: All&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Category : ".$c_name."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;From: ".$_REQUEST['date_from']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;To: ".$_REQUEST['date_to'];
    
    $smarty->assign('report_title',$report_title);
  	  
      $smarty->assign('label',$label);
  		$smarty->assign('table',$table);
  		$smarty->assign('category2',$category2);
  		$smarty->assign('sku',$sku);
  		$smarty->assign('branch_name',$branch_name);
      $smarty->assign("branches", $branches);
  		$smarty->display('report.daily_pwp_sku_sales.tpl');
		//$con_multi->close_connection();
  }
}

$report = new DailyPwpSkuSales('Daily PWP SKU Sales');

?>
