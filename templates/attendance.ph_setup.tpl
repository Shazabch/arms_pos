{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var PH_SETUP = {
	initialize: function(){		
		PH_DIALOG.initialize();
	},
	// function when user active / deactivate holiday
	toggle_active: function(ph_id){
		if(!ph_id)	return;
		
		var img_ph_active = $('img_ph_active-'+ph_id);
		var is_active = img_ph_active.src.indexOf('deact')>0 ? 0 : 1;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
				
		var THIS = this;
		var params = {
			a: 'ajax_toggle_ph_active',
			ph_id: ph_id,
			is_active: is_active
		};
		
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
						// Update HTML
						var src = 'ui/act.png';
						var title = 'Activate';
						if(is_active){
							src = 'ui/deact.png';
							title = 'Deactivate';
						}
						img_ph_active.src = src;
						img_ph_active.title = title;
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
};

var PH_DIALOG = {
	initialize: function(){
		new Draggable('div_ph_dialog',{ handle: 'div_ph_dialog_header'});
	},
	open: function(ph_id){
		$('div_ph_dialog_content').update(_loading_);
		
		// Show Dialog
		curtain(true, 'curtain2');
		center_div($('div_ph_dialog').show());
				
		var THIS = this;
		var params = {
			a: 'ajax_show_ph',
			ph_id: ph_id
		};
		
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
	                if(ret['ok'] && ret['html']){ // success
						// Update HTML
						$('div_ph_dialog_content').update(ret['html']);
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
	// function to check form
	validate_form: function(){
		if(!check_required_field(document.f_ph))	return false;
		
		
		return true;
	},
	// function when user click on button save
	save_clicked: function(){
		if(!this.validate_form())	return;
		this.set_action_button(false);
		$('btn_save').value = 'Saving...';
		
		var THIS = this;
		var params = (document.f_ph).serialize();
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				THIS.set_action_button(true);
				$('btn_save').value = 'Save';
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Update HTML
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
			}
		});
	},
	// function to enable / disable action button
	set_action_button: function(is_enable){
		if(!is_enable)	is_enable = false;
		
		$$('#p_action input').each(function(inp){
			inp.disabled = !is_enable;
		});
	}
}
{/literal}
</script>

{* Public Holiday Dialog *}
<div id="div_ph_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:600px;height:250px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_ph_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Holiday Info</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="SHIFT_DIALOG.close();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_ph_dialog_content" style="padding:2px;overflow-y:auto;height:460px;">
	</div>
</div>

<h1>{$PAGE_TITLE}</h1>

<div>
	<a href="javascript:void(PH_DIALOG.open(0))">
		<img src="ui/new.png" title="New" align="absmiddle" border="0" /> Add New Holiday
	</a> 
</div>
<br />

<table class="report_table">
	<tr class="header">
		<th>&nbsp;</th>
		<th>Code</th>
		<th>Description</th>
	</tr>
	
	<tbody id="tbody_ph_list">
		{foreach from=$ph_list key=ph_id item=r}
			<tr class="tr_ph" id="tr_ph-{$ph_id}">
				<td>
					{* Edit *}
					<a href="javascript:void(PH_DIALOG.open('{$ph_id}'))"><img src="ui/ed.png" title="Edit" border="0" /></a>
					
					{* Active / Inactve *}
					<a href="javascript:void(PH_SETUP.toggle_active('{$ph_id}'))">
						<img src="{if $r.active}ui/deact.png{else}ui/act.png{/if}" title="{if $r.active}Deactivate{else}Activate{/if}" border="0" id="img_ph_active-{$ph_id}" />
					</a>
				</td>
				
				<td>{$r.code}</td>
				<td>{$r.description}</td>
			</tr>
		{/foreach}
	</tbody>
</table>

<script>PH_SETUP.initialize();</script>
{include file='footer.tpl'}