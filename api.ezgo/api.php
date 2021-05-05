<?php
/*
12/5/2018 4:12 PM Andy
- New API for ezgo.

12/31/2018 4:57 PM Andy
- Added API to Get Voucher Batch Listing.
- Added API to Get Voucher Listing by batch_id.
- Added API to Get Voucher by voucher_id.
- Added API to Get Member Points History
- Added API to Get Member Transaction History
- Added API to Adjust Member Points
- Added API to Get Coupon Batch Listing.
- Added API to Get Redemption Listing.
- Added API to Get Single Redemption by redemption_id.

1/10/2019 2:36 PM Andy
- Change Access-Control-Allow-Methods to 'GET, POST, OPTIONS'.
- Fixed origin spelling error.
- Enhanced get header to use getallheaders().

1/11/2019 2:02 PM Andy
- Fixed getallheaders() function is not available in some php version.
*/
include("../include/common.php");
ini_set('memory_limit', '1024M');
set_time_limit(0);

/*$request_parts = explode('/', $_SERVER['REQUEST_URI']); // array('users', 'show', 'abc')
$file_type     = $_GET['type'];

print_r($request_parts);
print_r($_GET);
print_r($_SERVER);
exit;
$output = get_data_from_db(); //Do your processing here
                              //You can outsource to other files via an include/require

//Output based on request
switch($file_type) {
    case 'json':
        echo json_encode($output);
        break;
    case 'xml':
        echo xml_encode($output); //This isn't a real function, but you can make one
        break;
    default:
        echo $output;
}*/

class API_EZGO{
	//var $folder = "api.ezgo/";
	var $log_folder = "logs";
	
	var $rcv_data = array();
	var $bcode = '';
	var $bid = 0;
	var $all_header = array();
	
	// Error List
	var $err_list = array(
		"unknown_query_error" => "Unknown SQL Error",
		"invalid_api_method" => 'Invalid API Method',
		"invalid_data" => 'Invalid Data',
		"invalid_nric" => "Invalid Member NRIC",
		"invalid_cardno" => "Invalid Member Card Number",
		"invalid_cardno_used" => "Member Card Number Already Used by other Member",
		"duplicate_data" => "Data Already Exists",
		'adjust_member_points_need_remark' => 'Remark is required.',
		'adjust_member_points_need_points' => 'Points cannot be zero.',
		'adjust_member_points_need_integer' => 'Points must be integer.',
	);
	
	var $race_list = array(
		'C' => 'Chinese',
		'I' => 'Indian',
		'M' => 'Malay',
		'O' => 'Others',
	);
	
	function __construct(){
		$this->construct_return_header();
		
		// Convert all header to uppercase
		$tmp_header = getallheaders();
		if($tmp_header){
			foreach($tmp_header as $k=>$v){
				$this->all_header[strtoupper($k)] = $v;
			}
		}
		//file_put_contents('test_api.txt', '');
		//file_put_contents('test_api.txt', print_r($_SERVER, true), FILE_APPEND);
		//print_r($this->all_header);
		
		if($_SERVER['SERVER_NAME'] == 'maximus')	$this->is_debug = 1;
	
		$this->folder = dirname(__FILE__);
		$this->log_folder = $this->folder."/".$this->log_folder;
		
		// Initialise Folder and Database
		$this->prepareDB();
	}
	
	// function to create all db and folder
	private function prepareDB(){
		global $con;
		
		// Create Program Main Folder
		//check_and_create_dir($this->folder);
		
		// Create Program Logs Folder
		check_and_create_dir($this->log_folder);
	}
	
	private function put_log($log){
		$filename = date("Y-m-d").".txt";
		$str = date("Y-m-d H:i:s")."; ".$log;
		file_put_contents($this->log_folder."/".$filename, $str."\r\n", FILE_APPEND);
	}	
	
	private function _requestStatus($code) {
        $status = array(  
            200 => 'OK',
			201 => "Content Created",
			400 => "Invalid data supplied",
			403 => "Invalid ID",
            404 => 'Not Found',
			409 => 'Data Already Exists',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ); 
        return ($status[$code])?$status[$code]:$status[500]; 
    }
	
