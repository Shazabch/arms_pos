{if $adj_tab eq 'setting'}
    <a class="btn btn-light btn-sm disabled animated fadeInDown"><i class="fas fa-cog"></i> Change Setting</a>
{else}
    <a href="adjustment.php?a=show_setting" class="btn btn-info btn-sm animated fadeInDown"><i class="fas fa-cog"></i> Change Setting</a>
{/if}

{if !$adj_tab or $adj_tab eq 'scan_item'}
    <a class="btn btn-light btn-sm disabled animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> Scan Item</a>
{else}
	<a href="adjustment.php" class="btn btn-indigo btn-sm animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> Scan Item</a>
{/if}

{if $adj_tab eq 'view_items'}
    <a class="btn btn-light btn-sm animated fadeInDown disabled"><i class="fas fa-th-list"></i> View Items List</a>
{else}
    <a href="adjustment.php?a=view_items" class="btn btn-success btn-sm animated fadeInDown"><i class="fas fa-th-list"></i> View Items List</a>
{/if}
