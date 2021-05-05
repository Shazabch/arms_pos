<?php
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

print "Read from ".$_SERVER['argv'][1]."\n";

$f = fopen($_SERVER['argv'][1], "r");
if (!$f)
{
    die("Error reading file. Please contact administrator.");
}
else
{	// build input values
	$con->sql_query("truncate category");
	
	$root_id[0] = 0;
	$n=1;
    while ($cols = fgetcsv($f, 1024, ",")) {
		$level = 0;
		while ($cols)
		{
			$level++;
			$root = $root_id[$level-1];
			$desc =	trim(strtoupper(array_shift($cols)));
			if ($desc == '') continue;
			if (isset($have_id[$root.$desc]))
			{
			    $root_id[$level] = $have_id[$root.$desc];
				continue;
			}
			$con->sql_query("insert into category (root_id, level, code, description, active) values ($root, ".($level-1).", '$n', ".ms($desc).", 1)") or die(mysql_error());
			$id = $con->sql_nextid();
			$root_id[$level] = $id;
			$have_id[$root.$desc] = $id;
			$n++;
		}
    }
	fclose($f);
}
?>
