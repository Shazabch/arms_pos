<?
include("include/common.php");
set_time_limit(0);

switch (BRANCH_CODE)
{
	case 'HQ': $bid = 1; break;
	case 'GURUN':$bid = 4; break;
	case 'BALING': $bid = 2; break;
	case 'TMERAH': $bid = 7; break;
	case 'JITRA': $bid = 6; break;
	case 'DUNGUN': $bid = 3; break;
	case 'KANGAR': $bid = 5; break;
}

tb_clone("log");
tb_clone("counter_inventory");
tb_clone("counter_settings");
tb_clone("membership_inventory_history");
tb_clone("membership_receipt");
tb_clone("membership_receipt_items");
tb_clone("membership_drawer_history");

function tb_clone($tbname)
{
	global $con, $bid;
	
	print "Cloning $tbname<br />";
	$rs1 = $con->sql_query("select * from $tbname where branch_id = $bid");
	print $con->sql_numrows($rs1)." Records to be cloned.<br />";
	while($x = $con->sql_fetchrow($rs1))
	{
	    $con->sql_query("replace into $tbname ".mysql_insert_by_field($x));
	}
	
	$con->sql_query("select count(*) from $tbname where branch_id = $bid");
	$x = $con->sql_fetchrow();
	print "$x[0] Records now in local.<br />";

}

?>
