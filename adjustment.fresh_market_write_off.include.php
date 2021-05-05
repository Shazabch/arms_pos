<?php
/*
6/24/2011 2:45:30 PM Andy
- Make all branch default sort by sequence, code.

7/27/2011 4:24:32 PM Justin
- Added to pick up sku item's doc decimal point.

7/3/2013 11:32 AM Fithri
- pm notification standardization
*/

$maintenance->check(1);
define('ADJ_FRESH_MARKET_WRITEOFF_TYPE', 'FRESH MARKET SKU WRITE-OFF');
//define('ADJ_FRESH_MARKET_WRITEOFF_TYPE', 'STORE USE - FOODCOURT BJ');
$smarty->assign('time_value', 1000000000);

function load_adj_header($branch_id, $id, $redirect_if_not_found = false){
	global $con,$smarty, $LANG, $sessioninfo;

	$con->sql_query("select adj.*,user.u as username,b.code as bcode
	from adjustment adj
	left join user on user.id=adj.user_id
	left join branch b on b.id=adj.branch_id
	where adj.branch_id=".mi($branch_id)." and adj.id=".mi($id)." and adj.adjustment_type=".ms(ADJ_FRESH_MARKET_WRITEOFF_TYPE));
	$form = $con->sql_fetchrow();
	if(!$form){
		if($redirect_if_not_found)  js_redirect(sprintf($LANG['ADJUSTMENT_NOT_FOUND'], $id), "$_SERVER[PHP_SELF]");
		else return false;
	}
	$con->sql_freeresult();

	if ($form['approval_history_id']>0){
		$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u
from branch_approval_history_items i
left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
left join user on i.user_id=user.id
where h.ref_table='ADJUSTMENT' and i.branch_id=$branch_id and i.approval_history_id=$form[approval_history_id]
order by i.timestamp");
		$smarty->assign("approval_history", $con->sql_fetchrowset($q2));
	}

	$params = array();
	$params['user_id'] = $sessioninfo['id'];
	$params['id'] = $form['approval_history_id'];
	$params['branch_id'] = $branch_id;
	$params['check_is_approval'] = true;
	$is_approval = check_is_last_approval_by_id($params, $con);
	if($is_approval)  $form['is_approval'] = 1;
	
	return $form;
}

function load_adj_items($branch_id, $id, $load_from_tmp = false){
    global $con,$smarty, $LANG, $sessioninfo;

    $filter = array();
    if($load_from_tmp){
		$tbl = 'tmp_adjustment_items';
		$filter[] = "adji.user_id=$sessioninfo[id]";
	}else   $tbl = 'adjustment_items';

	$filter[] = "adji.branch_id=".mi($branch_id)." and adjustment_id=".mi($id);
	$filter = "where ".join(' and ', $filter);

	$con->sql_query("select adji.*,si.sku_item_code, ifnull(si.artno,si.mcode) as artno_mcode, si.description as sku_description,uom.code as uom_code,uom.fraction as uom_fraction, si.doc_allow_decimal
from $tbl adji
left join sku_items si on si.id=adji.sku_item_id
left join uom on uom.id=si.packing_uom_id
$filter order by adji.id");
	return $con->sql_fetchrowset();
}

function init_selection(){
    global $con, $sessioninfo, $smarty;
    
    // load departments
	if ($sessioninfo['level'] < 9999){
		if (!$sessioninfo['departments'])
			$depts = "id in (0)";
		else
			$depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
	}
	else{
		$depts = 1;
	}
	$con->sql_query("select id, description from category where active=1 and level=2 and $depts order by description");
	$smarty->assign("dept", $con->sql_fetchrowset());
	$con->sql_freeresult();
	
	// load branches
	$con->sql_query("select * from branch order by sequence, code");
	while($r = $con->sql_fetchrow()){
		$branches[$r['id']] = $r;
	}
	$con->sql_freeresult();
	$smarty->assign("branches", $branches);
	
	// UOM
	$con->sql_query("select * from uom order by code");
	while($r = $con->sql_fetchrow()){
		$uom[$r['id']] = $r;
	}
	$con->sql_freeresult();
	$smarty->assign("uom", $uom);
}

function adj_approval($branch_id, $adj_id){
	global $con, $smarty, $sessioninfo, $LANG, $approval_status;
	$q1=$con->sql_query("select adj.*, bah.approvals
from adjustment adj
left join branch_approval_history bah on bah.id = adj.approval_history_id and bah.branch_id = adj.branch_id
where adj.id=$adj_id and adj.branch_id=$branch_id");
	$r1 = $con->sql_fetchrow($q1);

	$status=1;
	$approved = 1;
	$comment="'Approved'";

	$aid = mi($r1['approval_history_id']);
	$approvals = $r1['approvals'];

	$con->sql_query("insert into branch_approval_history_items (approval_history_id, branch_id, user_id, status, log) values ($aid, $branch_id, $sessioninfo[id], $status, $comment)");

	$con->sql_query("update adjustment set status=$status, approved=$approved where id=$adj_id and branch_id=$branch_id");

	update_sku_item_cost($adj_id,$branch_id);
	//send_pm_to_user($adj_id,$aid,$status,$branch_id);
	$to = get_pm_recipient_list($adj_id,$aid,$status,'approval',$branch_id,'adjustment');
	send_pm($to, "Adjustment Approval (ID#$adj_id) $approval_status[$status]", "adjustment.php?a=view&id=$adj_id&branch_id=$branch_id");
}

function update_sku_item_cost($id,$branch_id){
	global $sessioninfo, $con;
	$con->sql_query("update sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (select sku_item_id from adjustment_items adj_items where adj_items.adjustment_id=$id and adj_items.branch_id=$branch_id)");
}

/*
function send_pm_to_user($adj_id,$aid,$status,$branch_id){

	global $con, $sessioninfo, $smarty, $approval_status;
	// get the PM list
	$con->sql_query("select notify_users
	from branch_approval_history where id = $aid and branch_id = $branch_id");
	$r = $con->sql_fetchrow();

	$recipients = $r[0];
	$recipients = str_replace("|$sessioninfo[id]|", "|", $recipients);
	$to = preg_split("/\|/", $recipients);

	// send pm
	send_pm($to, "Adjustment Approval (ID#$adj_id) $approval_status[$status]", "adjustment.php?a=view&id=$adj_id&branch_id=$branch_id");
}
*/

function adj_reset($branch_id, $adj_id){
	global $con,$sessioninfo,$config,$smarty,$LANG;
	$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;

	if($sessioninfo['level']<$required_level){
        js_redirect(sprintf('Forbidden', 'Adjustment', BRANCH_CODE), $_SERVER['PHP_SELF']);
	}

	//add reset config
	$check_date = strtotime($_REQUEST['adjustment_date']);

	if (isset($config['reset_date_limit']) && $config['reset_date_limit'] >= 0){
		$reset_limit = $config['reset_date_limit'];
		$reset_limit = strtotime("-1 day",strtotime("-$reset_limit day" , strtotime("now")));
		if ($check_date<$reset_limit){
			js_redirect($LANG['ADJUSTMENT_DATE_RESET_LIMIT'], $_SERVER['PHP_SELF']);
		}
	}

	$form = load_adj_header($branch_id, $adj_id);

	$aid=$form['approval_history_id'];
	$approvals=$form['approvals'];
	$status = 0;

	if($aid){
        $upd = array();
		$upd['approval_history_id'] = $aid;
		$upd['branch_id'] = $branch_id;
		$upd['user_id'] = $sessioninfo['id'];
		$upd['status'] = $status;
		$upd['log'] = $_REQUEST['reason'];

		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd));
		$con->sql_query("update branch_approval_history set status=$status where id = $aid and branch_id = $branch_id");
	}

	$upd = array();
	$upd['status'] = $status;
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	$upd['approved'] = 0;

	update_sku_item_cost($adj_id,$branch_id);

	$con->sql_query("update adjustment set ".mysql_update_by_field($upd)." where id=$adj_id and branch_id=$branch_id");
    log_br($sessioninfo['id'], 'Adjustment', $adj_id, sprintf("Adjustment Reset (#$form[id])",$adj_id));

	header("Location: $_SERVER[PHP_SELF]?t=reset&save_id=$adj_id");
	exit;
}
?>
