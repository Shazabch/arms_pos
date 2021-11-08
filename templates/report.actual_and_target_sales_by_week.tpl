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
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if $err}
<div class="alert alert-danger mx-3 rounded">
	The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
</div>
{/if}
{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form method=post class=form>
			<div class="row">
				<div class="col-md-3">
					<input type=hidden name=report_title value="{$report_title}">
			<b class="form-label">From</b> 
			<div class="form-inline">
				<input class="form-control" size=23 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</div>
				</div>
			
			<div class="col-md-3">
				<b class="form-label">To</b> 
			<div class="form-inline">
				<input class="form-control" size=23 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
			
			<div class="col-md-3">
				{if $BRANCH_CODE eq 'HQ'}
			<b class="form-label">Branch</b> <select class="form-control" name="branch_id">
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
			</div>
			
			<div class="col-md-3">
				<b class="form-label">Department</b>
			<select class="form-control" name="department_id">
			<option value=0>-- All --</option>
			{foreach from=$departments item=dept}
			<option value={$dept.id} {if $smarty.request.department_id eq $dept.id}selected{/if}>{$dept.description}</option>
			{/foreach}
			</select>
			</div>
			
			<div class="col-md-3">
				<b class="form-label">By</b>
			<select class="form-control" name="view_type">
			<option value="day" {if $smarty.request.view_type eq 'day'}selected{/if}>Day</option>
			<option value="week" {if $smarty.request.view_type eq 'week'}selected{/if}>Week</option>
			</select>
			</div>
			
			<div class="col-md-3">
				<input type=hidden class="btn btn-primary mt-4" name=submit value=1>
			<button class="btn btn-primary mt-4" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info mt-4" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			</div>
			</div>
			<div class="alert alert-primary mt-2" style="max-width: 300px;">
				Note: Report Maximum Shown 365 Days
			</div>
			</form>
	</div>
</div>
{/if}
{if !$data and !$predata and !$target}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{$report_title}
				<!--Date: {$smarty.request.date_from} to {$smarty.request.date_to} &nbsp;&nbsp;&nbsp;&nbsp;
				Department: {$dept_desc} &nbsp;&nbsp;&nbsp;&nbsp;
				Type: {$smarty.request.view_type|capitalize}-->
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{foreach from=$branch_list key=bid item=b}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h5 class="content-title mb-0 my-auto ml-4 text-primary">
				{if $bid < 10000}
	{$branches.$bid.code}
{else}
	{assign var=bgid value=$bid-10000}
	{$branch_group.header.$bgid.code}
{/if}
			</h5><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table small_printing table mb-0 text-md-nowrap  table-hover" width=100%>
				<div class="thead bg-gray-100">
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
				</div>
				<div class="thead bg-gray-100">
					<tr class=header>
						<th>{if $smarty.request.view_type eq 'day'}Date{else}{$smarty.request.view_type|ucfirst}{/if}</th>
						<th>Actual</th>
						<th>Target</th>
						<th>Var Amount</th>
						<th>Var %</th>
						</tr>
				</div>
				{foreach name=i from=$y_axis item=dt}
				<div class="tbody fs-08">
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
				</div>
				{/foreach}
				<div class="tbody fs-08">
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
				</div>
				</table>
				
		</div>
	</div>
</div>
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

