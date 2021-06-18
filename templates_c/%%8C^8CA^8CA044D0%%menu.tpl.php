<?php /* Smarty version 2.6.18, created on 2021-06-18 21:11:00
         compiled from menu.tpl */ ?>
<!-- <div id=goto_branch_popup class="curtain_popup" style="width:300px;height:100px;display:none;">
	<div style="text-align:right"><img src=/ui/closewin.png onclick="default_curtain_clicked()"></div>
	<h3>Select Branch to login</h3>
	<span id=goto_branch_list></span> <button onclick="goto_branch_select()">Login</button>
</div> -->

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
					<h4 class="font-weight-semibold mb-0"><?php if (! $this->_tpl_vars['sa_session']): ?><?php echo $this->_tpl_vars['BRANCH_CODE']; ?>
<?php endif; ?></h4>
					<span class="mb-0 text-muted">
						<?php if ($this->_tpl_vars['sessioninfo']): ?>
							Logged in as 
							<?php if ($_SESSION['admin_session']): ?>
								<?php echo $_SESSION['admin_session']['u']; ?>
</b> (now running as <b><?php echo $this->_tpl_vars['sessioninfo']['u']; ?>
</b> |)
							<?php else: ?>
								<?php echo $this->_tpl_vars['sessioninfo']['u']; ?>

							<?php endif; ?>
						<?php elseif ($this->_tpl_vars['vp_session']): ?>
							Logged in as <?php echo $this->_tpl_vars['vp_session']['description']; ?>

						<?php elseif ($this->_tpl_vars['dp_session']): ?>
							Logged in as <?php echo $this->_tpl_vars['dp_session']['description']; ?>

						<?php elseif ($this->_tpl_vars['sa_session']): ?>
							Logged in as <?php echo $this->_tpl_vars['sa_session']['name']; ?>

						<?php endif; ?>
					</span>
				</div>
			</div>
		</div>
<ul class="side-menu">
<?php if ($this->_tpl_vars['sessioninfo']): ?>
	<li class="slide">
		<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-home side-menu__icon"></i><span class="side-menu__label">Home</span><i class="angle fe fe-chevron-down"></i></a>
		<ul class="slide-menu">
			<li><a class="sub-slide-item" href="home.php" >Dashboard</a></li>
			<li><a class="sub-slide-item" href="product-details.html" >Go To Branch</a></li>
			<li><a class="sub-slide-item" href="/login.php?logout=1" onclick="return confirm('<?php echo $this->_tpl_vars['LANG']['CONFIRM_LOGOUT']; ?>
