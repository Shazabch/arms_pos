<?php
/*
10/10/2018 11:56 AM Andy
- New ARMS API for Internal App Use.

12/21/2018 10:45 AM Andy
- Enhanced 'get_products' to accept parameters 'min_changes_row_index'.

1/8/2019 10:51 AM Andy
- Change get header method to use getallheaders().
- Change all header to uppercase.
- Add photo in get_product_details()

1/11/2019 11:39 AM Andy
- Fixed get_product_details cannot show last category.
- Added API keep_user_session_active() to keep user session token acitve.

1/11/2019 2:02 PM Andy
- Fixed getallheaders() function is not available in some php version.
- Added API get_category_list() to get category listing.

1/16/2019 11:44 AM Andy
- Added 'tree_str' for get_products().
- Added 'non_member_date_from', 'non_member_date_to', 'member_date_from' and 'member_date_to' for get_product_details().

1/24/2019 4:00 PM Andy
- Enhanced get_product_count to can filtr min_changes_row_index.
- Change get_product_count and get_products to always get all items including active and inactive sku.

2/12/2019 10:59 AM Andy
- Added column 'active' for get_category_list.
- Enhanced get_branches_info to can accept parameter 'all_allowed_branch' to load all users allowed branch.
- Enhanced get_product_details to can accept parameter 'selected_branch_id' to load selected branch data.
- Added API get_debtor_list().
- Added API create_transfer_do() and create_credit_sales_do().

2/26/2019 2:54 PM Andy
- Enhanced get_branches_info() to return branch_group_id, branch_group_code and branch_group_desc.
- Enhanced user_login() to return is_arms_user.
- Enhanced user_login() to login by user barcode.

3/12/2019 5:37 PM Andy
- Fixed ngx will change underscore to dash, program have to manually replace '-' to '_'.

4/18/2019 4:42 PM Andy
- Enhanced user_login() to allow consignment mode to return all device allowed branch.
- Added API add_con_mr_data().

5/29/2019 11:59 AM Andy
- Enhanced to have DO Relationship GUID.

6/26/2019 11:08 AM Andy
- Added Membership Mobile App API.

7/16/2019 6:03 PM Andy
- Enhanced "get_my_member_promotion_list" to show selling_price.

8/13/2019 4:28 PM Andy
- Enhanced "get_cycle_count_list" to have "wip_start_time".

8/16/2019 11:00 AM Andy
- Enhanced "get_my_member_promotion_list" to able to get the promotion list even no login member.
- Enhanced "member_login" to able to login by email.
- Added membership api "validate_my_member_pass", "change_my_member_pass", "reset_my_member_pass", "register_new_member", "existing_member_setup", "get_member_notice_board" and "update_my_member_info".
- Enhanced "get_product_details" to always allow 'selected_branch_id' if app_type is 'member'.
- Enhanced "get_my_member_promotion_list" to return "allowed_member_type".

9/20/2019 4:20 PM Andy
- Added api "get_my_member_coupon_list" and "logout_my_member".
- Modified api "get_my_member_voucher_list" to only get current month expired or used voucher.

9/23/2019 2:43 PM Andy
- Added api "get_machine_branch_info" (For KB Fun).

9/27/2019 2:02 PM Andy
- Fixed api "get_machine_branch_info".

10/1/2019 11:09 AM Andy
- Enhanced api "register_new_member" to check config.membership_mobile_new_register_expiry_duration_year to control new member expiry_date.

10/21/2019 10:47 AM Andy
- Enhanced get_product_details to return promo_photo_url.
- Added Package API "get_my_member_package_list", "get_my_member_package_redeem_history" and "rate_my_member_package_redeem_history".

10/30/2019 3:04 PM Andy
- Added api "get_config".
- Enhanced First time setup feature.
- Enhanced "get_branches_info" to have branch outlet photo, address, phone number, contact email, Longitude, Latitube and Operation Time
- Enhanced "get_branches_info" to only return active branch.
- Enhanced "get_branches_info" to can return all active branch for member app.
- Enhanced error return to return error_code.
- Enhanced "get_my_member_info" to return mobile_registered_time.
- Enhanced "get_my_member_coupon_list" to return member_limit_mobile_day_start, member_limit_mobile_day_end and member_limit_profile_info
- Enhanced "get_my_member_coupon_list" to check member_limit_mobile_day_start, member_limit_mobile_day_end and member_limit_profile_info.

12/11/2019 4:08 PM Andy
- Enhanced to use "sql_query_skip_logbin" when modify table "member_app_session" or "suite_session".

12/17/2019 11:16 AM Andy
- Added function "member_app_version_compare".
- Enhanced "get_my_member_coupon_list" to check if version less than 2.1.0 then will  not return limited coupon.

12/31/2019 1:55 PM Andy
- Change "sql_query_skip_logbin" to use back "sql_query" due to there maybe have report server feature in future.

1/10/2020 11:08 AM Andy
- Fixed spelling.
- Enhanced to check config.server_name when sending mobile app email.

1/3/2020 5:06 PM William
- Enhanced to insert "membership_guid" field for "membership" and "membership_history" table.

1/14/2020 3:39 PM Andy
- disabled isMail().

2/5/2020 11:18 AM Andy
- Added GRR Handler.
- Added User Handler.
- Added Member Handler
- Added Vendor Handler
- Moved api "get_my_member_info" to Member Handler.
- Moved api "get_my_member_coupon_list" to Member Handler.
- Moved api "member_login" to Member Handler.

6/11/2020 1:05 PM Andy
- Enhanced api "get_config" to have "ecom_token".

6/25/2020 11:17 AM William
- Added Debtor Handler.
- Added Sales Order Handler.
- Moved api "get_debtor_list" to Debtor Handler.

7/2/2020 3:30 PM Andy
- Enhanced api "user_login" to return "skip_dongle_checking" if device_type is "barcoder".

7/29/2020 2:38 PM William
- Change function "get_products" first photo to use promotion photo.

8/26/2020 1:44 PM William
- Added Category Handler.
- Moved api "get_category_list" to Category Handler.

9/29/2020 10:30 AM William
- Added Pos Handler.

12/1/2020 4:43 PM William
- Enhanced api "validate_device" return counter_id when app type is pos.
- Enhanced api "is_valid_user" uncheck suite session when APP Type is "pos".
- Enhanced api "get_product_details" to get member point when params "get_point_settings" is 1.
- Enhanced api "get_config" to get 'receipt_running_no' and 'membership_module' config.

2/1/2021 2:35 PM Andy
- Enhanced to support ARMS Internal API in Sync Server.

2/8/2021 1:23 PM Andy
- Added Brand Handler.
- Enhanced api "get_product_details" to have "sku_type" and "trade_discount_code".
- Added SKU Handler.

2/25/2021 6:32 PM William
- Enhanced api "get_products" api to able to filter by description.

2/26/2021 1:39 PM Andy
- Enhanced api "get_product_count", "get_products" and "get_product_details" to can filter by "marketplace_sku_only".

4/20/2021 9:31 AM William
- Enhanced get_config api to get "ewallet_settings".
- Enhanced to return branch code when validate_device.
*/
include("include/common.php");
include("include/price_checker.include.php");
ini_set('memory_limit', '1024M');
set_time_limit(0);

class API_ARMS_INTERNAL{
	var $is_debug = 0;
	var $folder = "attch/api.arms.internal";
	var $log_folder = "attch/api.arms.internal/logs";
	var $hash_prefix = 'Arms2018'; // $hash_prefix + $app_access_code
	
	// Global Info
	var $ENCRYPT_TOKEN = '';
	var $DEVICE_ID = '';
	var $APP_TYPE = '';
	var $APP_VERSION = '';
	var $X_MEM_NRIC = '';
	var $X_MEM_TOKEN = '';
	
	// Device Info
	var $suite_device = array();
	var $suite_device_status = array();
	var $device_guid = '';
	var $device_branch_id = 0;
	var $app_branch_id = 0;
	var $device_ip = '';
	var $all_header = array();
	var $is_member_app = false;
	
	// User Info
	var $user = array();
	
	// Member Info
	var $member_nric = '';
	var $member = array();
	
	var $call_method = '';
	
	// Handler
	var $handler = false;
	var $handler_list = array(
		'grr' => array(
			'file' => 'api.arms.internal.grr.php'
		),
		'user' => array(
			'file' => 'api.arms.internal.user.php'
		),
		'member' => array(
			'file' => 'api.arms.internal.member.php'
		),
		'vendor' => array(
			'file' => 'api.arms.internal.vendor.php'
		),
		'grn' => array(
			'file' => 'api.arms.internal.grn.php'
		),
		'debtor' => array(
			'file' => 'api.arms.internal.debtor.php'
		),
		'sales_order' => array(
			'file' => 'api.arms.internal.sales_order.php'
		),
		'category' => array(
			'file' => 'api.arms.internal.category.php'
		),
		'pos' => array(
			'file' => 'api.arms.internal.pos.php'
		),
		'brand' => array(
			'file' => 'api.arms.internal.brand.php'
		),
		'sku' => array(
			'file' => 'api.arms.internal.sku.php'
		),
	);
	
	// Handler Module
	var $grr_handler = false;
	var $user_handler = false;
	var $member_handler = false;
	var $vendor_handler = false;
	var $grn_handler = false;
	var $debtor_handler = false;
	var $sales_order_handler = false;
	var $category_handler = false;
	var $pos_handler = false;
	var $brand_handler = false;
	var $sku_handler = false;
	
	// Error List
	var $err_list = array(
		"invalid_api_method" => 'Invalid API Method.',
		"invalid_handler_api_method" => 'Invalid Hanlder API Method.',
		'authen_failed' => "Device Authentication Failed.",
		'missing_params' => "Missing Required Parameters.",
		'unknown_error' => "Unknown Error Occured.",
		"invalid_branch_id_list" => "Invalid Branch ID Array.",
		"no_product_found" => "No Product is found.",
		"no_product_to_check" => "No Product to Check.",
		"invalid_login" => "Invalid Username / Password.",
		"no_privilege_login" => "You do not have Login Privilege.",
		"user_authen_failed" => "User Authentication Failed.",
		"user_session_timeout" => "User Session Timeout.",
		"no_category_found" => "No Category is found.",
		"no_debtor_found" => "No Debtor is found.",
		"not_allow_access_other_branch_data" => "You are not allow to access other branch data.",
		"do_no_items" => "No DO Item",
		"do_invalid_date" => "Invalid DO Date",
		"need_user_session" => "This Action require user session access",
		"do_create_failed" => "Failed to Create Delivery Order",
		"do_invalid_do_type" => "Invalid DO Type",
		"no_items" => "No Item is Found.",
		"invalid_date" => "Invalid Date",
		"batch_required" => "Batch Required",
		"monthly_report_not_print" => "Please print the monthly report first.",
		"monthly_report_cannot_edit" => "Monthly Report cannot be edit.",
		"monthly_report_invalid_sku" => "Monthly Report Invalid SKU.",
		"member_login_failed" => "Login Failed",
		"member_data_not_found" => "Data Not Found",
		"member_missing_device_info" => "Mobile Type or Push Notification Token Missing",
		"member_device_type_wrong" => "Mobile Type must be 'android' or 'ios'",
		"member_pass_invalid" => "Password Incorrect",
		"member_reset_failed_alrdy_login" => "Reset Failed: You cannot reset password while you already login.",
		"member_reset_failed_wrong_email" => "Reset Failed: Your email address is wrong.",
		"member_failed_alrdy_login" => "You cannot do this action because you already login as member.",
		"member_email_alrdy_used" => "Email Already Used.",
		"member_alrdy_hv_mobile_access" => "First Time Setup Failed: Your member already have mobile access, Please use Forgot Password",
		"member_default_type_missing" => "Invalid Default Member Type",
		"member_update_nothing" => "Nothing to Update.",
		"member_update_invalid_name" => "'Name' cannot be empty.",
		"member_update_invalid_gender" => "'Gender' value is invalid.",
		"member_update_invalid_dob" => "'DOB' value is invalid.",
		"member_update_invalid_dob_y" => "'DOB' Year is invalid.",
		"member_update_invalid_dob_m" => "'DOB' Month is invalid.",
		"member_update_invalid_dob_d" => "'DOB' Day is invalid.",
		"member_update_invalid_postcode" => "'Postcode' value is invalid.",
		"member_update_invalid_address" => "'Address' value is invalid.",
		"member_update_invalid_city" => "'City' value is invalid.",
		"member_update_invalid_state" => "'State' value is invalid.",
		"member_update_invalid_phone_3" => "'Mobile Phone' value is invalid.",
		"cycle_count_need_doc_no" => "Cycle Count Document No. is Required.",
		"cycle_count_invalid_doc_no" => "Invalid Cycle Count Document No.",
		"cycle_count_no_item" => "Cycle Count No Item to Update",
		"cycle_count_item_invalid_st_time" => "Cycle Count SKU ITEM ID#%s have invalid stock take time",
		"cycle_count_need_wip" => "Cycle Count status must be 'wip'",
		"invalid_data" => "Invalid [%s]",
		"invalid_image" => "The File [%s] is not a valid image file.",
		"invalid_rating" => "Rate must be 1 to 5 only.",
		"redeem_history_not_found" => "Redeem History Not Found.",
		"redeem_history_sa_not_found" => "Redeem History Sales Agent ID [%s] Not Found.",
		"first_time_setup_failed" => "Failed to Setup due to this member doesn't have email and mobile phone number.",
		"first_time_otp_setup_failed_got_email" => "Failed to Setup due to this member already have email.",
		"server_no_sms_config" => "Server doesn't have send sms feature.",
		"invalid_member_mobile" => "Member Mobile Phone Number is different with database.",
		"send_otp_failed" => "Failed to send OTP.",
		"invalid_otp" => "Invalid OTP.",
		"invalid_hanlder" => "Invalid Handler.",
		"unknown_error" => "Unknown Error.",
		"suite_device_invalid" => "Invalid Suite Device GUID.",
		"no_sku_items_found" => "No SKU Items is found.",
		"api_not_compatible_for_sync_server" => "This API cannot work under Sync Server.",
		"marketplace_config_disabled" => "Marketplace Config is not enabled.",
		"marketplace_sku_invalid" => "This SKU is not added into Marketplace.",
		"marketplace_sku_inactive" => "This Marketplace SKU has been deactivated.",
	);
	
	var $is_sync_server = false;
	var $sync_server_compatible_api = array('user_login', 'get_product_details', 'get_config', 'get_branches_info', 'validate_device');
	
	function __construct(){
		if($_SERVER['SERVER_NAME'] == 'maximus')	$this->is_debug = 1;
		if(defined('SYNC_SERVER'))	$this->is_sync_server = true;
		
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
		$this->device_ip = $_SERVER['REMOTE_ADDR'];
		$this->put_log("Calling Method [$a], IP: ".$this->device_ip);
		
		// Got Module Hanlder
		$handler = trim($_REQUEST['handler']);
		if($handler){
			if(!isset($this->handler_list[$handler])){
				$this->error_die($this->err_list["invalid_hanlder"], "invalid_hanlder");
			}
			$this->put_log("Using Handler: ".$handler);
			$this->handler = $handler;
		}
		
		// No Calling Method
		if(!$a){
			$this->error_die($this->err_list["invalid_api_method"], "invalid_api_method");
		}
			
		if($this->handler){
			// Check handler method exists
			$this->check_handler_method($this->handler, $a);
		}else{
			// Check if method not exists
			if(!method_exists($this, $a)){
				$this->error_die($this->err_list["invalid_api_method"], "invalid_api_method");
			}
			
			// Sync Server
			if($this->is_sync_server){
				// Check if the API Function can be used in Sync Server
				if(!in_array($a, $this->sync_server_compatible_api)){
					$this->error_die($this->err_list["api_not_compatible_for_sync_server"], "api_not_compatible_for_sync_server");
				}
			}
		}		
		
		$this->call_method = $a;
		
		// Check Valid User
		if(isset($this->all_header['X_USER_SESSION_TOKEN'])){
			$this->is_valid_user();
		}
		
		//parent::__construct($title);
		$this->_default();
	}
	
