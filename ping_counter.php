<?php
/*
7/25/2018 10:59 AM Andy
- Added "revision_type".
- Enhanced to check the status and only update once every 10 minutes.

9/3/2018 11:38 AM Andy
- Change counter_status revision to char(10).

12/23/2020 10:41 AM Andy
- Enhanced PING Counter to can pass using $_REQUEST.
*/
define('SKIP_BROWSER', 1);
include("include/common.php");
$maintenance->check(1);

if(isset($_REQUEST['not_sz']) && $_REQUEST['not_sz']){
	// Not Serialize Mode
	$bid = mi($_REQUEST['bid']);
	$cid = mi($_REQUEST['cid']);
	$st = trim($_REQUEST['status']);
	$lasterr = trim($_REQUEST['lasterr']);
	$user_id = mi($_REQUEST['user_id']);
	$revision = trim($_REQUEST['revision']);
	$revision_type = trim($_REQUEST['revision_type']);
}else{
	// Serialize Mode
	$sz = unserialize($_REQUEST['sz']);
	
	$bid = mi($sz['bid']);
	$cid = mi($sz['cid']);
	$st = trim($sz['status']);
	$lasterr = trim($sz['lasterr']);
	$user_id = mi($sz['user_id']);
	$revision = trim($sz['revision']);
	$revision_type = trim($sz['revision_type']);
}

$ip = trim($_SERVER['REMOTE_ADDR']);

print "receive PING from $ip";

if ($bid>0 && $cid>0)
{
	// Get Current Data
	$con->sql_query("select * from counter_status where branch_id=$bid and id=$cid");
	$data = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	// All Variable Same
	if($st == $data['status'] && $ip == $data['ip'] && $lasterr == $data['lasterr']  && $user_id == $data['user_id'] && $revision == $data['revision'] && $revision_type == $data['revision_type']){
		if(time() - strtotime($data['lastping']) < 600){
			exit;	// 10 min only update
		}
	}
	
	$con->sql_query($sql = "replace into counter_status (branch_id, id, ip, status, lastping, user_id, lasterr, revision, revision_type) values ($bid, $cid, ".ms($ip).", ".ms($st).", NOW(), $user_id, ".ms($lasterr).", ".ms($revision).", ".ms($revision_type).")");
}
?>
