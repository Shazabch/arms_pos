<tr id="tr_lose_item_row-{$group_id}-{$row_id}" class="tr_lose_item_row">
	<td align="center">
		{if $can_edit}
			<img src="ui/del.png" align="absmiddle" title="Delete" class="clickable" onClick="REPACKING_FORM.remove_lose_item_row_clicked('{$group_id}', '{$row_id}');" />
		{/if}
		
		<input type="hidden" name="items[{$group_id}][lose][{$row_id}][sku_item_id]" value="{$item.sku_item_id}" />
		<input type="hidden" id="inp_lose_item_sku_id-{$group_id}-{$row_id}" class="inp_lose_item_sku_id-{$group_id}" value="{$item.sku_id}" />
	</td>
	
	<td>{$item.sku_item_code}</td>
	<td>{$item.artno|default:'-'}</td>
	<td>{$item.mcode|default:'-'}</td>
	<td>
		{$item.description|default:'-'}
		{include file=details.uom.tpl uom=$item.packing_uom_code}
	</td>
	
	{* cost *}
	<td align="right">
		{$item.cost|number_format:$config.global_cost_decimal_points}
		
		<input type="hidden" name="items[{$group_id}][lose][{$row_id}][cost]" value="{$item.cost}" />
	</td>
	
	{* Qty *}
	<td align="center">
		<input type="text" name="items[{$group_id}][lose][{$row_id}][qty]" size="5" value="{$item.qty}" style="text-align:right;" onChange="REPACKING_FORM.lose_item_qty_changed('{$group_id}', '{$row_id}', '{$item.doc_allow_decimal}');" class="required" title="Lose Qty" />
	</td>
	
	{assign var=row_cost value=$item.cost*$item.qty}
	{* Total Cost *}
	<td align="right">
		<span id="span_lose_item_row_total_cost-{$group_id}-{$row_id}">{$row_cost|number_format:$config.global_cost_decimal_points}</span>
		
		<input type="hidden" id="inp_lose_item_row_cost-{$group_id}-{$row_id}" value="{$row_cost}" />
	</td>
</tr>
