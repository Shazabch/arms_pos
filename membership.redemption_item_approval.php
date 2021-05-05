<?php
/*
1/20/2011 12:05:35 PM Justin
- Added to capture update status and store into View Log.
- Added to capture approved by.

6/24/2011 4:59:33 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:08:00 PM Andy
- Change split() to use explode()

3/25/2014 4:36 PM Justin
- Modified the wording from "Canceled" to "Cancelled".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP_ITEM_CFRM')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_ITEM_CFRM', BRANCH_CODE), "/index.php");
$maintenance->check(34);

class MembershipRedemptionItemApproval extends Module
{
	var $branch_id;
	function __construct($title, $template='')
	{
		global $sessioninfo,$con,$smarty;
		$branch_id = get_request_branch(true);
		$_REQUEST['branch_id'] = $branch_id;
		
		if(!$_REQUEST['no_init_load'])	$this->init_load();
		$smarty->assign('allow_edit',1);
		parent::__construct($title, $template);	
	
	}
	
	function _default()
	{
		$this->init_table();
		$this->display();	
	}
	
	private function init_table(){}
	
	private function init_load(){
		global $con, $smarty;
		
		// branch
		$con->sql_query("select * from branch order by sequence,code") or die(mysql_error());
		while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
			$filter_branches[$r['code']] = $r['id'];
		}
		$smarty->assign('branches',$branches);
		$smarty->assign('filter_branches',$filter_branches);
		
		// branch group
		// load header
		$con->sql_query("select * from branch_group");
		while($r = $con->sql_fetchrow()){
		    $branches_group['header'][$r['id']] =$r;
		}

		// load items
		$con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id
		order by branch.sequence, branch.code");
		while($r = $con->sql_fetchrow()){
	        $branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}

		$smarty->assign('branches_group',$branches_group);
	}
	
	function refresh_item_list(){
		global $con, $smarty, $sessioninfo;

		$t = mi($_REQUEST['t']);

		$having = "having if(days_left != '', days_left > 0, 1=1)";

		switch($t){
			case 1:	// waiting for approval
				$filter[] = "mrs.active = 1 and mrs.confirm=0";
				break;
			case 2: // approved
				$filter[] = "mrs.active = 1 and mrs.confirm=1";
				break;
			case 3: // inactive
				$filter[] = "mrs.active = 0 and mrs.confirm=1";
				$having = '';
				$order_by = "mrs.cancel_date,";
				break;
			default:
				die('Invalid Page');
		}

		if($_REQUEST['branch_id']) $branch_id = $_REQUEST['branch_id'];
		else{
			if(BRANCH_CODE!='HQ'){
				$branch_id = $sessioninfo['branch_id'];
			}else{
				$con->sql_query("select group_concat(branch_id) as grp_branch_id
								 from user_privilege 
								 where privilege_code = 'MEMBERSHIP_ITEM_CFRM'
								 and allowed and user_id = $sessioninfo[id]");
			
				$branch_id = $con->sql_fetchfield(0);
			}
		}

		if($branch_id) $filter[] = "mrs.branch_id in ($branch_id)";

	    $sql = "select mrs.*, if(sp.price is null, si.selling_price, sp.price) as selling_price, si.sku_item_code,sc.grn_cost,
			 	sc.qty, si.id as sku_item_id, si.description, branch.code as bcode, ucase(ut.u) as cancel_user, ucase(uc.u) as create_user, ucase(um.u) as approve_user,
			 	if(mrs.valid_date_to != '' and mrs.valid_date_to != '0000-00-00', datediff(mrs.valid_date_to, date_format(CURDATE(), '%Y-%m-%d')) + 1, '') as days_left
				from membership_redemption_sku mrs
				left join sku_items si on mrs.sku_item_id = si.id
				left join sku_items_cost sc on sc.sku_item_id = si.id and sc.branch_id = ".$sessioninfo['branch_id']."
				left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id = ".$sessioninfo['branch_id']."
				left join branch on branch.id=mrs.branch_id
				left join user ut on ut.id = mrs.cancel_by
				left join user uc on uc.id = mrs.created_by
				left join user um on um.id = mrs.approved_by
				where ".join(' and ', $filter)."
				$having
				order by $order_by mrs.id";
		//print $sql;
        $r_1 = $con->sql_query($sql);

		while($r = $con->sql_fetchrow($r_1)){

		    $r['available_branches'] = unserialize($r['available_branches']);
		    if($r['available_branches']){
				$r['available_branches2'] = join(',',array_keys($r['available_branches']));
			}

            $redemption_items[] = $r;
		}
		$con->sql_freeresult();
		
		$smarty->assign('redemption_items',$redemption_items);

		$this->display('membership.redemption_item_approval.list.tpl');
	}
	
	function ajax_set_item_status(){
        global $con, $sessioninfo;

		$item_array = $_REQUEST['item_array'];
		$v = mi($_REQUEST['v']);
		
		if($v == 1){ // is confirm
			$status_msg = "Approved";
			$updates = "confirm = 1, approved_by = ".mi($sessioninfo['id']);
		}elseif($v == 2){ // is reset
			$status_msg = "Reset";
			$updates = "active = 0, confirm = 0";
		}elseif($v == 3){ // is cancel
			$status_msg = "Cancelled";
			$updates = "active = 0, cancel_by = ".mi($sessioninfo['id']).", cancel_date = CURRENT_TIMESTAMP";
		}elseif($v == 4){ // is reject
			$status_msg = "Rejected";
			$updates = "active = 0";
		}
		
		if($item_array){
			foreach($item_array as $key){
                list($bid,$id) = explode("_",$key);
                $con->sql_query("update membership_redemption_sku set $updates where branch_id=$bid and id=$id");
                log_br($sessioninfo['id'], 'Redemption',$sku_item_id, "Redemption Item ".$status_msg." (Branch#".$bid.", Item ID#$id)");
			}
		}

		print "OK";
	}
}

$MembershipRedemptionItemApproval = new MembershipRedemptionItemApproval ('Redemption Item Approval');
?>
