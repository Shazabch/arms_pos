{*
1/16/2013 10:22 AM Justin
- Enhanced to show category column.
- Enhanced to have pre-confirm feature.

2/1/2013 6:01 PM Justin
- Enhanced to include old code column.
*}
<td nowrap>
	<!--img src="ui/remove16.png" class="clickable" title="Delete Row" onclick="delete_item({$item.id})" align="absmiddle" alt="{$item.id}"-->
	<span class="no" id="no_{$item.id}" title="{$item.id}">
		{$smarty.foreach.fitem.iteration|default:$row_no}.
	</span>
</td>
<td>{$item.sku_item_code}</td>
<td align="center">{$item.artno|default:"-"}</td>
<td align="center">{$item.mcode|default:"-"}</td>
<td align="center">{$item.link_code|default:"-"}</td>
<td>{$item.description} {include file=details.uom.tpl uom=$item.packing_uom_code}</td>
<td>{$item.category}</td>
<td>
	<input type="text" size="10" name="cost_price[{$item.id}]" value="{$item.cost_price|number_format:$config.global_cost_decimal_points:".":""}" class="r" onclick="clear0(this);" onchange="mf(this, {$config.global_cost_decimal_points}); GRN.recalc_row({$item.id});" {if $disable}disabled{/if} />
	<input type="hidden" name="selling_price[{$item.id}]" value="{$item.selling_price}" />
</td>
<td>
	<input type="text" size="10" name="qty[{$item.id}]" value="{$item.qty}" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} GRN.recalc_row({$item.id});" class="r" {if $disable}disabled{/if} />
</td>
<td id="amount_{$item.id}" class="r">&nbsp;</td>