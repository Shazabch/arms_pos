{*
3/26/2018 5:23 PM Justin
- Amended the Notes for Use GRN and Use HQ GRN.

2/26/2019 5:48 PM Andy
- Enhanced the report to show item Old Code.
- Fixed report should not show input tax and output tax if gst is not turn on.

3/8/2019 2:28 PM Andy
- Fixed "Old Code" should follow the config.link_code_name.

06/30/2020 02:25 PM Sheila
- Updated button css.

12/29/2020 2:58 PM William
- Enhanced to add new checkbox "No Screen Pagination", checked to show result on one table.

3/2/2021 4:12 PM Ian
-Enhanced to add new checkbox "Show More Sku Info",checked to show more info with added columns.
-Extend table by adding the "Color" ,"Size" ,"Department","Cat 1 ,2 ,3" when show more sku info was clicked.
-Made compatible with group by parent SKU & adjust the colspan accordingly.

3/23/2021 9:04 PM Ian
-Changed Column name from Cat1,2,3 to Category 1,2,3

5/3/2021 11:30 PM Ian
-Adding "Brand" Column when "Show More Sku Info" checked.
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
.below_cost 
{
	font-weight:bold;
	color:red;
}

@media print {
	.small_printing {
		font-size:10px;
	}	
}

.green{
	color:green;
}

.red{
	color:red;
}
</style>

<script>
function do_print(){
	window.print();
}

function chk_filter(){
	var allow_use_grn = true;
	var allow_use_hq_grn = true;
	
	if(document.f_d['branch_id']){
		if(!document.f_d['branch_id'].value){
			allow_use_hq_grn = allow_use_grn = false;
		}	
	}
	
	if(!$('vendor_id').value){
		allow_use_hq_grn = allow_use_grn = false;
	}	
	
	var inp_use_grn = $('use_grn');
	var inp_use_hq_grn = $('use_hq_grn');
	
	// not allow to select both at the same time
	if(allow_use_grn && inp_use_grn.checked)	allow_use_hq_grn = false;
	if(allow_use_hq_grn && inp_use_hq_grn.checked)	allow_use_grn = false;
	
	if(allow_use_grn){
		inp_use_grn.disabled=false;
	}
	else{
		inp_use_grn.checked=false;	
		inp_use_grn.disabled=true;	
	}
	
	if(allow_use_hq_grn){
		inp_use_hq_grn.disabled=false;
	}
	else{
		inp_use_hq_grn.checked=false;	
		inp_use_hq_grn.disabled=true;	
	}
}

function show_sc_date(k){
	curtain(true);
	center_div($('div_sc_date_'+k).show());
}
</script>
{/literal}
{/if}
<div class="noprint">
<h1>{$PAGE_TITLE}</h1>
</div>

<div class="noscreen">
<h2>{$p_branch.description}<br>{$title}</h2>
</div>
{if !$no_header_footer}
<div class="noprint stdframe" style="background:#fff;">

<form name="f_d">
<input type="hidden" name="report_title" value="Stock Balance by Department">
<input type="hidden" name="show_export_csv" value="{$smarty.request.show_export_csv}" />

<p>
{if $BRANCH_CODE eq 'HQ'}
	<b>Branch</b>
	<select name="branch_id" onChange="chk_filter();">
	{section name=i loop=$branch}
	<option value="{$branch[i].id}" {if $smarty.request.branch_id eq $branch[i].id}selected{/if}>{$branch[i].code}</option>
	{/section}
	</select> &nbsp;&nbsp;
{/if}

<b>Vendor</b>
<select name="vendor_id" onchange="chk_filter();" id="vendor_id">
<option value="">-- All --</option>
{section name=i loop=$vendor}
<option value="{$vendor[i].id}" {if $smarty.request.vendor_id eq $vendor[i].id}selected{assign var="_vd" value=`$vendor[i].description`}{/if}>{$vendor[i].description}</option>
{/section}
</select> &nbsp;&nbsp;

<span>
<b>Brand</b>
<select name="brand_id">
<option value=''>-- All --</option>
<option value=0>UN-BRANDED</option>

{if $brand_groups}
<optgroup label="Brand Group">
	{foreach from=$brand_groups key=bgk item=bgv}
	<option value="{$bgk}" {if $smarty.request.brand_id eq $bgk}selected{/if}>{$bgv}</option>
	{/foreach}
</optgroup>
{/if}

{if $brand}
<optgroup label="Brand">
{section name=i loop=$brand}
<option value="{$brand[i].id}" {if $smarty.request.brand_id eq $brand[i].id}selected{/if}>{$brand[i].description}</option>
{/section}
</optgroup>
{/if}
</select> &nbsp;&nbsp;
</span>

<span>
<b>SKU Type</b>
<select name="sku_type">
	<option value="">-- All --</option>
	{foreach from=$sku_type item=t}
	    <option value="{$t.code}" {if $smarty.request.sku_type eq $t.code}selected {/if}>{$t.description}</option>
	{/foreach}
</select>&nbsp;&nbsp;
</span>

<span>
<b>Sort by</b>
<select name="sort_by">
	<option value="">--</option>
	<option value="sku_item_code" {if $smarty.request.sort_by eq 'sku_item_code'}selected {/if}>ARMS Code</option>
    <option value="artno" {if $smarty.request.sort_by eq 'artno'}selected {/if}>Art No</option>
    <option value="mcode" {if $smarty.request.sort_by eq 'mcode'}selected {/if}>MCode</option>
    <option value="link_code" {if $smarty.request.sort_by eq 'link_code'}selected {/if}>{$config.link_code_name}</option>
    <option value="description" {if $smarty.request.sort_by eq 'description'}selected {/if}>Description</option>
</select>
</span>
</p>

<p>
	<b>Blocked Item in PO:</b>
	<select name="blocked_po">
		<option value="">-- No Filter --</option>
		<option value="yes" {if $smarty.request.blocked_po eq 'yes'}selected {/if}>Yes</option>
		<option value="no" {if $smarty.request.blocked_po eq 'no'}selected {/if}>No</option>
	</select> &nbsp;&nbsp;
	
	<b>Status</b>
	<select name="status">
		<option value="all" {if $smarty.request.status eq 'all'}selected {/if}>All</option>
		<option value="1" {if !isset($smarty.request.a) or $smarty.request.status eq '1'}selected {/if}>Active</option>
		<option value="0" {if $smarty.request.status eq '0'}selected {/if}>Inactive</option>
	</select>
	
	{if $config.enable_gst}
		&nbsp;&nbsp;
		<span>
			<b>Input Tax</b>
			<select name="input_tax_filter">
				<option value="">-- All --</option>
				{foreach from=$input_tax_list key=rid item=r}
					<option value="{$r.id}" {if $smarty.request.input_tax_filter eq $r.id}selected {/if}>{$r.code} ({$r.rate}%)</option>
				{/foreach}
			</select>
		</span>
		&nbsp;&nbsp;
		<span>
			<b>Output Tax</b>
			<select name="output_tax_filter">
				<option value="">-- All --</option>
				{foreach from=$output_tax_list key=rid item=r}
					<option value="{$r.id}" {if $smarty.request.output_tax_filter eq $r.id}selected {/if}>{$r.code} ({$r.rate}%)</option>
				{/foreach}
			</select>
		</span>
	{/if}
</p>

<p>
	{include file='category_autocomplete.tpl' all=$config.allow_all_sku_branch_for_selected_reports}
</p>

<p>
<b>Date</b> 
<input type="text" name="date" value="{$form.date}" id="added1" readonly="1" size="12"> <img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;

<input type="checkbox" id="use_grn" name="use_grn" {if $form.use_grn}checked{/if} onChange="chk_filter();" /> <b>Use GRN</b> [<a href="javascript:void(0)" onclick="alert('{$LANG.SD_USE_GRN_INFO|escape:javascript}')">?</a>]
&nbsp;&nbsp;

<input type="checkbox" id="use_hq_grn" name="use_hq_grn" {if $form.use_hq_grn}checked{/if} onChange="chk_filter();" /> <b>Use HQ GRN</b> [<a href="javascript:void(0)" onclick="alert('{$LANG.SD_USE_HQ_GRN_INFO|escape:javascript}')">?</a>]
&nbsp;&nbsp;

<input type="checkbox" id="group_by_sku" name="group_by_sku" {if $form.group_by_sku}checked{/if}> <b>Group By Parent SKU</b>
&nbsp;&nbsp;

{if $config.sku_listing_show_hq_cost and $sessioninfo.privilege.SHOW_COST and $BRANCH_CODE eq 'HQ'}
<input type="checkbox" id="hq_cost" name="hq_cost" {if $form.hq_cost}checked{/if}> <b>HQ Cost</b>
&nbsp;&nbsp;
{/if}

<input type="checkbox" id="no_screen_pagination" name="no_screen_pagination" {if $form.no_screen_pagination}checked{/if}> <b>No Screen Pagination</b>
&nbsp;&nbsp;

<input type="checkbox" id="show_more_info" name="show_more_info" {if $form.show_more_info}checked{/if}> <b>Show More SKU Info</b>
&nbsp;&nbsp;
</p>
<p>
<button class="btn btn-primary" name="a" value="show_report">Show Report</button>
<!--<input type=hidden name=submit value=1>-->
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
	<button class="btn btn-primary" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
<input type="button" onclick="do_print()" value="Print">

{if $smarty.request.show_export_csv}
<button value="export_csv" onClick="do_submit('export_csv');" >Export CSV</button>
{/if}

</p>

</form>
</div>

<br />
{/if}
{if $msg}<p style="color:red">{$msg}</p>{/if}

<ul>
	<li> Items with zero Stock Take Adjust and Closing Balance will not be displayed.</li>
	<li> <font color="red">*</font> means cost is not the latest</li>
	<li>Closing Balance's Value = {if $form.group_by_sku}AVG {/if}Cost * Qty</li>
	<li>Closing Balance's Sales Value = Selling Price * Qty</li>
</ul>

{assign var=item_per_page value=$config.stock_balance_item_per_page|ifzero:20}
{capture assign=HEADER}
<table class="report_table small_printing" {if $no_header_footer}border="1"{/if} width=100%>
<tr class="header">
	<th width="20" rowspan="2">&nbsp;</th>
	<th rowspan="2">ARMS Code</th>
	<th rowspan="2">MCode</th>
	<th rowspan="2">Artno</th>
	<th rowspan="2">{$config.link_code_name}</th>
	<th rowspan="2">Description</th>

	{if $form.show_more_info}
		<th rowspan="2">Size</th>
		<th rowspan="2">Color</th>
		<th rowspan="2">Department</th>
		<th rowspan="2">Category 1</th>
		<th rowspan="2">Category 2</th>
		<th rowspan="2">Category 3</th>
		<th rowspan="2">Brand</th>
	{/if}

	{if $config.enable_gst}
		<th rowspan="2">Input Tax</th>
		<th rowspan="2">Output Tax</th>
	{/if}
	
	{if $sessioninfo.privilege.SHOW_COST}
		<th rowspan="2" nowrap>{if $form.group_by_sku}AVG {/if}Cost</th>
	{/if}
	
	{if $sessioninfo.privilege.SHOW_COST}
		{assign var=default_colspan value=2}
		{assign var=default_stock_check_colspan value=5}
	{else}
		{assign var=default_colspan value=1}
		{assign var=default_stock_check_colspan value=3}
	{/if}	

	{assign var=colspan value=2}
	{if $got_closing_sc}
		{assign var=colspan value=$colspan+1}
	{/if}
	
	{if !$form.group_by_sku}
		{assign var=colspan value=$colspan+1}
	{/if}
	
	{if $sessioninfo.privilege.SHOW_COST}
		{assign var=colspan value=$colspan+1}
	{/if}

	<th colspan="{$colspan}">Closing Balance</th>
</tr>

<tr class="header">
	{if $got_closing_sc}
		<th>Stock Check Adjust</th>
	{/if}
	{if !$form.group_by_sku}
		<th nowrap>Selling<br>Price</th>
	{/if}
	<th>Qty</th>
	{if $sessioninfo.privilege.SHOW_COST}<th nowrap>Value</th>{/if}
	<th nowrap>Sales<br />Value</th>
	
</tr>
{/capture}

{capture assign=total_row}
	<tr class="header">
	    {assign var=colspan value=6}
		{if $config.enable_gst}
			{assign var=colspan value=$colspan+2}
		{/if}
		{if $form.show_more_info}
			{assign var=colspan value=$colspan+7}
		{/if}
	    {if $sessioninfo.privilege.SHOW_COST}{assign var=colspan value=$colspan+1}{/if}
	    <th colspan="{$colspan}" align="right">Total</th>
		{if $got_closing_sc}
			<th class="r">{$total.closing_sc_adj|qty_nf}</th>
		{/if}
		{if !$form.group_by_sku}
			<th></th>
			
		{/if}
		<th align="right">{$total.closing_bal|qty_nf}</th>
		{if $sessioninfo.privilege.SHOW_COST}<th align="right">{$total.closing_bal_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>{/if}
		<th align="right">{$total.closing_bal_sel_val|number_format:2|ifzero:'-'}</th>
	</tr>
{/capture}

{if $err}
	<ul style="color:red;">
	{foreach from=$err item=e}
	    <li>{$e}</li>
	{/foreach}
	</ul>
{/if}

{if $table}

{assign var=c value=0}
{assign var=put_header value=1}
{assign var=pg value=1}

{foreach from=$table key=k item=r}
	{if $put_header}
		<div>{$HEADER}
        {assign var=put_header value=0}
	{/if}
    {assign var=c value=$c+1}
    
    <tr>
        <td>{$c}</td>
		<td>{$r.info.sku_item_code}{if $r.info.changed}<font color="red">*</font>{/if}</td>
		<td>{$r.info.mcode}</td>
		<td>{$r.info.artno}</td>
		<td>{$r.info.link_code}</td>
		<td>{$r.info.description}</td>
			
		{if $form.show_more_info}
			<td>{$r.info.size}</td>
			<td>{$r.info.color}</td>
			<td>{$r.info.dept_desc|default:"-"}</td>
			<td>{$r.info.c1_desc|default:"-"}</td>
			<td>{$r.info.c2_desc|default:"-"}</td>
			<td>{$r.info.c3_desc|default:"-"}</td>
			<td>{$r.info.b_desc|default:"-"}</td>
		{/if}

		{if $config.enable_gst}
			<td align="center">{$r.info.input_tax.code}</td>
			<td align="center">{$r.info.output_tax.code}</td>
		{/if}
		
        {if $sessioninfo.privilege.SHOW_COST}
			<td align="right">{$r.cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</td>
		{/if}
		
		{if $got_closing_sc}
			<td align="right">{$r.closing_sc_adj|qty_nf}</td>
		{/if}
		{if !$form.group_by_sku}
			<td align="right" {if $r.cost>$r.selling_price}class="below_cost"{/if}>
				{$r.selling_price|number_format:2|ifzero}
				{if $branch_is_under_gst && $r.selling_price_before_gst}
					<br><h5 style="color: blue; white-space: nowrap">Excl.: {$r.selling_price_before_gst|number_format:2|ifzero}</h5>
				{/if}
			</td>
		{/if}
		<td align="right">{$r.closing_bal|qty_nf}</td>
		{if $sessioninfo.privilege.SHOW_COST}
			<td align="right">{$r.closing_bal_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}</td>
		{/if}
		<td align="right">{$r.closing_bal_sel_val|number_format:2|ifzero:'-'}</td>
	</tr>

    {if $c%$item_per_page==0 && !$form.no_screen_pagination}
        {if $c eq count($table)}{$total_row}{/if}
		</table></div>
		<h3 align="right">Page {$pg++}</h3>
		<div style="page-break-before:always"></div>
		
		{assign var=put_header value=1}
	{/if}
{/foreach}

{if !$put_header}
    {if $c eq count($table)}{$total_row}{/if}
    </table></div>
    {if !$form.no_screen_pagination}
	<h3 align="right">Page {$pg++}</h3>
	{/if}
	  {*<div style="page-break-before:always"></div>*}
{/if}
</table>

{else}
-- No Data --
{/if}

<div class="noscreen">
{include file=report_footer.landscape.tpl}
</div>
{if !$no_header_footer}
{literal}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "added1",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
	chk_filter();
</script>
{/literal}
{/if}
{include file=footer.tpl}
