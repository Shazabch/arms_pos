<table width="100%" class="report_table">
	<tr class="header">
		<th width="20%">Vendor Code</th>
		<th>Vendor Description</th>
		<th width="15%">Quotation Cost</th>
	</tr>
	{foreach from=$data key=vid item=r}
		<tr>
			<td>{$r.vd_code}</td>
			<td>{$r.vd_desc}</td>
			<td align="right">{$r.cost|number_format:$config.global_cost_decimal_points}</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="3">- No Record Found -</td>
		</tr>
	{/foreach}
</table>