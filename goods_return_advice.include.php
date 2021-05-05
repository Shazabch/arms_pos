<?
/*
Revision history
=================
5/22/2007 4:32:46 PM  yinsee
- add user's department filter to get_gra_items

5/30/2007 1:35:55 PM gary
- add department filter and pagination.

11/6/2007 11:16:02 AM gary
- call artno, mcode, selling and price type (in get_gra_items) for SKU FOR RETURN LIST.

22/7/2009 5:22:37 PM yinsee
exclude items that cannot check out when viewing in gra checkout

7/2/2010 4:11:39 PM Alex
- Add $config['document_page_size'] to set limit items per page and fix search bugs

7/6/2010 4:37:37 PM Justin
- Replaced the Selling Price to take from GRA Items table.

7/29/2011 3:38:21 PM Justin
- Modified the round up for cost to base on config.

8/17/2011 11:40:21 AM Justin
- Added pagination for GRA items.

10/28/2011 4:17:52 PM Andy
- Move GRA Return Type to PHP become a variable, no longer hard code.

4/23/2012 3:41:06 PM Alex
- add packing uom code

7/19/2012 5:16:23 PM Justin
- Enhanced to have show disposal GRA list while called from tpl.
- While doing reset, update current GRA type become "Return".

7/24/2012 11:30 AM Justin
- Added to pickup Account ID and Code.

7/31/2012 11:42:34 AM Justin
- Enhanced to have vendor search on GRN list.
- Removed the branch filter while search from HQ

8/7/2012 10:16 AM Justin
- Bug fixed when search by empty vendor ID, system shows sql error.

7/1/2013 4:49 PM Andy
- when reset gra, change to select sku_item_id to update changed=1 instead of using sub query.

7/4/2013 2:53 PM Justin
- Enhanced to filter and show new info for approval tabs.
- Enhanced to pickup approvels.
- Enhanced to set approved=0 while do reset.

7/31/2013 11:26 AM Andy
- Enhance GRA to have approval history when reset.

2/20/2014 4:58 PM Justin
- Enhanced to process GRA items under tmp table before it is update into the actual table.

3/24/2015 11:09 AM Justin
- Enhanced to have print DN feature.

4/1/2015 5:47 PM Justin
- Bug fixed on GRA return mysql errors.

4/7/2015 2:34 PM Justin
- Bug fixed on print D/N report does not filter off active=0.

4/9/2015 3:35 PM Justin
- Enhanced to set D/N to cancel if found GRA has been reset or cancelled.

4/18/2015 4:23 PM Justin
- Enhanced to pickup more info for item not in ARMS.

4/23/2015 10:47 AM Justin
- Bug fixed on printing D/N will retrieve the inactive one.

4/30/2015 1:33 PM Justin
- Enhanced to have privilege checking for generate/print DN report.

1/21/2016 4:26 PM Qiu Ying
- Add function "calculate_gra_gst_sp";

03/07/2016 14:34 Edwin
- Bug fixed on incorrect filter of Find GRA/Vendor in GRA checkout

03/17/2016 10:15 Edwin
- Bug fixed on incorrect filter of Find GRA/Vendor in GRA checkout when $config.gra_no_approval_flow is true

03/22/2016 16:25 Edwin
- Bug fixed on unable to find completed and disposal GRA in Find GRA/Vendor
- Bug fixed on incorrect filter of Find GRA/Vendor when search by Vendor

4/3/2017 4:55 PM Justin
- Enhanced to allow user to reset GRA if have privilege "GRA_ALLOW_USER_RESET".

4/26/2018 4:22 PM Justin
- Enhanced to have foreign currency feature.

7/13/2018 3:54 PM Andy
- Enhanced to show Department.

12/6/2018 10:43 AM Justin
- Enhanced to have Rounding Adjust.

5/02/2019 02:11 PM Liew
Enhanced to display Old Code 

5/21/2019 10:50 PM William
- Pickup report_prefix for enhance "GRA".
*/

