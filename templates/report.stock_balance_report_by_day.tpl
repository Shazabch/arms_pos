{*
5/6/2010 3:07:28 PM Alex
- Add HQ Cost and Show Cost privilege

2/23/2011 5:56:58 PM Andy
- Remove branch dropdown when user is login at branches.

3/2/2011 11:34:21 AM Andy
- Fix word mistake.

5/25/2011 12:07:08 PM Alex
- change "date from" as "date"

7/7/2011 10:53:55 AM Alex
- add print button

8/5/2011 10:35:29 AM Alex
- add selling price column

8/11/2011 6:28:21 PM Alex
- Change number_format to num_format for qty

8/15/2011 11:33:21 AM Justin
- Added filter "Blocked Item in PO" in stock balance by department report.
- Added filter "Status" for SKU.

10/14/2011 4:44:12 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

4/12/2012 5:10:21 PM Andy
- Change report default select filter active SKU.

4/18/2012 5:03:15 PM Andy
- Change report form submit method to "post".

2/4/2013 5:34 PM Justin
- Enhanced to show and filter branches from regions or branch group base on user's regions.

07/01/2016 14:00 Edwin
- Show 'HQ Cost' when branch is HQ and $config.sku_listing_show_hq_cost is enabled
*}

{include file=header.tpl}

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
<script>
function do_print(){
	window.print();
}

function chkempty()
{
   if(document.f_d.from.value=="")
   {
      alert("Please Select Date");
      return false;
   }
     
   else if((document.f_d.category_id.value=="") && (document.f_d.all_cat.checked !==true))
   {
      alert("Please Select Category");
      return false;
   }
}

function submit_xls()
{
    //document.f_d.a.value="export_excel";
    document.f_d.v.value="branch_stock_balance";
    document.f_d.f.value="Stock_Balance_Report_by_Day";
    document.f_d.fn.value="report.stock_balance_report_by_day";
    //alert(document.f_d.a.value);
    document.f_d.submit();
    //document.f_d.a.value="stock_balance";
  //alert(document.f_d.a.value);
    ///document.ff_table.tocsv.value=0;
}

