{*
06/30/2020 02:25 PM Sheila
- Updated button css.

10/16/2020 1:34 PM William
- Enhanced to add tax checking.
*}

{include file='header.tpl'}
{literal}
<style>
	table.form_body th{
		padding-right: 10px;
	}
	table.form_body td{
		padding-right: 25px;
	}
	table tr.group{
		background-color: lightgrey;
	}
</style>
{/literal}
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
	var MPRICE_SALES_REPORT = {
		f: undefined,
		initialize: function(){
			this.f = document.f_a;
			
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
		},
		submit_form: function(t){
			this.f['export_excel'].value = 0;
			if(t == 'excel'){
				this.f['export_excel'].value = 1;
			}
			for(var i=0; i<$('sku_code_list').length; i++) {
				$('sku_code_list').options[i].selected = true;
			}
			this.f.submit();
		}
	}
	{/literal}
	</script>
{/if}

<h1>{$PAGE_TITLE}</h1>
{if !$no_header_footer}
	<div class="noprint stdframe">
	<form method="post" name="f_a" onSubmit="return false;">
	<input type="hidden" name="form_submit" value="1" />
	<input type="hidden" name="export_excel" />
	
	<table class="form_body">
		<tr>
			{if $BRANCH_CODE eq "HQ"}
				<th align="left">Branch :</th>
				<td>
					<select name="branch_id">
						{foreach from=$branch_list key=k item=i}
							<option value="{$k}" {if $form.branch_id eq $k}selected{/if}>{$i.code}</option>
						{/foreach}
					</select>
				</td>
			{else}
				<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
			{/if}
			<th align="left">Date From :</th>
			<td>
				<input type="text" name="date_from" id="date_from" size="10" value="{$form.date_from}" />
				<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date From" />
			</td>
			<th>Date To :</th>
			<td>
				<input type="text" name="date_to" id="date_to" size="10" value="{$form.date_to}" />
				<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date To" />
			</td>
		</tr>
		<tr>
			<th align="left">SKU Type :</th>
			<td>
				<select name="sku_type">
					<option value="">-- All --</option>
					{foreach from=$sku_type_list item=r}
						<option value="{$r.code}" {if $form.sku_type eq $r.code}selected{/if}>{$r.code}</option>
					{/foreach}
				</select>
			</td>
			<th align="left">Sort by :</th>
			<td>
				<select name="sort_field">
					{foreach from=$sort_field_list key=k item=i}
						<option value="{$k}" {if $form.sort_field eq $k}selected{/if}>{$i}</option>
					{/foreach}
				</select>
				<select name="sort_order">
					{foreach from=$sort_order_list key=k item=i}
						<option value="{$k}" {if $form.sort_order eq $k}selected{/if}>{$i}</option>
					{/foreach}
				</select>
			</td>
		</tr>
	</table>
	
	{include file='sku_items_autocomplete_multiple_add2.tpl' parent_form='document.f_a'}
	<table class="form_body">
		<tr>
			<th>Show finalized POS only</th>
			<td><input type="checkbox" name="show_finalized" {if !isset($show_finalized) || $show_finalized}checked{/if}/></td>
		</tr>
	</table>
	<br />
	<input class="btn btn-primary" type="button" value="Show Report" onClick="MPRICE_SALES_REPORT.submit_form();"/>
	{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
		<button class="btn btn-primary" onClick="MPRICE_SALES_REPORT.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	{/if}
	<br /><br />
	<ul>
		<li> Report is allowed to show 30 days of transaction only.</li>
		<li> Cost and GP will show out once POS is finalized.</li>
		<li> Show finalized POS only is reconmended for accurate GP% calculation.</li>
	</ul>
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
				<th align="center">Art No</th>
				<th align="center">Description</th>
				<th align="center">MPrice Type</th>
				<th align="center">Qty</th>
				<th align="center">Amt</th>
				{if $show_gst_amt}
					<th align="center">Tax Amt</th>
				{/if}
				<th align="center">Cost</th>
				<th align="center">GP Amount</th>
				<th align="center">GP%</th>
			</tr>
		</thead>
		{assign var=group_date value=0}
		{foreach from=$data item=d}
			{if $group_date != $d.date}
				<tr class="group">
					{foreach from=$total_by_date key=total_date item=ti}
						{if $d.date == $total_date}
							<th colspan=5 align="left">{$d.date}{if $ti.finalized eq 0}<span style='color:crimson; padding-left: 5px'>(Unfinalize)</span>{/if}</th>
							<th align="right">{$ti.qty}</th>
							<th align="right">{$ti.amt|number_format:2}</th>
							{if $show_gst_amt}
								<th align="right">{$ti.gst_amt|number_format:2|ifzero:'-'}</th>
							{/if}
							<th align="right">{$ti.cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
							<th align="right">{$ti.gp_amt|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
							<th align="right">{if $ti.cost ne 0}{$ti.gp_amt/$ti.amt*100|number_format:$config.global_cost_decimal_points}{else}-{/if}</th>
						{/if}
					{/foreach}
				</tr>
				{assign var=group_date value=$d.date}
			{/if}
				<tr>
					<td style="white-space:nowrap;">{$d.sku_item_code}</td>
					<td style="white-space:nowrap;">{$d.mcode}</td>
					<td style="white-space:nowrap;">{$d.artno}</td>
					<td style="white-space:nowrap;">{$d.description|upper}</td>
					<td style="white-space:nowrap;">{$d.mprice_type}</td>
					<td align="right">{$d.qty}</td>
					<td align="right">{$d.amt|number_format:2}</td>
					{if $show_gst_amt}
						<td align="right">{$d.gst_amt|number_format:2|ifzero:'-'}</td>
					{/if}
					<td align="right">{$d.cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</td>
					<td align="right">{$d.gp_amt|number_format:$config.global_cost_decimal_points|ifzero:'-'}</td>
					<td align="right">{if $d.cost ne 0}{$d.gp_amt/$d.amt*100|number_format:$config.global_cost_decimal_points}{else}-{/if}</td>
				</tr>
		{/foreach}
		<tr class="header">
				<th colspan="5" align="right">Total</th>
				<th align="right">{$total.total.qty}</th>
				<th align="right">{$total.total.amt|number_format:2}</th>
				{if $show_gst_amt}
					<th align="right">{$total.total.gst_amt|number_format:2|ifzero:'-'}</th>
				{/if}
				<th align="right">{$total.total.cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
				<th align="right">{$total.total.gp_amt|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
				<th align="right">{if $total.total.cost ne 0}{$total.total.gp_amt/$total.total.amt*100|number_format:$config.global_cost_decimal_points}{else}-{/if}</th>
		</tr>
		{foreach from=$total.mprice_total key=tmk item=tmi}
			<tr class="header">
				<th colspan="5" align="right">{$tmk}</th>
				<th align="right">{$tmi.qty}</th>
				<th align="right">{$tmi.amt|number_format:2}</th>
				{if $show_gst_amt}
					<th align="right">{$tmi.gst_amt|number_format:2|ifzero:'-'}</th>
				{/if}
				<th align="right">{$tmi.cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
				<th align="right">{$tmi.gp_amt|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
				<th align="right">{if $tmi.cost ne 0}{$tmi.gp_amt/$tmi.amt*100|number_format:$config.global_cost_decimal_points}{else}-{/if}</th>
			</tr>
		{/foreach}
	</table>
{elseif $date_error}
	<ul><li>{$date_error}</li></ul>
{else}
	{if $form.form_submit}
		<ul><li>No data</li></ul>
	{/if}
{/if}

{include file='footer.tpl'}

<script type="text/javascript">
{literal}
MPRICE_SALES_REPORT.initialize();
{/literal}
</script>
