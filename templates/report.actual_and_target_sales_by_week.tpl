{*
4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)
- report not to use branch group table anymore, change to individual branch

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
	        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
	        {/if}
	    {/foreach}
	    {if $branch_group.header}
	        <optgroup label="Branch Group">
				{foreach from=$branch_group.header item=r}
				    {capture assign=bgid}bg,{$r.id}{/capture}
					<option value="bg,{$r.id}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
				{/foreach}
			</optgroup>
		{/if}
	</select>
{/if}

<b>Department</b>
<select name="department_id">
<option value=0>-- All --</option>
{foreach from=$departments item=dept}
<option value={$dept.id} {if $smarty.request.department_id eq $dept.id}selected{/if}>{$dept.description}</option>
{/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;

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
{if !$data and !$predata and !$target}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<h2>
  {$report_title}
	<!--Date: {$smarty.request.date_from} to {$smarty.request.date_to} &nbsp;&nbsp;&nbsp;&nbsp;
	Department: {$dept_desc} &nbsp;&nbsp;&nbsp;&nbsp;
	Type: {$smarty.request.view_type|capitalize}-->
</h2>
{foreach from=$branch_list key=bid item=b}
<h1>
{if $bid < 10000}
	{$branches.$bid.code}
{else}
	{assign var=bgid value=$bid-10000}
	{$branch_group.header.$bgid.code}
{/if}
</h1>
<table class="report_table small_printing" width=100%>
<tr class=header>
<th>{if $bid < 10000}
	{$branches.$bid.code}
{else}
	{assign var=bgid value=$bid-10000}
	{$branch_group.header.$bgid.code}
{/if}</th>
<th rowspan=2>{if $smarty.request.view_type eq 'day'}{$smarty.request.view_type|ucfirst}{else}Sunday{/if}</th>
<th rowspan=2>Actual</th>
<th rowspan=2>Last Year</th>
<th rowspan=2>Var Amount</th>
<th rowspan=2>Var %</th>
<th rowspan=2>Target</th>
<th rowspan=2>Var Amount</th>
<th rowspan=2>Var %</th>
<th colspan=4>YTD</th>
</tr>
<tr class=header>
<th>{if $smarty.request.view_type eq 'day'}Date{else}{$smarty.request.view_type|ucfirst}{/if}</th>
<th>Actual</th>
<th>Target</th>
<th>Var Amount</th>
<th>Var %</th>
</tr>
{foreach name=i from=$y_axis item=dt}
<tr class={if $dt|date_format:"%A" eq 'Sunday'}csunday{else}{cycle values="c0,c1"}{/if}>
<th align=center>{if $smarty.request.view_type eq 'week'}{$smarty.foreach.i.iteration}{else}{$dt}{/if}</th>
<th align=center>{if $smarty.request.view_type eq 'week'}{assign var=datetime value=$dt|strtotime}{$datetime-86400|date_format:"%Y-%m-%d"}{else}{$dt|date_format:"%A"}{/if}</th>
<td align=right>{$data.$dt.$bid|number_format:2}</td>
<td align=right>{$predata.$dt.$bid|number_format:2}</td>
<td align=right>{$actual_var.$dt.$bid|number_format:2}</td>
<td align=right>{$actual_var_p.$dt.$bid|number_format:2}</td>
<td align=right>{$target.$dt.$bid|number_format:2}</td>
<td align=right>{$target_var.$dt.$bid|number_format:2}</td>
<td align=right>{$target_var_p.$dt.$bid|number_format:2}</td>
<td align=right>{$ytd_total.$dt.$bid|number_format:2}</td>
<td align=right>{$ytd_target.$dt.$bid|number_format:2}</td>
<td align=right>{$ytd_var.$dt.$bid|number_format:2}</td>
<td align=right>{$ytd_var_p.$dt.$bid|number_format:2}</td>
</tr>
{/foreach}
<tr>
<th align=center colspan=2>Total</th>
<td align=right>{$totalbybranch.$bid|number_format:2}</td>
<td align=right>{$pretotalbybranch.$bid|number_format:2}</td>
<td align=right>{$total_actual.$bid|number_format:2}</td>
{if $pretotalbybranch.$bid > 0}{assign var=total_actual_p value=$total_actual.$bid/$pretotalbybranch.$bid*100}{/if}
<td align=right>{$total_actual_p|number_format:2} %</td>
<td align=right>{$totaltargetbybranch.$bid|number_format:2}</td>
<td align=right>{$total_target.$bid|number_format:2}</td>
{if $totaltargetbybranch.$bid > 0}{assign var=total_target_p value=$total_target.$bid/$totaltargetbybranch.$bid*100}{/if}
<td align=right>{$total_target_p|number_format:2} %</td>
<td colspan=4></td>
</tr>
</table>
<br />
{/foreach}

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

