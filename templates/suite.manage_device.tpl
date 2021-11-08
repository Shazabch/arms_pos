{*
7/2/2020 3:19 PM Andy
- Added "Skip Dongle Checking" for Barcoder device.

9/28/2020 5:43 PM William
- Enhanced to hide branch list when device type is "arms_fnb".

11/9/2020 5:32 PM William
- Enhanced to hide branch list when device type is 'pos'.
*}

{include file='header.tpl'}

<style>
{literal}

{/literal}
</style>
<script>

var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var DEVICE_SETUP = {
	initialise: function(){
		DEVICE_SETUP_POPUP.initialise();
	},
	// function when users click add new device
	add_device: function(){
		DEVICE_SETUP_POPUP.open();
	},
	// function when users click edit device
	edit_device: function(device_guid){
		DEVICE_SETUP_POPUP.open(device_guid);
	},
	// function when users active / inactive device
	toggle_active: function(device_guid){
		var img = $('img_device_active-'+device_guid);
		if(!img)	return;
		
		// Processing
		if(img.src.indexOf('clock')>=0){
			return;
		}
		
		var active = 1;
		if(img.src.indexOf('deact')>=0){
			active = 0;
		}
		
		img.src = 'ui/clock.gif';
		var THIS = this;
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: {
				'a': 'ajax_update_device_active',
				'active': active,
				'device_guid': device_guid
			},
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Update html
						THIS.reload_device_list();				
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
				THIS.reload_device_list();
			}
		});
	},
	// function when users click un-pair device
	unpair_device_clicked: function(device_guid){
		if(!device_guid)	return;
		
		if(!confirm('Are you sure to un-pair this device?\n\nYour device will become unusable until it is paired again.'))	return false;
		
		this.unpair_device(device_guid);
	},
	// core function to un-pair device
	unpair_device: function(device_guid){
		var span_paired_status = $('span_paired_status-'+device_guid);
		if(!span_paired_status)	return;
		
		$(span_paired_status).update(_loading_);
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: {
				'a': 'ajax_unpair_device',
				'device_guid': device_guid
			},
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Update html
						$(span_paired_status).update('-');
						
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
				$(span_paired_status).update('Failed to Un-pair');
			}
		});
	},
	// core function to reload device list
	reload_device_list: function(){
		$('div_reload_device_list').update(_loading_);
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: {
				'a': 'ajax_reload_device_list',
			},
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

				$('div_reload_device_list').update('');
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						$(div_table).update(ret['html']);
						
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
	// function to generate new access code
	generate_access_token: function(){
		var alpha_list = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
		var num_list = [0,1,2,3,4,5,6,7,8,9];
		var rand_char = alpha_list.concat(num_list, num_list, num_list);
		var code_length = 10;
		var code = '';
		
		for(var i=0; i<code_length; i++){
			var j = Math.floor(Math.random()*(rand_char.length));
			code += rand_char[j];
		}
		
		return code;
	}
}

var DEVICE_SETUP_POPUP = {
	initialise: function(){
	
	},
	open: function(device_guid){
		if(!device_guid)	device_guid = '';
		
		var params = {
			'a': 'ajax_show_device',
			'device_guid': device_guid
		}
		
		$('div_device_details_dialog_content').update(_loading_);
		
		//curtain(true);
		//center_div($('div_device_details_dialog').show());
		jQuery('#div_device_details_dialog').modal('show');
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: params,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						$('div_device_details_dialog_content').update(ret['html']);
						
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
				$('div_device_details_dialog_content').update(err_msg);
			}
		});
	},
	close: function(){
		default_curtain_clicked();
	},
	// function when users click generate access code
	generate_access_token_clicked: function(){
		var device_access_token = DEVICE_SETUP.generate_access_token();
		document.f_a['device_access_token'].value = device_access_token;
	},
	// core function to check form
	check_form: function(){
		if(!check_required_field(document.f_a)){
			return false;
		}
		return true;
	},
	// function when users click save
	save: function(){
		if(!this.check_form())	return false;
		
		// Disable Save Button
		$$('#div_btn_update input').each(function(inp){
			inp.disabled = true;
		});
		$('div_device_updating').update(_loading_);
		var THIS = this;
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: $(document.f_a).serialize(),
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						THIS.close();
						DEVICE_SETUP.reload_device_list();
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
				$$('#div_btn_update input').each(function(inp){
					inp.disabled = false;
				});
				$('div_device_updating').update('');
			}
		});
	},
	// function when users toggle allowed branches
	toggle_allowed_branches: function(){
		var c = $('inp_toggle_allowed_branches').checked;
		
		$(document.f_a).getElementsBySelector("input.inp_allowed_branches").each(function(inp){
			inp.checked = c;
		});
	},
	// function when users change device type
	check_device_type: function(){
		var device_type = document.f_a['device_type'].value;
		//alert(device_type);
		if(device_type == 'arms_fnb' || device_type == 'pos'){
			$$('#div_device_details tr.tr_branch_list').invoke('hide');
		}else{
			$$('#div_device_details tr.tr_branch_list').invoke('show');
		}
		$$('#div_device_details tr.tr_special_data').invoke('hide');
		$$('#div_device_details tr.tr_special_data-'+device_type).invoke('show');
	}
}
{/literal}
</script>
<div class="modal"  id="div_device_details_dialog" >
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header bg-danger" id="div_device_details_dialog_header">
                <h6 class="modal-title text-white">Device Information</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true" class="text-white">&times;</span></button>
            </div>
			<div style="clear:both;"></div>
			<div class="modal-body">
                <div id="div_device_details_dialog_content" style="padding:2px;">
		
				</div>
            </div>
        </div>
    </div>
</div>



<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<ul style="list-style-type: none;">
			{if $sessioninfo.id eq 1}
				<li>
					<a href="javascript:void(DEVICE_SETUP.add_device())" /><img src="ui/new.png" title="New" align="absmiddle" border="0" /> Add Device</a>
				</li>
			{/if}
		</ul>
	</div>
</div>

<div id="div_reload_device_list" style="height:20px;"></div>
<div id="div_table" class="stdframe">
	{include file='suite.manage_device.table.tpl'}
</div>


<script>
	DEVICE_SETUP.initialise();
</script>

{include file='footer.tpl'}