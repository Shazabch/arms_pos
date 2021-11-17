{*
4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

4/29/2016 3:08 PM Andy
- Add vendor code and vendor description.

5/4/2017 2:10 PM Justin
- Enhanced to show all branches even it is under branch group.

5/9/2017 9:55 AM Justin
- Enhanced to show out the branches under the group instead of showing it from un-grouped area.

5/18/2017 5:56 PM Justin
- Bug fixed on mysql errors when choose branch group.
- Bug fixed on branch group is not bolded and list the same column as branch.

2017-09-15 11:09 AM Qiu Ying
- Bug fixed on mcode and artno join into same line when export to excel 

11/7/2018 11:33 AM Justin
- Enhanced the report to allow user to choose show data by Quantity, Sales, Cost, GP or GP(%).
- Enhanced to have new sorting list of Cost, GP and GP(%).

2/26/2019 5:48 PM Andy
- Enhanced the report to show item Old Code.
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

.quantity { background:#ff9; }
.amount { background:#aff; }
.cost { background:#faf; }
.gp { background:#ae1; }
.gp_perc { background:#1ae; }

option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}

</style>
{/literal}

{literal}
<script type="text/javascript">

function toggle_all_data_row(obj){
	$$('#rpt_form .data_row_list').each(function(ele, i){
		if(obj.checked == true) ele.checked = true;
		else ele.checked = false;
	});
}

function validate_data(){
	var got_tick_data_row = false;
	$$('#rpt_form .data_row_list').each(function(ele, i){
		if(ele.checked == true) got_tick_data_row = true;
	});
	
	if(got_tick_data_row == false){
		alert("You must choose at least one Data Row to show the report.");
		return false;
	}
	
	return true;
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
<ul class="err">
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
</div>
{/if}
{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form method="post" class="form" name="report_form" id="rpt_form" onSubmit="return validate_data();">
			<input type="hidden" name="report_title" value="{$report_title}">
			<div class="row">
				<div class="col">
					{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label">Branch</b> 
				<select class="form-control" name="branch_id">
						<option value="">-- All --</option>
						{foreach from=$branches item=b}
							{if !$branch_group.have_group[$b.id]}
							<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
							{/if}
						{/foreach}
						{if $branch_group.header}
							<!--optgroup label="Branch Group">
								{foreach from=$branch_group.header item=r}
									{capture assign=bgid}bg,{$r.id}{/capture}
									<option value="bg,{$r.id}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
								{/foreach}
							</optgroup-->
							
							<optgroup label='Branch Group'>
								{foreach from=$branch_group.header key=bgid item=bg}
									{capture assign=tmp_bgid}bg,{$bg.id}{/capture}
									<option value="bg,{$bg.id}" {if $smarty.request.branch_id eq $tmp_bgid}selected {/if}><b>{$bg.code}</b></option>
									{foreach from=$branch_group.items.$bgid item=r}
										{if $config.sales_report_branches_exclude}
											{if in_array($r.code,$config.sales_report_branches_exclude)}
												{assign var=skip_this_branch value=1}
											{else}
												{assign var=skip_this_branch value=0}
											{/if}
										{/if}
				
										{if !$skip_this_branch}
											<option value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>&emsp;&emsp;{$r.code} - {$r.description}</option>
										{/if}
									{/foreach}
								{/foreach}
							</optgroup>
						{/if}
					</select>
				{/if}
				</div>
				
				<div class="col">
					<b class="form-label">From</b> 
			<div class="form-inline">
				<input class="form-control" size="22" type="text" name="date_from" value="{$smarty.request.date_from}" id="date_from">
				&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</div>
				</div>
				
				<div class="col">
					<b class="form-label">To</b> 
			<div class="form-inline">
				<input class="form-control" size="22" type="text" name="date_to" value="{$smarty.request.date_to}" id="date_to">
				&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			</div>
				</div>
			</div>
			
			
			<p>
			{include file="category_autocomplete.tpl" all=true}
			</p>
			
			<div class="row">
				<div class="col">
					<b class="form-label">Minimum Transaction Count</b> 
				<input class="form-control" size="10" type="text" name="min_tran" value="{$smarty.request.min_tran}">
				</div>
	
				<div class="col">
					<b class="form-label">Minimum Amount</b> 
				<input class="form-control" size="10" type="text" name="min_amount" value="{$smarty.request.min_amount}">
				</div>
	
				<div class="col">
					<b class="form-label">From</b>
				<div class="form-inline">
					<select class="form-control" name="order_type">
						<option value="top" {if $smarty.request.order_type eq 'top'}selected{/if}>Top</option>
						<option value="bottom" {if $smarty.request.order_type eq 'bottom'}selected{/if}>Bottom</option>
						</select>
						&nbsp;&nbsp;<input class="form-control" size="5" type="text" name="filter_number" value="{$filter_number|default:10}"> 
				</div>
				</div>
	
				<div class="col">
					<b class="form-label"> By </b>
					<div class="form-inline">
						<select class="form-control" name="quantity_amount_type">
							{foreach from=$data_row_list key=data_type item=data_desc}
								<option value="{$data_type}" {if $smarty.request.quantity_amount_type eq $data_type}selected{/if}>{$data_desc}</option>
							{/foreach}
							</select>
							&nbsp;(Max 1000)
					</div>
				</div>
			</div>
			
			<div class="alert alert-primary rounded mt-2" style="max-width: 250px;">
				<b>Show Data Row by:</b>
			</div>
			
			<div class="form-label">
				<input type="checkbox" name="all_data_row" value="1" onclick="toggle_all_data_row(this);" /> <b>All</b>&nbsp;&nbsp;
			{foreach from=$data_row_list key=row_value item=data_desc}
			<input type="checkbox" name="data_row[{$row_value}]" class="data_row_list" value="1" {if $smarty.request.data_row.$row_value || !isset($smarty.request.data_row)}checked{/if} />&nbsp;{$data_desc}&nbsp;&nbsp;
			{/foreach}
			</div>
			
			<div class="form-label">
				<b>Display Item Code:</b> <input type="checkbox" name="display_item_code[link_code]" value="1" {if $smarty.request.display_item_code.link_code}checked {/if} /> {$config.link_code_name}
			</div>
		
			<input type="hidden" name="submit" value="1">
			<button class="btn btn-primary mt-2 mb-3" name="show_report">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info mt-2 mb-3" name="output_excel">{#OUTPUT_EXCEL#}</button>
			{/if}
			<div class="form-label form-inline">
				<input type="checkbox" name="group_sku" {if $smarty.request.group_sku}checked {/if}> <b>&nbsp;Group by SKU</b>
			
			<label>&nbsp;<input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
			</div>
			<div class="alert alert-primary rounded mt-2" style="max-width: 230px;">
				<b>Note:</b> Report Maximum Shown 1 Year
			</div>
			
			</form>
	</div>
</div>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}
				<!--Branch: {$branch_name}--></h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if $smarty.request.display_item_code.link_code}
	{assign var=show_old_code value=1}
{/if}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table" width=100%>
				<thead class="bg-gray-100">
					<tr class="header">
						<th {if !$smarty.request.group_sku}rowspan="2"{/if}></th>
						<th {if !$smarty.request.group_sku}rowspan="2"{/if}>{if $smarty.request.group_sku}SKU ID{else}ARMS Code{/if}</th>
						
						{if $show_old_code}
							<th {if !$smarty.request.group_sku}rowspan="2"{/if}>{$config.link_code_name}</th>
						{/if}
						
						{if !$smarty.request.group_sku}
							<th>MCode</th>
						{/if}
						<th {if !$smarty.request.group_sku}rowspan="2"{/if}>Vendor</th>
						<th {if !$smarty.request.group_sku}rowspan="2"{/if}>SKU Description</th>
						{if count($smarty.request.data_row) > 1}
							<th {if !$smarty.request.group_sku}rowspan="2"{/if}>Data</th>
						{/if}
						{foreach from=$label item=lbl}
							<th {if !$smarty.request.group_sku}rowspan="2"{/if}>{$lbl}</th>
						{/foreach}
						<th {if !$smarty.request.group_sku}rowspan="2"{/if}>Total</th>
					</tr>
				</thead>
				{if !$smarty.request.group_sku}
					<tr class="header">
						<th>Artno</th>
					</tr>
				{/if}
				
				{if count($smarty.request.data_row) > 1}
					{assign var=rowspan value=$artno_rowspan+$mcode_rowspan}
					{assign var=one_data_rowspan value=1}
					{assign var=ttl_rowspan value=$rowspan}
				{else}
					{if !$smarty.request.group_sku}
						{assign var=rowspan value=2}
						{assign var=one_data_rowspan value=2}
					{else}
						{assign var=rowspan value=1}
						{assign var=one_data_rowspan value=1}
					{/if}
					{assign var=ttl_rowspan value=1}
				{/if}
				
				{section loop=$table name=i max=$filter_number}
				<tbody class="fs-08">
					<tr>
						<td rowspan="{$rowspan}">{$smarty.section.i.iteration}</td>
						<td rowspan="{$rowspan}" class="c1">{if $smarty.request.group_sku}{$table[i].sku_id}{else}{$table[i].sku_item_code}{/if}</td>
						{if $show_old_code}
							<td rowspan="{$rowspan}" class="c1">{$table[i].link_code}</td>
						{/if}
						
						{if !$smarty.request.group_sku}
							<td rowspan="{$mcode_rowspan}">{$table[i].mcode|default:'-'}</td>
						{/if}
						<td rowspan="{$rowspan}">{$table[i].vendor_description}</td>
						<td rowspan="{$rowspan}">{$table[i].description}</td>
					
						{if count($smarty.request.data_row) > 1}
							<td nowrap><b>{$data_row_list.$first_data_type}</b></td>
						{/if}
						{foreach from=$label key=date item=r}
							<td rowspan="{$one_data_rowspan}" class="{$first_data_type} r">
								{if $first_data_type eq "quantity"}
									{$table[i].$first_data_type.$date|qty_nf}
								{else}
									{$table[i].$first_data_type.$date|number_format:2}
								{/if}
							</td>
						{/foreach}
						<td rowspan="{$one_data_rowspan}" class="{$first_data_type} r">
							{if $first_data_type eq "quantity"}
								{$table[i].$first_data_type.total|qty_nf}
							{else}
								{$table[i].$first_data_type.total|number_format:2}
							{/if}
						</td>
					</tr>
				</tbody>
				
				{if count($smarty.request.data_row) > 1}
					{assign var=artno_showed value=0}
					{assign var=row_count value=0}
					{assign var=data_row_count value=0}
					{foreach from=$data_row_list key=data_type item=data_desc}
						{capture}{$row_count++}{/capture}
						{if $smarty.request.data_row.$data_type && $data_type ne $first_data_type}
							{capture}{$data_row_count++}{/capture}
							<tbody class="fs-08">
								<tr>
									{if !$smarty.request.group_sku && !$artno_showed && $data_type eq $mcode_display_row}
										<td rowspan="{$artno_rowspan}">{$table[i].artno|default:'-'}</td>
										{assign var=artno_showed value=1}
									{/if}
									<td nowrap><b>{$data_desc}</b></td>
									{foreach from=$label key=date item=r}
										{if $data_type eq "quantity"}
											<td class="{$data_type} r">{$table[i].$data_type.$date|qty_nf}</td>
										{else}
											<td class="{$data_type} r">{$table[i].$data_type.$date|number_format:2}</td>
										{/if}
									{/foreach}
									<td class="{$data_type} r">
										{if $data_type eq "quantity"}
											{$table[i].$data_type.total|qty_nf}
										{else}
											{$table[i].$data_type.total|number_format:2}
										{/if}
									</td>
								</tr>
							</tbody>
						{/if}
					{/foreach}
				{elseif !$smarty.request.group_sku}
					<tr>
						<td rowspan="{$artno_rowspan}">{$table[i].artno|default:'-'}</td>
					</tr>
				{/if}
				
				{/section}
				<tr>
					{assign var=colspan value=4}
					{if !$smarty.request.group_sku}{assign var=colspan value=$colspan+1}{/if}
					{if $show_old_code}{assign var=colspan value=$colspan+1}{/if}
				
					<th colspan="{$colspan}" rowspan="{$ttl_rowspan}" class="r">Total</th>
					
					{if count($smarty.request.data_row) > 1}
						<td nowrap><b>{$data_row_list.$first_data_type}</b></td>
					{/if}
					{foreach from=$label key=date item=r}
						<td class="{$first_data_type} r">
							{if $first_data_type eq "quantity"}
								{$table2.$first_data_type.$date|qty_nf}
							{else}
								{$table2.$first_data_type.$date|number_format:2}
							{/if}
						</td>
					{/foreach}
					<td class="{$first_data_type} r">
						{if $first_data_type eq "quantity"}
							{$table2.$first_data_type.total|qty_nf}
						{else}
							{$table2.$first_data_type.total|number_format:2}
						{/if}
					</td>
				</tr>
				{if count($smarty.request.data_row) > 1}
					{assign var=row_count value=0}
					{foreach from=$data_row_list key=data_type item=data_desc}
						{capture}{$row_count++}{/capture}
						{if $smarty.request.data_row.$data_type && $data_type ne $first_data_type}
							<tr>
								<td nowrap><b>{$data_desc}</b></td>
								{foreach from=$label key=date item=r}
									<td class="{$data_type} r">
										{if $data_type eq "quantity"}
											{$table2.$data_type.$date|qty_nf}
										{else}
											{$table2.$data_type.$date|number_format:2}
										{/if}
									</td>
								{/foreach}
								<td class="{$data_type} r">
									{if $data_type eq "quantity"}
										{$table2.$data_type.total|qty_nf}
									{else}
										{$table2.$data_type.total|number_format:2}
									{/if}
								</td>
							</tr>
						{/if}
					{/foreach}
				{/if}
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

