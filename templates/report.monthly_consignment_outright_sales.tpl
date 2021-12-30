{*
11/12/2010 4:11:07 PM Alex
- add check_form function

2/16/2011 3:14:24 PM ALex
- fix bugs on unable to display data if 2011-01-01 fall in 52nd week

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)
- report not to use branch group table anymore, change to individual branch

06/30/2020 11:17 AM Sheila
- Updated button css.
*}

{include file=header.tpl}
{if !$no_header_footer}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
{literal}
<style>

.c1 { background:#9ff; }
.c2 { background:#f9f; }
.c3 { background:#ff9; }
.c4 { background:#f99; }
.c5 { background:#9f9; }
.c6 { background:#99f; }
.sun {color:#f00; }
td.day2 { font-size:20px; }
td.tline { border-bottom:none !important;}
td.tline2 { border-bottom:none !important; border-top:1px solid #000 !important;}

</style>

<script>
function check_form(obj){
	if (!$('category_id').value && !$('all_category').checked){
	    alert("Invalid Category");
		return false;
	}
}

</script>

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
		<form name=f method=post class=form onsubmit="return check_form(this);">
			<input type=hidden name=report_title value="{$report_title}">
			<div class="row">
			
			<div class="col-md-4">
				<b class="form-label">From</b>
			<div class="form-inline">
				<input class="form-control" size=23 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
			<div class="col-md-4">
				<b class="form-label">To</b> 
			<div class="form-inline">
				<input class="form-control" size=23 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
			
			<div class="col-md-4">
				{if $BRANCH_CODE eq 'HQ'}
			<b class="form-label">Branch</b> 
			<select class="form-control" name="branch_id">
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
			{else}
			<br>
			{/if}
			</div>
			</div>
			
			<p>
			{include file="category_autocomplete.tpl" all=true}
			</p>
			
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			<div class="alert alert-primary mt-2" style="max-width: 240px;">
				<b>Note: </b>Maximum Show 31 Days
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
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table small_printing table mb-0 text-md-nowrap  table-hover" width=100% >
				{foreach name=w from=$total_weeks item=start_week}
					{if $smarty.foreach.w.first}
						<thead class="bg-gray-100">
							<tr class=header>
								<th>SKU Type</th>
								{assign var=dd value=1}
								{section name=d loop=7}
								<th align=center>{$str_week.$dd}</th>
								<!--{$dd++}-->
								{/section}
								</tr>
						</thead>
					{/if}
					<tbody class="fs-08">

						<tr>
							<td class="tline2">&nbsp;</td>
							{assign var=dd value=1}
							{section name=d loop=7}
							<td class="tline2 day2 {if $dd == 7}sun{/if}">{$c_label.$start_week.$dd}</td>
							<!--{$dd++}-->
							{/section}
						</tr>
						<tr>
							<td class="tline">Total</td>
							{assign var=dd value=1}
							{section name=d loop=7}
							<td align=right class="tline {if $dd == 7}sun{/if}">{$data.$start_week.$dd|number_format:2|ifzero}</td>
							<!--{$dd++}-->
							{/section}
						</tr>
						{foreach from=$data_by_sku_type key=sku_type item=r}
							<tr class="{cycle values="c3,c4"}">
							<td class="tline">{$sku_type}</td>
							{assign var=dd value=1}
							{section name=d loop=7}
							<td align=right class="tline {if $dd == 7}sun{/if}">{$data_by_sku_type.$sku_type.$start_week.$dd|number_format:2|ifzero}</td>
							<!--{$dd++}-->
							{/section}
							</tr>
					</tbody>
					{/foreach}
				<!--{$start_week++}-->
				{/foreach}
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