')">Logout</a></li>
		</ul>
	</li>
	<!-- Administrator -->
	<?php if ($this->_tpl_vars['sessioninfo']['privilege']['USERS_ADD'] || $this->_tpl_vars['sessioninfo']['privilege']['USERS_MNG'] || $this->_tpl_vars['sessioninfo']['privilege']['USERS_ACTIVATE'] || $this->_tpl_vars['sessioninfo']['privilege']['MST_APPROVAL'] || $this->_tpl_vars['sessioninfo']['privilege']['POS_IMPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['SKU_EXPORT'] || $this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
	<li class="slide">
		<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-account side-menu__icon"></i><span class="side-menu__label">Administrator</span><i class="angle fe fe-chevron-down"></i></a>
		<ul class="slide-menu">
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['USERS_MNG'] || $this->_tpl_vars['sessioninfo']['privilege']['USERS_ACTIVATE']): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Users</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						<?php if ($this->_tpl_vars['sessioninfo']['privilege']['USERS_ADD']): ?><li><a class="sub-slide-item" href="users.php?t=create" >Create Profile</a></li><?php endif; ?>
						<?php if ($this->_tpl_vars['sessioninfo']['privilege']['USERS_ACTIVATE']): ?><li><a class="sub-slide-item" href="users.php?t=update" >Update Profile</a></li><?php endif; ?>
						<?php if ($this->_tpl_vars['sessioninfo']['level'] == 500 || $this->_tpl_vars['sessioninfo']['level'] >= 9999): ?><li><a class="sub-slide-item" href="admin.inactive_user.php" >No-Activity User Report</a></li><?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/users.application.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['USERS_EFORM']): ?>
						<li class="sub-slide-sub">
							<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">User Application E-Form</span><i class="sub-angle fe fe-chevron-down"></i></a>
							<ul class="sub-slide-menu-sub">
								<?php if ($this->_tpl_vars['config']['single_server_mode'] || ( ! $this->_tpl_vars['config']['single_server_mode'] && $this->_tpl_vars['sessioninfo']['branch_id'] == 1 )): ?><li><a class="sub-slide-item"  href="users.application.php?a=generate_code" >Generate QR Code</a></li><?php endif; ?>
								<li><a class="sub-slide-item" href="users.application.php?a=application_list" >Application List</a></li>
							</ul>
						</li>
						<?php endif; ?>
					</ul>
				</li>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_APPROVAL']): ?><li><a href="approval_flow.php" class="slide-item">Approval Flows</a></li><?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999 && $this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Selling Price</span><i class="sub-angle fe fe-chevron-down"></i></a>
				<ul class="sub-slide-menu">
					<li><a class="sub-slide-item" href="admin.copy_selling.php" >Copy Selling Price</a></li>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_selling.php" )): ?>
					<li><a class="sub-slide-item" href="admin.import_selling.php" >Import Selling Price</a></li>
					<?php endif; ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.update_price_type.php" )): ?>
					<li><a class="sub-slide-item" href="admin.update_price_type.php" >Update Price Type</a></li>
					<?php endif; ?>
				</ul>
			</li>			
			<?php endif; ?>
			<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && $this->_tpl_vars['sessioninfo']['level'] >= 9999 && ( $this->_tpl_vars['sessioninfo']['privilege']['ADMIN_UPDATE_SKU_MASTER_COST'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.update_sku_master_cost.php" ) )): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Cost Price</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						<li><a class="sub-slide-item" href="admin.update_sku_master_cost.php" >Update SKU Master Cost</a></li>
					</ul>
				</li>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999 && $this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>
				<li><a href="admin.sku_block.php" class="slide-item">Block/Ublock in SKU in PO (CSV)</a></li>
			<?php endif; ?>

			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['SKU_EXPORT'] || $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['POS_IMPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_SKU'] || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_VENDOR'] || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_BRAND'] || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_DEBTOR'] || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_UOM'] || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_DEACTIVATE_SKU']): ?>
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Import / Export</span><i class="sub-angle fe fe-chevron-down"></i></a>
				<ul class="sub-slide-menu">
				<?php if ($this->_tpl_vars['sessioninfo']['privilege']['SKU_EXPORT']): ?>
					<li><a class="sub-slide-item"  href="admin.sku_export.php">Export SKU Items</a></li>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.weightcode_export.php" ) && ! $this->_tpl_vars['config']['consignment_modules']): ?>
						<li><a class="sub-slide-item"  href="admin.weightcode_export.php">Export Weighing Scale Items</a></li>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['POS_IMPORT']): ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.export_points.php" )): ?><li><a class="sub-slide-item"  href="admin.export_points.php">Export Member Points</a></li><?php endif; ?>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['POS_IMPORT']): ?>
										<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_pos_sales.php" )): ?>
					    					<?php endif; ?>
		  			<li><a class="sub-slide-item"  href="admin.stockchk_import.php">Import Stock Take</a></li>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['config']['sku_application_require_multics'] && ( $this->_tpl_vars['sess']['level'] == 500 || $this->_tpl_vars['sessioninfo']['level'] >= 9999 )): ?>
				<li><a class="sub-slide-item"  href="admin.update_dat.php">Update Multics DAT files</a></li>
				<?php endif; ?>
	            <?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['POS_IMPORT']): ?>
	                <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_member_points.php" )): ?>
	                    <li><a class="sub-slide-item"  href="admin.import_member_points.php">Import Member Points</a></li>
	                <?php endif; ?>
	                <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_members.php" )): ?>
	                    <li><a class="sub-slide-item"  href="admin.import_members.php">Import Members</a></li>
	                <?php endif; ?>
	                <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.preactivate_member_cards.php" )): ?>
	                    <li><a class="sub-slide-item"  href="admin.preactivate_member_cards.php">Pre-activate Member Cards</a></li>
	                <?php endif; ?>
	            <?php endif; ?>
				
				<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && ( $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_SKU'] )): ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_sku.php" )): ?>
	                    <li><a class="sub-slide-item"  href="admin.import_sku.php">Import SKU</a></li>
	                <?php endif; ?>
				<?php endif; ?>
				
				<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && ( $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_VENDOR'] )): ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_vendor.php" )): ?>
	                    <li><a class="sub-slide-item"  href="admin.import_vendor.php">Import Vendor</a></li>
	                <?php endif; ?>
				<?php endif; ?>
				
				<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && ( $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_BRAND'] )): ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_brand.php" )): ?>
	                    <li><a class="sub-slide-item"  href="admin.import_brand.php">Import Brand</a></li>
	                <?php endif; ?>
				<?php endif; ?>
	            
	            <?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && ( $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_DEBTOR'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_debtor.php" )): ?>
					<li><a class="sub-slide-item"  href="admin.import_debtor.php">Import Debtor</a></li>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && ( $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_UOM'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_uom.php" )): ?>
	                 <li><a class="sub-slide-item"  href="admin.import_uom.php">Import UOM</a></li>
	            <?php endif; ?>
				<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && ( $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_DEACTIVATE_SKU'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.deactivate_sku.php" )): ?>
	                 <li><a class="sub-slide-item"  href="admin.deactivate_sku.php">Deactivate SKU by CSV</a></li>
	            <?php endif; ?>
				</ul>
			</li>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999 && $this->_tpl_vars['BRANCH_CODE'] == 'HQ' && $this->_tpl_vars['config']['show_tracker']): ?>
			    <li><a class="slide-item" href="admin.arms_tracker.php">ARMS Request Tracker</a></li>
			<?php endif; ?>
			<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.monthly_closing.php" ) && $this->_tpl_vars['config']['monthly_closing'] && $this->_tpl_vars['sessioninfo']['privilege']['ADMIN_MONTHLY_CLOSING']): ?>
				<li  aria-haspopup="true" class="sub-menu-sub"><a>Monthly Closing</a>
					<ul class="sub-slide-menu">
						<li><a class="sub-slide-item"  href="admin.monthly_closing.php">Monthly Closing</a></li>
						<li><a class="sub-slide-item"  href="admin.monthly_closing.php?a=show_closed_month">Monthly Closing History</a></li>
					</ul>
				</li>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
				<li><a class="slide-item" href="admin.update_log.php">System Update log</a></li>
				<li><a class="slide-item" href="sales_target.php">Sales Target</a></li>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Settings</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.settings.php" )): ?>
						<li><a class="sub-slide-item"  href="admin.settings.php?file=color.txt">Edit Colour</a></li>
						<li><a class="sub-slide-item"  href="admin.settings.php?file=size.txt">Edit Size</a></li>
						<?php endif; ?>
												<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.upload_logo.php" )): ?>
						<li><a class="sub-slide-item"  href="admin.upload_logo.php">Edit Logo Settings</a></li>
						<?php endif; ?>
					</ul>
				</li>
								<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && $this->_tpl_vars['sessioninfo']['id'] == 1): ?>
				    <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Server Management</span><i class="sub-angle fe fe-chevron-down"></i></a>
				        <ul class="sub-slide-menu">
				            <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.config_manager.php" )): ?>
				                <li><a class="sub-slide-item"  href="admin.config_manager.php">Config Manager</a></li>
				            <?php endif; ?>
				            <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.privilege_manager.php" )): ?>
				                <li><a class="sub-slide-item"  href="admin.privilege_manager.php">Privilege Manager</a></li>
				            <?php endif; ?>
				            				        </ul>
					</li>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['config']['enable_gst'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_gst_settings.php" ) && $this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
					<li><a  href="masterfile_gst_settings.php">GST Settings</a></li>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['config']['enable_tax'] && $this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Tax</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.tax_settings.php" )): ?>
						<li><a class="sub-slide-item"  href="admin.tax_settings.php">Tax Settings</a></li>
					<?php endif; ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.tax_listing.php" )): ?>
						<li><a class="sub-slide-item"  href="admin.tax_listing.php">Tax Listing</a></li>
					<?php endif; ?>
					</ul>
				</li>
			<?php endif; ?>
						<?php if ($this->_tpl_vars['config']['foreign_currency'] && ( $this->_tpl_vars['sessioninfo']['privilege']['ADMIN_FOREIGN_CURRENCY_RATE_UPDATE'] )): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Foreign Currency</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ADMIN_FOREIGN_CURRENCY_RATE_UPDATE'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.foreign_currency.rate.php" )): ?>
							<li><a class="sub-slide-item"  href="admin.foreign_currency.rate.php">Currency Rate Table</a></li>
						<?php endif; ?>
					</ul>
				</li>
			<?php endif; ?>
					</ul>
	</li>	
	<?php endif; ?>
<!-- /Administrator -->

<!-- Office -->
    <?php if (! $this->_tpl_vars['config']['arms_go_modules'] || ( $this->_tpl_vars['config']['arms_go_modules'] && ( $this->_tpl_vars['config']['arms_go_enable_official_modules'] || ( ! $this->_tpl_vars['config']['arms_go_enable_official_modules'] && $this->_tpl_vars['BRANCH_CODE'] != 'HQ' ) ) )): ?>
        <?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_APPLY'] || $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_APPROVAL'] || $this->_tpl_vars['sessioninfo']['privilege']['PO'] || $this->_tpl_vars['sessioninfo']['privilege']['PO_REQUEST'] || $this->_tpl_vars['sessioninfo']['privilege']['PO_FROM_REQUEST'] || $this->_tpl_vars['sessioninfo']['privilege']['PO_REPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['GRN_APPROVAL'] || $this->_tpl_vars['sessioninfo']['privilege']['GRA'] || $this->_tpl_vars['sessioninfo']['privilege']['GRR_REPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['GRN_REPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['SHIFT_RECORD_VIEW'] || $this->_tpl_vars['sessioninfo']['privilege']['SHIFT_RECORD_EDIT'] || $this->_tpl_vars['sessioninfo']['privilege']['PAYMENT_VOUCHER'] || $this->_tpl_vars['sessioninfo']['privilege']['DO'] || $this->_tpl_vars['sessioninfo']['privilege']['ADJ'] || $this->_tpl_vars['sessioninfo']['privilege']['ACCOUNT_EXPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['OSTRIO_ACCOUNTING_STATUS'] || $this->_tpl_vars['sessioninfo']['privilege']['SPEED99_INTEGRATION_STATUS'] || $this->_tpl_vars['sessioninfo']['privilege']['KOMAISO_INTEGRATION_STATUS']): ?>
        <li class="slide">
			<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-briefcase side-menu__icon"></i><span class="side-menu__label">Office</span><i class="angle fe fe-chevron-down"></i></a>
	        <ul class="slide-menu">
	    
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['ADJ']): ?>
		            <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label"> Adjustment</span><i class="sub-angle fe fe-chevron-down"></i></a>
			            <ul class="sub-slide-menu">
			                <li><a class="sub-slide-item" href="/adjustment.php"> Adjustment</a>
			                <?php if ($this->_tpl_vars['sessioninfo']['privilege']['ADJ_APPROVAL']): ?>
			                    <li><a class="sub-slide-item"  href="/adjustment_approval.php">Adjustment Approval</a></li>
			                <?php endif; ?>
			                <li><a class="sub-slide-item"  href="/adjustment.summary.php"> Adjustment Summary</a></li>
			            </ul>
		        	</li>
	            <?php endif; ?>	
	    
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['SHIFT_RECORD_VIEW'] || $this->_tpl_vars['sessioninfo']['privilege']['SHIFT_RECORD_EDIT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/shift_record.php" )): ?>
	            	<li><a class="slide-item" href="/shift_record.php">Shift Record</a></li>
	            <?php endif; ?>
	            
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PAYMENT_VOUCHER']): ?>
		            <?php if (BRANCH_CODE != 'HQ'): ?>
		            	<li><a class="slide-item" href="/payment_voucher.php">Payment Voucher</a></li>
		            <?php else: ?>
		            	<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Payment Voucher</span><i class="sub-angle fe fe-chevron-down"></i></a>
				            <ul class="sub-slide-menu">
				                <li><a class="sub-slide-item"  href="/payment_voucher.php">Payment Voucher</a></li>
				                <li><a class="sub-slide-item"  href="/payment_voucher.log_sheet.php">Cheque Issue Log Sheet</a></li>
				            </ul>
				        </li>
		            <?php endif; ?>	    
	            <?php endif; ?>
	            
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_APPLY'] || $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_APPROVAL'] || $this->_tpl_vars['sessioninfo']['privilege']['SKU_REPORT']): ?>
		            <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">SKU</span><i class="sub-angle fe fe-chevron-down"></i></a>
			            <ul class="sub-slide-menu">
			            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_APPLY'] && $this->_tpl_vars['sessioninfo']['branch_type'] != 'franchise'): ?>
			                <li><a class="sub-slide-item"  href="masterfile_sku_application.php">SKU Application</a></li>
			                <li><a class="sub-slide-item"  href="masterfile_sku_application.php?a=revise_list">SKU Application Revise List</a></li>
			                
			                <?php if (! $this->_tpl_vars['config']['menu_hide_bom_application']): ?><li><a class="sub-slide-item"  href="masterfile_sku_application_bom.php">Create BOM SKU</a></li><?php endif; ?>
			            <?php endif; ?>
			            
			            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_APPLY'] || $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_APPROVAL']): ?><li><a class="sub-slide-item"  href="masterfile_sku_application.php?a=list">SKU Application Status</a></li><?php endif; ?>
			            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['SKU_REPORT']): ?>
			            <!--li><a href="sku.summary.php">SKU Summary (Testing)</a></li-->
			            <!--li><a href="sku.history.php">SKU History (Testing)</a></li-->
			            <?php endif; ?>
			            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_UPDATE_FUTURE_PRICE'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sku_items.future_price.php" )): ?>
			                <li><a class="sub-slide-item"  href="masterfile_sku_items.future_price.php">Batch Selling Price Change</a></li>
			            <?php endif; ?>
			            </ul>
			        </li>
	            <?php endif; ?>
				
								
					            <?php if ($this->_tpl_vars['config']['allow_sales_order'] && ( $this->_tpl_vars['sessioninfo']['privilege']['SO_EDIT'] || $this->_tpl_vars['sessioninfo']['privilege']['SO_APPROVAL'] || $this->_tpl_vars['sessioninfo']['privilege']['SO_REPORT'] )): ?>
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Sales Order</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        <?php if ($this->_tpl_vars['sessioninfo']['privilege']['SO_EDIT']): ?>
								<li><a class="sub-slide-item"  href="sales_order.php">Create / Edit Order</a></li>
	                        <?php endif; ?>
	                        <?php if ($this->_tpl_vars['sessioninfo']['privilege']['SO_APPROVAL']): ?>
	                            <li><a class="sub-slide-item"  href="sales_order_approval.php">Sales Order Approval</a></li>
	                        <?php endif; ?>
	                        <?php if ($this->_tpl_vars['sessioninfo']['privilege']['SO_REPORT']): ?>
								<li><a class="sub-slide-item"  href="report.spbt.php">Sales Order Report</a></li>
								<li><a class="sub-slide-item"  href="report.spbt_summary.php">Sales Order Summary Report</a></li>
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/sales_order.monitor_report.php" )): ?>
									<li><a class="sub-slide-item"  href="sales_order.monitor_report.php">Sales Order Monitor Report</a></li>
								<?php endif; ?>
							<?php endif; ?>
	                    </ul>
	                </li>
	            <?php endif; ?>
	            <!-- DO -->
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['DO']): ?>
		            <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">DO (Delivery Order)</span><i class="sub-angle fe fe-chevron-down"></i></a>
			            <ul class="sub-slide-menu">
			                			                <?php if ($this->_tpl_vars['sessioninfo']['branch_type'] != 'franchise'): ?>
			                    <li><a class="sub-slide-item"  href="do.php">Transfer DO</a></li>
			                <?php endif; ?>
			                <?php if ($this->_tpl_vars['config']['do_allow_cash_sales']): ?>
			                    <li><a class="sub-slide-item"  href="do.php?page=open">Cash Sales DO</a></li>
			                <?php endif; ?>
			                <?php if ($this->_tpl_vars['config']['do_allow_credit_sales']): ?>
			                    <li><a class="sub-slide-item"  href="do.php?page=credit_sales">Credit Sales DO</a></li>
			                <?php endif; ?>
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['DO_PREPARATION']): ?>
								<li class="sub-slide-sub">
									<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">DO Preparation</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu-sub">
										<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/do.simple.php" )): ?>
											<li><a class="sub-slide-item"  href="do.simple.php?do_type=transfer">Transfer DO</a></li>
											<li><a class="sub-slide-item"  href="do.simple.php?do_type=open">Cash Sales DO</a></li>
											<li><a class="sub-slide-item"  href="do.simple.php?do_type=credit_sales">Credit Sales DO</a></li>
										<?php endif; ?>
									</ul>
								</li>
							<?php endif; ?>
			                <?php if ($this->_tpl_vars['sessioninfo']['privilege']['DO_APPROVAL']): ?>
			                    <li><a class="sub-slide-item" href="do_approval.php">DO Approval</a></li>
			                <?php endif; ?>
			                <li><a class="sub-slide-item"  href="do.summary.php">DO Summary</a></li>
			                <li><a class="sub-slide-item"  href="report.do_summary.php">DO Summary By Day / Month</a></li>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.do_summary_by_items.php" )): ?>
							<li><a class="sub-slide-item"  href="report.do_summary_by_items.php">DO Summary By Items</a></li>
							<?php endif; ?>
			                <li><a class="slide-item" href="do.report.php">Transfer Report</a></li>
			                <?php if ($this->_tpl_vars['sessioninfo']['privilege']['DO_REQUEST']): ?>
			                    <li><a class="slide-item" href="do_request.php">DO Request</a></li>
			                    <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/do_request.rejected_report.php" )): ?>
			                        <li><a class="slide-item" href="do_request.rejected_report.php">DO Request Rejected Report</a></li>
			                    <?php endif; ?>
			                <?php endif; ?>
			                <?php if ($this->_tpl_vars['sessioninfo']['privilege']['DO_REQUEST_PROCESS']): ?>
			                    <li><a class="slide-item" href="do_request.process.php">Process DO Request</a></li>
			                <?php endif; ?>
			                							
							<?php if ($this->_tpl_vars['config']['enable_one_color_matrix_ibt'] && BRANCH_CODE == 'HQ' && file_exists ( 'do.matrix_ibt_process.php' )): ?>
								<li><a class="sub-slide-item" class="slide-item" href="do.matrix_ibt_process.php">Matrix IBT Process</a></li>
							<?php endif; ?>
			            </ul>
			        </li>
	            <?php endif; ?>
				
	    
	            <!-- PO -->
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO'] || $this->_tpl_vars['sessioninfo']['privilege']['PO_REQUEST'] || $this->_tpl_vars['sessioninfo']['privilege']['PO_FROM_REQUEST'] || $this->_tpl_vars['sessioninfo']['privilege']['PO_REPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['PO_REQUEST_APPROVAL']): ?>
		            <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">PO (Purchase Order)</span><i class="sub-angle fe fe-chevron-down"></i></a>
		                <ul class="sub-slide-menu">
		                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO'] || $this->_tpl_vars['sessioninfo']['privilege']['PO_VIEW_ONLY']): ?>
		                        <li><!--a href="purchase_order.php">Purchase Order</a-->
		                        <a class="sub-slide-item" href="po.php">Purchase Order</a></li>
		                    <?php endif; ?>
		                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO_APPROVAL']): ?>
		                        <li><a class="sub-slide-item"  href="po_approval.php">PO Approval</a></li>
		                    <?php endif; ?>
		                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO_FROM_REQUEST']): ?>
		                        <li><a class="sub-slide-item"  href="po_request.process.php">Create PO from Request</a></li>
		                    <?php endif; ?>
		                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO_REQUEST']): ?>
		                        <li><a class="sub-slide-item"  href="po_request.request.php">PO Request</a></li>
		                    <?php endif; ?>
		                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO_REQUEST_APPROVAL']): ?>
		                        <li><a class="sub-slide-item"  href="po_request.approval.php">PO Request Approval</a></li>
		                    <?php endif; ?>
		                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO_TICKET'] && $this->_tpl_vars['config']['po_allow_vendor_request']): ?>
		                        <li><a class="sub-slide-item"  href="vendor_po_request.php">Vendor PO Access</a></li>
		                    <?php endif; ?>
		                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO_REPORT']): ?><li><a class="sub-slide-item"  href="purchase_order.summary.php">PO Summary</a></li><?php endif; ?>
		                    <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/po_qty_performance.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['PO_REPORT']): ?>
		                        <li><a class="sub-slide-item"  href="po_qty_performance.php">PO Quantity Performance</a></li>
		                    <?php endif; ?>
		                    <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.stock_reorder.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['PO'] && $this->_tpl_vars['sessioninfo']['privilege']['PO_REPORT']): ?>
		                        <li><a class="sub-slide-item"  href="report.stock_reorder.php">Stock Reorder Report</a></li>
		                    <?php endif; ?>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/sku_purchase_history.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['PO'] && $this->_tpl_vars['sessioninfo']['privilege']['PO_REPORT']): ?>
		                        <li><a class="sub-slide-item"  href="sku_purchase_history.php">SKU Purchase History</a></li>
		                    <?php endif; ?>
		                </ul>
		            </li>
	            <?php endif; ?>
	            
	            <?php if ($this->_tpl_vars['config']['enable_po_agreement'] && $this->_tpl_vars['sessioninfo']['privilege']['PO_SETUP_AGREEMENT'] && $this->_tpl_vars['BRANCH_CODE'] == 'HQ' && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/po.po_agreement.setup.php" )): ?>
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Purchase Agreement</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        <li><a class="sub-slide-item"  href="po.po_agreement.setup.php">Add/Edit Purchase Agreement</a></li>
	                    </ul>
	                </li>
	            <?php endif; ?>
	    
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRN_APPROVAL'] && ! $this->_tpl_vars['config']['use_grn_future']): ?><li><a class="slide-item" href="goods_receiving_note_approval.account.php">GRN Account Verification</a><?php endif; ?>
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRA']): ?>
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">GRA (Goods Return Advice)</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        <li><a class="sub-slide-item"  href="goods_return_advice.php">GRA</a></li>
	                        <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRA_APPROVAL']): ?>
	                            <li><a class="sub-slide-item"  href="/goods_return_advice.approval.php">GRA Approval</a></li>
	                        <?php endif; ?>
	                    </ul>
	                </li>
	            <?php endif; ?>
	    
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRA_REPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['GRR_REPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['GRN_REPORT']): ?>
	            <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">GRR / GRN / GRA Reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
		            <ul class="sub-slide-menu">
		            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRR_REPORT']): ?>
		            <li><a class="sub-slide-item"  href="goods_receiving_record.report.php">GRR Report</a></li>
		            <li><a class="sub-slide-item"  href="goods_receiving_record.status.php">GRR Status Report</a></li>
		            <?php endif; ?>
		            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRN_REPORT']): ?>
		                <li><a class="sub-slide-item"  href="goods_receiving_note.summary.php">GRN Summary</a></li>
		                <li><a class="sub-slide-item"  href="goods_receiving_note.category_summary.php">GRN Summary by Category</a></li>
		                <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/goods_receiving_note.distribution_report.php" )): ?>
		                    <li><a class="sub-slide-item"  href="goods_receiving_note.distribution_report.php">GRN Distribution Report</a></li>
		                <?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sku_receiving_history.php" )): ?>
		                    <li><a class="sub-slide-item"  href="report.sku_receiving_history.php">SKU Receiving History</a></li>
						<?php endif; ?>
		            <?php endif; ?>
		            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRA_REPORT']): ?>
		                <li><a class="sub-slide-item"  href="goods_return_advice.listing_report.php">GRA Listing</a></li>
		                <li><a class="sub-slide-item"  href="goods_return_advice.summary_by_dept.php">GRA Summary by Department</a></li>
		                <li><a class="sub-slide-item"  href="goods_return_advice.summary_by_category.php">GRA Summary by Category</a></li>
		            <?php endif; ?>
		            <?php if ($this->_tpl_vars['config']['gra_enable_disposal'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.goods_return_advice.disposal.php" )): ?>
		                <li><a class="sub-slide-item"  href="report.goods_return_advice.disposal.php">GRA Disposal Report</a></li>
		            <?php endif; ?>
		            </ul>
		        </li>
	            <?php endif; ?>
	            
				<?php if ($this->_tpl_vars['sessioninfo']['privilege']['DN'] && ! $this->_tpl_vars['config']['consignment_modules'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/dnote.php" )): ?>
	              <li><a class="slide-item" href="dnote.php">Debit Note</a></li>
	            <?php endif; ?>
				
				<?php if ($this->_tpl_vars['sessioninfo']['privilege']['CN'] && ! $this->_tpl_vars['config']['consignment_modules'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/cnote.php" )): ?>
	              <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Credit Note</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                <ul class="sub-slide-menu">
	                    <li><a class="sub-slide-item"  href="cnote.php">Credit Note</a></li>
	                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['CN_APPROVAL']): ?>
	                        <li><a class="sub-slide-item"  href="/cnote.approval.php">Credit Note Approval</a></li>
	                    <?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/cnote.summary.php" )): ?>
							<li><a class="sub-slide-item"  href="cnote.summary.php">CN Summary</a></li>
						<?php endif; ?>
	                </ul>
	              </li>
	            <?php endif; ?>

	            	            <?php if ($this->_tpl_vars['config']['enable_vendor_portal'] && ( $this->_tpl_vars['sessioninfo']['privilege']['REPORTS_REPACKING'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.repacking.php" ) )): ?>
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Vendor Portal</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        <?php if ($this->_tpl_vars['sessioninfo']['privilege']['REPORTS_REPACKING'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.repacking.php" )): ?>
	                            <li><a class="sub-slide-item"  href="report.repacking.php">Repacking Report</a></li>
	                        <?php endif; ?>
	                    </ul>
	                </li>
	            <?php endif; ?>

	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['ACCOUNT_EXPORT']): ?>
	              <li><a class="slide-item" href="acc_export.php">Account & GAF Export</a></li>
	              <li><a class="slide-item" href="acc_export.php?a=setting">Account & GAF Export Setting</a></li>
	            <?php endif; ?>
				
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_AND_GST_SETTING'] || $this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_EXPORT_SETUP'] || $this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_EXPORT']): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Custom Accounting Export</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_AND_GST_SETTING'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/custom.acc_and_gst_setting.php" )): ?>
								<li><a class="sub-slide-item"  href="custom.acc_and_gst_setting.php">Custom Account & GST Setting</a></li>
							<?php endif; ?>
							
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_EXPORT_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/custom.setup_acc_export.php" )): ?>
								<li><a class="sub-slide-item"  href="custom.setup_acc_export.php">Setup Custom Accounting Export</a></li>
							<?php endif; ?>
							
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_EXPORT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/custom.acc_export.php" )): ?>
								<li><a class="sub-slide-item"  href="custom.acc_export.php">Custom Accounting Export</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
				
								<?php if ($this->_tpl_vars['config']['arms_accounting_api_setting'] && ( $this->_tpl_vars['sessioninfo']['privilege']['ARMS_ACCOUNTING_SETTING'] || $this->_tpl_vars['sessioninfo']['privilege']['ARMS_ACCOUNTING_STATUS'] )): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">ARMS Accounting Integration &nbsp;</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ARMS_ACCOUNTING_SETTING'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/arms_accounting.setting.php" )): ?>
								<li><a class="sub-slide-item"  href="arms_accounting.setting.php">Setting</a></li>
							<?php endif; ?>
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ARMS_ACCOUNTING_STATUS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/arms_accounting.status.php" )): ?>
								<li><a class="sub-slide-item"  href="arms_accounting.status.php">Integration Status</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
				
								<?php if ($this->_tpl_vars['config']['os_trio_settings'] && ( $this->_tpl_vars['sessioninfo']['privilege']['OSTRIO_ACCOUNTING_STATUS'] )): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">OS Trio Accounting Integration &nbsp;</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['OSTRIO_ACCOUNTING_STATUS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/ostrio_accounting.status.php" )): ?>
								<li><a class="sub-slide-item"  href="ostrio_accounting.status.php">Integration Status</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
				
								<?php if ($this->_tpl_vars['config']['speed99_settings'] && ( $this->_tpl_vars['sessioninfo']['privilege']['SPEED99_INTEGRATION_STATUS'] )): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Speed99 Integration &nbsp;</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['SPEED99_INTEGRATION_STATUS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/speed99.integration_status.php" )): ?>
								<li><a class="sub-slide-item"  href="speed99.integration_status.php">Integration Status</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
				
								<?php if ($this->_tpl_vars['config']['komaiso_settings'] && $this->_tpl_vars['sessioninfo']['privilege']['KOMAISO_INTEGRATION_STATUS']): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Komaiso Integration &nbsp;</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['KOMAISO_INTEGRATION_STATUS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/komaiso.integration_status.php" )): ?>
								<li><a class="sub-slide-item"  href="komaiso.integration_status.php">Integration Status</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
				
				<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_table_setup.php" ) && ( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_SETUP'] || $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_ASSIGN'] )): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Time Attendance</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">

							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_TIME_OVERVIEW'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.overview.php" )): ?>
								<li><a class="sub-slide-item"  href="attendance.overview.php">Time Attendance Overview</a></li>
							<?php endif; ?>
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_TIME_SETTING'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.settings.php" )): ?>
								<li><a class="sub-slide-item"  href="attendance.settings.php">Settings</a></li>
							<?php endif; ?>
							
							<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_table_setup.php" ) ) || ( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_ASSIGN'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_assignment.php" ) )): ?>
								<li class="sub-slide-sub">
									<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">Shift</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu-sub">
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_table_setup.php" )): ?>
											<li><a class="sub-slide-item"  href="attendance.shift_table_setup.php">Shift Table Setup</a></li>
										<?php endif; ?>
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_ASSIGN'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_assignment.php" )): ?>
											<li><a class="sub-slide-item"  href="attendance.shift_assignment.php">Shift Assignments</a></li>
										<?php endif; ?>
									</ul>
								</li>
							<?php endif; ?>
							
							<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_PH_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.ph_setup.php" ) ) || ( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_PH_ASSIGN'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.ph_assignment.php" ) )): ?>
								<li class="sub-slide-sub">
									<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">Holiday</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu-sub">
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_PH_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.ph_setup.php" )): ?>
											<li><a class="sub-slide-item"  href="attendance.ph_setup.php">Holiday Setup</a></li>
										<?php endif; ?>
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_PH_ASSIGN'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.ph_assignment.php" )): ?>
											<li><a class="sub-slide-item"  href="attendance.ph_assignment.php">Holiday Assignments</a></li>
										<?php endif; ?>
									</ul>
								</li>
							<?php endif; ?>
							
							<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_LEAVE_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.leave_setup.php" ) ) || ( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_LEAVE_ASSIGN'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.leave_assignment.php" ) )): ?>
								<li class="sub-slide-sub">
									<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">Leave</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu-sub">
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_LEAVE_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.leave_setup.php" )): ?>
											<li><a class="sub-slide-item"  href="attendance.leave_setup.php">Leave Table Setup</a></li>
										<?php endif; ?>
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_LEAVE_ASSIGN'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.leave_assignment.php" )): ?>
											<li><a class="sub-slide-item"  href="attendance.leave_assignment.php">Leave Assignments</a></li>
										<?php endif; ?>
									</ul>
								</li>
							<?php endif; ?>
							
							
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_USER_MODIFY'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.user_records.php" )): ?>
								<li><a class="slide-item" href="attendance.user_records.php">User Attendance Records</a></li>
							<?php endif; ?>
							
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_CLOCK_REPORT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.report.daily.php" )): ?>
								<li class="sub-slide-sub">
									<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">Reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu-sub">
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_CLOCK_REPORT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.report.daily.php" )): ?>
											<li><a class="sub-slide-item"  href="attendance.report.daily.php">Daily Attendance Report</a></li>
										<?php endif; ?>
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_CLOCK_REPORT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.report.monthly_ledger.php" )): ?>
											<li><a class="sub-slide-item"  href="attendance.report.monthly_ledger.php">Monthly Attendance Ledger</a></li>
										<?php endif; ?>
									</ul>
								</li>
							<?php endif; ?>						
						</ul>
					</li>
				<?php endif; ?>
	        </ul>
	    </li>
        <?php endif; ?>
        <!-- Office Ends -->
        <!-- Store Starts -->
        <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRR'] || $this->_tpl_vars['sessioninfo']['privilege']['GRN'] || $this->_tpl_vars['sessioninfo']['privilege']['GRA_CHECKOUT'] || $this->_tpl_vars['sessioninfo']['privilege']['DO_CHECKOUT'] || $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE']): ?>
        <li class="slide">
			<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-domain side-menu__icon"></i><span class="side-menu__label">Store</span><i class="angle fe fe-chevron-down"></i></a>
	        <ul class="slide-menu">
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRR']): ?><li><a class="slide-item" href="goods_receiving_record.php">GRR (Goods Receiving Record)</a></li><?php endif; ?>
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRN']): ?>
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">GRN (Goods Receiving Note)</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        <li><a class="sub-slide-item"  href="/goods_receiving_note.php">GRN</a></li>
	                        <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRN_APPROVAL']): ?>
	                        <li><a class="sub-slide-item"  href="/goods_receiving_note_approval.php">GRN Approval</a></li>
	                        <?php endif; ?>
	                    </ul>
	                </li>
	            <?php endif; ?>
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRA_CHECKOUT']): ?><li><a class="slide-item" href="goods_return_advice.checkout.php">GRA Checkout</a><?php endif; ?>
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['DO_CHECKOUT']): ?><li><a class="slide-item" href="do_checkout.php">Delivery Order Checkout</a><?php endif; ?>
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE']): ?>
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Stock Take</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        <li><a class="sub-slide-item"  href="admin.stock_take.php">Stock Take</a></li>
	                        <li><a class="sub-slide-item"  href="admin.stock_take.php?a=import_page">Import / Reset Stock Take</a></li>
	                        <li><a class="sub-slide-item"  href="admin.stock_take.php?a=change_batch">Change Batch</a></li>
	                        <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.stock_take_zerolize_negative_stocks.php" ) && $this->_tpl_vars['config']['consignment_modules']): ?>
	                            <li><a class="sub-slide-item"  href="admin.stock_take_zerolize_negative_stocks.php">Zerolize Negative Stocks</a></li>
	                        <?php endif; ?>
	                    </ul>
	                </li>
	            <?php endif; ?>
				<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT'] || $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT'] || $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT_SCHEDULE_LIST'] || $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT_APPROVAL'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.cycle_count.assignment.php" )): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Cycle Count</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT'] || $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.cycle_count.assignment.php" )): ?>
								<li><a class="sub-slide-item"  href="admin.cycle_count.assignment.php">Cycle Count Assignment</a></li>
							<?php endif; ?>
							<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT_APPROVAL'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.cycle_count.approval.php" )): ?>
								<li><a class="sub-slide-item"  href="admin.cycle_count.approval.php">Cycle Count Approval</a></li>
							<?php endif; ?>
							<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT_SCHEDULE_LIST'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.cycle_count.schedule_list.php" )): ?>
								<li><a class="sub-slide-item"  href="admin.cycle_count.schedule_list.php">Monthly Schedule List</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
	        </ul>
    	</li>
        <?php endif; ?>
    <?php else: ?>
        <li><a href="#">Office</a>
	        <ul class="slide-menu">
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_APPLY'] && $this->_tpl_vars['sessioninfo']['branch_type'] != 'franchise'): ?>
	                <li><a class="slide-item" href="masterfile_sku_application.php">SKU Application</a></li>
	                   <?php if (! $this->_tpl_vars['config']['menu_hide_bom_application']): ?><li><a class="slide-item" href="masterfile_sku_application_bom.php">Create BOM SKU</a></li><?php endif; ?>
	            <?php endif; ?>
	            
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_APPLY'] || $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_APPROVAL']): ?><li><a class="slide-item" href="masterfile_sku_application.php?a=list">SKU Application Status</a><?php endif; ?>
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['SKU_REPORT']): ?>
	            <!--li><a href="sku.summary.php">SKU Summary (Testing)</a></li-->
	            <!--li><a href="sku.history.php">SKU History (Testing)</a></li-->
	            <?php endif; ?>
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_UPDATE_FUTURE_PRICE'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sku_items.future_price.php" )): ?>
	                <li><a class="slide-item" href="masterfile_sku_items.future_price.php">Batch Selling Price Change</a></li>
	            <?php endif; ?>

	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['ACCOUNT_EXPORT']): ?>
	              <li><a class="slide-item" href="acc_export.php">Account Export</a>
	              <li><a class="slide-item" href="acc_export.php?a=setting">Account Export Setting</a>
	            <?php endif; ?>
				
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_AND_GST_SETTING'] || $this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_EXPORT_SETUP'] || $this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_EXPORT']): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Custom Accounting Export</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_AND_GST_SETTING'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/custom.acc_and_gst_setting.php" )): ?>
								<li><a class="sub-slide-item"  href="custom.acc_and_gst_setting.php">Custom Account & GST Setting</a></li>
							<?php endif; ?>
							
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_EXPORT_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/custom.setup_acc_export.php" )): ?>
								<li><a class="sub-slide-item"  href="custom.setup_acc_export.php">Setup Custom Accounting Export</a></li>
							<?php endif; ?>
							
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_EXPORT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/custom.acc_export.php" )): ?>
								<li><a class="sub-slide-item"  href="custom.acc_export.php">Custom Accounting Export</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
				
				<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_table_setup.php" ) && ( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_SETUP'] )): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Time Attendance</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_table_setup.php" )): ?>
								<li><a class="sub-slide-item"  href="attendance.shift_table_setup.php">Shift Table Setup</a></li>
							<?php endif; ?>			
						</ul>
					</li>
				<?php endif; ?>
	        </ul>
    	</li>
    <?php endif; ?>

