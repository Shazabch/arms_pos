<?php
/*
8/14/2019 1:43 PM Justin
- ARMS Marketplace API

11/21/2019 2:36 PM Andy
- Added api "validate_arms_login".

12/10/2019 10:50 AM Andy
- Enhanced "validate_arms_login" to return user_id.

12/23/2019 5:14 PM Andy
- Fixed api "get_sku" cant return sku application photo .

12/27/2019 5:42 PM Andy
- Fixed api "cancel_order_items" unable to delete the item if the same item repeat in multiple row.
- Fixed log_br should log the action to marketplace branch.

1/3/2020 11:24 AM Justin
- Bug fixed on "uncheckout_do_qty" was passing the wrong branch ID.

1/15/2020 1:15 PM Andy
- Fixed shipping_item_code not assigned.

1/20/2020 1:14 PM William
- Enhanced to show detail error message of do status to function cancel_order_items.

2/5/2020 2:44 PM Andy
- Enhanced "get_sku" to return vendor_id.
- Added API "get_vendor_count" and "get_vendor".

2/26/2020 2:26 PM Andy
- Enhanced "get_user" to return active and locked.

3/3/2020 2:35 PM William
- Enhanced to change column "Additional Description" to "Marketplace Description".

3/5/2020 11:07 AM Andy
- Enhance API "get_sku" to removed "Internal Description" and "Model".

3/11/2020 3:50 PM Andy
- Enhanced to have changes_row_index for sku, category, user, brand and vendor.

3/23/2020 12:56 PM William
- Enhanced to capture "Marketplace Invoice" for "DO" column "mkt_inv_no".

4/8/2020 3:31 PM Andy
- Remove sku photo from "get_sku".
- Enhanced to have "last_stock_changed" in "get_sku".

8/24/2020 5:48 PM William
- Enhance get_sku to show sku item photo in photo_list.

12/4/2020 10:21 AM Andy
- Added "hq_stock_qty" and "hq_uncheckout_do_qty" in api "get_sku".

02/2/2021 5:26 PM Rayleen
- Added "packing_uom_fraction" and "family_stock" in api "get_sku".
- Added  function get_family_stock() to calculate parent/child family stock

02/4/2021 3:43 PM Rayleen
- Fix bug on get_family_stock() function not returning correct stock value - wrong return index

02/18/2021 3:40 PM Rayleen
- Fix bug on get_family_stock() function - should not change negative stock to zero

*/
include("include/common.php");
include("include/price_checker.include.php");
ini_set('memory_limit', '1024M');
set_time_limit(0);

class ARMS_MARKETPLACE_API {
	var $is_debug = 0;
	var $folder = "attch/api.arms.marketplace";
	var $log_folder = "attch/api.arms.marketplace/logs";
	
	var $rcv_data = array();
	var $settings = array();
	var $call_method = '';
	var $http_method = '';
	var $shipping_item_sid = 0;
	
	// Error List
	var $err_list = array(
		"config_not_set" => "No Configuration Found",
		"config_error" => "Configuration Error",
		"invalid_api_method" => "Invalid API Method.",
		"invalid_http_method" => "Invalid HTTP Method.",
		"authen_failed" => "Authentication Failed.",
		"invalid_branch" => "Invalid Branch %s.",
		"branch_is_inactive" => "Branch %s are inactive.",
		"data_not_found" => "No %s were found.",
		"invalid_data" => "Invalid Data '%s'.",
		"unknown_error" => "Unknown Error",
		"not_marketplace_sku" => "SKU ITEM ID '%s' is not marketplace sku.",
		"data_duplicated" => "'%s' '%s' is duplicated.",
		"item_id_duplicated" => "'do_sequence' '%s' is duplicated.",
		"do_amount_not_match" => "'do_sequence' '%s' total_do_amount [%s] not match with items total amount + shipping fee - discount = [%s].",
		"do_qty_not_match" => "'do_sequence' '%s' total_do_qty not match with items total qty.",
		"order_do_already_created" => "'do_sequence' '%s' DO already created.",
		"order_do_already_cancelled" => "'do_sequence' '%s' DO already cancelled.",
		"invalid_date_format" => "'%s' is not a Valid Date Format.",
		"order_amount_not_match" => "Order total_amount not match with DO Total Amount.",
		"order_qty_not_match" => "Order total_qty not match with DO Total Qty.",
		"order_already_completed" => "Cannot update Order due to its already marked as completed.",
		"order_items_not_found" => "Order Items with SKU ITEM ID [%s] Not Found in ARMS DO.",
		"order_items_not_match" => "Order Items with SKU ITEM ID [%s] Not Matched with Item ID [%s].",
		"shipping_items_not_found" => "Shipping Item Not Found in ARMS DO.",
		"arms_do_not_allow_to_edit" => "ARMS DO ID [%s] status is not allow to edit.",
		"arms_do_inactive_not_allow_edit" => "ARMS DO ID [%s] on 'Inactive' status is not allow to edit.",
		"arms_do_rejected_not_allow_edit" => "ARMS DO ID [%s] on 'Rejected' status is not allow to edit.",
		"arms_do_cancelled_not_allow_edit" => "ARMS DO ID [%s] on 'Cancelled' status is not allow to edit.",
		"arms_do_terminated_not_allow_edit" => "ARMS DO ID [%s] on 'Terminated' status is not allow to edit.",
		"arms_do_approved_not_allow_edit" => "ARMS DO ID [%s] on 'Approved' status is not allow to edit.",
		"arms_do_checkout_not_allow_edit" => "ARMS DO ID [%s] on 'Checkout' status is not allow to edit.",
		"arms_login_failed" => "Login Failed.",
	);
	
	var $valid_marketplace_sku_list = array();
	var $marketplace_mprice_list = array('lazada_price', 'shopee_price');
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
		global $con;
		
