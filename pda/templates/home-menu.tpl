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
	<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">{$LNG.DO}</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white animated fadeInDown">
			<li class="breadcrumb-item">
				<a href="home.php">{$LNG.DASHBOARD}</a>
			</li>
			<li class="breadcrumb-item active">{$LNG.DO}</li>
		</ol>
	</nav>
<div class="container p-0 mt-5">
	<div class="row">
	{if $smarty.session.do.id>0 and $smarty.session.do.branch_id eq $sessioninfo.branch_id}	
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-warning-transparent">
							<i class="fas fa-clock text-warning"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CONTINUE_LAST_DO}</h5>
						</div>
					</div>
				</div>
				<a href="do.php" class="stretched-link"></a>
			</div>
		</div>
	{/if}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-info-transparent">
							<i class="fas fa-exchange-alt text-info"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CREATE_NEW_TRANSFER_DO}</h5>
						</div>
					</div>
				</div>
				<a href="do.php?a=new_do" class="stretched-link"></a>
			</div>
		</div>
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-success-transparent">
							<i class="fas fa-money-check-alt text-success"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CREATE_NEW_CASH_SALES_DO}</h5>
						</div>
					</div>
				</div>
				<a href="do.php?a=new_do&do_type=open" class="stretched-link"></a>
			</div>
		</div>
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-pink-transparent">
							<i class="fas fa-credit-card text-pink"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CREATE_NEW_CREDIT_SALES_DO}</h5>
						</div>
					</div>
				</div>
				<a href="do.php?a=new_do&do_type=credit_sales" class="stretched-link"></a>
			</div>
		</div>
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-warning-transparent">
							<i class="fas fa-truck-loading text-warning"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.OPEN_DO_BY_DO_NO}</h5>
						</div>
					</div>
				</div>
				<a href="do.php?a=open" class="stretched-link"></a>
			</div>
		</div>
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-purple-transparent">
							<i class="fas fa-tasks text-purple"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.OPEN_DO_CHECKLIST}</h5>
						</div>
					</div>
				</div>
				<a href="do.php?a=open_checklist" class="stretched-link"></a>
			</div>
		</div>
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-orange-transparent">
							<i class="fas fa-clipboard-check text-orange"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.DO_PICKING_VERIFICATION}</h5>
						</div>
					</div>
				</div>
				<a href="do.picking_verification.php?a=open" class="stretched-link"></a>
			</div>
		</div>
	</div>
</div>
{/if}
{if $config.allow_sales_order and file_exists('sales_order.php') and $smarty.request.id eq 'sales_order' }
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">{$LNG.SALES_ORDER}</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white animated fadeInDown">
			<li class="breadcrumb-item">
				<a href="home.php">{$LNG.DASHBOARD}</a>
			</li>
			<li class="breadcrumb-item active">{$LNG.SALES_ORDER}</li>
		</ol>
	</nav>
<div class="container p-0 mt-5">
	<div class="d-flex flex-column justify-content-center align-items-center">
	{if $smarty.session.so.id>0 and $smarty.session.so.branch_id eq $sessioninfo.branch_id}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-warning-transparent">
							<i class="fas fa-clock text-warning"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CONTINUE_LAST_SALES_ORDER}</h5>
						</div>
					</div>
				</div>
				<a href="sales_order.php" class="stretched-link"></a>
			</div>
		</div>
	{/if}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-info-transparent">
							<i class="fas fa-plus text-info"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CREATE_NEW_SALES_ORDER}</h5>
						</div>
					</div>
				</div>
				<a href="sales_order.php?a=new_so" class="stretched-link"></a>
			</div>
		</div>
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-success-transparent">
							<i class="fas fa-search text-success"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.OPEN_BY_ORDER_NO}</h5>
						</div>
					</div>
				</div>
				<a href="sales_order.php?a=open" class="stretched-link"></a>
			</div>
		</div>
	</div>
</div>
{/if}

