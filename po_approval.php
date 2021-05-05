<?php
/*
6/9/2008 6:14:10 PM yinsee
- superuser can approve any PO, and is final approval

1/25/2011 10:32:12 AM Andy
- Fix a bugs which cause multiple approval make document stuck.

4/19/2012 2:58:20 PM Andy
- realign script layout.

11/2/2012 4:20 PM Justin
- Bug fixed on system do not show all departments due to the filter of department limits while in approval flow (view mode).

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/25/2013 5:04 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

10/7/2016 11:46 AM Andy
- Fixed stucked approval redirect to wrong php.

2/28/2020 5:35 PM Andy
- Changed memory limit to 256mb.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_APPROVAL', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
set_time_limit(0);
ini_set('memory_limit', '256M');
include("po.include.php");

$approval_status = array(1 => "Approved", 2 => "Rejected", 3 => "KIV (Pending)", 4 => "Terminated");

$smarty->assign("PAGE_TITLE", "Purchase Order Approval");

$branch_id = intval($_REQUEST['branch_id']);
if ($branch_id ==''){
	$branch_id = $sessioninfo['branch_id'];
}

//init_selection();
get_allowed_user_list();

if ($_REQUEST['on_behalf_of'] && $_REQUEST['on_behalf_by']) {
	$con->sql_query("select group_concat(u separator ', ') as u from user where id in (".str_replace('-',',',$_REQUEST['on_behalf_of']).")");
	$on_behalf_of_u = $con->sql_fetchfield(0);
	$con->sql_query("select u from user where id = ".mi($_REQUEST['on_behalf_by'])." limit 1");
	$on_behalf_by_u = $con->sql_fetchfield(0);
	$approval_on_behalf = array(
		'on_behalf_of' => str_replace('-',',',$_REQUEST['on_behalf_of']),
		'on_behalf_by' => mi($_REQUEST['on_behalf_by']),
		'on_behalf_of_u' => $on_behalf_of_u,
		'on_behalf_by_u' => $on_behalf_by_u,
	);
}
else {
	$approval_on_behalf = false;
}
$smarty->assign('approval_on_behalf', $approval_on_behalf);

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	
		case 'ajax_load_po':
			ajax_load_po();
			exit;

		case 'kiv_approval':
		case 'terminate_approval':
		case 'reject_approval':
		case 'save_approval':		
			po_approval();
		    break;
		
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    fail("<h1>Unhandled Request</h1>");
		    exit;
	}
}	
do_approval_all();

function ajax_load_po(){
	global $con, $smarty, $branch_id;
	$form=array();	
	$id=intval($_REQUEST['id']);
	$form=load_po_header($id, $branch_id);

	$con->sql_query("select u from user where id=".mi($form['user_id']));
	$r=$con->sql_fetchrow();
	$form['u']=$r[0];
	
	$con->sql_query("select description from category where id=".mi($form['department_id']));
	$r=$con->sql_fetchrow();
	$form['department']=$r[0];
	
	// show department option
	$con->sql_query("select id, description from category where active and level = 2 order by description");
	$smarty->assign("dept", $con->sql_fetchrowset());
	
	// default action is create New PO
	$con->sql_query("select id, code from branch where active=1 order by sequence,code");
	$smarty->assign("branch", $con->sql_fetchrowset());
	$con->sql_query("select id, code, fraction from uom where active order by code");
	$smarty->assign("uom", $con->sql_fetchrowset());
	
	$form['approval_screen']=1;
	$smarty->assign("po_items", load_po_items($form));
	$smarty->assign('form', $form);
    $smarty->assign("readonly", 1);
	$smarty->display("po.new.tpl");
}

function po_approval(){
	global $con, $sessioninfo, $LANG, $approval_status, $branch_id, $approval_on_behalf;
	 // save approval status (1 = approve, 2 = rejected. 3 = KIV, 4 = Terminate)
	$form=$_REQUEST;
	
	$flow_completed=false;	
    if ($form['a']=='terminate_approval')
		$approve=4;
	elseif ($form['a']=='reject_approval')
		$approve=2;
	elseif ($form['a']=='kiv_approval')
	    $approve=3;
	else
		$approve=1;

	// save approval status
	$approve_comment = trim($form['approve_comment']); 
	
	if ($approval_on_behalf) {
		$approve_comment .= ' (by '.$approval_on_behalf['on_behalf_by_u'].' on behalf of '.$approval_on_behalf['on_behalf_of_u'].')';
	}
	
	$sz=ms($approve_comment);
	$aid=intval($form['approval_history_id']);
	$poid=intval($form['id']);
	
	check_must_can_edit($branch_id, $poid, true);
	
	// do not set remark2 if remark2 is disabled (not last approval) 
	if (isset($form['remark2']))
		$remark2=sz($form['remark2']);
	else
		$remark2='remark2';

	if($aid > 0){
		
		/*if ($sessioninfo['level']<9999) // superadmin can approve anything
		{ 
			$con->sql_query("select approvals from branch_approval_history where id=$aid and branch_id=$branch_id");
			if ($app=$con->sql_fetchrow()){
			    if (!strstr($app[0], "|$sessioninfo[id]|")){
			    	print "<script>alert('".sprintf($LANG['PO_NOT_APPROVAL'], $poid)."');</script>\n";
			    	return;
			    }
			}
		}*/
		
		$params = array();
		$params['approve'] = 1;
		$params['user_id'] = $sessioninfo['id'];
		$params['id'] = $aid;
		$params['branch_id'] = $branch_id;
		$params['check_is_approval'] = true;
	    $is_approval = check_is_last_approval_by_id($params, $con);
	    if (!$is_approval){
	    	print "<script>alert('".sprintf($LANG['PO_NOT_APPROVAL'], $poid)."');</script>\n";
	    	return;
	    }
		
		$con->sql_query("update po set status=$approve, remark2=$remark2 where id=$poid and branch_id=$branch_id");
		log_br($sessioninfo['id'], 'PURCHASE ORDER', $poid, "PO Approval (ID#$poid, Status: $approval_status[$approve])");

		// if this is not KIV.
		if ($approve!=3){
			$con->sql_query("insert into branch_approval_history_items (approval_history_id, branch_id, user_id, status, log) values ($aid, $branch_id, $sessioninfo[id], $approve, $sz)");

			/*
			$con->sql_query("select flow_approvals, approvals, po.user_id, notify_users from branch_approval_history left join po on branch_approval_history.ref_id = po.id and branch_approval_history.branch_id = po.branch_id where branch_approval_history.id = $aid and branch_approval_history.branch_id = $branch_id");
			$r = $con->sql_fetchrow();

			$recipients = $r[3];
			$po_owner = $r[2];
			$flow_approvals = $r[0];
					
     		$recipients = str_replace("|$sessioninfo[id]|", "|", $recipients);
     		$to = preg_split("/\|/", $recipients);
			*/
			
			$q1=$con->sql_query("select vendor_id, department_id from po where id = $poid and branch_id = $branch_id");
			$r1 = $con->sql_fetchrow($q1);

			$q3 = $con->sql_query("select description from vendor where id=$r1[vendor_id]");
			$r3 = $con->sql_fetchrow($q3);
			$vendor=$r3['description'];
			
			$q2 = $con->sql_query("select description from category where id=$r1[department_id]");
			$r2 = $con->sql_fetchrow($q2);
			$dept=$r2['description'];
			
	      	if($approve==1){  // approve
	        	$params = array();
	    		$params['approve'] = $approve;
	    		$params['user_id'] = $sessioninfo['id'];
	    		$params['id'] = $aid;
	    		$params['branch_id'] = $branch_id;
	    		$params['update_approval_flow'] = true;
	        	$is_last = check_is_last_approval_by_id($params, $con);
	        	if($is_last)  $flow_completed = true; 	
	      	}else{
	        	$con->sql_query("update branch_approval_history set status = $approve, approvals = replace(approvals, '|$sessioninfo[id]|', '|') where id = $aid and branch_id = $branch_id");
	        	$flow_completed = true; 
	      	}					
			

			// check if completed, superuser is final approve 
			/*if ($sessioninfo['level']<9999)
			{
				$con->sql_query("select approvals from branch_approval_history where id = $aid and branch_id = $branch_id");
				$r = $con->sql_fetchrow();
				$flow_completed = ($r[0] == '|' || $r[0] == '');
			}
			else
				$flow_completed = true; */
			
			// send pm
			//if ($flow_completed) $to[] = $po_owner;
			$to = get_pm_recipient_list2($poid,$aid,$approve,'approval',$branch_id,'po');
			$status_str = ($is_last || $approve != 1) ? $approval_status[$approve] : '';
			send_pm2($to, "Purchase Order Approval (ID#$poid, Dept:$dept, Vendor:$vendor) $status_str", "po.php?a=view&id=$poid&branch_id=$branch_id", array('module_name'=>'po'));
		}
	}

	if ($flow_completed){
		if($approve==1)
			post_process_po($poid, $branch_id);
		if($approve == 4)
			$con->sql_query("update po set active=0 where id=$poid and branch_id=$branch_id");
	}
	print "<script>alert('PO $approval_status[$approve]');</script>\n";
	
	if ($approval_on_behalf) {
		header("Location: /stucked_document_approvals.php?m=po");
		exit;
	}
}

