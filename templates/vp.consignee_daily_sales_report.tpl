{*
10/8/2012 5:19 PM Andy
- Add legend to show SCC format.

10/22/2012 2:45 PM Andy
- Add email feature.
- Add can generate report by date range, but without monthly bonus.

11/15/2012 2:14 PM Andy
- Add show other % icon if found got more then 1 % use in same day.
- Add show other bonus % icon if found got other category bonus %.

1/28/2013 10:27 AM Andy
- Modified to show different % used by category or sku.

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise" and "License" to "Licence".
*}

{include file="header.tpl"}

{if $is_email}
<style>
{literal}
.r{
	text-align: right;
}
{/literal}
</style>
{/if}

{if !$no_header_footer}
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
{literal}
.negative{
	font-weight: bold;
	color: red;
}
.tr_date_total th, .tr_date_total td{
	background-color: #ccf;
}
{/literal}
</style>

<script type="text/javascript">

{literal}
var CONSIGNEE_REPORT = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
	},
	// function to validate form before submit
	check_form: function(){
				
		return true;
	},
	// function when user click show report
	submit_form: function(type){
		if(!this.check_form())	return false;
		
		this.f['submit_type'].value = '';
		if(type == 'excel')	this.f['submit_type'].value = type;
		
		this.f.submit();
	},
	// function when user click print
	print_form: function(){
		window.print();
	}
};

