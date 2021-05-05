{*
REVISION HISTORY:
++++++++++++++++++

10/23/2007 4:45:01 PM gary
- add cost indicate column.

2008-12-1 5:11:00 PM Andy
- Remove all sheet_n

9/20/2011 12:28:11 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.
*}



<!-- po item table -->
<table width=100% style="border:1px solid #999; padding:5px;" class="small body" border=0 cellspacing=1 cellpadding=1>
<thead>
<!-- for HQ -->
{if is_array($form.deliver_to)}
<tr bgcolor=#cccccc>
	<th colspan=2 rowspan=3>#</th>
	<th nowrap rowspan=3>Art/MCode<br>ARMS</th>
	<th nowrap rowspan=3>SKU Description</th>
	<th rowspan=3>Selling<br>UOM</th>
	{if $form.po_option eq '1'}
	<th colspan=2>Cost Price</th>
	{else}
	<th rowspan=3>Cost<br>Price</th>
	<th rowspan=3>Cost<br>Indicate</th>
	{/if}
	<th rowspan=3>Purchase<br>UOM</th>
	<!--th rowspan=2>PM<br>(%)</th-->
	<th nowrap colspan={count multi=5 var=$form.deliver_to}>Qty</th>
	<th rowspan=3>Total<br>Qty</th>
	<th rowspan=3>Total<br>FOC</th>
	<th rowspan=3>Gross<br>Amount</th>
	<th rowspan=3>Tax<br>(%)</th>
	<th rowspan=3>Discount<br>[<a href="javascript:void(discount_help())">?</a>]</th>
	<th rowspan=3>Nett<br>Amount</th>
	<th rowspan=3>Total<br>Selling</th>
	<th rowspan=3>Gross<br>Profit</th>
	<th rowspan=3>Profit(%)</th>
</tr>
<tr bgcolor=#cccccc>
{if $form.po_option eq '1'}
<th rowspan=2>HQ</th>
<th rowspan=2>Branch</th>
{/if}

{if $form.deliver_to}
{section name=i loop=$branch}
{if in_array($branch[i].id,$form.deliver_to)}
	<th colspan=3>{$branch[i].code}<br>
	<th colspan=2>FOC<br>
{/if}
{/section}
{/if}
</tr>

{if $form.deliver_to}
<tr bgcolor=#cccccc>
{section name=i loop=$branch}
{if in_array($branch[i].id,$form.deliver_to)}
	<th>S.P.</th>
	<th>Ctn</th>
	<th>Pcs</th>
	<th>Ctn</th>
	<th>Pcs</th>
{/if}
{/section}
{/if}
</tr>


{else}
<tr bgcolor=#cccccc>
	<th rowspan=2 colspan=2>#</th>
	<th rowspan=2 nowrap>Art/MCode<br>ARMS</th>
	<th rowspan=2 nowrap>SKU Description</th>
	<th rowspan=2>Suggested<br>Selling<br>Price</th>
	<th rowspan=2>Selling<br>UOM</th>
	<th rowspan=2>Cost<br>Price</th>
	<th rowspan=2>Cost<br>Indicate</th>
	<th rowspan=2>Purchase<br>UOM</th>
	<th colspan=2>Qty</th>
	<th colspan=2>FOC</th>
	{if $form.delivered}<th rowspan=2>Delivered</th>{/if}
	<th rowspan=2>Gross<br>Amount</th>
	<th rowspan=2>Tax<br>(%)</th>
	<th rowspan=2>Discount</th>
	<th rowspan=2>Nett<br>Amount</th>
	<th rowspan=2>Total<br>Selling</th>
	<th rowspan=2>Gross<br>Profit</th>
	<th rowspan=2>Profit(%)</th>
</tr>
<tr bgcolor=#cccccc>
	<th>Ctn</th>
	<th>Pcs</th>
	<th>Ctn</th>
	<th>Pcs</th>
</tr>
{/if}
</thead>

<tbody id="po_items">
<!-- if have items, load them -->
{foreach name=item from=$po_items item=item}
<tr height=24 bgcolor="{cycle name=c1 values="#ffffff,#eeeeee"}">
{include file=po.view.po_row.tpl}
</tr>
{/foreach}
</tbody>

