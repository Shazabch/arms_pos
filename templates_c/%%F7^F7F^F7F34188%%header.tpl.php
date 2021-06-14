<?php /* Smarty version 2.6.18, created on 2021-06-11 18:27:21
         compiled from header.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'header.tpl', 172, false),)), $this); ?>
<?php if (! $this->_tpl_vars['no_header_footer']): ?><html>
<head>
<?php echo smarty_function_config_load(array('file' => "site.conf"), $this);?>

<?php if (strpos ( $_SERVER['SERVER_NAME'] , 'arms-go' ) !== false): ?>
	<?php echo smarty_function_config_load(array('file' => "common-go.conf"), $this);?>

<?php else: ?>
	<?php echo smarty_function_config_load(array('file' => "common.conf"), $this);?>

<?php endif; ?>
<meta charset="UTF-8">
<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta NAME="Description" CONTENT="<?php echo $this->_config[0]['vars']['META_DESCRIPTION']; ?>
">
<title><?php echo $this->_tpl_vars['BRANCH_CODE']; ?>
 | <?php echo $this->_config[0]['vars']['SITE_NAME']; ?>
 | <?php echo $this->_tpl_vars['PAGE_TITLE']; ?>
</title>
<?php if (dirname ( $_SERVER['REQUEST_URI'] ) != '/'): ?><base href="http<?php if ($_SERVER['HTTPS']): ?>s<?php endif; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>
/"><?php endif; ?>
<!-- <link rel="stylesheet" href="/templates/default.css?v=7" type="text/css">
<link rel="stylesheet" media="print" href="/templates/print.css" type="text/css">
<link rel="shortcut icon" href="/favicon.ico">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet"> -->

		<!-- Icons css -->
		<link href="../assets/css/icons.css" rel="stylesheet">

		<!--  Owl-carousel css-->
		<link href="../assets/plugins/owl-carousel/owl.carousel.css" rel="stylesheet" />

		<!--  Custom Scroll bar-->
		<link href="../assets/plugins/mscrollbar/jquery.mCustomScrollbar.css" rel="stylesheet"/>

		<!--  Right-sidemenu css -->
		<link href="../assets/plugins/sidebar/sidebar.css" rel="stylesheet">

		

		<!-- Maps css -->
		<link href="../assets/plugins/jqvmap/jqvmap.min.css" rel="stylesheet">

		<!-- style css -->
		<link href="../assets/css/style.css" rel="stylesheet">
		<link href="../assets/css/style-dark.css" rel="stylesheet">

		<!-- Custom css -->
		<link href="../assets/custom-css/custom.css" rel="stylesheet">

		<!---Skinmodes css-->
		<link href="../assets/css/skin-modes.css" rel="stylesheet" />

		<!--- Animations css-->
		<link href="../../assets/css/animate.css" rel="stylesheet">
		
		

<script src="/js/prototype.js" language=javascript type="text/javascript"></script>
<script src="/js/scriptaculous.js" language=javascript type="text/javascript"></script>
<script src="/js/sorttable.js?v=2" type="text/javascript"></script>
<script src="/js/forms.js?v=28" language=javascript type="text/javascript"></script>
<script language="javascript" type="text/javascript">
	<?php if ($this->_tpl_vars['config']['arms_currency']['code']): ?>ARMS_CURRENCY['code'] = "<?php echo $this->_tpl_vars['config']['arms_currency']['code']; ?>
"; <?php endif; ?>
	<?php if ($this->_tpl_vars['config']['arms_currency']['symbol']): ?>ARMS_CURRENCY['symbol'] = "<?php echo $this->_tpl_vars['config']['arms_currency']['symbol']; ?>
"; <?php endif; ?>
	<?php if ($this->_tpl_vars['config']['arms_currency']['name']): ?>ARMS_CURRENCY['name'] = '<?php echo $this->_tpl_vars['config']['arms_currency']['name']; ?>
';<?php endif; ?>
	<?php if ($this->_tpl_vars['config']['arms_currency']['country']): ?>ARMS_CURRENCY['country'] = '<?php echo $this->_tpl_vars['config']['arms_currency']['country']; ?>
';<?php endif; ?>
	<?php if ($this->_tpl_vars['config']['arms_currency']['rounding']): ?>ARMS_CURRENCY['rounding'] = '<?php echo $this->_tpl_vars['config']['arms_currency']['rounding']; ?>
';<?php endif; ?>
</script>

</head>

<body class="main-body" onmousemove = "mouse_trapper(event);" id="top">

<div id=curtain onclick="default_curtain_clicked()" style="position:absolute;display:none;z-index:9999;background:#fff;opacity:0.1;"></div>
<div id="curtain2" style="position:absolute;display:none;z-index:9999;background:#000;opacity:0.1;"></div>
<div id="div_global_wait_popup" style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
	<p align="center" id="div_global_wait_popup_content">
		Please wait..
		<br /><br />
		<img src="ui/clock.gif" border="0" />
	</p>
</div>

<div id="div_top_ajax_loading" style="position:fixed;display:none;background:#ff9;opacity:0.6;top:0px;padding:5px 10px;font-weight:bold;z-index:10000;">
	<img src="/ui/alarm.png" align="absmiddle" />
	Loading…
</div>


<!-- Item Details -->
<div id="div_item_details_popup" style="display:none;width:750px;height:450px;border: 3px solid rgb(0, 0, 0);padding: 10px;background:rgb(255, 255, 255) none repeat scroll 0% 0%;
position:absolute;z-index:10001;" class="curtain_popup">
	<div style="float:right;"><img onclick="GLOBAL_MODULE.hide_item_details();" src="/ui/closewin.png" /></div>
	<div id="div_item_details_popup_content">
	</div>
</div>
<!-- End of Item Details-->

<?php if ($this->_tpl_vars['config']['gpm_customer_name'] && $this->_tpl_vars['sessioninfo'] && ! $this->_tpl_vars['no_menu_templates']): ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.gpm_broadcast_message.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php endif; ?>


		<div class="page">

			<?php $this->assign('http_host', $_SERVER['HTTP_HOST']); ?>


			
			<?php if ($this->_tpl_vars['sessioninfo']): ?>
			<!-- main-header opened -->
			<div class="main-header nav nav-item hor-header">
				<div class="container">
					<div class="main-header-left ">
						<a class="animated-arrow hor-toggle horizontal-navtoggle"><span></span></a><!-- sidebar-toggle-->
						<a class="header-brand" href="index.html">
							<img src="../../assets/img/brand/logo-white.png" class="desktop-dark">
							<img src="../../assets/img/brand/logo.png" class="desktop-logo">
							<img src="../../assets/img/brand/favicon.png" class="desktop-logo-1">
							<img src="../../assets/img/brand/favicon-white.png" class="desktop-logo-dark">
						</a>
						<div class="main-header-center  ml-4">
							<input class="form-control" placeholder="Search for anything..." type="search"><button class="btn"><i class="fe fe-search"></i></button>
						</div>
					</div><!-- search -->
					<div class="main-header-right">
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
												<svg xmlns="http://www.w3.org/2000/svg" class="header-icon-svgs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
											</button>
										</span>
									</div>
								</form>
							</div>
							<div class="nav-item full-screen fullscreen-button">
								<a class="new nav-link full-screen-link" href="#"><svg xmlns="http://www.w3.org/2000/svg" class="header-icon-svgs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg></a>
							</div>
							<div class="dropdown main-profile-menu nav nav-item nav-link">
								<a class="profile-user d-flex" href=""><img alt="" src="../../assets/img/faces/6.jpg"></a>
								<div class="dropdown-menu">
									<div class="main-header-profile bg-primary p-3">
										<div class="d-flex wd-100p">
											<div class="main-img-user"><img alt="" src="../../assets/img/faces/6.jpg" class=""></div>
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
							<div class="dropdown main-header-message right-toggle">
								<a class="nav-link pr-0" data-toggle="sidebar-right" data-target=".sidebar-right">
									<svg xmlns="http://www.w3.org/2000/svg" class="header-icon-svgs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /main-header -->
			<?php if (! $this->_tpl_vars['no_menu_templates'] && ! $_SESSION[$this->_tpl_vars['http_host']]['is_remote']): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "menu.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?>
			<?php endif; ?>

<?php endif; ?>