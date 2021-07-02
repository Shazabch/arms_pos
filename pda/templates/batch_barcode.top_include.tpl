
{if !$tab or $tab eq 'scan_item'}
	<button class="btn btn-light disabled animated fadeInDown mr-1"><i class="mdi mdi-barcode-scan"></i> Scan Item</button>
{else}
	<a href="{$smarty.server.PHP_SELF}?search={$smarty.request.search|default:$search}" class="btn btn-sm btn-indigo animated fadeInDown mr-1"><i class="mdi mdi-barcode-scan mr-1"></i> Scan Item</a>
{/if}

{if $smarty.session.batch_barcode.id && $smarty.session.batch_barcode.branch_id}
	{if $tab eq 'view_items'}
		[View Items List]
		<a class="btn btn-success animated fadeInDown disabled mr-1"><i class="mdi mdi-format-list-text"></i> View Items List</a>
	{else}
		<a href="{$smarty.server.PHP_SELF}?a=view_items&find_batch_barcode={$form.search_var}" class="btn btn-success btn-sm animated fadeInDown mr-1"><i class="mdi mdi-format-list-text"></i> View Items List</a>
	{/if}
{/if}

<h4 class="ml-2 animated fadeInDown">{if !$smarty.session.batch_barcode.id}New{/if}</h4>