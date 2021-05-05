{*
REVISION HISTORY:
++++++++++++++++++

10/23/2007 4:45:01 PM gary
- add cost indicate column.

*}

{if $sheet_n eq '' and $sheet_n ne '0'}<h1>Error - sheet_n not defined</h1>{/if}
<div id="sheet[{$sheet_n}]" style="margin-bottom:10px; z-index:0;">

<!--div style="float:right"><img src=ui/del.png align=absmiddle> <a href="javascript:void(cancel_sheet('sheet[{$sheet_n}]'))">remove sheet</a></div-->

<!--h3>PO Sheet #{$sheet_n+1}</h3-->

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
{include file=purchase_order.view.po_row.tpl}
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
			<th colspan=2 align=right id=qty[{$sheet_n}][{$branch[i].code}]>{$total[$sheet_n].qty_allocation[$bid]}</th>
			{/if}
		{/section}
	{/if*}
	<th align=right id=total_ctn[{$sheet_n}]>Ctn: {$total[$sheet_n].ctn|number_format}</th>
	<th align=right id=total_pcs[{$sheet_n}]>Pcs: {$total[$sheet_n].qty+$total[$sheet_n].foc|number_format}</th>
{else}
	<th colspan=9 align=right>Total</th>
	<th colspan=2 align=right id=total_ctn[{$sheet_n}]>Ctn: {$total[$sheet_n].ctn|number_format}</th>
	<th colspan=2 align=right id=total_pcs[{$sheet_n}]>Pcs: {$total[$sheet_n].qty+$total[$sheet_n].foc|number_format}</th>
	{if $form.delivered}<th>&nbsp;</th>{/if}
{/if}
	<th align=right id=total_gross_amount[{$sheet_n}]>{$total[$sheet_n].gamount|number_format:2}</th>
	<th align=right colspan=3 id=total_amount[{$sheet_n}]>{$total[$sheet_n].amount|number_format:2}</th>
	<th align=right id=total_sell[{$sheet_n}]>{$total[$sheet_n].sell|number_format:2}</th>
	{assign var=total_profit value=$total[$sheet_n].sell-$total[$sheet_n].amount}
	<th align=right id=total_profit[{$sheet_n}] {if $total_profit<=0}style="color:#f00"{/if}>{$total_profit|number_format:2}</th>
	<th align=right id=total_margin[{$sheet_n}] {if $total_profit<=0}style="color:#f00"{/if}>{if $total[$sheet_n].sell<=0}-{else}{$total_profit/$total[$sheet_n].sell*100|number_format:2}%{/if}</th>
</tr>

<!-- misc cost -->
<tr class=normal>
{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
<td colspan=5 rowspan=7 style="border:1px solid #999;padding:10px" valign=top>
<!--- remark box -->
<b>Remark:</b><br>
{$form.remark[$sheet_n]|nl2br}
</td>
<td colspan=4 rowspan=7 style="border:1px solid #999;padding:10px" valign=top>
<b>Remark#2 (For Internal Use):</b><br>
{$form.remark2[$sheet_n]|nl2br}
</td>
{if $form.deliver_to}
	<th align=right colspan={count var=$form.deliver_to multi=4 offset=3}>
{else}
	<th align=right colspan=6>
{/if}
    	Misc Cost
    </th>
    <th align=right colspan=2>
		{$form.misc_cost[$sheet_n]|default:"-"}
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
		{$form.sdiscount[$sheet_n]|default:"-"}
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
		{$form.rdiscount[$sheet_n]|default:"-"}
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
		{$form.ddiscount[$sheet_n]|default:"-"}
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
		{$form.transport_cost[$sheet_n]|default:"-"}
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
	<th align=right colspan=2 id=final_amount[{$sheet_n}] class=large>
	{$total[$sheet_n].final_amount|number_format:2}
	</th>
	{assign var=final_profit value=$total[$sheet_n].sell-$total[$sheet_n].final_amount}
	<th align=right colspan=2 id=final_profit[{$sheet_n}] class=large style="{if $final_profit<=0}color:#f00{/if}">
	{$final_profit|number_format:2}
	</th>
	<th align=right id=final_margin[{$sheet_n}] class=large style="{if $final_profit<=0}color:#f00{/if}">
	{if $total[$sheet_n].sell}
	{$final_profit/$total[$sheet_n].sell*100|number_format:2}%
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
	<th align=right colspan=2 id=final_amount2[{$sheet_n}] class=large>
	{$total[$sheet_n].final_amount2|number_format:2}
	</th>
</tr>

</tfoot>
</table>

</div>