		// Normal Settings
		$this->settings = array();
		$con->sql_query("select * from marketplace_settings");
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
			if(!$this->shipping_item_sid){
				$this->shipping_item_code = $shipping_item_code;
			}
		}
	}
	
	private function check_api_key(){
		global $con, $config;
		
		$X_ACCESS_TOKEN = isset($this->all_header['X_ACCESS_TOKEN']) ? trim($this->all_header['X_ACCESS_TOKEN']) : '';

		$this->put_log("Checking ACCESS TOKEN: ".$X_ACCESS_TOKEN);
		
		// Must have config
		if(!$config['arms_marketplace_settings']){
			$this->error_die("config_not_set", $this->err_list['config_not_set']);
		}
		
		// Check Access Token
		if(!$config['arms_marketplace_settings']['access_token']){
			$this->error_die("config_error", $this->err_list['config_error']);
		}else{
			if(md5($config['arms_marketplace_settings']['access_token']) != strtolower($X_ACCESS_TOKEN)){
				$this->error_die("authen_failed", $this->err_list['authen_failed']);
			}
		}
		
		// Check Branch Code
		if(!$config['arms_marketplace_settings']['branch_code']){
			$this->error_die("invalid_branch", $this->err_list['invalid_branch']);
		}else{
			// select the branch, must active
			$branch_code = strtoupper($config['arms_marketplace_settings']['branch_code']);
			$q1 = $con->sql_query("select * from branch where code = ".ms($branch_code));
			$this->branch_info = $con->sql_fetchassoc($q1);

			if($con->sql_numrows($q1) == 0){ // return error msg if branch couldn't be found
				$this->error_die("invalid_branch", sprintf($this->err_list['invalid_branch'], $branch_code));
			}elseif(!$this->branch_info['active']){ // return error msg if it is inactive branch
				$this->error_die("branch_is_inactive", sprintf($this->err_list['branch_is_inactive'], $branch_code));
			}else{ // store the branch id
				$this->branch_id = $this->branch_info['id'];
			}
			$con->sql_freeresult($q1);
			
			unset($branch_code);
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
	
	private function is_valid_marketplace_sku($sid){
		global $con;
		
		$sid = mi($sid);
		if($sid<=0)	return false;
		
		// Check cache
		if(!isset($this->valid_marketplace_sku_list[$sid])){
			// Select from database
			$con->sql_query("select sku_item_id from marketplace_sku_items where sku_item_id=$sid and active=1");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$this->valid_marketplace_sku_list[$sid] = $tmp ? true : false;
		}		
		
		return $this->valid_marketplace_sku_list[$sid];
	}
	
	private function get_branch(){
		$ret = array();
		if($this->branch_info){
			// set the fields which we needed
			$branch_required_fields = array("code", "company_no", "description", "address", "contact_person", "contact_email", "phone_1", "active");
			$alt_field_key = array('code' => 'branch_code', 'address' => 'address1', 'company_no' => 'company_registration_number', 'phone_1' => 'contact_no');
			
			// Construct Data to return
			$ret['result'] = 1;
			foreach($branch_required_fields as $field){
			$read_field = isset($alt_field_key[$field]) ? $alt_field_key[$field] : $field;
				$ret['branch_data'][$read_field] = '';
				if(isset($this->branch_info[$field])){
					$ret['branch_data'][$read_field] = $this->branch_info[$field];
				}
			}
			unset($branch_required_fields);
		}else{
			$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "Branch"));
		}
		unset($b_info);
		
		// Return Data				
		$this->respond_data($ret);
	}
	
	private function get_category(){
		global $con;
		
		$filter = array();
		
		if($this->rcv_data['category_id_list']){ // it is filter by category id list
			$filter[] = "c.id in (".join(",", $this->rcv_data['category_id_list']).")";
		}
		
		// get limit by certain records
		if(!$this->rcv_data['category_id_list'] && $this->rcv_data['start_from'] >= 0 && $this->rcv_data['limit_count'] > 0){
			$limit = "limit ".mi($this->rcv_data['start_from']).", ".$this->rcv_data['limit_count'];
		}
		
		$min_changes_row_index = mi($this->rcv_data['min_changes_row_index']);
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}

		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Construct Data to return
		$ret = $category_list = array();
		$sql = "select c.id as category_id, c.code, c.description, c.level, c.root_id as parent_category_id, c.active, ifnull(tmp.row_index,0) as changes_row_index
				from category c
				left join tmp_trigger_log tmp on tmp.tablename='category' and tmp.id=c.id
				$str_filter
				order by changes_row_index
				$limit";

		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$category_list[$r['category_id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($category_list){
			// Construct Data to return
			$ret['result'] = 1;
			$ret['category_data'] = $category_list;
		}else{
			$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "Category"));
		}
		unset($category_list);
		
		// Return Data				
		$this->respond_data($ret);
	}
	
	private function get_category_count(){
		global $con;
		
		$filter = array();
		
		if($this->rcv_data['category_id_list']){ // it is filter by category id list
			$filter[] = "c.id in (".join(",", $this->rcv_data['category_id_list']).")";
		}
		
		$min_changes_row_index = mi($this->rcv_data['min_changes_row_index']);
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Count all categories
		$this->put_log("Checking Category Count.");	
		$q1 = $con->sql_query("select count(*) as count
							   from category c
							   left join tmp_trigger_log tmp on tmp.tablename='category' and tmp.id=c.id
							   $str_filter");
		$tmp = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		$ret['count'] = $tmp['count'];
		
		// Return Data
		$this->respond_data($ret);
	}
	
	private function get_sku(){
		global $con, $appCore;
		
		$filter = array();
		$promo_required_fields = array('default_price', 'non_member_price', 'non_member_discount', 'non_member_date_from', 'non_member_date_to', 'time_from', 'time_to');
		$alt_field_key = array('time_from' => 'non_member_time_from', 'time_to' => 'non_member_time_to');
		
		// check if the request is filtering sku item id or barcode list
		if($this->rcv_data['sku_item_id_list']){ // it is filter by sku item id list
			$filter[] = "mpsi.sku_item_id in (".join(",", $this->rcv_data['sku_item_id_list']).")";
		}else{ // do nothing
			//if($this->is_debug) $filter[] = "mpsi.sku_item_id = 509734";
		}
		
		// get limit by certain records
		if(!$this->rcv_data['sku_item_id_list'] && $this->rcv_data['start_from'] >= 0 && $this->rcv_data['limit_count'] > 0){
			$limit = "limit ".mi($this->rcv_data['start_from']).", ".$this->rcv_data['limit_count'];
		}
		
		////////// Filter using OR //////////////
		$filter_or = array();
		$min_changes_row_index = mi($this->rcv_data['min_changes_row_index']);
		if($min_changes_row_index>0){
			$filter_or[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		$last_stock_changed = trim($this->rcv_data['last_stock_changed']);
		if($last_stock_changed){
			$filter_or[] = "sic.last_update>".ms($last_stock_changed);
		}
		
		if($filter_or)	$filter[] = "(".join(' or ', $filter_or).")";
		////////// End of Filter using OR //////////////
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		unset($filter);

		$tbl_vsh = "vendor_sku_history_b".$this->branch_id;
		
		// currently doesn't have:
		$sql = "select si.id, si.sku_id, si.sku_item_code, si.sku_apply_items_id, si.mcode, si.artno, si.link_code, si.description, si.marketplace_description,
				si.receipt_description, si.size, si.color, si.active as si_active, si.is_parent, b.id as brand_id, mpsi.active as si_mktplace_active, round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(p.price,si.selling_price)/(100+output_gst.rate)*100,ifnull(p.price,si.selling_price)),2) as selling_price_before_tax, round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(p.price,si.selling_price),ifnull(p.price,si.selling_price)*(100+output_gst.rate)/100),2) as selling_price_inclusive_tax,
				round(ifnull(p.price, si.selling_price), 2) as selling_price, ifnull(sic.grn_cost, si.cost_price) as cost_price, si.lastupdate as last_modified, 
				sic.qty as stock_qty, if(sic.changed=1 or sic.changed is null,0,1) as is_stock_updated, c.id as category_id, c.description as category_description, si.weight_kg as weight, si.length, si.height, si.width,
				(select group_concat(simp.type,'=',simp.price SEPARATOR ';') from sku_items_mprice simp where simp.branch_id=".mi($this->branch_id)." and simp.sku_item_id = si.id) as mprice_list, sku.vendor_id, ifnull(tmp.row_index,0) as changes_row_index, sic.last_update as last_stock_changed, sic_hq.qty as hq_stock_qty, uom.fraction as packing_uom_fraction
				from marketplace_sku_items mpsi
				join sku_items si on si.id = mpsi.sku_item_id
				left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = ".mi($this->branch_id)."
				left join sku_items_cost sic_hq on sic_hq.sku_item_id = si.id and sic_hq.branch_id = 1
				left join sku_items_price p on p.sku_item_id = si.id and p.branch_id = ".mi($this->branch_id)."
				left join sku on sku.id=si.sku_id
				left join category c on c.id=sku.category_id
				left join category_cache cc on cc.category_id=sku.category_id
				left join gst input_gst on input_gst.id=if(if(si.input_tax<0,sku.mst_input_tax,si.input_tax)<0,cc.input_tax,if(si.input_tax<0,sku.mst_input_tax,si.input_tax))
				left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
				left join brand b on b.id = sku.brand_id
				left join tmp_trigger_log tmp on tmp.tablename='sku_items' and tmp.id=si.id
				left join uom on si.packing_uom_id=uom.id
				$str_filter
				order by changes_row_index
				$limit";
		// print $sql;exit;
		
		$q1 = $con->sql_query($sql);
		
		while($si = $con->sql_fetchassoc($q1)){
			$r = $si;
			
			// Below fields no need
			unset($r['selling_price_before_tax'], $r['selling_price_inclusive_tax'], $r['selling_price']);
			
			// get uncheckout do qty
			$prms = $do_info = array();
			$prms['sid'] = $r['id'];
			$prms['bid'] = $this->branch_id;
			$do_info = $this->load_uncheckout_do($prms);
			$r['uncheckout_do_qty'] = mf($do_info['uncheckout_do_qty']);
			unset($prms, $do_info);
			
			// Get HQ Uncheckout DO Qty
			$prms = $do_info = array();
			$prms['sid'] = $r['id'];
			$prms['bid'] = 1;
			$do_info = $this->load_uncheckout_do($prms);
			$r['hq_uncheckout_do_qty'] = mf($do_info['uncheckout_do_qty']);
			unset($prms, $do_info);
			
			// check if the additional desc was serialised, unserialize it and join it with \n
			/*if($r['additional_description']){
				$additional_description = unserialize($r['additional_description']);
				$r['additional_description'] = join("\n", $additional_description);
				unset($additional_description);
			}*/
			
			//get promotion photo
			$promo_photo = $appCore->skuManager->getSKUItemPromoPhotos($si['id']);
			
			// get sku apply photo, photo and promotion photo
			$apply_photo_list = get_sku_apply_item_photos($si['sku_apply_items_id']);
			
			// get sku photo
			$photo_list = get_sku_item_photos($si['id'], $si);
			
			$r['photo_list'] = array();
			if(!$apply_photo_list) $apply_photo_list = array();
			if(!$photo_list)	$photo_list = array();
			if(!$promo_photo)  $promo_photo = array();
			$all_photo_list = array_merge($promo_photo, $apply_photo_list, $photo_list);
			if($all_photo_list){
				foreach($all_photo_list as $photo_path){
					$photo_details = array();
					$photo_details['abs_path'] = $photo_path;
					if(file_exists($photo_path)){
						$photo_details['last_update'] = date("Y-m-d H:i:s", filemtime($photo_path));
						$photo_details['name'] = basename($photo_path);			
						$r['photo_list'][] = $photo_details;
					}
					unset($photo_details);
				}
			}
			unset($all_photo_list);
			
			// get promotion
			$params = array();
			$params['code'] = $r['sku_item_code'];
			$params['branch_id'] = $this->branch_id;
			$promo = check_price($params);
			unset($params);
			
			foreach($promo_required_fields as $field){
				$read_field = isset($alt_field_key[$field]) ? $alt_field_key[$field] : $field;
				$r[$read_field] = '';
				if(isset($promo[$field])){
					$r[$read_field] = $promo[$field];
				}
			}
			
			unset($promo);
			
			// mprice_list
			
			if($r['mprice_list']){
				$tmp_mprice_list = explode(';', $r['mprice_list']);
				$r['mprice_list'] = array();
				foreach($tmp_mprice_list as $str_mprice){
					list($mprice_type, $mprice) = explode("=", $str_mprice);
					
					if(!in_array($mprice_type, $this->marketplace_mprice_list))	continue;	// this mprice no use for marketplace
					$r['mprice_list'][$mprice_type] = round($mprice, 2);
				}
			}
			if(!$r['mprice_list'])	$r['mprice_list'] = array();
			
			// Get Latest Vendor ID
			$q_vsh = $con->sql_query("select vendor_id from $tbl_vsh where sku_item_id=".mi($si['id'])." order by to_date desc limit 1");
			$vsh = $con->sql_fetchassoc($q_vsh);
			$con->sql_freeresult($q_vsh);
			if($vsh){
				if($vsh['vendor_id']>0 && $si['vendor_id'] !=$vsh['vendor_id']){
					$si['vendor_id'] =$vsh['vendor_id'];
				}
			}

			// get family stock 
			$family_stock = $this->get_family_stock($r['sku_id'], $r['id']);
			if($family_stock){
				$r['family_stock'] = $family_stock;
			}

			$si_list[$r['id']] = $r;
			//unset($apply_photo_list, $promo_photo_list);

		}
		$con->sql_freeresult($q1);
		
		$ret = array();
		if($si_list){
			// Construct Data to return
			$ret['result'] = 1;
			$ret['sku_data'] = $si_list;
		}else{
			$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "SKU"));
		}
		
		// Return Data				
		$this->respond_data($ret);
	}
	
	private function get_sku_count(){
		global $con;
		
		$filter = array();
		
		if($this->rcv_data['sku_item_id_list']){ // it is filter by sku item id list
			$filter[] = "mpsi.sku_item_id in (".join(",", $this->rcv_data['sku_item_id_list']).")";
		}
		
		////////// Filter using OR //////////////
		$filter_or = array();
		$min_changes_row_index = mi($this->rcv_data['min_changes_row_index']);
		if($min_changes_row_index>0){
			$filter_or[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		$last_stock_changed = trim($this->rcv_data['last_stock_changed']);
		if($last_stock_changed){
			$filter_or[] = "sic.last_update>".ms($last_stock_changed);
		}
		
		if($filter_or)	$filter[] = "(".join(' or ', $filter_or).")";
		////////// End of Filter using OR //////////////
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Count all sku
		$this->put_log("Checking SKU Count.");	
		$q1 = $con->sql_query("select count(*) as c 
							   from marketplace_sku_items mpsi
							   left join tmp_trigger_log tmp on tmp.tablename='sku_items' and tmp.id=mpsi.sku_item_id
							   left join sku_items_cost sic on sic.sku_item_id = mpsi.sku_item_id and sic.branch_id = ".mi($this->branch_id)."
							   $str_filter");
		$tmp = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		$ret['count'] = $tmp['c'];
		
		// Return Data
		$this->respond_data($ret);
	}
	
	private function load_uncheckout_do($prms=array()){
		global $con;
		
		if(!$prms['bid'] || !$prms['sid']) return;
		
		$q2 = $con->sql_query($sql="select di.*, do.deliver_branch, u.fraction as uom_fraction
							   from do 
							   left join do_items di on di.do_id = do.id and di.branch_id = do.branch_id
							   left join uom u on u.id = di.uom_id
							   where do.active=1 and do.status in (0,1) and do.checkout=0
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
	
	private function get_brand(){
		global $con;
		
		$filter = array();
		
		if($this->rcv_data['brand_id_list']){ // it is filter by category id list
			$filter[] = "b.id in (".join(",", $this->rcv_data['brand_id_list']).")";
		}
		
		// get limit by certain records
		if(!$this->rcv_data['brand_id_list'] && $this->rcv_data['start_from'] >= 0 && $this->rcv_data['limit_count'] > 0){
			$limit = "limit ".mi($this->rcv_data['start_from']).", ".$this->rcv_data['limit_count'];
		}
		
		$min_changes_row_index = mi($this->rcv_data['min_changes_row_index']);
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}

		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Construct Data to return
		$ret = $brand_list = array();		
		$sql = "select b.id as brand_id, b.code, b.description, b.active, ifnull(tmp.row_index,0) as changes_row_index
				from marketplace_sku_items mpsi
				join sku_items si on si.id = mpsi.sku_item_id
				join sku on sku.id = si.sku_id
				join brand b on b.id = sku.brand_id
				left join tmp_trigger_log tmp on tmp.tablename='brand' and tmp.id=b.id
				$str_filter
				group by brand_id
				order by changes_row_index
				$limit";

		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$brand_list[$r['brand_id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($brand_list){
			// Construct Data to return
			$ret['result'] = 1;
			$ret['brand_data'] = $brand_list;
		}else{
			$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "Brand"));
		}
		unset($brand_list);
		
		// Return Data				
		$this->respond_data($ret);
	}
	
	private function get_brand_count(){
		global $con;
		
		$filter = $brand_list = array();
		
		if($this->rcv_data['brand_id_list']){ // it is filter by category id list
			$filter[] = "b.id in (".join(",", $this->rcv_data['brand_id_list']).")";
		}
		
		$min_changes_row_index = mi($this->rcv_data['min_changes_row_index']);
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		$str_filter = '';
		$brand_count = 0;
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Count all categories
		$this->put_log("Checking Brand Count.");	
		$q1 = $con->sql_query("select b.id
							   from brand b
							   join sku on sku.brand_id = b.id
							   join sku_items si on si.sku_id = sku.id
							   join marketplace_sku_items mpsi on mpsi.sku_item_id = si.id
							   left join tmp_trigger_log tmp on tmp.tablename='brand' and tmp.id=b.id
							   $str_filter
							   group by b.id");
		while($r = $con->sql_fetchassoc($q1)){
			if(!$brand_list[$r['id']]){
				$brand_list[$r['id']] = 1;
				$brand_count++;
			}
		}
		$con->sql_freeresult($q1);
		unset($brand_list);
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		$ret['count'] = $brand_count;
		
		// Return Data
		$this->respond_data($ret);
	}
	
	private function get_user(){
		global $con;
		
		$filter = array();
		
		if($this->rcv_data['user_id_list']){ // it is filter by category id list
			$filter[] = "u.id in (".join(",", $this->rcv_data['user_id_list']).")";
		}
		
		// get limit by certain records
		if(!$this->rcv_data['user_id_list'] && $this->rcv_data['start_from'] >= 0 && $this->rcv_data['limit_count'] > 0){
			$limit = "limit ".mi($this->rcv_data['start_from']).", ".$this->rcv_data['limit_count'];
		}
		
		$min_changes_row_index = mi($this->rcv_data['min_changes_row_index']);
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}

		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Construct Data to return
		$ret = $user_list = array();		
		$sql = "select u.id as user_id, u.l as login_id, u.p as password, u.u as username, u.level, u.fullname, u.email, u.is_arms_user, u.active, u.locked, ifnull(tmp.row_index,0) as changes_row_index
				from user u
				join user_privilege up on up.user_id = u.id and up.branch_id = ".mi($this->branch_id)." and up.privilege_code = 'MARKETPLACE_LOGIN'
				left join tmp_trigger_log tmp on tmp.tablename='user' and tmp.id=u.id
				$str_filter
				order by u.id
				$limit";

		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$user_list[$r['user_id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($user_list){
			// Construct Data to return
			$ret['result'] = 1;
			$ret['user_data'] = $user_list;
		}else{
			$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "User"));
		}
		unset($user_list);
		
		// Return Data				
		$this->respond_data($ret);
	}
	
	private function get_user_count(){
		global $con;
		
		$filter = array();
		
		if($this->rcv_data['user_id_list']){ // it is filter by vendor id
			$filter[] = "u.id in (".join(",", $this->rcv_data['user_id_list']).")";
		}
		
		$min_changes_row_index = mi($this->rcv_data['min_changes_row_index']);
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Count all sku
		$this->put_log("Checking User Count.");	
		$q1 = $con->sql_query("select count(*) as c 
							   from user u
							   join user_privilege up on up.user_id = u.id and up.branch_id = ".mi($this->branch_id)." and up.privilege_code = 'MARKETPLACE_LOGIN'
							   left join tmp_trigger_log tmp on tmp.tablename='user' and tmp.id=u.id
							   $str_filter");
		$tmp = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		$ret['count'] = $tmp['c'];
		
		// Return Data
		$this->respond_data($ret);
	}
	
	private function create_order(){
		global $con, $appCore;
		
		$order = array();
		
		// Order_no Not Found
		$order['order_no'] = trim($this->rcv_data['order_no']);
		if(!$order['order_no'])	$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "order_no"));
		
		// Order Date
		$order['order_date'] = trim($this->rcv_data['order_date']);
		if(!$order['order_date'])	$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "order_date"));
		if(!$appCore->isValidDateFormat($order['order_date']))	$this->error_die("invalid_date_format", sprintf($this->err_list['invalid_date_format'], "order_date"));
		
		// Customer Name
		$order['cust_name'] = trim($this->rcv_data['cust_name']);
		if(!$order['cust_name'])	$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "cust_name"));
		
		// Customer Address
		$order['cust_address'] = trim($this->rcv_data['cust_address']);
		if(!$order['cust_address'])	$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "cust_address"));
		
		// Total Amount cannot zero or negative
		$order['total_amount'] = round(mf($this->rcv_data['total_amount']), 2);
		if($order['total_amount']<0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "total_amount"));
		
		// Total Qty cannot zero or negative
		$order['total_qty'] = mi($this->rcv_data['total_qty']);
		if($order['total_qty']<=0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "total_qty"));
		
		// Check DO Array
		if(!$this->rcv_data['do_data'] || !is_array($this->rcv_data['do_data']))	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "do_data"));
		
		$do_list = array();
		$total_amount = $total_qty = $total_disc_amt = 0;
		foreach($this->rcv_data['do_data'] as $index => $tmp_do_data){
			$do_data = array();
			$total_do_amount = $total_do_qty = 0;
			
			// DO Sequence
			$do_sequence = $do_data['do_sequence'] = mi($tmp_do_data['do_sequence']);
			if($do_data['do_sequence']<=0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "do_sequence"));
			
			if(isset($do_list[$do_sequence])){
				// DO Sequence already used
				$this->error_die("data_duplicated", sprintf($this->err_list['data_duplicated'], 'do_sequence', $do_sequence));
			}
			
			// Shipping Fee
			$do_data['shipping_fee'] = round(mf($tmp_do_data['shipping_fee']), 2);
			$total_do_amount += $do_data['shipping_fee'];
			
			// Tracking Code
			$do_data['tracking_code'] = trim($tmp_do_data['tracking_code']);
			
			// Shipping Method
			$do_data['shipping_provider'] = trim($tmp_do_data['shipping_provider']);
			if(!$do_data['shipping_provider'])	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "shipping_provider"));
			
			// DO Amount
			$do_data['total_do_amount'] = round(mf($tmp_do_data['total_do_amount']), 2);
			if($do_data['total_do_amount']<0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "total_do_amount"));
			
			// DO Qty
			$do_data['total_do_qty'] = mi($tmp_do_data['total_do_qty']);
			if($do_data['total_do_qty']<=0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "total_do_qty"));
			
			// DO Discount
			$do_data['total_do_discount_amt'] = round(mf($tmp_do_data['total_do_discount_amt']), 2);
			if($do_data['total_do_discount_amt']<0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "total_do_discount_amt"));
			
			//Marketplace invoice no
			$do_data['mkt_invoice_no'] = $tmp_do_data['mkt_invoice_no'];
			
			// Order Items
			if(!$tmp_do_data['order_items'] || !is_array($tmp_do_data['order_items']))	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "order_items"));
			$do_data['do_items'] = array();
			
			foreach($tmp_do_data['order_items'] as $tmp_order_items){
				// Item ID
				$item_id = mi($tmp_order_items['item_id']);
				if($item_id<=0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "item_id"));
				
				if(isset($do_data['do_items'][$item_id]))	$this->error_die("data_duplicated", sprintf($this->err_list['data_duplicated'], 'item_id', $item_id));
				$do_data['do_items'][$item_id]['item_id'] = $item_id;
				
				// Qty
				$do_data['do_items'][$item_id]['qty'] = mi($tmp_order_items['qty']);
				if($do_data['do_items'][$item_id]['qty'] <= 0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "qty"));
				
				// Unit Price
				$do_data['do_items'][$item_id]['unit_price'] = round(mf($tmp_order_items['unit_price']), 2);
				if($do_data['do_items'][$item_id]['unit_price'] <= 0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "unit_price"));
				
				// SKU Item ID
				$do_data['do_items'][$item_id]['sku_item_id'] = mi($tmp_order_items['sku_item_id']);
				if($do_data['do_items'][$item_id]['sku_item_id'] <= 0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "sku_item_id"));
				
				if(!$this->is_valid_marketplace_sku($do_data['do_items'][$item_id]['sku_item_id'])){
					$this->error_die("not_marketplace_sku", sprintf($this->err_list['not_marketplace_sku'], $do_data['do_items'][$item_id]['sku_item_id']));
				}
				
				// Item Total Amount
				$do_amount = round($do_data['do_items'][$item_id]['qty'] * $do_data['do_items'][$item_id]['unit_price'], 2);
				$total_do_amount += $do_amount;
				$total_do_qty += $do_data['do_items'][$item_id]['qty'];
			}
			
			// Deduct Discount
			$total_do_amount -= $do_data['total_do_discount_amt'];
			$total_do_amount = round($total_do_amount, 2);
			
			// DO Amount Not Tally
			if($do_data['total_do_amount'] != $total_do_amount)	$this->error_die("do_amount_not_match", sprintf($this->err_list['do_amount_not_match'], $do_sequence, $do_data['total_do_amount'], $total_do_amount));
			
			// DO Qty Not Tally
			if($do_data['total_do_qty'] != $total_do_qty)	$this->error_die("do_qty_not_match", sprintf($this->err_list['do_qty_not_match'], $do_sequence));
			
			$do_list[$do_sequence] = $do_data;
			
			$total_amount += $total_do_amount;
			$total_qty += $total_do_qty;
			$total_disc_amt += $do_data['total_do_discount_amt'];
		}
		
		// Order Total Amount Not Match
		if($order['total_amount'] != $total_amount)	$this->error_die("order_amount_not_match", $this->err_list['order_amount_not_match']);
		// Order Total Qty Not Match
		if($order['total_qty'] != $total_qty)	$this->error_die("order_qty_not_match", $this->err_list['order_qty_not_match']);
		
		//print_r($order);
		//print_r($do_list);exit;
		
		// Begin Transaction
		$con->sql_begin_transaction();
			
		// Insert Order Record
		$upd = array();
		$upd['order_no'] = $order['order_no'];
		$upd['order_date'] = $order['order_date'];
		$upd['cust_name'] = $order['cust_name'];
		$upd['cust_address'] = $order['cust_address'];
		$upd['total_amount'] = $order['total_amount'];
		$upd['total_qty'] = $order['total_qty'];
		$upd['marketplace_name'] = lcase($this->rcv_data['marketplace_name']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['api_data'] = json_encode($this->rcv_data);
		
		// Check current record
		$con->sql_query("select * from marketplace_order where order_no=".ms($order['order_no']));
		$marketplace_order = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($marketplace_order){
			// Already completed
			if($marketplace_order['completed'])	$this->error_die("order_already_completed", $this->err_list['order_already_completed']);
			
			$marketplace_order_id = mi($marketplace_order['id']);
			
			// Update Existing Order
			$con->sql_query("update marketplace_order set ".mysql_update_by_field($upd)." where id=$marketplace_order_id");
		}else{
			// Add New Order
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into marketplace_order ".mysql_insert_by_field($upd));
			$marketplace_order_id = $con->sql_nextid();
			$is_new = true;
		}
		
		if(!$is_new){
			// Set all Order become inactive
			$con->sql_query("update marketplace_order_do set active=0, last_update=CURRENT_TIMESTAMP where marketplace_order_id=$marketplace_order_id");
		}
		
		foreach($do_list as $do_sequence => $do_data){	// Loop DO
			$do_sequence = mi($do_sequence);
			
			// Check DO Exists
			$con->sql_query("select * from marketplace_order_do where marketplace_order_id=$marketplace_order_id and do_sequence=$do_sequence");
			$order_do = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$upd2 = array();
			$upd2['shipping_fee'] = $do_data['shipping_fee'];
			$upd2['tracking_code'] = $do_data['tracking_code'];
			$upd2['shipping_provider'] = $do_data['shipping_provider'];
			$upd2['total_do_amount'] = $do_data['total_do_amount'];
			$upd2['total_do_qty'] = $do_data['total_do_qty'];
			$upd2['discount'] = $do_data['total_do_discount_amt'];
			$upd2['mkt_inv_no'] = $do_data['mkt_invoice_no'];
			$upd2['active'] = 1;
			$upd2['last_update'] = 'CURRENT_TIMESTAMP';
			
			if($order_do){
				// Existing
				$marketplace_order_do_id = mi($order_do['id']);
				
				if($order_do['do_id']>0){	// DO already created
					$this->error_die("order_do_already_created", sprintf($this->err_list['order_do_already_created'], $do_sequence));
				}
				
				// Update DO Record
				$con->sql_query("update marketplace_order_do set ".mysql_update_by_field($upd2)." where id=$marketplace_order_do_id");
			}else{
				// NEW
				$upd2['marketplace_order_id'] = $marketplace_order_id;
				$upd2['do_sequence'] = $do_sequence;
				$upd2['branch_id'] = $this->branch_id;
				$upd2['added'] = 'CURRENT_TIMESTAMP';
				
				// Insert DO Record
				$con->sql_query("insert into marketplace_order_do ".mysql_insert_by_field($upd2));
				$marketplace_order_do_id = $con->sql_nextid();
			}
			
			// Record down ID
			//$do_list[$do_sequence]['id'] = $marketplace_order_do_id;
			
			if(!$is_new){
				// Set all Order Items become inactive
				$con->sql_query("update marketplace_order_do_items set active=0, last_update=CURRENT_TIMESTAMP where marketplace_order_id=$marketplace_order_id and do_sequence=$do_sequence");
			}
				
			// Insert DO Items Record
			foreach($do_data['do_items'] as $item_id => $do_items){
				$item_id = mi($item_id);
				
				$con->sql_query("select * from marketplace_order_do_items where marketplace_order_id=$marketplace_order_id and do_sequence=$do_sequence and item_id=$item_id");
				$order_do_items = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				$upd3 = array();
				$upd3['qty'] = $do_items['qty'];
				$upd3['unit_price'] = $do_items['unit_price'];
				$upd3['sku_item_id'] = $do_items['sku_item_id'];
				$upd3['active'] = 1;
				$upd3['last_update'] = 'CURRENT_TIMESTAMP';
				if($order_do_items){
					// Existing
					$marketplace_order_do_items_id = $order_do_items['id'];
					
					// Update DO Items
					$con->sql_query("update marketplace_order_do_items set ".mysql_update_by_field($upd3)." where id=$marketplace_order_do_items_id");
				}else{
					// New
					$upd3['marketplace_order_id'] = $marketplace_order_id;
					$upd3['do_sequence'] = $do_sequence;
					$upd3['item_id'] = $item_id;
					$upd3['added'] = 'CURRENT_TIMESTAMP';
					
					// Insert DO Items
					$con->sql_query("insert into marketplace_order_do_items ".mysql_insert_by_field($upd3));
					$marketplace_order_do_items_id = $con->sql_nextid();
				}
				
				// Record down ID
				//$do_list[$do_sequence]['do_items'][$item_id]['id'] = $marketplace_order_do_items_id;
			}
		}
		
		// Commit Transaction
		$con->sql_commit();
		
		//print_r($do_list);exit;
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		// Create DO
		$result = $appCore->doManager->createMarketplaceDO($marketplace_order_id);
		if(!$result['do_info_list']){
			$err_msg = $result['error'] ? $result['error'] : $this->err_list['unknown_error'];
			$this->error_die("unknown_error", $err_msg);
		}
		// Commit Transaction
		$con->sql_commit();
		
		$ret = array();
		$ret['result'] = 1;
		$ret['do_list'] = array();
		foreach($result['do_info_list'] as $do_id => $r){
			$ret['do_list'][$do_id]['do_sequence'] = $r['do_sequence'];
			$ret['do_list'][$do_id]['do_id'] = $r['do_id'];
		}
		
		log_br(1, 'DELIVERY ORDER', 0, "Marketplace Order Created: (Order No#".$order['order_no'].", DO ID:".join(',', array_keys($result['do_info_list'])).")", $this->branch_id);
		
		// Return Data				
		$this->respond_data($ret);
	}
	
	function cancel_order_items(){
		global $con, $appCore;
		
		// Order_no Not Found
		$order_no = trim($this->rcv_data['order_no']);
		if(!$order_no)	$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "order_no"));
		
		$upd = array();
		$upd['order_no'] = $order_no;
		$upd['api_data'] = json_encode($this->rcv_data);
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into marketplace_order_cancel ".mysql_insert_by_field($upd));
		$cancel_id = $con->sql_nextid();
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		// Get Order
		$con->sql_query("select * from marketplace_order where order_no=".ms($order_no)." for update");
		$order = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Order Not Found
		if(!$order)	$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "Order"));
		$marketplace_order_id = mi($order['id']);
		
		// Check DO Array
		if(!$this->rcv_data['do_data'] || !is_array($this->rcv_data['do_data']))	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "do_data"));
		
		$do_list = array();
		foreach($this->rcv_data['do_data'] as $index => $tmp_do_data){
			$do_data = array();
			
			// DO Sequence
			$do_sequence = $do_data['do_sequence'] = mi($tmp_do_data['do_sequence']);
			if($do_data['do_sequence']<=0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "do_sequence"));
			
			if(isset($do_list[$do_sequence])){
				// DO Sequence already used
				$this->error_die("data_duplicated", sprintf($this->err_list['data_duplicated'], 'do_sequence', $do_sequence));
			}
			
			// Get Order DO
			$con->sql_query("select * from marketplace_order_do where marketplace_order_id=$marketplace_order_id and do_sequence=$do_sequence for update");
			$order_do = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			// Order DO Not Found
			if(!$order_do)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "do_sequence"));
			if(!$order_do['active'])	$this->error_die("order_do_already_cancelled", sprintf($this->err_list['order_do_already_cancelled'], $do_sequence));
				
			$do_data['db_obj'] = $order_do;
			
			if($order_do['do_id']>0){
				$con->sql_query("select * from do where branch_id=".mi($order_do['branch_id'])." and id=".mi($order_do['do_id']));
				$do = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!($do['active']==1 && $do['status']==0 && $do['approved']==0 && $do['checkout']==0)){
					if($do['active'] == 0){
						$this->error_die("arms_do_inactive_not_allow_edit", sprintf($this->err_list['arms_do_inactive_not_allow_edit'], $order_do['do_id']));
					}elseif($do['status'] == 2 && $do['approved'] == 0 && $do['active'] == 1){
						$this->error_die("arms_do_rejected_not_allow_edit", sprintf($this->err_list['arms_do_rejected_not_allow_edit'], $order_do['do_id']));
					}elseif($do['status'] == 4 && $do['active'] == 1){
						$this->error_die("arms_do_cancelled_not_allow_edit", sprintf($this->err_list['arms_do_cancelled_not_allow_edit'], $order_do['do_id']));
					}elseif($do['status'] == 5 && $do['active'] == 1){
						$this->error_die("arms_do_terminated_not_allow_edit", sprintf($this->err_list['arms_do_terminated_not_allow_edit'], $order_do['do_id']));
					}elseif($do['approved']==1 && $do['checkout']==0 && $do['status'] == 1){
						$this->error_die("arms_do_approved_not_allow_edit", sprintf($this->err_list['arms_do_approved_not_allow_edit'], $order_do['do_id']));
					}elseif($do['approved']==1 && $do['checkout']==1){
						$this->error_die("arms_do_checkout_not_allow_edit", sprintf($this->err_list['arms_do_checkout_not_allow_edit'], $order_do['do_id']));
					}else{
						$this->error_die("arms_do_not_allow_to_edit", sprintf($this->err_list['arms_do_not_allow_to_edit'], $order_do['do_id']));
					}
				}
			}
			
			// New Shipping Fee
			$do_data['shipping_fee'] = round(mf($tmp_do_data['shipping_fee']), 2);
			
			// Cancel Order Items
			if(!$tmp_do_data['cancel_order_items'] || !is_array($tmp_do_data['cancel_order_items']))	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "cancel_order_items"));
			$do_data['cancel_do_items'] = array();
			$cancelled_do_item_id_list = array();
			
			foreach($tmp_do_data['cancel_order_items'] as $tmp_order_items){
				// Item ID
				$item_id = mi($tmp_order_items['item_id']);
				if($item_id<=0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "item_id"));
				
				if(isset($do_data['cancel_do_items'][$item_id]))	$this->error_die("data_duplicated", sprintf($this->err_list['data_duplicated'], 'item_id', $item_id));
				$do_data['cancel_do_items'][$item_id]['item_id'] = $item_id;
				
				// Cancel Qty
				$do_data['cancel_do_items'][$item_id]['cancel_qty'] = mi($tmp_order_items['cancel_qty']);
				if($do_data['cancel_do_items'][$item_id]['cancel_qty'] <= 0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "cancel_qty"));
				
				// SKU Item ID
				$do_data['cancel_do_items'][$item_id]['sku_item_id'] = mi($tmp_order_items['sku_item_id']);
				if($do_data['cancel_do_items'][$item_id]['sku_item_id'] <= 0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "sku_item_id"));
				
				// Check item_id match with sku_item_id
				$con->sql_query("select * from marketplace_order_do_items where marketplace_order_id=$marketplace_order_id and do_sequence=$do_sequence and item_id=$item_id and sku_item_id=".mi(mi($tmp_order_items['sku_item_id']))." for update");
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				// Not Match
				if(!$tmp)	$this->error_die("order_items_not_match", sprintf($this->err_list['order_items_not_match'], $tmp_order_items['sku_item_id'], $item_id));
				$do_data['cancel_do_items'][$item_id]['db_obj'] = $tmp;
				
				// Get do_items
				if($order_do['do_id']>0){
					$con->sql_query("select * from do_items where branch_id=".mi($order_do['branch_id'])." and do_id=".mi($order_do['do_id'])." and sku_item_id=".mi(mi($tmp_order_items['sku_item_id']))." and cost_price=".mf($tmp['unit_price'])." and pcs>0 ".($cancelled_do_item_id_list ? ' and id not in ('.join(',', $cancelled_do_item_id_list).')': '')." order by id limit 1");
					$do_items = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if(!$do_items)	$this->error_die("order_items_not_found", sprintf($this->err_list['order_items_not_found'], $tmp_order_items['sku_item_id']));
					
					$do_data['cancel_do_items'][$item_id]['do_items_obj'] = $do_items;
					$cancelled_do_item_id_list[] = $do_items['id'];
				}
				
			}
			
			// Get Shipping Fee Item
			if($order_do['do_id'] && $order_do['shipping_fee']>0){
				if($this->shipping_item_sid > 0){
					// Get from do_items
					$con->sql_query("select * from do_items where branch_id=".mi($order_do['branch_id'])." and do_id=".mi($order_do['do_id'])." and sku_item_id=".mi($this->shipping_item_sid));
					$do_items = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($do_items){
						$do_data['shipping_do_items'] = $do_items;
					}
				}else{
					// Get from do_open_items
					$con->sql_query("select * from do_open_items where branch_id=".mi($order_do['branch_id'])." and do_id=".mi($order_do['do_id'])." and artno_mcode=".ms($this->shipping_item_code));
					$do_open_items = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($do_open_items){
						$do_data['shipping_do_open_items'] = $do_open_items;
					}
				}
				
				// Shipping Item Not Found
				if($do_data['shipping_fee']>0 && (!$do_data['shipping_do_items'] && !$do_data['shipping_do_open_items'])){
					$this->error_die("shipping_items_not_found", $this->err_list['shipping_items_not_found']);
				}
			}
			
			
			
			$do_list[$do_sequence] = $do_data;
		}
		
		//print_r($do_list);exit;
		
		foreach($do_list as $do_sequence => $do_data){	// Loop DO
			$bid = mi($do_data['db_obj']['branch_id']);
			$do_id = mi($do_data['db_obj']['do_id']);
			
			foreach($do_data['cancel_do_items'] as $item_id => $order_items){	// Loop Items
				$cancel_qty = mi($order_items['cancel_qty']);
				$sid = mi($order_items['sku_item_id']);
				
				$new_qty = $order_items['db_obj']['qty'] - $cancel_qty;
				if($new_qty<0)	$new_qty = 0;
				if($order_items['do_items_obj']){	// Got DO Items
					if($new_qty>0){
						// Update New Qty
						$upd = array();
						$upd['pcs'] = $new_qty;
						$con->sql_query("update do_items set ".mysql_update_by_field($upd)." where branch_id=$bid and do_id=$do_id and id=".mi($order_items['do_items_obj']['id']));
					}else{
						// Delete DO Items
						$con->sql_query("delete from do_items where branch_id=$bid and do_id=$do_id and id=".mi($order_items['do_items_obj']['id']));
					}
				}
				
				// Update Order Items
				$upd = array();
				$upd['qty'] = $new_qty;
				$upd['last_update'] = 'CURRENT_TIMESTAMP';
				if(!$new_qty)	$upd['active'] = 0;
				$con->sql_query("update marketplace_order_do_items set ".mysql_update_by_field($upd)." where id=".mi($order_items['db_obj']['id']));
			}
			
			// Update Shipping Fee
			if($do_data['shipping_fee']>0){
				// Got Shipping Fee
				$upd = array();
				$upd['cost_price'] = $do_data['shipping_fee'];
				if($do_data['shipping_do_items']){
					$con->sql_query("update do_items set ".mysql_update_by_field($upd)."where branch_id=$bid and do_id=$do_id and id=".mi($do_data['shipping_do_items']['id']));
				}elseif($do_data['shipping_do_open_items']){
					$con->sql_query("update do_open_items set ".mysql_update_by_field($upd)."where branch_id=$bid and do_id=$do_id and id=".mi($do_data['shipping_do_open_items']['id']));
				}
			}else{
				// No more shipping Fee
				if($do_data['shipping_do_items']){
					$con->sql_query("delete from do_items where branch_id=$bid and do_id=$do_id and id=".mi($do_data['shipping_do_items']['id']));
				}elseif($do_data['shipping_do_open_items']){
					$con->sql_query("delete from do_open_items where branch_id=$bid and do_id=$do_id and id=".mi($do_data['shipping_do_open_items']['id']));
				}
			}
				
			if($do_id){
				// Calculate DO amount
				$appCore->doManager->recalculateDOAmount($bid, $do_id);
				
				// Update DO Last Update
				$upd = array();
				$upd['last_update'] = 'CURRENT_TIMESTAMP';
				
				// Check if need to cancel this DO
				$con->sql_query("select count(*) as c from do_items where branch_id=$bid and do_id=$do_id
					union
					select count(*) as c from do_open_items where branch_id=$bid and do_id=$do_id");
				$item_count = 0;
				while($r = $con->sql_fetchassoc()){
					$item_count += $r['c'];
				}
				$con->sql_freeresult();
				
				if(!$item_count){	// No Item in DO, Need Cancel
					$upd['status'] = 4;
				}
				$con->sql_query("update do set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$do_id");
				
				if(!$item_count){
					log_br(1, 'DELIVERY ORDER', $do_id, "DO Cancelled from Marketplace: (ID#".$do_id.")", $this->branch_id);
				}
			}
			
			// Check active order items
			$con->sql_query("select count(*) as c from marketplace_order_do_items where marketplace_order_id=$marketplace_order_id and do_sequence=$do_sequence and active=1");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$tmp['c']){	// No more active item
				$upd = array();
				$upd['active'] = 0;
				$upd['last_update'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("update marketplace_order_do set ".mysql_update_by_field($upd)." where marketplace_order_id=$marketplace_order_id and do_sequence=$do_sequence");
			}
		}
		
		// Check active order DO
		$con->sql_query("select count(*) as c from marketplace_order_do where marketplace_order_id=$marketplace_order_id and active=1");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$tmp['c']){	// No more active item
			$upd = array();
			$upd['active'] = 0;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("update marketplace_order set ".mysql_update_by_field($upd)." where id=$marketplace_order_id");
		}
		
		$upd = array();
		$upd['cancel_success'] = 1;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update marketplace_order_cancel set ".mysql_update_by_field($upd)." where id=$cancel_id");
		
		log_br(1, 'DELIVERY ORDER', $do_id, "Marketplace cancel item, Cancel ID: (ID#".$cancel_id.")", $this->branch_id);
		
		// Commit Transaction
		$con->sql_commit();
		
		//print_r($do_list);exit;
		
		$ret = array();
		$ret['result'] = 1;
		// Return Data				
		$this->respond_data($ret);
	}
	
	function get_order_status(){
		global $con, $appCore;
		
		// Order_no Not Found
		$order_no = trim($this->rcv_data['order_no']);
		if(!$order_no)	$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "order_no"));
		
		// Get Order
		$con->sql_query("select * from marketplace_order where order_no=".ms($order_no));
		$order = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Order Not Found
		if(!$order)	$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "Order"));
		$marketplace_order_id = mi($order['id']);
		
		$total_amount = $total_qty = 0;
		
		// Get Order DO
		$order_do_list = array();
		$q1 = $con->sql_query("select * from marketplace_order_do where marketplace_order_id=$marketplace_order_id");
		while($order_do = $con->sql_fetchassoc($q1)){
			$bid = mi($order_do['branch_id']);
			$do_id = mi($order_do['do_id']);
			
			$do = $do_items_list = $do_open_items_list = array();
			if($do_id){
				// Get DO
				$con->sql_query("select * from do where branch_id=$bid and id=$do_id");
				$do = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				$do['open_info'] = unserialize($do['open_info']);
				$do_checkout_image = $appCore->doManager->load_do_checkout_img($do_id, $bid);
				
				// Get DO Items
				$q_di = $con->sql_query("select di.*, uom.fraction as uom_fraction
					from do_items di
					left join uom on uom.id=di.uom_id
					where di.branch_id=$bid and di.do_id=$do_id order by di.id");
				while($do_items = $con->sql_fetchassoc($q_di)){
					$do_items_list[$do_items['id']] = $do_items;
				}
				$con->sql_freeresult($q_di);
				
				// Get DO Open Items
				$q_di = $con->sql_query("select * from do_open_items where branch_id=$bid and do_id=$do_id order by id");
				while($do_open_items = $con->sql_fetchassoc($q_di)){
					$do_open_items_list[$do_open_items['id']] = $do_open_items;
				}
				$con->sql_freeresult($q_di);
				
				$total_amount += $do['total_inv_amt'];
				$total_qty += $do['total_qty'];
			}
			
			$order_do['do_obj'] = $do;
			$order_do['do_items_list_obj'] = $do_items_list;
			$order_do['do_open_items_list'] = $do_open_items_list;
			$order_do_list[$order_do['id']] = $order_do;
			
			
		}
		$con->sql_freeresult($q1);
		
		//print_r($order_do_list);exit;
		
		$ret = array();
		$ret['result'] = 1;
		$ret['order_no'] = $order_no;
		$ret['active'] = $order['active'];
		$ret['completed'] = $order['completed'];
		$ret['total_amount'] = $total_amount;
		$ret['total_qty'] = $total_qty;
		$ret['do_checkout_image'] = $do_checkout_image;
		$ret['do_data'] = array();
		
		$do_count = $completed_count = $cancelled_count = 0;
		
		foreach($order_do_list as $order_do){	// Loop Order DO
			$do_count++;
			
			$do_data = array();
			$do_data['do_sequence'] = $order_do['do_sequence'];
			$do_data['do_id'] = $order_do['do_id'];
			$do_data['do_date'] = trim($order_do['do_obj']['do_date']);
			$do_data['do_no'] = trim($order_do['do_obj']['do_no']);
			$do_data['invoice_no'] = trim($order_do['do_obj']['inv_no']);
			$do_data['tracking_code'] = trim($order_do['do_obj']['tracking_code']);
			$do_data['shipping_provider'] = trim($order_do['do_obj']['shipment_method']);
			$do_data['total_do_amount'] = trim($order_do['do_obj']['total_inv_amt']);
			$do_data['total_do_qty'] = trim($order_do['do_obj']['total_qty']);
			$do_data['arms_status'] = '';
			
			if($order_do['do_obj']){
				if($order_do['do_obj']['status']==4 || !$order_do['do_obj']['active']){
					$do_data['arms_status'] = 'cancelled';
					$cancelled_count++;
				}else{
					if($order_do['do_obj']['status']==1){
						if($order_do['do_obj']['approved']==1){
							if($order_do['do_obj']['checkout']==1){
								$do_data['arms_status'] = 'checkout';
								if($do_data['invoice_no']){	// Have printed invoice
									$completed_count++;
								}
							}else{
								$do_data['arms_status'] = 'approved';
							}
						}else{
							$do_data['arms_status'] = 'confirmed';							
						}						
					}else{
						$do_data['arms_status'] = 'draft';
					}
				}
			}
			
			$do_data['cust_name'] = trim($order_do['do_obj']['open_info']['name']);
			$do_data['cust_address'] = trim($order_do['do_obj']['open_info']['address']);
			
			// Items
			$do_data['order_items'] = array();
			if($order_do['do_items_list_obj']){
				// Loop DO Items
				foreach($order_do['do_items_list_obj'] as $do_items){
					$item_data = array();
					$item_data['ctn'] = $do_items['ctn'];
					$item_data['pcs'] = $do_items['pcs'];
					$item_data['uom_fraction'] = $do_items['uom_fraction'];
					$item_data['sku_item_id'] = $do_items['sku_item_id'];
					$item_data['unit_price'] = $do_items['cost_price'];
					
					$do_data['order_items'][] = $item_data;
				}
			}
			
			// Open Item
			$do_data['open_items'] = array();
			if($order_do['do_open_items_list']){
				// Loop DO Items
				foreach($order_do['do_open_items_list'] as $do_open_items){
					$item_data = array();
					$item_data['item_code'] = $do_open_items['artno_mcode'];
					$item_data['item_desc'] = $do_open_items['description'];
					$item_data['pcs'] = $do_open_items['pcs'];
					$item_data['unit_price'] = $do_open_items['cost_price'];
					
					$do_data['open_items'][] = $item_data;
				}
			}
			
			$ret['do_data'][] = $do_data;
		}
		
		if($do_count == $completed_count && !$ret['completed']){
			// All DO Completed
			$upd = array();
			$upd = array();
			$upd['completed'] = 1;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("update marketplace_order set ".mysql_update_by_field($upd)." where id=$marketplace_order_id");
			log_br(1, 'DELIVERY ORDER', 0, "Marketplace Order Makred as Completed: (Order No#".$order_no.")", $this->branch_id);
			
			$ret['completed'] = 1;
		}elseif($do_count == $cancelled_count && $ret['active']){
			// All DO Cancelled
			$upd = array();
			$upd['active'] = 0;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("update marketplace_order set ".mysql_update_by_field($upd)." where id=$marketplace_order_id");
			log_br(1, 'DELIVERY ORDER', 0, "Marketplace Order Makred as Cancelled: (Order No#".$order_no.")", $this->branch_id);
			
			$ret['active'] = 0;
		}
		
		// Return Data				
		$this->respond_data($ret);
	}
	
	function validate_arms_login(){
		global $con, $appCore, $config;
		
		// User ID
		$user_id = trim($this->rcv_data['user_id']);
		if($user_id<=0)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "user_id"));
		
		// Code
		$code = trim($this->rcv_data['code']);
		if(!$code)	$this->error_die("invalid_data", sprintf($this->err_list['invalid_data'], "code"));
		
		$filter = array();
		$filter[] = "user_id=$user_id and code=".ms($code);
		$filter[] = "added>=".ms(date("Y-m-d H:i:s", strtotime("-1 minute")));
		//$filter[] = "added>=".ms(date("Y-m-d H:i:s", strtotime("-1 hour")));
		$str_filter = "where ".join(' and ', $filter);
		$sql = "select * from marketplace_login_data $str_filter";
		//print $sql;exit;
		
		$con->sql_query($sql);
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$data){
			$this->error_die("arms_login_failed", $this->err_list['arms_login_failed']);
		}
		
		// Delete User Code
		$con->sql_query("delete from marketplace_login_data where user_id=$user_id");
		
		$ret = array();
		$ret['result'] = 1;
		$ret['user_id'] = $user_id;
		$ret['confirm_code'] = md5($code.$config['arms_marketplace_settings']['access_token']);
		
		// Return Data				
		$this->respond_data($ret);
	}
	
	private function get_vendor_count(){
		global $con;
		
		$filter = array();
		
		if($this->rcv_data['vendor_id_list']){ // it is filter by vendor id
			$filter[] = "v.id in (".join(",", $this->rcv_data['vendor_id_list']).")";
		}
		
		$min_changes_row_index = mi($this->rcv_data['min_changes_row_index']);
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Count all sku
		$this->put_log("Checking Vendor Count.");	
		$q1 = $con->sql_query("select count(*) as c 
							   from vendor v
							   left join tmp_trigger_log tmp on tmp.tablename='vendor' and tmp.id=v.id
							   $str_filter");
		$tmp = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		$ret['count'] = $tmp['c'];
		
		// Return Data
		$this->respond_data($ret);
	}
	
	private function get_vendor(){
		global $con;
		
		$filter = array();
		
		if($this->rcv_data['vendor_id_list']){ // it is filter by vendor id
			$filter[] = "v.id in (".join(",", $this->rcv_data['vendor_id_list']).")";
		}
		
		// get limit by certain records
		if(!$this->rcv_data['vendor_id_list'] && $this->rcv_data['start_from'] >= 0 && $this->rcv_data['limit_count'] > 0){
			$limit = "limit ".mi($this->rcv_data['start_from']).", ".$this->rcv_data['limit_count'];
		}
		
		$min_changes_row_index = mi($this->rcv_data['min_changes_row_index']);
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}

		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Construct Data to return
		$ret = $vendor_list = array();
		$sql = "select v.*, ifnull(tmp.row_index,0) as changes_row_index
				from vendor v
				left join tmp_trigger_log tmp on tmp.tablename='vendor' and tmp.id=v.id
				$str_filter
				order by changes_row_index
				$limit";
		//die($sql);
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$tmp = array();
			$tmp['vendor_id'] = trim($r['id']);
			$tmp['vendor_code'] = trim($r['code']);
			$tmp['description'] = trim($r['description']);
			$tmp['company_no'] = trim($r['company_no']);
			$tmp['bank_account'] = trim($r['bank_account']);
			$tmp['address'] = trim($r['address']);
			$tmp['phone_1'] = trim($r['phone_1']);
			$tmp['phone_2'] = trim($r['phone_2']);
			$tmp['phone_3'] = trim($r['phone_3']);
			$tmp['contact_person'] = trim($r['contact_person']);
			$tmp['contact_email'] = trim($r['contact_email']);
			$tmp['changes_row_index'] = mi($r['changes_row_index']);
			
			$vendor_list[$r['id']] = $tmp;
		}
		$con->sql_freeresult($q1);
		
		if($vendor_list){
			// Construct Data to return
			$ret['result'] = 1;
			$ret['vendor_data'] = $vendor_list;
		}else{
			$this->error_die("data_not_found", sprintf($this->err_list['data_not_found'], "Vendor"));
		}
		unset($vendor_list);
		
		// Return Data				
		$this->respond_data($ret);
	}

	private function get_family_stock($sku_id, $item_id){
		global $con;
				
		$family_stock = $ret = array();
		// Construct Data to return
		$sql = "select si.id, si.sku_id, si.is_parent, uom.fraction as packing_uom_fraction, sic.qty as stock_qty
				from sku_items si
				left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = ".mi($this->branch_id)."
				left join uom on si.packing_uom_id=uom.id
				where si.sku_id=".mi($sku_id)."
				order by si.is_parent desc";
		$parent_stock = 0;
		$q1 = $con->sql_query($sql);

		while($r = $con->sql_fetchassoc($q1)){
			$uncheckout_do_qty = $final_stock = 0;
			$prms = $do_info = array();
			$prms['sid'] = $r['id'];
			$prms['bid'] = $this->branch_id;
			$do_info = $this->load_uncheckout_do($prms);
			$uncheckout_do_qty = mf($do_info['uncheckout_do_qty']);

			$final_stock = mf($r['stock_qty']) - $uncheckout_do_qty;
			$parent_stock += ($final_stock*$r['packing_uom_fraction']);

			$family_stock[$r['id']] = array(
										'packing_uom_fraction' => $r['packing_uom_fraction'],
										'is_parent' => $r['is_parent']
									);

		}
		$con->sql_freeresult($q1);
		foreach($family_stock as $id => $data){
			if($data['is_parent']){
				$ret[$id] = $parent_stock;
			}else{
				$child_stock = $parent_stock/$data['packing_uom_fraction'];
				$ret[$id] = floor($child_stock);
			}
		}

		return $ret[$item_id];
	}
}

$ARMS_MARKETPLACE_API = new ARMS_MARKETPLACE_API();
$ARMS_MARKETPLACE_API->_default();
?>