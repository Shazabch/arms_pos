<?php
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
set_time_limit(0);

$BRANCH_CODE = 'JITRA';
$sku_item_id = 4;

$con->sql_query("select id from branch where code = ".ms($BRANCH_CODE));
$r = $con->sql_fetchrow();
if (!$r) die("Invalid branch ".BRANCH_CODE);
$branch_id = $r[0];

$mcon = new mysql_multi();

print "creating temp<br />\n";
$a = $mcon->sql_query("create temporary table tmp_pos_items select sum(qty) as qty, sku_item_code from pos_transaction where branch_id=$branch_id group by sku_item_code");
if ($a)
{
	$mcon->sql_query("alter table tmp_pos_items add index(sku_item_code)");
	print "done creating temp\n";
}
else
{
	print "temp exists\n";
}
print "getting item<br />\n";
$mcon->sql_query("select sku_items.sku_item_code, sku_items.description, qty from sku_items left join tmp_pos_items using (sku_item_code) order by qty desc");

//select * from sku_items where sku_item_code not in (select sku_item_code from tmp_pos_items) 

//print "<table>";
while($r=$mcon->sql_fetchrow())
{
	//print "<tr><td>$r[sku_item_code]</td><td>$r[description]</td></tr>";
	print "$r[sku_item_code]\t$r[qty]\t$r[description]\n";
}
//print "</table>";

print "$query_count\n$query_time";
?>
