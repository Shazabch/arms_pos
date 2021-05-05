{*
3/5/2018 3:27 PM Justin
- Bug fixed on columns showing empty instead of zero when no figure.
*}

{foreach from=$table key=sid item=r}
	<tr bgcolor="#AFF380" class="vd_sku_list_{$vendor_id}">
		<td>{$r.sku_item_code}</td>
		<td>{$r.mcode}{if $r.artno}<br />{$r.artno}{/if}</td>
		<td>{$r.description}</td>
		<td class="r">{$r.sales_qty|default:0|qty_nf}</td>
		<td class="r">{$r.sales_amt|default:0|number_format:2}</td>
		<td class="r">{$r.cost_amt|default:0|number_format:$config.global_cost_decimal_points}</td>
		<td class="r">{$r.gp|default:0|number_format:2}</td>
		<td class="r">{$r.gp_perc|default:0|number_format:2}%</td>
	</tr>
{/foreach}
