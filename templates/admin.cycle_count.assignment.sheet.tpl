{*
8/26/2019 4:14 PM Andy
- Fixed unable to recall and re-open.
*}

{include file='header.tpl'}

<style>
{literal}
#tbl_item_list tr:nth-child(even) {
    background-color: #eee;
}

input.inp_backend_qty{
	width: 90px;
	text-align: right;
}

td.td_total_qty, td.td_calculated_st_qty{
	background-color: #ABFE92;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var wip = int('{$form.wip}');
var completed = int('{$form.completed}');
var can_edit = int('{$can_edit}');
var global_qty_decimal_points = int('{$config.global_qty_decimal_points}');

{literal}
var CC_SHEET = {
	f: undefined,
	backend_qty_list: {},
	initialize: function(){
		this.f = document.f_a;
		
		reset_sku_autocomplete();
	},
	// function when user change the page
	page_changed: function(){
		this.reload_item_list();
	},
	// Core function to reload item
	reload_item_list: function(ref_params){
		if(!ref_params)	ref_params = {};
		$('div_item_list').update(_loading_);
		
		var params = $(this.f).serialize()+'&a=ajax_change_sheet_page';
		if(ref_params['highlight_sku_item_id']){
			params += '&highlight_sku_item_id='+ref_params['highlight_sku_item_id'];
		}
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			asynchronous: false,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Reload
						$('div_item_list').update(ret['html']);
						
						// Put back edited backend_qty
						THIS.restore_edited_backend_qty();
						
						// Focus on backend qty
						if(ref_params['highlight_sku_item_id']){
							THIS.focus_backend_qty(ref_params['highlight_sku_item_id']);
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
				$('div_item_list').update(err_msg);
			}
		});
	},
	// function when users click on print
	print_clicked: function(regen){
		if(!wip && !completed && can_edit){
			if(regen){
				if(!confirm('Are you sure to Regenerate the SKU Listing?')){
					return false;
				}
			}
		}else{
			regen = 0;
		}
		
		var branch_id = this.f['branch_id'].value;
		var id = this.f['id'].value;
		var url = phpself+'?a=print_cycle_count&branch_id='+branch_id+'&id='+id;
		if(regen){
			url += '&regen=1';
		}
		window.open(url);
		
		if(regen){
			// show wait popup
			GLOBAL_MODULE.show_wait_popup();
			
			// Reload current page
			location.reload(true);
		}
	},
	// function when user click on start now
	start_clicked: function(){
		if(!confirm('Are you sure to Start Now?')){
			return false;
		}
		
		this.f['a'].value = 'ajax_mark_wip';
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		var params = $(this.f).serialize();
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			asynchronous: false,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Reload current page
						location.reload(true);
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
				GLOBAL_MODULE.hide_wait_popup();
			}
		});
	},
	// function when user changed backend_qty
	backend_qty_changed: function(item_id){
		if(!item_id){
			alert('Invalid Item ID');
			return false;
		}
		
		// Recalculate
		this.recalculate_row(item_id);
		
		// Get the Element
		var inp_backend_qty = $('inp_backend_qty-'+item_id);
		
		// Get Qty
		var backend_qty = inp_backend_qty.value.trim();
		
		// Update Backend Qty List
		var item_key = 'tmp_backend_qty['+item_id+']';
		this.backend_qty_list[item_key] = backend_qty;	
	},
	// Core function to recalculate row
	recalculate_row: function(item_id){
		if(!item_id){
			return false;
		}
		
		var inp_backend_qty = $('inp_backend_qty-'+item_id);
		var inp_app_qty = $('inp_app_qty-'+item_id);
		var inp_doc_allow_decimal = $('inp_doc_allow_decimal-'+item_id);
		
		// Check Qty
		var backend_qty = inp_backend_qty.value.trim();
		var doc_allow_decimal = inp_doc_allow_decimal ? int($(inp_doc_allow_decimal).value) : 0;
		//////////////////////////////
		if(doc_allow_decimal){
			backend_qty = parseFloat(backend_qty);
		}else{
			backend_qty = parseInt(backend_qty, 10);
		}
		
		if (isNaN(backend_qty)){
			backend_qty = '';
		}else{
			if(doc_allow_decimal){
				backend_qty = round(backend_qty, global_qty_decimal_points);
			}
		}
		inp_backend_qty.value = backend_qty;
		var app_qty = inp_app_qty.value.trim();
		var total_qty = '';
		
		// Sum Qty
		if(backend_qty !== '' || app_qty !== ''){
			total_qty = int(backend_qty) + int(app_qty);
		}
		
		// Update Total
		$('span_total_qty-'+item_id).update(total_qty);
	},
	// Core function to put back backend_qty
	restore_edited_backend_qty: function(){
		// Get All Item Row
		var tr_item_list = $$('#tbl_item_list tr.tr_item');
		
		// Loop Item Row
		for(var i=0,len=tr_item_list.length; i < len; i++){
			// Get Row Item ID
			var item_id = this.get_item_id_by_obj(tr_item_list[i]);
			
			// Construct Item Key
			var item_key = 'tmp_backend_qty['+item_id+']';
			
			// Check if got edit qty previously
			if(this.backend_qty_list[item_key]){
				// Restore the qty
				$('inp_backend_qty-'+item_id).value = this.backend_qty_list[item_key];
				
				// Recalculate Row
				this.recalculate_row(item_id);
			}
		}
	},
	// Core function to get item_id
	get_item_id_by_obj: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the div contain group id
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_item')){    // found the div
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
	// function when users click on save
	save_clicked: function(is_confirm){
		if(!is_confirm)	is_confirm = 0;
		
		if(is_confirm){
			if(!confirm('Are you sure to mark it as Complete?'))	return false;
		}
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		this.f['a'].value = 'ajax_wip_save';
		var params = $(this.f).serialize();
		if(is_confirm)	params += '&is_confirm=1';
		params += '&'+$H(this.backend_qty_list).toQueryString();
		
		var THIS = this;
		
		//alert(params);return;
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			asynchronous: false,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						if(is_confirm){
							// Go back to main page
							document.location = phpself+'?t=completed&id='+THIS.f['id'].value;
						}else{
							// Reload current page
							location.reload(true);
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
				GLOBAL_MODULE.hide_wait_popup();
			}
		});
	},
	// function to find sku
	find_sku: function(sku_item_id, ref_params){
		if(!ref_params)	ref_params = {};
		var find_by_grn_barcode = 0;
		if(ref_params['find_by_grn_barcode'])	find_by_grn_barcode = 1;
		
		if(!sku_item_id && !find_by_grn_barcode)	return;
		
		var found = false;
		if(sku_item_id){
			// Check this page first
			found = this.highlight_sku_item_id(sku_item_id);
			if(found)	return;
		}
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		var params = $(this.f).serialize()+'&a=ajax_find_sku';
		if(find_by_grn_barcode)	params += '&find_by_grn_barcode=1';
		
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			asynchronous: false,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				GLOBAL_MODULE.hide_wait_popup();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['page_num'] && ret['sku_item_id']){ // success
						// Change the page num
						THIS.f['sel_page'].value = ret['page_num'];
						// Reload with Highlight
						THIS.reload_item_list({'highlight_sku_item_id': ret['sku_item_id']});
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
				
			}
		});
	},
	// function to highlight sku by sku_item_id
	highlight_sku_item_id: function(sku_item_id){
		if(!sku_item_id)	return false;
		
		// Remove all highlight
		$$('#tbl_item_list tr.tr_item').each(function(tr){
			$(tr).removeClassName('highlight_row');
		});
		
		// Get which item is this sku_item_id
		var inp_sku_item_id_list = $$('#tbl_item_list input.inp_sku_item_id-'+sku_item_id);
		if(!inp_sku_item_id_list)	return false;
		
		// Get Item ID
		var item_id = this.get_item_id_by_obj(inp_sku_item_id_list[0]);
		if(!item_id)	return false;
		
		// Mark Highlight
		$('tr_item-'+item_id).addClassName('highlight_row');
		
		// Focus on backend qty
		this.focus_backend_qty(sku_item_id);
		
		return true;
	},
	// function to move focus to item backend qty field
	focus_backend_qty: function(sku_item_id){
		// Get which item is this sku_item_id
		var inp_sku_item_id_list = $$('#tbl_item_list input.inp_sku_item_id-'+sku_item_id);
		if(!inp_sku_item_id_list)	return false;
		
		// Get Item ID
		var item_id = this.get_item_id_by_obj(inp_sku_item_id_list[0]);
		if(!item_id)	return false;
		
		var inp = $("inp_backend_qty-"+item_id);
		
		if(!inp)	return;
		
		inp.focus();
	},
	// function when user click on pos_qty icon
	show_pos_qty_notify: function(){
		var str = "POS Quantity since the Stock Take Started until the SKU First Stock Take.\n\n";
		str += "Example:\n";
		str += "=================\n";
		str += "Stock Take Date: 2019-06-14\n";
		str += "SKU 100123 First Stock Take Time: 2019-06-14 16:40\n";
		str += "POS Quantity = Sales Quantity from 2019-06-14 00:00 until 2019-06-14 16:40";
		alert(str);
	},
	// function when user click on stock take icon
	show_st_qty_notify: function(){
		var str = "Stock Take Quantity = Cycle Count Total Quantity (Backend + App) + POS Qty.\n\n";
		alert(str);
	},
	// function when user click on re-open
	reopen_form: function(){
		if(!confirm('Are you sure?'))	return false;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		var params = {
			'a': 'ajax_reopen_wip',
			'branch_id': this.f['branch_id'].value,
			'id': this.f['id'].value
		};
		
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			asynchronous: false,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Reload
						location.reload(true);
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
				GLOBAL_MODULE.hide_wait_popup();
			}
		});
	},
	// function when user click on regenerate pos_qty
	regen_pos_qty_clicked: function(){
		if(!confirm('Are you sure?'))	return false;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		var params = {
			'a': 'ajax_regen_pos_qty',
			'branch_id': this.f['branch_id'].value,
			'id': this.f['id'].value,
		};
		
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			asynchronous: false,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Reload
						location.reload(true);
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
				GLOBAL_MODULE.hide_wait_popup();
			}
		});
	},
	// function when user click on send to stock take
	send_to_stock_take: function(){
		if(!confirm('Are you sure?'))	return false;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		var cc_id = this.f['id'].value;
		
		var params = {
			'a': 'ajax_send_to_stock_take',
			'branch_id': this.f['branch_id'].value,
			'id': cc_id,
		};
		
		if($('inp_zerolise_non_st').checked){
			params['zerolise_non_st'] = 1;
		}
		
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			asynchronous: false,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Reload
						document.location = phpself+'?t=send_st&id='+cc_id;
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
				GLOBAL_MODULE.hide_wait_popup();
			}
		});
	},
	// function when user click on recall from stock take
	recall_form: function(){
		if(!confirm('Are you sure?\n\n- This will Delete all related sku from Store Stock Take!'))	return false;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		var params = {
			'a': 'ajax_recall_stock_take',
			'branch_id': this.f['branch_id'].value,
			'id': this.f['id'].value,
		};
		
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			asynchronous: false,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Reload
						location.reload(true);
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
				GLOBAL_MODULE.hide_wait_popup();
			}
		});
	}
};

