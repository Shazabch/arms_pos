<?php
/*
12/9/2014 11:58 AM Andy
- Move the old stock balance summary to custom report for smarthq.
*/
include("../../include/common.php");
$maintenance->check(119);

ini_set('memory_limit', '1024M');
set_time_limit(0);
if($sessioninfo['u'] == 'admin'){
	ini_set("display_errors",1);
}
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");

class Stock_Balance_Summary extends Module{
    function __construct($title){
        global $con, $smarty, $sessioninfo, $config;

        if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
		if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));

        // branches
        $q1 = $con->sql_query("select * from branch where active=1 order by sequence,code") or die(mysql_error());
        while($r = $con->sql_fetchassoc($q1)){
			if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
			$branches[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);

		// load items
		$q1 = $con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id order by branch.sequence, branch.code",false,false);
		while($r = $con->sql_fetchassoc($q1)){
			if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['branch_id'])) continue;
	        $branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
   		$con->sql_freeresult($q1);
		
		// branch group
		// load header
		$q1 = $con->sql_query("select * from branch_group",false,false);
		while($r = $con->sql_fetchassoc($q1)){
			if(!$branches_group['items'][$r['id']]) continue;
		    $branches_group['header'][$r['id']] =$r;
		}
		$con->sql_freeresult($q1);
   		
		$smarty->assign('branches_group',$branches_group);
		$smarty->assign('branches',$branches);

		// vendor
		if($sessioninfo['vendor_ids']){
			$filter_vendor = "and id in ($sessioninfo[vendor_ids])";
		}
		$con->sql_query("select id,description from vendor where active=1 $filter_vendor order by description") or die(mysql_error());
		while($r = $con->sql_fetchrow()){
		    $vendors[$r['id']] =$r;
		}
		$con->sql_freeresult();
		$smarty->assign('vendors',$vendors);
		
		// sku type
		$con->sql_query("select * from sku_type") or die(mysql_error());
		$smarty->assign("sku_type", $con->sql_fetchrowset());
		$con->sql_freeresult();
		
		// sort array
		$sort_arr = array(
			'cname'=>'Category Name',
			'vcode'=>'Vendor Code',
			'vname'=>'Vendor Name',
			'bcode'=>'Branch Code',
			'sb_from'=>'Opening Balance Qty',
			'sb_from_val'=>'Opening Balance Value',
			'sb_to'=>'Closing Balance Qty',
			'sb_to_val'=>'Closing Balance Value'
		);
		$smarty->assign('sort_arr',$sort_arr);
        parent::__construct($title);
	}
	
	function _default(){
		$this->display("smarthq/report.stock_balance_summary.tpl");
	}
	
	function output_excel(){
		global $smarty, $sessioninfo;
		// Export function

		include("include/excelwriter.php");
		$smarty->assign('no_header_footer', true);

		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=stock_balance_summary_'.time().'.xls');

		print ExcelWriter::GetHeader();
		$this->show_report();
		print ExcelWriter::GetFooter();

	  	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export $_REQUEST[report_title] To Excel()");

		exit;
	}
	

	function show_report(){
	    global $con, $smarty, $sessioninfo, $con_multi, $config;
	    
	    //print_r($_REQUEST);
		$date_from = $_REQUEST['from'];
		$date_from_opening = date('Y-m-d', strtotime("-1 day", strtotime($date_from)));
		$date_to = $_REQUEST['to'];
 		$date_to_timestamp = date('Y-m-d', strtotime("+1 day", strtotime($date_to)));
		$category_id = mi($_REQUEST['category_id']);
		$all_cat = $_REQUEST['all_cat'];
		$sku_type = $_REQUEST['sku_type'];
		$vendor_id = mi($_REQUEST['vendor_id']);
		if($vendor_id>0) $show_by = 'cat';
		else $show_by = $_REQUEST['show_by'];
		$branch_id =get_request_branch(true);
		$sort_by = $_REQUEST['sort_by'];
		$order_by = $_REQUEST['order_by'];
		$use_grn=$_REQUEST['use_grn'];
		$got_opening_sc = $_REQUEST['got_opening_sc'];
		$got_range_sc = $_REQUEST['got_range_sc'];
		
	    $branches_group = $smarty->get_template_vars('branches_group');
	    $branches = $smarty->get_template_vars('branches');

	    // checking parameters
		$bid_list = array();
		if($branch_id>0){   // selected single branch
            $bid_list[] = $branch_id;
            $report_header[] = "Branch: ".$branches[$branch_id]['code'];
		}else{
			if(preg_match("/^REGION_/", $_REQUEST['branch_id'])){
				$region = str_replace("REGION_", "", $_REQUEST['branch_id']);
				$q1 = $con->sql_query("select b.* from branch b where b.active = 1 and b.region = ".ms($region));

				while($r = $con->sql_fetchassoc($q1)){
					if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
					$bid_list[] = $r['id'];
				}
				$con->sql_freeresult($q1);
				$report_header[] = "Region: ".$region;
			}elseif($branch_id<0){   // negative branch id is branch group
                $bgid = abs($branch_id);
				if(!$branches_group['items'][$bgid]) $err[] = "Invalid Branch.";
				else{
					foreach($branches_group['items'][$bgid] as $bid=>$b){
						$bid_list[] = $bid;
					}
					$report_header[] = "Branch Group: ".$branches_group['header'][$bgid]['code'];
				}
			}else{  // all branches
				foreach($branches as $b){
                    $bid_list[] = $b['id'];
				}
				$report_header[] = "Branch: All";
			}
		}
	  
		if(!$date_from) $err[] = "Invalid From Date.";
		if(!$date_to) $err[] = "Invalid To Date.";
		
		$report_header []= "Date from $date_from to $date_to";
		if(!$all_cat){
            if(!$category_id) $err[] = "Invalid Category.";
		}
		if(!$bid_list)  $err[] = "No Branch Selected.";
		
		if($use_grn && !$vendor_id)	$err[] = "Use GRN must have vendor selected";
		
		if($err){
			$smarty->assign('err',$err);
			$this->display('smarthq/report.stock_balance_summary.tpl');
			exit;
		}
		
		//vendor
		if($vendor_id){
            if (!$use_grn)   $filter[] = "sku.vendor_id=$vendor_id";
			$con->sql_query("select description from vendor where id=$vendor_id");
			$report_header[] = "Vendor: ".$con->sql_fetchfield(0);
		}else
			$report_header[] = "Vendor: All";
      
        //sku type
		if($sku_type){
			$filter[] = "sku.sku_type=".ms($sku_type);
			$report_header[] = "SKU Type: $sku_type";
		}else $report_header[] = "SKU Type: All";

		if($_REQUEST['blocked_po']){
			$report_header[] = "Blocked Item in PO: ".ucwords($_REQUEST['blocked_po']);
		}

		if(!$_REQUEST['status']) $status = "Inactive";
		elseif($_REQUEST['status'] == 1) $status = "Active";
		else $status = ucwords($_REQUEST['status']);

		$report_header[] = "Status: ".$status;
		
		$filter[] = "cc.p2 in (".$sessioninfo['department_ids'].")";
		
		//category
		if($all_cat){
			$c_level = 1;
			$report_header[] = "Category: All";	
		}else{
			$con->sql_query("select *, (select max(level) from category) as max_level from category where id=$category_id") or die(mysql_error());
			$selected_category = $con->sql_fetchrow();

			if($selected_category['level'] != $selected_category['max_level'])
				$c_level = $selected_category['level'] + 1;
			else
				$c_level = $selected_category['level'];

			$filter[] = "cc.p".mi($selected_category['level'])."=$category_id";

			$report_header[] = "Category: ".$selected_category['description'];

		}

		$filter[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
		
		if($show_by=='cat') $report_header[] = "Show by: Category";
		elseif($show_by=='branch') $report_header[] = "Show by: Branch";
		else  $report_header[] = "Show by: Vendor";

		$blocked_po = trim($_REQUEST['blocked_po']);
		$status = trim($_REQUEST['status']);
		
		if($blocked_po){
			if($blocked_po=='yes'){
				$filter[] = "si.block_list like ".ms("%i:$bid;s:2:\"on\";%");
			}elseif($blocked_po=='no'){
				$filter[] = "(si.block_list not like ".ms("%i:$bid;s:2:\"on\";%")." or si.block_list is null)";
			}
		}
		
		if($status != "all") $filter[] = "si.active = ".mi($status);
		
		if($filter) $filter = "where ".join(' and ', $filter);
		
		/*$con->sql_query("drop table tmp_sb_compare", false,false);
		$con->sql_query("create table tmp_sb_compare (
			sku_item_id int primary key,
			value_add double default 0,
			value_sub double default 0
		)");*/
		
		$b_count = 0;
        foreach($bid_list as $bid){
        	$b_count++;
        	
        	if($sessioninfo['u']=='admin'){
			//	print ("b_count = $b_count start: ".memory_get_usage()."<br>");
			}
            // table for stock balance - from
            list($y,$m,$d) = explode("-",$date_from_opening);
            $tbl_sb_1 = "stock_balance_b".mi($bid)."_".mi($y);
			$prms = array();
			$prms['tbl'] = $tbl_sb_1;
			initial_branch_sb_table($prms);
            //$this->check_table($tbl_sb_1);
            
			// table for stock balance - to
            list($y,$m,$d) = explode("-",$date_to);
            $tbl_sb_2 = "stock_balance_b".mi($bid)."_".mi($y);
			$prms = array();
			$prms['tbl'] = $tbl_sb_2;
			initial_branch_sb_table($prms);
            //$this->check_table($tbl_sb_2);

			$sort = "order by ".($show_by=='vendor'? 'vname': 'cname');

			//add HQ Cost
			if ($_REQUEST['hq_cost']) $extrasql = ", si.hq_cost as start_cost, si.hq_cost as cost";
			else    $extrasql= ", ifnull(sb1.cost,si.cost_price) as start_cost, ifnull(sb2.cost,si.cost_price) as cost";

			if($sessioninfo['u'] == 'admin'){
				//print "Start Query for Branch ID#$bid: ".memory_get_usage()."<br>";
			}
			
			//joining table
			$grn_sid_list = array();
			if ($use_grn){
				// select those sku of this grn vendor between this date
				/*$vsh_filter = array();
				$vsh_filter[] = "vsh.branch_id=".mi($bid)." and vsh.source='grn'";
				$vsh_filter[] = "vsh.added between ".ms($date_from)." and ".ms($date_to);
				$vsh_filter[] = "vsh.vendor_id=".mi($vendor_id);
				$vsh_filter = join(' and ', $vsh_filter);
				
				$sql = "select distinct(sku_item_id) as sid
				from vendor_sku_history vsh 
				left join sku_items si on si.id=vsh.sku_item_id
				left join sku on si.sku_id = sku.id
				left join category_cache cc on cc.category_id=sku.category_id
				$filter and $vsh_filter";
				$con_multi->sql_query($sql) or die(mysql_error());
				$grn_sid_list = array();
				while($r = $con_multi->sql_fetchassoc()){
					$grn_sid_list[] = mi($r['sid']);
				}
				$con_multi->sql_freeresult();
			
				// construct sql
			    $ven_sql=",(select vsh.vendor_id from vendor_sku_history vsh where vsh.sku_item_id=si.id and vsh.branch_id=$bid and vsh.added <= '$date_from' order by vsh.branch_id, vsh.sku_item_id, vsh.added desc limit 1) as last_grn_vendor_id,sku.vendor_id as master_vendor_id";*/
				$left_join = "left join vendor on vendor.id=$vendor_id";
				//$having = "	having vendor_id = ".mi($vendor_id);
				
				$filter_grn_vendor = ($filter ? ' and ':'')."si.id in (select vsh.sku_item_id from vendor_sku_history_b".$bid." vsh where vendor_id=$vendor_id and (".ms($date_from)." between vsh.from_date and vsh.to_date or ".ms($date_to)." between vsh.from_date and vsh.to_date or vsh.from_date between ".ms($date_from)." and ".ms($date_to)."))";
			}else{
			    $ven_sql=",sku.vendor_id";
			    $left_join = "left join vendor on vendor.id=sku.vendor_id";
			}
			
			if ($config['stock_balance_report_show_additional_selling']){
				//get selling price
				$sell_sql=",ifnull((select price from sku_items_price_history osh
						where osh.branch_id=$bid and osh.sku_item_id=si.id and osh.added <= ".ms($date_from_opening)."
						order by osh.added desc limit 1),si.selling_price) as os_selling, 
						ifnull((select price from sku_items_price_history osh2
						where osh2.branch_id=$bid and osh2.sku_item_id=si.id and osh2.added <= ".ms($date_to)."
						order by osh2.added desc limit 1),si.selling_price) as cs_selling";
			}

	       $sql = "select si.id as sid,si.id,si.sku_item_code ,si.artno,si.mcode,si.description,si.selling_price,c.id as category_id $ven_sql,sku.sku_type,sb1.qty as sb_from,
				sb2.qty as sb_to,c.description as cname $extrasql,
				sku.category_id as real_cid,vendor.code as vcode,vendor.description as vname,c.tree_str,ifnull(sic.changed,1) as changed,
				(select count(*) from category c2 where c2.root_id=c.id) as got_child $sell_sql
				from sku_items si
				left join sku on si.sku_id=sku.id
				left join $tbl_sb_1 sb1 on sb1.sku_item_id=si.id and ((".ms($date_from_opening)."
					between sb1.from_date and sb1.to_date))
				left join $tbl_sb_2 sb2 on sb2.sku_item_id=si.id and ((".ms($date_to)."
					between sb2.from_date and sb2.to_date))
				left join category_cache cc on cc.category_id=sku.category_id
				left join category c on c.id=cc.p".mi($c_level)."
				left join sku_items_cost sic on si.id = sic.sku_item_id and sic.branch_id = $bid
				$left_join
				$filter $filter_grn_vendor
				$having
				$sort";
			//print $sql."<br /><br />";
			//if($sessioninfo['id']==317)print $sql;
			if($sessioninfo['u']=='admin'){
			//	print ("b_count = $b_count , b4 get item list: ".memory_get_usage()."<br>");
			}
			$sql_rid=$con_multi->sql_query($sql) or die(mysql_error());
			$sid_list = array();
			$default_open_bal_by_sku = array();
			$sku = array();
			
			
			while($r = $con_multi->sql_fetchassoc($sql_rid)){
				$sid = mi($r['sid']);
				
				if($use_grn){
					/*if(($r['last_grn_vendor_id'] != $vendor_id) && !in_array($sid, $grn_sid_list)){
						if(!$config['use_grn_last_vendor_include_master']){
							continue;
						}elseif($r['master_vendor_id'] != $vendor_id){
							continue;
						}
					}*/
					$r['vendor_id'] = $vendor_id;
				}
			    // category id
				if($r['real_cid']==$category_id){
                    $cid = $r['real_cid'];
				}    
				else    $cid = $r['category_id'];

				// make category array to store category information
				if(!$category_info[$cid]){
				    if($cid==0){
		            	$category_info[$cid]['id'] = $cid;
		            	$category_info[$cid]['description'] = 'Uncategorized';
		            }elseif($all_cat||$cid!=$category_id){
                        $category_info[$cid]['id'] = $cid;
                        $category_info[$cid]['description'] = $r['cname'];
					}else   $category_info[$cid] = $selected_category;
				}
				$category_info[$cid]['got_child'] = $r['got_child'];

				if(!$vendor_info[$r['vendor_id']]){
                    $vendor_info[$r['vendor_id']]['id'] = $r['vendor_id'];
                    $vendor_info[$r['vendor_id']]['code'] = $r['vcode'];
                    $vendor_info[$r['vendor_id']]['description'] = $r['vname'];
				}
				
				if($show_by=='vendor') $key = $r['vendor_id'];
				elseif($show_by=='branch') $key = $bid;
				else{
                    $key = $cid;
                    if(!$r['tree_str']) $r['tree_str'] = $selected_category['tree_str']."($cid)";
                    $table[$key]['tree_str'] = $r['tree_str'];
                    $table[$key]['level'] = $c_level;
				}
				
				$table[$key]['key'] = $key;
				//$table[$key]['branches'] .= "($bid)";
				$table[$key]['sb_from'] += $r['sb_from'];
				$table[$key]['sb_to'] += $r['sb_to'];

				$table[$key]['sb_from_val'] += ($r['sb_from']*$r['start_cost']);
				$table[$key]['sb_to_val'] += ($r['sb_to']*$r['cost']);
				
				if (!$table[$key]['changed']) {
					if ($r['changed']) $table[$key]['changed'] = $r['changed'];
				}
				
				//if($sessioninfo['u'] != 'admin'){
					//$default_open_bal_by_sku[$sid] += $r['sb_from']*$r['start_cost'];
					$sid_info[$sid]['start_cost'] = $r['start_cost'];
					$sid_info[$sid]['open_bal'] += $r['sb_from']*$r['start_cost'];
				//}	
					$sid_info[$sid]['closing_cost']=$r['cost'];	// next time if got memory problem, need to remove this and insert data into tmp table
				
				
				//$tmp_sb_compare = array();
				//$tmp_sb_compare['sku_item_id'] = $sid;
				//$tmp_sb_compare['value_add'] += $r['sb_from']*$r['start_cost'];
				//$con->sql_query("insert into tmp_sb_compare ".mysql_insert_by_field($tmp_sb_compare)." on duplicate key update value_add=value_add+".ms($tmp_sb_compare['value_add']));
				
				if ($config['stock_balance_report_show_additional_selling']){
					$table[$key]['sb_from_selling'] += ($r['sb_from']*$r['os_selling']);						
					$table[$key]['sb_to_selling'] += ($r['sb_to']*$r['cs_selling']);
				}
				
				//if($sessioninfo['u'] != 'admin'){		
					//$sid_list[$r['id']] = $r['id'];
					
					if($show_by=='vendor'){
						// show by vendor
						$sid_info[$r['id']]['vendor_id'] = $r['vendor_id'];
					}elseif($show_by=='branch'){
						// show by branch
					}else{
						// show by cat
						$sid_info[$r['id']]['category_id'] = $cid;
					}
				//}
			}
			
			$con_multi->sql_freeresult($sql_rid);
			if($sessioninfo['u'] == 'admin'){
				//print "End Query: ".memory_get_usage()."<br>";
				//print "Total Item Count: ".count($sku)."<br>";
				//exit;
			}
			
			$sid_count = count($sid_info);
			for($i=0; $i<$sid_count; $i+=5000){
				if($sessioninfo['u']=='admin'){
					//print ("b_count = $b_count , $i to ".($i+5000).": ".memory_get_usage()."<br>");
				}
				
				//$sku = array();	// next time if got memory problem, need to un-comment this row
				//$sid_list2 = array_slice($sid_list, $i, 5000);
				
				$sid_list2 = array_keys(array_slice($sid_info, $i, 5000, true));
				$where_sid = "sku_item_id in (" . join(",", $sid_list2) . ")";
				$where_sid2 = "si.id in (" . join(",", $sid_list2) . ")";
				$sid_list2 = array();
				
				if ($_REQUEST['hq_cost']) $getcost = ", ifnull(sku_items.hq_cost,sku_items.cost_price) as grn_cost";
				else    $getcost = ", ifnull(sich.grn_cost,sku_items.cost_price) as grn_cost";
					
				if($sessioninfo['u']=='admin'){
					//print ("b_count = $b_count , b4 get item cost: ".memory_get_usage()."<br>");
				}
					//Closing cost
				$q_cc = $con_multi->sql_query("select sich.date,sich.sku_item_id as sid $getcost,
				(select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=$bid and sh.date <='$date_to') as stock_date
				from
				sku_items_cost_history sich
				left join sku_items on sich.sku_item_id = sku_items.id
				where branch_id=$bid and sich.date <= '$date_to' and sich.date > 0 and $where_sid
				having stock_date=sich.date order by null ") or die(mysql_error());
	
				while($r = $con_multi->sql_fetchassoc($q_cc)){
	
				    $sid = $r['sid'];
		            $sid_info[$sid]['closing_cost'] = $r['grn_cost'];
				}
				$con_multi->sql_freeresult($q_cc);
				
				if($sessioninfo['u']=='admin'){
					//print ("b_count = $b_count , after get item cost: ".memory_get_usage()."<br>==========<br>");
				}	
	/*			$q_cc = $con_multi->sql_query("select sich.date,sich.sku_item_id as sid $getcost,
				(select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=$bid and sh.date <='$date_to') as stock_date
				from
				sku_items_cost_history sich
				left join sku_items on sich.sku_item_id = sku_items.id
				where branch_id=$bid and sich.date <= '$date_to' and sich.date > 0 and $where_sid
				having stock_date=sich.date order by null ") or die(mysql_error());
	*/

				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , b4 get grn: ".memory_get_usage()."<br>");
				}
				// GRN
				//GRN = get the rcv qty
				$tmp_grp = $tmp_col = '';
				if($_REQUEST['use_grn']){
					$tmp_col = ", grn.vendor_id as grn_vendor_id";
					$tmp_grp = ", grn_vendor_id";
				}
				$sql = "select grn_items.sku_item_id as sid,
			sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
			sum(
			(if(grn_items.acc_ctn is null and grn_items.acc_pcs is null, (grn_items.ctn * rcv_uom.fraction) + grn_items.pcs, (grn_items.acc_ctn * rcv_uom.fraction) + grn_items.acc_pcs) - (ifnull(grn_items.return_ctn * rcv_uom.fraction,0) + ifnull(grn_items.return_pcs,0))) * if(grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost) / rcv_uom.fraction +
			ifnull(((if(grn_items.acc_ctn is null and grn_items.acc_pcs is null, (grn_items.ctn * rcv_uom.fraction) + grn_items.pcs, (grn_items.acc_ctn *rcv_uom.fraction) + grn_items.acc_pcs) - (ifnull(grn_items.return_ctn * rcv_uom.fraction,0) + ifnull(grn_items.return_pcs,0))) * if(grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost) / rcv_uom.fraction) *
			(grn.grn_tax/100), 0)) as total_rcv_cost $tmp_col
			from grn_items
			left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
			left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
			left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
			where grn.branch_id=$bid and rcv_date between ".ms($date_from)." and ".ms($date_to)." and $where_sid and grn.approved=1 and grn.status=1 and grn.active=1 and grr.active=1
			group by sid $tmp_grp";
				$con_multi->sql_query($sql) or die(mysql_error());
				while($r = $con_multi->sql_fetchrow()){
				    $sid = $r['sid'];
				    
                    if($show_by=='vendor') $key = $sid_info[$sid]['vendor_id'];
                    elseif($show_by=='branch') $key = $bid;
					else $key = $sid_info[$sid]['category_id'];

					
					$table[$key]['grn'] += $r['qty'];
					$table[$key]['grn_cost'] += $r['total_rcv_cost'];
					
					if($use_grn){
						if($r['grn_vendor_id'] == $vendor_id){
							$table[$key]['grn_vendor_qty']+= $r['qty'];
							$table[$key]['grn_vendor_cost']+= $r['total_rcv_cost'];
						}
					}
				}
				$con_multi->sql_freeresult();
				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , after get grn: ".memory_get_usage()."<br>==========<br>");
				}
				
				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , b4 get gra: ".memory_get_usage()."<br>");
				}
				//GRA
				$sql = "select
			gra_items.sku_item_id as sid,
			sum(qty) as qty, sum(qty * gra_items.cost) as total_gra_cost
			from gra_items
			left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
			where gra.branch_id=$bid and return_timestamp between ".ms($date_from)." and ".ms($date_to_timestamp)." and $where_sid and gra.status=0 and gra.returned=1
			group by sid";
			    //print $sql;
				$con_multi->sql_query($sql) or die(mysql_error());
				while($r = $con_multi->sql_fetchrow()){
				    $sid = $r['sid'];

                    if($show_by=='vendor') $key = $sid_info[$sid]['vendor_id'];
					elseif($show_by=='branch') $key = $bid;
					else $key = $sid_info[$sid]['category_id'];


					$table[$key]['gra'] += $r['qty'];
					$table[$key]['gra_cost'] += $r['total_gra_cost'];
				}
				$con_multi->sql_freeresult();
				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , after get gra: ".memory_get_usage()."<br>==========<br>");
				}
				
				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , b4 get pos: ".memory_get_usage()."<br>");
				}
				// POS
				$tbl="sku_items_sales_cache_b".$bid;
				$sql = "select
						si.id as sid,
						sum(qty) as qty,sum(pos.cost) as total_pos_cost
						from $tbl pos
						left join sku_items si on si.id=pos.sku_item_id
						where date between ".ms($date_from)." and ".ms($date_to)." and $where_sid
						group by sid";
			    //print $sql;
			 	$con_multi->sql_query($sql) or die(mysql_error());
			 	while($r = $con_multi->sql_fetchrow()){
				    $sid = $r['sid'];

                    if($show_by=='vendor') $key = $sid_info[$sid]['vendor_id'];
					elseif($show_by=='branch') $key = $bid;
					else $key = $sid_info[$sid]['category_id'];


					$table[$key]['pos'] += $r['qty'];
					$table[$key]['pos_cost'] += $r['total_pos_cost'];
				}
				$con_multi->sql_freeresult();
				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , after get pos: ".memory_get_usage()."<br>==========<br>");
				}
				
				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , b4 get DO: ".memory_get_usage()."<br>");
				}
				// DO
				$sql = "select
			do_items.sku_item_id as sid,
			sum(do_items.ctn *uom.fraction + do_items.pcs) as qty
			from do_items
			left join uom on do_items.uom_id=uom.id
			left join do on do_id = do.id and do_items.branch_id = do.branch_id
			where do_items.branch_id=$bid and do_date between ".ms($date_from)." and ".ms($date_to)." and $where_sid and do.approved=1 and do.checkout=1 and do.status<2
			group by sid";
			    //print $sql;
				$con_multi->sql_query($sql) or die(mysql_error());
			 	while($r = $con_multi->sql_fetchrow()){
				    $sid = $r['sid'];

                    if($show_by=='vendor') $key = $sid_info[$sid]['vendor_id'];
					elseif($show_by=='branch') $key = $bid;
					else $key = $sid_info[$sid]['category_id'];


					$table[$key]['do'] += $r['qty'];
					
					if($sessioninfo['u']=='admin'){
						//print "$sid = $r[qty] * ".$sku[$sid]['closing_cost']."<br />";
					}
					$table[$key]['do_cost'] += $r['qty'] * $sid_info[$sid]['closing_cost'];
				}
				$con_multi->sql_freeresult();
				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , after get DO: ".memory_get_usage()."<br>==========<br>");
				}
				
				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , b4 get adj: ".memory_get_usage()."<br>");
				}
				// ADJ
				$sql = "select
				ai.sku_item_id as sid,
				sum(qty) as qty,
				sum(ai.qty * ai.cost) as item_cost,
				if(qty>=0,'p','n') as type
				from adjustment_items ai
				left join adjustment adj on adjustment_id = adj.id and ai.branch_id = adj.branch_id
				where ai.branch_id =$bid and adjustment_date between ".ms($date_from)." and ".ms($date_to)." and $where_sid and adj.approved=1 and adj.status<2
				group by type, sid";
				//print $sql.'<br />';
				$con_multi->sql_query($sql) or die(mysql_error());
				while($r = $con_multi->sql_fetchrow()){
				    $sid = $r['sid'];

                    if($show_by=='vendor') $key = $sid_info[$sid]['vendor_id'];
					elseif($show_by=='branch') $key = $bid;
					else $key = $sid_info[$sid]['category_id'];
					
					if($r['type']=='p'){
						$table[$key]['adj_in'] += $r['qty'];
						$table[$key]['adj_in_cost'] += ($r['item_cost']);
					}
					elseif($r['type']=='n'){
						$table[$key]['adj_out']+=abs($r['qty']);
						$table[$key]['adj_out_cost']+=(abs($r['item_cost']));
					}
				}
				$con_multi->sql_freeresult();
				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , after get adj: ".memory_get_usage()."<br>==========<br>");
				}
				
				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , b4 check stock take at opening: ".memory_get_usage()."<br>");
				}
				// check if got stock take at opening
				$minus_1_day=strtotime("-1 day",strtotime($date_from));
				$sb_year=date("Y",$minus_1_day);
				$sc1day_date= date("Y-m-d",$minus_1_day);
				$sb_tbl="stock_balance_b$bid"."_".$sb_year;
				
				$sql = "select si.id as sid, sum(sc.qty) as qty, sc.date, sb.qty as sb_qty,
						sum(if(".ms($_REQUEST['hq_cost']).", si.hq_cost, sc.cost) * sc.qty) as cost
						from stock_check sc
						join sku_items si on si.sku_item_code=sc.sku_item_code
						left join $sb_tbl sb on si.id=sb.sku_item_id and ".ms($sc1day_date)." between sb.from_date and sb.to_date 
						where sc.branch_id=$bid and sc.date = ".ms($date_from)." and $where_sid2
						group by sid";
				//print $sql;
                $con_multi->sql_query($sql) or die(mysql_error());
                $opening_sc_balance_val = $opening_sc_balance_sids = array();
				while($r = $con_multi->sql_fetchrow()){
				    $sid = $r['sid'];
				    $got_opening_sc = true;

                    if($show_by=='vendor') $key = $sid_info[$sid]['vendor_id'];
					elseif($show_by=='branch') $key = $bid;
					else $key = $sid_info[$sid]['category_id'];
					
					//$opening_sc_balance_sids[$r['date']][$sid]=$sid;
					//$opening_sc_balance_val[$r['date']][$sid]['qty']+=$r['qty'];
					//$opening_sc_balance_val[$r['date']][$sid]['cost']+=$r['cost'];
					
					// deduct opening cost first
					$table[$key]['sb_from_val'] -= $sid_info[$sid]['open_bal'];
					
					// adjust qty
					$sc_adj_qty = $r['qty'] - $r['sb_qty'];
					
					$table[$key]['sc_adj_from'] += $sc_adj_qty;
					$table[$key]['sb_from'] += $sc_adj_qty;
					
					$new_cost = 0;
					if($r['cost'] > 0){	// use stock take cost
						$new_cost = $r['cost'];
					}else{	
						$new_cost = $sid_info[$sid]['start_cost']*$r['qty'];
					}
					// add in new opening cost
					$table[$key]['sb_from_val'] += $new_cost;
				}
				$con_multi->sql_freeresult();
				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , after check stock take at opening: ".memory_get_usage()."<br>==========<br>");
				}
				
				if($sessioninfo['u']=='wsatp'){
					//print_r($default_open_bal_by_sku);
					//print "count = ".count($opening_sc_balance_sids['2012-06-27'])."<br>";
					//print "count default_open_bal_by_sku = ".count($default_open_bal_by_sku)."<br>";
				}
				
				// get opening stock balance qty and value before stock check
				/*if($opening_sc_balance_sids){
					if($sessioninfo['u']=='admin'){
					//	print ("b_count = $b_count , b4 check stock take opening adj: ".memory_get_usage()."<br>");
					}
					foreach ($opening_sc_balance_sids as $sc_date => $sids){
						$minus_1_day=strtotime("-1 day",strtotime($sc_date));
						$sb_year=date("Y",$minus_1_day);
						$sc1day_date=ms(date("Y-m-d",$minus_1_day));
						
						$sb_tbl="stock_balance_b$bid"."_".$sb_year;
					
						$sql = "select si.id as sid, sc.qty as qty
								from sku_items si
								left join $sb_tbl sc on si.id=sc.sku_item_id and $sc1day_date between sc.from_date and sc.to_date 
								where si.id in (".join(",",$sids).")
								group by sid";
						
						//print $sql."<br />";
						$q_sb = $con_multi->sql_query($sql) or die(mysql_error());
						
						while($r = $con_multi->sql_fetchassoc($q_sb)){
							$sid = $r['sid'];
							
		//					print $sc_date."    ".$sid."  =  ".$r['cost']."  <br />";
							if($show_by=='vendor') $key = $sid_info[$sid]['vendor_id'];
							elseif($show_by=='branch') $key = $bid;
							else $key = $sid_info[$sid]['category_id'];
							
							//$table[$key]['sb_from_val'] -= $default_open_bal_by_sku[$sid];
							$table[$key]['sb_from_val'] -= $sid_info[$sid]['open_bal'];
							//$table[$key]['sb_from_val'] = 0;
							
							//$tmp_sb_compare = array();
							//$tmp_sb_compare['sku_item_id'] = $sid;
							//$tmp_sb_compare['value_sub'] += $default_open_bal_by_sku[$sid];
							//$con->sql_query("insert into tmp_sb_compare ".mysql_insert_by_field($tmp_sb_compare)." on duplicate key update value_sub=value_sub+".ms($tmp_sb_compare['value_sub']));
							
							$sc_adj_qty = $opening_sc_balance_val[$sc_date][$sid]['qty'] - $r['qty'];
							
							$table[$key]['sc_adj_from'] += $sc_adj_qty;
							$table[$key]['sb_from'] += $sc_adj_qty;
							
							$new_cost = 0;
							if($opening_sc_balance_val[$sc_date][$sid]['cost']){
								//$unit_cost = $opening_sc_balance_val[$sc_date][$sid]['cost']/($opening_sc_balance_val[$sc_date][$sid]['qty'] - $r['qty']);
								//$unit_cost = $opening_sc_balance_val[$sc_date][$sid]['cost']/($opening_sc_balance_val[$sc_date][$sid]['qty']);
								
								$new_cost = $opening_sc_balance_val[$sc_date][$sid]['cost'];
							}else{
								//$unit_cost = $sku[$sid]['closing_cost'];
								$new_cost = $sid_info[$sid]['closing_cost']*$opening_sc_balance_val[$sc_date][$sid]['qty'];
							}
							//$sc_cost=($opening_sc_balance_val[$sc_date][$sid]['qty'] - $r['qty'])*$unit_cost;
							//$sc_cost= ($opening_sc_balance_val[$sc_date][$sid]['qty'])*$unit_cost;
							//$table[$key]['sb_from_val'] += $sc_cost;
							$table[$key]['sb_from_val'] += $new_cost;
							
							$got_opening_sc = true;
						}
						$con_multi->sql_freeresult($q_sb);
					}
		
					unset($opening_sc_balance_sids, $opening_sc_balance_val);
					if($sessioninfo['u']=='admin'){
					//	print ("b_count = $b_count , after check stock take opening adj: ".memory_get_usage()."<br>==========<br>");
					}
				}*/
				//unset($default_open_bal_by_sku);	// free memmory
				
				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , b4 check stock take in between: ".memory_get_usage()."<br>");
				}
				//---------------calculate stock check for selected period
				$date_start=date("Y-m-d", strtotime("+1 day",strtotime($date_from)));
				$date_end=$date_to;
				$sc_balance_sids = $sc_balance_val = array();
				
				$sql = "select si.id as sid, sum(if(".ms($_REQUEST['hq_cost']).", si.hq_cost, sc.cost) * sc.qty) as cost, sc.date, 
						sum(sc.qty) as qty
						from stock_check sc
						left join sku_items si on sc.sku_item_code=si.sku_item_code
						where sc.branch_id=$bid and sc.date between ".ms($date_start)." and ".ms($date_end)." and $where_sid2
						group by sc.date, sid";
				//print $sql."<br />";
	            $con_multi->sql_query($sql) or die(mysql_error());
				while($r = $con_multi->sql_fetchassoc()){
				    $sid = $r['sid'];
							
					if($show_by=='vendor') $key = $sid_info[$sid]['vendor_id'];
					elseif($show_by=='branch') $key = $bid;
					else $key = $sid_info[$sid]['category_id'];
	
					//print $sid."  =  ".$r['cost']."  <br />";
					//$sc_balance_sids[$r['date']][$sid]=$sid;
					$sc_balance_val[$r['date']][$sid]['qty']+=$r['qty'];
					//$table[$key]['sc_adj']+=$r['qty'];
					$sc_balance_val[$r['date']][$sid]['cost']+=$r['cost'];
					//$table[$key]['sc_adj_cost']+=$r['cost'];
					//$sc_balance_val[$r['date']][$sid]['cost']+=$r['qty']*$r['cost'];
					
					$got_range_sc = true;
				}
				$con_multi->sql_freeresult();
				if($sessioninfo['u']=='admin'){
				//	print ("b_count = $b_count , after check stock take in between: ".memory_get_usage()."<br>==========<br>");
				}
				
				// get stock balance qty and value before stock check
				if($sc_balance_val){
					if($sessioninfo['u']=='admin'){
					//	print ("b_count = $b_count , b4 check stock take adj in between: ".memory_get_usage()."<br>");
					}
					foreach ($sc_balance_val as $sc_date => $tmp_balance_val_list){
						$minus_1_day=strtotime("-1 day",strtotime($sc_date));
						$sb_year=date("Y",$minus_1_day);
						$sc1day_date=ms(date("Y-m-d",$minus_1_day));
						
						$sb_tbl="stock_balance_b$bid"."_".$sb_year;
					
						$sql = "select si.id as sid, sc.qty as qty
								from sku_items si
								left join $sb_tbl sc on si.id=sc.sku_item_id and $sc1day_date between sc.from_date and sc.to_date 
								where si.id in (".join(",", array_keys($tmp_balance_val_list)).")
								group by sid";
						//print $sql."<br />";
						$con_multi->sql_query($sql) or die(mysql_error());
						while($r = $con_multi->sql_fetchassoc()){
							$sid = $r['sid'];
		//					print $sc_date."    ".$sid."  =  ".$r['cost']."  <br />";
							if($show_by=='vendor') $key = $sid_info[$sid]['vendor_id'];
							elseif($show_by=='branch') $key = $bid;
							else $key = $sid_info[$sid]['category_id'];
							
							if($sc_balance_val[$sc_date][$sid]['cost']){
								$unit_cost = $sc_balance_val[$sc_date][$sid]['cost']/$sc_balance_val[$sc_date][$sid]['qty'];
							}else{
								$unit_cost = $sid_info[$sid]['closing_cost'];
							}
							
							$sc_qty = $sc_balance_val[$sc_date][$sid]['qty'];
							$qty_b4_sc = $r['qty'];
							
							$adj_qty = $sc_qty - $qty_b4_sc;
							$adj_cost = $unit_cost * $adj_qty;
							
							
							/*if($sc_balance_val[$sc_date][$sid]['cost']){
								$unit_cost = $sc_balance_val[$sc_date][$sid]['cost']/($sc_balance_val[$sc_date][$sid]['qty']);
							}else{
								$unit_cost = $sku[$sid]['closing_cost'];
							}
							$sc_cost=($table[$key]['qty'] - $r['qty'])*$unit_cost;*/
							if($sessioninfo['u']=='wsatp'){
								//if($adj_qty)	print "$sid. b4=$qty_b4_sc, sc_qty = $sc_qty, adj = $adj_qty<br>";
							}
							$table[$key]['sc_adj'] += $adj_qty;
							$table[$key]['sc_adj_cost'] += $adj_cost;
							$table[$key]['got_sc'] = true;
						}
						$con_multi->sql_freeresult();
					}
		
					unset($sc_balance_sids, $sc_balance_val);
					if($sessioninfo['u']=='admin'){
					//	print ("b_count = $b_count , after check stock take adj in between: ".memory_get_usage()."<br>==========<br>");
					}
				}

				// check got stock take
				/*$sql = "select si.id as sid,count(*) from stock_check sc
right join sku_items si on si.sku_item_code=sc.sku_item_code
where sc.branch_id=$bid and sc.date between ".ms($date_from)." and ".ms($date_to)." and $where_sid2
group by sid";
				//print $sql;
                $con_multi->sql_query($sql) or die(mysql_error());
				while($r = $con_multi->sql_fetchrow()){
				    $sid = $r['sid'];

                    if($show_by=='vendor')  $key = $sid_info[$sid]['vendor_id'];
					else    $key = $sid_info[$sid]['category_id'];
					
					$table[$key]['got_sc'] = true;
				}
				$con_multi->sql_freeresult();*/

				
				if($config['consignment_modules']){
			        // CN get cn qty
					$q_cn =$con_multi->sql_query("select
				cni.sku_item_id as sid,
				sum(cni.ctn *uom.fraction + cni.pcs) as qty,
				if(cn.date>='$date_from',0,1) as bal,

				(cn.date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = cni.sku_item_id and sh.branch_id=$bid and sh.date <'$from_date')) as dont_count

				from cn_items cni
				left join uom on cni.uom_id=uom.id
				left join cn on cni.cn_id = cn.id and cni.branch_id = cn.branch_id
				where cn.to_branch_id=$bid and cn.date <='$date_to' and $where_sid and cn.active=1 and cn.status=1 and cn.approved=1 group by bal,dont_count, sid order by null") or die(mysql_error());

					while($r = $con_multi->sql_fetchrow($q_cn)){
						if(!$r['dont_count']){
							$sid=$r['sid'];

		                    if($show_by=='vendor')  $key = $sid_info[$sid]['vendor_id'];
							elseif($show_by=='branch') $key = $bid;
							else    $key = $sid_info[$sid]['category_id'];

							$qty=$r['qty'];

							if(!$r['bal']){
								$table[$key]['cn_qty']+=$qty;
								$table[$key]['cn_val']+=$qty * $sid_info[$sid]['closing_cost'];
							}
						}
					}
					$con_multi->sql_freeresult($q_cn);

					// DN get dn qty
					$q_dn =$con_multi->sql_query("select
				cni.sku_item_id as sid,
				sum(cni.ctn *uom.fraction + cni.pcs) as qty,
				if(dn.date>='$date_from',0,1) as bal,

				(dn.date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = cni.sku_item_id and sh.branch_id=$bid and sh.date <'$from_date')) as dont_count

				from dn_items cni
				left join uom on cni.uom_id=uom.id
				left join dn on cni.dn_id = dn.id and cni.branch_id = dn.branch_id
				where dn.to_branch_id=$bid and dn.date <='$date_to' and $where_sid and dn.active=1 and dn.status=1 and dn.approved=1 group by bal,dont_count, sid order by null") or die(mysql_error());

					while($r = $con_multi->sql_fetchrow($q_dn)){
						if(!$r['dont_count']){
							$sid=$r['sid'];

		                    if($show_by=='vendor') $key = $sid_info[$sid]['vendor_id'];
							elseif($show_by=='branch') $key = $bid;
							else $key = $sid_info[$sid]['category_id'];

							$qty=$r['qty'];

							if(!$r['bal']){
								$table[$key]['dn_qty']+=$qty;
								$table[$key]['dn_bal']+=$qty * $sid_info[$sid]['closing_cost'];
							}
						}
					}
					$con_multi->sql_freeresult($q_dn);
				}
			}
			
			if($sessioninfo['u']=='admin'){
				//print ("b_count = $b_count end: ".memory_get_usage()."<br>===============<br>");
				
				//if($b_count>=2)	die();
			}
		}
		
		//recalculating
		if($table){
		    $total = array();
			foreach($table as $thread => $r){
				//clear empty data
				if (!$r['sb_from'] && !$r['sb_to']&& !$r['sb_from_val'] && !$r['sb_to_val'] && !$r['grn_cost'] && !$r['gra_cost'] && !$r['pos_cost'] && !$r['do_cost'] && !$r['adj_in'] && !$r['adj_out']){
					unset($table[$thread]);
					continue;
				}
				
				if($r['sb_from'] < 0 && $r['sb_from'] > -0.01) $table[$thread]['sb_from'] = $r['sb_from'] = 0;
				if($r['sb_from_val'] < 0 && $r['sb_from_val'] > -0.01) $table[$thread]['sb_from_val'] = $r['sb_from_val'] = 0;

			    //only calculate total if no ajax call
				if (!$_REQUEST['ajax']){
					$total['sc_adj_from'] += $r['sc_adj_from'];
					$total['sb_from'] += $r['sb_from'];
					$total['sb_from_val'] += $r['sb_from_val'];
					$total['sb_from_selling'] += $r['sb_from_selling'];
					$total['sb_to'] += $r['sb_to'];
					$total['sb_to_val'] += $r['sb_to_val'];
					$total['sb_to_selling'] += $r['sb_to_selling'];
					$total['grn'] += $r['grn'];
					$total['grn_cost'] += $r['grn_cost'];
					$total['grn_vendor_qty'] += $r['grn_vendor_qty'];
					$total['grn_vendor_cost'] += $r['grn_vendor_cost'];
					
					$total['gra'] += $r['gra'];
					$total['gra_cost'] += $r['gra_cost'];
					$total['pos'] += $r['pos'];
					$total['pos_cost'] += $r['pos_cost'];
					$total['do'] += $r['do'];
					$total['do_cost'] += $r['do_cost'];
					$total['sc_adj'] += $r['sc_adj'];
					$total['sc_adj_cost'] += $r['sc_adj_cost'];
					$total['adj_in'] += $r['adj_in'];
					$total['adj_in_cost'] += $r['adj_in_cost'];
					$total['adj_out'] += $r['adj_out'];
					$total['adj_out_cost'] += $r['adj_out_cost'];
					// cn
					$total['cn_qty']+=$r['cn_qty'];
					$total['cn_val']+=$r['cn_val'];
					// dn
					$total['dn_qty']+=$r['dn_qty'];
					$total['dn_val']+=$r['dn_val'];
				}
			}
		}
		//print_r($table);
		
		if($sort_by&&$table){   // sort data
		    $this->sort_by = $sort_by;
		    $this->order_by = $order_by;
		    
		    $normal_sort = array('sb_from','sb_from_val','sb_to','sb_to_val');
		    if(in_array($sort_by, $normal_sort)){
                usort($table, array($this,"normal_sort_table"));
			}else{
			    if($show_by=='vendor'&&in_array($sort_by, array('vcode','vname'))){
                    $this->vendor_info = $vendor_info;
                    usort($table, array($this,"enhanced_sort_table"));
				}
				elseif($show_by=='branch'&&in_array($sort_by, array('bcode'))){
                    $this->branches = $branches;
                    usort($table, array($this,"enhanced_sort_table"));
				}elseif($sort_by=='cname'){
				    $this->category_info = $category_info;
				    $this->category_id = $category_id;
				    usort($table, array($this,"enhanced_sort_table"));
				}
			}
		}
		
		/*
		echo '<pre>';
		print_r($table);
		echo '</pre>';
		*/
		
        $smarty->assign('table',$table);
        $smarty->assign('total',$total);
        $smarty->assign('category_info',$category_info);
        $smarty->assign('vendor_info',$vendor_info);
        $smarty->assign('tree_lv',$_REQUEST['tree_lv']+1);
        $smarty->assign('report_header', join("&nbsp;&nbsp;&nbsp;&nbsp;",$report_header));
        $smarty->assign('got_opening_sc', $got_opening_sc);
        $smarty->assign('got_range_sc', $got_range_sc);
        
        if($branch_id>0) $smarty->assign('show_sku_img', 1);

        if($_REQUEST['ajax']){
            $smarty->assign("bgcolor",$_REQUEST['bgcolor']);
			$this->display('smarthq/report.stock_balance_summary.row.tpl');
		}else{
            $this->display('smarthq/report.stock_balance_summary.tpl');
		}
	}
	
	private function check_table($table)
	{
      /*global $con;
		if(!$con->sql_query("explain $table",false,false)){
			$sql_check="create table if not exists $table (
			sku_item_id int not null,
			from_date date,
			to_date date,
			qty double,
			cost double,
			avg_cost double,
			is_latest tinyint(1),
			index(sku_item_id),index(from_date),index(to_date),index(is_latest)
			)";
			$con->sql_query($sql_check) or die(mysql_error());
		}*/
	}

	private function normal_sort_table($a,$b)
	{
		$col = $this->sort_by;
		$order = $this->order_by;

	    if ($a[$col]==$b[$col]) return 0;
	    elseif($order=='desc') return ($a[$col]>$b[$col]) ? -1 : 1;
	    else return ($a[$col]>$b[$col]) ? 1 : -1;
	}
	
	
	private function enhanced_sort_table($a,$b)
	{
	    $key1 = $a['key'];
	    $key2 = $b['key'];
	    $order = $this->order_by;
	    
	    if($this->sort_by=='vcode'){
			$col1 = $this->vendor_info[$key1]['code'];
			$col2 = $this->vendor_info[$key2]['code'];
		}elseif($this->sort_by=='bcode'){
		    $col1 = $this->branches[$key1]['code'];
			$col2 = $this->branches[$key2]['code'];
		}elseif($this->sort_by=='vname'){
		    $col1 = $this->vendor_info[$key1]['description'];
			$col2 = $this->vendor_info[$key2]['description'];
		}elseif($this->sort_by=='cname'){
		    $col1 = $this->category_info[$key1]['description'];
			$col2 = $this->category_info[$key2]['description'];
			if($key1==$this->category_id) return ($order=='desc') ? 1 : -1;
			elseif($key2==$this->category_id) return ($order=='desc') ? -1: 1;
		}

	    if ($col1==$col2) return 0;
	    elseif($order=='desc') return ($col1>$col2) ? -1 : 1;
	    else return ($col1>$col2) ? 1 : -1;
	}
}

$con_multi = new mysql_multi();
$Stock_Balance_Summary = new Stock_Balance_Summary('Stock Balance Summary');
$con_multi->close_connection();
?>
