<?php
/**/
class API_ARMS_INTERNAL_DEBTOR {
	var $main_api = false;

	function __construct($main_api){
		$this->main_api = $main_api;
	}
	
	function get_debtor_count(){
		global $con;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$debtor_id_list = $_REQUEST['debtor_id_list'];
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		$filter = array();
		if($debtor_id_list){ // it is filter by debtor id
			$filter[] = "d.id in (".join(",", $debtor_id_list).")";
		}
		
		if($min_changes_row_index > 0){
			$extra_join = "left join tmp_trigger_log tmp on tmp.tablename='debtor' and tmp.id=d.id";
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Count all debtor
		$this->main_api->put_log("Checking Debtor Count.");
		$q1 = $con->sql_query("select count(*) as c 
							   from debtor d
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
	
	function get_debtor_list(){
		global $con, $config;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		$start_from = mi($_REQUEST['start_from']);
		$limit_count = mi($_REQUEST['limit']);
		$debtor_id = mi($_REQUEST['debtor_id']);
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		
		$filter = array();
		//$filter[] = "d.active=1";
		
		if($debtor_id){
			$filter[] = "d.id = ".mi($debtor_id);
		}
		
		if(!$debtor_id && $start_from >= 0 && $limit_count > 0){
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
		
		$sql = "select d.id as debtor_id, d.code, d.description, d.company_no, d.address, d.delivery_address, d.area, d.phone_1, d.phone_2, d.phone_3, d.contact_email, d.term, d.credit_limit, d.active, ifnull(tmp.row_index,0) as changes_row_index
			from debtor d
			left join tmp_trigger_log tmp on tmp.tablename='debtor' and tmp.id=d.id
			$str_filter
			order by changes_row_index
			$limit";
			
		//print $sql;exit;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$ret['debtor_data'][] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($ret['debtor_data']){
			// Return Data
			$this->main_api->respond_data($ret);
		}else{
			$this->main_api->error_die($this->main_api->err_list["no_debtor_found"], "no_debtor_found");
		}
	}
}
?>