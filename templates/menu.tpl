<!-- <div id=goto_branch_popup class="curtain_popup" style="width:300px;height:100px;display:none;">
	<div style="text-align:right"><img src=/ui/closewin.png onclick="default_curtain_clicked()"></div>
	<h3>Select Branch to login</h3>
	<span id=goto_branch_list></span> <button onclick="goto_branch_select()">Login</button>
</div> -->
<!-- Basic modal -->
<div class="modal fade" id="goto_branch_popup">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content modal-content-demo">
			<div class="modal-header">
				<h6 class="modal-title">Select Branch to login</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<label>Select Branch</label>
				<span id=goto_branch_list></span>
				<button class="btn btn-primary" onclick="goto_branch_select()">Login</button>
			</div>
		</div>
	</div>

</div>
<!-- End Basic modal -->

<div class="">
	<div class="horizontal-main hor-menu clearfix side-header">
		<div class="horizontal-mainwrapper container clearfix">
			<!--Nav-->
			<nav class="horizontalMenu clearfix">
				<ul class="horizontalMenu-list">
{if $sessioninfo}

			<li aria-haspopup="true"><a href="home.php" class=""><i class="fas fa-home"></i> Home</a></li>
			<li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-tachometer-alt"></i> Dashboard<i class="fe fe-chevron-down horizontal-icon"></i></a>
				<ul class="sub-menu">
					<li aria-haspopup="true"><a href="home.php" class="slide-item"> Home</a></li>
					<li aria-haspopup="true"><a href="product-details.html" class="slide-item">Go To Branch</a></li>
				</ul>
			</li>
	<!-- Administrator -->
	{if $sessioninfo.privilege.USERS_ADD or $sessioninfo.privilege.USERS_MNG or $sessioninfo.privilege.USERS_ACTIVATE or $sessioninfo.privilege.MST_APPROVAL or $sessioninfo.privilege.POS_IMPORT or $sessioninfo.privilege.SKU_EXPORT or $sessioninfo.level>=9999}
	<li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-user-tie"></i> Administrator <i class="fe fe-chevron-down horizontal-icon"></i></a>
		<ul class="sub-menu">
			<li aria-haspopup="true"><a href="" class="slide-item">O level</a></li>
			{if $sessioninfo.privilege.USERS_MNG or $sessioninfo.privilege.USERS_ACTIVATE}
				<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Users</a>
					<ul class="sub-menu">
						{if $sessioninfo.privilege.USERS_ADD}<li aria-haspopup="true"><a href="users.php?t=create" class="slide-item">Create Profile</a></li>{/if}
						{if $sessioninfo.privilege.USERS_ACTIVATE}<li aria-haspopup="true"><a href="users.php?t=update" class="slide-item">Update Profile</a></li>{/if}
						{if $sessioninfo.level==500 || $sessioninfo.level>=9999}<li aria-haspopup="true"><a href="admin.inactive_user.php" class="slide-item">No-Activity User Report</a></li>{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/users.application.php") and $sessioninfo.privilege.USERS_EFORM }
						<li aria-haspopup="true" class="slide-item sub-menu-sub"><a href="#">User Application E-Form</a>
							<ul class="sub-menu">
								{if $config.single_server_mode or (!$config.single_server_mode and $sessioninfo.branch_id eq 1)}<li aria-haspopup="true"><a  href="users.application.php?a=generate_code" class="slide-item">Generate QR Code</a></li>{/if}
								<li aria-haspopup="true"><a href="users.application.php?a=application_list" class="slide-item">Application List</a></li>
							</ul>
						</li>
						{/if}
					</ul>
				</li>
			{/if}
			{if $sessioninfo.privilege.MST_APPROVAL}<li aria-haspopup="true"><a href="approval_flow.php" class="slide-item">Approval Flows</a></li>{/if}
			{if $sessioninfo.level>=9999 and $BRANCH_CODE eq 'HQ'}
			<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Selling Price</a>
				<ul class="sub-menu">
					<li aria-haspopup="true"><a href="admin.copy_selling.php" class="slide-item">Copy Selling Price</a></li>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_selling.php")}
					<li aria-haspopup="true"><a href="admin.import_selling.php" class="slide-item">Import Selling Price</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.update_price_type.php")}
					<li aria-haspopup="true"><a href="admin.update_price_type.php" class="slide-item">Update Price Type</a></li>
					{/if}
				</ul>
			</li>			
			{/if}
			{if $BRANCH_CODE eq 'HQ' and $sessioninfo.level>=9999 and ($sessioninfo.privilege.ADMIN_UPDATE_SKU_MASTER_COST and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.update_sku_master_cost.php"))}
				<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Cost Price</a>
					<ul class="sub-menu">
						<li aria-haspopup="true"><a href="admin.update_sku_master_cost.php" class="slide-item">Update SKU Master Cost</a></li>
					</ul>
				</li>
			{/if}
			{if $sessioninfo.level>=9999 and $BRANCH_CODE eq 'HQ'}
				<li aria-haspopup="true"><a href="admin.sku_block.php" class="slide-item">Block/Ublock in SKU in PO (CSV)</a></li>
			{/if}

			{if $sessioninfo.privilege.SKU_EXPORT ||  $sessioninfo.level>=9999 || $sessioninfo.privilege.POS_IMPORT || $sessioninfo.privilege.ALLOW_IMPORT_SKU || $sessioninfo.privilege.ALLOW_IMPORT_VENDOR  || $sessioninfo.privilege.ALLOW_IMPORT_BRAND || $sessioninfo.privilege.ALLOW_IMPORT_DEBTOR || $sessioninfo.privilege.ALLOW_IMPORT_UOM || $sessioninfo.privilege.ALLOW_IMPORT_DEACTIVATE_SKU}
			<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Import / Export</a>
				<ul class="sub-menu">
				{if $sessioninfo.privilege.SKU_EXPORT}
					<li aria-haspopup="true"><a class="slide-item" href="admin.sku_export.php">Export SKU Items</a></li>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.weightcode_export.php") and !$config.consignment_modules}
						<li aria-haspopup="true"><a class="slide-item" href="admin.weightcode_export.php">Export Weighing Scale Items</a></li>
					{/if}
				{/if}
				{if $sessioninfo.level>=9999 || $sessioninfo.privilege.POS_IMPORT}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.export_points.php")}<li aria-haspopup="true"><a class="slide-item" href="admin.export_points.php">Export Member Points</a></li>{/if}
				{/if}
				{if $sessioninfo.level>=9999 || $sessioninfo.privilege.POS_IMPORT}
					{*<li aria-haspopup="true"><a class="slide-item" href="admin.pos_transaction_import.php">Import POS Transaction</a></li>*}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_pos_sales.php")}
					    {*<li aria-haspopup="true"><a class="slide-item" href="admin.import_pos_sales.php">Import POS Sales</a></li>*}
					{/if}
		  			<li aria-haspopup="true"><a class="slide-item" href="admin.stockchk_import.php">Import Stock Take</a></li>
				{/if}
				{if $config.sku_application_require_multics && ($sess.level==500 || $sessioninfo.level>=9999)}
				<li aria-haspopup="true"><a class="slide-item" href="admin.update_dat.php">Update Multics DAT files</a></li>
				{/if}
	            {if $sessioninfo.level>=9999 || $sessioninfo.privilege.POS_IMPORT}
	                {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_member_points.php")}
	                    <li aria-haspopup="true"><a class="slide-item" href="admin.import_member_points.php">Import Member Points</a></li>
	                {/if}
	                {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_members.php")}
	                    <li aria-haspopup="true"><a class="slide-item" href="admin.import_members.php">Import Members</a></li>
	                {/if}
	                {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.preactivate_member_cards.php")}
	                    <li aria-haspopup="true"><a class="slide-item" href="admin.preactivate_member_cards.php">Pre-activate Member Cards</a></li>
	                {/if}
	            {/if}
				
				{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_SKU)}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_sku.php")}
	                    <li aria-haspopup="true"><a class="slide-item" href="admin.import_sku.php">Import SKU</a></li>
	                {/if}
				{/if}
				
				{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_VENDOR)}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_vendor.php")}
	                    <li aria-haspopup="true"><a class="slide-item" href="admin.import_vendor.php">Import Vendor</a></li>
	                {/if}
				{/if}
				
				{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_BRAND)}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_brand.php")}
	                    <li aria-haspopup="true"><a class="slide-item" href="admin.import_brand.php">Import Brand</a></li>
	                {/if}
				{/if}
	            
	            {if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_DEBTOR) && file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_debtor.php")}
					<li aria-haspopup="true"><a class="slide-item" href="admin.import_debtor.php">Import Debtor</a></li>
				{/if}
				{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_UOM) && file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_uom.php")}
	                 <li aria-haspopup="true"><a class="slide-item" href="admin.import_uom.php">Import UOM</a></li>
	            {/if}
				{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_DEACTIVATE_SKU) && file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.deactivate_sku.php")}
	                 <li aria-haspopup="true"><a class="slide-item" href="admin.deactivate_sku.php">Deactivate SKU by CSV</a></li>
	            {/if}
				</ul>
			</li>
			{/if}
			{if $sessioninfo.level>=9999 and $BRANCH_CODE eq 'HQ' and $config.show_tracker}
			    <li aria-haspopup="true"><a class="slide-item" href="admin.arms_tracker.php">ARMS Request Tracker</a></li>
			{/if}
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.monthly_closing.php") and $config.monthly_closing and $sessioninfo.privilege.ADMIN_MONTHLY_CLOSING}
				<li  aria-haspopup="true" class="sub-menu-sub"><a>Monthly Closing</a>
					<ul class="sub-menu">
						<li aria-haspopup="true"><a class="slide-item" href="admin.monthly_closing.php">Monthly Closing</a></li>
						<li aria-haspopup="true"><a class="slide-item" href="admin.monthly_closing.php?a=show_closed_month">Monthly Closing History</a></li>
					</ul>
				</li>
			{/if}
			{if $sessioninfo.level>=9999}
				<li aria-haspopup="true"><a class="slide-item" href="admin.update_log.php">System Update log</a></li>
				<li aria-haspopup="true"><a class="slide-item" href="sales_target.php">Sales Target</a></li>
				<li arai-haspopup="true" class="sub-menu-sub"><a href="#">Settings</a>
					<ul class="sub-menu">
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.settings.php")}
						<li aria-haspopup="true"><a class="slide-item" href="admin.settings.php?file=color.txt">Edit Colour</a></li>
						<li aria-haspopup="true"><a class="slide-item" href="admin.settings.php?file=size.txt">Edit Size</a></li>
						{/if}
						{* if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.upload_config.php")}
						<li aria-haspopup="true"><a class="slide-item" href="admin.upload_config.php">Upload Config CSV</a></li>
						{/if *}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.upload_logo.php")}
						<li aria-haspopup="true"><a class="slide-item" href="admin.upload_logo.php">Edit Logo Settings</a></li>
						{/if}
					</ul>
				</li>
				{*
				{if $BRANCH_CODE eq 'HQ' and $sessioninfo.privilege.SERVER_MAINTENANCE}
				    <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Server Maintenance</a>
				        <ul class="sub-menu">
				            {if file_exists("admin.server_maintenance.archive_database.php")}
				            <li aria-haspopup="true"><a class="slide-item" href="admin.server_maintenance.archive_database.php">Archive Database</a></li>
				            <li aria-haspopup="true"><a class="slide-item" href="admin.server_maintenance.archive_database.php?a=restore">Restore Database</a></li>
				            {/if}
				        </ul>
				    </li>
				{/if}
				*}
				{if $BRANCH_CODE eq 'HQ' and $sessioninfo.id eq 1}
				    <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Server Management</a>
				        <ul class="sub-menu">
				            {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.config_manager.php")}
				                <li aria-haspopup="true"><a class="slide-item" href="admin.config_manager.php">Config Manager</a></li>
				            {/if}
				            {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.privilege_manager.php")}
				                <li aria-haspopup="true"><a class="slide-item" href="admin.privilege_manager.php">Privilege Manager</a></li>
				            {/if}
				            {*{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.reset_db.php")}
				                <li aria-haspopup="true"><a class="slide-item" href="admin.reset_db.php">Reset Data</a></li>
				            {/if}*}
				        </ul>
					</li>
				{/if}
				{if $config.enable_gst && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_gst_settings.php") && $sessioninfo.level>=9999}
					<li aria-haspopup="true"><a class="slide-item" href="masterfile_gst_settings.php">GST Settings</a></li>
				{/if}
			{/if}
			{if $config.enable_tax and $sessioninfo.level>=9999}
				<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Tax</a>
					<ul class="sub-menu">
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.tax_settings.php") }
						<li aria-haspopup="true"><a class="slide-item" href="admin.tax_settings.php">Tax Settings</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.tax_listing.php") }
						<li aria-haspopup="true"><a class="slide-item" href="admin.tax_listing.php">Tax Listing</a></li>
					{/if}
					</ul>
				</li>
			{/if}
			{* Foreign Currency *}
			{if $config.foreign_currency and ($sessioninfo.privilege.ADMIN_FOREIGN_CURRENCY_RATE_UPDATE)}
				<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Foreign Currency</a>
					<ul class="sub-menu">
						{if $sessioninfo.privilege.ADMIN_FOREIGN_CURRENCY_RATE_UPDATE and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.foreign_currency.rate.php")}
							<li aria-haspopup="true"><a class="slide-item" href="admin.foreign_currency.rate.php">Currency Rate Table</a></li>
						{/if}
					</ul>
				</li>
			{/if}
			{*
			{if $BRANCH_CODE eq 'HQ' && $config.stock_copy && $sessioninfo.privilege.STOCK_COPY}
			    {if file_exists("admin.stock_copy.php")}
			    	<li aria-haspopup="true"><a class="slide-item" href="admin.stock_copy.php">Stock Copy</a></li>
			    {/if}
			{/if}
			*}
		</ul>
	</li>	
	{/if}
<!-- /Administrator -->

<!-- Office -->
    {if !$config.arms_go_modules || ($config.arms_go_modules && ($config.arms_go_enable_official_modules || (!$config.arms_go_enable_official_modules && $BRANCH_CODE ne 'HQ')))}
        {if $sessioninfo.privilege.MST_SKU_APPLY or $sessioninfo.privilege.MST_SKU_APPROVAL or $sessioninfo.privilege.PO or $sessioninfo.privilege.PO_REQUEST or $sessioninfo.privilege.PO_FROM_REQUEST or $sessioninfo.privilege.PO_REPORT or $sessioninfo.privilege.GRN_APPROVAL or $sessioninfo.privilege.GRA or $sessioninfo.privilege.GRR_REPORT or $sessioninfo.privilege.GRN_REPORT or $sessioninfo.privilege.SHIFT_RECORD_VIEW or $sessioninfo.privilege.SHIFT_RECORD_EDIT or $sessioninfo.privilege.PAYMENT_VOUCHER or $sessioninfo.privilege.DO or $sessioninfo.privilege.ADJ or $sessioninfo.privilege.ACCOUNT_EXPORT or $sessioninfo.privilege.OSTRIO_ACCOUNTING_STATUS or $sessioninfo.privilege.SPEED99_INTEGRATION_STATUS or $sessioninfo.privilege.KOMAISO_INTEGRATION_STATUS}
        <li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-briefcase"></i> Office<i class="fe fe-chevron-down horizontal-icon"></i></a>
	        <ul class="sub-menu">
	    
	            {if $sessioninfo.privilege.ADJ}
		            <li aria-haspopup="true" class="sub-menu-sub"><a href="#"> Adjustment</a>
			            <ul class="sub-menu">
			                <li aria-haspopup="true"><a class="slide-item" href="/adjustment.php"> Adjustment</a>
			                {if $sessioninfo.privilege.ADJ_APPROVAL}
			                    <li aria-haspopup="true"><a class="slide-item" href="/adjustment_approval.php">Adjustment Approval</a></li>
			                {/if}
			                <li aria-haspopup="true"><a class="slide-item" href="/adjustment.summary.php"> Adjustment Summary</a></li>
			            </ul>
		        	</li>
	            {/if}	
	    
	            {if $sessioninfo.privilege.SHIFT_RECORD_VIEW or $sessioninfo.privilege.SHIFT_RECORD_EDIT && file_exists("`$smarty.server.DOCUMENT_ROOT`/shift_record.php")}
	            	<li aria-haspopup="true"><a class="slide-item" href="/shift_record.php">Shift Record</a></li>
	            {/if}
	            
	            {if $sessioninfo.privilege.PAYMENT_VOUCHER}
		            {if BRANCH_CODE ne 'HQ'}
		            	<li aria-haspopup="true"><a class="slide-item" href="/payment_voucher.php">Payment Voucher</a></li>
		            {else}
		            	<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Payment Voucher</a>
				            <ul class="sub-menu">
				                <li aria-haspopup="true"><a class="slide-item" href="/payment_voucher.php">Payment Voucher</a></li>
				                <li aria-haspopup="true"><a class="slide-item" href="/payment_voucher.log_sheet.php">Cheque Issue Log Sheet</a></li>
				            </ul>
				        </li>
		            {/if}	    
	            {/if}
	            
	            {if $sessioninfo.privilege.MST_SKU_APPLY or $sessioninfo.privilege.MST_SKU_APPROVAL or $sessioninfo.privilege.SKU_REPORT}
		            <li aria-haspopup="true" class="sub-menu-sub"><a href="#">SKU</a>
			            <ul class="sub-menu">
			            {if $sessioninfo.privilege.MST_SKU_APPLY && $sessioninfo.branch_type ne "franchise"}
			                <li aria-haspopup="true"><a class="slide-item" href="masterfile_sku_application.php">SKU Application</a></li>
			                <li aria-haspopup="true"><a class="slide-item" href="masterfile_sku_application.php?a=revise_list">SKU Application Revise List</a></li>
			                
			                {if !$config.menu_hide_bom_application}<li aria-haspopup="true"><a class="slide-item" href="masterfile_sku_application_bom.php">Create BOM SKU</a></li>{/if}
			            {/if}
			            
			            {if $sessioninfo.privilege.MST_SKU_APPLY or $sessioninfo.privilege.MST_SKU_APPROVAL}<li aria-haspopup="true"><a class="slide-item" href="masterfile_sku_application.php?a=list">SKU Application Status</a></li>{/if}
			            {if $sessioninfo.privilege.SKU_REPORT}
			            <!--li><a href="sku.summary.php">SKU Summary (Testing)</a></li-->
			            <!--li><a href="sku.history.php">SKU History (Testing)</a></li-->
			            {/if}
			            {if $sessioninfo.privilege.MST_SKU_UPDATE_FUTURE_PRICE && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_items.future_price.php")}
			                <li aria-haspopup="true"><a class="slide-item" href="masterfile_sku_items.future_price.php">Batch Selling Price Change</a></li>
			            {/if}
			            </ul>
			        </li>
	            {/if}
				
				{* Old *}
				{* if $config.allow_sales_order}
	                <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Sales Order</a>
	                    <ul class="sub-menu">
	                        <li aria-haspopup="true"><a class="slide-item" href="sales_order.php">Create / Edit Order</a></li>
	                        {if $sessioninfo.privilege.SO_APPROVAL}
	                            <li aria-haspopup="true"><a class="slide-item" href="sales_order_approval.php">Sales Order Approval</a></li>
	                        {/if}
	                        <li aria-haspopup="true"><a class="slide-item" href="report.spbt.php">Sales Order Report</a></li>
	                        <li aria-haspopup="true"><a class="slide-item" href="report.spbt_summary.php">Sales Order Summary Report</a></li>
	                        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/sales_order.monitor_report.php")}
	                            <li aria-haspopup="true"><a class="slide-item" href="sales_order.monitor_report.php">Sales Order Monitor Report</a></li>
	                        {/if}
	                    </ul>
	                </li>
	            {/if *}

				{* New *}
	            {if $config.allow_sales_order && ($sessioninfo.privilege.SO_EDIT || $sessioninfo.privilege.SO_APPROVAL || $sessioninfo.privilege.SO_REPORT)}
	                <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Sales Order</a>
	                    <ul class="sub-menu">
	                        {if $sessioninfo.privilege.SO_EDIT}
								<li aria-haspopup="true"><a class="slide-item" href="sales_order.php">Create / Edit Order</a></li>
	                        {/if}
	                        {if $sessioninfo.privilege.SO_APPROVAL}
	                            <li aria-haspopup="true"><a class="slide-item" href="sales_order_approval.php">Sales Order Approval</a></li>
	                        {/if}
	                        {if $sessioninfo.privilege.SO_REPORT}
								<li aria-haspopup="true"><a class="slide-item" href="report.spbt.php">Sales Order Report</a></li>
								<li aria-haspopup="true"><a class="slide-item" href="report.spbt_summary.php">Sales Order Summary Report</a></li>
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/sales_order.monitor_report.php")}
									<li aria-haspopup="true"><a class="slide-item" href="sales_order.monitor_report.php">Sales Order Monitor Report</a></li>
								{/if}
							{/if}
	                    </ul>
	                </li>
	            {/if}
	            <!-- DO -->
	            {if $sessioninfo.privilege.DO}
		            <li aria-haspopup="true" class="sub-menu-sub"><a href="#">DO (Delivery Order)</a>
			            <ul class="sub-menu">
			                {*<li aria-haspopup="true"><a class="slide-item" href="do.php">Delivery Order</a></li>
			                <li aria-haspopup="true"><a class="slide-item" href="do.summary.php?p=do">DO Summary</a></li>
			                <li aria-haspopup="true"><a class="slide-item" href="do.summary.php?p=invoice">Invoice Summary</a></li>*}
			                {if $sessioninfo.branch_type ne "franchise"}
			                    <li aria-haspopup="true"><a class="slide-item" href="do.php">Transfer DO</a></li>
			                {/if}
			                {if $config.do_allow_cash_sales}
			                    <li aria-haspopup="true"><a class="slide-item" href="do.php?page=open">Cash Sales DO</a></li>
			                {/if}
			                {if $config.do_allow_credit_sales}
			                    <li aria-haspopup="true"><a class="slide-item" href="do.php?page=credit_sales">Credit Sales DO</a></li>
			                {/if}
							{if $sessioninfo.privilege.DO_PREPARATION}
								<li aria-haspopup="true" class="sub-menu-sub"><a href="#">DO Preparation</a>
									<ul class="sub-menu">
										{if file_exists("`$smarty.server.DOCUMENT_ROOT`/do.simple.php")}
											<li aria-haspopup="true"><a class="slide-item" href="do.simple.php?do_type=transfer">Transfer DO</a></li>
											<li aria-haspopup="true"><a class="slide-item" href="do.simple.php?do_type=open">Cash Sales DO</a></li>
											<li aria-haspopup="true"><a class="slide-item" href="do.simple.php?do_type=credit_sales">Credit Sales DO</a></li>
										{/if}
									</ul>
								</li>
							{/if}
			                {if $sessioninfo.privilege.DO_APPROVAL}
			                    <li aria-haspopup="true"><a class="slide-item" href="do_approval.php">DO Approval</a></li>
			                {/if}
			                <li aria-haspopup="true"><a class="slide-item" href="do.summary.php">DO Summary</a></li>
			                <li aria-haspopup="true"><a class="slide-item" href="report.do_summary.php">DO Summary By Day / Month</a></li>
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.do_summary_by_items.php")}
							<li aria-haspopup="true"><a class="slide-item" href="report.do_summary_by_items.php">DO Summary By Items</a></li>
							{/if}
			                <li aria-haspopup="true"><a class="slide-item" href="do.report.php">Transfer Report</a></li>
			                {if $sessioninfo.privilege.DO_REQUEST}
			                    <li aria-haspopup="true"><a class="slide-item" href="do_request.php">DO Request</a></li>
			                    {if file_exists("`$smarty.server.DOCUMENT_ROOT`/do_request.rejected_report.php")}
			                        <li aria-haspopup="true"><a class="slide-item" href="do_request.rejected_report.php">DO Request Rejected Report</a></li>
			                    {/if}
			                {/if}
			                {if $sessioninfo.privilege.DO_REQUEST_PROCESS}
			                    <li aria-haspopup="true"><a class="slide-item" href="do_request.process.php">Process DO Request</a></li>
			                {/if}
			                {*
			                {if $config.enable_sn_bn && file_exists('masterfile_sku_items.serial_no.import_do_items.php')}
			                <li aria-haspopup="true"><a class="slide-item" href="masterfile_sku_items.serial_no.import_do_items.php">Serial No - IBT Validation</a></li>
			                {/if}
			                *}
							
							{if $config.enable_one_color_matrix_ibt and BRANCH_CODE eq 'HQ' and file_exists('do.matrix_ibt_process.php')}
								<li aria-haspopup="true"><a class="slide-item" href="do.matrix_ibt_process.php">Matrix IBT Process</a></li>
							{/if}
			            </ul>
			        </li>
	            {/if}
				
	    
	            <!-- PO -->
	            {if $sessioninfo.privilege.PO or $sessioninfo.privilege.PO_REQUEST or $sessioninfo.privilege.PO_FROM_REQUEST or $sessioninfo.privilege.PO_REPORT or $sessioninfo.privilege.PO_REQUEST_APPROVAL}
		            <li aria-haspopup="true" class="sub-menu-sub"><a href="#">PO (Purchase Order)</a>
		                <ul class="sub-menu">
		                    {if $sessioninfo.privilege.PO or $sessioninfo.privilege.PO_VIEW_ONLY}
		                        <li><!--a href="purchase_order.php">Purchase Order</a-->
		                        <a href="po.php">Purchase Order</a></li>
		                    {/if}
		                    {if $sessioninfo.privilege.PO_APPROVAL}
		                        <li aria-haspopup="true"><a class="slide-item" href="po_approval.php">PO Approval</a></li>
		                    {/if}
		                    {if $sessioninfo.privilege.PO_FROM_REQUEST}
		                        <li aria-haspopup="true"><a class="slide-item" href="po_request.process.php">Create PO from Request</a></li>
		                    {/if}
		                    {if $sessioninfo.privilege.PO_REQUEST}
		                        <li aria-haspopup="true"><a class="slide-item" href="po_request.request.php">PO Request</a></li>
		                    {/if}
		                    {if $sessioninfo.privilege.PO_REQUEST_APPROVAL}
		                        <li aria-haspopup="true"><a class="slide-item" href="po_request.approval.php">PO Request Approval</a></li>
		                    {/if}
		                    {if $sessioninfo.privilege.PO_TICKET && $config.po_allow_vendor_request}
		                        <li aria-haspopup="true"><a class="slide-item" href="vendor_po_request.php">Vendor PO Access</a></li>
		                    {/if}
		                    {if $sessioninfo.privilege.PO_REPORT}<li aria-haspopup="true"><a class="slide-item" href="purchase_order.summary.php">PO Summary</a></li>{/if}
		                    {if file_exists("`$smarty.server.DOCUMENT_ROOT`/po_qty_performance.php") and $sessioninfo.privilege.PO_REPORT}
		                        <li aria-haspopup="true"><a class="slide-item" href="po_qty_performance.php">PO Quantity Performance</a></li>
		                    {/if}
		                    {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.stock_reorder.php") and $sessioninfo.privilege.PO and $sessioninfo.privilege.PO_REPORT}
		                        <li aria-haspopup="true"><a class="slide-item" href="report.stock_reorder.php">Stock Reorder Report</a></li>
		                    {/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/sku_purchase_history.php") and $sessioninfo.privilege.PO and $sessioninfo.privilege.PO_REPORT}
		                        <li aria-haspopup="true"><a class="slide-item" href="sku_purchase_history.php">SKU Purchase History</a></li>
		                    {/if}
		                </ul>
		            </li>
	            {/if}
	            
	            {if $config.enable_po_agreement and $sessioninfo.privilege.PO_SETUP_AGREEMENT and $BRANCH_CODE eq 'HQ' and file_exists("`$smarty.server.DOCUMENT_ROOT`/po.po_agreement.setup.php")}
	                <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Purchase Agreement</a>
	                    <ul class="sub-menu">
	                        <li aria-haspopup="true"><a class="slide-item" href="po.po_agreement.setup.php">Add/Edit Purchase Agreement</a></li>
	                    </ul>
	                </li>
	            {/if}
	    
	            {if $sessioninfo.privilege.GRN_APPROVAL && !$config.use_grn_future}<li aria-haspopup="true"><a class="slide-item" href="goods_receiving_note_approval.account.php">GRN Account Verification</a>{/if}
	            {if $sessioninfo.privilege.GRA}
	                <li aria-haspopup="true" class="sub-menu-sub"><a href="#">GRA (Goods Return Advice)</a>
	                    <ul class="sub-menu">
	                        <li aria-haspopup="true"><a class="slide-item" href="goods_return_advice.php">GRA</a></li>
	                        {if $sessioninfo.privilege.GRA_APPROVAL}
	                            <li aria-haspopup="true"><a class="slide-item" href="/goods_return_advice.approval.php">GRA Approval</a></li>
	                        {/if}
	                    </ul>
	                </li>
	            {/if}
	    
	            {if $sessioninfo.privilege.GRA_REPORT or $sessioninfo.privilege.GRR_REPORT or $sessioninfo.privilege.GRN_REPORT}
	            <li aria-haspopup="true" class="sub-menu-sub"><a href="#">GRR / GRN / GRA Reports</a>
		            <ul class="sub-menu">
		            {if $sessioninfo.privilege.GRR_REPORT}
		            <li aria-haspopup="true"><a class="slide-item" href="goods_receiving_record.report.php">GRR Report</a></li>
		            <li aria-haspopup="true"><a class="slide-item" href="goods_receiving_record.status.php">GRR Status Report</a></li>
		            {/if}
		            {if $sessioninfo.privilege.GRN_REPORT}
		                <li aria-haspopup="true"><a class="slide-item" href="goods_receiving_note.summary.php">GRN Summary</a></li>
		                <li aria-haspopup="true"><a class="slide-item" href="goods_receiving_note.category_summary.php">GRN Summary by Category</a></li>
		                {if file_exists("`$smarty.server.DOCUMENT_ROOT`/goods_receiving_note.distribution_report.php")}
		                    <li aria-haspopup="true"><a class="slide-item" href="goods_receiving_note.distribution_report.php">GRN Distribution Report</a></li>
		                {/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sku_receiving_history.php")}
		                    <li aria-haspopup="true"><a class="slide-item" href="report.sku_receiving_history.php">SKU Receiving History</a></li>
						{/if}
		            {/if}
		            {if $sessioninfo.privilege.GRA_REPORT}
		                <li aria-haspopup="true"><a class="slide-item" href="goods_return_advice.listing_report.php">GRA Listing</a></li>
		                <li aria-haspopup="true"><a class="slide-item" href="goods_return_advice.summary_by_dept.php">GRA Summary by Department</a></li>
		                <li aria-haspopup="true"><a class="slide-item" href="goods_return_advice.summary_by_category.php">GRA Summary by Category</a></li>
		            {/if}
		            {if $config.gra_enable_disposal && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.goods_return_advice.disposal.php")}
		                <li aria-haspopup="true"><a class="slide-item" href="report.goods_return_advice.disposal.php">GRA Disposal Report</a></li>
		            {/if}
		            </ul>
		        </li>
	            {/if}
	            
				{if $sessioninfo.privilege.DN and !$config.consignment_modules and file_exists("`$smarty.server.DOCUMENT_ROOT`/dnote.php")}
	              <li aria-haspopup="true"><a class="slide-item" href="dnote.php">Debit Note</a></li>
	            {/if}
				
				{if $sessioninfo.privilege.CN and !$config.consignment_modules and file_exists("`$smarty.server.DOCUMENT_ROOT`/cnote.php")}
	              <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Credit Note</a>
	                <ul class="sub-menu">
	                    <li aria-haspopup="true"><a class="slide-item" href="cnote.php">Credit Note</a></li>
	                    {if $sessioninfo.privilege.CN_APPROVAL}
	                        <li aria-haspopup="true"><a class="slide-item" href="/cnote.approval.php">Credit Note Approval</a></li>
	                    {/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/cnote.summary.php")}
							<li aria-haspopup="true"><a class="slide-item" href="cnote.summary.php">CN Summary</a></li>
						{/if}
	                </ul>
	              </li>
	            {/if}

	            {* Vendor Portal Related *}
	            {if $config.enable_vendor_portal and ($sessioninfo.privilege.REPORTS_REPACKING and file_exists("`$smarty.server.DOCUMENT_ROOT`/report.repacking.php"))}
	                <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Vendor Portal</a>
	                    <ul class="sub-menu">
	                        {if $sessioninfo.privilege.REPORTS_REPACKING and file_exists("`$smarty.server.DOCUMENT_ROOT`/report.repacking.php")}
	                            <li aria-haspopup="true"><a class="slide-item" href="report.repacking.php">Repacking Report</a></li>
	                        {/if}
	                    </ul>
	                </li>
	            {/if}

	            {if $sessioninfo.privilege.ACCOUNT_EXPORT}
	              <li aria-haspopup="true"><a class="slide-item" href="acc_export.php">Account & GAF Export</a></li>
	              <li aria-haspopup="true"><a class="slide-item" href="acc_export.php?a=setting">Account & GAF Export Setting</a></li>
	            {/if}
				
				{* Accounting Export*}
				{if $sessioninfo.privilege.CUSTOM_ACC_AND_GST_SETTING or $sessioninfo.privilege.CUSTOM_ACC_EXPORT_SETUP or $sessioninfo.privilege.CUSTOM_ACC_EXPORT}
					<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Custom Accounting Export</a>
						<ul class="sub-menu">
							{if $sessioninfo.privilege.CUSTOM_ACC_AND_GST_SETTING and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.acc_and_gst_setting.php")}
								<li aria-haspopup="true"><a class="slide-item" href="custom.acc_and_gst_setting.php">Custom Account & GST Setting</a></li>
							{/if}
							
							{if $sessioninfo.privilege.CUSTOM_ACC_EXPORT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.setup_acc_export.php")}
								<li aria-haspopup="true"><a class="slide-item" href="custom.setup_acc_export.php">Setup Custom Accounting Export</a></li>
							{/if}
							
							{if $sessioninfo.privilege.CUSTOM_ACC_EXPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.acc_export.php")}
								<li aria-haspopup="true"><a class="slide-item" href="custom.acc_export.php">Custom Accounting Export</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{* ARMS Accounting Integration *}
				{if $config.arms_accounting_api_setting and ($sessioninfo.privilege.ARMS_ACCOUNTING_SETTING or $sessioninfo.privilege.ARMS_ACCOUNTING_STATUS)}
					<li aria-haspopup="true" class="sub-menu-sub"><a href="#">ARMS Accounting Integration &nbsp;</a>
						<ul class="sub-menu">
							{if $sessioninfo.privilege.ARMS_ACCOUNTING_SETTING and file_exists("`$smarty.server.DOCUMENT_ROOT`/arms_accounting.setting.php")}
								<li aria-haspopup="true"><a class="slide-item" href="arms_accounting.setting.php">Setting</a></li>
							{/if}
							{if $sessioninfo.privilege.ARMS_ACCOUNTING_STATUS and file_exists("`$smarty.server.DOCUMENT_ROOT`/arms_accounting.status.php")}
								<li aria-haspopup="true"><a class="slide-item" href="arms_accounting.status.php">Integration Status</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{* OS Trio Accounting Integration *}
				{if $config.os_trio_settings and ($sessioninfo.privilege.OSTRIO_ACCOUNTING_STATUS)}
					<li aria-haspopup="true" class="sub-menu-sub"><a href="#">OS Trio Accounting Integration &nbsp;</a>
						<ul class="sub-menu">
							{if $sessioninfo.privilege.OSTRIO_ACCOUNTING_STATUS and file_exists("`$smarty.server.DOCUMENT_ROOT`/ostrio_accounting.status.php")}
								<li aria-haspopup="true"><a class="slide-item" href="ostrio_accounting.status.php">Integration Status</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{* Speed99 Integration *}
				{if $config.speed99_settings and ($sessioninfo.privilege.SPEED99_INTEGRATION_STATUS)}
					<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Speed99 Integration &nbsp;</a>
						<ul class="sub-menu">
							{if $sessioninfo.privilege.SPEED99_INTEGRATION_STATUS and file_exists("`$smarty.server.DOCUMENT_ROOT`/speed99.integration_status.php")}
								<li aria-haspopup="true"><a class="slide-item" href="speed99.integration_status.php">Integration Status</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{* Komaiso Integration *}
				{if $config.komaiso_settings  and $sessioninfo.privilege.KOMAISO_INTEGRATION_STATUS}
					<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Komaiso Integration &nbsp;</a>
						<ul class="sub-menu">
							{if $sessioninfo.privilege.KOMAISO_INTEGRATION_STATUS and file_exists("`$smarty.server.DOCUMENT_ROOT`/komaiso.integration_status.php")}
								<li aria-haspopup="true"><a class="slide-item" href="komaiso.integration_status.php">Integration Status</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php") and ($sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP or $sessioninfo.privilege.ATTENDANCE_SHIFT_ASSIGN)}
					<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Time Attendance</a>
						<ul class="sub-menu">

							{if $sessioninfo.privilege.ATTENDANCE_TIME_OVERVIEW and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.overview.php")}
								<li aria-haspopup="true"><a class="slide-item" href="attendance.overview.php">Time Attendance Overview</a></li>
							{/if}
							{if $sessioninfo.privilege.ATTENDANCE_TIME_SETTING and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.settings.php")}
								<li aria-haspopup="true"><a class="slide-item" href="attendance.settings.php">Settings</a></li>
							{/if}
							
							{if ($sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php")) or ($sessioninfo.privilege.ATTENDANCE_SHIFT_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_assignment.php"))}
								<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Shift</a>
									<ul class="sub-menu">
										{if $sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php")}
											<li aria-haspopup="true"><a class="slide-item" href="attendance.shift_table_setup.php">Shift Table Setup</a></li>
										{/if}
										{if $sessioninfo.privilege.ATTENDANCE_SHIFT_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_assignment.php")}
											<li aria-haspopup="true"><a class="slide-item" href="attendance.shift_assignment.php">Shift Assignments</a></li>
										{/if}
									</ul>
								</li>
							{/if}
							
							{if ($sessioninfo.privilege.ATTENDANCE_PH_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.ph_setup.php")) or ($sessioninfo.privilege.ATTENDANCE_PH_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.ph_assignment.php"))}
								<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Holiday</a>
									<ul class="sub-menu">
										{if $sessioninfo.privilege.ATTENDANCE_PH_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.ph_setup.php")}
											<li aria-haspopup="true"><a class="slide-item" href="attendance.ph_setup.php">Holiday Setup</a></li>
										{/if}
										{if $sessioninfo.privilege.ATTENDANCE_PH_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.ph_assignment.php")}
											<li aria-haspopup="true"><a class="slide-item" href="attendance.ph_assignment.php">Holiday Assignments</a></li>
										{/if}
									</ul>
								</li>
							{/if}
							
							{if ($sessioninfo.privilege.ATTENDANCE_LEAVE_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.leave_setup.php")) or ($sessioninfo.privilege.ATTENDANCE_LEAVE_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.leave_assignment.php"))}
								<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Leave</a>
									<ul class="sub-menu">
										{if $sessioninfo.privilege.ATTENDANCE_LEAVE_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.leave_setup.php")}
											<li aria-haspopup="true"><a class="slide-item" href="attendance.leave_setup.php">Leave Table Setup</a></li>
										{/if}
										{if $sessioninfo.privilege.ATTENDANCE_LEAVE_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.leave_assignment.php")}
											<li aria-haspopup="true"><a class="slide-item" href="attendance.leave_assignment.php">Leave Assignments</a></li>
										{/if}
									</ul>
								</li>
							{/if}
							
							
							{if $sessioninfo.privilege.ATTENDANCE_USER_MODIFY and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.user_records.php")}
								<li aria-haspopup="true"><a class="slide-item" href="attendance.user_records.php">User Attendance Records</a></li>
							{/if}
							
							{if $sessioninfo.privilege.ATTENDANCE_CLOCK_REPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.report.daily.php")}
								<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Reports</a>
									<ul class="sub-menu">
										{if $sessioninfo.privilege.ATTENDANCE_CLOCK_REPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.report.daily.php")}
											<li aria-haspopup="true"><a class="slide-item" href="attendance.report.daily.php">Daily Attendance Report</a></li>
										{/if}
										{if $sessioninfo.privilege.ATTENDANCE_CLOCK_REPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.report.monthly_ledger.php")}
											<li aria-haspopup="true"><a class="slide-item" href="attendance.report.monthly_ledger.php">Monthly Attendance Ledger</a></li>
										{/if}
									</ul>
								</li>
							{/if}						
						</ul>
					</li>
				{/if}
	        </ul>
	    </li>
        {/if}
        <!-- Office Ends -->
        <!-- Store Starts -->
        {if $sessioninfo.privilege.GRR or $sessioninfo.privilege.GRN or $sessioninfo.privilege.GRA_CHECKOUT or $sessioninfo.privilege.DO_CHECKOUT or $sessioninfo.privilege.STOCK_TAKE}
        <li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-store"></i> Store<i class="fe fe-chevron-down horizontal-icon"></i></a>
	        <ul class="sub-menu">
	            {if $sessioninfo.privilege.GRR}<li aria-haspopup="true"><a class="slide-item" href="goods_receiving_record.php">GRR (Goods Receiving Record)</a></li>{/if}
	            {if $sessioninfo.privilege.GRN}
	                <li aria-haspopup="true" class="sub-menu-sub"><a href="#">GRN (Goods Receiving Note)</a>
	                    <ul class="sub-menu">
	                        <li aria-haspopup="true"><a class="slide-item" href="/goods_receiving_note.php">GRN</a></li>
	                        {if $sessioninfo.privilege.GRN_APPROVAL}
	                        <li aria-haspopup="true"><a class="slide-item" href="/goods_receiving_note_approval.php">GRN Approval</a></li>
	                        {/if}
	                    </ul>
	                </li>
	            {/if}
	            {if $sessioninfo.privilege.GRA_CHECKOUT}<li aria-haspopup="true"><a class="slide-item" href="goods_return_advice.checkout.php">GRA Checkout</a>{/if}
	            {if $sessioninfo.privilege.DO_CHECKOUT}<li aria-haspopup="true"><a class="slide-item" href="do_checkout.php">Delivery Order Checkout</a>{/if}
	            {if $sessioninfo.privilege.STOCK_TAKE}
	                <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Stock Take</a>
	                    <ul class="sub-menu">
	                        <li aria-haspopup="true"><a class="slide-item" href="admin.stock_take.php">Stock Take</a></li>
	                        <li aria-haspopup="true"><a class="slide-item" href="admin.stock_take.php?a=import_page">Import / Reset Stock Take</a></li>
	                        <li aria-haspopup="true"><a class="slide-item" href="admin.stock_take.php?a=change_batch">Change Batch</a></li>
	                        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.stock_take_zerolize_negative_stocks.php") && $config.consignment_modules}
	                            <li aria-haspopup="true"><a class="slide-item" href="admin.stock_take_zerolize_negative_stocks.php">Zerolize Negative Stocks</a></li>
	                        {/if}
	                    </ul>
	                </li>
	            {/if}
				{if ($sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT or $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT or $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_SCHEDULE_LIST or $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_APPROVAL) and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.cycle_count.assignment.php")}
					<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Cycle Count</a>
						<ul class="sub-menu">
							{if ($sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT or $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT) and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.cycle_count.assignment.php")}
								<li aria-haspopup="true"><a class="slide-item" href="admin.cycle_count.assignment.php">Cycle Count Assignment</a></li>
							{/if}
							{if ($sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_APPROVAL) and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.cycle_count.approval.php")}
								<li aria-haspopup="true"><a class="slide-item" href="admin.cycle_count.approval.php">Cycle Count Approval</a></li>
							{/if}
							{if ($sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_SCHEDULE_LIST) and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.cycle_count.schedule_list.php")}
								<li aria-haspopup="true"><a class="slide-item" href="admin.cycle_count.schedule_list.php">Monthly Schedule List</a></li>
							{/if}
						</ul>
					</li>
				{/if}
	        </ul>
    	</li>
        {/if}
    {else}
        <li aria-haspopup="true"><a href="#">Office</a>
	        <ul class="sub-menu">
	            {if $sessioninfo.privilege.MST_SKU_APPLY && $sessioninfo.branch_type ne "franchise"}
	                <li aria-haspopup="true"><a class="slide-item" href="masterfile_sku_application.php">SKU Application</a></li>
	                   {if !$config.menu_hide_bom_application}<li aria-haspopup="true"><a class="slide-item" href="masterfile_sku_application_bom.php">Create BOM SKU</a></li>{/if}
	            {/if}
	            
	            {if $sessioninfo.privilege.MST_SKU_APPLY or $sessioninfo.privilege.MST_SKU_APPROVAL}<li aria-haspopup="true"><a class="slide-item" href="masterfile_sku_application.php?a=list">SKU Application Status</a>{/if}
	            {if $sessioninfo.privilege.SKU_REPORT}
	            <!--li><a href="sku.summary.php">SKU Summary (Testing)</a></li-->
	            <!--li><a href="sku.history.php">SKU History (Testing)</a></li-->
	            {/if}
	            {if $sessioninfo.privilege.MST_SKU_UPDATE_FUTURE_PRICE && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_items.future_price.php")}
	                <li aria-haspopup="true"><a class="slide-item" href="masterfile_sku_items.future_price.php">Batch Selling Price Change</a></li>
	            {/if}

	            {if $sessioninfo.privilege.ACCOUNT_EXPORT}
	              <li aria-haspopup="true"><a class="slide-item" href="acc_export.php">Account Export</a>
	              <li aria-haspopup="true"><a class="slide-item" href="acc_export.php?a=setting">Account Export Setting</a>
	            {/if}
				
				{* Accounting Export*}
				{if $sessioninfo.privilege.CUSTOM_ACC_AND_GST_SETTING or $sessioninfo.privilege.CUSTOM_ACC_EXPORT_SETUP or $sessioninfo.privilege.CUSTOM_ACC_EXPORT}
					<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Custom Accounting Export</a>
						<ul class="sub-menu">
							{if $sessioninfo.privilege.CUSTOM_ACC_AND_GST_SETTING and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.acc_and_gst_setting.php")}
								<li aria-haspopup="true"><a class="slide-item" href="custom.acc_and_gst_setting.php">Custom Account & GST Setting</a></li>
							{/if}
							
							{if $sessioninfo.privilege.CUSTOM_ACC_EXPORT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.setup_acc_export.php")}
								<li aria-haspopup="true"><a class="slide-item" href="custom.setup_acc_export.php">Setup Custom Accounting Export</a></li>
							{/if}
							
							{if $sessioninfo.privilege.CUSTOM_ACC_EXPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.acc_export.php")}
								<li aria-haspopup="true"><a class="slide-item" href="custom.acc_export.php">Custom Accounting Export</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php") and ($sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP)}
					<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Time Attendance</a>
						<ul class="sub-menu">
							{if $sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php")}
								<li aria-haspopup="true"><a class="slide-item" href="attendance.shift_table_setup.php">Shift Table Setup</a></li>
							{/if}			
						</ul>
					</li>
				{/if}
	        </ul>
    	</li>
    {/if}

{/if}
<!-- Session info if ends here -->
	</ul>
		</nav>
		<!--Nav-->
			</div>
				</div>
					</div>
					<!--Horizontal-main -->

<!-- Menu Ends Here And Page Content Container Starts From Here -->

<!-- main-content opened -->
<div class="main-content horizontal-content">

	<!-- container opened -->
	<div class="container-fluid">