{*
10/22/2015 10:20 AM Andy
- Fix only can reset own branch.

10/29/2015 11:40 AM Andy
- Enhanced to store sub total amount.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

1/25/2016 9:29 AM Andy
- Enhanced to put a hint for CN Invoice No.

06/17/2016 15:30 Edwin
- Bugs fixed on added items consists of incomplete info.
- Bugs fixed on prompt error when added multiple items in one time.
- Bugs fixed on grn barcoder does not compare maximum quantity with DO.

02/27/2017 4:53 PM Zhi Kai
- Change wording of 'General Informations' to 'General Information'.

5/29/2017 16:50 Qiu Ying
- Enhanced to return multiple invoice

6/15/2017 10:02 Am Qiu Ying
- Bug fixed on item qty greater than DO Invoice qty can be saved when click on save button

6/20/2017 13:33 Qiu Ying
- Bug fixed on viewing wrong credit note adjustment when in HQ

7/3/2017 09:30 AM Qiu Ying
- Bug fixed on blocking negative value in quantity field

2017-08-21 11:17 AM Qiu Ying
- Enhanced to load reason list from config
- Enhanced to load debtor and branch for customer info
- Enhanced to load the item price, uom and gst code from DO invoice when add item

2017-08-24 17:25 PM Qiu Ying
- Bug fixed on showing branch code in customer name

06/24/2020 04:46 PM Sheila
- Updated button css
*}

{if !$form.approval_screen}
	{include file='header.tpl'}
{else}
	<hr noshade size="2">
{/if}

{literal}
<style>
.input_no_border input, .input_no_border select{
	border:1px solid #999;
	background: #fff;
	font-size: 10px;
	padding:2px;
}

input[disabled],input[readonly],select[disabled], textarea[disabled]{
  background-color: #ddd;
}

input.inp_qty_ctn{
	width:30px;
}
input.inp_qty_pcs{
	width:30px;
	background:#fc9;
}
input.inp_doc_allow_decimal{
	width:40px !important;
}

#div-item_reason_list ul li {
	cursor:pointer;
	display:block;
	margin:0;padding:4px;
}
#div-item_reason_list ul li:hover {
	background:#ff9;
}

#div-item_reason_list ul li.current {
	background:#9ff;
}

#div-item_reason_list:hover ul {
	visibility:visible;
}

</style>
{/literal}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<script>
var phpself = 'cnote.php';
var can_edit = int('{$can_edit}');
var can_save = int('{$can_save}');
// gst
var enable_gst = int('{$config.enable_gst}');
var global_gst_start_date = '{$config.global_gst_start_date}';
var is_under_gst = int('{$form.is_under_gst}');
var branch_gst_register_no = '{$sessioninfo.gst_register_no}';
var branch_gst_start_date = '{$sessioninfo.gst_start_date}';
var gst_is_active = int('{$sessioninfo.gst_is_active}');
var skip_gst_validate = int('{$sessioninfo.skip_gst_validate}');
var global_qty_decimal_points = int('{$config.global_qty_decimal_points}');
var needCheckExit = true;


{literal}
var the_do = {};

