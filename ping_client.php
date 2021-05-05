<?
/*
1/12/2011 3:56:37 PM Andy
- Move 'ip' and 'lastping' from table branch to branch_status.
*/
define('SKIP_BROWSER', 1);
include("include/common.php");

print "receive PING from $_REQUEST[b] at $_SERVER[REMOTE_ADDR]:$_REQUEST[p]";

if (isset($_REQUEST['b']))
{
	/*$con->sql_query("update branch set ip = '$_SERVER[REMOTE_ADDR]:$_REQUEST[p]', lastping = NOW() where code = " . ms($_REQUEST['b'])) or die(mysql_error());*/
	$con->sql_query("select id from branch where code=".ms($_REQUEST['b']));
	$bid = mi($con->sql_fetchfield(0));
	if($bid){
		$upd = array();
		$upd['ip'] = $_SERVER['REMOTE_ADDR'].':'.$_REQUEST['p'];
		$upd['lastping'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update branch_status set ".mysql_update_by_field($upd)." where branch_id=$bid");
	}
}
?>
