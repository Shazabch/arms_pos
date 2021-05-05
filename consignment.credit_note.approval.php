<?php
/*
11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

8/1/2013 5:58 PM Andy
- Change to prompt error if the cn/dn is not allow to approve/reject.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
include("consignment.include.php");
include("consignment.credit_note.include.php");

if (!privilege('CON_'.strtoupper(NOTE_TBL).'_APPROVAL')) js_redirect(sprintf($LANG['CON_'.strtoupper(NOTE_TBL).'_APPROVAL'], 'CON_'.strtoupper(NOTE_TBL).'_APPROVAL', BRANCH_CODE), "/index.php");

init_selection();

$branch_id = mi($_REQUEST['branch_id']);

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
		case 'ajax_load_cn':
			$form=array();
			$id=mi($_REQUEST['id']);
			$branch_id=mi($_REQUEST['branch_id']);
			$form=load_header($branch_id, $id);
			$form['approval_screen']=1;
    		$smarty->assign("form", $form);
    		$smarty->assign("readonly", 1);
    		$items = load_items($branch_id, $id);
			$smarty->assign("items", $items);
    		$smarty->display("consignment.credit_note.open.tpl");
			exit;
			
		case 'check_printed_report':
			$mod = defined('DEBIT_NOTE_MODE') ? 'dn':'cn';
			if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($_REQUEST['curr_date'],$branch_id,$mod)) {
				//print '<div class=errmsg><ul><li>'.$LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED'].'</li></ul></div>';
				die($LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED']);
			}
			else print '0';
			exit;

		case 'cancel':
			$set_active=1;
		case 'approve':
		case 'reject':
			$form=$_REQUEST;
			$id=intval($form['id']);
			$branch_id=intval($form['branch_id']);

		    if ($form['a']=='approve')
				$status=1;
			elseif ($form['a']=='reject')
				$status=2;
			elseif ($form['a']=='cancel')
				$status=5;
			cn_approval($branch_id, $id, $status, false);
			exit;

		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

list_approval_all();

function list_approval_all(){
	global $smarty, $LANG, $sessioninfo, $con, $config, $approval_on_behalf;

	$bid = $sessioninfo['branch_id'];
	
	if ($approval_on_behalf) {
		$u = explode(',',$approval_on_behalf['on_behalf_of']);
		$search_approval = $u[0];
		$doc_filter = ' and cn.id = '.mi($_REQUEST['id']).' and cn.branch_id = '.mi($_REQUEST['branch_id']).' ';
	}
	else $search_approval = $sessioninfo['id'];

	$sql = "select cn.*, approvals, flow_approvals as org_approvals, branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name
from ".NOTE_TBL." cn
left join branch_approval_history bah on cn.approval_history_id = bah.id and cn.branch_id=bah.branch_id
left join user on user.id=cn.user_id
left join branch on cn.branch_id = branch.id
where (
(approvals like '|$search_approval|%' and approval_order_id=1) or
(approvals like '%|$search_approval|%' and approval_order_id in (2,3))
) and cn.branch_id=$bid and cn.status=1 and cn.approved=0 and cn.active=1 $doc_filter ";
	//print $sql;
   	$con->sql_query($sql);
	while($list=$con->sql_fetchrow()){
		$cn_list[]=$list;
	}
    $smarty->assign("sheet_list", $cn_list);
	$smarty->assign("PAGE_TITLE", "Consignment ".ucwords(strtolower(SHEET_NAME))." Approval");
   	$smarty->display("consignment.credit_note.approval.tpl");
}
?>
