{*
8/17/2012 4:08 PM Andy
- Change allowed branches list to edit in popup

9/13/2012 5:29 PM Andy
- Add new purchase agreement type, Seasonal.
- Add purchase agreement to support multiple add item.

9/24/2012 11:08 AM Andy
- Change "Normal" purchasee agreement to editable in approved status, instead of seasonal.

9/25/2012 10:44 AM Andy
- Change to allow normal user to edit "Normal PA" in approved status.

10/9/2012 5:09 PM Andy
- Add purchase agreement active,status,approved in form hidden input, to fix after form submit and return the status is gone.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

5/28/2015 5:28 PM Andy
- Fix Add FOC item cannot select rule.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

9/8/2016 4:23 PM Andy
- Enhanced to have remark for purchase agreement.

2/27/2017 10:16 AM Zhi Kai 
- Change wording of 'General Informations' to 'General Information'.

4/19/2017 9:37 AM Khausalya
- Enhanced changes from RM to use config setting. 

9/26/2018 4:35 PM Andy
- Enhanced to have "Upload CSV" for Purchase Agreement.

06/24/2020 03:13 PM Sheila
- Updated button css
*}

{if !$form.approval_screen}{include file='header.tpl'}{/if}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
{literal}
input[disabled] {
  color:black;
  background:rgb(255,238,153);
}
input[readonly] {
  color:black;
  background:rgb(255,238,153);
}
select[disabled] {
  color:black;
  background:rgb(255,238,153);
}
span.span_latest_cost{
	color:blue;
	font-size: 80%;
}

ul.ul_allowed_branches, ul.ul_allowed_branches li{
	list-style-type:none;
	margin:0;
	padding:0;
}

div.div_allowed_branches_popup{
	position:absolute;
	background:#cfcfcf;
	white-space:nowrap;
	margin-left:-4px;
	margin-top:4px;
	padding:5px;
	border:2px outset grey;
}
{/literal}
</style>

<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';
var allow_edit = '{$allow_edit}';
var form_label = '{$form.label}';
var is_approval_screen = '{$form.approval_screen}';
var global_cost_decimal_points = int('{$config.global_cost_decimal_points}');
var currency_symbol = '{$config.arms_currency.symbol}';
{literal}