function add_autocomplete(){
	CC_SHEET.find_sku($('sku_item_id').value);
}

function add_grn_barcode_item(value)
{
	value = trim(value);
	if (value=='')
	{
		$('grn_barcode').select();
		$('grn_barcode').focus();
		return;
	}
	CC_SHEET.find_sku(0, {'find_by_grn_barcode': 1});
}

{/literal}
</script>

<h1>Cycle Count Sheet - {$form.doc_no}</h1>

<h3>Status:
	{if $form.sent_to_stock_take}
		Completed (Sent to Stock Take)
	{elseif $form.completed}
		Completed
	{elseif $form.wip}
		Working In Progress
	{else}
		Printed
	{/if}
</h3>
	
<div class="stdframe">
	<table class="report_table" style="background-color: #fff;" width="100%">
		<tr>
			<td class="col_header"><b>Propose Stock Take Date</b></td>
			<td>{$form.propose_st_date}</td>
			<td class="col_header"><b>Stock Take Person</b></td>
			<td>{$form.pic_username}</td>
		</tr>
		<tr>
			<td class="col_header"><b>Remark</b></td>
			<td colspan="3"><pre style="margin:0;">{$form.remark|escape}</pre></td>
		</tr>
		{if $form.wip}
			<tr>
				<td class="col_header"><b>Stock Take Date</b></td>
				<td>{$form.st_date}</td>
				<td class="col_header"><b>Stock Taked SKU</b></td>
				<td>{$form.st_sku_count} / {$form.estimate_sku_count} ({$form.st_sku_per}%)</td>
			</tr>
		{/if}
		{if $form.sent_to_stock_take}
			<tr>
				<td class="col_header"><b>Sent to Stock Take</b></td>
				<td>{$form.sent_to_stock_take_time}</td>
			</tr>
		{/if}
	</table>
