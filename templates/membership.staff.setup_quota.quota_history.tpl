<table width="100%" class="report_table" style="background-color:#fff;">
	<tr class="header">
		<th>Timestamp</th>
		<th>Updated by</th>
		<th>Quota Amount</th>
	</tr>
	
	{foreach from=$quota_history_list item=r}
		<tr>
			<td>{$r.added_timestamp}</td>
			<td>{$r.update_by|default:'-'}</td>
			<td align="right">{$r.quota_value}</td>
		</tr>
	{/foreach}
</table>
