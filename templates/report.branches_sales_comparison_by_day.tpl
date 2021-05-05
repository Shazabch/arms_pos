{*
4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

06/30/2020 10:41 AM Sheila
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
<input type=hidden name=report_title value="{$report_title}">
<b>From</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;

<b>Department</b>
<select name="department_id">
<option value=0>-- All --</option>
{foreach from=$departments item=dept}
<option value={$dept.id} {if $smarty.request.department_id eq $dept.id}selected{/if}>{$dept.description}</option>
{/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;
{if $branch_group.header}
<b>Branch Group</b>
	<select name="branch_group">
	<option value="">-- All --</option>
	{foreach from=$branch_group.header item=r}
	    <option value="{$r.id}" {if $smarty.request.branch_group eq $r.id}selected {/if}>{$r.code}</option>
	{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
{/if}

<b>By</b>
<select name="view_type">
<option value="day" {if $smarty.request.view_type eq 'day'}selected{/if}>Day</option>
<option value="week" {if $smarty.request.view_type eq 'week'}selected{/if}>Week</option>
</select>

<input type=hidden name=submit value=1>
<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name=output_excel>{#OUTPUT_EXCEL#}</button>
{/if}
<br>Note: Report Maximum Shown 365 Days
</form>
{/if}
{if !$data}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<h2>
{$report_title}
<!--Financial Start Year : {$financial_start_date} &nbsp;&nbsp;&nbsp;&nbsp;
Date: {$smarty.request.date_from} to {$smarty.request.date_to} &nbsp;&nbsp;&nbsp;&nbsp;
Department: {$dept_desc} &nbsp;&nbsp;&nbsp;&nbsp;
Type: {$smarty.request.view_type|capitalize}
{if $smarty.request.branch_group}&nbsp;&nbsp;&nbsp;&nbsp;
Branch Group: {$branch_group.header[$smarty.request.branch_group].code}
{/if}-->
</h2>
<table class="report_table small_printing" width=100%>
<tr class=header>
	<th>Branch</th><th rowspan=2>Day</th>
	{if !$smarty.request.branch_group}
	    {foreach from=$branches item=b}
		
			{if $config.sales_report_branches_exclude}
			{if in_array($b.code,$config.sales_report_branches_exclude)}
			{assign var=skip_this_branch value=1}
			{else}
			{assign var=skip_this_branch value=0}
			{/if}
			{/if}
		
	        {if !in_array($b.id,$branch_group.have_group) and $b.code ne 'HQ' and !$skip_this_branch}
				<th rowspan=2>{$b.code}</th>
			{/if}
		{/foreach}
		{foreach from=$branch_group.header item=bg}
	        <th rowspan=2>{$bg.code}</th>
	    {/foreach}
	{else}
	    {foreach from=$branch_group.items[$smarty.request.branch_group] key=bid item=b}
		
			{if $config.sales_report_branches_exclude}
			{if in_array($b.code,$config.sales_report_branches_exclude)}
			{assign var=skip_this_branch value=1}
			{else}
			{assign var=skip_this_branch value=0}
			{/if}
			{/if}
			
			{if !$skip_this_branch}
	        <th rowspan=2>{$b.code}</th>
			{/if}
			
	    {/foreach}
	{/if}
	<th rowspan=2>Total</th>
	<th rowspan=2>YTD</th>
 
</tr>
<tr class=header>
	<th>{if $smarty.request.view_type eq 'day'}Date{else}Week{/if}</th>
</tr>
{assign var=$cross_year value=0}

{if $smarty.request.view_type eq 'day'}
    {assign var=temp value=0}
	{foreach from=$data key=date item=d}
	{assign var=dayname value=$date|date_format:"%A"}
	<tr {if $dayname eq 'Sunday'}class=csunday{else}class={cycle values="c0,c1"}{/if}>
		<th>{$date}</th>
		<th>{$dayname}</th>
		{if !$smarty.request.branch_group}
		    {foreach from=$branches item=b}
			
				{if $config.sales_report_branches_exclude}
				{if in_array($b.code,$config.sales_report_branches_exclude)}
				{assign var=skip_this_branch value=1}
				{else}
				{assign var=skip_this_branch value=0}
				{/if}
				{/if}
			
		        {if !in_array($b.id,$branch_group.have_group) and $b.code ne 'HQ' and !$skip_this_branch}
					<td align=right>{$d[$b.id]|number_format:2|ifzero:"-"}</td>
				{/if}
			{/foreach}
			{foreach from=$branch_group.header key=bg_id item=bg}
			    {assign var=bgid value=$bg_id+10000}
		        <td align=right>{$d[$bgid]|number_format:2|ifzero:"-"}</td>
		    {/foreach}
		{else}
		    {foreach from=$branch_group.items[$smarty.request.branch_group] key=bid item=b}
			
				{if $config.sales_report_branches_exclude}
				{if in_array($b.code,$config.sales_report_branches_exclude)}
				{assign var=skip_this_branch value=1}
				{else}
				{assign var=skip_this_branch value=0}
				{/if}
				{/if}
			
		       {if !$skip_this_branch} 
				<td align=right>{$d.$bid|number_format:2|ifzero:"-"}</td>
				{/if}
				
		    {/foreach}
		{/if}
		<td class=r>{$d.total|number_format:2|ifzero:'-'}</td>
		{if $date >= $financial_end_date and $cross_year eq 0}
		    {assign var=cross_year value=1}
		    {assign var=ytd_amount value=0}
		{/if}
		{assign var=ytd_amount value=$ytd_amount+$d.total}
		<td class=r>{$ytd_amount|number_format:2|ifzero:'-'}</td>
	</tr>
	{/foreach}
{else}
	{foreach from=$data key=year item=d}
		{foreach from=$d key=week item=w}
		<tr class={cycle values="c0,c1"}>
			<th>{$week}</th>
			<th>{$wk.$year.$week}</th>
			{if !$smarty.request.branch_group}
			    {foreach from=$branches item=b}
				
					{if $config.sales_report_branches_exclude}
					{if in_array($b.code,$config.sales_report_branches_exclude)}
					{assign var=skip_this_branch value=1}
					{else}
					{assign var=skip_this_branch value=0}
					{/if}
					{/if}
				
			        {if !in_array($b.id,$branch_group.have_group) and $b.code ne 'HQ' and !$skip_this_branch}
						{if $b.code ne 'HQ'}<td align=right>{$w[$b.id]|number_format:2|ifzero:"-"}</td>{/if}
					{/if}
				{/foreach}
				{foreach from=$branch_group.header key=bg_id item=bg}
				    {assign var=bgid value=$bg_id+10000}
			        <td align=right>{$w[$bgid]|number_format:2|ifzero:"-"}</td>
			    {/foreach}
			{else}
			    {foreach from=$branch_group.items[$smarty.request.branch_group] key=bid item=b}
				
					{if $config.sales_report_branches_exclude}
					{if in_array($b.code,$config.sales_report_branches_exclude)}
					{assign var=skip_this_branch value=1}
					{else}
					{assign var=skip_this_branch value=0}
					{/if}
					{/if}
				
				   {if !$skip_this_branch}
					<td align=right>{$w[$bid]|number_format:2|ifzero:"-"}</td>
			       {/if} 
			    {/foreach}
			{/if}
			<td class=r>{$w.total|number_format:2|ifzero:'-'}</td>
			{if $wk.$year.$week >= $financial_end_date and $cross_year eq 0}
			    {assign var=cross_year value=1}
			    {assign var=ytd_amount value=0}
			{/if}
			{assign var=ytd_amount value=$ytd_amount+$w.total}
			<td class=r>{$ytd_amount|number_format:2|ifzero:'-'}</td>
		</tr>
		{/foreach}
	{/foreach}
{/if}
<tr>
<th colspan=2>Total</th>
{if !$smarty.request.branch_group}
	{foreach from=$branches item=b}
	
		{if $config.sales_report_branches_exclude}
		{if in_array($b.code,$config.sales_report_branches_exclude)}
		{assign var=skip_this_branch value=1}
		{else}
		{assign var=skip_this_branch value=0}
		{/if}
		{/if}
	
		{if !in_array($b.id,$branch_group.have_group) and $b.code ne 'HQ' and !$skip_this_branch}
		    <td align=right>{$totalbybranch[$b.id]|number_format:2|ifzero:"-"}</td>
		{/if}
	{/foreach}
	{foreach from=$branch_group.header key=bg_id item=bg}
		{assign var=bgid value=$bg_id+10000}
		<td align=right>{$totalbybranch[$bgid]|number_format:2|ifzero:"-"}</td>
	{/foreach}
{else}
	{foreach from=$branch_group.items[$smarty.request.branch_group] key=bid item=b}
	
		{if $config.sales_report_branches_exclude}
		{if in_array($b.code,$config.sales_report_branches_exclude)}
		{assign var=skip_this_branch value=1}
		{else}
		{assign var=skip_this_branch value=0}
		{/if}
		{/if}
	
		{if !$skip_this_branch}
		<td align=right>{$totalbybranch[$bid]|number_format:2|ifzero:"-"}</td>
		{/if}
	{/foreach}
{/if}
	<td class=r>{$totalbybranch.total|number_format:2|ifzero:'-'}</td>
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

