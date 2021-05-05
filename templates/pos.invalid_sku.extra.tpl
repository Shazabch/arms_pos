{*
11/18/2011 10:02:07 AM Alex
- created

4/20/2017 4:23 PM Khausalya 
- Enhanced changes from RM to use config setting. 
*}

{if $match_items}
<table class="report_table">
	<tr class="header">
		<th>&nbsp;</th>
		<th>Receipt Description</th>
		<th>ARMS Code</th>
		<th>Art No</th>
		<th>Manufacture Code</th>
		<th>Link Code</th>
		<th>Cost ({$config.arms_currency.symbol}) per unit</th>
		<th>Price ({$config.arms_currency.symbol}) per unit</th>
	</tr>
	{foreach from=$match_items item=items}
		<tr class="match">
			<td>
				<a onclick="replace_with_match_sku(this)">Replace</a> | <a onclick="replace_with_match_sku(this,1)">Replace All</a>
				<input class="items_id" type='hidden' value='{$items.sku_item_id}'>
			</td>
			<td class="items_receipt_description">{$items.receipt_description|default:'-'}</td>
			<td class="items_sku_item_code">{$items.sku_item_code|default:'-'}</td>
			<td class="items_artno">{$items.artno|default:'-'}</td>
			<td class="items_mcode">{$items.mcode|default:'-'}</td>
			<td class="items_link_code">{$items.link_code|default:'-'}</td>
			<td class="items_cost_price r">{$items.cost_price|default:0|number_format:2|ifzero:'-'}</td>
			<td class="items_selling_price r">{$items.selling_price|default:0|number_format:2|ifzero:'-'}</td>
		</tr>
	{/foreach}
</table>
{/if}
