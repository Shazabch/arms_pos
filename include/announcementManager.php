<?php
/*
9/27/2018 3:15 PM Andy
- Add announcementManager.
*/

class announcementManager{
	// public var
	
	// private var
	var $announcementList = array();
	var $announcementListFilename = "announcement.data.php";
	
	function __construct(){
		global $smarty, $con, $appCore;

		
	}
	
	// function to get single announcement object
	// return array
	public function getAnnouncement($announcementCode){
		$this->loadAnnouncementList();
		return $this->announcementList[$announcementCode];
	}
	
	
	// function to check new announcement
	public function checkAnnouncement($params = array()){
		global $con, $smarty;
		
		$user_id = mi($params['user_id']);
		
		$this->loadAnnouncementList();
		
		if($user_id>0){	// Need to check which announcement is new for this user
			foreach($this->announcementList as $announcementCode => $r){
				// Check if user got read this announcement
				$this->announcementList[$announcementCode]['is_new'] = !$this->isAnnouncementOpened($user_id, $announcementCode);
			}
		}
		
		$smarty->assign('announcementList', $this->announcementList);
		return $this->announcementList;
	}
	
	// Check if user already read this announcement
	// return boolean
	public function isAnnouncementOpened($userID, $announcementCode){
		global $con;
		
		$userID = mi($userID);
		if(!$userID || !$announcementCode)	return false;
		
		$con->sql_query("select opened from user_announcement_status where user_id=$userID and code=".ms($announcementCode)." and opened=1");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $tmp ? true : false;
	}
	
	// function to update user already open this announcement
	// return null
	public function updateUserOpened($userID, $announcementCode){
		global $con;
		
		$userID = mi($userID);
		if(!$userID || !$announcementCode)	return false;
		
		$opened = $this->isAnnouncementOpened($userID, $announcementCode);
		if($opened)	return;	// Already open
		
		$upd = array();
		$upd['code'] = $announcementCode;
		$upd['user_id'] = $userID;
		$upd['opened'] = 1;
		$upd['added'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("replace into user_announcement_status ".mysql_insert_by_field($upd));
	}
	
	// Core function to load announcement list from file
	// return null
	private function loadAnnouncementList(){
		if(!$this->announcementList){
			include($_SERVER['DOCUMENT_ROOT']."/".$this->announcementListFilename);
			$this->announcementList = $announcementList;
		}		
	}
	
}
?>
