<?php
/*
Revision History
================
4/20/07 3:31:00 PM   yinsee
- remove sku_vendor_history where HQ using GURUN's data

4/20/07 4:32:22 PM yinsee
- change to only load from whatever is in vendor_sku_history
- show selling and cost from vendor_sku_history

10/10/2007 11:44:57 AM gary
- changing the query to get_vendor_sku().

3/5/2008 12:07:54 PM gary
- change the old po link to new po link.

3/7/2008 4:44:37 PM yinsee
- skip blocked item

12/24/2008 1:14:27 PM yinsee
- get current selling price instead of vendor_sku_history

12/26/2008 4:55:14 PM yinsee
- sku from vendor ==> remove branch checking when HQ

7/20/2010 3:39:10 PM Alex
- Add sku_items block and active for branch

8/25/2010 3:59:45 PM Alex
- Fix block list and active sku_items for branch

4/20/2012 5:45:00 PM Alex
- add packing uom code => get_vendor_po_request_sku()

2/10/2017 5:10 PM Andy
- Enhanced Load Vendor SKU to check master vendor if got config po_vendor_listing_enable_check_master_vendor.

6/22/2017 15:00 Qiu Ying
- Enhanced to select multiple department in vendor PO access

8/18/2017 10:47 AM Andy
- Fixed get_vendor_sku() cannot get latest cost.

9/14/2018 4:56 PM Andy
- Fixed selling price always show as 1.00 if no vendor_sku_history.
*/