// rounding base on config['gra_cost_decimal_points'], otherwise take from global cost decimal points.
if($config['gra_cost_decimal_points']) $dp = $config['gra_cost_decimal_points'];
else $dp = $config['global_cost_decimal_points'];
$smarty->assign("dp", $dp);

$maintenance->check(376);

$return_type_list = array(
	'Damage',
	'Expiry',
	'Exchange',
	'No Order',
	'Short Supply',
	'Slow Moving',
	'Fair Return',
	'Over Delivery',
	'Consignment Return',
	'Over Qty (Cost Not Billing)',
	'Other'
);
$smarty->assign("return_type_list", $return_type_list);

function load_gra_list($tpl = "goods_return_advice.list.tpl", $exclude_not_allow_checkout=false)
{
	global $con, $sessioninfo, $branch_id, $smarty, $config;

 	$filter .= " and category.id in (".join(",",array_keys($sessioninfo['departments'])).")";
	switch ($_REQUEST['t'])
	{
		case 0: // find
			$where = array();
			$str = intval($_REQUEST['search']);
			$vendor_id = $_REQUEST['search_vendor_id'];

			if(!$str && !$vendor_id) die('Cannot search empty string');

			if($str || $vendor_id){
				if($str){
					$where[] = "gra.id = ".mi($str);
				}
				
				if($vendor_id){
					$where[] = "gra.vendor_id = ".mi($vendor_id);
				}
			
				if($tpl == "goods_return_advice.checkout.list.tpl") {
					
					if($config['gra_no_approval_flow']){
						// save, completed/disposed, cancelled
						$where[] = "((gra.status=0 and gra.approved=0 and gra.returned=0)or(gra.status=0 and gra.approved=1 and gra.returned=1)or(gra.status=1 and gra.approved=0 and gra.returned=0))";
					}
					else{
						// approved, completed/disposed, cancelled
						$where[] = "((gra.status=0 and gra.approved=1 and gra.returned=0)or(gra.status=0 and gra.approved=1 and gra.returned=1)or(gra.status=1 and gra.approved=0 and gra.returned=0))";
					}
						
				}
			}
			
			if($_REQUEST['vendor_id']){	// this not for gra checkout
				$v = intval($_REQUEST['vendor_id']);
				// 2 = waiting for approval
	   		    $where[] = "status in (0,2) and returned = 0 and gra.vendor_id=$v and gra.branch_id=$branch_id $filter";
			}
			
			if(BRANCH_CODE != "HQ") $where[] = "gra.branch_id=".mi($branch_id);
			$where = join(" and ", $where).$filter;

			/*$con->sql_query("select gra.*,vendor.description as vendor from gra
left join vendor on gra.vendor_id = vendor.id
left join category on gra.dept_id = category.id
where gra.id = $s and gra.branch_id=$branch_id $filter order by gra.last_update desc");
			while ($gra = $con->sql_fetchrow()){
				if ($gra){
					$gra['misc_info'] = unserialize($gra['misc_info']);
  					$gra['extra']= unserialize($gra['extra']);
				}
				$gra_list[]=$gra;
			}
			//echo"<pre>";print_r($gra_list);echo"</pre>";
			$smarty->assign("gra_list",$gra_list);*/
			break;

		case 1: // saved GRA
		    $where="gra.status=0 and gra.returned=0 and gra.approved = 0 and gra.branch_id=$branch_id $filter";
			break;

		case 2: // completed GRA
		    $where="gra.status=0 and gra.returned=1 and gra.approved = 1 and gra.type = 'Return' and gra.branch_id=$branch_id $filter";
			break;

		case 3: // Canceled/Terminated GRA
		    $where="gra.status=1 and gra.branch_id=$branch_id $filter";
			$smarty->assign("mode",'cancel');
			break;

		case 4: // Disposed GRA
		    $where="gra.status=0 and gra.returned=1 and gra.approved = 1 and gra.type = 'Disposal' and gra.branch_id=$branch_id $filter";
			break;

		case 5: // waiting for approval GRA
		    $where="gra.status=2 and gra.returned=0 and gra.approved = 0 and gra.branch_id=$branch_id $filter";
			break;

		case 6: // approved GRA
		    $where="gra.status=0 and gra.returned=0 and gra.approved = 1 and gra.branch_id=$branch_id $filter";
			break;
	
		default:
			print_r($_REQUEST);
			exit;
	}
	// pagination
	$start = intval($_REQUEST['s']);

	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else{
		if (isset($config['document_page_size'])) $sz=$config['document_page_size'];
			else	$sz = 25;
	}
	$con->sql_query("select count(*) from gra left join vendor on gra.vendor_id = vendor.id left join category on gra.dept_id = category.id where $where order by gra.last_update desc");
	$r = $con->sql_fetchrow();
	$total = $r[0];
	if ($total > $sz)
	{
	    if ($start > $total) $start = 0;
		// create pagination
		$pg = "<b>Goto Page</b> <select onchange=\"list_sel($_REQUEST[t],this.value)\">";
		for ($i=0,$p=1;$i<$total;$i+=$sz,$p++)
		{
			$pg .= "<option value=$i";
			if ($i == $start)
			{
				$pg .= " selected";
			}
			$pg .= ">$p</option>";
		}
		$pg .= "</select>";
		$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");

  	}
  	
	/*if ($exclude_not_allow_checkout)
	{
		// exclude items that cannot check out when viewing in gra checkout
		$where = str_replace("order by", " having not_allow_checkout=0 order by ", $where);
	}*/
	$got_rounding_adj = false;
	$r=$con->sql_query("select gra.*, vendor.description as vendor,category.description as dept_code, vendor.code as vendor_code, 
						if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id, b.code as branch_code, b.report_prefix,
						bah.approvals, bah.approval_order_id
						from gra
						left join vendor on gra.vendor_id = vendor.id
						left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = gra.branch_id
						left join branch b on b.id = gra.branch_id
						left join category on gra.dept_id = category.id
						left join branch_approval_history bah on bah.id = gra.approval_history_id and bah.branch_id = gra.branch_id
						where $where
						order by gra.last_update desc
						limit $start, $sz");

	while ($gra = $con->sql_fetchrow($r)){
		$gra['misc_info'] = unserialize($gra['misc_info']);
		$gra['extra']= unserialize($gra['extra']);
		$gra['not_allow_checkout'] = 0;
		$gi_count = 0;
		
		$q1=$con->sql_query("select gi.*
							 from gra_items gi
							 left join gra on gra.id=gi.gra_id and gra.branch_id=gi.branch_id 
							 where gra.id=".mi($gra['id'])." and gra.branch_id=".mi($branch_id));
							 
		while($r1 = $con->sql_fetchassoc($q1)){
			if(!$r1['batchno']) $gra['not_allow_checkout']++;
			$gi_count++;
		}
		
		if($gra['extra']['code']) $gi_count += count($gra['extra']['code']);
		
		if ($exclude_not_allow_checkout && $gra['not_allow_checkout'] > 0) continue;
		
		// verify if this checkout GRA is using ARMS generated DN
		if($gi_count > 0 && !$gra['status'] && $gra['returned'] && $gra['approved'] && $gra['type'] == "Return"){
			$q2 = $con->sql_query("select * from dnote where ref_table = 'gra' and ref_id = ".mi($gra['id'])." and branch_id = ".mi($gra['branch_id'])." and active=1");
			$dnote_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			if(privilege('GRA_GENERATE_DN')){ // privilege check
				if($dnote_info){ // found this GRN already have DN generated
					$gra['print_arms_dn'] = 1;
				}elseif(!$gra['misc_info']['dn_no'] && !$gra['misc_info']['dn_date'] && !$gra['misc_info']['dn_amount']){ // found this GRN already have DN generated
					$gra['generate_arms_dn'] = 1;
				}else $gra['generate_arms_dn'] = 0;
			}
		}
		
		if($gra['rounding_adjust']) $got_rounding_adj = true;
		
		$gra_list[]=$gra;
	}
	//echo"<pre>";print_r($gra_list);echo"</pre>";
	$smarty->assign("limit", $limit);
	$smarty->assign("gra_list",$gra_list);
	$smarty->assign("got_rounding_adj",$got_rounding_adj);

	$smarty->display($tpl);
}

function get_gra_items($filter='', $pagination=false, $use_tmp=false){
	global $con, $smarty, $branch_id, $sessioninfo;

	$left_join = "";
	$gra_id = $_REQUEST['gra_id'];
	if($use_tmp) $tbl = "tmp_gra_items";
	else{
		$tbl = "gra_items";
		$left_join = "left join tmp_gra_items tgri on tgri.item_id = gri.id and tgri.branch_id = gri.branch_id and tgri.gra_id = ".mi($gra_id);
	}
	
	if ($filter == ''){
		$filter = "gri.gra_id=0 and gri.temp_gra_uid=0 and gri.branch_id=$branch_id";
	}
	$filter .= " and category.department_id in (".join(",",array_keys($sessioninfo['departments'])).")";
	if(!$use_tmp) $filter .= " and tgri.id is null";
	
	// pagination
	if($pagination){
		$start = intval($_REQUEST['s']);
		if (isset($_REQUEST['sz']))
			$sz = intval($_REQUEST['sz']);
		else{
			if (isset($config['document_page_size'])) $sz=$config['document_page_size'];
				else $sz = 25;
		}
		$limit =  "desc limit $start, $sz";

		$con->sql_query("select count(*)
						 from $tbl gri
						 left join sku_items on gri.sku_item_id = sku_items.id
						 left join sku on sku_items.sku_id = sku.id
						 left join trade_discount_type tdt on sku.trade_discount_type = tdt.id
						 left join category on sku.category_id = category.id
						 left join vendor on gri.vendor_id = vendor.id
						 $left_join
						 where $filter");

		$r = $con->sql_fetchrow();
		$total = $r[0];
		if ($total > $sz){
			if ($start > $total) $start = 0;
			// create pagination
			if(!$_REQUEST['sku_type']) $_REQUEST['sku_type'] = "*";
			$pg = "<b>Goto Page</b> <select onchange=\"do_sort_type(".ms($_REQUEST['sku_type']).", this.value)\">";
			for ($i=0,$p=1;$i<$total;$i+=$sz,$p++){
				$pg .= "<option value=$i";
				if ($i == $start) $pg .= " selected";
				$pg .= ">$p</option>";
			}
			$pg .= "</select>";
			$smarty->assign("items_pagination", "<div style=\"padding:4px;\">$pg</div>");
		}
	}
	
	$con->sql_query("select gri.*,sku_items.sku_item_code, sku_items.description as sku, sku.sku_type, 
					 vendor.description as vendor, sku_items.artno as artno, sku_items.mcode as mcode, sku_items.link_code as link_code,
	 				 gri.selling_price, sku.default_trade_discount_code as default_price_type,puom.code as packing_uom_code,if(if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)='inherit',cc.inclusive_tax,if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)) as inclusive_tax, dept.description as dept_name
					 from $tbl gri
					 left join sku_items on gri.sku_item_id = sku_items.id
					 left join uom puom on puom.id=sku_items.packing_uom_id
					 left join sku on sku_items.sku_id = sku.id
					 left join trade_discount_type tdt on sku.trade_discount_type = tdt.id
					 left join category on sku.category_id = category.id
					 left join category_cache cc on cc.category_id=sku.category_id
					 left join category dept on dept.id=category.department_id
					 left join vendor on gri.vendor_id = vendor.id
					 $left_join
					 where $filter order by gri.added $limit");
	//echo $filter;
	//$gra_items=$con->sql_fetchrowset();
	//echo"<pre>";print_r($gra_items);echo"</pre>";
	$smarty->assign("limit", $limit);
	$smarty->assign("gra_items",$con->sql_fetchrowset());
	
}

function do_reset($gra_id,$branch_id){
	global $con,$sessioninfo,$config;
	$gra_id = mi($gra_id);
	$branch_id = mi($branch_id);
	
	$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;

	if($sessioninfo['level']<$required_level && !privilege('GRA_ALLOW_USER_RESET')){
        js_redirect(sprintf('Forbidden', 'GRA', BRANCH_CODE), "/goods_return_advice.php");
	}

    $con->sql_query("select gra.*, vendor.description as vendor,category.description as dept_code,branch.report_prefix from gra left join vendor on gra.vendor_id = vendor.id left join category on gra.dept_id = category.id left join branch on gra.branch_id=branch.id where gra.id = $gra_id and branch_id = $branch_id and gra.returned=1");
	$form = $con->sql_fetchrow();
    if(!$form)  js_redirect(sprintf('Invalid GRA', 'GRA', BRANCH_CODE), "/goods_return_advice.php");
	$report_prefix=$form['report_prefix'];
    //update sku_item_cost
    /*$con->sql_query("select distinct sku_item_id as sid
    from gra_items 
    left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id 
    where gra_items.gra_id=$gra_id and gra_items.branch_id=$branch_id");
    $sid_list = array();
    while($r = $con->sql_fetchassoc()){
    	$sid_list[] = mi($r['sid']);
    }
    $con->sql_freeresult();
    if($sid_list){
    	$con->sql_query("update sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',', $sid_list).")");
    	unset($sid_list);
    }*/
	
	set_sku_items_cost_changed($gra_id, $branch_id);
    
	//$con->sql_query("update sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (select sku_item_id from gra_items left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id where gra_items.gra_id=$gra_id and gra_items.branch_id=$branch_id and gra_items.checkout=1 and gra.returned)");

    // update items
	$q_i = $con->sql_query("update gra_items set checkout=0 where gra_id=$gra_id and branch_id=$branch_id") or die(mysql_error());
	
	// update main gra
	$upd = array();
	$upd['status'] = 0;
	$upd['approved'] = 0;
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	$upd['returned'] = 0;
	$upd['type'] = 'Return';
	$upd['rounding_adjust'] = 0;
	$con->sql_query("update gra set ".mysql_update_by_field($upd)." where id=$gra_id and branch_id=$branch_id") or die(mysql_error());
	
	$con->sql_query("update dnote set active=0 where ref_table = 'gra' and ref_id = ".mi($gra_id)." and branch_id = ".mi($branch_id));

    log_br($sessioninfo['id'], 'GRA', $gra_id, sprintf("GRA Reset (#$form[id])",$gra_id));
    
    if($form['approval_history_id']){
        $upd = array();
		$upd['approval_history_id'] = $form['approval_history_id'];
		$upd['branch_id'] = $branch_id;
		$upd['user_id'] = $sessioninfo['id'];
		$upd['status'] = 0;
		$upd['log'] = $_REQUEST['reason'];

		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
		$con->sql_query("update branch_approval_history set status=0 where id = ".mi($form['approval_history_id'])." and branch_id = $branch_id");
	}
	
	header("Location: /goods_return_advice.php?t=reset&id=$gra_id&report_prefix=$report_prefix");
}

function set_sku_items_cost_changed($gra_id, $branch_id){
	global $con;

	//update sku_item_cost
    $q1 = $con->sql_query("select distinct sku_item_id as sid
						   from gra_items 
						   left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id 
						   where gra_items.gra_id=$gra_id and gra_items.branch_id=$branch_id");
    $sid_list = array();
    while($r = $con->sql_fetchassoc($q1)){
    	$sid_list[] = mi($r['sid']);
    }
    $con->sql_freeresult($q1);
    if($sid_list){
    	$con->sql_query("update sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',', $sid_list).")");
    	unset($sid_list);
    }
}

function print_arms_dn($branch_id, $gra_id){
	global $con, $LANG;

	$form = $_REQUEST;
	$branch_id = mi($branch_id);
	$gra_id = mi($gra_id);
	
	if(!$branch_id || !$gra_id)	die("Invalid Parameters");
	
	// temporary update for testing purpose
	// ===================
	//$con->sql_query("update gra set misc_info='' where id = ".mi($gra_id)." and branch_id = ".mi($branch_id));
	
	//$con->sql_query("delete dni.* from dnote dn left join dnote_items dni on dni.dnote_id = dn.id and dni.branch_id = dn.branch_id where dn.branch_id = ".mi($branch_id)." and dn.ref_table = 'gra' and dn.ref_id = ".mi($gra_id));
	
	//$con->sql_query("delete from dnote where branch_id = ".mi($branch_id)." and ref_table = 'gra' and ref_id = ".mi($gra_id));
	// ===================
	
	// get the gra
	$con->sql_query("select * from gra where branch_id=$branch_id and id=$gra_id");
	$gra = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$gra)	die("GRA ID#$gra_id not found.");
	
	$gra['misc_info'] = unserialize($gra['misc_info']);
	
	// found grn, verify if the DN is generated from arms system
	$q1 = $con->sql_query("select * from dnote where ref_table = 'gra' and ref_id = ".mi($gra_id)." and branch_id = ".mi($branch_id)." and active=1");
	$dnote_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	// found this gra already have external DN, show error message
	if($gra['misc_info']['dn_no']){
		if($dnote_info){
			// already have dn no
			header("Location: /dnote.php?a=print_dn&id=".mi($dnote_info['id'])."&branch_id=".mi($dnote_info['branch_id']));
		}else{
			// found it is external DN, redirect user to GRN main page 
			js_redirect($LANG['GST_DN_EXISTED'], "/goods_return_advice.checkout.php");
		}
		exit;
	}
	
	// generate arms dn
	header("Location: /dnote.php?a=generate_dn&from_module=gra&branch_id=$branch_id&id=$gra_id&inv_no=".$form['inv_no']."&inv_date=".$form['inv_date']."&remark=".$form['remark']."&need_print=1");
	exit;
}

function calculate_gra_gst_sp($prms){
	global $con, $smarty, $config;
	
	if(!$prms || !$prms['selling_price']) return;

	if($prms['inclusive_tax'] == "yes"){
		$tmp_gst_rate = $prms['gst_rate'] + 100;
		$ret['gst_selling_price'] = round($prms['selling_price'] * 100 / $tmp_gst_rate, 4);
		$ret['gst_amt'] = round($ret['gst_selling_price'] * $prms['gst_rate'] / 100, 4);
	}else{
		$ret['gst_amt'] = round($prms['selling_price'] * $prms['gst_rate'] / 100, 4);
		$ret['gst_selling_price'] = $prms['selling_price'] + $ret['gst_amt'];
	}
	
	return $ret;
}

function loadGRACurrencyCodeList($gra=array()){
	global $config, $smarty, $appCore;
	
	// got turn on currency
	if($config['foreign_currency']){
		// Get Foreign Currency Code Array
		$foreignCurrencyCodeList = $appCore->currencyManager->getCurrencyCodes();
		
		// If GRA using the Foreign Currency which now already inactive, need to append into array
		if($gra['currency_code'] && !isset($foreignCurrencyCodeList[$gra['currency_code']])){
			$foreignCurrencyCodeList[$gra['currency_code']] = $gra['currency_code'];
		}
	}
	
	if(isset($smarty) && $smarty){
		$smarty->assign('foreignCurrencyCodeList', $foreignCurrencyCodeList);
	}
	return $foreignCurrencyCodeList;
}

function loadCurrencyRate(){
	global $LANG, $appCore;
	
	$form = $_REQUEST;
	if(!$form['date']) $ret['err'] = sprintf($LANG['GRR_INVALID_RECEIVE_DATE'], "");
	
	$date = date("Y-m-d", strtotime($form['date']));
	
	if(!$ret['err']){
		$ret = $appCore->currencyManager->loadCurrencyRateByDate($date, $form['currency_code']);
	}
	
	// if found got code but no rate, ned to prompt error
	if($form['currency_code'] && !$ret['rate']) $ret['err'] = $LANG['CURRENCY_RATE_ZERO'];
	
	print json_encode($ret);
}
?>
