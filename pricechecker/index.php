<?
if (isset($_REQUEST['branch'])) setcookie('arms_login_branch',$_REQUEST['branch'],strtotime('+1 year'));
?>
<html>
<head>
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache, must-revalidate">
<meta http-equiv="expires" content="0">
<meta http-equiv="last-modified" content="">
</head>

<frameset rows="*,30" border="0">
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
