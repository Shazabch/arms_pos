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

while ($cols = fgetcsv($f, 1024, "|"))
{
	if (count($cols)<5) continue;
	$cols[0] = trim($cols[0]);
	$cols[1] = trim($cols[1]);
	print "<li> $cols[0] $cols[1]";
	$con->sql_query("select id from temp_sku_items where link_code = '$cols[0]' and mcode = '$cols[1]'");
	if ($con->sql_numrows()>0) continue;
	// if not found, find same multic code and duplicate
	$con->sql_query("insert into temp_sku_items (sku_id, sku_item_code, artno, mcode, link_code, description, selling_price, cost_price) select sku_id, sku_item_code+1, artno, '$cols[1]', link_code, description, selling_price, cost_price from temp_sku_items where link_code = '$cols[0]' order by sku_item_code desc limit 1");
	if ($con->sql_affectedrows()>0)
	{
	$newid = $con->sql_nextid();
	print "-> add as $newid";
	}
	else
	{
	print "-> canot find item with same link_code";
	}
}
fclose($f);
print "<br>done: $und  not done: $unc";
?>
