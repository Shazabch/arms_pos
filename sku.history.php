<?
/* this script is retired */
include("include/common.php");
require_once("include/gdgraph.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

$smarty->assign("PAGE_TITLE", "SKU History");
/*if (BRANCH_CODE == 'HQ'){
	$con->sql_query("select id, code from branch order by id");
	$smarty->assign("branch",$con->sql_fetchrowset());
}*/
$bid = get_request_branch();

if (isset($_REQUEST['sku_item_id']))
{
	$id = mi($_REQUEST['sku_item_id']);
	if (!isset($_REQUEST['sku_item_code']))
	{
		$con->sql_query("select sku_item_code, description from sku_items where id = $id");
		$r = $con->sql_fetchrow();
		$_REQUEST['sku_item_code'] = $r[0];
		$_REQUEST['sku'] = $r[1];
	}
	
	if (BRANCH_CODE == 'HQ' && ($bid <= 1))
	{
		// HQ mode, display balances from all branches
		
		$con->sql_query("select branch_id, branch.code as branch, sum(grn-gra-pos+adjust) as total, sum(grn) as grn, sum(gra) as gra, sum(pos) as pos, sum(adjust) as adjust from sku_items_inventory_history left join branch on branch_id = branch.id where sku_item_id = $id group by branch.code");
		$smarty->assign("branches_balance", $con->sql_fetchrowset());
	}
	else
	{
		
		$con->sql_query("select sum(grn-gra-pos+adjust) as total, sum(grn) as grn, sum(gra) as gra, sum(pos) as pos, sum(adjust) as adjust from sku_items_inventory_history where branch_id = $bid and sku_item_id = $id");
		$smarty->assign("balance", $con->sql_fetchrow());
		
		$con->sql_query("select * from sku_items_inventory_history where branch_id = $bid and sku_item_id = $id order by date");
		
		$tb = $con->sql_fetchrowset();
		$smarty->assign("history", $tb);
	}
}
elseif (isset($_REQUEST['sku_id']))
{
	$id = mi($_REQUEST['sku_id']);
	if (!isset($_REQUEST['sku_item_code']))
	{
		$con->sql_query("select sku_item_code, description from sku_items where sku_id = $id order by id limit 1");
		$r = $con->sql_fetchrow();
		$_REQUEST['sku_item_code'] = $r[0];
		$_REQUEST['sku'] = $r[1];
	}
	
	if (BRANCH_CODE == 'HQ' && ($bid <= 1))
	{
		// HQ mode, display balances from all branches
		
		$con->sql_query("select branch_id, branch.code as branch, sum(grn-gra-pos+adjust) as total, sum(grn) as grn, sum(gra) as gra, sum(pos) as pos, sum(adjust) as adjust from sku_items_inventory_history left join sku_items on sku_item_id = sku_items.id left join branch on branch_id = branch.id where sku_items.sku_id = $id group by branch.code");
		$smarty->assign("branches_balance", $con->sql_fetchrowset());
	}
	else
	{
		
		$con->sql_query("select sum(grn-gra-pos+adjust) as total, sum(grn) as grn, sum(gra) as gra, sum(pos) as pos, sum(adjust) as adjust from sku_items_inventory_history left join sku_items on sku_item_id = sku_items.id where branch_id = $bid and sku_items.sku_id = $id");
		$smarty->assign("balance", $con->sql_fetchrow());
		
		$con->sql_query("select * from sku_items_inventory_history left join sku_items on sku_item_id = sku_items.id where branch_id = $bid and sku_items.sku_id = $id order by date");
		
		$tb = $con->sql_fetchrowset();
		$smarty->assign("history", $tb);
	}
}

$smarty->display("sku.history.tpl");
exit;

?>
