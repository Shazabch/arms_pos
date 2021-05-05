{*
5/8/2015 2:33 PM Andy
- Enhanced to fix templates show js error when display using ajax.

6/3/2015 6:03 PM Andy
- Fix rounding calculation.

6/19/2015 3:41 PM Andy
- Fix cost price rounding issue when no GST.

7/29/2015 4:18 PM Joo Chia
- Create new branch quantity form popup to allow user to key in quantity.

10/6/2015 12:22 PM Andy
- Fix gst amt rounding issue.

11/17/2015 6:04 PM Andy
- Enhanced to have copy qty by branch group.

12/14/2015 2:44 PM DingRen
- show from branch stock balance on Qty Form
- show to branch stock balance on Qty Form

2/16/2016 3:40 PM Andy
- Improve the Qty Form performance.

5/31/2017 11:16 AM Justin
- Added 2 new functions "qty_changed" and "update_bom_package_qty".

3/12/2019 2:16 PM Andy
- Enhanced to have "Invoice Amount Adjust".

12/3/2019 5:54 PM William
- Added round 2 decimal to discount amount.
*}
<script>

var doc_readonly = int('{$readonly}');

{literal}
if(typeof(do_use_rcv_pcs) == "undefined")	do_use_rcv_pcs = 0;
if(typeof(do_enable_cash_sales_rounding) == "undefined")	do_enable_cash_sales_rounding = 0;
if(typeof(masterfile_branch_region) == "undefined")	masterfile_branch_region = 0;
if(typeof(consignment_multiple_currency) == "undefined")	consignment_multiple_currency = 0;

