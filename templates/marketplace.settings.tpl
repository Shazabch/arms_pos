{*
3/16/2020 5:48 PM Andy
- Added Marketplace DO Owner Settings.
*}

{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var MKT_SETTINGS = {
	f: undefined,
	initialise: function(){
		
		this.f = document.f_a;
	},
	// function when user click on button save
	save_clicked: function(){
		this.save_data();
	},
	// function to validate form data
	validate_data: function(){
		// check all required fields
		if(!check_required_field(this.f))	return false;		
		
		// DO User
		var do_user_id = int(this.f['data[normal_settings][do_user_id]'].value);
		if(do_user_id<=0){
			alert('Please Select Marketplace DO Owner');
			this.f['data[normal_settings][do_user_id]'].focus();
			return false;
		}
		return true;
	},
	// core function to save data
	save_data: function(){
		// check form
		if(!this.validate_data())	return false;
		
		var params = $(this.f).serialize();
		var btn_save = $('btn_save');
		btn_save.value = 'Saving...';
		btn_save.disabled = true;
		
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    
				btn_save.value = 'Save';
				btn_save.disabled = false;
			    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						alert('Save successfully!');
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
	}
};

{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_save_settings" />
	
	<table class="report_table">
		<tr class="header">
			<th>Setting Name</th>
			<th>Value</th>
			<th>&nbsp;</th>
			<th>Description</th>
		</tr>
		
		{* Shipping Item Code *}
		<tr valign="top">
			<td>Shipping Item Code</td>
			<td>
				<input type="text" name="data[normal_settings][shipping_item_code]" value="{$data.normal_settings.shipping_item_code}" class="required" title="Shipping Item Code" />
			</td>
			<td><img src="ui/rq.gif" /></td>
			<td>
				<ul>
					<li>If key in the actual SKU ARMS Code, system will add that sku into DO when there are shipping fee.</li>
					<li>If it is not actual ARMS Code, system will add the shipping fee as Open Item.</li>
				<ul>
			</td>
		</tr>
		
		{* Shipping Item Description *}
		<tr valign="top">
			<td>Shipping Item Description</td>
			<td>
				<input type="text" name="data[normal_settings][shipping_item_desc]" value="{$data.normal_settings.shipping_item_desc|default:'Shipping Fee'}" class="required" title="Shipping Item Description" />
			</td>
			<td><img src="ui/rq.gif" /></td>
			<td>
				<ul>
					<li>If above "Shipping Item Code" use the actual SKU ARMS Code, this setting will be ignored.</li>
					<li>Else, system will use this as the shipping item description.</li>
				<ul>
			</td>
		</tr>
		
		{* DO Owner *}
		<tr valign="top">
			<td>Marketplace DO Owner</td>
			<td>
				<select name="data[normal_settings][do_user_id]" class="required" title="Marketplace DO Owner">
					<option value="0">-- Please Select --</option>
					{foreach from=$do_user_list key=user_id item=user}
						<option value="{$user_id}" {if $user_id eq $data.normal_settings.do_user_id}selected {/if}>{$user.u}</option>
					{/foreach}
				</select>
			</td>
			<td><img src="ui/rq.gif" /></td>
			<td>
				<ul>
					<li>Users required to have privilege [DO] in Marketplace Branch.</li>
				<ul>
			</td>
		</tr>
	</table>
	
	<br />
	<input type="button" value="Save" onClick="MKT_SETTINGS.save_clicked();" id="btn_save" />
</form>

<script>MKT_SETTINGS.initialise();</script>

{include file='footer.tpl'}