{*
12/23/2013 2:00 PM Andy
- Add can sort by category.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

8/18/2014 2:11 PM Justin
- Enhanced to add privilege checking for export excel.

10/13/2014 6:03 PM Justin
- Bug fixed on branch filter able to filter at sub branch.

12/14/2015 3:46 PM DingRen
add group by parent & child

6/6/2017 11:40 AM Justin
- Enhanced to have sku filter.
- Re-arranged the filter options.
*}

{include file='header.tpl'}

{if !$no_header_footer}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var HOURLY_SALES_REPORT = {
	f: undefined,
	initialize: function(){
		var THIS = this;
		this.f = document.f_a;
	},
	toggle_show_by_date: function(show){
		if (show) $('show_by_date_type').show();
		else $('show_by_date_type').hide();
	},
	
	submit_report: function(t){
		this.f['output_excel'].value = 0;
		
		if(t == 'excel'){
			this.f['output_excel'].value = 1;
		}
		
		// select all sku code
		toggle_select_all_opt(this.f['sku_code_list[]'], true);
		this.f.submit();
	},
}
{/literal}
</script>

{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

{if !$no_header_footer}
<div class="noprint stdframe">
<form name="f_a" method="post" onSubmit="return false;">
<input type="hidden" name="show_report" value="1" />
<input type="hidden" name="output_excel" />

{if $BRANCH_CODE eq "HQ"}
<b>Branch :</b>
	<select name="branch_id">
		<option value="0">-- All --</option>
		{foreach from=$branch_arr key=k item=i}
		<option value="{$k}" {if $k eq $form.branch_id}selected{/if}>{$i.code}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;
{else}
	<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
{/if}

<b>Date From :</b>
<input type="text" name="date_from" id="date_from" size="10" value="{$form.date_from}" />
<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date From" />
&nbsp;&nbsp;&nbsp;

<b>Date To :</b>
<input type="text" name="date_to" id="date_to" size="10" value="{$form.date_to}" />
<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date To" />

<br /><br />

<b>Vendor :</b>
<select name="vendor_id">
	<option value="0">-- All --</option>
	{foreach from=$vendor_arr key=k item=i}
	<option value="{$k}" {if $k eq $form.vendor_id}selected{/if}>{$i.description}</option>
	{/foreach}
</select>
&nbsp;&nbsp;&nbsp;

<b>Brand :</b>
<select name="brand_id">
	<option value="">-- All --</option>
	<option value="0" {if $smarty.request.brand_id eq '0'}selected {/if}>UNBRANDED</option>
	{if $brand_groups}
	<optgroup label="Brand Group">
	{foreach from=$brand_groups key=k item=i}
	<option value="{$k}" {if $k eq $form.brand_id}selected{/if}>{$i}</option>
	{/foreach}
	</optgroup>
	{/if}
	{if $brand_arr}
	<optgroup label="Brand">
	{foreach from=$brand_arr key=k item=i}
	<option value="{$k}" {if $k eq $form.brand_id}selected{/if}>{$i.description}</option>
	{/foreach}
	</optgroup>
	{/if}
</select>
&nbsp;&nbsp;&nbsp;

<p>
{include file="category_autocomplete.tpl" all=true}
</p>

<p>
{include file="sku_items_autocomplete_multiple_add2.tpl"}
</p>

<br /><br />
<label><input type="checkbox" name="group_by_parent_child" value="1" {if $smarty.request.group_by_parent_child}checked{/if} /><b>Group by parent & child</b></label>&nbsp;&nbsp;&nbsp;
<b>Sort By :</b>
<select name="sort_by">
	<option value="sku_item_code" {if $form.sort_by eq 'sku_item_code'}selected{/if}>ARMS Code</option>
	<option value="mcode" {if $form.sort_by eq 'mcode'}selected{/if}>MCode</option>
	<option value="link_code" {if $form.sort_by eq 'link_code'}selected{/if}>Old Code</option>
	<option value="description" {if $form.sort_by eq 'description'}selected{/if}>Description</option>
	<option value="artno" {if $form.sort_by eq 'artno'}selected{/if}>Art No</option>
	<option value="category" {if $form.sort_by eq 'category'}selected{/if}>Category</option>
</select>
&nbsp;&nbsp;&nbsp;

<label><input type="checkbox" name="show_by_date" value="1" {if $form.show_by_date}checked{/if} onclick="HOURLY_SALES_REPORT.toggle_show_by_date(this.checked);" /><b>Show by Date</b></label>
&nbsp;
<span id="show_by_date_type" style="display:{if !$smarty.request.show_by_date}none{/if};">
<label><input type="radio" value="qty" name="show_type" {if $form.show_type eq 'qty'}checked{/if}>Sales Qty</label>
<label><input type="radio" value="amt" name="show_type" {if $form.show_type eq 'amt'}checked{/if}>Sales Amount</label>
</span>
&nbsp;&nbsp;&nbsp;

<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>Exclude inactive SKU</b></label>
&nbsp;&nbsp;&nbsp;

<button onClick="HOURLY_SALES_REPORT.submit_report();">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button onClick="HOURLY_SALES_REPORT.submit_report('excel');">{#OUTPUT_EXCEL#}</button>
{/if}
</form>
</div>
<br />
{/if}

{if $data}
	<h3>{$report_title}</h3>
	<table class="report_table small_printing" width="100%">
		<thead>
			<tr class="header">
				<th align="center">ARMS Code</th>
				<th align="center">Mcode</th>
				<th align="center">Old Code</th>
				<th align="center">Art No</th>
				<th align="center">Description</th>
				<th align="center">Category</th>
				<th align="center">Vendor</th>
				<th align="center">Brand</th>
				
				{if $form.show_by_date}
					{assign var=first value=1}
					{foreach from=$day_arr item=d}
					<th align="center">
						{if $first or $d[2] == '01'}
						<span class="small">{$d[1]}/{$d[0]}</span><br />{$d[2]}<br />
						{else}
						{$d[2]}
						{/if}
					</th>
					{assign var=first value=0}
					{/foreach}
				{/if}
				
				<th align="center">Qty</th>
				<th align="center">Amt</th>
			</tr>
		</thead>
		
		{foreach from=$sku_item_arr key=s_key item=s}
		<tr>
			<td style="white-space:nowrap;">{$s.sku_item_code}</td>
			<td style="white-space:nowrap;">{$s.mcode}</td>
			<td style="white-space:nowrap;">{$s.link_code}</td>
			<td style="white-space:nowrap;">{$s.artno}</td>
			<td style="white-space:nowrap;">{$s.description|upper}</td>
			<td style="white-space:nowrap;">{$s.category|upper}</td>
			<td style="white-space:nowrap;">{$vendor_arr[$s.vendor_id].description|upper}</td>
			<td style="white-space:nowrap;">{$brand_arr[$s.brand_id].description|upper}</td>
			
			{if $form.show_by_date}
			{foreach from=$day_arr item=d}
			{assign var=tmpkey value="`$d.0`-`$d.1`-`$d.2`"}
			{if $smarty.request.show_type eq 'qty'}
			<td align="right">{$data.$s_key.$tmpkey.qty|default:'&nbsp;'}</td>
			{else}
			<td align="right">{$data.$s_key.$tmpkey.amt|number_format:2|ifzero:'&nbsp;'}</td>
			{/if}
			{/foreach}
			{/if}
			
			<td align="right">{$data.$s_key.qty}</td>
			<td align="right">{$data.$s_key.amt|number_format:2}</td>
		</tr>
		{/foreach}
		
		<tr class="header">
			<th colspan="8" align="right">Total</th>
			
			{if $form.show_by_date}
			{foreach from=$day_arr item=d}
			{assign var=tmpkey value="`$d.0`-`$d.1`-`$d.2`"}
			{if $smarty.request.show_type eq 'qty'}
			<th align="right">{$day_qty_total.$tmpkey|default:'&nbsp;'}</td>
			{else}
			<th align="right">{$day_amt_total.$tmpkey|number_format:2|ifzero:'&nbsp;'}</td>
			{/if}
			{/foreach}
			{/if}
			
			<th align="right">{$qty_total}</th>
			<th align="right">{$amt_total|number_format:2}</th>
		</tr>
	</table>
{elseif $form.show_report}
	<ul>
		<li>No data</li>
	</ul>
{/if}

{include file='footer.tpl'}

<script type="text/javascript">
{literal}
HOURLY_SALES_REPORT.initialize();

Calendar.setup({
	inputField	:	"date_from",		// id of the input field
	ifFormat	:	"%Y-%m-%d",			// format of the input field
	button		:	"img_date_from",	// trigger for the calendar (button ID)
	align		:	"Bl",				// alignment (defaults to "Bl")
	singleClick	:	true
});

Calendar.setup({
	inputField	:	"date_to",			// id of the input field
	ifFormat	:	"%Y-%m-%d",			// format of the input field
	button		:	"img_date_to",		// trigger for the calendar (button ID)
	align		:	"Bl",				// alignment (defaults to "Bl")
	singleClick	:	true
});
{/literal}
</script>
