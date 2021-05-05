<?
/*
4/3/2012 3:37:45 PM Andy
- Change function init_selection to init_so_selection().

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('SO_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SO_APPROVAL', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
include("sales_order.include.php");

init_so_selection();

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
		case 'ajax_load_sales_order':
			$form=array();
			$id=mi($_REQUEST['id']);
			$branch_id=mi($_REQUEST['branch_id']);
			$form=load_order_header($branch_id, $id);
			$form['approval_screen']=1;
    		$smarty->assign("form", $form);
    		$smarty->assign("readonly", 1);
    		$items = load_order_items($branch_id, $id);
			$smarty->assign("items", $items);
    		$smarty->display("sales_order.open.tpl");
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
				
			sales_order_approval($id, $branch_id, $status, false);
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
		$doc_filter = ' and so.id = '.mi($_REQUEST['id']).' and so.branch_id = '.mi($_REQUEST['branch_id']).' ';
	}
	else $search_approval = $sessioninfo['id'];

	$sql = "select so.*, approvals, flow_approvals as org_approvals, branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name
from sales_order so
left join branch_approval_history bah on so.approval_history_id = bah.id and so.branch_id=$bid
left join user on user.id=so.user_id
left join branch on so.branch_id = branch.id
where (
(approvals like '|$search_approval|%' and approval_order_id=1) or
(approvals like '%|$search_approval|%' and approval_order_id in (2,3))
) and so.status=1 and so.approved=0 and so.active=1 $doc_filter ";
	//print $sql;
   	$con->sql_query($sql);
	while($list=$con->sql_fetchrow()){
		$order_list[]=$list;
	}
    $smarty->assign("order_list", $order_list);
	$smarty->assign("PAGE_TITLE", "Sales Order Approval");
   	$smarty->display("sales_order_approval.tpl");
}

?>
