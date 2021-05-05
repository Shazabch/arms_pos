{*
REVISION HISTORY:
++++++++++++++++++

10/23/2007 4:45:01 PM gary
- add cost indicate column.

*}
{strip}
{assign var=item_id value=$item.id}
<td nowrap colspan=2 align=right>
{$smarty.foreach.item.iteration}.
</td>

<td {if $item.is_foc}style="color:#090"{/if} nowrap>
	{$item.artno_mcode}<br>{$item.sku_item_code}
</td>

<td {if $item.is_foc}style="color:#090"{/if}>
{if $item.is_foc}<sup id="foc_id{$item.id}" style="color:#f00">{$item.foc_id}</sup>{/if}
{$item.description}
<sup style="color:#f00" id="foc_annotation{$item.id}">{$foc_annotations.$item_id}</sup>
<!-- supplier remark -->
{if $item.remark}
<div><img src=ui/note16.png align=absmiddle> {$item.remark|escape}</div>
{/if}
{if $item.remark2}
<div><img src=ui/note16.png align=absmiddle> {$item.remark2|escape}</div>
{/if}
</td>
{if !is_array($form.deliver_to)}
<td align=right>
	{$item.selling_price|number_format:2}
</td>
{/if}
<td align=right>
	{array_find_key array=$uom find=$item.selling_uom_id key='id' return='code' default='EACH'}
</td>
<td align=right>
	{$item.order_price|number_format:3}
</td>

<td align=center>
	{$item.cost_indicate|default:"-"}
</td>

{if $form.po_option eq '1'}
<td align=right>
	{$item.resell_price|number_format:3}
</td>
{/if}
<td align=right>
	{array_find_key array=$uom find=$item.order_uom_id key='id' return='code' default='EACH'}
</td>

{if is_array($form.deliver_to)}
{section name=i loop=$branch}
{if in_array($branch[i].id,$form.deliver_to)}
{assign var=bid value=`$branch[i].id`}
<td align=right nowrap>{$item.selling_price_allocation[$bid]|ifzero:"&nbsp;"}</td>
{if !$item.is_foc}
<td align=right nowrap>{$item.qty_allocation[$bid]|ifzero:"&nbsp;"}</td>
<td align=right nowrap>{$item.qty_loose_allocation[$bid]|ifzero:"&nbsp;"}</td>
{else}
<td>&nbsp;</td>
<td>&nbsp;</td>
{/if}
<td align=right nowrap>{$item.foc_allocation[$bid]|ifzero:"&nbsp;"}</td>
<td align=right nowrap>{$item.foc_loose_allocation[$bid]|ifzero:"&nbsp;"}</td>
{/if}
{/section}
<td align=right>{$item.qty|ifzero:"&nbsp;"}</td>
<td align=right>{$item.foc|ifzero:"&nbsp;"}</td>
{else}
{* single branch *}
{if !$item.is_foc}
<td align=right nowrap>{$item.qty|ifzero:"&nbsp;"}</td>
<td align=right nowrap>{$item.qty_loose|ifzero:"&nbsp;"}</td>
{else}
<td>&nbsp;</td>
<td>&nbsp;</td>
{/if}
<td align=right nowrap>{$item.foc|ifzero:"&nbsp;"}</td>
<td align=right nowrap>{$item.foc_loose|ifzero:"&nbsp;"}</td>
{/if}
{if $form.delivered}<td align=right>{$item.delivered|ifzero:"&nbsp;"}</td>{/if}
{if $item.is_foc}
<td align=center>FOC</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td align=center>FOC</td>
{else}
<td align=right>{$item.gamount|number_format:2}</td>
<td align=right>{$item.tax|ifzero:"&nbsp;"}</td>
<td align=right>
	{if $item.disc_remark}
		{$item.disc_remark}<br>
		({$item.discount})
	{else}
	{$item.discount}
	{if strchr($item.discount,'%')}<br>({$item.disc_amount|number_format:2}){/if}
	{/if}
</td>
<td align=right>{$item.amount|number_format:2}</td>
{/if}
{if $item.is_foc}
{assign var=total_profit value=$item.total_selling}
{else}
{assign var=total_profit value=$item.total_selling-$item.amount}
{/if}
<td align=right><span id=total_sell{$item.id}>{$item.total_selling|number_format:2}</span></td>
<td align=right><span id=total_profit{$item.id} style="{if $item.is_foc}color:#090;{elseif $total_profit<=0}color:#f00{/if}">{$total_profit|number_format:2}</span></td>
<td align=right><span id=total_margin{$item.id} style="{if $item.is_foc}color:#090;{elseif $total_profit<=0}color:#f00{/if}">{if $item.total_selling<=0}-{else}{$total_profit/$item.total_selling*100|number_format:2}%{/if}</span></td>
{/strip}
