<?
define('TERMINAL',1);
include("include/common.php");
include("calculate_balance.include.php");
set_time_limit(0);
ini_set("memory_limit", "128M");
ob_end_clean();

$arg = $_SERVER['argv'];
$branch = BRANCH_CODE;
$allbranch = false;
$usebranch = true;
$date=date('Y-m-d');
array_shift($arg);

while($a = array_shift($arg)){
	switch ($a){
		case '-nobranch_id':
			$usebranch= false;
			break;
			
		case '-branch':
			$branch = array_shift($arg);
			break;
			
		case '-allbranch':
			$allbranch = true;
			break;
			
		case '-date':
			$dt = array_shift($arg);
			if (strtotime($dt)!==false){
				$date = $dt;
			}
			else{
				die("Error: Invalid date, please enter date with syntax [-date yyyy-mm-dd]\n$dt.");
			}
			break;
			
		case '-table':
			$tbl_name = array_shift($arg);
			break;
			
		default:
			die("Unknown option: $a\n");						
	}
}

if($allbranch){
	$branch_id='';
	$usebranch= true;
}
else{
	$q1=$con->sql_query("select id from branch where code=".ms($branch));
	$r1=$con->sql_fetchrow($q1);
	$branch_id=$r1['id'];
}

if($tbl_name){
	if($usebranch){	
		$sql="create table if not exists $tbl_name (branch_id int not null, sku_item_id int not null, date date not null, qty integer, grn_cost double, primary key (branch_id, sku_item_id, date))";
	}
	else{
		$sql="create table if not exists $tbl_name (sku_item_id int not null, date date not null, qty integer, grn_cost double, primary key (sku_item_id, date))";	
	}
	$con->sql_query("$sql");
}
else{
	die("Error: Please enter table name with syntax [-table ......]\n");
}

run_balance($usebranch, $branch_id, $tbl_name, $date);

?>
