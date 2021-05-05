<div style="height:80%;border:2px inset black;background-color:#fff;overflow-y:auto;">
	<form name="f_sku_group_item" onSubmit="return false;" id="f_sku_group_item">
		<table width="100%" class="report_table">
			<tr class="header">
				<th width="20"><input type="checkbox" id="chx_toggle_all_sku_group_item" onChange="SALES_ORDER.toggle_all_sku_group_item();" /></th>
				<th>ARMS Code</th>
				<th>MCode</th>
				<th>Art No</th>
				<th>Description</th>
			</tr>
			{foreach from=$item_list item=r}
				<tr>
					<td align="center">
						<input type="checkbox" name="sku_group_item[{$r.sid}]" class="chx_sku_group_item" value="{$r.sid}" />
					</td>
					<td>{$r.sku_item_code}</td>
					<td>{$r.mcode|default:'-'}</td>
					<td>{$r.artno|default:'-'}</td>
					<td>{$r.description|default:'-'}</td>
				</tr>
			{/foreach}
		</table>
	</form>	
</div>

<div align="center">
	<br />
	<input type="button" value="Add" onClick="SALES_ORDER.add_sku_group_item_clicked();" />
</div>


