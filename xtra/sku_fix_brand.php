<?

include("include/common.php");

// fix brand
$r1 = $con->sql_query("select id, description from brand order by id desc");
while($r=$con->sql_fetchrow($r1))
{
	$wrong = preg_replace("/[^a-z0-9\.\-]/i", "", $r[1]);
	if (isset($br[$wrong]) && $br[$wrong] > 0)
	{
		// change all sku using the wrong id $br[$wrong] to this id $r[0]
		$con->sql_query("update sku set brand_id = $r[0] where brand_id = $br[$wrong]");
		print "<li> ($wrong) $br[$wrong] -> ($r[1]) $r[0], update sku " . $con->sql_affectedrows();
		$con->sql_query("delete from brand where id = $br[$wrong]");
		$br[$wrong] = 0;
	}

	$br[$r[1]] = $r[0];
}


?>