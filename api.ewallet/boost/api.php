<?php
/*
4/8/2019 2:11 PM Andy
- Fixed live api url.

4/17/2019 4:31 PM Justin
- Added void payment function.

5/16/2019 2:02 PM Justin
- Bug fixed on production url had been inserted wrongly.

5/17/2019 11:56 AM Andy
- Added to check "error_description" when api call failed.
*/
class EWALLET_API_BOOST extends EWALLET_API {
	var $ewallet_type = 'boost';
	var $stage_authentication_url = 'https://stage-wallet.boostorium.com';
	var $authentication_url = 'https://wallet.boost-my.com';
	var $stage_api_url = 'https://stage-wallet.boostorium.com/api/v1.0';
	var $api_url = 'https://wallet.boost-my.com/api/v1.0';
	
	var $api_key = '';
	var $api_secret = '';
	var $merchant_code = '';
	var $outlet_code = '';
	var $counter_pos_id;
	var $apiToken = '';
	
	function __construct(){
		parent::__construct();
		
		// Define Folder Path
		$this->folder = dirname(__FILE__);
		$this->log_folder = $this->folder."/".$this->log_folder;
		
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
		if(!$this->ewallet_branch_settings['merchant_code'] || !$this->ewallet_branch_settings['outlet_code']){
			$this->error_respond('Invalid eWallet Branch Setting');
		}
		
		$this->merchant_code = trim($this->ewallet_branch_settings['merchant_code']);
		$this->outlet_code = trim($this->ewallet_branch_settings['outlet_code']);
	}
	
	public function validate_ewallet_counter_setting(){
		if(!$this->counter){
			$this->error_respond('Invalid eWallet Counter');
		}
		$this->counter_pos_id = $this->counter['branch_id']."_".$this->counter['id'];
	}
	
	private function boost_authentication(){
		if($this->apiToken)	return;	// Already got the token
		
		$headers = array(
			'Content-Type: application/json'
		);
	  
		$url = $this->is_debug ? $this->stage_authentication_url : $this->authentication_url;
		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $url.'/authentication');
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		
		$data = array();
		$data['apiKey'] = $this->api_key;
		$data['apiSecret'] = $this->api_secret;
		
		curl_setopt($s, CURLOPT_POSTFIELDS, json_encode($data));
		$result = curl_exec($s);
		curl_close($s);
		
		//$this->put_log('url: '.$url.'/authentication');
		$json = json_decode($result, true);
		$ret = array();
		
		if($json['apiToken']){	// Authentication Success
			$this->apiToken = $json['apiToken'];
			$ret['ok'] = 1;
		}else{	// Failed
			$this->failed_obj = $json;
			
			$err_msg = '';
			if($json['errorMessage'])	$err_msg = $json['errorMessage'];
			if(!$err_msg && $json['details'])	$err_msg = $json['details'];
			if(!$err_msg && $json['error_description'])	$err_msg = $json['error_description'];
			
			$ret['error'] = $err_msg;
		}
		
		return $ret;
	}
	
	public function make_payment(){
		// Validate
		if(!$this->apiToken){
			$result = $this->boost_authentication();
			if(!$result['ok']){
				return $result;
			}
		}
		
		
		// Call API to Pay
		$result = $this->pay_by_customer_QR();
		if(!$result['ok']){
			return $result;
		}
		
		$ret = array();
		$ret['ok'] = 1;
		
		return $ret;
	}
	
	private function pay_by_customer_QR(){
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$this->apiToken
		);
	  
		$url = $this->is_debug ? $this->stage_api_url : $this->api_url;
		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $url.'/cloud/transaction/payment/customerQR');
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		
		$data = array();
		$data['merchantId'] = $this->merchant_code;
		$data['outletId'] = $this->outlet_code;
		$data['posId'] = $this->counter_pos_id;
		$data['posRefNum'] = $this->receipt_ref_no;
		$data['customerToken'] = $this->customer_token;
		$data['amount'] = $this->transaction_amount;
		
		curl_setopt($s, CURLOPT_POSTFIELDS, json_encode($data));
		$result = curl_exec($s);
		curl_close($s);
		
		$json = json_decode($result, true);
		$ret = array();
		
		if($json['transactionStatus'] == 'completed' && $json['boostRefNum']){	// Authentication Success
			$this->success_info = $json;
			$this->success_ref_no = $json['boostRefNum'];
			$ret['ok'] = 1;
		}else{	// Failed
			$this->failed_obj = $json;
			
			$err_msg = '';
			if($json['errorMessage'])	$err_msg = $json['errorMessage'];
			if(!$err_msg && $json['details'])	$err_msg = $json['details'];
			if(!$err_msg && $json['message'])	$err_msg = $json['message'];
			if(!$err_msg && $json['error_description'])	$err_msg = $json['error_description'];
			
			$ret['error'] = $err_msg;
		}
		
		return $ret;
	}
	
	public function check_payment_status($prms=array()){
		// Validate
		if(!$this->apiToken){
			$result = $this->boost_authentication();
			if(!$result['ok']){
				return $result;
			}
		}
		
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$this->apiToken
		);
	  
		$url = $this->is_debug ? $this->stage_api_url : $this->api_url;
		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $url.'/cloud/transaction/ref/'.$this->merchant_code.'/'.$this->outlet_code.'/'.$this->receipt_ref_no);
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($s);
		curl_close($s);
		
		$json = json_decode($result, true);
		$ret = array();
		
		//print_r($json);exit;
		if($json['transactionStatus'] == 'completed' && $json['boostRefNum']){	// Authentication Success
			$this->success_info = $json;
			$this->success_ref_no = $json['boostRefNum'];
			$ret['ok'] = 1;
		}else{	// Failed
			$this->failed_obj = $json;
			
			$err_msg = '';
			if($json['errorMessage'])	$err_msg = $json['errorMessage'];
			if(!$err_msg && $json['details'])	$err_msg = $json['details'];
			if(!$err_msg && $json['message'])	$err_msg = $json['message'];
			
			$ret['error'] = $err_msg;
		}
		
		return $ret;
	}
	
		
	public function void_payment($prms=array()){
		// Validate
		if(!$this->apiToken){
			$result = $this->boost_authentication();
			if(!$result['ok']){
				return $result;
			}
		}
		
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$this->apiToken
		);
	  
		$url = $this->is_debug ? $this->stage_api_url : $this->api_url;
		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $url.'/cloud/transaction/void/');
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		
		$data = array();
		$data['merchantId'] = $this->merchant_code;
		$data['posRefNum'] = $this->receipt_ref_no;
		$data['boostPaymentRefNum'] = $this->ewallet_ref_no;
		$data['remark'] = "Customer request refund";
		
		curl_setopt($s, CURLOPT_POSTFIELDS, json_encode($data));
		$result = curl_exec($s);
		curl_close($s);
		
		$json = json_decode($result, true);
		$ret = array();
		
		if($json['transactionStatus'] == 'completed' && $json['boostRefNum']){	// Authentication Success
			$this->success_info = $json;
			$this->success_ref_no = $json['boostRefNum'];
			$ret['ok'] = 1;
		}else{	// Failed
			$this->failed_obj = $json;
			
			$err_msg = '';
			if($json['errorMessage'])	$err_msg = $json['errorMessage'];
			if(!$err_msg && $json['details'])	$err_msg = $json['details'];
			if(!$err_msg && $json['message'])	$err_msg = $json['message'];
			
			$ret['error'] = $err_msg;
		}
		
		return $ret;
	}
}

$EWALLET_API_BOOST = new EWALLET_API_BOOST();
?>