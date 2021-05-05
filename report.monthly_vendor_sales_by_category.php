<?php
/*
1/25/2011 1:59:10 PM Alex
- change use report_server
- add department privilege filter
- fix date bugs

3/10/2011 3:05:09 PM Alex
- fix bugs on missing month display while view report as subranch

3/11/2011 10:35:01 AM Justin
- Fixed the use GRN cannot function.

6/24/2011 6:21:04 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:44:07 PM Andy
- Change split() to use explode()

10/27/2011 5:33:43 PM Andy
- Fix "Use GRN" script.
- Change to only load active branch data when choose all branches.

11/14/2011 11:37:29 AM Andy
- Add sorting by ARMS Code, MCode, Artno, Description and Old Code.

11/15/2011 1:57:40 PM Andy
- Add checking for config.use_grn_last_vendor_include_master, if found config then last GRN only check master vendor.

11/23/2011 5:45:31 PM Andy
- Fix sorting does not work correctly when choose "All branches".

2:15 PM 1/10/2012 Justin
- Fixed the date is not sort by ascending order.

1/30/2012 3:51:12 PM Andy
- Fix monthly label sorting bugs.

10/24/2012 1:58:00 PM Fithri
- can group by SKU - affect total qty calculation (based on uom fraction)
- remove (hide) sorting by description

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/19/2020 9:45 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
//$con = new sql_db('agrofresh-hq.dyndns.org','arms_slave','arms_slave','armshq');
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");

class MonthlyVendorSalesReportByCategory extends Report
{
	var $where;
	function run_report($bid_list)
	{
	    global $con,$sessioninfo,$con_multi,$config;
	    $filter = $this->filter;
		$isAllBranches = $this->isAllBranches;
		$group_by2 = $this->group_by2;
		$vendor_id = $this->vendor_id;
		$GRN = $this->grn;
		
		/*if($this->sort_by){
			$order_by = "order by $this->sort_by $this->sort_order";
		}*/
		
		//$table = $this->table;
		//$label = $this->label;
		//$category = $this->category;
		$v_filter = array();

		$v_filter = '';
		if(!$GRN){
			if($vendor_id!='all'){
				$v_filter = " and vendor_id = ".intval($_REQUEST['vendor_id']);
			}else{
	            if($sessioninfo['vendor_ids']){
					$v_filter = " and vendor_id in ($sessioninfo[vendor_ids])";
				}
			}
		}
		
		/*if($vendor_id!='all'){
	    	if($GRN&&$bid<10000){
				// find items that we receive by
				$con_multi->sql_query("select sku_item_id, added from vendor_sku_history where branch_id=".mi($bid)." and vendor_id=".mi($vendor_id)." and added < ".ms($this->date_to)." order by sku_item_id, added desc");

				if ($con_multi->sql_numrows()<=0){
					return false;
				}

				while($r=$con_multi->sql_fetchrow()){
					if ($items[$r[0]]) continue;
					$items[$r[0]] = 1;
					//print "<li> $r[0] $r[1]";
				}
				$v_filter[] = "and sku_items.id in (".join(",", array_keys($items)).")";
			}else{
				$v_filter[] = "and sku.vendor_id = ".intval($_REQUEST['vendor_id']);
			}
		}else{
		
            if($sessioninfo['vendor_ids']){
				$v_filter[] = "and sku.vendor_id in ($sessioninfo[vendor_ids])";
			}
		}*/


		if(!$bid_list || !is_array($bid_list))	return false;
		
		$checked_branch_sku_vendor = array();
		
		foreach($bid_list as $bid){
			$tbl = 'sku_items_sales_cache_b'.$bid;
			$sql="select year,month,sku_item_code,sku_item_id,si.description,sum(pos.qty) as quantity,sum(pos.amount) as amount,p3,category.description as cname,sku.vendor_id as master_vendor_id, si.artno,si.mcode,si.link_code,si.is_parent,si.sku_id,uom.fraction 
			from $tbl pos
left join sku_items si on sku_item_id = si.id
left join sku on sku_id = sku.id
left join category_cache using (category_id)
left join category on category_cache.p3 = category.id
left join uom on si.packing_uom_id=uom.id 
where $filter $v_filter group by sku_item_id,$group_by2 p3 order by year asc, month asc";

			if($isAllBranches)  $lbl = $bid;
			$q1 = $con_multi->sql_query($sql);//print "$sql<br /><br />";//xx
			while($r = $con_multi->sql_fetchassoc($q1)){
				if(!$isAllBranches){
					$lbl = sprintf("%04d%02d", $r['year'], $r['month']);
			    	$this->label[$lbl] = $this->months[$r['month']]." " .$r['year'];
				}
				
				if($GRN){	// got use grn
					// check last vendor
					if(!isset($checked_branch_sku_vendor[$bid][$r['sku_item_id']])){
						$q2 = $con_multi->sql_query("select vsh.vendor_id
											   from vendor_sku_history vsh
											   where vsh.added <= ".ms($this->date_from)."
											   and vsh.branch_id=$bid
											   and vsh.sku_item_id = ".mi($r['sku_item_id'])."
											   and source='GRN' 
											   order by vsh.added desc
											   limit 1");
						$last_vendor = $con_multi->sql_fetchassoc($q2);
						$con_multi->sql_freeresult($q2);
						
						
						if($last_vendor){	// got last vendor
							if($last_vendor['vendor_id'] == $vendor_id){
								$checked_branch_sku_vendor[$bid][$r['sku_item_id']] = true;
							}
						}else{
							if($config['use_grn_last_vendor_include_master'] && $r['master_vendor_id']==$vendor_id){	// use master vendor
								$checked_branch_sku_vendor[$bid][$r['sku_item_id']] = true;
							}
						}
						
						// check whether got receive GRN between this date range
						if(!$checked_branch_sku_vendor[$bid][$r['sku_item_id']]){
							$q3 = $con_multi->sql_query("select vsh.vendor_id
									   from vendor_sku_history vsh
									   where vsh.added between ".ms($this->date_from)." and ".ms($this->date_to)."
									   and vsh.branch_id=$bid
									   and vsh.sku_item_id = ".mi($r['sku_item_id'])."
									   and source='GRN' and vendor_id=".mi($vendor_id)."
									   limit 1");
							if($con_multi->sql_numrows($q3)>0){
								$checked_branch_sku_vendor[$bid][$r['sku_item_id']] = true;
							}
							$con_multi->sql_freeresult($q3);
						}
					}
					
					if(!$checked_branch_sku_vendor[$bid][$r['sku_item_id']])	continue;
				}
                
				if ($_REQUEST['group_by_sku']) $r['quantity'] = $r['quantity']*$r['fraction']; //multiply with fraction
				
				$this->category[$r['p3']]['name']=$r['cname'];
                $this->category[$r['p3']]['quantity'][$lbl]+=$r['quantity'];
                $this->category[$r['p3']]['quantity']['total']+=$r['quantity'];
                $this->category[$r['p3']]['amount'][$lbl]+=$r['amount'];
                $this->category[$r['p3']]['amount']['total']+=$r['amount'];
                
                $this->category['total']['quantity'][$lbl]+=$r['quantity'];
                $this->category['total']['quantity']['total']+=$r['quantity'];
                $this->category['total']['amount'][$lbl]+=$r['amount'];
                $this->category['total']['amount']['total']+=$r['amount'];
				
				$this->table[$r['p3']][$r['sku_item_id']]['quantity'][$lbl]+=$r['quantity'];
				$this->table[$r['p3']][$r['sku_item_id']]['amount'][$lbl]+=$r['amount'];
				if(!isset($this->table[$r['p3']][$r['sku_item_id']]['sku_item_id'])){
					$this->table[$r['p3']][$r['sku_item_id']]['sku_id'] = $r['sku_id'];
					$this->table[$r['p3']][$r['sku_item_id']]['sku_item_id'] = $r['sku_item_id'];
				    $this->table[$r['p3']][$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];
				    $this->table[$r['p3']][$r['sku_item_id']]['artno'] = $r['artno'];
				    $this->table[$r['p3']][$r['sku_item_id']]['mcode'] = $r['mcode'];
				    $this->table[$r['p3']][$r['sku_item_id']]['link_code'] = $r['link_code'];
				    $this->table[$r['p3']][$r['sku_item_id']]['description'] = $r['description'];
				    $this->table[$r['p3']][$r['sku_item_id']]['is_parent'] = $r['is_parent'];
				    
				}
				$this->table[$r['p3']][$r['sku_item_id']]['quantity']['total'] += $r['quantity'];
			    $this->table[$r['p3']][$r['sku_item_id']]['amount']['total'] += $r['amount'];
			}
			
			$con_multi->sql_freeresult($q1);
		}
		
		// find parent to those parent-less child
		//if (false) {
		if (isset($_REQUEST['group_by_sku']) && $this->table) {
			$parents = $childs = array();
			foreach($this->table as $grkey => $grdata) {
				foreach ($grdata as $ikey => $idata) {
					if ($idata['is_parent']) {
						if (!in_array($idata['sku_id'],$parents)) $parents[] = $idata['sku_id'];
					}
					else {
						if (!in_array($idata['sku_id'],$childs)) $childs[] = $idata['sku_id'];
					}
				}
			}
			foreach ($childs as $childkey => $child) if (in_array($child,$parents)) unset($childs[$childkey]);
			
			if($childs){
				$sql4="select sku_item_code,si.id as sku_item_id,si.description,0 as quantity,0 as amount,p3,category.description as cname,sku.vendor_id as master_vendor_id, si.artno,si.mcode,si.link_code,si.is_parent,si.sku_id 
				from sku_items si
	left join sku on sku_id = sku.id
	left join category_cache using (category_id)
	left join category on category_cache.p3 = category.id
	where is_parent=1 and sku_id in (".join(',',$childs).")";//print "$sql4<br /><br />";
				$q4 = $con_multi->sql_query($sql4);
				while($r4 = $con_multi->sql_fetchassoc($q4)){
					$this->table[$r4['p3']][$r4['sku_item_id']]['sku_id'] = $r4['sku_id'];
					$this->table[$r4['p3']][$r4['sku_item_id']]['sku_item_id'] = $r4['sku_item_id'];
					$this->table[$r4['p3']][$r4['sku_item_id']]['sku_item_code'] = $r4['sku_item_code'];
					$this->table[$r4['p3']][$r4['sku_item_id']]['artno'] = $r4['artno'];
					$this->table[$r4['p3']][$r4['sku_item_id']]['mcode'] = $r4['mcode'];
					$this->table[$r4['p3']][$r4['sku_item_id']]['link_code'] = $r4['link_code'];
					$this->table[$r4['p3']][$r4['sku_item_id']]['description'] = $r4['description'];
					$this->table[$r4['p3']][$r4['sku_item_id']]['is_parent'] = $r4['is_parent'];
					$this->table[$r4['p3']][$r4['sku_item_id']]['quantity']['total'] = 0;
					$this->table[$r4['p3']][$r4['sku_item_id']]['amount']['total'] = 0;
				}
				$con_multi->sql_freeresult($q4);
			}
			
			foreach($this->table as $grkey => $grdata) {
				foreach ($grdata as $ikey => $idata) {
					if ($idata['is_parent']) {
						$all_parents[$grkey][$ikey] = $idata;
					}
					else {
						$all_childs[$grkey][$ikey] = $idata;
					}
				}
			}
			
			foreach ($all_parents as $apk => $apv) uasort($all_parents[$apk], array($this,"sort_table"));
			
			foreach ($all_childs as $ack => $acv) uasort($all_childs[$ack], array($this,"sort_table"));
			
			$this->table = array();
			foreach ($all_parents as $apk => $apv) {
				foreach ($apv as $apvk => $apvv) {
					$this->table[$apk][$apvk] = $apvv;
					foreach ($all_childs as $ack => $acv) {
						foreach ($acv as $acvk => $acvv) {
							if ($acvv['sku_id'] == $apvv['sku_id']) {
								$this->table[$ack][$acvk] = $acvv;
							}
						}
					}
				}
			}
			
			/*
			echo '<pre>';
			print_r($all_parents);
			print_r($all_childs);
			echo '</pre>';
			*/
			
		}
		
		/*$tbl = $tbl_name['sku_items_sales_cache'];

		$sql="select year,month,sku_item_code,sku_item_id,sku_items.description,sum(pos.qty) as quantity,sum(pos.amount) as amount,p3,category.description as cname from
$tbl pos
left join sku_items on sku_item_id = sku_items.id
left join sku on sku_id = sku.id
left join category_cache using (category_id)
left join category on category_cache.p3 = category.id
where $filter $v_filter group by sku_item_id,$group_by2 p3
order by year,month";

		if($isAllBranches)  $lbl = $bid;

        $con_multi->sql_query($sql) or die(sql_error());
		if($con_multi->sql_numrows()>0){
            foreach($con_multi->sql_fetchrowset() as $r){
                if(!$isAllBranches){
                    $lbl = sprintf("%04d%02d", $r['year'], $r['month']);
			    	$this->label[$lbl] = $this->months[$r['month']] ." " . $r['year'];
				}
	            
                $this->category[$r['p3']]['name']=$r['cname'];
                $this->category[$r['p3']]['quantity'][$lbl]+=$r['quantity'];
                $this->category[$r['p3']]['quantity']['total']+=$r['quantity'];
                $this->category[$r['p3']]['amount'][$lbl]+=$r['amount'];
                $this->category[$r['p3']]['amount']['total']+=$r['amount'];
                
                $this->category['total']['quantity'][$lbl]+=$r['quantity'];
                $this->category['total']['quantity']['total']+=$r['quantity'];
                $this->category['total']['amount'][$lbl]+=$r['amount'];
                $this->category['total']['amount']['total']+=$r['amount'];
                
				$this->table[$r['p3']][$r['sku_item_id']]['quantity'][$lbl]+=$r['quantity'];
				$this->table[$r['p3']][$r['sku_item_id']]['amount'][$lbl]+=$r['amount'];
				$this->table[$r['p3']][$r['sku_item_id']]['sku_item_id'] = $r['sku_item_id'];
			    $this->table[$r['p3']][$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];
			    $this->table[$r['p3']][$r['sku_item_id']]['description'] = $r['description'];
			    $this->table[$r['p3']][$r['sku_item_id']]['quantity']['total'] += $r['quantity'];
			    $this->table[$r['p3']][$r['sku_item_id']]['amount']['total'] += $r['amount'];
	        }
		}*/
	
		//$this->table = $table;
		//$this->label = $label;
		//$this->category = $category;
	}

	function generate_report()
	{
		global $con, $smarty, $con_multi;
        $branch_group = $this->branch_group;
        $isAllBranches = $this->isAllBranches;
        $g_bid = array();
        
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			$get_branch_group_code = $con_multi->sql_query("select branch_group_items.branch_id, branch_group.code 
													  from branch_group 
													  join branch_group_items on branch_group.id = branch_group_items.branch_group_id 
													  where id = $bg_id");

			while($bg = $con_multi->sql_fetchrow($get_branch_group_code)){
				$g_bid[] = $bg['branch_id'];
				//$tbl_name[] = "sku_items_sales_cache_b".$bg['branch_id'];
				$bg_code = $bg['code'];
				$this->label[$bg['branch_id']] = $bg['code'];
			}
			$con_multi->sql_freeresult($get_branch_group_code);
			
			//$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_bg".$bg_id;
			$this->run_report($g_bid);
			$branch_name = $branch_group['header'][$bg_id]['code'];
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
	            $g_bid[] = $bid;
	            $this->run_report($g_bid);
	            $branch_name = BRANCH_CODE;
			}else{
				if($bid==0){
	                $branch_name = "All";
	                
	                $b_sql = "select * from branch where active=1 order by sequence,code";
	                $q_b = $con_multi->sql_query($b_sql);
	                while($r = $con_multi->sql_fetchrow($q_b)){
                        $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$r['id'];
                        $g_bid[] = $r['id'];
			            $this->label[$r['id']] = $r['code'];
					}
					$con_multi->sql_freeresult($q_b);
					$this->run_report($g_bid);
					/*if($branch_group['header']){
						foreach($branch_group['header'] as $bg_id=>$bg){
                            $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_bg".$bg_id;
				            $this->run_report($bg_id+10000,$tbl_name);
				            $this->label[$bg_id+10000] = $bg['code'];
						}
					}*/
				}else{
	                $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
	                $g_bid[] = $bid;
		            $this->run_report($g_bid);
					$branch_name = get_branch_code($bid);
				}
			}
		}
		
		//$table = $this->table;
		//$label = $this->label;
		//$category = $this->category;
		
        $vendor_id = $this->vendor_id;
		if($vendor_id!=0){
            $con_multi->sql_query("select description from vendor where id=".mi($vendor_id))or die(mysql_error());
            $vn = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
            $vendor_name = $vn['description'];
		}
		
		@ksort($this->label);
		if($this->sort_by && $this->table && !isset($_REQUEST['group_by_sku'])){
			foreach($this->table as $cat_id=>$item_list){
				uasort($this->table[$cat_id], array($this,"sort_table"));
			}
		}
    
	    if($vendor_name)
	    	$ven = $vendor_name;
	    else
	    	$ven = "All";


	    $rpt_title[] = "Branch: ".$branch_name;
	    $rpt_title[] = "Date: From $this->date_from to $this->date_to";
		$rpt_title[] = "Vendor: ".$ven;
		$rpt_title[] = "Category: ".$this->cat_desc;

		$report_title = join('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$rpt_title);

	    $smarty->assign('report_title',$report_title);
    
		/*
		echo '<pre>';
    	print_r($this->table);
		echo '</pre>';
		*/
		
        $smarty->assign('vendor_name',$vendor_name);
		$smarty->assign('label',$this->label);
		$smarty->assign('table',$this->table);
		$smarty->assign('category',$this->category);
		$smarty->assign('branch_name',$branch_name);
		$smarty->assign('group_by_sku',isset($_REQUEST['group_by_sku']));
	}
	
	function sort_table($a,$b)
	{
	    if ($a[$this->sort_by]==$b[$this->sort_by]) return 0;
	    
	    if($this->sort_order=="desc"){
            return ($a[$this->sort_by] > $b[$this->sort_by]) ? -1 : 1;
		}else{
            return ($a[$this->sort_by] < $b[$this->sort_by]) ? -1 : 1;
		}
	}
	
	function process_form()
	{
		// do my own form process
		global $con,$smarty,$sessioninfo,$con_multi;
		
		// call parent
		parent::process_form();
        $bid  = get_request_branch(true);
            
		$department_id = $_REQUEST['department_id'];
	    $sku_type_code = $_REQUEST['sku_type_code'];
	    $vendor_id = $_REQUEST['vendor_id'];
	    $GRN = $_REQUEST['GRN'];
        $category_id = $_REQUEST['category_id'];
        $this->sort_by = trim($_REQUEST['sort_by']);
		$this->sort_order = trim($_REQUEST['sort_order']);
		if($this->sort_order!= 'asc' && $this->sort_order != 'desc')	$this->sort_order = 'asc';
		
		$this->date_from=$_REQUEST['date_from'];
		$this->date_to=$_REQUEST['date_to'];

		$mtest =strtotime("+1 year",strtotime($this->date_from));
		if (strtotime($this->date_to) > $mtest || strtotime($this->date_to)< strtotime($this->date_from)){
        	$this->date_to = date("Y-m-d",$mtest);
        	$_REQUEST['date_to'] = $this->date_to;
		}
	
		if($category_id)
		{
	        $con_multi->sql_query("select level,description from category where id=".mi($category_id)) or die(mysql_error());
    		$temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
    		$level = $temp['level'];
			$this->cat_desc = $temp['description'];
    	}else{
            $this->cat_desc = "All";
		}

		if($bid || strpos($_REQUEST['branch_id'],'bg,')===0){
			$this->group_by2 ='year,month,';
		}else{
            $this->isAllBranches = true;
		}
		
		$filter = array();
		if($category_id)
		{
	        $filter[] = "p$level=".mi($category_id);
	    }else{
            $filter[] = "p2 in ($sessioninfo[department_ids])";
		}
		$filter[] = "pos.date between ".ms($this->date_from)." and ".ms($this->date_to);
		//$filter[] = $v_filter;
		if($sku_type_code != "all")
		{
        $filter[] = "sku_type=".ms($sku_type_code);
    	}
		
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
		$filter = join(' and ',$filter);
		
		$this->filter = $filter;
		$this->department_id = $department_id;
		$this->sku_type_code = $sku_type_code;
		$this->vendor_id = $vendor_id;
		$this->grn = $GRN;
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
$report = new MonthlyVendorSalesReportByCategory('Monthly Vendor Sales by Category');
//$con_multi->close_connection();
?>
