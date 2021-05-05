<?php
/*
6/9/2010 6:27:03 PM Andy
- Ignore those CN/DN with zero amount when export ubs.
- CN/DN invoice no. add /CN or /DN.

6/10/2010 10:27:21 AM Andy
- Enhance invoice generation script.
- Fix Consignment Debit Note wording mistake.

11/9/2010 11:15:41 AM Andy
- Add checking for canceled/deleted and prevent it to be edit.

1/25/2011 11:13:21 AM Andy
- Fix a bugs which cause multiple approval make document stuck.

1/26/2011 10:13:56 AM Andy
- Fix a bugs cause if direct approve will not get invoice no.

7/5/2011 3:57:41 PM Andy
- Change split() to use explode()

12/15/2011 4:11:32 PM Justin
- Added to pick up currency code during print report.

2/17/2012 6:22:54 PM Justin
- Fixed the bug when do reset, system update it as terminated/cancelled.

10/1/2012 3:46 PM Justin
- Bug fixed on take too long to generate invoice no.

7/3/2013 11:32 AM Fithri
- pm notification standardization

8/1/2013 5:39 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.
- Enhance to load more_info when select approval history.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

1/21/2015 5:55 PM Justin
- Enhanced to have GST calculation.

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

10/7/2016 11:46 AM Andy
- Fixed stucked approval redirect to wrong php.
*/

$approval_status = array(1 => "Approved", 2 => "Rejected", 5 => "Cancelled/Terminated");
$maintenance->check(244);

if(defined('DEBIT_NOTE_MODE')){
	define('NOTE_TBL','dn');
	define('NOTE_TBL_ITEMS','dn_items');
	define('NOTE_TBL_TMP_ITEMS','tmp_dn_items');
	define('SHEET_NAME','DEBIT NOTE');
}else{
    define('NOTE_TBL','cn');
	define('NOTE_TBL_ITEMS','cn_items');
	define('NOTE_TBL_TMP_ITEMS','tmp_cn_items');
	define('SHEET_NAME','CREDIT NOTE');
}
$smarty->assign('sheet_type', NOTE_TBL);
$smarty->assign('sheet_name', SHEET_NAME);

function init_selection(){
	global $con, $sessioninfo, $smarty, $config, $gst_list;

	// uom
	$con->sql_query("select * from uom where active=1 order by code");
	while($r = $con->sql_fetchrow()){
		$uom[$r['id']] = $r;
	}
	$smarty->assign('uom', $uom);
	$con->sql_freeresult();

	// branch & branch group
	load_branch_group();
	load_branch();
	
	if(!$gst_list){
		$gst_list = construct_gst_list('supply');
	}
	
	// select gst setting
	$con->sql_query("select * from gst_settings where setting_name = 'export_gst_type'");
	$gst_export_settings = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	foreach($gst_list as $r){
		if($r['id'] == $gst_export_settings['setting_value']){
			$export_settings = $r;
			break;
		}
	}

	$smarty->assign("gst_export_settings", $export_settings);
}

