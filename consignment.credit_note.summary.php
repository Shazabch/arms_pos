<?php
/*
1/21/2011 9:58:45 AM Andy
- Add can search rejected, canceled/terminated for invoice and CN/DN.

1/21/2015 5:55 PM Justin
- Enhanced to have GST calculation.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if(defined('DEBIT_NOTE_MODE')){
	// debit note mode
	if (!privilege('CON_DEBIT_NOTE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CON_DEBIT_NOTE', BRANCH_CODE), "/index.php");
}else{
	// credit note mode
	if (!privilege('CON_CREDIT_NOTE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CON_CREDIT_NOTE', BRANCH_CODE), "/index.php");
}

include("consignment.include.php");
include("consignment.credit_note.include.php");

class CREDIT_NOTE_SUMMARY extends Module{
	var $branches = array();
	var $branch_group = array();
	
    function __construct($title){
		global $con, $smarty;

		if(!$_REQUEST['skip_init_load'])    init_selection();

		parent::__construct($title);
	}

	function _default(){
	    global $con, $smarty, $config;

        if (!isset($_REQUEST['date_to'])) $_REQUEST['date_to'] = date('Y-m-d');
		if (!isset($_REQUEST['date_from'])) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));

		// load user list
        $con->sql_query("select cn.user_id, (select u from user where user.id=cn.user_id) as u
		from ".NOTE_TBL." cn
		group by cn.user_id");
		while($r = $con->sql_fetchassoc()){
			$users[$r['user_id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign("users", $users);
		
		if($_REQUEST['load_report']){
			$this->load_report();
		}

		$this->display('consignment.credit_note.summary.tpl');
	}
	
	private function load_report(){
	    global $con, $smarty;
	    
	    $filter = array();
	    $date_from = trim($_REQUEST['date_from']);
	    $date_to = trim($_REQUEST['date_to']);
	    $user_id = mi($_REQUEST['user_id']);
	    $to_branch_id = mi($_REQUEST['to_branch_id']);
	    $status = mi($_REQUEST['status']);
	    
	    if(!$date_from || !$date_to)    $err[] = "Please select date.";
	    elseif(strtotime($date_from)>strtotime($date_to))   $err[] = "Date to cannot early than date from";
	    
	    if($err){   // got error
			$smarty->assign('err', $err);
			return false;
		}
	    $filter[] = "cn.date between ".ms($date_from)." and ".ms($date_to);
	    
	    if($user_id>0)  $filter[] = "cn.user_id=$user_id";
	    if($to_branch_id>0) $filter[] = "cn.to_branch_id=$to_branch_id";
	    $filter[] = "cn.active=1";
	    
		switch ($status){
			case 1: // show saved inv
	        	$filter[] = "cn.status in (0,1) and cn.approved=0";
	        	break;
			case 2: // show approved
			    $filter[] = "cn.status=1 and cn.approved=1";
			    break;
            case 3: // rejected
			    $filter[] = "cn.status=2 and cn.approved=0";
			    break;
	        case 4: // terminated
			    $filter[] = "cn.status in (4,5) and cn.approved=0";
			    break;
			default:
			    //$filter[] = "cn.status in (0,1)";
			    break;
		}
		
		$filter = "where ".join(' and ', $filter);
		//print $filter;
		
		$sql =  "select cn.*, branch.report_prefix as branch_prefix, branch.code as branch_name_1,b2.code as branch_code_2, bah.approvals, user.u as user_name, b2.description as to_branch_description, bah.approval_order_id
from ".NOTE_TBL." cn
left join branch on cn.branch_id = branch.id
left join branch b2 on cn.to_branch_id = b2.id
left join user on user.id = cn.user_id
left join branch_approval_history bah on bah.id = cn.approval_history_id
$filter order by cn.date";
		//print $sql;
		$con->sql_query($sql);
		
		$is_under_gst = false;
		while($r = $con->sql_fetchassoc()){
			if($r['is_under_gst']) $is_under_gst = true;
			$data[] = $r;
		}
		$con->sql_freeresult();
		
		//print_r($data);
		$smarty->assign('data', $data);
		$smarty->assign('is_under_gst', $is_under_gst);
	}
}

$CREDIT_NOTE_SUMMARY = new CREDIT_NOTE_SUMMARY('Consignment '.ucwords(strtolower(SHEET_NAME)).' Summary');
?>
