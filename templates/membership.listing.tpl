{*
3/16/2010 5:50:42 PM Andy
- Add Expiry & Date filter for membership listing

8/11/2010 11:53:55 AM Justin
- Added a new date filter called "Birth Date".
- Allows user to filter by date from/to based on the member's DOB.
- Re-format DOB from integer become date format during display.

8/25/2010 6:06:58 PM Justin
- Added 2 new columns called "Last Renew Branch" and "Last Purchase Branch".
- Disabled the checking of Branch filter that only can filter by HQ.

11/30/2010 6:54PM yinsee
- added "SMS Broadcast" feature 

3/23/2011 3:21:28 PM Justin
- Added points update filter.

6/29/2011 12:30:57 PM Andy
- Add print preview page for membership printing.
- Add can choose field to export excel.

7/19/2011 11:55:21 AM Justin
- Taken out the membership listing table and place under one template.
- Added the live sorting functions.

8/17/2011 2:10:34 PM Justin
- Modified the export excel fields selection to display by using array loop.

11/9/2011 5:41:32 PM Justin
- Added new filter "Age".

11/10/2011 11:43:16 AM Andy
- Change "SMS Broadcast" to use privilege checking instead of only admin level.

11/14/2011 11:17:43 AM Justin
- Added new filter "Gender".

11/28/2011 4:24:32 PM Justin
- Added new div to show up while user click on "SMS Broadcast".
- Added sms length count function to onlive update the characters typed, show bold and red counting while it is overlength.
- Modified to show confirmation window for user of those sms which less than 20 characters or more than 140 characters instead of terminate and disallow user to send sms for these conditions occur.

12/9/2011 12:29:32 PM Justin
- Modified the characters count from maximum 140 becomes 70 since isms calculate every 70 characters = 1 sms.
- Modified to show live update for sms credit used.
- Modified to show loading msg while system is triggering sms balance from isms.com.my.
- Aligned the sms window into center of the browser.
- Fixed the SMS length that calculation should be maximum 69 instead of 70 per sms.
- Prefix the SMS credits by 1, 2 and 3 for 1~69, 70~135 and 136~200.
- Added a "?" that is clickable to show info regarding SMS.
- Added maxlength onto textarea for disable user to key in after 200 characters.

12/27/2011 yinsee
- auto detect and calculate accurate SMS costing, no limit

1/3/2012 11:22:32 AM Yinsee
- Fixed the calculation for "RM0.00 " to count as 7 instead of 8.
- Fixed the enter calculation to count as 1 character instead of 2.

6/26/2012 2:59:23 PM Justin
- Enlarged the width of export excel window.

9/13/2012 3:59 PM Justin
- Enhanced to provide info for "*" indication.
- Enhanced to mark * for points while point is not up to date.
- Enhanced to have a link to allow user click and mark member points recalculate when user has privilege.

1/21/2013 4:11 PM Andy
- Add can filter member type and staff type in membership listing.

12/26/2013 6:05 PM Justin
- Enhanced to show notes for sms broadcast.

1/16/2014 5:55 PM Justin
- Enhanced to show SMS broadcast in progress info.

11/19/2015 9:20 AM Qiu Ying
- remove the config of membership export and print

12/08/2015 2:49 PM DingRen
- add iSMS info that when the cron will take place.

05/30/2016 10:25 Edwin
- Rearrange code structure.

9/9/2016 2:19 PM Andy
- Fixed membership listing searching bug.

12/4/2019 3:52 PM Andy
- Enhanced to can filter member by mobile registered yes/no and mobile register date from/to.
- Enhanced to check form_filter error.
- Enhanced to allow users to select which fields to display / print / export.

1/13/2020 3:38 PM William
- Enhanced to add remark for new search type "Phone".

2/12/2020 11:30 AM William
- Enhanced to add new birthday month and birthday day filter.

06/29/2020 03:15 PM Sheila
- Updated button css.

9/23/2020 12:07 PM William
- Enhanced point filter able to enter 0 value. 

02/17/2021 5:31 AM Rayleen
- Add button to Export CSV File
- Change "Export" to "Export Excel" 
*}

{include file=header.tpl}

{if !$no_header_footer}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

{literal}
<style>
#clr_in_red{
	color:#f00;
}

.progress_bar{
	vertical-align:bottom !important;
}
</style>
{/literal}

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<script type="text/javascript" src="js/jsProgressBarHandler.js"></script>

<script type="text/javascript">
var php_self = '{$smarty.server.PHP_SELF}';
var total_row = int('{$total_row}');
var sms_credit = 0;
var curr_sort_order = new Array();
var curr_sort = new Array();
var progress_bar_list = [];
var got_membership_mobile = {if $config.membership_mobile_settings}1{else}0{/if};