function load_items($branch_id, $id, $load_from_tmp = false){
    global $con,$smarty, $LANG, $sessioninfo, $gst_list;

    $filter = array();
    if($load_from_tmp){
		$tbl = NOTE_TBL_TMP_ITEMS;
		$filter[] = "cni.user_id=$sessioninfo[id]";
	}else   $tbl = NOTE_TBL_ITEMS;

	$filter[] = "cni.branch_id=".mi($branch_id)." and cni.".NOTE_TBL."_id=".mi($id);
	$filter = "where ".join(' and ', $filter);

	$q1 = $con->sql_query("select cni.*,si.sku_item_code, ifnull(si.artno,si.mcode) as artno_mcode, si.description as sku_description, si.additional_description,uom.code as uom_code,uom.fraction as uom_fraction,tdt.code as trade_discount_code
from $tbl cni
left join sku_items si on si.id=cni.sku_item_id
left join uom on uom.id=cni.uom_id
left join trade_discount_type tdt on cni.price_type_id=tdt.id
$filter order by cni.id");

	while($r = $con->sql_fetchassoc($q1)){
		// pre load gst id, code and rate
		if($_REQUEST['is_under_gst'] && !$r['gst_id']){
			$r['gst_id'] = $gst_list[0]['id'];
			$r['gst_code'] = $gst_list[0]['code'];
			$r['gst_rate'] = $gst_list[0]['rate'];
		}
		
		if($r['discount_per'])	$r['disc_arr'] = explode("+", $r['discount_per']);
		
		// gst
		if($r['gst_id'] > 0){
			check_and_extend_gst_list($r);
		}

		$items[] = $r;
	}

	return $items;
}

function load_header($branch_id, $id, $redirect_if_not_found = false){
	global $con,$smarty, $LANG, $sessioninfo, $config;
	
	$con->sql_query("select cn.*,user.u as username
	from ".NOTE_TBL." cn
	left join user on user.id=cn.user_id
	where cn.branch_id=".mi($branch_id)." and cn.id=".mi($id));
	$form = $con->sql_fetchrow();
	if(!$form){
		if($redirect_if_not_found)  js_redirect($LANG['INVALID_'.NOTE_TBL.'_ID'], "$_SERVER[PHP_SELF]");
		else return false;
	}
	if($form['discount'])	$form['disc_arr'] = explode("+", $form['discount']);

	$con->sql_freeresult();

	if ($form['approval_history_id']>0){
		$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u, i.more_info
from branch_approval_history_items i
left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
left join user on i.user_id=user.id
where h.ref_table='".NOTE_TBL."' and i.branch_id=$branch_id and i.approval_history_id=$form[approval_history_id]
order by i.timestamp");
		$approval_history = array();
		while($r = $con->sql_fetchassoc($q2)){
			$r['more_info'] = unserialize($r['more_info']);
			$approval_history[] = $r;
		}
		$con->sql_freeresult($q2);
		$smarty->assign("approval_history", $approval_history);
	}

	$params = array();
	$params['user_id'] = $sessioninfo['id'];
	$params['id'] = $form['approval_history_id'];
	$params['branch_id'] = $branch_id;
	$params['check_is_approval'] = true;
	$is_approval = check_is_last_approval_by_id($params, $con);
	if($is_approval)  $form['is_approval'] = 1;

	/*if($form['active'] == 1 && ($form['status']==0 || $form['status'] ==2) && !$form['approved']){
		// check whether this do is under gst
		if($config['enable_gst']){
			$params = array();
			$params['date'] = $form['date'];
			$params['branch_id'] = $branch_id;
			$form['is_under_gst'] = check_gst_status($params);
			
			if($form['is_under_gst']){
				construct_gst_list();
			}
		}else{
			$form['is_under_gst'] = 0;
		}
	}*/
	
	return $form;
}

function cn_approval($branch_id, $cn_id, $status, $auto_approve=false, $redirect=true){
    global $con, $sessioninfo, $smarty, $config, $approval_status;

 	$form=$_REQUEST;
 	$approved=0;
 	if($auto_approve) $form=load_header($branch_id, $cn_id);
	else	check_must_can_edit($branch_id, $cn_id, true);
    
	$aid=$form['approval_history_id'];
	$approvals=$form['approvals'];

	if ($_REQUEST['on_behalf_of'] && $_REQUEST['on_behalf_by']) {
		$con->sql_query("select group_concat(u separator ', ') as u from user where id in (".str_replace('-',',',$_REQUEST['on_behalf_of']).")");
		$on_behalf_of_u = $con->sql_fetchfield(0);
		$con->sql_query("select u from user where id = ".mi($_REQUEST['on_behalf_by'])." limit 1");
		$on_behalf_by_u = $con->sql_fetchfield(0);
		$approval_on_behalf = array(
			'on_behalf_of' => str_replace('-',',',$_REQUEST['on_behalf_of']),
			'on_behalf_by' => mi($_REQUEST['on_behalf_by']),
			'on_behalf_of_u' => $on_behalf_of_u,
			'on_behalf_by_u' => $on_behalf_by_u,
		);
	}
	else {
		$approval_on_behalf = false;
	}
	
	if($status==1){
		$comment="Approved";
		$params = array();
		$params['approve'] = 1;
		$params['user_id'] = $sessioninfo['id'];
		$params['id'] = $aid;
		$params['branch_id'] = $branch_id;
		$params['update_approval_flow'] = true;
		if($auto_approve) $params['auto_approve'] = true;
    	$is_last = check_is_last_approval_by_id($params, $con);
    	if($is_last)  $approved = 1;
	}
	else{
	  	$comment= trim($form['comment']);
    	$con->sql_query("update branch_approval_history set status=$status, approvals = ".ms($approvals)." where id = $aid and branch_id = $branch_id") or die(mysql_error());
  	}

	$upd = array();
	$upd['approval_history_id'] = $aid;
	$upd['branch_id'] = $branch_id;
	$upd['user_id'] = $sessioninfo['id'];
	$upd['status'] = $status;
	
	if ($approval_on_behalf) $comment .= " (by ".$approval_on_behalf['on_behalf_by_u']." on behalf of ".$approval_on_behalf['on_behalf_of_u'].")";
	
	$upd['log'] = $comment;
	
	if($_REQUEST['direct_approve_due_to_less_then_min_doc_amt'])	$upd['more_info']['direct_approve_due_to_less_then_min_doc_amt'] = 1;
	if($upd['more_info'])	$upd['more_info'] = serialize($upd['more_info']);
	
	$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd));

	$upd = array();
	$upd['status'] = mi($status);
	$upd['approved'] = mi($approved);
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	if(!$form['inv_no']&&$approved) $upd['inv_no'] = assign_inv_no($branch_id, $cn_id);
	$con->sql_query("update ".NOTE_TBL." set ".mysql_update_by_field($upd)." where id=$cn_id and branch_id=$branch_id");
	//send_pm_to_user($cn_id, $branch_id, $aid, $status);
	$status_str = ($is_last || $status != 1) ? $approval_status[$status] : '';
	if(NOTE_TBL=='cn'){
		$to = get_pm_recipient_list2($cn_id,$aid,$status,'approval',$branch_id,'cn');
        send_pm2($to, SHEET_NAME." Approval (ID#$cn_id) $status_str", "consignment.credit_note.php?a=view&id=$cn_id&branch_id=$branch_id", array('module_name'=>'cn'));
	}else{
		$to = get_pm_recipient_list2($cn_id,$aid,$status,'approval',$branch_id,'dn');
        send_pm2($to, SHEET_NAME." Approval (ID#$cn_id) $status_str", "consignment.debit_note.php?a=view&id=$cn_id&branch_id=$branch_id", array('module_name'=>'dn'));
	}

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

	if($approved)   update_sku_items_cost($branch_id, $cn_id);  // update cost changed=1

	log_br($sessioninfo['id'], SHEET_NAME, $cn_id, SHEET_NAME." $status_msg by $sessioninfo[u] (ID#$cn_id)");
	if($redirect){
	
		if ($approval_on_behalf) {
			header("Location: /stucked_document_approvals.php?m=".NOTE_TBL);
			exit;
		}
	
	    if(NOTE_TBL=='cn'){
            if($auto_approve)
				header("Location: /consignment.credit_note.php?t=approve&save_id=$cn_id");
			else{
			    header("Location: /consignment.credit_note.approval.php?t=$form[a]&id=$cn_id");
			}
		}else{
            if($auto_approve)
				header("Location: /consignment.debit_note.php?t=approve&save_id=$cn_id");
			else{
			    header("Location: /consignment.debit_note.approval.php?t=$form[a]&id=$cn_id");
			}
		}
        
		exit;
	}
}

