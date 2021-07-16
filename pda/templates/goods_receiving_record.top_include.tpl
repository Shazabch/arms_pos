<div class="row px-2">
    {if !$grr_tab or $grr_tab eq 'setting'}
        <button class="btn btn-light btn-rounded btn-sm mr-1 disabled"><i class="fas fa-cog"></i> {$LNG.CHANGE_SETTING}</button>
    {else}
        <a href="goods_receiving_record.php?a=show_setting&find_grr={$smarty.request.find_grr}" class="btn btn-info btn-rounded btn-sm mr-1"><i class="fas fa-cog"></i> {$LNG.CHANGE_SETTING}</a>
    {/if}

    {if $grr_tab eq 'view_items'}
        <button class="btn btn-light btn-rounded btn-sm mr-1 disabled"><i class="fas fa-th-list"></i> {$LNG.ADD_VIEW_ITEMS_LIST}</button>
    {else}
        <a href="goods_receiving_record.php?a=view_items&find_grr={$form.find_grr}" class="btn btn-success btn-rounded btn-sm mr-1"><i class="fas fa-th-list"></i> {$LNG.ADD_VIEW_ITEMS_LIST}</a>
    {/if}
</div>