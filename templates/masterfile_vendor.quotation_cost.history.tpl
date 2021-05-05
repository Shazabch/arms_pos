<table width="100%" class="report_table" style="background-color: #fff;">
	<tr class="header">
		<th>Timestamp</th>
		<th>Cost</th>
		<th>User</th>
	</tr>
	{foreach from=$sku_cost_his item=r}
		<tr>
			<td align="center">{$r.added}</td>
			<td align="right">{$r.cost|number_format:$config.global_cost_decimal_points}</td>
			<td align="center">{$r.u}</td>
		</tr>
	{/foreach}
</table>