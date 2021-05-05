{*
1/16/2020 2:51 PM Andy
- Enhanced to show old data history.

06/25/2020 11:29 Sheila
- Updated button css
*}

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
.mark_del{
	text-decoration: line-through;
}
.calendar, .calendar table {
	z-index:100000;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var ATTENDANCE_USER_RECORD = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		this.init_calendar();
		this.init_user_autocomplete();
		
		DAILY_RECORD_DIALOG.initialize();
	},
	init_calendar: function(){
		Calendar.setup({
			inputField     :    "inp_date_from",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_date_from",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true,
			//,
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
			//,
			//onUpdate       :    function(e){
			//	DAILY_ATTENDANCE_REPORT.reload_user_list();
			//}
		});
	},
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
		if(!this.f['date_from'].value){
			alert('Please select Date From.');
			return false;
		}
		
		// Date To
		if(!this.f['date_to'].value){
			alert('Please select Date To.');
			return false;
		}
		
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
		this.f['a'].value = 'ajax_load_user_record';
		this.f['user_id'].value = user_id;
		var THIS = this;
		var params = $(this.f).serialize();
		
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
	del_record_clicked: function(bid, user_id, date){
		if(!confirm('Are you sure?'))	return false;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		var THIS = this;
		var params = {
			a: 'ajax_delete_daily_record',
			branch_id: bid,
			user_id: user_id,
			date: date
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
	},
	// function when users click on view history
	view_history_clicked: function(){
		if(!this.validate_form())	return false;
		
		this.f['a'].value = 'view_history';
		this.f['user_id'].value = $('inp_selected_user_id').value;
		this.f.target = '_blank';
		this.f.submit();
	}
}

