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

foreach (split(",", "hq,gurun,baling,tmerah,dungun,kangar,jitra") as $site)
{
    	$s1 = "akad$site";
	$port = 4001;
	if ($site == 'hq')
	{
	 $port = 3306;
	}
	$con = connect_db("$s1.no-ip.org:$port", 'arms_slave', 'arms_slave','armshq');

	$t = $con->sql_query("show tables");
	while($r=$con->sql_fetchrow($t))
	//foreach(array("membership","membership_history") as $table)
	{
		$table = $r[0];
		if ($con->sql_query("select count(*),max(id) from $table",false,false))
		{    
			$c = $con->sql_fetchrow();
		}
		else if ($con->sql_query("select count(*) from $table",false,false))
		{    
			$c = $con->sql_fetchrow();
		}
		else
		{
			$c = array(-1,-1);
		}
		$count[$site][$table] = $c[0];
		$maxid[$site][$table] = $c[1];
		$tb[$table] = 1;
	}
}

print "<table border=1 cellspacing=0 cellpadding=2>";
print "<tr><th>&nbsp;</th>";
foreach (split(",", "hq,gurun,baling,tmerah,dungun,kangar,jitra") as $b)
{
    print "<th>$b</th>";
}
print "</tr>";
foreach(array_keys($tb) as $t)
{
	print "<tr>";
    print "<td><b>$t</b></td>";
	foreach (split(",", "hq,gurun,baling,tmerah,dungun,kangar,jitra") as $b)
	{
	    print "<td ";
	    if ($count[$b][$t] > $count['hq'][$t]) print "bgcolor=#ffff00";
	    elseif ($count[$b][$t] < $count['hq'][$t]) print "bgcolor=#00ff00";
		print ">".$count[$b][$t]." (".$maxid[$b][$t].")</td>";
	}
}
print "</table>";
?>
