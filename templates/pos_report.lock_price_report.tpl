{include file="header.tpl"}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<h1>{$PAGE_TITLE}</h1>
{if $error neq ""}
The following error(s) has occured:
<ul>
{foreach from=$error item=e}
<li>{$e}</li>
{/foreach}
</ul>
{/if}
<form method="post" name="myForm" class="form">
<input type="hidden" name="load_report" value="1" />
<!-- Start Date -->
<b>From</b><input size=10 type=text name=date_from value="{$smarty.request.date_from}" id="date_from"><img align=absmiddle src="ui/calendar.gif" id="dt_from" style="cursor: pointer;" title="Select Date">&nbsp;&nbsp;&nbsp;&nbsp;<b>To</b><input size=10 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
<img align=absmiddle src="ui/calendar.gif" id="dt_to" style="cursor: pointer;" title="Select Date">
<!-- End Date -->
<!-- Start Cashier-->
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<b>Cashier</b>
<select name="cashier_id">
	<option value="">-- All --</option>
	{foreach from=$cashier key=cid item=r}
	<option value="{$cid}" {if $smarty.request.cashier_id eq $cid}selected {/if}>{$r.u}</option>
	{/foreach}
</select>
<!-- End Cashier -->
<!-- Start Branch-->
<p>
{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b>
<select name="branch_id" style="visibility:visible !important;">
	<option value="">-- All --</option>
	{foreach from=$branches key=bid item=r}
		<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$r.code} {if $r.description}-{/if} {$r.description}</option>
	{/foreach}
</select>
&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
<!-- End Branch-->
<!-- Start Counter -->
<b>Counter</b>
<select name="counter_id" style="visibility:visible !important;">
	<option value="">-- All --</option>
	{foreach from=$counters key=counter_id item=r}
	<option value="{$counter_id}" {if $smarty.request.counter_id eq $counter_id}selected{/if}>{$r.network_name}</option>
	{/foreach}
</select>
<!-- End Counter -->
</p>
<!-- Start Category-->
<p>{include file="category_autocomplete.tpl" all=1}
<!-- End Category -->
<input type="submit" name="submits" value="{#SHOW_REPORT#}" />
</form>

{if isset($smarty.request.submits) && $error eq ""}
	{if !$temp_price_items_table}
		<div style="text-align:center">-- No Data --</div>
	{else}
	<h2>
		Date from {$report_header.date_from} to {$report_header.date_to}
		&nbsp;&nbsp;&nbsp;
		Cashier: {$report_header.user}
		&nbsp;&nbsp;&nbsp;
		{if $BRANCH_CODE eq 'HQ'}
		Branch: {$report_header.branch} 
		&nbsp;&nbsp;&nbsp;
		{/if}
		Counter: {$report_header.counter}
		&nbsp;&nbsp;&nbsp;
		Category: {$report_header.category}
	</h2>
	<table width="100%" class="sortable report_table small_printing" id="table_{$bid}">
		<thead>
			<tr class="header">
				<th>Active</th>
				<th>Branch</th>
				<th>ARMS Code</th>
				<th>Item Description</th>
				<th>Selling Price</th>
				<th>Temp Price</th>
				<th>Reason</th>
				<th>Temp By</th>
				<th>Counter</th>
				<th>Status</th>
				<th>Added Date</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$temp_price_items_table item=lp_info}
			<tr>
				<td align="center">{if $lp_info.item_id}<img src="ui/approved.png"/>{/if}</td>
				<td>{$lp_info.branch}</td>
				<td>{$lp_info.arms_code}</td>
				<td>{$lp_info.item_description}</td>
				<td align="right">{$lp_info.selling_price|number_format:2}</td>
				<td align="right">{$lp_info.temp_price|number_format:2}</td>
				<td>{$lp_info.reason}</td>
				<td>{$lp_info.username}</td>
				<td>{$lp_info.counter}</td>
				<td>{if $lp_info.active eq 0}Unset{/if}</td>
				<td>{$lp_info.added_datetime}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
	{/if}
{/if}
{literal}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "dt_from",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "dt_to",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
</script>
{/literal}
{include file="footer.tpl"}