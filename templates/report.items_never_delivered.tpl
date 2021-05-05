
{include file=header.tpl}
{literal}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
{/literal}
{if !$no_header_footer}

<style>
{literal}
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
</script>

{/if}
<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}

{if !$no_header_footer}
<form method=post class=form name="f_a">
<b>SKU Created From</b> <input size=10 type=text name=date_from value="{$date_from}" id="date_from">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>Deliver Branch</b>
<select name="branch_id" id="branch_id">
	{foreach from=$branches key=bid item=b}
		{if !$branch_group.have_group.$bid and $bid neq 1}
			<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$b.code} - {$b.description}</option>
		{/if}
	{/foreach}
	{foreach from=$branch_group.header key=bgid item=bg}
		<option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
			{foreach from=$branch_group.items.$bgid key=bid item=b}
				<option class="bg_item" value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$b.code} - {$b.description}</option>
			{/foreach}
	{/foreach}
</select>
&nbsp;&nbsp;&nbsp;&nbsp;
<b>Sort by</b>
<select name="sort_by">
	<option value="sku_item_code" {if $smarty.request.sort_by eq 'sku_item_code'}selected{/if}>ARMS Code</option>
	<option value="artno" {if $smarty.request.sort_by eq 'artno'}selected{/if}>Art No</option>
	<option value="mcode" {if $smarty.request.sort_by eq 'mcode'}selected{/if}>MCode</option>
	<option value="description" {if $smarty.request.sort_by eq 'description'}selected{/if}>Description</option>
	<option value="added" {if $smarty.request.sort_by eq 'added'}selected{/if}>Added Date</option>
</select>
&nbsp;&nbsp;&nbsp;&nbsp;
<input type=hidden name=submit value=1>
<button name="show_report">{#SHOW_REPORT#}</button>
<button name="output_excel">{#OUTPUT_EXCEL#}</button>
</form>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<h2>
{if $single_branch}
Branch: {$branches[$branch_id].code}
{else}
Branch Group: {$branch_group.header.$branch_id.code}
{/if}
&nbsp;&nbsp;&nbsp;&nbsp;
Date From: {$smarty.request.date_from}&nbsp;&nbsp;&nbsp;&nbsp;
</h2>

<table width="100%" class="report_table">
	<tr class="header">
		<th></th>
		<th>ARMS Code</th>
		<th>Art No</th>
		<th>MCode</th>
		<th>Description</th>
		<th>Added Date</th>
	</tr>
	
	{foreach from=$table item=r name=f}
	<tr>
		<td align="center" width="5%">{$smarty.foreach.f.iteration}</td>
		<td align="center" width="10%">{$r.sku_item_code}</td>
		<td align="center" width="10%">{$r.artno}</td>
		<td align="center" width="10%">{$r.mcode}</td>
		<td>{$r.description}</td>
		<td align="center" width="10%">{$r.added}</td>
	</tr>
	{/foreach}
	
	<tr class="header">
	<td colspan="6">&nbsp;</td>
	</tr>
</table>

{/if}

{if !$no_header_footer}
{literal}
<script type="text/javascript">
Calendar.setup({
	inputField	:	"date_from",	// id of the input field
	ifFormat	:	"%Y-%m-%d",		// format of the input field
	button		:	"t_added1",		// trigger for the calendar (button ID)
	align		:	"Bl",			// alignment (defaults to "Bl")
	singleClick	:	true
});
</script>
{/literal}
{/if}

{include file=footer.tpl}
