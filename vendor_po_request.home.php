<?php
/*
REVISION HISTORY
================
3/5/2008 12:02:45 PM gary
- change the old po link to new po link.

4/22/2008 4:07:32 PM gary
- temporary hide the

4/29/2008 gary
- split the diff PO if have max item per PO.
- PO branch follow the access branch.

06.05.2008 18:36:01 saw
- Vendor PO request take away category column

6/5/2008 3:10:06 PM yinsee
- join sku_items.packing_uom_id instead of sku.uom_id

9/7/2009 12:32:21 PM yinsee
- show balance and 30d/90d sales if not intranet

7/10/2009 12:25:51 PM yinsee
- save branch_id to po_branch_id if create from HQ

6/14/2010 3:24:42 PM Alex
- Add sku_items block and active for branch

11/4/2010 12:40:12 PM Justin
- Added maintenance check.
- Added 2 new fields which is order_price and foc.
- Added a config check to replace the order price with the new field that keyed in by user if found config on.
- Modified the checking of to insert item based on qty or foc instead of qty only.
- Extract these new info from database and do unserialize.

6/16/2011 4:59:52 PM Andy
- Add "Cost Indicate" for PO create by vendor, if got config "po_vendor_use_order_price", cost indicate will be "VENDOR".

4/23/2015 3:04 PM Justin
- Enhanced to pickup GST information.

12/29/2015 11:07 AM Kee Kee
- Added po_date to avoid failed to check gst status

12/30/2015 2:02 PM Kee Kee
- Fixed get wrong input tax

08/05/2016 11:00 Edwin
- Bug fixed on "gst_selling_price" always zero when generate po.

2/13/2017 2:01 PM Andy
- Enhanced Load Vendor SKU to check master vendor if got config po_vendor_listing_enable_check_master_vendor.

3/24/2017 12:05 PM Andy
- Fixed load item check master vendor bug.

3/24/2017 1:52 PM Andy
- Enhanced to auto recalculate all PO Amount using reCalcatePOUsingOldMethod() when generate or update po.

3/27/2017 3:01 PM Andy
- Change reCalcatePOUsingOldMethod() to reCalcatePOAmt().

6/12/2017 4:34 PM Andy
- Enhanced to store only edited items in tmp_vendor_po_items.

6/13/2017 11:09 AM Andy
- Enhanced to ignore zero balance and store positive qty and foc only.

6/23/2017 16:22 AM Qiu Ying
- Enhanced to select multiple department in vendor PO access

8/9/2017 5:26 PM Andy
- Fixed vendor_sku_history to get latest cost_price.

8/17/2017 5:29 PM Andy
- Fixed if the same item got received in more than one branch, users login at HQ will get the wrong last cost.

11/2/2017 5:42 PM Justin
- Enhanced to lock UOM similar to PO's checking.

11/30/2017 12:07 PM Andy
- Enhanced the save function to use ajax.
*/
session_start();
$ssid = session_id();
include("include/common.php");
require_once("vendor_sku.include.php");
$maintenance->check(37);
/*
if(!$_SESSION['ticket']['ac'] || $login)
{
	header("Location: /");
}
*/

// check ticket validity
$u_id = ms($_SESSION['ticket']['ac']);

