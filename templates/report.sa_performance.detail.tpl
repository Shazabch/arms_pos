{*
5/12/2014 2:27 PM Justin
- Enhanced to have total qty column.

12/20/2017 5:26 PM Justin
- Enhanced the report to show details from sales cache instead of real time data.
- Enhanced to have KPI Performance Summary.
*}

{foreach from=$table key=date item=sa}
	<tr bgcolor="#eceece" class="dtl_{$sa_id}_{$bid}_{$year}{$month}">
		<td>{$date}</td>
		<td class="r">{$sa.curr_sales_qty|qty_nf}</td>
		<td class="r">{$sa.curr_sales_amt|number_format:2}</td>
		<td align="right">
			{if $target_sales_amt > 0}{$target_sales_amt|number_format:2}{else}0.00{/if}
		</td>
		<td align="center">
			{assign var=achieve_bal_amt value=$target_sales_amt-$sa.curr_sales_amt}
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
	</tr>
	{assign var=target_sales_amt value=$target_sales_amt-$sa.curr_sales_amt}
{/foreach}