DO_MODULE = {
	f: undefined,
	initilize: function(){
		this.f = document.f_a;
		if(!doc_readonly){
			BRANCH_QTY_FORM_DIALOG.initilize();
		}
	},
	// function to get cost price decimal
	get_display_cost_price_round_decimal: function(){
		var price_indicate = getRadioValue(this.f['price_indicate']);
		return (price_indicate == 1 || price_indicate == 3) ? global_cost_decimal_points : 2;
	},
	// function when user toggle price is inclusive tax
	display_cost_price_is_inclusive_changed: function(item_id){
		this.item_row_changed(item_id);
	},
	// function when user change display cost price
	display_cost_price_changed: function(item_id){
		this.item_row_changed(item_id);
	},
	// function when user change cost price
	cost_price_changed: function(item_id, need_round){
		if(need_round){
			var round_decimal = this.get_display_cost_price_round_decimal();
			mf(this.f['cost_price['+item_id+']'], round_decimal);
		}
		this.item_row_changed(item_id);
	},
	// function to calculate real cost price from display cost price
	calculate_real_cost_price: function(item_id){
		//console.log('calculate_real_cost_price');
		// get the display cost price
		var inp_display_cost_price = this.f['display_cost_price['+item_id+']'];
		// round price to 4
		mf(inp_display_cost_price, 4);
		
		var display_cost_price = float(round(inp_display_cost_price.value,4));
		
		// not allow negative
		if(display_cost_price<0){
			display_cost_price = 0;
			inp_display_cost_price.value = 0;
		}
		var cost_price = display_cost_price;
		
		// check display cost price inclusive
		if(this.f['display_cost_price_is_inclusive['+item_id+']'].checked){
			// is inclusive tax
			// calculate before tax price
			var gst_rate = float(this.f["gst_rate["+item_id+"]"].value);
			if(gst_rate>0){
				cost_price = display_cost_price / (100+gst_rate) * 100;
			}
			$('div_cost_price_info-'+item_id).show();
		}else{
			// exclusive tax
			$('div_cost_price_info-'+item_id).hide();
		}
		
		// display cost price to label
		this.f['cost_price['+item_id+']'].value = cost_price;
		$('span_cost_price_label-'+item_id).update(round(cost_price,4));
	},
	// item row changed
	item_row_changed: function(item_id){
		//console.log('item_row_changed');
		// calculate real cost price
		if(is_under_gst){
			this.calculate_real_cost_price(item_id);
		}
		
		// recalculate row total
		this.item_row_recal(item_id);
	},
	// function to recal item row
	item_row_recal: function(item_id){
		row_recalc(item_id);
	},
	// function when user change row gst
	item_gst_changed: function(item_id){
		// update the selected gst
		update_selected_gst(item_id);
			
		// recalculate row
		this.item_row_changed(item_id);
	},
	// function when user change item uom
	item_uom_changed: function(item_id){
		var sel = $('sel_uom'+item_id);
		var a = sel.value.split(",");
		var old_fraction = float($('uom_fraction'+item_id).value);
		var old_cost;
		var new_cost;
		var round_decimal = this.get_display_cost_price_round_decimal();
		
		// check old fraction
		if(is_under_gst){
			old_cost=float(this.f['display_cost_price['+item_id+']'].value)/old_fraction;
			new_cost=old_cost*float(a[1]);
			
			// change new cost
			this.f['display_cost_price['+item_id+']'].value = round(new_cost, round_decimal);
		}else{
			
			old_cost=float(this.f['cost_price['+item_id+']'].value)/old_fraction;
			new_cost=old_cost*float(a[1]);
			
			// change new cost
			this.f['cost_price['+item_id+']'].value = round(new_cost, round_decimal);
		}
		
		
		// foreign currency
		if(consignment_modules && consignment_multiple_currency && do_type =='transfer'){
			var old_foreign_cost=float($('foreign_cost_price_'+item_id).value)/old_fraction;
			var new_foreign_cost=old_foreign_cost*float(a[1]);
			$('foreign_cost_price_'+item_id).value=round(new_foreign_cost,round_decimal);
		}
		
		// update new uom
		$('uom_id'+item_id).value=a[0];
		$('uom_fraction'+item_id).value=a[1];
		
		// change ctn/pcs
		this.check_row_ctn_pcs_input(item_id);
		
		// recalulate row
		this.item_row_changed(item_id);

		// calc foreign
		if(consignment_modules && consignment_multiple_currency && do_type =='transfer') foreign_variable_recalc();
	},
	// function to check row ctn/pcs input
	check_row_ctn_pcs_input: function(item_id){
		var inp_qty_ctn_list = $$('#titem'+item_id+' input.inp_qty_ctn');
		var uom_fraction = float($('uom_fraction'+item_id).value);
		
		for(var i=0,len=inp_qty_ctn_list.length; i<len; i++){
			var inp = inp_qty_ctn_list[i];
			if(uom_fraction == 1){
				inp.value = '--';
				inp.disabled = true;
			}else{
				inp.disabled = false;
				if(inp.value == '--' || inp.value == '-'){
					inp.value = '';
				}
			}
			
		}
	},
	recalc_all_items: function (){
		var row_ctn=0, total_ctn=0;
		var row_pcs=0, total_pcs=0;
		
		var do_total_gross_amt = 0;
		var do_total_gst_amt = 0;
		var total_amount=0;
		var total_qty=0;
		var total_inv_amt = 0;
		
		var sub_total_gross_amt = 0;
		var sub_total_gst_amt = 0;
		var sub_total_amt = 0;
		
		var inv_sub_total_gross_amt = 0;
		var inv_sub_total_gst_amt = 0;
		var sub_total_inv_amt = 0;
		
		var inv_gross_discount_amt = 0;
		var inv_gst_discount_amt = 0;
		var inv_discount_amt = 0;
		var inv_sheet_discount_per = 0;
		var inv_sheet_adj_amt = 0;
		
		var inv_total_gross_amt = 0;
		var inv_total_gst_amt = 0;
		
		var total_rcv = 0;
			
		var do_markup = 0;
		var do_markup1 = 0;
		var do_markup2 = 0;
		var do_markup_type = '';
		
		
		if(this.f['do_markup']){
			var do_markup = this.f['do_markup'].value;
			do_markup_type = getRadioValue(this.f['markup_type']);
			var do_markup1 = float(do_markup.split("+")[0]);
			if(do_markup.split("+").length>1)	do_markup2 = float(do_markup.split("+")[1]);
			if(do_markup_type=='down'){
				do_markup1 *= -1;
				do_markup2 *= -1;
			}
		}  
		
		var is_currency_mode = false;
		var exchange_rate = 0;
		var price_indicate = getRadioValue(this.f['price_indicate']);
		var currency_multiply = 1;
		if(consignment_modules && masterfile_branch_region && consignment_multiple_currency && this.f['exchange_rate'].value > 1){
			is_currency_mode = true;
			exchange_rate = float(this.f['exchange_rate'].value);
			currency_multiply = exchange_rate;
		}
		
		// get branch id list
		var do_branch_id_list = [];
		
		if(do_type=='transfer'){
			do_branch_id_list = get_do_branch_list();
		}		
		
		// construct params for calculate invoice discount
		var disc_params = {};
		if(is_currency_mode && price_indicate!=1 && exchange_rate>0){	// not cost
			disc_params['currency_multiply'] = currency_multiply;
		}
		if(do_branch_id_list.length>1){
			disc_params['discount_by_value_multiply'] = do_branch_id_list.length;
		}
		
		
		if ($('do_items')==undefined) return;
		// get all item row
		var all_row_amt = $$('#do_items span.row_amt');
		var item_len = all_row_amt.length;
		
		// check again gst price
		if(is_under_gst){
			for(var i=0; i<all_row_amt.length; i++){
				var item_id = $(all_row_amt[i]).title.split(',')[1];
				
				if(int(this.f["gst_id["+item_id+"]"].value)<=0){
					update_selected_gst(item_id);
				}
			}
		}
		
		// loop for each row
		for(var i=0; i<all_row_amt.length; i++){
			var item_id = $(all_row_amt[i]).title.split(',')[1];
			
			// get cost price
			var item_cost = float(this.f["cost_price["+item_id+"]"].value);
			// get ctn
			var row_ctn = 0;
			$$('#titem'+item_id+' input.inp_qty_ctn').each(function(inp){
				row_ctn += float(inp.value);
			});
			
			// get pcs
			var row_pcs = 0;
			$$('#titem'+item_id+' input.inp_qty_pcs').each(function(inp){
				row_pcs += float(inp.value);
			});
			// get uom fraction
			item_fraction = float(this.f["uom_fraction["+item_id+"]"].value);
			var row_qty = (row_ctn * item_fraction) + row_pcs;
			total_qty += row_qty;
			//console.log("row_qty = "+row_qty);
			$('row_qty'+item_id).update(float(row_qty));
			
			if(this.f["rcv_pcs["+item_id+"]"]){
				var row_rcv = float(this.f["rcv_pcs["+item_id+"]"].value);
				total_rcv += row_rcv;
			}
			
			var use_cost = item_cost;
			if(do_markup1){
				use_cost *= (1+do_markup1/100);
			}
			if(do_markup2){
				use_cost *= (1+do_markup2/100);
			}
			
			// gst
			var gross_amt = float(use_cost*row_qty/item_fraction);
			
			// use round 2 to sum
			sub_total_gross_amt += float(round(gross_amt,2));
			row_total = gross_amt;
			
			var gst_amt = 0;
			if(is_under_gst){
				$('span-gross_amt-'+item_id).innerHTML=round(gross_amt,2);
				var gst_rate = float(this.f["gst_rate["+item_id+"]"].value);
				gst_amt = float(gross_amt*gst_rate/100);
				
				row_total = float(round(gross_amt+gst_amt, 2));
				
				var gross_amt_rounded = float(round(gross_amt, 2));
				gst_amt = float(round(row_total - gross_amt_rounded, 2));
				
				$('span-gst_amt-'+item_id).update(round(gst_amt, 2));
				
				sub_total_gst_amt += float(round(gst_amt,2));
			}
			
			
			$('row_amount'+item_id).innerHTML=round(row_total,2);
			
			sub_total_amt += float(round(row_total,2));
			total_ctn += row_ctn;
			total_pcs += row_pcs;
			
			total_amount += float(round(row_total,2));
			
			this.f["line_gross_amt["+item_id+"]"].value = round(gross_amt,2);
			this.f["line_gst_amt["+item_id+"]"].value = round(gst_amt,2);
			this.f["line_amt["+item_id+"]"].value = row_total;
			
			var inv_line_gross_amt = gross_amt;
			var inv_line_gst_amt = 0;
			var inv_line_amt = gross_amt;
			
			if(show_discount){
				// get inv row discount
				var row_inv_discount_format = $('inp_item_discount_'+item_id).value.trim();
				// calculate row inv discount amt
				var row_inv_discount_amt = float(get_discount_amt(inv_line_gross_amt, row_inv_discount_format, disc_params));
				if(row_inv_discount_amt){
					inv_line_gross_amt = float(inv_line_gross_amt - row_inv_discount_amt);
				}
				
				inv_sub_total_gross_amt += float(round(inv_line_gross_amt,2));
				inv_line_amt = inv_line_gross_amt;
				
				if(is_under_gst){
					$('span-gross_invoice_amt-'+item_id).update(round(inv_line_gross_amt,2));
					var gst_rate = float(this.f["gst_rate["+item_id+"]"].value);
					inv_line_gst_amt = float(inv_line_gross_amt * (gst_rate/100));
					
					inv_line_amt = float(round(inv_line_gross_amt + inv_line_gst_amt, 2));
					var inv_line_gross_amt_rounded = float(round(inv_line_gross_amt, 2));
					inv_line_gst_amt = float(round(inv_line_amt - inv_line_gross_amt_rounded, 2));
					
					$('span-invoice_gst_amt-'+item_id).update(round(inv_line_gst_amt,2));
					
					inv_sub_total_gst_amt += float(round(inv_line_gst_amt,2));
				}
				
				sub_total_inv_amt += float(round(inv_line_amt,2));
				
				$('span_row_invoice_amt_'+item_id).innerHTML = round(inv_line_amt, 2);
				total_inv_amt += float(round(inv_line_amt,2));
				
				// update discount amt
				this.f["item_discount_amount["+item_id+"]"].value = round(row_inv_discount_amt, 2);
				this.f["item_discount_amount2["+item_id+"]"].value = 0;
				
				// update all inv related amt
				this.f["inv_line_gross_amt["+item_id+"]"].value = round(inv_line_gross_amt,2);
				this.f["inv_line_gst_amt["+item_id+"]"].value = round(inv_line_gst_amt,2);
				this.f["inv_line_amt["+item_id+"]"].value = round(inv_line_amt,2);
				
				var inv_line_gross_amt2 = inv_line_gross_amt;
				var inv_line_gst_amt2 = inv_line_gst_amt;
				var inv_line_amt2 = inv_line_amt;
				
				this.f["inv_line_gross_amt2["+item_id+"]"].value = round(inv_line_gross_amt2,2);
				this.f["inv_line_gst_amt2["+item_id+"]"].value = round(inv_line_gst_amt2,2);
				this.f["inv_line_amt2["+item_id+"]"].value = round(inv_line_amt2,2);
			}
			
			
		}
			
		// sub total
		if(is_under_gst){
			// sub total gross amt
			$('span-sub_total_gross_amt').update(round(sub_total_gross_amt, 2));
			// sub total gst amt
			$('span-sub_total_gst_amt').update(round(sub_total_gst_amt, 2));
		}
		// sub total amt
		$('span-sub_total_amt').update(round2(sub_total_amt));
		
		if(show_discount){
			if(is_under_gst){
				// sub total inv gross amt
				$('span-sub_total_gross_inv_amt').update(round(inv_sub_total_gross_amt,2));
				// sub total inv gst amt
				$('span-sub_total_inv_gst_amt').update(round(inv_sub_total_gst_amt, 2));
			}
			// sub total inv amt
			$('span-sub_total_inv_amt').update(round2(sub_total_inv_amt));
		}
		
		// calculate sheet invoice discount amount
		if(show_discount){		
			// sheet discount
			var inv_discount_format = this.f['discount'].value.trim();
			inv_discount_format = validate_discount_format(inv_discount_format);
			
			// calculate sheet discount
			
			var inv_discount_amt = float(round(get_discount_amt(sub_total_inv_amt, inv_discount_format, disc_params),2));
			var inv_gross_discount_amt = 0;
			var inv_gst_discount = 0;
			
			if(inv_discount_amt){
				// find the inv discount percent
				inv_sheet_discount_per = inv_discount_amt / sub_total_inv_amt;
				
				inv_gross_discount_amt = round(inv_sub_total_gross_amt * inv_sheet_discount_per ,2);
				
				if(is_under_gst){
					inv_gst_discount = float(round(inv_discount_amt-inv_gross_discount_amt,2));
				}
				
				var remaining_inv_gross_discount_amt = inv_gross_discount_amt;
				var remaining_inv_gst_discount = inv_gst_discount;
				var remaining_inv_discount_amt = inv_discount_amt;
				
				// update item amt 2
				for(var i=0; i<all_row_amt.length; i++){
					var item_id = $(all_row_amt[i]).title.split(',')[1];
					
					var inv_line_gross_amt = float(this.f["inv_line_gross_amt["+item_id+"]"].value);
					var inv_line_gst_amt = float(this.f["inv_line_gst_amt["+item_id+"]"].value);
					var inv_line_amt = float(this.f["inv_line_amt["+item_id+"]"].value);
					
					var inv_line_gross_amt2 = float(round(inv_line_gross_amt*(1-inv_sheet_discount_per),2));
					var inv_line_amt2 = float(round(inv_line_amt*(1-inv_sheet_discount_per),2));
					
					var inv_line_gross_amt2_rounded = float(round(inv_line_gross_amt2, 2));
					var inv_line_amt2_rounded = float(round(inv_line_amt2, 2));
					
					var inv_line_gst_amt2 = float(round(inv_line_amt2_rounded-inv_line_gross_amt2_rounded,2));
					var item_discount_amount2 = float(round(inv_line_amt - inv_line_amt2_rounded,2));
					
					remaining_inv_gross_discount_amt = float(round(remaining_inv_gross_discount_amt - (inv_line_gross_amt - inv_line_gross_amt2), 2));
					remaining_inv_gst_discount = float(round(remaining_inv_gst_discount - (inv_line_gst_amt - inv_line_gst_amt2), 2));
					remaining_inv_discount_amt = float(round(remaining_inv_discount_amt - (item_discount_amount2), 2));
					
					if(i == item_len-1){
						if(remaining_inv_gross_discount_amt != 0){
							inv_line_gross_amt2 -= remaining_inv_gross_discount_amt;
							remaining_inv_gross_discount_amt = 0;
						}
						if(remaining_inv_gst_discount != 0){
							inv_line_gst_amt2 -= remaining_inv_gst_discount;
							remaining_inv_gst_discount = 0;
						}
						if(remaining_inv_discount_amt != 0){
							inv_line_amt2 -= remaining_inv_discount_amt;
							item_discount_amount2 += remaining_inv_discount_amt;
							remaining_inv_discount_amt = 0;
						}
					}
					
					this.f["inv_line_gross_amt2["+item_id+"]"].value = round(inv_line_gross_amt2,2);
					this.f["inv_line_gst_amt2["+item_id+"]"].value = round(inv_line_gst_amt2,2);
					this.f["inv_line_amt2["+item_id+"]"].value = round(inv_line_amt2,2);
					
					this.f["item_discount_amount2["+item_id+"]"].value = round(item_discount_amount2,2);
				}
			}
			
			inv_total_gross_amt = float(round2(inv_sub_total_gross_amt - inv_gross_discount_amt));
			inv_total_gst_amt = float(round2(inv_sub_total_gst_amt - inv_gst_discount));
			
			if(inv_discount_amt){
				total_inv_amt -= inv_discount_amt;
			}
			
			// got gst
			if(is_under_gst){
				$('span-inv_gross_discount_amt').update(round2(inv_gross_discount_amt));
				$('span-inv_gst_discount_amt').update(round2(inv_gst_discount));
			}
			
			$('span-inv_discount_amt').update(round2(inv_discount_amt));

			
			
			this.f["inv_sheet_gross_discount_amt"].value = inv_gross_discount_amt;
			this.f["inv_sheet_gst_discount"].value = inv_gst_discount;
			this.f["inv_sheet_discount_amt"].value = inv_discount_amt;
		}
		
		this.f["sub_total_gross_amt"].value = round(sub_total_gross_amt,2);
		this.f["sub_total_gst_amt"].value = round(sub_total_gst_amt,2);
		this.f["sub_total_amt"].value = round(sub_total_amt,2);
		
		// Invoice Adjust
		if(this.f['inv_sheet_adj_amt']){
			inv_sheet_adj_amt = float(this.f['inv_sheet_adj_amt'].value);
			total_inv_amt += inv_sheet_adj_amt;
		}
		
		$('span_total_inv_amt').update(round(total_inv_amt,2));
		$('inp_total_inv_amt').value = round(total_inv_amt,2);
		
		this.f["inv_sub_total_gross_amt"].value = round(inv_sub_total_gross_amt,2);
		this.f["inv_sub_total_gst_amt"].value = round(inv_sub_total_gst_amt,2);
		this.f["sub_total_inv_amt"].value = round(sub_total_inv_amt,2);
		
		do_total_gross_amt = sub_total_gross_amt;
		do_total_gst_amt = sub_total_gst_amt;
		
		$('t_ctn').innerHTML=float(round(total_ctn, global_qty_decimal_points));
		$('t_pcs').innerHTML=float(round(total_pcs, global_qty_decimal_points));
		$('total_ctn').value=float(round(total_ctn, global_qty_decimal_points));
		$('total_pcs').value=float(round(total_pcs, global_qty_decimal_points));
		$('display_total_amount').innerHTML=round(total_amount,2);
		$('total_amount').value=round(total_amount,3);
		
		this.f["do_total_gross_amt"].value = round(do_total_gross_amt,2);
		this.f["do_total_gst_amt"].value = round(do_total_gst_amt,2);
		
		this.f["inv_total_gross_amt"].value = round(inv_total_gross_amt,2);
		this.f["inv_total_gst_amt"].value = round(inv_total_gst_amt,2);
		
		if(is_under_gst){
			$('span-do_total_gross_amt').update(round2(do_total_gross_amt));
			$('span-do_total_gst_amt').update(round2(do_total_gst_amt));
		}
		
		if(show_discount){
			if(is_under_gst){
				$('span-inv_total_gross_amt').update(round2(inv_total_gross_amt));
				$('span-inv_total_gst_amt').update(round2(inv_total_gst_amt));
			}
		}
		
		if(do_use_rcv_pcs){
			$('t_qty').update(float(round(total_qty, global_qty_decimal_points)));
			$('t_rcv').update(float(round(total_rcv, global_qty_decimal_points)));
		}	
		$('total_qty').value = float(round(total_qty, global_qty_decimal_points));
		
		$('total_rcv').value = float(round(total_rcv, global_qty_decimal_points));
		
		if(do_enable_cash_sales_rounding == 1){
			var total_rounded_amount = rounding(total_amount);
			var rounding_amount = float(total_rounded_amount - total_amount);
			
			if(show_discount){
				var total_rounded_inv_amt = rounding(total_inv_amt);
				var rounding_inv_amt = float(round(total_rounded_inv_amt - total_inv_amt,2));
				$('span_rounding_inv_amt').update(round(rounding_inv_amt, 2));
				$('span_ttl_bf_round_inv_amt').update(round(total_inv_amt, 2));
				$('total_round_inv_amt').value = round(rounding_inv_amt, 2);
				$('span_total_inv_amt').update(round(total_rounded_inv_amt, 2));
				$('inp_total_inv_amt').value = round(total_rounded_inv_amt, 2);
			}

			$('span_rounding_amt').update(round(rounding_amount, 2));
			$('span_ttl_bf_round_amt').update(round(total_amount, 2));
			$('total_round_amt').value = round(rounding_amount, 2);
			$('display_total_amount').update(round(total_rounded_amount, 2));
			$('total_amount').value = round(total_rounded_amount, 2);
		}
	},
	// function when user click on open qty form icon
	open_branch_qty_form: function(item_id){
		BRANCH_QTY_FORM_DIALOG.open(item_id);
	},
	
	qty_changed: function(inp){
		var arr = inp.title.split(",");
		var bid = arr[0];
		var item_id = arr[1];
		
		if(sku_bom_additional_type){	// got bom package config
			this.update_bom_package_qty(item_id, bid);	
		}
	},

	update_bom_package_qty: function(item_id, bid){
		var order_uom_fraction = float(document.f_a['uom_fraction['+item_id+']'].value);
		var total_pcs = 0;
		
		if(document.f_a['bom_ref_num['+item_id+']']){
			if(document.f_a['bom_ref_num['+item_id+']'].value.trim() != ''){	// is bom package
				var bom_ref_num = document.f_a['bom_ref_num['+item_id+']'].value.trim();
				var bom_qty_ratio = float(document.f_a['bom_qty_ratio['+item_id+']'].value);
				var multiply_ratio = 0;
				var ctn = 0;
				var pcs = 0;

				if (document.f_a['qty_ctn['+item_id+']['+bid+']'] != undefined){	// multiple branch
					// get the changed branch id
					//bid = $(inp).readAttribute('item_for_bid');
					
					ctn = float(document.f_a['qty_ctn['+item_id+']['+bid+']'].value);
					pcs = float(document.f_a['qty_pcs['+item_id+']['+bid+']'].value);
				}else{	// single branch
					ctn = float(document.f_a['qty_ctn['+item_id+']'].value);
					pcs = float(document.f_a['qty_pcs['+item_id+']'].value);
				}
				total_pcs = (ctn * order_uom_fraction) + pcs;
				multiply_ratio = float(round(total_pcs / bom_qty_ratio,4));
				
				// get all the same bom ref num tr
				var td_bom_ref_num_list = $$('#do_items td.td_bom_ref_num-'+bom_ref_num);
				
				if(int(multiply_ratio) != multiply_ratio){	// not allow decimal
					var group_allow_decimal_qty = true;
				
					// loop to check item can decimal qty or not
					for(var i=0; i<td_bom_ref_num_list.length; i++){
						// get the row po item id
						var tmp_item_id = td_bom_ref_num_list[i].title;
						var tmp_doc_allow_decimal = int(document.f_a['inp_item_doc_allow_decimal['+tmp_item_id+']'].value);

						if(!tmp_doc_allow_decimal){
							group_allow_decimal_qty = false;
							break;
						}
					}
					
					if(!group_allow_decimal_qty)	multiply_ratio = int(multiply_ratio);	// group cannot hv decimal, make int
				}
				
				// loop to update qty
				for(var i=0; i<td_bom_ref_num_list.length; i++){
					// get the row po item id
					var inp = td_bom_ref_num_list[i];
					var tmp_item_id = td_bom_ref_num_list[i].title;
					
					var tmp_bom_qty_ratio = float(document.f_a['bom_qty_ratio['+tmp_item_id+']'].value);
					
					var tmp_order_uom_fraction = float(document.f_a['uom_fraction['+tmp_item_id+']'].value);
					
					var tmp_ctn = 0;
					var tmp_pcs = 0;
					var tmp_total_pcs = tmp_bom_qty_ratio * multiply_ratio;
					 
					if(tmp_order_uom_fraction > 1){
						tmp_ctn = Math.floor(tmp_total_pcs / tmp_order_uom_fraction);
						tmp_pcs = tmp_total_pcs - (tmp_ctn*tmp_order_uom_fraction);
					}else{
						tmp_pcs = tmp_total_pcs;
					}
					
					if (document.f_a['qty_ctn['+tmp_item_id+']['+bid+']'] != undefined){	// multiple branch
						document.f_a['qty_ctn['+tmp_item_id+']['+bid+']'].value = tmp_ctn;
						document.f_a['qty_pcs['+tmp_item_id+']['+bid+']'].value = tmp_pcs;
					}else{	// single branch
						if(tmp_order_uom_fraction > 1){
							document.f_a['qty_ctn['+tmp_item_id+']'].value = tmp_ctn;
						}else{
							document.f_a['qty_pcs['+tmp_item_id+']'].value = '--';
						}
						
						document.f_a['qty_pcs['+tmp_item_id+']'].value = tmp_pcs;
					}
					
					// recal row
					if(tmp_item_id != item_id) row_recalc(tmp_item_id, bid);
				}
			}
		}
	},
	invoice_adj_change: function (){
		var inp = this.f['inv_sheet_adj_amt'];
		if(!inp)	return;
		
		mf(inp);
		
		// recalculate 
		this.recalc_all_items();
	}
}

