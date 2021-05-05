<?php
include_once('include/common.php');
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('SOP_YMP_ACTIVITY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SOP_YMP_ACTIVITY', BRANCH_CODE), "index.php");

include_once('yearly_marketing_plan.include.php');

class YEARLY_MARKETING_PLAN_ACTIVITY extends Module{
	var $branches = array();

	function __construct($title){
		global $con, $smarty, $sessioninfo;

		if(!$_REQUEST['skip_init_load'])    $this->init_load(); // initial load
		parent::__construct($title);
	}

	function _default(){
	    global $con, $smarty;

		//$this->load_user_all_data();
		//$this->display();
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
	
	function open_promotion_activity(){
		global $con, $smarty, $sessioninfo, $SOP_LANG;
		
		// escape all params
		$marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
		$marketing_plan_created_branch_id = mi($_REQUEST['marketing_plan_created_branch_id']);
	    $promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
	    $activity_created_branch_id = mi($sessioninfo['branch_id']);
	    $activity_id = mi($_REQUEST['activity_id']);
	    
	    // validate
		if(!$marketing_plan_id)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
		else{
            // check marketing plan header
	    	$marketing_plan = load_marketing_plan_header($marketing_plan_id);   // load header
	    	if(!$marketing_plan)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
	    	else{
                if(!$marketing_plan['active'])  $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INACTIVE'], $marketing_plan_id); // deleted
			}
		}
		
		
        if(!$promotion_plan_id || !$marketing_plan_created_branch_id)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);
        else{
            $promotion_plan = load_promotion_plan_header($marketing_plan_created_branch_id, $promotion_plan_id);   // load promotion header

               // promotion not found
            if(!$promotion_plan)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);
            else{
                // inactive
                if(!$promotion_plan['active'])    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INACTIVE'], $promotion_plan_id);
                //elseif($promotion_plan['deleted'])    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_DELETED'], $promotion_plan_id);    // deleted
			}
            
		}

		if($err){   // found error
			display_redir('index.php', $this->title,  sop_display_error($err, true));
		}
		
		// load data
		$promotion_activity = load_promotion_activity_header($activity_created_branch_id, $activity_id);
		if(!$promotion_activity['active']){
		    // inactive
            $err[] = sprintf($SOP_LANG['SOP_PROMOTION_ACTIVITY_INACTIVE'], $promotion_plan_id);
		}elseif($promotion_activity['deleted']){
			// deleted
			$err[] = sprintf($SOP_LANG['SOP_PROMOTION_ACTIVITY_DELETED'], $promotion_plan_id);
		}
		
		if($err){   // found error
		    display_redir('index.php', $this->title,  sop_display_error($err, true));
			
		}
		
		// check whether use can view this activity
		if($sessioninfo['level']<9999){
		    $can_access = false;
			if($promotion_activity['pic_user_id_list']){    // if activity got assign pic
				foreach($promotion_activity['pic_user_id_list'] as $uid){   // loop all pic id
					if($sessioninfo['id']==$uid){   // found you are the assigned user
                        $can_access = true;
                        break;
					}
				}
			}
			if(!$can_access){   // user cannot access
                display_redir('index.php', $this->title,  sprintf($SOP_LANG['SOP_PROMOTION_ACTIVITY_INVALID_ID'], $activity_id));
			}
		}
		
		$smarty->assign('marketing_plan', $marketing_plan);
		$smarty->assign('promotion_plan', $promotion_plan);
		$smarty->assign('promotion_activity', $promotion_activity);
		$this->display('yearly_marketing_plan.activity.open_promotion_activity.tpl');
	}
	
	/*private function load_user_all_data(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;
	    
	    $filter = array();
	    $filter[] = "mar_p.active = 1 and mar_p.status=1 and mar_p.approved=1";
	    $filter[] = "mar_promo.active=1 and mar_promo.deleted=0 and mar_act.active=1 and mar_act.deleted=0 and mar_usr_act.id>0";
	    
	    if($sessioninfo['level']<=9999){
	        $filter_or = array();
	        $filter_or[] = "mar_p.user_id = ".mi($sessioninfo['id']);
	        $filter_or[] = "(mar_usr_act.user_id=".mi($sessioninfo['id'])." and mar_usr_act.active=1)";
            $filter[] = "(".join(' or ', $filter_or).")";
		}  
	    $filter = "where ".join(' and ', $filter);
	    
	    // load marketing plan
	    $sql = "select mar_act.sop_marketing_plan_id, mar_act.sop_promotion_plan_id, mar_act.id as activity_id , mar_p.title as mar_p_title, mar_promo.title as promo_title,mar_act.title as act_title,mar_act.for_branch_id as act_for_branch_id, mar_act.date_from as act_date_from, mar_act.date_to as act_date_to, mar_act.completed_percent,mar_act.pic_user_id_list
		from sop_marketing_plan mar_p
		left join sop_marketing_plan_promotion mar_promo on mar_promo.sop_marketing_plan_id=mar_p.id
		left join sop_marketing_plan_promotion_activity mar_act on mar_act.sop_marketing_plan_id=mar_promo.sop_marketing_plan_id and mar_act.sop_promotion_plan_id=mar_promo.id
		left join sop_marketing_plan_promotion_user_activity mar_usr_act on mar_usr_act.sop_marketing_plan_id=mar_act.sop_marketing_plan_id and mar_usr_act.sop_promotion_plan_id=mar_act.sop_promotion_plan_id and mar_usr_act.sop_activity_id=mar_act.id
		$filter
		group by sop_marketing_plan_id, sop_promotion_plan_id, activity_id
		order by sop_marketing_plan_id desc, sop_promotion_plan_id desc, activity_id desc";
		//print $sql;
		$q_mar_p = $con->sql_query_false($sql, true);
		while($r = $con->sql_fetchassoc($q_mar_p)){
		    $r['act_for_branch_id'] = unserialize($r['act_for_branch_id']);
		    $r['pic_user_id_list'] = unserialize($r['pic_user_id_list']);
		    
			$form['data'][] = $r;
		}
		$con->sql_freeresult($q_mar_p);
		
		$smarty->assign('users', sop_load_userlist());
		$smarty->assign('form', $form);
	}*/
}

$YEARLY_MARKETING_PLAN_ACTIVITY = new YEARLY_MARKETING_PLAN_ACTIVITY('Activity Management');
?>