{literal}
function date_filter_changed(){
	if(document.f_a['date_filter'].value=='')   $('span_date_filter').hide();
	else	$('span_date_filter').show();
}

function submit_form(a){
	if(a=='refresh'||!a){
        document.f_a.target = '';
        document.f_a['a'].value = 'member_list';
		if(a=='refresh'){
			// change back to first page when is refresh
			if(document.f_a['s']){
				document.f_a['s'].value ='0';
			}
		}
	}else if(a=='print'){
        document.f_a.target = '_blank';
		document.f_a['a'].value = 'print_member_list';
	}else if(a=='print_mailing_list'){
        document.f_a.target = '_blank';
		document.f_a['a'].value = 'print_mailing_list';
	}else if(a == 'export_excel'){
        document.f_a.target = '_blank';
		document.f_a['a'].value = 'export_excel';
	}else if(a == 'export_csv'){
        document.f_a.target = '_blank';
		document.f_a['a'].value = 'export_csv';
	}
	
	document.f_a.submit();
}

function send_sms()
{
	$("span_sms_loading").update(_loading_+" Please Wait.");
	$('div_sms').show();
	$("div_sms_menu").hide();
	curtain(true);
	// get credit
	new Ajax.Request(
		"membership.listing.php",
		{
		    method: 'get',
			parameters: 'a=sms_get_credit',
			onComplete:function(ret) {
				sms_credit = parseInt(ret.responseText);
				if (isNaN(sms_credit)) {
					alert('Failed to check credit balance');
					curtain(false);
					$('div_sms').hide();
					return false;
				}
				/*if (sms_credit < total_row)
				{
					alert('You have insufficient credit, please top up.\nYour current balance: '+sms_credit+', need at least '+total_row);
					curtain(false);
					$('div_sms').hide();
					return false;
				}*/

				$('div_sms').style.zIndex = 10000;
				$('msg').value = "";
				$('div_sms_credit_balance').update("Credit Balance: "+sms_credit);
				$('div_sms_length_dis').update("Character Count: 0 (0 credits)");
				$("span_sms_loading").update();
				$("div_sms_menu").show();
				center_div('div_sms');
			}
	});
}

var have_unicode = false;
var sms_bal_count = 1;

function sms_length_handler(){
	return calculate_text_length($('msg'));
	/*var msg = $('msg').value;
	have_unicode = false;
	for (var i = 0, n = msg.length; i < n; i++) {
		if (msg[i].charCodeAt() > 255) {
			have_unicode = true; 
			break;
		}
	}
	var ret = msg.length;
	if (have_unicode && ret > 63) ret += 7;
	if (!have_unicode && ret > 153) ret += 7;	
	return ret;*/
}

function sms_handler(){
	var msg = $('msg').value;
	
	var sms_length = sms_length_handler();
	have_unicode = contain_unicode(msg);
	
	if (msg==undefined || sms_length < (have_unicode?30:100)) {
		if(!confirm('Your message is too short. Are you sure want to send?')) return false;
	}

	if (sms_bal_count > 5) 
	{
		alert('Maximum credit per SMS allowed is 5, please shorten your message');
		return false;
	}
	
	if ((total_row*sms_bal_count) > sms_credit)
	{
		alert('You have insufficient credit, please top up.\nYour current balance: '+sms_credit+', credits needed: '+(sms_bal_count*total_row));
		return false;
	}
	
	document.f_a['a'].target = '';
	document.f_a['sms'].value = msg;
	document.f_a['a'].value = 'send_sms';
	document.f_a.submit();
}

function sms_length_upd(){
	var sms_length = sms_length_handler();
	have_unicode = contain_unicode($('msg').value);
	
	if (have_unicode)
		sms_bal_count = (sms_length>63) ? Math.ceil(sms_length/62) : 1;
	else
		sms_bal_count = (sms_length>153) ? Math.ceil(sms_length/152) : 1;
	
	if (sms_bal_count > 5)
		$('div_sms_length_dis').update("Maximum credit per SMS allowed is 5, please shorten your message");
	else
		$('div_sms_length_dis').update("Character Count: "+(sms_length)+" ("+sms_bal_count+" credits)");
}

function toggle_export_field(ele){
	var c = ele.checked;
	
	$(document.f_a).getElementsBySelector('input.chx_export_field').each(function(chx){
		chx.checked = c;
	});
}

function check_export_excel(){
	// check selected data
	var chx_export_field = $(document.f_a).getElementsBySelector('input.chx_export_field');
	var passed = false;
	for(var i=0; i<chx_export_field.length; i++){
		if(chx_export_field[i].checked){
			passed = true;
			break;
		}
	}
	
	if(!passed){
		alert('Please at least select 1 data to export.');
		return false;
	}
	
	submit_form('export_excel');
}

