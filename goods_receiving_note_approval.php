<?php

/*
12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

7/16/2015 1:33 PM Joo Chia
- GRN future: if got edited from Account Verification, show Account Verification columns

9/23/2015 9.56 AM DingRen
- always show Account Verification on grn approval

11/18/2015 2:09 PM DingRen
- Fix approval list when using approval flow follow sequence

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

8/9/2018 5:36 PM Justin
- Enhanced to show images attached from GRR.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRN_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRN_APPROVAL', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
include("goods_receiving_note2.include.php");

init_selection();

$id = mi($_REQUEST['id']);
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
		case 'ajax_load_grn':
			$form=array();
			$form['approval_screen']=1;
    		$smarty->assign("readonly", 1);
    		$smarty->assign("approval_screen", 1);
			$form=load_grn_header($id, $branch_id);
			//copy_to_tmp($id, $branch_id);
			$form['items']=load_grn_items($id, $branch_id,$form['po_items'], false);

			// check whether got edited from Account Verification, if yes then need to show Account Verification columns
			/*foreach($form['items'] as $gid=>$r){
				if((trim($r['acc_ctn']) != "" && $r['acc_ctn'] >= 0) || (trim($r['acc_pcs']) != "" && $r['acc_pcs'] >= 0) || (trim($r['inv_qty']) != "" && $r['inv_qty'] >= 0) || (trim($r['acc_cost']) != "" && $r['acc_cost'] >= 0)|| (trim($r['inv_cost']) != "" && $r['inv_cost'] >= 0)){
					$smarty->assign("manager_col", 1);
					$smarty->assign("cu_id", 1);
					break;
				}
			}*/
			$smarty->assign("manager_col", 1);
			$smarty->assign("cu_id", 1);
			$smarty->assign("form", $form);
			
			$prms = array();
			$prms['is_grn'] = true;
			$prms['grr_id'] = $form['grr_id'];
			$prms['branch_id'] = $branch_id;
			$appCore->grnManager->load_grr_images($prms);
			
			$smarty->display("goods_receiving_note_approval.show.tpl");
			exit;

		case 'approve':
		case 'reject':
		case 'cancel':
			$form=$_REQUEST;
			
		    if ($form['a']=='approve'){
				$status=1;
			}elseif($form['a']=='reject'){
				$status=2;
			}elseif ($form['a']=='cancel'){
				$status=5;
			}
			grn_approval($id, $branch_id, $status, false);
			exit;
		case 'ajax_add_variance_item':
			ajax_add_variance_item($id, $branch_id);
			exit;
		
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}
	
list_approval_all(false);

function list_approval_all($err){
	global $smarty, $sessioninfo, $con, $approval_on_behalf;
	
	$bid = $sessioninfo['branch_id'];
	
	if ($approval_on_behalf) {
		$u = explode(',',$approval_on_behalf['on_behalf_of']);
		$search_approval = $u[0];
		$doc_filter = ' and grn.id = '.mi($_REQUEST['id']).' and grn.branch_id = '.mi($_REQUEST['branch_id']).' ';
	}
	else $search_approval = $sessioninfo['id'];

	$sql = "select grn.*, approvals, flow_approvals as org_approvals, branch.report_prefix as prefix, 
			branch.code as branch_name, user.u as user_name
			from grn
			left join branch_approval_history bah on grn.approval_history_id = bah.id and grn.branch_id=$bid
			left join user on user.id=grn.user_id
			left join branch on grn.branch_id = branch.id
			where (
(approvals like '|$search_approval|%' and approval_order_id=1) or
(approvals like '%|$search_approval|%' and approval_order_id in (2,3))
) and grn.status=1 and grn.approved=0 and grn.active=1 $doc_filter ";

	//print $sql;
   	$con->sql_query($sql);
	while($list=$con->sql_fetchrow()){
		$grn_list[]=$list;
	}

	$smarty->assign("errm", $err);
    $smarty->assign("grn_list", $grn_list);
	$smarty->assign("PAGE_TITLE", "GRN Approval");
   	$smarty->display("goods_receiving_note_approval.tpl");
}

/*function save_grn_items(){
	global $con, $branch_id;
	
	$form=$_REQUEST;

	for($doc_type=1; $doc_type<=2; $doc_type++){
		if($form[$doc_type.'_pcs']){
			foreach($form[$doc_type.'_pcs'] as $k=>$v){
				$update = array();
			    $update['pcs'] = mi($form[$doc_type.'_pcs'][$k]);
			    $update['item_check'] = mi($form[$doc_type.'_item_return'][$k]);
			    
				$con->sql_query("update tmp_grn_items set " . mysql_update_by_field($update) . " where id=$k and branch_id=$branch_id");
			}
		}
	}
}*/
?>
