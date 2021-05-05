{*
12/11/2014 3:40 PM Justin
- Enhanced to have GST information.

6/1/2017 2:42 PM Justin
- Bug fixed on GP calculation.

4/20/2018 2:19 PM Andy
- Added Foreign Currency feature.

7/31/2019 2:31 PM William
- Fixed php warning display when division by 0.

02/17/2021 4:01 PM Rayleen
- Remove bgcolor in table row and department link if it is an export file
*}

<br>
{literal}
<style>
.sl
{
	white-space: nowrap;
}
.sl td, .sl th
{
	border-left:1px solid #000;	
}
td.noborder, th.noborder
{
	border-left:none;
}
</style>

{/literal}
<div class="xnoscreen">
	<h3>
		User: {if $_u}{$_u}{else}All{/if} &nbsp; 
		Branch: {if $_br}{$_br}{else}All{/if} &nbsp; 
		Department: {if $_dp}{$_dp}{else}All{/if} &nbsp; 
		Status: 
		{if $smarty.request.status==1}
			Draft
		{elseif $smarty.request.status==2}
			Proforma
		{elseif $smarty.request.status==3}
			Approved
		{else}
			All
		{/if} &nbsp;
		{if $config.foreign_currency}
			Currency:
			{if !$smarty.request.currency_code}All
			{elseif $smarty.request.currency_code eq 'base_currency'}Base Currency
			{else}
				{$smarty.request.currency_code}
			{/if}
		{/if}
	</h3>
</div>

{if !$tb}
	** no data **
{else}
	{if $config.foreign_currency}* {$LANG.BASE_CURRENCY_CONVERT_NOTICE}{/if}
	<table class="report_table" width="100%">
	<tr class="header">
		<th class="noborder" {if $config.foreign_currency}rowspan="2"{/if}>Department</th>
		
		{* Branch *}
		{foreach from=$uq_br key=br item=br_data}
			<th {if $config.foreign_currency}colspan="{count var=$br_data.currency offset=1}"{/if}>{$br}</th>
		{/foreach}
		
		{* Total Cost *}
		<th {if $config.foreign_currency}colspan="{count var=$total.currency offset=1}"{/if}>Total Cost</th>
		
		{* GST *}
		{if $is_under_gst}
			<th {if $config.foreign_currency}rowspan="2"{/if}>Total GST</th>
		{/if}
		
		{* Total Selling *}
		<th {if $config.foreign_currency}rowspan="2"{/if}>Total Selling
			{if $config.enable_gst}<br />(Exclude GST){/if}
		</th>
		
		<th {if $config.foreign_currency}rowspan="2"{/if}>GP %</th>
	</tr>
	
	{if $config.foreign_currency}
		<tr class="header">
			{* Branch *}
			{foreach from=$uq_br key=br item=br_data}
				{foreach from=$br_data.currency item=currency_code}
					<th>
						{$currency_code|default:$config.arms_currency.code}
						{* if $currency_code}
							<br /><span class="small converted_base_amt">{$config.arms_currency.code}</span>
						{/if *}
					</th>
				{/foreach}
				<th>Total*<br />({$config.arms_currency.code})</th>
			{/foreach}
			
			{* Total Cost *}
			{foreach from=$total.currency key=currency_code item=currency_info}
				<th>{$currency_code|default:$config.arms_currency.code}</th>
			{/foreach}
			<th>Total*</th>
		</tr>
	{/if}
	
	{assign var=total_sel value=0}
	{foreach from=$uq_dp key=dp item=dept_id}
		<tr {if !$is_export} bgcolor="{cycle values=',#eeeeee'}" {/if}>
			{* Department *}
			<td nowrap>
				{if $is_export}
					{$dp}
				{else}
					<a href="javascript:void(zoom_dept({$dept_id}))">{$dp}</a></th>
				{/if}
			
			{* Branch*}
			{foreach from=$uq_br key=br item=br_data}
				{if $config.foreign_currency}
					{foreach from=$br_data.currency item=currency_code}
						<td align="right">
							{$tb.$br.$dp.currency.$currency_code.total_cost|number_format:2}
							{* if $currency_code and $tb.$br.$dp.currency.$currency_code.total_cost}
								<br /><span class="small converted_base_amt">{$tb.$br.$dp.currency.$currency_code.base_total_cost|number_format:2}</span>
							{/if *}
						</td>
					{/foreach}
				{/if}
				<td align="right" {if $config.foreign_currency}class="converted_base_amt"{/if}>{$tb.$br.$dp.total_cost|number_format:2}{if $config.foreign_currency}*{/if}</td>
			{/foreach}
			
			{* Total Cost *}
			{if $config.foreign_currency}
				{foreach from=$total.currency key=currency_code item=currency_info}
					<td align="right">{$row_total.$dp.currency.$currency_code.total_cost|number_format:2}</td>
				{/foreach}
			{/if}
			<td align="right" {if $config.foreign_currency}class="converted_base_amt"{/if}>{$row_total.$dp.total_cost|number_format:2}{if $config.foreign_currency}*{/if}</td>
			
			{* GST *}
			{if $is_under_gst}
				{assign var=total_gst value=$total_gst+$row_total.$dp.total_gst}
				<td align="right">{$row_total.$dp.total_gst|number_format:2}</td>
			{/if}
			
			{* Total Selling *}
			<td align="right">{$row_total.$dp.total_selling|number_format:2}</td>
			
			{* GP *}
			{assign var=temp value=$row_total.$dp.total_selling-$row_total.$dp.total_cost}
			{if $row_total.$dp.total_selling == 0} 
				{assign var=GP value=0}
			{else}
				{assign var=GP value=$temp/$row_total.$dp.total_selling*100}
			{/if}
			<td align="right">{$GP|number_format:2}</td>
		</tr>
		{assign var=total_sel value=$total_sel+$row_total.$dp.total_selling}
	{/foreach}

	<tr class="header">
		<td><b>Total</b></th>
		
		{* Branch *}
		{foreach from=$uq_br key=br item=br_data}
			{if $config.foreign_currency}
				{foreach from=$br_data.currency item=currency_code}
					<td align="right">{$col_total.$br.currency.$currency_code.total_cost|number_format:2}</td>
				{/foreach}
			{/if}
			<td align="right" {if $config.foreign_currency}class="converted_base_amt"{/if}>{$col_total.$br.total_cost|number_format:2}{if $config.foreign_currency}*{/if}</td>
		{/foreach}
		
		{* Total Cost *}
		{if $config.foreign_currency}
			{foreach from=$total.currency key=currency_code item=currency_info}
				<td align="right">{$total.currency.$currency_code.total_cost|number_format:2}</td>
			{/foreach}
		{/if}
		<td align="right" {if $config.foreign_currency}class="converted_base_amt"{/if}>{$total.total_cost|number_format:2}{if $config.foreign_currency}*{/if}</td>
		
		{* GST *}
		{if $is_under_gst}
			<td align="right">{$total_gst|number_format:2}</td>
		{/if}
		
		{* Total Selling *}
		<td align="right">{$total_sel|number_format:2}</td>
		
		{* GP *}
		{assign var=total_temp value=$total_sel-$total.total_cost}
		{if $total_sel == 0} 
			{assign var=total_GP value=0}
		{else}
			{assign var=total_GP value=$total_temp/$total_sel*100}
		{/if}
		<td align="right">{$total_GP|number_format:2}</td>
	</tr>
	</table>
{/if}