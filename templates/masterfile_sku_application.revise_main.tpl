{include file="header.tpl"}

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<ul>
	{if !$config.single_server_mode and $BRANCH_CODE ne 'HQ'}
		<li style="color:red;"> Data may different with HQ due to the MySQL replicate. </li>
	{/if}
</ul>
<div class="card mx-3">
	<div class="card-body">
		
<div class="table-responsive">
	<table width="100%"  class=" report_table report_table table mb-0 text-md-nowrap  table-hover">
		<thead class="bg-gray-100">
			<tr class="header">
				<th colspan="2">&nbsp;</th>
				<th>ID</th>
				<th>Apply Branch</th>
				<th>Department</th>
				<th>Category</th>
				<th>Vendor</th>
				<th>Brand</th>
				<th>Owner</th>
				<th>Last Update</th>
			</tr>
		</thead>
		{foreach from=$sku_list item=sku name=f}
		<tbody class="fs-08">
			<tr>
				<td align="right">{$smarty.foreach.f.iteration}.</td>
				<td>
					{if $sessioninfo.id eq $sku.apply_by and $sessioninfo.branch_id eq $sku.apply_branch_id}
						<a href="?a=revise&id={$sku.id}">
							<img src="ui/ed.png" align="absmiddle" title="Revise" />
						</a>
					{else}
						<a href="?a=view&id={$sku.id}">
							<img src="ui/view.png" align="absmiddle" title="View" />
						</a>
					{/if}
				</td>
				<td align="center">{$sku.id}</td>
				<td>{$sku.apply_branch_code|default:'-'}</td>
				<td>{$sku.dept_desc|default:'-'}</td>
				<td>{$sku.cat_desc|default:'-'}</td>
				<td>{$sku.vendor_desc|default:'-'}</td>
				<td>{$sku.brand_desc|default:'UN-BRANDED'}</td>
				<td>{$sku.user_u|default:'-'}</td>
				<td align="center">{$sku.timestamp}</td>
			</tr>
		</tbody>
		{foreachelse}
			<tbody class="fs-08">
				<tr>
					<td colspan="10">* No Data *</td>
				</tr>
			</tbody>
		{/foreach}
	</table>
	
</div>

	</div>
</div>
{include file="footer.tpl"}
