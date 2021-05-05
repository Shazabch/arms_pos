<?php
/*
6/20/2018 2:25 PM Andy
- Add into standard sync server svn.
*/
define('TERMINAL',1);
require("pdo_db.php");
require("function.php");
require("config.php");

run();

function run(){
	global $con, $hq_db_default_connection;

	$hqcon=connect_db("mysql:dbname=$hq_db_default_connection[3];host=$hq_db_default_connection[0]", $hq_db_default_connection[1],$hq_db_default_connection[2]);

	if (!$hqcon){
		print "Unable connect to backend server\n";
		exit;
	}

	// backend server time
	$hqcon->query("select CURRENT_TIMESTAMP as hq_time");
	$backend_clock_info = $hqcon->sql_fetchassoc();
	$backend_clock = $backend_clock_info['hq_time'];

	// sync server time
	$clock = date("Y-m-d H:i:s");

	print "Sync server time: $clock\n";
	print "Backend server time: $backend_clock\n";

	// find the time difference between sync and backend server by minute
	$time_diff = abs(strtotime($backend_clock)-strtotime($clock)) / 60;

	// if found sync server having 5 minutes different with backend, auto set time from sync server to follow backend
	if ($time_diff >= 5){
		passthru("date -s '$backend_clock'");
		print "Changed sync server time from $clock to $backend_clock\n";
	}else{
		print "Sync server time is up-to-date.\n";
	}
}

function connect_db($server, $u, $p)
{
	$conn = new pdo_db($server, $u, $p);

	if(!$conn->resource_obj)
	{
		print date("[H:i:s m.d.y] ");
		print("Error: Could not connect to database $db@$server\n");
		return false;
	}
	return $conn;
}
?>