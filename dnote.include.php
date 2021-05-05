<?php
/*
5/6/2015 5:55 PM Justin
- Enhanced DN generate for GRN can create from account verification.

5/7/2015 12:02 PM Justin
- Bug fixed on extra DN row from GRN the description is getting html tag code.
- Bug fixed on extra dn remark that did not decode.

11:30 AM 5/8/2015 Justin
- Enhanced to pickup document date.

5/15/2015 3:33 PM Justin
- Bug fixed on generating D/N items for GRN.

7/9/2015 3:13 PM Justin
- Bug fixed on D/N for GRA decimal rounded to 2 instead of 4.

11/24/2015 2:02 PM Justin
- Bug fixed on Driver Name and NRIC will missing after generate D/N for GRA.

11/30/2015 9:43 PM DingRen
- generate dn from gra with direct use gra items amount, gst and gst amount

10/20/2017 5:27 PM Justin
- Enhanced to capture current date as D/N date for GRN and GRA while found config is turned on.

5/2/2018 4:16 PM Justin
- Enhanced to have foreign currency feature.

9/24/2018 5:52 PM Justin
- Enhanced generate D/N for GRN which is not under GST status.

4/29/2019 2:34 PM Andy
- Fixed total_amount zero if no turn on GST.
*/
function generate_dn_from_gra($branch_id, $gra_id, $need_print=false){
	global $con, $sessioninfo, $LANG, $config;
	
	$form = $_REQUEST;
	$branch_id = mi($branch_id);
	$gra_id = mi($gra_id);
	
	if(!$branch_id || !$gra_id) js_redirect("Invalid Parameters", "/goods_return_advice.checkout.php");
	
	// get the gra
	$con->sql_query("select * from gra where branch_id=$branch_id and id=$gra_id");
	$gra = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$gra) js_redirect("GRA ID#$gra_id not found.", "/goods_return_advice.checkout.php");
	
	$gra['misc_info'] = unserialize($gra['misc_info']);
	
	// found grn, verify if the DN is generated from arms system
	$q1 = $con->sql_query("select * from dnote where ref_table = 'gra' and ref_id = ".mi($gra_id)." and branch_id = ".mi($branch_id));
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
	
	$gra_items_list = array();
	$total_gross_amount = $total_gst_amount = $total_amount = 0;
	
	// GRA needs check again for GST status
	/*$is_under_gst = 0;
	if($config['enable_gst']){
		$prms = array();
		$prms['vendor_id'] = $gra['vendor_id'];
		$prms['date'] = date("Y-m-d", strtotime($gra['return_timestamp']));
		$is_under_gst = check_gst_status($prms);
	}*/
	
	// get gra items list
	$q1 = $con->sql_query("select gi.* 
						   from gra_items gi
						   where gi.branch_id=$branch_id and gi.gra_id=$gra_id
						   order by gi.id");

	if($con->sql_numrows($q1) > 0){
		while($r = $con->sql_fetchassoc($q1)){
			$row_amount = $r['amount'];
			//$row_qty = $r['qty'];
			
			$total_gross_amount += $row_amount;
			$r['item_gross_amount'] = $row_amount;
		
			if($gra['is_under_gst']){
				$gst_info = get_sku_gst("output_tax", $r['sku_item_id']);
				/*$r['gst_id'] = $r['id'];
				$r['gst_code'] = $r['code'];
				$r['gst_rate'] = $gst_info['rate'];*/
				$r['gst_indicator'] = $gst_info['indicator_receipt'];
				
				// check if selling price is already inclusive tax, need to calculate and pickup selling price before gst
				/*$is_sku_inclusive = get_sku_gst("inclusive_tax", $r['sku_item_id']);
				if($is_sku_inclusive == 'yes'){ // is inclusive tax
					// find the price before tax
					$gst_tax_price = round($r['selling_price'] / ($r['gst_rate']+100) * $r['gst_rate'], 2);
					$r['selling_price'] -= $gst_tax_price;
				}*/
				/*$row_gst_amount = round($r['gst_rate'] / 100 * $r['cost'] * $r['qty'], $config['global_cost_decimal_points']);*/
				
				$total_gst_amount += $r['gst'];
				$r['item_gst_amount'] = $r['gst'];
			}
			$r['item_amount'] = ($row_amount + $r['gst']);
			$total_amount += $r['item_amount'];

			// setup more info
			$more_info = array();
			$more_info['doc_no'] = $r['doc_no'];
			$more_info['doc_date'] = $r['doc_date'];
			$r['more_info'] = serialize($more_info);
			
			$gra_items_list[] = $r;
		}
	}
	$con->sql_freeresult($q1);
	
	$xtra_items = unserialize($gra['extra']);
	foreach($xtra_items['code'] as $idx=>$code){
		$r = array();
		$r['sku_item_id'] = 0;
		$r['selling_price'] = $xtra_items['cost'][$idx];
		$r['cost'] = $xtra_items['cost'][$idx];
		$r['qty'] = $xtra_items['qty'][$idx];
		$r['reason'] = $xtra_items['reason'][$idx];
		$row_amount = $r['cost'] * $r['qty'];
		
		// setup more info
		$more_info = array();
		$more_info['code'] = $xtra_items['code'][$idx];
		$more_info['description'] = $xtra_items['description'][$idx];
		$more_info['doc_no'] = $xtra_items['doc_no'][$idx];
		$more_info['doc_date'] = $xtra_items['doc_date'][$idx];
		$r['more_info'] = serialize($more_info);
		
		$total_gross_amount += $row_amount;
		$r['item_gross_amount'] = $row_amount;
		
		if($gra['is_under_gst']){
			$gst_rate = $xtra_items['gst_rate'][$idx];

			$row_gst_amount=round($row_amount*((100+$gst_rate)/100),2);
			$row_amount=round($row_amount,2);
			$row_gst=$row_gst_amount-$row_amount;

			$total_gst_amount += $row_gst;
			$r['item_gst_amount'] = $row_gst;
			$r['gst_id'] = $xtra_items['gst_id'][$idx];
			$r['gst_code'] = $xtra_items['gst_code'][$idx];
			$r['gst_rate'] = $xtra_items['gst_rate'][$idx];
		}
		$r['item_amount'] = ($row_amount + $row_gst);
		$total_amount += $r['item_amount'];

		$gra_items_list[] = $r;
	}
	
	if(!$gra_items_list) js_redirect($LANG['GRA_NO_ITEMS'], "/goods_return_advice.checkout.php");
	
	// insert dnote
	$ins = array();
	$ins['branch_id'] = $branch_id;
	$ins['vendor_id'] = $gra['vendor_id'];
	$ins['ref_id'] = $gra['id'];
	$ins['ref_table'] = "gra";
	if($config['gra_dn_date_use_current_date']) $ins['dn_date'] = date("Y-m-d", time());
	else $ins['dn_date'] = date("Y-m-d", strtotime($gra['return_timestamp']));
	$ins['is_under_gst'] = $gra['is_under_gst'];
	$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
	//$ins['inv_no'] = strtoupper($form['inv_no']);
	//$ins['inv_date'] = $form['inv_date'];
	$ins['remark'] = $form['remark'];
	$ins['total_gross_amount'] = $total_gross_amount;
	$ins['total_gst_amount'] = $total_gst_amount;
	$ins['total_amount'] = $total_amount;
	if($gra['currency_code']){
		$ins['currency_code'] = $gra['currency_code'];
		$ins['currency_rate'] = $gra['currency_rate'];	
	}
	//$ins['rounding_adjustment'] = $total_rounding_adj;
	
	$con->sql_query("insert into dnote ".mysql_insert_by_field($ins));
	$dnote_id = $con->sql_nextid();

	// generate dn no (branch report prefix + dnote id)
	$con->sql_query("select report_prefix from branch where id=$branch_id");
	$report_prefix = $con->sql_fetchfield(0);
	$con->sql_freeresult();
	$arms_dn_no = $report_prefix.sprintf('%05d', $dnote_id);
	
	// update arms_dn_no into dnote and gra
	$upd = array();
	$upd['dn_no'] = $arms_dn_no;
	$con->sql_query("update dnote set ".mysql_update_by_field($upd)." where id = ".mi($dnote_id)." and branch_id = ".mi($branch_id));
	
	$upd = array();
	$misc_info = array();
	$misc_info['dn_no'] = $arms_dn_no;
	if($config['gra_dn_date_use_current_date']) $misc_info['dn_date'] = date("Y-m-d", time());
	else $misc_info['dn_date'] = date("Y-m-d", strtotime($gra['return_timestamp']));
	$misc_info['dn_amount'] = strval(round($total_amount, 2));
	$misc_info['return_ctn'] = strval(round($gra['misc_info']['return_ctn'], $config['global_qty_decimal_points']));
	$misc_info['name'] = $gra['misc_info']['name'];
	$misc_info['nric'] = $gra['misc_info']['nric'];
	$upd['misc_info'] = serialize($misc_info);
	$con->sql_query("update gra set ".mysql_update_by_field($upd)." where id = ".mi($gra['id'])." and branch_id = ".mi($branch_id));
	
	// insert dnote_items
	foreach($gra_items_list as $item){
		$ins = array();
		$ins['branch_id'] = $branch_id;
		$ins['dnote_id'] = $dnote_id;
		$ins['sku_item_id'] = $item['sku_item_id'];
		$ins['cost'] = $item['cost'];
		$ins['selling_price'] = $item['selling_price'];
		$ins['qty'] = $item['qty'];
		$ins['reason'] = ($item['reason']) ? $item['reason'] : $item['return_type'];
		$ins['item_gross_amount'] = $item['item_gross_amount'];
		$ins['more_info'] = $item['more_info'];
		if($gra['is_under_gst']){
			$ins['gst_id'] = $item['gst_id'];
			$ins['gst_code'] = $item['gst_code'];
			$ins['gst_rate'] = $item['gst_rate'];
			$ins['gst_indicator'] = $item['gst_indicator'];
		}
		$ins['item_gst_amount'] = $item['item_gst_amount'];
		$ins['item_amount'] = $item['item_amount'];
		
		$con->sql_query("insert into dnote_items ".mysql_insert_by_field($ins));
	}
	
	// capture log
	log_br($sessioninfo['id'], 'ARMS_DN', $dnote_id, "ARMS D/N Generated from GRA ID#$gra_id, ID#$dnote_id, BID#$branch_id");
	
	// print out debit note
	if($need_print){
		// call a function print_dn to print
		header("Location: ?a=print_dn&id=$dnote_id&branch_id=$branch_id");
		exit;
	}
}