/*
SQL for first time copying SKU to vendor_sku_history
----------------------------------------------------
insert into vendor_sku_history (branch_id,vendor_id,sku_item_id,selling_price,cost_price) select 1,vendor_id,sku_items.id,selling_price,cost_price from sku_items left join sku on sku_id = sku.id

*/
// return list of vendor's SKU with checkbox infront
function get_vendor_po_request_sku($tpl = "vendor_po_request.show_sku.tpl")
{
	global $con, $smarty, $sessioninfo, $branch_id, $config;
//	print_r($_REQUEST);
    $vendor_id = intval($_REQUEST['vendor_id']);
	
	if($_REQUEST['department_ids']){
		$dept_id = implode(",", array_keys($_REQUEST['department_ids']));
	}
	
	$bid = $sessioninfo['branch_id'];
	
	// for testing purpose, open the 2nd option
	$check = "vendor_sku_history.vendor_id = $vendor_id and category.department_id in ($dept_id)";
	if (BRANCH_CODE != 'HQ') $check .= " and vendor_sku_history.branch_id = $bid";
	//$branch_check = "vendor_sku_history.branch_id = $bid and ";
	
	// sort descending by history added - to get the latest price info
	$sql ="select si.sku_id, si.sku_item_code, si.id, si.description,si.block_list, si.artno, si.mcode, sku.varieties, ifnull(sip.price,vendor_sku_history.selling_price) as selling_price, vendor_sku_history.cost_price, dept.description as dept_name, category.description as cat_name, uom.code as packing_uom_code
		from vendor_sku_history
		left join sku_items si on vendor_sku_history.sku_item_id = si.id
		left join sku_items_price sip on sip.sku_item_id=si.id and sip.branch_id=$branch_id
		left join sku on sku_id = sku.id and si.sku_id= sku.id
		left join category on sku.category_id = category.id
		left join category dept on dept.id=category.department_id
		left join uom on uom.id=si.packing_uom_id
		where $check and si.active=1 order by si.id, vendor_sku_history.added desc, si.description, si.artno, si.mcode";

	$q2 = $con->sql_query($sql);

	$items = array();
	while ($r2=$con->sql_fetchrow($q2))
	{
	    if (isset($items[$r2['id']])) continue;
		$blocker=unserialize($r2['block_list']);
		
		if (isset($blocker[$bid])) continue;

		$items[$r2['id']] = $r2;
	}
	$con->sql_freeresult($q2);
	
	if($config['po_vendor_listing_enable_check_master_vendor']){
		if($items){
			$str_exclude_idlist = "and si.id not in (".join(",",array_keys($items)).")";
		}
		
		$q1 = $con->sql_query("select si.sku_id, si.sku_item_code, si.block_list, si.id, si.description, si.artno, si.mcode, sku.varieties, ifnull(sip.price, si.selling_price) as selling_price, sic.grn_cost as cost_price
		from sku_items si
		left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
		left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
		left join sku on sku_id = sku.id 
		left join category on sku.category_id = category.id 
		where category.department_id in ($dept_id) $str_exclude_idlist and si.active=1 and sku.vendor_id=$vendor_id
		order by si.id, si.description, si.artno, si.mcode");
		
		while($r = $con->sql_fetchassoc($q1)){
			// skip if item is blocked
			$blocker = unserialize($r['block_list']);
			if (isset($blocker[$branch_id])) continue;

			$items[$r['id']] = $r;	
		}
		$con->sql_freeresult($q1);
	}
	
	$form['vendor_id']=$vendor_id;
	$form['vendor']=$_REQUEST['vendor'];
	$smarty->assign("items", $items);
	$smarty->assign("form", $form);
	$smarty->display($tpl);
}


// return list of vendor's SKU with checkbox infront
//$tpl = "purchase_order.new.show_sku.tpl"
function get_vendor_sku($tpl = "purchase_order.new.show_sku.tpl"){
	global $con, $smarty, $sessioninfo, $branch_id, $config;
	
    $vendor_id = intval($_REQUEST['vendor_id']);
	$dept_id = intval($_REQUEST['department_id']);
	$str_exclude_idlist = '';
	$exclude_idlist = array();
	
	if (isset($_REQUEST['id'])){
		$poid = intval($_REQUEST['id']);

		//added by gary - option for new po.
		if($tpl=="po.new.show_sku.tpl"){
			$con->sql_query("select sku_item_id from tmp_po_items where branch_id = $branch_id and po_id = $poid and user_id = $sessioninfo[id]");
		}
		else{
			$con->sql_query("select sku_item_id from po_items where branch_id = $branch_id and po_id = $poid and user_id = $sessioninfo[id]");		
		}
		
		while($r = $con->sql_fetchrow()){
			$exclude_idlist[] = $r[0];
		}
		$con->sql_freeresult();
		if ($exclude_idlist){
			$str_exclude_idlist = "and si.id not in (".join(",",$exclude_idlist).")";		
		}
	}

	//$bid = $branch_id;
	
	// for testing purpose, open the 2nd option
	$branch_check = "vendor_sku_history.vendor_id = $vendor_id and ";
	
	if (BRANCH_CODE != 'HQ') {
		$branch_check .= "vendor_sku_history.branch_id = $branch_id and ";	
	}
	//3/17/2008 9:47:48 AM gary -always using filter by branch_id
	//$branch_check .= "vendor_sku_history.branch_id = $branch_id and ";
		
	// sort descending by history added - to get the latest price info
	$rr1 = $con->sql_query("select si.sku_id, sku_item_code, si.block_list, si.id, si.description, si.artno, si.mcode, sku.varieties, if (sip.price is null, vendor_sku_history.selling_price, sip.price) as selling_price, vendor_sku_history.cost_price 
from vendor_sku_history 
left join sku_items si on vendor_sku_history.sku_item_id = si.id 
left join sku_items_price sip on sip.sku_item_id=si.id and sip.branch_id=$branch_id
left join sku on sku_id = sku.id 
left join category on sku.category_id = category.id 
where $branch_check category.department_id = $dept_id $str_exclude_idlist and si.active=1
order by si.id, vendor_sku_history.added desc, si.description, si.artno, si.mcode");
	$items = array();
	while ($r=$con->sql_fetchassoc($rr1)){
		// skip same item
		if (isset($items[$r['id']])) continue;
		// skip if item is blocked
		$blocker = unserialize($r['block_list']);
		if (isset($blocker[$branch_id])) continue;

		$items[$r['id']] = $r;		
	}
	$con->sql_freeresult($rr1);
	
	if($config['po_vendor_listing_enable_check_master_vendor']){
		if($items){
			$exclude_idlist = array_merge($exclude_idlist, array_keys($items));
			if ($exclude_idlist){
				$str_exclude_idlist = "and si.id not in (".join(",",$exclude_idlist).")";		
			}
		}
		$q1 = $con->sql_query("select si.sku_id, si.sku_item_code, si.block_list, si.id, si.description, si.artno, si.mcode, sku.varieties, ifnull(sip.price, si.selling_price) as selling_price, sic.grn_cost as cost_price
		from sku_items si
		left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
		left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
		left join sku on sku_id = sku.id 
		left join category on sku.category_id = category.id 
		where category.department_id = $dept_id $str_exclude_idlist and si.active=1 and sku.vendor_id=$vendor_id
		order by si.id, si.description, si.artno, si.mcode");
		
		while($r = $con->sql_fetchassoc($q1)){
			// skip if item is blocked
			$blocker = unserialize($r['block_list']);
			if (isset($blocker[$branch_id])) continue;

			$items[$r['id']] = $r;	
		}
		$con->sql_freeresult($q1);
	}
	$smarty->assign("items", $items);
	$smarty->display($tpl);
}

// return list of vendor's child-SKU with checkbox infront
//$tpl = "purchase_order.new.show_sku.tpl"
function expand_sku($skuid, $tpl = "purchase_order.new.show_sku.tpl"){
	global $con, $smarty, $sessioninfo;
	$con->sql_query("select sku_items.* from sku_items where sku_id = $skuid and sku_item_code not like '%0000' order by sku_items.description, sku_items.artno, sku_items.mcode");
	$smarty->assign("items", $con->sql_fetchrowset());
    $smarty->assign("sku_id", $skuid);
	$smarty->display($tpl);
}
?>
