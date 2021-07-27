
{if !$tab or $tab eq 'scan_item'}
	<button class="btn btn-light disabled  mr-1"><i class="mdi mdi-barcode-scan"></i> {$LNG.SCAN_ITEM}</button>
{else}
	<a href="{$smarty.server.PHP_SELF}?search={$smarty.request.search|default:$search}" class="btn btn-sm btn-indigo  mr-1"><i class="mdi mdi-barcode-scan mr-1"></i> {$LNG.SCAN_ITEM}</a>
{/if}

{if $smarty.session.batch_barcode.id && $smarty.session.batch_barcode.branch_id}
	{if $tab eq 'view_items'}
		<a class="btn btn-light btn-sm  disabled mr-1"><i class="fas fa-th-list"></i> {$LNG.VIEW_ITEMS_LIST}</a>
	{else}
		<a href="{$smarty.server.PHP_SELF}?a=view_items&find_batch_barcode={$form.search_var}" class="btn btn-success btn-sm  mr-1"><i class="fas fa-th-list"></i> {$LNG.VIEW_ITEMS_LIST}</a>
	{/if}
{/if}

<h4 class="ml-2 ">{if !$smarty.session.batch_barcode.id}{$LNG.NEW}{/if}</h4>