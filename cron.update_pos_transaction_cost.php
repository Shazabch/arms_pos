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

	
$con = connect_db($read_mysql?$read_mysql:$db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);


$arg = $_SERVER['argv'];

if($arg['1']=='all'){
	$where='';
	load_all();	
}
else if($arg['1']){
	$where='(grn_cost is null or grn_cost=0)';
	load_branch($arg[1]);
}
else{
	$where='(grn_cost is null or grn_cost=0)';
	load_all();
}

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
	$q1=$con->sql_query("select timestamp, sku_item_code, branch_id, year, month, day from pos_transaction where branch_id = $bid and $where group by sku_item_code, branch_id, year, month, day");
	$n = $con->sql_numrows($q1);
	print "$n rows to process\n";
	while ($r1 = $con->sql_fetchrow($q1)){
		process($n, $r1, $bid);
	}
	$con->sql_freeresult($q1);		
}

function process(&$n, $r1, $bid)
{
	global $con;
	
	print "$n\r";
	$n--;
	$q2=$con->sql_query("select id, cost_price as cost from sku_items where sku_item_code='$r1[sku_item_code]'");
	$r2 = $con->sql_fetchrow($q2);
	
	if($r2){
		$q3=$con->sql_query("select grn_cost, avg_cost
	from sku_items_cost_history
	where date<='$r1[timestamp]' and sku_item_id=$r2[id] and branch_id=$bid order by date desc limit 1");
		$r3 = $con->sql_fetchrow($q3);
		if(!$r3){
			$grn_cost=$r2['cost'];
			$avg_cost=0;		
		}
		else{
			$grn_cost=$r3['grn_cost'];
			$avg_cost=$r3['avg_cost'];
		}		
		$con->sql_query("update pos_transaction set grn_cost='$grn_cost', avg_cost='$avg_cost' where sku_item_code=$r1[sku_item_code] and branch_id=$r1[branch_id] and year=$r1[year] and month=$r1[month] and day=$r1[day]");
		//echo "update pos_tarnsaction for $r1[sku_item_code] in $r1[timestamp]\n";		
	}
	else{
		echo "Fail to update pos_tarnsaction for $r1[sku_item_code] in $r1[timestamp]\n";
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
