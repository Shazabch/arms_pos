<table class="report_table" width="100%" id="tbl_item_list">
	<tr class="header">
		<th colspan="2">&nbsp;</th>
		<th>Requesting Qty</th>
		{if $config.do_request_show_sku_photo}
			<th>Photos</th>
		{/if}
		<th>ARMS Code</th>
		<th>MCode</th>
		<th>Art No</th>
		<th>{$config.link_code_name}</th>
		<th>Description</th>
		<th>Added Time</th>
	</tr>
	{foreach from=$item_list key=sid item=r name=fs}
		<tr class="tr_item">
			<td width="30" align="right">{$smarty.foreach.fs.iteration}.</td>
			<td align="center">
				<input type="input" name="item_qty[{$sid}]" class="chx_item_qty" onChange="ADVANCED_ADD.item_qty_changed('{$sid}');" style="width:50px;text-align:right;" />
			</td>
			<td align="right">{$r.requesting_qty|qty_nf}</td>
			{if $config.do_request_show_sku_photo}
				<td align="center">
					<div>
						{show_sku_photo sku_item_id=$sid container_id="sku_photo_`$sid`" show_as_first_image=1}
					</div>
				</td>
			{/if}
			<td align="center">{$r.sku_item_code}</td>
			<td align="center">{$r.mcode}</td>
			<td align="center">{$r.artno}</td>
			<td align="center">{$r.link_code}</td>
			<td>{$r.description}</td>
			<td align="center" nowrap>{$r.added}</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="10">No Data</th>
		</tr>
	{/foreach}
</table>