</div>

<h3>SKU List ({$form.estimate_sku_count} sku) - <small><i>Generated at {$form.print_time}</i></small></h3>

<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="a" />
	<input type="hidden" name="branch_id" value="{$form.branch_id}" />
	<input type="hidden" name="id" value="{$form.id}" />
	
	<div>
		<b>Page: </b>
		<select name="sel_page" onChange="CC_SHEET.page_changed();">
			{section loop=$form.item_totalpage name=i}
				<option value="{$smarty.section.i.iteration}">{$smarty.section.i.iteration}</option>
			{/section}
		</select> of {$form.item_totalpage}
	</div><br />
	
	<div class="stdframe" id="div_item_list">
		{include file="admin.cycle_count.assignment.sheet.item_list.tpl"}
	</div>
	
	<br />
	<div style="background:#ddd;border:1px solid #999;">
		{include file='scan_barcode_autocomplete.tpl' need_hr_out_bottom=1 _add_value='Find'}
		{include file=sku_items_autocomplete.tpl _add_value='Find'}
		<span id="span_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	</div>
</form>

{if $form.completed and !$form.sent_to_stock_take and $sessioninfo.id eq $form.pic_user_id}
	<br />
	<div class="stdframe" style="border: 3px outset #012BFE;">
		
		<h3>POS Qty</h3>
		POS Qty was generated when cycle count mark as complete. If you have sales synced after cycle count complete, you need to regenerate the POS Qty.<br />
		<i>Generated Time: {$form.generated_pos_qty_time}</i><br />
		<input type="button" value="Regenerate POS Qty" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onClick="CC_SHEET.regen_pos_qty_clicked();" />
		<br />
		
		<h3>Send to Store Stock Take</h3>
		Send this Cycle Count to Store Stock Take to continue normal store procedures.<br />
		<input type="checkbox" name="zerolise_non_st" id="inp_zerolise_non_st" checked /> <label  for="inp_zerolise_non_st">Zerolise Non-Stock Take SKU</label ><br />
		<input type="button" value="Send to Store Stock Take" style="font:bold 20px Arial; background-color:#091; color:#fff;" onClick="CC_SHEET.send_to_stock_take();" />
	</div>
{/if}



