{*
4/5/2018 11:10 AM Andy
- Fixed roudning decimal issue for Actual Transfer Out.

3/2/2021 3:44 PM Andy
- Enhanced Work Order to can transfer by Weight to Pcs.
- Fixed not allow to edit other branch work order.
*}

{include file='header.tpl'}

<style>
{literal}
span.span_price2{
	font: 80%;
	color: blue;
}
input.inp_qty{
	width:50px;
	background:#fc9;
}

input.inp_doc_allow_decimal{
	width:80px !important;
}
input.inp_rcv_weight{
	width: 50px;
	background:#fc9;
}
input.inp_qty[readonly], input.inp_rcv_weight[readonly]{
	background:#ccc;
}
input.inp_other_cost{
	width:100px;
	background:#fc9;
	text-align: right;
}
span.negative_value{
	color: red;
	font-weight: bold;
}
{/literal}
</style>

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var can_edit = int('{$can_edit}');
var needCheckExit = true;
var global_qty_decimal_points = int('{$config.global_qty_decimal_points}');
var global_cost_decimal_points = int('{$config.global_cost_decimal_points}');
var global_weight_decimal_points = int('{$config.global_weight_decimal_points}');
var action = '{$action}';

// gst
var enable_gst = int('{$config.enable_gst}');
var branch_is_under_gst = int('{$form.branch_is_under_gst}');

{literal}

