<?php

function load_order_header($branch_id, $id, $redirect_if_not_found = false){
	global $con,$smarty, $LANG, $dp_session;
	
	$con->sql_query("select so.*,user.u as username
	from sales_order so
	left join user on user.id=so.user_id
	where so.branch_id=".mi($branch_id)." and so.id=".mi($id));
	$form = $con->sql_fetchrow();
	if(!$form){
		if($redirect_if_not_found)  js_redirect($LANG['INVALID_SO_ID'], "$_SERVER[PHP_SELF]");
		else return false;
	}
	$con->sql_freeresult();
	
	if ($form['approval_history_id']>0){
		$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u
from branch_approval_history_items i
left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
left join user on i.user_id=user.id
where h.ref_table='sales_order' and i.branch_id=$branch_id and i.approval_history_id=$form[approval_history_id]
order by i.timestamp");

		$smarty->assign("approval_history", $con->sql_fetchrowset($q2));
	}
	
	/*if($form['delivered']){
		// get DO - unsaved, waiting approve, approved and rejected
		$con->sql_query("select do.id,do.branch_id,do.do_no,branch.report_prefix
		from do
		left join branch on branch.id=do.branch_id
		where do.ref_tbl='sales_order' and do.ref_no=".ms($form['order_no'])." and do.active=1 and do.status in (0,1,2)");
		$smarty->assign("do_list", $con->sql_fetchrowset());
		$con->sql_freeresult();
	}elseif($form['exported_to_pos']){
		if($form['active'] && $form['status'] == 1 && $form['approved']){
			// get sales order exported to pos
			$form['receipt_details'] = get_sales_order_receipt_list($branch_id, $id);
		}	
	}*/
	
	/*$params = array();
	$params['user_id'] = $sessioninfo['id'];
	$params['id'] = $form['approval_history_id'];
	$params['branch_id'] = $branch_id;
	$params['check_is_approval'] = true;
	$is_approval = check_is_last_approval_by_id($params, $con);
	if($is_approval)  $form['is_approval'] = 1;*/

	//print_r($form);
	return $form;
}

function load_order_items($branch_id, $id){
    global $con,$smarty, $LANG, $dp_session;
    
    $filter = array();
    $tbl = 'sales_order_items';

	$filter[] = "oi.branch_id=".mi($branch_id)." and sales_order_id=".mi($id);
	$filter = "where ".join(' and ', $filter);
	
	$sql = "select oi.*,si.sku_item_code, ifnull(si.artno,si.mcode) as artno_mcode, si.description as sku_description,uom.code as uom_code,uom.fraction as uom_fraction, si.doc_allow_decimal, puom.code as packing_uom_code
from $tbl oi
left join sku_items si on si.id=oi.sku_item_id
left join uom on uom.id=oi.uom_id
left join uom puom on puom.id=si.packing_uom_id
$filter
order by oi.id";
	//print $sql;
	$q1 = $con->sql_query($sql);
	$items = array();
	while($r = $con->sql_fetchassoc($q1)){
		
		/*if(!$load_from_tmp){
			$po_info = get_sales_order_items_po_info($r['branch_id'], $r['id']);
			
			if(is_array($po_info) && $po_info)	$r = array_merge($r, $po_info);
		}*/
		
		$items[$r['id']] = $r;
	}
	$con->sql_freeresult($q1);
	
	return $items;
}

function init_so_selection(){
	global $con, $dp_session, $smarty, $config;

	// uom
	$con->sql_query("select * from uom where active=1 order by code");
	while($r = $con->sql_fetchrow()){
		$uom[$r['id']] = $r;
	}
	$smarty->assign('uom', $uom);
	$con->sql_freeresult();

	// branch
	$con->sql_query("select * from branch order by sequence,code");
	while($r = $con->sql_fetchrow()){
		$branches[$r['id']] = $r;
	}
	$smarty->assign('branches', $branches);
	$con->sql_freeresult();
	
	// user
	$user_list = array();
	$con->sql_query("select * from user where active=1 and template=0 order by u");
	while($r = $con->sql_fetchassoc()){
		$user_list[$r['id']] = $r;
	}
	$smarty->assign('user_list', $user_list);
	$con->sql_freeresult();
}

?>
