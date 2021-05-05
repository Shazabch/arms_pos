{*
*}
<h3>Result Status:</h3>
<p style="color:blue;">
	{if $result.updated_row}
		Total {$result.updated_row} of {$result.ttl_row} item(s) will be updated.<br />
	{/if}
	{if $result.error_row > 0}
		Total {$result.error_row} of {$result.ttl_row} item(s) will fail to update due to some error found, please check the error message at the end of <span style="color:red">highlighted</span> row.<br />
		Additionally, click <a id="invalid_link" href="attachments/update_sku_{$method}/invalid_{$file_name}" download>HERE</a> to download and view the invalid data.<br />
	{/if}
	* Please ENSURE the result data is fill to the header accordingly before proceed to update.<br />
	<br/>
	<input type="button" id="update_btn" name="update_btn" value="Update" onclick="UPDATE_MODULE.update_sku('{$method}');" {if !$result.updated_row}disabled{/if} />&nbsp;&nbsp;
	<input type="checkbox" name="force_upd_price_type" id="force_upd_price_type" value="1" {if $smarty.request.force_upd_price_type}checked{/if} />&nbsp;<font color="black"><b>Also Update to Latest Price Type (will update ALL BRANCHES to use the Price Type)</font></b>
</p>
<div class="div_tbl">
	<table id="si_tbl" width="100%">
		<tr bgcolor="#ffffff">
			<th>#</th>
			{foreach from=$item_header item=i}
				<th>{$i}</th>
			{/foreach}
		</tr>
		<tbody>
		{foreach from=$item_lists item=i name=brand}
			<tr class="{if $i.error}tr_error{/if}">
				<td>{$smarty.foreach.brand.iteration}.</td>
				{foreach from=$i key=k item=r}
					<td>{$r}</td>
				{/foreach}
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>