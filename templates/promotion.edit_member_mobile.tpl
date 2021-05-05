{include file='header.tpl'}

<style>
{literal}

{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var can_edit = int('{$can_edit}');

{literal}
var PROMO_MEM_MOBILE = {
	f: undefined,
	f_banner: undefined,
	f_item: undefined,
	f_settings: undefined,
	initialise: function(){
		this.f = document.f_a;
		this.f_banner = document.f_banner;
		this.f_item = document.f_item;
		this.f_settings = document.f_settings;
		
		if(can_edit){
		
		}else{
			Form.disable(this.f);
			Form.disable(this.f_banner);
			Form.disable(this.f_settings);
			Form.disable(this.f_item);
		}
		
		// check show member mobile
		this.check_show_in_member_mobile();
	},
	// function to check show member mobile
	check_show_in_member_mobile: function(){
		var is_show = int(this.f['show_in_member_mobile'].value);
		if(is_show){
			//Form.enable(this.f_banner);
			$('div_data').show();
		}else{
			//Form.disable(this.f_banner);
			$('div_data').hide();
			
		}
	},
	// function when user change 'Show in Membership Mobile'
	show_in_member_mobile_changed: function(){
		GLOBAL_MODULE.show_wait_popup();
		
		var params = $(this.f).serialize();
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
	                if(ret['ok']){ // success
						// Update html
						location.reload(true);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'Server No Respond';
				
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user click on upload banner vertical #1
	upload_banner_vertical_1_clicked: function(){
		// Check File Extension
		if (this.f_banner['banner_vertical_1'].value == '') {
			alert('Select a file to upload');
			return false;
		}else if (!/\.jpg|\.jpeg|\.png|\.gif/i.test(this.f_banner['banner_vertical_1'].value))
		{
			alert("Selected file must be a valid JPG/JPEG/PNG/GIF image");
			return false;
		}
		
		var oFile = this.f_banner['banner_vertical_1'].files[0];
		if (oFile.size > 1000000 ) // 1 mb for bytes.
		{
			alert("Image File Size is limited to a maximum of 1MB only.");
			return false;
		}
		
		if(!confirm('Are you sure?')) return false;
		
		this.set_banner_uploading(true);
		this.f_banner.submit();
	},
	set_banner_uploading: function(is_uploading){
		if(is_uploading){
			$('btn_submit_banner_vertical_1').disabled = true;
			$('span_loading_banner_vertical_1').show();
		}else{
			$('btn_submit_banner_vertical_1').disabled = false;
			$('span_loading_banner_vertical_1').hide();
		}
	},
	// callback function when upload failed
	banner_uploaded_failed: function(){
		this.set_banner_uploading(false);
	},
	// callback function after banner uploaded
	banner_vertical_1_uploaded: function(filepath){
		$('img_banner_vertical_1').src = filepath;
		this.set_banner_uploading(false);
	},
	// function when user change item show in mobile
	item_show_in_member_mobile_changed: function(item_id){
		var chx = $('chx_show_in_member_mobile-'+item_id);
		var img_process = $('img_process_show_in_member_mobile-'+item_id);
		var is_show = chx.checked ? 1 : 0;
		
		$(chx).hide();
		$(img_process).show();
		
		var params = {
			'branch_id': this.f_item['branch_id'].value,
			'id': this.f_item['id'].value,
			'a': 'ajax_update_promo_item_in_membership_mobile',
			'item_id': item_id,
			'is_show': is_show
		}
		var THIS = this;
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				
				$(chx).show();
				$(img_process).hide();
						
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// success
						
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'Server No Respond';
				
			    // prompt the error
			    alert(err_msg);
				$(chx).checked = !is_show;
			}
		});
	},
	// function when user change special for you
	enable_special_for_you_changed: function(){
		var c = int(this.f_settings['enable_special_for_you'].value);
		
		if(c){
			$('div_special_for_you_details').show();
		}else{
			$('div_special_for_you_details').hide();
		}
	},
	// function when user click on save settings
	save_settings_clicked: function(){
		if(int(this.f_settings['special_for_you_info[qty]'].value) <= 0){
			alert('Please key in Hit Ratio Quantity');
			this.f_settings['special_for_you_info[qty]'].focus();
			return false;
		}
		
		if(int(this.f_settings['special_for_you_info[month]'].value) <= 0){
			alert('Please key in Hit Ratio Month');
			this.f_settings['special_for_you_info[month]'].focus();
			return false;
		}
		
		GLOBAL_MODULE.show_wait_popup();
		
		var params = $(this.f_settings).serialize();
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
	                if(ret['ok']){ // success
						// Update html
						//location.reload(true);
						alert('Settings Updated.');
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'Server No Respond';
				
			    // prompt the error
			    alert(err_msg);
			}
		});
	}
}
{/literal}
</script>

