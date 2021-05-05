<?php
/*
5/16/2011 3:29:46 PM Alex
- created by me

6/15/2012 04:01:00 PM Andy
- Add new function activate_voucher($voucher_batch_arr = array(), $code_to_activate = array(), $params = array())

2/28/2013 5:53 PM Justin
- Enhanced not to filter session branch while allowed to activate across branch.

6/26/2019 5:38 PM Andy
- Enhanced to can link voucher to member.

10/16/2019 11:25 AM Andy
- Enhanced to pass branch_id when send push notification to member.

7/17/2020 1:11 PM Andy
- Rename the default printing format name for Membership Auto Redemption Voucher.
*/

$maintenance->check(130);

	if (!$config['voucher_value_prefix'])			$config['voucher_value_prefix']= array(10,20,30,40,50);
	if (!$config['voucher_active_remark_prefix'])	$config['voucher_active_remark_prefix'] = array('Purchase','Promotion','Redemption');
	if (!$config['voucher_generate_limit']) 		$config['voucher_generate_limit'] = 500;

// default format for membership auto redemption format, put at first
$tmp_voucher_member_redeem_print_template  = array('default:3x2' => array (
		'description' => 'Membership Auto Redemption Default (A4 paper: 3 x 2)',
		'row' => 3,
		'column' => 2,
		'address' => 'masterfile_voucher.auto_redemption.print.vc3x2.tpl'
	));
if(!is_array($config['voucher_member_redeem_print_template']))	$config['voucher_member_redeem_print_template'] = array();
$config['voucher_member_redeem_print_template'] = array_merge($tmp_voucher_member_redeem_print_template, $config['voucher_member_redeem_print_template']);

	$smarty->assign("config",$config);
	
function activate_voucher($voucher_batch_arr = array(), $code_to_activate = array(), $params = array()){
	global $con, $sessioninfo, $config, $appCore;
	
	if(!is_array($voucher_batch_arr) || !is_array($params)){
		die('Invalid Parameters');
	}
	
	if(count($voucher_batch_arr)<=0 || !$params){
		die('Activation Error');
	}
	
	$all_voucher_in_batch = $params['all_voucher_in_batch'] ? true : false;
	if(!$all_voucher_in_batch && (!is_array($code_to_activate) || !$code_to_activate)){
		die('Must give voucher to activate');
	}
	
	$time_stamp = $params['timestamp'] ? $params['timestamp'] : 'CURRENT_TIMESTAMP';
	$active_remark = trim($params['active_remark']);
	$valid_from = $params['valid_from'];
	$valid_to = $params['valid_to'];
	$interbranch = $params['interbranch'];
	$disallow_disc_promo = mi($params['disallow_disc_promo']);
	$disallow_other_voucher = mi($params['disallow_other_voucher']);
	$is_print_only = isset($params['is_print_only']) ? mi($params['is_print_only']) : 1;
	if($params['member_card_no'])	$member_card_no = trim($params['member_card_no']);
	
	$upd = array();
	$upd['last_update'] = $time_stamp;
	
	$str_batch = join(',', $voucher_batch_arr);
	if($code_to_activate)	$str_voucher = join(',', $code_to_activate);
	
	$ext_filter = "";
	if(!$config['voucher_allow_cross_branch_activate']) $ext_filter = " and branch_id=".mi($sessioninfo['branch_id']);
	
	// update batch		
	$con->sql_query("update mst_voucher_batch set ".mysql_update_by_field($upd)." where batch_no in (".$str_batch.")".$ext_filter);

	// voucher
	$upd['active'] = 1;
	$upd['active_remark'] = $active_remark;
	$upd['activated'] = $time_stamp;
	$upd['valid_from'] = $valid_from;
	$upd['valid_to'] = $valid_to;			
	$upd['allow_interbranch'] = serialize($interbranch);
	$upd['active_user_id'] = $sessioninfo['id'];
	$upd['disallow_disc_promo'] = $disallow_disc_promo;
	$upd['disallow_other_voucher'] = $disallow_other_voucher;
	if($member_card_no)	$upd['member_card_no'] = $member_card_no;
	
	$filter = array();
	$filter[] = "batch_no in (".$str_batch.")";
	if(!$config['voucher_allow_cross_branch_activate']) $filter[] = "branch_id=".$sessioninfo['branch_id'];
	
	if($str_voucher){
		$filter[] = "code in (".$str_voucher.")";	// filter by given voucher code
	}else{
		if($is_print_only)	$filter[] = "is_print>0";
		$filter[] = "cancel_status=0 and active=0";	// filter by all code in this batch (only those printed)
	}
	
	$filter = "where ".join(' and ', $filter);
	$con->sql_query("update mst_voucher set ".mysql_update_by_field($upd)." $filter");
	
	if($all_voucher_in_batch){
		log_br($sessioninfo['id'], 'VOUCHER',$sessioninfo['branch_id'], "Activate voucher batch (".$str_batch.") , From Branch:".BRANCH_CODE.", Allow branches: ".join(", ", $interbranch));
	}else{
		log_br($sessioninfo['id'], 'VOUCHER',$sessioninfo['branch_id'], "Activate voucher codes (".$str_voucher.") , From Branch:".BRANCH_CODE.", Allow branches: ".join(", ", $interbranch));
	}
	
	// Send Push Notification
	if($member_card_no && $config['enable_push_notification']){
		$title = "Voucher Activated";
		$message = "You have received Voucher(s).";
		$params = array();
		$params['branch_id'] = $sessioninfo['branch_id'];
		$appCore->memberManager->sendPushNotificationToMember($member_card_no, $title, $message, $params);
	}
}
?>
