{if $grn_tab eq 'setting'}
     <a class="btn btn-light btn-sm disabled animated fadeInDown"><i class="fas fa-cog"></i> Change Setting</a>
{else}
<a href="goods_receiving_note.php?a=show_setting&find_grn={$form.find_grn|default:$smarty.request.find_grn|default:$find_grn}" class="btn btn-info btn-sm  animated fadeInDown"><i class="fas fa-cog"></i> Change Setting</a>
{/if}

{if !$grn_tab or $grn_tab eq 'scan_item'}
<a class="btn btn-light btn-sm disabled animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> Scan Item</a>
{else}
    <a href="goods_receiving_note.php?find_grn={$smarty.request.find_grn|default:$find_grn}" class="btn btn-indigo btn-sm  animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> Scan Item</a>
{/if}

{if $grn_tab eq 'view_items'}
    <a class="btn btn-light btn-sm animated fadeInDown disabled"><i class="fas fa-th-list"></i> View Items List</a>
{else}
    <a href="goods_receiving_note.php?a=view_items&find_grn={$form.find_grn}" class="btn btn-success btn-sm animated fadeInDown"><i class="fas fa-th-list"></i> View Items List</a>
{/if}