{if $sessioninfo.privilege.GRR and $smarty.request.id eq 'grr'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">{$LNG.GRR}</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white animated fadeInDown">
			<li class="breadcrumb-item">
				<a href="home.php">{$LNG.DASHBOARD}</a>
			</li>
			<li class="breadcrumb-item active">{$LNG.GRR}</li>
		</ol>
	</nav>

<div class="container p-0 mt-5">
	<div class="d-flex flex-column justify-content-center align-items-center">
	{if $smarty.session.grr.id>0 and $smarty.session.grr.branch_id eq $sessioninfo.branch_id}	
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-warning-transparent">
							<i class="fas fa-clock text-warning"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CONTINUE_LAST_ORDER}</h5>
						</div>
					</div>
				</div>
				<a href="goods_receiving_record.php" class="stretched-link"></a>
			</div>
		</div>
	{/if}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-info-transparent">
							<i class="fas fa-plus text-info"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CREATE_NEW_GRR}</h5>
						</div>
					</div>
				</div>
				<a href="goods_receiving_record.php?a=new_grr" class="stretched-link"></a>
			</div>
		</div>
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-success-transparent">
							<i class="fas fa-search text-success"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.OPEN_BY_GRR_NO}</h5>
						</div>
					</div>
				</div>
				<a href="goods_receiving_record.php?a=open" class="stretched-link"></a>
			</div>
		</div>
	</div>
</div>
{/if}

{if $sessioninfo.privilege.GRN and $smarty.request.id eq 'grn'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">{$LNG.GRN}</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white animated fadeInDown">
			<li class="breadcrumb-item">
				<a href="home.php">{$LNG.DASHBOARD}</a>
			</li>
			<li class="breadcrumb-item active">{$LNG.GRN}</li>
		</ol>
	</nav>
<div class="container p-0 mt-5">
	<div class="d-flex flex-column justify-content-center align-items-center">
	{if $smarty.session.grn.id>0 and $smarty.session.grn.branch_id eq $sessioninfo.branch_id}	
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-warning-transparent">
							<i class="fas fa-clock text-warning"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CONTINUE_LAST_GRN}</h5>
						</div>
					</div>
				</div>
				<a href="goods_receiving_note.php" class="stretched-link"></a>
			</div>
		</div>
	{/if}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-info-transparent">
							<i class="fas fa-plus text-info"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CREATE_NEW_GRN}</h5>
						</div>
					</div>
				</div>
				<a href="goods_receiving_note.php?a=show_grr_list" class="stretched-link"></a>
			</div>
		</div>
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-success-transparent">
							<i class="fas fa-search text-success"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.OPEN_BY_GRN_GRR_NO}</h5>
						</div>
					</div>
				</div>
				<a href="goods_receiving_note.php?a=open" class="stretched-link"></a>
			</div>
		</div>
	</div>
</div>
{/if}

{if $sessioninfo.privilege.GRA and $smarty.request.id eq 'gra'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">{$LNG.GRA}</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white animated fadeInDown">
			<li class="breadcrumb-item">
				<a href="home.php">{$LNG.DASHBOARD}</a>
			</li>
			<li class="breadcrumb-item active">{$LNG.GRA}</li>
		</ol>
	</nav>
<div class="container p-0 mt-5">
	<div class="d-flex flex-column justify-content-center align-items-center">
	{if $smarty.session.gra.id>0 and $smarty.session.gra.branch_id eq $sessioninfo.branch_id}	
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-warning-transparent">
							<i class="fas fa-clock text-warning"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="goods_return_advice.php">{$LNG.CONTINUE_LAST_GRA}</h5>
						</div>
					</div>
				</div>
				<a  href="goods_return_advice.php" class="stretched-link"></a>
			</div>
		</div>
	{/if}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-info-transparent">
							<i class="fas fa-plus text-info"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CREATE_NEW_GRA}</h5>
						</div>
					</div>
				</div>
				<a href="goods_return_advice.php?a=new_gra" class="stretched-link"></a>
			</div>
		</div>
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-success-transparent">
							<i class="fas fa-search text-success"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.OPEN_BY_GRA_NO}</h5>
						</div>
					</div>
				</div>
				<a href="goods_return_advice.php?a=open" class="stretched-link"></a>
			</div>
		</div>
	</div>
