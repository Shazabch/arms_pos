{*
9/19/2017 5:47 PM Justin
- Enhanced to highlight the field if stock balance is less than min qty.
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

.font_bgcolor{
	background-color:#ff9;
	opacity:0.8;
}

.highlight_row_title {
	background:none repeat scroll 0 0 yellow !important;
	border:1px solid black;
	font-weight:bold;
}
</style>

{/literal}

<script>
var phpself = "{$smarty.server.PHP_SELF}";
var date_from = "{$smarty.request.date_from}";
var date_to = "{$smarty.request.date_to}";
var branch_id = "{$smarty.request.branch_id}";
var vendor_id = "{$smarty.request.vendor_id}";
var use_grn = "{$smarty.request.use_grn}";
var owner_id = "{$smarty.request.owner_id}";

{literal}

function check_form(){
	passArrayToInput();
	if (document.f_a['serial_no'].value == "" && document.f_a['sku_code_list_2'].value == "") {
		alert("Please assign Serial No or SKU item.");
		return false;
	}
	
	return true;
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
<div class="card mx-3 card-body">
	<form method="post" class="form" action="report.broken_size_clr_by_branch.php" name="f_a" onSubmit="return check_form();">
		<p>
			<div class="row">
				{if $BRANCH_CODE eq 'HQ'}
				<div class="col">
					<b class="form-label">Branch</b>
				<select class="form-control" name="branch_id">
					<option value="">-- All --</option>
					{foreach from=$branches item=b}
						<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
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
			{/if}
		
			<div class="col">
				<b class="form-label">Age Group</b>
			<select class="form-control" name="age_group">
				{foreach from=$age_group_list key=val item=desc}
					<option value="{$val}" {if $smarty.request.age_group eq $val}selected{/if}>{$desc}</option>
				{/foreach}
			</select>
			</div>
			
			
			<div class="col">
				<b class="form-label">Brand</b>
			<select class="form-control" name="brand_id">
				<option value="">-- All --</option>
				{foreach from=$brand_list key=brand_id item=r}
					{if !$brand_group.have_group.$brand_id}
						<option value="{$brand_id}" {if $smarty.request.brand_id eq $brand_id}selected {/if}>{$r.description}</option>
					{/if}
				{/foreach}
				<optgroup label="Brand Group">
					{foreach from=$brand_group.header key=bgid item=bg}
						{if $brand_group.items.$bgid}
							{capture assign="brground_id"}group,{$bgid}{/capture}
							<option class="bg" value="group,{$bgid}" {if $brground_id eq $smarty.request.brand_id}selected {/if}>{$bg.description}</option>
							{foreach from=$brand_group.items.$bgid key=brand_id item=r}
								<option class="bg_item" value="{$brand_id}" {if $smarty.request.brand_id eq $brand_id}selected {/if}>{$brand_list.$brand_id.description}</option>
							{/foreach}
						{/if}			
					{/foreach}
				</optgroup>
			</select>
			</div>
			
			<div class="col">
				<div class="form-label form-inline mt-4">
					<label><input type="checkbox" name="include_nrr" value="1" {if $smarty.request.include_nrr}checked{/if} /><b>&nbsp;Including Not Reorder Require&nbsp;</b></label>
				</div>
			</div>
			</div>
		</p>
		
		<p>
		{include file="category_autocomplete.tpl" all=true}
		</p>
		
		<input type="hidden" name="subm" value="1">
		<button name="a" class="btn btn-primary mt-2" value="show_report">{#SHOW_REPORT#}</button>
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
		<button name="a" class="btn btn-info mt-2" value="output_excel">{#OUTPUT_EXCEL#}</button>
		{/if}
		</form>
</div>
{/if}
{if !$table}
<p align=center>-- No data --</p>
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<p>
<div class="alert alert-primary rounded mx-3">
	<b>Note:<br />
		* <label class="highlight_row_title"><img src="/ui/pixel.gif" width="17" height="15"></label> indicates the Stock Balance is less than min qty.
		</b>
</div>
</p>
{foreach from=$bid_list key=bid item=b}
	{if $table.$bid}
		{assign var=row_count value=0}
		<div class="card mx-3">
			<div class="card-body">
				<div class="table-responsive">
					<h4>{$branches.$bid.code} - {$branches.$bid.description}</h4>
		<table class="report_table small_printing table mb-0 text-md-nowrap  table-hover" width="100%" id="report_tbl">
		<thead class="bg-gray-100">
			<tr class="header">
				<th rowspan="2">#</th>
				<th rowspan="2">SKU Item Code</th>
				<th rowspan="2">MCode</th>
				<th rowspan="2">Description</th>
				<th rowspan="2">Stock Age Days</th>
				<th rowspan="2">&nbsp;</th>
				<th colspan="{count var=$size_list}">Size</th>
			</tr>
			<tr class="header">
				{foreach from=$size_list key=arr item=size name=s}
					<th>{$size}</th>
				{/foreach}
			</tr>
		</thead>
			<tbody class="fs-08">
				{foreach from=$table.$bid key=hid item=r name=tbl}
					<!--{$row_count++}-->
					<tr>
						<td rowspan="2">{$row_count}.</td>
						<td rowspan="2">{$r.sku_item_code}</td>
						<td rowspan="2">{$r.mcode}</td>
						<td rowspan="2">{$r.description}</td>
						<td align="right" rowspan="2">{$r.stock_age_days|qty_nf}</td>
						<td><b>Stock Balance</b></td>
						{foreach from=$size_list key=arr item=size name=s}
							<td align="right" {if $r.less_than_min_qty.RED.$size}style="background-color:yellow;"{/if}>{$r.sb_qty.RED.$size|qty_nf}</td>
						{/foreach}
					</tr>
					<tr>
						<td><b>30 Days Sales</b></td>
						{foreach from=$size_list key=arr item=size name=s}
							<td align="right">{$r.sales_qty.RED.$size|qty_nf}</td>
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
		</table>
				</div>
			</div>
		</div>
		<br /><br />
	{/if}
{/foreach}
{/if}

{if !$no_header_footer}
{literal}
<script type="text/javascript">
	reset_sku_autocomplete();
</script>
{/literal}
{/if}
{include file=footer.tpl}