PURCHASE_AGREEMENT_MODULE = {
	f: undefined,
	bid: 0,
	pa_id: 0,
	initialize: function(){
		this.f = document.f_a;
		this.bid = this.f['branch_id'].value;
		this.pa_id = this.f['id'].value;
		
		var THIS = this;
		
		if(allow_edit==1 && !is_approval_screen){
			// autocomplete for vendor
			new Ajax.Autocompleter("inp_autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor&block=po", {
				paramName: 'vendor',
				afterUpdateElement: function (obj, li){
					var  s = li.title.split(",");
				    if(s[0]==0){
				        $('inp_autocomplete_vendor').value = '';
				        return;
					}
					THIS.f['vendor_id'].value = li.title;
				}
			});
			
			// event when user click to continue
			$('btn_refresh').observe('click', function(){
				THIS.refresh_page();
			});
			
			// initial calendar
			Calendar.setup({
				    inputField     :    "inp_date_from",     // id of the input field
				    ifFormat       :    "%Y-%m-%d",      // format of the input field
				    button         :    "img_date_from",  // trigger for the calendar (button ID)
				    align          :    "Bl",           // alignment (defaults to "Bl")
				    singleClick    :    true
				});
	
				Calendar.setup({
				    inputField     :    "inp_date_to",     // id of the input field
				    ifFormat       :    "%Y-%m-%d",      // format of the input field
				    button         :    "img_date_to",  // trigger for the calendar (button ID)
				    align          :    "Bl",           // alignment (defaults to "Bl")
				    singleClick    :    true
			});
				
			// initial sku autocomplete
			SKU_AUTOCOMPLETE_POPUP.initialize();
			
			// initiali select rule popup
			SELECT_RULE_NUM_POPUP.initialize();
		}else{
			Form.disable(this.f);
		}
		
		
		this.recalculate_all_gp();
	},
	// function when user "click to continue"
	refresh_page: function(){
		// validate header
		if(!this.check_header())    return false;
		
		if (check_login()) {
          this.f['a'].value = 'refresh';
		  this.f.submit();
        }
	},
	// function to check form header
	check_header: function(){
		if(!this.f)	return false;
		
		if(!check_required_field(this.f))	return false;

		if(!this.f['vendor_id'].value){
			alert('Please search and select vendor');
			return false;
		}
		
		return true;
	},
	// function to check all items before submit
	check_form: function(){
		var tr_item_row_list = this.get_all_rows('item');
		
		for(var i=0; i<tr_item_row_list.length; i++){
			var tr = tr_item_row_list[i];
			
			var pai_id = tr.id.split("-")[1];
			
			// check qty type
			var qty_type = $('sel_qty_type-'+pai_id).value;
			if(qty_type=='range'){
				if(float($('inp-qty1-item-'+pai_id).value)>=float($('inp-qty2-item-'+pai_id).value)){
					alert('Invalid Qty From/To.');
					$('inp-qty1-item-'+pai_id).focus();
					return false;
				}
			}
		}
		
		return true;
	},
	// function when user add new item
	add_new_item: function(sid_list){
		if(!sid_list)	return false;
		var THIS = this;
		
		// show loading
		$('span_adding_item_loading').show();
		
		var params = {
			a: 'ajax_add_item',
			'sid_list[]': sid_list,
			branch_id: this.bid,
			pa_id: this.pa_id
		};
		
		ajax_request(phpself, {
			method: 'post',
			parameters: params,
			onComplete: function(msg){
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$('span_adding_item_loading').hide();

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						new Insertion.Bottom('tbody_pa_item_list', ret['html']);
						THIS.reset_row_num('item');
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    if(!err_msg)	err_msg = 'Error: No respond from server.';
			    alert(err_msg);
			}
		});
	},
	// function reset row number
	reset_row_num: function(item_type){
		if(item_type=='foc_item'){
			$$('#tbody_pa_foc_item_list span.span_no').each(function(ele, i){
				$(ele).update(i+1);
			});
		}else{
			$$('#tbody_pa_item_list span.span_no').each(function(ele, i){
				$(ele).update(i+1);
			});
		}
	},
	// function to toggle allowed branches
	toggle_allowed_branches: function(item_type, pai_id){
		// get main checkbox got checked or not
		var c = $('inp_toggle_all_branch-'+item_type+'-'+pai_id).checked;
		
		// update branches list input
		$$('#pa_content_list input.inp_allowed_branches-'+item_type+'-'+pai_id).each(function(ele){
			ele.checked = c;
		});
		
		this.update_allowed_branches_label(item_type, pai_id);
	},
	// function when user tick/untick allowed branches
	update_allowed_branches_label: function(item_type, pai_id){
		var inp_allowed_branches_list = $$('#tr_'+item_type+'_row-'+pai_id+' input.inp_allowed_branches-'+item_type+'-'+pai_id);
		var div = $('div_allowed_branches_details-'+item_type+'-'+pai_id);
		var html = '';
		
		for(var i=0; i<inp_allowed_branches_list.length; i++){
			if(inp_allowed_branches_list[i].checked){
				if(html)	html += ', ';
				html += inp_allowed_branches_list[i].title;
			}
		}
		
		if(html==''){
			div.update('-- NONE --');
		}else{
			div.update(html);
		}
	},
	// function when user change qty type
	qty_type_changed: function(pai_id){
		if(!pai_id)	return false;
		
		var qty_type = $('sel_qty_type-'+pai_id).value;
		var show_qty2 = false;
		if(qty_type=='range')	show_qty2 = true;
		
		var span = $('span_qty2-'+pai_id);
		var inp_qty2 = this.f['qty2[item]['+pai_id+']'];
		
		if(show_qty2){
			span.show();
			inp_qty2.addClassName('required');
		}else{
			span.hide();
			inp_qty2.removeClassName('required');
		}
	},
	// function when user click save or confirm
	submit_form: function(act, skip_checking, need_ask_reason){
		if(!act)	return false;
		
		if (check_login()) {
		  this.f['a'].value = act;

		  if(!skip_checking){
			  if(!this.check_header())    return false;

			  if(!this.check_form())	return false;
		  }

		  if(need_ask_reason){
			  var reason = prompt('Please enter reason');
			  if(!reason || reason.trim()=='')	return false;

			  this.f['reason'].value = reason;
		  }
		  // ask last confirmation
		  if(!confirm('Are you sure?'))   return false;

		  // enable the form before submit
		  Form.enable(this.f);

		  this.f.submit();
		}
	},
	// function when user change qty
	qty_changed: function(ele){
		mfz(ele);
		
		var pai_id = ele.id.split("-")[3];
		var type = ele.id.split("-")[1];
		
		var qty_type = $('sel_qty_type-'+pai_id).value;
		
		if(qty_type=='range'){
			var inp_qty1 = $('inp-qty1-item-'+pai_id);
			var inp_qty2 = $('inp-qty2-item-'+pai_id);

			var qty1 = float(inp_qty1.value);
			var qty2 = float(inp_qty2.value);
			
			if(type=='qty2'){	// check if edit qty2
				if(qty2<=qty1){
					alert('Qty To cannot less then or equal to Qty From');
					inp_qty2.value = '';
					return false;
				}
			}else if(type=='qty1'){
				if(qty2>0){
					if(qty2<=qty1){
						alert('Qty From cannot more then or equal to Qty To');
						inp_qty1.value = '';
						return false;
					}
				}
			}
		}		
	},
	foc_qty_changed: function(ele){
		mfz(ele);
	},
	// function when user change selling price or purchase price
	item_price_changed: function(ele){
		var ids = ele.id.split("-");
		var type = ids[0];
		var item_type = ids[1];
		var pai_id = ids[2];
		
		if(type=='purchase_price'){
			mf(ele, global_cost_decimal_points, 1);
		}else{
			mfz(ele);
		}		
		
		if(item_type=='foc_item'){
		
		}else{
			// recalculate gp
			this.recalculate_item_gp(pai_id);
		}	
	},
	// function to recalculate gp
	recalculate_item_gp: function(pai_id){
		if(!pai_id)	return false;
		
		var sp = float($('suggest_selling_price-item-'+pai_id).value);
		var cost = float($('purchase_price-item-'+pai_id).value);
		
		var gp_per = 0;
		if(sp){
			// get discount element
			var inp = $('inp_discount-item-'+pai_id);
					
			// get discount format
			var discount_format = inp.value.trim();
			
			// get discount amt
			discount_amt = float(round(get_discount_amt(cost, discount_format),2));
			if(discount_amt){
				cost -= discount_amt
			}
	
			var gp = sp-cost;
			gp_per = gp/sp*100;
		}
		$('span_gp_per-item-'+pai_id).update(round(gp_per,2));
	},
	// function when user change discount
	item_discount_changed: function(pai_id){
		if(!pai_id)	return false;

		// get input element
		var inp = $('inp_discount-item-'+pai_id);
				
		// get discount format
		var discount_format = inp.value.trim();
	
		// check discount pattern
		discount_format = validate_discount_format(discount_format);
		
		// update back the value
		inp.value = discount_format;
		
		// recalculate row
		this.recalculate_item_gp(pai_id);
	},
	// function to recalculate gp for all row
	recalculate_all_gp: function(){
		// get all items
		var tr_item_row_list = this.get_all_rows('item');;
		
		for(var i=0; i<tr_item_row_list.length; i++){
			var tr = tr_item_row_list[i];
			
			var pai_id = tr.id.split("-")[1];
			
			this.recalculate_item_gp(pai_id);
		}
	},
	// function to delete item
	delete_item: function(pai_id){
		if(!pai_id)	return false;
		
		// check whether this item got use for foc
		var rule_num = $('inp_rule_num-'+pai_id).value;
		
		var ele_use_this_rule = $$('#tbody_pa_foc_item_list input.inp_ref_rule_num-'+rule_num);
		
		if(ele_use_this_rule.length>0){
			alert('There are some item using this item for FOC. Please delete the FOC item first.');
			return false;
		}
		
		if(!confirm('Are you sure?'))	return false;
		
		$('tr_item_row-'+pai_id).remove();
		
		this.reset_row_num();
	},
	// function to delete item
	delete_foc_item: function(pafi_id){
		if(!pafi_id)	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		$('tr_foc_item_row-'+pafi_id).remove();
		
		this.reset_row_num('foc_item');
	},
	// function to return all <tr> element
	get_all_rows: function(item_type){
		if(item_type=='foc_item'){
		
		}else{
			var tr_item_row_list = $$('#tbody_pa_item_list tr.tr_item_row');
			return tr_item_row_list;
		}
	},
	// function to construct and return row related info
	get_row_info_by_pai_id: function(pai_id){
		var ret = {};
		
		// rule num
		ret['rule_num'] = {};
		ret['rule_num']['ele'] = $('inp_rule_num-'+pai_id);
		ret['rule_num']['val'] = ret['rule_num']['ele'].value;
		
		// arms code
		ret['sku_item_code'] = {};
		ret['sku_item_code']['ele'] = $('td_sku_item_code-item-'+pai_id);
		ret['sku_item_code']['val'] = ret['sku_item_code']['ele'].innerHTML;
		
		// mcode
		ret['mcode'] = {};
		ret['mcode']['ele'] = $('td_mcode-item-'+pai_id);
		ret['mcode']['val'] = ret['mcode']['ele'].innerHTML;
		
		// artno
		ret['artno'] = {};
		ret['artno']['ele'] = $('td_artno-item-'+pai_id);
		ret['artno']['val'] = ret['artno']['ele'].innerHTML;
		
		// description
		ret['desc'] = {};
		ret['desc']['ele'] = $('td_desc-item-'+pai_id);
		ret['desc']['val'] = ret['desc']['ele'].innerHTML;
		
		return ret;
	},
	// open the purchase agreement in edit mode
	open_as_edit_mode: function(){
		window.location = '?a=open&id='+this.f['id'].value+'&branch_id='+this.f['branch_id'].value;
	}
}

