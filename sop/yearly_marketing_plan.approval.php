<?php
include_once('include/common.php');
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('SOP_YMP_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SOP_YMP_APPROVAL', BRANCH_CODE), "index.php");

include_once('yearly_marketing_plan.include.php');

class YEARLY_MARKETING_PLAN_APPROVAL extends Module{
	var $branches = array();
	
    function __construct($title){
		global $con, $smarty, $sessioninfo;

		if(!$_REQUEST['skip_init_load'])    $this->init_load(); // initial load
		
		$smarty->assign('approval_screen' , 1);
		parent::__construct($title);
	}

	function _default(){
	    global $con, $smarty;

	    // load the marketing plan list which waiting for approve
	    $this->load_marketing_plan_list();

		$this->display();
	}
	
	private function init_load(){
        global $con, $smarty;

		// load branches
		$this->branches = array();
		$con->sql_query_false("select * from branch order by sequence", true);
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches', $this->branches);
	}
	
	private function load_marketing_plan_list(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;

		$sql = "select mar_p.*, ah.approvals, ah.flow_approvals as org_approvals, user.u as user_name
from ".DATABASE_NAME.".marketing_plan mar_p
left join ".DATABASE_NAME.".approval_history ah on mar_p.approval_history_id = ah.id
left join user on user.id=mar_p.user_id
where (
(ah.approvals like '|$sessioninfo[id]|%' and ah.approval_order_id=1) or
(ah.approvals like '%|$sessioninfo[id]|%' and ah.approval_order_id in (2,3))
) and mar_p.active=1 and mar_p.status=1 and mar_p.approved=0";
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
		    $r['for_branch_id'] = unserialize($r['for_branch_id']);
			$marketing_plan_list[] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('marketing_plan_list', $marketing_plan_list);
	}
	
	function load_marketing_plan_details(){
		global $con, $smarty, $sessioninfo, $SOP_LANG;

		$id = mi($_REQUEST['marketing_plan_id']);
		if(!$id)    display_redir($_SERVER['PHP_SELF'], $this->title, sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $id));  // invalid id

		$form = load_marketing_plan_header($id);   // load header

		if($form){
		    // inactive
			if(!$form['active'])    display_redir($_SERVER['PHP_SELF'], $this->title, sprintf($SOP_LANG['SOP_MARKETING_PLAN_INACTIVE'], $id));
		}else{
		    // not found
            display_redir($_SERVER['PHP_SELF'], $this->title, sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $id));  // invalid id
		}

		// load promotion plan list
		$form['promotion_plan_list'] = load_promotion_plan_list($id);

		$this->update_title($this->title.' - '.$form['title']);    // update title
		$smarty->assign('form', $form);
		$this->display("yearly_marketing_plan.marketing_plan_details.tpl");
	}
	
	function save_marketing_plan(){
		global $con, $smarty, $sessioninfo, $SOP_LANG;
		
		$marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
		$action = trim($_REQUEST['action']);
		$params['comment'] = trim($_REQUEST['comment']);
		
		if($action=='approve')  $status = 1;
		elseif($action=='reject')   $status = 2;
		elseif($action=='terminate')    $status = 5;
		else    die('Invalid Action');
		
		$success = marketing_plan_approval($marketing_plan_id, $status, $params);   // call function to do all the approval stuff
		
		if($success){   // action failed
            $ret['ok'] = 1;
		}else{
			$ret['failed_reason'] = 'This marketing plan no longer need approval, please refresh the page.';
		}
		
		print json_encode($ret);
	}
}

$YEARLY_MARKETING_PLAN_APPROVAL = new YEARLY_MARKETING_PLAN_APPROVAL('Yearly Marketing Plan Approvals');
?>
