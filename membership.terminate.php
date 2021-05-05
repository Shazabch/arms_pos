<?php
/*
11/8/2010 4:00:41 PM Justin
- Added the points deduction (total points of member) for all the teminated members.
- Do update for the membership total points become 0 after terminated.
- Created a point history indicates the points left deducted by the termination.

6/24/2011 5:02:55 PM Andy
- Make all branch default sort by sequence, code.

8/28/2012 6:08 PM Justin
- Enhanced to insert "added" field for membership_history.

10/8/2012 3:48 PM Justin
- Added new function to pickup member_type base on card no and update/insert into database.

1/3/2020 1:52 PM William
- Enhanced to insert "membership_guid" field for membership_history and membership_points table.

1/30/2020 5:17 Andy
- Increased maintenance checking to v438.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
$maintenance->check(438);

// insert into privilege values ('MEMBERSHIP_TERMINATE', 'Membership Termination', 0, 0);
// insert into privilege values ('MEMBERSHIP_UNBLOCK', 'Allow Unblock of membership card', 0, 0);

if (!privilege('MEMBERSHIP_TERMINATE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_TERMINATE', BRANCH_CODE), "/index.php");

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'list':
			do_list();
			break;
		
		case 'terminate':
			do_terminate();
			exit;
			
		default: 
			print "<h1>Unhandled Request</h1>";
			print_r($_REQUEST);
			exit;
	}
}

// branches
$con->sql_query("select id, code from branch order by sequence,code");
$smarty->assign("branch", $con->sql_fetchrowset());

$smarty->assign('PAGE_TITLE', "Membership Termination");
$smarty->display('membership.terminate.tpl');
exit;

function do_list()
{
	global $con, $smarty;
	$br = get_request_branch(true);
	if ($br) $bid = "apply_branch_id = $br and ";
	$dt = ms($_REQUEST['expiry']);
	
	$con->sql_query("select membership.*, branch.code as branch_code from membership left join branch on apply_branch_id = branch.id where $bid next_expiry_date=$dt and terminated_date = 0 order by card_no");
	
	$smarty->assign("members", $con->sql_fetchrowset());
}

function do_terminate()
{
	global $sessioninfo, $con;
	
	set_time_limit(0);
	
	log_br($sessioninfo['id'], 'MEMBERSHIP', 0, 'Terminate Membership');
	foreach ($_REQUEST['nric'] as $nric){
		$con->sql_query("select membership_guid, nric, card_no, $sessioninfo[branch_id] as branch_id, card_type, issue_date, expiry_date, 'T' as remark, $sessioninfo[id] as user_id, m_type from membership_history where nric=".ms($nric)." order by expiry_date desc, issue_date desc limit 1");
		$r = $con->sql_fetchrow();
	
		if($r){
			$r['added'] = "CURRENT_TIMESTAMP";
			$con->sql_query("insert into membership_history ".mysql_insert_by_field($r, array("membership_guid", "nric", "card_no", "branch_id", "card_type", "issue_date", "expiry_date", "remark", "user_id", "added", "m_type")));
			if ($con->sql_affectedrows()<=0){
				print "<li> Failed to update membership_history for $nric.";
			}else{
				$s[] = ms($nric);
				$con->sql_query("select sum(points) as total_pts from membership_points where nric=".ms($nric));
				$mbr = $con->sql_fetchrow();
				if($mbr['total_pts'] != 0){
					$mp['membership_guid'] = $r['membership_guid'];
					$mp['nric'] = $r['nric'];
				    $mp['card_no'] = $r['card_no'];
				    $mp['branch_id'] = $sessioninfo['branch_id'];
					$mp['date'] = 'CURRENT_TIMESTAMP';
					$mp['points'] = mf($mbr['total_pts']*-1);
					$mp['remark'] = "Terminated";
					$mp['type'] = 'ADJUST';
					$mp['user_id'] = $sessioninfo['id'];
					$con->sql_query("insert into membership_points ".mysql_insert_by_field($mp)) or die(mysql_error());
				}
			}
		}
	}
	
	$con->sql_query("update membership set terminated_date = CURRENT_TIMESTAMP, points = 0 where nric in (".join(",",$s).")");
	print $con->sql_affectedrows()." records terminated. <a href=\"$_SERVER[PHP_SELF]\">Click here to continue</a>";
}
?>
