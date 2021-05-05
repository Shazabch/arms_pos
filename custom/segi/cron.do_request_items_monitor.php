<?php
/*
5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.
*/

define('TERMINAL',1);
define('QUERY_PER_CALL', 2000);

include_once('../../config.php');
if((version_compare(PHP_VERSION, '7.0.0', '>='))){
	include_once('../../include/mysqli.php');
}else{
	include_once('../../include/mysql.php');
}
include_once('../../include/db.php');
include_once('../../include/functions.php');
error_reporting (E_ALL ^ E_NOTICE);

if (!$con->db_connect_id) { die('cannot connect '.mysql_error()); }

ob_end_clean();
//$maintenance->check(1);

ini_set('memory_limit', '512M');
set_time_limit(0);

$arg = $_SERVER['argv'];
$branch = $dt = "";

array_shift($arg);
while($a = array_shift($arg)){
	switch ($a){
		case '-branch':
			$branch = array_shift($arg);
			break;
		case '-date':
			$dt = array_shift($arg);
			break;
		case '-dept_id':
			$dept_id = array_shift($arg);
			break;
		default:
			die("Unknown option: $a\n");
	}
}
if(!$branch){
	// run all branch
	$br = $con->sql_query("select code from branch");
	while($r=$con->sql_fetchassoc($br)){
		run($r['code']);
	}
	$con->sql_freeresult($br);
}else{
	run($branch);
}

function run($branch){
	global $con, $dt, $dept_id, $config;
	
	$q1 = $con->sql_query("select id from branch where code = ".ms($branch));
	$b = $con->sql_fetchrow($q1);
	if (!$b){
		die("Error: Invalid branch $branch.");
	}
	$con->sql_freeresult($q1);
	$bid = $b['id'];

	if(!$dt) $dt = date("Y-m-d");
	
	$filter = array();
	if(!$dept_id){
		die("Error: Empty Department");
	}else{
		$q1 = $con->sql_query("select * from category where id in (".$dept_id.")");
		
		while($r = $con->sql_fetchassoc($q1)){
			$dept_id_list[$r['id']] = $r['id'];
		}
		$con->sql_freeresult($q1);
		
		if(!$dept_id_list){
			die("Error: Empty Department");
		}else{
		
		}
	}

	print "Processing branch ".$branch."...\n";

	$filter[] = "dri.branch_id = ".mi($bid);
	$filter[] = "dri.expect_do_date > 0 and dri.expect_do_date < ".ms($dt);
	$filter[] = "c.department_id in (".join(",", $dept_id_list).")";
	$filter[] = "dri.status=0 and dri.active=1";
	
	$filters = join(" and ", $filter);
	
	$succ_count = 0;
	// check for those do request saved items
	$q1 = $con->sql_query("select dri.*, si.sku_item_code
						   from do_request_items dri
						   left join sku_items si on si.id = dri.sku_item_id
						   left join sku on sku.id = si.sku_id
						   left join category c on c.id = sku.category_id
						   where $filters");
	$succ_count = $con->sql_numrows($q1);

	if($succ_count > 0){
		print "Found ".$succ_count." Request item(s) need to set as reject for ".$branch."...\n";
					
		while($r = $con->sql_fetchassoc($q1)){
			$upd = array();
			$upd['status'] = 3;
			$upd['reason'] = "Fresh Expired";
			$upd['selected'] = 0;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$upd['reject_by'] = 1;
			
			$q2 = $con->sql_query("update do_request_items set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
			
			if($con->sql_affectedrows($q2) > 0){
				print "Updated ID#".$r['id']." - SKU#".$r['sku_item_code']."\n";
				log_br(1, 'DO Request', $r['id'], "Reject Item (Fresh Expired), Item ID: ".$r['id']);
			}
		}
		$con->sql_freeresult($q1);
		
		print "Updated ".mi($succ_count)." Request item(s) into rejected for ".$branch."...\n";
	}else print "No Request item(s) found for branch ".$branch."...\n"; 
}
?>