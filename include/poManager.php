<?php
/*
3/22/2017 4:26 PM Andy
- Enhanced to store po.allocation_info and po_items.item_allocation_info.

3/24/2017 2:48 PM Andy
- Enhanced to check selling amount before deviding to get gp.

4/18/2017 11:30 AM Andy
- Add function reCalcatePOAmt()

5/19/2017 3:56 PM Andy
- Fixed item_allocation_info in reCalcatePOAmt().

5/22/2017 12:45 PM Andy
- Add function gotApprovalFlow()

4/4/2018 11:38 AM Andy
- Added poManager function loadPOCurrencyCodeList(), sendCurrencyRateChangedNotification() and loadPOCurrencyRateHistory()
*/
class poManager{
	// public var
	
	
	// private var
	
	function __construct(){
		global $smarty, $con, $appCore;

	
	}
	
	// function to recalulcate PO all Amount using old method
	// return null
	public function reCalcatePOUsingOldMethod($bid, $poID){
		global $config, $con;
		
		$bid = mi($bid);
		$poID = mi($poID);
		
		if(!$bid || !$poID)	die("Invliad Branch ID / PO ID");
		
		//print "Recalculate PO $bid, $poID";
		
		// select header
		$con->sql_query("select * from po where branch_id=$bid and id=$poID");
		$po = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$po)	die("PO Branch ID: $bid, ID: $poID Not Found");
		
		$subtotal_po_gross_amount = 0;
		$subtotal_po_nett_amount = 0;
		$subtotal_po_gst_amount = 0;
		$subtotal_po_amount_incl_gst = 0;
		$po_amount = 0;
		$po_gst_amount = 0;
		$po_amount_incl_gst = 0;
		$misc_cost_amt = 0;
		$sdiscount_amt = 0;
		$rdiscount_amt = 0;
		$ddiscount_amt = 0;
		$transport_cost_amt = 0;
		$total_selling_amt = 0;
		$total_gst_selling_amt = 0;
		$supplier_po_amt = 0;
		$supplier_po_gst_amt = 0;
		$supplier_po_amt_incl_gst = 0;
		
		/// field not store in po ///////
		$supplier_misc_cost_amt = 0;
		$supplier_gst_misc_cost_amt = 0;
		$supplier_sdiscount_amt = 0;
		$supplier_gst_sdiscount_amt = 0;
		$gst_misc_cost_amt = 0;
		$gst_sdiscount_amt = 0;
		$gst_rdiscount_amt = 0;
		$gst_ddiscount_amt = 0;
		$subtotal_gp_amt = 0;
		$total_gp_amt = 0;
		$total_gst_gp_amt = 0;
		/////////////////////////////////
		
		$strval_po_fields = array('subtotal_po_gross_amount','subtotal_po_nett_amount','subtotal_po_gst_amount','subtotal_po_amount_incl_gst','po_gst_amount','po_amount_incl_gst','po_amount','misc_cost_amt','sdiscount_amt','rdiscount_amt','ddiscount_amt','transport_cost_amt','total_selling_amt','total_gst_selling_amt','supplier_po_amt','supplier_po_amt_incl_gst','gst_misc_cost_amt','supplier_misc_cost_amt','supplier_gst_misc_cost_amt','gst_sdiscount_amt','supplier_sdiscount_amt','supplier_gst_sdiscount_amt','gst_rdiscount_amt');
		$strval_po_items_fields = array('tax_amt','discount_amt','item_gross_amt','item_nett_amt','item_gst_amt','item_amt_incl_gst','item_total_selling','item_total_gst_selling');
		
		$po['deliver_to'] = unserialize($po['deliver_to']);
		
		$po['sdiscount']=unserialize($po['sdiscount']);
		if(is_array($po['sdiscount']))	$po['sdiscount']=$po['sdiscount'][0];
		
		$po['rdiscount']=unserialize($po['rdiscount']);
		if(is_array($po['rdiscount']))	$po['rdiscount']=$po['rdiscount'][0];
		
		$po['ddiscount']=unserialize($po['ddiscount']);
		if(is_array($po['ddiscount']))	$po['ddiscount']=$po['ddiscount'][0];
		
		$po['misc_cost']=unserialize($po['misc_cost']);
		if(is_array($po['misc_cost']))	$po['misc_cost']=$po['misc_cost'][0];

		$po['transport_cost']=unserialize($po['transport_cost']);
		if(is_array($po['transport_cost']))	$po['transport_cost']=$po['transport_cost'][0];
	
		//print_r($po);
		
		// select items
		$pi_upd = array();
		$q1 = $con->sql_query("select * from po_items where branch_id=$bid and po_id=$poID order by id");
		$pi_list = array();
		while($pi = $con->sql_fetchassoc($q1)){
			$line_qty = 0;
			$line_foc_qty = 0;
			$item_total_selling = 0;
			$item_total_gst_selling = 0;
			
			if($po['deliver_to'] && is_array($po['deliver_to'])){
				$pi['qty_allocation'] = unserialize($pi['qty_allocation']);
				$pi['qty_loose_allocation'] = unserialize($pi['qty_loose_allocation']);
				$pi['foc_allocation'] = unserialize($pi['foc_allocation']);
				$pi['foc_loose_allocation'] = unserialize($pi['foc_loose_allocation']);
				$pi['selling_price_allocation'] = unserialize($pi['selling_price_allocation']);
				$pi['gst_selling_price_allocation'] = unserialize($pi['gst_selling_price_allocation']);
				
				// multi branch
				foreach($po['deliver_to'] as $tmp_po_bid){
					$tmp_branch_qty = $pi['qty_allocation'][$tmp_po_bid] * $pi['order_uom_fraction'] + $pi['qty_loose_allocation'][$tmp_po_bid];
					$tmp_branch_foc_qty = $pi['foc_allocation'][$tmp_po_bid] * $pi['order_uom_fraction'] + $pi['foc_loose_allocation'][$tmp_po_bid];
					
					$line_qty += $tmp_branch_qty;
					$line_foc_qty += $tmp_branch_foc_qty;
					
					$line_selling_amt = round($pi['selling_price_allocation'][$tmp_po_bid] * ($tmp_branch_qty + $tmp_branch_foc_qty), 2);
					if($pi['selling_gst_id'] && $pi['gst_selling_price_allocation'][$tmp_po_bid]){
						$line_gst_selling_amt = round($pi['gst_selling_price_allocation'][$tmp_po_bid] * ($tmp_branch_qty + $tmp_branch_foc_qty), 2);
					}
					$item_total_selling += $line_selling_amt;
					$item_total_gst_selling += $line_gst_selling_amt;
				}
			}else{
				// single branch
				$line_qty = $pi['qty'] * $pi['order_uom_fraction'] + $pi['qty_loose'];
				$line_foc_qty = $pi['foc'] * $pi['order_uom_fraction'] + $pi['foc_loose'];
				
				// Selling
				$item_total_selling = round($pi['selling_price'] * ($line_qty + $line_foc_qty),2);
				
				// Selling Inclusive GST
				if($pi['selling_gst_id'] && $pi['gst_selling_price']){
					$item_total_gst_selling = round($pi['gst_selling_price'] * ($line_qty + $line_foc_qty),2);
				}
			}
			
			// Gross Amount
			$item_gross_amt = ($pi['order_price'] / $pi['order_uom_fraction']) * $line_qty;
			$item_nett_amt = $item_gross_amt;
			
			// Tax Amount
			$tax_amt = 0;
			if($pi['tax']){
				$tax_amt = mf($pi['tax']) / 100 * $item_nett_amt;
				$item_nett_amt += $tax_amt;
			}
			
			// Discount Amount
			$discount_amt = 0;
			if($pi['discount']){
				$camt = $item_nett_amt;
				$item_nett_amt = parse_formula($item_nett_amt, $pi['discount']);
				$discount_amt = round($camt - $item_nett_amt, 2);
			}
			
			$item_nett_amt = round($item_nett_amt, 2);
			
			$item_gst_amt = 0;
			// calculate gst
			if($po['is_under_gst']){
				$avg_order_price = round($item_nett_amt / ($line_qty + $line_foc_qty), $config['global_cost_decimal_points']);
				$unit_gst_amount = round($avg_order_price * $pi['cost_gst_rate'] / 100, $config['global_cost_decimal_points']);
				$item_gst_amt = round($unit_gst_amount * ($line_qty + $line_foc_qty), 2);
			}
			$item_amt_incl_gst = $item_nett_amt + $item_gst_amt;
			
			
			$item_gp_amt = round($item_total_selling - $item_nett_amt, 2);
			$item_gp_per = round($item_gp_amt / $item_total_selling * 100, 2);
			
			//print "<br>";
			//print "item_gross_amt = $item_gross_amt, tax_amt = $tax_amt, discount_amt = $discount_amt, item_nett_amt = $item_nett_amt, unit_gst_amount = $unit_gst_amount, item_gst_amt = $item_gst_amt, item_amt_incl_gst = $item_amt_incl_gst, item_total_selling = $item_total_selling, item_gp_amt = $item_gp_amt, item_gp_per = $item_gp_per, item_total_gst_selling = $item_total_gst_selling";
			
			$subtotal_po_gross_amount += $item_gross_amt;
			$subtotal_po_nett_amount += $item_nett_amt;
			$subtotal_po_gst_amount += $item_gst_amt;
			$subtotal_po_amount_incl_gst += $item_amt_incl_gst;
			$total_selling_amt += $item_total_selling;
			$total_gst_selling_amt += $item_total_gst_selling;
			
			$upd = array();
			$pi['tax_amt'] = $tax_amt;
			$pi['discount_amt'] = $discount_amt;
			$pi['item_gross_amt'] = $item_gross_amt;
			$pi['item_nett_amt'] = $item_nett_amt;
			$pi['item_gst_amt'] = $item_gst_amt;
			$pi['item_amt_incl_gst'] = $item_amt_incl_gst;
			$pi['item_total_selling'] = $item_total_selling;
			$pi['item_total_gst_selling'] = $item_total_gst_selling;
			$pi['item_allocation_info'] = array();

			$pi_list[$pi['id']] = $pi;
		}
		$con->sql_freeresult($q1);
		
