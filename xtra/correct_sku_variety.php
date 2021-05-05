<?
include("../config.php");
set_time_limit(0);


// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("../include/mysql.php");

//$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

$con = connect_db(HQ_MYSQL, $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

function ms($str,$null_if_empty=0)
{
	if ($str == '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", stripslashes($str));
	return "'" . (trim($str)) . "'";
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

// find sku which have item 0 same description with its application's variety-0 
$r1 = $con->sql_query("select * from sku where remark = 'New Application' order by id");
while($sku = $con->sql_fetchrow($r1))
{
	$con->sql_query("select description from sku_items where sku_id = $sku[id] order by sku_item_code");
	if ($con->sql_numrows()<=1) continue; // skip if only 1 variety
	$count = $con->sql_numrows();
	$it1 = $con->sql_fetchrow();
	
	$con->sql_query("select description from sku_apply_items where sku_id = $sku[id] order by id limit 1");

	$it2 = $con->sql_fetchrow();
	if ($it2)
	{
		if ($it2[0] != $it1[0])
		{
			print "fix sku $sku[id] => add $it2[0] (first: $it1[0], total $count)<br>";
			/*$con->sql_query("update sku_items set sku_item_code = sku_item_code + 1 where sku_id = $sku[id]");
			$con->sql_query("insert into sku_items (sku_id,sku_apply_items_id, receipt_description,sku_item_code, mcode, link_code, artno, description, selling_price, cost_price) select sku_id, sku_apply_items_id, receipt_description,sku_item_code-1, mcode, link_code, artno, ".ms($it2[0]).", selling_price, cost_price from sku_items where sku_id = $sku[id] order by sku_item_code limit 1");*/
			exit;
		}
	}
}

?>
