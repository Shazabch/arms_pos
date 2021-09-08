
 
<!-- Page -->
<div class="page">

<!-- main-sidebar -->
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
<aside class="app-sidebar sidebar-scroll">
	<div class="main-sidebar-header active">
		<a class="desktop-logo logo-light active" href="index.html"><img src="../../assets/img/brand/logo.png" class="main-logo" alt="logo"></a>
		<a class="desktop-logo logo-dark active" href="index.html"><img src="../../assets/img/brand/logo-white.png" class="main-logo dark-theme" alt="logo"></a>
		<a class="logo-icon mobile-logo icon-light active" href="index.html"><img src="../../assets/img/brand/favicon.png" class="logo-icon" alt="logo"></a>
		<a class="logo-icon mobile-logo icon-dark active" href="index.html"><img src="../../assets/img/brand/favicon-white.png" class="logo-icon dark-theme" alt="logo"></a>
	</div>
	
	<div class="main-sidemenu">
		<div class="app-sidebar__user clearfix">
			<div class="dropdown user-pro-body">
				<div class="user-info">
					<h4 class="font-weight-semibold mb-0">{if !$sa_session}{$BRANCH_CODE}{/if}</h4>
					<span class="mb-0 text-muted">
						{if $sessioninfo}
							Logged in as 
							{if $smarty.session.admin_session}
								{$smarty.session.admin_session.u}</b> (now running as <b>{$sessioninfo.u}</b> |)
							{else}
								{$sessioninfo.u}
							{/if}
						{elseif $vp_session}
							Logged in as {$vp_session.description}
						{elseif $dp_session}
							Logged in as {$dp_session.description}
						{elseif $sa_session}
							Logged in as {$sa_session.name}
						{/if}
					</span>
				</div>
			</div>

			
		</div>
<ul class="side-menu" id="menu-list">
<li class="slide">
	<div class="search-bar">
		<div class="input-group rounded">
			<input type="search" id="search-input" class="form-control rounded" placeholder="Search Menu" aria-label="Search"
			  aria-describedby="search-addon" />
			<span class="input-group-text border-0" id="search-addon">
			  <i class="fas fa-search"></i>
			</span>
		  </div>
	</div>
	<div id="search-content" class="d-flex flex-column bg-gray-100"></div>
</li>

