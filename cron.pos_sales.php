<?php
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
$maintenance->check(1);

$arg = $_SERVER['argv'];
$branch = BRANCH_CODE;
$datefilter = "last";
$delfilter = "";
$allbranch = false;
array_shift($arg);
while($a = array_shift($arg)){
	switch ($a){
		case '-branch':
			$branch = array_shift($arg);
			break;
		case '-allbranch':
			$allbranch = true;
			break;
		case '-runall':
			$datefilter = "";
			break;
		case '-date':
			$dt = array_shift($arg);
			if (strtotime($dt)!==false){
				$datefilter = " and (pos.timestamp between ".ms($dt)." and date_add(".ms($dt).", interval 1 day))";
				$delfilter = " and date = ".ms($dt);
			}
			else{
				die("Error: Invalid date $dt.");
			}
			break;
		default:
			die("Unknown option: $a\n");						
	}
}

if ($allbranch){
	// run all branch
	$br = $con->sql_query("select code from branch");
	while($r=$con->sql_fetchrow($br)){
		run($r[0]);
	}
}
else{
	run($branch);
}
	
$con->sql_query("optimize table pos_transaction_cache");
$con->sql_query("analyze table pos_transaction_cache"); 

function run($branch){
	global $con, $datefilter, $delfilter;
	
	$con->sql_query("select id from branch where code = ".ms($branch));
	$r = $con->sql_fetchrow();
	if (!$r){
		die("Error: Invalid branch $branch.");
	}
	$bid = $r[0];
	
	print "Runnning branch_id = $bid ";
	$df = $datefilter;
	$ddel = $delfilter;
	if ($datefilter == 'last'){
		// get last POS date of this branch
		$con->sql_query("select date(max(timestamp)) from pos_transaction where branch_id = $bid");
		$lastd = $con->sql_fetchrow();
		if ($lastd[0]){
			print "Last date = $lastd[0] ";
			$df = "and (pos.timestamp between ".ms($lastd[0])." and date_add(".ms($lastd[0]).", interval 1 day))";
			$ddel = "and date = ".ms($lastd[0]);
		}
		else{
			print "No data\n";
			$df = "";
			$ddel = "";
			return;
		}
	}
	
	$rs=$con->sql_query("select branch_id, sku_item_code, date(pos.timestamp) as dt, sum(pos.qty) as qty,  sum(pos.amount) as amount, grn_cost as cost
from pos_transaction pos 
where branch_id = $bid $df
group by dt, sku_item_code,branch_id");
	print " -> ".$con->sql_numrows()." rows\n";
	while($r=$con->sql_fetchrow($rs)){
		$t = xr($r);
		$con->sql_query("replace into pos_transaction_cache values (".join(",",$t).")"); 
	}
	print " saved.\n";
}


function xr($r){
	$ret = array();
	for($i=0;$i<count($r)/2;$i++){
		$ret[$i] = ms($r[$i]); 
	}
	return $ret;
}

?>