</div>
{/if}

{if $sessioninfo.privilege.ADJ and file_exists('adjustment.php') and $smarty.request.id eq 'adjustment'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">{$LNG.ADJUSTMENT}</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white animated fadeInDown">
			<li class="breadcrumb-item">
				<a href="home.php">{$LNG.DASHBOARD}</a>
			</li>
			<li class="breadcrumb-item active">{$LNG.ADJUSTMENT}</li>
		</ol>
	</nav>
<div class="container p-0 mt-5">
	<div class="d-flex flex-column justify-content-center align-items-center">
	{if $smarty.session.adj.id>0 and $smarty.session.adj.branch_id eq $sessioninfo.branch_id}	
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-warning-transparent">
							<i class="fas fa-clock text-warning"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CONTINUE_LAST_ADJUSTMENT}</h5>
						</div>
					</div>
				</div>
				<a href="adjustment.php" class="stretched-link"></a>
			</div>
		</div>
	{/if}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-info-transparent">
							<i class="fas fa-plus text-info"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CREATE_NEW_ADJUSTMENT}</h5>
						</div>
					</div>
				</div>
				<a href="adjustment.php?a=new_adj" class="stretched-link"></a>
			</div>
		</div>
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-success-transparent">
							<i class="fas fa-search text-success"></i>
						</div>
						<div class="ml-3 my-auto">
						</div>
					</div>
							<h5 class="">{$LNG.OPEN_BY_ADJUSTMENT_NO}</h5>
				</div>
				<a href="adjustment.php?a=open" class="stretched-link"></a>
			</div>
		</div>
	</div>
</div>
{/if}

{if $sessioninfo.privilege.PO and file_exists('po.php') and ($smarty.request.id eq 'po' || $smarty.request.id eq 'purchase_order')}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">{$LNG.PO}</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white animated fadeInDown">
			<li class="breadcrumb-item">
				<a href="home.php">{$LNG.DASHBOARD}</a>
			</li>
			<li class="breadcrumb-item active">{$LNG.PO}</li>
		</ol>
	</nav>
<div class="container p-0 mt-5">
	<div class="d-flex flex-column justify-content-center align-items-center">
	{if $smarty.session.po.id>0 and $smarty.session.po.branch_id eq $sessioninfo.branch_id}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-warning-transparent">
							<i class="fas fa-clock text-warning"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$CONTINUE_LAST_PO}</h5>
						</div>
					</div>
				</div>
				<a href="po.php" class="stretched-link"></a>
			</div>
		</div>
	{/if}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-info-transparent">
							<i class="fas fa-plus text-info"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CREATE_NEW_PO}</h5>
						</div>
					</div>
				</div>
				<a href="po.php?a=new_po" class="stretched-link"></a>
			</div>
		</div>
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-success-transparent">
							<i class="fas fa-search text-success"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.OPEN_BY_PO_NO}</h5>
						</div>
					</div>
				</div>
				<a href="po.php?a=open" class="stretched-link"></a>
			</div>
		</div>
	</div>
</div>
{/if}

{if $sessioninfo.privilege.STOCK_TAKE and file_exists('stock_take.php') and $smarty.request.id eq 'stock_take'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">{$LNG.STOCK_TAKE}</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white animated fadeInDown">
			<li class="breadcrumb-item">
				<a href="home.php">{$LNG.DASHBOARD}</a>
			</li>
			<li class="breadcrumb-item active">{$LNG.STOCK_TAKE}</li>
		</ol>
	</nav>
<div class="container p-0 mt-5">
	<div class="d-flex flex-column justify-content-center align-items-center">
	{if $smarty.session.st.date and $smarty.session.st.location and $smarty.session.st.shelf and $smarty.session.st.branch_id eq $sessioninfo.branch_id}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-warning-transparent">
							<i class="fas fa-clock text-warning"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CONTINUE_LAST_STOCK_TAKE}</h5>
						</div>
					</div>
				</div>
				<a href="stock_take.php?a=show_scan" class="stretched-link"></a>
			</div>
		</div>
	{/if}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-success-transparent">
							<i class="fas fa-search text-success"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.OPEN_STOCK_TAKE}</h5>
						</div>
					</div>
				</div>
				<a href="stock_take.php?a=stock_take" class="stretched-link"></a>
			</div>
		</div>
		{*<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-success-transparent">
							<i class="fas fa-search text-success"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">Open Existing Stock Take</h5>
						</div>
					</div>
				</div>
				<a href="stock_take.php?a=open" class="stretched-link"></a>
			</div>
		</div>*}
	</div>
