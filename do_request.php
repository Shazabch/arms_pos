<?php
/*
11/3/2009 5:37:16 PM Andy
- Add Rejected tab (status=3), rejected items can revert or cancel

11/23/2009 1:14:12 PM Andy
- Add get stock balance for request branch
- Add Show sales trend for from branch

11/25/2009 12:30:19 PM Andy
- Add Log

6/17/2010 3:57:06 PM Justin
- Moved the table creation for do_request_items to maintenance.php

9/29/2010 10:55:46 AM Andy
- Show request by user.
- Show reject by user.

6/24/2011 10:56:18 AM Andy
- Add user department permission filter for DO Request.

11/21/2011 2:59:22 PM Andy
- Add show Ctn#1 and Ctn#2 at DO Request if found config.do_request_show_ctn_1_2

3/29/2012 5:06:43 PM Justin
- Added to show Ctn #1 and Ctn #2 after chosen a SKU item if found config.do_request_show_ctn_1_2.

12/10/2012 2:09 PM Andy
- Add Expected Delivery Date. can be disabled by config "do_request_no_expected_delivery_date".

12/12/2012 2:32 PM Andy
- Add auto fill in expect delivery date if found got config "do_request_default_expected_delivery_date_extend_day".
- Add checking to block same item same expect delivery date, only for those item not yet process. need config "do_request_block_same_item_same_expect_delivery_date".

12/24/2012 11:45 AM Andy
- DO Request can add by sku group.

12/26/2012 10:24 AM Andy
- Add show link code at "DO Request (Add by SKU Group)".
- use array_map(utf8_encode, $ret) to fix json_encode problem.

1/10/2012 3:09 PM Andy
- Add show packing uom at sku group item list.
- Add can choose to sort which field when refresh sku group item.

1/17/2013 5:03 PM Justin
- Enhanced to filter off those expired sku items set on Vendor Portal SKU Group Item Date Control.

2/19/2013 5:27 PM Justin
- Enhanced to have sorting feature by Last Update or Department.

3/5/2013 12:48 PM Justin
- Enhanced to have validation for expected delivery date that disallow user to choose previous date base on config set.

3/5/2013 4:20 PM Andy
- Enhance to get group stock balance when add item.

3/7/2013 3:22 PM Andy
- Enhanced to pre-load Stock Balance From at DO Request (Add by SKU Group).
- Enhanced to capture Group Stock Balance From when add item.
- Enhanced to round Group Stock Balance From/By to config qty decimal.

03/08/2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

03/24/2016 09:30 Edwin
- Added on check supply branch's stock balance when SKU is requested if config do_request_not_allow_if_no_stock is enable

7/6/2017 12:07 PM Andy
- Enhanced to able to highlight DO Request Item by SKU_ITEM_ID.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

3/22/2019 11:06 AM Andy
- Added "Advanced Add" feature.

5/28/2019 2:46 PM Justin
- Enhanced to have remove items by selection.

12/11/2020 2:46 PM Rayleen
- Add Old Code Link in request do query
- Add Additional desction in so search sku query

12/22/2020 5:46 PM Rayleen
- Add sku_items column in search sku query

12/23/2020 2:59 PM Rayleen
- Add color and size column in table list sku query
*/
include("include/common.php");
$maintenance->check(191);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('DO_REQUEST')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DO_REQUEST', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
include("do.include.php");

$smarty->assign("PAGE_TITLE", "DO Request");
$page_size = 30;

if($_REQUUEST['ajax']!=1||$_REQUEST['init_selection'])	init_selection();

// check default expect do date
if(!$config['do_request_no_expected_delivery_date'] && $config['do_request_default_expected_delivery_date_extend_day']){
	$pattern = $config['do_request_default_expected_delivery_date_extend_day']>0 ? "+".mi($config['do_request_default_expected_delivery_date_extend_day']) : mi($config['do_request_default_expected_delivery_date_extend_day']);
	$default_expect_do_date = date("Y-m-d", strtotime("$pattern day", time()));
	$smarty->assign('default_expect_do_date', $default_expect_do_date);
}

