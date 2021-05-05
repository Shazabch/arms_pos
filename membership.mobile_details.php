<?php
/*
7/31/2019 2:25 PM Andy
- Added Module "Member Mobile App Details".

9/24/2019 2:56 PM Andy
- Added "Profile Photo".
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_EDIT', BRANCH_CODE), "/index.php");
$maintenance->check(410);

class MEMBER_MOBILE_DETAILS extends Module{
	
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
		$this->load_member_mobile_details($nric);
		
		$this->display();
	}
	
	private function load_member_mobile_details($nric){
		global $con, $smarty, $appCore;
		
		$nric = trim($_REQUEST['nric']);
		if(!$nric)	display_redir("/index.php", $this->title, $LANG['MEMBERSHIP_NRIC_NOT_IN_DATABASE']);
		
		// get member by NRIC only
		$member = $appCore->memberManager->getMember($nric, false);
		if(!member)	display_redir("/index.php", $this->title, $LANG['MEMBERSHIP_NRIC_NOT_IN_DATABASE']);
		
		// Get member mobile list
		$member_app_session = array();
		$q1 = $con->sql_query("select * from member_app_session where nric=".ms($nric)." order by last_access desc");
		while($r = $con->sql_fetchassoc($q1)){
			$member_app_session[] = $r;
		}
		$con->sql_freeresult($q1);
		
		//print_r($member_app_session);
		
		$smarty->assign('member', $member);
		$smarty->assign('member_app_session', $member_app_session);
	}
	
	function ajax_change_pass(){
		global $con, $smarty, $appCore, $sessioninfo;
		
		$nric = trim($_REQUEST['nric']);
		$new_p = trim($_REQUEST['new_p']);
		$new_p2 = trim($_REQUEST['new_p2']);
		
		if(!$nric)	die("Invalid NRIC");
		if(!$new_p || !$new_p2)	die("Invalid Password");
		if($new_p != $new_p2){
			die("Password Not Match");
		}
		
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		$result = $appCore->memberManager->changeMemberMobilePassword($nric, $new_p, $params);
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function upload_profile_image(){
		global $con, $appCore, $LANG, $sessioninfo;
		
		$nric = trim($_REQUEST['nric']);
		if(!$nric)	$errmsg = "Invalid NRIC";

		// No File was Uploaded
		if(!isset($_FILES['profile_image']))	$errmsg = "Image Not Found";
		
		// Set Image
		$result = $appCore->memberManager->setMemberProfileImage($nric, $_FILES['profile_image']);
		if(!$result['ok']){
			$errmsg = $result['error'] ? $result['error'] : "Unknown Error";
		}else{
			$image_url = $result['image_url'];
		}
		
		// Got Error
		if($errmsg){
			print "<script>parent.window.UPLOAD_PROFILE_IMAGE_DIALOG.upload_image_failed('$errmsg');</script>";
			exit;
		}
		
		log_br($sessioninfo['id'], 'MEMBERSHIP', 0, "Update Profile Image, NRIC: $nric, Path: $image_url");
		
		print "<script>parent.window.UPLOAD_PROFILE_IMAGE_DIALOG.upload_image_done('$image_url');</script>";
	}
}

$MEMBER_MOBILE_DETAILS = new MEMBER_MOBILE_DETAILS('Member Mobile Details');
?>