	function _default(){		
		if($this->handler){
			// Call method in handler file
			$this->getHandler($this->handler)->{$this->call_method}();
		}else{
			// Call method in this file
			$this->{$this->call_method}();
		}
	}
	
	function check_handler_method($handler, $call_method){
		if(!method_exists($this->getHandler($handler), $call_method)){
			$this->error_die($this->err_list["invalid_handler_api_method"], "invalid_handler_api_method");
		}
		
		if($this->is_sync_server){
			if(!method_exists($this->getHandler($handler), 'is_api_support_sync_server')){
				// API Not Supported in Sync Server
				$this->error_die($this->err_list["api_not_compatible_for_sync_server"], "api_not_compatible_for_sync_server");
			}else{
				if(!$this->getHandler($handler)->is_api_support_sync_server($call_method)){
					// API Not Supported in Sync Server
					$this->error_die($this->err_list["api_not_compatible_for_sync_server"], "api_not_compatible_for_sync_server");
				}
			}
		}
	}
	
	function getHandler($handler){
		if(!$handler)	return false;
		
		switch($handler){
			case 'grr':	// GRR Module
				if(!$this->grr_handler){
					include_once($this->handler_list[$handler]['file']);
					$this->grr_handler = new API_ARMS_INTERNAL_GRR($this);
				}
				return $this->grr_handler;
				break;
			case 'user':	// User Module
				if(!$this->user_handler){
					include_once($this->handler_list[$handler]['file']);
					$this->user_handler = new API_ARMS_INTERNAL_USER($this);
				}
				return $this->user_handler;
				break;
			case 'member':	// Member Module
				if(!$this->member_handler){
					include_once($this->handler_list[$handler]['file']);
					$this->member_handler = new API_ARMS_INTERNAL_MEMBER($this);
				}
				return $this->member_handler;
				break;
			case 'vendor':	// Vendor Module
				if(!$this->vendor_handler){
					include_once($this->handler_list[$handler]['file']);
					$this->vendor_handler = new API_ARMS_INTERNAL_VENDOR($this);
				}
				return $this->vendor_handler;
				break;
			case 'grn':	// GRN Module
				if(!$this->grn_handler){
					include_once($this->handler_list[$handler]['file']);
					$this->grn_handler = new API_ARMS_INTERNAL_GRN($this);
				}
				return $this->grn_handler;
				break;
			case 'debtor':	//Debtor Module
				if(!$this->debtor_handler){
					include_once($this->handler_list[$handler]['file']);
					$this->debtor_handler = new API_ARMS_INTERNAL_DEBTOR($this);
				}
				return $this->debtor_handler;
				break;
			case 'sales_order':  //Sales Order Module
				if(!$this->sales_order_handler){
					include_once($this->handler_list[$handler]['file']);
					$this->sales_order_handler = new API_ARMS_INTERNAL_SALES_ORDER($this);
				}
				return $this->sales_order_handler;
				break;
			case 'category':	//Category Module
				if(!$this->category_handler){
					include_once($this->handler_list[$handler]['file']);
					$this->category_handler = new API_ARMS_INTERNAL_CATEGORY($this);
				}
				return $this->category_handler;
				break;
			case 'pos':	//pos Module
				if(!$this->pos_handler){
					include_once($this->handler_list[$handler]['file']);
					$this->pos_handler = new API_ARMS_INTERNAL_POS($this);
				}
				return $this->pos_handler;
				break;
			case 'brand':	// Brand Module
				if(!$this->brand_handler){
					include_once($this->handler_list[$handler]['file']);
					$this->brand_handler = new API_ARMS_INTERNAL_BRAND($this);
				}
				return $this->brand_handler;
				break;
			case 'sku':	// SKU Module
				if(!$this->sku_handler){
					include_once($this->handler_list[$handler]['file']);
					$this->sku_handler = new API_ARMS_INTERNAL_SKU($this);
				}
				return $this->sku_handler;
				break;
		}
		
		return false;
	}
	
	// function to create all db and folder
	private function prepareDB(){
		global $con;
		
		// Create Program Main Folder
		check_and_create_dir("attch");
		
		// Create Program Main Folder
		check_and_create_dir($this->folder);
		
		// Create Program Logs Folder
		check_and_create_dir($this->log_folder);
	}
	
	function is_valid_device($exit_on_error = true, $is_paring = false){
		global $con, $config;
		
		// Encrypt Token
		$this->ENCRYPT_TOKEN = $ENCRYPT_TOKEN = isset($this->all_header['X_ENCRYPT_TOKEN']) ? trim($this->all_header['X_ENCRYPT_TOKEN']) : '';
		// Device ID
		$this->DEVICE_ID = $DEVICE_ID = isset($this->all_header['X_DEVICE_ID']) ? trim($this->all_header['X_DEVICE_ID']) : '';
		// App Type
		$this->APP_TYPE = $APP_TYPE = isset($this->all_header['X_APP_TYPE']) ? trim($this->all_header['X_APP_TYPE']) : '';
		// App Version
		$this->APP_VERSION = $APP_VERSION = isset($this->all_header['X_APP_VERSION']) ? trim($this->all_header['X_APP_VERSION']) : '';
		// App Branch ID
		$APP_BRANCH_ID = isset($this->all_header['X_APP_BRANCH_ID']) ? mi($this->all_header['X_APP_BRANCH_ID']) : 0;
		
		
		$this->put_log("Checking Device ID: ".$DEVICE_ID);
		$this->put_log("Checking Device App Type: ".$APP_TYPE);
		$this->put_log("Checking Device App Version: ".$APP_VERSION);
		$this->put_log("Checking Device Encrypted Token: ".$ENCRYPT_TOKEN);
		
		$authen_success = false;
		$have_error = false;
		
		if($APP_TYPE == 'member'){	// Member App no need check suite device
			$this->is_member_app = true;
			$this->app_branch_id = 1;
			$this->X_MEM_NRIC = isset($this->all_header['X_MEM_NRIC']) ? trim($this->all_header['X_MEM_NRIC']) : '';
			$this->X_MEM_TOKEN = isset($this->all_header['X_MEM_TOKEN']) ? trim($this->all_header['X_MEM_TOKEN']) : '';
			
			if($this->X_MEM_NRIC){
				$this->put_log("Member NRIC: ".$this->X_MEM_NRIC);
			}
		
			if(!$ENCRYPT_TOKEN || !$config['membership_mobile_settings'] || !$config['membership_mobile_settings']['access_token']){
				$have_error = true;
			}else{
				$my_encrypt_token = md5($this->hash_prefix.$config['membership_mobile_settings']['access_token']);
				if($my_encrypt_token != $ENCRYPT_TOKEN){
					$have_error = true;
				}else{
					if(!$this->X_MEM_NRIC){
						// First login, no check nric
						$authen_success = true;
					}else{
						if(!$this->X_MEM_TOKEN){
							// must have token to check
							$have_error = true;
						}else{
							$con->sql_query("select * from member_app_session where nric=".ms($this->X_MEM_NRIC)." and session_token=".ms($this->X_MEM_TOKEN)." and device_id=".ms($this->DEVICE_ID));
							$member_app_session = $con->sql_fetchassoc();
							$con->sql_freeresult();
							
							if(!$member_app_session){
								// Session not found
								$have_error = true;
							}else{
								// Get Member
								$con->sql_query("select m.* from membership m where m.nric=".ms($this->X_MEM_NRIC));
								$member = $con->sql_fetchassoc();
								$con->sql_freeresult();
								
								if(!$member){
									// Member Not Found
									$have_error = true;
								}else{
									$upd = array();
									$upd['ip'] = $this->device_ip;
									$upd['app_type'] = $this->APP_TYPE;
									$upd['app_version'] = $this->APP_VERSION;
									$upd['last_access'] = 'CURRENT_TIMESTAMP';
									$con->sql_query("update member_app_session set ".mysql_update_by_field($upd)." where nric=".ms($this->X_MEM_NRIC)." and session_token=".ms($this->X_MEM_TOKEN)." and device_id=".ms($this->DEVICE_ID));
									
									$this->member_nric = $member_app_session['nric'];
									$this->member = $member;
									$authen_success = true;
								}
							}
						}
					}
				}
			}
		}else{
			if($ENCRYPT_TOKEN){
				if($is_paring){
					// Paring Device, check unpair device
					$sql = "select * from suite_device_status where encrypt_token=".ms($ENCRYPT_TOKEN)." and paired=0 and paired_device_id=''";
				}else{
					// Normal Check, Check Already Paired Device
					$sql = "select * from suite_device_status where encrypt_token=".ms($ENCRYPT_TOKEN)." and paired=1 and paired_device_id=".ms($DEVICE_ID);
				}
				//print $sql;
				$con->sql_query($sql);
				$suite_device_status = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($suite_device_status){
					// Check Device
					$device_guid = trim($suite_device_status['device_guid']);
					$con->sql_query("select * from suite_device where guid=".ms($device_guid)." and active=1");
					$suite_device = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($suite_device){
						// Check Device Type Matched or Not
						if(!$suite_device['device_type'] || $suite_device['device_type'] == $APP_TYPE){
							$suite_device['allowed_branches'] = unserialize($suite_device['allowed_branches']);
						
							// Get Branch
							$con->sql_query("select id,code from branch where id=".mi($suite_device['branch_id'])." and active=1");
							$app_branch = $branch = $con->sql_fetchassoc();
							$con->sql_freeresult();
							
							if($branch){
								$this->suite_device = $suite_device;
								$this->suite_device_status = $suite_device_status;
								$this->device_guid = $device_guid;
								$this->app_branch_id = $this->device_branch_id = mi($suite_device['branch_id']);
								$this->put_log("Device Branch: ".$branch['code']);
								
								// HQ select other branch
								if($this->device_branch_id  == 1 && $APP_BRANCH_ID>0){
									$this->app_branch_id = $APP_BRANCH_ID;
									
									if($this->app_branch_id != $this->device_branch_id){
										// Check other branch is active or not
										$con->sql_query("select id,code from branch where id=".mi($this->app_branch_id)." and active=1");
										$app_branch = $con->sql_fetchassoc();
										$con->sql_freeresult();
										
										if(!$app_branch)	$have_error = true;
									}
								}
								$this->put_log("App Branch: ".$app_branch['code']);
								
								if(!$have_error){
									// Check whether need to update status
									$need_update_status = true;
									if(!$is_paring){
										if($suite_device_status['app_version'] == $APP_VERSION && $suite_device_status['ip'] == $this->device_ip && $upd['app_type'] == $APP_TYPE){
											if(time() - strtotime($data['last_access']) < 60){
												$need_update_status = false;	// 1 min only update
											}
										}
									}
									
									
									if($need_update_status){
										$upd = array();
										$upd['app_version'] = $APP_VERSION;
										$upd['app_type'] = $APP_TYPE;
										$upd['ip'] = $this->device_ip;
										$upd['last_access'] = 'CURRENT_TIMESTAMP';
										if($is_paring){
											$upd['paired'] = 1;
											$upd['paired_device_id'] = $DEVICE_ID;
										}
										$con->sql_query("update suite_device_status set ".mysql_update_by_field($upd)." where device_guid=".ms($this->device_guid));	
									}
									
									$authen_success = true;
								}
							}
						}				
					}
				}
			}
		}
		
		if(!$authen_success && $exit_on_error){
			$this->error_die($this->err_list["authen_failed"], "authen_failed");
		}
		
		if($authen_success){
			// Validate Success
			if($this->is_member_app){
				$str = "Authentication Success";
			}else{
				$str = "Authentication Success [".$this->suite_device['device_code']."]";
			}
			$this->put_log($str);
			
			if($is_paring && !$this->is_member_app){
				// Paired Success
				$this->put_log("Paired Success [".$this->suite_device['device_code']."] to Device ID [$DEVICE_ID]");
				
				// Log
				log_br(1, 'SUITE_DEVICE', 0, 'Paired Device: '. $this->suite_device['device_code'] . ". GUID: ".$device_guid.", Device ID [$DEVICE_ID]");
			}
		}		
		
		return $authen_success;
	}
		
	function is_valid_user($exit_on_error = true, $update_last_active = true){
		global $con;
		
		$have_error = false;
		$is_timeout = false;
		
		// User Session Token
		$USER_SESSION_TOKEN = isset($this->all_header['X_USER_SESSION_TOKEN']) ? trim($this->all_header['X_USER_SESSION_TOKEN']) : '';
		if(!$USER_SESSION_TOKEN)	$have_error = true;
		
		if(!$have_error){
			$con->sql_query("select *,TIMESTAMPDIFF(MINUTE, last_active, CURRENT_TIMESTAMP()) as min_diff from suite_session where ssid=".ms($USER_SESSION_TOKEN));
			$suite_session = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($suite_session){
				// More than 30 min inactive
				if($suite_session['min_diff']>30 && $this->APP_TYPE!= 'pos'){
					$is_timeout = $have_error = true;
				}
			}else{
				// Session Not Found
				$have_error = true;
			}
			
			if(!$have_error){
				// Get User
				$con->sql_query("select id,u,fullname,vendors from user where id=".mi($suite_session['user_id'])." and active=1 and locked=0 and template=0");
				$user = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($user){
					// Get All User Allowed Branch ID
					$user['allowed_bid_list'] = array();
					$con->sql_query("select branch_id from user_privilege up where up.privilege_code='SUITE_USER_LOGIN' and up.allowed=1 and up.user_id=".mi($user['id']));
					while($r = $con->sql_fetchassoc()){
						$user['allowed_bid_list'][] = $r['branch_id'];
					}
					$con->sql_freeresult();
					
					if($user['vendors']) $user['vendors'] = unserialize($user['vendors']);
					$this->user = $user;
				}else{
					$have_error = true;
				}
			}
		}
		
		if($have_error && $exit_on_error){
			if($is_timeout){
				$this->error_die($this->err_list["user_session_timeout"], "user_session_timeout");
			}else{
				$this->error_die($this->err_list["user_authen_failed"], "user_authen_failed");
			}
		}
		
		if(!$have_error && $update_last_active){
			$con->sql_query("update suite_session set last_active=CURRENT_TIMESTAMP where ssid=".ms($USER_SESSION_TOKEN));
		}
	}
		
	function put_log($log){
		$filename = date("Y-m-d").".txt";
		$str = date("Y-m-d H:i:s")."; ".$log;
		file_put_contents($this->log_folder."/".$filename, $str."\r\n", FILE_APPEND);
	}
	
	function error_die($err_msg, $err_code = '', $extra_data = array()){
		if(!$err_code)	$err_code = 'unknown_error';
		$this->put_log('Error ['.$err_code."]: ".$err_msg);
		
		$ret = array();
		$ret['result'] = 0;
		$ret['error_code'] = $err_code;
		$ret['error_msg'] = $err_msg;
		if($extra_data && is_array($extra_data)){
			$ret = array_merge($ret, $extra_data);
		}
		
		$this->construct_return_header();
		print json_encode($ret);
		exit;
	}
	
	function respond_data($ret){
		$this->put_log("Sending Data.");
		
		$this->construct_return_header();
		print json_encode($ret);
		$this->put_log("Data sent.");
		exit;
	}
	
	/*public function array_utf8_encode($arr){
		foreach($arr as $key => $v){
			if(is_array($v)){
				$arr[$key] = $this->array_utf8_encode($v);
			}else{
				$arr[$key] = utf8_encode($v);
			}
		}
		
		return $arr;
	}*/
	
	private function construct_return_header(){
		//header("Access-Control-Allow-Origin: *");
        //header("Access-Control-Allow-Methods: *");
        //header("Access-Control-Allow-Methods: 'GET, POST, OPTIONS'");
		//header("Access-Control-Allow-Headers: *");
		//header('Access-Control-Allow-Credentials: true');
		//header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header("Content-Type: application/json; charset=utf-8");
	}
	
