{*
10/2/2019 1:22 PM William
- Add new extra module "Machines Upload Summary".

10/3/2019 4:44 PM William
-Fixed bug show error message "Internal Server Error" when Export to Excel.
- Add and display title of search filter.
*}
{include file='header.tpl'}

{if !$no_header_footer}
<style>
{literal}
tr:nth-child(odd) {
	background: #ffffff;
}
tr:nth-child(even) {
	background: #eeeeee;
}
{/literal}
</style>
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
{/if}

<h1>{$PAGE_TITLE}</h1>
{if !$no_header_footer}
<form name="f_a" method="post" class="form">
<input type="hidden" name="a" value="generate_report"/>
<p>
	<span>
		<b>Date From</b>
		<input type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size="12"> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
		&nbsp;&nbsp;
		<b>To</b>
		<input type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size="12"> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
		&nbsp;&nbsp;
	</span>&nbsp;&nbsp;&nbsp;&nbsp;
	
	{if $BRANCH_CODE eq 'HQ'}
	<b>Filter by Branch (Machine)</b> <select name="branch_id">
		<option value="">-- All --</option>
			{foreach from=$branches item=b}
				<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{else}
		<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
	{/if}
	</p>
	
	<p>
	<span>
		<b>Filter by Transfer DO From (Agent)</b> 
		<select name="branch_id_from">
		<option value="">-- All --</option>
			{foreach from=$branches item=b}
				<option value="{$b.id}" {if $smarty.request.branch_id_from eq $b.id}selected {/if}>{$b.code}</option>
			{/foreach}

		</select>
	</span>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<span>
		<b>Status</b>
		<select name="status">
			<option value="0" {if $smarty.request.status == 0}selected{/if}>-- All --</option>
			<option value="2" {if $smarty.request.status == 2}selected{/if}>Approved</option>
			<option value="1" {if $smarty.request.status == 1}selected{/if}>Not Approved</option>
			<option value="3" {if $smarty.request.status == 3}selected{/if}>Checkout</option>
		</select>
	</span>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<span>
		<b>By user</b>
		<select name="user_id">
			<option {if $smarty.request.user_id eq ''}selected{/if} value="0">-- All --</option>
			{section name=i loop=$user}
			<option value={$user[i].id} {if $smarty.request.user_id eq $user[i].id}selected{assign var=_u value=`$user[i].u`}{/if}>{$user[i].u}</option>
			{/section}
		</select>
	</span>&nbsp;&nbsp;&nbsp;&nbsp;
</p>
<input type="hidden" name="submit" value="1" />
<button name="generate_report">Show Report</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
</form>
{/if}

{if !$table}
	{if $smarty.request.submit}-- No data --{/if}
{else}
<h3>{$report_title}</h3>
<table class="sortable" cellpadding="4" cellspacing="1" border="0" style="padding:1px;border:1px solid #000">
	<thead>	
		<tr class="thd">
			<th>Machine No</th>
			<th>Transfer DO</th>
			<th>Credit Sales DO</th>
		</tr>
	</thead>
	
	<tbody>
	{foreach from=$table item=r}
	<tr>
		<td align="left">{$r.b_code}</td>
		<td align="left">
			{if $r.transfer_do_no}
				<a {if !$no_header_footer}href="do.php?a=view&id={$r.transfer_id}&branch_id={$r.transfer_do_bid}&do_type=transfer" target="_blank"{/if}>{$r.transfer_do_no}</a>
			{else}
				{if $r.transfer_id}
					<a {if !$no_header_footer}href="do.php?a=view&id={$r.transfer_id}&branch_id={$r.transfer_do_bid}&do_type=transfer" target="_blank"{/if}>{$r.prefix}{$r.transfer_id|string_format:"%05d"}(DD)</a>
				{/if}
			{/if}
		</td>
		<td align="left">
			{if $r.credit_sales_do_no}
				<a {if !$no_header_footer}href="do.php?a=view&id={$r.credit_sales_id}&branch_id={$r.credit_sales_bid}&do_type=credit_sales" target="_blank"{/if}>{$r.credit_sales_do_no}</a>
			{else}
				{if $r.credit_sales_id}
					<a {if !$no_header_footer}href="do.php?a=view&id={$r.credit_sales_id}&branch_id={$r.credit_sales_bid}&do_type=credit_sales" target="_blank"{/if}>{$r.credit_sales_prefix}{$r.credit_sales_id|string_format:"%05d"}(DD)</a>
				{/if}
			{/if}
		</td>
	</tr>
	{/foreach}
	</tbody>
</table>
{/if}

{literal}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "added1",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "added2",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
</script>
{/literal}

{include file='footer.tpl'}