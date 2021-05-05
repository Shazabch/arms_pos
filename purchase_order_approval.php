<?
/*revision history
-------------------
8/20/2007 5:25:46 PM gary
- add sql query to get vendor n dept info for send_pm.

9/18/2007 10:41:49 AM yinsee
- add PO creator to PM list when flow completed 

9/26/2007 11:20:12 AM yinsee +gary
- add branch_id filter when get the branch approval history.

12/3/2007 4:49:22 PM gary
- modify the approval method like standard.
*/
//error_reporting(0);
header("Location: /po_approval.php");
exit;

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_APPROVAL', BRANCH_CODE), "/index.php");
include("purchase_order.include.php");

//if ($sessioninfo['u']!='yinsee') die("this module is temporary disabled.");

$approval_status = array(1 => "Approved", 2 => "Rejected", 3 => "KIV (Pending)", 4 => "Terminated");

//$branch_id = intval($_REQUEST['branch_id']);
$branch_id = $sessioninfo['branch_id'];

$smarty->assign("PAGE_TITLE", "Purchase Order Approval");

if (isset($_REQUEST['a'])){

	switch($_REQUEST['a']){
	
		case 'ajax_load_po':
			$id = intval($_REQUEST['id']);
			load_po($id,true,false);
			//echo"<pre>";print_r($po);echo"</pre>";
			$form = $smarty->get_template_vars("form");
			
			$con->sql_query("select u from user where id = " . mi($form['user_id']));
			$r = $con->sql_fetchrow();
			$form['u'] = $r[0];
			
			$con->sql_query("select description from category where id = " . mi($form['department_id']));
			$r = $con->sql_fetchrow();
			$form['department'] = $r[0];

			$smarty->assign('form', $form);
			$smarty->display("purchase_order_approval.view.tpl");
			exit;

		case 'kiv_approval':
		case 'terminate_approval':
		case 'reject_approval':
		case 'save_approval':
			 // save approval status (1 = approve, 2 = rejected. 3 = KIV, 4 = Terminate)
			$flow_completed = false;
		    if ($_REQUEST['a'] == 'terminate_approval')
		    {
				$approve = 4;
			}
			elseif ($_REQUEST['a'] == 'reject_approval')
		    {
				$approve = 2;
			}
			elseif ($_REQUEST['a'] == 'kiv_approval')
			{
			    $approve = 3;
			}
			else
			{
				$approve = 1;
			}
			
			// save approval status
			$sz = ms($_REQUEST['approve_comment']);
			$aid = intval($_REQUEST['approval_history_id']);
			$poid = intval($_REQUEST['id']);

			if ($aid > 0)
			{
			    // double check approval
				$con->sql_query("select approvals from branch_approval_history where id = $aid and branch_id = $branch_id");
				if ($app = $con->sql_fetchrow())
				{
				    // not allowed
				    if (!strstr($app[0], "|$sessioninfo[id]|"))
				    {
				    	print "<script>alert('".sprintf($LANG['PO_NOT_APPROVAL'], $poid)."');</script>\n";
				    	break;
				    }
				}

				// update SKU status
				$con->sql_query("update po set status = $approve where id = $poid and branch_id = $branch_id");
				log_br($sessioninfo['id'], 'PURCHASE ORDER', $poid, "PO Approval (ID#$poid, Status: $approval_status[$approve])");

				// if this is not KIV.
				if ($approve!=3)
				{
					// update approval records
					$con->sql_query("insert into branch_approval_history_items (approval_history_id, branch_id, user_id, status, log) values ($aid, $branch_id, $sessioninfo[id], $approve, $sz)");

					// get the PM list
					//yinsee + gary (add branch filter)
					$con->sql_query("select flow_approvals, approvals, po.user_id, notify_users from branch_approval_history left join po on branch_approval_history.ref_id = po.id and branch_approval_history.branch_id = po.branch_id where branch_approval_history.id = $aid and branch_approval_history.branch_id = $branch_id");
					$r = $con->sql_fetchrow();

					$recipients = $r[3];
					$po_owner = $r[2];
					$flow_approvals = $r[0];
							
					//str_replace($r[1], "|", $r[0]) . $r[3];
					// if reject, no need to send to apply person
					//if ($approve != 2) $recipients .= $r[2];
	               	$recipients = str_replace("|$sessioninfo[id]|", "|", $recipients);
	               	$to = preg_split("/\|/", $recipients);

					//to merge the all approvers to notification list
					/*
					$flow_approvals = preg_split("/\|/", $flow_approvals);
					$to=array_merge($to,$flow_approvals);
					print "<pre>"; print_r($to);print"</pre>";
					exit;
					*/
					
					//select from po to get vendor_id and dept_id
					$q1=$con->sql_query("select vendor_id, department_id from po where id = $poid and branch_id = $branch_id");
					$r1 = $con->sql_fetchrow($q1);

					// 8/20/2007 5:16:51 PM gary (add vendor info in send_pm)
					$q3 = $con->sql_query("select description from vendor where id=$r1[vendor_id]");
					$r3 = $con->sql_fetchrow($q3);
					$vendor=$r3['description'];
					
					// 8/20/2007 5:16:51 PM gary (add category info in send_pm)
					$q2 = $con->sql_query("select description from category where id=$r1[department_id]");
					$r2 = $con->sql_fetchrow($q2);
					$dept=$r2['description'];
										
					// remove current user from the approval list
					$con->sql_query("update branch_approval_history set status = $approve, approvals = replace(approvals, '|$sessioninfo[id]|', '|') where id = $aid and branch_id = $branch_id");

					// check if completed
					$con->sql_query("select approvals from branch_approval_history where id = $aid and branch_id = $branch_id");
					$r = $con->sql_fetchrow();
					$flow_completed = ($r[0] == '|' || $r[0] == '');
					
					// send pm
					if ($flow_completed) $to[] = $po_owner; // send to owner if finalized
					send_pm($to, "Purchase Order Approval (ID#$poid, Dept:$dept, Vendor:$vendor) $approval_status[$approve]", "purchase_order.php?a=view&id=$poid&branch_id=$branch_id");
				}
			}

			if ($flow_completed)
			{
				if($approve == 1)
					post_process_po($poid, $branch_id);

				if($approve == 4)
					$con->sql_query("update po set active=0 where id = $poid and branch_id = $branch_id");
			}

			print "<script>alert('PO $approval_status[$approve]');</script>\n";
		    break;

		
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    fail("<h1>Unhandled Request</h1>");
		    exit;
	}
}
	
do_approval_all();

function do_approval_all(){
	global $smarty, $LANG, $sessioninfo, $con, $branch_id;
	
   	$con->sql_query("select po.po_date, po.branch_id, po.id, po.status , branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name
from po 
left join branch_approval_history on po.approval_history_id = branch_approval_history.id and po.branch_id = branch_approval_history.branch_id 
left join user on user.id=po.user_id
left join branch on po.branch_id = branch.id
where approvals like '|$sessioninfo[id]|%' and po.branch_id = $sessioninfo[branch_id] and po.status = 1 order by po.last_update");

//	if ($con->sql_numrows()>0)
//	{
	   	$smarty->assign("po", $con->sql_fetchrowset());
	   	$smarty->display("purchase_order_approval.index.tpl");
/*   	}

   	else
   	{
	   	$smarty->assign("title", "Purchase Order Approval");
   	    $smarty->assign("url", "/home.php");
	    $smarty->assign("subject", sprintf($LANG['PO_APPROVAL_COMPLETED']));
	    $smarty->display("redir.tpl");
	    exit;
	}
*/
}

?>
