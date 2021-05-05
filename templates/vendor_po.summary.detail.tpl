{*
REVISION HISTORY
=================
3/5/2008 11:59:15 AM gary
- change old po link to new po link.

4/20/2017 10:23 AM Khausalya
- Enhanced changes from RM to use config setting.

5/3/2018 1:13 PM Andy
- Added Foreign Currency feature.
*}
<div class="noscreen">
<h4>{$title}</h4>
</div>

{if !$po_list}
** no data **
{else}
<table id="tbl_po" class="report_table" width="100%">
<tr class="header">
	<th>&nbsp;</th>
	<th>PO No.</th>
	<th>Branch</th>
	<th>PO Date</th>
	<th>Department</th>
	<th>User</th>
	<th>Approvals</th>
	<th>Total Selling<br />({$config.arms_currency.code})</th>
	{if $got_currency}
		<th>PO Amount<br />(Foreign Currency)</th>
		<th>Exchange Rate</th>
	{/if}
	<th>PO Amount<br />({$config.arms_currency.code})</th>
	<th>Margin</th>
</tr>

{foreach from=$po_list item=item name=i}
<tr bgcolor='{cycle values=",#eeeeee"}'>
	<td>{$smarty.foreach.i.iteration}.</td>
	<td>
	<a href="/po.php?a=view&id={$item.po_id}&branch_id={$item.branch_id}" target="_blank">
	{$item.po_no}
	</a>
	</td>
	<td>{$item.branch}</td>
	<td align="center">{$item.po_date}</td>
	<td>{$item.department}</td>
	<td>{$item.user}</td>
	<td>{get_user_list list=$item.approvals}</td>
	<td align="right">{$item.total_selling_amt|number_format:2}</td>
	
	{if $got_currency}
		{if $item.currency_code}
			<td align="right">{$item.currency_code} {$item.po_amount|number_format:2}</th>
			<td align="right">{$item.currency_rate}</th>
		{else}
			<td align="right">-</td>
			<td align="right">-</td>
		{/if}
	{/if}
	
	<td align="right">
		{if $item.currency_code}<span class="converted_base_amt">{/if}
		{$item.base_po_amount|number_format:2}
		{if $item.currency_code}*{/if}
	</td>
	{if $item.total_selling_amt}
		{assign var=gp value=$item.total_selling_amt-$item.base_po_amount}
		{assign var=gp value=$gp/$item.total_selling_amt*100}
	{/if}
	<td align="right">{$gp|number_format:2}%</td>
	{assign var=gp value=0}
</tr>
{assign var=sum_sell value=$sum_sell+$item.total_selling_amt}
{assign var=sum_po_amt value=$sum_po_amt+$item.base_po_amount}
{/foreach}

<tr class="header" align="right">
	<th colspan="7">Total</th>
	<th>{$sum_sell|number_format:2}</th>
	
	{if $got_currency}
		<th align="right">-</th>
		<th align="right">-</th>
	{/if}
	
	<th>{$sum_po_amt|number_format:2}</th>
	{if $sum_sell}
		{assign var=sum_gp value=$sum_sell-$sum_po_amt}
		{assign var=sum_gp value=$sum_gp/$sum_sell*100}
	{/if}
	<th>{$sum_gp|number_format:2}%</th>
</tr>
</table>
<script>
//ts_makeSortable($('tbl_po'));
</script>
{/if}
