<table width="100%" class="report_table" style="background-color: #fff;">
	<tr class="header">
		<th>ARMS Code</th>
		<th>MCode</th>
		<th>Art No</th>
		<th>{$config.link_code_name}</th>
		<th>Description</th>
	</tr>
	{foreach from=$item_list item=r}
		<tr>
			<td align="center">{$r.sku_item_code}</td>
			<td align="center">{$r.mcode|default:'-'}</td>
			<td align="center">{$r.artno|default:'-'}</td>
			<td align="center">{$r.link_code|default:'-'}</td>
			<td>{$r.description|default:'-'}</td>
		</tr>
	{foreachelse}
		<tr>
			<td align="center" colspan="5">
				No Data
			</td>
		</tr>
	{/foreach}
</table>

Maximum show {$sample_sku_limit} sample sku.