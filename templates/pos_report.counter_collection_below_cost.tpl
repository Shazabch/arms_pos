{*
10/16/2014 1:41 PM Justin
- Enhanced to show extra note.
*}

{include file=header.tpl}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var date_from = '{$smarty.request.date_from}';
var date_to = '{$smarty.request.date_to}';
</script>
{literal}
<style>

</style>
<script>

</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>

<!-- Item Details -->
<div id="div_details" style="display:none;width:800px;height:400px;">
<div style="float:right;padding-bottom:5px;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
<div id="div_content">
</div>
</div>

<form method="post" name="myForm" class="form">
<input type="hidden" name="a" value="load_table" />
<b>From</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>Cashier</b> <select name="cashier_id">
	<option value="all">-- All --</option>
	{foreach from=$cashier key=cid item=r}
	    <option value="{$cid}" {if $smarty.request.cashier_id eq $cid}selected {/if}>{$r.u}</option>
	{/foreach}
</select>
{if $BRANCH_CODE eq 'HQ'}
&nbsp;&nbsp;&nbsp;&nbsp;
<b>Branch</b> <select name="branch_id">
	<option value="all">-- All --</option>
	{foreach from=$branches key=bid item=r}
	    <option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$r}</option>
	{/foreach}
</select>
{/if}

<p>

</p>
<input type="submit" name="submits" value="{#SHOW_REPORT#}" />
<br />
<p>
Note: <br />
- Report maximum shown in 1 month.<br />
- Please ensure the sales date selected above have been fully finalised.
</p>
</form>


{if isset($smarty.request.submits)}
{if !$table}
No data
{else}
{foreach from=$table key=bid item=p}
<h3>{$branches.$bid}: {count var=$p} record(s)</h3>
<table width="100%" class="sortable report_table small_printing" id="table_{$bid}">
<tr class="header">
    <th >No.</th>
	<th >Cashier Name</th>
	<th >Counter</th>
	<th >Date</th>
	<th >Time</th>
	<th>ARMS Code</th>
	<th>Description</th>
	<th>Selling Price</th>
	<th >Cost</th>
	<th >Different</th>
</tr>
	{foreach from=$p item=r name=f}
	    <tr>
	        <td>{$smarty.foreach.f.iteration}</td>
	        <td>{$r.u|default:'-'}</td>
	        <td>{$r.counter_id}</td>
	        <td>{$r.timestamp|date_format:'%Y-%m-%d'}</td>
	        <td>{$r.timestamp|date_format:'%I:%M:%S %p'}</td>
	        <td>{$r.sku_item_code}</td>
	        <td>{$r.description}</td>
	        <td class="r">{$r.sell|number_format:2}</td>
	        <td class="r">{$r.grn_cost|number_format:2}</td>
	        <td class="r">{$r.different|number_format:2}</td>
	    </tr>
	{/foreach}
<tr class="header sortbottom">
	<td colspan="7" class="r"><b>Total</b></td>
	<td class="r">{$total.$bid.sell|number_format:2}</td>
	<td class="r">{$total.$bid.grn_cost|number_format:2}</td>
	<td class="r">{$total.$bid.different|number_format:2}</td>
</tr>
</table>
{/foreach}
{/if}
{/if}

{literal}
<script type="text/javascript">
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
</script>
{/literal}
{include file=footer.tpl}
