{*
12/02/2020 11:57 AM  Sheila
- Fixed table and button css

12/4/2020 1:42 PM Shane
- Added Branch Group and User filter
- Added Copy

12/7/2020 5:27 PM Shane
- Added input disabled if is in view mode.
- Hide Copy button if cannot copy. (If logged in branch is not HQ and Announcement created is not from this branch, then cannot copy.)

12/9/2020 7:55 PM Shane
- Added show_branch_group checking.
*}

{include file=header.tpl}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
input[disabled] {
  color:black;
  background:rgb(255,238,153);
}
input[readonly] {
  color:black;
  background:rgb(255,238,153);
}
select[disabled] {
  color:black;
  background:rgb(255,238,153);
}

.div_member_type_override > div{
	height:25px;
	vertical-align:middle;
}

.div_username_container{
	border: 1px solid blue;
	min-width: 100px;
	float: left;
	padding:3px;
	background-color: #eee;
	margin-left: 5px;
	margin-bottom: 5px;
}
</style>
{/literal}
<script type="text/javascript">
var readonly = int('{$readonly}');
var phpself = '{$smarty.server.PHP_SELF}';
var show_branch_group = int('{$show_branch_group}');
{literal}
function init_calendar(sstr){
	Calendar.setup({
	    inputField     :    "dt1"+sstr,     // id of the input field
	    ifFormat       :    "%e/%m/%Y",      // format of the input field
	    button         :    "t_dt1"+sstr,  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});

	Calendar.setup({
	    inputField     :    "dt2"+sstr,     // id of the input field
	    ifFormat       :    "%e/%m/%Y",      // format of the input field
	    button         :    "t_dt2"+sstr,  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
}

function change_wholeday(obj){
	if (obj.checked){
		$('time_from_id').value="00:00";
		$('time_from_id').readOnly=true;
		$('time_to_id').value="23:59";
		$('time_to_id').readOnly=true;
	}else{
		$('time_from_id').readOnly=false;
		$('time_to_id').readOnly=false;
	}
}

function toggle_all_branch_group(obj){
	var all_chx = $$('input.branch_group_cb');
	for(var i=0; i<all_chx.length; i++){
		if(obj.checked == true){
			all_chx[i].checked = true;
		}else{
			all_chx[i].checked = false;
		}
		toggle_branch_group(all_chx[i]);
	}
}

function toggle_branch_group(obj){
	var bgid = obj.getAttribute('data-bgid');
	var all_chx = $$('input.branch_group_cb');
	if(obj.checked == true || readonly){
		//Disable Manage Branch
		$('manage_branch_'+bgid).update('View Branch');
		$('manage_branch_'+bgid).addClassName('btn-success');
		$('manage_branch_'+bgid).removeClassName('btn-primary');
		$('manage_branch_'+bgid).href = 'javascript:void(BRANCH_ASSGN.view_branch_clicked('+bgid+',"view"));';
	}else{
		//Enable Manage Branch
		$('manage_branch_'+bgid).update('Manage Branch');
		$('manage_branch_'+bgid).removeClassName('btn-success');
		$('manage_branch_'+bgid).addClassName('btn-primary');
		$('manage_branch_'+bgid).href = 'javascript:void(BRANCH_ASSGN.view_branch_clicked('+bgid+',"edit"));';
	}
	reset_branch_count(bgid);
}

function toggle_all_branches(obj,is_popup = false){
	if(is_popup){
		var postfix = '_pu';
	}else{
		var postfix = '';
	}
	var all_chx = $$('input.branch_cb'+postfix);
	for(var i=0; i<all_chx.length; i++){
		if(obj.checked == true){
			all_chx[i].checked = true;
		}else{
			all_chx[i].checked = false;
		}
	}
	if(obj.checked == true){
		$('cbx_all_counters'+postfix).checked = true;//prop('checked',true);
	}else{
		$('cbx_all_counters'+postfix).checked = false;
	}
	toggle_all_counters(obj,is_popup);
}

function toggle_all_counters(obj,is_popup = false){
	if(is_popup){
		var postfix = '_pu';
	}else{
		var postfix = '';
	}
	var all_chx = $$('input.all_counters'+postfix);
	for(var i=0; i<all_chx.length; i++){
		if(obj.checked == true){
			all_chx[i].checked = true;
		}else{
			all_chx[i].checked = false;
		}
		toggle_all_branch_counters(all_chx[i],is_popup);
	}
}

function toggle_branch_counter_all(obj,is_popup = false){
	if(is_popup){
		var postfix = '_pu';
	}else{
		var postfix = '';
	}
	var bid = obj.getAttribute('data-bid');
	var all_chx = $$('input.all_counters'+postfix+'[data-bid="'+bid+'"]');
	for(var i=0; i<all_chx.length; i++){
		if(obj.checked == true){
			all_chx[i].checked = true;
		}else{
			all_chx[i].checked = false;
		}
		toggle_all_branch_counters(all_chx[i],is_popup);
	}
}

function toggle_all_branch_counters(obj,is_popup = false){
	if(is_popup){
		var postfix = '_pu';
	}else{
		var postfix = '';
	}
	var bid = obj.getAttribute('data-bid');
	var all_chx = $$('input.branch_counter'+postfix+'[data-bid="'+bid+'"]');
	for(var i=0; i<all_chx.length; i++){
		if(obj.checked == true){
			all_chx[i].checked = true;
			all_chx[i].disabled = true;
		}else{
			all_chx[i].checked = false;
			all_chx[i].disabled = false;
		}
	}
	if(obj.checked == true){
		$$('input.branch_cb'+postfix+'[data-bid="'+bid+'"]')[0].checked = true;
	}
}

function toggle_counter(obj,is_popup = false){
	if(is_popup){
		var postfix = '_pu';
	}else{
		var postfix = '';
	}
	var bid = obj.getAttribute('data-bid');
	if(obj.checked == true){
		$$('input.branch_cb'+postfix+'[data-bid="'+bid+'"]').first().checked = true;
	}
}

function toggle_all_days(obj){
	var all_chx = $$('input.all_days');
	for(var i=0; i<all_chx.length; i++){
		if(obj.checked == true){
			all_chx[i].checked = true;
		}else{
			all_chx[i].checked = false;
		}
	}
}

function do_save_branches_and_counters(branch_group_id){
	//Branch
	var cbx = $$('input.branch_cb_pu');
	var branch_ids = {};
	for(var i=0; i<cbx.length; i++){
		if(cbx[i].checked == true){
			var bid = cbx[i].value;
			branch_ids[bid] = {};
			branch_ids[bid]['branch_id'] = bid;
			//All Counter flag
			if($$('input.all_counters_pu[data-bid="'+bid+'"]').first().checked == true){
				branch_ids[bid]['all_counter_flag'] = 1;
			}else{
				branch_ids[bid]['all_counter_flag'] = 0;
				//Counter
				var cbx_counter = $$('input.branch_counter_pu[data-bid="'+bid+'"]');
				var counter_ids = [];
				for(var j=0; j<cbx_counter.length; j++){
					if(cbx_counter[j].checked == true){
						counter_ids.push(cbx_counter[j].value);
					}
				}
				branch_ids[bid]['counter_ids'] = counter_ids;
			}
		}
	}

	//saving as string in the hidden input
	$('branch_id_by_group_'+branch_group_id).value = JSON.stringify(branch_ids);
	reset_branch_count(branch_group_id);
	BRANCH_DIALOG.close();
}

function do_save(){
	document.f_a.a.value='save';
	document.f_a.target = "";
	Form.enable(document.f_a);
	document.f_a.submit();
}

function do_confirm(){
	if (confirm('Finalise this announcement?')){
		document.f_a.a.value='confirm';
		document.f_a.target = "";
		Form.enable(document.f_a);
		document.f_a.submit();
	}
}

function do_delete(){
	if (confirm('Delete this Announcement?')){
		document.f_b.a.value='delete';
		document.f_b.target = "";
		document.f_b.submit();
	}
}

function do_cancel(){
	if (confirm('Cancel this Announcement?')){
		document.f_b.a.value='cancel';
		document.f_b.target = "";
		document.f_b.submit();
	}
}

function do_copy(){
	if (confirm('Copy announcement?')){
		document.f_b.a.value='copy';
		document.f_b.target = "";
		document.f_b.submit();
	}
}

function reset_branch_announcement_checkbox(branch_group_id){
	var branch_ids_str = $('branch_id_by_group_'+branch_group_id).value;
	if(branch_ids_str){
		var branch_ids = JSON.parse(branch_ids_str);
		for (var key in branch_ids) {
		    // skip loop if the property is from prototype
		    if (!branch_ids.hasOwnProperty(key)) continue;

		    var obj = branch_ids[key];
		    var bid = obj.branch_id;
		    var all_counter_flag = obj.all_counter_flag;

		    //Check branch
		    $$('input.branch_cb_pu[data-bid="'+bid+'"]').first().checked = true;

		    //All Counter
		    if(all_counter_flag){
		    	var all_counter_cbx = $$('input.all_counters_pu[data-bid="'+bid+'"]').first();
		    	all_counter_cbx.checked = true;
		    	//Check and disable all counters of this branch
		    	toggle_branch_counter_all(all_counter_cbx,true);
		    }else{
		    	//Specifc Counters of this branch
		    	var counter_ids = obj.counter_ids;
		    	counter_ids.each(function(cid,i){
		    		var branch_counter_inp = $$('input.branch_counter_pu[data-cid="'+cid+'"]').first();
		    		branch_counter_inp.checked = true;
		    	});
		    }
		}
	}
}

function reset_branch_count(branch_group_id = false){
	if(!show_branch_group){
		return false;
	}
	if(branch_group_id !== false){
		var branch_group_cbx = $('announcement_branch_group_id_'+branch_group_id);
		if(branch_group_cbx.checked){
			var selected_branches = 'All branches selected';
		}else{
			var branch_ids_str = $('branch_id_by_group_'+branch_group_id).value;
			if(branch_ids_str){
				var branch_ids = JSON.parse(branch_ids_str);
				var ct = Object.keys(branch_ids).length;
				if(ct > 1){
					var selected_branches = ct+' branches selected';
				}else{
					var selected_branches = ct+' branch selected';
				}
			}else{
				var selected_branches = 'No branch selected';
			}
		}
		$('selected_branches_'+branch_group_id).update(selected_branches);
	}else{
		var inp_bibg = $$('input.branch_id_by_group');
		for(var i=0; i<inp_bibg.length; i++){
			var bgid = inp_bibg[i].getAttribute('data-bgid');
			reset_branch_count(bgid);
		}
	}
}

function check_counter_cbx_disabled(){
	var all_cbx = $$('input.all_counters');
	for(var i=0; i<all_cbx.length; i++){
		var bid = all_cbx[i].getAttribute('data-bid');
		if(all_cbx[i].checked){
			//disable counter cbx
			var branch_counter_cbx = $$('input.branch_counter[data-bid="'+bid+'"]');
			for(var j=0; j<branch_counter_cbx.length; j++){
				branch_counter_cbx[j].disabled = true;
			}
		}
	}
}

BRANCH_ASSGN = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		SEARCH_USER_DIALOG.initialize();
		BRANCH_DIALOG.initialize();

	},
	// function when users click on Manage Branch
	view_branch_clicked: function(branch_group_id,mode){
		// Show Branch
		BRANCH_DIALOG.open(branch_group_id,mode);
	},

	// function when user click on search user
	search_user_click: function(){
		SEARCH_USER_DIALOG.open();
	},

	del_user_assign: function(user_id){
		$('div_user_assign-'+user_id).remove();
	},

	// function to add audit user
	add_user: function(user_id, username){
		$('span_user_loading').update(_loading_);
		
		new Ajax.Request(phpself, {
			parameters: {
				a: 'ajax_add_user',
				user_id: user_id,
			},
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$('span_user_loading').update('');
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						new Insertion.Bottom($('span_user_list'), ret['html']);
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

var BRANCH_DIALOG = {
	initialize: function(){
		
	},
	open: function(branch_group_id,mode){
		// Show Loading
		$('div_branch_dialog_content').update(_loading_);
		
		// Show Dialog
		curtain(true);
		center_div($('div_branch_dialog').show());
		
		var THIS = this;
		if(readonly || mode=='view'){
			var view_mode = 1;
		}else{
			var view_mode = 0;
		}

		var params = 'branch_group_id='+branch_group_id+'&view_mode='+view_mode+'&a=ajax_show_branch_list';
		
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
						$('div_branch_dialog_content').update(ret['html']);
						//check if branch group is checked
						var branch_group_cbx = $('announcement_branch_group_id_'+branch_group_id);
						if(branch_group_cbx.checked == true){
							toggle_all_branches(branch_group_cbx,true);
						}else{
							reset_branch_announcement_checkbox(branch_group_id);
						}

						//disable all checkbox
						var cbx = $$('table.branch_table input[type="checkbox"]');
						if(view_mode){
							for(var i=0; i<cbx.length; i++){
								cbx[i].disabled = true;
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
				THIS.close();
			}
		});
	},
	close: function(){
		default_curtain_clicked();
	},
};

var SEARCH_USER_DIALOG = {
	initialize: function(){
		this.f = document.f_search_user;
		
		USER_AUTOCOMPLETE.initialize({
			'callback': function(user_id, username){
				SEARCH_USER_DIALOG.add_user_clicked(user_id, username);
			}
		});
	},
	open: function(){
		// Clear Form value
		this.f.reset();
		$('inp_selected_user_id').value = '';
		
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
		this.close();
		BRANCH_ASSGN.add_user(user_id,username);
	}
}
</script>
{/literal}

{if $readonly}
	{assign var=allow_edit value=0}
{else}
	{assign var=allow_edit value=1}
{/if}

<div id=wait_popup style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
	<p align=center>
		Please wait..<br /><br /><img src="ui/clock.gif" border="0" />
	</p>
</div>
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{if $form.id}
Status:
{if $form.status == 1}
	Draft Announcement
{elseif $form.status == 2}
	Cancelled
{elseif $form.status == 3}
	Confirmed
{elseif $form.status == 4}
	Deleted
{/if}
{/if}
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<br>

{if $errm.top}
<div class="alert alert-danger mx-3 rounded">
	<div id=err><div class=errmsg><ul>
		{foreach from=$errm.top item=e}
		<li> {$e}
		{/foreach}
		</ul></div></div>
</div>
{/if}

{if $smarty.request.msg}<div style="color:blue;">{$smarty.request.msg}</div>{/if}

<form name="f_a" method=post action="front_end.announcement.php" ENCTYPE="multipart/form-data">
<input type=hidden name=id value="{$form.id}">
<input type=hidden name=user_id value="{$form.user_id|default:$sessioninfo.id}">
<input type=hidden name=branch_id value="{$form.branch_id|default:$sessioninfo.branch_id}">
<input type=hidden name=a value="save">
<input type=hidden name=readonly value="{$readonly}">
<input type=hidden name=active value="{$form.active}">

<div class="card mx-3">
	<div class="card-body">
		<div class="stdframe" >
			<table>
			<tr>
			<td><b class="form-label">Title<span class="text-danger" title="Required Field"> *</span></b> </td>
			<td colspan=3><input class="form-control" name="title" value="{$form.title|escape}" size=80> </td>
			</tr>
			<tr>
			<td valign=top><b class="form-label">Content<span class="text-danger" title="Required Field"> *</span></b> </td>
			<td colspan=3><textarea class="form-control" cols="71" rows="6" name="content">{$form.content|escape}</textarea></td>
			</tr>
			<tr>
			<td><b class="form-label">Date</b></td>
			<td colspan=3>
				<div class="form-inline">
					<input class="form-control" name="date_from" value="{if $form.date_from>0}{$form.date_from|date_format:"%Y-%m-%d"}{else}{$smarty.now|date_format:"%Y-%m-%d"}{/if}" onclick="if(this.value)this.select();" size="22" id="inp_date_from" />
				{if $allow_edit}
				&nbsp;	<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date" /> <span class="text-danger" title="Required Field"> *</span>
				{/if}
				&nbsp;<b class="form-label">To&nbsp;</b>
			
				<input class="form-control" name="date_to" value="{if $form.date_to>0}{$form.date_to|date_format:"%Y-%m-%d"}{else}{$smarty.now|date_format:"%Y-%m-%d"}{/if}" onclick="if(this.value)this.select();" size="22" id="inp_date_to" />
				{if $allow_edit}
					&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date" /> <span class="text-danger" title="Required Field"> *</span>
				{/if}
				&nbsp;(yyyy-mm-dd)
				</div>
			</td>
			</tr>
			<tr><td><b class="form-label">Time</b></td>
			<td colspan=3>
			<div class="form-inline">
				<input class="form-control" id="time_from_id" name="time_from" value="{if $form.time_from>0}{$form.time_from|date_format:"%H:%M"}{else}00:00{/if}" onclick="if(this.value)this.select();" size=22>
			<b class="form-label">&nbsp;To&nbsp;</b> 
			<input class="form-control" id="time_to_id" name="time_to" value="{if $form.time_to>0}{$form.time_to|date_format:"%H:%M"}{else}23:59{/if}" onclick="if(this.value)this.select();" size=22>&nbsp; (hh:mm)
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" id="all_day_id" name="all_day" value="all_day" onclick="change_wholeday(this)"> <label for="all_day_id"><b class="form-label">&nbsp;All Day</b></label>
			
			</div>
			</td>
			</tr>
			<tr>
			<td valign=top><b class="form-label">Allowed Day(s)</b> <span class="text-danger" title="Required Field"> *</span></td>
			<td>
				<table class="small" border=0 id=tbl_days>
				<tr>
					<td><label for="all_days"><input type="checkbox" id="all_days" name="all_days" value="1" onclick="toggle_all_days(this);" /> All</label></td>
				</tr>
				{foreach from=$form.all_days item=day key=i}
					<tr>
						<td valign="top">
						<label>
							<input type=checkbox name="allowed_day[]" value="{$i}" class="all_days" {if is_array($form.allowed_day) and in_array($i,$form.allowed_day)}checked{/if} />&nbsp;{$day|upper}
						</label>
						</td>
					</tr>
				{/foreach}
				</table>
			</td>
			</tr>
			<tr>
				<td valign=top><b class="form-label">Branches Announcement</b> 
					<span class="text-danger" title="Required Field"> *</span></td>
			{if $form.branch_id==1 && BRANCH_CODE=='HQ'}
				{*<!-- <td>You may select multiple branches <br>
					<table class="small" border=0 id=tbl_branch>
					<tr>
						<td><label for="all_branches"><input type="checkbox" id="all_branches" name="all_branches" value="1" onclick="toggle_all_branches(this);" /> All</label></td>
					</tr>
					{section name=i loop=$branch}
					{assign var=bid value=$branch[i].id}
					<tr>
						<td valign=top>
						<label>
							<input type=checkbox name="announcement_branch_id[]" value="{$branch[i].id}" class="branch branch_cb" {if is_array($form.announcement_branch_id) and in_array($branch[i].id,$form.announcement_branch_id)}checked{/if} />&nbsp;{$branch[i].code}
						</label>
						</td>
					</tr>
					{/section}
					</table>
				</td> -->*}
			
				{* new table *}
				{if $max_counter >= $max_counter_per_row}
					{assign var=colspan value=$max_counter_per_row}
				{else}
					{assign var=colspan value=$max_counter}
				{/if}
				{*<!-- <td>You may select multiple branches <br>
					<table class="small branch_table" border="1" cellspacing="0" cellpadding="4">
					<tr>
						<td><label for="all_branches"><input type="checkbox" id="all_branches" name="all_branches" value="1" onclick="toggle_all_branches(this);" /> All</label></td>
						<td nowrap><label><input type="checkbox" id="cbx_all_counters" name="cbx_all_counters" value="1" onclick="toggle_all_counters(this);" /> All</label></td>
						<td colspan="{$max_counter}">Counter Name</td>
					</tr>
					{section name=i loop=$branch}
					{assign var=bid value=$branch[i].id}
					{math equation=ceil(x/y) x=$branch_counter.$bid|@count y=$max_counter_per_row assign=rowspan}
					<tr>
						<td rowspan="{$rowspan}" valign=top nowrap>
						<label>
							<input type=checkbox name="announcement_branch_id[]" value="{$branch[i].id}" class="branch branch_cb" data-bid="{$bid}" onclick="toggle_branch_counter_all(this);" {if is_array($form.announcement_branch_id) and in_array($branch[i].id,$form.announcement_branch_id)}checked{/if} />&nbsp;{$branch[i].code}
						</label>
						</td>
						<td rowspan="{$rowspan}" valign=top nowrap><label><input type="checkbox" class="all_counters" onclick="toggle_all_branch_counters(this);" data-bid="{$bid}" /> All</label></td>
						{assign var=ct value=0}
						{foreach from=$branch_counter.$bid item=c}
							{assign var=ct value=$ct+1}
							{if $ct > $max_counter_per_row}
								</tr>
								<tr>
								{assign var=ct value=1}
							{/if}
							<td nowrap><label><input type="checkbox" class="counter_id branch_counter" data-bid="{$bid}" /> {$c.network_name}</label></td>
						{/foreach}
						{if $ct < $colspan}
							{section name=bc start=$ct loop=$colspan}
								<td style="background-color:#d7d7d7;">&nbsp;</td>
							{/section}
						{elseif $max_counter == 0}
							<td style="background-color:#d7d7d7;">&nbsp;</td>
						{/if}
					</tr>
					{/section}
					</table>
				</td> -->*}
				{if $show_branch_group}
					<td>
						{* new table version 2 *}
						<table border=0>
						</tr>
							<td valign=top nowrap>
								Select Branch Group to broadcast announcement to all branches of the selected group.<br>
								Click Manage Branch if want to broadcast to specific branch or counter.<br>
							<table class="small" border=0 id=tbl_branch>
								<tr>
									<td><label><input type="checkbox" id="all_branch_group" name="all_branch_group" value="1" onclick="toggle_all_branch_group(this)" />&nbsp; All Group</label></td>
								</tr>
								{foreach from=$branch_group_list item=bgl}
									{if is_array($form.announcement_branch_group_id) and in_array($bgl.id,$form.announcement_branch_group_id)}
										{assign var=cbxchecked value=checked}
										{assign var=btn value=btn-success}
										{assign var=manage_branch value='View Branch'}
										{assign var=amode value='view'}
									{else}
										{assign var=cbxchecked value=''}
										{if $readonly}
											{assign var=btn value=btn-success}
											{assign var=manage_branch value='View Branch'}
											{assign var=amode value='view'}
										{else}
											{assign var=btn value=btn-primary}
											{assign var=manage_branch value='Manage Branch'}
											{assign var=amode value='edit'}
										{/if}
									{/if}
									{assign var=bibg value="branch_id_by_group_`$bgl.id`"}
									<tr>
										<td valign=top>
										<label>
											<input type=checkbox id="announcement_branch_group_id_{$bgl.id}" name="announcement_branch_group_id[]" value="{$bgl.id}" data-bgid="{$bgl.id}" onclick="toggle_branch_group(this)" class="branch branch_group_cb" {$cbxchecked}/>&nbsp; {$bgl.description}
										</label>
										</td>
										<td valign=top>
										<a class="btn {$btn}" id="manage_branch_{$bgl.id}" href="javascript:void(BRANCH_ASSGN.view_branch_clicked({$bgl.id},'{$amode}'));">{$manage_branch}</a>
										<span id="selected_branches_{$bgl.id}"></span>
										</td>
									</tr>
									<input type="hidden" id="{$bibg}" name="{$bibg}" data-bgid="{$bgl.id}" class="branch_id_by_group" value='{$form.$bibg}' />
								{/foreach}
								
								</table>
							</td>
						</tr>
						</table>
					</td>
				{else}
					{*show branch directly*}
					<td>You may select multiple branches <br>
						<table class="small branch_table" border="1" cellspacing="0" cellpadding="4">
						<tr>
							<td><label for="all_branches"><input type="checkbox" id="all_branches" name="all_branches" value="1" onclick="toggle_all_branches(this);" /> All</label></td>
							<td nowrap><label><input type="checkbox" id="cbx_all_counters" name="cbx_all_counters" value="1" onclick="toggle_all_counters(this);" /> All</label></td>
							<td colspan="{$max_counter}">Counter Name</td>
						</tr>
						{section name=i loop=$branch}
						{assign var=bid value=$branch[i].id}
						{math equation=ceil(x/y) x=$branch_counter.$bid|@count y=$max_counter_per_row assign=rowspan}
						<tr>
							<td rowspan="{$rowspan}" valign=top nowrap>
							<label>
								<input type=checkbox name="announcement_branch_id[]" value="{$branch[i].id}" class="branch branch_cb" data-bid="{$bid}" value="{$bid}" onclick="toggle_branch_counter_all(this);" {if is_array($form.announcement_branch_id) and in_array($branch[i].id,$form.announcement_branch_id)}checked{/if} />&nbsp;{$branch[i].code}
							</label>
							</td>
							<td rowspan="{$rowspan}" valign=top nowrap><label><input type="checkbox" name="announcement_all_counter_flag[]" class="all_counters" onclick="toggle_all_branch_counters(this);" data-bid="{$bid}" value="{$bid}" {if is_array($form.announcement_counter_id.$bid) and !$form.announcement_counter_id.$bid} checked {/if} /> All</label></td>
							{assign var=ct value=0}
							{foreach from=$branch_counter.$bid item=c}
								{assign var=ct value=$ct+1}
								{if $ct > $max_counter_per_row}
									</tr>
									<tr>
									{assign var=ct value=1}
								{/if}
								<td nowrap><label><input type="checkbox" name="announcement_counter_id_{$bid}[]" class="counter_id branch_counter" data-bid="{$bid}" value="{$c.id}" onclick="toggle_counter(this);" {if is_array($form.announcement_counter_id.$bid) and (in_array($c.id,$form.announcement_counter_id.$bid) or !$form.announcement_counter_id.$bid)} checked {/if} /> {$c.network_name} </label></td>
							{/foreach}
							{if $ct < $colspan}
								{section name=bc start=$ct loop=$colspan}
									<td style="background-color:#d7d7d7;">&nbsp;</td>
								{/section}
							{elseif $max_counter == 0}
								<td style="background-color:#d7d7d7;">&nbsp;</td>
							{/if}
						</tr>
						{/section}
						</table>
					</td>
				{/if}
			{else}
				<td>
					<table class="small branch_table" border="1" cellspacing="0" cellpadding="4">
					{if BRANCH_CODE=='HQ'}
						{assign var=this_branch value=$form.branch_id}
					{else}
						{assign var=this_branch value=$session_branch_id}
					{/if}
					{if $branches[$this_branch]}
						{if $branch_counter.$this_branch|@count >= $max_counter_per_row}
							{assign var=colspan value=$max_counter_per_row}
						{else}
							{assign var=colspan value=$branch_counter.$this_branch|@count}
						{/if}
						{math equation=ceil(x/y) x=$branch_counter.$this_branch|@count y=$max_counter_per_row assign=rowspan}
						<tr>
							<td rowspan="{$rowspan}" valign="top">
							<label>
								{*<!-- <span style="display:none;"><input type="checkbox" name="announcement_branch_id[{$session_branch_id}]" type="hidden" value="{$session_branch_id}" class="branch_cb" checked /></span> -->*}
								<br>{$branches[$this_branch].code}
							</label>
							</td>
							<td rowspan="{$rowspan}" valign=top nowrap><label><input type="checkbox" name="announcement_all_counter_flag" class="all_counters" onclick="toggle_all_branch_counters(this);" data-bid="{$this_branch}" {if $form.announcement_all_counter_flag} checked {/if} /> ALL COUNTERS</label></td>
							{assign var=ct value=0}
							{foreach from=$branch_counter.$this_branch item=c}
								{assign var=ct value=$ct+1}
								{if $ct > $max_counter_per_row}
									</tr>
									<tr>
									{assign var=ct value=1}
								{/if}
								<td nowrap><label><input type="checkbox" name="announcement_counter_id[]" class="counter_id branch_counter" data-bid="{$this_branch}" value="{$c.id}" {if (is_array($form.announcement_counter_id.$this_branch) and in_array($c.id,$form.announcement_counter_id.$this_branch)) or $form.announcement_all_counter_flag} checked {/if} /> {$c.network_name}</label></td>
							{/foreach}
							{if $ct < $colspan}
								{section name=bc start=$ct loop=$colspan}
									<td style="background-color:#d7d7d7;">&nbsp;</td>
								{/section}
							{elseif $branch_counter.$this_branch|@count == 0}
								<td style="background-color:#d7d7d7;">&nbsp;</td>
							{/if}
						</tr>
					{/if}
					</table>
				</td>
			{/if}
			</tr>
			
			{* User *}
			<tr>
				<td valign="top"><b class="form-label">User</b></td>
				<td>
					{if !$readonly}
					(Leave this empty if want to broadcast to all users) <br>
					{/if}
					<table width="100%" border="0" cellspacing="0" cellpadding="4">
						<tr>
							<td valign="top" width="10px">
							{if !$readonly}
								<img src="ui/ed.png" align="absmiddle" onClick="BRANCH_ASSGN.search_user_click();" style="float:left;" />
							{/if}
							</td>
							<td valign="top">
							<span id="span_user_list">
								{if $form.announcement_user_id}
									{foreach from=$form.announcement_user_id item=tmp_user_id}
										{include file='front_end.announcement.open.user.tpl' user=$user_list.$tmp_user_id}
									{/foreach}
								{else}
									{if $readonly}All Users{/if}
								{/if}
							</span>
							<span id="span_user_loading"></span>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			
			</table>
			
			</div>
	</div>
</div>
</form>

{* Branch Dialog *}
<div id="div_branch_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:50%;height:470px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_branch_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">BRANCH LISTS</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="BRANCH_DIALOG.close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_branch_dialog_content" style="padding:2px;height:430px;overflow-y:auto;">
	</div>
</div>

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
			<p align="center">
				<b>Search User: &nbsp;&nbsp;&nbsp;</b>
				{include file='user_autocomplete.tpl' btn_add=1}
			</p>
		</form>
	</div>
</div>

<script type="text/javascript">
{literal}
function calendar_setup(){
    Calendar.setup({
		    inputField     :    "inp_date_from",     // id of the input field
		    ifFormat       :    "%Y-%m-%d",      // format of the input field
		    button         :    "img_date_from",  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
		});

		Calendar.setup({
		    inputField     :    "inp_date_to",     // id of the input field
		    ifFormat       :    "%Y-%m-%d",      // format of the input field
		    button         :    "img_date_to",  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
	});
}
</script>
{/literal}
	<form name="f_b" method=post action="front_end.announcement.php" ENCTYPE="multipart/form-data">
		<input type=hidden name=a>
		<input type=hidden name=id value="{$form.id}">
		<input type=hidden name=user_id value="{$form.user_id|default:$sessioninfo.id}">
		<input type=hidden name=branch_id value="{$form.branch_id|default:$sessioninfo.branch_id}">
		<input type=hidden name=readonly value="{$readonly}">
		<input type=hidden name=active value="{$form.active}">
	</form>

	<p id=submitbtn align=center>
	{if $allow_edit}
	<input name=bsubmit class="btn btn-primary" type=button value="Save & Close" onclick="do_save()">
	{/if}

	{if $form.status == 3 && $form.active}
	<input type=button class="btn btn-warning" value="Cancel Announcement" onclick="do_cancel()">
	{/if}

	{if $allow_edit and $form.status <= 1}
	<input type=button class="btn btn-success" value="Confirm" onclick="do_confirm()">
	{/if}

	{if ($form.active || $form.status == 1) && $allow_edit}
	<input type=button class="btn btn-error" value="Delete" onclick="do_delete()">
	{/if}

	{if $form.id && ($sessioninfo.branch_id == 1 || $sessioninfo.branch_id == $form.branch_id)}
	<input type=button class="btn btn-primary" value="Copy" onclick="do_copy()">
	{/if}

	<input type=button class="btn btn-error" value="Close" onclick="document.location='/front_end.announcement.php'">
	</p>
<script>
{if $allow_edit}
	calendar_setup();
{/if}
reset_branch_count();
BRANCH_ASSGN.initialize();

{if $smarty.request.a ne 'open'}
	Form.disable(document.f_a);
{else}
	Form.enable(document.f_a);
{/if}

{if !$show_branch_group}
	check_counter_cbx_disabled();
{/if}
</script>

{include file='footer.tpl'}