<?php
/*
3/26/2018 5:24 PM Justin
Bug fixed on report show empty result even have negative stock take adjust on closing stock.

4/25/2019 2:56 PM Andy
- Enhanced to can select all branch.

2/24/2020 10:29 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
$maintenance->check(119);

ini_set('memory_limit', '1024M');
set_time_limit(0);
ini_set("display_errors",1);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class Closing_Stock_Report extends Module{
	var $branches = array();
	var $branch_group_list = array();
	var $vendors = array();
	var $category_info = array();
	var $vendor_info = array();
	
	function __construct($title)
	{
		global $con, $smarty, $sessioninfo, $config, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		if (!$_REQUEST['date']) $_REQUEST['date'] = date('Y-m-d');
		
		//branches
		$con_multi->sql_query("select * from branch where active=1 order by sequence,code");
		while ($r = $con_multi->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branches', $this->branches);
		
		//vendors
		if ($sessioninfo['vendor_ids']) $fvendor = "and id in ({$sessioninfo[vendor_ids]})";
		$con_multi->sql_query("select id,description from vendor where active=1 $fvendor order by description");
		while ($r = $con_multi->sql_fetchassoc()){
			$this->vendors[$r['id']] =$r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('vendors', $this->vendors);
		
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
			'sb_to'=>'Closing Balance Qty',
			'sb_to_val'=>'Closing Balance Value'
		);
		$smarty->assign('sort_arr',$sort_arr);
		
		if($config['enable_gst']){
			$q1 = $con_multi->sql_query("select * from gst where active=1");

			while($r = $con_multi->sql_fetchassoc($q1)){
				if($r['type'] == "purchase"){
					$this->input_tax_list[$r['id']] = $r;
				}else{
					$this->output_tax_list[$r['id']] = $r;
				}
			}
			$con_multi->sql_freeresult($q1);

			$smarty->assign("input_tax_list", $this->input_tax_list);
			$smarty->assign("output_tax_list", $this->output_tax_list);
			
			$this->sql_join_gst = " left join gst input_gst on input_gst.id=if(if(si.input_tax<0,sku.mst_input_tax,si.input_tax)<0,cc.input_tax,if(si.input_tax<0,sku.mst_input_tax,si.input_tax))
									left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax)) ";
		}
		
		$this->init_data();
		
		$smarty->assign("form", $_REQUEST);
		parent::__construct($title);
	}
	
	function init_data(){
		global $con, $smarty, $appCore;
		
		$this->branch_group_list = $appCore->branchManager->getBranchGroupList();
		//print_r($this->branch_group_list);
		$smarty->assign("branch_group_list", $this->branch_group_list);
	}

	function _default()
	{
		$this->display('report.closing_stock.tpl');
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

		log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export $this->title To Excel");
		exit;
	}

	function show_report()
	{		
		global $smarty, $sessioninfo, $con_multi, $config;
		
		$err = array();
		
		$all_category = $_REQUEST['all_category'] ? true : false;
		$category_id = mi($_REQUEST['category_id']);
		if (!$all_category && !$category_id) $err[] = "Invalid Category";
		
		$this->show_by = $_REQUEST['show_by'];
		if($this->show_by != 'vendor' && $this->show_by != 'branch')	$this->show_by = 'cat';
		$ajax = $_REQUEST['ajax'] ? true : false;
		$sku_type = $_REQUEST['sku_type'];
		$blocked_po = trim($_REQUEST['blocked_po']);
		$status = trim($_REQUEST['status']);
		$vendor_id = mi($_REQUEST['vendor_id']);
		$tmp_bid = mi($_REQUEST['branch_id']);
		$sort_by = $_REQUEST['sort_by'];
		$order_by = $_REQUEST['order_by'];
		$use_grn = $_REQUEST['use_grn'] ? true : false;
		$hq_cost = $_REQUEST['hq_cost'] ? true : false;
		$input_tax_filter = mi($_REQUEST['input_tax_filter']);
		$output_tax_filter = mi($_REQUEST['output_tax_filter']);
		$date = $_REQUEST['date'];
		$grn_all_vendor = ($use_grn && !$vendor_id) ? true : false;
		$this->got_closing_sc = $_REQUEST['got_closing_sc'];
		
		if(BRANCH_CODE == 'HQ'){
			$bid_list = array();
			if($tmp_bid > 0){	// Selected Branch
				$bid_list[] = $tmp_bid;
				$branch_desc = 'Branch: '.$this->branches[$tmp_bid]['description'];
			}elseif($tmp_bid < 0){	// Branch Group
				$bgid = abs($tmp_bid);
				foreach($this->branch_group_list['group'][$bgid]['itemList'] as $bid => $r){
					$bid_list[] = $bid;
				}
				$branch_desc = 'Branch Group: '.$this->branch_group_list['group'][$bgid]['code'];
			}else{	// All
				foreach($this->branches as $bid => $r){
					$bid_list[] = $bid;
				}
				$branch_desc = 'Branch: All';
			}
		}else{
			$bid_list = array($sessioninfo['branch_id']);
			$branch_desc = 'Branch: '.BRANCH_CODE;
		}
		
		// Category
		if ($all_category)
		{
			$this->cat_level = 2;
			$this->group_cat_level = 1;
			//do it by department
			$this->plist = array();
			$sql = "select distinct p2 from category_cache cc
				join category c on cc.p2=c.id
				where c.id in ($sessioninfo[department_ids]) and c.active=1 ";
			$q1 = $con_multi->sql_query($sql);
			while ($r = $con_multi->sql_fetchassoc($q1)){
				$this->plist[] = mi($r['p2']);
			}
			$con_multi->sql_freeresult($q1);
		}
		else
		{
			$this->plist = array($category_id);
			$sql = "select level from category where id = $category_id";
			$q1 = $con_multi->sql_query($sql);
			$r = $con_multi->sql_fetchassoc($q1);
			$con_multi->sql_freeresult($q1);
			$this->cat_level = mi($r['level']);
			$this->group_cat_level = $this->cat_level + 1;
			
			// check max level
			$con_multi->sql_query("select max(level) from category");
			$max_cat_lv = mi($con_multi->sql_fetchfield(0));
			$con_multi->sql_freeresult();
			
			if($this->group_cat_level > $max_cat_lv)	$this->group_cat_level = $max_cat_lv;
		}

		// Got Error
		if ($err)
		{
			$smarty->assign('err',$err);
			$this->display('report.closing_stock.tpl');
			exit;
		}
		
		$this->table = array();
		foreach($bid_list as $bid){
			$this->generate_data($bid);
		}

		// Got Data - Need Sorting
		if($this->table)
		{
			$total = array();
			
			foreach($this->table as $thread => $r)
			{
				//clear empty data
				if (!$r['sb_to'] && !$r['sb_to_val'] && !$r['sc_adj_to']){
					unset($this->table[$thread]);
					continue;
				}
				
				$this->table[$thread]['key'] = $thread;
				if ($this->show_by == 'cat') $this->table[$thread]['tree_str'] = $this->category_info[$thread]['tree_str'];

				//only calculate total if no ajax call
				if(!$ajax){
					$total['sc_adj_to'] += $r['sc_adj_to'];
					$total['sb_to'] += $r['sb_to'];
					$total['sb_to_val'] += $r['sb_to_val'];
					$total['sb_to_selling'] += $r['sb_to_selling'];
					$total['sales_value_to'] += $r['sales_value_to'];
				}
			}
			
		}
		
		if ($sort_by && $this->table)
		{
			$this->sort_by = $sort_by;
			$this->order_by = $order_by;

			$normal_sort = array('sb_to','sb_to_val');
			if (in_array($sort_by, $normal_sort)) usort($this->table, array($this,"normal_sort_table"));
			else
			{
				if ($this->show_by == 'vendor' && in_array($sort_by, array('vcode','vname')))
				{
					usort($this->table, array($this,"enhanced_sort_table"));
				}
				elseif ($this->show_by == 'branch' && in_array($sort_by, array('bcode')))
				{
					usort($this->table, array($this,"enhanced_sort_table"));
				}
				elseif ($sort_by == 'cname')
				{
					$this->category_id = $category_id;
					usort($this->table, array($this,"enhanced_sort_table"));
				}
			}
		}
		
		$report_header = array();
		$report_header[] = $branch_desc;	// Branch
		$report_header[]= "Date: ".$date;
		
		if ($vendor_id)
		{
			$report_header[] = "Vendor: ".$this->vendors[$vendor_id]['description'];
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
		
		if ($this->show_by == 'cat') $report_header[] = "Show by: Category";
		if ($this->show_by == 'vendor') $report_header[] = "Show by: Vendor";
		if ($this->show_by == 'branch') $report_header[] = "Show by: Branch";

		if($input_tax_filter && $config['enable_gst']) $report_header[] = "Input Tax: ".$this->input_tax_list[$input_tax_filter]['code']." (".mi($this->input_tax_list[$input_tax_filter]['rate'])."%)";
		if($output_tax_filter && $config['enable_gst']) $report_header[] = "Output Tax: ".$this->output_tax_list[$output_tax_filter]['code']." (".mi($this->output_tax_list[$output_tax_filter]['rate'])."%)";
		
		
		//print_r($this->table);
		$smarty->assign('table', $this->table);
		
		$smarty->assign('total', $total);
		$smarty->assign('category_info', $this->category_info);
		$smarty->assign('vendor_info',$this->vendor_info);
		$smarty->assign('tree_lv', $_REQUEST['tree_lv']+1);
		$smarty->assign('report_header', join("&nbsp;&nbsp;&nbsp;&nbsp;",$report_header));
		$smarty->assign('got_closing_sc', $this->got_closing_sc);

		if (count($bid_list) == 1) $smarty->assign('show_sku_img', 1);

		if ($ajax)
		{
			$smarty->assign("bgcolor", $_REQUEST['bgcolor']);
			$this->display('report.closing_stock.row.tpl');
		}
		else{
			$this->display('report.closing_stock.tpl');
		}
	}
	
	private function generate_data($bid){
		global $smarty, $sessioninfo, $con_multi, $config;
		
		$filter = array();
		
		$all_category = $_REQUEST['all_category'] ? true : false;
		$category_id = mi($_REQUEST['category_id']);
		$show_by = $this->show_by;
		$sku_type = $_REQUEST['sku_type'];
		$blocked_po = trim($_REQUEST['blocked_po']);
		$status = trim($_REQUEST['status']);
		$vendor_id = mi($_REQUEST['vendor_id']);
		$sort_by = $_REQUEST['sort_by'];
		$order_by = $_REQUEST['order_by'];
		$use_grn = $_REQUEST['use_grn'] ? true : false;
		
		$hq_cost = $_REQUEST['hq_cost'] ? true : false;
		$input_tax_filter = mi($_REQUEST['input_tax_filter']);
		$output_tax_filter = mi($_REQUEST['output_tax_filter']);
		$grn_all_vendor = ($use_grn && !$vendor_id) ? true : false;
		$date = $_REQUEST['date'];
		$ldate = date('Y-m-d',(strtotime($date)+86400));
		
		//temp tables
		$tmp_sbs_sid = 'tmp_cs_sid';
		$tmp_grn_sid = 'tmp_cs_grn_sid';

		$sid_sb_info = array();

		if ($show_by == 'vendor')
		{
			if ($use_grn) $show_by_col = $vendor_id;
			else $show_by_col = 'sku.vendor_id';
		}
		else if ($show_by == 'branch'){
			$show_by_col = $bid;
		}
		elseif($show_by == 'cat')
		{
			$show_by_col = "cc.p$this->group_cat_level";
		}
		
		if ($vendor_id && !$use_grn) $filter[] = "sku.vendor_id = $vendor_id";
		if ($sku_type) $filter[] = "sku.sku_type = ".ms($sku_type);
		
		if ($blocked_po)
		{
			if ($blocked_po == 'yes') $filter[] = "si.block_list like ".ms("%i:$bid;s:2:\"on\";%");
			if ($blocked_po == 'no') $filter[] = "(si.block_list not like ".ms("%i:$bid;s:2:\"on\";%")." or si.block_list is null)";
		}
		
		if ($status != "all") $filter[] = "si.active = ".mi($status);
		
		if ($use_grn && $vendor_id) $filter[] = "si.id in (select sku_item_id from vendor_sku_history_b$bid where vendor_id = $vendor_id and (".ms($date)." between from_date and to_date))";
		
		$filter[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
		
		if($input_tax_filter && $config['enable_gst']) $filter[] = 'input_gst.id = '.mi($input_tax_filter);
		if($output_tax_filter && $config['enable_gst']) $filter[] = 'output_gst.id = '.mi($output_tax_filter);
		
		$str_filter = join(' and ',$filter);
		
		if ($grn_all_vendor)
		{
			$con_multi->sql_query_skip_logbin("drop table if exists $tmp_grn_sid");
			$con_multi->sql_query_skip_logbin("create temporary table $tmp_grn_sid (sid int, vid int, PRIMARY KEY (sid))");
			$con_multi->sql_query_skip_logbin("replace into $tmp_grn_sid (sid, vid) select sku_item_id, vendor_id from vendor_sku_history_b$bid where (".ms($date)." between from_date and to_date) order by from_date");
		}

		foreach ($this->plist as $cat_id) { //START ALL LOOP
			
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
			
				$sid_list_sql = "select si.id, si.sku_item_code, si.cost_price, si.hq_cost, if($show_by_col, $show_by_col, $category_id)
					from sku_items si 
					left join sku on si.sku_id = sku.id 
					left join category_cache cc on cc.category_id = sku.category_id 
					join $tmp_grn_sid t1 on si.id = t1.sid ".$this->sql_join_gst." 
					where cc.p".$this->cat_level." = $cat_id and $str_filter and t1.sid>0";
			}
			else
			{
				
				$sid_list_sql = "select si.id, si.sku_item_code, si.cost_price, si.hq_cost, if($show_by_col, $show_by_col, $category_id)
					from sku_items si 
					left join sku on si.sku_id = sku.id 
					left join category_cache cc on cc.category_id = sku.category_id ".$this->sql_join_gst." 
					where cc.p".$this->cat_level." = $cat_id and $str_filter";
			}
			
			$con_multi->sql_query_skip_logbin("insert into $tmp_sbs_sid (sid, code, cp, hqcp, show_by) $sid_list_sql");

			//==============================================================================================================================================

	  		if ($hq_cost) $extrasql = "tt.hqcp";
	  		else $extrasql = "ifnull(sb2.cost,tt.cp)";
			list($y,$dummy1,$dummy2) = explode("-",$date);
			$y = mi($y);
			$sql="select sb2.qty as sb_to, $extrasql as cost, tt.show_by, tt.sid,
				  ifnull((select siph.price 
				  from sku_items_price_history siph USE INDEX(bsa)
				  where siph.branch_id = " . mi($bid) . " and siph.sku_item_id = si.id and siph.added < " . ms($ldate) . "
				  order by siph.added desc limit 1),si.selling_price) as sales_price
				  from $tmp_sbs_sid tt 
				  left join stock_balance_b{$bid}_{$y} sb2 on tt.sid = sb2.sku_item_id and ('$date' between sb2.from_date and sb2.to_date)
				  left join sku_items si on tt.sid = si.id";

			$q1 = $con_multi->sql_query($sql);
			
			while ($r = $con_multi->sql_fetchassoc($q1)){
				$key = $r['show_by'];
				$sid = $r['sid'];
				
				$sid_sb_info[$sid]['closing_bal'] += ($r['sb_to'] * $r['cost']);
				$sid_sb_info[$sid]['closing_cost'] = $r['cost'];
				
				$this->table[$key]['sb_to'] += $r['sb_to'];
				$this->table[$key]['sb_to_val'] += ($r['sb_to'] * $r['cost']);
				$this->table[$key]['sales_value_to'] += ($r['sb_to'] * $r['sales_price']);
				$sid_sb_info[$sid]['closing_sales_value'] += ($r['sb_to'] * $r['sales_price']);
				$sid_sb_info[$sid]['sales_price'] = $r['sales_price'];
			}
			$con_multi->sql_freeresult($q1);
					
			
			//==============================================================================================================================================
			// search if next day got stock take then use it to replace the closing stock qty
			list($y,$dummy1,$dummy2) = explode("-",$date);
			$y = mi($y);
			$sub2 = $hq_cost ? 'tt.hqcp' : 'sc.cost';

			$sql="select tt.sid as sid, sum(sc.qty) as qty, sc.date, sb.qty as sb_qty, sum($sub2 * sc.qty) as cost, tt.show_by
			from stock_check sc 
			left join $tmp_sbs_sid tt on tt.code=sc.sku_item_code 
			left join stock_balance_b{$bid}_{$y} sb on tt.sid=sb.sku_item_id and '$date' between sb.from_date and sb.to_date 
			left join sku_items si on tt.sid = si.id
			where sc.branch_id=$bid and sc.date = '$ldate' and tt.sid 
			group by sid";

			$q1 = $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchassoc($q1))
			{
				$this->got_closing_sc = true;
				
				$sid = $r['sid'];
				$key = $r['show_by'];
				
				$this->table[$key]['sb_to_val'] -= $sid_sb_info[$sid]['closing_bal'];

				$sc_adj_qty = $r['qty'] - $r['sb_qty'];

				$this->table[$key]['sc_adj_to'] += $sc_adj_qty;
				$this->table[$key]['sb_to'] += $sc_adj_qty;

				$new_cost = 0;
				if ($r['cost'] > 0) $new_cost = $r['cost'];
				else $new_cost = $sid_sb_info[$sid]['closing_cost'] * $r['qty'];
				$this->table[$key]['sb_to_val'] += $new_cost;
				
				$this->table[$key]['sales_value_to'] -= $sid_sb_info[$sid]['closing_sales_value'];
				$this->table[$key]['sales_value_to'] += ($r['qty'] * $sid_sb_info[$sid]['sales_price']);
			}
			$con_multi->sql_freeresult($q1);
			
			
			//==============================================================================================================================================
			
			$sql = "select distinct tt.show_by from sku_items_cost sic left join $tmp_sbs_sid tt on sic.sku_item_id = tt.sid where sic.branch_id = $bid and tt.sid and sic.changed = 1";
			$q1 = $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchassoc($q1)){
				$key = $r['show_by'];
				$this->table[$key]['changed'] = true;
			}
			$con_multi->sql_freeresult($q1);
			
			//==============================================================================================================================================
			

			if ($show_by == 'vendor'){
				//build vendor data
				$sql = "select v.id, v.code, v.description from vendor v where v.id in (select distinct show_by from $tmp_sbs_sid)";
				$q1 = $con_multi->sql_query($sql);
				while($r = $con_multi->sql_fetchassoc($q1)){
					$vid = $r['id'];
					$this->vendor_info[$vid]['id'] = $vid;
					$this->vendor_info[$vid]['code'] = $r['code'];
					$this->vendor_info[$vid]['description'] = $r['description'];
				}
				$con_multi->sql_freeresult($q1);
			}elseif ($show_by == 'cat'){
				//build category data
				$cid_list = array(0);
				$sql = "select distinct show_by from $tmp_sbs_sid";
				$q1 = $con_multi->sql_query($sql);
				while($r = $con_multi->sql_fetchassoc($q1)) $cid_list[] = mi($r['show_by']);
				$con_multi->sql_freeresult($q1);
				$cid_list = join(',',$cid_list);
				
				$childcount_sql = 'select count(*) as c from category c2 where c2.root_id = c.id';
				$sql = "select c.id, c.description, c.tree_str, c.level, ($childcount_sql) as childcount from category c left join category_cache cc on c.id = cc.category_id where c.id in ($cid_list)";
				$q1 = $con_multi->sql_query($sql);
				while($r = $con_multi->sql_fetchassoc($q1)){
					$cid = $r['id'];
					$this->category_info[$cid]['id'] = $cid;
					$this->category_info[$cid]['description'] = $r['description'];
					$this->category_info[$cid]['level'] = $r['level'];
					$this->category_info[$cid]['tree_str'] = $r['tree_str'];
					$this->category_info[$cid]['got_child'] = $r['childcount'] ? true : false;
				}
				$con_multi->sql_freeresult($q1);
			}
			
		} // END ALL LOOP
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
$Closing_Stock_Report = new Closing_Stock_Report('Closing Stock Report');
//$con_multi->close_connection();
