{*
3/21/2014 4:14 PM Justin
- Enhanced to show custom payment type label if found it is set.
*}

<h3 align="center">Payment Details</h3>
{if !$payment_details}
No Data
{else}
<table width="100%" class="popup_table">
<tr align="left">
	<th>Payment Type</th>
	<th class="r">Amount</th>
</tr>
{foreach from=$payment_details key=type item=r}
<tr>
	<td>{$pos_config.payment_type_label.$type|default:$type}</td>
	<td class="r">{$r.amount|number_format:2}</td>
</tr>
{/foreach}
</table>
{/if}
