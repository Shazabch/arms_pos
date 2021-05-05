<?php
/*
11/18/2014 10:22 AM Fithri
- Enhanced to allow user can use last GRN without select vendor.

12/9/2014 2:15 PM Andy
- Fix sku icon not show when show report by category.

5/18/2015 5:33 PM Justin
- Bug fixed on status filter will show wrong info on report title.

7/15/2015 10.59 AM Joo Chia
- DO cost value change to multiply quantity with do_items.cost

9/23/2015 11:23 AM DingRen
- when enable use_grn_future_allow_generate_gra do not deduct return ctn/pcs.

12/12/2016 4:54 PM Andy
- Fixed report error when select the last level of category.

8/9/2017 10:21 AM Qiu Ying
- Enhanced to add sales value at opening and closing balance

10/4/2017 4:40 PM Andy
- Force MySQL to use index bsa when query sku_items_price_history.

10/10/2017 3:10 PM Justin
- Enhanced to sum up closing balance from accumulated cost (GRA, GRN, Adjustment and etc) when config is turned on.

10/16/2017 3:26 PM Justin
- Enhanced to all columns that showing both qty and value become showing either Qty or Cost base on "Show by Qty" or "Show by Cost" button.

10/20/2017 4:46 PM Justin
- Bug fixed on adjustment out will sum up instead of deduct.

1/26/2018 11:05 AM Justin
- Bug fixed on opening sales values did not check against stock take qty.

3/7/2018 3:06 PM Justin
- Bug fixed on system will not showing anything even there was a Stock Take Adjust for certain SKU items.

3/12/2018 6:06 PM HockLee
- Added filter by Input Tax and Output Tax.
- Report title show up if enable_gst is on.

5/3/2018 10:24 AM Andy
- Added Foreign Currency feature.

7/5/2019 9:57 AM William
- Added new "Day Turnover" column to stock balance report.

7/10/2019 10:27 AM William
- Bug fixed "Error sql message" display when branch_id did not found.

2/21/2020 2:36 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

6/4/2020 1:14 PM William
- Change opening and closing cost use hq cost when filter by HQ cost.
- Bug fixed function get_cc sometime cannot get sku_items.cost_price.
*/
include("include/common.php");
$maintenance->check(119);

