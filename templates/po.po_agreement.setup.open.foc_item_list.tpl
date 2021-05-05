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
		<th>Required Rule #</th>
		<th>Foc Qty</th>
		<th>Suggested Selling Price</th>
		<th>Purchase Price
			<br />
			<span class="span_latest_cost">Latest Cost</span>
		</th>
		<th>Allowed Branches</th>
	</tr>
	
	<tbody id="tbody_pa_foc_item_list">
		{foreach from=$item_list.foc_item item=foc_pa_item name=fitem}
			{include file="po.po_agreement.setup.open.foc_item_list.row.tpl"}
		{/foreach}
	</tbody>
</table>