{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
	<div><div class="errmsg"><ul>
	{foreach from=$err item=e}
		<li> {$e}</li>
	{/foreach}
	</ul></div></div>
{/if}

{if !$no_header_footer}
<form name="f_a" method="post" class="stdframe noprint">
	<input type="hidden" name="load_report" value="1" />
	<input type="hidden" name="submit_type" value="" />
	
	<b>Year</b>	
	<select name="year">
		{foreach from=$year_list item=y}
			<option value="{$y}" {if $smarty.request.year eq $y}selected {/if}>{$y}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Month</b>
	<select name="month">
		{foreach from=$month_list key=m item=m_label}
			<option value="{$m}" {if $smarty.request.month eq $m}selected {/if}>{$m_label}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<input type="button" value="Show Report" onClick="CONSIGNEE_REPORT.submit_form();" />
	<input type="button" value="Print" onClick="CONSIGNEE_REPORT.print_form();" />
	<button onClick="CONSIGNEE_REPORT.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	
	<br />
	<ul>
		<li> This report only show finalised sales.</li>
		<li> SCC Format: branch_id, vendor_code, order_id (can be blank), date(YYYY-MM-DD), amount, receipt id (can be blank), receipt ref (can be blank)</li>
	</ul>
</form>

<script type="text/javascript">
	CONSIGNEE_REPORT.initialize();
</script>
{/if}

{if $smarty.request.load_report and !$err}
	<br />
	{if !$data}
	 * No Data
	{else}
		<h3>{$report_title}</h3>
		
		<table class="report_table" cellpadding="0" cellspacing="0" {if $is_email}border="1"{/if}>
			<tr class="header">
				<th>Licence Fee %</th>
				<th>Date</th>
				<th>Sales Type</th>
				<th>Licencee Sales</th>
				<th>Licencee Fee Amount</th>
				<th>Licencee Nett Sales</th>
			</tr>
			
			{foreach from=$data.data key=date item=r}
				<tr>
					<td rowspan="3">{$r.discount_rate}
						{if $r.other_discount_rate and !$no_header_footer}
							{capture assign=str_alert}{strip}
								Other % used in this date\n
								=========================\n
								{foreach from=$r.other_discount_rate key=type item=type_list}
									{foreach from=$type_list key=type_v item=other_discount_rate}
										{if $type eq 'SKU'}
											SKU: {$vp_global_si_info_list.$type_v.sku_item_code} ({$other_discount_rate})\n
										{elseif $type eq 'CATEGORY'}
											CATEGORY: {$vp_global_cat_info_list.$type_v.description} ({$other_discount_rate})\n
										{else}
											{$other_discount_rate}\n
										{/if}
									{/foreach}
								{/foreach}
							{/strip}{/capture}
							<img src="/ui/icons/information.png" align="absmiddle" class="clickable" title="Information" onClick="alert('{$str_alert}');" />
						{/if}
					</td>
					<td rowspan="3">{$date}</td>
					<td>SALES TO SCC</td>
					
					{* Licensee Sales *}
					<td class="r">{$r.scc.amt|number_format:2}</td>
					
					{* Licensee Fee Amount *}
					<td class="r">{$r.scc.disc_amt|number_format:$config.global_cost_decimal_points}</td>
					
					{* Licensee Nett Sales *}
					<td class="r">{$r.scc.cost|number_format:$config.global_cost_decimal_points}</td>
				</tr>
				<tr>
					<td>STORE POS SALES</td>
					
					{* Licensee Sales *}
					<td class="r">{$r.pos.amt|number_format:2}</td>
					
					{* Licensee Fee Amount *}
					<td class="r">{$r.pos.disc_amt|number_format:$config.global_cost_decimal_points}</td>
					
					{* Licensee Nett Sales *}
					<td class="r">{$r.pos.cost|number_format:$config.global_cost_decimal_points}</td>
				</tr>
				<tr class="tr_date_total">
					<th class="r">Total</th>
					
					{* Licensee Sales *}
					<td class="r"><b>{$r.total.amt|number_format:2}</b></td>
					
					{* Licensee Fee Amount *}
					<td class="r"><b>{$r.total.disc_amt|number_format:$config.global_cost_decimal_points}</b></td>
					
					{* Licensee Nett Sales *}
					<td class="r"><b>{$r.total.cost|number_format:$config.global_cost_decimal_points}</b></td>
				</tr>
			{/foreach}
			<tr class="header">
				<th class="r" colspan="3">Total</th>
				
				{* Licensee Sales *}
				<td class="r"><b>{$data.total.amt|number_format:2}</b></td>
				
				{* Licensee Fee Amount *}
				<td class="r"><b>{$data.total.disc_amt|number_format:$config.global_cost_decimal_points}</b></td>
				
				{* Licensee Nett Sales *}
				<td class="r"><b>{$data.total.cost|number_format:$config.global_cost_decimal_points}</b></td>
			</tr>
			
			{if !$no_bonus}
				{* bonus *}
				<tr class="header">
					<th class="r" colspan="5">Bonus ({$data.total.bonus_per|default:'0'}%)
					
						{if $data.bonus_by_type and !$no_header_footer}
							{capture assign=str_alert}{strip}
								Other Bonus %\n
								=========================\n
								{foreach from=$data.bonus_by_type key=type item=type_list}
									{foreach from=$type_list key=type_v item=other_bonus_per}
										{if $type eq 'SKU'}
											SKU: {$vp_global_si_info_list.$type_v.sku_item_code} ({$other_bonus_per})\n
										{elseif $type eq 'CATEGORY'}
											CATEGORY: {$vp_global_cat_info_list.$type_v.description} ({$other_bonus_per})\n
										{else}
											{$other_bonus_per}\n
										{/if}
									{/foreach}
								{/foreach}
							{/strip}{/capture}
							<img src="/ui/icons/information.png" align="absmiddle" class="clickable" title="Information" onClick="alert('{$str_alert}');" />
						{/if}
					</th>
					
					<td class="r"><b>{$data.total.bonus_amt|number_format:2}</b></td>
				</tr>
				
				{* Amount After Bonus *}
				<tr class="header">
					<th class="r" colspan="5">Total After Bonus</th>
					
					<td class="r"><b>{$data.total.cost_after_bonus|number_format:$config.global_cost_decimal_points}</b></td>
				</tr>
			{/if}
		</table>		
	{/if}
{/if}

{include file="footer.tpl"}