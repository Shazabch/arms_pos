<?php
error_reporting(0);
/*revision history
-------------------
**gary 6/09/2007
  - add sell/cost price from gsn>po>master (useless/remove)

gary 6/18/2007 1:53:11 PM
- added uom_fraction and uom from GRN>PO>master

6/27/2007 5:54:24 PM -gary
-added allowed_user list for each selected branch

7/23/2007 4:07:38 PM 
-avoid empty artno/mcode (line 277)

7/24/2007 11:04:46 AM yinsee
- add_temp_item() revert back to use artno if exists (remove the check for $r['vendor_id'] == $_REQUEST['vendor_id'])

8/20/2007 5:17:37 PM gary
- add sql query to get vendor n dept info for send_pm.

10/17/2007 3:30:42 PM yinsee
- select vendor.term, vendor.prompt_payment_term, vendor.prompt_payment_discount in load_po

10/23/2007 4:47:15 PM gary
-add cost indicate column when insert or update po_items.

11/30/2007 3:06:11 PM gary
- list out related sku.

12/10/2007 9:45:34 AM gary
- add hq po distribution list when load_po.

11/28/2007 3:54:56 PM gary
- remove the sku_items_cost query.

12/12/2007 2:26:40 PM gary
- add "having cost > 0" in get_cost (ignore grn with zero cost)
*/
include("include/class.phpmailer.php");

// default action is create New PO
$con->sql_query("select id, code from branch where active order by code");
$smarty->assign("branch", $con->sql_fetchrowset());
$con->sql_query("select id, code, fraction from uom where active order by code");
$smarty->assign("uom", $con->sql_fetchrowset());

// manager and above can see all department
if ($sessioninfo['level'] < 9999)
{
	if (!$sessioninfo['departments'])
		$depts = "id in (0)";
	else
		$depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
}
else
{
	$depts = 1;
}

// show department option
$con->sql_query("select id, description from category where active and level = 2 and $depts order by description");
$smarty->assign("dept", $con->sql_fetchrowset());

