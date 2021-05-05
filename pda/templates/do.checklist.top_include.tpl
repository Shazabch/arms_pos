<span class="small">
{if !$do_tab || $do_tab eq 'add_checklist'}
	[Scan Item]
{else}
	<a href="do.php?a=scan_checklist_item&id={$form.id}&branch_id={$form.branch_id}">[Scan Item]</a>
{/if}

{if $do_tab eq 'view_checklist'}
    [View Checklist Items]
{else}
    <a href="do.php?a=view_checklist_items&id={$form.id}&branch_id={$form.branch_id}">[View Checklist Items]</a>
{/if}
</span>