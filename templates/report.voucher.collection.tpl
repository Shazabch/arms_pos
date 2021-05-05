{*
4/21/2011 12:18:39 PM Alex
- create by me

2/13/2012 10:12:58 AM Alex
- put background color

06/29/2020 11:04 AM Sheila
- Updated button css.
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
#content_id tr:nth-child(odd){
	background-color: #eeeeee;
}
</style>
{/literal}
{/if}

<h1>{$PAGE_TITLE}</h1>

{if !$no_header_footer}

<form name="f_a" method=post class="form">
	<p>
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name="branch_id">
			{foreach from=$branches key=id item=branch}
			<option value="{$branch.id}" {if $smarty.request.branch_id eq $branch.id}selected{/if}>{$branch.code}</option>
			{/foreach}
		</select> &nbsp;&nbsp;
	{/if}

	<b>Date From</b>
	<input type="text" name="from_date" value="{$form.from_date}" id="added1" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
	&nbsp;&nbsp;

	<b>To</b>
	<input type="text" name="to_date" value="{$form.to_date}" id="added2" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
	&nbsp;&nbsp;

	</p>
	<p>
	<button class="btn btn-primary" name=a value=show_report >{#SHOW_REPORT#}</button>&nbsp;&nbsp;
	{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
		<button class="btn btn-primary" name=a value=output_excel >{#OUTPUT_EXCEL#}</button>
	{/if}
	</p>
</form>

{/if}

<h2>{$report_title}</h2>

{if $detail}

<table class="report_table" id="report_tbl">
	<tr class=header>
		<th>Date</th>
			{foreach from=$branches key=id item=branch}
				{if $id ne $form.branch_id}
				<th>{$branch.code}</th>
				{/if}
			{/foreach}
		<th>Total Collect</th>
	</tr>
	<tbody id="content_id">
	{foreach from=$detail key=date item=data}
	    {assign var=total_collect value=0}
		<tr>
		    <td>{$date}</td>
			{foreach from=$branches key=id item=branch}
			{if $id ne $form.branch_id}
		    	<td class="r">{$data.$id.collect|number_format:2|ifzero}</td>
		    	{assign var=total_collect value=$total_collect+$data.$id.collect}
			{/if}
			{/foreach}
			<td class="r">{$total_collect|number_format:2|ifzero}</td>
		</tr>
	{/foreach}
	</tbody>
	<tr class="header">
	    <th align="right">Total</th>
		{foreach from=$branches key=id item=branch}
			{if $id ne $form.branch_id}
				<td class="r">{$total.$id.collect|number_format:2|ifzero}</td>
		    	{assign var=all_total_collect value=$all_total_collect+$total.$id.collect}
			{/if}
		{/foreach}
		<td class="r">{$all_total_collect|number_format:2|ifzero}</td>
	</tr>
</table>
{else}
	{if $table}- No Data -{/if}
{/if}
{if !$no_header_footer}
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
{/if}
{include file=footer.tpl}
