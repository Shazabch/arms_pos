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

<div class="breadcrumb-header justify-content-between mt-3 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">{$LNG.DASHBOARD}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0">/ {$LNG.CHOOSE_MODULE_MSG}</span>
		</div>
	</div>
</div>
<div class="row row-sm mt-3">
	{if $sessioninfo.privilege.GRR}
	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body ">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-package project bg-primary-transparent mx-auto text-primary "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.GRR}</h6>
				<a href="home.php?a=menu&id=grr" class="stretched-link"></a>
			</div>
		</div>
	</div>
	{/if}

	{if $sessioninfo.privilege.GRN}
	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body ">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-pie-chart project bg-pink-transparent mx-auto text-pink "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.GRN}</h6>
				<a href="home.php?a=menu&id=grn" class="stretched-link"></a>
			</div>
		</div>
	</div>
	{/if}

	{if $sessioninfo.privilege.GRA}
	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-pulse  project bg-teal-transparent mx-auto text-teal "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.GRA}</h6>
				<a href="home.php?a=menu&id=gra" class="stretched-link"></a>
			</div>
		</div>
	</div>
	{/if}

	{if $sessioninfo.privilege.ADJ and file_exists('adjustment.php')}
	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body ">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-arrows-corner project bg-success-transparent mx-auto text-success "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.ADJUSTMENT}</h6>
				<a href="home.php?a=menu&id=adjustment" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if $config.allow_sales_order and file_exists('sales_order.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-bar-chart project bg-warning-transparent mx-auto text-warning "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.ADJUSTMENT}</h6>
				<a href="home.php?a=menu&id=sales_order" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if $sessioninfo.privilege.DO}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-truck  project bg-purple-transparent mx-auto text-purple "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.DO}</h6>
				<a href="home.php?a=menu&id=do" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if $sessioninfo.privilege.PO and file_exists('po.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-bag  project bg-secondary-transparent mx-auto text-secondary "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.PO}</h6>
				<a href="home.php?a=menu&id=po" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if $sessioninfo.privilege.STOCK_TAKE and file_exists('stock_take.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-money  project bg-primary-transparent mx-auto text-primary "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.STOCK_TAKE}</h6>
				<a href="home.php?a=menu&id=stock_take" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if file_exists('promotion.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-stats-up  project bg-success-transparent mx-auto text-success "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.PROMOTION}</h6>
				<a href="home.php?a=menu&id=promotion" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if file_exists('batch_barcode.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-layout-column4-alt  project bg-pink-transparent mx-auto text-pink "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.BATCH_BARCODE}</h6>
				<a href="home.php?a=menu&id=batch_barcode" class="stretched-link"></a>
			</div>
		</div>
	</div>
	{/if}

	{if $sessioninfo.privilege.MST_VOUCHER and file_exists('mst_voucher.php')}
	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-gift  project bg-teal-transparent mx-auto text-teal "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.VOUCHER_ACTIVATION}</h6>
				<a href="mst_voucher.php" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if file_exists('check_code.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-check-box  project bg-orange-transparent mx-auto text-orange "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.CHECK_CODE}</h6>
				<a href="check_code.php" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}
	
  	{if $config.membership_module && file_exists('member_enquiry.php')}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-user  project bg-teal-transparent mx-auto text-teal "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.MEMBER_ENQUIRY}</h6>
				<a href="member_enquiry.php" class="stretched-link"></a>
			</div>
		</div>
	</div>
  	{/if}

  	{if isset($config.pda_custom_modules)}
  	<div class="col-xl-3 col-lg-6 col-sm-6 col-6 animated zoomIn">
		<div class="card text-center">
			<div class="card-body">
				<div class="feature widget-2 text-center mt-0 mb-3">
					<i class="ti-pulse  project bg-teal-transparent mx-auto text-teal "></i>
				</div>
				<h6 class="mb-1 text-muted">{$LNG.CUSTOM_MODULES}</h6>
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
		            <li><a href="do.php">{$LNG.CONTINUE_LAST_DO}</a></li>
		        {/if}
			    <li><a href="do.php?a=new_do">{$LNG.CREATE_NEW_TRANSFER_DO}</a></li>
			    <li><a href="do.php?a=new_do&do_type=open">{$LNG.CREATE_NEW_CASH_SALES_DO}</a></li>
			    <li><a href="do.php?a=new_do&do_type=credit_sales">{$LNG.CREATE_NEW_CREDIT_SALES_DO}</a></li>
				<li><a href="do.php?a=open">{$LNG.OPEN_DO_BY_DO_NO}</a></li>
				<li><a href="do.php?a=open_checklist">{$LNG.OPEN_DO_CHECKLIST}</a></li>
				<li><a href="do.picking_verification.php?a=open">{$LNG.DO_PICKING_VERIFICATION}</a></li>
		     </ul>
		</li>
	{/if}
	{if $config.allow_sales_order and file_exists('sales_order.php')}
		<li>Sales Order
			<ul>
			    {if $smarty.session.so.id>0 and $smarty.session.so.branch_id eq $sessioninfo.branch_id}
		            <li><a href="sales_order.php">{$LNG.CONTINUE_LAST_SALES_ORDER}</a></li>
		        {/if}
			    <li><a href="sales_order.php?a=new_so">{$LNG.CREATE_NEW_SALES_ORDER}</a></li>
			    <li><a href="sales_order.php?a=open">{$LNG.OPEN_BY_ORDER_NO}</a></li>
			</ul>
		</li>
	{/if}

	{if $sessioninfo.privilege.GRR}
		<li>GRR
			<ul>
				{if $smarty.session.grr.id>0 and $smarty.session.grr.branch_id eq $sessioninfo.branch_id}
					<li><a href="goods_receiving_record.php">{$LNG.}</a></li>
				{/if}
				<li><a href="goods_receiving_record.php?a=new_grr">{$LNG.CREATE_NEW_GRR}</a></li>
				<li><a href="goods_receiving_record.php?a=open">{$LNG.OPEN_BY_GRR_NO}</a></li>
			</ul>
		</li>
	{/if}
	
	{if $sessioninfo.privilege.GRN}
	<li>GRN
		<ul>
			{if $smarty.session.grn.id>0 and $smarty.session.grn.branch_id eq $sessioninfo.branch_id}
				<li><a href="goods_receiving_note.php">{$LNG.CONTINUE_LAST_GRN}</a></li>
			{/if}
			<li><a href="goods_receiving_note.php?a=show_grr_list">{$LNG.CREATE_NEW_GRN}</a></li>
			<li><a href="goods_receiving_note.php?a=open">{$LNG.OPEN_BY_GRN_GRR_NO}</a></li>
		</ul>
	</li>
	{/if}
	
	{if $sessioninfo.privilege.GRA}
	<li>GRA
		<ul>
			{if $smarty.session.gra.id>0 and $smarty.session.gra.branch_id eq $sessioninfo.branch_id}
				<li><a href="goods_return_advice.php">{$LNG.CONTINUE_LAST_GRA}</a></li>
			{/if}
			<li><a href="goods_return_advice.php?a=new_gra">{$LNG.CREATE_NEW_GRA}</a></li>
			<li><a href="goods_return_advice.php?a=open">{$LNG.OPEN_BY_GRA_NO}</a></li>
		</ul>
	</li>
	{/if}
	
	{if $sessioninfo.privilege.ADJ and file_exists('adjustment.php')}
		<li>Adjustment
			<ul>
			    {if $smarty.session.adj.id>0 and $smarty.session.adj.branch_id eq $sessioninfo.branch_id}
		            <li><a href="adjustment.php">{#LNG.CONTINUE_LAST_ADJUSTMENT}</a></li>
		        {/if}
			    <li><a href="adjustment.php?a=new_adj">{#LNG.CREATE_NEW_ADJUSTMENT}</a></li>
			    <li><a href="adjustment.php?a=open">{#LNG.OPEN_BY_ADJUSTMENT_NO}</a></li>
			</ul>
		</li>
	{/if}
	{if $sessioninfo.privilege.PO and file_exists('po.php')}
		<li>PO
			<ul>
		        {if $smarty.session.po.id>0 and $smarty.session.po.branch_id eq $sessioninfo.branch_id}
		            <li><a href="po.php">{$LNG.CONTINUE_LAST_PO}</a></li>
		        {/if}
			    <li><a href="po.php?a=new_po">{$LNG.CREATE_NEW_PO}</a></li>
				<li><a href="po.php?a=open">{$LNG.OPEN_BY_PO_NO}</a></li>
			</ul>
		</li>
	{/if}
	{if $sessioninfo.privilege.STOCK_TAKE and file_exists('stock_take.php')}
		<li>Stock Take
			<ul>
			    {if $smarty.session.st.date and $smarty.session.st.location and $smarty.session.st.shelf and $smarty.session.st.branch_id eq $sessioninfo.branch_id}
		            <li><a href="stock_take.php?a=show_scan">{$LNG.CONTINUE_LAST_STOCK_TAKE}</a></li>
		        {/if}
			    <li style="display:none;"><a href="stock_take.php?a=stock_take">{$LNG.OPEN_STOCK_TAKE}</a></li>
			</ul>
		</li>
	{/if}
	{if file_exists('batch_barcode.php')}
		<li>Batch Barcode
			<ul>
			    {if $smarty.session.batch_barcode.id && $smarty.session.batch_barcode.branch_id eq $sessioninfo.branch_id}
		            <li><a href="batch_barcode.php">{$LNG.CONTINUE_LAST_BATCH}</a></li>
		        {/if}
			    <li><a href="batch_barcode.php?a=new_batch_barcode">{$LNG.CREATE_NEW_BATCH}</a></li>
			    <li><a href="batch_barcode.php?a=open">{$LNG.OPEN_BATCH_BARCODE_LIST}</a></li>
			</ul>
		</li>
	{/if}
	{if file_exists('promotion.php')}
		<li>Promotion
			<ul>
			    {if $smarty.session.promotion.id>0}
				<li><a href="promotion.php">{$LNG.CONTINUE_LAST_PROMOTION}</a></li>
				{/if}
			    <li><a href="promotion.php?a=create">{$LNG.CREATE_NEW_PROMOTION}</a></li>
			</ul>
		</li>
	{/if}
	{if $sessioninfo.privilege.MST_VOUCHER and file_exists('mst_voucher.php')}
		<li><a href="mst_voucher.php">{$LNG.VOUCHER}</a></li>
	{/if}
	{if file_exists('check_code.php')}
		<li><a href="check_code.php">{$LNG.CHECK_CODE}</a></li>
	{/if}
	
	{if isset($config.pda_custom_modules)}
		{include file=$config.pda_custom_modules}
	{/if}
</ul>
</p>
*}
{include file='footer.tpl'}
