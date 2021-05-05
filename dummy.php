<?php
/*
5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.
*/
define("TERMINAL",1);
include("config.php");
if((version_compare(PHP_VERSION, '7.0.0', '>='))){
	require("include/mysqli.php");
}else{
	require("include/mysql.php");
}

function connect_db($server, $u, $p, $db){
	$con = new sql_db($server, $u, $p, $db, false);
	if(!$con->db_connect_id){
		print date("[H:i:s m.d.y] ");
		print("Error: Could not connect to database $u:$p@$server/$db\n".mysql_error());
		return false;
	}
	return $con;
}

$con = connect_db($read_mysql?$read_mysql:$db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

// session
$sessioninfo = array();
session_start();
$ssid = session_id();
if (!isset($_REQUEST['ac']))
	$con->sql_query("update session set last_active=CURRENT_TIMESTAMP where ssid = '$ssid'");
else
	$con->sql_query("update login_tickets set last_update=CURRENT_TIMESTAMP where ssid = '$ssid'");

if($con){
	echo "OK";
}
?>
