{*
03/11/2020 4:00 PM Rayleen 
- Created and applied design for Sub Menus Page

05/11/2020 11:44 AM Sheila 
- Added breadcrumbs

12/17/2020 10:32 AM Andy
- Fixed Batch Barcode "Continue Last Batch" bug.

*}

{include file='header.tpl'}

{*<a href="home.php" class="menu-back">< Back</a>*}

{if $sessioninfo.privilege.DO and ($smarty.request.id eq 'do' || $smarty.request.id eq 'cash_sales_do' || $smarty.request.id eq 'credit_sales_do' || $smarty.request.id eq 'transfer_do') }
	<p class="menu-title">DO</p>
	<span class="breadcrumbs"><a href="home.php">< Dashboard</a></span>
	<div style="margin-bottom: 10px"></div>
	<div class="home-row">
		{if $smarty.session.do.id>0 and $smarty.session.do.branch_id eq $sessioninfo.branch_id}
			<div class="home-menu-column">
			  	<a href="do.php">
				    <div><p>Continue Last DO</p></div>
			    </a>
			</div>
		{/if}
		<div class="home-menu-column">
		  	<a href="do.php?a=new_do">
			    <div><p>Create New Transfer DO</p></div>
		    </a>
		</div>
		<div class="home-menu-column">
		  	<a href="do.php?a=new_do&do_type=open">
			    <div><p>Create New Cash Sales DO</p></div>
		    </a>
		</div>
		<div class="home-menu-column">
		  	<a href="do.php?a=new_do&do_type=credit_sales">
			    <div><p>Create New Credit Sales DO</p></div>
		    </a>
		</div>
		<div class="home-menu-column">
		  	<a href="do.php?a=open">
			    <div><p>Open DO by Do No</p></div>
		    </a>
		</div>
		<div class="home-menu-column">
		  	<a href="do.php?a=open_checklist">
			    <div><p>Open DO Checklist</p></div>
		    </a>
		</div>
		<div class="home-menu-column">
		  	<a href="do.picking_verification.php?a=open">
			    <div><p>DO Picking Verification</p></div>
		    </a>
		</div>
	</div>
{/if}
{if $config.allow_sales_order and file_exists('sales_order.php') and $smarty.request.id eq 'sales_order' }
	<p class="menu-title">SALES ORDER</p>
	<span class="breadcrumbs"><a href="home.php">< Dashboard</a></span>
	<div style="margin-bottom: 10px"></div>
	<div class="home-row">
		{if $smarty.session.so.id>0 and $smarty.session.so.branch_id eq $sessioninfo.branch_id}
			<div class="home-menu-column">
			  	<a href="sales_order.php">
				    <div><p>Continue Last Sales Order</p></div>
			    </a>
			</div>
		{/if}
		<div class="home-menu-column">
		  	<a href="sales_order.php?a=new_so">
			    <div><p>Create New Sales Order</p></div>
		    </a>
		</div>
		<div class="home-menu-column">
		  	<a href="sales_order.php?a=open">
			    <div><p>Open by Order No</p></div>
		    </a>
		</div>
	</div>
{/if}

{if $sessioninfo.privilege.GRR and $smarty.request.id eq 'grr'}
	<p class="menu-title">GRR</p>
	<span class="breadcrumbs"><a href="home.php">< Dashboard</a></span>
	<div style="margin-bottom: 10px"></div>
	<div class="home-row">
		{if $smarty.session.grr.id>0 and $smarty.session.grr.branch_id eq $sessioninfo.branch_id}
			<div class="home-menu-column">
			  	<a href="goods_receiving_record.php">
				    <div><p>Continue Last GRR</p></div>
			    </a>
			</div>
		{/if}
		<div class="home-menu-column">
		  	<a href="goods_receiving_record.php?a=new_grr">
			    <div><p>Create New GRR</p></div>
		    </a>
		</div>
		<div class="home-menu-column">
		  	<a href="goods_receiving_record.php?a=open">
			    <div><p>Open by GRR No</p></div>
		    </a>
		</div>
	</div>
{/if}

{if $sessioninfo.privilege.GRN and $smarty.request.id eq 'grn'}
	<p class="menu-title">GRN</p>
	<span class="breadcrumbs"><a href="home.php">< Dashboard</a></span>
	<div style="margin-bottom: 10px"></div>
	<div class="home-row">
		{if $smarty.session.grn.id>0 and $smarty.session.grn.branch_id eq $sessioninfo.branch_id}
			<div class="home-menu-column">
			  	<a href="goods_receiving_note.php">
				    <div><p>Continue Last GRN</p></div>
			    </a>
			</div>
		{/if}
		<div class="home-menu-column">
		  	<a href="goods_receiving_note.php?a=show_grr_list">
			    <div><p>Create New GRN</p></div>
		    </a>
		</div>
		<div class="home-menu-column">
		  	<a href="goods_receiving_note.php?a=open">
			    <div><p>Open by GRN No / GRR No</p></div>
		    </a>
		</div>
	</div>
{/if}

{if $sessioninfo.privilege.GRA and $smarty.request.id eq 'gra'}
	<p class="menu-title">GRA</p>
	<span class="breadcrumbs"><a href="home.php">< Dashboard</a></span>
	<div style="margin-bottom: 10px"></div>	
	<div class="home-row">
		{if $smarty.session.gra.id>0 and $smarty.session.gra.branch_id eq $sessioninfo.branch_id}
			<div class="home-menu-column">
			  	<a href="goods_return_advice.php">
				    <div><p>Continue Last GRA</p></div>
			    </a>
			</div>
		{/if}
		<div class="home-menu-column">
		  	<a href="goods_return_advice.php?a=new_gra">
			    <div><p>Create New GRA</p></div>
		    </a>
		</div>
		<div class="home-menu-column">
		  	<a href="goods_return_advice.php?a=open">
			    <div><p>Open by GRA No</p></div>
		    </a>
		</div>
	</div>
{/if}

