<?
include("include/common.php");
ini_set("display_errors",1);
set_time_limit(0);

$rs1=$con->sql_query("select * from po_o where branch_id=7 and id > 5000 and id not in (select id from po where branch_id=7 and id > 5000 )");
while($r = $con->sql_fetchrow($rs1))
{
	$p = array();
	foreach ($r as $k=>$v)
	{
	    if (!is_numeric($k)) $p[$k] = $v;
	}
	$con->sql_query("insert into po ".mysql_insert_by_field($p));
	$x =  $con->sql_nextid();
	print "<li> po $p[id] ... =>$x \n";
}

?>
