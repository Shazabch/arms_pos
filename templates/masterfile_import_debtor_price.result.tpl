{*
*}
<h3>Result Status:</h3>
<form name="f_b" method="post">
<p style="color:blue;">
	{if $result.updated_row}
		Total {$result.updated_row} of {$result.ttl_row} item(s) will be updated.<br />
	{/if}
	{if $result.error_row > 0}
		Total {$result.error_row} of {$result.ttl_row} item(s) will fail to update due to some error found, please check the error message at the end of <span style="color:red">highlighted</span> row.<br />
		Additionally, click <a id="invalid_link" href="attachments/update_debtor_price/invalid_{$file_name}" download>HERE</a> to download and view the invalid data.<br /><br />
	{/if}
	* Please ENSURE the result data is fill to the header accordingly before proceed to update.<br />
	{if $BRANCH_CODE eq 'HQ'}* Please select the branch before proceed to update.{/if}
</p>
<p>
	Branch :
	{if $BRANCH_CODE eq 'HQ'}
			<label><input id="all_branch" type="checkbox" onclick="UPDATE_MODULE.check_all_branch(this)" onchange="UPDATE_MODULE.branch_checkbox_changed()" value="all">All</label>&nbsp;&nbsp;
		{foreach from=$branch item=b}
			<label><input name="branch_list[]" type="checkbox" onchange="UPDATE_MODULE.branch_checkbox_changed()" value="{$b.id}">{$b.code}</label>&nbsp;&nbsp;
		{/foreach}
	{else}
		{$BRANCH_CODE}
	{/if}
	<br/>
	<br/>
	<input type="button" id="update_btn" name="update_btn" value="Update" {if $BRANCH_CODE eq 'HQ'}disabled{/if} onclick="UPDATE_MODULE.import_debtor();"/>
</p>
</form>
<div class="div_tbl">
	<table id="si_tbl">
		<tr bgcolor="#ffffff">
			<th>#</th>
			{foreach from=$item_header item=i}
				<th>{$i}</th>
			{/foreach}
		</tr>
		<tbody>
		{foreach from=$item_lists item=i name=debtor}
			<tr class="{if $i.error}tr_error{/if}">
				<td>{$smarty.foreach.debtor.iteration}.</td>
				{foreach from=$i key=k item=r}
					<td>{$r}</td>
				{/foreach}
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>