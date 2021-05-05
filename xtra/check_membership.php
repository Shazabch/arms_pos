<?php
ini_set("display_errors",1);
set_time_limit(0);

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("../include/mysql.php");

$con = connect_db("akadhq.no-ip.org:4001","arms","793505","armshq");


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

global $con;

print "Downloading data from HQ...\n";

$con->sql_query("select card_no, nric, name from membership order by card_no") or die(mysql_error());
print $con->sql_numrows() . " records\n";

while($r=$con->sql_fetchrow())
{
	$by_ic[$r['nric']] = $r;
	$by_card[$r['card_no']] = $r;
}
compare("AKVIP/TKGVIP");
compare("AKVIP/TDGVIP");
compare("AKVIP/TGRVIP");
compare("AKVIP/TKGVIP");
compare("AKVIP/TJTVIP");
compare("AKVIP/TTMVIP");

function compare($fn)
{
	print "---------------------------------------------------------------------\nComparing with $fn\n";
	global $by_ic, $by_card;
	
	foreach (file($fn) as $orgline)
	{
		$x = preg_split("/\s+/", trim($orgline));
		$line[0] = array_shift($x);
		if (!preg_match("/^AK/", $line[0])) continue;

		$line[1] = array_shift($x);
		$line[2] = join(" ", $x);
		
		$orgline =  join("|", $line);
		if (!isset($by_ic[$line[1]]))
		{
			print "$orgline - no such IC ($line[1]) in DB\n"; 
		}
		elseif ($by_ic[$line[1]]['card_no']!=$line[0])
		{
			$bc = $by_ic[$line[1]];
			print "$orgline - IC Same, Card not match ($bc[0]|$bc[1]|$bc[2])\n";
		}
		
		if (!isset($by_card[$line[0]]))
		{
			print "$orgline - no such Card ($line[0]) in DB\n"; 
		}
		elseif ($by_card[$line[0]]['nric']!=$line[1])
		{
			$bc = $by_card[$line[0]];
			print "$orgline - Card same, IC not match ($bc[0]|$bc[1]|$bc[2])\n";
		}
	}
}
/*
while()
$con->sql_query('select nric, card_no, name, date_format(issue_date,"%d/%m/%Y") as d1, date_format(next_expiry_date,"%d/%m/%Y") as d2 from membership order by card_no');
while ($r=$con->sql_fetchrow())
{
	printf("%-14s%-15s%-29s%-11s%-11s\n",$r[nric],$r[card_no],$r[name],$r[d1],$r[d2]);
}*/
?>
