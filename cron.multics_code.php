<?php
/*
5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.
*/
include("config.php");

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

if((version_compare(PHP_VERSION, '7.0.0', '>='))){
	require("include/mysqli.php");
}else{
	require("include/mysql.php");
}

while (1)
{
	$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);
	
	print date("[H:i:s m.d.y] ");
	if (!$con) 
	{
		print "Unable to connect to DB\n";
	    sleep(10);
	    continue;
	}
	$ff = @file("brands.no_multics");
	if (!$ff)
	{
		print "Error: Please create brands.no_multics\n";
		sleep(10);
		continue;
	}
	$brandlist = trim($ff[0]);
	$vendorlist = trim($ff[1]);
	$deptlist = trim($ff[2]);
	print "$brandlist - ";
       $con->sql_query("update sku_items left join sku on sku_id = sku.id left join category dept on category_id = dept.id set sku_items.link_code = '-' where (sku_items.link_code = '' or sku_items.link_code is null) and brand_id in ($brandlist) and department_id in ($deptlist) and vendor_id in ($vendorlist) and sku_type='CONSIGN'");
	print "Affected: " . $con->sql_affectedrows();
	print "\n";
	sleep(3600);
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
?>
