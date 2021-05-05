{*
4/25/2017 11:03 AM Khausalya
- Enhanced changes from RM to use config setting. 

*}

<table width="100%" style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border body" cellspacing="1" cellpadding="1" id="docs_items">

<!--START HEADER-->
<thead class="small">
	<tr bgcolor="#ffffff">
		<th width=20>#</th>
		<th nowrap rowspan=2 width=100>ARMS Code</th>
		<th nowrap rowspan=2 width=80>Article /<br>MCode</th>
		<th nowrap rowspan=2>SKU Description</th>
		<th width="50">Stock Balance</th>
		<th width=60>Selling Price<br>({$config.arms_currency.symbol})</th>
		<th width=60>Unit Cost<br>({$config.arms_currency.symbol})</th>
		<th width=80>UOM</th>
		<th nowrap>Qty</th>
		<th width=60>Total Selling<BR>({$config.arms_currency.symbol})</th>
		<th width=60>Total Cost<BR>({$config.arms_currency.symbol})</th>
	</tr>
</thead>
    {assign var=total_cost value=0}
	{assign var=total_qty value=0}
	{assign var=total_selling value=0}
<tbody id="tbody_container">
	{foreach from=$items item=item name=f}
	    {include file='adjustment.fresh_market_write_off.open.sheet.item_row.tpl'}
	{/foreach}
</tbody>
<tfoot id="tbl_footer">
	<tr bgcolor="#ffffff" class="normal">
	    <td class="r" height="24" colspan="8"><b>Total</b></td>
	    <td class="r"><span style="font-weight:bold;" id="span_total_qty">-</td>
	    <td class="r"><span style="font-weight:bold;" id="span_total_selling">-</td>
	    <td class="r"><span style="font-weight:bold;" id="span_total_cost">-</td>
	</tr>
</tfoot>
</table>
