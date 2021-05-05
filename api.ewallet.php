<?php
/*
3/7/2019 10:05 AM Andy
- eWallet API.

4/2/2019 3:18 PM Andy
- Enhanced to get branch and counter ID from request as if counter doesn't send it via header.

4/4/2019 1:38 PM Justin
- Enhanced to have some paramater to be used by Paydibs.

4/17/2019 4:32 PM Justin
- Enhanced to have void payment.

7/23/2019 5:17 PM Justin
- Enhanced to pass trans_id used by paydibs into its own api for processing.

3/11/2021 11:33 AM William
- Enhanced to add new payment type "ipay88".

4/23/2021 12:13 PM William
- Enhanced ipay88 api make_payment to return "actual_ewallet_type" when integrator_type is all.
*/

include("include/common.php");
ini_set('memory_limit', '1024M');
set_time_limit(0);

abstract class EWALLET_API {
	var $is_debug = 0;
	var $folder = '';
	var $log_folder = 'logs';
	var $ewallet_settings = array();
	var $ewallet_arms_settings = array();
	var $ewallet_branch_settings = array();
	var $ewallet_counter_settings = array();
	
	var $bid = 0;
	var $branch = array();
	var $counter_id = 0;
	var $counter = array();
	var $receipt_ref_no = '';
	var $transaction_date = '';
	var $customer_token = '';
	var $user_id = 0;
	
	var $success_info = array();
	var $failed_obj = array();
	var $success_ref_no = '';
	var $ewallet_ref_no = '';
	var $actual_ewallet_type = '';
	
	abstract function validate_ewallet_arms_setting();
	abstract function validate_ewallet_branch_setting();
	abstract function validate_ewallet_counter_setting();
	
	abstract function make_payment();
	abstract function check_payment_status($prms);
	abstract function void_payment($prms);
	
	
	function __construct(){
		global $config;
		
		$this->construct_return_header();
		
		// Convert all header to uppercase
		$tmp_header = getallheaders();
		if($tmp_header){
			foreach($tmp_header as $k=>$v){
				$k = str_replace('-', '_', strtoupper($k));
				$this->all_header[$k] = $v;
			}
		}
		
		// mst_ewallet_type are used for paydibs, we will not overwrite the $this->ewallet_type
		if($this->mst_ewallet_type) $curr_ewallet_type = $this->mst_ewallet_type;
		else $curr_ewallet_type = $this->ewallet_type;
		
		// Get the eWallet Settings
		$this->ewallet_settings = $config['ewallet_settings'][$curr_ewallet_type];
		
		//if($_SERVER['SERVER_NAME'] == 'maximus' || $_SERVER['SERVER_NAME'] == 'luke.arms.com.my' || $_SERVER['SERVER_ADDR'] == '10.1.1.200')	$this->is_debug = 1;
		if($this->ewallet_settings['is_debug']){
			$this->is_debug = 1;
		}
		
		// Get the ARMS eWallet Settings
		$debug_type = $this->is_debug ? 'stage' : 'production';
		$this->ewallet_arms_settings = $config['ewallet_arms_setting'][$curr_ewallet_type][$debug_type];
	}
	
	function _default(){
		//print "Start";
		
		//print_r($_SERVER);
		// Check API Key
		$this->check_api_key();
		
		// Process function
		$this->process_action();
	}
	