function get_items_detail($sku_item_id,$branch_id){
	global $con, $smarty, $sessioninfo, $r;
    /*
    //get from sku_item_price
	$result=$con->sql_query("select sku_items.*, sku_items_price.price as selling_price, 
if (sku_items_price.cost is null,sku_items.cost_price,sku_items_price.cost) as cost_price
from sku_items_price
left join sku_items on sku_items.id =sku_items_price.sku_item_id
where sku_items_price.branch_id = $branch_id and sku_items_price.sku_item_id = $sku_item_id");
	*/	
	//get from grn				    
	$branch_chk = "";
	if (BRANCH_CODE!='HQ'){
		$branch_chk = "grn_items.branch_id =$branch_id and ";
	}
	$result=$con->sql_query("select sku_items.*, grn.vendor_id as vendor_id, if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost) as order_price, grn_items.uom_id as uom_id, sku_items.packing_uom_id as selling_uom_id, uom.fraction as order_uom_fraction, u1.fraction as selling_uom_fraction
from grn_items
left join sku_items on sku_item_id = sku_items.id
left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
left join uom u1 on sku_items.packing_uom_id = u1.id
left join uom on uom_id = uom.id
where $branch_chk grn.approved and sku_item_id=$sku_item_id 
having order_price>0
order by grr.rcv_date desc, grr.id desc limit 1");
// grn_items.selling_price as selling_price,
$selection="GRN";
	
	if($con->sql_numrows()==0){
	    //get from po
	    if (BRANCH_CODE!='HQ'){
			$branch_chk = "po_items.branch_id =$branch_id and ";
		}
		$result=$con->sql_query("select sku_items.*, po.vendor_id as vendor_id, po_items.order_price as order_price, po_items.order_uom_id as uom_id, sku_items.packing_uom_id as selling_uom_id, uom.fraction as order_uom_fraction, u1.fraction as selling_uom_fraction
from po_items
left join sku_items on sku_item_id = sku_items.id
left join po on po_id = po.id and po.branch_id = po.branch_id
left join uom u1 on sku_items.packing_uom_id = u1.id
left join uom on order_uom_id = uom.id
where po.active and po.approved and $branch_chk sku_item_id=$sku_item_id 
having order_price>0 
order by po.po_date desc, po.id desc limit 1");
//po_items.selling_price as selling_price, 
		$selection="PO";
	}
	
	if($con->sql_numrows()==0){
	    //get from master
		$result=$con->sql_query("select sku.vendor_id, sku_items.*, cost_price as order_price, cost_price as resell_price, u1.fraction as selling_uom_fraction, sku_items.packing_uom_id as selling_uom_id 
from sku_items 
left join sku on sku_id = sku.id 
left join uom u1 on sku_items.packing_uom_id = u1.id
where sku_items.id = $sku_item_id");
		$selection="SKU";
	}
	$arr = array();
    $r = $con->sql_fetchrow($result);

	//get selling price from sku_item_price, if have used it to replace
	if (is_array($_REQUEST['deliver_to'])){		
		foreach($_REQUEST['deliver_to'] as $k=>$v){
			$r1=get_selling_price($sku_item_id,$v);
			if($r1['price_sip']){
				$r['selling_price_allocation'][$v]=$r1['price_sip'];
			}
			else{
				$r['selling_price_allocation'][$v]=$r1['price_si'];
			}		
		}

	}
	else{
		$r1=get_selling_price($sku_item_id,$branch_id);
		if($r1['price_sip']){
			$r['selling_price']=$r1['price_sip'];
		}
		else{
			$r['selling_price']=$r1['price_si'];
		}	
	}
	$r['cost_indicate']=$selection;
	//echo"<pre>";print_r($r);echo"</pre>";  
	return $r;
}

function get_selling_price($sid,$bid){
	global $con;
	
	$q1=$con->sql_query("select sip.price as price_sip, si.selling_price as price_si  
from sku_items si
left join sku_items_price sip on sip.sku_item_id=si.id and branch_id=$bid
where si.id=$sid");
    $r1 = $con->sql_fetchrow($q1);
	return $r1;
}


function restore_po_request_item($po_id,$po_branch_id){
	global $con;
	
	$con->sql_query("select * from po_items where po_id=".mi($po_id)." and branch_id = ".mi($po_branch_id));

	while($r = $con->sql_fetchrow()){
		$con->sql_query("update po_request_items set active=1,added=added where po_item_id=$r[id] and branch_id=".mi($po_branch_id));
	}

}


function load_po($poid = -1, $load = true, $check_owner = true){

	global $con, $smarty, $sessioninfo, $LANG, $branch_id;

	$form = $_REQUEST;
	if ($poid == -1) $poid = intval($_REQUEST['id']);
	if ($poid > 0 && $load){
	    // load additional informations if print
		$con->sql_query("select po.*, user.u as user, user2.u as cancel_user, vendor.description as vendor, vendor.term, vendor.prompt_payment_term, vendor.prompt_payment_discount, branch.code as branch, branch2.code as po_branch, branch.report_prefix 
from po 
left join user on user_id = user.id 
left join user user2 on cancel_by = user2.id 
left join vendor on vendor_id = vendor.id 
left join branch on branch_id = branch.id 
left join branch branch2 on po_branch_id = branch2.id 
where po.id = $poid and po.branch_id = $branch_id");
		$form = $con->sql_fetchrow();
		// retrieve
		if (!$form){
		    $smarty->assign("url", "/purchase_order.php");
		    $smarty->assign("title", "Purchase Order");
		    $smarty->assign("subject", sprintf($LANG['PO_NOT_FOUND'], $poid));
		    $smarty->display("redir.tpl");
		    exit;
		}
		// check owner
		if ($check_owner && $form['user_id'] != $sessioninfo['id'] && $sessioninfo['level']<9999){
		    $smarty->assign("url", "/purchase_order.php");
		    $smarty->assign("title", "Purchase Order");
		    $smarty->assign("subject", sprintf($LANG['PO_NO_ACCESS'], $poid));
		    //print ($form['user_id'] . " $poid " . $sessioninfo['id']);
		    $smarty->display("redir.tpl");
		    exit;
		}
		$form['deliver_to'] = unserialize($form['deliver_to']);
		if($form['deliver_to']){
			foreach($form['deliver_to'] as $k=>$v){
				$q1=$con->sql_query("select user_id,u from user_privilege
				left join user on user_id = user.id
				where privilege_code  = 'PO_VIEW_ONLY' and branch_id=$v");

				while ($r_u = $con->sql_fetchrow($q1)){
					$temp['user'][]=$r_u['u'];
					$temp['user_id'][]=$r_u['user_id'];
				}
				$user_list[$v]=$temp;
				$temp='';			
			}
			$smarty->assign("user_list",$user_list);
		}
		$form['delivery_vendor'] = unserialize($form['delivery_vendor']);
		if (is_array($form['delivery_vendor'])){
			foreach ($form['delivery_vendor'] as $bid => $vid){
				$con->sql_query("select description from vendor where id = " . mi($vid));
				$r = $con->sql_fetchrow();
				$form['delivery_vendor_name'][$bid] = $r[0];
			}
		}
		if (is_array($form['deliver_to'])){
			$form['delivery_date'] = unserialize($form['delivery_date']);
			$form['cancel_date'] = unserialize($form['cancel_date']);
			$form['partial_delivery'] = unserialize($form['partial_delivery']);
		}
    	$form['allowed_user'] =unserialize($form['allowed_user']);
		$form['sdiscount'] = unserialize($form['sdiscount']);
		$form['rdiscount'] = unserialize($form['rdiscount']);
		$form['ddiscount'] = unserialize($form['ddiscount']);
		$form['misc_cost'] = unserialize($form['misc_cost']);
		$form['transport_cost'] = unserialize($form['transport_cost']);
		$form['remark'] = unserialize($form['remark']);
		$form['remark2'] = unserialize($form['remark2']);
		$smarty->assign("form", $form);
	}
	elseif ($poid == 0){	
		if (isset($_REQUEST['copy_id'])){
		    $copy_id = intval($_REQUEST['copy_id']);
		    // copy header from old PO
		    $con->sql_query("select branch_id, vendor_id, department_id, po_option, allowed_user, deliver_to, delivery_vendor, vendor.description as vendor, branch.code as branch, branch.report_prefix from po left join vendor on vendor_id = vendor.id left join branch on branch_id = branch.id where po.id = $copy_id and branch_id = $branch_id");
			$form = $con->sql_fetchrow();
			$form['id'] = 0;
   			$form['allowed_user'] =unserialize($form['allowed_user']);
			$form['deliver_to'] = unserialize($form['deliver_to']);
			if($form['deliver_to']){
				foreach($form['deliver_to'] as $k=>$v){
					$q1=$con->sql_query("select user_id,u from user_privilege
					left join user on user_id = user.id
					where privilege_code  = 'PO_VIEW_ONLY' and branch_id=$v");

					while ($r_u = $con->sql_fetchrow($q1)){
						$temp['user'][]=$r_u['u'];
						$temp['user_id'][]=$r_u['user_id'];
					}
					$user_list[$v]=$temp;
					$temp='';
				}
				$smarty->assign("user_list",$user_list);
			}
			$form['delivery_vendor'] = unserialize($form['delivery_vendor']);
			// load
			if (is_array($form['delivery_vendor'])){
				foreach ($form['delivery_vendor'] as $bid => $vid){
					$con->sql_query("select description from vendor where id = " . mi($vid));
					$r = $con->sql_fetchrow();
					$form['delivery_vendor_name'][$bid] = $r[0];
				}
			}
			$smarty->assign("form", $form);
		}
	    elseif (isset($_REQUEST['branch_id'])){
	        // just a page refresh of new PO, do nothing
		}
		else{
		    // new totally PO
	    	$smarty->assign("form", array("branch_id" => $branch_id));
	    }
	}

	$q1=$con->sql_query("select po_items.*, sku_items.sku_item_code, sku_items.description, sku_items.artno, sku_items.mcode, sku_items.link_code, uom.code as order_uom 
from po_items 
left join sku_items on sku_item_id = sku_items.id 
left join uom on order_uom_id = uom.id 
where 
po_id = $poid and branch_id = $branch_id ". (($poid==0)?"and user_id = $sessioninfo[id]":"") ." order by po_sheet_id, id");
	$po_items = array();
	$total = array();
    $foc_annotations = array();
    $foc_id = 0;
    // prepare data for each row
	while ($r = $con->sql_fetchrow($q1)){
	    if (is_array($form['deliver_to'])){
			$r['balance'] = unserialize($r['balance']);
		}

	    if ($r['is_foc']){
			$foc_id++;
			$r['foc_id'] = $foc_id;
		}
	    $r['foc_share_cost'] = unserialize($r['foc_share_cost']);
	    if ($r['foc_share_cost']){
			foreach($r['foc_share_cost'] as $i => $dummy){
			    if ($foc_annotations[$i] != '') $foc_annotations[$i] .= "/";
				$foc_annotations[$i] .= "$foc_id";
			}		
		}
		$r['selling_price_allocation'] = unserialize($r['selling_price_allocation']);
		$r['qty_allocation'] = unserialize($r['qty_allocation']);
		$r['qty_loose_allocation'] = unserialize($r['qty_loose_allocation']);
		$r['foc_allocation'] = unserialize($r['foc_allocation']);
		$r['foc_loose_allocation'] = unserialize($r['foc_loose_allocation']);

		$r['total_selling'] = 0;
		if ($r['selling_uom_fraction']==0) $r['selling_uom_fraction']=1;
		if ($r['order_uom_fraction']==0) $r['order_uom_fraction']=1;
		if (is_array($form['deliver_to'])){
			$r['qty'] = 0;
		    foreach ($form['deliver_to'] as $v=>$k){
			    $q = $r['qty_allocation'][$k]*$r['order_uom_fraction'] + $r['qty_loose_allocation'][$k];
			    $r['qty'] += $q;
			    $total[$r['po_sheet_id']]['qty_allocation'][$k] += $q;
			    $total[$r['po_sheet_id']]['qty'] += $q;

			    $q2 = $r['foc_allocation'][$k]*$r['order_uom_fraction'] + $r['foc_loose_allocation'][$k];
			    $r['foc'] += $q2;
			    $total[$r['po_sheet_id']]['foc_allocation'][$k] += $q2;
			    $total[$r['po_sheet_id']]['foc'] += $q2;

			    $r['ctn'] += $r['qty_allocation'][$k] + $r['foc_allocation'][$k];
				$total[$r['po_sheet_id']]['ctn'] += $r['qty_allocation'][$k] + $r['foc_allocation'][$k];
				$r['total_selling'] += ($q+$q2)/$r['selling_uom_fraction']*$r['selling_price_allocation'][$k];

				$r["br_sp"][$k] = ($q+$q2)/$r['selling_uom_fraction']*$r['selling_price_allocation'][$k];
				$r["br_cp"][$k] = $q/$r['order_uom_fraction']*$r['order_price'];

				$total[$r['po_sheet_id']]['br_sp'][$k] += $r["br_sp"][$k];
				$total[$r['po_sheet_id']]['br_cp'][$k] += $r["br_cp"][$k];
				
				$total['total_ctn'][$k] += $r['qty_allocation'][$k] + $r['foc_allocation'][$k];
				$total['total_pcs'][$k] += $r['qty_loose_allocation'][$k] + $r['foc_loose_allocation'][$k];
			}
		}
		else{
		    $total[$r['po_sheet_id']]['qty'] += $r['qty']*$r['order_uom_fraction']+$r['qty_loose'];
		    $total[$r['po_sheet_id']]['foc'] += $r['foc']*$r['order_uom_fraction']+$r['foc_loose'];

		    $total[$r['po_sheet_id']]['ctn'] += $r['qty'] + $r['foc'];
		}
		if ($r['order_uom_fraction']==0){
			$r['order_uom_fraction'] = 1;
		}

		if (is_array($form['deliver_to'])){
	  		$r['gamount'] = $r['qty']/$r['order_uom_fraction']*$r['order_price'];
  		}
		else{
		    $r['gamount'] = ($r['qty']+($r['qty_loose']/$r['order_uom_fraction']))*$r['order_price'];
	  		$r['total_selling'] = ($r['qty']*$r['order_uom_fraction']+$r['qty_loose']+$r['foc']*$r['order_uom_fraction']+$r['foc_loose'])/$r['selling_uom_fraction']*$r['selling_price'];
		}

		$total[$r['po_sheet_id']]['sell'] += $r['total_selling'];

	    if (!$r['is_foc']){
			$total[$r['po_sheet_id']]['gamount'] += $r['gamount'];
		}
		$r['amount'] = $r['gamount'];

		if ($r['tax']>0){
			$r['amount'] *= ($r['tax']+100)/100;		
		}

		if ($r['discount']){
			$camt = $r['amount'];
			$r['amount'] = parse_formula($r['amount'],$r['discount']);
			$r['disc_amount'] = $camt - $r['amount'];
		}
		//add by gary to avoid empty artno/mcode
		if(!$r['artno_mcode']){
			if($r['artno']){
				$r['artno_mcode']=$r['artno'];			
			}
			else{
				$r['artno_mcode']=$r['mcode'];			
			}				
		}
		if (!$r['is_foc']){
 			$total[$r['po_sheet_id']]['amount'] += $r['amount'];		
		}
		$po_items[$r['po_sheet_id']][$r['id']] = $r;
	}
	// calculate grand total
	foreach ($total as $k=>$dummy){
	    // calculate grand total
	    $a = $total[$k]['amount'];
	    $a = parse_formula($a,$form['misc_cost'][$k],true);
	    $tmpa = $a;
	    $a = parse_formula($a,$form['sdiscount'][$k],false);
	    $total[$k]['sdiscount_amount'] = $tmpa - $a;
	    $b = $a;
	    $a = parse_formula($a,$form['rdiscount'][$k],false); // hidden discount
	    $a = parse_formula($a,$form['ddiscount'][$k],false); // hidden discount (deduct cost)
	    $a += $form['transport_cost'][$k];
	    $b += $form['transport_cost'][$k];
	    $total[$k]['final_amount2'] = $b;
	    $total[$k]['final_amount'] = $a;
	}
	$smarty->assign("total", $total);
	$smarty->assign("po_items", $po_items);
	$smarty->assign("foc_annotations", $foc_annotations);
	// update po amount if load
	if ($load){
	    $con->sql_query("update po set po_amount = ".mf($total[0]['final_amount'])." where id = $poid and branch_id = $branch_id");
	}
	// load comment from approval
	if ($poid > 0){
		$con->sql_query("select i.timestamp, i.log, i.status, user.u from branch_approval_history_items i left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id left join user on i.user_id = user.id where h.ref_table = 'po' and i.branch_id = $branch_id and h.ref_id = $poid order by i.timestamp");

		$smarty->assign("approval_history", $con->sql_fetchrowset());
	}
	
	//get the hq PO distribution list.
	if($form['branch_id']=='1' && $form['approved']){
		$q_po=$con->sql_query("select po_no, po_branch_id, po.id as po_id, branch_id, b1.report_prefix as b_name
from po
left join branch b1 on b1.id=po_branch_id
where hq_po_id = $poid");		
		$r_po=$con->sql_fetchrowset($q_po);
		$smarty->assign("hq_po_list", $r_po);
	}
	//echo"<pre>";print_r($r_po);echo"</pre>";
}


// this is now real PO
function post_process_po($poid,$branch_id)
{
	global $con,$sessioninfo;

	$con->sql_query("select * from po where id = $poid and branch_id = $branch_id");

	$po_head = $con->sql_fetchrow();
	if ($po_head['po_option']==0)
	{
		return assign_po_no($poid, $branch_id);
	}
	$po_head['deliver_to'] = unserialize($po_head['deliver_to']);
	$po_head['allowed_user'] = unserialize($po_head['allowed_user']);
	$po_head['delivery_vendor'] = unserialize($po_head['delivery_vendor']);
    $po_head['delivery_date'] = unserialize($po_head['delivery_date']);
    $po_head['cancel_date'] = unserialize($po_head['cancel_date']);
    $po_head['partial_delivery'] = unserialize($po_head['partial_delivery']);
	$ptop['misc_cost'] = unserialize($po_head['misc_cost']);
	$ptop['sdiscount'] = unserialize($po_head['sdiscount']);
	$ptop['rdiscount'] = unserialize($po_head['rdiscount']);
	$ptop['ddiscount'] = unserialize($po_head['ddiscount']);
	$ptop['transport_cost'] = unserialize($po_head['transport_cost']);

	$big_amount = 0;
	$ret = array();
	$i=0;
	foreach ($po_head['deliver_to'] as $dummy=>$bid)
	{
		$po = $po_head;
		if ($po_head['delivery_vendor'][$bid]>0) // if diff vendor
			$po['vendor_id'] = $po_head['delivery_vendor'][$bid];
		$po['hq_po_id'] = $poid;
		
		if($po_head['allowed_user'][$bid]){
			foreach ($po_head['allowed_user'][$bid] as $k=>$v){
				$temp .= '|'.$k;
			}
		}
		$temp.='|';
		$po['allowed_user']=$temp;
		$temp='';
		$po['delivery_date'] = $po_head['delivery_date'][$bid];
		$po['cancel_date'] = $po_head['cancel_date'][$bid];
		$po['partial_delivery'] = $po_head['partial_delivery'][$bid];
		$po['branch_id'] = $branch_id;
		$po['po_branch_id'] = $bid;
		$po['added'] = 'CURRENT_TIMESTAMP';

		$con->sql_query("insert into po " . mysql_insert_by_field($po, array("branch_id", "po_branch_id", "user_id", "vendor_id", "department_id", "po_date", "added", "status", "approval_history_id", "delivery_date", "cancel_date", "partial_delivery", "sdiscount", "misc_cost", "remark", "transport_cost", "rdiscount", "remark2", "ddiscount", "hq_po_id",'allowed_user')));
		
		$saved_po[$bid] = $con->sql_nextid();
		$ret[] = assign_po_no($saved_po[$bid], $branch_id);
		
		// 8/20/2007 5:16:51 PM gary (add vendor info in send_pm)
		$q1 = $con->sql_query("select description, contact_email from vendor where id=$po[vendor_id]");
		$r1 = $con->sql_fetchrow($q1);
		$vendor=$r1['description'];
		$vendor_email=$r1['contact_email'];
				
		// 8/20/2007 5:16:51 PM gary (add category info in send_pm)
		$q2 = $con->sql_query("select description from category where id=$po[department_id]");
		$r2 = $con->sql_fetchrow($q2);
		$dept=$r2['description'];
		
		if($po_head['allowed_user'][$bid]){
			foreach ($po_head['allowed_user'][$bid] as $k=>$v){
				send_pm($k, sprintf("PO (on behalf from HQ Approved) (HQ%05d, Dept:$dept, Vendor:$vendor)",$saved_po[$bid]), "/purchase_order.php?a=view&id=$saved_po[$bid]&branch_id=$branch_id");
			}
		}

		// load the items and split
		$ritems = $con->sql_query("select * from po_items where po_id = $poid and branch_id = $branch_id order by id");
		$po_total[$bid] = 0;
		while ($item = $con->sql_fetchrow($ritems))
		{
			$item['qty_allocation'] = unserialize($item['qty_allocation']);
			$item['selling_price_allocation'] = unserialize($item['selling_price_allocation']);
			$item['qty_loose_allocation'] = unserialize($item['qty_loose_allocation']);
			$item['foc_allocation'] = unserialize($item['foc_allocation']);
			$item['foc_loose_allocation'] = unserialize($item['foc_loose_allocation']);

			$item['qty'] = $item['qty_allocation'][$bid];
			$item['qty_loose'] = $item['qty_loose_allocation'][$bid];
			$item['foc'] = $item['foc_allocation'][$bid];
			$item['foc_loose'] = $item['foc_loose_allocation'][$bid];
			$item['po_id'] = $saved_po[$bid];
			$item['selling_price'] = $item['selling_price_allocation'][$bid];
			$item['disc_remark'] = $item['discount'];

			if ($po_head['po_option']==1)
			{
				$item['order_price'] = $item['resell_price'];
			}

			//calc weight and discount
			$total = @array_sum($item['qty_allocation'])*$item['order_uom_fraction']+@array_sum($item['qty_loose_allocation']);
			$this_total = $item['qty']*$item['order_uom_fraction']+$item['qty_loose'];

			$amt = $this_total/$item['order_uom_fraction']*$item['order_price'];
			$amt *= ($item['tax']+100)/100;

			if ($item['discount'] != '')
			{
				$amt = parse_formula($amt, $item['discount'], false, $this_total/$total, $z);
				$item['discount'] = sprintf("%.3f", -$z);
			}
			$po_total[$bid] += $amt;
			$big_amount += $amt;

			$con->sql_query("insert into po_items " . mysql_insert_by_field($item, array("po_id", "branch_id", "user_id", "po_sheet_id", "sku_item_id", "qty", "selling_price", "is_foc", "foc_share_cost", "foc_noprint", "selling_uom_id", "order_uom_id", "order_price", "order_uom_fraction", "selling_uom_fraction", "tax", "discount", "disc_remark", "qty_loose", "remark", "remark2", "foc", "foc_loose", "artno_mcode", 'cost_indicate')));
		}
	}

	foreach ($po_head['deliver_to'] as $dummy=>$bid)
	{
		/// calculate amount vs total amount
		$weight = $po_total[$bid]/$big_amount;
		$a = $po_total[$bid];
		$update = array();

		$a = parse_formula($a,$ptop['misc_cost'][0],true,$weight,$z);
		$update['misc_cost'] = serialize(array("0"=> sprintf("%.3f",$z)));

		$a = parse_formula($a,$ptop['sdiscount'][0],false,$weight,$z);
		$update['sdiscount'] = serialize(array("0"=> sprintf("%.3f",-$z)));

		$a = parse_formula($a,$ptop['rdiscount'][0],false,$weight,$z); // hidden discount
		$update['rdiscount'] = serialize(array("0"=> sprintf("%.3f",-$z)));

		$a = parse_formula($a,$ptop['ddiscount'][0],false,$weight,$z); // hidden discount (deduct cost)
		$update['ddiscount'] = serialize(array("0"=> sprintf("%.3f",-$z)));

		$a += $ptop['transport_cost'][0]*$weight;
		$update['transport_cost'] = serialize(array("0"=> sprintf("%.3f",$ptop['transport_cost'][0]*$weight)));

		$update['po_amount'] =  sprintf("%.2f",$a);
		//print_r($update);
		$con->sql_query("update po set " . mysql_update_by_field($update) . " where id=$saved_po[$bid] and branch_id = $branch_id");
	}
	
	$con->sql_query("update po set approved=1, active=0 where id=$poid and branch_id = $branch_id");	
	//email_to_supplier($vendor_email);
	
	return join(" ", $ret);
}
/*
function email_to_supplier($vendor_email){
	global $con, $sessioninfo, $smarty;
	
	ini_set("display_errors",1);
	$mailer = new PHPMailer();
	$mailer->Subject="Purchase Order From Aneka";
	$mailer->Host="mail.aneka.com.my";
	$mailer->Body="write somethg related";

	//$mailer->AddAddress("yinsee@wsatp.com");
	$mailer->AddAddress("garykoay@wsatp.com");
	//$mailer->AddAddress("$vendor_email");
	//$mailer->AddAddress("sllee@aneka.com.my");
	print "Sending";
	$send = $mailer->Send();
	var_dump($send);
}
*/

function assign_po_no($poid, $branch_id)
{
	global $con;
	$con->sql_query("select report_prefix, ip from branch where id = $branch_id");
	$report_prefix = $con->sql_fetchrow();

	// set approved flag and generate PO number
	$con->sql_query("select max(po_no) as mx from po where branch_id = $branch_id and po_no like '$report_prefix[0]%'");
	$r = $con->sql_fetchrow();

	if (!$r)
		$n = 1;
	else
		$n = substr($r[0],-5)+1;


	$pono = $report_prefix[0] . sprintf("%05d", $n);
	while(!$con->sql_query("update po set po_no='$pono', approved=1 where id=$poid and branch_id = $branch_id",false,false))
	{
		$n++;
		$pono = $report_prefix[0] . sprintf("%05d", $n);
	}
	return $pono;
}

function save_temp_items(){
	global $con, $branch_id;

	$pfield = '';

	// we can save the values user entered into the table.
 	if (isset($_REQUEST['is_foc'])) $pfield = 'is_foc';
/* 	elseif (isset($_REQUEST['is_foc'])) $pfield = 'is_foc';
	die("no reference field ");*/

	if ($pfield)
	{
	    foreach ($_REQUEST[$pfield] as $n => $dummy)
	    {
			foreach ($_REQUEST[$pfield][$n] as $rid => $dummy)
			{
			//'sku_item_id','qty','cost','selling','qty_allocation', 'is_foc', 'foc_share_cost', 'foc_noprint' )
				$update = array();
				$update['artno_mcode'] = $_REQUEST['artno_mcode'][$n][$rid];
			    $update['remark'] = $_REQUEST['item_remark'][$n][$rid];
			    $update['remark2'] = $_REQUEST['item_remark2'][$n][$rid];

			    $update['selling_price'] = $_REQUEST['selling_price'][$n][$rid];
			    $update['selling_uom_id'] = $_REQUEST['selling_uom_id'][$n][$rid];
			    $update['selling_uom_fraction'] = $_REQUEST['selling_uom_fraction'][$n][$rid];
			    $update['order_price'] = $_REQUEST['order_price'][$n][$rid];
			    $update['cost_indicate'] = $_REQUEST['cost_indicate'][$n][$rid];
			    $update['resell_price'] = $_REQUEST['resell_price'][$n][$rid];
			    $update['order_uom_id'] = $_REQUEST['order_uom_id'][$n][$rid];
			    $update['order_uom_fraction'] = $_REQUEST['order_uom_fraction'][$n][$rid];
			    if ($branch_id == 1)
				{
					$update['qty_allocation'] = serialize($_REQUEST['qty_allocation'][$n][$rid]);
			    	$update['qty_loose_allocation'] = serialize($_REQUEST['qty_loose_allocation'][$n][$rid]);
			    	$update['foc_allocation'] = serialize($_REQUEST['foc_allocation'][$n][$rid]);
			    	$update['foc_loose_allocation'] = serialize($_REQUEST['foc_loose_allocation'][$n][$rid]);
			    	$update['selling_price_allocation'] = serialize($_REQUEST['selling_price_allocation'][$n][$rid]);
				}
				else
				{
					$update['qty'] = $_REQUEST['qty'][$n][$rid];
			    	$update['qty_loose'] = $_REQUEST['qty_loose'][$n][$rid];
					$update['foc'] = $_REQUEST['foc'][$n][$rid];
			    	$update['foc_loose'] = $_REQUEST['foc_loose'][$n][$rid];
			    }
			    $update['tax'] = $_REQUEST['tax'][$n][$rid];
			    $update['discount'] = $_REQUEST['discount'][$n][$rid];
				$con->sql_query("update po_items set " . mysql_update_by_field($update) . " where id = $rid and branch_id = $branch_id");
			}
		}
	}
}

// add item to po rows
// return -1 if item exist
// return -2 if max row reached
// otherwise return the row ID
function add_temp_row(&$r, $n, $foc_sz = ''){

	global $con, $sessioninfo, $LANG;

 	// && $r['vendor_id'] == $_REQUEST['vendor_id']
 	if ($r['artno'])
	{
		$r['artno_mcode'] = $r['artno'];
	}
	else
	{
		$r['artno_mcode'] = $r['mcode'];
	}
		
	$r['po_id'] = intval($_REQUEST['id']);
	$r['branch_id'] = intval($_REQUEST['branch_id']);
	$r['po_sheet_id'] = intval($n);
	$r['sku_item_id'] = $r['id'];
    $r['user_id'] = $sessioninfo['id'];
    $r['sku'] = $r['description'];

    if($r['order_uom_fraction'])
    	$r['order_uom_fraction'] = $r['order_uom_fraction'];
	else
		$r['order_uom_fraction'] = '1';
		
    if($r['selling_uom_fraction'])
    	$r['selling_uom_fraction'] = $r['selling_uom_fraction'];
	else
		$r['selling_uom_fraction'] = '1';
    //$r['order_uom_fraction'] = 1;
    //$r['selling_uom_fraction'] = 1;
    
    if($r['uom_id'])
    	$r['order_uom_id'] = $r['uom_id'];
	else
		$r['order_uom_id'] = '1';

    if($r['selling_uom_id'])
    	$r['selling_uom_id'] = $r['selling_uom_id'];
	else
		$r['selling_uom_id'] = '1';
    
    //$r['order_uom_id'] = 1;
    //$r['selling_uom_id'] = 1;
    if (!isset($r['resell_price'])) $r['resell_price'] = $r['selling_price'];
    if (!isset($r['order_price'])) $r['order_price'] = $r['cost_price'];
    if ($foc_sz)
    {
        $r['is_foc'] = 1;
        $r['foc_share_cost'] = $foc_sz;
	}
	else
		$r['is_foc'] = 0;

	// allow multiple row for same FOC item
	if (!$r['is_foc'])
	{
		// save to temporary table
		$con->sql_query("select id from po_items where po_id = $r[po_id] and branch_id = $r[branch_id] and po_sheet_id = $r[po_sheet_id] and sku_item_id= $r[sku_item_id] and user_id = $sessioninfo[id] and is_foc = $r[is_foc]");
		if ($con->sql_numrows() > 0)
		{
		     return -1;
		}
	}

	// check total items
	$con->sql_query("select count(*) from po_items where po_id = $r[po_id] and branch_id = $r[branch_id] and po_sheet_id = $r[po_sheet_id] and user_id = $sessioninfo[id]");
	$t = $con->sql_fetchrow();
	if ($t[0] >= MAX_ITEMS_PER_PO)
	{
     	return -2;
	}

    $con->sql_query("insert into po_items " . mysql_insert_by_field($r, array('po_id', 'branch_id', 'user_id','po_sheet_id','sku_item_id', 'artno_mcode', 'order_uom_fraction', 'selling_uom_fraction', 'is_foc', 'foc_share_cost', 'selling_price', 'resell_price', 'order_price','selling_uom_id','order_uom_id','cost_indicate') ));
 	$r['id'] = $con->sql_nextid();
 	return $r['id'];
}

require_once("vendor_sku.include.php");

?>