var SKU_AUTOCOMPLETE_POPUP = {
	f: undefined,
	item_type: 'item',
	initialize: function(){
		this.f = document.f_sku_autocomplete;
		
		new Draggable('div_sku_autocomplete_dialog',{ handle: 'div_sku_autocomplete_dialog_header'});
	},
	// function to close popup
	close: function(only_popup){
		if(only_popup){
			$('div_sku_autocomplete_dialog').hide();
		}else{
			default_curtain_clicked();
		}
	},
	// function to show popup
	open: function(item_type){
		this.item_type = item_type;
				
		if(this.item_type=='foc_item'){	// need checking before show search popup
			$('btn_sku_autocomplete_multiple_add').hide();
			
			// get item row
			var tr_item_row_list = PURCHASE_AGREEMENT_MODULE.get_all_rows('item');
			if(tr_item_row_list.length<=0){
				alert('There is no item to attach for FOC.');
				return false;
			}
		}else{
			$('btn_sku_autocomplete_multiple_add').show();
		}
		
		
		reset_sku_autocomplete();
		curtain(true);
		center_div($('div_sku_autocomplete_dialog').show());
		$('autocomplete_sku').focus();
	},
	// function to add item
	add_item: function(){
		var sid = $('sku_item_id').value;
		if(!sid){
			alert('Please search and select SKU first.');
			$('autocomplete_sku').focus();
			return false;
		}
		
		if(this.item_type == 'foc_item'){
			this.close(1);
			SELECT_RULE_NUM_POPUP.new_foc_item(sid);
		}else{
			this.close();
			PURCHASE_AGREEMENT_MODULE.add_new_item([sid]);
		}
	},
	// function for multiple add
	add_multiple_clicked: function(){
		var sid_list = [];
		
		$A(this.f['sid[]']).each(function(inp){
			if(inp.checked)	sid_list.push(inp.value);
		});
		if(sid_list.length<=0){
			alert('Please tick at least 1 item.');
			return false;
		}
		this.close();
		PURCHASE_AGREEMENT_MODULE.add_new_item(sid_list);
	},
	// function when user close multiple add
	multiple_window_close: function(){
		$('div_multiple_add_popup').hide();
	}
}

