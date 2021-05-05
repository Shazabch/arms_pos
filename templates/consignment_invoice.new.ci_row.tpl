{*
3/30/2011 12:14:40 PM Justin
- Modified the cost price to round to 3 instead of 2 decimal points.
*}

{assign var=row_no value=$item_n|default:$smarty.foreach.fitem.iteration}

<td align=center nowrap width=50>
{if $form.create_type ne '3' && ($form.status<1 || $form.status eq '2') && !$form.approval_screen && !$readonly}
	<img src=ui/remove16.png class=clickable title="Delete Row" onclick="delete_item({$item.id})" align=absmiddle alt="{$item.id}">
{/if}
<span class="no" id="no_{$smarty.foreach.fitem.iteration}">
{$smarty.foreach.fitem.iteration}.</span>
</td>

<td align=center>{$item.sku_item_code}</td>
<td nowrap>{$item.artno_mcode}</td>
<td>{$item.description}</td>

{if $form.create_type==3}
	<td align=right>
	<input class="r cost uom" id=po_cost_{$item.id} name=po_cost[{$item.id}] value="{$form.po_cost|number_format:2:".":""}" size=6 title="{$bid},{$item.id}" onchange="mf(this);" {if $readonly}disabled{/if}>
	</td>
{/if}

<td align=center>
{assign var=cost value=$item.cost_price|number_format:3:".":""}
<input class="r cost uom" id=cost_price_{$item.id} name=cost_price[{$item.id}] value="{$cost}" size=6 title="{$bid},{$item.id}" onchange="row_recalc({$item.id},{$bid}); this.value=round(this.value, 3);">
</td>


<td align=center>
<input type=hidden name=no_item value="{$no_item}">
<input type=hidden name=sku_item_code[{$item.id}] id=sku_item_code{$item.id} value="{$item.sku_item_code}">
<input type=hidden name=uom_id[{$item.id}] id=uom_id{$item.id} value="{$item.uom_id|default:1}">
<input type=hidden name=uom_fraction[{$item.id}] class="uom" title="{$item.id}" id=uom_fraction{$item.id} value="{$item.uom_fraction|default:1}">

<select name=sel_uom[{$item.id}] id="sel_uom{$item.id}" onchange="uom_change(this.value,'{$item.id}');row_recalc({$item.id},'')">
{section name=i loop=$uom}
<option value="{$uom[i].id},{$uom[i].fraction}" {if $item.uom_id == $uom[i].id or ($item.uom_id==0 and $uom[i].code eq 'EACH')}selected{/if}>{$uom[i].code}</option>
{/section}
</select>
</td>

{assign var=qty value=0}
{assign var=foc value=0}
{assign var=total_qty value=0}
{assign var=total_foc value=0}

{if $form.deliver_branch}
{section name=i loop=$branch}
{if $branch[i].id eq $form.deliver_branch}
{assign var=bid value=`$branch[i].id`}
{assign var=b_name value=`$branch[i].report_prefix`}

{assign var=qty value=$qty+$item.ctn_allocation.$bid*$item.uom_fraction+$item.pcs_allocation.$bid}
{assign var=amount value=$cost/$item.uom_fraction}
{assign var=amount value=$amount*$qty}

<td align=center nowrap valign=top>
<input id="qty_ctn{$item.id}_{$bid}" title="{$bid},{$item.id}" class="r uom" name="qty_ctn[{$item.id}][{$bid}]" {if $item.uom_fraction == 1 or $item.uom_id==1 or !$item.uom_id}disabled value="--"{else} value="{$item.ctn_allocation.$bid}"{/if} style="width:30px;" size=1 onchange="row_recalc({$item.id},{$bid})">

<input id="qty_pcs{$item.id}_{$bid}" title="{$bid},{$item.id}" class="r uom" name="qty_pcs[{$item.id}][{$bid}]" style="width:30px; background:#fc9;" size=1 onchange="row_recalc({$item.id},{$bid})" value="{$item.pcs_allocation.$bid}">

{if !$form.open_info.name}
<div align=center>
	S.P({$b_name}) <input name="selling_price[{$item.id}][{$bid}]" value="{if $item.selling_price_allocation.$bid}{$item.selling_price_allocation.$bid|number_format:2}{else}-{/if}" class="r" readonly style="background:#ddd;" size=4>
</div>
{/if}

</td>

{/if}
{/section}

<td align=right>
	<span id=row_qty{$item.id} class="uom" title=",{$item.id}">{$qty|default:0}</span>
</td>
<td align=right>
	<span id=row_amount{$item.id} class="uom" title=",{$item.id}">{$amount|default:0|number_format:3:".":""}</span>
</td>
{/if}
