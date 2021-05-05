<tr style="background:#addfff !important" class="member_child_{$card_no}">
	<th>&nbsp;</th>
	<th>SKU Item Code</th>
	<th colspan="2">Description</th>
	<th>MCode</th>
	<th>Qty</th>
	<th>Price</th>
</tr>
{foreach from=$table key=key item=r name=mm}
	<tr style="background:#e0ffff !important" class="member_child_{$card_no}">
		<td>&nbsp;</td>
		<td align="center">{$r.sku_item_code}</td>
		<td colspan="2">{$r.description}</td>
		<td>{$r.mcode|default:"&nbsp;"}</td>
		<td align="right">{$r.qty|number_format:0}</td>
		<td align="right">{$r.price|number_format:2}</td>
	</tr>
	{assign var=ttl_qty value=$ttl_qty+$r.qty}
	{assign var=ttl_price value=$ttl_price+$r.price}
{/foreach}
<tr style="background:#addfff !important" class="member_child_{$card_no}">
		<td align="right" colspan="5"><b>Sub Total</b></td>
		<td align="right">{$ttl_qty|number_format:0}</td>
		<td align="right">{$ttl_price|number_format:2}</td>
</tr>
