<?
/*
4/2/2013 5:50 PM Andy
- Add debtor login screen.
*/
include("include/common.php");
if ($_REQUEST['clear_cache']) 
{
	passthru("rm -vf templates_c/*");
	print "Smarty cache cleared\n";
	exit;
}

if (!$login && !$vp_login && !$dp_login)
{
	header("Location: /login.php");
}
else
{
	header("Location: /home.php");
}
?>