var BRANCH_QTY_FORM_DIALOG = {
	f: undefined,
	bid_list: [],
	inp_qform_bg_list: [],
	initilize: function(){
		this.f = document.f_branch_qty;
		
		//construct branch list
		BRANCH_QTY_FORM_DIALOG.load_branch();
		
	},
	load_branch: function(){
		
		if ($('new_sheets')){
			var deliver_branch_list = $('new_sheets').getElementsBySelector('th.deliver_branch_list');
			this.bid_list = [];
			for(var i=0,len=deliver_branch_list.length; i<len; i++){
				this.bid_list.push(deliver_branch_list[i].id);
			}
		}
		
		var THIS = this;
		// check branch group
		$$('#div_qform_bg input.inp_qform_bg').each(function(inp){
			THIS.inp_qform_bg_list.push(inp);
		});
		if(this.inp_qform_bg_list.length>1){
			$('div_qform_bg').show();
		}
	},
	open: function(item_id){
		curtain(true);
		center_div($('div_qty_form_dialog').show());
		
		$('inp_item_id').value = item_id;
		
		var total_ctn=0;
		var total_pcs=0;
		
		var	td_do_uom = $('sel_uom'+item_id)	
		var sel_uom = td_do_uom.options[td_do_uom.selectedIndex].textContent;
		
		$('span_arms_code').innerHTML = $('sku_item_code-'+item_id).innerHTML;
		$('span_mcode').innerHTML = $('artno_mcode-'+item_id).innerHTML;
		$('span_sku_description').innerHTML = $('item_desc-'+item_id).innerHTML;
		$('span_do_uom').innerHTML = sel_uom;
		$('span_do_from_stock_balance').innerHTML = document.f_a['stock_balance1['+item_id+']'].value;
		
		if (this.bid_list.length<=0)
			BRANCH_QTY_FORM_DIALOG.load_branch();
		
		for (var i=0, len=this.bid_list.length; i<len; i++){
			var bid = this.bid_list[i];

			$('inp_sb_qty-'+bid).innerHTML=$('stock_balance2,'+item_id+','+bid).value;
			
			if (sel_uom == "EACH"){
				$('inp_ctn_qty-'+bid).value = "--";
				$('inp_ctn_qty-'+bid).disabled = true;
			} else {
				$('inp_ctn_qty-'+bid).value = $('qty_ctn'+item_id+'_'+bid).value;
				$('inp_ctn_qty-'+bid).disabled = false;
			}
			
			$('inp_pcs_qty-'+bid).value = $('qty_pcs'+item_id+'_'+bid).value;
		}
		
		if (sel_uom == "EACH"){
			$('copy_ctn_img').hidden = true;
			$('span_ttl_ctn').innerHTML = "--";
		} else {
			$('copy_ctn_img').hidden = false;
			BRANCH_QTY_FORM_DIALOG.recalc("ctn");
		}
		
		BRANCH_QTY_FORM_DIALOG.recalc("pcs");
	},
	// function to copy ctn/pcs quantity to all branches
	copy_all: function(qty_type){

		var new_qty = prompt("Please enter "+qty_type.toUpperCase()+" quantity.\nThis quantity will copy to all branches / highlighted branches.\n\n", "0");
		
		if (new_qty){
			// get highlighted rows
			var tr_qform_branch_row_list = $$('#tbl_do_qty tr.tr_qform_branch_row_highlight');
			
			// no row is highlighted
			if(tr_qform_branch_row_list.length<=0)	tr_qform_branch_row_list = $$('#tbl_do_qty tr.tr_qform_branch_row');
			
			// loop row
			for(var i=0,len=tr_qform_branch_row_list.length; i<len; i++){
				// get branch id
				var bid = tr_qform_branch_row_list[i].id.split('-')[1];
				// get qty input
				var inp = $('inp_'+qty_type+'_qty-'+bid);
				// change qty
				inp.value = new_qty;
				this.qty_changed(qty_type, inp);
			}
		}
	},
	// function to recalculate total ctn and total pcs
	recalc: function(qty_type){
		var total_qty = 0;
		
		if (qty_type == "ctn") {
			var all_row_ctn = $('div_qty_form_dialog_content').getElementsBySelector('input.inp_ctn_qty');
			
			for(var i=0,len=all_row_ctn.length; i<len; i++){		
				total_qty += Number(all_row_ctn[i].value);
			}
		} else if (qty_type == "pcs") {
			var all_row_pcs = $('div_qty_form_dialog_content').getElementsBySelector('input.inp_pcs_qty');
			
			for(var i=0,len=all_row_pcs.length; i<len; i++){		
				total_qty += Number(all_row_pcs[i].value);
			}
		}
		
		$('span_ttl_'+qty_type).innerHTML = total_qty;
	},
	// function when change ctn/pcs quantity
	qty_changed: function(qty_type, obj){
	
		var item_id = $('inp_item_id').value;
		var doc_allow_decimals = document.f_a['inp_item_doc_allow_decimal['+item_id+']'].value;
		
		if (!isNaN(Number(obj.value))){
		
			if(doc_allow_decimals){
				obj.value = float(round(obj.value, global_qty_decimal_points));
			}else{
				mi(obj);
			}
			
		} else {
			obj.value = 0;
		}

		if (obj.value < 0)
			obj.value = 0;
		
		BRANCH_QTY_FORM_DIALOG.recalc(qty_type);
	},
	// function when click OK to populate qty to main table
	confirm: function(){
		var item_id = $('inp_item_id').value;
		var inp_qty_ctn_list = $$('#titem'+item_id+' input.inp_qty_ctn');
		var inp_qty_pcs_list = $$('#titem'+item_id+' input.inp_qty_pcs');
		
		for(var i=0,len=this.bid_list.length; i<len; i++){
			var bid = this.bid_list[i];
			
			if ($('inp_ctn_qty-'+bid).value != "--")
				$('qty_ctn'+item_id+'_'+bid).value = $('inp_ctn_qty-'+bid).value;
									
			$('qty_pcs'+item_id+'_'+bid).value = $('inp_pcs_qty-'+bid).value;
			
			// calc row, but skip calc all total
			row_recalc(item_id, bid, true);
		}
		
		// calc all total
		DO_MODULE.recalc_all_items();
		
		default_curtain_clicked();
	},
	// function when user click close branch qty form
	close_clicked: function(){
		default_curtain_clicked();
	},
	// function when user click on branch group
	branch_group_clicked: function(bgid){
		bgid = int(bgid);
		if(bgid>0){	// got select branch group
			// get the button
			var btn = $('inp_qform_bg-'+bgid);
			
			if(btn.hasClassName('inp_qform_bg_selected')){	// already selected, now is remove select
				this.remove_branch_group_selected(bgid);
			}else{
				// is mark selected
				this.set_branch_group_selected(bgid);
			}
		}else{	// select none
			// remove all selection
			this.remove_all_branch_group_selection();
		}
	},
	// function to remove all branch group selection
	remove_all_branch_group_selection: function(){
		for(var i=1,len=this.inp_qform_bg_list.length; i<len;i++){
			// remove selected
			$(this.inp_qform_bg_list[i]).removeClassName('inp_qform_bg_selected');
		}
		
		// mark none as selected
		$(this.inp_qform_bg_list[0]).addClassName('inp_qform_bg_selected');
		
		// remove selected from all row
		$$('#tbl_do_qty tr.tr_qform_branch_row').each(function(tr){
			$(tr).removeClassName('tr_qform_branch_row_highlight');
		});
	},
	// function to clear the selection
	remove_branch_group_selected: function(bgid){
		if(!bgid)	return;
		var btn = $('inp_qform_bg-'+bgid);
		if(!btn)	return;
		
		// remove selected
		$(btn).removeClassName('inp_qform_bg_selected');
		
		// check whether still got other selection
		var got_other = false;
		for(var i=1,len=this.inp_qform_bg_list.length; i<len;i++){
			// remove selected
			if($(this.inp_qform_bg_list[i]).hasClassName('inp_qform_bg_selected')){
				got_other = true;
				break;
			}
		}
		
		if(!got_other){
			// no more other selection
			this.remove_all_branch_group_selection();
			return;
		}
			
		// loop all branch in this branch group
		for(var i=0,len=branches_group_items[bgid].length; i<len; i++){
			var bid = branches_group_items[bgid][i];
			
			var tr_qform_branch_row = $('tr_qform_branch_row-'+bid);
			if(!tr_qform_branch_row)	continue;
			
			// remove highlight
			tr_qform_branch_row.removeClassName('tr_qform_branch_row_highlight')
			
		}
	},
	// function to set branch group selected
	set_branch_group_selected: function(bgid){
		if(!bgid)	return;
		var btn = $('inp_qform_bg-'+bgid);
		if(!btn)	return;
		
		// remove none
		$(this.inp_qform_bg_list[0]).removeClassName('inp_qform_bg_selected');
		// mark selected
		$(btn).addClassName('inp_qform_bg_selected');
		
		// loop all branch in this branch group
		for(var i=0,len=branches_group_items[bgid].length; i<len; i++){
			var bid = branches_group_items[bgid][i];
			
			var tr_qform_branch_row = $('tr_qform_branch_row-'+bid);
			if(!tr_qform_branch_row)	continue;
			
			if(!tr_qform_branch_row.hasClassName('tr_qform_branch_row_highlight')){
				tr_qform_branch_row.addClassName('tr_qform_branch_row_highlight')
			}
		}
	}
}
{/literal}
</script>

