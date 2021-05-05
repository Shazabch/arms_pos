{include file='header.tpl'}

<style>
{literal}
.div_debtor_price_container{
	float:left;
	padding-right:10px;
}
.span_debtor_branch{
	background-color: #060;
	color: #fff;
	padding: 0 3px;
	font-weight: bold;
}
.span_debtor_price{
	color: blue;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var DEBTOR_PRICE = {
	f_debtor: undefined,
	f_a: undefined,
	p: 0,
	initialise: function(){
		this.f_debtor = document.f_debtor;
		
		// init debtor autocomplete
		DEBTOR_AUTOCOMPLETE_MAIN.initialize({}, refresh_debtor);
		
		if(document.f_a){
			this.f_a = document.f_a;
						
			// initial the sku autocomplete
			reset_sku_autocomplete();
			
			new Draggable('div_sku_price_dialog',{ handle: 'div_sku_price_dialog_header'});
		}
	},
	// function when user click show debtor
	refresh_debtor: function(debtor_id){
		// no debtor selected
		if(debtor_id<=0)	return;
		
		this.f_debtor.submit();
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
				debtor_id: document.f_a['debtor_id'].value,
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
							if(confirm('SKU not found in Debtor Price List, do you want to add it?')){
								SKU_PRICE_DIALOG.open(sku_item_id);
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
	// function when user click on view price history
	view_price_history: function(sid, bid){
		if(!sid)	return false;
		if(!bid)	bid = 0;
		
		$('div_sku_price_history_dialog_content').update(_loading_);
		
		curtain(true);
		center_div($('div_sku_price_history_dialog').show());
		
		new Ajax.Request(phpself, {
			parameters: {
				a: 'ajax_load_price_history',
				sid: sid,
				bid: bid,
				debtor_id: document.f_a['debtor_id'].value
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
						$('div_sku_price_history_dialog_content').update(ret['html']);
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

var SKU_PRICE_DIALOG = {
	open: function(sku_item_id, bid){
		if(sku_item_id<=0)	return false;
		if(!bid)	bid = 0;
		
		$('div_sku_price_dialog_content').update(_loading_);
		curtain(true , "curtain2");
		center_div($('div_sku_price_dialog').show());
		
		new Ajax.Request(phpself, {
			parameters: {
				a: 'ajax_open_sku',
				debtor_id: document.f_a['debtor_id'].value,
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
						$('div_sku_price_dialog_content').update(ret['html']);
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
				$('div_sku_price_dialog_content').update(err_msg);
			}
		});
		
	},
	close: function(){
		default_curtain_clicked();
		curtain(false, 'curtain2');
	},
	// function when user change debtor price
	debtor_price_changed: function(inp){
		inp.value = float(inp.value);
		if(inp.value > 0){
			inp.value = round2(inp.value);
		}else{
			inp.value = '';
		}
	},
	// function when user click edit all branch debtor price
	edit_all_branch_debtor_price: function(){
		var price = prompt("Please key in Debtor Price, this will apply to all branch.");
		
		if(price == undefined)	return; // escape
		var price = float(round2(price));
		
		$(document.f_sku).getElementsBySelector("input.debtor_price").each(function(inp){
			inp.value = price<=0 ? '' : round2(price);
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
						DEBTOR_PRICE.filter_sku();
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

function refresh_debtor(debtor_id){
	DEBTOR_PRICE.refresh_debtor(debtor_id);
}

{/literal}
</script>

{* SKU Price Dialog *}
<div id="div_sku_price_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:750px;height:400px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_sku_price_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">SKU Price</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="SKU_PRICE_DIALOG.close();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sku_price_dialog_content" style="padding:2px;">
		
	</div>
</div>

{* SKU Price History *}
<div id="div_sku_price_history_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:500px;height:400px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_sku_price_history_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">SKU Price History</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sku_price_history_dialog_content" style="padding:2px;height:90%;overflow-y:auto;">
		
	</div>
</div>


<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<br />

<form name="f_debtor" method="post" onSubmit="return false;" class="stdframe" style="background-color:#fff;" action="{$smarty.server.PHP_SELF}">
	{include file='debtor_autocomplete.tpl' add_value="Show" parent_form="document.f_debtor"}
</form>

{if $form}
<br />
<h3>{$form.info.code} - {$form.info.description}</h3>

<form name="f_a" method="post" onSubmit="return false;" class="stdframe">
	<input type="hidden" name="debtor_id" value="{$form.debtor_id}" />
	{include file='sku_items_autocomplete.tpl' no_add_button=1}
	
	<input type="button" value="Reload All SKU" onClick="DEBTOR_PRICE.reload_all_sku();" />
	<input type="button" value="Find" onClick="DEBTOR_PRICE.find_sku();" />
</form>
<br />

<span id="span_loading_sku_list" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
<div id="div_sku_list">
	{include file='masterfile_debtor_price.sku_list.tpl'}
</div>
{/if}


<script>
DEBTOR_PRICE.initialise();
</script>

{include file='footer.tpl'}