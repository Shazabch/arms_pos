<?php
/*

*/
include("include/common.php");
include("web_bridge.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('WB') || !privilege('WB_AR_TRANS_SETT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'WB/WB_AR_TRANS_SETT', BRANCH_CODE), "/index.php");
if(BRANCH_CODE != 'HQ')	js_redirect($LANG['HQ_ONLY'], "/index.php");

class WEB_BRIDGE_AR_TRANS_SETTINGS extends Module{

	function _default(){
		$this->load_ar_settings();
		
		$this->display();
	}
	
	private function load_ar_settings(){
		global $con, $smarty;
		
		$settings = load_ar_settings();
		//print_r($settings);
		$smarty->assign('form', $settings);
	}
	
	function save_settings(){
		global $con,$sessioninfo;
		
		//print_r($_REQUEST);
		
		$form = $_REQUEST['ar_settings'];
		foreach($form as $name=>$r){
			$upd = array();
			$upd = $r;
			
			$con->sql_query("replace into web_bridge_ar_settings ".mysql_insert_by_field($upd));
		}
		
		log_br($sessioninfo['id'], 'WEB_BRIDGE', '', "Update AR Settings");
		print "OK";
	}
}

$WEB_BRIDGE_AR_TRANS_SETTINGS = new WEB_BRIDGE_AR_TRANS_SETTINGS('Web Bridge: AR Trans Settings');
?>
