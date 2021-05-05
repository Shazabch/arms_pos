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


$supp = array();
$con->sql_query("select id, code from vendor");
while($r = $con->sql_fetchrow())
{
	$supp[$r[1]] = $r[0];
}
print "supp: " . count($supp);


$linkcode = array();
$con->sql_query("select id, left(link_code,12) from sku_items");
while($r = $con->sql_fetchrow())
{
	$linkcode[$r[1]] = $r[0];
}
print "linkcode: " . count($linkcode);

$bid = $_REQUEST['b'];

print "Read from $_REQUEST[f]\n";

$f = fopen($_REQUEST['f'], "r");
if (!$f)
{
    die("Error reading file. Please contact administrator.");
}

while ($cols = fgetcsv($f, 1024, "|"))
{
	if (count($cols)!=15) continue;
	$cols[0] = substr($cols[0],0,12);
	$cols[12] = trim($cols[12]);
	// check supplier
	if (!isset($supp[$cols[12]]))
	{
		//print "supplier not found - $cols[12]";
		continue;
	}
	if (!isset($linkcode[$cols[0]]))
	{
		//print "linkcode not found - $cols[0]";
		continue;
	}
	$vid = $supp[$cols[12]];
	$sid = $linkcode[$cols[0]];
	$art = $cols[2];
	//print "<li> $cols[1] ";
	$con->sql_query("insert into vendor_sku_history (branch_id, vendor_id, sku_item_id, selling_price , cost_price,source,artno) values ($bid,$vid,$sid,$cols[10],$cols[9],'multx',".ms($art).")");
}
	print "OK!";

fclose($f);

?>
