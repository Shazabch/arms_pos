{*
5/13/2015 11:04 AM Andy
- Enhance to show the table if got the gst summary.

3/31/2017 2:51 PM Justin
- Enhanced to have new class to control no border.
*}

{if $gst_summary_list}
	<table {if $gst_tbl_no_border}class="gst_tbl_no_border"{/if}>
		<tr>
			<th>Code</th>
			<th align="right" width="80">Amount</th>
			<th align="right" width="80">GST</th>
		</tr>
		{foreach from=$gst_summary_list item=r}
			<tr>
				<td>{$r.code} @{$r.rate}%</td>
				<td align="right">{$r.amount|number_format:2}</td>
				<td align="right">{$r.gst_amt|number_format:2}</td>
			</tr>
		{/foreach}
	</table>
{/if}