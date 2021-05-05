<table width="100%" class="report_table" style="background-color: #fff;">
	<tr class="header">
		<th>Timestamp</th>
		<th>Price</th>
		<th>User</th>
	</tr>
	{foreach from=$sku_price_his item=r}
		<tr>
			<td align="center">{$r.added}</td>
			<td align="right">{$r.price|number_format:2}</td>
			<td align="center">{$r.u}</td>
		</tr>
	{/foreach}
</table>