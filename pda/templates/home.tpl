{*
3/1/2011 3:07:12 PM Andy
- Add new module: Sales Order

7/26/2011 1:06:26 PM Justin
- Add new modules: GRR & GRN.

11/10/2011 4:36:43 PM Justin
- Added new module "Check Code".

8/29/2012 1:36 PM Andy
- Add privilege checking for DO, GRR, GRN, Adj, Stock Take and Voucher.

9/6/2012 5:45 PM Justin
- Added new stock take search engine.

11/30/2012 2:52:PM Fithri
- PDA - GRA Module

3/7/2013 1:40 PM Justin
- Added new module "Batch Barcode".

2/24/2014 4:24 PM Andy
- Fix all module to check branch ID before show "continue last document" link.
- Fix the wrong session checking for "Continue Last Stock Take" bug.
- Remove the link "Open Existing Stock Take", because its same as "Open Stock Take".

3/20/2014 1:33 PM Justin
- Added new module "Open DO Checklist".

4/11/2014 10:53 AM Fithri
- add new Promotion module for PDA

3/18/2015 4:35 PM Justin
- Enhanced to have custom modules.

11/25/2015 11:00 AM Qiu Ying
- PDA GRN can search GRR

1/9/2019 3:34 PM Justin
- Added new module "DO Picking Verification".

23/9/2019 11:38 AM William 
- Added new module Purchase Order.

02/11/2020 5:24 PM Rayleen 
- Applied new dashboard design (hidden temporarily).

03/11/2020 4:00 PM Rayleen 
- Fix dashboard/menu responsiveness (old codes are commented).

04/11/2020 2:56 PM Rayleen 
- Changed Voucher to Voucher Activation

1/25/2020 9:55 AM William
- Added new module Member Enquiry.

*}

{include file='header.tpl'}
<!-- <nav aria-label="breadcrumb">
	<ol class="breadcrumb bg-white">
		<li class="breadcrumb-item">
			<a href="#">Home</a>
		</li>
		<li class="breadcrumb-item">
			<a href="#">Library</a>
		</li>
		<li class="breadcrumb-item active">Data</li>
	</ol>
</nav> -->

<div class="breadcrumb-header justify-content-between mt-3">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">Dashboard</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0">/ Please Choose Module</span>
		</div>
	</div>
