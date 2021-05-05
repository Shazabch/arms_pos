{*
1/16/2013 9:39:00 AM Fithri
- add column to show items category (level 3)
- can sort by category or description
*}

{foreach from=$adjust_items item=item name=fitem}

<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" id="titem_{$item.id}" class="titem">

<td align="center" nowrap width=50>
	<span class="no" id="no_{$item.id}">
	{$count|default:$smarty.foreach.fitem.iteration}.</span>
	{*<input type="hidden" name="item_sku_item_id[{$item.id}]" value="{$item.sku_item_id}" />*}
</td>
<input type="hidden" name="row_id[{$item.id}]" value="{$item.id}">
<td align="center">{$item.sku_item_code}</td>
<td nowrap>{$item.artno} / {$item.mcode}</td>
<td>{$item.description} {include file=details.uom.tpl uom=$item.packing_uom_code}</td>
<td>{$item.category}</td>

<td nowrap class="r">
<input class="r n qty" name="n_qty[{$item.id}]" id="n_qty_{$item.id}" size=8 onclick="if(this.value)this.select();" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} DSP.calc_total();" title="{$item.id}" value="{if $smarty.request.n_qty[$item.id]}{$smarty.request.n_qty[$item.id]}{else}{$item.qty|abs}{/if}"/>
</td>

<input type="hidden" name="cost[{$item.id}]" value="{$item.cost}" />
<input type="hidden" name="selling_price[{$item.id}]" value="{$item.selling_price}" />
<input type="hidden" name="stock_balance[{$item.id}]" value="{$item.stock_balance}" />
<input type="hidden" name="is_new_item[{$item.id}]" value="{$item.new_item}" />

</tr>
{/foreach}
