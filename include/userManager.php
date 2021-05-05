<?php
/*
9/7/2016 6:05 PM Andy
- Add public userManager variable $removedPrivilegeList.

2/16/2017 11:54 AM Andy
- Add public function checkUserAllowLoginToBranch().

4/17/2018 2:36 PM Andy
- Add userManager function checkUserPrivilegeUsingLogin().

11/8/2019 2:03 PM Andy
- Added userManager function getUserByBarcode().

2/18/2020 11:21 AM Andy
- Fixed userManager function "getUserByBarcode" didn't filter user.locked.

2/6/2020 1:56 PM Andy
- Added userManager function "setUserProfilePhoto".
*/
class userManager{
	// public var
	var $removedPrivilegeList = array('POS_RETURN_POLICY');
	
	// private var
	
	function __construct(){
		global $smarty, $con, $appCore;

	
	}
	
	// function to check whether a user allow to reset document
	// return boolean
	public function isUserAllowToResetDocument($user_id){
		global $config;
		
		if(!$user_id)	return false;
		$user = $this->getUser($user_id);
				
		$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;

		if($user['level']<$required_level){
			return false;
		}
		
		return true;
	}
	
	// function to get user
	// return array $user
	public function getUser($user_id){
		global $con;
		
		$user_id = mi($user_id);
		if($user_id<=0)	return false;
		
		$con->sql_query("select user.*
			from user
			where user.id=$user_id");
		$user = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($user){
			$user['departments'] = unserialize($user['departments']);
			$user['vendors'] = unserialize($user['vendors']);
			$user['brands'] = unserialize($user['brands']);
			$user['allow_mprice'] = unserialize($user['allow_mprice']);
			$user['regions'] = unserialize($user['regions']);
		}
		
		return $user;
	}
	
	// function to get user
	// return array $user
	public function getUserByBarcode($user_barcode, $params = array()){
		global $con;
		
		$user_barcode = trim($user_barcode);
		if(!$user_barcode)	return false;
		
		$filter = array();
		$filter[] = "user.barcode=".ms($user_barcode);
		if(isset($params['active']))	$filter[] = "user.active=".mi($params['active']);
		if(isset($params['locked']))	$filter[] = "user.locked=".mi($params['locked']);
		$str_filter = "where ".join(' and ', $filter);
		
		$con->sql_query("select user.*
			from user
			$str_filter");
		$user = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($user){
			$user['departments'] = unserialize($user['departments']);
			$user['vendors'] = unserialize($user['vendors']);
			$user['brands'] = unserialize($user['brands']);
			$user['allow_mprice'] = unserialize($user['allow_mprice']);
			$user['regions'] = unserialize($user['regions']);
		}
		
		return $user;
	}
	
	// function to check whether the user is allow to login to the branch
	// return boolean
	public function checkUserAllowLoginToBranch($userID, $branchID, $check_remote = false){
		global $con, $LANG;
		
		if(!$userID || !$branchID)	return array('error'=>'No User ID or Branch ID');
		
		// check LOGIN
		$con->sql_query("select allowed from user_privilege where user_id = ".mi($userID)." and privilege_code = 'LOGIN' and branch_id=".mi($branchID));
		$pv = $con->sql_fetchrow();
		$con->sql_freeresult();
		
		if (!$pv) {
			return array('error'=>sprintf($LANG['NO_PRIVILEGE'], 'LOGIN', get_branch_code($branchID)));
		}
		
		if(!$check_remote)	return array('ok'=>1);
		
		// check LOGIN_REMOTE
		$con->sql_query("select allowed from user_privilege where user_id = ".mi($userID)." and privilege_code = 'LOGIN_REMOTE' and branch_id=".mi($branchID));
		$pv = $con->sql_fetchrow();
		$con->sql_freeresult();
				
		return $pv ? array('ok'=>1) : array('error'=>sprintf($LANG['NO_PRIVILEGE'], 'LOGIN_REMOTE', get_branch_code($branchID)));
	}
	
	// function to check user privilegeCode
	// return array
	public function checkUserPrivilegeUsingLogin($u, $p, $privilegeCode){
		global $con, $LANG;
		
		$u = trim($u);
		$p = trim($p);
		$privilegeCode = trim($privilegeCode);
		
		if(!u || !$p || !$privilegeCode)	return array('err'=>$LANG['INVALID_LOGIN_TRY_AGAIN']);
		
		$con->sql_query("select user.id
		from user
		join user_privilege up on up.user_id=user.id and up.privilege_code=".ms($privilegeCode)."
		where user.active=1 and user.locked=0 and user.template=0 and user.u=".ms($u)." and p=md5(".ms($p).")");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($tmp){
			return array('ok'=>1, 'user_id' => $tmp['id']);
		}
		return array('err'=>$LANG['INVALID_LOGIN_TRY_AGAIN']);
	}
	
	/*public function setUserProfilePhoto($user_id, $file_obj, $params = array()){
		global $con, $appCore, $LANG;
		
		$result = $appCore->isValidUploadImageFile($file_obj);
		if(!$result['ok'])	return $result;
		$ext = trim($result['ext']);
		if(!$ext)	return array("error" => 'Invalid File Extension');
		
		$user_id = mi($user_id);
		if($user_id<=0){
			return array('error' => sprintf($LANG['INVALID_DATA'], "user_id", $user_id));
		}
		
		$log_user_id = mi($params['log_user_id']);
		if(!$log_user_id)	$log_user_id = 1;
		
		// Get User
		$user = $this->getUser($user_id);
		if(!$user){
			return array('error' => sprintf($LANG['INVALID_DATA'], "user_id", $user_id));
		}
		
		// Already have url
		if($user['profile_photo_url']){
			// Rename extension
			$profile_photo_url = preg_replace("/\.(jpg|jpeg|png|gif)$/i", ".".$ext, $user['profile_photo_url']);
			
			// Move Uploaded File
			if(!move_uploaded_file($file_obj['tmp_name'], $profile_photo_url)){
				return array('error' => "Failed to Move Uploaded File.");
			}
			
			if($user['profile_photo_url'] != $profile_photo_url){
				// Delete old image
				unlink($user['profile_photo_url']);
			}
		}else{
			// Generate New URL
			//print ($tmp_str);
			$folder_1 = $user_id;
			//print "-$folder_1-$folder_2";exit;
			
			$directory = "attch/user_profile_image";
			if(!check_and_create_dir($directory)){
				return array('error' => "Create Image Folder Failed");
			}
			
			$directory .= "/".$folder_1;
			if(!check_and_create_dir($directory)){
				return array('error' => "Create Image Folder Failed");
			}
			
			$profile_photo_url = $directory."/".$appCore->newGUID().".".$ext;
			
			// Move Uploaded File
			if(!move_uploaded_file($file_obj['tmp_name'], $profile_photo_url)){
				return array('error' => "Failed to Move Uploaded File.");
			}
		}
		
		$upd = array();
		$upd['profile_photo_url'] = $profile_photo_url;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update user set ".mysql_update_by_field($upd)." where id=".ms($user_id));
		
		log_br($log_user_id, 'USER PROFILE', $user_id, "Updated User Profile Image, User: ".$user['u']." url: $profile_photo_url");
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['profile_photo_url'] = $profile_photo_url;
		return $ret;
	}
	
	function setUserFingerPrint($user_id, $params){
		global $con, $appCore, $LANG;
		
		// User ID
		$user_id = mi($user_id);
		if($user_id<=0){
			return array('error' => sprintf($LANG['INVALID_DATA'], "user_id", $user_id));
		}
		
		// Finger Print
		$fingerprint1 = trim($params['fingerprint1']);
		$fingerprint2 = trim($params['fingerprint2']);
		$fingerprint3 = trim($params['fingerprint3']);
		$fingerprint4 = trim($params['fingerprint4']);
		
		if(!$fingerprint1)	return array('error' => sprintf($LANG['INVALID_DATA'], "fingerprint1", $fingerprint1));
		if(!$fingerprint2)	return array('error' => sprintf($LANG['INVALID_DATA'], "fingerprint2", $fingerprint2));
		if(!$fingerprint3)	return array('error' => sprintf($LANG['INVALID_DATA'], "fingerprint3", $fingerprint3));
		if(!$fingerprint4)	return array('error' => sprintf($LANG['INVALID_DATA'], "fingerprint4", $fingerprint4));
		
		// Get User
		$user = $this->getUser($user_id);
		if(!$user){
			return array('error' => sprintf($LANG['INVALID_DATA'], "user_id", $user_id));
		}
		
		$log_user_id = mi($params['log_user_id']);
		if(!$log_user_id)	$log_user_id = 1;
		
		// Get Current Data
		$con->sql_query("select * from user_finger_print where user_id=$user_id");
		$curr_data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$upd = array();
		$upd['fingerprint1'] = $fingerprint1;
		$upd['fingerprint2'] = $fingerprint2;
		$upd['fingerprint3'] = $fingerprint3;
		$upd['fingerprint4'] = $fingerprint4;
		
		// Allowed Time Attendance
		if($params['allowed_time_attendance_branch']){
			if($curr_data){
				$curr_data['allowed_time_attendance_branch'] = unserialize($curr_data['allowed_time_attendance_branch']);
				$upd['allowed_time_attendance_branch'] = $curr_data['allowed_time_attendance_branch'];
			}else{
				$upd['allowed_time_attendance_branch'] = array();
			}
			foreach($params['allowed_time_attendance_branch'] as $tmp_bid => $allowed){
				if($allowed){
					$upd['allowed_time_attendance_branch'][$tmp_bid] = 1;
				}else{
					unset($upd['allowed_time_attendance_branch'][$tmp_bid]);
				}
				
			}
			$upd['allowed_time_attendance_branch'] = serialize($upd['allowed_time_attendance_branch']);
		}
			
		if($curr_data){
			// Update
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("update user_finger_print set ".mysql_update_by_field($upd)." where user_id=$user_id");
		}else{
			// Insert
			$upd['user_id'] = $user_id;
			$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into user_finger_print ".mysql_insert_by_field($upd));
		}
		$con->sql_query("update user set last_update=CURRENT_TIMESTAMP where id=$user_id");
		
		log_br($log_user_id, 'USER PROFILE', $user_id, "Updated User Finger Print, User: ".$user['u']);
		
		$ret = array();
		$ret['ok'] = 1;
		return $ret;
	}*/
}
?>
