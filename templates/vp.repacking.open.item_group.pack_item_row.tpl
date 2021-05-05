<tr id="tr_pack_item_row-{$group_id}-{$row_id}" class="tr_pack_item_row">
	<td align="center">
		{if $can_edit}
			<img src="ui/del.png" align="absmiddle" title="Delete" class="clickable" onClick="REPACKING_FORM.remove_pack_item_row_clicked('{$group_id}', '{$row_id}');" />
		{/if}
		
		<input type="hidden" name="items[{$group_id}][pack][{$row_id}][sku_item_id]" value="{$item.sku_item_id}" />
		<input type="hidden" name="items[{$group_id}][pack][{$row_id}][cost]" value="{$item.cost}" />
	</td>
	
	<td>{$item.sku_item_code}</td>
	<td>{$item.artno|default:'-'}</td>
	<td>{$item.mcode|default:'-'}</td>
	<td>
		{$item.description|default:'-'}
		{include file=details.uom.tpl uom=$item.packing_uom_code}
	</td>
	
	{* misc cost *}
	<td align="right">
		<input type="text" name="items[{$group_id}][pack][{$row_id}][misc_cost]" size="5" value="{$item.misc_cost}" style="text-align:right;" onChange="REPACKING_FORM.pack_item_misc_cost_changed('{$group_id}', '{$row_id}');" title="Misc Cost" />
	</td>
	
	{* Calculated Cost *}
	<td align="center">
		<input type="text" name="items[{$group_id}][pack][{$row_id}][calc_cost]" size="5" value="{$item.calc_cost}" style="text-align:right;" readonly />
	</td>
	
	{* Qty *}
	<td align="center">
		<input type="text" name="items[{$group_id}][pack][{$row_id}][qty]" size="5" value="{$item.qty}" style="text-align:right;" onChange="REPACKING_FORM.pack_item_qty_changed('{$group_id}', '{$row_id}', '{$item.doc_allow_decimal}');" class="required" title="Pack Qty" />
	</td>
	
	{assign var=row_cost value=$item.calc_cost*$item.qty}
	
	{* Total Cost *}
	<td align="right">
		<span id="span_pack_item_row_total_cost-{$group_id}-{$row_id}">{$row_cost|number_format:$config.global_cost_decimal_points}</span>
	</td>
</tr>