	private function check_api_key(){
		global $con, $config;
		
		//$this->put_log("eWallet Type: ".$this->ewallet_type);
		if(!$this->ewallet_settings['active']){	// Check ewallet payment active
			$this->error_respond('eWallet Payment is inactive');
		}
		
		// Check ARMS eWallet Setting
		$this->validate_ewallet_arms_setting();
		
		$BRANCH_ID = isset($this->all_header['X_BRANCH_ID']) ? mi($this->all_header['X_BRANCH_ID']) : 0;
		if(!$BRANCH_ID) $BRANCH_ID = mi($_REQUEST['branch_id']);
		
		$COUNTER_ID = isset($this->all_header['X_COUNTER_ID']) ? mi($this->all_header['X_COUNTER_ID']) : 0;
		if(!$COUNTER_ID) $COUNTER_ID = mi($_REQUEST['counter_id']);

		$this->put_log("Branch ID: ".$BRANCH_ID);
		$this->put_log("Counter ID: ".$COUNTER_ID);
		
		//print_r($this->ewallet_settings);
		
		if($BRANCH_ID>0 && $this->ewallet_settings){
			// Check is active branch
			$con->sql_query("select id,code from branch where id=".mi($BRANCH_ID)." and active=1");
			$branch = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($branch){	// Active Branch
				$bcode = trim($branch['code']);
				$this->bid = mi($branch['id']);
				$this->branch = $branch;
				
				$this->put_log("Branch Code: ".$bcode);
				
				// Check this branch got ewallet setting
				if(!isset($this->ewallet_settings['branch_settings'][$bcode])){
					$this->error_respond('eWallet Setting Not Setup');
				}
				
				$this->ewallet_branch_settings = $this->ewallet_settings['branch_settings'][$bcode];
				$this->validate_ewallet_branch_setting();
				
				$con->sql_query("select * from counter_settings where branch_id=".mi($BRANCH_ID)." and id=".mi($COUNTER_ID));
				$counter = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$counter){
					$this->error_respond('Invalid Counter ID');
				}
				
				$this->counter_id = $COUNTER_ID;
				$this->counter = $counter;
				
				//$counter['ewallet_settings'] = unserialize($counter['ewallet_settings']);
				//$this->ewallet_counter_settings = $counter['ewallet_settings'][$this->ewallet_type];
				//$this->ewallet_counter_settings = array('counter_pos_id'=>'x123');
				
				$this->validate_ewallet_counter_setting();
				
				
				
				
			}else{	// Branch Not Found
				$this->error_respond('Invalid Branch ID');
			}
			
		}
		
		// Validate Success
		$this->put_log("Authentication Success.");
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
	
	protected function put_log($log){
		$filename = date("Y-m-d").".txt";
		$str = date("Y-m-d H:i:s")."; ".$log;
		file_put_contents($this->log_folder."/".$filename, $str."\r\n", FILE_APPEND);
	}
	
	// function to return success
	protected function success_respond($data, $status = 200){
		$data['ok'] = 1;
		$this->put_log("Process Done.");
		
		$this->_response($data, $status);
	}
	
	// function to return error
	protected function error_respond($err_msg, $log_msg = '', $status = 400){
		if(!$err_msg)	$err_msg = 'Unknown Error Occured';
		
		$ret = array();
		$ret['failed'] = 1;
		$ret['error'] = $err_msg;
		
		if(!$log_msg)	$log_msg = $err_msg;
		$this->put_log($log_msg);
		
		$this->_response($ret, $status);
	}
	
