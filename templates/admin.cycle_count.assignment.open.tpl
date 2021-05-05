{*
06/25/2020 04:43 PM Sheila
- Updated button css

11/27/2020 10:51 AM Sheila
- Put the audit person, notify person and stock take person into <table>
*}
{if !$form.approval_screen}
	{include file='header.tpl'}
{else}
	<hr noshade size=2>
{/if}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
{literal}
.div_username_container{
	border: 1px solid blue;
	min-width: 100px;
	float: left;
	padding:3px;
	background-color: #eee;
	margin-left: 5px;
	margin-bottom: 5px;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var can_edit = int('{$can_edit}');
var cycle_count_too_many_sku_count = int('{$cycle_count_too_many_sku_count}');

{literal}
CC_ASSGN = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		if(!can_edit){	// View Only
			Form.disable(this.f);
		}else{
			SEARCH_USER_DIALOG.initialize();
			SAMPLE_SKU_DIALOG.initialize();
			
			// Stock Take Date
			Calendar.setup({
				inputField     :    "inp_st_date",
				ifFormat       :    "%Y-%m-%d",
				button         :    "img_st_date",
				align          :    "Bl",
				singleClick    :    true
			});
		}
	},
	// function to check stock take content type
	check_st_content_type: function(){
		var st_content_type = this.f['st_content_type'].value;
		
		$$('#div_content_type_list div.div_content_type').invoke('hide');
		
		if(st_content_type){
			$('div_content_type-'+st_content_type).show();
		}
		
		// mark content changed
		this.st_content_related_changed();
	},
	// function when user click on search pic (stock take person)
	search_pic_click: function(){
		SEARCH_USER_DIALOG.open('pic');
	},
	// function when user click on search audit person
	search_audit_person_click: function(){
		SEARCH_USER_DIALOG.open('audit');
	},
	// function when user click on search notify person
	search_notify_person_click: function(){
		SEARCH_USER_DIALOG.open('notify');
	},
	// function to add pic user
	add_pic_user: function(user_id, username){
		this.f['pic_user_id'].value = user_id;
		$('span_pic_user').update(username);
	},
	// function to add audit user
	add_audit_notify_user: function(user_type, user_id, username){
		$('span_user_loading-'+user_type).update(_loading_);
		
		new Ajax.Request(phpself, {
			parameters: {
				a: 'ajax_add_audit_notify_user',
				user_type: user_type,
				user_id: user_id,
			},
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$('span_user_loading-'+user_type).update('');
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						new Insertion.Bottom($('span_user_list-'+user_type), ret['html']);
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
	// function when user click on delete audit or notify user
	del_user_assign: function(user_type, user_id){
		$('div_user_assign-'+user_type+'-'+user_id).remove();
	},
	// Core function to validate form data
	validate_form: function(){
		// Stock Take Branch
		if(!this.f['st_branch_id'].value){
			alert('Please Select Stock Take Branch.');
			this.f['st_branch_id'].focus();
			return false;
		}
		
		// Stock Take Content
		var st_content_type = this.f['st_content_type'].value;
		if(!st_content_type){
			alert('Please Select Stock Take Content.');
			this.f['st_content_type'].focus();
			return false;
		}
		if(st_content_type == 'sku_group'){
			// SKU Group
			if(!this.f['tmp_sku_group_id'].value){
				alert('Please Select SKU Group.');
				this.f['tmp_sku_group_id'].focus();
				return false;
			}
		}else if(st_content_type == 'cat_vendor_brand'){
			// Category + Vendor + Brand
			var is_all_cat = $('all_category').checked;
			var category_id = $('category_id').value;
			if(!is_all_cat && !category_id){
				alert('Please search category');
				category_onfocus();
				return false;
			}
			
			var vendor_id = this.f['vendor_id'].value;
			var brand_id = this.f['brand_id'].value;
			
			// Not allow to select all for 3 type
			if(is_all_cat && !vendor_id && brand_id==-1){
				alert('Not allow to select ALL Category + All Vendor + All Brand');
				return false;
			}
		}else{
			alert('Invalid Stock Take Content.');
			this.f['st_content_type'].focus();
			return false;
		}
		
		// Stock Take Data
		if(!this.f['propose_st_date'].value){
			alert('Please Key in Stock Take Date.');
			this.f['propose_st_date'].focus();
			return false;
		}
		
		// PIC
		if(!this.f['pic_user_id'].value){
			alert('Please Select Stock Take Person.');
			return false;
		}
		
		return true;
	},
	// function when user click on recalculate estimate sku
	recalculate_estimate_sku: function(){
		// Validate Form
		if(!this.validate_form()){
			return false;
		}
		
		$('btn_recalculate_estimate_sku').disabled = true;
		$('span_recalculate_estimate_sku_loading').update(_loading_);
		
		var THIS = this;
		var params = $(this.f).serialize()+'&a=ajax_calculate_estimate_sku_count';
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			asynchronous: false,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$('btn_recalculate_estimate_sku').disabled = false;
				$('span_recalculate_estimate_sku_loading').update('');
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Update html
						var estimate_sku_count = int(ret['estimate_sku_count']);
						THIS.f['estimate_sku_count'].value = estimate_sku_count;
						
						// Check to show warning
						if(estimate_sku_count> cycle_count_too_many_sku_count){
							$('span_sku_count_too_many').show();
						}else{
							$('span_sku_count_too_many').hide();
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
	// function when user click save
	save_form: function(is_confirm){
		if(is_confirm == undefined)	is_confirm = false;
		
		// Validate Form
		if(!this.validate_form()){
			return false;
		}
		
		if(is_confirm){
			if(!confirm('Are you sure?'))	return false;
		}
		
		// Must Login
		if(!check_login())	return;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup({'no_need_effect':1});
		
		// Check estimate sku count
		if(this.f['estimate_sku_count'].value == '-'){
			this.recalculate_estimate_sku();
		}
		
		// Too Many SKU
		if(this.f['estimate_sku_count'].value > cycle_count_too_many_sku_count){
			if(!confirm('You have selected esimate of ['+this.f['estimate_sku_count'].value+'] sku to stock take, Are you sure?')){
				GLOBAL_MODULE.hide_wait_popup();
				return false;
			}
		}
		
		var THIS = this;
		var params = $(this.f).serialize()+'&a=ajax_save_cycle_count';
		if(is_confirm)	params += '&is_confirm=1';
		
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
	                if(ret['ok'] && ret['id']){ // success
						// Redirect to main page
						document.location = phpself+'?t='+ret['t']+'&id='+ret['id'];
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
	// function when user click on cancel
	cancel_form: function(is_reset){
		if(!is_reset)	is_reset = 0;
		
		// Must Login
		if(!check_login())	return;
		
		var cancel_type = is_reset ? 'Reset' : 'Cancel';
		this.f['cancel_reason'].value = '';
		var p = prompt('Enter reason to '+cancel_type+' :');
		if (p.trim()=='' || p==null) return;
		this.f['cancel_reason'].value = p;
		if (!confirm(cancel_type+' this Cycle Count?')){
			return false;
		}
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup({'no_need_effect':1});
		
		var THIS = this;
		if(!can_edit)Form.enable(this.f);
		var params = $(this.f).serialize()+'&a=ajax_cancel_cycle_count';
		if(!can_edit)Form.disable(this.f);
		
		if(is_reset)	params += '&is_reset=1';
		
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
	                if(ret['ok'] && ret['id']){ // success
						// Redirect to main page
						if(is_reset){
							document.location = phpself+'?t=reset&id='+ret['id'];
						}else{
							document.location = phpself+'?t=cancelled&id='+ret['id'];
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
				GLOBAL_MODULE.hide_wait_popup();
			}
		});
	},
	// function when users changed sku group
	sku_group_changed: function(){
		var tmp_sku_group_id = this.f['tmp_sku_group_id'].value;
		var sku_group_bid = 0;
		var sku_group_id = 0;
		if(tmp_sku_group_id){
			var tmp = tmp_sku_group_id.split('_');
			sku_group_bid = tmp[0];
			sku_group_id = tmp[1];
		}
		this.f['sku_group_bid'].value = sku_group_bid;
		this.f['sku_group_id'].value = sku_group_id;
		
		this.st_content_related_changed();
	},
	// function when users changed vendor
	vendor_changed: function(){
		this.st_content_related_changed();
	},
	// function when users changed brand
	brand_changed: function(){
		this.st_content_related_changed();
	},
	// function when users changed category
	cat_changed: function(){
		this.st_content_related_changed();
	},
	// core function when system found users change stock take content
	st_content_related_changed: function(){
		this.f['estimate_sku_count'].value = '-';
	},
	// function when users click on view sample sku
	view_sample_sku_clicked: function(){
		// Validate Form
		if(!this.validate_form()){
			return false;
		}
		
		// Must Login
		if(!check_login())	return;
		
		// Check estimate sku count
		if(this.f['estimate_sku_count'].value == '-'){
			// show wait popup
			GLOBAL_MODULE.show_wait_popup({'no_need_effect':1});
		
			// Check Estimate SKU
			this.recalculate_estimate_sku();
			
			// Hide wait popup
			GLOBAL_MODULE.hide_wait_popup();
		}
		
		// Show Sample SKU
		SAMPLE_SKU_DIALOG.open();
	},
	// function when user change notify day
	notify_day_changed: function(){
		var inp = this.f['notify_day'];
		miz(inp);
		var day = int(inp.value);
		if(day<0){
			day = 0;
		}else if(day>100){
			alert('Maximum 100 days.');
			day = 100;
		}
		inp.value = day;
	}
}

var SEARCH_USER_DIALOG = {
	initialize: function(){
		this.f = document.f_search_user;
		
		USER_AUTOCOMPLETE.initialize({
			'callback': function(user_id, username){
				SEARCH_USER_DIALOG.add_user_clicked(user_id, username);
			}
		});
	},
	open: function(user_type){
		if(!user_type)	return false;
		
		// Clear Form value
		this.f.reset();
		$('inp_selected_user_id').value = '';
		
		// Set user type
		this.f['user_type'].value = user_type;
		
		// Show Dialog
		curtain(true);
		center_div($('div_search_user_dialog').show());
		
		// Focus on search input
		USER_AUTOCOMPLETE.focus_inp_search_username();
	},
	close: function(){
		default_curtain_clicked();
	},
	// function when user click to add user
	add_user_clicked: function(user_id, username){
		if(!user_id){
			alert('Please search the user.');
			// Focus on search input
			USER_AUTOCOMPLETE.focus_inp_search_username();
			return false;
		}
		
		var user_type = this.f['user_type'].value;
		
		if(user_type == 'pic'){
			this.close();
			CC_ASSGN.add_pic_user(user_id, username);
		}else if(user_type == 'audit' || user_type == 'notify'){
			this.close();
			CC_ASSGN.add_audit_notify_user(user_type, user_id, username);
		}
	}
}

var SAMPLE_SKU_DIALOG = {
	initialize: function(){
		
	},
	open: function(){
		// Show Loading
		$('div_sample_sku_dialog_content').update(_loading_);
		
		// Show Dialog
		curtain(true);
		center_div($('div_sample_sku_dialog').show());
		
		var THIS = this;
		var params = $(CC_ASSGN.f).serialize()+'&a=ajax_show_sample_sku';
		
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
						$('div_sample_sku_dialog_content').update(ret['html']);
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
	},
};

{/literal}
</script>

{* Search User Dialog *}
<div id="div_search_user_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:500px;height:100px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_search_user_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Search User</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="SEARCH_USER_DIALOG.close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_search_user_dialog_content" style="padding:2px;">
		<form name="f_search_user">
			<input type="hidden" name="user_type" />
			
			<p align="center">
				<b>Search User: &nbsp;&nbsp;&nbsp;</b>
				{include file='user_autocomplete.tpl' btn_add=1}
			</p>
		</form>
	</div>
</div>

{* Sample SKU Dialog *}
<div id="div_sample_sku_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:600px;height:470px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_sample_sku_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Sample SKU</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="SAMPLE_SKU_DIALOG.close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sample_sku_dialog_content" style="padding:2px;height:430px;overflow-y:auto;">
	</div>
</div>

<h1>{$PAGE_TITLE} - {if is_new_id($form.id)}NEW{else}{$form.doc_no}{/if}</h1>

{if !is_new_id($form.id)}
	<h3>Status:
		{if !$form.active}
			Cancelled (Reason: {$form.cancel_reason|default:'-'})
		{else}
			{if $form.completed}
				Completed
			{else}
				{if $form.status eq 0}
					Saved
				{elseif $form.status eq 1}
					{if $form.approved}
						Approved
					{else}
						Confirmed
					{/if}
				{elseif $form.status eq 2}
					Rejected
				{/if}
			{/if}
		{/if}
	</h3>
	{include file=approval_history.tpl approval_history=$form.approval_history}
	
	{if $form.approval_screen}
		<form name="f_approval" method="post">
			<input type="hidden" name="branch_id" value="{$form.branch_id}" />
			<input type="hidden" name="id" value="{$form.id}"/>
			<input type="hidden" name="comment" value="">
			<input type="hidden" name="a" value="submit_approval">
			<input type="hidden" name="status_type" value="">
			<input type="hidden" name="approvals" value="{$form.approvals}">
			<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}">
		</form>
	{/if}
{/if}

<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="branch_id" value="{$form.branch_id}" />
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="doc_no" value="{$form.doc_no}" />
	<input type="hidden" name="cancel_reason" />
	<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}" />
	
	<div class="stdframe" style="background:#fff;">
		<h4>General Information</h4>
		
		<table>
			{* Stock Take Branch *}
			<tr>
				<td width="200"><b>Stock Take Branch</b></td>
				<td>
					{if $BRANCH_CODE eq 'HQ' and $form.branch_id eq 1}
						<select name="st_branch_id">
							<option value="">-- Please Select --</option>
							{foreach from=$branches key=bid item=b}
								<option value="{$bid}" {if $bid eq $form.st_branch_id}selected {/if}>{$b.code}</option>
							{/foreach}
						</select>
					{else}
						<input type="hidden" name="st_branch_id" value="{$form.st_branch_id}" />
						{$branches[$form.st_branch_id].code}
					{/if}
				</td>
			</tr>
			
			{* Stock Take Content *}
			<tr>
				<td valign="top"><b>Stock Take Content</b></td>
				<td>
					<select name="st_content_type" onChange="CC_ASSGN.check_st_content_type();">
						<option value="">-- Please Select --</option>
						{foreach from=$st_content_type_list key=v item=r}
							<option value="{$v}" {if $form.st_content_type eq $v}selected {/if}>{$r.desc}</option>
						{/foreach}
					</select>
					
					<div id="div_content_type_list">
						{* Category + Vendor + Brand *}
						<div id="div_content_type-cat_vendor_brand" class="div_content_type" style="{if $form.st_content_type ne 'cat_vendor_brand'}display:none;{/if}">
							<br />
							
							<div class="stdframe">								
								<table>
									<tr>
										<td width="100">Category</td>
										<td>										
											<div>
												{include file='category_autocomplete.tpl' all=1 autocomplete_callback='CC_ASSGN.cat_changed();'}
												<hr />
											</div>
										</td>
									</tr>
									
									<tr>
										<td>Vendor</td>
										<td>
											<select name="vendor_id" onChange="CC_ASSGN.vendor_changed();">
												<option value="">-- All --</option>
												{foreach from=$vendor_list key=vid item=r}
													<option value="{$vid}" {if $form.vendor_id eq $vid}selected {/if}>{$r.code} - {$r.description}</option>
												{/foreach}
											</select>
										</td>
									</tr>
									
									<tr>
										<td>Brand</td>
										<td>
											<select name="brand_id" onChange="CC_ASSGN.brand_changed();">
												<option value="-1">-- All --</option>
												<option value="0" {if isset($form.brand_id) and !$form.brand_id}selected {/if}>UN-BRANDED</option>
												{foreach from=$brand_list key=brand_id item=r}
													<option value="{$brand_id}" {if $form.brand_id eq $brand_id}selected {/if}>{$r.code} - {$r.description}</option>
												{/foreach}
											</select>
										</td>
									</tr>
								</table>
								
							</div>
						</div>
						
						{* SKU Group *}
						<div id="div_content_type-sku_group" class="div_content_type" style="{if $form.st_content_type ne 'sku_group'}display:none;{/if}">
							<br />
							
							<div class="stdframe">								
								<table>
									<tr>
										<td width="100">SKU Group</td>
										<td>
											<input type="hidden" name="sku_group_bid" value="{$form.sku_group_bid}" />
											<input type="hidden" name="sku_group_id" value="{$form.sku_group_id}" />
											
											<select name="tmp_sku_group_id" onChange="CC_ASSGN.sku_group_changed();">
												<option value="">-- Please Select --</option>
												{foreach from=$sku_group_list key=k item=r}
													<option value="{$k}" {if $form.tmp_sku_group_id eq $k}selected {/if}>{$r.code} - {$r.description}</option>
												{/foreach}
											</select>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</td>
			</tr>
			
			{* Stock Take Date *}
			<tr>
				<td><b>Propose Stock Take Date</b></td>
				<td>
					<input name="propose_st_date" id="inp_st_date" size="10" maxlength="10"  value="{$form.propose_st_date|date_format:"%Y-%m-%d"}" />
					{if $can_edit}
						<img align="absmiddle" src="ui/calendar.gif" id="img_st_date" style="cursor: pointer;" title="Select Stock Take Date" />
					{/if}
				</td>
			</tr>
			
			{* Stock Take Person *}
			<tr>
				<td><b>Stock Take Person</b></td>
				<td>
					<input type="hidden" name="pic_user_id" value="{$form.pic_user_id}" />
					
					<table width="100%" border="0" cellspacing="0" cellpadding="4">
					<tr>
						<td valign="top" width="10px">
						{if $can_edit}
							<img src="ui/ed.png" align="absmiddle" onClick="CC_ASSGN.search_pic_click();" />
						{/if}
						</td>
						<td valign="top">
						<span id="span_pic_user">
							{$form.pic_username}
						</span>
						</td>
					</tr>
					</table>
					
				</td>
			</tr>
			
			{* Audit Person *}
			<tr>
				<td><b>Audit Person</b></td>
				<td>
					<table width="100%" border="0" cellspacing="0" cellpadding="4">
					<tr>
						<td valign="top" width="10px">
						{if $can_edit}
							<img src="ui/ed.png" align="absmiddle" onClick="CC_ASSGN.search_audit_person_click();" style="float:left;" />
						{/if}
						</td>
						<td valign="top">
						<span id="span_user_list-audit">
							{foreach from=$form.audit_user_list item=tmp_user_id}
								{include file='admin.cycle_count.assignment.open.user.tpl' user_type='audit' user=$user_list.$tmp_user_id}
							{foreachelse}
								{if !$can_edit}-{/if}	
							{/foreach}
						</span>
						<span id="span_user_loading-audit"></span>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			
			{* Notify Person *}
			<tr>
				<td><b>Notify Person</b></td>
				<td>
					<table width="100%" border="0" cellspacing="0" cellpadding="4">
						<tr>
							<td valign="top" width="10px">
							{if $can_edit}
								<img src="ui/ed.png" align="absmiddle" onClick="CC_ASSGN.search_notify_person_click();" style="float:left;" />
							{/if}
							</td>
							<td valign="top">
							<span id="span_user_list-notify">
								{foreach from=$form.notify_user_list item=tmp_user_id}
									{include file='admin.cycle_count.assignment.open.user.tpl' user_type='notify' user=$user_list.$tmp_user_id}
								{foreachelse}
									{if !$can_edit}-{/if}
								{/foreach}
							</span>
							<span id="span_user_loading-notify"></span>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			
			{* Notification Time *}
			<tr>
				<td><b>Notification Time</b></td>
				<td>
					Send Notification to above users
					<input type="text" style="width:50px;text-align:right;" name="notify_day" value="{$form.notify_day|ifempty:7}" onChange="CC_ASSGN.notify_day_changed();" />
					days before Propose Stock Take Month.
				</td>
			</tr>
			
			{* Remark *}
			<tr>
				<td><b>Remark</b></td>
				<td><textarea name="remark" cols="68" rows="2">{$form.remark}</textarea></td>
			</tr>
		</table>
	</div>
	
	<br />
	
	<div class="stdframe" style="background-color: #fff;">
		<h4>Items</h4>
		
		<table>
			<tr>
				<td width="200"><b>Estimate SKU Count</b></td>
				<td>
					<input type="text" name="estimate_sku_count" size="10" readonly value="{$form.estimate_sku_count|ifempty:'-'}" style="text-align:right;" />
					{if $can_edit}
						<input class="btn btn-primary" type="button" value="Calculate" onClick="CC_ASSGN.recalculate_estimate_sku();" id="btn_recalculate_estimate_sku" />
						<span id="span_recalculate_estimate_sku_loading"></span>
						<br />
						<a href="javascript:void(CC_ASSGN.view_sample_sku_clicked());">View Sample SKU</a>
					{/if}
					
					<br />
					<span id="span_sku_count_too_many" style="color:red;{if $form.estimate_sku_count<=$cycle_count_too_many_sku_count}display:none;{/if}">
						<img src="ui/messages.gif" align="absmiddle" /> Too many SKU in this cycle count. (More than {$cycle_count_too_many_sku_count} is considered too many)
					</span>
				</td>
			</tr>
		</table>
	</div>
</form>

<p id="p_submit_btn" align="center">
	 {if $form.status==1 and $form.approved==0 and $form.approval_screen}
		<input type="button" value="Approve" style="background-color:#f90; color:#fff;" onclick="CC_APPROVAL.do_approve()">
		<input type="button" value="Reject" style="background-color:#f90; color:#fff;" onclick="CC_APPROVAL.do_reject()">
		<input type="button" value="Terminate" style="background-color:#900; color:#fff;" onclick="CC_APPROVAL.do_cancel()">
	{/if}
	
	{if !$form.approval_screen}
		{if $form.active eq 1}
			{if $form.status eq 0 || $form.status eq 2}
				{* Draft *}
				{if $can_edit}
					<input type="button" value="Save & Close" style="font-weight:bold; background-color: #5d842e !important; border-color: #5d842e !important;color: #fff !important;padding: 4px 12px;font-size: 13px;line-height: 1.42857143;vertical-align: middle;" onclick="CC_ASSGN.save_form();" />
					<input type="button" value="Confirm" style="font-weight: 600;background-color: #32405b ;border-color: #32405b ;color: #fff ;padding: 4px 12px;font-size: 13px;line-height: 1.42857143;vertical-align: middle;" onclick="CC_ASSGN.save_form(1);" />
					
					{if !is_new_id($form.id)}
						<input type="button" value="Cancel" style="background-color: #D89A11 !important;border-color: #D89A11 !important;color: #fff !important;padding: 4px 12px;font-size: 13px;line-height: 1.42857143;vertical-align: middle;" onclick="CC_ASSGN.cancel_form();" />
					{/if}
				{/if}
			{elseif $form.status eq 1}
				{* Confirmed *}
				
				{if $sessioninfo.branch_id eq $form.branch_id and $form.approved and !$form.printed and ($sessioninfo.level >= $config.doc_reset_level || $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ALLOW_RESET)}
					{* Approved *}
					<input type="button" value="Reset" style="background-color: #D89A11 !important;border-color: #D89A11 !important;color: #fff !important;padding: 4px 12px;font-size: 13px;line-height: 1.42857143;vertical-align: middle;" onclick="CC_ASSGN.cancel_form(1);" />
				{/if}
			{/if}
		{/if}
		<input type="button" value="Close" style="background-color: #e84118;border-color: #e84118;color: #fff;padding: 4px 12px;font-size: 13px;font-weight:bold;line-height: 1.42857143;vertical-align: middle;" onclick="document.location='{$smarty.server.PHP_SELF}'" />
	{/if}
</p>

<script>CC_ASSGN.initialize();</script>

{if !$form.approval_screen}
{include file='footer.tpl'}
{/if}