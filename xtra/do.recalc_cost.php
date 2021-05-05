<?php
define('TERMINAL',1);
include("../config.php");

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("../include/mysql.php");

$con = connect_db($db_default_connection[0], $db_default_connection[1],
$db_default_connection[2], $db_default_connection[3]);

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

$con->sql_query("create table backup_do select * from do");
$con->sql_query("create table backup_do_items select * from do_items");
$con->sql_query("create table backup_grr select * from grr");
$con->sql_query("create table backup_grr_items select * from grr_items");
$con->sql_query("create table backup_grn select * from grn");
$con->sql_query("create table backup_grn_items select * from grn_items");

$flog_small = fopen("do_log_small.txt", "w");
$flog_big = fopen("do_log_big.txt", "w");
$flog_zero = fopen("do_log_zero.txt", "w");

$q1=$con->sql_query("select grn.id as grn_id, grn.branch_id as create_grn_bid, doc_no, type, do.do_date, do.id as do_id, do.branch_id as create_do_bid, grr_items.grr_id as grr_id, grr_items.id as grr_item_id    
from grn 
left join grr_items on grn.grr_item_id = grr_items.id and grn.grr_id = grr_items.grr_id and grn.branch_id= grr_items.branch_id 
left join do on doc_no=do_no 
where grn.by_account=0 and grn.approved=1 and grn.branch_id>0  
order by do_date, doc_no");
while ($r1 = $con->sql_fetchrow($q1)){
	print "process grn_id : $r1[grn_id], branch_id : $r1[create_grn_bid]......\n";
	fix_do($r1);
	fix_grn($r1);	
}
echo "DONE\n";

function fix_do($pass){
	global $con, $flog_small, $flog_big, $flog_zero;	
	$do_id=$pass['do_id'];
	$branch_id=$pass['create_do_bid'];
	$do_date=$pass['do_date'];
	$do_no=$pass['doc_no'];
	
	$count=0;
	$q1=$con->sql_query("select do_items.*, uom.fraction as uom_fraction, si.sku_item_code as sku  
from do_items 
left join uom on uom.id=uom_id 
left join sku_items si on si.id=sku_item_id
where do_id=$do_id and branch_id=$branch_id");
	while ($do = $con->sql_fetchrow($q1)){
		//get the latest cost based-on the do_date.
		$new_cost=get_cost($branch_id, $do['sku_item_id'], $do_date);
		if($do['uom_fraction']){
			$new_cost=$new_cost*$do['uom_fraction'];
			$uom=$do['uom_fraction'];
		}
		else{
			$con->sql_query("update do_items set uom_id=1 where do_id=$do_id and branch_id=$branch_id and sku_item_id=$do[sku_item_id]");
			$uom=1;		
		}
		
		$count++;
		$log="DO($do_no,id:$do_id,bid:$branch_id) => sku: $do[sku], cost(DO): $do[cost_price]\n";
				
		$cost=$do['cost_price'];		
		//compare old cost with new cost, if diff only update
		if(!$do['cost_price'])
			fwrite($flog_zero, $log);	
		else{
			$con->sql_query("update do_items set cost_price='$new_cost' where do_id=$do_id and branch_id=$branch_id and sku_item_id=$do[sku_item_id]");
			$cost=$new_cost;
			$update_header=1;
		}
			
		$total_amt+=round((($cost*$do['ctn'])+(($cost/$uom)*$do['pcs'])),2);	
	}
	if($update_header==1)
		$con->sql_query("update do set total_amount='$total_amt' where id=$do_id and branch_id=$branch_id");			
}

function fix_grn($pass){
	global $con, $flog_small, $flog_big, $flog_zero;
	$grn_id=$pass['grn_id'];
	$grn_bid=$pass['create_grn_bid'];	
	$do_id=$pass['do_id'];
	$branch_id=$pass['create_do_bid'];
	
	$total_amt=0;
	$q1=$con->sql_query("select grn_items.*, uom.fraction as uom_fraction, si.sku_item_code as sku
from grn_items 
left join uom on uom.id=uom_id	
left join sku_items si on si.id=sku_item_id 
where grn_id=$grn_id and branch_id=$grn_bid");

	while ($grn = $con->sql_fetchrow($q1)){	
		$q2=$con->sql_query("select cost_price from do_items where do_id=$do_id and branch_id=$branch_id and sku_item_id=$grn[sku_item_id]");
		$do = $con->sql_fetchrow($q2);
		
		if($grn['uom_fraction'])
			$uom=$grn['uom_fraction'];
		else{
			$con->sql_query("update grn_items set uom_id=1 where grn_id=$grn_id and branch_id=$grn_bid and sku_item_id=$grn[sku_item_id]");
			$uom=1;		
		}
		
		$log="GRN(id:$grn_id,bid:$grn_bid)/DO(id:$do_id,bid:$branch_id) => sku: $grn[sku], cost(grn): $grn[cost], new_cost(DO): $do[cost_price]\n";
		
		$cost=$grn['cost'];		
		
		if(!$do['cost_price'] || !$grn['cost'])
			fwrite($flog_zero, $log);			
		elseif($grn['cost']>$do['cost_price']){
			fwrite($flog_big, $log);
			$con->sql_query("update grn_items set cost='$do[cost_price]' where grn_id=$grn_id and branch_id=$grn_bid and sku_item_id=$grn[sku_item_id]");
			$cost=$do['cost_price'];
			$update_header=1;
		}
		elseif($grn['cost']<$do['cost_price']){
			fwrite($flog_small, $log);
			$con->sql_query("update grn_items set cost='$do[cost_price]' where grn_id=$grn_id and branch_id=$grn_bid and sku_item_id=$grn[sku_item_id]");
			$cost=$do['cost_price'];
			$update_header=1;	
		}
		
		$total_amt+=round((($grn['ctn']*$cost)+($cost/$uom*$grn['pcs'])),2);
	}

	$grr_id=$pass['grr_id'];
	$grr_item_id=$pass['grr_item_id'];
	
	if($update_header){
		$con->sql_query("update grr set grr_amount='$total_amt' where id=$grr_id and branch_id=$grn_bid");
		
		$con->sql_query("update grr_items set amount='$total_amt' where id=$grr_item_id and branch_id=$grn_bid");
		
		$con->sql_query("update grn set amount='$total_amt' where id=$grn_id and branch_id=$grn_bid");
	}

}

function get_cost($branch_id, $sku_item_id, $do_date){
	global $con;
	$q1=$con->sql_query("select round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/uom.fraction,3) as cost 
from grn_items
left join uom on uom_id = uom.id 
left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
where grn_items.branch_id=$branch_id and grn.approved
and grn_items.sku_item_id=$sku_item_id and grr.rcv_date<'$do_date' 
having cost > 0 
order by grr.rcv_date desc limit 1");
	$r1 = $con->sql_fetchrow($q1);
	if(!$r1){
		$q1=$con->sql_query("select cost_price as cost from sku_items where id=$sku_item_id");
		$r1 = $con->sql_fetchrow($q1);		
	}
	return $r1['cost'];	
}
fclose($flog_small);
fclose($flog_big);
fclose($flog_zero);
?>
