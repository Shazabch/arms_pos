{*
3/19/2010 5:34:35 PM edward
- add if not $smarty.request.remote then show menu

3/8/2011 4:03:55 PM Andy
- Add new div, id=curtain2, which will not auto hide even user click on it

5/6/2011 2:14:39 PM Andy
- Change curtain2 color to black.

7/4/2011 11:49:01 AM Andy
- Add checking for SERVER_NAME, if contain 'arms-go' it will include other conf file.

8/2/2011 10:47:33 AM Andy
- Change ARMS system description at consignment mode.

28/5/2012 1:52 PM yinsee
- change Home menu to Home > Dashboard for iPad usability issue

7/24/2012 2:03 PM Andy
- Show vendor description at vendor portal header.

10/10/2012 5:06 PM Andy
- change sortable js version to 1

1/9/2013 5:54:00 PM Fithri
- add customer name at ARMS header, read from arms.lic

4/2/2013 12:19 PM Justin
- change form js version to 1

5/28/2013 5:14 PM Andy
- Enhance to show debtor description while login to debtor portal.

9/30/2013 3:13 PM Andy
- Enhance to show GPM Broadcast Message.

11/15/2013 4:38 PM Fithri
- Change ARMS(™) to ARMS® for arms-go

4/2/2013 12:19 PM Justin
- change form js version to 2.

2/19/2014 3:54 PM Justin
- change form js version to 3.

4/3/2014 11:58 AM Andy
- change form js version to 4.

5/5/2015 3:51 PM Andy
- change form js version to 5.
- Added "div_item_details_popup" for transaction details popup.

5/13/2015 1:02 PM Andy
- change form js version to 6.

6/2/2015 1:59 PM Andy
- change form js version to 7.

1/12/2016 4:36 PM Andy
- change form js version to 9.

11/9/2016 3:26 PM Andy
- Enhanced to stop search engine robot.

1/24/2017 2:57 PM Andy
- change form js version to 10.

4/18/2017 9:27 AM Andy
- change form js version to 11.

5/8/2017 11:31 AM Andy
- change form js version to 12.
- Add to override javascript ARMS_CURRENCY.

6/6/2017 1:17 PM Andy
- change form js version to 13.

8/21/2017 10:25 AM Andy
- change form js version to 14.

10/13/2017 3:59 PM Andy
- Add to include print.css

12/4/2017 4:43 PM Andy
- change form js version to 15.

3/22/2019 5:05 PM Andy
- change form js version to 16.

5/24/2019 4:07 PM Andy
- Change form js version to 17.
- Adde Global Wait Popup.

7/15/2019 2:40 PM Andy
- Enhanced to check 'mobile_scale' to scale the screen.

8/2/2019 9:35 AM Andy
- Change form js version to 18.

8/15/2019 4:27 PM Andy
- Change form js version to 19.

9/25/2019 11:19 AM Andy
- Change form js version to 20.

10/2/2019 3:24 PM andy
- Change form js version to 21.

11/4/2019 10:36 AM Andy
- Added js feature to show loading when ajax is running.
- Change form js version to 22.

11/22/2019 10:14 AM Justin
- Enhanced to show sales agent name.
- Enhanced not to show branch code when it is sales agent portal.

12/6/2019 5:15 PM Andy
- Change form js version to 23.

1/15/2020 10:10 AM Andy
- Added temporary broadcast message for rakanda old server.

03/18/2020 Sheila
- Modified layout to compatible with new UI.
- Change to always use default.css

05/07/2020 Sheila
- Updated table column widths in the header

6/15/2020 2:45 PM Andy
- Change form js version to 24.

6/17/2020 10:53 AM Andy
- Change default.css version to 2.

6/17/2020 4:37 PM Andy
- Change form js version to 25.

6/18/2020 2:13 PM Andy
- Change default.css version to 3.

7/6/2020 12:26 PM Andy
- Change default.css version to 4.

7/30/2020 11:22 AM Andy
- Change default.css version to 5.

8/5/2020 1:13 PM Andy
- Change sorttable.js version to 2.

9/4/2020 10:16 AM William
- Change form.js version to 26.

11/5/2020 11:56 AM William
- Change form.js version to 27.

11/27/2020 2:40 PM Andy
- Change default.css version to 6.

12/8/2020 2:50 PM Andy
- Change default.css version to 7.

2/5/2021 3:24 PM Shane
- Change forms.js version to 28

03/09/2021 2:33 PM Rayleen
- Add table id="top_nav_header" in header table
*}
{if !$no_header_footer}<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name='robots' content='noindex, nofollow'>
{config_load file="site.conf"}
{if strpos($smarty.server.SERVER_NAME, 'arms-go') !==false}
	{config_load file="common-go.conf"}
{else}
	{config_load file="common.conf"}
{/if}

