<div class="mt-2"></div>
{if $gra_tab eq 'setting'}
    <button class="btn btn-light btn-sm disabled animated fadeInDown"><i class="fas fa-cog"></i> Change Setting</button>
{else}
    <a href="goods_return_advice.php?a=show_setting" class="btn btn-info btn-sm animated fadeInDown"><i class="fas fa-cog"></i> Change Setting</a>
{/if}

{if !$gra_tab or $gra_tab eq 'scan_item'}
    <button class="btn btn-light btn-sm disabled animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> Scan Item</button>
{else}
	<a href="goods_return_advice.php" class="btn btn-indigo btn-sm animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> Scan Item</a>
{/if}

{if $gra_tab eq 'view_items'}
    <button class="btn btn-light btn-sm animated fadeInDown disabled"><i class="fas fa-th-list"></i> View Items List</button>
{else}
    <a href="goods_return_advice.php?a=view_items" class="btn btn-success btn-sm animated fadeInDown"><i class="fas fa-th-list"></i> View Items List</a>
{/if}
