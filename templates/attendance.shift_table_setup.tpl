{include file='header.tpl'}

<link rel="stylesheet" media="screen" type="text/css" href="include/css/colorpicker.css" />
<script src="/js/jquery-1.7.2.min.js"></script>

<script>
$.noConflict();
</script>
<script src="/js/colorpicker.js"></script>

<style>
{literal}
.colorpicker{
	z-index: 10010;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var got_error = {if $err}1{else}0{/if}

{literal}
var SHIFT_SETUP = {
	initialize: function(){
		if(!got_error){
			this.check_empty();
		}
		
		SHIFT_DIALOG.initialize();
		
		//this.init_color_picker();
	},
	// function to check if the shift table is empty
	check_empty: function(){
		var tr_shift_count = $$('#tbody_shift_list tr.tr_shift').length;
		if(tr_shift_count > 0)	return;
		
		if(!confirm('Found Shift Table is Empty\n\nDo you want system to generate the default Shift Table?'))	return;
		
		document.location = '?a=generate_default_shift';
	},
	// function when user active / deactivate shift
	toggle_active: function(shift_id){
		if(!shift_id)	return;
		
		var img_shift_active = $('img_shift_active-'+shift_id);
		var is_active = img_shift_active.src.indexOf('deact')>0 ? 0 : 1;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
				
		var THIS = this;
		var params = {
			a: 'ajax_toggle_shift_active',
			shift_id: shift_id,
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
						img_shift_active.src = src;
						img_shift_active.title = title;
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
	init_color_picker: function(){
		var default_color = 'white';
		$$('#tbody_shift_list div.colorSelector').each(function(div){
			// initial color picker
			jQuery(div).ColorPicker({
				color: default_color,   // current selecting color
				onShow: function (colpkr) {
					jQuery(colpkr).fadeIn(500);
					return false;
				},
				onHide: function (colpkr) {
					jQuery(colpkr).fadeOut(500);
					return false;
				},
				// function when user click confirm color
				onSubmit: function (hsb, hex, rgb, el) {
					jQuery(el).find('div:first')
						.css('backgroundColor', '#' + hex)  // change the background color
						//.attr('title', '#'+hex);  // change title

					jQuery(el).ColorPickerHide();    // hide color picker
				}
			});
		});
	}
};

var SHIFT_DIALOG = {
	initialize: function(){
		new Draggable('div_shift_dialog',{ handle: 'div_shift_dialog_header'});
	},
	open: function(shift_id){
		$('div_shift_dialog_content').update(_loading_);
		
		// Show Dialog
		curtain(true, 'curtain2');
		center_div($('div_shift_dialog').show());
				
		var THIS = this;
		var params = {
			a: 'ajax_show_shift',
			shift_id: shift_id
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
						$('div_shift_dialog_content').update(ret['html']);
						THIS.init_color_picker();
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
		if(!check_required_field(document.f_shift))	return false;
		
		// Break 1
		if(document.f_shift['break_1_start_time'].value.trim() || document.f_shift['break_1_end_time'].value.trim()){
			if(!document.f_shift['break_1_start_time'].value.trim() || !document.f_shift['break_1_end_time'].value.trim()){
				alert('Please enter both Break 1 Start Time and End Time');
				return false;
			}
		}
		
		// Break 2
		if(document.f_shift['break_2_start_time'].value.trim() || document.f_shift['break_2_end_time'].value.trim()){
			// If no break 1, cannot key in break 2
			if(!document.f_shift['break_1_start_time'].value.trim() || !document.f_shift['break_1_end_time'].value.trim()){
				alert('Please key in Break 1 first.');
				return false;
			}
		
			if(!document.f_shift['break_2_start_time'].value.trim() || !document.f_shift['break_2_end_time'].value.trim()){
				alert('Please enter both Break 2 Start Time and End Time');
				return false;
			}
		}
		
		return true;
	},
	// function when user click on button save
	save_clicked: function(){
		if(!this.validate_form())	return;
		this.set_action_button(false);
		$('btn_save').value = 'Saving...';
		
		var THIS = this;
		var params = (document.f_shift).serialize();
		
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
	},
	init_color_picker: function(){
		// initial color picker
		jQuery('#div_shift_color').ColorPicker({
			color: jQuery('#div_shift_color').attr('default_color'),   // current selecting color
			onShow: function (colpkr) {
				jQuery(colpkr).fadeIn(500);
				return false;
			},
			onHide: function (colpkr) {
				jQuery(colpkr).fadeOut(500);
				return false;
			},
			// function when user click confirm color
			onSubmit: function (hsb, hex, rgb, el) {
				jQuery(el).find('div:first')
					.css('backgroundColor', '#' + hex)  // change the background color
					//.attr('title', '#'+hex);  // change title

				jQuery(el).ColorPickerHide();    // hide color picker
				
				document.f_shift['shift_color'].value = hex;
			}
		});
	}
}

{/literal}
</script>



{* Shift Dialog *}
<div id="div_shift_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:600px;height:250px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_shift_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Shift Info</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="SHIFT_DIALOG.close();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_shift_dialog_content" style="padding:2px;overflow-y:auto;height:460px;">
	</div>
</div>

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{if $err}
	<ul class="errmsg">
		{foreach from=$err item=e}
			<div class="alert alert-danger rouded mx-3">
				<li> {$e}</li>
			</div>
		{/foreach}
	</ul>
{/if}

<div>
<div class="card mx-3">
	<div class="card-body">
		<a href="javascript:void(SHIFT_DIALOG.open(0))">
			<img src="ui/new.png" title="New" align="absmiddle" border="0" /> Add New Shift
		</a> 
	</div>
</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table  class="report_table table mb-0 text-md-nowrap  table-hover"
			>
				<thead class="bg-grya-100">
					<tr class="header">
						<th>&nbsp;</th>
						<th>Code</th>
						<th>Color</th>
						<th>Description</th>
						<th>Start Time</th>
						<th>End Time</th>
					</tr>
				</thead>
				
				<tbody id="tbody_shift_list" class="fs-08">
					{foreach from=$shift_list key=shift_id item=r}
						<tr class="tr_shift" id="tr_shift-{$shift_id}">
							<td>
								{* Edit *}
								<a href="javascript:void(SHIFT_DIALOG.open('{$shift_id}'))"><img src="ui/ed.png" title="Edit" border="0" /></a>
								
								{* Active / Inactve *}
								<a href="javascript:void(SHIFT_SETUP.toggle_active('{$shift_id}'))">
									<img src="{if $r.active}ui/deact.png{else}ui/act.png{/if}" title="{if $r.active}Deactivate{else}Activate{/if}" border="0" id="img_shift_active-{$shift_id}" />
								</a>
							</td>
							
							<td>{$r.code}</td>
							<td>
								<div style="margin:0;padding:0;border:1px solid black;">
									<div title="{$r.shift_color}" style="background-color:{$r.shift_color}">&nbsp;</div>
								</div>
							</td>
							<td>{$r.description}</td>
							<td>{$r.start_time}</td>
							<td>{$r.end_time}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			
		</div>
	</div>
</div>

<script>SHIFT_SETUP.initialize();</script>
{include file='footer.tpl'}