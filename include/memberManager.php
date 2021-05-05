<?php
/*
8/1/2019 9:56 AM Andy
- Added memberManager function "changeMemberMobilePassword".

8/15/2019 11:42 AM Andy
- Enhanced to record mobile_registered_time when first time set mobile password.

8/19/2019 3:45 PM Andy
- Added new memberManager function "getMobileAppNewCardNo".

9/5/2019 9:45 AM Andy
- Enhanced "getMember" to able to check membership_history.
- Added new memberManager function "setMemberProfileImage".

10/4/2019 3:40 PM Andy
- Added several memberManager function related to membership package.
- Enhanced to capture history when send push notification to member.
- Enhanced to can accept params branch_id in send push notification to member.

11/25/2019 5:45 PM Andy
- Added new member function "sendMemberMobileOTP".
- Added mobile app push notification screen list.
- Enhanced "sendPushNotificationToMember" to can pass params.screen_tag

2/13/2020 4:13 PM Andy
- Added memberManager function "getMemberByGUID", "generateMemberReferralCode" and "setReferByReferralCode".

3/23/2020 3:50 PM Justin
- Added new membermanager function "getMembershipCreditPromoByUniqueID".

- Added new function "getPosCreditMemberTopUpPayment" and "getPosCreditMemberTopUpPromoUsed".

10/14/2020 9:34 AM Andy
- Added new memberManager function "getEFormNewCardNo".
*/
class memberManager{
	// common variable
	public $mobileAppPNScreenList = array(
		'home' => array(
			'description' => 'Home Screen',
			'img_url' => ''
		),
		'coupon' => array(
			'description' => 'Coupon Screen',
			'img_url' => ''
		),
		'promotion' => array(
			'description' => 'Promotion Landing Screen',
			'img_url' => ''
		),
		'package' => array(
			'description' => 'Package Landing Screen',
			'img_url' => ''
		),
		'package_rating' => array(
			'description' => 'Package Rating Screen',
			'img_url' => ''
		),
		'notice_board' => array(
			'description' => 'Notice Board screen',
			'img_url' => ''
		),
	);
	
	// private
	

	function __construct(){
		
	}
	
	public function getMember($nric_or_cardno, $check_card_no = true){
		global $con;
		
		$nric_or_cardno = trim($nric_or_cardno);
		if(!$nric_or_cardno)	return false;
		
		$filter = array();
		if($check_card_no){
			$filter[] = "(m.nric=".ms($nric_or_cardno)." or m.card_no=".ms($nric_or_cardno).")";
		}else{
			$filter[] = "m.nric=".ms($nric_or_cardno);
		}
		$str_filter = "where ".join(' and ', $filter);
		
		// check from member
		$con->sql_query("select m.* 
			from membership m 
			$str_filter");
		$member = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$member && $check_card_no){
			// check from member history
			$con->sql_query("select nric from membership_history where card_no=".ms($nric_or_cardno)." limit 1");
			$mh = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($mh){
				// get member by nric
				return $this->getMember($mh['nric'], false);
			}
			return false;
		}
		
		return $member;
	}
	
