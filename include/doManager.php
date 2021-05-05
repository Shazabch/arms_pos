<?php
/*
2/12/2019 5:56 PM Andy
- Added doManager.
- Enhanced createDOFromTMP() to able to generate credit sales DO.

3/14/2019 4:02 PM Andy
- Enhanced to have "Invoice Amount Adjust".

5/29/2019 11:59 AM Andy
- Enhanced to have DO Relationship GUID.

8/8/2019 11:12 AM Justin
- Enhanced to have load last driver function.
- Enhanced to have load auto generate GRR and GRN function.

8/16/2019 9:27 AM Justin
- Bug fixed on auto generate GRR and GRN got encoding errors.

9/26/2019 4:31 PM Justin
- Bug fixed on DO split by price type will cause approval cycle issue.

10/15/2019 2:14 PM Andy
- Enhanced "createDOFromTMP" to can create Cash Sales DO.
- Enhanced "createDOFromTMP" to can predefined cost_price.
- Enhanced "createDOFromTMP" to can create Open Item.

10/15/2019 2:19 PM Andy
- Added doManager function "createMarketplaceDO".

11/8/2019 10:32 AM Andy
- Enhanced "createDOFromTMP" to can have discount.

11/19/2019 6:00 PM William
- Enhanced to add multi do branch checking to function "recalculateDOAmount".

1/14/2020 2:50 PM Justin
- Enhanced to insert ID manually for some tables that uses auto increment.

3/17/2020 3:52 PM Andy
- Enhanced to use Marketplace DO Owner Settings when create Marketplace DO.

3/25/2020 10：35 AM William
- Enhanced to change auto approve do when using function createMarketplaceDO create.

3/30/2021 4:10 PM Ian
- Added the function Export_do to export do items to csv.
- Enhanced the function to support open item when adding do items to csv.
*/
class doManager{
	// common variable
	var $defaultPriceSettings = array(1 => "Cost", 2=> "Selling (Normal)", 3 => "Last DO", 4 => "PO Cost");
	
	function __construct(){
		
	}