	private function _requestStatus($code) {
        $status = array(  
            200 => 'OK',
			201 => "Content Created",
			400 => "Invalid data supplied",
			401 => "Login Failed",
			402 => "Request Timeout",
			403 => "Invalid ID",
            404 => 'Not Found',
            405 => 'Method Not Allowed',
			409 => 'Data Already Exists',
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
	
	private function process_action(){
		if(isset($_REQUEST['a'])){
			switch($_REQUEST['a']){
				case 'make_payment':	// make payment
					$this->prepare_make_payment();
					exit;
				case 'check_payment_status':	// check payment status
					$this->prepare_check_payment_status();
					exit;
				case 'void_payment':	// check payment status
					$this->prepare_void_payment();
					exit;
			}
		}
		
		$this->error_respond("Invalid API Method");
	}
	
	private function check_required_transaction_data(){
		// Branch ID
		if($this->bid <= 0){
			$this->error_respond("Invalid Branch ID");
		}
		
		// Counter ID
		if($this->counter_id <= 0){
			$this->error_respond("Invalid Counter ID");
		}
		
		// Receipt Reference No
		if(!$this->receipt_ref_no){
			$this->error_respond("Invalid Receipt Reference No");
		}
		
		// Transaction Date
		if(date("Y", strtotime($this->transaction_date))<2000){
			$this->error_respond("Invalid Transaction Date");
		}
		
		// ewallet Type
		if(!$this->ewallet_type){
			$this->error_respond("Invalid eWallet Type");
		}
	}
	
	private function prepare_make_payment(){
		global $con, $appCore;
		
		// Params
		$this->receipt_ref_no = trim($_REQUEST['receipt_ref_no']);
		$this->transaction_date = date("Y-m-d", strtotime($_REQUEST['transaction_date']));
		$this->transaction_amount = mf($_REQUEST['amount']);
		$this->customer_token = trim($_REQUEST['customer_token']);
		$this->ewallet_remark = trim($_REQUEST['ewallet_remark']);
		$this->user_id = mi($_REQUEST['user_id']);
		
		// Validate
		$this->check_required_transaction_data();
		
		// Further Validate
		if($this->transaction_amount <= 0){
			$this->error_respond("Invalid Amount");
		}
		
		if(!$this->customer_token){
			$this->error_respond("Invalid Customer Token");
		}
		
		$need_update_success_info = false;
		$prev_payment_success = false;
		
		// Check system whether already paid before
		$this->success_info = array();
		$prev_data = $this->get_prev_payment_info();
		//print_r($prev_data);
		//print_r($this->success_info);
		if($prev_data['data'] ){	// got previous payment
			$guid = $prev_data['data']['guid'];
			if($this->success_info){
				$prev_payment_success = true;
			
				if($prev_data['need_update']){	// need update database
					$need_update_success_info = true;
				}
			}
		}
		
		// First time pay or previous payment failed
		if(!$prev_payment_success){	
			$upd = array();
			if(!$guid){	// first time pay
				// Create a new eWallet Payment Record
				$guid = $appCore->newGUID();
				$is_new = true;
				
				$upd['guid'] = $guid;
				$upd['added'] = 'CURRENT_TIMESTAMP';
			}
			
			$upd['branch_id'] = $this->bid;
			$upd['counter_id'] = $this->counter_id;
			$upd['date'] = $this->transaction_date;
			$upd['user_id'] = $this->user_id;			
			$upd['receipt_ref_no'] = $this->receipt_ref_no;
			$upd['ewallet_type'] = $this->ewallet_type;
			$upd['amount'] = $this->transaction_amount;
			$upd['remark'] = $this->ewallet_remark;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			
			if($is_new){
				$con->sql_query("insert into pos_transaction_ewallet_payment ".mysql_insert_by_field($upd));
			}else{
				$con->sql_query("update pos_transaction_ewallet_payment set ".mysql_update_by_field($upd)." where guid=".ms($guid));
			}
			
			// Call API to make payment
			$result = $this->make_payment();
			
			// Payment Failed
			if(!$result['ok']){
				// Record the failed info
				$upd2 = array();
				$upd2['last_update'] = 'CURRENT_TIMESTAMP';
				$upd2['failed_info'] = serialize($this->failed_obj);
				$con->sql_query("update pos_transaction_ewallet_payment set ".mysql_update_by_field($upd2)." where guid=".ms($guid));
				$this->error_respond($result['error']);
			}
			
			$this->paid_amount = $this->transaction_amount;
			
			// Payment Success, need update database
			$need_update_success_info = true;
		}
		
		// Payment success and need update database
		if($need_update_success_info){
			// Update Success info
			$upd2 = array();
			$upd2['more_info']['success_info'] = $this->success_info;
			$upd2['more_info'] = serialize($upd2['more_info']);
			$upd2['success'] = 1;
			$upd2['success_ref_no'] = $this->success_ref_no;
			$upd2['last_update'] = 'CURRENT_TIMESTAMP';
			$upd2['failed_info'] = '';
			$con->sql_query("update pos_transaction_ewallet_payment set ".mysql_update_by_field($upd2)." where guid=".ms($guid));
		}
		
		// Return Data to Counter
		$ret = array();
		$ret['success'] = 1;
		$ret['success_info'] = $this->success_info;
		$ret['success_ref_no'] = $this->success_ref_no;
		$ret['paid_amount'] = $this->paid_amount;
		if($this->actual_ewallet_type){
			$ret['actual_ewallet_type'] = $this->actual_ewallet_type;
		}
		$this->success_respond($ret);
	}
	
	private function get_prev_payment_info($is_void_pymt=false){
		global $con;
		
		// Validate
		$this->check_required_transaction_data();
		
		// Get Data from database
		$con->sql_query("select * from pos_transaction_ewallet_payment where branch_id=".mi($this->bid)." and counter_id=".mi($this->counter_id)." and date=".ms($this->transaction_date)." and receipt_ref_no=".ms($this->receipt_ref_no)." and ewallet_type=".ms($this->ewallet_type)." order by added desc limit 1");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
				
		// Never pay before
		if(!$data)	return false;
		
		$data['more_info'] = unserialize($data['more_info']);
		
		$ret = array();
		$ret['need_update'] = false;
		
		// Got pay before
		if($data['success']==1){
			// Payment already success
			if($is_void_pymt) $this->success_info = $data['more_info']['void_success_info'];
			else $this->success_info = $data['more_info']['success_info'];
			$this->paid_amount = $data['amount'];
		}else{
			// Payment not success - Call API to check again
			$prms = array();
			$prms['more_info'] = $data['more_info'];
			$result = $this->check_payment_status($prms);
			if($result['ok'] && $this->success_info){
				$ret['need_update'] = true;
				$this->paid_amount = $data['amount'];
			}
		}
		
		$ret['data'] = $data;
		
		return $ret;
	}
	
	public function prepare_check_payment_status(){
		// Params
		$this->receipt_ref_no = trim($_REQUEST['receipt_ref_no']);
		$this->transaction_date = date("Y-m-d", strtotime($_REQUEST['transaction_date']));
		$this->transaction_amount = mf($_REQUEST['amount']);
		
		// Validate
		$this->check_required_transaction_data();
		
		// Further Validate
		if($this->transaction_amount <= 0){
			$this->error_respond("Invalid Amount");
		}
	
		$need_update_success_info = false;
		$prev_payment_success = false;
		
		// Check system whether already paid before
		$this->success_info = array();
		$prev_data = $this->get_prev_payment_info();
		//print_r($prev_data);
		//print_r($this->success_info);
		if($prev_data['data'] && $this->success_info){	// previous payment success
			$prev_payment_success = true;
			
			if($prev_data['need_update']){	// need update database
				$need_update_success_info = true;
				$guid = $prev_data['data']['guid'];
			}
		}
		
		// Payment success and need update database
		if($need_update_success_info){
			// Update Success info
			$upd2 = array();
			$upd2['more_info']['success_info'] = $this->success_info;
			$upd2['more_info'] = serialize($upd2['more_info']);
			$upd2['success'] = 1;
			$upd2['last_update'] = 'CURRENT_TIMESTAMP';
			$upd2['success_ref_no'] = $this->success_ref_no;
			$con->sql_query("update pos_transaction_ewallet_payment set ".mysql_update_by_field($upd2)." where guid=".ms($guid));
		}
		
		$ret = array();
		if($prev_payment_success){
			$ret['success'] = 1;
			$ret['success_info'] = $this->success_info;
			$ret['paid_amount'] = $this->paid_amount;
			$ret['success_ref_no'] = $this->success_ref_no;
		}else{
			$ret['success'] = 0;
		}
		
		$this->success_respond($ret);
	}
	
	public function prepare_void_payment(){
		global $con, $config, $appCore;
		
		// Params
		$this->receipt_ref_no = trim($_REQUEST['receipt_ref_no']);
		$this->transaction_date = date("Y-m-d", strtotime($_REQUEST['transaction_date']));
		
		// Validate
		$this->check_required_transaction_data();
		
		$this->success_info = array();
		$prev_data = array();
		$prev_data = $this->get_prev_payment_info(true);
		$guid = "";
		
		// return errors if the ewallet ref no could not be found from system
		if(!$prev_data['data']['success_ref_no']){
			$this->error_respond("Invalid eWallet Reference No");
		}
		$this->ewallet_ref_no = $prev_data['data']['success_ref_no'];
		
		$prms = array();
		$prms['branch_id'] = $this->bid;
		$prms['counter_id'] = $this->counter_id;
		$prms['date'] = $this->transaction_date;
		$prms['receipt_ref_no'] = $this->receipt_ref_no;
		
		// config + branch_id + counter_id + date (YYYYMMDD) + receipt_ref_no
		$arms_sign = $appCore->posManager->generate_arms_sign($prms);
		
		// found it is not match between counter and backend, return errors
		if($arms_sign != $_REQUEST['arms_sign']){
			$this->error_respond("Invalid ARMS Sign Key");
		}
		
		
		// Check system whether already paid before
		$need_update_success_info = false;
		$prev_void_payment_success = false;
		if($prev_data['data']){	// got previous payment
			$guid = $prev_data['data']['guid'];
			if($this->success_info){
				$prev_void_payment_success = true;
			
				if($prev_data['need_update']){	// need update database
					$need_update_success_info = true;
				}
			}
		}
		
		// First time pay or previous payment failed
		if(!$prev_void_payment_success){
			// Call API to void payment
			$prms = array();
			$prms['more_info'] = $prev_data['data']['more_info'];
			$result = $this->void_payment($prms);
			
			// Payment Failed
			if(!$result['ok']){
				// Record the failed info
				$upd2 = array();
				$upd2['last_update'] = 'CURRENT_TIMESTAMP';
				$upd2['failed_info'] = serialize($this->failed_obj);
				$con->sql_query("update pos_transaction_ewallet_payment set ".mysql_update_by_field($upd2)." where guid=".ms($guid));
				$this->error_respond($result['error']);
			}
			
			// Payment Success, need update database
			$need_update_success_info = true;
		}
		
		// Payment success and need update database
		if($need_update_success_info){
			// include the void success info into payment success info
			$more_info = array();
			$more_info['more_info'] = $prev_data['data']['more_info'];
			$more_info['void_success_info'] = $this->success_info;
		
			// Update Success info
			$upd2 = array();
			$upd2['more_info'] = serialize($more_info);
			$upd2['void_success'] = 1;
			$upd2['void_success_ref_no'] = $this->success_ref_no;
			$upd2['last_update'] = 'CURRENT_TIMESTAMP';
			$upd2['failed_info'] = '';
			$con->sql_query("update pos_transaction_ewallet_payment set ".mysql_update_by_field($upd2)." where guid=".ms($guid));
		}
		
		// Return Data to Counter
		$ret = array();
		$ret['success'] = 1;
		$ret['success_info'] = $this->success_info;
		$ret['success_ref_no'] = $this->success_ref_no;
		$ret['paid_amount'] = $this->paid_amount;
		$this->success_respond($ret);
	}
}

//print_r($_REQUEST);

if(isset($_REQUEST['ewallet_type'])){
	switch($_REQUEST['ewallet_type']){
		case 'boost':
			require_once('api.ewallet/boost/api.php');
			exit;
		default:
			if(preg_match("/^paydibs/", $_REQUEST['ewallet_type'])){
				require_once('api.ewallet/paydibs/api.php');
			}elseif(preg_match("/^ipay88/", $_REQUEST['ewallet_type'])){
				require_once('api.ewallet/ipay88/api.php');
			}
			exit;
	}
}

$ret = array();
$ret['failed'] = 1;
$ret['error'] = "Invalid eWallet Type";
print json_encode($ret);
exit;

?>