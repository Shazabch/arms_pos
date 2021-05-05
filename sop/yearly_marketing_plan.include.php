<?php
$SOP_SETTINGS->check_version(1);

include_once('festival_date.include.php');

function load_marketing_plan_header($id){
	global $con;
	
	$id = mi($id);  // escape integer
	
    $con->sql_query_false("select * from ".DATABASE_NAME.".marketing_plan where id=$id", true);
	$form = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$form)  return false;
	
	$form['label'] = 'draft';
	if(!$form['active'])    $form['label'] = 'deleted';
	elseif($form['status']==1 && !$form['approved'])   $form['label'] = 'waiting_approve';
	elseif($form['status']==1 && $form['approved'])    $form['label'] = 'approved';
	elseif($form['status']==2)  $form['label'] = 'rejected';
	elseif($form['status']==5)  $form['label'] = 'terminated';
	
	if ($form['approval_history_id']>0){
		$q2 = $con->sql_query("select i.timestamp, i.log, i.status, user.u
from ".DATABASE_NAME.".approval_history_items i
left join ".DATABASE_NAME.".approval_history h on i.approval_history_id = h.id
left join user on i.user_id=user.id
where h.ref_table='sop_marketing_plan' and i.approval_history_id=".mi($form['approval_history_id'])."
order by i.timestamp");
		$form['approval_history_data'] = $con->sql_fetchrowset($q2);
		$con->sql_freeresult($q2);
	}
	
	return $form;
}

