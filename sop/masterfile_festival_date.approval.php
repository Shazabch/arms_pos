<?php
include_once('include/common.php');
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('SOP_FD_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SOP_FD_APPROVAL', BRANCH_CODE), "index.php");

include_once('festival_date.include.php');

class MASTERFILE_FESTIVAL_DATE_APPROVAL extends Module{
	var $branches = array();

    function __construct($title){
		global $con, $smarty, $sessioninfo;

		if(!$_REQUEST['skip_init_load'])    $this->init_load(); // initial load

		$smarty->assign('approval_screen' , 1);
		parent::__construct($title);
	}

	function _default(){
	    global $con, $smarty;

	    // load the festival sheet list
	    $this->load_festival_sheet_list();

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
	
	private function load_festival_sheet_list(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;
	    
	    $sql = "select fs.*, ah.approvals, ah.flow_approvals as org_approvals, user.u as user_name
from ".DATABASE_NAME.".festival_sheet fs
left join ".DATABASE_NAME.".approval_history ah on fs.approval_history_id = ah.id
left join user on user.id=fs.user_id
where (
(ah.approvals like '|$sessioninfo[id]|%' and ah.approval_order_id=1) or
(ah.approvals like '%|$sessioninfo[id]|%' and ah.approval_order_id in (2,3))
) and fs.status=1 and fs.approved=0";
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
			$festival_sheet_list[] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('festival_sheet_list', $festival_sheet_list);
	}
	
	function load_festival_date_list(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;

	    $year = mi($_REQUEST['year']);
	    if(!$year){
			header("Location: $_SERVER[PHP_SELF]");
			return;
		}
		open_festival_sheet($year);
	}
	
	function save_festival_sheet_approval(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;

		$year = mi($_REQUEST['year']);
		$action = trim($_REQUEST['action']);
		$params['comment'] = trim($_REQUEST['comment']);

		if($action=='approve')  $status = 1;
		elseif($action=='reject')   $status = 2;
		elseif($action=='terminate')    $status = 5;
		else    die('Invalid Action');

		$success = festival_sheet_approval($year, $status, $params);   // call function to do all the approval stuff

		if($success){   // action failed
            $ret['ok'] = 1;
		}else{
			$ret['failed_reason'] = 'This festival sheet no longer need approval, please refresh the page.';
		}

		print json_encode($ret);
	}
}

$MASTERFILE_FESTIVAL_DATE_APPROVAL = new MASTERFILE_FESTIVAL_DATE_APPROVAL('Festival Date Master File Approval');
?>
