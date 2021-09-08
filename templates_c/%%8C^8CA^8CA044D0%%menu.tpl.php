<?php /* Smarty version 2.6.18, created on 2021-09-08 19:28:49
         compiled from menu.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', 'menu.tpl', 1361, false),)), $this); ?>

 
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

<?php if ($this->_tpl_vars['sessioninfo']): ?>
	<li class="slide">
		<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-home side-menu__icon"></i><span class="side-menu__label">Home</span><i class="angle fe fe-chevron-down"></i></a>
		<ul class="slide-menu">
			<li><a class="sub-slide-item" href="home.php" >Dashboard</a></li>
			<li><a class="sub-slide-item" href="javascript:void(goto_branch(0))" >Go To Branch</a></li>
			<li><a class="sub-slide-item" href="/login.php?logout=1" onclick="return confirm('<?php echo $this->_tpl_vars['LANG']['CONFIRM_LOGOUT']; ?>
')">Logout</a></li>
		</ul>
	</li>
	<!-- Administrator -->
	<?php if ($this->_tpl_vars['sessioninfo']['privilege']['USERS_ADD'] || $this->_tpl_vars['sessioninfo']['privilege']['USERS_MNG'] || $this->_tpl_vars['sessioninfo']['privilege']['USERS_ACTIVATE'] || $this->_tpl_vars['sessioninfo']['privilege']['MST_APPROVAL'] || $this->_tpl_vars['sessioninfo']['privilege']['POS_IMPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['SKU_EXPORT'] || $this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
	<li class="slide">
		<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-account side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Administrator</span><i class="angle fe fe-chevron-down"></i></a>
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
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_APPROVAL']): ?>
			<li>
				<a href="approval_flow.php" class="slide-item">Approval Flows</a></li><?php endif; ?>
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
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide"  href="masterfile_gst_settings.php">GST Settings</a></li>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['config']['enable_tax'] && $this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
				<li class="slide">
					<ul class="sub-slide">
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
		            <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">SKU reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
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
		                        <li class="sub-slide-sub"><!--a href="purchase_order.php">Purchase Order</a-->
		                        <a class="sub-slide-item" href="po.php">Purchase Order</a></li>
		                    <?php endif; ?>
		                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO_APPROVAL']): ?>
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="po_approval.php">PO Approval</a></li>
		                    <?php endif; ?>
		                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO_FROM_REQUEST']): ?>
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="po_request.process.php">Create PO from Request</a></li>
		                    <?php endif; ?>
		                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO_REQUEST']): ?>
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="po_request.request.php">PO Request</a></li>
		                    <?php endif; ?>
		                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO_REQUEST_APPROVAL']): ?>
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="po_request.approval.php">PO Request Approval</a></li>
		                    <?php endif; ?>
		                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO_TICKET'] && $this->_tpl_vars['config']['po_allow_vendor_request']): ?>
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="vendor_po_request.php">Vendor PO Access</a></li>
		                    <?php endif; ?>
		                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO_REPORT']): ?><li><a class="sub-slide-item"  href="purchase_order.summary.php">PO Summary</a></li><?php endif; ?>
		                    <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/po_qty_performance.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['PO_REPORT']): ?>
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="po_qty_performance.php">PO Quantity Performance</a></li>
		                    <?php endif; ?>
		                    <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.stock_reorder.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['PO'] && $this->_tpl_vars['sessioninfo']['privilege']['PO_REPORT']): ?>
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="report.stock_reorder.php">Stock Reorder Report</a></li>
		                    <?php endif; ?>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/sku_purchase_history.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['PO'] && $this->_tpl_vars['sessioninfo']['privilege']['PO_REPORT']): ?>
		                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="sku_purchase_history.php">SKU Purchase History</a></li>
		                    <?php endif; ?>
		                </ul>
		            </li>
	            <?php endif; ?>
	            
	            <?php if ($this->_tpl_vars['config']['enable_po_agreement'] && $this->_tpl_vars['sessioninfo']['privilege']['PO_SETUP_AGREEMENT'] && $this->_tpl_vars['BRANCH_CODE'] == 'HQ' && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/po.po_agreement.setup.php" )): ?>
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Purchase Agreement</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="po.po_agreement.setup.php">Add/Edit Purchase Agreement</a></li>
	                    </ul>
	                </li>
	            <?php endif; ?>
	    
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRN_APPROVAL'] && ! $this->_tpl_vars['config']['use_grn_future']): ?><li><a class="slide-item" href="goods_receiving_note_approval.account.php">GRN Account Verification</a><?php endif; ?>
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRA']): ?>
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">GRA (Goods Return Advice)</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="goods_return_advice.php">GRA</a></li>
	                        <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRA_APPROVAL']): ?>
	                            <li class="sub-slide-sub"><a class="sub-slide-item"  href="/goods_return_advice.approval.php">GRA Approval</a></li>
	                        <?php endif; ?>
	                    </ul>
	                </li>
	            <?php endif; ?>
	    
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRA_REPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['GRR_REPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['GRN_REPORT']): ?>
	            <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">GRR / GRN / GRA Reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
		            <ul class="sub-slide-menu">
		            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRR_REPORT']): ?>
		            <li class="sub-slide-sub"><a class="sub-slide-item"  href="goods_receiving_record.report.php">GRR Report</a></li>
		            <li class="sub-slide-sub"><a class="sub-slide-item"  href="goods_receiving_record.status.php">GRR Status Report</a></li>
		            <?php endif; ?>
		            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRN_REPORT']): ?>
		                <li ><a class="sub-slide-item"  href="goods_receiving_note.summary.php">GRN Summary</a></li>
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
	                    <li class="sub-slide-sub"><a class="sub-slide-item"  href="cnote.php">Credit Note</a></li>
	                    <?php if ($this->_tpl_vars['sessioninfo']['privilege']['CN_APPROVAL']): ?>
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="/cnote.approval.php">Credit Note Approval</a></li>
	                    <?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/cnote.summary.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="cnote.summary.php">CN Summary</a></li>
						<?php endif; ?>
	                </ul>
	              </li>
	            <?php endif; ?>

	            	            <?php if ($this->_tpl_vars['config']['enable_vendor_portal'] && ( $this->_tpl_vars['sessioninfo']['privilege']['REPORTS_REPACKING'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.repacking.php" ) )): ?>
	                <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Vendor Portal</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                    <ul class="sub-slide-menu">
	                        <?php if ($this->_tpl_vars['sessioninfo']['privilege']['REPORTS_REPACKING'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.repacking.php" )): ?>
	                            <li class="sub-slide-sub"><a class="sub-slide-item"  href="report.repacking.php">Repacking Report</a></li>
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
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="custom.acc_and_gst_setting.php">Custom Account & GST Setting</a></li>
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
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="arms_accounting.setting.php">Setting</a></li>
							<?php endif; ?>
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ARMS_ACCOUNTING_STATUS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/arms_accounting.status.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="arms_accounting.status.php">Integration Status</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
				
								<?php if ($this->_tpl_vars['config']['os_trio_settings'] && ( $this->_tpl_vars['sessioninfo']['privilege']['OSTRIO_ACCOUNTING_STATUS'] )): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">OS Trio Accounting Integration &nbsp;</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['OSTRIO_ACCOUNTING_STATUS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/ostrio_accounting.status.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="ostrio_accounting.status.php">Integration Status</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
				
								<?php if ($this->_tpl_vars['config']['speed99_settings'] && ( $this->_tpl_vars['sessioninfo']['privilege']['SPEED99_INTEGRATION_STATUS'] )): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Speed99 Integration &nbsp;</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['SPEED99_INTEGRATION_STATUS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/speed99.integration_status.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="speed99.integration_status.php">Integration Status</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
				
								<?php if ($this->_tpl_vars['config']['komaiso_settings'] && $this->_tpl_vars['sessioninfo']['privilege']['KOMAISO_INTEGRATION_STATUS']): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Komaiso Integration &nbsp;</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['KOMAISO_INTEGRATION_STATUS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/komaiso.integration_status.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="komaiso.integration_status.php">Integration Status</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
				
				<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_table_setup.php" ) && ( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_SETUP'] || $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_ASSIGN'] )): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Time Attendance</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">

							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_TIME_OVERVIEW'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.overview.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.overview.php">Time Attendance Overview</a></li>
							<?php endif; ?>
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_TIME_SETTING'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.settings.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.settings.php">Settings</a></li>
							<?php endif; ?>
							
							<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_table_setup.php" ) ) || ( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_ASSIGN'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_assignment.php" ) )): ?>
								<li class="sub-slide-sub">
									<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">Shift</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu-sub">
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_table_setup.php" )): ?>
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.shift_table_setup.php">Shift Table Setup</a></li>
										<?php endif; ?>
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_ASSIGN'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_assignment.php" )): ?>
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.shift_assignment.php">Shift Assignments</a></li>
										<?php endif; ?>
									</ul>
								</li>
							<?php endif; ?>
							
							<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_PH_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.ph_setup.php" ) ) || ( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_PH_ASSIGN'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.ph_assignment.php" ) )): ?>
								<li class="sub-slide-sub">
									<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">Holiday</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu-sub">
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_PH_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.ph_setup.php" )): ?>
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.ph_setup.php">Holiday Setup</a></li>
										<?php endif; ?>
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_PH_ASSIGN'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.ph_assignment.php" )): ?>
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.ph_assignment.php">Holiday Assignments</a></li>
										<?php endif; ?>
									</ul>
								</li>
							<?php endif; ?>
							
							<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_LEAVE_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.leave_setup.php" ) ) || ( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_LEAVE_ASSIGN'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.leave_assignment.php" ) )): ?>
								<li class="sub-slide-sub">
									<a class="sub-side-menu__item sub-slide-item" data-toggle="sub-slide-sub" href="#"><span class="sub-side-menu__label">Leave</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu-sub">
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_LEAVE_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.leave_setup.php" )): ?>
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.leave_setup.php">Leave Table Setup</a></li>
										<?php endif; ?>
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_LEAVE_ASSIGN'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.leave_assignment.php" )): ?>
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.leave_assignment.php">Leave Assignments</a></li>
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
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.report.daily.php">Daily Attendance Report</a></li>
										<?php endif; ?>
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_CLOCK_REPORT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.report.monthly_ledger.php" )): ?>
											<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.report.monthly_ledger.php">Monthly Attendance Ledger</a></li>
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
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="/goods_receiving_note.php">GRN</a></li>
	                        <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRN_APPROVAL']): ?>
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="/goods_receiving_note_approval.php">GRN Approval</a></li>
	                        <?php endif; ?>
	                    </ul>
	                </li>
	            <?php endif; ?>
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['GRA_CHECKOUT']): ?><li><a class="slide-item" href="goods_return_advice.checkout.php">GRA Checkout</a><?php endif; ?>
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['DO_CHECKOUT']): ?><li><a class="slide-item" href="do_checkout.php">Delivery Order Checkout</a><?php endif; ?>
	            <?php if ($this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE']): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Stock Take</span><i class="sub-angle fe fe-chevron-down"></i></a>
	                	
	                    <ul class="sub-slide-menu">
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.stock_take.php">Stock Take</a></li>
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.stock_take.php?a=import_page">Import / Reset Stock Take</a></li>
	                        <li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.stock_take.php?a=change_batch">Change Batch</a></li>
	                        <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.stock_take_zerolize_negative_stocks.php" ) && $this->_tpl_vars['config']['consignment_modules']): ?>
	                            <li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.stock_take_zerolize_negative_stocks.php">Zerolize Negative Stocks</a></li>
	                        <?php endif; ?>
	                    </ul>
	                </li>
	            <?php endif; ?>
				<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT'] || $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT'] || $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT_SCHEDULE_LIST'] || $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT_APPROVAL'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.cycle_count.assignment.php" )): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Cycle Count</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT'] || $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.cycle_count.assignment.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.cycle_count.assignment.php">Cycle Count Assignment</a></li>
							<?php endif; ?>
							<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT_APPROVAL'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.cycle_count.approval.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.cycle_count.approval.php">Cycle Count Approval</a></li>
							<?php endif; ?>
							<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['STOCK_TAKE_CYCLE_COUNT_SCHEDULE_LIST'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.cycle_count.schedule_list.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="admin.cycle_count.schedule_list.php">Monthly Schedule List</a></li>
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
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="custom.acc_and_gst_setting.php">Custom Account & GST Setting</a></li>
							<?php endif; ?>
							
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_EXPORT_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/custom.setup_acc_export.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="custom.setup_acc_export.php">Setup Custom Accounting Export</a></li>
							<?php endif; ?>
							
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['CUSTOM_ACC_EXPORT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/custom.acc_export.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="custom.acc_export.php">Custom Accounting Export</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
				
				<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_table_setup.php" ) && ( $this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_SETUP'] )): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Time Attendance</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ATTENDANCE_SHIFT_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/attendance.shift_table_setup.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item"  href="attendance.shift_table_setup.php">Shift Table Setup</a></li>
							<?php endif; ?>			
						</ul>
					</li>
				<?php endif; ?>
	        </ul>
    	</li>
    <?php endif; ?>
	<!--master files start-->

			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MASTERFILE']): ?>
		<li class="slide">
				<a href="#" class="side-menu__item" data-toggle="slide"><i class="mdi mdi-library-books side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Master files</span><i class="angle fe fe-chevron-down"></i></a>
			<ul class="slide-menu">
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Category</span><i class="sub-angle fe fe-chevron-down"></i></a>
	 					<ul class="sub-slide-menu">
			 				<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_category.php">Category Listing</a></li>
								 					</ul>
				</li>
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_UPDATE'] || $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_APPLY'] || $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU']): ?>
				<li class="sub-slide"><a href="#" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">SKU reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.php">SKU Listing</a></li>
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_UPDATE_PRICE']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_sku_items_price.php">Change Selling Price</a></li><?php endif; ?>
								<?php if (! $this->_tpl_vars['config']['menu_hide_bom_application']): ?>	
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="bom.php">BOM Editor</a></li><?php endif; ?>
				
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_group.php">SKU Group</a></li>
				
				
								<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && $this->_tpl_vars['config']['po_enable_ibt'] && $this->_tpl_vars['config']['enable_sku_monitoring2'] && $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_MORN_GRP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sku_monitoring_group.php" )): ?>
				   			 <li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_monitoring_group.php">SKU Monitoring Group</a></li>
								<?php endif; ?>
								<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && $this->_tpl_vars['config']['enable_replacement_items'] && $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_RELP_ITEM'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_replacement_items.php" )): ?>
				    		<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_replacement_items.php">Replacement Items</a></li>
								<?php endif; ?>
								<?php if ($this->_tpl_vars['config']['enable_sn_bn'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sku_items.serial_no.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_items.serial_no.php">Serial No Listing</a></li>
								<?php endif; ?>
								<?php if ($this->_tpl_vars['config']['enable_sn_bn'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sku_items.batch_no_setup.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_items.batch_no_setup.php">SKU Batch No Setup</a></li>
								<?php endif; ?>
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sku.price_list.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.price_list.php">SKU Price List</a></li>
								<?php endif; ?>
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sku_items.po_reorder_qty_by_branch.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_PO_REORDER_QTY_BY_BRANCH']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_items.po_reorder_qty_by_branch.php">PO Reorder Qty by Branch</a></li>
								<?php endif; ?>
								<?php if ($this->_tpl_vars['config']['enable_gst'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_gst.price_wizard.php" ) && ( $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_UPDATE_PRICE'] || $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_UPDATE_FUTURE_PRICE'] )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_gst.price_wizard.php">GST Price Wizard</a></li>
								<?php endif; ?>
				
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_stock_balance_listing.php">SKU Stock Balance Listing (Download)</a></li>

								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sku_tag.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_TAG']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku_tag.php">SKU Tag</a></li>
								<?php endif; ?>
				
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sku.update_brand_vendor.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_UPDATE']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.update_brand_vendor.php?method=brand">Update SKU Brand by CSV</a></li>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.update_brand_vendor.php?method=vendor">Update SKU Vendor by CSV</a></li>
								<?php endif; ?>
				
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sku.update_po_reorder_qty.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_UPDATE']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.update_po_reorder_qty.php">Update SKU Stock Reorder Min & Max Qty by CSV</a></li>
								<?php endif; ?>
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sku.update_category.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_UPDATE']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.update_category.php">Update SKU Category by CSV</a></li>
								<?php endif; ?>
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sku.update_category_discount.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_UPDATE'] && $this->_tpl_vars['sessioninfo']['privilege']['CATEGORY_DISCOUNT_EDIT']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.update_category_discount.php">Update SKU Category Discount by CSV</a></li>
								<?php endif; ?>
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sku.update_sku.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_SKU_UPDATE']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item"  href="masterfile_sku.update_sku.php">Update SKU Info by CSV</a></li>
								<?php endif; ?>
						</ul>
		<?php endif; ?>
		<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="masterfile_uom.php"><span class="sub-side-menu__label">UOM</span></a>
		<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="masterfile_brand.php"><span class="sub-side-menu__label">Brand</span></a>
		<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="masterfile_brgroup.php"><span class="sub-side-menu__label">Brand Group</span></a>
		
		
		<li class="sub-slide"><a href="#" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Vendor</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_vendor.php">Add / Edit</a></li>
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_VENDOR_QUOTATION_COST'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_vendor.quotation_cost.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_vendor.quotation_cost.php">Quotation Cost</a></li>
								<?php endif; ?>
						</ul>
		</li>
		
		<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_BRANCH']): ?>
		<li class="sub-slide"><a href="#" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Branch</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_branch.php">Add / Edit</a></li>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_branch_group.php">Branches Group</a></li>
								<?php if ($this->_tpl_vars['config']['masterfile_branch_enable_additional_sp'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_branch.additional_selling_price.php" )): ?>
							<li><a href="masterfile_branch.additional_selling_price.php">Branches Additional Selling Price</a></li>
								<?php endif; ?>
						</ul>
		</li>
		<?php endif; ?>
		
		<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_DEBTOR'] || $this->_tpl_vars['sessioninfo']['privilege']['MST_DEBTOR_PRICE_LIST']): ?>
		<li class="sub-slide"><a href="#" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Debtor</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_DEBTOR'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_debtor.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_debtor.php">Add / Edit</a></li>
								<?php endif; ?>
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_DEBTOR_PRICE_LIST'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_debtor_price.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_debtor_price.php">Debtor Price List</a></li>
								<?php endif; ?>
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_DEBTOR_CSV_UPDATE_PRICE'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_import_debtor_price.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_import_debtor_price.php">Import / Update Debtor Price by CSV</a></li>
								<?php endif; ?>
						</ul>
		</li>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_TRANSPORTER'] && $this->_tpl_vars['config']['enable_transporter_masterfile']): ?>
		<li class="sub-slide"><a href="masterfile_transporter.php" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Transporter</span><i class="sub-angle fe fe-chevron-down"></i></a>
		
		<?php endif; ?>
		<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_TRANSPORTER_v2'] && $this->_tpl_vars['config']['enable_reorder_integration']): ?>
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
		<?php endif; ?>
		<?php if ($this->_tpl_vars['config']['use_consignment_bearing'] && $this->_tpl_vars['sessioninfo']['privilege']['MST_CONTABLE'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_consignment_bearing.php" )): ?>
		<li class="sub-slide"><a href="masterfile_consignment_bearing.php" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Consignment Bearing</span><i class="sub-angle fe fe-chevron-down"></i></a>   
		
		<?php endif; ?>
		<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && $this->_tpl_vars['sessioninfo']['privilege']['MST_BANK_INTEREST'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_bank_interest.php" ) && $this->_tpl_vars['config']['enable_sku_monitoring2']): ?>
		    <li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_bank_interest.php">Bank Interest</a></li>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_COUPON']): ?>
		<li class="sub-slide-menu"><a href="masterfile_transporter.php" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Coupon</span><i class="sub-angle fe fe-chevron-down"></i></a>
				<ul class="sub-slide-menu">
				   			 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_coupon.php" )): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_coupon.php">
							<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>Create / Print<?php else: ?>View<?php endif; ?>
							</a>
						</li>
							<?php endif; ?>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.coupon.transaction.php" )): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="report.coupon.transaction.php">Transaction Report</a></li>
							<?php endif; ?>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.coupon.details.php" )): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="report.coupon.details.php">Details Report</a></li>
							<?php endif; ?>
				</ul>
			</li>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_VOUCHER']): ?>
		<li class="sub-slide"><a href="masterfile_transporter.php" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Master Transport V2</span><i class="sub-angle fe fe-chevron-down"></i></a>
		        <ul class="sub-slide-menu">
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_voucher.setup.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_VOUCHER_SETUP']): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_voucher.setup.php">Setup</a></li>
					<?php endif; ?>
					
		            <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_voucher.php" )): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_voucher.php">Listing</a></li>
					<?php endif; ?>
                    <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_voucher.register.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_VOUCHER_REGISTER'] && $this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_voucher.register.php">Registration</a></li>
					<?php endif; ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_voucher.activate.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_VOUCHER_ACTIVATE']): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_voucher.activate.php">Activation</a></li>
					<?php endif; ?>

					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.voucher.transaction.php" )): ?>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.voucher.transaction.php">Transaction Report</a></li><?php endif; ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.voucher.details.php" )): ?>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.voucher.details.php">Details Report</a></li><?php endif; ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.voucher.activation.php" )): ?>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.voucher.activation.php">Activation & Cancellation Report</a></li><?php endif; ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.voucher.collection.php" )): ?>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.voucher.collection.php">Account-receivable Report</a></li><?php endif; ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.voucher.payment.php" )): ?>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.voucher.payment.php">Account-payable Report</a></li><?php endif; ?>
					
					<?php if ($this->_tpl_vars['config']['enable_voucher_auto_redemption'] && ( ( file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_voucher.auto_redemption.setup.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_VOUCHER_AUTO_REDEMP_SETUP'] ) || ( file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_voucher.auto_redemption.generate.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_VOUCHER_AUTO_REDEMP_GENERATE'] ) )): ?>
						<li>
							<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-account-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Auto Redemption</span><i class="angle fe fe-chevron-down"></i></a>
					        <ul class="slide-menu">
					        	<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_voucher.auto_redemption.setup.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_VOUCHER_AUTO_REDEMP_SETUP']): ?>
					        		<li class="slide-item"><a class="sub-side-menu__item" href="masterfile_voucher.auto_redemption.setup.php">Setup</a></li>
					        	<?php endif; ?>
					        	
					        	<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_voucher.auto_redemption.generate.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MST_VOUCHER_AUTO_REDEMP_GENERATE']): ?>
					        		<li class="slide-item"><a class="sub-side-menu__item" href="masterfile_voucher.auto_redemption.generate.php">Generate Voucher</a></li>
					        		<li class="slide-item"><a class="sub-side-menu__item" href="masterfile_voucher.auto_redemption.generate.php?a=his_list">History Listing</a></li>
					        	<?php endif; ?>
					        </ul>
					    </li>
					<?php endif; ?>
				</ul>
			</li>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['config']['enable_supermarket_code'] && $this->_tpl_vars['config']['consignment_modules'] && $this->_tpl_vars['BRANCH_CODE'] == 'HQ' && $this->_tpl_vars['sessioninfo']['privilege']['MST_SUPERMARKET_CODE'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_supermarket_code.php" )): ?>
		<li class="sub-slide"><a href="masterfile_supermarket_code.php" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Super market code</span></a>
		
		<?php endif; ?>
		<?php if ($this->_tpl_vars['config']['masterfile_enable_sa'] && $this->_tpl_vars['sessioninfo']['privilege']['MST_SALES_AGENT']): ?>
		<li class="sub-slide"><a href="" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Sales Agent</span><i class="sub-angle fe fe-chevron-down"></i></a>
				<ul class="sub-slide-menu">
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sa.php" )): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_sa.php">Create / Edit</a></li>
					<?php endif; ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sa_commission.php" )): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_sa_commission.php">Commission Table</a></li>
					<?php endif; ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sa.position_setup.php" )): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="masterfile_sa.position_setup.php">Position Table</a></li>
					<?php endif; ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sa.kpi_setup.php" )): ?>
						<li class="sub-slide-sub"><a href="#" class=submenu>KPI</a>
							<ul class="sub-slide-menu-sub">
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_SALES_AGENT_KPI_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sa.kpi_setup.php" )): ?>
									<li><a class="sub-slide-item" href="masterfile_sa.kpi_setup.php">KPI Table</a></li>
								<?php endif; ?>
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_sa.kpi_result.php" )): ?>
									<li><a class="sub-slide-item" href="masterfile_sa.kpi_result.php">KPI Result</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					<li class="sub-slide"><a href="" class="sub-side-menu__item" data-toggle="sub-slide"><span class="sub-side-menu__label">Reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.view_sa_commission.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item" href="report.view_sa_commission.php">View Sales Agent Commission</a></li>
							<?php endif; ?>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sa_commission_calculation.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item" href="report.sa_commission_calculation.php">Sales Agent Commission Calculation Report</a></li>
							<?php endif; ?>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sa_performance.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item" href="report.sa_performance.php">Sales Agent Performance Report</a></li>
							<?php endif; ?>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sa_commission_statement_by_company.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item" href="report.sa_commission_statement_by_company.php">Sales Agent Commission Statement by Company Report</a></li>
							<?php endif; ?>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sa_daily_details.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item" href="report.sa_daily_details.php">Sales Agent Daily Details Report</a></li>
							<?php endif; ?>
						</ul>
					</li>
				</ul>
			</li>
		<?php endif; ?>
				<?php if ($this->_tpl_vars['config']['enable_gst'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_gst.php" )): ?>
			<li><a href="masterfile_gst.php">Masterfile GST Tax Code</a>
		<?php endif; ?>
	</ul>

	</li>
	<?php endif; ?>

	<!--master files ends-->


			<!--membership files start-->

			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP'] || $this->_tpl_vars['sessioninfo']['privilege']['RPT_MEMBERSHIP']): ?>
			<li class="slide">
				<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-account-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Membership</span><i class="angle fe fe-chevron-down"></i></a>
			<ul class="slide-menu">
				<?php if ($this->_tpl_vars['config']['membership_allow_add_at_backend']): ?>
				<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_ADD']): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="membership.php?a=add"><span class="sub-side-menu__label">Add New Member</span></a>
		
					
				<?php endif; ?>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_EDIT'] || $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_ADD']): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="membership.php?t=update"><span class="sub-side-menu__label">Update Information</span></a>
					
				<?php endif; ?>
				<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_VERIFY']): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="membership.php?t=verify"><span class="sub-side-menu__label">Verification</span></a>
					
				<?php endif; ?>
				<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_EDIT'] || $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_ADD']): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="membership.listing.php"><span class="sub-side-menu__label">Member Listing</span></a>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="membership.php?t=history"><span class="sub-side-menu__label">Check Points &amp; History</span></a>
					
				<?php endif; ?>
				<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_TERMINATE']): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="membership.terminate.php"><span class="sub-side-menu__label">Terminate</span></a>
					
				<?php endif; ?>
				<?php if ($this->_tpl_vars['sessioninfo']['privilege']['RPT_MEMBERSHIP']): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Membership report</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						<li class="sub-slide-sub"><a class="sub-slide-item" href="report.mem_counter.php">Membership Counters Report</a></li>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="report.mem_verification.php">Membership Verification Report</a></li>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.redemption_points_history.php" ) && $this->_tpl_vars['config']['membership_redemption_module']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.redemption_points_history.php">Membership Points History Report</a></li>
						<?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.membership_expiration.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.membership_expiration.php">Membership Expiration Report</a></li>
						<?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.membership_renewal.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.membership_renewal.php">Membership Renewal Report</a></li>
						<?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.membership_fees_collection_summary.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.membership_fees_collection_summary.php">Membership Fees Collection Summary Report</a></li>
						<?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.membership_daily_collection.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.membership_daily_collection.php">Membership Daily Collection Report</a></li>
						<?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.membership_points_detail.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.membership_points_detail.php">Membership Points Detail Report</a></li>
						<?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.membership_issued_points.php" )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="report.membership_issued_points.php">Membership Issued Points Report</a></li>
						<?php endif; ?>
					</ul>
				</li>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['config']['membership_redemption_module']): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Membership redemption</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_SETREDEEM']): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.redemption_setup.php">Redemption Item Setup</a></li>
						<?php endif; ?>
						<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_ITEM_CFRM'] && $this->_tpl_vars['config']['membership_redemption_use_enhanced']): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.redemption_item_approval.php">Redemption Item Approval</a></li>
						<?php endif; ?>
						<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_REDEEM']): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.redemption.php">Make Redemption</a></li>
						<?php endif; ?>
						<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_REDEEM'] || $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_CANCEL_RE']): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.redemption_history.php">Redemption History</a></li>
						<?php endif; ?>
						<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_REDEEM_RPT']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.redemption_summary.php">Redemption Summary</a></li>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.redemption_ranking.php">Redemption Ranking</a></li>
						<?php endif; ?>
					</ul>
				</li>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['config']['membership_control_counter_adjust_point']): ?>
					<li><a href="membership.delivery.php">Delivery</a></li>
				<?php endif; ?>
				
				<?php if ($this->_tpl_vars['config']['membership_enable_staff_card']): ?>
					<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_STAFF']): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Membership staff card</span><i class="sub-angle fe fe-chevron-down"></i></a>
							<ul class="sub-slide-menu">
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_STAFF_SET_QUOTA'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/membership.staff.setup_quota.php" )): ?>
									<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.staff.setup_quota.php">Setup Quota</a></li>
								<?php endif; ?>
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/membership.staff.usage_report.php" )): ?>
									<li class="sub-slide-sub"><a  class="sub-slide-item" href="membership.staff.usage_report.php">Quota Usage Report</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
				<?php endif; ?>
				
				<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_OVERVIEW']): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Membership overview</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu">
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/membership.overview.general.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.overview.general.php">Composition</a></li>
							<?php endif; ?>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/membership.overview.sales.php" )): ?>
								<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.overview.sales.php">Sales</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
				
				<?php if ($this->_tpl_vars['config']['membership_mobile_settings'] && ( $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_MOBILE_ADS_SETUP'] )): ?>
					<li><a href="#" class="submenu">Mobile App</a>
						<ul>
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_MOBILE_ADS_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/membership.mobile_app.ads.php" )): ?>
								<li><a href="membership.mobile_app.ads.php">Advertisement Setup</a></li>
							<?php endif; ?>
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_MOBILE_NOTICE_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/membership.mobile_app.notice_board.php" )): ?>
								<li><a href="membership.mobile_app.notice_board.php">Notice Board Setup</a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
				
				<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/membership.package.setup.php" ) && ( $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_PACK_SETUP'] || $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_PACK_REDEEM'] || $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_PACK_REPORT'] )): ?>
				<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Membership package</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/membership.package.setup.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_PACK_SETUP']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.package.setup.php">Package Setup</a></li>
						<?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/membership.package.details.php" ) && ( $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_PACK_REDEEM'] )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.package.details.php?a=scan_member">Package Redemption</a></li>
						<?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/membership.package.rating_report.php" ) && ( $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_PACK_REPORT'] )): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="membership.package.rating_report.php">Package Rating Analysis Report</a></li>
						<?php endif; ?>
					</ul>
				</li>
				<?php endif; ?>
				
				<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/membership.credit.promotion.php" ) && ( $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_CREDIT_PROMO'] || $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_CREDIT_SETTINGS'] )): ?>
				<li class="slide">
					<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-account-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Membership Credit</span><i class="angle fe fe-chevron-down"></i></a>
					<ul class="slide-menu">
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/membership.credit.promotion.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_CREDIT_PROMO']): ?>
							<li class="sub-slide"><a class="sub-side-menu__item" href="membership.credit.promotion.php">Credit Promotion</a></li>
						<?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/membership.credit.settings.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_CREDIT_SETTINGS']): ?>
							<li class="sub-slide"><a class="sub-side-menu__item" href="membership.credit.settings.php">Credit Settings</a></li>
						<?php endif; ?>
					</ul>
				</li>
				<?php endif; ?>
			</ul>
			<?php endif; ?>
				<!--membership files ends-->
<!--Fresh market menu start-->
<?php if ($this->_tpl_vars['config']['enable_fresh_market_sku'] && $this->_tpl_vars['sessioninfo']['privilege']['FM']): ?>
<li class="slide">
	<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-account side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Fresh Market</span><i class="angle fe fe-chevron-down"></i></a>
	<ul class="slide-menu">
		<?php if ($this->_tpl_vars['sessioninfo']['privilege']['FM_WRITE_OFF'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/adjustment.fresh_market_write_off.php" )): ?>
			<li class=""><a class="sub-slide-item" data-toggle="sub-slide" href="/adjustment.fresh_market_write_off.php"> SKU Write-Off</a></li>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['sessioninfo']['privilege']['FM_STOCK_TAKE']): ?>
		<li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Stock Take</a>
			<ul class="sub-slide-menu">
				<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.fresh_market_stock_take.php" )): ?>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="admin.fresh_market_stock_take.php"> Stock Take</a></li>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="admin.fresh_market_stock_take.php?a=import_page"> Import / Reset Stock Take</a></li>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="admin.fresh_market_stock_take.php?a=change_batch">Change Batch</a></li>
				<?php endif; ?>
			</ul>
		</li>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['sessioninfo']['privilege']['FM_REPORT']): ?>
		<li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Report</a>
			<ul class="sub-slide-menu">
				<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.fresh_market_stock_take.php" )): ?>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.fresh_market_stock_take.php"> Fresh Market Stock Take Report</a></li>
				<?php endif; ?>
				<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.fresh_market_sales.php" )): ?>
					<li class="sub-slide-sub"><a href="sub-slide-item" href="report.fresh_market_sales.php"> Fresh Market Sales Report</a></li>
				<?php endif; ?>
			</ul>
		</li>
		<?php endif; ?>
	</ul>
</li>
<?php endif; ?>

<!--fresh market menu ends-->
				<!--report files start-->

				 <!-- Report -->
			<?php if (! $this->_tpl_vars['config']['consignment_modules']): ?>
			<li class="slide">
					<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-chart-bar side-menu__icon" ></i><span class="side-menu__label">Reports</span><i class="angle fe fe-chevron-down"></i></a>
				<ul class="slide-menu">
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "menu.reports.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
					 <?php if ($this->_tpl_vars['sessioninfo']['privilege']['REPORTS_SKU']): ?>
					 <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">SKU reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
							 <ul class="sub-slide-menu">
								 <?php if ($this->_tpl_vars['config']['enable_sn_bn'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.batch_no.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.batch_no.php">Batch No Report</a></li><?php endif; ?>
								 
								 <?php if ($this->_tpl_vars['config']['enable_sn_bn'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.batch_no_transaction_detail.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.batch_no_transaction_detail.php">Batch No Transaction Details Report</a></li><?php endif; ?>
								 
								 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.brand_sales_price_type_discount.php" )): ?>
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.brand_sales_price_type_discount.php">Brand / Vendor Sales by Price Type and Discount Report</a></li>
								 <?php endif; ?>						
								 
								 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.closing_stock_by_sku.php" )): ?>
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.closing_stock_by_sku.php">Closing Stock by SKU Report</a></li>
								 <?php endif; ?>
								 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.closing_stock.php" )): ?>
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.closing_stock.php">Closing Stock Report</a></li>
								 <?php endif; ?>
								 <?php if ($this->_tpl_vars['config']['enable_one_color_matrix_ibt'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.broken_size_clr_by_branch.php" )): ?>
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.broken_size_clr_by_branch.php">Broken Size & Color by Branch Report</a></li>
								 <?php endif; ?>
								 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.mprice_sales.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.mprice_sales.php">MPrice Sales Report</a></li><?php endif; ?>
								 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.multi_branch_sales.php" )): ?>
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.multi_branch_sales.php">Multi Branch Sales Report</a></li>
								 <?php endif; ?>
								 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.multi_branch_stock_balance.php" )): ?>
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.multi_branch_stock_balance.php">Multi Branch Stock Balance</a></li>
								 <?php endif; ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.negative_stock.php">Negative Stock</a></li>
								 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.new_sku_sales_monitor.php" )): ?>
								 <li class="sib-slide-sub"><a class="sub-slide-item" href="report.new_sku_sales_monitor.php">New SKU Sales Monitoring Report</a></li><?php endif; ?>
								 <?php if ($this->_tpl_vars['config']['enable_sn_bn'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sn_activation.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sn_activation.php">Serial No Activation Report</a></li><?php endif; ?>
								 <?php if ($this->_tpl_vars['config']['enable_sn_bn'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sn_expiry.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sn_expiry.php">Serial No Expiry Report</a></li><?php endif; ?>
								 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sn_return.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sn_return.php">Serial No Return Report</a></li><?php endif; ?>
								 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sn_status.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sn_status.php">Serial No Status Report</a></li><?php endif; ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_items_gp.php">SKU Items Gross Profit Report</a></li>
								 <?php if ($this->_tpl_vars['config']['enable_sku_monitoring'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sku_monitoring.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_monitoring.php">SKU Monitoring</a></li>
								 <?php endif; ?>
								 
								 <?php if ($this->_tpl_vars['config']['enable_sku_monitoring2'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sku_monitoring2.php" )): ?>
									 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_monitoring2.php">SKU Monitoring 2</a></li>
								 <?php endif; ?>
								 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sku_sales.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_sales.php">SKU Sales Report</a></li><?php endif; ?>
								 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sku_sales_purchase_profit_margin.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_sales_purchase_profit_margin.php">SKU Sales & Purchase Profit Margin Special Calculation Report</a></li><?php endif; ?>
								 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sku_trans_type_filter.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_trans_type_filter.php">SKU Transaction Type Filter</a></li>
								 <?php endif; ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.slow_moving_item.php">Slow Moving Items</a></li>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_aging.php">Stock Aging Report</a></li>
								 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.stock_balance_detail_by_day.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_balance_detail_by_day.php">Stock Balance Detail by SKU Report</a></li><?php endif; ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_balance.php">Stock Balance Report by Department</a></li>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_balance_summary.php">Stock Balance Summary</a></li>
							 </ul>
						 </li>
					 <?php endif; ?>
		 
					 <?php if ($this->_tpl_vars['config']['enable_gst'] && $this->_tpl_vars['sessioninfo']['privilege']['REPORTS_GST']): ?>
					 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide"> GST Reports</a>
						 <ul class="sub-slide-menu">
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.gst_summary.php">GST Summary</a></li>
						 </ul>
						 </li>
					 <?php endif; ?>
					 
					 <?php if ($this->_tpl_vars['sessioninfo']['privilege']['REPORTS_CUSTOM_BUILDER_CREATE'] || ( $this->_tpl_vars['sessioninfo']['privilege']['REPORTS_CUSTOM_VIEW'] && ( count($this->_tpl_vars['available_custom_report_list']['group']) > 0 || count($this->_tpl_vars['available_custom_report_list']['nogroup']) > 0 ) )): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Custom reports</span><i class="sub-angle fe fe-chevron-down"></i></a>
						 <ul class="sub-slide-menu">
							 <?php if ($this->_tpl_vars['sessioninfo']['privilege']['REPORTS_CUSTOM_BUILDER_CREATE'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/custom_report.builder.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="custom_report.builder.php">Report Builder</a></li>
							 <?php endif; ?>
							 
							 <?php if ($this->_tpl_vars['sessioninfo']['privilege']['REPORTS_CUSTOM_VIEW']): ?>
								 								 <?php $_from = $this->_tpl_vars['available_custom_report_list']['group']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['custom_report_group_name'] => $this->_tpl_vars['custom_report_group_list']):
?>
								 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide"> <?php echo $this->_tpl_vars['custom_report_group_name']; ?>
</a>
								 
								 
										 <ul class="sub-slide-menu" >
											 <?php $_from = $this->_tpl_vars['custom_report_group_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['r']):
?>
												 <li class="sub-slide-sub"><a class="sub-slide-item" href="custom_report.php?report_id=<?php echo $this->_tpl_vars['r']['id']; ?>
"><?php echo $this->_tpl_vars['r']['report_title']; ?>
</a></li>
											 <?php endforeach; endif; unset($_from); ?>
										 </ul>
									 </li>
								 <?php endforeach; endif; unset($_from); ?>
								 
								 								 <?php $_from = $this->_tpl_vars['available_custom_report_list']['nogroup']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['r']):
?>
								 <li class="sub-slide"><a href="custom_report.php?report_id=<?php echo $this->_tpl_vars['r']['id']; ?>
" class="sub-slide-menu__item" data-toggle="sub-slide"><?php echo $this->_tpl_vars['r']['report_title']; ?>
</a>
									
								 <?php endforeach; endif; unset($_from); ?>
							 <?php endif; ?>
						 </ul>
					 </li>
					 <?php endif; ?>
					 
					 <?php if ($this->_tpl_vars['config']['show_old_report']): ?>
						 <?php ob_start(); ?><?php echo ''; ?><?php if ($this->_tpl_vars['sessioninfo']['privilege']['REPORTS_SALES']): ?><?php echo '<li class="sub-slide"><a class="sub-slide-menu__item" href="sales_report.brand.php">'; ?><?php echo 'Daily Brand Sales Report</a></li><li class="sub-slide"><a class="sub-slide-menu__item" href="sales_report.vendor.php">'; ?><?php echo 'Daily Vendor Sales Report</a></li><li class="sub-slide"><a class="sub-slide-menu__item" href="sales_report.department.php">'; ?><?php echo 'Department Monthly Sales Report</a></li>'; ?><?php endif; ?><?php echo ''; ?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('report_html', ob_get_contents());ob_end_clean(); ?>
						 <?php if ($this->_tpl_vars['report_html']): ?>
						 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Old report</a>
								 <ul class="sub-slide-menu"><?php echo $this->_tpl_vars['report_html']; ?>
</ul>
							 </li>
						 <?php endif; ?>
					 <?php endif; ?>
			 
					 <?php if (isset ( $this->_tpl_vars['config']['custom_report'] )): ?>
						 <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['config']['custom_report'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
					 <?php endif; ?>
			 
					 <?php if ($this->_tpl_vars['sessioninfo']['id'] == 1 && $this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>
					 <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="/pivot.php?a=new"><span class="sub-side-menu__label">Create/Modify Reports</span></a>
					
						 
					 <?php endif; ?>
			 
					 <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PIVOT_SALES'] && $this->_tpl_vars['pivots']): ?>
					 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Sales report<i class="sub-angle fe fe-chevron-down"></i></a>
						 <ul class="sub-slide-menu">
							 <?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['pivots']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
							 <?php if ($this->_tpl_vars['pivots'][$this->_sections['i']['index']]['rpt_group'] == 'Sales'): ?><li class="sub-slide-sub"><a class="sub-side-item" href="/pivot.php?a=load&id=<?php echo $this->_tpl_vars['pivots'][$this->_sections['i']['index']]['id']; ?>
"><?php echo $this->_tpl_vars['pivots'][$this->_sections['i']['index']]['title']; ?>
</a></li><?php endif; ?>
							 <?php endfor; endif; ?>
						 </ul>
					 <?php endif; ?>
			 
					 <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PIVOT_OFFICER'] && $this->_tpl_vars['pivots']): ?>
					 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Officers reports</a>
						 <ul class="sub-slide-menu">
							 <?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['pivots']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
							 <?php if ($this->_tpl_vars['pivots'][$this->_sections['i']['index']]['rpt_group'] == 'Officer'): ?><li class="sub-slide-sub"><a class="sub-slide-item" href="/pivot.php?a=load&id=<?php echo $this->_tpl_vars['pivots'][$this->_sections['i']['index']]['id']; ?>
"><?php echo $this->_tpl_vars['pivots'][$this->_sections['i']['index']]['title']; ?>
</a></li><?php endif; ?>
							 <?php endfor; endif; ?>
						 </ul>
					 <?php endif; ?>
					 
					 <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PIVOT_MANAGEMENT'] && $this->_tpl_vars['pivots']): ?>
					 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Management reports</a>
						 <ul class="sub-slide-menu">
							 <?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['pivots']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
							 <?php if ($this->_tpl_vars['pivots'][$this->_sections['i']['index']]['rpt_group'] == 'Management'): ?><li class="sub-slide-sub"><a class="sub-slide-item" href="/pivot.php?a=load&id=<?php echo $this->_tpl_vars['pivots'][$this->_sections['i']['index']]['id']; ?>
"><?php echo $this->_tpl_vars['pivots'][$this->_sections['i']['index']]['title']; ?>
</a></li><?php endif; ?>
							 <?php endfor; endif; ?>
						 </ul>
					 <?php endif; ?>
					 
					 <?php if ($this->_tpl_vars['config']['monthly_closing'] && $this->_tpl_vars['sessioninfo']['privilege']['REPORTS_MONTHLY_CLOSING']): ?>
					 <li class="sub-slide"><a href="#" class="sub-slide-menu__item" data-toggle="sub-slide">Monthly closing</a>
						 <ul class="sub-slide-menu">
							 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.monthly_closing_stock_balance.php" )): ?>
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.monthly_closing_stock_balance.php">Monthly Closing Stock Balance Report by Department</a></li>
							 <?php endif; ?>
							 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.monthly_closing_stock_balance_summary.php" )): ?>
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.monthly_closing_stock_balance_summary.php">Monthly Closing Stock Balance Summary</a></li>
							 <?php endif; ?>
						 </ul>
					 <?php endif; ?>
			 
					 <?php if ($this->_tpl_vars['sessioninfo']['privilege']['STOCK_CHECK_REPORT'] && ! strstr ( $this->_tpl_vars['config']['hide_from_menu'] , 'STOCK_CHECK_REPORT' )): ?>
					 <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Stock Take</span><i class="sub-angle fe fe-chevron-down"></i></a>
						 <ul class="sub-slide-menu">
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="pivot.stockchk.php?a=list">Customize Reports</a></li>
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_check.php">Stock Take Summary</a></li>
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_take_variance_by_dept.php">Stock Take Variance by Dept Report</a></li>
							 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_take_variance.php">Stock Take Variance Report</a></li>
							 <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.stock_take_inquiry.php" )): ?>
								 <li class="sub-slide-sub"><a class="sub-slide-item" href="report.stock_take_inquiry.php">Stock Take Inquiry</a></li>
							 <?php endif; ?>
						 </ul>
					 <?php endif; ?>
					 <?php if ($this->_tpl_vars['sessioninfo']['level'] >= 500): ?>
					 <?php if ($this->_tpl_vars['sessioninfo']['privilege']['PO_REPORT']): ?>
					 <li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="vendor_po.summary.php"><span class="sub-side-menu__label">Vendor Purchase Ranking</span></a>
					 <?php endif; ?>
					 <?php endif; ?>
				
				</ul>	 
			</li>
			<?php endif; ?>
			 <!-- End of Report -->

				<!--report files end-->



			<!--Front end files start here-->

			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['POS_BACKEND'] || $this->_tpl_vars['sessioninfo']['privilege']['POS_VERIFY_SKU'] || $this->_tpl_vars['sessioninfo']['privilege']['POS_REPORT']): ?>
			<?php $this->assign('pos_checking', true); ?>
		<?php endif; ?>
		
		<?php if ($this->_tpl_vars['sessioninfo']['privilege']['CC_FINALIZE'] || $this->_tpl_vars['sessioninfo']['privilege']['CC_UNFINALIZE'] || $this->_tpl_vars['sessioninfo']['privilege']['CC_DEPOSIT']): ?>
			<?php $this->assign('cc_checking', true); ?>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['sessioninfo']['privilege']['FRONTEND_SETUP'] || $this->_tpl_vars['sessioninfo']['privilege']['PROMOTION'] || $this->_tpl_vars['pos_checking'] || $this->_tpl_vars['cc_checking'] || $this->_tpl_vars['sessioninfo']['privilege']['FRONTEND_PRINT_FULL_TAX_INVOICE']): ?>
		<li class="slide">
		<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-newspaper side-menu__icon" ></i><span class="side-menu__label">Front end</span><i class="angle fe fe-chevron-down"></i></a>
		<ul class="slide-menu">
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['FRONTEND_SETUP']): ?>
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Settings</span><i class="sub-angle fe fe-chevron-down"></i></a>
		
				<ul class="sub-slide-menu">
					<li class="sub-slide-sub"><a class="sub-slide-item" href="frontend.php">Counters Setup</a></li>
					<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999 && $this->_tpl_vars['sessioninfo']['is_arms_user'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/info.counter_configuration.php" )): ?>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="info.counter_configuration.php">Counter Setup Information</a><?php endif; ?>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="pos.settings.php">POS Settings</a></li>
					<?php if ($this->_tpl_vars['sessioninfo']['privilege']['FRONTEND_SET_CASHIER'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/front_end.cashier_setup.php" )): ?>
						<li class="sub-slide-sub"><a class="sub-slide-item" href="front_end.cashier_setup.php">Cashier Setup</a></li>
					<?php endif; ?>
				</ul>
			</li>
			<?php endif; ?>
			
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['PROMOTION']): ?>
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Promotion</span><i class="sub-angle fe fe-chevron-down"></i></a>
				<ul class="sub-slide-menu">
					<li class="sub-slide-sub"><a class="sub-slide-item" href="promotion.php">Create / Edit</a></li>
					<?php if ($this->_tpl_vars['sessioninfo']['privilege']['PROMOTION_APPROVAL']): ?>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="promotion_approval.php">Approval</a></li><?php endif; ?>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.promotion_summary.php">Promotion Summary</a></li>
					<li class="sub-slide-sub"><a class="sub-slide-item" href="report.promotion_result.php">Promotion Result</a></li>
										<li class="sub-slide-sub"><a class="sub-slide-item" href="report.mix_n_match_promotion_result.php">Mix and Match Promotion Result</a></li>
				</ul>
			</li>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['POS_BACKEND'] || $this->_tpl_vars['sessioninfo']['privilege']['CC_FINALIZE'] || $this->_tpl_vars['sessioninfo']['privilege']['CC_UNFINALIZE']): ?>
				<?php if ($this->_tpl_vars['config']['counter_collection_server']): ?>
					<?php if ($this->_tpl_vars['sessioninfo']['privilege']['POS_BACKEND']): ?>
						<li class="sub-slide"><a class="sub-slide-menu__item" href="javascript:void(open_from_dc('<?php echo $this->_tpl_vars['config']['counter_collection_server']; ?>
/sales_live.php?',<?php echo $this->_tpl_vars['sessioninfo']['id']; ?>
,<?php echo $this->_tpl_vars['sessioninfo']['branch_id']; ?>
, 'Sales Live'))"> Sales Live</a></li>
						<li class="sub-slide"><a class="sub-slide-menu__item" href="javascript:void(open_from_dc('<?php echo $this->_tpl_vars['config']['counter_collection_server']; ?>
/pos_live.php?',<?php echo $this->_tpl_vars['sessioninfo']['id']; ?>
,<?php echo $this->_tpl_vars['sessioninfo']['branch_id']; ?>
, 'POS Live'))"> Pos Live</a></li>
					<?php endif; ?>
					<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['POS_BACKEND'] || $this->_tpl_vars['sessioninfo']['privilege']['CC_FINALIZE'] || $this->_tpl_vars['sessioninfo']['privilege']['CC_UNFINALIZE'] )): ?>
						<li class="sub-slide"><a  class="sub-slide-menu__item" href="javascript:void(open_cc('<?php echo $this->_tpl_vars['config']['counter_collection_server']; ?>
',<?php echo $this->_tpl_vars['sessioninfo']['id']; ?>
,<?php echo $this->_tpl_vars['sessioninfo']['branch_id']; ?>
))">Counter Collection</a></li>
					<?php endif; ?>
				<?php else: ?>
					<?php if ($this->_tpl_vars['sessioninfo']['privilege']['POS_BACKEND']): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="pos_live.php"><span class="sub-side-menu__label">POS live</span></a>
						
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_monitoring.php" )): ?>
						<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="pos_monitoring.php"><span class="sub-side-menu__label">POS Monitoring (DEV)</span></a>
							
						<?php endif; ?>
						<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="sales_live.php"><span class="sub-side-menu__label">Sales live</span></a>

					<?php endif; ?>
					<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['POS_BACKEND'] || $this->_tpl_vars['sessioninfo']['privilege']['CC_FINALIZE'] || $this->_tpl_vars['sessioninfo']['privilege']['CC_UNFINALIZE'] )): ?>
					<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="counter_collection.php"><span class="sub-side-menu__label">Counter Collection</span></a>	
					
					<?php endif; ?>
				<?php endif; ?>
			<!--li><a href="collection_report.php">Collection Report</a></li-->
			<?php endif; ?>
						
			<!-- Deposit -->
			<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos.deposit_cancellation.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['CC_DEPOSIT']): ?>
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Deposit</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos.deposit_listing.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['CC_DEPOSIT']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="pos.deposit_listing.php">Deposit Listing</a></li>
						<?php endif; ?>
											</ul>
				</li>
			<?php endif; ?>
			
			<!-- Invalid SKU -->
			<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos.invalid_sku.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['POS_VERIFY_SKU']): ?>
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Invalid SKU</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos.invalid_sku.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['POS_VERIFY_SKU']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="pos.invalid_sku.php">Verify Invalid SKU</a></li>
						<?php endif; ?>
					</ul>
				</li>
			<?php endif; ?>
			
			<!-- Trade In -->
			<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos.trade_in.write_off.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['POS_TRADE_IN_WRITEOFF']): ?>
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Trade in</span><i class="sub-angle fe fe-chevron-down"></i></a>
					<ul class="sub-slide-menu">
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos.trade_in.write_off.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['POS_TRADE_IN_WRITEOFF']): ?>
							<li class="sub-slide-sub"><a class="sub-slide-item" href="pos.trade_in.write_off.php">Manage Trade In Write-Off</a></li>
						<?php endif; ?>
					</ul>
				</li>
			<?php endif; ?>
					
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['POS_REPORT']): ?>
			<li class="sub-slide"><a href="#" class="sub-side-menu__item" data-toggle="sub-slide">POS Report<i class="sub-angle fe fe-chevron-down"></i></a>
				<ul class="sub-slide-menu">
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">Transaction</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu-sub">
							<li><a class="sub-slide-item" href="pos_report.tran_details.php">Transaction Details</a></li>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.tran_details_item_listing.php" )): ?>
								<li><a class="sub-slide-item" href="pos_report.tran_details_item_listing.php">Transaction Details with Item Listing</a></li>
							<?php endif; ?>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.sku_tran_details.php" )): ?>
								<li><a class="sub-slide-item" href="pos_report.sku_tran_details.php">SKU Transaction Details</a></li>
							<?php endif; ?>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.return_item.php" )): ?>
								<li><a class="sub-slide-item" href="pos_report.return_item.php">POS Return Items Report</a></li>
							<?php endif; ?>
						</ul>
					</li>
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">Cashier</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu-sub">
							<li><a class="sub-slide-item"  href="pos_report.cashier_performance.php">Cashier Performance Report</a></li>
							<li><a class="sub-slide-item"  href="pos_report.cashier_unnormal_behaviour.php">Cashier Abnormal Behaviour Report</a></li>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.cashier_variance.php" )): ?>
								<li><a class="sub-slide-item"  href="pos_report.cashier_variance.php">Cashier Variance Report</a></li>
							<?php endif; ?>
													</ul>
					</li>
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">Counter collection</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu-sub">
							<li><a class="sub-slide-item" href="pos_report.counter_collection_below_cost.php">Counter Collection below Cost Report</a></li>
							<li><a class="sub-slide-item" href="report.counter_collection_sales_vs_category_sales.php">Counter Collection Sales vs Category Sales</a></li>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.daily_counter_collection.php" )): ?>
								<li><a class="sub-slide-item" href="report.daily_counter_collection.php">Daily Counter Collection Cash Denomination</a></li>
							<?php endif; ?>
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.payment_list.php" )): ?>
								<li><a class="sub-slide-item" href="pos_report.payment_list.php">Payment List Report</a></li>
							<?php endif; ?>
							
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.counter_collection_details.php" )): ?>
								<li><a class="sub-slide-item" href="pos_report.counter_collection_details.php">Counter Collection Details Report</a></li>
							<?php endif; ?>
							
							<?php if ($this->_tpl_vars['config']['counter_collection_enable_co2_module'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.counter_collection_co2.php" )): ?>
								<li><a class="sub-slide-item" href="pos_report.counter_collection_co2.php">Counter Collection CO2</a></li>
							<?php endif; ?>
	
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.cash_advance.php" )): ?>
								<li><a class="sub-slide-item" href="pos_report.cash_advance.php">Cash Advance Report</a></li>
							<?php endif; ?>
						</ul>
					</li>
					
										<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.cross_branch_deposit.php" ) || file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.cancel_deposit.php" ) || file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.in_out_deposit.php" )): ?>
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">Deposit</span><i class="sub-angle fe fe-chevron-down"></i></a>
							<ul class="sub-slide-menu-sub">
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.cross_branch_deposit.php" )): ?>
									<li><a class="sub-slide-item" href="pos_report.cross_branch_deposit.php">Cross Branch Deposit Report</a></li>
								<?php endif; ?>
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.cancel_deposit.php" )): ?>
									<li><a class="sub-slide-item" href="pos_report.cancel_deposit.php">Cancelled Deposit Report</a></li>
								<?php endif; ?>
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.in_out_deposit.php" )): ?>
									<li><a class="sub-slide-item" href="pos_report.in_out_deposit.php">Daily Deposit In/Out Report</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.trade_in.php" )): ?>
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">Trade In</span><i class="sub-angle fe fe-chevron-down"></i></a>
							<ul class="sub-slide-menu-sub">
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.trade_in.php" )): ?>
									<li><a class="sub-slide-item" href="pos_report.trade_in.php">Trade In Report</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					
					<?php if ($this->_tpl_vars['config']['enable_gst']): ?>
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">GST</span></a>
							<ul class="sub-slide-menu-sub">
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.counter_sales_gst_report.php" )): ?>
									<li class="sub-slide-item"><a href="pos_report.counter_sales_gst_report.php">Counter Sales GST Report</a></li>
								<?php endif; ?>
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.receipt_summary_gst_report.php" )): ?>
									<li class="sub-slide-item"><a href="pos_report.receipt_summary_gst_report.php">Receipt Summary GST Report</a></li>
								<?php endif; ?>
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.gst_credit_note_report.php" )): ?>
									<li class="sub-slide-item"><a href="pos_report.gst_credit_note_report.php">GST Credit Note Report</a></li>
								<?php endif; ?>
															</ul>
						</li>
					<?php endif; ?>
					
					<li class="sub-slide-sub"><a href="#" class="sub-side-menu_item sub-slide-item" data-toggle="sub-slide-sub"><span class="sub-side-menu_label">Service charge</span><i class="sub-angle fe fe-chevron-down"></i></a>
						<ul class="sub-slide-menu-sub">
							<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos_report.service_charge_summary.php" )): ?>
								<li><a class="sub-slide-item" href="pos_report.service_charge_summary.php">Service Charge Summary</a></li>
							<?php endif; ?>
						</ul>
					</li>
				</ul>
			</li>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['FRONTEND_PRINT_FULL_TAX_INVOICE'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos.print_full_tax_invoice.php" )): ?>
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="pos.print_full_tax_invoice.php"><span class="sub-side-menu__label">Print Full Tax Invoice</span></a> 
			
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['FRONTEND_EJOURNAL'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos.ejournal.php" )): ?>
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="pos.ejournal.php"><span class="sub-side-menu__label">E-Journal</span></a> 	
	
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['FRONTEND_AUDIT_LOG'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos.audit_log.php" )): ?>
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="pos.audit_log.php"><span class="sub-side-menu__label">Audit Log</span></a> 		
			
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['FRONTEND_ANNOUNCEMENT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/front_end.announcement.php" )): ?>
			<li class="sub-slide">
				<a class="sub-side-menu__item" data-toggle="sub-slide" href="front_end.announcement.php"><span class="sub-side-menu__label">POS Announcement</span></a> 		
		
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['FRONTEND_POPULAR_ITEMS_SETUP'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/pos.popular_items_listing_setup.php" )): ?>
			<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="pos.popular_items_listing_setup.php"><span class="sub-side-menu__label">Popular Items Listing Setup</span></a> 		

			<?php endif; ?>
		</ul>
		</li>
		<?php endif; ?>
	
		<?php if ($this->_tpl_vars['config']['enable_suite_device'] && ( $this->_tpl_vars['sessioninfo']['privilege']['SUITE_MANAGE_DEVICE'] )): ?>
			<li class="slide">
				<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-monitor side-menu__icon"></i><span class="side-menu__label">Suite</span><i class="angle fe fe-chevron-down"></i></a>
				<ul class="slide-menu">
						<?php if ($this->_tpl_vars['sessioninfo']['privilege']['SUITE_MANAGE_DEVICE'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/suite.manage_device.php" )): ?>
						<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Device</span><i class="sub-angle fe fe-chevron-down"></i></a>
									<ul class="sub-slide-menu">
										<li class="sub-slide-sub"><a class="sub-slide-item" href="suite.manage_device.php">Suite Device Setup</a></li>
									</ul>
								</li>
							<?php endif; ?>
							<?php if ($this->_tpl_vars['sessioninfo']['privilege']['SUITE_POS_DEVICE_MANAGEMENT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/suite.pos_device_management.php" )): ?>
							<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="suite.pos_device_management.php">POS Device Management</a></li>
							<?php endif; ?>
						
					
				</ul>
			</li>
		<?php endif; ?>
		
		<?php if ($this->_tpl_vars['config']['arms_marketplace_settings'] && ( $this->_tpl_vars['config']['arms_marketplace_settings']['branch_code'] == $this->_tpl_vars['BRANCH_CODE'] || $this->_tpl_vars['BRANCH_CODE'] == 'HQ' ) && ( $this->_tpl_vars['sessioninfo']['privilege']['MARKETPLACE_MANAGE_SKU'] || $this->_tpl_vars['sessioninfo']['privilege']['MARKETPLACE_SETTINGS'] || ( $this->_tpl_vars['config']['arms_marketplace_settings']['branch_code'] == $this->_tpl_vars['BRANCH_CODE'] && $this->_tpl_vars['sessioninfo']['privilege']['MARKETPLACE_LOGIN'] ) )): ?>
			<li class="slide">
				<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-cash-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Marketplace</span><i class="angle fe fe-chevron-down"></i></a>
				<ul class="slide-menu">
										
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/marketplace.settings.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MARKETPLACE_SETTINGS']): ?>
						<li class="slide-item"><a href="marketplace.settings.php">Settings</a></li>
					<?php endif; ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/marketplace.manage_sku.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['MARKETPLACE_MANAGE_SKU']): ?>
						<li class="slide-item"><a  href="marketplace.manage_sku.php">Manage SKU</a></li>
					<?php endif; ?>
				</ul>
			</li>
		<?php endif; ?>
		
		
		<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MKT']): ?>
		<li class="slide">
			<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-cash-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Marketing Tools</span><i class="angle fe fe-chevron-down"></i></a>
			<ul class="slide-menu">
				<li class="slide-item"><a  href="mkt_annual.php"> Annual Planner and Review</a>
				<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>
				<li class="slide-item"><a href="mkt_settings.php">&nbsp; Settings</a>
				<li class="slide-item"><a href="mkt0.php">&nbsp; Create New Offers</a>
				<?php endif; ?>
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
		<?php endif; ?>
		
		<?php if ($this->_tpl_vars['config']['enable_web_bridge'] && $this->_tpl_vars['sessioninfo']['privilege']['WB']): ?>
			<li class="slide">
				<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-cash-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Web Bridge</span><i class="angle fe fe-chevron-down"></i></a>
				<ul class="slide-menu">
					<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['WB_AP_TRANS_SETT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/web_bridge.ap_trans.settings.php" ) ) || ( $this->_tpl_vars['sessioninfo']['privilege']['WB_AP_TRANS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/web_bridge.ap_trans.php" ) )): ?>
						<li class="sub-slide">
							<a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">AP Trans</span><i class="sub-angle fe fe-chevron-down"></i></a>
						
							<ul class="sub-slide-menu">
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['WB_AP_TRANS_SETT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/web_bridge.ap_trans.settings.php" )): ?>
									<li class="sub-slide-sub"><a class="sub-slide-item" href="web_bridge.ap_trans.settings.php">Settings</a></li>
								<?php endif; ?>
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['WB_AP_TRANS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/web_bridge.ap_trans.php" )): ?>
									<li class="sub-slide-sub"><a class="sub-slide-item" href="web_bridge.ap_trans.php">AP Trans</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['WB_AR_TRANS_SETT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/web_bridge.ar_trans.settings.php" ) ) || ( $this->_tpl_vars['sessioninfo']['privilege']['WB_AR_TRANS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/web_bridge.ar_trans.php" ) )): ?>
						<li class="sub-slide">
							<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">AR Trans</span><i class="sub-angle fe fe-chevron-down"></i></a>
							<ul class="sub-slide-menu">
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['WB_AR_TRANS_SETT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/web_bridge.ar_trans.settings.php" )): ?>
									<li class="sub-slide-sub"><a class="sub-slide-item" href="web_bridge.ar_trans.settings.php">Settings</a></li>
								<?php endif; ?>				
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['WB_AR_TRANS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/web_bridge.ar_trans.php" )): ?>
									<li class="sub-slide-sub"><a class="sub-slide-item" href="web_bridge.ar_trans.php">AR Trans</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					<?php if (( $this->_tpl_vars['sessioninfo']['privilege']['WB_CC_TRANS_SETT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/web_bridge.cc_trans.settings.php" ) ) || ( $this->_tpl_vars['sessioninfo']['privilege']['WB_CC_TRANS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/web_bridge.cc_trans.php" ) )): ?>
						<li class="sub-slide">
							<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">CC Trans</span><i class="sub-angle fe fe-chevron-down"></i></a>
							<ul class="sub-slide-menu">
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['WB_CC_TRANS_SETT'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/web_bridge.cc_trans.settings.php" )): ?>
									<li class="sub-slide-sub"><a class="sub-slide-item" href="web_bridge.cc_trans.settings.php">Settings</a></li>
								<?php endif; ?>
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['WB_CC_TRANS'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/web_bridge.cc_trans.php" )): ?>
									<li class="sub-slide-sub"><a class="sub-slide-item" href="web_bridge.cc_trans.php">CC Trans</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
				</ul>
			</li>
		<?php endif; ?>

			<!--Front end files ends here-->



			<!--Miscellaneous files starts here-->

				<li class="slide">
					<a class="side-menu__item" data-toggle="slide" href="#"><i class="mdi mdi-cash-multiple side-menu__icon" style="margin-top: 0%; padding-top: 0%;"></i><span class="side-menu__label">Miscellaneous</span><i class="angle fe fe-chevron-down"></i></a>
					
						
						<ul class="slide-menu">
								<?php if ($this->_tpl_vars['sessioninfo']['level'] > 0): ?>
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['UPDATE_PROFILE']): ?>
								<li class="sub-slide"><a class="sub-side-menu__item" href="my_profile.php">Update My Profile</a><?php endif; ?>
								<?php if ($this->_tpl_vars['sessioninfo']['privilege']['VIEWLOG']): ?>
								<li class="sub-slide"><a class="sub-side-menu__item" href="viewlog.php">View Logs</a><?php endif; ?>
								<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 1000 && ( ! $this->_tpl_vars['config']['single_server_mode'] || $this->_tpl_vars['config']['show_server_status'] )): ?>
								<li class="sub-slide"><a class="sub-side-menu__item" href="server_status.php">Server Status</a><?php endif; ?>
								<?php endif; ?>
								<li class="sub-slide"><a class="sub-side-menu__item" href="/login.php?logout=1" onclick="return confirm('<?php echo $this->_tpl_vars['LANG']['CONFIRM_LOGOUT']; ?>
')">Logout</a>
								<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
									<?php if ($_SESSION['admin_session']): ?>
									<li class="sub-slide"><a class="sub-side-menu__item" href="/login.php?logout_as=1">Logout as <?php echo $this->_tpl_vars['sessioninfo']['u']; ?>
</a>
									<?php else: ?>
									<li class="sub-slide"><a class="sub-side-menu__item" href="#" onclick="return login_as();">Login as...</a>
									<?php endif; ?>
								<?php endif; ?>
								<li class="sub-slide"><a class="sub-side-menu__item" href="/front_end.check_code.php" target=_fe>Check Code</a>
								<li class="sub-slide"><a class="sub-side-menu__item" href="/price_check" target=_fe>Price Checker</a>
																<?php if (is_dir ( 'db' ) && $this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
																	<?php endif; ?>
								<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/eform.php" )): ?>
									<li class="sub-slide"><a class="sub-side-menu__item" href="#" class="submenu">eForm</a>          
									  <ul class="sub-slide-menu">
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['E_FORM_SETUP']): ?><li><a href="/eform.setup.php">Setup eForm</a></li><?php endif; ?>
										<li class="sub-slide-sub"><a class="sub-slide-item" href="/eform.php">My eForms</a></li>
										<?php if ($this->_tpl_vars['sessioninfo']['privilege']['E_FORM_APPROVAL']): ?>
										<li class="sub-slide-sub"><a class="sub-slide-item" href="/eform.approval.php">eForm Approval</a></li>
										<?php endif; ?>
									  </ul>          
									</li>
								<?php endif; ?>
								<li class="sub-slide"><a class="sub-side-menu__item" href="./ui/3of9/mrvcode39extma.ttf">Download Barcode Font</a>
						</ul>
					</li>
				

			<!--Miscellaneous files ends here-->

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
								<div class="dropdown  nav-item d-none d-md-flex">
									<div class="d-sm-none d-md-block">
										<?php if ($this->_tpl_vars['sessioninfo']): ?>
											Logged in as 
											<?php if ($_SESSION['admin_session']): ?>
												<?php echo $_SESSION['admin_session']['u']; ?>
 (now running as <?php echo $this->_tpl_vars['sessioninfo']['u']; ?>
 |)
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