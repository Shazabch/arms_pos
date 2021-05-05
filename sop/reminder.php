<?php
include_once('include/common.php');
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('SOP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SOP', BRANCH_CODE), "/index.php");

class REMINDER extends Module{
	var $ref_task_list = array(
		'promotion_activity' => 'Yearly Marketing Plan Promotion Activity'
	);
	
	function __construct($title){
		global $con, $smarty, $sessioninfo;

		//if(!$_REQUEST['skip_init_load'])    $this->init_load(); // initial load
		$smarty->assign('ref_task_list', $this->ref_task_list);
		parent::__construct($title);
	}
	
    function _default(){
        //$this->load_reminder_list();
        $this->display();
    }
    
    function open_reminder(){
		global $con, $smarty, $sessioninfo, $SOP_LANG;
		
		$branch_id = mi($_REQUEST['branch_id']);
		$id = mi($_REQUEST['id']);
		
		if($id>0){  // exists
			$form = $this->load_reminder_row($branch_id, $id);
		}else{  // new
            $con->sql_query("select count(*) from sop_reminder where user_id=".mi($sessioninfo['id']));  // get the max number
		    $form['title'] = $form['remark'] = "Reminder #".mi($con->sql_fetchfield(0)+1);
		    $con->sql_freeresult();

		    // default information
			$form['date_from'] = date('Y').'-01-01';
			$form['date_to'] = date('Y').'-12-31';
			$form['branch_id'] = $sessioninfo['branch_id'];
		}
		
		$smarty->assign('form', $form);
		$this->display('reminder.dialog.tpl');
	}
	
	function open_reminder_task_list(){
		global $con, $smarty, $sessioninfo, $SOP_LANG;
		
		$task = trim($_REQUEST['task']);
		
		switch($task){
			case 'promotion_activity':
			    $this->load_user_promotion_activity_list();
			    exit;
			default:
			    die('Invalid Task Request');
			    break;
		}
	}
	
	private function load_user_promotion_activity_list(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $branch_id = mi($_REQUEST['branch_id']);
        $id = mi($_REQUEST['id']);
        
        // get current user
        $sql = "select * from
		sop_reminder where user_id=".mi($sessioninfo['id'])." and ref_task='promotion_activity'";
		//print $sql;
		$con->sql_query($sql);
		$used_act = array();
		while($r = $con->sql_fetchassoc()){
		    $r['ref_info'] = unserialize($r['ref_info']);
		    
		    if($branch_id==$r['branch_id'] && $id == $r['id']){ // this is user current selected activity
				$selected_promotion_activity = $r;
				continue;
			}
			
			$used_act[] = $r['ref_id'];   // store the used activity
		}
		$con->sql_freeresult();
		
		// load available activity
        $filter = array();
        $filter[] = "mar_usr_act.user_id=".mi($sessioninfo['id']);
        
        $filter = "where ".join(' and ', $filter);
        
		$sql = "select mar_usr_act.* , mar_act.title as mar_act_title, mar_promo.title as mar_promo_title, mar_act.id as activity_id, mar_act.created_branch_id as activity_created_branch_id
from sop_marketing_plan_promotion_user_activity mar_usr_act
left join sop_marketing_plan_promotion_activity mar_act on mar_act.reference_id=mar_usr_act.activity_reference_id
left join sop_marketing_plan_promotion mar_promo on mar_promo.created_branch_id=mar_act.sop_marketing_plan_created_branch_id and mar_promo.id=mar_act.sop_promotion_plan_id
$filter";
		//print $sql;
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
		    if(!$r['activity_id'] || !$r['activity_created_branch_id']) continue;   // invalid activity
		    
			if(in_array($r['activity_reference_id'], $used_act))   continue;   // this activity already used
			$promotion_activity_list[] = $r;
		}
		$con->sql_freeresult();
		
