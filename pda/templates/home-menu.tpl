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
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">DO</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white">
			<li class="breadcrumb-item">
				<a href="home.php">Dashboard</a>
			</li>
			<li class="breadcrumb-item active">DO</li>
		</ol>
	</nav>
	<div class="d-flex justify-content-center align-items-center mt-5">
		<div class="col-md-6 d-flex flex-column justify-content-center align-items-center py-2 px-2">
		{if $smarty.session.do.id>0 and $smarty.session.do.branch_id eq $sessioninfo.branch_id}
			<a href="do.php" class="btn btn-secondary-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="far fa-clock"></i> Continue Last DO</a>
		{/if}
			<a href="do.php?a=new_do" class="btn btn-info-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> Create New Transfer Do</a>

			<a href="do.php?a=new_do&do_type=open" class="btn btn-danger-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> Create New Cash Sales Do</a>

			<a href="do.php?a=new_do&do_type=credit_sales" class="btn btn-warning-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> Create New Credit Sales Do</a>

			<a href="do.php?a=open" class="btn btn-success-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> Open DO by Do No</a>

			<a href="do.php?a=open_checklist" class="btn btn-primary-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> Open DO Checklist</a>

			<a href="do.picking_verification.php?a=open" class="btn btn-dark-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> DO Picking Verification</a>
		</div>
	</div>
{/if}
{if $config.allow_sales_order and file_exists('sales_order.php') and $smarty.request.id eq 'sales_order' }
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">SALES ORDER</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white">
			<li class="breadcrumb-item">
				<a href="home.php">Dashboard</a>
			</li>
			<li class="breadcrumb-item active">SALES ORDER</li>
		</ol>
	</nav>
	<div class="d-flex justify-content-center align-items-center mt-5">
		<div class="col-md-6 d-flex flex-column justify-content-center align-items-center py-2 px-2">
		{if $smarty.session.so.id>0 and $smarty.session.so.branch_id eq $sessioninfo.branch_id}
			<a href="sales_order.php" class="btn btn-secondary-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="far fa-clock"></i> Continue Last Sales Order</a>
		{/if}
			<a href="sales_order.php?a=new_so" class="btn btn-info-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> Create New Sales Order</a>
			<a href="sales_order.php?a=open" class="btn btn-warning-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-search"></i>Open by Order No</a>
		</div>
	</div>
{/if}

