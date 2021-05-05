{*
Revision History
----------------
9 Apr 2007 - yinsee
- added branch dropdown selection (HQ only)
4/13/2012 3:42:54 PM Alex
- add search keyword features

9/11/2012 6:14 Drkoay
- User allow to filter with User All (Branch and type cannot to be all when user is all)
- add from date and to date

11/5/2013 5:08 PM Justin
- Enhanced to take off the page reload while user click user, branch and type.

2/14/2020 2:49 PM William
- Enhanced to combine similar log type into one group.

06/30/2020 04:59 PM Sheila
- Updated button css.
*}
{include file=header.tpl}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<style>
{literal}
.optionGroup {
    font-weight: bold;
	font-family: sans-serif;
}
{/literal}
</style>

<h1>View Logs</h1>
<form method="get" name="f_f" onsubmit="return reload();">
	<input type="hidden" name="find" value="1"/>
<table >
<tr>
	<td>
	{if $sessioninfo.privilege.VIEWLOG_ALL}
	
		<b>User</b>
	
		<select id="user_id" name="user_id">
		<option value="">All</option>
		{section name=i loop=$users}
		<option value={$users[i].id} {if $smarty.request.user_id eq $users[i].id}selected{/if}>{$users[i].u} ({$users[i].branch_code}){if !$users[i].active} - inactive{/if}</option>
		{/section}
		</select>
	
	{else}
	<input name="user_id" type="hidden" value="{$sessioninfo.id}">
	{/if}
	{if $BRANCH_CODE eq 'HQ'}
	
		<b>Branch</b>
	
		<select id="branch_id" name="branch_id">
		<option value="">All</option>
		{section name=i loop=$branches}
		<option value="{$branches[i].id}" {if $smarty.request.branch_id eq $branches[i].id}selected{/if}>{$branches[i].code}</option>
		{/section}
		</select>
	
	{/if}
	
		<b>Type</b>
		<select id="filter_type" name="filter_type">
		<option class="optionGroup" value="">All</option>
		{foreach from=$types key=type_group item=type_child}
			{assign var="group_type" value="G:`$type_group`"}
			<option class="optionGroup" {if $smarty.request.filter_type eq $group_type}selected{/if} value="{$group_type}">{$type_group}</option>
			{foreach from=$type_child key=keys item=child_type_name}
				<option {if $smarty.request.filter_type eq $type_child[$keys]}selected{/if} value="{$type_child[$keys]}">&nbsp;&nbsp;&nbsp;&nbsp;{$type_child[$keys]}</option>
			{/foreach}
		{/foreach}
		</select>
	</td>
</tr>
<tr>
	<td>
		<b>From</b>
		<input id="inp_date_from" size="10" value="{$smarty.request.date_from}" name="date_from" onblur="check_date(this)">
		<img id="img_date_from" align="absmiddle" title="Select Date" style="cursor: pointer;" src="ui/calendar.gif">
		<b>To</b>
		<input id="inp_date_to" size="10" value="{$smarty.request.date_to}" name="date_to" onblur="check_date(this)">
		<img id="img_date_to" align="absmiddle" title="Select Date" style="cursor: pointer;" src="ui/calendar.gif">
		(yyyy-mm-dd) 
		
	
		<b>Search</b>
	
		<input type="text" name="keyword" value="{$smarty.request.keyword}">
	
		<b>Records per page</b>
	
		<select name="pg" onChange="return reload(true)">
		<option value="100" {if $smarty.request.pg == 100}selected{/if}>100</option>
		<option value="50" {if $smarty.request.pg == 50}selected{/if}>50</option>
		<option value="20" {if $smarty.request.pg == 20}selected{/if}>20</option>
		</select>
	
		<input class="btn btn-primary" type="submit" value="View">
	</td>
</tr>
</table>
</form>
{if $pagination}<p align="center">{$pagination}</p>{/if}
<div class="stdframe">
<table  cellpadding="4" cellspacing="0" width="100%">
<tr>
	<th>Branch</th>
	{if $smarty.request.user_id eq ""}<th>User</th>{/if}
	<th>Date/Time</th>
	<th>Type</th>
	<th>Description</th>
</tr>
{section name=i loop=$logs}
<tr>
	<td>{$logs[i].branch}</td>
	{if $smarty.request.user_id eq ""}<td>{$logs[i].u}</td>{/if}
	<td nowrap>{$logs[i].timestamp|date_format:"%e/%m/%y %H:%M:%S"}</td>
	<td nowrap>{$logs[i].type}</td>
	<td>{$logs[i].log|nl2br}</td>
</tr>
{/section}
</table>
</div>

{if $pagination}<p align="center">{$pagination}</p>{/if}

{include file=footer.tpl}
<script>
{literal}
	calendar_setup();

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

	function check_date(obj){
		if(obj.value=="") return;
		if(!isValidDate(obj.value)){
			alert('Invalid date. Date must in format (yyyy-mm-dd)');
			obj.focus();
		}
		return;
	}
	
	function isValidDate(date) {
		var valid = true;
		
		date = date.replace(/-/g, '');

		if(date.length>8) return false;
		
		var year  = parseInt(date.substring(0, 4),10);
        var month = parseInt(date.substring(4, 6),10);
        var day   = parseInt(date.substring(6, 8),10);
        
        if((month < 1) || (month > 12)) valid = false;
        else if((day < 1) || (day > 31)) valid = false;
        else if(((month == 4) || (month == 6) || (month == 9) || (month == 11)) && (day > 30)) valid = false;
        else if((month == 2) && (((year % 400) == 0) || ((year % 4) == 0)) && ((year % 100) != 0) && (day > 29)) valid = false;
        else if((month == 2) && ((year % 100) == 0) && (day > 29)) valid = false;

		return valid;
	}
		
	function reload(auto){
		
		
		var valid=true;
		if(document.getElementById('user_id').value==""){
			//console.log(document.getElementById('user_id').value);
			if((document.getElementById('branch_id').value=="") && (document.getElementById('filter_type').value=="")){
				alert("Branch and Type can not be All when user is All.");				
				valid=false;
			}			
		}
		
		if(document.getElementById('inp_date_from').value!='' && !isValidDate(document.getElementById('inp_date_from').value)){
			alert('Invalid date. Date must in format (yyyy-mm-dd)');
			valid=false;
		}
		if(document.getElementById('inp_date_to').value!='' && !isValidDate(document.getElementById('inp_date_to').value)){
			alert('Invalid date. Date must in format (yyyy-mm-dd)');
			valid=false;
		}
		
		if(auto && valid) f_f.submit();
		else return valid;
	}
{/literal}
</script>
