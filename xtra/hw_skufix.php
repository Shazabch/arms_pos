<?
include("config.php");
set_time_limit(0);


// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("include/mysql.php");

$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

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

// search for mcode with ^2
// if this is the only one, keep (remove mcode)
// if this have other subitem, remove it and pull the items up
$r1=$con->sql_query("select * from sku_items where mcode like '2%'") or die(mysql_errors());
print "total rows: ".$con->sql_numrows();
while($r=$con->sql_fetchrow($r1))
{
        print "<li> $r[sku_item_code] $r[mcode] $r[sku_id]";
        $con->sql_query("select count(*) from sku_items where sku_id = $r[sku_id]");
        $c = $con->sql_fetchrow();
        print " $c[0]";
        if ($c[0]==1)
        {
            $con->sql_query("update sku_items set mcode='' where id=$r[id]");
        }
        else
        {
            $con->sql_query("delete from sku_items where id=$r[id]");
            print "<li> delete $r[id]";
            // renumber
            $r2 = $con->sql_query("select id from sku_items where sku_id = $r[sku_id] order by id");
            $code = sprintf(ARMS_SKU_CODE_PREFIX, $r['sku_id']) . "0000";
            while($a = $con->sql_fetchrow($r2))
            {
                $con->sql_query("update sku_items set sku_item_code = '$code' where id = $a[0]");
                print "<li> assign $code => $a[0]";
                $code++;
            }
        }
}
?>
