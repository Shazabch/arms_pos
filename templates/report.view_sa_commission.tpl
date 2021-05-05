{*
4/20/2017 4:03 PM Khausalya
- Enhanced changes from RM to use onfig setting. 

06/29/2020 01:53 PM Sheila
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
var category_id = "{$smarty.request.category_id}";
var category = "{$smarty.request.category}";
var vendor_id = "{$smarty.request.vendor_id}";

{literal}
function toggle_filter(){
	var filter_by = document.f_a.filter_by.value;
	if(filter_by == 'sku'){
		$('filter_sku').show();
		$('filter_cb').hide();
	}else if (filter_by == 'dept_brand'){
		$('filter_sku').hide();
		$('filter_cb').show();
	}else{
		//if();
		$('filter_sku').hide();
		$('filter_cb').hide();
	}
}

function toggle_price_type(condition){
	var price_type_list = $('filter_cb').getElementsByClassName("price_type_list");
	price_type_count = price_type_list.length;
	var obj = $('cpt_toggle');

	if(price_type_count > 0){
		$A(price_type_list).each(
			function (r,idx){
				if(obj.checked == true && condition == true) r.checked = true;
				else r.checked = false;
			}
		);
	}
}
{/literal}
</script>

{/if}
<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class="err">
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}
{if !$no_header_footer}
<form name="f_a" method="post" class="form" action="{$smarty.server.PHP_SELF}" onSubmit="passArrayToInput();">

<p>
{if $BRANCH_CODE eq 'HQ'}
	<b>Branch</b>
	<select name="branch_id">
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
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
{/if}

<b>Date From</b> <input size="10" type="text" name="date_from" value="{$smarty.request.date_from}{$form.date_from}" id="date_from">
<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
<b>To</b>
<input size="10" type="text" name="date_to" value="{$smarty.request.date_to}{$form.date_to}" id="date_to">
<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>Filter By</b>
<select name="filter_by" onchange="toggle_filter();">
	<option value="">-- All --</option>
	<option value="sku" {if $smarty.request.filter_by eq 'sku'}selected{/if}>SKU</option>
	<option value="dept_brand" {if $smarty.request.filter_by eq 'dept_brand'}selected{/if}>Department + Brand</option>
</select>
&nbsp;&nbsp;&nbsp;&nbsp;
<b>Sales Agent</b>
<select name="sa_id">
   <option value="">-- All --</option>
    {foreach from=$sa item=sa}
        <option value="{$sa.id}" {if $smarty.request.sa_id eq $sa.id}selected {/if}>{$sa.code} - {$sa.name}</option>
    {/foreach}
</select>
</p>

<div id="filter_sku" {if $smarty.request.filter_by ne 'sku'}style="display:none;"{/if}>
	{include file="sku_items_autocomplete_multiple.tpl"}
</div>
<div id="filter_cb" {if $smarty.request.filter_by ne 'dept_brand'}style="display:none;"{/if}>
<p>
<b><u>Category + Brand Combinations (Either One)</u></b><br />
{include file="category_autocomplete.tpl" all=true}

<b>Brand</b>
<select name="brand_id">
	<option value="">-- All --</option>
	<option value="0" {if $smarty.request.brand_id eq '0'}selected{/if}>UNBRANDED</option>
	{foreach from=$brand_list item=b}
		<option value="{$b.id}" {if $smarty.request.brand_id eq $b.id}selected{/if}>{$b.description}</option>
	{/foreach}
</select>
</p>

<p>
<b><u>Additional Filter (Optional)</u></b><br />

<b>SKU Type</b>&nbsp;
<select name="sku_type">
	<option value="">-- All --</option>
	{foreach from=$sku_type_list item=st}
		<option value="{$st.code}" {if $smarty.request.sku_type eq $st.code}selected{/if}>{$st.code}</option>
	{/foreach}
</select><br />
<b>Price Type </b>&nbsp;
<input type="checkbox" style="margin-left:0;" id="cpt_toggle" onclick="toggle_price_type(true);"> All 
{foreach from=$price_type_list item=pt}
	{assign var=pt_code value=$pt.code}
	<input type="checkbox" style="margin-left:0;" name="price_type[{$pt_code}]" {if $smarty.request.price_type.$pt_code}checked{/if} class="price_type_list" /> {$pt_code}
{/foreach}<br />

