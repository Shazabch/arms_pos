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

$res1 = $con->sql_query("select id from temp_sku order by id");
$COUNTER = 25204;
print "<table cellspacing=0 cellpadding=0 border=1>";
while ($r=$con->sql_fetchrow($res1))
{
  print "<tr><td>$r[id]</td><td>$COUNTER</td>";
  $sku_code = sprintf("28%06d",$COUNTER);
  $con->sql_query("update temp_sku set id=$COUNTER, sku_code='$sku_code' where id = $r[id]");
  $con->sql_query("update temp_sku_items set sku_id = $COUNTER, sku_item_code = concat('$sku_code',right(sku_item_code,4)) where sku_id = $r[id]");
  print "<td>".$con->sql_affectedrows()."</td>";
  print "<td>$r[id]</td></tr>";
  $COUNTER++;
}  
print "</table>";

?>
