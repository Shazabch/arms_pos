<?php
include("config.php");
set_time_limit(0);
$grab = BRANCH_CODE;

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("include/mysql.php");

$con = connect_db($db_default_connection[0], $db_default_connection[1],
$db_default_connection[2], $db_default_connection[3]);

$con->sql_query("select id from branch where code = '$grab'");
$rr = $con->sql_fetchrow();
$branch_id = $rr[0];
print "Branch ID = $branch_id\n";


while (1)
{
	$con = connect_db($db_default_connection[0], $db_default_connection[1],
$db_default_connection[2], $db_default_connection[3]);

	print date("[H:i:s m.d.y] ");
	if (file_exists('atp_promo_table.txt'))
	{
		// rename table before process
		rename('atp_promo_table.txt', 'atp_promo_table.txt.processing');
		$fp = fopen('atp_promo_table.txt.processing','r');
		$n=0;
		while(!feof($fp))
		{
		    $line = preg_split('/\s+\|\s+/',fgets($fp));
		    if ($line[0]=='') continue;
		    $con->sql_query("replace into atp_promotion_table (sku_item_code,promo_price,promo_discount,date_start,date_end) values ('".join("','",$line)."')");
		    $n++;
		}
		print "$n records insert\n";
		fclose($fp);
		unlink('atp_promo_table.txt.processing');
	}
	else
	    print "No new data\n";


	sleep(30);
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
