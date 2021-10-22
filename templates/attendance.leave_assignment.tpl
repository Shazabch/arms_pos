{include file='header.tpl'}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
{literal}
.calendar, .calendar table {
	z-index:100000;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var ATTENDANCE_LEAVE_ASSIGN = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		//this.init_calendar();
		this.init_user_autocomplete();
		
		//DAILY_RECORD_DIALOG.initialize();
	},
	/*init_calendar: function(){
		Calendar.setup({
			inputField     :    "inp_date_from",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_date_from",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true,
			//onUpdate       :    function(e){
			//	DAILY_ATTENDANCE_REPORT.reload_user_list();
			//}
		});
		
		Calendar.setup({
			inputField     :    "inp_date_to",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_date_to",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true,
			//onUpdate       :    function(e){
			//	DAILY_ATTENDANCE_REPORT.reload_user_list();
			//}
		});
	}*/
	// core function to init user autocomplete
	init_user_autocomplete: function(){
		USER_AUTOCOMPLETE.initialize();
	},
	// core function to validate form
	validate_form: function(){
		// User
		if($('inp_selected_user_id').value<=0){
			alert('Please search and select a user.');
			USER_AUTOCOMPLETE.focus_inp_search_username();
			return false;
		}
		
		// Date From
		/*if(!this.f['date_from'].value){
			alert('Please select Date From.');
			return false;
		}
		
		// Date To
		if(!this.f['date_to'].value){
			alert('Please select Date To.');
			return false;
		}*/
		
		return true;
	},
	// function when user click on button search
	search_user_clicked: function(){
		this.reload_user_records();
	},
	// core function to reload user list
	reload_user_records: function(){
		if(!this.validate_form())	return;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		var user_id = $('inp_selected_user_id').value;
		var THIS = this;
		var params = $(this.f).serialize()+'&user_id='+user_id;
		
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
	                if(ret['ok'] && ret['html']){ // success
						// Update HTML
						$('div_user_record').update(ret['html']);
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
	// function when user click on delete record
	del_record_clicked: function(guid){
		if(!confirm('Are you sure?'))	return false;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		var THIS = this;
		var params = {
			a: 'ajax_delete_leave_record',
			guid: guid
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
						THIS.reload_user_records();
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
	}
};

var USER_LEAVE_DIALOG = {
	initialize: function(){
		new Draggable('div_user_leave_dialog',{ handle: 'div_user_leave_dialog_header'});
	},
	open: function(user_id, guid){
		$('div_user_leave_dialog_content').update(_loading_);
		var is_new = 0;
		if(!guid){
			is_new = 1;
			guid = '';
		}
		// Show Dialog
		curtain(true, 'curtain2');
		center_div($('div_user_leave_dialog').show());
				
		var THIS = this;
		var params = {
			a: 'ajax_show_user_leave_record',
			user_id: user_id,
			is_new: is_new,
			branch_id: $('tmp_bid').value,
			guid: guid
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
						$('div_user_leave_dialog_content').update(ret['html']);
						
						// Init Calendar
						Calendar.setup({
							inputField     :    "inp_date_from",     // id of the input field
							ifFormat       :    "%Y-%m-%d",      // format of the input field
							button         :    "img_date_from",  // trigger for the calendar (button ID)
							align          :    "Bl",           // alignment (defaults to "Bl")
							singleClick    :    true,
							onUpdate       :    function(e){
								if($('inp_date_to').value==''){
									$('inp_date_to').value = $('inp_date_from').value;
								}
							}
						});
						
						Calendar.setup({
							inputField     :    "inp_date_to",     // id of the input field
							ifFormat       :    "%Y-%m-%d",      // format of the input field
							button         :    "img_date_to",  // trigger for the calendar (button ID)
							align          :    "Bl",           // alignment (defaults to "Bl")
							singleClick    :    true,
							//onUpdate       :    function(e){
							//	DAILY_ATTENDANCE_REPORT.reload_user_list();
							//}
						});
						
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
		if(!check_required_field(document.f_leave_record))	return false;
		
		if(strtotime(document.f_leave_record['date_from'].value) > strtotime(document.f_leave_record['date_to'].value)){
			alert('Date To is ealier than Date From');
			return false;
		}
			
		return true;
	},
	// function when user click on button save
	save_clicked: function(){
		if(!this.validate_form())	return;
		this.set_action_button(false);
		$('btn_save').value = 'Saving...';
		
		var THIS = this;
		var params = (document.f_leave_record).serialize();
		
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
						ATTENDANCE_LEAVE_ASSIGN.reload_user_records();
						// Update HTML
						alert('Update Successfully');
						THIS.close();
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

{* User Leave Dialog *}
<div id="div_user_leave_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:600px;height:200px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_user_leave_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Leave Record</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="SHIFT_DIALOG.close();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_user_leave_dialog_content" style="padding:2px;overflow-y:auto;height:480px;">
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
		<form name="f_a" onSubmit="return false;" class="stdframe">
			<input type="hidden" name="a" value="ajax_load_user_leave" />
			
			{if $BRANCH_CODE eq 'HQ'}
				<span>
					<b class="form-label">Branch: </b>
					<select class="form-control" name="branch_id">
						<option value="">-- All --</option>
						{foreach from=$branch_list key=bid item=b}
							<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
						{/foreach}
					</select>&nbsp;&nbsp;&nbsp;&nbsp;
				</span>
			{else}
				<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
			{/if}
			
			<div>
				<b class="form-label">User: </b>
				{include file='user_autocomplete.tpl'}
			</div>
				
			<p>
				<input type="button" class="btn btn-primary" value="Search" onClick="ATTENDANCE_LEAVE_ASSIGN.search_user_clicked();" />
			</p>
		</form>
	</div>
</div>

<br />
<div id="div_user_record">

</div>

<script>ATTENDANCE_LEAVE_ASSIGN.initialize();</script>
{include file='footer.tpl'}