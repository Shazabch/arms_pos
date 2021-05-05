<?php
// turn off output buffering and time limit
ini_set('memory_limit','256M');
ini_set('display_errors',1);
set_time_limit(0);
//ob_end_flush();


include("../include/mysql.php");

// get list from source #1
print "getting duplicate codes..\n";
//$con1 = new sql_db("cwmhq.no-ip.org:4001", "arms", "sc440", "armshq") or die(mysql_error());
$con1 = new sql_db("hiwaybh.no-ip.org:4001", "arms", "4383659", "armshq");
//$con1 = new sql_db("localhost", "root", "", "armshq");
if (!$con1)
{
	die(mysql_error());
}
$rs1=$con1->sql_query("select sku_item_code, sku_id, count(*) as c from sku_items group by sku_item_code, sku_id having c>1 ");
while($r=$con1->sql_fetchrow($rs1))
{
	print "$r[0] $r[1] $r[2]\n";
/*	if (substr($r[0],-4) != '0000') 
	{
	print "Not ZERO - ";/
	print "$r[0] $r[1] $r[2]\n";
	}*/
	$rs2=$con1->sql_query("select id, sku_item_code from sku_items where sku_id = $r[1] order by id");
	$newcode = $r[0];  
	while($p=$con1->sql_fetchrow($rs2))
	{
		print "$p[0] $p[1] => $newcode\n";
	//	$con1->sql_query("update sku_items set sku_item_code = '$newcode' where id = $p[0]");
		$newcode++;
	}
}
?>
