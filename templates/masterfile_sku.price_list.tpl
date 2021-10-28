{*
8/11/2011 7:06:37 PM Alex
- create by me

8/15/2011 12:06:24 PM Alex
- add sku type, active, block po item filter

8/19/2011 1:51:16 PM Alex
- add show multiple type price in a page

9/19/2011 10:11:00 AM Alex
- remove calender

3/19/2012 4:22:59 PM Andy
- Add vendor and brand filter.
- Add checkbox to toglle all price type.

11/7/2016 11:49 AM Andy
- Enhanced to check config.allow_all_sku_branch_for_selected_reports, and allow user to select category from department level.

11/15/2019 4:04 PM William
- Fixed bug error show in "SKU Price List" when select multi "Price List" and sorting by price.

06/26/2020 Sheila 02:26 PM
- Updated button css.
*}

{include file=header.tpl}
{if !$no_header_footer}
{literal}

<style>
.rpt_table tr:nth-child(even){
	background-color:#eeeeee;
}

/* standard style for report table */
.rpt_table {
	border-top:1px solid #000;
	border-right:1px solid #000;
}

.rpt_table td, .rpt_table th{
	border-left:1px solid #000;
	border-bottom:1px solid #000;
	padding:4px;
}

.rpt_table tr.header td, .rpt_table tr.header th{
	background:#fe9;
	padding:6px 4px;
}

</style>
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var branch_id = "{$smarty.request.branch_id}";
var category_id = "{$smarty.request.category_id}";
var category = "{$smarty.request.category}";

{literal}
function do_print(){
	window.print();
}