function load_promotion_plan_list($marketing_plan_id){
    global $con, $smarty, $sessioninfo;

    // escape integer
    $marketing_plan_id = mi($marketing_plan_id);
    if(!$marketing_plan_id) return false;   // nothing to load

	$filter = array();
	$filter[] = "sop_promo.marketing_plan_id=$marketing_plan_id";
	/*if(!YMP_HQ_EDIT){   // not HQ user
	    $filter_or = array();
		foreach($sessioninfo['SOP_YMP_ALLOWED_BRANCHES'] as $bid){
            $filter_or[] = "sop_promo.for_branch_id_list like '%:".mi($bid).";%'";
		}
		$filter[] = "(".join(' or ', $filter_or).")";
		unset($filter_or);
	}*/
	$filter = "where ".join(' and ', $filter);

    // load promotion list
    $qm = $con->sql_query_false("select sop_promo.*,user.u as created_by
	from ".DATABASE_NAME.".marketing_plan_promotion sop_promo
	left join user on user.id=sop_promo.created_by_user_id
	$filter order by sop_promo.added", true);

    $promotion_plan_list = array();
    while($r = $con->sql_fetchassoc($qm)){
        $r['for_branch_id_list'] = unserialize($r['for_branch_id_list']);
        $r['branch_own_info'] = unserialize($r['branch_own_info']);
        $key = $r['id'];

		$promotion_plan_list[$key] = $r;
	}
	$con->sql_freeresult($qm);
	return $promotion_plan_list;
}

function load_promotion_plan_header($promotion_plan_id, $show_row_tpl = false){
	global $con, $smarty;
	
	// escape integer
	$promotion_plan_id = mi($promotion_plan_id);

	// load promotion
	$con->sql_query("select sop_promo.*,user.u as created_by
	from ".DATABASE_NAME.".marketing_plan_promotion sop_promo
	left join user on user.id=sop_promo.created_by_user_id
	where sop_promo.id=$promotion_plan_id");
	$promotion_plan = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$promotion_plan)    return false;   // promotion not found, return
	
	$promotion_plan['for_branch_id_list'] = unserialize($promotion_plan['for_branch_id_list']);
	$promotion_plan['branch_own_info'] = unserialize($promotion_plan['branch_own_info']);
	
	$marketing_plan_id = mi($promotion_plan['marketing_plan_id']);
	$marketing_plan = load_marketing_plan_header($marketing_plan_id);

	if($show_row_tpl){  // if need to show templates
	    $smarty->assign('marketing_plan', $marketing_plan);
	    $smarty->assign('promotion_plan', $promotion_plan);
        return $smarty->fetch('yearly_marketing_plan.marketing_plan_details.promotion_plan_list.row.tpl');
	}
	
	return $promotion_plan;
}


function load_promotion_plan_own_data_header($promotion_plan_id, $branch_id, $show_row_tpl = false){
    global $con, $smarty, $sessioninfo;
    
    // escape integer
	$promotion_plan_id = mi($promotion_plan_id);
    $branch_id = mi($branch_id);

	// load promotion
	$promotion_plan = load_promotion_plan_header($promotion_plan_id);
    if(!$promotion_plan)    return false;   // promotion not found, return
    
    $promotion_plan_b = $promotion_plan['branch_own_info'][$branch_id];
	if(!$promotion_plan_b)    return false;   // branch own data for this promotion not found, return

	if($show_row_tpl){  // if need to show templates
	    // load marketing plan
	    $marketing_plan_id = mi($promotion_plan['marketing_plan_id']);
		$marketing_plan = load_marketing_plan_header($marketing_plan_id);

        $smarty->assign('marketing_plan', $marketing_plan);
    	$smarty->assign('promotion_plan', $promotion_plan);
	    $smarty->assign('promotion_plan_b', $promotion_plan_b);
        return $smarty->fetch('yearly_marketing_plan.marketing_plan_details.promotion_plan_list.row.branch_data.tpl');
	}

	return $promotion_plan_b;
}

function load_promotion_activity_list($params){
    global $con, $smarty, $sessioninfo;

    // escape integer
    $marketing_plan_id = mi($params['marketing_plan_id']);
    $promotion_plan_id = mi($params['promotion_plan_id']);
    $branch_id = mi($params['branch_id']);
    $root_id = mi($params['root_id']);

    if(!$promotion_plan_id || !$branch_id)	return false;   // nothing to load

    $filter = array();
    //$filter[] = "sop_act.sop_marketing_plan_created_branch_id=$marketing_plan_created_branch_id";
    $filter[] = "sop_act.promotion_plan_id=$promotion_plan_id";
    $filter[] = "sop_act.branch_id=$branch_id";
    $filter[] = "sop_act.root_id=$root_id";

    $filter = 'where '.join(' and ', $filter);

    $con->sql_query("select sop_act.*
	from ".DATABASE_NAME.".marketing_plan_promotion_activity sop_act
	$filter order by sop_act.level, sop_act.id");

	while($r = $con->sql_fetchassoc()){
		$key = $r['branch_id'].'_'.$r['id'];
        $promotion_activity_list[$key] = $r;
	}
	$con->sql_freeresult();

	return $promotion_activity_list;
}

function regenerate_promotion_activity_cache($params = array()){
	global $con;

	$marketing_plan_id = mi($params['marketing_plan_id']);
    $promotion_plan_id = mi($params['promotion_plan_id']);
    $branch_id = mi($params['branch_id']);
    $activity_id = mi($params['activity_id']);
    
	if(!$branch_id) return false;   // must have branch
	
    if($activity_id){   // if got pass data
		$con->sql_query("select * from
		".DATABASE_NAME.".marketing_plan_promotion_activity
		where branch_id=$branch_id and id=$activity_id");
		$promotion_activity = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if(!$promotion_activity)    return false;
	}else{
	    if(!$promotion_plan_id)   die('Insufficient parameters');
	    $activity_filter[] = "mar_act.promotion_plan_id=$promotion_plan_id and mar_act.branch_id=$branch_id";
	}

	// find max level
	$con->sql_query("select max(level) from ".DATABASE_NAME.".marketing_plan_promotion_activity");
	$max_level = mi($con->sql_fetchfield(0));
	$con->sql_freeresult();

	$cache_table = DATABASE_NAME.'.marketing_plan_promotion_activity_cache';

    create_activity_cache_table();  // try to create table first
	// only alter table
	// check table current highest level
	$tbl_max_level = 0;
	$con->sql_query("explain $cache_table");
	while($r = $con->sql_fetchrow()){
		if(preg_match("/^p/", $r[0])){
			if(is_numeric(substr($r[0], 1))){
                $lv = mi(substr($r[0], 1));
				if($lv > $tbl_max_level) $tbl_max_level = $lv;
			}
		}
	}
	$con->sql_freeresult();
		
    if($max_level > $tbl_max_level){  // if current activity level higher than table level
	    for($i =$tbl_max_level+1; $i<=$max_level; $i++){
	        $px = 'p'.$i;
            $alter_str []= "add $px int not null default 0, add index branch_id_n_".$px." (branch_id, $px)";
		}
		if($alter_str){ // alter table to add in extra column
			$con->sql_query("alter table $cache_table ".join(',', $alter_str));
		}
	}
		
	if($promotion_activity){    // only change the row
		create_activity_cache_table_by_row($promotion_activity);    // create cache row
	}else{
	    $activity_filter = "where ".join(' and ', $activity_filter);
        $q1 = $con->sql_query("select mar_act.* from ".DATABASE_NAME.".marketing_plan_promotion_activity mar_act $activity_filter");
	    while($r = $con->sql_fetchassoc($q1)){
            create_activity_cache_table_by_row($r); // create row
		}
		$con->sql_freeresult($q1);
	}
}

function create_activity_cache_table($extra_col='', $extra_index=''){
	global $con;
	$con->sql_query("create table if not exists arms_sop.marketing_plan_promotion_activity_cache(
        branch_id int,
        activity_id int,
		$extra_col
		last_update timestamp default 0,
		$extra_index
	    primary key(branch_id, activity_id)
	)");
}

function create_activity_cache_table_by_row($promotion_activity){
	global $con;
	if(!$promotion_activity)    return false;

	$tree_str = str_replace(")(", ",", $promotion_activity['tree_str']);
    $tree_str = preg_replace("/[()]/", "", $tree_str);
    $lv_arr = split(",", $tree_str);
    $upd = array();
    //$upd['sop_marketing_plan_created_branch_id'] = $promotion_activity['sop_marketing_plan_created_branch_id'];
    //$upd['sop_promotion_plan_id'] = $promotion_activity['sop_promotion_plan_id'];
    $upd['branch_id'] = $promotion_activity['branch_id'];
    $upd['activity_id'] = $promotion_activity['id'];
    $upd['last_update'] = 'CURRENT_TIMESTAMP';

    if($lv_arr){
		foreach($lv_arr as $i=>$r){
			if($i==0) continue;
			$upd['p'.$i] = $lv_arr[$i];
		}
	}
	$upd['p'.$promotion_activity['level']] = $promotion_activity['id'];
	$con->sql_query("replace into ".DATABASE_NAME.".marketing_plan_promotion_activity_cache ".mysql_insert_by_field($upd));
}

function load_promotion_activity_header($branch_id, $activity_id){
	global $con, $smarty;

	// escape integer
	$branch_id = mi($branch_id);
	$activity_id = mi($activity_id);
	
	$con->sql_query("select sop_act.*, user.u as created_by
	from ".DATABASE_NAME.".marketing_plan_promotion_activity sop_act
	left join user on user.id=sop_act.owner_user_id
	where sop_act.branch_id=$branch_id and sop_act.id=$activity_id");
	$promotion_activity = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	$promotion_activity['pic_user_id_list'] = unserialize($promotion_activity['pic_user_id_list']);
	
	if($promotion_activity['pic_user_id_list']){
		foreach($promotion_activity['pic_user_id_list'] as $uid){
            $uid = mi($uid);
            $con->sql_query("select id, u from user where id=$uid"); // get username
            $promotion_activity['pic_user_name_list'][$uid] = $con->sql_fetchassoc();
            $con->sql_freeresult();
		}
	}
	
	return $promotion_activity;

}

function generate_user_activity_table($params){
	global $con, $sessioninfo, $smarty, $approval_status;

	$marketing_plan_id = mi($params['marketing_plan_id']);
	$promotion_plan_id = mi($params['promotion_plan_id']);
	$branch_id = mi($params['branch_id']);
	$activity_id = mi($params['activity_id']);
	$delete_only = mi($params['delete_only']);  // delete mode
	//$include_sub = mi($params['include_sub']);
	
	if(!$marketing_plan_id) die('No marketing plan id!');

	$filter[] = "mar_promo.marketing_plan_id=$marketing_plan_id";
	
	if($promotion_plan_id){
        $filter[] = "mar_act.promotion_plan_id=$promotion_plan_id";
        if($branch_id && $activity_id){
            $filter[] = "mar_act.branch_id=$branch_id";
            $filter[] = "mar_act.id=$activity_id";
            $include_sub = mi($params['include_sub']);
		}  
	}

	$filter = "where ".join(' and ', $filter);
	$sql = "select mar_act.*, mar_promo.active as promo_active, mar_p.active as mar_p_active, mar_p.status as mar_p_status, mar_p.approved as mar_p_approved
	from ".DATABASE_NAME.".marketing_plan_promotion_activity mar_act
	left join ".DATABASE_NAME.".marketing_plan_promotion mar_promo on mar_promo.id=mar_act.promotion_plan_id
	left join ".DATABASE_NAME.".marketing_plan mar_p on mar_p.id=mar_promo.marketing_plan_id
	$filter";
	//print $sql;
	$q1 = $con->sql_query($sql);
	while($r = $con->sql_fetchassoc($q1)){
	    $r['pic_user_id_list'] = unserialize($r['pic_user_id_list']);   // get all pic list
	    $filter2 = '';
	    $pic_list = array();

	    
	    $activity_reference_id = trim($r['reference_id']);
	    
	    $activity_filter = array();
	    $activity_filter[] = "activity_reference_id=".ms($activity_reference_id);
	    $activity_filter = 'where '.join(' and ', $activity_filter);

		if(!$delete_only){
	        if($r['active'] && $r['promo_active'] && $r['mar_p_active'] && $r['mar_p_status'] && $r['mar_p_approved']){ // not deleted activity
	            if($r['pic_user_id_list']){     // if got pic
					foreach($r['pic_user_id_list'] as $uid){    // loop for each pic
					    $uid = mi($uid);
					    if($uid<=0)   continue;
						$usr_act = array();
						$usr_act['user_id'] = $uid;
						$usr_act['activity_reference_id'] = $activity_reference_id;
						$usr_act['added'] = 'CURRENT_TIMESTAMP';
						$usr_act['last_update'] = 'CURRENT_TIMESTAMP';

						// insert entry, if found already exists, turn it active back
						$con->sql_query("insert into ".DATABASE_NAME.".marketing_plan_promotion_user_activity ".mysql_insert_by_field($usr_act)."
		                     on duplicate key update
							 last_update=CURRENT_TIMESTAMP");
	                    $pic_list[] = $uid;
					}
				}
			}
		}
	    

		if($pic_list)   $filter2 = " and user_id not in (".join(',',$pic_list).")";
		$con->sql_query("delete from ".DATABASE_NAME.".marketing_plan_promotion_user_activity $activity_filter $filter2"); // delete all
		
		if($include_sub){   // recursive
		    // load all sub
			$q2 = $con->sql_query("select * from ".DATABASE_NAME.".marketing_plan_promotion_activity where promotion_plan_id=$promotion_plan_id and branch_id=$branch_id and root_id=$activity_id");
			while($r = $con->sql_fetchassoc($q2)){
				$new_params = array();
				$new_params['marketing_plan_id'] = $marketing_plan_id;
				$new_params['promotion_plan_id'] = $promotion_plan_id;
				$new_params['branch_id'] = $branch_id;
				$new_params['activity_id'] = $r['id'];
				$new_params['delete_only'] = $delete_only;
				$new_params['include_sub'] = $include_sub;
				generate_user_activity_table($new_params);
			}
			$con->sql_freeresult($q2);
		}
	}
	$con->sql_freeresult($q1);
}

function marketing_plan_approval($marketing_plan_id, $status, $params = array()){
    global $con, $sessioninfo, $smarty, $config;

 	$approved=0;
 	$form = load_marketing_plan_header($marketing_plan_id); // load marketing plan data
	if(!$form)  return false;

	if(!$form['active'] || $form['status'] != 1)    return false;

	$approval_history_id = mi($form['approval_history_id']);

	if($status==1){ // 1 = approve
		// if action is approve, update approval flow
		$comment="'Approved'";
		$params = array();
		$params['approve'] = 1;
		$params['user_id'] = $sessioninfo['id'];
		$params['id'] = $approval_history_id;
		$params['update_approval_flow'] = true;
		$params['tbl'] = 'approval_history';
		$params['database'] = DATABASE_NAME;
		
    	$is_last = check_is_last_approval_by_id($params, $con);
    	if($is_last)  $approved = 1;    // if it is already last approval, set the sheet become approved
	}
	else{   // other action, maybe reject or terminate
	  	$comment = ms($params['comment']);    // put the comment
    	$con->sql_query("update ".DATABASE_NAME.".approval_history set status=$status	where id=$approval_history_id");
  	}

	// add approval items row
	$con->sql_query("insert into ".DATABASE_NAME.".approval_history_items (approval_history_id, user_id, status, log)
	values ($approval_history_id, $sessioninfo[id], $status, $comment)");

	// update marketing plan
	$con->sql_query("update ".DATABASE_NAME.".marketing_plan set status=".mi($status).",approved=".mi($approved)." where id=$marketing_plan_id");

	if ($approved)
		$status_msg="Fully Approved";
	elseif ($status==1)
		$status_msg="Approved";
	elseif ($status==2)
		$status_msg="Rejected";
	elseif ($status==5)
		$status_msg="Cancelled/Terminated";
	else
	    die("WTF?");

	// send pm to notify users
    send_pm_to_user($marketing_plan_id, $approval_history_id, $status, $status_msg);

    if($approved){
        $usr_act = array();
		$usr_act['marketing_plan_id'] = $marketing_plan_id;
		generate_user_activity_table($usr_act); // regenerate user activity entry
	}
    // record log
	log_br($sessioninfo['id'], 'SOP YMP', $marketing_plan_id, "Yearly Marketing Plan $status_msg by $sessioninfo[u] (ID#$marketing_plan_id)");
	return true;    // return success
}

function send_pm_to_user($marketing_plan_id, $approval_history_id, $status, $status_msg){
	global $con, $sessioninfo, $smarty, $approval_status;

	// get the PM list
	$con->sql_query("select notify_users from ".DATABASE_NAME.".approval_history where id=$approval_history_id");
	$r = $con->sql_fetchrow();

	$recipients = $r[0];
	$recipients = str_replace("|$sessioninfo[id]|", "|", $recipients);
	$to = preg_split("/\|/", $recipients);

	// send pm
	if($status)
	send_pm($to, "Marketing Plan Approval (ID#$marketing_plan_id) $status_msg", "/sop/yearly_marketing_plan.calendar.php?marketing_plan_id=$marketing_plan_id&calendar_only=1&show=1");
}


/*
function send_promotion_activity_notify($params){
	global $con, $smarty, $sessioninfo, $SOP_LANG;

	// escape all params
	$marketing_plan_id = mi($params['marketing_plan_id']);
	$marketing_plan_created_branch_id = mi($params['marketing_plan_created_branch_id']);
    $promotion_plan_id = mi($params['promotion_plan_id']);
    $activity_created_branch_id = mi($params['activity_created_branch_id']);
    $activity_id = mi($params['activity_id']);
    $include_sub_activity = $activity_id ? mi($params['include_sub_activity']) : 0; // only can process if got activity id


    // check marketing plan
    if(!$marketing_plan_id){
		$ret['err'][] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
		return $ret;
	}else{
        $marketing_plan = load_marketing_plan_header($marketing_plan_id);
        if(!$marketing_plan){
            $ret['err'][] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
			return $ret;
		}
	}
	if(!$marketing_plan['active'] || $marketing_plan['status']!=1 || $marketing_plan['approved']!=1){
        $ret['err'][] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_NOT_YET_APPROVE'], $marketing_plan_id);
		return $ret;
	}

	$filter[] = "mar_promo.sop_marketing_plan_id=$marketing_plan_id";
	if($promotion_plan_id && $marketing_plan_created_branch_id){
	    $filter[] = "mar_act.sop_marketing_plan_created_branch_id=$marketing_plan_created_branch_id";
        $filter[] = "mar_act.sop_promotion_plan_id=$promotion_plan_id";
        if($activity_id){
            $filter[] = "mar_act.id=$activity_id";
		}
	}
	$filter[] = "mar_act.created_branch_id=$activity_created_branch_id";

	$filter[] = "mar_p.active=1 and mar_p.status=1 and mar_p.approved=1 and mar_promo.active=1 and mar_act.active=1";
	$filter = "where ".join(' and ', $filter);

	// constuct sql
	$sql = "select mar_act.*, mar_promo.sop_marketing_plan_id
from sop_marketing_plan_promotion_activity mar_act
left join sop_marketing_plan_promotion mar_promo on mar_promo.created_branch_id=mar_act.sop_marketing_plan_created_branch_id and mar_promo.id=mar_act.sop_promotion_plan_id
left join sop_marketing_plan mar_p on mar_p.id=mar_promo.sop_marketing_plan_id
$filter";
	//print $sql;
	$q_act = $con->sql_query($sql);
	while($r = $con->sql_fetchassoc($q_act)){
	    send_promo_act_notify($r);  // call function to send
		if($activity_id){
			$activity = $r;
		}

	}
	$con->sql_freeresult($q_act);

	if($include_sub_activity && $activity){  // send notify to all sub activity
		$pf = 'p'.mi($activity['level']);
		$sql = "select mar_act.*, mar_promo.sop_marketing_plan_id
		from sop_marketing_plan_promotion_activity mar_act
		left join sop_marketing_plan_promotion_activity_cache mar_act_c on mar_act_c.created_branch_id=mar_act.created_branch_id and mar_act_c.sop_activity_id=mar_act.id
		left join sop_marketing_plan_promotion mar_promo on mar_promo.created_branch_id=mar_act.sop_marketing_plan_created_branch_id and mar_promo.id=mar_act.sop_promotion_plan_id
		where mar_promo.sop_marketing_plan_id=$marketing_plan_id and mar_act.sop_marketing_plan_created_branch_id=$marketing_plan_created_branch_id and mar_act.sop_promotion_plan_id=$promotion_plan_id and mar_act.created_branch_id=$activity_created_branch_id and mar_act_c.".$pf."=".mi($activity['id']);
		$q_act = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q_act)){
		    if($r['id']==$activity_id) continue;   // skip own, because already sent
		    send_promo_act_notify($r);
		}
		$con->sql_freeresult($q_act);
	}

	$ret['ok'] = 1;
	return $ret;
}

function send_promo_act_notify($r){
	if(!$r) return false;
    $r['pic_user_id_list'] = unserialize($r['pic_user_id_list']);
    $msg = "Notification: You have involve in the activity (".$r['title']."). references id #".$r['reference_id'];
    $url = "/sop/yearly_marketing_plan.activity.php?a=open_promotion_activity&marketing_plan_id=".mi($r['sop_marketing_plan_id'])."&marketing_plan_created_branch_id=".mi($r['sop_marketing_plan_created_branch_id'])."&promotion_plan_id=".mi($r['sop_promotion_plan_id'])."&activity_id=".mi($r['id'])."&activity_created_branch_id=".mi($r['created_branch_id']);

    if($r['pic_user_id_list']){
		foreach($r['pic_user_id_list'] as $uid){
            send_pm($uid, $msg, $url, -1, true);  // send notification
		}
	}
}
*/

function generate_calendar($marketing_plan_id, $branch_id='', $show_festival = true){
	global $con, $smarty, $SOP_LANG;
	// escape integer
	$marketing_plan_id = mi($marketing_plan_id);
	$branch_id = mi($branch_id);
	
	// checking
	$err = array();
	if(!$marketing_plan_id)  $err[] = "Please select marketing plan.";
	else{
        $marketing_plan = load_marketing_plan_header($marketing_plan_id);   // load header
	    if(!$marketing_plan)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
	}
	if($err){   // got error
		$smarty->assign('err', $err);
		return false;
	}

	$year = mi($marketing_plan['year']);
	$min_date = $marketing_plan['date_from'];
	$max_date = $marketing_plan['date_to'];
	
	$year_from = mi(date("Y", strtotime($min_date)));
	$month_from = mi(date("m", strtotime($min_date)));
	$year_to = mi(date("Y", strtotime($max_date)));
	$month_to = mi(date("m", strtotime($max_date)));
	
	$start_date = $year_from.'-'.$month_from.'-1';
	$end_date = $year_to.'-'.$month_to.'-'.days_of_month($month_to, $year_to);
	
	$from_time = strtotime($start_date);
	$to_time = strtotime($end_date);
	
	// generate date list array
	$sop_calendar = array();
	for($i = $from_time; $i <= $to_time; $i+=86400){
	    $y = mi(date('Y', $i));
	    $m = mi(date('m', $i));
	    $d = mi(date('d', $i));
		$sop_calendar[$y][$m][$d]['date'] = date('Y-m-d', $i);
	}
	
	// select all promotion plan for this year
	$tmp_promotion_plan_list = load_promotion_plan_list($marketing_plan_id);
	
	if($tmp_promotion_plan_list){
		foreach($tmp_promotion_plan_list as $promotion_plan_id=>$r){
		    $skip = false;
		    $calendar_color = $r['calendar_color'];
		    
		    $use_r = $r;
		    if($branch_id){
		        // check this promotion got related to branch or not
				$use_r = get_branch_own_promotion($r, $branch_id);
				if(!$use_r){
                    $skip = true;
				}
			}
			// check need to add into calendar or not
      		if(!$use_r['active']){  // inactive promotion
				$skip = true;
			}

			if($skip){
                unset($promotion_plan_list[$promotion_plan_id]);
				continue;
			}
			
            add_promotion_plan_into_calendar($sop_calendar, $use_r);    // add data to calendar
            $promotion_plan_list[$promotion_plan_id] = $use_r;
            
		}
	}

    if($show_festival){
        $festival_date_list = load_festival_date_list($year);
        if($festival_date_list){
			foreach($festival_date_list as $festival_date){
			    if(!$festival_date['active'])   continue;
				add_festival_date_into_calendar($sop_calendar, $festival_date);
			}
		}
	}

	if($sop_calendar){
		foreach($sop_calendar as $y => $marketing_plan_years){
			foreach($marketing_plan_years as $m => $marketing_plan_months){
			    $max_promo_row_count = 0;
       			$max_festival_row_count = 0;
       
				foreach($marketing_plan_months as $d => $marketing_plan_days){
				    // check got promotion
				    if(count($marketing_plan_days['data'])>$max_promo_row_count)
						$max_promo_row_count = count($marketing_plan_days['data']);
						
				    // check got festival
				    if(count($marketing_plan_days['festival'])>$max_festival_row_count)
						$max_festival_row_count = count($marketing_plan_days['festival']);
				}
				
				$sop_calendar_info[$y][$m]['max_promo_row_count'] = $max_promo_row_count;
				$sop_calendar_info[$y][$m]['max_festival_row_count'] = $max_festival_row_count;
			}
		}
	}
	
	//print_r($sop_calendar);
	$smarty->assign('marketing_plan', $marketing_plan);
	$smarty->assign('sop_calendar', $sop_calendar);
	$smarty->assign('sop_calendar_info', $sop_calendar_info);
	$smarty->assign('promotion_plan_list', $promotion_plan_list);
	$smarty->assign('festival_date_list', $festival_date_list);
}

function add_promotion_plan_into_calendar(&$sop_calendar, $promotion_plan){
	$date_from = $promotion_plan['date_from'];
	$date_to = $promotion_plan['date_to'];
	
	$from_time = strtotime($date_from);
	$to_time = strtotime($date_to);
	
	$last_ym = '';
	
	for($time = $from_time; $time <= $to_time; $time+=86400){
        $y = mi(date('Y', $time));
	    $m = mi(date('m', $time));
	    $d = mi(date('d', $time));
	    
	    $ym = date("Ym", $time);
	    if($last_ym != $ym){ // new monthly
			// check row id
			$use_row_id = 0;
			$i = 0;
			
			if($ym == date("Ym", $from_time)){  // if this is first month, start at from day
				$start_i = mi(date("d", $from_time));
			}else   $start_i = 1;
			
			if($ym == date("Ym", $to_time)){  // if this is last month, max_d set to last day
				$max_d = mi(date("d", $to_time));
			}else   $max_d = days_of_month($m, $y);

			while($i<=$max_d){  // always loop from 1 if cannot find a suitable row
                for($i = $start_i; $i <= $max_d; $i++){    // loop all date in this month
					if(isset($sop_calendar[$y][$m][$i]['data'][$use_row_id])){ // found another marketing plan using this row
                        $use_row_id++;  // increase row id
                        break;
					}
				}
			}
		}
		
		if(isset($sop_calendar[$y][$m][$d]))
			$sop_calendar[$y][$m][$d]['data'][$use_row_id]['promotion_plan_id'] = $promotion_plan['id'];
		
		$last_ym = $ym;
	}
}

function add_festival_date_into_calendar(&$sop_calendar, $festival_date){
    $date_from = $festival_date['date_from'];
	$date_to = $festival_date['date_to'];

	$from_time = strtotime($date_from);
	$to_time = strtotime($date_to);

	$last_ym = '';
	
	for($time = $from_time; $time <= $to_time; $time+=86400){
        $y = mi(date('Y', $time));
	    $m = mi(date('m', $time));
	    $d = mi(date('d', $time));

	    $ym = date("Ym", $time);
	    if($last_ym != $ym){ // new monthly
			// check row id
			$use_row_id = 0;
			$i = 0;

			if($ym == date("Ym", $from_time)){  // if this is first month, start at from day
				$start_i = mi(date("d", $from_time));
			}else   $start_i = 1;

			if($ym == date("Ym", $to_time)){  // if this is last month, max_d set to last day
				$max_d = mi(date("d", $to_time));
			}else   $max_d = days_of_month($m, $y);

			while($i<=$max_d){  // always loop from 1 if cannot find a suitable row
                for($i = $start_i; $i <= $max_d; $i++){    // loop all date in this month
					if(isset($sop_calendar[$y][$m][$i]['festival'][$use_row_id])){ // found another festival using this row
                        $use_row_id++;  // increase row id
                        break;
					}
				}
			}
		}

        if(isset($sop_calendar[$y][$m][$d]))
			$sop_calendar[$y][$m][$d]['festival'][$use_row_id]['festival_date_id'] = $festival_date['id'];

		$last_ym = $ym;
	}
}

function get_branch_own_promotion($r, $bid){
    $bid = mi($bid);
	if(!$r || !$bid)    return false;
	
	// no branch id list
	if(!$r['for_branch_id_list'] || !is_array($r['for_branch_id_list']))    return false;
    else{
        // this branch is not selected for this promotion
        if(!in_array($bid, $r['for_branch_id_list'])) return false;
        else{
            // check branch own
			if(isset($r['branch_own_info'][$bid])){
			    // replace main promotion with branch own data
				$r['date_from'] = $r['branch_own_info'][$bid]['date_from'];
				$r['date_to'] = $r['branch_own_info'][$bid]['date_to'];
				$r['active'] = $r['branch_own_info'][$bid]['active'];
			}
		}
		return $r;
	}
}

?>
