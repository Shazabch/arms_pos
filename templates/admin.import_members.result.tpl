<div class="card mx-3">
	<div class="card-body">
		<h3>Result Status:</h3>
<p style="">
	{if $result.import_row}
		Total {$result.import_row} of {$result.ttl_row} item(s) will be imported.<br />
	{/if}
	{if $result.error_row > 0}
		Total {$result.error_row} of {$result.ttl_row} item(s) will fail to import due to some error found, please check the error message at the end of <span style="color:red">highlighted</span> row.<br />
	{/if}
	* Please ENSURE the result data is fill to the header accordingly before proceed to import.<br />
	<br/>
	<input type="button" id="import_btn" name="import_btn" value="Import" onclick="IMPORT_MEMBERS.import_members({$method});" {if !$result.import_row}disabled{/if} />
</p>
<div class="div_tbl">
	<div class="table-responsive">
		<table id="si_tbl" class="report_table table mb-0 text-md-nowrap  table-hover" width="100%">
		<thead class="bg-gray-100">
			<tr>
				<th>#</th>
				{foreach from=$item_header item=i}
					<th>{$i}</th>
				{/foreach}
			</tr>
		</thead>
			<tbody class="fs-08">
			{foreach from=$item_lists item=i name=members}
				<tr class="{if $i.error}tr_error{/if}">
					<td>{$smarty.foreach.members.iteration}.</td>
					{foreach from=$i key=k item=r}
						<td>{$r}</td>
					{/foreach}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

</div>

	</div>
</div>