/*
function send_pm_to_user($cn_id, $branch_id, $aid, $status){
	global $con, $sessioninfo, $smarty, $approval_status;
	// get the PM list
	$con->sql_query("select notify_users
from branch_approval_history where id=$aid and branch_id = $branch_id");
	$r = $con->sql_fetchrow();

	$recipients = $r[0];
	$recipients = str_replace("|$sessioninfo[id]|", "|", $recipients);
	$to = preg_split("/\|/", $recipients);

	// send pm
	if(NOTE_TBL=='cn'){
        send_pm($to, SHEET_NAME." Approval (ID#$cn_id) $approval_status[$status]", "consignment.credit_note.php?a=view&id=$cn_id&branch_id=$branch_id");
	}else{
        send_pm($to, SHEET_NAME." Approval (ID#$cn_id) $approval_status[$status]", "consignment.debit_note.php?a=view&id=$cn_id&branch_id=$branch_id");
	}
	
}
*/

function assign_inv_no($branch_id, $cn_id){
	global $con;

	$con->sql_query("select report_prefix, ip from branch where id=$branch_id");
	$report_prefix = $con->sql_fetchrow();

    // check whether already have do_no
	$con->sql_query("select inv_no from ".NOTE_TBL." where branch_id=$branch_id and id=$cn_id and inv_no like ".ms($report_prefix[0].'%'));
	$temp = $con->sql_fetchrow();
	if($temp)   return $temp['inv_no'];

	$con->sql_query("select max(inv_no) as mx from ".NOTE_TBL." where branch_id = $branch_id and inv_no like '$report_prefix[0]%'");
	$r = $con->sql_fetchrow();

	if (!$r)
		$n = 1;
	else
		$n = preg_replace("/^".$report_prefix[0]."/","", $r[0])+1;

	$inv_no = $report_prefix[0] . sprintf("%05d", $n)."/".strtoupper(NOTE_TBL);
	while(!$con->sql_query("update ".NOTE_TBL." set inv_no='$inv_no', approved=1 where id=$cn_id and branch_id = $branch_id",false,false)){
		$n++;
		$inv_no = $report_prefix[0] . sprintf("%05d", $n)."/".strtoupper(NOTE_TBL);
	}
	return $inv_no;
}

