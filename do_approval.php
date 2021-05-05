<?
/*
4/22/2009 2:27 PM Andy
- modify to get $_REQUEST[branch_id] to only load specified branch DO

8/6/2009 5:31:32 PM Andy
- add to show credit sales DO

11/16/2009 10:29:52 AM Andy
- fix to only get active DO

11/20/2009 12:39:37 PM Andy
- pass one more parameter ($form) to function load_do_items

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('DO_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DO_APPROVAL', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
include("do.include.php");

init_selection();

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

$branch_id = mi($_REQUEST['branch_id']);
if ($branch_id ==''){
	$branch_id = $sessioninfo['branch_id'];
}

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
		case 'ajax_load_do':
			$form=array();
			$id=mi($_REQUEST['id']);
			$branch_id=mi($_REQUEST['branch_id']);
			$form=load_do_header($id, $branch_id);		
			$form['approval_screen']=1;
    		$smarty->assign("form", $form);
			$smarty->assign("do_items", load_do_items($id, $branch_id,$form));
    		$smarty->assign("readonly", 1);
    		if($form['do_type']=='transfer')    $smarty->display("do.transfer.new.tpl");
    		elseif($form['do_type']=='credit_sales')    $smarty->display("do.credit_sales.new.tpl");
    		else	$smarty->display("do.new.tpl");
			exit;
			
		case 'check_printed_report':
			if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($_REQUEST['curr_date'],$branch_id)) {
				print '<div class=errmsg><ul><li>'.$LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED'].'</li></ul></div>';
			}
			else print '0';
			exit;

		case 'cancel':
			$set_active=1;
		case 'approve':
		case 'reject':
			$form=$_REQUEST;
			$do_id=intval($form['id']);
			$branch_id=intval($form['branch_id']);
			
		    if ($form['a']=='approve')
				$status=1;
			elseif ($form['a']=='reject')
				$status=2;
			elseif ($form['a']=='cancel')
				$status=5;
				
			do_approval($do_id, $branch_id, $status, false);
			exit;
		
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}
	
do_approval_all();

function do_approval_all(){
	global $smarty, $LANG, $sessioninfo, $con, $config, $approval_on_behalf;
	
	if($config['consignment_modules'])  $bid = intval($_REQUEST['branch_id']);
	else $bid = $sessioninfo['branch_id'];
	
	if ($approval_on_behalf) {
		$u = explode(',',$approval_on_behalf['on_behalf_of']);
		$search_approval = $u[0];
		$doc_filter = ' and do.id = '.mi($_REQUEST['id']).' and do.branch_id = '.mi($_REQUEST['branch_id']).' ';
	}
	else $search_approval = $sessioninfo['id'];
	
	/*$sql = "select do.*, approvals, flow_approvals as org_approvals, category.description as dept_name, branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name
from do
left join branch_approval_history bah on do.approval_history_id = bah.id and do.branch_id=$bid
left join category on category.id=do.dept_id
left join user on user.id=do.user_id
left join branch on do.branch_id = branch.id
where bah.approvals like '|$sessioninfo[id]|%' and do.status=1 and do.approved=0 and do.active=1";*/

	$sql = "select do.*, approvals, flow_approvals as org_approvals, category.description as dept_name, branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name
from do
left join branch_approval_history bah on do.approval_history_id = bah.id and do.branch_id=$bid
left join category on category.id=do.dept_id
left join user on user.id=do.user_id
left join branch on do.branch_id = branch.id
where (
(approvals like '|$search_approval|%' and approval_order_id=1) or
(approvals like '%|$search_approval|%' and approval_order_id in (2,3))
) and do.status=1 and do.approved=0 and do.active=1 $doc_filter";
	//print $sql;
   	$con->sql_query($sql);
	while($list=$con->sql_fetchrow()){
		$do[]=$list;
	}
    $smarty->assign("do", $do);
	$smarty->assign("PAGE_TITLE", "DO Approval");
   	$smarty->display("do_approval.index.tpl");
}

?>
