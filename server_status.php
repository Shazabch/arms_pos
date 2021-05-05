<?
/*
1/12/2011 3:56:37 PM Andy
- Move 'ip' and 'lastping' from table branch to branch_status.

6/27/2011 10:19:37 AM Andy
- Make all branch default sort by sequence, code.

3/14/2013 5:28 PM Justin
- Enhanced to show out server status while in multi server mode.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
$maintenance->check(44);

// show server status while in multi server mode
if(!$config['single_server_mode']){
	// render
	$con->sql_query("select b.code, b.description, bs.ip, UNIX_TIMESTAMP(bs.lastping) as lastping
	from branch b
	left join branch_status bs on bs.branch_id=b.id
	where b.active=1 order by b.sequence, b.code");
	$smarty->assign("stats", $con->sql_fetchrowset());
}

$smarty->assign("PAGE_TITLE", "Server Status");
$smarty->display("server_status.tpl");
?>

