<?php /* Smarty version 2.6.18, created on 2021-06-11 15:52:15
         compiled from menu.tpl */ ?>
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
<?php if ($this->_tpl_vars['sessioninfo']): ?>

			<li aria-haspopup="true"><a href="home.php" class=""><i class="fas fa-tachometer-alt"></i> Home</a></li>
			<li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-tachometer-alt"></i> Dashboard<i class="fe fe-chevron-down horizontal-icon"></i></a>
				<ul class="sub-menu">
					<li aria-haspopup="true"><a href="home.php" class="slide-item"> Home</a></li>
					<li aria-haspopup="true"><a href="product-details.html" class="slide-item">Go To Branch</a></li>
				</ul>
			</li>

	<?php if ($this->_tpl_vars['sessioninfo']['privilege']['USERS_ADD'] || $this->_tpl_vars['sessioninfo']['privilege']['USERS_MNG'] || $this->_tpl_vars['sessioninfo']['privilege']['USERS_ACTIVATE'] || $this->_tpl_vars['sessioninfo']['privilege']['MST_APPROVAL'] || $this->_tpl_vars['sessioninfo']['privilege']['POS_IMPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['SKU_EXPORT'] || $this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
	<!-- Administrator -->
	<li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-tachometer-alt"></i> Administrator <i class="fe fe-chevron-down horizontal-icon"></i></a>
		<ul class="sub-menu">
			<li aria-haspopup="true"><a href="" class="slide-item">O level</a></li>
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['USERS_MNG'] || $this->_tpl_vars['sessioninfo']['privilege']['USERS_ACTIVATE']): ?>
				<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Users</a>
					<ul class="sub-menu">
						<?php if ($this->_tpl_vars['sessioninfo']['privilege']['USERS_ADD']): ?><li aria-haspopup="true"><a href="users.php?t=create" class="slide-item">Create Profile</a></li><?php endif; ?>
						<?php if ($this->_tpl_vars['sessioninfo']['privilege']['USERS_ACTIVATE']): ?><li aria-haspopup="true"><a href="users.php?t=update" class="slide-item">Update Profile</a></li><?php endif; ?>
						<?php if ($this->_tpl_vars['sessioninfo']['level'] == 500 || $this->_tpl_vars['sessioninfo']['level'] >= 9999): ?><li aria-haspopup="true"><a href="admin.inactive_user.php" class="slide-item">No-Activity User Report</a></li><?php endif; ?>
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/users.application.php" ) && $this->_tpl_vars['sessioninfo']['privilege']['USERS_EFORM']): ?>
						<li aria-haspopup="true" class="slide-item sub-menu-sub"><a href="#">User Application E-Form</a>
							<ul class="sub-menu">
								<?php if ($this->_tpl_vars['config']['single_server_mode'] || ( ! $this->_tpl_vars['config']['single_server_mode'] && $this->_tpl_vars['sessioninfo']['branch_id'] == 1 )): ?><li aria-haspopup="true"><a  href="users.application.php?a=generate_code" class="slide-item">Generate QR Code</a></li><?php endif; ?>
								<li aria-haspopup="true"><a href="users.application.php?a=application_list" class="slide-item">Application List</a></li>
							</ul>
						</li>
						<?php endif; ?>
					</ul>
				</li>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_APPROVAL']): ?><li aria-haspopup="true"><a href="approval_flow.php" class="slide-item">Approval Flows</a></li><?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999 && $this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>
			<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Selling Price</a>
				<ul class="sub-menu">
					<li aria-haspopup="true"><a href="admin.copy_selling.php" class="slide-item">Copy Selling Price</a></li>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_selling.php" )): ?>
					<li aria-haspopup="true"><a href="admin.import_selling.php" class="slide-item">Import Selling Price</a></li>
					<?php endif; ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.update_price_type.php" )): ?>
					<li aria-haspopup="true"><a href="admin.update_price_type.php" class="slide-item">Update Price Type</a></li>
					<?php endif; ?>
				</ul>
			</li>			
			<?php endif; ?>
			<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && $this->_tpl_vars['sessioninfo']['level'] >= 9999 && ( $this->_tpl_vars['sessioninfo']['privilege']['ADMIN_UPDATE_SKU_MASTER_COST'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.update_sku_master_cost.php" ) )): ?>
				<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Cost Price</a>
					<ul class="sub-menu">
						<li aria-haspopup="true"><a href="admin.update_sku_master_cost.php" class="slide-item">Update SKU Master Cost</a></li>
					</ul>
				</li>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999 && $this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>
				<li aria-haspopup="true"><a href="admin.sku_block.php" class="slide-item">Block/Ublock in SKU in PO (CSV)</a></li>
			<?php endif; ?>

			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['SKU_EXPORT'] || $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['POS_IMPORT'] || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_SKU'] || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_VENDOR'] || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_BRAND'] || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_DEBTOR'] || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_UOM'] || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_DEACTIVATE_SKU']): ?>
			<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Import / Export</a>
				<ul class="sub-menu">
				<?php if ($this->_tpl_vars['sessioninfo']['privilege']['SKU_EXPORT']): ?>
					<li aria-haspopup="true"><a class="slide-item" href="admin.sku_export.php">Export SKU Items</a>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.weightcode_export.php" ) && ! $this->_tpl_vars['config']['consignment_modules']): ?>
						<li aria-haspopup="true"><a class="slide-item" href="admin.weightcode_export.php">Export Weighing Scale Items</a></li>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['POS_IMPORT']): ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.export_points.php" )): ?><li aria-haspopup="true"><a class="slide-item" href="admin.export_points.php">Export Member Points</a></li><?php endif; ?>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['POS_IMPORT']): ?>
										<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_pos_sales.php" )): ?>
					    					<?php endif; ?>
		  			<li aria-haspopup="true"><a class="slide-item" href="admin.stockchk_import.php">Import Stock Take</a>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['config']['sku_application_require_multics'] && ( $this->_tpl_vars['sess']['level'] == 500 || $this->_tpl_vars['sessioninfo']['level'] >= 9999 )): ?>
				<li aria-haspopup="true"><a class="slide-item" href="admin.update_dat.php">Update Multics DAT files</a>
				<?php endif; ?>
	            <?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['POS_IMPORT']): ?>
	                <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_member_points.php" )): ?>
	                    <li aria-haspopup="true"><a class="slide-item" href="admin.import_member_points.php">Import Member Points</a></li>
	                <?php endif; ?>
	                <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_members.php" )): ?>
	                    <li aria-haspopup="true"><a class="slide-item" href="admin.import_members.php">Import Members</a></li>
	                <?php endif; ?>
	                <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.preactivate_member_cards.php" )): ?>
	                    <li aria-haspopup="true"><a class="slide-item" href="admin.preactivate_member_cards.php">Pre-activate Member Cards</a></li>
	                <?php endif; ?>
	            <?php endif; ?>
				
				<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && ( $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_SKU'] )): ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_sku.php" )): ?>
	                    <li aria-haspopup="true"><a class="slide-item" href="admin.import_sku.php">Import SKU</a></li>
	                <?php endif; ?>
				<?php endif; ?>
				
				<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && ( $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_VENDOR'] )): ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_vendor.php" )): ?>
	                    <li aria-haspopup="true"><a class="slide-item" href="admin.import_vendor.php">Import Vendor</a></li>
	                <?php endif; ?>
				<?php endif; ?>
				
				<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && ( $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_BRAND'] )): ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_brand.php" )): ?>
	                    <li aria-haspopup="true"><a class="slide-item" href="admin.import_brand.php">Import Brand</a></li>
	                <?php endif; ?>
				<?php endif; ?>
	            
	            <?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && ( $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_DEBTOR'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_debtor.php" )): ?>
					<li aria-haspopup="true"><a class="slide-item" href="admin.import_debtor.php">Import Debtor</a></li>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && ( $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_UOM'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.import_uom.php" )): ?>
	                 <li aria-haspopup="true"><a class="slide-item" href="admin.import_uom.php">Import UOM</a></li>
	            <?php endif; ?>
				<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && ( $this->_tpl_vars['sessioninfo']['level'] >= 9999 || $this->_tpl_vars['sessioninfo']['privilege']['ALLOW_IMPORT_DEACTIVATE_SKU'] ) && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.deactivate_sku.php" )): ?>
	                 <li aria-haspopup="true"><a class="slide-item" href="admin.deactivate_sku.php">Deactivate SKU by CSV</a></li>
	            <?php endif; ?>
				</ul>
			</li>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999 && $this->_tpl_vars['BRANCH_CODE'] == 'HQ' && $this->_tpl_vars['config']['show_tracker']): ?>
			    <li aria-haspopup="true"><a class="slide-item" href="admin.arms_tracker.php">ARMS Request Tracker</a>
			<?php endif; ?>
			<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.monthly_closing.php" ) && $this->_tpl_vars['config']['monthly_closing'] && $this->_tpl_vars['sessioninfo']['privilege']['ADMIN_MONTHLY_CLOSING']): ?>
				<li  aria-haspopup="true" class="sub-menu-sub"><a>Monthly Closing</a>
					<ul class="sub-menu">
						<li aria-haspopup="true"><a class="slide-item" href="admin.monthly_closing.php">Monthly Closing</a></li>
						<li aria-haspopup="true"><a class="slide-item" href="admin.monthly_closing.php?a=show_closed_month">Monthly Closing History</a></li>
					</ul>
				</li>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
				<li aria-haspopup="true"><a class="slide-item" href="admin.update_log.php">System Update log</a>
				<li aria-haspopup="true"><a class="slide-item" href="sales_target.php">Sales Target</a>
				<li arai-haspopup="true" class="sub-menu-sub"><a href="#">Settings</a>
					<ul class="sub-menu">
						<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.settings.php" )): ?>
						<li aria-haspopup="true"><a class="slide-item" href="admin.settings.php?file=color.txt">Edit Colour</a>
						<li aria-haspopup="true"><a class="slide-item" href="admin.settings.php?file=size.txt">Edit Size</a>
						<?php endif; ?>
												<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.upload_logo.php" )): ?>
						<li aria-haspopup="true"><a class="slide-item" href="admin.upload_logo.php">Edit Logo Settings</a>
						<?php endif; ?>
					</ul>
				</li>
								<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ' && $this->_tpl_vars['sessioninfo']['id'] == 1): ?>
				    <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Server Management</a>
				        <ul class="sub-menu">
				            <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.config_manager.php" )): ?>
				                <li aria-haspopup="true"><a class="slide-item" href="admin.config_manager.php">Config Manager</a></li>
				            <?php endif; ?>
				            <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.privilege_manager.php" )): ?>
				                <li aria-haspopup="true"><a class="slide-item" href="admin.privilege_manager.php">Privilege Manager</a></li>
				            <?php endif; ?>
				            				        </ul>
					</li>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['config']['enable_gst'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/masterfile_gst_settings.php" ) && $this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
					<li aria-haspopup="true"><a class="slide-item" href="masterfile_gst_settings.php">GST Settings</a></li>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['config']['enable_tax'] && $this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
				<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Tax</a>
					<ul class="sub-menu">
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.tax_settings.php" )): ?>
						<li aria-haspopup="true"><a class="slide-item" href="admin.tax_settings.php">Tax Settings</a></li>
					<?php endif; ?>
					<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.tax_listing.php" )): ?>
						<li aria-haspopup="true"><a class="slide-item" href="admin.tax_listing.php">Tax Listing</a></li>
					<?php endif; ?>
					</ul>
				</li>
			<?php endif; ?>
						<?php if ($this->_tpl_vars['config']['foreign_currency'] && ( $this->_tpl_vars['sessioninfo']['privilege']['ADMIN_FOREIGN_CURRENCY_RATE_UPDATE'] )): ?>
				<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Foreign Currency</a>
					<ul>
						<?php if ($this->_tpl_vars['sessioninfo']['privilege']['ADMIN_FOREIGN_CURRENCY_RATE_UPDATE'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/admin.foreign_currency.rate.php" )): ?>
							<li aria-haspopup="true"><a class="slide-item" href="admin.foreign_currency.rate.php">Currency Rate Table</a></li>
						<?php endif; ?>
					</ul>
				</li>
			<?php endif; ?>
					




		</ul>
	</li>	
	<?php endif; ?>
<!-- /Administrator -->
<?php endif; ?>
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