CNOTE_OPEN = {
	f: undefined,
	reason_for_item_id: 0,
	initialize: function(){
		this.f = document.f_a;

		if(can_edit){
			var THIS=this;
			// init calendar
			this.init_calendar();
			
			// totally recalculate
			this.totally_recalculate();
			
			// check on exit
			if(can_save){
				window.onbeforeunload=this.confirmExit;
			}
			
			new Insertion.Before('autocomplete_sku_choices', '<button class="btn btn-primary" id="btn_cn_available_do" type="button" onclick="void(CNOTE_OPEN.toggle_do_items(true))">Available item in DO</button>');
			
			if(document.f_a['return_type'].value == "multiple_inv"){
				$("btn_cn_available_do").hide();
			}else{
				CNOTE_OPEN.check_do_no();
			}
			
		}else{
			// disable form
			Form.disable(this.f);
		}
	},
	// function to init calendar
	init_calendar: function(){
		// cn date
		Calendar.setup({
			inputField     :    "inp_cn_date",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_cn_date",
			align          :    "Bl",
			singleClick    :    true
		});
		
		// inv date
		/*Calendar.setup({
			inputField     :    "inp_inv_date",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_inv_date",
			align          :    "Bl",
			singleClick    :    true
		});*/
	},
	// function when user change cn date
	on_cn_date_changed: function(){
		// get the object
		var inp = this.f['cn_date'];
		// check max/min limit
		upper_lower_limit(inp);
		// check gst
		if(enable_gst)	this.check_gst_date_changed();
	},
	// function when do date is changed
	check_gst_date_changed: function(){
		var allow_gst = false;

		// gst is not enable
		if(!enable_gst)	return;

		// gst is active and branch got register
		if(gst_is_active && branch_gst_register_no){
			if(skip_gst_validate){
				allow_gst = true;
			}else{
				// got gst start date
				if(global_gst_start_date && branch_gst_start_date){
					// get Date
					var d = this.f["cn_date"].value.trim();

					if(d){
						// check Date
						if(strtotime(d) >= strtotime(global_gst_start_date) && strtotime(d) >= strtotime(branch_gst_start_date)){
							allow_gst = true;
						}
					}
				}
			}
		}

		if(allow_gst){
			// date have gst
			if(!is_under_gst)	this.need_refresh_sheet();
		}else{
			// date no gst
			if(is_under_gst)	this.need_refresh_sheet();
		}
	},
	// function to show need refresh
	need_refresh_sheet: function(){
		// show the refresh div
		$('div_refresh').style.display='';
		// activate the refresh button
		$('refresh_btn').disabled=false;
		$('refresh_btn').show();
		// hide the sheet
		$('div_sheets').hide();
		// hide submit button
		$('p_submit_btn').hide();
	},
	// function to refresh current form
	refresh_tables: function(){
		// check allow to refresh or not
		if(!this.is_valid_for_refresh())	return;
		
		this.f['a'].value = 'refresh';
		this.f.submit();
	},
	// function to check whether it is valid to refresh
	is_valid_for_refresh: function(){
		// cn date
		if(!validateTimestamp(this.f['cn_date'].value)){
			alert('Invalid CN Date');
			this.f['cn_date'].focus();
			return false;
		}
		
		if(this.f['return_type'].value.trim() != "multiple_inv"){
			// inv no
			if(this.f['inv_no'].value.trim() == ''){
				alert('Please key in Invoice No.');
				this.f['inv_no'].focus();
				return false;
			}
			
			// inv date
			if(!validateTimestamp(this.f['inp_inv_date'].value)){
				alert('Invalid Invoice Date');
				this.f['inp_inv_date'].focus();
				return false;
			}
		}
		
		// customer name
		if(this.f['cust_name'].value.trim() == ''){
			alert('Please key in Customer Name');
			this.f['cust_name'].focus();
			return false;
		}
		
		return true;
	},
	// function to check whether it is valid for save
	is_valid_for_save: function(action){
		if(!this.is_valid_for_refresh())	return false;
		
		// item counter
		if($$('#tbody_items tr.tr_cn_item').length<=0){
			alert('Please add at least one item');
			return false;
		}else{
			if(document.f_a["return_type"].value == "multiple_inv"){
				var ele = $$('input.return_inv_no');
				for(i = 0; i < ele.length; i++){
					if(ele[i].value == ""){
						alert("Invoice No. cannot be empty");
						ele[i].focus();
						return false;
					}
				}
			}
		}
		
		if(action == "confirm"){
			var ele = $$('span.span_line_qty');
			var ele_pcs = $$('input.inp_qty_pcs');
			
			for(i = 0; i < ele.length; i++){
				if(float(ele[i].innerHTML) < 1){
					alert("Min total quantity is 1");
					ele_pcs[i].focus();
					return false;
				}
			}
		}
		return true;
	},
	// function when user change customer name
	cust_name_changed: function(){
		// trim customer name
		this.f['cust_name'].value = this.f['cust_name'].value.trim();
		if(this.f['cust_name'].value.value != ''){
			// if the sheets is hiding, need refresh
			if($('div_sheets').style.display=='none'){
				this.need_refresh_sheet();
			}
		}
	},
	// function to toggle adding item is in progress
	toggle_adding_inprogress: function(is_show){
		if(is_show){
			$('span_autocomplete_loading').update(_loading_).show();
		}else{
			$('span_autocomplete_loading').hide();
		}
	},
	// function when user click add item by barcde
	add_grn_barcode_item: function(value){
		if(document.f_a["return_type"].value != "multiple_inv" && $("inv_no").value.trim() == ""){
			alert("Invoice No. cannot be empty");
			$("inv_no").focus();
			return false;
		}
	
		value = trim(value);
		
		// no value
		if (value=='')
		{
			$('grn_barcode').select();
			$('grn_barcode').focus();
			return;
		}
		
		// construct query string
		var query_string = Form.serialize(document.f_a)+'&a=ajax_add_item_row&grn_barcode='+value;
		
		// call to add item
		this.add_item(query_string, {'is_scan_barcode': true});
		
	},
	// core function to add item
	add_item: function(query_string, optional_params){
		if(!optional_params)	optional_params = {};
		var THIS = this;

		// is scan barcode or not
		var is_scan_barcode = optional_params['is_scan_barcode'] ? true : false;
		
		// show adding item
		this.toggle_adding_inprogress(true);
		
		// clear value in scan barcode
		$('grn_barcode').value='';
		$('sku_code_list').length = 0;

		// ajax_add_item_row
		ajax_request(phpself, {
			parameters: query_string,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// insert the html at the div bottom
	                    new Insertion.Bottom($('tbody_items'), ret['html']);

						if(is_scan_barcode){
							// focus back scan barcode if is scan barcode
							$('grn_barcode').focus();
						}else{
							// focus back search sku
							$('autocomplete_sku').focus();
						}

						// reset item no
						THIS.reset_item_no();
						
						// loop item id
						if(ret['item_id_list']){
							for(var i=0,len=ret['item_id_list'].length; i<len; i++){
								if(is_under_gst){
									THIS.update_item_selected_gst(ret['item_id_list'][i]);
								}else{
									THIS.item_row_changed(ret['item_id_list'][i]);
								}
							}
						}
						
						var radios = $$("input.return_type");

						for (var i = 0; i < radios.length; i++) {
							radios[i].disabled = true;
						}
						
						$("inv_no").readOnly=true;
						
						//  hide loading icon
						THIS.toggle_adding_inprogress(false);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
			    
				if(is_scan_barcode){
					// focus back scan barcode if is scan barcode
					$('grn_barcode').focus();
				}else{
					// focus back search sku
					$('autocomplete_sku').focus();
				}
				//  hide loading icon
				THIS.toggle_adding_inprogress(false);
				
			}
		});
	},
	// function to reset item no
	reset_item_no: function(){
		var no = 1;
		$$('#tbody_items span.span_item_no').each(function(ele){
			$(ele).update(no);
			no++;
		});
	},
	// function when user click delete item
	delete_item_clicked: function(item_id){
		if(!item_id)	return;
		
		// get the img element
		var img = $('img-delete_item-'+item_id);
		if(img.src.indexOf('remove')<0)	return;
		
		if(!confirm('Are you sure?'))	return;
		
		// change the img
		img.src = 'ui/clock.gif';
		var THIS = this;
		
		// construct params
		var params = $(this.f).serialize()+'&a=ajax_delete_item&delete_item_id='+item_id;
		
		// ajax_add_item_row
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// remove the elements
						$('tr_cn_item-'+item_id).remove();
						// reset item no
						THIS.reset_item_no();
						// recalculate
						THIS.calc_all_items();
						var obj = $$('#tbody_items span.span_item_no');
						
						if(obj.length <= 0){
							var radios = $$("input.return_type");

							for (var i = 0; i < radios.length; i++) {
								radios[i].disabled = false;
							}
							$("inv_no").readOnly=false;
						}
						
						
						
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
				img.src = 'ui/remove16.png';
			}
		});
	},
	// function when user change display price is inclusive checkbox
	display_price_is_inclusive_changed: function(item_id){
		this.item_row_changed(item_id);
	},
	// function when user change display price
	display_price_changed: function(item_id){
		this.item_row_changed(item_id);
	},
	// function when user change price
	price_changed: function(item_id, need_round){
		if(need_round){
			mf(this.f['items_list['+item_id+'][price]'], 2);
		}
		this.item_row_changed(item_id);
	},
	amount_changed: function(item_id, need_round){
		if(need_round){
			mf(this.f['items_list['+item_id+'][line_amt]'], 2);
		}

		var amount=this.f['items_list['+item_id+'][line_amt]'].value;
		var uom_fraction = float(this.f['items_list['+item_id+'][uom_fraction]'].value);
		if(uom_fraction<=0)	uom_fraction = 1;

		var row_ctn = float(this.f['items_list['+item_id+'][ctn]'].value);
		var row_pcs = float(this.f['items_list['+item_id+'][pcs]'].value);
		var row_qty = (row_ctn * uom_fraction) + row_pcs;

		var display_price=amount;
		var price = display_price;

		if (is_under_gst) {
			var gst_rate = float(this.f['items_list['+item_id+'][gst_rate]'].value);

			if(this.f['items_list['+item_id+'][display_price_is_inclusive]'].checked){
				price = (display_price / (100+gst_rate) * 100) / row_qty;
				display_price = display_price/row_qty;
			}else{
				price=(display_price/(100+gst_rate)*100) / row_qty;
				display_price=price;
			}

			this.f['items_list['+item_id+'][display_price]'].value=round(display_price,2);
		}
		else{
			display_price=display_price/row_qty;
			price=display_price;
		}

		this.f['items_list['+item_id+'][price]'].value = price;
		$('span_price_label-'+item_id).update(round(price, 2));

		this.item_row_recal(item_id);
	},
	// function when user change uom
	item_uom_changed: function(item_id){
		var sel_uom = this.f['items_list['+item_id+'][sel_uom]'];
		var inp_uom_fraction = this.f['items_list['+item_id+'][uom_fraction]'];
		var inp_uom_id = this.f['items_list['+item_id+'][uom_id]'];

		var a = sel_uom.value.split(",");
		var uom_id = a[0];
		var uom_fraction = float(a[1]);
		var old_fraction = float(inp_uom_fraction.value);
		var old_price;
		var new_price;

		// check old fraction
		if(is_under_gst){
			old_price = float(this.f['items_list['+item_id+'][display_price]'].value) / old_fraction;
			new_price = old_price * uom_fraction;
			
			// change new cost
			this.f['items_list['+item_id+'][display_price]'].value = round(new_price, 2);
		}else{
			old_price = float(this.f['items_list['+item_id+'][price]'].value) / old_fraction;
			new_price = old_price * uom_fraction;
			
			// change new cost
			this.f['items_list['+item_id+'][price]'].value = round(new_price, 2);
		}
		
		// update new uom
		inp_uom_id.value = uom_id;
		inp_uom_fraction.value = uom_fraction;

		// change ctn/pcs
		this.check_row_ctn_pcs_input(item_id);
		
		// recalulate row
		this.item_row_changed(item_id);
	},
	// function to check row ctn/pcs input
	check_row_ctn_pcs_input: function(item_id){
		var inp_qty_ctn_list = $$('#tr_cn_item-'+item_id+' input.inp_qty_ctn');
		var uom_fraction = float(this.f['items_list['+item_id+'][uom_fraction]'].value);
		
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
	// function when user change ctn/pcs
	item_qty_changed: function(item_id, qty_type){
		// get whether this item is allow decimal
		var doc_allow_decimal = this.is_item_allow_decimal(item_id);
		var do_item_id = this.f['items_list['+item_id+'][do_item_id]'].value;
		
		if(doc_allow_decimal){
			// allow decimal
			mf(this.f['items_list['+item_id+']['+qty_type+']'], global_qty_decimal_points);
		}else{
			// not allow decimal
			mi(this.f['items_list['+item_id+']['+qty_type+']']);
		}
		
		if(this.f["return_type"].value == "multiple_inv" && this.f['items_list['+item_id+'][return_inv_no]'].value == ""){
			this.f['items_list['+item_id+']['+qty_type+']'].value = 0;
			this.f['items_list['+item_id+'][return_inv_no]'].focus();
			alert("Please enter Invoice No. first");
			return false;
		}
		
		if(this.f['items_list['+item_id+']['+qty_type+']'].value < 0){
			this.f['items_list['+item_id+']['+qty_type+']'].value = 0;
			alert("Quantity cannot be negative value");
			this.f['items_list['+item_id+']['+qty_type+']'].focus();
		}
		
		var pcs = float(this.f['items_list['+item_id+'][pcs]'].value);
		var ctn = float(this.f['items_list['+item_id+'][ctn]'].value);
		var uom_fraction = float(this.f['items_list['+item_id+'][uom_fraction]'].value);
		var tmp_total_qty = (ctn*uom_fraction)+pcs;
		var do_total_qty;
		
		if (this.f["return_type"].value == "multiple_inv") {
			do_total_qty = float(this.f['items_list['+item_id+'][do_total_qty]'].value);
        }else{
			if(the_do.items[do_item_id] != undefined){
				do_total_qty = float(the_do.items[do_item_id]['do_total_qty']);
			}
		}
		
		if(tmp_total_qty > do_total_qty){
			this.f['items_list['+item_id+']['+qty_type+']'].value = 0;
			alert("Max total quantity is " + do_total_qty);
			this.f['items_list['+item_id+']['+qty_type+']'].focus();
		}
		
		// trigger row changed
		this.item_row_changed(item_id);
	},
	// core function to call when an item row get edit
	item_row_changed: function(item_id){
		// calculate real cost price
		if(is_under_gst){
			this.calculate_real_price(item_id);
		}
		
		// recalculate row total
		this.item_row_recal(item_id);
	},
	// function to calculate real price based on display price
	calculate_real_price: function(item_id){
		if(!is_under_gst)	return;
		
		// got problem on selected gst
		//if(this.f['items_list['+item_id+'][gst_id]'].value == ""){
		//	this.update_item_selected_gst(item_id);
		//}
		
		// get the display cost price
		var inp_display_price = this.f['items_list['+item_id+'][display_price]'];
		// round price to 2
		mf(inp_display_price, 2);
		
		var display_price = float(round(inp_display_price.value, 2));
		
		// not allow negative
		if(display_price<0){
			display_price = 0;
			display_price.value = 0;
		}
		var price = display_price;
		
		// check display price inclusive
		if(this.f['items_list['+item_id+'][display_price_is_inclusive]'].checked){
			// is inclusive tax
			// calculate before tax price
			var gst_rate = float(this.f['items_list['+item_id+'][gst_rate]'].value);
			if(gst_rate>0){
				price = display_price / (100+gst_rate) * 100;
			}
			$('div_price_info-'+item_id).show();
		}else{
			// exclusive tax
			$('div_price_info-'+item_id).hide();
		}
		
		// display cost price to label
		this.f['items_list['+item_id+'][price]'].value = price;
		$('span_price_label-'+item_id).update(round(price, 2));
	},
	// function to check whether the item is allow decimal
	is_item_allow_decimal: function(item_id){
		return this.f['items_list['+item_id+'][doc_allow_decimal]'].value==1 ? true : false;
	},
	// core function to recalculate item row total
	item_row_recal: function(item_id, recalc_total){
		if(recalc_total == undefined)	recalc_total = true;
		
		// got problem on selected gst
		if(is_under_gst && this.f['items_list['+item_id+'][gst_id]'].value == ""){
			this.update_item_selected_gst(item_id);
		}
		
		var uom_fraction = float(this.f['items_list['+item_id+'][uom_fraction]'].value);
		if(uom_fraction<=0)	uom_fraction = 1;
		
		var row_ctn = float(this.f['items_list['+item_id+'][ctn]'].value);
		var row_pcs = float(this.f['items_list['+item_id+'][pcs]'].value);
		var row_qty = (row_ctn * uom_fraction) + row_pcs;
		var price = float(this.f['items_list['+item_id+'][price]'].value);
		//var discount=this.f['items_list['+item_id+'][discount]'].value.trim();

		// update line qty
		$('span_line_qty-'+item_id).update(row_qty);
		
		// gross amt
		var line_gross_amt = (price/uom_fraction)*row_qty;
		var line_gst_amt = 0;
		var line_amt = line_gross_amt;
		
		if(is_under_gst){
			// get gst rate
			var gst_rate = float(this.f['items_list['+item_id+'][gst_rate]'].value);
			
			line_gst_amt = float(line_gross_amt*gst_rate/100);
			line_amt = float(round(line_gross_amt+line_gst_amt, 2));
			
			var line_gross_amt_rounded = float(round(line_gross_amt, 2));
			line_gst_amt = float(round(line_amt - line_gross_amt_rounded, 2));
				
			// update into span
			$('span_line_gross_amt-'+item_id).update(round(line_gross_amt, 2));
			$('span-line_gst_amt-'+item_id).update(round(line_gst_amt, 2));
		}

		/*if (discount!="") {
			var row_discount_amt = float(get_discount_amt(line_gross_amt, discount));
			if(row_discount_amt){
				line_gross_amt = float(line_gross_amt - row_discount_amt);
			}

			if(is_under_gst){
				var gst_rate = float(this.f['items_list['+item_id+'][gst_rate]'].value);
				line_gst_amt = float(line_gross_amt * (gst_rate/100));
				line_amt = float(round(line_gross_amt + line_gst_amt, 2));

				var line_gross_amt_rounded = float(round(line_gross_amt, 2));
				line_gst_amt = float(round(line_amt - line_gross_amt_rounded, 2));
			}
        }*/
		
		this.f['items_list['+item_id+'][line_gross_amt]'].value = round(line_gross_amt, 2);
		this.f['items_list['+item_id+'][line_gst_amt]'].value = round(line_gst_amt, 2);
		this.f['items_list['+item_id+'][line_amt]'].value = round(line_amt, 2);
		//this.f['items_list['+item_id+'][item_discount_amount]'].value = round(row_discount_amt, 2);
		// amt 2
		this.f['items_list['+item_id+'][line_gross_amt2]'].value = round(line_gross_amt, 2);
		this.f['items_list['+item_id+'][line_gst_amt2]'].value = round(line_gst_amt, 2);
		this.f['items_list['+item_id+'][line_amt2]'].value = round(line_amt, 2);

		
		if(recalc_total){
			this.calc_all_items();
		}
	},
	// function to recalculate all items
	calc_all_items: function(){
		var sub_total_gross_amount = 0;
		var sub_total_gst_amount = 0;
		var sub_total_amount = 0;
		var gross_discount_amt = 0;
		var gst_discount_amt = 0;
		var discount_amt = 0;
		var total_gross_amount = 0;
		var total_gst_amount = 0;
		var total_amount = 0;
		var total_ctn = 0;
		var total_pcs = 0;
		var total_qty = 0;
		var discount_format=this.f['discount'].value.trim();
		var sheet_discount_per=0;
		
		$('row-sub_total').hide();
		$('row-discount').hide();

		// get all cn item row
		var tr_cn_item_list = $$('#tbody_items tr.tr_cn_item');
		// loop each row
		for(var i=0,len=tr_cn_item_list.length; i<len; i++){
			// get item id
			var item_id = this.get_item_id_by_ele(tr_cn_item_list[i]);

			var line_gross_amt = float(this.f['items_list['+item_id+'][line_gross_amt]'].value);
			var line_gst_amt = float(this.f['items_list['+item_id+'][line_gst_amt]'].value);
			var line_amt = float(this.f['items_list['+item_id+'][line_amt]'].value);

			sub_total_gross_amount += line_gross_amt;
			sub_total_gst_amount += line_gst_amt;
			sub_total_amount += line_amt;
			
			total_gross_amount+=line_gross_amt;
			total_gst_amount+=line_gst_amt;
			total_amount+=line_amt;

			// qty
			var uom_fraction = float(this.f['items_list['+item_id+'][uom_fraction]'].value);
			if(uom_fraction<=0)	uom_fraction = 1;
			
			var row_ctn = float(this.f['items_list['+item_id+'][ctn]'].value);
			var row_pcs = float(this.f['items_list['+item_id+'][pcs]'].value);
			var row_qty = (row_ctn*uom_fraction)+row_pcs;
			
			total_ctn += row_ctn;
			total_pcs += row_pcs;
			total_qty += row_qty;
		}
		
		if (discount_format!="") {
			total_gross_amount = 0;
			total_gst_amount = 0;
			total_amount = 0;

			discount_amt = float(round(get_discount_amt(sub_total_amount, discount_format),2));

			if(discount_amt){
				sheet_discount_per = discount_amt / sub_total_amount;
				gross_discount_amt = round(sub_total_gross_amount * sheet_discount_per ,2);

				$('row-sub_total').show();
				$('row-discount').show();
			}
			
			if(is_under_gst){
				gst_discount_amt = float(round(discount_amt-gross_discount_amt,2));
			}

			var remaining_gross_discount_amt = gross_discount_amt;
			var remaining_gst_discount_amt = gst_discount_amt;
			var remaining_discount_amt = discount_amt;

			for(var i=0,len=tr_cn_item_list.length; i<len; i++){
				var item_id = this.get_item_id_by_ele(tr_cn_item_list[i]);

				var line_gross_amt = float(this.f['items_list['+item_id+'][line_gross_amt]'].value);
				var line_gst_amt = float(this.f['items_list['+item_id+'][line_gst_amt]'].value);
				var line_amt = float(this.f['items_list['+item_id+'][line_amt]'].value);

				var line_gross_amt2 = float(round(line_gross_amt*(1-sheet_discount_per),2));
				var line_amt2 = float(round(line_amt*(1-sheet_discount_per),2));
				
				var line_gross_amt2_rounded = float(round(line_gross_amt2, 2));
				var line_amt2_rounded = float(round(line_amt2, 2));

				var line_gst_amt2 = float(round(line_amt2_rounded-line_gross_amt2_rounded,2));
				var item_discount_amount2 = float(round(line_amt - line_amt2_rounded,2));
				
				remaining_gross_discount_amt = float(round(remaining_gross_discount_amt - (line_gross_amt - line_gross_amt2), 2));
				remaining_gst_discount_amt = float(round(remaining_gst_discount_amt - (line_gst_amt - line_gst_amt2), 2));
				remaining_discount_amt = float(round(remaining_discount_amt - (item_discount_amount2), 2));
				
				if(i == tr_cn_item_list.length-1){
					if(remaining_gross_discount_amt != 0){
						line_gross_amt2 -= remaining_gross_discount_amt;
						remaining_gross_discount_amt = 0;
					}
					if(remaining_gst_discount_amt != 0){
						line_gst_amt2 -= remaining_gst_discount_amt;
						remaining_gst_discount_amt = 0;
					}
					if(remaining_discount_amt != 0){
						line_amt2 -= remaining_discount_amt;
						item_discount_amount2 += remaining_discount_amt;
						remaining_discount_amt = 0;
					}
				}

				this.f['items_list['+item_id+'][line_gross_amt2]'].value = round(line_gross_amt2, 2);
				this.f['items_list['+item_id+'][line_gst_amt2]'].value = round(line_gst_amt2, 2);
				this.f['items_list['+item_id+'][line_amt2]'].value = round(line_amt2, 2);
				this.f['items_list['+item_id+'][item_discount_amount2]'].value = round(item_discount_amount2, 2);

				total_gross_amount+=line_gross_amt2;
				total_gst_amount+=line_gst_amt2;
				total_amount+=line_amt2;
			}
        }
		
		// update into input
		this.f['sub_total_gross_amount'].value = round(sub_total_gross_amount, 2);
		this.f['sub_total_gst_amount'].value = round(sub_total_gst_amount, 2);
		this.f['sub_total_amount'].value = round(sub_total_amount, 2);
		this.f['gross_discount_amt'].value = round(gross_discount_amt, 2);
		this.f['gst_discount_amt'].value = round(gst_discount_amt, 2);
		this.f['discount_amt'].value = round(discount_amt, 2);
		this.f['total_gross_amount'].value = round(total_gross_amount, 2);
		this.f['total_gst_amount'].value = round(total_gst_amount, 2);
		this.f['total_amount'].value = round(total_amount, 2);
		this.f['total_ctn'].value = round(total_ctn, 2);
		this.f['total_pcs'].value = round(total_pcs, 2);
		this.f['total_qty'].value = round(total_qty, 2);

		// update into span
		if(is_under_gst){
			$('span-sub_total_gross_amount').update(round(sub_total_gross_amount, 2));
			$('span-sub_total_gst_amount').update(round(sub_total_gst_amount, 2));
			$('span-gross_discount_amt').update(round(gross_discount_amt, 2));
			$('span-gst_discount_amt').update(round(gst_discount_amt, 2));
			$('span-total_gross_amount').update(round(total_gross_amount, 2));
			$('span-total_gst_amount').update(round(total_gst_amount, 2));
		}
		$('span-sub_total_amount').update(round(sub_total_amount, 2));
		$('span-discount_amt').update(round(discount_amt, 2));
		$('span-total_amount').update(round(total_amount, 2));
		
		$('span-total_ctn').update(total_ctn);
		$('span-total_pcs').update(total_pcs);
	},
	// function when user change row gst
	item_gst_changed: function(item_id){
		// update the selected gst
		this.update_item_selected_gst(item_id);
			
		// recalculate row
		this.item_row_changed(item_id);
	},
	// function to update gst id/code/rate
	update_item_selected_gst: function(item_id){
		this.f['items_list['+item_id+'][gst_id]'].value = "";
		this.f['items_list['+item_id+'][gst_code]'].value = "";
		this.f['items_list['+item_id+'][gst_rate]'].value = "";
		
		var sel = this.f['items_list['+item_id+'][item_gst]'];
		
		if(sel.selectedIndex >= 0){
			// got select
			var opt = sel.options[sel.selectedIndex];
			var gst_id = $(opt).readAttribute("gst_id");
			var gst_code = $(opt).readAttribute("gst_code");
			var gst_rate = $(opt).readAttribute("gst_rate");
			
			this.f['items_list['+item_id+'][gst_id]'].value = gst_id;
			this.f['items_list['+item_id+'][gst_code]'].value = gst_code;
			this.f['items_list['+item_id+'][gst_rate]'].value = gst_rate;
		}
	},
	// function to get cn item id by element
	get_item_id_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parent until it found the container
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_cn_item')){    // found the element
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}

		if(!parent_ele) return 0;

		var item_id = parent_ele.id.split('-')[1];
		return item_id;
	},
	// function when user click to show reason option
	show_reason_option: function(item_id){
		var div_item_reason_list = $('div-item_reason_list');
		
		if(this.reason_for_item_id != item_id || div_item_reason_list.style.display=='none'){
			var curr_reason = this.f['items_list['+item_id+'][reason]'].value.trim();
			div_item_reason_list.show();
			Position.clone($('div-item_reason-'+item_id), div_item_reason_list, {setHeight: false, setWidth:true});
			
			div_item_reason_list.getElementsBySelector('li').each(function (obj,idx){
				if (obj.innerHTML == curr_reason){
					obj.className = 'current';
					obj.scrollToPosition;
				}
				else{
					obj.className = '';		
				}
			});
			this.reason_for_item_id = item_id;
		}
		else{
			div_item_reason_list.hide();
			this.reason_for_item_id = 0;
		}
	},
	// function when user selected reason
	item_reason_selected: function(reason){
		if(!this.reason_for_item_id)	return;
		
		this.f['items_list['+this.reason_for_item_id+'][reason]'].value = reason;
		this.item_reason_changed(this.reason_for_item_id);
		this.reason_for_item_id = 0;
		$('div-item_reason_list').hide();
	},
	// function when item reason changed
	item_reason_changed: function(item_id){
		uc(this.f['items_list['+item_id+'][reason]']);
	},
	// function to recalculate from start to bottom
	totally_recalculate: function(){
		// get all cn item row
		var tr_cn_item_list = $$('#tbody_items tr.tr_cn_item');
		// loop each row
		for(var i=0,len=tr_cn_item_list.length; i<len; i++){
			// get item id
			var item_id = this.get_item_id_by_ele(tr_cn_item_list[i]);
			// calc each item
			this.item_row_recal(item_id, false);
		}
		// calc total
		this.calc_all_items();
	},
	// function to close the form
	form_close_clicked: function(){
		document.location = phpself;
	},
	// function when user want exit
	confirmExit: function(e) {
		
		if(!e) e = window.event;
		if(needCheckExit){
			//e.cancelBubble is supported by IE - this will kill the bubbling process.
			/*e.cancelBubble = true;
			e.returnValue = 'Are You sure you want to leave at this time? Sales will be in-correct if finalize does not fully complete. '; //This is displayed on the dialog
		
			//e.stopPropagation works in Firefox.
			if (e.stopPropagation) {
				e.stopPropagation();
				e.preventDefault();
			}*/
			//console.log('exit');
			//return 'Data had not being saved.';
		}
	},
	// function to submit form
	submit_form: function(params){
		if(!params)	params = {};
	
		var action = params['a'] || 'save';
		
		// check save/confirm
		if(action == 'save'  || action == 'confirm'){
			if(!this.is_valid_for_save(action))	return false;
		}
		
		var THIS = this;
		
		// action
		this.f['a'].value = 'ajax_'+action;
		
		// construct params
		var params = $(this.f).serialize();
		
		// show curtain 2
		this.toggle_processing_form(true);
		
		// ajax_add_item_row
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						needCheckExit = false;
						// redirect to module main page
						if(ret['approved'])	action = 'approve';
						document.location = phpself+'?t='+action+'&cn_id='+ret['cn_id'];
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
				THIS.toggle_processing_form(false);
			}
		});
	},
	// function to show/hide form loading
	toggle_processing_form: function(is_show){
		
		if(is_show){
			center_div($('div_wait_popup').show());
			curtain(true,'curtain2');
		}else{
			$('div_wait_popup').hide();
			curtain(false,'curtain2');
		}
	},
	// function when user click on delete form
	form_delete_clicked: function(){
		document.f_a['deleted_reason'].value = '';
		var p = prompt('Enter reason to Delete :');
		if (p==null || p.trim()=='') return;
		document.f_a['deleted_reason'].value = p;
		if (confirm('Delete this CN?')){
			this.submit_form({'a':'delete'});
		}
	},
	// function when user click confirm form
	form_confirm_clicked: function(){
		if (confirm('Finalise and submit for approval?')){
			this.submit_form({'a':'confirm'});
		}
	},
	// function when user click reset form
	form_reset_clicked: function(){
		document.f_do_reset['reason'].value = '';
		var p = prompt('Enter reason to Reset :');
		if (p==null || p.trim()=='' ) return false;
		document.f_do_reset['reason'].value = p;

		if(!confirm('Are you sure to reset?'))  return false;

		document.f_do_reset.submit();
		return false;
	},
	check_do_no: function(){
		document.f_a['inv_no'].value=document.f_a['inv_no'].value.trim();
		var do_no=document.f_a['inv_no'].value;

		if (do_no!="") {
			var THIS = this;
			THIS.toggle_processing_form(true);

            var params={};
			params['a']="ajax_check_do_no";
			params['do_no']=do_no;
			params['branch_id']=document.f_a['branch_id'].value;

			// ajax_add_item_row
			ajax_request(phpself, {
				parameters: params,
				method: 'post',
				onComplete: function(ret){
					var str = ret.responseText.trim();

					try{
						ret = JSON.parse(str);

						if (ret!==false) {
                            document.f_a['inv_date'].value = ret.do_date;

							the_do=ret;

							document.f_a['do_id'].value = the_do.id;

							if(document.f_a['cust_name'].value.trim() == ''){	// only replace if customer name is empty
								switch (the_do.do_type) {
									case 'transfer':
									
										document.f_a['cust_name'].value = the_do.branch_info.description;
										document.f_a['cust_address'].value = the_do.branch_info.address;
										document.f_a['cust_brn'].value = the_do.branch_info.company_no;

									break;
									case 'open':
										document.f_a['cust_name'].value = the_do.open_info.name;
										document.f_a['cust_address'].value = the_do.open_info.address;
										document.f_a['cust_brn'].value = "";
									break;
									case 'credit_sales':
										document.f_a['cust_name'].value = the_do.debtor_info.description;
										document.f_a['cust_address'].value = the_do.debtor_info.address;
										document.f_a['cust_brn'].value = the_do.debtor_info.company_no;
									break;
								}
							}

							if($('div_sheets').style.display=='none'){
								THIS.need_refresh_sheet();
							}

						}
						else{
							alert("Invoice not found.");
							document.f_a['inv_no'].value = "";
							document.f_a['inv_date'].value = "";
						}
					}catch(ex){ // failed to decode json, it is plain text response
						document.f_a['inv_no'].value = "";
						document.f_a['inv_date'].value = "";
					}

					THIS.toggle_processing_form(false);
				}
			});
		}else{
			$('div_sheets').hide();
			$('p_submit_btn').hide();
			document.f_a['inv_date'].value = "";
			document.f_a['do_id'].value = "";
			document.f_a['cust_address'].value = "";
			document.f_a['cust_brn'].value = "";
			document.f_a['cust_name'].value = "";
			document.f_a['remark'].value = "";
			document.f_a['discount'].value = "";
		}
	},
	show_do_item:function(obj,item_id){
		$('div_do').hide();
		$('div-do_item').update("");

		var rect = obj.parentNode.parentNode.getBoundingClientRect();

		var THIS = this;
		//THIS.toggle_processing_form(true);

		var params={};
		params['a']="ajax_show_do_item";
		
		if(document.f_a['return_type'].value == "multiple_inv"){
			if(this.f['items_list['+item_id+'][return_inv_no]'].value == ""){
				alert("Please enter Invoice No. first");
				this.f['items_list['+item_id+'][return_inv_no]'].focus();
				return false;
			}
			params['do_id']=this.f['items_list['+item_id+'][return_do_id]'].value.trim();
		}else{
			
			params['do_id']=document.f_a['do_id'].value;
		}
		
		params['branch_id']=document.f_a['branch_id'].value;
		params['do_item_id']=this.f['items_list['+item_id+'][do_item_id]'].value.trim();

		// ajax_add_item_row
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(ret){
				var str = ret.responseText.trim();

				$('div-do_item').update(str);

				//THIS.toggle_processing_form(false);

				center_div($('div_do').show());
			}
		});
	},
	close_do_item:function(){
		$('div_do').hide();
		$('div-do_item').update("");
		//curtain(false,'curtain2');
	},
	build_do_items:function(sel_do_item_id){
		var str='<input id="do_items_all" type="checkbox" onclick="CNOTE_OPEN.check_do_items(this)"/> <label for="do_items_all">All</label>';
		str+='<ul style="list-style-type:none;margin:0;padding:0;" id="do_items_list">';
		for (i in the_do.items) {
			if (sel_do_item_id.indexOf(i) < 0) {
                continue;
            }
			var desc=this.escapeHtml(the_do.items[i].description);
			str+='<li title="'+the_do.items[i].sku_item_id+','+the_do.items[i].sku_item_code+'" style="display:block;margin:0;padding:2px;" onmouseover="this.style.backgroundColor=\'#ff9\'" onmouseout="this.style.backgroundColor=\'\'" id="li'+i+'">'+
			'<input id="do_items_'+i+'" value="'+the_do.items[i].sku_item_id+','+the_do.items[i].sku_item_code+'" type="checkbox" class="sku_item" title="'+desc+'" artno="'+the_do.items[i].artno+'" mcode="'+the_do.items[i].mcode+'" do_item_id="'+i+'">'+
			'<label class="clickable" for="do_items_'+i+'">'+the_do.items[i].description+'&nbsp;&nbsp;&nbsp;';
			if (the_do.items[i].artno) {
                str+='<font color=#009911>(Article: '+the_do.items[i].artno+')</font>';
            }
			else{
				str+='<font color=#009911>(MCode: '+the_do.items[i].mcode+')</font>';
			}
			str+='<br>'+
			'&nbsp;&nbsp;&nbsp;&nbsp;'+
			'<span class=small>'+
			'<font color=blue>ARMS Code:</font> '+the_do.items[i].sku_item_code+
			'</span></label>'+
			'<li>';

        }
		str+='</ul>';

		str+='<center><button class="btn btn-primary" type="button" onclick="CNOTE_OPEN.add_do_items();">Add</button><button class="btn btn-error" type="button" onclick="CNOTE_OPEN.toggle_do_items(false);">Close</button><center>';

		$('autocomplete_sku_choices').update(str);
	},
	add_do_items:function(){
		//var opts = $('autocomplete_sku_choices').getElementsByTagName('input');
		//
		//var do_items="";
		//for(var i=0;i<opts.length;i++)
		//{
		//	if (opts[i].checked && parseInt(opts[i].getAttribute('do_item_id'))>0){
		//		do_items += opts[i].getAttribute('do_item_id')+",";
		//	}
		//}
		//
		//do_items = do_items.slice(0, -1);
		//
		//document.f_a['do_items'].value=do_items;
		//
		add_autocomplete();
		this.toggle_do_items(false);
	},
	toggle_do_items: function(is_show){
		if(is_show){
			if($("inv_no").value.trim() == ""){
				alert("Invoice No. cannot be empty");
				$("inv_no").focus();
				return false;
			}
			
			var exist_do_item_id = new Array();
			var tr_cn_item_list = $$('#tbody_items tr.tr_cn_item');
			for(var i=0,len=tr_cn_item_list.length; i<len; i++){
				var item_id = this.get_item_id_by_ele(tr_cn_item_list[i]);
				
				var do_item_id = float(this.f['items_list['+item_id+'][do_item_id]'].value);
				exist_do_item_id.push(do_item_id);
			}
			
			var sel_do_item_id = new Array();
			for(i in the_do.items) {
				if (exist_do_item_id.indexOf(i) < 0) {
					sel_do_item_id.push(i);
				}	
			}
			
			if(sel_do_item_id.length > 0) {
				$('autocomplete_sku_choices').setAttribute('data-style',$('autocomplete_sku_choices').getAttribute('style'));
				this.build_do_items(sel_do_item_id);
				$('autocomplete_sku_choices').style.zIndex=10000;
				$('autocomplete_sku_choices').style.height='auto';
				center_div($('autocomplete_sku_choices').show());
				curtain(true,'curtain2');
			}else {
				alert("All DO items have been assigned.");
			}
		}else{
			$('autocomplete_sku_choices').setAttribute('style',$('autocomplete_sku_choices').getAttribute('data-style'));
			$('autocomplete_sku_choices').hide();
			$('autocomplete_sku_choices').update('');
			curtain(false,'curtain2');
		}
	},
	escapeHtml: function(text) {
		var map = {
		  '&': '&amp;',
		  '<': '&lt;',
		  '>': '&gt;',
		  '"': '&quot;',
		  "'": '&#039;'
		};
	  
		return text.replace(/[&<>"']/g, function(m) { return map[m]; });
	},
	check_do_items: function(obj){
		var checkboxes = $$("#do_items_list input[type=checkbox]");

		checkboxes.each(function(box){
			box.checked = obj.checked;
		});
	},
	return_type_changed: function(obj){
		if(obj.value == "single_inv"){
			$('tr_inv').show();
			$("btn_cn_available_do").show();
			$("note1").update("* NOTE: Price, UOM and GST Code will auto load from invoice");
		}else{
			$('tr_inv').hide();
			$("btn_cn_available_do").hide();
			$("note1").update("* NOTE: Price, UOM and GST Code will auto load from invoice when invoice no changed. Default Price will be 0.00 when item added");
		}
		document.f_a['return_type'].value = obj.value;
		document.f_a['inv_no'].value = "";
		document.f_a['inv_date'].value = "";
		document.f_a['do_id'].value = "";
		document.f_a['cust_address'].value = "";
		document.f_a['cust_brn'].value = "";
		document.f_a['cust_name'].value = "";
		document.f_a['remark'].value = "";
		document.f_a['discount'].value = "";
		CNOTE_OPEN.need_refresh_sheet();
	},
	invoice_no_changed:function(obj,row_id){
		var return_inv_id = obj.value.trim();
		var return_sku_item = document.f_a['items_list['+row_id+'][sku_item_id]'].value.trim();
		var THIS = this;
		if (return_inv_id != "") {
			//check duplicate
			var count = 0;
			var tr_cn_item_list = $$('#tbody_items tr.tr_cn_item');
			for(var i=0,len=tr_cn_item_list.length; i<len; i++){
				var item_id = this.get_item_id_by_ele(tr_cn_item_list[i]);
				
				if(row_id == item_id){
					continue;
				}
				
				if(document.f_a['items_list['+item_id+'][return_inv_no]'].value.trim() == ""){
					continue;
				}
				
				if(document.f_a['items_list['+item_id+'][return_inv_no]'].value.trim() == return_inv_id && document.f_a['items_list['+item_id+'][sku_item_id]'].value.trim() == return_sku_item){
					obj.value = "";
					obj.focus();
					document.f_a['items_list['+row_id+'][return_inv_date]'].value = "";
					alert("Item already added in Credit Note");
					return false;
				}
			}
			
			
			THIS.toggle_processing_form(true);
			var params = $(this.f).serialize()+'&a=ajax_check_inv_no&tmp_row_id='+row_id+'&return_inv_no='+return_inv_id;
			
			ajax_request(phpself, {
				parameters: params,
				method: 'post',
				onComplete: function(ret){
					var str = ret.responseText.trim();
					
					try{
						ret = JSON.parse(str);
						if (ret.err) {
							obj.value = "";
							document.f_a['items_list['+row_id+'][return_inv_date]'].value = "";
							document.f_a['items_list['+row_id+'][return_do_id]'].value = "";
							document.f_a['items_list['+row_id+'][do_item_id]'].value = "";
							document.f_a['items_list['+row_id+'][do_total_qty]'].value = "";
							document.f_a['items_list['+row_id+'][price]'].value = "0.00";
							document.f_a['items_list['+row_id+'][uom_id]'].value = "1";
							document.f_a['items_list['+row_id+'][uom_fraction]'].value = "1";
							document.f_a['items_list['+row_id+'][sel_uom]'].value = "1,1";
							document.f_a['items_list['+row_id+'][display_price]'].value = "0.00";
							document.f_a['items_list['+row_id+'][display_price_is_inclusive]'].checked = false;
							document.f_a['items_list['+row_id+'][ctn]'].value = "-";
							document.f_a['items_list['+row_id+'][ctn]'].disabled = true;
							$('div_price_info-' + row_id).hide();
							alert(ret.err);
							obj.focus();
						}else{
							document.f_a['items_list['+row_id+'][return_inv_date]'].value = ret.items[row_id]['return_inv_date'];
							document.f_a['items_list['+row_id+'][return_do_id]'].value = ret.items[row_id]['return_do_id'];
							document.f_a['items_list['+row_id+'][do_item_id]'].value = ret.items[row_id]['do_item_id'];
							document.f_a['items_list['+row_id+'][do_total_qty]'].value = ret.items[row_id]['do_total_qty'];
							document.f_a['items_list['+row_id+'][price]'].value = round(ret.items[row_id]['price'],2);
							document.f_a['items_list['+row_id+'][uom_id]'].value = ret.items[row_id]['uom_id'];
							document.f_a['items_list['+row_id+'][uom_fraction]'].value = ret.items[row_id]['uom_fraction'];
							document.f_a['items_list['+row_id+'][sel_uom]'].value = ret.items[row_id]['uom_id'] + "," +ret.items[row_id]['uom_fraction'];
							
							if(is_under_gst){
								document.f_a['items_list['+row_id+'][gst_id]'].value = ret.items[row_id]['gst_id'];
								document.f_a['items_list['+row_id+'][gst_code]'].value = ret.items[row_id]['gst_code'];
								document.f_a['items_list['+row_id+'][gst_rate]'].value = ret.items[row_id]['gst_rate'];
								document.f_a['items_list['+row_id+'][item_gst]'].value = ret.items[row_id]['gst_id'];
								document.f_a['items_list['+row_id+'][display_price]'].value = round(ret.items[row_id]['display_price'],2);
								document.f_a['items_list['+row_id+'][display_price_is_inclusive]'].checked = ret.items[row_id]['display_price_is_inclusive'];
							}
							//alert("r:" + document.f_a['items_list['+row_id+'][uom_fraction]'].value);
							if(float(ret.items[row_id]['uom_fraction']) == 1){//alert("aaa");
								document.f_a['items_list['+row_id+'][ctn]'].value = "-";
								document.f_a['items_list['+row_id+'][ctn]'].disabled = true;
							}else{//alert("bbb");
								document.f_a['items_list['+row_id+'][ctn]'].value = "0";
								document.f_a['items_list['+row_id+'][ctn]'].disabled = false;
							}
							
							if(!(is_under_gst && !ret.items[row_id]['display_price_is_inclusive'])){
								$('div_price_info-' + row_id).show();
								if(is_under_gst){
									$('span_price_label-' + row_id).show();
								}
							}							
							$('span_price_label-'+row_id).update(round(ret.items[row_id]['price'],2));
						}
					}catch(ex){
					}
					THIS.toggle_processing_form(false);
				}
			});
		}else{
			obj.value = "";
			document.f_a['items_list['+row_id+'][return_inv_date]'].value = "";
			document.f_a['items_list['+row_id+'][return_do_id]'].value = "";
			document.f_a['items_list['+row_id+'][do_item_id]'].value = "";
			document.f_a['items_list['+row_id+'][do_total_qty]'].value = "";
			alert("Invoice No. cannot be empty");
			obj.focus();
		}
		document.f_a['items_list['+row_id+'][pcs]'].value  = "0";
		document.f_a['items_list['+row_id+'][ctn]'].value  = "";
		THIS.check_row_ctn_pcs_input(row_id);
		THIS.item_row_changed(row_id);
	},
	
	filter_debtor_desc: function(){
		var str = document.f_search_debtor['debtor_desc'].value.trim().toLowerCase();
		if(str==''){
			$$('#tbl_debtor_list tr.db_row').each(function(ele){
				$(ele).show();
			});
			return false;
		}
		
		$$('#tbl_debtor_list tr.db_row').each(function(ele){
			var code = $(ele).getElementsBySelector('.db_code')[0].innerHTML.toLowerCase();
			var desc = $(ele).getElementsBySelector('.db_desc')[0].innerHTML.toLowerCase();
			if(desc.indexOf(str)>=0 || code.indexOf(str)>=0)    $(ele).show();
			else    $(ele).hide();
		});
	},
	
	filter_branch_desc: function(){
		var str = document.f_search_branch['branch_desc'].value.trim().toLowerCase();
		if(str==''){
			$$('#tbl_branch_list tr.br_row').each(function(ele){
				$(ele).show();
			});
			return false;
		}
		
		$$('#tbl_branch_list tr.br_row').each(function(ele){
			var desc = $(ele).getElementsBySelector('.br_desc')[0].innerHTML.toLowerCase();
			if(desc.indexOf(str)>=0)    $(ele).show();
			else    $(ele).hide();
		});
	},
	choose_debtor_to_add: function(){
		curtain(true);
		center_div($('div_choose_debtor_to_add').show());
		
		//if($('tbl_debtor_list'))	fxheaderInit('tbl_debtor_list',300);
	},

	choose_branch_to_add: function(){
		curtain(true);
		center_div($('div_choose_branch_to_add').show());
	},
	
	choose_this_debtor: function(ele){
		var db_desc = $(ele).getElementsBySelector('.db_desc')[0];
		var db_address = $(ele).getElementsBySelector('.db_address')[0];
		var db_company_no = $(ele).getElementsBySelector('.db_company_no')[0].innerHTML;
		
		$('cust_name').value = (db_desc.textContent || db_desc.innerText);
		$('cust_address').value = (db_address.textContent || db_address.innerText);
		$('cust_address').value = (db_address.textContent || db_address.innerText);
		$('cust_brn').value = db_company_no;

		default_curtain_clicked();
	},

	choose_this_branch: function(ele){
		var br_desc = $(ele).getElementsBySelector('.br_desc')[0];
		var br_address = $(ele).getElementsBySelector('.br_address')[0];
		var br_company_no = $(ele).getElementsBySelector('.br_company_no')[0].innerHTML;

		$('cust_name').value = (br_desc.textContent || br_desc.innerText);
		$('cust_address').value = (br_address.textContent || br_address.innerText);
		$('cust_brn').value = br_company_no;
		
		default_curtain_clicked();
	}
};

function add_grn_barcode_item(value){
	CNOTE_OPEN.add_grn_barcode_item(value);
}

function do_ajax_add(query_string){
	CNOTE_OPEN.add_item(query_string);
}

function curtain_clicked(){
    $('div_choose_branch_to_add').hide();
    $('div_choose_debtor_to_add').hide();
}
{/literal}
</script>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{$PAGE_TITLE}

					{if is_new_id($form.id)}
						(NEW)
					{else}
						ID#{$form.id}
					{/if}
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
		<h5 class="content-title mb-0 my-auto ml-4 text-primary">
			Status:
	{if $form.active and $form.status eq 1 and $form.approved eq 1}
		Fully Approved
	{elseif $form.active eq 1 and $form.status == 1}
		In Approval Cycle
	{elseif $form.active eq 1 and !$form.status}
		Draft
	{elseif $form.active eq 1 and $form.status eq 2}
		Rejected
	{elseif $form.active eq 1 and $form.status eq 5}
		Cancelled/Terminated
	{elseif !$form.active}
		Deleted
	{/if}
		</h5>
	</div>
</div>

{if $form.adj_id_list}
<div class="stdframe" style="background-color:#F0FFF0;">
    <h4>Adjustment Docs</h4>
    {foreach from=$form.adj_id_list item=adj_id name=fadj}
        {if !$smarty.foreach.fadj.first}, {/if}
		<a href="adjustment.php?a=view&branch_id={$form.branch_id}&id={$adj_id}" target="_blank">ID#{$adj_id}</a>
    {/foreach}
</div>
{/if}

{include file='approval_history.tpl' approval_history=$form.approval_history}

<div id="div-item_reason_list" style="display:none;position:absolute;z-index:100;background:#fff;border:1px solid #000;margin: 0 0 0 0;height:200px;width:342px;overflow:auto;">
	<ul>
		{foreach from=$itemReasonList item=v}
			<li onclick="CNOTE_OPEN.item_reason_selected('{$v}');">{$v|upper}</li>
		{/foreach}
	</ul>
</div>

<div id="div_wait_popup" style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
	<p align="center">
		Please wait..
		<br /><br />
		<img src="ui/clock.gif" border="0" />
	</p>
</div>

<div class="ndiv" id="div_do" style="position:absolute;left:150;top:150;display:none;">
	<div class="blur">
		<div class="shadow">
			<div class="content">
				<div style="height:20px;background-color:#6883C6;position:absolute;left:0;top:0;width:100%;color:white;font-weight:bold;padding:2px;" id="div_branch_header">
					<div class="small" style="position:absolute; right:10; text-align:right;top:2px;"><a href="javascript:void(CNOTE_OPEN.close_do_item());"><img src="ui/closewin.png" border="0" align="absmiddle"></a></div>
				</div>

				<div id="div-do_item" style="margin-top:20px;">
					
				</div>
			</div>
		</div>
	</div>
</div>


<form name="f_do_reset" method="post" style="display:none;">
	<input type=hidden name="a" value="do_reset">
	<input type=hidden name="branch_id" value="{$form.branch_id}">
	<input type=hidden name="id" value="{$form.id}" >
	<input type=hidden name="reason" value="">
</form>

<form name="f_a" method="post" onSubmit="return false;">
	<input type="hidden" name="a" />
	<input type="hidden" name="branch_id" value="{$form.branch_id|default:$sessioninfo.branch_id}" />
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="total_gross_amount" value="{$form.total_gross_amount}" />
	<input type="hidden" name="total_gst_amount" value="{$form.total_gst_amount}" />
	<input type="hidden" name="total_amount" value="{$form.total_amount}" />
	<input type="hidden" name="gross_discount_amt" value="{$form.gross_discount_amt}" />
	<input type="hidden" name="gst_discount_amt" value="{$form.gst_discount_amt}" />
	<input type="hidden" name="discount_amt" value="{$form.discount_amt}" />
	<input type="hidden" name="sub_total_gross_amount" value="{$form.sub_total_gross_amount}" />
	<input type="hidden" name="sub_total_gst_amount" value="{$form.sub_total_gst_amount}" />
	<input type="hidden" name="sub_total_amount" value="{$form.sub_total_amount}" />
	<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}" />
	<input type="hidden" name="is_under_gst" value="{$form.is_under_gst}"/>
	<input type="hidden" name="edit_time" value="{$form.edit_time}"/>
	<input type="hidden" name="total_ctn" value="{$form.total_ctn}"/>
	<input type="hidden" name="total_pcs" value="{$form.total_pcs}"/>
	<input type="hidden" name="total_qty" value="{$form.total_qty}"/>
	<input type="hidden" name="active" value="{$form.active}"/>
	<input type="hidden" name="status" value="{$form.status}"/>
	<input type="hidden" name="deleted_reason" />
	<input type="hidden" id="do_id" name="do_id" value="{$form.do_id}"/>
	{*<input type="hidden" id="do_items" name="do_items" value=""/>*}
	<div class="card mx-3">
		<div class="card-body">
			<div class="stdframe">
				<h4>General Information</h4>
				<table width="100%">
					{* CN Date *}
					<tr>
						<td width="120"><b class="form-label">CN Date<span class="text-danger" title="Required Field"> *</span></b></td>
						<td>
							<div class="form-inline">
								<input  name="cn_date" id="inp_cn_date"  onchange="CNOTE_OPEN.on_cn_date_changed();"  maxlength="10"  value="{$form.cn_date|date_format:"%Y-%m-%d"}" 
							class="required form-control" title="CN Date" />
							{if $can_edit}
								<img align="absmiddle" src="ui/calendar.gif" id="img_cn_date" style="cursor: pointer;" title="Select Date" />
							{/if}
							
							</div>
						</td>
					</tr>
					
					<tr>
						<td width="120"><b class="form-label">Return Type<span class="text-danger" title="Required Field"> *</span> </b></td>
						<td>
							<input type="hidden" name="return_type" value="{$form.return_type}" />
							<input name="rbtn_return_type" class="return_type" onchange="CNOTE_OPEN.return_type_changed(this);" value="single_inv"  type="radio" {if $form.return_type neq "multiple_inv"}checked{/if} {if !is_new_id($form.id) && !$is_refresh}disabled{/if}>Single Invoice
							<input name="rbtn_return_type" class="return_type" onchange="CNOTE_OPEN.return_type_changed(this);" value="multiple_inv" type="radio" {if $form.return_type eq "multiple_inv"}checked{/if} {if !is_new_id($form.id) && !$is_refresh}disabled{/if}>Multiple Invoice
							
						</td>
					</tr>
		
		
					{* Invoice *}
					<tr id="tr_inv" {if $form.return_type eq "multiple_inv"}style="display:none"{/if}>
						<td valign="top"><b class="form-label">Invoice</b> [<a href="javascript:void(alert('Only accept DO Invoice Number'))">?</a>]</td>
						<td>
							<table>
								<tr>
									<td class="form-label">No.<span class="text-danger" title="Required Field"> *</span></td>
									<td>
										<input type="text" id="inv_no" name="inv_no"  class="required form-control" title="Invoice No." value="{$form.inv_no}" onChange="uc(this); CNOTE_OPEN.check_do_no();" {if !is_new_id($form.id) && !$is_refresh}readonly{/if}/>
										
									</td>
								</tr>
								<tr>
									<td class="form-label">Date<span class="text-danger" title="Required Field"> *</span></td>
									<td>
										<input name="inv_date" id="inp_inv_date"  value="{$form.inv_date|date_format:"%Y-%m-%d"}" class="required form-control title="Invoice Date" readonly/>
										{*if $can_edit}
											<img align="absmiddle" src="ui/calendar.gif" id="img_inv_date" style="cursor: pointer;" title="Select Date" />
										{/if*}
									
									</td>
								</tr>
							</table>
							
						</td>
					</tr>
								
					{* Owner *}
					{if !is_new_id($form.id)}
						<tr>
							<td align="left"><b class="form-label">Owner</b></td>
							<td style="color:blue;"><input type="hidden" name="owner_username" value="{$form.owner_username}" />{$form.owner_username}</td>
						</tr>
					{/if}
		
					{* Remark *}
					<tr>
						<td valign="top"><b class="form-label">Remarks</b></td>
						<td>
							<textarea class="form-control"  rows="2" cols="28" name="remark" onchange="uc(this);">{$form.remark}</textarea>
						</td>
					</tr>
					
					{* Customer *}
					<tr>
						<td valign="top"><b class="form-label">Customer</b></td>
						<td>
							<table>
								<tr>
									<td class="form-label">Name<span class="text-danger" title="Required Field"> *</span></td>
									<td>
										<input class="form-control" type="text" id="cust_name" name="cust_name" size="30" maxlength="100" value="{$form.cust_name}" onChange="CNOTE_OPEN.cust_name_changed();" />
								
										{if $can_edit}
											<input class="btn btn-primary mt-2 mb-2" type=button value="Choose Debtor" onclick="CNOTE_OPEN.choose_debtor_to_add();" />
											<input class="btn btn-primary  mt-2 mb-2" type=button value="Choose Branch" onclick="CNOTE_OPEN.choose_branch_to_add();" />
										{/if}
									</td>
								</tr>
								<tr>
									<td class="form-label">Address</td>
									<td>
										<textarea class="form-control" id="cust_address"  name="cust_address" rows="3" cols="20">{$form.cust_address}</textarea>
									</td>
								</tr>
								<tr>
									<td class="form-label">BRN</td>
									<td><input class="form-control" type="text" id="cust_brn" name="cust_brn" size="30" maxlength="20" value="{$form.cust_brn}" /></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td valign="top"><b class="form-label">Discount</b></td>
						<td>
						<div class="form-inline">
							<input class="form-control" type="text" id="discount" name="discount" value="{$form.discount}" onchange="CNOTE_OPEN.totally_recalculate();"/> 
						<b>[<a href="javascript:void(show_discount_help());">?</a>]</b> 
						<font color="blue">Discount on Invoice: <span id="discount_on_invoice">-</span></font>
						</div>
					</td>
					</tr>
				</table>
				
				<div id="div_refresh" style="display:none; padding-top:10px; padding-left:130px; ">
					<input id="refresh_btn" type="button" onclick="void(CNOTE_OPEN.refresh_tables())" style="font-size:1.5em; color:#fff; background:#091" value="click here to continue">
				</div>
			</div>
		
			<span id="note1">
				<div class="alert alert-primary">
					<b>* NOTE:</b>{if $form.return_type eq "multiple_inv"} Price, UOM and GST Code will auto load from invoice when invoice no changed. Default Price will be 0.00 when item added
				{else} Price, UOM and GST Code will auto load from invoice{/if}
				</div>
			</span>
		</div>
	</div>
	<div id="div_sheets" style="{if !$form.cust_name}display:none;{/if}">
		{include file='cnote.open.sheet.tpl'}
		
		{if $can_edit}
			<div style="background:#ddd;border:1px solid #999;" id="div_add_item">
				{include file='scan_barcode_autocomplete.tpl' need_hr_out_bottom=1}
				{include file=sku_items_autocomplete_multiple_add.tpl allow_edit=1 default_mcode=1}
				<span id="span_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
			</div>
			<script>reset_sku_autocomplete();</script>
		{/if}
	</div>
	
	
