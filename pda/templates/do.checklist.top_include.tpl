{if !$do_tab || $do_tab eq 'add_checklist'}
<a class="btn btn-light btn-sm disabled animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> Scan Item</a>
{else}
	<a href="do.php?a=scan_checklist_item&id={$form.id}&branch_id={$form.branch_id}"><i class="mdi mdi-barcode-scan"></i> Scan Item</a>
{/if}

{if $do_tab eq 'view_checklist'}
     <a class="btn btn-light btn-sm animated fadeInDown disabled"><i class="fas fa-th-list"></i> View Items List</a>
{else}
    <a href="do.php?a=view_checklist_items&id={$form.id}&branch_id={$form.branch_id}" class="btn btn-success btn-sm animated fadeInDown"><i class="fas fa-th-list"></i> View Checklist Items</a>
{/if}