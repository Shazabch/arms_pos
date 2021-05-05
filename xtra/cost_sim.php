<?php
/*
REVISION HISTORY
================
12/4/2007 6:11:40 PM gary
- set initial value for grn_cost (get from sku_items).

11/13/2009 3:09:22 PM Andy
- change grn status from <2 to status=1,active to active=1
- change if($t['grn']||$t['stock_check']) to if(isset($t['grn'])||isset($t['stock_check'])), cuz if zero qty it won't update
*/
define("TERMINAL",1);
include("../config.php");
require("../include/mysql.php");


$arg = $_SERVER['argv'];
$BRANCH_CODE = $arg[1];
$arms_code = $arg[2];

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2],$db_default_connection[3]);

if (!is_numeric($BRANCH_CODE))
{
$con->sql_query("select id from branch where code = ".ms($BRANCH_CODE), false, false);
$r = $con->sql_fetchrow();
if (!$r) die("Invalid branch ".$BRANCH_CODE);
$branch_id = $r[0];
}
else
$branch_id = $BRANCH_CODE;

$rs1 = $con->sql_query("select id, description, cost_price from sku_items left join sku_items_cost on sku_items.id = sku_item_id and branch_id = $branch_id where sku_item_code = ".ms($arms_code)." order by id", false, false);
	
print $con->sql_numrows() . " SKU to update.\n";
$total_n = $con->sql_numrows();
while($r = $con->sql_fetchrow($rs1))
{
	print "$total_n $r[description]...\n";
	run_history($r);
	$total_n--;
}
print "Done.\n";

