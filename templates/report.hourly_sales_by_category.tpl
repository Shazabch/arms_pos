{*
4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

11/27/2015 9:17 AM Qiu Ying
- Make it same as select Branch filter from "Sales report>Daily Category Sales Report" 

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
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
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
<input type=hidden name=report_title value="{$report_title}">
<b>Date</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;

{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> <select name="branch_id">
	    <option value="">-- All --</option>
	    {foreach from=$branches item=b}
		
			{if $config.sales_report_branches_exclude}
			{if in_array($b.code,$config.sales_report_branches_exclude)}
			{assign var=skip_this_branch value=1}
			{else}
			{assign var=skip_this_branch value=0}
			{/if}
			{/if}
		
	        {if !$branch_group.have_group[$b.id] and !$skip_this_branch}
	        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code} - {$b.description}</option>
	        {/if}
	    {/foreach}
	    {if $branch_group.header}
	        <optgroup label="Branch Group">
				{foreach from=$branch_group.header key=bgid item=bg}
		    	    <option class="bg" value="{$bgid*-1}" {if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
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
	</select>
{/if}

&nbsp;&nbsp;&nbsp;&nbsp;
<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} />Exclude inactive SKU</label>

&nbsp;&nbsp;&nbsp;&nbsp;
<input type=hidden name=submit value=1>
<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name=output_excel>{#OUTPUT_EXCEL#}</button>
{/if}
<br>Note: Report Maximum Show 1 Day
</form>
{/if}
{if !$data}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<h2>
{$report_title}
<!--Date: {$smarty.request.date_from} &nbsp;&nbsp;&nbsp;&nbsp;
Branch: {$branch_code}-->
</h2>
{capture assign=hrcount}{count var=$hour}{/capture}
<table class="report_table small_printing" width=100%>
	<tr class=header>
	<th>Category</th>
	{foreach from=$hour key=h item=hrs}
		<th nowrap>{$hrs}</th>
	{/foreach}
	<th>Total</th>
	<th>AVG Hour Amount</th>
	</tr>
	{foreach from=$data key=kp1 item=p1}
		{foreach from=$p1 key=kp2 item=p2}
		<tr>
			<!--<th>{$cat.$kp2}</th>-->
			<th nowrap align="left">{$kp2}</th>
			{foreach from=$hour key=h item=hrs}
				<td align=right>{$p2.$h|number_format:2|ifzero:"-"}</td>
			{/foreach}
			<td align=right>{$total_p2.$kp2|number_format:2|ifzero:"-"}</td>
		  
			<td align=right>{$total_p2.$kp2/$hrcount|number_format:2|ifzero:"-"}</td>
		</tr>
		{/foreach}
		<tr bgcolor="#ffffcc">
			<!--<th>{$cat.$kp1}</th>-->
			<td nowrap align="left"><img src="/ui/pixel.gif" width="20" align="absmiddle" />{$kp1}</td>
			{foreach from=$hour key=h item=hrs}
				<td align=right>{$p1_data.$kp1.$h|number_format:2|ifzero:"-"}</td>
			{/foreach}	
			<td align=right>{$total_p1.$kp1|number_format:2|ifzero:"-"}</td>	
			<td align=right>{$total_p1.$kp1/$hrcount|number_format:2|ifzero:"-"}</td>
		</tr>
	{/foreach}
	<tr>
		<th>Total</th>
		{foreach from=$hour key=h item=hrs}
			<td align=right>{$total_by_hour.$h|number_format:2|ifzero:"-"}</td>
		{/foreach}
		<td align=right>{$total_data|number_format:2|ifzero:"-"}</td>
		<td align=right>{$total_data/$hrcount|number_format:2|ifzero:"-"}</td>
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
</script>
{/literal}
{/if}
{include file=footer.tpl}

