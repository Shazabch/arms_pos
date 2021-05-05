<?
/*
revision history
----------------

5/11/2007 6:08:20 PM - yinsee
- use cost and selling from vendor_sku_history when genereate PO
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO', BRANCH_CODE), "/index.php");
include("purchase_order.include.php");

$smarty->assign("PAGE_TITLE", "Purchase Order");

$branch_id = intval($_REQUEST['branch_id']);
if ($branch_id == 0) $branch_id = $sessioninfo['branch_id'];

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'ajax_load_sku':
			$smarty->assign("show_varieties",1);
			get_vendor_sku("purchase_order.from_vendor.show_sku.tpl");
			exit;
					    
		case 'ajax_expand_sku':
		    $smarty->assign("hideheader",1);
		    $smarty->assign("show_varieties",0);
		    expand_sku(intval($_REQUEST['sku_id']), "purchase_order.from_vendor.show_sku.tpl");
		    exit;
		    
		case 'ajax_generate_po':
			generate_po();
			exit;

		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}


$smarty->display("purchase_order.from_vendor.tpl");
exit;

function generate_po()
{
	global $con, $sessioninfo, $smarty, $branch_id;
	
	$counter = 0;
	$vid = $_REQUEST['vendor_id'];
	$deptid = $_REQUEST['department_id'];
	
	$po_array = array();
	$err = array();
	
	foreach (array_keys($_REQUEST['sel']) as $sid)
	{
		if ($counter % MAX_ITEMS_PER_PO == 0)
		{
			// create new PO header
			
			$con->sql_query("insert into po (branch_id, user_id, vendor_id, department_id, added) values ($branch_id, $sessioninfo[id], $vid, $deptid, CURRENT_TIMESTAMP)");
			$po_id = $con->sql_nextid();
			log_br($sessioninfo['id'], 'PURCHASE ORDER', $po_id, "Create PO from Vendor SKU (ID#$po_id)");
			$po_array[] = $po_id;
		}
		
		// add items
		if (BRANCH_CODE != 'HQ') $branch_check = "and branch_id = $branch_id";
		$con->sql_query("select vendor_sku_history.artno, mcode, vendor_sku_history.selling_price, vendor_sku_history.cost_price from vendor_sku_history left join sku_items on vendor_sku_history.sku_item_id = sku_items.id where vendor_id = $vid $branch_check and sku_item_id = $sid order by vendor_sku_history.added desc limit 1");
		$r = $con->sql_fetchrow();
		if ($r)
		{
			$item['po_id'] = $po_id;
			$item['branch_id'] = $branch_id;
			$item['user_id'] = $sessioninfo['id'];
			$item['po_sheet_id'] = 0;
			$item['sku_item_id'] = $sid;
			$item['artno_mcode'] = ($r['artno'] ? $r['artno'] : $r['mcode']);
			$item['selling_uom_fraction'] = 1;
			$item['order_uom_fraction'] = 1;
			$item['selling_price'] = $r['selling_price'];
			$item['order_price'] = $r['cost_price'];
			
			$con->sql_query("insert into po_items ".mysql_insert_by_field($item));
			$counter++;
		}
		else
		{
			$err[] = "<li> Error: Invalid SKU Item ($sid)";
		}
		
	}
	
	$smarty->assign("err", $err);
	$smarty->assign("po", $po_array);
	$smarty->display("purchase_order.from_vendor.result.tpl");
}
?>
