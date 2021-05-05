<?php
/*
3/24/2017 11:31 AM Andy
- Added new vendorManager function "getVendorSpecialGSTID".

8/27/2018 5:52 PM Andy
- Added new vendorManager function "getTaxRegister".

11/23/2018 5:14 PM Justin
- Added new function "getSKUItemCost", "getSKUItemQuotationCost" and "updateVendorQuotationCost".

3/22/2019 11:43 AM Andy
- Enhanced vendorManager function "getVendorList" to can filter user allowed vendor.
*/
class vendorManager{
	// common variable
	
	
	function __construct(){
		
	}

	// function to get vendor list
	// return array vendorList
	public function getVendorList($params = array()){
		global $con, $sessioninfo;

		$filter = array();
		if(isset($params['active']))	$filter[] = "v.active=".mi($params['active']);
		if(trim($sessioninfo['vendor_ids'])){
			$filter[] = " v.id in ($sessioninfo[vendor_ids])";
		}
		if($filter)	$str_filter = 'where '.join(' and ', $filter);

		$vendorList = array();
		$q1 = $con->sql_query("select v.* from vendor v $str_filter order by description");
	
		while($r = $con->sql_fetchassoc($q1)){
			$vendorList[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);

		return $vendorList;
	}

	// function to get vendor
	// return array vendor
	public function getVendorInfo($vid){
		global $con;

		$con->sql_query("select v.* from vendor v where v.id=".mi($vid));
		$vendor = $con->sql_fetchassoc();
		$con->sql_freeresult();

		return $vendor;
	}
	
	// function to get GST ID if the vendor is using special GST Rate such as RX-FR
	// return int gstID
	public function getVendorSpecialGSTID($vid){
		global $con;
		
		$vid = mi($vid);
		if(!$vid)	return;
		
		$con->sql_query("select gst_register from vendor where gst_register not in (0, -1) and id=$vid");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return mi($tmp['gst_register']);
	}
	
	// function to check whether vendor Tax Registered info
	// return array
	public function getTaxRegister($vid){
		global $con;
		
		$vid = mi($vid);
		if(!$vid)	return false;
		
		$con->sql_query("select tax_register, tax_percent from vendor where id=$vid");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $data;
	}
	
	function getSKUItemCost($bid, $sid){
		global $con;
		
		$bid = mi($bid);
		$sid = mi($sid);
		
		if(!$bid || !$sid)	return 0;
		
		$q1 = $con->sql_query("select ifnull(sic.grn_cost, si.cost_price) as cost_price
							   from sku_items si
							   left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
							   where si.id=$sid");
		$data = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		return mf($data['cost_price']);
	}
	
	function getSKUItemQuotationCost($bid, $sid, $vendor_id){
		global $con;
		
		$bid = mi($bid);
		$sid = mi($sid);
		$vendor_id = mi($vendor_id);
		if(isset($params['get_normal_cost']))	$get_normal_cost = mi($params['get_normal_cost']);
		
		if(!$bid || !$sid || !$vendor_id)	return 0;
		
		$con->sql_query("select cost from sku_items_vendor_quotation_cost where branch_id=$bid and sku_item_id=$sid and vendor_id=$vendor_id");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$cost = mf($data['cost']);
		
		// if Quotation Cost is not set, user normal cost
		if($get_normal_cost && $cost<=0)	$cost = $this->getSKUItemCost($bid, $sid);
		
		return $cost;
	}
	
	function updateVendorQuotationCost($params = array()){
		global $con, $config;
		
		$user_id = mi($params['user_id']);
		$vendor_id = mi($params['vendor_id']);
		$bid = mi($params['bid']);
		$sid = mi($params['sid']);
		$cost = round(mf($params['cost']), $config['global_cost_decimal_points']);
		if($cost < 0)	$cost = 0;
		$bcode = get_branch_code($bid);
		
		// sku_items_vendor_quotation_cost
		$sidp = array();
		$sidp['branch_id'] = $bid;
		$sidp['sku_item_id'] = $sid;
		$sidp['vendor_id'] = $vendor_id;
		$sidp['cost'] = $cost;
		$sidp['user_id'] = $user_id;
		$sidp['last_update'] = 'CURRENT_TIMESTAMP';
		
		// Select current row
		$q1 = $con->sql_query("select * from sku_items_vendor_quotation_cost where branch_id=$bid and sku_item_id=$sid and vendor_id=$vendor_id");
		$curr_data = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$curr_data && !$cost){	// Currently no data and cost is zero, no need update
			return;
		}
		
		if($curr_data['cost'] == $cost){	// same cost, no need update
			return;
		}
		
		// Update Record
		$con->sql_query("replace into sku_items_vendor_quotation_cost ".mysql_insert_by_field($sidp));
		
		// Select Again the record
		$con->sql_query("select * from sku_items_vendor_quotation_cost where branch_id=$bid and sku_item_id=$sid and vendor_id=$vendor_id");
		$sidph = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$sidph['added'] = $sidph['last_update'];
		unset($sidph['last_update']);
		
		// Update Record History
		$con->sql_query("replace into sku_items_vendor_quotation_cost_history ".mysql_insert_by_field($sidph));
		
		log_br($user_id, 'VD_QUOTATION_COST', $sid, "Vendor Quotation Cost Updated: $bcode, Vendor ID#$vendor_id, SKU ITEM ID#$sid, Cost: $cost");
		
		$ret = array();
		$ret['ok'] = 1;
		
		return $ret;
	}
}
?>
