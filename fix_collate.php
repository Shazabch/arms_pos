<?php
/*
11/15/2017 10:41 AM Andy
- Enhanced to check arguments -is_fix then only will alter table.
*/
set_time_limit(0);

ini_set('display_errors', 1);

define('TERMINAL',1);
define('NO_OB',0);

include("include/common.php");

$is_fix = false;
$total_tbl_count = 0;
$tbl_need_fix = 0;
$tbl_fixed = 0;
$tbl_ok = 0;

// get arguments
$arg = $_SERVER['argv'];
// remove 1st arguments
$a = array_shift($arg);

while($a = array_shift($arg)){
	if(preg_match('/^-is_fix/', $a)){	// date
		$is_fix = true;
	}else{
		die("Unknown option $a\n");
	}
}

$x = $con->sql_query("show tables");
while ($r=$con->sql_fetchrow($x))
{
	$total_tbl_count++;
	
	$con->sql_query("show create table `$r[0]`");
	$a = $con->sql_fetchfield(1);
	if (preg_match('/COLLATE=latin1_general_ci/', $a))
	{
		print "$r[0] already OK\n";
		$tbl_ok++;
		continue;
	}
	
	if($is_fix){
		print "Fix $r[0]\n";
		$sql = "alter table `$r[0]` convert to charset latin1 collate latin1_general_ci";
		if($config['single_server_mode']){
			$con->sql_query($sql);
		}else{
			$con->sql_query_skip_logbin($sql);
		}
		
		$tbl_fixed++;
		
		// fix one only
		//break;
	}else{
		print "$r[0] need fix\n";
		$tbl_need_fix++;
	}
}

print "\n";
print "Total Table: $total_tbl_count\n";
if($tbl_need_fix>0)	print "Total Table Require Fix: $tbl_need_fix\n";
if($tbl_fixed>0)	print "Total Table Fixed: $tbl_fixed\n";
if($tbl_ok>0)	print "Total Table OK: $tbl_ok\n";
print "Done.\n";

?>
