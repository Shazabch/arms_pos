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
	if (count($cols)<15) continue;
	$cols[0] = substr(trim($cols[0]), 0, 12);
	$uu[$cols[0]][0] = $cols[9];
	$uu[$cols[0]][1] = $cols[10];
}
fclose($f);
print "Reading from DB...";
$r1 = $con->sql_query("select left(link_code,12),id from temp_sku_items");
while ($r=$con->sql_fetchrow($r1))
{
	if (isset($uu[$r[0]]))
	{
		$t = $uu[$r[0]];
		$con->sql_query("update temp_sku_items set cost_price=$t[0], selling_price=$t[1] where id = $r[1]");
		$und++;
	}
	else
	{
		$unc++;
	}		
}
print "<br>done: $und  not done: $unc";
?>
