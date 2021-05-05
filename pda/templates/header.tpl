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
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

{config_load file="site.conf"}
{config_load file="common.conf"}
<meta NAME="Description" CONTENT="{#META_DESCRIPTION#}">
<meta http-equiv="pragma" content="no-cache" />
<meta name="MobileOptimized" content="225">
<meta name="viewport" content="width=device-width">
{if dirname($smarty.server.REQUEST_URI) ne '/'}<base href="http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.HTTP_HOST}/pda/">{/if}
<title>{$BRANCH_CODE} | {#SITE_NAME#} | {$PAGE_TITLE}</title>
<link rel="stylesheet" href="{#SITE_CSS#}" type="text/css">
<link rel="stylesheet" href="/templates/pda.css?v=1" type="text/css">
<link rel="shortcut icon" href="/favicon.ico">

<script src="include/jquery.js" language=javascript type="text/javascript"></script>
<script src="include/forms.js" language=javascript type="text/javascript"></script>
{literal}
<style>
.small input, .small select { font: 10px Arial !important; }
</style>
{/literal}
</head>

<body id="page-top" class="pda-body" style="display: flex;flex-direction: column;min-height: 100vh;">
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
<div class="pda-font body">
{/if}