var WO_OPEN = {
	f: undefined,
	check_need_auto_fill_expect_in_qty: true,
	initialize: function(){
		this.f = document.f_a;
		
		if(can_edit){
			var THIS=this;
			// init calendar
			this.init_calendar();
			
			this.calc_all_items();
			
			// check on exit
			window.onbeforeunload=this.confirmExit;
			
			new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
		}else{
			// disable form
			Form.disable(this.f);
		}
	},
	// function to init calendar
	init_calendar: function(){
		// cn date
		Calendar.setup({
			inputField     :    "inp_adj_date",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_adj_date",
			align          :    "Bl",
			singleClick    :    true
		});
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
			return 'Data had not being saved.';
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
		
		query_string += '&action='+action;
		
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
	                    new Insertion.Bottom($('tbody_items-'+action), ret['html']);
						
						if(action == 'out'){
							if(ret['need_recalc']){	// need recalculate
								for(var i=0; i<ret['item_id_list'].length; i++){
									THIS.out_qty_changed(ret['item_id_list'][i]);
								}
							}
						}else{
							THIS.check_need_auto_fill_expect_in_qty = false;
							for(var i=0; i<ret['item_id_list'].length; i++){
								THIS.init_in_item(ret['item_id_list'][i]);
							}
						}						
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
					if(!err_msg)	err_msg = 'No respond from server.';
				}

				if(err_msg){	// got error
					// prompt the error
					alert(err_msg);
				}else{	// no error
					// reset item no
					THIS.reset_item_no();
				}	
			    
				if(is_scan_barcode){
					// focus back scan barcode if is scan barcode
					$('grn_barcode').focus();
				}else{
					// focus back search sku
					$('autocomplete_sku').focus();
				}
				//  hide loading icon
				THIS.toggle_adding_inprogress(false);
				
				if(action == 'out'){
				
				}else{
					THIS.check_need_auto_fill_expect_in_qty = true;
					THIS.calc_all_items();
				}
			}
		});
	},
	// function to reset item no
	reset_item_no: function(){
		var no = 1;
		$$('#tbody_items-'+action+' span.span_item_no').each(function(ele){
			$(ele).update(no);
			no++;
		});
	},
	// function when user click delete item
	delete_item_clicked: function(item_id){
		if(!item_id)	return;
		
		// get the img element
		var img = $('img-delete_item-'+action+'-'+item_id);
		if(img.src.indexOf('remove')<0)	return;
		
		if(!confirm('Are you sure?'))	return;
		
		// change the img
		img.src = 'ui/clock.gif';
		var THIS = this;
		
		// construct params
		var params = $(this.f).serialize()+'&a=ajax_delete_item&action='+action+'&delete_item_id='+item_id;
		
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
						$('tr_item-'+action+'-'+item_id).remove();
						// reset item no
						THIS.reset_item_no();
						
						// recalculate
						THIS.calc_all_items();
						
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
	// function when user change transfer out qty
	out_qty_changed: function(item_id){
		// check allow decimal point or not
		var doc_allow_decimal = int(this.f['items_list[out]['+item_id+'][doc_allow_decimal]'].value);
		if(!doc_allow_decimal){
			// not allow decimal point, convert to integer
			this.f['items_list[out]['+item_id+'][qty]'].value = int(this.f['items_list[out]['+item_id+'][qty]'].value);
		}
		
		// get qty
		var qty = float(this.f['items_list[out]['+item_id+'][qty]'].value);

		// qty cannot negative
		if(qty < 0){
			this.f['items_list[out]['+item_id+'][qty]'].value = 0;
		}
		
		// trigger row changed
		this.out_item_row_changed(item_id);
	},
	// core function to call when an item row get edit
	out_item_row_changed: function(item_id){
		var recalc_total = this.no_
		// recalculate row total
		this.out_item_row_recal(item_id);
	},
	// core function to recalculate item row total
	out_item_row_recal: function(item_id, recalc_total){
		if(recalc_total == undefined)	recalc_total = true;
		
		///////// TRANSFER OUT ////////////
		// get all data
		var cost = float(this.f['items_list[out]['+item_id+'][cost]'].value);
		var qty = float(this.f['items_list[out]['+item_id+'][qty]'].value);
		var weight_kg = float(this.f['items_list[out]['+item_id+'][weight_kg]'].value);
		
		// calculate total
		var total_cost = float(round(qty * cost, global_cost_decimal_points));
		var total_weight = float(round(qty * weight_kg, global_weight_decimal_points));
		
		// update & show total cost
		this.f['items_list[out]['+item_id+'][line_total_cost]'].value = total_cost;
		$('span_line_total_cost-out-'+item_id).update(round(total_cost, global_cost_decimal_points));
		
		// update & show total weight
		this.f['items_list[out]['+item_id+'][line_exptected_weigth]'].value = total_weight;
		$('span_line_exptected_weigth-out-'+item_id).update(total_weight);

		///////// ACTUAL TRANSFER OUT ////////////
		var actual_received_weigth = float(round(this.f['items_list[out]['+item_id+'][line_actual_received_weigth]'].value, global_weight_decimal_points));
		
		// calculate shrinkage weight
		var shrinkage_weigth = float(round(total_weight - actual_received_weigth, global_weight_decimal_points));
		if(shrinkage_weigth < 0)	shrinkage_weigth = 0;
		
		this.f['items_list[out]['+item_id+'][line_shrinkage_weigth]'].value = shrinkage_weigth;
		$('span_line_shrinkage_weigth-out-'+item_id).update(shrinkage_weigth);
		
		// calculate cost per kg
		var cost_per_weight = 0;
		if(actual_received_weigth > 0){
			cost_per_weight = float(round(total_cost / actual_received_weigth, global_cost_decimal_points));
		}
		this.f['items_list[out]['+item_id+'][cost_per_weight]'].value = cost_per_weight;
		$('span_cost_per_weight-out-'+item_id).update(cost_per_weight);
		
		if(recalc_total){
			this.calc_all_items();
		}
	},
	// function when user change transfer out actual received weight
	out_actual_rcv_weight_changed: function(item_id){
		mf(this.f['items_list[out]['+item_id+'][line_actual_received_weigth]'], global_weight_decimal_points);
		var actual_received_weigth = float(round(this.f['items_list[out]['+item_id+'][line_actual_received_weigth]'].value, global_weight_decimal_points));
		if(actual_received_weigth < 0){
			this.f['items_list[out]['+item_id+'][line_actual_received_weigth]'].value = 0;
		}
		
		// cannot received more than transfer out
		var line_exptected_weigth = float(this.f['items_list[out]['+item_id+'][line_exptected_weigth]'].value);
		if(actual_received_weigth > line_exptected_weigth){
			this.f['items_list[out]['+item_id+'][line_actual_received_weigth]'].value = actual_received_weigth = line_exptected_weigth;
		}
		
		// trigger row changed
		this.out_item_row_changed(item_id);
	},
	// function to recalculate all items
	calc_all_items: function(){
		if(action == 'in'){
			// TRANSFER IN
			this.calc_in_total();
		}else{
			// TRANSFER OUT
			this.calc_out_total();
		}
	},
	// function to recalculate all transfer out
	calc_out_total: function(){
		var total_qty = 0;
		var total_cost = 0;
		var total_weight = 0;
		var total_actual_weight = 0;
		var total_shrinkage_weight = 0;
		
		// get all out item row
		var tr_item_list = $$('#tbody_items-out tr.tr_item');
		
		// loop each row
		for(var i=0,len=tr_item_list.length; i<len; i++){
			// get item id
			var item_id = this.get_item_id_by_ele(tr_item_list[i]);
			
			var line_qty = float(this.f['items_list[out]['+item_id+'][qty]'].value);
			var line_cost = float(this.f['items_list[out]['+item_id+'][line_total_cost]'].value);
			var line_weight = float(this.f['items_list[out]['+item_id+'][line_exptected_weigth]'].value);
			
			var line_actual_received_weigth = float(this.f['items_list[out]['+item_id+'][line_actual_received_weigth]'].value);
			var line_shrinkage_weigth = float(this.f['items_list[out]['+item_id+'][line_shrinkage_weigth]'].value);
			
			total_qty += line_qty;
			total_cost += line_cost;
			total_weight += line_weight;
			
			total_actual_weight += line_actual_received_weigth;
			total_shrinkage_weight += line_shrinkage_weigth;
		}
		
		total_actual_weight = float(round(total_actual_weight, global_weight_decimal_points));
		total_shrinkage_weight = float(round(total_shrinkage_weight, global_weight_decimal_points));
		
		// qty
		$('span_out_total_qty').update(total_qty);
		this.f['out_total_qty'].value = total_qty;
		
		// cost
		$('span_out_total_cost').update(round(total_cost, global_cost_decimal_points));
		this.f['out_total_cost'].value = round(total_cost, global_cost_decimal_points);
		
		// weight
		$('span_out_total_weight').update(total_weight);
		this.f['out_total_weight'].value = total_weight;
		
		// actual weight
		$('span_out_actual_received_weight').update(total_actual_weight);
		this.f['out_actual_received_weight'].value = total_actual_weight;
		
		// shrinkage weight
		$('span_out_shrinkage_weight').update(total_shrinkage_weight);
		this.f['out_shrinkage_weight'].value = total_shrinkage_weight;
		
		// calculate cost per kg
		var cost_per_weight = 0;
		if(total_actual_weight > 0){
			cost_per_weight = float(round(total_cost / total_actual_weight, global_cost_decimal_points));
		}
		this.f['out_actual_cost_per_kg'].value = cost_per_weight;
		$('span_out_actual_cost_per_kg').update(cost_per_weight);
	},	
	// function to get item id by element
	get_item_id_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parent until it found the container
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_item')){    // found the element
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}

		if(!parent_ele) return 0;

		var item_id = parent_ele.id.split('-')[2];
		return item_id;
	},
	// function to close the form
	form_close_clicked: function(){
		document.location = phpself;
	},
	// function to submit form
	submit_form: function(params){
		if(!params)	params = {};
	
		var a = params['a'] || 'save';
		
		// check save/confirm
		if(a == 'save'  || a == 'confirm'){
			if(!this.is_valid_for_save(a))	return false;
		}
		
		var THIS = this;
		
		// action
		this.f['a'].value = 'ajax_'+a;
		
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
						if(ret['approve'])	a = 'approve';
						document.location = phpself+'?t='+a+'&wo_id='+ret['wo_id'];
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
	// function to check whether it is valid for save
	is_valid_for_save: function(a){
		// item count
		if($$('#tbody_items-'+action+' tr.tr_item').length<=0){
			var msg = 'Please add at least one Trasnfer Out item';
			if(action == 'in')	msg = 'Please add at least one Trasnfer In item';
			
			alert(msg);
			return false;
		}
		
		// department
		if(!this.f['dept_id'].value){
			alert('Please select department.');
			return false;
		}
		
		var transfer_type = this.f['transfer_type'].value;
		
		if(a == 'confirm'){	// do extra checking for confirm
			if(action == 'in'){
				// Expected Shrinkage
				var expect_shrinkage_weight = float(this.f['expect_shrinkage_weight'].value);
				if(expect_shrinkage_weight < 0){
					alert('Expected Shrinkage Negative is Not Allowed');
					return false;
				}
				
				// Actual Shrinkage
				var shrinkage_weight = float(this.f['shrinkage_weight'].value);
				if(shrinkage_weight < 0){
					alert('Actual Shrinkage Negative is Not Allowed');
					return false;
				}
			}else if(action == 'out'){
				if(transfer_type == 'w2p'){	// Weight to Pcs
					
				}
			}			
		}
		return true;
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
		if (confirm('Confirm Delete?')){
			this.submit_form({'a':'delete'});
		}
	},
	// function when user click confirm form
	form_confirm_clicked: function(){
		var msg = 'Confirm and send to Process Transfer In?';
		if(action == 'in')	msg = "Complete this Work Order?";
		if (confirm(msg)){
			this.submit_form({'a':'confirm'});
		}
	},
	// function when user click reset form
	form_reset_clicked: function(to_action){
		if(to_action != 'out' && to_action != 'in'){
			alert('Invalid Reset Option.');
			return;
		}
		//document.f_do_reset['reason'].value = '';
		document.f_do_reset['to_action'].value = '';
		
		//var p = prompt('Enter reason to Reset :');
		//if (p==null || p.trim()=='' ) return false;
		
		//document.f_do_reset['reason'].value = p;
		document.f_do_reset['to_action'].value = to_action;
		
		if(!confirm('Are you sure to reset?'))  return false;

		needCheckExit = false;
		document.f_do_reset.submit();
		return false;
	},
	// function to initialise transfer in row
	init_in_item: function(item_id){
		// get remaing weight for this sku
		var expect_shrinkage_weight = float(this.f['expect_shrinkage_weight'].value);
		
		// get cost per kg
		var cost_per_kg = float(this.f['out_actual_cost_per_kg'].value);
		
		// get this sku unit weight
		var weight_kg = float(this.f['items_list[in]['+item_id+'][weight_kg]'].value);
		
		// calculate expect qty
		var expect_qty = int(Math.floor(expect_shrinkage_weight / weight_kg));
		this.f['items_list[in]['+item_id+'][expect_qty]'].value = expect_qty;
		
		this.in_expected_qty_changed(item_id);
	},
	// function when user change expect in qty
	in_expected_qty_changed: function(item_id){
		// check allow decimal point or not
		var doc_allow_decimal = int(this.f['items_list[in]['+item_id+'][doc_allow_decimal]'].value);
		if(!doc_allow_decimal){
			// not allow decimal point, convert to integer
			this.f['items_list[in]['+item_id+'][expect_qty]'].value = int(this.f['items_list[in]['+item_id+'][expect_qty]'].value);
		}
		
		// get qty
		var expect_qty = float(this.f['items_list[in]['+item_id+'][expect_qty]'].value);
		var weight_kg = float(this.f['items_list[in]['+item_id+'][weight_kg]'].value);
				
		// qty cannot negative
		if(expect_qty < 0){
			this.f['items_list[in]['+item_id+'][expect_qty]'].value = 0;
		}
		
		// cannot less than actual qty
		//var actual_qty = float(this.f['items_list[in]['+item_id+'][actual_qty]'].value);
		//if(expect_qty < actual_qty){
		//	this.f['items_list[in]['+item_id+'][actual_qty]'].value = expect_qty;
		//}
		
		// trigger row changed
		this.in_item_row_changed(item_id);
	},
	// function when transfer in row changed
	in_item_row_changed: function(item_id, recalc_total){
		if(recalc_total == undefined)	recalc_total = true;
				
		///////// EXPECTED TRANSFER IN ////////////
		var expect_qty = float(this.f['items_list[in]['+item_id+'][expect_qty]'].value);
		var weight_kg = float(this.f['items_list[in]['+item_id+'][weight_kg]'].value);
		
		// calculate total
		var line_total_expect_weight = float(round(expect_qty * weight_kg, global_weight_decimal_points));
				
		// update & show total weight
		this.f['items_list[in]['+item_id+'][line_total_expect_weight]'].value = line_total_expect_weight;
		$('span_line_total_expect_weight-in-'+item_id).update(line_total_expect_weight);

		///////// ACTUAL TRANSFER IN ////////////
		var actual_qty = float(this.f['items_list[in]['+item_id+'][actual_qty]'].value);
		var line_total_actual_weight = float(round(actual_qty * weight_kg, global_weight_decimal_points));
		
		// total weight
		this.f['items_list[in]['+item_id+'][line_total_actual_weight]'].value = line_total_actual_weight;
		$('span_line_total_actual_weight-in-'+item_id).update(line_total_actual_weight);
		
		if(recalc_total){
			this.calc_all_items();
		}
	},
	// function to calculate all transfer in
	calc_in_total: function(){
		var transfer_type = this.f['transfer_type'].value;
		var out_total_cost = float(round(this.f['out_total_cost'].value, global_cost_decimal_points));
		var out_actual_received_weight = float(this.f['out_actual_received_weight'].value);
		
		// Expect
		var in_total_expect_qty = 0;
		var in_total_expect_cost = 0;
		var in_total_expect_weight = 0;
		var expect_shrinkage_weight = out_actual_received_weight;
		
		// Actual
		var in_total_actual_qty = 0;
		var in_total_actual_cost = 0;
		var in_total_actual_weight = 0;
		var shrinkage_weight = out_actual_received_weight;
		
		var min_weight_kg = 0;
		
		// calculate shrinkage weight and unit cost
		
		// get all out item row
		var tr_item_list = $$('#tbody_items-in tr.tr_item');
		// loop each row
		for(var i=0,len=tr_item_list.length; i<len; i++){
			// get item id
			var item_id = this.get_item_id_by_ele(tr_item_list[i]);
			var weight_kg = float(this.f['items_list[in]['+item_id+'][weight_kg]'].value, global_weight_decimal_points);
			
			if(min_weight_kg == 0 || weight_kg < min_weight_kg){
				min_weight_kg = weight_kg;
			}
			
			// EXPECTED
			var expect_qty = float(this.f['items_list[in]['+item_id+'][expect_qty]'].value);
			//var line_total_expect_cost = float(this.f['items_list[in]['+item_id+'][line_total_expect_cost]'].value);
			var line_total_expect_weight = float(this.f['items_list[in]['+item_id+'][line_total_expect_weight]'].value);
			
			in_total_expect_qty += expect_qty;
			//in_total_expect_cost += line_total_expect_cost;
			in_total_expect_weight += line_total_expect_weight;
			
			// Actual
			var actual_qty = float(this.f['items_list[in]['+item_id+'][actual_qty]'].value);
			//var line_total_actual_cost = float(this.f['items_list[in]['+item_id+'][line_total_actual_cost]'].value);
			var line_total_actual_weight = float(this.f['items_list[in]['+item_id+'][line_total_actual_weight]'].value);
			
			in_total_actual_qty += actual_qty;
			//in_total_actual_cost += line_total_actual_cost;
			in_total_actual_weight += line_total_actual_weight;
		}
		
		// Expected Cost per KG
		var expect_cost_per_kg = float(round(out_total_cost / in_total_expect_weight, global_cost_decimal_points));
		var actual_cost_per_kg = float(round(out_total_cost / in_total_actual_weight, global_cost_decimal_points));
		
		this.f['expect_cost_per_kg'].value = expect_cost_per_kg;
		this.f['actual_cost_per_kg'].value = actual_cost_per_kg;
		
		// loop each row and apply unit cost
		for(var i=0,len=tr_item_list.length; i<len; i++){
			// get item id
			var item_id = this.get_item_id_by_ele(tr_item_list[i]);
			var weight_kg = float(this.f['items_list[in]['+item_id+'][weight_kg]'].value);
						
			// EXPECTED
			var expect_qty = float(this.f['items_list[in]['+item_id+'][expect_qty]'].value);
			var expect_cost = float(round(weight_kg * expect_cost_per_kg, global_cost_decimal_points));
			var line_total_expect_cost = float(round(expect_qty * expect_cost, global_cost_decimal_points));
			
			$('span_expect_cost-in-'+item_id).update(round(expect_cost, global_cost_decimal_points));
			this.f['items_list[in]['+item_id+'][expect_cost]'].value = expect_cost;
			
			$('span_line_total_expect_cost-in-'+item_id).update(round(line_total_expect_cost, global_cost_decimal_points));
			this.f['items_list[in]['+item_id+'][line_total_expect_cost]'].value = line_total_expect_cost;
						
			in_total_expect_cost += line_total_expect_cost;
			
			// Actual
			var actual_qty = float(this.f['items_list[in]['+item_id+'][actual_qty]'].value);
			var actual_cost = float(round(weight_kg * actual_cost_per_kg, global_cost_decimal_points));
			var line_total_actual_cost = float(round(actual_qty * actual_cost, global_cost_decimal_points));
			
			$('span_actual_cost-in-'+item_id).update(round(actual_cost, global_cost_decimal_points));
			this.f['items_list[in]['+item_id+'][actual_cost]'].value = actual_cost;
			
			$('span_line_total_actual_cost-in-'+item_id).update(round(line_total_actual_cost, global_cost_decimal_points));
			this.f['items_list[in]['+item_id+'][line_total_actual_cost]'].value = line_total_actual_cost;
			
			
			in_total_actual_cost += line_total_actual_cost;
			
		}
		
		if(transfer_type == 'w2p'){
			// Weight to Pcs direct use total cost from all transfer out item
			in_total_actual_cost = out_total_cost
		}
		
		//////////
		// Expected Total Weight
		$('span_in_total_expect_weight').update(in_total_expect_weight);
		this.f['in_total_expect_weight'].value = in_total_expect_weight;
		
		expect_shrinkage_weight -= in_total_expect_weight;
		shrinkage_weight -= in_total_actual_weight;
		
		expect_shrinkage_weight = round(expect_shrinkage_weight, global_weight_decimal_points);
		shrinkage_weight = round(shrinkage_weight, global_weight_decimal_points);
		//if(expect_shrinkage_weight<0)	expect_shrinkage_weight = 0;
		
		// Expected Qty
		this.f['in_total_expect_qty'].value = in_total_expect_qty;
		
		// Actual Qty
		this.f['in_total_actual_qty'].value = in_total_actual_qty;
		$('span_in_total_actual_qty').update(in_total_actual_qty);
		
		// Expected Shrinkage
		$('span_expect_shrinkage_weight').update(expect_shrinkage_weight);
		this.f['expect_shrinkage_weight'].value = expect_shrinkage_weight;
		
		// Actual Total Weight
		$('span_in_total_actual_weight').update(in_total_actual_weight);
		this.f['in_total_actual_weight'].value = in_total_actual_weight;
		
		// Actual Shrinkage
		$('span_shrinkage_weight').update(shrinkage_weight);
		this.f['shrinkage_weight'].value = shrinkage_weight;
		
		// Total Expected Cost
		this.f['in_total_expect_cost'].value = in_total_expect_cost;
		
		// Total Actual Cost
		$('span_in_total_actual_cost').update(round(in_total_actual_cost, global_cost_decimal_points));
		this.f['in_total_actual_cost'].value = in_total_actual_cost;
		
		// labour cost
		var labour_cost = float(this.f['labour_cost'].value);
		
		// packing cost
		var packaging_cost = float(this.f['packaging_cost'].value);
		
		// Total Cost
		var total_cost = float(round(in_total_actual_cost + labour_cost + packaging_cost, global_cost_decimal_points));
		
		// Final Cost per KG
		var final_cost_per_kg = float(round(total_cost / in_total_actual_weight, global_cost_decimal_points));
		
		$('span_total_cost').update(round(total_cost, global_cost_decimal_points));
		this.f['total_cost'].value = total_cost;
		
		$('span_final_cost_per_kg').update(round(final_cost_per_kg, global_cost_decimal_points));
		this.f['final_cost_per_kg'].value = final_cost_per_kg;
		
		// Final Cost Per Qty
		var final_cost_per_qty = float(round(total_cost / in_total_actual_qty, global_cost_decimal_points));
		$('span_final_cost_per_qty').update(round(final_cost_per_qty, global_cost_decimal_points));
		this.f['final_cost_per_qty'].value = final_cost_per_qty;
		
		// loop each row
		for(var i=0,len=tr_item_list.length; i<len; i++){
			// get item id
			var item_id = this.get_item_id_by_ele(tr_item_list[i]);
			var weight_kg = float(this.f['items_list[in]['+item_id+'][weight_kg]'].value);
			var actual_qty = float(this.f['items_list[in]['+item_id+'][actual_qty]'].value);
			
			var finish_cost = 0;
			var line_total_finish_cost = 0;
			if(transfer_type == 'w2w'){
				// Weight to Weight
				finish_cost = float(round(weight_kg * final_cost_per_kg, global_cost_decimal_points));
				line_total_finish_cost = float(round(finish_cost * actual_qty, global_cost_decimal_points));
			}else if(transfer_type == 'w2p'){
				// Weight to Pcs
				var uom_fraction = this.get_in_uom_fraction(item_id);
				finish_cost = float(round(final_cost_per_qty*uom_fraction, global_cost_decimal_points));
				line_total_finish_cost = float(round(finish_cost * actual_qty / uom_fraction, global_cost_decimal_points));
			}
			
			
			// Finish Cost
			this.f['items_list[in]['+item_id+'][finish_cost]'].value = finish_cost;
			$('span_finish_cost-in-'+item_id).update(round(finish_cost, global_cost_decimal_points));
			
			// Finish Total Cost
			this.f['items_list[in]['+item_id+'][line_total_finish_cost]'].value = line_total_finish_cost;
			$('span_line_total_finish_cost-in-'+item_id).update(round(line_total_finish_cost, global_cost_decimal_points));
			
			var gp = 0;
			var gp_per = 0;
			
			if(actual_qty > 0){
				var price = float(this.f['items_list[in]['+item_id+'][price]'].value);
				gp = float(round(price - finish_cost, global_cost_decimal_points));
				gp_per = float(round(gp / price * 100, 2));
			}
			
			var span_gp = $('span_line_total_finish_gp-in-'+item_id);
			var span_gp_per = $('span_line_total_finish_gp_per-in-'+item_id);
			
			span_gp.update(round(gp, global_cost_decimal_points));
			span_gp_per.update(round(gp_per, 2));
			
			if(gp <= 0){
				if(!span_gp.hasClassName('negative_value')){
					span_gp.addClassName('negative_value');
				}
			}else{
				span_gp.removeClassName('negative_value');
			}
			
			if(gp_per <= 0){
				if(!span_gp_per.hasClassName('negative_value')){
					span_gp_per.addClassName('negative_value');
				}
			}else{
				span_gp_per.removeClassName('negative_value');
			}
		}
		
		// Check if got extra expected shrinkage can fill back into sku
		/*if(this.check_need_auto_fill_expect_in_qty && min_weight_kg> 0 && expect_shrinkage_weight >= 0 && expect_shrinkage_weight >= min_weight_kg){
			var need_recalc_expect_qty = confirm("There are some shrinkage under Expected Transfer In, do you want system to auto fill up?");
			if(need_recalc_expect_qty != undefined && need_recalc_expect_qty){
				this.auto_fill_expect_in_qty();
			}
			return;
		}*/
		
		//////////// Actual Transfer In //////////
		
		// prompt error if expected shrinkage is negative
		if(expect_shrinkage_weight < 0){
			alert('Expected Shrinkage is negative, please check your Expected Transfer In Qty');
			return;
		}
		
		// prompt error if actual shrinkage is negative
		if(shrinkage_weight < 0){
			alert('Actual Shrinkage is negative, please check your Actual Transfer In Qty');
			return;
		}
	},
	// function to automatically fill in expected qty
	auto_fill_expect_in_qty: function(){
		// get remaining shrinkage
		var expect_shrinkage_weight = float(this.f['expect_shrinkage_weight'].value);
		var need_recalc = false;
		
		// get all out item row
		var tr_item_list = $$('#tbody_items-in tr.tr_item');
		
		// loop each row
		for(var i=0,len=tr_item_list.length; i<len; i++){
			// get item id
			var item_id = this.get_item_id_by_ele(tr_item_list[i]);
			var weight_kg = float(this.f['items_list[in]['+item_id+'][weight_kg]'].value);
			var expect_qty = float(this.f['items_list[in]['+item_id+'][expect_qty]'].value);
			var extra_qty = 0;
			
			// calculate extra qty
			if(weight_kg <= expect_shrinkage_weight){
				extra_qty = int(Math.floor(expect_shrinkage_weight / weight_kg));
			}
			
			// got extra qty
			if(extra_qty > 0){
				need_recalc = true;
				this.f['items_list[in]['+item_id+'][expect_qty]'].value = expect_qty + extra_qty;
				
				expect_shrinkage_weight -= (extra_qty * weight_kg);
				
				this.check_need_auto_fill_expect_in_qty = false;
				this.in_expected_qty_changed(item_id);
			}
		}
		
		if(need_recalc){
			this.check_need_auto_fill_expect_in_qty = true;
			this.recalc_total();
		}
	},
	// function when actual transfer in changed
	in_actual_qty_changed: function(item_id){
		// check allow decimal point or not
		var doc_allow_decimal = int(this.f['items_list[in]['+item_id+'][doc_allow_decimal]'].value);
		if(!doc_allow_decimal){
			// not allow decimal point, convert to integer
			this.f['items_list[in]['+item_id+'][actual_qty]'].value = int(this.f['items_list[in]['+item_id+'][actual_qty]'].value);
		}
		
		var actual_qty = float(this.f['items_list[in]['+item_id+'][actual_qty]'].value);
		if(actual_qty < 0){
			this.f['items_list[in]['+item_id+'][actual_qty]'].value = 0;
		}		
		
		// cannot received more than expected transfer in
		var expect_qty = float(this.f['items_list[in]['+item_id+'][expect_qty]'].value);
		if(actual_qty > expect_qty){
			this.f['items_list[in]['+item_id+'][actual_qty]'].value = actual_received_weigth = expect_qty;
		}
		
		// trigger row changed
		this.in_item_row_changed(item_id);
	},
	// function when user change labour cost
	labour_cost_changed: function(){
		// labour cost
		var labour_cost = float(round(this.f['labour_cost'].value, global_cost_decimal_points));
		if(labour_cost < 0){
			labour_cost = 0;
		}
		this.f['labour_cost'].value = float(round(labour_cost, global_cost_decimal_points));
		
		// calculate total in
		this.calc_in_total();
	},
	// function when user change packing cost
	packing_cost_changed: function(){
		// packing cost
		var packaging_cost = float(round(this.f['packaging_cost'].value, global_cost_decimal_points));
		if(packaging_cost < 0){
			packaging_cost = 0;
		}
		this.f['packaging_cost'].value = float(round(packaging_cost, global_cost_decimal_points));
		
		// calculate total in
		this.calc_in_total();
	},
	// function to fill all shrinkage to transfer in items
	fill_expected_shrinkage_clicked: function(){
		var expect_shrinkage_weight = float(this.f['expect_shrinkage_weight'].value);
		if(expect_shrinkage_weight <= 0){
			alert('There is no shrinkage, auto fill is not available.');
			return;
		}
		
		var need_recalc_expect_qty = confirm("There are "+expect_shrinkage_weight+" KG shrinkage under Expected Transfer In, do you want system to auto fill up?");
		if(need_recalc_expect_qty != undefined && need_recalc_expect_qty){
			this.auto_fill_expect_in_qty();
		}
	},
	// function when users changed transfer type
	transfer_type_changed: function(){
		this.update_transfer_type_field();
	},
	// core function to show / hide field related to transfer type
	update_transfer_type_field: function(){
		var transfer_type = this.f['transfer_type'].value;
		
		if(transfer_type != 'w2w'){
			// Not Weight to Weight - Hide all w2w col
			$$('#div_sheets .col_w2w').invoke('hide');
		}else if(transfer_type != 'w2p'){
			// Not Weight to Pcs - Hide all w22 col
			$$('#div_sheets .col_w2p').invoke('hide');
		}
		
		if(transfer_type == 'w2w'){
			// Is Weight to Weight - Show all w2w col
			$$('#div_sheets .col_w2w').invoke('show');
		}else if(transfer_type == 'w2p'){
			// Is Weight to Pcs - Show all w2p col
			$$('#div_sheets .col_w2p').invoke('show');
		}
	},
	// core function to get actual in uom fraction
	get_in_uom_fraction: function(item_id){
		//console.log("item_id = "+item_id);
		var selectedIndex = this.f['items_list[in]['+item_id+'][uom_id]'].selectedIndex;
		var opt = this.f['items_list[in]['+item_id+'][uom_id]'].options[selectedIndex];
		
		var fraction = $(opt).readAttribute('fraction');
		return float(fraction);
	},
	// function when users changed in uom
	in_uom_id_changed: function(item_id){
		// Get UOM Fraction
		//var uom_fraction = this.get_in_uom_fraction(item_id);
				
		// calculate line qty
		this.calculate_in_actual_qty(item_id);
	},
	// function when user changed transfer in pcs
	in_actual_adj_qty_changed: function(item_id){
		// not allow decimal point, convert to integer
		this.f['items_list[in]['+item_id+'][actual_adj_qty]'].value = int(this.f['items_list[in]['+item_id+'][actual_adj_qty]'].value);
		
		// calculate line qty
		this.calculate_in_actual_qty(item_id);
	},
	calculate_in_actual_qty: function(item_id){
		// Get UOM Fraction
		var uom_fraction = this.get_in_uom_fraction(item_id);
		var actual_adj_qty = int(this.f['items_list[in]['+item_id+'][actual_adj_qty]'].value);
		
		// Update Actual Qty
		var actual_qty = uom_fraction * actual_adj_qty;
		this.f['items_list[in]['+item_id+'][actual_qty]'].value = actual_qty;
		
		// trigger row changed
		this.in_item_row_changed(item_id);
	}
}