<p id="p_submit_btn" align="center">
	{if $can_edit and !$form.wip}
		<input type="button" value="Regenerate SKU & Print" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="CC_SHEET.print_clicked(1);" />
	{/if}
	
	<input type="button" value="Print" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="CC_SHEET.print_clicked();" />
	
	{if $can_edit}
		{if !$form.wip}
			<input type="button" value="Start Now" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="CC_SHEET.start_clicked();" />
		{else}
			{if !$form.completed}
				<input type="button" value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="CC_SHEET.save_clicked();" />
				<input type="button" value="Mark as Complete" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="CC_SHEET.save_clicked(1);" />
			{/if}
		{/if}
	{else}
		{if $form.sent_to_stock_take}
			{if $sessioninfo.branch_id eq $form.st_branch_id and ($sessioninfo.level >= $config.doc_reset_level || $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ALLOW_RESET)}
				<input type="button" value="Re-call from Stock Take" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="CC_SHEET.recall_form();" />
			{/if}
		{else}
			{if $form.completed and $sessioninfo.branch_id eq $form.st_branch_id and ($sessioninfo.level >= $config.doc_reset_level || $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ALLOW_RESET)}
				<input type="button" value="Re-open to WIP" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="CC_SHEET.reopen_form();" />
			{/if}
		{/if}
	{/if}
	
	<input type="button" value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='{$smarty.server.PHP_SELF}'" />
</p>

<script>
	CC_SHEET.initialize();
</script>
{include file='footer.tpl'}