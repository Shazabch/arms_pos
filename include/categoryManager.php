<?php
class categoryManager{
	// common variables
	
	// private variables
	private $cacheCatInfoList;

	function __construct(){
		
	}

	function getCategoryInfo($cat_id){
		global $con;

		$cat_id = mi($cat_id);
		if($cat_id <= 0)	return;

		if(!is_array($this->cacheCatInfoList))	$this->cacheCatInfoList = array();

		if(isset($this->cacheCatInfoList[$cat_id]))    return $this->cacheCatInfoList[$cat_id];
		
		$con->sql_query("select c.*, cc.*
		from category c
		left join category_cache cc on cc.category_id=c.id
		where c.id=$cat_id");
		$r = $con->sql_fetchassoc();
		$r['min_sku_photo'] = unserialize($r['min_sku_photo']);
		$r['category_disc_by_branch'] = unserialize($r['category_disc_by_branch']);
		$r['category_point_by_branch'] = unserialize($r['category_point_by_branch']);
		$r['category_staff_disc_by_branch'] = unserialize($r['category_staff_disc_by_branch']);
		$this->cacheCatInfoList[$cat_id] = $r;
		$con->sql_freeresult();

		return $this->cacheCatInfoList[$cat_id];
	}
}
?>
