<?php
/*
10/21/2013 10:41 AM Justin
- Enhanced to load custom payment type.
- Enhanced the payment types to load from PHP instead of hardcoded.

3/20/2014 5:34 PM Justin
- Modified to change the wording from "Check" to "Cheque".
*/
include("include/common.php");
include("web_bridge.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('WB') || !privilege('WB_CC_TRANS_SETT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'WB/WB_CC_TRANS_SETT', BRANCH_CODE), "/index.php");
if(BRANCH_CODE != 'HQ')	js_redirect($LANG['HQ_ONLY'], "/index.php");

class WEB_BRIDGE_CC_TRANS_SETTINGS extends Module{

	function _default(){
		global $con, $smarty;
		
		$this->load_cc_settings();
		
		// default payment type
		$payment_type = array("cash" => "Cash", "cc" => "Credit Card", "coupon" => "Coupon", "voucher" => "Voucher", "check" => "Cheque");

		// load custom payment type from pos settings
		$q1 = $con->sql_query("select * from pos_settings where branch_id = 1 and setting_name = 'payment_type'");
		$ps_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($ps_info['setting_value']){
			$ps_info['setting_value'] = unserialize($ps_info['setting_value']);
			foreach($ps_info['setting_value'] as $ptype=>$val){
				if($ptype == "credit_card") $ptype = "cc";
				$ptype = trim($ptype);
				if(!$val || $payment_type[$ptype]) continue;
				
				$payment_type[$ptype] = ucwords($ptype);
			}
		}
		
		// default payment type by credit card
		$cc_type = array("diners" => "Diners", "amex" => "Amex", "visa" => "Visa", "master" => "Master", "discover" => "Discover", "others" => "Others");
		
		$smarty->assign("payment_type", $payment_type);
		$smarty->assign("cc_type", $cc_type);
		$this->display();
	}
	
	private function load_cc_settings(){
		global $con, $smarty;
		
		$settings = load_cc_settings();
		//print_r($ap_settings);
		$smarty->assign('form', $settings);
	}
	
	function save_settings(){
		global $con,$sessioninfo;
		
		//print_r($_REQUEST);
		
		$form = $_REQUEST['cc_settings'];
		foreach($form as $name=>$r){
			$upd = array();
			$upd = $r;
			$con->sql_query("replace into web_bridge_cc_settings ".mysql_insert_by_field($upd));
		}
		
		log_br($sessioninfo['id'], 'WEB_BRIDGE', '', "Update CC Settings");
		print "OK";
	}
}

$WEB_BRIDGE_CC_TRANS_SETTINGS = new WEB_BRIDGE_CC_TRANS_SETTINGS('Web Bridge: CC Trans Settings');
?>
