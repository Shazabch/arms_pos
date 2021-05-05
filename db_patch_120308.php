<?php

include("include/common.php");

if (!$config['single_server_mode'])
{
print "<br />adjustment_items...";
//$con->sql_query("alter table adjustment_items modify qty double default 0") or die(mysql_error());
}

print "<br />tmp_adjustment_items...";
$con->sql_query("create table if not exists tmp_adjustment_items (`id` int(11) NOT NULL auto_increment, `adjustment_id` int(11) NOT NULL, `branch_id` int(11) NOT NULL, `user_id` int(11) default NULL, `sku_item_id` int(11) default NULL, `qty` double default '0', PRIMARY KEY (`id`) )") or die(mysql_error());
$con->sql_query("alter table tmp_adjustment_items modify qty double default 0") or die(mysql_error());

if (!$config['single_server_mode'])
{
print "<br />bom_items...";
//$con->sql_query("alter table bom_items modify qty double default 0") or die(mysql_error());
}

print "<br />tmp_bom_items...";
$con->sql_query("create table if not exists tmp_bom_items (`id` int(11) NOT NULL auto_increment, `bom_id` int(11) NOT NULL default '0', `sku_item_id` int(11) NOT NULL default '0', `user_id` int(11) default NULL, `qty` double default '0', PRIMARY KEY (`id`))") or die(mysql_error());
$con->sql_query("alter table tmp_bom_items modify qty double default 0") or die(mysql_error());

if (!$config['single_server_mode'])
{
print "<br />sku_items_cost...";
//$con->sql_query("alter table sku_items_cost modify qty double default 0") or die(mysql_error());

print "<br />sku_items_cost_history...";
//$con->sql_query("alter table sku_items_cost_history modify qty double default 0") or die(mysql_error());
}

// self destruct :)
print "<br />Removing ".__FILE__;
if ($_SERVER['HOSTNAME'] != 'maximus') unlink(__FILE__);
?>
<br />OK.
