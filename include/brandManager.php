<?php
/*
3/22/2019 2:46 PM Andy
- Enhanced brandManager function "getBrandList" to can filter user allowed brand.
*/
class brandManager{
	// common variable
	
	
	function __construct(){
		
	}

	// function to get brand group list
	// return array brandGroupList
	public function getBrandGroupList($params = array()){
		global $con;

		$filter = array();
		$item_filter = array();
		if(isset($params['active'])){
			$filter[] = "brg.active=".mi($params['active']);
			$item_filter[] = "brand.active=".mi($params['active']);
		}	

		if($filter)	$str_filter = 'where '.join(' and ', $filter);
		if($item_filter)	$str_item_filter = 'where '.join(' and ', $item_filter);

		$brandGroupList = array();
		
		// load header
		$q1 = $con->sql_query("select brg.* from brgroup brg $str_filter order by code");
		while($r = $con->sql_fetchassoc($q1)){
			$brandGroupList['group'][$r['id']] = $r;
		}
		$con->sql_freeresult($q1);

		// load item			
		$q2 = $con->sql_query("select brgi.*,brand.code,brand.description 
			from brand_brgroup brgi 
			left join brand on brand.id=brgi.brand_id 
			$str_item_filter order by brand.description");
		while($r = $con->sql_fetchassoc($q2)){
			$brandGroupList['group'][$r['brgroup_id']]['itemList'][$r['brand_id']] = $r;
			$brandGroupList['have_group'][$r['brand_id']] = $r['brand_id'];
		}
		$con->sql_freeresult($q2);

		return $brandGroupList;
	}

	// function to get brandh list
	// return array brandList
	public function getBrandList($params = array()){
		global $con, $sessioninfo;

		$filter = array();
		if(isset($params['active']))	$filter[] = "b.active=".mi($params['active']);
		if(trim($sessioninfo['brand_ids'])){
			$filter[] = "id in ($sessioninfo[brand_ids])";
		}
		
		if($filter)	$str_filter = 'where '.join(' and ', $filter);

		$brandList = array();
		$q1 = $con->sql_query("select b.* from brand b $str_filter order by description");
	
		while($r = $con->sql_fetchassoc($q1)){
			$brandList[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);

		return $brandList;
	}
}
?>