	// Core function to return json data
	private function _response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));

        print json_encode($data);
		exit;
    }
	
	// function to return error
	private function error_respond($err_msg, $status = 405){
		$this->put_log($err_msg);
		
		$this->_response($err_msg, $status);
	}
	
	// function to return success
	private function success_respond($data, $status = 200){
		$this->put_log("Process Done.");
		
		$this->_response($data, $status);
	}
	
	// function to return invalid api
	private function respond_invalid_api(){
		$this->error_respond($this->err_list['invalid_api_method']);
	}
	
	// function to return invalid data
	private function respond_invalid_data($msg = '', $status = 400){
		if(!$msg)	$msg = $this->err_list['invalid_data'];
		$this->error_respond($msg, $status);
	}
	
	// function to return invalid data
	private function respond_duplicate_data(){
		$this->error_respond($this->err_list['duplicate_data'], 409);
	}
	
	private function get_post_json_data(){
		$this->rcv_data = json_decode(file_get_contents('php://input'), true);
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
	
	function _default(){
		//print_r($_SERVER);
		// Check API Key
		$this->check_api_key();
		
		// Check whether it is POST, GET
		$this->check_method();
		
		// Process function
		$this->process_action();
		
		
	}
	
	private function check_api_key(){
		global $con, $config;
		
		$API_KEY = isset($this->all_header['API-KEY']) ? trim($this->all_header['API-KEY']) : '';

		$this->put_log("Checking API KEY: ".$API_KEY);
		
		if($API_KEY && isset($config['ezgo_api_setting'])){
			foreach($config['ezgo_api_setting'] as $bcode => $b_data){
				if($b_data['api_key'] == $API_KEY){
					$this->bcode = $bcode;
					break;
				}
			}
			
			if($this->bcode){
				$con->sql_query("select id from branch where code=".ms($this->bcode)." and active=1");
				$branch = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($branch){
					$this->bid = mi($branch['id']);
				}
			}
		}
		
		if(!$this->bid){
			//throw new Exception('Invalid API Key');
			$this->error_respond('Invalid API Key', 404);
		}
		
		//$this->_response(array('no_problem'=>1));
		
		// Validate Success
		$this->put_log("Authentication Success Branch ID [".$this->bid."], Branch Code [".$this->bcode."]");
	}
	
	private function check_method(){
		$this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }
		
		switch($this->method) {
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
				$this->error_respond('Invalid Method', 405);
				break;
        }
	}
	
	private function process_action(){
		$this->put_log("Method [".$this->method."] URL [".$_SERVER['REQUEST_URI']."]");
		
		$request_parts = explode('/', $_SERVER['REQUEST_URI']);
		
		array_shift($request_parts);
		array_shift($request_parts);
		//print_r($request_parts);
		
		if(count($request_parts)>0){
			// Check First Param
			$arg1 = strtolower(trim(array_shift($request_parts)));
			if($arg1){
				switch($arg1){
					case 'member':	// Member API
						$this->check_member_action($request_parts);
						break;
					case 'voucher':	// Voucher API
						$this->check_voucher_action($request_parts);
						break;
					case 'coupon':	// Coupon API
						$this->check_coupon_action($request_parts);
						break;
					case 'redemption':	// Redemption API
						$this->check_redemption_action($request_parts);
						break;
				}
			}
		}
		
		// No Action or Action Method Not Found
		$this->respond_invalid_api();
	}
	
	// Member Method
	private function check_member_action($request_parts){
		switch($this->method){
			case 'GET':	// member/{nric}
				$nric = trim(array_shift($request_parts));
				if(!$nric){	// GET must have NRIC
					return;
				}
				
				/*
					member/{nric}/
					member/{nric}/point
					member/{nric}/transactions
				*/
				
				// Get 3rd param
				$get_type = trim(array_shift($request_parts));
				if(!$get_type){	// member/{nric}
					// Get Member Normal Info
					$this->get_member_info($nric);
				}else{
					/*
						member/{nric}/point
						member/{nric}/transactions
					*/
					if($get_type == 'point'){
						// Get Points History
						$this->get_member_points_history($nric);
					}elseif($get_type == 'transactions'){
						// Get Points History
						$this->get_member_transactions($nric);
					}
				}
				break;
			case 'POST':	// /member
				$nric = trim(array_shift($request_parts));
				if(!$nric){	// No NRIC - Add New Member
					$this->update_member('', true);
					break;
				}else{	// Got NRIC - Update Member
					$update_type = trim(array_shift($request_parts));
					if($update_type == 'info'){
						$this->update_member($nric);
					}elseif($update_type == 'point'){
						$this->adjust_member_points($nric);
					}			
					break;
				}
				break;
		}
	}
	
	private function get_member_info($nric){
		global $con;
		
		if(!$nric){
			$this->respond_invalid_data($this->err_list['invalid_nric']);
		}
		
		// Get Membership Data by NRIC
		$member_data = $this->get_member_info_by_nric($nric);
		
		// Member Not Found
		if(!$member_data){
			$this->respond_invalid_data();
		}
		
		// Construct Return Data
		$ret = array();
		$ret['nric'] = $member_data['membership']['nric'];
		$ret['cardNumber'] = $member_data['membership']['card_no'];
		$ret['gender'] = $member_data['membership']['gender'];
		$ret['name'] = $member_data['membership']['name'];
		$ret['issueDate'] = $member_data['membership']['issue_date'];
		$ret['expiryDate'] = $member_data['membership']['next_expiry_date'];
		$ret['race'] = substr($member_data['membership']['race'], 0, 1);	// Only return C, I, M, O
		$ret['address'] = $member_data['membership']['address'];
		$ret['postcode'] = $member_data['membership']['postcode'];
		$ret['city'] = $member_data['membership']['city'];
		$ret['state'] = $member_data['membership']['state'];
		$ret['phone_1'] = $member_data['membership']['phone_1'];
		$ret['phone_2'] = $member_data['membership']['phone_2'];
		$ret['phone_3'] = $member_data['membership']['phone_3'];
		$ret['email'] = $member_data['membership']['email'];
		$ret['points'] = $member_data['membership']['points'];
		
		$this->success_respond($ret);
	}
	
	private function get_member_info_by_nric($nric, $params = array()){
		global $con;
		
		if(!$nric)	return false;
		
		$data = array();
		$con->sql_query("select * from membership where nric=".ms($nric));
		$member = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$member)	return false;
		
		$data['membership'] = $member;
		
		return $data;
	}
	
	private function is_member_card_no_used_by_other($card_no, $nric){
		global $con;
		
		if(!$card_no)	return false;
		
		$con->sql_query("select * from membership_history where card_no=".ms($card_no)." and nric<>".ms($nric));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $tmp ? true : false;
	}
	
	private function update_member($nric = '', $is_add = false){
		global $con, $config;
		
		if(!$this->rcv_data){
			$this->respond_invalid_data();
		}
		
		$need_add_history = false;
		
		// membership
		$upd = array();
		
		if($is_add){	// Add New Member
			$need_add_history = true;
			
			$nric = $upd['nric'] = $this->rcv_data['nric'];
			$upd['card_no'] = $this->rcv_data['cardNumber'];
			$upd['apply_branch_id'] = $this->bid;
			$upd['verified_by'] = 1;
			$upd['verified_date'] = "CURRENT_TIMESTAMP";
			$upd['issue_date'] = date("Y-m-d", strtotime($this->rcv_data['issueDate']));
		}else{	// Update Member
			// Only Update Card No if provided
			if($this->rcv_data['cardNumber']){
				$upd['card_no'] = $this->rcv_data['cardNumber'];
			}
		}
		
		// Must have NRIC
		if(!$nric){
			$this->respond_invalid_data($this->err_list['invalid_nric']);
		}
		
		// New Member must have Card No
		if($is_add && !$upd['card_no']){
			$this->respond_invalid_data($this->err_list['invalid_cardno']);
		}
		
		// Got change card no
		if($upd['card_no']){
			// Card Number used by other member
			if($this->is_member_card_no_used_by_other($upd['card_no'], $nric)){
				$this->respond_invalid_data($this->err_list['invalid_cardno_used']);
			}
		}
		
		$upd['name'] = $this->rcv_data['name'];
		$upd['member_type'] = $this->rcv_data['member_type'] ? $this->rcv_data['member_type'] : 'member1';
		$upd['gender'] = $this->rcv_data['gender'];
		
		// Race
		$race_key = strtoupper(trim($this->rcv_data['race']));
		if(!isset($this->race_list[$race_key])){
			$race_key = 'O';	// Other
		}
		$upd['race'] = $this->race_list[$race_key];
		$upd['address'] = $this->rcv_data['address'];
		$upd['postcode'] = $this->rcv_data['post_code'];
		$upd['city'] = $this->rcv_data['city'];
		$upd['state'] = $this->rcv_data['state'];
		$upd['phone_1'] = $this->rcv_data['phone_1'];
		$upd['phone_2'] = $this->rcv_data['phone_2'];
		$upd['phone_3'] = $this->rcv_data['phone_3'];
		$upd['email'] = $this->rcv_data['email'];
		$upd['next_expiry_date'] = date("Y-m-d", strtotime($this->rcv_data['expiryDate']));
		
		// Get Membership Data by NRIC
		$member_data = $this->get_member_info_by_nric($nric);
		
		if($is_add){	// Add
			if($member_data){	// Member Already Exists
				$this->respond_duplicate_data();	// Duplicate Error
			}
			
			// Insert Data
			$con->sql_query("insert into membership ".mysql_insert_by_field($upd));
		}else{	// Edit
			//print "Check $nric";
			
			if(!$member_data){	// Member Not Exists
				$this->respond_invalid_data('', 403);
			}
			
			// Card No Changed
			if($upd['card_no'] && $upd['card_no'] != $member_data['membership']['card_no']){
				$need_add_history = true;
			}
			
			// Update Data
			$con->sql_query("update membership set ".mysql_update_by_field($upd)." where nric=".ms($nric));
		}
		
		
		// membership_history
		if($need_add_history){
			$upd2 = array();
			$upd2['nric'] = $nric;
			$upd2['card_no'] = $upd['card_no'];
			$upd2['branch_id'] = $this->bid;
			$upd2['user_id'] = 1;
			
			$card_type = '';
			foreach($config['membership_cardtype'] as $type=>$ct)
			{
				if (preg_match($ct['pattern'], $upd2['card_no']))
				{
					$card_type = $type;
					break;
				}
			}
			
			$upd2['card_type'] = $card_type;
			$upd2['issue_date'] = date("Y-m-d", strtotime($this->rcv_data['issueDate']));
			$upd2['expiry_date'] = $upd['next_expiry_date'];
			$upd2['remark'] = 'N';
			$upd2['added'] = 'CURRENT_TIMESTAMP';
			$upd2['m_type'] = $upd['member_type'];
			
			$con->sql_query("insert into membership_history ".mysql_insert_by_field($upd2));
		}
		
		// Log
		if($is_add){
			$log_str = "New Member Created: NRIC [".$upd['nric']."], Card No: [".$upd['card_no']."]";
		}else{
			$log_str = "Update Member: NRIC [".$upd['nric']."], Card No: [".$upd['card_no']."]";
		}
		
		log_br(1, 'MEMBERSHIP', 0, $log_str);
		$this->put_log($log_str);
		
		if($is_add){
			$this->success_respond("Member Created", 201);
		}else{
			$this->success_respond("Member Updated", 200);
		}
	}
	
	private function get_member_points_history($nric){
		global $con;
		
		if(!$nric){
			$this->respond_invalid_data($this->err_list['invalid_nric']);
		}
		
		// Get Membership Data by NRIC
		$member_data = $this->get_member_info_by_nric($nric);
		
		// Member Not Found
		if(!$member_data){
			$this->respond_invalid_data();
		}
		
		$ret = array();
		$ret['total'] = 0;
		$ret['point_history'] = array();
		
		$q1 = $con->sql_query("select mp.*
			from membership_points mp
			where mp.nric=".ms($nric)."
			order by mp.date desc");
		while($r = $con->sql_fetchassoc($q1)){
			$point_history_id = $r['type']."-".$r['branch_id']."-".strtotime($r['date'])."-".$r['card_no'];
			
			$data = array();
			$data['point_history_id'] = $point_history_id;
			$data['nric'] = $r['nric'];
			$data['card_no'] = $r['card_no'];
			$data['date'] = $r['date'];
			$data['branch_id'] = $r['branch_id'];
			$data['type'] = $r['type'];
			$data['points'] = $r['points'];
			$data['remark'] = trim($r['remark']);
			$data['point_source'] = trim($r['point_source']);
			
			//$data['editable'] = $r['type'] == 'ADJUST' ? 1 : 0;
			
			$ret['point_history'][] = $data;
			$ret['total']++;
		}
		$con->sql_freeresult($q1);
		
		$this->success_respond($ret);
	}
	
	private function get_member_transactions($nric){
		global $con;
		
		if(!$nric){
			$this->respond_invalid_data($this->err_list['invalid_nric']);
		}
		
		// Get Membership Data by NRIC
		$member_data = $this->get_member_info_by_nric($nric);
		
		// Member Not Found
		if(!$member_data){
			$this->respond_invalid_data();
		}
		
		$ret = array();
		$ret['total'] = 0;
		$ret['transactions'] = array();
		
		// Get card_no list
		$card_no_list = array();
		
		$con->sql_query("select distinct card_no as c from membership_history where nric=".ms($nric));
		while($r = $con->sql_fetchassoc()){
			$card_no_list[] = $r['c'];
		}
		$con->sql_freeresult();
		
		if($card_no_list){
			$filter = array();
			$filter[] = "p.member_no in (".join(',', array_map('ms', $card_no_list)).")";
			$filter[] = "p.cancel_status=0";
			
			$str_filter = "where ".join(' and ', $filter);
			
			$con->sql_query("select p.receipt_ref_no,p.branch_id,p.date,p.pos_time, p.point
				from pos p
				$str_filter
				order by date desc");
			while($r = $con->sql_fetchassoc()){
				$ret['transactions'][] = $r;
				$ret['total']++;
			}
			$con->sql_freeresult();
		}
		
		
		$this->success_respond($ret);
	}
	
	private function adjust_member_points($nric){
		global $con, $config;
		
		// Must have NRIC
		if(!$nric){
			$this->respond_invalid_data($this->err_list['invalid_nric']);
		}
		
		// Body Data
		if(!$this->rcv_data){
			$this->respond_invalid_data();
		}
		
		// Get Membership Data by NRIC
		$member_data = $this->get_member_info_by_nric($nric);
		
		// Member Not Found
		if(!$member_data){
			$this->respond_invalid_data();
		}
		
		$upd = array();
		$upd['remark'] = trim($this->rcv_data['remark']);
		$upd['points'] = trim($this->rcv_data['points']);
		
		// Remark
		if(!$upd['remark']){
			$this->respond_invalid_data($this->err_list['adjust_member_points_need_remark']);
		}
		
		if(mi($upd['points']) != mf($upd['points'])){
			$this->respond_invalid_data($this->err_list['adjust_member_points_need_integer']);
		}
		
		$upd['points'] = mi($upd['points']);
		
		// Points
		if(!$upd['points']){
			$this->respond_invalid_data($this->err_list['adjust_member_points_need_points']);
		}
		
		$upd['nric'] = $nric;
		$upd['card_no'] = $member_data['membership']['card_no'];
		$upd['branch_id'] = $this->bid;
		$upd['date'] = 'CURRENT_TIMESTAMP';
		$upd['type'] = 'ADJUST';
		$upd['user_id'] = 1;
		$upd['point_source'] = 'API';
		
		$success = $con->sql_query_false("insert into membership_points ".mysql_insert_by_field($upd));
		if(!$success){
			$this->respond_invalid_data($this->err_list['unknown_query_error']);
		}
		
		$con->sql_query("update membership set points = points + ".mf($upd['points']).", points_update = CURRENT_TIMESTAMP where nric = ".ms($nric));
		
		// Log
		$log_str = 'Adjust point for ' . $nric ." => ".$upd['card_no']." (Points: ".$upd['points'].")";		
		log_br(1, 'MEMBERSHIP', 0, $log_str);
		$this->put_log($log_str);
		
		$this->success_respond("Points Adjusted", 200);
	}
	
	// Voucher Method
	private function check_voucher_action($request_parts){
		switch($this->method){
			case 'GET':	// voucher/batch or voucher/{$voucher_id}
				$get_type = trim(array_shift($request_parts));
				
				if(!$get_type){	// GET must have 2nd param
					return;
				}
				
				/*
					voucher/batch
					voucher/{$voucher_id}
				*/
				
				if($get_type == 'batch'){	// Get Voucher Batch
					/*
						voucher/batch
						voucher/batch/{$batch_id}
					*/
					$batch_id = trim(array_shift($request_parts));
					if(!$batch_id){	// voucher/batch
						// Get Voucher Batch List
						$this->get_voucher_batch_list();
						
					}else{	// voucher/batch/{$batch_id}
						// Get Voucher in Selected Batch
						$this->get_vouchers_info(array('batch_id' => $batch_id));
					}
				}else{	// Get Single Voucher
					/*
						voucher/{$voucher_id}
					*/
					$voucher_id = $get_type;
					$this->get_vouchers_info(array('voucher_id' => $voucher_id));
				}
				
				break;
			case 'POST':	// /member
				/*$nric = trim(array_shift($request_parts));
				if(!$nric){	// No NRIC - Add New Member
					$this->update_member('', true);
					break;
				}else{	// Got NRIC - Update Member
					$update_type = trim(array_shift($request_parts));
					if($update_type == 'info'){
						$this->update_member($nric);
					}					
					break;
				}*/
				break;
		}
	}
	
	private function get_voucher_batch_list(){
		global $con;
		
		$ret = array();
		$ret['total'] = 0;
		$ret['batch'] = array();
		
		$con->sql_query("select concat(mvb.branch_id,'-',mvb.batch_no) as batch_id,
			(
			select count(*) from mst_voucher mv where mv.branch_id=mvb.branch_id and mv.batch_no=mvb.batch_no
			) as voucher_qty
			from mst_voucher_batch mvb
			where mvb.cancel_status=0
			order by added desc");
		while($r = $con->sql_fetchassoc()){
			$ret['batch'][] = $r;
			$ret['total']++;
		}
		$con->sql_freeresult();
		
		$this->success_respond($ret);
	}
	
	private function get_vouchers_info($params = array()){
		global $con;
		
		if(isset($params['voucher_id']))	$voucher_id = $params['voucher_id'];
		elseif(isset($params['batch_id']))	$batch_id = $params['batch_id'];
		
		$filter = array();
		
		$error = false;
		
		if($voucher_id){
			list($bid, $batch_no, $id) = explode("-", $voucher_id);
		}elseif($batch_id){
			list($bid, $batch_no) = explode("-", $batch_id);
		}else{
			$error = true;
		}
		
		if($error){
			$this->respond_invalid_data();
		}else{
			if($bid){
				$filter[] = "mv.branch_id=".mi($bid);
				$filter[] = "mv.batch_no=".mi($batch_no);
				
				if($voucher_id){
					$filter[] = "mv.id=".mi($id);
				}
			}
		}
		
		// Get Voucher Prefix
		$sql_pre = "select * 
				from pos_settings ps
				where ps.branch_id = $this->bid and ps.setting_name='barcode_voucher_prefix'";

        $con->sql_query($sql_pre);
	    $ps = $con->sql_fetchassoc();

		if (!$ps['setting_value']){
			$voucher_prefix = "VC";
		}else{
            $voucher_prefix = strtoupper($ps['setting_value']);
		}
		
		$ret = array();
		$ret['total'] = 0;
		$ret['vouchers'] = array();
		
		//print_r($filter);exit;
		$str_filter = "where ".join(' and ', $filter);
		
		$q1 = $con->sql_query("select concat(mv.branch_id,'-',mv.batch_no,'-',mv.id) as voucher_id, concat(mv.branch_id,'-',mv.batch_no) as batch_id, mv.code, mv.voucher_value, mv.active as active, mv.activated as activated_time, mv.valid_from, mv.valid_to, mv.cancel_status as cancelled
		from mst_voucher mv
		$str_filter
		order by mv.id");
		
		while($r = $con->sql_fetchassoc($q1)){
			// Construct Voucher Barcode
			$barcode = str_pad($r['code'],7,"0",STR_PAD_LEFT).str_pad(($r['voucher_value']*100),5,"0",STR_PAD_LEFT);
			$r['voucher_barcode']=$voucher_prefix.$barcode.substr(encrypt_for_verification($barcode),0,2);
				
			// Check This voucher was used or not
			$r['voucher_used'] = 0;
			$r['used_time'] = '0000-00-00 00:00:00';
			$r['used_receipt_ref_no'] = '';
				
			$q2 = $con->sql_query("select count(*) as num_used, max(pos.pos_time) as max_pos_time, pos.receipt_ref_no
					from pos_payment pp 
					left join pos on pp.pos_id=pos.id and pp.counter_id=pos.counter_id and pp.branch_id=pos.branch_id and pp.date=pos.date 
					where pos.cancel_status=0 and pp.type='Voucher' and length(pp.remark)=12 and pp.remark like ".ms($r['code'].'%')." and pp.adjust=0");
			$used = $con->sql_fetchassoc($q2);
			$con->sql_fetchassoc($q2);
			
			if($used['num_used']>0){
				$r['voucher_used'] = 1;
				$r['used_time'] = $used['max_pos_time'];
				$r['used_receipt_ref_no'] = $used['receipt_ref_no'];
			}
			
			$ret['total']++;
			$ret['vouchers'][] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($batch_id || $voucher_id){
			if($ret['total']<=0){
				$this->respond_invalid_data();
			}
			
			// Get Single Voucher
			if($voucher_id){
				$ret = $ret['vouchers'][0];
			}
		}
		
		$this->success_respond($ret);
	}
	
	// Check Coupon Action
	private function check_coupon_action($request_parts){
		switch($this->method){
			case 'GET':	// coupon/batch
				$get_type = trim(array_shift($request_parts));
				
				if(!$get_type){	// GET must have 2nd param
					return;
				}
				
				/*
					coupon/batch
				*/
				
				if($get_type == 'batch'){	// Get Voucher Batch
					// Get Voucher Batch List
					$this->get_coupon_batch_list();
				}
				
				break;
		}
	}
	
	private function get_coupon_batch_list(){
		global $con;
		
		$ret = array();
		$ret['total'] = 0;
		$ret['batch'] = array();
		
		$con->sql_query("select concat(branch_id,'-',id) as batch_id, code, valid_from, valid_to, remark 
			from coupon
			where active=1
			order by added desc");
		while($r = $con->sql_fetchassoc()){
			$ret['batch'][] = $r;
			$ret['total']++;
		}
		$con->sql_freeresult();
		
		$this->success_respond($ret);
	}
	
	// Redemption
	private function check_redemption_action($request_parts){
		switch($this->method){
			case 'GET':	// redemption
				$redemption_id = trim(array_shift($request_parts));
				
				if(!$redemption_id){	// GET redemption list
					$this->get_redemption_info();
					return;
				}
				
				/*
					redemption/{$id}
				*/
				
				
				// Get Redemption by redemption_id
				$this->get_redemption_info(array('redemption_id'=>$redemption_id));
				
				
				break;
		}
	}
	
	private function get_redemption_info($params = array()){
		global $con;
		
		global $con;
		
		if(isset($params['redemption_id']))	$redemption_id = $params['redemption_id'];
		
		$filter = array();
		$filter[] = "mrs.active=1 and mrs.confirm=1";
		
		if($redemption_id){
			list($bid, $id) = explode("-", $redemption_id);
		}
		
		if($bid && $id){
			$filter[] = "mrs.branch_id=".mi($bid);
			$filter[] = "mrs.id=".mi($id);
		}
		
		$ret = array();
		$ret['total'] = 0;
		$ret['redemptions'] = array();
		
		//print_r($filter);exit;
		$str_filter = "where ".join(' and ', $filter);
		
		$q1 = $con->sql_query("select concat(mrs.branch_id,'-',mrs.id) as redemption_id, si.sku_item_code,si.description, mrs.is_voucher, mrs.voucher_value, mrs.point as points_required
			from membership_redemption_sku mrs 
			join sku_items si on si.id=mrs.sku_item_id
			$str_filter
			order by points_required");
		while($r = $con->sql_fetchassoc($q1)){
			$ret['redemptions'][] = $r;
			$ret['total']++;
		}
		$con->sql_freeresult($q1);
		
		if($redemption_id){
			if($ret['total']<=0){
				$this->respond_invalid_data();
			}
			
			// Get Single Redemption
			$ret = $ret['redemptions'][0];
			
		}
		$this->success_respond($ret);
	}
}

if (!function_exists('getallheaders')) 
{ 
    function getallheaders() 
    { 
       $headers = array();
       foreach ($_SERVER as $name => $value) 
       { 
           if (substr($name, 0, 5) == 'HTTP_') 
           { 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       return $headers; 
    } 
} 

$API_EZGO = new API_EZGO();
$API_EZGO->_default();

?>