
<span class="small">
{if $smarty.request.do_tab eq 'setting'}
    [Change Setting]
{else}
    <a href="do.php?a=show_setting">[Change Setting]</a>
{/if}

{if !$smarty.request.do_tab or $smarty.request.do_tab eq 'scan_item'}
	[Scan Item]
{else}
	<a href="do.php">[Scan Item]</a>
{/if}

{if $smarty.request.do_tab eq 'view_items'}
    [View Items List]
{else}
    <a href="do.php?a=view_items">[View Items List]</a>
{/if}
</span>