var SELECT_RULE_NUM_POPUP = {
	f: undefined,
	sid: 0,
	foc_pai_id: 0,
	initialize: function(){
		this.f = document.f_select_rule;
	},
	close: function(){
		default_curtain_clicked();
	},
	new_foc_item: function(sid){
		this.sid = sid;
		this.open(0, []);
	},
	// function to open popup
	open: function(foc_pai_id, selected_rule_num_arr){
		this.foc_pai_id = foc_pai_id;
		
		this.refresh_rule_page();
		center_div($('div_select_rule_dialog').show());
	},
	// function to refresh rule page
	refresh_rule_page: function(){
		// clear list
		$('tbody_select_rule_list').update('');
		
		// get all item row
		var tr_item_row_list = PURCHASE_AGREEMENT_MODULE.get_all_rows('item');

		for(var i=0; i<tr_item_row_list.length; i++){
			// get tmp row
			var tmp_row = cloneEle($('tmp_tr_select_rule'));
			tmp_row.id = '';
			var new_html = tmp_row.innerHTML;
			
			var pai_id = tr_item_row_list[i].id.split("-")[1];
			var row_info = PURCHASE_AGREEMENT_MODULE.get_row_info_by_pai_id(pai_id);
			
			new_html = new_html.replace(/__RULE_NUM__/g, row_info['rule_num']['val']);
			new_html = new_html.replace(/__ARMS_CODE__/g, row_info['sku_item_code']['val']);
			new_html = new_html.replace(/__MCODE__/g, row_info['mcode']['val']);
			new_html = new_html.replace(/__DESCRIPTION__/g, row_info['desc']['val']);			
			
			$(tmp_row).update(new_html).style.display = '';
			
			new Insertion.Bottom('tbody_select_rule_list', toHTML(tmp_row));
		}
	},
	// function when user click save
	save_foc_item: function(){
		var THIS = this;
		
		// check rule
		var inp_select_rule_list = $$('#div_select_rule_dialog_content input.inp_select_rule');
		var got_rule = false;
		for(var i=0; i<inp_select_rule_list.length; i++){
			if(inp_select_rule_list[i].checked){
				got_rule = true;
				break;
			}
		}
		
		if(!got_rule){
			alert('Please select at least 1 rule.');
			return false;
		}
		
		if(this.foc_pai_id){	// just update rule num
		
		}else{	// add new foc item
			this.f['sid'].value = this.sid;	// assign sku item id
			
			var params = $(this.f).serialize();
			var inp_saving_foc_item = $('inp_saving_foc_item');
			
			inp_saving_foc_item.disabled = true;
			inp_saving_foc_item.value = 'Saving . . .';
			
			ajax_request(phpself, {
				parameters: params,
				method: 'post',
				onComplete: function(msg){
					// insert the html at the div bottom
					var str = msg.responseText.trim();
					var ret = {};
				    var err_msg = '';
					inp_saving_foc_item.disabled = false;
					inp_saving_foc_item.value = 'Save FOC Item';
					
				    try{
		                ret = JSON.parse(str); // try decode json object
		                if(ret['ok'] && ret['html']){ // success
		                	new Insertion.Bottom('tbody_pa_foc_item_list', ret['html']);
		                	PURCHASE_AGREEMENT_MODULE.reset_row_num('foc_item');
		                	THIS.close();
			                return;
						}else{  // save failed
							if(ret['failed_reason'])	err_msg = ret['failed_reason'];
							else    err_msg = str;
						}
					}catch(ex){ // failed to decode json, it is plain text response
						err_msg = str;
					}
	
				    // prompt the error
				    if(!err_msg)	err_msg = 'Error: No respond from server.';
				    alert(err_msg);
				}
			});
		}
	}
}

