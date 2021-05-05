<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
//if (!privilege('POS_BACKEND')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_BACKEND', BRANCH_CODE), "/index.php");

foreach (glob("db/*.tgz") as $filename) {
	preg_match('/^db\/(.*)\.tgz$/',$filename,$matches);
	$r['file'] = $matches[1]; 
    $r['url'] = $filename;
    $r['timestamp'] = date("d-m-Y H:i:s", filemtime($filename));
    $file[] = $r;
}

$smarty->assign("PAGE_TITLE","POS DB download");
$smarty->assign('file',$file);
$smarty->display('misc.pos_db.tpl');
?>
