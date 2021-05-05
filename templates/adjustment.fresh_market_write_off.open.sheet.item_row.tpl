{assign var=cost value=$item.cost}
{assign var=selling value=$item.selling_price}

{assign var=qty value=$item.qty|abs}
{assign var=row_amount value=$cost/$item.uom_fraction}
{assign var=row_amount value=$row_amount*$qty}

{assign var=row_selling value=$selling/$item.uom_fraction}
{assign var=row_selling value=$row_selling*$qty}

<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';"  id="tr_item,{$item.id}" class="tr_item {if $smarty.request.highlight_item_id eq $item.sku_item_id}highlight_row{/if}">
    <td align=center nowrap width=50>
		{if !$readonly}
		<img src=ui/remove16.png class=clickable title="Delete Row" onclick="delete_item('{$item.id}')" align="absmiddle" alt="{$item.id}">
		{/if}
		<span class="no">{$smarty.foreach.f.iteration}.</span>
	</td>
	<td>{$item.sku_item_code|default:'-'}</td>
	<td>{$item.artno_mcode|default:'-'}</td>
	<td>{$item.sku_description|default:'-'}</td>
	<td align="center">
		<input size="6" class="small r" readonly name="stock_balance[{$item.id}]" id="stock_balance,{$item.id}" value="{$item.stock_balance}" style="background:#ddd;" sku_item_id="{$item.sku_item_id}" />
	</td>
    <td align="center">
		<input class="r selling" id="selling_price,{$item.id}" name="selling_price[{$item.id}]" value="{$selling|number_format:2:".":""}" size="8" readonly />
	</td>
	<td align="center">
		<input class="r cost" id="cost,{$item.id}" name="cost[{$item.id}]" value="{$cost|number_format:3:".":""}" size="8" onchange="row_recalc('{$item.id}');" readonly />
	</td>
	<td align="center">
	    <input type="hidden" name="uom_id[{$item.id}]" id="uom_id,{$item.id}" value="{$item.uom_id|default:1}" />
	    <input type="hidden" name="uom_fraction[{$item.id}]" id="uom_fraction,{$item.id}" value="{$item.uom_fraction|default:1}" />
	    <select name="sel_uom[{$item.id}]" disabled>
	        {foreach from=$uom key=uom_id item=u}
	            <option value="{$uom_id},{$u.fraction}" {if ($item.uom_id eq $uom_id) or (!$item.uom_id and $u.code eq 'EACH')}selected {/if}>{$u.code}</option>
	        {/foreach}
	    </select>
	</td>
	<td align="center" width="60">
	    <input class="r qty" id="qty,{$item.id}" name="qty[{$item.id}]"  size="8" onchange="row_recalc('{$item.id}');" value="{$qty|num_format:2}" {if $readonly}disabled {/if} />
	</td>
	<td align=right>
		<span id="row_selling,{$item.id}" class="row_selling">{$row_selling|default:0|number_format:2:".":""}</span>
	</td>
	<td align="right">
		<span id="row_amount,{$item.id}" class="row_amt">{$row_amount|default:0|number_format:3:".":""}</span>
	</td>
</tr>