function add_autocomplete(){
	SKU_AUTOCOMPLETE_POPUP.add_item();
}

function discount_help()
{
	msg = '';
	msg += "Sample input\n";
	msg += "------------\n";
	msg += "10% => discount of 10 percent\n";
	msg += "10  => discount of "+currency_symbol+"10\n";
	msg += "10%+10 => discount 10%, follow by "+currency_symbol+"10\n";
	msg += "10+10% => discount "+currency_symbol+"10, then discount 10%\n";

	alert(msg);
}

function do_save(){
	PURCHASE_AGREEMENT_MODULE.submit_form('save');
}

function do_delete(){
    PURCHASE_AGREEMENT_MODULE.submit_form('delete', 1);
}

function do_confirm(){
    PURCHASE_AGREEMENT_MODULE.submit_form('confirm');
}

function do_reset(){
    PURCHASE_AGREEMENT_MODULE.submit_form('pa_reset', 1, 1);
}

function do_edit(){
	PURCHASE_AGREEMENT_MODULE.open_as_edit_mode();
}

function submit_multi_add(ele){
	SKU_AUTOCOMPLETE_POPUP.add_multiple_clicked();
}

function alternative_multiple_window_close(){
	SKU_AUTOCOMPLETE_POPUP.multiple_window_close();
}
{/literal}
</script>

