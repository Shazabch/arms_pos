<?php
/*
6/9/2008 6:14:10 PM yinsee
- superuser can approve any PO, and is final approval

7/3/2008 4:46:45 PM yinsee
- move get_items_detail and get_selling_price from po.php

8/8/2008 3:48:35 PM yinsee
- add GP(%) display for single branch PO
- calculate GP(%) during startup

1/13/2009 1:19:39 PM yinsee
- fix serialize error in branch selling allocation

8/7/2009 2:53:41 PM Andy
- function get_items_details and load_po_items, add collect master sku packing_uom_id as master_uom_id

2009/10/09 16:10:35 PM Andy
- Add split approval history id if delivery to multiple branch
- Add clear and reset approval history if reset the PO which have duplicate approval history id

11/11/2009 10:53:56 AM edward
- change log type from PO to PURCHASE ORDER

2/3/2010 10:41:59 AM Andy
- Fix PO Cost take from Transfer DO cost even if config not set

6/2/2010 3:53:47 PM Andy
- When Add item, if PO choose to deliver to only HQ, show all branches sales trend. (Default ON, can add config to disable)

11/9/2010 12:08:49 PM Andy
- Add checking for canceled/deleted and prevent it to be edit.

1/25/2011 10:37:10 AM Andy
- Fix a bugs which cause multiple approval make document stuck.

3/8/2011 1:55:44 PM Andy
- Add get sku_id in function load_po_items()

6/2/2011 5:01:15 PM Andy
- Add show photo at PO.

6/6/2011 1:48:23 PM Andy
- Fix sql column "cost_price" got ambiguous bugs.

6/24/2011 5:11:29 PM Andy
- Make all branch default sort by sequence, code.

9/6/2011 4:46:04 PM Andy
- Increase maintenance version checking.

9/9/2011 10:14:44 AM Alex
- Add missing data=>load_po_items()

9/13/2011 1:16:17 PM Andy
- Fix missing "A.Bal" and "P.Bal" when confirm PO.

9/14/2011 12:42:14 PM Alex
- fix total selling and total cost calculation bugs

9/23/2011 2:15:43 PM Justin
- Applied when get item list, pick up sku item's doc_allow_decimal.

11/7/2011 6:01:08 PM Andy
- Fix PO show unrelated user in allowed user selection.

11/24/2011 4:48:57 PM Andy
- Add show "Delivered GRN" for those delivered PO.

12/2/2011 3:46:43 PM Justin
- Fixed the bugs where system always show all GRN documents for a PO instead of show the specific GRN document that matched with PO.

1/13/2012 6:02:11 PM Justin
- Added to pickup sku ID, size and color.

3/8/2012 11:03:02 AM Alex
- add parent stock balance and parent sales trend
- fix sql bugs of calling sum with no group by 
- fix bugs by return an array value => calculate_stock_balance()

3/21/2012 4:23:43 PM Justin
- Added to filter off those inactive users for user selection.

4/10/2012 10:37:42 AM Andy
- Add show relationship between PO and SO.
- Add can show highlight row color.

4/20/2012 11:16:05 AM Alex
- add show packing uom code => get_items_detail()

7/13/2012 5:10:34 PM Justin
- Enhanced to pick up packing UOM fraction.

7/19/2012 11:26:34 AM Justin
- Enhanced to use po item's uom fraction instead of master uom fraction.
- Bug fixed while adding new po item, system shows negative gross profit.

7/20/2012 9:33:34 AM Justin
- Fixed bugs of using wrong uom fraction for selling uom.

8/13/2012 11:49 AM Andy
- Add purchase agreement control.

8/14/2012 3:08 PM Andy
- Add new purchase agreement item type "Multiply".

9/13/2012 5:29 PM Andy
- Add new purchase agreement type, Seasonal.

10/1/2012 3:46 PM Justin
- Bug fixed on take too long to generate PO no.

10/23/2012 5:57 PM Andy
- Increase maintenance version to 165.

3/1/2013 10:59 AM Fithri
- Bugfix: PO add vendor item, the edited qty will missing
- add vendor item change to ajax method

4/4/2013 4:20 PM Fithri
- item last po row, enhance to check < (po date+1 day) and < added
- cost indicate in last po row just show "-"

4/19/2013 3:04 PM Fithri
- bugfix : last PO item row does not appear when add SKU

6/27/2013 3:25 PM Andy
- Enhance to get last approval user info when load po header.

7/29/2013 11:42 AM Andy
- Enhance to load more_info when select approval history.

10/1/2013 2:32 PM Justin
- Enhanced to allow user can maintain and send email custom message to vendor.

10/7/2013 1:35 PM Justin
- Enhanced to load default values for HQ payment.

12/27/2013 10:29 AM Fithri
- when create PO at branch allow to have user selection to send PM/email

5/21/2014 11:34 AM Justin
- Enhanced to pickup HQ cost.

11/8/2014 10:40 AM Justin
- Enhanced to have GST calculation and settings.
- Enhanced to add config checking while loading gst list.

3/11/2015 11:10 AM Justin
- Bug fixed on GST amount sum up twice while printing report.

11:36 AM 3/25/2015 Andy
- Fix when add item getting the wrong default input tax.

3/30/2015 3:53 PM Justin
- Enhanced the report to show N.S.P and S.S.P when PO under GST status.

4/6/2015 10:39 AM Justin
- Bug fixed GST info get wrongly from other branch.

4/17/2015 5:19 PM Justin
- Bug fixed on selling price include gst always show zero once revoke from cancelled PO.

5/15/2015 10:52 AM Justin
- Enhanced to calculate total selling include GST.

5/20/2015 3:38 PM Justin
- Bug fixed on total amount did not round to 2 decimal points.

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

2/23/2016 5:59 PM Qiu Ying
- Fix po sellng price wrong

4/5/2016 11:12 AM Andy
- Removed the checking of config grn_do_transfer_update_cost.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.

06/22/2016 15:50 Edwin
- Show price before tax in S.S.P when vendor is not under GST

07/18/2016 16:30 Edwin
- Enhanced on delivery date format changed to YYYY-MM-DD.
- Enhanced on PO items tax code change to flat rate if vendor's gst type is flat rate.

8/23/2016 5:05 PM Andy
- Fix po foc item wrong ssp.

9/9/2016 12:01 PM Andy
- Increase maintenance version checking to 297.

12/1/2016 4:11 PM Andy
- Increase maintenance version checking to 304.

12/6/2016 9:39 AM Andy
- Hide ARMS user from user list.

1/11/2017 3:11 PM Andy
- Enhanced to check gst selling price when branch is under gst.

3/2/2017 10:57 AM Andy
- Enhanced to auto recalculate all PO Amount using reCalcatePOUsingOldMethod() when saved, revoke and approve multiple branch PO.
- Increase maintenance version checking to 310.

3/27/2017 3:01 PM Andy
- Change reCalcatePOUsingOldMethod() to reCalcatePOAmt().

10/2/2017 5:20 PM Justin
- Enhanced to call sales trend from skuManager.php. 

11/13/2017 11:34 AM Justin
- Bug fixed on Sales Trend missing when delivery branch is HQ.

11/16/2017 10:55 AM Andy
- Increase maintenance version checking to 337.

4/4/2018 11:38 AM Andy
- Added Foreign Currency feature.

6/26/2018 12:24 PM Andy
- Enhanced to get inclusive_tax for get_last_po_item()
- Fixed last po item cannot be loaded when PO is created at HQ and deliver to sub branch.

8/29/2018 4:11 PM Andy
- Increase maintenance version checking to 357.

10/12/2018 5:25 PM Andy
- Increase maintenance version checking to 368.

11/30/2018 2:59 PM Andy
- Fixed php7.1 error when HQ deliver to multiple branch.

11/27/2018 4:38 PM Justin
- Enhanced to load Quotation cost when getting item detail.

5/22/2019 9:53 AM William
- Pickup report_prefix for enhance "GRN".

12/6/2019 3:44 PM William
- Pickup "category_sales_trend_cache" table data when PO has item. 

2/4/2020 9:25 AM William
- Fixed bug "category_sales_trend_cache" not show when add from sub branch.

4/15/2020 2:43 PM William
- Enhanced to block reset when got config "monthly_closing" and document date has closed.

3/26/2021 10:25 AM Ian
-Enhanced to check Block GRN, if got block grn, then not allow to add item & foc item/unable to enter qty.
-Retain the value after refresh
*/
require_once("vendor_sku.include.php");
include("include/class.phpmailer.php");
$maintenance->check(368);
$maintenance->check(310,true);

$pa_qty_type_list = array('fixed' => 'Fixed', 'range'=>'Range', 'multiply' => 'Multiply');
if($smarty)	$smarty->assign('pa_qty_type_list', $pa_qty_type_list);

$pa_type_list = array('normal'=>'Normal', 'seasonal'=>'Seasonal');
if($smarty)	$smarty->assign('pa_type_list', $pa_type_list);

// load gst list
$output_gst_list = construct_gst_list('supply');
$smarty->assign("output_gst_list", $output_gst_list);
$input_gst_list = construct_gst_list('purchase');
$smarty->assign("input_gst_list", $input_gst_list);

$q1 = $con->sql_query("select * from vendor order by id");
while($r = $con->sql_fetchassoc($q1)){
	$vd_list[$r['id']] = $r;
}
$con->sql_freeresult($q1);
$smarty->assign("vd_list", $vd_list);

