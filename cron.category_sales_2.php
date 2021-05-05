<?php
/*
to verify the result, use this SQL (change branch id):
==================================
select sum(amount) from sku_items_sales_cache_b4
union all
select sum(amount) from pos_transaction where branch_id=4
union all
select sum(amount) from category_sales_cache_b4


3/31/2008 6:54:06 PM yinsee
fix bug - grn_cost * qty = item cache's cost 

cron....php -allbranch -runall

9/5/2008 8:25:44 AM andy
- add disc_amt to items sales cache

9/25/2008 11:55:01 AM yinsee
- add memory limit

2/18/2008 5:00:00 PM Andy
- add branch group generation

11/3/2009 4:49:13 PM yinsee
- fix bug when pos_transaction min date is empty string, it wont delete anything. thus wont regen.

3/1/2010 5:38:09 PM Andy
- Fix bugs if got move category or item change category

5/19/2010 5:14:48 PM alex
- add dept_trans_cache_b table for counting transaction

7/8/2010 1:40:14 PM Andy
- Fix a script error which will cause cron terminate.

12/14/2010 3:33:40 PM Andy
- Fixed sales recalculation script when sku change category. 
- Disable branch group cron calculation.

1/11/2012 4:07:43 PM Justin
- Fixed the table missing fields "fresh_market_cost" and "last_grn_vendor_id".

1/28/2013 10:46:58 AM yinsee
- fix "already running" for ARMS-GO (use 'ps x' instead of 'ps ax')

4/11/2016 11:51 AM Andy
- Fix when got category changed, disc_amt, disc_amt2 & tax_amount no update.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.

12/5/2017 5:17 PM Andy
- Enhanced to recalculate member and non-member sales data from cache table when category changed.
*/
define('TERMINAL',1);
define('QUERY_PER_CALL', 2000);
include("include/common.php");
ob_end_clean();
//$maintenance->check(1);

ini_set('memory_limit', '1024M');
set_time_limit(0);

// check if myself is running, exit if yes
if (!preg_match('/(root|arms|admin|wsatp)/', `whoami`))
  @exec('ps x | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
else
  @exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
  
if (count($exec)>1)
{
	print date("[H:i:s m.d.y]")." Another process is already running\n";
	print_r($exec);
	exit;
}
$arg = $_SERVER['argv'];
$branch = BRANCH_CODE;
$datefilter = "last";
$delfilter = "";
$allbranch = false;
$last_gdate = "";
$min_gdate = "";
$only_dept = false;

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
			$datefilter = "";
			break;
		case '-date':
			$dt = array_shift($arg);
			if (strtotime($dt)!==false)
			{
				$datefilter = " and (pos.timestamp between ".ms($dt)." and date_add(".ms($dt).", interval 1 day))";
				$delfilter = "date = ".ms($dt);
    			$idate = ms($dt);
			}
			else
			{
				die("Error: Invalid date $dt.");
			}
			break;
		case '-only_dept':
		    $only_dept = true;
		    break;
		default:
			die("Unknown option: $a\n");
						
	}
}

if ($allbranch)
{
	// run all branch
	$br = $con->sql_query("select code,id from branch");
	while($r=$con->sql_fetchrow($br))
	{
	
		if ($only_dept){
            //run_dept($r[0]);
		}	
		else {
			//run_dept($r[0]);
			run($r[0]);
		}
		// run category changed
	  	$bid = $r['id'];
	  	$tbl = array();
	  	$tbl['sku_cache'] = 'sku_items_sales_cache_b'.$bid;
	    $tbl['cat_cache'] = 'category_sales_cache_b'.$bid;
	  	run_changed_cat($bid, $tbl);
	}
	
	// select all branch group id - build branch group id array for later use
	$q_bg = $con->sql_query("select id from branch_group");
	while($r = $con->sql_fetchrow($q_bg)){
		$bg_id_array[$r[0]] = $r[0];
	}
}
else
{
	if ($only_dept){
		//run_dept($branch);
	}	
	else{
		run($branch);
		//run_dept($branch);
	}

	// run category changed
	$con->sql_query("select id from branch where code = ".ms($branch));
	$b = $con->sql_fetchrow();
	$bid = $b['id'];
	$tbl = array();
	$tbl['sku_cache'] = 'sku_items_sales_cache_b'.$bid;
  	$tbl['cat_cache'] = 'category_sales_cache_b'.$bid;
	run_changed_cat($bid, $tbl);
	
	// select branch group id - build branch group id array for later use
	$q_bg = $con->sql_query("select distinct(branch_group_id) from branch_group_items where branch_id=$b[id]");
	while($r = $con->sql_fetchrow($q_bg)){
		$bg_id_array[$r[0]] = $r[0];
	}
}	

//$con->sql_query("update category set changed=0");