var DAILY_RECORD_DIALOG = {
	initialize: function(){
		new Draggable('div_daily_record_dialog',{ handle: 'div_daily_record_dialog_header'});
	},
	open: function(bid, user_id, date){
		$('div_daily_record_dialog_content').update(_loading_);
		var is_new = 0;
		if(!date){
			date = '';
			is_new = 1;
		}
		// Show Dialog
		curtain(true, 'curtain2');
		center_div($('div_daily_record_dialog').show());
				
		var THIS = this;
		var params = {
			a: 'ajax_show_user_daily_record',
			branch_id: bid,
			user_id: user_id,
			date: date,
			is_new: is_new
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
						$('div_daily_record_dialog_content').update(ret['html']);
						
						// Init Calendar
						if(is_new){
							Calendar.setup({
								inputField     :    "inp_date",     // id of the input field
								ifFormat       :    "%Y-%m-%d",      // format of the input field
								button         :    "img_date",  // trigger for the calendar (button ID)
								align          :    "Bl",           // alignment (defaults to "Bl")
								singleClick    :    true,
							});
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
				THIS.close();
			}
		});
	},
	close: function(){
		default_curtain_clicked();
		curtain(false, 'curtain2');
	},
	// function when user changed shift
	shift_changed: function(){
		var shift_id = document.f_daily_record['shift_id'].value;
		if(!shift_id)	return;
		
		var opt = document.f_daily_record['shift_id'].options[document.f_daily_record['shift_id'].selectedIndex];
		//alert(opt);
		
		var start_time = $(opt).readAttribute('start_time');
		var end_time = $(opt).readAttribute('end_time');
		var break_1_start_time = $(opt).readAttribute('break_1_start_time');
		var break_1_end_time = $(opt).readAttribute('break_1_end_time');
		var break_2_start_time = $(opt).readAttribute('break_2_start_time');
		var break_2_end_time = $(opt).readAttribute('break_2_end_time');
		var shift_color = $(opt).readAttribute('shift_color');
		var shift_code = $(opt).readAttribute('shift_code');
		var shift_description = $(opt).readAttribute('shift_description');
		
		document.f_daily_record['start_time'].value = start_time;
		document.f_daily_record['end_time'].value = end_time;
		document.f_daily_record['break_1_start_time'].value = break_1_start_time;
		document.f_daily_record['break_1_end_time'].value = break_1_end_time;
		document.f_daily_record['break_2_start_time'].value = break_2_start_time;
		document.f_daily_record['break_2_end_time'].value = break_2_end_time;
		document.f_daily_record['shift_code'].value = shift_code;
		document.f_daily_record['shift_description'].value = shift_description;
		
		document.f_daily_record['shift_color'].value = shift_color;
		$('div_shift_color').style.backgroundColor = '#'+shift_color;
	},
	// function when user tick / untick delete scan time
	del_scan_changed: function(row_no){
		var c = document.f_daily_record['delete_scan['+row_no+']'].checked;
		
		if(c){
			$('tr_delete_scan_time-'+row_no).addClassName('mark_del');
		}else{
			$('tr_delete_scan_time-'+row_no).removeClassName('mark_del');
		}
	},
	// function to check form
	validate_form: function(){
		if(!check_required_field(document.f_daily_record))	return false;
		
		// Break 1
		if(document.f_daily_record['break_1_start_time'].value.trim() || document.f_daily_record['break_1_end_time'].value.trim()){
			if(!document.f_daily_record['break_1_start_time'].value.trim() || !document.f_daily_record['break_1_end_time'].value.trim()){
				alert('Please enter both Break 1 Start Time and End Time');
				return false;
			}
		}
		
		// Break 2
		if(document.f_daily_record['break_2_start_time'].value.trim() || document.f_daily_record['break_2_end_time'].value.trim()){
			// If no break 1, cannot key in break 2
			if(!document.f_daily_record['break_1_start_time'].value.trim() || !document.f_daily_record['break_1_end_time'].value.trim()){
				alert('Please key in Break 1 first.');
				return false;
			}
		
			if(!document.f_daily_record['break_2_start_time'].value.trim() || !document.f_daily_record['break_2_end_time'].value.trim()){
				alert('Please enter both Break 2 Start Time and End Time');
				return false;
			}
		}
		
		// New Scan Date
		for(var i=1; i<=6; i++){
			var new_scan_date = document.f_daily_record['new_scan_date['+i+']'].value.trim();
			if(new_scan_date){
				var new_scan_time = document.f_daily_record['new_scan_time['+i+']'].value.trim();
				if(!new_scan_time){
					alert('Please key in Scan Time.');
					document.f_daily_record['new_scan_time['+i+']'].focus();
					return false;
				}
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
		var params = (document.f_daily_record).serialize();
		
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
						ATTENDANCE_USER_RECORD.reload_user_records();
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

{* Daily Record Dialog *}
<div id="div_daily_record_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:600px;height:500px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_daily_record_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Daily Record Info</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="SHIFT_DIALOG.close();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_daily_record_dialog_content" style="padding:2px;overflow-y:auto;height:480px;">
	</div>
</div>

<h1>{$PAGE_TITLE}</h1>

<form name="f_a" onSubmit="return false;" class="stdframe">
	<input type="hidden" name="a" value="ajax_load_user_record" />
	<input type="hidden" name="user_id" />
	
	{if $BRANCH_CODE eq 'HQ'}
		<span>
			<b>Branch: </b>
			<select name="branch_id">
				{foreach from=$branch_list key=bid item=b}
					<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
				{/foreach}
			</select>&nbsp;&nbsp;&nbsp;&nbsp;
		</span><br /><br />
	{else}
		<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
	{/if}
	
	<div>
		<b>User: </b>
		{include file='user_autocomplete.tpl'}
	</div>
	
	<span>
		<b>Date: </b>
		<input type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size="12" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
		
		<b>to </b>
		
		<input type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size="12" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;
	</span>
	
	<p>
		<input class="btn btn-primary" type="button" value="Search" onClick="ATTENDANCE_USER_RECORD.search_user_clicked();" />
		<input class="btn btn-primary" type="button" value="View Modification History" onClick="ATTENDANCE_USER_RECORD.view_history_clicked();" />
	</p>
</form>

<br />
<div id="div_user_record">

</div>

<script>ATTENDANCE_USER_RECORD.initialize();</script>
{include file='footer.tpl'}