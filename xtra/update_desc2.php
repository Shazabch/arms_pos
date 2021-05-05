<?
include("../config.php");
set_time_limit(0);

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("../include/mysql.php");

$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);


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

$rs1=$con->sql_query("select sku_id,id from sku_items where description is null or description = ''");
while($r=$con->sql_fetchrow($rs1))
{
	$con->sql_query("select description from sku_items where sku_id = $r[sku_id] and id <> $r[id] and not (description is null or description = '') limit 1");
	$r2 = $con->sql_fetchrow();
	if ($r2)
	{
		print "<li> $r[1] => $r2[0]";
		$con->sql_query("update sku_items set description = ".ms($r2[0]).",receipt_description = ".ms($r2[0])." where id = $r[1]");
	}
	else
	print "<li> $r[1] => no match";
	print "\n";
}

print "<br>done: $und";
?>