<meta NAME="Description" CONTENT="{#META_DESCRIPTION#}">
{if isset($mobile_scale)}
	<meta name="viewport" content="width=device-width,initial-scale={$mobile_scale};" />
{/if}
<title>{$BRANCH_CODE} | {#SITE_NAME#} | {$PAGE_TITLE}</title>
{if dirname($smarty.server.REQUEST_URI) ne '/'}<base href="http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.HTTP_HOST}/">{/if}
{*<link rel="stylesheet" href="{#SITE_CSS#}" type="text/css">*}
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
	{if $config.arms_currency.code}ARMS_CURRENCY['code'] = "{$config.arms_currency.code}"; {/if}
	{if $config.arms_currency.symbol}ARMS_CURRENCY['symbol'] = "{$config.arms_currency.symbol}"; {/if}
	{if $config.arms_currency.name}ARMS_CURRENCY['name'] = '{$config.arms_currency.name}';{/if}
	{if $config.arms_currency.country}ARMS_CURRENCY['country'] = '{$config.arms_currency.country}';{/if}
	{if $config.arms_currency.rounding}ARMS_CURRENCY['rounding'] = '{$config.arms_currency.rounding}';{/if}
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

{if $config.gpm_customer_name and $sessioninfo and !$no_menu_templates}
	{* is GPM CUSTOMER *}
	{include file="header.gpm_broadcast_message.tpl"}

{/if}

{* Temporary for Rakanda Old Server *}
{*
<div style="background: none repeat scroll 0 0 #FFFF99;border-bottom: 1px solid #CC0022;clear: both;left: 0;top:0;position: fixed;width: 100%;">
	<marquee onmouseout="this.scrollAmount=3;" onmouseover="this.scrollAmount=0;" scrollamount="3" scrolldelay="10" style="color: #CC0022;font-size: 12px;font-weight: bold;height: 25px;line-height: 25px;">
		
		<span style="color:#666;">2020-01-15: </span>
		This server is no longer use! Please go to Live server at <a href="https://rakanda-hq.arms.com.my" target="_blank" style="color: blue;">https://rakanda-hq.arms.com.my</a>
		<img src="ui/pixel.gif" width="50" height="1" />		
	</marquee>
</div>
<div style="height:26px;"></div>
*}

<!-- Loader -->
		<!-- <div id="global-loader">
			<img src="../../assets/img/loader.svg" class="loader-img" alt="Loader">
		</div> -->
		<!-- /Loader -->

		<!-- Page -->
		<div class="page">

			{assign var=http_host value=$smarty.server.HTTP_HOST}

			{if !$no_menu_templates && !$smarty.session.$http_host.is_remote}{include file=menu.tpl}{/if}


			

			<!-- main-content -->
			<div class="main-content app-content">

				<!-- main-header -->
				<div id ="top_nav_header" class="main-header sticky side-header nav nav-item">
					<div class="container-fluid">
						<div class="main-header-left ">
							<div class="responsive-logo">
								<a href="index.html"><img src="../../assets/img/brand/logo.png" class="logo-1" alt="logo"></a>
								
							</div>
							<div class="app-sidebar__toggle" data-toggle="sidebar">
								<a class="open-toggle" href="#"><i class="header-icon fe fe-align-left" ></i></a>
								<a class="close-toggle" href="#"><i class="header-icons fe fe-x"></i></a>
							</div>
							<div class="main-header-center ml-3 d-sm-none d-md-none d-lg-block">
								<p style="display: table-cell;" class="lead">{if strpos($smarty.server.SERVER_NAME, 'arms-go') !==false}
							ARMS&reg; GO Retail Management System &amp; Point Of Sale
						{elseif $config.consignment_modules}
							ARMS&reg; Consignment Retail Management System &amp; Point Of Sale
						{else}
							{#SYSTEM_ID#}
						{/if}</p>
							</div>
						</div>
						<div class="main-header-right">
							<i class="mdi mdi-calendar"></i> &nbsp; {$smarty.now|date_format:"%a %d %b, %I:%M %p"}
							
						</div>
					</div>
				</div>
				<!-- /main-header -->
<div class=container-fluid>
<img src=ui/pixel.gif width=700 height=1><br>
{/if}