		$subtotal_gp_amt = round($total_selling_amt - $subtotal_po_nett_amount, 2);
		$subtotal_gp_per = $subtotal_gp_amt / $total_selling_amt * 100;
		
		$supplier_po_amt = $po_amount = $subtotal_po_nett_amount;
		$supplier_po_gst_amt = $po_gst_amount = $subtotal_po_gst_amount;
		$supplier_po_amt_incl_gst = $po_amount_incl_gst = $subtotal_po_amount_incl_gst;
		
		
		$total_gp_amt = 0;
		$total_gst_gp_amt = 0;
		
		// Misc Cost
		if($po['misc_cost']){
			$po_amount = parse_formula($po_amount, $po['misc_cost'],true, 1, $misc_cost_amt);
			$po_amount_incl_gst = parse_formula($po_amount_incl_gst, $po['misc_cost'],true, 1, $gst_misc_cost_amt);
			
			$supplier_po_amt = parse_formula($supplier_po_amt, $po['misc_cost'],true, 1, $supplier_misc_cost_amt);
			$supplier_po_amt_incl_gst = parse_formula($supplier_po_amt_incl_gst, $po['misc_cost'],true, 1, $supplier_gst_misc_cost_amt);
		}
		
		// Discount
		if($po['sdiscount']){
			$po_amount = parse_formula($po_amount, $po['sdiscount'], false, 1, $sdiscount_amt);
			$po_amount_incl_gst = parse_formula($po_amount_incl_gst, $po['sdiscount'], false, 1, $gst_sdiscount_amt);
			$sdiscount_amt *= -1;
			$gst_sdiscount_amt *= -1;
			
			$supplier_po_amt = parse_formula($supplier_po_amt, $po['sdiscount'], false, 1, $supplier_sdiscount_amt);
			$supplier_po_amt_incl_gst = parse_formula($supplier_po_amt_incl_gst, $po['sdiscount'], false, 1, $supplier_gst_sdiscount_amt);
			$supplier_sdiscount_amt *= -1;
			$supplier_gst_sdiscount_amt *= -1;
		}
		
		// Discount from Remark#2
		if($po['rdiscount']){
			$po_amount = parse_formula($po_amount, $po['rdiscount'], false, 1, $rdiscount_amt);
			$po_amount_incl_gst = parse_formula($po_amount_incl_gst, $po['rdiscount'], false, 1, $gst_rdiscount_amt);
			$rdiscount_amt *= -1;
			$gst_rdiscount_amt *= -1;
		}
		
		// Deduct Cost from Remark#2
		if($po['ddiscount']){
			$po_amount = parse_formula($po_amount, $po['ddiscount'], false, 1, $ddiscount_amt);
			$po_amount_incl_gst = parse_formula($po_amount_incl_gst, $po['ddiscount'], false, 1, $gst_ddiscount_amt);
			$ddiscount_amt *= -1;
			$gst_ddiscount_amt *= -1;
		}
		
		// Transportation Charges
		if($po['transport_cost']){
			$transport_cost_amt = mf($po['transport_cost']);
			$po_amount += $transport_cost_amt;
			$po_amount_incl_gst += $transport_cost_amt;
			
			$supplier_po_amt += $transport_cost_amt;
			$supplier_po_amt_incl_gst += $transport_cost_amt;
		}
		
		$po_amount = round($po_amount, 2);
		$po_amount_incl_gst = round($po_amount_incl_gst, 2);
		$supplier_po_amt = round($supplier_po_amt, 2);
		$supplier_po_amt_incl_gst = round($supplier_po_amt_incl_gst, 2);
		
		
		// Total GST
		$po_gst_amount = $po_amount_incl_gst - $po_amount;
		$supplier_po_gst_amt = $supplier_po_amt_incl_gst - $supplier_po_amt;
		
		// Total GP
		$total_gp_amt = round($total_selling_amt - $po_amount, 2);
		$total_gp_per = $total_selling_amt? $total_gp_amt / $total_selling_amt * 100 : 0;
		
		// Total GP Include GST
		$total_gst_gp_amt = round($total_gst_selling_amt - $po_amount_incl_gst, 2);
		$total_gst_gp_per = $total_gst_selling_amt ? $total_gst_gp_amt / $total_gst_selling_amt * 100 : 0;
		
