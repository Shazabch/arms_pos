{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var got_error = {if $err}1{else}0{/if}

{literal}
var LEAVE_SETUP = {
	initialise: function(){
		LEAVE_DIALOG.initialise();
		
		if(!got_error){
			this.check_empty();
		}
	},
	// function to check if the shift table is empty
	check_empty: function(){
		var tr_count = $$('#tbody_leave_list tr.tr_leave').length;
		
		if(tr_count > 0)	return;
		
		if(!confirm('Found Leave Table is Empty\n\nDo you want system to generate the default Leave Table?'))	return;
		
		document.location = '?a=generate_default_leave';
	},
	// function when user active / deactivate holiday
	toggle_active: function(leave_id){
		if(!leave_id)	return;
		
		var img_leave_active = $('img_leave_active-'+leave_id);
		var is_active = img_leave_active.src.indexOf('deact')>0 ? 0 : 1;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
				
		var THIS = this;
		var params = {
			a: 'ajax_toggle_leave_active',
			leave_id: leave_id,
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
						img_leave_active.src = src;
						img_leave_active.title = title;
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
}

var LEAVE_DIALOG = {
	initialise: function(){
		new Draggable('div_leave_dialog',{ handle: 'div_leave_dialog_header'});
	},
	open: function(leave_id){
		$('div_leave_dialog_content').update(_loading_);
		
		// Show Dialog
		curtain(true, 'curtain2');
		center_div($('div_leave_dialog').show());
				
		var THIS = this;
		var params = {
			a: 'ajax_show_leave',
			leave_id: leave_id
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
						$('div_leave_dialog_content').update(ret['html']);
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
		if(!check_required_field(document.f_leave))	return false;
		
		
		return true;
	},
	// function when user click on button save
	save_clicked: function(){
		if(!this.validate_form())	return;
		this.set_action_button(false);
		$('btn_save').value = 'Saving...';
		
		var THIS = this;
		var params = (document.f_leave).serialize();
		
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

{* Leave Dialog *}
<div id="div_leave_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:600px;height:250px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_leave_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Leave Info</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="SHIFT_DIALOG.close();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_leave_dialog_content" style="padding:2px;overflow-y:auto;height:460px;">
	</div>
</div>

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>


<div>
	<div class="card mx-3">
		<div class="card-body">
			<a href="javascript:void(LEAVE_DIALOG.open(0))">
				<img src="ui/new.png" title="New" align="absmiddle" border="0" /> Add New Leave
			</a> 
		</div>
	</div>
</div>
<br />

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table table mb-0 text-md-nowrap  table-hover"
			>
				<thead class="bg-gray-100" style="height: 20px;">
					<tr class="header">
						<th>&nbsp;</th>
						<th class="text-center">Code</th>
						<th class="text-center">Description</th>
					</tr>
				</thead>
				
				<tbody id="tbody_leave_list" class="fs-08">
					{foreach from=$leave_list key=leave_id item=r}
						<tr class="tr_leave" id="tr_leave-{$leave_id}">
							<td>
								{* Edit *}
								<a href="javascript:void(LEAVE_DIALOG.open('{$leave_id}'))"><img src="ui/ed.png" title="Edit" border="0" /></a>
								
								{* Active / Inactve *}
								<a href="javascript:void(LEAVE_SETUP.toggle_active('{$leave_id}'))">
									<img src="{if $r.active}ui/deact.png{else}ui/act.png{/if}" title="{if $r.active}Deactivate{else}Activate{/if}" border="0" id="img_leave_active-{$leave_id}" />
								</a>
							</td>
							
							<td>{$r.code}</td>
							<td>{$r.description}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
</div>

<script>LEAVE_SETUP.initialise();</script>
{include file='footer.tpl'}