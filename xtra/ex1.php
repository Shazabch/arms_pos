<?
include("include/common.php");

$r1 = $con->sql_query("select count(*) as c, link_code, sku_items.description, d.description as dept from sku_items left join sku on sku_id = sku.id left join category on category_id = category.id left join category d on category.department_id = d.id where link_code is not null and 	sku_apply_items_id is null and d.tree_str like '(0)(1)%' group by link_code, description having c>1");

$f=fopen("t.csv","w");
while ($r=$con->sql_fetchrow($r1))
{
	fputs($f, "$r[1],$r[2],$r[3]\n");
	$con->sql_query("select sku_item_code, mcode,link_code,artno from sku_items where link_code = $r[1]");
	while ($r2 = $con->sql_fetchrow())
	{
		fputs($f, "$r2[2],$r2[1],$r2[3],$r2[0]\n");
	}
	fputs($f, "\n");
}
fclose($f);
?>
