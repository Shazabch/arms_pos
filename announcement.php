<?php
include("include/common.php");

class ANNOUNCEMENT extends Module{
	
	function __construct($title){
		
		parent::__construct($title);
	}
	
	function _default(){
		
	}
	
	function view(){
		global $appCore, $sessioninfo, $smarty;
		
		$code = trim($_REQUEST['code']);
		
		// Get Announcement Data
		$form = $appCore->announcementManager->getAnnouncement($code);
		
		// Not Found
		if(!$form){
		    $smarty->assign("url", "/index.php");
		    $smarty->assign("title", "Announcement Error");
		    $smarty->assign("subject", "The Announcement you request is no longer exists");
		    $smarty->display("redir.tpl");
		    exit;
		}
		
		// Update as Opened
		$appCore->announcementManager->updateUserOpened($sessioninfo['id'], $code);
		
		if($form['url']){	// This is external URL
			header("Location: ".$form['url']);
			exit;
		}
		
		$smarty->assign('form', $form);
		$smarty->display('announcement.view.tpl');
	}
}

$ANNOUNCEMENT = new ANNOUNCEMENT('Announcement');
?>