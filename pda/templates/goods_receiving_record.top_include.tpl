
<span class="small">
{if !$grr_tab or $grr_tab eq 'setting'}
    [Change Setting]
{else}
    <a href="goods_receiving_record.php?a=show_setting&find_grr={$smarty.request.find_grr}">[Change Setting]</a>
{/if}

{if $grr_tab eq 'view_items'}
    [Add / View Items List]
{else}
    <a href="goods_receiving_record.php?a=view_items&find_grr={$form.find_grr}">[Add / View Items List]</a>
{/if}
</span>
