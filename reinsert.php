<?
/*
=============== USAGE EXAMPLE ============================
Preview Mode
php reinsert.php -p "select * from user where id=310"

Process Mode
php reinsert.php -y "select * from user where id=310" user
==========================================================

8/11/2011 11:56:20 AM Andy
- Enhance script to compatible to all customer database.
*/
ini_set('memory_limit','256M');
ini_set('display_errors', 0);
set_time_limit(0);
error_reporting (E_ALL ^ E_NOTICE);
define('TERMINAL', 1);

include("include/common.php");

//$con = new sql_db("localhost", "arms", "793505", "armshq") or die(mysql_error());
$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

// check option, only can -p or -y
$mode = trim($_SERVER['argv'][1]);
if ($mode != '-p' &&  $mode != '-y') die("Invalid option, -p or -y only\n");

// sql to select from
$sql = trim($_SERVER['argv'][2]);
if(!$sql)	die("Invalid SQL\n");

// table to perform replace
$tbl_replace = trim($_SERVER[argv][3]);
if($mode=='-y' && !$tbl_replace)	die("Invalid replace to table\n");

$r1=$con->sql_query($sql);
$n=0;
while($r=$con->sql_fetchassoc($r1)){
        if ($mode == '-p') print_r($r);
        if ($mode == '-y'){
        	// try make empty field to null
			if(!$con->sql_query("replace into $tbl_replace ".mysql_insert_by_field($r, false, true),false,false)){
				// do not make empty field to null, try replace again
				$con->sql_query("replace into $tbl_replace ".mysql_insert_by_field($r));
			}
		} 
        $n++;
}
print "$n rows updated\n";


?>