function toggle_all_price(){
	var p_all = $('p_all');
	
	$(document.f).getElementsBySelector('input.inp_price').each(function(inp){
		inp.checked = p_all.checked;
	});
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
	<b>The following error(s) has occured:</b>
<ul class="errmsg " >
{foreach from=$err item=e}
<div class="alert alert-danger mx-3 rounded">
		<li> {$e}</li>

</div>
{/foreach}
</ul>
</div>
{/if}
{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form name="f" method="post" class="form">

			<div class="row">
				<div class="col-md-3">
					{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label mt-2">Branch</b>
				<select class="form-control" name="branch_id">
					{foreach from=$branches key=bid item=bcode}
						<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$bcode}</option>
					{/foreach}
				</select>
			{/if}
				</div>

			<div class="col-md-3">
				<b class="form-label mt-2">Sku Type</b>
			<select class="form-control" name="sku_type">
				<option value="all" {if $smarty.request.sku_type eq 'all' || !$smarty.request.sku_type}selected {/if}>All</option>
				{foreach from=$sku_type key=scode item=stype}
					<option value="{$scode}" {if $smarty.request.sku_type eq $scode}selected {/if}>{$stype}</option>
				{/foreach}
			</select>
			</div>
			
			<div class="col-md-3">
				<b class="form-label mt-2">Status</b>
			<select class="form-control" name="status">
				<option value="all" {if $smarty.request.status eq 'all' || !$smarty.request.status}selected {/if}>All</option>
				<option value="1" {if $smarty.request.status eq '1'}selected {/if}>Active</option>
				<option value="0" {if $smarty.request.status eq '0'}selected {/if}>Inactive</option>
			</select>
			</div>
			
			<div class="col-md-3">
				<span>
					<b class="form-label mt-2">Brand</b>
					<select class="form-control" name="brand_id">
						<option value="" {if $smarty.request.brand_id === ''}selected {/if}>-- All --</option>
						<option value="0" {if $smarty.request.brand_id === '0'}selected {/if}>UNBRANDED</option>
						{foreach from=$brand_list key=brand_id item=r}
							<option value="{$brand_id}" {if $smarty.request.brand_id eq $brand_id}selected {/if}>{$r.description}</option>
						{/foreach}
					</select>
					</span>
			</div>
				
			<div class="col-md-3">
				<span>
					<b class="form-label mt-2">Vendor</b>
					<select class="form-control" name="vendor_id">
						<option value="">-- All --</option>
						{foreach from=$vendor_list key=vid item=r}
							<option value="{$vid}" {if $smarty.request.vendor_id eq $vid}selected {/if}>{$r.description}</option>
						{/foreach}
					</select>
					</span>	
			</div>
			
				<div class="col-md-3">
					<b class="form-label mt-2">Blocked Item in PO:</b>
				<select class="form-control" name="blocked_po">
					<option value="">-- No Filter --</option>
					<option value="yes" {if $smarty.request.blocked_po eq 'yes'}selected {/if}>Yes</option>
					<option value="no" {if $smarty.request.blocked_po eq 'no'}selected {/if}>No</option>
				</select>
				</div>
				<div class="col-md-3">
					<b class="form-label mt-2">Sort by</b>
				<select class="form-control" name="sort_by">
					<option value="sku_item_code" {if $smarty.request.sort_by eq "sku_item_code"}selected {/if}>ARMS Code</option>
					<option value="mcode" {if $smarty.request.sort_by eq "mcode"}selected {/if}>Manufacture Code</option>
					<option value="artno" {if $smarty.request.sort_by eq "artno"}selected {/if}>Article No</option>
					<option value="description" {if $smarty.request.sort_by eq "description"}selected {/if}>Description</option>
					<optgroup label="Price">
					<option value="normal" {if $smarty.request.sort_by eq 'normal'}selected {/if}>Normal</option>
					{foreach from=$config.sku_multiple_selling_price item=multiple_price}
						<option value="{$multiple_price}" {if $smarty.request.sort_by eq $multiple_price}selected {/if}>{$multiple_price|ucfirst}</option>
					{/foreach}
					</optgroup>
				</select>
				</div>
			</div>
		
			
			<p>
			{if $config.allow_all_sku_branch_for_selected_reports}
				{assign var=cat_level value=1}
				{assign var=cat_notice value="* Category start from Department level."}
			{else}
				{assign var=cat_level value=2}
				{assign var=cat_notice value="* Category start from 3rd level."}
			{/if}
			{include file="category_autocomplete.tpl" cat_level=$cat_level}
			</p>
			
			<table>
				<td valign="top"><b style="line-height:20px;" class="form-label">Price List</b></td>
				<td>
					<div style="width:130px;height:120px;padding:5px;overflow-x:hidden;overflow-y:auto;">
						<!-- All -->
						<input type="checkbox" id="p_all" onChange="toggle_all_price();" /> <label for="p_all"><b>All</b></label><br />
						
						<!-- Normal -->
						<input name="price_list[normal]" class="inp_price" id="p_normal" type="checkbox" value="normal" {if $smarty.request.price_list.normal}checked {/if}>
						<label for="p_normal">Normal</label><br>
						
						<!-- price type -->
						{foreach from=$config.sku_multiple_selling_price item=multiple_price}
							<input name="price_list[{$multiple_price}]" class="inp_price" id="p_{$multiple_price}" type="checkbox" value="{$multiple_price}" {if $smarty.request.price_list.$multiple_price }checked {/if}>
							<label for="p_{$multiple_price}">{$multiple_price|ucfirst}</label><br>
						{/foreach}
					</div>
			
				</td>
			</table>
			
			
			<p>
				<button class="btn btn-primary mt-2" name="a" value="show_report">{#SHOW_REPORT#}</button>
				{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
					<button class="btn btn-info mt-2" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
				{/if}
				<input type=button class="btn btn-info mt-2" onclick="do_print()" value="Print">
			</p>			
			{$cat_notice}
			</form>
	</div>
</div>
{/if}
{if !$table}
{if !$err && $generate}<p align=center>-- No Data --</p>{/if}
{else}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<p>
Table printed at {$time_printed}<br>
</p>
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="rpt_table" width=100% >
				<thead class="bg-gray-100">
					<tr class="header">
						<th rowspan=2 width="45px">#</th>
						<th rowspan=2 width="100px">ARMS Code</th>
						<th rowspan=2 width="100px">Manufacture Code</th>
						<th rowspan=2 width="150px">Article No.</th>
						<th rowspan=2>Description</th>
						<th colspan="{$price_col}">Price</th>	
					</tr>
					<tr class="header">
						{foreach from=$smarty.request.price_list item=ptype}
							<th width="80px">{$ptype}</th>
						{/foreach}
					</tr>	
					
				</thead>
				{foreach from=$table key=sid item=s name=t}
					<tbody class="fs-08">
						<tr>
							<td>{$smarty.foreach.t.iteration}.</td>
							<td>{$s.sku_item_code|default:"&nbsp;"}</td>
							<td>{$s.mcode|default:"&nbsp;"}</td>
							<td>{$s.artno|default:"&nbsp;"}</td>
							<td>{$s.description|default:"&nbsp;"}</td>
							{foreach from=$s.price item=ptype}
								<td class="r">{$ptype|number_format:2|ifzero:"-"}</td>
							{/foreach}
						</tr>
					</tbody>
					{assign var=ttl_batch_qty value=$ttl_batch_qty+$table.$sku_key.batch_qty}
					{assign var=ttl_sb_qty value=$ttl_sb_qty+$table.$sku_key.sb_qty}
				{/foreach}
			</table>
		</div>
	</div>
</div>
{/if}

{include file=footer.tpl}