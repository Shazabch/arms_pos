{*
*}
<h3>Result Status:</h3>
<p style="color:blue;">
	{if $result.updated_row}
		Total {$result.updated_row} of {$result.ttl_row} item(s) will be updated.<br />
	{/if}
	{if $result.error_row > 0}
		Total {$result.error_row} of {$result.ttl_row} item(s) will fail to update due to some error found, please check the error message at the end of <span style="color:red">highlighted</span> row.<br />
		Additionally, click <a id="invalid_link" href="attachments/update_sku_category_discount/invalid_{$file_name}" download>HERE</a> to download and view the invalid data.<br />
	{/if}
	* Please ENSURE the result data is fill to the header accordingly before proceed to update.<br />
</p>

<p>
	<b><u>Filter Options:</u></b><br />
	{assign var=update_method value=$form.update_method}
	<b>Update SKU Category Discount by:&nbsp;&nbsp;{$upd_method_list.$update_method}</b>
	<input type="hidden" name="update_method" value="{$form.update_method}" />
	<div {if $form.update_method ne "by_branch"}style="display:none;"{/if}>
		<b>Branch Selected: </b>
		{foreach from=$form.branch_list key=bid item=dummy name=b}
			<input type="hidden" name="branch_list[{$bid}]" value="1" /> <b>{$branch_list.$bid}</b>{if !$smarty.foreach.b.last}, {/if}
		{/foreach}
	</div>
	<br />
	
	<input type="button" id="update_btn" name="update_btn" value="Update" onclick="UPDATE_SKU_CATEGORY_DISCOUNT_MODULE.update_sku(1);" {if !$result.updated_row}disabled{/if} />
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