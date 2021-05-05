<?
/*
REVISION HISTORY
================
11/28/2007 3:54:56 PM gary
- remove the sku_items_cost query.

1/25/2011 10:41:47 AM Andy
- Fix a bugs which cause multiple approval make document stuck.

6/24/2011 2:50:49 PM Andy
- Make all branch default sort by sequence, code.

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/25/2013 5:04 PM Andy
- Enhance to check approval settings when confirm/approve.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

1/21/2014 4:04 PM Justin
- Enhanced to check and update serial no accordingly base on adjustment item's status.

1/28/2014 11:39 AM Justin
- Enhanced to have serial no feature.

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

10/7/2016 11:46 AM Andy
- Fixed stucked approval redirect to wrong php.

10/26/2016 3:25 PM Andy
- Fixed load approval listing when got config adjustment_branch_selection.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ADJ_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ADJ_APPROVAL', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

include("adjustment.include.php");

$approval_status = array(1 => "Approved", 2 => "Rejected", 3 => "KIV (Pending)", 4 => "Terminated");

init_selection();

$branch_id = mi($_REQUEST['branch_id']);
if ($branch_id ==''){
	$branch_id = $sessioninfo['branch_id'];
}

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
	
		case 'ajax_load_adj':
		    if($config['adjustment_branch_selection']){
                load_branch();
		    	$branch_group = load_branch_group();
			}
			load_adj(false, true, false, true);
			$smarty->display("adjustment.new.tpl");
			exit;

		case 'approval_validate':
			approval_validate();
			exit;
		
		case 'cancel':
		case 'approve':
		case 'reject':
		    //print_r($_REQUEST);die();
		    if ($_REQUEST['a']=='approve')
				$status=1;
			elseif ($_REQUEST['a']=='reject')
				$status=2;
			elseif ($_REQUEST['a']=='cancel')
				$status=5;
				
			$aid = intval($_REQUEST['approval_history_id']);
			$id = intval($_REQUEST['id']);
			$comment = $_REQUEST['comment'];
			if ($approval_on_behalf) {
				$comment .= " (by ".$on_behalf_by_u." on behalf of ".$on_behalf_of_u.")";
			}
			$comment = ms($comment);
			$approvals = $_REQUEST['approvals'];
			check_must_can_edit($branch_id, $id, true);
			
			if ($status==1){ // approve
				/*$approvals = str_replace("|$sessioninfo[id]|","|",$approvals);
				if ($approvals == '|') $approved = 1;*/
				$params = array();
				$params['approve'] = 1;
				$params['user_id'] = $sessioninfo['id'];
				$params['id'] = $aid;
				$params['branch_id'] = $branch_id;
				$params['update_approval_flow'] = true;
				$is_last = check_is_last_approval_by_id($params, $con);
				if ($is_last)  $approved = 1;
			}
			else {
				$con->sql_query("update branch_approval_history set status=$status,approvals = ".ms($approvals)." where id = $aid and branch_id = $branch_id");
			}
			if(!$approved)$approved=0;
			
			$con->sql_query("insert into branch_approval_history_items (approval_history_id, branch_id, user_id, status, log) values ($aid, $branch_id, $sessioninfo[id], $status, $comment)");
						
			//$con->sql_query("update branch_approval_history set status=$status,approvals = ".ms($approvals)." where id = $aid and branch_id = $branch_id");
			
			$con->sql_query("update adjustment set status=$status, approved=$approved where id=$id and branch_id=$branch_id");					
			$to = get_pm_recipient_list2($id,$aid,$status,'approval',$branch_id,'adjustment');
			$status_str = ($is_last || $status != 1) ? $approval_status[$status] : '';
			send_pm2($to, "Adjustment Approval (ID#$id) $status_str", "adjustment.php?a=view&id=$id&branch_id=$branch_id", array('module_name'=>'adjustment'));
			
			if ($approved){
				//update sku_items_cost for each items.
				update_sku_item_cost($id,$branch_id);
				
				// serial no handler
				$params = array();
				$params['id'] = $id;
				$params['branch_id'] = $branch_id;
				$params['skip_sn_error'] = true;
				$params['use_tmp'] = false;
				manage_serial_no($params);
				
				log_br($sessioninfo['id'], 'Adjustment', $id, "Adjustment Fully Approved by $sessioninfo[u] (ID#$id)");			
			}
			elseif ($status==1)
			    log_br($sessioninfo['id'], 'Adjustment', $id, "Adjustment Approved by $sessioninfo[u] (ID#$id)");
			elseif ($status==2)
			    log_br($sessioninfo['id'], 'Adjustment', $id, "Adjustment Rejected by $sessioninfo[u] (ID#$id)");
			elseif ($status==5)
			    log_br($sessioninfo['id'], 'Adjustment', $id, "Adjustment Cancelled by $sessioninfo[u] (ID#$id)");
			else
			    die("WTF?");
				
			if ($approval_on_behalf) {
				header("Location: /stucked_document_approvals.php?m=adjustment");
				exit;
			}
            if($config['adjustment_branch_selection'])
                header("Location: /adjustment_approval.php?t=$_REQUEST[a]&id=$id".(BRANCH_CODE == 'HQ' ? "&original_approval_bid=".$_REQUEST['original_approval_bid'] : ''));
			else header("Location: /adjustment_approval.php?t=$_REQUEST[a]&id=$id");
		    exit;

		
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    fail("<h1>Unhandled Request</h1>");
		    exit;
	}
}
	
