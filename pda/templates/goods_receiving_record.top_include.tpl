<div class="row px-2">
    {if !$grr_tab or $grr_tab eq 'setting'}
        <button class="btn btn-light btn-rounded btn-sm mr-1 disabled">Change Setting</button>
    {else}
        <a href="goods_receiving_record.php?a=show_setting&find_grr={$smarty.request.find_grr}" class="btn btn-indigo btn-rounded btn-sm mr-1">Change Setting</a>
    {/if}

    {if $grr_tab eq 'view_items'}
        <button class="btn btn-light btn-rounded btn-sm mr-1 disabled">Add / View Items List</button>
    {else}
        <a href="goods_receiving_record.php?a=view_items&find_grr={$form.find_grr}" class="btn btn-success btn-rounded btn-sm mr-1">Add / View Items List</a>
    {/if}
</div>