<tfoot>
<!-- total -->
<tr bgcolor=#cccccc class=normal height=24>
{if $form.deliver_to}
	{if $form.po_option eq '1'}
	<th colspan=9 align=right>Total</th>
	{else}
	<th colspan=8 align=right>Total</th>
	{/if}
	<th align=right colspan={count multi=5 var=$form.deliver_to}>
	{*if $form.deliver_to}
		{section name=i loop=$branch}
			{if in_array($branch[i].id,$form.deliver_to)}
			{assign var=bid value=`$branch[i].id`}
			<th colspan=2 align=right id=qty[{$branch[i].code}]>{$total.qty_allocation[$bid]}</th>
			{/if}
		{/section}
	{/if*}
	<th align=right id=total_ctn>Ctn: {$total.ctn|qty_nf}</th>
	<th align=right id=total_pcs>Pcs: {$total.qty+$total.foc|qty_nf}</th>
{else}
	<th colspan=9 align=right>Total</th>
	<th colspan=2 align=right id=total_ctn>Ctn: {$total.ctn|qty_nf}</th>
	<th colspan=2 align=right id=total_pcs>Pcs: {$total.qty+$total.foc|qty_nf}</th>
	{if $form.delivered}<th>&nbsp;</th>{/if}
{/if}
	<th align=right id=total_gross_amount>{$total.gamount|number_format:2}</th>
	<th align=right colspan=3 id=total_amount>{$total.amount|number_format:2}</th>
	<th align=right id=total_sell>{$total.sell|number_format:2}</th>
	{assign var=total_profit value=$total.sell-$total.amount}
	<th align=right id=total_profit {if $total_profit<=0}style="color:#f00"{/if}>{$total_profit|number_format:2}</th>
	<th align=right id=total_margin {if $total_profit<=0}style="color:#f00"{/if}>{if $total.sell<=0}-{else}{$total_profit/$total.sell*100|number_format:2}%{/if}</th>
</tr>

<!-- misc cost -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
<td colspan=5 rowspan=7 style="border:1px solid #999;padding:10px" valign=top>
<!--- remark box -->
<b>Remark:</b><br>
{$form.remark|nl2br}
</td>
<td colspan=4 rowspan=7 style="border:1px solid #999;padding:10px" valign=top>
<b>Remark#2 (For Internal Use):</b><br>
{$form.remark2|nl2br}
</td>
{if $form.deliver_to}
	<th align=right colspan={count var=$form.deliver_to multi=4 offset=3}>
{else}
	<th align=right colspan=6>
{/if}
    	Misc Cost
    </th>
    <th align=right colspan=2>
		{$form.misc_cost|number_format:$config.global_cost_decimal_points|default:"-"}
	</th>
</tr>

<!-- final discount -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
{if is_array($form.deliver_to)}
	<th align=right colspan={count var=$form.deliver_to multi=4 offset=3}>
{else}
	<th align=right colspan=6>
{/if}
		Discount
	</th>
	<th align=right colspan=2>
		{$form.sdiscount|default:"-"}
	</th>
</tr>


<!-- "special" discount -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
{if is_array($form.deliver_to)}
	<th align=right colspan={count var=$form.deliver_to multi=4 offset=3}>
{else}
	<th align=right colspan=6>
{/if}
		Discount from Remark#2
	</th>
	<th align=right colspan=2>
		{$form.rdiscount|default:"-"}
	</th>
</tr>

<!-- "special" discount -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
{if is_array($form.deliver_to)}
	<th align=right colspan={count var=$form.deliver_to multi=4 offset=3}>
{else}
	<th align=right colspan=6>
{/if}
		Deduct Cost from Remark#2
	</th>
	<th align=right colspan=2>
		{$form.ddiscount|default:"-"}
	</th>
</tr>


<!-- transportation cost -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
{if is_array($form.deliver_to)}
	<th align=right colspan={count var=$form.deliver_to multi=4 offset=3}>
{else}
	<th align=right colspan=6>
{/if}
    	Transportation Charges
    </th>
    <th align=right colspan=2>
		{$form.transport_cost|number_format:$config.global_cost_decimal_points|default:"-"}
	</th>
</tr>

<!-- total amount -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
{if is_array($form.deliver_to)}
	<th align=right colspan={count var=$form.deliver_to multi=4 offset=3}>
{else}
	<th align=right colspan=6>
{/if}
		PO Amount
	</th>
	<th align=right colspan=2 id=final_amount class=large>
	{$total.final_amount|number_format:2}
	</th>
	{assign var=final_profit value=$total.sell-$total.final_amount}
	<th align=right colspan=2 id=final_profit class=large style="{if $final_profit<=0}color:#f00{/if}">
	{$final_profit|number_format:2}
	</th>
	<th align=right id=final_margin class=large style="{if $final_profit<=0}color:#f00{/if}">
	{if $total.sell}
	{$final_profit/$total.sell*100|number_format:2}%
	{/if}
	</th>
</tr>

<!-- supplier amount -->
<tr class=normal>
{if is_array($form.deliver_to)}
	<th align=right colspan={count var=$form.deliver_to multi=4 offset=3}>
{else}
	<th align=right colspan=6>
{/if}
		Supplier PO Amount
	</th>
	<th align=right colspan=2 id=final_amount2 class=large>
	{$total.final_amount2|number_format:2}
	</th>
</tr>

</tfoot>
</table>