{if $sessioninfo}
	<li class="slide">
		<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-home side-menu__icon"></i><span class="side-menu__label">Home</span><i class="angle fe fe-chevron-down"></i></a>
		<ul class="slide-menu">
			<li><a class="sub-slide-item" href="home.php" >Dashboard</a></li>
			<li><a class="sub-slide-item" href="javascript:void(goto_branch(0))" >Go To Branch</a></li>
			<li><a class="sub-slide-item" href="/login.php?logout=1" onclick="return confirm('{$LANG.CONFIRM_LOGOUT}')">Logout</a></li>
		</ul>
	</li>
	<!-- Administrator -->
	{if $sessioninfo.privilege.USERS_ADD or $sessioninfo.privilege.USERS_MNG or $sessioninfo.privilege.USERS_ACTIVATE or $sessioninfo.privilege.MST_APPROVAL or $sessioninfo.privilege.POS_IMPORT or $sessioninfo.privilege.SKU_EXPORT or $sessioninfo.level>=9999}
	<li class="slide">
		<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-account side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Administrator</span><i class="angle fe fe-chevron-down"></i></a>
		<ul class="slide-menu">
			{if $sessioninfo.privilege.USERS_MNG or $sessioninfo.privilege.USERS_ACTIVATE}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Users</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						{if $sessioninfo.privilege.USERS_ADD}<li><a class="sub-slide-item" href="users.php?t=create" >Create Profile</a></li>{/if}
						{if $sessioninfo.privilege.USERS_ACTIVATE}<li><a class="sub-slide-item" href="users.php?t=update" >Update Profile</a></li>{/if}
						{if $sessioninfo.level==500 || $sessioninfo.level>=9999}<li><a class="sub-slide-item" href="admin.inactive_user.php" >No-Activity User Report</a></li>{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/users.application.php") and $sessioninfo.privilege.USERS_EFORM }
						<li class="sub-slide-sub">
							<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">User Application E-Form</span><i class="sub-angle fe fe-chevron-down"></i></a>
							<ul class="sub-slide-menu-sub">
								{if $config.single_server_mode or (!$config.single_server_mode and $sessioninfo.branch_id eq 1)}<li><a class="sub-slide-item"  href="users.application.php?a=generate_code" >Generate QR Code</a></li>{/if}
								<li><a class="sub-slide-item" href="users.application.php?a=application_list" >Application List</a></li>
							</ul>
						</li>
						{/if}
					</ul>
				</li>
			{/if}
			{if $sessioninfo.privilege.MST_APPROVAL}
			<li>
				<a href="approval_flow.php" class="slide-item">Approval Flows</a></li>{/if}
			{if $sessioninfo.level>=9999 and $BRANCH_CODE eq 'HQ'}
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Selling Price</span><i class="sub-angle fe fe-chevron-down"></i></a>
				<ul class="sub-slide-menu">
					<li><a class="sub-slide-item" href="admin.copy_selling.php" >Copy Selling Price</a></li>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_selling.php")}
					<li><a class="sub-slide-item" href="admin.import_selling.php" >Import Selling Price</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.update_price_type.php")}
					<li><a class="sub-slide-item" href="admin.update_price_type.php" >Update Price Type</a></li>
					{/if}
				</ul>
			</li>			
			{/if}
			{if $BRANCH_CODE eq 'HQ' and $sessioninfo.level>=9999 and ($sessioninfo.privilege.ADMIN_UPDATE_SKU_MASTER_COST and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.update_sku_master_cost.php"))}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Cost Price</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						<li><a class="sub-slide-item" href="admin.update_sku_master_cost.php" >Update SKU Master Cost</a></li>
					</ul>
				</li>
			{/if}
			{if $sessioninfo.level>=9999 and $BRANCH_CODE eq 'HQ'}
				<li><a href="admin.sku_block.php" class="slide-item">Block/Ublock in SKU in PO (CSV)</a></li>
			{/if}

			{if $sessioninfo.privilege.SKU_EXPORT ||  $sessioninfo.level>=9999 || $sessioninfo.privilege.POS_IMPORT || $sessioninfo.privilege.ALLOW_IMPORT_SKU || $sessioninfo.privilege.ALLOW_IMPORT_VENDOR  || $sessioninfo.privilege.ALLOW_IMPORT_BRAND || $sessioninfo.privilege.ALLOW_IMPORT_DEBTOR || $sessioninfo.privilege.ALLOW_IMPORT_UOM || $sessioninfo.privilege.ALLOW_IMPORT_DEACTIVATE_SKU}
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Import / Export</span><i class="sub-angle fe fe-chevron-down"></i></a>
				<ul class="sub-slide-menu">
				{if $sessioninfo.privilege.SKU_EXPORT}
					<li><a class="sub-slide-item"  href="admin.sku_export.php">Export SKU Items</a></li>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.weightcode_export.php") and !$config.consignment_modules}
						<li><a class="sub-slide-item"  href="admin.weightcode_export.php">Export Weighing Scale Items</a></li>
					{/if}
				{/if}
				{if $sessioninfo.level>=9999 || $sessioninfo.privilege.POS_IMPORT}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.export_points.php")}<li><a class="sub-slide-item"  href="admin.export_points.php">Export Member Points</a></li>{/if}
				{/if}
				{if $sessioninfo.level>=9999 || $sessioninfo.privilege.POS_IMPORT}
					{*<li><a class="sub-slide-item"  href="admin.pos_transaction_import.php">Import POS Transaction</a></li>*}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_pos_sales.php")}
					    {*<li><a class="sub-slide-item"  href="admin.import_pos_sales.php">Import POS Sales</a></li>*}
					{/if}
		  			<li><a class="sub-slide-item"  href="admin.stockchk_import.php">Import Stock Take</a></li>
				{/if}
				{if $config.sku_application_require_multics && ($sess.level==500 || $sessioninfo.level>=9999)}
				<li><a class="sub-slide-item"  href="admin.update_dat.php">Update Multics DAT files</a></li>
				{/if}
	            {if $sessioninfo.level>=9999 || $sessioninfo.privilege.POS_IMPORT}
	                {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_member_points.php")}
	                    <li><a class="sub-slide-item"  href="admin.import_member_points.php">Import Member Points</a></li>
	                {/if}
	                {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_members.php")}
	                    <li><a class="sub-slide-item"  href="admin.import_members.php">Import Members</a></li>
	                {/if}
	                {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.preactivate_member_cards.php")}
	                    <li><a class="sub-slide-item"  href="admin.preactivate_member_cards.php">Pre-activate Member Cards</a></li>
	                {/if}
	            {/if}
				
				{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_SKU)}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_sku.php")}
	                    <li><a class="sub-slide-item"  href="admin.import_sku.php">Import SKU</a></li>
	                {/if}
				{/if}
				
				{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_VENDOR)}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_vendor.php")}
	                    <li><a class="sub-slide-item"  href="admin.import_vendor.php">Import Vendor</a></li>
	                {/if}
				{/if}
				
				{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_BRAND)}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_brand.php")}
	                    <li><a class="sub-slide-item"  href="admin.import_brand.php">Import Brand</a></li>
	                {/if}
				{/if}
	            
	            {if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_DEBTOR) && file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_debtor.php")}
					<li><a class="sub-slide-item"  href="admin.import_debtor.php">Import Debtor</a></li>
				{/if}
				{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_UOM) && file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_uom.php")}
	                 <li><a class="sub-slide-item"  href="admin.import_uom.php">Import UOM</a></li>
	            {/if}
				{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_DEACTIVATE_SKU) && file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.deactivate_sku.php")}
	                 <li><a class="sub-slide-item"  href="admin.deactivate_sku.php">Deactivate SKU by CSV</a></li>
	            {/if}
				</ul>
			</li>
			{/if}
			{if $sessioninfo.level>=9999 and $BRANCH_CODE eq 'HQ' and $config.show_tracker}
			    <li><a class="slide-item" href="admin.arms_tracker.php">ARMS Request Tracker</a></li>
			{/if}
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.monthly_closing.php") and $config.monthly_closing and $sessioninfo.privilege.ADMIN_MONTHLY_CLOSING}
				<li  aria-haspopup="true" class="sub-menu-sub"><a>Monthly Closing</a>
					<ul class="sub-slide-menu">
						<li><a class="sub-slide-item"  href="admin.monthly_closing.php">Monthly Closing</a></li>
						<li><a class="sub-slide-item"  href="admin.monthly_closing.php?a=show_closed_month">Monthly Closing History</a></li>
					</ul>
				</li>
			{/if}
			{if $sessioninfo.level>=9999}
				<li><a class="slide-item" href="admin.update_log.php">System Update log</a></li>
				<li><a class="slide-item" href="sales_target.php">Sales Target</a></li>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Settings</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.settings.php")}
						<li><a class="sub-slide-item"  href="admin.settings.php?file=color.txt">Edit Colour</a></li>
						<li><a class="sub-slide-item"  href="admin.settings.php?file=size.txt">Edit Size</a></li>
						{/if}
						{* if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.upload_config.php")}
						<li><a class="sub-slide-item"  href="admin.upload_config.php">Upload Config CSV</a></li>
						{/if *}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.upload_logo.php")}
						<li><a class="sub-slide-item"  href="admin.upload_logo.php">Edit Logo Settings</a></li>
						{/if}
					</ul>
				</li>
				{*
				{if $BRANCH_CODE eq 'HQ' and $sessioninfo.privilege.SERVER_MAINTENANCE}
				    <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Server Maintenance</span><i class="sub-angle fe fe-chevron-down"></i></a>
				        <ul class="sub-slide-menu">
				            {if file_exists("admin.server_maintenance.archive_database.php")}
				            <li><a class="sub-slide-item"  href="admin.server_maintenance.archive_database.php">Archive Database</a></li>
				            <li><a class="sub-slide-item"  href="admin.server_maintenance.archive_database.php?a=restore">Restore Database</a></li>
				            {/if}
				        </ul>
				    </li>
				{/if}
				*}
				{if $BRANCH_CODE eq 'HQ' and $sessioninfo.id eq 1}
				    <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Server Management</span><i class="sub-angle fe fe-chevron-down"></i></a>
				        <ul class="sub-slide-menu">
				            {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.config_manager.php")}
				                <li><a class="sub-slide-item"  href="admin.config_manager.php">Config Manager</a></li>
				            {/if}
				            {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.privilege_manager.php")}
				                <li><a class="sub-slide-item"  href="admin.privilege_manager.php">Privilege Manager</a></li>
				            {/if}
				            {*{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.reset_db.php")}
				                <li><a class="sub-slide-item"  href="admin.reset_db.php">Reset Data</a></li>
				            {/if}*}
				        </ul>
					</li>
				{/if}
				{if $config.enable_gst && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_gst_settings.php") && $sessioninfo.level>=9999}
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide"  href="masterfile_gst_settings.php">GST Settings</a></li>
				{/if}
			{/if}
			{if $config.enable_tax and $sessioninfo.level>=9999}
				<li class="slide">
					<ul class="sub-slide">
						<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Tax</span><i class="sub-angle fe fe-chevron-down"></i></a>
							<ul class="sub-slide-menu">
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.tax_settings.php") }
								<li><a class="sub-slide-item"  href="admin.tax_settings.php">Tax Settings</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.tax_listing.php") }
								<li><a class="sub-slide-item"  href="admin.tax_listing.php">Tax Listing</a></li>
							{/if}
							</ul>
						</li>
					</ul>
				</li>
			{/if}
			{* Foreign Currency *}
			{if $config.foreign_currency and ($sessioninfo.privilege.ADMIN_FOREIGN_CURRENCY_RATE_UPDATE)}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Foreign Currency</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						{if $sessioninfo.privilege.ADMIN_FOREIGN_CURRENCY_RATE_UPDATE and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.foreign_currency.rate.php")}
							<li><a class="sub-slide-item"  href="admin.foreign_currency.rate.php">Currency Rate Table</a></li>
						{/if}
					</ul>
				</li>
			{/if}
			{*
			{if $BRANCH_CODE eq 'HQ' && $config.stock_copy && $sessioninfo.privilege.STOCK_COPY}
			    {if file_exists("admin.stock_copy.php")}
			    	<li><a class="slide-item" href="admin.stock_copy.php">Stock Copy</a></li>
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
        <li class="slide">
			<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-briefcase side-menu__icon"></i><span class="side-menu__label">Office</span><i class="angle fe fe-chevron-down"></i></a>
	        <ul class="slide-menu">
	    
	            {if $sessioninfo.privilege.ADJ}
		            <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label"> Adjustment</span><i class="sub-angle fe fe-chevron-down"></i></a>
			            <ul class="sub-slide-menu">
			                <li><a class="sub-slide-item" href="/adjustment.php"> Adjustment</a>
			                {if $sessioninfo.privilege.ADJ_APPROVAL}
			                    <li><a class="sub-slide-item"  href="/adjustment_approval.php">Adjustment Approval</a></li>
			                {/if}
			                <li><a class="sub-slide-item"  href="/adjustment.summary.php"> Adjustment Summary</a></li>
			            </ul>
		        	</li>
	            {/if}	
	    
	            {if $sessioninfo.privilege.SHIFT_RECORD_VIEW or $sessioninfo.privilege.SHIFT_RECORD_EDIT && file_exists("`$smarty.server.DOCUMENT_ROOT`/shift_record.php")}
	            	<li><a class="slide-item" href="/shift_record.php">Shift Record</a></li>
	            {/if}
	            
	            {if $sessioninfo.privilege.PAYMENT_VOUCHER}
		            {if BRANCH_CODE ne 'HQ'}
		            	<li><a class="slide-item" href="/payment_voucher.php">Payment Voucher</a></li>
		            {else}
		            	<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Payment Voucher</span><i class="sub-angle fe fe-chevron-down"></i></a>
				            <ul class="sub-slide-menu">
				                <li><a class="sub-slide-item"  href="/payment_voucher.php">Payment Voucher</a></li>
				                <li><a class="sub-slide-item"  href="/payment_voucher.log_sheet.php">Cheque Issue Log Sheet</a></li>
				            </ul>
				        </li>
		            {/if}	    
	            {/if}
	            
	            {if $sessioninfo.privilege.MST_SKU_APPLY or $sessioninfo.privilege.MST_SKU_APPROVAL or $sessioninfo.privilege.SKU_REPORT}
		            <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">SKU reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
			            <ul class="sub-slide-menu">
			            {if $sessioninfo.privilege.MST_SKU_APPLY && $sessioninfo.branch_type ne "franchise"}
			                <li><a class="sub-slide-item"  href="masterfile_sku_application.php">SKU Application</a></li>
			                <li><a class="sub-slide-item"  href="masterfile_sku_application.php?a=revise_list">SKU Application Revise List</a></li>
			                
			                {if !$config.menu_hide_bom_application}<li><a class="sub-slide-item"  href="masterfile_sku_application_bom.php">Create BOM SKU</a></li>{/if}
			            {/if}
			            
			            {if $sessioninfo.privilege.MST_SKU_APPLY or $sessioninfo.privilege.MST_SKU_APPROVAL}<li><a class="sub-slide-item"  href="masterfile_sku_application.php?a=list">SKU Application Status</a></li>{/if}
			            {if $sessioninfo.privilege.SKU_REPORT}
			            <!--li><a href="sku.summary.php">SKU Summary (Testing)</a></li-->
			            <!--li><a href="sku.history.php">SKU History (Testing)</a></li-->
			            {/if}
			            {if $sessioninfo.privilege.MST_SKU_UPDATE_FUTURE_PRICE && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_items.future_price.php")}
			                <li><a class="sub-slide-item"  href="masterfile_sku_items.future_price.php">Batch Selling Price Change</a></li>
			            {/if}
			            </ul>
			        </li>
	            {/if}
				
				{* Old *}
				{* if $config.allow_sales_order}
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Sales Order</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        <li><a class="sub-slide-item"  href="sales_order.php">Create / Edit Order</a></li>
	                        {if $sessioninfo.privilege.SO_APPROVAL}
	                            <li><a class="sub-slide-item"  href="sales_order_approval.php">Sales Order Approval</a></li>
	                        {/if}
	                        <li><a class="sub-slide-item"  href="report.spbt.php">Sales Order Report</a></li>
	                        <li><a class="sub-slide-item"  href="report.spbt_summary.php">Sales Order Summary Report</a></li>
	                        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/sales_order.monitor_report.php")}
	                            <li><a class="sub-slide-item"  href="sales_order.monitor_report.php">Sales Order Monitor Report</a></li>
	                        {/if}
	                    </ul>
	                </li>
	            {/if *}

				{* New *}
	            {if $config.allow_sales_order && ($sessioninfo.privilege.SO_EDIT || $sessioninfo.privilege.SO_APPROVAL || $sessioninfo.privilege.SO_REPORT)}
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Sales Order</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        {if $sessioninfo.privilege.SO_EDIT}
								<li><a class="sub-slide-item"  href="sales_order.php">Create / Edit Order</a></li>
	                        {/if}
	                        {if $sessioninfo.privilege.SO_APPROVAL}
	                            <li><a class="sub-slide-item"  href="sales_order_approval.php">Sales Order Approval</a></li>
	                        {/if}
	                        {if $sessioninfo.privilege.SO_REPORT}
								<li><a class="sub-slide-item"  href="report.spbt.php">Sales Order Report</a></li>
								<li><a class="sub-slide-item"  href="report.spbt_summary.php">Sales Order Summary Report</a></li>
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/sales_order.monitor_report.php")}
									<li><a class="sub-slide-item"  href="sales_order.monitor_report.php">Sales Order Monitor Report</a></li>
								{/if}
							{/if}
	                    </ul>
	                </li>
	            {/if}
	            <!-- DO -->
	            {if $sessioninfo.privilege.DO}
		            <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">DO (Delivery Order)</span><i class="sub-angle fe fe-chevron-down"></i></a>
			            <ul class="sub-slide-menu">
			                {*<li><a class="sub-slide-item"  href="do.php">Delivery Order</a></li>
			                <li><a class="sub-slide-item"  href="do.summary.php?p=do">DO Summary</a></li>
			                <li><a class="sub-slide-item"  href="do.summary.php?p=invoice">Invoice Summary</a></li>*}
			                {if $sessioninfo.branch_type ne "franchise"}
			                    <li><a class="sub-slide-item"  href="do.php">Transfer DO</a></li>
			                {/if}
			                {if $config.do_allow_cash_sales}
			                    <li><a class="sub-slide-item"  href="do.php?page=open">Cash Sales DO</a></li>
			                {/if}
			                {if $config.do_allow_credit_sales}
			                    <li><a class="sub-slide-item"  href="do.php?page=credit_sales">Credit Sales DO</a></li>
			                {/if}
							{if $sessioninfo.privilege.DO_PREPARATION}
								<li class="sub-slide-sub">
									<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">DO Preparation</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu-sub">
										{if file_exists("`$smarty.server.DOCUMENT_ROOT`/do.simple.php")}
											<li><a class="sub-slide-item"  href="do.simple.php?do_type=transfer">Transfer DO</a></li>
											<li><a class="sub-slide-item"  href="do.simple.php?do_type=open">Cash Sales DO</a></li>
											<li><a class="sub-slide-item"  href="do.simple.php?do_type=credit_sales">Credit Sales DO</a></li>
										{/if}
									</ul>
								</li>
							{/if}
			                {if $sessioninfo.privilege.DO_APPROVAL}
			                    <li><a class="sub-slide-item" href="do_approval.php">DO Approval</a></li>
			                {/if}
			                <li><a class="sub-slide-item"  href="do.summary.php">DO Summary</a></li>
			                <li><a class="sub-slide-item"  href="report.do_summary.php">DO Summary By Day / Month</a></li>
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.do_summary_by_items.php")}
							<li><a class="sub-slide-item"  href="report.do_summary_by_items.php">DO Summary By Items</a></li>
							{/if}
			                <li><a class="slide-item" href="do.report.php">Transfer Report</a></li>
			                {if $sessioninfo.privilege.DO_REQUEST}
			                    <li><a class="slide-item" href="do_request.php">DO Request</a></li>
			                    {if file_exists("`$smarty.server.DOCUMENT_ROOT`/do_request.rejected_report.php")}
			                        <li><a class="slide-item" href="do_request.rejected_report.php">DO Request Rejected Report</a></li>
			                    {/if}
			                {/if}
			                {if $sessioninfo.privilege.DO_REQUEST_PROCESS}
			                    <li><a class="slide-item" href="do_request.process.php">Process DO Request</a></li>
			                {/if}
			                {*
			                {if $config.enable_sn_bn && file_exists('masterfile_sku_items.serial_no.import_do_items.php')}
			                <li><a class="sub-slide-item" class="slide-item" href="masterfile_sku_items.serial_no.import_do_items.php">Serial No - IBT Validation</a></li>
			                {/if}
			                *}
							
							{if $config.enable_one_color_matrix_ibt and BRANCH_CODE eq 'HQ' and file_exists('do.matrix_ibt_process.php')}
								<li><a class="sub-slide-item" class="slide-item" href="do.matrix_ibt_process.php">Matrix IBT Process</a></li>
							{/if}
			            </ul>
			        </li>
	            {/if}
				
	    
	            <!-- PO -->
	            {if $sessioninfo.privilege.PO or $sessioninfo.privilege.PO_REQUEST or $sessioninfo.privilege.PO_FROM_REQUEST or $sessioninfo.privilege.PO_REPORT or $sessioninfo.privilege.PO_REQUEST_APPROVAL}
		            <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">PO (Purchase Order)</span><i class="sub-angle fe fe-chevron-down"></i></a>
		                <ul class="sub-slide-menu">
		                    {if $sessioninfo.privilege.PO or $sessioninfo.privilege.PO_VIEW_ONLY}
		                        <li class="sub-slide-sub"><!--a href="purchase_order.php">Purchase Order</a-->
		                        <a class="sub-slide-item" href="po.php">Purchase Order</a></li>
		                    {/if}
		                    {if $sessioninfo.privilege.PO_APPROVAL}
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="po_approval.php">PO Approval</a></li>
		                    {/if}
		                    {if $sessioninfo.privilege.PO_FROM_REQUEST}
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="po_request.process.php">Create PO from Request</a></li>
		                    {/if}
		                    {if $sessioninfo.privilege.PO_REQUEST}
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="po_request.request.php">PO Request</a></li>
		                    {/if}
		                    {if $sessioninfo.privilege.PO_REQUEST_APPROVAL}
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="po_request.approval.php">PO Request Approval</a></li>
		                    {/if}
		                    {if $sessioninfo.privilege.PO_TICKET && $config.po_allow_vendor_request}
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="vendor_po_request.php">Vendor PO Access</a></li>
		                    {/if}
		                    {if $sessioninfo.privilege.PO_REPORT}<li><a class="sub-slide-item"  href="purchase_order.summary.php">PO Summary</a></li>{/if}
		                    {if file_exists("`$smarty.server.DOCUMENT_ROOT`/po_qty_performance.php") and $sessioninfo.privilege.PO_REPORT}
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="po_qty_performance.php">PO Quantity Performance</a></li>
		                    {/if}
		                    {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.stock_reorder.php") and $sessioninfo.privilege.PO and $sessioninfo.privilege.PO_REPORT}
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="report.stock_reorder.php">Stock Reorder Report</a></li>
		                    {/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/sku_purchase_history.php") and $sessioninfo.privilege.PO and $sessioninfo.privilege.PO_REPORT}
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="sku_purchase_history.php">SKU Purchase History</a></li>
		                    {/if}
		                </ul>
		            </li>
	            {/if}
	            
	            {if $config.enable_po_agreement and $sessioninfo.privilege.PO_SETUP_AGREEMENT and $BRANCH_CODE eq 'HQ' and file_exists("`$smarty.server.DOCUMENT_ROOT`/po.po_agreement.setup.php")}
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Purchase Agreement</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="po.po_agreement.setup.php">Add/Edit Purchase Agreement</a></li>
	                    </ul>
	                </li>
	            {/if}
	    
	            {if $sessioninfo.privilege.GRN_APPROVAL && !$config.use_grn_future}<li><a class="slide-item" href="goods_receiving_note_approval.account.php">GRN Account Verification</a>{/if}
	            {if $sessioninfo.privilege.GRA}
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">GRA (Goods Return Advice)</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="goods_return_advice.php">GRA</a></li>
	                        {if $sessioninfo.privilege.GRA_APPROVAL}
	                            <li class="sub-slide-sub"><a class="sub-slide-item"  href="/goods_return_advice.approval.php">GRA Approval</a></li>
	                        {/if}
	                    </ul>
	                </li>
	            {/if}
	    
	            {if $sessioninfo.privilege.GRA_REPORT or $sessioninfo.privilege.GRR_REPORT or $sessioninfo.privilege.GRN_REPORT}
	            <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">GRR / GRN / GRA Reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
		            <ul class="sub-slide-menu">
		            {if $sessioninfo.privilege.GRR_REPORT}
		            <li class="sub-slide-sub"><a class="sub-slide-item"  href="goods_receiving_record.report.php">GRR Report</a></li>
		            <li class="sub-slide-sub"><a class="sub-slide-item"  href="goods_receiving_record.status.php">GRR Status Report</a></li>
		            {/if}
		            {if $sessioninfo.privilege.GRN_REPORT}
		                <li ><a class="sub-slide-item"  href="goods_receiving_note.summary.php">GRN Summary</a></li>
		                <li><a class="sub-slide-item"  href="goods_receiving_note.category_summary.php">GRN Summary by Category</a></li>
		                {if file_exists("`$smarty.server.DOCUMENT_ROOT`/goods_receiving_note.distribution_report.php")}
		                    <li><a class="sub-slide-item"  href="goods_receiving_note.distribution_report.php">GRN Distribution Report</a></li>
		                {/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sku_receiving_history.php")}
		                    <li><a class="sub-slide-item"  href="report.sku_receiving_history.php">SKU Receiving History</a></li>
						{/if}
		            {/if}
		            {if $sessioninfo.privilege.GRA_REPORT}
		                <li><a class="sub-slide-item"  href="goods_return_advice.listing_report.php">GRA Listing</a></li>
		                <li><a class="sub-slide-item"  href="goods_return_advice.summary_by_dept.php">GRA Summary by Department</a></li>
		                <li><a class="sub-slide-item"  href="goods_return_advice.summary_by_category.php">GRA Summary by Category</a></li>
		            {/if}
		            {if $config.gra_enable_disposal && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.goods_return_advice.disposal.php")}
		                <li><a class="sub-slide-item"  href="report.goods_return_advice.disposal.php">GRA Disposal Report</a></li>
		            {/if}
		            </ul>
		        </li>
	            {/if}
	            
				{if $sessioninfo.privilege.DN and !$config.consignment_modules and file_exists("`$smarty.server.DOCUMENT_ROOT`/dnote.php")}
	              <li><a class="slide-item" href="dnote.php">Debit Note</a></li>
	            {/if}
				
				{if $sessioninfo.privilege.CN and !$config.consignment_modules and file_exists("`$smarty.server.DOCUMENT_ROOT`/cnote.php")}
	              <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Credit Note</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                <ul class="sub-slide-menu">
	                    <li class="sub-slide-sub"><a class="sub-slide-item"  href="cnote.php">Credit Note</a></li>
	                    {if $sessioninfo.privilege.CN_APPROVAL}
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="/cnote.approval.php">Credit Note Approval</a></li>
	                    {/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/cnote.summary.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="cnote.summary.php">CN Summary</a></li>
						{/if}
	                </ul>
	              </li>
	            {/if}

	            {* Vendor Portal Related *}
	            {if $config.enable_vendor_portal and ($sessioninfo.privilege.REPORTS_REPACKING and file_exists("`$smarty.server.DOCUMENT_ROOT`/report.repacking.php"))}
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Vendor Portal</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        {if $sessioninfo.privilege.REPORTS_REPACKING and file_exists("`$smarty.server.DOCUMENT_ROOT`/report.repacking.php")}
	                            <li class="sub-slide-sub"><a class="sub-slide-item"  href="report.repacking.php">Repacking Report</a></li>
	                        {/if}
	                    </ul>
	                </li>
	            {/if}

	            {if $sessioninfo.privilege.ACCOUNT_EXPORT}
	              <li><a class="slide-item" href="acc_export.php">Account & GAF Export</a></li>
	              <li><a class="slide-item" href="acc_export.php?a=setting">Account & GAF Export Setting</a></li>
	            {/if}
				
				{* Accounting Export*}
				{if $sessioninfo.privilege.CUSTOM_ACC_AND_GST_SETTING or $sessioninfo.privilege.CUSTOM_ACC_EXPORT_SETUP or $sessioninfo.privilege.CUSTOM_ACC_EXPORT}
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Custom Accounting Export</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							{if $sessioninfo.privilege.CUSTOM_ACC_AND_GST_SETTING and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.acc_and_gst_setting.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="custom.acc_and_gst_setting.php">Custom Account & GST Setting</a></li>
							{/if}
							
							{if $sessioninfo.privilege.CUSTOM_ACC_EXPORT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.setup_acc_export.php")}
								<li><a class="sub-slide-item"  href="custom.setup_acc_export.php">Setup Custom Accounting Export</a></li>
							{/if}
							
							{if $sessioninfo.privilege.CUSTOM_ACC_EXPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.acc_export.php")}
								<li><a class="sub-slide-item"  href="custom.acc_export.php">Custom Accounting Export</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{* ARMS Accounting Integration *}
				{if $config.arms_accounting_api_setting and ($sessioninfo.privilege.ARMS_ACCOUNTING_SETTING or $sessioninfo.privilege.ARMS_ACCOUNTING_STATUS)}
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">ARMS Accounting Integration &nbsp;</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							{if $sessioninfo.privilege.ARMS_ACCOUNTING_SETTING and file_exists("`$smarty.server.DOCUMENT_ROOT`/arms_accounting.setting.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="arms_accounting.setting.php">Setting</a></li>
							{/if}
							{if $sessioninfo.privilege.ARMS_ACCOUNTING_STATUS and file_exists("`$smarty.server.DOCUMENT_ROOT`/arms_accounting.status.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="arms_accounting.status.php">Integration Status</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{* OS Trio Accounting Integration *}
				{if $config.os_trio_settings and ($sessioninfo.privilege.OSTRIO_ACCOUNTING_STATUS)}
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">OS Trio Accounting Integration &nbsp;</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							{if $sessioninfo.privilege.OSTRIO_ACCOUNTING_STATUS and file_exists("`$smarty.server.DOCUMENT_ROOT`/ostrio_accounting.status.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="ostrio_accounting.status.php">Integration Status</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{* Speed99 Integration *}
				{if $config.speed99_settings and ($sessioninfo.privilege.SPEED99_INTEGRATION_STATUS)}
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Speed99 Integration &nbsp;</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							{if $sessioninfo.privilege.SPEED99_INTEGRATION_STATUS and file_exists("`$smarty.server.DOCUMENT_ROOT`/speed99.integration_status.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="speed99.integration_status.php">Integration Status</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{* Komaiso Integration *}
				{if $config.komaiso_settings  and $sessioninfo.privilege.KOMAISO_INTEGRATION_STATUS}
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Komaiso Integration &nbsp;</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							{if $sessioninfo.privilege.KOMAISO_INTEGRATION_STATUS and file_exists("`$smarty.server.DOCUMENT_ROOT`/komaiso.integration_status.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="komaiso.integration_status.php">Integration Status</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php") and ($sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP or $sessioninfo.privilege.ATTENDANCE_SHIFT_ASSIGN)}
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Time Attendance</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">

							{if $sessioninfo.privilege.ATTENDANCE_TIME_OVERVIEW and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.overview.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.overview.php">Time Attendance Overview</a></li>
							{/if}
							{if $sessioninfo.privilege.ATTENDANCE_TIME_SETTING and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.settings.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.settings.php">Settings</a></li>
							{/if}
							
							{if ($sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php")) or ($sessioninfo.privilege.ATTENDANCE_SHIFT_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_assignment.php"))}
								<li class="sub-slide-sub">
									<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">Shift</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu-sub">
										{if $sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php")}
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.shift_table_setup.php">Shift Table Setup</a></li>
										{/if}
										{if $sessioninfo.privilege.ATTENDANCE_SHIFT_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_assignment.php")}
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.shift_assignment.php">Shift Assignments</a></li>
										{/if}
									</ul>
								</li>
							{/if}
							
							{if ($sessioninfo.privilege.ATTENDANCE_PH_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.ph_setup.php")) or ($sessioninfo.privilege.ATTENDANCE_PH_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.ph_assignment.php"))}
								<li class="sub-slide-sub">
									<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">Holiday</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu-sub">
										{if $sessioninfo.privilege.ATTENDANCE_PH_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.ph_setup.php")}
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.ph_setup.php">Holiday Setup</a></li>
										{/if}
										{if $sessioninfo.privilege.ATTENDANCE_PH_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.ph_assignment.php")}
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.ph_assignment.php">Holiday Assignments</a></li>
										{/if}
									</ul>
								</li>
							{/if}
							
							{if ($sessioninfo.privilege.ATTENDANCE_LEAVE_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.leave_setup.php")) or ($sessioninfo.privilege.ATTENDANCE_LEAVE_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.leave_assignment.php"))}
								<li class="sub-slide-sub">
									<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">Leave</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu-sub">
										{if $sessioninfo.privilege.ATTENDANCE_LEAVE_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.leave_setup.php")}
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.leave_setup.php">Leave Table Setup</a></li>
										{/if}
										{if $sessioninfo.privilege.ATTENDANCE_LEAVE_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.leave_assignment.php")}
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.leave_assignment.php">Leave Assignments</a></li>
										{/if}
									</ul>
								</li>
							{/if}
							
							
							{if $sessioninfo.privilege.ATTENDANCE_USER_MODIFY and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.user_records.php")}
								<li><a class="slide-item" href="attendance.user_records.php">User Attendance Records</a></li>
							{/if}
							
							{if $sessioninfo.privilege.ATTENDANCE_CLOCK_REPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.report.daily.php")}
								<li class="sub-slide-sub">
									<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">Reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu-sub">
										{if $sessioninfo.privilege.ATTENDANCE_CLOCK_REPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.report.daily.php")}
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.report.daily.php">Daily Attendance Report</a></li>
										{/if}
										{if $sessioninfo.privilege.ATTENDANCE_CLOCK_REPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.report.monthly_ledger.php")}
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.report.monthly_ledger.php">Monthly Attendance Ledger</a></li>
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
        <li class="slide">
			<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-domain side-menu__icon"></i><span class="side-menu__label">Store</span><i class="angle fe fe-chevron-down"></i></a>
	        <ul class="slide-menu">
	            {if $sessioninfo.privilege.GRR}<li><a class="slide-item" href="goods_receiving_record.php">GRR (Goods Receiving Record)</a></li>{/if}
	            {if $sessioninfo.privilege.GRN}
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">GRN (Goods Receiving Note)</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="/goods_receiving_note.php">GRN</a></li>
	                        {if $sessioninfo.privilege.GRN_APPROVAL}
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="/goods_receiving_note_approval.php">GRN Approval</a></li>
	                        {/if}
	                    </ul>
	                </li>
	            {/if}
	            {if $sessioninfo.privilege.GRA_CHECKOUT}<li><a class="slide-item" href="goods_return_advice.checkout.php">GRA Checkout</a>{/if}
	            {if $sessioninfo.privilege.DO_CHECKOUT}<li><a class="slide-item" href="do_checkout.php">Delivery Order Checkout</a>{/if}
	            {if $sessioninfo.privilege.STOCK_TAKE}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Stock Take</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                	
	                    <ul class="sub-slide-menu">
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.stock_take.php">Stock Take</a></li>
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.stock_take.php?a=import_page">Import / Reset Stock Take</a></li>
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.stock_take.php?a=change_batch">Change Batch</a></li>
	                        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.stock_take_zerolize_negative_stocks.php") && $config.consignment_modules}
	                            <li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.stock_take_zerolize_negative_stocks.php">Zerolize Negative Stocks</a></li>
	                        {/if}
	                    </ul>
	                </li>
	            {/if}
				{if ($sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT or $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT or $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_SCHEDULE_LIST or $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_APPROVAL) and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.cycle_count.assignment.php")}
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Cycle Count</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							{if ($sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT or $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT) and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.cycle_count.assignment.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.cycle_count.assignment.php">Cycle Count Assignment</a></li>
							{/if}
							{if ($sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_APPROVAL) and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.cycle_count.approval.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.cycle_count.approval.php">Cycle Count Approval</a></li>
							{/if}
							{if ($sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_SCHEDULE_LIST) and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.cycle_count.schedule_list.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.cycle_count.schedule_list.php">Monthly Schedule List</a></li>
							{/if}
						</ul>
					</li>
				{/if}
	        </ul>
    	</li>
        {/if}
    {else}
        <li><a href="#">Office</a>
	        <ul class="slide-menu">
	            {if $sessioninfo.privilege.MST_SKU_APPLY && $sessioninfo.branch_type ne "franchise"}
	                <li><a class="slide-item" href="masterfile_sku_application.php">SKU Application</a></li>
	                   {if !$config.menu_hide_bom_application}<li><a class="slide-item" href="masterfile_sku_application_bom.php">Create BOM SKU</a></li>{/if}
	            {/if}
	            
	            {if $sessioninfo.privilege.MST_SKU_APPLY or $sessioninfo.privilege.MST_SKU_APPROVAL}<li><a class="slide-item" href="masterfile_sku_application.php?a=list">SKU Application Status</a>{/if}
	            {if $sessioninfo.privilege.SKU_REPORT}
	            <!--li><a href="sku.summary.php">SKU Summary (Testing)</a></li-->
	            <!--li><a href="sku.history.php">SKU History (Testing)</a></li-->
	            {/if}
	            {if $sessioninfo.privilege.MST_SKU_UPDATE_FUTURE_PRICE && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_items.future_price.php")}
	                <li><a class="slide-item" href="masterfile_sku_items.future_price.php">Batch Selling Price Change</a></li>
	            {/if}

	            {if $sessioninfo.privilege.ACCOUNT_EXPORT}
	              <li><a class="slide-item" href="acc_export.php">Account Export</a>
	              <li><a class="slide-item" href="acc_export.php?a=setting">Account Export Setting</a>
	            {/if}
				
				{* Accounting Export*}
				{if $sessioninfo.privilege.CUSTOM_ACC_AND_GST_SETTING or $sessioninfo.privilege.CUSTOM_ACC_EXPORT_SETUP or $sessioninfo.privilege.CUSTOM_ACC_EXPORT}
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Custom Accounting Export</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							{if $sessioninfo.privilege.CUSTOM_ACC_AND_GST_SETTING and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.acc_and_gst_setting.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="custom.acc_and_gst_setting.php">Custom Account & GST Setting</a></li>
							{/if}
							
							{if $sessioninfo.privilege.CUSTOM_ACC_EXPORT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.setup_acc_export.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="custom.setup_acc_export.php">Setup Custom Accounting Export</a></li>
							{/if}
							
							{if $sessioninfo.privilege.CUSTOM_ACC_EXPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.acc_export.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="custom.acc_export.php">Custom Accounting Export</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php") and ($sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP)}
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Time Attendance</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							{if $sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.shift_table_setup.php">Shift Table Setup</a></li>
							{/if}			
						</ul>
					</li>
				{/if}
	        </ul>
    	</li>
    {/if}
	<!--master files start-->

			{if $sessioninfo.privilege.MASTERFILE}
		<li class="slide">
				<a href="#" class="side-menu__item" data-toggle="slide"><i class="mdi mdi-library-books side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Master files</span><i class="angle fe fe-chevron-down"></i></a>
			<ul class="slide-menu">
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Category</span><i class="sub-angle fe fe-chevron-down"></i></a>
	 					<ul class="sub-slide-menu">
			 				<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_category.php">Category Listing</a></li>
							{*<li><a href="masterfile_category_markup.php">Category Markup %</a></li>*}
	 					</ul>
				</li>
								{if $sessioninfo.privilege.MST_SKU_UPDATE or $sessioninfo.privilege.MST_SKU_APPLY or $sessioninfo.privilege.MST_SKU}
				<li class="sub-slide"><a href="#" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">SKU reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.php">SKU Listing</a></li>
								{if $sessioninfo.privilege.MST_SKU_UPDATE_PRICE}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_sku_items_price.php">Change Selling Price</a></li>{/if}
								{if !$config.menu_hide_bom_application}	
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="bom.php">BOM Editor</a></li>{/if}
				
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_group.php">SKU Group</a></li>
				
				
								{if $BRANCH_CODE eq 'HQ' and $config.po_enable_ibt and $config.enable_sku_monitoring2 and $sessioninfo.privilege.MST_SKU_MORN_GRP and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_monitoring_group.php")}
				   			 <li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_monitoring_group.php">SKU Monitoring Group</a></li>
								{/if}
								{if $BRANCH_CODE eq 'HQ' and $config.enable_replacement_items and $sessioninfo.privilege.MST_SKU_RELP_ITEM and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_replacement_items.php")}
				    		<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_replacement_items.php">Replacement Items</a></li>
								{/if}
								{if $config.enable_sn_bn && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_items.serial_no.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_items.serial_no.php">Serial No Listing</a></li>
								{/if}
								{if $config.enable_sn_bn && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_items.batch_no_setup.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_items.batch_no_setup.php">SKU Batch No Setup</a></li>
								{/if}
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku.price_list.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.price_list.php">SKU Price List</a></li>
								{/if}
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_items.po_reorder_qty_by_branch.php") && $sessioninfo.privilege.MST_PO_REORDER_QTY_BY_BRANCH}
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_items.po_reorder_qty_by_branch.php">PO Reorder Qty by Branch</a></li>
								{/if}
								{if $config.enable_gst && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_gst.price_wizard.php") && ($sessioninfo.privilege.MST_SKU_UPDATE_PRICE || $sessioninfo.privilege.MST_SKU_UPDATE_FUTURE_PRICE)}
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_gst.price_wizard.php">GST Price Wizard</a></li>
								{/if}
				
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_stock_balance_listing.php">SKU Stock Balance Listing (Download)</a></li>

								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_tag.php") && $sessioninfo.privilege.MST_SKU_TAG}
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_tag.php">SKU Tag</a></li>
								{/if}
				
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku.update_brand_vendor.php") && $sessioninfo.privilege.MST_SKU_UPDATE}
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.update_brand_vendor.php?method=brand">Update SKU Brand by CSV</a></li>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.update_brand_vendor.php?method=vendor">Update SKU Vendor by CSV</a></li>
								{/if}
				
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku.update_po_reorder_qty.php") && $sessioninfo.privilege.MST_SKU_UPDATE}
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.update_po_reorder_qty.php">Update SKU Stock Reorder Min & Max Qty by CSV</a></li>
								{/if}
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku.update_category.php") && $sessioninfo.privilege.MST_SKU_UPDATE}
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.update_category.php">Update SKU Category by CSV</a></li>
								{/if}
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku.update_category_discount.php") && $sessioninfo.privilege.MST_SKU_UPDATE && $sessioninfo.privilege.CATEGORY_DISCOUNT_EDIT}
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.update_category_discount.php">Update SKU Category Discount by CSV</a></li>
								{/if}
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku.update_sku.php") && $sessioninfo.privilege.MST_SKU_UPDATE}
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.update_sku.php">Update SKU Info by CSV</a></li>
								{/if}
						</ul>
		{/if}
		<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="masterfile_uom.php"><span class="sub-side-menu__label">UOM</span></a>
		<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="masterfile_brand.php"><span class="sub-side-menu__label">Brand</span></a>
		<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="masterfile_brgroup.php"><span class="sub-side-menu__label">Brand Group</span></a>
		
		
		<li class="sub-slide"><a href="#" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Vendor</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_vendor.php">Add / Edit</a></li>
								{if $sessioninfo.privilege.MST_VENDOR_QUOTATION_COST and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_vendor.quotation_cost.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_vendor.quotation_cost.php">Quotation Cost</a></li>
								{/if}
						</ul>
		</li>
		
		{if $sessioninfo.privilege.MST_BRANCH}
		<li class="sub-slide"><a href="#" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Branch</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_branch.php">Add / Edit</a></li>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_branch_group.php">Branches Group</a></li>
								{if $config.masterfile_branch_enable_additional_sp && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_branch.additional_selling_price.php")}
							<li><a href="masterfile_branch.additional_selling_price.php">Branches Additional Selling Price</a></li>
								{/if}
						</ul>
		</li>
		{/if}
		
		{if $sessioninfo.privilege.MST_DEBTOR or $sessioninfo.privilege.MST_DEBTOR_PRICE_LIST}
		<li class="sub-slide"><a href="#" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Debtor</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
								{if $sessioninfo.privilege.MST_DEBTOR and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_debtor.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_debtor.php">Add / Edit</a></li>
								{/if}
								{if $sessioninfo.privilege.MST_DEBTOR_PRICE_LIST and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_debtor_price.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_debtor_price.php">Debtor Price List</a></li>
								{/if}
								{if $sessioninfo.privilege.MST_DEBTOR_CSV_UPDATE_PRICE and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_import_debtor_price.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_import_debtor_price.php">Import / Update Debtor Price by CSV</a></li>
								{/if}
						</ul>
		</li>
		{/if}
		{if $sessioninfo.privilege.MST_TRANSPORTER and $config.enable_transporter_masterfile}
		<li class="sub-slide"><a href="masterfile_transporter.php" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Transporter</span><i class="sub-angle fe fe-chevron-down"></i></a>
		
		{/if}
		{if $sessioninfo.privilege.MST_TRANSPORTER_v2 and $config.enable_reorder_integration}
		<li class="sub-slide"><a href="masterfile_transporter.php" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Transporter V2</span><i class="sub-angle fe fe-chevron-down"></i></a>
				<ul class="sub-slide-menu">
					<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_shipper.php?a=transporter">Transporter</a></li>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_shipper.php?a=transporter_vehicle">Vehicle</a></li>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_shipper.php?a=transporter_driver">Driver</a></li>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_shipper.php?a=transporter_route_area">Route Area</a></li>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Maintenance</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<li class="sub-slide-sub"><a class="sub-slide-menu__item" href="masterfile_shipper.php?a=transporter_area">Area</a></li>
							<li class="sub-slide-sub"><a class="sub-slide-menu__item" href="masterfile_shipper.php?a=transporter_route">Route</a></li>							
							<li class="sub-slide-sub"><a class="sub-slide-menu__item" href="masterfile_shipper.php?a=transporter_type">Type</a></li>
							<li class="sub-slide-sub"><a class="sub-slide-menu__item" href="masterfile_shipper.php?a=transporter_vehicle_brand">Vehicle Brand</a></li>
							<li class="sub-slide-sub"><a class="sub-slide-menu__item" href="masterfile_shipper.php?a=transporter_vehicle_status">Vehicle Status</a></li>
							<li class="sub-slide-sub"><a class="sub-slide-menu__item" href="masterfile_shipper.php?a=transporter_vehicle_type">Vehicle Type</a></li>
						</ul>
					</li>
				</ul>
			</li>
		{/if}
		{if $config.use_consignment_bearing and $sessioninfo.privilege.MST_CONTABLE and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_consignment_bearing.php")}
		<li class="sub-slide"><a href="masterfile_consignment_bearing.php" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Consignment Bearing</span><i class="sub-angle fe fe-chevron-down"></i></a>   
		
		{/if}
		{if $BRANCH_CODE eq 'HQ' and $sessioninfo.privilege.MST_BANK_INTEREST and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_bank_interest.php") and $config.enable_sku_monitoring2}
		    <li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_bank_interest.php">Bank Interest</a></li>
		{/if}
		{if $sessioninfo.privilege.MST_COUPON}
		<li class="sub-slide-menu"><a href="masterfile_transporter.php" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Coupon</span><i class="sub-angle fe fe-chevron-down"></i></a>
				<ul class="sub-slide-menu">
				   			 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_coupon.php")}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_coupon.php">
							{if $BRANCH_CODE eq 'HQ'}Create / Print{else}View{/if}
							</a>
						</li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.coupon.transaction.php")}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="report.coupon.transaction.php">Transaction Report</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.coupon.details.php")}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="report.coupon.details.php">Details Report</a></li>
							{/if}
				</ul>
			</li>
		{/if}

		{if $sessioninfo.privilege.MST_VOUCHER}
		<li class="sub-slide"><a href="masterfile_transporter.php" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Master Transport V2</span><i class="sub-angle fe fe-chevron-down"></i></a>
		        <ul class="sub-slide-menu">
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.setup.php") and $sessioninfo.privilege.MST_VOUCHER_SETUP}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_voucher.setup.php">Setup</a></li>
					{/if}
					
		            {if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.php")}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_voucher.php">Listing</a></li>
					{/if}
                    {if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.register.php") and $sessioninfo.privilege.MST_VOUCHER_REGISTER and $BRANCH_CODE eq 'HQ'}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_voucher.register.php">Registration</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.activate.php") and $sessioninfo.privilege.MST_VOUCHER_ACTIVATE}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_voucher.activate.php">Activation</a></li>
					{/if}

					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.voucher.transaction.php")}
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.voucher.transaction.php">Transaction Report</a></li>{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.voucher.details.php")}
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.voucher.details.php">Details Report</a></li>{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.voucher.activation.php")}
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.voucher.activation.php">Activation & Cancellation Report</a></li>{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.voucher.collection.php")}
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.voucher.collection.php">Account-receivable Report</a></li>{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.voucher.payment.php")}
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.voucher.payment.php">Account-payable Report</a></li>{/if}
					
					{if $config.enable_voucher_auto_redemption and ((file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.auto_redemption.setup.php") and $sessioninfo.privilege.MST_VOUCHER_AUTO_REDEMP_SETUP) or (file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.auto_redemption.generate.php") and $sessioninfo.privilege.MST_VOUCHER_AUTO_REDEMP_GENERATE))}
						<li>
							<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-account-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Auto Redemption</span><i class="angle fe fe-chevron-down"></i></a>
					        <ul class="slide-menu">
					        	{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.auto_redemption.setup.php") and $sessioninfo.privilege.MST_VOUCHER_AUTO_REDEMP_SETUP}
					        		<li class="slide-item"><a class="sub-side-menu__item" href="masterfile_voucher.auto_redemption.setup.php">Setup</a></li>
					        	{/if}
					        	
					        	{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.auto_redemption.generate.php") and $sessioninfo.privilege.MST_VOUCHER_AUTO_REDEMP_GENERATE}
					        		<li class="slide-item"><a class="sub-side-menu__item" href="masterfile_voucher.auto_redemption.generate.php">Generate Voucher</a></li>
					        		<li class="slide-item"><a class="sub-side-menu__item" href="masterfile_voucher.auto_redemption.generate.php?a=his_list">History Listing</a></li>
					        	{/if}
					        </ul>
					    </li>
					{/if}
				</ul>
			</li>
		{/if}
		{if $config.enable_supermarket_code and $config.consignment_modules and $BRANCH_CODE eq 'HQ' and $sessioninfo.privilege.MST_SUPERMARKET_CODE and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_supermarket_code.php")}
		<li class="sub-slide"><a href="masterfile_supermarket_code.php" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Super market code</span></a>
		
		{/if}
		{if $config.masterfile_enable_sa && $sessioninfo.privilege.MST_SALES_AGENT}
		<li class="sub-slide"><a href="" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Sales Agent</span><i class="sub-angle fe fe-chevron-down"></i></a>
				<ul class="sub-slide-menu">
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sa.php")}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_sa.php">Create / Edit</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sa_commission.php")}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_sa_commission.php">Commission Table</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sa.position_setup.php")}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_sa.position_setup.php">Position Table</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sa.kpi_setup.php")}
						<li class="sub-slide-sub"><a href="#" class=submenu>KPI</a>
							<ul class="sub-slide-menu-sub">
								{if $sessioninfo.privilege.MST_SALES_AGENT_KPI_SETUP && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sa.kpi_setup.php")}
									<li><a class="sub-slide-item" href="masterfile_sa.kpi_setup.php">KPI Table</a></li>
								{/if}
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sa.kpi_result.php")}
									<li><a class="sub-slide-item" href="masterfile_sa.kpi_result.php">KPI Result</a></li>
								{/if}
							</ul>
						</li>
					{/if}
					<li class="sub-slide"><a href="" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.view_sa_commission.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item" href="report.view_sa_commission.php">View Sales Agent Commission</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sa_commission_calculation.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item" href="report.sa_commission_calculation.php">Sales Agent Commission Calculation Report</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sa_performance.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item" href="report.sa_performance.php">Sales Agent Performance Report</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sa_commission_statement_by_company.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item" href="report.sa_commission_statement_by_company.php">Sales Agent Commission Statement by Company Report</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sa_daily_details.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item" href="report.sa_daily_details.php">Sales Agent Daily Details Report</a></li>
							{/if}
						</ul>
					</li>
				</ul>
			</li>
		{/if}
		{*{if $config.masterfile_enable_return_policy}
			<li><a href="#" class=submenu>Return Policy</a>
				<ul class="sub-slide-menu">
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_return_policy.php")}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_return_policy.php">Create / Edit</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_return_policy_configure.php")}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_return_policy_configure.php">Configure</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.rp_item_return.php")}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="report.rp_item_return.php">Return Policy Item Returned Report</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.rp_pending_item.php")}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="report.rp_pending_item.php">Return Policy Pending Item Report</a></li>
					{/if}
				</ul>
			</li>
		{/if}
		*}
		{if $config.enable_gst && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_gst.php")}
			<li><a href="masterfile_gst.php">Masterfile GST Tax Code</a>
		{/if}
	</ul>

	</li>
	{/if}

	<!--master files ends-->


			<!--membership files start-->

			{if $sessioninfo.privilege.MEMBERSHIP || $sessioninfo.privilege.RPT_MEMBERSHIP}
			<li class="slide">
				<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-account-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Membership</span><i class="angle fe fe-chevron-down"></i></a>
			<ul class="slide-menu">
				{if $config.membership_allow_add_at_backend}
				{if $sessioninfo.privilege.MEMBERSHIP_ADD}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="membership.php?a=add"><span class="sub-side-menu__label">Add New Member</span></a>
		
					
				{/if}
				{/if}
				{if $sessioninfo.privilege.MEMBERSHIP_EDIT or $sessioninfo.privilege.MEMBERSHIP_ADD}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="membership.php?t=update"><span class="sub-side-menu__label">Update Information</span></a>
					
				{/if}
				{if $sessioninfo.privilege.MEMBERSHIP_VERIFY}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="membership.php?t=verify"><span class="sub-side-menu__label">Verification</span></a>
					
				{/if}
				{if $sessioninfo.privilege.MEMBERSHIP_EDIT or $sessioninfo.privilege.MEMBERSHIP_ADD}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="membership.listing.php"><span class="sub-side-menu__label">Member Listing</span></a>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="membership.php?t=history"><span class="sub-side-menu__label">Check Points &amp; History</span></a>
					
				{/if}
				{if $sessioninfo.privilege.MEMBERSHIP_TERMINATE}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="membership.terminate.php"><span class="sub-side-menu__label">Terminate</span></a>
					
				{/if}
				{if $sessioninfo.privilege.RPT_MEMBERSHIP}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Membership report</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						<li class="sub-slide-sub"><a class="sub-slide-item" href="report.mem_counter.php">Membership Counters Report</a></li>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="report.mem_verification.php">Membership Verification Report</a></li>
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.redemption_points_history.php") && $config.membership_redemption_module}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.redemption_points_history.php">Membership Points History Report</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.membership_expiration.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.membership_expiration.php">Membership Expiration Report</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.membership_renewal.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.membership_renewal.php">Membership Renewal Report</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.membership_fees_collection_summary.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.membership_fees_collection_summary.php">Membership Fees Collection Summary Report</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.membership_daily_collection.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.membership_daily_collection.php">Membership Daily Collection Report</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.membership_points_detail.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.membership_points_detail.php">Membership Points Detail Report</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.membership_issued_points.php")}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.membership_issued_points.php">Membership Issued Points Report</a></li>
						{/if}
					</ul>
				</li>
				{/if}
				{if $config.membership_redemption_module}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Membership redemption</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						{if $sessioninfo.privilege.MEMBERSHIP_SETREDEEM}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.redemption_setup.php">Redemption Item Setup</a></li>
						{/if}
						{if $sessioninfo.privilege.MEMBERSHIP_ITEM_CFRM && $config.membership_redemption_use_enhanced}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.redemption_item_approval.php">Redemption Item Approval</a></li>
						{/if}
						{if $sessioninfo.privilege.MEMBERSHIP_REDEEM}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.redemption.php">Make Redemption</a></li>
						{/if}
						{if $sessioninfo.privilege.MEMBERSHIP_REDEEM or $sessioninfo.privilege.MEMBERSHIP_CANCEL_RE}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.redemption_history.php">Redemption History</a></li>
						{/if}
						{if $sessioninfo.privilege.MEMBERSHIP_REDEEM_RPT}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.redemption_summary.php">Redemption Summary</a></li>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.redemption_ranking.php">Redemption Ranking</a></li>
						{/if}
					</ul>
				</li>
				{/if}
				{if $config.membership_control_counter_adjust_point}
					<li><a href="membership.delivery.php">Delivery</a></li>
				{/if}
				
				{if $config.membership_enable_staff_card}
					{if $sessioninfo.privilege.MEMBERSHIP_STAFF}
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Membership staff card</span><i class="sub-angle fe fe-chevron-down"></i></a>
							<ul class="sub-slide-menu">
								{if $sessioninfo.privilege.MEMBERSHIP_STAFF_SET_QUOTA and file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.staff.setup_quota.php")}
									<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.staff.setup_quota.php">Setup Quota</a></li>
								{/if}
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.staff.usage_report.php")}
									<li class="sub-slide-sub"><a  class="sub-slide-item" href="membership.staff.usage_report.php">Quota Usage Report</a></li>
								{/if}
							</ul>
						</li>
					{/if}
				{/if}
				
				{if $sessioninfo.privilege.MEMBERSHIP_OVERVIEW}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Membership overview</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.overview.general.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.overview.general.php">Composition</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.overview.sales.php")}
								<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.overview.sales.php">Sales</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{if $config.membership_mobile_settings and ($sessioninfo.privilege.MEMBERSHIP_MOBILE_ADS_SETUP)}
					<li><a href="#" class="submenu">Mobile App</a>
						<ul>
							{if $sessioninfo.privilege.MEMBERSHIP_MOBILE_ADS_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.mobile_app.ads.php")}
								<li><a href="membership.mobile_app.ads.php">Advertisement Setup</a></li>
							{/if}
							{if $sessioninfo.privilege.MEMBERSHIP_MOBILE_NOTICE_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.mobile_app.notice_board.php")}
								<li><a href="membership.mobile_app.notice_board.php">Notice Board Setup</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.package.setup.php") and ($sessioninfo.privilege.MEMBERSHIP_PACK_SETUP or $sessioninfo.privilege.MEMBERSHIP_PACK_REDEEM or $sessioninfo.privilege.MEMBERSHIP_PACK_REPORT)}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Membership package</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.package.setup.php") and $sessioninfo.privilege.MEMBERSHIP_PACK_SETUP}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.package.setup.php">Package Setup</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.package.details.php") and ($sessioninfo.privilege.MEMBERSHIP_PACK_REDEEM)}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.package.details.php?a=scan_member">Package Redemption</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.package.rating_report.php") and ($sessioninfo.privilege.MEMBERSHIP_PACK_REPORT)}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.package.rating_report.php">Package Rating Analysis Report</a></li>
						{/if}
					</ul>
				</li>
				{/if}
				
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.credit.promotion.php") and ($sessioninfo.privilege.MEMBERSHIP_CREDIT_PROMO or $sessioninfo.privilege.MEMBERSHIP_CREDIT_SETTINGS)}
				<li class="slide">
					<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-account-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Membership Credit</span><i class="angle fe fe-chevron-down"></i></a>
					<ul class="slide-menu">
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.credit.promotion.php") and $sessioninfo.privilege.MEMBERSHIP_CREDIT_PROMO}
							<li class="sub-slide"><a class="sub-side-menu__item" href="membership.credit.promotion.php">Credit Promotion</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.credit.settings.php") and $sessioninfo.privilege.MEMBERSHIP_CREDIT_SETTINGS}
							<li class="sub-slide"><a class="sub-side-menu__item" href="membership.credit.settings.php">Credit Settings</a></li>
						{/if}
					</ul>
				</li>
				{/if}
			</ul>
			{/if}
				<!--membership files ends-->
<!--Fresh market menu start-->
{if $config.enable_fresh_market_sku and $sessioninfo.privilege.FM}
<li class="slide">
	<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-account side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Fresh Market</span><i class="angle fe fe-chevron-down"></i></a>
	<ul class="slide-menu">
		{if $sessioninfo.privilege.FM_WRITE_OFF and file_exists("`$smarty.server.DOCUMENT_ROOT`/adjustment.fresh_market_write_off.php")}
			<li class=""><a class="sub-slide-item" data-toggle="sub-slide" href="/adjustment.fresh_market_write_off.php"> SKU Write-Off</a></li>
		{/if}
		{if $sessioninfo.privilege.FM_STOCK_TAKE}
		<li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Stock Take</a>
			<ul class="sub-slide-menu">
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.fresh_market_stock_take.php")}
					<li class="sub-slide-sub"><a class="sub-slide-item" href="admin.fresh_market_stock_take.php"> Stock Take</a></li>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="admin.fresh_market_stock_take.php?a=import_page"> Import / Reset Stock Take</a></li>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="admin.fresh_market_stock_take.php?a=change_batch">Change Batch</a></li>
				{/if}
			</ul>
		</li>
		{/if}
		{if $sessioninfo.privilege.FM_REPORT}
		<li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Report</a>
			<ul class="sub-slide-menu">
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.fresh_market_stock_take.php")}
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.fresh_market_stock_take.php"> Fresh Market Stock Take Report</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.fresh_market_sales.php")}
					<li class="sub-slide-sub"><a href="sub-slide-item" href="report.fresh_market_sales.php"> Fresh Market Sales Report</a></li>
				{/if}
			</ul>
		</li>
		{/if}
	</ul>
</li>
{/if}

<!--fresh market menu ends-->
				<!--report files start-->

				 <!-- Report -->
			{if !$config.consignment_modules}
			<li class="slide">
					<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-chart-bar side-menu__icon" ></i><span class="side-menu__label">Reports</span><i class="angle fe fe-chevron-down"></i></a>
				<ul class="slide-menu">
					{include file=menu.reports.tpl}
					 {if $sessioninfo.privilege.REPORTS_SKU}
					 <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">SKU reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
							 <ul class="sub-slide-menu">
								 {if $config.enable_sn_bn && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.batch_no.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.batch_no.php">Batch No Report</a></li>{/if}
								 
								 {if $config.enable_sn_bn && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.batch_no_transaction_detail.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.batch_no_transaction_detail.php">Batch No Transaction Details Report</a></li>{/if}
								 
								 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.brand_sales_price_type_discount.php")}
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.brand_sales_price_type_discount.php">Brand / Vendor Sales by Price Type and Discount Report</a></li>
								 {/if}						
								 
								 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.closing_stock_by_sku.php")}
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.closing_stock_by_sku.php">Closing Stock by SKU Report</a></li>
								 {/if}
								 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.closing_stock.php")}
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.closing_stock.php">Closing Stock Report</a></li>
								 {/if}
								 {if $config.enable_one_color_matrix_ibt && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.broken_size_clr_by_branch.php")}
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.broken_size_clr_by_branch.php">Broken Size & Color by Branch Report</a></li>
								 {/if}
								 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.mprice_sales.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.mprice_sales.php">MPrice Sales Report</a></li>{/if}
								 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.multi_branch_sales.php")}
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.multi_branch_sales.php">Multi Branch Sales Report</a></li>
								 {/if}
								 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.multi_branch_stock_balance.php")}
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.multi_branch_stock_balance.php">Multi Branch Stock Balance</a></li>
								 {/if}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.negative_stock.php">Negative Stock</a></li>
								 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.new_sku_sales_monitor.php")}
								 <li class="sib-slide-sub"><a class="sub-slide-item" href="report.new_sku_sales_monitor.php">New SKU Sales Monitoring Report</a></li>{/if}
								 {if $config.enable_sn_bn && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sn_activation.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sn_activation.php">Serial No Activation Report</a></li>{/if}
								 {if $config.enable_sn_bn && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sn_expiry.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sn_expiry.php">Serial No Expiry Report</a></li>{/if}
								 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sn_return.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sn_return.php">Serial No Return Report</a></li>{/if}
								 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sn_status.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sn_status.php">Serial No Status Report</a></li>{/if}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_items_gp.php">SKU Items Gross Profit Report</a></li>
								 {if $config.enable_sku_monitoring and file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sku_monitoring.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_monitoring.php">SKU Monitoring</a></li>
								 {/if}
								 
								 {if $config.enable_sku_monitoring2 and file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sku_monitoring2.php")}
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_monitoring2.php">SKU Monitoring 2</a></li>
								 {/if}
								 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sku_sales.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_sales.php">SKU Sales Report</a></li>{/if}
								 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sku_sales_purchase_profit_margin.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_sales_purchase_profit_margin.php">SKU Sales & Purchase Profit Margin Special Calculation Report</a></li>{/if}
								 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sku_trans_type_filter.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_trans_type_filter.php">SKU Transaction Type Filter</a></li>
								 {/if}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.slow_moving_item.php">Slow Moving Items</a></li>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_aging.php">Stock Aging Report</a></li>
								 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.stock_balance_detail_by_day.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_balance_detail_by_day.php">Stock Balance Detail by SKU Report</a></li>{/if}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_balance.php">Stock Balance Report by Department</a></li>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_balance_summary.php">Stock Balance Summary</a></li>
							 </ul>
						 </li>
					 {/if}
		 
					 {if $config.enable_gst and $sessioninfo.privilege.REPORTS_GST}
					 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide"> GST Reports</a>
						 <ul class="sub-slide-menu">
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.gst_summary.php">GST Summary</a></li>
						 </ul>
						 </li>
					 {/if}
					 
					 {if $sessioninfo.privilege.REPORTS_CUSTOM_BUILDER_CREATE || ($sessioninfo.privilege.REPORTS_CUSTOM_VIEW && ($available_custom_report_list.group|@count > 0 || $available_custom_report_list.nogroup|@count > 0)) }
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Custom reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
						 <ul class="sub-slide-menu">
							 {if $sessioninfo.privilege.REPORTS_CUSTOM_BUILDER_CREATE and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom_report.builder.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="custom_report.builder.php">Report Builder</a></li>
							 {/if}
							 
							 {if $sessioninfo.privilege.REPORTS_CUSTOM_VIEW}
								 {* Group *}
								 {foreach from=$available_custom_report_list.group key=custom_report_group_name item=custom_report_group_list}
								 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide"> {$custom_report_group_name}</a>
								 
								 
										 <ul class="sub-slide-menu" >
											 {foreach from=$custom_report_group_list item=r}
												 <li class="sub-slide-sub"><a class="sub-slide-item" href="custom_report.php?report_id={$r.id}">{$r.report_title}</a></li>
											 {/foreach}
										 </ul>
									 </li>
								 {/foreach}
								 
								 {* non-group *}
								 {foreach from=$available_custom_report_list.nogroup item=r}
								 <li class="sub-slide"><a href="custom_report.php?report_id={$r.id}" class="sub-slide-menu__item" data-toggle="sub-slide">{$r.report_title}</a>
									
								 {/foreach}
							 {/if}
						 </ul>
					 </li>
					 {/if}
					 
					 {if $config.show_old_report}
						 {capture assign=report_html}{strip}
							 {if $sessioninfo.privilege.REPORTS_SALES}
								 <li class="sub-slide"><a class="sub-slide-menu__item" href="sales_report.brand.php">{*<img src="/ui/print.png" align="absmiddle" border="0">*}Daily Brand Sales Report</a></li>
								 <li class="sub-slide"><a class="sub-slide-menu__item" href="sales_report.vendor.php">{*<img src="/ui/print.png" align="absmiddle" border="0">*}Daily Vendor Sales Report</a></li>
								 <li class="sub-slide"><a class="sub-slide-menu__item" href="sales_report.department.php">{*<img src="/ui/print.png" align="absmiddle" border="0">*}Department Monthly Sales Report</a></li>
							 {/if}
						 {/strip}{/capture}
						 {if $report_html}
						 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Old report</a>
								 <ul class="sub-slide-menu">{$report_html}</ul>
							 </li>
						 {/if}
					 {/if}
			 
					 {if isset($config.custom_report)}
						 {include file=$config.custom_report}
					 {/if}
			 
					 {if $sessioninfo.id eq 1 && $BRANCH_CODE eq 'HQ'}
					 <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="/pivot.php?a=new"><span class="sub-side-menu__label">Create/Modify Reports</span></a>
					
						 
					 {/if}
			 
					 {if $sessioninfo.privilege.PIVOT_SALES and $pivots}
					 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Sales report<i class="sub-angle fe fe-chevron-down"></i></a>
						 <ul class="sub-slide-menu">
							 {section name=i loop=$pivots}
							 {if $pivots[i].rpt_group eq 'Sales'}<li class="sub-slide-sub"><a class="sub-side-item" href="/pivot.php?a=load&id={$pivots[i].id}">{$pivots[i].title}</a></li>{/if}
							 {/section}
						 </ul>
					 {/if}
			 
					 {if $sessioninfo.privilege.PIVOT_OFFICER and $pivots}
					 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Officers reports</a>
						 <ul class="sub-slide-menu">
							 {section name=i loop=$pivots}
							 {if $pivots[i].rpt_group eq 'Officer'}<li class="sub-slide-sub"><a class="sub-slide-item" href="/pivot.php?a=load&id={$pivots[i].id}">{$pivots[i].title}</a></li>{/if}
							 {/section}
						 </ul>
					 {/if}
					 
					 {if $sessioninfo.privilege.PIVOT_MANAGEMENT and $pivots}
					 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Management reports</a>
						 <ul class="sub-slide-menu">
							 {section name=i loop=$pivots}
							 {if $pivots[i].rpt_group eq 'Management'}<li class="sub-slide-sub"><a class="sub-slide-item" href="/pivot.php?a=load&id={$pivots[i].id}">{$pivots[i].title}</a></li>{/if}
							 {/section}
						 </ul>
					 {/if}
					 
					 {if $config.monthly_closing and $sessioninfo.privilege.REPORTS_MONTHLY_CLOSING}
					 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Monthly closing</a>
						 <ul class="sub-slide-menu">
							 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.monthly_closing_stock_balance.php") }
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.monthly_closing_stock_balance.php">Monthly Closing Stock Balance Report by Department</a></li>
							 {/if}
							 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.monthly_closing_stock_balance_summary.php") }
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.monthly_closing_stock_balance_summary.php">Monthly Closing Stock Balance Summary</a></li>
							 {/if}
						 </ul>
					 {/if}
			 
					 {if $sessioninfo.privilege.STOCK_CHECK_REPORT and !strstr($config.hide_from_menu,'STOCK_CHECK_REPORT')}
					 <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Stock Take</span><i class="sub-angle fe fe-chevron-down"></i></a>
						 <ul class="sub-slide-menu">
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="pivot.stockchk.php?a=list">Customize Reports</a></li>
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_check.php">Stock Take Summary</a></li>
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_take_variance_by_dept.php">Stock Take Variance by Dept Report</a></li>
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_take_variance.php">Stock Take Variance Report</a></li>
							 {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.stock_take_inquiry.php")}
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_take_inquiry.php">Stock Take Inquiry</a></li>
							 {/if}
						 </ul>
					 {/if}
					 {if $sessioninfo.level>=500}
					 {if $sessioninfo.privilege.PO_REPORT}
					 <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="vendor_po.summary.php"><span class="sub-side-menu__label">Vendor Purchase Ranking</span></a>
					 {/if}
					 {/if}
				
				</ul>	 
			</li>
			{/if}
			 <!-- End of Report -->

				<!--report files end-->



			<!--Front end files start here-->

			{if $sessioninfo.privilege.POS_BACKEND or $sessioninfo.privilege.POS_VERIFY_SKU or $sessioninfo.privilege.POS_REPORT}
			{assign var=pos_checking value=true}
		{/if}
		
		{if $sessioninfo.privilege.CC_FINALIZE or $sessioninfo.privilege.CC_UNFINALIZE or $sessioninfo.privilege.CC_DEPOSIT}
			{assign var=cc_checking value=true}
		{/if}
		{if $sessioninfo.privilege.FRONTEND_SETUP or $sessioninfo.privilege.PROMOTION or $pos_checking or $cc_checking or $sessioninfo.privilege.FRONTEND_PRINT_FULL_TAX_INVOICE}
		<li class="slide">
		<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-newspaper side-menu__icon" ></i><span class="side-menu__label">Front end</span><i class="angle fe fe-chevron-down"></i></a>
		<ul class="slide-menu">
			{if $sessioninfo.privilege.FRONTEND_SETUP}
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Settings</span><i class="sub-angle fe fe-chevron-down"></i></a>
		
				<ul class="sub-slide-menu">
					<li class="sub-slide-sub"><a class="sub-slide-item" href="frontend.php">Counters Setup</a></li>
					{if $sessioninfo.level >=9999 && $sessioninfo.is_arms_user && file_exists("`$smarty.server.DOCUMENT_ROOT`/info.counter_configuration.php")}
					<li class="sub-slide-sub"><a class="sub-slide-item" href="info.counter_configuration.php">Counter Setup Information</a>{/if}
					<li class="sub-slide-sub"><a class="sub-slide-item" href="pos.settings.php">POS Settings</a></li>
					{if $sessioninfo.privilege.FRONTEND_SET_CASHIER and file_exists("`$smarty.server.DOCUMENT_ROOT`/front_end.cashier_setup.php")}
						<li class="sub-slide-sub"><a class="sub-slide-item" href="front_end.cashier_setup.php">Cashier Setup</a></li>
					{/if}
				</ul>
			</li>
			{/if}
			
			{if $sessioninfo.privilege.PROMOTION}
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Promotion</span><i class="sub-angle fe fe-chevron-down"></i></a>
				<ul class="sub-slide-menu">
					<li class="sub-slide-sub"><a class="sub-slide-item" href="promotion.php">Create / Edit</a></li>
					{if $sessioninfo.privilege.PROMOTION_APPROVAL}
					<li class="sub-slide-sub"><a class="sub-slide-item" href="promotion_approval.php">Approval</a></li>{/if}
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.promotion_summary.php">Promotion Summary</a></li>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.promotion_result.php">Promotion Result</a></li>
					{*{if $config.enable_mix_and_match_promotion}{/if}*}
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.mix_n_match_promotion_result.php">Mix and Match Promotion Result</a></li>
				</ul>
			</li>
			{/if}
			{if $sessioninfo.privilege.POS_BACKEND or $sessioninfo.privilege.CC_FINALIZE or $sessioninfo.privilege.CC_UNFINALIZE}
				{if $config.counter_collection_server}
					{if $sessioninfo.privilege.POS_BACKEND}
						<li class="sub-slide"><a class="sub-slide-menu__item" href="javascript:void(open_from_dc('{$config.counter_collection_server}/sales_live.php?',{$sessioninfo.id},{$sessioninfo.branch_id}, 'Sales Live'))">{*<img src="/ui/icons/chart_curve.png" align=absmiddle border=0>&nbsp;*} Sales Live</a></li>
						<li class="sub-slide"><a class="sub-slide-menu__item" href="javascript:void(open_from_dc('{$config.counter_collection_server}/pos_live.php?',{$sessioninfo.id},{$sessioninfo.branch_id}, 'POS Live'))">{*<img src="/ui/icons/chart_curve.png" align=absmiddle border=0>&nbsp;*} Pos Live</a></li>
					{/if}
					{if ($sessioninfo.privilege.POS_BACKEND or $sessioninfo.privilege.CC_FINALIZE or $sessioninfo.privilege.CC_UNFINALIZE)}
						<li class="sub-slide"><a  class="sub-slide-menu__item" href="javascript:void(open_cc('{$config.counter_collection_server}',{$sessioninfo.id},{$sessioninfo.branch_id}))">Counter Collection</a></li>
					{/if}
				{else}
					{if $sessioninfo.privilege.POS_BACKEND}
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="pos_live.php"><span class="sub-side-menu__label">{*<img src="/ui/icons/chart_curve.png" align=absmiddle border=0>&nbsp;*}POS live</span></a>
						
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_monitoring.php")}
						<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="pos_monitoring.php"><span class="sub-side-menu__label">POS Monitoring (DEV)</span></a>
							
						{/if}
						<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="sales_live.php"><span class="sub-side-menu__label">{*<img src="/ui/icons/chart_curve.png" align=absmiddle border=0>&nbsp;*}Sales live</span></a>

					{/if}
					{if ($sessioninfo.privilege.POS_BACKEND or $sessioninfo.privilege.CC_FINALIZE or $sessioninfo.privilege.CC_UNFINALIZE)}
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="counter_collection.php"><span class="sub-side-menu__label">Counter Collection</span></a>	
					
					{/if}
				{/if}
			<!--li><a href="collection_report.php">Collection Report</a></li-->
			{/if}
			{*if ($sessioninfo.privilege.POS_BACKEND or $sessioninfo.privilege.CC_FINALIZE or $sessioninfo.privilege.CC_UNFINALIZE or $sessioninfo.privilege.POS_VERIFY_SKU)}		
				{if file_exists('pos.invalid_sku.php')}
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="pos.invalid_sku.php"><span class="sub-side-menu__label">Invalid SKU Sold</span></a>		
			
				{/if}
			{/if*}
			
			<!-- Deposit -->
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.deposit_cancellation.php") and $sessioninfo.privilege.CC_DEPOSIT}
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Deposit</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.deposit_listing.php") and $sessioninfo.privilege.CC_DEPOSIT}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="pos.deposit_listing.php">Deposit Listing</a></li>
						{/if}
						{* if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.deposit_cancellation.php") and $sessioninfo.privilege.CC_DEPOSIT}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="pos.deposit_cancellation.php">Deposit Cancellation</a></li>
						{/if *}
					</ul>
				</li>
			{/if}
			
			<!-- Invalid SKU -->
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.invalid_sku.php") and $sessioninfo.privilege.POS_VERIFY_SKU}
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Invalid SKU</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.invalid_sku.php") and $sessioninfo.privilege.POS_VERIFY_SKU}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="pos.invalid_sku.php">Verify Invalid SKU</a></li>
						{/if}
					</ul>
				</li>
			{/if}
			
			<!-- Trade In -->
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.trade_in.write_off.php") and $sessioninfo.privilege.POS_TRADE_IN_WRITEOFF}
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Trade in</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.trade_in.write_off.php") and $sessioninfo.privilege.POS_TRADE_IN_WRITEOFF}
							<li class="sub-slide-sub"><a class="sub-slide-item" href="pos.trade_in.write_off.php">Manage Trade In Write-Off</a></li>
						{/if}
					</ul>
				</li>
			{/if}
					
			{if $sessioninfo.privilege.POS_REPORT}
			<li class="sub-slide"><a href="#" class="sub-side-menu__item" data-toggle="sub-slide">POS Report<i class="sub-angle fe fe-chevron-down"></i></a>
				<ul class="sub-slide-menu">
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">Transaction</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu-sub">
							<li><a class="sub-slide-item" href="pos_report.tran_details.php">Transaction Details</a></li>
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.tran_details_item_listing.php")}
								<li><a class="sub-slide-item" href="pos_report.tran_details_item_listing.php">Transaction Details with Item Listing</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.sku_tran_details.php")}
								<li><a class="sub-slide-item" href="pos_report.sku_tran_details.php">SKU Transaction Details</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.return_item.php")}
								<li><a class="sub-slide-item" href="pos_report.return_item.php">POS Return Items Report</a></li>
							{/if}
						</ul>
					</li>
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">Cashier</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu-sub">
							<li><a class="sub-slide-item"  href="pos_report.cashier_performance.php">Cashier Performance Report</a></li>
							<li><a class="sub-slide-item"  href="pos_report.cashier_unnormal_behaviour.php">Cashier Abnormal Behaviour Report</a></li>
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.cashier_variance.php")}
								<li><a class="sub-slide-item"  href="pos_report.cashier_variance.php">Cashier Variance Report</a></li>
							{/if}
							{* if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.abnormal_clocking_log.php")}
								<li><a class="sub-slide-item"  href="pos_report.abnormal_clocking_log.php">Abnormal Clocking Log</a></li>
							{/if *}
						</ul>
					</li>
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">Counter collection</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu-sub">
							<li><a class="sub-slide-item" href="pos_report.counter_collection_below_cost.php">Counter Collection below Cost Report</a></li>
							<li><a class="sub-slide-item" href="report.counter_collection_sales_vs_category_sales.php">Counter Collection Sales vs Category Sales</a></li>
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.daily_counter_collection.php")}
								<li><a class="sub-slide-item" href="report.daily_counter_collection.php">Daily Counter Collection Cash Denomination</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.payment_list.php")}
								<li><a class="sub-slide-item" href="pos_report.payment_list.php">Payment List Report</a></li>
							{/if}
							
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.counter_collection_details.php")}
								<li><a class="sub-slide-item" href="pos_report.counter_collection_details.php">Counter Collection Details Report</a></li>
							{/if}
							
							{if $config.counter_collection_enable_co2_module and file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.counter_collection_co2.php")}
								<li><a class="sub-slide-item" href="pos_report.counter_collection_co2.php">Counter Collection CO2</a></li>
							{/if}
	
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.cash_advance.php")}
								<li><a class="sub-slide-item" href="pos_report.cash_advance.php">Cash Advance Report</a></li>
							{/if}
						</ul>
					</li>
					
					{* if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.temp_price_report.php")}
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">Temp price</span></a>
							<ul class="sub-slide-menu-sub">
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.temp_price_report.php")}
									<li><a class="sub-slide-item" href="pos_report.temp_price_report.php">Temp Price Report</a></li>
								{/if}
							</ul>
						</li>
					{/if *}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.cross_branch_deposit.php") or file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.cancel_deposit.php") or file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.in_out_deposit.php")}
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">Deposit</span><i class="sub-angle fe fe-chevron-down"></i></a>
							<ul class="sub-slide-menu-sub">
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.cross_branch_deposit.php")}
									<li><a class="sub-slide-item" href="pos_report.cross_branch_deposit.php">Cross Branch Deposit Report</a></li>
								{/if}
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.cancel_deposit.php")}
									<li><a class="sub-slide-item" href="pos_report.cancel_deposit.php">Cancelled Deposit Report</a></li>
								{/if}
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.in_out_deposit.php")}
									<li><a class="sub-slide-item" href="pos_report.in_out_deposit.php">Daily Deposit In/Out Report</a></li>
								{/if}
							</ul>
						</li>
					{/if}
					
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.trade_in.php")}
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">Trade In</span><i class="sub-angle fe fe-chevron-down"></i></a>
							<ul class="sub-slide-menu-sub">
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.trade_in.php")}
									<li><a class="sub-slide-item" href="pos_report.trade_in.php">Trade In Report</a></li>
								{/if}
							</ul>
						</li>
					{/if}
					
					{if $config.enable_gst}
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">GST</span></a>
							<ul class="sub-slide-menu-sub">
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.counter_sales_gst_report.php")}
									<li class="sub-slide-item"><a href="pos_report.counter_sales_gst_report.php">Counter Sales GST Report</a></li>
								{/if}
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.receipt_summary_gst_report.php")}
									<li class="sub-slide-item"><a href="pos_report.receipt_summary_gst_report.php">Receipt Summary GST Report</a></li>
								{/if}
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.gst_credit_note_report.php")}
									<li class="sub-slide-item"><a href="pos_report.gst_credit_note_report.php">GST Credit Note Report</a></li>
								{/if}
								{* if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.sku_gst_report.php")}
									<li class="sub-slide-item"><a href="pos_report.sku_gst_report.php">SKU GST Report</a></li>
								{/if *}
							</ul>
						</li>
					{/if}
					
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">Service charge</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu-sub">
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.service_charge_summary.php")}
								<li><a class="sub-slide-item" href="pos_report.service_charge_summary.php">Service Charge Summary</a></li>
							{/if}
						</ul>
					</li>
				</ul>
			</li>
			{/if}
			{if $sessioninfo.privilege.FRONTEND_PRINT_FULL_TAX_INVOICE and file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.print_full_tax_invoice.php")}
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="pos.print_full_tax_invoice.php"><span class="sub-side-menu__label">Print Full Tax Invoice</span></a> 
			
			{/if}
			{if $sessioninfo.privilege.FRONTEND_EJOURNAL and file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.ejournal.php")}
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="pos.ejournal.php"><span class="sub-side-menu__label">E-Journal</span></a> 	
	
			{/if}
			{if $sessioninfo.privilege.FRONTEND_AUDIT_LOG and file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.audit_log.php")}
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="pos.audit_log.php"><span class="sub-side-menu__label">Audit Log</span></a> 		
			
			{/if}
			{if $sessioninfo.privilege.FRONTEND_ANNOUNCEMENT and file_exists("`$smarty.server.DOCUMENT_ROOT`/front_end.announcement.php")}
			<li class="sub-slide">
				<a class="sub-side-menu__item" data-toggle="sub-slide" href="front_end.announcement.php"><span class="sub-side-menu__label">POS Announcement</span></a> 		
		
			{/if}
			{if $sessioninfo.privilege.FRONTEND_POPULAR_ITEMS_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.popular_items_listing_setup.php")}
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="pos.popular_items_listing_setup.php"><span class="sub-side-menu__label">Popular Items Listing Setup</span></a> 		

			{/if}
		</ul>
		</li>
		{/if}
	
		{if $config.enable_suite_device and ($sessioninfo.privilege.SUITE_MANAGE_DEVICE)}
			<li class="slide">
				<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-monitor side-menu__icon"></i><span class="side-menu__label">Suite</span><i class="angle fe fe-chevron-down"></i></a>
				<ul class="slide-menu">
						{if $sessioninfo.privilege.SUITE_MANAGE_DEVICE and file_exists("`$smarty.server.DOCUMENT_ROOT`/suite.manage_device.php")}
						<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Device</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu">
										<li class="sub-slide-sub"><a class="sub-slide-item" href="suite.manage_device.php">Suite Device Setup</a></li>
									</ul>
								</li>
							{/if}
							{if $sessioninfo.privilege.SUITE_POS_DEVICE_MANAGEMENT and file_exists("`$smarty.server.DOCUMENT_ROOT`/suite.pos_device_management.php")}
							<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="suite.pos_device_management.php">POS Device Management</a></li>
							{/if}
						
					
				</ul>
			</li>
		{/if}
		
		{if $config.arms_marketplace_settings and ($config.arms_marketplace_settings.branch_code eq $BRANCH_CODE || $BRANCH_CODE eq 'HQ') and ($sessioninfo.privilege.MARKETPLACE_MANAGE_SKU or $sessioninfo.privilege.MARKETPLACE_SETTINGS or ($config.arms_marketplace_settings.branch_code eq $BRANCH_CODE and $sessioninfo.privilege.MARKETPLACE_LOGIN))}
			<li class="slide">
				<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-cash-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Marketplace</span><i class="angle fe fe-chevron-down"></i></a>
				<ul class="slide-menu">
					{* if file_exists("`$smarty.server.DOCUMENT_ROOT`/marketplace.home.php") and ($config.arms_marketplace_settings.branch_code eq $BRANCH_CODE or $BRANCH_CODE eq 'HQ') and $sessioninfo.privilege.MARKETPLACE_LOGIN}
						<li class="slide-item"><a href="marketplace.home.php?a=goto_marketplace" target="_blank">Go to Marketplace</a></li>
					{/if *}
					
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/marketplace.settings.php") and $sessioninfo.privilege.MARKETPLACE_SETTINGS}
						<li class="slide-item"><a href="marketplace.settings.php">Settings</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/marketplace.manage_sku.php") and $sessioninfo.privilege.MARKETPLACE_MANAGE_SKU}
						<li class="slide-item"><a  href="marketplace.manage_sku.php">Manage SKU</a></li>
					{/if}
				</ul>
			</li>
		{/if}
		
		
		{if $sessioninfo.privilege.MKT}
		<li class="slide">
			<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-cash-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Marketing Tools</span><i class="angle fe fe-chevron-down"></i></a>
			<ul class="slide-menu">
				<li class="slide-item"><a  href="mkt_annual.php"> Annual Planner and Review</a>
				{if $BRANCH_CODE eq 'HQ'}
				<li class="slide-item"><a href="mkt_settings.php">&nbsp; Settings</a>
				<li class="slide-item"><a href="mkt0.php">&nbsp; Create New Offers</a>
				{/if}
				<li class="slide-item"><a href="mkt_review_keyin.php">&nbsp; Daily Sales Keyin</a>
				<li class="slide-item"><a  href="mkt1.php"><sup>1</sup> Branch Sales Target and Expenses</a>
				<li class="slide-item"><a  href="mkt2.php"><sup>2</sup> Department Target Review</a>
				<li class="slide-item"><a  href="mkt3.php"><sup>3</sup> Brand/Item Proposal (by Branch)</a>
				<li class="slide-item"><a  href="mkt4.php"><sup>4</sup> Brand/Item Planner (by HQ)</a>
				<li class="slide-item"><a  href="mkt5.php"><sup>5</sup> Publishing Planner (by HQ)</a>
				<!--li><a href="mkt_status.php"><sup>5.2</sup> Offer Publishing Planner (by HQ)</a-->
				<li class="slide-item"><a  href="mkt6.php"><sup>6</sup> A&amp;P Materials Review</a>
			</ul>
		</li>
		{/if}
		
		{if $config.enable_web_bridge and $sessioninfo.privilege.WB}
			<li class="slide">
				<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-cash-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Web Bridge</span><i class="angle fe fe-chevron-down"></i></a>
				<ul class="slide-menu">
					{if ($sessioninfo.privilege.WB_AP_TRANS_SETT and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ap_trans.settings.php")) or ($sessioninfo.privilege.WB_AP_TRANS and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ap_trans.php"))}
						<li class="sub-slide">
							<a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">AP Trans</span><i class="sub-angle fe fe-chevron-down"></i></a>
						
							<ul class="sub-slide-menu">
								{if $sessioninfo.privilege.WB_AP_TRANS_SETT and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ap_trans.settings.php")}
									<li class="sub-slide-sub"><a class="sub-slide-item" href="web_bridge.ap_trans.settings.php">Settings</a></li>
								{/if}
								{if $sessioninfo.privilege.WB_AP_TRANS and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ap_trans.php")}
									<li class="sub-slide-sub"><a class="sub-slide-item" href="web_bridge.ap_trans.php">AP Trans</a></li>
								{/if}
							</ul>
						</li>
					{/if}
					{if ($sessioninfo.privilege.WB_AR_TRANS_SETT and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ar_trans.settings.php")) or ($sessioninfo.privilege.WB_AR_TRANS and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ar_trans.php"))}
						<li class="sub-slide">
							<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">AR Trans</span><i class="sub-angle fe fe-chevron-down"></i></a>
							<ul class="sub-slide-menu">
								{if $sessioninfo.privilege.WB_AR_TRANS_SETT and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ar_trans.settings.php")}
									<li class="sub-slide-sub"><a class="sub-slide-item" href="web_bridge.ar_trans.settings.php">Settings</a></li>
								{/if}				
								{if $sessioninfo.privilege.WB_AR_TRANS and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ar_trans.php")}
									<li class="sub-slide-sub"><a class="sub-slide-item" href="web_bridge.ar_trans.php">AR Trans</a></li>
								{/if}
							</ul>
						</li>
					{/if}
					{if ($sessioninfo.privilege.WB_CC_TRANS_SETT and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.cc_trans.settings.php")) or ($sessioninfo.privilege.WB_CC_TRANS and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.cc_trans.php"))}
						<li class="sub-slide">
							<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">CC Trans</span><i class="sub-angle fe fe-chevron-down"></i></a>
							<ul class="sub-slide-menu">
								{if $sessioninfo.privilege.WB_CC_TRANS_SETT and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.cc_trans.settings.php")}
									<li class="sub-slide-sub"><a class="sub-slide-item" href="web_bridge.cc_trans.settings.php">Settings</a></li>
								{/if}
								{if $sessioninfo.privilege.WB_CC_TRANS and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.cc_trans.php")}
									<li class="sub-slide-sub"><a class="sub-slide-item" href="web_bridge.cc_trans.php">CC Trans</a></li>
								{/if}
							</ul>
						</li>
					{/if}
				</ul>
			</li>
		{/if}

			<!--Front end files ends here-->



			<!--Miscellaneous files starts here-->

				<li class="slide">
					<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-cash-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Miscellaneous</span><i class="angle fe fe-chevron-down"></i></a>
					
						
						<ul class="slide-menu">
								{if $sessioninfo.level>0}
								{if $sessioninfo.privilege.UPDATE_PROFILE}
								<li class="sub-slide"><a class="sub-side-menu__item" href="my_profile.php">Update My Profile</a>{/if}
								{if $sessioninfo.privilege.VIEWLOG}
								<li class="sub-slide"><a class="sub-side-menu__item" href="viewlog.php">View Logs</a>{/if}
								{if $sessioninfo.level >=1000 && (!$config.single_server_mode || $config.show_server_status)}
								<li class="sub-slide"><a class="sub-side-menu__item" href="server_status.php">Server Status</a>{/if}
								{/if}
								<li class="sub-slide"><a class="sub-side-menu__item" href="/login.php?logout=1" onclick="return confirm('{$LANG.CONFIRM_LOGOUT}')">Logout</a>
								{if $sessioninfo.level>=9999}
									{if $smarty.session.admin_session}
									<li class="sub-slide"><a class="sub-side-menu__item" href="/login.php?logout_as=1">Logout as {$sessioninfo.u}</a>
									{else}
									<li class="sub-slide"><a class="sub-side-menu__item" href="#" onclick="return login_as();">Login as...</a>
									{/if}
								{/if}
								<li class="sub-slide"><a class="sub-side-menu__item" href="/front_end.check_code.php" target=_fe>Check Code</a>
								<li class="sub-slide"><a class="sub-side-menu__item" href="/price_check" target=_fe>Price Checker</a>
								{*
								{if file_exists('PO_COST.DAT')}
								<li class="sub-slide"><a class="sub-side-menu__item" href="/misc.po_cost.php" target=_pc>Multics PO Cost</a>
								{/if}
								*}
								{if is_dir('db') and $sessioninfo.level>=9999}
									{*<li class="sub-slide"><a class="sub-side-menu__item" href="/misc.pos_db.php">POS db</a>*}
								{/if}
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/eform.php")}
									<li class="sub-slide"><a class="sub-side-menu__item" href="#" class="submenu">eForm</a>          
									  <ul class="sub-slide-menu">
										{if $sessioninfo.privilege.E_FORM_SETUP}<li><a href="/eform.setup.php">Setup eForm</a></li>{/if}
										<li class="sub-slide-sub"><a class="sub-slide-item" href="/eform.php">My eForms</a></li>
										{if $sessioninfo.privilege.E_FORM_APPROVAL}
										<li class="sub-slide-sub"><a class="sub-slide-item" href="/eform.approval.php">eForm Approval</a></li>
										{/if}
									  </ul>          
									</li>
								{/if}
								<li class="sub-slide"><a class="sub-side-menu__item" href="./ui/3of9/mrvcode39extma.ttf">Download Barcode Font</a>
						</ul>
					</li>
				

			<!--Miscellaneous files ends here-->

{/if}
<!-- Session info if ends here -->

				</ul>
			</div>
		</aside>
		<!-- main-sidebar -->

		<!-- main-content -->
		<div class="main-content app-content">

			<!-- main-header -->
			<div class="main-header sticky side-header nav nav-item">
				<div class="container-fluid">
					<div class="main-header-left ">
						<div class="responsive-logo">
							<a href="index.html"><img src="../../assets/img/brand/logo.png" class="logo-1" alt="logo"></a>
							<a href="index.html"><img src="../../assets/img/brand/logo-white.png" class="dark-logo-1" alt="logo"></a>
							<a href="index.html"><img src="../../assets/img/brand/favicon.png" class="logo-2" alt="logo"></a>
							<a href="index.html"><img src="../../assets/img/brand/favicon-white.png" class="dark-logo-2" alt="logo"></a>
						</div>
						<div class="app-sidebar__toggle" data-toggle="sidebar">
							<a class="open-toggle" href="#"><i class="header-icon fe fe-align-left" ></i></a>
							<a class="close-toggle" href="#"><i class="header-icons fe fe-x"></i></a>
						</div>
						<div class="main-header-center ml-3 d-sm-none d-md-none d-lg-block">
							<h5 class="text-dark my-auto">
								{if strpos($smarty.server.SERVER_NAME, 'arms-go') !==false}
									ARMS&reg; GO Retail Management System &amp; Point Of Sale
								{elseif $config.consignment_modules}
									ARMS&reg; Consignment Retail Management System &amp; Point Of Sale
								{else}
									{#SYSTEM_ID#}
								{/if}
							</h5>
						</div>
					</div>
					<div class="main-header-right">
						<ul class="nav">
							<li >
								<div class="dropdown  nav-item d-none d-md-flex">
									<div class="d-sm-none d-md-block">
										{if $sessioninfo}
											Logged in as 
											{if $smarty.session.admin_session}
												{$smarty.session.admin_session.u} (now running as {$sessioninfo.u} |)
											{else}
												{$sessioninfo.u}
											{/if}
										{elseif $vp_session}
											Logged in as {$vp_session.description}
										{elseif $dp_session}
											Logged in as {$dp_session.description}
										{elseif $sa_session}
											Logged in as {$sa_session.name}
										{/if}
									</div>
								</div>
							</li>
						</ul>
						<div class="nav nav-item  navbar-nav-right ml-auto">
							<div class="dropdown main-profile-menu nav nav-item nav-link">
								<a class="profile-user d-flex" href="#"><img alt="" src="../../assets/img/faces/6.jpg"></a>
								<div class="dropdown-menu">
									<div class="main-header-profile bg-primary p-3">
										<div class="d-flex wd-100p">
											<div class="main-img-user"><img alt="" src="../../assets/img/faces/6.jpg" ></div>
											<div class="ml-3 my-auto">
												<h6>Petey Cruiser</h6><span>Premium Member</span>
											</div>
										</div>
									</div>
									<a class="dropdown-item" href=""><i class="bx bx-user-circle"></i>Profile</a>
									<a class="dropdown-item" href=""><i class="bx bx-cog"></i> Edit Profile</a>
									<a class="dropdown-item" href=""><i class="bx bxs-inbox"></i>Inbox</a>
									<a class="dropdown-item" href=""><i class="bx bx-envelope"></i>Messages</a>
									<a class="dropdown-item" href=""><i class="bx bx-slider-alt"></i> Account Settings</a>
									<a class="dropdown-item" href="page-signin.html"><i class="bx bx-log-out"></i> Sign Out</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /main-header -->
			
			<!-- container -->
			<div class="container-fluid">

				<div class="modal p" id="goto_branch_popup">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content tx-size-sm">
							<div class="modal-body tx-center ">
								<button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button> 
								
								<div class="px-5 py-5">	
									<h3 class="text-primary mt-1 text-md mb-2">Select Branch to login</h3>
									<span id="goto_branch_list" ></span> 
									<button onclick="goto_branch_select()" class="btn btn-primary btn-block mt-3">Login</button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- Here Wil Be the Main Pgae starts  -->
				


			</div>