$con->sql_query("select login_tickets.*,vendor.description as vendor, vendor.address as address, vendor.phone_1 as phone_1, vendor.phone_2 as phone_2 , vendor.phone_3 as phone_3 ,category.description as dept_name, user.u, user.fullname, user2.u as u_create, user2.fullname as fullname_create
from login_tickets
left join user on login_tickets.user_id=user.id
left join user user2 on login_tickets.create_by=user2.id
left join category on dept_id=category.id
left join vendor on login_tickets.vendor_id=vendor.id
where login_tickets.ac=$u_id");
$r=$con->sql_fetchrow();

if($ssid!=$r['ssid'] or $_SESSION['ticket']['ac']!=$r['ac']){
	js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
}

$smarty->assign("PAGE_TITLE", "Vendor PO Request");
$con->sql_query("select * from uom where active order by id");
$smarty->assign("uom", $con->sql_fetchrowset());

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a']){

		case 'ajax_refresh_items':
				load_items($r);
				$smarty->assign("items", $items);
				$smarty->assign("form",$form);
				$smarty->display("vendor_po_request.home.items.tpl");
				exit;

		case 'logout':
    		$con->sql_query("update login_tickets set ssid='' where ac=$u_id");
			session_unset();
			header("Location: /login.php?logout=1");
			exit;

		case 'confirm':
		case 'print':
		case 'save':
			save_vendor_items();
			break;

		case 'submit':

			$form=$_REQUEST;
			//if($_SESSION['ticket']['ac'] == '567860'){
			//	print_r($form);exit;
			//}
			
			$qty = array();
			$counter=0;
			foreach($form['qty'] as $k=>$v){
			    if ($v!='' || $form['foc'][$k]!=''){
					$qty[$k] = $form['qty'][$k];
					$foc[$k] = $form['foc'][$k];
					$counter++;
				}
			}
			if($counter<1){
				$errm['top'][] = $LANG['VENDOR_PO_REQUEST_NO_ITEM'];
				load_items($r);
				$smarty->assign("items", $items);
				$smarty->assign("form",$form);
				$smarty->assign("errm",$errm);
				$smarty->display("vendor_po_request.home.tpl");
				exit;
			}
			$temp['qty'] = $qty;
			$temp['foc'] = $foc;

			$price = array();
		
			
			// here is where check the config and set the price to get from user keyed in order price.
			
			if($config['po_vendor_use_order_price']) $form['price'] = $form['order_price'];
			
			foreach($temp['qty'] as $k=>$v){
			    if ($v!='' || $form['foc'][$k]!=''){
					$price[$k] = $form['price'][$k];
				}
			}
			$temp['price'] = $price;

			$uom_id = array();
			foreach($temp['price'] as $k=>$v){
			    if($v!=''){
					$uom_id[$k] = $form['uom_id'][$k];
				}
			}
			$temp['uom_id'] = $uom_id;

			foreach($temp['uom_id'] as $k=>$v){
			    if ($v!=''){
			    	$d=$form['dept_id'][$k];
			    	// not to multiply the fraction whenever config on.
			    	if($config['po_vendor_use_order_price']) $fraction=1;
			    	else $fraction=$form['uom_fraction'][$k];
					$items[$d][$k]['qty']=$temp['qty'][$k];
					$items[$d][$k]['foc']=$temp['foc'][$k];
					$items[$d][$k]['price']=round($temp['price'][$k]*$fraction,2);
					$items[$d][$k]['uom_id']=$temp['uom_id'][$k];
					$items[$d][$k]['dept_id']=$form['dept_id'][$k];
					$items[$d][$k]['balance']=$form['balance'][$k];
					$items[$d][$k]['remark']=$form['remark'][$k];
					$items[$d][$k]['selling_price']=round($form['selling_price'][$k],2);
					$items[$d][$k]['artno_mcode']=$form['artno_mcode'][$k];
					$items[$d][$k]['cost_indicate'] = $form['cost_indicate'][$k];
					//$s[$k]=$k;
				}
			}
			$form['items'] = $items;

			$dept = array();
			foreach($form['dept_id'] as $k=>$v){
				if($v){
					$dept[$v] = 1;
				}
			}
			$form['dept_id'] = $dept;

			//$form['items'] = array_merge_recursive($form['items'],$form['uom_id']);
			$po=$form;
			$counter=0;
			$create_msg=array();
			
			$prms = array();
			$prms['branch_id'] = $po['branch_id'];
			$prms['date'] = date("Y-m-d");
			$branch_is_under_gst = check_gst_status($prms);
			
			$prms = array();
			$prms['vendor_id'] = $po['vendor_id'];
			$prms['date'] = date("Y-m-d");
			$vendor_is_under_gst = check_gst_status($prms);
			
			if($branch_is_under_gst)	$output_gst_list = construct_gst_list('supply');
			if($vendor_is_under_gst)	$input_gst_list = construct_gst_list('purchase');

			//if($_SESSION['ticket']['ac'] == '567860'){
			//	print_r($form);exit;
			//}
			
			//split out if have diff dept (1st draf without filter dept_id when generate acess code.)
			$po_id_list = array();
			foreach($form['dept_id'] as $k=>$v){
				$po['department_id']=$k;
				$po['request_by']=$po['user_id'];
				$po['added']='CURRENT_TIMESTAMP()';
				$po['login_ticket_ac']=$_SESSION['ticket']['ac'];
				$po['po_date']=date("Y-m-d");
				$po['is_under_gst'] = $vendor_is_under_gst;
				
				if (BRANCH_CODE == 'HQ') $po['po_branch_id']=$form['branch_id'];

				if($po['items'][$k]){
					foreach($po['items'][$k] as $k1=>$v1){
						$p_item = array();
						if($config['po_set_max_items']){
							if ($counter % $config['po_set_max_items'] == 0){
								$po_id=generate_new_po($po);
								$po_id_list[] = $po_id;
							}
						}
						else{
							if ($counter==0){
								$po_id=generate_new_po($po);
								$po_id_list[] = $po_id;
							}								
						}

						$p_item['po_id']=$po_id;
						$p_item['branch_id']=$po['branch_id'];
						$p_item['user_id']=$po['user_id'];
						$p_item['sku_item_id']=$k1;
						$p_item['order_uom_id']=$po['items'][$k][$k1]['uom_id'];
						$p_item['remark']=$po['items'][$k][$k1]['remark'];
						$p_item['balance']=$po['items'][$k][$k1]['balance'];
						$p_item['order_price']=$po['items'][$k][$k1]['price'];
						
						if($config['po_vendor_use_order_price']){
							$p_item['cost_indicate'] = 'VENDOR';	// cost given by vendor
						}else{
							$p_item['cost_indicate'] = $po['items'][$k][$k1]['cost_indicate'];
						}
						
						
						$p_item['selling_price']=$po['items'][$k][$k1]['selling_price'];

		    			$q1=$con->sql_query("select fraction from uom where id=$p_item[order_uom_id]");
						$r1 = $con->sql_fetchrow($q1);
						$p_item['order_uom_fraction']=$r1['fraction'];
						if($r1['fraction']>1){
							$p_item['qty']=$po['items'][$k][$k1]['qty'];
							$p_item['qty_loose']='';
						}
						else{
							$p_item['qty_loose']=$po['items'][$k][$k1]['qty'];
							$p_item['qty']='';
						}
						$p_item['foc_loose']=$po['items'][$k][$k1]['foc'];
						$p_item['artno_mcode']=$po['items'][$k][$k1]['artno_mcode'];
						
						if($branch_is_under_gst){
							if(!$p_item['selling_gst_id']){
								$output_gst = get_sku_gst("output_tax", $p_item['sku_item_id']);
								if($output_gst){
									$p_item['selling_gst_id'] = $output_gst['id'];
									$p_item['selling_gst_code'] = $output_gst['code'];
									$p_item['selling_gst_rate'] = $output_gst['rate'];
								}else{
									$p_item['selling_gst_id'] = $output_gst_list[0]['id'];
									$p_item['selling_gst_code'] = $output_gst_list[0]['code'];
									$p_item['selling_gst_rate'] = $output_gst_list[0]['rate'];
								}
							}
							
							$inclusive_tax = get_sku_gst("inclusive_tax", $p_item['sku_item_id']);
							
							$prms = array();
							$prms['selling_price'] = $p_item['selling_price'];
							$prms['inclusive_tax'] = $inclusive_tax;
							$prms['gst_rate'] = $p_item['selling_gst_rate'];
							$gst_sp_info = calculate_gst_sp($prms);
							$p_item['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
							
							if($inclusive_tax == "yes"){
								$p_item['gst_selling_price'] = $p_item['selling_price'];
								$p_item['selling_price'] = $gst_sp_info['gst_selling_price'];
							}
						}
						
						if($vendor_is_under_gst){
							if(!$p_item['cost_gst_id']){
								$input_gst = get_sku_gst("input_tax", $p_item['sku_item_id']);
								
								if($input_gst){
									$p_item['cost_gst_id'] = $input_gst['id'];
									$p_item['cost_gst_code'] = $input_gst['code'];
									$p_item['cost_gst_rate'] = $input_gst['rate'];
								}else{
									$p_item['cost_gst_id'] = $input_gst_list[0]['id'];
									$p_item['cost_gst_code'] = $input_gst_list[0]['code'];
									$p_item['cost_gst_rate'] = $input_gst_list[0]['rate'];
								}
							}
						}
						
						$con->sql_query("insert into po_items " . mysql_insert_by_field($p_item));
						$counter++;

					}
				}

				/*
				if (BRANCH_CODE != 'HQ'){
		    		$q11=$con->sql_query("select approvals from approval_flow where type='PURCHASE_ORDER' and sku_category_id=$k and branch_id=$r[branch_id]");
		    		$r11 = $con->sql_fetchrow($q11);
		    		$new_str=str_replace("|", ",", $r11['approvals']);
		    		$r12 = split(",", $new_str);
					$po['user_id']=$r12[1];
				}

				if($po['items'][$k]){
					$con->sql_query("insert into po " . mysql_insert_by_field($po, array("branch_id", "user_id", "vendor_id", "department_id", "added", "request_by","login_ticket_ac")));
					$po_id=$con->sql_nextid();
					$errm['top'][] = sprintf($LANG['PO_CREATED'], $po_id);
				}

	    		*/

			}

			if($po_id_list){
				foreach($po_id_list as $po_id){
					// recalculate po amount
					//$appCore->poManager->reCalcatePOUsingOldMethod($po['branch_id'], $po_id);
					$appCore->poManager->reCalcatePOAmt($po['branch_id'], $po_id);
				}
			}
			//reset temporary items to empty
			$form['login_tickets_ac'] =$_SESSION['ticket']['ac'];
			$form['balance'] ='';
			$form['qty'] = '';
			$form['remark'] = '';
			$con->sql_query("replace into tmp_vendor_po_items " . mysql_insert_by_field($form, array("login_tickets_ac", "balance", "qty","remark")));			
			$smarty->assign("form",$_REQUEST);
			$smarty->assign("create_msg",$create_msg);
			$smarty->display("vendor_po_request.home.logout.tpl");

			exit;
		case 'ajax_save_vendor_items':
			ajax_save_vendor_items();
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}
load_items($r);
$smarty->assign("items", $items);
$smarty->assign("form",$form);

$smarty->assign("show_system_balance",!is_intranet());

if($_REQUEST['a']=='print'){
	$bid = $r['branch_id'];
	$q1=$con->sql_query("select * from branch where id=$bid");
	$r1 = $con->sql_fetchrow($q1);
	$branch=$r1;
	$smarty->assign("branch",$branch);
	$smarty->display("vendor_po_request.home.print.tpl");
}
else{
	$smarty->display("vendor_po_request.home.tpl");
}
exit;

function generate_new_po($po){	
	global $con, $create_msg, $LANG;	
	$con->sql_query("insert into po " . mysql_insert_by_field($po, array("branch_id", "user_id", "vendor_id", "department_id", "added", "request_by","login_ticket_ac","po_branch_id", "is_under_gst","po_date")));
	$po_id=$con->sql_nextid();
	$create_msg[] = sprintf($LANG['PO_CREATED'], $po_id);

	$msg="Vendor PO Request Received (ID#$po_id)";
	$url="/po.php?a=open&id=$po_id&branch_id=$po[branch_id]";

	$con->sql_query("insert into pm (branch_id, from_user_id, to_user_id, msg, url, added) values ($po[branch_id], $po[user_id], $po[user_id], ".ms($msg).", ".ms($url).", CURRENT_TIMESTAMP())");
	return $po_id;
}

function load_items($r){

	global $con, $smarty, $sessioninfo, $items, $form, $config, $appCore;

	$form=array();
	$form=$r;
    $vendor_id = intval($r['vendor_id']);
	
	// get default UOM (each)
	$defaultUOM = $appCore->uomManager->getUOMForEach();
	$smarty->assign("defaultUOM", $defaultUOM);
    
	if($r['multiple_dept_id']){
		$dept_id = implode(",", array_keys(unserialize($r['multiple_dept_id'])));
	}else{
		$dept_id = intval($r['dept_id']);
	}
	
	$q2 = $con->sql_query("select id, description from category where id in ($dept_id)");
	while($r2 = $con->sql_fetchassoc($q2)){
		$form["d_id"][] = $r2["description"];
	}
	$con->sql_freeresult($q2);
	
	$bid = $r['branch_id'];

	if(preg_match("/^(artno|mcode|sku_item_code|description|cat_name)$/",$_REQUEST['s_field'])){
		$s_field="$_REQUEST[s_field]";
	}
	else{
		$s_field='sku_item_code';
	}
	if($_REQUEST['s_arrow']=="DESC"){
		$s_arrow="DESC";
	}
	else{
		$s_arrow='';
	}
	$smarty->assign("s_field", $s_field);
	$smarty->assign("s_arrow", $s_arrow);
	// for testing purpose, open the 2nd option
	$branch_check = "vendor_sku_history.vendor_id = $vendor_id and category.department_id in ($dept_id) ";
	if (BRANCH_CODE != 'HQ'){
		$branch_check .= " and vendor_sku_history.branch_id = $bid ";
	}
	
	$sql = "select vendor_sku_history.sku_item_id, sku_id, sku_item_code, sku_items.id, sku_items.description, sku_items.artno, sku_items.mcode,sku_items.block_list, sku.varieties, vendor_sku_history.selling_price, vendor_sku_history.cost_price, category.department_id as dept_id, sip.price as sell_price_1, sku_items.selling_price as sell_price_2, uom.id as cost_uom, uom.fraction as uom_fraction, uom.code as uom_code, sic.grn_cost, sic.qty as stock, sic.l90d_pos, sic.l30d_pos, uom.fraction as master_uom_fraction
from vendor_sku_history
left join sku_items on vendor_sku_history.sku_item_id = sku_items.id
left join sku on sku_id = sku.id
left join sku_items_price sip on sip.sku_item_id=sku_items.id and sip.branch_id=$bid
left join sku_items_cost sic on sic.sku_item_id = sku_items.id and sic.branch_id = $bid
left join uom on sku_items.packing_uom_id=uom.id
left join category on sku.category_id = category.id
where $branch_check and not (sku_item_code is null) and sku_items.active=1 
order by $s_field $s_arrow, vendor_sku_history.added desc";
	//print $sql;
	// insert into temporary table first
	$con->sql_query("create temporary table tmp_vsh (unique(sku_item_id)) ignore ($sql)");
	
	// sort descending by history added - to get the latest price info
	$q2 = $con->sql_query("select * from tmp_vsh");
	
	//echo "$branch_check";
	while($r2=$con->sql_fetchrow($q2)){

	    $blocker=unserialize($r2['block_list']);

		if (isset($blocker[$bid])) continue;

		$temp_id=$r2['id'];
		$q3=$con->sql_query("select uom.id as cost_uom, uom.fraction as uom_fraction, uom.code as uom_code
from grn_items
left join uom on grn_items.uom_id=uom.id
where sku_item_id=$temp_id".(BRANCH_CODE=='HQ' ? '' : " and grn_items.branch_id=$bid"));
		$r3 = $con->sql_fetchrow($q3);
		if($r3['cost_uom']){
			$r2['uom_id_1']=$r3['cost_uom'];
			$r2['uom_fraction_1']=$r3['uom_fraction'];
			$r2['uom_code_1']=$r3['uom_code'];
			$r2['cost_indicate'] = 'GRN';
		}
		else{
			$q4=$con->sql_query("select uom.id as cost_uom, uom.fraction as uom_fraction, uom.code as uom_code
	from po_items
	left join uom on po_items.order_uom_id=uom.id
	where sku_item_id=$temp_id".(BRANCH_CODE=='HQ' ? '' : " and po_items.branch_id=$bid"));
			$r4 = $con->sql_fetchrow($q4);
			if($r4['cost_uom']){
				$r2['uom_id_1']=$r4['cost_uom'];
				$r2['uom_fraction_1']=$r4['uom_fraction'];
				$r2['uom_code_1']=$r4['uom_code'];
				$r2['cost_indicate'] = 'PO';
			}
			else{
				$r2['uom_id_1']=$r2['cost_uom'];
				$r2['uom_fraction_1']=$r2['uom_fraction'];
				$r2['uom_code_1']=$r2['uom_code'];
				$r2['cost_indicate'] = 'SKU';
			}
		}
		$temp_items[$r2['sku_item_id']]=$r2;
	}
	$con->sql_freeresult($q2);
	
	if($config['po_vendor_listing_enable_check_master_vendor']){
		if ($temp_items){
			$str_exclude_idlist = "and si.id not in (".join(",",array_keys($temp_items)).")";
		}
		
		/*
		vendor_sku_history.sku_item_id, sku_id, sku_item_code, sku_items.id, sku_items.description, sku_items.artno, sku_items.mcode,sku_items.block_list, sku.varieties, vendor_sku_history.selling_price, vendor_sku_history.cost_price, category.department_id as dept_id, sip.price as sell_price_1, sku_items.selling_price as sell_price_2, uom.id as cost_uom, uom.fraction as uom_fraction, uom.code as uom_code, sic.grn_cost, sic.qty as stock, sic.l90d_pos, sic.l30d_pos
		*/
		$q1 = $con->sql_query($sql = "select si.id as sku_item_id, si.sku_id, si.sku_item_code, si.id, si.block_list, si.description, si.artno, si.mcode, sku.varieties, ifnull(sip.price is null, si.selling_price) as selling_price, sic.grn_cost as cost_price, category.department_id as dept_id, sip.price as sell_price_1, si.selling_price as sell_price_2, uom.id as cost_uom, uom.fraction as uom_fraction, uom.code as uom_code, uom.fraction as master_uom_fraction, sic.grn_cost, sic.qty as stock, sic.l90d_pos, sic.l30d_pos
		from sku_items si
		left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
		left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
		left join sku on sku_id = sku.id 
		left join category on sku.category_id = category.id 
		left join uom on si.packing_uom_id=uom.id
		where category.department_id in ($dept_id) $str_exclude_idlist and si.active=1 and sku.vendor_id=$vendor_id
		order by si.id, si.description, si.artno, si.mcode");
		//print $sql;
		while($r2 = $con->sql_fetchassoc($q1)){
			// skip if item is blocked
			$blocker = unserialize($r2['block_list']);
			if (isset($blocker[$bid])) continue;

			$temp_items[$r2['id']] = $r2;	
		}
		$con->sql_freeresult($q1);
		
	}

	$q5=$con->sql_query("select * from tmp_vendor_po_items where login_tickets_ac=".ms($r['ac']));
	$r5 = $con->sql_fetchrow($q5);
	$con->sql_freeresult($q5);
	$r5['balance'] = unserialize($r5['balance']);
	$r5['uom_id'] = unserialize($r5['uom_id']);
	$r5['order_price'] = unserialize($r5['order_price']);
	$r5['qty'] = unserialize($r5['qty']);
	$r5['foc'] = unserialize($r5['foc']);
	$r5['remark'] = unserialize($r5['remark']);

	if($r5['qty']){
		foreach($r5['qty'] as $k=>$v){
			$uom_id=$r5['uom_id'][$k];
			if($v>0){
				$q6=$con->sql_query("select * from uom where id=$uom_id");
				$r6=$con->sql_fetchrow($q6);
				$r5['uom_id'][$k]=$r6['id'];
				$r5['uom_code'][$k]=$r6['code'];
				$r5['uom_fraction'][$k]=$r6['fraction'];
			}
		}
	}
	$temp=$r5;

	if($_REQUEST['a']=='confirm' || $_REQUEST['a']=='print'){
		$form['is_confirm']=1;
		if($temp_items){
			foreach($temp_items as $k=>$v){
				$val=$temp_items[$k]['sku_item_id'];
				foreach($r5['qty'] as $k1=>$v1){
					if($k1==$val && ($v1>0 || $r5['foc'][$k1]>0)){
						$items[$v['sku_item_id']]=$v;
					}
				}
			}
		}
	}
	else{
		$items=$temp_items;
	}
	//echo"<pre>";print_r($temp);echo"</pre>";
	$smarty->assign("temp",$temp);	
	$q6=$con->sql_query("select po.id as id, po_items.sku_item_id as sku_item_id
from po
left join po_items on po_id=po.id and po_items.branch_id=$bid
where login_ticket_ac='$r[ac]'");
	while ($r6 = $con->sql_fetchrow($q6)){
		$used_items['used_items'][$r6['sku_item_id']]=$r6['sku_item_id'];
	}
	//echo"<pre>";print_r($used_items);echo"</pre>";
	$smarty->assign("used_items",$used_items);
	//echo"<pre>";print_r($items);echo"</pre>";
	return $items;
}

function save_vendor_items(){
	global $con, $config, $r;
	
	$form['login_tickets_ac'] =$r['ac'] ;
	//$form['balance'] = serialize($_REQUEST['balance']);
	//$form['uom_id'] = serialize($_REQUEST['uom_id']);
	//$form['order_price'] = serialize($_REQUEST['order_price']);
	//$form['qty'] = serialize($_REQUEST['qty']);
	//$form['foc'] = serialize($_REQUEST['foc']);
	//$form['remark'] = serialize($_REQUEST['remark']);
	$form['balance'] = $form['uom_id'] = $form['order_price'] = $form['qty'] = $form['foc'] = $form['remark'] = array();
	if($_REQUEST['balance']){
		$show_system_balance = !is_intranet();
		foreach($_REQUEST['balance'] as $sid => $bal){	
			$valid = false;
			if($show_system_balance){
				if(mf($_REQUEST['order_price'][$sid])>0 || mi($_REQUEST['qty'][$sid])>0 || mi($_REQUEST['foc'][$sid])>0 || trim($_REQUEST['remark'][$sid]))
				{
					$valid = true;
				}
			}else{
				if(mf($bal) || mf($_REQUEST['order_price'][$sid])>0 || mi($_REQUEST['qty'][$sid])>0 || mi($_REQUEST['foc'][$sid])>0 || trim($_REQUEST['remark'][$sid])){
					$valid = true;
				}
			}
			
			if($valid){
				$form['balance'][$sid] = $bal;
				$form['uom_id'][$sid] = $_REQUEST['uom_id'][$sid];
				$form['order_price'][$sid] = $_REQUEST['order_price'][$sid];
				$form['qty'][$sid] = $_REQUEST['qty'][$sid];
				$form['foc'][$sid] = $_REQUEST['foc'][$sid];
				$form['remark'][$sid] = $_REQUEST['remark'][$sid];
			}
		}
	}
	$form['balance'] = serialize($form['balance']);
	$form['uom_id'] = serialize($form['uom_id']);
	$form['order_price'] = serialize($form['order_price']);
	$form['qty'] = serialize($form['qty']);
	$form['foc'] = serialize($form['foc']);
	$form['remark'] = serialize($form['remark']);
	$con->sql_query("replace into tmp_vendor_po_items " . mysql_insert_by_field($form, array("login_tickets_ac", "balance", "uom_id", "order_price", "qty", "foc", "remark")));
	
	return true;
}

function ajax_save_vendor_items(){
	$success = save_vendor_items();
	$ret = array();
	if($success){
		$ret['ok'] = 1;
	}else{
		$ret['error'] = 'Save Failed';
	}
	
	print json_encode($ret);
	exit;
}

?>
