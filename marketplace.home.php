<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MARKETPLACE_LOGIN')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MARKETPLACE_LOGIN', BRANCH_CODE), "/index.php");
$maintenance->check(426);

class MKTPLACE_LOGIN_HOME extends Module{
	var $safe_key = 'mkt20191121';
	
	function __construct($title){
		global $con, $smarty;
		
		//$this->init_load();
		
		parent::__construct($title);
	}
	
	function _default(){
		
	    $this->display();
	}
	
	function goto_marketplace(){
		global $con, $appCore, $sessioninfo, $config;
		
		$marketplace_url = trim($config['arms_marketplace_settings']['marketplace_url']);
		if(!$marketplace_url)	die("Marketplace URL Not Found.");
				
		// Create Login Code
		$code = md5($sessioninfo['id'].time().$this->safe_key);
		//print $code;
		
		$upd = array();
		$upd['user_id'] = $sessioninfo['id'];
		$upd['code'] = $code;
		$upd['added'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("replace into marketplace_login_data ".mysql_insert_by_field($upd));
		
		log_br($sessioninfo['id'], 'MARKETPLACE', $sessioninfo['id'], "Attemp to Login Marketplace");
		
		$marketplace_url .= "/arms_login?user_id=".mi($sessioninfo['id'])."&code=".$code;
		//print $marketplace_url;
		header("Location: $marketplace_url");
		exit;
	}
}

$MKTPLACE_LOGIN_HOME = new MKTPLACE_LOGIN_HOME('Marketplace Home');
?>