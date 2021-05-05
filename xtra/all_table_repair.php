<?
include("../config.php");

set_time_limit(0);

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("../include/mysql.php");

function connect_db($server, $u, $p, $db)
{
	$con = new sql_db($server, $u, $p, $db, true);
	if(!$con->db_connect_id)
	{
		print date("[H:i:s m.d.y] ");
		print("Error: Could not connect to database $db@$server\n");
		return false;
	}
	return $con;
}

foreach (split(",", "hq,baling,gurun,dungun,kangar,tmerah") as $site)
{
	$port = 4001;
	$s1 = $site;
	if ($site == 'hq')
	{
	 $port = 5001;
	 $s1='gurun';
	}
	$con = connect_db("akad$s1.no-ip.org:$port", $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

	$t = $con->sql_query("show tables");
	while($r=$con->sql_fetchrow($t))
	{
		$con->sql_query("repair table $r[0]",false,false);
		$c = $con->sql_fetchrow();

		$count[$site][$r[0]] = $c[2];
		$maxid[$site][$r[0]] = $c[3];
		$tb[$r[0]] = 1;
	}
}

print "<table border=1 cellspacing=0 cellpadding=2>";
print "<tr><th>&nbsp;</th>";
foreach (split(",", "hq,baling,gurun,dungun,kangar,tmerah") as $b)
{
    print "<th>$b</th>";
}
print "</tr>";
foreach(array_keys($tb) as $t)
{
	print "<tr>";
    print "<td><b>$t</b></td>";
	foreach (split(",", "hq,baling,gurun,dungun,kangar,tmerah") as $b)
	{
	    print "<td ";
	    if ($count[$b][$t] > $count['hq'][$t]) print "bgcolor=#ffff00";
	    elseif ($count[$b][$t] < $count['hq'][$t]) print "bgcolor=#00ff00";
		print ">".$count[$b][$t]." (".$maxid[$b][$t].")</td>";
	}
}
print "</table>";
?>
