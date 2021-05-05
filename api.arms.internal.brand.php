<?php
/*
2/8/2021 1:23 PM Andy
- Added Brand Handler.
- Added api "get_brand_count" and "get_brand_list".
*/
class API_ARMS_INTERNAL_BRAND {
	var $main_api = false;
	
	var $err_list = array(
		"no_brand_found" => "No Brand is found.",
	);
	
	var $sync_server_compatible_api = array('get_brand_count', 'get_brand_list'); 
	function __construct($main_api){
		$this->main_api = $main_api;
	}
	
	function is_api_support_sync_server($api_name){
		if(in_array($api_name, $this->sync_server_compatible_api)){
			return true;
		}
		return false;
	}
	
	function get_brand_count(){
		global $con;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		//if(!$this->main_api->user){
		//	$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		//}
		
		$brand_id_list = $_REQUEST['brand_id_list'];
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		$filter = array();
		if($brand_id_list){ // it is filter by debtor id
			$filter[] = "b.id in (".join(",", $brand_id_list).")";
		}
		
		if($min_changes_row_index > 0){
			$extra_join = "left join tmp_trigger_log tmp on tmp.tablename='brand' and tmp.id=b.id";
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Count all Brand
		$this->main_api->put_log("Checking Brand Count.");
		$q1 = $con->sql_query("select count(*) as c 
							   from brand b
							   $extra_join
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
	
	function get_brand_list(){
		global $con, $config;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		$start_from = mi($_REQUEST['start_from']);
		$limit_count = mi($_REQUEST['limit']);
		$brand_id_list = $_REQUEST['brand_id_list'];
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		
		$filter = array();
		//$filter[] = "d.active=1";
		
		if($brand_id_list){ // it is filter by debtor id
			$filter[] = "b.id in (".join(",", $brand_id_list).")";
		}
		
		if(!$brand_id_list && $start_from >= 0 && $limit_count > 0){
			$limit = "limit $start_from, $limit_count";
		}
		
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}

		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Construct Data to return
		$ret = array();
		$ret['result'] = 1;
		
		$sql = "select b.id as brand_id, b.code, b.description, b.active, ifnull(tmp.row_index,0) as changes_row_index
			from brand b
			left join tmp_trigger_log tmp on tmp.tablename='brand' and tmp.id=b.id
			$str_filter
			order by changes_row_index
			$limit";
			
		//print $sql;exit;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$ret['brand_data'][] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($ret['brand_data']){
			// Return Data
			$this->main_api->respond_data($ret);
		}else{
			$this->main_api->error_die($this->err_list["no_brand_found"], "no_brand_found");
		}
	}
}
?>
