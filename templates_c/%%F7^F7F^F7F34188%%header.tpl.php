<?php /* Smarty version 2.6.18, created on 2021-06-03 21:15:06
         compiled from header.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'header.tpl', 174, false),array('modifier', 'date_format', 'header.tpl', 327, false),)), $this); ?>
<?php if (! $this->_tpl_vars['no_header_footer']): ?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name='robots' content='noindex, nofollow'>
<?php echo smarty_function_config_load(array('file' => "site.conf"), $this);?>

<?php if (strpos ( $_SERVER['SERVER_NAME'] , 'arms-go' ) !== false): ?>
	<?php echo smarty_function_config_load(array('file' => "common-go.conf"), $this);?>

<?php else: ?>
	<?php echo smarty_function_config_load(array('file' => "common.conf"), $this);?>

<?php endif; ?>

<meta NAME="Description" CONTENT="<?php echo $this->_config[0]['vars']['META_DESCRIPTION']; ?>
">
<?php if (isset ( $this->_tpl_vars['mobile_scale'] )): ?>
	<meta name="viewport" content="width=device-width,initial-scale=<?php echo $this->_tpl_vars['mobile_scale']; ?>
;" />
<?php endif; ?>
<title><?php echo $this->_tpl_vars['BRANCH_CODE']; ?>
 | <?php echo $this->_config[0]['vars']['SITE_NAME']; ?>
 | <?php echo $this->_tpl_vars['PAGE_TITLE']; ?>
</title>
<?php if (dirname ( $_SERVER['REQUEST_URI'] ) != '/'): ?><base href="http<?php if ($_SERVER['HTTPS']): ?>s<?php endif; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>
/"><?php endif; ?>
<link rel="stylesheet" href="/templates/default.css?v=7" type="text/css">
<link rel="stylesheet" media="print" href="/templates/print.css" type="text/css">
<link rel="shortcut icon" href="/favicon.ico">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

<!-- Icons css -->
		<link href="../assets/css/icons.css" rel="stylesheet">

		<!--  Owl-carousel css-->
		<link href="../assets/plugins/owl-carousel/owl.carousel.css" rel="stylesheet" />

		<!-- P-scroll bar css-->
		<link href="../assets/plugins/perfect-scrollbar/p-scrollbar.css" rel="stylesheet" />

		<!--  Right-sidemenu css -->
		<link href="../assets/plugins/sidebar/sidebar.css" rel="stylesheet">

		<!-- Sidemenu css -->
		<link rel="stylesheet" href="../assets/css/sidemenu.css">

		<!-- Maps css -->
		<link href="../assets/plugins/jqvmap/jqvmap.min.css" rel="stylesheet">

		<!-- style css -->
		<link href="../assets/css/style.css" rel="stylesheet">
		<link href="../assets/css/style-dark.css" rel="stylesheet">

		<!---Skinmodes css-->
		<link href="../assets/css/skin-modes.css" rel="stylesheet" />
		

		
		

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

<body class="main-body app sidebar-mini" onmousemove = "mouse_trapper(event);" id="page-top">

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


<!-- Loader -->
		<!-- <div id="global-loader">
			<img src="../../assets/img/loader.svg" class="loader-img" alt="Loader">
		</div> -->
		<!-- /Loader -->

		<!-- Page -->
		<div class="page">

			<?php $this->assign('http_host', $_SERVER['HTTP_HOST']); ?>

			<?php if (! $this->_tpl_vars['no_menu_templates'] && ! $_SESSION[$this->_tpl_vars['http_host']]['is_remote']): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "menu.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?>


			

			<!-- main-content -->
			<div class="main-content app-content">

				<!-- main-header -->
				<div id ="top_nav_header" class="main-header sticky side-header nav nav-item">
					<div class="container-fluid">
						<div class="main-header-left ">
							<div class="responsive-logo">
								<a href="index.html"><img src="../../assets/img/brand/logo.png" class="logo-1" alt="logo"></a>
							

								<a href="index.html"><img src="../../assets/img/brand/favicon.png" class="logo-2" alt="logo"></a>
								
								
							</div>
							<div class="app-sidebar__toggle" data-toggle="sidebar">
								<a class="open-toggle" href="#"><i class="header-icon fe fe-align-left" ></i></a>
								<a class="close-toggle" href="#"><i class="header-icons fe fe-x"></i></a>
							</div>
							<div class="main-header-center ml-3 d-sm-none d-md-none d-lg-block">
								<p style="display: table-cell;" class="lead"><?php if (strpos ( $_SERVER['SERVER_NAME'] , 'arms-go' ) !== false): ?>
							ARMS&reg; GO Retail Management System &amp; Point Of Sale
						<?php elseif ($this->_tpl_vars['config']['consignment_modules']): ?>
							ARMS&reg; Consignment Retail Management System &amp; Point Of Sale
						<?php else: ?>
							<?php echo $this->_config[0]['vars']['SYSTEM_ID']; ?>

						<?php endif; ?></p>
							</div>
						</div>
						<div class="main-header-right">
							<i class="mdi mdi-calendar"></i> &nbsp; <?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%a %d %b, %I:%M %p") : smarty_modifier_date_format($_tmp, "%a %d %b, %I:%M %p")); ?>

							
						</div>
					</div>
				</div>
				<!-- /main-header -->
<div class=container-fluid>
<img src=ui/pixel.gif width=700 height=1><br>
<?php endif; ?>