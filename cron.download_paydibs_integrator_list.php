<?php
/*
4/2/2019 5:48 PM Justin
- New cron to download integrator list for PayDibs.

====================
Command
php cron.download_paydibs_integrator_list.php

*/
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '1024M');
set_time_limit(0);

$argv = $_SERVER['argv'];
$DL_EWALLET_LIST = new DL_EWALLET_LIST();
$DL_EWALLET_LIST->start();

class DL_EWALLET_LIST {
	
	var $ewallet_integrator_list = array();
	var $fp_path = '';
	var $payment_type = 'MSU'; // merchant scan customer
	var $regen = false;
	var $merchant_code = '';
	var $merchant_password = '';
	var $api_url = 'https://mcashbizapi.paydibs.com/version1';
	var $authToken = '';
	var $api_key = '';
	var $api_token = '';
	var $currTime = '';
	var $ewallet_type = 'paydibs';
	
	function __construct(){
	    global $con;

		$this->fp_path = dirname(__FILE__)."/".basename(__FILE__, '.php').".running";	// use this prevent wrong "include" path
		
		$this->fp = fopen($this->fp_path, "w");
		chmod($this->fp_path, 0777);
		
		// get current time
		$this->currTime = time();
		
		$this->mark_start_process();
	}
	
	function __destruct() {
        $this->mark_close_process();
    }
	
	private function mark_start_process(){		
		if(!is_writable(dirname($this->fp_path))){
			print "The folder '".dirname($this->fp_path)."' permission not allow this process to be run, please contact system admin.\n";
			exit;
		}
		
		if(!flock($this->fp, LOCK_EX | LOCK_NB)){
			print "Other process is running, please wait for them to finish.\n";
			exit;
		}
	}
	
	private function mark_close_process(){
		flock($this->fp, LOCK_UN);
	}
	
	function start(){		
		$this->filter_argv();
		
		$this->start_generate();
	}
	
	function filter_argv(){
		global $argv, $con, $config;
		
		//print_r($argv);
		
		$dummy = array_shift($argv);
		$this->b_list = array();
		$selected_sales_type_list = array();

		while($cmd = array_shift($argv)){
			list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
			
			if($cmd_head == '-regen'){	// optional, re-download all ewallet list
				$this->regen = true;
			}else{
				print "Unknown command $cmd\n";
				exit;
			}
		}
	}
	
	private function validate_required_data(){
		global $config;
		
		// get the settings from first array
		$ewallet_settings = reset($config['ewallet_settings'][$this->ewallet_type]['branch_settings']);
		
		// get merchant code and password
		$this->merchant_code = trim($ewallet_settings['merchant_code']);
		$this->merchant_password = trim($ewallet_settings['merchant_password']);
		
		if(!$this->merchant_code || !$this->merchant_password){
			die("Invalid Merchant Code or Password.\n");
		}
		
		// get api and secret keys (always get the production site)
		$ewallet_arms_settings = $config['ewallet_arms_setting'][$this->ewallet_type]['production'];
		$this->api_key = $ewallet_arms_settings['api_key'];
		$this->api_secret = $ewallet_arms_settings['api_secret'];
		
		if(!$this->api_key || !$this->api_secret){
			die("Invalid API or Secret Key.\n");
		}
	}
	
	private function start_generate(){
		print "Start\n";
		
		// get required data
		$this->validate_required_data();
		
		// get paydibs auth token
		$this->paydibs_authentication();
		
		// first, get the ewallet list
		$this->download_ewallet_list();
		
		// clear the current ewallet list from db if got pass -regen
		$this->clear_ewallet_list();
		
		// insert ewallet list
		$this->insert_ewallet_list();
		
		print "All Done.\n";
	}
	
	private function download_ewallet_list(){
		if(!$this->authToken) die("Invalid Auth-Token!\n");
		
		$headers = array(
			 'Content-Type: application/x-www-form-urlencoded',
			 'Key: '.$this->api_key,
			 'Auth-Token: '.$this->authToken
		);
	  
		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $this->api_url.'/payment/integrator-master');
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		
		// build up the data
		$data = array();
		$data['pay_type'] = $this->payment_type;
		$data['time'] = $this->currTime;
		$data['sign'] = $this->getSign($data);
		
