<?php
/*
6/15/2017 6:01 PM Justin
- Enhanced to have add hardware feature.
*/
define('TERMINAL',1);
require("config.php");
include("language.php");
ini_set('memory_limit', '256M');
set_time_limit(0);

if(!$_REQUEST['SKIP_CONNECT_MYSQL']){
    //$link = mysql_connect($db_default_connection[0], $db_default_connection[1],$db_default_connection[2]);
	//$db_selected = mysql_select_db($db_default_connection[3], $link);
	
	require_once('include/db.php');
}
  
switch($_REQUEST['a']){
	case 'get_sku_list':
	    get_sku_list();
	    exit;
	case 'get_brand_list':
	    get_brand_list();
	    exit;
}

function get_sku_list(){
	global $con;
	
	$result = array();
	$limit = "";
	
	// need to limit if testing at maximus, sku is too large
	if ($_SERVER['SERVER_NAME'] == 'maximus' || $_SERVER['SERVER_NAME'] == '10.1.1.200'){
		$limit = "limit 1000";
	}
	
	$filters = array();
	$filters[] = "si.active=1";
	if($_REQUEST['brand_id']) $filters[] = "s.brand_id = ".mi($_REQUEST['brand_id']);
	
	$filter = join(" and ", $filters);
	
	$q1 = $con->sql_query("select si.id as sku_item_id, si.sku_item_code, si.description, si.internal_description, if(sip.price is null, si.selling_price, sip.price) as selling_price
						   from sku_items si
						   left join sku_items_price sip on sip.sku_item_id=si.id and sip.branch_id=1
						   left join sku s on s.id = si.sku_id
						   where $filter
						   order by si.description
						   $limit");

	if($con->sql_numrows($q1) == 0){
		return;
	}

	while($r = $con->sql_fetchassoc($q1)){
		// load multiple price
		$q2 = $con->sql_query("select * from sku_items_mprice where branch_id = 1 and sku_item_id = ".mi($r['sku_item_id']));
		while($r1 = $con->sql_fetchassoc($q2)){
			$r['mprice'][$r1['type']] = $r1['price'];
		}
		$con->sql_fetchassoc($q2);
		$result[] = $r;
	}
	$con->sql_freeresult($q1);

	print json_encode($result);
}

function get_brand_list(){
	global $con;
	
	$result = array();
	$limit = "";
	
	// need to limit if testing at maximus, sku is too large
	if ($_SERVER['SERVER_NAME'] == 'maximus' || $_SERVER['SERVER_NAME'] == '10.1.1.200'){
		$limit = "limit 1000";
	}
	
	$q1 = $con->sql_query("select b.id, b.code, b.description
						   from brand b
						   where b.active=1 
						   order by b.code, b.description
						   $limit");
	
	if($con->sql_numrows($q1) == 0){
		return;
	}

	// need to creates un-brand selection
	$tmp = array();
	$tmp['id'] = 0;
	$tmp['code'] = "UN-BRANDED";
	$tmp['description'] = "UN-BRANDED";
	$result[] = $tmp;
	unset($tmp);

	while($r = $con->sql_fetchassoc($q1)){
		$result[] = $r;
	}
	$con->sql_freeresult($q1);

	print json_encode($result);
}

function ms($str,$null_if_empty=0)
{
	if (trim($str) === '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", $str);
	$str = str_replace("\\", "\\\\", $str);
	return "'" . (trim($str)) . "'";
}

function mi($intv,$null_if_empty=0)
{
	if ($intv == '' && $null_if_empty) return "null";
	$intv = str_replace(",","",$intv);
	settype($intv, 'int');
	return $intv;
}
?>
