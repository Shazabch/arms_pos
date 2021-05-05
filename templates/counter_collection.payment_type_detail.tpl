{*
3/21/2014 4:14 PM Justin
- Enhanced to show custom payment type label if found it is set.
*}

<h1>Payment Type Detail</h1>
<div style="overflow:auto;height:350px;">
<table class="tb" width=100% cellpadding=4 cellspacing=0 border=0>
<tr class=header style="background:#fe9">
<th>Receipt No</th>
<th>Payment Type</th>
<th>Payment Amount</th>
</tr>
{assign var=total_amount value=0}
{assign var=adjusted_total_amount value=0}
{foreach from=$items item=item}
<tr {if $item.adjust == 1}class=strike style="color:#f00;"{elseif $item.changed == 1} style="color:#00f;"{/if}>
<td>{receipt_no_prefix_format branch_id=$item.branch_id counter_id=$item.counter_id receipt_no=$item.receipt_no}</td>
<td>
	{assign var=pt value=$item.type}
	{$pos_config.payment_type_label.$pt|default:$pt}
</td>
<td align=right>{$item.payment_amount|number_format:2}</td>
</tr>
{if $item.changed == 0}{assign var=total_amount value=$total_amount+$item.payment_amount}{/if}
{if $item.adjust == 0}{assign var=adjusted_total_amount value=$adjusted_total_amount+$item.payment_amount}{/if}
{/foreach}
<tr style="background:#ffc">
<td colspan=2>Total</td>
<td align=right>{$total_amount|number_format:2}</td>
</tr>
<tr style="background:#fe9;">
<td colspan=2>Adjusted Total</td>
<td align=right>{$adjusted_total_amount|number_format:2}</td>
</tr>
</table>
</div>
