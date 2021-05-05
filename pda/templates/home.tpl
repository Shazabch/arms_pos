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

<p class="menu-title">Dashboard</p>
<p class="menu-sub-title">Please choose module</p>
<div class="home-row">
	{if $sessioninfo.privilege.GRR}
	  <div class="home-column">
	  	<a href="home.php?a=menu&id=grr">
		  	<div id="home-grr">
		  	</div>
		    <p>GRR</p>
	    </a>
	  </div>
	{/if}

	{if $sessioninfo.privilege.GRN}
	  <div class="home-column">
	  	<a href="home.php?a=menu&id=grn">
		    <div id="home-grn">
		  	</div>
		    <p>GRN</p>
	    </a>
	  </div>
	{/if}

	{if $sessioninfo.privilege.GRA}
	  <div class="home-column">
	  	<a href="home.php?a=menu&id=gra">
		    <div id="home-gra">
		  	</div>
		    <p>GRA</p>
	    </a>
	  </div>
	{/if}

	{if $sessioninfo.privilege.ADJ and file_exists('adjustment.php')}
	  <div class="home-column">
	  	<a  href="home.php?a=menu&id=adjustment">
		  	<div id="home-adjustment">
		  	</div>
		    <p>Adjustment</p>
	    </a>
	  </div>
  	{/if}

  	{if $config.allow_sales_order and file_exists('sales_order.php')}
  	<div class="home-column">
	  	<a  href="home.php?a=menu&id=sales_order">
		    <div id="home-sales-order">
		  	</div>
		    <p>Sales Order</p>
	    </a>
  	</div>
  	{/if}

  	{if $sessioninfo.privilege.DO}
	  <div class="home-column">
	  	<a href="home.php?a=menu&id=do">
		    <div id="home-do">
		  	</div>
		    <p>DO</p>
	    </a>
	  </div>
  	{/if}

  	{if $sessioninfo.privilege.PO and file_exists('po.php')}
	<div class="home-column">
	  	<a href="home.php?a=menu&id=po">
		    <div id="home-po">
		  	</div>
		    <p>PO</p>
	    </a>
  	</div>
  	{/if}

  	{if $sessioninfo.privilege.STOCK_TAKE and file_exists('stock_take.php')}
	<div class="home-column">
	  	<a href="home.php?a=menu&id=stock_take">
		    <div id="home-stock-take">
		  	</div>
		    <p>Stock Take</p>
	    </a>
  	</div>
  	{/if}

  	{if file_exists('promotion.php')}
	<div class="home-column">
	  	<a  href="home.php?a=menu&id=promotion">
		    <div id="home-promotion">
		  	</div>
		    <p>Promotion</p>
	    </a>
  	</div>
  	{/if}

  	{if file_exists('batch_barcode.php')}
	<div class="home-column">
	  	<a href="home.php?a=menu&id=batch_barcode">
		    <div id="home-batch-barcode">
		  	</div>
		    <p>Batch Barcode</p>
	    </a>
	</div>
	{/if}

	{if $sessioninfo.privilege.MST_VOUCHER and file_exists('mst_voucher.php')}
 	<div class="home-column">
	  	<a href="mst_voucher.php">
		    <div id="home-voucher">
		  	</div>
		    <p>Voucher Activation</p>
	    </a>
  	</div>
  	{/if}

  	{if file_exists('check_code.php')}
  	<div class="home-column">
	  	<a href="check_code.php">
		    <div id="home-check-code">
		  	</div>
		    <p>Check Code</p>
	    </a>
  	</div>
  	{/if}
	
  	{if $config.membership_module && file_exists('member_enquiry.php')}
  	<div class="home-column">
	  	<a href="member_enquiry.php">
		    <div id="home-member">
		  	</div>
		    <p>Member Enquiry</p>
	    </a>
  	</div>
  	{/if}

  	{if isset($config.pda_custom_modules)}
  	<div class="home-column">
	  	<a href="home.php?a=menu&id=custom">
		    <div id="home-custom-modules">
		  	</div>
			<p>Custom Modules</p>
	    </a>
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
