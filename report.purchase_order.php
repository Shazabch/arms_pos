<?php
	include("include/common.php");
	if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
	if (!privilege('RPT_PURCHASE_ORDER')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'RPT_PURCHASE_ORDER', BRANCH_CODE), "/index.php");

	$smarty->assign("PAGE_TITLE", "Purchase Order Reports");
	
	if (isset($_REQUEST['a']))
	{
	    switch($_REQUEST['a'])
	    {
	        case 'ajax_refresh_table':
	            // ajax call to refresh table by selected date
	            show_table();
	            exit;
	        case 'print':
	        	small_report();
	        	exit;
			default:
				print "<h1>Error: Unhandled Request</h1>";
			    print_r($_REQUEST);
			    exit;
		}
	}
	
	// by defauilt show table
	$smarty->display("report.purchase_order.tpl");
?>