</form>

<p id="p_submit_btn" align="center">
	{if $form.is_approval and $form.active==1 and $form.status==1 and $form.approved==0 and $form.approval_screen}
		<input type="button" value="Approve" style="background-color:#f90; color:#fff;" onclick="CNOTE_APPROVAL.do_approve();">
		<input type="button" value="Reject" style="background-color:#f90; color:#fff;" onclick="CNOTE_APPROVAL.do_reject();">
		<input type="button" value="Terminate" style="background-color:#900; color:#fff;" onclick="CNOTE_APPROVAL.do_cancel();">
	{/if}

	{if !$form.approval_screen}
		{if $can_edit && $can_save}
			{if $form.active && (!$form.status || $form.status==2)}
				<input type="button" class="btn btn-success" value="Save & Close" onclick="CNOTE_OPEN.submit_form();" />
			{/if}
			
			{if !is_new_id($form.id)}
				<input type="button" class="btn btn-error" value="Delete" onclick="CNOTE_OPEN.form_delete_clicked()" />
			{/if}
			<input type="button" class="btn btn-error" value="Close" onclick="CNOTE_OPEN.form_close_clicked();" />
			
			{if $form.active && (!$form.status || $form.status==2)}
				<input class="btn btn-primary" type="button" value="Confirm" onclick="CNOTE_OPEN.form_confirm_clicked();" />
			{/if}
		{else}
			{if $form.branch_id eq $sessioninfo.branch_id and $form.active and $form.status eq 1 and $form.approved and ($sessioninfo.level>=$config.doc_reset_level)}
				<input class="btn btn-warning" type="button" value="Reset" onclick="CNOTE_OPEN.form_reset_clicked();" />
			{/if}
			<input class="btn btn-error" type="button" value="Close" onclick="CNOTE_OPEN.form_close_clicked();">
		{/if}
	{/if}