{if $sessioninfo.privilege.ADJ and file_exists('adjustment.php') and $smarty.request.id eq 'adjustment'}
	<p class="menu-title">ADJUSTMENT</p>
	<span class="breadcrumbs"><a href="home.php">< Dashboard</a></span>
	<div style="margin-bottom: 10px"></div>
	<div class="home-row">
		{if $smarty.session.adj.id>0 and $smarty.session.adj.branch_id eq $sessioninfo.branch_id}
			<div class="home-menu-column">
			  	<a href="adjustment.php">
				    <div><p>Continue Last Adjustment</p></div>
			    </a>
			</div>
		{/if}
		<div class="home-menu-column">
		  	<a href="adjustment.php?a=new_adj">
			    <div><p>Create New Adjustment</p></div>
		    </a>
		</div>
		<div class="home-menu-column">
		  	<a href="adjustment.php?a=open">
			    <div><p>Open by Adjustment No</p></div>
		    </a>
		</div>
	</div>
{/if}

{if $sessioninfo.privilege.PO and file_exists('po.php') and ($smarty.request.id eq 'po' || $smarty.request.id eq 'purchase_order')}
	<p class="menu-title">PO</p>
	<span class="breadcrumbs"><a href="home.php">< Dashboard</a></span>
	<div style="margin-bottom: 10px"></div>
	<div class="home-row">
		{if $smarty.session.po.id>0 and $smarty.session.po.branch_id eq $sessioninfo.branch_id}
			<div class="home-menu-column">
			  	<a href="po.php">
				    <div><p>Continue Last PO</p></div>
			    </a>
			</div>
		{/if}
		<div class="home-menu-column">
		  	<a href="po.php?a=new_po">
			    <div><p>Create New PO</p></div>
		    </a>
		</div>
		<div class="home-menu-column">
		  	<a href="po.php?a=open">
			    <div><p>Open PO by Po No</p></div>
		    </a>
		</div>
	</div>
{/if}

{if $sessioninfo.privilege.STOCK_TAKE and file_exists('stock_take.php') and $smarty.request.id eq 'stock_take'}
	<p class="menu-title">STOCK TAKE</p>
	<span class="breadcrumbs"><a href="home.php">< Dashboard</a></span>
	<div style="margin-bottom: 10px"></div>
	<div class="home-row">
		{if $smarty.session.st.date and $smarty.session.st.location and $smarty.session.st.shelf and $smarty.session.st.branch_id eq $sessioninfo.branch_id}
			<div class="home-menu-column">
			  	<a href="stock_take.php?a=show_scan">
				    <div><p>Continue Last Stock Take</p></div>
			    </a>
			</div>
		{/if}
		<div class="home-menu-column">
		  	<a href="stock_take.php?a=stock_take">
			    <div><p>Open Stock Take</p></div>
		    </a>
		</div>
		{*<div class="home-menu-column">
		  	<a href="stock_take.php?a=open">
			    <div><p>Open Existing Stock Take</p></div>
		    </a>
		</div>*}
	</div>
{/if}

{if file_exists('batch_barcode.php') and $smarty.request.id eq 'batch_barcode'}
	<p class="menu-title">BATCH BARCODE</p>
	<span class="breadcrumbs"><a href="home.php">< Dashboard</a></span>
	<div style="margin-bottom: 10px"></div>
	<div class="home-row">
		{if $smarty.session.batch_barcode.id && $smarty.session.batch_barcode.branch_id eq $sessioninfo.branch_id}
			  	<a href="batch_barcode.php">
				    <div><p>Continue Last Batch</p></div>
			    </a>
			</div>
		{/if}
		<div class="home-menu-column">
		  	<a href="batch_barcode.php?a=new_batch_barcode">
			    <div><p>Create New Batch</p></div>
		    </a>
		</div>
		<div class="home-menu-column">
		  	<a href="batch_barcode.php?a=open">
			    <div><p>Open Batch Barcode List</p></div>
		    </a>
		</div>
	</div>
{/if}

{if file_exists('promotion.php') and $smarty.request.id eq 'promotion'}
	<p class="menu-title">PROMOTION</p>
	<span class="breadcrumbs"><a href="home.php">< Dashboard</a></span>
	<div style="margin-bottom: 10px"></div>
	<div class="home-row">
		{if $smarty.session.promotion.id>0}
		<div class="home-menu-column">
			  	<a href="promotion.php">
				    <div><p>Continue Last Promotion</p></div>
			    </a>
			</div>
		{/if}
		<div class="home-menu-column">
		  	<a href="promotion.php?a=create">
			    <div><p>Create New Promotion</p></div>
		    </a>
		</div>
	</div>
{/if}

{if isset($config.pda_custom_modules) and $smarty.request.id eq 'custom'}
	<p class="menu-title">CUSTOM MODULES</p>
	<span class="breadcrumbs"><a href="home.php">< Dashboard</a></span>
	<div style="margin-bottom: 10px"></div>
	<div class="home-row">
		{include file=$config.pda_custom_modules}
	</div>
{/if}

{include file='footer.tpl'}
