<?php
/*
1/8/2020 2:41 PM Andy
- New API for OSTrio WMS.
*/
include("include/common.php");
//include("include/price_checker.include.php");
ini_set('memory_limit', '1024M');
set_time_limit(0);

class ARMS_WMS_API {
	var $is_debug = 0;
	var $folder = "attch/api.arms.wms";
	var $log_folder = "attch/api.arms.wms/logs";
	
	var $rcv_data = array();
	var $settings = array();
	var $call_method = '';
	var $http_method = '';
	var $wms_user_id = 0;
	
	
	// Error List
	var $err_list = array(
		"config_not_set" => "No Configuration Found",
		"config_error" => "Configuration Error",
		"invalid_api_method" => "Invalid API Method.",
		"invalid_http_method" => "Invalid HTTP Method.",
		"authen_failed" => "Authentication Failed.",
		"invalid_branch" => "Invalid Branch %s.",
		"data_not_found" => "No %s were found.",
		"invalid_data" => "Invalid Data '%s'.",
		"invalid_date_format" => "Invalid Date Format for '%s'.",
		"unknown_error" => "Unknown Error",
		"data_duplicated" => "'%s' '%s' is duplicated.",
		"date_from_to_error" => "%s is earlier than %s",
		"row_data_error" => "Data '%s' in '%s' row '%s' is invalid",
		"row_data_required" => "Data '%s' in '%s' row '%s' is required",
		"row_data_not_found" => "Data not found in '%s' row '%s'",
		"row_data_not_match" => "Data '%s' or '%s' not found in '%s' and '%s' row",
	);
	
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
		if(!$this->call_method || !method_exists($this, $this->call_method)){
			$this->error_die("invalid_api_method", $this->err_list["invalid_api_method"]);
		}
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
		$this->load_settings();
		
		// Check API Key
		$this->check_api_key();
		
		// Check whether it is POST, GET
		$this->check_method();
		
