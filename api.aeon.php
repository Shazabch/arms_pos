<?php
/*
4/13/2021 9:28 AM Andy
- Added new ARMS AEON API.

4/27/2021 2:58 PM Andy
- Fixed invoice_pdf_path.
- Added branch_code for api get_member_tran_list and get_tran_details
*/

include("include/common.php");
include("include/price_checker.include.php");
ini_set('memory_limit', '2G');
set_time_limit(0);

class ARMS_AEON_API {
	var $is_debug = 0;
	var $folder = "attch/api.aeon";
	var $log_folder = "attch/api.aeon/logs";
	
	var $rcv_data = array();
	var $settings = array();
	var $call_method = '';
	var $http_method = '';
	
	// Error List
	var $err_list = array(
		"x_access_token_empty" => "X_ACCESS_TOKEN is Required.",
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

		$this->put_log("Checking ACCESS TOKEN: ".$X_ACCESS_TOKEN);
		
		// Must have config
		if(!$config['arms_aeon_settings']){
			$this->error_die("config_not_set", $this->err_list['config_not_set']);
		}
		
		if(!$X_ACCESS_TOKEN){
			$this->error_die("x_access_token_empty", $this->err_list['x_access_token_empty']);
		}
		
		// Check Access Token
		if(!isset($config['arms_aeon_settings']['access_token'])){
			$this->error_die("config_error", $this->err_list['config_error']);
		}else{
			if(md5($config['arms_aeon_settings']['access_token']) != strtolower($X_ACCESS_TOKEN)){
				$this->error_die("authen_failed", $this->err_list['authen_failed']);
			}
		}
				
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
	
	private function get_member_tran_list(){
		global $con, $con_multi, $appCore;
		
		// Member No
		$member_no = trim($this->rcv_data['member_no']);
		$limit = mi($this->rcv_data['limit']);
		if(!$member_no){
			$this->error_die("invalid_data", sprintf($this->err_list["invalid_data"], 'member_no'));
		}
		
		$ret = array();
		$ret['result'] = 1;
		$ret['pos_list'] = array();
		
		// Connect Report Server
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		$filter = array();
		$filter[] = "p.member_no=".ms($member_no)." and p.cancel_status=0";
		
		if(!$limit || $limit > 30)	$limit = 30;
		$str_filter = "where ".join(' and ', $filter);
		
		$sql = "select p.*, (select sum(pp.amount) from pos_payment pp where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type in ('Discount', 'Mix & Match Total Disc') and pp.adjust=0) as total_disc_amt, b.code as bcode
			from pos p
			left join branch b on b.id=p.branch_id
			$str_filter
			order by p.date desc 
			limit $limit";
		$q1 = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q1)){
			$tmp = array();
			$tmp['branch_code'] = $r['bcode'];
			$tmp['date'] = $r['date'];
			$tmp['counter_id'] = $r['counter_id'];
			$tmp['pos_time'] = $r['pos_time'];
			$tmp['receipt_ref_no'] = $r['receipt_ref_no'];
			$tmp['amount'] = round($r['amount_tender'] - $r['amount_change'] - $r['total_disc_amt'], 2);
			
			// Check if pdf receipt is available
			$tmp['invoice_pdf'] = '';
			$invoice_pdf_path = "attch/pos_pdf_invoice/".$r['branch_id']."/".$r['date']."/".$r['receipt_ref_no'].".pdf";
			if(file_exists($invoice_pdf_path)){
				$tmp['invoice_pdf'] = $invoice_pdf_path;
			}
			$ret['pos_list'][] = $tmp;
		}
		$con_multi->sql_freeresult($q1);
		
		//$ret['member_no'] = $member_no;
		
