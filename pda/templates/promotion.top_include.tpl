
<span class="small">
{if $promotion_tab eq 'setting'}
    <a class="btn btn-light btn-sm disabled animated fadeInDown"><i class="fas fa-cog"></i> Change Setting</a>
{else}
    <a href="promotion.php?a=show_setting" class="btn btn-info btn-sm animated fadeInDown"><i class="fas fa-cog"></i> Change Setting]</a>
{/if}

{if !$promotion_tab or $promotion_tab eq 'scan_item'}
	<a class="btn btn-light btn-sm disabled animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> Scan Item</a>
{else}
	<a href="promotion.php?a=show_scan_product" class="btn btn-indigo btn-sm animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> Scan Item</a>
{/if}

{if $promotion_tab eq 'view_items'}
   <a class="btn btn-light btn-sm animated fadeInDown disabled"><i class="fas fa-th-list"></i> View Items List</a>
{else}
    <a href="promotion.php?a=view_items" class="btn btn-success btn-sm animated fadeInDown"><i class="fas fa-th-list"></i> View Items List]</a>
{/if}
</span>
