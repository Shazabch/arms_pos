<?php
/*
4/23/2021 12:13 PM William
- Enhanced function "pay_by_customer_QR" to return actual_ewallet_type when integrator_type is all.
*/
class EWALLET_API_IPAY88 extends EWALLET_API {
	var $ewallet_type = '';
	var $mst_ewallet_type = '';
	var $api_url = 'https://payment.ipay88.com.my/ePayment/Webservice';
	var $soap_action_url = 'https://www.mobile88.com';
	var $ewallet_id = '';
	var $merchant_code = '';
	var $merchant_key = '';
	var $product_description = '';
	var $currency = '';
	var $user_contact = '';
	var $user_email = '';
	var $user_name = '';
	var $backend_url = '';
	var $lang = 'UTF-8';
	
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
		
		if($this->ewallet_type == 'ipay88_all'){
			$this->ewallet_id = 0;
		}else{
			// Create Integrator list
			list($ewallet_type, $integrator_type) = explode("_", $this->ewallet_type, 2);
			$q1 = $con->sql_query("select * from ewallet_integrator_list where integrator_type = ".ms($integrator_type)." and ewallet_type = ".ms($ewallet_type));
			$integrator_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			$this->ewallet_id = $integrator_info['integrator_id'];
		}
	}
	
	private function start(){
		// Run Common Process
		$this->_default();
	}
	
	public function validate_ewallet_arms_setting(){}
	
	public function validate_ewallet_branch_setting(){
		if(!$this->ewallet_branch_settings['merchant_code'] || !$this->ewallet_branch_settings['merchant_key']){
			$this->error_respond('Invalid eWallet Branch Setting');
		}
		
		//data required
		$this->merchant_code = trim($this->ewallet_branch_settings['merchant_code']);
		$this->merchant_key = trim($this->ewallet_branch_settings['merchant_key']);
		$this->user_contact = trim($this->ewallet_branch_settings['user_contact']);
		$this->user_email = trim($this->ewallet_branch_settings['user_email']);
		$this->user_name = trim($this->ewallet_branch_settings['user_name']);
		$this->currency = trim($this->ewallet_branch_settings['currency']);
		$this->product_description = trim($this->ewallet_branch_settings['product_description']);
		
		//data optional field
		$this->terminal_id = mi($this->counter_id);
		$this->backend_url = $this->ewallet_branch_settings['backend_url'] ? trim($this->ewallet_branch_settings['backend_url']) : '';
	}
	
	public function validate_ewallet_counter_setting(){
		if(!$this->counter){
			$this->error_respond('Invalid eWallet Counter');
		}
		$this->counter_pos_id = $this->counter['branch_id']."_".$this->counter['id'];
	}
	
	public function make_payment(){
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
		global $con;
		
		$url = $this->api_url;
		$action_url = $this->soap_action_url;
		$xml_string = $this->generate_XML_string();

		$headers = array(
			'Content-Type: text/xml;charset=UTF-8',
			'soapAction: '.$action_url.'/IGatewayService/EntryPageFunctionality'
		);

		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $url.'/MHGatewayService/GatewayService.svc');
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($s, CURLOPT_HEADER, false);
		
		curl_setopt($s, CURLOPT_POSTFIELDS, $xml_string);
		
		$response = curl_exec($s);
		curl_close($s);
		
		//remove xml data namespace
		$xml_string = preg_replace('/(<\/|<)[a-zA-Z]+:([a-zA-Z0-9]+[ =>])/', '$1$2', $response);
		
		$xml = simplexml_load_string($xml_string);
		
		//convert to json
		$json = json_encode($xml);
		
		//convert to php array
		$result = json_decode($json,TRUE);
		
		if($result['Body']['EntryPageFunctionalityResponse']['EntryPageFunctionalityResult']['Status'] == 1){
			$this->success_info = $result;
			$this->success_ref_no = $result['Body']['EntryPageFunctionalityResponse']['EntryPageFunctionalityResult']['RefNo'];
			
			list($ewallet_type, $integrator_type) = explode("_", $this->ewallet_type, 2);
			if($integrator_type == 'all'){
				$PaymentId = $result['Body']['EntryPageFunctionalityResponse']['EntryPageFunctionalityResult']['PaymentId'];
				
				$q1 = $con->sql_query("select * from ewallet_integrator_list where integrator_id = ".mi($PaymentId)." and ewallet_type = ".ms($ewallet_type));
				$integrator_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				$this->actual_ewallet_type = $integrator_info['integrator_type'];
			}
			$ret['ok'] = 1;
		}else{
			$this->failed_obj = $result;
			
			$err_msg = '';
			$err_msg = $result['Body']['EntryPageFunctionalityResponse']['EntryPageFunctionalityResult']['ErrDesc'];
			$ret['error'] = $err_msg;
		}
		
		return $ret;
	}
	
	public function generate_XML_string(){
		global $appCore;
		
		$amount = $this->transaction_amount;
		$backend_url = $this->backend_url;
		$barcode = $this->customer_token;
		$currency = $this->currency;
		$discounted_amount = '';
		$payment_id = $this->ewallet_id;
		$merchant_code = $this->merchant_code;
		$merchant_key = $this->merchant_key;
		$user_contact = $this->user_contact;
		$user_email = $this->user_email;
		$user_name = $this->user_name;
		$terminal_id = $this->counter_id;
		$product_desc = $this->product_description;
		$ref_no = $appCore->newGUID();
		$remark = trim($_REQUEST['remark']);
		$signature_type = 'SHA256'; //fixed
		$lang = $this->lang;
		$xfield1 = '';
		$xfield2 = '';
		//$xfield1 = trim($_REQUEST['xfield1']);
		//$xfield2 = trim($_REQUEST['xfield2']);
		
		//signature_hash 
		$amount_data = preg_replace("/[^0-9]/", "", number_format($amount, 2));
		$signature = $merchant_key.$merchant_code.$ref_no.$amount_data.$currency.$xfield1.$barcode.$terminal_id;
		$signature_hash = hash('sha256', $signature);
		
		$xml_string = '
		<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mob="https://www.mobile88.com" xmlns:mhp="http://schemas.datacontract.org/2004/07/MHPHGatewayService.Model">
			<soap:Header/>
			<soap:Body>
			  <mob:EntryPageFunctionality>
			  <mob:requestModelObj>
				<mhp:Amount>'.$amount.'</mhp:Amount>
				<mhp:BackendURL>'.$backend_url.'</mhp:BackendURL>
				<mhp:BarcodeNo>'.$barcode.'</mhp:BarcodeNo>
				<mhp:Currency>'.$currency.'</mhp:Currency>
				<mhp:DiscountedAmount>'.$discounted_amount.'</mhp:DiscountedAmount>
				<mhp:MerchantCode>'.$merchant_code.'</mhp:MerchantCode>
				<mhp:PaymentId>'.$payment_id.'</mhp:PaymentId>
				<mhp:ProdDesc>'.$product_desc.'</mhp:ProdDesc>
				<mhp:RefNo>'.$ref_no.'</mhp:RefNo>
				<mhp:Remark>'.$remark.'</mhp:Remark>
				<mhp:Signature>'.$signature_hash.'</mhp:Signature>
				<mhp:SignatureType>'.$signature_type.'</mhp:SignatureType>
				<mhp:TerminalID>'.$terminal_id.'</mhp:TerminalID>
				<mhp:UserContact>'.$user_contact.'</mhp:UserContact>
				<mhp:UserEmail>'.$user_email.'</mhp:UserEmail>
				<mhp:UserName>'.$user_name.'</mhp:UserName>
				<mhp:lang>'.$lang.'</mhp:lang>
				<mhp:xfield1>'.$xfield1.'</mhp:xfield1>
				<mhp:xfield2>'.$xfield2.'</mhp:xfield2>
			  </mob:requestModelObj>
			  </mob:EntryPageFunctionality>
			</soap:Body>
		</soap:Envelope>';
		
		return $xml_string;
	}
	
	public function check_payment_status($prms=array()){
		// Validate
		if(!$this->apiToken){
			//$result = $this->boost_authentication();
			if(!$result['ok']){
				return $result;
			}
		}
	}
	
	public function void_payment($prms=array()){
		$url = $this->api_url;
		
		$merchant_key = $this->merchant_key;
		$merchant_code = $this->merchant_code;
		$cctransid = $_REQUEST['trans_id'];
		$amount = $_REQUEST['amount'];
		$currency = $this->currency;
		
		//remove all symbol
		$amount_data = preg_replace("/[^0-9]/", "", number_format($amount, 2));
		
		//signature_hash 
		$signature = $merchant_key.$merchant_code.$cctransid.$amount_data.$currency;
		$signature_hash = $this->iPay88_signature($signature);
		
		
		$data_list = array(
			"merchantcode" => $merchant_code,
			"cctransid" => $cctransid,
			"currency" => $currency,
			"amount" => $amount,
			"signature" => $signature_hash,
		);
		$data = http_build_query($data_list);
		
		$headers = array(
			'Content-Type: application/x-www-form-urlencoded', 
			'Host: payment.ipay88.com.my',
		);

		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $url.'/VoidAPI/VoidFunction.asmx/VoidTransaction');
		curl_setopt($s, CURLOPT_POST, true);
		curl_setopt($s, CURLOPT_POSTFIELDS, $data);
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec ($s);
		curl_close ($s);
		
		$xml = simplexml_load_string($response);
		
		//convert to json
		$json = json_encode($xml);
		
		//convert to php array
		$result = json_decode($json,TRUE);
		$return_code = $result[0];
		
		$ret = array();
		if($return_code == 0){	// Authentication Success
			$this->success_info = $this->void_message_response($return_code);
			$this->success_ref_no = $cctransid;
			$ret['ok'] = 1;
		}else{	// Failed
			$this->failed_obj = $result;
			
			$err_msg = '';
			if(!$err_msg)	$err_msg = $this->void_message_response($return_code);
			
			$ret['error'] = $err_msg;
		}
		
		return $ret;
	}

	public function iPay88_signature($source){
		return base64_encode($this->hex2bin1(sha1($source)));
	}

	public function hex2bin1($hexSource){
		for ($i=0;$i<strlen($hexSource);$i=$i+2){
			$bin .= chr(hexdec(substr($hexSource,$i,2)));
		}
		return $bin;
	}
	
	//void message code response
	private function void_message_response($code){
		$year = date("Y");
		$status = array(  
			0 => "Approved",
			1 => "Refer to card issuer",
			3 => "Invalid Merchant",
			4 => "Retain Card",
			5 => "Do not honor",
			6 => "System error",
			7 => "Pick up card (special)",
			12 => "Invalid transaction",
			13 => "Invalid Amount",
			14 => "Invalid card number",
			15 => "Invalid issuer",
			19 => "System timeout",
			20 => "Invalid response",
			21 => "No action taken",
			22 => "Suspected malfunction",
			30 => "Format error",
			33 => "Expired card",
			34 => "Suspected fraud",
			36 => "Restricted card",
			41 => "Pick up card (lost)",
			43 => "Pick up card (stolen)",
			51 => "Not sufficient funds",
			54 => "Expired card",
			59 => "Suspected fraud",
			61 => "Exceeds withdrawal limit",
			62 => "Restricted card",
			63 => "Security violation",
			65 => "Activity count exceeded",
			91 => "Issuer or switch inoperative",
			96 => "System malfunction",
			1001 => "Merchant Code is empty",
			1002 => "Transaction ID is empty",
			1003 => "Amount is empty",
			1004 => "Currency is empty",
			1005 => "Signature is empty",
			1006 => "Signature not match",
			1007 => "Invalid Amount",
			1008 => "Invalid Currency",
			1009 => "Invalid Merchant Code",
			1010 => "This transaction is not eligible for voiding",
			1011 => "Transaction not foundCopyright ©iPay88 (M) Sdn Bhd ".$year.". All rights reserved. 9 of 9 Private & Confidential",
			1012 => "Connection error",
			9999 => "Transaction already voided"
		); 
		
		return ($status[$code])?$status[$code]:$status[8]; 
	}
}
$EWALLET_API_IPAY88 = new EWALLET_API_IPAY88();
?>