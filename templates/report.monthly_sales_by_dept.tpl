{*
4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)
- report not to use branch group table anymore, change to individual branch

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

06/30/2020 11:17 AM Sheila
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
.positive{
	font-weight: bold;
	color:green;
}
.negative{
    font-weight: bold;
	color:red;
}
.weekend{
	color:red;
}
</style>
{/literal}

<script>
{literal}
function view_type_check(){
	if($('date_from').value > $('date_to').value){
		alert('Date Start cannot be late than Date End');
		return false;
	}
}

function group_split_check(){
	split_bg = document.f_a['branch_id'].value.split( ",");

	if(!document.f_a['branch_id'].value || split_bg[0] == "bg"){
		$('branch_split').style.display = "";
	}else{
		$('branch_split').style.display = "none";
		document.f_a['split_bg'].checked = false;
	}
}

{/literal}
</script>
{/if}

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>


{if $err}
<div class="alert alert-primary rounded mx-3">
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
		<form method="post" class="form" name="f_a" onSubmit="return view_type_check();">
			<p>
				<div class="row">
					{if $BRANCH_CODE eq 'HQ'}
					<div class="col">
						<b class="form-label">Branch</b>
					<select class="form-control" name="branch_id" {if $branch_group.header}onchange="group_split_check();"{/if}>
						<option value="">-- All --</option>
						{foreach from=$branches item=b}
						
							{if $config.sales_report_branches_exclude}
							{if in_array($b.code,$config.sales_report_branches_exclude)}
							{assign var=skip_this_branch value=1}
							{else}
							{assign var=skip_this_branch value=0}
							{/if}
							{/if}
						
							{if !$skip_this_branch}
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
					</div>
				<div class="col">
					<span id="branch_split" {if !$branch_group.header}style="display:'none';"{/if}>
						<div class="form-inline form-label mt-4">
							<input type="checkbox" id="split_bg" name="split_bg" {if $smarty.request.split_bg}checked{/if}> <b>&nbsp;Split Branch Group</b>
						</div>
					</span>
				</div>
				{/if}
				</div>
			</p>
			<p>
				<div class="row">
					<div class="col">
						<b class="form-label">Department</b>
				<select class="form-control" name="department_id">
				<option value=0>-- All --</option>
				{foreach from=$departments item=dept}
				<option value={$dept.id} {if $smarty.request.department_id eq $dept.id}selected{/if}>{$dept.description}</option>
				{/foreach}
				</select>
					</div>

				<div class="col">
					<b class="form-label">SKU Type</b>
				<select class="form-control" name="sku_type">
					<option value="">-- All --</option>
					{foreach from=$sku_type item=t}
					<option value="{$t.code}" {if $smarty.request.sku_type eq $t.code}selected {/if}>{$t.description}</option>
					{/foreach}
				</select>
				</div>
				</div>
			</p>
			<p>
				<div class="row">
					<div class="col">
						<b class="form-label">Date</b> 
				<div class="form-inline">
					<input class="form-control" size=23 type=text name=date_from value="{$smarty.request.date_from}{$form.from}" id="date_from">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
				</div>
			
					</div>
				
				<div class="col">
					<b class="form-label">To</b> 
				<div class="form-inline">
					<input class="form-control" size=23 type=text name=date_to value="{$smarty.request.date_to}{$form.to}" id="date_to">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
				</div>
				</div>
				
				
				<div class="col">
					<div class="form-label form-inline mt-4">
						<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
					</div>
					</div>
				</div>
			</p>
			<p>
			<div class="alert alert-primary rounded mt-2" style="max-width: 300px;">
				* Monthly View maximum 365 days
			</div>
			</p>
			<p>
			<input type="hidden" name="submit" value="1" />
			<button class="btn btn-primary" name="show_report">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name="output_excel">{#OUTPUT_EXCEL#}</button>
			{/if}
			</p>
			</form>
	</div>
</div>
{/if}

{if !$table}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
	{foreach from=$dept_set item=skutype key=d name=dept_loop}
		{foreach from=$skutype item=st name=st_loop}
			{assign var=loop_count value=$loop_count+1}
			{if $loop_count%$record_chop==0}
				</table>
			{/if}
			{if $loop_count%$record_chop==0 || $loop_count==1}
			<div class="breadcrumb-header justify-content-between">
				<div class="my-auto">
					<div class="d-flex">
						<h4 class="content-title mb-0 my-auto ml-4 text-primary">
							{$report_title}&nbsp;&nbsp;&nbsp;&nbsp;Page: {$loop_count/$record_chop+1|number_format:0}
						</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
					</div>
				</div>
			</div>
			
			<div class="card mx-3">
				<div class="card-body">
					<div class="table-responsive">
						<table class="report_table small_printing table mb-0 text-md-nowrap  table-hover" width="100%">
							<thead class="bg-gray-100">
								<tr class="header">
									<th width="15%" rowspan=3>Department</th>
									<th width="10%" rowspan=3>SKU Type</th>
									{foreach from=$column_label item=branch key=dl}
										<th colspan="{$column_count*2}">{$dl|date_format:"%B %Y"}</th>
									{/foreach}
									<th width="20%" colspan=3 rowspan=2>Total</th>
								</tr>
								<tr class="header">
									{foreach from=$column_label item=branch key=dl}
										{foreach from=$branch item=branch key=bl}
											<th colspan="2">{$branch}</th>
										{/foreach}
									{/foreach}
								</tr>
								<tr class="header">
									{foreach from=$column_label item=branch key=dl}
										{foreach from=$branch item=branch key=bl}
										<th>Sales</th>
										<th>Discount Amount</th>
										{/foreach}
									{/foreach}
									<th>Sales</th>
									<th>Discount Amount</th>
									<th>Contributions %</th>
								</tr>
							</thead>
						{/if}
							<tbody class="fs-08">
								<tr>
									{if $next_dept != $table.$d.$st.description}
										<td rowspan={$dept_rs_count.$d}>{$table.$d.$st.description}</td>
									{/if}
									{assign var=next_dept value=$table.$d.$st.description}
									<td>{$st}</td>
									{foreach from=$column_label item=branch key=dl}
										{foreach from=$branch item=branch key=bl}
										<td align=right>{$table.$d.$st.$dl.$bl.amount|number_format:2|ifzero:'-'}</td>
										<td align=right>{$table.$d.$st.$dl.$bl.disc_amount|number_format:2|ifzero:'-'}</td>
										{/foreach}
									{/foreach}
									<td align=right>{$row_total.$d.$st.amount|number_format:2|ifzero:'-'}</td>
									<td align=right>{$row_total.$d.$st.disc_amount|number_format:2|ifzero:'-'}</td>
									<td align=right>{$row_total.$d.$st.amount/$grand_total.amount*100|number_format:2|ifzero:'-'}</td>
									{assign var=total_perc value=$total_perc+$row_total.$d.$st.amount/$grand_total.amount*100}
								</tr>
							</tbody>
					{/foreach}
				{/foreach}	
					<tr class="header">
						<th class='r' colspan=2>Total</th>
						{foreach from=$column_label item=branch key=dl}
							{foreach from=$branch item=branch key=bl}
							<th align=right>{$col_total.$dl.$bl.amount|number_format:2|ifzero:'-'}</th>
							<th align=right>{$col_total.$dl.$bl.disc_amount|number_format:2|ifzero:'-'}</th>
							{/foreach}
						{/foreach}
						<th align=right>{$grand_total.amount|number_format:2|ifzero:'-'}</td>
						<th align=right>{$grand_total.disc_amount|number_format:2|ifzero:'-'}</td>
						<th align=right>{$total_perc|number_format:2|ifzero:'-'}</td>
					</tr>
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