</p>

<div id="div_choose_debtor_to_add" style="display:none;position:absolute;z-index:10000;width:500px;height:450px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;">
	<div id="div_choose_debtor_to_add_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
		<span style="float:left;">Available Debtor  Details</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_choose_debtor_to_add_content" style="padding:2px;">
		<form name="f_search_debtor" onSubmit="CNOTE_OPEN.filter_debtor_desc();return false;">
			<b>Filter by Description:</b>
			<input type="text" size="30" name="debtor_desc" />
			<input type="submit" value="Refresh" />
		</form>
		<form name="f_choose_debtor" onSubmit="return false;">
		<div style="height:350px;border:1px solid grey;overflow-x:hidden;overflow-y:auto;">
		<table id="tbl_debtor_list" width="100%">
			<tr style="background:#ffc;">
				<th width="30">&nbsp;</th>
				<th width="80">Code</th>
				<th>Description</th>
				<th>Address</th>
				<th nowrap>Company No.</th>
			</tr>
			<tbody style="background:#fff;">
			{foreach from=$debtor key=id item=r name=f}
				<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="clickable db_row" onClick="CNOTE_OPEN.choose_this_debtor(this);" special_exemption="{$r.special_exemption}">
					<td>{$smarty.foreach.f.iteration}.</td>
					<td>{$r.code}
						<span class="db_code" style="display:none;">{$r.code}</span>
					</td>
					<td>{$r.description|truncate:30:'...'}
						<span class="db_desc" style="display:none;">{$r.description}</span>
					</td>
					<td>{$r.address|truncate:30:'...'}
						<span class="db_address" style="display:none;">{$r.address}</span>
					</td>
					<td>{$r.company_no}
						<span class="db_company_no" style="display:none;">{$r.company_no}</span>
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		</div>
		<p align="center">
			<input type="button" value="Close" name="close" onClick="default_curtain_clicked();" />
		</p>
		</form>
	</div>
