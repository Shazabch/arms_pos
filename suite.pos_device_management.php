<?php
/*
4/16/2021 3:00 PM William
- Enhanced pos device banner screen allow to add video.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!$config['enable_suite_device'])	js_redirect($LANG['NEED_CONFIG'], "/index.php");
if (!privilege('SUITE_POS_DEVICE_MANAGEMENT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SUITE_POS_DEVICE_MANAGEMENT', BRANCH_CODE), "/index.php");
$maintenance->check(496);

class POS_DEVICE_MANAGEMENT extends Module{
	var $BannerInfo = array(
		'logo'=> array('maximun_qty'=> 1, 'allow_upload_image'=>true),
		'slideshow_vertical'=> array('allow_upload_video'=>true, 'allow_upload_image'=>true)
	);
	function __construct($title, $template= ''){
		parent::__construct($title, $template='');
	}
	
	function _default(){
		global $con, $smarty, $appCore;
		
		// Select Main Screen by Default
		if(!isset($_REQUEST['screen_name'])){
			$_REQUEST['screen_name'] = 'slideshow';
		}
		
		// Load All Banner List
		$banner_list = $this->getBannerList();
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
		$smarty->assign('screen_list', $screen_list);
		
		$this->display();
	}

	function ajax_load_screen_banner(){
		global $con, $smarty, $appCore;
		
		$screen_name = trim($_REQUEST['screen_name']);
		if(!$screen_name)	die("Invalid Screen.");
		
		$banner_list = $this->getBannerList($screen_name);
		$smarty->assign('banner_list', $banner_list);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('suite.pos_device_management.table.tpl');
		
		print json_encode($ret);
	}
	
	//get banner list
	function getBannerList($screen_name=''){
		global $con;
		
		$bannerList = array();
		$filter = array();
		$filter[] = "spb.active=1";
		if($screen_name)	$filter[] = "spb.screen_name=".ms($screen_name);
		
		$str_filter = 'where '.join(' and ', $filter);
		$con->sql_query("select spb.*
			from suite_pos_banner spb
			$str_filter
			order by spb.screen_name, spb.sequence");
		while($r = $con->sql_fetchassoc()){
			$r['banner_info'] = unserialize($r['banner_info']);
			
			$bannerList[$r['banner_name']] = $r;
		}
		$con->sql_freeresult();
		
		return $bannerList;
	}
	
	function getSuitePosBanner($banner_name){
		global $con;
		
		$filter = array();
		$filter[] = "spb.banner_name=".ms($banner_name);
		
		$str_filter = 'where '.join(' and ', $filter);
		$con->sql_query("select spb.*
			from suite_pos_banner spb
			$str_filter");
		$banner = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($banner){
			$banner['banner_info'] = unserialize($banner['banner_info']);
		}
		return $banner;
	}
	
	function ajax_add_item(){
		global $con, $appCore, $smarty, $sessioninfo;
		
		$banner_name = trim($_REQUEST['banner_name']);
		$item_type = trim($_REQUEST['item_type']);
		if($item_type != 'image' && $item_type != 'video'){
			die("Invalid Item Type");
		}
		
		$con->sql_begin_transaction();
		
		// Get Max Sequence
		$con->sql_query("select max(sequence) as max_sequence from suite_pos_banner_items where banner_name=".ms($banner_name)." for update");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$upd = array();
		$upd['banner_name'] = $banner_name;
		$upd['item_type'] = $item_type;
		$upd['sequence'] = $tmp['max_sequence']+1;
		$upd['added'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("insert into suite_pos_banner_items ".mysql_insert_by_field($upd));
		$item_id = $con->sql_nextid();
		
		log_br($sessioninfo['id'], 'POS_DEVICE_MANAGEMENT', $item_id,  "New Item Added, ID#$item_id (Banner Name: $banner_name)");
		
		$item = $this->get_banner_item($banner_name, $item_id);
		
		$con->sql_commit();
		
		$smarty->assign('item', $item);
		$smarty->assign('item_id', $item_id);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('suite.pos_device_management.item.tpl');
		
		print json_encode($ret);
	}
	
	function get_banner_item($banner_name, $item_id){
		global $con;
		
		$con->sql_query("select * from suite_pos_banner_items where id=".mi($item_id)." and banner_name=".ms($banner_name));
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $data;
	}
	
	function load_banner_item_list($banner_name){
		global $con, $smarty;
		
		$filter = array();
		if(isset($banner_name))	$filter[] = "banner_name=".ms($banner_name);
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		$item_list = array();
		$con->sql_query("select *
			from suite_pos_banner_items
			$str_filter
			order by sequence");
		while($r = $con->sql_fetchassoc()){
			$r['str_last_update'] = strtotime($r['last_update']);
			$item_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		$smarty->assign("item_list", $item_list);
	}
	
	function open(){
		global $con, $smarty, $appCore;
		
		$banner_name = trim($_REQUEST['banner_name']);
		$banner = $this->getSuitePosBanner($banner_name);
		if(!$banner){
			js_redirect("Banner Not Found", "/index.php");
		}
		
		$this->load_banner_item_list($banner_name);
		$smarty->assign('banner_info', $this->BannerInfo[$banner_name]);
		$smarty->assign('banner', $banner);
		$smarty->display('suite.pos_device_management.open.tpl');
	}
	
	function add_item_photo(){
		global $con, $smarty, $LANG, $sessioninfo, $appCore; 
		
		$banner_name = trim($_REQUEST['banner_name']);
		$item_id = mi($_REQUEST['item_id']);
		
		$errmsg = '';
		
		if(!$banner_name)	$errmsg = 'Invalid Banner Name';
		if(!$item_id)	$errmsg = "Invalid Item ID";
		
		// Get Banner
		$banner = $this->getSuitePosBanner($banner_name);
		
		// Create Folder
		$folder = "attch/suite_pos_banner";
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
			// Check File Error
			if ($_FILES['image_file']['error'] == 0 && preg_match("/\.(jpg|jpeg|png|gif)$/i",$_FILES['image_file']['name'], $ext)){
				$filename = $item_id.$ext[0];
				$final_path = $folder."/".$filename;
				
				// Move File to Actual Folder
				if(move_uploaded_file($_FILES['image_file']['tmp_name'], $final_path)){
					$file_uploaded = true;
				}else{
					$file_uploaded = false;
				}
				
				if($file_uploaded){
					$upd = array();
					$upd['item_url'] = $final_path;
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("update suite_pos_banner_items set ".mysql_update_by_field($upd)." where id=$item_id");
				}
			}elseif (!preg_match("/\.(jpg|jpeg|png|gif)$/i", $_FILES['image_file']['name'])){
				$errmsg = $LANG['POS_SETTINGS_INVALID_FORMAT'];
			}	
			else{
				$errmsg = $LANG['POS_SETTINGS_UPLOAD_ERROR'];
			}
		}
		
		if($errmsg || !$file_uploaded){
			print "<script>parent.UPLOAD_IMAGE_DIALOG.upload_image_failed('$item_id', '$errmsg');</script>";
			exit;
		}else{
			print "<script>parent.UPLOAD_IMAGE_DIALOG.upload_image_done('$item_id', '$final_path?t=".time()."');</script>";
		}
	}
	
	function add_item_video(){
		global $con, $smarty, $LANG, $sessioninfo, $appCore; 
		
		$banner_name = trim($_REQUEST['banner_name']);
		$item_id = mi($_REQUEST['item_id']);
		
		$errmsg = '';
		
		if(!$banner_name)	$errmsg = 'Invalid Banner Name';
		if(!$item_id)	$errmsg = "Invalid Item ID";
		
		// Get Banner
		$banner = $this->getSuitePosBanner($banner_name);
		
		//
		$banner_item = $this->get_banner_item($banner_name, $item_id);
		
		// Create Folder
		$folder = "attch/suite_pos_banner";
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
			// Check File Error
			if ($_FILES['video_file']['error'] == 0 && preg_match("/\.(mp4|avi|webm|ogv)$/i",$_FILES['video_file']['name'], $ext)){
				$filename = $item_id.$ext[0];
				$final_path = $folder."/".$filename;
				
				// Move File to Actual Folder
				if(move_uploaded_file($_FILES['video_file']['tmp_name'], $final_path)){
					$file_uploaded = true;
				}else{
					$file_uploaded = false;
				}
				
				if($file_uploaded){
					$upd = array();
					$upd['item_url'] = $final_path;
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("update suite_pos_banner_items set ".mysql_update_by_field($upd)." where id=$item_id");
					
					if($banner_item['item_url'] && $banner_item['item_url'] != $final_path){
						unlink($banner_item['item_url']);
					}
				}
			}elseif (!preg_match("/\.(mp4|avi|webm|ogv)$/i", $_FILES['video_file']['name'])){
				$errmsg = $LANG['POS_SETTINGS_INVALID_FORMAT'];
			}	
			else{
				$errmsg = $LANG['POS_SETTINGS_UPLOAD_ERROR'];
			}
		}
		
		if($errmsg || !$file_uploaded){
			print "<script>parent.EDIT_VIDEO_DIALOG.upload_video_failed('$errmsg');</script>";
			exit;
		}else{
			print "<script>parent.EDIT_VIDEO_DIALOG.upload_video_done('$item_id', '$final_path?".time()."');</script>";
		}
	}
	
	function ajax_remove_item(){
		global $con, $appCore, $LANG, $sessioninfo;
		
		$banner_name = trim($_REQUEST['banner_name']);
		$item_id = mi($_REQUEST['item_id']);
		
		if(!$item_id)	die("Invalid Item ID");
		if(!$banner_name)	die("Invalid Banner Name");
		
		$item = $this->get_banner_item($banner_name, $item_id);
		if(!$item)	die("Item Not Found.");
		
		//delete file
		if($item['item_url']){
			unlink($item['item_url']);
		}
		
		$con->sql_query("delete from suite_pos_banner_items where banner_name=".ms($banner_name)." and id=$item_id");
		log_br($sessioninfo['id'], 'POS_DEVICE_MANAGEMENT', $item_id,  "Deleted Banner#$banner_name Item, ID#$item_id");
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
	
	function ajax_move_item(){
		global $con, $appCore, $LANG, $sessioninfo;
		
		$banner_name = trim($_REQUEST['banner_name']);
		$item_id = mi($_REQUEST['item_id']);
		$direction = trim($_REQUEST['direction']);
		
		if(!$banner_name)	die("Invalid Banner Item.");
		if(!$item_id)	die("Invalid Item ID");
		if($direction != 'up' && $direction != 'down')	die("Invalid Direction");
		
		$item = $this->get_banner_item($banner_name, $item_id);
		if(!$item)	die("Item Not Found.");
		
		$curr_sequence = mi($item['sequence']);
		
		$filter = array();
		if($direction == 'up'){
			$filter[] = "sequence < $curr_sequence";
			$order_by = " order by sequence desc";
		}else{
			$filter[] = "sequence > $curr_sequence";
			$order_by = " order by sequence";
		}
		$str_filter = "where ".join(' and ', $filter);
		
		// Get previous item
		$con->sql_query("select id, sequence from suite_pos_banner_items $str_filter $order_by limit 1");
		$swap_item = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$swap_item)	die("No Item to Swap.");
		
		$con->sql_begin_transaction();
		
		// Swap Sequence
		$upd = array();
		$upd['sequence'] = $swap_item['sequence'];
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update suite_pos_banner_items set ".mysql_update_by_field($upd)." where id=$item_id");
		
		$upd2 = array();
		$upd2['sequence'] = $item['sequence'];
		$upd2['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update suite_pos_banner_items set ".mysql_update_by_field($upd2)." where id=".mi($swap_item['id']));
		
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
		
	}
	
	function ajax_update_banner_item_link(){
		global $con, $smarty, $LANG, $sessioninfo, $appCore; 
		
		$banner_name = trim($_REQUEST['banner_name']);
		$item_id = mi($_REQUEST['item_id']);
		$image_click_link = trim($_REQUEST['image_click_link']);
		
		if(!$banner_name)   die("Invalid Banner Name");
		if(!$item_id)	die("Invalid Item ID");
		
		
		$upd = array();
		$upd['image_click_link'] = $image_click_link;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update suite_pos_banner_items set ".mysql_update_by_field($upd)." where banner_name=".ms($banner_name)." and id=$item_id");
		
		log_br($sessioninfo['id'], 'POS_DEVICE_MANAGEMENT', $item_id, "Update Image Click Link, ID#$item_id, Path: $image_click_link");
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}

	
	function ajax_update_active(){
		global $con, $appCore, $LANG, $sessioninfo;
		
		$banner_name = trim($_REQUEST['banner_name']);
		$item_id = mi($_REQUEST['item_id']);
		$is_active = mi($_REQUEST['is_active']);
		
		if(!$banner_name)   die("Invalid Banner Name");
		if(!$item_id)	die("Invalid Item ID");
		
		$upd = array();
		$upd['active'] = $is_active;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update suite_pos_banner_items set ".mysql_update_by_field($upd)." where banner_name=".ms($banner_name)." and id=$item_id");
		
		log_br($sessioninfo['id'], 'POS_DEVICE_MANAGEMENT', $item_id, ($is_active ? 'Activate':'Deactivate')." Item, ID#$item_id");
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
}

$POS_DEVICE_MANAGEMENT = new POS_DEVICE_MANAGEMENT('POS Device Management');
?>