<h3>Result Status:</h3>
<p style="color:blue;">
	{if $result.import_row}
		Total {$result.import_row} of {$result.ttl_row} item(s) will be imported.<br />
	{/if}
	{if $result.error_row > 0}
		Total {$result.error_row} of {$result.ttl_row} item(s) will fail to import due to some error found, please check the error message at the end of <span style="color:red">highlighted</span> row.<br />
	{/if}
	* Please ENSURE the result data is fill to the header accordingly before proceed to import.<br />
	<br/>
	<input type="button" id="import_btn" name="import_btn" value="Import" onclick="ADJUSTMENT_UPLOAD_CSV.import_adjustment({$method});" {if !$result.import_row}disabled{/if} />
	<div id="div_reload_import"></div>
</p>

<div class="div_tbl">
	<table id="si_tbl" width="100%">
		<tr bgcolor="#ffffff">
			<th><input type="checkbox" id="" onclick="ADJUSTMENT_UPLOAD_CSV.check_all_item(this);" /></th>
			<th>#</th>
			{foreach from=$item_header item=i}
				<th>{$i}</th>
			{/foreach}
		</tr>
		<tbody>
		{foreach from=$item_lists item=i name=adj}
			<tr class="{if $i.error}tr_error{/if}">
				<td align="center"><input id="adj_items_list-{$smarty.foreach.adj.iteration}" type="checkbox" name="adj_tmp_item[]" value="{$smarty.foreach.adj.iteration}" {if $i.error}disabled{else}checked{/if} /></td>
				<td align="center">{$smarty.foreach.adj.iteration}.</td>
				{foreach from=$i key=k item=r}
					<td>{$r}</td>
				{/foreach}
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>
<br />