function load_po_header($po_id, $branch_id){
	global $con, $smarty, $sessioninfo, $user_level, $config, $appCore;
	
	$q1=$con->sql_query("select po.*, user.u as user, user2.u as cancel_user, vendor.description as vendor, branch.code as branch, branch2.code as po_branch, branch.report_prefix , bah.approvals, vendor.gst_register, vendor.gst_start_date
	from po 
	left join user on user_id=user.id 
	left join user user2 on cancel_by=user2.id 
	left join vendor on vendor_id=vendor.id 
	left join branch on branch_id=branch.id 
	left join branch branch2 on po_branch_id=branch2.id 
	left join branch_approval_history bah on bah.id=po.approval_history_id and bah.branch_id=po.branch_id
	where po.id=$po_id and po.branch_id=$branch_id");
	$form=$con->sql_fetchrow($q1);
	if (!$form) return false;
	if ($form['login_ticket_ac']!='') $form['is_vendor_po'] = true;
	$form['allowed_user']=unserialize($form['allowed_user']);
	
	$form['deliver_to']=unserialize($form['deliver_to']);
	$form['allocation_info']=unserialize($form['allocation_info']);
	if(is_array($form['deliver_to']) /* && BRANCH_CODE=='HQ' */){
			
		foreach($form['deliver_to'] as $k=>$v){
			$q1=$con->sql_query("select user_id,u from user_privilege
left join user on user_id=user.id
where privilege_code='PO_VIEW_ONLY' and branch_id=$v and user.departments like '%i:$form[department_id];%' and user.is_arms_user=0");
			$temp = array();
			while($r_u = $con->sql_fetchrow($q1)){
				$temp['user'][]=$r_u['u'];
				$temp['user_id'][]=$r_u['user_id'];
			}
			$user_list[$v]=$temp;
		}
		$smarty->assign("user_list",$user_list);	
	}
	else if($form['po_branch_id'] && $form['branch_id']==1){
		$v=$form['po_branch_id'];
		$q1=$con->sql_query("select user_id,u from user_privilege
left join user on user_id=user.id
where privilege_code='PO_VIEW_ONLY' and branch_id=$v and user.departments like '%i:$form[department_id];%' and user.is_arms_user=0");
		$temp = array();
		while($r_u = $con->sql_fetchrow($q1)){
			$temp['user'][]=$r_u['u'];
			$temp['user_id'][]=$r_u['user_id'];
		}
		$user_list[$v]=$temp;
		$smarty->assign("user_list",$user_list);
	}
	else {
		$q1=$con->sql_query("select user_id,u from user_privilege
left join user on user_id=user.id
where privilege_code='PO_VIEW_ONLY' and branch_id=".mi($form['branch_id'])." and user.departments like '%i:$form[department_id];%' and user.is_arms_user=0");
		$temp = array();
		while($r_u = $con->sql_fetchrow($q1)){
			$temp['user'][]=$r_u['u'];
			$temp['user_id'][]=$r_u['user_id'];
		}
		$user_list[$form['branch_id']]=$temp;
		$smarty->assign("user_list",$user_list);
	}
	
	$form['delivery_vendor']=unserialize($form['delivery_vendor']);	
	if(is_array($form['delivery_vendor'])){
		foreach ($form['delivery_vendor'] as $bid => $vid){
			$con->sql_query("select description from vendor where id=" . mi($vid));
			$r=$con->sql_fetchrow();
			$form['delivery_vendor_name'][$bid]=$r[0];
		}
	}
	
	if(is_array($form['deliver_to'])){
		$form['delivery_date']=unserialize($form['delivery_date']);
		$form['cancel_date']=unserialize($form['cancel_date']);
		$form['partial_delivery']=unserialize($form['partial_delivery']);
		foreach($form['deliver_to'] as $k=>$v){
			if($form['delivery_date'][$v]){
                $form['delivery_date'][$v] = dmy_to_sqldate($form['delivery_date'][$v]);
                $form['cancel_date'][$v] = dmy_to_sqldate($form['cancel_date'][$v]);
                    
                if($form['po_option'] == 3){ // found it is HQ payment, need to preset the HQ control
					$form['hq_delivery_date'] = $form['delivery_date'][$v];
					$form['hq_cancel_date'] = $form['cancel_date'][$v];
					$form['hq_partial_delivery'] = $form['partial_delivery'][$v];
					break;
				}
			}
		}
	}else {
        $form['delivery_date'] = dmy_to_sqldate($form['delivery_date']);
        $form['cancel_date'] = dmy_to_sqldate($form['cancel_date']);
    }
	
	$form['sdiscount']=unserialize($form['sdiscount']);
	if(is_array($form['sdiscount'])) 
		$form['sdiscount']=$form['sdiscount'][0];
	
	$form['rdiscount']=unserialize($form['rdiscount']);
	if(is_array($form['rdiscount'])) 
		$form['rdiscount']=$form['rdiscount'][0];
	
	$form['ddiscount']=unserialize($form['ddiscount']);
	if(is_array($form['ddiscount'])) 
		$form['ddiscount']=$form['ddiscount'][0];
	
	$form['misc_cost']=unserialize($form['misc_cost']);
	if(is_array($form['misc_cost'])) 
		$form['misc_cost']=$form['misc_cost'][0];

	$form['transport_cost']=unserialize($form['transport_cost']);
	if(is_array($form['transport_cost']))
		$form['transport_cost']=$form['transport_cost'][0];
	
	$form['remark']=unserialize($form['remark']);
	if(is_array($form['remark']))
		$form['remark']=$form['remark'][0];
		
	$form['remark2']=unserialize($form['remark2']);
	if(is_array($form['remark2']))
		$form['remark2']=$form['remark2'][0];

	if ($form['approval_history_id']>0){
		$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u, user.level,user.fullname,i.more_info
from branch_approval_history_items i
left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
left join user on i.user_id = user.id
where h.ref_table='po' and i.branch_id=$branch_id and i.approval_history_id=$form[approval_history_id] 
order by i.timestamp");
		$approval_history = array();
		while($r = $con->sql_fetchassoc($q2)){
			$r['more_info'] = unserialize($r['more_info']);
			
			$approval_history[] = $r;
			if($r['status']==1){	// approved
				$form['last_approval_user']['u'] = $r['u'];
				$form['last_approval_user']['fullname'] = $r['fullname'];
				$form['last_approval_user']['position'] = '';
				
				foreach($user_level as $position => $lv){
					if($lv == $r['level']){
						$form['last_approval_user']['position'] = $position;
						break;	
					}
				}
				
			}
		}
		$con->sql_freeresult($q2);
		$smarty->assign("approval_history", $approval_history);
	}
	
	$params = array();
	$params['user_id'] = $sessioninfo['id'];
	$params['id'] = $form['approval_history_id'];
	$params['branch_id'] = $branch_id;
	// check is last approval
	$is_last = check_is_last_approval_by_id($params, $con);
	if(!$is_last){ // check is approval
    $params['check_is_approval'] = true;
    $is_approval = check_is_last_approval_by_id($params, $con);
  }
  
  if($is_last){
    $form['is_approval'] = 1;
	  $form['last_approver'] = 1;
  }elseif($is_approval){
    $form['is_approval'] = 1;
  }
  
	/*if ($sessioninfo['level']>=9999)	// superuser approve and final
	{
	    $form['is_approval'] = 1;
	    $form['last_approver'] = 1;
	}
	else
	{
		if (preg_match("/^\|$sessioninfo[id]\|/", $form['approvals']))
		    $form['is_approval'] = 1;
		if (preg_match("/^\|\d+\|$/", $form['approvals']))
		    $form['last_approver'] = 1;
	}*/
	
	// check if this PO is under GST	
	if($form['active'] && !$form['status'] && !$form['approved']){
		if($config['enable_gst'] && !$form['currency_code']){
			$prms = array();
			$prms['vendor_id'] = $form['vendor_id'];
			$prms['date'] = $form['po_date'];
			$form['is_under_gst'] = check_gst_status($prms);		
		}
	}
	
	if($config['enable_gst']){
		$prms = array();
		$prms['branch_id'] = $branch_id;
		$prms['date'] = $form['po_date'];
		$branch_is_under_gst = check_gst_status($prms);
        $form['branch_is_under_gst'] = $branch_is_under_gst;
	}
	
	// got turn on currency
	if($config['foreign_currency']){
		// Load Currency Rate History
		$form['currency_rate_history'] = $appCore->poManager->loadPOCurrencyRateHistory($branch_id, $po_id);
		
		// load Currency Code List
		$appCore->poManager->loadPOCurrencyCodeList($form, array('smarty_assign'=>'foreignCurrencyCodeList'));
	}
	
	$po_branch_id_list = array();
	if($form['branch_id'] == 1){
		if($form['deliver_to']){
			$po_branch_id_list = $form['deliver_to'];
		}else{
			$po_branch_id_list[] = $form['po_branch_id'];
		}
	}else{
		if($form['po_branch_id']){
			$po_branch_id_list[] = $form['po_branch_id'];
		}else{
			$po_branch_id_list[] = $form['branch_id'];
		}
	}
	if($form['department_id'] && $po_branch_id_list) $form['category_sales_trend'] = load_category_sales_trend($form['department_id'], $po_branch_id_list);
	
	return $form;
}

function load_po_items($form, $use_tmp=false, $tmp_ids=array()){
	global $con, $branch_id, $sessioninfo, $smarty, $config, $input_gst_list, $output_gst_list;
	
	$owner_filter='';	
	$po_id=mi($form['id']);	
	
	if($use_tmp){
		$table="tmp_po_items";
		$owner_filter=" and tpi.user_id=$sessioninfo[id] ";
	}
	else{
		$table="po_items";
	}
	
	$tmp_id_filter = '';
	$sp_inclusive_tax = 0;
	if ($use_tmp && $tmp_ids) $tmp_id_filter = ' and tpi.id in ('.join(',',$tmp_ids).') ';
	
	$q1=$con->sql_query($abc="select tpi.*, si.sku_item_code,si.additional_description, si.description, si.artno, si.mcode,pkuom.code as packing_uom, if(pkuom.fraction > 1, uom.fraction, pkuom.fraction) as selling_uom_fraction, pkuom.fraction as master_uom_fraction, si.link_code, uom.code as order_uom, u2.code as selling_uom,si.packing_uom_id as master_uom_id, si.sku_id, sai.photo_count, si.sku_apply_items_id, si.doc_allow_decimal, si.sku_id, si.size, si.color,tpi.so_branch_id,tpi.so_item_id, si.hq_cost, sku.category_id
	from $table tpi  
	left join sku_items si on tpi.sku_item_id = si.id
	left join sku on sku.id = si.sku_id
	left join sku_apply_items sai on sai.id=si.sku_apply_items_id
	left join uom on uom.id = tpi.order_uom_id
	left join uom u2 on u2.id=tpi.selling_uom_id
	left join uom pkuom on pkuom.id = si.packing_uom_id
	where tpi.po_id=$po_id and tpi.branch_id=$branch_id $owner_filter $tmp_id_filter
	order by tpi.id");//print $abc;
	$po_items=array();
	$total=array();
    $foc_annotations=array();
    $foc_id=0;
    
	$sku_use_photo_list = array();
	
	while($r1=$con->sql_fetchrow($q1)){
		$sid = mi($r1['sku_item_id']);
		$sku_apply_items_id = mi($r1['sku_apply_items_id']);
		
		$r1['deliver_to']=unserialize($r1['deliver_to']);
		//Check if branch is HQ
		if($branch_id==1)
		{
			//keep the block grn data after refresh
			$con->sql_query("select block_list,doc_block_list from sku_items where id = $sid limit 1");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			$block_list_array = unserialize($tmp['block_list']);
			$doc_block_list_array = unserialize($tmp['doc_block_list']);		
			$sel_branch =$form['deliver_to'];

			//assign an array
			$blocked_branch=array();
				
			//Check how many branches is blocked
			foreach($sel_branch as $br_id)
			{		
				if ($block_list_array || $doc_block_list_array) {
					if($br_id==1)$in_block_list = isset($block_list_array[$br_id]);
					else $in_block_list =false;			

					$in_grn_block_list = isset($doc_block_list_array['grn'][$br_id]);
					//either block po/grn will add 1 to counter
					if ($in_block_list||$in_grn_block_list)
					{
						$r1['blocked_branch'][$br_id] = true;
					} 		
				}			
			
			}		
		}

		if($_REQUEST['a']!='print'){
			$r1['last_po']=get_last_po_item($r1['sku_item_id'], $branch_id, $form['po_branch_id'], $form['po_date'], $form['id']);
		}
		
		if(is_array($r1['deliver_to']))
			$r1['balance']=unserialize($r1['balance']);
			
	    if($r1['is_foc']){
			$foc_id++;
			$r1['foc_id']=$foc_id;
		}
	    $r1['foc_share_cost']=unserialize($r1['foc_share_cost']);
	    if($r1['foc_share_cost']){
			foreach($r1['foc_share_cost'] as $i=>$dummy){
			    if ($foc_annotations[$i]!=''){
					$foc_annotations[$i].="/";
				}
				$foc_annotations[$i].="$foc_id";
			}		
		}
		$r1['selling_price_allocation']=unserialize($r1['selling_price_allocation']);
		$r1['gst_selling_price_allocation']=unserialize($r1['gst_selling_price_allocation']);
		$r1['qty_allocation']=unserialize($r1['qty_allocation']);
		$r1['qty_loose_allocation']=unserialize($r1['qty_loose_allocation']);
		$r1['foc_allocation']=unserialize($r1['foc_allocation']);
		$r1['foc_loose_allocation']=unserialize($r1['foc_loose_allocation']);
		$r1['sales_trend']=unserialize($r1['sales_trend']);
		$r1['stock_balance']=unserialize($r1['stock_balance']);
		$r1['parent_stock_balance']=unserialize($r1['parent_stock_balance']);
		$r1['item_allocation_info']=unserialize($r1['item_allocation_info']);
		if($use_tmp){
			$_REQUEST['deliver_to'] = $form['deliver_to'];
			$_REQUEST['po_branch_id'] = $form['po_branch_id'];
			$sales=get_sales_trend($r1['sku_item_id']);
			$balance=get_stock_balance($r1['sku_item_id']);
			$r1=array_merge($r1, $sales, $balance);		
		}
		//$r1['total_selling'] = 0;
		if($r1['selling_uom_fraction']==0)
			$r1['selling_uom_fraction']=1;
		if($r1['order_uom_fraction']==0)
			$r1['order_uom_fraction']=1;
			
		if(is_array($form['deliver_to'])){
			$r1['balance'] = unserialize($r1['balance']);
			$r1['qty']=0;
		    foreach($form['deliver_to'] as $v=>$k){
				$q = $r1['qty_allocation'][$k]*$r1['order_uom_fraction'] + $r1['qty_loose_allocation'][$k];
			    $r1['qty'] += $q;
			    $total['qty_allocation'][$k] += $q;
			    $total['qty'] += $q;
				$r1['row_qty'] += $q;
				
			    $q2 = $r1['foc_allocation'][$k]*$r1['order_uom_fraction'] + $r1['foc_loose_allocation'][$k];
			    $r1['foc'] += $q2;
			    $total['foc_allocation'][$k] += $q2;
			    $total['foc'] += $q2;
			    $r1['row_foc'] += $q2;
			    
			    $r1['branch_qty'][$k] += $q + $q2;
			    $r1['branch_qty']['total'] += $q + $q2;

			    $r1['ctn'] += $r1['qty_allocation'][$k] + $r1['foc_allocation'][$k];
				$total['ctn'] += $r1['qty_allocation'][$k] + $r1['foc_allocation'][$k];
				//$r1['total_selling'] += ($q+$q2)/$r1['selling_uom_fraction']*$r1['selling_price_allocation'][$k];
				$r1["br_sp"][$k] = ($q+$q2)/$r1['selling_uom_fraction']*$r1['selling_price_allocation'][$k];
				$r1["br_cp"][$k] = $q/$r1['order_uom_fraction']*$r1['order_price'];

				$total['br_sp'][$k] += $r1["br_sp"][$k];
				$total['br_cp'][$k] += $r1["br_cp"][$k];
				
				$total['total_ctn'][$k] += $r1['qty_allocation'][$k] + $r1['foc_allocation'][$k];
				$total['total_pcs'][$k] += $r1['qty_loose_allocation'][$k] + $r1['foc_loose_allocation'][$k];
				
				if($form['branch_is_under_gst']){
					//$r1['total_gst_selling'] += ($q+$q2)/$r1['selling_uom_fraction']*$r1['gst_selling_price_allocation'][$k];
				}
			}
			//$r1['gamount'] = $r1['qty']/$r1['order_uom_fraction']*$r1['order_price'];
		}
		else{
		    $r1['row_qty'] = $r1['qty']*$r1['order_uom_fraction']+$r1['qty_loose'];
		    $r1['row_foc'] = $r1['foc']*$r1['order_uom_fraction']+$r1['foc_loose'];

		    $total['qty']+=$r1['row_qty'];
		    
		    $total['foc']+=$r1['row_foc'];

		    $total['ctn']+=$r1['qty']+$r1['foc'];

		    //$r1['total_selling'] = ($r1['row_qty']+$r1['row_foc'])/$r1['selling_uom_fraction']*$r1['selling_price'];

			//$r1["br_sp"] = $r1['total_selling'];
			//$r1["br_cp"] = $r1['row_qty']/$r1['order_uom_fraction']*$r1['order_price'];

			//$total['br_sp'] += $r1["br_sp"];
			//$total['br_cp'] += $r1["br_cp"];

		    //$r1['gamount'] = ($r1['qty']+($r1['qty_loose']/$r1['order_uom_fraction']))*$r1['order_price'];
			
			//if($form['branch_is_under_gst']){
				//$r1['total_gst_selling'] = ($r1['row_qty']+$r1['row_foc'])/$r1['selling_uom_fraction']*$r1['gst_selling_price'];
			//}
		}

		//$total['sell'] += $r1['total_selling'];
		//$total['gst_sell'] += $r1['total_gst_selling'];

	    //if (!$r1['is_foc'])
		//	$total['gamount'] += $r1['gamount'];
		//$r1['amount'] = $r1['gamount'];

		//if ($r1['tax']>0)
		//	$r1['amount'] *= ($r1['tax']+100)/100;
				
		//if ($r1['discount']){
			// $camt = $r1['amount'];
			// $r1['amount'] = parse_formula($r1['amount'],$r1['discount']);
			// $r1['disc_amount'] = $camt - $r1['amount'];
		// }
		
		if(!$r1['artno_mcode']){
			if($r1['artno'])
				$r1['artno_mcode']=$r1['artno'];
			else
				$r1['artno_mcode']=$r1['mcode'];				
		}
	
		// if (!$r1['is_foc'])
 			// $total['amount'] += round($r1['amount'], 2);

		// calculate branch gp

		if(is_array($form['deliver_to'])){
			$branch_disc = 0; $branch_gp = 0;
		    foreach($form['deliver_to'] as $v=>$k){
				if ($r1['br_sp'][$k]>0) 
				{
					$currency_rate = 1;
					if($form['currency_code'])	$currency_rate = $form['currency_rate'];
					$branch_disc = $r1['discount_amt'] * $r1['branch_qty'][$k] / $r1['branch_qty']['total'];
					$branch_gp = ($r1['br_sp'][$k]-(($r1['br_cp'][$k]-$branch_disc)*$currency_rate))/$r1['br_sp'][$k];
					
					$r1['branch_gp'][$k] = round($branch_gp*100,2);
				}
			}
		}
		
		if($config['po_show_photo']){
			$r1['photo'] = po_get_sku_item_photo($sid, $sku_apply_items_id, $r1, $sku_use_photo_list);
		}
		
		if($form['active'] && $config['allow_sales_order'] && $r1['so_branch_id'] && $r1['so_item_id']){
			// get sales order item relationship
			$q_so = $con->sql_query("select soi.*,so.order_no
			from sales_order_items soi
			left join sales_order so on so.branch_id=soi.branch_id and so.id=soi.sales_order_id
			where soi.branch_id=".mi($r1['so_branch_id'])." and soi.id=".mi($r1['so_item_id']));
			$tmp_so_info = $con->sql_fetchassoc($q_so);
			$con->sql_freeresult($q_so);
			
			if($tmp_so_info){
				$r1['sales_order_items'] = $tmp_so_info;
			}
		}
        
        $prms = array();
		$prms['branch_id'] = $branch_id;
		$prms['date'] = $form['po_date'];
		$branch_is_under_gst = check_gst_status($prms);
        $form['branch_is_under_gst'] = $branch_is_under_gst;
        
		if($form['branch_is_under_gst']) {
			// if found it is not being used previous and it is refresh, then load new gst info
			//if($_REQUEST['a'] == "refresh"){
            $is_inclusive_tax = get_sku_gst("inclusive_tax", $sid);
			if(!$r1['selling_gst_id']){
				$output_gst = get_sku_gst("output_tax", $sid);
				if($output_gst){
					$r1['selling_gst_id'] = $output_gst['id'];
					$r1['selling_gst_code'] = $output_gst['code'];
					$r1['selling_gst_rate'] = $output_gst['rate'];
				}else{
					$r1['selling_gst_id'] = $output_gst_list[0]['id'];
					$r1['selling_gst_code'] = $output_gst_list[0]['code'];
					$r1['selling_gst_rate'] = $output_gst_list[0]['rate'];
				}
			}
            
            // get inclusive tax follow by sku > category
			$r1['inclusive_tax'] = get_sku_gst("inclusive_tax", $r1['sku_item_id']);
			
			if($r1['inclusive_tax'] == "yes") $sp_inclusive_tax = 1;
            
            if($r1['selling_gst_id']){
                $prms = array();
                $prms['gst_id'] = $r1['selling_gst_id'];
                $prms['gst_code'] = $r1['selling_gst_code'];
                $prms['gst_rate'] = $r1['selling_gst_rate'];
                $prms['gst_list'] = $output_gst_list;
                $output_gst_list = check_and_extend_gst_list($prms); // selling GST
            }
        }
        
        if($form['is_under_gst']) {
			if(!$r1['cost_gst_id']){
				$input_gst = get_sku_gst("input_tax", $sid);
				if($input_gst){
					$r1['cost_gst_id'] = $input_gst['id'];
					$r1['cost_gst_code'] = $input_gst['code'];
					$r1['cost_gst_rate'] = $input_gst['rate'];
				}else{
					$r1['cost_gst_id'] = $input_gst_list[0]['id'];
					$r1['cost_gst_code'] = $input_gst_list[0]['code'];
					$r1['cost_gst_rate'] = $input_gst_list[0]['rate'];
				}
			}
			//}
            
            $con->sql_query("select gst_register from vendor where id = ".$form['vendor_id']);
            $r = $con->sql_fetchassoc();
            $con->sql_freeresult();
            $con->sql_query("select id, code, rate from gst where code = 'TX-FR'");
            $s = $con->sql_fetchassoc();
            $con->sql_freeresult();
            if($r['gst_register'] == $s['id']) {
                $r1['cost_gst_id'] = $s['id'];
				$r1['cost_gst_code'] = $s['code'];
				$r1['cost_gst_rate'] = $s['rate'];
            }
            
			if(!$r1['is_foc']){
				if($r1['row_qty']){
					// calculate GST cost
					/*$order_price = round($r1['amount'] / $r1['row_qty'], $config['global_cost_decimal_points']);
					$r1['cost_gst_amt'] = round($order_price*$r1['cost_gst_rate']/100, $config['global_cost_decimal_points']);
					$r1['row_cost_gst'] = round($r1['cost_gst_amt'] * $r1['row_qty'], 2);
					$r1['row_cost_gst_amt'] = $r1['amount'] + $r1['row_cost_gst'];
					$total['gst_rate_amount'] += round($r1['row_cost_gst'], 2);
					$total['gst_amount'] += round($r1['row_cost_gst_amt'], 2);*/
					$r1['unit_gst_incl_foc'] = $r1['item_nett_amt'] / ($r1['row_qty']+$r1['row_foc']) * $r1['cost_gst_rate'] / 100;
				}
			}
			
			
			// gst
            if($r1['cost_gst_id']){
                $prms = array();
                $prms['gst_id'] = $r1['cost_gst_id'];
                $prms['gst_code'] = $r1['cost_gst_code'];
                $prms['gst_rate'] = $r1['cost_gst_rate'];
                $prms['gst_list'] = $input_gst_list;
                $input_gst_list = check_and_extend_gst_list($prms); // cost GST
            }
		}
		
		$po_items[$r1['id']] = $r1;
	}
	$con->sql_freeresult($q1);
	
	/*print "<pre>";
	print_r($po_items);
	print "</pre>";*/
	//print_r($total);
	// calculate grand total
	// foreach ($total as $k=>$dummy){
	    // $a = $total['amount'];
	    // $a = parse_formula($a,$form['misc_cost'],true);
	    // $tmpa = $a;
	    // $a = parse_formula($a,$form['sdiscount'],false);
	    // $total['sdiscount_amount'] = $tmpa - $a;
	    // $b = $a;
	    // $a = parse_formula($a,$form['rdiscount'],false); // hidden discount
	    // $a = parse_formula($a,$form['ddiscount'],false); // hidden discount (deduct cost)
	    // $a += $form['transport_cost'];
	    // $b += $form['transport_cost'];
	    // $total['final_amount2'] = $b;
	    // $total['final_amount'] = $a;
		
	    // $a = $total['gst_amount'];
	    // $a = parse_formula($a,$form['misc_cost'],true);
	    // $tmpa = $a;
	    // $a = parse_formula($a,$form['sdiscount'],false);
	    // $total['sdiscount_gst_amount'] = $tmpa - $a;
	    // $b = $a;
	    // $a = parse_formula($a,$form['rdiscount'],false); // hidden discount
	    // $a = parse_formula($a,$form['ddiscount'],false); // hidden discount (deduct cost)
	    // $a += $form['transport_cost'];
	    // $b += $form['transport_cost'];
	    // $total['final_gst_amount2'] = $b;
	    // $total['final_gst_amount'] = $a;
	// }
	
	$smarty->assign("total", $total);
	$smarty->assign("foc_annotations", $foc_annotations);
	$smarty->assign("output_gst_list", $output_gst_list);
	$smarty->assign("input_gst_list", $input_gst_list);
	$smarty->assign("sp_inclusive_tax", $sp_inclusive_tax);
	if($form['department_id'] && $po_items) $smarty->assign("disabled_dept", 1);	
	
	/*
	echo '<pre>';
	print_r($po_items);
	echo '</pre>';
	*/
	//print_r($po_items);
	return $po_items;
}

// this is now real PO
function post_process_po($poid, $branch_id){
	global $con,$sessioninfo,$config, $appCore;

	$con->sql_query("select * from po where id=$poid and branch_id=$branch_id");
	$po_head = $con->sql_fetchrow();
	$ret = array();
	if ($po_head['po_option'] != 3)
	{
		if($config['enable_gst']){
			$prms = array();
			$prms['branch_id'] = $branch_id;
			$prms['date'] = $po_head['po_date'];
			$po_head['branch_is_under_gst'] = check_gst_status($prms);
		}
		
		
		$po_head['allowed_user'] = unserialize($po_head['allowed_user']);
		if ($po_head['po_option']==0){
			//just HQ opened PO allow
			if($po_head['branch_id']==1){
				$po_bid=$po_head['po_branch_id'];
				send_notification($poid, $branch_id, $po_head['vendor_id'], $po_head['department_id'], $po_head['allowed_user'][$po_bid]);
			}
			else {
				$po_bid=$branch_id;
				send_notification($poid, $branch_id, $po_head['vendor_id'], $po_head['department_id'], $po_head['allowed_user'][$po_bid]);
			}
			return assign_po_no($poid, $branch_id);
		}
		
		$po_head['deliver_to'] = unserialize($po_head['deliver_to']);
		$po_head['delivery_vendor'] = unserialize($po_head['delivery_vendor']);
	    $po_head['delivery_date'] = unserialize($po_head['delivery_date']);
	    $po_head['cancel_date'] = unserialize($po_head['cancel_date']);
	    $po_head['partial_delivery'] = unserialize($po_head['partial_delivery']);
		$ptop['misc_cost'] = unserialize($po_head['misc_cost']);
		if(is_array($ptop['misc_cost'])){
			$ptop['misc_cost'] = $ptop['misc_cost'][0];
		}
		$ptop['sdiscount'] = unserialize($po_head['sdiscount']);
		if(is_array($ptop['sdiscount'])){
			$ptop['sdiscount'] = $ptop['sdiscount'][0];
		}
		$ptop['rdiscount'] = unserialize($po_head['rdiscount']);
		if(is_array($ptop['rdiscount'])){
			$ptop['rdiscount'] = $ptop['rdiscount'][0];
		}
		$ptop['ddiscount'] = unserialize($po_head['ddiscount']);
		if(is_array($ptop['ddiscount'])){
			$ptop['ddiscount'] = $ptop['ddiscount'][0];
		}
		$ptop['transport_cost'] = unserialize($po_head['transport_cost']);
		if(is_array($ptop['transport_cost'])){
			$ptop['transport_cost'] = $ptop['transport_cost'][0];
		}
		$big_amount = 0;
		
		$i=0;
		$aid = $po_head['approval_history_id'];
		
		// deliver multiple branches
		if(count($po_head['deliver_to'])>1){
			$is_deliver_to_many = true;
			
			$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
			$report_prefix = $con->sql_fetchfield(0);
			$id_no = $report_prefix.sprintf('%05d', $poid);
			$history_log = "approved from PO <a href=\"po.php?a=view&id=".mi($poid)."&branch_id=".mi($branch_id)."\">$id_no</a>";
			
			$con->sql_query("select * from branch_approval_history where id=$aid and branch_id=$branch_id") or die(mysql_error());
			$app_history = $con->sql_fetchrow();
		}
		
		foreach ($po_head['deliver_to'] as $dummy=>$bid){
			$po = $po_head;			
			if ($po_head['delivery_vendor'][$bid]>0)
				$po['vendor_id'] = $po_head['delivery_vendor'][$bid];		
			$po['hq_po_id'] = $poid;
	
			$po['allowed_user']=serialize($po_head['allowed_user']);
			$po['delivery_date'] = $po_head['delivery_date'][$bid];
			$po['cancel_date'] = $po_head['cancel_date'][$bid];
			$po['partial_delivery'] = $po_head['partial_delivery'][$bid];
			$po['branch_id'] = $branch_id;
			$po['po_branch_id'] = $bid;
			$po['added'] = 'CURRENT_TIMESTAMP';
			
			// Foreign Currency
			if($config['foreign_currency']){
				$po['currency_code'] = $po_head['currency_code'];
				$po['currency_rate'] = $po_head['currency_rate'];
			}
	
			if($is_deliver_to_many){
				// own approval history
				// branch_approval_history
				$upd = array();
				$upd['approval_flow_id'] = $app_history['approval_flow_id'];
				$upd['ref_table'] = $app_history['ref_table'];
				$upd['ref_id'] = $app_history['ref_id'];
				$upd['active'] = $app_history['active'];
				$upd['flow_approvals'] = $app_history['flow_approvals'];
				$upd['approvals'] = $app_history['approvals'];
				$upd['notify_users'] = $app_history['notify_users'];
				$upd['status'] = $app_history['status'];
				$upd['branch_id'] = $branch_id;
				$upd['added'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("insert into branch_approval_history ".mysql_insert_by_field($upd)) or die(mysql_error());
				$new_aid = $con->sql_nextid();
				
				// branch_approval_history_items
				$upd = array();
				$upd['approval_history_id'] = $new_aid;
				$upd['user_id'] = $sessioninfo['id'];
				$upd['log'] = $history_log;
				$upd['timestamp'] = 'CURRENT_TIMESTAMP';
				$upd['status'] = 1;
				$upd['branch_id'] = $branch_id;
				$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
				
				$po['approval_history_id'] = $new_aid;
			}
			
			$fields_arr = array("branch_id", "po_branch_id", "user_id", "vendor_id", "department_id", "po_date", "added", "status", "approval_history_id", "delivery_date", "cancel_date", "partial_delivery", "sdiscount", "misc_cost", "remark", "transport_cost", "rdiscount", "remark2", "ddiscount", "hq_po_id",'allowed_user','is_ibt','is_under_gst');
			// Foreign Currency
			if($config['foreign_currency']){
				$fields_arr = array_merge($fields_arr, array('currency_code','currency_rate'));
			}
			$con->sql_query("insert into po " . mysql_insert_by_field($po, $fields_arr));
			
			$saved_po[$bid] = $con->sql_nextid();
			$ret[]=assign_po_no($saved_po[$bid], $branch_id);
			
			send_notification($saved_po[$bid], $branch_id, $po['vendor_id'], $po['department_id'], $po_head['allowed_user'][$bid]);
	
			// load the items and split
			$ritems = $con->sql_query("select * from po_items where po_id = $poid and branch_id = $branch_id order by id");
			$po_total[$bid] = 0;
			while ($item = $con->sql_fetchrow($ritems)){
				$item['qty_allocation'] = unserialize($item['qty_allocation']);
				$item['selling_price_allocation'] = unserialize($item['selling_price_allocation']);
				$item['qty_loose_allocation'] = unserialize($item['qty_loose_allocation']);
				$item['foc_allocation'] = unserialize($item['foc_allocation']);
				$item['foc_loose_allocation'] = unserialize($item['foc_loose_allocation']);
				$item['gst_selling_price_allocation'] = unserialize($item['gst_selling_price_allocation']);
	
				$item['qty'] = $item['qty_allocation'][$bid];
				$item['qty_loose'] = $item['qty_loose_allocation'][$bid];
				$item['foc'] = $item['foc_allocation'][$bid];
				$item['foc_loose'] = $item['foc_loose_allocation'][$bid];
				$item['po_id'] = $saved_po[$bid];
				$item['selling_price'] = $item['selling_price_allocation'][$bid];
				$item['disc_remark'] = $item['discount'];
				$item['stock_balance'] =  unserialize($item['stock_balance']);
				$item['stock_balance'] = serialize($item['stock_balance'][$bid]);
				$item['parent_stock_balance'] =  unserialize($item['parent_stock_balance']);
				$item['parent_stock_balance'] = serialize($item['parent_stock_balance']['qty']);
	
				if ($po_head['po_option']==1){
					$item['order_price'] = $item['resell_price'];
				}
	
				//calc weight and discount
				$total = @array_sum($item['qty_allocation'])*$item['order_uom_fraction']+@array_sum($item['qty_loose_allocation']);
				$this_total = $item['qty']*$item['order_uom_fraction']+$item['qty_loose'];
	
				$amt = $this_total/$item['order_uom_fraction']*$item['order_price'];
				$amt *= ($item['tax']+100)/100;
	
				if ($item['discount'] != ''){
					$amt = parse_formula($amt, $item['discount'], false, $this_total/$total, $z);
					$item['discount'] = sprintf("%.3f", -$z);
				}
				$po_total[$bid] += $amt;
				$big_amount += $amt;
				
				if($po_head['branch_is_under_gst']){
					$item['gst_selling_price'] = $item['gst_selling_price_allocation'][$bid];
				}
	
				$con->sql_query("insert into po_items " . mysql_insert_by_field($item, array("po_id", "branch_id", "user_id", "po_sheet_id", "sku_item_id", "qty", "selling_price", "is_foc", "foc_share_cost", "foc_noprint", "selling_uom_id", "order_uom_id", "order_price", "order_uom_fraction", "selling_uom_fraction", "tax", "discount", "disc_remark", "qty_loose", "remark", "remark2", "foc", "foc_loose", "artno_mcode", 'cost_indicate', 'stock_balance','parent_stock_balance','selling_gst_id','selling_gst_code','selling_gst_rate','cost_gst_id','cost_gst_code','cost_gst_rate','gst_selling_price')));
			}
		}
		if (!is_array($po_head['deliver_to'])) {
			send_notification($poid, $branch_id, $po_head['vendor_id'], $po_head['department_id'], $po_head['allowed_user'][$branch_id]);
		}
		
		foreach ($po_head['deliver_to'] as $dummy=>$bid){
			// calculate amount vs total amount
			$weight = $po_total[$bid]/$big_amount;
			$a = $po_total[$bid];
			$update = array();
			
			$a = parse_formula($a,$ptop['misc_cost'],true,$weight,$z);
			$update['misc_cost'] = serialize(array("0"=> sprintf("%.3f",$z)));
	
			$a = parse_formula($a,$ptop['sdiscount'],false,$weight,$z);
			$update['sdiscount'] = serialize(array("0"=> sprintf("%.3f",-$z)));
	
			$a = parse_formula($a,$ptop['rdiscount'],false,$weight,$z); // hidden discount
			$update['rdiscount'] = serialize(array("0"=> sprintf("%.3f",-$z)));
	
			$a = parse_formula($a,$ptop['ddiscount'],false,$weight,$z); // hidden discount (deduct cost)
			$update['ddiscount'] = serialize(array("0"=> sprintf("%.3f",-$z)));
	
			$a += $ptop['transport_cost']*$weight;
			$update['transport_cost'] = serialize(array("0"=> sprintf("%.3f",$ptop['transport_cost']*$weight)));
	
			$update['po_amount'] =  sprintf("%.2f",$a);
			//print_r($update);
			$con->sql_query("update po set " . mysql_update_by_field($update) . " where id=$saved_po[$bid] and branch_id = $branch_id");
			
			// update all amount
			//$appCore->poManager->reCalcatePOUsingOldMethod($branch_id, $saved_po[$bid]);
			$appCore->poManager->reCalcatePOAmt($branch_id, $saved_po[$bid]);
		}
		$po_active = 0;
	}
	else
	{
		assign_po_no($poid, $branch_id);
		$po_active = 1;
	}
	$con->sql_query("update po set approved=1, active=$po_active where id=$poid and branch_id = $branch_id");
	
	return join(" ", $ret);
}

function send_notification($po_id, $branch_id, $vendor_id, $dept_id, $allowed_user){
	global $con;
	
	$q1 = $con->sql_query("select description, contact_email from vendor where id=$vendor_id");
	$r1 = $con->sql_fetchrow($q1);
	$vendor=$r1['description'];
	$vendor_email=$r1['contact_email'];
	
	$q2 = $con->sql_query("select description from category where id=$dept_id");
	$r2 = $con->sql_fetchrow($q2);
	$dept=$r2['description'];
				
	if ($allowed_user)
	{
		foreach ($allowed_user as $k=>$v){
			send_pm($k, sprintf("PO (on behalf from HQ Approved) (HQ%05d, Dept:$dept, Vendor:$vendor)",$po_id), "/po.php?a=view&id=$po_id&branch_id=$branch_id");
		}
	}
}

function assign_po_no($poid, $branch_id){
	global $con;
	$con->sql_query("select report_prefix, ip from branch where id = $branch_id");
	$report_prefix = $con->sql_fetchrow();

    // check whether already have do_no
	$con->sql_query("select po_no from po where branch_id=$branch_id and id=$poid and po_no like ".ms($report_prefix[0].'%')) or die(mysql_error());
	$temp = $con->sql_fetchrow();
	$con->sql_freeresult();
	if($temp){
        $con->sql_query("update po set approved=1 where id=$poid and branch_id = $branch_id  and po_no like ".ms($report_prefix[0].'%')) or die(mysql_error());
        return $temp['po_no'];
	}   
	
	// get max length of po no
	$con->sql_query("select max(length(po_no)) as mx_lgth from po where branch_id = $branch_id and po_no like '$report_prefix[0]%'");
	$max_length = mi($con->sql_fetchfield(0));
	$con->sql_freeresult();
	
	// set approved flag and generate PO number
	if($max_length > 0) $filter = " and length(po_no) >= ".mi($max_length);
	$con->sql_query("select max(po_no) as mx from po where branch_id = $branch_id and po_no like '$report_prefix[0]%'".$filter);
	$r = $con->sql_fetchrow();
	$con->sql_freeresult();

	if (!$r){
		$n = 1;	
	}
	else{
		$n = preg_replace("/^".$report_prefix[0]."/", "", $r[0])+1;
	}

	$pono = $report_prefix[0] . sprintf("%05d", $n);
	while(!$con->sql_query("update po set po_no='$pono', approved=1 where id=$poid and branch_id = $branch_id",false,false)){
		$n++;
		$pono = $report_prefix[0] . sprintf("%05d", $n);
	}
	return $pono;
}

function get_stock_balance($sid)
{
	global $con, $sessioninfo;
	$form=$_REQUEST;
	$data=array();
	
	//branch filter
	if(BRANCH_CODE=='HQ' && is_array($form['deliver_to'])){
		$single=false;
		$bid = "branch_id in (".join(",", $form['deliver_to']).")";
	}
	else{
		$single=true;
		if($form['po_branch_id'])
			$bid=intval($form['po_branch_id']);
		else
			$bid=intval($sessioninfo['branch_id']);
		$bid = "branch_id = $bid";
	}
	
	//sku items filter
	$result = $con->sql_query("select * from sku_items where id=".mi($sid));
	$sid_data = $con->sql_fetchassoc($result);
	$con->sql_freeresult($result);
	
	$filter = "where sic.sku_item_id = ".mi($sid)." and $bid";
	$data=calculate_stock_balance("sum(sic.qty)",$filter,$single);
	
	if ($sid_data['is_parent']){
		$result = $con->sql_query("select * from sku_items where sku_id=".mi($sid_data['sku_id']));
		while ($r = $con->sql_fetchassoc($result)){
			$sid_list[$r['id']]=$r['id'];
		}
		$con->sql_freeresult($result);

		if (count($sid_list)>1){
			$filter = "where sic.sku_item_id in (".implode(",",$sid_list).") and $bid";
			$tmp_data = calculate_stock_balance("sum(sic.qty*uom.fraction)",$filter,$single);
			//use my style
			$data['parent_stock_balance']['qty']=$tmp_data['stock_balance'];
		}
	}
			
	return $data;
}

function calculate_stock_balance($subquery,$filter,$single){
	global $con, $sessioninfo;
	$data = array();
	
	$q1=$con->sql_query("select sic.branch_id,$subquery as qty 
					from sku_items_cost sic 
					left join sku_items si on si.id=sic.sku_item_id
					left join uom on uom.id=si.packing_uom_id
					$filter
					group by sic.branch_id");

	while($r=$con->sql_fetchassoc($q1)){	
		if (!$single){
			$data['stock_balance'][$r['branch_id']] += $r['qty'];
		}else{
			$data['stock_balance'] += $r['qty'];
		}
	}
	$con->sql_freeresult($q1);
	return $data;
}

function get_sales_trend($sid){
	global $con, $sessioninfo, $config;
	$sid_single[$sid]=$sid;	

	$result=$con->sql_query("select id from branch where active=1 order by sequence,code");
	$branches = $con->sql_fetchrowset($result);
	$con->sql_freeresult($result);
	
	//check if is parent
	$result=$con->sql_query("select * from sku_items where id=".mi($sid));
	$sid_data = $con->sql_fetchassoc($result);
	$con->sql_freeresult($result);
	
	if ($sid_data['is_parent']){
		$result=$con->sql_query("select * from sku_items where sku_id=".mi($sid_data['sku_id']));
		while ($r = $con->sql_fetchassoc($result)){
			$sid_list[$r['id']]=$r['id'];	
		}
		
		$con->sql_freeresult($result);
	}
	
	$data = calculate_sales_trend("sum(sc.qty)",$branches,$sid_single);
	
	if ($sid_data['is_parent'] && count($sid_list)>1){
		$tmp_data = calculate_sales_trend("sum(sc.qty*uom.fraction)",$branches,$sid_list);
		$data['sales_trend']['parent']['qty']=$tmp_data['sales_trend']['qty'];
		unset($tmp_data);
	}
	
	return $data;
}

function calculate_sales_trend($subquery,$branches,$sid_list){
	global $con, $sessioninfo, $config, $appCore;
	
	$form=$_REQUEST;
	$data=array();
	$dt=ms(date('Y-m-d', strtotime('-1 year')));
	
	//$bid_list = array();
	if(BRANCH_CODE=='HQ' && is_array($form['deliver_to'])){
		$total_branch=count($form['deliver_to']);
		//return when no branch selected
		if ($total_branch==0) return $data;
		$divide=1;
		if($total_branch==1&&$form['deliver_to'][0]==1&&!$config['po_disable_hq_get_all_branches_sales_trend']&&!$config['consignment_modules']){    // only one po to and it is HQ
			foreach($branches as $r) $bid_list[] = $r['id'];
		}else{
			$bid_list = $form['deliver_to'];
		}
	}
	else{
		if($form['po_branch_id'])
			$bid=intval($form['po_branch_id']);
		else
			$bid=intval($sessioninfo['branch_id']);

		if($bid==1&&!$config['po_disable_hq_get_all_branches_sales_trend']&&!$config['consignment_modules']){
			foreach($branches as $r) $bid_list[] = $r['id'];
		}else $bid_list[] = $bid;
	}	
		
	$prms = array();
	$prms['qty_sum_method'] = $subquery;
	//$data = get_sku_sales_trend($bid_list, $sid_list, $prms); // call it from include/functions.php
	$data['sales_trend'] = $appCore->skuManager->getSKUSalesTrend($bid_list, $sid_list, $prms); // call it from skuManager.php
	
	if ($data['sales_trend']) ksort($data['sales_trend']['qty']);
	//print_r($data);
	return $data;
}

function get_last_po_item($sku_item_id,$branch_id,$po_branch_id,$po_date,$form_id){
	global $con, $sessioninfo, $smarty, $gst_list, $config;

	if (BRANCH_CODE=='HQ' && $branch_id==1){	// PO is created by HQ and view under HQ
		$branch_chk=" ";
	}
	else{
		if($po_branch_id)
			$bid=intval($po_branch_id);
		else
			$bid=$branch_id;
		$branch_chk=" po_items.branch_id=$bid and ";	
	}	
	
	if ($po_date) {
		$po_date = strtotime("+1 day",strtotime($po_date));
		$po_date = date('Y-m-d',$po_date);
		if ($po_date) $po_date_sql = " and po.po_date < ".ms($po_date)." ";
	}
	
	if ($form_id) {
		$con->sql_query("select added from po where id = ".mi($form_id)." and branch_id = ".mi($branch_id)." limit 1") or die(mysql_error());
		$po_added_date = $con->sql_fetchfield(0);
		if ($po_added_date) $po_added_date_sql = " and po.added < ".ms($po_added_date)." ";
	}
	
	$q_last=$con->sql_query($abc="select po_items.*, uom.code as uom_code, u2.code as selling_uom_code, po.currency_code, po.currency_rate
from po_items
left join po on po_items.po_id = po.id and po_items.branch_id = po.branch_id
left join uom on po_items.order_uom_id = uom.id
left join uom u2 on po_items.selling_uom_id = u2.id
where po.active = 1 and po.approved = 1 and $branch_chk sku_item_id=$sku_item_id and (po_items.qty>0 or po_items.qty_loose>0 or po_items.foc>0 or po_items.foc_loose>0)
$po_date_sql $po_added_date_sql
order by po.po_date desc limit 1");//print "$abc<br /><br />";
	$r_last=$con->sql_fetchassoc($q_last);
	$con->sql_freeresult($q_last);
	
	// Get GST info
	if($r_last && $config['enable_gst']){
		$prms = array();
		$prms['branch_id'] = $branch_id;
		$prms['date'] = $po_date;
		$branch_is_under_gst = check_gst_status($prms);
		
		if($branch_is_under_gst) {
			// get inclusive tax info for selling price
			$r_last['inclusive_tax'] = get_sku_gst("inclusive_tax", $sku_item_id);
		}
	}
	
	
	return $r_last;
}

//allowed_user list in each selected branch
function get_allowed_user_list(){
	global $con, $smarty, $sessioninfo, $LANG;

	$form=$_REQUEST;
	$dept_id=mi($form['department_id']);

	if(BRANCH_CODE=='HQ' && (is_array($form['deliver_to']) || $form['po_branch_id'])){

		if(is_array($form['deliver_to'])){
			foreach($form['deliver_to'] as $k=>$v){
				$q1=$con->sql_query("select user_id,u from user_privilege
left join user on user_id=user.id
where privilege_code='PO_VIEW_ONLY' and branch_id=$v and user.departments like '%i:$dept_id;%' and user.active = 1 and user.is_arms_user=0");
				$temp = array();
				while ($r_u = $con->sql_fetchrow($q1)){
					$temp['user'][]=$r_u['u'];
					$temp['user_id'][]=$r_u['user_id'];
				}
				$user_list[$v]=$temp;
			}		
		}
		elseif($form['po_branch_id']){
			$bid=intval($form['po_branch_id']);
			$q1=$con->sql_query("select user_id,u from user_privilege
left join user on user_id=user.id
where privilege_code='PO_VIEW_ONLY' and branch_id=$bid and user.departments like '%i:$dept_id;%' and user.is_arms_user=0");
			$temp = array();
			while ($r_u = $con->sql_fetchrow($q1)){
				$temp['user'][]=$r_u['u'];
				$temp['user_id'][]=$r_u['user_id'];
			}
			$user_list[$bid]=$temp;
		}
	}
	else {
			$q1=$con->sql_query("select user_id,u from user_privilege
left join user on user_id=user.id
where privilege_code='PO_VIEW_ONLY' and branch_id=".mi($form['branch_id'])." and user.departments like '%i:$dept_id;%' and user.is_arms_user=0");
			$temp = array();
			while ($r_u = $con->sql_fetchrow($q1)){
				$temp['user'][]=$r_u['u'];
				$temp['user_id'][]=$r_u['user_id'];
			}
			$user_list[$form['branch_id']]=$temp;
	}
	$smarty->assign("user_list",$user_list);
}

$vendor_gst_list = array();
function init_selection(){
	global $con, $sessioninfo, $smarty, $depts, $config, $vendor_gst_list;
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
	$con->sql_query("select id, description from category where active and level = 2 and $depts order by description");
	$smarty->assign("dept", $con->sql_fetchrowset());
	
	// default action is create New PO
	$q1 = $con->sql_query("select id, code from branch where active=1 order by sequence,code");
	while($r = $con->sql_fetchassoc($q1)){
		$branch[] = $blist[$r['id']] = $r;
	}
	$con->sql_freeresult($q1);
	$smarty->assign("branch", $branch);
	$smarty->assign("branch_list", $blist);
	$con->sql_query("select id, code, fraction from uom where active order by code");
	$smarty->assign("uom", $con->sql_fetchrowset());
	
	// load custom message for email send to vendor
	$custom_email_msg = file_get_contents("custom_email_msg.txt");
	$smarty->assign("custom_email_msg", $custom_email_msg);
	
	if($config['enable_gst']){
		$q1 = $con->sql_query("select * from vendor where gst_register not in (0, -1)");
		
		while($r = $con->sql_fetchassoc($q1)){
			$vendor_gst_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
	}
}

function get_items_detail($sku_item_id, $branch_id){
	global $con, $smarty, $sessioninfo, $config, $input_gst_list, $output_gst_list, $vendor_gst_list;	
	$form=$_REQUEST;
		    
	$branch_chk="";	
	$filter = $grn_filter = $qc_filters = $grn_info = array();
  	
	if(BRANCH_CODE!='HQ'){ // create from sub branch - single branch delivery
		$branch_chk=" grn_items.branch_id=$branch_id and ";
		$grn_filter[] = "grn_items.branch_id=$branch_id"; 
		$qc_filters[] ="sivqch.branch_id = ".mi($branch_id);
	}
	elseif($form['po_branch_id'] && !is_array($form['deliver_to'])){ // single delivery branch 
		$bid=intval($form['po_branch_id']);
		$branch_chk=" grn_items.branch_id=$bid and ";
		$grn_filter[] = "grn_items.branch_id=$bid";
		$qc_filters[] ="sivqch.branch_id = ".mi($bid);
	}else{ // multi delivery branch
		if(is_array($form['deliver_to']) && count($form['deliver_to']) == 1) $qc_filters[] ="sivqch.branch_id = ".mi($form['deliver_to'][0]);
		else $qc_filters[] ="sivqch.branch_id = ".mi($branch_id);
	}
	
	// setup quotation cost query
	$qc_sql1 = "select sivqch.*
				from sku_items_vendor_quotation_cost_history sivqch
				join sku_items si on sivqch.sku_item_id=si.id
				where ";
	$qc_sql2 = " order by sivqch.added desc limit 1";
	
	$qc_filters[] = "sivqch.vendor_id = ".mi($form['vendor_id']);
	$qc_filters[] = "sivqch.sku_item_id = ".mi($sku_item_id);
	$qc_filters[] = "sivqch.added <= ".ms($form['po_date']." 23:59:59");
	
	// the quotation cost selection as below:
	// - PO create from HQ with multi delivery branches = will always get the quotation cost from HQ
	// - PO create from HQ with single delivery branch  = will get the quotation cost from delivery branch first, then only get it from HQ if couldn't found
	// - PO create from sub branch                      = will get the quotation cost from PO creation branch
	$q1 = $con->sql_query($qc_sql1.join(" and ", $qc_filters).$qc_sql2);
	$qc_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	// need to select again using HQ if couldn't find the data for the PO create from HQ with single delivery branch
	// have to check and gain below access if user trying to add single delivery branch from multiple branch selection
	if(BRANCH_CODE == 'HQ' && !$qc_info['cost'] && (($form['po_branch_id'] && !is_array($form['deliver_to'])) || (is_array($form['deliver_to']) && count($form['deliver_to']) == 1))){
		$qc_filters = array();
		$qc_filters[] ="sivqch.branch_id = ".mi($branch_id);
		$qc_filters[] ="sivqch.vendor_id = ".mi($form['vendor_id']);
		$qc_filters[] ="sivqch.sku_item_id = ".mi($sku_item_id);
		$qc_filters[] ="sivqch.added <= ".ms($form['po_date']." 23:59:59");
		
		$q1 = $con->sql_query($qc_sql1.join(" and ", $qc_filters).$qc_sql2);
		$qc_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
	}
	
	$grn_filter[] = "grn.approved=1 and grn.active=1 and grr.active=1";
	$grn_filter[] = "sku_item_id=$sku_item_id";
	$grn_filter[] = "grr_items.type<>'DO'";
	/*if(!$config['grn_do_transfer_update_cost']){  // don't count DO
	$grn_filter[] = "grr_items.type<>'DO'";
	}else{  // only count transfer DO
	$grn_filter[] = "(grr_items.type<>'DO' or (grr_items.type='DO' and do_type='transfer'))";
	}*/
	$grn_filter = join(' and ', $grn_filter);
  
	//get from grn
	$sql = "select si.*, grn.vendor_id as vendor_id, if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost) as order_price, grn_items.uom_id as uom_id, si.packing_uom_id as selling_uom_id, uom.fraction as order_uom_fraction, u1.fraction as selling_uom_fraction,si.packing_uom_id as master_uom_id,u1.code as packing_uom, grr_items.type as grr_item_type,do.do_type,sai.photo_count, u1.fraction as master_uom_fraction, sku.category_id, grn_items.gst_id as cost_gst_id, grn_items.gst_rate as cost_gst_rate, grn_items.gst_code as cost_gst_code, grn.branch_id as grn_bid, grn.vendor_id as grn_vd_id
	from grn_items
	left join sku_items si on sku_item_id = si.id
	left join sku on sku.id = si.sku_id
	left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
	left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
	left join uom u1 on si.packing_uom_id = u1.id
	left join uom on grn_items.uom_id = uom.id
	left join grr_items on grr_items.id=grn.grr_item_id and grr_items.branch_id=grn.branch_id
	left join do on do.do_no=grr_items.doc_no
	left join sku_apply_items sai on sai.id=si.sku_apply_items_id
	where $grn_filter
	having order_price>0
	order by grr.rcv_date desc, grr.id desc limit 1";

	$result=$con->sql_query($sql);
	$selection="GRN";
	
	// if found grn item, get quotation cost base on grn data
	if($con->sql_numrows($result)==0){
		if (BRANCH_CODE!='HQ'){
			$branch_chk = " po_items.branch_id=$branch_id and ";
		}
		elseif ($form['po_branch_id'] && !is_array($form['deliver_to'])){
			$bid=intval($form['po_branch_id']);
			$branch_chk = " po_items.branch_id=$bid and ";
		}
		
		//get from po
		$result=$con->sql_query("select si.*, po.vendor_id as vendor_id, po_items.order_price as order_price, po_items.order_uom_id as uom_id, si.packing_uom_id as selling_uom_id, uom.fraction as order_uom_fraction, u1.fraction as selling_uom_fraction,si.packing_uom_id as master_uom_id,u1.code as packing_uom, sai.photo_count, u1.fraction as master_uom_fraction, sku.category_id, po_items.selling_gst_id, po_items.selling_gst_rate, 
		po_items.selling_gst_code, po_items.cost_gst_id, po_items.cost_gst_rate, po_items.cost_gst_code	
		from po_items
		left join sku_items si on sku_item_id = si.id
		left join sku on sku.id = si.sku_id
		left join po on po_id = po.id and po.branch_id = po_items.branch_id
		left join uom u1 on si.packing_uom_id = u1.id
		left join uom on order_uom_id = uom.id
		left join sku_apply_items sai on sai.id=si.sku_apply_items_id
		where po.active=1 and po.approved=1 and $branch_chk sku_item_id=$sku_item_id 
		having order_price>0 
		order by po.po_date desc, po.id desc limit 1");

		//po_items.selling_price as selling_price, 
		$selection="PO";
	}
	
	if($con->sql_numrows($result)==0){
	    //get from master
		$result=$con->sql_query("select sku.vendor_id, si.*, si.cost_price as order_price, si.cost_price as resell_price, u1.fraction as selling_uom_fraction, si.packing_uom_id as selling_uom_id ,si.packing_uom_id as master_uom_id,u1.code as packing_uom, sai.photo_count, u1.fraction as master_uom_fraction, sku.category_id
		from sku_items si
		left join sku on si.sku_id = sku.id 
		left join uom u1 on si.packing_uom_id = u1.id
		left join sku_apply_items sai on sai.id=si.sku_apply_items_id
		where si.id = $sku_item_id");
		$selection="SKU";
	}
	$arr = array();
	$r = $con->sql_fetchassoc($result);
	$con->sql_freeresult($result);
	
	// check if have quotation cost, need to use it
	if($qc_info['cost']>0){
		$r['use_qc'] = 1;
		$r['qc_is_higher'] = 0;
		// need to show errors if quotation cost is higher than order price
		if($qc_info['cost'] > $r['order_price']) $r['qc_is_higher'] = 1;
		$r['order_price'] = $qc_info['cost'];
		$selection = "QUOTATION";
	}
	
	unset($qc_sql1, $qc_sql2, $qc_info, $qc_filters);
	
    $prms = array();
    $prms['branch_id'] = $branch_id;
    $prms['date'] = $form['po_date'];
    $branch_is_under_gst = check_gst_status($prms);
    
    if($branch_is_under_gst) {
		if(!$output_gst_list) $output_gst_list = construct_gst_list('supply');
	
		// get inclusive tax info for selling price
		$r['inclusive_tax'] = get_sku_gst("inclusive_tax", $sku_item_id);
	
		if(!$r['selling_gst_id'] || !$config['enable_get_last_gst_info']){ // check to get selling GST info
			$output_gst = get_sku_gst("output_tax", $sku_item_id);
			if($output_gst){
				$r['selling_gst_id'] = $output_gst['id'];
				$r['selling_gst_code'] = $output_gst['code'];
				$r['selling_gst_rate'] = $output_gst['rate'];
			}else{
				$r['selling_gst_id'] = $output_gst_list[0]['id'];
				$r['selling_gst_code'] = $output_gst_list[0]['code'];
				$r['selling_gst_rate'] = $output_gst_list[0]['rate'];
			}
		}
    }
        
	if($form['is_under_gst']){
		// if found got set special vendor gst code, then all items must default choose it
		if(!$input_gst_list) $input_gst_list = construct_gst_list('purchase');
			
		// if found got set special vendor gst code, then all items must default choose it
		if($vendor_gst_list[$form['vendor_id']]['gst_register'] > 0){
			$vd_gst = $vendor_gst_list[$form['vendor_id']]['gst_register'];
			foreach($input_gst_list as $tmp_gst_info){
				if($tmp_gst_info['id'] == $vd_gst){
					$r['cost_gst_id'] = $tmp_gst_info['id'];
					$r['cost_gst_code'] = $tmp_gst_info['code'];
					$r['cost_gst_rate'] = $tmp_gst_info['rate'];
					break;
				}
			}
		}else if(!$r['cost_gst_id'] || !$config['enable_get_last_gst_info']){ // check to get cost GST info
            $input_gst = get_sku_gst("input_tax", $sku_item_id);
            if($input_gst){
                $r['cost_gst_id'] = $input_gst['id'];
                $r['cost_gst_code'] = $input_gst['code'];
                $r['cost_gst_rate'] = $input_gst['rate'];
            }else{
                $r['cost_gst_id'] = $input_gst_list[0]['id'];
                $r['cost_gst_code'] = $input_gst_list[0]['code'];
                $r['cost_gst_rate'] = $input_gst_list[0]['rate'];
            }
        }
	}
	
	//get selling price from sku_item_price, if have used it to replace
	if (BRANCH_CODE=='HQ' && is_array($form['deliver_to'])){		
		foreach($form['deliver_to'] as $k=>$v){
			$r1=get_selling_price($sku_item_id,$v);
			if($r1['price_sip']){
				$r['selling_price_allocation'][$v]=$r1['price_sip'];
			}
			else{
				$r['selling_price_allocation'][$v]=$r1['price_si'];
			}
			
			if($branch_is_under_gst){
				$prms = array();
				$prms['selling_price'] = $r['selling_price_allocation'][$v];
				$prms['inclusive_tax'] = $r['inclusive_tax'];
				$prms['gst_rate'] = $r['selling_gst_rate'];
				$gst_sp_info = calculate_gst_sp($prms);
				
				if($r['inclusive_tax'] == "yes"){
					$r['gst_selling_price_allocation'][$v] = $r['selling_price_allocation'][$v];
					$r['selling_price_allocation'][$v] = $gst_sp_info['gst_selling_price'];
				}else{
					$r['gst_selling_price_allocation'][$v] = $gst_sp_info['gst_selling_price'];
				}
			}
		}
	}
	else{
		if($form['po_branch_id']){
			$bid=intval($form['po_branch_id']);
		}
		else{
			$bid=$branch_id;
		}
		$r1=get_selling_price($sku_item_id,$bid);
		if($r1['price_sip']){
			$r['selling_price']=$r1['price_sip'];
		}
		else{
			$r['selling_price']=$r1['price_si'];
		}
		
		if($branch_is_under_gst){
			$prms = array();
			$prms['selling_price'] = $r['selling_price'];
			$prms['inclusive_tax'] = $r['inclusive_tax'];
			$prms['gst_rate'] = $r['selling_gst_rate'];
			$gst_sp_info = calculate_gst_sp($prms);
			$r['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
			//$r['gst_amt'] = $gst_sp_info['gst_amt'];
			
			if($r['inclusive_tax'] == "yes"){
				$r['gst_selling_price'] = $r['selling_price'];
				$r['selling_price'] = $gst_sp_info['gst_selling_price'];
			}else{
				$r['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
			}
		}
	}
	$r['cost_indicate']=$selection;
	
	if($config['po_show_photo']){
		$r['photo'] = po_get_sku_item_photo($sku_item_id, $r['sku_apply_items_id'], $r, $sku_use_photo_list);
	}
	return $r;
}

function get_selling_price($sid, $bid){
	global $con;
	
	$q1=$con->sql_query("select sip.price as price_sip, si.selling_price as price_si  
from sku_items si
left join sku_items_price sip on sip.sku_item_id=si.id and branch_id=$bid
where si.id=$sid");
    $r1 = $con->sql_fetchassoc($q1);
    $con->sql_freeresult($q1);
	return $r1;
}

function do_reset($po_id,$branch_id){
	global $con,$sessioninfo,$config, $appCore, $smarty, $LANG;
	$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;

	if($sessioninfo['level']<$required_level){
        js_redirect(sprintf('Forbidden', 'PO', BRANCH_CODE), "/po.php");
	}

	$form=load_po_header($po_id, $branch_id);

	$aid=$form['approval_history_id'];
	
	/*if($config['monthly_closing']){
		$is_month_closed = $appCore->is_month_closed($form['po_date']);
		if($is_month_closed)  js_redirect($LANG['MONTH_DOCUMENT_IS_CLOSED'], "/po.php");
	}*/
		
	// check duplicate approval history id
	$con->sql_query("select count(*) from po where approval_history_id=$aid") or die(mysql_error());
	if($con->sql_fetchfield(0)>1){
		$con->sql_query("select * from branch_approval_history where id=$aid and branch_id=$branch_id") or die(mysql_error());
		$app_history = $con->sql_fetchassoc();
		// use new aid
		// own approval history
		// branch_approval_history
		$upd = array();
		$upd['approval_flow_id'] = $app_history['approval_flow_id'];
		$upd['ref_table'] = $app_history['ref_table'];
		$upd['ref_id'] = $app_history['ref_id'];
		$upd['active'] = $app_history['active'];
		$upd['flow_approvals'] = $app_history['flow_approvals'];
		$upd['approvals'] = $app_history['approvals'];
		$upd['notify_users'] = $app_history['notify_users'];
		$upd['status'] = $app_history['status'];
		$upd['branch_id'] = $branch_id;
		$upd['added'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into branch_approval_history ".mysql_insert_by_field($upd)) or die(mysql_error());
		$new_aid = $con->sql_nextid();
		
		// branch_approval_history_items
		$upd = array();
		$upd['approval_history_id'] = $new_aid;
		$upd['user_id'] = $sessioninfo['id'];
		$upd['log'] = "Approval History clear by system";
		$upd['timestamp'] = 'CURRENT_TIMESTAMP';
		$upd['status'] = 1;
		$upd['branch_id'] = $branch_id;
		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
		
		$con->sql_query("update po set approval_history_id=$new_aid where id=$po_id and branch_id=$branch_id") or die(mysql_error());
		$aid = $new_aid;
	}
	
	$approvals=$form['approvals'];
	$status = 0;

	$upd = array();
	$upd['approval_history_id'] = $aid;
	$upd['branch_id'] = $branch_id;
	$upd['user_id'] = $sessioninfo['id'];
	$upd['status'] = $status;
	$upd['log'] = $_REQUEST['reason'];

	$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
	$con->sql_query("update branch_approval_history set status=$status where id = $aid and branch_id = $branch_id") or die(mysql_error());

	$upd = array();
	$upd['status'] = $status;
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	$upd['approved'] = 0;
	$upd['active'] = 1;
	if($config['foreign_currency'] && $form['currency_code']){
		$upd['can_change_currency_rate'] = 1;
	}
	
	$con->sql_query("update po set ".mysql_update_by_field($upd)." where id=$po_id and branch_id=$branch_id") or die(mysql_error());
    log_br($sessioninfo['id'], 'PURCHASE ORDER', $po_id, sprintf("PO Reset ($form[po_no])",$po_id));
    
	header("Location: /po.php?t=reset&save_id=$po_id");
}

function check_must_can_edit($branch_id, $po_id, $is_approval_screen = false){
	global $con, $LANG;

    $con->sql_query("select active, status, approved from po where branch_id=".mi($branch_id)." and id=".mi($po_id));

	if($r = $con->sql_fetchrow()){  // invoice exists
		if(!$r['active']){  // inactive
            display_redir($_SERVER['PHP_SELF'], "Purchase Order", sprintf($LANG['PO_INACTIVE'], $po_id));
		}elseif ($r['status']==4 || $r['status']==5){    // canceled or deleted
		    display_redir($_SERVER['PHP_SELF'], "Purchase Order", sprintf($LANG['PO_ALREADY_CANCELED_OR_DELETED'], $po_id));
		}else{
		    if($is_approval_screen){
                if($r['approved']){    // already approved
                    display_redir($_SERVER['PHP_SELF'], "Purchase Order", sprintf($LANG['PO_ALREADY_CONFIRM_OR_APPROVED'], $po_id));
                }
			}elseif(($r['status']>0 && $r['status'] !=2) || $r['approved']){    // confimred or approved
			    display_redir($_SERVER['PHP_SELF'], "Purchase Order", sprintf($LANG['PO_ALREADY_CONFIRM_OR_APPROVED'], $po_id));
			}
		}
	}else{
        display_redir($_SERVER['PHP_SELF'], "Purchase Order", sprintf($LANG['PO_NOT_FOUND'], $po_id)); // invoice not found
	}
	$con->sql_freeresult();
}

function po_get_sku_item_photo($sid, $sku_apply_items_id, $r, &$sku_use_photo_list){
	global $config, $con;
	
	$use_photo = '';
	if($config['po_show_photo']){
		// check apply photo
		if(!isset($sku_use_photo_list[$sid])){
			// get apply photo
			if($photos = get_sku_apply_item_photos($sku_apply_items_id)){
				foreach($photos as $imgname){
					if(check_image_exists($imgname)){
						$use_photo = $imgname;
						break;
					}
				}
			}
			
			if(!$use_photo){
				// get actual photo
				if($photos = get_sku_item_photos($sid, $r)){
					foreach($photos as $imgname){
						if(check_image_exists($imgname)){
							$use_photo = $imgname;
							break;
						}
					}
				}
			}
			$sku_use_photo_list[$sid] = $use_photo;
		}
	
		// assign photo path
		$use_photo = $sku_use_photo_list[$sid];
	}
	return $use_photo;
}

function find_delivered_grn($bid, $po_id, $po_no=''){
	global $con, $config;
	
	$bid = mi($bid);
	$po_id = mi($po_id);
	
	if(!$bid || !$po_id)	return array();
	
	if(!$po_no){
		$con->sql_query("select po_no from po where branch_id=$bid and id=$po_id limit 1");
		$po_no = $con->sql_fetchfield(0);
		$con->sql_freeresult();
	}
	
	if(!$po_no)	return array();
	
	if(!$config['use_grn_future']) $extra_filter = "and grn.grr_item_id = gri.id";
	
	$sql = "select grn.branch_id,grn.id as grn_id,branch.report_prefix as report_prefix
from grr_items gri
left join branch on gri.branch_id=branch.id
join grr on grr.branch_id=gri.branch_id and grr.id=gri.grr_id
join grn on grn.branch_id=grr.branch_id and grn.grr_id=grr.id $extra_filter
where gri.type='PO' and gri.doc_no=".ms($po_no)." and grn.active=1 and grr.active=1
order by grn.branch_id,grn.id";
	$con->sql_query($sql);
	$delivered_grn_list = array();
	while($r = $con->sql_fetchassoc()){
		$delivered_grn_list[] = $r;
	}
	$con->sql_freeresult();
	
	return $delivered_grn_list;
}

function load_purchase_agreement_header($branch_id, $id, $redirect_if_not_found = false){
	global $con, $sessioninfo, $smarty, $LANG;

	$con->sql_query("select pa.*, bah.approvals, vendor.description as vendor_desc
					from purchase_agreement pa
					left join branch_approval_history bah on bah.id=pa.approval_history_id and bah.branch_id=pa.branch_id
					left join vendor on vendor.id=pa.vendor_id
					where pa.id = ".mi($id)." and pa.branch_id = ".mi($branch_id));
	$form = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$form){
		if($redirect_if_not_found){
            display_redir("/po.po_agreement.setup.php", "Purchase Agreement", sprintf($LANG['PURCHASE_AGREEMENT_NOT_FOUND'], $id));
		}
		else return false;
	}

	if ($sessioninfo['level']>=9999)	// superuser approve and final
	{
		$form['is_approval'] = 1;
		$form['last_approver'] = 1;
	}
	else
	{
		if (preg_match("/\|$sessioninfo[id]\|/", $form['approvals']))
			$form['is_approval'] = 1;
		if (preg_match("/\|\d+\|$/", $form['approvals']))
			$form['last_approver'] = 1;
	}

	if ($form['approval_history_id']>0){
		$q2 = $con->sql_query("select i.timestamp, i.log, i.status, user.u
from branch_approval_history_items i
left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
left join user on i.user_id = user.id
where h.ref_table='purchase_agreement' and i.branch_id=$branch_id and i.approval_history_id=$form[approval_history_id]
order by i.timestamp");
		$approval_history = array();
		while($r = $con->sql_fetchassoc($q2)){
			$r['more_info'] = unserialize($r['more_info']);
			$approval_history[] = $r;
		}
		$con->sql_freeresult($q2);
		$smarty->assign("approval_history", $approval_history);
	}
	
	$form['label'] = 'draft';
	if($form['status']==1 && !$form['approved'])   $form['label'] = 'waiting_approve';
	elseif($form['status']==1 && $form['approved'])    $form['label'] = 'approved';
	elseif($form['status']==2)  $form['label'] = 'rejected';
	elseif($form['status']==4)  $form['label'] = 'cancelled_terminated';

	// load data such as sku type, price type, etc...
	load_pa_required_data();
		
	return $form;
}

function load_purhcase_agreement_items_list($branch_id, $id, $load_from_tmp = false){
	global $con,$smarty, $LANG, $sessioninfo, $config;

	$items = $foc_items = array();
    $filter = array();
    $tbl_items = "purchase_agreement_items";
    $tbl_foc_items = "purchase_agreement_foc_items";
    
    if($load_from_tmp){
		$tbl_items = 'tmp_purchase_agreement_items';
		$tbl_foc_items = "tmp_purchase_agreement_foc_items";
		$filter[] = "pai.user_id=$sessioninfo[id]";
	}

	$filter[] = "pai.branch_id=".mi($branch_id)." and pai.purchase_agreement_id=".mi($id);
	
	$filter = "where ".join(' and ', $filter);

	$sql = "select pai.*, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description, b.code as branch_code
	from $tbl_items pai 
	left join sku_items si on si.id=pai.sku_item_id
	left join branch b on b.id = pai.branch_id
	$filter order by pai.id";
	//print $sql;
	
	$item_list = array();
	
	// items
	$q1 = $con->sql_query($sql);
	while($r = $con->sql_fetchassoc($q1)){
		$r['allowed_branches'] = unserialize($r['allowed_branches']);
		// find overlap
		//$r['overlap_items'] = get_mix_n_match_overlap($promo, $r);
		$item_list['item'][] = $r;
	}
	$con->sql_freeresult($q1);
	
	// foc items
	$q2 = $con->sql_query("select pai.*, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description, b.code as branch_code
	from $tbl_foc_items pai 
	left join sku_items si on si.id=pai.sku_item_id
	left join branch b on b.id = pai.branch_id
	$filter order by pai.id");
	while($r = $con->sql_fetchassoc($q2)){
		$r['allowed_branches'] = unserialize($r['allowed_branches']);
		$r['ref_rule_num'] = unserialize($r['ref_rule_num']);

		
		// find overlap
		//$r['overlap_items'] = get_mix_n_match_overlap($promo, $r);
		$item_list['foc_item'][] = $r;
	}
	$con->sql_freeresult($q2);
	
	return $item_list;
}

function load_pa_required_data(){
	global $con, $sessioninfo, $smarty;
	
	// manager and above can see all department
	if ($sessioninfo['level'] < 9999){
		if (!$sessioninfo['departments'])
			$dept_filter = "id in (0)";
		else
			$dept_filter = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
	}
	else{
		$dept_filter = 1;
	}
	// show department option
	$con->sql_query("select id, description from category where active=1 and level = 2 and $dept_filter order by description");
	$dept_list = array();
	while($r = $con->sql_fetchassoc()){
		$dept_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	$smarty->assign("dept_list", $dept_list);
	
	// load branches list
	$branches_list = array();
	$con->sql_query("select id, code from branch where active=1 order by sequence,code");
	while($r = $con->sql_fetchassoc()){
		$branches_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	$smarty->assign('branches_list', $branches_list);
}

function load_purchase_agreement_item($branch_id, $pai_id, $load_from_tmp = false, $show_tpl = false){
    global $con,$smarty, $LANG, $sessioninfo;
    
    // escape integer
    $branch_id = mi($branch_id);
    $pai_id = mi($pai_id);
    
    $filter = array();
    if($load_from_tmp){
		$tbl = 'tmp_purchase_agreement_items';
		$filter[] = "pai.user_id=$sessioninfo[id]";
	}else   $tbl = 'purchase_agreement_items';
	
	$filter[] = "pai.branch_id=".mi($branch_id)." and pai.id=".mi($pai_id);
	$filter = "where ".join(' and ', $filter);
	
	// load item
	$con->sql_query("select pai.*, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description 
	from $tbl pai 
	left join sku_items si on si.id=pai.sku_item_id
	$filter");
	$pa_item = $con->sql_fetchassoc();
    $con->sql_freeresult();
    
	if($pa_item){
	    $pa_item['allowed_branches'] = unserialize($pa_item['allowed_branches']);
	    
	    // find overlap
		//$promo_item['overlap_pi'] = get_mix_n_match_overlap($promo, $promo_item);
		//print_r($promo_item['overlap_pi']);
	}
	if($show_tpl){
		load_pa_required_data();
		
		$smarty->assign('pa_item', $pa_item);
		return $smarty->fetch('po.po_agreement.setup.open.item_list.row.tpl');
	}
	return $pa_item;
}

function load_purchase_agreement_foc_item($branch_id, $foc_pai_id, $load_from_tmp = false, $show_tpl = false){
	global $con,$smarty, $LANG, $sessioninfo;
    
    // escape integer
    $branch_id = mi($branch_id);
    $foc_pai_id = mi($foc_pai_id);
    
    $filter = array();
    if($load_from_tmp){
		$tbl = 'tmp_purchase_agreement_foc_items';
		$filter[] = "pafi.user_id=$sessioninfo[id]";
	}else   $tbl = 'purchase_agreement_foc_items';
	
	$filter[] = "pafi.branch_id=".mi($branch_id)." and pafi.id=".mi($foc_pai_id);
	$filter = "where ".join(' and ', $filter);
	
	// load item
	$con->sql_query("select pafi.*, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description 
	from $tbl pafi 
	left join sku_items si on si.id=pafi.sku_item_id
	$filter");
	$foc_pa_item = $con->sql_fetchassoc();
    $con->sql_freeresult();
    
	if($foc_pa_item){
	    $foc_pa_item['allowed_branches'] = unserialize($foc_pa_item['allowed_branches']);
	    $foc_pa_item['ref_rule_num'] = unserialize($foc_pa_item['ref_rule_num']);
	    
	    // find overlap
		//$promo_item['overlap_pi'] = get_mix_n_match_overlap($promo, $promo_item);
		//print_r($promo_item['overlap_pi']);
	}
	if($show_tpl){
		load_pa_required_data();
		
		$smarty->assign('foc_pa_item', $foc_pa_item);
		return $smarty->fetch('po.po_agreement.setup.open.foc_item_list.row.tpl');
	}
	return $pa_item;
}

// load current branch category_sales_trend and group by recent month
function load_category_sales_trend($category_id, $branch_id=array()){
	global $con;
	
	if($category_id && $branch_id){
		foreach($branch_id as $key=>$bid){
			$q3 = $con->sql_query("select * from category_sales_trend_cache where category_id=".mi($category_id)." and branch_id=".mi($bid));
			while($r3 = $con->sql_fetchassoc($q3)){
				foreach(array(1,3,6,12) as $mm){
					if($r3['recent_month'] && $mm >= $r3['recent_month']){
						$form['qty'][$mm]+=$r3['qty'];
					}
				}
			}
			$con->sql_freeresult($q3);
		}
	}
	return $form;
}
?>