function check_export_csv(){
	// check selected data
	var chx_export_field = $(document.f_a).getElementsBySelector('input.chx_export_field');
	var passed = false;
	for(var i=0; i<chx_export_field.length; i++){
		if(chx_export_field[i].checked){
			passed = true;
			break;
		}
	}
	
	if(!passed){
		alert('Please at least select 1 data to export.');
		return false;
	}
	
	submit_form('export_csv');
}

function SetCookie(cookieName,cookieValue,nDays) {
 var today = new Date();
 var expire = new Date();
 if (nDays==null || nDays==0) nDays=1;
 expire.setTime(today.getTime() + 3600000*24*nDays);
 document.cookie = cookieName+"="+escape(cookieValue)
                 + ";expires="+expire.toGMTString();
}

function sort_reloadTable(col,grp)
{
	if (curr_sort[grp]==undefined || curr_sort[grp] != col)
	{
		curr_sort[grp] = col;
		curr_sort_order[grp] = 'asc';
	}
	else
	{
		curr_sort_order[grp] =  (curr_sort_order[grp] == 'asc' ? 'desc' : 'asc' );
	}
	SetCookie('_tbsort_'+grp, curr_sort[grp],1);
	SetCookie('_tbsort_'+grp+'_order', curr_sort_order[grp],1);

	// ajax reload
	$('span_loading').update('<img src=/ui/clock.gif align=absmiddle> Sorting in process...');
	
	document.f_a['a'].value = 'member_list';
	new Ajax.Updater('div_content',php_self+"?sort=1",{
		parameters: document.f_a.serialize(),
		method: 'post',
		evalScripts: true,
		onComplete: function(){
            $('span_loading').update('');
		}
	});
}

var REFRESH_INTERVAL = 3000;
function reload_sms_broadcast(){
	 
	//if(sms_timeout) clearTimeout(sms_timeout);
	// check if the progress is already done, then no need to ajax call 
	var have_in_progress_sms = false;
	$$('.sms_progress').each(function(inp){
		var tmp_id = $(inp).readAttribute('sms_id');
		var tmp_bid = $(inp).readAttribute('sms_bid');
		
		if(inp.value != 100) have_in_progress_sms = true;
	});
	
	if(have_in_progress_sms == false) return false;
	
	new Ajax.Request(
	    php_self,
	    {
			method:'post',
			parameters: 'a=ajax_reload_sms_broadcast',
		    evalScripts: true,
			onFailure: function(msg) {
				alert('Failed to update SMS Broadcast Status, please try again later.');
			},
			onSuccess: function(msg) {
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';

				try{
					//ret = JSON.parse(str); // try decode json object
					eval("var json = "+msg.responseText);
					setTimeout('reload_sms_broadcast()', REFRESH_INTERVAL);
					
					for(var tr_key in json){
						if(json[tr_key]['id'] > 0){ // success
							var tmp_id = json[tr_key]['id'];
							var tmp_bid = json[tr_key]['branch_id'];
							var curr_progress_bar = "progress_bar"+tmp_id+"_"+tmp_bid;
							
							// set latest percentage
							if($(curr_progress_bar) != undefined){
								progress_bar_list[curr_progress_bar].setPercentage(json[tr_key]['progress_perc']);
								$("progress_perc"+tmp_id+"_"+tmp_bid).value = float(json[tr_key]['progress_perc']);
							}
						}
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
	    	}
		}
	);
}


var MEMBER_LISTING = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		// init calendar
		this.init_calendar();
		
		// sms diaglog
		new Draggable('div_sms');
		center_div('div_sms');
		
		PN_DIALOG.initialize();
	},
	// function to init calendar
	init_calendar: function(){
		// Date
		Calendar.setup({
			inputField     :    "date_from",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "t_added1",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
		});

		Calendar.setup({
			inputField     :    "date_to",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "t_added2",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
		});
		
		if(got_membership_mobile){
			// Mobile Register date
			Calendar.setup({
				inputField     :    "inp_mobile_registered_date_from",     // id of the input field
				ifFormat       :    "%Y-%m-%d",      // format of the input field
				button         :    "img_mobile_registered_date_from",  // trigger for the calendar (button ID)
				align          :    "Bl",           // alignment (defaults to "Bl")
				singleClick    :    true
			});

			Calendar.setup({
				inputField     :    "inp_mobile_registered_date_to",     // id of the input field
				ifFormat       :    "%Y-%m-%d",      // format of the input field
				button         :    "img_mobile_registered_date_to",  // trigger for the calendar (button ID)
				align          :    "Bl",           // alignment (defaults to "Bl")
				singleClick    :    true
			});
		}
	},
	// function when user change mobile registered
	mobile_registered_changed: function(){
		var v = this.f['mobile_registered'].value;
		
		if(v == 'y'){
			$('div_mobile_filter').show();
		}else{
			$('div_mobile_filter').hide();
		}
	}
}

