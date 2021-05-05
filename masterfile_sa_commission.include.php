<?php
/*
11/14/2019 5:15 PM Andy
- Added maintenance check version 422.
*/

$maintenance->check(422);

// pre-load vendors
$con->sql_query("select id,description from vendor order by description") or die(mysql_error());
$temp = $con->sql_fetchrowset();
$con->sql_freeresult();
foreach($temp as $r){
	$vendor[$r['id']] = $r;
}
$smarty->assign("vendor",$vendor);

// pre-load sku type
$con->sql_query("select * from sku_type where active=1 order by code");
$sku_type_list = $con->sql_fetchrowset();
$con->sql_freeresult();
$smarty->assign('sku_type_list', $sku_type_list);

function get_commission_condition_item_info($value){
	global $con;
	
	$ret = array();
	if($value['sku_item_id'] > 0){
		$con->sql_query("select si.id as sku_item_id,si.sku_item_code,si.artno,si.description
						 from sku_items si
						 where si.id=".mi($value['sku_item_id']));

		$ret = $con->sql_fetchassoc();
		$con->sql_freeresult();
	}else{
		if(isset($value['brand_id'])){
			if($value['brand_id']==='0'){
				$ret['brand_id'] = 0;
				$ret['brand_desc'] = "UNBRANDED";
			}elseif($value['brand_id'] > 0){
				$con->sql_query("select description from brand where id=".mi($value['brand_id']));
				$ret['brand_id'] = $value['brand_id'];
				$ret['brand_desc'] = $con->sql_fetchfield(0);
				$con->sql_freeresult();
			}
		}
		if($value['category_id'] > 0){
			$con->sql_query("select description from category where id=".mi($value['category_id']));
			$ret['category_id'] = $value['category_id'];
			$ret['cat_desc'] = $con->sql_fetchfield(0);
			$con->sql_freeresult();
		}
	}

	if($value['vendor_id']){
		$con->sql_query("select description from vendor where id=".mi($value['vendor_id']));
		$ret['vendor_id'] = $value['vendor_id'];
		$ret['vendor_desc'] = $con->sql_fetchfield(0);
		$con->sql_freeresult();
	}
	
	return $ret;
}

function load_commission_items($sac_id, $branch_id, &$date_list){
	global $con, $smarty, $LANG;
	
	$form = $_REQUEST;
	$item = array();

	if(!$sac_id) die("No Master ID found, unable to load commission item detail!");

	$q1 = $con->sql_query("select saci.* from sa_commission_items saci where saci.sac_id = ".mi($sac_id)." and saci.branch_id = ".mi($branch_id)." order by date_from desc, id asc");

	while($r = $con->sql_fetchrow($q1)){
		$item_info = $conditions = array();
		$r['conditions'] = unserialize($r['conditions']);
		if(trim($r['commission_value'])){
			if(unserialize($r['commission_value'])) $r['commission_value'] = unserialize($r['commission_value']);	
		}else unset($r['commission_value']);
		$item_info = get_commission_condition_item_info($r['conditions']);
		$item_info['sku_type'] = $r['conditions']['sku_type'];
		if($r['conditions']['price_type']){
			$price_type_list = $price_type = array();
			$price_type_list = explode(",", $r['conditions']['price_type']);
			foreach($price_type_list as $row=>$code){
				$price_type[$code] = $code;
			}
			$item_info['price_type'] = $price_type;
			unset($price_type_list);
			unset($price_type);
		}
		$r = array_merge($r, $item_info);
		
		if(!$date_list[$r['date_from']]){
			$date_count++;
			$date_list[$r['date_from']] = $date_count;
		}
		//$date_list[$r['date_from']] = $date_count;
		$items[$date_list[$r['date_from']]][] = $r;
	}
	$con->sql_freeresult($q1);

	return $items;
}
?>
