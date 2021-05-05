{*

29/9/2009 10:00:00 PM jeff
- change sku autocomplete to multiple add

6/3/2010 11:03:56 AM Andy
- Right align the total selling amount.

7/6/2010 10:12:46 AM Justin
- Added the checking function to disable/enable positive and negative fields base on the Adjustment Type.

7/6/2011 10:45:02 AM Andy
- Fix when save, item missing if user open multiple adjustment.
- Fix auto clear all temp items when user enter adjustment list page.

7/27/2011 4:19:23 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config but not fixed by 2.

12/1/2011 10:48:43 AM Justin
- Fixed the bugs where positive and negative qty that added separator automatically.

4/20/2012 5:47:34 PM Alex
- add packing uom code after description

5/16/2013 2:46 PM Justin
- Bug fixed on system capture the wrong cost whenever the cost is over one thousand.

11:46 AM 1/28/2014 Justin
- Enhanced to align qty to right instead of left side.

3/20/2017 5:22 PM Andy
- Add item_doc_allow_decimal input to store item.doc_allow_decimal.
*}

<td align=center nowrap width=50>
	{if (!$form.status or ($form.status==2 and $form.user_id==$sessioninfo.id))}
	<img src=ui/remove16.png class=clickable title="Delete Row" onclick="delete_item({$item.id})" align=absmiddle alt="{$item.id}">
	{/if}
	<span class="no" id="no_{$smarty.foreach.fitem.iteration}">
	{$count|default:$smarty.foreach.fitem.iteration}.</span>
	<input type="hidden" name="item_sku_item_id[{$item.id}]" value="{$item.sku_item_id}" />
	<input type="hidden" name="item_doc_allow_decimal[{$item.id}]" value="{$item.doc_allow_decimal}" />
</td>
<input type=hidden name=row_id[{$item.id}] value="{$item.id}">
<td align=center>{$item.sku_item_code}</td>

<td nowrap>{$item.artno} / {$item.mcode}</td>

<td>{$item.description} {include file=details.uom.tpl uom=$item.packing_uom_code}</td>
<td><input size=5 class="small r" readonly name="stock_balance[{$item.id}]" id="stock_balance,{$item.sku_item_id}" value="{$item.stock_balance}" ></td>
<td><input size=5 class="small r inp_selling_price" readonly name="selling_price[{$item.id}]" id="selling_price{$item.id}" value="{$item.selling_price|round2}" title="{$item.id}"></td>

<td class="r" {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>
<input size=5 class="small r" readonly name="unit_cost[{$item.id}]" id="unit_cost{$item.id}" value="{$item.cost|number_format:$config.global_cost_decimal_points:'.':''}">
</td>

<td nowrap class="r">
<input class="p qty r" name="p_qty[{$item.id}]" id="p_qty_{$item.id}" size=8 onclick="if(this.value)this.select();" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if};calc_total({$item.id});" title="{$item.id}" {if $smarty.request.is_config_adj_type == '-' || $is_config_adj_type == '-'}readonly{/if} value="{if $item.qty>0}{$item.qty}{/if}">
</td>

<td nowrap class="r">
<input class="n qty r" name="n_qty[{$item.id}]" id="n_qty_{$item.id}" size=8 onclick="if(this.value)this.select();" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if};calc_total({$item.id});" title="{$item.id}" {if $smarty.request.is_config_adj_type == '+' || $is_config_adj_type == '+'}readonly{/if} value="{if $item.qty<0}{$item.qty|abs}{/if}">
</td>

<td class="r" {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>
<input size=8 class="qty small r" readonly id="cost_{$item.id}" name="cost[{$item.id}]" value="{$item.cost*$item.qty|number_format:2:'.':''|ifzero:''}" >
</td>

<td class="r"><input size=8 class="qty small r" readonly id="total_selling_{$item.id}" name="total_selling[{$item.id}]" value="{$item.selling_price*$item.qty|number_format:2:'.':''|ifzero:''}"></td>
<!--td id="row_total_{$item.id}" class="r qty">{$item.total|default:0}</td-->