ini_set('memory_limit', '1024M');
set_time_limit(0);
ini_set("display_errors",1);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class Stock_Balance_Summary extends Module{

	function __construct($title)
	{
		global $con, $smarty, $sessioninfo, $config, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
		if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
		
		//branches
		$branches = array();
		$con_multi->sql_query("select * from branch where active=1 order by sequence,code");
		while ($r = $con_multi->sql_fetchassoc()) $branches[$r['id']] = $r;
		$con_multi->sql_freeresult();
		$smarty->assign('branches',$branches);
		$this->branches = $branches;
		
		//vendors
		$vendors = array();
		if ($sessioninfo['vendor_ids']) $fvendor = "and id in ({$sessioninfo[vendor_ids]})";
		$con_multi->sql_query("select id,description from vendor where active=1 $fvendor order by description");
		while ($r = $con_multi->sql_fetchassoc()) $vendors[$r['id']] =$r;
		$con_multi->sql_freeresult();
		$smarty->assign('vendors',$vendors);
		$this->vendors = $vendors;
		
		// sku type
		$con_multi->sql_query("select * from sku_type");
		$smarty->assign("sku_type", $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
		
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
		
		// get input and output tax code
		if($config['enable_gst']){
			$q1 = $con_multi->sql_query("select * from gst where active=1");
			while($r = $con_multi->sql_fetchassoc($q1)){
				if($r['type'] == "purchase"){
					$input_tax_list[$r['id']] = $r;
				}else{
					$output_tax_list[$r['id']] = $r;
				}
			}
			$con_multi->sql_freeresult($q1);
			$smarty->assign("input_tax_list", $input_tax_list);
			$smarty->assign("output_tax_list", $output_tax_list);
		}
		
		$smarty->assign("form", $_REQUEST);
		parent::__construct($title);
	}

	function _default()
	{
		$this->display('report.stock_balance_summary.tpl');
	}
	
	function output_excel()
	{
		global $smarty, $sessioninfo;

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

	function show_report()
	{
		$start_time = date("g:i a");
		
		global $smarty, $sessioninfo, $con_multi, $config;
		
		global $bid, $to, $sid_closing_cost, $hq_cost;
		
		$err = array();
		$filter = array(1);
		
		$all_category = $_REQUEST['all_category'] ? true : false;
		$category_id = mi($_REQUEST['category_id']);
		if (!$all_category && !$category_id) $err[] = "Invalid Category";
		
		if(!$_REQUEST['branch_id']){
			$_REQUEST['branch_id'] = mi($sessioninfo['branch_id']);
		}
		$show_by = $_REQUEST['show_by'];
		$ajax = $_REQUEST['ajax'] ? true : false;
		$sku_type = $_REQUEST['sku_type'];
		$blocked_po = trim($_REQUEST['blocked_po']);
		$status = trim($_REQUEST['status']);
		$vendor_id = mi($_REQUEST['vendor_id']);
		if ($vendor_id > 0) $show_by = 'cat';
		$bid = get_request_branch(true);
		$sort_by = $_REQUEST['sort_by'];
		$order_by = $_REQUEST['order_by'];
		$use_grn = $_REQUEST['use_grn'] ? true : false;
		$got_opening_sc = $_REQUEST['got_opening_sc'];
		$got_range_sc = $_REQUEST['got_range_sc'];
		$hq_cost = $_REQUEST['hq_cost'] ? true : false;
		$input_tax = mi($_REQUEST['input_tax']);
		$output_tax = mi($_REQUEST['output_tax']);

		$from = $_REQUEST['from'];
		$to = $_REQUEST['to'];
		
		$grn_all_vendor = ($use_grn && !$vendor_id) ? true : false;

		$total_sku = 0;
		$report_header = array();

		$efrom = date('Y-m-d',(strtotime($from)-86400));
		$lfrom = date('Y-m-d',(strtotime($from)+86400));
		$lto = date('Y-m-d',(strtotime($to)+86400));

		if ($all_category)
		{
			$cat_level = 2;
			$group_cat_level = 1;
			//do it by department
			$plist = array();
			$sql13 = "select distinct p2 from category_cache";
			$res13 = $con_multi->sql_query($sql13);
			while ($row13 = $con_multi->sql_fetchassoc($res13)) $plist[] = mi($row13['p2']);
			$con_multi->sql_freeresult($res13);
		}
		else
		{
			$plist = array($category_id);
			$sql14 = "select level from category where id = $category_id";
			$res14 = $con_multi->sql_query($sql14);
			$row14 = $con_multi->sql_fetchassoc($res14);
			$cat_level = mi($row14['level']);
			$con_multi->sql_freeresult($res14);
			$group_cat_level = $cat_level + 1;
			
			// check max level
			$con_multi->sql_query("select max(level) from category");
			$max_cat_lv = mi($con_multi->sql_fetchfield(0));
			$con_multi->sql_freeresult();
			
			if($group_cat_level > $max_cat_lv)	$group_cat_level = $max_cat_lv;
		}

		//if ($use_grn && !$vendor_id) $err[] = "Use GRN must have vendor selected";

		if ($show_by == 'vendor')
		{
			if ($use_grn) $show_by_col = $vendor_id;
			else $show_by_col = 'sku.vendor_id';
		}
		else if ($show_by == 'cat')
		{
			$show_by_col = "cc.p$group_cat_level";
		}
		else $err[] = "Invalid 'Show by' option";

		if ($err)
		{
			$smarty->assign('err',$err);
			$this->display('report.stock_balance_summary.tpl');
			exit;
		}

		//temp tables
		$tmp_sbs_sid = 'tmp_sbs_sid';
		$tmp_grn_sid = 'tmp_grn_sid';
		$sql = array();
		$cc_sid = array();

		$sid_key = array();
		$sid_start_cost = array();
		$sid_open_bal = array();
		$sid_closing_cost = array();

		$category_info = array();
		$vendor_info = array();
		$table = array();
		$sid_open_sales_info = array();

		if ($vendor_id && !$use_grn) $filter[] = "sku.vendor_id = $vendor_id";
		if ($sku_type) $filter[] = "sku.sku_type = '$sku_type'";
		
		if ($blocked_po)
		{
			if ($blocked_po == 'yes') $filter[] = "si.block_list like ".ms("%i:$bid;s:2:\"on\";%");
			if ($blocked_po == 'no') $filter[] = "(si.block_list not like ".ms("%i:$bid;s:2:\"on\";%")." or si.block_list is null)";
		}
		
		if ($status != "all") $filter[] = "si.active = ".mi($status);
		
		if ($use_grn && $vendor_id) $filter[] = "si.id in (select sku_item_id from vendor_sku_history_b$bid where vendor_id = $vendor_id and ('$from' between from_date and to_date or '$to' between from_date and to_date or from_date between '$from' and '$to'))";
		
		// filter by input tax
		if ($input_tax) $filter[] = "if(si.input_tax <= -1,if(sku.mst_input_tax <= -1,cc.input_tax,sku.mst_input_tax),si.input_tax) = $input_tax";
		
		// filter by output tax
		if ($output_tax) $filter[] = "if(si.output_tax <= -1,if(sku.mst_output_tax <= -1,cc.output_tax,sku.mst_output_tax),si.output_tax) = $output_tax";
		
		$filter[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
		
		$filter = join(' and ',$filter);
		
		if ($grn_all_vendor)
		{
			$con_multi->sql_query_skip_logbin("create temporary table $tmp_grn_sid (sid int, vid int, PRIMARY KEY (sid))");
			$con_multi->sql_query_skip_logbin("replace into $tmp_grn_sid (sid, vid) select sku_item_id, vendor_id from vendor_sku_history_b$bid where ('$from' between from_date and to_date or '$to' between from_date and to_date or from_date between '$from' and '$to') order by from_date");
			
			/*
			$sql19 = "select count(*) as c from $tmp_grn_sid";
			$res19 = $con_multi->sql_query($sql19);
			$row19 = $con_multi->sql_fetchassoc($res19);
			$con_multi->sql_freeresult($res19);
			print $row19['c'].' grn sid rows<br />';
			*/
		}

		foreach ($plist as $cat_id) { //START ALL LOOP
			
			//if select ALL department, filter by allowed department only
			if ($all_category && $sessioninfo['departments'])
			{
				if (!isset($sessioninfo['departments'][$cat_id])) continue;
			}

			//build lists of respective sku items
			$con_multi->sql_query_skip_logbin("drop table if exists $tmp_sbs_sid");
			$con_multi->sql_query_skip_logbin("create temporary table $tmp_sbs_sid (sid int, code char(12), cp double, hqcp double, show_by int, PRIMARY KEY (sid), INDEX(code), INDEX(show_by))");
			
			if ($grn_all_vendor)
			{
				if ($show_by == 'vendor') $show_by_col = 't1.vid';
				$sid_list_sql = "select si.id, si.sku_item_code, si.cost_price, si.hq_cost, if($show_by_col,$show_by_col,$category_id) from sku_items si left join sku on si.sku_id = sku.id left join category_cache cc on cc.category_id = sku.category_id left join $tmp_grn_sid t1 on si.id = t1.sid where cc.p$cat_level = $cat_id and $filter and t1.sid";
			}
			else
			{
				$sid_list_sql = "select si.id, si.sku_item_code, si.cost_price, si.hq_cost, if($show_by_col,$show_by_col,$category_id) from sku_items si left join sku on si.sku_id = sku.id left join category_cache cc on cc.category_id = sku.category_id where cc.p$cat_level = $cat_id and $filter";
			}
			
			$con_multi->sql_query_skip_logbin("insert into $tmp_sbs_sid (sid, code, cp, hqcp, show_by) $sid_list_sql");
			
			/*
			$sql20 = "select count(*) as c from $tmp_sbs_sid";
			$res20 = $con_multi->sql_query($sql20);
			$row20 = $con_multi->sql_fetchassoc($res20);
			$con_multi->sql_freeresult($res20);
			print $row20['c'].' sbs sid rows<br />';
			*/

			//==============================================================================================================================================
			if ($hq_cost) $start_cost ="si.hq_cost as start_cost";
			else   $start_cost= "ifnull(sb1.cost,tt.cp) as start_cost";
			
			list($y,$dummy1,$dummy2) = explode("-",$efrom);
			$y = mi($y);
			$sql1="select tt.sid, sb1.qty as sb_from, $start_cost, tt.show_by,
			ifnull((select siph.price 
			from sku_items_price_history siph USE INDEX(bsa)
			where siph.branch_id = " . mi($bid) . " and siph.sku_item_id = si.id and siph.added < " . ms($from) . "
			order by siph.added desc limit 1),si.selling_price) as sales_price
			from $tmp_sbs_sid tt 
			left join stock_balance_b{$bid}_{$y} sb1 on tt.sid = sb1.sku_item_id and ('$efrom' between sb1.from_date and sb1.to_date)
			left join sku_items si on tt.sid = si.id";
			$res1 = $con_multi->sql_query($sql1);
			while ($row1 = $con_multi->sql_fetchassoc($res1))
			{
				$total_sku++;
				$key = $row1['show_by'];
				$sid = $row1['sid'];
				
				$sid_start_cost[$sid] = $row1['start_cost'];
				$sid_open_bal[$sid] += $row1['sb_from'] * $row1['start_cost'];
				
				$table[$key]['sb_from'] += $row1['sb_from'];
				$table[$key]['sb_from_val'] += ($row1['sb_from'] * $row1['start_cost']);
				if($config['stock_balance_use_accumulate_last_cost']) $table[$key]['sb_to_val'] += ($row1['sb_from'] * $row1['start_cost']);
				$table[$key]['sales_value_from'] += ($row1['sb_from'] * $row1['sales_price']);
				$sid_open_sales_info[$sid]['open_sales_value'] += ($row1['sb_from'] * $row1['sales_price']);
				$sid_open_sales_info[$sid]['sales_price'] = $row1['sales_price'];
			}
			$con_multi->sql_freeresult($res1);

			//==============================================================================================================================================
			if ($_REQUEST['hq_cost']) $cost = "si.hq_cost as cost";
			else    $cost= "ifnull(sb2.cost,tt.cp) as cost";

			list($y,$dummy1,$dummy2) = explode("-",$to);
			$y = mi($y);
			$sql2="select sb2.qty as sb_to, $cost, tt.show_by,
			ifnull((select siph.price 
			from sku_items_price_history siph USE INDEX(bsa)
			where siph.branch_id = " . mi($bid) . " and siph.sku_item_id = si.id and siph.added < " . ms($lto) . "
			order by siph.added desc limit 1),si.selling_price) as sales_price
			from $tmp_sbs_sid tt 
			left join stock_balance_b{$bid}_{$y} sb2 on tt.sid = sb2.sku_item_id and ('$to' between sb2.from_date and sb2.to_date)
			left join sku_items si on tt.sid = si.id";
			$res2 = $con_multi->sql_query($sql2);
			while ($row2 = $con_multi->sql_fetchassoc($res2))
			{
				$key = $row2['show_by'];
				$sid = $row2['sid'];
				
				$sid_closing_cost[$sid] = $row2['cost'];
				
				$table[$key]['sb_to'] += $row2['sb_to'];
				if(!$config['stock_balance_use_accumulate_last_cost']) $table[$key]['sb_to_val'] += ($row2['sb_to'] * $row2['cost']);
				$table[$key]['sales_value_to'] += ($row2['sb_to'] * $row2['sales_price']);
			}
			$con_multi->sql_freeresult($res2);

			//==============================================================================================================================================

			$tmp_grp = $tmp_col = '';

			if ($use_grn)
			{
				$tmp_col = ", grn.vendor_id as grn_vendor_id ";
				$tmp_grp = ", grn_vendor_id ";
			}
			if(!$config['use_grn_future_allow_generate_gra']) $return_pcs=" - (ifnull(grn_items.return_ctn * rcv_uom.fraction,0) + ifnull(grn_items.return_pcs,0))";
			$sql3="select grn_items.sku_item_id as sid, sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty, sum((if(grn_items.acc_ctn is null and grn_items.acc_pcs is null, (grn_items.ctn * rcv_uom.fraction) + grn_items.pcs, (grn_items.acc_ctn * rcv_uom.fraction) + grn_items.acc_pcs)$return_pcs) * if(grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost) / rcv_uom.fraction * if(grr.currency_rate<0,1,grr.currency_rate)) as total_rcv_cost, tt.show_by $tmp_col 
			from grn_items 
			left join $tmp_sbs_sid tt on grn_items.sku_item_id=tt.sid 
			left join uom rcv_uom on grn_items.uom_id=rcv_uom.id 
			left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id 
			left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id 
			where grn.branch_id = $bid and rcv_date between '$from' and '$to' and tt.sid and grn.approved=1 and grn.status=1 and grn.active=1 and grr.active=1 
			group by sid $tmp_grp";
			//print $sql3;
			$res3 = $con_multi->sql_query($sql3);
			while($row3 = $con_multi->sql_fetchassoc($res3))
			{
				$key = $row3['show_by'];
				$sid = $row3['sid'];
				
				$table[$key]['grn'] += $row3['qty'];
				$table[$key]['grn_cost'] += $row3['total_rcv_cost'];
				if($config['stock_balance_use_accumulate_last_cost']) $table[$key]['sb_to_val'] += $row3['total_rcv_cost'];
				
				if($use_grn){
					if ($row3['grn_vendor_id'] == $vendor_id)
					{
						$table[$key]['grn_vendor_qty']+= $row3['qty'];
						$table[$key]['grn_vendor_cost']+= $row3['total_rcv_cost'];
					}
				}
			}
			$con_multi->sql_freeresult($res3);

			//==============================================================================================================================================

			$sql4="select gra_items.sku_item_id as sid, sum(qty) as qty, sum(qty * gra_items.cost * if(gra.currency_rate<0,1,gra.currency_rate)) as total_gra_cost, tt.show_by from gra_items left join $tmp_sbs_sid tt on gra_items.sku_item_id=tt.sid left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id where gra.branch_id=$bid and return_timestamp between '$from' and '$lto' and tt.sid and gra.status=0 and gra.returned=1 group by sid";
			$res4 = $con_multi->sql_query($sql4);
			while($row4 = $con_multi->sql_fetchassoc($res4))
			{
				$key = $row4['show_by'];
				
				$table[$key]['gra'] += $row4['qty'];
				$table[$key]['gra_cost'] += $row4['total_gra_cost'];
				if($config['stock_balance_use_accumulate_last_cost']) $table[$key]['sb_to_val'] -= $row4['total_gra_cost'];
			}
			$con_multi->sql_freeresult($res4);

			//==============================================================================================================================================

			$sql5="select pos.sku_item_id as sid, sum(qty) as qty,sum(pos.cost) as total_pos_cost, tt.show_by from sku_items_sales_cache_b{$bid} pos straight_join $tmp_sbs_sid tt on pos.sku_item_id=tt.sid where date between '$from' and '$to' and tt.sid group by sid";
			$res5 = $con_multi->sql_query($sql5);
			while($row5 = $con_multi->sql_fetchassoc($res5))
			{
				$key = $row5['show_by'];
				
				$table[$key]['pos'] += $row5['qty'];
				$table[$key]['pos_cost'] += $row5['total_pos_cost'];
				if($config['stock_balance_use_accumulate_last_cost']) $table[$key]['sb_to_val'] -= $row5['total_pos_cost'];
				
				if($table[$key]['pos_cost']){
					$table[$key]['total_sb_val'] = $table[$key]['sb_from_val'] + $table[$key]['sb_to_val'];
					
					if($table[$key]['total_sb_val'] != 0){
						$table[$key]['turnover_ratio'] = $table[$key]['pos_cost']/($table[$key]['total_sb_val']/2);
					}
					
					if($table[$key]['turnover_ratio'] && $table[$key]['turnover_ratio']!= 0){
						$table[$key]['turnover_days'] = 365/$table[$key]['turnover_ratio'];
					}			
				}
			}
			$con_multi->sql_freeresult($res5);

			//==============================================================================================================================================

			$sql6="select do_items.sku_item_id as sid, sum(do_items.ctn *uom.fraction + do_items.pcs) as qty, tt.show_by, do_items.cost from do_items left join $tmp_sbs_sid tt on do_items.sku_item_id=tt.sid left join uom on do_items.uom_id=uom.id left join do on do_id = do.id and do_items.branch_id = do.branch_id where do_items.branch_id=$bid and do_date between '$from' and '$to' and tt.sid and do.approved=1 and do.checkout=1 and do.status<2 group by sid";
			$res6 = $con_multi->sql_query($sql6);
			while($row6 = $con_multi->sql_fetchassoc($res6))
			{
				$sid = $row6['sid'];
				$key = $row6['show_by'];
				
				$table[$key]['do'] += $row6['qty'];
				//$table[$key]['do_cost'] += $row6['qty'] * $this->get_cc($sid);
				$table[$key]['do_cost'] += $row6['qty'] * $row6['cost'];
				if($config['stock_balance_use_accumulate_last_cost']) $table[$key]['sb_to_val'] -= ($row6['qty'] * $row6['cost']);
			}
			$con_multi->sql_freeresult($res6);

			//==============================================================================================================================================

			$sql7="select ai.sku_item_id as sid, sum(qty) as qty, sum(ai.qty * ai.cost) as item_cost, if(qty>=0,'p','n') as type, tt.show_by from adjustment_items ai left join $tmp_sbs_sid tt on ai.sku_item_id=tt.sid left join adjustment adj on adjustment_id = adj.id and ai.branch_id = adj.branch_id where ai.branch_id =$bid and adjustment_date between '$from' and '$to' and tt.sid and adj.approved=1 and adj.status<2 group by type, sid";
			$res7 = $con_multi->sql_query($sql7);
			while($row7 = $con_multi->sql_fetchassoc($res7))
			{
				$key = $row7['show_by'];
				
				if ($row7['type'] == 'p')
				{
					$table[$key]['adj_in'] += $row7['qty'];
					$table[$key]['adj_in_cost'] += $row7['item_cost'];
				}
				elseif ($row7['type'] == 'n')
				{
					$table[$key]['adj_out'] += abs($row7['qty']);
					$table[$key]['adj_out_cost'] += abs($row7['item_cost']);
				}
				if($config['stock_balance_use_accumulate_last_cost']) $table[$key]['sb_to_val'] += $row7['item_cost'];
			}
			$con_multi->sql_freeresult($res7);

			//==============================================================================================================================================

			list($y,$dummy1,$dummy2) = explode("-",$efrom);
			$y = mi($y);
			$sub2 = $hq_cost ? 'tt.hqcp' : 'sc.cost';

			$sql8="select tt.sid as sid, sum(sc.qty) as qty, sc.date, sb.qty as sb_qty, sum($sub2 * sc.qty) as cost, tt.show_by
			from stock_check sc 
			left join $tmp_sbs_sid tt on tt.code=sc.sku_item_code 
			left join stock_balance_b{$bid}_{$y} sb on tt.sid=sb.sku_item_id and '$efrom' between sb.from_date and sb.to_date 
			left join sku_items si on tt.sid = si.id
			where sc.branch_id=$bid and sc.date = '$from' and tt.sid 
			group by sid";
			$res8 = $con_multi->sql_query($sql8);
			while($row8 = $con_multi->sql_fetchassoc($res8))
			{
				$got_opening_sc = true;
				
				$sid = $row8['sid'];
				$key = $row8['show_by'];
				
				$table[$key]['sb_from_val'] -= $sid_open_bal[$sid];

				$sc_adj_qty = $row8['qty'] - $row8['sb_qty'];

				$table[$key]['sc_adj_from'] += $sc_adj_qty;
				$table[$key]['sb_from'] += $sc_adj_qty;

				$new_cost = 0;
				if ($row8['cost'] > 0) $new_cost = $row8['cost'];
				else $new_cost = $sid_start_cost[$sid] * $row8['qty'];
				$table[$key]['sb_from_val'] += $new_cost;
				if($config['stock_balance_use_accumulate_last_cost']){
					$table[$key]['sb_to_val'] -= $sid_open_bal[$sid];
					$table[$key]['sb_to_val'] += $new_cost;
				}

				$table[$key]['sales_value_from'] -= $sid_open_sales_info[$sid]['open_sales_value'];
				$table[$key]['sales_value_from'] += ($row8['qty'] * $sid_open_sales_info[$sid]['sales_price']);
			}
			$con_multi->sql_freeresult($res8);

			//==============================================================================================================================================

			$sc_balance_val = array();

			$sql9="select tt.sid as sid, sum($sub2 * sc.qty) as cost, sc.date, sum(sc.qty) as qty, tt.show_by from stock_check sc left join $tmp_sbs_sid tt on sc.sku_item_code=tt.code where sc.branch_id=$bid and sc.date between '$lfrom' and '$to' and tt.sid group by sc.date, sid";
			$res9 = $con_multi->sql_query($sql9);
			while($row9 = $con_multi->sql_fetchassoc($res9))
			{
				$got_range_sc = true;
				
				$sid = $row9['sid'];
				$key = $row9['show_by'];
				
				$sc_balance_val[$row9['date']][$sid]['qty'] += $row9['qty'];
				$sc_balance_val[$row9['date']][$sid]['cost'] += $row9['cost'];
				
			}
			$con_multi->sql_freeresult($res9);

			if($sc_balance_val)
			{
				foreach ($sc_balance_val as $sc_date => $tmp_balance_val_list)
				{
					$minus_1_day = strtotime($sc_date) - 86400;
					$sb_year = date("Y", $minus_1_day);
					$sc1day_date = date('Y-m-d',$minus_1_day);
					
					$sb_tbl = "stock_balance_b$bid"."_".$sb_year;
				
					$sql10 = "select tt.sid as sid, sc.qty as qty, tt.show_by from $tmp_sbs_sid tt left join $sb_tbl sc on tt.sid=sc.sku_item_id and '$sc1day_date' between sc.from_date and sc.to_date where tt.sid in (".join(",", array_keys($tmp_balance_val_list)).") group by sid";
					$res10 = $con_multi->sql_query($sql10);
					while($row10 = $con_multi->sql_fetchassoc($res10))
					{
						$sid = $row10['sid'];
						$key = $row10['show_by'];
						
						if ($sc_balance_val[$sc_date][$sid]['cost'])
							$unit_cost = $sc_balance_val[$sc_date][$sid]['cost']/$sc_balance_val[$sc_date][$sid]['qty'];
						else
							$unit_cost = $this->get_cc($sid);
						
						$sc_qty = $sc_balance_val[$sc_date][$sid]['qty'];
						$qty_b4_sc = $row10['qty'];
						
						$adj_qty = $sc_qty - $qty_b4_sc;
						$adj_cost = $unit_cost * $adj_qty;
						
						$table[$key]['sc_adj'] += $adj_qty;
						$table[$key]['sc_adj_cost'] += $adj_cost;
						$table[$key]['got_sc'] = true;
						if($config['stock_balance_use_accumulate_last_cost']) $table[$key]['sb_to_val'] += $adj_cost;
					}
					$con_multi->sql_freeresult($res10);
				}
				unset($sc_balance_val);
			}
			
			//==============================================================================================================================================
			
			if ($config['consignment_modules'])
			{
				$sql16 = "select tt.show_by, cni.sku_item_id as sid, sum(cni.ctn *uom.fraction + cni.pcs) as qty, if(cn.date>='$from',0,1) as bal,(cn.date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = cni.sku_item_id and sh.branch_id=$bid and sh.date <'$from')) as dont_count from cn_items cni left join $tmp_sbs_sid tt on cni.sku_item_id = tt.sid left join uom on cni.uom_id=uom.id left join cn on cni.cn_id = cn.id and cni.branch_id = cn.branch_id where cn.to_branch_id=$bid and cn.date <='$to' and tt.sid and cn.active=1 and cn.status=1 and cn.approved=1 group by bal,dont_count, sid order by null";
				$res16 = $con_multi->sql_query($sql16);

				while($row16 = $con_multi->sql_fetchassoc($res16))
				{
					if (!$row16['dont_count'])
					{
						$sid = $row16['sid'];
						$key = $row16['show_by'];

						$qty = $row16['qty'];
						if (!$row16['bal'])
						{
							$table[$key]['cn_qty']+=$qty;
							$table[$key]['cn_val']+=$qty * $this->get_cc($sid);
							if($config['stock_balance_use_accumulate_last_cost']) $table[$key]['sb_to_val'] += $qty * $this->get_cc($sid);
						}
					}
				}
				$con_multi->sql_freeresult($res16);
				
				$sql17 = "select tt.show_by, cni.sku_item_id as sid, sum(cni.ctn *uom.fraction + cni.pcs) as qty, if(dn.date>='$from',0,1) as bal, (dn.date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = cni.sku_item_id and sh.branch_id=$bid and sh.date <'$from')) as dont_count from dn_items cni left join $tmp_sbs_sid tt on cni.sku_item_id = tt.sid left join uom on cni.uom_id=uom.id left join dn on cni.dn_id = dn.id and cni.branch_id = dn.branch_id where dn.to_branch_id=$bid and dn.date <='$to' and tt.sid and dn.active=1 and dn.status=1 and dn.approved=1 group by bal,dont_count, sid order by null";
				$res17 = $con_multi->sql_query($sql17);
				
				while($row17 = $con_multi->sql_fetchassoc($res17))
				{
					if (!$row17['dont_count'])
					{
						$sid = $row17['sid'];
						$key = $row17['show_by'];

						$qty=$row17['qty'];
						if (!$row17['bal'])
						{
							$table[$key]['dn_qty']+=$qty;
							$table[$key]['dn_val']+=$qty * $this->get_cc($sid);
							if($config['stock_balance_use_accumulate_last_cost']) $table[$key]['sb_to_val'] -= $qty * $this->get_cc($sid);
						}
					}
				}
				$con_multi->sql_freeresult($res17);
			}
			
			//==============================================================================================================================================
			
			$sql18 = "select distinct tt.show_by from sku_items_cost sic left join $tmp_sbs_sid tt on sic.sku_item_id = tt.sid where sic.branch_id = $bid and tt.sid and sic.changed = 1";
			$res18 = $con_multi->sql_query($sql18);
			while($row18 = $con_multi->sql_fetchassoc($res18))
			{
				$key = $row18['show_by'];
				$table[$key]['changed'] = true;
			}
			$con_multi->sql_freeresult($res18);
			
			//==============================================================================================================================================

			if ($show_by == 'cat')
			{
				//build category data
				$cid_list = array(0);
				$sql15 = "select distinct show_by from $tmp_sbs_sid";
				$res15 = $con_multi->sql_query($sql15);
				while($row15 = $con_multi->sql_fetchassoc($res15)) $cid_list[] = mi($row15['show_by']);
				$con_multi->sql_freeresult($res15);
				$cid_list = join(',',$cid_list);
				
				$childcount_sql = 'select count(*) as c from category c2 where c2.root_id = c.id';
				$sql11 = "select c.id, c.description, c.tree_str, c.level, ($childcount_sql) as childcount from category c left join category_cache cc on c.id = cc.category_id where c.id in ($cid_list)";
				$res11 = $con_multi->sql_query($sql11);
				while($row11 = $con_multi->sql_fetchassoc($res11))
				{
					$cid = $row11['id'];
					$category_info[$cid]['id'] = $cid;
					$category_info[$cid]['description'] = $row11['description'];
					$category_info[$cid]['level'] = $row11['level'];
					$category_info[$cid]['tree_str'] = $row11['tree_str'];
					$category_info[$cid]['got_child'] = $row11['childcount'] ? true : false;
				}
				$con_multi->sql_freeresult($res11);
			}

			if ($show_by == 'vendor')
			{
				//build vendor data
				$sql11 = "select v.id, v.code, v.description from vendor v where v.id in (select distinct show_by from $tmp_sbs_sid)";
				$res11 = $con_multi->sql_query($sql11);
				while($row11 = $con_multi->sql_fetchassoc($res11))
				{
					$vid = $row11['id'];
					$vendor_info[$vid]['id'] = $vid;
					$vendor_info[$vid]['code'] = $row11['code'];
					$vendor_info[$vid]['description'] = $row11['description'];
				}
				$con_multi->sql_freeresult($res11);
			}
			
		} // END ALL LOOP
		
		//print "$sid_list_sql<br /><br />";

		//==============================================================================================================================================

		if($table)
		{
			$total = array();
			
			foreach($table as $thread => $r)
			{
				//clear empty data
				if (!$r['sb_from'] && !$r['sb_to']&& !$r['sb_from_val'] && !$r['sb_to_val'] && !$r['grn_cost'] && !$r['gra_cost'] && !$r['pos_cost'] && !$r['do_cost'] && !$r['adj_in'] && !$r['adj_out'] && !$r['sc_adj_from'])
				{
					unset($table[$thread]);
					continue;
				}
				
				if ($r['sb_from'] < 0 && $r['sb_from'] > -0.01) $table[$thread]['sb_from'] = $r['sb_from'] = 0;
				if ($r['sb_from_val'] < 0 && $r['sb_from_val'] > -0.01) $table[$thread]['sb_from_val'] = $r['sb_from_val'] = 0;
				
				$table[$thread]['key'] = $thread;
				if ($show_by == 'cat') $table[$thread]['tree_str'] = $category_info[$thread]['tree_str'];

				//only calculate total if no ajax call
				if (!$ajax)
				{
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
					$total['sales_value_from'] += $r['sales_value_from'];
					$total['sales_value_to'] += $r['sales_value_to'];
					
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
				if($total['pos_cost']){
					$total['total_sb_val'] = $total['sb_to_val'] + $total['sb_from_val'];
					if($total['total_sb_val'] != 0){
						$total['turnover_ratio'] = $total['pos_cost']/($total['total_sb_val']/2);
					}
					if($total['turnover_ratio'] && $total['turnover_ratio'] != 0){
						$total['turnover_days'] = 365/$total['turnover_ratio'];
					}
				}
			}
			
		}
		
		if ($sort_by && $table)
		{
			$this->sort_by = $sort_by;
			$this->order_by = $order_by;

			$normal_sort = array('sb_from','sb_from_val','sb_to','sb_to_val');
			if (in_array($sort_by, $normal_sort)) usort($table, array($this,"normal_sort_table"));
			else
			{
				if ($show_by == 'vendor' && in_array($sort_by, array('vcode','vname')))
				{
					$this->vendor_info = $vendor_info;
					usort($table, array($this,"enhanced_sort_table"));
				}
				elseif ($show_by == 'branch' && in_array($sort_by, array('bcode')))
				{
					$branches = $this->branches;
					usort($table, array($this,"enhanced_sort_table"));
				}
				elseif ($sort_by == 'cname')
				{
					$this->category_info = $category_info;
					$this->category_id = $category_id;
					usort($table, array($this,"enhanced_sort_table"));
				}
			}
		}
		
		$branches = $this->branches;
		$report_header[] = "Branch: ".$branches[$bid]['code'];
		$report_header []= "Date from $from to $to";
		
		if ($vendor_id)
		{
			$vendors = $this->vendors;
			$report_header[] = "Vendor: ".$vendors[$vendor_id]['description'];
		}
		else $report_header[] = "Vendor: All";
		
		if ($sku_type) $report_header[] = "SKU Type: $sku_type";
		else $report_header[] = "SKU Type: All";
		
		if ($blocked_po) $report_header[] = "Blocked Item in PO: ".ucwords($blocked_po);
		
		if($status == "all") $statusl = ucwords($status);
		elseif ($status == 1) $statusl = "Active";
		else $statusl = "Inactive";
		$report_header[] = "Status: ".$statusl;
		
		if ($all_category) $report_header[] = "Category: All";
		else $report_header[] = "Category: ".$_REQUEST['category'];
		
		if ($show_by == 'cat') $report_header[] = "Show by: Category";
		if ($show_by == 'vendor') $report_header[] = "Show by: Vendor";
		
		if($config['enable_gst']){
			if (!$input_tax) {
				$report_header[] = "Input Tax: All";
			} else {
				$inpt_tax = get_gst_settings($input_tax);		
				if ($inpt_tax['active'] == 1) $report_header[] = "Input Tax: ".$inpt_tax['code']." (".$inpt_tax['rate']."%)";
			}
			
			if (!$output_tax) {
				$report_header[] = "Output Tax: All";
			} else {
				$outpt_tax = get_gst_settings($output_tax);
				if ($outpt_tax['active'] == 1) $report_header[] = "Output Tax: ".$outpt_tax['code']." (".$outpt_tax['rate']."%)";
			}
		}

		$smarty->assign('table',$table);
		
		/*
		print '<pre>';
		print_r($table);
		print '</pre>';
		*/
		
		$smarty->assign('total',$total);
		$smarty->assign('category_info',$category_info);
		$smarty->assign('vendor_info',$vendor_info);
		$smarty->assign('tree_lv',$_REQUEST['tree_lv']+1);
		$smarty->assign('report_header', join("&nbsp;&nbsp;&nbsp;&nbsp;",$report_header));
		$smarty->assign('got_opening_sc', $got_opening_sc);
		$smarty->assign('got_range_sc', $got_range_sc);

		if ($bid > 0) $smarty->assign('show_sku_img', 1);

		if ($ajax)
		{
			$smarty->assign("bgcolor",$_REQUEST['bgcolor']);
			$this->display('report.stock_balance_summary.row.tpl');
		}
		else $this->display('report.stock_balance_summary.tpl');
	
		/*
		print "total of $total_sku SKU processed<br />";
		$end_time = date("g:i a");
		print "start at : $start_time<br />";
		print "end at : $end_time<br />";
		$this->echomem('final memory');
		*/
	}

	function get_cc($sid)
	{
		global $con_multi, $bid, $to, $sid_closing_cost, $hq_cost;
		
		$sid = mi($sid);
		
		if ($hq_cost) $sub1 = 'ifnull(si.hq_cost,si.cost_price)';
		else $sub1 = 'ifnull(sich.grn_cost,si.cost_price)';
		
		$con_multi->sql_query("select max(date) as stock_date from sku_items_cost_history sh where sh.sku_item_id = $sid and sh.branch_id = $bid and sh.date <= '$to'");
		$row1 = $con_multi->sql_fetchrowset();
		$con_multi->sql_freeresult();
		
		if($row1['stock_date']) $filter = "and sich.date=".ms($row1['stock_date']);
		$sql12 = "select sich.date, si.id as sid, $sub1 as grn_cost from sku_items si 
		left join sku_items_cost_history sich on sich.sku_item_id = si.id and sich.branch_id = $bid and sich.date <= '$to' and sich.date > 0 $filter
		where si.id = $sid order by null"; 
		$res12 = $con_multi->sql_query($sql12);
		while($row12 = $con_multi->sql_fetchassoc($res12))
		{
			$sid = $row12['sid'];
			$v = $row12['grn_cost'];
			$sid_closing_cost[$sid] = $v;
		}
		$con_multi->sql_freeresult($res12);
		
		return $v;
	}

	function echomem($rem)
	{
		print 'memory : '.round(memory_get_usage()/1048576,2)." MB - ($rem)<br />";
	}
	
	private function normal_sort_table($a,$b)
	{
		$col = $this->sort_by;
		$order = $this->order_by;

		if ($a[$col] == $b[$col]) return 0;
		elseif ($order=='desc') return ($a[$col]>$b[$col]) ? -1 : 1;
		else return ($a[$col]>$b[$col]) ? 1 : -1;
	}
	
	private function enhanced_sort_table($a,$b)
	{
		$key1 = $a['key'];
		$key2 = $b['key'];
		$order = $this->order_by;

		if($this->sort_by=='vcode')
		{
			$col1 = $this->vendor_info[$key1]['code'];
			$col2 = $this->vendor_info[$key2]['code'];
		}
		elseif($this->sort_by=='bcode')
		{
			$col1 = $this->branches[$key1]['code'];
			$col2 = $this->branches[$key2]['code'];
		}
		elseif($this->sort_by=='vname')
		{
			$col1 = $this->vendor_info[$key1]['description'];
			$col2 = $this->vendor_info[$key2]['description'];
		}
		elseif($this->sort_by=='cname')
		{
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

//$con_multi = new mysql_multi();
$Stock_Balance_Summary = new Stock_Balance_Summary('Stock Balance Summary');
//$con_multi->close_connection();
