<?php
/*
1/24/2011 10:18:10 AM Alex
- change use report_server

6/24/2011 6:29:58 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:39:00 PM Andy
- Change split() to use explode()

11/15/2011 5:35:11 PM Andy
- Change "Use GRN" query.
- Add checking for config.use_grn_last_vendor_include_master, if found config then last GRN only check master vendor.

3/15/2012 2:10:21 PM Andy
- Change Report to use sku vendor from cache table.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/19/2020 10:40 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
set_time_limit(0);
ini_set('memory_limit', '256M');
//$con = new sql_db('agrofresh-hq.dyndns.org','arms_slave','arms_slave','armshq');
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");
if (BRANCH_CODE != 'HQ') js_redirect($LANG['REPORT_IS_HQ_ONLY'], "/index.php");

class BrandSales extends Report
{
	var $where;
	function run($bid, $tbl_name)
	{
		global $con_multi,$sessioninfo,$config;

        $selected_bid = $this->selected_bid;
        $filter = trim($this->filter);
        $use_grn_filter = $this->use_grn_filter;
        $one_more_level = $this->one_more_level;
        
        $table = $this->table;
        $sku = $this->sku;
        $category2 = $this->category2;
        $label = $this->label;
        $vendor_id = $this->vendor_id;
        
        if($one_more_level){
            $str_category = " left join category_cache using (category_id)
    left join category on category_cache.p$one_more_level = category.id ";
            $str_p = "p$one_more_level as p,category.description as cname,";
            $order_p = " order by p";
        }else{
        	$str_category = " left join category_cache using (category_id)
    left join category on category_cache.p2 = category.id ";
            $str_p = "p2 as p,category.description as cname,";
            $order_p = " order by p";
        }
    
        $tbl = $tbl_name['sku_items_sales_cache'];
        
        // got choose branch
		if($selected_bid!=''){
			if($this->use_grn && $vendor_id){
				// select those sku of this grn vendor between this date
				/*$vsh_filter = array();
				$vsh_filter[] = "vsh.branch_id=".mi($selected_bid)." and vsh.source='grn'";
				$vsh_filter[] = "vsh.added between ".ms($this->date_from)." and ".ms($this->date_to);
				$vsh_filter[] = "vsh.vendor_id=".mi($vendor_id);
				$vsh_filter = join(' and ', $vsh_filter);
				
				$sql = "select distinct(sku_item_id) as sid
				from vendor_sku_history vsh 
				left join sku_items on sku_items.id=vsh.sku_item_id
				left join sku on sku_items.sku_id = sku.id
				left join category_cache cc on cc.category_id=sku.category_id
				where $use_grn_filter and $vsh_filter";
				$con_multi->sql_query($sql) or die(mysql_error());
				$grn_sid_list = array();
				while($r = $con_multi->sql_fetchassoc()){
					$grn_sid_list[] = mi($r['sid']);
				}
				$con_multi->sql_freeresult();
				
				$ven_sql=",(select vsh.vendor_id from vendor_sku_history vsh where vsh.sku_item_id=sku_items.id and vsh.branch_id=$selected_bid and vsh.added <= ".ms($this->date_from)." order by vsh.branch_id, vsh.sku_item_id, vsh.added desc limit 1) as last_grn_vendor_id,sku.vendor_id as master_vendor_id";*/
				
				//$filter = ($filter ? $filter.' and ': '')." vsh.vendor_id=$vendor_id";
				
				$use_grn_xtra_join = "join vendor_sku_history_b".$bid." vsh on vsh.sku_item_id=pos.sku_item_id and pos.date between vsh.from_date and vsh.to_date and vsh.vendor_id=$vendor_id";
			}
            $sql = "select year,month,sku_item_code,sku_id as sku_item_id,sum(pos.qty) as qty,sum(pos.amount) as amount,sku_items.description,sku.category_id,".$str_p."sku_type $ven_sql
