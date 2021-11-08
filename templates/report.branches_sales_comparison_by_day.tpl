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
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>


{if $err}
<div class="alert alert-danger">
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
					<b class="form-label">Department</b>
				<select class="form-control" name="department_id">
				<option value=0>-- All --</option>
				{foreach from=$departments item=dept}
				<option value={$dept.id} {if $smarty.request.department_id eq $dept.id}selected{/if}>{$dept.description}</option>
				{/foreach}
				</select>
				</div>
		
				
					{if $branch_group.header}
					<div class="col-md-3">
				<b class="form-label">Branch Group</b>
					<select class="form-control" name="branch_group">
					<option value="">-- All --</option>
					{foreach from=$branch_group.header item=r}
						<option value="{$r.id}" {if $smarty.request.branch_group eq $r.id}selected {/if}>{$r.code}</option>
					{/foreach}
					</select>
				</div>
					{/if}
				
		
				<div class="col-md-3">
					<b class="form-label">By</b>
				<select class="form-control" name="view_type">
				<option value="day" {if $smarty.request.view_type eq 'day'}selected{/if}>Day</option>
				<option value="week" {if $smarty.request.view_type eq 'week'}selected{/if}>Week</option>
				</select>
				</div>
		
				<div class="col-md-3">
					<input class="btn btn-primary mt-4" type=hidden name=submit value=1>
				<button class="btn btn-primary mt-4" name=show_report>{#SHOW_REPORT#}</button>
				{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
				<button class="btn btn-info mt-4" name=output_excel>{#OUTPUT_EXCEL#}</button>
				{/if}
				</div>
			</div>
			<br><div class="alert alert-primary mt-2" style="max-width: 300px;">
				Note: Report Maximum Shown 365 Days
			</div>
	</form>
	</div>
</div>
{/if}
{if !$data}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{$report_title}
<!--Financial Start Year : {$financial_start_date} &nbsp;&nbsp;&nbsp;&nbsp;
Date: {$smarty.request.date_from} to {$smarty.request.date_to} &nbsp;&nbsp;&nbsp;&nbsp;
Department: {$dept_desc} &nbsp;&nbsp;&nbsp;&nbsp;
Type: {$smarty.request.view_type|capitalize}
{if $smarty.request.branch_group}&nbsp;&nbsp;&nbsp;&nbsp;
Branch Group: {$branch_group.header[$smarty.request.branch_group].code}
{/if}-->
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table small_printing table mb-0 text-md-nowrap  table-hover" width=100%>
				<div class="thead bg-gray-100">
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
				</div>
				{assign var=$cross_year value=0}
				
				{if $smarty.request.view_type eq 'day'}
					{assign var=temp value=0}
					{foreach from=$data key=date item=d}
					{assign var=dayname value=$date|date_format:"%A"}
					<tbody class="fs-08">
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
					</tbody>
					{/foreach}
				{else}
					{foreach from=$data key=year item=d}
						{foreach from=$d key=week item=w}
					<div class="tbody fs-08">
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
					</div>
						{/foreach}
					{/foreach}
				{/if}
				<div class="tbody fs-08">
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
				</div>
				</table>
		</div>
	</div>
</div>
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

