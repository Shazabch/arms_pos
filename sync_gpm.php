<?php
/*
10/8/2013 4:25 PM Andy
- Fix sync broadcast message cannot correctly update sync completed status if sync to multiple branch.

10/15/2013 10:19 AM Andy
- Add sync broadcast trade offer.

12/20/2013 3:43 PM Andy
- Add sync counter status.

12/23/2013 4:57 PM Andy
- Add auto sync for last 30 days sales.

12/31/2013 2:09 PM Andy
- Change to check finalized=0 for those sales not yet finalized.

1/28/2014 4:02 PM Andy
- Enhance to send POS RAW DATA entry to server.
- Change to use class object method.

2/20/2014 3:06 PM Andy
- Add more index to sales table.

2/25/2014 2:04 PM Andy
- Enhance to able to accept parameter to change the POS start date checking.

*/
if(php_sapi_name() == 'cli'){
	define('TERMINAL',1);
	ob_end_clean();
}

require_once('include/common.php');
//$db_default_connection = array("localhost", "root", "", "yy");
//$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

ini_set('memory_limit', '512M');
set_time_limit(0);
error_reporting (E_ALL ^ E_NOTICE);

if(!isset($config['gpm_server_con']))	die("Please set GPM server config first.\n");
if(!$config['gpm_customer_name'])	die("Please set GPM customer name config first.\n");

if(defined('TERMINAL')){	// terminal
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
	array_shift($arg);	// remove first param
	$sync_type = '';
	$available_type = array('all', 'sku', 'cat', 'pos', 'branch', 'broadcast_msg', 'broadcast_offer','counter_status');
	$check_date_from = '';
	
	while($a = array_shift($arg)){
		if(preg_match('/^-sync=/', $a)){
			$tmp = str_replace('-sync=', '', $a);
			if(!$tmp || !in_array($tmp, $available_type))	die("Invalid -sync Type. Either (".join(',', $available_type).")\n");
				$sync_type = $tmp;	
		}elseif(preg_match('/^-date_from=/', $a)){
			$tmp = str_replace('-date_from=', '', $a);
			if(!$tmp)	die("Invalid -date_from, data cannot be empty.\n");
			$check_date_from = date("Y-m-d", strtotime($tmp));
			if(date("Y", strtotime($check_date_from))<2013)	die("Year cannot earlier than 2013.\n");
			
			print "Date Start = $check_date_from\n";
		}else{
			die("Unknown option $a\n");
		}
	}
	
	if(!$sync_type)	die("Invalid -sync Type. Either (".join(',', $available_type).")\n");
	
	print "Sync Type: $sync_type\n";
	print "GPM Customer: ".$config['gpm_customer_name']."\n";
	
	// connect gpm
	$GPM_DATA_HELPER = new GPM_DATA_HELPER();
	if($check_date_from)	$GPM_DATA_HELPER->check_date_from = $check_date_from;
	
	print "GPM Customer ID: $customer_id\n";
		
	$start_time = time();
	$start_timestamp = date("Y-m-d H:i:s", $start_time);
	print "Sync Start at $start_timestamp.\n";
	
	$sync_status = array();
	$sync_status['customer_id'] = $customer_id;
	$sync_status['sync_start'] = $start_timestamp;
	$sync_status['success'] = 0;
	$con_gpm->sql_query("replace into sync_status ".mysql_insert_by_field($sync_status));
	
	if($sync_type == 'cat' || $sync_type == 'all')	$GPM_DATA_HELPER->sync_category();	// down
	if($sync_type == 'sku' || $sync_type == 'all')	$GPM_DATA_HELPER->sync_sku();	// down
	if($sync_type == 'broadcast_msg' || $sync_type == 'all')	$GPM_DATA_HELPER->sync_broadcast_msg();	// down
	if($sync_type == 'broadcast_offer' || $sync_type == 'all')	$GPM_DATA_HELPER->sync_broadcast_offer();	// down
	if($sync_type == 'branch' || $sync_type == 'all')	$GPM_DATA_HELPER->sync_branch();	// up
	if($sync_type == 'pos' || $sync_type == 'all')	$GPM_DATA_HELPER->sync_pos();	// up (include stock)
	if($sync_type == 'counter_status' || $sync_type == 'all')	$GPM_DATA_HELPER->sync_counter_status();	// up
	
	$end_time = time();
	$end_timestamp = date("Y-m-d H:i:s", $end_time);
	
	$sync_status['sync_end'] = $end_timestamp;
	$sync_status['success'] = 1;
	$con_gpm->sql_query("replace into sync_status ".mysql_insert_by_field($sync_status));
	print "Sync Finish at $end_timestamp.\n";

}else{	// http
	$GPM_DATA_HELPER = new GPM_DATA_HELPER();
	
	switch($_REQUEST['a']){
		case 'sync_gpm':
			$msg_id = mi($_REQUEST['msg_id']);
			if($GPM_DATA_HELPER->sync_msg($msg_id)){
				print "OK";
			}else{
				print "Failed";
			}
			break;
		case 'sync_offer':
			$offer_id = mi($_REQUEST['offer_id']);
			if($GPM_DATA_HELPER->sync_offer($offer_id)){
				print "OK";
			}else{
				print "Failed";
			}
			break;
	}
}

class GPM_DATA_HELPER {

	var $customer_branch = array();
	var $tmp_raw_data_file = 'sync_gpm.pos_raw_data.csv';
	var $fp_pos;
	var $gpm_sku_list = array();
	var $check_date_from = '';
	
	function __construct()
	{
		global $con, $con_gpm, $config;
		
		$this->connect_gpm();
		
	}
	
	function connect_gpm(){
		global $con_gpm, $config, $customer_id;
		
		// connect gpm
		$con_gpm = connect_db($config['gpm_server_con'][0], $config['gpm_server_con'][1], $config['gpm_server_con'][2], $config['gpm_server_con'][3]);
		if(!$con_gpm)	die("Cant connect to GPM server.\n");
		
		if(!$customer_id = $this->get_customer_id())	die("Customer name '".$config['gpm_customer_name']."' is invalid.\n");
	}
	
