<table class="report_table" width="100%">
	<tr class="header">
		<th width="5%">#</th>
		<th width="40%">SKU Item/SKU Group/<br />Category</th>
		{if $BRANCH_CODE ne 'HQ'}
			<th>{$BRANCH_CODE}</th>
		{/if}
		<th>All</th>
	</tr>
	<tbody id="rp_configuration_items">
	{foreach from=$items key=r item=item}
		{include file="masterfile_return_policy_configure.table.row.tpl"}
	{foreachelse}
		<tr>
			<td colspan="3" align="center" id="no_data">- No Record -</td>
		</tr>
	{/foreach}
	</tbody>
</table>
<p align="center">
	<input type="button" name="save" id="save_btn" value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;">
</p>