		$smarty->assign('promotion_activity_list', $promotion_activity_list);
		$smarty->assign('selected_promotion_activity', $selected_promotion_activity);
		$this->display('reminder.dialog.task_list.promotion_activity.tpl');
	}
	
	function save_reminder(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $id = mi($_REQUEST['id']);
        $branch_id = mi($_REQUEST['branch_id']);
        
        $upd = array();
        $upd['title'] = trim($_REQUEST['title']);
        $upd['remark'] = trim($_REQUEST['remark']);
        $upd['ref_task'] = trim($_REQUEST['ref_task']);
        $upd['ref_table'] = trim($_REQUEST['ref_table']);
        $upd['ref_id'] = trim($_REQUEST['ref_id']);
        $upd['ref_info'] = $_REQUEST['ref_info'];
        $upd['date_from'] = $_REQUEST['date_from'];
        $upd['date_to'] = $_REQUEST['date_to'];
        $upd['last_update'] = 'CURRENT_TIMESTAMP';
        
        if(!$upd['title'])  $err[] = $SOP_LANG['SOP_REMINDER_NEED_TITLE'];
        if(!$upd['date_from'] || !$upd['date_to'])  $err[] = $SOP_LANG['SOP_REMINDER_NEED_BOTH_DATE'];
        if($upd['ref_task']){   // not custom task
			if(!$upd['ref_info']['task_name'])  $err[] = $SOP_LANG['SOP_REMINDER_NEED_PICK_TASK'];
			if(!$upd['ref_id'])  $err[] = $SOP_LANG['SOP_REMINDER_NEED_REF_ID'];
		}
		
		if($err){   // found error
		    sop_display_error($err);
		}
		
		$upd['ref_info'] = serialize($upd['ref_info']);
		
		if($id>0){  // exists
			$con->sql_query("update sop_reminder set ".mysql_update_by_field($upd)." where branch_id=$branch_id and id=$id and user_id=".mi($sessioninfo['id']));
			$affected = $con->sql_affectedrows();
			if(!$affected){ // update not success
                $err[] = $SOP_LANG['SOP_REMINDER_NOT_UPDATED'];
                sop_display_error($err);
			}
		}else{  // new
		    $branch_id = $sessioninfo['branch_id'];
			$upd['branch_id'] = $branch_id;
			$upd['user_id'] = $sessioninfo['id'];
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into sop_reminder ".mysql_insert_by_field($upd));
			$upd['id'] = $id = $con->sql_nextid();
		}
		

		$ret = array();
		$ret['branch_id'] = $branch_id;
		$ret['id'] = $id;
		$ret['html'] = $this->load_reminder_row($branch_id, $id, true);

		// record log
		log_br($sessioninfo['id'], 'SOP', $id, "Reminder Saved: Branch ID#$branch_id , ID#".$id);
        
		print json_encode($ret);
	}
	
	private function load_reminder_row($branch_id, $id, $show_tpl = false){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        // escape integer
        $branch_id = mi($branch_id);
        $id = mi($id);
        
        $filter = array();
        $filter[] = "remd.user_id=".mi($sessioninfo['id']);
        $filter[] = "remd.branch_id=$branch_id and remd.id=$id";
        
        $filter = "where ".join(' and ', $filter);

		$sql = "select remd.* 
		from sop_reminder remd
		$filter order by remd.id";
		
		$con->sql_query($sql);
		$reminder = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($reminder){
            $reminder['ref_info'] = unserialize($reminder['ref_info']);
		}
		
		if($show_tpl){
            $smarty->assign('reminder', $reminder);
            return $smarty->fetch('reminder.list.row.tpl');
		}
		
		return $reminder;
	}
	
	function load_reminder_list(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;

        $filter = array();
        $filter[] = "remd.user_id=".mi($sessioninfo['id']);

        $filter = "where ".join(' and ', $filter);

		$sql = "select remd.*
		from sop_reminder remd
		$filter order by remd.added";

		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
		    $r['ref_info'] = unserialize($r['ref_info']);
            $reminder_list[] = $r;
		}
		$con->sql_freeresult();

		$smarty->assign('reminder_list', $reminder_list);
	}
	
	function remove_reminder(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;

        // escape integer
        $branch_id = mi($_REQUEST['branch_id']);
        $id = mi($_REQUEST['id']);
        
        if(!$id || !$branch_id) $err[] = $SOP_LANG['SOP_REMINDER_INVALID_ID'];
        
        if($err)	sop_display_error($err);    // found error
        
        $con->sql_query("delete from sop_reminder where branch_id=$branch_id and id=$id and user_id=".mi($sessioninfo['id']));
        $affected = $con->sql_affectedrows();
        
        if(!$affected){
            $err[] = $SOP_LANG['SOP_REMINDER_NOT_DELETED'];
            sop_display_error($err);    // found error
		}
		log_br($sessioninfo['id'], 'SOP', $id, "Reminder Removed: Branch ID#$branch_id , ID#".$id);
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function update_reminder_activation(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;

        $branch_id = mi($_REQUEST['branch_id']);
		$id = mi($_REQUEST['id']);
		$active = mi($_REQUEST['active']);

		// validate
        if(!$branch_id || !$id)    $err[] = $SOP_LANG['SOP_REMINDER_INVALID_ID'];

        if($err){   // found error
			sop_display_error($err);
		}

		$upd['active'] = $active;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update sop_reminder set ".mysql_update_by_field($upd)." where branch_id=$branch_id and id=$id and user_id=".mi($sessioninfo['id']));
		
		$status = $active ? 'Activated' : 'Deactivated';
		log_br($sessioninfo['id'], 'SOP', $id, "Reminder $status: Branch ID#$branch_id , ID#".$id);

	    print "OK";
	}
}

$REMINDER = new REMINDER('Reminder');
?>
