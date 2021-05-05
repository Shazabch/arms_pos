<?php
/*
11/11/2020 5:58 PM William
- Enhanced to add "show_in_suite_pos" filter to category api.

2/25/2021 5:38 PM William
- Enhanced to add photo path to "get_category_list" api.
*/
class API_ARMS_INTERNAL_CATEGORY {
	var $main_api = false;
	
	function __construct($main_api){
		$this->main_api = $main_api;
	}
	
	function get_category_count(){
		global $con;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$category_id_list = $_REQUEST['category_id_list'];
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		$filter = array();
		if($category_id_list){ // it is filter by category id
			$filter[] = "c.id in (".join(",", $category_id_list).")";
		}
		
		if($min_changes_row_index > 0){
			$xtra_join = "left join tmp_trigger_log tmp on tmp.tablename='category' and tmp.id=c.id";
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Count all category
		$this->main_api->put_log("Checking Category Count.");
		$q1 = $con->sql_query("select count(*) as c 
							   from category c
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
	
	function get_category_list(){
		global $con, $config;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		$start_from = mi($_REQUEST['start_from']);
		$limit_count = mi($_REQUEST['limit']);
		$category_id = mi($_REQUEST['category_id']);
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		//$show_in_suite_pos = mi($_REQUEST['show_in_suite_pos']);
		
		$filter = array();
		//$filter[] = "c.active=1";
		
		if($category_id){
			$filter[] = "c.id = ".mi($category_id);
		}
		
		if(!$category_id && $start_from >= 0 && $limit_count > 0){
			$limit = "limit $start_from, $limit_count";
		}
		
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		/*if($show_in_suite_pos){
			$filter[] = "c.show_in_suite_pos =".mi($show_in_suite_pos);
		}*/

		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Construct Data to return
		$ret = array();
		$ret['result'] = 1;
		
		$sql = "select c.id as category_id, c.code, c.description, c.level, c.root_id, c.tree_str, c.active, ifnull(tmp.row_index,0) as changes_row_index,
			c.got_pos_photo as got_pos_photo
			from category c
			left join tmp_trigger_log tmp on tmp.tablename='category' and tmp.id=c.id
			$str_filter
			order by changes_row_index
			$limit";
			
		//print $sql;exit;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			if($r['got_pos_photo']){
				$cat_id = mi($r['category_id']);
				$group_num = ceil($cat_id/10000);
				$photo_abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/category_photo/".$group_num."/".$cat_id."/1.jpg";
				if(file_exists($photo_abs_path)){
					$imagep = str_replace($_SERVER['DOCUMENT_ROOT']."/", "", $photo_abs_path);
					$r['photo'] = $imagep;
				}
			}else{
				$r['photo'] = '';
			}
			$ret['category_data'][] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($ret['category_data']){
			// Return Data				
			$this->main_api->respond_data($ret);
		}else{
			$this->main_api->error_die($this->err_list["no_category_found"], "no_category_found");
		}	
		
	}
}
?>