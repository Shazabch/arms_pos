<?php
/*
1/17/2011 5:20:32 PM Alex
- change use report_server

6/24/2011 5:59:26 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:26:01 PM Andy
- Change split() to use explode()

11/16/2011 5:03:04 PM Andy
- Change "Use GRN" query.
- Add checking for config.use_grn_last_vendor_include_master, if found config then last GRN only check master vendor.

3/12/2012 4:44:13 PM Andy
- Change Report to use sku vendor from cache table.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

7/15/2015 2:32 PM Justin
- Bug fixed on system getting wrong trade discount code.

18/2/2020 5:32 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
//$con = new sql_db('agrofresh-hq.dyndns.org','arms_slave','arms_slave','armshq');
//$con = new sql_db('jwt-uni.dyndns.org','arms','4383659','armshq');
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");

class ConsignmentPerformanceReport extends Report
{
	function run_report($bid,$tbl_name)
	{
	    global $con, $sessioninfo, $con_multi,$config;
	
		$filter = $this->filter;
		$GRN = $this->GRN;
		$category_id = $this->category_id;
		$selected_year = $this->selected_year;
		$selected_month = $this->selected_month;
		$selected_date = $this->selected_date;
		$selected_to_date = $this->selected_to_date;
		$one_more_level = $this->one_more_level;
		$brand_id = $this->brand_id;
		$vendor_id = $this->vendor_id;
		
		//$table = $this->table;
		//$sku = $this->sku;
		//$category2 = $this->category2;
		//$label = $this->label;
  
  		
		
      	if($GRN && $vendor_id)
  		{
  			// select those sku of this grn vendor between this date
			/*$vsh_filter = array();
			$vsh_filter[] = "vsh.branch_id=".mi($bid)." and vsh.source='grn'";
			$vsh_filter[] = "vsh.added between ".ms($selected_date)." and ".ms($selected_to_date);
			$vsh_filter[] = "vsh.vendor_id=".mi($vendor_id);
			if($filter)	$vsh_filter[] = join(' and ', $filter);
			$vsh_filter = join(' and ', $vsh_filter);
			
			$sql = "select distinct(sku_item_id) as sid
			from vendor_sku_history vsh 
			left join sku_items on sku_items.id=vsh.sku_item_id
			left join sku on sku_items.sku_id = sku.id
			left join category_cache cc on cc.category_id=sku.category_id
			where $vsh_filter";
			$con_multi->sql_query($sql) or die(mysql_error());
			$grn_sid_list = array();
			while($r = $con_multi->sql_fetchassoc()){
				$grn_sid_list[] = mi($r['sid']);
			}
			$con_multi->sql_freeresult();
				
			$ven_sql=",(select vsh.vendor_id from vendor_sku_history vsh where vsh.sku_item_id=sku_items.id and vsh.branch_id=$bid and vsh.added <= ".ms($selected_date)." order by vsh.added desc limit 1) as last_grn_vendor_id,sku.vendor_id as master_vendor_id";*/
			
			$use_grn_xtra_join = "join vendor_sku_history_b".$bid." vsh on vsh.sku_item_id=pos.sku_item_id and pos.date between vsh.from_date and vsh.to_date and vsh.vendor_id=".intval($vendor_id);
			//$filter[] = "vsh.vendor_id=".intval($vendor_id);
  		}
  		else
  		{
  			 $filter[] = "sku.vendor_id = ".intval($vendor_id);
  		}
		  
		$filter[] = "month=".mi($selected_month);
		$filter[] = "year=".mi($selected_year);
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';
		$filter[] = "sku.sku_type='CONSIGN'";
		$filter = join(" and ", $filter);
		
  		$tbl = $tbl_name['sku_items_sales_cache'];
      if($category_id)
  		{
          $sql = "select date,day(date) as day,sku_item_code,pos.sku_item_id,sku_items.description,sum(qty) as qty,sum(amount) as amount,p$one_more_level as p,category.description as cname,artno $ven_sql
  from $tbl pos
  left join sku_items on sku_item_id = sku_items.id
  left join sku on sku_id = sku.id
  left join category_cache using (category_id)
  left join category on category_cache.p$one_more_level = category.id
  $use_grn_xtra_join
  where $filter group by day,sku_item_code order by date";
      }else
      {
          $sql = "select date,day(date) as day,sku_item_code,pos.sku_item_id,sku_items.description,sum(qty) as qty,sum(amount) as amount,artno $ven_sql
  from $tbl pos
  left join sku_items on sku_item_id = sku_items.id
  left join sku on sku_id = sku.id 
  $use_grn_xtra_join
  where $filter group by day,sku_item_code order by date";
          
      }