function do_approval_all(){
	global $smarty, $LANG, $sessioninfo, $con, $branch_id, $approval_on_behalf;
	
	  /*if ($sessioninfo['level']<9999 || $sessioninfo['id']!=1)    // only wsatp
		$usercheck = "approvals like '|$sessioninfo[id]|%' and";
	
   	$con->sql_query("select po.po_date, po.branch_id, po.id, po.status , branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name, vendor.description as vendor  
from po 
left join branch_approval_history on po.approval_history_id = branch_approval_history.id and po.branch_id=branch_approval_history.branch_id 
left join user on user.id=po.user_id
left join branch on po.branch_id = branch.id 
left join vendor on vendor.id=vendor_id 
where $usercheck po.branch_id = $sessioninfo[branch_id] and po.status = 1 and po.approved=0 order by po.last_update");*/

	if ($approval_on_behalf) {
		$u = explode(',',$approval_on_behalf['on_behalf_of']);
		$search_approval = $u[0];
		$doc_filter = ' and po.id = '.mi($_REQUEST['id']).' and po.branch_id = '.mi($_REQUEST['branch_id']).' ';
	}
	else $search_approval = $sessioninfo['id'];

    $con->sql_query("select po.po_date, po.branch_id, po.id, po.status , branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name, vendor.description as vendor  
from po 
left join branch_approval_history on po.approval_history_id = branch_approval_history.id and po.branch_id=branch_approval_history.branch_id 
left join user on user.id=po.user_id
left join branch on po.branch_id = branch.id 
left join vendor on vendor.id=vendor_id 
where (
(approvals like '|$search_approval|%' and approval_order_id=1) or
(approvals like '%|$search_approval|%' and approval_order_id in (2,3))
) and po.branch_id = $sessioninfo[branch_id] and po.status = 1 and po.approved=0 $doc_filter order by po.last_update");

   	$smarty->assign("po", $con->sql_fetchrowset());
   	$smarty->display("po_approval.index.tpl");
}

?>
