<?

//include("../config.php");

set_time_limit(0);

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("../include/mysql.php");

function connect_db($server, $u, $p, $db)
{
	$con = new sql_db($server, $u, $p, $db, false);
	if(!$con->db_connect_id)
	{
		print date("[H:i:s m.d.y] ");
		print("Error: Could not connect to database $db@$server\n");
		print mysql_error();
		return false;
	}
	return $con;
}

// if not HQ, connect to HQ
$con = connect_db("localhost", 'root', '', 'armshq');
$x1=$con->sql_query("select sku_items.id, sku_id,category_id, mcode from sku_items left join sku on sku_id = sku.id where category_id<4969 and mcode <> '' and mcode is not null order by sku_id");
$xx = $con->sql_fetchrowset();
$n = count($xx);
print "N = $n\n";
$con2 = connect_db("pktpt.no-ip.info:4001", "arms_pkt", "585858", "arms_pkt");
$con2->sql_query("select id, code from category where code like '%A'");
while($r=$con2->sql_fetchrow())
{
	$cmap[$r[1]] = $r[0];
	print "$r[1] = $r[0]\n";
}

foreach($xx as $r)
{
	print "$n. $r[0] $r[1] $r[2] $r[3] ";
	$con2->sql_query("select sku_id from sku_items where mcode = '$r[mcode]'") or die(mysql_error());
	if ($x = $con2->sql_fetchrow())
	{
		$ccc=$cmap[$r['category_id'].'A'];
		if ($ccc==0) continue;
		$con2->sql_query("update sku set category_id = $ccc where id = $x[sku_id]") or die(mysql_error());
		print "> update-".$con2->sql_affectedrows()."\n";
	}
	else
	{
		print "> no match\n";
	}
	$n--;
}

