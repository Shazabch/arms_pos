<?php
/*
11/3/2009 5:38:02 PM Andy
- Item can be reject in "saved tab"

11/23/2009 1:05:40 PM Andy
- add reject picking list

11/25/2009 12:33:43 PM Andy
- add log

9/29/2010 11:00:30 AM Andy
- Show request by user.
- Add record down who reject the item and show reject by user under the list.

6/24/2011 10:56:18 AM Andy
- Add user department permission filter for DO Request.

4/26/2012 4:37:56 PM Alex
- change get price type and selling price based on do date

6/13/2012 11:28:32 AM Justin
- Added to store DO as credit sales while logged on branch was franchise.

7/16/2012 4:47:32 PM Justin
- Bug fixed system wrongly generate DO document for Franchise.

11/26/2012 10:13:00 AM Fithri
- New Tab "Exported to PO" for item (deliver qty < default request qty && po_qty >0)
- can tick and print picking list"

12/10/2012 2:09 PM Andy
- Add Expected Delivery Date. can be disabled by config "do_request_no_expected_delivery_date".
- Add when process DO Request can filter expected delivery date.
- Add can sort by expected delivery date.
- Change picking list item to sort by category.

12/12/2012 5:00 PM Andy
- Add checking to config "do_request_process_maximum_filter_expect_delivery_date_day" to limit how long the expected delivery date can show.

12/14/2012 11:37 AM Andy
- Remove the expect do date restriction on Process DO Request.
- Add default "expect do date to" if got config "do_request_process_maximum_filter_expect_delivery_date_day".
- Swap from branch and to branch info at print picking list.

2/7/2013 5:17 PM Andy
- Add DO Request Process to able to print alternative picking list templates.
- Add when print picking list will get the latest stock balance.

2/19/2013 2:12 PM Justin
- Enhanced to capture decimal points for qty.
- Enhanced sort list can now sort by department.

2/26/2013 3:52 PM Justin
- Enhanced to always capture UOM as EACH while generating new DO or PO.

3/5/2013 5:05 PM Andy
- Enhance to get latest group stock balance by when print picking list.

4/19/2013 10:50 AM Andy
- Fix wrong item cost while generate DO.

6/10/2014 4:45 PM Justin
- Enhanced to sort by location while print picking list (need config).

6/19/2014 11:07 AM Justin
- Enhanced to sort by location while editing picking list (need config).

6/24/2014 2:13 PM Justin
- Bug fixed on sort by locatino while editing picking list is not functioning.

3/24/2015 5:09 PM Andy
- Enhance to have GST info when generate PO and DO.
- Change the PO Selling Price to use request_branch selling price, not from_branch.

4/29/2015 11:43 AM Andy
- Enhanced to have "Display Cost" features.

12/11/2015 5:14 PM Andy
- Fix sql error when generate DO.

03/24/2016 09:30 Edwin
- Added on decline to print picking list when supply branch's stock balance <= 0 if config do_request_process_restrict_print_if_no_stock is enabled

04/12/2016 15:45 Edwin
- Revert back to enable select/unselect all item function
- Bug fixed on pagination error in picking list tab

5/13/2016 1:29 PM Andy
- Fix print picking list to filter user department.

06/24/2016 16:15 Edwin
- Enhanced on check and update supply branch's stock balance when refresh "Request Items" if config.do_request_not_allow_if_no_stock is enabled.

1/5/2017 10:21 AM Andy
- Fixed exported to po tab always show empty result.

3/24/2017 11:51 AM Andy
- Enhanced to auto recalculate all PO Amount using reCalcatePOUsingOldMethod() when generate or update po.
- Fixed wrong po nsp and ssp.

3/27/2017 3:01 PM Andy
- Change reCalcatePOUsingOldMethod() to reCalcatePOAmt().

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

5/27/2019 5:25 PM Justin
- Enhanced to have reject items by selection.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('DO_REQUEST_PROCESS')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DO_REQUEST_PROCESS', BRANCH_CODE), "/index.php");
include("do.include.php");
$maintenance->check(266);

$smarty->assign("PAGE_TITLE", "Process DO Request");
$page_size = 50;
$request_branch_id = $sessioninfo['branch_id'];
if($_REQUUEST['ajax']!=1||$_REQUEST['init_selection'])	init_selection();

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
		case 'ajax_list_sel':
			ajax_list_sel();
			exit;
		case 'print_picking_list':
			print_picking_list();
			exit;
		case 'ajax_update_field':
			ajax_update_field();
			exit;
		case 'generate_do':
			generate_do();
			exit;
		case 'ajax_show_generate_po':
			ajax_show_generate_po();
			exit;
		case 'ajax_load_vendor_sku':
			ajax_load_vendor_sku();
			exit;
		case 'ajax_generate_po':
			ajax_generate_po();
			exit;
		case 'ajax_change_item_status':
			ajax_change_item_status();
			exit;
		case 'open_picking_list':
			open_picking_list();
			exit;
		case 'save_picking_list':
			save_picking_list();
			exit;
		case 'confirm_picking_list':
			save_picking_list(true);
			exit;
		case 'ajax_reject_item':
		  ajax_reject_item();
		  exit;
		case 'reject_picking_list':
		    reject_picking_list();
		    exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	    
	}
}

if(!$config['do_request_no_expected_delivery_date']){	// got use expected do date
	if($config['do_request_process_maximum_filter_expect_delivery_date_day']){	
		$pattern = "+".mi($config['do_request_process_maximum_filter_expect_delivery_date_day']);

		$default_expect_do_date_to = date("Y-m-d", strtotime("$pattern day", time()));
		$smarty->assign('default_expect_do_date_to', $default_expect_do_date_to);
	}
}

init_table();
init_selection2();
$smarty->display("do_request.process.tpl");
exit;

function init_table(){ /* moved to maintenance.php */ }

