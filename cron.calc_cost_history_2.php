<?php
/*
12/4/2007 6:11:40 PM gary
- set initial value for grn_cost (get from sku_items).

2008/05/02 15:03:40 yinsee
- use average cost from GRN / stock check

5/30/2009 Andy
- Get stock closing

7/27/2009 4:47:44 PM Andy
- don't update cost if GRN is type 'DO'

8/18/2009 10:01:51 AM Andy
- add arg = 'run_bal', will run again all stock closing balance

9/14/2009 5:17:38 PM Andy
- avg cost use round(x,5)

9/18/2009 5:30:10 PM Andy
- add cron stock_balance_bX_YYYY

2009-10-17 yinsee
- add round(5) to grn cost calculation

2009/10/19 10:07:05 AM Andy
- add config['grn_do_transfer_update_cost'], default don't update cost for DO, only update if isset this config

11/13/2009 1:13:33 PM Andy
- change grn status from <2 to status=1,active to active=1
- change if($t['grn']||$t['stock_check']) to if(isset($t['grn'])||isset($t['stock_check'])), cuz if zero qty it won't update

12/11/2009 11:29:15 AM Andy
- Add don't create stock balance entry if stock take year equal to zero

12/22/2009 1:19:44 PM Andy
- Change stock balance table to store qty as float

1/19/2010 1:09:16 PM Andy
- Fix stock balance duplicate entry error

1/22/2010 5:18:09 PM Andy
- Rewrite script to improve performance

1/25/2010 4:01:09 PM Andy
- Fix config['grn_do_transfer_update_cost'] bugs
- Optimize sql speed

2/2/2010 12:32:27 PM Andy
- add always initial stock balance table, this is to prevent some time no item and it will not generate the table

2/3/2010 4:27:04 PM Andy
- Fix GRN Cost problem

2/26/2010 1:15:31 PM Andy
- Fix stock balance incorrect due to cross year problem.

3/10/2010 11:20:49 AM Andy
- add consignment modules enhancement, branch follow HQ cost under consignment modules
- add 3 configs (H2B, B2H, B2B) to manage whether DO need to update cost or not

3/31/2010 4:23:13 PM Andy
- change to store stock balance last entry year in mysql, no longer use .txt file

5/31/2010 2:54:17 PM Andy
- Stock balance and inventory calculation include CN(-)/DN(+), can see under SKU Masterfile->Inventory.

6/2/2010 6:16:35 PM Andy
- add start and close qty for stock balance database.
- Fix if use date filter calculation and the item have no cost history before the filter date, it may cause stock balance missing around the filter date.

6/8/2010 10:11:36 AM Andy
- CN/DN Swap

6/16/2010 1:30:50 PM Andy
- Fix consignment calculation slow bugs

7/27/2010 10:25:08 AM Andy
- Cost calculation change to if grn cost is zero, will not change the latest cost.
- Item with "SKU without inventory" will always have zero stock balance.

8/19/2010 3:05:31 PM Andy
- Add config control to no inventory sku.

9/24/2010 5:41:42 PM Andy
- Fix cron calculate duplicate bugs when using -mX parameters.

1/3/2011 10:12:54 AM Andy
- Fix cross year copy bugs, the from_date and to_date is wrong.

5/11/2011 5:21:22 PM Andy
- Add checking for stock balance table exists or not before create table statement.

2/8/2011 10:44:27 AM Andy
- Add calculate fresh market cost in cron.

6/28/2011 6:09:11 PM Justin/Andy
- Fixed the wrong calculation for Average Cost.
- Enhanced to have different calculation while with/without GRN future.

7/4/2011 4:53:40 PM Andy
- Change to hide E_NOTICE.

8/16/2011 11:01:21 AM Justin
- Added the checking of active=1 for DO and Adjustment.
- Fixed some of the SQL query to use proper filtering.

11/14/2011 4:26:19 PM Andy
- Change to only run for active branch.

3/26/2012 11:43:28 AM Andy
- Add generate sku vendor data when running cron of calculation cost.
- Add checking for cron version to run some maintenance.

5/30/2012 4:42:00 PM Andy
- Add last vendor id into table vendor_sku_history_bX (date from/to also 0000-00-00)

8/27/2012 12:00 PM Andy
- Add new AVG cost calculation, only available if the item not fresh market, not consignment module and got config "sku_use_avg_cost_as_last_cost".
- Add new GRN cost calculation, which will affect parent/child. only available if the item not fresh market, not consignment module and got config "sku_update_cost_by_parent_child".

11/30/2012 12:09 PM Andy
- Fix opening balance bug on group calculation.

1/28/2013 10:46:58 AM yinsee
- fix "already running" for ARMS-GO (use 'ps x' instead of 'ps ax')

2/19/2013 4:20 PM Andy
- Fix when copy cross year stock balance, the start_qty should be same as qty.

2/28/2013 4:02 PM Andy
- Fix get cost sequence to calculate parent and then child, and always process stock take first, then follow by grn and others.

3/25/2013 10:30 AM Andy
- Enhanced new parent/child avg cost calculation.

3/27/2013 5:00 PM Andy
- Enhanced to skip zero qty grn for group calculation.
- Enhanced to check group total pcs before and after grn, if either them are negative will use grn cost as latest avg cost.

5/22/2013 11:54 AM Andy
- Add checking to config "terminal_check_own_process_only", if found it will only check own process.

7/10/2013 10:18 AM Andy
- Enhance cost calculation to consider got negative qty, so it will only calculate stock balance but won't affect cost.

08/01/2013 05:27 PM Justin
- Modified to replace config "terminal_check_own_process_only" into "arms_go_modules".

5/28/2015 11:53 AM Andy
- Fix fresh market cost calculation.

5/29/2015 3:06 PM Andy
- Enhance fresh market cost recalculate.

11/12/2015 5:33 PM Andy
- Fix fresh market cost span should filter from previous stock take date.
- Fixed cost of goods sold calculation do not add the sku write-off cost.

1/13/2016 3:06 PM Andy
- Enhance to set changed=2 when calculation in progress, this is to prevent got other transaction happen within the calculation and cause the wrong stock.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.

2/28/2017 3:58 PM Andy
- Fixed move cross year stock balance error.

4/21/2017 5:29 PM Justin
- Enhanced to auto generate next year stock balance cache table while reach year end.
- Optimised the scripts to reduce memory usage.

7/13/2017 11:44 AM Andy
- Fixed stock balance wrong when got parameter -m.

8/14/2017 1:43 PM Andy
- Fix cost calculation error if turn on parent and child average cost. (Method A and B)

9/8/2017 11:16 AM Andy
- Change to use grn cost as average cost if found average cost or total pcs is negative.

9/11/2017 2:20 PM Andy
- Bug fix on "global_cost_decimal_points" and "global_qty_decimal_points" missing in default config.
- Fix devision by zero warning.

10/2/2017 2:07 PM Andy
- Fixed if stock qty or total avg cost is negative before grn, take the grn cost to replace avg cost.

1/5/2017 4:24 PM Andy
- Fixed wrong avg cost when got -m
- Enhanced to able to pass -date=

1/29/2018 5:37 PM Andy
- Fixed vendor sku history bug when calculate history by sku.

1/31/2018 3:16 PM Andy
- Enhanced cost calculation to check work order.
- Fixed run_history() should always calculate stock balance even the sku is no inventory.

5/2/2018 4:55 PM Andy
- Added Foreign Currency feature.

6/28/2018 3:59 PM Andy
- Fixed stock_balance_b data error if got pass -date or -m

7/18/2018 11:01 AM Andy
- Fixed more stock_balance_b data error if got pass -date or -m.

8/29/2018 2:37 PM Andy
- Enhanced to calculate grn cost by multiply the grr tax percent.

2/26/2019 4:16 PM Andy
- Fixed Fresh Market cost need to deduct DO cost.

8/18/2020 1:44 PM Andy
- Increase memory limit to 1G.
*/
define("TERMINAL",1);
include("config.php");
include_once("default_config.php");
if((version_compare(PHP_VERSION, '7.0.0', '>='))){
	require("include/mysqli.php");
}else{
	require("include/mysql.php");
}

require_once('include/functions.php');

//ini_set('memory_limit', '256M');
ini_set('memory_limit', '1G');
set_time_limit(0);
error_reporting (E_ALL ^ E_NOTICE);
//$config['consignment_modules'] = 1;
//$db_default_connection = array("localhost", "root", "", "arms_cm"); // run for port 2004
//$db_default_connection = array("localhost", "root", "", "armshq_segi"); // run for port 2005
//$db_default_connection = array(":/tmp/mysql.sock3", "root", "", "armstest"); // run for port 2005
//$db_default_connection = array("10.1.1.202", "arms", "Arms54321.", "arms_montanicsb");
//$db_default_connection = array("10.1.1.202", "arms", "Arms54321.", "armshq_cst"); // port 2003
//$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