{if $sessioninfo.privilege.GRR and $smarty.request.id eq 'grr'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">GRR</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white">
			<li class="breadcrumb-item">
				<a href="home.php">Dashboard</a>
			</li>
			<li class="breadcrumb-item active">GRR</li>
		</ol>
	</nav>
	<div class="d-flex justify-content-center align-items-center mt-5">
		<div class="col-md-6 d-flex flex-column justify-content-center align-items-center py-2 px-2">
			{if $smarty.session.grr.id>0 and $smarty.session.grr.branch_id eq $sessioninfo.branch_id}
			<a href="goods_receiving_record.php" class="btn btn-secondary-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="far fa-clock"></i> Continue Last GRR</a>
			{/if}
			<a href="goods_receiving_record.php?a=new_grr" class="btn btn-info-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> Create New GRR</a>
			<a href="goods_receiving_record.php?a=open" class="btn btn-warning-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-search"></i> Open by GRR No</a>
		</div>
	</div>
{/if}

{if $sessioninfo.privilege.GRN and $smarty.request.id eq 'grn'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">GRN</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white">
			<li class="breadcrumb-item">
				<a href="home.php">Dashboard</a>
			</li>
			<li class="breadcrumb-item active">GRN</li>
		</ol>
	</nav>
	<div class="d-flex justify-content-center align-items-center mt-5">
		<div class="col-md-6 d-flex flex-column justify-content-center align-items-center py-2 px-2">
		{if $smarty.session.grn.id>0 and $smarty.session.grn.branch_id eq $sessioninfo.branch_id}
			<a href="goods_receiving_note.php" class="btn btn-secondary-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="far fa-clock"></i> Continue Last GRN</a>
		{/if}
			<a href="goods_receiving_note.php?a=show_grr_list" class="btn btn-info-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> Create New GRN</a>

			<a href="goods_receiving_note.php?a=open" class="btn btn-warning-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-search"></i> Open by GRN No / GRR No</a>
		</div>
	</div>
{/if}

{if $sessioninfo.privilege.GRA and $smarty.request.id eq 'gra'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">GRA</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white">
			<li class="breadcrumb-item">
				<a href="home.php">Dashboard</a>
			</li>
			<li class="breadcrumb-item active">GRA</li>
		</ol>
	</nav>
	<div class="d-flex justify-content-center align-items-center mt-5">
		<div class="col-md-6 d-flex flex-column justify-content-center align-items-center py-2 px-2">
			{if $smarty.session.gra.id>0 and $smarty.session.gra.branch_id eq $sessioninfo.branch_id}
			<a href="goods_return_advice.php" class="btn btn-secondary-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="far fa-clock"></i> Continue Last GRA</a>
			{/if}
			<a  href="goods_return_advice.php?a=new_gra" class="btn btn-info-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> Create New GRA</a>

			<a href="goods_return_advice.php?a=open" class="btn btn-warning-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-search"></i> Open by GRA No</a>
		</div>
	</div>
{/if}

{if $sessioninfo.privilege.ADJ and file_exists('adjustment.php') and $smarty.request.id eq 'adjustment'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">ADJUSTMENT</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white">
			<li class="breadcrumb-item">
				<a href="home.php">Dashboard</a>
			</li>
			<li class="breadcrumb-item active">ADJUSTMENT</li>
		</ol>
	</nav>
	<div class="d-flex justify-content-center align-items-center mt-5">
		<div class="col-md-6 d-flex flex-column justify-content-center align-items-center py-2 px-2">
		{if $smarty.session.adj.id>0 and $smarty.session.adj.branch_id eq $sessioninfo.branch_id}
			<a href="adjustment.php" class="btn btn-secondary-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="far fa-clock"></i> Continue Last Adjustment</a>
		{/if}
			<a  href="adjustment.php?a=new_adj" class="btn btn-info-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> Create New Adjustment</a>

			<a href="adjustment.php?a=open" class="btn btn-warning-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-search"></i> Open by Adjustment No</a>
		</div>
	</div>
{/if}

{if $sessioninfo.privilege.PO and file_exists('po.php') and ($smarty.request.id eq 'po' || $smarty.request.id eq 'purchase_order')}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">PO</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white">
			<li class="breadcrumb-item">
				<a href="home.php">Dashboard</a>
			</li>
			<li class="breadcrumb-item active">PO</li>
		</ol>
	</nav>
	<div class="d-flex justify-content-center align-items-center mt-5">
		<div class="col-md-6 d-flex flex-column justify-content-center align-items-center py-2 px-2">
		{if $smarty.session.po.id>0 and $smarty.session.po.branch_id eq $sessioninfo.branch_id}
			<a href="po.php" class="btn btn-secondary-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="far fa-clock"></i> Continue Last PO</a>
		{/if}
			<a href="po.php?a=new_po" class="btn btn-info-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> Create New PO</a>
			<a href="po.php?a=open" class="btn btn-warning-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-search"></i> Open PO by PO No</a>
		</div>
	</div>
{/if}

{if $sessioninfo.privilege.STOCK_TAKE and file_exists('stock_take.php') and $smarty.request.id eq 'stock_take'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">STOCK TAKE</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white">
			<li class="breadcrumb-item">
				<a href="home.php">Dashboard</a>
			</li>
			<li class="breadcrumb-item active">STOCK TAKE</li>
		</ol>
	</nav>
	<div class="d-flex justify-content-center align-items-center mt-5">
		<div class="col-md-6 d-flex flex-column justify-content-center align-items-center py-2 px-2">
		{if $smarty.session.st.date and $smarty.session.st.location and $smarty.session.st.shelf and $smarty.session.st.branch_id eq $sessioninfo.branch_id}
			<a href="stock_take.php?a=show_scan" class="btn btn-secondary-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="far fa-clock"></i> Continue Last Stock Take</a>
		{/if}
			<a href="stock_take.php?a=stock_take" class="btn btn-warning-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-search"></i> Open Stock Take</a>

			{*<a href="stock_take.php?a=open" class="btn btn-dark-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="far fa-clock"></i> Open Existing Stock Take</a>*}
		</div>
	</div>
{/if}

{if file_exists('batch_barcode.php') and $smarty.request.id eq 'batch_barcode'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">BATCH BARCODE</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white">
			<li class="breadcrumb-item">
				<a href="home.php">Dashboard</a>
			</li>
			<li class="breadcrumb-item active">BATCH BARCODE</li>
		</ol>
	</nav>
	<div class="d-flex justify-content-center align-items-center mt-5">
		<div class="col-md-6 d-flex flex-column justify-content-center align-items-center py-2 px-2">
		{if $smarty.session.batch_barcode.id && $smarty.session.batch_barcode.branch_id eq $sessioninfo.branch_id}
			<a href="batch_barcode.php" class="btn btn-secondary-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="far fa-clock"></i> Continue Last Batch</a>
		{/if}
			<a href="batch_barcode.php?a=new_batch_barcode" class="btn btn-info-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> Create New Batch</a>
			<a href="batch_barcode.php?a=open" class="btn btn-warning-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-search"></i> Open Batch Barcode List</a>
		</div>
	</div>
{/if}

{if file_exists('promotion.php') and $smarty.request.id eq 'promotion'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">PROMOTION</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white">
			<li class="breadcrumb-item">
				<a href="home.php">Dashboard</a>
			</li>
			<li class="breadcrumb-item active">PROMOTION</li>
		</ol>
	</nav>
	<div class="d-flex justify-content-center align-items-center mt-5">
		<div class="col-md-6 d-flex flex-column justify-content-center align-items-center py-2 px-2">
		{if $smarty.session.promotion.id>0}
			<a href="promotion.php" class="btn btn-secondary-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="far fa-clock"></i> Continue Last Promotion</a>
		{/if}
			<a href="promotion.php?a=create" class="btn btn-info-gradient btn-with-icon btn-block btn-lg pyc-2 shadow mb-1"><i class="fas fa-plus"></i> Create New Promotion</a>
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
