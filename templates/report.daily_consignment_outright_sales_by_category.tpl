{*
11/12/2010 4:11:46 PM Alex
- add check_form function

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)
- report not to use branch group table anymore, change to individual branch

11/3/2017 2:27 PM Justin
- Bug fixed on wrong end day (Sunday) of the week.

06/30/2020 10:57 AM Sheila
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
/*.sun {background:#999; }*/
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
		<form name=report_form method=post class=form onsubmit="return check_form(this);">
			<input type=hidden name=report_title value="{$report_title}">
			<div class="row">
				<div class="col-md-4">
					{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label">Branch</b> <select class="form-control" name="branch_id">
						<option value="">-- Please Select --</option>
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
			</div>
		
			
			<p>{include file="category_autocomplete.tpl" all=true}</p>
			
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			<div class="alert alert-primary mt-2" style="max-width: 300px;">
				*Data Shown Between 1 month only
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

<!--Branch: {$branch_code} &nbsp;&nbsp;&nbsp;&nbsp;
Date: {$smarty.request.date_from} to {$smarty.request.date_to} &nbsp;&nbsp;&nbsp;&nbsp;
Category: {$cat_desc}-->
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table small_printing table mb-0 text-md-nowrap  table-hover" width=100% cellpadding=0 cellspacing=0>
				{foreach from=$total_sku_type key=dept_id item=d}
					{cycle values="c1,c2" assign=r1}
					{assign var=dd value=$start_day}
					{assign var=noofday value=11}
					{section name=n loop=3}
						{capture name=cs assign=colspan}{count var=$d}{/capture}
						{math assign=cols equation=(x+2)*3 x=$colspan}
						<tr>
						{if $smarty.section.n.first}<td class={$r1} rowspan={$cols}>{$cat.$dept_id}</td>{/if}
						<td class=c3>SKU Type</td>
						{if $smarty.section.n.first}
							{assign var=day_start_no value=$offset_day}
						{/if}
						{section name=m loop=$noofday}
							{if $dd > $max_month}
							{assign var=day value=$dd-$max_month}
							{else}
							{assign var=day value=$dd}
							{/if}
							{if ($dd < $start_day+31)}<td class=c3 align=center>{$day}{if $day_start_no % 7 == 0}{assign var=day_start_no value=0} (Sun){/if}</td>{/if}
							<!--{$day_start_no++}-->
							<!--{$dd++}-->
						{/section}
						{if $smarty.section.n.last}<td class=c3 colspan=2 align=center>Total</td>{/if}
						</tr>
						<tr class={$r1}>
						{math assign=dd equation="(1+(y-1)*x)+($start_day-1)" y=$smarty.section.n.iteration x=$noofday}
						<td class=c4>Total</td>
						{section name=m loop=$noofday}
							{if $dd > $max_month}
							{assign var=day value=$dd-$max_month}
							{else}
							{assign var=day value=$dd}
							{/if}
							{if ($dd < $start_day+31)}
							<td class=c4 align=right>
							{if $dd <= $s_day+$max_month}
							{$data.$dept_id.$day|number_format:2|ifzero:"-"}
							{else}
							-
							{/if}
							</td>
							{/if}
							<!--{$dd++}-->
						{/section}
						{if $smarty.section.n.last}<td class=c4 align=right colspan=2>{$total_data.$dept_id|number_format:2|ifzero:"-"}</td>{/if}
						</tr>
						{foreach from=$d key=sku_type item=st}
						{*{cycle values="c5,c6" assign=r2}*}
						{assign var=r2 value='c5'}
						<tr class={$r1}>
						{math assign=dd equation="(1+(y-1)*x)+($start_day-1)" y=$smarty.section.n.iteration x=$noofday}
						<td class={$r2}>{$sku_type}</td>
						{section name=m loop=$noofday}
							{if $dd > $max_month}
							{assign var=day value=$dd-$max_month}
							{else}
							{assign var=day value=$dd}
							{/if}
							{if ($dd < $start_day+31)}
								<td class={$r2} align=right>
								{if $dd <= $s_day+$max_month}
								{$d.$sku_type.$day|number_format:2|ifzero:"-"}
								{else}
								-
								{/if}
								</td>
							{/if}
							<!--{$dd++}-->
						{/section}
						{if $smarty.section.n.last}<td class={$r2} align=right colspan=2>{$total_amount_sku_type.$dept_id.$sku_type|number_format:2|ifzero:"-"}</td>{/if}
						</tr>
						{/foreach}
					{/section}
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

