{*
REVISION HISTORY
================
6/23/2020 04:22 PM Sheila
- Updated button css
*}

{include file='header.tpl'}

{literal}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<script type="text/javascript" src="js/do.js"></script>

<style>
.div_vendor_quotation_cost_container{
	float:left;
	padding-right:10px;
}
.span_vendor_branch{
	background-color: #060;
	color: #fff;
	padding: 0 3px;
	font-weight: bold;
}
.span_vendor_quotation_cost{
	color: blue;
}

.sortable_col a{
	color:black;
}
</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
var process_type = '{$smarty.request.process_type|default:"confirm"}';

{literal}
var DO_MULTI_CONFIRM_CHECKOUT = {
	f_filters: undefined,
	f_a: undefined,
	checked_do_list: {}, // to be used for record what user had clicked on the DO
	initialise: function(){
		this.f_filters = document.f_filters;
		this.f_a = document.f_a;
		this.process_type = process_type;
		
		this.do_type_changed();
	},
	// core function to reload do list
	ajax_reload_do_list: function(is_reload){
		if(is_reload) this.checked_do_list = {}; // unset all the checked do
		$('show_btn').disabled = true;
		$('span_loading_do_list').show();
		$('div_do_list').update('');
		$('div_do_list').hide();
		var THIS = this;

		new Ajax.Request(phpself, {
			parameters:{
				a: 'ajax_reload_do_list',
				do_type: this.f_filters['do_type'].value,
				deliver_to: this.f_filters['deliver_to'].value,
				debtor_id: this.f_filters['debtor_id'].value,
				process_type: process_type
			},
			method: 'post',
			onComplete: function(msg){
				// hide the loading icon
			    $('span_loading_do_list').hide();
				$('show_btn').disabled = false;
			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						$('div_do_list').update(ret['html']);
						$('div_do_list').show();
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
	
	do_checkbox_clicked: function(do_id, branch_id){
		if(!do_id || !branch_id){
			alert('Invalid DO or BRANCH ID');
			return false;
		}
		
		// Get the Element
		var chkbox_do = this.f_a['chk_do_list['+branch_id+']['+do_id+']'];
		
		// Construct Item Key
		var do_key = 'chk_do_list['+branch_id+']['+do_id+']';
		
		// Update checked DO List
		if(chkbox_do.checked == true){
			this.checked_do_list[do_key] = 1;
		}else{
			delete this.checked_do_list[do_key];
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

		//
		var do_id_branch_id = parent_ele.id.split('-')[1]+"|"+parent_ele.id.split('-')[2];
		return do_id_branch_id;
	},
	
	check_all_do: function(obj){
		var do_checkbox_list = $$('#tbl_do_list .do_checkbox');
		
		// Loop Item Row
		for(var i=0,len=do_checkbox_list.length; i < len; i++){
			if(obj.checked == true){
				$(do_checkbox_list[i]).checked = true;
			}else{
				$(do_checkbox_list[i]).checked = false;
			}
			
			// result will return as do_id|branch_id
			var do_id_branch_id = this.get_item_id_by_obj(do_checkbox_list[i]);
			var do_id = do_id_branch_id.split('|')[0];
			var branch_id = do_id_branch_id.split('|')[1];
			
			// store the checkbox status
			this.do_checkbox_clicked(do_id, branch_id);
		}
	},
	
	// Core function to auto check those checked do
	restore_checked_do_list: function(){
		// Get All Item Row
		var tr_item_list = $$('#tbl_do_list tr.tr_item');
		
		// Loop Item Row
		for(var i=0,len=tr_item_list.length; i < len; i++){
			// Get Row Item ID
			
			// result will return as do_id|branch_id
			var do_id_branch_id = this.get_item_id_by_obj(tr_item_list[i]);
			var do_id = do_id_branch_id.split('|')[0];
			var branch_id = do_id_branch_id.split('|')[1];
			
			// Construct Item Key
			var do_key = 'chk_do_list['+branch_id+']['+do_id+']';
			
			// Check if got edit checked the do previously
			if(this.checked_do_list[do_key]){
				// Restore the checkbox
				this.f_a['chk_do_list['+branch_id+']['+do_id+']'].checked = true;
			}
		}
	},
	
	// function when users click on save
	confirm_checkout_clicked: function(){
		if(Object.keys(this.checked_do_list).length == 0){
			alert("Please select a DO before confirm");
			return false;
		}
	
		if(!confirm('Are you sure want to confirm?'))	return false;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		this.f_a['a'].value = 'ajax_confirm_checkout_do';
		var params = $(this.f_a).serialize();
		params += '&'+$H(this.checked_do_list).toQueryString();
		
		//alert(params);return;
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
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						if(ret['is_error'] != undefined && ret['is_error']){
							alert("Found errors while trying to "+this.process_type+" the DO, please refer to the highlighted rows.");
							$('div_do_list').update(ret['html']);
							THIS.restore_checked_do_list();
						}else{
							if(this.process_type == "checkout"){
								alert("All selected DO have been checkout");
							}else{
								alert("All selected DO have been confirmed and sent for approval");
							}
							THIS.ajax_reload_do_list(true);
						}
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
	
	do_type_changed: function(obj){
		var do_type = this.f_filters['do_type'].value;
	
		if(do_type == "credit_sales"){
			// credit sales requires to have debtor filter
			$('span_debtors').show();
			$('span_deliver_to').hide();
		}else if(do_type == "open"){
			// cash sales doesn't need deliver branch and debtor filters
			$('span_debtors').hide();
			$('span_deliver_to').hide();
		}else if(do_type == "transfer"){
			// transfer do requires to have deliver to filter
			$('span_debtors').hide();
			$('span_deliver_to').show();
		}else{
			// does not filter do type, show both filter options
			$('span_debtors').show();
			$('span_deliver_to').show();
		}
	},
	
	load_driver_info: function(){
		// disable load driver info button
		$('load_di_btn').disabled = true;
		$('span_loading_do_list').show();
	
		// construct params
		var params = {
			a: 'ajax_load_driver_info'
		};

		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				// enable back the button
				$('load_di_btn').disabled = false;
				
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';

				try{
					ret = JSON.parse(str); // try decode json object
					$('span_loading_do_list').hide();
					
					if(ret['ok']){ // success
						if(ret['lorry_no'] != "") THIS.f_a['checkout_info[lorry_no]'].value = ret['lorry_no'];
						if(ret['name'] != "") THIS.f_a['checkout_info[name]'].value = ret['name'];
						if(ret['nric'] != "") THIS.f_a['checkout_info[nric]'].value = ret['nric'];
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
	
	use_same_do_date_clicked: function(){
		if(this.f_a['use_same_do_date'].checked == true){
			$('span_use_same_do_date').show();
		}else{
			$('span_use_same_do_date').hide();
		}
		
		// Get All DO Date
		var do_date_list = $$('#tbl_do_list .span_do_date_list');
		var follow_global_do_date_list = $$('#tbl_do_list .span_use_above_do_date');
		
		// Loop DO Date
		for(var i=0,len=do_date_list.length; i < len; i++){
			if(this.f_a['use_same_do_date'].checked == true){
				//do_date_list[i].hide();
				follow_global_do_date_list[i].show();
				do_date_list[i].hide();
			}else{
				follow_global_do_date_list[i].hide();
				do_date_list[i].show();
			}
		}
	}
}

{/literal}
</script>


<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
		<div class="alert alert-danger rounded">	<li> {$e}</li></div>
		{/foreach}
	</ul>
{/if}

<br />

<div class="card mx-3">
	<div class="card-body">
		<form name="f_filters" method="post" onSubmit="return false;" class="stdframe" style="background-color:#fff;" action="{$smarty.server.PHP_SELF}">
			<div class="row">
				<div class="col-md-3">
					<b class="form-label">DO Type:</b>
					<select class="form-control" name="do_type" onchange="DO_MULTI_CONFIRM_CHECKOUT.do_type_changed();">
						<option value="">-- All --</option>
						{foreach from=$do_type_list key=do_type item=do_type_desc}
							<option value="{$do_type}" {if $smarty.request.do_type eq $do_type}selected{/if}>{$do_type_desc}</option>
						{/foreach}
					</select>
					
				</div>
				
				<div class="col-md-3">
					<span id="span_deliver_to">
						<b class="form-label">Deliver To:</b>
						<select class="form-control" name="deliver_to">
							<option value="">-- All --</option>
							{foreach from=$branches key=bid item=r}
								<option value="{$r.id}">{$r.code}</option>				
							{/foreach}
						</select>
					</span>
				</div>
				
				<div class="col-md-3">
					<span id="span_debtors" {if !$form.do_type || $form.do_type ne "credit_sales"}style="display:none;"{/if}>
						<b class="form-label">Debtor:</b>
						<select class="form-control" name="debtor_id">
							<option value="">-- All --</option>
							{foreach from=$debtor_list key=debtor_id item=r}
								<option value="{$debtor_id}" {if $smarty.request.debtor_id eq $debtor_id}selected{/if}>{$r.description}</option>
							{/foreach}
						</select>
						
					</span>
				</div>
				
				<div class="col-md-3"><input class="btn btn-primary mt-4" type="button" name="submit" onclick="DO_MULTI_CONFIRM_CHECKOUT.ajax_reload_do_list(true);" id="show_btn" value="Show" /></div>
			</div>
		</form>
	</div>
</div>

<br />

<form name="f_a" method="post" onSubmit="return false;" action="{$smarty.server.PHP_SELF}">
	<input type="hidden" name="a" value="ajax_confirm_checkout_do" />
	<input type="hidden" name="process_type" value="{$smarty.request.process_type}" />
	<span id="span_loading_do_list" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	<div id="div_do_list" class="stdframe" style=" display:none;">
		{include file='do.multi_confirm_checkout.do_list.tpl'}
	</div>
</form>


<script>
DO_MULTI_CONFIRM_CHECKOUT.initialise();
</script>

{include file='footer.tpl'}