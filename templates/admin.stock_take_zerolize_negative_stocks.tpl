{*
*}

{include file=header.tpl}

{if !$no_header_footer}
{literal}

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
.err{
	color: red;
}
</style>
{/literal}

<script>
{literal}

function on_submit(){
	if(document.f_a.a.innerHTML == "Processing...") return false;
	if(!confirm('Are you sure want generate?')) return false;
	document.f_a.a.update("Processing...");
}

{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
<div class=err>
The following error(s) has occured:
<ul>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
</div>
{/if}

<form method="post" class="form" name="f_a" onsubmit="return on_submit();">

{if $url}
	<div>
	<font color="green">The list of SKU items with zerolize negative stocks has been generated, please view them from <a href="{$url}" target="_blank">HERE</a></font>.
	<br />
	</div>
{/if}

<table cellspacing="5">
	{if $BRANCH_CODE eq 'HQ'}
		<tr>
			<td><b>Branch</b></td>
			<td>
				<select name="branch_id">
					{foreach from=$branches item=b}
						<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
					{/foreach}
				</select>
			</td>
		</p>
		</tr>
	{else}
		<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
	{/if}
	<tr>
		<td><b>Date</b> <font color="red" size="+1">*</font></td>
		<td>
			<input size="10" type="text" name="date" value="{$smarty.request.date|default:$form.date}" id="date" readonly>
			<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
		</td>
	</tr>
	<tr>
		<td><b>Location</b> <font color="red" size="+1">*</font></td>
		<td>
			<input size="10" type="text" name="location" value="{$smarty.request.location|default:$form.location}">
		</td>
	</tr>
	<tr>
		<td><b>Shelf</b> <font color="red" size="+1">*</font></td>
		<td>
			<input size="10" type="text" name="shelf" value="{$smarty.request.shelf|default:$form.shelf}">
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<br />
			<input type="hidden" name="submit" value="1" />
			<button name="a" value="run">Generate</button>
		</td>
	</tr>
</p>
</table>
</form>

{literal}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "date",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
</script>
{/literal}

{include file=footer.tpl}
