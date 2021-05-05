{*
23/9/2019 11:38 AM William 
- Added new module Purchase Order.
*}
<span class="small">
{if $smarty.request.po_tab eq 'setting'}
	[Setting]
{else}
	<a href="po.php?a=show_setting">[Setting]</a>
{/if}

{if !$smarty.request.po_tab or $smarty.request.po_tab eq 'scan_item'}
	[Scan Item]
{else}
	<a href="po.php">[Scan Item]</a>
{/if}

{if $smarty.request.po_tab eq 'view_items'}
    [View Items List]
{else}
    <a href="po.php?a=view_items">[View Items List]</a>
{/if}
</span>