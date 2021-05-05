<table width="100%" class="report_table">
	<tr class="header">
		<th>Package Doc No.</th>
		<th>Package Title</th>
		
		{foreach from=$rate_list key=rate item=v}
			<th>{$v}</th>
		{/foreach}
		
		<th>Total Star</th>
		<th>Rating Count</th>
		<th>Average Star</th>
	</tr>
	
	{foreach from=$data.by_package key=package_unique_id item=p}
		<tr>
			<td align="center">{$p.doc_no}</td>
			<td>{$p.mp_title}</td>
			
			{foreach from=$rate_list key=rate item=v}
				<td align="right">{$p.rate_list.$rate}</td>
			{/foreach}
			
			<td align="right">{$p.total_rate}</td>
			<td align="right">{$p.rate_count}</td>
			<td align="right">{$p.avg_rate}</td>
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