
<span class="small">
{if $so_tab eq 'setting'}
    [Change Setting]
{else}
    <a href="sales_order.php?a=show_setting">[Change Setting]</a>
{/if}

{if !$so_tab or $so_tab eq 'scan_item'}
	[Scan Item]
{else}
	<a href="sales_order.php">[Scan Item]</a>
{/if}

{if $so_tab eq 'view_items'}
    [View Items List]
{else}
    <a href="sales_order.php?a=view_items">[View Items List]</a>
{/if}
</span>