</div>
{/if}

{if file_exists('batch_barcode.php') and $smarty.request.id eq 'batch_barcode'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">{$LNG.BATCH_BARCODE}</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white animated fadeInDown">
			<li class="breadcrumb-item">
				<a href="home.php">{$LNG.DASHBOARD}</a>
			</li>
			<li class="breadcrumb-item active">{$LNG.BATCH_BARCODE}</li>
		</ol>
	</nav>
<div class="container p-0 mt-5">
	<div class="d-flex flex-column justify-content-center align-items-center">
	{if $smarty.session.batch_barcode.id && $smarty.session.batch_barcode.branch_id eq $sessioninfo.branch_id}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-warning-transparent">
							<i class="fas fa-clock text-warning"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CONTINUE_LAST_BATCH}</h5>
						</div>
					</div>
				</div>
				<a href="batch_barcode.php" class="stretched-link"></a>
			</div>
		</div>
	{/if}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-info-transparent">
							<i class="fas fa-plus text-info"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CREATE_NEW_BATCH}</h5>
						</div>
					</div>
				</div>
				<a href="batch_barcode.php?a=new_batch_barcode" class="stretched-link"></a>
			</div>
		</div>
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-success-transparent">
							<i class="fas fa-search text-success"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.OPEN_BATCH_BARCODE_LIST}</h5>
						</div>
					</div>
				</div>
				<a href="batch_barcode.php?a=open" class="stretched-link"></a>
			</div>
		</div>
	</div>
</div>
{/if}

{if file_exists('promotion.php') and $smarty.request.id eq 'promotion'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">{$LNG.PROMOTION}</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white animated fadeInDown">
			<li class="breadcrumb-item">
				<a href="home.php">{$LNG.DASHBOARD}</a>
			</li>
			<li class="breadcrumb-item active">{$LNG.PROMOTION}</li>
		</ol>
	</nav>
<div class="container p-0 mt-5">
	<div class="d-flex flex-column justify-content-center align-items-center">
	{if $smarty.session.promotion.id>0}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-warning-transparent">
							<i class="fas fa-clock text-warning"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CONTINUE_LAST_PROMOTION}</h5>
						</div>
					</div>
				</div>
				<a href="promotion.php" class="stretched-link"></a>
			</div>
		</div>
	{/if}
		<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12 animated zoomIn">
			<div class="card ">
				<div class="card-body">
					<div class="counter-status d-flex md-mb-0">
						<div class="counter-icon bg-info-transparent">
							<i class="fas fa-plus text-info"></i>
						</div>
						<div class="ml-3 my-auto">
							<h5 class="">{$LNG.CREATE_NEW_PROMOTION}</h5>
						</div>
					</div>
				</div>
				<a href="promotion.php?a=create" class="stretched-link"></a>
			</div>
		</div>
	</div>
</div>
{/if}

{if isset($config.pda_custom_modules) and $smarty.request.id eq 'custom'}
	<div class="breadcrumb-header justify-content-between mt-3 mb-2">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-1">{$LNG.CUSTOM_MODULES}</h4>
			</div>
		</div>
	</div>
	<nav aria-label="breadcrumb m-0 mb-2">
		<ol class="breadcrumb bg-white animated fadeInDown">
			<li class="breadcrumb-item">
				<a href="home.php">{$LNG.DASHBOARD}</a>
			</li>
		</ol>
	</nav>
	<div class="container p-0 mt-5">
		{include file=$config.pda_custom_modules}
	</div>
{/if}

{include file='footer.tpl'}