</div>

<div id="div_choose_branch_to_add" style="display:none;position:absolute;z-index:10000;width:500px;height:450px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;">
	<div id="div_choose_branch_to_add_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
		<span style="float:left;">Available Branch  Details</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_choose_branch_to_add_content" style="padding:2px;">
		<form name="f_search_branch" onSubmit="CNOTE_OPEN.filter_branch_desc();return false;">
			<b>Filter by Description:</b>
			<input type="text" size="30" name="branch_desc" />
			<input type="submit" value="Refresh" />
		</form>
		<form name="f_choose_branch" onSubmit="return false;">
		<div style="height:350px;border:1px solid grey;overflow-x:hidden;overflow-y:auto;">
		<table id="tbl_branch_list" width="100%">
			<tr style="background:#ffc;">
				<th width="30">&nbsp;</th>
				<th width="80">Code</th>
				<th>Description</th>
				<th>Address</th>
				<th nowrap>Company Registration No.</th>
			</tr>
			<tbody style="background:#fff;">
			{foreach from=$branch key=id item=r name=f}
				<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="clickable br_row" onClick="CNOTE_OPEN.choose_this_branch(this);">
					<td>{$smarty.foreach.f.iteration}.</td>
					<td class="br_code">{$r.code}</td>
					<td>{$r.description|truncate:30:'...'}
						<span class="br_desc" style="display:none;">{$r.description}</span>
					</td>
					<td>{$r.address|truncate:30:'...'}
						<span class="br_address" style="display:none;">{$r.address}</span>
					</td>
					<td>{$r.company_no}
						<span class="br_company_no" style="display:none;">{$r.company_no}</span>
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		</div>
		<p align="center">
			<input type="button" value="Close" name="close" onClick="default_curtain_clicked();" />
		</p>
		</form>
	</div>
</div>

<script>
{literal}
new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
CNOTE_OPEN.initialize();
new Draggable('div_do');

if($('div_choose_debtor_to_add')){
    new Draggable('div_choose_debtor_to_add',{ handle: 'div_choose_debtor_to_add_header'});
}
if($('div_choose_branch_to_add')){
    new Draggable('div_choose_branch_to_add',{ handle: 'div_choose_branch_to_add_header'});
}
{/literal}
</script>

{if !$form.approval_screen}
	{include file='footer.tpl'}
{/if}