function generate_dn_from_grn($prms){
	global $con, $sessioninfo, $config, $LANG;
	
	$form = $_REQUEST;
	$branch_id = mi($prms['branch_id']);
	$grn_id = mi($prms['grn_id']);
	$is_generate = mi($prms['is_generate']);
	
	if(!$branch_id || !$grn_id) js_redirect("Invalid Parameters", "/goods_receiving_note.php");
	
	// get the grn
	$con->sql_query("select grn.*, grr.rcv_date
					 from grn 
					 left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
					 where grn.branch_id=$branch_id and grn.id=$grn_id");
	$grn = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	// get the doc no and date from grr_items
	$con->sql_query("select gi.*
					 from grr_items gi 
					 left join grr on grr.id = gi.grr_id and grr.branch_id = gi.branch_id
					 where grr.branch_id=".mi($grn['branch_id'])." and grr.id=".mi($grn['grr_id'])." and gi.type = 'INVOICE' 
					 order by gi.id
					 limit 1");
	$grr = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$grn) js_redirect("GRN ID#$grn_id not found.", "/goods_receiving_note.php");
	
	// found grn, verify if the DN is generated from arms system
	if($is_generate) $filter = " and active=1";
	$q1 = $con->sql_query("select * from dnote where ref_table = 'grn' and ref_id = ".mi($grn['id'])." and branch_id = ".mi($grn['branch_id']).$filter);
	$dnote_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	if($grn['dn_number'] && !$is_generate){
		if($dnote_info){
			// already have dn no
			header("Location: ?a=print_dn&id=".mi($dnote_info['id'])."&branch_id=".mi($dnote_info['branch_id']));
		}else{
			// found it is external DN, redirect user to GRN main page 
			js_redirect($LANG['GST_DN_EXISTED'], "/goods_receiving_note.php");
		}
		exit;
	}
	
	$grn_items_list = array();
	$total_gross_amount = $total_gst_amount = $total_amount = $total_rounding_adj = 0;
	
	// get grn items list
	$q1 = $con->sql_query($abc="select gi.*, u.fraction
						   from grn_items gi
						   left join uom u on u.id = gi.uom_id
						   where gi.branch_id=$branch_id and gi.grn_id=$grn_id
						   order by gi.id");
	while($r = $con->sql_fetchassoc($q1)){
		$inv_qty = ($r['ctn'] * $r['fraction']) + $r['pcs'];
		$inv_cost = $r['cost'];
		
		// check account cost and qty
		if($r['inv_cost'] != ""){
			$inv_cost = $r['inv_cost'];
			if($r['acc_cost'] != ""){
				$acc_cost = $r['acc_cost'];
			}else{
				$acc_cost = $r['cost'];
			}
		}elseif($r['acc_cost'] != "") $acc_cost = $r['acc_cost'];
		else $acc_cost = $inv_cost;
		
		if($r['inv_qty'] != ""){ // if found account got set invoice qty
			$inv_qty = $r['inv_qty'];
			if($r['acc_ctn'] != "" || $r['acc_pcs'] != ""){
				$acc_qty = ($r['acc_ctn'] * $r['fraction']) + $r['acc_pcs'];
			}else{
				$acc_qty = ($r['ctn'] * $r['fraction']) + $r['pcs'];
			}
		}elseif($r['acc_ctn'] != "" || $r['acc_pcs'] != ""){
			$acc_qty = ($r['acc_ctn'] * $r['fraction']) + $r['acc_pcs'];
		}else $acc_qty = $inv_qty;
		
		// check if no different between account grn vs grn then skip this row
		if($acc_cost == $inv_cost && $acc_qty == $inv_qty) continue;
		
		// found it is having account cost != grn cost and account qty != grn qty
		if($acc_cost != $inv_cost && $inv_qty != $acc_qty){
			$r['qty'] = ($inv_qty - $acc_qty);
			$r['cost'] = ((($acc_cost * $acc_qty) - ($inv_cost * $inv_qty)) / ($acc_qty - $inv_qty)) / $r['fraction'];
		}elseif($acc_cost == $inv_cost && $inv_qty != $acc_qty){ // found acc qty not equal to grn qty
			$r['qty'] = ($inv_qty - $acc_qty) / $r['fraction'];
			$r['cost'] = $acc_cost;
		}else{ // found acc cost not equal to grn cost
			$r['qty'] = $acc_qty / $r['fraction'];
			$r['cost'] = ($inv_cost - $acc_cost);
		}
		$r['cost'] = round($r['cost'], $config['global_cost_decimal_points']);
		
		$row_amount = round($r['cost'] * $r['qty'], 2);

		if($grn['is_under_gst']){
			// get the actual gst rate
			$gst_rate = $r['gst_rate'];
			if($r['acc_gst_rate']) $gst_rate = $r['acc_gst_rate'];
			
			// get price indicator
			$gst_info = get_sku_gst("output_tax", $r['sku_item_id']);
			$r['gst_indicator'] = $gst_info['indicator_receipt'];
			
			$row_gst_amount = round($gst_rate / 100 * $r['cost'] * $r['qty'], 2);
			
			// calculate the rounding adjustment
			$row_inv_gst_amt = round($inv_qty / $r['fraction'] * $inv_cost, 2) + round(($inv_qty / $r['fraction'] * $inv_cost * $gst_rate / 100), 2);
			$row_acc_amt = round($acc_qty / $r['fraction'] * $acc_cost, 2);
			$row_acc_gst_amt = round($row_acc_amt + $row_amount + $row_gst_amount, 2) + round(($acc_qty / $r['fraction'] * $acc_cost * $gst_rate / 100), 2);
			$row_rounding_adj = round($row_inv_gst_amt - $row_acc_gst_amt, 2);
			
			$r['item_gst_amount'] = $row_gst_amount;
			
			$total_rounding_adj += $row_rounding_adj;
			$total_gst_amount += $row_gst_amount;
		}
		$r['item_amount'] = $row_amount + $row_gst_amount;

		$total_gross_amount += $row_amount;
		$r['item_gross_amount'] = $row_amount;
		$grn_items_list[] = $r;
	}
	$con->sql_freeresult($q1);
	
	// if found got set extra D/N amount, need to capture it and place as one of the dnote items
	if($is_generate && $form['use_extra_dn']){
		$tmp = array();
		$tmp['sku_item_id'] = 0;
		$tmp['gst_id'] = $form['extra_gst_id'];
		$tmp['gst_code'] = $form['extra_gst_code'];
		$tmp['gst_rate'] = $form['extra_gst_rate'];
		$tmp['qty'] = 1;
		$tmp['cost'] = $form['extra_dn_amount'];
		$more_info['description'] = urldecode($form['extra_dn_remark']);
		$tmp['more_info'] = serialize($more_info);
		
		$row_amount = round($tmp['cost'] * $tmp['qty'], 2);
		$row_gst_amount = 0;
		if($grn['is_under_gst']) $row_gst_amount = round($tmp['gst_rate'] / 100 * $tmp['cost'] * $tmp['qty'], 2);

		$tmp['item_gross_amount'] = $row_amount;
		
		if($grn['is_under_gst']){
			$tmp['item_gst_amount'] = $row_gst_amount;
		
			$total_gst_amount += $row_gst_amount;
		}
		$tmp['item_amount'] = $row_amount + $row_gst_amount;
		$total_gross_amount += $row_amount;
		
		$grn_items_list[] = $tmp;
	}
	
	$total_amount = $total_gross_amount + round($total_gst_amount, 2);
	
	if($total_amount == 0) js_redirect($LANG['GST_DN_NOTHING_TO_PRINT'], "/goods_receiving_note.php");
	
	// insert dnote
	$ins = array();
	$ins['total_gross_amount'] = $total_gross_amount;
	
	//if($grn['is_under_gst']){
		$ins['total_gst_amount'] = $total_gst_amount;
		$ins['total_amount'] = $total_amount;
	//}
	//$ins['rounding_adjustment'] = $total_rounding_adj;
	$ins['last_update'] = "CURRENT_TIMESTAMP";
	
	if($dnote_info && $is_generate){
		$con->sql_query("update dnote set ".mysql_update_by_field($ins)." where id = ".mi($dnote_info['id'])." and branch_id = ".mi($dnote_info['branch_id']));
		$dnote_id = $dnote_info['id'];
		
		$con->sql_query("delete from dnote_items where dnote_id = ".mi($dnote_info['id'])." and branch_id = ".mi($dnote_info['branch_id']));
	}else{
		$ins['branch_id'] = $branch_id;
		$ins['vendor_id'] = $grn['vendor_id'];
		$ins['ref_id'] = $grn['id'];
		$ins['ref_table'] = "grn";
		$ins['is_under_gst'] = $grn['is_under_gst'];
		if($config['grn_dn_date_use_current_date']) $ins['dn_date'] = date("Y-m-d", time());
		else $ins['dn_date'] = date("Y-m-d", strtotime($grn['rcv_date']));
		//$ins['inv_no'] = strtoupper($form['inv_no']);
		//$ins['inv_date'] = $form['inv_date'];
		//$ins['remark'] = $form['remark'];
		$ins['added'] = "CURRENT_TIMESTAMP";

		$con->sql_query("insert into dnote ".mysql_insert_by_field($ins));
		$dnote_id = $con->sql_nextid();
	}

	// generate dn no (branch report prefix + dnote id)
	$con->sql_query("select report_prefix from branch where id=$branch_id");
	$report_prefix = $con->sql_fetchfield(0);
	$con->sql_freeresult();
	$arms_dn_no = $report_prefix.sprintf('%05d', $dnote_id);
	
	// update arms_dn_no into dnote and grn
	$upd = array();
	$upd['dn_no'] = $arms_dn_no;
	$con->sql_query("update dnote set ".mysql_update_by_field($upd)." where id = ".mi($dnote_id)." and branch_id = ".mi($branch_id));
	
	$upd = array();
	$upd['dn_issued'] = 1;
	$upd['dn_number'] = $arms_dn_no;
	$upd['dn_amount'] = $total_amount;
	$upd['final_amount'] = $grn['account_amount'] - $total_amount;
	$upd['rounding_amt'] = $total_rounding_adj;
	$con->sql_query("update grn set ".mysql_update_by_field($upd)." where id = ".mi($grn['id'])." and branch_id = ".mi($branch_id));
	
	// insert dnote_items
	foreach($grn_items_list as $item){
		$ins = array();
		$ins['branch_id'] = $branch_id;
		$ins['dnote_id'] = $dnote_id;
		$ins['sku_item_id'] = $item['sku_item_id'];
		$ins['cost'] = $item['cost'];
		$ins['selling_price'] = $item['selling_price'];
		$ins['qty'] = $item['qty'];
		$ins['reason'] = "Amount Variance";
		$ins['gst_id'] = $item['gst_id'];
		$ins['gst_code'] = $item['gst_code'];
		$ins['gst_rate'] = $item['gst_rate'];
		$ins['item_gross_amount'] = $item['item_gross_amount'];
		$ins['item_gst_amount'] = $item['item_gst_amount'];
		$ins['item_amount'] = $item['item_amount'];
		$ins['gst_indicator'] = $item['gst_indicator'];
		// if found got invoice set, need to store doc_no and doc_date
		if($grr){
			$more_info = array();
			if($item['more_info']){
				$more_info = unserialize($item['more_info']);
			}
			$more_info['doc_no'] = $grr['doc_no'];
			$more_info['doc_date'] = $grr['doc_date'];
			$item['more_info'] = serialize($more_info);
		}
		$ins['more_info'] = $item['more_info'];
		
		$con->sql_query("insert into dnote_items ".mysql_insert_by_field($ins));
	}
	
	// capture log
	log_br($sessioninfo['id'], 'ARMS_DN', $dnote_id, "ARMS D/N Generated from GRN ID#$grn_id, DN ID#$dnote_id, BID#$branch_id");
	
	// print out debit note
	if($prms['need_print']){
		// call a function print_dn to print
		header("Location: ?a=print_dn&id=$dnote_id&branch_id=$branch_id");
		exit;
	}
}
?>
