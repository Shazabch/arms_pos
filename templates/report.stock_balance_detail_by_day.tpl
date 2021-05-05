{*
12/14/2015 1:57 PM DingRen
- add Include 0 Qty SKU filter

1/2/2018 3:59 PM Justin
- Bug fixed on the total does not show out the total qty for Stock Take Adjust.

3/5/2019 10:45 AM Andy
- Moved "Include 0 qty SKU" position.
- Changed search sku to use 'sku_items_autocomplete_multiple_add2.tpl'.
- Enhanced to have date range filter.

3/13/2019 11:55 AM Andy
- Enhanced to show * for item stock not up to date.
- Enhanced to have Stock Take Adjust.
- Fixed "No Data" cannot show.

7/19/2019 2:57 PM William
- Enhanced branch filter can filter by "All".

06/30/2020 02:42 PM Sheila
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

.clr_red {
	color:#FF0000;
}

.clr_blue {
	color:#306EFF;
}

.span_stock_not_update{
	color: red;
}
</style>
{/literal}
<script>
var phpself = "{$smarty.server.PHP_SELF}";
{literal}

var REPORTS = {
	f: undefined,
	initialise: function(){
		this.f = document.f_a;
		
		// sku autocomplete
		reset_sku_autocomplete();
		
		// calendar
		Calendar.setup({
			inputField     :    "inp_date_from",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_date_from",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
		});
		Calendar.setup({
			inputField     :    "inp_date_to",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_date_to",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
		});
	},
	// function to validate form
	validate_form(){
		if($('all_category').checked){
			if(!confirm('You have selected to show "All" Category, please take note the report may unable to load properly if there are too many sku.\n- Click OK to proceed.')){
				return false;
			}
		}
		return true;
	},
	// function when user click show report
	submit_form: function(t){
		this.f['output_excel'].value = '';
		if(t == 'excel')	this.f['output_excel'].value = 1;
		
		if(!this.validate_form())	return;
		
		for(var i=0; i<$('sku_code_list').length; i++){
			$('sku_code_list').options[i].selected = true;
		}
		
		this.f.submit();
	}
}

{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class="errmsg">
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}

{if !$no_header_footer}
<form method="post" class="form" name="f_a" onSubmit="return false;">
	<input type="hidden" name="load_report" value="1" />
	<input type="hidden" name="output_excel" />
<p>
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name="branch_id">
			<option value="">-- All --</option>
		    {foreach from=$branches key=bid item=b}
				{if !$branch_group.have_group.$bid}
					<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
				{/if}
		    {/foreach}
			
		    {if $branch_group.header}
				{foreach from=$branch_group.header key=bgid item=bg}
					<optgroup label="{$bg.code} - {$bg.description}">
						{foreach from=$branch_group.items.$bgid key=bid item=b}
							<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
						{/foreach}
					</optgroup>
				{/foreach}
			{/if}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
	
	<b>Date From</b> <input size="10" type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date From" />&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>To</b> <input size="10" type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date To" />&nbsp;&nbsp;&nbsp;&nbsp;

	
	<!--&nbsp;&nbsp;&nbsp;&nbsp;
	<b>SKU Group</b>&nbsp;
	<select name="sku_group_id">
	   <option value="">Please Select</option>
		{foreach from=$sku_group item=sg}
			<option value="{$sg.sku_group_id}" {if $smarty.request.sku_group_id eq $sg.sku_group_id}selected{/if}>{$sg.code} - {$sg.description}</option>
		{/foreach}
	</select>-->
</p>
<p>
{include file="category_autocomplete.tpl" all=true}
</p>
<p>
{include file='sku_items_autocomplete_multiple_add2.tpl'}
</p>

<input type="checkbox" name="include_0_sku" value="1" {if $smarty.request.include_0_sku}checked{/if} id="include_0_sku"><label for="include_0_sku"><b>Include 0 Qty SKU</b></label>

<!--p>
<b>
Note:<br />
- Stock Opening/Closing having <font color="red" size="4">*</font> means it is not up to date.
</b>
</p-->
	<br />
	Item mark with <span class="span_stock_not_update">*</span> indicate the stock balance is not up to date.
<p>

<button class="btn btn-primary" onClick="REPORTS.submit_form();">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
	<button class="btn btn-primary" onClick="REPORTS.submit_form('excel');">{#OUTPUT_EXCEL#}</button>
{/if}
</p>
</form>
{/if}

{if !$table}
{if $smarty.request.load_report && !$err}<p align="center">-- No data --</p>{/if}
{else}
	<h2>{$report_title}</h2>
	<table class="rpt_table" width="100%" cellspacing="0" cellpadding="0" id="report_tbl">
		<tr class="header">
			<th rowspan="2">SKU Item Code</th>
			<th rowspan="2">MCode</th>
			<th rowspan="2">Artno</th>
			<th width="40%" rowspan="2">Description</th>
			<th {if !$got_opening_sc}rowspan="2"{else}colspan="2"{/if}>Opening Balance</th>
			{if $got_sc_adj}
				<th {if $smarty.request.branch_id neq ''}colspan="3"{else}colspan="1"{/if}>Stock Take</th>
			{/if}
			<th rowspan="2">GRN</th>
			<th rowspan="2">GRA</th>
			<th rowspan="2">POS</th>
			<th rowspan="2">DO</th>
			<th colspan="2">ADJ</th>
			<th rowspan="2">Closing Balance</th>
		</tr>
		<tr class="header">
			{if $got_opening_sc}
				<th>Stock Take Adjust</th>
				<th>Qty</th>
			{/if}
			{if $got_sc_adj}
				<th>Adj Qty</th>
				{if $smarty.request.branch_id neq ''}
				<th>Date</th>
				<th>Qty</th>
				{/if}
			{/if}
			<th>In</th>
			<th>Out</th>
		</tr>
		<tbody class="r">
			{foreach from=$table item=item key=sid}
				<tr>
					<td align="center" nowrap>{$item.sku_item_code}
						{if $item.changed}
							<span class="span_stock_not_update">*</span>
						{/if}
					</td>
					<td align="left">{$item.mcode|default:"&nbsp;"}</td>
					<td align="left">{$item.artno|replace:' ':'&nbsp;'|default:"&nbsp;"}</td>
					<td align="left">{$item.description}</td>
					{if $got_opening_sc}
						<td>{$item.sc_adj_qty}</td>
						{assign var=ttl_sc_adj_qty value=$ttl_sc_adj_qty+$item.sc_adj_qty}
					{/if}
					
					<td>{$item.ob_qty|default:0|qty_nf}</td>
					
					{if $got_sc_adj}
						<td class="{if $item.sc_adj_qty2 gt 0}positive_value{elseif $item.sc_adj_qty2 lt 0}negative_value{/if}">
							{if $item.sc_adj_qty2 gt 0}+{elseif $item.sc_adj_qty2 lt 0}-{/if}{$item.sc_adj_qty2|qty_nf}
						</td>
						{if $smarty.request.branch_id neq ''}
						<td>{$item.sc_date}</td>
						<td>{$item.sc_qty|qty_nf}</td>
						{/if}
					{/if}
					
					<td>{$item.grn_qty|default:0|qty_nf}</td>
					<td>{$item.gra_qty|default:0|qty_nf}</td>
					<td>{$item.pos_qty|default:0|qty_nf}</td>
					<td>{$item.do_qty|default:0|qty_nf}</td>
					<td>{$item.adj_in_qty|default:0|qty_nf}</td>
					<td>{$item.adj_out_qty|default:0|qty_nf}</td>
					<td>{$item.cb_qty|default:0|qty_nf}</td>
				</tr>
				{assign var=ttl_ob_qty value=$ttl_ob_qty+$item.ob_qty}
				{assign var=ttl_grn_qty value=$ttl_grn_qty+$item.grn_qty}
				{assign var=ttl_gra_qty value=$ttl_gra_qty+$item.gra_qty}
				{assign var=ttl_pos_qty value=$ttl_pos_qty+$item.pos_qty}
				{assign var=ttl_do_qty value=$ttl_do_qty+$item.do_qty}
				{assign var=ttl_adj_in_qty value=$ttl_adj_in_qty+$item.adj_in_qty}
				{assign var=ttl_adj_out_qty value=$ttl_adj_out_qty+$item.adj_out_qty}
				{assign var=ttl_cb_qty value=$ttl_cb_qty+$item.cb_qty}
				{assign var=ttl_adj_qty value=$ttl_adj_qty+$item.sc_adj_qty2}
			{/foreach}
		</tbody>
		<tr class="header" align="right">
			<th colspan="4">Total</th>
			{if $got_opening_sc}
				<th>{$ttl_sc_adj_qty|qty_nf}</th>
			{/if}
			<th>{$ttl_ob_qty|default:0|qty_nf}</th>
			{if $got_sc_adj}
				<th>{$ttl_adj_qty|default:0|qty_nf}</th>
				{if $smarty.request.branch_id neq ''}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				{/if}
			{/if}
			<th>{$ttl_grn_qty|default:0|qty_nf}</th>
			<th>{$ttl_gra_qty|default:0|qty_nf}</th>
			<th>{$ttl_pos_qty|default:0|qty_nf}</th>
			<th>{$ttl_do_qty|default:0|qty_nf}</th>
			<th>{$ttl_adj_in_qty|default:0|qty_nf}</th>
			<th>{$ttl_adj_out_qty|default:0|qty_nf}</th>
			<th>{$ttl_cb_qty|default:0|qty_nf}</th>
		</tr>
	</table>
{/if}

{if !$no_header_footer}
{literal}
<script type="text/javascript">
	REPORTS.initialise();
</script>
{/literal}
{/if}
{include file=footer.tpl}
