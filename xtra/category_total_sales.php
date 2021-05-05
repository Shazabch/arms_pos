<?
// group sales by branch, arms_code
$con->sql_query("create table tmp_yearly_sales select branch_id, sku_item_code, sum(amount) as amount, sum(qty) as qty from pos_transaction where timestamp >= '2006-10-01' and timestamp < '2007-10-01' group by branch_id, sku_item_code");

// get detail (description, category) - 9sec
$con->sql_query("create table tmp_yearly_sales_final select branch.code, dept.description as dept, category.description as category, sku_item_code, sku_items.description as item, amount, qty 
into outfile '/home/ARMS/www/reports/supermarket2007.csv' fields terminated by '|'
from tmp_yearly_sales left join branch on branch_id = branch.id left join sku_items using (sku_item_code) left join sku on sku_id = sku.id left join category_cache using (category_id) left join category on category_cache.p3 = category.id left join category dept on category.department_id = dept.id where category_cache.p1 = 1 ;");

// search for sku without sales
$con->sql_query("select dept.description as dept, category.description as category, sku_item_code, sku_items.description as item, sku.added as created 
into outfile '/home/ARMS/www/reports/supermarket2007_nosales.csv' fields terminated by '|'
from sku_items left join  sku on sku_id = sku.id left join category_cache using (category_id) left join category on category_cache.p3 = category.id left join category dept on category.department_id = dept.id where category_cache.p1 = 1 and sku.added < '2007-10-01' and sku_items.sku_item_code not in (select  distinct sku_item_code from tmp_yearly_sales) order by category,sku_item_code;");


?>
