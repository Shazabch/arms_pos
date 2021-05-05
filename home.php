<?
/*
7/10/2012 5:10 PM Andy
- Add can login using vendor portal key.

4/2/2013 5:50 PM Andy
- Add debtor login screen.

11/5/2019 3:14 PM Andy
- Add Sales Agent login screen.
*/
include("include/common.php");

if (!$login && !$_SESSION['sa_ticket'] && !$vp_login && !$dp_login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

if($login) include("notification.php");
elseif($vp_login)	include('vp.home.php');
elseif($dp_login)	include('dp.home.php');
elseif($sa_login)	include('sa.home.php');

$smarty->assign("PAGE_TITLE", "Home");
$smarty->display("home.tpl");

?>