function update_sku_items_cost($branch_id, $cn_id){
	global $con;
	
	// get cn branch id
	$con->sql_query("select to_branch_id from ".NOTE_TBL." where branch_id=$branch_id and id=$cn_id");
	$cn_branch_id = $con->sql_fetchfield(0);
	
	// get sku item id list
	$q1 = $con->sql_query("select distinct(sku_item_id) from ".NOTE_TBL_ITEMS." where branch_id=$branch_id and ".NOTE_TBL."_id=$cn_id");
	while($r = $con->sql_fetchrow($q1)){
        $sid_arr[] = $r[0];
        if(count($sid_arr)>=1000){
            //update sku_items_cost
			$con->sql_query("update sku_items_cost set changed=1 where branch_id=$cn_branch_id and sku_item_id in (".join(',', $sid_arr).")");
            $sid_arr = array();
		}
	}
	if($sid_arr){
        $con->sql_query("update sku_items_cost set changed=1 where branch_id=$cn_branch_id and sku_item_id in (".join(',', $sid_arr).")");
	}
    
}

function reset_cn($branch_id, $cn_id){
    global $con,$sessioninfo,$config, $LANG;
	$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;

	if($sessioninfo['level']<$required_level){
        js_redirect(sprintf('Forbidden', SHEET_NAME, BRANCH_CODE), $_SERVER['PHP_SELF']);
	}

	$form = load_header($branch_id, $cn_id);
	if(!$form['approved']){
		header("Location: $_SERVER[PHP_SELF]?save_id=$cn_id&err_msg=".SHEET_NAME." Already Reset");
		exit;
	}

	$aid=$form['approval_history_id'];
	$approvals=$form['approvals'];
	$status = 0;

	$upd = array();
	$upd['approval_history_id'] = $aid;
	$upd['branch_id'] = $branch_id;
	$upd['user_id'] = $sessioninfo['id'];
	$upd['status'] = $status;
	$upd['log'] = $_REQUEST['reason'];

	$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
	$con->sql_query("update branch_approval_history set status=$status,approved_by='' where id = $aid and branch_id = $branch_id") or die(mysql_error());

	$status = 0;
	$upd = array();
	$upd['status'] = $status;
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	$upd['approved'] = 0;
	$upd['active'] = 1;
	$con->sql_query("update ".NOTE_TBL." set ".mysql_update_by_field($upd)." where id=$cn_id and branch_id=$branch_id");

    update_sku_items_cost($branch_id, $cn_id);  // update cost changed=1
	log_br($sessioninfo['id'], SHEET_NAME, $cn_id, sprintf(SHEET_NAME." Reset ($form[inv_no])",$cn_id));

	header("Location: $_SERVER[PHP_SELF]?t=reset&save_id=$cn_id");
}

