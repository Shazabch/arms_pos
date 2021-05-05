<h3 align="center">Transaction Details</h3>
{if !$tran_details}
No Data
{else}
<div class="div_content">
<table width="100%" class="popup_table">
<tr align="left">
	<th>Transaction time</th>
	<th>Cashier</th>
	<th>Receipt No.</th>
	<th class="r">Amount</th>
	<th width="15">&nbsp;</th>
</tr>
<tbody {if count($tran_details)>18} style="height:350;overflow-y:auto;overflow-x:hidden;"{/if}>
{foreach from=$tran_details item=r}
<tr>
	<td>{$r.pos_time|default:'-'}</td>
	<td>{$r.u|default:'-'}</td>
	<td>
	<a href="javascript:items_details('{$r.branch_id}','{$r.counter_id}','{$r.id}','{$r.date}')">
	{$r.receipt_no|default:'-'}
	</a>{if $r.cancel_status eq 1}<span class="small">(Cancelled)</span>{/if}
	</td>
	<td class="r">{$r.amount|number_format:2}</td>
</tr>
{/foreach}
</tbody>
</table>
</div>
{/if}
