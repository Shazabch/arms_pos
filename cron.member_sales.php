#!/opt/lampp/bin/php
<?php
/*
2/19/2008 4:02:00 PM Andy
- add branch group generation

12/14/2009 3:19:07 PM Andy
- card_no change to char(20)

6/8/2010 4:14:12 PM Andy
- Script retired.
*/

die("Script Retired!!!");

define('TERMINAL',1);
define('QUERY_PER_CALL', 2000);
include("include/common.php");
ob_end_clean();

$arg = $_SERVER['argv'];
$branch = BRANCH_CODE;
$datefilter = "";
$allbranch = false;
$last_gdate = "";
$min_gdate = "";

array_shift($arg);
while($a = array_shift($arg))
{
	switch ($a)
	{
		case '-branch':
			$branch = array_shift($arg);
			break;
		case '-allbranch':
			$allbranch = true;
			break;
		case '-runall':
			$datefilter = "all";
			break;
		case '-date':
			$dt = array_shift($arg);
			if (strtotime($dt)!==false)
			{
				$datefilter = $dt;
			}
			else
			{
				die("Error: Invalid date $dt.");
			}
			break;
		default:
			die("Unknown option: $a\n");

	}
}

if ($allbranch)
{
	// run all branch
	$br = $con->sql_query("select code from branch");
	while($r=$con->sql_fetchrow($br))
	{
		run($r[0]);
	}
}
else
{
	run($branch);
}

$bg = $con->sql_query("select * from branch_group");
while($r = $con->sql_fetchrow($bg)){
	run_group($r['id']);
}

//$con->sql_query("optimize table category_sales_cache");
//$con->sql_query("analyze table category_sales_cache");

