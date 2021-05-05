{*
10/8/2010 4:54:07 PM Andy
- Fix wrong pos time minute.
*}

{literal}
<style>
.item_table{
	border-collapse:collapse;
}
.item_table tr{
    border-right:1px black solid;
	border-top:1px black solid;
}
.item_table th,.item_table td{
	border-left:1px black solid;
	border-bottom:1px black solid;
	padding-left:5px;
}
</style>
{/literal}

<div>{$receipt_detail.counter_id} / {$receipt_detail.u} / {$receipt_detail.receipt_no} - {$receipt_detail.pos_time|date_format:'%Y-%m-%d %I:%M%p'}</div>
{if !$item_details}
No Data
{else}
<div class="div_content">
{if $receipt_detail.member_no}Membership No.: {$receipt_detail.member_no}{/if}
<table width="100%" class="item_table" cellpadding="2">
<tr class="header">
	<th rowspan="2">ARMS Code</th>
	<th rowspan="2">MCode</th>
	<th rowspan="2">Description</th>
	<th rowspan="2">Qty</th>
	<th colspan="3" style="text-align:center;">Price</th>
</tr>
<tr class="header">
	<th class="r">Actual</th>
    <th class="r">Discount</th>
	<th class="r">Selling</th>
</tr>
{foreach from=$item_details item=r}
<tr>
	<td>{$r.sku_item_code|default:'-'}</td>
	<td>{$r.mcode|default:'-'}</td>
	<td>{$r.description|default:'-'}</td>
	<td class="r">{$r.qty|number_format|default:'-'}</td>
	<td class="r">{$r.price|number_format:2|ifzero:'-'}</td>
	<td class="r">{$r.discount|number_format:2|ifzero:'-'}{if $r.discount_per}({$r.discount_per|number_format:1|ifzero:'-'}){/if}</td>
	<td class="r">{$r.selling_price|number_format:2|ifzero:'-'}</td>
</tr>
{/foreach}
<tr>
	<td colspan="3" class="r"><b>Total</b></td>
	<td class="r">{$total.qty|number_format}</td>
	<td class="r">{$total.price|number_format:2|ifzero:'-'}</td>
	<td class="r">{$total.discount|number_format:2|ifzero:'-'}{if $total.discount_per}({$total.discount_per|number_format:1|ifzero:'-'}){/if}</td>
	<td class="r">{$total.selling_price|number_format:2|ifzero:'-'}</td>
</tr>
</table>
</div>
{/if}