$default_expect_min_do_date = "";
if($config['do_request_expected_delivery_date_limit']){
	$smarty->assign('do_request_expected_delivery_date_times', $config['do_request_expected_delivery_date_limit']['time_expire']);
	$smarty->assign('do_request_expected_delivery_date_days', $config['do_request_expected_delivery_date_limit']['days_lockdown']);
	
	$curr_date = date("Y-m-d");
	$splt_time_expire = explode(":", $config['do_request_expected_delivery_date_limit']['time_expire']);
	$expect_min_do_date = date("Y-m-d H:i:s", strtotime("+".mi($splt_time_expire[0])." hours +".mi($splt_time_expire[1])." minutes", strtotime($curr_date)));
	if(time() > strtotime($expect_min_do_date)){
		$default_expect_min_do_date = date("Y-m-d", strtotime("+".mi($config['do_request_expected_delivery_date_limit']['days_lockdown'])." days", time()));
	}else $default_expect_min_do_date = date("Y-m-d", strtotime("+1 days", time()));
	$smarty->assign('default_expect_min_do_date', $default_expect_min_do_date);
}

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
		case 'ajax_get_item_info':
			ajax_get_item_info();
			exit;
		case 'ajax_add_item':
			ajax_add_item();
			exit;
		case 'ajax_list_sel':
			ajax_list_sel();
			exit;
		case 'ajax_remove_item':
			ajax_remove_item();
			exit;
		case 'ajax_update_request_qty':
			ajax_update_request_qty();
			exit;
		case 'ajax_cancel_item':
		  ajax_cancel_item();
		  exit;
		case 'ajax_revert_item':
		  ajax_revert_item();
		  exit;
		case 'add_by_sku_group_main':
			add_by_sku_group_main();
			exit;
		case 'ajax_refresh_sku_group_item_list':
			ajax_refresh_sku_group_item_list();
			exit;
		case 'ajax_add_do_request_item_by_sku_group':
			ajax_add_do_request_item_by_sku_group();
			exit;
		case 'advanced_add_main':
			advanced_add_main();
			exit;
		case 'ajax_advance_search_items':
			ajax_advance_search_items();
			exit;
		case 'ajax_advance_search_add_items':
			ajax_advance_search_add_items();
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	    
	}
}

init_table();
$smarty->display("do_request.tpl");
exit;

function init_table(){}	// moved to maintenance.php

