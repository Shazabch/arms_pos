{*
10/11/2011 11:27:32 AM Justin
- Added to include "forms.js".
- Removed js function to put it into forms.js.

11/23/2011 10:44:43 AM Justin
- Added new meta tag "MobileOptimized" to allow windows mobile phone to have 240 width.

11/23/2011 10:44:43 AM Justin
- Changed the width from 240 into 225.

8/17/2012 2:49 PM Justin
- Enhanced to fix the font size become Arial.

3/18/2015 4:30 PM Justin
- Enhanced to add base tag.

10/22/2020 5:20 PM Rayleen
- Added color to header table and width to table row

11/09/2020 4:45 PM Sheila
- Added pda.css and meta link for IOS display

1/26/2020 9:38 AM William
- Change pda.css version to 1.
*}

{if !$no_header_footer}
{*<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">*}
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		{config_load file="site.conf"}
		{config_load file="common.conf"}
		<meta NAME="Description" CONTENT="{#META_DESCRIPTION#}">
		<meta http-equiv="pragma" content="no-cache" />
		{if dirname($smarty.server.REQUEST_URI) ne '/'}<base href="http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.HTTP_HOST}/pda/">{/if}
		<title>{$BRANCH_CODE} | {#SITE_NAME#} | {$PAGE_TITLE}</title>
		<!-- Old Theme -->
		<!-- <link rel="stylesheet" href="{#SITE_CSS#}" type="text/css">
		<link rel="stylesheet" href="/templates/pda.css?v=1" type="text/css">
		<link rel="shortcut icon" href="/favicon.ico">

		<script src="include/jquery.js" language=javascript type="text/javascript"></script>
		<script src="include/forms.js" language=javascript type="text/javascript"></script>
		{literal}
		<style>
		.small input, .small select { font: 10px Arial !important; }
		</style>
		{/literal} -->
		<!-- /Old Theme -->

		<!-- Favicon -->
		<link rel="icon" href="../../assets/img/brand/favicon.png" type="image/x-icon"/>

		<!-- Icons css -->
		<link href="../../assets/css/icons.css" rel="stylesheet">

		<!-- Internal Select2 css -->
		<link href="../../assets/plugins/select2/css/select2.min.css" rel="stylesheet">

		<!--  Right-sidemenu css -->
		<link href="../../assets/plugins/sidebar/sidebar.css" rel="stylesheet">

		<!-- P-scroll bar css-->
		<link href="../../assets/plugins/perfect-scrollbar/p-scrollbar.css" rel="stylesheet" />

		<!--  Left-Sidebar css -->
		<link rel="stylesheet" href="../../assets/css/sidemenu.css">

		<!--- Style css --->
		<link href="../../assets/css/style.css" rel="stylesheet">

		<!--- Dark-mode css --->
		<link href="../../assets/css/style-dark.css" rel="stylesheet">

		<!---Skinmodes css-->
		<link href="../../assets/css/skin-modes.css" rel="stylesheet" />

		<!--- Animations css-->
		<link href="../../assets/css/animate.css" rel="stylesheet">

		<!-- Custom css -->
		<link href="../../assets/custom-css/custom.css" rel="stylesheet">


</head>

<body class="main-body">

		<!-- Page -->
		<div class="page">

			<!-- main-content -->
			<!-- <div class="main-content app-content"> -->
			<div class="">
				{if $sessioninfo}
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
							<ul class="nav">
								<li class="">
									
								</li>
							</ul>
						</div>
						<div class="main-header-right">
							<ul class="nav">
								<li class="text-capitalize mr-2">
									{$sessioninfo.u}
								</li>
								<li class="text-capitalize">
									<a href="login.php?logout=1"><i class="fas fa-sign-out-alt"></i></a>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<!-- /main-header -->
				{/if}

				<!-- container -->
				<div class="container-fluid">

<!-- <body id="page-top" class="pda-body" style="display: flex;flex-direction: column;min-height: 100vh;">
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
	<tr>
	    <td class="redbar pda-header" style="width: 5%" nowrap="" align="center">{$BRANCH_CODE}</td>
	    <td class="greenbar pda-header" style="width: 5%" nowrap="" align="center">{#SITE_NAME#}</td>
	    <td class="greenbar pda-header"  align="right" nowrap="">
		{if $sessioninfo}
			<p>{$sessioninfo.u}   <a href="login.php?logout=1" style="padding-left: 5px; padding-right: 5px"><img id="logout-icon" src="/ui/pda/logout.png" width="20px" alt="Logout"></a> </p>

		{/if}
		</td>
	</tr>
</table>
<div class="pda-font body"> -->
{/if}