function run($branch)
{
	global $con, $datefilter, $delfilter,$last_gdate,$min_gdate,$idate,$dt;
	
	$con->sql_query("select id from branch where code = ".ms($branch));
	$r = $con->sql_fetchrow();
	if (!$r)
	{
		die("Error: Invalid branch $branch.");
	}
	$bid = $r[0];
	
	print "Runnning branch_id = $bid ";
	
	// items cache
	//$con->sql_query("create table if not exists sku_items_sales_cache_b$bid (sku_item_id int, date date, year integer, month integer, amount double, cost double,disc_amt double, qty double, fresh_market_cost double, last_grn_vendor_id int, primary key (date,sku_item_id), index(sku_item_id), index(year, month))") or die(mysql_error());
	// category cache
	//$con->sql_query("create table if not exists category_sales_cache_b$bid (category_id int, date date not null, sku_type char(10), year integer, month integer, amount double, cost double, qty double, fresh_market_cost double, primary key (date, category_id, sku_type), index(category_id), index(sku_type), index(year, month))") or die(mysql_error());
	// sales target
	//$con->sql_query("create table if not exists sales_target_b$bid (date date, year int, month int, sku_type enum('CONSIGN','OUTRIGHT') default 'CONSIGN', department_id int, target double, PRIMARY KEY (date,department_id,sku_type), index(year,month,target))") or die(mysql_error());


	$df = $datefilter;
	$ddel = $delfilter;
	if($dt){    // selected date
		print "Date = $dt\n";
		update_sales_cache($bid, $dt);
		return;
	}elseif ($datefilter == 'last'){    // no date given
		
		// get last sku_items_sales_cache and POS date of this branch
		$con->sql_query("select max(date) from sku_items_sales_cache_b$bid");
		$startd = $con->sql_fetchrow();
		$con->sql_query("select max(date) from pos_finalized where branch_id = $bid and finalized=1");
		$lastd = $con->sql_fetchrow();
		
  		if($lastd[0] > $last_gdate) $last_gdate = $lastd[0];
		
		if ($lastd[0]<=$startd[0])
		{

			print "Date range = $startd[0] - $lastd[0] (No data to process)\n";
			return;
		}
		else if ($lastd[0])
		{
			print "Date range = $startd[0] - $lastd[0]\n";
			$df = "and (pos.timestamp between ".ms($startd[0])." and date_add(".ms($lastd[0]).", interval 1 day))";
			$ddel = "date between ".ms($startd[0])." and ".ms($lastd[0]);
			
			update_sales_cache($bid, '', $startd[0], $lastd[0]);
			return;
		}
		else
		{
			print "No data\n";
			$df = "";
			$ddel = "";
			return;
		}
	}
	elseif ($ddel=='') {    // runall
        update_sales_cache($bid,'', '2001-01-01');
        return;
	}
	return;
}

function run_changed_cat($bid, $tbl, $is_group = false){
  global $con, $datefilter, $delfilter,$last_gdate,$min_gdate;
	if($is_group)  print "Runnning category changed check for branch group id $bid ";
	else print "Runnning category changed check for branch_id $bid ";
	
	$con->sql_query("select category_id from category_changed where branch_id=$bid");
	//$con->sql_query("select id from category where changed=1");
	while($r = $con->sql_fetchrow()){
	    $cat_id_changed[] = $r['category_id'];
	  }
  
  $sku_cache = $tbl['sku_cache'];
  $cat_cache = $tbl['cat_cache'];
  if($cat_id_changed){
    print " -> ".count($cat_id_changed)." category changed found";
    
    // clear data first
    $con->sql_query("delete from $cat_cache where category_id in (".join(',', $cat_id_changed).") ".($delfilter? ' and '.$delfilter: ''));
    
    // insert again data
    $rs=$con->sql_query("select sku.category_id as category_id, pos.date as date, sku.sku_type as sku_type, pos.year as year, pos.month as month, sum(pos.amount) as amount, sum(pos.cost) as cost, sum(pos.qty) as qty, sum(pos.fresh_market_cost) as fresh_market_cost, sum(disc_amt) as disc_amt, sum(disc_amt2) as disc_amt2, sum(tax_amount) as tax_amount, sum(memb_qty) as memb_qty, sum(memb_amt) as memb_amt, sum(memb_tax) as memb_tax, sum(memb_disc) as memb_disc, sum(memb_disc2) as memb_disc2, sum(memb_cost) as memb_cost, sum(memb_fm_cost) as memb_fm_cost
	from $sku_cache pos
left join sku_items on sku_item_id = sku_items.id
left join sku on sku_id = sku.id
where sku.category_id in (".join(',',$cat_id_changed).") ".($delfilter? ' and '.$delfilter: '')."
group by date, category_id, sku_type");
    print " -> ".$con->sql_numrows()." rows from $sku_cache \n";
	  $n=0; $query = '';
    while($r=$con->sql_fetchrow($rs))
  	{
  		$values = xr($r, $fields);
  		if ($n==0){
		  	//$query = "replace into $cat_cache (".join(",", $fields).") values (".join(",",$values).")";
		  	$query = "replace into $cat_cache ".mysql_insert_by_field($r);
		  }
  		else
  			$query .= ", (".join(",",$values).")";
  		$n++;
  
  		if ($n==QUERY_PER_CALL)
  		{
  	  		print ".";
  			$con->sql_query($query) or die(mysql_error());
  			//$con->sql_freeresult();
  	  		$n = 0;
  	  		$query = '';
  		}
    }
  	if ($query)
  	{
  		$con->sql_query($query) or die(mysql_error());
  		//$con->sql_freeresult();
  	}
  	$con->sql_freeresult($rs);
  	$con->sql_query("delete from category_changed where branch_id=$bid and category_id in (".join(',', $cat_id_changed).")");
  }else print "-> No Category changed\n";
}

function xr($r)
{
	$ret = array();
	for($i=0;$i<count($r)/2;$i++)
	{
		$ret[$i] = ms($r[$i]);
	}
	return $ret;
}
?>
