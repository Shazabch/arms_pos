<?php
/*
4/16/2019 11:36 AM Andy
- Added Cash Sales Integration.

5/14/2019 5:59 PM Andy
- Enhanced Cash Sales to have Deposit Product.
- Enhanced to remove Deposit Received Amount from total sales.

5/17/2019 4:48 PM Andy
- Added Account Receivable Integration.

5/31/2019 5:19 PM Andy
- Change AR customer_code to use branch integration_code.

8/9/2019 11:18 AM Andy
- Enhanced to log access token.

8/13/2019 1:23 PM Andy
- Enhanced to round 2 decimal for cash sales unit price, amount and tax amount.

1/6/2019 1:55 PM Andy
- Enhanced to round 2 decimal for all unit price, amount and tax amount.
*/
include("include/common.php");
ini_set('memory_limit', '1024M');
set_time_limit(0);

class API_ARMS_ACCOUNTING extends Module{
	var $is_debug = 0;
	var $folder = "attch/api.arms.accounting";
	var $log_folder = "attch/api.arms.accounting/logs";

	var $remote_ip = '';
	var $branch_list = array();
	var $selected_batch_id = '';
	var $branch_accounting_settings_list = array();
	var $credit_card_identifier_list = array();
	
	// Error List
	var $err_list = array(
		"invalid_api_method" => 'Invalid API Method.',
		"api_not_ready" => 'API Not Ready.',
		'authen_failed' => "Device Authentication Failed.",
		'server_config_error' => "ARMS Server Configuration is not setup properly",
		'invalid_access_token' => 'Invalid Access Token',
		'invalid_branch_code' => 'Invalid Branch Code [%s]',
		'invalid_branch' => 'Invalid Branch',
		'invalid_integration_start_date' => 'Invalid Integration Start Date',
		'require_grr_id' => 'GRR ID is Required',
		'invalid_grr_id' => 'Invalid GRR ID',
		'invalid_status_code' => 'Invalid Status Code',
		'required_doc_no' => 'Doc No is Required',
		'required_failed_reason' => 'Failed Reason is Required',
		'require_batch_id' => 'Batch ID is Required',
		'grr_not_found' => 'GRR Not Found',
		'grr_batch_id_not_match' => 'Batch ID Not Match with GRR',
		'require_tran_id' => 'Tran ID is Required',
		'tran_id_not_found' => 'Tran ID Not Found',
		'cs_batch_id_not_match' => 'Batch ID Not Match with Cash Sales',
		'require_do_id' => 'DO ID is Required',
		'invalid_do_id' => 'Invalid DO ID',
		'do_not_found' => 'DO Not Found',
		'do_batch_id_not_match' => 'Batch ID Not Match with DO',
	);
	
	var $tmp_tbl_ap_grr = 'tmp_ap_grr';
	var $tmp_tbl_ap_grr_tax = 'tmp_ap_grr_tax';
	
	function __construct($title){
		if($_SERVER['SERVER_NAME'] == 'maximus')	$this->is_debug = 1;
		
		// Convert all header to uppercase
		$tmp_header = getallheaders();
		if($tmp_header){
			foreach($tmp_header as $k=>$v){
				$k = str_replace('-', '_', strtoupper($k));
				$this->all_header[$k] = $v;
			}
		}
		
		//file_put_contents('test_api.txt', '');
		//file_put_contents('test_api.txt', print_r($this->all_header, true), FILE_APPEND);
		//print_r($this->all_header);exit;
		
		// Initialise Folder and Database
		$this->prepareDB();
		
		// Log Action Called
		$a = '';
		if(isset($_REQUEST['a']))	$a = $_REQUEST['a'];
		$this->remote_ip = $_SERVER['REMOTE_ADDR'];
		$this->put_log("Calling Method [$a], IP: ".$this->remote_ip);
		
		// Authentication
		$this->authentication();
		
		// Check if method not exists
		if(!$a || !method_exists($this, $a)){
			$this->error_die($this->err_list["invalid_api_method"]);
		}
				
		parent::__construct($title);
	}
	
	private function authentication(){
		global $config;
		
		$access_token = '';
		if(isset($config['arms_accounting_api_setting']['access_token']))	$access_token = $config['arms_accounting_api_setting']['access_token'];
		
		if(!$access_token){
			$this->error_die($this->err_list["server_config_error"]);
		}
		
		$this->put_log("Access Token: ".$this->all_header['X_ACCESS_TOKEN']);
		if($this->all_header['X_ACCESS_TOKEN'] != $access_token){
			$this->error_die($this->err_list["invalid_access_token"]);
		}
	}
	
	function _default(){
		
	}
	
	// function to create all db and folder
	private function prepareDB(){
		global $con, $pos_config;
		
		// Create Program Main Folder
		check_and_create_dir($this->folder);
		
		// Create Program Logs Folder
		check_and_create_dir($this->log_folder);
		
		// Credit Card Identifier
		$tmp_credit_card_identifier = $pos_config['credit_card'];
		foreach($tmp_credit_card_identifier as $c){
			$ptype = strtolower($c);
			$this->credit_card_identifier_list[$ptype] = $ptype;
		}
		//print_r($this->credit_card_identifier_list);exit;
	}
	
	private function put_log($log){
		$filename = date("Y-m-d").".txt";
		$str = date("Y-m-d H:i:s")."; ".$log;
		file_put_contents($this->log_folder."/".$filename, $str."\r\n", FILE_APPEND);
	}
	
	private function error_die($err_msg){
		$this->put_log($err_msg);
		
		$ret = array();
		$ret['result'] = 0;
		$ret['error_msg'] = $err_msg;
		
		$this->construct_return_header();
		print json_encode($ret);
		exit;
	}
	
	private function respond_data($ret){
		$this->put_log("Sending Data.");
		
		$this->construct_return_header();
		print json_encode($ret);
		$this->put_log("Data sent.");
		exit;
	}
	
	private function construct_return_header(){
		//header("Access-Control-Allow-Origin: *");
        //header("Access-Control-Allow-Methods: *");
        //header("Access-Control-Allow-Methods: 'GET, POST, OPTIONS'");
		//header("Access-Control-Allow-Headers: *");
		//header('Access-Control-Allow-Credentials: true');
		//header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header("Content-Type: application/json");
	}
	
	private function get_branch_accunting_settings($bid, $check_invalid_and_die = true){
		global $con, $config, $appCore;
		
		$this->branch_accounting_settings_list[$bid] = array();
		
		// Default get HQ settings
		$setting_bid = 1;
		
		if($bid > 1){
			// Not HQ, check whether use own settings
			$con->sql_query("select * from arms_acc_other_settings where branch_id=$bid and setting_name='use_own_settings' and setting_value=1");
			$use_own_settings = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			// Use own branch settings
			if($use_own_settings)	$setting_bid = $bid;
		}
		
		// Branch use the HQ Setting
		if($setting_bid == 1 && $bid != $setting_bid){
			if($this->branch_accounting_settings_list[$setting_bid]){
				// Already got HQ Data - clone HQ data to it
				$this->branch_accounting_settings_list[$bid] = $this->branch_accounting_settings_list[$setting_bid];
				return;
			}
		}
		
		// Accounting Settings
		$con->sql_query("select * from arms_acc_settings where branch_id=$setting_bid");
		while($r = $con->sql_fetchassoc()){
			$this->branch_accounting_settings_list[$bid]['normal'][$r['type']] = $r;
			
			// Also store a copy for HQ if it is using HQ settings
			if($setting_bid == 1 && $bid != $setting_bid){
				$this->branch_accounting_settings_list[$setting_bid]['normal'][$r['type']] = $r;
			}
		}
		$con->sql_freeresult();
		
		// Payment Type Settings
		$con->sql_query("select * from arms_acc_payment_settings where branch_id=$setting_bid");
		while($r = $con->sql_fetchassoc()){
			$this->branch_accounting_settings_list[$bid]['payment'][$r['payment_type']] = $r;
			
			// Also store a copy for HQ if it is using HQ settings
			if($setting_bid == 1 && $bid != $setting_bid){
				$this->branch_accounting_settings_list[$setting_bid]['payment'][$r['payment_type']] = $r;
			}
		}
		$con->sql_freeresult();
		
		if($config['enable_gst']){
			// GST Setting
			$con->sql_query("select * from arms_acc_gst_settings where branch_id=$setting_bid");
			while($r = $con->sql_fetchassoc()){
				$this->branch_accounting_settings_list[$bid]['gst_settings'][$r['gst_id']] = $r;
				
				// Also store a copy for HQ if it is using HQ settings
				if($setting_bid == 1 && $bid != $setting_bid){
					$this->branch_accounting_settings_list[$setting_bid]['gst_settings'][$r['gst_id']] = $r;
				}
			}
			$con->sql_freeresult();
		}
		
		// Other Settings
		$con->sql_query("select * from arms_acc_other_settings where branch_id=$setting_bid");
		while($r = $con->sql_fetchassoc()){
			$this->branch_accounting_settings_list[$bid]['other'][$r['setting_name']] = $r;
			
			// Also store a copy for HQ if it is using HQ settings
			if($setting_bid == 1 && $bid != $setting_bid){
				$this->branch_accounting_settings_list[$setting_bid]['other'][$r['setting_name']] = $r;
			}
		}
		$con->sql_freeresult();
		
		if($check_invalid_and_die){
			// Integration Start Date
			$tmp_date = trim($this->branch_accounting_settings_list[$bid]['other']['integration_start_date']['setting_value']);
			if(!$appCore->isValidDateFormat($tmp_date)){
				$this->error_die($this->err_list["invalid_integration_start_date"]);
			}
			
			if(date("Y", strtotime($tmp_date))<2018){
				$this->error_die($this->err_list["invalid_integration_start_date"]);
			}
		}
	}
	