function run($branch)
{
	global $con, $datefilter,$last_gdate,$min_gdate;

	$con->sql_query("select id from branch where code = ".ms($branch));
	$r = $con->sql_fetchrow();
	if (!$r)
	{
		die("Error: Invalid branch $branch.");
	}
	$bid = $r[0];

	print "Runnning branch_id = $bid ";
	
	//$con->sql_query("drop table if exists member_sales_cache_b$bid");
	$con->sql_query("
	create table if not exists member_sales_cache_b$bid (
	date date,
	year int(4),
	month int(2),
	day int(2),
	hour int,
	card_no char(20),
	race enum('C','I','M','O'),
	transaction_count int,
	amount double,
	primary key(date,hour,card_no),index(year),index(month),index(day),index(race),index(transaction_count,amount))
	");
	
	$con->sql_query("alter table member_sales_cache_b$bid convert to charset latin1 collate latin1_general_ci");

	if ($datefilter == 'all')
	{
  $con->sql_query("select date(min(timestamp)) from pos_transaction where branch_id = $bid");
		$min_date = $con->sql_fetchfield(0);
		if($min_date){
			if($min_gdate=='')	$min_gdate = $min_date;
			elseif($min_date<$min_gdate)    $min_gdate = $min_date;

			$ddel = "date>= ".ms($min_date);
			$date_select = "and timestamp>=".ms($min_date);
		}
	}
	elseif ($datefilter != '')
	{
	    $ddel = "date=".ms($datefilter);
	    $date_select = "and (timestamp between ".ms($datefilter)." and date_add(".ms($datefilter).", interval 1 day))";
	}
	else
	{
		// get last sku_items_sales_cache and POS date of this branch
		$con->sql_query("select max(date) from member_sales_cache_b$bid");
		$startd = $con->sql_fetchrow();
		$con->sql_query("select date(max(timestamp)) from pos_transaction where branch_id = $bid");
		$lastd = $con->sql_fetchrow();
		if($lastd[0] > $last_gdate) $last_gdate = $lastd[0];
		
		if ($lastd[0]<=$startd[0])
		{
			print "Date range = $startd[0] - $lastd[0] (No data to process)\n";
			return;
		}else if ($lastd[0])
		{
			print "Date range = $startd[0] - $lastd[0]\n";
			$date_select = "and (pos.timestamp between ".ms($startd[0])." and date_add(".ms($lastd[0]).", interval 1 day))";
			$ddel = "date between ".ms($startd[0])." and ".ms($lastd[0]);
		}
		else
		{
			print "No data\n";
			$df = "";
			$ddel = "";
			return;
		}
	}
	if($ddel!=''){
		$con->sql_query("delete from member_sales_cache_b$bid where $ddel");
	    print $con->sql_affectedrows()." rows deleted.";
	}
	    
	$rs = $con->sql_query("select date(timestamp) as date,year,month,day,hour,card_no,substring(race,1,1) as race,count(distinct(transaction_id)) as transaction_count,sum(amount) as amount from pos_transaction pos where branch_id = $bid $date_select group by year,month,day,card_no,hour");

	print " -> ".$con->sql_numrows()." rows from pos_transaction";
	$n=0; $query = '';
    while($r=$con->sql_fetchrow($rs))
	{
		$values = xr($r, $fields);
		if ($n==0)
			$query = "replace into member_sales_cache_b$bid (".join(",", $fields).") values (".join(",",$values).")";
		else
			$query .= ", (".join(",",$values).")";
		$n++;

		if ($n==QUERY_PER_CALL)
		{
	  		print ".";
			$con->sql_query($query) or die(mysql_error());
			$con->sql_freeresult();
	  		$n = 0;
	  		$query = '';
		}
  	}
	if ($query)
	{
		$con->sql_query($query) or die(mysql_error());
		$con->sql_freeresult();
	}
	$con->sql_freeresult($rs);
	print " saved.\n";
	$con->sql_query("optimize table member_sales_cache_b$bid");
	$con->sql_query("analyze table member_sales_cache_b$bid");
}

function run_group($bg_id){
    global $con,$branch,$allbranch,$dt,$last_gdate,$min_gdate;

    $sql = "select bgi.branch_id,branch.code from branch_group_items bgi left join branch on bgi.branch_id=branch.id
where bgi.branch_group_id=".mi($bg_id);

	$con->sql_query($sql) or die(mysql_error());
	if($con->sql_numrows()<=0)  return false;
	while($r = $con->sql_fetchrow()){
		$branches_list[] = $r['branch_id'];
		$code_list[] = strtoupper($r['code']);
	}

	// branch checking
	if(!$allbranch){
	    if(!in_array(strtoupper($branch),$code_list)){
			return false;
		}
	}

 	print "\n Runnning branch_group_id $bg_id \n";
 	$con->sql_query("
	create table if not exists member_sales_cache_bg$bg_id (
	date date,
	year int(4),
	month int(2),
	day int(2),
	hour int,
	card_no char(20),
	race enum('C','I','M','O'),
	transaction_count int,
	amount double,
	primary key(date,hour,card_no),index(date),index(year),index(month),index(day),index(race),index(transaction_count,amount))
	");
	
	$con->sql_query("alter table member_sales_cache_bg$bg_id convert to charset latin1 collate latin1_general_ci");
	
	// date checking
	if ($datefilter == 'last')
	{
		$con->sql_query("select max(date) from member_sales_cache_bg$bg_id");
		$startd = $con->sql_fetchrow();

		if ($last_gdate<=$startd[0])
		{
			print "Date range = $startd[0] - $last_gdate (No data to process)\n";
			return;
		}
		else if ($last_gdate)
		{
			print "Date range = $startd[0] - $last_gdate\n";
			$where = "where date between ".ms($startd[0])." and ".ms($last_gdate);
		}
		else
		{
			print "No data\n";
			return;
		}
	}elseif($dt){
		$where = "where date=".ms($dt);
	}else{
    	$where = "where date>=".ms($min_gdate);
	}

	if($where){
		// delete data
		$con->sql_query("delete from member_sales_cache_bg$bg_id $where") or die(mysql_error());
	}
	
	foreach($branches_list as $bid){
		$target_tbl = "member_sales_cache_bg$bg_id";
		$from_tbl = "member_sales_cache_b$bid";

		$sql = "insert into $target_tbl (select * from $from_tbl $where) ON DUPLICATE KEY UPDATE
$target_tbl.amount=$target_tbl.amount+$from_tbl.amount,
$target_tbl.transaction_count=$target_tbl.transaction_count+$from_tbl.transaction_count";
		// insert data
		$con->sql_query($sql) or die(mysql_error());
		print " \n ".$con->sql_affectedrows()." rows from $from_tbl saved";
	}
	print "\n Done. \n";
}

function xr($r, &$fields)
{
	$ret = array();
	$fields = array();
	foreach($r as $k=>$v)
	{
	    if (is_numeric($k)) continue;
	    $fields[] = "`$k`";
		$ret[] = ms($v);
	}
	return $ret;
}

?>
