{*
9/24/2012 11:08 AM Andy
- Add artno.
*}

<table class="report_table" width="100%">
	<tr class="header">
		<th>&nbsp;</th>
		<th>ARMS Code</th>
		<th>MCode</th>
		<th>Art No.</th>
		<th>Description</th>
		<th>Rule #</th>
		<th>Qty Type</th>
		<th>Qty</th>
		<th>Discount [<a href="javascript:void(discount_help())">?</a>]</th>
		<th>Suggested Selling Price</th>
		<th>Purchase Price
			<br />
			<span class="span_latest_cost">Latest Cost</span>
		</th>
		<th>Allowed Branches</th>
		<th>GP %</th>
	</tr>
	<tbody id="tbody_pa_item_list">
		{foreach from=$item_list.item item=pa_item name=fitem}
			{include file="po.po_agreement.setup.open.item_list.row.tpl"}
		{/foreach}
	</tbody>
</table>
