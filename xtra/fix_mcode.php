<?php
ini_set("display_errors", 1);

require("config.php");
require("include/db.php");

$p=$con->sql_query("select distinct(pos_transaction.sku_item_code), artno_mcode, mcode from pos_transaction left join sku_items on pos_transaction.sku_item_code = sku_items.sku_item_code having mcode is null") or die(mysql_error());
print "<pre>";
while($r=$con->sql_fetchrow($p))
{
	//print "<li> $r[0] [$r[1]]\n";
	$r[1] = trim($r[1]);
	if ($r[1] == '' || $r[1] == '-')
	{
	    printf ("%15s%15s\n",$r[0],"??");
		continue;
	}
	$con->sql_query("select sku_item_code from sku_items where mcode = '$r[1]'") or die(mysql_error());
	$b = $con->sql_fetchrow();
	if ($b)
	{
	    $con->sql_query("update pos_transaction set sku_item_code = '$b[0]' where artno_mcode = '$r[1]'") or die(mysql_error());
	    printf ("%15s%15s\n",$b[0],$r[1]);
	}
	else
	{
		printf ("%15s%15s\n","??",$r[1]);
	}
	
}
?>
