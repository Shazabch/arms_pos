{*
4/10/2013 5:08 PM Andy
- Remove sheet discount and not allow user to edit item discount.
- Remove batch code.

5/14/2013 3:07 PM Andy
- Remove use promotion price from debtor sales order.

5/29/2013 2:09 PM Andy
- Enhance to allow user to manually add item.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call
*}

{include file="header.tpl"}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
input[disabled],input[readonly],select[disabled], textarea[disabled]{
  color:black;
}

.inp_ctn, .inp_pcs{
	width: 30px;
}

.inp_doc_allow_decimal{
	width: 45px !important;
}

#p_submit_btn input[disabled]{
	background-color: grey !important;
}
</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var global_qty_decimal_points = int('{$config.global_qty_decimal_points}');
var global_cost_decimal_points = int('{$config.global_cost_decimal_points}');
var can_edit = !int('{$readonly}');
{literal}

var SALES_ORDER = {
	f: undefined,
	sku_autocomplete: undefined,
	initialize: function(){
		this.f = document.f_a;
		var THIS = this;
		
		if(can_edit){
			// init calendar
			Calendar.setup({
				inputField     :    "inp_order_date",
				ifFormat       :    "%Y-%m-%d",
				button         :    "img_order_date",
				align          :    "Bl",
				singleClick    :    true
			});
			
			this.sku_autocomplete = new SKU_AUTOCOMPLETE(this.f, 'so-', function(sid, ret_params){
				THIS.add_item_clicked(sid, ret_params);			
			});
		
			new Draggable('div_sku_group_item_popup',{ handle: 'div_sku_group_item_popup_header'});
			
			//new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
		}else{
			Form.disable(this.f);
		}
	},
	// function to check form header
	check_header: function(){
		if(!this.f['order_date'].value.trim()){
			alert('Please select Order Date.');
			this.f['order_date'].focus();
			return false;
		}
		
		/*if(!this.f['batch_code'].value.trim()){
			alert('Please key in Batch Code.');
			this.f['batch_code'].focus();
			return false;
		}*/
		
		if(this.f['user_id']){
			if(!this.f['user_id'].value){
				alert('Please select Assigned Owner.');
				this.f['user_id'].focus();
				return false;
			}
		}
		return true;
	},
	// function to check items
	check_items: function(){
		var tr_item_list = $$("#tbody_item_list tr.tr_item");
		
		if(tr_item_list.length<=0){
			alert('No item in the list');
			return false;
		}
		
		return true;
	},
	// function when user click save
	btn_save_clicked: function(){
		this.perform_save({});	// call function to save
	},
	// core function to save
	perform_save: function(params){
		if(!this.check_header())	return false;
		
		if(!this.check_items())	return false;
		$('span_saving_so').show();
		$$('#p_submit_btn input').invoke('disable');
		
		var xtra_params = '';
		if(params['confirm'])	xtra_params += '&a=ajax_confirm';
		else	xtra_params += '&a=ajax_save';
		var params = $(this.f).serialize()+xtra_params;
		
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    $('span_saving_so').hide();
				$$('#p_submit_btn input').invoke('enable');
						
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['id']){ // success
						var t = 'save';
						if(params['confirm'])	t = 'confirm';
						window.location = 'dp.sales_order.php?t='+t+'&id='+ret['id'];
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user click reload item list from sku group
	reload_item_list_by_sku_group_clicked: function(){
		if(!this.f['sku_group_id'].value){
			alert('Please select SKU Group first.');
			return false;
		}
		
		var tr_item_list = $$("#tbody_item_list tr.tr_item");
		if(tr_item_list.length>0 && !confirm('Are you sure? All current item information will lost.'))	return false;
		
		var THIS = this;
		
		this.enable_submit_in_progress();
		
		var params = {
			'a': 'ajax_reload_item_list_by_sku_group',
			'sku_group_id': this.f['sku_group_id'].value,
			'branch_id': this.f['branch_id'].value,
			'sales_order_id': this.f['id'].value,
			'date': this.f['order_date'].value
			//'use_promo_price': this.f['use_promo_price'].checked ? 1 : 0
		};
		
		$('span_add_item_processing').show();
		
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    THIS.disable_submit_in_progress();
				$('span_add_item_processing').hide();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						$('tbody_item_list').update(ret['html']);	// update item into html
						
						THIS.f['selling_type'].value = ret['mprice_type'];	// update mprice type
						
						THIS.renum_row_no();	// re-number row no
						THIS.recalc_all_total();	// recalculate total
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user click show sku group item
	show_item_list_by_sku_group_clicked: function(){
		if(!this.f['sku_group_id'].value){
			alert('Please select SKU Group first.');
			return false;
		}
		
		var THIS = this;
		var params = {
			'a': 'ajax_show_item_list_by_sku_group',
			'sku_group_id': this.f['sku_group_id'].value
		};
		
		$('div_sku_group_item_popup_content').update(_loading_);
		
		curtain(true);
		center_div($('div_sku_group_item_popup').show());
		
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						$('div_sku_group_item_popup_content').update(ret['html']);	// update item into html
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
				$('div_sku_group_item_popup_content').update(err_msg);
				
			    // prompt the error
			    alert(err_msg);
			    
			}
		});
	},
	// function to show submit in progress
	enable_submit_in_progress: function(){
		$('span_saving_so').show();
		$$('#p_submit_btn input').invoke('disable');
	},
	// function to turn off submit in progress
	disable_submit_in_progress: function(){
		$('span_saving_so').hide();
		$$('#p_submit_btn input').invoke('enable');
	},
	// function to re-number row no
	renum_row_no: function(){
		var row_no = 0;
		$$("#tbody_item_list tr.tr_item span.span_no").each(function(ele){
			row_no++;
			$(ele).update(row_no+'.');
		});
	},
	// function to recalculate row total
	recalc_row: function(so_item_id, no_recalc_all){
		if(!so_item_id)	return false;
		
		var row_ctn = float(this.f['item_list['+so_item_id+'][ctn]'].value);
		var row_pcs = float(this.f['item_list['+so_item_id+'][pcs]'].value);
		var fraction = float(this.f['item_list['+so_item_id+'][uom_fraction]'].value);
		var selling = float(this.f['item_list['+so_item_id+'][selling_price]'].value);
		var row_qty = (row_ctn*fraction)+row_pcs;
		var row_amt = (row_ctn*selling)+(selling/fraction*row_pcs);
		var discount_format = this.f['item_list['+so_item_id+'][item_discount]'].value.trim();
		
		// calculate discount amount
		var discount_amt = float(round(get_discount_amt(row_amt, discount_format),2));
		if(discount_amt){
			row_amt -= discount_amt
		}
	
		row_qty = float(round(row_qty, global_qty_decimal_points));
		row_amt = float(round(row_amt, 2));
		
		$('inp_row_qty-'+so_item_id).value = row_qty;
		$('inp_row_amt-'+so_item_id).value = row_amt;
		
		$('span_row_qty-'+so_item_id).update(row_qty);
		$('span_row_amount-'+so_item_id).update(round(row_amt, 2));
		
		this.f['item_list['+so_item_id+'][item_discount_amount]'].value = discount_amt;
		
		if(!no_recalc_all)	this.recalc_all_total();	// recalculate sheet total
	},
	// function to recalculate all total
	recalc_all_total: function(){
		var tr_item_list = $$("#tbody_item_list tr.tr_item");
		
		var total_ctn = total_pcs = total_qty = total_amt = 0;
		var sheet_discount = this.f['sheet_discount'].value.trim();
		var sheet_discount_amt = 0;
	
		for(var i=0, len = tr_item_list.length; i<len; i++){
			var so_item_id = tr_item_list[i].id.split("-")[1];
			
			var row_ctn = float(this.f['item_list['+so_item_id+'][ctn]'].value);
			var row_pcs = float(this.f['item_list['+so_item_id+'][pcs]'].value);
			var row_qty = float($('inp_row_qty-'+so_item_id).value);
			var row_amt = float($('inp_row_amt-'+so_item_id).value);
			
			total_ctn += row_ctn;
			total_pcs += row_pcs;
			total_qty += row_qty;
			total_amt += row_amt;
		}
		
		var sub_total_amt = total_amt;
		sheet_discount_amt = float(round(get_discount_amt(sub_total_amt, sheet_discount),2));
		if(sheet_discount_amt){
			total_amt -= sheet_discount_amt;
		}
		
		total_ctn = float(round(total_ctn, global_qty_decimal_points));
		total_pcs = float(round(total_pcs, global_qty_decimal_points));
		total_qty = float(round(total_qty, global_qty_decimal_points));
		total_amt = float(round(total_amt, 2));
		
		if(sub_total_amt != total_amt){
			$('span_sheet_discount').update(sheet_discount);
			$('span_sheet_discount_amount').update(round(sheet_discount_amt*-1, 2));
			$('span_sub_total_amount').update(round(sub_total_amt, 2));
			
			$('tr_sheet_discount').show();
			$('tr_sub_total').show();
		}else{
			$('tr_sheet_discount').hide();
			$('tr_sub_total').hide();
		}
		
		this.f['total_ctn'].value = total_ctn;
		this.f['total_pcs'].value = total_pcs;
		this.f['total_qty'].value = total_qty;
		this.f['total_amount'].value = total_amt;
		this.f['sheet_discount_amount'].value = sheet_discount_amt;
		
		$('span_total_ctn').update(total_ctn);
		$('span_total_pcs').update(total_pcs);
		$('span_total_amt').update(round(total_amt, 2));
	},
	// function when user change item ctn
	inp_ctn_changed: function(so_item_id){
		if(!so_item_id)	return false;
		
		var doc_allow_decimal = int(this.f['item_list['+so_item_id+'][doc_allow_decimal]'].value);
		var inp = this.f['item_list['+so_item_id+'][ctn]'];
		
		if(doc_allow_decimal){
			mf(inp, global_qty_decimal_points);
		}else{
			mi(inp);
		}
		
		this.recalc_row(so_item_id);
	},
	// function when user change item pcs
	inp_pcs_changed: function(so_item_id){
		if(!so_item_id)	return false;
		
		var doc_allow_decimal = int(this.f['item_list['+so_item_id+'][doc_allow_decimal]'].value);
		var inp = this.f['item_list['+so_item_id+'][pcs]'];
		
		if(doc_allow_decimal){
			mf(inp, global_qty_decimal_points);
		}else{
			mi(inp);
		}
		
		this.recalc_row(so_item_id);
	},
	// function when user change selling price
	inp_selling_changed: function(so_item_id){
		if(!so_item_id)	return false;
		
		var inp = this.f['item_list['+so_item_id+'][selling_price]'];
		mf(inp, 2);
		
		this.recalc_row(so_item_id);
	},
	// function when user change item discount
	item_discount_changed: function(so_item_id){
		if(!so_item_id)	return false;
		
		var inp = this.f['item_list['+so_item_id+'][item_discount]'];
		var discount_format = inp.value.trim();
	
		// check discount pattern
		discount_format = validate_discount_format(discount_format);
		
		inp.value = discount_format;
		
		// recalculate row
		this.recalc_row(so_item_id);
	},
	// function when user change sheet discount
	sheet_discount_changed: function(){
		var inp = this.f['sheet_discount'];
		var discount_format = inp.value.trim();
		
		// check discount pattern
		discount_format = validate_discount_format(discount_format);
		
		// found if discount more than 100%, set it become maximum 100% 
		//if(discount != '' && discount > 100) discount = 100;
		
		inp.value = discount_format;
		
		// recalculate total
		this.recalc_all_total();
	},
	// function when user toggle use promo price
	/*use_promo_price_changed: function(){
		var THIS = this;
		var tr_item_list = $$("#tbody_item_list tr.tr_item");
		
		if(tr_item_list.length<=0)	return false;	// no item
		
		var params = $(this.f).serialize()+'&a=reload_selling_price';
		
		this.f['use_promo_price'].disabled = true;
		$('span_promo_price_loading').show();
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    THIS.f['use_promo_price'].disabled = false;
				$('span_promo_price_loading').hide();
						
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['item_list']){ // success
						for(var so_item_id in ret['item_list']){
							var fraction = float(THIS.f['item_list['+so_item_id+'][uom_fraction]'].value);
							var selling = float(round(float(ret['item_list'][so_item_id]['selling_price'])*fraction,2));
							
							THIS.f['item_list['+so_item_id+'][selling_price]'].value = round(selling, 2);
							THIS.recalc_row(so_item_id, true);
						}
						THIS.recalc_all_total();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);
			}
		});
	},*/
	// function when user click delete
	btn_delete_clicked: function(){
		var reason = prompt('Reason');
		if(!reason || !reason.trim())	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		this.enable_submit_in_progress();
		var THIS = this;
		
		var params = {
			'a': 'ajax_delete_so',
			'branch_id': this.f['branch_id'].value,
			'id': this.f['id'].value,
			'reason': reason
		};
		
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    THIS.disable_submit_in_progress();
						
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						window.location = 'dp.sales_order.php?t=delete&id='+THIS.f['id'].value;
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user click add item
	add_item_clicked: function(sid, ret_params){
		if(!sid)	return false;
		
		this.add_item([sid]);
	},
	// core function to add item
	add_item: function(sid_list, params){
		if(!sid_list || sid_list.length<=0)	return false;
		
		this.enable_submit_in_progress();
		$('span_add_item_processing').show();
		
		var THIS = this;
		
		var tmp_params = {
			'sid_list[]': sid_list
		};
		
		var str_params = $(this.f).serialize()+'&'+$H(tmp_params).toQueryString()+'&a=ajax_add_item';
		
		ajax_request(phpself, {
			parameters: str_params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    THIS.disable_submit_in_progress();
				$('span_add_item_processing').hide();
						
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						new Insertion.Bottom($('tbody_item_list'), ret['html']);
						
						THIS.renum_row_no();	// re-number row no
						//THIS.recalc_all_total();	// recalculate total
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user click delete item
	delete_item_clicked: function(item_id){
		if(!item_id)	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		$('tr_item-'+item_id).remove();
		this.renum_row_no();	// re-number row no
		this.recalc_all_total();	// recalculate total
	},
	// function when user toggle select all sku group item
	toggle_all_sku_group_item: function(){
		var checked = $('chx_toggle_all_sku_group_item').checked;
		
		$(document.f_sku_group_item).getElementsBySelector('input.chx_sku_group_item').each(function(ele){
			ele.checked = checked;
		})
	},
	// function when user click add sku group item
	add_sku_group_item_clicked: function(){
		if(!document.f_sku_group_item)	return false;
		
		var sid_list = [];
		var chx_sku_group_item_list = $$('#f_sku_group_item input.chx_sku_group_item');
		for(var i=0, len=chx_sku_group_item_list.length; i<len; i++){
			if(chx_sku_group_item_list[i].checked){
				sid_list.push(chx_sku_group_item_list[i].value);
			}
		}
		
		if(sid_list.length<=0){
			alert('Please select at least 1 item');
			return false;
		}
		
		curtain(false);
		$('div_sku_group_item_popup').hide();
		
		this.add_item(sid_list);
	}
};

{/literal}
</script>

<div id="div_sku_group_item_popup" class="curtain_popup" style="position:absolute;z-index:10000;width:500px;height:500px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding: 0 !important;">
	<div id="div_sku_group_item_popup_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">SKU Group Iten List</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sku_group_item_popup_content" style="padding:2px;"></div>
</div>

<h1>{$PAGE_TITLE}</h1>

<h3>Status:
{if $form.delivered}
	Delivered
{elseif $form.approved}
	Fully Approved
{elseif $form.status == 1}
	In Approval Cycle
{elseif $form.status == 5}
	Cancelled
{elseif $form.status == 4}
	Terminated
{elseif $form.status == 3}
	In Approval Cycle (KIV)
{elseif $form.status == 2}
	Rejected
{elseif $form.status == 0}
	Draft Order
{/if}
</h3>
{include file=approval_history.tpl}

<form name="f_a" onSubmit="return false;">

<input type="hidden" name="a" value="save" />
<input type="hidden" name="branch_id" value="{$form.branch_id}" />
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="order_no" value="{$form.order_no}" />
<input type="hidden" name="total_ctn" value="{$form.total_ctn}" />
<input type="hidden" name="total_pcs" value="{$form.total_pcs}" />
<input type="hidden" name="total_amount" value="{$form.total_amount}" />
<input type="hidden" name="total_qty" value="{$form.total_qty}" />
<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}" />
<input type="hidden" name="reason" />
<input type="hidden" name="sheet_discount_amount" value="{$form.sheet_discount_amount}" />
<input type="hidden" name="selling_type" value="{$form.selling_type}" />

<div class="stdframe" style="background:#fff">
	<h4>General Informations</h4>

	<table border="0" cellspacing="0" cellpadding="4">
		<tr>
			<th width="120" align="left">Order Date </th>
			<td><input name="order_date" id="inp_order_date" size="10" maxlength="10"  value="{$form.order_date|default:$smarty.now|date_format:"%Y-%m-%d"}" />
				{if !$readonly}
					<img align="absmiddle" src="ui/calendar.gif" id="img_order_date" style="cursor: pointer;" title="Select Date" />
				{/if}
			</td>
		</tr>
		
		<tr style="display:none;">
			<th align="left">Batch Code</th>
			<td><input name="batch_code" size=12 value="{$form.batch_code}" id="inp_batch_code" /> <img src="ui/rq.gif" align="absmiddle" />
			    <span id="span_loading_batch_code" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading…</span>
			</td>
		</tr>
		
		<tr>
			<th align="left">Customer PO</th>
			<td><input name="cust_po" size=12 value="{$form.cust_po}" /></td>
		</tr>
		
		<!-- Sheet Discount -->
		<tr style="{if !$form.sheet_discount}display:none;{/if}">
			<th align="left">Discount</th>
			<td>
				<input name="sheet_discount" size="12" value="{$form.sheet_discount}" onChange="SALES_ORDER.sheet_discount_changed();" type="hidden" />
				{$form.sheet_discount}
				<b>[<a href="javascript:void(show_discount_help());">?</a>]</b>
			</td>
		</tr>
		
		<tr>
			<td valign="top"><b>Remarks</b></td>
			<td>
				<textarea rows="2" cols="68" name="remark" onchange="uc(this);">{$form.remark}</textarea>
			</td>
		</tr>
		
		{*<tr>
			<td valign="top"><b>Use Promotion Price</b></td>
			<td>
				<input type="checkbox" name="use_promo_price" value="1" {if $form.use_promo_price}checked {/if} onChange="SALES_ORDER.use_promo_price_changed();" />
				<span id="span_promo_price_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading…</span>
			</td>
		</tr>*}
		
		<tr>
		    <th align="left">Branch</th>
		    <td>
				{$branches[$form.branch_id].code} - {$branches[$form.branch_id].description}
			</td>
		</tr>
		
		<tr>
			<th align="left">Assigned Owner</th>
			<td>
				{if is_new_id($form.id)}
					<select name="user_id">
						<option value="">-- Please Select --</option>
						{foreach from=$user_list key=uid item=r}
							<option value="{$uid}">{$r.u}</option>
						{/foreach}
					</select>
					(Owner cannot be change after save)
				{else}
					{$form.username}
				{/if}
			</td>
		</tr>
	</table>
</div>

<br />

<div id="div_sheets">
	{include file='dp.sales_order.open.sheet.tpl'}
</div>

{if !$readonly}
	<div style="background:#ddd;border:1px solid #999;">
		<table>
			<tr>
				<td><b>Reload Item List from SKU Group</b></td>
				<td>
					<select name="sku_group_id">
						<option value="">-- Please Select --</option>
						{foreach from=$sku_group_list item=r}
							<option value="{$r.branch_id},{$r.sku_group_id}">{$r.code} - {$r.description}</option>
						{/foreach}
					</select>
					{*<input type="button" id="btn_reload_item_list_by_sku_group" value="Reload Item List" onClick="SALES_ORDER.reload_item_list_by_sku_group_clicked();" />*}
					<input type="button" id="btn_show_item_list_by_sku_group" value="Show Item List" onClick="SALES_ORDER.show_item_list_by_sku_group_clicked();" />
					<span id="sku_group_item_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading…</span>
				</td>
			</tr>
		</table>
		
		<hr />
		
		{include file="dp.sku_items_autocomplete.tpl" prefix="so-"}
		
		<br />
		<span id="span_add_item_processing" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading…</span>
	</div>
{/if}
	
</form>

<p id="p_submit_btn" align="center">
	{if !$form.approval_screen}
		{if !$readonly}
			{if (!$form.status || $form.status==2) and $form.branch_id}
				<input name="bsubmit" type="button" value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="SALES_ORDER.btn_save_clicked()" />
			{/if}

			{if !is_new_id($form.id) and $form.create_by_debtor_id eq $dp_session.id}
				<input type="button" value="Delete" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="SALES_ORDER.btn_delete_clicked()" />
			{/if}

			{if (!$form.status || $form.status==2) and $form.branch_id}
				{*<input type="button" value="Confirm" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="SALES_ORDER.btn_confirm_clicked()" />*}
			{/if}
		{/if}
		<input type="button" value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/dp.sales_order.php'" />
	{/if}
	
	<br /><br />
	<span id="span_saving_so" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading…</span>
</p>

<script type="text/javascript">SALES_ORDER.initialize();</script>

{include file="footer.tpl"}