	function get_customer_id(){
		global $con, $con_gpm, $config;
		
		// check whether this customer exists in gpm main server
		$con_gpm->sql_query("select id from customer where server_name=".ms($config['gpm_customer_name']));
		$id = mi($con_gpm->sql_fetchfield(0));
		$con_gpm->sql_freeresult();
		
		return $id;
	}
	
	function get_arms_branch_id_from_gpm_bid($gpm_bid){
		global $con, $con_gpm, $customer_id;
		
		$con_gpm->sql_query("select customer_branch_id from customer_branch where customer_id=$customer_id and id=".mi($gpm_bid));
		$bid = mi($con_gpm->sql_fetchfield(0));
		$con_gpm->sql_freeresult();
		
		return $bid;
	}
	
	function get_arms_cat_by_gpm_cat($code){
		global $con;
		
		$con->sql_query("select * from category where code=".ms($code));
		$cat = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $cat;
	}
	
	function get_gpm_branch_id($bid){
		global $con, $con_gpm, $customer_id;
		
		if(!isset($this->customer_branch[$customer_id][$bid])){
			$con_gpm->sql_query("select id,code from customer_branch where customer_id=$customer_id and customer_branch_id=".mi($bid));
			$this->customer_branch[$customer_id][$bid] = $con_gpm->sql_fetchassoc();
			$con_gpm->sql_freeresult();
		}
		
		$gpm_bid = mi($this->customer_branch[$customer_id][$bid]['id']);
		
		return $gpm_bid;
	}
	
	function get_arms_sku_item_id_by_mcode($mcode){
		global $con;
		
		$mcode = trim($mcode);
		if(!$mcode)	return 0;
		
		$con->sql_query("select id from sku_items where mcode=".ms($mcode)." order by id limit 1");
		$sid = mi($con->sql_fetchfield(0));
		$con->sql_freeresult();
		
		return $sid;
	}
	
	
	/////// API ////////
	
	function extend_category_cache($max_lv){
		global $con;
		
		$col_list = array();
		$con->sql_query("explain category_cache");
		while($r = $con->sql_fetchrow()){
			$col_list[$r[0]] = 1;
		}
		$con->sql_freeresult();
		
		for($i=0; $i<=$max_lv; $i++){
			$col = 'p'.$i;
			
			if(!isset($col_list[$col])){
				$con->sql_query("alter table category_cache add $col int default 0, add index ($col)");
				$col_list[$col] = 1;
			}
		}
	}
	
	function load_branch_info_list(){
		global $con, $con_gpm, $customer_id, $branch_info_list;
		
		if(!isset($branch_info_list)){
			$branch_info_list = array();
			$con->sql_query("select id,code from branch where active=1 order by id");
			while($r = $con->sql_fetchassoc()){
				$branch_info_list[$r['id']] = $r;
			}
			$con->sql_freeresult();
		}
	}
	
