{*
*}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">Result Status:</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<div class="alert alert-primary mx-3 rounded">
	
	{if $result.updated_row}
		Total {$result.updated_row} of {$result.ttl_row} item(s) will be updated.<br />
	{/if}
	{if $result.error_row > 0}
		Total {$result.error_row} of {$result.ttl_row} item(s) will fail to update due to some error found, please check the error message at the end of <span style="color:red">highlighted</span> row.<br />
		Additionally, click <a id="invalid_link" href="attachments/update_sku_po_reorder_qty/invalid_{$file_name}" download>HERE</a> to download and view the invalid data.<br />
	{/if}
	* Please ENSURE the result data is fill to the header accordingly before proceed to update.<br />

</div>

<p>
	<b><u class="form-label">Filter Options:</u></b><br />
	<div>
		<b class="form-label">Branch Selected: </b>
		{foreach from=$form.branch_list key=bid item=dummy name=b}
			<input class="form-control" type="hidden" name="branch_list[{$bid}]" value="1" /> <b>{$branch_list.$bid}</b>{if !$smarty.foreach.b.last}, {/if}
		{/foreach}
	</div>
	<br />
	
	<input type="button" class="btn btn-primary" id="update_btn" name="update_btn" value="Update" onclick="IMPORT_QUOTATION_COST_MODULE.update_quotation_cost(1);" {if !$result.updated_row}disabled{/if} />
</p>
<div class="div_tbl">
	<div class="table-responsive">
		<table id="si_tbl" width="100%" class=" table mb-0 text-md-nowrap  table-hover">
			<thead class="bg-gray-100">
				<tr >
					<th>#</th>
					{foreach from=$item_header item=i}
						<th>{$i}</th>
					{/foreach}
				</tr>
			</thead>
			<tbody class="fs-08">
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
</div>