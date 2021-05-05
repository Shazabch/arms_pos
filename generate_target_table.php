<?php
define('TERMINAL',1);
include("include/common.php");
include("include/class.report.php");

print "checking pos\n";
$con->sql_query("select distinct(year(date)) as year from pos");
$a = $con->sql_fetchrowset();
$con->sql_freeresult();

$con->sql_query("select id from branch");
$b = $con->sql_fetchrowset();
$con->sql_freeresult();

foreach ($b as $bid)
{
foreach ($a as $year)
{
	Report::generate_target_table($bid[0],$year[0]);
}
}
?>