function add_grn_barcode_item(value){
	WO_OPEN.add_grn_barcode_item(value);
}

function do_ajax_add(query_string){
	WO_OPEN.add_item(query_string);
}
{/literal}
</script>

<h1>
	{$PAGE_TITLE}
	{if is_new_id($form.id)}
		(NEW)
	{else}
		ID#{$form.id}
		{if $form.adj_id}
			, Adjustment ID#{$form.adj_id}
		{/if}
	{/if}
</h1>

<h3>
	Status:
	{if !$form.active}
		Cancelled
	{elseif $form.status eq 0}
		Process Transfer Out
	{elseif $form.status eq 1 and $form.completed eq 0}
		Process Transfer In
	{elseif $form.status eq 1 and $form.completed eq 1}
		Completed
	{/if}
</h3>

{if $form.adj_id}
<div class="stdframe" style="background-color:#F0FFF0;">
    <h4>Adjustment Docs</h4>
    <a href="adjustment.php?a=view&branch_id={$form.branch_id}&id={$form.adj_id}" target="_blank">ID#{$form.adj_id}</a>
</div>
<br />
{/if}

<div id="div_wait_popup" style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
	<p align="center">
		Please wait..
		<br /><br />
		<img src="ui/clock.gif" border="0" />
	</p>