	function load_all_sku_info(){
		global $con_gpm;
		
		if(!$this->gpm_sku_list){
			$this->gpm_sku_list = array();
			
			$con_gpm->sql_query("select sku.*, c2.description as cat2_desc, c3.description as cat3_desc
	from sku
	left join category c on c.id=sku.category_id
	left join category_cache cc on cc.category_id=c.id
	left join category c2 on c2.id=cc.p2
	left join category c3 on c3.id=cc.p3");
			while($r = $con_gpm->sql_fetchassoc()){
				$this->gpm_sku_list[$r['id']] = $r;
			}
			$con_gpm->sql_freeresult();
		}
		
	}

	//////// SYNC FUNCTION //////////
	
	function sync_category(){
		global $con, $con_gpm;
		
		print "Sync category. . .\n";
		
		$gpm_cat_info = array();
		
		// current arms max cat level
		$con->sql_query("select max(level) max_lv from category");
		$max_lv = mi($con->sql_fetchfield(0));
		$con->sql_freeresult();
		
		// get all category from gpm
		$q1 = $con_gpm->sql_query("select * from category order by level,id");
		$total_row = $con_gpm->sql_numrows($q1);
		$curr_row = 0;
		while($r = $con_gpm->sql_fetchassoc($q1)){
			$curr_row++;
			print "\r$curr_row / $total_row. . .";
			
			$gpm_cat_info[$r['id']] = $r;
			
			$parent_cat = array();
			$root_id = mi($r['root_id']);
			$code = trim($r['code']);
			$lv = mi($r['level']);
			$desc = trim($r['description']);
			
			if(!$code)	continue;	// no code cannot sync
			
			// check arms whether this category code exists
			$cat = $this->get_arms_cat_by_gpm_cat($code);
			
			// do something if this cat not found
			if(!$cat){
				$upd = array();
				$upd['code'] = $code;
				$upd['description'] = $desc;
				$upd['active'] = 1;
				$upd['level'] = 1;
				$upd['root_id'] = 0;
				$upd['no_inventory'] = $upd['is_fresh_market'] = 'no';
				
				$arms_parent_cat = array();
				
				if($root_id){
					// get parent category
					$gpm_parent_cat = $gpm_cat_info[$root_id];
					
					$arms_parent_cat = $this->get_arms_cat_by_gpm_cat($gpm_parent_cat['code']);
					
					$upd['root_id'] = $arms_parent_cat['id'];
					$upd['level'] = $arms_parent_cat['level']+1;
					
					if($upd['level']>2)	$upd['department_id'] = $arms_parent_cat['department_id'];
					$upd['tree_str'] = $arms_parent_cat['tree_str'].'('.mi($arms_parent_cat['id']).')';
					
					$upd['no_inventory'] = $upd['is_fresh_market'] = 'inherit';
				}
				
				// create new cat
				$con->sql_query("insert into category ".mysql_insert_by_field($upd));
				$new_cat_id = $con->sql_nextid();
				
				// extend category cache level
				if($upd['level'] > $max_lv){
					$this->extend_category_cache($upd['level']);
					$max_lv = $upd['level'];
				}	
				
				// create category_cache
				$cc = array();
				
				if($arms_parent_cat){
					$con->sql_query("select * from category_cache where category_id=".$arms_parent_cat['id']);
					$cc = $con->sql_fetchassoc();
					$con->sql_freeresult();
				}
				
				$cc['category_id'] = $new_cat_id;
				$cc['p'.$upd['level']] = $new_cat_id;
				$con->sql_query("insert into category_cache ".mysql_insert_by_field($cc));
			}
		}
		$con_gpm->sql_freeresult($q1);
		
		print "Sync category done.\n";
	}

	function sync_sku(){
		global $con, $con_gpm;
		
		print "Sync SKU. . .\n";
		
		$con->sql_query("create table if not exists tmp_gpm_sku_link(
			mcode char(20) primary key,
			gpm_sku_id int unique,
			need_delete tinyint(1) default 0
		)");
		
		// get max sku code
		$con->sql_query("select max(sku_code) from sku where sku_code like '28%'");
		$sku_code = mi($con->sql_fetchfield(0));
		$con->sql_freeresult();
	
		if(!$sku_code)	$sku_code = 28000000;
		
		$con->sql_query("update tmp_gpm_sku_link set need_delete=1");	// mark all need delete
		
		// select all sku from gpm
		$q1 = $con_gpm->sql_query("select * from sku order by id");
		$total_row = $con_gpm->sql_numrows($q1);
		$curr_row = 0;
		$failed_arr = array();
		$created_count = 0;
		$skip_count = 0;
		
		while($r = $con_gpm->sql_fetchassoc($q1)){
			$curr_row++;
			print "\r$curr_row / $total_row. . .";
			
			$mcode = trim($r['mcode']);
			//print "$mcode\n";
			// check whether this mcode exists in arms
			$con->sql_query("select * from sku_items where mcode=".ms($mcode)." limit 1");
			$si = $con->sql_fetchassoc();
			$con->sql_freeresult();
				
			if(!$si){	// item not found
				$cat_id = 0;
				
				// get gpm category
				if($r['category_id']){
					$con_gpm->sql_query("select * from category where id=".mi($r['category_id']));
					$gpm_cat = $con_gpm->sql_fetchassoc();
					$con->sql_freeresult();
					
					// check arms whether this category code exists
					$arms_cat = $this->get_arms_cat_by_gpm_cat($gpm_cat['code']);
					if(!$arms_cat){
						$failed_arr[] = array('mcode'=>$mcode, 'error'=>'category not found');
						continue;
					}
					$cat_id = mi($arms_cat['id']);
				}
				// need to copy
				$sku_code++;
				
				$sku_ins = array();
				$sku_ins['sku_code'] = $sku_code;
				$sku_ins['category_id'] = $cat_id; 
				$sku_ins['uom_id'] = 1;	// always EACH
				$sku_ins['vendor_id'] = 1;	// vendors always gpm
				$sku_ins['brand_id'] = 0;
				$sku_ins['status'] = 1;
				$sku_ins['active'] = 1;
				$sku_ins['sku_type'] = 'OUTRIGHT';
				$sku_ins['apply_branch_id'] = 1;
				$sku_ins['added'] = 'CURRENT_TIMESTAMP';
		
				$con->sql_query("insert into sku ".mysql_insert_by_field($sku_ins));
				$sku_id = intval($con->sql_nextid());
				$sku_item_code = $sku_code.'0000';
				
				$si_ins = array();
				$si_ins['is_parent'] = 1;
				$si_ins['packing_uom_id'] = 1;
				$si_ins['sku_id'] = $sku_id;
				$si_ins['mcode'] = $mcode;
				//$si_ins['link_code'] = '';
				//$si_ins['artno'] = $artno;
				$si_ins['description'] =  trim($r['description']);
				if(trim($r['receipt_description'])){ // found receipt description…
					$si_ins['receipt_description'] = trim($r['receipt_description']);
				}else $si_ins['receipt_description'] = trim($r['description']); // or else use back the product description
				$si_ins['sku_item_code'] = $sku_item_code;
				$si_ins['hq_cost'] = $si_ins['cost_price'] = floatval(round($r['selling_price']*0.9, 5));
				$si_ins['selling_price'] = floatval($r['selling_price']);
				$si_ins['active'] = 1;
				$si_ins['added'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("insert into sku_items ".mysql_insert_by_field($si_ins));
				
				$sid = $con->sql_nextid();
				
				$created_count++;
			}else	$skip_count++;
			
			$gpm_sku_link = array();
			$gpm_sku_link['mcode'] = $mcode;
			$gpm_sku_link['gpm_sku_id'] = $r['id'];
			$con->sql_query("replace into tmp_gpm_sku_link ".mysql_insert_by_field($gpm_sku_link));
			
		}
		$con_gpm->sql_freeresult($q1);
		
		$con->sql_query("delete from tmp_gpm_sku_link where need_delete=1");
		
		print "\nDone. $created_count new sku created. $skip_count old sku skipped. ";
		if($failed_arr){
			print count($failed_arr)." error found, please check the error:\n";
			print_r($failed_arr);
		}
		print "\n";
	}

	
	function sync_broadcast_msg(){
		global $con, $con_gpm, $customer_id;
		
		print "Sync Broadcast Message.\n";
		
		$q1 = $con_gpm->sql_query("select * from broadcast_msg where sync_complete=0 and expire_timestamp>CURRENT_TIMESTAMP order by id");
		while($r = $con_gpm->sql_fetchassoc($q1)){
			$r['recipient_customer'] = unserialize($r['recipient_customer']);
			$r['synced_recipient'] = unserialize($r['synced_recipient']);
			
			if($r['recipient_customer'][$customer_id] && !$r['synced_recipient'][$customer_id]){
				print "Sync ID#".mi($r['id'])."\n";
				if($this->sync_msg($r['id'])){
					print "OK.\n";
				}else{
					print "Failed.\n";
				}
			}
		}
		$con_gpm->sql_freeresult($q1);
		print "Done.\n";
	}
	
	function sync_broadcast_offer(){
		global $con, $con_gpm, $customer_id;
		
		print "Sync Broadcast Trade Offer.\n";
		
		$q1 = $con_gpm->sql_query("select * from broadcast_trade_offer where sync_complete=0 and current_date<=date_to order by id");
		while($r = $con_gpm->sql_fetchassoc($q1)){
			$r['recipient_customer'] = unserialize($r['recipient_customer']);
			$r['synced_recipient'] = unserialize($r['synced_recipient']);
			
			if($r['recipient_customer'][$customer_id] && !$r['synced_recipient'][$customer_id]){
				print "Sync ID#".mi($r['id'])."\n";
				if($this->sync_offer($r['id'])){
					print "OK.\n";
				}else{
					print "Failed.\n";
				}
			}
		}
		$con_gpm->sql_freeresult($q1);
		print "Done.\n";
	}
	
	function sync_branch(){
		global $con, $con_gpm, $customer_id;
		
		print "Sync Branch. . .\n";
		
		// get all branch
		$q1 = $con->sql_query("select id,code,description from branch order by id");
		$total_row = $con->sql_numrows($q1);
		$curr_row = 0;
		
		while($r = $con->sql_fetchassoc($q1)){
			$curr_row++;
			print "\r$curr_row / $total_row. . .";
			// get gpm customer branch
			$con_gpm->sql_query("select * from customer_branch where customer_id=$customer_id and customer_branch_id=$r[id]");
			$customer_branch = $con_gpm->sql_fetchassoc();
			$con_gpm->sql_freeresult();
			
			if($customer_branch){	// already exists
				$upd = array();
				if($customer_branch['branch_code'] != $r['code'])	$upd['branch_code'] = $r['code'];
				if($customer_branch['branch_name'] != $r['description'])	$upd['branch_name'] = $r['description'];
				if($upd){
					$con_gpm->sql_query("update customer_branch set ".mysql_update_by_field($upd)." where customer_id=$customer_id and customer_branch_id=$r[id]");
				}
			}else{	// not exists
				$upd = array();
				$upd['customer_id'] = $customer_id;
				$upd['customer_branch_id'] = $r['id'];
				$upd['code'] = $upd['branch_code'] = $r['code'];
				$upd['name'] = $upd['branch_name'] = $r['description'];
				$con_gpm->sql_query("insert into customer_branch ".mysql_insert_by_field($upd));
			}
		}
		$con->sql_freeresult($q1);
		print "\nDone.\n";
	}
	
	function sync_pos(){
		global $con, $con_gpm, $customer_id, $branch_info_list, $config;
		
		print "Sync POS. . .\n";
		
		// get branch info list
		$this->load_branch_info_list();
		$this->load_all_sku_info();
		
		if(!$branch_info_list){
			print "No branch to sync sales.\n";
			return;
		}
		
		// create table to store last pos sync time
		$con->sql_query("create table if not exists tmp_gpm_pos_sync_info(
			branch_id int,
			date date,
			last_sync timestamp default 0,
			primary key (branch_id, date)
		)");
			
		
		$date = date("Y-m-d");	// sync today
		$check_date_from = date("Y-m-d", strtotime("-30 day"));
		if($this->check_date_from)	$check_date_from = $this->check_date_from;
		print "Check POS Start at $check_date_from.\n";
		
		foreach($branch_info_list as $b){	// loop for each branch
			$bid = mi($b['id']);
			print "Sync Branch ID#$bid, Code: ".$b['code']."\n";
			
			// get gpm branch id
			if(!$gpm_bid = $this->get_gpm_branch_id($bid)){
				print "GPM Branch ID Not Found, skipped.\n";
				continue;
			}
			print "GPM Branch ID#$gpm_bid\n";
			
			$date_list = array();
			$date_list[] = $date;
			
			// get those finalized sales, sync 1 more time
			$con->sql_query("select date from pos_finalized where gpm_synced=0 and branch_id=$bid and finalized=1 and date<".ms($date)." and date>'2013-09-18'");
			while($r = $con->sql_fetchassoc()){
				$date_list[] = $r['date'];
			}
			$con->sql_freeresult();
			
			// get those sales not yet sync from last 30 days
			$con->sql_query("select p.date,max(p.end_time) as end_time,ps.last_sync 
	from pos p 
	left join tmp_gpm_pos_sync_info ps on ps.branch_id=p.branch_id and ps.date=p.date
	left join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date
	where p.branch_id=$bid and p.date>".ms($check_date_from)." and (pf.finalized=0) and p.cancel_status=0 and (p.end_time>ps.last_sync or ps.last_sync is null)
	group by date");
			$pos_max_time = array();
			
			while($r = $con->sql_fetchassoc()){
				if(!in_array($r['date'], $date_list)){
					$date_list[] = $r['date'];
					$pos_max_time[$r['date']] = $r['end_time'];
				}
			}
			$con->sql_freeresult();
			asort($date_list);
			
			//print_r($date_list);
	
			foreach($date_list as $dd){	// loop for each date
				print "$dd\n";
				list($y, $m, $day) = explode('-', $dd);
				
				$tbl_hr = 'sales_by_hour_'.$y.'_'.$m;
				$tbl_race = 'sales_by_race_'.$y.'_'.$m;
				$tbl_item = 'sales_by_item_'.$y.'_'.$m;
				
				// create table
				$con_gpm->sql_query("create table if not exists $tbl_hr (
					branch_id int,
					date date,
					hr tinyint,
					sku_id int,
					qty double not null default 0,
					amt double not null default 0,
					discount double not null default 0,
					primary key (branch_id, date, hr, sku_id),
					index date (date),
					index sku_id (sku_id),
					index hr (hr)
				)");
				
				$con_gpm->sql_query("create table if not exists $tbl_race (
					branch_id int,
					date date,
					race char(5),
					sku_id int,
					qty double not null default 0,
					amt double not null default 0,
					discount double not null default 0,
					primary key (branch_id, date, race, sku_id),
					index date (date),
					index sku_id (sku_id),
					index race (race)
				)");
				
				$con_gpm->sql_query("create table if not exists $tbl_item (
					branch_id int,
					date date,
					sku_id int,
					qty double not null default 0,
					amt double not null default 0,
					discount double not null default 0,
					primary key (branch_id, date, sku_id),
					index date (date),
					index sku_id (sku_id)
				)");
				
				// delete data
				$con_gpm->sql_query("delete from $tbl_hr where branch_id=$gpm_bid and date=".ms($dd));
				$con_gpm->sql_query("delete from $tbl_race where branch_id=$gpm_bid and date=".ms($dd));
				$con_gpm->sql_query("delete from $tbl_item where branch_id=$gpm_bid and date=".ms($dd));
				$con_gpm->sql_query("delete from customer_branch_pos_raw_data_info where branch_id=$gpm_bid and date=".ms($dd));
				
				file_put_contents($this->tmp_raw_data_file, '');	// clear content
				
				$this->fp_pos = fopen($this->tmp_raw_data_file, 'w'); // create file handle
				
				$sql = "select pi.sku_item_id,pi.qty,pi.price,pi.discount,s2.gpm_sku_id,if(pos.race='' and pos.member_no<>'', (select if(m.race is null or m.race = '', 'O', substring(m.race,1,1))
											 from membership_history mh
											 left join membership m on m.nric = mh.nric
											 where mh.card_no=pos.member_no limit 1), pos.race) as race, hour(pos.end_time) as hr,pos.date, sic.qty as stock_balance, sic.changed as stock_changed, pos.end_time, pos.receipt_no, si.mcode,si.description, pos.cashier_id
	from pos_items pi
	join pos on pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id and pos.id=pi.pos_id
	join sku_items si on si.id=pi.sku_item_id
	join tmp_gpm_sku_link s2 on s2.mcode=si.mcode
	left join sku_items_cost sic on sic.branch_id=pi.branch_id and sic.sku_item_id=pi.sku_item_id
	where pos.branch_id=$bid and pos.date=".ms($dd)." and pos.cancel_status=0";
				$q1 = $con->sql_query($sql);
				
				$upd_hr = array();				
				$upd_hr['branch_id'] = $gpm_bid;
				$upd_hr['date'] = $dd;
				
				$upd_rc = array();
				$upd_rc['branch_id'] = $gpm_bid;
				$upd_rc['date'] = $dd;
				
				$upd_item = array();
				$upd_item['branch_id'] = $gpm_bid;
				$upd_item['date'] = $dd;
					
				$sid_list = array();
				
				$dd_dmy = date("d/m/Y", strtotime($dd));	// dd/mm/YYYY
				
				while($r = $con->sql_fetchassoc($q1)){
					// raw data info
					$raw_data = array();
					$raw_data['store_code'] = $config['gpm_customer_name']."-".$b['code'];
					$raw_data['date'] = $dd_dmy;
					$raw_data['hr'] = date("h", strtotime($r['end_time']));
					$raw_data['min'] = date("i", strtotime($r['end_time']));
					$raw_data['sec'] = date("s", strtotime($r['end_time']));
					$raw_data['am_pm'] = date("A", strtotime($r['end_time']));
					$raw_data['24hr'] = date("H", strtotime($r['end_time']));
					$raw_data['tran_no'] = date("dmy", strtotime($dd)).sprintf("%06s", $r['receipt_no']);
					$raw_data['mcode'] = $r['mcode'];
					$raw_data['item_desc'] = $this->gpm_sku_list[$r['gpm_sku_id']]['description'];
					$raw_data['cat2_desc'] = $this->gpm_sku_list[$r['gpm_sku_id']]['cat2_desc'];
					$raw_data['cat3_desc'] = $this->gpm_sku_list[$r['gpm_sku_id']]['cat3_desc'];
					$raw_data['qty'] = $r['qty'];
					$raw_data['unit_price'] = round(($r['price']-$r['discount'])/$r['qty'],3);
					$raw_data['total_price'] = round($r['price']-$r['discount'],3);
					$raw_data['cashier_id'] = $r['cashier_id'];
					
					if(!in_array($r['sku_item_id'], $sid_list))	$sid_list[] = $r['sku_item_id'];
					
					// insert sales by hour
					$upd_hr['hr'] = $r['hr'];
					$upd_hr['sku_id'] = $r['gpm_sku_id'];
					$upd_hr['qty'] = $r['qty'];
					$upd_hr['amt'] = round($r['price']-$r['discount'],5);
					$upd_hr['discount'] = round($r['discount'], 5);
			
					$con_gpm->sql_query("insert into $tbl_hr ".mysql_insert_by_field($upd_hr)." on duplicate key update
					qty=qty+".$upd_hr['qty'].",
					amt=amt+".$upd_hr['amt'].",
			        discount=discount+".$upd_hr['discount']);
			        
			        // insert sales by race
			        $upd_rc['race'] = $r['race'];
					$upd_rc['sku_id'] = $r['gpm_sku_id'];
					$upd_rc['qty'] = $r['qty'];
					$upd_rc['amt'] = round($r['price']-$r['discount'],5);
					$upd_rc['discount'] = round($r['discount'], 5);
					
					$con_gpm->sql_query("insert into $tbl_race ".mysql_insert_by_field($upd_rc)." on duplicate key update
					qty=qty+".$upd_rc['qty'].",
					amt=amt+".$upd_rc['amt'].",
			        discount=discount+".$upd_rc['discount']);
			        
			        // insert sales by item
					$upd_item['sku_id'] = $r['gpm_sku_id'];
					$upd_item['qty'] = $r['qty'];
					$upd_item['amt'] = round($r['price']-$r['discount'],5);
					$upd_item['discount'] = round($r['discount'], 5);
					
					$con_gpm->sql_query("insert into $tbl_item ".mysql_insert_by_field($upd_item)." on duplicate key update
					qty=qty+".$upd_item['qty'].",
					amt=amt+".$upd_item['amt'].",
			        discount=discount+".$upd_item['discount']);
			        
			        $this->write_pos_raw_data($raw_data);
				}
				$con->sql_freeresult($q1);
				
				fclose($this->fp_pos); // close file pointer
				
				// upload to gpm
				$tmp = array();
				$tmp['branch_id'] = $gpm_bid;
				$tmp['date'] = $dd;
				$tmp['raw_info'] = file_get_contents($this->tmp_raw_data_file);	// get file string
				$tmp['added'] = 'CURRENT_TIMESTAMP';
				
				if($tmp['raw_info']){
					$con_gpm->sql_query("replace into  customer_branch_pos_raw_data_info ".mysql_insert_by_field($tmp));
				}else{
					$con_gpm->sql_query("delete from customer_branch_pos_raw_data_info where branch_id=$gpm_bid and date=".ms($dd));
				}
				
				
				if($dd != $date){
					$con->sql_query("update pos_finalized set gpm_synced=1 where branch_id=$bid and finalized=1 and date=".ms($dd));
				}
				
				if($pos_max_time[$dd]){
					$ps = array();
					$ps['branch_id'] = $bid;
					$ps['date'] = $dd;
					$ps['last_sync'] = $pos_max_time[$dd];
					$con->sql_query("replace into tmp_gpm_pos_sync_info ".mysql_insert_by_field($ps));
				}
				
				
				// check whether need update trade offer
				if($sid_list){
					// check whether today sales got related to any trade offer which has calculate done
					$q_offer = $con->sql_query("select bto.* 
	from gpm_broadcast_trade_offer bto
	join gpm_broadcast_trade_offer_items btoi on btoi.gpm_trade_offer_id=bto.id
	where ".ms($dd)." between bto.date_from and bto.date_to and btoi.sku_item_id in (".join(',', $sid_list).") and bto.active=1 and bto.status=1");
					while($r = $con->sql_fetchassoc($q_offer)){
						$r['allowed_branch'] = unserialize($r['allowed_branch']);
						if(!$r['allowed_branch'][$bid])	continue;	// this trade offer does not related to this branch
						
						// remove calculate branch
						if(!$r['calculate_branch'])	$r['calculate_branch'] = array();
						else	$r['calculate_branch'] = unserialize($r['calculate_branch']);
						
						if(isset($r['calculate_branch'][$bid]))	unset($r['calculate_branch'][$bid]);
						
						$upd = array();
						$upd['calculate_done'] = 0;
						$upd['calculate_branch'] = serialize($r['calculate_branch']);
						$con->sql_query("update gpm_broadcast_trade_offer set ".mysql_update_by_field($upd)." where id=".$r['id']);
					}
					$con->sql_freeresult($q_offer);		
				}
			}
			
			// sync stock
			print "Update Stock.\n";
			$qs = $con->sql_query("select si.id as sid, s2.gpm_sku_id, sic.qty as stock, sic.changed as changed, sic.last_update
	from tmp_gpm_sku_link s2
	join sku_items si on si.mcode=s2.mcode
	left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id");
			while($r = $con->sql_fetchassoc($qs)){
				$upd_stock = array();
				$upd_stock['customer_id'] = $customer_id;
				$upd_stock['branch_id'] = $bid;
				$upd_stock['sku_id'] = mi($r['gpm_sku_id']);
				$upd_stock['stock'] = mf($r['stock']);
				$upd_stock['last_update'] = $r['last_update'];
				$upd_stock['changed'] = $r['changed'];
				
				$con_gpm->sql_query("replace into sku_inventory ".mysql_insert_by_field($upd_stock));
			}
			$con->sql_freeresult($qs);
			print "Stock update done.\n";
			
			// sync trade offer summary
			print "Update Trade Offer Summary.\n";
			$q_offer = $con->sql_query("select bto.* 
	from gpm_broadcast_trade_offer bto
	where bto.active=1 and bto.status=1 and calculate_done=0");
			while($r = $con->sql_fetchassoc($q_offer)){
				$r['allowed_branch'] = unserialize($r['allowed_branch']);
				$r['calculate_branch'] = unserialize($r['calculate_branch']);
				
				if(!$r['allowed_branch'][$bid])	continue;	// no relate this branch
				if($r['calculate_branch'][$bid])	continue;	// this branch alrdy calculated
				
				$this->update_trade_offer_summary($bid, $r['id'], array('trade_offer'=>$r));
			}
			$con->sql_freeresult($q_offer);
	
		 	print "Update Trade Offer Summary Done.\n";	
		 	
		}	// end looping branch
		
		print "\nDone.\n";
	}

	function sync_counter_status(){
		global $con, $con_gpm, $customer_id, $branch_info_list;
		
		print "Sync COUNTER STATUS. . .\n";
		
		// get branch info list
		$this->load_branch_info_list();
		
		if(!$branch_info_list){
			print "No branch to sync.\n";
			return;
		}
		
		foreach($branch_info_list as $b){	// loop for each branch
			$bid = mi($b['id']);
			print "Sync Branch ID#$bid, Code: ".$b['code']."\n";
			
			// get gpm branch id
			if(!$gpm_bid = $this->get_gpm_branch_id($bid)){
				print "GPM Branch ID Not Found, skipped.\n";
				continue;
			}
			print "GPM Branch ID#$gpm_bid\n";
			
			$q_cs = $con->sql_query("select cs.branch_id,cs.id, cs.network_name, cst.status, cst.lastping
	from counter_settings cs
	left join counter_status cst on cst.branch_id=cs.branch_id and cst.id=cs.id
	where cs.branch_id=$bid and cs.active=1 
	order by cs.id");
			while($counter = $con->sql_fetchassoc($q_cs)){
				$upd = array();
				$upd['branch_id'] = $gpm_bid;
				$upd['customer_counter_id'] = $counter['id'];
				$upd['network_name'] = $counter['network_name'];
				$upd['status'] = $counter['status'];
				$upd['lastping'] = $counter['lastping'];
				
				$con_gpm->sql_query("insert into customer_branch_counter ".mysql_insert_by_field($upd)." on duplicate key update
					network_name=".ms($upd['network_name']).",
					status=".ms($upd['status']).",
					lastping=".ms($upd['lastping']));
				
			}
			$con->sql_freeresult($q_cs);
		}
		print "Done.\n";
	}
		
	function sync_msg($msg_id){
		global $con, $con_gpm, $customer_id;
		
		if(!$msg_id)	return false;
		
		$con_gpm->sql_query("select * from broadcast_msg where id=$msg_id");
		$msg = $con_gpm->sql_fetchassoc();
		$con_gpm->sql_freeresult();
		
		if(!$msg)	return false;
		
		$msg['recipient_customer'] = unserialize($msg['recipient_customer']);
		$msg['synced_recipient'] = unserialize($msg['synced_recipient']);
		
		if(!$msg['recipient_customer'][$customer_id]['branch'])	return;	// not for this customer
		
		$upd = array();
		$upd['id'] = $msg_id;
		$upd['msg'] = $msg['msg'];
		$upd['expire_timestamp'] = $msg['expire_timestamp'];
		$upd['active'] = $msg['active'];
		$upd['added'] = $msg['added'];
		$upd['last_update'] = $msg['last_update'];
		
		$allowed_branch = array();
		
		foreach($msg['recipient_customer'][$customer_id]['branch'] as $gpm_bid => $dummy){
			$bid = mi($this->get_arms_branch_id_from_gpm_bid($gpm_bid));
			if(!$bid)	continue;
			
			$allowed_branch[$bid] = $bid;
		}
		
		$upd['allowed_branch'] = serialize($allowed_branch);
		
		$con->sql_query("replace into gpm_broadcast_msg ".mysql_insert_by_field($upd));
		
		$gpm_upd = array();
		$gpm_upd['synced_recipient'] = $msg['synced_recipient'];
		if(!is_array($gpm_upd['synced_recipient']))	$gpm_upd['synced_recipient'] = array();
		$gpm_upd['synced_recipient'][$customer_id] = 1;
		$gpm_upd['synced_recipient'] = serialize($gpm_upd['synced_recipient']);
		
		$con_gpm->sql_query("update broadcast_msg set ".mysql_update_by_field($gpm_upd)." where id=$msg_id");
		
		return true;
	}
	
	function sync_offer($offer_id){
		global $con, $con_gpm, $customer_id;
		
		if(!$offer_id)	return false;
		
		// get header
		$con_gpm->sql_query("select * from broadcast_trade_offer where id=$offer_id");
		$offer = $con_gpm->sql_fetchassoc();
		$con_gpm->sql_freeresult();
		
		if(!$offer)	return false;
		
		// get items
		$con_gpm->sql_query("select btoi.*, sku.mcode
		from broadcast_trade_offer_items btoi
		left join sku on sku.id=btoi.sku_id
		where btoi.trade_offer_id=$offer_id order by id");
		$item_list = array();
		while($r = $con_gpm->sql_fetchassoc()){
			$item_list[] = $r;
		}
		$con_gpm->sql_freeresult();
		
		$offer['recipient_customer'] = unserialize($offer['recipient_customer']);
		$offer['synced_recipient'] = unserialize($offer['synced_recipient']);
		
		if(!$offer['recipient_customer'][$customer_id]['branch'])	return;	// not for this customer
		
		$upd = array();
		$upd['id'] = $offer_id;
		$upd['title'] = $offer['title'];
		$upd['date_from'] = $offer['date_from'];
		$upd['date_to'] = $offer['date_to'];
		$upd['qualify_qty'] = $offer['qualify_qty'];
		$upd['qualify_offer'] = $offer['qualify_offer'];
		$upd['active'] = $offer['active'];
		$upd['status'] = $offer['status'];
		$upd['added'] = $offer['added'];
		$upd['last_update'] = $offer['last_update'];
		$upd['calculate_done'] = 0;
		$upd['calculate_branch'] = '';
		
		$allowed_branch = array();
		
		foreach($offer['recipient_customer'][$customer_id]['branch'] as $gpm_bid => $dummy){
			$bid = mi($this->get_arms_branch_id_from_gpm_bid($gpm_bid));
			if(!$bid)	continue;
			
			$allowed_branch[$bid] = $bid;
		}
		
		$upd['allowed_branch'] = serialize($allowed_branch);
		
		$con->sql_query("replace into gpm_broadcast_trade_offer ".mysql_insert_by_field($upd));
		
		$first_item_id = 0;
		$failed_item_sync = false;
		if($item_list){
			$upd2 = array();
			$upd2['gpm_trade_offer_id'] = $offer_id;
			
			foreach($item_list as $r){
				$sid = $this->get_arms_sku_item_id_by_mcode($r['mcode']);
				if(!$sid){	// got item cannot sync
					$failed_item_sync = true;
					continue;
				}
				
				$upd2['gpm_sku_id'] = $r['sku_id'];
				$upd2['mcode'] = $r['mcode'];
				$upd2['sku_item_id'] = $sid;
				
				$con->sql_query("insert into gpm_broadcast_trade_offer_items ".mysql_insert_by_field($upd2));
				if(!$first_item_id)	$first_item_id = $con->sql_nextid();
			}
		}
		$con->sql_query("delete from gpm_broadcast_trade_offer_items where gpm_trade_offer_id=$offer_id ".($first_item_id > 0 ? " and id<$first_item_id" : ''));
		
		if(!$failed_item_sync){
			$gpm_upd = array();
			$gpm_upd['synced_recipient'] = $offer['synced_recipient'];
			if(!is_array($gpm_upd['synced_recipient']))	$gpm_upd['synced_recipient'] = array();
			$gpm_upd['synced_recipient'][$customer_id] = 1;
			$gpm_upd['synced_recipient'] = serialize($gpm_upd['synced_recipient']);
			
			$con_gpm->sql_query("update broadcast_trade_offer set ".mysql_update_by_field($gpm_upd)." where id=$offer_id");
			
			return true;
		}else{
			return false;
		}
	}

	function update_trade_offer_summary($bid, $offer_id, $params = array()){
		global $con, $con_gpm, $customer_id;
		
		$bid = mi($bid);
		$offer_id = mi($offer_id);
		if(!$bid || !$offer_id)	return false;
		
		if(!$gpm_bid = $this->get_gpm_branch_id($bid)){
			print "GPM Branch ID Not Found, skipped.\n";
			return false;
		}
		
		$offer = array();
		if($params['trade_offer'])	$offer = $params['trade_offer'];
		
		if(!$offer){
			// manually set trade offer
			$con->sql_query("select bto.* 
	from gpm_broadcast_trade_offer bto
	where id=$offer_id");
			$offer = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$offer)	return false;
			
			$offer['allowed_branch'] = unserialize($offer['allowed_branch']);
			$offer['calculate_branch'] = unserialize($offer['calculate_branch']);
		}
		
		if(!$offer['allowed_branch'][$bid])	return false;	// no relate this branch
		if($offer['calculate_branch'][$bid])	return false;	// this branch alrdy calculated
		
		print "Calculating Branch ID#$bid, ID#$offer_id\n";
		
		// select sales
		$q1 = $con->sql_query("select pos.date, hour(pos.end_time) as hr, pi.sku_item_id,s2.gpm_sku_id, sum(pi.qty) as qty
	from pos_items pi
	join pos on pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id and pos.id=pi.pos_id
	join sku_items si on si.id=pi.sku_item_id
	join tmp_gpm_sku_link s2 on s2.mcode=si.mcode
	join gpm_broadcast_trade_offer_items btoi on btoi.sku_item_id=pi.sku_item_id 
	where pos.branch_id=$bid and pos.date between ".ms($offer['date_from'])." and ".ms($offer['date_to'])." and pos.cancel_status=0 and btoi.gpm_trade_offer_id=$offer_id
	group by date,hr,sku_item_id
	order by date,hr,si.mcode");
		
		// delete current summary item
		$con->sql_query("delete from gpm_broadcast_trade_offer_summary_items where branch_id=$bid and gpm_trade_offer_id=$offer_id");
		
		// delete from gpm
		$con_gpm->sql_query("delete from broadcast_trade_offer_summary_items where branch_id=$gpm_bid and trade_offer_id=$offer_id");
		
		$total_qualify_qty = 0;
		
		while($r = $con->sql_fetchassoc($q1)){
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['gpm_trade_offer_id'] = $offer_id;
			$upd['date'] = $r['date'];
			$upd['hr'] = $r['hr'];
			$upd['sku_item_id'] = $r['sku_item_id'];
			$upd['qty'] = $r['qty'];
				
			$total_qualify_qty += $r['qty'];
			
			$con->sql_query("replace into gpm_broadcast_trade_offer_summary_items ".mysql_insert_by_field($upd));
			
			// gpm
			$gpm_upd = array();
			$gpm_upd['branch_id'] = $gpm_bid;
			$gpm_upd['trade_offer_id'] = $offer_id;
			$gpm_upd['date'] = $r['date'];
			$gpm_upd['hr'] = $r['hr'];
			$gpm_upd['sku_id'] = $r['gpm_sku_id'];
			$gpm_upd['qty'] = $r['qty'];
			
			$con_gpm->sql_query("replace into broadcast_trade_offer_summary_items ".mysql_insert_by_field($gpm_upd));
			
		}
		$con->sql_freeresult($q1);
		
		$summary = array();
		$summary['branch_id'] = $bid;
		$summary['gpm_trade_offer_id'] = $offer_id;
		$summary['total_qualify_qty'] = $total_qualify_qty;
		$summary['total_qualify_counter'] = floor($total_qualify_qty/$offer['qualify_qty']);
		$summary['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("replace into gpm_broadcast_trade_offer_summary ".mysql_insert_by_field($summary));
		
		// sync to gpm
		$gpm_summary = array();
		$gpm_summary['branch_id'] = $gpm_bid;
		$gpm_summary['trade_offer_id'] = $offer_id;
		$gpm_summary['total_qualify_qty'] = $total_qualify_qty;
		$gpm_summary['total_qualify_counter'] = floor($total_qualify_qty/$offer['qualify_qty']);
		$gpm_summary['last_update'] = 'CURRENT_TIMESTAMP';
		$con_gpm->sql_query("replace into broadcast_trade_offer_summary ".mysql_insert_by_field($gpm_summary));
		
		// get again trade offer
		$con->sql_query("select bto.* 
	from gpm_broadcast_trade_offer bto
	where id=$offer_id");
		$new_offer = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$upd2 = array();	
		$upd2['calculate_branch'] = unserialize($new_offer['calculate_branch']);
		if(!$upd2['calculate_branch'])	$upd2['calculate_branch'] = array();
		
		$upd2['calculate_branch'][$bid] = $bid;
		$upd2['last_update'] = 'CURRENT_TIMESTAMP';
		
		if(count($upd2['calculate_branch']) >= count($offer['allowed_branch'])){
			$upd2['calculate_done'] = 1;
		}
		$upd2['calculate_branch'] = serialize($upd2['calculate_branch']);
		
		
		
		$con->sql_query("update gpm_broadcast_trade_offer set ".mysql_update_by_field($upd2)." where id=$offer_id");
		
		return true;
	}
	
	private function write_pos_raw_data($data){
		if(!$this->fp_pos)	die("Invalid File Pointer.\n");
		
		$fields = array('store_code', 'date', 'hr', 'min', 'sec', 'am_pm', '24hr', 'tran_no', 'mcode', 'item_desc', 'cat2_desc', 'cat3_desc', 'qty', 'unit_price', 'total_price', 'cashier_id');
		$arr = array();
		foreach($fields as $f){
			$arr[] = $data[$f];
		}
		
		fputcsv($this->fp_pos, $arr);
	}
}
















?>
