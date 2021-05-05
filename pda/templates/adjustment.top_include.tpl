
<span class="small">
{if $adj_tab eq 'setting'}
    [Change Setting]
{else}
    <a href="adjustment.php?a=show_setting">[Change Setting]</a>
{/if}

{if !$adj_tab or $adj_tab eq 'scan_item'}
	[Scan Item]
{else}
	<a href="adjustment.php">[Scan Item]</a>
{/if}

{if $adj_tab eq 'view_items'}
    [View Items List]
{else}
    <a href="adjustment.php?a=view_items">[View Items List]</a>
{/if}
</span>