</div>

<form name="f_do_reset" method="post" style="display:none;">
	<input type="hidden" name="a" value="do_reset" />
	<input type="hidden" name="branch_id" value="{$form.branch_id}" />
	<input type="hidden" name="id" value="{$form.id}" />
	{*<input type="hidden" name="reason" value="" />*}
	<input type="hidden" name="to_action" value="" />
</form>

<form name="f_a" method="post" onSubmit="return false;">
	<input type="hidden" name="a" />
	<input type="hidden" name="branch_id" value="{$form.branch_id|default:$sessioninfo.branch_id}" />
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="branch_is_under_gst" value="{$form.branch_is_under_gst}" />
	<input type="hidden" name="active" value="{$form.active}"/>
	<input type="hidden" name="status" value="{$form.status}"/>
	<input type="hidden" id="inp_adj_id" name="adj_id" value="{$form.adj_id}"/>
	<input type="hidden" name="edit_time" value="{$form.edit_time}"/>
	<input type="hidden" name="action" value="{$action}"/>
	<input type="hidden" name="deleted_reason" />
	
	<div class="stdframe">
		<h4>General Information</h4>
		<table width="100%">
			{* Branch *}
			<tr>
				<td width="120"><b>Branch</b></td>
				<td>
					{$form.branch_code}
				</td>
			</tr>
			
			{* Adjustment Date *}
			<tr>
				<td><b>Adjustment Date</b></td>
				<td>
					<input name="adj_date" id="inp_adj_date" size="10" maxlength="10"  value="{$form.adj_date|date_format:"%Y-%m-%d"}" class="required" title="Adjustment Date" />
					{if $can_edit}
						<img align="absmiddle" src="ui/calendar.gif" id="img_adj_date" style="cursor: pointer;" title="Select Date" />
					{/if}
					<span><img src="ui/rq.gif" align="absbottom" title="Required Field" /></span>
				</td>
			</tr>
			
			{* Department *}
			<tr>
				<td><b>Department</b></td>
				<td>
					<select class="form-control select2" name="dept_id">
						<option value="">-- Please Select --</option>
						{foreach from=$dept_list key=dept_id item=dept}
							<option value="{$dept_id}" {if $form.dept_id eq $dept_id}selected {/if}>{$dept.description}</option>
						{/foreach}
					</select>
					<span><img src="ui/rq.gif" align="absbottom" title="Required Field" /></span>
				</td>
			</tr>
			
			{* Transfer Type *}
			<tr>
				<td><b>Transfer Type</b> [<a href="javascript:void(alert('Transfer Type cannot be changed after confirmed (sent to Transfer In)'))">?</a>]</td>
				<td>
					{if !$form.status}
						<select name="transfer_type" onChange="WO_OPEN.transfer_type_changed();">
							{foreach from=$transfer_type_list key=k item=v}
								<option value="{$k}" {if $form.transfer_type eq $k}selected {/if}>{$v}</option>
							{/foreach}
						</select>
						<span><img src="ui/rq.gif" align="absbottom" title="Required Field" /></span>
					{else}
						<input type="hidden" name="transfer_type" value="{$form.transfer_type}" />
						{$transfer_type_list[$form.transfer_type]}
					{/if}
				</td>
			</tr>
			
			{* Owner *}
			{if !is_new_id($form.id)}
				<tr>
					<td align="left"><b>Owner</b></td>
					<td style="color:blue;"><input type="hidden" name="owner_username" value="{$form.owner_username}" />{$form.owner_username}</td>
				</tr>
			{/if}
			
			{* Remark *}
			<tr>
				<td valign="top"><b>Remarks</b></td>
				<td>
					<textarea rows="2" cols="68" name="remark" onchange="uc(this);">{$form.remark}</textarea>
				</td>
			</tr>
			
			{* Notify Users *}
			<tr>
				<td valign="top"><b>Notify Users</b></td>
				<td>
					When confirm and send the document to (Transfer In), will notify below users using pm
					<div class="stdframe" style="height:80px;width:200px;overflow-y:auto;background-color:#fff;">
						{foreach from=$form.notify_users_in_obj key=uid item=r}
							{if !$transferInUserList.$uid}
								<input type="checkbox" name="notify_users_in[{$uid}]" value="{$uid}" checked {if $action ne 'out'}onChange="this.checked=!this.checked;"{/if} /> {$r.u}<br />
							{/if}
						{/foreach}
						
						{foreach from=$transferInUserList key=uid item=r}
							<input type="checkbox" name="notify_users_in[{$uid}]" value="{$uid}" {if $form.notify_users_in.$uid}checked {/if} {if $action ne 'out'}onfocus="blur();"{/if} /> {$r.u}<br />
						{/foreach}
					</div>
					When complete, will send to Notify Users in Adjustment Approval Flow
				</td>
			</tr>
		</table>
	</div>
	
	<br />
	<div id="div_sheets">
		{include file='work_order.open.out.tpl'}
		{include file='work_order.open.in.tpl'}
		
		{if $can_edit}
			<br />
			<div style="background:#ddd;border:1px solid #999;" id="div_add_item">
				{include file='scan_barcode_autocomplete.tpl' need_hr_out_bottom=1}
				{assign var=need_weight_kg value=1}
				{if $action eq 'in'}
					{if $form.transfer_type eq 'w2p'}
						{assign var=need_weight_kg value=0}
					{/if}
				{/if}
				
				{include file=sku_items_autocomplete_multiple_add.tpl allow_edit=1 default_mcode=1 need_weight_kg=$need_weight_kg}
				<span id="span_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
				<ul style="list-style:none;">
					<li> <img src="/ui/icons/help.png" align="top" /> Only SKU with Weight more than 0 can be found.</li>
				</ul>
			</div>
			<script>reset_sku_autocomplete();</script>
		{/if}
	</div>
