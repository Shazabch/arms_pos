{*
3/9/2021 16:54 PM Sin Rou
- Added config check to hide "Batch Code" in column table.
*}

<tr id="td_order_no-{$so.branch_id}-{$so.id}">
	<td>
		<img src="/ui/cancel.png" align="absmiddle" title="Delete" class="clickable" onClick="remove_order_no_from_list('{$so.branch_id}', '{$so.id}');" />
		<input type="hidden" name="so_list[]" value="{$so.branch_id}-{$so.id}" class="inp_so_list" />
	</td>
	<td class="td_order_no">{$so.order_no}</td>
	{if !$config.sales_order_hide_batch_code}
		<td>{$so.batch_code|default:'-'}</td>
	{/if}
	<td>{$so.cust_po|default:'-'}</td>
	<td>{$so.order_date|default:'-'}</td>
</tr>