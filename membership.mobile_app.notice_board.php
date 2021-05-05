<?php
/*
8/20/2019 1:55 PM Andy
- Added new module "Notice Board Setup".

9/24/2019 2:42 PM Andy
- Fixed Add Notice Board Image checking.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP_MOBILE_NOTICE_SETUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_MOBILE_NOTICE_SETUP', BRANCH_CODE), "/index.php");
if (!$config['membership_mobile_settings']) js_redirect($LANG['NEED_CONFIG'], "/index.php");
$maintenance->check(406);

class MEMBERSHIP_MOBILE_NOTICE_BOARD extends Module{
	var $image_size = array(
		'min_width' => 320,
		'max_width' => 560,
		'min_height' => 100,
		'max_width' => 315,
	);
	var $folder = "attch/member_notice_board";
	
	function __construct($title, $template='')
	{
		global $smarty;
		
		$smarty->assign('image_size', $this->image_size);
		
		parent::__construct($title, $template='');
	}
	
	function _default(){
		global $con, $smarty, $appCore;
				
		
		$this->init_folder();
		$this->load_item_list();		
		$this->display();
	}
	
	function init_folder(){
		global $smarty;
		
		// Create Folder
		if(!file_exists($this->folder)){
			$success = check_and_create_dir($this->folder);
			if(!$success){
				$errmsg = 'Unable to Create Folder';
			}
		}
		
		if($errmsg){
			$smarty->assign("url", "/index.php");
		    $smarty->assign("title", $this->title);
		    $smarty->assign("subject", $errmsg);
		    $smarty->display("redir.tpl");
			exit;
		}
	}
	
	function load_item_list(){
		global $con, $smarty, $appCore;
		
		$item_list = $appCore->memberManager->getNoticeBoardItemList();
		$smarty->assign("item_list", $item_list);
	}
	
	function ajax_add_item(){
		global $con, $appCore, $smarty, $sessioninfo;
		
		$item_type = trim($_REQUEST['item_type']);
		if($item_type != 'image' && $item_type != 'video'){
			die("Invalid Item Type");
		}
		
		$con->sql_begin_transaction();
		
		// Get Max Sequence
		$con->sql_query("select max(sequence) as max_sequence from memberships_notice_board_items for update");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$upd = array();
		$upd['item_type'] = $item_type;
		$upd['sequence'] = $tmp['max_sequence']+1;
		$upd['added'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("insert into memberships_notice_board_items ".mysql_insert_by_field($upd));
		$item_id = $con->sql_nextid();
		
		log_br($sessioninfo['id'], 'MEMBERSHIP', $item_id, "New Item Added, ID#$item_id (Type: $item_type)");
		
		$item = $appCore->memberManager->getNoticeBoardItem($item_id);
		
		$con->sql_commit();
		
		$smarty->assign('item', $item);
		$smarty->assign('item_id', $item_id);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('membership.mobile_app.notice_board.item.tpl');
		
		print json_encode($ret);
	}
	
	function add_item_photo(){
		global $con, $appCore, $LANG, $sessioninfo;
		
		$item_id = mi($_REQUEST['item_id']);
		if(!$item_id)	$errmsg = "Invalid Item ID";

		if ($_FILES['image_file']['error'] == 0 && preg_match("/\.(jpg|jpeg|png|gif)$/i", $_FILES['image_file']['name'], $ext)){
			// No Error
		}elseif (!preg_match("/\.(jpg|jpeg|png|gif)$/i", $_FILES['image_file']['name'])){
			$errmsg = $LANG['POS_SETTINGS_INVALID_FORMAT'];
		}	
		else{
			$errmsg = $LANG['POS_SETTINGS_UPLOAD_ERROR'];
		}
		
		if(!$errmsg){
			$item_path = $this->folder."/".$item_id;
			$success = check_and_create_dir($item_path);
			if(!$success){
				$errmsg = 'Unable to Create Item Folder';
			}
		}
		
		// Got Error
		if($errmsg){
			print "<script>parent.window.upload_image_failed('$item_id', '$errmsg');</script>";
			exit;
		}
		
		$filepath = $item_path."/".$_FILES['image_file']['name'];
		
		move_uploaded_file($_FILES['image_file']['tmp_name'], $filepath);
		
		$upd = array();
		$upd['item_url'] = $filepath;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update memberships_notice_board_items set ".mysql_update_by_field($upd)." where id=$item_id");

		log_br($sessioninfo['id'], 'MEMBERSHIP', $item_id, "Update Image, ID#$item_id, Path: $filepath");
		
		print "<script>parent.window.upload_image_done('$item_id', '$filepath');</script>";
	}
	
	function ajax_update_video_url(){
		global $con, $appCore, $LANG, $sessioninfo;
		
		$item_id = mi($_REQUEST['item_id']);
		$video_site = trim($_REQUEST['video_site']);
		$video_link = trim($_REQUEST['video_link']);
		
		if(!$item_id)	die("Invalid Item ID");
		if(!$video_link)	die("Invalid Video ID");
		
		if($video_site == 'youtube'){
			$item_url = 'https://www.youtube.com/embed/'.$video_link;
		}else{
			die("Invalid Video Site");
		}
		
		$upd = array();
		$upd['item_url'] = $item_url;
		$upd['video_site'] = $video_site;
		$upd['video_link'] = $video_link;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update memberships_notice_board_items set ".mysql_update_by_field($upd)." where id=$item_id");
		
		log_br($sessioninfo['id'], 'MEMBERSHIP', $item_id, "Update Video, ID#$item_id, Path: $item_url");
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['item_url'] = $item_url;
		
		print json_encode($ret);
	}
	
	function ajax_update_image_click_link(){
		global $con, $appCore, $LANG, $sessioninfo;
		
		$item_id = mi($_REQUEST['item_id']);
		$image_click_link = trim($_REQUEST['image_click_link']);
		
		if(!$item_id)	die("Invalid Item ID");
		
		$upd = array();
		$upd['image_click_link'] = $image_click_link;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update memberships_notice_board_items set ".mysql_update_by_field($upd)." where id=$item_id");
		
		log_br($sessioninfo['id'], 'MEMBERSHIP', $item_id, "Update Image Click Link, ID#$item_id, Path: $image_click_link");
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
	
	function ajax_update_active(){
		global $con, $appCore, $LANG, $sessioninfo;
		
		$item_id = mi($_REQUEST['item_id']);
		$is_active = mi($_REQUEST['is_active']);
		
		if(!$item_id)	die("Invalid Item ID");
		
		$upd = array();
		$upd['active'] = $is_active;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update memberships_notice_board_items set ".mysql_update_by_field($upd)." where id=$item_id");
		
		log_br($sessioninfo['id'], 'MEMBERSHIP', $item_id, ($is_active ? 'Activate':'Deactivate')." Item, ID#$item_id");
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
	
	function ajax_remove_item(){
		global $con, $appCore, $LANG, $sessioninfo;
		
		$item_id = mi($_REQUEST['item_id']);
		
		if(!$item_id)	die("Invalid Item ID");
		
		$item = $appCore->memberManager->getNoticeBoardItem($item_id);
		if(!$item)	die("Item Not Found.");
		
		/*if($item['item_type'] == 'image'){
			if($item['item_url']){
				$folder_path = dirname($item['item_url']);
				die($folder_path);
			}
		}*/
		
		$con->sql_query("delete from memberships_notice_board_items where id=$item_id");
		log_br($sessioninfo['id'], 'MEMBERSHIP', $item_id, "Deleted Item, ID#$item_id");
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
	
	function ajax_move_item(){
		global $con, $appCore, $LANG, $sessioninfo;
		
		$item_id = mi($_REQUEST['item_id']);
		$direction = trim($_REQUEST['direction']);
		
		if(!$item_id)	die("Invalid Item ID");
		if($direction != 'up' && $direction != 'down')	die("Invalid Direction");
		
		$item = $appCore->memberManager->getNoticeBoardItem($item_id);
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
		$con->sql_query("select id, sequence from memberships_notice_board_items $str_filter $order_by limit 1");
		$swap_item = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$swap_item)	die("No Item to Swap.");
		
		$con->sql_begin_transaction();
		
		// Swap Sequence
		$upd = array();
		$upd['sequence'] = $swap_item['sequence'];
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update memberships_notice_board_items set ".mysql_update_by_field($upd)." where id=$item_id");
		
		$upd2 = array();
		$upd2['sequence'] = $item['sequence'];
		$upd2['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update memberships_notice_board_items set ".mysql_update_by_field($upd2)." where id=".mi($swap_item['id']));
		
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
		
	}
}

$MEMBERSHIP_MOBILE_NOTICE_BOARD = new MEMBERSHIP_MOBILE_NOTICE_BOARD("Notice Board Setup");
?>