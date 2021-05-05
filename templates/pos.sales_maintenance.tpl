{*
7/9/2014 5:12 PM Fithri
- add option to alter timestamp by hour, minute & second

9/13/2017 5:55 PM Andy
- Combine submit_wrong_date() and submit_time_range() into one function submit_change().
- Enhanced to check and change receipt_no.
*}

{include file=header.tpl}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var LOADING = '<img src="/ui/clock.gif" />';
{literal}
function init_calendar(sstr){
	Calendar.setup({
	    inputField     :    "date",     // id of the input field
	    ifFormat       :    "%e/%m/%Y",      // format of the input field
	    button         :    "t_date",  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
	{/literal}
	{if $smarty.request.a eq 'wrong_date' || $smarty.request.a eq 'time_range'}
	{if $items or $pos_drawer or $pos_cash_domination or $pos_receipt_cancel or $pos_cash_history}
	{literal}
	Calendar.setup({
	    inputField     :    "date_change",     // id of the input field
	    ifFormat       :    "%e/%m/%Y",      // format of the input field
	    button         :    "t_date_change",  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
	{/literal}
	{/if}
	{/if}
	{literal}
}

function get_counter(branch_id,selected)
{
	var counter = '';
	
	if (selected != '') counter = '&counter_id='+selected;
	if (branch_id == 0)
	{
		branch_id = document.f_a.branch_id.value;
	}
	
	new Ajax.Updater('counter',phpself+'?a=get_counter&branch_id='+branch_id+counter,
	{
	    method: 'post'
	});
}

function do_submit()
{
	if (document.f_a.a.value != '')
	document.f_a.submit();
}

function confirm_fix(f)
{
	if (f.date_change.value == '')
	{
		alert('Please select date');
		return false;
	}
	if (!confirm('Are you sure?'))
		return false;
	return true;
}

function check_all(obj,name)
{
	var chkbox = $$('#'+name+' input.chkbox');
	
	for(i=0;i<chkbox.length;i++)
	{
		chkbox[i].checked = obj.checked;
	}
}

function sel_type(obj)
{
	if (obj.value == 'time_range')
	{
		$('time_selection').show();
	}
	else
	{
		$('time_selection').hide();
	}
}

function chk_limit(f) {
	if (int(f.value) > 59) f.value = 59;
}

{/literal}
</script>
{if $smarty.request.msg}{assign var=msg value=$smarty.request.msg}{/if}
<p align=center><font color=red>{$msg}</font></p>

<h1>{$PAGE_TITLE}</h1>

<form class=form name=f_a method=post>
{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> {dropdown name=branch_id onchange="get_counter(0);" values=$branches selected=$smarty.request.branch_id key=id value=code}
&nbsp;&nbsp;
{/if}
<b>Counter</b> <span id=counter></span>
&nbsp;&nbsp;
<b>Date</b> <input id=date name=date value="{$smarty.request.date}" size=10> <img align=absbottom src="ui/calendar.gif" id="t_date" style="cursor: pointer;" title="Select Date"/>

&nbsp;&nbsp;
<b>Search By</b> 
<select name="a" onchange="sel_type(this);">
<option value="wrong_date" {if $smarty.request.a eq 'wrong_date'}selected{/if}>Wrong Date</option>
<option value="time_range" {if $smarty.request.a eq 'time_range'}selected{/if}>Time Range</option>
</select>

<span id=time_selection {if $smarty.request.a == '' || $smarty.request.a == 'wrong_date' || $smarty.request.a == 'submit_wrong_date'}style="display:none"{/if}>
<br /><br />
<b>Time Selection</b>&nbsp;&nbsp;
From </td><td><input name=from_time size=6 maxlength=8 value="{$smarty.request.from_time}">
To </td><td><input name=to_time size=6 maxlength=8 value="{$smarty.request.to_time}"> (eg. From 00:00:00 To 15:28:50)
</span>
&nbsp;&nbsp;&nbsp;&nbsp;
<input name=fsubmit type="submit" value="Refresh">
</form>

<form name=f method=post onsubmit="return confirm_fix(this);">
<input name=a value="submit_change" type=hidden>
<input name=date value="{$smarty.request.date}" type=hidden>
<input name=counter_id value="{$smarty.request.counter_id}" type=hidden>
<input name=branch_id value="{$smarty.request.branch_id}" type=hidden>
<input name=l value="{$smarty.request.l}" type=hidden>
{if $smarty.request.a eq 'wrong_date' || $smarty.request.a eq 'time_range'}
{if $items or $pos_drawer or $pos_cash_domination or $pos_receipt_cancel or $pos_cash_history}
<b>Date</b> <input id=date_change name=date_change value="{$smarty.request.date_change}" size=10> <img align=absbottom src="ui/calendar.gif" id="t_date_change" style="cursor: pointer;" title="Select Date"/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<b>Timestamp</b>&nbsp;
<select name="add_minus">
	<option value="add">Add</option>
	<option value="minus">Minus</option>
</select>&nbsp;
<input name="add_hour" size="1" maxlength="3" onchange="mi(this);" /> H : 
<input name="add_minute" size="1" maxlength="2" onchange="mi(this);chk_limit(this);" /> M : 
<input name="add_second" size="1" maxlength="2" onchange="mi(this);chk_limit(this);" /> S
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type=submit value="Change">
{/if}
{/if}
{if $items}
<h3>POS</h3>
<table class="tb" id=data width=100% cellpadding=4 cellspacing=0 border=0>
<tr class=header style="background:#fe9;">
<th><input type=checkbox onclick="check_all(this,'data')"></th>
<th>ID</th>
<th>Receipt No</th>
<th>Amount</th>
<th>Amount<br>Tender</th>
<th>Amount<br>Change</th>
<th>Date</th>
<th>Pos Time</th>
<th>Start Time</th>
<th>End Time</th>
<th>Cancel</th>
</tr>
{foreach from=$items item=item}
<tr>
<td align="center">
	<input class="chkbox" name="id[{$item.id}]" type="checkbox" />
	<input name="receipt_no[{$item.id}]" type="hidden" value="{$item.receipt_no}" />

</td>
<td>{$item.id}</td>
<td>{$item.receipt_no}</td>
<td>{$item.amount}</td>
<td>{$item.amount_tender}</td>
<td>{$item.amount_change}</td>
<td>{$item.date}</td>
<td>{$item.pos_time}</td>
<td>{$item.start_time}</td>
<td>{$item.end_time}</td>
<td>{$item.cancel_status}</td>
</tr>
{/foreach}
</table><br>
{else}
<p align=center>-- No POS Data --</p>
{/if}
{if $pos_drawer}
<h3>POS Drawer</h3>
<table class="tb" id=pos_drawer width=100% cellpadding=4 cellspacing=0 border=0>
<tr class=header style="background:#fe9;">
<th><input type=checkbox onclick="check_all(this,'pos_drawer')"></th>
<th>ID</th>
<th>Cashier ID</th>
<th>Date</th>
<th>Timestamp</th>
</tr>
{foreach from=$pos_drawer item=item}
<tr>
<td align=center><input class=chkbox name=pos_drawer_id[{$item.id}] type=checkbox></td>
<td>{$item.id}</td>
<td>{$item.user_id}</td>
<td>{$item.date}</td>
<td>{$item.timestamp}</td>
</tr>
{/foreach}
</table><br>
{else}
<p align=center>-- No POS Drawer Data --</p>
{/if}

{if $pos_cash_domination}
<h3>POS Cash Denomination</h3>
<table class="tb" id=pos_cash_domination width=100% cellpadding=4 cellspacing=0 border=0>
<tr class=header style="background:#fe9;">
<th><input type=checkbox onclick="check_all(this,'pos_cash_domination')"></th>
<th>ID</th>
<th>Cashier ID</th>
<th>Clear Drawer</th>
<th>Date</th>
<th>Timestamp</th>
</tr>
{foreach from=$pos_cash_domination item=item}
<tr>
<td align=center><input class=chkbox name=pos_cash_domination_id[{$item.id}] type=checkbox></td>
<td>{$item.id}</td>
<td>{$item.user_id}</td>
<td>{$item.clear_drawer|ifzero:0}</td>
<td>{$item.date}</td>
<td>{$item.timestamp}</td>
</tr>
{/foreach}
</table><br>
{else}
<p align=center>-- No POS Cash Denomination Data --</p>
{/if}

{* if $pos_receipt_cancel}
<h3>POS Receipt Cancel</h3>
<table class="tb" id=pos_receipt_cancel width=100% cellpadding=4 cellspacing=0 border=0>
<tr class=header style="background:#fe9;">
<th><input type=checkbox onclick="check_all(this,'pos_receipt_cancel')"></th>
<th>ID</th>
<th>Receipt No</th>
<th>Cancelled by</th>
<th>Cancelled Time</th>
<th>Date</th>
</tr>
{foreach from=$pos_receipt_cancel item=item}
<tr>
<td align=center><input class=chkbox name=pos_receipt_cancel_id[{$item.id}] type=checkbox></td>
<td>{$item.id}</td>
<td>{$item.receipt_no}</td>
<td>{$item.cancelled_by}</td>
<td>{$item.cancelled_time}</td>
<td>{$item.date}</td>
</tr>
{/foreach}
</table><br>
{else}
<p align=center>-- No POS Receipt Cancel Data --</p>
{/if *}

{if $pos_cash_history}
<h3>POS Cash History</h3>
<table class="tb" id=pos_cash_history width=100% cellpadding=4 cellspacing=0 border=0>
<tr class=header style="background:#fe9;">
<th><input type=checkbox onclick="check_all(this,'pos_cash_history')"></th>
<th>ID</th>
<th>Cashier ID</th>
<th>Collected by</th>
<th>Amount</th>
<th>Date</th>
<th>Timestamp</th>
</tr>
{foreach from=$pos_cash_history item=item}
<tr>
<td align=center><input class=chkbox name=pos_cash_history_id[{$item.id}] type=checkbox></td>
<td>{$item.id}</td>
<td>{$item.user_id}</td>
<td>{$item.collected_by}</td>
<td>{$item.amount}</td>
<td>{$item.date}</td>
<td>{$item.timestamp}</td>
</tr>
{/foreach}
</table><br>
{else}
<p align=center>-- No POS Cash History Data --</p>
{/if}
</form>

<script>
init_calendar();
get_counter({if $BRANCH_CODE ne 'HQ'}{$branches.$BRANCH_CODE.id}{else}0{/if}{if $smarty.request.counter_id},{$smarty.request.counter_id}{/if});
</script>
{include file=footer.tpl}
