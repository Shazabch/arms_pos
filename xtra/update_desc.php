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

//$con = connect_db(HQ_MYSQL, $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

print "Read from $_REQUEST[f]\n";
$f = fopen($_REQUEST['f'], "r");
if (!$f)
{
    die("Error reading file. Please contact administrator.");
}
$cols = fgetcsv($f, 1024, ","); // skip 1st line
foreach ($cols as $c)
{
	$hd[$n] = trim(strtolower($c));
	$n++;
}

while ($cols = fgetcsv($f, 1024, ","))
{
	$n = 0;
	foreach ($cols as $value)
	{
		$r[$hd[$n]] = trim($value);
		$n++;
	}

	if (trim($cols[0])=="") continue;
	
	$con->sql_query("update sku_items set receipt_description = ".ms($r['description']). ", description = ".ms($r['description']). " where mcode = ".ms($r['m-code'])." or link_code = ".ms($r['multics code']));

	print "\n<li>$r[description] - " . $r['m-code'] . " - " . $r['multics code'] . " : " . $con->sql_affectedrows();
	
	$und += $con->sql_affectedrows();
}

print "<br>done: $und";
?>
