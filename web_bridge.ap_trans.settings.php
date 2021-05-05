<?php
/*

*/
include("include/common.php");
include("web_bridge.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('WB') || !privilege('WB_AP_TRANS_SETT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'WB/WB_AP_TRANS_SETT', BRANCH_CODE), "/index.php");
if(BRANCH_CODE != 'HQ')	js_redirect($LANG['HQ_ONLY'], "/index.php");

class WEB_BRIDGE_AP_TRANS_SETTINGS extends Module{
	
	function _default(){
		$this->load_ap_settings();
		
		$this->display();
	}
	
	private function load_ap_settings(){
		global $con, $smarty;
		
		$ap_settings = load_ap_settings();
		//print_r($ap_settings);
		$smarty->assign('form', $ap_settings);
	}
	
	function save_settings(){
		global $con,$sessioninfo;
		
		//print_r($_REQUEST);
		
		$form = $_REQUEST['ap_settings'];
		foreach($form as $name=>$r){
			$upd = array();
			$upd = $r;
			$con->sql_query("replace into web_bridge_ap_settings ".mysql_insert_by_field($upd));
		}
		
		log_br($sessioninfo['id'], 'WEB_BRIDGE', '', "Update AP Settings");
		print "OK";
	}
}

$WEB_BRIDGE_AP_TRANS_SETTINGS = new WEB_BRIDGE_AP_TRANS_SETTINGS('Web Bridge: AP Trans Settings');

?>
