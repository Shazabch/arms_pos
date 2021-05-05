<span class="small">
{if $grn_tab eq 'setting'}
    [Change Setting]
{else}
    <a href="goods_receiving_note.php?a=show_setting&find_grn={$form.find_grn|default:$smarty.request.find_grn|default:$find_grn}">[Change Setting]</a>
{/if}

{if !$grn_tab or $grn_tab eq 'scan_item'}
	[Scan Item]
{else}
	<a href="goods_receiving_note.php?find_grn={$smarty.request.find_grn|default:$find_grn}">[Scan Item]</a>
{/if}

{if $grn_tab eq 'view_items'}
    [View Items List]
{else}
    <a href="goods_receiving_note.php?a=view_items&find_grn={$form.find_grn}">[View Items List]</a>
{/if}
</span>
