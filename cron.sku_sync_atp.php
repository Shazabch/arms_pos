<?php
define('TERMINAL',1);
include("config.php");
$grab = BRANCH_CODE;

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("include/mysql.php");
// DO NOT MODIFY!!! OR ELSE SHIT YOU EAT
$szs = array(10,20,20,20,20,40,10,10,10,10);

$con = connect_db($db_default_connection[0], $db_default_connection[1],$db_default_connection[2], $db_default_connection[3]);

$con->sql_query("select id from branch where code = '$grab'") or die(mysql_error());
$rr = $con->sql_fetchrow();
$branch_id = $rr[0];
print "Branch ID = $branch_id\n";

while (1)
{
	$run_atp = 0;
	$con = connect_db($db_default_connection[0], $db_default_connection[1],
$db_default_connection[2], $db_default_connection[3]);

	print date("[H:i:s m.d.y] ");
	if (!$con)
	{
		print "Unable to connect to DB\n";
	    sleep(10);
	    continue;
	}
	// check new SKU
/*	$f = @file('atp.lastid');
	$lastupdate = trim($f[0]);
	if ($f[0] == '')
	{
		$lastupdate = date('Y-m-d', strtotime('-1 day'));
		//print "SKU atp.lastid not found, use $lastupdate\n";
	}
*/
print "SKU: " ;

	// get new SKU items or new price
	$c = $con->sql_query("select sku_items.id, sku_item_code, artno, mcode, link_code, receipt_description, if (p.price is null, selling_price, p.price) as selling, round(if (p.cost is null, cost_price, p.cost),3) as cost, if (p.trade_discount_code is null, default_trade_discount_code, trade_discount_code) as disc_code, sku_type, lastupdate, last_update from sku_items left join sku on sku_id = sku.id left join sku_items_price p on p.sku_item_id = sku_items.id and p.branch_id = $branch_id where sku_items.id not in (select sku_item_id from tmp_sku_items_sync where counter_id = $branch_id) order by sku_items.lastupdate") or die(mysql_error());
	
	if ($con->sql_numrows()>0)
	{
		// dump data
		$ff = fopen("atp_sku.txt", "a+");
		if (!$ff) die("Cant append atp_sku.txt");
		//$flog = fopen("atp_sku.log", "a+");
		//fputs($flog,"from:$lastupdate   ");
		while($r = $con->sql_fetchrow($c))
		{
			$lastupdate = $r['lastupdate'];
			$skus[] = $r['sku_item_code'];
			for ($i=0;$i<10;$i++)
			{
				fputs($ff,str_pad($r[$i],$szs[$i]));
			}
			
			// export multiple selling price
			if (isset($config['sku_multiple_selling_price']))
			{
				$mprice = array();
				$con->sql_query("select type, price from sku_items_mprice where branch_id = $branch_id and sku_item_id = $r[id]");
				while($p=$con->sql_fetchrow())
				{
					$mprice[$p['type']] = $p['price'];
				}
				foreach($config['sku_multiple_selling_price'] as $type)
				{
					fputs($ff,str_pad((isset($mprice[$type])?$mprice[$type]:$r['selling']),10));
				}
				$con->sql_freeresult();
			}
			
			fputs($ff,"SKU\n");
			
			//$q=$con->sql_query("insert delayed into tmp_sku_items_sync values ($branch_id,$r[id])");
			//$con->sql_freeresult($q);
		}
		
	//	fputs($flog,"next:$lastupdate;\n".join(",",$skus)."\n");
	//	fclose($flog);
		fclose($ff);

		$run_atp = 1;
		print $con->sql_numrows($c)." records added, Last Update = $lastupdate\n";
	}
	else
	{
		print "No new records\n";
	}

	// update last id
	//$fo = fopen("atp.lastid","w");
	//fputs($fo, $lastupdate."\n");
	//fclose($fo);

	if ($run_atp) exit;

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
