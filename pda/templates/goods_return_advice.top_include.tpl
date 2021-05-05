
<span class="small">
{if $gra_tab eq 'setting'}
    [Change Setting]
{else}
    <a href="goods_return_advice.php?a=show_setting">[Change Setting]</a>
{/if}

{if !$gra_tab or $gra_tab eq 'scan_item'}
	[Scan Item]
{else}
	<a href="goods_return_advice.php">[Scan Item]</a>
{/if}

{if $gra_tab eq 'view_items'}
    [View Items List]
{else}
    <a href="goods_return_advice.php?a=view_items">[View Items List]</a>
{/if}
</span>
