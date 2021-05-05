<?
/*
4/25/2007 added gary
- when PO is cancelled, REQuest items can be used again
- when item deleted from PO, REQuest items can be used again

6/11/2007 5:24:39 PM added gary
- cost/selling price GRN>PO>Master

6/18/2007 1:54:11 PM added gary
- uom and uom_fraction from GRN>PO>MATER

6/27/2007 5:54:24 PM -gary
- added allowed_user list for each selected branch

6/28/2007 5:46:55 PM - yinsee
- fix bug when rejected PO directly "confirm", give error PO_ALREADY_CONFIRM_OR_APPROVED 

8/21/2007 3:06:15 PM -gary
-add mysql query to get selling price from sku_item_price > grn > po > master 

9/18/2007 3:45:18 PM yinsee
- delivered PO not allow cancel

9/20/2007 10:52:51 AM gary
- get the all splited branch po_no for approved_hq_po tab.

9/27/2007 4:27:59 PM gary
- remove confusing $dept usage
- po sending with mail to suppliers

10/23/2007 2:47:12 PM gary
- only get the selling_price from sku_item_price table if have.

10/23/2007 4:45:50 PM gary
- get cost_indicate when add item.

11/1/2007 5:31:31 PM gary
- add print distribution selection. (either print with total qty or individual qty)

11/28/2007 1:47:10 PM gary
- call Selling UOM from SKU_ITEMs packing_uom_id

1/9/2008 3:25:50 PM gary
- PO to indicate payment terms when printing.

2/20/2008 5:59:46 PM  yinsee
- zero qty not allow confirm

2/22/2008 4:35:39 PM gary
- not allow to add FOC items for the same FOC items.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO') && !privilege('PO_VIEW_ONLY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO/PO_VIEW_ONLY', BRANCH_CODE), "/index.php");
include("purchase_order.include.php");

$smarty->assign("PAGE_TITLE", "Purchase Order");

$branch_id = intval($_REQUEST['branch_id']);
if ($branch_id == 0){
 	$branch_id = $sessioninfo['branch_id'];
}
get_allowed_user_list();

if (isset($_REQUEST['a'])){

	switch($_REQUEST['a']){
	
		case 'ajax_show_related_sku':
		    $sku_item_id = intval($_REQUEST['sku_item_id']);
		
			if (isset($_REQUEST['id'])){
				$poid = intval($_REQUEST['id']);
		
				$con->sql_query("select sku_item_id from po_items where branch_id = $branch_id and po_id = $poid and user_id = $sessioninfo[id]");
		
				$idlist = array();
				while($r = $con->sql_fetchrow()){
					$idlist[] = $r[0];
				}
				if ($idlist){
					$idlist = "and sku_items.id not in (".join(",",$idlist).")";		
				}
				else{
					$idlist = '';		
				}
			}
			else{
				$idlist = '';	
			}
			
			$con->sql_query("select sku_id from sku_items where id=$sku_item_id");
			$r1 = $con->sql_fetchrow();			
			$sku_id=$r1['sku_id'];
		
			$rr1 = $con->sql_query("select sku_id, sku_item_code, sku_items.id, sku_items.description, sku_items.mcode, sku.varieties, sku_items.artno, sku_items.selling_price, sku_items.cost_price
from sku_items
left join sku on sku_id = sku.id 
left join category on sku.category_id = category.id 
where sku_id=$sku_id $idlist
group by sku_items.id
order by sku_items.id, sku_items.description, sku_items.artno, sku_items.mcode");
		
			$items = array();
			while ($r=$con->sql_fetchrow($rr1)){
				$items[] = $r;
			}
			$smarty->assign("items", $items);
			$smarty->assign("related_sku", '1');
			$smarty->display('purchase_order.new.show_sku.tpl');
		    exit;	
		
		case 'ajax_show_vendor_sku':
			$smarty->assign("show_varieties",1);
		    get_vendor_sku();
		    exit;

		case 'ajax_expand_sku':
			if (!isset($_REQUEST['showheader'])) $smarty->assign("hideheader",1);
			$smarty->assign("show_varieties",0);
		    expand_sku(intval($_REQUEST['sku_id']));
		    exit;

		case 'ajax_add_vendor_sku':
			$n = intval($_REQUEST['po_sheet_id']);
			foreach (array_keys($_REQUEST['sel']) as $sku_item_id)
			{
				// find all varieties of this sku
				//$result = $con->sql_query("select sku.vendor_id, sku_items.*, cost_price as order_price, cost_price as resell_price from sku_items left join sku on sku_id = sku.id where sku_items.id = $sku_item_id");
				if (BRANCH_CODE != 'HQ') $branch_check = "and branch_id = $branch_id";
				//if($_REQUEST['related_sku']){
				//	$r = get_items_detail($sku_item_id,$branch_id);			
				//}
				//else{
				$r = get_items_detail($sku_item_id,$branch_id);
/*					$result = $con->sql_query("select sku_items.*,vendor_sku_history.vendor_id,vendor_sku_history.artno, mcode, vendor_sku_history.selling_price, vendor_sku_history.cost_price as resell_price,vendor_sku_history.cost_price as order_price from vendor_sku_history left join sku_items on vendor_sku_history.sku_item_id = sku_items.id where sku_item_id =$sku_item_id $branch_check order by vendor_sku_history.added desc limit 1");
					$r = $con->sql_fetchrow($result);					*/
				//}				
			
				$ret = add_temp_row($r, 0);
				if ($ret==-2){
				    print "<script>alert('".jsstring(sprintf($LANG['PO_MAX_ITEM_CANT_ADD'], MAX_ITEMS_PER_PO))."');</script>\n";
					break;
				}
			}
			print "<script>refresh_tables();</script>";
			exit;

	    case 'ajax_add_sheet':
	    	save_temp_items();
	        if ($branch_id == 1 && count($_REQUEST['deliver_to'])==0)
			{
				fail("No Delivery Branch Selected");
			}

	        $smarty->assign("form", $_REQUEST);
	        $smarty->assign("sheet_n", intval($_REQUEST['n']));
	        $smarty->display("purchase_order.new.sheet.tpl");
	        exit;


		case 'ajax_temp_save':
		    save_temp_items();
			exit;

	    case 'ajax_delete_po_row':
	        $id = intval($_REQUEST['id']);
	        $con->sql_query("update po_request_items set active=1,added=added where po_item_id=$id and branch_id=$branch_id");// restore request item to re-use (gary)
	        $con->sql_query("delete from po_items where id=$id and branch_id=$branch_id");
			exit;

		case 'ajax_update_foc_row':
		    $id = intval($_REQUEST['id']);
		    if (!isset($_REQUEST['sel_foc']) && !isset($_REQUEST['no_item']))
		    {
			    fail("No Item selected");
			}
			validate_duplicate_foc('sid');
			$foc_sz = serialize($_REQUEST['sel_foc']);

			$con->sql_query("update po_items set foc_share_cost = ".ms($foc_sz)." where id = $id and branch_id = $branch_id");
			print $LANG['PO_FOC_TABLE_UPDATED'];
			exit;

		case 'ajax_refresh_foc_annotations':
		    $poid = intval($_REQUEST['po_id']);
			// regenerate foc annotations and return as XML for update
			$con->sql_query("select id, is_foc, foc_share_cost from po_items where po_id = $poid and branch_id = $branch_id and user_id = $sessioninfo[id] order by po_sheet_id, id");
		    $foc_id = 0;
			$arr  = array();
		    // prepare data for each row
			while ($r = $con->sql_fetchrow())
			{
			    if ($r['is_foc'])
				{
					$foc_id++;
					$arr[$r['id']]['fid'] = $foc_id;
				}
				else
				{
					$arr[$r['id']]['fid'] = '';
				}
			    $r['foc_share_cost'] = unserialize($r['foc_share_cost']);
			    if ($r['foc_share_cost'])
					foreach($r['foc_share_cost'] as $i => $dummy)
					{
					    if ($arr[$i]['tag'] != '') $arr[$i]['tag'] .= "/";
						$arr[$i]['tag'] .= "$foc_id";
					}
			}
			$arr2 = array();
			foreach ($arr as $k=>$c)
			{
			    $arr2[] = array("id" => $k, "fid" => $c['fid'], "tag" => $c['tag']);
			}
			header('Content-Type: text/xml');
	        print array_to_xml($arr2);
			exit;

		case 'ajax_sel_foc_cost':
			$sid = intval($_REQUEST['sid']);
		    $n = intval($_REQUEST['n']);
		    $id = intval($_REQUEST['id']);
		    $poid = intval($_REQUEST['po_id']);
		    
		    $smarty->assign("sheet_n", $n);
		    $smarty->assign("foc_item_id", $id);
		    $smarty->assign("sid", $sid);
		    $foc_sz = array();
		    if ($id > 0)
		    {
				$con->sql_query("select foc_share_cost from po_items where id = $id and branch_id=$branch_id");
				$r = $con->sql_fetchrow();
				$foc_sz = unserialize($r[0]);
			}
		    $con->sql_query("select p1.*, sku_items.sku_item_code, sku_items.description from po_items p1 left join sku_items on sku_item_id = sku_items.id where po_sheet_id = $n and is_foc = 0 and branch_id = $branch_id and po_id = $poid ".(($poid==0)? "and user_id = $sessioninfo[id]" : "") . " order by id");
		    //or (1))
		    $smarty->assign("foc_sel", $foc_sz);
		    $smarty->assign("po_items", $con->sql_fetchrowset());
		    $smarty->display("purchase_order.new.sel_foc_cost.tpl");
		    exit;

		case 'ajax_add_foc_row':
		    if (!isset($_REQUEST['sel_foc']) && !isset($_REQUEST['no_item']))
		    {
			    fail("No Item selected");
			}
			validate_duplicate_foc('sku_item_id');
			$foc_sz = serialize($_REQUEST['sel_foc']);
			// continue as normal add row

		case 'ajax_add_po_row':
        	save_temp_items();

   	        $n = intval($_REQUEST['n']);
			$smarty->assign("sheet_n", $n);

			$sku_item_id = $_REQUEST['sku_item_id'][$n];
			$smarty->assign("form", array('po_option' => $_REQUEST['po_option'], 'deliver_to' => $_REQUEST['deliver_to']));
												    
			$r = get_items_detail($sku_item_id,$branch_id);
			$ret = add_temp_row($r, $n, $foc_sz);
			if ($ret==-1)
				fail($LANG['PO_ITEM_ALREADY_IN_PO']);
	    	elseif ($ret==-2)
				fail(sprintf($LANG['PO_MAX_ITEM_CANT_ADD'], MAX_ITEMS_PER_PO));

	    	$smarty->assign("item", $r);

	    	$con->sql_query("select count(*) from po_items where po_id=$_REQUEST[id] and po_sheet_id=$n and branch_id = $branch_id ".(($poid==0)? "and user_id = $sessioninfo[id]" : ""));
	    	$item_count = $con->sql_fetchrow();
	    	$smarty->assign("item_n", $item_count[0]);
    		$rowdata = $smarty->fetch("purchase_order.new.po_row.tpl");
	    	$arr[] = array("id" => $r['id'], "rowdata" => $rowdata);

			header('Content-Type: text/xml');
	        print array_to_xml($arr);
			exit;


		case 'ajax_load_po_list':
		    load_po_list();
		    $smarty->display("purchase_order.list.tpl");
		    exit;


		// ---------------------------------------------------------------------
		// copy to new PO
		case 'revoke':
			//
			$id = intval($_REQUEST['id']);
			$con->sql_query("insert into po (branch_id, user_id, vendor_id, department_id, po_date, po_option, deliver_to, delivery_vendor, delivery_date, cancel_date, partial_delivery, sdiscount, misc_cost, remark, remark2, transport_cost, rdiscount, ddiscount, po_amount) select branch_id, user_id, vendor_id, department_id, po_date, po_option, deliver_to, delivery_vendor, delivery_date, cancel_date, partial_delivery, sdiscount, misc_cost, remark, remark2, transport_cost, rdiscount, ddiscount, po_amount from po where id=$id and branch_id = $branch_id");
			$new_po_id = $con->sql_nextid();
			$con->sql_query("update po set added = CURRENT_TIMESTAMP, revoke_id = $new_po_id where id=$id and branch_id = $branch_id");

			$con->sql_query("insert into po_items (po_id, branch_id, user_id, po_sheet_id, sku_item_id, artno_mcode, qty, selling_price, selling_price_allocation, qty_allocation, is_foc, foc_share_cost, foc_noprint, selling_uom_id, order_uom_id, order_price, resell_price, order_uom_fraction, selling_uom_fraction, qty_loose_allocation, tax, discount, qty_loose, remark, remark2, foc, foc_loose, foc_allocation, foc_loose_allocation, cost_indicate) 
select $new_po_id, branch_id, user_id, po_sheet_id, sku_item_id, artno_mcode, qty, selling_price, selling_price_allocation, qty_allocation, is_foc, foc_share_cost, foc_noprint, selling_uom_id, order_uom_id, order_price, resell_price, order_uom_fraction, selling_uom_fraction, qty_loose_allocation, tax, discount, qty_loose, remark, remark2, foc, foc_loose, foc_allocation, foc_loose_allocation, cost_indicate
from po_items where po_id=$id and branch_id = $branch_id order by id");

		    $smarty->assign("id", $new_po_id);
		    $smarty->assign("type", "revoke");
		    log_br($sessioninfo['id'], 'PURCHASE ORDER', $new_po_id, "PO Revoked (ID#$id -> ID#$new_po_id)");
		    break;

		// PO printing
        case 'print_distribution':
        	load_po($_REQUEST['id'],true,false);
			$form = $smarty->get_template_vars('form');
        	$con->sql_query("select u from user where id=$form[user_id]");
        	$tmp = $con->sql_fetchrow();
        	$form['username'] = $tmp[0];
        	$con->sql_query("select description from category where id=$form[department_id]");
        	$tmp = $con->sql_fetchrow();
        	$form['department'] = $tmp[0];
        	$dd = $form['delivery_date'];
        	$cd = $form['cancel_date'];
        	foreach($form['deliver_to'] as $bid)
        	{
        		$con->sql_query("select id, code, description from branch where id=$bid");
        		$tmp = $con->sql_fetchrow();
				$form['branches'][] = array("id" => $tmp[0], "code"=>$tmp[1],"description"=>$tmp[2],"delivery"=>$dd[$bid],"cancel"=>$cd[$bid]);
			}
        	$smarty->assign('from_request', $_REQUEST);
        	$smarty->assign('form', $form);
        	$smarty->display("purchase_order.print_distribution.tpl");
        	exit;
        
		// PO printing	
        case 'print':
			if (isset($_REQUEST['load'])){
			    load_po($_REQUEST['id'],true,false);
				$form = $smarty->get_template_vars('form');
			}
			else{
				save_temp_items();

				$form = $_REQUEST;
				load_po($_REQUEST['id'],false,false);
			}
			
			//get vendor payment term
			$form['payment_term']=get_payment_term($form['vendor_id'],$branch_id);			

			$total = $smarty->get_template_vars("total");
			if ($total[0]['qty']+$total[0]['foc']<=0)
			{
			    print "<script>alert('$LANG[PO_PRINT_ZERO_QTY]');</script>\n";
			    exit;
			}
			// dept name
			$con->sql_query("select description from category where id = " . mi($form['department_id']));
			$r = $con->sql_fetchrow();
			$form['department'] = $r[0];

			// user name
			$con->sql_query("select fullname from user where id = " . mi($form['user_id']));
			$r = $con->sql_fetchrow();
			$form['fullname'] = $r[0];

			if (!$form['approved'])
			{
			    $con->sql_query("select report_prefix from branch where id = $branch_id");
			    $report_prefix = $con->sql_fetchrow();
			    $smarty->assign("report_prefix", $report_prefix[0]);

			    if ($form['status']==0)
			    	$form['po_no'] = sprintf("%s%05d(DP)",$report_prefix[0],$form['id']);
				else
				    $form['po_no'] = sprintf("%s%05d(PP)",$report_prefix[0],$form['id']);
			}

			// if po is single branch
			if (!is_array($form['deliver_to']))
			{
				$smarty->assign("form", $form);

				// get vendor's address for this branch if exist
				$con->sql_query("select * from vendor where id = " . mi($form['vendor_id']));
				$vd = $con->sql_fetchrow();
				$con->sql_query("select * from branch_vendor where vendor_id = " . mi($form['vendor_id']) . " and branch_id = " . mi($form['branch_id']));
				if ($vdb = $con->sql_fetchrow()) $vd = array_merge($vd, $vdb);
				$smarty->assign("vendor", $vd);

				if ($form['po_branch_id']==0)
				{
					$con->sql_query("select * from branch where id = " . mi($form['branch_id']));
					$smarty->assign("billto", $con->sql_fetchrow());
					$con->sql_query("select * from branch where id = " . mi($form['branch_id']));
					$smarty->assign("deliver", $con->sql_fetchrow());
				}
				else
				{
					$con->sql_query("select * from branch where id = " . mi($form['po_branch_id']));
					$smarty->assign("billto", $con->sql_fetchrow());
					$con->sql_query("select * from branch where id = " . mi($form['po_branch_id']));
					$smarty->assign("deliver", $con->sql_fetchrow());
				}

				$smarty->assign("print", array("vendor_copy"=>isset($_REQUEST['print_vendor_copy']), "branch_copy"=>isset($_REQUEST['print_branch_copy'])));
				//echo"<pre>";print_r($form);echo"</pre>";
				$smarty->display("purchase_order.print.tpl");
			}
			else
			{

				$org_po_items = $smarty->get_template_vars("po_items");
				$org_total = $smarty->get_template_vars("total");
				$org_form = $form;

				foreach($form['deliver_to'] as $dummy=>$bid)
				{
					// post process (split) po items
					$po_items = $org_po_items;
					$form = $org_form;
					$total = array();

					foreach ($po_items[0] as $r)
					{
						$r_org = $r;
						$r['qty'] = $r['qty_allocation'][$bid];
						$r['qty_loose'] = $r['qty_loose_allocation'][$bid];
						$r['foc'] = $r['foc_allocation'][$bid];
						$r['foc_loose'] = $r['foc_loose_allocation'][$bid];
						$r['selling_price'] = $r['selling_price_allocation'][$bid];

						if ($r['order_uom_fraction']==0) $r['order_uom_fraction'] = 1;

						$total[$r['po_sheet_id']]['qty'] += $r['qty'] * $r['order_uom_fraction'] + $r['qty_loose'];
						$total[$r['po_sheet_id']]['foc'] += $r['foc'] * $r['order_uom_fraction'] + $r['foc_loose'];
						$total[$r['po_sheet_id']]['ctn'] += $r['qty'] + $r['foc'];

						$r['gamount'] = ($r['qty']+($r['qty_loose']/$r['order_uom_fraction']))*$r['order_price'];
						$r['total_selling'] = ($r['qty']*$r['order_uom_fraction']+$r['qty_loose']+$r['foc']*$r['order_uom_fraction']+$r['foc_loose'])/$r['selling_uom_fraction']*$r['selling_price'];

						$total[$r['po_sheet_id']]['sell'] += $r['total_selling'];

						if (!$r['is_foc']) $total[$r['po_sheet_id']]['gamount'] += $r['gamount'];
						$r['amount'] = $r['gamount'];

						if ($r['tax']>0)
							$r['amount'] *= ($r['tax']+100)/100;

						if ($r['discount'])
						{
							$camt = $r['gamount'];
							//print "$r[gamount] $r_org[gamount] $r[disc_amount]<br>";
							//$r['amount'] = parse_formula($r['amount'],$r['discount']);
							//$r['disc_amount'] = $camt - $r['amount'];

							// calculate discount amount proportional to branch-amount versus total-amount
							$r['disc_amount'] = $r_org['disc_amount']*($r['gamount']/$r_org['gamount']);
							$r['amount'] = $camt - $r['disc_amount'];
						}

						if (!$r['is_foc']) $total[$r['po_sheet_id']]['amount'] += $r['amount'];
						$po_items[$r['po_sheet_id']][$r['id']] = $r;
					}

					// calculate grand total
					foreach ($total as $k=>$dummy)
					{
						// calculate grand total
						$weight = $total[$k]['amount']/$org_total[$k]['amount'];

						$a = $total[$k]['amount'];
						$a = parse_formula($a,$form['misc_cost'][$k],true, $weight,$z);
						$form['misc_cost_amount'][$k] = sprintf(" (%.2f)",$z);

						//$tmpa = $a;
						$a = parse_formula($a,$form['sdiscount'][$k],false, $weight,$z);
						$total[$k]['sdiscount_amount'] = -$z;
						$b = $a;
						$a = parse_formula($a,$form['rdiscount'][$k],false, $weight,$z); // hidden discount
						$form['rdiscount_amount'][$k] = sprintf(" (%.2f)",-$z);
						$a = parse_formula($a,$form['ddiscount'][$k],false, $weight,$z); // hidden discount (deduct cost)
						$form['ddiscount_amount'][$k] = sprintf(" (%.2f)",-$z);
						$a += $form['transport_cost'][$k]*$weight;
						$b += $form['transport_cost'][$k]*$weight;
						$form['transport_cost_amount'][$k] = sprintf(" (%.2f)",$form['transport_cost'][$k]*$weight);
						$total[$k]['final_amount2'] = $b;
						$total[$k]['final_amount'] = $a;
					}
					$smarty->assign("total", $total);
					$smarty->assign("po_items", $po_items);
				// finish post-process

					$form['delivery_date'] = $form['delivery_date'][$bid];
					$form['cancel_date'] = $form['cancel_date'][$bid];

					$smarty->assign("form", $form);
					if ($form['delivery_vendor'][$bid] > 0)
						$vid = $form['delivery_vendor'][$bid];
					else
						$vid = $form['vendor_id'];


					// get vendor's address for this branch if exist
					$con->sql_query("select * from vendor where id = " . mi($vid));
					$vd = $con->sql_fetchrow();
					$con->sql_query("select * from branch_vendor where vendor_id = " . mi($vid) . " and branch_id = " . mi($form['branch_id']));
					if ($vdb = $con->sql_fetchrow()) $vd = array_merge($vd, $vdb);
					$smarty->assign("vendor", $vd);

					if ($form['po_option']==1) // hq purchase and sell to branch
					{
						$con->sql_query("select * from branch where id = " . mi($form['branch_id']));
						$smarty->assign("billto", $con->sql_fetchrow());
					}
					else // hq purchase on behalf of branch
					{
						$con->sql_query("select * from branch where id = " . mi($bid));
						$smarty->assign("billto", $con->sql_fetchrow());
					}
					$con->sql_query("select * from branch where id = " . mi($bid));
					$smarty->assign("deliver", $con->sql_fetchrow());
					$smarty->assign("print", array("vendor_copy"=>isset($_REQUEST['print_vendor_copy']), "branch_copy"=>isset($_REQUEST['print_branch_copy'])));
					$smarty->display("purchase_order.print.tpl");
					$smarty->assign("skip_header",1);
				}
			}

			$con->sql_query("update po set print_counter=print_counter+1, last_update=last_update where id=$form[id] and branch_id=$form[branch_id]");
			log_br($sessioninfo['id'], 'PURCHASE ORDER', $form['id'], "Print PO (ID#$form[po_no])");
			exit;

		case 'delete':
			$id = intval($_REQUEST['id']);
			$branch_id = intval($_REQUEST['branch_id']);
			
			$poid = intval($_REQUEST['id']);
		    if ($sessioninfo['level']<9999) $usrcheck = " and user_id = $sessioninfo[id]";
		    if ($poid==0)
		    {
		        /// if opening only, delete
				// $con->sql_query("delete from po where id = $poid and branch_id = $branch_id and user_id = $sessioninfo[id]");
			    $con->sql_query("delete from po_items where po_id = $poid and branch_id = $branch_id and user_id = $sessioninfo[id]");
			}
			else
		    {
				//call restore_po_request_item function from po.include.php
				// restore whole PO request items (gary)
				restore_po_request_item($id,$branch_id);

		        // otherwise, do cancel for saved PO
			    $con->sql_query("update po set cancel_by=$sessioninfo[id],cancelled=CURRENT_TIMESTAMP(),last_update=last_update,status=5,active=0 where id = $poid and branch_id = $branch_id $usrcheck");
			}
			if ($con->sql_affectedrows()>0)
			{
			    $smarty->assign("id", $poid);
			    $smarty->assign("type", "delete");
			    log_br($sessioninfo['id'], 'PURCHASE ORDER', $poid, "PO Deleted (ID#$poid)");
		    }
		    break;

		case 'cancel':
			$id = intval($_REQUEST['id']);
			$branch_id = intval($_REQUEST['branch_id']);
		
			if ($sessioninfo['level']<9999) $usrcheck = " and user_id = $sessioninfo[id]";
		    // PO status 5 = cancelled
		    $poid = intval($_REQUEST['id']);
		    $con->sql_query("update po set cancel_by=$sessioninfo[id],cancelled=CURRENT_TIMESTAMP(),last_update=last_update,status=5,active=0 where delivered=0 and id = $poid and branch_id = $branch_id $usrcheck");
		    if (!$con->sql_affectedrows())
		    {
		    	print "<script>alert('$LANG[PO_CANNOT_CANCEL_DELIVERED_PO]')</script>";
		    	break;
			}

			//call restore_po_request_item function from po.include.php
			restore_po_request_item($id,$branch_id);// restore whole PO request items (gary)

			if ($con->sql_affectedrows()>0)
			{
			    $smarty->assign("id", $poid);
			    $smarty->assign("type", "cancel");
			    log_br($sessioninfo['id'], 'PURCHASE ORDER', $poid, "PO Cancelled (ID#$poid)");
		    }
			break;

        case 'refresh':
			save_temp_items();
			get_allowed_user_list();
			$smarty->assign("form", $_REQUEST);
			load_po($_REQUEST['id'],false);
			$smarty->display("purchase_order.new.tpl");
			exit;


		case 'confirm':
		    if ($_REQUEST['id']>0)
		    {
		        // make sure this po is not yet confirmed...
		        $con->sql_query("select status, active, approved from po where id = $_REQUEST[id] and branch_id = $branch_id");
		        if ($r = $con->sql_fetchrow())
		        {
		            if (!$r['active'])
		            {
					    $smarty->assign("url", "/purchase_order.php");
					    $smarty->assign("title", "Purchase Order");
					    $smarty->assign("subject", sprintf($LANG['PO_INACTIVE'], $_REQUEST['id']));
					    $smarty->display("redir.tpl");
			            exit;
					}
		            elseif (($r['status']>0 && $r['status'] !=2) || $r['approved'])
		            {
					    $smarty->assign("url", "/purchase_order.php");
					    $smarty->assign("title", "Purchase Order");
					    $smarty->assign("subject", sprintf($LANG['PO_ALREADY_CONFIRM_OR_APPROVED'], $_REQUEST['id']));
					    $smarty->display("redir.tpl");
			            exit;
					}
				}
				else
				{
				    $smarty->assign("url", "/purchase_order.php");
				    $smarty->assign("title", "Purchase Order");
				    $smarty->assign("subject", sprintf($LANG['PO_NOT_FOUND'], $poid));
				    $smarty->display("redir.tpl");
				    exit;
				}
			}
		    $is_confirm=true;
		    // continue to saving

		case 'save':
		    $last_approval = false;
			$form = $_REQUEST;
		    save_temp_items();
		    $errm = validate_data($form,$is_confirm);

			// if PO is confirmed.... do what leh?
		    if (!$errm && $is_confirm){
		        // 1. check approval flow
		        if ($form['is_request'])
		        	$astat = check_and_create_branch_approval('PURCHASE_ORDER_REQUEST', $branch_id, 'po', "sku_category_id = $form[department_id]");
		        else
		        	$astat = check_and_create_branch_approval('PURCHASE_ORDER', $branch_id, 'po', "sku_category_id = $form[department_id]");
       			if (!$astat)
				{
					$errm['top'][] = $form['is_request'] ? $LANG['PO_REQUEST_NO_APPROVAL_FLOW'] : $LANG['PO_NO_APPROVAL_FLOW'];
				}
				else
				{
					$form['approval_history_id'] = $astat[0];
				    // then set status as approved
	       			if ($astat[1] == '|') $last_approval = true;
				}
			}

		    if (!$errm){
		        // make PO actual
			    if ($is_confirm) $form['status'] = 1;
		    	$pid = save_po($form);

		    	if ($is_confirm){
		    	
			        log_br($sessioninfo['id'], 'PURCHASE ORDER', $pid, "PO Confirmed (ID#$pid)");
				    if ($last_approval)
				    {
						$po_no = post_process_po($pid,$branch_id);
      					$smarty->assign("id", $pid);
					    $smarty->assign("pono", $po_no);
					    $smarty->assign("type", "approved");
					}
					else
					{
						$con->sql_query("update branch_approval_history set ref_id = $pid where id = $form[approval_history_id] and branch_id = $branch_id");
					    $smarty->assign("id", $pid);

					    $con->sql_query("select report_prefix from branch where id = $branch_id");
					    $report_prefix = $con->sql_fetchrow();
         				$smarty->assign("pono", sprintf("%s%06d(PP)", $report_prefix[0], $pid));
					    $smarty->assign("type", "confirm");
				    }
		    	}
				else
				{
				    log_br($sessioninfo['id'], 'PURCHASE ORDER', $pid, "PO Saved (ID#$pid)");
				    // after save , return to front page lo
				    $smarty->assign("id", $pid);
				    $smarty->assign("type", "save");
			    }
		    }
		    else{
		        $smarty->assign("form", $form);
		        $smarty->assign("errm", $errm);
				load_po($form['id'], false);
				$smarty->display("purchase_order.new.tpl");
				exit;
			}
			break;


		case 'view':
		    load_po(-1,true,false);
		    $form = $smarty->get_template_vars('form');
		    $con->sql_query("select description from category where id = " . mi($form['department_id']));
		    $r=$con->sql_fetchrow();
		    $smarty->assign("department", $r[0]);
			if (isset($_REQUEST['ajax']))
				$smarty->display("purchase_order.ajax_view.tpl");
			else
				$smarty->display("purchase_order.view.tpl");
			exit;

		case 'open':
		    // load PO
		    load_po();
		    $form = $smarty->get_template_vars('form');
		    // check PO status, if new or rejected then allow edit
		    if (privilege('PO') && ($form['id']==0 || ($form['active'] && !$form['approved'] && ($form['status']==0 || $form['status']==2))))
				$smarty->display("purchase_order.new.tpl");
			else{
			    $con->sql_query("select description from category where id = " . mi($form['department_id']));
			    $r=$con->sql_fetchrow();
			    $smarty->assign("department", $r[0]);
				$smarty->display("purchase_order.view.tpl");
			}
			if ($form['id']) log_br($sessioninfo['id'], 'PURCHASE ORDER', $form['id'], "Load PO (ID#$form[id])");
  			//echo"<pre>";print_r($form);echo"</pre>";
			exit;

		case 'chown':
		    $id = intval($_REQUEST['id']);
			$con->sql_query("select id from user left join user_privilege on user.id = user_privilege.user_id where user_privilege.branch_id = $sessioninfo[branch_id] and user.u = " . ms($_REQUEST['new_owner']));
			$r = $con->sql_fetchrow();
			if ($r)
			{
			    $con->sql_query("update po set user_id = $r[0] where id = $id and branch_id = $branch_id");
			}

		    if ($r && $con->sql_affectedrows() > 0)
		    {
		        $con->sql_query("update po_items set user_id = $r[0] where po_id = $id and branch_id = $branch_id");

				printf($LANG['PO_CHOWN_SUCCESS'], $_REQUEST['new_owner']);
			}
			else
		    {
		        printf($LANG['PO_CHOWN_FAILED'], $_REQUEST['new_owner']);
			}
			exit;


		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

$smarty->display("purchase_order.home.tpl");
exit;

function load_po_list($t = 0)
{
	global $con, $sessioninfo, $smarty;

	if (!$t) $t = intval($_REQUEST['t']);

	if (!$sessioninfo['departments'])
		$depts = "(0)";
	else
		$depts = "(" . join(",", array_keys($sessioninfo['departments'])) . ")";

	if ($sessioninfo['level']>=9999)
		$owner_check = "";
	elseif ($sessioninfo['level']>=800) // HQ dept head and above allow to see all branch and own dept
	{
		$owner_check = "(po.department_id in $depts) and ";
	}
	elseif ($sessioninfo['level']>=400) // branch dept head and above allow to see own branch and own dept
	{
		$owner_check = "((po.branch_id = $sessioninfo[branch_id] or po_branch_id = $sessioninfo[branch_id]) and po.department_id in $depts) and ";
	}
	else
		$owner_check = "(user_id = $sessioninfo[id] or allowed_user like '%$sessioninfo[id]%') and";

	switch ($t)
	{
	    case 0:
	        $where = 'po.id = ' . mi($_REQUEST['s']) . ' or po.po_no like ' . ms('%'.$_REQUEST['s']);
	        $_REQUEST['s'] = '';
	        break;

		case 1: // show saved PO
		    $owner_check = "po.user_id = $sessioninfo[id] and";    // strictly own PO and same branch only
        	$where = "po.status = 0 and not po.approved and po.active ";
        	break;

		case 2: // show waiting for approval (and KIV)
		    $where = "(po.status = 1 or po.status = 3) and not po.approved and po.active";
		    break;

		case 3: // show inactive
		   $where = "(po.status = 4 or po.status = 5)";
		    break;

		case 4: // show approved
		    $where = "po.approved = 1 and po.active";
		    break;

		case 5: // show rejected
		    $where = "po.status = 2 and not po.approved and po.active";
		    break;

		case 6: // show approved HQ PO 
		    //$owner_check = "po.user_id = $sessioninfo[id] and";
        	$where = "po.branch_id=1 and po.approved and po.status = 1 and not po.active ";
        	break;

	}

	// pagination
	$start = intval($_REQUEST['s']);
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else
		$sz = 25;
	$con->sql_query("select count(*) from po where $owner_check $where");
	$r = $con->sql_fetchrow();
	$total = $r[0];
	if ($total > $sz)
	{
	    if ($start > $total) $start = 0;
		// create pagination
		$pg = "<b>Goto Page</b> <select onchange=\"list_sel($t,this.value)\">";
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

	$q1=$con->sql_query("select po.*, branch_approval_history.approvals, user.u, vendor.description as vendor, category.description as dept, b1.code as branch, b2.code as po_branch, b1.report_prefix
from po
left join vendor on vendor_id = vendor.id
left join category on po.department_id = category.id
left join branch b1 on po.branch_id = b1.id
left join branch b2 on po_branch_id = b2.id
left join user on user.id = po.user_id
left join branch_approval_history on (po.approval_history_id = branch_approval_history.id and po.branch_id = branch_approval_history.branch_id)
where $owner_check $where
order by po.last_update desc limit $start, $sz");
while ($r1=$con->sql_fetchrow($q1)){
	if($r1['branch_id']=='1' && $r1['approved']){
		$q2=$con->sql_query("select po_no, po_branch_id, po.id as po_id, branch_id, b1.report_prefix as b_name
from po
left join branch b1 on b1.id=po_branch_id
where hq_po_id = $r1[id]");		
		$r2=$con->sql_fetchrowset($q2);
		$r1['po_no_list']=$r2;
	}
	$po_list[]=$r1;
}
//echo"<pre>";print_r($po_list);echo"</pre>";
$smarty->assign("po_list",$po_list);

}

function save_po($form){

	global $con, $smarty, $sessioninfo, $LANG;

	//$form['branch_id'] = $sessioninfo['branch_id'];
	if (!$form['user_id']) $form['user_id'] = $sessioninfo['id'];
	$form['po_date'] = dmy_to_sqldate($form['po_date']);
	$form['added'] = 'CURRENT_TIMESTAMP';

	if (is_array($form['deliver_to']))
	{
		$form['delivery_date'] = serialize($form['delivery_date']);
		$form['cancel_date'] = serialize($form['cancel_date']);
		$form['partial_delivery'] = serialize($form['partial_delivery']);
		$form['deliver_to'] = serialize($form['deliver_to']);
	}
	$form['allowed_user'] = serialize($form['allowed_user']);
	$form['delivery_vendor'] = serialize($form['delivery_vendor']);
	$form['sdiscount'] = serialize($form['sdiscount']);
	$form['rdiscount'] = serialize($form['rdiscount']);
	$form['ddiscount'] = serialize($form['ddiscount']);
	$form['misc_cost'] = serialize($form['misc_cost']);
	$form['transport_cost'] = serialize($form['transport_cost']);
	$form['remark'] = serialize($form['remark']);
	$form['remark2'] = serialize($form['remark2']);
	if ($form['id'] == 0)
	{
		// create new po header
	    while (1)
		{
			// get last po number
			//$con->sql_query("select max(id) from po where branch_id = $sessioninfo[branch_id]");
			//$m = $con->sql_fetchrow();
			//$form['id'] = intval($m[0])+1;
			// branch_id int, user_id int, primary key (id, branch_id), vendor_id int, department_id int, last_update timestamp, po_date timestamp default '0000-00-00', status int(1), temp_po_option int(1), temp_delivery_branches blob
	    	$con->sql_query("insert into po " . mysql_insert_by_field($form, array('branch_id', 'user_id', 'approval_history_id', 'status', 'vendor_id', 'department_id', 'po_date', 'added', 'po_option', 'deliver_to', 'delivery_vendor', 'delivery_date', 'cancel_date', 'partial_delivery', 'sdiscount', 'rdiscount', 'ddiscount', 'misc_cost', 'transport_cost', 'remark', 'remark2', 'po_amount', 'allowed_user') )); //, false, false);
	    	$form['id'] = $con->sql_nextid();
	    	// if insert successful, break
	    	if ($con->sql_affectedrows()>0)
	    	{
	    		$con->sql_query("update po_items set po_id = $form[id] where po_id = 0 and branch_id = $form[branch_id] and user_id = $sessioninfo[id]");
	    		break;
	    	}
		}
	}
	else
	{
	    $con->sql_query("update po set " . mysql_update_by_field($form, array('user_id', 'approval_history_id', 'status', 'vendor_id', 'department_id', 'po_date', 'po_option', 'deliver_to', 'delivery_vendor', 'delivery_date', 'cancel_date', 'partial_delivery', 'sdiscount', 'rdiscount', 'ddiscount', 'misc_cost', 'transport_cost', 'remark', 'remark2','po_amount','allowed_user') ) . " where id = $form[id] and branch_id = $form[branch_id]"); //, false, false);
	}
    //print_r($_REQUEST);
    //exit;
    return $form['id'];
}

function validate_data(&$form, $is_confirm)
{
	global $LANG;

	$err = array();
	
	$form['id'] = intval($form['id']);
	if ($form['vendor_id'] == 0) $err['top'][] = $LANG['PO_INVALID_VENDOR'];
	if ($form['department_id'] == 0) $err['top'][] = $LANG['PO_INVALID_DEPARTMENT'];

	// po date
	$form['po_date'] = str_replace("-", "/", $form['po_date']);
	if ($form['po_date'] == '' || dmy_to_time($form['po_date']) <= 0)
	{
		$err['top'][] = $LANG['PO_INVALID_PO_DATE'];
	}

	// HQ branch id must be 1
	if (isset($form['deliver_to']))
	{
		if (!$form['po_option']) $err['top'][] = $LANG['PO_INVALID_PO_OPTION'];
		if (!$form['deliver_to']) $err['top'][] = $LANG['PO_INVALID_DELIVER_TO'];

	    foreach ($form['delivery_date'] as $k=>$dummy)
	    {
	    	if (in_array($k,$form['deliver_to']))
	    	{
				$form['delivery_date'][$k] = str_replace("-", "/", $form['delivery_date'][$k]);
			    $form['cancel_date'][$k] = str_replace("-", "/", $form['cancel_date'][$k]);

				if ($form['delivery_date'][$k] == '' || dmy_to_time($form['delivery_date'][$k]) <= 0)
				    $err['top'][] = sprintf($LANG['PO_INVALID_DATE'], 'Delivery Date');
				if ($form['cancel_date'][$k] == '' || dmy_to_time($form['cancel_date'][$k]) <= 0)
				    $err['top'][] = sprintf($LANG['PO_INVALID_DATE'], 'Cancellation Date');

		        if (dmy_to_time($form['delivery_date'][$k]) < dmy_to_time($form['po_date']))
				    $err['top'][] = sprintf($LANG['PO_INVALID_DATE'], 'Delivery is before PO Date');
				if (dmy_to_time($form['delivery_date'][$k]) > dmy_to_time($form['cancel_date'][$k]))
				    $err['top'][] = sprintf($LANG['PO_INVALID_DATE'], 'Delivery is after Cancellation Date');
			}
		}
	}
	else
	{
		$form['delivery_date'] = str_replace("-", "/", $form['delivery_date']);
	    $form['cancel_date'] = str_replace("-", "/", $form['cancel_date']);

		if ($form['delivery_date'] == '' || dmy_to_time($form['delivery_date']) <= 0)
		    $err['top'][] = sprintf($LANG['PO_INVALID_DATE'], 'Delivery Date');
		if ($form['cancel_date'] == '' || dmy_to_time($form['cancel_date']) <= 0)
		    $err['top'][] = sprintf($LANG['PO_INVALID_DATE'], 'Cancellation Date');

        if (dmy_to_time($form['delivery_date']) < dmy_to_time($form['po_date']))
		    $err['top'][] = sprintf($LANG['PO_INVALID_DATE'], 'Delivery is before PO Date');
		if (dmy_to_time($form['delivery_date']) > dmy_to_time($form['cancel_date']))
		    $err['top'][] = sprintf($LANG['PO_INVALID_DATE'], 'Delivery is after Cancellation Date');
	}

	if ($form['sheet'])
	{
		foreach($form['sheet'] as $k=>$dummy)
		{
			if (count($form['is_foc'][$k])==0)
			{
			    $err[$k][] = sprintf($LANG['PO_EMPTY']);
			}
			elseif (count($form['is_foc'][$k]) > MAX_ITEMS_PER_PO)
			{
			    $err[$k][] = sprintf($LANG['PO_MAX_ITEM_CANT_SAVE'],MAX_ITEMS_PER_PO,count($form['is_foc'][$k]));
			}
			elseif ($is_confirm && $form['total_check'][$k]<=0)
			{
				$err[$k][] = sprintf($LANG['PO_CONFIRM_TOTAL_QTY_IS_ZERO']);	
			} 

		}
	}
	return $err;
}

function get_allowed_user_list(){
	global $con, $smarty, $sessioninfo, $LANG;
	//-added allowed_user list in each selected branch(gary)
	$dept_id=mi($_REQUEST['department_id']);
	if($_REQUEST['deliver_to']){
		foreach($_REQUEST['deliver_to'] as $k=>$v){
			$q1=$con->sql_query("select user_id,u from user_privilege
	left join user on user_id = user.id
	where privilege_code  = 'PO_VIEW_ONLY' and branch_id=$v and user.departments like '%i:$dept_id;%'");

			while ($r_u = $con->sql_fetchrow($q1)){
				$temp['user'][]=$r_u['u'];
				$temp['user_id'][]=$r_u['user_id'];
			}
			$user_list[$v]=$temp;
			$temp='';
		}
		$smarty->assign("user_list",$user_list);
	}
	//echo"<pre>";print_r($user_list);echo"</pre>";
}


function get_payment_term($vendor_id,$branch_id){
	global $con, $sessioninfo;

	$q=$con->sql_query("select term from branch_vendor where vendor_id=".mi($vendor_id)." and branch_id=".mi($branch_id));		
	$r=$con->sql_fetchrow($q);
	
	if(!$r){
		$q=$con->sql_query("select term from vendor where id=".mi($vendor_id));		
		$r=$con->sql_fetchrow($q);
	}
	$term=$r[0];
	return $term;
	//echo"<pre>";print_r($term);echo"</pre>";		
}

//checking duplicate foc items
function validate_duplicate_foc($var){
	global $LANG;
	$n = intval($_REQUEST['n']);
	//echo"<pre>";print_r($_REQUEST);echo"</pre>";	
	$total_foc=count($_REQUEST['sel_foc']);
	if($total_foc==1){
		foreach($_REQUEST['sel_foc'] as $k=>$v){
			if($_REQUEST[$var][$n]==$_REQUEST['foc_items'][$k]){
				fail($LANG['PO_DUPLICATE_FOC']);
			}
		}
	}
}
?>

