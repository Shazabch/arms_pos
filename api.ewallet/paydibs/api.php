<?php
/*
7/22/2019 3:25 PM Justin
- Bug fixed on payment success also need to check on "user_order_status = S".
*/

class EWALLET_API_PAYDIBS extends EWALLET_API {
	var $ewallet_type = '';
	var $mst_ewallet_type = '';
	var $stage_api_url = "https://merchanttest.paydibs.com/version1";
	var $api_url = "https://mcashbizapi.paydibs.com/version1";
	var $api_key = '';
	var $api_secret = '';
	var $merchant_code = '';
	var $merchant_password = '';
	var $counter_pos_id;
	var $authToken = '';
	var $currTime = '';
	var $transID = '';
	var $ewallet_integrator_id = '';
	
	function __construct(){		
		// set ewallet type
		$this->ewallet_type = $_REQUEST['ewallet_type'];
		
		// set parent ewallet type
		list($ewallet_type, $integrator_type) = explode("_", $this->ewallet_type, 2);
		$this->mst_ewallet_type = $ewallet_type;
		
		// api.ewallet.php callback
		parent::__construct();
		
		// Define Folder Path 
		$this->folder = dirname(__FILE__);
		$this->log_folder = $this->folder."/".$this->log_folder;
		
		// get current time
		$this->currTime = time();
		
		// Create Folders and Database
		$this->prepareDB();
		
		// Start the API
		$this->start();
	}
	
	// function to create all db and folder 
	private function prepareDB(){
		global $con;
		
		// Create Program Logs Folder
		check_and_create_dir($this->log_folder);
				
		// Create Integrator list
		list($ewallet_type, $integrator_type) = explode("_", $this->ewallet_type, 2);
		$q1 = $con->sql_query("select * from ewallet_integrator_list where integrator_type = ".ms($integrator_type)." and ewallet_type = ".ms($ewallet_type));
		$integrator_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$this->ewallet_integrator_id = $integrator_info['integrator_id'];
	}
	
	private function start(){
		// Run Common Process
		$this->_default();
	}
	
	public function validate_ewallet_arms_setting(){
		if(!$this->ewallet_arms_settings['api_key'] || !$this->ewallet_arms_settings['api_secret']){
			$this->error_respond('Invalid eWallet ARMS Setting');
		}
		
		$this->api_key = trim($this->ewallet_arms_settings['api_key']);
		$this->api_secret = trim($this->ewallet_arms_settings['api_secret']);
	}
	
	public function validate_ewallet_branch_setting(){
		if(!$this->ewallet_branch_settings['merchant_code'] || !$this->ewallet_branch_settings['merchant_password']){
			$this->error_respond('Invalid eWallet Branch Setting');
		}
		
		$this->merchant_code = trim($this->ewallet_branch_settings['merchant_code']);
		$this->merchant_password = trim($this->ewallet_branch_settings['merchant_password']);
	}
	
	public function validate_ewallet_counter_setting(){
		if(!$this->counter){
			$this->error_respond('Invalid eWallet Counter');
		}
		$this->counter_pos_id = $this->counter['branch_id']."_".$this->counter['id'];
	}
	
	private function paydibs_authentication(){
		if($this->authToken)	return;	// Already got the token
		
		$headers = array(
			 'Content-Type: application/x-www-form-urlencoded',
			 'Key: '.$this->api_key
		);
	  
		$url = $this->is_debug ? $this->stage_api_url : $this->api_url;
		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $url.'/token/api-token');
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
		
		$ret = array();
		$json = json_decode($result, true);
		if($json['data']['status'] == 1 && $json['data']['Auth_Token']){	// Authentication Success
			$this->authToken = $json['data']['Auth_Token'];
			$ret['ok'] = 1;
		}else{	// Failed
			$this->failed_obj = $json;
			
			$err_msg = '';
			if($json['code'] != 200 && $json['msg'])	$err_msg = $json['msg'];
			if(!$err_msg && $json['data']['msg'])	$err_msg = $json['data']['msg'];
			
			$ret['error'] = $err_msg;
		}
		
