{*
10/25/2019 10:26 AM Andy
- Rename to word from "Entry" to "Credit".

06/29/2020 05:55 PM Sheila
- Updated button css.
*}

{include file='header.tpl'}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var can_edit = int('{$can_edit}');

{literal}
var MEMBERSHIP_PACKAGE = {
	f: undefined,
	initialise: function(){
		this.f = document.f_a;
		
		if(can_edit){
			SEARCH_SKU_DIALOG.initialise();
			
			// Init Calendar
			this.init_calendar();
			
			// Check Arrow
			this.check_update_list_arrow();
			
			//refresh the session each 25 minutes to avoid timeout when user take long time
			new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
		}else{
			// Disable Edit
			Form.disable(this.f);
		}
	},
	// core function to init calendar
	init_calendar: function(){
		// Valid From
		Calendar.setup({
			inputField     :    "inp_valid_from",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_valid_from",
			align          :    "Bl",
			singleClick    :    true
		});
		
		// Valid To
		Calendar.setup({
			inputField     :    "inp_valid_to",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_valid_to",
			align          :    "Bl",
			singleClick    :    true
		});
	},
	// function when user changed total entry earn
	total_entry_earn_changed: function(){
		var v = int(this.f['total_entry_earn'].value);
		if(v<0)	v = 0;
		this.f['total_entry_earn'].value = v;
	},
	// function when user click on button add item
	add_item_clicked: function(){
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		this.f['a'].value = 'ajax_add_item';
		var params = $(this.f).serialize();
		
		var THIS = this;
		
		//alert(params);return;
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				GLOBAL_MODULE.hide_wait_popup();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						new Insertion.Bottom($('tbody_package_items_list'), ret['html']);
						THIS.check_update_list_arrow();
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
	// function to check arrow key
	check_update_list_arrow: function(){
		var tr_item_list = $$('#tbody_package_items_list tr.tr_package_items');
		
		for(var i=0,len=tr_item_list.length; i<len; i++){
			var tr_package_items = tr_item_list[i];
			
			var img_move_up = $(tr_package_items).getElementsBySelector('img.img_move_up')[0];
			var img_move_down = $(tr_package_items).getElementsBySelector('img.img_move_down')[0];
			
			$(img_move_up).style.visibility = i == 0 ? 'hidden' : '';
			$(img_move_down).style.visibility = i == len-1 ? 'hidden' : '';
		}
	},
	// function when user click on move up / down
	move_item: function(item_guid, direction){
		var curr_tr_item = $('tr_package_items-'+item_guid);
		var swap_tr_item;
		if(direction == 'up'){
			swap_tr_item = $(curr_tr_item).previousElementSibling;
		}else{
			swap_tr_item = $(curr_tr_item).nextElementSibling ;
		}
		
		swap_ele(curr_tr_item, swap_tr_item);
		this.check_update_list_arrow();
	},
	// function when user click on remove item
	remove_item: function(item_guid){
		if(!confirm('Are you sure?'))	return;
		
		$('tr_package_items-'+item_guid).remove();
		this.check_update_list_arrow();
	},
	// function to check form
	validate_form: function(){
		if(!check_required_field(this.f))	return false;
		
		// Allowed Branch
		var chx_allowed_branches_count = 0;
		$$('#tbl_allowed_branches input.chx_allowed_branches').each(function(inp){
			if(inp.checked)	chx_allowed_branches_count++;
		});
		if(chx_allowed_branches_count<=0){
			alert('Please select Allowed Branches');
			return false;
		}
		
		// Total Entry Earn
		var total_entry_earn = int(this.f['total_entry_earn'].value);
		if(total_entry_earn<=0){
			alert('Total Entry Earn must not be zero');
			this.f['total_entry_earn'].select();
			return false;
		}
		
		// Check Package Items
		var THIS = this;
		var item_count = 0;
		$$('#tbody_package_items_list tr.tr_package_items').each(function(tr){
			item_count++;
			
			var item_guid = THIS.get_item_guid_by_ele(tr);
			//alert(item_guid);
		});
		if(item_count<=0){
			alert("Please add at least one item.");
			return false;
		}
		
		if(!this.f['link_sku_item_id'].value){
			alert("Please select the Link SKU.");
			return false;
		}
		
		return true;
	},
	// function to get item id by element
	get_item_guid_by_ele: function(ele){
		var tr_package_items = $(ele).closest("tr.tr_package_items");
		if(!tr_package_items)	return 0;
		
		var item_guid = $(tr_package_items).readAttribute('item_guid');
		return item_guid;
	},
	// function when users click on save
	save_clicked: function(is_confirm){
		if(!is_confirm)	is_confirm = 0;
		
		if(!this.validate_form())	return false;
		
		if(is_confirm){
			if(!confirm('Are you sure?'))	return false;
		}
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		this.f['a'].value = 'ajax_save';
		var params = $(this.f).serialize();
		if(is_confirm)	params += '&is_confirm=1';
		
		var THIS = this;
		
		//alert(params);return;
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['doc_no']){ // success
						if(is_confirm){
							// Go back to main page
							document.location = phpself+'?t=confirmed&doc_no='+ret['doc_no'];
						}else{
							document.location = phpself+'?t=saved&doc_no='+ret['doc_no'];
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
	// function when user selected linked sku
	select_link_sku: function(sku_item_id){
		if(!sku_item_id){
			alert('No SKU ITEM ID Found');
			return;
		}
		
		this.f['link_sku_item_id'].value = sku_item_id;
				
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		this.f['a'].value = 'ajax_load_sku_info';
		var params = $(this.f).serialize();
		
		var THIS = this;
		
		//alert(params);return;
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				GLOBAL_MODULE.hide_wait_popup();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						$('div_linked_sku_info').update(ret['html']);
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
	// function when user changed allowed branch
	allowed_branch_changed: function(){
		// Get table
		var tbl_selling_by_branch = $('tbl_selling_by_branch');
		if(!tbl_selling_by_branch)	return;
		
		// Hide all branch first
		$(tbl_selling_by_branch).getElementsBySelector('tr.tr_selling_by_branch').invoke('hide');
		
		// Loop branch checkbox
		$$('#tbl_allowed_branches input.chx_allowed_branches').each(function(inp){
			if(!inp.checked)	return;	// branch not selected
			
			// show the tr
			var tr_selling_by_branch = $('tr_selling_by_branch-'+inp.value);
			if(tr_selling_by_branch){
				$(tr_selling_by_branch).show();
			}
		});
	},
	// function when user click on cancel
	cancel_form: function(){
		this.f['cancel_reason'].value = '';
		var p = prompt('Enter reason to cancel:');
		if (p.trim()=='' || p==null) return;
		this.f['cancel_reason'].value = p;
		if (!confirm('Cancel this Package?')){
			return false;
		}
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		var THIS = this;
		if(!can_edit)Form.enable(this.f);
		var params = $(this.f).serialize()+'&a=ajax_cancel_package';
		if(!can_edit)Form.disable(this.f);
				
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
	                if(ret['ok'] && ret['doc_no']){ // success
						// Redirect to main page
						document.location = phpself+'?t=cancelled&doc_no='+ret['doc_no'];						
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
}

var SEARCH_SKU_DIALOG = {
	initialise: function(){
		this.f = document.f_search_sku;
		reset_sku_autocomplete();
		new Draggable('div_search_sku_dialog',{ handle: 'div_search_sku_dialog_header'});
	},
	open: function(){

		// Clear Form value
		this.f.reset();
		
		// Show Dialog
		//curtain(true);
		//center_div($('div_search_sku_dialog').show());
		jQuery('#div_search_sku_dialog').modal('show');
		this.focus_search_field();
		
	},
	close: function(){
		default_curtain_clicked();
	},
	// function when user click on select sku
	select_sku_clicked: function(){
		var sku_item_id = $('sku_item_id').value;
		if(!sku_item_id){
			alert("Please search and select a SKU.");
			this.focus_search_field();
			return;
		}
		this.close();
		
		// select as linked sku
		MEMBERSHIP_PACKAGE.select_link_sku(sku_item_id);
	},
	focus_search_field: function(){
		$('autocomplete_sku').select();
	}
}

function add_autocomplete(){
	SEARCH_SKU_DIALOG.select_sku_clicked();
}

{/literal}
</script>

{* Search SKU Dialog *}

<!--popuup starts-->
<div class="modal" id="div_search_sku_dialog" >
    <div class="modal-dialog modal modal-dialog-centered " role="document">
        <div class="modal-content modal-content-demo" >
            <div class="modal-header bg-danger" id="div_search_sku_dialog_header">
                <h6 class="modal-title text-white">Search SKU</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true" class="text-white">&times;</span></button>
				<div style="clear:both;"></div>
			</div>
            <div class="modal-body" id="div_search_sku_dialog_content" >
                <form name="f_search_sku">			
					<p align="center">
						{include file='sku_items_autocomplete.tpl' parent_form='document.f_search_sku' _add_value='Select'}
					</p>
				</form>
            </div>
        </div>
    </div>
</div>
<!--popup ends-->

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">Package ({if $form.id}{$form.doc_no}{else}NEW{/if})</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h5 class="content-title mb-0 my-auto ml-4 text-primary">
				Status:
	{if $form.active}
		{if $form.status eq 1}
			Confirmed
		{else}
			Draft
		{/if}
	{else}
		Cancelled
	{/if}
			</h5><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" method="post" ENCTYPE="multipart/form-data">
			<input type="hidden" name="a" value="save" />
			<input type="hidden" name="branch_id" value="{$form.branch_id}" />
			<input type="hidden" name="id" value="{$form.id}" />
			<input type="hidden" name="unique_id" value="{$form.unique_id}" />
			<input type="hidden" name="doc_no" value="{$form.doc_no}" />
			<input type="hidden" name="cancel_reason" />
			
			<div class="stdframe" style="background:#fff">
				<h4>General Information</h4>
				
				<div class="table-responsive">
					<table border="0" cellspacing="0" cellpadding="4">
						{* Title *}
						<tr>
							<th width="150" align="left" class="form-label">Title</th>
							<td>
								<input  type="text" name="title" maxlength="100" style="width:300px;" value="{$form.title|escape:html}" title="Title" class="required form-control" />
							</td>
						</tr>
						
						{* Valid Date *}
						<tr>
							<th align="left" class="form-label">Valid Date</th>
							<td>
							<div class="form-inline">
								<input  type="text" name="valid_from" id="inp_valid_from" size="10" maxlength="10"  value="{$form.valid_from|escape:html}" title="Valid Date From" class="required form-control" />
								{if $can_edit}
									&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_valid_from" style="cursor: pointer;" title="Select Date" />
								{/if}
							
								<b class="form-label">&nbsp;to&nbsp;</b>
								<input type="text" name="valid_to" id="inp_valid_to" size="10" maxlength="10"  value="{$form.valid_to|escape:html}" title="Valid Date To" class="required form-control" />
								{if $can_edit}
								&nbsp;	<img align="absmiddle" src="ui/calendar.gif" id="img_valid_to" style="cursor: pointer;" title="Select Date" />
								{/if}
							</div>	
							</td>
						</tr>
						
						{* Remark *}
						<tr>
							<th align="left" valign="top" class="form-label">Remark</th>
							<td>
								<textarea class="form-control" name="remark" style="width:300px;height:100px;">{$form.remark|escape:html}</textarea>
							</td>
						</tr>
						
						{* Branches *}
						<tr>
							<th align="left" valign="top" class="form-label">Allowed Branches</th>
							<td>
								<table class="small" border="0" id="tbl_allowed_branches">
									{if $form.branch_id eq 1}
										{* Created by HQ *}
										{foreach from=$branches_list key=bid item=b}
											<tr>
												<td valign="top">
													<input type="checkbox" class="chx_allowed_branches" name="allowed_branches[{$bid}]" value="{$bid}" {if $form.allowed_branches.$bid}checked {/if} onChange="MEMBERSHIP_PACKAGE.allowed_branch_changed();" /> {$b.code}
												</td>
											</tr>
										{/foreach}
									{else}
										{* Created by Branch *}
										{assign var=bid value=$form.branch_id}
										<tr>
											<td valign="top">
												<input type="checkbox" class="chx_allowed_branches" name="allowed_branches[{$bid}]" value="{$bid}" checked style="display:none;" /> {$branches_list.$bid.code}
											</td>
										</tr>
									{/if}
								</table>
							</td>
						</tr>
						
						{* Linked SKU *}
						<tr>
							<th align="left" valign="top" class="form-label">Linked SKU</th>
							<td>
								<input class="form-control" type="hidden" name="link_sku_item_id" value="{$form.link_sku_item_id}" />
								
								{if $can_edit}
									<input style="margin-bottom: 5px;" class="btn btn-sm btn-dark" type="button" value="Edit SKU" onClick="SEARCH_SKU_DIALOG.open();" />
								{/if}
								<div id="div_linked_sku_info">
									{if $form.linked_sku_info}
										{include file='membership.package.setup.open.linked_sku_info.tpl' linked_sku_info=$form.linked_sku_info}
									{/if}
								</div>
								<div >
									<div class="alert alert-danger rounded">
										<img src="ui/messages.gif" align="absmiddle" /> Alert!!!<br />
									<ul>
										{*<li>Once Package is confirmed, this SKU will become unable to sell at POS Counter until this Package enabled the selling.</li>*}
										<li>The SKU will only be able to sell at the allowed branches.</li>
										<li>One SKU can only be used by one Package.</li>
									</ul>
									</div>
								</div>
							</td>
						</tr>
						
						{* Total Credit Earn *}
						<tr>
							<th align="left" class="form-label">Total Credit Earn</th>
							<td>
								<input class="form-control" type="text" name="total_entry_earn" value="{$form.total_entry_earn|default:0}" style="width:50px;" maxlength="5" onChange="MEMBERSHIP_PACKAGE.total_entry_earn_changed();" />
							</td>
						</tr>
					</table>
				</div>
			</div>
			
		
		</div>
	</div>
			<div class="card mx-3">
				<div class="card-body">
					<h4>Redemption Items</h4>
			<div class="stdframe" >
				<div class="table-responsive">
					<table class="report_table" width="100%">
						<thead class="bg-gray-100">
							<tr class="header">
								{if $can_edit}
									<th width="80">&nbsp;</th>
								{/if}
								<th width="210">Title</th>
								<th>Description</th>
								<th width="400">Remark</th>
								<th width="90">Credit Needed</th>
								<th width="90">Max Redeem Count [<a href="javascript:void(alert('0 = No limit'))">?</a>]</th>
							</tr>
						</thead>
						
						<tbody class="fs-08" id="tbody_package_items_list">
							{foreach from=$form.package_items key=item_guid item=item}
								{include file='membership.package.setup.open.item.tpl'}
							{/foreach}
						</tbody>
					</table>
				</div>
				
				{if $can_edit}
					<p>
						<input type="button" class="btn btn-primary mt-2" value="Add Item" onClick="MEMBERSHIP_PACKAGE.add_item_clicked();" />
					</p>
				{/if}
			</div>
		</form>
				</div>
			</div>


<p id="p_submit_btn" align="center">
	{if $can_edit}
		<input class="btn btn-success" type="button" value="Save" onclick="MEMBERSHIP_PACKAGE.save_clicked();" />
		<input class="btn btn-primary" type="button" value="Confirm" onclick="MEMBERSHIP_PACKAGE.save_clicked(1);" />
	{else}
		{if $form.active and $form.status eq 1}
			{if $sessioninfo.branch_id eq $form.branch_id and ($sessioninfo.level >= $config.doc_reset_level || $sessioninfo.privilege.MEMBERSHIP_PACK_CANCEL)}
				<input class="btn btn-warning" type="button" value="Cancel" color:#fff;" onclick="MEMBERSHIP_PACKAGE.cancel_form();" />
			{/if}
		{/if}
	{/if}
	<input class="btn btn-danger" type="button" value="Close" onclick="document.location='{$smarty.server.PHP_SELF}'" />
</p>


<script>MEMBERSHIP_PACKAGE.initialise();</script>
{include file='footer.tpl'}