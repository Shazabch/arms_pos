
<div class="mt-2 animated fadeInDown">
{if $so_tab eq 'setting'}
    <button class="btn btn-light btn-rounded btn-sm mr-1 disabled"><i class="fas fa-cog"></i> Change Setting</button>
{else}
    <a href="sales_order.php?a=show_setting" class="btn btn-info btn-sm btn-rounded"><i class="fas fa-cog"></i> Change Setting</a>
{/if}

{if !$so_tab or $so_tab eq 'scan_item'}
	<button class="btn btn-light btn-sm btn-rounded disabled"><i class="mdi mdi-barcode-scan"></i> Scan Item</button>
{else}
	<a href="sales_order.php" class="btn btn-indigo btn-sm btn-rounded"><i class="mdi mdi-barcode-scan"></i> Scan Item</a>
{/if}

{if $so_tab eq 'view_items'}
    <button class="btn btn-light btn-rounded btn-sm mr-1 disabled"><i class="fas fa-th-list"></i> View Items List</button>
{else}
    <a href="sales_order.php?a=view_items" class="btn btn-success btn-sm btn-rounded"><i class="fas fa-th-list"></i> View Items List</a>
{/if}
</div>