<h1>Membership Mobile Promotion Configuration</h1>

<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_change_promo_show_in_member_mobile" />
	<input type="hidden" name="branch_id" value="{$form.branch_id}" />
	<input type="hidden" name="id" value="{$form.id}" />
	
	<div class="stdframe" style="background:#fff">
		<table border=0 cellspacing=0 cellpadding=4>
			{* Title *}
			<tr>
				<td><b>Title</b></td>
				<td>{$form.title}</td>
			</tr>
			
			<tr>
				<td><b>Show in Membership Mobile</b></td>
				<td>
					<select name="show_in_member_mobile" onChange="PROMO_MEM_MOBILE.show_in_member_mobile_changed();">
						<option value="0">No</option>
						<option value="1" {if $form.show_in_member_mobile}selected{/if} >Yes</option>
					</select>
				</td>
			</tr>
		</table>
	</div>
</form>


<div id="div_data" style="display:none;">

	{* Banner *}
	<br />
	<form name="f_banner" onSubmit="return false;" method="post" enctype="multipart/form-data" target="if_banner" >
		<input type="hidden" name="a" value="upload_promo_banner" />
		<input type="hidden" name="branch_id" value="{$form.branch_id}" />
		<input type="hidden" name="id" value="{$form.id}" />
		
		<div class="stdframe" style="background:#fff" id="div_banner">
			<table width="100%">
				<tr>
					<td>
						<h3>Vertical Banner #1 (320x200)</h3>
			
						<h4>Current:</h4>
						<img style="border:2px solid black;width:320px;height:200px;" src="{$form.banner_vertical_1}" title="Vertical Banner #1" id="img_banner_vertical_1" /><br/><br />
					
						<table>
							<tr>
								<td style="color:#0000ff;">
									<b>Note:</b>
									<ul>
										<li> Please ensure the file is a valid image file (JPG/JPEG/PNG/GIF).</li>
										<li> Uploaded banner will replace existing one.</li>
										<li> If no upload, mobile app will display using the default banner.</li>
										<li> Image File Size is limited to a maximum of 1MB only.</li>
										<li> Recommended size is 320x200.</li>
									</ul>
								</td>
							</tr>
							
							{if $can_edit}
								<tr>
									<td>
										<b>Please Select Image: </b>
										&nbsp;&nbsp;&nbsp;
										<input type="file" name="banner_vertical_1"/>&nbsp;&nbsp;&nbsp;
										<input type="button" value="Upload" onClick="PROMO_MEM_MOBILE.upload_banner_vertical_1_clicked();" id="btn_submit_banner_vertical_1" />
										<span id="span_loading_banner_vertical_1" style="display:none;background:yellow;padding:2px;">
											<img src="/ui/clock.gif" align="absmiddle" /> Loading...
										</span>
									</td>
								</tr>
							{/if}
						</table>
					</td>
					
					<td align="right">
						{* wireframe_url *}
						<img src="ui/promo_banner_sample.png" style="max-height:500px;margin-right:20px;" />
					</td>
				</tr>
			</table>
		</div>
	</form>
	<iframe name="if_banner" style="width:1px;height:1px;visibility:hidden;"></iframe>
	
	{* Settings *}
	<br />
	<form name="f_settings" onSubmit="return false;" method="post">
		<input type="hidden" name="branch_id" value="{$form.branch_id}" />
		<input type="hidden" name="id" value="{$form.id}" />
		<input type="hidden" name="a" value="ajax_update_special_for_you_settings" />
		
		<div class="stdframe" style="background:#fff" id="div_settings">
			<h3>Settings</h3>
			
			<table>
				<tr>
					<td valign="top"><b>Enable Special For You Feature</b></td>
					<td valign="top">
						<select name="enable_special_for_you" onChange="PROMO_MEM_MOBILE.enable_special_for_you_changed();">
							<option value="0">No</option>
							<option value="1" {if $form.enable_special_for_you}selected{/if} >Yes</option>
						</select>
						
						<div id="div_special_for_you_details" style="{if !$form.enable_special_for_you}display:none;{/if}">
							<b>Hit Ratio:</b><br />
							Have purchased the
							<select name="special_for_you_info[target]">
								<option value="sku" {if $form.special_for_you_info.target eq 'sku'}selected {/if}>Same SKU</option>
								<option value="cat" {if $form.special_for_you_info.target eq 'cat'}selected {/if}>Same Category Product</option>
							</select>
							with
							<input type="text" name="special_for_you_info[qty]" value="{$form.special_for_you_info.qty|default:1}" style="width:50px;text-align:right;" onChange="miz(this)" /> 
							Quantity in last
							<input type="text" name="special_for_you_info[month]" value="{$form.special_for_you_info.month|default:6}" style="width:50px;text-align:right;" onChange="miz(this)" /> 
							Month before the Promotion Start Date ({$form.date_from}).
						</div>
					</td>
				</tr>
				
			</table>
			
			{if $can_edit}
				<input type="button" value="Save Settings" onClick="PROMO_MEM_MOBILE.save_settings_clicked();" />
			{/if}
		</div>
	</form>
	
	{* Items *}
	<br />
	<form name="f_item" onSubmit="return false;" method="post">
		<input type="hidden" name="branch_id" value="{$form.branch_id}" />
		<input type="hidden" name="id" value="{$form.id}" />
		
		<div class="stdframe" style="background:#fff" id="div_items">
			<h3>Item List</h3>
			
			<table class="report_table" width="100%">
				<tr class="header">
					<th width="30" rowspan="2">&nbsp;</th>
					<th width="100" rowspan="2">Show in Mobile</th>
					<th width="100" rowspan="2">Promo Photo</th>
					<th width="120" rowspan="2">ARMS Code</th>
					<th width="120" rowspan="2">MCode</th>
					<th width="120" rowspan="2">Art No</th>
					<th width="120" rowspan="2">{$config.link_code_name}</th>
					<th rowspan="2">Description</th>
					<th colspan="2">Member</th>
					<th colspan="2">Non Member</th>
				</tr>
				<tr class="header">
					<th>Discount</th>
					<th>Price</th>
					<th>Discount</th>
					<th>Price</th>
				</tr>
				
				{foreach from=$promo_items item=r name=fp}
					{assign var=item_id value=$r.id}
					<tr>
						<td>{$smarty.foreach.fp.iteration}.</td>
						<td align="center">
							
							<input type="checkbox" name="promo_items[show_in_member_mobile][{$item_id}]" value="1" {if $r.show_in_member_mobile}checked {/if} id="chx_show_in_member_mobile-{$item_id}" onChange="PROMO_MEM_MOBILE.item_show_in_member_mobile_changed('{$item_id}');" id="chx_show_in_member_mobile-{$item_id}" />
						
						
							<img src="ui/clock.gif" title="Updating..." id="img_process_show_in_member_mobile-{$item_id}" style="display:none;" />
							
						</td>
						<td align="center">
							{if $r.promo_photo_url}
								<img width="110" height="100" align="absmiddle" vspace="4" hspace="4" alt="Promo Photo" src="/thumb.php?w=110&h=100&cache=1&img={$r.promo_photo_url|urlencode}" border="0" style="cursor:pointer" onClick="show_sku_image_div('{$r.promo_photo_url|escape:javascript}');" title="View" />
							{else}
								-
							{/if}
						</td>
						<td align="center">{$r.sku_item_code}</td>
						<td align="center">{$r.mcode}</td>
						<td align="center">{$r.artno}</td>
						<td align="center">{$r.link_code}</td>
						<td>{$r.description}</td>
						
						{* Member *}
						<td>{$r.member_disc_p|ifzero:'&nbsp;'}</td>
						<td>{$r.member_disc_a|ifzero:'&nbsp;'}</td>
						
						{* Non Member *}
						<td>{$r.non_member_disc_p|ifzero:'&nbsp;'}</td>
						<td>{$r.non_member_disc_a|ifzero:'&nbsp;'}</td>
					</tr>
				{/foreach}
			</table>
		</div>
	</form>
</div>
<script>PROMO_MEM_MOBILE.initialise();</script>
{include file='footer.tpl'}