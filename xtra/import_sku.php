<?
include("../config.php");
set_time_limit(0);

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("../include/mysql.php");

//$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

$con = connect_db(HQ_MYSQL, $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

function ms($str,$null_if_empty=0)
{
	if ($str == '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", stripslashes($str));
	return "'" . (trim($str)) . "'";
}


function connect_db($server, $u, $p, $db)
{
	$con = new sql_db($server, $u, $p, $db, false);
	if(!$con->db_connect_id)
	{
		print date("[H:i:s m.d.y] ");
		print("Error: Could not connect to database $db@$server\n");
		return false;
	}
	return $con;
}

if (!isset($_REQUEST['u']) || !isset($_REQUEST['f']))
{
  show_form();
  exit;
}

$con->sql_query("select id from user where u = ".ms($_REQUEST['u']));
if ($r = $con->sql_fetchrow())
{
	$aid = $r[0];
	print "User id = $aid";
}
else
{
	die("Unknown user $_REQUEST[u]");
}

$supp = array();
$con->sql_query("select id, code from vendor");
while($r = $con->sql_fetchrow())
{
	$supp[$r[1]] = $r[0];
}
print "supp: " . count($supp);

$brand = array();
$con->sql_query("select id, description from brand");
while($r = $con->sql_fetchrow())
{
	$brand[$r[1]] = $r[0];
}
$brand[0] = 0;
$brand["UNBRAND"] = 0;
print "brand: " . count($brand);

$uom = array();
$con->sql_query("select id, code from uom");
while($r = $con->sql_fetchrow())
{
	$uom[$r[1]] = $r[0];
}
print "uom: " . count($uom);


print "Read from $_REQUEST[f]\n";

$f = fopen($_REQUEST['f'], "r");
if (!$f)
{
    die("Error reading file. Please contact administrator.");
}

$fw  = fopen($_REQUEST['f'].".err", "wt");

// get header
$cols = fgetcsv($f, 1024, ",");
dump($cols);
$n = 0;
$total_count = 0;
foreach ($cols as $c)
{
	$hd[$n] = trim(strtolower($c));
	$n++;
}

while ($cols = fgetcsv($f, 1024, ","))
{
	$n = 0;
	foreach ($cols as $value)
	{
		$r[$hd[$n]] = trim($value);
		$n++;
	}

	if (trim($cols[0])=="") continue;

	$r['brand desc'] = preg_replace("/[^a-z0-9\s\.\-]/i", "", $r['brand desc']);
	if ($r['brand desc'] == '') $r['brand desc'] = 'UNBRAND';
	if ($r['brand desc'] == 'UNBRAND') $r['brand'] = 0;

	// no uom, insert
	if ($uom[$r['uom']] == '')
	{
		$r['uom'] = 'EACH';
		$r['frac'] = 1;
	}

	if (!isset($uom[$r['uom']]))
	{
		$con->sql_query("insert into uom (code, fraction, description, active) values ('$r[uom]', $r[frac], '$r[uom]', 1)");
		$uom[$r['uom']] = $con->sql_nextid();
		print "<li> add uom $r[uom]";
	}

	if (!isset($brand[$r['brand desc']]))
	{
//		dump($cols);
//		print "<li> brand ".$r['brand desc'].$brand[$r['brand desc']]." not found";
		$con->sql_query("insert into brand (code, description, active) values (".ms($r['brand']).", ".ms($r['brand desc']).", 1)");
		$brand[$r['brand desc']] = $con->sql_nextid();
		print "<li> add brand ".$r['brand desc'];
	}

	if (!isset($supp[$r['supplier']]))
	{
		dump($cols);
		print "<li> supplier $r[supplier]".$supp[$r['supplier']]." not found";
		continue;
	}

	if (intval($r['cat code'])==0)
	{
		//dump($cols);
		//print "<li> cat code zero or empty";
		continue;
	}

	$con->sql_query("insert into sku (category_id, vendor_id, uom_id, brand_id, status, active, sku_type, apply_by, apply_branch_id, timestamp, added,remark) values (".$r['cat code']." ,".$supp[$r['supplier']].",".$uom[$r['uom']].",".$brand[$r['brand desc']].",1,1,'OUTRIGHT',$aid,1,CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP(),'Import from Multics')");
	$id = $con->sql_nextid();
	$skucode = 28000000 + $id;
	$con->sql_query("update sku set sku_code = '$skucode' where id = $id");

	$con->sql_query("insert into sku_items (sku_id, sku_item_code, mcode, link_code, artno, description, selling_price, cost_price) values ($id, '".$skucode."0000', ".ms($r['m-code']).",".ms($r['multics code']).",".ms($r['weight/article']).",".ms($r['description']).",".doubleval($r['sp']).",".doubleval($r['cost']).")");

	$total_count++;

}
print "<hr>$total_count inserted";

fclose($f);
fclose($fw);
rename($_REQUEST['f'], $_REQUEST['f'].".done");

show_form();
exit;

function dump($a)
{
	global $fw;
	fputs($fw, join(",",$a) . "\n");
}

function show_form()
{
?>
<form>
User ID: <input name=u><br>
File: <select name=f>
<?
$dir = "CSV";
if ($dh = opendir($dir)) {
       while (($file = readdir($dh)) !== false) {
          if (strstr($file, ".csv") && !strstr($file, ".csv.err") && !strstr($file, ".csv.done"))
            echo "<option value=\"$dir/$file\">$dir/$file</option>";
       }
       closedir($dh);
   }
   else
   {
    die("canot opendir");
   }
?>
</select>
<input type=submit>
</form>
<?
}
?>