</form>

<p id="p_submit_btn" align="center">
	{if $can_edit}
		<input type="button" value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="WO_OPEN.submit_form();" />
		
		{if !is_new_id($form.id) and $action eq 'out'}
			<input type="button" class="btn btn-danger" value="Delete"  onclick="WO_OPEN.form_delete_clicked()" />
		{/if}
		<input type="button" class="btn btn-danger" value="Close"  onclick="WO_OPEN.form_close_clicked();" />
		
		{if $action eq 'out'}
			<input type="button" class="btn btn-success" value="Confirm (Send to Transfer In)"  onclick="WO_OPEN.form_confirm_clicked();" />
		{else}
			{if ($sessioninfo.level>=$config.doc_reset_level)}
			<input type="button" class="btn btn-danger" value="Reset to Transfer Out"  onclick="WO_OPEN.form_reset_clicked('out');" />
			{/if}
			<input type="button" class="btn btn-success" value="Complete" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="WO_OPEN.form_confirm_clicked();" />
		{/if}
	{else}
		{if $form.branch_id eq $sessioninfo.branch_id and $form.active eq 1 and $form.status eq 1 and ($sessioninfo.level>=$config.doc_reset_level)}
			<input type="button" class="btn btn-danger" value="Reset to Transfer Out"  onclick="WO_OPEN.form_reset_clicked('out');" />
			{if $form.completed eq 1}
				<input type="button" class="btn btn-danger" value="Reset to Transfer In"  onclick="WO_OPEN.form_reset_clicked('in');" />
			{/if}
		{/if}
		<input type="button" value="Close" class="btn btn-info" ;" onclick="WO_OPEN.form_close_clicked();">
	{/if}
</p>

<script>
{literal}
WO_OPEN.initialize();
{/literal}
</script>
{include file='footer.tpl'}