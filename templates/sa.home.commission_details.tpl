{if $table}
	{if $total}
		<div style="float:right;">
			<span style="font-size:48px;">Total Commission:</span><span class="comm_amt">&nbsp;&nbsp;{$total.commission_amt|number_format:2}</span>
		</div>
	{/if}
	<b>* The following are base on finalised sales only</b>
	{foreach from=$table name=s_a key=bid item=sa}
		<h1>{$branches.$bid.code} - {$branches.$bid.description}</h1>
		<table class="rpt_table" width="100%" cellspacing="0" cellpadding="0" id="report_tbl">
			<tr class="header">
				<th>MTD Sales</th>
				<th>Target Sales <br />Amount</th>
				<th>Balance to <br />Achieve</th>
				<th>Durations to <br />Achieve</th>
				<th>Average Sales <br />need to<br />Achieve / Day</th>
				<th>Commission Amount</th>
			</tr>
			
			<tr>
				<td class="r">{$sa.sales_amt|number_format:2}</td>
				<td align="right">{$sa.target_sales_amt|default:0|number_format:2}</td>
				<td align="center">
					{assign var=achieve_bal_amt value=$sa.target_sales_amt-$sa.sales_amt}
					{if $achieve_bal_amt <= 0}
						Over
					{else}
						<div class="clr_red" align="right">{$achieve_bal_amt|number_format:2}</div>
					{/if}
				</td>
				<td align="right">
					{if $achieve_bal_amt <= 0}
						<div align="center">-</div>
					{elseif $sa.remaining_days < 0}
						0
					{else}
						{$sa.remaining_days|number_format:0}
					{/if}
				</td>
				<td align="right">
					{if $achieve_bal_amt <= 0}
						<div align="center">-</div>
					{elseif $sa.remaining_days < 0}
						{$achieve_bal_amt|number_format:2}
					{else}
						{if $sa.remaining_days eq 0}
							{assign var=avg_ach_bal_amt value=$achieve_bal_amt}
						{else}
							{assign var=avg_ach_bal_amt value=$achieve_bal_amt/$sa.remaining_days}
						{/if}
						{$avg_ach_bal_amt|number_format:2}
					{/if}
				</td>
				<td class="r comm_amt">{$sa.commission_amt|number_format:2}</td>
			</tr>
		</table>
	{/foreach}
{else}
	-- No Data --
{/if}