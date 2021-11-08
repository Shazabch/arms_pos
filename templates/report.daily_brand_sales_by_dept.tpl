{*
5/14/2010 11:48:21 AM Andy
- Modified some words
- column width change to 15%

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

1/9/2014 3:08 PM Justin
- Enhanced to take off the "All" options for branch filter.
- Bug fixed on system did not auto tick the option for view type after submit to view data.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/7/2019 5:33 PM William
- Added four column "Gross sales", "Discount", "Cost" and "Gross Profit(GP)".

6/26/2019 9:18 AM William
- tpl file calculate gross amount change to use php calculate.

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
		<form method="post" class="form" name="f_a" onSubmit="return view_type_check();">
			
				<div class="row">
					<div class="col-md-3">
						<b class="form-label mt-2">Department</b>
					<select class="form-control" name="department_id">
					<option value=0>-- All --</option>
					{foreach from=$departments item=dept}
					<option value={$dept.id} {if $smarty.request.department_id eq $dept.id}selected{/if}>{$dept.description}</option>
					{/foreach}
					</select>
					</div>
	
					<div class="col-md-3">
						<b class="form-label mt-2">SKU Type</b>
					<select class="form-control" name="sku_type">
						<option value="">-- All --</option>
						{foreach from=$sku_type item=t}
						<option value="{$t.code}" {if $smarty.request.sku_type eq $t.code}selected {/if}>{$t.description}</option>
						{/foreach}
					</select>
					</div>
				
					<div class="col-md-3">
						<b class="form-label mt-2">Date</b> 
					<div class="form-inline">
						<input class="form-control" size=23 type=text name=date_from value="{$smarty.request.date_from}{$form.from}" id="date_from">
					&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
					</div>
					</div>
					
					
					<div class="col-md-3">
						<b class="form-label mt-2">To</b> 
					<div class="form-inline">
						<input class="form-control" size=23 type=text name=date_to value="{$smarty.request.date_to}{$form.to}" id="date_to">
					&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
					</div>
					</div>
				
				<div class="col-md-3">
					{if $BRANCH_CODE eq 'HQ'}
					<b class="form-label mt-2">Branch</b>
					<select class="form-control" name="branch_id">
						<!--option value="">-- All --</option-->
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
				{/if}
				</div>
					<div class="col-md-3">
						<b class="form-label mt-2">View By</b>
					<input type="radio" name="view_type" value="day" {if !$smarty.request.view_type or $smarty.request.view_type eq 'day'}checked {/if} /> Daily
					<input type="radio" name="view_type" value="month" {if $smarty.request.view_type eq 'month'}checked{/if} /> Monthly
					
					</div>
					<div class="col-md-3">
						<div class="form-inline form-label mt-2">
							<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
						</div>
					</div>
				</div>
			
			<div class="alert alert-primary mt-2" style="max-width: 300px;">
				* Daily View maximum 30 days<br>
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
{foreach from=$brand item=brand_type key=b name=brand_loop}
	{assign var=loop_count value=$smarty.foreach.brand_loop.iteration}
	{if $loop_count%$record_chop==0}
		</table>
	{/if}
	{if $loop_count%$record_chop==0 || $loop_count==1}
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">
					{$report_title}Page: {$loop_count/$record_chop+1|number_format:0}
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
						{assign var=date_type value=$smarty.request.view_type}
						{if $smarty.request.view_type eq 'month'}
							{assign var=row_span value=1}
						{else}
							{assign var=row_span value=2}
						{/if}
						<th width="15%" rowspan={$row_span}>Brand</th>
						{foreach from=$date_label key=dl item=date}
							{if $date_set != $date_label.$dl.$date_type|date_format:"%B %Y"}
								{assign var=date_set value=$date_label.$dl.$date_type|date_format:"%B %Y"}
								{foreach from=$date_label key=dc item=date_count}
									{if $date_set == $date_label.$dc.$date_type|date_format:"%B %Y"}
										{assign var=col_count value=$col_count+1}
									{/if}
								{/foreach}
								<th colspan={$col_count}>{$date_label.$dl.$date_type|date_format:"%B %Y"}</th>
								{assign var=col_count value=0}
							{/if}
						{/foreach}
						<th width="10%" rowspan={$row_span}>Gross Sales</th>
						<th width="10%" rowspan={$row_span}>Discount</th>
						<th width="10%" rowspan={$row_span}>Total</th>
						{if $sessioninfo.privilege.SHOW_COST}
						<th width="10%" rowspan={$row_span}>Cost</th>
						{/if}
						{if $sessioninfo.privilege.SHOW_REPORT_GP}
						<th width="10%" rowspan={$row_span}>GP</th>
						{/if}
						<th width="10%" rowspan={$row_span}>Contributions %</th>
					</tr>
				</thead>
				{if $smarty.request.view_type eq 'day'}
					<thead class="bg-gray-100">
						<tr class="header">
							{foreach from=$date_label key=dl item=date}
								<th>{$date_label.$dl.$date_type|date_format:"%d"}</th>
							{/foreach}
						</tr>
					</thead>
				{/if}
			{/if}
				{assign var=row_total value=0}
				{assign var=discount_total value=0}
				{assign var=gross_profit value=0}
				{assign var=cost value=0}
					<tbody class="fs-08">
						<tr>
							<td>{if $brand_type}{$brand_type}{else}UNBRANDED{/if}</td>
							 {foreach from=$date_label key=dl item=date}
								 {assign var=date_set value=$date_label.$dl.$date_type}
								<td class="r">{$table.$brand_type.$date_set.amount|number_format:2|ifzero:'-'}</td>
								{assign var=row_total value=$row_total+$table.$brand_type.$date_set.amount}
								{assign var=discount_total value=$discount_total+$table.$brand_type.$date_set.discount}
								{assign var=gross_profit value=$gross_profit+$table.$brand_type.$date_set.amount-$table.$brand_type.$date_set.cost}
								{assign var=cost value=$cost+$table.$brand_type.$date_set.cost}
							{/foreach}
							{assign var=gross_sales value=$discount_total+$row_total}
							<td class="r">{$gross_sales|number_format:2}</td>
							<td class="r">{$discount_total|number_format:2}</td>
							<td class="r">{$row_total|number_format:2|ifzero:'-'}</td>
							{if $sessioninfo.privilege.SHOW_COST}
							<td class="r">{$cost|number_format:2}</td>
							{/if}
							{if $sessioninfo.privilege.SHOW_REPORT_GP}
							<td class="r">{$gross_profit|number_format:2}</td>
							{/if}
							<td class="r">{$row_total/$table.grand_total*100|number_format:2|ifzero:'-'}</td>
						</tr>
					</tbody>
		{/foreach}
				<tr class="header">
					{assign var=total_gp value=$total_selling-$total_cost}
					{if $total_selling}{assign var=total_gp_per value=$total_gp/$total_selling*100}{/if}
					{if $total_tran}
						{assign var=total_avg_tran value=$total_selling/$total_tran}
					{else}
						{assign var=total_avg_tran value=0}
					{/if}
						
					<th class="r">Total</th>
					{foreach from=$date_label key=dl item=date}
						{assign var=date_set value=$date_label.$dl.$date_type|ifzero:'-'}
						<th class="r">{$table.$date_set.col_total|number_format:2|ifzero:'-'}</th>
						{assign var=col_total value=$col_total+$table.$date_set.col_total|ifzero:'-'}
					{/foreach}
					{assign var=gross_profit_total value=$table.grand_total-$table.cost_total}
					<th class="r">{$table.gross_amt_total|number_format:2|ifzero:'-'}</th>
					<th class="r">{$table.discount_total|number_format:2|ifzero:'-'}</th>
					<th class="r">{$table.grand_total|number_format:2|ifzero:'-'}</th>
					{if $sessioninfo.privilege.SHOW_COST}
					<th class="r">{$table.cost_total|number_format:2|ifzero:'-'}</th>
					{/if}
					{if $sessioninfo.privilege.SHOW_REPORT_GP}
					<th class="r">{$gross_profit_total|number_format:2|ifzero:'-'}</th>
					{/if}
					<th class="r">{$col_total/$table.grand_total*100|number_format:2|ifzero:'-'}</th>
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