function run_history($r)
{
	global $branch_id, $con;

	$sku_item_id = $r['id'];
	$cost = $r['cost_price'];

	$where = "sku_items.id = $sku_item_id ";

	$data = array();

	// stock check
	$con->sql_query("select sum(qty) as qty, sum(qty*cost) as cost, date from stock_check left join sku_items using (sku_item_code) where $where and branch_id = $branch_id group by date order by date", false, false);
	while($r=$con->sql_fetchrow())
	{
		$data[$r['date']]['stock_check'] = $r['qty'];
		$data[$r['date']]['stock_check_cost'] = $r['cost'];
	}
	$con->sql_freeresult();

	// POS
	$con->sql_query("select qty, date as dt from sku_items_sales_cache_b$branch_id where sku_item_id = $sku_item_id", false, false);
	while($r=$con->sql_fetchrow())
	{
		$data[$r['dt']]['pos'] = $r['qty'];
	}
	$con->sql_freeresult();

	// GRA
	$con->sql_query("select sum(qty) as qty, date(return_timestamp) as dt from gra_items left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id left join sku_items on sku_item_id = sku_items.id where $where and gra_items.branch_id = $branch_id and gra.status=0 and gra.returned group by dt", false, false);
	while($r=$con->sql_fetchrow())
	{
		$data[$r['dt']]['gra'] = $r['qty'];
	}
	$con->sql_freeresult();

	//FROM DO
	$con->sql_query("select sum(do_items.ctn *uom.fraction + do_items.pcs) as qty, do.do_date as dt
from do_items
left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom on do_items.uom_id=uom.id
where $where and do_items.branch_id = $branch_id and do.approved and do.checkout and do.status<2 group by dt", false, false);
	while($r=$con->sql_fetchrow())
	{
		$data[$r['dt']]['do'] = $r['qty'];
	}
	$con->sql_freeresult();

	//FROM ADJUSTMENT
	$con->sql_query("select sum(qty) as qty, adjustment_date as dt
from adjustment_items
left join adjustment on adjustment.id=adjustment_items.adjustment_id and adjustment.branch_id=adjustment_items.branch_id
left join sku_items on sku_item_id = sku_items.id
where $where and adjustment_items.branch_id = $branch_id and adjustment.approved and adjustment.status<2 group by dt", false, false);
	while($r=$con->sql_fetchrow())
	{
		$data[$r['dt']]['adj'] = $r['qty'];
	}
	$con->sql_freeresult();

	// grn
	$sql = "select (if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
	(
	  if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost)
	  *
	  if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
	  	grn_items.ctn + grn_items.pcs / rcv_uom.fraction,
	  	grn_items.acc_ctn + grn_items.acc_pcs / rcv_uom.fraction
	  )
	) as cost,
		grr.rcv_date as dt,grr_items.type,do.do_type
		from grn_items
		left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
		left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
		left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
		left join grr_items on grr_items.id=grn.grr_item_id and grr_items.branch_id=grn.branch_id
		left join do on do.do_no=grr_items.doc_no
		left join sku_items on grn_items.sku_item_id = sku_items.id
		where $where and grn_items.branch_id = $branch_id and grn.approved=1 and grn.status=1 and grn.active=1";
	$con->sql_query($sql, false, false);
	while($r=$con->sql_fetchrow())
	{
	    $data[$r['dt']]['grn'] += $r['qty'];

		/*if($config['grn_do_dont_update_cost']){
		    if($r['type']!='DO'&&$r['do_type']!='transfer'){
	            $data[$r['dt']]['grn_cost'] += $r['cost'];
	            if($data[$r['dt']]['grn']){
                    $data[$r['dt']]['real_cost'] = $data[$r['dt']]['grn_cost']/$data[$r['dt']]['grn'];
				}

	            $data[$r['dt']]['grn_cost_need_update'] = true;
			}
		}else{
            $data[$r['dt']]['grn_cost'] += $r['cost'];
            $data[$r['dt']]['grn_cost_need_update'] = true;
		}*/

		if($config['grn_do_transfer_update_cost']){
			$data[$r['dt']]['grn_cost'] += $r['cost'];
            $data[$r['dt']]['grn_cost_need_update'] = true;
		}else{
            if($r['type']!='DO'&&$r['do_type']!='transfer'){
	            $data[$r['dt']]['grn_cost'] += $r['cost'];
	            if($data[$r['dt']]['grn']){
                    $data[$r['dt']]['real_cost'] = $data[$r['dt']]['grn_cost']/$data[$r['dt']]['grn'];
				}
	            $data[$r['dt']]['grn_cost_need_update'] = true;
			}
		}

	}
	$con->sql_freeresult();


	ksort($data);
	reset($data);
	
	$qty = 0;
	$con->sql_query("select cost_price from sku_items where id = $sku_item_id", false, false);
	$t=$con->sql_fetchrow();
	$cost = doubleval($t[0]);
	$avg_cost = $cost;
	$avg_total_cost = 0;
	$avg_total_qty = 0;
	
	$last3mth = date('Y-m-d', strtotime("-3 month"));
	$l90d_grn = 0;
	$l90d_pos = 0;
	$last1mth = date('Y-m-d', strtotime("-1 month"));
	$l30d_grn = 0;
	$l30d_pos = 0;

	foreach ($data as $d => $t)
	{
		if ($d >= $last3mth)
		{
			$l90d_grn += $t['grn'];
			$l90d_pos += $t['pos'];
		}
		if ($d >= $last1mth)
		{
			$l30d_grn += $t['grn'];
			$l30d_pos += $t['pos'];
		}
		print "$d ";
		if (isset($t['stock_check'])) 
		{
			if ($t['stock_check_cost']==0) $t['stock_check_cost']=$cost*$t['stock_check'];
			print "\tSCHK: $t[stock_check] @ ".($t['stock_check']?$t['stock_check_cost']/$t['stock_check']:0);
			if ($t['stock_check']>0)
			{
				$cost =  $t['stock_check_cost'] / $t['stock_check'];
				$avg_total_cost = $t['stock_check_cost'];
				$avg_total_qty = $t['stock_check'];
				$avg_cost =  $avg_total_cost / $avg_total_qty;
			}
			else
			{
				//$cost =  0;
				//$avg_cost =  0;
			}
			$qty = $t['stock_check'];
			  
		}
		if ($t['grn']) 
		{
			print "\tGRN: $t[grn] @ ".($t['grn_cost']/$t['grn']);
			if ($qty<=0)	// reset to GRN cost if stock level below or equal zero
			{
				print "\treset AVG_COST";
				$avg_total_cost = $t['grn_cost'];
				$avg_total_qty = $t['grn'];
			}
			else
			{
				$avg_total_cost += $t['grn_cost'];
				$avg_total_qty += $t['grn'];
			}
			$avg_cost =  $avg_total_cost / $avg_total_qty;
			if ($t['grn_cost']>0) $cost = $t['grn_cost'] / $t['grn'] ;
			$qty += $t['grn'];
		}
		if ($t['gra']) { 
			print "\tGRA: $t[gra]"; 
			$qty -= $t['gra']; 
		}
		if ($t['pos']) { 
			print "\tSold: $t[pos]"; 
			$qty -= $t['pos'];
		}
		if ($t['do']) { 
			print "\tDO: $t[do]"; 
			$qty -= $t['do'];
		}
		if ($t['adj']) { 
			print "\tAdj: $t[adj]"; 
			$qty += $t['adj'];
		}
		
		//if ($qty <= 0) $avg_cost = 0; 
		print "\tBalance: $qty\tCost: $cost\tAvg_Cost: $avg_cost ($avg_total_cost / $avg_total_qty)\n";
		
		if (isset($t['grn']) || isset($t['stock_check']))  print("insert into sku_items_cost_history (branch_id, sku_item_id, grn_cost, avg_cost, qty, date) values ($branch_id, $sku_item_id, $cost, $avg_cost, $qty, '$d')\n");
		
		$last_date = $d;
	}

	
	print("replace into sku_items_cost_history (branch_id, sku_item_id, grn_cost, avg_cost, qty, date) values ($branch_id, $sku_item_id, $cost, $avg_cost, $qty, '$last_date')\n");
	
	print("replace into sku_items_cost (branch_id, sku_item_id, grn_cost, avg_cost, qty, date, l90d_grn, l90d_pos, l30d_grn, l30d_pos) values ($branch_id, $sku_item_id, $cost, $avg_cost, $qty, '$last_date', $l90d_grn, $l90d_pos, $l30d_grn, $l30d_pos)\n");	
}

function connect_db($server, $u, $p, $db)
{
	$con = new sql_db($server, $u, $p, $db, false);
	if(!$con->db_connect_id)
	{
		print date("[H:i:s m.d.y] ");
		print("Error: Could not connect to database $u:$p@$server/$db\n".mysql_error());
		exit;
	}
	return $con;
}

function ms($str,$null_if_empty=0)
{
	if ($str == '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", $str);
	return "'" . (trim($str)) . "'";
}
?>
