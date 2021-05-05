<?php
define("TERMINAL",1);
include("config.php");

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("include/mysql.php");

do {
	$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);
	if (!$con) 
	{
		print "Error: Cannot connect to database server, retry in 5 seconds...\n";
		sleep(5);
	}
} while (!$con);


$con->sql_query("select id from branch where code = ".ms(BRANCH_CODE));
$r = $con->sql_fetchrow();
if (!$r) die("Invalid branch ".BRANCH_CODE);
$bid = $r[0];

foreach (file("COSTMT.DAT") as $line)
{
	$cols = preg_split('/\s*;\s*/', $line);
	$type = $cols[1];
	$price = $cols[2];
	$cols[0] = ms($cols[0]);
	$cols[1] = ms($cols[1]);
	$cols[2] = ms($cols[2]);
	$cols[3] = ms($cols[3]);
	// code ; type ; selling; cost 
	$rs1 = $con->sql_query("select id from sku_items where sku_item_code = $cols[0] order by sku_item_code");
	if ($con->sql_numrows($rs1))
	{
		while($r=$con->sql_fetchrow($rs1))
		{
			print ">> updating selling for $cols[0] = $r[0] - ";
			$con->sql_query("select price,trade_discount_code from sku_items_price where branch_id = $bid and sku_item_id = $r[0]");
			$p = $con->sql_fetchrow();
			print "($p[0]/$sval/$cols[2]) ";
			if ($p['price']==$price && $p['trade_discount_code']==$type) { print "selling/type same, skipping\n"; continue; }	// skip if price is same
			
			$con->sql_query("replace into sku_items_price (branch_id, sku_item_id, last_update, price, cost, trade_discount_code) values ($bid, $r[0], CURRENT_TIMESTAMP, $cols[2], $cols[3], $cols[1])");
			$con->sql_query("insert into sku_items_price_history (branch_id, sku_item_id, added, price, cost, trade_discount_code, source) values ($bid, $r[0], CURRENT_TIMESTAMP, $cols[2], $cols[3], $cols[1], 'COSTMT.DAT')");
			print "updated\n";
		}
	}
	else
	{
		print "Error: no sku matches $cols[0]\n";
	}
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

function ms($str,$null_if_empty=0)
{
	if ($str == '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", $str);
	return "'" . (trim($str)) . "'";
}

// from link-code, find arms code
function find_arms_code($linkcode)
{
	global $con, $nomatch;
	
	$linkcode = trim($linkcode);
	
	
	$c = $con->sql_fetchrow();
	if (!$c)
	{
		print "$linkcode is not in sku database.\n";
		return false;
	}

	return $c[0];
}

?>
