{*
REVISION HISTORY
=================
2/19/2008 5:12:13 PM gary
- change GP calculation to use GR = (POS* SELLING)-(POS*COST)

2/20/2008 9:38:41 AM gary
- re-format the GP calculation.

7/1/2008 3:03:47 PM yinsee
- change department dropdown become category dropdown

7/7/2008 4:22:04 PM yinsee 
- fix on hand cost bug where Adjustment In was not included

4/15/2009 11:00 AM Andy
- add sorting dropdown

11/4/2009 10:42:58 AM Andy
- edit cost, selling & stock check, change new calculation

11/10/2009 9:58:25 AM Andy
- GP % column edited

11/16/2009 2:18:38 PM Andy
- Add SKU Type filter

12/29/2009 10:51:38 AM Andy
- Modify stock check popup to show more details information

2/23/2010 3:10:19 PM Andy
- add config to control number of item per page

4/2/2010 3:00:53 PM Andy
- Add turnover calculation, only show if got config

5/6/2010 4:47:25 PM Alex
- Add hq cost and Show Cost privilege

9/2/2010 9:52:30 AM Andy
- Change turnover to no decimal point.

11/9/2010 10:23:03 AM Alex
- add "0" if got stock check but no qty

3/14/2011 4:07:13 PM Alex
- do value remarks

6/22/2011 12:33:38 PM Andy
- Add filter "Blocked Item in PO" in stock balance by department report.

7/7/2011 2:58:16 PM Alex
- add stock check adjustment
- fix column bugs when no show cost privilege

8/4/2011 11:42:28 AM Alex
- add selling price for opeing, total on hand, closing

8/11/2011 6:28:21 PM Alex
- Change number_format to num_format for qty

8/15/2011 11:33:21 AM Justin
- Added filter "Status" for SKU.

9/27/2011 3:31:10 PM Andy
- Change report to also show those SKU which got GRN between from/to date.
- Add GRN Qty,Cost to show additional qty/cost for selected vendor when use GRN.
- Modify "use grn" popup message.

10/14/2011 4:44:12 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

11/15/2011 4:19:12 PM Andy
- Fix toggle "Use GRN" checkbox error.

11/24/2011 3:04:35 PM Andy
- Change "Use GRN" popup information message.

4/12/2012 5:47:15 PM Andy
- Change report default select filter active SKU.

5/4/2012 2:21:12 PM Justin
- Added to pickup new info "stock check adjust qty" for opening balance and "stock take adjust qty and value" for range.

1/10/2013 1:52:00 PM Fithri
- add (*) for those item changed=1

7/17/2013 3:00 PM Andy
- Remove un-needed stock take date list.

4/2/2014 11:02 AM Fithri
- add option to select all SKU / branches for some reports, require config 'allow_all_sku_branch_for_selected_reports' to be turned on

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

10/7/2015 3:22 PM Andy
- Enhanced to put calculate format as hint.

03/30/2016 14:45 Edwin
- Enhanced on show exclusive selling price when SKU item is inclusive tax and branch is unser gst

07/01/2016 14:00 Edwin
- Show 'HQ Cost' when branch is HQ and $config.sku_listing_show_hq_cost is enabled

5/9/2017 5:05 PM Andy
- Added "Use HQ GRN" feature.
- Split column "ArtNo/MCode" to two columns.

8/15/2017 15:16 PM Qiu Ying
- Enhanced to add Sales Value at Opening and Closing
- Enhanced to move the GP and turnover columns to before Closing Balance column
- Enhanced to add "Category Level" filter, default "Any Level" in category autocomplete

10/10/2017 3:10 PM Justin
- Enhanced to sum up closing balance from accumulated cost (GRA, GRN, Adjustment and etc) when config is turned on.

10/16/2017 3:26 PM Justin
- Enhanced to hide "Selling Price" while group by SKU filter is ticked.
- Enhanced to all columns that showing both qty and value become showing either Qty or Cost base on "Show by Qty" or "Show by Cost" button.

10/26/2017 4:36 PM Justin
- Enhanced to show cost decimal points base on config set.

3/12/2018 6:06 PM HockLee
- Added filter by Input Tax and Output Tax.
- Show up if enable_gst is on.

7/5/2019 9:16 AM William
- Added new "Day Turnover" column to stock balance report.
- Remove config of "Turnover", when turnover don't have config turnover also will show.

06/30/2020 02:42 PM Sheila
- Updated button css.

8/3/2020 11:06 AM William
- Enhanced to show red color text for negative value.

10/23/2020 11:11 AM William
- Enhanced to hide input tax and output tax when config "enable_gst" not activate.
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

function do_submit(mode){
	document.f_d.type.value=mode;
}

/*
var category_autocompleter = null;
function show_child(id)
{
	// reactivate the auto-completer with child of the category
	setTimeout('category_autocompleter.options.defaultParams = "child='+id+'";category_autocompleter.activate()',250);
}
function init_autocomplete()
{
	category_autocompleter = new Ajax.Autocompleter("autocomplete_category", "autocomplete_category_choices", "ajax_autocomplete.php?a=ajax_search_category&min_level=1", {
	afterUpdateElement: function (obj,li)
	{
	    this.defaultParams = '';
		var s = li.title.split(',');
		document.f_d.category_id.value = s[0];
		sel_category(obj,s[1]);
	}});
}

function sel_category(obj,have_child)
{
	var str = new String(obj.value);
	str.replace('<span class=sh>', '');
	str.replace('</span>', '');
	document.f_d.category_tree.value = str;
	$('str_cat_tree').innerHTML = str;
	obj.value = str.substr(str.lastIndexOf(">")+2, str.length);
}
*/
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

{assign var=got_sc value=0}

<form name="f_d">
<input type=hidden name=report_title value="Stock Balance by Department">
<input type=hidden name=type>
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
<select name=vendor_id onchange="chk_filter();" id="vendor_id">
<option value="">-- All --</option>
{section name=i loop=$vendor}
<option value="{$vendor[i].id}" {if $smarty.request.vendor_id eq $vendor[i].id}selected{assign var=_vd value=`$vendor[i].description`}{/if}>{$vendor[i].description}</option>
{/section}
</select> &nbsp;&nbsp;

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

<b>SKU Type</b>
<select name="sku_type">
	<option value="">-- All --</option>
	{foreach from=$sku_type item=t}
	    <option value="{$t.code}" {if $smarty.request.sku_type eq $t.code}selected {/if}>{$t.description}</option>
	{/foreach}
</select>

&nbsp;&nbsp;

<b>Sort by</b>
<select name="sort_by">
	<option value="">--</option>
	<option value="sku_item_code" {if $smarty.request.sort_by eq 'sku_item_code'}selected {/if}>ARMS Code</option>
    <option value="artno" {if $smarty.request.sort_by eq 'artno'}selected {/if}>Art No</option>
    <option value="mcode" {if $smarty.request.sort_by eq 'mcode'}selected {/if}>MCode</option>
    <option value="linkcode" {if $smarty.request.sort_by eq 'linkcode'}selected {/if}>CM Code</option>
    <option value="description" {if $smarty.request.sort_by eq 'description'}selected {/if}>Description</option>
</select>
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
</p>

<p>
{*
<b>Category</b>
{if $config.allow_all_sku_branch_for_selected_reports}
<input type=checkbox id=all_cat name=all_cat {if $smarty.request.all_cat}checked{/if} onchange="showdiv('sel_cat', this.checked)"> <label for=all_cat><b>All</b></label>
{/if}

<span id=sel_cat {if $smarty.request.all_cat}style="display:none"{/if}>
<input readonly name=category_id size=1 value="{$smarty.request.category_id}">
<input type=hidden name=category_tree value="{$smarty.request.category_tree}">
<input id=autocomplete_category name=category value="{$smarty.request.category|default:'Enter keyword to search'}" onfocus=this.select() size=50><br><span id=str_cat_tree class=small style="color:#00f;margin-left:90px;">{$smarty.request.category_tree|default:''}</span>
</span>
<div id=autocomplete_category_choices class=autocomplete style="width:600px !important"></div>
*}

{include file='category_autocomplete.tpl' all=$config.allow_all_sku_branch_for_selected_reports}
</p>
{if $config.enable_gst}
<p>
	<b>Input Tax</b>
	<select name="input_tax">
		<option value="">-- All --</option>
		{foreach from=$input_tax_list key=rid item=r}
			<option value="{$r.id}" {if $smarty.request.input_tax eq $r.id}selected {/if}>{$r.code} ({$r.rate}%)</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;
	<b>Output Tax</b>
	<select name="output_tax">
		<option value="">-- All --</option>
		{foreach from=$output_tax_list key=rid item=r}
			<option value="{$r.id}" {if $smarty.request.output_tax eq $r.id}selected {/if}>{$r.code} ({$r.rate}%)</option>
		{/foreach}
	</select>
</p>
{/if}
<p>
<b>Date From</b> 
<input type="text" name="from" value="{$form.from}" id="added1" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"> 
&nbsp; 
<b>To</b> 
<input type="text" name="to" value="{$form.to}" id="added2" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;

<input type="checkbox" id="use_grn" name="use_grn" {if $form.use_grn}checked{/if} onChange="chk_filter();" /> <b>Use GRN</b> [<a href="javascript:void(0)" onclick="alert('{$LANG.USE_GRN_INFO|escape:javascript}')">?</a>]
&nbsp;&nbsp;

<input type="checkbox" id="use_hq_grn" name="use_hq_grn" {if $form.use_hq_grn}checked{/if} onChange="chk_filter();" /> <b>Use HQ GRN</b> [<a href="javascript:void(0)" onclick="alert('{$LANG.USE_HQ_GRN_INFO|escape:javascript}')">?</a>]
&nbsp;&nbsp;

<input type=checkbox id=group_by_sku name=group_by_sku {if $form.group_by_sku}checked{/if}> <b>Group By Parent SKU</b>
&nbsp;&nbsp;

{if $config.sku_listing_show_hq_cost and $sessioninfo.privilege.SHOW_COST and $BRANCH_CODE eq 'HQ'}
<input type=checkbox id=hq_cost name=hq_cost {if $form.hq_cost}checked{/if}> <b>HQ Cost</b>
&nbsp;&nbsp;
{/if}
</p>
<p>
<button class="btn btn-primary" name="a" value=show_report onclick="do_submit('qty')" >Show by Qty</button>
{if $sessioninfo.privilege.SHOW_COST}
	<button class="btn btn-primary" name=a value=show_report onclick="do_submit('cost')">Show by Cost</button>
{/if}
<!--<input type=hidden name=submit value=1>-->
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
	<button class="btn btn-primary" name=a value=output_excel onclick="do_submit('qty')">{#OUTPUT_EXCEL#} by Qty</button>
	{if $sessioninfo.privilege.SHOW_COST}
		<button class="btn btn-primary" name=a value=output_excel onclick="do_submit('cost')">{#OUTPUT_EXCEL#} by Cost</button>
	{/if}
{/if}
<input class="btn btn-primary" type=button onclick="do_print()" value="Print">

{if $smarty.request.show_export_csv}
<button value="export_csv" onClick="do_submit('export_csv');" >Export CSV</button>
{/if}

</p>

</form>
</div>
{/if}
{if $msg}<p style="color:red">{$msg}</p>{/if}

<ul>
	<li> Items with zero Opening Balance, Qty in Hand and Closing Balance will not be displayed.</li>
	<li> <sup>1</sup> The value is from closing date.</li>
	<li> <sup>2</sup> The Value is from start date.</li>
	<li> <sup>3</sup> GP is based on Items Sold (POS column) within the selected period.</li>
	<li> DO{if $config.consignment_modules}, CN, DN{/if} value is from closing date.</li>
	<li> <font color="red">*</font> means cost is not the latest</li>
	<li>
		Opening Balance + Stock Check Adj Qty + Receiving Qty + Adj IN = Total On Hand Qty<br />
		Total On Hand Qty - POS - GRA - DO - Adj OUT = Closing Balance.
	</li>
	<li>Opening / Closing Balance's Value = {if $form.group_by_sku}AVG {/if}Cost * Qty</li>
	<li>Opening / Closing Balance's Sales Value = Selling Price * Qty</li>
	<li> Day Turnover Method:
		<ul>
			<li> Ratio = total sales / ((opening stock + closing stock) / 2)</li>
			<li> Days = 365 / ratio</li>
		</ul>
	</li>
</ul>

{assign var=item_per_page value=$config.stock_balance_item_per_page|ifzero:20}
{capture assign=HEADER}
<table class="report_table small_printing" {if $no_header_footer}border="1"{/if} width=100%>
<tr class=header>
	<th width="20" rowspan="2">&nbsp;</th>
	<th rowspan="2">ARMS Code</th>
	<th rowspan="2">MCode</th>
	<th rowspan="2">Artno</th>
	<th rowspan="2">Description</th>
	{if $config.enable_gst}
    <th rowspan="2">Input Tax</th>
    <th rowspan="2">Output Tax</th>
	{/if}
	{if $sessioninfo.privilege.SHOW_COST}
	<th rowspan="2" nowrap>{if $form.group_by_sku}AVG {/if}Cost <sup>1</sup></th>
	{/if}
	
	{if $sessioninfo.privilege.SHOW_COST}
		{assign var=default_colspan value=2}
		{assign var=default_stock_check_colspan value=5}
	{else}
		{assign var=default_colspan value=1}
		{assign var=default_stock_check_colspan value=3}
	{/if}	

	{if $got_opening_sc}
		{assign var=osc_colspan value=1}
	{/if}
	
	{assign var=add_colspan2 value=1}
	
	{if !$form.group_by_sku}
		{assign var=add_colspan2 value=$add_colspan2+1}
	{/if}

	<th colspan="{$default_colspan+$osc_colspan+$add_colspan2}">Opening Balance</th>
    {if $got_stock_check}
	<th colspan="3">Stock Check</th>
	{/if}
	<th rowspan="2">GRN</th>
	<th rowspan="2">Adj IN</th>
	<th rowspan="2">Total On Hand</th>
	<th rowspan="2">POS</th>
	<th rowspan="2">GRA</th>
	<th rowspan="2">DO</th>
	<th rowspan="2">Adj OUT</th>
    {if $config.consignment_modules}
        <th rowspan="2">CN</th>
        <th rowspan="2">DN</th>
    {/if}
	{if $sessioninfo.privilege.SHOW_REPORT_GP}
	<th colspan="2" nowrap>GP<sup>3</sup></th>
	{/if}
	<th rowspan="2">Turnover</th>
	<th colspan="{$default_colspan+$add_colspan2}">Closing Balance</th>
	<th colspan="2">Days Turnover</th>
</tr>

<tr class=header>
	{if $got_opening_sc}
		<th>Stock Check Adjust</th>
	{/if}
	{if !$form.group_by_sku}
		<th nowrap>Selling<br>Price <sup>2</sup></th>
	{/if}
	<th>Qty</th>
	{if $sessioninfo.privilege.SHOW_COST}<th nowrap>Value <sup>2</sup></th>{/if}
	<th nowrap>Sales<br />Value <sup>2</sup></th>
	{if $got_stock_check}
		<th nowrap>Adj {if $form.type eq 'qty'}Qty{else}Value<sup>1</sup>{/if}</th>
	    <th>Date</th>
	    <th>{if $form.type eq 'qty'}Qty{else}Value{/if}</th>
	{/if}
	
	{if $sessioninfo.privilege.SHOW_REPORT_GP}
		<th>Value</th>
		<th>%</th>
	{/if}
	{if !$form.group_by_sku}
		<th nowrap>Selling<br>Price <sup>1</sup></th>
	{/if}
	<th>Qty</th>
	{if $sessioninfo.privilege.SHOW_COST}<th nowrap>Value {if !$config.stock_balance_use_accumulate_last_cost}<sup>1</sup>{/if}</th>{/if}
	<th nowrap>Sales<br />Value <sup>1</sup></th>
	<th nowrap>Ratio</th>
	<th nowrap>Days</th>
</tr>
{/capture}

{capture assign=total_row}
	<tr class="header">
	    {assign var=cols value=5}
		{if $config.enable_gst}{assign var=cols value=$cols+2}{/if}
	    {if $sessioninfo.privilege.SHOW_COST}{assign var=cols value=$cols+1}{/if}
	    <th colspan="{$cols}" align="right">Total</th>
		{if $got_opening_sc}
			<th class="r {if $total.open_sc_adj < 0}red{/if}">{$total.open_sc_adj|qty_nf}</th>
		{/if}
		{if !$form.group_by_sku}
			<th class="r"></th>
		{/if}
	    <th class="r {if $total.open_bal < 0}red{/if}">{$total.open_bal|qty_nf}</th>
	    {if $sessioninfo.privilege.SHOW_COST}<th class="r {if $total.open_bal_val < 0}red{/if}">{$total.open_bal_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>{/if}
		<th align="right" class="{if $total.open_bal_sel_val < 0}red{/if}">{$total.open_bal_sel_val|number_format:2|ifzero:'-'}</th>
	    {if $got_stock_check}
	        {if !$no_header_footer}
	        <div id="div_sc_date_total" class="noprint curtain_popup" style="position:absolute;width:400px;height:500px;border:1px solid black;background-color:white;display:none;z-index:10000;">
				<h3>Stock Check Info</h3>
				    <div style="width:400px;height:400px;overflow-y:auto;">
					<ul>
					{foreach from=$total.sc_date key=sku_item_code item=sc_info}
			           <li><b>{$sku_item_code}</b> at {$sc_info.sc_date} ({$sc_info.sc_bal|qty_nf} qty, value {$sc_info.sc_bal_val|number_format:$config.global_cost_decimal_points})</li>
			        {/foreach}
		            </ul>
		            </div>
				<p align="center"><input type="button" value="Close" onClick="default_curtain_clicked();" /></p>
			</div>
			{/if}
			<th class="r {if ($form.type eq 'qty' && $total.sc_adj_bal>0) || ($form.type ne 'qty' && $total.sc_adj_bal_val>0)}green{elseif ($form.type eq 'qty' && $total.sc_adj_bal<0) || ($form.type ne 'qty' && $total.sc_adj_bal_val<0)}red{/if}" nowrap>
				{if ($form.type eq 'qty' && $total.sc_adj_bal>0) || ($form.type ne 'qty' && $total.sc_adj_bal_val>0)}+{/if}{if $form.type eq 'qty'}{$total.sc_adj_bal|qty_nf|ifzero:"0"}{else}{$total.sc_adj_bal_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}{/if}
			</th>
	        <th>
				{if $total.sc_date}
					{if !$no_header_footer}
					<a href="javascript:void(show_sc_date('total'));">[ {count var=$total.sc_date} ]</a>
					{else}
					    [ {count var=$total.sc_date} ]
					{/if}
				{/if}
			</th>
	        <th class="r {if ($form.type eq 'qty' && $total.sc_bal<0) || ($form.type neq 'qty' && $total.sc_bal_val<0)}red{/if}">
			{if $form.type eq 'qty'}{$total.sc_bal|qty_nf|ifzero:"0"}{else}{$total.sc_bal_val|number_format:$config.global_cost_decimal_points|ifzero:"-"}{/if}
			</th>
	    {/if}
	    <th class="r">
			{if $form.type eq 'qty'}
				<font {if $total.rcv_qty < 0} color="red" {/if}>{$total.rcv_qty|qty_nf|ifzero} </font>
				{if $smarty.request.use_grn}
					<font {if $total.rcv_vendor_qty < 0} color="red" {/if}>
					({$total.rcv_vendor_qty|qty_nf|ifzero})
					</font>
				{/if}
			{else}
				<font {if $total.rcv_val < 0} color="red" {/if}>{$total.rcv_val|number_format:$config.global_cost_decimal_points|ifzero}</font>
				{if $smarty.request.use_grn}
					<font {if $total.rcv_vendor_val < 0} color="red" {/if}>
					({$total.rcv_vendor_val|number_format:$config.global_cost_decimal_points|ifzero})
					</font>
				{/if}
			{/if}
		</th>
		<th align="right" class="{if ($form.type eq 'qty' && $total.adj_in < 0) || ($form.type neq 'qty' && $total.adj_in_val < 0) }red{/if}">
		{if $form.type eq 'qty'}{$total.adj_in|qty_nf|ifzero}{else}{$total.adj_in_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
		</th>
		<th class="r {if ($form.type eq 'qty' && $total.on_hand_qty < 0) || ($form.type neq 'qty' && $total.on_hand_val < 0) }red{/if}">
			{if $form.type eq 'qty'}
				{$total.on_hand_qty|qty_nf}
			{else}
				{$total.on_hand_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}
			{/if}
		</th>
		<th align="right" class="{if ($form.type eq 'qty' && $total.pos_qty < 0) || ($form.type neq 'qty' && $total.pos_val < 0) }red{/if}">
		{if $form.type eq 'qty'}{$total.pos_qty|qty_nf|ifzero}{else}{$total.pos_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
		</th>
		<th align="right" class="{if ($form.type eq 'qty' && $total.gra_qty < 0) || ($form.type neq 'qty' && $total.gra_val < 0) }red{/if}">
		{if $form.type eq 'qty'}{$total.gra_qty|qty_nf|ifzero}{else}{$total.gra_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
		</th>
		<th align="right" class="{if ($form.type eq 'qty' && $total.do_qty < 0) || ($form.type neq 'qty' && $total.do_val < 0) }red{/if}">
		{if $form.type eq 'qty'}{$total.do_qty|qty_nf|ifzero}{else}{$total.do_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
		</th>
		<th align="right" class="{if ($form.type eq 'qty' && $total.adj_out < 0) || ($form.type neq 'qty' && $total.adj_out_val < 0) }red{/if}">
		{if $form.type eq 'qty'}{$total.adj_out|qty_nf|ifzero}{else}{$total.adj_out_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
		</th>
	    {if $config.consignment_modules}
	        <th align="right" class="{if ($form.type eq 'qty' && $total.cn_qty < 0) || ($form.type neq 'qty' && $total.cn_val < 0) }red{/if}">
			{if $form.type eq 'qty'}{$total.cn_qty|qty_nf|ifzero}{else}{$total.cn_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
			</th>
			
	        <th align="right" class="{if ($form.type eq 'qty' && $total.dn_qty < 0) || ($form.type neq 'qty' && $total.dn_val < 0) }red{/if}">
			{if $form.type eq 'qty'}{$total.dn_qty|qty_nf|ifzero}{else}{$total.dn_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
			</th>
	    {/if}
		{if $sessioninfo.privilege.SHOW_REPORT_GP}
			<th align="right" class="{if $total.gp_val < 0}red{/if}">{$total.gp_val|number_format:$config.global_cost_decimal_points|ifzero}</th>
			<th align="right" class="{if $total.pos_qty < 0}red{/if}">{if $total.pos_qty}{$total.gp_per|number_format:2|ifzero:'':'%'}{/if}</th>
		{/if}
		<th align="right" class="{if $total.turnover < 0}red{/if}">{$total.turnover|qty_nf}</th>
		{if !$form.group_by_sku}
			<th></th>
		{/if}
		<th align="right" class="{if $total.closing_bal < 0}red{/if}">{$total.closing_bal|qty_nf}</th>
		{if $sessioninfo.privilege.SHOW_COST}<th align="right" class="{if $total.closing_bal_val < 0}red{/if}">{$total.closing_bal_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>{/if}
		<th align="right" class="{if $total.closing_bal_sel_val < 0}red{/if}">{$total.closing_bal_sel_val|number_format:2|ifzero:'-'}</th>
		<th align="right" class="{if $total.turnover_ratio < 0}red{/if}">{$total.turnover_ratio|number_format:2|ifzero:'0'}</th>
		<th align="right" class="{if $total.turnover_days < 0}red{/if}">{$total.turnover_days|number_format:2|ifzero:'0'}</th>
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
		<td>{$r.info.description}</td>
		{if $config.enable_gst}
        <td align="center">{$r.info.input_tax.code}</td>
		<td align="center">{$r.info.output_tax.code}</td>
		{/if}
        {if $sessioninfo.privilege.SHOW_COST}
			<td align="right" class="{if $r.cost < 0}red{/if}">{$r.cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</td>
		{/if}
		
		{if $got_opening_sc}
			<td align="right" class="{if $r.open_sc_adj < 0}red{/if}">{$r.open_sc_adj|qty_nf}</td>
		{/if}
		{if !$form.group_by_sku}
			<td align="right" {if $r.cost>$r.opening_selling_price}class="below_cost"{/if}>
				<font {if $r.opening_selling_price < 0} color="red" {/if}>{$r.opening_selling_price|number_format:2|ifzero}</font>
				{if $branch_is_under_gst && $r.opening_selling_price_before_gst}
					<br><h5 style="color: blue; white-space: nowrap">Excl.: {$r.opening_selling_price_before_gst|number_format:2|ifzero}</h5>
				{/if}
			</td>
		{/if}
		<td align="right" class="{if $r.open_bal < 0}red{/if}">{$r.open_bal|qty_nf}</td>
		{if $sessioninfo.privilege.SHOW_COST}<td align="right" class="{if $r.open_bal_val < 0}red{/if}">{$r.open_bal_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}</td>{/if}
		<td align="right" class="{if $r.open_bal_sel_val < 0}red{/if}">{$r.open_bal_sel_val|number_format:2|ifzero:'-'}</td>
		{if $got_stock_check}
		    {if $form.group_by_sku}
		        {if $r.got_sc}
		            {if !$no_header_footer}
		            <div id="div_sc_date_{$k}" class="noprint curtain_popup" style="position:absolute;width:400px;height:500px;border:1px solid black;background-color:white;display:none;z-index:10000;overflow-y:auto;">
						<ul>{foreach from=$r.sc_date key=sku_item_code item=sc_info}
					        <li><b>{$sku_item_code}</b> at {$sc_info.sc_date} ({$sc_info.sc_bal|qty_nf} qty{if $sessioninfo.privilege.SHOW_COST}, value {$sc_info.sc_bal_val|number_format:$config.global_cost_decimal_points}){/if}</li>
					    {/foreach}</ul>
					</div>
					{/if}
					<td class="r {if ($form.type eq 'qty' && $r.sc_adj_bal>0) || ($form.type ne 'qty' && $r.sc_adj_bal_val>0)}green{elseif ($form.type eq 'qty' && $r.sc_adj_bal<0) || ($form.type ne 'qty' && $r.sc_adj_bal_val<0)}red{/if}" nowrap>
						{if ($form.type eq 'qty' && $r.sc_adj_bal>0) || ($form.type ne 'qty' && $r.sc_adj_bal_val>0)}+{/if}{if $form.type eq 'qty'}{$r.sc_adj_bal|qty_nf|ifzero:"0"}{else}{$r.sc_adj_bal_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}{/if}
					</td>
					<td align="right">{$r.last_sc_date}</td>
					<td align="right">
					{if $form.type eq 'qty'}
						<font {if $r.sc_bal < 0} color="red" {/if}>
						{$r.sc_bal|qty_nf|ifzero:"0"}
						</font>
					{else}
						<font {if $r.sc_bal_val < 0} color="red" {/if}>
						{$r.sc_bal_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}
						</font>
					{/if}
					</td>
                {else}
				    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
				{/if}
			{else}
				{if $r.got_sc}
					<td class="r {if ($form.type eq 'qty' && $r.sc_adj_bal>0) || ($form.type ne 'qty' && $r.sc_adj_bal_val>0)}green{elseif ($form.type eq 'qty' && $r.sc_adj_bal<0) || ($form.type ne 'qty' && $r.sc_adj_bal_val<0)}red{/if}" nowrap>
						{if ($form.type eq 'qty' && $r.sc_adj_bal>0) || ($form.type ne 'qty' && $r.sc_adj_bal_val>0)}+{/if}{if $form.type eq 'qty'}{$r.sc_adj_bal|qty_nf|ifzero:"0"}{else}{$r.sc_adj_bal_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}{/if}
					</td>
					<td align="right">{$r.sc_date}</td>
					<td align="right class="{if ($form.type eq 'qty' && $r.sc_bal<0) || ($form.type neq 'qty' && $r.sc_bal_val<0)}red{/if}">
					{if $form.type eq 'qty'}{$r.sc_bal|qty_nf|ifzero:"0"}{else}{$r.sc_bal_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}{/if}
					</td>
				{else}
				    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
				{/if}
			{/if}
		{/if}
		<td align="right">
			{if $form.type eq 'qty'}
				<font {if $r.rcv_qty < 0} color="red" {/if}>{$r.rcv_qty|qty_nf|ifzero}</font>
				{if $smarty.request.use_grn}
					<font {if $r.rcv_vendor_qty < 0} color="red" {/if}>({$r.rcv_vendor_qty|qty_nf|ifzero})</font>
				{/if}
			{else}
				<font {if $r.rcv_val < 0} color="red" {/if}>{$r.rcv_val|number_format:$config.global_cost_decimal_points|ifzero}</font>
				{if $smarty.request.use_grn}
					<font {if $r.rcv_vendor_val < 0} color="red" {/if}>({$r.rcv_vendor_val|number_format:$config.global_cost_decimal_points|ifzero})</font>
				{/if}
			{/if}
		</td>
		<td align="right" class="{if ($form.type eq 'qty' && $r.adj_in < 0) || ($form.type neq 'qty' && $r.adj_in_val<0)}red{/if}">
			{if $form.type eq 'qty'}{$r.adj_in|qty_nf|ifzero}{else}{$r.adj_in_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
		</td>
		<td align="right" class="{if ($form.type eq 'qty' && $r.on_hand_qty < 0) || ($form.type neq 'qty' && $r.on_hand_val<0)}red{/if}">
			{if $form.type eq 'qty'}{$r.on_hand_qty|qty_nf|ifzero:"0"}{else}{$r.on_hand_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}{/if}
		</td>
		<td align="right" class="{if ($form.type eq 'qty' && $r.pos_qty < 0) || ($form.type neq 'qty' && $r.pos_val<0)}red{/if}">
		{if $form.type eq 'qty'}{$r.pos_qty|qty_nf|ifzero}{else}{$r.pos_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
		</td>
		<td align="right" class="{if ($form.type eq 'qty' && $r.gra_qty < 0) || ($form.type neq 'qty' && $r.gra_val<0)}red{/if}">
		{if $form.type eq 'qty'}{$r.gra_qty|qty_nf|ifzero}{else}{$r.gra_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
		</td>
		<td align="right" class="{if ($form.type eq 'qty' && $r.do_qty < 0) || ($form.type neq 'qty' && $r.do_val<0)}red{/if}">
		{if $form.type eq 'qty'}{$r.do_qty|qty_nf|ifzero}{else}{$r.do_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
		</td>
		<td align="right" class="{if ($form.type eq 'qty' && $r.adj_out < 0) || ($form.type neq 'qty' && $r.adj_out_val<0)}red{/if}">
		{if $form.type eq 'qty'}{$r.adj_out|qty_nf|ifzero}{else}{$r.adj_out_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
		</td>
		    {if $config.consignment_modules}
                <td align="right" class="{if ($form.type eq 'qty' && $r.cn_qty < 0) || ($form.type neq 'qty' && $r.cn_val<0)}red{/if}">
				{if $form.type eq 'qty'}{$r.cn_qty|qty_nf|ifzero}{else}{$r.cn_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
				</td>
                <td align="right" class="{if ($form.type eq 'qty' && $r.dn_qty < 0) || ($form.type neq 'qty' && $r.dn_val<0)}red{/if}">
				{if $form.type eq 'qty'}{$r.dn_qty|qty_nf|ifzero}{else}{$r.dn_val|number_format:$config.global_cost_decimal_points|ifzero}{/if}
				</td>
		    {/if}
		{if $sessioninfo.privilege.SHOW_REPORT_GP}
			<td align="right" class="{if $r.gp_val < 0}red{/if}">{$r.gp_val|number_format:2|ifzero}</td>
			<td align="right" class="{if $r.pos_qty < 0}red{/if}">{if $r.pos_qty}{$r.gp_per|number_format:2|ifzero:'':'%'}{/if}</td>
		{/if}
        <td align="right" class="{if $r.turnover < 0}red{/if}">{$r.turnover|qty_nf}</td>
		{if !$form.group_by_sku}
			<td align="right" {if $r.cost>$r.selling_price}class="below_cost"{/if}>
				<font {if $r.selling_price < 0} color="red" {/if}>
				{$r.selling_price|number_format:2|ifzero}
				</font>
				{if $branch_is_under_gst && $r.selling_price_before_gst}
					<br><h5 style="color: blue; white-space: nowrap">Excl.: {$r.selling_price_before_gst|number_format:2|ifzero}</h5>
				{/if}
			</td>
		{/if}
		<td align="right" class="{if $r.closing_bal < 0}red{/if}">{$r.closing_bal|qty_nf}</td>
		{if $sessioninfo.privilege.SHOW_COST}
			<td align="right" class="{if $r.closing_bal_val < 0}red{/if}">{$r.closing_bal_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}</td>
		{/if}
		<td align="right" class="{if $r.closing_bal_sel_val < 0}red{/if}">{$r.closing_bal_sel_val|number_format:2|ifzero:'-'}</td>
		<td align="right" class="{if $r.turnover_ratio < 0}red{/if}">{$r.turnover_ratio|number_format:2|ifzero:'0'}</td>
		<td align="right" class="{if $r.turnover_days < 0}red{/if}">{$r.turnover_days|number_format:2|ifzero:'0'}</td>
	</tr>

    {if $c%$item_per_page==0}
        {if $c eq count($table)}{$total_row}{/if}
		</table></div>
		<h3 align=right>Page {$pg++}</h3>
		<div style="page-break-before:always"></div>
		
		{assign var=put_header value=1}
	{/if}
{/foreach}

{if !$put_header}
    {if $c eq count($table)}{$total_row}{/if}
    </table></div>
    <h3 align=right>Page {$pg++}</h3>
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

    Calendar.setup({
        inputField     :    "added2",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
chk_filter();
//init_autocomplete();
</script>
{/literal}
{/if}
{include file=footer.tpl}