<b>Vendor</b>&nbsp;
<select name="vendor_id" id="vendor_id">
    <option value="">-- All --</option>
    {foreach from=$vendor item=r}
        <option value="{$r.id}" {if $smarty.request.vendor_id eq $r.id}selected {/if}>{$r.description}</option>
    {/foreach}
</select>&nbsp;&nbsp;
</p>
</div>

<input type="hidden" name="subm" value="1">
<button class="btn btn-primary" name="a" value="show_report">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
<br>
</form>

{/if}
{if !$table}
{if $smarty.request.subm && !$err}<p align="center">-- No Data --</p>{/if}
{else}
<h2>{$report_title}</h2>
<table class="rpt_table" width=100% cellspacing=0 cellpadding=0>
	<tr class="header">
		<th width="2%">#</th>
		<th width="5%">Date Start</th>
		<th width="5%">Date End</th>
		<th width="30%">Condition</th>
		<th width="30%">Additional Filter</th>
		<th width="26%">Commission Method</th>
	</tr>
	<tbody>
	{foreach from=$table key=sac_id item=d name=t}
		<tr class="r" valign="top">
			<td align="left">{$smarty.foreach.t.iteration}.</td>
			<td align="center">{$d.date_from}</td>
			<td align="center">
				{if $d.date_to}
					{$d.date_to}
				{else}
					As present
				{/if}
			</td>
			<td align="left">
				{if $d.sku_item_id}
					<b>SKU: </b>{$d.sku_item_code} / {$d.artno|default:'-'} / {$d.description}
				{else}
					{if $d.cat_desc}
						<b>Category:</b> {$d.cat_desc}
					{/if}
					{if $d.brand_desc}
						{if $d.cat_desc}<br />+<br />{/if}
						<b>Brand:</b> {$d.brand_desc}
					{/if}
				{/if}
				&nbsp;
			</td>
			<td align="left">
				{if $d.sku_type}
				<b>SKU Type: </b>
					{$d.sku_type}
				{/if}
				{if $d.price_type}
					{if $d.sku_type}<br />+<br />{/if}
					<b>Price Type: </b>
					{$d.price_type}
				{/if}
				{if $d.vendor_desc}
					{if $d.sku_type || $d.price_type}<br />+<br />{/if}
					<b>Vendor: </b>
					{$d.vendor_desc}
				{/if}
				&nbsp;
			</td>
			<td align="left" nowrap>
				<b>Type: </b>{$d.commission_method}<br />
				{if $d.commission_method ne "Flat"}
					<table width="100%" border="1">
						<tr>
							<th bgcolor="#B6B6B6" width="65%" style="border-right:1px solid #000;">Range</th>
							<th bgcolor="#B6B6B6" width="35%" style="border-right:1px solid #000;">Value</th>
						</tr>
						{if $d.commission_method eq "Sales Range"}
							{assign var=amt_label value=$config.arms_currency.symbol}
						{else}
							{assign var=amt_label value=""}
						{/if}
						{foreach from=$d.commission_value item=cv key=r name=cv_list}
							<tr>
								<td bgcolor="#CACACA" style="border-right:1px solid #000;">
									{if $cv.range_from > 0 && $cv.range_to > 0}
										Between {$amt_label}{$cv.range_from} - {$amt_label}{$cv.range_to}
									{elseif $cv.range_from > 0 && $cv.range_to eq 0}
										Start from {$amt_label}{$cv.range_from}
									{elseif $cv.range_from eq 0 && $cv.range_to > 0}
										At most {$amt_label}{$cv.range_to}
									{/if}
								</td>
								<td align="right" bgcolor="#CACACA" style="border-right:1px solid #000;">{$cv.value}</td>
							</tr>
						{foreachelse}
							<tr id="cm_range_no_data_{$saci.id}">
								<td colspan="3" bgcolor="#CACACA" align="center" style="border-right:1px solid #000;">No Record</td>
							</tr>
						{/foreach}
					</table>
				{else}
					<b>Value: </b>{$d.commission_value|default:0}
				{/if}
			</td>
		</tr>
	{/foreach}
	</tbody>
</table>
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
	reset_sku_autocomplete();
</script>
{/literal}
{/if}

{include file=footer.tpl}

