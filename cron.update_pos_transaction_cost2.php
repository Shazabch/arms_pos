<?php
/*
REVISION HISTORY
===============
12/5/2007 3:56:09 PM gary
- add option for all pos update

12/10/2007 4:13:19 PM yinsee
- gary forgot to add "global $where" in the functions!!
*/
define("TERMINAL",1);
include("config.php");
require("include/mysql.php");
ob_end_clean();

ini_set('display_errors',1);
	
$con = connect_db($read_mysql?$read_mysql:$db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

$arg = $_SERVER['argv'];

if($arg[1]=='all')
	load_all();	
else
	load_branch($arg[1]);

function load_branch($branch_code){
	global $con;
	$branch=strtoupper($branch_code);
	$con->sql_query("select id, code from branch where code='$branch'");
	$r0 = $con->sql_fetchrow();
	if (!$r0) { die("Invalid branch $branch\n"); }
	run($r0['id']);
}

function load_all(){
	global $con;
	$q0=$con->sql_query("select id, code from branch order by id");
	while($r0=$con->sql_fetchrow($q0))
	{
		print "Processing branch $r0[code]\n";
		run($r0['id']);
	}
}

function run($bid)
{
	global $con, $where;
	
	
	$q1=$con->sql_query("select date, sku_item_code, avg_cost, grn_cost, cost_price from sku_items_cost_history left join sku_items on sku_item_id = sku_items.id where sku_items_cost_history.branch_id = $bid order by sku_item_code, date");
	$n = $con->sql_numrows($q1);
	print "$n rows to process\n";
	$r0 = array();
	while ($r1 = $con->sql_fetchrow($q1)){
		process($n, $r0, $r1, $bid);
		$r0 = $r1;
	}
	process($n, $r0, array(), $bid);
	$con->sql_freeresult($q1);		
}

function process(&$n, $r0, $r1, $bid)
{
	global $con;
	global $last_code;

	print "$n...\r";
	$n--;	
	// if previous arms code is differerent from current arms code
	// update cost of previous sku-item after the previous date with the last avg / grn cost
	// update cost of current sku-item before the current date with cost-price (from master sku)
	/*print join (",", $r0)."\n";
	print join (",", $r1)."\n";*/
	if ($r0['sku_item_code'] != $r1['sku_item_code'])
	{
		if (isset($r0['sku_item_code'])) 
			$con->sql_query("update LOW_PRIORITY pos_transaction set grn_cost='$r0[grn_cost]', avg_cost='$r0[avg_cost]' where sku_item_code='$r0[sku_item_code]' and branch_id=$bid and timestamp >= '$r0[date]'");
		
		if (isset($r1['sku_item_code'])) 
			$con->sql_query("update LOW_PRIORITY pos_transaction set grn_cost='$r1[cost_price]', avg_cost='0' where sku_item_code='$r1[sku_item_code]' and branch_id=$bid and timestamp < '$r1[date]'");
	}
	else
	{
		$con->sql_query("update LOW_PRIORITY pos_transaction set grn_cost='$r0[grn_cost]', avg_cost='$r0[avg_cost]' where sku_item_code='$r1[sku_item_code]' and branch_id=$bid and timestamp between '$r0[date]' and '$r1[date]'");
	}
}


function connect_db($server, $u, $p, $db){
	$con = new sql_db($server, $u, $p, $db, false);
	if(!$con->db_connect_id)
	{
		print date("[H:i:s m.d.y] ");
		print("Error: Could not connect to database $u:$p@$server/$db\n".mysql_error());
		return false;
	}
	return $con;
}

function ms($str,$null_if_empty=0){
	if ($str == '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", $str);
	return "'" . (trim($str)) . "'";
}

?>
