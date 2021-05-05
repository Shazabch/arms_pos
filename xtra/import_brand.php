<?

include("include/common.php");

// if not HQ, connect to HQ
$con = connect_db("akadgurun.no-ip.org:5001", 'arms', '793505', 'armshq');

$con->sql_query("truncate brand");
foreach (file("brands.csv") as $line)
{
	if (trim($line) == '') continue;
	$m = split(",", $line);
	if ($m[1] == '') continue;
	$con->sql_query("insert into brand (code, description, active) values (".ms(trim($m[0])).",".ms(trim($m[1])).",1)");
	print "<li> add $m[1] $m[0]";
	$c++;
}
print $c;

?>