var PN_DIALOG = {
	f: undefined,
	initialize: function(){
		new Draggable('div_pn_dialog',{ handle: 'div_pn_dialog_header'});
	},
	open: function(){
		if(!document.f_a['mobile_registered'] || document.f_a['mobile_registered'].value != 'y'){
			alert('Mobile Registered must be selected as YES.');
			return;
		}
		
		// Show Loading
		$('div_pn_dialog_content').update(_loading_);
		
		// Show Dialog
		// Show Dialog
		curtain(true, 'curtain2');
		center_div($('div_pn_dialog').show());
		
		var THIS = this;
		var params = {
			a: 'ajax_show_pn'
		}
		
		new Ajax.Request(php_self, {
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
						$('div_pn_dialog_content').update(ret['html']);
						center_div('div_pn_dialog_content');
						document.f_pn['pn_title'].focus();
						THIS.f = document.f_pn;
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
	// function when user key in something in push notification text
	pn_length_calculate: function(){		
		var pn_length = calculate_text_length(this.f['pn_msg']);
		
		$('div_pn_length_count').update(pn_length);
	},
	// core function to check form before send
	validate_form: function(){
		// Title
		if(!this.f['pn_title'].value.trim()){
			alert('Push Title is Empty');
			this.f['pn_title'].focus();
			return false;
		}
		
		// Message
		if(!this.f['pn_msg'].value.trim()){
			alert('Push Text is Empty');
			this.f['pn_msg'].focus();
			return false;
		}
		
		return true;
	},
	// function when user click on button send push notification
	send_pn_clicked: function(){
		if(!this.validate_form())	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		// Reset Value
		$('progress_pn').value = 0;
		$('progress_pn').max = 100;
		this.set_pn_progress_value(0,100, 'Starting...');
		
		// Show Progress Bar
		this.show_pn_progress(true);
		
		// Disable action button
		this.set_action_btn(false);
		
		// Disable Form
		$(this.f).disable();
		
		var THIS = this;
		var params = $(document.f_a).serialize();
		params += '&pn_title='+encodeURIComponent(this.f['pn_title'].value);
		params += '&pn_msg='+encodeURIComponent(this.f['pn_msg'].value);
		params += '&screen_tag='+this.f['screen_tag'].value;
		params += '&a=ajax_send_pn';
		
		new Ajax.Request(php_self, {
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
	                if(ret['ok'] && ret['pn_guid']){ // success
						THIS.f['pn_guid'].value = ret['pn_guid'];
						
						// Monitor Status after 1s
						setTimeout(function(){ 
							THIS.monitor_pn_status();
						}, 1000);
						
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
				
				// Enable Form
				$(THIS.f).enable();
		
				// Hide Progress Bar
				THIS.show_pn_progress(false);
				
				// Disable action button
				THIS.set_action_btn(true);
			}
		});
	},
	// Core function to show / hide push notification
	show_pn_progress: function(is_show){
		if(is_show){
			$('div_pn_progress').style.visibility = '';
		}else{
			$('div_pn_progress').style.visibility = 'hidden';
		}
	},
	// Core function to set progress value and label
	set_pn_progress_value: function(v, max, label){
		if(v>=0){
			$('progress_pn').value = v;
		}
		if(max>=0){
			$('progress_pn').max = max;
		}
		$('span_pn_progress_label').update(label);
	},
	// Core function to set progress done
	set_pn_progress_completed: function(success_count, total_count){
		$('progress_pn').value = $('progress_pn').max = total_count;
		$('span_pn_progress_label').update('Done!');
	},
	// Core function to enable / disable action button
	set_action_btn: function(is_enable){
		$$('#p_action input').each(function(inp){
			inp.disabled = !is_enable
		})
	},
	// Core function to monitor push notification status
	monitor_pn_status: function(){
		var pn_guid = this.f['pn_guid'].value;
		var THIS = this;
		var params = {
			a: 'ajax_monitor_pn',
			pn_guid: pn_guid
		};
		
		new Ajax.Request(php_self, {
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
						if(ret['err_msg']){
							// Error
							THIS.set_pn_progress_value(-1,-1, "<font color=red>"+ret['err_msg']+"</font>");
						}else{
							// Completed
							if(ret['completed']){
								THIS.set_pn_progress_completed(ret['success_count'], ret['total_count']);
								setTimeout(function(){ 
									alert("Send Finished.\n\n"+ret['success_count']+" / "+ret['total_count']+" success");
									THIS.close();
								}, 100);
							}else{
								// Update Progress
								if(ret['total_count'] && ret['curr_count']){
									THIS.set_pn_progress_value(ret['curr_count'], ret['total_count'], ret['curr_count']+" / "+ret['total_count']);
								}
								// Check Again Status in 2s
								setTimeout(function(){ THIS.monitor_pn_status(); }, 2000);
							}
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
	}
}
{/literal}
</script>

{/if}

<div id="div_sms" class="curtain_popup" style="display:none; width:340px;">
	<span id="span_sms_loading"></span>
	<div id="div_sms_menu" style="display:none;">
		<h3>SMS Broadcast</h3>
		<p><b>
			Please note that there would be no refund if the message has been failed to send out due to the following reasons :-<br />
			- Mobile is out of coverage for more than 24 hours.<br />
			- Mobile is switched off for more than 24 hours.<br />
			- Mobile storage is full for more than 24 hours.<br />
			- Mobile number does not exist or is barred by telco<br />
		</b></p>
		<textarea id="msg" cols="40" rows="10" onkeyup="sms_length_upd();" maxlength="760"></textarea>
		
		<p>
			<span id="div_sms_credit_balance"></span> 
			<span id="div_sms_length_dis"></span>
			[<a href="javascript:void(0)" onclick="alert('{$LANG.MEMBERSHIP_SMS_INFO|escape:javascript}');">?</a>]
		</p>
		<p align="center"><button onclick="sms_handler();">Send SMS</button></p>
	</div>
</div>

{* Push Notification Dialog *}
<div id="div_pn_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:550px;height:470px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_pn_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Send Push Notification</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="SALES_AGENT_PHOTO_DIALOG.close();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_pn_dialog_content" style="padding:2px;">		
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
	<div class="alert alert-danger mx-3 rounded">
		<ul class="errmsg">
			{foreach from=$err item=e}
				<li> {$e}</li>
			{/foreach}
		</ul>
	</div>
{/if}

{if $sessioninfo.privilege.MEMBERSHIP_ALLOW_SMS && $config.isms_user && $sms_info}
	<a href="javascript:void(togglediv('div_sms_progress'))">Show SMS Broadcast Status</a>
	<div id="div_sms_progress" class="stdframe" style="background:#fff; display:none;">
		<table width="100%">
		{assign var=row_count value=-1}
		{foreach from=$sms_info name=sms key=row item=r}
			<tr>
				<td>
					<b>Message: </b><br />
					{$r.msg}<br />
				</td>
			</tr>
			<tr valign="top">
				{assign var=row value=$smarty.foreach.sms.iteration}
				{assign var=progressbar_name value=progress_bar`$r.id`_`$r.branch_id`}
				<td>
					<span class="progress_bar" id="progress_bar{$r.id}_{$r.branch_id}">[ Loading Progress Bar ]</span>
					<input type="hidden" class="sms_progress" id="progress_perc{$r.id}_{$r.branch_id}" sms_id="{$r.id}" sms_bid="{$r.branch_id}" value="{$r.progress_perc}" />
					<script type="text/javascript">
						var id = '{$r.id}';
						var bid = '{$r.branch_id}';
						var progress_perc = '{$r.progress_perc|number_format:0}';

						progress_bar_list['{$progressbar_name}'] {literal} = new JS_BRAMUS.jsProgressBar(
							$("progress_bar"+id+"_"+bid),
							progress_perc,
							{
								barImage	: Array(
									'ui/percentImage_back4.png',
									'ui/percentImage_back3.png',
									'ui/percentImage_back2.png',
									'ui/percentImage_back1.png'
								)
							}
						);
						{/literal}
					</script>
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
		{/foreach}
		</table>
	</div>
	<br />
{/if}

<iframe style="visibility:hidden;" width="1" height="1" name="ifprint"></iframe>
<form name="f_a" class="noprint" style="line-height:24px" method="post">
<input name=refresh type=hidden value=1>
<input name=sms type=hidden value=''>
<input name="a" type=hidden value="member_list" />
<div class="card mx-3">
	<div class="card-body">
		<div class=stdframe >
			<div class="row">
				<div class="col-md-3">
					<b class="form-label">Branch</b>
			<select class="form-control" name="branch_id">
				<option value="">-- All --</option>
				{foreach from=$branches item=branch_id key=branch}
					<option value="{$branch_id}" {if $smarty.request.branch_id eq $branch_id}selected{/if}>{$branch}</option>
				{/foreach}
			</select>
				</div>
			<div class="col-md-3">
				<b class="form-label">Race</b>
			<select class="form-control" name="race">
				<option value="">-- All --</option>
				{foreach from=$races item=race}
					<option value="{$race}" {if $smarty.request.race eq $race}selected{/if}>{$race}</option>
				{/foreach}
			</select>
			</div>
			<div class="col-md-3">
				<b class="form-label">National</b>
			<select class="form-control" name="national">
				<option value="">-- All --</option>
				{foreach from=$nationals item=national}
					<option value="{$national}" {if $smarty.request.national eq $national}selected{/if}>{$national}</option>
				{/foreach}
			</select>
			</div>
			<div class="col-md-3">
				<b class="form-label">City</b>
			<select class="form-control" name="city">
				<option value="">-- All --</option>
				{foreach from=$cities item=city}
					<option value="{$city}" {if $smarty.request.city eq $city}selected{/if}>{$city}</option>
				{/foreach}
			</select>
			</div>
			<div class="col-md-3">
				<b class="form-label">State</b>
			<select class="form-control" name="state">
				<option value="">-- All --</option>
				{foreach from=$states item=state}
					<option value="{$state}" {if $smarty.request.state eq $state}selected{/if}>{$state}</option>
				{/foreach}
			</select>
			</div>
			<div class="col-md-3">
				<b class="form-label">Postcode</b>
			<select class="form-control" name="postcode">
				<option value="">-- All --</option>
				{foreach from=$postcode item=p}
					<option value="{$p}" {if $smarty.request.postcode eq $p}selected{/if}>{$p}</option>
				{/foreach}
			</select>
			</div>
			<div class="col-md-3">
				<b class="form-label">Date Filter</b>
			<select class="form-control" name="date_filter" onChange="date_filter_changed();">
				<option value="">No Filter</option>
				{foreach from=$date_filter key=k item=df}
					<option value="{$k}" {if $smarty.request.date_filter eq $k}selected{/if}>{$df}</option>
				{/foreach}
			</select>
			</div>
			</div>
			<span id="span_date_filter" style="{if !$smarty.request.date_filter}display:none;{/if}">
				<div class="row">
				<div class="col-md-3">
					<b class="form-label">From</b> 
					<div class="form-inline">
						<input class="form-control" size="20" type="text" name="date_from" value="{$smarty.request.date_from}" id="date_from">
					<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
					</div>
				</div>
				
				<div class="col-md-3">
					<b class="form-label">To</b>
					<div class="form-inline">
						<input class="form-control" size="20" type="text" name="date_to" value="{$smarty.request.date_to}" id="date_to">
						<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
					</div>
				</div>
				</div>
				
			</span>
			
			<p>
				<div class="row">
					<div class="col-md-3">
						<div class="form-inline">
							<b class="form-label">Search by </b>(<a href="#" onClick="alert('Search by Phone will including Phone (Home), Phone (Office) and Phone (Mobile).')">?</a>)
							<select class="form-control" name="search_type">
							{foreach from=$search_type key=l item=st}
								<option value="{$l}" {if $smarty.request.search_type eq $l}selected{/if}>{$st}</option>
							{/foreach}
						</select>
						<input class="form-control" name="search_value" value="{$smarty.request.search_value}">
						</div>
					</div>
					<div class="col-md-3">
						<b class="form-label">Terminated</b>
				<select class="form-control" name="terminated">
					<option value=''>-- All --</option>
					<option value='y' {if $smarty.request.terminated eq 'y'}selected{/if}>Yes</option>
					<option value='n' {if $smarty.request.terminated eq 'n'}selected{/if}>No</option>
				</select>
					</div>
				<div class="col-md-3">
					<b class="form-label">Blocked</b>
				<select class="form-control" name="blocked">
					<option value=''>-- All --</option>
					<option value='y' {if $smarty.request.blocked eq 'y'}selected{/if}>Yes</option>
					<option value='n' {if $smarty.request.blocked eq 'n'}selected{/if}>No</option>
				</select>
				</div>
				<div class="col-md-3">
					<b class="form-label">Verified</b>
				<select class="form-control" name="verified">
					<option value=''>-- All --</option>
					<option value='y' {if $smarty.request.verified eq 'y'}selected{/if}>Yes</option>
					<option value='n' {if $smarty.request.verified eq 'n'}selected{/if}>No</option>
				</select>
				</div>
				<div class="col-md-3">
					<b class="form-label">Expiry</b>
				<select class="form-control" name="expiry">
					<option value=''}>-- All --</option>
					<option value='y' {if $smarty.request.expiry eq 'y'}selected{/if}>Yes</option>
					<option value='n' {if $smarty.request.expiry eq 'n'}selected{/if}>No</option>
				</select>
				</div>
				<div class="col-md-3">
					<b class="form-label">Point From</b>
				<input class="form-control" type="text" name="point_from" value="{$smarty.request.point_from}" size="5" onChange="mi(this);" /> 
					
				</div>
				<div class="col-md-3">
					<b class="form-label">To</b>
				<input class="form-control" type="text" name="point_to" value="{$smarty.request.point_to}" size="5" onChange="mi(this);" />
				</div>
				</div>
			</p>
			<p>
				<div class="row">
					<div class="col-md-3">
						<b class="form-label">Gender</b>
				<select class="form-control" name="gender">
					<option value=''>-- All --</option>
					<option value='M' {if $smarty.request.gender eq 'M'}selected{/if}>Male</option>
					<option value='F' {if $smarty.request.gender eq 'F'}selected{/if}>Female</option>
				</select>
					</div>
				<div class="col-md-3">
					<b class="form-label">Age From</b>
				<input class="form-control" type="text" name="age_from" value="{$smarty.request.age_from}" size="5" onChange="miz(this);" /> 
				
				</div>
				<div class="col-md-3">
					<b class="form-label">To</b>
				<input class="form-control" type="text" name="age_to" value="{$smarty.request.age_to}" size="5" onChange="miz(this);" />
				</div>
			
				{if $config.membership_type}
					<div class="col-md-3">
						<b class="form-label">Member Type</b>
					<select class="form-control" name="member_type">
						<option value="">-- All --</option>
						{foreach from=$config.membership_type key=member_type item=member_type_label}
							<option value="{$member_type}" {if $smarty.request.member_type eq $member_type}selected {/if}>{$member_type_label}</option>
						{/foreach}
					</select>
					</div>
				{/if}
				{if $config.membership_enable_staff_card}
					<div class="col-md-3">
						<b class="form-label">Staff Type</b>
					<select class="form-control" name="staff_type">
						<option value="">-- All --</option>
						{foreach from=$config.membership_staff_type key=staff_type item=staff_type_label}
							<option value="{$staff_type}" {if $smarty.request.staff_type eq $staff_type}selected {/if}>{$staff_type_label}</option>
						{/foreach}
					</select>
					</div>
				{/if}
				
					<div class="col-md-3">
						<b class="form-label">Birthday Month From</b>
					<select class="form-control" name="birthday_month_from">
						<option value='0' {if $smarty.request.birthday_month_from eq '0'}selected {/if}>No Filter</option>
						{foreach from=$months key=month item=month_name}
							<option value="{$month}" {if $smarty.request.birthday_month_from eq $month}selected {/if}>{$month_name}</option>
						{/foreach}
					</select>
					</div>

					<div class="col-md-3">
						<b class="form-label">To</b>
					<select class="form-control" name="birthday_month_to">
						<option value='0' {if $smarty.request.birthday_month_to eq '0'}selected {/if}>No Filter</option>
						{foreach from=$months key=month item=month_name}
							<option value="{$month}" {if $smarty.request.birthday_month_to eq $month}selected {/if}>{$month_name}</option>
						{/foreach}
					</select>
					</div>

					<div class="col-md-3">
						<b class="form-label">Birthday Day From</b>
					<select class="form-control" name="birthday_day_from">
						<option value='0' {if $smarty.request.birthday_day_from eq $smarty.section.day.index}selected {/if}>No Filter</option>
						{section name=day start=1 loop=32 step=1}
							<option value='{$smarty.section.day.index}' {if $smarty.request.birthday_day_from eq $smarty.section.day.index}selected {/if}>{$smarty.section.day.index}</option>
						{/section}
					</select>
					</div>
					<div class="col-md-3">
						<b class="form-label">To</b>
					<select class="form-control" name="birthday_day_to">
						<option value='0' {if $smarty.request.birthday_day_to eq $smarty.section.day.index}selected {/if}>No Filter</option>
						{section name=day start=1 loop=32 step=1}
							<option value='{$smarty.section.day.index}' {if $smarty.request.birthday_day_to eq $smarty.section.day.index}selected {/if}>{$smarty.section.day.index}</option>
						{/section}
					</select>
					</div>
			
				</div>
			</p>
			
			{if $config.membership_mobile_settings}
				<p>
					<span>
						<b class="form-label">Mobile Registered</b>
						<select class="form-control" name="mobile_registered" onChange="MEMBER_LISTING.mobile_registered_changed();">
							<option value="">-- All --</option>
							<option value='y' {if $smarty.request.mobile_registered eq 'y'}selected{/if}>Yes</option>
							<option value='n' {if $smarty.request.mobile_registered eq 'n'}selected{/if}>No</option>
						</select>
					</span>
					
					<div id="div_mobile_filter" style="{if $smarty.request.mobile_registered ne 'y'}display:none;{/if}">
						<fieldset>
							<legend><b class="form-label">Mobile Filters</b></legend>
						<div class="row">
							<div class="col">
								<b class="form-label">Register Date From</b> 
							<div class="form-inline">
								<input class="form-control" size="20" type="text" name="mobile_registered_date_from" value="{$smarty.request.mobile_registered_date_from}" id="inp_mobile_registered_date_from" />
						&nbsp;	<img align="absmiddle" src="ui/calendar.gif" id="img_mobile_registered_date_from" style="cursor: pointer;" title="Select Date" />
							</div>
							</div>
							
							<div class="col">
								<b class="form-label">To</b> 
						<div class="form-inlilne">
							<input size="10" type="text" name="mobile_registered_date_to" value="{$smarty.request.mobile_registered_date_to}" id="inp_mobile_registered_date_to" />
							&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_mobile_registered_date_to" style="cursor: pointer;" title="Select Date">
						</div>
							</div>
						</div>
							
						</fieldset>
					</div>
				</p>
			{/if}
			
			
			
			<p>
				<fieldset style="width:500px;" class="stdframe">
					<legend>Fields</legend>
					<table width="100%">
						<tr>
							<td>
								<input type="checkbox" onChange="toggle_export_field(this);" /> <b>All</b>
							</td>
						</tr>
						
						<tr>
						{assign var=row_count value=-1}
						{foreach from=$available_field key=field item=label_field}
							{assign var=row_count value=$row_count+1}
							{if $row_count%3 eq 0}
							</tr>
							<tr>
							{/if}
							<td>
								<input type="checkbox" name="export_field[{$field}]" value="1" class="chx_export_field" {if $smarty.request.export_field.$field}checked{/if} /> 
								<b>{$label_field.label}</b>
								</td>
						{/foreach}
						</tr>
					</table>
				</fieldset>
			</p>
			
			<p>
				<input class="btn btn-primary" type="submit" value="Refresh" onClick="submit_form('refresh');">
				<button class="btn btn-primary" onClick="submit_form('print');"><img src="/ui/print.png" align="absmiddle" /> Print</button>
				<button class="btn btn-info" name="output_excel" onClick="check_export_excel();return false;">{#OUTPUT_EXCEL#} Excel</button>
				<button class="btn btn-info" name="output_csv" onClick="check_export_csv();return false;">{#OUTPUT_EXCEL#} CSV </button>
				<button class="btn btn-primary" onClick="submit_form('print_mailing_list');"><img src="/ui/print.png" align="absmiddle" /> Print Mailing List</button>
				{if $sessioninfo.privilege.MEMBERSHIP_ALLOW_SMS && $config.isms_user}
					<button class="btn btn-primary" onClick="send_sms();return false;"><img src="ui/icons/ipod_cast.png" align="absmiddle" /> SMS Broadcast</button>
				{/if}
				{if $config.membership_mobile_settings and $config.enable_push_notification and $sessioninfo.privilege.MEMBERSHIP_ALLOW_PN and $smarty.request.mobile_registered eq 'y'}
					<button class="btn btn-primary" onClick="PN_DIALOG.open();return false;"><img src="/ui/icons/note_go.png" align="absmiddle" /> Send Push Notification</button>
				{/if}
			</p>
			
			<ul>
				<li> Please do not select too many fields for printing, as the paper may not have space to fit all of them.</li>
				{if $sessioninfo.privilege.MEMBERSHIP_ALLOW_SMS && $config.isms_user}
					<li> Please note that the iSMS will send every 5 minutes from 9AM to 10PM.</li>
				{/if}
			</ul>
			{*<input type=button value="Add Member" onclick="window.location = '/membership.php?a=add';">*}
		</div>
	</div>
</div>
<div style="margin:5px 0;" align=right class="mx-3">
	{$pagination}
	<br />
	<div style="float:left;"><font style="color:red; font-size:16px; font-weight:bold;">*</font> Indicate points is not up to date.</div>
	<div style="float:right;">Total {$total_row|number_format} members found.</div>
	<br />
</div>
</form>

{if $members}
	<div class="card mx-3">
		<div class="card-body">
			<span id="span_loading"></span>
	<div id="div_content">
		{include file="membership.listing.table.tpl"}
	</div>
	<p align=center>{$pagination2}</p>
{else}
	<p align=center>- No Data -</p>
		</div>
	</div>
{/if}

{include file=footer.tpl}

{if !$no_header_footer}
<script type="text/javascript">
{if $sms_info}
	setTimeout('reload_sms_broadcast()', REFRESH_INTERVAL);
{/if}

MEMBER_LISTING.initialize();
</script>
{/if}
