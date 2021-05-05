<?php
/*
7/2/2019 4:55 PM Andy
- Added new module "Membership Mobile Advertisement Setup".
-
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP_MOBILE_ADS_SETUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_MOBILE_ADS_SETUP', BRANCH_CODE), "/index.php");
if (!$config['membership_mobile_settings']) js_redirect($LANG['NEED_CONFIG'], "/index.php");
//ini_set("display_errors", 0);

//die("Promotion is currently under maintenance, please come back later.");

class MEMBERSHIP_MOBILE_ADS extends Module{
	
	function __construct($title, $template='')
	{
		parent::__construct($title, $template='');
	}
	
	function _default(){
		global $con, $smarty, $appCore;
		
		// Select Main Screen by Default
		if(!isset($_REQUEST['screen_name'])){
			$_REQUEST['screen_name'] = 'main';
		}
		
		// Load All Banner List
		$banner_list = $appCore->memberManager->getMemberMobileAdsBannerList();
		$screen_list = array();
		if($banner_list){
			foreach($banner_list as $banner_name => $r){
				$screen_name = trim($r['screen_name']);
				
				if(!isset($screen_list[$screen_name])){
					$screen_list[$screen_name]['screen_name'] = $screen_name;
					$screen_list[$screen_name]['screen_description'] = $r['screen_description'];
				}
				
				$screen_list[$screen_name]['banner_list'][$r['banner_name']] = $r;
			}
		}
		//print_r($screen_list);
		$smarty->assign('screen_list', $screen_list);
		
		$this->display();
	}
	
	function ajax_load_screen_banner(){
		global $con, $smarty, $appCore;
		
		$screen_name = trim($_REQUEST['screen_name']);
		if(!$screen_name)	die("Invalid Screen.");
		$banner_list = $appCore->memberManager->getMemberMobileAdsBannerList($screen_name);
		//print_r($banner_list);
		$smarty->assign('banner_list', $banner_list);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('membership.mobile_app.ads.table.tpl');
		
		print json_encode($ret);
	}
	
	function open(){
		global $con, $smarty, $appCore;
		
		//print_r($_REQUEST);
		
		$banner_name = trim($_REQUEST['banner_name']);
		$banner = $appCore->memberManager->getMemberMobileAdsBanner($banner_name);
		if(!$banner){
			js_redirect("Banner Not Found", "/index.php");
		}
		
		//print_r($banner);
		$smarty->assign('banner', $banner);
		$smarty->display('membership.mobile_app.ads.open.tpl');
	}
	
	function upload_banner_photo(){
		global $con, $smarty, $LANG, $sessioninfo, $appCore; 
		
		$banner_name = trim($_REQUEST['banner_name']);
		$banner_num = mi($_REQUEST['banner_num']);
		
		$errmsg = '';
		
		if(!$banner_name)	$errmsg = 'Invalid Banner Name';
		if($banner_num<=0)	$errmsg = 'Invalid Banner Number';
		
		// Get Banner
		$banner = $appCore->memberManager->getMemberMobileAdsBanner($banner_name);
		
		// Create Folder
		$folder = "attch/member_ads";
		if(!file_exists($folder)){
			$success = check_and_create_dir($folder);
			if(!$success){
				$errmsg = 'Unable to Create Banner Folder';
			}
		}
		
		if(!$errmsg){
			// Folder by Banner Name
			$folder = $folder."/".$banner_name;
			if(!file_exists($folder)){
				$success = check_and_create_dir($folder);
				if(!$success){
					$errmsg = 'Unable to Create Banner Sub Folder';
				}
			}
		}
		
		if(!$errmsg){
			$fname = 'banner';
		
			// Check File Error
			if ($_FILES[$fname]['error'] == 0 && preg_match("/\.(jpg|jpeg|png|gif)$/i",$_FILES[$fname]['name'], $ext)){
				$filename = $banner_num.$ext[0];
				$final_path = $folder."/".$filename;
				
				// Move File to Actual Folder
				if(move_uploaded_file($_FILES[$fname]['tmp_name'], $final_path)){
					$file_uploaded = true;
				}
				else{
					$file_uploaded = false;
				}
				
				// Call Back
				if($file_uploaded){
					$banner['banner_info']['banner_list'][$banner_num]['path'] = $final_path;
					$upd = array();
					$upd['banner_info'] = serialize($banner['banner_info']);
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("update membership_mobile_ads_banner set ".mysql_update_by_field($upd)." where banner_name=".ms($banner_name));
					
					log_br($sessioninfo['id'], 'MEMBERSHIP', 0, "Banner ($banner_name) $banner[banner_description], Photo #$banner_num Uploaded");
				
					// Delete other file with same name but different extension
					foreach(glob("$folder/".$banner_num.".*") as $f){
						if(basename($f) != $filename){
							unlink($f);
						}
					}
					
					
				}else{
					$errmsg = $LANG['POS_SETTINGS_CANT_MOVE_FILE'];
				}
			}elseif (!preg_match("/\.(jpg|jpeg|png|gif)$/i", $_FILES[$fname]['name'])){
				$errmsg = $LANG['POS_SETTINGS_INVALID_FORMAT'];
			}	
			else{
				$errmsg = $LANG['POS_SETTINGS_UPLOAD_ERROR'];
			}
		}
		
		//print_r($_FILES);
		if($errmsg || !$file_uploaded){
			print "<script>parent.alert('$errmsg');parent.BANNER_EDIT.banner_uploaded_failed('$banner_num');</script>";
		}else{
			print "<script>parent.BANNER_EDIT.banner_uploaded('$banner_num', '$final_path?".time()."');</script>";
		}
	}
	
	function ajax_delete_banner(){
		global $con, $smarty, $LANG, $sessioninfo, $appCore; 
		
		$banner_name = trim($_REQUEST['banner_name']);
		$banner_num = mi($_REQUEST['banner_num']);
		
		$errmsg = '';
		
		if(!$banner_name)	die('Invalid Banner Name');
		if($banner_num<=0)	die('Invalid Banner Number');
		
		$folder = "attch/member_ads/$banner_name";
		if(!file_exists($folder)){
			die("Folder Not Found");
		}
		
		// Get Banner
		$banner = $appCore->memberManager->getMemberMobileAdsBanner($banner_name);
		
		// Delete file
		foreach(glob("$folder/".$banner_num.".*") as $f){
			unlink($f);
		}
		
		unset($banner['banner_info']['banner_list'][$banner_num]);
		$upd = array();
		$upd['banner_info'] = serialize($banner['banner_info']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update membership_mobile_ads_banner set ".mysql_update_by_field($upd)." where banner_name=".ms($banner_name));
					
		log_br($sessioninfo['id'], 'MEMBERSHIP', 0, "Banner ($banner_name) $banner[banner_description], Photo #$banner_num Removed");
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_update_banner_link(){
		global $con, $smarty, $LANG, $sessioninfo, $appCore; 
		
		$banner_name = trim($_REQUEST['banner_name']);
		$banner_num = mi($_REQUEST['banner_num']);
		$banner_link = trim($_REQUEST['banner_link']);
		
		if(!$banner_name)	die('Invalid Banner Name');
		if($banner_num<=0)	die('Invalid Banner Number');
		
		// Get Banner
		$banner = $appCore->memberManager->getMemberMobileAdsBanner($banner_name);
		
		$banner['banner_info']['banner_list'][$banner_num]['link'] = $banner_link;
		
		$upd = array();
		$upd['banner_info'] = serialize($banner['banner_info']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update membership_mobile_ads_banner set ".mysql_update_by_field($upd)." where banner_name=".ms($banner_name));
					
		log_br($sessioninfo['id'], 'MEMBERSHIP', 0, "Banner ($banner_name) $banner[banner_description], Link Updated");
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
}

$MEMBERSHIP_MOBILE_ADS = new MEMBERSHIP_MOBILE_ADS("Membership Mobile Advertisement Setup");
?>