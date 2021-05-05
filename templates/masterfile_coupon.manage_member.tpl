{*
2/11/2020 5:58 PM Andy
- Added new coupon feature "Referral Program".
*}

{include file='header.tpl'}

<style>
{literal}
input.btn_add_member{
	width:100px;
	background-color:#f90;
	color:#fff;
}

div.div_error{
	color: red;
}
div.div_info{
	color: blue;
}
{/literal}
</style>

<script>

var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var COUPON_MEMBER = {
	initialise: function(){
		ADD_MEMBER_DIALOG.initialise();
		
		this.reload_member_list();
	},
	// function to reload member list
	reload_member_list: function(){
		$('tbody_member_list').update(_loading_);
		
		var THIS = this;
		var params = $(document.f_a).serialize()+'&a=ajax_reload_coupon_member_list';
		
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
						// Redirect to main page
						$('tbody_member_list').update(ret['html']);
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
				$('tbody_member_list').update(err_msg);
			}
		});
	},
	// function when user change active
	active_changed: function(card_no){
		var chx = $('cbx_active-'+card_no);
		var img_loading = $('img_active_loading-'+card_no);
		
		var active = chx.checked;
		chx.hide();
		img_loading.show();
		
		var params = {
			a: 'ajax_set_coupon_member_active',
			coupon_code: document.f_a['coupon_code'].value,
			card_no: card_no,
			active: active ? 1 : 0
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
				chx.show();
				img_loading.hide();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Redirect to main page
						
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
				chx.checked = active ? false : true
			}
		});
	},
	// function when user click on delete member
	delete_clicked: function(card_no){
		if(!confirm('Are you sure?'))	return false;
		
		GLOBAL_MODULE.show_wait_popup();
		
		var params = {
			a: 'ajax_delete_coupon_member',
			coupon_code: document.f_a['coupon_code'].value,
			card_no: card_no
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
				GLOBAL_MODULE.hide_wait_popup();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Remove member
						$('tr_member-'+card_no).remove();
						THIS.reset_row_number();
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
	// core function to reset the row number
	reset_row_number: function(){
		var row_no = 0;
		$$('#tbody_member_list span.span_row_no').each(function(span){
			row_no++;
			$(span).update(row_no);
		});
	}
};

var ADD_MEMBER_DIALOG = {
	initialise: function(){
	
	},
	open: function(){
		curtain(true, 'curtain2');
		$('div_add_member_dialog_content').update(_loading_);
		center_div($('div_add_member_dialog').show());
		
		var THIS = this;
		var params = $(document.f_a).serialize()+'&a=ajax_show_add_member';
		
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
						// Redirect to main page
						$('div_add_member_dialog_content').update(ret['html']);
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
				THIS.close();
			}
		});
	},
	close: function(){
		default_curtain_clicked();
		curtain(false, 'curtain2');
	},
	// function when user click on add member
	add_member_clicked: function(add_type){
		if(add_type != 'manual' && add_type != 'csv'){
			alert('Invalid Add Type');
			return false;
		}
		
		if(add_type == 'manual'){
			// Manual
			if(document.f_member['add_member_list'].value.trim() == ''){
				alert('Please key in member to impoty');
				document.f_member['add_member_list'].focus();
				return false;
			}
		}else{
			// CSV
			// only accept csv file
			if(document.f_member['member_file'].value.indexOf('.csv')<0){
				alert('Please select a valid csv file');
				return false;
			}
		}
		
		if(add_type == 'manual'){	// manual add
			// show loading
			$('div_action_loading').update(_loading_);
		
			var THIS = this;
			document.f_member['a'].value = 'ajax_add_member';
			var params = $(document.f_member).serialize()+'&add_type='+add_type;
			$(document.f_member).disable();
			this.enable_action_button(false);
			$('div_add_info_manual').removeClassName("div_error");
			$('div_add_info_manual').removeClassName("div_info");
			
			new Ajax.Request(phpself, {
				parameters: params,
				method: 'post',
				asynchronous: false,
				onComplete: function(msg){			    
					// insert the html at the div bottom
					var str = msg.responseText.trim();
					var ret = {};
					var err_msg = '';
					$('div_action_loading').update('');
					$(document.f_member).enable();
					THIS.enable_action_button(true);
					
					try{
						ret = JSON.parse(str); // try decode json object
						if(ret['ok'] && ret['html']){ // success
							// Redirect to main page
							$('div_add_info_manual').update(ret['html']).addClassName("div_info");
							// reload member list
							COUPON_MEMBER.reload_member_list();
							return;
						}else{  // save failed
							if(ret['failed_reason']){
								$('div_add_info_manual').update(ret['failed_reason']).addClassName("div_error");
								return;
							}
							else    err_msg = str;
						}
					}catch(ex){ // failed to decode json, it is plain text response
						err_msg = str;
					}

					// prompt the error
					alert(err_msg);
				}
			});
		}else{	// add by csv
			document.f_member['a'].value = 'csv_add_coupon_member';
			document.f_member.submit();
			
			// show loading
			$('div_action_loading').update(_loading_);
			
			// disable button
			this.enable_action_button(false);
		}
	},
	// function to enable or disable all button
	enable_action_button: function(is_enable){
		$$('#p_action_btn input').each(function(inp){
			inp.disabled = is_enable ? false : true;
		});
	},
	// core function when csv import failed
	csv_import_error: function(html){
		$('div_add_info_csv').removeClassName("div_info").removeClassName("div_error");
		$('div_add_info_csv').update(html).addClassName("div_error");
		
		// no more loading
		$('div_action_loading').update('');
		
		// disable button
		this.enable_action_button(true);
	},
	csv_import_success: function(html){
		$('div_add_info_csv').removeClassName("div_info").removeClassName("div_error");
		$('div_add_info_csv').update(html).addClassName("div_info");
		
		COUPON_MEMBER.reload_member_list();
		
		// no more loading
		$('div_action_loading').update('');
		
		// disable button
		this.enable_action_button(true);
	}
};