	//Compare two sets of versions, where major/minor/etc. releases are separated by dots.
	//Returns 0 if both are equal, 1 if A > B, and -1 if B < A.
	function member_app_version_compare($a, $b)
	{
		$a = explode(".", rtrim($a, ".0")); //Split version into pieces and remove trailing .0
		$b = explode(".", rtrim($b, ".0")); //Split version into pieces and remove trailing .0
		foreach ($a as $depth => $aVal)
		{ //Iterate over each piece of A
			if (isset($b[$depth]))
			{ //If B matches A to this depth, compare the values
				if ($aVal > $b[$depth]) return 1; //Return A > B
				else if ($aVal < $b[$depth]) return -1; //Return B > A
				//An equal result is inconclusive at this point
			}
			else
			{ //If B does not match A to this depth, then A comes after B in sort order
				return 1; //so return A > B
			}
		}
		//At this point, we know that to the depth that A and B extend to, they are equivalent.
		//Either the loop ended because A is shorter than B, or both are equal.
		return (count($a) < count($b)) ? -1 : 0;
	}
	
	private function load_uncheckout_do($prms=array()){
		global $con;
		
		if(!$prms['bid'] || !$prms['sid']) return;
		
		$q2 = $con->sql_query($sql="select di.*, do.deliver_branch, u.fraction as uom_fraction
							   from do 
							   left join do_items di on di.do_id = do.id and di.branch_id = do.branch_id
							   left join uom u on u.id = di.uom_id
							   where do.active=1 and do.status in (0,1,2) and do.checkout=0
							   and di.branch_id = ".mi($prms['bid'])." and di.sku_item_id = ".mi($prms['sid']));
		
		$uncheckout_do_qty = 0;
		while($r = $con->sql_fetchassoc($q2)){
			$ctn = $pcs = 0;
			$r['deliver_branch'] = unserialize($r['deliver_branch']);
			// is multi branch
			if(is_array($r['deliver_branch']) && $r['deliver_branch']){
				$ctn_allocation = unserialize($r['ctn_allocation']);
				$pcs_allocation = unserialize($r['pcs_allocation']);
				foreach($r['deliver_branch'] as $tmp_bid){
					$ctn += $ctn_allocation[$tmp_bid];
					$pcs += $pcs_allocation[$tmp_bid];
				}
			}else{
				$ctn = $r['ctn'];
				$pcs = $r['pcs'];
			}
			
			$curr_qty = ($ctn * $r['uom_fraction']) + $pcs;
			$uncheckout_do_qty += $curr_qty;
		}
		$con->sql_freeresult($q2);
		
		$ret = array();
		$ret['uncheckout_do_qty'] = $uncheckout_do_qty;
		
		return $ret;
	}
	
	//////////////////////// PUBLIC API //////////////////////
	
	public function pair_device(){
		global $con;
		
		// Validate Device - with paring
		$this->is_valid_device(true, true);
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		
		// Return Data
		$this->respond_data($ret);
	}
	
	public function validate_device(){
		global $con;
				
		// Validate Device
		$this->is_valid_device();
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		$ret['branch_id'] = $this->device_branch_id;
		$ret['device_guid'] = $this->suite_device['guid'];
		$ret['device_code'] = $this->suite_device['device_code'];
		$ret['device_name'] = $this->suite_device['device_name'];
		if($this->APP_TYPE== 'pos'){
			$con->sql_query("select id
			from counter_settings 
			where branch_id=".mi($this->device_branch_id)." and suite_device_guid=".ms($this->suite_device['guid'])." and active=1");
			$r = $con->sql_fetchrow();
			$con->sql_freeresult();
			$ret['counter_id'] = $r['id'];
			
			$con->sql_query("select code 
			from branch 
			where id=".mi($this->device_branch_id)." and active=1");
			$r2 = $con->sql_fetchrow();
			$con->sql_freeresult();
			$ret['branch_code'] = $r2['code'];
		}
		if($this->device_branch_id == 1){
			$ret['allowed_branch_id'] = array_values($this->suite_device['allowed_branches']);
		}
		
		// Return Data
		$this->respond_data($ret);
	}
	
	public function get_product_count(){
		global $con, $config;
				
		// Validate Device
		$this->is_valid_device();
				
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		$marketplace_sku_only = mi($_REQUEST['marketplace_sku_only']);
		
		// Must turn on Marketplace Config
		if($marketplace_sku_only && !$config['arms_marketplace_settings']){
			$this->error_die($this->err_list["marketplace_config_disabled"], "marketplace_config_disabled");
		}
		
		$filter = array();
		//$filter[] = "si.active=1";
		$xtra_join = '';
		if($min_changes_row_index > 0){
			$xtra_join = "left join tmp_trigger_log tmp on tmp.tablename='sku_items' and tmp.id=si.id";
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		
		if($marketplace_sku_only){
			$xtra_join .= " join marketplace_sku_items mkt_si on mkt_si.sku_item_id=si.id";
			$xtra_cols .= ", mkt_si.active as mkt_active";
		}
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Count all sku
		$this->put_log("Checking Product Count.");	
		$con->sql_query("select count(*) as c 
			from sku_items si
			$xtra_join
			$str_filter");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		$ret['count'] = $tmp['c'];
		
		// Return Data
		$this->respond_data($ret);
	}
	
	public function get_branches_info(){
		global $con;
				
		// Validate Device
		$this->is_valid_device();
		
		$filter = array();
		$filter[] = "b.active=1";
		
		// All User Allowed Branch
		if($_REQUEST['all_allowed_branch'] && $this->user){
			$request_bid_list = $this->user['allowed_bid_list'];
		}else{
			// Receive Branch ID List
			$request_bid_list = $_REQUEST['branch_id_list'];			
		}
		
		if($this->user){
			// user must check allowed branch
			if(!is_array($request_bid_list) || !$request_bid_list){
				$this->error_die($this->err_list["invalid_branch_id_list"], "invalid_branch_id_list");
			}
		}
		
		if($request_bid_list)	$filter[] = "b.id in (".join(', ', array_map('mi', $request_bid_list)).")";
		
		$str_filter = "where ".join(' and ', $filter);
		
		// Get Branch Data
		$ret = array();
		$ret['result'] = 1;
		$ret['branch_data'] = array();
		$sql = "select b.id,b.code,b.description, b.address, b.phone_1, b.phone_2, b.phone_3, b.contact_email, b.outlet_photo_url,b.operation_time, b.longitude, b.latitude, bgi.branch_group_id, bg.code as branch_group_code, bg.description as branch_group_desc
		from branch b
		left join branch_group_items bgi on bgi.branch_id=b.id
		left join branch_group bg on bg.id=bgi.branch_group_id
		$str_filter 
		order by sequence, code";
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
			$ret['branch_data'][] = $r;
		}
		$con->sql_freeresult();
		
		// Return Data
		$this->respond_data($ret);
	}
	
	public function get_products(){
		global $con, $appCore, $config;
				
		// Validate Device
		$this->is_valid_device();
		
		$start_from = mi($_REQUEST['start_from']);
		$limit_count = mi($_REQUEST['limit']);
		$sid = mi($_REQUEST['sku_item_id']);
		$barcode = trim($_REQUEST['barcode']);
		$last_change = trim($_REQUEST['last_change']);
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		$get_by_desc = mi($_REQUEST['get_by_desc']);
		$description = trim($_REQUEST['description']);
		$marketplace_sku_only = mi($_REQUEST['marketplace_sku_only']);
		
		// Must turn on Marketplace Config
		if($marketplace_sku_only && !$config['arms_marketplace_settings']){
			$this->error_die($this->err_list["marketplace_config_disabled"], "marketplace_config_disabled");
		}
		
		$filter = array();
		//$filter[] = "si.active=1";
		
		// testing purpose
		if(!$sid && !$barcode && (!$description || !$get_by_desc)){
			if($this->is_debug){
				//$filter[] = "si.sku_id = 402423";
				//$limit = "limit 3";
			}
		}else{
			if($sid){
				$filter[] = "si.id = ".mi($sid);
			}elseif($barcode){
				$filter[] = "(si.sku_item_code=".ms($barcode)." or si.mcode=".ms($barcode)." or si.artno=".ms($barcode)." or si.link_code=".ms($barcode).")";
			}elseif($get_by_desc){
				$filter[] = "si.description like ".ms("%".$description."%");
			}
		}
		
		if(!$sid && $start_from >= 0 && $limit_count > 0){
			$limit = "limit $start_from, $limit_count";
		}
		
		if($last_change){	// Filter only products which got modified or stock changed after the date
			$filter[] = "(sic.last_update >= ".ms($last_change)." or si.lastupdate >= ".ms($last_change).")";
		}
		
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}

		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// select max level from category
		$con->sql_query("select max(level) as max_lvl from category");
		$cat_max_lvl = mi($con->sql_fetchfield("max_lvl"));
		$con->sql_freeresult();
		
		// select extra column from category_cache
		$xtra_cols = "";
		for($i = 1; $i <= $cat_max_lvl; $i++){
			$xtra_cols .= ", p".$i;
		}
		
		$xtra_join = '';
		if($marketplace_sku_only){
			$xtra_join .= "join marketplace_sku_items mkt_si on mkt_si.sku_item_id=si.id";
			$xtra_cols .= ", mkt_si.active as mkt_active";
		}
		
		$sql = "select si.id,si.sku_id,si.sku_apply_items_id,si.sku_item_code,si.mcode,si.artno,si.link_code,si.description as product_description,si.receipt_description, si.internal_description,si.weight,si.size,si.color, si.active, 	round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(p.price,si.selling_price)/(100+output_gst.rate)*100,ifnull(p.price,si.selling_price)),2)
			as selling_price_before_tax,
			round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(p.price,si.selling_price),ifnull(p.price,si.selling_price)*(100+output_gst.rate)/100),2)
			as selling_price_inclusive_tax,
			round(ifnull(p.price, si.selling_price), 2) as selling_price, si.lastupdate as last_modified, c.tree_str, 
		
			sic.qty as stock_qty, sic.last_update as last_stock_changed, c.id as category_id, c.description as category_description, ifnull(tmp.row_index,0) as changes_row_index

			$xtra_cols
			from sku_items si
			left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = $this->app_branch_id
			left join sku_items_price p on p.sku_item_id = si.id and p.branch_id = $this->app_branch_id
			left join sku on sku.id=si.sku_id
			left join category c on c.id=sku.category_id
			left join category_cache cc on cc.category_id=sku.category_id
			left join gst input_gst on input_gst.id=if(if(si.input_tax<0,sku.mst_input_tax,si.input_tax)<0,cc.input_tax,if(si.input_tax<0,sku.mst_input_tax,si.input_tax))
			left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
			left join tmp_trigger_log tmp on tmp.tablename='sku_items' and tmp.id=si.id
			$xtra_join
			$str_filter
			order by changes_row_index
			$limit";
			
		//print $sql;exit;
		$q1 = $con->sql_query($sql);
		
		while($si = $con->sql_fetchassoc($q1)){
			$r = $si;
			//$r['product_description'] = utf8_encode($r['product_description']);
			//print_r($r);exit;
			//$r['receipt_description'] = utf8_encode($r['receipt_description']);
			//$r['category_description'] = utf8_encode($r['category_description']);
			
			$prms = $do_info = array();
			$prms['sid'] = $r['id'];
			$prms['bid'] = $this->app_branch_id;
			$do_info = $this->load_uncheckout_do($prms);
			$r['uncheckout_do_qty'] = mf($do_info['uncheckout_do_qty']);
			unset($prms, $do_info);
			
			// color
			//list($dummy, $r['color']) = explode(";", $r['color']);
			
			$promo_photo_list = $appCore->skuManager->getSKUItemPromoPhotos($si['id']);
			$apply_photo_list = get_sku_apply_item_photos($si['sku_apply_items_id']);
			$photo_list = get_sku_item_photos($si['id'],$si);
			$r['photo_list'] = array();
			if(!$promo_photo_list)	$promo_photo_list = array();
			if(!$apply_photo_list)	$apply_photo_list = array();
			if(!$photo_list)	$photo_list = array();
			$all_photo_list = array_merge($promo_photo_list, $apply_photo_list, $photo_list);
			if($all_photo_list){
				foreach($all_photo_list as $photo_path){
					$photo_details = array();
					$photo_details['abs_path'] = $photo_path;
					if(file_exists($photo_path)){
						$photo_details['last_update'] = date("Y-m-d H:i:s", filemtime($photo_path));
						//$photo_details['name'] = basename($photo_path);			
						$r['photo_list'][] = $photo_details;
					}
				}
			}
			
			// loop and construct category tree list
			for($i = 1; $i <= $cat_max_lvl; $i++){
				if($r['p'.$i]){
					$cat_id = $r['p'.$i];
					if(!$category_list[$cat_id]){ // category not in the list, need to select
						$q2 = $con->sql_query("select id, description from category where id = ".mi($cat_id));
						$category_list[$cat_id] = $con->sql_fetchassoc($q2);
						$con->sql_freeresult($q2);
					}
					
					if(!$category_list[$cat_id]) continue; // if still couldn't get, something wrong on the category table
					
					$r['category_tree'][$i-1] = $category_list[$cat_id];
					
				}

				unset($r['p'.$i]); // always need to unset so that it won't become part of the serialisation
			}
			
			$si_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
				
		
		if ($si_list){
			$ret = array();
			$ret['result'] = 1;
			$ret['products_data'] = $si_list;
			
			//print_r($ret);exit;
			// Return Data				
			$this->respond_data($ret);
		}	
		else{
			$this->error_die($this->err_list["no_product_found"], "no_product_found");
		}
		
	}
	
	public function get_product_details(){
		global $con, $config, $appCore;
				
		// Validate Device
		$this->is_valid_device();
		
		$sid = mi($_REQUEST['sku_item_id']);
		$barcode = trim($_REQUEST['barcode']);
		$selected_branch_id = mi($_REQUEST['selected_branch_id']);
		$get_point_settings = mi($_REQUEST['get_point_settings']);
		$marketplace_sku_only = mi($_REQUEST['marketplace_sku_only']);
		
		// Must turn on Marketplace Config
		if($marketplace_sku_only && !$config['arms_marketplace_settings']){
			$this->error_die($this->err_list["marketplace_config_disabled"], "marketplace_config_disabled");
		}
		
		if($selected_branch_id>0 && $this->APP_TYPE != 'member'){
			if(!$this->user || !in_array($selected_branch_id, $this->user['allowed_bid_list'])){
				$this->error_die($this->err_list["not_allow_access_other_branch_data"], "not_allow_access_other_branch_data");
			}
		}
		
		if($sid <=0 && !$barcode){
			$this->error_die($this->err_list["no_product_to_check"], "no_product_to_check");
		}
		
		// Search by using sku item id
		if($sid > 0){
			$con->sql_query("select sku_item_code from sku_items where id=$sid");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$barcode = $tmp['sku_item_code'];
			
			if(!$barcode){
				$this->error_die($this->err_list["no_product_found"], "no_product_found");
			}
		}
		
		$use_bid = $this->app_branch_id;
		if($selected_branch_id>0)	$use_bid = $selected_branch_id;
		
		$params = array();
		$params["code"] =  $barcode;
		$params["branch_id"] = $use_bid;
		if(!$this->is_sync_server)	$params["get_stock"] = 1;
		if($sid > 0)	$params['sku_item_id'] = $sid;
		$params["get_point_settings"] = $get_point_settings;
		
		//print_r($params);exit;
		$sku = check_price($params);
		
		// Item Not Found
		if(isset($sku['error'])){
			$this->error_die($this->err_list["no_product_found"], "no_product_found");
		}
		
		// Get Marketplace SKU Only
		if($marketplace_sku_only){
			$con->sql_query("select * from marketplace_sku_items where sku_item_id=".mi($sku['id']));
			$mkt_si = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			// Not Marketplace SKU
			if(!$mkt_si){
				$this->error_die($this->err_list["marketplace_sku_invalid"], "marketplace_sku_invalid");
			}
			
			// Inactive Marketplace SKU
			if(!$mkt_si['active']){
				$this->error_die($this->err_list["marketplace_sku_inactive"], "marketplace_sku_inactive");
			}
		}
		
		$ret = array();
		$ret['result'] = 1;
		
		$required_fields = array('id', 'sku_id', 'sku_item_code', 'mcode', 'link_code', 'artno', 'description', 'receipt_description', 'added', 'lastupdate', 'location', 'weight', 'weight_kg', 'size', 'color', 'flavor', 'misc', 'internal_description', 'not_allow_disc', 'category_id', 'tree_str', 'default_price', 'member_price', 'member_discount', 'non_member_price', 'non_member_discount', 'photo', 'non_member_date_from', 'non_member_date_to', 'member_date_from', 'member_date_to', 'is_bom', 'bom_type', 'member_type_cat_discount', 'member_type_price', 'member_point', 'member_type_point', 'matched_condition', 'sku_type', 'trade_discount_code');
		$alt_field_key = array('lastupdate' => 'last_modified');
		foreach($required_fields as $field){
			$read_field = isset($alt_field_key[$field]) ? $alt_field_key[$field] : $field;
			$ret[$read_field] = '';
			if(isset($sku[$field])){
				$ret[$read_field] = $sku[$field];
			}
		} 
  
		if(!$this->is_sync_server){
			// Get Last Stock Changed
			$con->sql_query("select qty, last_update from sku_items_cost where branch_id=$use_bid and sku_item_id=".mi($sku['id']));
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$ret['stock_qty'] = $tmp['qty'];
			$ret['last_stock_changed'] = $tmp['last_update'];
		}
		
		// Get MPrice
		$ret['mprice_data'] = array();
		if($config['sku_multiple_selling_price']){
			$con->sql_query("select * from sku_items_mprice where branch_id=$use_bid and sku_item_id=".mi($sku['id'])." order by type");
			while($r = $con->sql_fetchassoc()){
				if(in_array($r['type'], $config['sku_multiple_selling_price'])){
					$ret['mprice_data'][] = array('type'=>$r['type'], 'price'=>$r['price']);
				}
			}
			$con->sql_freeresult();
		}
		
		// Category Tree
		if($ret['tree_str']){
			$tree_str = str_replace(")(", ",", $ret['tree_str']);
			$tree_str = preg_replace("/[()]/", "", $tree_str);
			$tree_str = explode(',', $tree_str);
			$ret['category_tree'] = array();
			
			if($tree_str){
				foreach($tree_str as $tmp_cat_id){
					if($tmp_cat_id <= 0)	continue;
					
					$q2 = $con->sql_query("select id, description from category where id = ".mi($tmp_cat_id));
					$tmp_cat = $con->sql_fetchassoc($q2);
					$con->sql_freeresult($q2);
					$ret['category_tree'][] = $tmp_cat;
				}
			}
			
			if($ret['category_id']){
				$q2 = $con->sql_query("select id, description from category where id = ".mi($ret['category_id']));
				$tmp_cat = $con->sql_fetchassoc($q2);
				$con->sql_freeresult($q2);
				$ret['category_tree'][] = $tmp_cat;
			}
		}
		
		// POS Photo
		if($this->is_sync_server){
			if($sku['got_pos_photo']){
				$group_num = ceil($sku['id']/10000);
				$ret['promo_photo_url'] = "sku_photos/promo_photo/".$group_num."/".$sku['id']."/1.jpg";
			}
		}else{
			$promo_photo_list = $appCore->skuManager->getSKUItemPromoPhotos($sku['id']);
			if($promo_photo_list){
				$ret['promo_photo_url'] = $promo_photo_list[0];
			}
		}
		

		// Return Data				
		$this->respond_data($ret);
	}
	
	public function user_login(){
		global $con, $config, $ssid;
				
		// Validate Device
		$this->is_valid_device();
		
		$u = trim($_REQUEST['u']);
		$p = trim($_REQUEST['p']);
		$barcode = trim($_REQUEST['barcode']);
		
		$filter = array();
		$filter[] = "user.active=1 and user.locked=0 and user.template=0";
		
		if($barcode){
			// Login by Barcode
			$filter[] = "md5(user.barcode)=".ms($barcode);
		}else{
			// Login by username and password
			if(!$u || !$p){
				$this->error_die($this->err_list["invalid_login"], "invalid_login");
			}
			$filter[] = "user.l=".ms($u)." and user.p=".ms($p);
		}
		
		$str_filter = "where ".join(' and ', $filter);
		
		// Check User
		$con->sql_query("select user.*
			from user
			$str_filter
			order by id limit 1");
		$user = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$user){	// User Not Found
			$this->error_die($this->err_list["invalid_login"], "invalid_login");
		}
		
		// Check Privilege - Both User and Device Must allow login to this branch
		$filter = array();
		$allowed_bid_list = array();
		$filter[] = "up.user_id=".mi($user['id']);
		$filter[] = "up.privilege_code='SUITE_USER_LOGIN' and up.allowed=1";
		if($this->device_branch_id != 1)	$filter[] = "up.branch_id=".mi($this->device_branch_id);
		else{
			if($this->suite_device['allowed_branches']){
				$filter[] = "up.branch_id in (".join(',', $this->suite_device['allowed_branches']).")";
			}else{
				$this->error_die($this->err_list["no_privilege_login"], "no_privilege_login");
			}
		}
		$str_filter = join(' and ', $filter);		
		
		$con->sql_query("select up.branch_id 
			from user_privilege up 
			join branch b on b.id=up.branch_id and b.active=1
			where $str_filter");
		while($r = $con->sql_fetchassoc()){
			$allowed_bid_list[] = mi($r['branch_id']);
		}
		$con->sql_freeresult();
		
		if(!$allowed_bid_list){
			$this->error_die($this->err_list["no_privilege_login"], "no_privilege_login");
		}
		
		if($config['consignment_modules']){
			// consignment can login the other branch without user privilege
			foreach($this->suite_device['allowed_branches'] as $tmp_bid){
				$tmp_bid = mi($tmp_bid);
				if(!in_array($tmp_bid, $allowed_bid_list))	$allowed_bid_list[] = $tmp_bid;
			}
		}
		
		// Check if barcoder, need to skip dongle checking or not, for barcoder v2.0 above
		$skip_dongle_checking = 0;
		if($this->APP_TYPE == 'barcoder'){
			$skip_dongle_checking = mi($this->suite_device['skip_dongle_checking']);
		}
		
		// create suite_session
		$upd = array();
		$upd['ssid'] = $ssid;
		$upd['user_id'] = $user['id'];
		$upd['last_active'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("replace into suite_session ".mysql_insert_by_field($upd));
		
		// Construct Data to return
		$ret = array();
		$ret['result'] = 1;
		$ret['user_id'] = $user['id'];
		$ret['username'] = $user['u'];
		$ret['fullname'] = $user['fullname'];
		$ret['user_session_token'] = $ssid;
		$ret['allowed_branches'] = $allowed_bid_list;
		if($user['is_arms_user'])	$ret['is_arms_user'] = 1;
		if($skip_dongle_checking)	$ret['skip_dongle_checking'] = 1;
		
		// Return Data				
		$this->respond_data($ret);
	}
	
	function keep_user_session_active(){
		global $con, $config;
				
		// Validate Device
		$this->is_valid_device();
		
		if(!$this->user){
			$this->error_die($this->err_list["user_authen_failed"], "user_authen_failed");
		}
		
		// Construct Data to return
		$ret = array();
		$ret['result'] = 1;
		
		// Return Data				
		$this->respond_data($ret);
	}
	
	function get_category_list(){
		global $con, $config;
		
		$this->getHandler('category')->get_category_list();
	}
	
	function get_debtor_list(){
		global $con, $config;
		
		$this->getHandler('debtor')->get_debtor_list();
	}
	
	function create_transfer_do(){
		$this->create_do('transfer');
	}
	
	function create_credit_sales_do(){
		$this->create_do('credit_sales');
	}
	
	private function create_do($do_type){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		// Must already login by user
		if(!$this->user){
			$this->error_die($this->err_list["need_user_session"], "need_user_session");
		}
		
		// Check DO Items
		$do_items = json_decode($_REQUEST['items'], true);
		if(!$do_items){
			$this->error_die($this->err_list["do_no_items"], "do_no_items");
		}
				
		$tmp_generate_do = array();
		$tmp_generate_do['guid'] = $appCore->newGUID();
		$tmp_generate_do['branch_id'] = $this->app_branch_id;
		$tmp_generate_do['do_type'] = $do_type;
		if($do_type == 'transfer'){
			$tmp_generate_do['do_branch_id'] = mi($_REQUEST['do_branch_id']);
		}elseif($do_type == 'credit_sales'){
			$tmp_generate_do['debtor_id'] = mi($_REQUEST['debtor_id']);
		}else{
			$this->error_die($this->err_list["do_invalid_do_type"], "do_invalid_do_type");
		}
		
		$tmp_generate_do['user_id'] = $this->user['id'];
		$tmp_generate_do['added'] = 'CURRENT_TIMESTAMP';
		$tmp_generate_do['remark'] = trim($_REQUEST['remark']);
		
		$relationship_guid = trim($_REQUEST['relationship_guid']);
		if($relationship_guid){
			$tmp_generate_do['relationship_guid'] = $relationship_guid;
		}
		
		// DO Date
		$tmp_generate_do['do_date'] = date("Y-m-d", strtotime(trim($_REQUEST['do_date'])));
		if(date("Y", strtotime($tmp_generate_do['do_date']))<2000){
			$this->error_die($this->err_list["do_invalid_date"], "do_invalid_date");
		}
		
		$con->sql_query("insert into tmp_generate_do ".mysql_insert_by_field($tmp_generate_do));
		
		$uomForEACH = $appCore->uomManager->getUOMForEach();
		
		// Loop SKU
		$sequence = 0;
		foreach($do_items as $r){
			$sequence++;
			
			$tmp_generate_do_items = array();
			$tmp_generate_do_items['guid'] = $appCore->newGUID();
			$tmp_generate_do_items['gen_do_guid'] = $tmp_generate_do['guid'];
			$tmp_generate_do_items['sku_item_id'] = $r['sku_item_id'];
			$tmp_generate_do_items['uom_id'] = $uomForEACH['id'];
			$tmp_generate_do_items['ctn'] = 0;
			$tmp_generate_do_items['pcs'] = $r['qty'];
			$tmp_generate_do_items['added'] = 'CURRENT_TIMESTAMP';
			$tmp_generate_do_items['sequence'] = $sequence;
			$con->sql_query("insert into tmp_generate_do_items ".mysql_insert_by_field($tmp_generate_do_items));
		}
		
		// Create DO
		$do_id = mi($appCore->doManager->createDOFromTMP($tmp_generate_do['guid']));
		
		// Construct Data to return		
		if($do_id>0){
			$ret = array();
			$ret['result'] = 1;
			$ret['do_id'] = $do_id;
			
			// Return Data
			$this->respond_data($ret);
		}else{
			$this->error_die($this->err_list["do_create_failed"], "do_create_failed");
		}		
	}
	
	function add_con_mr_data(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		// Must already login by user
		if(!$this->user){
			$this->error_die($this->err_list["need_user_session"], "need_user_session");
		}
		
		$date = trim($_REQUEST['date']);
		$batch_id = trim($_REQUEST['batch_id']);
		
		// Check Items
		$items_list = json_decode($_REQUEST['items'], true);
		if(!$items_list){
			$this->error_die($this->err_list["no_items"], "no_items");
		}
		
		// Date
		$date = date("Y-m-d", strtotime($date));
		if(date("Y", strtotime($date))<2000){
			$this->error_die($this->err_list["invalid_date"], "invalid_date");
		}
		$y = mi(date("Y", strtotime($date)));
		$m = mi(date("m", strtotime($date)));
		
		// Batch
		if(!$batch_id){
			$this->error_die($this->err_list["batch_required"], "batch_required");
		}
		//print "app_branch_id = ".$this->app_branch_id;exit;
		
		$bid = mi($this->app_branch_id);
		
		// Monthly Report Info
		$con->sql_query("select * from monthly_report_list where branch_id=$bid and year=$y and month=$m");
		$monthly_report_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if(!$monthly_report_info){
			$this->error_die($this->err_list["monthly_report_not_print"], "monthly_report_not_print");
		}
		if($monthly_report_info['status'] != 0){
			$this->error_die($this->err_list["monthly_report_cannot_edit"], "monthly_report_cannot_edit");
		}

		// Check items
		foreach($items_list as $r){
			$sid = mi($r['sku_item_id']);			
			if($sid<=0){
				$this->error_die($this->err_list["monthly_report_invalid_sku"], "monthly_report_invalid_sku");
			}
		}
		
		// Get old item list
		/*$old_items_list = array();
		$q1 = $con->sql_query("select * from suite_consignment_report_data where branch_id=".mi($this->app_branch_id)." and device_guid=".ms($this->device_guid)." and batch_id=".ms($batch_id));
		while($r = $con->sql_fetchassoc($q1)){
			$old_items_list[$r['sku_item_id']] = $r;
		}
		$con->sql_freeresult($q1);*/
		
		$last_date_of_month = $y.'-'.$m.'-'.days_of_month($m, $y);
		
		$con->sql_begin_transaction();
		
		// Delete old data
		$con->sql_query("delete from suite_consignment_report_data where branch_id=$bid and device_guid=".ms($this->device_guid)." and batch_id=".ms($batch_id));
		
		// insert new data
		$upd = array();
		$upd['branch_id'] = $bid;
		$upd['device_guid'] = $this->device_guid;
		$upd['batch_id'] = $batch_id;
		$upd['date'] = $date;
		$upd['y'] = $y;
		$upd['m'] = $m;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		// Loop items
		$sid_list = array();
		foreach($items_list as $r){
			$sid = mi($r['sku_item_id']);
			$upd['sku_item_id'] = $sid;
			$upd['sales_qty'] = mi($r['sales_qty']);
			$con->sql_query("insert into suite_consignment_report_data ".mysql_insert_by_field($upd)." on duplicate key update
			sales_qty=sales_qty+".$upd['sales_qty']);
			
			$sid_list[$sid] = $sid;
		}
		
		// Select total by sku
		$q1 = $con->sql_query("select scrd.sku_item_id as sid, sum(sales_qty) as sales_qty, cr_sku.page_num, sku.default_trade_discount_code, si.artno, si.selling_price
			from suite_consignment_report_data scrd
			left join consignment_report_sku cr_sku on cr_sku.branch_id=scrd.branch_id and cr_sku.year=scrd.y and cr_sku.month=scrd.m and cr_sku.sku_item_id=scrd.sku_item_id
			left join sku_items si on si.id=scrd.sku_item_id
			left join sku on sku.id=si.sku_id
			where scrd.branch_id=$bid and scrd.y=$y and scrd.m=$m and scrd.sku_item_id in (".join(',', $sid_list).")
			group by sid");
		$item_list_info = array();
		$new_page = array();
		while($r = $con->sql_fetchassoc($q1)){
			$sid = $r['sid'];
			
			if(!$r['page_num']){
				$q_pt = $con->sql_query("select price as selling_price,trade_discount_code 
					from sku_items_price_history 
					where sku_item_id=".mi($sid)." and branch_id=$bid and added<=".ms($last_date_of_month.' 23:59:59')." order by added desc limit 1");
				$temp = $con->sql_fetchassoc($q_pt);
				$con->sql_freeresult($q_pt);
				
				if($temp['selling_price'])	$r['selling_price'] = $temp['selling_price'];
				if($temp['trade_discount_code'])   $r['discount_code'] = $temp['trade_discount_code'];
				else    $r['discount_code'] = $r['default_trade_discount_code'];
				
				$new_page[$r['discount_code']][] = $r;
			}
			$item_list_info[$sid] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($new_page){	// Need to create new page
			//print_r($new_page);exit;
			// get max page number
			$con->sql_query("select max(page) as max_page from consignment_report_page_info where branch_id=$bid and year=$y and month=$m");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$page_num = mi($tmp['max_page']);
			
			foreach($new_page as $discount_code=>$items){
				$exist_page_num = 0;
				$exist_row_num = 0;
				
				// check for existing page
				$con->sql_query("select * from consignment_report_page_info 
					where branch_id=$bid and year=$y and month=$m and discount_code=".ms('extra,'.$discount_code));
				$exist_page = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($exist_page){    // page already exists
					$exist_page_num = mi($exist_page['page']);
					
					$con->sql_query("select max(row_num) 
						from consignment_report_sku 
						where branch_id=$bid and year=$y and month=$m and page_num=$exist_page_num");
					$exist_row_num = mi($con->sql_fetchfield(0));
					$con->sql_freeresult();
				}else{  // it is new page
					// add new page
					$page_num++;
					$row_num = 0;

					$upd = array();
					$upd['branch_id'] = $bid;
					$upd['year'] = $y;
					$upd['month'] = $m;
					$upd['page'] = $page_num;
					$upd['discount_code'] = 'extra,'.$discount_code;

					$con->sql_query("insert into consignment_report_page_info".mysql_insert_by_field($upd));
				}
				
				$use_page = ($exist_page_num>0)?$exist_page_num:$page_num;
				$use_row = ($exist_row_num>0)?$exist_row_num:$row_num;
				
				foreach($items as $r){
					$use_row++;
					
					$upd2 = array();
					$upd2['page_num'] = $use_page;
					$upd2['row_num'] = $use_row;
					$upd2['sku_item_id'] = $r['sid'];
					$upd2['art_no'] = $r['artno'];

					if($config['enable_gst'] && $y && $m){
						$gst_date = date("Y-m-d", strtotime("-1 day", strtotime("+1 months", strtotime($y."-".$m."-01"))));
						$is_under_gst = check_gst_status(array('date'=>$gst_date, 'branch_id'=>1));
						if($is_under_gst){
							$is_inclusive_tax = get_sku_gst("inclusive_tax", $r['sid']);
							$gst_info = get_sku_gst("output_tax", $r['sid']);

							$prms['selling_price'] = $r['selling_price'];
							$prms['inclusive_tax'] = $is_inclusive_tax;
							$prms['gst_rate'] = $gst_info['rate'];
							$gst_sp_info = calculate_gst_sp($prms);
							
							if($is_inclusive_tax == "no") $r['selling_price'] = $gst_sp_info['gst_selling_price'];
						}
					}
					
					$upd2['price'] = round($r['selling_price'], 2);
					$upd2['year'] = $y;
					$upd2['month'] = $m;
					$upd2['branch_id'] = $bid;
					
					$con->sql_query("insert into consignment_report_sku ".mysql_insert_by_field($upd2));
				}
			}
		}
		
		// Update Quantity
		//print_r($item_list_info);exit;
		foreach($item_list_info as $sid => $r){
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['sku_item_id'] = $r['sid'];
			$upd['year'] = $y;
			$upd['month'] = $m;
			$upd['qty'] = $r['sales_qty'];
			
			$con->sql_query("insert into consignment_report ".mysql_insert_by_field($upd)." on duplicate key update
			qty=".mi($upd['qty']));
		}
		$con->sql_query("update monthly_report_list set last_update=CURRENT_TIMESTAMP where branch_id=$bid and year=$y and month=$m");
		
		$con->sql_commit();
		
		$ret = array();
		$ret['result'] = 1;
		$ret['items'] = array();
		// Return current stock and monthly report qty
		$con->sql_query("select si.id as sku_item_id, sic.qty as stock_bal, cr.qty as sales_qty
			from sku_items si
			left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
			left join consignment_report cr on cr.branch_id=$bid and cr.year=$y and cr.month=$m and cr.sku_item_id=si.id
			where si.id in (".join(',', $sid_list).")");
		while($r = $con->sql_fetchassoc()){
			$ret['items'][] = $r;
		}
		$con->sql_freeresult();
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function member_login(){
		global $con, $config, $appCore;
		
		// Call using member handler
		$this->getHandler('member')->member_login();
	}
	
	function set_member_device_info(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		if(!$this->member){
			$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		$mobile_type = strtolower(trim($_REQUEST['mobile_type']));
		$push_notification_token = trim($_REQUEST['push_notification_token']);
		
		if(!$mobile_type || !$push_notification_token){
			$this->error_die($this->err_list["member_missing_device_info"], "member_missing_device_info");
		}
		if($mobile_type != 'android' && $mobile_type != 'ios'){
			$this->error_die($this->err_list["member_device_type_wrong"], "member_device_type_wrong");
		}
		
		$upd = array();
		$upd['mobile_type'] = $mobile_type;
		$upd['push_notification_token'] = $push_notification_token;
		$upd['last_access'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update member_app_session set ".mysql_update_by_field($upd)." where nric=".ms($this->X_MEM_NRIC)." and session_token=".ms($this->X_MEM_TOKEN)." and device_id=".ms($this->DEVICE_ID));
		
		$ret = array();
		$ret['result'] = 1;
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function get_my_member_info(){
		global $con, $config, $appCore;
		
		// Call using member handler
		$this->getHandler('member')->get_my_member_info();
	}
	
	function get_my_member_point_history(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		if(!$this->member){
			$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		$last_recalculate_time = trim($_REQUEST['last_recalculate_time']);
		
		$nric = trim($this->member['nric']);
		
		$ret = array();
		$ret['result'] = 1;
		$ret['last_recalculate_time'] = $this->member['last_recalculate_time'];
		if($last_recalculate_time == $this->member['last_recalculate_time']){
			$ret['no_change'] = 1;
		}else{
			$ret['history_data'] = array();
			
			$q1 = $con->sql_query("select mp.*, b.description as branch_desc
				from membership_points mp
				left join branch b on b.id=mp.branch_id
				where mp.nric=".ms($nric)."
				order by mp.date desc
				limit 50");
			while($r = $con->sql_fetchassoc($q1)){
				//$point_history_id = $r['type']."-".$r['branch_id']."-".strtotime($r['date'])."-".$r['card_no'];
				
				$data = array();
				//$data['point_history_id'] = $point_history_id;
				$data['nric'] = $r['nric'];
				$data['card_no'] = $r['card_no'];
				$data['date'] = $r['date'];
				$data['branch_id'] = $r['branch_id'];
				$data['branch_desc'] = $r['branch_desc'];
				$data['type'] = $r['type'];
				$data['points'] = $r['points'];
				$data['remark'] = trim($r['remark']);
				$data['point_source'] = trim($r['point_source']);
				
				$ret['history_data'][] = $data;
			}
			$con->sql_freeresult($q1);
		}
		// Return Data
		$this->respond_data($ret);
	}
	
	function get_my_member_promotion_list(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		//if(!$this->member){
		//	$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		//}
		
		$ret = array();
		$ret['result'] = 1;
		$ret['promo_list'] = array();
		
		// Got member
		if($this->member){
			$nric = trim($this->member['nric']);
			
			// Get Card No List
			$card_no_list = $appCore->memberManager->getMemberCardNoList($nric);
			$str_card_no = join(',', array_map('ms', $card_no_list));
		}
		
		
		$d = date("Y-m-d");
		$item_is_special_for_you = array();
		$item_is_special_for_you['sku'] = array();
		$item_is_special_for_you['cat'] = array();
		
		// Get Promotion
		$sql = "select p.*
			from promotion p
			where p.active=1 and p.status=1 and p.approved=1 and p.show_in_member_mobile=1 and ".ms($d)." <= p.date_to and p.promo_type='discount'
			order by p.date_to desc";
		//die($sql);
		$q1 = $con->sql_query($sql);
		while($promo = $con->sql_fetchassoc($q1)){
			$branch_id = mi($promo['branch_id']);
			$promo_id = mi($promo['id']);
			$promo['special_for_you_info'] = unserialize($promo['special_for_you_info']);
			
			$promo_data = array();
			$promo_data['promo_key'] = $branch_id.'_'.$promo_id;
			$promo_data['title'] = $promo['title'];
			$promo_data['date_from'] = $promo['date_from'];
			$promo_data['date_to'] = $promo['date_to'];
			$promo_data['time_from'] = $promo['time_from'];
			$promo_data['time_to'] = $promo['time_to'];
			
			// Get Promotion Banner
			$folder = "attch/promo_banner/$branch_id/$promo_id";
			if(file_exists($folder)){
				$file_list = glob("$folder/banner_vertical_1.*");
				if($file_list[0]){
					$promo_data['banner_vertical_1'] = trim($file_list[0]);
				}			
			}
		
			$promo_data['promo_branch_id'] = unserialize($promo['promo_branch_id']);
			$promo_data['item_list'] = array();
			
			// Get Promotion Items
			$q2 = $con->sql_query("select pi.*, si.description as sku_description, si.sku_apply_items_id, if(sip.price is null, si.selling_price, sip.price) as selling_price
				from promotion_items pi
				join sku_items si on si.id=pi.sku_item_id
				left join sku_items_price sip on si.id = sip.sku_item_id and sip.branch_id = pi.branch_id
				where pi.branch_id=$branch_id and pi.promo_id=$promo_id and pi.show_in_member_mobile=1
				order by pi.id");
			while($pi = $con->sql_fetchassoc($q2)){
				$pi['allowed_member_type'] = unserialize($pi['allowed_member_type']);
				
				$pi_data = array();
				$sid = mi($pi['sku_item_id']);
				$pi_data['sku_item_id'] = $sid;
				$pi_data['sku_description'] = $pi['sku_description'];
				$pi_data['selling_price'] = $pi['selling_price'];
				
				// Get Promo Photo
				$promo_photo_list = $appCore->skuManager->getSKUItemPromoPhotos($pi['sku_item_id']);
				if($promo_photo_list){
					$pi_data['promo_photo_url'] = $promo_photo_list[0];
				}
				
				// Get Normal Photo
				$apply_photo_list = get_sku_apply_item_photos($pi['sku_apply_items_id']);
				$photo_list = get_sku_item_photos($pi['sku_item_id'], $pi);
				$pi_data['sku_photo_list'] = array();
				if(!$apply_photo_list)	$apply_photo_list = array();
				if(!$photo_list)	$photo_list = array();
				$all_photo_list = array_merge($apply_photo_list, $photo_list);
				if($all_photo_list){
					foreach($all_photo_list as $photo_path){
						$photo_details = array();
						$photo_details['abs_path'] = $photo_path;
						if(file_exists($photo_path)){
							$photo_details['last_update'] = date("Y-m-d H:i:s", filemtime($photo_path));
							//$photo_details['name'] = basename($photo_path);			
							$pi_data['sku_photo_list'][] = $photo_details;
						}
					}
				}
			
				// Member
				if($pi['member_disc_a']){	// Set Price
					$pi_data['member_fixed_price'] = round($pi['member_disc_a'], 2);
				}elseif($pi['member_disc_p']){	// Discount
					if(strpos($pi['member_disc_p'], '%')){
						$pi_data['member_discount_percent'] = str_replace('%', '', $pi['member_disc_p']);
					}else{
						$pi_data['member_discount_amount'] = $pi['member_disc_p'];
					}
				}
				
				// Non - Member
				if($pi['non_member_disc_a']){	// Set Price
					$pi_data['non_member_fixed_price'] = round($pi['non_member_disc_a'], 2);
				}elseif($pi['non_member_disc_p']){	// Discount
					if(strpos($pi['non_member_disc_p'], '%')){
						$pi_data['non_member_discount_percent'] = str_replace('%', '', $pi['non_member_disc_p']);
					}else{
						$pi_data['non_member_discount_amount'] = $pi['non_member_disc_p'];
					}
				}
				
				// Got selected member type
				if($pi['allowed_member_type']['member_type']){
					$pi_data['allowed_member_type'] = array();
					foreach($pi['allowed_member_type']['member_type'] as $tmp_member_type){
						$pi_data['allowed_member_type'][] = array(
							'member_type' => $tmp_member_type, 
							'member_type_desc' => trim($config['membership_type'][$tmp_member_type])
						);
					}
				}
				
				// Check Special For you
				if($promo['enable_special_for_you'] && $card_no_list){
					$target = trim($promo['special_for_you_info']['target']);
					if($target != 'sku' && $target != 'cat')	continue;	// unknown target
					
					$filter_fav = array();
					$filter_fav[] = "tbl.card_no in ($str_card_no)";
					$filter_fav[] = "tbl.date <".ms($promo['date_from']);
					$filter_fav[] = "tbl.date >=".ms(date("Y-m-d", strtotime("-".mi($promo['special_for_you_info']['month'])." month", strtotime($promo['date_from']))));

					$having = array();
					$having[] = "total_qty >=".mi($promo['special_for_you_info']['qty']);
					
					
					if($target == 'sku'){
						// Same SKU
						if(!isset($item_is_special_for_you['sku'][$sid])){
							$filter_fav[] = "tbl.sku_item_id=$sid";
							$str_filter_fav = "where ".join(' and ', $filter_fav);
							$str_having = "having ".join(' and ', $having);

							$sql = "select sum(qty) as total_qty
								from membership_fav_items tbl
								$str_filter_fav
								$str_having
								limit 1";
							//die($sql);
							$con->sql_query($sql);
							$tmp = $con->sql_fetchassoc();
							$con->sql_freeresult();
							
							$item_is_special_for_you['sku'][$sid] = $tmp['total_qty']>0 ? 1 : 0;
						}
						
						$pi_data['special_for_you'] = $item_is_special_for_you['sku'][$sid];
					}else{
						// Same Category
						
						// Get Category ID
						$con->sql_query("select c.id as cat_id, c.level as cat_level
							from sku_items si
							join sku on sku.id=si.sku_id
							join category c on c.id=sku.category_id
							where si.id=$sid");
						$cat_info = $con->sql_fetchassoc();
						$con->sql_freeresult();
						
						if($cat_info){
							// Found Category
							$cat_id = mi($cat_info['cat_id']);
							$cat_level = mi($cat_info['cat_level']);
							
							if(!isset($item_is_special_for_you['cat'][$cat_id])){
								$filter_fav[] = "cc.p".$cat_level."=$cat_id";
								$str_filter_fav = "where ".join(' and ', $filter_fav);
								$str_having = "having ".join(' and ', $having);
								
								$sql = "select sum(qty) as total_qty
									from membership_fav_items tbl
									join sku_items si on si.id=tbl.sku_item_id
									join sku on sku.id=si.sku_id
									join category_cache cc on cc.category_id=sku.category_id
									$str_filter_fav
									$str_having
									limit 1";
									
								$con->sql_query($sql);
								$tmp = $con->sql_fetchassoc();
								$con->sql_freeresult();
								
								$item_is_special_for_you['cat'][$cat_id] = $tmp['total_qty']>0 ? 1 : 0;
							}
							
							$pi_data['special_for_you'] = $item_is_special_for_you['cat'][$cat_id];
						}	
					}
				}
				
				$promo_data['item_list'][] = $pi_data;
				unset($pi_data);
			}
			$con->sql_freeresult($q2);
			
			$ret['promo_list'][] = $promo_data;
			unset($promo_data);
		}
		$con->sql_freeresult($q1);
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function get_my_member_voucher_list(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		if(!$this->member){
			$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		$ret = array();
		$ret['result'] = 1;
		$ret['voucher_list'] = array();
		
		$nric = trim($this->member['nric']);
		
		// Get Card No List
		$card_no_list = $appCore->memberManager->getMemberCardNoList($nric);
				
		if($card_no_list){
			// Get Voucher Prefix
			$sql_pre = "select * 
					from pos_settings ps
					where ps.branch_id = 1 and ps.setting_name='barcode_voucher_prefix'";

			$con->sql_query($sql_pre);
			$ps = $con->sql_fetchassoc();

			if (!$ps['setting_value']){
				$voucher_prefix = "VC";
			}else{
				$voucher_prefix = strtoupper($ps['setting_value']);
			}
			
			$first_day_of_month = date("Y-m-1");
			
			$filter = array();
			$filter[] = "mv.member_card_no in (".join(', ', array_map('ms', $card_no_list)).")";
			$filter[] = "mv.valid_to>=".ms($first_day_of_month);
			$str_filter = "where ".join(' and ', $filter);
			
			$sql = "select concat(mv.branch_id,'-',mv.batch_no,'-',mv.id) as voucher_id, concat(mv.branch_id,'-',mv.batch_no) as batch_id, mv.code, mv.voucher_value, mv.active as active, mv.activated as activated_time, mv.valid_from, mv.valid_to, mv.cancel_status as cancelled
				from mst_voucher mv
				$str_filter
				order by mv.id";
			//die($sql);
			$q1 = $con->sql_query($sql);
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
					
					// Used before current month
					if(strtotime($r['used_time']) < strtotime($first_day_of_month)){
						continue;
					}
				}
				
				unset($r['code']);
				
				$ret['voucher_list'][] = $r;
			}
			$con->sql_freeresult($q1);
		}
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function get_member_ads_banner_list(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		$ret = array();
		$ret['result'] = 1;
		$ret['banner_list'] = array();
		
		// Load All Banner List
		$banner_list = $appCore->memberManager->getMemberMobileAdsBannerList();
		if($banner_list){
			foreach($banner_list as $banner_name => $r){
				$tmp = array();
				$tmp['banner_name'] = $r['banner_name'];
				$tmp['banner_description'] = $r['banner_description'];
				$tmp['screen_name'] = $r['screen_name'];
				$tmp['screen_description'] = $r['screen_description'];
				$tmp['banner_info'] = $r['banner_info'];
				$tmp['last_update'] = $r['last_update'];
				
				$ret['banner_list'][] = $tmp;
			}
		}
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function get_cycle_count_list(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		// Must already login by user
		if(!$this->user){
			$this->error_die($this->err_list["need_user_session"], "need_user_session");
		}
		
		$doc_no = trim($_REQUEST['doc_no']);
		
		$ret = array();
		$ret['result'] = 1;
		$ret['data'] = array();
		
		$filter = array();
		$filter[] = "cc.st_branch_id=".mi($this->app_branch_id);
		$filter[] = "cc.pic_user_id=".mi($this->user['id']);
		$filter[] = "cc.active=1 and cc.status=1 and cc.approved=1";
		
		if($_REQUEST['year'] && $_REQUEST['month']){
			$y = mi($_REQUEST['year']);
			$m = mi($_REQUEST['month']);
			
			$date_from = $y.'-'.$m.'-1';
			$date_to = $y.'-'.$m.'-'.days_of_month($m, $y);
			
			$filter[] = "cc.propose_st_date between ".ms($date_from)." and ".ms($date_to);
		}
		
		if($doc_no)	$filter[] = "cc.doc_no=".ms($doc_no);
		$str_filter = "where ".join(' and ', $filter);
		
		$sql = "select cc.*, c.description as cat_desc, v.description as vendor_desc, br.description as brand_desc, sg.code sg_code, sg.description as sg_desc
			from cycle_count cc
			left join category c on c.id=cc.category_id
			left join vendor v on v.id=cc.vendor_id
			left join brand br on br.id=cc.brand_id
			left join sku_group sg on sg.branch_id=cc.sku_group_bid and sg.sku_group_id=cc.sku_group_id
			$str_filter
			order by cc.propose_st_date";
		//die($sql);
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$tmp = array();
			$tmp['doc_no'] = $r['doc_no'];
			$tmp['propose_st_date'] = $r['propose_st_date'];
			$tmp['estimate_sku_count'] = mi($r['estimate_sku_count']);
			$tmp['remark'] = trim($r['remark']);
			
			if($r['sent_to_stock_take']){
				$cc_status = 'sent_to_stock_take';
			}elseif($r['completed']){
				$cc_status = 'completed';
			}elseif($r['wip']){
				$cc_status = 'wip';
			}elseif($r['printed']){
				$cc_status = 'printed';
			}elseif($r['approved']){
				$cc_status = 'approved';
			}else{
				$cc_status = '';
			}
			$tmp['cc_status'] = $cc_status;
			
			$tmp['st_content_type'] = $r['st_content_type'];
			
			if($tmp['st_content_type'] == 'cat_vendor_brand'){
				//$tmp['category_id'] = $r['category_id'];
				if($r['category_id']>0){
					$tmp['cat_desc'] = $r['cat_desc'];
				}else{
					$tmp['cat_desc'] = 'All';
				}
				
				//$tmp['vendor_id'] = $r['vendor_id'];
				if($r['vendor_id']>0){
					$tmp['vendor_desc'] = $r['vendor_desc'];
				}else{
					$tmp['vendor_desc'] = 'All';
				}
				//$tmp['brand_id'] = $r['brand_id'];
				if($r['brand_id'] >= 0){
					if($r['brand_id'] == 0){
						$tmp['brand_desc'] = 'UN-BRANDED';
					}else{
						$tmp['brand_desc'] = $r['brand_desc'];
					}
				}else{
					$tmp['brand_desc'] = 'All';
				}
			}elseif($tmp['st_content_type'] == 'sku_group'){
				//$tmp['sku_group_bid'] = $r['sku_group_bid'];
				//$tmp['sku_group_id'] = $r['sku_group_id'];
				$tmp['sg_code'] = $r['sg_code'];
				$tmp['sg_desc'] = $r['sg_desc'];
			}
			
			// WIP Start Time
			if($r['wip']){
				$tmp['wip_start_time'] = $r['wip_start_time'];
			}
			
			$ret['data'][] = $tmp;
		}
		$con->sql_freeresult($q1);
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function get_cycle_count_by_doc_no($doc_no){
		global $con;
		
		if(!$doc_no){
			$this->error_die($this->err_list["cycle_count_need_doc_no"], "cycle_count_need_doc_no");
		}
		
		$filter = array();
		$filter[] = "cc.st_branch_id=".mi($this->app_branch_id);
		$filter[] = "cc.pic_user_id=".mi($this->user['id']);
		$filter[] = "cc.active=1 and cc.status=1 and cc.approved=1 and printed=1";
		$filter[] = "cc.doc_no=".ms($doc_no);
		$str_filter = "where ".join(' and ', $filter);
		
		// Get Cycle Count
		$con->sql_query($sql = "select cc.* from cycle_count cc $str_filter");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Cycle Count Not Found
		if(!$form){
			$this->error_die($this->err_list["cycle_count_invalid_doc_no"], "cycle_count_invalid_doc_no");
		}
		
		return $form;
	}
	
	function get_cycle_count_item_list(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		// Must already login by user
		if(!$this->user){
			$this->error_die($this->err_list["need_user_session"], "need_user_session");
		}
		
		// Get Cycle Count
		$doc_no = trim($_REQUEST['doc_no']);
		$form = $this->get_cycle_count_by_doc_no($doc_no);
		
		$ret = array();
		$ret['result'] = 1;
		$ret['items'] = array();
		
		$bid = mi($form['branch_id']);
		$cc_id = mi($form['id']);
		
		$filter = array();
		$filter[] = "branch_id=$bid and cc_id=$cc_id";
		$str_filter = "where ".join(' and ', $filter);
		
		// Get Cycle Count Items
		$q1 = $con->sql_query($sql = "select * from cycle_count_items $str_filter order by item_id");
		//die($sql);
		while($r = $con->sql_fetchassoc()){
			$tmp = array();
			$tmp['item_guid'] = $r['item_guid'];
			$tmp['item_id'] = $r['item_id'];
			$tmp['page_num'] = $r['page_num'];
			$tmp['row_num'] = $r['row_num'];
			$tmp['sku_item_id'] = $r['sku_item_id'];
			
			$ret['items'][] = $tmp;
		}
		$con->sql_freeresult();
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function cycle_count_mark_wip(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		// Must already login by user
		if(!$this->user){
			$this->error_die($this->err_list["need_user_session"], "need_user_session");
		}
		
		// Get Cycle Count
		$doc_no = trim($_REQUEST['doc_no']);
		$form = $this->get_cycle_count_by_doc_no($doc_no);
		$bid = mi($form['branch_id']);
		$cc_id = mi($form['id']);
		
		// Mark WIP
		$params = array();
		$params['user_id'] = $this->user['id'];
		
		$con->sql_begin_transaction();
		$result = $appCore->stockTakeManager->startCycleCount($bid, $cc_id, $params);
		
		if(!$result['ok']){
			// Failed to mark WIP
			if($result['error_code'] && $result['error']){
				$this->error_die($result['error'], $result['error_code']);
			}else{
				$this->error_die($this->err_list["unknown_error"], "unknown_error");
			}
		}
		$con->sql_commit();
		
		$ret = array();
		$ret['result'] = 1;
		$this->respond_data($ret);
	}
	
	function set_cycle_count_stock_take(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		// Must already login by user
		if(!$this->user){
			$this->error_die($this->err_list["need_user_session"], "need_user_session");
		}
		
		// Get Cycle Count
		$doc_no = trim($_REQUEST['doc_no']);
		$form = $this->get_cycle_count_by_doc_no($doc_no);
		
		if(!$form['wip'] || $form['completed']){
			$this->error_die($this->err_list["cycle_count_need_wip"], "cycle_count_need_wip");
		}
		
		$bid = mi($form['branch_id']);
		$cc_id = mi($form['id']);
		
		// Check Items
		$items = json_decode($_REQUEST['items'], true);
		//print_r($items);exit;
		if(!$items){
			$this->error_die($this->err_list["cycle_count_no_item"], "cycle_count_no_item");
		}
		
		$con->sql_begin_transaction();
		
		$form_st_time = strtotime($form['st_date']);
		
		$ret = array();
		$ret['result'] = 1;
		$ret['updated_items'] = array();
		
		// Loop items
		foreach($items as $r){
			$sid = mi($r['sku_item_id']);
			
			$upd = array();
			$upd['app_qty'] = mf($r['st_qty']);
			$upd['st_time'] = trim($r['st_time']);
			
			if(strtotime($upd['st_time']) < $form_st_time){
				$this->error_die(sprintf($this->err_list["cycle_count_item_invalid_st_time"], $sid), "cycle_count_item_invalid_st_time");
			}
			
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("update cycle_count_items set ".mysql_update_by_field($upd)." where branch_id=$bid and cc_id=$cc_id and sku_item_id=$sid");
			if($con->sql_affectedrows()){
				$ret['updated_items'][] = $sid;
			}
		}
		$con->sql_commit();		
		
		$this->respond_data($ret);
	}
	
	function validate_my_member_pass(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		// Member
		if(!$this->member){
			$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		$p = trim($_REQUEST['p']);
		if(!$p){
			$this->error_die($this->err_list["missing_params"], "missing_params");
		}
		
		if($p != $this->member['memb_password']){
			$this->error_die($this->err_list["member_pass_invalid"], "member_pass_invalid");
		}
		
		$ret = array();
		$ret['result'] = 1;
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function change_my_member_pass(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		// Member
		if(!$this->member){
			$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		$old_p = trim($_REQUEST['old_p']);
		$new_p = trim($_REQUEST['new_p']);
		if(!$old_p || !$new_p){
			$this->error_die($this->err_list["missing_params"], "missing_params");
		}
		
		// Old Password Incorrect
		if($old_p != $this->member['memb_password']){
			$this->error_die($this->err_list["member_pass_invalid"], "member_pass_invalid");
		}
		
		// Check Password
		$params = array();
		$params['user_id'] = 1;
		$result = $appCore->memberManager->changeMemberMobilePassword($this->member['nric'], $new_p, $params);
		
		if(!$result['ok']){
			if($result['error_code'] && $result['error']){
				$this->error_die($result['error'], $result['error_code']);
			}else{
				$this->error_die($this->err_list["unknown_error"], "unknown_error");
			}
		}
		
		$ret = array();
		$ret['result'] = 1;		
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function reset_my_member_pass(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		// Member
		if($this->member){
			$this->error_die($this->err_list["member_reset_failed_alrdy_login"], "member_reset_failed_alrdy_login");
		}
		
		$login_id = trim($_REQUEST['login_id']);
		$email = trim($_REQUEST['email']);
		
		if(!$login_id || !$email){
			$this->error_die($this->err_list["missing_params"], "missing_params");
		}
		
		// Get Member
		$member = $appCore->memberManager->getMember($login_id);
		if(!$member){
			$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		// Check Email
		if($member['email'] != $email){
			$this->error_die($this->err_list["member_reset_failed_wrong_email"], "member_reset_failed_wrong_email");
		}
		
		// Generate New Password
		$new_p = $appCore->generateRandomCode(10);
		
		$upd = array();
		$upd['memb_password'] = md5($new_p);
		
		// Send Email
		include_once("include/class.phpmailer.php");
	
		$mailer = new PHPMailer(true);
		//$mailer->From = "noreply@arms.com.my";
		$server_name = trim($config['server_name']);
		$mailer->FromName = $server_name." Membership Notification";
		$mailer->Subject = $server_name." Membership Password Reset";
		$mailer->IsHTML(true);
		//$mailer->IsMail();
		//$mailer->AddCustomHeader("Content-Transfer-Encoding: base64");
	
		$mailer->AddAddress($email);
		$email_body = "<h2><u>You have reset your membership mobile app password</u></h2>\r\n";
		$email_body .= "Below is your membership details.<br />\r\n";
		$email_body .= "<b>NRIC / Passport</b>: ".$member['nric']."<br />\r\n";
		$email_body .= "<b>Card No</b>: ".$member['card_no']."<br />\r\n";
		$email_body .= "<b>Full Name</b>: ".$member['name']."<br />\r\n";
		$email_body .= "<b>Password</b>: ".$new_p."<br />\r\n";
		
		$email_body .= "<br />If you didn't do it, please contact us immediately.<br /><br />\r\n";
		
		$mailer->Body = $email_body;
		
		// send the mail
		//print_r($mailer);
		if($send_success = phpmailer_send($mailer, $mailer_info)){
			//print ": OK";
		}else{
			//print ": Failed";
			//print "> ".$mailer_info['err'];
		}

		$mailer->ClearAddresses();
		
		$con->sql_query("update membership set ".mysql_update_by_field($upd)." where nric=".ms($member['nric']));
		
		log_br(1, 'MEMBERSHIP', 0, 'Reset Member Mobile App Password (NRIC:'.$member['nric'].")");
		
		$ret = array();
		$ret['result'] = 1;		
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function register_new_member(){
		global $con, $config, $appCore, $LANG;
		
		// Validate Device
		$this->is_valid_device();
		
		// Member
		if($this->member){
			$this->error_die($this->err_list["member_failed_alrdy_login"], "member_failed_alrdy_login");
		}
		
		// Email
		$email = trim($_REQUEST['email']);
		if(!$email){
			$this->error_die($LANG['MEMBERSHIP_EMAIL_EMPTY'], 'LANG_MEMBERSHIP_EMAIL_EMPTY');
		}else{
			if(!preg_match(EMAIL_REGEX, $email)){
				$this->error_die($LANG['MEMBERSHIP_EMAIL_PATTERN_INVALID'], 'LANG_MEMBERSHIP_EMAIL_PATTERN_INVALID');
			}
		}
		
		// Password
		$password = trim($_REQUEST['p']);
		if(!$password){
			$this->error_die($this->err_list["missing_params"], "missing_params");
		}else{
			// Minimum 6 char
			if(strlen($password)<6){
				$this->error_die($LANG['MEMBERSHIP_MOBILE_PASS_MIN_CHAR'], 'LANG_MEMBERSHIP_MOBILE_PASS_MIN_CHAR');
			}
			
			// must alphanumeric only
			if(!ctype_alnum($password)){
				$this->error_die($LANG['MEMBERSHIP_MOBILE_PASS_ALPHANUMERIC'], 'LANG_MEMBERSHIP_MOBILE_PASS_ALPHANUMERIC');
			}
		}
		
		// Member Type
		$default_member_type = 'member1';
		if($config['membership_mobile_settings']['default_member_type']){
			$default_member_type = trim($config['membership_mobile_settings']['default_member_type']);
			if(!$config['membership_type'][$default_member_type]){
				$this->error_die($this->err_list['member_default_type_missing'], 'member_default_type_missing');
			}
		}
		
		// Check Duplicate Email
		$con->sql_query("select email from membership where email=".ms($email));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($tmp){
			$this->error_die($this->err_list['member_email_alrdy_used'], "member_email_alrdy_used");
		}
		
		$con->sql_begin_transaction();
		
		// Get New Card No
		$result = $appCore->memberManager->getMobileAppNewCardNo();
		if(!$result['ok']){
			if($result['error_code'] && $result['error']){
				$this->error_die($result['error'], $result['error_code']);
			}else{
				$this->error_die($this->err_list["unknown_error"], "unknown_error");
			}
		}
		
		$new_card_no = $result['new_card_no'];
		$nric = $new_card_no;
		
		$upd = array();
		$membership_guid = $appCore->newGUID();
		$upd['membership_guid'] = $membership_guid;
		$upd['nric'] = $nric;
		$upd['name'] = 'Mobile App User';
		//$upd['gender'] = $form['gender'];
		$upd['member_type'] = $default_member_type;
		//$upd['dob'] = $form['dob'];
		//$upd['postcode'] = trim($form['postcode']);
		//$upd['address'] = trim($form['address']);
		//$upd['city'] = trim($form['city']);
		//$upd['state'] = trim($form['state']);
		//$upd['phone_3'] = $form['phone_3'];
		$upd['email'] = $email;
		
		$upd['card_no'] = $new_card_no;
		$upd['issue_date'] = date("Y-m-d");
		
		$membership_mobile_new_register_expiry_duration_year = 1;
		if($config['membership_mobile_new_register_expiry_duration_year']){
			// life = no expiry date
			if(trim($config['membership_mobile_new_register_expiry_duration_year'])=='life'){
				$upd['next_expiry_date'] = '2037-12-31';
			}else{
				$membership_mobile_new_register_expiry_duration_year = mi($config['membership_mobile_new_register_expiry_duration_year']);
				if($membership_mobile_new_register_expiry_duration_year <= 0){
					$membership_mobile_new_register_expiry_duration_year = 1;
				}
				$upd['next_expiry_date'] = date("Y-m-d", strtotime("+".$membership_mobile_new_register_expiry_duration_year." year"));
			}
		}else{
			$upd['next_expiry_date'] = date("Y-m-d", strtotime("+1 year"));
		}
		
		
		
		$upd['apply_branch_id'] = 1;
		$upd['mobile_registered'] = 1;
		$upd['mobile_registered_time'] = 'CURRENT_TIMESTAMP';
		$upd['memb_password'] = md5($password);
		
		//print_r($upd);
		
		$con->sql_query("insert into membership ".mysql_insert_by_field($upd));
		log_br(1, 'MEMBERSHIP', 0, 'Add Membership from Mobile, Email: ' . $email);
		
		// Verify
		$upd2 = array();
		$upd2['membership_guid'] = $upd['membership_guid'];
		$upd2['nric'] = $upd['nric'];
		$upd2['card_no'] = $upd['card_no'];
		$upd2['branch_id'] = $upd['apply_branch_id'];
		$upd2['user_id'] = 1;
		
		$card_type = '';
		foreach($config['membership_cardtype'] as $type=>$ct)
		{
			if (preg_match($ct['pattern'], $upd['card_no']))
			{
				$card_type = $type;
				break;
			}
		}
		
		$upd2['card_type'] = $card_type;
		$upd2['issue_date'] = $upd['issue_date'];
		$upd2['expiry_date'] = $upd['next_expiry_date'];
		$upd2['remark'] = 'N';
		$upd2['added'] = 'CURRENT_TIMESTAMP';
		$upd2['m_type'] = $form['member_type'];
		$con->sql_query("insert into membership_history ".mysql_insert_by_field($upd2));
		
		$upd3 = array();
		$upd3['verified_by'] = 1;
		$upd3['verified_date'] = "CURRENT_TIMESTAMP";
		$con->sql_query("update membership set ".mysql_update_by_field($upd3)." where nric=".ms($upd['nric']));

		// Send Email
		include_once("include/class.phpmailer.php");
	
		$mailer = new PHPMailer(true);
		//$mailer->From = "noreply@arms.com.my";
		$server_name = trim($config['server_name']);
		$mailer->FromName = $server_name." Membership Notification";
		$mailer->Subject = $server_name." New Member Registered";
		$mailer->IsHTML(true);
		//$mailer->IsMail();
		//$mailer->AddCustomHeader("Content-Transfer-Encoding: base64");
	
		$mailer->AddAddress($email);
		//$mailer->AddAddress("nava@arms.my");
		$email_body = "<h2><u>New Member Registered</u></h2>\r\n";
		$email_body .= "Congratulation, you have successfully registered as our membership, below is your membership details.<br />\r\n";
		//$email_body .= "<b>NRIC / Passport</b>: ".$form['nric']."<br />\r\n";
		$email_body .= "<b>Card No</b>: ".$new_card_no."<br />\r\n";
		//$email_body .= "<b>Full Name</b>: ".$form['name']."<br />\r\n";
		$email_body .= "<b>Password</b>: ".$password."<br />\r\n";
		
		$mailer->Body = $email_body;
		
		// send the mail
		//print_r($mailer);
		if($send_success = phpmailer_send($mailer, $mailer_info)){
			//print ": OK";
		}else{
			//print ": Failed";
			//print "> ".$mailer_info['err'];
		}

		$mailer->ClearAddresses();
		
		$con->sql_commit();
		
		$ret = array();
		$ret['result'] = 1;
		$ret['card_no'] = $new_card_no;
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function existing_member_setup(){
		global $con, $config, $appCore, $LANG;
		
		// Validate Device
		$this->is_valid_device();
		
		// Member
		if($this->member){
			$this->error_die($this->err_list["member_failed_alrdy_login"], "member_failed_alrdy_login");
		}
		
		$login_id = trim($_REQUEST['login_id']);
				
		if(!$login_id){
			$this->error_die($this->err_list["missing_params"], "missing_params");
		}
		
		/*$filter = array();
		$filter[] = "(m.nric=".ms($login_id)." or m.card_no=".ms($login_id).")";
		//$filter[] = "m.mobile_registered=0";
		
		$str_filter = "where ".join(' and ', $filter);
		$sql = "select m.*
			from membership m
			$str_filter";
		//die($sql);
		
		// Check Database
		$con->sql_query($sql);
		$member = $con->sql_fetchassoc();
		$con->sql_freeresult();*/
		
		// Get Member
		$member = $appCore->memberManager->getMember($login_id);
		if(!$member){
			$this->error_die($this->err_list["member_data_not_found"]);
		}
		
		// Member already registered
		if($member['mobile_registered'] || $member['memb_password']){
			$this->error_die($this->err_list["member_alrdy_hv_mobile_access"], "member_alrdy_hv_mobile_access");
		}
		
		// Email Address
		$email = trim($member['email']);
		
		// Mobile Phone
		$mobile_num = preg_replace("/[^0-9]/", "", trim($member['phone_3']));
		
		$success_register = 0;
		if($email){	// Got Email
			// Generate Random Password
			/*$password = $appCore->generateRandomCode(10);
			
			// Check Password
			$params = array();
			$params['user_id'] = 1;
			$result = $appCore->memberManager->changeMemberMobilePassword($member['nric'], $password, $params);
			if(!$result['ok']){
				$this->error_die($result["error"]);
			}
			
			// Send Email
			include_once("include/class.phpmailer.php");
		
			$mailer = new PHPMailer(true);
			//$mailer->From = "noreply@arms.com.my";
			$mailer->FromName = "ARMS Notification";
			$mailer->Subject = "Member First Time Login Setup";
			$mailer->IsHTML(true);
			$mailer->IsMail();
			//$mailer->AddCustomHeader("Content-Transfer-Encoding: base64");
		
			$mailer->AddAddress($email);
			//$mailer->AddAddress("nava@arms.my");
			$email_body = "<h2><u>New Mobile App Member Setup</u></h2>\r\n";
			$email_body .= "Congratulation, you have successfully setup your mobile app membership, below is your membership details.<br />\r\n";
			$email_body .= "<b>NRIC / Passport</b>: ".$member['nric']."<br />\r\n";
			$email_body .= "<b>Card No</b>: ".$member['card_no']."<br />\r\n";
			$email_body .= "<b>Full Name</b>: ".$member['name']."<br />\r\n";
			$email_body .= "<b>Password</b>: ".$password."<br />\r\n";
			
			$mailer->Body = $email_body;
			
			// send the mail
			//print_r($mailer);
			if($send_success = phpmailer_send($mailer, $mailer_info)){
				//print ": OK";
			}else{
				//print ": Failed";
				//print "> ".$mailer_info['err'];
			}

			$mailer->ClearAddresses();*/
			$this->generate_member_first_time_setup_email($member, $email);
			
			$success_register = 1;
		}elseif($mobile_num){	// No Email but got Mobile Phone Number
			if(!$config['isms_user'] || !$config['isms_pass']){
				// Server no turn on isms config
				$this->error_die($this->err_list["server_no_sms_config"], "server_no_sms_config");
			}
		}else{
			// No Email and No Mobile Phone Number
			$this->error_die($this->err_list["first_time_setup_failed"], "first_time_setup_failed");
		}
		
		$ret = array();
		$ret['result'] = 1;
		$ret['success_register'] = $success_register;
		if(!$success_register && $mobile_num){
			$ret['mobile_num'] = $mobile_num;
		}
				
		// Return Data
		$this->respond_data($ret);
	}
	
	private function generate_member_first_time_setup_email($member, $email){
		global $con, $config, $appCore, $LANG;
		
		// Generate Random Password
		$password = $appCore->generateRandomCode(10);
		
		// Check Password
		$params = array();
		$params['user_id'] = 1;
		$result = $appCore->memberManager->changeMemberMobilePassword($member['nric'], $password, $params);
		if(!$result['ok']){
			if($result['error_code'] && $result['error']){
				$this->error_die($result['error'], $result['error_code']);
			}else{
				$this->error_die($this->err_list["unknown_error"], "unknown_error");
			}
		}
		
		// Send Email
		include_once("include/class.phpmailer.php");
	
		$mailer = new PHPMailer(true);
		//$mailer->From = "noreply@arms.com.my";
		$server_name = trim($config['server_name']);
		$mailer->FromName = $server_name. " Membership Notification";
		$mailer->Subject = $server_name. " Membership First Time Login Setup";
		$mailer->IsHTML(true);
		//$mailer->IsMail();
		//$mailer->AddCustomHeader("Content-Transfer-Encoding: base64");
	
		$mailer->AddAddress($email);
		//$mailer->AddAddress("nava@arms.my");
		$email_body = "<h2><u>New Mobile App Member Setup</u></h2>\r\n";
		$email_body .= "Congratulation, you have successfully setup your mobile app membership, below is your membership details.<br />\r\n";
		$email_body .= "<b>NRIC / Passport</b>: ".$member['nric']."<br />\r\n";
		$email_body .= "<b>Card No</b>: ".$member['card_no']."<br />\r\n";
		$email_body .= "<b>Full Name</b>: ".$member['name']."<br />\r\n";
		$email_body .= "<b>Password</b>: ".$password."<br />\r\n";
		
		$mailer->Body = $email_body;
		
		// send the mail
		//print_r($mailer);
		if($send_success = phpmailer_send($mailer, $mailer_info)){
			//print ": OK";
		}else{
			//print ": Failed";
			//print "> ".$mailer_info['err'];
		}

		$mailer->ClearAddresses();
	}
	
	function get_member_notice_board(){
		global $con, $config, $appCore, $LANG;
		
		// Validate Device
		$this->is_valid_device();
				
		$last_update = trim($_REQUEST['last_update']);
		
		// Got provide last update, can check got changes or not
		$con->sql_query("select max(last_update) as latest_update from memberships_notice_board_items");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
			
		$latest_update = $tmp['latest_update'];
		
		$ret = array();
		$ret['result'] = 1;
		$ret['last_update'] = $latest_update;
		$ret['data'] = array();
		
		if($latest_update){	// Got Data
			if($last_update){	// client got provide last update
				if(strtotime($last_update) == strtotime($latest_update)){
					unset($ret['data']);
					$ret['no_change'] = 1;
				}
			}
			
			if(!isset($ret['no_change'])){
				$item_list = $appCore->memberManager->getNoticeBoardItemList(array('active'=>1));
				//$ret['data'] = $item_list;
				if($item_list){
					$required_fields = array('id', 'item_type', 'image_click_link', 'item_url', 'video_site', 'video_link', 'sequence');
					foreach($item_list as $r){
						$tmp = array();
						foreach($required_fields as $field){
							$tmp[$field] = $r[$field];
						}
						$ret['data'][] = $tmp;
					}
				}
			}
		}
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function update_my_member_info(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		if(!$this->member){
			$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		$nric = trim($this->member['nric']);
		
		$upd = array();
		
		// Name
		if(isset($_REQUEST['name'])){
			$upd['name'] = trim($_REQUEST['name']);
			if(!$upd['name'])	$this->error_die($this->err_list["member_update_invalid_name"], "member_update_invalid_name");
		}
		
		// Gender
		if(isset($_REQUEST['gender'])){
			$upd['gender'] = strtoupper(trim($_REQUEST['gender']));
			if($upd['gender'] != 'M' && $upd['gender'] != 'F')	$this->error_die($this->err_list["member_update_invalid_gender"], "member_update_invalid_gender");
		}
		
		// DOB
		if(isset($_REQUEST['dob'])){
			$dob = trim($_REQUEST['dob']);
			if(!$dob)	$this->error_die($this->err_list["member_update_invalid_dob"], "member_update_invalid_dob");
			
			list($dob_y, $dob_m, $dob_d) = explode("-", $dob);
			if($dob_y < 1900)	$this->error_die($this->err_list["member_update_invalid_dob_y"], "member_update_invalid_dob_y");
			if($dob_m < 1 || $dob_m > 12)	$this->error_die($this->err_list["member_update_invalid_dob_m"], "member_update_invalid_dob_m");
			if($dob_d < 1 || $dob_d > days_of_month($dob_m, $dob_y))	$this->error_die($this->err_list["member_update_invalid_dob_m"], "member_update_invalid_dob_m");
			$upd['dob'] = sprintf("%04d%02d%02d", $dob_y, $dob_m, $dob_d);
		}
		
		// Postcode
		if(isset($_REQUEST['postcode'])){
			$upd['postcode'] = trim($_REQUEST['postcode']);
			if(!$upd['postcode'])	$this->error_die($this->err_list["member_update_invalid_postcode"], "member_update_invalid_postcode");
		}
		
		// Address
		if(isset($_REQUEST['address'])){
			$upd['address'] = $appCore->removeLinebreakAndWhitespace(trim($_REQUEST['address']));
			if(!$upd['address'])	$this->error_die($this->err_list["member_update_invalid_address"], "member_update_invalid_address");
		}
		
		// City
		if(isset($_REQUEST['city'])){
			$upd['city'] = trim($_REQUEST['city']);
			if(!$upd['city'])	$this->error_die($this->err_list["member_update_invalid_city"], "member_update_invalid_city");
		}
		
		// State
		if(isset($_REQUEST['state'])){
			$upd['state'] = trim($_REQUEST['state']);
			if(!$upd['state'])	$this->error_die($this->err_list["member_update_invalid_state"], "member_update_invalid_state");
			
			$state_list = array('Johor', 'Kedah', 'Kuala Lumpur', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Penang', 'Pahang', 'Perak', 'Perlis', 'Selangor', 'Terengganu', 'Sabah', 'Sarawak', 'Others');
			if(!in_array($upd['state'], $state_list))	$this->error_die($this->err_list["member_update_invalid_state"], "member_update_invalid_state");
		}
		
		// Mobile Phone
		if(isset($_REQUEST['phone_3'])){
			$upd['phone_3'] = trim($_REQUEST['phone_3']);
			if(!$upd['phone_3'])	$this->error_die($this->err_list["member_update_invalid_phone_3"], "member_update_invalid_phone_3");
		}
		
		if(!$upd){
			$this->error_die($this->err_list["member_update_nothing"], "member_update_nothing");
		}
		
		$con->sql_query("update membership set ".mysql_update_by_field($upd)." where nric=".ms($nric));
		
		log_br(1, 'MEMBERSHIP', 0, 'Mobile Update Profie, NRIC#'.$nric.", changes: ".print_r($upd, true));
		
		$ret = array();
		$ret['result'] = 1;		
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function get_my_member_coupon_list(){
		global $con, $config, $appCore;
		
		// Call using member handler
		$this->getHandler('member')->get_my_member_coupon_list();
	}
	
	function logout_my_member(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		if(!$this->member){
			$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		$con->sql_query("delete from member_app_session where nric=".ms($this->X_MEM_NRIC)." and session_token=".ms($this->X_MEM_TOKEN)." and device_id=".ms($this->DEVICE_ID));
		
		$ret = array();
		$ret['result'] = 1;
		// Return Data
		$this->respond_data($ret);
	}
	
	// This function is use for KB Fun only
	function get_machine_branch_info(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		// Must already login by user
		if(!$this->user){
			$this->error_die($this->err_list["need_user_session"], "need_user_session");
		}
		
		$to_bid = mi($_REQUEST['to_bid']);
		if($to_bid<=0)	$this->error_die(sprintf($this->err_list["invalid_data"], 'to_bid'), "invalid_data");
		
		// Load uncheckout DO
		$con->sql_query("select count(*) as c
						   from do 
						   where do.active=1 and do.status in (0,1,2) and do.checkout=0
						   and ((do.do_type='credit_sales' and do.branch_id=$to_bid) or (do.do_type='transfer' and do.do_branch_id=$to_bid))");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$uncheckout_do_count = mi($tmp['c']);
		
		
		$ret = array();
		$ret['result'] = 1;
		$ret['uncheckout_do_count'] = $uncheckout_do_count;
		// Return Data
		$this->respond_data($ret);
	}
	
	function upload_my_member_profile_image(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		if(!$this->member){
			$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		$nric = trim($this->member['nric']);
		
		// No File was Uploaded
		if(!isset($_FILES['profile_image']))	$this->error_die(sprintf($this->err_list["invalid_data"], 'profile_image'), "invalid_data");
		
		// Set Image
		$result = $appCore->memberManager->setMemberProfileImage($nric, $_FILES['profile_image']);
		if(!$result['ok']){
			if($result['error_code'] && $result['error']){
				$this->error_die($result['error'], $result['error_code']);
			}else{
				$this->error_die($this->err_list["unknown_error"], "unknown_error");
			}
		}
		$image_url = $result['image_url'];
	
		$ret = array();
		$ret['result'] = 1;
		$ret['image_url'] = $image_url;
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function get_my_member_package_list(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		if(!$this->member){
			$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		$nric = trim($this->member['nric']);
		
		// Get Available Package List
		$mpp_list = $appCore->memberManager->getMemberPurchasePackageList($nric, 'available');
		$package_data = array();
		if($mpp_list){
			foreach($mpp_list as $mpp){
				$data = array();
				$data['guid'] = $mpp['guid'];
				$data['package_ref_no'] = $mpp['ref_no'];
				$data['pos_receipt_ref_no'] = $mpp['pos_receipt_ref_no'];
				$data['date'] = $mpp['date'];
				$data['qty'] = $mpp['qty'];
				$data['earn_entry'] = $mpp['earn_entry'];
				$data['used_entry'] = $mpp['used_entry'];
				$data['remaining_entry'] = $mpp['remaining_entry'];
				$data['added'] = $mpp['added'];
				$data['last_update'] = $mpp['last_update'];
				$data['package_unique_id'] = $mpp['package_unique_id'];
				$data['doc_no'] = $mpp['doc_no'];
				$data['title'] = $mpp['title'];
				$data['pos_branch_id'] = $mpp['pos_branch_id'];
				$data['pos_bcode'] = $mpp['pos_bcode'];
				$data['sku_item_id'] = $mpp['link_sku_item_id'];
				$data['item_list'] = array();
				
				// Get Package Items
				$mpp_items_list = $appCore->memberManager->getMemberPurchasedPackageItems($mpp['guid']);
				if($mpp_items_list){
					foreach($mpp_items_list as $mpp_items){
						$tmp = array();
						$tmp['guid'] = $mpp_items['guid'];
						$tmp['title'] = $mpp_items['title'];
						$tmp['description'] = $mpp_items['description'];
						$tmp['remark'] = $mpp_items['remark'];
						$tmp['entry_need'] = $mpp_items['entry_need'];
						$tmp['max_redeem'] = $mpp_items['max_redeem'];
						$tmp['used_count'] = $mpp_items['used_count'];
						$tmp['sequence'] = $mpp_items['sequence'];
						
						$data['item_list'][] = $tmp;
					}
				}
				
				$package_data[] = $data;
			}
		}
		$ret = array();
		$ret['result'] = 1;
		$ret['package_list'] = $package_data;
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function get_my_member_package_redeem_history(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		if(!$this->member){
			$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		$nric = trim($this->member['nric']);
		$package_guid = trim($_REQUEST['package_guid']);
		$package_items_guid = trim($_REQUEST['package_items_guid']);
		
		// Get Package Redeem History
		$history_data = $sa_list = array();
		$params = array();
		$params['get_sa_info'] = 1;
		if($package_guid)	$params['mpp_guid'] = $package_guid;
		if($package_items_guid)	$params['mppi_guid'] = $package_items_guid;
		
		$his_list = $appCore->memberManager->getMemberPurchasePackageRedeemHistory($nric, $params);
		if($his_list){
			foreach($his_list as $r){
				$data = array();
				$data['guid'] = $r['guid'];
				$data['branch_id'] = $r['branch_id'];
				$data['bcode'] = $r['bcode'];
				$data['package_guid'] = $r['mpp_guid'];
				$data['package_items_guid'] = $r['purchased_package_items_guid'];
				$data['date'] = $r['date'];
				$data['used_entry'] = $r['used_entry'];
				$data['service_rating'] = $r['service_rating'];
				$data['added'] = $r['added'];
				$data['package_title'] = $r['package_title'];
				$data['item_title'] = $r['item_title'];
				$data['sa_info'] = array();
				
				if($r['sa_info']){
					foreach($r['sa_info']['sa_list'] as $sa_id => $sa){
						$tmp['sa_id'] = $sa_id;
						$tmp['code'] = $sa['sa_info']['code'];
						$tmp['name'] = $sa['sa_info']['name'];
						$tmp['rate'] = mi($sa['rate']);
						$tmp['photo_url'] = $sa['sa_info']['photo_url'];
						$data['sa_info'][] = $tmp;
					}
				}
				
				$history_data[] = $data;
			}
		}
		$ret = array();
		$ret['result'] = 1;
		$ret['history_data'] = $history_data;
				
		// Return Data
		$this->respond_data($ret);
	}
	
	function rate_my_member_package_redeem_history(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		if(!$this->member){
			$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		$nric = trim($this->member['nric']);
		
		// Redeem GUID
		$redeem_guid = trim($_REQUEST['redeem_guid']);
		if(!$redeem_guid)	$this->error_die(sprintf($this->err_list["invalid_data"], 'redeem_guid'), "invalid_data");
		
		// Service Rate
		$service_rating = mi($_REQUEST['service_rating']);
		if(!$service_rating)	$this->error_die(sprintf($this->err_list["invalid_data"], 'service_rating'), "invalid_data");
		if($service_rating<0 || $service_rating>5)	$this->error_die($this->err_list["invalid_rating"], "invalid_rating");
		
		$sa_rating = trim($_REQUEST['sa_rating']);
		if($sa_rating){
			$sa_rating = json_decode($sa_rating, true);
			//print_r($sa_rating);
		}
		
		// Get Redeem History
		$params = array();
		$params['mppir_guid'] = $redeem_guid;		
		$mppir = $appCore->memberManager->getMemberPurchasePackageRedeemHistory($nric, $params);
		if(!$mppir)	$this->error_die($this->err_list["redeem_history_not_found"], "redeem_history_not_found");
			
		// Customer got submit sales agent rating
		
		$params = array();
		$params['service_rating'] = $service_rating;
		if($sa_rating){
			// Check Sales Agent is Valid or Not
			foreach($sa_rating as $sa_id => $sa_rate){
				if(!isset($mppir['sa_info']['sa_list'][$sa_id])){
					$this->error_die(sprintf($this->err_list["redeem_history_sa_not_found"], $sa_id), "redeem_history_sa_not_found");
				}
				
				$params['sa_rating'][$sa_id] = $sa_rate;
			}
		}
		
		// Begin
		$con->sql_begin_transaction();
		
		// Update Rating
		$result = $appCore->memberManager->updateMemberPurchasePackageRedeemHistoryRate($redeem_guid, $params);
		if(!$result['ok']){
			if($result['error_code'] && $result['error']){
				$this->error_die($result['error'], $result['error_code']);
			}else{
				$this->error_die($this->err_list["unknown_error"], "unknown_error");
			}
		}
		
		// Commit
		$con->sql_commit();
		
		//print_r($mppir);exit;
		$ret = array();
		$ret['result'] = 1;
				
		// Return Data
		$this->respond_data($ret);
	}
	
	function get_config(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		$selected_config_name = trim($_REQUEST['config_name']);
		
		//print_r($mppir);exit;
		$ret = array();
		$ret['result'] = 1;
		$ret['config_data'] = array();
		
		$config_list = array('membership_state_settings', 'arms_currency', 'arms_marketplace_settings', 'receipt_running_no', 'membership_module', 'ewallet_settings');
		foreach($config_list as $config_name){
			if($selected_config_name && $selected_config_name != $config_name)	continue;
			
			if($config_name == 'arms_marketplace_settings'){
				// Marketplace Config - Only return certain value
				$ret['config_data'][$config_name]['marketplace_url'] = trim($config[$config_name]['marketplace_url']);
				$ret['config_data'][$config_name]['ecom_token'] = trim($config[$config_name]['ecom_token']);
			}else{
				// Other - Return full value
				$ret['config_data'][$config_name] = $config[$config_name];
			}
		}
		
				
		// Return Data
		$this->respond_data($ret);
	}
	
	function request_sms_otp(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->is_valid_device();
		
		if(!$config['isms_user'] || !$config['isms_pass']){
			// Server no turn on isms config
			$this->error_die($this->err_list["server_no_sms_config"], "server_no_sms_config");
		}
			
		//if(!$this->member){
			// No Login yet
			$login_id = trim($_REQUEST['login_id']);
			$mobile_num = preg_replace("/[^0-9]/", "", trim($_REQUEST['mobile_num']));
			
			if(!$login_id || !$mobile_num){
				$this->error_die($this->err_list["missing_params"], "missing_params");
			}
			
			// Get Member
			$member = $appCore->memberManager->getMember($login_id);		
			if(!$member){
				$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
			}
			
			// Validate Mobile Phone
			$mem_mobile_num = preg_replace("/[^0-9]/", "", trim($member['phone_3']));
			if($mem_mobile_num != $mobile_num){
				// Mobile Phone Number Different
				$this->error_die($this->err_list["invalid_member_mobile"], "invalid_member_mobile");
			}
		//}else{
			
		//}
		
		
		// Begin
		$con->sql_begin_transaction();
		
		$params = array();
		$params['member_object'] = $member;
		$success = $appCore->memberManager->sendMemberMobileOTP($member['nric'], $mem_mobile_num, $params);
		
		// Commit
		$con->sql_commit();
		
		if(!$success){
			// Send SMS Failed
			$this->error_die($this->err_list["send_otp_failed"], "send_otp_failed");
		}
		
		$ret = array();
		$ret['result'] = 1;
		
		// Return Data
		$this->respond_data($ret);
	}
	
	function existing_member_setup_with_otp(){
		global $con, $config, $appCore, $LANG;
		
		// Validate Device
		$this->is_valid_device();
		
		// Member
		if($this->member){
			$this->error_die($this->err_list["member_failed_alrdy_login"], "member_failed_alrdy_login");
		}
		
		$login_id = trim($_REQUEST['login_id']);
		$mobile_num = preg_replace("/[^0-9]/", "", trim($_REQUEST['mobile_num']));
		$otp_code = trim($_REQUEST['otp_code']);
		$email = trim($_REQUEST['email']);
		
		if(!$login_id || !$mobile_num || !$otp_code){
			$this->error_die($this->err_list["missing_params"], "missing_params");
		}
		
		// Check Email
		if(!$email){
			$this->error_die($LANG['MEMBERSHIP_EMAIL_EMPTY'], 'LANG_MEMBERSHIP_EMAIL_EMPTY');
		}else{
			if(!preg_match(EMAIL_REGEX, $email)){
				$this->error_die($LANG['MEMBERSHIP_EMAIL_PATTERN_INVALID'], 'LANG_MEMBERSHIP_EMAIL_PATTERN_INVALID');
			}
		}
		
		// Get Member
		$member = $appCore->memberManager->getMember($login_id);		
		if(!$member){
			$this->error_die($this->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		// Member already have email
		if($member['email']){
			$this->error_die($this->err_list["first_time_otp_setup_failed_got_email"], "first_time_otp_setup_failed_got_email");
		}
		
		// Validate Mobile Phone
		$mem_mobile_num = preg_replace("/[^0-9]/", "", trim($member['phone_3']));
		if($mem_mobile_num != $mobile_num){
			// Mobile Phone Number Different
			$this->error_die($this->err_list["invalid_member_mobile"], "invalid_member_mobile");
		}
		
		// Check Duplicate Email
		$con->sql_query("select email from membership where email=".ms($email));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($tmp){
			$this->error_die($this->err_list['member_email_alrdy_used'], "member_email_alrdy_used");
		}
		// Begin
		$con->sql_begin_transaction();
		
		// Check OTP Code
		$filter = array();
		$filter[] = "card_no=".ms($member['card_no']);
		$filter[] = "mobile_num=".ms($mobile_num);
		$str_filter = "where ".join(' and ', $filter);
		$con->sql_query("select * from memberships_otp $str_filter order by added desc limit 1");
		$otp_data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// OTP Data Not Found or Already Used or Different with Database
		$valid_time = strtotime("-5 minute");
		if(!$otp_data || $otp_data['used'] || $otp_data['otp_code'] != $otp_code || strtotime($otp_data['added']) < $valid_time){
			$this->error_die($this->err_list['invalid_otp'], "invalid_otp");
		}
		
		// Update Member Email
		$upd = array();
		$upd['email'] = $email;
		$con->sql_query("update membership set ".mysql_update_by_field($upd)." where nric=".ms($member['nric']));
		
		// Send Email
		$this->generate_member_first_time_setup_email($member, $email);
		
		$upd = array();
		$upd['used'] = 1;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update memberships_otp set ".mysql_update_by_field($upd)." where guid=".ms($otp_data['guid']));
		
		// Commit
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

$API_ARMS_INTERNAL = new API_ARMS_INTERNAL();
?>