function ajax_add_item($params = array()){
	global $con,$smarty, $sessioninfo, $config, $default_expect_min_do_date;
	$branch_id = mi($sessioninfo['branch_id']);
	$sku_item_id = mi($_REQUEST['sku_item_id']);
	
	$upd['branch_id'] = $branch_id;
	$upd['request_branch_id'] = mi($_REQUEST['request_branch_id']);
	$upd['sku_item_id'] = $sku_item_id;
	$upd['user_id'] = $sessioninfo['id'];
	$upd['request_qty'] = mf($_REQUEST['request_qty']);
	$upd['stock_balance'] = mf($_REQUEST['stock_balance']);
	//$upd['uom_id'] = mi($_REQUEST['uom_id']);
	$upd['comment'] = $_REQUEST['comment'];
	$upd['added'] = 'CURRENT_TIMESTAMP';
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	$upd['sales_trend'] = serialize($_REQUEST['sales_trend']);
	
	$no_die = $params['no_die'];
	$need_cont = $params['need_cont'];
	if($params){
		if($params['sku_item_id']){
			$sku_item_id = $upd['sku_item_id'] = $params['sku_item_id'];
		}	
		if($params['request_branch_id'])	$upd['request_branch_id'] = $params['request_branch_id'];
		if($params['request_qty'])	$upd['request_qty'] = $params['request_qty'];
		if($params['stock_balance'])	$upd['stock_balance'] = $params['stock_balance'];
		if($params['comment'])	$upd['comment'] = $params['comment'];
		if($params['sales_trend'])	$upd['sales_trend'] = serialize($params['sales_trend']);
	}
	
	$upd['default_request_qty'] = mf($_REQUEST['request_qty']);
	$err = array();
	
	if(!$config['do_request_no_expected_delivery_date']){
		$upd['expect_do_date'] = $_REQUEST['expect_do_date'];
		// check expected delivery date
		if($expect_do_date && $config['do_request_expected_delivery_date_limit'] && strtotime($upd['expect_do_date']) < strtotime($default_expect_min_do_date)){
			$errmsg = "Expected Delivery Date cannot earlier than ".$default_expect_min_do_date;
			
			if($no_die){
				$err[] = $errmsg;
				return $err;
			}else{
				die($errmsg);
			}
		}
		
		// check duplicate if got config
		if($config['do_request_block_same_item_same_expect_delivery_date'] && $upd['expect_do_date']){
			$con->sql_query("select dri.id, si.sku_item_code
			from do_request_items dri
			join sku_items si on si.id=dri.sku_item_id
			where dri.branch_id=".mi($upd['branch_id'])." and dri.sku_item_id=".mi($upd['sku_item_id'])." and dri.expect_do_date=".ms($upd['expect_do_date'])." and dri.status=0 and (dri.print_picking_list_by=0 or dri.print_picking_list_by is null) and (dri.po_id =0 or dri.po_id is null) limit 1");
			$dup_item = $con->sql_fetchassoc();
			$con->sql_freeresult();

			if($dup_item){
				$errmsg = "The item (".$dup_item['sku_item_code'].") already requested for date ".$upd['expect_do_date'];
				
				if($no_die){
					$err[] = $errmsg;
					return $err;
				}else{
					die($errmsg);
				}
			}
		}
	}	
	
	// selling price
	$sql = "select ifnull(sip.price,selling_price) as selling_price, si.sku_id, uom.fraction as uom_fraction
	from sku_items si
	left join sku_items_price sip on sip.sku_item_id=si.id and sip.branch_id=$branch_id
	left join uom on uom.id=si.packing_uom_id
 	where si.id=$sku_item_id";
 	//print $sql;
	$con->sql_query($sql) or die(mysql_error());
	$si_info = $con->sql_fetchassoc();
	$con->sql_freeresult();
	$sku_id = mi($si_info['sku_id']);
	
	$upd['selling_price'] = $si_info['selling_price'];
	
	// stock balance of request branch
	//$con->sql_query("select qty from sku_items_cost where sku_item_id=$sku_item_id and branch_id=".mi($upd['request_branch_id'])) or die(mysql_error());
	//$upd['stock_balance2'] = $con->sql_fetchfield(0);

	$qsc = $con->sql_query("select sic.sku_item_id, sic.qty, uom.fraction as uom_fraction 
	from sku_items_cost sic
	join sku_items si on si.id=sic.sku_item_id
	left join uom on uom.id=si.packing_uom_id
	where si.sku_id=$sku_id and sic.branch_id=".mi($upd['request_branch_id']));
	$upd['group_stock_balance2'] = 0;
	while($r = $con->sql_fetchassoc($qsc)){
		$upd['group_stock_balance2'] += $r['qty']*$r['uom_fraction'];
		
		if($r['sku_item_id'] == $sku_item_id){
			$upd['stock_balance2'] = $r['qty'];
		}
	}
	$con->sql_freeresult($qsc);
	
	// group stock balance by
	if($upd['group_stock_balance2'] && $si_info['uom_fraction']){
		$upd['group_stock_balance2'] = round($upd['group_stock_balance2'] / $si_info['uom_fraction'], $config['global_qty_decimal_points']);
	}
	
	// group stock balance from
	$con->sql_query("select sum(sic.qty*uom.fraction) as total_pcs
	from sku_items_cost sic
	join sku_items si on si.id=sic.sku_item_id
	left join uom on uom.id=si.packing_uom_id
	where sic.branch_id=$branch_id and si.sku_id=$sku_id");
	$upd['group_stock_balance'] = $con->sql_fetchfield(0);
	$con->sql_freeresult();
	if($upd['group_stock_balance'] && $si_info['uom_fraction']){
		$upd['group_stock_balance'] = round($upd['group_stock_balance'] / $si_info['uom_fraction'], $config['global_qty_decimal_points']);
	}
	
	if($config['do_request_not_allow_if_no_stock']) {
		if($upd['stock_balance2']<=0) {
			print "SKU requested to supply branch currently out of stock.";
			exit;
		}
	}
	
	//print_r($upd);exit;
	$con->sql_query("insert into do_request_items ".mysql_insert_by_field($upd)) or die(mysql_error());
	$new_id = $con->sql_nextid();
	
	$con->sql_query("select code from branch where id=$upd[request_branch_id]") or die(mysql_error());
	$do_request_branch_code = $con->sql_fetchfield(0);
	
	log_br($sessioninfo['id'], 'DO Request', $new_id, "Add New Item, Request to $do_request_branch_code, Item ID: $new_id, SKU Item ID: $sku_item_id, Qty: $upd[request_qty]");
	
	if(!$need_cont){
		print "OK";
		exit;
	}
}

function ajax_get_item_info(){
	global $con, $sessioninfo, $smarty;
	
	$sku_item_id = mi($_REQUEST['sku_item_id']);
	$branch_id = mi($sessioninfo['branch_id']);
	// stock balance
	/*$year = mi(date('Y'));
	$sb_tbl = "stock_balance_b".$branch_id."_".$year;
	$con->sql_query("select * from $sb_tbl where sku_item_id=$sku_item_id and is_latest=1") or die(mysql_error());*/
	$con->sql_query("select sku_items_cost.*, uom.code as uom_code, u_ctn1.code as ctn1_uom_code, u_ctn2.code as ctn2_uom_code, si.additional_description, si.color, si.size, si.weight, si.flavor, si.model, si.width, si.height, si.length, si.sn_we, si.sn_we_type, si.internal_description, si.marketplace_description, si.misc, si.weight_kg
					from sku_items_cost
					left join sku_items si on si.id=$sku_item_id
					left join uom on uom.id=si.packing_uom_id
					left join uom u_ctn1 on u_ctn1.id = si.ctn_1_uom_id
					left join uom u_ctn2 on u_ctn2.id = si.ctn_2_uom_id
					where sku_item_id=$sku_item_id and branch_id=$branch_id") or die(mysql_error());
	$ret['sb'] = $con->sql_fetchrow();
	
	$ret['sb']['additional_description'] = unserialize($ret['sb']['additional_description']);

	$sku_info = array();
	$sku_info['weight_desc'] = $ret['sb']['weight'];
	$sku_info['weight'] = ($ret['sb']['weight_kg']) ? $ret['sb']['weight_kg'].' kg' : '';
	$sku_info['flavor'] = $ret['sb']['flavor'];
	$sku_info['misc'] = $ret['sb']['misc'];
	$sku_info['model'] = $ret['sb']['model'];
	$sku_info['width'] = ($ret['sb']['width']) ? $ret['sb']['width'].' cm' : '';
	$sku_info['height'] = ($ret['sb']['height']) ? $ret['sb']['height'].' cm' : '';
	$sku_info['length'] = ($ret['sb']['length']) ? $ret['sb']['length'].' cm' : '';
	$sku_info['warranty'] = ($ret['sb']['sn_we']) ? $ret['sb']['sn_we'].' '.$ret['sb']['sn_we_type'] : '';
	$sku_info['internal_description'] = $ret['sb']['internal_description'];
	$sku_info['marketplace_description'] = $ret['sb']['marketplace_description'];

	// masterfile sku
	// $con->sql_query("select * from sku_items where id=$sku_item_id") or die(mysql_error());
	// $ret['sku_items'] = $con->sql_fetchrow();

	// get sales trend
	$sales_trend = get_sales_trend($branch_id,$sku_item_id);
	$item = array();
	$item = array_merge($item, $sales_trend);
	$smarty->assign('item',$item);
	$smarty->assign('sb_info',$ret['sb']);
	$smarty->assign('sku_info',$sku_info);

	// $ret['sku_info'] = $sku_info;
	$ret['item_details'] = $smarty->fetch('do_request.item_details.tpl');
	
	print json_encode($ret);
}

function ajax_list_sel(){
	global $con, $smarty, $sessioninfo, $page_size, $appCore, $config;
	//print_r($_REQUEST);
	$t = mi($_REQUEST['t']);
	$p = mi($_REQUEST['p']);
	$sort_by = ($_REQUEST['sort_by']) ? $_REQUEST['sort_by'] : 1;
	$sort_order = ($_REQUEST['sort_order']) ? $_REQUEST['sort_order'] : "asc";
	$highlight_sku_item_id = mi($_REQUEST['highlight_sku_item_id']);
	
	$size = $page_size;
	$start = $p*$size;
	
	switch($t){
		case 1:	// saved items
			$filter[] = "dri.status=0";
			break;
		case 2: // processed items
			$filter[] = "dri.status=1";
			break;
		case 3: // fully processed
			$filter[] = "dri.status in (2,4)";
			break;
		case 4: // search items
			$str = $_REQUEST['search_str'];
			if(!$str)	die('Cannot search empty string');
			$filter_or[] = "si.sku_item_code=".ms($str);
			$filter_or[] = "si.artno=".ms($str);
			$filter_or[] = "si.mcode=".ms($str);
			$filter_or[] = "si.description like ".ms('%'.replace_special_char($str).'%');
			$filter[] = "(".join(' or ',$filter_or).")";
			break;
		case 5: // rejected
		  $filter[] = "dri.status=3";
		  break;
		default:
			die('Invalid Page');
	}
	$filter[] = "dri.active=1";
	$filter[] = "dri.branch_id=".mi($sessioninfo['branch_id']);
	$filter[] = "c.department_id in ($sessioninfo[department_ids])";
	if($highlight_sku_item_id)	$filter[] = "dri.sku_item_id=$highlight_sku_item_id";
	
	$filter = "where ".join(' and ',$filter);
	
	$con->sql_query("select count(*) 
	from do_request_items dri 
	left join sku_items si on si.id=dri.sku_item_id
	left join sku on sku.id=si.sku_id
	left join category c on c.id=sku.category_id
	left join branch b on b.id=dri.request_branch_id $filter") or die(mysql_error());
	$total_rows = $con->sql_fetchfield(0);
	
	if($start>=$total_rows){
		$start = 0;
		$_REQUEST['p'] = 0;
	}
	$limit = "limit $start, $size";
	
	if($sort_by == 1) $order = "order by last_update ".$sort_order;
	else $order = "order by c2.description ".$sort_order.", last_update desc";
	
	$total_page = ceil($total_rows/$size);
	$sql = "select dri.*,si.sku_item_code,si.artno,si.mcode,si.link_code,si.description,b.code as request_branch_code,user.u,
			u2.u as request_by,user_reject.u as reject_by_user, pack_uom.code as packing_uom_code, 
			pack_uom.fraction as packing_uom_fraction, c1_uom.code as ctn_1_code, c1_uom.fraction as ctn_1_fraction, 
			c2_uom.code as ctn_2_code, c2_uom.fraction as ctn_2_fraction, c2.description as department, si.color, si.size
			from do_request_items dri 
			left join sku_items si on si.id=dri.sku_item_id
			left join sku on sku.id=si.sku_id
			left join category c on c.id=sku.category_id
			left join category_cache cc on cc.category_id=c.id
			left join category c2 on c2.id = cc.p2
			left join branch b on b.id=dri.request_branch_id
			left join user on user.id=dri.print_picking_list_by
			left join user u2 on u2.id=dri.user_id
			left join user user_reject on user_reject.id=dri.reject_by
			left join uom pack_uom on pack_uom.id=si.packing_uom_id
			left join uom c1_uom on c1_uom.id=si.ctn_1_uom_id
			left join uom c2_uom on c2_uom.id=si.ctn_2_uom_id
			$filter $order $limit";
	//print $sql;
	$q1 = $con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow($q1)){
		$r['do_list'] = unserialize($r['do_list']);
		$r['sales_trend'] = unserialize($r['sales_trend']);
		
		// make float to prevent number_format warning at php v5.3.2
		$r['sales_trend']['qty'][1] = mf($r['sales_trend']['qty'][1]);
		$r['sales_trend']['qty'][3] = mf($r['sales_trend']['qty'][3]);
		$r['sales_trend']['qty'][6] = mf($r['sales_trend']['qty'][6]);
		$r['sales_trend']['qty'][12] = mf($r['sales_trend']['qty'][12]);	
		$items[] = $r;
	}
	//print_r($items);
	$con->sql_freeresult($q1);
	$smarty->assign('items',$items);
	$smarty->assign('total_page',$total_page);
	$smarty->assign('item_counter',$start+1);
	$smarty->assign('open_mode','open');
	if($highlight_sku_item_id)	$smarty->assign('highlight_sku_item_id', $highlight_sku_item_id);
		
	$sort_list = array(1=>"Last Update",2=>"Department");
	$smarty->assign("sort_list", $sort_list);
	$smarty->assign("sort_by", $sort_by);
	$smarty->assign("sort_order", $sort_order);
	$smarty->assign("curr_tab", ($t) ? $t : 1);
	
	$smarty->display('do_request.table.tpl');
}

function ajax_remove_item(){
	global $con, $sessioninfo;
	
	$item_id_list = $_REQUEST['item_id_list'];
	// the list_sel always use sessioninfo branch ID
	if($_REQUEST['branch_id']) $branch_id = mi($_REQUEST['branch_id']);
	else $branch_id = mi($_REQUEST['curr_branch_id']);
	
	if(!$item_id_list) die("No item were selected for remove.");
	if(!$branch_id) die("Invalid Branch ID");
	
	foreach($item_id_list as $item_id){
		$q1 = $con->sql_query("delete from do_request_items where id=$item_id and branch_id=$branch_id and status=0 and active=1") or die(mysql_error());
		$effected = $con->sql_affectedrows($q1);
	
		if($effected>0){
			log_br($sessioninfo['id'], 'DO Request', $item_id, "Delete Item, Item ID: $item_id");
		}
	}
    
	print "OK";
	exit;
}

function ajax_update_request_qty(){
	global $con, $sessioninfo;
	
	$id = mi($_REQUEST['id']);
	$branch_id = mi($_REQUEST['branch_id']);
	$qty = mf($_REQUEST['qty']);
	
	if($qty<=0)	die('Invalid Qty');
	
	$upd['request_qty'] = $qty;
	$upd['default_request_qty'] = $qty;
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	
	$con->sql_query("update do_request_items set ".mysql_update_by_field($upd)." where id=$id and branch_id=$branch_id and status=0 and active=1") or die(mysql_error());
	if($con->sql_affectedrows()){
        log_br($sessioninfo['id'], 'DO Request', $id, "Update Request Qty, Item ID: $id, Qty: $qty");
        print "OK";
	}	
	else 	print "Update does not effect anything";
	exit;
}

function ajax_cancel_item(){
  global $con, $sessioninfo;
  
  	$id = mi($_REQUEST['id']);
	$branch_id = mi($_REQUEST['branch_id']);
	
	$upd['status'] = 4;
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	
	$con->sql_query("update do_request_items set ".mysql_update_by_field($upd)." where id=$id and branch_id=$branch_id and status=3 and active=1") or die(mysql_error());
	$effected = $con->sql_affectedrows();
	
	if($effected>0){
	    log_br($sessioninfo['id'], 'DO Request', $id, "Cancel Item, Item ID: $id");
        print "OK";
	}	
	exit;
}

function ajax_revert_item(){
  	global $con, $sessioninfo;
  
  	$id = mi($_REQUEST['id']);
	$branch_id = mi($_REQUEST['branch_id']);
	$new_qty = mf($_REQUEST['new_qty']);
	
	if($new_qty<=0)  die('Invalid Request Qty: '.$new_qty);
	
	$upd['status'] = 0;
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	$upd['request_qty'] = $new_qty;
	
	$con->sql_query("update do_request_items set ".mysql_update_by_field($upd)." where id=$id and branch_id=$branch_id and status=3 and active=1") or die(mysql_error());
	$effected = $con->sql_affectedrows();
	
	if($effected>0){
        log_br($sessioninfo['id'], 'DO Request', $id, "Revert Item, Item ID: $id");
        print "OK";
	}	
	exit;
}

function add_by_sku_group_main(){
	global $con, $smarty, $sessioninfo, $config;
	
	// load sku group
	if($config['sku_group_searching_need_filter_user']){
		if($sessioninfo['level']>=900){
			$sku_group_filter = '';
		}elseif($sessioninfo['level']>=500){
	        $sku_group_filter = "where s1.branch_id=".mi($sessioninfo['branch_id']);
		}else{
	        $sku_group_filter = "where s1.user_id=".mi($sessioninfo['id']);
		}
	}
	
	$sku_group_list = array();
	
	$sql = "select s1.*,count(s2.sku_item_code) as item_count from sku_group s1
	left join sku_group_item s2 using(sku_group_id,branch_id)
	$sku_group_filter group by sku_group_id,branch_id order by description";
		
	$q1 = $con->sql_query($sql);
	while($r = $con->sql_fetchrow($q1)){
        $sku_group_list[] = $r;
	}
	$con->sql_freeresult($q1);
			
	$smarty->assign('sku_group_list', $sku_group_list);
	$smarty->display('do_request.add_by_sku_group_main.tpl');
}

function ajax_refresh_sku_group_item_list(){
	global $con, $smarty, $sessioninfo, $config;
	
	$sku_group_ids = $_REQUEST['sku_group_ids'];
	$sort_by = trim($_REQUEST['sort_by']);
	$sort_order = trim($_REQUEST['sort_order']) == 'desc' ? 'desc' : 'asc';
	
	if(!$sku_group_ids)	die("Invalid SKU Group ID");
	if(!$sort_by)	$sort_by = "si.sku_item_code";
	
	list($sku_group_bid, $sku_group_id) = explode(',', $sku_group_ids);
	
	$sgi_items = array();
	
	$sql = "select sgi.*, si.id as sid, si.sku_item_code,si.artno,si.mcode,si.link_code,si.description,uom.code as packing_uom_code, si.doc_allow_decimal, sic.qty as stock_balance
from sku_group_item sgi
join sku_group sg on sg.branch_id=sgi.branch_id and sg.sku_group_id=sgi.sku_group_id
join sku_items si on si.sku_item_code=sgi.sku_item_code
left join uom on uom.id=si.packing_uom_id
left join sku_items_cost sic on sic.branch_id=".mi($sessioninfo['branch_id'])." and sic.sku_item_id=si.id
join sku_group_vp_date_control vpdc on vpdc.branch_id=sgi.branch_id and vpdc.sku_group_id=sgi.sku_group_id and vpdc.sku_item_id=si.id and curdate() between vpdc.from_date and vpdc.to_date
where sg.branch_id=$sku_group_bid and sg.sku_group_id=$sku_group_id
order by $sort_by $sort_order";
print $sql;
	$q1 = $con->sql_query($sql);
	
	while($r = $con->sql_fetchassoc($q1)){
		$sgi_items[] = $r;
	}
	$con->sql_freeresult($q1);
	
	$ret = array();

	$smarty->assign('sgi_items', $sgi_items);

	$ret['html'] = $smarty->fetch('do_request.add_by_sku_group_main.item_list.tpl');
	$ret['ok'] = 1;
	
	$ret = array_map(utf8_encode, $ret);	// must put this before json encode
	print json_encode($ret);
}

function ajax_add_do_request_item_by_sku_group(){
	global $con, $smarty, $sessioninfo, $config, $default_expect_min_do_date;
	
	$form = $_REQUEST;
	//print_r($form);
	
	$branch_id = $sessioninfo['branch_id'];
	$request_branch_id = $form['request_branch_id'];
	$request_qty_list = $form['request_qty_list'];
	$stock_balance_list = $form['stock_balance_list'];
	$expect_do_date = $form['expect_do_date'];
	$comment = trim($form['comment']);
	
	$err = array();
	if($request_qty_list){
		$sid_info_list = array();
		
		foreach($request_qty_list as $sid => $request_qty){
			if($request_qty <= 0)	continue;
		
			// store sku info first
			$sid_info_list[$sid]['request_qty'] = $request_qty;
			$sid_info_list[$sid]['stock_balance'] = $stock_balance_list[$sid];
			$sid_info_list[$sid]['sku_item_code'] = $form['sku_item_code_list'][$sid];
			
		}
	}
	
	if(!$request_branch_id)	$err[] = "Please select request from branch.";
	
	if($sid_info_list){
		// checking
		if(!$config['do_request_no_expected_delivery_date']){
			// check expected delivery date
			if($expect_do_date && $config['do_request_expected_delivery_date_limit'] && strtotime($expect_do_date) < strtotime($default_expect_min_do_date)){
				$err[] = "Expected Delivery Date cannot earlier than ".$default_expect_min_do_date;
			}
		
			// check duplicate if got config
			if($config['do_request_block_same_item_same_expect_delivery_date'] && $expect_do_date){
				$con->sql_query("select si.sku_item_code
				from do_request_items dri
				join sku_items si on si.id=dri.sku_item_id
				where dri.branch_id=".mi($branch_id)." and dri.sku_item_id in (".join(',', array_keys($sid_info_list)).") and dri.expect_do_date=".ms($expect_do_date)." and dri.status=0 and (dri.print_picking_list_by=0 or dri.print_picking_list_by is null) and (dri.po_id =0 or dri.po_id is null) group by sku_item_code");
				while($r = $con->sql_fetchassoc()){
					$err[] = "The item (".$r['sku_item_code'].") already requested for date ".$expect_do_date;
				}
				$con->sql_freeresult();
			}
		}
	}else{
		$err[] = "No item to add.";
	}
	
	// got error
	if($err){
		foreach($err as $e){
			print "$e\n";
		}
		exit;
	}

	$html = '<ul>';
	
	foreach($sid_info_list as $sid => $si_info){
		$params = array();
		
		$params['sku_item_id'] = $sid;
		$params['request_branch_id'] = $request_branch_id;
		$params['request_qty'] = $si_info['request_qty'];
		$params['comment'] = $comment;
		$sales_trend = get_sales_trend($branch_id, $sid);
		if($sales_trend){
			$sales_trend['sales_trend']['qty'][1] = round($sales_trend['sales_trend']['qty'][1]);
			$sales_trend['sales_trend']['qty'][3] = round($sales_trend['sales_trend']['qty'][3]);
			$sales_trend['sales_trend']['qty'][6] = round($sales_trend['sales_trend']['qty'][6]);
			$sales_trend['sales_trend']['qty'][12] = round($sales_trend['sales_trend']['qty'][12]);
		}
		$params['sales_trend'] = $sales_trend['sales_trend'];
		$params['stock_balance'] = $si_info['stock_balance'];
		
		$params['need_cont'] = 1;
		
		$e = ajax_add_item($params);
		
		// got unexpected error
		if($e && is_array($e)){
			print_r($e);
			exit;
		}
		
		$html .= "<li> ".$si_info['sku_item_code']." requested ".$params['request_qty']." qty.</li>";
	}
	$html .= '</ul>';
	
	$ret = array();
	$ret['ok'] = 1;
	$ret['html'] = $html;
	
	print json_encode($ret);
}

function advanced_add_main(){
	global $con, $smarty, $sessioninfo, $config, $appCore;
	
	// Vendor List
	$vendor_list = $appCore->vendorManager->getVendorList(array('active'=>1));
	
	// Brand List
	$brand_list = $appCore->brandManager->getBrandList(array('active'=>1));
	
	$smarty->assign('vendor_list', $vendor_list);
	$smarty->assign('brand_list', $brand_list);
	$smarty->display('do_request.advanced_add_main.tpl');
}

function ajax_advance_search_items(){
	global $con, $smarty, $sessioninfo, $config, $appCore;
	
	//print_r($_REQUEST);
	
	$vendor_id = mi($_REQUEST['vendor_id']);
	$brand_id = mi($_REQUEST['brand_id']);
	$cat_id = mi($_REQUEST['category_id']);
	$all_category = $_REQUEST['all_category'];
	$sku_added_filter = trim($_REQUEST['sku_added_filter']);
	$sku_added_date_from = trim($_REQUEST['sku_added_date_from']);
	$sku_added_date_to = trim($_REQUEST['sku_added_date_to']);
	$sort_by = trim($_REQUEST['sort_by']);
	$sort_order = trim($_REQUEST['sort_order']);
	$branch_id = mi($sessioninfo['branch_id']);
	
	$filter = array();
	
	// Vendor
	if(trim($sessioninfo['vendor_ids'])){
		$filter[] = "sku.vendor_id in ($sessioninfo[vendor_ids])";
	}
	if($vendor_id > 0){
		$filter[] = "sku.vendor_id=$vendor_id";
	}
	
	if(trim($sessioninfo['brand_ids'])){
		$filter[] = "sku.brand_id in ($sessioninfo[brand_ids])";
	}
	// Brand
	if($brand_id == -1){	// UN-BRANDED
		$filter[] = "sku.brand_id=0";
	}elseif($brand_id > 0){
		$filter[] = "sku.brand_id=$brand_id";
	}
	
	
	// Category
	$filter[] = "c.department_id in ($sessioninfo[department_ids])";
	if($cat_id > 0){
		$cat_info = get_category_info($cat_id);
		if($cat_info){
			$filter[] = "cc.p".mi($cat_info['level'])."=$cat_id";
		}
	}
	
	// SKU Added Date
	if($sku_added_filter){
		$filter[] = "si.added between ".ms($sku_added_date_from)." and ".ms($sku_added_date_to." 23:59:59");
	}
	$filter[] = "si.active=1";
	
	$str_filter = "where ".join(' and ', $filter);
	
	// Sorting
	$str_sort = 'order by ';
	if($sort_by){
		$str_sort .= $sort_by.' '.$sort_order;
	}else{
		$str_sort .= "si.sku_item_code desc";
	}
	
	$sql = "select si.id as sku_item_id, si.sku_item_code, si.mcode, si.link_code, si.artno, si.link_code, si.description, si.added,
		(select sum(dri.request_qty) from do_request_items dri where dri.branch_id=$branch_id and dri.sku_item_id=si.id and dri.status in (0,1) and dri.active=1) as requesting_qty
		from sku_items si
		join sku on sku.id=si.sku_id
		join category c on c.id=sku.category_id
		left join category_cache cc on cc.category_id=c.id
		$str_filter
		$str_sort
		limit 500";
		
	//print $sql;exit;
	$q1 = $con->sql_query($sql);
	$item_list = array();
	while($r = $con->sql_fetchassoc($q1)){
		$item_list[$r['sku_item_id']] = $r;
	}
	$con->sql_freeresult($q1);
	
	$smarty->assign('item_list', $item_list);
	
	$ret = array();
	$ret['html'] = $smarty->fetch('do_request.advanced_add_main.item_list.tpl');
	$ret['ok'] = 1;
	
	$ret = array_map(utf8_encode, $ret);	// must put this before json encode
	print json_encode($ret);
}

function ajax_advance_search_add_items(){
	global $con, $smarty, $sessioninfo, $config, $appCore, $default_expect_min_do_date;
	
	$form = $_REQUEST;
	//print_r($form);
	
	$request_branch_id = mi($form['request_branch_id']);
	$comment = trim($form['comment']);
	$expect_do_date = trim($form['expect_do_date']);
	$branch_id = mi($sessioninfo['branch_id']);
	$err = array();
	
	if(!$request_branch_id)	$err[] = "Please select request from branch.";
	
	if($form['item_qty']){
		$sid_info_list = array();
		
		foreach($form['item_qty'] as $sid => $request_qty){
			// store sku info first
			if($request_qty > 0){
				$sid_info_list[$sid]['request_qty'] = $request_qty;
			}			
		}
	}
	
	if($sid_info_list){
		// checking
		if(!$config['do_request_no_expected_delivery_date']){
			// check expected delivery date
			if($expect_do_date && $config['do_request_expected_delivery_date_limit'] && strtotime($expect_do_date) < strtotime($default_expect_min_do_date)){
				$err[] = "Expected Delivery Date cannot earlier than ".$default_expect_min_do_date;
			}
		
			// check duplicate if got config
			if($config['do_request_block_same_item_same_expect_delivery_date'] && $expect_do_date){
				$con->sql_query("select si.sku_item_code
				from do_request_items dri
				join sku_items si on si.id=dri.sku_item_id
				where dri.branch_id=".mi($branch_id)." and dri.sku_item_id in (".join(',', array_keys($sid_info_list)).") and dri.expect_do_date=".ms($expect_do_date)." and dri.status=0 and (dri.print_picking_list_by=0 or dri.print_picking_list_by is null) and (dri.po_id =0 or dri.po_id is null) group by sku_item_code");
				while($r = $con->sql_fetchassoc()){
					$err[] = "The item (".$r['sku_item_code'].") already requested for date ".$expect_do_date;
				}
				$con->sql_freeresult();
			}
		}
	}else{
		$err[] = "No item to add.";
	}
	
	
	if($err){
		foreach($err as $e){
			print "$e\n";
		}
		exit;
	}
	
	$item_added_count = 0;
	foreach($sid_info_list as $sid => $si_info){
		$params = array();
		
		$params['sku_item_id'] = $sid;
		$params['request_branch_id'] = $request_branch_id;
		$params['request_qty'] = $si_info['request_qty'];
		$params['comment'] = $comment;
		$sales_trend = get_sales_trend($branch_id, $sid);
		if($sales_trend){
			$sales_trend['sales_trend']['qty'][1] = round($sales_trend['sales_trend']['qty'][1]);
			$sales_trend['sales_trend']['qty'][3] = round($sales_trend['sales_trend']['qty'][3]);
			$sales_trend['sales_trend']['qty'][6] = round($sales_trend['sales_trend']['qty'][6]);
			$sales_trend['sales_trend']['qty'][12] = round($sales_trend['sales_trend']['qty'][12]);
		}
		$params['sales_trend'] = $sales_trend['sales_trend'];
		
		$stock_data = $appCore->skuManager->getSKULatestStockAndCost($sid, $branch_id);
		$params['stock_balance'] = $stock_data['qty'];
		$params['need_cont'] = 1;
		
		$e = ajax_add_item($params);
		
		// got unexpected error
		if($e && is_array($e)){
			print_r($e);
			exit;
		}
		
		$item_added_count++;
	}
	
	$ret = array();
	$ret['ok'] = 1;
	$ret['item_added_count'] = $item_added_count;
	
	print json_encode($ret);
}
?>
