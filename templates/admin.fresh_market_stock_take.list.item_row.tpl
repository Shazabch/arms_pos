<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';" item_id="{$r.id}" class="tr_item" id="tr_st_item_{$item.id}">
    <td nowrap>
        <span id="span_st_item_{$item.id}" class="span_st_item">
        <!-- Delete item -->
        <a href="javascript:void(delete_item('{$item.id}'));"><img src="ui/deact.png" title="Delete" border="0" /></a>
        <!-- Swap Up -->
		<a href="javascript:void(swap('up','{$item.id}'));" class="a_swap_up" style="{if $smarty.foreach.f.first}visibility:hidden;{/if}"><img src="ui/icons/arrow_up.png" title="Swap Up" border="0" /></a>
		<!-- Swap Down -->
		<a href="javascript:void(swap('down','{$item.id}'));" class="a_swap_down" style="{if $smarty.foreach.f.last}visibility:hidden;{/if}"><img src="ui/icons/arrow_down.png" title="Swap Down" border="0" /></a>
		</span>
		<span id="span_st_item_loading_{$item.id}"></span>
	</td>
    <td>{$item.date|default:'-'}</td>
    <td>{$item.location|default:'-'}</td>
    <td>{$item.shelf|default:'-'}</td>
    <td>{$item.u|default:'-'}</td>
    <td>{$item.sku_item_code|default:'-'}</td>
    <td>{$item.mcode|default:'-'}</td>
    <td>{$item.artno|default:'-'}</td>
    <td>{$item.description}</td>
    <td>{$item.uom_code}</td>
    <td><input type="text" size="3" name="qty[{$item.id}]" value="{$item.qty}" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if}; recalc_variance({$item.id}, this.value, {$item.sb_qty|default:0});" style="text-align:right"></td>
    <td class="r">{$item.sb_qty|qty_nf}</td>
    <td class="r {if $item.variances>0}positive{elseif $item.variances<0}negative{/if}" id="var_{$item.id}">{if $item.variances>0}+{/if}{$item.variances|qty_nf}</td>
</tr>