	// function to automatically recalculate DO and DO Items Amount
	// return null
	public function recalculateDOAmount($branch_id, $do_id){
		global $con, $config;
	
		$branch_id = mi($branch_id);
		$do_id = mi($do_id);
		if(!$branch_id || !$do_id)	return;
		
		$q1 = $con->sql_query("select * from do where branch_id=$branch_id and id=$do_id");
		$form = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		// check currency mode
		if($form['do_type'] == 'transfer' && $config['consignment_modules'] && $config['masterfile_branch_region'] && $config['consignment_multiple_currency'] && $form['exchange_rate']>1){
			$is_currency_mode = true;
			
			if($form['price_indicate']==1)	$use_cost_indicate = true;
			
			// is currency mode
			if($is_currency_mode){
				if($use_cost_indicate)	$currency_multiply = 1;
				else	$currency_multiply = $form['exchange_rate'];
				
				$currency_discount_params = array('currency_multiply'=>$currency_multiply);
				$currency_multiply_rate = 1/$form['exchange_rate'];
				
				if($use_cost_indicate)	$foreign_currency_discount_params['currency_multiply'] = $currency_multiply_rate;
			}
		}
		
		// check got DO markup
		if($form['do_markup']){
			$form['do_markup_arr'] = explode("+", $form['do_markup']);
			if($form['markup_type']=='down'){
				$form['do_markup_arr'][0] *= -1;
				$form['do_markup_arr'][1] *= -1;
			}
		}
		
		$item_list = array();
		
		// select items
		$q2 = $con->sql_query("select di.*, uom.fraction as uom_fraction
		from do_items di
		left join uom on uom.id=di.uom_id
		where di.branch_id=$branch_id and di.do_id=$do_id
		order by di.id");
		while($r = $con->sql_fetchassoc($q2)){
			$item_list[] = $r;
		}
		$con->sql_freeresult($q2);
		
		// select open items
		$q3 = $con->sql_query("select doi.*, uom.fraction as uom_fraction
		from do_open_items doi
		left join uom on uom.id=doi.uom_id
		where doi.branch_id=$branch_id and doi.do_id=$do_id
		order by doi.id");
		while($r = $con->sql_fetchassoc($q3)){
			$r['is_open_item'] = 1;
			$item_list[] = $r;
		}
		$con->sql_freeresult($q3);
		
		$total_ctn = 0;
		$total_pcs = 0;
		$total_qty = 0;
		$total_rcv = 0;
		
		// DO amt
		$sub_total_gross_amt = 0;
		$sub_total_gst_amt = 0;
		$sub_total_amt = 0;
		
		$do_total_gross_amt = 0;
		$do_total_gst_amt = 0;
		$total_amount = 0;
		$total_round_amt = 0;
		$total_foreign_amount = 0;
		
		// INV amt
		$inv_sub_total_gross_amt = 0;
		$inv_sub_total_gst_amt = 0;
		$sub_total_inv_amt = 0;
		$sub_total_foreign_inv_amt = 0;
		
		$inv_total_gross_amt = 0;
		$inv_total_gst_amt = 0;
		$total_inv_amt = 0;
		$total_round_inv_amt = 0;
		$total_foreign_inv_amt = 0;
		
		// inv sheet discount
		$inv_sheet_gross_discount_amt = 0;
		$inv_sheet_gst_discount = 0;
		$inv_sheet_discount_amt = 0;
		$inv_sheet_foreign_discount_amt = 0;
		
		if($item_list){
			foreach($item_list as $k=>$r){
				$line_gross_amt = 0;
				$line_gst_amt = 0;
				$line_amt = 0;
				
				$item_discount_amount = 0;
				$item_discount_amount2 = 0;
				
				$inv_line_gross_amt = 0;
				$inv_line_gst_amt = 0;
				$inv_line_amt = 0;
				
				$inv_line_gross_amt2 = 0;
				$inv_line_gst_amt2 = 0;
				$inv_line_amt2 = 0;
				
				$cost = $r['cost_price'];
				if($is_currency_mode){
					$foreign_cost = round($cost*$currency_multiply_rate, $config['global_cost_decimal_points']);
				}
				
				// do markup
				if($form['do_markup_arr'][0]){
					$cost = $cost * (1+($form['do_markup_arr'][0]/100));
				}
				if($form['do_markup_arr'][1]){
					$cost = $cost * (1+($form['do_markup_arr'][1]/100));
				}
				
				// currency markup
				if($is_currency_mode){
					if($form['do_markup_arr'][0]){
						$foreign_cost = $foreign_cost * (1+($form['do_markup_arr'][0]/100));
					}
					if($form['do_markup_arr'][1]){
						$foreign_cost = $foreign_cost * (1+($form['do_markup_arr'][1]/100));
					}
				}
				
				$is_multi_branch = false;
				if($form['do_type']=='transfer' && ($form['do_branch_id']== 0 || !$form['do_branch_id'])&& unserialize($form['deliver_branch'])){
					$deliver_branch = unserialize($form['deliver_branch']);
					$ctn_allocation = unserialize($r['ctn_allocation']);
					$pcs_allocation = unserialize($r['pcs_allocation']);
					$row_qty = 0;
					$line_amt = 0;
					$line_gross_amt = 0;
					$total_item_ctn = 0;
					$total_item_pcs = 0;
					$is_multi_branch = true;
					foreach($deliver_branch as $key=>$bid){
						$row_qty+= ($ctn_allocation[$bid]*$r['uom_fraction'])+$pcs_allocation[$bid];
						
						$amount_ctn = $ctn_allocation[$bid]*$cost;
						$amount_pcs = $pcs_allocation[$bid]*$cost/$r['uom_fraction'];
						
						$line_amt += ($amount_ctn+$amount_pcs);
						$line_gross_amt += ($amount_ctn+$amount_pcs);
						$total_item_ctn += $ctn_allocation[$bid];
						$total_item_pcs += $pcs_allocation[$bid];
					}
				}else{
					$row_qty = $r['ctn']*$r['uom_fraction']+$r['pcs'];
					
					$amount_ctn = $r['ctn']*$cost;
					$amount_pcs = $r['pcs']*$cost/$r['uom_fraction'];
					
					if($is_currency_mode){
						$foreign_row_amt = round(($r['pcs']*$foreign_cost/$r['uom_fraction'])+($r['ctn']*$foreign_cost),2);
					}
					$line_amt = $line_gross_amt = $amount_ctn+$amount_pcs;
				}
									
				// calculate DO amount
				if($form['is_under_gst']){
					$line_gst_amt = $line_gross_amt * $r['gst_rate'] / 100;
					
					$line_amt = round($line_gross_amt + $line_gst_amt, 2);
					$line_gross_amt_rounded = round($line_gross_amt, 2);
					
					$line_gst_amt = round($line_amt - $line_gross_amt_rounded, 2);
				}
				
				
				// calculate invoice amount
				$inv_line_gross_amt = $line_gross_amt;
				if($is_multi_branch && count($deliver_branch) > 1){
					$currency_discount_params['discount_by_value_multiply'] = count($deliver_branch);
				}
				
				// invoice discount
				if($r['item_discount']){
					$item_discount_amount = get_discount_amt($inv_line_gross_amt, $r['item_discount'], $currency_discount_params);
					$inv_line_gross_amt -= $item_discount_amount;
				}
				
				$inv_line_amt = $inv_line_gross_amt;
				
				if($form['is_under_gst']){
					$inv_line_gst_amt = $inv_line_gross_amt * $r['gst_rate'] / 100;
					
					$inv_line_amt = round($inv_line_gross_amt + $inv_line_gst_amt, 2);
					$inv_line_gross_amt_rounded = round($inv_line_gross_amt, 2);
					
					$inv_line_gst_amt = round($inv_line_amt - $inv_line_gross_amt_rounded, 2);
				}
				
				
				// currency invoice
				if($is_currency_mode){
					$total_foreign_amount += $foreign_row_amt;
					
					// invoice
					$foreign_inv_amt = $foreign_row_amt;
					if($r['item_discount']){
						$foreign_inv_discount_amt = round(get_discount_amt($foreign_inv_amt, $r['item_discount'], $foreign_currency_discount_params),2);
						$foreign_inv_amt = round($foreign_inv_amt -$foreign_inv_discount_amt,2);
					}
					$sub_total_foreign_inv_amt += $foreign_inv_amt;
				}
				
				// total 
				if($is_multi_branch){
					$total_ctn += $total_item_ctn;
					$total_pcs += $total_item_pcs;
				}else{
					$total_ctn += $r['ctn'];
					$total_pcs += $r['pcs'];
				}
				$total_qty += $row_qty;
				$total_rcv += $r['rcv_pcs'];
				
				// DO amt
				$sub_total_gross_amt += round($line_gross_amt,2);
				$sub_total_gst_amt += round($line_gst_amt,2);
				$sub_total_amt += round($line_amt,2);
				
				// INV amt
				$inv_sub_total_gross_amt += round($inv_line_gross_amt,2);
				$inv_sub_total_gst_amt += round($inv_line_gst_amt,2);
				$sub_total_inv_amt += round($inv_line_amt,2);
				
				// assign back to array
				$item_list[$k]['line_gross_amt'] = round($line_gross_amt,2);
				$item_list[$k]['line_gst_amt'] = round($line_gst_amt,2);
				$item_list[$k]['line_amt'] = round($line_amt,2);
				
				$item_list[$k]['item_discount_amount'] = round($item_discount_amount,2);
				$item_list[$k]['item_discount_amount2'] = round($item_discount_amount2,2);
				
				$item_list[$k]['inv_line_gross_amt'] = round($inv_line_gross_amt,2);
				$item_list[$k]['inv_line_gst_amt'] = round($inv_line_gst_amt,2);
				$item_list[$k]['inv_line_amt'] = round($inv_line_amt,2);
				
				$inv_line_gross_amt2 = round($inv_line_gross_amt,2);
				$inv_line_gst_amt2 = round($inv_line_gst_amt,2);
				$inv_line_amt2 = round($inv_line_amt,2);
				
				$item_list[$k]['inv_line_gross_amt2'] = $inv_line_gross_amt2;
				$item_list[$k]['inv_line_gst_amt2'] = $inv_line_gst_amt2;
				$item_list[$k]['inv_line_amt2'] = $inv_line_amt2;
			}
			
			$do_total_gross_amt = $sub_total_gross_amt;
			$do_total_gst_amt = $sub_total_gst_amt;
			$total_amount = $sub_total_amt;
			
			$inv_total_gross_amt = $inv_sub_total_gross_amt;
			$inv_total_gst_amt = $inv_sub_total_gst_amt;
			$total_inv_amt = $sub_total_inv_amt;
			$total_foreign_inv_amt = $sub_total_foreign_inv_amt;
			
			// sheet invoice discount
			if($form['discount']){
				$inv_sheet_discount_amt = round(get_discount_amt($total_inv_amt, $form['discount'], $currency_discount_params),2);
				
				if($inv_sheet_discount_amt){
					// find the inv discount percent
					$inv_sheet_discount_per = $inv_sheet_discount_amt / $total_inv_amt;
					
					$inv_sheet_gross_discount_amt = round($inv_sub_total_gross_amt * $inv_sheet_discount_per, 2);
					
					if($form['is_under_gst']){					
						$inv_sheet_gst_discount = round($inv_sheet_discount_amt - $inv_sheet_gross_discount_amt, 2);
					}
				}
				
				$inv_total_gross_amt = round($inv_sub_total_gross_amt - $inv_sheet_gross_discount_amt, 2);
				$inv_total_gst_amt = round($inv_sub_total_gst_amt - $inv_sheet_gst_discount, 2);
				$total_inv_amt = round($total_inv_amt - $inv_sheet_discount_amt, 2);			
				
				if($item_list){
					$remaining_inv_sheet_gross_discount_amt = $inv_sheet_gross_discount_amt;
					$remaining_inv_sheet_gst_discount = $inv_sheet_gst_discount;
					$remaining_inv_sheet_discount_amt = $inv_sheet_discount_amt;
					
					$item_len = count($item_list);
					$curr_count = 0;
					foreach($item_list as $k => $r){
						$curr_count++;
						
						$inv_line_gross_amt2 = round($r['inv_line_gross_amt'] * (1 - $inv_sheet_discount_per), 2);
						$inv_line_amt2 = round($r['inv_line_amt'] * (1 - $inv_sheet_discount_per),2);
						$inv_line_gst_amt2 = round($inv_line_amt2 - $inv_line_gross_amt2, 2);
						$item_discount_amount2 = round($r['inv_line_amt'] - $inv_line_amt2, 2);
						
						$remaining_inv_sheet_gross_discount_amt = round($remaining_inv_sheet_gross_discount_amt - ($r['inv_line_gross_amt'] - $inv_line_gross_amt2), 2);
						$remaining_inv_sheet_gst_discount = round($remaining_inv_sheet_gst_discount - ($r['inv_line_gst_amt'] - $inv_line_gst_amt2), 2);
						$remaining_inv_sheet_discount_amt = round($remaining_inv_sheet_discount_amt - ($item_discount_amount2), 2);
						
						if($curr_count == $item_len){
							if($remaining_inv_sheet_gross_discount_amt != 0){
								$inv_line_gross_amt2 -= $remaining_inv_sheet_gross_discount_amt;
								$remaining_inv_sheet_gross_discount_amt = 0;
							}
							if($remaining_inv_sheet_gst_discount != 0){
								$inv_line_gst_amt2 -= $remaining_inv_sheet_gst_discount;
								$remaining_inv_sheet_gst_discount = 0;
							}
							if($remaining_inv_sheet_discount_amt != 0){
								$inv_line_amt2 -= $remaining_inv_sheet_discount_amt;
								$item_discount_amount2 -= $remaining_inv_sheet_discount_amt;
								$remaining_inv_sheet_discount_amt = 0;
							}
						}
						
						$item_list[$k]['inv_line_gross_amt2'] = $inv_line_gross_amt2;
						$item_list[$k]['inv_line_gst_amt2'] = $inv_line_gst_amt2;
						$item_list[$k]['inv_line_amt2'] = $inv_line_amt2;
						$item_list[$k]['item_discount_amount2'] = $item_discount_amount2;
					}
				}
			
				// get currency total invoice amt
				if($is_currency_mode){
					$foreign_inv_discount_amt = round(get_discount_amt($sub_total_foreign_inv_amt, $form['discount'], $foreign_currency_discount_params),2);
					$total_foreign_inv_amt -= $foreign_inv_discount_amt;
					$inv_sheet_foreign_discount_amt += $foreign_inv_discount_amt;
				}
			}
			
			// Invoice Amount Adjustment
			if($form['inv_sheet_adj_amt']){
				$total_inv_amt += round($form['inv_sheet_adj_amt'], 2);
			}
			
			// Cash Sales Rounding
			if($form['do_type']=='open' && $config['do_enable_cash_sales_rounding']){
				$total_rounded_amt = MYR_rounding($total_amount);
				$total_round_amt = round($total_rounded_amt - $total_amount, 2);
				$total_amount = $total_rounded_amt;
				
				if($total_inv_amt){
					$total_rounded_inv_amt = MYR_rounding($total_inv_amt);
					$total_round_inv_amt = round($total_rounded_inv_amt - $total_inv_amt, 2);
					$total_inv_amt = $total_rounded_inv_amt;
				}
			}
		}
		
		// qty
		$form['total_ctn'] = $total_ctn;
		$form['total_pcs'] = $total_pcs;
		$form['total_qty'] = $total_qty;
		$form['total_rcv'] = $total_rcv;
		
		// DO amt
		$form['sub_total_gross_amt'] = $sub_total_gross_amt;
		$form['sub_total_gst_amt'] = $sub_total_gst_amt;
		$form['sub_total_amt'] = $sub_total_amt;
		
		$form['do_total_gross_amt'] = $do_total_gross_amt;
		$form['do_total_gst_amt'] = $do_total_gst_amt;
		$form['total_amount'] = $total_amount;
		$form['total_round_amt'] = $total_round_amt;
		$form['total_foreign_amount'] = $total_foreign_amount;
		
		// INV amt
		$form['inv_sub_total_gross_amt'] = $inv_sub_total_gross_amt;
		$form['inv_sub_total_gst_amt'] = $inv_sub_total_gst_amt;
		$form['sub_total_inv_amt'] = $sub_total_inv_amt;
		$form['sub_total_foreign_inv_amt'] = $sub_total_foreign_inv_amt;
		
		$form['inv_total_gross_amt'] = $inv_total_gross_amt;
		$form['inv_total_gst_amt'] = $inv_total_gst_amt;
		$form['total_inv_amt'] = $total_inv_amt;
		$form['total_round_inv_amt'] = $total_round_inv_amt;
		$form['total_foreign_inv_amt'] = $total_foreign_inv_amt;
		
		// inv sheet discount
		$form['inv_sheet_gross_discount_amt'] = $inv_sheet_gross_discount_amt;
		$form['inv_sheet_gst_discount'] = $inv_sheet_gst_discount;
		$form['inv_sheet_discount_amt'] = $inv_sheet_discount_amt;
		$form['inv_sheet_foreign_discount_amt'] = $inv_sheet_foreign_discount_amt;
			
		//print_r($form);
		//print "<br>------------------------<br>";
		//print_r($item_list);
		
		$do_fields = array('total_ctn','total_pcs','total_qty','total_rcv','sub_total_gross_amt','sub_total_gst_amt','sub_total_amt','do_total_gross_amt','do_total_gst_amt','total_amount','total_round_amt','total_foreign_amount',
		'inv_sub_total_gross_amt','inv_sub_total_gst_amt','sub_total_inv_amt','sub_total_foreign_inv_amt','inv_total_gross_amt','inv_total_gst_amt','total_inv_amt','total_round_inv_amt','total_foreign_inv_amt',
		'inv_sheet_gross_discount_amt','inv_sheet_gst_discount','inv_sheet_discount_amt','inv_sheet_foreign_discount_amt','last_update');
		
		$line_gross_amt = 0;
				$line_gst_amt = 0;
				$line_amt = 0;
				
				$item_discount_amount = 0;
				$item_discount_amount2 = 0;
				
				$inv_line_gross_amt = 0;
				$inv_line_gst_amt = 0;
				$inv_line_amt = 0;
				
				$inv_line_gross_amt2 = 0;
				$inv_line_gst_amt2 = 0;
				$inv_line_amt2 = 0;
				
		$di_fields = array('line_gross_amt','line_gst_amt','line_amt','item_discount_amount','item_discount_amount2','inv_line_gross_amt','inv_line_gst_amt','inv_line_amt','inv_line_gross_amt2','inv_line_gst_amt2','inv_line_amt2');
		$doi_fields = $di_fields;
		
		$con->sql_query("update do set ".mysql_update_by_field($form, $do_fields)." where branch_id=$branch_id and id=$do_id");
		if($item_list){
			foreach($item_list as $r){
				if($r['is_open_item']){
					$con->sql_query("update do_open_items set ".mysql_update_by_field($r, $doi_fields)." where branch_id=$branch_id and id=".mi($r['id']));
				}else{
					$con->sql_query("update do_items set ".mysql_update_by_field($r, $di_fields)." where branch_id=$branch_id and id=".mi($r['id']));
				}
			}
		}
	}
	
	// function to create DO from 'tmp_generate_do' and 'tmp_generate_do_items'
	// return integer $do_id
	public function createDOFromTMP($tmp_do_guid){
		global $con, $config;
		
		// No GUID
		if(!$tmp_do_guid)	return;
		
		// Get tmp DO
		$con->sql_query("select * from tmp_generate_do where guid=".ms($tmp_do_guid));
		$tmp_do = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// DO not found
		if(!$tmp_do)	return;
		
		$tmp_do_items_list = $tmp_do_open_items_list = array();
		
		// Get tmp DO Items
		$q1 = $con->sql_query("select * from tmp_generate_do_items where gen_do_guid=".ms($tmp_do_guid)." order by sequence");
		while($r = $con->sql_fetchassoc($q1)){
			$tmp_do_items_list[$r['guid']] = $r;
		}
		$con->sql_freeresult($q1);
		
		// Get tmp DO Open Items
		$q1 = $con->sql_query("select * from tmp_generate_do_open_items where gen_do_guid=".ms($tmp_do_guid)." order by sequence");
		while($r = $con->sql_fetchassoc($q1)){
			$tmp_do_open_items_list[$r['guid']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$do = array();
		$do['branch_id'] = $tmp_do['branch_id'];
		$do['last_update'] = 'CURRENT_TIMESTAMP';
		$do['added'] = 'CURRENT_TIMESTAMP';
		$do['do_date'] = $tmp_do['do_date'] ? $tmp_do['do_date'] : date("Y-m-d");
		$do['user_id'] = $tmp_do['user_id'];
		$do['do_type'] = $tmp_do['do_type'];
		
		if($tmp_do['discount'])	$do['discount'] = $tmp_do['discount'];
		if($tmp_do['remark'])	$do['remark'] = $tmp_do['remark'];
		
		$do['price_indicate'] = 2;	// Selling by default
		if($config['do_default_price_from'] == 'cost'){
			$do['price_indicate'] = 1;
		}elseif($config['do_default_price_from'] == 'last_do'){
			$do['price_indicate'] = 3;
		}
		
		if($do['do_type']=='transfer'){
			$do['do_branch_id'] = $tmp_do['do_branch_id'];
		}elseif($do['do_type']=='credit_sales'){
			$do['debtor_id'] = $tmp_do['debtor_id'];
		}else{
			$tmp_do['open_info'] = unserialize($tmp_do['open_info']);
			
			$do['open_info']['name'] = $tmp_do['open_info']['name'];
			$do['open_info']['address'] = $tmp_do['open_info']['address'];
			
			$do['open_info'] = serialize($do['open_info']);
		}
		
		if($config['enable_gst']){
			$do['is_under_gst'] = $this->checkDOGstStatus($do);
		}
		
		// DO Relationship GUID
		if($tmp_do['relationship_guid']){
			$do['relationship_guid'] = $tmp_do['relationship_guid'];
		}
		
		$con->sql_query("insert into do ".mysql_insert_by_field($do));
		$do_id = $con->sql_nextid();
		
		if($tmp_do_items_list){
			foreach($tmp_do_items_list as $tmp_do_items_guid => $r){
				$sid = mi($r['sku_item_id']);
				if(!$sid)	continue;
				
				// new item	
				$q3=$con->sql_query("select sku_items.id as sku_item_id, if(sku_items.artno is null or sku_items.artno='',sku_items.mcode, sku_items.artno) as artno_mcode, uom.id as uom_id, uom.fraction as uom_fraction  
	from sku_items 
	left join sku on sku_id = sku.id
	left join uom on uom.id = ".mi($r['uom_id'])."
	where sku_items.id=$sid");
				$item = $con->sql_fetchassoc($q3);
				$con->sql_freeresult($q3);
				
				$item['branch_id'] = $do['branch_id'];
				$item['do_id'] = $do_id;
				$item['ctn'] = $r['ctn'];
				$item['pcs'] = $r['pcs'];
				
				if($r['cost_price']>0){
					// Use the predefined cost_price
					$item['cost_price'] = $r['cost_price'];
				}else{
					// select cost_price
					$tmp = $this->getItemPrice($item['sku_item_id'], $do['branch_id'], $do['price_indicate'], $do);
					$item = array_merge($item, $tmp);
				
					if($item['uom_fraction'] != 1){
						$item['cost_price'] *= $item['uom_fraction'];
						if(isset($item['display_cost_price']))	$item['display_cost_price'] *= $item['uom_fraction'];
					}
				}
				
				
				
				$tmp_sell = $this->getItemSelling($do['branch_id'], $item['sku_item_id'], false, $do['do_branch_id'], $do['do_type']);
				$item = array_merge($item, $tmp_sell);
				
				$tmp_do_date = date("Y-m-d", strtotime($do['do_date']." +1 day"));
				$tmp_cost = get_sku_item_cost_selling($do['branch_id'], $item['sku_item_id'], $tmp_do_date, array("cost"));
				if($tmp_cost) $item = array_merge($item, $tmp_cost);
		
				// stock balance
				$sql = $con->sql_query("select sku_item_id, qty from sku_items_cost where branch_id=".mi($do['branch_id'])." and sku_item_id=".mi($item['sku_item_id']));
				$item['stock_balance1'] = $con->sql_fetchfield('qty');
				$con->sql_freeresult($sql);
				
				// stock balance 2
				$sql = $con->sql_query("select sku_item_id, qty from sku_items_cost where branch_id=".mi($do['do_branch_id'])." and sku_item_id=".mi($item['sku_item_id']));
				$item['stock_balance2'] = $con->sql_fetchfield('qty');
				$con->sql_freeresult($sql);
				
				$fields = array('branch_id', 'do_id', 'sku_item_id', 'artno_mcode', 'uom_id', 'ctn', 'pcs', 'stock_balance1', 'stock_balance2', 'cost', 'cost_price', 'selling_price', 'price_indicate', 'price_no_history');
				if($config['enable_gst']){
					$fields[] = "display_cost_price_is_inclusive";
					$fields[] = "display_cost_price";
				}
				
				$con->sql_query("insert into do_items ".mysql_insert_by_field($item, $fields));
			}
		}
		
		// DO Open Items
		if($tmp_do_open_items_list){
			foreach($tmp_do_open_items_list as $r){
				$item = array();
				$item['branch_id'] = $do['branch_id'];
				$item['do_id'] = $do_id;
				$item['pcs'] = $r['pcs'];
				$item['artno_mcode'] = $r['artno_mcode'];
				$item['description'] = $r['description'];
				$item['cost_price'] = $r['cost_price'];
				
				$con->sql_query("insert into do_open_items ".mysql_insert_by_field($item));
			}
		}
		
		// Calculate all amount
		$this->recalculateDOAmount($do['branch_id'], $do_id);
		
		return $do_id;
	}
	
	// function to check DO got GST or not
	// return integer $is_under_gst
	public function checkDOGstStatus($form){
		global $config;
		
		$is_under_gst = 0;
		$check_gst = true;
		if($config['consignment_modules']){
			if($form['do_type']=='transfer')	$check_gst = false;
		}
		// check whether this do is under gst
		if($config['enable_gst'] && $check_gst){
			$params = array();
			$params['date'] = $form['do_date'];
			$params['branch_id'] = $form['branch_id'];
			
			if($form['do_type']=='transfer'){
				// Transfer DO
				if($form['do_branch_id']){
					// single branch
					$params['to_branch_id'] = $form['do_branch_id'];
					$is_under_gst = check_gst_status($params);
				}elseif($form['deliver_branch']){
					// multi branch
					
					foreach($form['deliver_branch'] as $bid){
						$params['to_branch_id'] = $bid;
						$tmp_is_under_gst = check_gst_status($params);
						if($tmp_is_under_gst){
							$is_under_gst = 1;
						}else{
							$is_under_gst = 0;
							break;
						}
					}
				}
			}else{
				// cash sales & credit sales no need check gst interbranch
				$is_under_gst = check_gst_status($params);
			}
		
		}else{
			$is_under_gst = 0;
		}
		
		if($is_under_gst){
			construct_gst_list();
		}
		return $is_under_gst;
	}
	
	// function to get DO Item Order Price
	// return array $tmp
	public function getItemPrice($sku_item_id, $branch_id, $price_indicate,$form, $output_gst = array(), $is_special_exemption = false){
		global $con, $smarty, $config, $appCore;
	
		$tmp=array();
		$next_timestamp = ms(date("Y-m-d H:i:s",strtotime("+1 day", strtotime($form['do_date']))));
		
		// it is credit sales DO
		if($form['do_type']=='credit_sales'){
			$debtor_id = mi($form['debtor_id']);
			if($form['use_debtor_price']){	// Use Debtor Price
				$debtor_price = $appCore->skuManager->getSKUItemDebtorPrice($branch_id, $sku_item_id, $debtor_id);
				if($debtor_price>0){
					$tmp['cost_price'] = $debtor_price;
					$tmp['price_indicate'] = 'debtor_price';
					$tmp['price_indicator'] = 'debtor_price';
				}	
			}
			
			if(!$form['no_use_credit_sales_cost'] && !$tmp){
				// always get last debtor price if got history
				$sql = "select (di.cost_price/uom.fraction) as cost_price, (di.display_cost_price/uom.fraction) as display_cost_price, di.display_cost_price_is_inclusive
				from do_items di
		left join do on do.id=di.do_id and do.branch_id=di.branch_id
		left join uom on uom.id=di.uom_id
		where do.do_type='credit_sales' and di.sku_item_id=".mi($sku_item_id)." and do.branch_id=".mi($branch_id)." and do.debtor_id=$debtor_id and do.id<>".mi($form['id'])." and do.approved=1 order by do.do_date desc,do.do_no desc limit 1";
				$con->sql_query($sql) or die(mysql_error());
				$r = $con->sql_fetchrow();
				$con->sql_freeresult();
		
				if($r){
					$tmp['cost_price'] = $r['cost_price'];
					$tmp['display_cost_price'] = $r['display_cost_price'];
					$tmp['display_cost_price_is_inclusive'] = $r['display_cost_price_is_inclusive'];
					$tmp['price_indicator'] = 'Credit Sales DO';
					//return $tmp;
				}
			}
			
			// no last debtor price, use price indicator
			if(!$tmp)	$tmp['price_no_history'] = 1;
		}
		
		//chk the option from user, if get from selling then get from sku_items_price if get from cost then get the price as last time.
		if($price_indicate==3){
			// get last DO price
			$q1=$con->sql_query("select cost_price from do_items left join do on do_id = do.id and do_items.branch_id = do.branch_id where do.active=1 and do_items.sku_item_id=".mi($sku_item_id)." and do_items.branch_id=$branch_id order by do_items.id desc limit 1");
			$r = $con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);
			$decimal_points=2;
		}
		elseif($price_indicate==2){
			// get selling price
			$q1=$con->sql_query("select price from sku_items_price_history where sku_item_id=".mi($sku_item_id)." and branch_id=$branch_id and added<$next_timestamp order by added desc limit 1");
			$r = $con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);
			$decimal_points=2;
		}
		elseif($price_indicate==1){
			// cost
			$q2=$con->sql_query("select grn_cost from sku_items_cost_history where sku_item_id=".mi($sku_item_id)." and branch_id=$branch_id and date<$next_timestamp order by date desc limit 1");
			$r = $con->sql_fetchrow($q2);
			$con->sql_freeresult($q2);

			// for DO transfer and consignment customer
			if(is_array($config['consignment_multiple_currency']) && $form['exchange_rate'] && $form['exchange_rate'] != 1){
				$foreign_cost = $r[0]/$form['exchange_rate'];
			}
			
			$decimal_points=$config['global_cost_decimal_points'];
		}
		elseif ($price_indicate==4)
		{
			// po cost
			$q2=$con->sql_query("select order_price/order_uom_fraction from po left join po_items pi on po.branch_id = pi.branch_id and po.id = pi.po_id where po.po_no = ".ms($_REQUEST['po_no'])." and pi.sku_item_id=".mi($sku_item_id));
			$r = $con->sql_fetchrow($q2);
			$con->sql_freeresult($q2);
			$decimal_points=$config['global_cost_decimal_points'];
		}
		elseif($price_indicate=="hqselling"){
			// hq selling
			$q2 = $con->sql_query("select hq_selling from sku_items where id = ".mi($sku_item_id));
			$r = $con->sql_fetchrow($q2);
			$con->sql_freeresult($q2);
			$decimal_points=2;
		}
		else{
			// other mprice type
			$q1=$con->sql_query($q = "select price from sku_items_mprice_history where sku_item_id=".mi($sku_item_id)." and type=".ms($price_indicate)." and branch_id=$branch_id and added<$next_timestamp order by added desc limit 1");
			//print "$q";
			$r = $con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);
			$decimal_points=2;
		}	
		$decimal_points = 4;
		
		if(!$r)	// if no price taken, use default master 
		{
			if ($price_indicate==1 or $price_indicate==3){ // DO or GRN selected
				// master cost
				$q2=$con->sql_query("select cost_price from sku_items where id=".mi($sku_item_id));
				$r = $con->sql_fetchrow($q2);
				$con->sql_freeresult($q2);
			}
			else
			{
				// get from selling price history
				$q1=$con->sql_query("select price from sku_items_price_history where sku_item_id=".mi($sku_item_id)." and branch_id=$branch_id and added<$next_timestamp order by added desc limit 1");
				$r = $con->sql_fetchrow($q1);
				$con->sql_freeresult($q1);
				
				if(!$r){	// master selling
					$q2=$con->sql_query("select selling_price from sku_items where id=".mi($sku_item_id));
					$r = $con->sql_fetchrow($q2);
					$con->sql_freeresult($q2);
				}
			}
		}

		// for DO transfer and consignment customer
		if($price_indicate != 1 && $config['consignment_modules'] && is_array($config['masterfile_branch_region']) && is_array($config['consignment_multiple_currency']) && $form['exchange_rate'] && $form['exchange_rate'] != 1){ // if the price is not from cost
			$foreign_cost = $r[0];
			$r[0] = $r[0]*$form['exchange_rate'];
		}
		
		if(!isset($tmp['cost_price'])){
			$tmp['cost_price'] = round($r[0], $decimal_points);
			if(!isset($tmp['display_cost_price_is_inclusive']))	$tmp['display_cost_price_is_inclusive'] = 0;
			if(!isset($tmp['display_cost_price']))	$tmp['display_cost_price'] = $tmp['cost_price'];
			$tmp['foreign_cost_price'] = round($foreign_cost, $config['global_cost_decimal_points']);
		}
		
		
		// GST
		if($config['enable_gst']){
			if($form['is_under_gst']){
				// get sku is inclusive
				$is_sku_inclusive = get_sku_gst("inclusive_tax", $sku_item_id);
				// get sku original output gst
				$sku_original_output_gst = get_sku_gst("output_tax", $sku_item_id);
				
				if($price_indicate != 1 && $price_indicate != 3  && $price_indicate != 4 && $is_sku_inclusive == 'yes'){
					$price_included_gst = $tmp['cost_price'];
					// is inclusive tax			
					if($is_special_exemption){
						// find the price before tax
						$gst_tax_price = $price_included_gst / ($sku_original_output_gst['rate']+100) * $sku_original_output_gst['rate'];
						$price_excl_gst = $price_included_gst - $gst_tax_price;
					
						// special exemption always use nett price
						$tmp['display_cost_price'] = $tmp['cost_price'] = $price_excl_gst;
					}else{
						$tmp['display_cost_price_is_inclusive'] = 1;
						$tmp['display_cost_price'] = $price_included_gst;
					
						$use_gst = $output_gst ? $output_gst : $sku_original_output_gst;
						$gst_tax_price = $price_included_gst / ($use_gst['rate']+100) * $use_gst['rate'];
						$tmp['cost_price'] = $price_included_gst - $gst_tax_price;
					}
				}
			}
		}
		
		if(!isset($tmp['price_indicate']) && isset($this->defaultPriceSettings[$form['price_indicate']])){
			$tmp['price_indicate'] = $form['price_indicate'];
		}
		
		//print_r($tmp);exit;
		
		return $tmp;
	}
	
	// function to get DO Item Selling
	// return array
	public function getItemSelling($branch_id, $sid, $deliver_branch, $do_bid, $do_date, $selling_price=0){
		global $con;
		//plus 1 day for comparing the do date with timestamp data
		$do_date = date("Y-m-d",strtotime("+1 day",strtotime($do_date)));
	 
		if (is_array($deliver_branch)){
			$ret = array();
			foreach($deliver_branch as $k=>$v){
				$sp = get_sku_item_cost_selling($v, $sid, $do_date, array('selling'));
				if($selling_price) $sp['selling'] = $selling_price;
				$ret['selling_price_allocation'][$v]= $sp['selling'];
				$ret['price_type'][$v] = $sp['trade_discount_code'];
			}
		}
		else{
			if($do_bid){
				$bid=intval($do_bid);
			}
			else{
				$bid=$branch_id; 
			}
			
			$ret = array();
			$result = get_sku_item_cost_selling($bid, $sid, $do_date, array('selling'));
			
			// Selling
			if($selling_price) $ret['selling_price'] = $selling_price;
			else $ret['selling_price'] = $result['selling'];
			
			// Price Type
			$ret['price_type'][$bid] = $result['trade_discount_code'];
		}
		//print_r($ret);
		
		return $ret;
	}
	
	public function doApprovalHandler($prms=array()){
		global $con, $sessioninfo, $LANG, $config;
		
		$ret = array();
		if(!$prms) die("Failed to check Approval");
		
		// check and create branch_approval_history data
	    $arr = array(); 
	    $arr['type'] = 'DO';
	    $arr['reftable'] = 'DO';
	    $arr['user_id'] = $sessioninfo['id'];
	    
	   	if($config['do_approval_by_department']){
			$arr['dept_id'] = $prms['dept_id'];
		}
	    
	    if($config['consignment_modules']){
          $arr['branch_id'] = 1;
          $arr['save_as_branch_id'] = $prms['branch_id'];
		}else{
          $arr['branch_id'] = $prms['branch_id'];              
		}
		
		$arr['doc_amt'] = $prms['total_amount'];
		if(isset($prms['total_inv_amt']))	$arr['doc_amt'] = $prms['total_inv_amt'];	// use invoice amt if got
		
		if($prms['approval_history_id']) $arr['curr_flow_id'] = $prms['approval_history_id']; // use back the same id if already have
		$astat = check_and_create_approval2($arr, $con);
  	 
		if(!$astat) $ret['errm']['top'][] = $LANG['DO_NO_APPROVAL_FLOW'];
  		else{
  			 $ret['approval_history_id'] = $astat[0];
     		 if ($astat[1] == '|'){
     		 	$ret['last_approval'] = true;
     		 	if($astat['direct_approve_due_to_less_then_min_doc_amt'])	$ret['direct_approve_due_to_less_then_min_doc_amt'] = 1;	// direct approve because no qualify for min doc amt
     		 } 
  		}
		unset($astat);
		
		return $ret;
	}
	
	public function priceTypeHandler($prms=array()){
		global $con, $sessioninfo, $config;
		
		$ret_rs = array();
		$ret_rs['split_failed'] = true; // always return split failed if reach certain area couldn't further split it
		if(!$prms) return $ret_rs;
		
		$form = $prms['do_info'];
		$do_id = $prms['do_id'];
		$branch_id = $prms['branch_id'];
		$use_tmp_tbl = $prms['use_tmp_tbl'];
		$need_redirect = $prms['need_redirect'];
		
		if($form['create_type']==2){
			$form['open_info'] = serialize($form['open_info']);
			$form['deliver_branch']='';
			return $ret_rs;
		}
		else{
			if(!($form['deliver_branch'])){
				$deliver_branch[]=$form['do_branch_id'];
			}
			else{
				$deliver_branch=$form['deliver_branch'];
			}
			$form['deliver_branch']='';
			$form['open_info']='';
		}
		
		foreach ($deliver_branch as $deliver_branch_id){
			$form['do_branch_id'] = mi($deliver_branch_id);
			unset($form['allowed_user']);
			$form['allowed_user'][$deliver_branch_id] = $tmp_form[$form['do_branch_id']];
			$form['allowed_user'] = serialize($form['allowed_user']);
			$do_branch_id = mi($deliver_branch_id);
			
			if($config['do_split_auto_add_do_discount']&&$do_branch_id){  // find discount table
				$q1 = $con->sql_query("select btd.*,tdt.code
									   from branch_trade_discount btd
									   left join trade_discount_type tdt on tdt.id=btd.trade_discount_id
									   where btd.branch_id=".mi($do_branch_id));
				while($r = $con->sql_fetchassoc($q1)){
					$branch_trade_discount[$r['code']] = $r['value'];
				}
				$con->sql_freeresult($q1);
				$form['default_do_markup'] = $form['do_markup'];
			}

			// separate items into different discount type
			if($use_tmp_tbl){
				$tbl_name = "tmp_do_items";
				$xtra_filter = "and tmp.user_id=$sessioninfo[id]";
			}else $tbl_name = "do_items";
			$sql = "select tmp.*,uom.fraction,if(sip.price is null,sku.default_trade_discount_code,sip.trade_discount_code) as discount_code, sku.default_trade_discount_code
					from $tbl_name tmp
					left join sku_items_price sip on sip.sku_item_id=tmp.sku_item_id and sip.branch_id=$do_branch_id
					left join sku_items si on si.id=tmp.sku_item_id
					left join sku on sku.id=si.sku_id
					left join uom on tmp.uom_id=uom.id
					where do_id=$do_id and tmp.branch_id=$branch_id $xtra_filter order by id";

			$con->sql_query($sql) or die(mysql_error());

			while($r = $con->sql_fetchassoc()){
				$pcs_allocation = unserialize($r['pcs_allocation']);
				$ctn_allocation = unserialize($r['ctn_allocation']);

				if($config['sku_always_show_trade_discount'] && !$r['discount_code']) $r['discount_code'] = $r['default_trade_discount_code'];
				
				//Multiple Branch
				if($pcs_allocation || $ctn_allocation){
					$price_type = array();

					//calc pcs
					$pcs=$pcs_allocation[$do_branch_id];
					$price_type_info[$r['discount_code']]['total_pcs'] += $pcs;
					$r['pcs']=$pcs;
					$r['pcs_allocation']='';    //set empty, avoid save into do_items
					
					$price_type[$do_branch_id] = $r['discount_code'];
					
					//calc ctn
					$ctn = $ctn_allocation[$do_branch_id];
					$price_type_info[$r['discount_code']]['total_ctn'] += $ctn;
					$r['ctn']=$ctn;
					$r['ctn_allocation']='';    //set empty, avoid save into do_items

					$r['price_type'] = $price_type;
					
					//get selling price
					$selling_price_allocation = unserialize($r['selling_price_allocation']);
					$r['selling_price'] = $selling_price_allocation[$do_branch_id];
					$r['selling_price_allocation']='';

					//stock balance 2
					$stock_balance2_allocation = unserialize($r['stock_balance2_allocation']);
					$r['stock_balance2'] = $stock_balance2_allocation[$do_branch_id];
					$r['stock_balance2_allocation']='';
					
					$row_qty = $ctn*$r['fraction']+$pcs;
				}else{
					//single branch
					$price_type_info[$r['discount_code']]['total_pcs'] += $r['pcs'];
					$price_type_info[$r['discount_code']]['total_ctn'] += $r['ctn'];
					
					$r['price_type'] = array(intval($do_branch_id)=>$r['discount_code']);

					$row_qty = $r['ctn']*$r['fraction']+$r['pcs'];
				}
							
				$row_amt = $r['line_amt'];
				$inv_amt = $r['inv_line_amt2'];

				$return_discount_code = $r['discount_code'];
				$price_type_info[$r['discount_code']]['total_inv_amt'] += $inv_amt;
				$price_type_info[$r['discount_code']]['total_amount'] += $row_amt;
				$price_type_info[$r['discount_code']]['total_qty'] += $row_qty;
				$items_type[$r['discount_code']][] = $r;
			}
			
			// doesn't need to split if DO items contain only one price type
			if(count($price_type_info)<=1){
				// get discount percent
				$sql = "select btd.*,tdt.code from branch_trade_discount btd
						left join trade_discount_type tdt on tdt.id=btd.trade_discount_id
						where btd.branch_id=".mi($form['do_branch_id'])." and tdt.code=".ms($return_discount_code);

				$q1 = $con->sql_query($sql) or die(mysql_error());
				$discount_percent = $con->sql_fetchfield('value');
				$con->sql_freeresult($q1);
				if($config['do_remark_add_profit']&&$form['do_branch_id']){
					if($form['remark']) $ret_rs['remark'] .= ", ";
					$ret_rs['remark'] .= "** PROFIT $discount_percent% (".$return_discount_code.") **";
				}
				if($config['do_split_auto_add_do_discount']&&$form['do_branch_id']){
					if(!$form['do_markup']){
						$ret_rs['do_markup'] = $discount_percent;
						$ret_rs['markup_type'] = 'down';
					} 
				}
				return $ret_rs;
			}

			$form['branch_id']=$branch_id;
			$form['last_update'] = 'CURRENT_TIMESTAMP';
			$form['added'] = 'CURRENT_TIMESTAMP';
			$form['user_id'] = $sessioninfo['id'];

			// always split and confirm
			$is_confirm = true;
			if ($is_confirm) $form['status'] = 1;

			$default_remark = $form['remark'];
			$count = 0;

			ksort($items_type);	// sort by price type
			
			foreach($items_type as $discount_code=>$items){
				// check and create branch_approval_history data
				$aprms = array();
				$aprms['dept_id'] = $form['dept_id'];
				$aprms['branch_id'] = $branch_id;
				$aprms['doc_amt'] = $price_type_info[$discount_code]['total_amount'];
				if(isset($price_type_info[$discount_code]['total_inv_amt'])) $aprms['doc_amt'] = $price_type_info[$discount_code]['total_inv_amt'];
				//$aprms['approval_history_id'] = $form['approval_history_id'];
				$ret = $this->doApprovalHandler($aprms, $form);
				$ret_rs['errm'] = $ret['errm'];
				$form['approval_history_id'] = $ret['approval_history_id'];
				$last_approval = $ret['last_approval'];
				$direct_approve_due_to_less_then_min_doc_amt = $ret['direct_approve_due_to_less_then_min_doc_amt'];
				unset($ret);
				
				// it contains no approval, revert back
				if($ret_rs['errm']) return $ret_rs;

				if ($last_approval) $form['approved'] = 1;

				if(!$form['create_type'])$form['create_type']=1;

				// get discount percent
				$sql = "select btd.*,tdt.code from branch_trade_discount btd
						left join trade_discount_type tdt on tdt.id=btd.trade_discount_id
						where btd.branch_id=".mi($do_branch_id)." and tdt.code=".ms($discount_code);
				$con->sql_query($sql) or die(mysql_error());
				$discount_percent = $con->sql_fetchfield('value');
				if($config['do_remark_add_profit']&&$do_branch_id){
					if($default_remark) $default_remark .= ", ";
					$form['remark'] = $default_remark."** PROFIT $discount_percent% (".$discount_code.") **";
				}

				$form['sheet_price_type'] = $discount_code;
				$form['total_pcs'] = $price_type_info[$discount_code]['total_pcs'];
				$form['total_ctn'] = $price_type_info[$discount_code]['total_ctn'];
				$form['total_qty'] = $price_type_info[$discount_code]['total_qty'];
				$form['total_amount'] = $price_type_info[$discount_code]['total_amount'];
				$form['sub_total_inv_amt'] = $form['total_inv_amt'] = $price_type_info[$discount_code]['total_inv_amt'];

				if($config['do_split_auto_add_do_discount']){
					if(!$form['default_do_markup']) $form['do_markup'] = $branch_trade_discount[$discount_code];
				}

				if($config['masterfile_enable_sa'] && $form['do_sa']) $form['mst_sa'] = serialize($form['do_sa']);
				else $form['mst_sa'] = "";
				
				$sql = "insert into do ".mysql_insert_by_field($form, array('branch_id', 'user_id', 'dept_id', 'status', 'approved', 'do_date', 'added', 'last_update','deliver_branch','total_pcs', 'total_ctn', 'total_qty', 'total_amount', 'remark','approval_history_id', 'do_branch_id', 'po_no', 'create_type', 'do_type','open_info','price_indicate','debtor_id','discount','total_inv_amt','do_markup','markup_type','exchange_rate', 'sub_total_inv_amt','sub_total_foreign_inv_amt','total_foreign_inv_amt','total_foreign_amount', 'sheet_price_type', 'mst_sa','allowed_user','no_use_credit_sales_cost', 'is_under_gst'));
				$con->sql_query($sql);
	
				$form['id'] = $con->sql_nextid();
				$do_id_arr[$form['id']]=$form['id'];

				foreach($items as $r){
					$upd['do_id']=$form['id'];
					$upd['branch_id']=$r['branch_id'];
					$upd['sku_item_id']=$r['sku_item_id'];
					$upd['artno_mcode']=$r['artno_mcode'];
					$upd['po_cost']=$r['po_cost'];
					$upd['cost']=$r['cost'];
					$upd['cost_price']=$r['cost_price'];
					$upd['foreign_cost_price']=$r['foreign_cost_price'];
					$upd['selling_price']=$r['selling_price'];
					$upd['uom_id']=$r['uom_id'];
					$upd['ctn']=$r['ctn'];
					$upd['pcs']=$r['pcs'];
					$upd['ctn_allocation']=$r['ctn_allocation'];
					$upd['pcs_allocation']=$r['pcs_allocation'];
					$upd['selling_price_allocation']=$r['selling_price_allocation'];
					$upd['price_type']= serialize($r['price_type']);
					$upd['stock_balance1'] = $r['stock_balance1'];
					$upd['stock_balance2'] = $r['stock_balance2'];
					$upd['stock_balance2_allocation'] = $r['stock_balance2_allocation'];
					$upd['item_discount'] = $r['item_discount'];
					$upd['dtl_sa'] = $r['dtl_sa'];
					$upd['price_indicate'] = $r['price_indicate'];
					$upd['gst_id'] = $r['gst_id'];
					$upd['gst_code'] = $r['gst_code'];
					$upd['gst_rate'] = $r['gst_rate'];
					
					$upd['display_cost_price_is_inclusive'] = $r['display_cost_price_is_inclusive'];
					$upd['display_cost_price'] = $r['display_cost_price'];
					$upd['bom_id'] = $r['bom_id'];
					$upd['bom_ref_num'] = $r['bom_ref_num'];
					$upd['bom_qty_ratio'] = $r['bom_qty_ratio'];
					
					$sql = "insert into do_items ".mysql_insert_by_field($upd);
					$con->sql_query($sql) or die(mysql_error());
				}

				// recalculate
				auto_update_do_all_amt($form['branch_id'], $form['id']);

				if ($is_confirm){

					$formatted=sprintf("%05d",$form[id]);
					//select report prefix from branch
					$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
					$r=$con->sql_fetchrow();

					log_br($sessioninfo['id'], 'DELIVERY ORDER', $form['id'], "Confirmed: (ID#".$r['report_prefix'].$formatted.", Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:".sprintf("%.2f",$form['total_amount']).")");

					if ($last_approval) {
						if($direct_approve_due_to_less_then_min_doc_amt)	$_REQUEST['direct_approve_due_to_less_then_min_doc_amt'] = 1;
						do_approval($form['id'], $branch_id, $form['status'], true, false);
					}
					else {
						$con->sql_query("update branch_approval_history set ref_id=$form[id] where id=$form[approval_history_id] and branch_id = $branch_id");
						$to = get_pm_recipient_list2($form['id'],$form['approval_history_id'],0,'confirmation',$branch_id,'do');
						send_pm2($to, "Delivery Order Approval (ID#$form[id])", "do.php?page=$form[do_type]&a=view&id=$form[id]&branch_id=$branch_id", array('module_name'=>'do'));
					}

				}
				else{
					$formatted=sprintf("%05d",$form[id]);
					//select report prefix from branch
					$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
					$r=$con->sql_fetchrow();

					log_br($sessioninfo['id'], 'DELIVERY ORDER', $form['id'], "Saved: (ID#".$r['report_prefix'].$formatted." ,Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:".sprintf("%.2f",$form['total_amount']).")");
				}
			}

			//reset value;
			unset($inv_amt, $items_type, $price_type_info, $form, $default_remark);
		}
		
		unset($deliver_branch, $branch_trade_discount);

		// delete old tmp do items
		if($use_tmp_tbl) $con->sql_query("delete from tmp_do_items where do_id=$do_id and branch_id = $branch_id and user_id = $sessioninfo[id]") or die(mysql_error());
		
		// hide original DO
		$con->sql_query("update do set active=0,status=4 where id=$do_id and branch_id=$branch_id") or die(mysql_error());
		
		// set split failed as false since system managed to split the DO by price type
		$ret_rs['split_failed'] = false;
		$ret_rs['do_id_list'] = $do_id_arr;
		
		return $ret_rs;
	}
	
	function doSendApprovalPM($form=array()){
		if(!$form) return;
		
		$formatted=sprintf("%05d", $form['id']);
		//select report prefix from branch
		$q1 = $con->sql_query("select report_prefix from branch where id = ".mi($form['branch_id']));
		$r=$con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		log_br($sessioninfo['id'], 'DELIVERY ORDER', $form['id'], "Confirmed: (ID#".$r['report_prefix'].$formatted.", Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:".sprintf("%.2f",$form['total_amount']).")");
		if ($form['last_approval'])	{
			if($form['direct_approve_due_to_less_then_min_doc_amt']) $_REQUEST['direct_approve_due_to_less_then_min_doc_amt'] = 1;
			do_approval($form['id'], $form['branch_id'], $form['status'], true);
		}
		else{
			$con->sql_query("update branch_approval_history set ref_id=".mi($form['id'])." where id=".mi($form['approval_history_id'])." and branch_id = ".mi($form['branch_id']));
			$to = get_pm_recipient_list2($form['id'],$form['approval_history_id'],0,'confirmation',$form['branch_id'],'do');
			send_pm2($to, "Delivery Order Approval (ID#$form[id])", "do.php?page=$form[do_type]&a=view&id=$form[id]&branch_id=".mi($form['branch_id']), array('module_name'=>'do'));
		}
	}
	
	function loadDriverInfo(){
		global $con, $sessioninfo;
		
		$q1 = $con->sql_query("select * 
						   from do 
						   where do.branch_id = ".mi($sessioninfo['branch_id'])." and do.approved=1 and do.active=1 and do.status=1 and do.checkout=1 and 
						   do.checkout_info != '' and do.checkout_info is not null and 
						   do.checkout_info not like '%:\"lorry_no\";s:0%' and do.checkout_info not like '%:\"name\";s:0%' and do.checkout_info not like '%:\"nric\";s:0%' 
						   order by last_update desc 
						   limit 1");

		$do_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$driver_info = unserialize($do_info['checkout_info']);
		$ret['lorry_no'] = $driver_info['lorry_no'];
		$ret['name'] = $driver_info['name'];
		$ret['nric'] = $driver_info['nric'];
		$ret['ok'] = 1;
		unset($do_info, $driver_info);
		
		return $ret;
	}
	
	function doGRNAutoGenerator($prms=array()){
		global $con, $config, $appCore;
		
		if(!$config['single_server_mode'] || $config['do_skip_generate_grn'] || !$prms) return;
		
		$q1=$con->sql_query("select * from do where branch_id=".mi($prms['branch_id'])." and id = ".mi($prms['id']));
		$r1 = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$r1) return;
		
		$is_under_gst = mi($r1['is_under_gst']);
		
		if($r1['do_markup']){
			$r1['do_markup_arr'] = explode("+", $r1['do_markup']);
			if($r1['markup_type']=='down'){
				$r1['do_markup_arr'][0] *= -1;
				$r1['do_markup_arr'][1] *= -1;
			}
		}
		
		$r1['checkout_info'] = unserialize($r1['checkout_info']);

		if($r1['do_branch_id']>0 && !$r1['open_info'] && $r1['do_type']!='credit_sales'){
			if($r1['do_type']=='transfer'){
				$from_bcode = get_branch_code($r1['branch_id']);
				// search internal code
				$q2=$con->sql_query("select id from vendor where internal_code=".ms($from_bcode));
				$vendor=$con->sql_fetchrow($q2);
				$con->sql_freeresult($q2);
				
				if(!$vendor){			// search vendor code		
					$q2=$con->sql_query("select id from vendor where code=".ms($from_bcode));
					$vendor=$con->sql_fetchrow($q2);
					$con->sql_freeresult($q2);
				}
			}

			$q2=$con->sql_query("select do.do_date, sku_items.artno, sku_items.mcode, sku.sku_code, sku_items.id as id, di.user_id,di.ctn, di.pcs, di.id as do_item_id , 
			sku_items.description as description, uom.id as uom_id, uom.fraction as uom_fraction, di.cost_price as cost_price, sku_items.sku_item_code, uom.code as uom_code, c2.description as dept_name, 
			c2.id as dept_id, di.sku_item_id as sku_item_id, di.artno_mcode as artno_mcode,do.do_type,do.total_rcv,di.rcv_pcs,
			di.gst_id, di.gst_code, di.gst_rate, di.inv_line_gross_amt2, di.inv_line_gst_amt2, di.inv_line_amt2, do.dept_id as do_dept_id
			from do_items di
			left join do on do_id = do.id and di.branch_id = do.branch_id
			left join sku_items	on di.sku_item_id = sku_items.id
			left join sku on sku_items.sku_id = sku.id
			left join uom on di.uom_id = uom.id
			left join category c1 on sku.category_id=c1.id
			left join category c2 on c1.department_id = c2.id
			where di.do_id=".mi($r1['id'])." and di.branch_id=".mi($r1['branch_id'])." group by dept_name, di.id order by di.id");
			while ($r2 = $con->sql_fetchassoc($q2)){
				if($config['do_approval_by_department'] && $r2['do_dept_id']){
					$dept_id = $r2['do_dept_id'];
				}
				
				// check if dept ID is empty, assign the dept from first SKU item as all item's dept ID
				if(!$dept_id) $dept_id = $r2['dept_id'];
				
				$temp[$dept_id][]=$r2;
			}
			$con->sql_freeresult($q2);

			$items = array();
			foreach($temp as $k=>$v){
				$total_amt_before_gst = $total_gst_amt = $total_amt_incl_gst = $total_do_amt_incl_gst = $total_do_gst_amt = 0;
				
				foreach($temp[$k] as $k1=>$v1){
					//echo "$v1[sku_code]<br>";
					$cost = $v1['cost_price'];			
					$gst_id = $v1['gst_id'];
					// do markup
					if($r1['do_markup_arr'][0]){
						$cost = $cost * (1+($r1['do_markup_arr'][0]/100));
					}
					if($r1['do_markup_arr'][1]){
						$cost = $cost * (1+($r1['do_markup_arr'][1]/100));
					}
						
					if($v1['do_type']=='transfer'&&$config['do_use_rcv_pcs']){
						$total_pcs[$k]+=$v1['rcv_pcs'];
					}else{
						$total_ctn[$k]+=$v1['ctn'];
						$total_pcs[$k]+=$v1['pcs'];
					}
					$cost = round($cost, $config['global_cost_decimal_points']);
					
					$total_amt[$k]+=round(($v1['ctn']*$cost)+($cost/$v1['uom_fraction']*$v1['pcs']), 2);
				}
				
				// call appCore to generate new ID
				unset($new_id);
				$new_id = $appCore->generateNewID("grr", "branch_id = ".mi($r1['do_branch_id']));
				
				if(!$new_id) die("Unable to generate new ID from appCore!");
				
				$grr = array();
				$grr['id'] = $new_id;
				$grr['branch_id']=$r1['do_branch_id'];
				$grr['user_id']=$r1['user_id'];
				$grr['rcv_by']=$r1['checkout_by'];
				$grr['rcv_date']=$r1['do_date'];
				$grr['grr_ctn']=$total_ctn[$k];	
				$grr['grr_amount']=$total_amt[$k];
				$grr['added']='CURRENT_TIMESTAMP()';
				$grr['grr_pcs']=$total_pcs[$k];
				$grr['department_id']=$k;
				$grr['status']=1;
				$grr['transport']=$r1['checkout_info']['lorry_no'];
				$grr['is_under_gst'] = $is_under_gst;
				
				if($r1['do_type']=='transfer' && $vendor){
					$grr['vendor_id']=$vendor['id'];
				}

				$con->sql_query("insert into grr " . mysql_insert_by_field($grr));
				$grr_id = $con->sql_nextid();
				
				// call appCore to generate new ID
				unset($new_id);
				$new_id = $appCore->generateNewID("grn", "branch_id = ".mi($r1['do_branch_id']));
				
				if(!$new_id) die("Unable to generate new ID from appCore!");
				
				$grn = array();
				$grr['id'] = $new_id;
				$grn['branch_id']=$r1['do_branch_id'];
				$grn['user_id']=$r1['checkout_by'];
				$grn['grr_id']=$grr_id;
				$grn['vendor_id']=$grr['vendor_id'];
				//$grn['grr_item_id']=$grr_items_id;								
				$grn['amount']=$total_amt[$k];
				$grn['status']=1;
				$grn['authorized']=1;
				$grn['approved']=1;
				$grn['added']='CURRENT_TIMESTAMP()';
				$grn['final_amount']=$total_amt[$k];
				$grn['department_id']=$k;
				$grn['is_ibt']=1;
				$grn['from_branch_id']=$r1['branch_id'];
				if($config['use_grn_future']){
					$grn['is_future'] = 1;
				}
				$grn['is_under_gst'] = $is_under_gst;
	
				//auto insert into grn							
				$con->sql_query("insert into grn " . mysql_insert_by_field($grn));				
				$grn_id = $con->sql_nextid();
				
				$grn_items = array();
				$grn_items['branch_id']=$r1['do_branch_id'];
				$grn_items['grn_id']=$grn_id;
																			
				foreach($temp[$k] as $k1=>$v1){
					$row_amt_before_gst = $row_gst_amt = $row_amt_incl_gst = 0;
					
					$grn_items['sku_item_id']=$v1['sku_item_id'];
					$grn_items['artno_mcode']=$v1['artno_mcode'];
					$grn_items['uom_id']=$v1['uom_id'];
					$cost = $v1['cost_price'];
					if($r1['do_markup_arr'][0]){
						$cost = $cost * (1+($r1['do_markup_arr'][0]/100));
					}
					if($r1['do_markup_arr'][1]){
						$cost = $cost * (1+($r1['do_markup_arr'][1]/100));
					}
					$cost = round($cost, $config['global_cost_decimal_points']);
					$grn_items['cost']=$cost;
					
					if($v1['do_type']=='transfer'&&$config['do_use_rcv_pcs']){
						$grn_items['pcs']=$v1['rcv_pcs'];
					}else{
						$grn_items['ctn']=$v1['ctn'];
						$grn_items['pcs']=$v1['pcs'];
					}
					
					$q3=$con->sql_query("select if(sp.price is null, si.selling_price, sp.price) as selling 
					from sku_items si
					left join sku on sku.id=si.sku_id 
					left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id=".mi($grn_items['branch_id'])."
					where si.id=".mi($v1['sku_item_id']));
					$r3 = $con->sql_fetchassoc($q3);
					$con->sql_freeresult($q3);
					
					$grn_items['selling_uom_id']=1;
					$grn_items['selling_price']=$r3['selling'];
					
					// find price before gst
					$row_amt_before_gst = round(($v1['ctn']*$cost)+($cost/$v1['uom_fraction']*$v1['pcs']), 2);
					if($config['enable_gst']){
						if($grn['is_under_gst']){
							// get sku is inclusive
							$is_sku_inclusive = get_sku_gst("inclusive_tax", $grn_items['sku_item_id']);
							// get sku original output gst
							$sku_original_output_gst = get_sku_gst("output_tax", $grn_items['sku_item_id']);
							
							if($is_sku_inclusive == 'yes'){
								// is inclusive tax
								$grn_items['gst_selling_price'] = $grn_items['selling_price'];
								
								// find the price before tax
								$sp = $grn_items['selling_price'];
								$gst_tax_price = round($sp / ($sku_original_output_gst['rate']+100) * $sku_original_output_gst['rate'], 2);
								$price_included_gst = $sp;
								$sp = $price_included_gst - $gst_tax_price;
								$grn_items['selling_price'] = $sp;
							}else{
								// is exclusive tax
								$gst_amt = round($grn_items['selling_price'] * $sku_original_output_gst['rate'] / 100, 2);
								$grn_items['gst_selling_price'] = round($grn_items['selling_price'] + $gst_amt, 2);
							}
						
							// get gst output tax
							$output_tax = get_sku_gst("output_tax", $grn_items['sku_item_id']);
							if($output_tax){
								$grn_items['selling_gst_id'] = $output_tax['id'];
								$grn_items['selling_gst_code'] = $output_tax['code'];
								$grn_items['selling_gst_rate'] = $output_tax['rate'];
							}
							
							// input tax
							$input_tax = get_sku_gst("input_tax", $grn_items['sku_item_id']);
							if($input_tax){
								$grn_items['gst_id'] = $input_tax['id'];
								$grn_items['gst_code'] = $input_tax['code'];
								$grn_items['gst_rate'] = $input_tax['rate'];
							}
						}
						
						if($grn_items['gst_rate']>0){
							$pcs_gst_amt = $cost*$grn_items['gst_rate']/100;
							$row_gst_amt = round(($pcs_gst_amt/$v1['uom_fraction']*$v1['pcs']), 2);
						}
						$row_amt_incl_gst = round($row_amt_before_gst + $row_gst_amt, 2);
						
						// total sum up for GRN
						$total_amt_before_gst += $row_amt_before_gst;
						$total_gst_amt += $row_gst_amt;
						$total_amt_incl_gst += $row_amt_incl_gst;
						
						// total sum up for GRR
						$total_do_gst_amt += $v1['inv_line_gst_amt2'];
						$total_do_amt_incl_gst += $v1['inv_line_amt2'];
					}
						
					
					// preset the item group = 3
					if($config['use_grn_future']){
						$grn_items['item_group'] = 3;
					}
					
					// call appCore to generate new ID
					unset($new_id);
					$new_id = $appCore->generateNewID("grn_items", "branch_id = ".mi($r1['do_branch_id']));
					
					if(!$new_id) die("Unable to generate new ID from appCore!");
					
					$grn_items['id'] = $new_id;
	
					//auto insert into grn							
					$con->sql_query("insert into grn_items " . mysql_insert_by_field($grn_items));			
					
					// configure items to insert GRR items by GST code
					$gst_id = mi($grn_items['gst_id']);
					//$items[$k][$gst_id]['amount']+=$row_amt_before_gst+$row_gst_amt;
					$items[$k][$gst_id]['amount']+=$v1['inv_line_amt2'];
					//$items[$k][$gst_id]['gst_amount']+=$row_gst_amt;
					$items[$k][$gst_id]['gst_amount']+=$v1['inv_line_gst_amt2'];
					$items[$k][$gst_id]['gst_id']=$grn_items['gst_id'];
					$items[$k][$gst_id]['gst_code']=$grn_items['gst_code'];
					$items[$k][$gst_id]['gst_rate']=$grn_items['gst_rate'];
					$items[$k][$gst_id]['ctn']+=$v1['ctn'];
					$items[$k][$gst_id]['pcs']+=$v1['pcs'];
				}
				
				// need to loop by item amount since will have different tax code
				foreach($items[$k] as $gst_id=>$item_r){
					// call appCore to generate new ID
					unset($new_id);
					$new_id = $appCore->generateNewID("grr_items", "branch_id = ".mi($r1['do_branch_id']));
					
					if(!$new_id) die("Unable to generate new ID from appCore!");
					
					$grr_items = array();
					$grr_items['id'] = $new_id;
					$grr_items['grr_id']=$grr_id;
					$grr_items['branch_id']=$r1['do_branch_id'];
					$grr_items['doc_no']=$r1['do_no'];
					$grr_items['doc_date']=$r1['do_date'];
					$grr_items['type']='DO';
					$grr_items['ctn']=$item_r['ctn'];
					$grr_items['pcs']=$item_r['pcs'];	
					$grr_items['amount']=$item_r['amount'];
					$grr_items['remark']=$r1['remark'];
					$grr_items['grn_used']=1;
					
					if($is_under_gst){
						$grr_items['gst_amount'] = $item_r['gst_amount'];
						$grr_items['gst_id'] = $item_r['gst_id'];
						$grr_items['gst_code'] = $item_r['gst_code'];
						$grr_items['gst_rate'] = $item_r['gst_rate'];
					}

					//auto insert into grr_items							
					$con->sql_query("insert into grr_items " . mysql_insert_by_field($grr_items));				
					if(!$grr_items_id) $grr_items_id = $con->sql_nextid();
				}
				
				// update total_selling
				$q4 = $con->sql_query("select sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)/sell_uom.fraction*grn_items.selling_price) as sell
				from grn_items
				left join uom sell_uom on grn_items.selling_uom_id=sell_uom.id
				left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
				where grn_id=".mi($grn_id)." and branch_id=".mi($grn['branch_id'])) or die(mysql_error());
				$t = $con->sql_fetchassoc($q4);
				$total_selling = doubleval($t['sell']);
				$con->sql_freeresult($q4);
				
				$upd_grn = array();
				$upd_grn['total_selling'] = $total_selling;
				$upd_grn['grr_item_id'] = $grr_items_id;
				
				// check if have gst variance between GRN vs DO
				$rounding_variance = $total_do_gst_amt - $total_gst_amt;
				if($rounding_variance != 0) $upd_grn['rounding_amt'] = $rounding_variance;

				if($config['enable_gst']){
					$upd_grn['amount'] = $total_amt_before_gst;
					//$upd_grn['final_amount'] = $upd_grn['account_amount'] = $total_amt_incl_gst;
					$upd_grn['final_amount'] = $upd_grn['account_amount'] = $total_do_amt_incl_gst;
				}
				
				
				// update back grn
				$con->sql_query("update grn set ".mysql_update_by_field($upd_grn)." where id=".mi($grn_id)." and branch_id=".mi($grn['branch_id']));

				if($config['enable_gst']){
					// no longer need to update since it is inserted above
					//$upd_grr_items = array();
					//$upd_grr_items['amount'] = $total_amt_incl_gst;
					//$upd_grr_items['gst_amount'] = $total_gst_amt;
					
					// update back grr_items
					//$con->sql_query("update grr_items set ".mysql_update_by_field($upd_grr_items)." where id=$grr_items_id and branch_id=$grr_items[branch_id]");
					
					$upd_grr = array();
					//$upd_grr['grr_amount'] = $total_amt_incl_gst;
					//$upd_grr['grr_gst_amount'] = $total_gst_amt;
					$upd_grr['grr_amount'] = $total_do_amt_incl_gst;
					$upd_grr['grr_gst_amount'] = $total_do_gst_amt;
					
					// update back grr
					$con->sql_query("update grr set ".mysql_update_by_field($upd_grr)." where id=".mi($grr_id)." and branch_id=".mi($grr['branch_id']));
				}
			}
		}
	}
	
	public function createMarketplaceDO($marketplace_order_id){
		global $con, $appCore, $sessioninfo;
		
		$marketplace_order_id = mi($marketplace_order_id);
		if($marketplace_order_id<=0)	return array("error" => "Invalid Marketplace Order ID");
		
		// Get Order
		$con->sql_query("select * from marketplace_order where id=$marketplace_order_id");
		$order = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Order Not Found
		if(!$order)	return array("error" => "Order Not Found.");
		
		// Order Already Cancelled
		if(!$order['active'])	return array("error" => "Order is already Cancelled.");
		
		// Order Already Completed
		if($order['completed'])	return array("error" => "Order is already Completed.");
		
		// Get Shipping Item Settings
		$con->sql_query("select * from marketplace_settings where setting_name='shipping_item_code'");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$shipping_item_code = trim($tmp['setting_value']);
		$shipping_item_sid = 0;
		$shipping_item_desc = '';
		
		if(preg_match("/^28/", $shipping_item_code) && strlen($shipping_item_code) == 12){
			$con->sql_query("select id from sku_items where sku_item_code=".ms($shipping_item_code));
			$tmp_si = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($tmp_si){
				// Is Actual SKU
				$shipping_item_sid = mi($tmp_si['id']);
			}
		}
		if(!$shipping_item_sid){
			// Shipping Item Not Actual SKU, Load Description
			$con->sql_query("select * from marketplace_settings where setting_name='shipping_item_desc'");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$shipping_item_desc = trim($tmp['setting_value']);
		}
		
		// Marketplace DO Owner
		$con->sql_query("select * from marketplace_settings where setting_name='do_user_id'");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$do_user_id = mi($tmp['setting_value']);
		if($do_user_id<=0)	$do_user_id = $sessioninfo ? $sessioninfo['id'] : 1;
		
		$uomEach = $appCore->uomManager->getUOMForEach();
		$uom_id = $uomEach['id'];
		$do_info_list = array();
		
		/// Get Order DO
		$q1 = $con->sql_query("select * from marketplace_order_do where marketplace_order_id=$marketplace_order_id and active=1 and do_id=0 order by id");
		while($order_do = $con->sql_fetchassoc($q1)){
			$do_sequence = mi($order_do['do_sequence']);
			
			// Get Order DO Items
			$order_do_items_list = array();
			$q2 = $con->sql_query("select * from marketplace_order_do_items where marketplace_order_id=$marketplace_order_id and do_sequence=$do_sequence and active=1 and qty>0 order by item_id");
			while($r = $con->sql_fetchassoc($q2)){
				$order_do_items_list[$r['id']] = $r;
			}
			$con->sql_freeresult($q2);
			
			if($order_do['do_id']){
				// Already have DO
			}else{
				//create do approval history
				$prms = array();
				$prms['branch_id'] = $order_do['branch_id'];
				$ret = $this->doApprovalHandler($prms);
				if($ret['errm']){
					return array("error" => $ret['errm']);
				}
				
				// No DO Yet
				if($order['marketplace_name']) $marketplace_name_remark = " (".ucase($order['marketplace_name']).")";
				$tmp_generate_do = array();
				$tmp_generate_do['guid'] = $appCore->newGUID();
				$tmp_generate_do['branch_id'] = $order_do['branch_id'];
				$tmp_generate_do['do_type'] = 'open';
				$tmp_generate_do['do_date'] = $order['order_date'];
				$tmp_generate_do['user_id'] = $do_user_id;
				$tmp_generate_do['discount'] = $order_do['discount'];
				$tmp_generate_do['remark'] = "Order No".$marketplace_name_remark.": ".$order['order_no'];
				$tmp_generate_do['open_info'] = serialize(array('name' => $order['cust_name'], 'address' => $order['cust_address']));
				$tmp_generate_do['added'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("insert into tmp_generate_do ".mysql_insert_by_field($tmp_generate_do));
				
				// Loop SKU
				$sequence = 0;
				foreach($order_do_items_list as $r){
					$sequence++;
					
					$tmp_generate_do_items = array();
					$tmp_generate_do_items['guid'] = $appCore->newGUID();
					$tmp_generate_do_items['gen_do_guid'] = $tmp_generate_do['guid'];
					$tmp_generate_do_items['sku_item_id'] = $r['sku_item_id'];
					$tmp_generate_do_items['uom_id'] = $uom_id;
					$tmp_generate_do_items['ctn'] = 0;
					$tmp_generate_do_items['pcs'] = $r['qty'];
					$tmp_generate_do_items['added'] = 'CURRENT_TIMESTAMP';
					$tmp_generate_do_items['sequence'] = $sequence;
					$tmp_generate_do_items['cost_price'] = $r['unit_price'];
					$con->sql_query("insert into tmp_generate_do_items ".mysql_insert_by_field($tmp_generate_do_items));
				}
				
				// Got Shipping Fee
				if($order_do['shipping_fee']>0){
					if($shipping_item_sid > 0){
						// Use Actual SKU for shipping item
						$sequence++;
					
						$tmp_generate_do_items = array();
						$tmp_generate_do_items['guid'] = $appCore->newGUID();
						$tmp_generate_do_items['gen_do_guid'] = $tmp_generate_do['guid'];
						$tmp_generate_do_items['sku_item_id'] = $shipping_item_sid;
						$tmp_generate_do_items['uom_id'] = $uom_id;
						$tmp_generate_do_items['ctn'] = 0;
						$tmp_generate_do_items['pcs'] = 1;
						$tmp_generate_do_items['added'] = 'CURRENT_TIMESTAMP';
						$tmp_generate_do_items['sequence'] = $sequence;
						$tmp_generate_do_items['cost_price'] = $order_do['shipping_fee'];
						$con->sql_query("insert into tmp_generate_do_items ".mysql_insert_by_field($tmp_generate_do_items));
					}else{
						// Use Open Item
						$tmp_generate_do_open_items = array();
						$tmp_generate_do_open_items['guid'] = $appCore->newGUID();
						$tmp_generate_do_open_items['gen_do_guid'] = $tmp_generate_do['guid'];
						$tmp_generate_do_open_items['artno_mcode'] = $shipping_item_code;
						$tmp_generate_do_open_items['description'] = $shipping_item_desc;
						$tmp_generate_do_open_items['pcs'] = 1;
						$tmp_generate_do_open_items['added'] = 'CURRENT_TIMESTAMP';
						$tmp_generate_do_open_items['sequence'] = 1;
						$tmp_generate_do_open_items['cost_price'] = $order_do['shipping_fee'];
						$con->sql_query("insert into tmp_generate_do_open_items ".mysql_insert_by_field($tmp_generate_do_open_items));
					}
				}
				
				// Create DO
				$do_id = mi($this->createDOFromTMP($tmp_generate_do['guid']));
				if($do_id > 0){
					$do_info_list[$do_id] = array('do_sequence'=> $do_sequence, 'do_id' => $do_id);
				}
				
				
				//update do approval
				$aid = mi($ret['approval_history_id']);
				$con->sql_query("update branch_approval_history set status=1,approvals ='|' where id = $aid and branch_id = $order_do[branch_id]");
				
				//insert branch_approval_history_items
				$upd2 = array();
				$upd2['branch_id'] = $order_do['branch_id'];
				$upd2['approval_history_id'] = $aid;
				$upd2['user_id'] = $do_user_id;
				$upd2['status'] = 1;
				$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd2)) or die(mysql_error());
				
				// Shipping Method and Tracking Code
				$upd = array();
				$upd['do_id'] = $do_id;
				$upd['last_update'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("update marketplace_order_do set ".mysql_update_by_field($upd)." where id=".mi($order_do['id']));
				
				$upd = array();
				$upd['approval_history_id'] = $aid;
				$upd['status'] = 1;
				$upd['approved'] = 1;
				$upd['is_mkt'] = 1;
				$upd['do_branch_id'] = 0;
				$do_no = $this->assign_do_no($do_id, $order_do['branch_id']);
				$upd['do_no'] = $do_no;
				if($order_do['shipping_provider']){
					$upd['shipment_method'] = $order_do['shipping_provider'];
				}
				if($order_do['tracking_code']){
					$upd['tracking_code'] = $order_do['tracking_code'];
				}
				if($order_do['mkt_inv_no']){
					$upd['mkt_inv_no'] =  $order_do['mkt_inv_no'];
				}
				
				if($upd){
					$con->sql_query("update do set ".mysql_update_by_field($upd)." where branch_id=".mi($order_do['branch_id'])." and id=$do_id");
				}
			}
		}
		$con->sql_freeresult($q1);
		
		if(!$do_info_list){
			return array("error" => "Create DO Failed.");
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['do_info_list'] = $do_info_list;
		
		return $ret;
	}
	
	public function assign_do_no($do_id, $branch_id){
		global $con;
		$type_postfix_list = array('transfer'=>'','credit_sales'=>'/D','open'=>'/C');
		
		// do type
		$con->sql_query("select do_type from do where id=$do_id and branch_id=$branch_id") or die(mysql_error());
		$do_type = $con->sql_fetchfield('do_type');
		$type_postfix = $type_postfix_list[$do_type];
		$con->sql_freeresult();
		
		// report prefix
		$con->sql_query("select report_prefix, ip from branch where id=$branch_id");
		$report_prefix = $con->sql_fetchrow();
		$con->sql_freeresult();
		
		// check whether already have do_no
		$con->sql_query("select do_no from do where  branch_id=$branch_id and id=$do_id and do_no like ".ms($report_prefix[0].'%')." and do_type=".ms($do_type)) or die(mysql_error());
		$temp = $con->sql_fetchrow();
		$con->sql_freeresult();
		if($temp)   return $temp['do_no'];

		// lookup for max length of do no
		$con->sql_query("select max(length(do_no)) as mx_lgth from do where branch_id = $branch_id and do_type=".ms($do_type)." and do_no like '$report_prefix[0]%'");
		$max_length = mi($con->sql_fetchfield(0));
		$con->sql_freeresult();
		
		if($max_length > 0) $filter = " and length(do_no) >= ".mi($max_length);
		$con->sql_query("select max(do_no) as mx from do where branch_id = $branch_id and do_type=".ms($do_type)." and do_no like '$report_prefix[0]%'".$filter);
		$r = $con->sql_fetchrow();
		$con->sql_freeresult();
		
		if (!$r)
			$n = 1;
		else{
			list($num,$dummy) = explode("/",$r[0]);
			$n = preg_replace("/^".$report_prefix[0]."/","", $num)+1;
		}
			

		$do_no = $report_prefix[0] . sprintf("%05d", $n).$type_postfix;
		while(!$con->sql_query("update do set do_no='$do_no', approved=1, last_update=CURRENT_TIMESTAMP where id=$do_id and branch_id = $branch_id",false,false)){
			$n++;
			$do_no = $report_prefix[0] . sprintf("%05d", $n).$type_postfix;
		}
		return $do_no;
	}
	
	public function load_do_checkout_img($do_id, $branch_id){
		global $sessioninfo;
		
		$id = mi($do_id);
		$bid = mi($branch_id);
		if($sessioninfo['branch_id'] == $bid || $config['single_server_mode']){
			$server_path = '';
		}else{
			// manually get server path if user does not provide
			$server_path = get_image_path($bid);
		}
		
		if($server_path)	$server_path .= "/";
		
		$image_list = array();
		$attch_folder = 'attch';
		$do_checkout_folder = 'do_checkout_img';
		$abs_path = $server_path.$attch_folder."/".$do_checkout_folder."/".$bid."/".$id;
		if($abs_path){
			foreach(array_merge(glob("$abs_path/*.[jJ][pP][gG]"),glob("$abs_path/*.[jJ][pP][eE][gG]")) as $f){
				$f = str_replace("$abs_path/", "", $f);
				$image_list[] = $server_path.$attch_folder."/".$do_checkout_folder."/".$bid."/".$id."/".$f;
			}
		}
		return $image_list;
	}

	//export DO items 
	public function export_do($id, $branch_id){
		global $con, $sessioninfo, $config;
	
		$got_item = false;
		$form = $_REQUEST;
	
		echo $form['oi'];
		//header
		$link_code_name = $config['link_code_name'] ? $config['link_code_name'] : 'Link Code';
		$header_array = array('ARMS Code', 'Mcode', 'Art-no', $link_code_name, 'UOM', 'CTN', 'PCS','Total Invoice Price');
	
		//select report prefix from branch
		$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
		$prefix=$con->sql_fetchrow();
		$con->sql_freeresult();
		$report_prefix = $prefix['report_prefix'];
	
		$formatted=sprintf("%05d",$id);
		$document_no = $report_prefix.$formatted;
	
		$con->sql_query("select do_no from do where id = ".mi($id)." and branch_id =".mi($branch_id));
		$do=$con->sql_fetchrow();
		$con->sql_freeresult();
		$do_no = $do['do_no'];

		if(strpos($do_no, '/'))
		{
			$do_no = str_replace('/', '_', $do_no);
		}
		$filename = 'DO_Export_'.$do_no.'.csv';
		$fp = fopen($filename, 'w');
	
		if($branch_id && $id){
			$sql = "select si.sku_item_code, si.mcode, si.artno, si.link_code, uom.code as code, di.ctn, di.pcs, di.inv_line_amt
			from do_items di 
			left join do on do.id=di.do_id and do.branch_id=di.branch_id
			left join sku_items si on si.id = di.sku_item_id
			left join uom on uom.id = di.uom_id
			where di.do_id=$id and di.branch_id=$branch_id";
			$q1 = $con->sql_query($sql);
			if ($con->sql_numrows($q1)>0) {
				fputcsv($fp, $header_array);
				$got_item = true;
				while($r = $con->sql_fetchassoc($q1)){
					$arr = array();
					$arr[] = $r['sku_item_code'];
					$arr[] = $r['mcode'];
					$arr[] = $r['artno'];
					$arr[] = $r['link_code'];
					$arr[] = $r['code'];
					$arr[] = $r['ctn'];
					$arr[] = $r['pcs'];
					$arr[] = $r['inv_line_amt'];
					fputcsv($fp, $arr);
				}
			}
			//open item export
			$sql2 = "select doi.artno_mcode,doi.ctn,doi.pcs,uom.code as code,doi.inv_line_amt
			from do_open_items doi 
			left join do on do.id=doi.do_id and do.branch_id=doi.branch_id
			left join uom on uom.id = doi.uom_id
			where doi.do_id=$id and doi.branch_id=$branch_id";
			$q2 = $con->sql_query($sql2);
			if ($con->sql_numrows($q2)>0) {
				$got_item = true;
				while($r = $con->sql_fetchassoc($q2)){
					$arr2 = array();
					$arr2[] = "";
					$arr2[] = "";
					$arr2[] = $r['artno_mcode'];
					$arr2[] = "";
					$arr2[] = $r['code'];
					$arr2[] = $r['ctn'];
					$arr2[] = $r['pcs'];
					$arr2[] = $r['inv_line_amt'];


					fputcsv($fp, $arr2);
				}
			}

			$con->sql_freeresult($q1);
			$con->sql_freeresult($q2);
			fclose($fp);
		}
	
		if ($got_item) {
			log_br($sessioninfo['id'], 'DO', $id, "Export DO items to CSV File");
			header('Content-Type: application/msexcel');
			header('Content-Disposition: attachment;filename='.$filename);
			print file_get_contents($filename);
		}
		unlink($filename);
	
		if (!$got_item){
			js_redirect("No DO items Data." .$id . $branch_id, $_SERVER['PHP_SELF']);
		}
		exit;
	}
}
?>