{*
1/21/2020 2:48 PM William
- Enhanced to show column sku photo and weight.

3/2/2020 2:22 PM William
- Enhanced to show only first 5 line of column "Marketplace Description".
*}

{include file='header.tpl'}

<style>
{literal}
div.div_mandatory_missing{
	text-align: center;
	background-color: #FCF863;
	width: 50px;
	padding: 5px;
	margin: auto;
	border: 1px solid red;
}
.div_marketplace_desc{
	overflow: hidden;
	text-overflow: ellipsis;
	display: -webkit-box;
	-webkit-box-orient: vertical;
	-webkit-line-clamp: 5;
}
{/literal}
</style>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var MKTPLACE_MANAGE_SKU = {
	f_a: undefined,
	f_b: undefined,
	checked_si_list: {}, // to be used for record what user had clicked on the SKU item
	initialise: function(){
		var THIS = this;
		this.f_a = document.f_a;
		this.f_b = document.f_b;
	},
	
	ajax_add_sku_items: function(){
	
		// attach all the sku items and add into sku_code_list hidden field
		toggle_select_all_opt(this.f_a['sku_code_list[]'], true);
		
		// show errors if no sku item were added into the list
		if(!this.f_a['sku_code_list[]'].value){
			alert("Please add a SKU Item to proceed");
			return;
		}
		
		// show confirm msg
		if(!confirm("Are you sure want to proceed add SKU item(s)?")){
			return;
		}
		
		// disable the add sku item button
		$('add_si_btn').disabled = true;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		this.f_a['a'].value = 'ajax_add_sku_items';
		var params = $(this.f_a).serialize();
		
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			asynchronous: false,
			onComplete: function(msg){
				// enable the add sku item button
				$('add_si_btn').disabled = false;
				
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						if(ret['added'] == true){
							//remove insert success sku item
							var sku_item_code_list = ret['sku_item_code_list'];
							var len_sku_list = document.getElementById("sku_code_list");
							for(var i = 0; i < sku_item_code_list.length; i++ ){
								for (var n=0; n < len_sku_list.length; n++) {
									if(len_sku_list.options[n].value == sku_item_code_list[i]){
										len_sku_list.remove(n);
									}
								}
							}
							alert("Successfully added SKU item(s) for marketplace.");
						}
						$('div_mktplace_sku_item_list').update(ret['html']);
						
						GLOBAL_MODULE.hide_wait_popup();
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
	
	ajax_reload_sku_items: function(){
		// disable the add sku item button
		$('refresh_btn').disabled = true;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();

		new Ajax.Request(phpself, {
			parameters:{
				a: 'ajax_reload_si_list',
				status: $('status').value,
			},
			method: 'post',
			onComplete: function(msg){
				// hide the loading icon
				$('refresh_btn').disabled = false;
			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						$('div_mktplace_sku_item_list').update(ret['html']);
						
						// hide wait popup
						GLOBAL_MODULE.hide_wait_popup();
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
				// hide wait popup
				GLOBAL_MODULE.hide_wait_popup();
			}
		});
	},
	
	si_checkbox_clicked: function(sid){
		if(!sid){
			alert('Invalid SKU ITEM ID');
			return false;
		}
		
		// Get the Element
		var chkbox_si = this.f_b['chk_si_list['+sid+']'];
		
		// Construct Item Key
		var si_key = 'chk_si_list['+sid+']';
		
		// Update checked SKU Item List
		if(chkbox_si.checked == true){
			this.checked_si_list[si_key] = 1;
		}else{
			delete this.checked_si_list[si_key];
		}
	},
	
	// when user check/uncheck all sku items
	check_all_si: function(obj){
		var si_checkbox_list = $$('#tbl_si_list .si_checkbox');
		
		// Loop Item Row
		for(var i=0,len=si_checkbox_list.length; i < len; i++){
			if(obj.checked == true){
				$(si_checkbox_list[i]).checked = true;
			}else{
				$(si_checkbox_list[i]).checked = false;
			}
			
			// result will return sku_item_id
			var sid = this.get_item_id_by_obj(si_checkbox_list[i]);
			
			// store the checkbox status
			this.si_checkbox_clicked(sid);
		}
	},
	
	// Core function to get item_id
	get_item_id_by_obj: function(ele){
		var parent_ele = ele;

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

		var sku_item_id = parent_ele.id.split('-')[1];
		return sku_item_id;
	},
	
	active_clicked: function(sid, active){
		// prompt error msg if SKU item ID not found
		if(sid==0 || sid==""){
			alert("Invalid SKU ITEM ID");
			return;
		}
		
		// msg for activate / deactivate
		if(active) var status_desc = "activate";
		else var status_desc = "deactivate";
		
		if(sid == -1){
			// re-insert the sku item key
			//this.check_all_si(this.f_b['chk_all_si']);
			
			// prompt err msg as if no item were selected
			if(Object.keys(this.checked_si_list).length == 0){
				alert("Please select a SKU item to "+status_desc);
				return;
			}
		}
		
		if(!confirm("Are you sure want to "+status_desc+" the selected SKU Item(s)?")) return;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		this.f_b['a'].value = 'ajax_active_changed';
		var params = $(this.f_b).serialize();
		params += '&sku_item_id='+sid+'&active='+active+'&status='+$('status').value;

		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				// hide the loading icon
				$('refresh_btn').disabled = false;
			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						$('div_mktplace_sku_item_list').update(ret['html']);
						THIS.checked_si_list = {};
						
						// hide wait popup
						GLOBAL_MODULE.hide_wait_popup();
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
				// hide wait popup
				GLOBAL_MODULE.hide_wait_popup();
			}
		});
	}
}
{/literal}
</script>
{include file='shared_sku_photo.script.tpl'}
<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<form name="f_a" method="post" class="stdframe" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_add_sku_items" />
	<p>
		{include file="sku_items_autocomplete_multiple_add2.tpl"}
		<label><input type="checkbox" name="add_parent_child_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>Add SKU with Parent & Child</b></label>
	</p>
	<input type="button" name="add_si_btn" id="add_si_btn" value="Add SKU Item(s)" onclick="MKTPLACE_MANAGE_SKU.ajax_add_sku_items();" />
</form>
<br />
					
<form name="f_b" method="post" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_active_changed" />
	<div id="div_mktplace_sku_item_list">
		{include file="marketplace.manage_sku.items.tpl"}
	</div>
</form>

<script>
MKTPLACE_MANAGE_SKU.initialise();
</script>

{include file='footer.tpl'}
