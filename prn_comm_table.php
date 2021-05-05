<?
include("include/common.php");

$dept = intval($_REQUEST['dept']);

$con->sql_query("select distinct brand_commission.department_id, category.description from brand_commission left join category on brand_commission.department_id = category.id order by category.description");
print "<form><select name=dept>";
while($r=$con->sql_fetchrow())
{
	print "<option value=$r[0]";
	if ($r[0] == $dept)
	{
	    $depts = $r[1];
	    print " selected";
	}
	print ">$r[1]</option>";
}
print "</select> <input type=submit></form>";

if (!$_REQUEST['dept'])
{
	exit;
}

$r1 = $con->sql_query("select distinct id, description from brand_commission left join brand on brand_id = brand.id");

while($r=$con->sql_fetchrow($r1))
{
	$r[0] = intval($r[0]);
	$r2 = $con->sql_query("select branch.code, skutype_code, rate from brand_commission left join branch on branch_id = branch.id where brand_id = $r[0] and department_id = $dept");
	$arr = array();
	$br = array(); $sc = array();
	$nozero=0;
	while($c=$con->sql_fetchrow($r2))
	{
		$arr[$c[0]][$c[1]] = $c[2];
		$br[$c[0]] = 1;
		$sc[$c[1]] = 1;
		if ($c[2]>0) $nozero=1;
	}
	if ($nozero)
	{
		print "<table border=1 cellspacing=0 cellpadding=0 style=\"margin:5px; page-break-inside:avoid; float:left;\">";
		print "<tr><td>&nbsp;</td><td colspan=22><h3>Brand:$r[1]<br />Dept:$depts</h3></td></tr>\n";
		print "<tr><td>&nbsp;</td>";
		foreach (array_keys($sc) as $s)
		{
			print "<th colspan=2>$s</th>";
		}
		print "</tr>";
		foreach (array_keys($br) as $b)
		{
			print "<tr><td width=20><b>$b</b></td>";
			foreach (array_keys($sc) as $s)
			{
				if ($arr[$b][$s]==0)
					print "<td width=20>&nbsp;</td>";
				else
					print "<td width=20>".$arr[$b][$s]."</td>";
				print "<td width=20>&nbsp;</td>";
			}
			print "</tr>";
		}
		print "</table>";
	}
}
?>
