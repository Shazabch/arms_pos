{*
*}
{if !$no_header_footer}<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
{config_load file="../../templates/site.conf"}
{config_load file="../../templates/common.conf"}
<meta NAME="Description" CONTENT="{#META_DESCRIPTION#}">
<title>{$BRANCH_CODE} | {#SITE_NAME#} | {$PAGE_TITLE}</title>
<link rel="stylesheet" href="{#SITE_CSS#}" type="text/css">
<link rel="stylesheet" href="include/sop.css" type="text/css">
<link rel="shortcut icon" href="/favicon.ico">
<link type="text/css" href="include/css/smoothness/jquery-ui-1.8.5.custom.css" rel="stylesheet" />
<link rel="stylesheet" media="screen" type="text/css" href="include/css/colorpicker.css" />
		<script type="text/javascript" src="include/js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="include/js/jquery-ui-1.8.5.custom.min.js"></script>
		<script type="text/javascript" src="include/js/colorpicker.js"></script>
		<script type="text/javascript" src="/js/json2.js"></script>
		<script type="text/javascript" src="include/sop.js"></script>
</head>

<body>
<div class="noprint">
<div id="div_top_right_loading" style="position:fixed;display:none;background:#ff9;opacity:0.6;top:0px;padding:5px 10px;font-weight:bold;z-index:10000;">
	<img src="/ui/alarm.png" align="absmiddle" />
	Loading...
</div>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
	<td class="redbar" align=center nowrap style="font-size:1.1em"><b>{$BRANCH_CODE}</b></td>
	<td width=1><img src="../ui/pixel.gif" width=1 height=20></td>
	<td class="greenbar" style="font-size:1.1em">{#SYSTEM_ID#}</td>
	<td align=right class="greenbar" nowrap>
	{if $sessioninfo}
	<b>Logged in as
	{if $smarty.session.admin_session}
		{$smarty.session.admin_session.u}</b> (now running as <b>{$sessioninfo.u}</b> | <a href="/login.php?logout_as=1"><u>Logout</u></a>)
	{else}
		{$sessioninfo.u}</b>
	{/if}
	 &nbsp;&nbsp;&nbsp;
	{/if}
	{$smarty.now|date_format:"%a %d %b, %I:%M%p"}</td>
	</tr>
	<tr><td colspan=4><img src="../ui/pixel.gif" width=760 height=1></td></tr>
</table>
<a name="#top"> </a>
{assign var=http_host value=$smarty.server.HTTP_HOST}
{if !$no_menu_templates && !$smarty.session.$http_host.is_remote}{include file='menu.tpl'}{/if}
</div>
<div class="body">
<img src="../ui/pixel.gif" width="700" height="1"><br>
{/if}