		// Process function
		$this->process_action();
	}
	
	private function load_settings(){
		global $con, $config;
		
		if(isset($config['arms_wms_settings']['user_id'])){
			$this->wms_user_id = mi($config['arms_wms_settings']['user_id']);
		}
		// Normal Settings
		$this->settings = array();
		/*$con->sql_query("select * from marketplace_settings");
		while($r = $con->sql_fetchassoc()){
			$this->settings['normal_settings'][$r['setting_name']] = $r['setting_value'];
		}
		$con->sql_freeresult();
		
		// Check Shipping Item Code
		if(isset($this->settings['normal_settings']['shipping_item_code']) && $this->settings['normal_settings']['shipping_item_code']){
			// Check if it is actual ARMS Code
			$shipping_item_code = trim($this->settings['normal_settings']['shipping_item_code']);
			if(preg_match("/^28/", $shipping_item_code) && strlen($shipping_item_code) == 12){
				$con->sql_query("select id from sku_items where sku_item_code=".ms($shipping_item_code));
				$si = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($si){
					// Is Actual SKU
					$this->shipping_item_sid = mi($si['id']);
				}
			}
		}*/
	}
	
	private function check_api_key(){
		global $con, $config;
		
		$X_ACCESS_TOKEN = isset($this->all_header['X_ACCESS_TOKEN']) ? trim($this->all_header['X_ACCESS_TOKEN']) : '';

		$this->put_log("Checking ACCESS TOKEN: ".$X_ACCESS_TOKEN);
		
		// Must have config
		if(!$config['arms_wms_settings']){
			$this->error_die("config_not_set", $this->err_list['config_not_set']);
		}
		
		// Check Access Token
		if(!$config['arms_wms_settings']['access_token']){
			$this->error_die("config_error", $this->err_list['config_error']);
		}else{
			if(md5($config['arms_wms_settings']['access_token']) != strtolower($X_ACCESS_TOKEN)){
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
		$this->{$this->call_method}();
	}
	
	function get_sku_image(){
		global $con, $appCore;
		
		$filter = array();
		
		// check sku_item_id_list
		if($this->rcv_data['sku_item_id_list'] && is_array($this->rcv_data['sku_item_id_list'])){ // it is filter by sku item id list
			$filter[] = "si.id in (".join(",", $this->rcv_data['sku_item_id_list']).")";
		}else{ // no sku item id
			$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "sku_item_id_list"));
		}
		
		if(!$filter)	$this->error_die("data_not_found", $this->err_list['data_not_found']);
		$str_filter = "where ".join(' and ', $filter);
		$sql = "select si.id, si.sku_apply_items_id, si.artno
				from sku_items si
				$str_filter
				order by si.id";
		//print $sql;exit;
		$sku_data = array();
		
		$q1 = $con->sql_query($sql);
		while($si = $con->sql_fetchassoc($q1)){
			$r = array();
			$r['id'] = $si['id'];
			$r['promo_photo_url'] = '';
			
			// get only first promo photo
			$promo_photo_list = $appCore->skuManager->getSKUItemPromoPhotos($si['id']);
			if($promo_photo_list){
				$r['promo_photo_url'] = trim($promo_photo_list[0]);
			}
			
			// get sku apply photo, photo and promotion photo
			$all_photo_list = array();
			$apply_photo_list = get_sku_apply_item_photos($si['sku_apply_items_id']);
			$photo_list = get_sku_item_photos($si['id'], $si);
			
			$r['photo_list'] = array();
			if(!$apply_photo_list) $apply_photo_list = array();
			if(!$photo_list) $photo_list = array();
			$all_photo_list = array_merge($apply_photo_list, $photo_list);
			
			// Got Photo
			if($all_photo_list){
				foreach($all_photo_list as $photo_path){
					$photo_details = array();
					$photo_details['abs_path'] = $photo_path;
					if(file_exists($photo_path)){
						$photo_details['last_update'] = date("Y-m-d H:i:s", filemtime($photo_path));
						//$photo_details['name'] = basename($photo_path);			
						$r['photo_list'][] = $photo_details;
					}
					unset($photo_details);
				}
			}
			unset($all_photo_list);
			
			$sku_data[] = $r;
		}
		$con->sql_freeresult($q1);		
		
		$ret = array();
		// Construct Data to return
		$ret['result'] = 1;
		$ret['sku_data'] = $sku_data;
			
		// Return Data				
		$this->respond_data($ret);
	}
	
	function get_do_request_items(){
		global $con, $appCore;
		
		//print_r($this->rcv_data);exit;
		
		$status = trim($this->rcv_data['status']);
		$date_from = trim($this->rcv_data['date_from']);
		$date_to = trim($this->rcv_data['date_to']);
		
		if(!$status)	$status = 'wip';
		if($status != 'wip' && $status != 'complete' && $status != 'cancel'){
			$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "status"));
		}
		if($date_from){
			if(!$appCore->isValidDateFormat($date_from)){
				$this->error_die("invalid_date_format", sprintf($this->err_list['invalid_date_format'], "date_from"));
			}
		}
		if($date_to){
			if(!$appCore->isValidDateFormat($date_to)){
				$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "date_to"));
			}
		}
		if($date_from && $date_to){
			if(strtotime($date_from) > strtotime($date_to)){
				$this->error_die("date_from_to_error", sprintf($this->err_list['date_from_to_error'], "date_to", "date_from"));
			}
		}
		
		$filter = array();
		$filter[] = "dri.active=1";
		switch($status){
			case 'complete':	// Completed
				$filter[] = "dri.status=2";
				break;
			case 'cancel':	// Cancelled
				$filter[] = "dri.status=4";
				break;
			default:	// Saved / Processing
				$filter[] = "dri.status in (0,1)";
				break;
		}
		if($date_from)	$filter[] = "dri.added>=".ms($date_from);
		if($date_to)	$filter[] = "dri.added<=".ms($date_to." 23:59:59");
		
		$str_filter = "where ".join(' and ', $filter);
		
		$sql = "select dri.*
			from do_request_items dri 
			$str_filter
			order by dri.added";
		//print $sql;exit;
		
		$ret = array();
		// Construct Data to return
		$ret['result'] = 1;
		$ret['items_list'] = array();
		
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			
			$tmp = array();
			$tmp['branch_id'] = $r['branch_id'];
			$tmp['id'] = $r['id'];
			$tmp['request_branch_id'] = $r['request_branch_id'];
			$tmp['sku_item_id'] = $r['sku_item_id'];
			$tmp['default_request_qty'] = mf($r['default_request_qty']);
			$tmp['total_do_qty'] = mf($r['total_do_qty']);
			$tmp['request_qty'] = mf($r['request_qty']);
			if($r['status'] == 2){
				$tmp['status'] = 'complete';
			}elseif($r['status'] == 4){
				$tmp['status'] = 'cancel';
			}elseif($r['status'] == 0 || $r['status'] == 1){
				$tmp['status'] = 'wip';
			}elseif($r['status'] == 3){
				// should not come here
				$tmp['status'] = 'reject';
			}
			$tmp['added'] = $r['added'];
			
			$ret['items_list'][] = $tmp;
		}
		$con->sql_freeresult($q1);
		
		// Return Data				
		$this->respond_data($ret);
	}
	
	function update_do_request_items_status(){
		global $con, $appCore;
		
		// check do_request_items_data
		if(!$this->rcv_data['do_request_items_data'] || !is_array($this->rcv_data['do_request_items_data'])){
			$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "do_request_items_data"));
		}
		
		// check item
		$update_data = array();
		foreach($this->rcv_data['do_request_items_data'] as $row => $r){
			$branch_id = mi($r['branch_id']);
			$id = mi($r['id']);
			$status = trim($r['status']);
			$total_do_qty = mf($r['total_do_qty']);
			
			// branch_id
			if($branch_id <= 0){
				$this->error_die("row_data_error", sprintf($this->err_list['row_data_error'], "branch_id", "do_request_items_data", $row+1));
			}
			
			// id
			if($id <= 0){
				$this->error_die("row_data_error", sprintf($this->err_list['row_data_error'], "id", "do_request_items_data", $row+1));
			}
			
			// status
			if($status != 'wip' && $status != 'complete' && $status != 'cancel'){
				$this->error_die("row_data_error", sprintf($this->err_list['row_data_error'], "status", "do_request_items_data", $row+1));
			}
			
			// if status is wip, must have total_do_qty
			if($status == 'wip'){
				if($total_do_qty <= 0){
					$this->error_die("row_data_required", sprintf($this->err_list['row_data_required'], "total_do_qty", "do_request_items_data", $row+1));
				}
			}
			
			// Get item from db
			$con->sql_query("select * from do_request_items where active=1 and branch_id=$branch_id and id=$id");
			$dri = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$dri)	$this->error_die("row_data_not_found", sprintf($this->err_list['row_data_not_found'], "do_request_items_data", $row+1));
			
			$tmp = array();
			$tmp['branch_id'] = $branch_id;
			$tmp['id'] = $id;
			if($status == 'complete'){	// complete
				$tmp['status'] = 2;
				$tmp['request_qty'] = 0;
				$tmp['total_do_qty'] = $dri['default_request_qty'] - $dri['po_qty'];
			}elseif($status == 'cancel'){	// cancel
				$tmp['status'] = 4;
			}elseif($status == 'wip'){
				$tmp['status'] = 0;
				$tmp['total_do_qty'] = $total_do_qty;
				$tmp['request_qty'] = $dri['default_request_qty'] - $dri['po_qty'] - $total_do_qty;
			}
			$tmp['last_update'] = 'CURRENT_TIMESTAMP';
			
			$update_data[] = $tmp;
		}
		
		//print_r($update_data);exit;
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		foreach($update_data as $r){
			$branch_id = mi($r['branch_id']);
			$id = mi($r['id']);
			
			$upd = $r;
			unset($upd['branch_id'], $upd['id']);
			
			$con->sql_query("update do_request_items set ".mysql_update_by_field($upd)." where branch_id=$branch_id and id=$id");
			
			$str = '';
			if($r['status'] == 2){
				$str = "WMS Completed Item, Branch ID: $branch_id, Item ID: $id";
			}elseif($r['status'] == 4){
				$str = "WMS Cancel Item, Branch ID: $branch_id, Item ID: $id";
			}elseif($r['status'] == 0){
				$str = "WMS Update Request Qty, Branch ID: $branch_id, Item ID: $id, Shipped Qty: ".$upd['total_do_qty'].", Remaining Qty: ".$upd['request_qty'];
			}
			
			// log
			log_br(1, 'DO Request', $id, $str);
		}
		
		// Commit Transaction
		$con->sql_commit();
		
		$ret = array();
		// Construct Data to return
		$ret['result'] = 1;
		// Return Data
		$this->respond_data($ret);
	}
	
	function create_grn(){
		global $con, $appCore, $config;
		
		if(!$this->rcv_data['items_data'] || !is_array($this->rcv_data['items_data'])){
			$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "items_data"));
		}
		
		$branch_id = mi($this->rcv_data['branch_id']);
		$branch_code = trim($this->rcv_data['branch_code']);
		$doc_no = trim($this->rcv_data['doc_no']);
		$doc_date = trim($this->rcv_data['doc_date']);
		$vendor_id = mi($this->rcv_data['vendor_id']);
		$vendor_code = trim($this->rcv_data['vendor_code']);
		$dept_id = mi($this->rcv_data['dept_id']);
		$dept_code = trim($this->rcv_data['dept_code']);
		$rcv_date = trim($this->rcv_data['rcv_date']);
		$lorry_no = trim($this->rcv_data['lorry_no']);
		$total_amount_incl_tax = mf($this->rcv_data['total_amount_incl_tax']);
		$tax_percent = mf($this->rcv_data['tax_percent']);
		$tax_amount = mf($this->rcv_data['tax_amount']);
		$rcv_pcs = mf($this->rcv_data['rcv_pcs']);
		
		// branch_id
		if($branch_id <= 0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "branch_id"));
		if(!$branch_code)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "branch_code"));
		
		// vendor_id
		if($vendor_id <= 0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "vendor_id"));
		if(!$vendor_code)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "vendor_code"));
		
		// dept_id
		if($dept_id <= 0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "dept_id"));
		if(!$dept_code)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "dept_code"));
		
		// Document
		if(!$doc_no)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "doc_no"));
		if(!$doc_date)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "doc_date"));
		if(!$rcv_date)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "rcv_date"));
		if(!$appCore->isValidDateFormat($doc_date))	$this->error_die("invalid_date_format", sprintf($this->err_list['invalid_date_format'], "doc_date"));
		if(!$appCore->isValidDateFormat($rcv_date))	$this->error_die("invalid_date_format", sprintf($this->err_list['invalid_date_format'], "rcv_date"));

		// Lorry
		if(!$lorry_no)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "lorry_no"));
		
		// Amount
		if($total_amount_incl_tax <= 0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "total_amount_incl_tax"));
		if($tax_percent < 0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "tax_percent"));
		if($tax_amount < 0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "tax_amount"));
		if($rcv_pcs <= 0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "rcv_pcs"));
		
		//checking doc_no duplicated
		$q = $con->sql_query("select doc_no from grr_items where grn_used=1 and branch_id=$branch_id and doc_no=".ms($doc_no));
		if($con->sql_numrows($q) > 0) $this->error_die("data_duplicated", sprintf($this->err_list['data_duplicated'], "doc_no", $doc_no));
		$con->sql_freeresult($q);
		
		//checking branch_code and branch_id
		$q1 = $con->sql_query("select code from branch where active=1 and id=$branch_id and code=".ms($branch_code));
		if($con->sql_numrows($q1) <= 0) $this->error_die("row_data_not_match", sprintf($this->err_list['row_data_not_match'], $branch_id, $branch_code, "branch_id", "branch_code"));
		$con->sql_freeresult($q1);
		
		//checking vendor_code and vendor_id
		$q2 = $con->sql_query("select code from vendor where active=1 and id=$vendor_id and code=".ms($vendor_code));
		if($con->sql_numrows($q2) <= 0) $this->error_die("row_data_not_match", sprintf($this->err_list['row_data_not_match'], $vendor_id, $vendor_code, "vendor_id", "vendor_code"));
		$con->sql_freeresult($q2);
		
		//checking dept id
		$q3 = $con->sql_query("select id from category where level=2 and active=1 and id=$dept_id and code=".ms($dept_code));
		if($con->sql_numrows($q3) <= 0) $this->error_die("row_data_not_match", sprintf($this->err_list['row_data_not_match'], $dept_id, $dept_code, "dept_id", "dept_code"));
		$con->sql_freeresult($q3);
		
		//checking grr items
		$grr = $grr_items = $grn = $grn_items = $tmp = array();
		foreach($this->rcv_data['items_data'] as $row=>$r){
			$sku_item_id = mi($r['sku_item_id']);
			$sku_item_code = trim($r['sku_item_code']);
			$uom_id = mi($r['uom_id']);
			$uom_code = trim($r['uom_code']);
			$pcs = mf($r['pcs']);
			$cost = mf($r['cost']);
			
			$q4 = $con->sql_query("select active from sku_items where id=$sku_item_id and sku_item_code=".ms($sku_item_code));
			if($con->sql_numrows($q4) <= 0){
				$this->error_die("row_data_not_match", sprintf($this->err_list['row_data_not_match'], $sku_item_id, $sku_item_code, "sku sku_item_id", "sku_item_code"));
			}
			$r4 = $con->sql_fetchrow($q4);
			if($r4['active'] == 0){
				$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], $sku_item_id." not active"));
			}
			$con->sql_freeresult($q4);
			
			$q5 = $con->sql_query("select code from uom where active=1 and id=$uom_id and code=".ms($uom_code));
			if($con->sql_numrows($q5) <= 0){
				$this->error_die("row_data_not_match", sprintf($this->err_list['row_data_not_match'], $uom_id, $uom_code, "uom_id", "uom_code"));
			}
			$con->sql_freeresult($q5);
			
			if($pcs <= 0) $this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "pcs"));
			if($cost <= 0) $this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "cost"));
			
			$tmp['sku_item_id'] = $sku_item_id;
			$tmp['sku_item_code'] = $sku_item_code;
			$tmp['uom_id'] = $uom_id;
			$tmp['pcs'] = $pcs;
			$tmp['cost'] = $cost;
			$total_cost += $cost;
			$tmp['last_update'] = 'CURRENT_TIMESTAMP';
			$grn_items[] = $tmp;
		}
		
		//insert grr
		$con->sql_begin_transaction();
		$grr_id = $appCore->generateNewID("grr", "branch_id=$branch_id");
		$grr['id'] = $grr_id;
		$grr['user_id'] = $this->wms_user_id;
		$grr['rcv_by'] = $this->wms_user_id;
		$grr['branch_id'] = $branch_id;
		$grr['vendor_id'] = $vendor_id;
		$grr['rcv_date'] = $rcv_date;
		$grr['grr_pcs'] = $rcv_pcs;
		$grr['grr_amount'] = $total_amount_incl_tax;
		$grr['tax_percent'] = $tax_percent;
		$grr['grr_tax'] = $tax_amount;
		$grr['grr_ctn'] = 0;
		if($tax_amount > 0 || $tax_percent > 0){
			$grr['tax_register'] = 1;
		}
		$grr['status'] = 1;
		$grr['added'] = 'CURRENT_TIMESTAMP';
		$grr['department_id'] = $dept_id;
		$grr['transport']= $lorry_no;
		$con->sql_query("insert into grr " . mysql_insert_by_field($grr));
		
		//insert grr_items
		$grr_items_id = $appCore->generateNewID("grr_items", "branch_id=$branch_id");
		$grr_items['id']=$grr_items_id;
		$grr_items['grr_id']=$grr_id;
		$grr_items['branch_id']=$branch_id;
		$grr_items['doc_no']=$doc_no;
		$grr_items['type']='OTHER';
		$grr_items['tax']=$tax_amount;
		$grr_items['grn_used'] = 1;
		$grr_items['amount']=$total_amount_incl_tax;
		$grr_items['pcs']=$rcv_pcs;
		$grr_items['po_id'] = 0;
		$grr_items['ctn'] = 0;
		$grr_items['gst_id'] = 0;
		$grr_items['doc_date'] = $doc_date;
		$con->sql_query("insert into grr_items " . mysql_insert_by_field($grr_items));
		
		//insert grn
		$grn_id = $appCore->generateNewID("grn", "branch_id=$branch_id");
		$grn['id'] = $grn_id;
		$grn['branch_id'] = $branch_id;
		$grn['user_id'] = $this->wms_user_id;
		$grn['grr_id'] = $grr_id;
		$grn['grr_item_id']=$grr_items_id;
		$grn['vendor_id'] = $vendor_id;
		$grn['department_id'] = $dept_id;
		$grn['is_future'] = 1;
		$grn['status'] = 1;
		$grn['approved'] = 1;
		$grn['authorized'] = 1;
		$grn['dn_amount'] = 0;
		$grn['rounding_amt'] = 0;
		$grn['account_amount'] = $total_amount_incl_tax;
		$grn['account_update']='CURRENT_TIMESTAMP';
		$grn['added']='CURRENT_TIMESTAMP';
		if($config['use_grn_future_allow_generate_gra']){
			$grn['generate_gra'] = 1;
		}
		$grn['grn_tax'] = $tax_percent;
		$con->sql_query("insert into grn ". mysql_insert_by_field($grn));
		
		//insert grn_items
		$acc_adjustment = 0;
		foreach($grn_items as $r){
			$grn_items_id = $appCore->generateNewID("grn_items", "branch_id = ".mi($branch_id));
			$grn_items['id']=$grn_items_id;
			$grn_items['sku_item_id']=$r['sku_item_id'];
			$grn_items['branch_id']=$branch_id;
			$grn_items['grn_id']=$grn_id;
			$grn_items['uom_id']=$r['uom_id'];
			$grn_items['cost']= mf($r['cost']);
			$grn_items['pcs']= mf($r['pcs']);
			$grn_items['return_ctn'] = 0;
			$grn_items['return_pcs'] = 0;
			$grn_items['po_cost'] = 0;
			$grn_items['original_cost'] = 0;
			$grn_items['bom_ref_num'] = 0;
			$grn_items['bom_qty_ratio'] = 0;
			$grn_items['acc_disc'] = 0;
			$grn_items['item_group'] = 3;
			$acc_adjustment += mf($r['cost']) * mf($r['pcs']);
			$q6 = $con->sql_query("select if(sp.price is null, selling_price, sp.price) as selling, if(si.artno is null or si.artno='',si.mcode, si.artno) as artno_mcode from sku_items si left join sku on sku.id=si.sku_id left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id=$branch_id where si.id=".mi($r['sku_item_id']));
			$r6 = $con->sql_fetchrow($q6);
			$con->sql_freeresult($q6);
			
			$grn_items['selling_uom_id']=1;
			$grn_items['selling_price']=mf($r6['selling']);
			$grn_items['artno_mcode'] = $r6['artno_mcode'];
			$con->sql_query("insert into grn_items " . mysql_insert_by_field($grn_items));
		}
		$appCore->grnManager->update_total_selling($grn_id, $branch_id);
		
		$appCore->grnManager->update_sku_item_cost($grn_id, $branch_id);
		$appCore->grnManager->update_sku_vendor_history($grn_id, $branch_id);
		$appCore->grnManager->items_return_handler($grn_id, $branch_id);
		$appCore->grnManager->update_total_amount($grn_id, $branch_id);
		
		$con->sql_query("update grn set acc_adjustment=".mf(round($acc_adjustment,2))." where branch_id=".mi($branch_id)." and id=".mi($grn_id));
		$con->sql_commit();
		
		// Return Data
		$ret = array();
		$ret['result'] = 1;
		$ret['branch_id'] = $branch_id;
		$ret['grn_id'] = $grn_id;
		$this->respond_data($ret);
	}
}


$ARMS_WMS_API = new ARMS_WMS_API();
$ARMS_WMS_API->_default();
?>