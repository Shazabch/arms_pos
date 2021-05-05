<?
ini_set("display_errors", 1);
set_time_limit(0);
require("config.php");
require("include/db.php");


$file=fopen("YS_CONS.RPT","r");
while($line = fgets($file, 1024))
{
	$ll = preg_split("/\s+/", trim($line));
	$ll[0] = substr($ll[0],0,12);
	$con->sql_query("select sku_id from sku_items where link_code = " . ms($ll[0]));
	$b = $con->sql_fetchrow();
	if ($b)
	{
	    $sid = $b[0];
	    print "<li> $ll[0] updating ";
	    /*$con->sql_query("update sku set sku_type = 'CONSIGN' where id = $sid and remark = 'Import from Multics'") or die(mysql_error());
	    print $con->sql_affectedrows();*/
	    
		$con->sql_query("update sku set default_trade_discount_code = ".ms($ll[1])." where id = $sid and remark = 'Import from Multics' and (default_trade_discount_code = '' or default_trade_discount_code is null)") or die(mysql_error());
		print " > $ll[1] " . $con->sql_affectedrows();
	}
	else
	{
	   // print "<li> $ll[0] not found";
	}
}

function ms($str) { return "'$str'"; }
?>