	function get_ap(){
		global $con;
		
		$this->selected_batch_id = trim($_REQUEST['batch_id']);
		$branch_code_list = strtolower(trim($_REQUEST['branch_code']));
		$this->branch_list = array();
		
		// Check branch
		if($branch_code_list == 'all'){	// all branch
			$con->sql_query("select id,code,description from branch where active=1 order by sequence,code");
			while($r = $con->sql_fetchassoc()){
				$this->branch_list[$r['id']] = $r;
			}
			$con->sql_freeresult();
		}elseif($branch_code_list){	//selected branch
			$tmp_arr = explode(',', $branch_code_list);
			foreach($tmp_arr as $tmp_bcode){
				$con->sql_query("select id,code,description from branch where active=1 and code=".ms($tmp_bcode));
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$tmp){
					$this->error_die(sprintf($this->err_list["invalid_branch_code"], $tmp_bcode));
				}
				
				$this->branch_list[$tmp['id']] = $tmp;
			}
		}
		
		// no branch found
		if(!$this->branch_list){
			$this->error_die($this->err_list["invalid_branch"]);
		}
		
		foreach($this->branch_list as $bid => $b){
			$this->get_branch_accunting_settings($bid);
		}
		//print_r($this->branch_accounting_settings_list);exit;
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		$ret['invoice_data'] = array();
		
		foreach($this->branch_list as $bid => $b){
			// Get AP by branch
			$ap_list = $this->get_ap_by_branch($bid);
			
			if($ap_list && is_array($ap_list)){
				// Merge into result
				$ret['invoice_data'] = array_merge($ret['invoice_data'], $ap_list);
			}
		}		
		
		//$ret['temporary_message'] = 'Still Work in Progress';
		