//print $sql."<br/>";
      
		$q1 = $con_multi->sql_query($sql);//print "$sql<br /><br />";//xx
		if($con_multi->sql_numrows($q1)>0)
    	{
		    while($t = $con_multi->sql_fetchassoc($q1)){
		    	/*if($GRN && $vendor_id){
	        		if(($t['last_grn_vendor_id'] != $vendor_id) && !in_array($t['sku_item_id'], $grn_sid_list)){
						if(!$config['use_grn_last_vendor_include_master']){
							continue;
						}elseif($t['master_vendor_id'] != $vendor_id){
							continue;
						}
					}
	        	}*/
		        	
                $discount_code = "";
                $sku_item_id = $t['sku_item_id'];
                $sql = "select * from sku_items_price_history where added<".ms($selected_date)." and sku_item_id=".mi($sku_item_id)." and branch_id = ".mi($bid)." order by added desc limit 1";
                $temp_sql = $con_multi->sql_query($sql) or die(sql_error());
                if($con_multi->sql_numrows($temp_sql)>0){
					$temp = $con_multi->sql_fetchrow($temp_sql);
					$discount_code = $temp['trade_discount_code'];
				}else{
                    $temp_sql2 = $con_multi->sql_query("select default_trade_discount_code from sku where id=".mi($sku_item_id)) or die(sql_error());
                    $temp = $con_multi->sql_fetchrow($temp_sql2);
					$con_multi->sql_freeresult($temp_sql2);
                    $discount_code = $temp['default_trade_discount_code'];
				}
				$con_multi->sql_freeresult($temp_sql);
                
                if($discount_code==''||$discount_code==null){
                    $discount_code = "Others";
				}
                
                if($discount_code!=''&&$discount_code!=null){
                  	$this->category2[$discount_code]['description']=$discount_code;
	                $this->category2[$discount_code]['qty'][$t['day']]+=$t['qty'];
	                $this->category2[$discount_code]['amount'][$t['day']]+=$t['amount'];
	                $this->category2[$discount_code]['qty']['total']+=$t['qty'];
	                $this->category2[$discount_code]['amount']['total']+=$t['amount'];
	                $this->category2['total']['qty'][$t['day']]+=$t['qty'];
	                $this->category2['total']['amount'][$t['day']]+=$t['amount'];
	                $this->category2['total']['qty']['total']+=$t['qty'];
	                $this->category2['total']['amount']['total']+=$t['amount'];

					$this->sku[$t['sku_item_code']]['sku_item_code']=$t['sku_item_code'];
					$this->sku[$t['sku_item_code']]['sku_item_id']=$t['sku_item_id'];
					$this->sku[$t['sku_item_code']]['artno']=$t['artno'];
					$this->sku[$t['sku_item_code']]['description']=$t['description'];

					$this->table[$discount_code][$t['sku_item_code']]['qty'][$t['day']]+=$t['qty'];
					$this->table[$discount_code][$t['sku_item_code']]['amount'][$t['day']]+=$t['amount'];
					$this->table[$discount_code][$t['sku_item_code']]['qty']['total']+=$t['qty'];
					$this->table[$discount_code][$t['sku_item_code']]['amount']['total']+=$t['amount'];
				}
			}
		}
		$con_multi->sql_freeresult($q1);
		
		//$this->table = $table;
        //$this->sku = $sku;
        //$this->category2 = $category2;
        //$this->label = $label;
	}
	
	function generate_report()
	{
		global $con, $smarty, $con_multi;
		$branch_group = $this->branch_group;

		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			//$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_bg".$bg_id;
			//$this->run_report($bg_id+10000,$tbl_name);
            $branch_name = $branch_group['header'][$bg_id]['code'];
            
            if($branch_group['items'][$bg_id]){
				foreach($branch_group['items'][$bg_id] as $tmp_bid=>$b){
					$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$tmp_bid;
	            	$this->run_report($tmp_bid,$tbl_name);
				}
			}
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
	            $this->run_report($bid,$tbl_name);
	            $branch_name = BRANCH_CODE;
			}else{
				if($bid==0){
		            //die("No Branch Selected");
		            $con_multi->sql_query("select id from branch where active=1 order by sequence,code");
	          		while($r = $con_multi->sql_fetchrow()){
	          			$branches[$r['id']] = $r;
	          		}
	          		$con_multi->sql_freeresult();
					
	          		foreach($branches as $bid => $v)
	          		{
						$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
						$this->run_report($bid,$tbl_name);
						$branch_name = get_branch_code($bid);
						$this->more_branch = "1";
	              	}
          		
				}else{
					$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
					$this->run_report($bid,$tbl_name);
					$branch_name = get_branch_code($bid);
				}
			}
		}
			
    $table = $this->table;
    $sku = $this->sku;
    $category2 = $this->category2;
    $label = $this->label;
        
		@ksort($table);
		
		$vendor_id = $this->vendor_id;
		if($vendor_id!=0){
            $con_multi->sql_query("select description from vendor where id=".mi($vendor_id))or die(mysql_error());
            $vn = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
            $vendor_name = $vn['description'];
		}
	  
	  $months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
	  
	  if($branch_name)
        $bran = $branch_name;
    else
        $bran = "All";
	
	  $report_title = "Branch: ".$bran."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Vendor: ".$vendor_name."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Year: ".$_REQUEST['year']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Month: ".$months[$_REQUEST['month']];
    
    $smarty->assign('report_title',$report_title);
	  
		$smarty->assign('label',$label);
		$smarty->assign('table',$table);
		$smarty->assign('category2',$category2);
		$smarty->assign('sku',$sku);
		
		if($this->more_branch == "1")
		{
        $smarty->assign('branch_name','All');
    }else
    { 
        $smarty->assign('branch_name',$branch_name);
    }
    
		$smarty->assign('vendor_name',$vendor_name);
	}
	
	function process_form()
	{
	    global $con,$smarty,$sessioninfo,$con_multi;
		// do my own form process
		
		// call parent
		parent::process_form();
		
		$category_id = $_REQUEST['category_id'];
	    $selected_month = $_REQUEST['month'];
	    $selected_year = $_REQUEST['year'];
	    $selected_date = $selected_year."-".$selected_month."-1";
      	$selected_to_date = $selected_year."-".$selected_month."-".days_of_month($selected_month, $selected_year);
      	
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
			if($level<$max_level)	$one_more_level = $level+1;
			else    $one_more_level = $level;
			
			$filter[] = "p$level=".mi($category_id);
		}
	    
		//$filter[] = "month=".mi($selected_month);
		//$filter[] = "year=".mi($selected_year);

		if($_REQUEST['report_type']=='brand'){
            $brand_id = $_REQUEST['brand_id'];
            $filter[] = "brand_id in (".join(',',process_brand_id($brand_id)).")";
		}
        
        $vendor_id = $_REQUEST['vendor_id'];
        $GRN = $_REQUEST['GRN'];
        
        //$filter = join(" and ",$filter);
        
        $this->filter = $filter;
        $this->category_id = $category_id;
        $this->selected_year = $selected_year;
        $this->selected_month = $selected_month;
        $this->selected_date = $selected_date;
        $this->selected_to_date = $selected_to_date;
        $this->one_more_level = $one_more_level;
        $this->brand_id = $brand_id;
        $this->GRN = $GRN;
        $this->vendor_id = $vendor_id;
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

/*$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}*/

$report = new ConsignmentPerformanceReport('Consignment Performance Report');
//$con_multi->close_connection();

?>
