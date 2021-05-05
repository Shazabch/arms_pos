{*
1/21/2011 5:44:31 PM Alex
- fix array looping while output data

*}
{include file=header.tpl}
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
.c0 {
	background:#eff;
}
.c1 {
	background:#efa;
}
.csunday {
	color:#f00;
}
</style>
{/literal}
<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}

<form method=post class=form>

<b>From</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;

{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> {dropdown name=branch_id all="-- All --" values=$branches selected=$smarty.request.branch_id key=id value=code}
{/if}

<input type=submit name=submit value="{#SHOW_REPORT#}">

</form>
{if !$data}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<table class="report_table small_printing" width=100%>
<tr class=header>
<th>No</th>
<th>Title</th>
<th>Date From</th>
<th>Date To</th>
<th>Time From</th>
<th>Time To</th>
<th>Added By</th>
</tr>
{foreach name=i from=$data item=item}
<tr>
	<td>{$smarty.foreach.i.iteration}</td>
	<td>{$item.title}</td>
	<td>{$item.date_from}</td>
	<td>{$item.date_to}</td>
	<td>{$item.time_from}</td>
	<td>{$item.time_to}</td>
	<td>{$item.l}</td>
</tr>
{/foreach}
</table>
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

