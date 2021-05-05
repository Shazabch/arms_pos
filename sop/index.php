<?php
include_once('include/common.php');
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('SOP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SOP', BRANCH_CODE), "/index.php");

class HOME extends Module{
	function _default(){
	    $this->load_notifications();
	    $this->load_all_pm();
	    //$this->load_user_reminder();
		$this->display();
	}
	
	private function load_notifications(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;
	    
	    // Marketing plan approval
	    if(privilege('SOP_YMP_APPROVAL')){
            $con->sql_query_false("select count(*) as sheet_count
			from ".DATABASE_NAME.".marketing_plan mar_p
left join ".DATABASE_NAME.".approval_history ah on mar_p.approval_history_id=ah.id
where (
(ah.approvals like '|$sessioninfo[id]|%' and ah.approval_order_id=1) or
(ah.approvals like '%|$sessioninfo[id]|%' and ah.approval_order_id in (2,3))
)
and mar_p.active=1 and mar_p.status=1 and mar_p.approved=0 having sheet_count>0", true);
			$smarty->assign("marketing_plan_approvals", $con->sql_fetchrow());
			$con->sql_freeresult();
		}
		
		// masterfile festival date
		if(privilege('SOP_FD_APPROVAL')){
            $con->sql_query_false("select count(*) as sheet_count
			from ".DATABASE_NAME.".festival_sheet fs
left join ".DATABASE_NAME.".approval_history ah on fs.approval_history_id=ah.id
where (
(ah.approvals like '|$sessioninfo[id]|%' and ah.approval_order_id=1) or
(ah.approvals like '%|$sessioninfo[id]|%' and ah.approval_order_id in (2,3))
)
and fs.status=1 and fs.approved=0 having sheet_count>0", true);
			$smarty->assign("festival_sheet_approvals", $con->sql_fetchrow());
			$con->sql_freeresult();
		}
	}
	
	private function load_all_pm(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;
	    
	    $filter = array();
	    $filter[] = "pm.url like '/sop/%'";
	    $filter[] = "pm.to_user_id=".mi($sessioninfo['id']);
	    $filter[] = "pm.status=0";
	    $filter = "where ".join(' and ', $filter);
	    
	    $sql = "select pm.*,user.u as from_user
		from pm
		left join user on user.id=pm.from_user_id
		$filter
		order by pm.id desc";
		//print $sql;
		$con->sql_query_false($sql, true);
		while($r = $con->sql_fetchassoc()){
		    if(!$r['opened'])    $all_pm['unread_count']++;
			$all_pm['pm_list'][] = $r;
			$all_pm['count']++;
		}
		$con->sql_freeresult();
		$smarty->assign('all_pm', $all_pm);
	}
	
	private function load_user_reminder(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;
	    
	    $filter = array();
	    $filter[] = "remd.user_id=".mi($sessioninfo['id']);
	    $filter[] = "remd.active=1";
	    $filter = "where ".join(' and ', $filter);

		$sql = "select remd.*
		from sop_reminder remd
		$filter order by remd.added";
	    
	    //print $sql;
	    $con->sql_query_false($sql, true);
		$today_time = strtotime(date("Y-m-d"));
		$daily_time = 86400;
		$urgent_time = $daily_time*15;
		$warning_time = $daily_time*30;
	    while($r = $con->sql_fetchassoc()){
	        $r['ref_info'] = unserialize($r['ref_info']);
	        $diff_time = strtotime($r['date_to'])-$today_time;
	        if($diff_time<=$urgent_time)    $r['urgent_type'] = 'usr_reminder_urgent';
	        elseif($diff_time<=$warning_time)    $r['urgent_type'] = 'usr_reminder_warning';
	        
			$user_reminder[] = $r;
		}
        $con->sql_freeresult();
        $smarty->assign('user_reminder', $user_reminder);
	}
	
	function ajax_delete_pm(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;
	    
	    $bid = mi($_REQUEST['bid']);
	    $id = mi($_REQUEST['id']);
	    $con->sql_query("update pm set status=1 where branch_id=$bid and id=$id and to_user_id=".mi($sessioninfo['id']));
	}
}

$HOME = new HOME('SOP');
?>
