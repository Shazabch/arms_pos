<?

include("include/common.php");

// if not HQ, connect to HQ
$con = connect_db("akadgurun.no-ip.org:5001", 'arms', '793505', 'armshq');

$con->sql_query("truncate vendor");
foreach (file("../YSVENDOR.RPT") as $line)
{
	if (trim($line) == '') continue;
	$m = split("\|", $line);
	if ($m[0] == '') continue;
	$con->sql_query("insert into vendor (code, description, phone_1, phone_2, address, active) values (".ms(trim($m[0])).",".ms(trim($m[1])).",".ms(trim($m[2])).",".ms(trim($m[3])).",".ms(trim($m[4])."\n".trim($m[5])."\n".trim($m[6])).",1)");
	print "<li> add $m[1] $m[0]";
	$c++;
}
print $c;

?>
