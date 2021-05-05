<?php
/*
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
	case 'validate_sn':
	    validate_sn();
	    exit;
}

function validate_sn(){
	global $con;
	
	$result = array();
	$sn = trim($_REQUEST['sn']);
	
	if(!$sn) return;
	
	$q1 = $con->sql_query("select * from pos_items_sn where serial_no = ".ms($sn));
	
	if($con->sql_numrows($q1) == 0){
		return;
	}

	while($r = $con->sql_fetchassoc($q1)){
		$q2 = $con->sql_query("select si.description as sku_description, sni.name, sni.nric, sni.address, sni.contact_no, sni.email, sni.warranty_expired
							   from sn_info sni
							   left join sku_items si on si.id = sni.sku_item_id
							   where sni.serial_no = ".ms($r['serial_no'])."
							   and sni.pos_id = ".mi($r['pos_id'])."
							   and sni.item_id = ".mi($r['pos_item_id'])."
							   and sni.branch_id = ".mi($r['pos_branch_id'])."
							   and sni.date = ".ms($r['date'])."
							   and sni.counter_id = ".mi($r['counter_id'])."
							   and sni.sku_item_id = ".mi($r['sku_item_id'])."
							   order by sni.date desc
							   limit 1");
		
		while($r1 = $con->sql_fetchassoc($q2)){
			$r1['remark'] = $r['remark'];
			$result[] = $r1;
		}
		$con->sql_freeresult($q2);
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