load_approval_list();

function load_approval_list(){
	global $smarty, $LANG, $sessioninfo, $con, $config, $approval_on_behalf;
	
	$bid_filter = "";
	if(BRANCH_CODE == 'HQ' && $config['adjustment_branch_selection']){
		if($_REQUEST['original_approval_bid'])	$_REQUEST['branch_id'] = $_REQUEST['original_approval_bid'];
		$original_approval_bid = mi($_REQUEST['branch_id']);
		if($_REQUEST['branch_id'])	$bid_filter = " and adj.branch_id=".$original_approval_bid;
		
	}
	else $bid_filter = " and adj.branch_id=".mi($sessioninfo['branch_id']);
	
	if ($approval_on_behalf) {
		$u = explode(',',$approval_on_behalf['on_behalf_of']);
		$search_approval = $u[0];
		$doc_filter = ' and adj.id = '.mi($_REQUEST['id']).' and adj.branch_id = '.mi($_REQUEST['branch_id']).' ';
	}
	else $search_approval = $sessioninfo['id'];
	
   	$con->sql_query("select adj.*, approvals, flow_approvals as org_approvals, category.description as dept_name, branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name
from adjustment adj
left join branch_approval_history bah on adj.approval_history_id = bah.id and adj.branch_id=bah.branch_id
left join category on category.id=adj.dept_id
left join user on user.id=adj.user_id
left join branch on adj.branch_id = branch.id
where (
(bah.approvals like '|$search_approval|%' and bah.approval_order_id=1) or
(bah.approvals like '%|$search_approval|%' and bah.approval_order_id in (2,3))
) and adj.status=1 and adj.approved=0 $bid_filter $doc_filter");

	while($list=$con->sql_fetchrow()){
		$adj[]=$list;
	}
	//echo"<pre>";print_r($adj);echo"</pre>";		
    $smarty->assign("adj", $adj);
    $smarty->assign("original_approval_bid", $original_approval_bid);
	$smarty->assign("PAGE_TITLE", "Adjustment Approval");
   	$smarty->display("adjustment_approval.index.tpl");
}

function load_branch($id=0){
	global $con,$smarty;

	if($id>0)   $filter = "where id=".mi($id);
	$q_b = $con->sql_query("select * from branch $filter order by sequence,code") or die(mysql_error());
	while($r = $con->sql_fetchrow($q_b)){
		$branches[$r['id']] = $r;
	}
	$con->sql_freeresult($q_b);
	$smarty->assign('branches',$branches);
	return $branches;
}

function approval_validate(){
	global $con, $smarty, $config, $sessioninfo, $LANG;
	
	$tmp = $ret = array();
	if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($_REQUEST['curr_date'],$branch_id,'adjustment')) {
		$ret['error'] = "<div class=errmsg><ul><li>".$LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED']."</li></ul></div>";
	}
	//$tmp['error'] = "<div class=errmsg><ul><li>".$LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED']."</li></ul></div>";

	if(!$tmp['error']){
		$params = array();
		$form = $_REQUEST;
		$params['id'] = $form['id'];
		$params['branch_id'] = $form['branch_id'];
		$params['skip_sn_error'] = false;
		$params['use_tmp'] = false;
		$sn_error = manage_serial_no($params);
		if($sn_error){
			$smarty->assign("sn_error", $sn_error);
			$smarty->assign("form_name", "f_b");
			$tmp['sn_error'] = $smarty->fetch("adjustment.sn.confirmation.tpl");
		}
	}
	
	if(!$tmp){
		$tmp['ok'] = 1;
	}
	
	$ret[] = $tmp;
	
	print json_encode($ret);
}
?>