	public function getMemberByGUID($membership_guid){
		global $con;
		
		$membership_guid = trim($membership_guid);
		if(!$membership_guid)	return false;
		
		// check from member
		$con->sql_query("select m.* 
			from membership m 
			where membership_guid=".ms($membership_guid));
		$member = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $member;
	}
	
	public function sendPushNotificationToMember($nric_or_cardno, $title, $message, $params = array()){
		global $con, $appCore, $sessioninfo;
		
		$nric_or_cardno = trim($nric_or_cardno);
		if(!$nric_or_cardno)	return false;
		
		// Get Member
		$member = $this->getMember($nric_or_cardno);
		if(!$member)	return false;
		
		$nric = trim($member['nric']);
		
		$bid = mi($params['branch_id']);
		if(!$bid)	$bid = $sessioninfo['branch_id'];
		if(!$bid)	$bid = 1;
		
		$screen_tag = '';
		if(isset($params['screen_tag']))	$screen_tag = trim($params['screen_tag']);
		
		// Create Message Object
		$payload = $appCore->createPayloadJson($title, $message, $screen_tag);
		$success = false;
		$device_sent = $success_sent = 0;
		$more_info = array();
		// Get member mobile app 
		$q1 = $con->sql_query("select * from member_app_session where nric=".ms($nric)." and mobile_type<>'' and push_notification_token<>'' and push_notification_token<>'null'");
		while($r = $con->sql_fetchassoc($q1)){
			$device_sent++;
			$tmp_success = $appCore->sendMobilePushNotification($r['mobile_type'], $r['push_notification_token'], $payload);
			if($tmp_success){
				$success = true;
				$success_sent++;
			}
			$tmp = array();
			$tmp['mobile_type'] = $r['mobile_type'];
			$tmp['push_notification_token'] = $r['push_notification_token'];
			if(!isset($more_info['device_list']))	$more_info['device_list'] = array();
			$more_info['device_list'][] = $tmp;
		}
		$con->sql_freeresult($q1);
		
		if($device_sent > 0){
			$upd = array();
			$upd['guid'] = $appCore->newGUID();
			$upd['branch_id'] = $bid;
			$upd['nric'] = $nric;
			$upd['title'] = $title;
			$upd['message'] = $message;
			$upd['device_sent'] = $device_sent;
			$upd['success_sent'] = $success_sent;
			$upd['more_info'] = serialize($more_info);
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into memberships_push_notification_history ".mysql_insert_by_field($upd));
		}
		
		return $success;
	}
	
	public function getMemberCardNoList($nric){
		global $con;
		
		$nric = trim($nric);
		if(!$nric)	return false;
		
		// Get Card No List
		$card_no_list = array();
		$con->sql_query("select distinct card_no
			from membership_history
			where nric=".ms($nric));
		while($r = $con->sql_fetchassoc()){
			$card_no_list[] = trim($r['card_no']);
		}
		$con->sql_freeresult();
		
		return $card_no_list;
	}
	
	public function getMemberMobileAdsBannerList($screen_name=''){
		global $con;
		
		$bannerList = array();
		$filter = array();
		$filter[] = "mmab.active=1";
		if($screen_name)	$filter[] = "mmab.screen_name=".ms($screen_name);
		
		$str_filter = 'where '.join(' and ', $filter);
		$con->sql_query("select mmab.*
			from membership_mobile_ads_banner mmab
			$str_filter
			order by mmab.screen_name, mmab.sequence");
		while($r = $con->sql_fetchassoc()){
			$r['banner_info'] = unserialize($r['banner_info']);
			
			$bannerList[$r['banner_name']] = $r;
		}
		$con->sql_freeresult();
		
		return $bannerList;
	}
	
	public function getMemberMobileAdsBanner($banner_name){
		global $con;
		
		$filter = array();
		//$filter[] = "mmab.active=1";
		$filter[] = "mmab.banner_name=".ms($banner_name);
		
		$str_filter = 'where '.join(' and ', $filter);
		$con->sql_query("select mmab.*
			from membership_mobile_ads_banner mmab
			$str_filter");
		$banner = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($banner){
			$banner['banner_info'] = unserialize($banner['banner_info']);
		}
		return $banner;
	}
	
	public function changeMemberMobilePassword($nric, $new_p, $params = array()){
		global $con, $LANG;
		
		$user_id = mi($params['user_id']);
		if(!$user_id)	$user_id = 1;
		
		// Get by NRIC only
		$member = $this->getMember($nric, false);
		if(!$member){
			return array('error' => $LANG['MEMBERSHIP_NRIC_NOT_IN_DATABASE']);
		}
		
		// Minimum 6 char
		$new_p = trim($new_p);
		if(strlen($new_p)<6){
			return array('error' => $LANG['MEMBERSHIP_MOBILE_PASS_MIN_CHAR']);
		}
		
		// must alphanumeric only
		if(!ctype_alnum($new_p)){
			return array('error' => $LANG['MEMBERSHIP_MOBILE_PASS_ALPHANUMERIC']);
		}
		
		$upd = array();
		if(!$member['mobile_registered']){
			$upd['mobile_registered'] = 1;
			$upd['mobile_registered_time'] = 'CURRENT_TIMESTAMP';
		}
		
		$upd['memb_password'] = md5($new_p);
		
		$con->sql_query("update membership set ".mysql_update_by_field($upd)." where nric=".ms($nric));
		
		log_br($user_id, 'MEMBERSHIP', 0, 'Updated Member Mobile App Password (NRIC:'.$nric.")");
		
		return array('ok' => 1);
	}
	
	public function getMobileAppNewCardNo(){
		global $con, $config;
		
		// Get current running no 
		$con->sql_query("select * from system_settings where setting_name='member_app_curr_running_no'");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$min_running_no = 0;
		if($tmp){
			$min_running_no = mi($tmp['setting_value']);
		}
		
		// Card No
		$card_no_prefix = trim($config['membership_mobile_settings']['card_no_prefix']);
		$card_no_running_no_length = mi($config['membership_mobile_settings']['card_no_running_no_length']);
		$card_no_running_no_min = mi($config['membership_mobile_settings']['card_no_running_no_min']);
		
		if($card_no_running_no_length<=0){
			return array("error" => "Invalid Card No Running Number Length");
		}
		
		if($min_running_no < $card_no_running_no_min){
			$min_running_no = $card_no_running_no_min;
		}
		$new_running_no = $min_running_no + 1;

		do{
			$new_card_no = $card_no_prefix.sprintf("%0".$card_no_running_no_length."d", $new_running_no);
			
			// Check Duplicated Card No
			// Membership
			$con->sql_query("select nric from membership where card_no=".ms($new_card_no));
			$mem = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$mem){
				// Membership History - Check Card No
				$con->sql_query("select nric from membership_history where card_no=".ms($new_card_no));
				$mem = $con->sql_fetchassoc();
				$con->sql_freeresult();
			}
			
			if(!$mem){
				// Membership History - Check NRIC
				$con->sql_query("select nric from membership_history where nric=".ms($new_card_no));
				$mem = $con->sql_fetchassoc();
				$con->sql_freeresult();
			}
			
			if($mem){
				$new_running_no++;
				$success = false;
			}else{
				$success = true;
			}
		}while(!$success);
		
		$upd = array();
		$upd['setting_name'] = 'member_app_curr_running_no';
		$upd['setting_value'] = $new_running_no;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("replace into system_settings ".mysql_insert_by_field($upd));
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['new_card_no'] = $new_card_no;
		
		return $ret;
	}
	
	public function getNoticeBoardItem($item_id){
		global $con;
		
		$con->sql_query("select * from memberships_notice_board_items where id=".mi($item_id));
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $data;
	}
	
	public function getNoticeBoardItemList($params = array()){
		global $con;
		
		$filter = array();
		if(isset($params['active']))	$filter[] = "active=".mi($params['active']);
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		$item_list = array();
		$con->sql_query("select *
			from memberships_notice_board_items
			$str_filter
			order by sequence");
		while($r = $con->sql_fetchassoc()){
			$item_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		return $item_list;
	}
	
	public function setMemberProfileImage($nric, $file_obj){
		global $con, $appCore, $LANG;
		
		$result = $appCore->isValidUploadImageFile($file_obj);
		if(!$result['ok'])	return $result;
		$ext = trim($result['ext']);
		if(!$ext)	return array("error" => 'Invalid File Extension');
		
		$nric = trim($nric);
		
		// Get by NRIC only
		$member = $this->getMember($nric, false);
		if(!$member){
			return array('error' => $LANG['MEMBERSHIP_NRIC_NOT_IN_DATABASE']);
		}
		
		// Already have url
		if($member['profile_image_url']){
			// Rename extension
			$profile_image_url = preg_replace("/\.(jpg|jpeg|png|gif)$/i", ".".$ext, $member['profile_image_url']);
			
			// Move Uploaded File
			if(!move_uploaded_file($file_obj['tmp_name'], $profile_image_url)){
				return array('error' => "Failed to Move Uploaded File.");
			}
			
			if($member['profile_image_url'] != $profile_image_url){
				// Delete old image
				unlink($member['profile_image_url']);
			}
		}else{
			// Generate New URL
			$tmp_str = md5($nric);
			//print ($tmp_str);
			$folder_1 = $tmp_str[0];
			$folder_2 = $tmp_str[1];
			//print "-$folder_1-$folder_2";exit;
			
			//$directory = dirname(__FILE__)."/../attch"
			$directory = "attch/member_profile_image";
			if(!check_and_create_dir($directory)){
				return array('error' => "Create Image Folder Failed");
			}
			
			$directory .= "/".$folder_1;
			if(!check_and_create_dir($directory)){
				return array('error' => "Create Image Folder Failed");
			}
			
			$directory .= "/".$folder_2;
			if(!check_and_create_dir($directory)){
				return array('error' => "Create Image Folder Failed");
			}
			
			$profile_image_url = $directory."/".$appCore->newGUID().".".$ext;
			//die($profile_image_url);
			
			// Move Uploaded File
			if(!move_uploaded_file($file_obj['tmp_name'], $profile_image_url)){
				return array('error' => "Failed to Move Uploaded File.");
			}
		}
		
		$upd = array();
		$upd['profile_image_url'] = $profile_image_url;
		$con->sql_query("update membership set ".mysql_update_by_field($upd)." where nric=".ms($nric));
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['image_url'] = $profile_image_url;
		return $ret;
	}
	
	public function getMembershipPackageByUniqueID($package_unique_id){
		global $con, $appCore, $LANG;
		
		$package_unique_id = mi($package_unique_id);
		if($package_unique_id<=0)	return array("error" => $LANG['MEMBERSHIP_PACKAGE_INVALID_UNIQUE_ID']);
		
		// Get Package
		$con->sql_query("select * from membership_package where unique_id=$package_unique_id");
		$package = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$package)	return array("error" => sprintf($LANG['MEMBERSHIP_PACKAGE_NOT_FOUND'], $package_unique_id));
		
		$package['allowed_branches'] = unserialize($package['allowed_branches']);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['data'] = $package;
		return $ret;
	}
	
	public function getMembershipPackageItemsByUniqueID($package_unique_id){
		global $con, $appCore, $LANG;
		
		$package_unique_id = mi($package_unique_id);
		if($package_unique_id<=0)	return array("error" => $LANG['MEMBERSHIP_PACKAGE_INVALID_UNIQUE_ID']);
		
		// Get Package Items
		$package_items = array();
		$q1 = $con->sql_query("select * from membership_package_items where package_unique_id=$package_unique_id order by sequence");
		while($r = $con->sql_fetchassoc($q1)){
			$package_items[$r['guid']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['data'] = $package_items;
		return $ret;
	}
	
	public function getMemberPOSPurchasedPackage($card_no, $pos_receipt_ref_no, $package_unique_id, $params = array()){
		global $con, $appCore, $LANG;
		
		$card_no = trim($card_no);
		if(!$card_no)	return false;	// No Member Card No, Cannot Check
		
		$package_unique_id = mi($package_unique_id);
		if(!$package_unique_id)	return false;	// No Package Unique ID, Cannot Check
		
		$pos_receipt_ref_no = trim($pos_receipt_ref_no);
		if(!$pos_receipt_ref_no)	return false;	// No Receipt Ref No, Cannot Check
				
		$filter = array();
		$filter[] = "mpp.card_no=".ms($card_no);
		$filter[] = "mpp.package_unique_id=".mi($package_unique_id);
		$filter[] = "mpp.pos_receipt_ref_no=".ms($pos_receipt_ref_no);
		$str_filter = "where ".join(' and ', $filter);
		
		$con->sql_query("select mpp.*
			from memberships_purchased_package mpp
			$str_filter
			order by added
			limit 1");
		$mpp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($mpp){
			// Do additional process for the package
			$mpp = $this->constructMemberPurchasedPackageData($mpp);
		}
		
		return $mpp;
	}
	
	public function getMemberPurchasedPackageByGUID($guid, $params = array()){
		global $con, $appCore, $LANG;
				
		$guid = trim($guid);
		if(!$guid)	return false;
		
		$filter = array();
		$filter[] = "mpp.guid=".ms($guid);
		$str_filter = "where ".join(' and ', $filter);
		
		$con->sql_query("select mpp.*
			from memberships_purchased_package mpp
			$str_filter
			order by added
			limit 1");
		$mpp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($mpp){
			// Do additional process for the package
			$mpp = $this->constructMemberPurchasedPackageData($mpp);
		}
		
		return $mpp;
	}
	
	private function constructMemberPurchasedPackageData($mpp){
		global $con, $appCore, $LANG;
		
		// so far nothing to process yet
		
		return $mpp;
	}
	
	public function addMemberPurchasedPackage($card_no, $package_unique_id, $qty=1, $params = array()){
		global $con, $appCore, $LANG, $config, $sessioninfo;
		
		$card_no = trim($card_no);
		if(!$card_no)	return array('error'=> $LANG['MEMBERSHIP_CARD_NO_EMPTY2']);
		
		$package_unique_id = mi($package_unique_id);
		if(!$package_unique_id)	return array('error'=> $LANG['MEMBERSHIP_PACKAGE_INVALID_UNIQUE_ID']);
		
		if($qty<=0)	return array('error'=> $LANG['MEMBERSHIP_PACKAGE_INVALID_PURCHASE_QTY']);
		
		$pos = isset($params['pos']) ? $params['pos'] : '';
		if($pos){
			//print_r($pos);
			$pos_branch_id = mi($pos['branch_id']);
			$pos_receipt_no = mi($pos['receipt_no']);
			$pos_receipt_ref_no = trim($pos['receipt_ref_no']);
			$date = trim($pos['date']);
			if(!$pos_branch_id || !$pos_receipt_no || !$pos_receipt_ref_no)	return array('error'=> $LANG['MEMBERSHIP_PACKAGE_INVALID_PURCHASE_POS']);
		}
		
		if(isset($params['date']))	$date = trim($params['date']);
		if(!$date)	$date = date("Y-m-d");
		
		$user_id = mi($params['user_id']);
		if(!$user_id)	$user_id = 1;
		
		// Get Package
		$result = $this->getMembershipPackageByUniqueID($package_unique_id);
		if(!$result['data'])	return array('error' => $result['error']);
		$package = $result['data'];
		
		// Get Package Items
		$result = $this->getMembershipPackageItemsByUniqueID($package_unique_id);
		if(!$result['data'])	return array('error' => $result['error']);
		$package_items = $result['data'];
		
		// Insert Package
		$upd = array();
		$guid = $upd['guid'] = $appCore->newGUID();
		$upd['card_no'] = $card_no;
		$upd['date'] = $date;
		
		if($pos){
			$upd['pos_branch_id'] = $pos_branch_id;
			$upd['pos_receipt_no'] = $pos_receipt_no;
			$upd['pos_receipt_ref_no'] = $pos_receipt_ref_no;
		}
		$upd['package_unique_id'] = $package_unique_id;
		$upd['qty'] = $qty;
		$upd['remaining_entry'] = $upd['earn_entry'] = $package['total_entry_earn']*$qty;
		if($params['remark']){
			$upd['remark'] = trim($params['remark']);
		}
		
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		// Generate Ref No
		$date_data = getdate();
		do{
			$ref_no = substr($guid, 0, 8).(time()-mktime(0, 0, 0, $date_data['mon'], $date_data['mday'], $date_data['year'])).rand(1, 9999);
			$con->sql_query("select ref_no from memberships_purchased_package where ref_no=".ms($ref_no));
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
		}while($tmp);
		$upd['ref_no'] = $ref_no;
		
		$con->sql_query("insert into memberships_purchased_package ".mysql_insert_by_field($upd));
		
		// Loop to insert Package Items
		foreach($package_items as $r){
			$upd2 = array();
			$upd2['guid'] = $appCore->newGUID();
			$upd2['purchased_package_guid'] = $guid;
			$upd2['membership_package_items_guid'] = $r['guid'];
			$upd2['title'] = $r['title'];
			$upd2['description'] = $r['description'];
			$upd2['remark'] = $r['remark'];
			$upd2['entry_need'] = $r['entry_need'];
			$upd2['max_redeem'] = $r['max_redeem']*$qty;
			$upd2['sequence'] = $r['sequence'];
			$con->sql_query("insert into memberships_purchased_package_items ".mysql_insert_by_field($upd2));
		}
		
		log_br($user_id, 'MEMBERSHIP', 0, "Added Membership Purchased Package (Card No: ".$card_no.", ".$package['doc_no'].", ".$package['title'].", Qty: $qty), Purchase Ref No: $ref_no");
		
		$params = array();
		$params['user_id'] = $user_id;
		if($pos_branch_id>0){
			$params['branch_id'] = $pos_branch_id;
		}elseif($sessioninfo['branch_id']){
			$params['branch_id'] = $sessioninfo['branch_id'];
		}else{
			$params['branch_id'] = 1;
		}
		$this->logMemberPurchasedPackageHistory($card_no, $guid, "New Package (".$package['doc_no'].", ".$package['title'].", Qty: $qty)", $params);
		
		if($config['membership_mobile_settings'] && $config['enable_push_notification']){
			$this->sendPushNotificationToMember($card_no, 'New Package', 'You have received member package.', array('branch_id'=>$params['branch_id']));
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['guid'] = $guid;
		return $ret;
	}
	
	public function cancelMemberPurchasedPackage($guid, $params = array()){
		global $con, $appCore, $LANG, $config, $sessioninfo;
		
		// Get Member Purchased Package
		$mpp = $this->getMemberPurchasedPackageByGUID($guid);
		if(!$mpp)	return array('error'=>sprintf($LANG['MEMBERSHIP_PACKAGE_INVALID_DATA'], 'guid'));
		
		// Still Active, Need Cancel
		if($mpp['active']){
			$user_id = mi($params['user_id']);
			if(!$user_id)	$user_id = 1;
			
			// Get Package
			$result = $this->getMembershipPackageByUniqueID($mpp['package_unique_id']);
			if(!$result['data'])	return array('error' => $result['error']);
			$package = $result['data'];
		
			// Update to Cancelled
			$upd = array();
			$upd['active'] = 0;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("update memberships_purchased_package set ".mysql_update_by_field($upd)." where guid=".ms($guid));
			
			log_br($user_id, 'MEMBERSHIP', 0, "Cancelled Membership Purchased Package (Card No: ".$mpp['card_no'].", ".$package['doc_no'].", ".$package['title'].", Qty: ".$mpp['qty']."), Purchase Ref No: ".$mpp['ref_no']);
			
			$params = array();
			$params['user_id'] = $user_id;
			if($mpp['pos_branch_id']>0){
				$params['branch_id'] = $mpp['pos_branch_id'];
			}elseif($sessioninfo['branch_id']){
				$params['branch_id'] = $sessioninfo['branch_id'];
			}else{
				$params['branch_id'] = 1;
			}
			$this->logMemberPurchasedPackageHistory($mpp['card_no'], $guid, "Cancel Package (".$package['doc_no'].", ".$package['title'].", Qty: ".$mpp['qty'].")", $params);
		
			//if($config['membership_mobile_settings'] && $config['enable_push_notification']){
				//$this->sendPushNotificationToMember($card_no, 'Package Cancelled', 'Your member package cancelled.');
			//}
		}
		
		$ret = array();
		$ret['ok'] = 1;
		return $ret;
	}
	
	public function getMemberPurchasePackageList($nric_or_cardno, $package_status='', $params = array()){
		global $con, $appCore, $LANG;
		
		$nric_or_cardno = trim($nric_or_cardno);
		if(!$nric_or_cardno)	return false;
		
		// Get Member
		$member = $this->getMember($nric_or_cardno);
		if(!$member)	return false;
		
		$nric = trim($member['nric']);
		
		// Get Card No List
		$card_no_list = $appCore->memberManager->getMemberCardNoList($nric);
		
		$mpp_list = array();
		
		if($card_no_list){
			$filter = array();
			$filter[] = "mpp.card_no in (".join(',', array_map('ms', $card_no_list)).")";
			if($package_status == 'available'){
				$filter[] = "mpp.active=1 and mpp.remaining_entry>0";
			}elseif($package_status == 'all_used'){
				$filter[] = "mpp.active=1 and mpp.remaining_entry<=0";
			}elseif($package_status == 'cancelled'){
				$filter[] = "mpp.active=0";
			}
			
			$str_filter = "where ".join(' and ', $filter);
			// Get Package List
			$q1 = $con->sql_query("select mpp.*, mp.doc_no, mp.title, b.code as pos_bcode, mp.link_sku_item_id		
			from memberships_purchased_package mpp
			join membership_package mp on mp.unique_id=mpp.package_unique_id
			left join branch b on b.id=mpp.pos_branch_id
			where mpp.card_no in (".join(',', array_map('ms', $card_no_list)).")
			order by mpp.date, mpp.added");
			while($r = $con->sql_fetchassoc($q1)){
				if($r['active']){
					if($r['remaining_entry']>0){
						$p_status = 'available';
					}else{
						$p_status = 'all_used';
					}
				}else{
					$p_status = 'cancelled';
				}
				$mpp_list[$p_status][$r['guid']] = $r;
			}
			$con->sql_freeresult($q1);
			
			
		}
		
		if($package_status){
			if(isset($mpp_list[$package_status])){
				return $mpp_list[$package_status];
			}
		}
		
		return $mpp_list;		
	}
	
	public function getMemberPurchasedPackageItems($mpp_guid, $item_guid ='', $params = array()){
		global $con, $appCore, $LANG;
		
		$mpp_guid = trim($mpp_guid);
		if(!$mpp_guid)	return false;
		
		
		$q1 = $con->sql_query("select mppi.*, (select count(*) from memberships_purchased_package_items_redeem where purchased_package_items_guid=mppi.guid) as used_count
			from memberships_purchased_package_items mppi
			where mppi.purchased_package_guid=".ms($mpp_guid)."
			order by mppi.sequence");
		$item_list = array();
		$single_item_to_return = array();
		while($r = $con->sql_fetchassoc($q1)){
			
			if($item_guid && $r['guid'] == $item_guid){	// Only Get 1 Item
				$single_item_to_return = $r;
				break;
			}
			$item_list[$r['guid']] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($single_item_to_return)	return $single_item_to_return;
		return $item_list;
	}
	
	public function logMemberPurchasedPackageHistory($card_no, $purchased_package_guid, $log, $params = array()){
		global $con, $appCore, $LANG, $sessioninfo;
		
		$card_no = trim($card_no);
		$purchased_package_guid = trim($purchased_package_guid);
		$log = trim($log);
		
		if(!$card_no)	return false;
		if(!$purchased_package_guid)	return false;
		if(!$log)	return false;
		
		$user_id = mi($params['user_id']);
		if(!$user_id)	$user_id = 1;
		
		$branch_id = mi($sessioninfo['branch_id']);
		if(!$branch_id)	$branch_id = mi($params['branch_id']);
		if(!$branch_id)	$branch_id = 1;
		
		$purchased_package_items_guid = trim($params['purchased_package_items_guid']);
		
		$upd = array();
		$upd['guid'] = $appCore->newGUID();
		$upd['card_no'] = $card_no;
		$upd['branch_id'] = $branch_id;
		$upd['user_id'] = $user_id;
		$upd['purchased_package_guid'] = $purchased_package_guid;
		$upd['purchased_package_items_guid'] = $purchased_package_items_guid;
		$upd['log'] = $log;
		$upd['added'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into memberships_purchased_package_log ".mysql_insert_by_field($upd));
		
		return true;
	}
	
	public function redeemMemberPurchasedPackage($nric, $mpp_guid, $redeem_item_guid, $params = array()){
		global $con, $appCore, $LANG, $sessioninfo, $config;
		
		$nric = trim($nric);
		$mpp_guid = trim($mpp_guid);
		$redeem_item_guid = trim($redeem_item_guid);
		$user_id = mi($params['user_id']);
		if(!$user_id)	$user_id = 1;
		$branch_id = mi($params['branch_id']);
		if(!$branch_id)	$branch_id = 1;
		
		if(!$nric)	return array('error' => $LANG['MEMBERSHIP_CARD_NO_EMPTY2']);
		if(!$mpp_guid)	return array('error' => $LANG['MEMBERSHIP_PACKAGE_INVALID_PURCHASE_GUID']);
		if(!$redeem_item_guid)	return array('error' => $LANG['MEMBERSHIP_PACKAGE_INVALID_REDEEM_ITEMS_GUID']);
		
		// Get Member
		$member = $this->getMember($nric, false);
		if(!member)	return array('error' => $LANG['MEMBERSHIP_NRIC_NOT_IN_DATABASE']);
		
		// Get Purchased Package
		$mpp = $this->getMemberPurchasedPackageByGUID($mpp_guid);
		if(!$mpp)	return array('error' => $LANG['MEMBERSHIP_PACKAGE_INVALID_PURCHASE_GUID']);
		
		// Get Package
		$result = $this->getMembershipPackageByUniqueID($mpp['package_unique_id']);
		if(!$result['data'])	return array('error' => $result['error']);
		$package = $result['data'];
			
		// Member Card No List
		$card_no_list = $this->getMemberCardNoList($nric);
		if(!$card_no_list)	return array('error' => $LANG['MEMBERSHIP_NO_CARD_FOUND']);
		
		// This package is not for this member
		if(!in_array($mpp['card_no'], $card_no_list))	return array('error' => $LANG['MEMBERSHIP_PACKAGE_INVALID_PURCHASE_GUID']);
		
		// Get Purchased Package Item
		$mpp_items = $this->getMemberPurchasedPackageItems($mpp_guid, $redeem_item_guid);
		
		$sa_list = array();
		if($config['masterfile_enable_sa']){
			// Got Sales Agent ID List
			if(isset($params['sa_id_list']) && is_array($params['sa_id_list'])){
				foreach($params['sa_id_list'] as $sa_id){
					// already have
					if($sa_list[$sa_id])	continue;
					
					// Search Sales Agent by ID
					$sa = $appCore->salesAgentManager->getSA($sa_id);	// Get Sales Agent
					if(!$sa){	// Not Found
						return array('error' => $LANG['SA_ID_INVALID']);
					}
					$sa_list[$sa_id] = $sa;
				}
			}
			
			// Got Sales Agent
			if(isset($params['sa_code_list']) && is_array($params['sa_code_list'])){
				foreach($params['sa_code_list'] as $sa_code){
					// Search Sales Agent by Code
					$sa = $appCore->salesAgentManager->getSA('', $sa_code);	// Get Sales Agent
					if(!$sa){	// Not Found
						return array('error' => sprintf($LANG['SA_CODE_INVALID'], $sa_code, 'invalid'));
					}
					$sa_list[$sa['id']] = $sa;
				}
			}
		}
		
		// Insert redeem
		$upd = array();
		$upd['guid'] = $redeem_guid = $appCore->newGUID();
		$upd['branch_id'] = $branch_id;
		$upd['purchased_package_items_guid'] = $redeem_item_guid;
		$upd['date'] = date("Y-m-d");
		$upd['used_entry'] = $mpp_items['entry_need'];
		$upd['user_id'] = $user_id;
		$upd['last_update'] = $upd['added'] = 'CURRENT_TIMESTAMP';
		if($sa_list){
			$upd['sa_info'] = array();
			foreach($sa_list as $sa_id => $sa){
				$tmp = array('id' => $sa_id);
				$upd['sa_info']['sa_list'][$sa_id] = $tmp;
			}
			$upd['sa_info'] = serialize($upd['sa_info']);
		}
		$con->sql_query("insert into memberships_purchased_package_items_redeem ".mysql_insert_by_field($upd));
		
		$upd2 = array();
		$upd2['used_entry'] = $mpp['used_entry']+$mpp_items['entry_need'];
		$upd2['remaining_entry'] = $mpp['earn_entry'] - $upd2['used_entry'];
		$upd2['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update memberships_purchased_package set ".mysql_update_by_field($upd2)." where guid=".ms($mpp_guid));
		
		// User Log
		log_br($user_id, 'MEMBERSHIP', 0, "Redeem Membership Purchased Package (Card No: ".$mpp['card_no'].", ".$package['doc_no'].", ".$package['title'].", Item: ".$mpp_items['title']."), Redeem GUID: $redeem_guid, Package Ref No: ".$mpp['ref_no']);
		
		// Package Log
		$params = array();
		$params['user_id'] = $user_id;
		$params['branch_id'] = $branch_id;
		$params['purchased_package_items_guid'] = $redeem_item_guid;
		$str_log = "Package Item Redeem: ".$mpp_items['title'];
		$this->logMemberPurchasedPackageHistory($mpp['card_no'], $mpp_guid, $str_log, $params);
		
		if($config['membership_mobile_settings'] && $config['enable_push_notification']){
			$this->sendPushNotificationToMember($nric, 'Package Item Redeemed', 'You had redeemed a package item.', array('branch_id'=>$branch_id));
		}
		
		$ret = array();
		$ret['ok'] = 1;
		return $ret;
	}
	
	public function getMemberPurchasePackageRedeemHistory($nric_or_cardno, $params = array()){
		global $con, $appCore, $LANG, $sessioninfo, $config;
		
		$nric_or_cardno = trim($nric_or_cardno);
		if(!$nric_or_cardno)	return false;
		
		// Get Member
		$member = $this->getMember($nric_or_cardno);
		if(!$member)	return false;
		
		// Params
		$get_sa_info = isset($params['get_sa_info']) ? mi($params['get_sa_info']) : 0;
		$mpp_guid = isset($params['mpp_guid']) ? trim($params['mpp_guid']) : '';
		$mppi_guid = isset($params['mppi_guid']) ? trim($params['mppi_guid']) : '';
		$mppir_guid = isset($params['mppir_guid']) ? trim($params['mppir_guid']) : '';
		
		$nric = trim($member['nric']);
		
		// Get Card No List
		$card_no_list = $appCore->memberManager->getMemberCardNoList($nric);
		
		$redeem_his_list = array();
		$sa_list = array();
		
		if($card_no_list){
			$filter = array();
			$filter[] = "mpp.card_no in (".join(',', array_map('ms', $card_no_list)).")";
			if($mpp_guid){
				$filter[] = "mpp.guid=".ms($mpp_guid);
			}
			if($mppi_guid){
				$filter[] = "mppir.purchased_package_items_guid=".ms($mppi_guid);
			}
			if($mppir_guid){
				$filter[] = "mppir.guid=".ms($mppir_guid);
			}
			$str_filter = "where ".join(' and ', $filter);
			// Get Redeem History
			$q1 = $con->sql_query("select mppir.*, mp.doc_no, mp.title as package_title, mppi.title as item_title, b.code as bcode, user.u as user_u, mpp.guid as mpp_guid, mpp.ref_no
				from memberships_purchased_package_items_redeem mppir
				join memberships_purchased_package_items mppi on mppi.guid=mppir.purchased_package_items_guid
				join memberships_purchased_package mpp on mpp.guid=mppi.purchased_package_guid
				join membership_package mp on mp.unique_id=mpp.package_unique_id
				left join branch b on b.id=mppir.branch_id
				left join user on user.id=mppir.user_id
				$str_filter
				order by mppir.date desc, mppir.added desc");
			while($r = $con->sql_fetchassoc($q1)){
				$r['sa_info'] = unserialize($r['sa_info']);
				if($get_sa_info && $r['sa_info']){	// Need to Get Sales Agent Info
					foreach($r['sa_info']['sa_list'] as $sa_id => $dummy){
						if(!isset($sa_list[$sa_id])){
							$sa_list[$sa_id] = $appCore->salesAgentManager->getSA($sa_id);	// Get Sales Agent
						}
						$r['sa_info']['sa_list'][$sa_id]['sa_info'] = $sa_list[$sa_id];
					}					
				}
				$redeem_his_list[] = $r;
			}
			$con->sql_freeresult($q1);
		}
		
		if($mppir_guid && $redeem_his_list)	return $redeem_his_list[0];	// return the first data
		//print_r($redeem_his_list);
		return $redeem_his_list;
	}
	
	public function updateMemberPurchasePackageRedeemHistoryRate($mppir_guid, $params = array()){
		global $con, $appCore, $LANG, $sessioninfo, $config;
		
		$mppir_guid = trim($mppir_guid);
		if(!$mppir_guid)	return array('error' => $LANG['MEMBERSHIP_PACKAGE_INVALID_REDEEM_ITEMS_GUID']);
		
		// Service Rating
		$service_rating = isset($params['service_rating']) ? mi($params['service_rating']) : 0;
		
		// Sales Agent Rating
		$sa_rating_list = array();
		if($params['sa_rating']){
			foreach($params['sa_rating'] as $sa_id => $sa_rating){
				if($sa_rating>0){
					$sa_rating_list[$sa_id] = $sa_rating;
				}
			}
		}
		
		// Nothing to Rate
		if($service_rating <=0 && !$sa_rating_list)	return array('error' => $LANG['MEMBERSHIP_PACKAGE_REDEEM_RATE_NOTHING']);
		
		// Get Redeem History
		$con->sql_query("select * from memberships_purchased_package_items_redeem where guid=".ms($mppir_guid)." for update");
		$mppir = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$mppir)	return array('error' => $LANG['MEMBERSHIP_PACKAGE_INVALID_REDEEM_ITEMS_GUID']);
		
		$mppir['sa_info'] = unserialize($mppir['sa_info']);
		
		$upd = array();
		if($service_rating>0)	$upd['service_rating'] = $service_rating;
		if($sa_rating_list){
			$upd['sa_info'] = $mppir['sa_info'];
			foreach($sa_rating_list as $sa_id => $sa_rating){
				// This sales agent is not in this redemption
				if(!isset($upd['sa_info']['sa_list'][$sa_id])){
					return array('error' => sprintf($LANG['MEMBERSHIP_PACKAGE_REDEEM_SA_INVALID'], $sa_id));
				}
				
				$upd['sa_info']['sa_list'][$sa_id]['rate'] = $sa_rating;
			}
		}
		
		// Calculcate Overall Rating
		$final_service_rating = $upd['service_rating'] ? $upd['service_rating'] : $service_rating;
		$final_sa_info = $upd['sa_info'] ? $upd['sa_info'] : $mppir['sa_info'];
		$divide_count = 1;
		$total_rate = $final_service_rating;
		if($final_sa_info){
			foreach($final_sa_info['sa_list'] as $sa_id => $sa){
				$divide_count++;
				$total_rate += $sa['rate'];
			}
		}
		$upd['overall_rating'] = floor($total_rate / $divide_count);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		if($upd['sa_info'])	$upd['sa_info'] = serialize($upd['sa_info']);
		
		$con->sql_query("update memberships_purchased_package_items_redeem set ".mysql_update_by_field($upd)." where guid=".ms($mppir_guid));
		
		$ret = array();
		$ret['ok'] = 1;
		return $ret;
	}
	
	public function sendMemberMobileOTP($nric, $mobile_num='', $params = array()){
		global $con, $appCore, $sessioninfo;
		
		$nric = trim($nric);
		if(!$nric)	return false;
		
		// Got pass member data, no need select again
		if(isset($params['member_object'])){
			$member = $params['member_object'];
		}
		
		// member data is empty, need select by NRIC
		if(!$member){
			$member = $this->getMember($nric, false);
		}
		
		// Member Not Found
		if(!$member)	return false;
		
		// if no specified mobile number, use phone_3
		if(!$mobile_num){
			$mobile_num = preg_replace("/[^0-9]/", "", trim($member['phone_3']));
		}
		
		// No Phone Number
		if(!$mobile_num)	return false;
		
		// Branch ID
		$bid = isset($params['branch_id']) ? mi($params['branch_id']) : mi($sessioninfo['branch_id']);
		if($bid <=0)	$bid = 1;
		
		// User ID
		$user_id = isset($params['user_id']) ? mi($params['user_id']) : mi($sessioninfo['id']);
		if($user_id <=0)	$user_id = 1;
		
		// Generate 6 Digit Number
		$otp_code = $appCore->generateRandomCode(6, true);
		
		$upd = array();
		$guid = $appCore->newGUID();
		$upd['guid'] = $guid;
		$upd['branch_id'] = $bid;
		$upd['user_id'] = $user_id;
		$upd['card_no'] = $member['card_no'];
		$upd['mobile_num'] = $mobile_num;
		$upd['otp_code'] = $otp_code;
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into memberships_otp ".mysql_insert_by_field($upd));
		
		$sms_msg = 'Your Verification Code: '.$otp_code.'.';
		$sms_msg .= "\nValid for 5 mins, please do not share this code with others";
		
		$tmp_params = array();
		$tmp_params['member_card_no'] = $member['card_no'];
		$tmp_params['branch_id'] = $bid;
		$tmp_params['user_id'] = $user_id;
		$success = $appCore->send_sms_single($mobile_num, $sms_msg, $tmp_params);
		
		return $success;
	}
	
	public function generateMemberReferralCode($nric){
		global $con, $appCore;
		
		$nric = trim($nric);
		if(!$nric)	return false;
		
		// Get Member by NRIC
		$member = $this->getMember($nric, false);
		if(!$member)	return false;
		
		// Return back the existing referral_code
		if($member['referral_code'])	return $member['referral_code'];
		
		// Use Back Card No as Referral Code
		//$referral_code = trim($member['card_no']);
		
		$chance = 3;
		do{
			if(!$referral_code){	// No Code, Random Generate
				$referral_code = $appCore->generateRandomCode(6);
			}
			
			// Prevent Duplicate
			$con->sql_query("select nric from membership where referral_code=".ms($referral_code)." and nric<>".ms($nric));
			$used = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($used){
				// Referral Code Used, need to regen
				$referral_code = '';
				$chance--;
			}
		}while($chance>0 && !$referral_code);
		
		// Failed to Generate Referral Code
		if(!$referral_code)	return false;
		
		$upd = array();
		$upd['referral_code'] = $referral_code;
		$upd['referral_code_generate_time'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update membership set ".mysql_update_by_field($upd)." where nric=".ms($nric));
		
		return $referral_code;
	}
	
	public function setReferByReferralCode($nric, $referral_code){
		global $con, $appCore, $LANG;
		
		$nric = trim($nric);
		$referral_code = trim($referral_code);
		if(!$nric)	return array("error"=>sprintf($LANG['INVALID_DATA'], "nric", $nric), "error_code"=>"INVALID_DATA");
		if(!$referral_code)	return array("error"=>sprintf($LANG['INVALID_DATA'], "referral_code", $referral_code), "error_code"=>"INVALID_DATA");
		
		// Get Member by NRIC
		$member = $this->getMember($nric, false);
		if(!$member)	return array('error' => $LANG['MEMBERSHIP_NRIC_NOT_IN_DATABASE'], "error_code"=>"MEMBERSHIP_NRIC_NOT_IN_DATABASE");
		
		// Required Membership GUID
		if(!$member['membership_guid'])	return array('error' => $LANG['MEMBERSHIP_GUID_EMPTY'], "error_code"=>"MEMBERSHIP_GUID_EMPTY");
		
		// Already refer to other people
		if($member['refer_by_referral_code']){
			return array('error' => $LANG['MEMBERSHIP_REFERRAL_CODE_USED'], "error_code"=>"MEMBERSHIP_REFERRAL_CODE_USED");
		}
		
		// Cannot refer to yourself
		if($member['referral_code'] == $referral_code){
			return array('error' => $LANG['MEMBERSHIP_REFER_YOURSELF'], "error_code"=>"MEMBERSHIP_REFER_YOURSELF");
		}
		
		// Check referral_code exist or not
		$con->sql_query("select membership_guid, nric from membership where referral_code=".ms($referral_code)." and nric<>".ms($nric));
		$referrer = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Referral Code Not Found
		if(!$referrer){
			return array('error' => $LANG['MEMBERSHIP_REFERRAL_CODE_NOT_FOUND'], "error_code"=>"MEMBERSHIP_REFERRAL_CODE_NOT_FOUND");
		}
		
		// Required Referrer GUID 
		if(!$referrer['membership_guid']){
			return array('error' => $LANG['MEMBERSHIP_GUID_EMPTY'], "error_code"=>"MEMBERSHIP_GUID_EMPTY");
		}

		// Update Member
		$upd = array();
		$upd['refer_by_referral_code'] = $referral_code;
		$upd['refer_by_added'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update membership set ".mysql_update_by_field($upd)." where nric=".ms($nric));
		
		// Insert Referral History
		$referral_history_guid = $appCore->newGUID();
		$upd2 = array();
		$upd2['guid'] = $referral_history_guid;
		$upd2['referee_membership_guid'] = $member['membership_guid'];
		$upd2['referrer_membership_guid'] = $referrer['membership_guid'];
		$upd2['referral_code'] = $referral_code;
		$upd2['added'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into memberships_referral_history ".mysql_insert_by_field($upd2));
		
		// Check Referral Program Coupon
		$appCore->couponManager->checkReferralProgramCouponByReferralHistory($referral_history_guid);
		
		return array("ok"=>1);
	}
	
	/*public function getMembershipCreditPromoByUniqueID($cp_unique_id){
		global $con, $appCore, $LANG;
		
		$cp_unique_id = mi($cp_unique_id);
		if($cp_unique_id<=0)	return array("error" => $LANG['MEMBERSHIP_CP_INVALID_UNIQUE_ID']);
		
		// Get Credit Promotion
		$con->sql_query("select * from membership_credit_promotion where unique_id=$cp_unique_id");
		$credit_promo = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$credit_promo)	return array("error" => sprintf($LANG['MEMBERSHIP_CP_NOT_FOUND'], $cp_unique_id));
		
		$credit_promo['allowed_branches'] = unserialize($credit_promo['allowed_branches']);
		$credit_promo['allowed_member_type'] = unserialize($credit_promo['allowed_member_type']);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['data'] = $credit_promo;
		
		return $ret;
	}
	
	public function getPosCreditMemberTopUpPayment($prms=array()){
		global $con, $appCore, $LANG;
		
		if(!$prms) return false;
		
		// Get Data from database
		$con->sql_query("select * from pos_credit_member_topup_payment where branch_id=".mi($prms['branch_id'])." and counter_id=".mi($prms['counter_id'])." and date=".ms($prms['date'])." and ref_no=".ms($prms['ref_no'])." order by added desc limit 1");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
				
		// Never have the record before
		if(!$data)	return false;
		
		$ret['data'] = $data;
		unset($data);
		
		return $ret;
	}
	
	public function getPosCreditMemberTopUpPromoUsed($prms=array()){
		global $con, $appCore, $LANG;
		
		if(!$prms) return false;
		
		$filters = array();
		$filters[] = "branch_id = ".mi($prms['branch_id']);
		$filters[] = "promo_unique_id = ".mi($prms['promo_unique_id']);
		$filters[] = "membership_guid = ".ms($prms['membership_guid']);
		$filters[] = "active = 1";
		$q1 = $con->sql_query("select count(*) as ttl_promo_used from pos_credit_member_topup where ".join(" and ", $filters));
		$data = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		unset($filters);
		
		return $data;
	}*/
	
	public function getEFormNewCardNo(){
		global $con, $config;
		
		// Get current running no 
		$con->sql_query("select * from system_settings where setting_name='member_eform_curr_running_no'");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$min_running_no = 0;
		if($tmp){
			$min_running_no = mi($tmp['setting_value']);
		}
		
		// Card No
		$card_no_prefix = trim($config['membership_eform_settings']['card_no_prefix']);
		$card_no_running_no_length = mi($config['membership_eform_settings']['card_no_running_no_length']);
		$card_no_running_no_min = mi($config['membership_eform_settings']['card_no_running_no_min']);
		
		if($card_no_running_no_length<=0){
			return array("error" => "Invalid Card No Running Number Length");
		}
		
		if($min_running_no < $card_no_running_no_min){
			$min_running_no = $card_no_running_no_min;
		}
		$new_running_no = $min_running_no + 1;

		do{
			$new_card_no = $card_no_prefix.sprintf("%0".$card_no_running_no_length."d", $new_running_no);
			
			// Check Duplicated Card No
			// Membership
			$con->sql_query("select nric from membership where card_no=".ms($new_card_no)." or nric=".ms($new_card_no));
			$mem = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$mem){
				// Membership History - Check Card No
				$con->sql_query("select nric from membership_history where card_no=".ms($new_card_no));
				$mem = $con->sql_fetchassoc();
				$con->sql_freeresult();
			}
			
			if(!$mem){
				// Membership History - Check NRIC
				$con->sql_query("select nric from membership_history where nric=".ms($new_card_no));
				$mem = $con->sql_fetchassoc();
				$con->sql_freeresult();
			}
			
			if($mem){
				$new_running_no++;
				$success = false;
			}else{
				$success = true;
			}
		}while(!$success);
		
		$upd = array();
		$upd['setting_name'] = 'member_eform_curr_running_no';
		$upd['setting_value'] = $new_running_no;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("replace into system_settings ".mysql_insert_by_field($upd));
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['new_card_no'] = $new_card_no;
		
		return $ret;
	}
}
?>