<!-- Qty Form DIALOG -->

<div id="div_qty_form_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:700px;height:580px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_qty_form_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Qty Form</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_qty_form_dialog_content" style="padding:2px;overflow-y:auto;height:550px;">
		<form name="f_branch_qty" onSubmit="return false;">
			<input type="hidden" id="inp_item_id" />
			<br />
			
			<b>ARMS Code: </b><span id="span_arms_code"></span><br />
			<b>MCode: </b><span id="span_mcode"></span><br />
			<b>SKU Description: </b><span id="span_sku_description"></span><br />
			<b>DO UOM: </b><span id="span_do_uom"></span><br />
			<b>Stock Balance: </b><span id="span_do_from_stock_balance"></span><br />
			<div id="div_qform_bg" style="display:none;">
				<br />
				<b>Highlights Branch Group: </b>
				<input type="button" value="None" class="inp_qform_bg inp_qform_bg_selected" id="inp_qform_bg-none" onClick="BRANCH_QTY_FORM_DIALOG.branch_group_clicked('');" />
				
				{foreach from=$branches_group.header key=bgid item=bg}
					{assign var=have_this_branch_group value=0}
									
					{foreach from=$form.deliver_branch item=bid}
						{if $branches_group.items.$bgid.$bid}
							{assign var=have_this_branch_group value=1}
						{/if}
					{/foreach}
					
					
					{if $have_this_branch_group}
						
						<input type="button" value="{$bg.code}" class="inp_qform_bg" onClick="BRANCH_QTY_FORM_DIALOG.branch_group_clicked('{$bgid}');" id="inp_qform_bg-{$bgid}" />
					{/if}
				{/foreach}
			</div>
			
			
			<br />
			<span style="float:right;"><b>Total Ctn:&nbsp;<span id="span_ttl_ctn"></span>&nbsp;&nbsp;Pcs:&nbsp;<span id="span_ttl_pcs"></span>&nbsp;</b></span>
			<br /><br />
			
			<table id="tbl_do_qty" width=100% style="border:1px solid #999; padding:1px; background-color:#ffffff;" cellspacing=1 cellpadding=1>
				<tr bgcolor="#ffee99">
					<th rowspan=2 width=60%>Branch</th>
					<th rowspan=2>Stock Balance</th>
					<th colspan=2>Qty</th>
				</tr>
				<tr bgcolor="#ffee99">
					<th>Ctn&nbsp;<img id="copy_ctn_img" src="/ui/icons/page_copy.png" title="Copy Ctn to All Branch" onClick="BRANCH_QTY_FORM_DIALOG.copy_all('ctn')" class="clickable"/>&nbsp;</th>
					<th>Pcs&nbsp;<img id="copy_pcs_img" src="/ui/icons/page_copy.png" title="Copy Pcs to All Branch" onClick="BRANCH_QTY_FORM_DIALOG.copy_all('pcs')" class="clickable"/>&nbsp;</th>
				</tr>
				
				{if $form.deliver_branch}
					{section name=i loop=$branch}
					{if in_array($branch[i].id,$form.deliver_branch)}
					<tr id="tr_qform_branch_row-{$branch[i].id}" class="tr_qform_branch_row">
						<td>{$branch[i].code}</td>
						<td nowrap align=center><span id="inp_sb_qty-{$branch[i].id}"></span></td>
						<td nowrap align=center>
						<input id="inp_ctn_qty-{$branch[i].id}" name="inp_ctn_qty-{$branch[i].id}" size="6" class="inp_ctn_qty" onChange="BRANCH_QTY_FORM_DIALOG.qty_changed('ctn',this);" />
						</td>
						<td nowrap align=center>
						<input id="inp_pcs_qty-{$branch[i].id}" name="inp_pcs_qty-{$branch[i].id}" size="6" class="inp_pcs_qty" onChange="BRANCH_QTY_FORM_DIALOG.qty_changed('pcs',this);" />
						</td>
					</tr>
					{/if}
					{/section}
				{/if}
			
			</table>
			
			<p align="center">
				<input type="button" value="OK" onclick="BRANCH_QTY_FORM_DIALOG.confirm();" /> 
				<input type="button" value="Cancel" onclick="BRANCH_QTY_FORM_DIALOG.close_clicked();" />
			</p>
		</form>
	</div>
</div>
<!-- End of Qty Form DIALOG -->