		// branch allocation
		$allocation_info = array();
		if($po['deliver_to'] && is_array($po['deliver_to'])){
			// loop branch
			foreach($po['deliver_to'] as $tmp_po_bid){
				// loop po_items
				foreach($pi_list as $po_item_id => $pi){
					$tmp_branch_qty = $pi['qty_allocation'][$tmp_po_bid] * $pi['order_uom_fraction'] + $pi['qty_loose_allocation'][$tmp_po_bid];
					$tmp_branch_foc_qty = $pi['foc_allocation'][$tmp_po_bid] * $pi['order_uom_fraction'] + $pi['foc_loose_allocation'][$tmp_po_bid];
					
					// allocation info
					
					// Selling
					$pi['item_allocation_info'][$tmp_po_bid]['item_total_selling'] = round($pi['selling_price_allocation'][$tmp_po_bid] * ($tmp_branch_qty + $tmp_branch_foc_qty), 2);
					if($pi['selling_gst_id'] && $pi['gst_selling_price_allocation'][$tmp_po_bid]){
						$pi['item_allocation_info'][$tmp_po_bid]['item_total_gst_selling'] = round($pi['gst_selling_price_allocation'][$tmp_po_bid] * ($tmp_branch_qty + $tmp_branch_foc_qty), 2);
					}
					
					
					// Gross Amount
					$pi['item_allocation_info'][$tmp_po_bid]['item_gross_amt'] = ($pi['order_price'] / $pi['order_uom_fraction']) * $tmp_branch_qty;
					$pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'] = $pi['item_allocation_info'][$tmp_po_bid]['item_gross_amt'];
					
					
					
					// Tax Amount
					$pi['item_allocation_info'][$tmp_po_bid]['tax_amt'] = 0;
					if($pi['tax']){
						$pi['item_allocation_info'][$tmp_po_bid]['tax_amt'] = mf($pi['tax']) / 100 * $pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'];
						$pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'] += $pi['item_allocation_info'][$tmp_po_bid]['tax_amt'];
					}
					
					// Discount Amount
					if ($pi['discount_amt']){
						$pi['item_allocation_info'][$tmp_po_bid]['discount_amt'] = $pi['discount_amt']*($pi['item_allocation_info'][$tmp_po_bid]['item_gross_amt']/$pi['item_gross_amt']);
						$pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'] -= $pi['item_allocation_info'][$tmp_po_bid]['discount_amt'];
					}
					$pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'] = round($pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'], 2);
					
					$pi['item_allocation_info'][$tmp_po_bid]['item_gst_amt'] = 0;
					
					if($po['is_under_gst']){
						// calculate gst amount
						$order_price = round($pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'] / ($tmp_branch_qty+$tmp_branch_foc_qty), $config['global_cost_decimal_points']);
						$unit_gst_amount = round($order_price * $pi['cost_gst_rate'] / 100, $config['global_cost_decimal_points']);
						$pi['item_allocation_info'][$tmp_po_bid]['item_gst_amt'] = round($unit_gst_amount * ($tmp_branch_qty+$tmp_branch_foc_qty), 2);
					}
					$pi['item_allocation_info'][$tmp_po_bid]['item_amt_incl_gst'] = $pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'] + $pi['item_allocation_info'][$tmp_po_bid]['item_gst_amt'];
					
					$allocation_info[$tmp_po_bid]['subtotal_po_gross_amount'] += $pi['item_allocation_info'][$tmp_po_bid]['item_gross_amt'];
					$allocation_info[$tmp_po_bid]['subtotal_po_nett_amount'] += $pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'];
					$allocation_info[$tmp_po_bid]['subtotal_po_gst_amount'] += $pi['item_allocation_info'][$tmp_po_bid]['item_gst_amt'];
					$allocation_info[$tmp_po_bid]['subtotal_po_amount_incl_gst'] += $pi['item_allocation_info'][$tmp_po_bid]['item_amt_incl_gst'];
					$allocation_info[$tmp_po_bid]['total_selling_amt'] += $pi['item_allocation_info'][$tmp_po_bid]['item_total_selling'];
					$allocation_info[$tmp_po_bid]['total_gst_selling_amt'] += $pi['item_allocation_info'][$tmp_po_bid]['item_total_gst_selling'];
			
					foreach($strval_po_items_fields as $field){
						$pi['item_allocation_info'][$tmp_po_bid][$field] = strval(round($pi['item_allocation_info'][$tmp_po_bid][$field], 2));
					}
					$pi_list[$po_item_id] = $pi;
					
				}
				
				$allocation_info[$tmp_po_bid]['supplier_po_amt'] = $allocation_info[$tmp_po_bid]['po_amount'] = $allocation_info[$tmp_po_bid]['subtotal_po_nett_amount'];
				$allocation_info[$tmp_po_bid]['supplier_po_gst_amt'] = $allocation_info[$tmp_po_bid]['po_gst_amount'] = $allocation_info[$tmp_po_bid]['subtotal_po_gst_amount'];
				$allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'] = $allocation_info[$tmp_po_bid]['po_amount_incl_gst'] = $allocation_info[$tmp_po_bid]['subtotal_po_amount_incl_gst'];
				
				// divide branch total with all total, to find out the ratio
				$allocation_info[$tmp_po_bid]['ratio'] = $ratio = $allocation_info[$tmp_po_bid]['subtotal_po_nett_amount'] / $subtotal_po_nett_amount;
				
				// Misc Cost
				if($po['misc_cost']){
					$allocation_info[$tmp_po_bid]['misc_cost_amt'] = $misc_cost_amt * $ratio;
					$allocation_info[$tmp_po_bid]['gst_misc_cost_amt'] = $gst_misc_cost_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['po_amount'] += $allocation_info[$tmp_po_bid]['misc_cost_amt'];
					$allocation_info[$tmp_po_bid]['po_amount_incl_gst'] += $allocation_info[$tmp_po_bid]['gst_misc_cost_amt'];
					
					$allocation_info[$tmp_po_bid]['supplier_misc_cost_amt'] = $supplier_misc_cost_amt * $ratio;
					$allocation_info[$tmp_po_bid]['supplier_gst_misc_cost_amt'] = $supplier_gst_misc_cost_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['supplier_po_amt'] += $allocation_info[$tmp_po_bid]['supplier_misc_cost_amt'];
					$allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'] += $allocation_info[$tmp_po_bid]['supplier_gst_misc_cost_amt'];
				}
				
				// Discount
				if($po['sdiscount']){
					$allocation_info[$tmp_po_bid]['sdiscount_amt'] = $sdiscount_amt * $ratio;
					$allocation_info[$tmp_po_bid]['gst_sdiscount_amt'] = $gst_sdiscount_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['po_amount'] -= $allocation_info[$tmp_po_bid]['sdiscount_amt'];
					$allocation_info[$tmp_po_bid]['po_amount_incl_gst'] -= $allocation_info[$tmp_po_bid]['gst_sdiscount_amt'];
					
					$allocation_info[$tmp_po_bid]['supplier_sdiscount_amt'] = $supplier_sdiscount_amt * $ratio;
					$allocation_info[$tmp_po_bid]['supplier_gst_sdiscount_amt'] = $supplier_gst_sdiscount_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['supplier_po_amt'] -= $allocation_info[$tmp_po_bid]['supplier_sdiscount_amt'];
					$allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'] -= $allocation_info[$tmp_po_bid]['supplier_gst_sdiscount_amt'];
				}
				
				// Discount from Remark#2
				if($po['rdiscount']){
					$allocation_info[$tmp_po_bid]['rdiscount_amt'] = $rdiscount_amt * $ratio;
					$allocation_info[$tmp_po_bid]['gst_rdiscount_amt'] = $gst_rdiscount_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['po_amount'] -= $allocation_info[$tmp_po_bid]['rdiscount_amt'];
					$allocation_info[$tmp_po_bid]['po_amount_incl_gst'] -= $allocation_info[$tmp_po_bid]['gst_rdiscount_amt'];
				}
				
				// Deduct Cost from Remark#2
				if($po['ddiscount']){
					$allocation_info[$tmp_po_bid]['ddiscount_amt'] = $ddiscount_amt * $ratio;
					$allocation_info[$tmp_po_bid]['gst_ddiscount_amt'] = $gst_ddiscount_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['po_amount'] -= $allocation_info[$tmp_po_bid]['ddiscount_amt'];
					$allocation_info[$tmp_po_bid]['po_amount_incl_gst'] -= $allocation_info[$tmp_po_bid]['gst_ddiscount_amt'];
				}
				
				// Transportation Charges
				if($po['transport_cost']){
					$allocation_info[$tmp_po_bid]['transport_cost_amt'] = $transport_cost_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['po_amount'] += $allocation_info[$tmp_po_bid]['transport_cost_amt'];
					$allocation_info[$tmp_po_bid]['po_amount_incl_gst'] += $allocation_info[$tmp_po_bid]['transport_cost_amt'];
					
					$allocation_info[$tmp_po_bid]['supplier_po_amt'] += $allocation_info[$tmp_po_bid]['transport_cost_amt'];
					$allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'] += $allocation_info[$tmp_po_bid]['transport_cost_amt'];
				}
				
				$allocation_info[$tmp_po_bid]['po_amount'] = round($allocation_info[$tmp_po_bid]['po_amount'], 2);
				$allocation_info[$tmp_po_bid]['po_amount_incl_gst'] = round($allocation_info[$tmp_po_bid]['po_amount_incl_gst'], 2);
				$allocation_info[$tmp_po_bid]['supplier_po_amt'] = round($allocation_info[$tmp_po_bid]['supplier_po_amt'], 2);
				$allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'] = round($allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'], 2);
				
				
				// Total GST
				$allocation_info[$tmp_po_bid]['po_gst_amount'] = (round($allocation_info[$tmp_po_bid]['po_amount_incl_gst'] - $allocation_info[$tmp_po_bid]['po_amount'], 2));
				$allocation_info[$tmp_po_bid]['supplier_po_gst_amt'] = (round($allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'] - $allocation_info[$tmp_po_bid]['supplier_po_amt'], 2));
		
				foreach($strval_po_fields as $field){
					$allocation_info[$tmp_po_bid][$field] = strval(round($allocation_info[$tmp_po_bid][$field], 2));
				}
				
			}
		}
		
		$po_upd = array();
		$po_upd['subtotal_po_gross_amount'] = $subtotal_po_gross_amount;
		$po_upd['subtotal_po_nett_amount'] = $subtotal_po_nett_amount;
		$po_upd['subtotal_po_gst_amount'] = $subtotal_po_gst_amount;
		$po_upd['subtotal_po_amount_incl_gst'] = $subtotal_po_amount_incl_gst;
		$po_upd['po_amount'] = $po_amount;
		$po_upd['po_gst_amount'] = $po_gst_amount;
		$po_upd['po_amount_incl_gst'] = $po_amount_incl_gst;
		$po_upd['misc_cost_amt'] = $misc_cost_amt;
		$po_upd['gst_misc_cost_amt'] = $gst_misc_cost_amt;
		$po_upd['sdiscount_amt'] = $sdiscount_amt;
		$po_upd['gst_sdiscount_amt'] = $gst_sdiscount_amt;
		$po_upd['rdiscount_amt'] = $rdiscount_amt;
		$po_upd['gst_rdiscount_amt'] = $gst_rdiscount_amt;
		$po_upd['ddiscount_amt'] = $ddiscount_amt;
		$po_upd['gst_ddiscount_amt'] = $gst_ddiscount_amt;
		$po_upd['transport_cost_amt'] = $transport_cost_amt;
		$po_upd['total_selling_amt'] = $total_selling_amt;
		$po_upd['total_gst_selling_amt'] = $total_gst_selling_amt;
		$po_upd['supplier_po_amt'] = $supplier_po_amt;
		$po_upd['supplier_po_amt_incl_gst'] = $supplier_po_amt_incl_gst;
		$po_upd['old_po_amt_updated'] = 1;
		$po_upd['allocation_info'] = $allocation_info;
		
		
		foreach($pi_list as $po_item_id => $pi){
			$upd = array();
			$upd['tax_amt'] = $pi['tax_amt'];
			$upd['discount_amt'] = $pi['discount_amt'];
			$upd['item_gross_amt'] = $pi['item_gross_amt'];
			$upd['item_nett_amt'] = $pi['item_nett_amt'];
			$upd['item_gst_amt'] = $pi['item_gst_amt'];
			$upd['item_amt_incl_gst'] = $pi['item_amt_incl_gst'];
			$upd['item_total_selling'] = $pi['item_total_selling'];
			$upd['item_total_gst_selling'] = $pi['item_total_gst_selling'];
			$upd['item_allocation_info'] = $pi['item_allocation_info'];
			
			
			//print_r($upd);
			$upd['item_allocation_info'] = strval(serialize($upd['item_allocation_info']));
			
			$con->sql_query("update po_items set ".mysql_update_by_field($upd)." where branch_id=$bid and po_id=$poID and id=$po_item_id");
		}
		//print_r($po_upd);
		$po_upd['allocation_info'] = serialize($po_upd['allocation_info']);
		$con->sql_query("update po set ".mysql_update_by_field($po_upd).",last_update=last_update where branch_id=$bid and id=$poID");
		
		/*print "<pre>";
		print_r($pi_list);
		print "</pre>";
		print "<br>";
		print "subtotal_po_gross_amount = $subtotal_po_gross_amount<br>";
		print "subtotal_po_nett_amount = $subtotal_po_nett_amount<br>";
		print "subtotal_po_gst_amount = $subtotal_po_gst_amount<br>";
		print "subtotal_po_amount_incl_gst = $subtotal_po_amount_incl_gst<br>";
		print "total_selling_amt = $total_selling_amt<br>";
		print "total_gst_selling_amt = $total_gst_selling_amt<br>";
		print "subtotal_gp_amt = $subtotal_gp_amt<br>";
		print "subtotal_gp_per = $subtotal_gp_per<br>";
		print "misc_cost_amt = $misc_cost_amt<br>";
		print "sdiscount_amt = $sdiscount_amt<br>";
		print "rdiscount_amt = $rdiscount_amt<br>";
		print "ddiscount_amt = $ddiscount_amt<br>";
		print "transport_cost_amt = $transport_cost_amt<br>";
		print "po_amount = $po_amount<br>";
		print "total_gp_amt = $total_gp_amt<br>";
		print "total_gp_per = $total_gp_per<br>";
		print "po_gst_amount = $po_gst_amount<br>";
		print "po_amount_incl_gst = $po_amount_incl_gst<br>";
		print "total_gst_gp_amt = $total_gst_gp_amt<br>";
		print "total_gst_gp_per = $total_gst_gp_per<br>";
		print "supplier_po_amt = $supplier_po_amt<br>";
		print "supplier_po_amt_incl_gst = $supplier_po_amt_incl_gst<br>";*/
	}
	
	// function to recalulcate PO all Amount using old method
	// return null
	public function reCalcatePOAmt($bid, $poID){
		global $config, $con;
		
		$bid = mi($bid);
		$poID = mi($poID);
		
		if(!$bid || !$poID)	die("Invliad Branch ID / PO ID");
		
		//print "Recalculate PO $bid, $poID";
		
		// select header
		$con->sql_query("select * from po where branch_id=$bid and id=$poID");
		$po = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$po)	die("PO Branch ID: $bid, ID: $poID Not Found");
		
		$subtotal_po_gross_amount = 0;
		$subtotal_po_nett_amount = 0;
		$subtotal_po_gst_amount = 0;
		$subtotal_po_amount_incl_gst = 0;
		$po_amount = 0;
		$po_gst_amount = 0;
		$po_amount_incl_gst = 0;
		$misc_cost_amt = 0;
		$sdiscount_amt = 0;
		$rdiscount_amt = 0;
		$ddiscount_amt = 0;
		$transport_cost_amt = 0;
		$total_selling_amt = 0;
		$total_gst_selling_amt = 0;
		$supplier_po_amt = 0;
		$supplier_po_gst_amt = 0;
		$supplier_po_amt_incl_gst = 0;
		
		/// field not store in po ///////
		$supplier_misc_cost_amt = 0;
		$supplier_gst_misc_cost_amt = 0;
		$supplier_sdiscount_amt = 0;
		$supplier_gst_sdiscount_amt = 0;
		$gst_misc_cost_amt = 0;
		$gst_sdiscount_amt = 0;
		$gst_rdiscount_amt = 0;
		$gst_ddiscount_amt = 0;
		$subtotal_gp_amt = 0;
		$total_gp_amt = 0;
		$total_gst_gp_amt = 0;
		/////////////////////////////////
		
		$strval_po_fields = array('subtotal_po_gross_amount','subtotal_po_nett_amount','subtotal_po_gst_amount','subtotal_po_amount_incl_gst','po_gst_amount','po_amount_incl_gst','po_amount','misc_cost_amt','sdiscount_amt','rdiscount_amt','ddiscount_amt','transport_cost_amt','total_selling_amt','total_gst_selling_amt','supplier_po_amt','supplier_po_amt_incl_gst','gst_misc_cost_amt','supplier_misc_cost_amt','supplier_gst_misc_cost_amt','gst_sdiscount_amt','supplier_sdiscount_amt','supplier_gst_sdiscount_amt','gst_rdiscount_amt');
		$strval_po_items_fields = array('tax_amt','discount_amt','item_gross_amt','item_nett_amt','item_gst_amt','item_amt_incl_gst','item_total_selling','item_total_gst_selling');
		
		$po['deliver_to'] = unserialize($po['deliver_to']);
		
		$po['sdiscount']=unserialize($po['sdiscount']);
		if(is_array($po['sdiscount']))	$po['sdiscount']=$po['sdiscount'][0];
		
		$po['rdiscount']=unserialize($po['rdiscount']);
		if(is_array($po['rdiscount']))	$po['rdiscount']=$po['rdiscount'][0];
		
		$po['ddiscount']=unserialize($po['ddiscount']);
		if(is_array($po['ddiscount']))	$po['ddiscount']=$po['ddiscount'][0];
		
		$po['misc_cost']=unserialize($po['misc_cost']);
		if(is_array($po['misc_cost']))	$po['misc_cost']=$po['misc_cost'][0];

		$po['transport_cost']=unserialize($po['transport_cost']);
		if(is_array($po['transport_cost']))	$po['transport_cost']=$po['transport_cost'][0];
	
		//print_r($po);
		
		// select items
		$pi_upd = array();
		$q1 = $con->sql_query("select * from po_items where branch_id=$bid and po_id=$poID order by id");
		$pi_list = array();
		while($pi = $con->sql_fetchassoc($q1)){
			$line_qty = 0;
			$line_foc_qty = 0;
			$item_total_selling = 0;
			$item_total_gst_selling = 0;
			
			if($po['deliver_to'] && is_array($po['deliver_to'])){
				$pi['qty_allocation'] = unserialize($pi['qty_allocation']);
				$pi['qty_loose_allocation'] = unserialize($pi['qty_loose_allocation']);
				$pi['foc_allocation'] = unserialize($pi['foc_allocation']);
				$pi['foc_loose_allocation'] = unserialize($pi['foc_loose_allocation']);
				$pi['selling_price_allocation'] = unserialize($pi['selling_price_allocation']);
				$pi['gst_selling_price_allocation'] = unserialize($pi['gst_selling_price_allocation']);
				
				// multi branch
				foreach($po['deliver_to'] as $tmp_po_bid){
					$tmp_branch_qty = $pi['qty_allocation'][$tmp_po_bid] * $pi['order_uom_fraction'] + $pi['qty_loose_allocation'][$tmp_po_bid];
					$tmp_branch_foc_qty = $pi['foc_allocation'][$tmp_po_bid] * $pi['order_uom_fraction'] + $pi['foc_loose_allocation'][$tmp_po_bid];
					
					$line_qty += $tmp_branch_qty;
					$line_foc_qty += $tmp_branch_foc_qty;
					
					$line_selling_amt = round($pi['selling_price_allocation'][$tmp_po_bid] * ($tmp_branch_qty + $tmp_branch_foc_qty), 2);
					if($pi['selling_gst_id'] && $pi['gst_selling_price_allocation'][$tmp_po_bid]){
						$line_gst_selling_amt = round($pi['gst_selling_price_allocation'][$tmp_po_bid] * ($tmp_branch_qty + $tmp_branch_foc_qty), 2);
					}
					$item_total_selling += $line_selling_amt;
					$item_total_gst_selling += $line_gst_selling_amt;
				}
			}else{
				// single branch
				$line_qty = $pi['qty'] * $pi['order_uom_fraction'] + $pi['qty_loose'];
				$line_foc_qty = $pi['foc'] * $pi['order_uom_fraction'] + $pi['foc_loose'];
				
				// Selling
				$item_total_selling = round($pi['selling_price'] * ($line_qty + $line_foc_qty),2);
				
				// Selling Inclusive GST
				if($pi['selling_gst_id'] && $pi['gst_selling_price']){
					$item_total_gst_selling = round($pi['gst_selling_price'] * ($line_qty + $line_foc_qty),2);
				}
			}
			
			// Gross Amount
			$item_gross_amt = ($pi['order_price'] / $pi['order_uom_fraction']) * $line_qty;
			$item_nett_amt = $item_gross_amt;
			
			// Tax Amount
			$tax_amt = 0;
			if($pi['tax']){
				$tax_amt = mf($pi['tax']) / 100 * $item_nett_amt;
				$item_nett_amt += $tax_amt;
			}
			
			// Discount Amount
			$discount_amt = 0;
			if($pi['discount']){
				$camt = $item_nett_amt;
				$item_nett_amt = parse_formula($item_nett_amt, $pi['discount']);
				$discount_amt = round($camt - $item_nett_amt, 2);
			}
			
			$item_nett_amt = round($item_nett_amt, 2);
			
			$item_gst_amt = 0;
			// calculate gst
			if($po['is_under_gst']){
				//$avg_order_price = round($item_nett_amt / ($line_qty + $line_foc_qty), $config['global_cost_decimal_points']);
				//$unit_gst_amount = round($avg_order_price * $pi['cost_gst_rate'] / 100, $config['global_cost_decimal_points']);
				//$item_gst_amt = round($unit_gst_amount * ($line_qty + $line_foc_qty), 2);
				
				$item_gst_amt = round($item_nett_amt * $pi['cost_gst_rate'] / 100, 2);
			}
			$item_amt_incl_gst = $item_nett_amt + $item_gst_amt;
			
			
			$item_gp_amt = round($item_total_selling - $item_nett_amt, 2);
			$item_gp_per = round($item_gp_amt / $item_total_selling * 100, 2);
			
			//print "<br>";
			//print "item_gross_amt = $item_gross_amt, tax_amt = $tax_amt, discount_amt = $discount_amt, item_nett_amt = $item_nett_amt, unit_gst_amount = $unit_gst_amount, item_gst_amt = $item_gst_amt, item_amt_incl_gst = $item_amt_incl_gst, item_total_selling = $item_total_selling, item_gp_amt = $item_gp_amt, item_gp_per = $item_gp_per, item_total_gst_selling = $item_total_gst_selling";
			
			$subtotal_po_gross_amount += $item_gross_amt;
			$subtotal_po_nett_amount += $item_nett_amt;
			$subtotal_po_gst_amount += $item_gst_amt;
			$subtotal_po_amount_incl_gst += $item_amt_incl_gst;
			$total_selling_amt += $item_total_selling;
			$total_gst_selling_amt += $item_total_gst_selling;
			
			$upd = array();
			$pi['tax_amt'] = $tax_amt;
			$pi['discount_amt'] = $discount_amt;
			$pi['item_gross_amt'] = $item_gross_amt;
			$pi['item_nett_amt'] = $item_nett_amt;
			$pi['item_gst_amt'] = $item_gst_amt;
			$pi['item_amt_incl_gst'] = $item_amt_incl_gst;
			$pi['item_total_selling'] = $item_total_selling;
			$pi['item_total_gst_selling'] = $item_total_gst_selling;
			$pi['item_allocation_info'] = array();

			$pi_list[$pi['id']] = $pi;
		}
		$con->sql_freeresult($q1);
		
		$subtotal_gp_amt = round($total_selling_amt - $subtotal_po_nett_amount, 2);
		$subtotal_gp_per = $subtotal_gp_amt / $total_selling_amt * 100;
		
		$supplier_po_amt = $po_amount = $subtotal_po_nett_amount;
		$supplier_po_gst_amt = $po_gst_amount = $subtotal_po_gst_amount;
		$supplier_po_amt_incl_gst = $po_amount_incl_gst = $subtotal_po_amount_incl_gst;
		
		
		$total_gp_amt = 0;
		$total_gst_gp_amt = 0;
		
		// Misc Cost
		if($po['misc_cost']){
			$po_amount = parse_formula($po_amount, $po['misc_cost'],true, 1, $misc_cost_amt);
			$po_amount_incl_gst = parse_formula($po_amount_incl_gst, $po['misc_cost'],true, 1, $gst_misc_cost_amt);
			
			$supplier_po_amt = parse_formula($supplier_po_amt, $po['misc_cost'],true, 1, $supplier_misc_cost_amt);
			$supplier_po_amt_incl_gst = parse_formula($supplier_po_amt_incl_gst, $po['misc_cost'],true, 1, $supplier_gst_misc_cost_amt);
		}
		
		// Discount
		if($po['sdiscount']){
			$po_amount = parse_formula($po_amount, $po['sdiscount'], false, 1, $sdiscount_amt);
			$po_amount_incl_gst = parse_formula($po_amount_incl_gst, $po['sdiscount'], false, 1, $gst_sdiscount_amt);
			$sdiscount_amt *= -1;
			$gst_sdiscount_amt *= -1;
			
			$supplier_po_amt = parse_formula($supplier_po_amt, $po['sdiscount'], false, 1, $supplier_sdiscount_amt);
			$supplier_po_amt_incl_gst = parse_formula($supplier_po_amt_incl_gst, $po['sdiscount'], false, 1, $supplier_gst_sdiscount_amt);
			$supplier_sdiscount_amt *= -1;
			$supplier_gst_sdiscount_amt *= -1;
		}
		
		// Discount from Remark#2
		if($po['rdiscount']){
			$po_amount = parse_formula($po_amount, $po['rdiscount'], false, 1, $rdiscount_amt);
			$po_amount_incl_gst = parse_formula($po_amount_incl_gst, $po['rdiscount'], false, 1, $gst_rdiscount_amt);
			$rdiscount_amt *= -1;
			$gst_rdiscount_amt *= -1;
		}
		
		// Deduct Cost from Remark#2
		if($po['ddiscount']){
			$po_amount = parse_formula($po_amount, $po['ddiscount'], false, 1, $ddiscount_amt);
			$po_amount_incl_gst = parse_formula($po_amount_incl_gst, $po['ddiscount'], false, 1, $gst_ddiscount_amt);
			$ddiscount_amt *= -1;
			$gst_ddiscount_amt *= -1;
		}
		
		// Transportation Charges
		if($po['transport_cost']){
			$transport_cost_amt = mf($po['transport_cost']);
			$po_amount += $transport_cost_amt;
			$po_amount_incl_gst += $transport_cost_amt;
			
			$supplier_po_amt += $transport_cost_amt;
			$supplier_po_amt_incl_gst += $transport_cost_amt;
		}
		
		$po_amount = round($po_amount, 2);
		$po_amount_incl_gst = round($po_amount_incl_gst, 2);
		$supplier_po_amt = round($supplier_po_amt, 2);
		$supplier_po_amt_incl_gst = round($supplier_po_amt_incl_gst, 2);
		
		
		// Total GST
		$po_gst_amount = $po_amount_incl_gst - $po_amount;
		$supplier_po_gst_amt = $supplier_po_amt_incl_gst - $supplier_po_amt;
		
		// Total GP
		$total_gp_amt = round($total_selling_amt - $po_amount, 2);
		$total_gp_per = $total_selling_amt? $total_gp_amt / $total_selling_amt * 100 : 0;
		
		// Total GP Include GST
		$total_gst_gp_amt = round($total_gst_selling_amt - $po_amount_incl_gst, 2);
		$total_gst_gp_per = $total_gst_selling_amt ? $total_gst_gp_amt / $total_gst_selling_amt * 100 : 0;
		
		// branch allocation
		$allocation_info = array();
		if($po['deliver_to'] && is_array($po['deliver_to'])){
			// loop branch
			foreach($po['deliver_to'] as $tmp_po_bid){
				// loop po_items
				foreach($pi_list as $po_item_id => $pi){
					$tmp_branch_qty = $pi['qty_allocation'][$tmp_po_bid] * $pi['order_uom_fraction'] + $pi['qty_loose_allocation'][$tmp_po_bid];
					$tmp_branch_foc_qty = $pi['foc_allocation'][$tmp_po_bid] * $pi['order_uom_fraction'] + $pi['foc_loose_allocation'][$tmp_po_bid];
					
					// allocation info
					
					// Selling
					$pi['item_allocation_info'][$tmp_po_bid]['item_total_selling'] = round($pi['selling_price_allocation'][$tmp_po_bid] * ($tmp_branch_qty + $tmp_branch_foc_qty), 2);
					if($pi['selling_gst_id'] && $pi['gst_selling_price_allocation'][$tmp_po_bid]){
						$pi['item_allocation_info'][$tmp_po_bid]['item_total_gst_selling'] = round($pi['gst_selling_price_allocation'][$tmp_po_bid] * ($tmp_branch_qty + $tmp_branch_foc_qty), 2);
					}
					
					
					// Gross Amount
					$pi['item_allocation_info'][$tmp_po_bid]['item_gross_amt'] = ($pi['order_price'] / $pi['order_uom_fraction']) * $tmp_branch_qty;
					$pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'] = $pi['item_allocation_info'][$tmp_po_bid]['item_gross_amt'];
					
					
					
					// Tax Amount
					$pi['item_allocation_info'][$tmp_po_bid]['tax_amt'] = 0;
					if($pi['tax']){
						$pi['item_allocation_info'][$tmp_po_bid]['tax_amt'] = mf($pi['tax']) / 100 * $pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'];
						$pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'] += $pi['item_allocation_info'][$tmp_po_bid]['tax_amt'];
					}
					
					// Discount Amount
					if ($pi['discount_amt']){
						$pi['item_allocation_info'][$tmp_po_bid]['discount_amt'] = $pi['discount_amt']*($pi['item_allocation_info'][$tmp_po_bid]['item_gross_amt']/$pi['item_gross_amt']);
						$pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'] -= $pi['item_allocation_info'][$tmp_po_bid]['discount_amt'];
					}
					$pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'] = round($pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'], 2);
					
					$pi['item_allocation_info'][$tmp_po_bid]['item_gst_amt'] = 0;
					
					// calculate gst
					if($po['is_under_gst']){
						$pi['item_allocation_info'][$tmp_po_bid]['item_gst_amt'] = round($pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'] * $pi['cost_gst_rate'] / 100, 2);
					}
					$pi['item_allocation_info'][$tmp_po_bid]['item_amt_incl_gst'] = $pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'] + $pi['item_allocation_info'][$tmp_po_bid]['item_gst_amt'];
					
					$allocation_info[$tmp_po_bid]['subtotal_po_gross_amount'] += $pi['item_allocation_info'][$tmp_po_bid]['item_gross_amt'];
					$allocation_info[$tmp_po_bid]['subtotal_po_nett_amount'] += $pi['item_allocation_info'][$tmp_po_bid]['item_nett_amt'];
					$allocation_info[$tmp_po_bid]['subtotal_po_gst_amount'] += $pi['item_allocation_info'][$tmp_po_bid]['item_gst_amt'];
					$allocation_info[$tmp_po_bid]['subtotal_po_amount_incl_gst'] += $pi['item_allocation_info'][$tmp_po_bid]['item_amt_incl_gst'];
					$allocation_info[$tmp_po_bid]['total_selling_amt'] += $pi['item_allocation_info'][$tmp_po_bid]['item_total_selling'];
					$allocation_info[$tmp_po_bid]['total_gst_selling_amt'] += $pi['item_allocation_info'][$tmp_po_bid]['item_total_gst_selling'];
			
					foreach($strval_po_items_fields as $field){
						$pi['item_allocation_info'][$tmp_po_bid][$field] = strval(round($pi['item_allocation_info'][$tmp_po_bid][$field], 2));
					}
					$pi_list[$po_item_id] = $pi;
					
				}
				
				$allocation_info[$tmp_po_bid]['supplier_po_amt'] = $allocation_info[$tmp_po_bid]['po_amount'] = $allocation_info[$tmp_po_bid]['subtotal_po_nett_amount'];
				$allocation_info[$tmp_po_bid]['supplier_po_gst_amt'] = $allocation_info[$tmp_po_bid]['po_gst_amount'] = $allocation_info[$tmp_po_bid]['subtotal_po_gst_amount'];
				$allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'] = $allocation_info[$tmp_po_bid]['po_amount_incl_gst'] = $allocation_info[$tmp_po_bid]['subtotal_po_amount_incl_gst'];
				
				// divide branch total with all total, to find out the ratio
				$allocation_info[$tmp_po_bid]['ratio'] = $ratio = $allocation_info[$tmp_po_bid]['subtotal_po_nett_amount'] / $subtotal_po_nett_amount;
				
				// Misc Cost
				if($po['misc_cost']){
					$allocation_info[$tmp_po_bid]['misc_cost_amt'] = $misc_cost_amt * $ratio;
					$allocation_info[$tmp_po_bid]['gst_misc_cost_amt'] = $gst_misc_cost_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['po_amount'] += $allocation_info[$tmp_po_bid]['misc_cost_amt'];
					$allocation_info[$tmp_po_bid]['po_amount_incl_gst'] += $allocation_info[$tmp_po_bid]['gst_misc_cost_amt'];
					
					$allocation_info[$tmp_po_bid]['supplier_misc_cost_amt'] = $supplier_misc_cost_amt * $ratio;
					$allocation_info[$tmp_po_bid]['supplier_gst_misc_cost_amt'] = $supplier_gst_misc_cost_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['supplier_po_amt'] += $allocation_info[$tmp_po_bid]['supplier_misc_cost_amt'];
					$allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'] += $allocation_info[$tmp_po_bid]['supplier_gst_misc_cost_amt'];
				}
				
				// Discount
				if($po['sdiscount']){
					$allocation_info[$tmp_po_bid]['sdiscount_amt'] = $sdiscount_amt * $ratio;
					$allocation_info[$tmp_po_bid]['gst_sdiscount_amt'] = $gst_sdiscount_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['po_amount'] -= $allocation_info[$tmp_po_bid]['sdiscount_amt'];
					$allocation_info[$tmp_po_bid]['po_amount_incl_gst'] -= $allocation_info[$tmp_po_bid]['gst_sdiscount_amt'];
					
					$allocation_info[$tmp_po_bid]['supplier_sdiscount_amt'] = $supplier_sdiscount_amt * $ratio;
					$allocation_info[$tmp_po_bid]['supplier_gst_sdiscount_amt'] = $supplier_gst_sdiscount_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['supplier_po_amt'] -= $allocation_info[$tmp_po_bid]['supplier_sdiscount_amt'];
					$allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'] -= $allocation_info[$tmp_po_bid]['supplier_gst_sdiscount_amt'];
				}
				
				// Discount from Remark#2
				if($po['rdiscount']){
					$allocation_info[$tmp_po_bid]['rdiscount_amt'] = $rdiscount_amt * $ratio;
					$allocation_info[$tmp_po_bid]['gst_rdiscount_amt'] = $gst_rdiscount_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['po_amount'] -= $allocation_info[$tmp_po_bid]['rdiscount_amt'];
					$allocation_info[$tmp_po_bid]['po_amount_incl_gst'] -= $allocation_info[$tmp_po_bid]['gst_rdiscount_amt'];
				}
				
				// Deduct Cost from Remark#2
				if($po['ddiscount']){
					$allocation_info[$tmp_po_bid]['ddiscount_amt'] = $ddiscount_amt * $ratio;
					$allocation_info[$tmp_po_bid]['gst_ddiscount_amt'] = $gst_ddiscount_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['po_amount'] -= $allocation_info[$tmp_po_bid]['ddiscount_amt'];
					$allocation_info[$tmp_po_bid]['po_amount_incl_gst'] -= $allocation_info[$tmp_po_bid]['gst_ddiscount_amt'];
				}
				
				// Transportation Charges
				if($po['transport_cost']){
					$allocation_info[$tmp_po_bid]['transport_cost_amt'] = $transport_cost_amt * $ratio;
					
					$allocation_info[$tmp_po_bid]['po_amount'] += $allocation_info[$tmp_po_bid]['transport_cost_amt'];
					$allocation_info[$tmp_po_bid]['po_amount_incl_gst'] += $allocation_info[$tmp_po_bid]['transport_cost_amt'];
					
					$allocation_info[$tmp_po_bid]['supplier_po_amt'] += $allocation_info[$tmp_po_bid]['transport_cost_amt'];
					$allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'] += $allocation_info[$tmp_po_bid]['transport_cost_amt'];
				}
				
				$allocation_info[$tmp_po_bid]['po_amount'] = round($allocation_info[$tmp_po_bid]['po_amount'], 2);
				$allocation_info[$tmp_po_bid]['po_amount_incl_gst'] = round($allocation_info[$tmp_po_bid]['po_amount_incl_gst'], 2);
				$allocation_info[$tmp_po_bid]['supplier_po_amt'] = round($allocation_info[$tmp_po_bid]['supplier_po_amt'], 2);
				$allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'] = round($allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'], 2);
				
				
				// Total GST
				$allocation_info[$tmp_po_bid]['po_gst_amount'] = (round($allocation_info[$tmp_po_bid]['po_amount_incl_gst'] - $allocation_info[$tmp_po_bid]['po_amount'], 2));
				$allocation_info[$tmp_po_bid]['supplier_po_gst_amt'] = (round($allocation_info[$tmp_po_bid]['supplier_po_amt_incl_gst'] - $allocation_info[$tmp_po_bid]['supplier_po_amt'], 2));
		
				foreach($strval_po_fields as $field){
					$allocation_info[$tmp_po_bid][$field] = strval(round($allocation_info[$tmp_po_bid][$field], 2));
				}
				
			}
		}
		
		$po_upd = array();
		$po_upd['subtotal_po_gross_amount'] = $subtotal_po_gross_amount;
		$po_upd['subtotal_po_nett_amount'] = $subtotal_po_nett_amount;
		$po_upd['subtotal_po_gst_amount'] = $subtotal_po_gst_amount;
		$po_upd['subtotal_po_amount_incl_gst'] = $subtotal_po_amount_incl_gst;
		$po_upd['po_amount'] = $po_amount;
		$po_upd['po_gst_amount'] = $po_gst_amount;
		$po_upd['po_amount_incl_gst'] = $po_amount_incl_gst;
		$po_upd['misc_cost_amt'] = $misc_cost_amt;
		$po_upd['gst_misc_cost_amt'] = $gst_misc_cost_amt;
		$po_upd['sdiscount_amt'] = $sdiscount_amt;
		$po_upd['gst_sdiscount_amt'] = $gst_sdiscount_amt;
		$po_upd['rdiscount_amt'] = $rdiscount_amt;
		$po_upd['gst_rdiscount_amt'] = $gst_rdiscount_amt;
		$po_upd['ddiscount_amt'] = $ddiscount_amt;
		$po_upd['gst_ddiscount_amt'] = $gst_ddiscount_amt;
		$po_upd['transport_cost_amt'] = $transport_cost_amt;
		$po_upd['total_selling_amt'] = $total_selling_amt;
		$po_upd['total_gst_selling_amt'] = $total_gst_selling_amt;
		$po_upd['supplier_po_amt'] = $supplier_po_amt;
		$po_upd['supplier_po_amt_incl_gst'] = $supplier_po_amt_incl_gst;
		$po_upd['new_po_amt_updated'] = 1;
		$po_upd['allocation_info'] = $allocation_info;
		
		
		foreach($pi_list as $po_item_id => $pi){
			$upd = array();
			$upd['tax_amt'] = $pi['tax_amt'];
			$upd['discount_amt'] = $pi['discount_amt'];
			$upd['item_gross_amt'] = $pi['item_gross_amt'];
			$upd['item_nett_amt'] = $pi['item_nett_amt'];
			$upd['item_gst_amt'] = $pi['item_gst_amt'];
			$upd['item_amt_incl_gst'] = $pi['item_amt_incl_gst'];
			$upd['item_total_selling'] = $pi['item_total_selling'];
			$upd['item_total_gst_selling'] = $pi['item_total_gst_selling'];
			$upd['item_allocation_info'] = $pi['item_allocation_info'];
			
			
			//print_r($upd);
			$upd['item_allocation_info'] = strval(serialize($upd['item_allocation_info']));
			
			$con->sql_query("update po_items set ".mysql_update_by_field($upd)." where branch_id=$bid and po_id=$poID and id=$po_item_id");
		}
		//print_r($po_upd);
		$po_upd['allocation_info'] = serialize($po_upd['allocation_info']);
		$con->sql_query("update po set ".mysql_update_by_field($po_upd).",last_update=last_update where branch_id=$bid and id=$poID");
		
		/*print "<pre>";
		print_r($pi_list);
		print "</pre>";
		print "<br>";
		print "subtotal_po_gross_amount = $subtotal_po_gross_amount<br>";
		print "subtotal_po_nett_amount = $subtotal_po_nett_amount<br>";
		print "subtotal_po_gst_amount = $subtotal_po_gst_amount<br>";
		print "subtotal_po_amount_incl_gst = $subtotal_po_amount_incl_gst<br>";
		print "total_selling_amt = $total_selling_amt<br>";
		print "total_gst_selling_amt = $total_gst_selling_amt<br>";
		print "subtotal_gp_amt = $subtotal_gp_amt<br>";
		print "subtotal_gp_per = $subtotal_gp_per<br>";
		print "misc_cost_amt = $misc_cost_amt<br>";
		print "sdiscount_amt = $sdiscount_amt<br>";
		print "rdiscount_amt = $rdiscount_amt<br>";
		print "ddiscount_amt = $ddiscount_amt<br>";
		print "transport_cost_amt = $transport_cost_amt<br>";
		print "po_amount = $po_amount<br>";
		print "total_gp_amt = $total_gp_amt<br>";
		print "total_gp_per = $total_gp_per<br>";
		print "po_gst_amount = $po_gst_amount<br>";
		print "po_amount_incl_gst = $po_amount_incl_gst<br>";
		print "total_gst_gp_amt = $total_gst_gp_amt<br>";
		print "total_gst_gp_per = $total_gst_gp_per<br>";
		print "supplier_po_amt = $supplier_po_amt<br>";
		print "supplier_po_amt_incl_gst = $supplier_po_amt_incl_gst<br>";*/
	}
	
	// function to check whether this po department got approval flow
	// return boolean
	public function gotApprovalFlow($bid, $dept_id){
		global $con;
		
		$bid = mi($bid);
		$dept_id = mi($dept_id);
		
		$filter = array();
		$filter[] = "branch_id=$bid";
		$filter[] = "sku_category_id=$dept_id";
		$filter[] = "type='PURCHASE_ORDER' and active=1";
		$filter = join(' and ', $filter);
		
		$con->sql_query("select * from approval_flow where $filter");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $tmp ? true : false;
	}
	
	// function to load currency code listing for PO
	// return array
	public function loadPOCurrencyCodeList($po, $params){
		global $config, $smarty, $appCore;
		
		if($params['smarty_assign'])	$smarty_assign = trim($params['smarty_assign']);
		
		// got turn on currency
		if($config['foreign_currency']){
			// Get Foreign Currency Code Array
			$foreignCurrencyCodeList = $appCore->currencyManager->getCurrencyCodes();
			
			// If PO using the Foreign Currency which now already inactive, need to append into array
			if($po['foreign_currency_code'] && !isset($foreignCurrencyCodeList[$po['foreign_currency_code']])){
				$foreignCurrencyCodeList[$po['foreign_currency_code']] = $po['foreign_currency_code'];
			}
		}
		
		if(isset($smarty) && $smarty && $smarty_assign){
			$smarty->assign('foreignCurrencyCodeList', $foreignCurrencyCodeList);
		}
		return $foreignCurrencyCodeList;
	}
	
	// function to send notification pm to related user about PO Currency Rate Changed
	// return array
	public function sendCurrencyRateChangedNotification($bid, $poID, $rateHistoryID){
		global $con, $LANG, $appCore;
		
		$bid = mi($bid);
		$poID = mi($poID);
		$rateHistoryID = mi($rateHistoryID);
		
		if(!$bid)	return array('err'=>sprintf($LANG['INVALID_BRANCH_ID'], $bid));
		if(!$poID)	return array('err'=>sprintf($LANG['PO_NOT_FOUND'], $poID));
		
		// Get PO
		$con->sql_query("select user_id, currency_code, approval_history_id from po where branch_id=$bid and id=$poID");
		$po = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$po)	return array('err'=>sprintf($LANG['PO_NOT_FOUND'], $poID));
		
		// Get Currency Rate History
		$filter = array();
		$filter[] = "pcrh.branch_id=$bid and po_id=$poID";
		
		if($rateHistoryID){
			$filter[] = "pcrh.id=$rateHistoryID";
		}
		$filter = "where ".join(' and ', $filter);
		
		$con->sql_query("select pcrh.*, u1.u as by_u, u2.u as override_by_u
			from po_currency_rate_history pcrh
			left join user u1 on u1.id=pcrh.user_id
			left join user u2 on u2.id=pcrh.override_by_user_id
			$filter order by pcrh.id desc limit 1");
		$rate_history = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$rate_history)	return array('err'=>$LANG['PO_NO_CURRENCY_RATE_HISTORY']);
		
		// Construct Message
		$poOwnerID = mi($po['user_id']);
		
		$url = "po.php?a=view&id=$poID&branch_id=$bid";
		$title = "PO (ID#$poID) [".$po['currency_code']."] Currency Rate change from [".$rate_history['old_rate']."] to [".$rate_history['new_rate']."]";
		
		$title .= " by ".$rate_history['by_u'];
		
		if($rate_history['override_by_u'] && $rate_history['override_by_u'] != $rate_history['by_u']){
			$title .= " (Override by ".$rate_history['override_by_u'].")";
		}
		
		// Get Users to Send
		$to = array();
		
		$approval_history_id = mi($po['approval_history_id']);
		if($approval_history_id){
			// Get Approval History
			$con->sql_query("select * from branch_approval_history where id = $approval_history_id and branch_id = $bid");
			$bah = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($bah){
				$bah['approval_settings'] = unserialize($bah['approval_settings']);
				
				// Notify Users
				$notify_users = $bah['notify_users'];
				$notify_users = preg_split("/\|/", $notify_users);
				if($notify_users){
					foreach($notify_users as $tmp_user_id){
						if($tmp_user_id){
							$tmp = array();
							$tmp['user_id'] = $tmp_user_id;
							$tmp['approval_settings'] = $bah['approval_settings']['notify'][$tmp_user_id];
							$tmp['type'] = 'notify';
							$to[$tmp_user_id] = $tmp;
						}
					}
				}

				// Approval Users
				$approvals = $bah['flow_approvals'];
				$approvals = preg_split("/\|/", $approvals);
				if($approvals){
					foreach($approvals as $tmp_user_id){
						if($tmp_user_id){
							$tmp = array();
							$tmp['user_id'] = $tmp_user_id;
							$tmp['approval_settings'] = $bah['approval_settings']['approval'][$tmp_user_id];	
							$tmp['type'] = 'approval';	
							$to[$tmp_user_id] = $tmp;
						}
					}
				}
				
				// Owner
				$tmp = array();
				$tmp['user_id'] = $poOwnerID;
				$tmp['approval_settings'] = $bah['approval_settings']['owner'];
				$tmp['type'] = 'owner';
				$to[$poOwnerID] = $tmp;
			}
		}
		
		if($to){
			print_r($to);
			send_pm2($to, $title, $url);
		}
		
		return array('ok'=>1);
	}
	
	// function to load PO Change Currency Rate History
	// return array or false
	public function loadPOCurrencyRateHistory($bid, $poID){
		global $con;
		
		$bid = mi($bid);
		$poID = mi($poID);
		
		// Get Currency Rate History
		$filter = array();
		$filter[] = "pcrh.branch_id=$bid and po_id=$poID";
		
		$filter = "where ".join(' and ', $filter);
		
		$con->sql_query("select pcrh.*, u1.u as by_u, u2.u as override_by_u
			from po_currency_rate_history pcrh
			left join user u1 on u1.id=pcrh.user_id
			left join user u2 on u2.id=pcrh.override_by_user_id
			$filter order by pcrh.id desc");
		$rate_history_list = array();
		while($r = $con->sql_fetchassoc()){
			$rate_history_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		return $rate_history_list;
	}
}
?>