</div>
<div class="row row-sm mt-3">
	{if $sessioninfo.privilege.GRR}
	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body ">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-bar-chart project bg-primary-transparent mx-auto text-primary "></i>
				</div>
				<h6 class="mb-1 text-muted">GRR</h6>
				<a href="home.php?a=menu&id=grr" class="stretched-link"></a>
			</div>
		</div>
	</div>
	{/if}

	{if $sessioninfo.privilege.GRN}
	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body ">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-pie-chart project bg-pink-transparent mx-auto text-pink "></i>
				</div>
				<h6 class="mb-1 text-muted">GRN</h6>
				<a href="home.php?a=menu&id=grn" class="stretched-link"></a>
			</div>
		</div>
	</div>
	{/if}

	{if $sessioninfo.privilege.GRA}
	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-pulse  project bg-teal-transparent mx-auto text-teal "></i>
				</div>
				<h6 class="mb-1 text-muted">GRA</h6>
				<a href="home.php?a=menu&id=gra" class="stretched-link"></a>
			</div>
		</div>
	</div>
	{/if}

	{if $sessioninfo.privilege.ADJ and file_exists('adjustment.php')}
	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body ">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-stats-up project bg-success-transparent mx-auto text-success "></i>
				</div>
				<h6 class="mb-1 text-muted">Adjustment</h6>
				<a href="home.php?a=menu&id=adjustment" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if $config.allow_sales_order and file_exists('sales_order.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-bar-chart project bg-warning-transparent mx-auto text-warning "></i>
				</div>
				<h6 class="mb-1 text-muted">Sales Order</h6>
				<a href="home.php?a=menu&id=sales_order" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if $sessioninfo.privilege.DO}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-truck  project bg-purple-transparent mx-auto text-purple "></i>
				</div>
				<h6 class="mb-1 text-muted">DO</h6>
				<a href="home.php?a=menu&id=do" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if $sessioninfo.privilege.PO and file_exists('po.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-bag  project bg-secondary-transparent mx-auto text-secondary "></i>
				</div>
				<h6 class="mb-1 text-muted">PO</h6>
				<a href="home.php?a=menu&id=po" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if $sessioninfo.privilege.STOCK_TAKE and file_exists('stock_take.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-pulse  project bg-primary-transparent mx-auto text-primary "></i>
				</div>
				<h6 class="mb-1 text-muted">Stock Take</h6>
				<a href="home.php?a=menu&id=stock_take" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if file_exists('promotion.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-pulse  project bg-success-transparent mx-auto text-success "></i>
				</div>
				<h6 class="mb-1 text-muted">Promotion</h6>
				<a href="home.php?a=menu&id=promotion" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if file_exists('batch_barcode.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-pulse  project bg-pink-transparent mx-auto text-pink "></i>
				</div>
				<h6 class="mb-1 text-muted">Batch Barcode</h6>
				<a href="home.php?a=menu&id=batch_barcode" class="stretched-link"></a>
			</div>
		</div>
	</div>
	{/if}

	{if $sessioninfo.privilege.MST_VOUCHER and file_exists('mst_voucher.php')}
	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-gift  project bg-teal-transparent mx-auto text-teal "></i>
				</div>
				<h6 class="mb-1 text-muted">Voucher Activation</h6>
				<a href="mst_voucher.php" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if file_exists('check_code.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-check-box  project bg-orange-transparent mx-auto text-orange "></i>
				</div>
				<h6 class="mb-1 text-muted">Check Code</h6>
				<a href="check_code.php" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}
	
  	{if $config.membership_module && file_exists('member_enquiry.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-pulse  project bg-teal-transparent mx-auto text-teal "></i>
				</div>
				<h6 class="mb-1 text-muted">Member Enquiry</h6>
				<a href="member_enquiry.php" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if isset($config.pda_custom_modules)}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-pulse  project bg-teal-transparent mx-auto text-teal "></i>
				</div>
				<h6 class="mb-1 text-muted">Custom Modules</h6>
				<a href="home.php?a=menu&id=custom" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}
</div>
{*
<ul>
	{if $sessioninfo.privilege.DO}
		<li>DO
		    <ul>
		        {if $smarty.session.do.id>0 and $smarty.session.do.branch_id eq $sessioninfo.branch_id}
		            <li><a href="do.php">Continue Last DO</a></li>
		        {/if}
			    <li><a href="do.php?a=new_do">Create New Transfer DO</a></li>
			    <li><a href="do.php?a=new_do&do_type=open">Create New Cash Sales DO</a></li>
			    <li><a href="do.php?a=new_do&do_type=credit_sales">Create New Credit Sales DO</a></li>
				<li><a href="do.php?a=open">Open DO by Do No</a></li>
				<li><a href="do.php?a=open_checklist">Open DO Checklist</a></li>
				<li><a href="do.picking_verification.php?a=open">DO Picking Verification</a></li>
		     </ul>
		</li>
	{/if}
	{if $config.allow_sales_order and file_exists('sales_order.php')}
		<li>Sales Order
			<ul>
			    {if $smarty.session.so.id>0 and $smarty.session.so.branch_id eq $sessioninfo.branch_id}
		            <li><a href="sales_order.php">Continue Last Sales Order</a></li>
		        {/if}
			    <li><a href="sales_order.php?a=new_so">Create New Sales Order</a></li>
			    <li><a href="sales_order.php?a=open">Open by Order No</a></li>
			</ul>
		</li>
	{/if}

	{if $sessioninfo.privilege.GRR}
		<li>GRR
			<ul>
				{if $smarty.session.grr.id>0 and $smarty.session.grr.branch_id eq $sessioninfo.branch_id}
					<li><a href="goods_receiving_record.php">Continue Last GRR</a></li>
				{/if}
				<li><a href="goods_receiving_record.php?a=new_grr">Create New GRR</a></li>
				<li><a href="goods_receiving_record.php?a=open">Open by GRR No</a></li>
			</ul>
		</li>
	{/if}
	
	{if $sessioninfo.privilege.GRN}
	<li>GRN
		<ul>
			{if $smarty.session.grn.id>0 and $smarty.session.grn.branch_id eq $sessioninfo.branch_id}
				<li><a href="goods_receiving_note.php">Continue Last GRN</a></li>
			{/if}
			<li><a href="goods_receiving_note.php?a=show_grr_list">Create New GRN</a></li>
			<li><a href="goods_receiving_note.php?a=open">Open by GRN No / GRR No</a></li>
		</ul>
	</li>
	{/if}
	
	{if $sessioninfo.privilege.GRA}
	<li>GRA
		<ul>
			{if $smarty.session.gra.id>0 and $smarty.session.gra.branch_id eq $sessioninfo.branch_id}
				<li><a href="goods_return_advice.php">Continue Last GRA</a></li>
			{/if}
			<li><a href="goods_return_advice.php?a=new_gra">Create New GRA</a></li>
			<li><a href="goods_return_advice.php?a=open">Open by GRA No</a></li>
		</ul>
	</li>
	{/if}
	
	{if $sessioninfo.privilege.ADJ and file_exists('adjustment.php')}
		<li>Adjustment
			<ul>
			    {if $smarty.session.adj.id>0 and $smarty.session.adj.branch_id eq $sessioninfo.branch_id}
		            <li><a href="adjustment.php">Continue Last Adjustment</a></li>
		        {/if}
			    <li><a href="adjustment.php?a=new_adj">Create New Adjustment</a></li>
			    <li><a href="adjustment.php?a=open">Open by Adjustment No</a></li>
			</ul>
		</li>
	{/if}
	{if $sessioninfo.privilege.PO and file_exists('po.php')}
		<li>PO
			<ul>
		        {if $smarty.session.po.id>0 and $smarty.session.po.branch_id eq $sessioninfo.branch_id}
		            <li><a href="po.php">Continue Last PO</a></li>
		        {/if}
			    <li><a href="po.php?a=new_po">Create New PO</a></li>
				<li><a href="po.php?a=open">Open PO by Po No</a></li>
			</ul>
		</li>
	{/if}
	{if $sessioninfo.privilege.STOCK_TAKE and file_exists('stock_take.php')}
		<li>Stock Take
			<ul>
			    {if $smarty.session.st.date and $smarty.session.st.location and $smarty.session.st.shelf and $smarty.session.st.branch_id eq $sessioninfo.branch_id}
		            <li><a href="stock_take.php?a=show_scan">Continue Last Stock Take</a></li>
		        {/if}
			    <li style="display:none;"><a href="stock_take.php?a=stock_take">Open Stock Take</a></li>
			</ul>
		</li>
	{/if}
	{if file_exists('batch_barcode.php')}
		<li>Batch Barcode
			<ul>
			    {if $smarty.session.batch_barcode.id && $smarty.session.batch_barcode.branch_id eq $sessioninfo.branch_id}
		            <li><a href="batch_barcode.php">Continue Last Batch</a></li>
		        {/if}
			    <li><a href="batch_barcode.php?a=new_batch_barcode">Create New Batch</a></li>
			    <li><a href="batch_barcode.php?a=open">Open Batch Barcode List</a></li>
			</ul>
		</li>
	{/if}
	{if file_exists('promotion.php')}
		<li>Promotion
			<ul>
			    {if $smarty.session.promotion.id>0}
				<li><a href="promotion.php">Continue Last Promotion</a></li>
				{/if}
			    <li><a href="promotion.php?a=create">Create New Promotion</a></li>
			</ul>
		</li>
	{/if}
	{if $sessioninfo.privilege.MST_VOUCHER and file_exists('mst_voucher.php')}
		<li><a href="mst_voucher.php">Voucher</a></li>
	{/if}
	{if file_exists('check_code.php')}
		<li><a href="check_code.php">Check Code</a></li>
	{/if}
	
	{if isset($config.pda_custom_modules)}
		{include file=$config.pda_custom_modules}
	{/if}
</ul>
</p>
*}
{include file='footer.tpl'}
