{if $adj_tab eq 'setting'}
    <a class="btn btn-light btn-sm disabled "><i class="fas fa-cog"></i> {$LNG.CHANGE_SETTING}</a>
{else}
    <a href="adjustment.php?a=show_setting" class="btn btn-info btn-sm "><i class="fas fa-cog"></i> {$LNG.CHANGE_SETTING}</a>
{/if}

{if !$adj_tab or $adj_tab eq 'scan_item'}
    <a class="btn btn-light btn-sm disabled "><i class="mdi mdi-barcode-scan"></i> {$LNG.SCAN_ITEM}</a>
{else}
	<a href="adjustment.php" class="btn btn-indigo btn-sm "><i class="mdi mdi-barcode-scan"></i> {$LNG.SCAN_ITEM}</a>
{/if}

{if $adj_tab eq 'view_items'}
    <a class="btn btn-light btn-sm  disabled"><i class="fas fa-th-list"></i> {$LNG.VIEW_ITEMS_LIST}</a>
{else}
    <a href="adjustment.php?a=view_items" class="btn btn-success btn-sm "><i class="fas fa-th-list"></i> {$LNG.VIEW_ITEMS_LIST}</a>
{/if}
