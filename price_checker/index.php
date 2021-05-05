<?php
/*
6/2/2010 4:30:56 PM Andy
- Add local IP checking, login checking for price checker. (local ip can set in config, default "192.168")

12/14/2016 10:52 AM Andy
- Enhanced to compatible to php7.
*/
if (isset($_REQUEST['branch'])) setcookie('arms_login_branch',$_REQUEST['branch'],strtotime('+1 year'));

include("../include/common.php");
include("../language.php");
if (!intranet_or_login()) js_redirect($LANG['ACCESS_DENIED_NEED_LOGIN_OR_INTRANET'], "/index.php");
?>
<html>
<head>
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache, must-revalidate">
<meta http-equiv="expires" content="0">
<meta http-equiv="last-modified" content="">
</head>

<frameset rows="*,40" border="0">
 <frame name="content" scrolling="no" src="idle.php">
 <frame name="nav" scrolling="no" noresize="noresize" src="nav.php">
<noframes>
 <body>
<!-- content for browser without frame ability -->
<div align=center>
<img src=/ui/bananaman.gif>
You need a browser that support frame.
</div>
 </body>
</noframes>
</frameset>
</html>