<?php endif; ?>
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
								<?php if (strpos ( $_SERVER['SERVER_NAME'] , 'arms-go' ) !== false): ?>
									ARMS&reg; GO Retail Management System &amp; Point Of Sale
								<?php elseif ($this->_tpl_vars['config']['consignment_modules']): ?>
									ARMS&reg; Consignment Retail Management System &amp; Point Of Sale
								<?php else: ?>
									<?php echo $this->_config[0]['vars']['SYSTEM_ID']; ?>

								<?php endif; ?>
							</h5>
						</div>
					</div>
					<div class="main-header-right">
						<ul class="nav">
							<li >
								<div class="dropdown  nav-itemd-none d-md-flex">
									<?php if ($this->_tpl_vars['sessioninfo']): ?>
										Logged in as 
										<?php if ($_SESSION['admin_session']): ?>
											<?php echo $_SESSION['admin_session']['u']; ?>
</b> (now running as <b><?php echo $this->_tpl_vars['sessioninfo']['u']; ?>
</b> |)
										<?php else: ?>
											<?php echo $this->_tpl_vars['sessioninfo']['u']; ?>

										<?php endif; ?>
									<?php elseif ($this->_tpl_vars['vp_session']): ?>
										Logged in as <?php echo $this->_tpl_vars['vp_session']['description']; ?>

									<?php elseif ($this->_tpl_vars['dp_session']): ?>
										Logged in as <?php echo $this->_tpl_vars['dp_session']['description']; ?>

									<?php elseif ($this->_tpl_vars['sa_session']): ?>
										Logged in as <?php echo $this->_tpl_vars['sa_session']['name']; ?>

									<?php endif; ?>
								</div>
							</li>
						</ul>
						<div class="nav nav-item  navbar-nav-right ml-auto">
							<div class="nav-link" id="bs-example-navbar-collapse-1">
								<form class="navbar-form" role="search">
									<div class="input-group">
										<input type="text" class="form-control" placeholder="Search">
										<span class="input-group-btn">
											<button type="reset" class="btn btn-default">
												<i class="fas fa-times"></i>
											</button>
											<button type="submit" class="btn btn-default nav-link resp-btn">
												<svg xmlns="http://www.w3.org/2000/svg" class="header-icon-svgs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
											</button>
										</span>
									</div>
								</form>
							</div>
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
				<!-- Here Wil Be the Main Pgae starts  -->