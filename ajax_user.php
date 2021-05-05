<?php
/*
10/22/2018 2:16 PM Justin
- Bug fixed on did not return the user ID when ajax does not provide username and password.
*/
include("include/common.php");

if (!$login) die("Session Timeout. Please Login");

class CHECK_USER extends Module{
	
	function __construct($title){		
		parent::__construct($title);
	}
	
	function _default(){
	    
	}
	
	function check_privilege(){
		global $sessioninfo, $appCore;
		
		$privilege_code = trim($_REQUEST['privilege_code']);
		$u = trim($_REQUEST['u']);
		$p = trim($_REQUEST['p']);
		$granted = 0;
		$ret = array();
		
		if($u && $p){
			// Check using username and password
			$result = $appCore->userManager->checkUserPrivilegeUsingLogin($u, $p, $privilege_code);
			if($result['err']){
				$ret['err'] = $result['err'];
			}elseif($result['ok'] && $result['user_id']){
				$ret['override_by_user_id'] = $result['user_id'];	// assign override user
				$granted = 1;
			}
		}else{
			// check current login user
			if($sessioninfo['privilege'][$privilege_code]){
				$ret['override_by_user_id'] = $sessioninfo['id'];
				$granted = 1;
			}
		}
		
		
		$ret['ok'] = 1;
		if($granted)	$ret['granted'] = 1;
		
		die(json_encode($ret));
	}
}

$CHECK_USER = new CHECK_USER('Check User');
?>