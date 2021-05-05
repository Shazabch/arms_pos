<?
include("../config.php");
set_time_limit(0);

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("../include/mysql.php");

$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

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

//$con = connect_db(HQ_MYSQL, $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

print "Read from $_REQUEST[f]\n";
$f = fopen($_REQUEST['f'], "r");
if (!$f)
{
    die("Error reading file. Please contact administrator.");
}

while ($line = fgets($f, 1024))
{
	$cols = preg_split("/\s+/", $line);
	$cols[0] = substr(trim($cols[0]), 0, 12);

	$con->sql_query("update sku_items set selling_price = $cols[1] where left(link_code,12) = '$cols[0]'");
	if ($con->sql_affectedrows()>0)
	{
	    print "<li> replaced $cols[0]";
	}
}
fclose($f);

?>
