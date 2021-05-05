<?php

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP_PACK_REDEEM')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_PACK_REDEEM', BRANCH_CODE), "/index.php");
$maintenance->check(410);

class MEMBER_PACK_DETAILS extends Module{
	var $package_tab_list = array(
		'available' => 'Available',
		'all_used' => 'Fully Used',
		'cancelled' => 'Cancelled'
	);
	
	function __construct($title)
	{
		// load all initial data
		//$this->init_load();
		
		parent::__construct($title);
	}
	
	function _default()
	{
		global $smarty, $sessioninfo, $config, $LANG;

		// Get NRIC
		$nric = trim($_REQUEST['nric']);
		if(!$nric)	display_redir("/index.php", $this->title, $LANG['MEMBERSHIP_NRIC_NOT_IN_DATABASE']);
		
		// Load Data
		$this->load_member_details($nric);
		
		$this->display();
	}
	
	private function load_member_details($nric){
		global $con, $smarty, $appCore;
		
		$smarty->assign('package_tab_list', $this->package_tab_list);
		
		$nric = trim($_REQUEST['nric']);
		if(!$nric)	display_redir("/index.php", $this->title, $LANG['MEMBERSHIP_NRIC_NOT_IN_DATABASE']);
		
		// get member by NRIC only
		$member = $appCore->memberManager->getMember($nric, false);
		if(!member)	display_redir("/index.php", $this->title, $LANG['MEMBERSHIP_NRIC_NOT_IN_DATABASE']);
		$smarty->assign('member', $member);

		// Get Card No List
		$card_no_list = $appCore->memberManager->getMemberCardNoList($nric);
		$redeem_his_list = $log_list = $mpp_list = array();
		if($card_no_list){
			// Get Package List
			$mpp_list = $appCore->memberManager->getMemberPurchasePackageList($nric);
			
			// Get Log
			$q2 = $con->sql_query("select mppl.*, b.code as bcode, user.u as user_u
				from memberships_purchased_package_log mppl
				left join branch b on b.id=mppl.branch_id
				left join user on user.id=mppl.user_id
				where card_no in (".join(',', array_map('ms', $card_no_list)).")
				order by added desc");
			while($r = $con->sql_fetchassoc($q2)){
				$log_list[] = $r;
			}
			$con->sql_freeresult($q2);
			
			// Get Redeem History
			$redeem_his_list = $appCore->memberManager->getMemberPurchasePackageRedeemHistory($nric, array('get_sa_info'=>1));
		}
		//print_r($redeem_his_list);
		$smarty->assign('mpp_list', $mpp_list);
		$smarty->assign('log_list', $log_list);
		$smarty->assign('redeem_his_list', $redeem_his_list);		
	}
	
	function scan_member(){
		global $con, $smarty, $appCore;
		
		$smarty->display('membership.package.details.scan_member.tpl');
	}
	
	function ajax_check_member(){
		global $con, $smarty, $appCore, $LANG;
		
		//print_r($_REQUEST);
		
		$nric_card_no = trim($_REQUEST['nric_card_no']);
		if(!$nric_card_no)	die($LANG['MEMBERSHIP_CARD_NO_EMPTY2']);
		
		// Get Member by NRIC or Card No
		$member = $appCore->memberManager->getMember($nric_card_no);
		if(!$member)	die($LANG['MEMBERSHIP_CARD_OR_NRIC_NOT_IN_DATABASE']);
		
		// found member
		$ret = array();
		$ret['ok'] = 1;
		$ret['nric'] = $member['nric'];
		print json_encode($ret);
	}
	
	function ajax_show_purchased_items(){
		global $con, $smarty, $appCore, $LANG;
		
		//print_r($_REQUEST);
		
		// NRIC
		$nric = trim($_REQUEST['nric']);
		if(!$nric)	die($LANG['MEMBERSHIP_CARD_NO_EMPTY2']);
		$member = $appCore->memberManager->getMember($nric, false);
		if(!member)	die($LANG['MEMBERSHIP_NRIC_NOT_IN_DATABASE']);
		
		// Member Purchased Package GUID
		$mpp_guid = trim($_REQUEST['mpp_guid']);
		if(!$mpp_guid)	die($LANG['MEMBERSHIP_PACKAGE_INVALID_PURCHASE_GUID']);
		
		// Member Card No List
		$card_no_list = $appCore->memberManager->getMemberCardNoList($nric);
		if(!$card_no_list)	die($LANG['MEMBERSHIP_NO_CARD_FOUND']);
		
		// Get Purchased Package
		$mpp = $appCore->memberManager->getMemberPurchasedPackageByGUID($mpp_guid);
		if(!$mpp)	die($LANG['MEMBERSHIP_PACKAGE_INVALID_PURCHASE_GUID']);
		
		// This package is not for this member
		if(!in_array($mpp['card_no'], $card_no_list))	die($LANG['MEMBERSHIP_PACKAGE_INVALID_PURCHASE_GUID']);
		
		// Get Item List
		$item_list = $appCore->memberManager->getMemberPurchasedPackageItems($mpp_guid);
		
		$smarty->assign('member', $member);
		$smarty->assign('mpp', $mpp);
		$smarty->assign('item_list', $item_list);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('membership.package.details.item_info.tpl');
		print json_encode($ret);		
	}
	
	function ajax_redeem_package(){
		global $con, $smarty, $appCore, $LANG, $config, $sessioninfo;
		
		//print_r($_REQUEST);exit;
		
		// NRIC
		$nric = trim($_REQUEST['nric']);		
		// Member Purchased Package GUID
		$mpp_guid = trim($_REQUEST['mpp_guid']);
		// Redeem Item GUID
		$redeem_item_guid = trim($_REQUEST['redeem_item_guid']);
		
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		$params['branch_id'] = $sessioninfo['branch_id'];
		// Sales Agent Code List
		if($config['masterfile_enable_sa']){
			$temp = preg_split("/\s*[\n\r,]+\s*/", trim($_REQUEST['sa_code_list']));
			$params['sa_code_list'] = array();
			
			if($temp){
				foreach($temp as $str){
					if(trim($str)=='')    continue;
					if(!in_array($str, $params['sa_code_list'])){
						$params['sa_code_list'][] = $str;
					}
				}
			}			
		}
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		// Redeem
		$result = $appCore->memberManager->redeemMemberPurchasedPackage($nric, $mpp_guid, $redeem_item_guid, $params);
		if(!$result['ok']){
			die($result['error']);
		}
		
		// Commit Transaction
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
}

$MEMBER_PACK_DETAILS = new MEMBER_PACK_DETAILS('Membership Package Info');
?>