<!-- SKU AUTOCOMPLETE DIALOG -->
<div id="div_sku_autocomplete_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:810px;height:150px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_sku_autocomplete_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Search SKU</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="SKU_AUTOCOMPLETE_POPUP.close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sku_autocomplete_dialog_content" style="padding:2px;">
		<form name="f_sku_autocomplete" onSubmit="return false;">
			{include file='sku_items_autocomplete.tpl' parent_form='document.f_sku_autocomplete' multiple_add=1 under_parent_div="div_sku_autocomplete_dialog"}
		</form>
	</div>
</div>
<!-- End of SKU AUTOCOMPLETE  DIALOG -->

<!-- RULE NUM DIALOG -->
<div id="div_select_rule_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:750px;height:400px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_select_rule_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Select Rule</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="SELECT_RULE_NUM_POPUP.close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_select_rule_dialog_content" style="padding:2px;height:370px;">
		<div style="height:330px;border:2px inset black;background-color:#fff;overflow:auto;">
			<form name="f_select_rule" onSubmit="return false;" onSubmit="return false;">
				<input type="hidden" name="a" value="ajax_add_foc_item" />
				<input type="hidden" name="sid" />
				<input type="hidden" name="branch_id" value="{$form.branch_id}" />
				<input type="hidden" name="pa_id" value="{$form.id}" />
				
				<table width="100%" class="report_table">
					<tr class="header">
						<th width="20">&nbsp;</th>
						<th>Rule #</th>
						<th>ARMS Code</th>
						<th>MCode</th>
						<th>Description</th>
					</tr>
					<tr id="tmp_tr_select_rule" style="display:none;">
						<td><input type="checkbox" name="select_rule[__RULE_NUM__]" value="__RULE_NUM__" class="inp_select_rule" /></td>
						<td align="center">__RULE_NUM__</td>
						<td>__ARMS_CODE__</td>
						<td>__MCODE__</td>
						<td>__DESCRIPTION__</td>
					</tr>
					<tbody id="tbody_select_rule_list">
					
					</tbody>
				</table>
			</form>
		</div>
		<div style="text-align:center;">
			<p><input type="button" value="Save FOC Item" onClick="SELECT_RULE_NUM_POPUP.save_foc_item();" id="inp_saving_foc_item" /></p>
		</div>		
	</div>
</div>
<!-- End of RULE NUM  DIALOG -->

<h1>{$PAGE_TITLE}</h1>

