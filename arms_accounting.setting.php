<?php
/*
4/16/2019 3:00 PM Andy
- Added Cash Sales Integration.

5/17/2019 4:48 PM Andy
- Added Account Receivable Integration.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ARMS_ACCOUNTING_SETTING')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ARMS_ACCOUNTING_SETTING', BRANCH_CODE), "/index.php");
$maintenance->check(386);

class ARMS_ACCOUNTING_SETTING extends Module {
	var $branch_list = array();
	var $acc_settings_list = array(
		'purchase' => array('name'=>'Purchase', 'default_acc_name'=>'Purchase', 'required'=>1),
		'cash_sales' => array('name'=>'Cash Sales',  'default_acc_name'=>'Cash Sales', 'required'=>1),
		'account_receivable' => array('name'=>'Account Receivable',  'default_acc_name'=>'Account Receivable', 'required'=>1),
		'global_input_tax' => array('name'=>'Global Input Tax',  'default_acc_name'=>'Input Tax'),
		'global_output_tax' => array('name'=>'Global Output Tax',  'default_acc_name'=>'Output Tax'),
	);
	var $gst_list = array();
	var $payment_type_list = array();
	
	function __construct($title)
	{
		// load all initial data
		$this->init_load();
		
		parent::__construct($title);
	}
	
	private function init_load(){
		global $config,$sessioninfo,$con, $smarty, $appCore, $pos_config;
		
		// Get Branch list
		$this->branch_list = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign("branch_list",$this->branch_list);
		
		// Accounting Settings List
		$smarty->assign("acc_settings_list",$this->acc_settings_list);
		
		// GST List
		if($config['enable_gst']){
			$con->sql_query("select * from gst where active=1 order by code");
			while($r = $con->sql_fetchassoc()){
				$this->gst_list[$r['id']] = $r;
			}
			$con->sql_freeresult();
			$smarty->assign("gst_list",$this->gst_list);
		}
		
		// Payment Type List
		$this->payment_type_list = array();
		
		// Default Payment Type
		foreach($pos_config['payment_type'] as $ptype){
			$ptype2 = strtolower($ptype);
			if($ptype2 == 'discount')	continue;	// skip discount
			
			$this->payment_type_list[$ptype2]['code'] = $ptype2;
			$this->payment_type_list[$ptype2]['desc'] = $ptype;
		}
		
		// deposit
		$ptype = 'deposit';
		$this->payment_type_list[$ptype]['code'] = $ptype;
		$this->payment_type_list[$ptype]['desc'] = ucwords($ptype);
		
		// Extra Payment Type
		if($config['counter_collection_extra_payment_type']){
			foreach($config['counter_collection_extra_payment_type'] as $ptype){
				$ptype2 = strtolower($ptype);
				$this->payment_type_list[$ptype2]['code'] = $ptype2;
				$this->payment_type_list[$ptype2]['desc'] = '(Extra) '.$ptype;
			}
		}
		
		// Foreign Currency
		$currency_list = $appCore->currencyManager->getCurrencyCodes();
		if($currency_list){
			foreach($currency_list as $currency_code){
				$ptype = strtolower($currency_code);
				$this->payment_type_list[$ptype]['code'] = $ptype;
				$this->payment_type_list[$ptype]['desc'] = '(Foreign Currency) '.$currency_code;
			}
			$ptype = 'currency_adjust';
			$this->payment_type_list[$ptype]['code'] = $ptype;
			$this->payment_type_list[$ptype]['desc'] = 'Currency Adjust';
		}
		//print_r($currency_list);
		
		// eWallet Payment
		if($config['ewallet_list']){
			$ewallet_list = $appCore->posManager->getEwalletList();
			if($ewallet_list){
				foreach($ewallet_list as $ewallet_type => $r){
					$ptype = 'ewallet_'.$ewallet_type;
					$this->payment_type_list[$ptype]['code'] = $ptype;
					$this->payment_type_list[$ptype]['desc'] = '(eWallet) '.$r['desc'];
				}
			}
			//print_r($ewallet_list);
		}
		//print_r($this->payment_type_list);
		$smarty->assign("payment_type_list",$this->payment_type_list);
	}
	
	function _default()
	{
		global $smarty, $sessioninfo, $config;

		$this->load_settings();
		$this->display();
	}
	
	private function load_settings(){
		global $smarty, $sessioninfo, $config, $con;
		
		$bid = mi($sessioninfo['branch_id']);
		$data = array();
		
		// Accounting Settings
		$con->sql_query("select * from arms_acc_settings where branch_id=$bid");
		while($r = $con->sql_fetchassoc()){
			$data['acc_settings']['normal'][$r['type']] = $r;
		}
		$con->sql_freeresult();
		
		// Payment Type
		$con->sql_query("select * from arms_acc_payment_settings where branch_id=$bid");
		while($r = $con->sql_fetchassoc()){
			$data['acc_settings']['payment'][$r['payment_type']] = $r;
		}
		$con->sql_freeresult();
		
		// GST Settings
		$con->sql_query("select * from arms_acc_gst_settings where branch_id=$bid");
		while($r = $con->sql_fetchassoc()){
			$data['gst_settings'][$r['gst_id']] = $r;
		}
		$con->sql_freeresult();
		
		// Other Settings
		$con->sql_query("select * from arms_acc_other_settings where branch_id=$bid");
		while($r = $con->sql_fetchassoc()){
			$data['acc_settings']['other'][$r['setting_name']] = $r['setting_value'];
		}
		$con->sql_freeresult();
		
		$smarty->assign('data', $data);
	}
	
	function ajax_save_settings(){
		global $con, $sessioninfo, $config;
		
		$bid = mi($sessioninfo['branch_id']);
		
		$form = $_REQUEST;
		
		//print_r($form);
		
		$con->sql_begin_transaction();
		
		if($form['data']['acc_settings']['normal']){
			// Normal Accounting Settings
			// Delete old
			$con->sql_query("delete from arms_acc_settings where branch_id=$bid");
			$con->sql_query("delete from arms_acc_payment_settings where branch_id=$bid");
			$con->sql_query("delete from arms_acc_gst_settings where branch_id=$bid");
			$con->sql_query("delete from arms_acc_other_settings where branch_id=$bid");
			
			//if(BRANCH_CODE == 'HQ' || $form['use_own_settings']){
				// Normal Accounting Settings
				foreach($form['data']['acc_settings']['normal'] as $type => $r){
					$upd = array();
					$upd['branch_id'] = $bid;
					$upd['type'] = $type;
					$upd['account_code'] = $r['account_code'];
					$upd['account_name'] = $r['account_name'];
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("replace into arms_acc_settings ".mysql_insert_by_field($upd));
				}
				
				// Payment Type
				foreach($form['data']['acc_settings']['payment'] as $type => $r){
					$upd = array();
					$upd['branch_id'] = $bid;
					$upd['payment_type'] = $type;
					$upd['account_code'] = $r['account_code'];
					$upd['account_name'] = $r['account_name'];
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("replace into arms_acc_payment_settings ".mysql_insert_by_field($upd));
				}
				
				// GST Settings
				if($config['enable_gst']){
					foreach($form['data']['gst_settings'] as $gst_id => $r){
						$upd = array();
						$upd['branch_id'] = $bid;
						$upd['gst_id'] = $gst_id;
						$upd['account_code'] = $r['account_code'];
						$upd['account_name'] = $r['account_name'];
						$upd['last_update'] = 'CURRENT_TIMESTAMP';
						$con->sql_query("replace into arms_acc_gst_settings ".mysql_insert_by_field($upd));
					}
				}
				
				// Other Accounting Settings
				foreach($form['data']['acc_settings']['other'] as $setting_name => $setting_value){
					$upd = array();
					$upd['branch_id'] = $bid;
					$upd['setting_name'] = trim($setting_name);
					$upd['setting_value'] = trim($setting_value);
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("replace into arms_acc_other_settings ".mysql_insert_by_field($upd));
				}
			//}
		}
		
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
		exit;
	}
}

$ARMS_ACCOUNTING_SETTING = new ARMS_ACCOUNTING_SETTING('ARMS Accounting Integration Settings');
?>