		// get Auth Token
		curl_setopt($s, CURLOPT_POST, true);
		curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($data));
		$result = curl_exec($s);
		curl_close($s);
		
		$json = json_decode($result, true);
		
		if(is_array($json['data'])){
			foreach($json['data'] as $dummy=>$r){
				if($r['active'] == "T") continue; // it is testing eWallet
				$r['integrator_name'] = strtolower($r['integrator_name']);
				$this->ewallet_integrator_list[$r['id']] = $r;
			}
		}else{
			die("Couldn't download the eWallet Integrator List.\n");
		}
	}
	
	private function clear_ewallet_list(){
		global $con;
		
		// don't run this if didn't regenerate in purpose
		// think twice before run this, the integrator_id might changed
		if(!$this->regen) return;
			
		print " - Purge eWallet list for ".$this->ewallet_type."\n";
		$con->sql_begin_transaction();
		
		$con->sql_query("delete from ewallet_integrator_list where ewallet_type = ".ms($this->ewallet_type));
		
		$con->sql_commit();
	}
	
	private function insert_ewallet_list(){
		global $con;
		
		$con->sql_begin_transaction();
		
		// Check Record and store ewallet into db
		foreach($this->ewallet_integrator_list as $integrator_id=>$info){
			print "Processing ".$info['integrator_name']."...\n";
			$ewallet_imgfile_timestamp = $arms_imgfile_timestamp = "";
			$q1 = $con->sql_query("select * from ewallet_integrator_list where integrator_type = ".ms($info['integrator_name'])." and ewallet_type = ".ms($this->ewallet_type)." and integrator_id = ".mi($integrator_id));
			
			// do update
			if($con->sql_numrows($q1) > 0){
				$upd = array();
				$upd['integrator_type'] = $info['integrator_name'];
				$upd['integrator_logo_link'] = $info['integrator_logo_link'];
				$upd['last_update'] = "CURRENT_TIMESTAMP";
				
				$con->sql_query("update ewallet_integrator_list set ".mysql_update_by_field($upd)." where integrator_type = ".ms($info['integrator_name'])." and ewallet_type = ".ms($this->ewallet_type)." and integrator_id = ".mi($integrator_id));
				
			}else{ // do insert
				$ins = array();
				$ins['integrator_id'] = $integrator_id;
				$ins['integrator_type'] = $info['integrator_name'];
				$ins['ewallet_type'] = $this->ewallet_type;
				$ins['integrator_logo_link'] = $info['integrator_logo_link'];
				$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
				
				$con->sql_query("replace into ewallet_integrator_list ".mysql_insert_by_field($ins));
			}
			$con->sql_freeresult($q1);
			
			// setup file name
			$file_name = "ui/ewallet-".$this->ewallet_type."_".$info['integrator_name'].".png";
			
			// get integrator image info
			$integrator_image_info = get_headers($info['integrator_logo_link'], 1);

			// load the image last modified date
			if (stristr($integrator_image_info[0], '200')) {
				foreach($integrator_image_info as $k=>$v) {
					if(strtolower(trim($k))=="last-modified") $ewallet_imgfile_timestamp = strtotime($v);
				}
			}
			
			if(file_exists($file_name)) $arms_imgfile_timestamp = filemtime($file_name);
			
			// save photo if both last modified time are different
			if($ewallet_imgfile_timestamp > $arms_imgfile_timestamp){
				// copy the image file to tmp folder first
				$tmp_ewallet_img_location = "/tmp/ewallet-".$this->ewallet_type."_".$info['integrator_name'].".png";
				copy($info['integrator_logo_link'], $tmp_ewallet_img_location);
				
				// copy the image file from tmp to ui folder
				if(file_exists($tmp_ewallet_img_location)){
					resize_photo($tmp_ewallet_img_location, $file_name, "png", 460);
					print "Downloaded ".$info['integrator_name']." ($file_name) image.\n";
				}
			}
			
			print "Done\n";
		}
		
		$con->sql_commit();
	}
	
	private function paydibs_authentication(){
		if($this->authToken) return;	// Already got the token
	  
	  	$headers = array(
			 'Content-Type: application/x-www-form-urlencoded',
			 'Key: '.$this->api_key
		);
	  
		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $this->api_url.'/token/api-token');
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		
		// build up the data
		$data = array();
		$data['login_id'] = $this->merchant_code;
		$data['login_password'] = md5($this->merchant_password);
		$data['time'] = $this->currTime;
		$data['sign'] = $this->getSign($data);
		
		// get Auth Token
		curl_setopt($s, CURLOPT_POST, true);
		curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($data));
		$result = curl_exec($s);
		curl_close($s);
		
		$json = json_decode($result, true);
		if($json['data']['status'] == 1 && $json['data']['Auth_Token']){	// Authentication Success
			$this->authToken = $json['data']['Auth_Token'];
		}else{	// Failed
			/*$err_msg = '';
			if($json['code'] != 200 && $json['msg'])	$err_msg = $json['msg'];
			if(!$err_msg && $json['data']['msg'])	$err_msg = $json['data']['msg'];*/
			
			die("Couldn't get Auth Token.\n");
		}
	}
	
	private function getSign($compare_arr=array()){
		if(!$compare_arr) return;
		
		$compare_key = array_keys($compare_arr);
		sort($compare_key);
		$compare_sign = $request_sign = $request_time = '';
		foreach ( $compare_key as $api_key => $api_value ){
			if ( $api_value == 'sign' ){
				$request_sign = $compare_arr[$api_value] ;
			}elseif ( $api_value == 'time' ){
				$request_time = $compare_arr[$api_value] ;
			}else{
				$compare_sign .= $compare_arr[$api_value] ;
			}
		}
		$compare_sign .= $request_time . $this->api_secret;
		$sign = md5($compare_sign);
		return $sign;
	}
}
?>