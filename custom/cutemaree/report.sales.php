<?php
include("../../include/common.php");
include("../../include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

$smarty->display("header.tpl");
$smarty->display("cutemaree/report.sales.tpl");
$smarty->display("footer.tpl");
?>
