<?php
    define(DATABASE_NAME,'arms_sop');
    
	include_once("../include/common.php");
	include_once("include/sop_maintenance.php");
	include_once("../sop/language.php");

	if(!$config['enable_sop'] || BRANCH_CODE != 'HQ'){
		header("Location: /index.php");
		exit;
	}
	
	// change smarty templates location
	$smarty->template_dir = '../sop/templates';
	$smarty->compile_dir = '../sop/templates_c';
	$smarty->config_dir = '../sop/templates';

    $months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
	$smarty->assign('months',$months);
	
	sop_load_user_privileges();
	
	// user have no SOP privilege
	if(!sop_check_privilege('SOP')){
        header("Location: /index.php");
		exit;
	}
	
	// IS_HQ_USER
	if($sessioninfo['default_branch_id']==1)    define('IS_HQ_USER', 1);
	else   define('IS_HQ_USER', 0);
	$smarty->assign('IS_HQ_USER', IS_HQ_USER);
	
	// YMP_HQ_EDIT
	if(sop_check_privilege('SOP_YMP_EDIT', 1))  define('YMP_HQ_EDIT', 1);
	else    define('YMP_HQ_EDIT', 0);
	$smarty->assign('YMP_HQ_EDIT', YMP_HQ_EDIT);
	
function sop_display_error($err, $no_print = false){
	$str = '';
	if($err){   // found error
		$str .= "<b>Following error has occur</b><ul>";
		foreach($err as $e){
			$str .= "<li>$e</li>";
		}
		$str .= "</ul>";
	}else{
		$str .= "<b> Unhandle Error!</b>";
	}
	
	if($no_print)   return $str;
	else{
		print $str;
		exit;
	}
}

function sop_load_userlist(){
	global $con;

	$con->sql_query("select id, u, active from user order by u");
	$users = array();
	while($r = $con->sql_fetchassoc()){
        $users[$r['id']] = $r;
	}
	$con->sql_freeresult();
	return $users;
}

function sop_load_user_privileges(){
	global $con, $sessioninfo;
	
	// all sop privileges
	$con->sql_query("select * from user_privilege where user_id=".mi($sessioninfo['id'])." and (privilege_code='SOP' or privilege_code like 'sop_%')");
	$all_privileges = array();
	while($r = $con->sql_fetchassoc()){
	    if($r['allowed']){
            $all_privileges[] = $r;
            $sessioninfo['sop_privilege'][$r['privilege_code']][$r['branch_id']] = 1;
		}	
	}
	$con->sql_freeresult();
	
	// yearly marketing plan promotion
	if($all_privileges){
		foreach($all_privileges as $r){
			if($r['privilege_code']=='SOP_YMP' || $r['privilege_code']=='SOP_YMP_EDIT'){
				$sessioninfo['SOP_YMP_ALLOWED_BRANCHES'][$r['branch_id']] = mi($r['branch_id']);
			}
		}
	}
	if(!$sessioninfo['SOP_YMP_ALLOWED_BRANCHES'])   $sessioninfo['SOP_YMP_ALLOWED_BRANCHES'] = array(0);
	
	//print_r($sessioninfo);
}

function sop_check_privilege($privilege_code, $branch_id=0){
	global $sessioninfo;
	if($branch_id){
        return $sessioninfo['sop_privilege'][$privilege_code][$branch_id]== 1 ? true : false;
	}else{
        return $sessioninfo['sop_privilege'][$privilege_code] ? true : false;
	}
}

function sop_send_pm_to_user($bid, $approval_history_id, $tbl, $status, $msg, $link){
	global $con, $sessioninfo, $smarty, $approval_status;

	if(!$tbl || !$msg || !$link ||!$approval_history_id)   return false;
	
	// get the PM list
	$filter = array();
	$filter[] = "id=".mi($approval_history_id);
	if($bid)    $filter[] = "branch_id=".mi($bid);
	$filter = "where ".join(' and ', $filter);
	$con->sql_query("select notify_users from ".DATABASE_NAME.".".$tbl." ".$filter);
	$r = $con->sql_fetchrow();

	$recipients = $r[0];
	$recipients = str_replace("|$sessioninfo[id]|", "|", $recipients);
	$to = preg_split("/\|/", $recipients);

	// send pm
	if($status)
	send_pm($to, $msg, $link);
}
?>
