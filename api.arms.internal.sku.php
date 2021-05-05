<?php
/*
2/8/2021 1:23 PM Andy
- Added SKU Handler.
- Added api "get_sku_group_count" and "get_sku_group_list".
*/
class API_ARMS_INTERNAL_SKU {
	var $main_api = false;
	
	var $err_list = array(
		"no_sku_group_found" => "No SKU Group is found.",
	);
	
	var $sync_server_compatible_api = array('get_sku_group_count', 'get_sku_group_list'); 
	function __construct($main_api){
		$this->main_api = $main_api;
	}
	
	function is_api_support_sync_server($api_name){
		if(in_array($api_name, $this->sync_server_compatible_api)){
			return true;
		}
		return false;
	}
	
	function get_sku_group_count(){
		global $con;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		//if(!$this->main_api->user){
		//	$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		//}
		
		$sku_group_id_list = $_REQUEST['sku_group_id_list'];
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		$filter = array();
		if($sku_group_id_list){ // it is filter by debtor id
			$filter[] = "sg.sku_group_id in (".join(",", $sku_group_id_list).")";
		}
		
		if($min_changes_row_index > 0){
			$extra_join = "left join tmp_trigger_log tmp on tmp.tablename='sku_group_item' and tmp.id=sg.sku_group_id";
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Count all debtor
		$this->main_api->put_log("Checking SKU Group Count.");
		$q1 = $con->sql_query("select count(distinct sku_group_id) as c 
							   from sku_group sg
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
	
	function get_sku_group_list(){
		global $con, $config;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		$start_from = mi($_REQUEST['start_from']);
		$limit_count = mi($_REQUEST['limit']);
		$sku_group_id_list = $_REQUEST['sku_group_id_list'];
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		
		$filter = array();
		//$filter[] = "d.active=1";
		
		if($sku_group_id_list){ // it is filter by debtor id
			$filter[] = "sg.sku_group_id in (".join(",", $sku_group_id_list).")";
		}
		
		if(!$sku_group_id_list && $start_from >= 0 && $limit_count > 0){
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
		
		$sql = "select sg.sku_group_id, ifnull(tmp.row_index,0) as changes_row_index
			from sku_group sg
			left join tmp_trigger_log tmp on tmp.tablename='sku_group_item' and tmp.id=sg.sku_group_id
			$str_filter
			order by changes_row_index
			$limit";
			
		//print $sql;exit;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$sg_id = mi($r['sku_group_id']);
			$r['by_branch'] = array();
			// Load SKU Group By Branch
			$q2 = $con->sql_query("select * from sku_group where sku_group_id=$sg_id");
			while($r2 = $con->sql_fetchassoc($q2)){
				$bid = mi($r2['branch_id']);
				
				$r['by_branch'][$bid]['branch_id'] = $bid;
				$r['by_branch'][$bid]['code'] = $r2['code'];
				$r['by_branch'][$bid]['description'] = $r2['description'];
				$r['by_branch'][$bid]['item_list'] = array();
				
				// Load SKU Items
				$q3 = $con->sql_query("select si.id as sku_item_id 
					from sku_group_item sgi 
					join sku_items si on si.sku_item_code=sgi.sku_item_code
					where sgi.branch_id=$bid and sgi.sku_group_id=$sg_id
					order by si.id");
				while($r3 = $con->sql_fetchassoc($q3)){
					$r['by_branch'][$bid]['item_list'][] = $r3['sku_item_id'];
				}
				$con->sql_freeresult($q3);
			}
			$con->sql_freeresult($q2);
			
			$ret['sg_data'][] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($ret['sg_data']){
			// Return Data
			$this->main_api->respond_data($ret);
		}else{
			$this->main_api->error_die($this->err_list["no_sku_group_found"], "no_sku_group_found");
		}
	}
}
?>
