<?php
// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("../include/mysql.php");

$con = connect_db("gmark-hq.arms.com.my:4001","arms_slave","arms_slave","armshq");


function ms($str,$null_if_empty=0)
{
	if ($str == '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", stripslashes($str));
	return "'" . (trim($str)) . "'";
}


function connect_db($server, $u, $p, $db)
{
	$con = new sql_db($server, $u, $p, $db, false);
	if(!$con->db_connect_id)
	{
		print date("[H:i:s m.d.y] ");
		print("Error: Could not connect to database $db@$server\n");
		return false;
	}
	return $con;
}

$con->sql_query("select * from vendor") or die(mysql_error());
while($v = $con->sql_fetchrow())
{
	$vcode[$v['code']] = $v['id'];
}

$con->sql_query("select sku.id, vendor.code as vcode, sku_item_code, link_code, sku_items.description 
	from sku_items left join sku on sku_id = sku.id
	left join vendor on vendor_id = vendor.id") or die(mysql_error());
//file_put_contents('db.sz',serialize($con->sql_fetchrowset()));
$db = $con->sql_fetchrowset();
//$db= unserialize(file_get_contents('db.sz'));
foreach($db as $r)
{
	$data[$r['link_code']] = $r;
}

foreach(file("gmark OLDSKU.TXT") as $line)
{
	$a = preg_split("/\s*;\s*/",$line);
	if (!is_numeric($a[0])) continue;
	if (!$a[19]) continue;
	if (!isset($data[$a[0]])) { die ("no sku matching $a[0]\n"); }
	$b = $data[$a[0]];
	if ($b['vcode']!=$a[19]) 
	{
		$vid = $vcode[$a[19]];
		if (!$vid) print("-- invalid vendor $a[0]-->$a[19]\n");
		print "update sku set vendor_id=$vid where id = $b[id];\n";
		//print "update sku set vendor_id=$vid where id = $b[id]\n$a[0] $b[sku_item_code] ($b[vcode]=?$a[19]) $b[description]\n";
	} 
}
?>