function ajax_list_sel(){
	global $con, $smarty, $sessioninfo, $page_size, $request_branch_id, $config;
	//print_r($_REQUEST);
	$t = mi($_REQUEST['t']);
	$p = mi($_REQUEST['p']);
	$branch_id = mi($_REQUEST['branch_id']);
	$expected_do_date_from = $_REQUEST['expected_do_date_from'];
	$expect_do_date_to = $_REQUEST['expect_do_date_to'];
	$clear_selected = mi($_REQUEST['clear_selected']);
	$sort_by = $_REQUEST['sort_by'];
	$sort_order = $_REQUEST['sort_order'] == 'desc' ? 'desc' : 'asc';
	
	$size = $page_size;
	$start = $p*$size;
	
	switch($t){
		case 1:	// request items
			$filter[] = "dri.status=0";
			break;
		case 2: // picking list
			//$filter[] = "dri.status=1";
			//$smarty->assign('open_mode','open');
			//break;
			load_picking_list_table();
			exit;
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
			$smarty->assign('open_mode','open');
			break;
		case 5: // rejected
		  $filter[] = "dri.status=3";
		  break;
		case 6: // exported to PO
		  $filter[] = "dri.status=2";
		  //$filter[] = "default_request_qty > total_do_qty";
		  $filter[] = "po_qty > 0";
		  break;
		default:
			die('Invalid Page');
	}
	$filter[] = "dri.request_branch_id=".mi($request_branch_id);
	$filter[] = "dri.active=1";
	$filter[] = "dri.branch_id=$branch_id";
	$filter[] = "c.department_id in ($sessioninfo[department_ids])";
	
	// filter by expect do date
	if(!$config['do_request_no_expected_delivery_date']){
		if($expected_do_date_from)	$filter[] = "dri.expect_do_date >= ".ms($expected_do_date_from);
		if($expect_do_date_to)	$filter[] = "dri.expect_do_date <= ".ms($expect_do_date_to);
		
		// check maximum expect do date to filter
		/*if($config['do_request_process_maximum_filter_expect_delivery_date_day']){
			$pattern = "+".mi($config['do_request_process_maximum_filter_expect_delivery_date_day']);

			$max_expect_do_date = date("Y-m-d", strtotime("$pattern day", time()));
			$filter[] = "dri.expect_do_date <= ".ms($max_expect_do_date);
		}*/
	}
	
	$filter = "where ".join(' and ',$filter);
	$sql = "select count(*) from do_request_items dri 
	left join sku_items si on si.id=dri.sku_item_id
	left join sku on sku.id=si.sku_id
	left join category c on c.id=sku.category_id
	left join branch b on b.id=dri.request_branch_id $filter";
	//print $sql;
	$con->sql_query($sql) or die(mysql_error());
	$total_rows = $con->sql_fetchfield(0);
	
	if($start>=$total_rows){
		$start = 0;
		$_REQUEST['p'] = 0;
	}
	$limit = "limit $start, $size";
	$order = "order by dri.last_update desc";
	
	if($sort_by && $sort_order){
		switch($sort_by){
			case 'category':
				$order = "order by sku.category_id $sort_order, cc.p3 $sort_order, cc.p2 $sort_order";
				break;
			case 'department':
				$order = "order by c2.description $sort_order, dri.last_update desc";
				break;
			default:
				$order = "order by $sort_by $sort_order";
				break;
		}
		
	}

	$total_page = ceil($total_rows/$size);
	
	if($clear_selected){
		$con->sql_query("update do_request_items dri 
		left join sku_items si on si.id=dri.sku_item_id 
		left join sku on sku.id=si.sku_id 
		left join category c on c.id=sku.category_id 
		left join branch b on b.id=dri.request_branch_id
		set dri.selected=0
		$filter");
	}
    
    if($t == 1 && $config['do_request_not_allow_if_no_stock']) {
        //get all do_request_items
        $dri_query = $con->sql_query("select dri.id, dri.branch_id, dri.sku_item_id, dri.stock_balance2, dri.group_stock_balance2, si.sku_id
                                     from do_request_items dri
                                     left join sku_items si on si.id=dri.sku_item_id
                                     left join sku on sku.id=si.sku_id
                                     left join category c on c.id=sku.category_id
                                     left join branch b on b.id=dri.request_branch_id
                                     $filter");
        while($r = $con->sql_fetchassoc($dri_query)) {
            $stock_balance = $group_stock_balance = 0;
            //get current qty of sku items
            $sic_query = $con->sql_query("select sic.sku_item_id, sic.qty, uom.fraction as uom_fraction
                                         from sku_items_cost sic
                                         join sku_items si on si.id=sic.sku_item_id
                                         left join uom on uom.id=si.packing_uom_id
                                         where sic.branch_id=$request_branch_id and si.sku_id=".mi($r['sku_id']));
                
            while($q = $con->sql_fetchassoc($sic_query)) {
                $group_stock_balance += $q['qty'] * $q['uom_fraction'];
                if($r['sku_item_id'] == $q['sku_item_id']){
                    $stock_balance = $q['qty'];
                }
            }
            $con->sql_freeresult($sic_query);
            
            //update qty if current qty and do request's supply branch's stock balance is different
            if($stock_balance != $r['stock_balance2'] || $group_stock_balance != $r['group_stock_balance2']) {
                $con->sql_query("update do_request_items set stock_balance2 = $stock_balance, group_stock_balance2 = $group_stock_balance where branch_id = ".$r['branch_id']." and id=".$r['id']);    
            }
        }
        $con->sql_freeresult($dri_query);
    }
    
	$sql = "select dri.*,si.sku_item_code,si.artno,si.mcode,si.description,b.code as request_branch_code,b2.code as branch_code,user.u,u2.u as request_by,user_reject.u as reject_by_user, uom.code as packing_uom_code, c2.description as department
	from do_request_items dri 
	left join sku_items si on si.id=dri.sku_item_id
	left join uom on uom.id=si.packing_uom_id
	left join sku on sku.id=si.sku_id
	left join category c on c.id=sku.category_id
	left join category_cache cc on cc.category_id=c.id
	left join category c2 on c2.id = cc.p2
	left join branch b on b.id=dri.request_branch_id
	left join branch b2 on b2.id=dri.branch_id
	left join user on user.id=dri.print_picking_list_by
	left join user u2 on u2.id=dri.user_id
	left join user user_reject on user_reject.id=dri.reject_by
	$filter $order $limit";
	//print $sql;
	
	$con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow()){
		$r['do_list'] = unserialize($r['do_list']);
		$r['sales_trend'] = unserialize($r['sales_trend']);
		
		// make float to prevent number_format warning at php v5.3.2
		$r['sales_trend']['qty'][1] = mf($r['sales_trend']['qty'][1]);
		$r['sales_trend']['qty'][3] = mf($r['sales_trend']['qty'][3]);
		$r['sales_trend']['qty'][6] = mf($r['sales_trend']['qty'][6]);
		$r['sales_trend']['qty'][12] = mf($r['sales_trend']['qty'][12]);
		
		$items[] = $r;
	}
	$con->sql_freeresult();
	$smarty->assign('total_item_count',$total_rows);
	$smarty->assign('items',$items);
	$smarty->assign('total_page',$total_page);
	$smarty->assign('item_counter',$start+1);
	
	$smarty->display('do_request.process.table.tpl');
}

function print_picking_list(){
	global $con, $smarty, $sessioninfo, $request_branch_id, $config;
	
	//print_r($_REQUEST);
	$is_new = true;
	$from_branch_id = mi($_REQUEST['from_branch']);
	$picking_list_id = mi($_REQUEST['picking_list_id']);
	if(!$from_branch_id){
		die("<script>alert('Please select from branch.')</script>");
	}
	if(!$request_branch_id){
		die("<script>alert('No Request branch selected.')</script>");
	}
	
	if($config['do_request_process_restrict_print_if_no_stock'])
	{
		$con->sql_query("select stock_balance2
						from do_request_items
						where status=0 and selected=1 and active=1 and branch_id=$from_branch_id and request_branch_id=$request_branch_id");
		while($data = $con->sql_fetchassoc()) {
			if($data['stock_balance2']<=0) die("<script>alert('You are not allow to print picking list due to some SKU from supply branch currently out of stock.')</script>");
		}
		$con->sql_freeresult();
	}
	
	//$extra = $_REQUEST['extra'];
	if ($_REQUEST['exported_to_po']=='1') {
		$filter[] = "dri.status=2";
		//$filter[] = "default_request_qty > total_do_qty";
		$filter[] = "po_qty > 0";
	}
	elseif($picking_list_id){
		//$filter[] = "dri.status=1 and print_picking_list_by=".mi($sessioninfo['id'])." and picking_list_id=$picking_list_id";
		$filter[] = "dri.status=1 and picking_list_id=$picking_list_id";
	}else{
		$filter[] = "dri.status=0";
		$filter[] = "c.department_id in ($sessioninfo[department_ids])";
	}
	$filter[] = "dri.selected=1";
	$filter[] = "dri.active=1 and dri.branch_id=$from_branch_id and dri.request_branch_id=$request_branch_id";
	$filter = "where ".join(' and ',$filter);
	
	if ($_REQUEST['exported_to_po']=='1') {
		$con->sql_query("update do_request_items dri set dri.request_qty = dri.default_request_qty - dri.total_do_qty $filter") or die(mysql_error());
	}
	
	$sql = "select dri.*,si.sku_item_code,si.artno,si.location,si.mcode,si.description, sic.qty as latest_stock_bal,
				((select sum(sic2.qty*pu.fraction)
				from sku_items si2
				join sku_items_cost sic2 on sic2.branch_id=$request_branch_id and sic2.sku_item_id=si2.id
				left join uom pu on pu.id=si2.packing_uom_id
				where si2.sku_id=si.sku_id)/uom.fraction) as latest_group_stock_bal
			from do_request_items dri 
			left join sku_items si on si.id=dri.sku_item_id
			left join uom on uom.id=si.packing_uom_id
			left join sku on sku.id=si.sku_id
			left join category c on c.id=sku.category_id
			left join category_cache cc on cc.category_id=sku.category_id
			left join sku_items_cost sic on sic.branch_id=dri.request_branch_id and sic.sku_item_id=dri.sku_item_id
			$filter order by sku.category_id,cc.p3,cc.p2";
	//print $sql;
	$con->sql_query($sql) or die(mysql_error());
	$total_rows = $con->sql_numrows();
	if(!$total_rows)	die("<script>alert('No item to print.')</script>");
	$id_list = array();
	while($r = $con->sql_fetchrow()){
		$items[] = $r;
		$id_list[] = $r['id'];
	}
	
	$upd_status = ($_REQUEST['exported_to_po']=='1') ? ',dri.status=0':'';
	
	if($config['do_print_picking_list_sort_by_loc']){
		usort($items, 'sort_location');
	}
	
	// new picking list
	if(!$picking_list_id) {
		$upd = array();
		$upd['branch_id'] = $request_branch_id;
		$upd['do_branch_id'] = $from_branch_id;
		$upd['user_id'] = $sessioninfo['id'];
		$upd['added'] = 'CURRENT_TIMESTAMP';
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("insert into picking_list ".mysql_insert_by_field($upd)) or die(mysql_error());
		$picking_list_id = $con->sql_nextid();
		
		$con->sql_query("update do_request_items dri 
			left join sku_items si on si.id=dri.sku_item_id
			left join sku on sku.id=si.sku_id
			left join category c on c.id=sku.category_id
			set dri.picking_list_id=$picking_list_id $upd_status $filter") or die(mysql_error());
	}else {
		$is_new = false;
	}
	
	$smarty->assign('pid',$picking_list_id);
	$con->sql_query("select added from picking_list where id=$picking_list_id and branch_id=$request_branch_id");
	$smarty->assign('pdate',$con->sql_fetchfield(0));
	//print_r($items);
	
	$items_per_page = mi($config['do_request_print_picking_list_size'])>5 ? mi($config['do_request_print_picking_list_size']) : 30;
	$items_per_last_page = $items_per_page - 5;
	$total_page = ceil($total_rows/$items_per_page);
	$last_page_item_count = $total_rows%$items_per_page;
	if($last_page_item_count>$items_per_last_page)	$total_page++;
	
	// branches info
	$con->sql_query("select * from branch where id in ($from_branch_id,$request_branch_id)") or die(mysql_error());
	
	while($r = $con->sql_fetchrow()) {
		//$key = ($r['id']==$from_branch_id) ? 'from_branch' : 'to_branch';
		$key = ($r['id']==$sessioninfo['branch_id']) ? 'from_branch' : 'to_branch';
		
		$branches_info[$key] = $r;
	}
	$smarty->assign('branches_info', $branches_info);
	
	$printing_tpl = $config['do_request_alt_print_picking_list'] ? $config['do_request_alt_print_picking_list'] : 'do_request.print_picking_list.tpl';

	for($page=1; $page<=$total_page; $page++){
		$smarty->assign("PAGE_SIZE", ($page < $total_page) ? $items_per_page : $items_per_last_page);
		$smarty->assign("is_lastpage", ($page >= $total_page));
		$smarty->assign("page", "Page $page of $total_page");
		$start_counter = ($page-1)*$items_per_page;
		$smarty->assign("start_counter", $start_counter);
		$smarty->assign("items", array_slice($items,$start_counter,$items_per_page));
		$smarty->display($printing_tpl);
		$smarty->assign("skip_header",1);
	}
	
	if($is_new){	//  first time print
		$upd = array();
		$upd['print_picking_list_by'] = mi($sessioninfo['id']);
		$upd['status'] = 1;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update do_request_items set ".mysql_update_by_field($upd)." 
		where branch_id=$from_branch_id and request_branch_id=$request_branch_id
		and status=0 and active=1 and id in (".join(',',$id_list).")") or die(mysql_error());
		//log_br($sessioninfo['id'], 'DO Request', '', "DO Request items move to processing by $sessioninfo[u]");
		log_br($sessioninfo['id'], 'DO Request', $picking_list_id, "Print Picking List, Picking List ID: $picking_list_id, Process by $sessioninfo[u]");
	}else{
        log_br($sessioninfo['id'], 'DO Request', $picking_list_id, "Print Picking List, Picking List ID: $picking_list_id");
	}
}

function init_selection2(){
	global $con, $smarty, $request_branch_id;
	
	$sql = "select count(*) as item_count,dri.branch_id from do_request_items dri 
where dri.request_branch_id=$request_branch_id and dri.active=1 and dri.status<2
group by dri.branch_id";
	$con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow()){
		$branches_items[$r['branch_id']] = $r;
	}
	$smarty->assign('branches_items',$branches_items);
}

function ajax_update_field(){
	global $con;
	
	$id = mi($_REQUEST['id']);
	$branch_id = mi($_REQUEST['branch_id']);
	$qty = mf($_REQUEST['qty']);
	$data_type = $_REQUEST['data_type'];
	
	if($qty<0)	die('Invalid Qty');
	
	switch($data_type){
		case 'do_qty':
			$upd['do_qty'] = $qty;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			break;
	}
	
	$con->sql_query("update do_request_items set ".mysql_update_by_field($upd)." where id=$id and branch_id=$branch_id and active=1") or die(mysql_error());
	if($con->sql_affectedrows())	print "OK";
	else 	print "Update does not effect anything";
	exit;
}

function generate_do($pid, $branch_id, $request_branch_id){
	global $con, $sessioninfo, $config;
	
	//$branch_id = mi($_REQUEST['from_branch']);
	
	$do_date = date("Y-m-d");
	
	$form = array();
	$form['branch_id'] = $request_branch_id;
	$form['do_branch_id'] = $branch_id;
	$form['user_id'] = $sessioninfo['id'];
	$form['last_update'] = 'CURRENT_TIMESTAMP';
	$form['added'] = 'CURRENT_TIMESTAMP';
	$form['do_date'] = $do_date;
	
	$q1 = $con->sql_query("select * from branch where id = ".mi($branch_id));
	$request_branch_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	if($request_branch_info['type'] == "franchise"){
		$form['do_type'] = 'credit_sales';
		$form['debtor_id'] = $request_branch_info['debtor_id'];
		$form['price_indicate'] = 2;
		$form['do_branch_id'] = 0;
	}else{
		$form['do_type'] = 'transfer';
		if($config['do_default_price_from']=='cost')    $form['price_indicate'] = 1;
		elseif($config['do_default_price_from']=='last_do')    $form['price_indicate'] = 3;
		else    $form['price_indicate'] = 2;
	}
	
	if($config['enable_gst']){
		$form['is_under_gst'] = $is_under_gst = check_do_gst_status($form);
	}
	
	$filter[] = "dri.branch_id=$branch_id";
	$filter[] = "dri.request_branch_id=$request_branch_id";
	$filter[] = "dri.print_picking_list_by=".$sessioninfo['id'];
	//$filter[] = "dri.active=1 and dri.status=1 and dri.do_qty>0";
	$filter[] = "dri.active=1 and dri.status=1";
	$filter[] = "dri.picking_list_id=$pid";
	
	$filter = "where ".join(' and ', $filter);
	$sql = "select dri.* ,si.packing_uom_id,ifnull(sip.price,si.selling_price) as sp, ifnull(sic.grn_cost,si.cost_price) as cost,si.artno,si.mcode
	,ifnull(sip.trade_discount_code,sku.default_trade_discount_code) as price_type
from do_request_items dri 
left join sku_items si on si.id=dri.sku_item_id
left join sku on sku.id=si.sku_id
left join sku_items_price sip on sip.sku_item_id=dri.sku_item_id and sip.branch_id=$request_branch_id
left join sku_items_cost sic on sic.sku_item_id=dri.sku_item_id and sic.branch_id=$request_branch_id
$filter";
	
	$con->sql_query($sql) or die(mysql_error());
	$items = array();
	$total_pcs = 0;
	$total_amount = 0;
	while($r = $con->sql_fetchrow()){
		if($r['do_qty']>0){
			$items[$r['sku_item_id']]['sku_item_id'] = $r['sku_item_id'];
			//$items[$r['sku_item_id']]['uom_id'] = $r['packing_uom_id'];
			$items[$r['sku_item_id']]['uom_id'] = 1;
			$items[$r['sku_item_id']]['selling_price'] = $r['sp'];
			$items[$r['sku_item_id']]['cost'] = $r['cost'];
			$items[$r['sku_item_id']]['artno_mcode'] = $r['artno']?$r['artno']:$r['mcode'];
			$items[$r['sku_item_id']]['price_type'] = array(mi($branch_id)=>$r['price_type']);
			$items[$r['sku_item_id']]['pcs'] += $r['do_qty'];
			$total_pcs += $r['do_qty'];
		}
		
		$r['do_list'] = unserialize($r['do_list']);
		$do_request_items[] = $r;
	}
	$con->sql_freeresult();
	
	if($items){
		// get stock balance
		$tbl_from = "stock_balance_b".mi($request_branch_id)."_".date('Y');
		$tbl_to = "stock_balance_b".mi($branch_id)."_".date('Y');
		$price_type = $config['do_default_price_from'] ? $config['do_default_price_from']:'selling';
		
		foreach($items as $sku_item_id=>$r){
			// stock balance - from branch
			//$con->sql_query("select qty from $tbl_from where sku_item_id=".mi($sku_item_id)." and is_latest=1 limit 1") or die(mysql_error());
			$con->sql_query("select qty from sku_items_cost where branch_id=$request_branch_id and sku_item_id=".mi($sku_item_id)) or die(mysql_error());
			$items[$sku_item_id]['stock_balance1'] = $con->sql_fetchfield(0);
			$con->sql_freeresult();
			
			// stock balance - to branch
			//$con->sql_query("select qty from $tbl_to where sku_item_id=".mi($sku_item_id)." and is_latest=1 limit 1") or die(mysql_error());
			$con->sql_query("select qty from sku_items_cost where branch_id=$branch_id and sku_item_id=".mi($sku_item_id)) or die(mysql_error());
			$items[$sku_item_id]['stock_balance2'] = $con->sql_fetchfield(0);
			$con->sql_freeresult();
			
			// get cost price	
			if($price_type=='last_do'){
				// get last DO price
				$q_p = $con->sql_query("select (di.cost_price/uom.fraction) as cost_price, (di.display_cost_price/uom.fraction) as display_cost_price, di.display_cost_price_is_inclusive
				from do_items di
				left join do on di.do_id = do.id and di.branch_id = do.branch_id 
				left join uom on uom.id=di.uom_id
				where do.active=1 and di.sku_item_id=".mi($sku_item_id)." and di.branch_id=$request_branch_id order by di.id desc limit 1");
			}
			elseif($price_type=='selling'){
				// get selling price
				$q_p = $con->sql_query("select price as cost_price from sku_items_price where sku_item_id=".mi($sku_item_id)." and branch_id=$request_branch_id");
			}
			elseif($price_type=='cost'){
				// cost
				$q_p = $con->sql_query("select grn_cost as cost_price from sku_items_cost where sku_item_id=".mi($sku_item_id)." and branch_id=$request_branch_id");
			}
			$temp_p = $con->sql_fetchrow($q_p);
			if(!$temp_p){
				if ($price_type=='last_do' or $price_type=='cost'){ // DO or GRN selected
					$q_m = $con->sql_query("select if(grn_cost is null, cost_price, grn_cost) as cost_price from  sku_items left join sku_items_cost on sku_item_id=sku_items.id and branch_id=$request_branch_id where id=".mi($sku_item_id));
				}
				else
				{
					$q_m = $con->sql_query("select if(price is null, selling_price, price) as cost_price from sku_items left join sku_items_price on sku_item_id=sku_items.id and branch_id=$request_branch_id where id=".mi($sku_item_id));
				}
				$temp_p = $con->sql_fetchrow($q_m);
			}
			
			$cost_price = $temp_p['cost_price'];
			$display_cost_price = isset($temp_p['display_cost_price']) ? $temp_p['display_cost_price'] : $cost_price;
			$display_cost_price_is_inclusive = isset($temp_p['display_cost_price_is_inclusive']) ? $temp_p['display_cost_price_is_inclusive'] : 0;
			
			if($config['enable_gst']){
				// get sku inclusive tax
				$is_sku_inclusive = get_sku_gst("inclusive_tax", $sku_item_id);
				
				// get sku original output gst
				$output_gst = get_sku_gst("output_tax", $sku_item_id);
				if($output_gst && $is_under_gst){
					if($price_type == 'selling'){
						if($is_sku_inclusive == 'yes'){
							// is inclusive tax
							$price_included_gst = $cost_price;
							$display_cost_price_is_inclusive = 1;
							$display_cost_price = $price_included_gst;
							
							// find the selling price before tax
							$gst_amt = $price_included_gst / ($output_gst['rate']+100) * $output_gst['rate'];
							$before_tax_price = $price_included_gst - $gst_amt;
						}else{
							// is exclusive tax
							$before_tax_price = $cost_price;
							$gst_amt = $before_tax_price * $output_gst['rate'] / 100;
							$price_included_gst = $before_tax_price + $gst_amt;
						}
						
						// cost price need to use before gst for selling price
						$cost_price = $before_tax_price;
					}
					
					
					$items[$sku_item_id]['gst_id'] = $output_gst['id'];
					$items[$sku_item_id]['gst_code'] = $output_gst['code'];
					$items[$sku_item_id]['gst_rate'] = $output_gst['rate'];
					$items[$sku_item_id]['display_cost_price_is_inclusive'] = $display_cost_price_is_inclusive;
					$items[$sku_item_id]['display_cost_price'] = $display_cost_price;
					
				}
			}
			
			$items[$sku_item_id]['cost_price'] = $cost_price;
			//$total_amount += ($r['pcs']*$cost_price);
		}
	}else{
		js_redirect(sprintf('No item to generate, please enter some deliver Qty.', 'DO_REQUEST_PROCESS', BRANCH_CODE), "$_SERVER[PHP_SELF]?a=open_picking_list&pid=$pid&branch_id=$request_branch_id");
	}

	$form['total_pcs'] = $total_pcs;
	//$form['total_inv_amt'] = $form['total_amount'] = $total_amount;
	
	$con->sql_query("insert into do ".mysql_insert_by_field($form)) or die(mysql_error());
	$do_id = $con->sql_nextid();
	
	foreach($items as $sku_item_id=>$upd){
		$upd['do_id'] = $do_id;
		$upd['branch_id'] = $request_branch_id;
		if($upd['price_type']) $upd['price_type'] = serialize($upd['price_type']);
		$con->sql_query("insert into do_items ".mysql_insert_by_field($upd)) or die(mysql_error());
	}
	
	// recalculate all amt
	auto_update_do_all_amt($form['branch_id'], $do_id);
	
	foreach($do_request_items as $r){
		$upd = array();
		$upd['do_list'] = $r['do_list'];
		if($r['do_qty']>=$r['request_qty']){
			$upd['status'] = 2;
			$upd['request_qty'] = 0;
		}else{
			$upd['status'] = 0;
			$upd['request_qty'] = $r['request_qty']-$r['do_qty'];
		}
		$upd['do_list'][] = $do_id;
		$upd['do_qty'] = 0;
		$upd['total_do_qty'] = $r['total_do_qty']+$r['do_qty'];
		$upd['do_list'] = serialize($upd['do_list']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		//$upd['picking_list_id'] = '';
		$con->sql_query("update do_request_items set ".mysql_update_by_field($upd)." where id=$r[id] and branch_id=$r[branch_id]") or die(mysql_error());
	}
	$con->sql_query("update picking_list set active=0 where id=$pid and branch_id=$request_branch_id") or die(mysql_error());
	
	log_br($sessioninfo['id'], 'DO Request', $pid, "Generate DO from Picking List, Picking List ID: $pid, DO ID: $do_id");
	
	//header("Location: do.php?a=open&id=$do_id&branch_id=$request_branch_id&do_type=transfer");
	header("Location: $_SERVER[PHP_SELF]?branch_id=$branch_id&do_id=$do_id&branch_id2=$request_branch_id&do_type=transfer");
	exit;
}

function ajax_generate_po(){
	global $con, $sessioninfo, $request_branch_id, $config, $appCore;
	
	$branch_id = mi($_REQUEST['from_branch']);
	$item_id = $_REQUEST['do_request_item_id'];
	$vendor_id = mi($_REQUEST['vendor_id']);
	$po_date = date("Y-m-d");
	
	if(!$item_id){
		print json_encode(array('error'=>'Please select at least one item.'));
		exit;
	}
	$filter[] = "dri.branch_id=$branch_id";
	$filter[] = "dri.request_branch_id=$request_branch_id";
	//$filter[] = "dri.print_picking_list_by=".$sessioninfo['id'];
	$filter[] = "dri.active=1 and dri.status=0 and dri.selected=1";
	$filter[] = "dri.id in (".join(',',$item_id).")";
	$filter = "where ".join(' and ', $filter);
	$sql = "select dri.* ,si.packing_uom_id,ifnull(sip.price,si.selling_price) as sp, ifnull(sip.cost,si.cost_price) as cost,si.artno,si.mcode
	,ifnull(sip.trade_discount_code,sku.default_trade_discount_code) as price_type,uom.fraction as uom_fraction,si.cost_price as master_cost
from do_request_items dri 
left join sku_items si on si.id=dri.sku_item_id
left join sku on sku.id=si.sku_id
left join sku_items_price sip on sip.sku_item_id=dri.sku_item_id and sip.branch_id=$request_branch_id
left join uom on uom.id=si.packing_uom_id
$filter";
	//print $sql;die();
	
	$con->sql_query($sql) or die(mysql_error());
	$items = array();
	$po_amount = 0;
	while($r = $con->sql_fetchrow()){			
		$items[$r['sku_item_id']]['sku_item_id'] = $r['sku_item_id'];
		//$items[$r['sku_item_id']]['selling_uom_id'] = $r['packing_uom_id'];
		$items[$r['sku_item_id']]['selling_uom_id'] = 1;
		//$items[$r['sku_item_id']]['order_uom_id'] = $r['packing_uom_id'];
		$items[$r['sku_item_id']]['order_uom_id'] = 1;
		//$items[$r['sku_item_id']]['selling_uom_fraction'] = $r['uom_fraction'];
		$items[$r['sku_item_id']]['selling_uom_fraction'] = 1;
		//$items[$r['sku_item_id']]['order_uom_fraction'] = $r['uom_fraction'];
		$items[$r['sku_item_id']]['order_uom_fraction'] = 1;
		$items[$r['sku_item_id']]['selling_price'] = $r['sp'];
		$items[$r['sku_item_id']]['artno_mcode'] = $r['artno']?$r['artno']:$r['mcode'];
		$items[$r['sku_item_id']]['qty_loose'] += $r['request_qty'];
		
		$do_request_items[] = $r;
		$sku_items[$r['sku_item_id']]['master_cost'] = $r['master_cost'];
	}
	$con->sql_freeresult();
	
	if($items){
		if($config['enable_gst']){
			$prms = array();
			$prms['vendor_id'] = $vendor_id;
			$prms['date'] = $po_date;
			$is_under_gst = check_gst_status($prms);
			
			if($is_under_gst){
				// check vendor gst
				$vendor_special_gst = get_vendor_special_gst_settings($vendor_id);
			}
			
			$prms = array();
			$prms['branch_id'] = $sessioninfo['branch_id'];
			$prms['date'] = $po_date;
			$branch_is_under_gst = check_gst_status($prms);
		}
		
		// get stock balance
		$tbl_from = "stock_balance_b".mi($request_branch_id)."_".date('Y');
		//$tbl_to = "stock_balance_b".mi($branch_id)."_".date('Y');
		
		foreach($items as $sku_item_id=>$r){
			// stock balance - from branch
			$con->sql_query("select qty from $tbl_from where sku_item_id=".mi($sku_item_id)." and is_latest=1 limit 1") or die(mysql_error());
			$items[$sku_item_id]['stock_balance'] = array($request_branch_id=>$con->sql_fetchfield(0));
			// stock balance - to branch
			//$con->sql_query("select qty from $tbl_to where sku_item_id=".mi($sku_item_id)." and is_latest=1 limit 1") or die(mysql_error());
			//$items[$sku_item_id]['stock_balance2'] = $con->sql_fetchfield(0);
			
			// get order price
			//get from grn
			$branch_chk=" grn_items.branch_id=$request_branch_id and ";
			$result = $con->sql_query("select sku_items.*, grn.vendor_id as vendor_id, if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost) as order_price, grn_items.uom_id as uom_id, sku_items.packing_uom_id as selling_uom_id, uom.fraction as order_uom_fraction, u1.fraction as selling_uom_fraction,sku_items.packing_uom_id as master_uom_id
		from grn_items
		left join sku_items on sku_item_id = sku_items.id
		left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
		left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
		left join uom u1 on sku_items.packing_uom_id = u1.id
		left join uom on uom_id = uom.id
		where $branch_chk grn.approved=1 and sku_item_id=$sku_item_id 
		having order_price>0
		order by grr.rcv_date desc, grr.id desc limit 1");
		$selection="GRN";
		
			if($con->sql_numrows()==0){
				$branch_chk = " po_items.branch_id=$request_branch_id and ";
				//get from po
				$result=$con->sql_query("select sku_items.*, po.vendor_id as vendor_id, po_items.order_price as order_price, po_items.order_uom_id as uom_id, sku_items.packing_uom_id as selling_uom_id, uom.fraction as order_uom_fraction, u1.fraction as selling_uom_fraction,sku_items.packing_uom_id as master_uom_id
		from po_items
		left join sku_items on sku_item_id = sku_items.id
		left join po on po_id = po.id and po.branch_id = po.branch_id
		left join uom u1 on sku_items.packing_uom_id = u1.id
		left join uom on order_uom_id = uom.id
		where po.active=1 and po.approved=1 and $branch_chk sku_item_id=$sku_item_id 
		having order_price>0 
		order by po.po_date desc, po.id desc limit 1");
		//po_items.selling_price as selling_price, 
				$selection="PO";
			}
			
			/*if($con->sql_numrows()==0){
				//get from master
				$result=$con->sql_query("select sku.vendor_id, sku_items.*, cost_price as order_price, cost_price as resell_price, u1.fraction as selling_uom_fraction, sku_items.packing_uom_id as selling_uom_id ,sku_items.packing_uom_id as master_uom_id
		from sku_items 
		left join sku on sku_id = sku.id 
		left join uom u1 on sku_items.packing_uom_id = u1.id
		where sku_items.id = $sku_item_id");
				$selection="SKU";
			}*/
			$cost_1 = $con->sql_fetchrow($result);
			if(!$cost_1){
				$items[$sku_item_id]['order_price'] = $sku_items[$sku_item_id]['master_cost'];
				$selection="SKU";
			}	
			else 	$items[$sku_item_id]['order_price'] = $cost_1['order_price'];
	
			$items[$sku_item_id]['cost_indicate'] = $selection;
			
			$sales = get_sales_trend($request_branch_id, $sku_item_id);
			$items[$sku_item_id]['sales_trend'] = $sales['sales_trend'];
			$items[$sku_item_id]['selling_price'] = $r['selling_price'];
			
			if($config['enable_gst']){
				if($branch_is_under_gst){
					$is_sku_inclusive = get_sku_gst("inclusive_tax", $sku_item_id);
					$output_gst = get_sku_gst("output_tax", $sku_item_id);
					$items[$sku_item_id]['selling_gst_id'] = $output_gst['id'];
					$items[$sku_item_id]['selling_gst_code'] = $output_gst['code'];
					$items[$sku_item_id]['selling_gst_rate'] = $output_gst['rate'];
				
					$prms = array();
					$prms['selling_price'] = $r['selling_price'];
					$prms['inclusive_tax'] = $is_sku_inclusive;
					$prms['gst_rate'] = $items[$sku_item_id]['selling_gst_id'];
					$gst_sp_info = calculate_gst_sp($prms);
					$items[$sku_item_id]['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					
					if($is_sku_inclusive == "yes"){
						$items[$sku_item_id]['gst_selling_price'] = $r['selling_price'];
						$items[$sku_item_id]['selling_price'] = $gst_sp_info['gst_selling_price'];
					}else{
						$items[$sku_item_id]['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					}
				}
				
				// got gst
				if($is_under_gst){
					if($vendor_special_gst){
						$items[$sku_item_id]['cost_gst_id'] = $vendor_special_gst['id'];
						$items[$sku_item_id]['cost_gst_code'] = $vendor_special_gst['code'];
						$items[$sku_item_id]['cost_gst_rate'] = $vendor_special_gst['rate'];
					}else{
						$input_gst = get_sku_gst("input_tax", $sku_item_id);
						if($input_gst){
							$items[$sku_item_id]['cost_gst_id'] = $input_gst['id'];
							$items[$sku_item_id]['cost_gst_code'] = $input_gst['code'];
							$items[$sku_item_id]['cost_gst_rate'] = $input_gst['rate'];
						}
					}
					
				}
			}
			
			$gross_amt = $items[$sku_item_id]['order_price']*$r['qty_loose'];
			$row_gst_amt = 0;
			if($is_under_gst && $items[$sku_item_id]['cost_gst_rate']>0){
				$row_gst_amt = round($gross_amt * $items[$sku_item_id]['cost_gst_rate'] / 100, 2);
			}
			$row_amt = $gross_amt + $row_gst_amt;
			$po_amount += $row_amt;
		}
	}else{
		die('No item to generate, please enter some deliver Qty.');
	}
	
	//print_r($items);
	// generate the PO
	$form = array();
	$form['branch_id'] = $sessioninfo['branch_id'];
	if (BRANCH_CODE=='HQ')
	{
		$form['po_branch_id'] = $branch_id;	
		$form['po_option'] = 3;
	}
	$form['remark'] = serialize(array('From '.get_branch_code($branch_id).' DO Request'));
	$form['user_id'] = $sessioninfo['id'];
	$form['vendor_id'] = $vendor_id;
	$form['last_update'] = 'CURRENT_TIMESTAMP';
	$form['added'] = 'CURRENT_TIMESTAMP';
	$form['po_date'] = $po_date;
	$form['po_amount'] = $po_amount;
	$form['is_under_gst'] = $is_under_gst;
	
	$con->sql_query("insert into po ".mysql_insert_by_field($form)) or die(mysql_error());
	$po_id = $con->sql_nextid();
	
	foreach($items as $sku_item_id=>$upd){
		$upd['po_id'] = $po_id;
		$upd['branch_id'] = $sessioninfo['branch_id'];
		$upd['user_id'] = $sessioninfo['id'];
		$upd['stock_balance'] = serialize($upd['stock_balance']);
		$upd['sales_trend'] = serialize($upd['sales_trend']);

		$con->sql_query("insert into po_items ".mysql_insert_by_field($upd)) or die(mysql_error());
	}
	//$appCore->poManager->reCalcatePOUsingOldMethod($form['branch_id'], $po_id);
	$appCore->poManager->reCalcatePOAmt($form['branch_id'], $po_id);
	
	// update do_request_items
	foreach($do_request_items as $r){
		$upd = array();
		$upd['po_id'] = $po_id;
		$upd['status'] = 2;
		$upd['request_qty'] = 0;
		$upd['po_qty'] = $r['request_qty'];
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update do_request_items set ".mysql_update_by_field($upd)." where id=$r[id] and branch_id=$r[branch_id]") or die(mysql_error());
	}
	
	log_br($sessioninfo['id'], 'DO Request', $po_id, "Generate PO, PO ID: $po_id");
	
	$ret = array('success'=>true,'po_id'=>$po_id, 'branch_id'=>$sessioninfo['branch_id']);
	print json_encode($ret);
	exit;
}

function ajax_show_generate_po(){
	global $con, $smarty, $sessioninfo, $request_branch_id, $config;
	
	$branch_id = mi($_REQUEST['from_branch']);
	
	$filter[] = "dri.branch_id=$branch_id";
	$filter[] = "dri.request_branch_id=$request_branch_id";
	//$filter[] = "dri.print_picking_list_by=".$sessioninfo['id'];
	$filter[] = "dri.active=1 and dri.status=0 and dri.selected=1";
	//$filter[] = "dri.do_list<>''";
	$filter[] = "dri.picking_list_id>0";
	$filter = "where ".join(' and ', $filter);
	
	// get all distinct sku item id
	$sql = "select distinct(sku_item_id),sku.vendor_id
from do_request_items dri 
left join sku_items si on si.id=dri.sku_item_id
left join sku on sku.id=si.sku_id
$filter";
	//print $sql;
	$con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow()){
		$sku_item_id_list[] = $r['sku_item_id'];
		$vendor_id_list[$r['vendor_id']] = mi($r['vendor_id']);
	}
	$con->sql_freeresult();
	
	// No item found
	if(!$sku_item_id_list) die('<p> <img src="/ui/flag.png" align="absmiddle" />No Item Selected to Generate PO</p><br />Only the item which has been processed can be generate to PO.');
	
	// get all distinct vendor_id
	//$sql = "select distinct(vendor_id) as vendor_id from vendor_sku_history where sku_item_id in (".join(',',$sku_item_id_list).")";
	$sql = "select distinct(vendor.id) as vendor_id
from po_items
left join po on po_items.po_id = po.id and po_items.branch_id = po.branch_id
left join vendor on vendor.id = po.vendor_id and vendor.active=1
where po.status=1 and po.approved and (po.branch_id=$request_branch_id or po.po_branch_id=$request_branch_id) 
and po_items.sku_item_id in (".join(',',$sku_item_id_list).") and po.po_no is not null order by po.added desc";
	//print $sql;
	$con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow()){
		$vendor_id_list[$r['vendor_id']] = mi($r['vendor_id']);
	}
	$con->sql_freeresult();
	
	// get all vendor information
	$sql = "select id,code,description from vendor where id in (".join(',',$vendor_id_list).") and active=1";
	$con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow()){
		$vendor_info[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	$smarty->assign('vendor_info',$vendor_info);
	$smarty->display('do_request.show_generate_po.tpl');
	exit;
}

function ajax_load_vendor_sku(){
	global $con, $smarty, $request_branch_id;
	
	$vendor_id = mi($_REQUEST['vendor_id']);
	$branch_id = mi($_REQUEST['from_branch']);
	
	$filter[] = "dri.branch_id=$branch_id";
	$filter[] = "dri.request_branch_id=$request_branch_id";
	//$filter[] = "dri.print_picking_list_by=".$sessioninfo['id'];
	$filter[] = "dri.active=1 and dri.status=0 and dri.selected=1";
	//$filter[] = "dri.do_list<>''";
	$filter[] = "dri.picking_list_id>0";
	$filter = "where ".join(' and ', $filter);
	
	// get all distinct sku item id
	$sql = "select distinct(sku_item_id),sku.vendor_id
from do_request_items dri 
left join sku_items si on si.id=dri.sku_item_id
left join sku on sku.id=si.sku_id
$filter";
	$con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow()){
		$sku_item_id_list[] = $r['sku_item_id'];
		if($r['vendor_id']==$vendor_id)	$vendor_sku_item_id_list[$r['sku_item_id']] = $r['sku_item_id'];
	}
	$con->sql_freeresult();
	
	// No item found
	if(!$sku_item_id_list) die('<p> <img src="/ui/flag.png" align="absmiddle" />No Item Selected to Generate PO</p>');
	
	// get all sku_item_id with selected vendor id
	//$sql = "select distinct(sku_item_id) from vendor_sku_history where sku_item_id in (".join(',',$sku_item_id_list).") and vendor_id=$vendor_id";
	$sql = "select distinct(po_items.sku_item_id) as sku_item_id
from po_items
left join po on po_items.po_id = po.id and po_items.branch_id = po.branch_id
left join vendor on vendor.id = po.vendor_id and vendor.active=1
where po.status=1 and po.approved and (po.branch_id=$request_branch_id or po.po_branch_id=$request_branch_id) 
and po_items.sku_item_id in (".join(',',$sku_item_id_list).") and po.po_no is not null order by po.added desc";
	$con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow()){
		$vendor_sku_item_id_list[$r['sku_item_id']] = $r['sku_item_id'];
	}
	$con->sql_freeresult();
	
	if(!$vendor_sku_item_id_list) die('<p> <img src="/ui/flag.png" align="absmiddle" />No Item for Selected Vendor</p>');
	// get selected request items
	$sql = "select dri.*,si.sku_item_code,si.artno,si.mcode,si.description
from do_request_items dri 
left join sku_items si on si.id=dri.sku_item_id
left join sku on sku.id=si.sku_id
$filter and dri.sku_item_id in (".join(',',$vendor_sku_item_id_list).")";
	$con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow()){
		$do_request_items[] = $r;
	}
	$con->sql_freeresult();
	
	$smarty->assign('do_request_items',$do_request_items);
	$smarty->display('do_request.vendor_sku_list.tpl');
}

function ajax_change_item_status(){
	global $con, $request_branch_id,$sessioninfo;
	
	$branch_id = mi($_REQUEST['from_branch']);
	$id_list = $_REQUEST['id_list'];
	$change_all = mi($_REQUEST['change_all']);
	$selected = mi($_REQUEST['selected']);
	
	if(!$change_all){
		if(!$id_list)	die('No Item to Change');
		$filter[] = "dri.id in (".join(',',$id_list).")";
	}else{
		$filter[] = "c.department_id in ($sessioninfo[department_ids])";
	}
	
	$filter[] = "dri.branch_id=$branch_id";
	$filter[] = "dri.request_branch_id=$request_branch_id";
	//$filter[] = "dri.print_picking_list_by=".$sessioninfo['id'];
	$filter[] = "dri.active=1 and (dri.status=0 or dri.status=2)";
	$filter = "where ".join(' and ', $filter);
	
	// get all distinct sku item id
	
	$sql = "update do_request_items dri 
		join sku_items si on si.id=dri.sku_item_id
		join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		set dri.selected=$selected 
		$filter";
	$con->sql_query($sql) or die(mysql_error());
	
	print "OK";
}

function load_picking_list_table(){
	global $con, $smarty, $sessioninfo, $page_size, $request_branch_id;
	
	$t = mi($_REQUEST['t']);
	$p = mi($_REQUEST['p']);
	$branch_id = mi($_REQUEST['branch_id']);
	$size = $page_size;
	$start = $p*$size;
	
	$filter[] = "dri.request_branch_id=".mi($request_branch_id);
	$filter[] = "dri.active=1";
	$filter[] = "dri.branch_id=$branch_id";
	$filter[] = "dri.status=1";
	$filter = "where ".join(' and ',$filter);
	//$sql = "select count(distinct(picking_list_id)) from do_request_items dri $filter";
	$sql = "select count(*) from picking_list where active=1 and branch_id=$branch_id and do_branch_id=".mi($request_branch_id)." order by last_update desc";
	$con->sql_query($sql) or die(mysql_error());
	$total_rows = $con->sql_fetchfield(0);
	
	if($start>=$total_rows){
		$start = 0;
		$_REQUEST['p'] = 0;
	}
	$limit = "limit $start, $size";
	$order = "order by dri.last_update desc";
	
	$total_page = ceil($total_rows/$size);
	$sql = "select pl.*,pl.branch_id,pl.do_branch_id,user.u,count(*) as item_count,sum(request_qty) as request_qty, sum(total_do_qty) as total_do_qty, sum(do_qty) as do_qty
			from picking_list pl
			left join do_request_items dri on dri.picking_list_id=pl.id
			left join user on user.id=pl.user_id
			$filter group by picking_list_id $order $limit";
	//print $sql;
	$con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow()){
		$picking_list[] = $r;
	}
	$con->sql_freeresult();
	$smarty->assign('total_item_count',$total_rows);
	$smarty->assign('picking_list',$picking_list);
	$smarty->assign('total_page',$total_page);
	$smarty->assign('item_counter',$start+1);
	$smarty->display('do_request.process.picking_list_table.tpl');
}

function open_picking_list(){
	global $con, $smarty, $config;
	
	$pid = mi($_REQUEST['pid']);
	$branch_id = mi($_REQUEST['branch_id']);
	$request_branch_id = $branch_id;
	
	if(!$pid||!$branch_id)	js_redirect(sprintf('Invalid ID', 'DO_REQUEST_PROCESS', BRANCH_CODE), "/do_request.process.php");
	
	$all_branch = $smarty->get_template_vars('all_branch');
	if($all_branch){
		$temp = array();
		foreach($all_branch as $b){
			$temp[$b['id']] = $b;
		}
		$smarty->assign('all_branch',$temp);
	}
	
	// check list
	$con->sql_query("select pl.*,user.u from picking_list pl left join user on user.id=pl.user_id where pl.id=$pid and pl.branch_id=$branch_id") or die(mysql_error());
	$form = $con->sql_fetchrow();
	if(!$form)	js_redirect(sprintf('Invalid ID', 'DO_REQUEST_PROCESS', BRANCH_CODE), "/do_request.process.php");
	
	$filter[] = "dri.request_branch_id=".mi($request_branch_id);
	$filter[] = "dri.active=1";
	//$filter[] = "dri.branch_id=$branch_id";
	$filter[] = "dri.status=1";
	$filter[] = "picking_list_id=$pid";
	$filter = "where ".join(' and ',$filter);
	$sql = "select dri.*,si.sku_item_code,si.artno,si.mcode,si.description,puom.code as packing_uom_code, si.doc_allow_decimal,si.location
	from do_request_items dri 
	left join sku_items si on si.id=dri.sku_item_id
	left join sku on sku.id=si.sku_id
	left join uom puom on puom.id=si.packing_uom_id
	left join category_cache cc on cc.category_id=sku.category_id
	$filter
	order by sku.category_id,cc.p3,cc.p2";
	//print $sql;
	
	$con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow()){
		$r['do_list'] = unserialize($r['do_list']);
		$items[] = $r;
	}
	
	if($config['do_print_picking_list_sort_by_loc']){
		usort($items, 'sort_location');
	}
	
	$smarty->assign('items', $items);
	$smarty->assign('form', $form);
	$smarty->display('do_request.process.open_picking_list.tpl');
}

function save_picking_list($generate_do = false){
	global $con, $smarty, $sessioninfo;
	
	$pid = mi($_REQUEST['pid']);
	$do_branch_id = mi($_REQUEST['do_branch_id']);
	$branch_id = mi($_REQUEST['branch_id']);
	$do_qty = $_REQUEST['do_qty'];
	
	if(!$pid||!$branch_id||!$do_branch_id)	js_redirect(sprintf('Invalid ID', 'DO_REQUEST_PROCESS', BRANCH_CODE), "/do_request.process.php");
	
	if($do_qty){
		foreach($do_qty as $id=>$qty){
			$con->sql_query("update do_request_items set do_qty=".mf($qty)." where id=".mi($id)." and branch_id=$do_branch_id and request_branch_id=$branch_id") or die(mysql_error());
		}
	}
	$con->sql_query("update picking_list set last_update=CURRENT_TIMESTAMP where id=$pid and branch_id=$branch_id") or die(mysql_error());
	log_br($sessioninfo['id'], 'DO Request', $pid, "Save Picking List, Picking List ID: $pid");
	
	if($generate_do){
		generate_do($pid, $do_branch_id, $branch_id);
		exit;
	}
	//header("Location: $_SERVER[PHP_SELF]?a=open_picking_list&pid=$pid&branch_id=$branch_id&request_branch_id=$request_branch_id");
	header("Location: $_SERVER[PHP_SELF]?branch_id=$do_branch_id&t=2");
}

function ajax_reject_item(){
	global $con, $smarty,$sessioninfo;

	$item_id_list = $_REQUEST['item_id_list'];
	$branch_id = mi($_REQUEST['from_branch']);
	$reason = trim($_REQUEST['reason']);

	if(!$item_id_list) die("No item were selected for reject.");
  
	foreach($item_id_list as $item_id){
		$upd = array();
		$upd['status'] = 3;
		$upd['reason'] = $reason;
		$upd['selected'] = 0;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['reject_by'] = $sessioninfo['id'];

		$sql = "update do_request_items set ".mysql_update_by_field($upd)." where active=1 and id=$item_id and branch_id=$branch_id and status=0";
		//print $sql;
		$q1 = $con->sql_query($sql) or die(mysql_error());
		$effected = $con->sql_affectedrows($q1);
		
		if($effected){
			log_br($sessioninfo['id'], 'DO Request', $item_id, "Reject Item, Item ID: $item_id");
		}
	}
	
	print "OK";
  
	exit;
}

function reject_picking_list(){
    global $con, $smarty, $request_branch_id, $sessioninfo;
    
    $pid = mi($_REQUEST['pid']);
	$branch_id = mi($_REQUEST['branch_id']);
	$uid = mi($sessioninfo['id']);
	// reset items
	$con->sql_query("update do_request_items set status=0 where picking_list_id=$pid and branch_id=$branch_id and request_branch_id=$request_branch_id and print_picking_list_by=$uid") or die(mysql_error());
	// reset picking list
	$con->sql_query("update picking_list set active=0 where id=$pid and branch_id=$request_branch_id and do_branch_id=$branch_id and user_id=$uid") or die(mysql_error());
	
	if($_REQUEST['ajax']){
		header("Location: $_SERVER[PHP_SELF]?a=ajax_list_sel&ajax=1&p=".$_REQUEST['p']."&t=2&branch_id=$branch_id");
		exit;
	}
	log_br($sessioninfo['id'], 'DO Request', $pid, "Reject Picking List, Picking List ID: $pid");
	header("Location: $_SERVER[PHP_SELF]?t=2&branch_id=$branch_id");
}

function sort_location($a, $b) {
 $locCmp = strnatcasecmp($a['location'], $b['location']);

 if ($locCmp != 0) // location are not equal
   return($locCmp);
}
?>
