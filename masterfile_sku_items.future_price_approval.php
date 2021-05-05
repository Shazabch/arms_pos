<?php
/*
7/29/2013 4:49 PM Andy
- Fix Batch Price Change Approval Module to load document based on the approval sequence.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

7/21/2016 10:16 AM Andy
- Fixed to check privilege 'MST_SKU_UPDATE_FUTURE_PRICE'.
*/
include("include/common.php");
include("masterfile_sku_items.future_price.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MST_SKU_UPDATE_FUTURE_PRICE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE_FUTURE_PRICE', BRANCH_CODE), "/index.php");

class BATCH_PRICE_CHANGE_APPROVAL_MODULE extends Module{
	function __construct($title){
		global $con, $smarty, $sessioninfo;

		$bid = $sessioninfo['branch_id'];
		
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
		$this->approval_on_behalf = $approval_on_behalf;
		
		if ($approval_on_behalf) {
			$u = explode(',',$approval_on_behalf['on_behalf_of']);
			$search_approval = $u[0];
			$doc_filter = ' and sifp.id = '.mi($_REQUEST['id']).' and sifp.branch_id = '.mi($_REQUEST['branch_id']).' ';
		}
		else $search_approval = $sessioninfo['id'];

		$sql = $con->sql_query("select sifp.*, bah.approvals, bah.flow_approvals as org_approvals, b.report_prefix as prefix, 
								b.code as branch_name, u.u as user_name
								from sku_items_future_price sifp
								left join branch_approval_history bah on sifp.approval_history_id = bah.id and sifp.branch_id=$bid
								left join user u on u.id=sifp.user_id
								left join branch b on sifp.branch_id = b.id
								where (
								(bah.approvals like '|$search_approval|%' and bah.approval_order_id=1) or
								(bah.approvals like '%|$search_approval|%' and bah.approval_order_id in (2,3))
								)
								and sifp.status=1 and sifp.approved=0 and sifp.active=1 $doc_filter");

		$future_price = $con->sql_fetchrowset($sql);
		$con->sql_freeresult($sql);
		$smarty->assign("future_price", $future_price);

		// load branches
		$con->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$branches);
		
    	parent::__construct($title);
    }
	
    function _default(){
		global $con, $smarty;
	    $this->display();
	}

	function ajax_load_fp(){
		global $smarty;

		$form=array();

		$fp_id = $_REQUEST['id'];
		$branch_id = $_REQUEST['branch_id'];
		$form = load_header($fp_id, $branch_id);
		$items = load_items($fp_id, $branch_id);
		$form = array_merge($form, $items);

		$form['approval_screen']=1;
		$smarty->assign("readonly", 1);
		$smarty->assign("form", $form);
		$smarty->display("masterfile_sku_items.future_price.open.tpl");
	}
	
	function approve(){
		future_price_approval($_REQUEST['id'], $_REQUEST['branch_id'], 1, false, $this->approval_on_behalf);
	}

	function reject(){
		future_price_approval($_REQUEST['id'], $_REQUEST['branch_id'], 2, false, $this->approval_on_behalf);
	}

	function cancel(){
		future_price_approval($_REQUEST['id'], $_REQUEST['branch_id'], 5, false, $this->approval_on_behalf);
	}
}

$BATCH_PRICE_CHANGE_APPROVAL_MODULE = new BATCH_PRICE_CHANGE_APPROVAL_MODULE('Batch Price Change Approval');
?>
