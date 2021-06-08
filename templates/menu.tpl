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



{if $sessioninfo}
<!--Horizontal-main -->
			<div class="sticky">
				<div class="horizontal-main hor-menu clearfix side-header">
					<div class="horizontal-mainwrapper container clearfix">
						<!--Nav-->
						<nav class="horizontalMenu clearfix">
							<ul class="horizontalMenu-list">
								<li aria-haspopup="true"><a href="home.php" class=""><i class="fas fa-tachometer-alt"></i> Home</a></li>
								<li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-tachometer-alt"></i> Dashboard<i class="fe fe-chevron-down horizontal-icon"></i></a>
									<ul class="sub-menu">
										<li aria-haspopup="true"><a href="home.php" class="slide-item"> Home</a></li>
										<li aria-haspopup="true"><a href="product-details.html" class="slide-item">Go To Branch</a></li>
									</ul>
								</li>
								<li aria-haspopup="true"><a href="#" class="sub-icon"><svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M6.26 9L12 13.47 17.74 9 12 4.53z" opacity=".3"/><path d="M19.37 12.8l-7.38 5.74-7.37-5.73L3 14.07l9 7 9-7zM12 2L3 9l1.63 1.27L12 16l7.36-5.73L21 9l-9-7zm0 11.47L6.26 9 12 4.53 17.74 9 12 13.47z"/></svg> Administrator<i class="fe fe-chevron-down horizontal-icon"></i></a>
									<div class="horizontal-megamenu clearfix">
										<div class="container">
											<div class="mega-menubg hor-mega-menu">
												<div class="row">
													<div class="col-lg-3 col-md-12 col-xs-12 link-list">

														<ul>
															<li><h3 class="fs-14 font-weight-bold mb-1 mt-2">Users</h3></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Create Profile</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">update Profile</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">No Activity User Report</a></li>
															<li><h3 class="fs-14 font-weight-bold mb-1 mt-2">User Application E-Form</h3></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Generate QR Code</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Application List</a></li>
															<li><h3 class="fs-14 font-weight-bold mb-1 mt-2">Others</h3></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Approval Flows</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">ARMS Request Tracker</a></li>


														</ul>
													</div>
													<div class="col-lg-3 col-md-12 col-xs-12 link-list">
														<ul>
															<li aria-haspopup="true"><a href="" class="slide-item">System Update Log</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Sales Target</a></li>
															<li><h3 class="fs-14 font-weight-bold mb-1 mt-2">Selling Price</h3></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Copy Selling Price</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Import Selling Price</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Update Price Type</a></li>
															<li><h3 class="fs-14 font-weight-bold mb-1 mt-2">Cost Price</h3></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Update SKU Master</a></li>
															<li><h3 class="fs-14 font-weight-bold mb-1 mt-2">SKU</h3></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Block / Unblock SKU in PO (csv)</a></li>
														</ul>
													</div>
													<div class="col-lg-3 col-md-12 col-xs-12 link-list">
														<ul>
															<li><h3 class="fs-14 font-weight-bold mb-1 mt-2">Import / Export</h3></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Export SKU Items</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Export Weighing Scale Items</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Export Member Points</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Import Stock Take</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Import Member Points</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Import Members</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Pre-activate Member Cards</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Import SKU</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Import Vendor</a></li>
														</ul>
													</div>
													<div class="col-lg-3 col-md-12 col-xs-12 link-list">
														<ul>
															<li aria-haspopup="true"><a href="" class="slide-item">Import Brand</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Import Debtor</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Import UOM</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Deactivate SKU by CSV</a></li>
															<li><h3 class="fs-14 font-weight-bold mb-1 mt-2">Settings</h3></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Edit Colour</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Edit Size</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Edit Logo Settings</a></li>
															<li><h3 class="fs-14 font-weight-bold mb-1 mt-2">Server Management</h3></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Config Manager</a></li>
															<li aria-haspopup="true"><a href="" class="slide-item">Privilege Manager</a></li>
														</ul>
													</div>
												</div>
											</div>
										</div>
									</div>
								</li>
								<li aria-haspopup="true"><a href="#" class="sub-icon"><svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" ><path d="M0 0h24v24H0z" fill="none"/><path d="M12 4c-4.41 0-8 3.59-8 8s3.59 8 8 8c.28 0 .5-.22.5-.5 0-.16-.08-.28-.14-.35-.41-.46-.63-1.05-.63-1.65 0-1.38 1.12-2.5 2.5-2.5H16c2.21 0 4-1.79 4-4 0-3.86-3.59-7-8-7zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 10 6.5 10s1.5.67 1.5 1.5S7.33 13 6.5 13zm3-4C8.67 9 8 8.33 8 7.5S8.67 6 9.5 6s1.5.67 1.5 1.5S10.33 9 9.5 9zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 6 14.5 6s1.5.67 1.5 1.5S15.33 9 14.5 9zm4.5 2.5c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5.67-1.5 1.5-1.5 1.5.67 1.5 1.5z" opacity=".3"/><path d="M12 2C6.49 2 2 6.49 2 12s4.49 10 10 10c1.38 0 2.5-1.12 2.5-2.5 0-.61-.23-1.21-.64-1.67-.08-.09-.13-.21-.13-.33 0-.28.22-.5.5-.5H16c3.31 0 6-2.69 6-6 0-4.96-4.49-9-10-9zm4 13h-1.77c-1.38 0-2.5 1.12-2.5 2.5 0 .61.22 1.19.63 1.65.06.07.14.19.14.35 0 .28-.22.5-.5.5-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.14 8 7c0 2.21-1.79 4-4 4z"/><circle cx="6.5" cy="11.5" r="1.5"/><circle cx="9.5" cy="7.5" r="1.5"/><circle cx="14.5" cy="7.5" r="1.5"/><circle cx="17.5" cy="11.5" r="1.5"/></svg> Advanced UI <i class="fe fe-chevron-down horizontal-icon"></i></a>
									<ul class="sub-menu">
										<li aria-haspopup="true"><a href="blog.html" class="slide-item">Blog</a></li>
										<li aria-haspopup="true" class="sub-menu-sub"><a href="#">Submenu</a>
											<ul class="sub-menu">
												<li aria-haspopup="true"><a href="#" class="slide-item">Submenu-01</a></li>
												<li aria-haspopup="true" class="slide-item sub-menu-sub"><a href="#">Submenu-02</a>
													<ul class="sub-menu">
														<li aria-haspopup="true"><a href="#" class="slide-item">SubmenuLevel-01</a></li>
														<li aria-haspopup="true"><a href="#" class="slide-item">SubmenuLevel-02</a></li>
														<li aria-haspopup="true"><a href="#" class="slide-item">SubmenuLevel-02</a></li>
													</ul>
												</li>
												<li aria-haspopup="true"><a href="form-layouts.html" class="slide-item">Submenu-03</a></li>
											</ul>
										</li>
									</ul>
								</li>
								<li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-tachometer-alt"></i> E-Commerce<i class="fe fe-chevron-down horizontal-icon"></i></a>
									<ul class="sub-menu">
										<li aria-haspopup="true"><a href="products.html" class="slide-item"> Products</a></li>
										<li aria-haspopup="true"><a href="product-details.html" class="slide-item">Product-Details</a></li>
										<li aria-haspopup="true"><a href="product-cart.html" class="slide-item">Shopping Cart</a></li>
									</ul>
								</li>
								<li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-tachometer-alt"></i> E-Commerce<i class="fe fe-chevron-down horizontal-icon"></i></a>
									<ul class="sub-menu">
										<li aria-haspopup="true"><a href="products.html" class="slide-item"> Products</a></li>
										<li aria-haspopup="true"><a href="product-details.html" class="slide-item">Product-Details</a></li>
										<li aria-haspopup="true"><a href="product-cart.html" class="slide-item">Shopping Cart</a></li>
									</ul>
								</li>
							</ul>
						</nav>
						<!--Nav-->
					</div>
				</div>
			</div>
			<!--Horizontal-main -->

			<!-- main-content opened -->
			<div class="main-content horizontal-content">

				<!-- container opened -->
				<div class="container">

{/if}