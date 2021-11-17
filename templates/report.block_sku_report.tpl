{*
4/2/2014 11:02 AM Fithri
- add option to select all SKU / branches for some reports, require config 'allow_all_sku_branch_for_selected_reports' to be turned on

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

7/14/2015 4:27 PM Joo Chia
- add option to filter by Vendor

10/17/2016 11:14 AM Andy
- Fix rowspan bug when export report

10/23/2018 3:08 PM Justin
- Enhanced to load SKU Type list from database instead of hardcoded it.

12/6/2018 5:01 PM Justin
- Bug fixed for the value of SKU Type is assigned wrongly.

06/30/2020 11:47 AM Sheila
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
<script>
function toggle_sub(tbody_id, el)
{
	if ($(tbody_id).style.display=='none')
	{
	    el.src='/ui/collapse.gif';
	    $(tbody_id).style.display='';
	}
	else
	{
	    el.src='/ui/expand.gif';
	    $(tbody_id).style.display='none';
	}
}

</script>
<style>
.c1 { background:#fff; }
.c2 { background:#eee; }
.c3 { background:#efefef; }
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
				<div class="col-md-4">
					<b class="form-label">Date From</b> 
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
				<div class="form-inline mt-4">
					<select class="form-control" name="monthly_or_total">
						<option value="monthly" {if $smarty.request.monthly_or_total eq 'monthly'}selected {/if}>Monthly</option>
						<option value="total" {if $smarty.request.monthly_or_total eq 'total'}selected {/if}>Total</option>
					</select>
					<select class="form-control" name="min_or_max">
						<option value="min" {if $smarty.request.min_or_max eq 'min'}selected {/if}>Min Quantity</option>
						<option value="max" {if $smarty.request.min_or_max eq 'max'}selected {/if}>Max Quantity</option>
					</select>
					<input class="form-control" type="text" name="quantity" value="{$smarty.request.quantity|default:'0'}" size="2">
				</div>
			</div>
			
			</div>
			<div class="row mt-2">
				<div class="col-md-4">
					{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label">Branch</b>
				<select class="form-control" name="branch_id">
					 <option value="">-- All --</option>
					 {foreach from=$branches item=b}
						<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
					{/foreach}
				</select>
				{/if}
				</div>
				<div class="col-md-4">
				<div class="form-label mt-4">
					<input type="radio" name="block" value="1" {if $smarty.request.block ne 0}checked {/if} /> <b>&nbsp;Block</b>
					<input type="radio" name="block" value="0" {if $smarty.request.block eq 0}checked {/if} /> <b>&nbsp;Unblock</b>
				</div>
				</div>
			</div>
			<p>
			<div class="row">
				<div class="col-md-4">
					<b class="form-label">Department</b> {*dropdown name=dept_id values=$departments selected=$smarty.request.dept_id key=id value=description*}
				<select class="form-control" name="dept_id">
					{if $config.allow_all_sku_branch_for_selected_reports}
					<option value="0">-- All --</option>
					{/if}
				{foreach from=$departments item=dpi}
					<option value="{$dpi.id}" {if $smarty.request.dept_id eq $dpi.id}selected {/if}>{$dpi.description}</option>
				{/foreach}
				</select>
				</div>
				
				<div class="col-md-4">
					<b class="form-label">SKU Type</b>
				<select class="form-control" name="sku_type">
					<option value="">-- All --</option>
					{foreach from=$sku_type key=st_code item=st}
						<option value="{$st.code}" {if $smarty.request.sku_type eq $st.code}selected{/if}>{$st.description}</option>
					{/foreach}
				</select>
				</div>
				
				<div class="col-md-4">
					<div class="form-label form-inline mt-4">
						<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
					</div>
				</div>
			</div>
			</p>
			
			<p>
			<div class="row">
				<div class="col-md-4">
					<b class="form-label">Vendor</b>
			<select class="form-control" name="v_id">
				<option value="">-- All --</option>
			{foreach from=$vendor item=vdr}
				<option value="{$vdr.id}" {if $smarty.request.v_id eq $vdr.id}selected{/if}>{$vdr.description}</option>
			{/foreach}
			</select>
				</div>
			</div>
			
			</p>
			
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			
			<div class="alert alert-primary rounded mt-2" style="max-width: 300px;">
				<b>Note:</b> Report Maximum show 12 months
			</div>
			</form>
	</div>
</div>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				Branch: {$branch_name}
Department: {$dept_name}
{$smarty.request.min_or_max|capitalize} Quantity: {$smarty.request.quantity|qty_nf}
Date: {$date_length}
Type: {if $smarty.request.block eq 0}Unblock{else}Block{/if}
SKU Type: {$smarty.request.sku_type|default:'All'}
Vendor: {$vendor_name}
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
						<th>Category</th>
						<th>Supplier Name</th>
						<th>ARMS Code</th>
						<th>MCode</th>
						<th>Art No.</th>
						<th>Description</th>
						{foreach from=$label key=lbl item=lbl2}
							<th>{$lbl2}</th>
						{/foreach}
						<th>Total</th>
					</tr>
				</thead>
				
				{assign var=max_export_row value=255}
				
				{foreach from=$table key=cat_id item=s}
				{cycle values="c2,c1" assign=row_class name=row1}
				
				{assign var=export_row_counter value=0}
				{assign var=remain_export_row value=$category.$cat_id.item_count}
				
				<tr class="{$row_class}">
					{if !$is_output_excel}
						<th rowspan="{$category.$cat_id.item_count}" bgcolor="white">{$category.$cat_id.cname}</th>
					{/if}
					{assign var=got_tr value=1}
					{foreach from=$s item=r}		
						{assign var=sku_key value=$r.code}
						{if $got_tr<=0}{cycle values="c2,c1" assign=row_class name=row1}</tr>
					<tbody class="fs-08">
						<tr class="{$row_class}">{/if}
							{if $is_output_excel && $export_row_counter eq 0}
								{if $remain_export_row lte $max_export_row}
									{assign var=export_rowspan value=$remain_export_row}
								{else}
									{assign var=export_rowspan value=$max_export_row}
								{/if}
								{assign var=remain_export_row value=$remain_export_row-$export_rowspan}
								<th rowspan="{$export_rowspan}" bgcolor="white">{$category.$cat_id.cname}</th>
							{/if}
						
							{assign var=vid value=$sku.$sku_key.vendor_id}
							<td>{$vendor.$vid.description} <span class="small" style="color:blue;">({$sku.$sku_key.vendor_type})</span></td>
							<td>{$sku.$sku_key.code}</td>
							<td>{$sku.$sku_key.mcode|default:'-'}</td>
							<td>{$sku.$sku_key.artno|default:'-'}</td>
							<td>{$sku.$sku_key.description}</td>
							{foreach from=$label key=lbl item=lbl2}
								<td class="r">{$r.qty.$lbl|qty_nf|ifzero:'-'}</td>
							{/foreach}
							<td class="r">{$r.qty.total|qty_nf|ifzero:'-'}</td>
						{assign var=got_tr value=0}
						{if $is_output_excel}
							{assign var=export_row_counter value=$export_row_counter+1}
							{if $export_row_counter >= $max_export_row}
								{assign var=export_row_counter value=0}
							{/if}
						{/if}
					{/foreach}
				</tr>
					</tbody>
				<tr class="header">
					<th colspan="6" class="r">Total</th>
					{foreach from=$label key=lbl item=lbl2}
						<th class="r">{$total.$cat_id.qty.$lbl|qty_nf|ifzero:'-'}</th>
					{/foreach}
					<th class="r">{$total.$cat_id.qty.total|qty_nf|ifzero:'-'}</th>
				</tr>
				{/foreach}
				<tr class="header">
					<th colspan="6" class="r">Total</th>
					{foreach from=$label key=lbl item=lbl2}
						<th class="r">{$total.total.qty.$lbl|qty_nf|ifzero:'-'}</th>
					{/foreach}
					<th class="r">{$total.total.qty.total|qty_nf|ifzero:'-'}</th>
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