// check if myself is running, exit if yes
if (!preg_match('/(root|arms|admin|wsatp)/', `whoami`) || $config['arms_go_modules']){
	@exec('ps x | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
	print "Checking other process using ps x\n";
}else{
	@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
	print "Checking other process using ps ax\n";
}
  
  
if (count($exec)>1)
{
	print date("[H:i:s m.d.y]")." Another process is already running\n";
	print_r($exec);
	exit;
}

$arg = $_SERVER['argv'];
if ($arg[1]=='setup')
{
	setup();
	print "Setup complete.\n";
	exit;
}

$verbose = false;
array_shift($arg);
$BRANCH_CODE = strtolower(array_shift($arg));
while($arg)
{
	$a = strtolower(array_shift($arg));

	if($a=='verbose')
	    $verbose = true;
	elseif(preg_match("/^-m/", $a)){  // count only last X months
		$month_to_less = abs(intval(str_replace("-m", '', $a)));
	}elseif(preg_match("/^-date=/", $a)){
		$selected_filter_from_date = str_replace("-date=", "", $a);
	}
}

if($month_to_less>0){ // got month filter
  $selected_filter_from_date = date('Y-m-d',strtotime("-$month_to_less month"));
}
if($selected_filter_from_date){
	print "Checking From Date: $selected_filter_from_date\n";
}

//print "grn_do_hq2branch_update_cost = ".$config['grn_do_hq2branch_update_cost'];
//exit;
// if ($BRANCH_CODE == 'HQ') die("no no i won't run in HQ.");

// config below is used for the entire cost calculation
/*$config['sku_use_avg_cost_as_last_cost'] = 0;
$config['sku_update_cost_by_parent_child'] = 0;
$config['enable_no_inventory_sku'] = 0;
$config['enable_fresh_market_sku'] = 0;
$config['use_grn_last_vendor_include_master'] = 0;
$config['grn_do_hq2branch_update_cost'] = 1;
$config['grn_do_branch2branch_update_cost'] = 0;
$config['grn_do_branch2hq_update_cost'] = 0;*/

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

//while (1)
//{
	//$hqcon = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);
	//$con = connect_db($read_mysql?$read_mysql:$db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);
	//$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);
	
	if (!$con)
	{
	    die("Failed to connect DB.\n");
	}

	// check renewal
	if ($BRANCH_CODE == 'all')
	{
		$branches = array();
		$q1 = $con->sql_query("select code from branch where active=1 order by sequence");
		while($r=$con->sql_fetchassoc($q1))
		{
		    if($config['consignment_modules']&&$r['code']=='HQ')    continue;
			$branches[] = $r['code'];
		}
		$con->sql_freeresult($q1);
	}
	else
	{
		$branches = array($BRANCH_CODE);
	}
	
	$con->sql_query("create table if not exists tmp_cron_cost_history_info(
		branch_id int primary key,
		version int default 0
	)");
	
	//$config['sku_use_avg_cost_as_last_cost'] = 1;
	$sku_use_avg_cost_as_last_cost = mi($config['sku_use_avg_cost_as_last_cost']);
	$sku_update_cost_by_parent_child = mi($config['sku_update_cost_by_parent_child']);
	//$sku_update_cost_by_parent_child = 1;
	
	foreach($branches as $BRANCH_CODE)
	{
		check_cron_cost_history_version($BRANCH_CODE);
		
		print date("[H:i:s m.d.y]")." [".memory_get_usage()."] Checking sku changes in $BRANCH_CODE...\n";

		$q_b = $con->sql_query("select id from branch where code = ".ms($BRANCH_CODE), false, false);
		$r = $con->sql_fetchassoc($q_b);
		$con->sql_freeresult($q_b);
		if (!$r) die("Invalid branch ".$BRANCH_CODE);
		$branch_id = $r['id'];
		// create empty stock balance table
		create_sb_table("stock_balance_b".$branch_id."_".(intval(date('Y'))-1), "stock_balance_b".$branch_id."_".intval(date('Y')));
		
		// initial vendor sku history table by branch
		initial_branch_vsh_table($branch_id);

		$rs1 = $con->sql_query("select si.id, si.sku_id, si.description, si.cost_price, if(sku.no_inventory='inherit', cc.no_inventory, sku.no_inventory) as no_inventory, 
		if(sku.is_fresh_market='inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market,sku.vendor_id as master_vendor_id,si.sku_item_code
		from sku_items si
		left join sku on sku.id=si.sku_id
		left join category_cache cc on cc.category_id=sku.category_id
		left join sku_items_cost sic on si.id=sic.sku_item_id and sic.branch_id=$branch_id
		where (sic.sku_item_id is null or sic.changed >= 1) order by si.sku_id, si.id",false, false);

		print $con->sql_numrows($rs1) . " SKU to update.\n";
		$total_n = $con->sql_numrows($rs1);
		while($r = $con->sql_fetchassoc($rs1))
		{
			print "$total_n $r[description]...\r";
			
			if(($sku_use_avg_cost_as_last_cost || $sku_update_cost_by_parent_child) && $r['is_fresh_market']=='no' && !$config['consignment_modules']){
				if($last_sku_id != $r['sku_id']){
					run_history_by_sku($r);
				}
				$last_sku_id = $r['sku_id'];
				//$con->sql_query("update sku_items_cost set changed=0 where sku_item_id=".ms($r['id'])." and branch_id=$branch_id");
			}else{
				run_history($r);
			}
			
			$total_n--;
		}
		$con->sql_freeresult($rs1);

		// cross year stock balance checking
		check_cross_year($branch_id);
		
		// check and create next year stock balance cache table
		create_next_year_sb_tbl($branch_id);

		print "Done.\n";
	}

	sleep(30);
//}

function run_history($si, $params = array())
{
	global $branch_id, $con, $verbose, $config, $selected_filter_from_date;
    $filter_from_date = $selected_filter_from_date;
    //$config['enable_fresh_market_sku'] = 1;   for maximus testing purpose
    //$config['consignment_modules'] = 1;
    
    $consignment_branch_need_update_cost = false;
    if($config['enable_no_inventory_sku'])	$no_inventory = $si['no_inventory'];
    if($config['enable_fresh_market_sku']) $is_fresh_market = $si['is_fresh_market'];
	
	$sku_item_id = $si['id'];
	$sku_item_code = $si['sku_item_code'];
	$sku_id = $si['sku_id'];
	$cost = doubleval($si['cost_price']);
	$master_cost = doubleval($si['cost_price']);
	$fresh_market_cost = 0;
	$master_vendor_id = mi($si['master_vendor_id']);
	$last_vendor_id = 0;
	
	// set changed = 2 (in progress)
	$con->sql_query("update sku_items_cost set changed=2 where branch_id=$branch_id and sku_item_id=$sku_item_id");
	
	$sid_list = array();
	$vendor_data = array();
	$vendor_sku_history_tbl = 'vendor_sku_history_b'.$branch_id;
	
	// check again for latest changed
	if(!$params['skip_checking']){
        $q_sic = $con->sql_query("select changed from sku_items_cost where branch_id=$branch_id and sku_item_id=$sku_item_id");
		$still_changed = $con->sql_fetchfield(0);
		$con->sql_freeresult($q_sic);
		if($still_changed===0) return; // already no changed, so no need to process
	}
	
	// if is fresh market items and no fresh market cost data
	if($is_fresh_market=='yes' && !$params['fresh_market_cost_span'] && !$params['run_normal']){
		generate_parent_fresh_market_cost_span($si);    // generate fresh market cost span
		return;
	}    
	else{
		
	}	
	$where_sid = "sku_items.id = $sku_item_id ";
	if(isset($params) && $params['filter_from_date'])	$filter_from_date = $params['filter_from_date'];
	
	//print "filter_from_date = $filter_from_date\n";exit;
	
	if($filter_from_date){
		// cost history
		$q_his = $con->sql_query("select * from sku_items_cost_history where branch_id=$branch_id and sku_item_id=$sku_item_id and date<'$filter_from_date' order by date desc limit 1");
		$cost_history = $con->sql_fetchassoc($q_his);
		$con->sql_freeresult($q_his);
		if($cost_history){
			$cost = floatval($cost_history['grn_cost']);
			$use_this_avg_cost = floatval($cost_history['avg_cost']);
			$fresh_market_cost = floatval($cost_history['fresh_market_cost']);
			$use_this_qty = floatval($cost_history['qty']);
			$filter_from_date = date('Y-m-d', strtotime("+1 day", strtotime($cost_history['date'])));
		}else	$filter_from_date = '';
	}
	
	//print "filter_from_date = $filter_from_date\n";exit;
	
	if($filter_from_date){
		// vendor sku history
		$q_his = $con->sql_query("select * from $vendor_sku_history_tbl where sku_item_id=$sku_item_id and ".ms($filter_from_date)." between from_date and to_date limit 1");
		while($tmp_data = $con->sql_fetchassoc($q_his)){
			$tmp_data['to_date'] = '';
			$vendor_data[$tmp_data['vendor_id']] = $tmp_data;
		}
		$con->sql_freeresult($q_his);
	}
	$con->sql_query("delete from $vendor_sku_history_tbl where sku_item_id=$sku_item_id ".($filter_from_date? " and (to_date>='$filter_from_date')":""));
	
	// no vendor data and use grn last vendor can include master
	if(!$vendor_data && $config['use_grn_last_vendor_include_master'] && $master_vendor_id){
		$tmp_data['sku_item_id'] = $sku_item_id;
		$tmp_data['vendor_id'] = $master_vendor_id;
		$vendor_data[$master_vendor_id] = $tmp_data;
		unset($tmp_data);
	}

	$data = array();

	if($config['consignment_modules']){   // cost changed history from HQ
	    if($filter_from_date){
	        $hq_c_filter = " and date>'$filter_from_date'";
	    }

		$q_his = $con->sql_query("select * from sku_items_cost_history where branch_id=1 and sku_item_id=$sku_item_id and date>0 $hq_c_filter order by date");
		while($r=$con->sql_fetchassoc($q_his))
		{
		    if($branch_id>1){   // is branches
                $data[$r['date']]['hq_cost_changed'] = $r['grn_cost'];
				$data[$r['date']]['hq_avg_cost_changed'] = $r['avg_cost'];
			}elseif($branch_id==1){ // is HQ
				$hq_cost_history_cache[$r['date']]['grn_cost'] = $r['grn_cost'];
			}

		}
		$con->sql_freeresult($q_his);
	}
	
	if($is_fresh_market=='yes'){
		$fresh_market_cost_span = $params['fresh_market_cost_span'] ? $params['fresh_market_cost_span'] : array();
		foreach($fresh_market_cost_span as $d=>$r){
			$data[$d]['fresh_market_cost_changed'] = $r['cost'];
		}
	}

	// stock check
	$q_sc = $con->sql_query("select sum(qty) as qty, sum(qty*cost) as cost, date from stock_check left join sku_items using (sku_item_code) 
	where $where_sid and branch_id = $branch_id ".($filter_from_date? " and date>='$filter_from_date'":"")." group by date order by date", false, false);
	while($r=$con->sql_fetchassoc($q_sc))
	{
		$data[$r['date']]['stock_check'] = $r['qty'];
		$data[$r['date']]['stock_check_cost'] = $r['cost'];
	}
	$con->sql_freeresult($q_sc);

	//if($no_inventory=='no'||!$no_inventory){
        // POS
		$q_p = $con->sql_query("select qty, date as dt from sku_items_sales_cache_b$branch_id where sku_item_id = $sku_item_id".($filter_from_date? " and date>='$filter_from_date'":""), false, false);
		while($r=$con->sql_fetchassoc($q_p))
		{
			$data[$r['dt']]['pos'] = $r['qty'];
		}
		$con->sql_freeresult($q_p);

		// GRA
		$q_gra = $con->sql_query("select sum(qty) as qty, date(return_timestamp) as dt from gra_items left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id left join sku_items on sku_item_id = sku_items.id where $where_sid and gra_items.branch_id = $branch_id and gra.status=0 and gra.returned=1 ".($filter_from_date? " and return_timestamp>='$filter_from_date'":"")." group by dt", false, false);
		while($r=$con->sql_fetchassoc($q_gra))
		{
			$data[$r['dt']]['gra'] = $r['qty'];
		}
		$con->sql_freeresult($q_gra);

		//FROM DO
		$q_do = $con->sql_query("select sum(do_items.ctn *uom.fraction + do_items.pcs) as qty, do.do_date as dt
	from do_items
	left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
	left join sku_items on sku_item_id = sku_items.id
	left join uom on do_items.uom_id=uom.id
	where $where_sid and do_items.branch_id = $branch_id and do.approved=1 and do.checkout=1 and do.status<2 and do.active=1 ".($filter_from_date? " and do_date>='$filter_from_date'":"")." group by dt", false, false);
		while($r=$con->sql_fetchassoc($q_do))
		{
			$data[$r['dt']]['do'] = $r['qty'];
		}
		$con->sql_freeresult($q_do);

		//FROM ADJUSTMENT
		/*$q_adj = $con->sql_query("select sum(qty) as qty, adjustment_date as dt
	from adjustment_items
	left join adjustment on adjustment.id=adjustment_items.adjustment_id and adjustment.branch_id=adjustment_items.branch_id
	left join sku_items on sku_item_id = sku_items.id
	where $where_sid and adjustment_items.branch_id = $branch_id and adjustment.approved=1 and adjustment.status<2 and adjustment.active=1 ".($filter_from_date? " and adjustment_date>='$filter_from_date'":"")." group by dt", false, false);*/
		$q_adj = $con->sql_query("select adji.adjustment_id, adji.sku_item_id as sid, adj.adjustment_date as dt, adj.module_type, sum(if(adji.qty>0,adji.qty,0)) as positive_qty, sum(if(adji.qty<0,adji.qty,0)) as negative_qty, wo.id as wo_id, woi.finish_cost, woi.line_total_finish_cost, wo.wo_no
	from adjustment_items adji
	left join adjustment adj on adj.id=adji.adjustment_id and adj.branch_id=adji.branch_id
	left join work_order wo on wo.branch_id=adj.branch_id and wo.adj_id=adj.id and adj.module_type='work_order' and wo.active=1 and wo.status=1 and wo.completed=1
	left join work_order_items_in woi on woi.branch_id=wo.branch_id and woi.work_order_id=wo.id and woi.sku_item_id=adji.sku_item_id
	where adji.sku_item_id=$sku_item_id and adji.branch_id = $branch_id and adj.approved=1 and adj.status=1 and adj.active=1
	group by adjustment_id, sid, dt, module_type
	order by dt, adjustment_id");
		$work_order_data = array();
		while($r=$con->sql_fetchassoc($q_adj))
		{
			if($r['module_type'] == 'work_order' && $r['wo_id']){
			if($r['negative_qty']<0){	// negative is transfer out, not affect cost
				$data[$r['dt']]['adj'] += $r['negative_qty'];
			}
			if($r['positive_qty']>0){	// positive is transfer in, will affect cost
				$tmp = array();
				$tmp['qty'] = $r['positive_qty'];
				$tmp['cost'] = $r['finish_cost'];
				$tmp['total_cost'] = $r['line_total_finish_cost'];
				//$tmp['link'] = sprintf("<a target=_blank href=\"/work_order.php?a=view&highlight_in_sid=$r[sid]&branch_id=$branch_id&id=%d\">%s</a>",trim($r['wo_id']),trim($r['wo_no']));
				$data[$r['dt']]['wo_list'][] = $tmp;
				
				// store by grn data
				$wo_key = $branch_id."_".$r['wo_id'];
				$work_order_data[$r['dt']][$wo_key]['qty'] += $tmp['qty'];
				$work_order_data[$r['dt']][$wo_key]['cost'] += $tmp['cost'];
				$work_order_data[$r['dt']][$wo_key]['total_cost'] += $tmp['total_cost'];
				//$work_order_data[$r['dt']][$wo_key]['link'] = $tmp['link'];	
			}	
		}else{
			// normal adjustment
			$data[$r['dt']]['adj'] += $r['positive_qty']+$r['negative_qty'];
		}
		}
		$con->sql_freeresult($q_adj);

		// grn
		$sql = "select (if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
		(
		  if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost)
		  *
		  if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
		  	grn_items.ctn + grn_items.pcs / rcv_uom.fraction,
		  	grn_items.acc_ctn + grn_items.acc_pcs / rcv_uom.fraction
		  )
		) as cost, grr.rcv_date as dt, grn.grr_id, grn.is_future,
		gi.type, do.do_type, do.branch_id as do_from_branch_id,grr.vendor_id, grr.currency_code, if(grr.currency_rate<0,1,grr.currency_rate) as currency_rate, grr.tax_percent as grr_tax_percent
		from grn_items
		left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
		left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
		left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
		left join grr_items gi on gi.id=grn.grr_item_id and gi.branch_id=grn.branch_id
		left join do on do.do_no=gi.doc_no and gi.type='DO'
		left join sku_items on grn_items.sku_item_id = sku_items.id
		where $where_sid and grn_items.branch_id = $branch_id and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1".($filter_from_date? " and grr.rcv_date>='$filter_from_date'":"")." order by grr.rcv_date, grr.id";

		$sql1 = $con->sql_query($sql, false, false);

		while($r=$con->sql_fetchassoc($sql1))
		{
		    $data[$r['dt']]['grn'] += $r['qty'];
		    $count_this_grn = false;

			if($r['qty'] > 0){	// if qty > 0, only check whether to calculate cost
				if($r['is_future']){
					$sql2 = $con->sql_query("select type, 
										 case when type = 'PO' then 1 when type = 'INVOICE' then 2 when type = 'DO' then 3 else 4 end as type_asc, do.do_type, do.branch_id as do_from_branch_id
										 from grr_items gi
										 left join grr on grr.id = gi.grr_id and grr.branch_id = gi.branch_id
										 left join do on do.do_no=gi.doc_no and gi.type='DO'
										 where gi.grr_id = $r[grr_id] and gi.branch_id = $branch_id
										 group by type_asc
										 order by type_asc asc
										 limit 1");
	
					$gi_info = $con->sql_fetchassoc($sql2);
					$con->sql_freeresult($sql2);
					$r['type'] = $gi_info['type'];
					$r['do_type'] = $gi_info['do_type'];
					$r['do_from_branch_id'] = $gi_info['do_from_branch_id'];
				}
			
				if($r['type']!='DO'){
		            $count_this_grn = true;
				}else{  // document type = DO
				    if(!$r['do_type']||!$r['do_from_branch_id'])  $count_this_grn = true; // DO from outside
				    else{   // inter transfer DO
		                if($config['grn_do_hq2branch_update_cost']&&$branch_id>1&&$r['do_from_branch_id']==1)   $count_this_grn = true;
		                if($config['grn_do_branch2branch_update_cost']&&$branch_id>1&&$r['do_from_branch_id']>1)   $count_this_grn = true;
		                if($config['grn_do_branch2hq_update_cost']&&$branch_id==1&&$r['do_from_branch_id']>1)   $count_this_grn = true;
					}
				}
			}
		    
		    /*if(($config['grn_do_transfer_update_cost']&&$r['type']=='DO'&&$r['do_type']=='transfer')||($config['grn_do_all_update_cost']&&$r['type']=='DO')){
		      $count_this_grn = true;
			}else{
			    if(($config['grn_do_branch_update_cost']&&$r['type']=='DO'&&$branch_id>1)||($r['type']!='DO')){
		        	$count_this_grn = true;
		      	}
			}*/

		    if($count_this_grn){
				if($r['currency_code']){	// Store Foreign Currency Cost
					$data[$r['dt']]['currency_grn_cost'][$r['currency_code']]['grn_cost'] += $r['cost'];
				}
				
				$base_cost = $r['cost']*$r['currency_rate'];
				$base_tax = ($base_cost*$r['grr_tax_percent']/100);
			
				$data[$r['dt']]['grn_cost'] += $base_cost+$base_tax;
				$data[$r['dt']]['grn_tax'] += $base_tax;
				$data[$r['dt']]['grn_qty_to_divide'] += $r['qty'];
		      	$data[$r['dt']]['grn_cost_need_update'] = true;
		      	
		      	if($r['vendor_id']){
		      		// multiple vendor at same date
					/*if(!$data[$r['dt']]['vendor_id_list'])	$data[$r['dt']]['vendor_id_list'] = array();
					if(!in_array($r['vendor_id'], $data[$r['dt']]['vendor_id_list'])){
						$data[$r['dt']]['vendor_id_list'][] = $r['vendor_id'];
					}*/
					
					// only last vendor for same date
					$data[$r['dt']]['vendor_id_list'] = array($r['vendor_id']);
				}
		    }
		}
		$con->sql_freeresult($sql1);

		if($config['consignment_modules']){
	        //FROM Credit Note
			$q_cn = $con->sql_query("select sum(cn_items.ctn *uom.fraction + cn_items.pcs) as qty, cn.date as dt
	from cn_items
	left join cn on cn.id=cn_items.cn_id and cn.branch_id=cn_items.branch_id
	left join sku_items on sku_item_id = sku_items.id
	left join uom on cn_items.uom_id=uom.id
	where $where_sid and cn.to_branch_id = $branch_id and cn.active=1 and cn.approved=1 and cn.status=1 ".($filter_from_date? " and cn.date>='$filter_from_date'":"")." group by dt", false, false);
			while($r=$con->sql_fetchassoc($q_cn))
			{
				$data[$r['dt']]['cn'] = $r['qty'];
			}
			$con->sql_freeresult($q_cn);

			//FROM Debit Note
			$q_dn = $con->sql_query("select sum(dn_items.ctn *uom.fraction + dn_items.pcs) as qty, dn.date as dt
	from dn_items
	left join dn on dn.id=dn_items.dn_id and dn.branch_id=dn_items.branch_id
	left join sku_items on sku_item_id = sku_items.id
	left join uom on dn_items.uom_id=uom.id
	where $where_sid and dn.to_branch_id = $branch_id and dn.active=1 and dn.approved=1 and dn.status=1 ".($filter_from_date? " and dn.date>='$filter_from_date'":"")." group by dt", false, false);
			while($r=$con->sql_fetchassoc($q_dn))
			{
				$data[$r['dt']]['dn'] = $r['qty'];
			}
			$con->sql_freeresult($q_dn);
		}
	//}

	ksort($data);
	reset($data);

	$qty = 0;

	//$con->sql_query("select cost_price from sku_items where id = $sku_item_id", false, false);
	//$t=$con->sql_fetchrow();
	//$con->sql_freeresult();
	//$cost = doubleval($t[0]);

	$avg_cost = $cost;
	$avg_total_cost = 0;
	$avg_total_qty = 0;
	$last_qty = 0;

	if($cost_history){
	    $avg_cost = $use_this_avg_cost;
	    $qty = $use_this_qty;
	    $avg_total_qty = $use_this_qty;
	    $avg_total_cost = $avg_total_qty*$avg_cost;
	}
	  
	if($filter_from_date){
        // get stock balance table row
	    $filter_sb_year = date('Y',strtotime($filter_from_date));
	    $q_sb = $con->sql_query("select * from stock_balance_b".$branch_id."_".$filter_sb_year." where sku_item_id=$sku_item_id and '$filter_from_date' between from_date and to_date limit 1",false,false);
	    $temp = $con->sql_fetchassoc($q_sb);
		$con->sql_freeresult($q_sb);
	    if($temp){
	      	$sb['from_date'] = $temp['from_date'];
	  		$sb['to_date'] = $temp['from_date'];
            $sb['start_qty'] = floatval($temp['start_qty']);
	  		$sb['qty'] = floatval($temp['qty']);
	  		$sb['cost'] = floatval($temp['cost']);
	  		$sb['avg_cost'] = floatval($temp['avg_cost']);
	  		$sb['fresh_market_cost'] = floatval($temp['fresh_market_cost']);
	  		$sb['sku_item_id'] = intval($temp['sku_item_id']);
	  		$last_qty = $sb['qty'];
	    }
	  }

	$con->sql_query("delete from sku_items_cost_history where branch_id=$branch_id and sku_item_id = $sku_item_id ".($filter_from_date? " and (date>='$filter_from_date' or date=0)":""), false, false);

	$last3mth = date('Y-m-d', strtotime("-3 month"));
	$l90d_grn = 0;
	$l90d_pos = 0;
	$last1mth = date('Y-m-d', strtotime("-1 month"));
	$l30d_grn = 0;
	$l30d_pos = 0;
	$sb_year = 0;

	$sb_list = array();
	//print_r($data);
	foreach ($data as $d => $t)
	{
	    $hq_cost_changed = false;
	    $fresh_market_cost_changed = false;
		$cost_changed = false;
	    $d2 = date('Y-m-d',strtotime('-1 day',strtotime($d)));
	    
	    unset($hq_cost);
	    unset($hq_avg_cost);

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
		if ($verbose)
		{
			print "\n$d ";
			print_r($t);
		}
		if (isset($t['stock_check']))
		{
			if ($t['stock_check_cost']==0) $t['stock_check_cost']=$cost*$t['stock_check'];
			if ($verbose) print "\tSCHK: $t[stock_check] @ ".($t['stock_check']?$t['stock_check_cost']/$t['stock_check']:"0");
			$avg_total_qty = $t['stock_check'];
			
			if ($t['stock_check']>0)
			{
				//$cost =  round($t['stock_check_cost'] / $t['stock_check'], 5);
				//$avg_cost =  round($avg_total_cost/$avg_total_qty, 5);
				if($config['consignment_modules']&&$branch_id>1){
					//get_hq_cost($sku_item_id, $d, $hq_cost, $hq_avg_cost, $master_cost);
					//$cost = $hq_cost;
					//$avg_cost = $hq_avg_cost;
					$avg_total_cost = $avg_total_qty * $avg_cost;
				}else{
                    $cost =  round($t['stock_check_cost'] / $t['stock_check'], 5);
					//$avg_cost =  round($avg_total_cost/$avg_total_qty, 5);
					//$avg_total_cost = $t['stock_check_cost'];
					$avg_total_cost = $avg_total_qty * $avg_cost;
				}

			}
			else
			{
				//$cost =  0;
				//$avg_cost =  0;
			}
			
			$cf = $qty;
			$qty = $t['stock_check'];
			
			if($cf<=0 || $qty<=0){
				$avg_cost =  $cost;
				$avg_total_cost = $avg_total_qty*$avg_cost;
			}

		}
		if ($t['grn'])
		{
			if ($verbose) print "\tGRN: $t[grn] @ ".round($t['grn_cost']/$t['grn'], 5);
			if($config['consignment_modules']&&$branch_id>1){
				// branch no need update cost if is consignment
			}elseif($t['grn_cost_need_update']){
	        	if ($qty<=0)	// reset to GRN cost if stock level below or equal zero
				{
					if ($verbose) print "\treset AVG_COST";
					$avg_total_cost = $t['grn_cost'];
					$avg_total_qty = $t['grn_qty_to_divide'];
				}
				else
				{
					$avg_total_cost += $t['grn_cost'];
					$avg_total_qty += $t['grn_qty_to_divide'];
				}

	        	if($t['grn_qty_to_divide']){
                    if($t['grn_cost'])	$cost = round($t['grn_cost'] / $t['grn_qty_to_divide'],5);
                    $avg_cost =  round($avg_total_cost/$avg_total_qty, 5);
				}
			}
			$qty += $t['grn'];
		}
		
		if(isset($work_order_data[$d])){
				foreach($work_order_data[$d] as $wo_key=>$wo){
					$qty += $wo['qty'];
					$cost = round($wo['cost'], $config['global_cost_decimal_points']);
					$avg_total_cost += round($wo['cost'], $config['global_cost_decimal_points']);
					$avg_total_qty += $wo['qty'];
					$avg_cost =  round($avg_total_cost / $avg_total_qty, $config['global_cost_decimal_points']);
					$cost_changed = true;
				}
			}
			
		if ($t['gra']) {
			if ($verbose) print "\tGRA: $t[gra]";
			$qty -= $t['gra'];
		}
		if ($t['pos']) {
			if ($verbose) print "\tPOS: $t[pos]";
			$qty -= $t['pos'];
		}
		if ($t['do']) {
			if ($verbose) print "\tDO: $t[do]";
			$qty -= $t['do'];
		}
		if ($t['adj']) {
			if ($verbose) print "\tAdj: $t[adj]";
			$qty += $t['adj'];
		}
		if ($t['cn']) {
			if ($verbose) print "\tCN: $t[cn]";
			$qty += $t['cn'];
		}
		if ($t['dn']) {
			if ($verbose) print "\tDN: $t[dn]";
			$qty -= $t['dn'];
		}

		if($t['hq_cost_changed']){
            if($cost!=$t['hq_cost_changed']){
		        $hq_cost_changed = true;
                $cost = $t['hq_cost_changed'];
                $avg_cost = $t['hq_avg_cost_changed'];
	            $avg_total_cost = $avg_cost * $avg_total_qty;
			}
		}
		
		if($t['fresh_market_cost_changed']){
		    if($fresh_market_cost!=$t['fresh_market_cost_changed']){
		        $fresh_market_cost_changed = true;
                $fresh_market_cost = $t['fresh_market_cost_changed'];
			}
		}
		
		if($t['vendor_id_list']){
			// loop for last changed vendor
			foreach($vendor_data as $tmp_vendor_id => $vd){
				$got_changed = true;
				
				// check new vendor list
				foreach($t['vendor_id_list'] as $new_vendor_id){
					if($tmp_vendor_id == $new_vendor_id){	//this vendor need maintain at this date
						$got_changed = false;
						break;
					}
				}
				
				if($got_changed){
					$vd['to_date'] = $d2;
					$con->sql_query("insert into $vendor_sku_history_tbl ".mysql_insert_by_field($vd));
					unset($vendor_data[$tmp_vendor_id]);
				}
			}
			
			foreach($t['vendor_id_list'] as $new_vendor_id){
				if(!$vendor_data[$new_vendor_id]){
					$tmp_data = array();
					$tmp_data['sku_item_id'] = $sku_item_id;
					$tmp_data['from_date'] = $d;
					$tmp_data['vendor_id'] = $new_vendor_id;
					$vendor_data[$new_vendor_id] = $tmp_data;
				}
			}
		}
		
		//if($no_inventory=='yes'){
		//    $avg_cost = $cost;
		//    $qty = 0;
		//    $avg_total_cost = $avg_total_qty * $avg_cost;
		//}

		//if ($qty <= 0) $avg_cost = 0;

		if ($verbose) print "\tBalance: $qty\tCost: $cost\tAvg_Cost: $avg_cost ($avg_total_cost / $avg_total_qty)\n";
		// stock balance
		if($sb && ($sb['qty']!=$qty || $sb['cost']!=$cost || ($sb['fresh_market_cost']!=$fresh_market_cost && $config['enable_fresh_market_sku']))){
			if(strtotime($sb['from_date']) == strtotime($d)){
				$sb['to_date'] = $d;
				$sb['qty'] = floatval($qty);
				$sb['cost'] = floatval($cost);
				$sb['fresh_market_cost'] = floatval($fresh_market_cost);
				$sb['avg_cost'] = floatval($avg_cost);
			}else{
				//print "d = $d";
				//print_r($sb);
				$last_qty = $sb['qty'];
				$sb['to_date'] = date('Y-m-d',strtotime('-1 day',strtotime($d)));
				
				// fresh market cost changed
				if($sb['fresh_market_cost']!=$fresh_market_cost && $config['enable_fresh_market_sku']){
					if($fm_cost_changed){
						$fm_cost_changed['to_date'] = date('Y-m-d',strtotime('-1 day',strtotime($d)));
						$fm_cost_changed_list[] = $fm_cost_changed;
						$fm_cost_changed = array();
					}
				
					$temp = array();
					$temp['sku_item_id'] = $sku_item_id;
					$temp['branch_id'] = $branch_id;
					$temp['fresh_market_cost'] = $fresh_market_cost;
					$temp['date_from'] = $d;
					$temp['date_to'] = '';
					//$temp['mysql_con'] = $con;
					//print_r($temp);
					
					$fm_cost_changed = $temp;
					//update_sales_cache_fresh_market_cost($temp);
				}
				
				$sb_list[] = $sb;
				$sb = array();
			}
		}
		if(!$sb){
			$sb['from_date'] = $d;
			$sb['to_date'] = $d;
			$sb['start_qty'] = $last_qty;
			$sb['qty'] = floatval($qty);
			$sb['cost'] = floatval($cost);			
			$sb['avg_cost'] = floatval($avg_cost);
			$sb['fresh_market_cost'] = $fresh_market_cost;
			$sb['sku_item_id'] = intval($sku_item_id);
		}


		if (isset($t['grn']) || isset($t['stock_check']) || ($hq_cost_changed && $config['consignment_modules']) || ($config['enable_fresh_market_sku'] && $fresh_market_cost_changed) || $cost_changed){
			$temp = array();
			$temp['branch_id'] = $branch_id;
			$temp['sku_item_id'] = $sku_item_id;
			$temp['grn_cost'] = $cost;			
			$temp['avg_cost'] = $avg_cost;
			$temp['fresh_market_cost'] = $fresh_market_cost;
			$temp['qty'] = $qty;
			$temp['date'] = $d;
			
            $con->sql_query("insert into sku_items_cost_history ".mysql_insert_by_field($temp), false, false);
            unset($temp);
            if(!$consignment_branch_need_update_cost){
                $consignment_branch_need_update_cost = check_consignment_need_update_cost($hq_cost_history_cache, $d, $cost);
			}
		}
		$last_date = $d;
	}

    if(($cost_history && $last_date)||!$cost_history){
    	$temp = array();
    	$temp['branch_id'] = $branch_id;
    	$temp['sku_item_id'] = $sku_item_id;
    	$temp['grn_cost'] = $cost;    	
    	$temp['avg_cost'] = $avg_cost;
    	$temp['fresh_market_cost'] = $fresh_market_cost;
    	$temp['qty'] = $qty;
    	$temp['date'] = $last_date;
      	$con->sql_query("replace into sku_items_cost_history ".mysql_insert_by_field($temp), false, false);
      	unset($temp);
      	
      	if(!$consignment_branch_need_update_cost){
            $consignment_branch_need_update_cost = check_consignment_need_update_cost($hq_cost_history_cache, $d, $cost);
		}
    }

	//$con->sql_query("update sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id=$sku_item_id");
	
	if($filter_from_date && !$last_date){	// no last date when got filter -m
		$con->sql_query("select date from sku_items_cost where branch_id=$branch_id and sku_item_id=$sku_item_id");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$last_date = $tmp['date'];
	}
		
	$temp = array();
	$temp['branch_id'] = $branch_id;
	$temp['sku_item_id'] = $sku_item_id;
	$temp['grn_cost'] = $cost;	
	$temp['avg_cost'] = $avg_cost;
	$temp['fresh_market_cost'] = $fresh_market_cost;
	$temp['qty'] = $qty;
	$temp['date'] = $last_date;
	$temp['l90d_grn'] = $l90d_grn;
	$temp['l90d_pos'] = $l90d_pos;
	$temp['l30d_grn'] = $l30d_grn;
	$temp['l30d_pos'] = $l30d_pos;
	$temp['changed'] = 0;
    //$con->sql_query("replace into sku_items_cost ".mysql_insert_by_field($temp), false, false);
	
	$q_sic = $con->sql_query("select changed from sku_items_cost where branch_id=$branch_id and sku_item_id=$sku_item_id");
	$latest_changed = $con->sql_fetchfield(0);
	$con->sql_freeresult($q_sic);
	if($latest_changed && $latest_changed != 2)	$temp['changed'] = 1;	// got changes when this calculation is running
	//die("check changed  = $latest_changed\n");
	
	$con->sql_query("insert into sku_items_cost ".mysql_insert_by_field($temp)." on duplicate key update
		grn_cost=".mf($temp['grn_cost']).",
		avg_cost=".mf($temp['avg_cost']).",
		fresh_market_cost=".mf($temp['fresh_market_cost']).",
		qty=".mf($temp['qty']).",
		date=".ms($temp['date']).",
		l90d_grn=".mf($temp['l90d_grn']).",
		l90d_pos=".mf($temp['l90d_pos']).",
		l30d_grn=".mf($temp['l30d_grn']).",
		l30d_pos=".mf($temp['l30d_pos']).",
		changed=".mi($temp['changed'])."
		");

	if($sb){
		$sb['to_date'] = '';
		// fresh market cost changed
		if($config['enable_fresh_market_sku'] && $is_fresh_market){
			if($fm_cost_changed){
				//$fm_cost_changed['to_date'] = date('Y-m-d',strtotime('-1 day',strtotime($d)));
				$fm_cost_changed_list[] = $fm_cost_changed;
				$fm_cost_changed = array();
			}
			
			if($fm_cost_changed_list){
				foreach($fm_cost_changed_list as $temp){
					$temp['mysql_con'] = $con;
					//print_r($temp);
					update_sales_cache_fresh_market_cost($temp);
				}
			}
	
			/*$temp = array();
			$temp['sku_item_id'] = $sku_item_id;
			$temp['branch_id'] = $branch_id;
			$temp['fresh_market_cost'] = $fresh_market_cost;
			$temp['date_from'] = $sb['from_date'];
			$temp['date_to'] = $sb['to_date'];
			$temp['mysql_con'] = $con;
			print_r($temp);
			update_sales_cache_fresh_market_cost($temp);*/
			
			// set fresh market cost updated
			$con->sql_query("update stock_check set fresh_market_updated=1 where branch_id=$branch_id and sku_item_code=".ms($sku_item_code)." ".
			($filter_from_date? " and date>='$filter_from_date'":""));
		}
		$sb_list[] = $sb;
	}
	
	if($vendor_data){
		foreach($vendor_data as $tmp_vendor_id => $vd){
			$last_vendor_id = $tmp_vendor_id;
			
			$vd['to_date'] = '9999-12-31';
			$con->sql_query("insert into $vendor_sku_history_tbl ".mysql_insert_by_field($vd));
			unset($vendor_data[$tmp_vendor_id]);
		}
	}
	if(!$last_vendor_id)	$last_vendor_id = $master_vendor_id;
	
	// last vendor
	$last_vd = array();
	$last_vd['sku_item_id'] = $sku_item_id;
	$last_vd['from_date'] = $last_vd['to_date'] = '0000-00-00';
	$last_vd['vendor_id'] = $last_vendor_id;
	$con->sql_query("delete from $vendor_sku_history_tbl where sku_item_id=$sku_item_id and from_date='0000-00-00' and to_date='0000-00-00'");
	$con->sql_query("replace into $vendor_sku_history_tbl".mysql_insert_by_field($last_vd));

	// clear sku data first
	$q1 = $con->sql_query("show tables");
	while($r = $con->sql_fetchrow($q1)){
		if(strpos($r[0],'stock_balance_b'.$branch_id.'_')===false){

		}else{
			if($filter_sb_year){  // do not delete all data if the deletion is just run for potion date
				$check_year = str_replace("stock_balance_b".$branch_id."_","",$r[0]);

				if($check_year<$filter_sb_year)  continue;  // skip the previous data
				elseif($check_year==$filter_sb_year){
					// if same year, only delete the data after the selected date
					$con->sql_query("delete from $r[0] where sku_item_id=".ms($sku_item_id)." and to_date>=".ms($filter_from_date),false,false);
					continue;
				}
			}
			
			

			$con->sql_query("delete from $r[0] where sku_item_id=".ms($sku_item_id),false,false);
			
			//print "delete sb year $check_year\n";
			//print "delete from $r[0] where sku_item_id=".ms($sku_item_id)."\n";
		}
	}
	$con->sql_freeresult($q1);

	//if($branch_id == 4 && $sku_item_id == 463934){
		//print_r($sb_list);
		//return;
	//}
  if($sb_list){ // insert stock balance data
    foreach($sb_list as $sb){
      create_stock_balance_row($sb, $branch_id);
    }
  }

  // consignment modules, sync HQ cost
  if($config['consignment_modules']&&$branch_id==1){
		if($consignment_branch_need_update_cost){
		    $q_b = $con->sql_query("select id from branch where active=1 and id>1");
		    $bid_list = array();
		    while($r = $con->sql_fetchassoc($q_b)){
				$bid_list[] = $r['id'];
			}
			$con->sql_freeresult($q_b);

			$con->sql_query("update sku_items_cost set changed=1 where sku_item_id=".ms($sku_item_id)." and branch_id in (".join(',',$bid_list).")");
		}
  }
  /*if($config['consignment_modules']&&$branch_id==1){
		sync_hq_cost($branch_id, $sku_item_id, $master_cost);
  }*/

  unset($sb_list);
  unset($sb);
}

function create_stock_balance_row($sb, $branch_id){
	global $con, $con ;

	$year = intval(date('Y',strtotime($sb['from_date'])));
	$latest_year = intval(date('Y'));

	if($year<=0)    return;
	$tbl = "stock_balance_b".$branch_id."_".$year;
	$last_year_tbl = "stock_balance_b".$branch_id."_".($year-1);
	create_sb_table($tbl,$last_year_tbl);

	if(!$sb['to_date']){ // is latest entry
		$sb['is_latest'] = 1;
		$sb['to_date'] = $year."-12-31";
	}
	else $sb['is_latest']=0;

	$con->sql_query("replace into $tbl (sku_item_id,from_date,to_date,qty,start_qty,cost,avg_cost,fresh_market_cost,is_latest) values ('$sb[sku_item_id]','$sb[from_date]','$sb[to_date]','$sb[qty]','$sb[start_qty]','$sb[cost]','$sb[avg_cost]',".ms($sb['fresh_market_cost']).",'$sb[is_latest]')") or die(mysql_error());

	$to_year = intval(date('Y',strtotime($sb['to_date'])));
	$c_year = $year;

  	if($c_year<$to_year){
		do{
			$c_year++;
			$tbl2 = "stock_balance_b".$branch_id."_".$c_year;
			create_sb_table($tbl2);
			$con->sql_query("replace into $tbl2 (sku_item_id,from_date,to_date,qty,start_qty,cost,avg_cost,fresh_market_cost, is_latest) values ('$sb[sku_item_id]',".ms($c_year.'-1-1').",".ms($sb['to_date']).",'$sb[qty]','$sb[qty]','$sb[cost]','$sb[avg_cost]',".ms($sb['fresh_market_cost']).",'$sb[is_latest]')");
		}while($c_year<$to_year);
	}

	if($c_year<$latest_year&&$sb['is_latest']){
		do{
			$c_year++;
			$tbl2 = "stock_balance_b".$branch_id."_".$c_year;
			create_sb_table($tbl2);
			$con->sql_query("replace into $tbl2 (sku_item_id,from_date,to_date,qty,start_qty,cost,avg_cost,fresh_market_cost,is_latest) values ($sb[sku_item_id],".ms($c_year.'-1-1').",".ms($c_year.'-12-31').",'$sb[qty]','$sb[qty]','$sb[cost]','$sb[avg_cost]',".ms($sb['fresh_market_cost']).",'$sb[is_latest]')") or die(mysql_error());
		}while($c_year<$latest_year);
	}
}

function create_sb_table($tbl,$tbl2=''){
	global $con;
	initial_branch_sb_table(array('tbl'=>$tbl, 'mysql_con'=>$con));
	/*if(!$con->sql_query("explain $tbl",false,false)){
		$con->sql_query("create table if not exists $tbl (
			sku_item_id int not null,
			from_date date,
			to_date date,
			start_qty double,
			qty double,
			cost double,
			avg_cost double,
			fresh_market_cost double,
			is_latest tinyint(1),
			index(sku_item_id),index(from_date),index(to_date),index(is_latest),
			index sid_n_fromDate_n_toDate(sku_item_id, from_date, to_date)
		)") or die(mysql_error());
	}*/

	if($tbl2){
	    initial_branch_sb_table(array('tbl'=>$tbl2, 'mysql_con'=>$con));
		/*if(!$con->sql_query("explain $tbl2",false,false)){
			$con->sql_query("create table if not exists $tbl2 (
				sku_item_id int not null,
				from_date date,
				to_date date,
				start_qty double,
				qty double,
				cost double,
				avg_cost double,
				fresh_market_cost double,
				is_latest tinyint(1),
				index(sku_item_id),index(from_date),index(to_date),index(is_latest),
				index sid_n_fromDate_n_toDate(sku_item_id, from_date, to_date)
			)") or die(mysql_error());
		}*/
	}
}

function connect_db($server, $u, $p, $db)
{
	$con = new sql_db($server, $u, $p, $db, false);
	if(!$con->db_connect_id)
	{
		print date("[H:i:s m.d.y] ");
		print("Error: Could not connect to database $u:$p@$server/$db\n".mysql_error());
		return false;
	}
	return $con;
}

// setup table
function setup()
{
	global $db_default_connection;

	$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

	print_r($con);

$con->sql_query("drop table if exists sku_items_cost_history") or die(mysql_error());
$con->sql_query("drop table if exists sku_items_cost") or die(mysql_error());

$con->sql_query("create table if not exists sku_items_cost_history ( `branch_id` int(11) NOT NULL default '0', `sku_item_id` int(11) NOT NULL default '0', `date` date default NULL, `grn_cost` double default NULL, `avg_cost` double default NULL, `source` char(10), `qty` int default 0, `ref_id` int(11) default NULL, `user_id` int(11) default NULL, PRIMARY KEY (`branch_id`, `sku_item_id`, `date`), KEY `source` (`source`,`ref_id`) )") or die(mysql_error());

$con->sql_query("create table if not exists sku_items_cost (`branch_id` int(11) NOT NULL default '0', `sku_item_id` int(11) NOT NULL default '0', `last_update` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, `date` date default NULL, `qty` int default 0, `grn_cost` double default NULL, `avg_cost` double default NULL, l90d_grn int default 0, l90d_pos int default 0, l30d_grn int default 0, l30d_pos int default 0, changed bool default 0, PRIMARY KEY (`branch_id`,`sku_item_id`), index(`date`))") or die(mysql_error());

// end setup
}

function check_cross_year($bid){
  global $con, $con;

  $con->sql_query("create table if not exists stock_balance_latest_entry(
		branch_id int not null primary key,
		year int
	)");
	$q_sb = $con->sql_query("select * from stock_balance_latest_entry where branch_id=$bid");
	$e = $con->sql_fetchassoc($q_sb);
	$con->sql_freeresult($q_sb);

  $found = false;
  $current_year = intval(date('Y'));
  if ($e) {
	  if($e['year']>=$current_year) $found = true;
  }

  if(!$found){  // run if entry not found
    print "Generating cross year stock balance...";
    $current_year = intval(date('Y'));
    $last_year = $current_year-1;

    // check discrepency
    $sb_current = 'stock_balance_b'.$bid.'_'.$current_year;
    $sb_last = 'stock_balance_b'.$bid.'_'.$last_year;
	$last_date = $last_year.'-12-31';
	
    $q = $con->sql_query("select distinct(sku_item_id) as sid from $sb_last where sku_item_id not in (select distinct(sku_item_id) from $sb_current)",false,false);
    if(!$q){
      create_sb_table($sb_current, $sb_last);
      $q = $con->sql_query("select distinct(sku_item_id) as sid from $sb_last where sku_item_id not in (select distinct(sku_item_id) from $sb_current)");
    }
	$total_sku = $con->sql_numrows($q);
	$sku_counter = 0;
    $sid_array = array();
    while($r = $con->sql_fetchassoc($q)){ // generate sku_item_id array to be copy
      $sid_array[$r['sid']] = $r['sid'];
	  
	  $sku_counter++;
	  
	  if($sku_counter >= $total_sku || $sku_counter % 1000 == 0){
		if($sid_array){ // copy item from last year table to latest table
		  //$con->sql_query("truncate $sb_current");
		  $con->sql_query("replace into $sb_current (sku_item_id, from_date, to_date, start_qty, qty, cost, avg_cost,fresh_market_cost, is_latest)(select sku_item_id, ".ms($current_year.'-1-1').", ".ms($current_year.'-12-31').", qty, qty, cost, avg_cost, fresh_market_cost, is_latest
	  from $sb_last sb
	  where sku_item_id in (".join(',',$sid_array).") and (('$last_date' between sb.from_date and sb.to_date) or ('$last_date'>=sb.from_date and sb.is_latest=1)))");
		  print "\n".$con->sql_affectedrows()." rows copied.\n";
		  $sid_array = array();
		}  
	  }
    }
	$con->sql_freeresult($q);
	print "\nTotal $sku_counter sku rows copied.\n";
    

    $con->sql_query("replace into stock_balance_latest_entry (branch_id, year) values('$bid','$current_year')");
  }
}

function sync_hq_cost($branch_id, $sku_item_id, $master_cost){
	global $selected_filter_from_date, $con, $con, $branches_array, $sb_table_array;

	if(!$branches_array){   // construct branches array, so no need to retrieve it everytime
		$q_b = $con->sql_query("select * from branch where active=1 and id>1");
		$branches_array = $con->sql_fetchrowset($q_b);
		$con->sql_freeresult($q_b);
	}
	if(!$branches_array)    return;

	$hq_cost_flow = array();
    if($selected_filter_from_date){ // if got month filter parameter
        $q_his = $con->sql_query("select * from sku_items_cost_history where branch_id=1 and sku_item_id=$sku_item_id and date<='$selected_filter_from_date' and date>0 order by date desc limit 1");
        $hq_last_entry = $con->sql_fetchassoc($q_his);
        $con->sql_freeresult($q_his);
	}
	if($hq_last_entry) $filter_date = " and date>='$hq_last_entry[date]'";

	// get HQ cost history flow
	$q_his = $con->sql_query("select * from sku_items_cost_history where branch_id=1 and sku_item_id=$sku_item_id and date>0 $filter_date order by date");
	$hq_cost_flow = $con->sql_fetchrowset($q_his);
	$con->sql_freeresult($q_his);

	$hq_last_cost = array();
	$q_sic = $con->sql_query("select * from sku_items_cost where branch_id=1 and sku_item_id=$sku_item_id");
	$hq_last_cost = $con->sql_fetchassoc($q_sic);
	$con->sql_freeresult($q_sic);

	if(!$hq_cost_flow){
		$use_master_cost = true;    // no cost history
	}

    if(!$sb_table_array){   // construct stock_balance_bX_20xx table, so no need to retrieve everytime
		$q1 = $con->sql_query("show tables");
		while($r = $con->sql_fetchrow($q1)){
			if(strpos($r[0],'stock_balance_b')===false){

			}else{
				$sb_table_array[] = $r[0];
			}
		}
		$con->sql_freeresult($q1);
	}

	$branch_to_sync = array();
	if($branch_id==1){  // HQ
	    foreach($branches_array as $b){
	        if($bid==1) continue;   // skip HQ
            $branch_to_sync[] = $b['id']; // get all branches to sync
		}
	}else{  // branches
        $branch_to_sync[] = $branch_id; // only sync own branch
	}
	if(!$hq_cost_flow)  return;
	$con->sql_query("update sku_items_cost set changed=1 where sku_item_id=$sku_item_id and branch_id in (".join(',',$branch_to_sync).")");

	$curr_year = intval(date('Y')); // get current year
}

function check_consignment_need_update_cost($hq_cost_history_cache, $d, $cost){
	if(!$hq_cost_history_cache) return true;
	$last_cost = 0;
	foreach($hq_cost_history_cache as $date=>$r){
        if(strtotime($date)>strtotime($d)) break;
        $last_cost = $r['grn_cost'];
	}
	//print "$last_cost == $cost \n";
	if($last_cost!=$cost)   return true;
	else	return false;
}

function generate_parent_fresh_market_cost_span($si){
	global $branch_id, $con, $verbose, $config, $selected_filter_from_date;

    $filter_from_date = $selected_filter_from_date;
	$sku_item_id = intval($si['id']);
	$sku_id = intval($si['sku_id']);
	$cost = doubleval($si['cost_price']);
	$master_cost = doubleval($si['cost_price']);
	$sid_list = array();
	$sku_item_list = array();
	
	if(!$sku_item_id || !$sku_id){
		print "\nError for sku item id: $sku_item_id\n";
		return;
	}

    $q_si = $con->sql_query("select si.id, si.sku_id, si.description, si.cost_price, if(sku.no_inventory='inherit', cc.no_inventory, sku.no_inventory) as no_inventory, 
	if(sku.is_fresh_market='inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market, si.is_parent, si.sku_item_code
		from sku_items si
		left join sku on sku.id=si.sku_id
		left join category_cache cc on cc.category_id=sku.category_id
		left join sku_items_cost sic on si.id=sic.sku_item_id and sic.branch_id=$branch_id
		where si.sku_id=$sku_id order by si.id");
	$parent_sid = 0;
	$parent_sku_item_code = '';
    while($r = $con->sql_fetchassoc($q_si)){
        $sid = intval($r['id']);
        $sku_item_list[$sid] = $r;
        $sid_list[] = $sid;
        
        if($r['is_parent']){
            $parent_sid = $sid;
            $parent_sku_item_code = $r['sku_item_code'];
		}
	}
	$con->sql_freeresult($q_si);
	
	if(!$parent_sid){
		print "No parent found for sku item id: $sku_item_id\n";
		return;
	}
	if(!$sid_list)  return; // no item?
    $sid_str = join(',', $sid_list);
    
    $data = array();

	if($filter_from_date){
        // if got filter date, get last stock check date
		$q_sc = $con->sql_query("select sc.date
		from stock_check sc
		where sc.sku_item_code=".ms($parent_sku_item_code)." and sc.date<".ms($filter_from_date)."
		order by sc.date desc limit 1");
		$first_sc_date = $con->sql_fetchfield(0);
		$con->sql_freeresult($q_sc);
		
		// re-assign filter date, if never have stock check, check from oldest
		$filter_from_date = $first_sc_date ? $first_sc_date : '';
	}
	
	// check whether still got item need recalculate fresh market cost
	$q_sc = $con->sql_query("select sc.date
		from stock_check sc
		where sc.branch_id=$branch_id and sc.sku_item_code=".ms($parent_sku_item_code)." ".($filter_from_date? " and sc.date>=".ms($filter_from_date):"")." and sc.is_fresh_market=1
		and fresh_market_updated=0 order by sc.date limit 1");
	$first_fm_sc_need_update = $con->sql_fetchassoc($q_sc);
	$con->sql_freeresult($q_sc);
		
	if($first_fm_sc_need_update){
		$fresh_market_cost_span = array();
		
		// get the previous stock take date
		$q_sc = $con->sql_query("select max(date) as dt from stock_check sc where sc.branch_id=$branch_id and sc.sku_item_code=".ms($parent_sku_item_code)." and sc.date<".ms($first_fm_sc_need_update['date']));
		$last_fm_sc_date = trim($con->sql_fetchfield(0));
		$con->sql_freeresult($q_sc);
		
		// get data after this stock check
		if($filter_from_date)	$filter_from_date = $last_fm_sc_date;
		
		// recalculate cost after this date
		$last_fm_cost = 0;
		$q_his = $con->sql_query("select * from sku_items_cost_history where branch_id=$branch_id and sku_item_id=$parent_sid and date<".ms($first_fm_sc_need_update['date'])." order by date");
		while($r = $con->sql_fetchassoc($q_his)){
			if($last_fm_cost != $r['fresh_market_cost']){
				$fresh_market_cost_span[$r['date']]['cost'] = $r['fresh_market_cost'];
			}
			$last_fm_cost = $r['fresh_market_cost'];
		}
		$con->sql_freeresult($q_his);
		
		// find all stock take
		$sql = "select sc.date as dt, sum(sc.selling*sc.qty) as sc_selling,sum(sc.cost*sc.qty) as sc_cost,sum(sc.qty) as sc_qty
	from stock_check sc
	where sc.branch_id=$branch_id and sc.sku_item_code=".ms($parent_sku_item_code)." ".($filter_from_date? " and sc.date>=".ms($filter_from_date):"")." and sc.is_fresh_market=1
	group by dt
	order by dt";
		$q_sc = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q_sc)){
			// get 1 qty cost
			if($r['sc_qty'])	$r['pcs_cost'] = round($r['sc_cost'] / $r['sc_qty'], 2);
			else    $r['pcs_cost'] = 0;
			$data[$r['dt']]['sc'] = $r;
		}
		$con->sql_freeresult($q_sc);
		
		// GRN
		$sql = "select grr.rcv_date as dt, (if (gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn*rcv_uom.fraction + gi.pcs, gi.acc_ctn *rcv_uom.fraction + gi.acc_pcs)) as qty,
			(
			  if (gi.acc_cost is null, gi.cost, gi.acc_cost)
			  *
			  if (gi.acc_ctn is null and gi.acc_pcs is null,
				gi.ctn + gi.pcs / rcv_uom.fraction,
				gi.acc_ctn + gi.acc_pcs / rcv_uom.fraction
			  )
			) as cost
				from grn_items gi
				left join uom rcv_uom on gi.uom_id=rcv_uom.id
				left join grn on gi.grn_id=grn.id and gi.branch_id=grn.branch_id
				left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
				where gi.branch_id=$branch_id and gi.sku_item_id in (".$sid_str.") and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1 ".($filter_from_date? " and grr.rcv_date>=".ms($filter_from_date):"")."
				order by dt";
		$q_grn = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q_grn)){
			//$data[$r['dt']]['grn']['last_cost'] += $r['cost'];
			//$data[$r['dt']]['grn']['cost'] += round($r['qty'] * $r['cost'], 2);
			$data[$r['dt']]['grn']['cost'] += $r['cost'];
		}
		$con->sql_freeresult($q_grn);
		
		// ADJ = SKU write off
		/*$sql = "select adj.adjustment_date as dt, sum(qty) as qty, sum(qty*cost) as total_cost
		from adjustment_items adji
		left join adjustment adj on adj.id=adji.adjustment_id and adj.branch_id=adji.branch_id
		where adji.sku_item_id in (".$sid_str.") and adji.branch_id=$branch_id and adj.active=1 and adj.approved=1 and adj.status=1 ".($filter_from_date? " and adj.adjustment_date>=".ms($filter_from_date):"")."
		group by dt
		order by dt";
		$q_adj = $con->sql_query($sql);
		while($r = $con->sql_fetchrow($q_adj)){
			$data[$r['dt']]['adj']['cost'] += round($r['total_cost'], 2);
		}
		$con->sql_freeresult($q_adj);*/
		
		// POS
		$tbl = "sku_items_sales_cache_b".$branch_id;
		$q_pos = $con->sql_query("select tbl.date as dt, sum(tbl.qty) as qty, sum(tbl.amount) as amt, sum(tbl.cost) as total_cost
		from $tbl tbl
		where tbl.sku_item_id in (".$sid_str.") ".($filter_from_date? " and tbl.date>=".ms($filter_from_date):"")."
		group by dt
		order by dt");
		while($r = $con->sql_fetchassoc($q_pos)){
			$data[$r['dt']]['pos']['amt'] += round($r['amt'],2);
		}
		$con->sql_freeresult($q_pos);
		
		// GRA
		$q_gra = $con->sql_query("select sum(qty) as qty, date(return_timestamp) as dt, sum(cost) as total_cost
		from gra_items gi
		left join gra on gi.gra_id = gra.id and gi.branch_id = gra.branch_id
		where gi.sku_item_id in (".$sid_str.") and gi.branch_id = $branch_id and gra.status=0 and gra.returned=1 ".($filter_from_date? " and return_timestamp>='$filter_from_date'":"")."
		group by dt
		order by dt");
		while($r = $con->sql_fetchassoc($q_gra)){
			$data[$r['dt']]['gra']['cost'] += $r['total_cost'];
		}
		$con->sql_freeresult($q_gra);
		
		// DO
		$q_do = $con->sql_query("select do.do_date as dt, sum((di.ctn *uom.fraction) + di.pcs) as qty, sum((di.ctn *uom.fraction) + di.pcs*di.cost) as total_cost
		from do_items di
		join do on do.id=di.do_id and do.branch_id=di.branch_id
		left join uom on di.uom_id=uom.id
		where di.sku_item_id in (".$sid_str.") and di.branch_id=$branch_id and do.active=1 and do.approved=1 and do.status=1 and do.checkout=1 ".($filter_from_date? " and do.do_date>='$filter_from_date'":"")." 
		group by dt
		order by dt");
		while($r = $con->sql_fetchassoc($q_do)){
			$data[$r['dt']]['do']['cost'] += $r['total_cost'];
		}
		$con->sql_freeresult($q_do);
		
		ksort($data);
		reset($data);
		//print_r($data);
		$fresh_market_cost_per_amt = 0;
		$last_sc = array();
		$corrected_fm_data = array();
		
		foreach ($data as $d => $t){
			if($t['sc']){
				if($last_sc){
					$corrected_fm_data['end_cost'] += $t['sc']['sc_cost'];
					$cogs = round($corrected_fm_data['cost'] - $corrected_fm_data['end_cost'], 2);
					$fresh_market_cost_per_amt = 0;
					// calculate cost per sales amt
					if($corrected_fm_data['sales_amt']>0){
						$fresh_market_cost_per_amt = round($cogs/ $corrected_fm_data['sales_amt'], 2);
					}
					//print "fresh_market_cost_per_amt = $cogs / $corrected_fm_data[sales_amt] = $fresh_market_cost_per_amt\n";
					// store into update array
					$fresh_market_cost_span[$last_sc['dt']]['cost'] = $fresh_market_cost_per_amt;
					$fresh_market_cost_span[$d]['cost'] = $fresh_market_cost_per_amt;
				}
				// renew last sc
				$last_sc = $t['sc'];
				
				// reset when got stock check
				//print_r($corrected_fm_data);
				$corrected_fm_data = array();
				$corrected_fm_data['cost'] += $t['sc']['sc_cost'];
			}
			
			if($t['grn']){  // got grn, increase cost
				$corrected_fm_data['cost'] += $t['grn']['cost'];
			}
			//if($t['adj']){  // gor sku write - off, add back the lose cost (use - for negative, so it will add back)
				//$corrected_fm_data['cost'] -= $t['adj']['cost'];
			//}
			if($t['gra']){
				$corrected_fm_data['cost'] -= $t['gra']['cost'];
			}
			if($t['do']){
				$corrected_fm_data['cost'] -= $t['do']['cost'];
			}
			if($t['pos']){
				$corrected_fm_data['sales_amt'] += $t['pos']['amt'];
			}
		}
	}else{
		$fresh_market_cost_span = array();
		$last_fm_cost = 0;
		$q1 = $con->sql_query("select * from sku_items_cost_history where branch_id=$branch_id and sku_item_id=$parent_sid ".($filter_from_date? " and date>=".ms($filter_from_date):"")." order by date");
		while($sich = $con->sql_fetchassoc($q1)){
			if($last_fm_cost != $sich['fresh_market_cost']){
				$fresh_market_cost_span[$sich['date']]['cost'] = $sich['fresh_market_cost'];
			}
			$last_fm_cost = $sich['fresh_market_cost'];
		}
		$con->sql_freeresult($q1);
	}
	
	//print_r($data);exit;
	//if($parent_sid == 1416){
	//if($parent_sid == 402404){	
		//print_r($fresh_market_cost_span);exit;
	//}
	
	
	$params['fresh_market_cost_span'] = $fresh_market_cost_span;
	$params['run_normal'] = 1;
	$params['skip_checking'] = 1;
	
	foreach($sku_item_list as $r){
		run_history($r ,$params);
	}
}

function get_cron_cost_history_version($bid='', $BRANCH_CODE=''){
	global $con;
	
	if(!$bid){
		if(!$BRANCH_CODE)	die('Invalid Branch Code from Cost History Version');
		$bid = get_branch_id($BRANCH_CODE);
		
		if(!$bid)	die('Invalid Branch Code from Cost History Version');
	}
	
	$q1 = $con->sql_query("select version from tmp_cron_cost_history_info where branch_id=".mi($bid));
	$version = mi($con->sql_fetchfield(0));
	$con->sql_freeresult($q1);
	
	return $version;
}

function update_cron_cost_history_version($bid, $version){
	global $con;
	
	$upd = array();
	$upd['branch_id'] = $bid;
	$upd['version'] = $version;
	
	$con->sql_query("replace into tmp_cron_cost_history_info ".mysql_insert_by_field($upd));
}

function check_cron_cost_history_version($BRANCH_CODE){
	global $con;
	
	$bid = mi(get_branch_id($BRANCH_CODE));
	if(!$bid)	die('Invalid Branch Code from Cost History Version');
	$version = get_cron_cost_history_version($bid);
		
	if($version < 1){
		$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (
			select distinct(gi.sku_item_id) 
			from grn_items gi
			left join grn on grn.branch_id=gi.branch_id and grn.id=gi.grn_id
			where grn.branch_id=$bid and grn.active=1 and grn.status=1 and grn.approved=1)");
		update_cron_cost_history_version($bid, 1);
	}
	
	if($version < 2){
		print "Updating cron version from 1 to 2…\n";
		
		print "Updating Branch ID#$bid\n";
		$vsh_tbl = 'vendor_sku_history_b'.$bid;
		
		$q_si = $con->sql_query("select si.id,ifnull(vsh.vendor_id,sku.vendor_id) as last_vendor_id
from sku_items si
left join sku on sku.id=si.sku_id
left join $vsh_tbl vsh on vsh.sku_item_id=si.id and vsh.to_date='9999-12-31'
where si.id not in (select distinct vsh2.sku_item_id from $vsh_tbl vsh2 where vsh2.from_date='0000-00-00' and vsh2.to_date='0000-00-00')
order by si.id");
		$total_count = $con->sql_numrows($q_si);
		$curr_count = 0;
		while($si = $con->sql_fetchassoc($q_si)){
			$upd = array();
			$upd['sku_item_id'] = $si['id'];
			$upd['from_date'] = $upd['to_date'] = '0000-00-00';
			$upd['vendor_id'] = $si['last_vendor_id'];
			$con->sql_query("replace into $vsh_tbl ".mysql_insert_by_field($upd));
			
			$curr_count ++;
			print "\r$curr_count / $total_count ……";
		}
		
		$con->sql_freeresult($q_si);
		update_cron_cost_history_version($bid, 2);
		print "done version 2.\n";
	}
	
	if($version < 3){	// fix grn negative qty
		print "Updating cron version from 3 to 3… Branch ID#$bid\n";
		
		$q1 = $con->sql_query("select distinct sku_item_id as sid from grn_items where branch_id=$bid and (ctn<0 or pcs<0 or acc_ctn<0 or acc_pcs<0)");
		$sid_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$sid_list[] = mi($r['sid']);
			
			if(count($sid_list)>1000){
				$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (".join(',', $sid_list).")");
				$sid_list = array();
			}
		}
		$con->sql_freeresult($q1);
		
		if($sid_list){
			$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (".join(',', $sid_list).")");
			$sid_list = array();
		}
		
		update_cron_cost_history_version($bid, 3);
		print "done version 3.\n";
	}
}

function run_history_by_sku($sku){
	global $branch_id, $con, $verbose, $config, $selected_filter_from_date;
    $filter_from_date = $selected_filter_from_date;

	$sku_use_avg_cost_as_last_cost = mi($config['sku_use_avg_cost_as_last_cost']);
	$sku_update_cost_by_parent_child = mi($config['sku_update_cost_by_parent_child']);
	
	$sku_id = mi($sku['sku_id']);
	$master_vendor_id = mi($sku['master_vendor_id']);
	
	if(!$sku_id)	return false;	// no sku id
	$si_info_list = array();
	$grn_data = array();
	$stock_take_data = array();
	$work_order_data = array();
	
	// table name	
	$vendor_sku_history_tbl = 'vendor_sku_history_b'.$branch_id;
	
	$tmp_cron_txt = 'tmp_cron.txt';
	//file_put_contents($tmp_cron_txt, '');
	
	// set changed = 2 (in progress)
	$con->sql_query("update sku_items_cost sic 
join sku_items si on si.id=sic.sku_item_id
set sic.changed=2
where sic.branch_id=$branch_id and si.sku_id=$sku_id");

	$q_si = $con->sql_query("select si.id,si.packing_uom_id, uom.fraction as packing_uom_fraction, si.cost_price, si.selling_price
	from sku_items si
	left join uom on uom.id=si.packing_uom_id
	where sku_id=$sku_id
	order by si.is_parent, si.id");
	while($r = $con->sql_fetchassoc($q_si)){
		$si_info_list[$r['id']]['info'] = $r;
		
		$si_info_list[$r['id']]['cost'] = $si_info_list[$r['id']]['avg_cost'] = $r['cost_price'];
		
		//file_put_contents($tmp_cron_txt, "SID: $r[id], master cost: $r[cost_price], Fraction: $r[packing_uom_fraction]\n", FILE_APPEND);
	}
	$con->sql_freeresult($q_si);
	$str_sid_list = trim(join(',', array_keys($si_info_list)));
	if(!$str_sid_list)	return false;	// no sku item id ?
	
	// got filter to start calculate from date
	if($filter_from_date){
		$day_b4_filter = date("Y-m-d", strtotime("-1 day", strtotime($filter_from_date)));
		$sb_tbl = "stock_balance_b".$branch_id."_".date("Y", strtotime($day_b4_filter));
		create_sb_table($sb_tbl);
		
		foreach($si_info_list as $sku_item_id => $si_info){
			// get the item qty & cost at 1 day before filter date
			$q_c = $con->sql_query("select qty, cost, avg_cost from $sb_tbl where sku_item_id=$sku_item_id and ".ms($day_b4_filter)." between from_date and to_date limit 1");
			$tmp_sb = $con->sql_fetchassoc($q_c);
			$con->sql_freeresult($q_c);
			
			if($tmp_sb){
				$si_info_list[$sku_item_id]['cost'] = $tmp_sb['cost'];
				$si_info_list[$sku_item_id]['avg_cost'] = $tmp_sb['avg_cost'];
				$si_info_list[$sku_item_id]['qty'] = $tmp_sb['qty'];
			}
			unset($tmp_sb);
			
			// get vendor sku history
			$q_vs = $con->sql_query("select * from $vendor_sku_history_tbl where sku_item_id=$sku_item_id and ".ms($day_b4_filter)." between from_date and to_date limit 1");
			while($tmp_data = $con->sql_fetchassoc($q_vs)){
				$tmp_data['to_date'] = '';
				$si_info_list[$sku_item_id]['vendor_data'][$tmp_data['vendor_id']] = $tmp_data;
			}
			$con->sql_freeresult($q_vs);
			unset($tmp_data);
			
			// no vendor data and use grn last vendor can include master
			if(!$si_info_list['vendor_data'] && $config['use_grn_last_vendor_include_master'] && $master_vendor_id){
				$tmp_data = array();
				$tmp_data['sku_item_id'] = $sku_item_id;
				$tmp_data['vendor_id'] = $master_vendor_id;
				$si_info_list[$sku_item_id]['vendor_data'][$master_vendor_id] = $tmp_data;
				unset($tmp_data);
			}
		}
	}
	
	$data = array();
	
	// stock check
	$q_sc = $con->sql_query("select si.id as sid, sc.qty, sc.cost, sc.date 
	from stock_check sc 
	join sku_items si using (sku_item_code) 
	where si.id in ($str_sid_list) and branch_id = $branch_id ".($filter_from_date? " and date>='$filter_from_date'":"")." order by date");
	while($r=$con->sql_fetchassoc($q_sc)){
		$data[$r['date']][$r['sid']]['stock_check'] += $r['qty'];
		//$data[$r['date']][$r['sid']]['stock_check_cost'] = $r['cost'];
		
		$c = trim(round($r['cost'], $config['global_cost_decimal_points']));
		if($c > 0){
			$stock_take_data[$r['date']]['item_list'][$r['sid']]['got_cost'][$c]['qty'] += $r['qty'];
		}else{
			$stock_take_data[$r['date']]['item_list'][$r['sid']]['no_cost']['qty'] += $r['qty'];
		}
	}
	$con->sql_freeresult($q_sc);
		
	// POS
	$q_sc = $con->sql_query($sql = "select sku_item_id as sid, qty, date as dt 
	from sku_items_sales_cache_b$branch_id 
	where sku_item_id in ($str_sid_list) ".($filter_from_date? " and date>='$filter_from_date'":""));
	while($r=$con->sql_fetchassoc($q_sc)){
		$data[$r['dt']][$r['sid']]['pos'] = $r['qty'];
	}
	$con->sql_freeresult($q_sc);

	//if($verbose)	print $sql."\n";
	
	// GRA
	$q_gi = $con->sql_query("select gra_items.sku_item_id as sid, sum(qty) as qty, date(return_timestamp) as dt 
	from gra_items 
	join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
	where gra_items.sku_item_id in ($str_sid_list) and gra_items.branch_id = $branch_id and gra.status=0 and gra.returned=1 ".($filter_from_date? " and return_timestamp>='$filter_from_date'":"")." group by sid, dt");
	while($r=$con->sql_fetchassoc($q_gi))
	{
		$data[$r['dt']][$r['sid']]['gra'] = $r['qty'];
	}
	$con->sql_freeresult($q_gi);
	
	//FROM DO
	$q_do = $con->sql_query("select do_items.sku_item_id as sid, sum(do_items.ctn *uom.fraction + do_items.pcs) as qty, do.do_date as dt
from do_items
left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
left join uom on do_items.uom_id=uom.id
where do_items.sku_item_id in ($str_sid_list) and do_items.branch_id = $branch_id and do.approved=1 and do.checkout=1 and do.status=1 and do.active=1 ".($filter_from_date? " and do_date>='$filter_from_date'":"")." group by sid,dt");
	while($r=$con->sql_fetchassoc($q_do)){
		$data[$r['dt']][$r['sid']]['do'] = $r['qty'];
	}
	$con->sql_freeresult($q_do);
		
	//FROM ADJUSTMENT
	/*$q_adj = $con->sql_query("select adjustment_items.sku_item_id as sid, sum(qty) as qty, adjustment_date as dt
from adjustment_items
left join adjustment on adjustment.id=adjustment_items.adjustment_id and adjustment.branch_id=adjustment_items.branch_id
where adjustment_items.sku_item_id in ($str_sid_list) and adjustment_items.branch_id = $branch_id and adjustment.approved=1 and adjustment.status=1 and adjustment.active=1 ".($filter_from_date? " and adjustment_date>='$filter_from_date'":"")." group by sid, dt");*/
	$q_adj = $con->sql_query("select adji.adjustment_id, adji.sku_item_id as sid, adj.adjustment_date as dt, adj.module_type, sum(if(adji.qty>0,adji.qty,0)) as positive_qty, sum(if(adji.qty<0,adji.qty,0)) as negative_qty, wo.id as wo_id, woi.finish_cost, woi.line_total_finish_cost, wo.wo_no
	from adjustment_items adji
	left join adjustment adj on adj.id=adji.adjustment_id and adj.branch_id=adji.branch_id
	left join work_order wo on wo.branch_id=adj.branch_id and wo.adj_id=adj.id and adj.module_type='work_order' and wo.active=1 and wo.status=1 and wo.completed=1
	left join work_order_items_in woi on woi.branch_id=wo.branch_id and woi.work_order_id=wo.id and woi.sku_item_id=adji.sku_item_id
	where adji.sku_item_id in ($str_sid_list) and adji.branch_id = $branch_id and adj.approved=1 and adj.status=1 and adj.active=1 ".($filter_from_date? " and adj.adjustment_date>='$filter_from_date'":"")."
	group by adjustment_id, sid, dt, module_type
	order by dt, adjustment_id");
	while($r=$con->sql_fetchassoc($q_adj)){
		if($r['module_type'] == 'work_order' && $r['wo_id']){
			if($r['negative_qty']<0){	// negative is transfer out, not affect cost
				$data[$r['dt']][$r['sid']]['adj'] += $r['negative_qty'];
			}
			if($r['positive_qty']>0){	// positive is transfer in, will affect cost
				$tmp = array();
				$tmp['qty'] = $r['positive_qty'];
				$tmp['cost'] = $r['finish_cost'];
				$tmp['total_cost'] = $r['line_total_finish_cost'];
				//$tmp['link'] = sprintf("<a target=_blank href=\"/work_order.php?a=view&highlight_in_sid=$r[sid]&branch_id=$branch_id&id=%d\">%s</a>",trim($r['wo_id']),trim($r['wo_no']));
				$data[$r['dt']][$r['sid']]['wo_list'][] = $tmp;
				
				// store by grn data
				$wo_key = $branch_id."_".$r['wo_id'];
				$work_order_data[$r['dt']][$wo_key]['item_list'][$r['sid']]['qty'] += $tmp['qty'];
				$work_order_data[$r['dt']][$wo_key]['item_list'][$r['sid']]['cost'] += $tmp['cost'];
				$work_order_data[$r['dt']][$wo_key]['item_list'][$r['sid']]['total_cost'] += $tmp['total_cost'];
				//$work_order_data[$r['dt']][$wo_key]['link'] = $tmp['link'];	
			}	
		}else{
			// normal adjustment
			$data[$r['dt']][$r['sid']]['adj'] += $r['positive_qty']+$r['negative_qty'];
		}
	}
	$con->sql_freeresult($q_adj);
		
	// grn
	$sql = "select grn_items.sku_item_id as sid, (if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
	(
	  if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost)
	  *
	  if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
	  	grn_items.ctn + grn_items.pcs / rcv_uom.fraction,
	  	grn_items.acc_ctn + grn_items.acc_pcs / rcv_uom.fraction
	  )
	) as cost, grr.rcv_date as dt, grn.grr_id, grn.is_future,grn_items.grn_id,
	gi.type, do.do_type, do.branch_id as do_from_branch_id,grr.vendor_id, grr.currency_code, if(grr.currency_rate<0,1,grr.currency_rate) as currency_rate, grr.tax_percent as grr_tax_percent
	from grn_items
	left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
	left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
	left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
	left join grr_items gi on gi.id=grn.grr_item_id and gi.branch_id=grn.branch_id
	left join do on do.do_no=gi.doc_no and gi.type='DO'
	where grn_items.sku_item_id in ($str_sid_list) and grn_items.branch_id = $branch_id and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1".($filter_from_date? " and grr.rcv_date>='$filter_from_date'":"")." order by grr.rcv_date, grr.id";

	$sql1 = $con->sql_query($sql);

	while($r=$con->sql_fetchassoc($sql1)){
		//if($r['qty']<=0)	continue;
		
		//$data[$r['dt']]['grn'] += $r['qty'];
	    $count_this_grn = false;
	    
		if($r['qty'] > 0){
			if($r['is_future']){
				$sql2 = $con->sql_query("select type, 
									 case when type = 'PO' then 1 when type = 'INVOICE' then 2 when type = 'DO' then 3 else 4 end as type_asc, do.do_type, do.branch_id as do_from_branch_id
									 from grr_items gi
									 left join grr on grr.id = gi.grr_id and grr.branch_id = gi.branch_id
									 left join do on do.do_no=gi.doc_no and gi.type='DO'
									 where gi.grr_id = $r[grr_id] and gi.branch_id = $branch_id
									 group by type_asc
									 order by type_asc asc
									 limit 1");
	
				$gi_info = $con->sql_fetchassoc($sql2);
				$con->sql_freeresult($sql2);
				$r['type'] = $gi_info['type'];
				$r['do_type'] = $gi_info['do_type'];
				$r['do_from_branch_id'] = $gi_info['do_from_branch_id'];
			}
	
		    
	
		    if($r['type']!='DO'){
	            $count_this_grn = true;
			}else{  // document type = DO
			    if(!$r['do_type']||!$r['do_from_branch_id'])  $count_this_grn = true; // DO from outside
			    else{   // inter transfer DO
	                if($config['grn_do_hq2branch_update_cost']&&$branch_id>1&&$r['do_from_branch_id']==1)   $count_this_grn = true;
	                if($config['grn_do_branch2branch_update_cost']&&$branch_id>1&&$r['do_from_branch_id']>1)   $count_this_grn = true;
	                if($config['grn_do_branch2hq_update_cost']&&$branch_id==1&&$r['do_from_branch_id']>1)   $count_this_grn = true;
				}
			}
		}
		
	    /*if(($config['grn_do_transfer_update_cost']&&$r['type']=='DO'&&$r['do_type']=='transfer')||($config['grn_do_all_update_cost']&&$r['type']=='DO')){
	      $count_this_grn = true;
		}else{
		    if(($config['grn_do_branch_update_cost']&&$r['type']=='DO'&&$branch_id>1)||($r['type']!='DO')){
	        	$count_this_grn = true;
	      	}
		}*/

		$tmp = array();
		$tmp['cost'] = $r['cost'] * $r['currency_rate'];	// this cost alrdy multiply qty
		$tmp['tax'] = $tmp['cost'] * $r['grr_tax_percent'] / 100;	// calculate tax
		$tmp['cost_before_tax'] = $tmp['cost'];	// capture the cost before tax
		$tmp['cost'] += $tmp['tax'];	// add tax into cost
		$tmp['qty'] = $r['qty'];
		$tmp['count_this_grn'] = $count_this_grn;
		
		$data[$r['dt']][$r['sid']]['grn_list'][] = $tmp;	// grn hv to save 1 by 1
		
	    if($count_this_grn){     	
	      	if($r['vendor_id']){
				// only last vendor for same date
				$data[$r['dt']][$r['sid']]['vendor_id_list'] = array($r['vendor_id']);
			}
	    }
		
		// store by grn data
		$grn_key = $branch_id."_".$r['grn_id'];
		$grn_data[$r['dt']][$grn_key]['item_list'][$r['sid']]['qty'] += $tmp['qty'];
		$grn_data[$r['dt']][$grn_key]['item_list'][$r['sid']]['total_cost'] += $tmp['cost'];
		$grn_data[$r['dt']][$grn_key]['item_list'][$r['sid']]['total_tax'] += $tmp['tax'];
		$grn_data[$r['dt']][$grn_key]['item_list'][$r['sid']]['total_cost_before_tax'] += $tmp['cost_before_tax'];
		$grn_data[$r['dt']][$grn_key]['count_this_grn'] = $count_this_grn;
	}
	$con->sql_freeresult($sql1);
		
	////////// START CALCULATION ////////////
	
	ksort($data);
	reset($data);
	
	//print_r($data);
	
	$last3mth = date('Y-m-d', strtotime("-3 month"));
	$last1mth = date('Y-m-d', strtotime("-1 month"));
	
	foreach($si_info_list as $sku_item_id => $si_info){
		if($filter_from_date){
	        // get stock balance table row
		    $filter_sb_year = date('Y',strtotime($filter_from_date));
		    $q_sb = $con->sql_query("select * from stock_balance_b".$branch_id."_".$filter_sb_year." where sku_item_id=$sku_item_id and '$filter_from_date' between from_date and to_date limit 1",false,false);
		    $temp = $con->sql_fetchassoc($q_sb);
			$con->sql_freeresult($q_sb);
		    if($temp){
		    	$sb = array();
		      	$sb['from_date'] = $temp['from_date'];
		  		$sb['to_date'] = $temp['from_date'];
	            $sb['start_qty'] = floatval($temp['start_qty']);
		  		$sb['qty'] = floatval($temp['qty']);
		  		$sb['cost'] = floatval($temp['cost']);
		  		$sb['avg_cost'] = floatval($temp['avg_cost']);
		  		$sb['fresh_market_cost'] = floatval($temp['fresh_market_cost']);
		  		$sb['sku_item_id'] = intval($temp['sku_item_id']);
		  		
		  		$si_info_list[$sku_item_id]['sb'] = $sb;
		  		$si_info_list[$sku_item_id]['last_qty'] = $sb['qty'];
		  		
		  		unset($sb);
		    }
	    }
	    $si_info_list[$sku_item_id]['l90d_grn'] = 0;
		$si_info_list[$sku_item_id]['l90d_pos'] = 0;
		$si_info_list[$sku_item_id]['l30d_grn'] = 0;
		$si_info_list[$sku_item_id]['l30d_pos'] = 0;
		$sb_year = 0;
		
		// an array to store stock balance list
		$si_info_list[$sku_item_id]['sb_list'] = array();
	}
	//file_put_contents("tmp_cron.txt", "");
	//file_put_contents($tmp_cron_txt, print_r($si_info_list, true), FILE_APPEND);
	// get total_pcs, total_cost and avg_pcs_cost
	$parent_child_total = get_and_update_parent_child_total($si_info_list);
	//file_put_contents("tmp_cron.txt", print_r($parent_child_total, true));
	//file_put_contents("tmp_cron.txt", print_r($si_info_list, true), FILE_APPEND);
	//file_put_contents($tmp_cron_txt, print_r($parent_child_total, true), FILE_APPEND);
	
	$avg_pcs_cost = $parent_child_total['avg_pcs_cost'];
	if($avg_pcs_cost>0){
		foreach($si_info_list as $sku_item_id => $si_info){
			$si_info_list[$sku_item_id]['avg_cost'] = round($avg_pcs_cost * $si_info['info']['packing_uom_fraction'], $config['global_cost_decimal_points']);
			if($sku_use_avg_cost_as_last_cost){
				$si_info_list[$sku_item_id]['cost'] = $si_info_list[$sku_item_id]['avg_cost'];
			}
			
			
			// renew cost to latest avg cost for the first day
			if($sb && ($si_info_list[$sku_item_id]['sb']['cost']!=$si_info_list[$sku_item_id]['cost'] || $si_info_list[$sku_item_id]['sb']['avg_cost']!=$si_info_list[$sku_item_id]['avg_cost'])){
				$si_info_list[$sku_item_id]['sb']['cost'] = $si_info_list[$sku_item_id]['cost'];
				$si_info_list[$sku_item_id]['sb']['avg_cost'] = $si_info_list[$sku_item_id]['avg_cost'];   
			}
		}
	}
	
	//file_put_contents("tmp_cron.txt", print_r($si_info_list, true), FILE_APPEND);
	
	// delete cost history
	$con->sql_query("delete from sku_items_cost_history where branch_id=$branch_id and sku_item_id in ($str_sid_list) ".($filter_from_date? " and (date>='$filter_from_date' or date=0)":""));
	
	$con->sql_query("delete from $vendor_sku_history_tbl where sku_item_id in ($str_sid_list) ".($day_b4_filter? " and (to_date>=".ms($day_b4_filter).")":""));
	
	$display_data = array();
	$filter_cutoff_done = false;
	foreach ($data as $d => $daily_sid_list){	// loop for each transaction date
	    $d2 = date('Y-m-d',strtotime('-1 day',strtotime($d)));
		$got_cost_changed = false;
		$got_qty_changed = false;
		
		foreach($si_info_list as $sid => $si_info){
			if(isset($daily_sid_list[$sid])){
				$t = $daily_sid_list[$sid];
			//foreach($daily_sid_list as $sid => $t){	// loop for each sku in this date
				if(!$si_info_list[$sid]['sb']){	// first time got data
					$sb = array();
			      	$sb['from_date'] = $sb['to_date'] = date('Y-m-d',strtotime('-1 day',strtotime($d)));
					if($filter_from_date && strtotime($sb['from_date'])<strtotime($filter_from_date)){
						$sb['from_date'] = $sb['to_date'] = $filter_from_date;
					}
		            $sb['start_qty'] = 0;
			  		$sb['qty'] = 0;
			  		$sb['cost'] = $si_info_list[$sid]['cost'];
			  		$sb['avg_cost'] = $si_info_list[$sid]['avg_cost'];
			  		$sb['sku_item_id'] = $sid;
			  		
			  		$si_info_list[$sid]['sb'] = $sb;
				}
						
				if ($d >= $last3mth){	// last 3 month
					if($t['grn_list']){
						foreach($t['grn_list'] as $grn){
							$si_info_list[$sid]['l90d_grn'] += $grn['qty'];
						}
					}
					
					$si_info_list[$sid]['l90d_pos'] += $t['pos'];
				}
				
				if ($d >= $last1mth){	// last 1 month
					if($t['grn_list']){
						foreach($t['grn_list'] as $grn){
							$si_info_list[$sid]['l30d_grn'] += $grn['qty'];
						}
					}
	
					$si_info_list[$sid]['l30d_pos'] += $t['pos'];
				}
				
				// got stock check for this item
				/*if (isset($t['stock_check'])){
					// stock check with zero cost, replace it with latest cost
					if ($t['stock_check_cost']==0) $t['stock_check_cost'] = $si_info_list[$sid]['cost'] * $t['stock_check'];				
					
					// update stock check qty into qty
					$si_info_list[$sid]['qty'] = $t['stock_check'];
					
					//file_put_contents($tmp_cron_txt, "Date: $d, SID: $sid, Stock Check Qty: $t[stock_check], Stock Check Total Cost: $t[stock_check_cost]\n", FILE_APPEND);
					
					// get each qty cost
					if($t['stock_check']){
						$si_info_list[$sid]['avg_cost'] = $si_info_list[$sid]['cost'] = $t['stock_check_cost']/$t['stock_check'];
					}				
					
					$params = array();
					if($sku_use_avg_cost_as_last_cost)	$params['update_avg_pcs_cost'] = 1;
					else	$params['update_use_item_grn_cost'] = array('sid'=>$sid, 'cost' => $si_info_list[$sid]['cost']);
					
					// update all child cost 
					get_and_update_parent_child_total($si_info_list, $params);
				}*/
			}
		}
		
		// New Stock Take Style
		if(isset($stock_take_data[$d])){	// this date got stock take
			//file_put_contents($tmp_cron_txt, "$d: Before Stock Take\n".print_r($si_info_list, true), FILE_APPEND);
			calculate_group_sku_stock_take($d, $si_info_list, $stock_take_data[$d], $display_data);
			//file_put_contents($tmp_cron_txt, "$d: After Stock Take\n".print_r($si_info_list, true), FILE_APPEND);
		}
		
		/*foreach($si_info_list as $sid => $si_info){
			if(isset($daily_sid_list[$sid])){
				$t = $daily_sid_list[$sid];
			//foreach($daily_sid_list as $sid => $t){	// loop for each sku in this date
				// got grn for this item
				if ($t['grn_list'])
				{
					foreach($t['grn_list'] as $grn){
						// item total cost before include grn
						$item_total_cost = round($si_info_list[$sid]['qty'] * $si_info_list[$sid]['avg_cost'], 5);
	
						$qty_b4_grn = $si_info_list[$sid]['qty'];
						
						//file_put_contents($tmp_cron_txt, "Date: $d, SID: $sid, Current Qty: ".$si_info_list[$sid]['qty'].", GRN Qty: $grn[qty], GRN Total Cost: ".$grn['cost']."\n", FILE_APPEND);
						
						if($grn['count_this_grn']){	
							$params = array();
							$params['grn_item']['sid'] = $sid;
							$params['grn'] = $grn;
							calculate_group_sku_grn_avg_cost($si_info_list, $params);
						}
												
						// increase qty
						$si_info_list[$sid]['qty'] += $grn['qty'];
						
						// this grn need update cost
						if($grn['count_this_grn']){
							// new additional cost to be add
								
							$params = array();
							if($sku_use_avg_cost_as_last_cost){
								$params['update_avg_pcs_cost'] = 1;							
							}	
							else{
								if($grn['qty']){
									$grn_cost = $grn['cost'] / $grn['qty'];
									if($grn_cost)	$si_info_list[$sid]['cost'] = $grn_cost;
								}
								
								$params['update_use_item_grn_cost'] = array('sid'=>$sid, 'cost' => $si_info_list[$sid]['cost']);
							}	
							
							// update all child cost 
							get_and_update_parent_child_total($si_info_list, $params);
						}
					}				
				}
			}
		}*/
		
		// new GRN style
		if(isset($grn_data[$d])){	// this date got grn
			calculate_group_sku_grn($d, $si_info_list, $grn_data[$d], $display_data);
			//file_put_contents($tmp_cron_txt, "$d: After GRN\n".print_r($si_info_list, true), FILE_APPEND);
		}
		
		// Work Order
		if(isset($work_order_data[$d])){	// this date got work order
			calculate_group_sku_work_order($d, $si_info_list, $work_order_data[$d], $display_data);
			$got_cost_changed = true;
		}
		
		foreach($daily_sid_list as $sid => $t){	// loop for each sku in this date
			if ($t['gra']) {
				//file_put_contents($tmp_cron_txt, "Date: $d, SID: $sid, Current Qty: ".$si_info_list[$sid]['qty'].", GRA Qty: $t[gra]\n", FILE_APPEND);
			
				$si_info_list[$sid]['qty'] -= $t['gra'];

				//file_put_contents($tmp_cron_txt, "Date: $d, SID: $sid, New Qty: ".$si_info_list[$sid]['qty']."\n", FILE_APPEND);
			}
			if ($t['pos']) {
				//file_put_contents($tmp_cron_txt, "Date: $d, SID: $sid, Current Qty: ".$si_info_list[$sid]['qty'].", POS Qty: $t[pos]\n", FILE_APPEND);
				
				$si_info_list[$sid]['qty'] -= $t['pos'];
				
				//file_put_contents($tmp_cron_txt, "Date: $d, SID: $sid, New Qty: ".$si_info_list[$sid]['qty']."\n", FILE_APPEND);
			}
			if ($t['do']) {
				//file_put_contents($tmp_cron_txt, "Date: $d, SID: $sid, Current Qty: ".$si_info_list[$sid]['qty'].", DO Qty: $t[do]\n", FILE_APPEND);
				
				$si_info_list[$sid]['qty'] -= $t['do'];
				
				//file_put_contents($tmp_cron_txt, "Date: $d, SID: $sid, New Qty: ".$si_info_list[$sid]['qty']."\n", FILE_APPEND);
			}
			if ($t['adj']) {
				//file_put_contents($tmp_cron_txt, "Date: $d, SID: $sid, Current Qty: ".$si_info_list[$sid]['qty'].", ADJ Qty: $t[adj]\n", FILE_APPEND);
				
				$si_info_list[$sid]['qty'] += $t['adj'];
				
				//file_put_contents($tmp_cron_txt, "Date: $d, SID: $sid, New Qty: ".$si_info_list[$sid]['qty']."\n", FILE_APPEND);
			}
			/*if ($t['cn']) {
				$si_info_list[$sid]['qty'] += $t['cn'];
			}
			if ($t['dn']) {
				$si_info_list[$sid]['qty'] -= $t['dn'];
			}*/
			
			if($t['vendor_id_list']){
				// loop for last changed vendor
				if($si_info_list[$sid]['vendor_data']){
					foreach($si_info_list[$sid]['vendor_data'] as $tmp_vendor_id => $vd){
						$got_changed = true;
						
						// check new vendor list
						foreach($t['vendor_id_list'] as $new_vendor_id){
							if($tmp_vendor_id == $new_vendor_id){	//this vendor need maintain at this date
								$got_changed = false;
								break;
							}
						}
						
						if($got_changed){
							$vd['to_date'] = $d2;
							$con->sql_query("insert into $vendor_sku_history_tbl ".mysql_insert_by_field($vd));
							unset($si_info_list[$sid]['vendor_data'][$tmp_vendor_id]);
						}
					}
				}				
				
				foreach($t['vendor_id_list'] as $new_vendor_id){
					if(!$si_info_list[$sid]['vendor_data'][$new_vendor_id]){
						$tmp_data = array();
						$tmp_data['sku_item_id'] = $sid;
						$tmp_data['from_date'] = $d;
						$tmp_data['vendor_id'] = $new_vendor_id;
						$si_info_list[$sid]['vendor_data'][$new_vendor_id] = $tmp_data;
					}
				}
			}
			
			// stock balance
			if($si_info_list[$sid]['sb'] && ($si_info_list[$sid]['sb']['qty']!=$si_info_list[$sid]['qty'] || $si_info_list[$sid]['sb']['cost']!=$si_info_list[$sid]['cost'])){
				$got_qty_changed = true;
			}
	
			if (isset($t['grn_list']) || isset($t['stock_check'])){
				$got_cost_changed = true;
			}
			$last_date = $d;
		}
		
		if($filter_from_date && !$filter_cutoff_done){
			foreach($si_info_list as $sid => $si_info){
				//if($sid == 468102){
					if($si_info_list[$sid]['sb'] && strtotime($si_info_list[$sid]['sb']['from_date']) < strtotime($filter_from_date) && strtotime($d)>strtotime($filter_from_date)){
						$old_sb = $si_info_list[$sid]['sb'];
						
						$si_info_list[$sid]['last_qty'] = $si_info_list[$sid]['sb']['qty'];
						$si_info_list[$sid]['sb']['to_date'] = date('Y-m-d',strtotime('-1 day',strtotime($filter_from_date)));
												
						$si_info_list[$sid]['sb_list'][] = $si_info_list[$sid]['sb'];
						$si_info_list[$sid]['sb'] = array();
											
						$sb = array();
						$sb['from_date'] = $filter_from_date;
						$sb['to_date'] = $filter_from_date;
						$sb['start_qty'] = $si_info_list[$sid]['last_qty'];
						$sb['qty'] = $old_sb['qty'];
						$sb['cost'] = $old_sb['cost'];
						$sb['avg_cost'] = $old_sb['avg_cost'];
						$sb['sku_item_id'] = $sid;
						
						$si_info_list[$sid]['sb'] = $sb;
						unset($sb);
						$filter_cutoff_done = true;
					}
				//}
			}
		}
		if($got_cost_changed || $got_qty_changed){
			foreach($si_info_list as $sid => $si_info){
				// check qty changed
				if($si_info_list[$sid]['sb'] && ($si_info_list[$sid]['sb']['qty']!=$si_info_list[$sid]['qty'] || $si_info_list[$sid]['sb']['cost']!=$si_info_list[$sid]['cost'])){
					if(strtotime($si_info_list[$sid]['sb']['from_date']) == strtotime($d)){
						$si_info_list[$sid]['sb']['to_date'] = $d;
						$si_info_list[$sid]['sb']['qty'] = floatval($si_info_list[$sid]['qty']);
					}else{
						$si_info_list[$sid]['last_qty'] = $si_info_list[$sid]['sb']['qty'];
						$si_info_list[$sid]['sb']['to_date'] = date('Y-m-d',strtotime('-1 day',strtotime($d)));
												
						$si_info_list[$sid]['sb_list'][] = $si_info_list[$sid]['sb'];
						$si_info_list[$sid]['sb'] = array();
					}
				}
				
				if(!$si_info_list[$sid]['sb']){
					$sb = array();
					$sb['from_date'] = $d;
					$sb['to_date'] = $d;
					$sb['start_qty'] = $si_info_list[$sid]['last_qty'];
					$sb['qty'] = $si_info_list[$sid]['qty'];
					$sb['cost'] = $si_info_list[$sid]['cost'];
					$sb['avg_cost'] = $si_info_list[$sid]['avg_cost'];
					$sb['sku_item_id'] = $sid;
					
					$si_info_list[$sid]['sb'] = $sb;
					unset($sb);
				}
			
				if($got_cost_changed){
					$temp = array();
					$temp['branch_id'] = $branch_id;
					$temp['sku_item_id'] = $sid;
					$temp['grn_cost'] = $si_info_list[$sid]['cost'];		
					$temp['avg_cost'] = $si_info_list[$sid]['avg_cost'];
					$temp['fresh_market_cost'] = $si_info_list[$sid]['cost'];
					$temp['qty'] = $si_info_list[$sid]['qty'];
					$temp['date'] = $d;
					
					if($sku_use_avg_cost_as_last_cost)	$temp['grn_cost'] = $temp['avg_cost'];	// use avg cost as last cost
					
		            $con->sql_query("insert into sku_items_cost_history ".mysql_insert_by_field($temp));
		            unset($temp);
				}
			}
		}
	}

	//file_put_contents("tmp_cron.txt", '');
	//file_put_contents("tmp_cron.txt", print_r($data, true), FILE_APPEND);
	//file_put_contents("tmp_cron.txt", print_r($si_info_list, true), FILE_APPEND);
	
	foreach($si_info_list as $sku_item_id => $si_info){
		/*
		if($last_date){
					$temp = array();
					$temp['branch_id'] = $branch_id;
					$temp['sku_item_id'] = $sku_item_id;
					$temp['grn_cost'] = $si_info_list[$sku_item_id]['cost'];    	
					$temp['avg_cost'] = $si_info_list[$sku_item_id]['cost'];
					$temp['fresh_market_cost'] = $si_info_list[$sku_item_id]['cost'];
					$temp['qty'] = $si_info_list[$sku_item_id]['qty'];
					$temp['date'] = $last_date;
					$con->sql_query("replace into sku_items_cost_history ".mysql_insert_by_field($temp));
					unset($temp);
				}
		*/
		if($filter_from_date && !$last_date){	// no last date when got filter -m
			$con->sql_query("select date from sku_items_cost where branch_id=$branch_id and sku_item_id=$sku_item_id");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$last_date = $tmp['date'];
		}
	
		$temp = array();
		$temp['branch_id'] = $branch_id;
		$temp['sku_item_id'] = $sku_item_id;
		$temp['grn_cost'] = $si_info_list[$sku_item_id]['cost'];	
		$temp['avg_cost'] = $si_info_list[$sku_item_id]['avg_cost'];
		$temp['fresh_market_cost'] = $si_info_list[$sku_item_id]['cost'];
		$temp['qty'] = $si_info_list[$sku_item_id]['qty'];
		$temp['date'] = $last_date;
		$temp['l90d_grn'] = $si_info_list[$sku_item_id]['l90d_grn'];
		$temp['l90d_pos'] = $si_info_list[$sku_item_id]['l90d_pos'];
		$temp['l30d_grn'] = $si_info_list[$sku_item_id]['l30d_grn'];
		$temp['l30d_pos'] = $si_info_list[$sku_item_id]['l30d_pos'];
		$temp['changed'] = 0;
		if($sku_use_avg_cost_as_last_cost)	$temp['grn_cost'] = $temp['avg_cost'];	// use avg cost as last cost
		
	    //$con->sql_query("replace into sku_items_cost ".mysql_insert_by_field($temp));
	    
		$q_sic = $con->sql_query("select changed from sku_items_cost where branch_id=$branch_id and sku_item_id=$sku_item_id");
		$latest_changed = $con->sql_fetchfield(0);
		$con->sql_freeresult($q_sic);
		if($latest_changed && $latest_changed != 2)	$temp['changed'] = 1;	// got changes when this calculation is running
	
		$con->sql_query("insert into sku_items_cost ".mysql_insert_by_field($temp)." on duplicate key update
			grn_cost=".mf($temp['grn_cost']).",
			avg_cost=".mf($temp['avg_cost']).",
			fresh_market_cost=".mf($temp['fresh_market_cost']).",
			qty=".mf($temp['qty']).",
			date=".ms($temp['date']).",
			l90d_grn=".mf($temp['l90d_grn']).",
			l90d_pos=".mf($temp['l90d_pos']).",
			l30d_grn=".mf($temp['l30d_grn']).",
			l30d_pos=".mf($temp['l30d_pos']).",
			changed=".mi($temp['changed'])."
			");
		
	    if($si_info_list[$sku_item_id]['sb']){
			$si_info_list[$sku_item_id]['sb']['to_date'] = '';
			$si_info_list[$sku_item_id]['sb_list'][] = $si_info_list[$sku_item_id]['sb'];
		}
		
		if($si_info_list[$sku_item_id]['vendor_data']){
			foreach($si_info_list[$sku_item_id]['vendor_data'] as $tmp_vendor_id => $vd){
				$last_vendor_id = $tmp_vendor_id;
				
				$vd['to_date'] = '9999-12-31';
				$con->sql_query("insert into $vendor_sku_history_tbl ".mysql_insert_by_field($vd));
				unset($si_info_list[$sku_item_id]['vendor_data'][$tmp_vendor_id]);
			}
		}
		if(!$last_vendor_id)	$last_vendor_id = $master_vendor_id;
		
		// last vendor
		$last_vd = array();
		$last_vd['sku_item_id'] = $sku_item_id;
		$last_vd['from_date'] = $last_vd['to_date'] = '0000-00-00';
		$last_vd['vendor_id'] = $last_vendor_id;
		$con->sql_query("delete from $vendor_sku_history_tbl where sku_item_id=$sku_item_id and from_date='0000-00-00' and to_date='0000-00-00'");
		$con->sql_query("replace into $vendor_sku_history_tbl ".mysql_insert_by_field($last_vd));
	
	  // clear sku data first
		$q1 = $con->sql_query("show tables");
		while($r = $con->sql_fetchrow($q1)){
			if(strpos($r[0],'stock_balance_b'.$branch_id.'_')===false){

			}else{
				if($filter_sb_year){  // do not delete all data if the deletion is just run for potion date
					$check_year = str_replace("stock_balance_b".$branch_id."_","",$r[0]);

					if($check_year<$filter_sb_year)  continue;  // skip the previous data
					elseif($check_year==$filter_sb_year){
						// if same year, only delete the data after the selected date
						$con->sql_query("delete from $r[0] where sku_item_id=".ms($sku_item_id)." and to_date>=".ms($filter_from_date),false,false);
						continue;
					}
				}

				$con->sql_query("delete from $r[0] where sku_item_id=".ms($sku_item_id),false,false);
			}
		}
		$con->sql_freeresult($q1);
	
		if($si_info_list[$sku_item_id]['sb_list']){ // insert stock balance data
			foreach($si_info_list[$sku_item_id]['sb_list'] as $sb){
				if($sku_use_avg_cost_as_last_cost){
					$sb['cost'] = $sb['avg_cost'];	// use avg cost as last cost
				}
			  create_stock_balance_row($sb, $branch_id);
			}
		}
	}
}

function get_and_update_parent_child_total(&$si_info_list, $params = array()){
	global $config;
	
	$sku_use_avg_cost_as_last_cost = mi($config['sku_use_avg_cost_as_last_cost']);
	
	if(!$si_info_list)	return;
	$tmp_cron_txt = 'tmp_cron.txt';
	
	$update_avg_pcs_cost = mi($params['update_avg_pcs_cost']);
	
	if(isset($params['update_use_item_grn_cost']))	$update_use_item_grn_cost = $params['update_use_item_grn_cost'];
	
	$ret = array(
		'total_cost' => 0,
		'total_pcs' => 0,
		'avg_pcs_cost' => 0
	);
	
	if($update_avg_pcs_cost){
		//file_put_contents($tmp_cron_txt, "Trigger To Update Cost for All SKU\n", FILE_APPEND);
	}
	foreach($si_info_list as $sku_item_id => $si_info){
		if($si_info['qty']<=0)	continue;
		
		$ret['total_pcs'] += $si_info['qty'] * $si_info['info']['packing_uom_fraction'];
		$ret['total_cost'] += $si_info['qty'] * $si_info['avg_cost'];
		
		if($update_avg_pcs_cost){
			//file_put_contents($tmp_cron_txt, "Add Total PCS, SID:$sku_item_id,  Total PCS += ".$si_info['qty']." * ".$si_info['info']['packing_uom_fraction']." = ".$ret['total_pcs']."\n", FILE_APPEND);
			//file_put_contents($tmp_cron_txt, "Add Total Cost, SID:$sku_item_id,  Total Cost += ".$si_info['qty']." * ".$si_info['avg_cost']." = ".$ret['total_cost']."\n", FILE_APPEND);
		}
	}
	
	if($ret['total_pcs']){
		$ret['avg_pcs_cost'] = round($ret['total_cost'] / $ret['total_pcs'], 5);
		
		if($update_avg_pcs_cost){
			//file_put_contents($tmp_cron_txt, "AVG PCS Cost, ".$ret['total_cost']." / ".$ret['total_pcs']." = ".$ret['avg_pcs_cost']."\n", FILE_APPEND);
		}
	}	
	
	if($update_use_item_grn_cost || $update_avg_pcs_cost){
		if($update_use_item_grn_cost){	// update by using item latest grn cost
			$sid = mi($update_use_item_grn_cost['sid']);
			$grn_cost = $update_use_item_grn_cost['cost'];
			
			$pcs_cost = $grn_cost / $si_info_list[$sid]['info']['packing_uom_fraction'];
			
			foreach($si_info_list as $sku_item_id => $si_info){
				if($sku_item_id == $sid)	continue;
				
				$si_info_list[$sku_item_id]['cost'] = round($si_info['info']['packing_uom_fraction'] * $pcs_cost, 5);
			}
		}
		
		// avg
		//file_put_contents($tmp_cron_txt, "Begin Update AVG Cost for All SKU, Total PCS: $ret[total_pcs], Total Cost: $ret[total_cost], AVG Cost = $ret[avg_pcs_cost]\n", FILE_APPEND);
		
		foreach($si_info_list as $sku_item_id => $si_info){	// need update avg cost as well
			$last_cost = $si_info_list[$sku_item_id]['avg_cost'];
			$si_info_list[$sku_item_id]['avg_cost'] = round($si_info['info']['packing_uom_fraction'] * $ret['avg_pcs_cost'], 5);
			
			if($update_avg_pcs_cost){
				$si_info_list[$sku_item_id]['cost'] = $si_info_list[$sku_item_id]['avg_cost'];
				//file_put_contents($tmp_cron_txt, "Update Cost, SID: $sku_item_id, Cost: ".$si_info_list[$sku_item_id]['cost']."\n", FILE_APPEND);
			}
			
			if(!$si_info_list[$sku_item_id]['avg_cost']){
				$si_info_list[$sku_item_id]['avg_cost'] = $last_cost;	// use back the cost before update if all item is zero pcs
				if($update_avg_pcs_cost){
					$si_info_list[$sku_item_id]['cost'] = $si_info_list[$sku_item_id]['avg_cost'];
					//file_put_contents($tmp_cron_txt, "Cannot Zero Cost, SID: $sku_item_id, Cost: Update Back to ".$si_info_list[$sku_item_id]['cost']."\n", FILE_APPEND);
				}
			}	
		}
	}
	
	return $ret;
}

function calculate_group_sku_grn_avg_cost(&$si_info_list, $params = array()){
	global $con, $sessioninfo, $config;
	
	if(!$params['grn'])	return;
	
	$group_pcs = 0;
	$group_total_avg_cost = 0;
	
	$grn_item_sid = $params['grn_item']['sid'];
	$grn = $params['grn'];
	
	//if($sessioninfo['u']=='admin'){
		//print "<br>==============<br>";
	//}
	
	foreach($si_info_list as $sku_item_id => $si_info){
		$group_pcs += $si_info['qty'] * $si_info['info']['packing_uom_fraction'];
		$group_total_avg_cost += $si_info['qty'] * $si_info['avg_cost'];
	
		//if($sessioninfo['u']=='admin'){
			//print "sku item id = $sku_item_id, qty = ".$si_info['qty'].", avg cost = ".$si_info['avg_cost'].", pcs = ".($si_info['qty'] * $si_info['info']['packing_uom_fraction']).", total avg cost = ".($si_info['qty'] * $si_info['avg_cost'])."<br>";
		//}	
	}
	
	$group_pcs = round($group_pcs, 5);
	$group_total_avg_cost = round($group_total_avg_cost, 5);
	
	//if($sessioninfo['u']=='admin'){
		//print_r($si_info_list);
		//print "group_pcs = $group_pcs, group_total_avg_cost = $group_total_avg_cost<br>";
	//}
	$group_pcs_b4_grn = $group_pcs;
	
	$grn_total_cost = $grn['cost'];
	$grn_total_pcs = $grn['qty'] * $si_info_list[$grn_item_sid]['info']['packing_uom_fraction'];
	$grn_pcs_cost = round($grn_total_cost / $grn_total_pcs, 5);
	
	$group_pcs += $grn_total_pcs;
	$group_total_avg_cost += $grn_total_cost;
	
	//if($sessioninfo['u']=='admin'){
		//print "GRN total pcs = $grn_total_pcs, grn total cost = $grn_total_cost<br>";
		//print "New group_pcs = $group_pcs, new group_total_avg_cost = $group_total_avg_cost<br>";
	//}
	
	$pcs_cost = $group_total_avg_cost / $group_pcs;
	//if($sessioninfo['u']=='admin'){
		//print "new pcs avg cost = ($group_total_avg_cost / $group_pcs) = $pcs_cost<br>";
	//}
	if($pcs_cost <= 0 || $group_pcs <= 0 || $group_pcs_b4_grn <= 0){
		//if($sessioninfo['u']=='admin'){
			//print "$pcs_cost is <= zero, use grn pcs cost = $grn_pcs_cost<br>";
		//}
		$pcs_cost = $grn_pcs_cost;
	}	
	//print "pcs_cost = $pcs_cost<br>";
	foreach($si_info_list as $sku_item_id => $si_info){
		$si_info_list[$sku_item_id]['avg_cost'] = $si_info['info']['packing_uom_fraction'] * $pcs_cost;
	}
}

function create_next_year_sb_tbl($bid){
	global $con;
	
	$curr_year = date("Y"); // current year
	$curr_month = date("m"); // current month
	$curr_day = date("d"); // current day
	$tbl = "stock_balance_b".$bid."_".($curr_year+1);
	
	// if found it is not last month of the year and also first day of the month, exit this function
	if($curr_month != 12) return;
	else{
		// check if the table is existed
		$q = $con->sql_query("select 1 from $tbl limit 1",false,false);
		if($q) return; // table is existed
		$con->sql_freeresult($q);
	}
	
	create_sb_table($tbl);
}

function calculate_group_sku_stock_take($d, &$si_info_list, $stock_take_info, &$display_data){
	global $config;
	
	if(!isset($stock_take_info['item_list']))	return;
	$entry = array('type'=>'stock_take');
	
	// BEFORE
	foreach($si_info_list as $sid => $si){
		$entry['before']['item_list'][$sid]['qty'] = $si['qty'];
		$entry['before']['item_list'][$sid]['cost'] = $si['cost'];
		$entry['before']['item_list'][$sid]['avg_cost'] = $si['avg_cost'];
		$entry['before']['item_list'][$sid]['total_cost'] = $si['qty']*$si['cost'];
		$entry['before']['item_list'][$sid]['total_avg_cost'] = $si['qty']*$si['avg_cost'];
		
		$entry['before']['total']['qty'] += $si['qty']*$si['info']['packing_uom_fraction'];
		$entry['before']['total']['total_cost'] += $entry['before']['item_list'][$sid]['total_cost'];
		$entry['before']['total']['total_avg_cost'] += $entry['before']['item_list'][$sid]['total_avg_cost'];
		
		$entry['before']['total']['cost'] = $display_data['current']['cost'];
		$entry['before']['total']['avg_cost'] = $display_data['current']['avg_cost'];
	}
	
	// ACTION
	$total_sc_cost = 0;
	$total_sc_pcs = 0;
	$pcs_cost = 0;
		
	foreach($stock_take_info['item_list'] as $sid => $sc){
		if($sc['got_cost']){
			foreach($sc['got_cost'] as $tmp_cost => $sc_details){
				$row_cost = $sc_details['qty']*$tmp_cost;
				$entry['action']['item_list'][$sid]['qty'] += $sc_details['qty'];
				$entry['action']['item_list'][$sid]['total_cost'] += $row_cost;
				$entry['action']['item_list'][$sid]['total_avg_cost'] += $row_cost;
				if($row_cost){
					$total_sc_cost += $row_cost;
					$total_sc_pcs += $sc_details['qty']*$si_info_list[$sid]['info']['packing_uom_fraction'];
					
					// got qty, cost can average
					$entry['action']['item_list'][$sid]['cost'] = round($entry['action']['item_list'][$sid]['total_cost']/$entry['action']['item_list'][$sid]['qty'], $config['global_cost_decimal_points']);
					$entry['action']['item_list'][$sid]['avg_cost'] = round($entry['action']['item_list'][$sid]['total_avg_cost']/$entry['action']['item_list'][$sid]['qty'], $config['global_cost_decimal_points']);
					
				}else{
					// no qty, need to get previous cost
					//if(!$entry['action']['item_list'][$sid]['cost'])	$entry['action']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
					//if(!$entry['action']['item_list'][$sid]['avg_cost'])	$entry['action']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
					$entry['action']['item_list'][$sid]['cost'] = 0;
					$entry['action']['item_list'][$sid]['avg_cost'] = 0;
				}				
				
				// store all the stock take row
				$entry['action']['item_list'][$sid]['sc_list'][] = array('qty'=>$sc_details['qty'], 'cost'=>$tmp_cost);
			}
		}
		if($sc['no_cost']){
			$entry['action']['item_list'][$sid]['qty'] += $sc['no_cost']['qty'];
			//$entry['action']['item_list'][$sid]['cost'] = $si_info_list[$sid]['cost'];
			//$entry['action']['item_list'][$sid]['avg_cost'] = $si_info_list[$sid]['avg_cost'];
			//$entry['action']['item_list'][$sid]['total_cost'] += $sc_details['qty']*$si_info_list[$sid]['cost'];
			//$entry['action']['item_list'][$sid]['total_avg_cost'] += $sc_details['qty']*$si_info_list[$sid]['avg_cost'];
		}
		
	}
	
	// BALANCE
	foreach($si_info_list as $sid => $si){
		if(isset($entry['action']['item_list'][$sid])){
			// take from ACTION
			$entry['balance']['item_list'][$sid] = $entry['action']['item_list'][$sid];
			
			if(!$entry['balance']['item_list'][$sid]['cost']){
				$entry['balance']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
				$entry['balance']['item_list'][$sid]['total_cost'] = $entry['balance']['item_list'][$sid]['cost']*$entry['balance']['item_list'][$sid]['qty'];
			}
			
			if(!$entry['balance']['item_list'][$sid]['avg_cost']){
				$entry['balance']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
				$entry['balance']['item_list'][$sid]['total_avg_cost'] = $entry['balance']['item_list'][$sid]['avg_cost']*$entry['balance']['item_list'][$sid]['qty'];
			}
		}else{
			// take from BEFORE
			$entry['balance']['item_list'][$sid] = $entry['before']['item_list'][$sid];
		}
		
		$entry['balance']['total']['qty'] += $entry['balance']['item_list'][$sid]['qty']*$si['info']['packing_uom_fraction'];
		$entry['balance']['total']['total_cost'] += $entry['balance']['item_list'][$sid]['total_cost'];
		$entry['balance']['total']['total_avg_cost'] += $entry['balance']['item_list'][$sid]['total_avg_cost'];
		
	}
	
	if($entry['balance']['total']['qty']){
		$entry['balance']['total']['cost'] = round($entry['balance']['total']['total_cost'] / $entry['balance']['total']['qty'], $config['global_cost_decimal_points']);
		$entry['balance']['total']['avg_cost'] = round($entry['balance']['total']['total_avg_cost'] / $entry['balance']['total']['qty'], $config['global_cost_decimal_points']);
	}
	
	
	if($total_sc_pcs > 0){	// stock take got change cost
		$pcs_cost = round($total_sc_cost / $total_sc_pcs, $config['global_cost_decimal_points']);
		$entry['after']['pcs_cost'] = $pcs_cost;
		$entry['after']['total_sc_cost'] = $total_sc_cost;
		$entry['after']['total_sc_pcs'] = $total_sc_pcs;
	}	
	
	// AfTER
	foreach($si_info_list as $sid => $si){
		$entry['after']['item_list'][$sid]['qty'] = $entry['balance']['item_list'][$sid]['qty'];
		
		if($pcs_cost > 0){			
			$entry['after']['item_list'][$sid]['cost'] = $pcs_cost*$si['info']['packing_uom_fraction'];
			$entry['after']['item_list'][$sid]['avg_cost'] = $pcs_cost*$si['info']['packing_uom_fraction'];
		}else{
			$entry['after']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
			$entry['after']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
		}
		$entry['after']['item_list'][$sid]['total_cost'] = $entry['after']['item_list'][$sid]['qty']*$entry['after']['item_list'][$sid]['cost'];
		$entry['after']['item_list'][$sid]['total_avg_cost'] = $entry['after']['item_list'][$sid]['qty']*$entry['after']['item_list'][$sid]['avg_cost'];
		
		$si_info_list[$sid]['qty'] = $entry['after']['item_list'][$sid]['qty'];
		$si_info_list[$sid]['cost'] = $entry['after']['item_list'][$sid]['cost'];
		$si_info_list[$sid]['avg_cost'] = $entry['after']['item_list'][$sid]['avg_cost'];
		//$si_info_list[$sid]['total_cost'] = $entry['after']['item_list'][$sid]['total_cost'];
		//$si_info_list[$sid]['total_avg_cost'] = $entry['after']['item_list'][$sid]['total_avg_cost'];
		
		$entry['after']['total']['qty'] += $entry['after']['item_list'][$sid]['qty']*$si['info']['packing_uom_fraction'];
		
		if($pcs_cost > 0){
			$entry['after']['total']['cost'] = $pcs_cost;
			$entry['after']['total']['avg_cost'] = $pcs_cost;
		}else{
			$entry['after']['total']['cost'] = $entry['before']['total']['cost'];
			$entry['after']['total']['avg_cost'] = $entry['before']['total']['avg_cost'];
		}		
		
		$entry['after']['total']['total_cost'] += $entry['after']['item_list'][$sid]['total_cost'];
		$entry['after']['total']['total_avg_cost'] += $entry['after']['item_list'][$sid]['total_avg_cost'];
	}
	
	$display_data['current']['cost'] = $entry['after']['total']['cost'];
	$display_data['current']['avg_cost'] = $entry['after']['total']['avg_cost'];
	//$display_data['date_list'][$d][] = $entry;
	
	//print "<br>Stock Take: $d<br>";
	//print_r($stock_take_info);
	//print_r($display_data);
}

function calculate_group_sku_grn($d, &$si_info_list, $grn_info, &$display_data){
	global $config;
	
	if(!isset($grn_info))	return;
	$tmp_cron_txt = 'tmp_cron.txt';
	//file_put_contents($tmp_cron_txt, "$d: GRN Data \n".print_r($grn_info, true), FILE_APPEND);
	//print "<br>GRN<br>";
	//print_r($grn_info);
	foreach($grn_info as $grn_key => $grn){
		// this GRN no touch the cost
		if(!$grn['count_this_grn']){
			foreach($grn['item_list'] as $sid => $grn_details){
				$si_info_list[$sid]['qty'] += $grn_details['qty'];
			}
			continue;
		}
		
		$entry = array('type'=>'grn', 'link'=>$grn['link']);
		
		// BEFORE
		foreach($si_info_list as $sid => $si){
			$entry['before']['item_list'][$sid]['qty'] = $si['qty'];
			$entry['before']['item_list'][$sid]['cost'] = $si['cost'];
			$entry['before']['item_list'][$sid]['avg_cost'] = $si['avg_cost'];
			$entry['before']['item_list'][$sid]['total_cost'] = $si['qty']*$si['cost'];
			$entry['before']['item_list'][$sid]['total_avg_cost'] = $si['qty']*$si['avg_cost'];
			
			$entry['before']['total']['qty'] += $si['qty']*$si['info']['packing_uom_fraction'];
			$entry['before']['total']['total_cost'] += $entry['before']['item_list'][$sid]['total_cost'];
			$entry['before']['total']['total_avg_cost'] += $entry['before']['item_list'][$sid]['total_avg_cost'];
			
			$entry['before']['total']['cost'] = $display_data['current']['cost'];
			$entry['before']['total']['avg_cost'] = $display_data['current']['avg_cost'];
		}
		
		// ACTION
		$total_grn_cost = 0;
		$total_grn_pcs = 0;
		$pcs_cost = 0;
		
		foreach($grn['item_list'] as $sid => $grn_details){
			
			$entry['action']['item_list'][$sid]['qty'] += $grn_details['qty'];
			
			// last cost
			$entry['action']['item_list'][$sid]['cost'] = round($grn_details['total_cost']/$grn_details['qty'], $config['global_cost_decimal_points']);
			if($entry['action']['item_list'][$sid]['cost']<= 0){	// cost less than zero?
				$entry['action']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
			}
			$entry['action']['item_list'][$sid]['total_cost'] = $entry['action']['item_list'][$sid]['qty']*$entry['action']['item_list'][$sid]['cost'];
			
			// avg cost
			$entry['action']['item_list'][$sid]['avg_cost'] = $entry['action']['item_list'][$sid]['cost'];
			$entry['action']['item_list'][$sid]['total_avg_cost'] = $entry['action']['item_list'][$sid]['total_cost'];
			
			// get total qty and cost of GRN
			if($grn_details['total_cost']){
				$total_grn_cost += $grn_details['total_cost'];
				$total_grn_pcs += $grn_details['qty']*$si_info_list[$sid]['info']['packing_uom_fraction'];
			}			
		}
		if($total_grn_pcs>0){	// this grn will update cost
			$pcs_cost = round($total_grn_cost / $total_grn_pcs, $config['global_cost_decimal_points']);
		}
		
		// BALANCE
		foreach($si_info_list as $sid => $si){
			if(isset($entry['action']['item_list'][$sid])){
				// take from ACTION
				$entry['balance']['item_list'][$sid]['qty'] = $entry['before']['item_list'][$sid]['qty']+$entry['action']['item_list'][$sid]['qty'];
				
				// cost
				if($pcs_cost){	// this grn will update cost
					$entry['balance']['item_list'][$sid]['cost'] = $pcs_cost * $si['info']['packing_uom_fraction'];
				}else{
					$entry['balance']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
				}
				$entry['balance']['item_list'][$sid]['total_cost'] += round($entry['balance']['item_list'][$sid]['qty']*$entry['balance']['item_list'][$sid]['cost'], $config['global_cost_decimal_points']);
				
				// avg cost
				if($pcs_cost){	// this grn will update cost
					$entry['balance']['item_list'][$sid]['total_avg_cost'] = round($entry['before']['item_list'][$sid]['total_avg_cost']+($pcs_cost * $si['info']['packing_uom_fraction'] * $entry['action']['item_list'][$sid]['qty']), $config['global_cost_decimal_points']);
					if($entry['balance']['item_list'][$sid]['qty']){
						$entry['balance']['item_list'][$sid]['avg_cost'] = round($entry['balance']['item_list'][$sid]['total_avg_cost'] / $entry['balance']['item_list'][$sid]['qty'], $config['global_cost_decimal_points']);
					}else{
						$entry['balance']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
					}
					
				}else{
					$entry['balance']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
					$entry['balance']['item_list'][$sid]['total_avg_cost'] = round($entry['balance']['item_list'][$sid]['avg_cost']*$entry['balance']['item_list'][$sid]['qty'], $config['global_cost_decimal_points']);
				}
			}else{
				// take from BEFORE
				$entry['balance']['item_list'][$sid] = $entry['before']['item_list'][$sid];
			}
		
			$entry['balance']['total']['qty'] += $entry['balance']['item_list'][$sid]['qty']*$si['info']['packing_uom_fraction'];
			$entry['balance']['total']['total_cost'] += $entry['balance']['item_list'][$sid]['total_cost'];
			$entry['balance']['total']['total_avg_cost'] += $entry['balance']['item_list'][$sid]['total_avg_cost'];
			
		}
		//$entry['balance']['total']['cost'] = round($entry['balance']['total']['total_cost'] / $entry['balance']['total']['qty'], $config['global_cost_decimal_points']);
		$entry['balance']['total']['avg_cost'] = round($entry['balance']['total']['total_avg_cost'] / $entry['balance']['total']['qty'], $config['global_cost_decimal_points']);
		
		if($pcs_cost){
			$entry['after']['pcs_cost'] = $pcs_cost;
			$entry['after']['total_grn_cost'] = $total_grn_cost;
			$entry['after']['total_grn_pcs'] = $total_grn_pcs;
		}
		
		if($entry['balance']['total']['avg_cost'] <= 0 || $entry['balance']['total']['qty'] <= 0 || $entry['before']['total']['qty'] <= 0 || $entry['before']['total']['total_avg_cost'] <= 0){	// average cost or total pcs is negative
			if($pcs_cost){
				//$entry['balance']['total']['old_avg_cost'] = $entry['balance']['total']['avg_cost'];	// record down the negative value
				$entry['balance']['total']['avg_cost'] = $pcs_cost;	// use grn cost as average cost
			}
		}
		
		// AfTER
		foreach($si_info_list as $sid => $si){
			$entry['after']['item_list'][$sid]['qty'] = $entry['balance']['item_list'][$sid]['qty'];
			
			// cost
			if($pcs_cost){	// this grn will update cost
				$entry['after']['item_list'][$sid]['cost'] = $pcs_cost * $si['info']['packing_uom_fraction'];
			}else{
				$entry['after']['item_list'][$sid]['cost'] = $entry['balance']['item_list'][$sid]['cost'];
			}
			$entry['after']['item_list'][$sid]['total_cost'] = $entry['balance']['item_list'][$sid]['qty']*$entry['after']['item_list'][$sid]['cost'];
			
			// avg cost			
			$entry['after']['item_list'][$sid]['avg_cost'] = $entry['balance']['total']['avg_cost']*$si['info']['packing_uom_fraction'];
			$entry['after']['item_list'][$sid]['total_avg_cost'] = $entry['balance']['item_list'][$sid]['qty']*$entry['after']['item_list'][$sid]['avg_cost'];
			
			$si_info_list[$sid]['qty'] = $entry['after']['item_list'][$sid]['qty'];
			$si_info_list[$sid]['cost'] = $entry['after']['item_list'][$sid]['cost'];
			$si_info_list[$sid]['avg_cost'] = $entry['after']['item_list'][$sid]['avg_cost'];
			//$si_info_list[$sid]['total_cost'] = $entry['after']['item_list'][$sid]['total_cost'];
			//$si_info_list[$sid]['total_avg_cost'] = $entry['after']['item_list'][$sid]['total_avg_cost'];
			
			$entry['after']['total']['qty'] += $entry['after']['item_list'][$sid]['qty']*$si['info']['packing_uom_fraction'];
			$entry['after']['total']['cost'] = $pcs_cost;
			$entry['after']['total']['total_cost'] += $entry['after']['item_list'][$sid]['total_cost'];
			$entry['after']['total']['avg_cost'] = $entry['balance']['total']['avg_cost'];
			$entry['after']['total']['total_avg_cost'] += $entry['after']['item_list'][$sid]['total_avg_cost'];
		}
	
		$display_data['current']['cost'] = $entry['after']['total']['cost'];
		$display_data['current']['avg_cost'] = $entry['after']['total']['avg_cost'];
		//$display_data['date_list'][$d][] = $entry;
	}
}

function calculate_group_sku_work_order($d, &$si_info_list, $wo_info, &$display_data){
	global $config, $sessioninfo;
	
	if(!isset($wo_info))	return;
	
	foreach($wo_info as $wo_key => $wo){		
		$entry = array('type'=>'work_order', 'link'=>$wo['link']);
		
		// BEFORE
		foreach($si_info_list as $sid => $si){
			$entry['before']['item_list'][$sid]['qty'] = $si['qty'];
			$entry['before']['item_list'][$sid]['cost'] = $si['cost'];
			$entry['before']['item_list'][$sid]['avg_cost'] = $si['avg_cost'];
			$entry['before']['item_list'][$sid]['total_cost'] = $si['qty']*$si['cost'];
			$entry['before']['item_list'][$sid]['total_avg_cost'] = $si['qty']*$si['avg_cost'];
			
			$entry['before']['total']['qty'] += $si['qty']*$si['info']['packing_uom_fraction'];
			$entry['before']['total']['total_cost'] += $entry['before']['item_list'][$sid]['total_cost'];
			$entry['before']['total']['total_avg_cost'] += $entry['before']['item_list'][$sid]['total_avg_cost'];
			
			$entry['before']['total']['cost'] = $display_data['current']['cost'];
			$entry['before']['total']['avg_cost'] = $display_data['current']['avg_cost'];
		}
		
		// ACTION
		$total_wo_cost = 0;
		$total_wo_pcs = 0;
		$pcs_cost = 0;
		
		foreach($wo['item_list'] as $sid => $wo_details){
			
			$entry['action']['item_list'][$sid]['qty'] += $wo_details['qty'];
			
			// last cost
			$entry['action']['item_list'][$sid]['cost'] = round($wo_details['total_cost']/$wo_details['qty'], $config['global_cost_decimal_points']);
			if($entry['action']['item_list'][$sid]['cost']<= 0){	// cost less than zero?
				$entry['action']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
			}
			$entry['action']['item_list'][$sid]['total_cost'] = $entry['action']['item_list'][$sid]['qty']*$entry['action']['item_list'][$sid]['cost'];
			
			// avg cost
			$entry['action']['item_list'][$sid]['avg_cost'] = $entry['action']['item_list'][$sid]['cost'];
			$entry['action']['item_list'][$sid]['total_avg_cost'] = $entry['action']['item_list'][$sid]['total_cost'];
			
			// get total qty and cost
			if($wo_details['total_cost']){
				$total_wo_cost += $wo_details['total_cost'];
				$total_wo_pcs += $wo_details['qty']*$si_info_list[$sid]['info']['packing_uom_fraction'];
			}			
		}
		if($total_wo_pcs>0){	// this grn will update cost
			$pcs_cost = round($total_wo_cost / $total_wo_pcs, $config['global_cost_decimal_points']);
		}
		
		// BALANCE
		foreach($si_info_list as $sid => $si){
			if(isset($entry['action']['item_list'][$sid])){
				// take from ACTION
				$entry['balance']['item_list'][$sid]['qty'] = $entry['before']['item_list'][$sid]['qty']+$entry['action']['item_list'][$sid]['qty'];
				
				// cost
				if($pcs_cost){	// this work order will update cost
					$entry['balance']['item_list'][$sid]['cost'] = $pcs_cost * $si['info']['packing_uom_fraction'];
				}else{
					$entry['balance']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
				}
				$entry['balance']['item_list'][$sid]['total_cost'] += round($entry['balance']['item_list'][$sid]['qty']*$entry['balance']['item_list'][$sid]['cost'], $config['global_cost_decimal_points']);
				
				// avg cost
				if($pcs_cost){	// this grn will update cost
					$entry['balance']['item_list'][$sid]['total_avg_cost'] = round($entry['before']['item_list'][$sid]['total_avg_cost']+($pcs_cost * $si['info']['packing_uom_fraction'] * $entry['action']['item_list'][$sid]['qty']), $config['global_cost_decimal_points']);
					
					if($entry['balance']['item_list'][$sid]['qty']){
						$entry['balance']['item_list'][$sid]['avg_cost'] = round($entry['balance']['item_list'][$sid]['total_avg_cost'] / $entry['balance']['item_list'][$sid]['qty'], $config['global_cost_decimal_points']);
					}else{
						$entry['balance']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
					}
					
				}else{
					$entry['balance']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
					$entry['balance']['item_list'][$sid]['total_avg_cost'] = round($entry['balance']['item_list'][$sid]['avg_cost']*$entry['balance']['item_list'][$sid]['qty'], $config['global_cost_decimal_points']);
				}
			}else{
				// take from BEFORE
				$entry['balance']['item_list'][$sid] = $entry['before']['item_list'][$sid];
			}
		
			$entry['balance']['total']['qty'] += $entry['balance']['item_list'][$sid]['qty']*$si['info']['packing_uom_fraction'];
			$entry['balance']['total']['total_cost'] += $entry['balance']['item_list'][$sid]['total_cost'];
			$entry['balance']['total']['total_avg_cost'] += $entry['balance']['item_list'][$sid]['total_avg_cost'];
			
		}
		//$entry['balance']['total']['cost'] = round($entry['balance']['total']['total_cost'] / $entry['balance']['total']['qty'], $config['global_cost_decimal_points']);
		$entry['balance']['total']['avg_cost'] = round($entry['balance']['total']['total_avg_cost'] / $entry['balance']['total']['qty'], $config['global_cost_decimal_points']);
		
		if($pcs_cost){
			$entry['after']['pcs_cost'] = $pcs_cost;
			$entry['after']['total_wo_cost'] = $total_wo_cost;
			$entry['after']['total_wo_pcs'] = $total_wo_pcs;
		}
		
		//if($sessioninfo['id'] == 1){
			if($entry['balance']['total']['avg_cost'] <= 0 || $entry['balance']['total']['qty'] <= 0 || $entry['before']['total']['qty'] <= 0 || $entry['before']['total']['total_avg_cost'] <= 0){	// average cost or total pcs is negative
				if($pcs_cost){
					$entry['balance']['total']['old_avg_cost'] = $entry['balance']['total']['avg_cost'];	// record down the negative value
					$entry['balance']['total']['avg_cost'] = $pcs_cost;	// use grn cost as average cost
				}
			}
		//}
		
		
		// AfTER
		foreach($si_info_list as $sid => $si){
			$entry['after']['item_list'][$sid]['qty'] = $entry['balance']['item_list'][$sid]['qty'];
			
			// cost
			if($pcs_cost){	// this grn will update cost
				$entry['after']['item_list'][$sid]['cost'] = $pcs_cost * $si['info']['packing_uom_fraction'];
			}else{
				$entry['after']['item_list'][$sid]['cost'] = $entry['balance']['item_list'][$sid]['cost'];
			}
			$entry['after']['item_list'][$sid]['total_cost'] = $entry['balance']['item_list'][$sid]['qty']*$entry['after']['item_list'][$sid]['cost'];
			
			// avg cost
			$entry['after']['item_list'][$sid]['avg_cost'] = $entry['balance']['total']['avg_cost']*$si['info']['packing_uom_fraction'];
			$entry['after']['item_list'][$sid]['total_avg_cost'] = $entry['balance']['item_list'][$sid]['qty']*$entry['after']['item_list'][$sid]['avg_cost'];
			
			$si_info_list[$sid]['qty'] = $entry['after']['item_list'][$sid]['qty'];
			$si_info_list[$sid]['cost'] = $entry['after']['item_list'][$sid]['cost'];
			$si_info_list[$sid]['avg_cost'] = $entry['after']['item_list'][$sid]['avg_cost'];
			//$si_info_list[$sid]['total_cost'] = $entry['after']['item_list'][$sid]['total_cost'];
			//$si_info_list[$sid]['total_avg_cost'] = $entry['after']['item_list'][$sid]['total_avg_cost'];
			
			$entry['after']['total']['qty'] += $entry['after']['item_list'][$sid]['qty']*$si['info']['packing_uom_fraction'];
			$entry['after']['total']['cost'] = $pcs_cost;
			$entry['after']['total']['total_cost'] += $entry['after']['item_list'][$sid]['total_cost'];
			$entry['after']['total']['avg_cost'] = $entry['balance']['total']['avg_cost'];
			$entry['after']['total']['total_avg_cost'] += $entry['after']['item_list'][$sid]['total_avg_cost'];
		}
	
		$display_data['current']['cost'] = $entry['after']['total']['cost'];
		$display_data['current']['avg_cost'] = $entry['after']['total']['avg_cost'];
		//$display_data['date_list'][$d][] = $entry;
	}
}
?>