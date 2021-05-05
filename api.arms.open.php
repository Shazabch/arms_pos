<?php
/*
1/26/2021 10:10 AM Andy
- Added new ARMS Open API.
*/

include("include/common.php");
include("include/price_checker.include.php");
ini_set('memory_limit', '1024M');
set_time_limit(0);

class ARMS_OPEN_API {
	var $is_debug = 0;
	var $folder = "attch/api.arms.open";
	var $log_folder = "attch/api.arms.open/logs";
	
	var $rcv_data = array();
	var $settings = array();
	var $call_method = '';
	var $http_method = '';
	
	// Error List
	var $err_list = array(
		"x_access_token_empty" => "X_ACCESS_TOKEN is Required.",
		"x_branch_code_empty" => "X_BRANCH_CODE is Required.",
		"config_not_set" => "No Configuration Found",
		"config_error" => "Configuration Error",
		"invalid_api_method" => "Invalid API Method.",
		"invalid_http_method" => "Invalid HTTP Method.",
		"authen_failed" => "Authentication Failed.",
		"invalid_branch" => "Invalid Branch '%s'.",
		"branch_is_inactive" => "Branch %s are inactive.",
		"data_not_found" => "No %s were found.",
		"invalid_data" => "Invalid Data '%s'.",
		"unknown_error" => "Unknown Error",
		"sku_not_found" => "SKU '%s' Not Found.",
		"no_barcode" => "No Barcode is found.",
	);
	
	var $device_ip = '';
	var $branch = array();
	var $branch_id = 0;
	
	function __construct(){
		global $config;
		
		$this->construct_return_header();
		
		if($_SERVER['SERVER_NAME'] == 'maximus')	$this->is_debug = 1;
		
		// Convert all header to uppercase
		$tmp_header = getallheaders();
		if($tmp_header){
			foreach($tmp_header as $k=>$v){
				$k = str_replace('-', '_', strtoupper($k));
				$this->all_header[$k] = $v;
			}
		}
		
		// Initialise Folder and Database
		$this->prepareDB();
		
		// Log Action Called
		$a = '';
		if(isset($_REQUEST['a'])){
			$this->call_method = trim($_REQUEST['a']);
		}
		$this->device_ip = $_SERVER['REMOTE_ADDR'];
		$this->put_log("Calling Method [".$this->call_method."], IP: ".$this->device_ip);
		
		// Check if method not exists
		//if(!$this->call_method || !method_exists($this, $this->call_method)){
		//	$this->error_die("invalid_api_method", $this->err_list["invalid_api_method"]);
		//}
	}
	
	// function to create all db and folder
	private function prepareDB(){
		global $con;
		
		// Create Program Main Folder
		check_and_create_dir($this->folder);
		
		// Create Program Logs Folder
		check_and_create_dir($this->log_folder);
	}
	
	private function construct_return_header(){
		header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        //header("Access-Control-Allow-Methods: 'GET, POST, OPTIONS'");
		header("Access-Control-Allow-Headers: *");
		header('Access-Control-Allow-Credentials: true');
		//header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header("Content-Type: application/json");
	}
	
	private function put_log($log){
		$filename = date("Y-m-d").".txt";
		$str = date("Y-m-d H:i:s")."; ".$log;
		file_put_contents($this->log_folder."/".$filename, $str."\r\n", FILE_APPEND);
	}
	
	private function error_die($err_key, $err_msg){
		$this->put_log($err_msg);
		
		$ret = array();
		$ret['result'] = 0;
		$ret['error_key'] = $err_key;
		$ret['error_msg'] = $err_msg;
		
		print json_encode($ret);
		exit;
	}
	
	private function get_post_json_data(){
		$this->rcv_data = json_decode(file_get_contents('php://input'), true);
		
		//print_r($this->rcv_data);exit;
	}
	
	private function respond_data($ret){
		$this->put_log("Sending Data.");
		print json_encode($ret);
		$this->put_log("Data sent.");
		exit;
	}
	
	function _default(){
		//print_r($_SERVER);
		// Load Settings
		//$this->load_settings();
		
		// Check API Key
		$this->check_api_key();
		
		// Check whether it is POST, GET
		$this->check_method();
		
		// Process function
		$this->process_action();
	}
	