		// Return Data
		$this->respond_data($ret);
	}
	
	private function get_ap_by_branch($bid){
		global $con, $appCore;
		
		$batch_id = '';
		if($this->selected_batch_id){	
			// Get Back the old batch_id
			$batch_id = $this->selected_batch_id;
		}else{
			// Get which batch need to resend
			$batch_id = trim($this->get_batch_id_need_resend($bid, 'ap'));
		}
		//print "batch_id = $batch_id";exit;
		// Integration Start Date
		$integration_start_date = trim($this->branch_accounting_settings_list[$bid]['other']['integration_start_date']['setting_value']);
			
		$filter = array();
		$filter[] = "grr.branch_id=$bid and grr.active=1 and grr.status=1";	// GRR
		$filter[] = "gi.type='INVOICE' and gi.doc_date>=".ms($integration_start_date);	// grr_items
		$filter[] = "grn.active=1 and grn.status=1 and grn.approved=1";	// grn

		if($batch_id){
			$filter[] = "gai.batch_id=".ms($batch_id);
		}else{
			$filter[] = "gai.batch_id is null";
		}
		
		$str_filter = "where ".join(' and ', $filter);
		
		$con->sql_begin_transaction();
		
		$sql = "select gi.*, if(gst.second_tax_code='' or gst.second_tax_code is null, gi.gst_code, gst.second_tax_code) as second_tax_code, v.code as vendor_code, gai.batch_id as arms_acc_batch_id, user.u as grr_owner, v.term as vendor_term, grr.currency_code, grr.currency_rate,
					(select po_user.u 
						from grr_items gi2 
						join po on po.po_no=gi2.doc_no and gi2.type='PO'
						left join user po_user on po_user.id=po.user_id
						where gi2.branch_id=grr.branch_id and gi2.grr_id=grr.id and gi2.type='PO' limit 1) as po_owner,
					grr.grr_amount, grr.tax_register, grr.tax_percent, grr.grr_tax, grr.is_under_gst, grr.grr_gst_amount, grr.arms_acc_exported
					from grr 
					join grr_items gi on grr.id = gi.grr_id and grr.branch_id = gi.branch_id
					join grn on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
					left join gst on gst.id=gi.gst_id
					left join vendor v on v.id=grr.vendor_id
					left join user on user.id=grr.user_id
					left join ap_arms_acc_info gai on gai.branch_id=grr.branch_id and gai.grr_id=grr.id
					$str_filter
					order by grr.branch_id, grr.id";
		//print $sql;exit;
		
		$q1 = $con->sql_query($sql);
		$inv_list = array();
		$bcode = $this->branch_list[$bid]['code'];
		$total_amt = $total_tax_amt = 0;
		$update_batch_id = '';
		
		while($r = $con->sql_fetchassoc($q1)){
			$grr_id = mi($r['grr_id']);
			$grr_key = $bid.'_'.$grr_id;
			
			if(!isset($inv_list[$grr_key])){
				$inv_list[$grr_key] = array();
				
				$inv_list[$grr_key]['grr_id'] = $bid.'_'.$grr_id;
				
				if($r['arms_acc_batch_id']){
					$inv_list[$grr_key]['batch_id'] = $r['arms_acc_batch_id'];
				}else{
					if(!$new_batch_id){
						// Generate New Batch ID
						$new_batch_id = trim($this->get_new_batch_id($bid, 'ap'));
					}
					$inv_list[$grr_key]['batch_id'] = $new_batch_id;
				}
				if(!$update_batch_id)	$update_batch_id = $inv_list[$grr_key]['batch_id'];
				
				$inv_list[$grr_key]['vendor_code'] = $r['vendor_code'];
				$inv_list[$grr_key]['doc_no'] = $r['doc_no'];
				$inv_list[$grr_key]['doc_date'] = $r['doc_date'];
				$inv_list[$grr_key]['purchase_agent'] = $r['po_owner'] ? $r['po_owner'] : $r['grr_owner'];
				$inv_list[$grr_key]['grr_no'] = $bcode.'-'.'GRR'.sprintf("%05d", $grr_id);
				$inv_list[$grr_key]['term'] = $r['vendor_term'];
				$inv_list[$grr_key]['pur_acc_code'] = $this->branch_accounting_settings_list[$bid]['normal']['purchase']['account_code'];
				$inv_list[$grr_key]['pur_acc_name'] = $this->branch_accounting_settings_list[$bid]['normal']['purchase']['account_name'];
				$inv_list[$grr_key]['amount'] = $r['grr_amount'];
				$inv_list[$grr_key]['tax_inclusive'] = 1;
				$inv_list[$grr_key]['tax_amount'] = 0;
				
				// Base Currency
				$inv_list[$grr_key]['currency_code'] = '';
				$inv_list[$grr_key]['exchange_rate'] = 1;
				
				if($r['currency_code']){
					// Foreign Currency
					$inv_list[$grr_key]['currency_code'] = $r['currency_code'];
					$inv_list[$grr_key]['exchange_rate'] = $r['currency_rate'];
				}
				
				if($r['is_under_gst']){
					// GST
					$inv_list[$grr_key]['tax_amount'] = $r['grr_gst_amount'];
				}elseif($r['tax_register']){
					// SST
					$inv_list[$grr_key]['tax_amount'] = $r['grr_tax'];
				}
				
				$inv_list[$grr_key]['tax_details'] = array();
				
				
				
				if(!$r['arms_acc_exported']){
					$con->sql_query("update grr set arms_acc_exported=1 where branch_id=$bid and id=$grr_id");
				}
				
				$total_amt += $inv_list[$grr_key]['amount'];
				$total_tax_amt += $inv_list[$grr_key]['tax_amount'];
			}
			
			// Tax Details
			if($r['is_under_gst']){
				// GST
				if($r['gst_id']){
					$tax_details = array();
					$tax_details['tax_code'] = $r['second_tax_code'] ? $r['second_tax_code'] : $r['gst_code'];
					$tax_details['tax_rate'] = $r['gst_rate'];
					$tax_details['amount'] = $r['amount'];
					$tax_details['tax_amount'] = $r['gst_amount'];
					$tax_details['tax_acc_code'] = $this->branch_accounting_settings_list[$bid]['gst_settings'][$r['gst_id']]['account_code'];
					if(!$tax_details['tax_acc_code']){
						$tax_details['tax_acc_code'] = $this->branch_accounting_settings_list[$bid]['normal']['global_input_tax']['account_code'];
					}
					$tax_details['tax_acc_name'] = $this->branch_accounting_settings_list[$bid]['gst_settings'][$r['gst_id']]['account_name'];
					if(!$tax_details['tax_acc_name']){
						$tax_details['tax_acc_name'] = $this->branch_accounting_settings_list[$bid]['normal']['global_input_tax']['account_name'];
					}					
					
					$inv_list[$grr_key]['tax_details'][] = $tax_details;
				}
			}elseif($r['tax_register']){
				// SST
				$tax_details = array();
				$tax_details['tax_code'] = $this->branch_accounting_settings_list[$bid]['other']['standard_ap_tax_code']['setting_value'];
				$tax_details['tax_rate'] = $r['tax_percent'];
				$tax_details['amount'] = $r['amount'];
				$tax_details['tax_amount'] = $r['tax'];
				$tax_details['tax_acc_code'] = $this->branch_accounting_settings_list[$bid]['normal']['global_input_tax']['account_code'];
				$tax_details['tax_acc_name'] = $this->branch_accounting_settings_list[$bid]['normal']['global_input_tax']['account_name'];
				$inv_list[$grr_key]['tax_details'][] = $tax_details;
			}
			
			// Update GRR batch_id
			$gai = array();
			$gai['branch_id'] = $bid;
			$gai['grr_id'] = $grr_id;
			$gai['batch_id'] = $inv_list[$grr_key]['batch_id'];
			$gai['tax_amount'] = $inv_list[$grr_key]['tax_amount'];
			$gai['amount'] = $inv_list[$grr_key]['amount'];
			$gai['inv_data'] = serialize($inv_list[$grr_key]);
			$gai['added'] = $gai['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into ap_arms_acc_info ".mysql_insert_by_field($gai)." on duplicate key update
				tax_amount=".mf($gai['tax_amount']).",
				amount=".mf($gai['amount']).",
				inv_data=".ms($gai['inv_data']).",
				last_update=CURRENT_TIMESTAMP");
		}
		$con->sql_freeresult($q1);
		
		if($inv_list)	$inv_list = array_values($inv_list);
		
		// mark last update for selected batch_id
		if($update_batch_id){
			$upd = array();
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$upd['tax_amount'] = $total_tax_amt;
			$upd['amount'] = $total_amt;
			$upd['inv_list_data'] = serialize($appCore->array_strval($inv_list));
			$con->sql_query("update arms_acc_batch_no set ".mysql_update_by_field($upd)." where branch_id=$bid and type='ap' and batch_id=".ms($update_batch_id));
		}
		
		$con->sql_commit();
		
		
		return $inv_list;
	}
	
	private function get_batch_id_need_resend($bid, $type){
		global $con;
		/*
			status
			================
			0 - Sent to them but they no notify they received
			1 - well received but haven't process to accounting software
			2 - succesfully processed 
			3 - failed to process
		*/
		$con->sql_query($sql = "select batch_id from arms_acc_batch_no where branch_id=".mi($bid)." and type=".ms($type)." and status in (0,3) order by id");
		//print $sql;exit;
		$batch_id_list = array();
		while($r = $con->sql_fetchassoc()){
			$batch_id_list[] = $r['batch_id'];
		}
		$con->sql_freeresult();
		//print_r($batch_id_list);exit;
		
		if($batch_id_list){
			foreach($batch_id_list as $batch_id){
				$batch_id = trim($batch_id);
		
				if($batch_id){	// check whether still got data status = 0 for this batch
					// Integration Start Date
					$integration_start_date = trim($this->branch_accounting_settings_list[$bid]['other']['integration_start_date']['setting_value']);
					
					if($type == 'ap'){
						// Purchase
						$filter = array();				
						$filter[] = "gai.branch_id=$bid and gai.status in (0,3) and gai.batch_id=".ms($batch_id);
						$str_filter = "where ".join(' and ', $filter);
					
						$con->sql_query("select gai.grr_id, grr.active, grr.status
							from ap_arms_acc_info gai
							left join grr on grr.branch_id=gai.branch_id and grr.id=gai.grr_id
							$str_filter
							limit 1");
						$tmp_grr = $con->sql_fetchassoc();
						$con->sql_freeresult();
						if($tmp_grr){
							if($tmp_grr['active'] != 1 || $tmp_grr['status'] != 1){
								// The GRR already reset or cancelled
								$con->sql_query("update ap_arms_acc_info set status=-1 where branch_id=$bid and grr_id=".mi($tmp_grr['grr_id'])." and batch_id=".ms($batch_id));
								unset($tmp_grr);
							}
						}
						
						if($tmp_grr){
							// Got GRR not yet notify received
							return $batch_id;
						}else{
							// all grr already received, check and update again arms_acc_batch_no
							$this->reupdate_grr_batch($bid, $batch_id);
						}
					}elseif($type == 'cs'){
						// Cash Sales
						$filter = array();				
						$filter[] = "csai.branch_id=$bid and csai.status in (0,3) and csai.batch_id=".ms($batch_id);
						$str_filter = "where ".join(' and ', $filter);
						
						$con->sql_query($sql = "select csai.*
							from cs_arms_acc_info csai
							$str_filter
							limit 1");
						//print $sql;exit;
						$tmp_csai = $con->sql_fetchassoc();
						$con->sql_freeresult();
						
						if($tmp_csai){
							// Got Cash sales not yet notify received
							return $batch_id;
						}else{
							// all cash sales already received, check and update again arms_acc_batch_no
							$this->reupdate_cs_batch($bid, $batch_id);
						}
					}elseif($type == 'ar'){
						// AR
						$filter = array();				
						$filter[] = "ai.branch_id=$bid and ai.status in (0,3) and ai.batch_id=".ms($batch_id);
						$str_filter = "where ".join(' and ', $filter);
					
						$con->sql_query($sql = "select ai.do_id, do.active, do.status, do.approved, do.checkout
							from ar_arms_acc_info ai
							left join do on do.branch_id=ai.branch_id and do.id=ai.do_id
							$str_filter
							limit 1");
						//print $sql;exit;
						$tmp_do = $con->sql_fetchassoc();
						$con->sql_freeresult();
						if($tmp_do){
							if($tmp_do['active'] != 1 || $tmp_do['status'] != 1 || $tmp_do['approved'] != 1 || $tmp_do['checkout'] != 1){
								// The DO already reset or cancelled
								$con->sql_query("update ar_arms_acc_info set status=-1 where branch_id=$bid and do_id=".mi($tmp_do['do_id'])." and batch_id=".ms($batch_id));
								unset($tmp_do);
							}
						}
						
						if($tmp_do){
							// Got Data not yet notify received
							return $batch_id;
						}else{
							// all data already received, check and update again arms_acc_batch_no
							$this->reupdate_ar_batch($bid, $batch_id);
						}
					}
				}
			}
		}
		
		return '';
	}
	
	
	
	private function get_new_batch_id($bid, $type){
		global $con;
		
		$con->sql_query("select max(id) as max_id from arms_acc_batch_no where branch_id=".mi($bid)." and type=".ms($type));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$max_id = mi($tmp['max_id']);
		
		$new_id = $max_id+1;
		$new_batch_id = $bid.'_'.sprintf("%06d", $new_id);
		
		$upd = array();
		$upd['branch_id'] = $bid;
		$upd['type'] = $type;
		$upd['id'] = $new_id;
		$upd['batch_id'] = $new_batch_id;
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into arms_acc_batch_no ".mysql_insert_by_field($upd));
		
		return $new_batch_id;
	}
	
	private function reupdate_grr_batch($bid, $batch_id){
		global $con;
		
		$status_list = array();
		// all grr already received, check and update again arms_acc_batch_no
		$con->sql_query("select status from ap_arms_acc_info where branch_id=$bid and batch_id=".ms($batch_id)."
			group by status
			order by status");
		while($r = $con->sql_fetchassoc()){
			$status_list[$r['status']] = 1;
		}
		$con->sql_freeresult();
		
		$upd = array();
		$upd['status'] = -1;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		if($status_list[3]){
			// Error
			$upd['status'] = 3;
		}elseif($status_list[0]){
			// New
			$upd['status'] = 0;
		}elseif($status_list[1]){
			// all received
			$upd['status'] = 1;
		}
		elseif($status_list[2]){
			// all processed
			$upd['status'] = 2;
		}
		$con->sql_query("update arms_acc_batch_no set ".mysql_update_by_field($upd)." where branch_id=$bid and batch_id=".ms($batch_id)." and type='ap'");
	}
	
	function notify_ap(){
		global $con;
		
		$tmp_grr_id = trim($_REQUEST['grr_id']);
		$batch_id = trim($_REQUEST['batch_id']);
		if(!$tmp_grr_id){
			$this->error_die($this->err_list["require_grr_id"]);
		}
		if(!$batch_id){
			$this->error_die($this->err_list["require_batch_id"]);
		}
		
		$status_code = mi($_REQUEST['status_code']);
		$failed_reason = trim($_REQUEST['failed_reason']);
		$acc_doc_no = trim($_REQUEST['acc_doc_no']);
		
		list($bid, $grr_id) = explode("_", $tmp_grr_id);
		$bid = mi($bid);
		$grr_id = mi($grr_id);
		if($bid <= 0 || $grr_id <= 0){
			$this->error_die($this->err_list["invalid_grr_id"]);
		}
		
		/*
			Status Code
			1 - well received but haven't process to accounting software
			2 - succesfully processed 
			3 - failed to process
		*/
		if($status_code == 1){
			
		}elseif($status_code == 2){
			if(!$acc_doc_no){
				$this->error_die($this->err_list["required_doc_no"]);
			}
		}elseif($status_code == 3){
			if(!$failed_reason){
				$this->error_die($this->err_list["required_failed_reason"]);
			}
		}else{
			$this->error_die($this->err_list["invalid_status_code"]);
		}
		
		// Select the data
		$con->sql_query("select * from ap_arms_acc_info where branch_id=$bid and grr_id=$grr_id");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Data Not Found
		if(!$data){
			$this->error_die($this->err_list["grr_not_found"]);
		}
		
		// Batch ID Not Match
		if($data['batch_id'] != $batch_id){
			$this->error_die($this->err_list["grr_batch_id_not_match"]);
		}
		
		$con->sql_begin_transaction();
		
		$upd = array();
		$upd['status'] = $status_code;
		$upd['acc_doc_no'] = '';
		$upd['failed_reason'] = '';
		if($status_code == 2){
			$upd['acc_doc_no'] = $acc_doc_no;
		}elseif($status_code == 3){
			$upd['failed_reason'] = $failed_reason;
		}
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update ap_arms_acc_info set ".mysql_update_by_field($upd)." where branch_id=$bid and grr_id=$grr_id");
		
		// Reupdate the GRR batch 
		$this->reupdate_grr_batch($bid, $batch_id);
		
		$con->sql_commit();
		
		$ret = array();
		$ret['result'] = 1;
		// Return Data
		$this->respond_data($ret);
	}
	
	function get_cs(){
		global $con;
		
		//$this->error_die($this->err_list["api_not_ready"]);
		
		$this->selected_batch_id = trim($_REQUEST['batch_id']);
		$branch_code_list = strtolower(trim($_REQUEST['branch_code']));
		$this->branch_list = array();
		
		// Check branch
		if($branch_code_list == 'all'){	// all branch
			$con->sql_query("select id,code,description from branch where active=1 order by sequence,code");
			while($r = $con->sql_fetchassoc()){
				$this->branch_list[$r['id']] = $r;
			}
			$con->sql_freeresult();
		}elseif($branch_code_list){	//selected branch
			$tmp_arr = explode(',', $branch_code_list);
			foreach($tmp_arr as $tmp_bcode){
				$con->sql_query("select id,code,description from branch where active=1 and code=".ms($tmp_bcode));
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$tmp){
					$this->error_die(sprintf($this->err_list["invalid_branch_code"], $tmp_bcode));
				}
				
				$this->branch_list[$tmp['id']] = $tmp;
			}
		}
		
		// no branch found
		if(!$this->branch_list){
			$this->error_die($this->err_list["invalid_branch"]);
		}
		
		foreach($this->branch_list as $bid => $b){
			$this->get_branch_accunting_settings($bid);
		}
		//print_r($this->branch_accounting_settings_list);exit;
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		$ret['sales_data'] = array();
		
		foreach($this->branch_list as $bid => $b){
			// Get AP by branch
			$cs_list = $this->get_cs_by_branch($bid);
			
			if($cs_list && is_array($cs_list)){
				// Merge into result
				$ret['sales_data'] = array_merge($ret['sales_data'], $cs_list);
			}
		}		
		
		//$ret['temporary_message'] = 'Still Work in Progress';
		
		// Return Data
		$this->respond_data($ret);
	}
	
	private function get_cs_by_branch($bid){
		global $con, $appCore;
		
		$batch_id = '';
		if($this->selected_batch_id){	
			// Get Back the old batch_id
			$batch_id = $this->selected_batch_id;
		}else{
			// Get which batch need to resend
			$batch_id = trim($this->get_batch_id_need_resend($bid, 'cs'));
		}
		//print "batch_id = $batch_id";exit;
		// Integration Start Date
		$integration_start_date = trim($this->branch_accounting_settings_list[$bid]['other']['integration_start_date']['setting_value']);
		
		// POS
		$filter = array();
		$filter[] = "p.branch_id=$bid and p.cancel_status=0";
		$filter[] = "p.date>=".ms($integration_start_date);
		//$filter[] = "p.date>='2019-4-4'";//.ms($integration_start_date);

		if($batch_id){
			$filter[] = "csai.batch_id=".ms($batch_id);
		}else{
			$filter[] = "csai.batch_id is null";
		}
		
		$str_filter = "where ".join(' and ', $filter);
		
		$con->sql_begin_transaction();
		
		$sql = "select p.branch_id, p.date, p.counter_id, p.id as pos_id, p.receipt_ref_no, csai.batch_id as arms_acc_batch_id, p.is_gst, p.arms_acc_exported, p.amount_change, csai.acc_tran_id,
					p.deposit, pd.deposit_amount
					from pos p 
					join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date and pf.finalized=1
					left join cs_arms_acc_info csai on csai.branch_id=p.branch_id and csai.type='pos' and csai.inv_no=p.receipt_ref_no
					left join pos_deposit pd on p.branch_id=pd.branch_id and p.date=pd.date and p.counter_id=pd.counter_id and p.id=pd.pos_id and p.deposit=1
					$str_filter
					order by p.branch_id, p.date, p.counter_id, p.id";
		//print $sql;exit;
		$q1 = $con->sql_query($sql);
		
		$inv_list = array();
		$total_amt = $total_tax_amt = 0;
		$update_batch_id = '';
		
		while($r = $con->sql_fetchassoc($q1)){
			$counter_id = mi($r['counter_id']);
			$pos_id = mi($r['pos_id']);
			$date_key = date("Ymd", strtotime($r['date'])).'_pos';
			$inv_data = array();
			
			// New Date = New Batch
			if(!isset($inv_list[$date_key])){
				$inv_list[$date_key] = array();								
				
				if($r['arms_acc_batch_id']){
					$inv_list[$date_key]['batch_id'] = $r['arms_acc_batch_id'];
				}else{
					if(!$new_batch_id){
						// Generate New Batch ID
						$new_batch_id = trim($this->get_new_batch_id($bid, 'cs'));
					}
					$inv_list[$date_key]['batch_id'] = $new_batch_id;
				}
				$update_batch_id = $inv_list[$date_key]['batch_id'];
				
				$inv_list[$date_key]['tran_id'] = $inv_list[$date_key]['batch_id'].'_'.$date_key;
				$inv_list[$date_key]['cs_acc_no'] = $this->branch_accounting_settings_list[$bid]['normal']['cash_sales']['account_code'];
				$inv_list[$date_key]['cs_acc_name'] = $this->branch_accounting_settings_list[$bid]['normal']['cash_sales']['account_name'];
				$inv_list[$date_key]['doc_date'] = $r['date'];
				$inv_list[$date_key]['sales_agent'] = 'Cash Sales';
				$inv_list[$date_key]['tax_inclusive'] = 1;
				$inv_list[$date_key]['amount'] = 0;
				$inv_list[$date_key]['tax_amount'] = 0;
				$inv_list[$date_key]['sales_items'] = array();
			}
			
			
			
			// POS Items
			$q2 = $con->sql_query($sql = "select pi.tax_code, if(gst.second_tax_code='' or gst.second_tax_code is null, pi.tax_code, gst.second_tax_code) as second_tax_code, pi.tax_rate,sum(round(pi.price-pi.discount-pi.discount2,2)) as amt_incl_tax, sum(pi.tax_amount) as tax_amount, sum(pi.qty) as qty, gst.id as gst_id
				from pos_items pi
				left join gst on gst.code=pi.tax_code
				where pi.branch_id=$bid and pi.date=".ms($r['date'])." and pi.counter_id=$counter_id and pi.pos_id=$pos_id
				group by tax_code");
			//print $sql;exit;
			while($pi = $con->sql_fetchassoc($q2)){
				$tax_key = $pi['tax_code'] ? $pi['tax_code'] : 'no_tax';
				
				if(!isset($inv_list[$date_key]['sales_items'][$tax_key])){
					$inv_list[$date_key]['sales_items'][$tax_key] = array();
					$inv_list[$date_key]['sales_items'][$tax_key]['cs_product_code'] = $this->branch_accounting_settings_list[$bid]['other']['cash_sales_standard_product_code']['setting_value'];
					$inv_list[$date_key]['sales_items'][$tax_key]['qty'] = 1;
					
					if($r['is_gst']){
						// GST
						$inv_list[$date_key]['sales_items'][$tax_key]['tax_code'] = $pi['second_tax_code'] ? $pi['second_tax_code'] : $pi['tax_code'];
						$inv_list[$date_key]['sales_items'][$tax_key]['tax_rate'] = $pi['tax_rate'];
						
						$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_code'] = $this->branch_accounting_settings_list[$bid]['gst_settings'][$pi['gst_id']]['account_code'];
						if(!$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_code']){
							$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_code'] = $this->branch_accounting_settings_list[$bid]['normal']['global_output_tax']['account_code'];
						}
						$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_name'] = $this->branch_accounting_settings_list[$bid]['gst_settings'][$pi['gst_id']]['account_name'];
						if(!$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_name']){
							$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_name'] = $this->branch_accounting_settings_list[$bid]['normal']['global_output_tax']['account_name'];
						}
					}else{
						// SST - no tax
					}
				}				
				
				$inv_list[$date_key]['sales_items'][$tax_key]['unit_price'] += round($pi['amt_incl_tax'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['unit_price'] = round($inv_list[$date_key]['sales_items'][$tax_key]['unit_price'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['amount'] = round($inv_list[$date_key]['sales_items'][$tax_key]['unit_price'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['tax_amount'] += round($pi['tax_amount'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['tax_amount'] = round($inv_list[$date_key]['sales_items'][$tax_key]['tax_amount'], 2);
				
				$inv_list[$date_key]['amount'] += $pi['amt_incl_tax'];
				$inv_list[$date_key]['tax_amount'] += $pi['tax_amount'];
				
				$total_amt += $pi['amt_incl_tax'];
				$total_tax_amt += $pi['tax_amount'];
				
				$inv_data['amount'] += $pi['amt_incl_tax'];
				$inv_data['tax_amount'] += $pi['tax_amount'];
				
				$inv_data['amount'] = round($inv_data['amount'], 2);
				$inv_data['tax_amount'] = round($inv_data['tax_amount'], 2);
				
			}
			$con->sql_freeresult($q2);
			
			// Deposit Received
			if($r['deposit']){
				// Rounding have no tax
				$tax_key = 'deposit';
				
				if(!isset($inv_list[$date_key]['sales_items'][$tax_key])){
					$inv_list[$date_key]['sales_items'][$tax_key] = array();
					$inv_list[$date_key]['sales_items'][$tax_key]['cs_product_code'] = $this->branch_accounting_settings_list[$bid]['other']['cash_sales_deposit_product_code']['setting_value'];
					$inv_list[$date_key]['sales_items'][$tax_key]['qty'] = 1;
				}				
				
				$inv_list[$date_key]['sales_items'][$tax_key]['unit_price'] += round($r['deposit_amount'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['unit_price'] = round($inv_list[$date_key]['sales_items'][$tax_key]['unit_price'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['amount'] = round($inv_list[$date_key]['sales_items'][$tax_key]['unit_price'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['tax_amount'] = 0;
				
				$inv_list[$date_key]['deposit_rcv_amount'] += $r['deposit_amount'];
				$inv_list[$date_key]['deposit_rcv_amount'] = round($inv_list[$date_key]['deposit_rcv_amount'], 2);
				
				//$total_amt += $r['deposit_amount'];
				//$inv_data['amount'] += $r['deposit_amount'];
			}
			
			// POS Payment
			if(!$inv_list[$date_key]['payment_data'])	$inv_list[$date_key]['payment_data'] = array();
			$q3 = $con->sql_query($sql = "select pp.* 
				from pos_payment pp
				where pp.branch_id=$bid and pp.date=".ms($r['date'])." and pp.counter_id=$counter_id and pp.pos_id=$pos_id and pp.adjust=0 and pp.type not in ('Discount', 'Mix & Match Total Disc', 'Rounding')");
			//print $sql;exit;
			while($pp = $con->sql_fetchassoc($q3)){
				$acc_ptype = $payment_type = strtolower(trim($pp['type']));
				
				if($this->credit_card_identifier_list[$acc_ptype]){
					// change visa, master all to "credit cards"
					$payment_type = $acc_ptype = 'credit cards';
				}
				
				// Remove the word ewallet_
				$payment_type = str_replace("ewallet_", "", $payment_type);
				
				$inv_list[$date_key]['payment_data'][$payment_type]['acc_code'] =  $this->branch_accounting_settings_list[$bid]['payment'][$acc_ptype]['account_code'];
				$inv_list[$date_key]['payment_data'][$payment_type]['acc_name'] =  $this->branch_accounting_settings_list[$bid]['payment'][$acc_ptype]['account_name'];
				$inv_list[$date_key]['payment_data'][$payment_type]['payment_type'] = $payment_type;
				$amt = $pp['amount'];
				if($pp['group_type'] == 'currency'){	// Foreign Currency
					list($foreign_amt, $rate) = explode('@', $pp['remark']);
					$inv_list[$date_key]['payment_data'][$payment_type]['foreign_amt'] += $foreign_amt;
					$rm_amt = round($foreign_amt / $rate, 2);
					$inv_list[$date_key]['payment_data'][$payment_type]['amount'] += $rm_amt;
					$inv_list[$date_key]['payment_data'][$payment_type]['exchange_rate'] = $inv_list[$date_key]['payment_data'][$payment_type]['amount'] / $inv_list[$date_key]['payment_data'][$payment_type]['foreign_amt'];
				}else{	// Base Currency
					$inv_list[$date_key]['payment_data'][$payment_type]['exchange_rate'] = 1;
					$inv_list[$date_key]['payment_data'][$payment_type]['amount'] += $amt;
				}
				
				$inv_list[$date_key]['payment_data'][$payment_type]['amount'] = round($inv_list[$date_key]['payment_data'][$payment_type]['amount'], 2);
			}
			$con->sql_freeresult($q3);
			
			// Rounding
			$q4 = $con->sql_query($sql = "select pp.* 
				from pos_payment pp
				where pp.branch_id=$bid and pp.date=".ms($r['date'])." and pp.counter_id=$counter_id and pp.pos_id=$pos_id and pp.adjust=0 and pp.type in ('Rounding')");
			//print $sql;exit;
			while($pp = $con->sql_fetchassoc($q4)){
				// Rounding have no tax
				$tax_key = 'rounding';
				
				if(!isset($inv_list[$date_key]['sales_items'][$tax_key])){
					$inv_list[$date_key]['sales_items'][$tax_key] = array();
					$inv_list[$date_key]['sales_items'][$tax_key]['cs_product_code'] = $this->branch_accounting_settings_list[$bid]['other']['cash_sales_rounding_product_code']['setting_value'];
					$inv_list[$date_key]['sales_items'][$tax_key]['qty'] = 1;
				}				
				
				$inv_list[$date_key]['sales_items'][$tax_key]['unit_price'] += $pp['amount'];
				$inv_list[$date_key]['sales_items'][$tax_key]['unit_price'] = round($inv_list[$date_key]['sales_items'][$tax_key]['unit_price'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['amount'] = round($inv_list[$date_key]['sales_items'][$tax_key]['unit_price'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['tax_amount'] = 0;
				
				$inv_list[$date_key]['amount'] += $pp['amount'];
				$inv_list[$date_key]['amount'] = round($inv_list[$date_key]['amount'], 2);
				
				$total_amt += $pp['amount'];
				$total_amt = round($total_amt, 2);
				
				$inv_data['amount'] += $pp['amount'];
				$inv_data['amount'] = round($inv_data['amount'], 2);
			}
			$con->sql_freeresult($q4);
			
			// Got amount change
			if($r['amount_change']){
				// create a cash payment
				$ptype = 'cash';
				$inv_list[$date_key]['payment_data'][$ptype]['acc_code'] =  $this->branch_accounting_settings_list[$bid]['payment'][$ptype]['account_code'];
				$inv_list[$date_key]['payment_data'][$ptype]['acc_name'] =  $this->branch_accounting_settings_list[$bid]['payment'][$ptype]['account_name'];
				$inv_list[$date_key]['payment_data'][$ptype]['payment_type'] = 'cash';
				$inv_list[$date_key]['payment_data'][$ptype]['exchange_rate'] = 1;
				$inv_list[$date_key]['payment_data'][$ptype]['amount'] -= $r['amount_change'];
				$inv_list[$date_key]['payment_data'][$ptype]['amount'] = round($inv_list[$date_key]['payment_data'][$ptype]['amount'], 2);
			}
			
			// Mark exported to accounting
			if(!$r['arms_acc_exported']){
				$con->sql_query("update pos set arms_acc_exported=1 where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and id=$pos_id");
			}
			
			// Update Cash Sales batch_id
			$gai = array();
			$gai['branch_id'] = $bid;
			$gai['type'] = 'pos';
			$gai['inv_no'] = $r['receipt_ref_no'];
			$gai['acc_tran_id'] = $inv_list[$date_key]['tran_id'];
			$gai['date'] = $r['date'];
			$gai['tax_amount'] = $inv_data['tax_amount'];
			$gai['amount'] = $inv_data['amount'];
			$gai['batch_id'] = $inv_list[$date_key]['batch_id'];
			$gai['added'] = $gai['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into cs_arms_acc_info ".mysql_insert_by_field($gai)." on duplicate key update
			tax_amount=".mf($gai['tax_amount']).",
			amount=".mf($gai['amount']).",
			last_update=CURRENT_TIMESTAMP");
		}
		$con->sql_freeresult($q1);
		
		// DO
		$filter = array();
		$filter[] = "do.branch_id=$bid and do.active=1 and do.status=1 and do.approved=1 and do.checkout=1 and (do.inv_no is not null and do.inv_no <>'') and do.do_type='open'";
		$filter[] = "do.do_date>=".ms($integration_start_date);

		if($batch_id){
			$filter[] = "csai.batch_id=".ms($batch_id);
		}else{
			$filter[] = "csai.batch_id is null";
		}
		
		$str_filter = "where ".join(' and ', $filter);
		
		$con->sql_begin_transaction();
		
		$sql = "select do.branch_id, do.id as do_id, do.do_date, csai.batch_id as arms_acc_batch_id, do.is_under_gst, do.arms_acc_exported, csai.acc_tran_id, do.inv_no, do.inv_sheet_adj_amt, do.total_round_inv_amt, do.inv_total_gst_amt, do.total_inv_amt
					from do
					left join cs_arms_acc_info csai on csai.branch_id=do.branch_id and csai.type='do' and csai.inv_no=do.inv_no
					$str_filter
					order by do.branch_id, do.id";
		//print $sql;exit;
		$q1 = $con->sql_query($sql);
		
		while($r = $con->sql_fetchassoc($q1)){			
			$do_id = mi($r['do_id']);
			$date_key = date("Ymd", strtotime($r['do_date'])).'_do';
			
			// New Date = New Batch
			if(!isset($inv_list[$date_key])){
				$inv_list[$date_key] = array();								
				
				if($r['arms_acc_batch_id']){
					$inv_list[$date_key]['batch_id'] = $r['arms_acc_batch_id'];
				}else{
					if(!$new_batch_id){
						// Generate New Batch ID
						$new_batch_id = trim($this->get_new_batch_id($bid, 'cs'));
					}
					$inv_list[$date_key]['batch_id'] = $new_batch_id;
				}
				$update_batch_id = $inv_list[$date_key]['batch_id'];
				
				$inv_list[$date_key]['tran_id'] = $inv_list[$date_key]['batch_id'].'_'.$date_key;
				$inv_list[$date_key]['cs_acc_no'] = $this->branch_accounting_settings_list[$bid]['normal']['cash_sales']['account_code'];
				$inv_list[$date_key]['cs_acc_name'] = $this->branch_accounting_settings_list[$bid]['normal']['cash_sales']['account_name'];
				$inv_list[$date_key]['doc_date'] = $r['do_date'];
				$inv_list[$date_key]['sales_agent'] = 'Cash Sales';
				$inv_list[$date_key]['tax_inclusive'] = 1;
				$inv_list[$date_key]['tax_amount'] = 0;
				$inv_list[$date_key]['amount'] = 0;
			
				$inv_list[$date_key]['sales_items'] = array();
			}
			
			$inv_data['tax_amount'] =
			
			$inv_list[$date_key]['tax_amount'] += $r['inv_total_gst_amt'];
			$inv_list[$date_key]['amount'] += $r['total_inv_amt'];
			
			$total_tax_amt += $r['inv_total_gst_amt'];
			$total_tax_amt = round($total_tax_amt, 2);
			
			$total_amt += $r['total_inv_amt'];
			$total_amt = round($total_amt, 2);
			
			// Update Cash Sales batch_id
			$gai = array();
			$gai['branch_id'] = $bid;
			$gai['type'] = 'do';
			$gai['inv_no'] = $r['inv_no'];
			$gai['acc_tran_id'] = $inv_list[$date_key]['tran_id'];
			$gai['date'] = $r['do_date'];
			$gai['tax_amount'] = $r['inv_total_gst_amt'];
			$gai['amount'] = $r['total_inv_amt'];
			$gai['batch_id'] = $inv_list[$date_key]['batch_id'];
			$gai['added'] = $gai['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into cs_arms_acc_info ".mysql_insert_by_field($gai)." on duplicate key update
			tax_amount=".mf($gai['tax_amount']).",
			amount=".mf($gai['amount']).",
			last_update=CURRENT_TIMESTAMP");
			
			// DO Items
			$q2 = $con->sql_query($sql = "select di.gst_code as tax_code, if(gst.second_tax_code='' or gst.second_tax_code is null, di.gst_code, gst.second_tax_code) as second_tax_code, di.gst_rate as tax_rate, di.gst_id, sum(round(di.inv_line_amt2,2)) as amt_incl_tax, sum(di.inv_line_gst_amt2) as tax_amount
				from do_items di
				left join gst on gst.id=di.gst_id
				where di.branch_id=$bid and di.do_id=$do_id
				group by tax_code");
			//print $sql;exit;
			while($di = $con->sql_fetchassoc($q2)){
				$tax_key = $di['tax_code'] ? $di['tax_code'] : 'no_tax';
				
				if(!isset($inv_list[$date_key]['sales_items'][$tax_key])){
					$inv_list[$date_key]['sales_items'][$tax_key] = array();
					$inv_list[$date_key]['sales_items'][$tax_key]['cs_product_code'] = $this->branch_accounting_settings_list[$bid]['other']['cash_sales_standard_product_code']['setting_value'];
					$inv_list[$date_key]['sales_items'][$tax_key]['qty'] = 1;
					
					if($r['is_under_gst']){
						// GST
						$inv_list[$date_key]['sales_items'][$tax_key]['tax_code'] = $di['second_tax_code'] ? $di['second_tax_code'] : $di['tax_code'];
						$inv_list[$date_key]['sales_items'][$tax_key]['tax_rate'] = $di['tax_rate'];
						
						$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_code'] = $this->branch_accounting_settings_list[$bid]['gst_settings'][$di['gst_id']]['account_code'];
						if(!$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_code']){
							$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_code'] = $this->branch_accounting_settings_list[$bid]['normal']['global_output_tax']['account_code'];
						}
						$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_name'] = $this->branch_accounting_settings_list[$bid]['gst_settings'][$di['gst_id']]['account_name'];
						if(!$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_name']){
							$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_name'] = $this->branch_accounting_settings_list[$bid]['normal']['global_output_tax']['account_name'];
						}
					}else{
						// SST - no tax
					}
				}				
				
				$inv_list[$date_key]['sales_items'][$tax_key]['unit_price'] += round($di['amt_incl_tax'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['unit_price'] = round($inv_list[$date_key]['sales_items'][$tax_key]['unit_price'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['amount'] = round($inv_list[$date_key]['sales_items'][$tax_key]['unit_price'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['tax_amount'] += round($di['tax_amount'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['tax_amount'] = round($inv_list[$date_key]['sales_items'][$tax_key]['tax_amount'], 2);
			}
			$con->sql_freeresult($q2);
			
			// DO Open Items
			$q2 = $con->sql_query($sql = "select di.gst_code as tax_code, if(gst.second_tax_code='' or gst.second_tax_code is null, di.gst_code, gst.second_tax_code) as second_tax_code, di.gst_rate as tax_rate, di.gst_id, sum(round(di.inv_line_amt2,2)) as amt_incl_tax, sum(di.inv_line_gst_amt2) as tax_amount
				from do_open_items di
				left join gst on gst.id=di.gst_id
				where di.branch_id=$bid and di.do_id=$do_id
				group by tax_code");
			//print $sql;exit;
			while($di = $con->sql_fetchassoc($q2)){
				$tax_key = $di['tax_code'] ? $di['tax_code'] : 'no_tax';
				
				if(!isset($inv_list[$date_key]['sales_items'][$tax_key])){
					$inv_list[$date_key]['sales_items'][$tax_key] = array();
					$inv_list[$date_key]['sales_items'][$tax_key]['cs_product_code'] = $this->branch_accounting_settings_list[$bid]['other']['cash_sales_standard_product_code']['setting_value'];
					$inv_list[$date_key]['sales_items'][$tax_key]['qty'] = 1;
					
					if($r['is_under_gst']){
						// GST
						$inv_list[$date_key]['sales_items'][$tax_key]['tax_code'] = $di['second_tax_code'] ? $di['second_tax_code'] : $di['tax_code'];
						$inv_list[$date_key]['sales_items'][$tax_key]['tax_rate'] = $di['tax_rate'];
						
						$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_code'] = $this->branch_accounting_settings_list[$bid]['gst_settings'][$di['gst_id']]['account_code'];
						if(!$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_code']){
							$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_code'] = $this->branch_accounting_settings_list[$bid]['normal']['global_output_tax']['account_code'];
						}
						$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_name'] = $this->branch_accounting_settings_list[$bid]['gst_settings'][$di['gst_id']]['account_name'];
						if(!$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_name']){
							$inv_list[$date_key]['sales_items'][$tax_key]['tax_acc_name'] = $this->branch_accounting_settings_list[$bid]['normal']['global_output_tax']['account_name'];
						}
					}else{
						// SST - no tax
					}
				}				
				
				$inv_list[$date_key]['sales_items'][$tax_key]['unit_price'] += round($di['amt_incl_tax'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['unit_price'] = round($inv_list[$date_key]['sales_items'][$tax_key]['unit_price'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['amount'] = round($inv_list[$date_key]['sales_items'][$tax_key]['unit_price'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['tax_amount'] += round($di['tax_amount'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['tax_amount'] = round($inv_list[$date_key]['sales_items'][$tax_key]['tax_amount'], 2);
			}
			$con->sql_freeresult($q2);
			
			// Invoice Amount Adjustment and Rounding
			if($r['inv_sheet_adj_amt'] || $r['total_round_inv_amt']){
				$tax_key = 'rounding';
				
				if(!isset($inv_list[$date_key]['sales_items'][$tax_key])){
					$inv_list[$date_key]['sales_items'][$tax_key] = array();
					$inv_list[$date_key]['sales_items'][$tax_key]['cs_product_code'] = $this->branch_accounting_settings_list[$bid]['other']['cash_sales_rounding_product_code']['setting_value'];
					$inv_list[$date_key]['sales_items'][$tax_key]['qty'] = 1;
				}				
				
				$inv_list[$date_key]['sales_items'][$tax_key]['unit_price'] += round($r['inv_sheet_adj_amt']+$r['total_round_inv_amt'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['unit_price'] = round($inv_list[$date_key]['sales_items'][$tax_key]['unit_price'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['amount'] = round($inv_list[$date_key]['sales_items'][$tax_key]['unit_price'], 2);
				$inv_list[$date_key]['sales_items'][$tax_key]['tax_amount'] = 0;
			}
			
			// create a cash payment
			$ptype = 'cash';
			$inv_list[$date_key]['payment_data'][$ptype]['acc_code'] =  $this->branch_accounting_settings_list[$bid]['payment'][$ptype]['account_code'];
			$inv_list[$date_key]['payment_data'][$ptype]['acc_name'] =  $this->branch_accounting_settings_list[$bid]['payment'][$ptype]['account_name'];
			$inv_list[$date_key]['payment_data'][$ptype]['payment_type'] = 'cash';
			$inv_list[$date_key]['payment_data'][$ptype]['exchange_rate'] = 1;
			$inv_list[$date_key]['payment_data'][$ptype]['amount'] += $r['total_inv_amt'];
			$inv_list[$date_key]['payment_data'][$ptype]['amount'] = round($inv_list[$date_key]['payment_data'][$ptype]['amount'], 2);
			
			// Mark exported to accounting
			if(!$r['arms_acc_exported']){
				$con->sql_query("update do set arms_acc_exported=1 where branch_id=$bid and id=$do_id");
			}
		}
		$con->sql_freeresult($q1);
		
		
		
		if($inv_list){
			// Sort data by date
			ksort($inv_list);
			
			foreach($inv_list as $date_key => $inv_data){
				// convert the array key to numeric
				if($inv_data['sales_items']){
					ksort($inv_data['sales_items']);
					$inv_list[$date_key]['sales_items'] = array_values($inv_data['sales_items']);
				}
				if($inv_data['payment_data']){
					// convert the array key to numeric
					ksort($inv_data['payment_data']);
					$inv_list[$date_key]['payment_data'] = array_values($inv_data['payment_data']);
				}
			}
			
			$inv_list = array_values($inv_list);
		}
		
		// mark last update for selected batch_id
		if($update_batch_id){
			$upd = array();
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$upd['tax_amount'] = $total_tax_amt;
			$upd['amount'] = $total_amt;
			$upd['inv_list_data'] = serialize($appCore->array_strval($inv_list));
			$con->sql_query("update arms_acc_batch_no set ".mysql_update_by_field($upd)." where branch_id=$bid and type='cs' and batch_id=".ms($update_batch_id));
		}
		
		$con->sql_commit();
		
		//print_r($inv_list);exit;
		return $inv_list;
	}
	
	function notify_cs(){
		global $con;
		
		//$this->error_die($this->err_list["api_not_ready"]);
		
		$tran_id = trim($_REQUEST['tran_id']);
		$batch_id = trim($_REQUEST['batch_id']);
		if(!$tran_id){
			$this->error_die($this->err_list["require_tran_id"]);
		}
		if(!$batch_id){
			$this->error_die($this->err_list["require_batch_id"]);
		}
		
		$status_code = mi($_REQUEST['status_code']);
		$failed_reason = trim($_REQUEST['failed_reason']);
		$acc_doc_no = trim($_REQUEST['acc_doc_no']);
				
		/*
			Status Code
			1 - well received but haven't process to accounting software
			2 - succesfully processed 
			3 - failed to process
		*/
		if($status_code == 1){
			
		}elseif($status_code == 2){
			if(!$acc_doc_no){
				$this->error_die($this->err_list["required_doc_no"]);
			}
		}elseif($status_code == 3){
			if(!$failed_reason){
				$this->error_die($this->err_list["required_failed_reason"]);
			}
		}else{
			$this->error_die($this->err_list["invalid_status_code"]);
		}
		
		// Select the data
		$con->sql_query("select * from cs_arms_acc_info where acc_tran_id=".ms($tran_id)." limit 1");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Data Not Found
		if(!$data){
			$this->error_die($this->err_list["tran_id_not_found"]);
		}
		$bid = mi($data['branch_id']);
		
		// Batch ID Not Match
		if($data['batch_id'] != $batch_id){
			$this->error_die($this->err_list["cs_batch_id_not_match"]);
		}
		
		$con->sql_begin_transaction();
		
		$upd = array();
		$upd['status'] = $status_code;
		$upd['acc_doc_no'] = '';
		$upd['failed_reason'] = '';
		if($status_code == 2){
			$upd['acc_doc_no'] = $acc_doc_no;
		}elseif($status_code == 3){
			$upd['failed_reason'] = $failed_reason;
		}
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update cs_arms_acc_info set ".mysql_update_by_field($upd)." where acc_tran_id=".ms($tran_id));
		
		// Reupdate the batch 
		$this->reupdate_cs_batch($bid, $batch_id);
		
		$con->sql_commit();
		
		$ret = array();
		$ret['result'] = 1;
		// Return Data
		$this->respond_data($ret);
	}
	
	private function reupdate_cs_batch($bid, $batch_id){
		global $con;
		
		$status_list = array();
		// all grr already received, check and update again arms_acc_batch_no
		$con->sql_query("select status from cs_arms_acc_info where branch_id=$bid and batch_id=".ms($batch_id)."
			group by status
			order by status");
		while($r = $con->sql_fetchassoc()){
			$status_list[$r['status']] = 1;
		}
		$con->sql_freeresult();
		
		$upd = array();
		$upd['status'] = -1;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		if($status_list[3]){
			// Error
			$upd['status'] = 3;
		}elseif($status_list[0]){
			// New
			$upd['status'] = 0;
		}elseif($status_list[1]){
			// all received
			$upd['status'] = 1;
		}
		elseif($status_list[2]){
			// all processed
			$upd['status'] = 2;
		}
		$con->sql_query("update arms_acc_batch_no set ".mysql_update_by_field($upd)." where branch_id=$bid and batch_id=".ms($batch_id)." and type='cs'");
	}
	
	function get_ar(){
		global $con;
		
		$this->selected_batch_id = trim($_REQUEST['batch_id']);
		$branch_code_list = strtolower(trim($_REQUEST['branch_code']));
		$this->branch_list = array();
		
		// Check branch
		if($branch_code_list == 'all'){	// all branch
			$con->sql_query("select id,code,description from branch where active=1 order by sequence,code");
			while($r = $con->sql_fetchassoc()){
				$this->branch_list[$r['id']] = $r;
			}
			$con->sql_freeresult();
		}elseif($branch_code_list){	//selected branch
			$tmp_arr = explode(',', $branch_code_list);
			foreach($tmp_arr as $tmp_bcode){
				$con->sql_query("select id,code,description from branch where active=1 and code=".ms($tmp_bcode));
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$tmp){
					$this->error_die(sprintf($this->err_list["invalid_branch_code"], $tmp_bcode));
				}
				
				$this->branch_list[$tmp['id']] = $tmp;
			}
		}
		
		// no branch found
		if(!$this->branch_list){
			$this->error_die($this->err_list["invalid_branch"]);
		}
		
		foreach($this->branch_list as $bid => $b){
			$this->get_branch_accunting_settings($bid);
		}
		//print_r($this->branch_accounting_settings_list);exit;
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		$ret['invoice_data'] = array();
		
		foreach($this->branch_list as $bid => $b){
			// Get AR by branch
			$ar_list = $this->get_ar_by_branch($bid);
			
			if($ar_list && is_array($ar_list)){
				// Merge into result
				$ret['invoice_data'] = array_merge($ret['invoice_data'], $ar_list);
			}
		}
		
		// Return Data
		$this->respond_data($ret);
	}
	
	private function get_ar_by_branch($bid){
		global $con, $appCore;
		
		$batch_id = '';
		if($this->selected_batch_id){	
			// Get Back the old batch_id
			$batch_id = $this->selected_batch_id;
		}else{
			// Get which batch need to resend
			$batch_id = trim($this->get_batch_id_need_resend($bid, 'ar'));
		}
		//print "batch_id = $batch_id";exit;
		// Integration Start Date
		$integration_start_date = trim($this->branch_accounting_settings_list[$bid]['other']['integration_start_date']['setting_value']);
			
		$filter = array();
		$filter[] = "do.branch_id=$bid and do.active=1 and do.status=1 and do.checkout=1 and do.inv_no<>'' and do.do_type in ('transfer', 'credit_sales')";	// DO
		$filter[] = "do.do_date>=".ms($integration_start_date);

		if($batch_id){
			$filter[] = "ai.batch_id=".ms($batch_id);
		}else{
			$filter[] = "ai.batch_id is null";
		}
		
		$str_filter = "where ".join(' and ', $filter);
		
		$con->sql_begin_transaction();
		
		$sql = "select do.branch_id, do.id as do_id, do.do_date, ai.batch_id as arms_acc_batch_id, do.is_under_gst, do.arms_acc_exported, do.inv_no, do.do_type, b2.integration_code branch_integration_code, d2.code as debtor_code, d2.term as debtor_term, do.total_round_inv_amt, do.inv_sheet_adj_amt, u.u as do_owner
					from do
					left join ar_arms_acc_info ai on ai.branch_id=do.branch_id and ai.do_id=do.id
					left join branch b2 on b2.id=do.do_branch_id and do.do_type='transfer'
					left join debtor d2 on d2.id=do.debtor_id and do.do_type='credit_sales'
					left join user u on u.id=do.user_id
					$str_filter
					order by do.branch_id, do.id";
		//print $sql;exit;
		
		$q1 = $con->sql_query($sql);
		$inv_list = array();
		$bcode = $this->branch_list[$bid]['code'];
		$total_amt = $total_tax_amt = 0;
		$update_batch_id = '';
		
		while($r = $con->sql_fetchassoc($q1)){
			$do_id = mi($r['do_id']);
			$do_key = $bid.'_'.$do_id;
			
			$inv_list[$do_key] = array();
			$inv_list[$do_key]['do_id'] = $bid.'_'.$do_id;
			
			if($r['arms_acc_batch_id']){
				$inv_list[$do_key]['batch_id'] = $r['arms_acc_batch_id'];
			}else{
				if(!$new_batch_id){
					// Generate New Batch ID
					$new_batch_id = trim($this->get_new_batch_id($bid, 'ar'));
				}
				$inv_list[$do_key]['batch_id'] = $new_batch_id;
			}
			if(!$update_batch_id)	$update_batch_id = $inv_list[$do_key]['batch_id'];
			
			if($r['do_type'] =='transfer'){
				$inv_list[$do_key]['customer_code'] = $r['branch_integration_code'];
			}else{
				$inv_list[$do_key]['customer_code'] = $r['debtor_code'];
			}
			
			$inv_list[$do_key]['doc_no'] = $r['inv_no'];
			$inv_list[$do_key]['doc_date'] = $r['do_date'];
			$inv_list[$do_key]['sales_agent'] = $r['do_owner'];
			$inv_list[$do_key]['term'] = $r['do_type'] == 'credit_sales' ? $r['debtor_term'] : '';
			$inv_list[$do_key]['ar_acc_code'] = $this->branch_accounting_settings_list[$bid]['normal']['account_receivable']['account_code'];
			$inv_list[$do_key]['ar_acc_name'] = $this->branch_accounting_settings_list[$bid]['normal']['account_receivable']['account_name'];
			$inv_list[$do_key]['amount'] = 0;
			$inv_list[$do_key]['tax_amount'] = 0;
			$inv_list[$do_key]['tax_inclusive'] = 1;
			$inv_list[$do_key]['sales_items'] = array();
			
			// DO Items
			$q2 = $con->sql_query($sql = "select di.gst_code as tax_code, if(gst.second_tax_code='' or gst.second_tax_code is null, di.gst_code, gst.second_tax_code) as second_tax_code, di.gst_rate as tax_rate, di.gst_id, sum(round(di.inv_line_amt2,2)) as amt_incl_tax, sum(di.inv_line_gst_amt2) as tax_amount
				from do_items di
				left join gst on gst.id=di.gst_id
				where di.branch_id=$bid and di.do_id=$do_id
				group by tax_code");
			//print $sql;exit;
			while($di = $con->sql_fetchassoc($q2)){
				$tax_key = $di['tax_code'] ? $di['tax_code'] : 'no_tax';
				
				if(!isset($inv_list[$do_key]['sales_items'][$tax_key])){
					$inv_list[$do_key]['sales_items'][$tax_key] = array();
					$inv_list[$do_key]['sales_items'][$tax_key]['ar_product_code'] = $this->branch_accounting_settings_list[$bid]['other']['ar_standard_product_code']['setting_value'];
					//$inv_list[$do_key]['sales_items'][$tax_key]['qty'] = 1;
					
					if($r['is_under_gst']){
						// GST
						$inv_list[$do_key]['sales_items'][$tax_key]['tax_code'] = $di['second_tax_code'] ? $di['second_tax_code'] : $di['tax_code'];
						$inv_list[$do_key]['sales_items'][$tax_key]['tax_rate'] = $di['tax_rate'];
						
						$inv_list[$do_key]['sales_items'][$tax_key]['tax_acc_code'] = $this->branch_accounting_settings_list[$bid]['gst_settings'][$di['gst_id']]['account_code'];
						if(!$inv_list[$do_key]['sales_items'][$tax_key]['tax_acc_code']){
							$inv_list[$do_key]['sales_items'][$tax_key]['tax_acc_code'] = $this->branch_accounting_settings_list[$bid]['normal']['global_output_tax']['account_code'];
						}
						$inv_list[$do_key]['sales_items'][$tax_key]['tax_acc_name'] = $this->branch_accounting_settings_list[$bid]['gst_settings'][$di['gst_id']]['account_name'];
						if(!$inv_list[$do_key]['sales_items'][$tax_key]['tax_acc_name']){
							$inv_list[$do_key]['sales_items'][$tax_key]['tax_acc_name'] = $this->branch_accounting_settings_list[$bid]['normal']['global_output_tax']['account_name'];
						}
					}else{
						// SST - no tax
					}
				}				
				
				//$inv_list[$do_key]['sales_items'][$tax_key]['unit_price'] += $di['amt_incl_tax'];
				$inv_list[$do_key]['sales_items'][$tax_key]['amount'] += $di['amt_incl_tax'];
				$inv_list[$do_key]['sales_items'][$tax_key]['tax_amount'] += $di['tax_amount'];
				
				$inv_list[$do_key]['amount'] += $di['amt_incl_tax'];
				$inv_list[$do_key]['tax_amount'] += $di['tax_amount'];
			}
			$con->sql_freeresult($q2);
			
			// DO Open Items
			$q3 = $con->sql_query($sql = "select di.gst_code as tax_code, if(gst.second_tax_code='' or gst.second_tax_code is null, di.gst_code, gst.second_tax_code) as second_tax_code, di.gst_rate as tax_rate, di.gst_id, sum(round(di.inv_line_amt2,2)) as amt_incl_tax, sum(di.inv_line_gst_amt2) as tax_amount
				from do_open_items di
				left join gst on gst.id=di.gst_id
				where di.branch_id=$bid and di.do_id=$do_id
				group by tax_code");
			//print $sql;exit;
			while($di = $con->sql_fetchassoc($q3)){
				$tax_key = $di['tax_code'] ? $di['tax_code'] : 'no_tax';
				
				if(!isset($inv_list[$do_key]['sales_items'][$tax_key])){
					$inv_list[$do_key]['sales_items'][$tax_key] = array();
					$inv_list[$do_key]['sales_items'][$tax_key]['ar_product_code'] = $this->branch_accounting_settings_list[$bid]['other']['ar_standard_product_code']['setting_value'];
					//$inv_list[$do_key]['sales_items'][$tax_key]['qty'] = 1;
					
					if($r['is_under_gst']){
						// GST
						$inv_list[$do_key]['sales_items'][$tax_key]['tax_code'] = $di['second_tax_code'] ? $di['second_tax_code'] : $di['tax_code'];
						$inv_list[$do_key]['sales_items'][$tax_key]['tax_rate'] = $di['tax_rate'];
						
						$inv_list[$do_key]['sales_items'][$tax_key]['tax_acc_code'] = $this->branch_accounting_settings_list[$bid]['gst_settings'][$di['gst_id']]['account_code'];
						if(!$inv_list[$do_key]['sales_items'][$tax_key]['tax_acc_code']){
							$inv_list[$do_key]['sales_items'][$tax_key]['tax_acc_code'] = $this->branch_accounting_settings_list[$bid]['normal']['global_output_tax']['account_code'];
						}
						$inv_list[$do_key]['sales_items'][$tax_key]['tax_acc_name'] = $this->branch_accounting_settings_list[$bid]['gst_settings'][$di['gst_id']]['account_name'];
						if(!$inv_list[$do_key]['sales_items'][$tax_key]['tax_acc_name']){
							$inv_list[$do_key]['sales_items'][$tax_key]['tax_acc_name'] = $this->branch_accounting_settings_list[$bid]['normal']['global_output_tax']['account_name'];
						}
					}else{
						// SST - no tax
					}
				}				
				
				//$inv_list[$do_key]['sales_items'][$tax_key]['unit_price'] += $di['amt_incl_tax'];
				$inv_list[$do_key]['sales_items'][$tax_key]['amount'] += $di['amt_incl_tax'];
				$inv_list[$do_key]['sales_items'][$tax_key]['tax_amount'] += $di['tax_amount'];
				
				$inv_list[$do_key]['amount'] += $di['amt_incl_tax'];
				$inv_list[$do_key]['tax_amount'] += $di['tax_amount'];
			}
			$con->sql_freeresult($q3);
			
			// Invoice Amount Adjustment and Rounding
			if($r['inv_sheet_adj_amt'] || $r['total_round_inv_amt']){
				$tax_key = 'rounding';
				
				if(!isset($inv_list[$do_key]['sales_items'][$tax_key])){
					$inv_list[$do_key]['sales_items'][$tax_key] = array();
					$inv_list[$do_key]['sales_items'][$tax_key]['ar_product_code'] = $this->branch_accounting_settings_list[$bid]['other']['ar_rounding_product_code']['setting_value'];
					//$inv_list[$do_key]['sales_items'][$tax_key]['qty'] = 1;
				}				
				
				//$inv_list[$do_key]['sales_items'][$tax_key]['unit_price'] += ($r['inv_sheet_adj_amt']+$r['total_round_inv_amt']);
				$inv_list[$do_key]['sales_items'][$tax_key]['amount'] += ($r['inv_sheet_adj_amt']+$r['total_round_inv_amt']);
				$inv_list[$do_key]['sales_items'][$tax_key]['tax_amount'] = 0;
				
				$inv_list[$do_key]['amount'] += ($r['inv_sheet_adj_amt']+$r['total_round_inv_amt']);
			}
			
			// Mark Exported
			if(!$r['arms_acc_exported']){
				$con->sql_query("update do set arms_acc_exported=1 where branch_id=$bid and id=$do_id");
			}
			
			$total_amt += $inv_list[$do_key]['amount'];
			$total_tax_amt += $inv_list[$do_key]['tax_amount'];
			
			
			// Update batch_id
			$gai = array();
			$gai['branch_id'] = $bid;
			$gai['do_id'] = $do_id;
			$gai['batch_id'] = $inv_list[$do_key]['batch_id'];
			$gai['tax_amount'] = $inv_list[$do_key]['tax_amount'];
			$gai['amount'] = $inv_list[$do_key]['amount'];
			$gai['inv_data'] = serialize($appCore->array_strval($inv_list[$do_key]));
			$gai['added'] = $gai['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into ar_arms_acc_info ".mysql_insert_by_field($gai)." on duplicate key update
				tax_amount=".mf($gai['tax_amount']).",
				amount=".mf($gai['amount']).",
				inv_data=".ms($gai['inv_data']).",
				last_update=CURRENT_TIMESTAMP");
		}
		$con->sql_freeresult($q1);
		
		if($inv_list){			
			foreach($inv_list as $do_key => $inv_data){
				// convert the array key to numeric
				if($inv_data['sales_items']){
					ksort($inv_data['sales_items']);
					$inv_list[$do_key]['sales_items'] = array_values($inv_data['sales_items']);
				}
			}
			
			$inv_list = array_values($inv_list);
		}
		
		if($inv_list)	$inv_list = array_values($inv_list);
		
		// mark last update for selected batch_id
		if($update_batch_id){
			$upd = array();
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$upd['tax_amount'] = $total_tax_amt;
			$upd['amount'] = $total_amt;
			$upd['inv_list_data'] = serialize($appCore->array_strval($inv_list));
			$con->sql_query("update arms_acc_batch_no set ".mysql_update_by_field($upd)." where branch_id=$bid and type='ar' and batch_id=".ms($update_batch_id));
		}
		
		$con->sql_commit();
		
		
		return $inv_list;
	}
	
	private function reupdate_ar_batch($bid, $batch_id){
		global $con;
		
		$status_list = array();
		// all grr already received, check and update again arms_acc_batch_no
		$con->sql_query("select status from ar_arms_acc_info where branch_id=$bid and batch_id=".ms($batch_id)."
			group by status
			order by status");
		while($r = $con->sql_fetchassoc()){
			$status_list[$r['status']] = 1;
		}
		$con->sql_freeresult();
		
		$upd = array();
		$upd['status'] = -1;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		if($status_list[3]){
			// Error
			$upd['status'] = 3;
		}elseif($status_list[0]){
			// New
			$upd['status'] = 0;
		}elseif($status_list[1]){
			// all received
			$upd['status'] = 1;
		}
		elseif($status_list[2]){
			// all processed
			$upd['status'] = 2;
		}
		$con->sql_query("update arms_acc_batch_no set ".mysql_update_by_field($upd)." where branch_id=$bid and batch_id=".ms($batch_id)." and type='ar'");
	}
	
	function notify_ar(){
		global $con;
		
		$tmp_do_id = trim($_REQUEST['do_id']);
		$batch_id = trim($_REQUEST['batch_id']);
		if(!$tmp_do_id){
			$this->error_die($this->err_list["require_do_id"]);
		}
		if(!$batch_id){
			$this->error_die($this->err_list["require_batch_id"]);
		}
		
		$status_code = mi($_REQUEST['status_code']);
		$failed_reason = trim($_REQUEST['failed_reason']);
		$acc_doc_no = trim($_REQUEST['acc_doc_no']);
		
		list($bid, $do_id) = explode("_", $tmp_do_id);
		$bid = mi($bid);
		$do_id = mi($do_id);
		if($bid <= 0 || $do_id <= 0){
			$this->error_die($this->err_list["invalid_do_id"]);
		}
		
		/*
			Status Code
			1 - well received but haven't process to accounting software
			2 - succesfully processed 
			3 - failed to process
		*/
		if($status_code == 1){
			
		}elseif($status_code == 2){
			if(!$acc_doc_no){
				$this->error_die($this->err_list["required_doc_no"]);
			}
		}elseif($status_code == 3){
			if(!$failed_reason){
				$this->error_die($this->err_list["required_failed_reason"]);
			}
		}else{
			$this->error_die($this->err_list["invalid_status_code"]);
		}
		
		// Select the data
		$con->sql_query("select * from ar_arms_acc_info where branch_id=$bid and do_id=$do_id");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Data Not Found
		if(!$data){
			$this->error_die($this->err_list["do_not_found"]);
		}
		
		// Batch ID Not Match
		if($data['batch_id'] != $batch_id){
			$this->error_die($this->err_list["do_batch_id_not_match"]);
		}
		
		$con->sql_begin_transaction();
		
		$upd = array();
		$upd['status'] = $status_code;
		$upd['acc_doc_no'] = '';
		$upd['failed_reason'] = '';
		if($status_code == 2){
			$upd['acc_doc_no'] = $acc_doc_no;
		}elseif($status_code == 3){
			$upd['failed_reason'] = $failed_reason;
		}
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update ar_arms_acc_info set ".mysql_update_by_field($upd)." where branch_id=$bid and do_id=$do_id");
		
		// Reupdate the GRR batch 
		$this->reupdate_ar_batch($bid, $batch_id);
		
		$con->sql_commit();
		
		$ret = array();
		$ret['result'] = 1;
		// Return Data
		$this->respond_data($ret);
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

$API_ARMS_ACCOUNTING = new API_ARMS_ACCOUNTING('ARMS Accounting API');
?>