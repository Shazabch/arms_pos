<span class="small">
{if !$tab or $tab eq 'scan_item'}
	[Scan Item]
{else}
	<a href="{$smarty.server.PHP_SELF}?search={$smarty.request.search|default:$search}">[Scan Item]</a>
{/if}

{if $smarty.session.batch_barcode.id && $smarty.session.batch_barcode.branch_id}
	{if $tab eq 'view_items'}
		[View Items List]
	{else}
		<a href="{$smarty.server.PHP_SELF}?a=view_items&find_batch_barcode={$form.search_var}">[View Items List]</a>
	{/if}
{/if}
</span>

<h4>{if !$smarty.session.batch_barcode.id}New{/if}</h4>