{/literal}
</script>

{* Add Member Dialog *}
<div id="div_add_member_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:600px;height:470px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_add_member_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Add Member</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="ADD_MEMBER_DIALOG.close();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_add_member_dialog_content" style="padding:2px;height:430px;overflow-y:auto;">
	</div>
</div>
<iframe name="if_member" style="width:1px;height:1px;visibility:hidden;"></iframe>

<h1>Manage Coupon Member</h1>

<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="coupon_code" value="{$coupon_items.coupon_code}" />
	
	<table width="100%" class="report_table">
		<tr>
			<td class="col_header" width="200"><b>Coupon Code</b></td>
			<td>{$coupon_items.coupon_code}</td>
		</tr>
		
		<tr>
			<td class="col_header"><b>Discount By</b></td>
			<td>
				{if $coupon_items.discount_by eq 'per'}
					{$coupon_items.print_value} %
				{else}
					Amount {$coupon_items.print_value|number_format:2}
				{/if}
			</td>
		</tr>
		
		<tr>
			<td class="col_header"><b>Limit Per Member</b></td>
			<td>
				{if $coupon_items.member_limit_count > 0}
					{$coupon_items.member_limit_count} use.
				{else}
					No Limit
				{/if}
			</td>
		</tr>
	</table>
</form>

<br />
<h3>Member Listing</h3>

{if $coupon_items.member_limit_type eq 'selected_member'}
	<p>
		<input type="button" value="Add Member" onClick="ADD_MEMBER_DIALOG.open();" />
	</p>
{/if}

<table width="100%" class="report_table">
	<tr class="header">
		<th width="20">&nbsp;</th>
		{if $coupon_items.member_limit_type eq 'selected_member' || $coupon_items.member_limit_type eq 'referral_program'}
			<th width="40">Active</th>
			{if $coupon_items.member_limit_type eq 'selected_member'}
				<th width="80">&nbsp;</th>
			{/if}
		{/if}
		<th>NRIC</th>
		<th>Card No</th>
		<th>Name</th>
		{if $coupon_items.member_limit_type eq 'referral_program'}
			{if $coupon_items.referrer_coupon_get > 0}
				<th>Referrer Count</th>
				<th>Referrer Coupon Earned</th>
			{/if}
			{if $coupon_items.referee_coupon_get > 0}
				<th>Referee Coupon Earned</th>
			{/if}
		{/if}
		<th width="50">Used</th>
		<th width="150">Added</th>
		<th width="150">Last Update</th>
	</tr>
	
	<tbody id="tbody_member_list">
	</tbody>
</table>

<script>COUPON_MEMBER.initialise();</script>
{include file='footer.tpl'}