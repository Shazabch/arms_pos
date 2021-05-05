<?php
/*
8/3/2020 6.00 PM William
- Enhanced to add vendor active to function get_vendor_list.

9/15/2020 9:23 AM William
- Enhanced function "get_vendor_list" order by changes_row_index.
*/
class API_ARMS_INTERNAL_VENDOR {
	var $main_api = false;
	
	function __construct($main_api){
		$this->main_api = $main_api;
	}
	
	function get_vendor_count(){
		global $con;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$vendor_id_list = $_REQUEST['vendor_id_list'];
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		$filter = array();
		if($vendor_id_list){ // it is filter by vendor id
			$filter[] = "v.id in (".join(",", $vendor_id_list).")";
		}
		
		if($min_changes_row_index > 0){
			$xtra_join = "left join tmp_trigger_log tmp on tmp.tablename='vendor' and tmp.id=v.id";
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		// do vendor filtering if this user does set to allow choose cetain vendors only
		if($this->main_api->user['vendors']) $filter[] = "id in (".join(",",array_keys($this->main_api->user['vendors'])).")";
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Count all vendor
		$this->main_api->put_log("Checking Vendor Count.");
		$q1 = $con->sql_query("select count(*) as c 
							   from vendor v
							   $xtra_join
							   $str_filter");
		$tmp = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		$ret['count'] = $tmp['c'];
		unset($tmp);
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function get_vendor_list(){
		global $con;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$start_from = $_REQUEST['start_from'];
		$limit_count = $_REQUEST['limit_count'];
		$vendor_id_list = $_REQUEST['vendor_id_list'];
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);

		$filter = array();
		if($vendor_id_list){ // it is filter by vendor id
			$filter[] = "v.id in (".join(",", $vendor_id_list).")";
		}
		
		// get limit by certain records
		if(!$vendor_id_list && $start_from >= 0 && $limit_count > 0){
			$limit = "limit ".$start_from.", ".$limit_count;
		}
		
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		// do vendor filtering if this user does set to allow choose cetain vendors only
		if($this->main_api->user['vendors']) $filter[] = "id in (".join(",",array_keys($this->main_api->user['vendors'])).")";

		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Construct Data to return
		$this->main_api->put_log("Checking Vendor List.");
		$ret = $vendor_list = array();
		$sql = "select v.id as vendor_id, v.code, v.description, v.company_no, v.bank_account, v.address, v.phone_1, v.phone_2, v.phone_3, 
				v.contact_person, v.contact_email, v.allow_grr_without_po, v.active, ifnull(tmp.row_index,0) as changes_row_index
				from vendor v
				left join tmp_trigger_log tmp on tmp.tablename='vendor' and tmp.id=v.id
				$str_filter
				order by changes_row_index
				$limit";
		//die($sql);
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$vendor_list[$r['vendor_id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($vendor_list){
			// Construct Data to return
			$ret['result'] = 1;
			$ret['vendor_data'] = $vendor_list;
		}else{
			$this->main_api->error_die("data_not_found", sprintf($this->main_api->err_list['data_not_found'], "Vendor"));
		}
		unset($vendor_list);
		
		// Return Data				
		$this->main_api->respond_data($ret);
	}
}
?>