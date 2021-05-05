{*
12/12/2012 4:56:00 PM Fithri
- multiple user view/edit
- owner can share sku with other user
- dont allow delete item from sku group if the item got sales

1/18/2013 4:05 PM Justin
- Enhanced to show mcode on the error message when sku items is duplicated.
*}

<tr id="tr_sku_item_code_row-{$item.sku_item_code}" class="tr_sku_item_code_row">
	<td>
		<input type="hidden" name="sku_code_list[{$item.sku_item_code}]" value="{$item.sku_item_code}" />
		<input type="hidden" name="added_by[{$item.sku_item_code}]" value="{$item.added_by}" />
		<input type="hidden" name="added_timestamp[{$item.sku_item_code}]" value="{$item.added_timestamp}" />
		<input type="hidden" id="cannot_delete_{$item.sku_item_code}" value="{$item.cannot_delete}" />
		<input type="checkbox" name="delete_sku_item_code[{$item.sku_item_code}]" value="{$item.sku_item_code}" id="inp_sku_item_code_row-{$item.sku_item_code}" {if $item.cannot_delete}disabled{/if} />
	</td>
	<td>{$item.sku_item_code}</td>
	<td>{$item.artno|default:'-'}</td>
	<td><span id="span-mcode-{$item.sku_item_code}">{$item.mcode|default:'-'}</span></td>
	<td>{$item.description|default:'-'}</td>
	<td>{$item.added_by_u|default:'-'}</td>
	<td>{$item.added_timestamp|default:'-'}</td>
</tr>