<?
// SQL codes
define('TERMINAL',1);
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);
include("../config.php");
require("../include/mysql.php");
set_time_limit(0);

if (isset($_FILES['upload']))
{
	$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);
	get_codes();
} 
?>
<h1>Stock Check List</h1>
Usage: Upload the data collector list and click Submit. <br>System will match UPC and append ARMS Code + Description to the uploaded file.
<form enctype="multipart/form-data" method="post">
<input name=upload type=file size=40> <input type=submit>
</form>
<?


function get_codes()
{
	Header('Content-Type: application/vnd.ms-excel');
	Header('Content-Disposition: inline;filename=arms'.time().'.csv');
	global $con;
	foreach(file($_FILES['upload']['tmp_name']) as $line)
	{
		$ll = preg_split("/\s*,\s*/", trim($line));
		if ($ll[2]=='') continue;
		$con->sql_query("select sku_item_code, description from sku_items where sku_item_code = ".ms($ll[2])." or mcode = ".ms($ll[2]));
		$r = $con->sql_fetchrow();
		print "\"$ll[0]\",\"$ll[1]\",\"$ll[2]\",\"$r[0]\",\"$r[1]\"\n";		
	}
	exit;
}

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


?>
