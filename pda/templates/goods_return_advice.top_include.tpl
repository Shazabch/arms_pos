<div class="mt-2"></div>
{if $gra_tab eq 'setting'}
    <button class="btn btn-light btn-sm disabled animated fadeInDown"><i class="fas fa-cog"></i> {$LNG.CHANGE_SETTING}</button>
{else}
    <a href="goods_return_advice.php?a=show_setting" class="btn btn-info btn-sm animated fadeInDown"><i class="fas fa-cog"></i> {$LNG.CHANGE_SETTING}</a>
{/if}

{if !$gra_tab or $gra_tab eq 'scan_item'}
    <button class="btn btn-light btn-sm disabled animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> {$LNG.SCAN_ITEM}</button>
{else}
	<a href="goods_return_advice.php" class="btn btn-indigo btn-sm animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> {$LNG.SCAN_ITEM}</a>
{/if}

{if $gra_tab eq 'view_items'}
    <button class="btn btn-light btn-sm animated fadeInDown disabled"><i class="fas fa-th-list"></i> {$LNG.VIEW_ITEMS_LIST}</button>
{else}
    <a href="goods_return_advice.php?a=view_items" class="btn btn-success btn-sm animated fadeInDown"><i class="fas fa-th-list"></i> {$LNG.VIEW_ITEMS_LIST}</a>
{/if}
