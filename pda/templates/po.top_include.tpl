{*
23/9/2019 11:38 AM William 
- Added new module Purchase Order.
*}
{if $smarty.request.po_tab eq 'setting'}
	<a class="btn btn-light btn-sm disabled animated fadeInDown"><i class="fas fa-cog"></i> Setting</a>
{else}
	<a href="po.php?a=show_setting"class="btn btn-info btn-sm animated fadeInDown"><i class="fas fa-cog"></i> Setting</a>
{/if}

{if !$smarty.request.po_tab or $smarty.request.po_tab eq 'scan_item'}
	<a class="btn btn-light btn-sm disabled animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> Scan Item</a>
{else}
	 <a href="po.php"  class="btn btn-indigo btn-sm animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> Scan Item</a>
{/if}

{if $smarty.request.po_tab eq 'view_items'}
    <a class="btn btn-light btn-sm animated fadeInDown disabled"><i class="fas fa-th-list"></i> View Items List</a>
{else}
    <a href="po.php?a=view_items" class="btn btn-success btn-sm animated fadeInDown"><i class="fas fa-th-list"></i> View Items List</a>
{/if}