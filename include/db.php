<?php
/*
11/5/2013 3:04 PM Justin
- Enhanced to not die if failed to connect db for offline mode.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.
*/

	// SQL codes
	define('BEGIN_TRANSACTION', 1);
	define('END_TRANSACTION', 2);
	define('IN_TRANSACTION', 3);

	if(defined('use_mysqli') || (version_compare(PHP_VERSION, '7.0.0', '>='))){
		require("mysqli.php");
	}else{
		require("mysql.php");
	}
	

	$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);


function connect_db($server, $u, $p, $db, $die_on_failed=true)
{
	$con = new sql_db($server, $u, $p, $db, false);
	
	if(!$con->db_connect_id){
		if($die_on_failed){
			if (!defined('TERMINAL'))
				die("<p>Error: Could not connect to database $db@$server<br>" . mysql_error()."</p>");
			else
				die("Error: Could not connect to database $db@$server\n" . mysql_error()."\n");
			exit;
		}else return false;
	}
	
	// add timezone settings
	/*if (date_default_timezone_get()!='Asia/Kuala_Lumpur')
    {
		$now = new DateTime();
		$mins = $now->getOffset() / 60;
		$sgn = ($mins < 0 ? -1 : 1);
		$mins = abs($mins);
		$hrs = floor($mins / 60);
		$mins -= $hrs * 60;
		$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
		$con->sql_query("SET time_zone='$offset';");
		//print "adjust time zone $offset";
	}*/
	return $con;
}
?>