	private function check_api_key(){
		global $con, $config;
		
		$X_ACCESS_TOKEN = isset($this->all_header['X_ACCESS_TOKEN']) ? trim($this->all_header['X_ACCESS_TOKEN']) : '';
		$X_BRANCH_CODE = isset($this->all_header['X_BRANCH_CODE']) ? trim($this->all_header['X_BRANCH_CODE']) : '';

		$this->put_log("Checking ACCESS TOKEN: ".$X_ACCESS_TOKEN);
		$this->put_log("Checking Branch Code: ".$X_BRANCH_CODE);
		
		
		if(!$X_ACCESS_TOKEN){
			$this->error_die("x_access_token_empty", $this->err_list['x_access_token_empty']);
		}
		if(!$X_BRANCH_CODE){
			$this->error_die("x_branch_code_empty", $this->err_list['x_branch_code_empty']);
		}
		$X_BRANCH_CODE = strtoupper($X_BRANCH_CODE);
		
		// Must have config
		if(!$config['arms_open_settings']){
			$this->error_die("config_not_set", $this->err_list['config_not_set']);
		}
		
		// Check Access Token
		if(!isset($config['arms_open_settings']['access_token'][$X_BRANCH_CODE])){
			$this->error_die("config_error", $this->err_list['config_error']);
		}else{
			if(md5($config['arms_open_settings']['access_token'][$X_BRANCH_CODE]) != strtolower($X_ACCESS_TOKEN)){
				$this->error_die("authen_failed", $this->err_list['authen_failed']);
			}
		}
		
		// Check Branch
		$con->sql_query("select id, code, description from branch where code=".ms($X_BRANCH_CODE)." and active=1");
		$branch = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if(!$branch){
			$this->error_die("invalid_branch", sprintf($this->err_list["invalid_branch"], $X_BRANCH_CODE));
		}
		
		$this->branch = $branch;
		$this->branch_id = $branch['id'];
				
		// Validate Success
		$this->put_log("Authentication Success");
	}
	
	private function check_method(){
		$this->http_method = $_SERVER['REQUEST_METHOD'];
        if ($this->http_method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->http_method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->http_method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }
		
		$this->put_log("HTTP Method: ".$this->http_method);
		
		switch($this->http_method) {
			case 'DELETE':
			case 'POST':
				//$this->request = $this->_cleanInputs($_POST);
				$this->get_post_json_data();
				break;
			case 'GET':
				//$this->request = $this->_cleanInputs($_GET);
				break;
			case 'PUT':
				//$this->request = $this->_cleanInputs($_GET);
				//$this->file = file_get_contents("php://input");
				break;
			default:
				$this->error_die("invalid_http_method", $this->err_list["invalid_http_method"]);
				break;
        }
	}
	
	private function process_action(){
		// Check if method not exists
		if(!$this->call_method || !method_exists($this, $this->call_method)){
			$this->error_die("invalid_api_method", $this->err_list["invalid_api_method"]);
		}
		
		$this->{$this->call_method}();
	}
	
	public function get_product_details(){
		global $con, $appCore;
		
		$barcode = trim($this->rcv_data['barcode']);
		if(!$barcode)	$this->error_die("no_barcode", $this->err_list["no_barcode"]);
		
		// ARMS Code only take 12 digits
		if (preg_match('/^28/', $barcode)) $barcode = substr($barcode,0,12);

		$params = array();
		$params['branch_id'] = $this->branch_id;
		$params['code'] = $barcode;
		//$params['get_cat_info'] = 1;
		$sku = check_price($params);
		
		
		if(!isset($sku['price'])){
			// Got Error
			if($sku['error'] == 'item_not_found'){
				// SKU Not Found
				$this->error_die("sku_not_found", sprintf($this->err_list["sku_not_found"], $barcode));
			}else{
				// Unknown Error
				$this->error_die("unknown_error", $this->err_list["unknown_error"]);
			}
		}else{
			// POS Photo
			$pos_photo_list = $appCore->skuManager->getSKUItemPromoPhotos($sku['id']);
			
			// No Error
			$ret = array();
			// Construct Data to return
			$ret['result'] = 1;
			
			$ret['sku_data'] = array();
			
			$return_fields = array('sku_item_code', 'mcode', 'link_code', 'artno', 'description', 'receipt_description', 'size', 'color', 'default_price', 'non_member_discount', 'non_member_price', 'member_discount', 'member_price');
			$round2_fields = array('default_price', 'non_member_discount', 'non_member_price', 'member_discount', 'member_price');
			foreach($return_fields as $f){
				if(in_array($f, $round2_fields)){
					$ret['sku_data'][$f] = round($sku[$f], 2);
				}else{
					$ret['sku_data'][$f] = trim($sku[$f]);
				}
			}
			
			$ret['sku_data']['photos'] = $sku['photos'];
			$ret['sku_data']['pos_photo_url'] = '';
			
			if($pos_photo_list){
				$ret['sku_data']['pos_photo_url'] = $pos_photo_list[0];
			}
			
			
			// Return Data				
			$this->respond_data($ret);
		}
		
	}
}

$ARMS_OPEN_API = new ARMS_OPEN_API();
$ARMS_OPEN_API->_default();
?>
