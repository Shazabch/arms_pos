<?php

include("include/common.php");
$con = connect_other_db("akadgurun.no-ip.org:5001");
$con->sql_query("truncate brand_commission");
$con->sql_query("select id, code from brand");
while ($r=$con->sql_fetchrow())
{
	$brandmap[$r[1]] = $r[0];
}

$f = fopen("../trade_discount.csv", "r");
while ($csv = fgetcsv($f, 1000))
{
	if ($csv[2] && $brandmap[$csv[0]])
	{
	    $bid = $brandmap[$csv[0]];
	    for ($i=1;$i<=5;$i++)
	    {
	    	print("<li> insert into brand_commission values ($i,$bid,'$csv[1]',$csv[2])");
			$con->sql_query("insert into brand_commission values ($i,$bid,'$csv[1]',$csv[2])");
		}
			
	}
	else
	{
	    print "<li> <font color=red>Canot find brand $csv[0]</font>";
	}
}

?>
