<?php
ini_set("display_errors",0);
include("config.php");
$grab = BRANCH_CODE;

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("include/mysql.php");

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

function dump_subs($front, $root_id)
{
	global $con;

	$res = $con->sql_query("select id, description from category where root_id = $root_id");
	while($r=$con->sql_fetchrow($res))
	{
		print "$r[0],$front$r[1]\n";
		dump_subs("$front$r[1],",$r[0]);
	}
}

dump_subs("", 0);

?>
