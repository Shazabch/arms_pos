<?
include("../config.php");
set_time_limit(0);

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("../include/mysql.php");

//$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

$con = connect_db(HQ_MYSQL, $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

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

$c1 = $con->sql_query("select sku_id, count(*) from sku_items group by sku_id");
while ($r = $con->sql_fetchrow($c1))
{
	print "<li> $r[0] $r[1]";
	$con->sql_query("update sku set varieties = $r[1] where id = $r[0]");
}

?>