		// Return Data				
		$this->respond_data($ret);
	}
	
	private function get_tran_details(){
		global $con, $con_multi, $appCore;
		
		// Member No
		$receipt_ref_no = trim($this->rcv_data['receipt_ref_no']);
		if(!$receipt_ref_no){
			$this->error_die("invalid_data", sprintf($this->err_list["invalid_data"], 'receipt_ref_no'));
		}
		
		// Connect Report Server
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		// Get POS
		$con_multi->sql_query("select p.*, (select sum(pp.amount) from pos_payment pp where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type in ('Discount', 'Mix & Match Total Disc') and pp.adjust=0) as total_disc_amt, b.code as bcode
		from pos p 
		left join branch b on b.id=p.branch_id
		where p.receipt_ref_no=".ms($receipt_ref_no)." and p.cancel_status=0");
		$pos = $con_multi->sql_fetchassoc();
		$con_multi->sql_freeresult();
		
		if(!$pos){
			$this->error_die("data_not_found", sprintf($this->err_list["data_not_found"], 'POS'));
		}
		
		$ret = array();
		$ret['result'] = 1;
		$ret['pos_header'] = array();
		$ret['pos_header']['branch_code'] = $pos['bcode'];
		$ret['pos_header']['date'] = $pos['date'];
		$ret['pos_header']['counter_id'] = $pos['counter_id'];
		$ret['pos_header']['pos_time'] = $pos['pos_time'];
		$ret['pos_header']['receipt_ref_no'] = $pos['receipt_ref_no'];
		$ret['pos_header']['amount'] = round($pos['amount_tender'] - $pos['amount_change'] - $pos['total_disc_amt'], 2);
		
		// Check if pdf receipt is available
		$ret['pos_header']['invoice_pdf'] = '';
		$invoice_pdf_path = "attch/pos_pdf_invoice/".$pos['branch_id']."/".$pos['date']."/".$pos['receipt_ref_no'].".pdf";
		if(file_exists($invoice_pdf_path)){
			$ret['pos_header']['invoice_pdf'] = $invoice_pdf_path;
		}
			
		// Get POS Items
		$ret['pos_items'] = array();
		$q1 = $con_multi->sql_query("select pi.*, si.mcode, si.link_code
			from pos_items pi
			left join sku_items si on si.id=pi.sku_item_id
			where pi.branch_id=".mi($pos['branch_id'])." and pi.date=".ms($pos['date'])." and pi.counter_id=".mi($pos['counter_id'])." and pi.pos_id=".mi($pos['id'])." order by pi.id");
		while($pi = $con_multi->sql_fetchassoc($q1)){
			$tmp = array();
			$tmp['item_id'] = $pi['item_id'];
			$tmp['barcode'] = $pi['link_code'] ? $pi['link_code'] : $pi['mcode'];
			$tmp['sku_description'] = $pi['sku_description'];
			$tmp['qty'] = $pi['qty'];
			$tmp['price'] = round($pi['price'], 2);
			$tmp['discount'] = round($pi['discount'], 2);
			$tmp['final_price'] = round($pi['price'] - $pi['discount'], 2);
			$ret['pos_items'][] = $tmp;
		}
		$con_multi->sql_freeresult($q1);
		
		// Get POS Discount
		$ret['pos_discount'] = array();
		$q2 = $con_multi->sql_query("select pp.*
			from pos_payment pp
			where pp.branch_id=".mi($pos['branch_id'])." and pp.date=".ms($pos['date'])." and pp.counter_id=".mi($pos['counter_id'])." and pp.pos_id=".mi($pos['id'])." and pp.adjust=0 and pp.type in ('Discount', 'Mix & Match Total Disc') order by pp.id");
		while($r = $con_multi->sql_fetchassoc($q2)){
			$tmp = array();
			$tmp['type'] = $r['type'];
			$tmp['amount'] = round($r['amount'], 2);
			$ret['pos_discount'][] = $tmp;
		}
		$con_multi->sql_freeresult($q2);
		
		// Get Rounding
		$ret['pos_rounding'] = 0;
		$con_multi->sql_query("select pp.*
			from pos_payment pp
			where pp.branch_id=".mi($pos['branch_id'])." and pp.date=".ms($pos['date'])." and pp.counter_id=".mi($pos['counter_id'])." and pp.pos_id=".mi($pos['id'])." and pp.adjust=0 and pp.type='Rounding' order by pp.id limit 1");
		$tmp = $con_multi->sql_fetchassoc();
		$con_multi->sql_freeresult();
		if($tmp){
			$ret['pos_rounding'] = round($tmp['amount'], 2);
		}
		
		// Get POS Payment
		$ret['pos_payment'] = array();
		$q3 = $con_multi->sql_query("select pp.*
			from pos_payment pp
			where pp.branch_id=".mi($pos['branch_id'])." and pp.date=".ms($pos['date'])." and pp.counter_id=".mi($pos['counter_id'])." and pp.pos_id=".mi($pos['id'])." and pp.adjust=0 and pp.type not in ('Discount', 'Mix & Match Total Disc', 'Rounding') order by pp.id");
		while($r = $con_multi->sql_fetchassoc($q3)){
			$tmp = array();
			$tmp['type'] = $r['type'];
			$tmp['amount'] = round($r['amount'], 2);
			$ret['pos_payment'][] = $tmp;
		}
		$con_multi->sql_freeresult($q3);
		$ret['amount_change'] = round($pos['amount_change'], 2);
		
		// Return Data				
		$this->respond_data($ret);
	}
}

$ARMS_AEON_API = new ARMS_AEON_API();
$ARMS_AEON_API->_default();
?>