<h3>Status:
{if $form.label eq 'approved'}
	Fully Approved
{elseif $form.label eq 'waiting_approve'}
	In Approval Cycle
{elseif $form.label eq 'cancelled_terminated'}
	Cancelled/Terminated
{elseif $form.label eq 'rejected'}
	Rejected
{else}
	Draft
{/if}
</h3>
{include file=approval_history.tpl}

{if $err}
	<div><div class="errmsg"><ul>
	{foreach from=$err item=e}
	<li> {$e}</li>
	{/foreach}
	</ul></div></div>
{/if}

{if $form.approval_screen}
	<form name="f_c" method="post">
		<input type="hidden" name="a" value="save_approval" />
		<input type="hidden" name="approve_comment" value="" />
		<input type="hidden" name="id" value="{$form.id}" />
		<input type="hidden" name="branch_id" value="{$form.branch_id}" />
		<input type="hidden" name="approvals" value="{$form.approvals}" />
		<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}" />
		{if $approval_on_behalf}
		<input type="hidden" name="on_behalf_of" value="{$approval_on_behalf.on_behalf_of}" />
		<input type="hidden" name="on_behalf_by" value="{$approval_on_behalf.on_behalf_by}" />
		{/if}
	</form>
{/if}

<form name="f_a" method="post" onSubmit="return false;">
	<input type="hidden" name="a" value="save" />
	<input type="hidden" name="branch_id" value="{$form.branch_id}" />
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}" />
	<input type="hidden" name="reason" />
	<input type="hidden" name="active" value="{$form.active}" />
	<input type="hidden" name="status"  value="{$form.status}" />
	<input type="hidden" name="approved" value="{$form.approved}" />
	
	<div class="stdframe" style="background:#fff">
		<h4>General Information</h4>
		<table border="0" cellspacing="0" cellpadding="4">
			<!-- Title -->
			<tr>
			    <td><b>Title</b></td>
			    <td>
			    	<input type="text" name="title" maxlength="200" size="80" value="{$form.title}" class="required" title="Title" />
			    	<img src=ui/rq.gif align=absbottom title="Required Field" />	
			    </td>
			</tr>
			
			<!-- Dept -->
			<tr>
				<td><b>Department</b></td>
				<td>
					<select name="dept_id" class="required">
						{foreach from=$dept_list key=dept_id item=r}
							<option value="{$dept_id}" {if $form.dept_id eq $dept_id}selected {/if}>{$r.description}</option>
						{/foreach}
					</select>
					<img src=ui/rq.gif align=absbottom title="Required Field" />
				</td>
			</tr>
			
			<!-- Vendor -->
			<tr>
				<td><b>Vendor</b></td>
				<td>
					<input name="vendor_id" size="1" value="{$form.vendor_id}" readonly />
					<input id="inp_autocomplete_vendor" name="vendor_desc" value="{$form.vendor_desc}" size="50" />
					<img src=ui/rq.gif align=absbottom title="Required Field" />
					
					{if !$form.approval_screen}
						<div id="autocomplete_vendor_choices" class="autocomplete" style="display:none;"></div>	
					{/if}
				</td>
			</tr>
			
			<!-- Type -->
			<tr>
				<td><b>Type</b></td>
				<td>
					<select name="pa_type">
						{foreach from=$pa_type_list key=t item=v}
							<option value="{$t}" {if $form.pa_type eq $t}selected{/if} >{$v}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			
			<!-- Date -->
			<tr>
			    <td><b>Date</b></td>
			    <td>
			        <input type="text" name="date_from" id="inp_date_from" size="12" value="{$form.date_from|default:$smarty.now|date_format:"%Y-%m-%d"}" class="required" title="Date From" />
					{if $allow_edit}
						<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date" />
					{/if}
					<b>to</b>
					<input type="text" name="date_to" id="inp_date_to" size="12" value="{$form.date_to|default:$smarty.now|date_format:"%Y-%m-%d"}" class="required" title="Date To" />
					{if $allow_edit}
						<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date" />
					{/if}
			    </td>
			</tr>
			
			{* Remark *}
			<tr>
				<td valign="top"><b>Remarks</b></td>
				<td>
					<textarea name="remark" rows="2" cols="60">{$form.remark}</textarea>
				</td>
			</tr>
		</table>
		
		<div id="div_refresh" style="{if ($smarty.request.a eq 'refresh' || $smarty.request.id) && !$need_refresh} display:none; {/if} padding-top:10px">
			<input id="btn_refresh" class="btn btn-primary" type="button" value="click here to continue" />
		</div>
	</div>
	
	{if ($smarty.request.a eq 'refresh' or !is_new_id($form.id) or $err) && !$need_refresh}
		<br />
		<div id="pa_content_list">
			<h3>Item List</h3>
			<div id="pa_item_list">
				{include file="po.po_agreement.setup.open.item_list.tpl"}
			</div>
			{if $allow_edit}
				<div style="border:1px solid #999; padding:2px; background-color:#dddddd">
					
					<input type="button" value="Add Item" onClick="SKU_AUTOCOMPLETE_POPUP.open('item');" />
					<span id="span_adding_item_loading" style="display:none;background: yellow;padding:2px;">
						<img src="/ui/clock.gif" align="absmiddle" /> Loading…
					</span>
					
				</div>
			{/if}
			
			<h3>FOC Item List</h3>
			<div id="pa_foc_item_list">
				{include file="po.po_agreement.setup.open.foc_item_list.tpl"}
			</div>
			
			{if $allow_edit}
				<div style="border:1px solid #999; padding:2px; background-color:#dddddd">
					<input type="button" value="Add FOC Item" onClick="SKU_AUTOCOMPLETE_POPUP.open('foc_item');" />
					<span id="span_adding_foc_item_loading" style="display:none;background: yellow;padding:2px;">
						<img src="/ui/clock.gif" align="absmiddle" /> Loading…
					</span>
				</div>
			{/if}
		</div>
	{/if}
