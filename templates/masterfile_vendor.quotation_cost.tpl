{*
06/26/20 4:00 PM Sheila
- Updated button css
*}

{include file='header.tpl'}

<style>
{literal}
.div_vendor_quotation_cost_container{
	float:left;
	padding-right:10px;
}
.span_vendor_branch{
	background-color: #060;
	color: #fff;
	padding: 0 3px;
	font-weight: bold;
}
.span_vendor_quotation_cost{
	color: blue;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';

{literal}
var VENDOR_QUOTATION_COST = {
	f_vendor: undefined,
	f_a: undefined,
	p: 0,
	initialise: function(){
		this.f_vendor = document.f_vendor;
		
		// init vendor autocomplete
		VENDOR_AUTOCOMPLETE_MAIN.initialize({}, refresh_vendor);
		
		if(document.f_a){
			this.f_a = document.f_a;
						
			// initial the sku autocomplete
			reset_sku_autocomplete();
			
			new Draggable('div_sku_quotation_cost_dialog',{ handle: 'div_sku_quotation_cost_dialog_header'});
		}
	},
	// function when user click show vendor
	refresh_vendor: function(vendor_id){
		// no vendor selected
		if(vendor_id<=0)	return;
		
		this.f_vendor.submit();
	},
	// function when user click find sku
	find_sku: function(){
		this.p = 0;
		this.filter_sku();
	},
	// core function to reload sku list
	filter_sku: function(){
		var sku_item_id = $('sku_item_id').value;
		//if(!sku_item_id)	return false;
		
		$('span_loading_sku_list').show();
		$('div_sku_list').update('');
		var THIS = this;
		
		new Ajax.Request(phpself, {
			parameters:{
				a: 'ajax_reload_sku_list',
				sku_item_id: sku_item_id,
				vendor_id: document.f_a['vendor_id'].value,
				p: this.p
			},
			method: 'post',
			onComplete: function(msg){
				// hide the loading icon
			    $('span_loading_sku_list').hide();
			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						$('div_sku_list').update(ret['html']);
						
						// SKU not found, ask to add a new one
	                    if(sku_item_id>0 && !ret['found']){
							if(confirm('SKU not found in Quotation Cost List, do you want to add it?')){
								SKU_QUOTATION_COST_DIALOG.open(sku_item_id);
							}
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
			}
		});
	},
	// function when user click reload all sku
	reload_all_sku: function(){
		this.p = 0;
		reset_sku_autocomplete();
		this.filter_sku();
	},
	// function when user click on view quotation cost history
	view_quotation_cost_history: function(sid, bid){
		if(!sid)	return false;
		if(!bid)	bid = 0;
		
		$('div_sku_quotation_cost_history_dialog_content').update(_loading_);
		
		curtain(true);
		center_div($('div_sku_quotation_cost_history_dialog').show());
		
		new Ajax.Request(phpself, {
			parameters: {
				a: 'ajax_load_quotation_cost_history',
				sid: sid,
				bid: bid,
				vendor_id: document.f_a['vendor_id'].value
			},
			method: 'post',
			onComplete: function(msg){
				
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						$('div_sku_quotation_cost_history_dialog_content').update(ret['html']);
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
	page_changed: function(){
		this.p = $('sel_page').value;
		this.filter_sku();
	}
}

var SKU_QUOTATION_COST_DIALOG = {
	open: function(sku_item_id, bid){
		if(sku_item_id<=0)	return false;
		if(!bid)	bid = 0;
		
		$('div_sku_quotation_cost_dialog_content').update(_loading_);
		curtain(true , "curtain2");
		center_div($('div_sku_quotation_cost_dialog').show());
		
		new Ajax.Request(phpself, {
			parameters: {
				a: 'ajax_open_sku',
				vendor_id: document.f_a['vendor_id'].value,
				sku_item_id: sku_item_id,
				bid: bid
			},
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						$('div_sku_quotation_cost_dialog_content').update(ret['html']);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    //alert(err_msg);
				$('div_sku_quotation_cost_dialog_content').update(err_msg);
			}
		});
		
	},
	close: function(){
		default_curtain_clicked();
		curtain(false, 'curtain2');
	},
	// function when user change quotation cost
	quotation_cost_changed: function(inp){
		inp.value = float(inp.value);
		if(inp.value > 0){
			inp.value = round(inp.value, global_cost_decimal_points);
		}else{
			inp.value = '';
		}
	},
	// function when user click edit all branch quotation cost
	edit_all_branch_quotation_cost: function(){
		var cost = prompt("Please key in Quotation Cost, this will apply to all branch.");
		
		if(cost == undefined)	return; // escape
		var cost = float(round(cost, global_cost_decimal_points));
		
		$(document.f_sku).getElementsBySelector("input.quotation_cost").each(function(inp){
			inp.value = cost <=0 ? '' : float(round(cost, global_cost_decimal_points));
		});
	},
	// function when user click on update
	update_clicked: function(){
		if(!confirm('Are you sure?'))	return false;
		
		this.mark_updating(true);
		var THIS = this;
		
		new Ajax.Request(phpself, {
			parameters: $(document.f_sku).serialize(),
			method: 'post',
			onComplete: function(msg){
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				THIS.mark_updating(false);
				
				try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Updated, refresh sku list
						THIS.close();
						VENDOR_QUOTATION_COST.filter_sku();
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
	// toggle updating status
	mark_updating: function(is_updating){		
		$$('#div_all_action_btn input').each(function(inp){
			inp.disabled = is_updating;
		});
		$('btn_update').value = is_updating ? "Updating...":  "Update";
	}
}

function refresh_vendor(vendor_id){
	VENDOR_QUOTATION_COST.refresh_vendor(vendor_id);
}

{/literal}
</script>

{* SKU Quotation Cost Dialog *}
<div id="div_sku_quotation_cost_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:750px;height:400px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_sku_quotation_cost_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">SKU Quotation Cost</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="SKU_QUOTATION_COST_DIALOG.close();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sku_quotation_cost_dialog_content" style="padding:2px;">
		
	</div>
</div>

{* SKU Quotation Cost History *}
<div id="div_sku_quotation_cost_history_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:500px;height:400px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_sku_quotation_cost_history_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">SKU Quotation Cost History</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sku_quotation_cost_history_dialog_content" style="padding:2px;height:90%;overflow-y:auto;">
		
	</div>
</div>


<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{if $sessioninfo.privilege.MST_VENDOR_IMPORT_QUOTATION_COST}
	<div class="card mx-3">
		<div class="card-body"><img src="ui/table_row_insert.png" align="absmiddle">&nbsp;<a href="masterfile_vendor.import_quotation_cost.php">Import Quotation Cost</a></img></div>
	</div>
{/if}

{if $err}
	<div class="alert alert-danger rounded mx-3">
		The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
	</div>
{/if}

<br />

<div class="card mx-3">
	<div class="card-body">
		<form name="f_vendor" method="post" onSubmit="return false;" class="stdframe"  action="{$smarty.server.PHP_SELF}">
			{include file='vendor_autocomplete.tpl' add_value="Show" parent_form="document.f_vendor"}
		</form>
	</div>
</div>

{if $form}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$form.info.code} - {$form.info.description}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<form name="f_a" method="post" onSubmit="return false;" class="stdframe">
	<input type="hidden" name="vendor_id" value="{$form.vendor_id}" />
	<div class="card mx-3">
		<div class="card-body">
			{include file='sku_items_autocomplete.tpl' no_add_button=1}
			<input class="btn btn-primary" type="button" value="Reload All SKU" onClick="VENDOR_QUOTATION_COST.reload_all_sku();" />
	<input class="btn btn-info" type="button" value="Find" onClick="VENDOR_QUOTATION_COST.find_sku();" />
		</div>
	</div>
	
	
</form>
<br />

<span id="span_loading_sku_list" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
<div id="div_sku_list">
	{include file='masterfile_vendor.quotation_cost.sku_list.tpl'}
</div>
{/if}


<script>
VENDOR_QUOTATION_COST.initialise();
</script>

{include file='footer.tpl'}