
<span class="small">
{if $promotion_tab eq 'setting'}
    <a class="btn btn-light btn-sm disabled animated fadeInDown"><i class="fas fa-cog"></i> {$LNG.CHANGE_SETTING}</a>
{else}
    <a href="promotion.php?a=show_setting" class="btn btn-info btn-sm animated fadeInDown"><i class="fas fa-cog"></i> Change Settin{$LNG.CHANGE_SETTING}g</a>
{/if}

{if !$promotion_tab or $promotion_tab eq 'scan_item'}
	<a class="btn btn-light btn-sm disabled animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> {$LNG.SCAN_ITEM}</a>
{else}
	<a href="promotion.php?a=show_scan_product" class="btn btn-indigo btn-sm animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> {$LNG.SCAN_ITEM}</a>
{/if}

{if $promotion_tab eq 'view_items'}
   <a class="btn btn-light btn-sm animated fadeInDown disabled"><i class="fas fa-th-list"></i> {$LNG.VIEW_ITEMS_LIST}</a>
{else}
    <a href="promotion.php?a=view_items" class="btn btn-success btn-sm animated fadeInDown"><i class="fas fa-th-list"></i> {$LNG.VIEW_ITEMS_LIST}</a>
{/if}
</span>
