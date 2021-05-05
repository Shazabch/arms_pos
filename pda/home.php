<?php
/*
3/15/2012 11:05:32 AM Justin
- Added "/pda" to redirect user back to pda login menu page.

11/27/2020 3:31 PM Andy
- Added to have home-menu checking.
*/
include("common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/pda/index.php");

if(isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
		case 'menu':
			$smarty->display('home-menu.tpl');
			exit;
	}
}

$smarty->display('home.tpl');
?>
