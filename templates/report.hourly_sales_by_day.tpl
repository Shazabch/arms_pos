{*
5/25/2011 4:10:11 PM Alex
- change "month" to "year and month"

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

03/10/2016 15:40 Edwin
- Enhanced to enable select child branch group in branch selection

06/30/2020 10:57 AM Sheila
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
.c0 {
	background:#eff;
}
.c1 {
	background:#efa;
}
.csunday {
	color:#f00;
}

option.bg {
	font-weight:bold;
	padding-left:10px;
}

option.bg_item {
	padding-left:20px;
}
</style>
{/literal}
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
<form method=post class=form>

<input name=view_type value="{$smarty.request.view_type}" type=hidden>
<b>Date</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;

<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;

{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> <select name="branch_id">
	    <option value="">-- All --</option>
	    {foreach from=$branches item=b}
	        {if !$branch_group.have_group[$b.id]}
	        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code} - {$b.description}</option>
	        {/if}
	    {/foreach}
	    {if $branch_group.header}
	        <optgroup label="Branch Group">
				{foreach from=$branch_group.header key=bgid item=bg}
		    	    <option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
		    	    {foreach from=$branch_group.items.$bgid item=r}
						{if $config.sales_report_branches_exclude}
						{if in_array($r.code,$config.sales_report_branches_exclude)}
						{assign var=skip_this_branch value=1}
						{else}
						{assign var=skip_this_branch value=0}
						{/if}
						{/if}
						{if !$skip_this_branch}
		    	        <option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
						{/if}
		    	    {/foreach}
		    	{/foreach}
			</optgroup>
		{/if}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
{/if}

<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} />Exclude inactive SKU</label>

<p>
{include file="category_autocomplete.tpl" all=true}
</p>
<input type=hidden name=submit value=1>
<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name=output_excel>{#OUTPUT_EXCEL#}</button>
{/if}
<br>Note: Report Maximum Show 1 {if $smarty.request.view_type == 'day'}Month{else}Year{/if}
<input type=hidden name=report_title value="{$report_title}">
</form>
{/if}
{if !$data}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<h2>
{$report_title}
<!--Category: {$cat_desc|default:"All"} &nbsp;&nbsp;&nbsp;&nbsp;
Date: {$smarty.request.date_from} to {$smarty.request.date_to} &nbsp;&nbsp;&nbsp;&nbsp;
Branch: {$branch_code}-->
</h2>
{capture assign=hrcount}{count var=$hour}{/capture}
<table class="report_table small_printing" width=100%>
	<tr class=header>
	<th>{$smarty.request.view_type|ucfirst}</th>
	{foreach from=$hour key=h item=hrs}
		<th nowrap>{$hrs}</th>
	{/foreach}
	<th>Total</th>
	<th>AVG Hour Amount</th>
	</tr>
	{foreach from=$days item=day}
	<tr>
	<th>{if $smarty.request.view_type eq 'day'}{$day}{else}{$years_arr.$day} {$months_arr.$day|str_month}{/if}</th>
	{foreach from=$hour key=h item=hrs}
		<td align=right>{$data.$day.$h|number_format:2|ifzero:"-"}</td>
	{/foreach}
	<td align=right>{$day_total.$day|number_format:2|ifzero:"-"}</td>
	<td align=right>{$day_total.$day/$hr_cnt|number_format:2|ifzero:"-"}</td>
	</tr>
	{/foreach}
	<tr>
	<th>Total</th>
	{foreach from=$hour key=h item=hrs}
		<td align=right>{$hour_total.$h|number_format:2|ifzero:"-"}</td>
	{/foreach}
	<td align=right>{$grand_total|number_format:2|ifzero:"-"}</td>
	</tr>
</table>
{/if}
{if !$no_header_footer}
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
{/if}
{include file=footer.tpl}