		return $ret;
	}
	
	private function paydibs_generate_trans_id(){
		if($this->transID)	return;	// Already got the token
		
		$headers = array(
			 'Content-Type: application/x-www-form-urlencoded',
			 'Key: '.$this->api_key,
			 'Auth-Token: '.$this->authToken
		);
	  
		$url = $this->is_debug ? $this->stage_api_url : $this->api_url;
		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $url.'/payment/create-trade');
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		
		// build up the data
		$data = array();
		$data['integrator_id'] = $this->ewallet_integrator_id;
		$data['amount'] = $this->transaction_amount;
		$data['time'] = $this->currTime;
		$data['trx_id'] = $this->receipt_ref_no; // receipt_ref_no
		$data['pay_type'] = "MSU"; // means we scan customer's QR code
		$data['sign'] = $this->getSign($data);
		
		// get Transaction ID
		curl_setopt($s, CURLOPT_POST, true);
		curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($data));
		$result = curl_exec($s);
		curl_close($s);
		
		$ret = array();
		$json = json_decode($result, true);
		if($json['data']['trans_id']){	// Authentication Success
			$this->transID = $json['data']['trans_id'];
			$ret['ok'] = 1;
		}else{	// Failed
			$this->failed_obj = $json;
			
			$err_msg = '';
			if($json['code'] != 200 && $json['msg'])	$err_msg = $json['msg'];
			if(!$err_msg && $json['data']['msg'])	$err_msg = $json['data']['msg'];
			
			$ret['error'] = $err_msg;
		}
		
		return $ret;
	}
	
	public function make_payment(){
		// Validate and generate Auth Token
		if(!$this->authToken){
			$result = array();
			$result = $this->paydibs_authentication();
			if(!$result['ok']){
				return $result;
			}
		}
		
		// Generate Transaction ID
		if(!$this->transID){
			$result = array();
			$result = $this->paydibs_generate_trans_id();
			if(!$result['ok']){
				return $result;
			}
		}
		
		// Call API to Pay
		$result = array();
		$result = $this->pay_by_customer_QR();
		if(!$result['ok']){
			return $result;
		}
		
		$ret = array();
		$ret['ok'] = 1;
		
		return $ret;
	}
	
	private function pay_by_customer_QR(){
		global $LANG;
		
		$headers = array(
			 'Content-Type: application/x-www-form-urlencoded',
			 'Key: '.$this->api_key,
			 'Auth-Token: '.$this->authToken
		);

	  
		$url = $this->is_debug ? $this->stage_api_url : $this->api_url;
		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $url.'/payment/payment');
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		
		// build up the data
		$data = array();
		$data['time'] = $this->currTime;
		$data['user_qr_code'] = $this->customer_token;
		$data['integrator_id'] = $this->ewallet_integrator_id;
		$data['amount'] = $this->transaction_amount;
		$data['trans_id'] = $this->transID;
		$data['sign'] = $this->getSign($data);
		
		curl_setopt($s, CURLOPT_POST, true);
		curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($data));
		$result = curl_exec($s);
		curl_close($s);
		
		$ret = array();
		$json = json_decode($result, true);
		
		if($json['data']['trans_id'] && $json['data']['status'] == 200 && $json['data']['user_order_status'] == "S"){	// Authentication Success
			$this->success_info = $json['data'];
			$this->success_ref_no = $json['data']['trans_id'];
			$ret['ok'] = 1;
		}else{	// Failed
			$this->failed_obj = $json;
			
			$err_msg = '';
			
			// if the payment was successful but it is under processing, need to stop user from checkout
			if($json['data']['status'] == 200 && $json['data']['user_order_status'] != "S") $err_msg = $LANG['EWALLET_API_PAYDIBS_PAYMENT_PROCESSED_FAILED'];
			elseif($json['code'] != 200 && $json['msg'])	$err_msg = $json['msg'];
			
			if(!$err_msg && $json['data']['msg'])	$err_msg = $json['data']['msg'];
			
			$ret['error'] = $err_msg;
		}
		
		return $ret;
	}
	
	public function check_payment_status($prms=array()){
		// Validate
		if(!$this->authToken){
			$result = array();
			$result = $this->paydibs_authentication();
			if(!$result['ok']){
				return $result;
			}
		}
		
		// api.ewellet.php must provide Transaction ID
		$transID = $prms['more_info']['success_info']['trans_id'];
		if(!$transID){
			$result = array();
			$result['error'] = "Invalid Transaction ID.";
			return $result;
		}
		
		$headers = array(
			 'Content-Type: application/x-www-form-urlencoded',
			 'Key: '.$this->api_key,
			 'Auth-Token: '.$this->authToken
		);
	  
		$url = $this->is_debug ? $this->stage_api_url : $this->api_url;
		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $url.'/payment/check-status');
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		
		// build up the data
		$data = array();
		$data['time'] = $this->currTime;
		$data['integrator_id'] = $this->ewallet_integrator_id; // ewallet type, 1=mcash, 3=boost
		$data['trans_id'] = $transID;
		$data['sign'] = $this->getSign($data);
		
		curl_setopt($s, CURLOPT_POST, true);
		curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($data));
		$result = curl_exec($s);
		curl_close($s);
		
		$ret = array();
		$json = json_decode($result, true);
		if($json['data']['pay_status'] == 'D' && $json['data']['msg'] == "Success Payment."){	// Authentication Success
			$this->success_info = $json['data'];
			$this->success_ref_no = $json['data']['trans_id'];
			$ret['ok'] = 1;
		}else{	// Failed
			$this->failed_obj = $json;
			
			$err_msg = '';
			if($json['code'] != 200 && $json['msg'])	$err_msg = $json['msg'];
			if(!$err_msg && $json['data']['msg'])	$err_msg = $json['data']['msg'];
			
			$ret['error'] = $err_msg;
		}
		
		return $ret;
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
	
	public function void_payment($prms=array()){
		// Validate
		if(!$this->authToken){
			$result = array();
			$result = $this->paydibs_authentication();
			if(!$result['ok']){
				return $result;
			}
		}
		
		// api.ewellet.php must provide Transaction ID
		$transID = $prms['more_info']['success_info']['trans_id'];
		if(!$transID){
			$result = array();
			$result['error'] = "Invalid Transaction ID.";
			return $result;
		}
		
		$headers = array(
			 'Content-Type: application/x-www-form-urlencoded',
			 'Key: '.$this->api_key,
			 'Auth-Token: '.$this->authToken
		);
	  
		$url = $this->is_debug ? $this->stage_api_url : $this->api_url;
		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $url.'/payment/process-void');
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		
		// build up the data
		$data = array();
		$data['time'] = $this->currTime;
		$data['integrator_id'] = $this->ewallet_integrator_id; // ewallet type, 1=mcash, 3=boost
		$data['trans_id'] = $transID;
		$data['void_pin'] = md5($this->merchant_password);
		$data['sign'] = $this->getSign($data);
		
		curl_setopt($s, CURLOPT_POST, true);
		curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($data));
		$result = curl_exec($s);
		curl_close($s);
		
		$ret = array();
		$json = json_decode($result, true);

		if($json['data']['status'] == 1){	// Authentication Success
			$this->success_info = $json['data'];
			$this->success_ref_no = $json['data']['transaction_id'];
			$ret['ok'] = 1;
		}else{	// Failed
			$this->failed_obj = $json;
			
			$err_msg = '';
			if($json['code'] != 200 && $json['msg'])	$err_msg = $json['msg'];
			if(!$err_msg && $json['data']['status'])	$err_msg = $this->void_error_respond($json['data']['status']);
			
			$ret['error'] = $err_msg;
		}
		
		return $ret;
	}
	
	private function void_error_respond($code){
		$status = array(  
			1 => "Success",
			3 => 'Record Could Not Be Found',
			5 => "The Request is Timeout",
			6 => "Invalid Password",
			7 => "Void Failed",
			8 => "Unknown Error Occured"
		); 
		
		return ($status[$code])?$status[$code]:$status[8]; 
	}
}

$EWALLET_API_PAYDIBS = new EWALLET_API_PAYDIBS();
?>