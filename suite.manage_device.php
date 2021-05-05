<?php
/*
1/18/2019 11:53 AM Andy
- Added device type "Business Intelligent".

4/18/2019 2:59 PM Andy
- Added device type "Consignment Sales Entry" for consignment mode only.

7/18/2019 1:30 PM Andy
- Added device type "Cycle Count".

2/3/2020 11:59 AM Andy
- Added device type "Mobile Suite".
- Added device type "Finger Print".
- Added device type "Time Attendance".

7/2/2020 3:19 PM Andy
- Added "Skip Dongle Checking" for Barcoder device.

9/28/2020 5:21 PM William
- Enhanced to add "arms_fnb" to device type.

11/9/2020 4:53 PM William
- Enhanced to add "pos" to device type.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if(!$config['enable_suite_device'])	js_redirect($LANG['NEED_CONFIG'], "/index.php");
if (!privilege('SUITE_MANAGE_DEVICE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SUITE_MANAGE_DEVICE', BRANCH_CODE), "/index.php");
$maintenance->check(377);

class SUITE_MANAGE_DEVICE extends Module{
	var $branches;
	var $hash_prefix = 'Arms2018'; // $hash_prefix + $app_access_code
	var $device_type_list = array(
		'barcoder' => 'Barcoder',
		'bi' => 'Business Intelligent',
		'cycle_count' => 'Cycle Count',
		'finger_print' => 'Finger Print',
		'mobile_suite' => 'Mobile Suite',
		'price_checker' => 'Price Checker',
		'sales_order' => 'Sales Order',
		'stock_take' => 'Stock Take',
		'time_attendance' => 'Time Attendance',
		'arms_fnb' => 'ARMS Fnb',
		'pos' => 'Kiosk / POS / Self Checkout',
	);
	
	function __construct($title){
		global $con, $smarty;
		
		$this->init_load();
		
		parent::__construct($title);
	}
	
	function _default(){
		$this->reload_device_list();
	    $this->display();
	}
	
	private function init_load(){
		global $con, $smarty, $appCore, $config;
		
		$this->branches = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign('branches', $this->branches);
		
		if($config['consignment_modules']){
			$this->device_type_list['con_sales_enty'] = 'Consignment Sales Entry';
		}
		
		$smarty->assign('device_type_list', $this->device_type_list);
	}
	
	private function reload_device_list(){
		global $con, $smarty, $config, $sessioninfo;
		
		$device_list = array();
		$filter = array();
		$filter[] = "sd.branch_id=".mi($sessioninfo['branch_id']);
		
		$str_filter = join(' and ', $filter);
		
		$q1 = $con->sql_query("select sd.*, sds.paired
			from suite_device sd 
			left join suite_device_status sds on sd.guid=sds.device_guid
			where $str_filter order by sd.device_code");
		while($r = $con->sql_fetchassoc($q1)){
			$r['allowed_branches'] = unserialize($r['allowed_branches']);
			$device_list[$r['guid']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('device_list', $device_list);
	}
	
	function ajax_unpair_device(){
		global $con, $smarty, $sessioninfo;
		
		$device_guid = trim($_REQUEST['device_guid']);
		
		if(!$device_guid){
			die("Invalid Device ID");
		}
		
		// Get Device
		$con->sql_query("select *
			from suite_device
			where guid=".ms($device_guid));
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Device Not Found
		if(!$form)	die("Invalid Device ID");
		
		$upd = array();
		$upd['paired'] = 0;
		$upd['paired_device_id'] = '';
		
		$con->sql_query("update suite_device_status set ".mysql_update_by_field($upd)." where device_guid=".ms($device_guid));
		$con->sql_query("update suite_device set last_update=CURRENT_TIMESTAMP where guid=".ms($device_guid));
		
		log_br($sessioninfo['id'], 'SUITE_DEVICE', 0, 'Un-Pair Device: '. $form['device_code'] . ". GUID: ".$device_guid);
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
	
	function ajax_show_device(){
		global $con, $smarty;
		
		$device_guid = trim($_REQUEST['device_guid']);
		
		if($device_guid){
			$form = array();
			$con->sql_query("select *
				from suite_device
				where guid=".ms($device_guid));
			$form = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$form)	die("Invalid Device ID");
			
			$form['allowed_branches'] = unserialize($form['allowed_branches']);
			
			$smarty->assign('form', $form);
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('suite.manage_device.open.tpl');
		
		print json_encode($ret);
	}
	
	function ajax_update_device_active(){
		global $con, $smarty, $sessioninfo;
		
		$device_guid = trim($_REQUEST['device_guid']);
		$active = mi($_REQUEST['active']);
		if($sessioninfo['id'] != 1)	die("Invalid User");
		
		// Get Device
		$con->sql_query("select *
			from suite_device
			where guid=".ms($device_guid));
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
				
		// Device Not Found
		if(!$form)	die("Invalid Device ID");
		
		$upd = array();
		$upd['active'] = $active;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("update suite_device set ".mysql_update_by_field($upd)." where guid=".ms($device_guid));
		
		log_br($sessioninfo['id'], 'SUITE_DEVICE', 0, ($active?'Activate':'In-activate').' Device: '. $form['device_code'] . ". GUID: ".$device_guid);
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
	
	 function ajax_reload_device_list(){
		global $con, $smarty, $sessioninfo;
		 
		$this->reload_device_list();
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('suite.manage_device.table.tpl');
		print json_encode($ret);
	}
	
	function ajax_update_device(){
		global $con, $smarty, $sessioninfo, $LANG, $appCore;
		
		$form = $_REQUEST;
		
		$device_guid = trim($form['guid']);
		
		$upd = array();
		$upd['device_code'] = trim($form['device_code']);
		$upd['device_name'] = trim($form['device_name']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		if($device_guid){
			// Edit
			$con->sql_query("select *
				from suite_device
				where guid=".ms($device_guid));
			$old_form = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$old_form)	die("Invalid Device ID");
		}else{
			// Add
			if($sessioninfo['id'] != 1){	// only admin can add
				die($LANG['USER_LEVEL_NO_REACH']);
			}
			
			$upd['guid'] = $appCore->newGUID();
			$upd['active'] = 1;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$upd['branch_id'] = $sessioninfo['branch_id'];
		}
		
		if(!$upd['device_code'])	die("Invalid Device Code");
		if(!$upd['device_name'])	die("Invalid Device Name");
		
		if(trim($form['device_type']) == 'arms_fnb' || trim($form['device_type']) == 'pos'){
			// check duplicate device name
			$con->sql_query($qry="select * from suite_device where device_name=".ms($upd['device_name'])." and branch_id=".mi($sessioninfo['branch_id'])." and device_type in('arms_fnb', 'pos') and guid<>".ms($device_guid));
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($tmp)	die("ARMS Fnb device name already exist.");
		}
		
		if($sessioninfo['id'] == 1){
			$upd['device_type'] = trim($form['device_type']);
			$upd['device_access_token'] = trim($form['device_access_token']);
			
			if(!$upd['device_access_token']){
				die("Invalid Device Access Token");
			}
			
			// check duplicate access token
			$con->sql_query("select * from suite_device where device_access_token=".ms($upd['device_access_token'])." and guid<>".ms($device_guid));
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($tmp)	die("Access Token Used by Other Device");
			
			if($upd['device_type'] == 'barcoder'){
				$upd['skip_dongle_checking'] = mi($form['skip_dongle_checking']);
			}
		}
		
		if(BRANCH_CODE == 'HQ'){
			if(trim($form['device_type']) == 'arms_fnb' || trim($form['device_type']) == 'pos'){
				$allowed_branch = array();
				$allowed_branch[] = $sessioninfo['branch_id'];
				$form['allowed_branches'] = $allowed_branch;
			}
			$upd['allowed_branches'] = serialize($form['allowed_branches']);
		}
		
		if($device_guid){
			// Edit
			$con->sql_query("update suite_device set ".mysql_update_by_field($upd)." where guid=".ms($device_guid));
			
			if($upd['device_access_token'] && $upd['device_access_token'] != $old_form['device_access_token']){
				$upd2 = array();
				$upd2['encrypt_token'] = md5($this->hash_prefix.$upd['device_access_token']);
				
				$con->sql_query("update suite_device_status set ".mysql_update_by_field($upd2)." where device_guid=".ms($device_guid));
			}
			
			log_br($sessioninfo['id'], 'SUITE_DEVICE', 0, 'Update Device: '. $upd['device_code'] . ". GUID: ".$device_guid);
		}else{
			// Add
			$con->sql_query("insert into suite_device ".mysql_insert_by_field($upd));
			
			$upd2 = array();
			$upd2['device_guid'] = $upd['guid'];
			$upd2['encrypt_token'] = md5($this->hash_prefix.$upd['device_access_token']);
			$upd2['paired'] = 0;
			
			$con->sql_query("insert into suite_device_status  ".mysql_insert_by_field($upd2));
			
			log_br($sessioninfo['id'], 'SUITE_DEVICE', 0, 'Add Device: '. $upd['device_code'] . ". GUID: ".$upd['guid']);
		}		
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
}

$SUITE_MANAGE_DEVICE = new SUITE_MANAGE_DEVICE('Suite Device Setup');
?>