<?php
/*
REVISION HISTORY
================
10/29/2007 4:10:24 PM gary
-add department and brand and branch filter.

11/1/2007 12:21:15 PM gary
- call artno from vendor_sku_history fro printing.

8/25/2010 3:59:45 PM Alex
- Fix block list and active sku_items for branch

11/16/2015 3:30 PM Qiu Ying
- Add sorting by sku arms code,mcode,artno, desription and department.
- Add export excel

5/22/2017 4:44 PM Justin
- Enhanced Load Vendor SKU to check master vendor if got config po_vendor_listing_enable_check_master_vendor.

11/9/2018 2:48 PM Andy
- Enhanced to have "Print Additional Week Column".
- Enhanced to can control print item per page for vendor sku list.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
//include("vendor_sku.include.php");

//$con = new sql_db("hq.aneka.com.my", "arms_slave", "arms_slave", "armshq");

$smarty->assign('PAGE_TITLE', 'Vendor SKU List');

// manager and above can see all department
if ($sessioninfo['level'] < 9999){
	if (!$sessioninfo['departments'])
		$depts = "id in (0)";
	else
		$depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
}
else{
	$depts = 1;
}

// show department option
$con->sql_query("select id, description from category where active and level=2 and $depts order by description");
$smarty->assign("dept", $con->sql_fetchrowset());


// manager and above can see all brand
if ($sessioninfo['level'] < 9999){
	if (!$sessioninfo['brands'])
		$brands = "id in (0)";
	else
		$brands = "id in (" . join(",", array_keys($sessioninfo['brands'])) . ")";
}
else{
	$brands = 1;
}

// show brand option
$con->sql_query("select id, description from brand where active and $brands order by description");

$smarty->assign("brand", $con->sql_fetchrowset());

if ($_REQUEST['a'])
{
	switch ($_REQUEST['a'])
	{
		case 'export':
			$items = get_sku();
			if (!$items)
			{
				print "<script>alert('No item to export');</script>";
				exit;
			}
			
			$smarty->assign("items", $items);
			include_once("include/excelwriter.php");
			log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Vendor SKU List To Excel");
			Header('Content-Type: application/msexcel');
			Header('Content-Disposition: attachment;filename=vendor'.time().'.xls');
			print ExcelWriter::GetHeader();
			$smarty->display("vendor_sku.print_list.list.tpl");
			exit;
		case 'list':
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			//exit;
			$items = get_sku();
			$smarty->assign("items", $items);
			$smarty->display("vendor_sku.print_list.list.tpl");
			exit;
		case 'print':
			$items = get_sku();
			if (!$items)
			{
				print "test<script>alert('No item to print');</script>";
				exit;
			}
			$con->sql_query("select * from branch where code = ".ms(BRANCH_CODE));
			$smarty->assign("branch", $con->sql_fetchrow());
			$con->sql_query("select description from vendor where id = {ms($_REQUEST[vendor_id])}");
			$v = $con->sql_fetchrow();
			$smarty->assign("title", "Vendor: $v[0]");
			$ITEMS_PER_PAGE = 20;
			if($config['po_vendor_sku_list_item_per_page']>0)	$ITEMS_PER_PAGE = $config['po_vendor_sku_list_item_per_page'];
			$totalpg = ceil(count($items)/$ITEMS_PER_PAGE);
			$smarty->assign("page_total", $totalpg);
			$pg = 1;
			$i = 0;
			$smarty->assign('week_col', $_REQUEST['week_col']);
			while($i<count($items))
			{
				$smarty->assign("items", array_slice($items,$i,$ITEMS_PER_PAGE));
				$smarty->assign("page_n", $pg);
				$smarty->display("vendor_sku.print_list.print.tpl");
				$smarty->assign("skip_header", 1);
				$i+=$ITEMS_PER_PAGE;
				$pg++;
			}
			exit;
		
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;				
	}
}

if ($sessioninfo['vendors']) $vd = "and id in (".join(",",array_keys($sessioninfo['vendors'])).")";
$con->sql_query("select id, description from vendor where active $vd order by description");
$smarty->assign("vendors", $con->sql_fetchrowset());
$smarty->display("vendor_sku.print_list.tpl");
exit;

function get_sku(){
	global $con, $smarty, $sessioninfo, $config;
	
	$is_export = ($_REQUEST['a'] == 'export') ? 1 : 0;
    $vendor_id = intval($_REQUEST['vendor_id']);
    $where='';
    if($_REQUEST['dept_id']){
    	$dept_id=intval($_REQUEST['dept_id']);
    	$where.=" and category.department_id=$dept_id ";
    }
    
    if($_REQUEST['brand_id']){
    	$brand_id=intval($_REQUEST['brand_id']);
    	$where.=" and brand.id=$brand_id";	
	}
	
	if(BRANCH_CODE!='HQ'){
    	$branch_check=" vendor_sku_history.branch_id=$sessioninfo[branch_id] and ";		
	}
	
	if($_REQUEST['sort_by']){
		$sort_by = "order by " . $_REQUEST['sort_by'];
	}
	
	if($_REQUEST['sort_by'] && $_REQUEST['sort_order']){
		$sort_order = $_REQUEST['sort_order'];
	}
	
	$q1 = $con->sql_query("select description from vendor where id = ".mi($vendor_id));
	$vendor_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);

	$q1 = $con->sql_query("select distinct sku_items.id, sku_item_code, link_code, sku_items.description, sku_items.mcode, sku.category_id, dept.description as department, brand.description as brand, sku_items.artno as artno, sku_items_cost.grn_cost as grn_cost, sku_items.block_list
from vendor_sku_history 
left join sku_items on vendor_sku_history.sku_item_id = sku_items.id 
left join sku on sku_id = sku.id 
left join sku_items_cost on vendor_sku_history.sku_item_id = sku_items_cost.sku_item_id and sku_items_cost.branch_id = ".mi($sessioninfo['branch_id'])."
left join category on sku.category_id = category.id
left join category dept on category.department_id = dept.id 
left join brand on brand.id=sku.brand_id
where $branch_check sku_items.active=1 and vendor_sku_history.vendor_id = ".mi($vendor_id)." $where
group by sku_items.id
$sort_by $sort_order");
//order by department, sku_item_code");

	while ($r=$con->sql_fetchassoc($q1)){

		$block_list=unserialize($r['block_list']);
		if (isset($block_list[$sessioninfo['branch_id']])) continue;

		$items[$r['id']]=$r;
	}
	$con->sql_freeresult($q1);
	
	if($config['po_vendor_listing_enable_check_master_vendor']){
		if($items){
			$str_exclude_idlist = "and si.id not in (".join(",",array_keys($items)).")";
		}
		
		$q1 = $con->sql_query("select si.id, si.sku_item_code, si.link_code, si.description, si.artno, si.mcode, si.block_list, sku.varieties, if(sic.grn_cost > 0, sic.grn_cost, si.cost_price) as grn_cost, dept.description as department, sku.category_id, brand.description as brand
		from sku_items si
		left join sku_items_price sip on sip.branch_id=".mi($sessioninfo['branch_id'])." and sip.sku_item_id=si.id
		left join sku_items_cost sic on sic.branch_id=".mi($sessioninfo['branch_id'])." and sic.sku_item_id=si.id
		left join sku on sku_id = sku.id 
		left join category on sku.category_id = category.id 
		left join category dept on category.department_id = dept.id 
		left join brand on brand.id=sku.brand_id
		where si.active=1 and sku.vendor_id = ".mi($vendor_id)." $where $str_exclude_idlist
		$sort_by $sort_order");
		
		while($r = $con->sql_fetchassoc($q1)){
			// skip if item is blocked
			$blocker = unserialize($r['block_list']);
			if (isset($blocker[$sessioninfo['branch_id']])) continue;

			$items[$r['id']] = $r;	
		}
		$con->sql_freeresult($q1);
	}
	
	$smarty->assign("is_export", $is_export);
	$smarty->assign("vendor_info", $vendor_info);
	
	return $items;
}

?>
