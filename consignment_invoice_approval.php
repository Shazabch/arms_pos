<?

/*
3/30/2009 1:00 PM Andy
- sort the drop down list by ci_branch_id

4/1/2010 5:40:47 PM Andy
- Monthly report change to only show active branch

6/24/2011 3:55:33 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 11:10:37 AM Andy
- Change split() to use explode()

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

8/1/2013 4:11 PM Andy
- Change to prompt error if the ci is not allow to approve/reject.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('CI_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CI_APPROVAL', BRANCH_CODE), "/index.php");
include('consignment.include.php');
include("consignment_invoice.include.php");

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

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
		case 'ajax_load_ci':
		    load_branch();
			load_branch_group();
			
			$form=array();
			$id=mi($_REQUEST['id']);
			$branch_id=mi($_REQUEST['branch_id']);
			$form=load_ci_header($id, $branch_id);
			$form['approval_screen']=1;
    		$smarty->assign("form", $form);
			$smarty->assign("ci_items", load_ci_items($id, $branch_id));
    		$smarty->assign("readonly", 1);
			$smarty->display("consignment_invoice.new.tpl");
			exit;
			
		case 'check_printed_report':
			if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($_REQUEST['curr_date'],$_REQUEST['branch_id'],'ci')) {
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
			$ci_id=intval($form['id']);
			$branch_id=intval($form['branch_id']);
			
		    if ($form['a']=='approve'){
                $status=1;
                // update total amount and discount amount first
				list($total_amt,$discount_percent,$discount_amt) = explode(",",$form['total_and_discount_info']);
				$upd = array();
				$upd['last_update'] = 'CURRENT_TIMESTAMP';

				if(floatval($form['default_discount_percent'])!=floatval($discount_percent)){
                    $upd['total_amount'] = $total_amt;
                    $upd['discount_percent'] = $discount_percent;
                    $upd['discount_amount'] = $discount_amt;
				}
				$con->sql_query("update ci set ".mysql_update_by_field($upd)." where id=$ci_id and branch_id=$branch_id") or die(mysql_error());
			}
			elseif ($form['a']=='reject')
				$status=2;
			elseif ($form['a']=='cancel')
				$status=5;
				
			ci_approval($ci_id, $branch_id, $status, false);
			exit;
		
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}
	
ci_approval_all();

function ci_approval_all(){
	global $smarty, $LANG, $sessioninfo, $con, $approval_on_behalf;
	
	if ($approval_on_behalf) {
		$u = explode(',',$approval_on_behalf['on_behalf_of']);
		$search_approval = $u[0];
		$doc_filter = ' and ci.id = '.mi($_REQUEST['id']).' and ci.branch_id = '.mi($_REQUEST['branch_id']).' ';
	}
	else $search_approval = $sessioninfo['id'];
	
   	/*$con->sql_query("select ci.*, approvals, flow_approvals as org_approvals, category.description as dept_name, branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name,b2.code as ci_branch_name
from ci
left join branch_approval_history bah on ci.approval_history_id = bah.id and ci.branch_id=$sessioninfo[branch_id]
left join category on category.id=ci.dept_id
left join user on user.id=ci.user_id
left join branch on ci.branch_id = branch.id
left join branch b2 on ci.ci_branch_id=b2.id
where bah.approvals like '|$sessioninfo[id]|%' and ci.status=1 and !ci.approved order by ci_branch_id,id");*/

  $con->sql_query("select ci.*, approvals, flow_approvals as org_approvals, category.description as dept_name, branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name,b2.code as ci_branch_name
from ci
left join branch_approval_history bah on ci.approval_history_id = bah.id and ci.branch_id=$sessioninfo[branch_id]
left join category on category.id=ci.dept_id
left join user on user.id=ci.user_id
left join branch on ci.branch_id = branch.id
left join branch b2 on ci.ci_branch_id=b2.id
where (
(approvals like '|$search_approval|%' and approval_order_id=1) or
(approvals like '%|$search_approval|%' and approval_order_id in (2,3))
) and ci.status=1 and ci.approved=0 $doc_filter order by b2.sequence,id");

	while($list=$con->sql_fetchrow()){
		$list['open_info'] = unserialize($list['open_info']);
		
		$ci[]=$list;
	}
    $smarty->assign("ci", $ci);
	$smarty->assign("PAGE_TITLE", "Invoice Approval");
   	$smarty->display("consignment_invoice_approval.index.tpl");
}
?>
