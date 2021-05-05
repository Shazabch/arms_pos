{*
2/1/2013 3:56 PM Fithri
- mix and match promotion change to no need config, always have for all customer

7/4/2013 2:36 PM Andy
- Enhance to show cancel at backend in transaction list.

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

05/06/2016 17:00 Edwin
- Add new table column "Receipt Remark" at Transaction Details.
- Show member name and card number when transaction type is "Member".

10/17/2016 11:19 AM Qiu Ying
- Bug fixed on transaction detail report show round up quantity instead of actual quantity

3/2/2017 9:59 AM Justin
- Enhanced to include deposit deduction for receipt amount.

4/19/2017 4:33 PM Justin
- Enhanced to show Receipt Ref No. column.
*}

<tr class="thover tr_pos_row" >
    <td>{$row_no}</td>
    <td><a href="javascript:void(POS_TRAN_DETAILS.items_details('{$p.branch_id}','{$p.counter_id}','{$p.id}','{$p.date}'))">{receipt_no_prefix_format branch_id=$p.branch_id counter_id=$p.counter_id receipt_no=$p.receipt_no}</a></td>
    <td><a href="javascript:void(POS_TRAN_DETAILS.items_details('{$p.branch_id}','{$p.counter_id}','{$p.id}','{$p.date}'))">{$p.receipt_ref_no}</a></td>
    <td>{$p.date}</td>
    <td>{$p.network_name}</td>
    <td>{$p.u}</td>
    <td>{$p.pos_time|date_format:'%H:%M:%S'}</td>
    <td>{$p.type}</td>
    <td>
		{if !$p.cancel_status}
			Valid 
		{else}
			{if $p.prune_status && $p.cancel_status}
				Pruned
			{else}
				Cancelled{if $p.cancel_at_backend} <sup class="sup_cancel_at_backend" title="Cancel at backend">@backend</sup>{/if}
			{/if}
			{if $p.cancelled_by_u}
				<br /><span class="small" style="color:blue;">(by {$p.cancelled_by_u})</span>
			{/if}
		{/if}</td>
    <td>
		{if $p.member_no}
			Member<br/>
			<span class="small" style="white-space:nowrap;color:blue;">
				<span>{$p.member_no}</span><br/>
				<span>{$p.member_name|default:'-'}</span>
			</span>
		{else}
			Non-member
		{/if}
	</td>
    <td>{if $p.got_goods_return}Yes{else}No{/if}</td>
    <td class=r>{$p.open_price|number_format:2}</td>
	<td class="r">{if $p.got_mm_discount}Yes{else}No{/if}</td>
	<td>
		{foreach from=$p.receipt_remark item=rr}
			{$rr.value}<br>
		{/foreach}
	</td>
    {if $smarty.request.payment_type ne 'all'}<td class=r>{$p.payment_amount|number_format:2}</td>{/if}
	<td class="r"><span class="span_pos_qty">{$p.total_qty|qty_nf}</span></td>
    <td class="r">
		<span class="span_pos_amt">{$p.amount-$p.deposit_amount|number_format:2}</span>
		{if $p.deposit_amount}
			<br />
			<font class="small" color="red">Deposit {$p.deposit_amount|number_format:2}</font>
		{/if}
	</td>
</tr>