from $tbl pos
left join sku_items on sku_item_id = sku_items.id
left join sku on sku_id = sku.id".$str_category."
$use_grn_xtra_join
where $filter group by sku_item_id,year,month".$order_p;
		}else{
			// all branch
            $sql = "select sku_item_code,sku_id as sku_item_id,sum(pos.qty) as qty,sum(pos.amount) as amount,sku_items.description,sku.category_id,".$str_p."sku_type
from $tbl pos
left join sku_items on sku_item_id = sku_items.id
left join sku on sku_id = sku.id".$str_category."
where $filter group by sku_item_id".$order_p;

            $lbl = $bid;
		}
		//die($sql);
		$con_multi->sql_query($sql) or die(sql_error());//print "$sql<br /><br />";//xx
        $category2['root']['description'] = $description;
		if($con_multi->sql_numrows()>0){
		    foreach($con_multi->sql_fetchrowset() as $t){
		        if($selected_bid!=""){
		        	/*if($this->use_grn && $vendor_id){
		        		if(($t['last_grn_vendor_id'] != $vendor_id) && !in_array($sid, $grn_sid_list)){
							if(!$config['use_grn_last_vendor_include_master']){
								continue;
							}elseif($t['master_vendor_id'] != $vendor_id){
								continue;
							}
						}
		        	}*/
	                $lbl = sprintf("%04d%02d", $t['year'], $t['month']);
				    $label[$lbl] = $this->months[$t['month']] ." " . $t['year'];
				}
				    
				    
	            $category2[$t['p']]['description']=$t['cname'];
	            $category2[$t['p']]['qty'][$t['sku_type']][$lbl]+=$t['qty'];
	            $category2[$t['p']]['amount'][$t['sku_type']][$lbl]+=$t['amount'];
	            $category2[$t['p']]['qty']['all_type'][$lbl]+=$t['qty'];
	            $category2[$t['p']]['amount']['all_type'][$lbl]+=$t['amount'];
	            $category2[$t['p']]['qty'][$t['sku_type']]['total']+=$t['qty'];
	            $category2[$t['p']]['amount'][$t['sku_type']]['total']+=$t['amount'];
	            $category2[$t['p']]['qty']['all_type']['total']+=$t['qty'];
	            $category2[$t['p']]['amount']['all_type']['total']+=$t['amount'];

	    		if($category2[$t['p']]['qty'][$t['sku_type']]['total']!=0){
	                $category2[$t['p']]['avg_sell'][$t['sku_type']]['total']=$category2[$t['p']]['amount'][$t['sku_type']]['total']/$category2[$t['p']]['qty'][$t['sku_type']]['total'];
	    		}

	    		if($category2[$t['p']]['qty']['all_type']['total']!=0){
	                $category2[$t['p']]['avg_sell']['all_type']['total']=$category2[$t['p']]['amount']['all_type']['total']/$category2[$t['p']]['qty']['all_type']['total'];
	    		}
	    		
	            $category2['total']['qty'][$t['sku_type']][$lbl]+=$t['qty'];
	            $category2['total']['amount'][$t['sku_type']][$lbl]+=$t['amount'];
	            $category2['total']['qty']['all_type'][$lbl]+=$t['qty'];
	            $category2['total']['amount']['all_type'][$lbl]+=$t['amount'];

	            $category2['total']['qty'][$t['sku_type']]['total']+=$t['qty'];
	            $category2['total']['amount'][$t['sku_type']]['total']+=$t['amount'];
	            $category2['total']['qty']['all_type']['total']+=$t['qty'];
	            $category2['total']['amount']['all_type']['total']+=$t['amount'];

		        if($category2['total']['qty'][$t['sku_type']]['total']!=0){
	                $category2['total']['avg_sell'][$t['sku_type']]['total']=$category2['total']['amount'][$t['sku_type']]['total']/$category2['total']['qty'][$t['sku_type']]['total'];
				}

	    		if($category2['total']['qty']['all_type']['total']!=0){
	                $category2['total']['avg_sell']['all_type']['total']=$category2['total']['amount']['all_type']['total']/$category2['total']['qty']['all_type']['total'];
	    		}

				$sku[$t['sku_item_code']]['sku_item_code']=$t['sku_item_code'];
				$sku[$t['sku_item_code']]['sku_item_id']=$t['sku_item_id'];
				$sku[$t['sku_item_code']]['artno']=$t['artno'];
				$sku[$t['sku_item_code']]['description']=$t['description'];

				$table[$t['p']][$t['sku_item_code']]['qty'][$t['sku_type']][$lbl]+=$t['qty'];
				$table[$t['p']][$t['sku_item_code']]['amount'][$t['sku_type']][$lbl]+=$t['amount'];
				$table[$t['p']][$t['sku_item_code']]['qty']['all_type'][$lbl]+=$t['qty'];
				$table[$t['p']][$t['sku_item_code']]['amount']['all_type'][$lbl]+=$t['amount'];

				$table[$t['p']][$t['sku_item_code']]['qty'][$t['sku_type']]['total']+=$t['qty'];
				$table[$t['p']][$t['sku_item_code']]['amount'][$t['sku_type']]['total']+=$t['amount'];
				$table[$t['p']][$t['sku_item_code']]['qty']['all_type']['total']+=$t['qty'];
				$table[$t['p']][$t['sku_item_code']]['amount']['all_type']['total']+=$t['amount'];

				if($table[$t['p']][$t['sku_item_code']]['qty'][$t['sku_type']]['total']!=0){
                    $table[$t['p']][$t['sku_item_code']]['avg_sell'][$t['sku_type']]['total']=$table[$t['p']][$t['sku_item_code']]['amount'][$t['sku_type']]['total']/$table[$t['p']][$t['sku_item_code']]['qty'][$t['sku_type']]['total'];
				}

				if($table[$t['p']][$t['sku_item_code']]['qty']['all_type']['total']!=0){
                    $table[$t['p']][$t['sku_item_code']]['avg_sell']['all_type']['total']=$table[$t['p']][$t['sku_item_code']]['amount']['all_type']['total']/$table[$t['p']][$t['sku_item_code']]['qty']['all_type']['total'];
				}
			}
		}
		$con_multi->sql_freeresult();
		$this->table = $table;
        $this->sku = $sku;
        $this->category2 = $category2;
        $this->label = $label;
	}
	
	function generate_report()
	{
		global $con, $smarty, $con_multi;

        $bid  = get_request_branch(true);
        $branch_group = $this->branch_group;
		
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
            list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
            if($branch_group['items'][$bg_id]){
				foreach($branch_group['items'][$bg_id] as $tmp_bid=>$b){
					$this->label[$tmp_bid] = $b['code'];
				    $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$tmp_bid;
					$this->run($tmp_bid,$tbl_name);
				}
			}
            /*$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_bg".mi($bg_id);
			$this->run($bg_id+10000,$tbl_name);
			$branch_name = $branch_group['header'][$bg_id]['code'];*/
        }else{
            if($bid==0){
				$branch_name = "All";
				$b0 = $con_multi->sql_query("select id,code from branch where active=1 order by sequence,code");
				while($b = $con_multi->sql_fetchrow($b0))
				{
				    $this->label[$b['id']] = $b['code'];
				    $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$b['id'];
					$this->run($b['id'],$tbl_name);
				}
				$con_multi->sql_freeresult($b0);
				/*if($branch_group['header']){
					foreach($branch_group['header'] as $bg_id=>$bg){
					    $this->label[$bg_id+10000] = $bg['code'];
                        $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_bg".mi($bg_id);
						$this->run($bg_id+10000,$tbl_name);
					}
				}*/
			}else{
				$branch_name =  get_branch_code($bid);
				$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
				$this->run($bid,$tbl_name);
			}
		}
		
		$table = $this->table;
        $sku = $this->sku;
        $category2 = $this->category2;
        $label = $this->label;
        
		$vendor_id = $this->vendor_id;
		if($vendor_id!='all'){
            $con_multi->sql_query("select description from vendor where id=".mi($vendor_id))or die(mysql_error());
            $vn = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
            $vendor_name = $vn['description'];
		}else{
            $vendor_name = 'All';
		}
		
		if($_REQUEST['sku_type_code']=='all'){
			$sku_type_choose = 'All';
		}else{
            $sku_type_choose = $_REQUEST['sku_type_code'];
		}
		
		if($this->selected_bid!=""){
			@ksort($label);
		}
		
		$report_title = "From: ".$_REQUEST['date_from']." to ".$_REQUEST['date_to']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Vendor: ".$vendor_name."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SKU Type: ".$sku_type_choose."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Branch: ".$branch_name;
		
		$smarty->assign('report_title',$report_title);
		$smarty->assign('vendor_name',$vendor_name);
		$smarty->assign('sku_type_choose',$sku_type_choose);
		$smarty->assign('branch_name',$branch_name);
		$smarty->assign('label',$label);
		$smarty->assign('table',$table);
		$smarty->assign('category2',$category2);
		$smarty->assign('sku',$sku);
	}
	
	function process_form()
	{
	    global $con,$smarty,$sessioninfo,$con_multi;
		// do my own form process
		
		// call parent
		parent::process_form();
		
		$category_id = $_REQUEST['category_id'];
	    $sku_type_code = $_REQUEST['sku_type_code'];
	    $vendor_id = $_REQUEST['vendor_id'];
	    $this->use_grn = $GRN = $_REQUEST['GRN'];
    	$use_grn_filter = array();
    	
	    if($category_id){
	        $con_multi->sql_query("select level,description from category where id=".mi($category_id)) or die(mysql_error());
    		$temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
    		$level = $temp['level'];
    		$description = $temp['description'];

    		// check one more level for grouping
    		$con_multi->sql_query("select max(level) from category") or die(mysql_error());
    		$max_level = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
    		if($level<$max_level)	$one_more_level = $level+1;
    		else    $one_more_level = $level;

    		$filter[] = "p$level=".mi($category_id);
    		$use_grn_filter[] = "p$level=".mi($category_id);
	    }
      
		$filter[] = "pos.date between ".ms($this->date_from)." and ".ms($this->date_to);
		if($sku_type_code!='all')
			$filter[] = "sku_type=".ms($sku_type_code);
			$use_grn_filter[] = "sku_type=".ms($sku_type_code);
   
	    if($_REQUEST['branch_id']!=""){
	        $brn_id = " and branch_id = ".ms($_REQUEST['branch_id']);
	    }
   
		if($vendor_id!='all'){
			$this->vendor_id = mi($_REQUEST['vendor_id']);
			if($GRN){
				// find items that we receive by

				/*$con->sql_query("select sku_item_id, added from vendor_sku_history where added < ".ms($this->date_to).$brn_id." and vendor_id=".mi($vendor_id)." order by sku_item_id, added desc");
				//print "select sku_item_id, added from vendor_sku_history where added < ".ms($this->date_to).$brn_id." and vendor_id=".mi($vendor_id)." order by sku_item_id, added desc";
				if ($con->sql_numrows()<=0){
					print $LANG['REPORT_NO_ITEMS_FOR_THIS_VENDOR'];
					return false;
				}
				while($r=$con->sql_fetchrow()){
					if ($items[$r[0]]) continue;
					$items[$r[0]] = 1;
					//print "<li> $r[0] $r[1]";
				}
				$filter[] = "sku_items.id in (".join(",", array_keys($items)).")";*/
			}
			else{
				$filter[] = "sku.vendor_id = ".$this->vendor_id;
			}
			
		}else{
			if($sessioninfo['vendor_ids']){
				$filter[] = "sku.vendor_id in ($sessioninfo[vendor_ids])";
			}
		}
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';
    
		$filter = join(" and ",$filter);
		
		$use_grn_filter = join(" and ",$use_grn_filter);
		$selected_bid = mi($_REQUEST['branch_id']);
		
		$this->selected_bid = $selected_bid;

		$this->filter = $filter;
		$this->use_grn_filter = $use_grn_filter;
		$this->one_more_level = $one_more_level;
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
$report = new BrandSales('SKU Items Sales of Vendor Comparison by Branch');
//$con_multi->close_connection();
?>
