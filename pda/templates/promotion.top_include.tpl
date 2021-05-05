
<span class="small">
{if $promotion_tab eq 'setting'}
    [Change Setting]
{else}
    <a href="promotion.php?a=show_setting">[Change Setting]</a>
{/if}

{if !$promotion_tab or $promotion_tab eq 'scan_item'}
	[Scan Item]
{else}
	<a href="promotion.php?a=show_scan_product">[Scan Item]</a>
{/if}

{if $promotion_tab eq 'view_items'}
    [View Items List]
{else}
    <a href="promotion.php?a=view_items">[View Items List]</a>
{/if}
</span>
