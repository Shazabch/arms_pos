<?php
ini_set("display_errors", 0);
ini_set("memory_limit", "256M");

header('Last-Modified: '.date('r', time()));

$folder = '';
if(isset($_REQUEST['folder']))	$folder = trim($_REQUEST['folder'])."/";
print file_get_contents($folder.strtolower($_REQUEST['branch'])."_weight.csv");
?>