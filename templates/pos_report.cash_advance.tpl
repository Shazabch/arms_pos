{*
06/30/2020 04:43 PM Sheila
- Updated button css.
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
.col_pi_status1{
	color:green;
}
.col_pi_status2{
	color:red;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function init_calendar(){
	Calendar.setup({
		inputField     :    "inp_date_from",
		ifFormat       :    "%Y-%m-%d",
		button         :    "img_date_from",
		align          :    "Bl",
		singleClick    :    true
	});
	Calendar.setup({
		inputField     :    "inp_date_to",
		ifFormat       :    "%Y-%m-%d",
		button         :    "img_date_to",
		align          :    "Bl",
		singleClick    :    true
	});
}

function submit_form(type){
	if(type == "excel") document.f_a['export_excel'].value = 1;
	else document.f_a['export_excel'].value = "";
	
	document.f_a.submit();
}
{/literal}
</script>
<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
		<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

{if !$no_header_footer}
<form name="f_a" method="post" onSubmit="return false;" class="stdframe">
	<input type="hidden" name="show_report" value="1" />
	<input type="hidden" name="export_excel" value="" />

	<p>
		<b>Counter</b> 
		<select name="counters">
			{foreach from=$counters item=r}
				{capture assign=counter_all}{$r.branch_id}|all{/capture}
				{capture assign=counter_item}{$r.branch_id}|{$r.id}{/capture}
				{if $last_bid ne $r.branch_id}
					<option value="{$counter_all}" {if $smarty.request.counters eq $counter_all}selected {/if}>{$r.code}</option>
					{assign var=last_bid value=$r.branch_id}
				{/if}
				<option value="{$counter_item}" {if $smarty.request.counters eq $counter_item}selected {/if}>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$r.network_name}
				</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
		
		<b>From</b>
		<input name="date_from" id="inp_date_from" size="10" maxlength="10"  value="{$smarty.request.date_from|date_format:"%Y-%m-%d"}" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date From" />
		&nbsp;&nbsp;&nbsp;&nbsp;
		
		<b>To</b>
		<input name="date_to" id="inp_date_to" size="10" maxlength="10"  value="{$smarty.request.date_to|date_format:"%Y-%m-%d"}" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date To" />
		&nbsp;&nbsp;&nbsp;&nbsp;

		<b>Reason</b>
		<select name="reason">
			<option value="" {if !$smarty.request.cashier}selected {/if}>-- All --</option>
			{foreach from=$config.pos_cash_advance_reason_list key=r item=reason}
				<option value="{$reason}" {if $smarty.request.reason eq $reason}selected {/if}>{$reason}</option>
			{/foreach}
		</select>
	</p>
	
	<p>
		<b>Cashier</b>
		<select name="cashier">
			<option value="" {if !$smarty.request.cashier}selected {/if}>-- All --</option>
			{foreach from=$cashiers key=cid item=r}
				<option value="{$r.id}" {if $smarty.request.cashier eq $r.id}selected {/if}>{$r.u}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
		
		<b>Approved By</b>
		<select name="approved_by">
			<option value="" {if !$smarty.request.approved_by}selected {/if}>-- All --</option>
			{foreach from=$cashiers key=cid item=r}
				<option value="{$r.id}" {if $smarty.request.approved_by eq $r.id}selected {/if}>{$r.u}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
		
		<b>Remark</b>
		<input type="text" name="remark" value="{$smarty.request.remark}" size="50" />&nbsp;&nbsp;&nbsp;&nbsp;
	</p>
	
	<p>
		<button class="btn btn-primary" onClick="submit_form();">{#SHOW_REPORT#}</button>
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-primary" onClick="submit_form('excel');">{#OUTPUT_EXCEL#}</button>
		{/if}
	</p>
</form>
{/if}

<br />
{if $smarty.request.show_report && !$err}
	{if !$data}
		-- No Data --
	{else}
		<h2>{$report_title}</h2>
		
		<table class="report_table" width="100%" {if $no_header_footer}border="1"{/if}>
			<tr class="header">
				<th>Date & Time</th>
				<th>Counter</th>
				<th>Cashier</th>
				<th>Approved By</th>
				<th>Reason</th>
				<th width="50%">Remark</th>
				<th>Amount</th>
			</tr>
			
			{foreach from=$data key=bid item=counter_data}
				<tr>
					<td colspan="7" bgcolor="#ccffff">{$branches.$bid}</td>
				</tr>
				{foreach from=$counter_data.details key=dummy item=r}
					<tr>
						<td align="center">{$r.timestamp}</td>
						<td>{$r.counter_name}</td>
						<td>{$r.cashier}</td>
						<td>{$r.approved_by}</td>
						<td>{$r.reason}</td>
						<td>{$r.remark}</td>
						<td class="r">{$r.ca_amount|number_format:2}</td>
					</tr>
					{assign var=ttl_ca_amt value=$ttl_ca_amt+$r.ca_amount|round2}
				{/foreach}
			{/foreach}
			<tr class="header sortbottom">
				<td colspan="6" class="r"><b>Total</b></td>
				<td class="r"><b>{$ttl_ca_amt|number_format:2}</b></td>
			</tr>
		</table>
	{/if}
{/if}

{if !$no_header_footer}
<script>
init_calendar();
</script>
{include file='footer.tpl'}
{/if}
