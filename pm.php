<?
/*
Revision History
----------------
4/20/07 2:35:37 PM yinsee
- added branch_id to pm table

5/21/2007 3:12:37 PM yinsee
- mark_read use id and branch_id

11/18/2015 9:30 AM Qiu Ying
- Enhance PM layout and allow user to delete

11/27/2015 4:41 PM Qiu Ying
- Fix PM cannot clear all

2017-08-24 14:32 PM Qiu Ying
- Enhanced to add pagination in dashboard pm
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

$bid = intval($_REQUEST['branch_id']);
$id = intval($_REQUEST['id']);

if (isset($_REQUEST['a']))
{
	switch ($_REQUEST['a'])
	{
		// add user
		case 'ajax_get_pm':
			$pg_start = intval($_REQUEST['s']);
			$page_size = 30;
			
			$con->sql_query("select count(*) as total_pm
			from pm
			left join user on from_user_id = user.id
			left join branch on user.default_branch_id = branch.id
			where pm.status = 0 and pm.to_user_id = $sessioninfo[id]");
			$total_pm = $con->sql_fetchfield("total_pm");
			$smarty->assign("total_pm", $total_pm);
			
			if($total_pm > 0 && $pg_start == $total_pm){
				$pg_start -= $page_size;
			}
			
			if ($total_pm > $page_size) {
				for ($i=0,$p=1;$i<$total_pm;$i+=$page_size,$p++) {
					$pagination[$i] = $p;
				}
				$smarty->assign("pagination", $pagination);
			}else{
				$sel_pg = 1;
				$page_size = $total_pm;
			}
			
			$result = $con->sql_query("select user.u,user.position,branch.code as branch,pm.branch_id,pm.id,pm.msg,pm.url,pm.timestamp,DATEDIFF(CURDATE(),pm.added) as age, pm.opened
					from pm
					left join user on from_user_id = user.id
					left join branch on user.default_branch_id = branch.id
					where pm.status = 0 and pm.to_user_id = $sessioninfo[id] order by msg, age
					limit $pg_start, $page_size");
			$smarty->assign("selected_page", $pg_start);
			$smarty->assign("pm", $con->sql_fetchrowset($result));
			$con->sql_freeresult($result);
			$smarty->display("notifications_pm.tpl");
			exit;
		case 'mark_all_read':
			$con->sql_query("update pm set status=1 where status = 0 and to_user_id = $sessioninfo[id]");
			exit; 
		case 'ajax_mark_read' :
		    $con->sql_query("update pm set status=1 where branch_id = $bid and id = $id");
		    /*$con->sql_query("select to_user_id, msg from pm where branch_id = $bid and id = $id");
		    $r = $con->sql_fetchrow();
		    $con->sql_query("update pm set status = 1 where to_user_id = " . mi($r['to_user_id']) . " and msg = " . ms($r['msg']));*/
			exit;
		case 'view_pm' :
		    $con->sql_query("select to_user_id, msg,url from pm where branch_id = $bid and id = $id");
		    $r = $con->sql_fetchrow();
		    $con->sql_query("update pm set opened = 1 where to_user_id = " . mi($r['to_user_id']) . " and msg = " . ms($r['msg']));
		    $smarty->assign("url", "$r[url]");
		    $smarty->assign("branch_id", $bid);
		    $smarty->assign("id", $id);
		    $smarty->display("view_pm.tpl");
		    exit;
		case 'control' :
		    $smarty->assign("branch_id", $bid);
		    $smarty->assign("id", $id);
		    $smarty->display("view_pm.control.tpl");
		    exit;

		default:
			print "<h3>Unhandled Request</h3>";
			print_r($_REQUEST);
			exit;
	}
}

?>
