<?php
include("../config.php");

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("../include/mysql.php");

print "connect db";
$con = connect_db('hq.aneka.com.my','arms','793505','armshq');
print "done\n";
//$con=connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);


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

function md($dmy)
{
	$a = split("\/",$dmy);
	$ymd = "$a[2]-$a[1]-$a[0]";
	return $ymd;
}
$con->sql_query("select id, sku_item_code from sku_items");
while($r=$con->sql_fetchrow())
{
	$sku[$r[1]] = $r[0];
}
$con->sql_freeresult();

$bid=7;
$bsz = ms(serialize(array(7=>'TMERAH')));

$con->sql_query("select title from promotion where branch_id=$bid");
while($p = $con->sql_fetchrow())
{
	$skip_promo[$p[0]] = 1; 
	print "skip $p[0]\n";
}


/*$con->sql_query("truncate promotion");
$con->sql_query("truncate promotion_items");*/
foreach (file("PS14974.TXT") as $line)
{
	$line = preg_split("/\s*\|\s*/", $line);
	if ($line[0]=='') continue;
	$pid = get_promo_id($line);
	if (!$pid) continue;
	
	$promo_col = ($line[1]=='Y') ? 'member_disc_p' : 'non_member_disc_p';
	$item = $sku[$line[4]];
	if (!isset($sku[$line[4]])) { print ($line[4]." not found<br />"); continue; }
	$con->sql_query("insert into promotion_items (branch_id,promo_id,user_id,sku_item_id,$promo_col) values ($bid,$pid,1,$item,'$line[5]%')");

}
foreach (file("BB14974.TXT") as $line)
{
	$line = preg_split("/\s*\|\s*/", $line);
	if ($line[0]=='') continue;
	$pid = get_promo_id($line);
	if (!$pid) continue;
	
	$promo_col = ($line[1]=='Y') ? 'member_disc_a' : 'non_member_disc_a';
	$item = $sku[$line[4]];
	if (!isset($sku[$line[4]])) { print ($line[4]." not found<br />"); continue; }
	$con->sql_query("insert into promotion_items (branch_id,promo_id,user_id,sku_item_id,$promo_col) values ($bid,$pid,1,$item,'$line[5]')");
}

function get_promo_id($line)
{
	global $promo_id, $con, $bid, $bsz, $skip_promo;
	if ($skip_promo[$line[0]]) return 0;
	if (!isset($promo_id[$line[0]]))
	{
		$batch = $line[0];
		$df = md($line[2]);
		$dt = md($line[3]);
		$n=0;
		
		$con->sql_query("insert into promotion (branch_id,user_id,title,date_from,date_to,time_from,time_to,approved,promo_branch_id,status) values ($bid,1,'$batch','$df','$dt','00:00','23:59',1,$bsz,1)");
		$pid = $con->sql_nextid();
		print "$batch => promo #$pid\n";
		$promo_id[$line[0]] = $pid;
	}
	
	return $promo_id[$line[0]];
}
?>
