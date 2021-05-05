<?php
include("include/common.php");
include("include/mysql.php");

$con_multi= new mysql_multi();
$tbl=$con_multi->sql_query("show tables");
if($tbl){
	echo "connected";
}

$con_multi->close_connection();
?>