function print_cn($branch_id, $id){
    global $con, $sessioninfo, $smarty, $config, $LANG;

    $form = load_header($branch_id, $id, true);
    $items = load_items($branch_id, $id);

	$con->sql_query("select * from branch where id =".mi($branch_id));
	$smarty->assign("from_branch", $con->sql_fetchrow());
	
	$con->sql_query("select * from branch where id =".mi($form['to_branch_id']));
	$to_branch = $con->sql_fetchrow();
	
	if($config['consignment_modules'] && $config['masterfile_branch_region'] && $to_branch['region']){
		$to_branch['currency_code'] = strtoupper($config['masterfile_branch_region'][$to_branch['region']]['currency']);
	}
	if(!$to_branch['currency_code']) $to_branch['currency_code'] = "RM";
	
	$smarty->assign("to_branch", $to_branch);

    $item_per_page = $config['cn_print_item_per_page']>5 ? $config['cn_print_item_per_page'] : 25;
    $item_per_lastpage = $config['cn_print_item_per_last_page']>0 ? $config['cn_print_item_per_last_page'] : $item_per_page-5;

    $totalpage = 1 + ceil((count($items)-$item_per_lastpage)/$item_per_page);
	
	
	$item_index = -1;
	$item_no = -1;
	$page = 1;
	
	$page_item_list = array();
	$page_item_info = array();
	
	foreach($items as $r){	// loop for each item
		if($item_index+1>=$item_per_page){
			$page++;
			$item_index = -1;
		}
		
		$item_no++;
		$item_index++;
		$r['item_no'] = $item_no;
		
		$page_item_list[$page][$item_index] = $r;	// add item to this page
		
		if($config['sku_enable_additional_description'] && $r['additional_description']){
			$r['additional_description'] = unserialize($r['additional_description']);
			foreach($r['additional_description'] as $desc){
				if($item_index+1>=$item_per_page){
					$page++;
					$item_index = -1;
				}
		
				$item_index++;
				$desc_row = array();
				$desc_row['sku_description'] = $desc;
				$page_item_list[$page][$item_index] = $desc_row;
				$page_item_info[$page][$item_index]['not_item'] = 1;
			}
		}
	}

	// fix last page
	if(count($page_item_list[$page]) > $item_per_lastpage){
		$page++;
		$page_item_list[$page] = array();
	}
	
	$totalpage = count($page_item_list);

    $smarty->assign('form', $form);
	
	foreach($page_item_list as $page => $item_list){
		$this_page_num = ($page < $totalpage) ? $item_per_page : $item_per_lastpage;
		$smarty->assign("PAGE_SIZE", $this_page_num);
		$smarty->assign("is_lastpage", ($page >= $totalpage));
		$smarty->assign("page", "Page $page of $totalpage");
		$smarty->assign("start_counter",$item_list[0]['item_no']);
		$smarty->assign("items", $item_list);
		$smarty->assign("page_item_info", $page_item_info[$page]);
		if($config['cn_alt_print_template'])    $smarty->display($config['cn_alt_print_template']);
        else	$smarty->display("consignment.credit_note.print.tpl");
		$smarty->assign("skip_header",1);
	}
	
	/*
    for ($i=0,$page=1;$page<=$totalpage;$i+=$item_per_page,$page++){
        $smarty->assign("PAGE_SIZE", ($page < $totalpage) ? $item_per_page : $item_per_lastpage);
		$smarty->assign("is_lastpage", ($page >= $totalpage));
        $smarty->assign("page", "Page $page of $totalpage");
        $smarty->assign("start_counter", $i);
        $smarty->assign("items", array_slice($items,$i,$item_per_page));

		if($config['cn_alt_print_template'])    $smarty->display($config['cn_alt_print_template']);
        else	$smarty->display("consignment.credit_note.print.tpl");
		$smarty->assign("skip_header",1);
    }
	*/
}

function check_must_can_edit($branch_id, $cn_id, $is_approval_screen = false){
	global $con, $LANG;

    $con->sql_query("select active, status, approved from ".NOTE_TBL." where branch_id=".mi($branch_id)." and id=".mi($cn_id));

	if($r = $con->sql_fetchrow()){  // invoice exists
		if(!$r['active']){  // inactive
            display_redir($_SERVER['PHP_SELF'], SHEET_NAME, sprintf($LANG['CN_DN_INACTIVE'], SHEET_NAME, $cn_id));
		}elseif ($r['status']==4 || $r['status']==5){    // canceled or deleted
		    display_redir($_SERVER['PHP_SELF'], SHEET_NAME, sprintf($LANG['CN_DN_ALREADY_CANCELED_OR_DELETED'], SHEET_NAME, $cn_id));
		}else{
		    if($is_approval_screen){
				if($r['approved']){
                    display_redir($_SERVER['PHP_SELF'], SHEET_NAME, sprintf($LANG['CN_DN_ALREADY_CONFIRM_OR_APPROVED'], SHEET_NAME, $cn_id));
				}
			}elseif(($r['status']>0 && $r['status'] !=2) || $r['approved']){    // confimred or approved
			    display_redir($_SERVER['PHP_SELF'], SHEET_NAME, sprintf($LANG['CN_DN_ALREADY_CONFIRM_OR_APPROVED'], SHEET_NAME, $cn_id));
			}
		}
	}else{
        display_redir($_SERVER['PHP_SELF'], SHEET_NAME, sprintf($LANG['CN_DN_NOT_FOUND'], SHEET_NAME, $cn_id)); // not found
	}
	$con->sql_freeresult();
}
?>
