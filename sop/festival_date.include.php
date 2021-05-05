<?php
function load_festival_header($year){
    global $con, $smarty, $sessioninfo, $SOP_LANG;
    
	if(!$year)  return false;
	
	$con->sql_query("select * from ".DATABASE_NAME.".festival_sheet where year=$year");
	$form = $con->sql_fetchassoc();
	$con->sql_freeresult();
	if(!$form)  return false;
	
	$form['label'] = 'draft';
	if($form['status']==1 && !$form['approved'])   $form['label'] = 'waiting_approve';
	elseif($form['status']==1 && $form['approved'])    $form['label'] = 'approved';
	elseif($form['status']==2)  $form['label'] = 'rejected';

	if ($form['approval_history_id']>0){
		$q2 = $con->sql_query("select i.timestamp, i.log, i.status, user.u
from ".DATABASE_NAME.".approval_history_items i
left join ".DATABASE_NAME.".approval_history h on i.approval_history_id = h.id
left join user on i.user_id=user.id
where h.ref_table='festival_sheet' and i.approval_history_id=".mi($form['approval_history_id'])."
order by i.timestamp");
		$form['approval_history_data'] = $con->sql_fetchrowset($q2);
		$con->sql_freeresult($q2);
	}

	return $form;
}

function load_festival_date_list($year){
    global $con, $smarty, $sessioninfo, $SOP_LANG;

	$year = mi($year);
	if(!$year)  return false;

    $filter = array();
	$filter[] = "fd.year=$year";
	$filter = "where ".join(' and ', $filter);

    // load festival date list
    $con->sql_query_false("select fd.*,user.u as created_by
	from ".DATABASE_NAME.".festival_date fd
	left join user on user.id=fd.user_id
	$filter order by fd.date_from", true);
	
	$festival_date_list = array();
    while($r = $con->sql_fetchassoc()){
		$festival_date_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	return $festival_date_list;
}

function load_festival_date($id, $show_row_tpl = false){
    global $con, $smarty, $sessioninfo, $SOP_LANG;
    
    $id = mi($id);
    if(!$id)    return false;
    
    $con->sql_query("select fd.*, user.u as created_by
	from ".DATABASE_NAME.".festival_date fd
    left join user on user.id=fd.user_id
    where fd.id=$id
	");
	$festival_date = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if($show_row_tpl){
	    $year = mi($festival_date['year']);
	    $festival_sheet = load_festival_header($year);
	    
	    $smarty->assign('festival_sheet', $festival_sheet);
        $smarty->assign('festival_date', $festival_date);
        return $smarty->fetch('masterfile_festival_date.open.festival_date_list.row.tpl');
	}else{
        return $festival_date;
	}
}

function send_festival_sheet_approval_pm($form, $status_msg){
	if(!$form)  return false;
	
    sop_send_pm_to_user(0, $form['approval_history_id'], 'approval_history', 1, "Festival Date Approval (Year#$form[year]) $status_msg", "/sop/masterfile_festival_date.php?a=view&year=$form[year]");
}

function festival_sheet_approval($year, $status, $params){
    global $con, $sessioninfo, $smarty, $config;

 	$approved=0;
 	$form = load_festival_header($year); // load form data
	if(!$form)  return false;

	if($form['status'] != 1)    return false;

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
    	$con->sql_query("update ".DATABASE_NAME.".approval_history set status=$status where id=$approval_history_id");
  	}

	// add approval items row
	$con->sql_query("insert into ".DATABASE_NAME.".approval_history_items (approval_history_id, user_id, status, log)
	values ($approval_history_id, $sessioninfo[id], $status, $comment)");

	// update marketing plan
	$con->sql_query("update ".DATABASE_NAME.".festival_sheet set status=".mi($status).",approved=".mi($approved)." where year=$year");

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
    send_festival_sheet_approval_pm($form, $status_msg);
    
    // record log
	log_br($sessioninfo['id'], 'SOP MST FESTIVAL', $year, "Festival Sheet $status_msg by $sessioninfo[u] (Year#$year)");
	return true;    // return success
}

function open_festival_sheet($year){
    global $con, $smarty, $sessioninfo, $SOP_LANG;

    $year = mi($_REQUEST['year']);
    if(!$year){
		header("Location: $_SERVER[PHP_SELF]");
		return;
	}

	$form = load_festival_header($year);   // load header
	if(!$form){
        // not found
        display_redir($_SERVER['PHP_SELF'], $this->title, sprintf($SOP_LANG['SOP_FESTIVAL_YEAR_NOT_FOUND'], $year));
	}

	// load festival date list
	$form['festival_date_list'] = load_festival_date_list($year);

	$smarty->assign('form', $form);
	$smarty->display("masterfile_festival_date.open.tpl");
}
?>