var category_autocompleter = null;
function show_child(id)
{
	// reactivate the auto-completer with child of the category
	setTimeout('category_autocompleter.options.defaultParams = "child='+id+'";category_autocompleter.activate()',250);
}
function init_autocomplete()
{
	category_autocompleter = new Ajax.Autocompleter("autocomplete_category2", "autocomplete_category_choices", "ajax_autocomplete.php?a=ajax_search_category&min_level=1", {
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

function show_sort(s)
{
    if(s == "0")
    {
        $('sorting').hide();
    }else
    {
        $('sorting').show();
    }
}

</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>
<form name="f_d" class="form" action="report.stock_balance_report_by_day.php" onSubmit="return chkempty()" method="post">
<input type=hidden name=report_title value="{$report_title}">
<input type=hidden name=title value="Stock Balance Report by Day">
<input type=hidden name=a value="stock_balance">
<input type=hidden name=f>
<input type=hidden name=v>
<input type=hidden name=fn>

{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b>
<select name="branch_id">
	<option value="all" {if $smarty.request.branch_id eq 'all'}selected{/if}>-- All --</option>
	{foreach from=$branches key=bid item=b}
	    {if !$branches_group.have_group.$bid}
	    	<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
		{/if}
	{/foreach}
	{if $branches_group.header}
		<optgroup label="Branches Group">
			{foreach from=$branches_group.header key=bgid item=bg}
				<option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
				{foreach from=$branches_group.items.$bgid item=r}
					<option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
				{/foreach}

			{/foreach}
		</optgroup>
	{/if}
	{if $config.consignment_modules && $config.masterfile_branch_region}
		<optgroup label='Region'>
		{foreach from=$config.masterfile_branch_region key=type item=f}
			{if ($sessioninfo.regions && $sessioninfo.regions.$type) || !$sessioninfo.regions}
				{assign var=curr_type value="REGION_`$type`"}
				<option value="REGION_{$type}" {if $smarty.request.branch_id eq $curr_type}selected {/if}>{$type|upper}</option>
			{/if}
		{/foreach}
		</optgroup>
	{/if}
</select>
&nbsp;&nbsp;
{/if}

<span id=sorting {if $is_branch}style="display:none"{/if}>
<b>Sort By</b>
<select name=sorting>
<option value="all" {if $smarty.request.sorting eq 'all'}selected {/if}>-- Please Select --</option>
<option value="sku" {if $smarty.request.sorting eq 'sku'}selected {/if}>ARMS Code</option>
<option value="sku_artno" {if $smarty.request.sorting eq 'sku_artno'}selected {/if}>ART No</option>
<option value="sku_mcode" {if $smarty.request.sorting eq 'sku_mcode'}selected {/if}>MCode</option>
<option value="sku_desc" {if $smarty.request.sorting eq 'sku_desc'}selected {/if}>Description</option>
</select>
</span> &nbsp;&nbsp;

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

{*include file='category_autocomplete.tpl' all=true*}

<b>Category</b>
<input type=checkbox id=all_cat name=all_cat {if $smarty.request.all_cat}checked{/if} onchange="showdiv('sel_cat', this.checked)"> <label for=all_cat><b>All</b></label>
<span id=sel_cat {if $smarty.request.all_cat}style="display:none"{/if}>
<input readonly name=category_id size=1 value="{$smarty.request.category_id}">
<input type=hidden name=category_tree value="{$smarty.request.category_tree}">
<input id=autocomplete_category2 name=category value="{$smarty.request.category|default:'Enter keyword to search'}" onfocus=this.select() size=50><br><span id=str_cat_tree class=small style="color:#00f;margin-left:90px;">{$smarty.request.category_tree|default:''}</span>
</span>
<div id=autocomplete_category_choices class=autocomplete style="width:600px !important;display:none"></div>

</p>
<b>SKU Type</b>
<select name=sku_type>
<option value="all" {if $smarty.request.sku_type eq 'all'}selected{/if}>All</option>
{foreach from=$sku_type item=r}
<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected {/if}>{$r.description}</option>
{/foreach}
</select> &nbsp;&nbsp;
<input id="hide_zero_id" type="checkbox" name=zero {if $smarty.request.zero eq 'on'}checked{/if}>
<label for="hide_zero_id"><b>Hide Zero Quantity</b></label>
&nbsp;&nbsp;
{if $config.sku_listing_show_hq_cost and $sessioninfo.privilege.SHOW_COST and $BRANCH_CODE eq 'HQ'}
<input type=checkbox id=hq_cost name=hq_cost {if $smarty.request.hq_cost eq 'on'}checked{/if}>
<label for="hq_cost"><b>HQ Cost</b></label>
&nbsp;&nbsp;{/if}
</p>
<b>Date</b>
<input type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"> 
<input id="rpt_type_1_id" type="radio" name="rpt_type"  onclick="show_sort('1')" value="sku" {if not $smarty.request.rpt_type}checked {else}{if $smarty.request.rpt_type eq 'sku'}checked{/if}{/if}><label for="rpt_type_1_id">View By SKU</label>
<input id="rpt_type_2_id" type="radio" name="rpt_type"  onclick="show_sort('0')" value="branch" {if $smarty.request.rpt_type eq 'branch'}checked {/if}><label for="rpt_type_2_id">View By Branch</label>
<input type=submit value="{#SHOW_REPORT#}">
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button name=export_xls onclick="submit_xls()">{#OUTPUT_EXCEL#}</button>
{/if}
<input type=button onclick="do_print()" value="Print">

</form>

</p>
<!-- start -->
<h2>
{if $data}
{$report_title}

{*Date: {$smarty.request.from} &nbsp;&nbsp; Category: {if $smarty.request.all_cat}All{else}{$smarty.request.category}{/if}&nbsp;&nbsp; {if $smarty.request.branch_id < 0}Branch Group: {$br|default:"All"}{else}Branch: {$br|default:"All"}{/if}*}</h2>
<table class="report_table small_printing">
<tr class="header">
{if $is_branch}
  <th rowspan=2>
    Branch 
  </th>
  <th width=100% rowspan=2>
    Description 
  </th>
{else}
 <th rowspan=2>
    Arms Code 
  </th>
  <th rowspan=2>
    MCode 
  </th>
  <th rowspan=2>
    ArtNo
  </th>
  <th width="100%" rowspan=2>
    Description
  </th>
{/if}
	{assign var=default_col value=1}
	{if $config.stock_balance_report_show_additional_selling}
		{assign var=default_col value=$default_col+2}
	{/if}
	{if $sessioninfo.privilege.SHOW_COST}
		{if not $is_branch}
			{assign var=default_col value=$default_col+2}
		{else}
			{assign var=default_col value=$default_col+1}
		{/if}
	{/if}

  <th nowrap colspan="{$default_col}">
    Opening Stock</th>
  <th nowrap colspan="{$default_col}">
    Closing Stock
  </th>
</tr>
<tr class="header">
	{if $sessioninfo.privilege.SHOW_COST}
	{if not $is_branch}<th nowrap>Unit Cost</th>{/if}{/if}
    {if $config.stock_balance_report_show_additional_selling}
    	{if not $is_branch}
  		<th nowrap>Unit Price</th>
		{/if}
	{/if}

	<th nowrap>Quantity</th>
	{if $sessioninfo.privilege.SHOW_COST}<th nowrap>Balance (RM)</th>{/if}
	{if $config.stock_balance_report_show_additional_selling}<th nowrap>Selling (RM)</th>{/if}
 	{if $sessioninfo.privilege.SHOW_COST}{if not $is_branch}<th nowrap>Unit Cost</th>{/if}{/if}
	{if $config.stock_balance_report_show_additional_selling}
		{if not $is_branch}
  		<th nowrap>Unit Price</th>
		{/if}
	{/if}
 	<th nowrap>Quantity</th>
	{if $sessioninfo.privilege.SHOW_COST}<th nowrap>Balance (RM)</th>{/if}
	{if $config.stock_balance_report_show_additional_selling}<th nowrap>Selling (RM)</th>{/if}
</tr>
{assign var=qty_total_open value=0}
{assign var=closing_total_open value=0}

{assign var=qty_total_close value=0}
{assign var=closing_total_close value=0}

{assign var=st_closing_open value=0}
{assign var=st_closing_close value=0}

{foreach from=$data key=branch item=r}
{foreach from=$data.$branch key=s item=rr}
{if !$smarty.request.zero || $rr.qty.open!=0 || $rr.qty.close!=0}
      <tr>
      {if $is_branch}
      <td>{$s}</td>
      <td>{$rr.description}</td>
      {else}
      <td>{$s}</td>
      <td>{$rr.mcode}</td>
      <td>{$rr.artno|replace:' ':'&nbsp;'}</td>
      <td>{$rr.description}</td>
      {/if}
      {if $sessioninfo.privilege.SHOW_COST}
      	{if not $is_branch}
        <td align=right>{$rr.grn_cost.open|number_format:4}</td>
      	{/if}
	  {/if}
      {if $config.stock_balance_report_show_additional_selling}
      	{if not $is_branch}
	  	<td align=right>{$rr.unit_selling.open|number_format:4}</td>
		{/if}
	  {/if}
      
      <td align=right nowrap>{$rr.qty.open|qty_nf}</td>
      {assign var=closing_open value=$rr.grn_cost.open*$rr.qty.open}
      <!--<td align=right>{$rr.closing.open|number_format:2}</td>-->
      
      {if $sessioninfo.privilege.SHOW_COST}
 	    <td align=right>{$rr.closing.open|number_format:2}</td>
	  {/if}
      {if $config.stock_balance_report_show_additional_selling}
		<td align=right>{$rr.closing.open_selling|number_format:2}</td>
      {/if}
      
      {if $sessioninfo.privilege.SHOW_COST}
      {if not $is_branch}
        <td align=right>{$rr.grn_cost.close|number_format:4}</td>
      {/if}{/if}
      {if $config.stock_balance_report_show_additional_selling}
      	{if not $is_branch}
	  	<td align=right>{$rr.unit_selling.close|number_format:4}</td>
		{/if}
	  {/if} 
 
      <td align=right>{$rr.qty.close|qty_nf}</td>
      
      {assign var=closing_close value=$rr.grn_cost.close*$rr.qty.close}
      <!--<td align=right>{$rr.closing.close|number_format:2}</td>-->
      
      {if $sessioninfo.privilege.SHOW_COST}
        <td align=right>{$rr.closing.close|number_format:2}</td>
      {/if}
      {if $config.stock_balance_report_show_additional_selling}
		<td align=right>{$rr.closing.close_selling|number_format:2}</td>
      {/if}      
      </tr>
      
      {assign var=qty_total_open value=$qty_total_open+$rr.qty.open}
      {assign var=closing_total_open value=$closing_total_open+$rr.closing.open}
      {assign var=closing_total_open_selling value=$closing_total_open_selling+$rr.closing.open_selling}
      {assign var=qty_total_close value=$qty_total_close+$rr.qty.close}
      {assign var=closing_total_close value=$closing_total_close+$rr.closing.close}
      {assign var=closing_total_close_selling value=$closing_total_close_selling+$rr.closing.close_selling}
{/if}
{/foreach}
{/foreach}
<tr class="header">
	<td colspan={if $is_branch}2{else}4{/if}><b>Total</b></td>
	{if $sessioninfo.privilege.SHOW_COST}{if not $is_branch}<td>&nbsp;</td>{/if}{/if}
	{if $config.stock_balance_report_show_additional_selling}
		{if not $is_branch}
  		<td nowrap>&nbsp;</td>
		{/if}
	{/if}
	
	<td align=right><b>{$qty_total_open|qty_nf}</b></td>
	{if $sessioninfo.privilege.SHOW_COST}<td align=right><b>{$closing_total_open|number_format:2}</b></td>{/if}
	{if $config.stock_balance_report_show_additional_selling}<td align=right><b>{$closing_total_open_selling|number_format:2}</b></td>{/if}
	{if $sessioninfo.privilege.SHOW_COST}{if not $is_branch}<td>&nbsp;</td>{/if}{/if}
	{if $config.stock_balance_report_show_additional_selling}
		{if not $is_branch}
  		<td nowrap>&nbsp;</td>
		{/if}
	{/if}

	<td align=right><b>{$qty_total_close|qty_nf}</b></td>
	{if $sessioninfo.privilege.SHOW_COST}<td align=right><b>{$closing_total_close|number_format:2}</b></td>{/if}
	{if $config.stock_balance_report_show_additional_selling}<td align=right><b>{$closing_total_close_selling|number_format:2}</b></td>{/if}
</tr>
</table>
 <!-- end -->
 {else}
 	{if $table}No Data{/if}
{/if}

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
//chk_filter();
init_autocomplete();
</script>
{/literal}

{include file=footer.tpl}