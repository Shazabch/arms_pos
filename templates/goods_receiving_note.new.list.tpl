{*
9/20/2007 3:31:23 PM gary
- remove selling uom dropdwon, all seeling uom as default is EACH.

11/7/2007 6:13:52 PM gary
- change the layout for each row.

2/22/2008 4:43:26 PM gary
- FOC items from PO set cost to zero and display as FOC.

5/8/2008 10:55:21 AM gary
- display selling uom follow PO items selling uom. (request by SLLEE)

6/9/2008 5:33:55 PM yinsee
- allow cost editing for non-PO item, otherwise READONLY (aneka)

9/26/2008 9:56:34 AM yinsee
- add default 1 for uomf and uom_id

6/30/2009 4:04 PM Andy
- add GRN Tax

8/7/2009 3:11:29 PM Andy
- if $config.doc_uom_control true and master uom id not equal 1, lock Recv uom

6/24/2011 12:30:31 PM Justin
- Make the field larger when found the SKU item is allow decimal points for ctn and pcs.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

9/20/2011 9:11:43 AM Justin
- Fixed the bugs for rounding up decimal points for cost.

4/20/2012 5:40:56 PM Alex
- add packing uom code after description

7/13/2012 4:49:34 PM Justin
- Enhanced to have UOM control by config and packing uom fraction.

9/5/2012 11:20 AM Justin
- Enhanced to disable UOM selection while found config "doc_disable_edit_uom".

4/25/2013 3:38 PM Justin
- Enhanced to return errors while found user trying to key in negative figure.

2/25/2014 10:43 AM Justin
- Enhanced to include tr tag.
*}

<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';"  id="titem{$item.id}">
{assign var=total_amt value=0}

{if !$config.doc_allow_edit_uom && $item.packing_uom_fraction ne 1}
	{assign var=uom_fraction value=1}
	{assign var=uom_id value=1}
{else}
	{assign var=uom_fraction value=$item.uom_fraction}
	{assign var=uom_id value=$item.uom_id}
{/if}

{if $grr.type eq 'PO'}
<input type="hidden" name="po_qty[{$item.id}]" value="{$item.po_qty}">
<input type="hidden" name="po_cost[{$item.id}]" value="{$item.po_cost|number_format:$config.global_cost_decimal_points:".":""}">
<input type="hidden" name="po_item_id[{$item.id}]" value="{$item.po_item_id}">
{/if}
<input type="hidden" name="uom_id[{$item.id}]" value="{$uom_id|default:1}">
<input type="hidden" name="uomf[{$item.id}]" value="{$uom_fraction|default:1}">
<input type="hidden" name="selling_uom_id[{$item.id}]" value="1">
<input type="hidden" name="selling_uomf[{$item.id}]" value="1">
<input type="hidden" name="master_uom_id[{$item.id}]" value="{$item.master_uom_id}" />
<input type="hidden" name="doc_allow_decimal[{$item.id}]" value="{$item.doc_allow_decimal}" />

<td nowrap>
<img src="ui/remove16.png" class="clickable" title="Delete Row" onclick="delete_item({$item.id})" align="absmiddle" alt="{$item.id}">
<span class="no" id="no_{$smarty.foreach.fitem.iteration}" title="{$item.id}">
{$smarty.foreach.fitem.iteration}.
</span>
</td>
<td>{$item.sku_item_code}</td>
<td align="center">{$item.artno|default:"-"}</td>
<td align="center">{$item.mcode|default:"-"}</td>
<td>{$item.description} {include file=details.uom.tpl uom=$item.packing_uom_code}</td>
<td align="right">
<input name="selling_price[{$item.id}]" size="5" value="{$item.selling_price|number_format:2:".":""}" {if $grr.type eq 'PO'}readonly{/if} onclick="clear0(this);" onchange="mf(this);" class="r">
</td>
<td align="center">{$item.selling_uom|default:"EACH"}</td>

{if $grr.type eq 'PO'}
	<td align="right">
	{if $item.po_cost>0}{$item.po_cost|number_format:$config.global_cost_decimal_points}{elseif $item.po_cost eq 'FOC'}{$item.po_cost|number_format:$config.global_cost_decimal_points}{else}-{/if}
	</td>
{/if}

<td align="right">
{if $item.po_cost eq 'FOC'}
FOC
<input type="hidden" name="cost[{$item.id}]" value="0">
{else}
<input size=7 name="cost[{$item.id}]" value="{$item.cost|number_format:$config.global_cost_decimal_points:".":""}" class=r onclick="clear0(this);" onchange="mf(this, {$config.global_cost_decimal_points});set_new_original_cost('{$item.id}');recalc_row({$item.id});" {if $item.po_item_id}readonly {/if} id="inp,cost,{$item.id}" />
{/if}
{if $config.grn_have_tax}
<input type="hidden" name="original_cost[{$item.id}]" value="{$item.original_cost|ifzero:$item.cost|number_format:$config.global_cost_decimal_points:".":""}" id="inp,original_cost,{$item.id}" class="original_cost" />
{/if}
</td>

<td align="center">
<select onchange="sel_uom({$item.id},this.value)" id="sel_uom{$item.id}" {if (!$config.doc_allow_edit_uom && $item.packing_uom_fraction ne 1) || $config.doc_disable_edit_uom}disabled {/if}>
{section name=i loop=$uom}
<option value="{$uom[i].id},{$uom[i].fraction}" {if $uom[i].id==$uom_id or ($uom[i].code eq 'EACH' && !$uom_id)}selected{/if}>{$uom[i].code}</option>
{/section}
</select>

</td>

<td class="r">
<input name="ctn[{$item.id}]" {if $item.doc_allow_decimal}size="10"{else}size="5"{/if} value="{$item.ctn}" onclick="clear0(this)" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} sel_uom({$item.id}, $('sel_uom{$item.id}').value); positive_check(this); recalc_row({$item.id});" {if $uom_fraction<=1}disabled{/if} class="r">
</td>
<td class="r">
<input name="pcs[{$item.id}]" {if $item.doc_allow_decimal}size="10"{else}size="5"{/if} value="{$item.pcs}" onclick="clear0(this)" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} sel_uom({$item.id}, $('sel_uom{$item.id}').value); positive_check(this); recalc_row({$item.id});" class="r">
</td>

{assign var=q value=$item.ctn+$item.pcs/$uom_fraction}
{assign var=amt value=$q*$item.cost}
<td class=r><input name="amt[{$item.id}]" size="8" value="{$amt|number_format:2:".":""}" readonly class="r"></td>
{if $grr.grn_get_weight}
<td>
<input name="weight[{$item.id}]" size="8" value="{$item.weight}" onclick="clear0(this)">
</td>
{/if}
</tr>
