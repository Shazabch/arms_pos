{*
REVISION HISTORY
=================
3/3/2008 4:38:16 PM gary
- add the total cost and total selling column.
- move the misc cost column.

4/7/2008 12:06:29 PM gary
- cost_price / selling_price get the latest b4 implode or explode.

5/12/2009 4:15:00 PM Andy
- Hide Cost if no $sessioninfo.privilege.SHOW_COST

29/9/2009 10:00:00 PM jeff
- change sku autocomplete to multiple add

10/10/2011 10:58:48 AM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point

*}
<td align=center nowrap>
{if !$form.disabled_edit}
<img src=ui/remove16.png class=clickable title="Delete Row" onclick="delete_item({$item.id})" align=absmiddle alt="{$item.id}">
{/if}
<span class="no" id="no_{$smarty.foreach.fitem.iteration}" title="{$item.id}">
{$smarty.foreach.fitem.iteration}.</span>
</td>

<input type=hidden name=row_id[{$item.id}] value="{$item.sku_item_id}">

<td align=center>
{$item.sku_item_code}
</td>

<td nowrap>{$item.artno} / {$item.mcode}</td>

<td>{$item.description}</td>

<td nowrap class="r">
<input title="{$item.id}" class="r total" size=5 value="{if $item.saved_cost>0}{$item.saved_cost|number_format:$config.global_cost_decimal_points}{else}-{/if}" readonly>
</td>

<td nowrap class="r">
<input title="{$item.id}" class="r total" size=5 value="{if $item.saved_selling>0}{$item.saved_selling|round2}{else}-{/if}" readonly >
</td>

<td nowrap class="r">
<input name="latest_cost[{$item.id}]" id=item_cost_{$item.id} title="{$item.id}" class="r total" size=5 value="{$item.latest_cost|number_format:$config.global_cost_decimal_points}" readonly {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>
{if !$sessioninfo.privilege.SHOW_COST}-{/if}
</td>

<td nowrap class="r">
<input name="latest_selling[{$item.id}]" id=item_selling_{$item.id} title="{$item.id}" class="r total" size=5 value="{$item.latest_selling|round2}" readonly >{if $BRANCH_CODE eq 'HQ'}<img src="/ui/option_button.jpg" style="border:1px solid #bad3fc;padding:1px;" align=top onclick="get_all_price({$item.sku_item_id},{$item.id});">{/if}
<div id="price_list_{$item.sku_item_id}"></div>
</td>

<td nowrap class="r">
<input class="r total" id=item_qty_{$item.id} name="qty[{$item.id}]" title="{$item.id}" size=5 onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if};calc_all();" onblur="focus_next_row(this);" value="{$item.qty}" {if $form.disabled_edit}readonly{/if} alt="{$smarty.foreach.fitem.iteration}" onclick="this.select();">
</td>

<td nowrap class="r total" id=row_cost_{$item.id} title="{$item.id}" {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>
</td>

<td nowrap class="r total" id=row_selling_{$item.id} title="{$item.id}">
</td>
