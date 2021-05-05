<?php

include("include/common.php");

$con->sql_query("update gra set returned=0,status=1 where returned=2");
print "<br />Change status: " . $con->sql_affectedrows();

$con->sql_query("update sku_items_cost set changed=1");
print "<br />Set changed: " . $con->sql_affectedrows();

if ($_SERVER['SERVER_NAME'] != 'maximus') unlink(__FILE__);
?>
