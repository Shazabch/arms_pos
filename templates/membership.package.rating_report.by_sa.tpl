<table width="100%" class="report_table">
	<tr class="header">
		<th>Sales Agent Code</th>
		<th>Sales Agent Name</th>
		
		{foreach from=$rate_list key=rate item=v}
			<th>{$v}</th>
		{/foreach}
		
		<th>Total Star</th>
		<th>Rating Count</th>
		<th>Average Star</th>
	</tr>
	
	{foreach from=$data.by_sa key=sa_id item=r}
		<tr>
			<td align="center">{$sa_list.$sa_id.code}</td>
			<td>{$sa_list.$sa_id.name}</td>
			
			{foreach from=$rate_list key=rate item=v}
				<td align="right">{$r.rate_list.$rate}</td>
			{/foreach}
			
			<td align="right">{$r.total_rate}</td>
			<td align="right">{$r.rate_count}</td>
			<td align="right">{$r.avg_rate}</td>
		</tr>
	{/foreach}
	
	<tr class="header">
		<td colspan="2" align="right"><b>Total</b></td>
		
		{foreach from=$rate_list key=rate item=v}
			<td align="right">{$data.total.rate_list.$rate}</td>
		{/foreach}
		
		<td align="right">{$data.total.total_rate}</td>
		<td align="right">{$data.total.rate_count}</td>
		<td align="right">{$data.total.avg_rate}</td>
	</tr>
</table>