</form>

<script type="text/javascript">
	PURCHASE_AGREEMENT_MODULE.initialize();
</script>

{if $form.is_approval and $form.status==1 and $form.approved==0 and $form.approval_screen}
	<p align="center">
		<input type=button value="Approve" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_approve({$form.last_approver})">
		<input type=button value="Reject" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_reject({$form.last_approver})">
		<input type=button value="Terminate" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_terminate({$form.last_approver})">
	</p>
{/if}
	
{if !$form.approval_screen}
      <p align="center">
        {if $allow_edit and (!$form.status or $form.status eq 2) and !$form.approved and !$form.first_time}
			<input type="button" value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save();" />
		{/if}
		
        {if is_new_id($form.id) || !$allow_edit}
			<input type="button" class="btn btn-error" value="Close" onclick="document.location='{$smarty.server.PHP_SELF}'" />
		{/if}

		{if !$form.first_time}
			{if $form.approved}
				{if $form.pa_type eq 'normal' and $allow_edit}
					<input type="button" value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save();" />
					<input type="button" value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='{$smarty.server.PHP_SELF}'" />
				{else}
					{if $form.status!=4 && $form.status!=5 && $form.status!=0 && $form.active}
						{if $form.user_id eq $sessioninfo.id || $sessioninfo.level>=9999}
							<input type=button value="Reset" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_reset();" />
						{/if}
						
						{if $form.pa_type eq 'normal'}
							<input type=button value="Edit" style="font:bold 20px Arial; background-color:#009; color:#fff;" onclick="do_edit();" />
						{/if}
					{/if}
				{/if}
			{/if}
						
			{if $form.user_id eq $sessioninfo.id || $sessioninfo.level>=9999}
				{if $form.approved}
									
				{elseif ($form.active || !$form.status) && $allow_edit &&!is_new_id($form.id)}
					<input type=button value="Delete" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_delete()">
				{/if}	
			{/if}

			{if $allow_edit and $form.status == 0 and $form.approved == 0}
				<input type=button value="Confirm" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="do_confirm();" />
			{/if}
		{/if}
    </p>
{/if}

{include file="footer.tpl"}
