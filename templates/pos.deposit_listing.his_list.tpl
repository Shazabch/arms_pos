{*
3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.
*}

<h2>Deposit History</h2>

<table width="100%" class="report_table" style="background-color: #fff;">
	<tr class="header">
		<th>Type</th>
		<th>Date</th>
		<th>Receipt</th>
		<th>Timestamp</th>
	</tr>
	
	{foreach from=$his_list item=r}
		<tr>
			{* Type *}
			<td align="center">
				{if $r.type eq 'RECEIVED'}
					Received
				{elseif $r.type eq 'USED'}
					Used
				{elseif $r.type eq 'CANCEL_RCV' || $r.type eq 'CANCEL_USED'}
					Cancelled
				{/if}
			</td>
			
			{* Date *}
			<td align="center">
				{if $r.type eq 'RECEIVED'}
					{$r.deposit_pos_date}
				{elseif $r.type eq 'USED'}
					{$r.pos_date}
				{elseif $r.type eq 'CANCEL_RCV' || $r.type eq 'CANCEL_USED'}
					{$r.cancel_date}
				{/if}
			</td>
			
			{* Receipt *}
			<td align="center">
				{if $r.type eq 'RECEIVED'}
					{receipt_no_prefix_format branch_id=$r.branch_id counter_id=$r.counter_id receipt_no=$r.deposit_receipt_no}
				{elseif $r.type eq 'USED'}
					{receipt_no_prefix_format branch_id=$r.branch_id counter_id=$r.counter_id receipt_no=$r.receipt_no}
				{elseif $r.type eq 'CANCEL_RCV'}
					{if $r.receipt_no}
						{receipt_no_prefix_format branch_id=$r.branch_id counter_id=$r.counter_id receipt_no=$r.receipt_no}
					{else}
						-
					{/if}
				{elseif $r.type eq 'CANCEL_USED'}
					-
				{/if}
			</td>
			
			{* Timestamp *}
			<td align="center">
				{$r.added}
			</td>
		</tr>
		
	{/foreach}
</table>
