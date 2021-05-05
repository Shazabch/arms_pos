<?php
/*
2/9/2018 12:32 PM Andy
- Fixed if pos_user_log got same timestamp in multiple record, it will cause record being replaced in the server.

2/15/2018 10:33 AM Andy
- Enhanced to only sync data from 2018-01-01.

7/19/2018 4:13 PM Andy
- Enhanced to move pos_error, pos_user_log, pos_transaction_audit_log, pos_transaction_ejournal and pos_transaction_clocking_log to no need check finalise when sync.

7/25/2018 4:31 PM Justin
- Enhanced to have sync pos_counter_collection_tracking from sync to main server.

8/1/2018 3:38 PM Andy
- Enhanced to default always sync last 3 months data only.

10/3/2018 12:34 PM Andy
- Fixed if deposit received and deposit claim sync at the same time, the sync will show error.

3/21/2019 10:33 AM Justin
- Enhanced to validate GUID when syncing pos table, stop and show error message when GUID is different with similar receipt_no.

7/17/2019 3:53 PM Justin
- Enhanced to sync=1 for pos_deposit_status and pos_deposit_status_history.

10/22/2019 5:22 PM Andy
- Enhanced to have beginTransaction() and commit() when sync pos.
- Enhanced to have auto clear old data.

11/6/2019 3:07 PM Andy
- Removed beginTransaction() and commit() for clear_old_data.
- Moved beginTransaction() and commit() on sync sales part to smaller part.
- Added beginTransaction() and commit() for $hqcon.

12/17/2019 11:33 AM Justin
- Enhanced to new function "upload_pos_counter_collection_configuration" to sync up the counter configuration info.

6/12/2020 1:26 PM Andy
- Added to call rollback if sync pos got error.

1/8/2021 5:16 PM Shane
- Added function to sync pos_day_start and pos_day_end.
*/
define('TERMINAL',1);
define('DISP_ERR',1);

ini_set('memory_limit', '512M');
print "Current Time: ".date("Y-m-d H:i:s")."\n";
print "Starting memory: ".memory_get_usage()."\n";

require_once("pdo_db.php");
require_once("function.php");
require_once("config.php");

@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
if (count($exec)>1)
{
	print date("[H:i:s m.d.y]")." Another process is already running\n";
	print_r($exec);
	exit;
}

// init object
$SYNC_SERVER_U = new SYNC_SERVER_U();

// connect to loca DB
$SYNC_SERVER_U->connect_sync_server_db();

// run maintenance to update local db
require_once("maintenance.php");
print "Maintenance Version: ".$maintenance->ver."\n";

// connect to HQ server
$SYNC_SERVER_U->connect_hq_db();

// check params to perform some special actions
$arg = $_SERVER['argv'];
array_shift($arg);

while($a = array_shift($arg)){
	if(preg_match('/^-min_date=/', $a)){	// date
		$tmp = date("Y-m-d", strtotime(str_replace('-min_date=', '', $a)));
		if(!$tmp)	die("Invalid Date.\n");
		if(date("Y", strtotime($tmp))<2018)	die("Date $tmp is invalid. Date must start at 2018-01-01.\n");
		$SYNC_SERVER_U->min_date = $tmp;
	}
	else{
		die("Unknown option $a\n");
	}
}

// Clear Old Data
$SYNC_SERVER_U->clear_old_data();

// no special action, perform regular sync
$SYNC_SERVER_U->start_sync();


class SYNC_SERVER_U {
	var $counter_errors_msg = array();
	var $sync_server_errors_msg = array();
	var $program_errors_msg = array();
	var $table_cols_definition = array();
	var $min_date = '';
	var $clear_before_date = '';
	
	function __construct(){
		// default 3 month
		$this->min_date = date("Y-m-d", strtotime("-3 month"));
		$this->clear_before_date = date("Y-m-d", strtotime("-6 month"));
	}
	
	private function connect_db($server, $u, $p){
		$conn = new pdo_db($server, $u, $p);
		if(!$conn->resource_obj)
		{
			print date("[H:i:s m.d.y] ");
			die("Error: Could not connect to database $db@$server\n");
			return false;
		}
		return $conn;
	}
	
	function connect_sync_server_db(){
		global $con, $db_default_connection;
		
		print "Connecting to Sync Server DB at ".$db_default_connection[0].". . .";
		if(strpos($db_default_connection[0], 'unix_socket') !== false){
			$con=$this->connect_db("mysql:dbname=sync_server;".$db_default_connection[0], $db_default_connection[1], $db_default_connection[2]);	// use by soc2
		}else{
			$con=$this->connect_db("mysql:dbname=sync_server;host=$db_default_connection[0]", $db_default_connection[1], $db_default_connection[2]);	// normal use
		}
		print "Connected.\n";
	}
	
	function connect_hq_db(){
		global $hqcon, $hq_db_default_connection;
		
		print "Connecting to HQ Server DB at ".$hq_db_default_connection[0].". . .";
		$hqcon = $this->connect_db("mysql:dbname=$hq_db_default_connection[3];host=$hq_db_default_connection[0]", $hq_db_default_connection[1],$hq_db_default_connection[2]);
		print "Connected.\n";
	}
	
	function connect_down_sync_db(){
		global $con2;
		
		include("../config.php");
		print "Connecting to Down Sync DB at ".$db_default_connection[0].". . .";
		$con2 = $this->connect_db("mysql:dbname=$db_default_connection[3];host=$db_default_connection[0]", $db_default_connection[1], $db_default_connection[2]);
		print "Connected.\n";
	}
	
	/*function _die_($msg, $die=true, $params)
	{
		$this->got_sync_error = true;
		file_put_contents("armspos.err", "Timestamp: ".date("Y-m-d H:i:s")."|".$msg);
		$this->erromessage[$params['date']][$params['branch_id']][$params['counter_id']][] = $msg;
		if ($die) 
			die($msg);
		else
			print "$msg\n";
	}
	function update_error_message()
	{
		global $hqcon;
		print_r($this->erromessage);
		if($this->erromessage)
		{
			foreach($this->erromessage as $date=>$val)
			{
				foreach($val as $branchID=>$val2)
				{
					foreach($val2 as $counterID=>$v)
					{
						if($counterID==0)
						{
							$str = serialize($v);
							$hqcon->query("replace into pos_transaction_sync_server_tracking (branch_id,date,error_message,lastupdate) values (".ms($branchID).",".ms($date).",".ms($str).",CURRENT_TIMESTAMP)");
						}
						else{
							$str = serialize($v);
							$hqcon->query("replace into pos_transaction_sync_server_counter_tracking (branch_id,counter_id,date,error_message,lastupdate) values (".ms($branchID).",".ms($counterID).",".ms($date).",".ms($str).",CURRENT_TIMESTAMP)");
						}
					}
				}
			}
			$this->erromessage = array();
		}
	}*/
	
	function store_counter_error($bid, $date, $counter_id, $msg, $display_err=false){
		$bid = mi($bid);
		$counter_id = mi($counter_id);	// Sync Server POS ID
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		//file_put_contents("counter_error.err", "Timestamp: ".date("Y-m-d H:i:s")."|".$msg);
		if(!$this->counter_errors_msg)	$this->counter_errors_msg = array();
		if(!isset($this->counter_errors_msg[$bid][$counter_id][$date]))	$this->counter_errors_msg[$bid][$counter_id][$date] = array();
		if(!in_array($msg, $this->counter_errors_msg[$bid][$counter_id][$date])){
			$this->counter_errors_msg[$bid][$counter_id][$date][] = $msg;
		}
		if($display_err)	print $msg."\n";
	}
	
	function upload_counter_error($bid, $date, $counter_id){
		global $hqcon;
		
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		if(isset($this->counter_errors_msg[$bid][$counter_id][$date])){
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['counter_id'] = $counter_id;
			$upd['date'] = $date;
			$upd['error_message'] = serialize($this->counter_errors_msg[$bid][$counter_id][$date]);
			$upd['lastupdate'] = 'CURRENT_TIMESTAMP';
			
			$hqcon->exec("insert into pos_transaction_sync_server_counter_tracking ".mysql_insert_by_field($upd)." on duplicate key update
			error_message=".ms($upd['error_message']));
			unset($this->counter_errors_msg[$bid][$counter_id][$date]);
		}else{
			$hqcon->exec("delete from pos_transaction_sync_server_counter_tracking where branch_id=$bid and counter_id=$counter_id and date=".ms($date));
		}
	}
	
	private function got_counter_error($bid, $date, $counter_id){
		$bid = mi($bid);
		$counter_id = mi($counter_id);	// Sync Server POS ID
		
		if(!$bid){
			print "Invalid Branch.\n";
			return true;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return true;
		}
		if(!$date){
			print "Invalid Date.\n";
			return true;
		}
		
		return isset($this->counter_errors_msg[$bid][$counter_id][$date]) ? true : false;
	}
	
	function store_sync_server_error($bid, $date, $msg, $display_err=false){
		$bid = mi($bid);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		//file_put_contents("sync_server_error.err", "Timestamp: ".date("Y-m-d H:i:s")."|".$msg);
		if(!$this->sync_server_errors_msg)	$this->sync_server_errors_msg = array();
		if(!isset($this->sync_server_errors_msg[$bid][$date]))	$this->sync_server_errors_msg[$bid][$date] = array();
		if(!in_array($msg, $this->sync_server_errors_msg[$bid][$date])){
			$this->sync_server_errors_msg[$bid][$date][] = $msg;
		}		
		if($display_err)	print $msg."\n";
	}
	
	function upload_sync_server_error($bid, $date){
		global $hqcon;
		
		$bid = mi($bid);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		if(isset($this->sync_server_errors_msg[$bid][$date])){
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['date'] = $date;
			$upd['error_message'] = serialize($this->sync_server_errors_msg[$bid][$date]);
			$upd['lastupdate'] = 'CURRENT_TIMESTAMP';
			
			$hqcon->exec("insert into pos_transaction_sync_server_tracking ".mysql_insert_by_field($upd)." on duplicate key update
			error_message=".ms($upd['error_message']));
			unset($this->sync_server_errors_msg[$bid][$date]);
		}else{
			$hqcon->exec("delete from pos_transaction_sync_server_tracking where branch_id=$bid and date=".ms($date));
		}
	}
	
	function upload_sync_server_error_using_append(){
		global $hqcon;
		
		if(isset($this->sync_server_errors_msg)){
			foreach($this->sync_server_errors_msg as $bid => $date_list){
				foreach($date_list as $date => $r){
					// get from hq server
					$q1 = $hqcon->query("select * from pos_transaction_sync_server_tracking where branch_id=".mi($bid)." and date=".ms($date));
					$current_data = $hqcon->sql_fetchassoc($q1);
					$hqcon->sql_freeresult($q1);
					
					if($current_data){	// got data
						$current_data['error_message'] = unserialize($current_data['error_message']);
						$upd = array();
						$upd['error_message'] = serialize(array_merge($current_data['error_message'], $r));
						$hqcon->exec("update pos_transaction_sync_server_tracking set ".mysql_update_by_field($upd)." where branch_id=$bid and date=".ms($date));
					}else{	// no data
						$upd = array();
						$upd['branch_id'] = $bid;
						$upd['date'] = $date;
						$upd['error_message'] = serialize($this->sync_server_errors_msg[$bid][$date]);
						$upd['lastupdate'] = 'CURRENT_TIMESTAMP';
						$hqcon->exec("insert into pos_transaction_sync_server_tracking ".mysql_insert_by_field($upd)." on duplicate key update
						error_message=".ms($upd['error_message']));
						
					}
				}
			}
			unset($this->sync_server_errors_msg);
		}
	}
	
	private function got_sync_server_error($bid, $date){
		$bid = mi($bid);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return true;
		}
		if(!$date){
			print "Invalid Date.\n";
			return true;
		}
		
		return isset($this->sync_server_errors_msg[$bid][$date]) ? true : false;
	}
	
	/*private function store_program_error($function_name, $msg){
		print "$function_name = $msg";
		
		if(!isset($this->program_errors_msg))	$this->program_errors_msg = array();
		if(!isset($this->program_errors_msg[$function_name]))	$this->program_errors_msg[$function_name] = array();
		if(!in_array($this->program_errors_msg[$function_name], $msg))	$this->program_errors_msg[$function_name][] = $msg;
	}
	
	private function got_program_errors(){
		return isset($this->program_errors_msg) && $this->program_errors_msg ? true : false;
	}
	
	private function upload_program_errors(){
		
	}*/
	
	function start_sync(){
		global $con;
		
		$start_time = microtime(true);
		print "Start Sync Time: ".date("Y-m-d H:i:s", $start_time)."\n";
		print "Using Memory: ".memory_get_usage()."\n";
		print "Min Date: ".$this->min_date."\n";
		
		// sync global data
		print "\nCollecting Global Data...";
		
		$sql = "select distinct branch_id, date(`date`) as date from membership_points where sync=0 and date>=".ms($this->min_date)." union
				select distinct branch_id, date(`lastupdate`) as date from sku_items_temp_price where sync=0 and lastupdate>=".ms($this->min_date)." union
				select distinct branch_id, added_date as date from sku_items_temp_price_history where sync=0 and added_date>=".ms($this->min_date)."
				order by branch_id, date";
		$q_global = $con->query($sql);
		print "\n";
		$num_of_days = mi($con->sql_numrows($q_global));
		
		if($num_of_days>0){
			print "$num_of_days days found.\n";
			while($r = $con->sql_fetchassoc($q_global))
			{
				$this->sync_global_data($r['branch_id'], $r['date']);
			}
		}else{
			print "Nothing to Sync.";
		}
		$con->sql_freeresult($q_global);
		unset($q_global);
		print "\n";
		
		// sync pos
		print "Collecting POS Branch & Date list...";
		$sql = "select distinct branch_id, date from pos where sync=0 and transaction_sync = 1 and date>=".ms($this->min_date)." union
				select distinct branch_id, date from pos_drawer where sync = 0 and date>=".ms($this->min_date)." union
				select distinct branch_id, date from pos_cash_history where sync = 0 and date>=".ms($this->min_date)." union
				select distinct branch_id, date from pos_cash_domination where sync = 0 and date>=".ms($this->min_date)." union
				select distinct branch_id, date from pos_error where sync = 0 and date>=".ms($this->min_date)." union
				select distinct branch_id, date from pos_transaction_audit_log where sync = 0 and date>=".ms($this->min_date)." union
				select distinct branch_id, date from pos_transaction_ejournal where sync = 0 and date>=".ms($this->min_date)." union
				select distinct branch_id, date(counter_date) as date from pos_transaction_clocking_log where sync = 0 and counter_date>=".ms($this->min_date)." union
				select distinct branch_id, date from pos_user_log where sync = 0 and date>=".ms($this->min_date)." union
				select distinct branch_id, date from pos_counter_collection_tracking where sync=0 and date>=".ms($this->min_date)." union
				select distinct branch_id, date from pos_day_start where sync = 0 and date>=".ms($this->min_date)." union
				select distinct branch_id, date from pos_day_end where sync = 0 and date>=".ms($this->min_date)."
				order by branch_id, date";
		$ret = $con->query($sql);
		print "\n";
		$num_of_days = mi($con->sql_numrows($ret));
		if($num_of_days>0){
			print "$num_of_days days found.\n";
			while($r = $con->sql_fetchassoc($ret))
			{
				$this->sync_by_branch_and_date($r['branch_id'], $r['date']);
			}
		}else{
			print "Nothing to Sync.\n";
		}
		$con->sql_freeresult($ret);
		unset($ret);
		
		$this->delete_invalid_sku();
		
		// sync up counter configuration
		$this->upload_pos_counter_collection_configuration();
		
		$end_time = microtime(true);
		print "\nFinish Time: ".date("Y-m-d H:i:s", $end_time)."\n";
		print "Time Used: ".round($end_time-$start_time, 4)." seconds.\n";
		print "Ending memory: ".memory_get_usage()."\n";
		print "Finish Sync.\n";
	}
	
	private function sync_global_data($bid, $date){
		global $con, $hqcon;
		
		$bid = mi($bid);
		print "\nBranch ID: $bid, Date: $date\n";
		
		if(!$bid){
			print $msg = "Invalid Branch.\n";
			//$this->store_program_error(__FUNCTION__, $msg);
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		///////////// sync those data without counter and no need to check finalise /////////////////
		$this->sync_membership_points($bid, $date);
		$this->sync_sku_items_temp_price($bid, $date);
		$this->sync_sku_items_temp_price_history($bid, $date);
		
		// sync up server error
		$this->upload_sync_server_error($bid, $date);
	}
	
	private function sync_by_branch_and_date($bid, $date){
		global $con, $hqcon;
		
		$bid = mi($bid);
		print "\nBranch ID: $bid, Date: $date\n";
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		// Begin
		//$con->beginTransaction();
		
		$branch_counter_id_list = array();
		////////// Sync those data with counter with no need check finalise /////////////////
		$this->sync_data_without_check_finalise($bid, $date, $branch_counter_id_list);
		
		///////////// sync those data with counter and need check finalise /////////////////
		$this->sync_data_with_check_finalise($bid, $date, $branch_counter_id_list);
		
		
		// Upload Error
		if($branch_counter_id_list){
			foreach($branch_counter_id_list as $counter_id){
				// upload error message
				$this->upload_counter_error($bid, $date, $counter_id);
			}
		}
		
		// Commit
		//$con->commit();
		
		print "Done.\n";
	}
	
	function sync_under_pos_tables($table, $pos, $ss_pos_id, $server_pos_id)
	{
		global $con, $hqcon;
		
		$bid = mi($pos['branch_id']);
		$counter_id = mi($pos['counter_id']);
		$date = $pos['date'];
		$ss_pos_id = mi($ss_pos_id);
		$server_pos_id = mi($server_pos_id);
		
		if(!$table){
			print "No Table.\n";
			return;
		}
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$ss_pos_id){
			print "Invalid Sync Server POS ID.\n";
			return;
		}
		if(!$server_pos_id){
			print "Invalid Server POS ID.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		// dont sync if this counter already have error
		if($this->got_counter_error($bid, $date, $counter_id))	return;
		
		// select all data
		$q1 = $con->query("select * from $table where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and pos_id=$ss_pos_id");
		$total_rows = $con->sql_numrows($q1);
		
		// got data
		if ($total_rows>0){
			// get column
			$cols = $this->get_table_cols($table);
			
			while($r = $con->sql_fetchassoc($q1)){
				// replace pos id with server pos id
				$r['pos_id'] = $server_pos_id;
				
				if($table == 'pos_goods_return'){
					// process pos_goods_return data
					$success = $this->pos_goods_return_handler($bid, $date, $counter_id, $ss_pos_id, $server_pos_id, $pos, $r);
					if(!$success)	break;
				}else{
					// normal process
					// insert into table (pos_items, pos_payment, etc)
					$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $cols));
					if ($num_rows<=0)	{
						// show error
						print $hqcon->exec_sql_error();
						$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert $table: ".$date." Branch ID#".$bid." Counter ID#".$counter_id." Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no'].", item ID#".$r['id'], true);
						break;
					}
					
					if($table == 'pos_items'){
						$success = $this->pos_items_handler($bid, $date, $counter_id, $ss_pos_id, $server_pos_id, $pos, $r);
						if(!$success)	break;
					}
				}				
			}
			
			// verify no. of records written
			$q2 = $hqcon->query("select count(*) as total from $table where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and pos_id=$server_pos_id");
			$tmp = $hqcon->sql_fetchassoc($q2);
			$hqcon->sql_freeresult($q2);

			// written rows not match
			if ($tmp['total'] != $total_rows) {
				$this->store_counter_error($bid, $date, $counter_id, "Error $table: ".$date.": Branch ID#".$bid." Counter ID#".$counter_id."  Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no'].", Written rows not same.", true);
			}
		}
		$con->sql_freeresult($q1);
	}
	
	private function membership_promotion_data($pos, $ss_pos_id, $server_pos_id){
		global $con, $hqcon;
		
		$bid = mi($pos['branch_id']);
		$counter_id = mi($pos['counter_id']);
		$date = $pos['date'];
		$ss_pos_id = mi($ss_pos_id);
		$server_pos_id = mi($server_pos_id);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$ss_pos_id){
			print "Invalid Sync Server POS ID.\n";
			return;
		}
		if(!$server_pos_id){
			print "Invalid Server POS ID.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		// dont sync if this counter already have error
		if($this->got_counter_error($bid, $date, $counter_id))	return;
		
		////////// membership_promotion_items /////////////
		// select all data
		$q1 = $con->query("select * from membership_promotion_items where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and pos_id=$ss_pos_id order by id");
		$total_rows = $con->sql_numrows($q1);
		// got data
		if ($total_rows>0){			
			// get id list from server
			$q2 = $hqcon->query("select id from membership_promotion_items where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and pos_id=$server_pos_id order by id");
			$id_list = array();
			while($r = $hqcon->sql_fetchassoc($q2)){
				$id_list[] = mi($r['id']);
			}
			$hqcon->sql_freeresult($q2);
				
			$row_num = 0;
			while($r = $con->sql_fetchassoc($q1)){
				unset($r['sync']);
				// replace pos id with server pos id
				$r['pos_id'] = $server_pos_id;
				
				$use_id = 0; 
				if(isset($id_list[$row_num]))	$use_id = $id_list[$row_num];
				
				// server have no data for this pos yet
				if(!$use_id){	
					// check whether id has been occupied
					$q_dup = $hqcon->query("select id from membership_promotion_items where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and id=".mi($r['id']));
					$tmp = $hqcon->sql_fetchassoc($q_dup);
					$hqcon->sql_freeresult($q_dup);
					
					if($tmp){	// server already have this row
						unset($r['id']);
					}
				}else{
					// use this id
					$r['id'] = $use_id;
				}
				
				$insert_type = $r['id'] ? "replace" : "insert";
				$num_rows = $hqcon->exec("$insert_type into membership_promotion_items ".mysql_insert_by_field($r));
				if ($num_rows<=0)	{
					// show error
					print $hqcon->exec_sql_error();
					$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert membership_promotion_items: ".$date." Branch ID#".$bid." Counter ID#".$counter_id." Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no'].", item ID#".$r['id'], true);
					break;
				}

				$row_num++;
			}
			
			// verify no. of records written
			$q2 = $hqcon->query("select count(*) as total from membership_promotion_items where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and pos_id=$server_pos_id");
			$tmp = $hqcon->sql_fetchassoc($q2);
			$hqcon->sql_freeresult($q2);

			// written rows not match
			if ($tmp['total'] != $total_rows) {
				$this->store_counter_error($bid, $date, $counter_id, "Error membership_promotion_items: ".$date.": Branch ID#".$bid." Counter ID#".$counter_id."  Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no'].", Written rows not same.", true);
			}
		}
		$con->sql_freeresult($q1);		
	}
	
	private function pos_goods_return_handler($bid, $date, $counter_id, $ss_pos_id, $server_pos_id, $pos, $r){
		global $con, $hqcon;
		
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		$ss_pos_id = mi($ss_pos_id);
		$server_pos_id = mi($server_pos_id);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		if(!$server_pos_id){
			print "Invalid Server POS ID.\n";
			return;
		}
		
		$table = 'pos_goods_return';
		
		
		$return_bid = mi($r['return_branch_id']);
		if(!$return_bid){
			$r['return_branch_id'] = $return_bid = $bid;
		}	
		
		$return_pos_id = mi($this->get_pos_id_from_server($return_bid, $r['return_counter_id'], $r['return_date'], $r['return_pos_id'], $r['return_receipt_no']));
		if(!$return_pos_id){
			$this->store_counter_error($bid, $date, $counter_id, "Error $table: ".$date.": Branch ID#".$bid." Counter ID#".$counter_id."  Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no'].", cannot found return_pos_id at server.", true);
			return;
		}
		
		// get column
		$cols = $this->get_table_cols($table);
		
		// replace with server return_pos_id
		$r['return_pos_id'] = $return_pos_id;
		
		// insert into table
		$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $cols));
		if ($num_rows<=0){
			// show error
			print $hqcon->exec_sql_error();
			$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert $table: ".$date." Branch ID#".$bid." Counter ID#".$counter_id." Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no'].", item ID#".$r['id'], true);
			return;
		}
		
		// verify no. of records written
		$q2 = $hqcon->query("select count(*) as total from $table where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and pos_id=$server_pos_id and item_id=".mi($r['item_id']));
		$tmp = $hqcon->sql_fetchassoc($q2);
		$hqcon->sql_freeresult($q2);
		
		// written rows not match
		if ($tmp['total'] != 1) {
			$this->store_counter_error($bid, $date, $counter_id, "Error $table: ".$date.": Branch ID#".$bid." Counter ID#".$counter_id."  Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no']." item_id#".$r['item_id'].", Written rows not same.", true);
			return;
		}
		
		// update S/N become inactive and available for next payment
		$sn = $hqcon->query("select * from sn_info where pos_id = ".mi($r['return_pos_id'])." and item_id = ".mi($r['return_item_id'])." and branch_id = ".mi($r['return_branch_id'])." and counter_id = ".mi($r['return_counter_id'])." and date = ".ms($r['return_date']));
		
		// here is where we do update on serial no. from frontend database and backend server
		if ($hqcon->sql_numrows($sn) > 0){
			$sn_item = $hqcon->sql_fetchassoc($sn);
			
			// get the latest serial number status
			$q_pis = $hqcon->query("select date from pos_items_sn where located_branch_id=".mi($sn_item['branch_id'])." and sku_item_id=".mi($sn_item['sku_item_id'])." and serial_no = ".ms($sn_item['serial_no']));
			$pos_items_sn = $hqcon->sql_fetchassoc($q_pis);
			$hqcon->sql_freeresult($q_pis);
			
			// if no status or the last status is older
			if(!$pos_items_sn['date'] || strtotime($pos_items_sn['date']) <= strtotime($date)){
				// update serial number status to "Available"
				$hqcon->exec("update pos_items_sn set status = ".ms("Available").", pos_id='',pos_item_id='',pos_branch_id='', date='', counter_id='',last_update = CURRENT_TIMESTAMP where located_branch_id = ".mi($sn_item['branch_id'])." and sku_item_id = ".mi($sn_item['sku_item_id'])." and serial_no = ".ms($sn_item['serial_no']));
			}				
			
			// set the last sold record active to 0
			$hqcon->exec("update sn_info set active=0 where pos_id = ".mi($r['return_pos_id'])." and item_id = ".mi($r['return_item_id'])." and branch_id = ".mi($r['return_branch_id'])." and counter_id = ".mi($r['return_counter_id'])." and date = ".ms($r['return_date']));
			
		}
		$hqcon->sql_freeresult($sn);
		
		return true;
	}
	
	private function pos_items_handler($bid, $date, $counter_id, $ss_pos_id, $server_pos_id, $pos, $r){
		global $con, $hqcon;
		
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		$item_id = mi($r['item_id']);
		$ss_pos_id = mi($ss_pos_id);
		$server_pos_id = mi($server_pos_id);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		if(!$ss_pos_id){
			print "Invalid Sync Server POS ID.\n";
			return;
		}
		if(!$server_pos_id){
			print "Invalid Server POS ID.\n";
			return;
		}
		if(!$item_id){
			print "Invalid Item ID.\n";
			return;
		}
		
		//////////////// sn_info ///////////////////
		// get column
		$cols = $this->get_table_cols('sn_info');
		
		// check serial number data at sync server
		$q_sn = $con->query("select * from sn_info where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and pos_id=$ss_pos_id and item_id=$item_id");
		$ss_sn_item = $con->sql_fetchassoc($q_sn);
		$con->sql_freeresult($q_sn);
		
		// got serial number
		if($ss_sn_item){
			// replace pos_id to server pos_id
			$ss_sn_item['pos_id'] = $server_pos_id;
			$num_rows = $hqcon->exec("replace into sn_info ".mysql_insert_by_field($ss_sn_item, $cols));
			if ($num_rows<=0){
				// show error
				print $hqcon->exec_sql_error();
				$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert sn_info: ".$date." Branch ID#".$bid." Counter ID#".$counter_id." Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no'].", item ID#".$item_id, true);
				return;
			}
			
			// get the latest serial number status
			$q_pis = $hqcon->query("select date from pos_items_sn where located_branch_id=$bid and sku_item_id=".mi($ss_sn_item['sku_item_id'])." and serial_no = ".ms($ss_sn_item['serial_no']));
			$pos_items_sn = $hqcon->sql_fetchassoc($q_pis);
			$hqcon->sql_freeresult($q_pis);
			
			// if no status or the last status is older
			if(!$pos_items_sn['date'] || strtotime($pos_items_sn['date']) <= strtotime($date)){	
				if(!$pos['cancel_status']){
					if($ss_sn_item['active'])
					{
						$upd = array();
						$upd['status'] = 'Sold';
						$upd['pos_id'] = $server_pos_id;
						$upd['pos_item_id'] = $item_id;
						$upd['pos_branch_id'] = $bid;
						$upd['date'] = $date;
						$upd['counter_id'] = $counter_id;
						$upd['last_update'] = 'CURRENT_TIMESTAMP';
						$hqcon->exec("update pos_items_sn set ".mysql_update_by_field($upd)." where located_branch_id=$bid and sku_item_id = ".mi($ss_sn_item['sku_item_id'])." and serial_no = ".ms($ss_sn_item['serial_no']));
					}
					else{
						// update serial number status to "Available"
						$hqcon->exec("update pos_items_sn set status = ".ms("Available").", pos_id='',pos_item_id='',pos_branch_id='', date='', counter_id='',last_update= CURRENT_TIMESTAMP where located_branch_id=$bid and sku_item_id = ".mi($ss_sn_item['sku_item_id'])." and serial_no=".ms($ss_sn_item['serial_no']));
					}
				}
			}
		}
		return true;
	}
	
	private function sync_pos_receipt_cancel($pos, $ss_pos_id, $server_pos_id){
		global $con, $hqcon;
		
		$bid = mi($pos['branch_id']);
		$counter_id = mi($pos['counter_id']);
		$date = $pos['date'];
		$ss_pos_id = mi($ss_pos_id);
		$server_pos_id = mi($server_pos_id);
		$receipt_no = $pos['receipt_no'];
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$ss_pos_id){
			print "Invalid Sync Server POS ID.\n";
			return;
		}
		if(!$server_pos_id){
			print "Invalid Server POS ID.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		if(!$receipt_no){
			print "Invalid Receipt No.\n";
			return;
		}
		
		// dont sync if this counter already have error
		if($this->got_counter_error($bid, $date, $counter_id))	return;
		
		$table = "pos_receipt_cancel";
		
		// data
		$q1 = $con->query("select * from $table where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and receipt_no=".ms($receipt_no));
		$r = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$r)	return;	// no data
		
		// get column
		$cols = $this->get_table_cols($table);
		
		$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $cols));
		if ($num_rows<=0){
			// show error
			print $hqcon->exec_sql_error();
			$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert $table: ".$date." Branch ID#".$bid." Counter ID#".$counter_id, true);
			return;
		}
		
	}
	
	private function sync_pos_deposit_status($pos, $ss_pos_id, $server_pos_id){
		global $con, $hqcon;
		
		$bid = mi($pos['branch_id']);
		$counter_id = mi($pos['counter_id']);
		$date = $pos['date'];
		$ss_pos_id = mi($ss_pos_id);
		$server_pos_id = mi($server_pos_id);
		$receipt_no = $pos['receipt_no'];
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$ss_pos_id){
			print "Invalid Sync Server POS ID.\n";
			return;
		}
		if(!$server_pos_id){
			print "Invalid Server POS ID.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		if(!$receipt_no){
			print "Invalid Receipt No.\n";
			return;
		}
		
		// dont sync if this counter already have error
		if($this->got_counter_error($bid, $date, $counter_id))	return;
		
		// pos_deposit_status
		// RECEIVED
		$q1 = $con->query("select * from pos_deposit_status where deposit_branch_id=$bid and deposit_counter_id=$counter_id and deposit_date=".ms($date)." and deposit_receipt_no=".ms($receipt_no));
		$deposit_rcv = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($deposit_rcv){	// Got Receive Deposit
			$need_update = true;
			
			// Check Server
			$q2 = $hqcon->query("select * from pos_deposit_status where deposit_branch_id=$bid and deposit_counter_id=$counter_id and deposit_date=".ms($date)." and deposit_receipt_no=".ms($receipt_no));
			$server_deposit_rcv = $hqcon->sql_fetchassoc($q2);
			$hqcon->sql_freeresult($q2);
			
			if($server_deposit_rcv){	// server already have entry
				if(strtotime($server_deposit_rcv['last_update'])>strtotime($deposit_rcv['last_update'])){
					$need_update = false;
				}
			}
			
			if($need_update){	// need update to server
				if($deposit_rcv['branch_id'] && $deposit_rcv['counter_id'] && $deposit_rcv['pos_id'] && $deposit_rcv['receipt_no'] && $deposit_rcv['date']){
					// got usage info
					$deposit_rcv['pos_id'] = $this->get_pos_id_from_server($deposit_rcv['branch_id'], $deposit_rcv['counter_id'], $deposit_rcv['date'], 0, $deposit_rcv['receipt_no']);
					if(!$deposit_rcv['pos_id']){
						// Cant find the used deposit info at server, maybe the used receipt not yet sync to server
						if($server_deposit_rcv['pos_id']){
							// server already have used info, here must also have
							$this->store_counter_error($bid, $date, $counter_id, "Error pos_deposit_status: ".$date.": Branch ID#".$bid." Counter ID#".$counter_id."  Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no'].", cannot found pos_id at server.", true);
							return;
						}else{
							// skip sync used info
							$deposit_rcv['branch_id'] = 0;
							$deposit_rcv['counter_id'] = 0;
							$deposit_rcv['pos_id'] = 0;
							$deposit_rcv['date'] = '';
							$deposit_rcv['receipt_no'] = '';
							$deposit_rcv['last_update'] = $pos['end_time'];	// use this deposit receive end time as last_update
						}						
					}
				}
				$deposit_rcv['deposit_pos_id'] = $server_pos_id;
				
				// get column
				$cols = $this->get_table_cols('pos_deposit_status');
				$num_rows = $hqcon->exec("replace into pos_deposit_status ".mysql_insert_by_field($deposit_rcv, $cols));
				if ($num_rows<=0){
					// show error
					print $hqcon->exec_sql_error();
					$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert pos_deposit_status: ".$date." Branch ID#".$bid." Counter ID#".$counter_id." Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no'], true);
					return;
				}else{
					$con->query("update pos_deposit_status set sync=1 where deposit_branch_id=$bid and deposit_counter_id=$counter_id and deposit_date=".ms($date)." and deposit_receipt_no=".ms($receipt_no));
				}
			}
		}
		
		// USED / CANCEL
		$q1 = $con->query("select * from pos_deposit_status where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and receipt_no=".ms($receipt_no));
		$deposit_used_canceled = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($deposit_used_canceled){	// this pos is used or cancelled deposit
			$need_update = true;
			
			// Check Server
			$q2 = $hqcon->query("select * from pos_deposit_status where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and receipt_no=".ms($receipt_no));
			$server_deposit_used_canceled = $hqcon->sql_fetchassoc($q2);
			$hqcon->sql_freeresult($q2);
			
			if($server_deposit_used_canceled){	// server already have entry
				if(strtotime($server_deposit_used_canceled['last_update'])>strtotime($deposit_used_canceled['last_update'])){
					$need_update = false;
				}
			}
			
			if($need_update){	// need update to server
				// get deposit main info
				$deposit_used_canceled['deposit_pos_id'] = $this->get_pos_id_from_server($deposit_used_canceled['deposit_branch_id'], $deposit_used_canceled['deposit_counter_id'], $deposit_used_canceled['deposit_date'], 0, $deposit_used_canceled['deposit_receipt_no']);
				if(!$deposit_used_canceled['deposit_pos_id']){
					$this->store_counter_error($bid, $date, $counter_id, "Error pos_deposit_status: ".$date.": Branch ID#".$bid." Counter ID#".$counter_id."  Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no'].", cannot found deposit_pos_id at server.", true);
					return;
				}
				
				$deposit_used_canceled['pos_id'] = $server_pos_id;
				
				// get column
				$cols = $this->get_table_cols('pos_deposit_status');
				$num_rows = $hqcon->exec("replace into pos_deposit_status ".mysql_insert_by_field($deposit_used_canceled, $cols));
				if ($num_rows<=0){
					// show error
					print $hqcon->exec_sql_error();
					$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert pos_deposit_status: ".$date." Branch ID#".$bid." Counter ID#".$counter_id." Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no'], true);
					return;
				}else{
					$con->query("update pos_deposit_status set sync=1 where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and receipt_no=".ms($receipt_no));
				}
			}
		}
		
		// pos_deposit_status_history
		$q1 = $con->query("select * from pos_deposit_status_history where branch_id=$bid and counter_id=$counter_id and pos_date=".ms($date)." and receipt_no=".ms($receipt_no));
		
		while($pdsh = $con->sql_fetchassoc($q1)){
			// get deposit main info
			$pdsh['deposit_pos_id'] = $this->get_pos_id_from_server($pdsh['deposit_branch_id'], $pdsh['deposit_counter_id'], $pdsh['deposit_pos_date'], 0, $pdsh['deposit_receipt_no']);
			if(!$pdsh['deposit_pos_id']){
				$this->store_counter_error($bid, $date, $counter_id, "Error pos_deposit_status_history: ".$date.": Branch ID#".$bid." Counter ID#".$counter_id."  Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no'].", cannot found deposit_pos_id at server.", true);
				return;
			}
				
			$ss_ori_pos_id = $pdsh['pos_id'];
			$pdsh['pos_id'] = $server_pos_id;
			
			// get column
			$cols = $this->get_table_cols('pos_deposit_status_history');
			$num_rows = $hqcon->exec("replace into pos_deposit_status_history ".mysql_insert_by_field($pdsh, $cols));
			if ($num_rows<=0){
				// show error
				print $hqcon->exec_sql_error();
				$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert pos_deposit_status_history: ".$date." Branch ID#".$bid." Counter ID#".$counter_id." Receipt#".$pos['receipt_no']." Invoice#".$pos['receipt_ref_no'], true);
				return;
			}else{
				$con->query("update pos_deposit_status_history set sync=1, added=added where branch_id=".mi($pdsh['branch_id'])." and counter_id=".mi($pdsh['counter_id'])." and pos_date=".ms($pdsh['pos_date'])." and pos_id=".mi($ss_ori_pos_id)." and type = ".ms($pdsh['type']));
			}
		}
		$con->sql_freeresult($q1);
	}
	
	private function sync_other_pos_related_table($table, $with_ref_no=false, $bid, $date, $counter_id){
		global $con, $hqcon;
		
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		
		if(!$table){
			print "No Table.\n";
			return;
		}
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		// get column
		$cols = $this->get_table_cols($table);
		
		// dont sync if this counter already have error
		if($this->got_counter_error($bid, $date, $counter_id))	return;
		
		// update sales record count
		print "Start to Sync $table...";
		$this->update_sales_record($table, $bid, $date, $counter_id);
			
		// select from table
		$q1 = $con->query("select *
			from $table
			where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and sync=0");
		$total_count = mi($con->sql_numrows($q1));
		$curr_count = 0;
		if($total_count > 0){
			while($r = $con->sql_fetchassoc($q1)){
				$curr_count++;
				print "\r$curr_count / $total_count. . . . .";
						
				$old_id = mi($r['id']);
				
				$server_r = array();
				
				// check using ref_no
				if($with_ref_no && isset($r['ref_no']) && $r['ref_no']){	// got ref_no
					// check server
					$q_ref_no = $hqcon->query("select id from $table where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and ref_no=".ms($r['ref_no'])." order by id limit 1");
					$server_r = $hqcon->sql_fetchassoc($q_ref_no);
					$hqcon->sql_freeresult($q_ref_no);
				}
				
				// check using timestamp
				if(!$server_r && isset($r['timestamp']) && $r['timestamp'] && (!isset($r['ref_no']) || !$r['ref_no'])){	// got timestamp
					$q_timestamp = $hqcon->query("select id from $table where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and timestamp=".ms($r['timestamp'])." order by id limit 1");
					$server_r = $hqcon->sql_fetchassoc($q_timestamp);
					$hqcon->sql_freeresult($q_timestamp);
				}
				
				if(isset($server_r['id']) && $server_r['id']){
					$r['id'] = $server_r['id'];
				}else{
					// get max id
					$q_max = $hqcon->query("select max(id) as id from $table where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id");
					$max_r = $hqcon->sql_fetchassoc($q_max);
					$hqcon->sql_freeresult($q_max);
					
					if($max_r && isset($max_r['id'])){
						$r['id'] = mi($max_r['id'])+1;
					}else{
						$r['id'] = 1;
					}
				}
				
				$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $cols));
				if ($num_rows<=0){
					// show error
					print $hqcon->exec_sql_error();
					$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert $table: ".$date." Branch ID#".$bid." Counter ID#".$counter_id, true);
					return;
				}
				
				// set sync=1
				$con->exec("update $table set sync=1 where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and id=$old_id");
			}
			
		}else{
			print "No Data. ";
		}
		$con->sql_freeresult($q1);
		
		
		// update sales record count
		$this->update_sales_record($table, $bid, $date, $counter_id);
	}
	
	private function sync_pos_error($bid, $date, $counter_id){
		global $con,$hqcon;
		
		$table = "pos_error";
		print "Update $table. ";
		
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		// mark last id
		$last_id = 0;
		
		// data
		$q1 = $con->query("select * from $table where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and sync=0 order by id");
		
		$total_count = mi($con->sql_numrows($q1));
		if($total_count > 0){
			// get column
			$cols = $this->get_table_cols($table);
			
			while($r = $con->sql_fetchassoc($q1)){
				$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $cols));
				if ($num_rows<=0){
					// show error
					print $hqcon->exec_sql_error();
					$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert $table: ".$date." Branch ID#".$bid." Counter ID#".$counter_id, true);
					return;
				}
				// set sync=1
				$con->exec("update $table set sync=1 where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and id=".mi($r['id']));
				$last_id = $r['id'];
			}
		}
		$con->sql_freeresult($q1);
		
		// delete old record
		//$hqcon->exec("delete from $table where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and id>$last_id");
		
		print "Done.\n";
	}
	
	private function sync_membership_points($bid, $date){
		global $con, $hqcon;
		
		print "Update Membership Points...";
		
		$bid = mi($bid);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}		
		
		$table = "membership_points";
		$q1 = $con->query("select * from $table where branch_id=$bid and date between ".ms($date)." and ".ms($date." 23:59:59")." and sync=0");
		$total_count = mi($con->sql_numrows($q1));
		if($total_count > 0){
			// get column
			$cols = $this->get_table_cols($table);
			
			while($r = $con->sql_fetchassoc($q1)){
				$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $cols));
				if ($num_rows<=0){
					// show error
					print $hqcon->exec_sql_error();
					$this->store_sync_server_error($bid, $date, "Unable to Insert $table: ".$date." Branch ID#".$bid, true);
					return;
				}
				// set sync=1
				$con->exec("update $table set sync=1 where branch_id=$bid and card_no=".ms($r['card_no'])." and type=".ms($r['type'])." and date=".ms($r['date']));
			}
		}
		$con->sql_freeresult($q1);
		print "Done.\n";
	}
	
	private function sync_sku_items_temp_price($bid, $date){
		global $con, $hqcon;
		
		$table = "sku_items_temp_price";
		print "Update $table ...";
		
		$bid = mi($bid);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}		
		
		$q1 = $con->query("select * from $table where branch_id=$bid and lastupdate between ".ms($date)." and ".ms($date." 23:59:59")." and sync=0 order by lastupdate");
		$total_count = mi($con->sql_numrows($q1));
		if($total_count > 0){
			// get column
			$cols = $this->get_table_cols($table);
			
			while($r = $con->sql_fetchassoc($q1)){
				$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $cols));
				if ($num_rows<=0){
					// show error
					print $hqcon->exec_sql_error();
					$this->store_sync_server_error($bid, $date, "Unable to Insert $table: ".$date." Branch ID#".$bid.", SKU ITEM ID#".mi($r['sku_item_id']), true);
					return;
				}
				// set sync=1
				$con->exec("update $table set sync=1 where branch_id=$bid and sku_item_id=".mi($r['sku_item_id']));
			}
		}
		$con->sql_freeresult($q1);
		print "Done.\n";
	}
	
	private function sync_sku_items_temp_price_history($bid, $date){
		global $con, $hqcon;
		
		$table = "sku_items_temp_price_history";
		print "Update $table ...";
		$bid = mi($bid);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		$q1 = $con->query("select * from $table where branch_id=$bid and added_date=".ms($date)." and sync=0 order by added_datetime");
		$total_count = mi($con->sql_numrows($q1));
		if($total_count > 0){
			// get column
			$cols = $this->get_table_cols($table);
			
			while($r = $con->sql_fetchassoc($q1)){
				$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $cols));
				if ($num_rows<=0){
					// show error
					print $hqcon->exec_sql_error();
					$this->store_sync_server_error($bid, $date, "Unable to Insert $table: ".$date." Branch ID#".$bid.", SKU ITEM ID#".mi($r['sku_item_id']).", Added Time#".$r['added_datetime'], true);
					return;
				}
				// set sync=1
				$con->exec("update $table set sync=1 where branch_id=$bid and sku_item_id=".mi($r['sku_item_id'])." and added_datetime=".ms($r['added_datetime']));
			}
		}
		$con->sql_freeresult($q1);
		print "Done.\n";
	}
	
	private function sync_ejournal_audit_log($bid, $date, $counter_id, $force_sync = false){
		global $con, $hqcon;
		
		print "Sync Ejournal & Audit Log ...\n";
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		$allow_sync = false;
		if($force_sync){	// got force sync
			$allow_sync = true;			
		}else{
			// check minute
			$curr_minute = mi(date("i"));
			
			// do not sync every minute, sync when found it is force_sync or minutes reaches 0 and 30
			if($curr_minute == 0 || $curr_minute == 30)	$allow_sync = true;
		}
		
		$tbl_list = array('pos_transaction_ejournal', 'pos_transaction_audit_log');
		foreach($tbl_list as $table){
			$q1 = $con->query("select * from $table where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and sync=0");
			$r = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			if($r){
				$diff_min = 0;
				if(!$allow_sync){
					$diff_min = (time()- strtotime($r['lastupdate']))/60;
				}
				if($allow_sync || $diff_min>=30){
					print "Sync $table ...";
					// get column
					$cols = $this->get_table_cols($table);
			
					$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $cols));
					if ($num_rows<=0){
						// show error
						print $hqcon->exec_sql_error();
						$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert $table: ".$date." Branch ID#".$bid." Counter ID#".$counter_id, true);
						return;
					}
					// set sync=1
					$con->exec("update $table set sync=1 where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id");
					print " Done.\n";
				}else{
					print "Time condition not meet, wait next minute to try again.\n";
				}
			}else{
				print "$table no data.\n";
			}
		}		
	}
	
	private function sync_clocking_log($bid, $date, $counter_id){
		global $con, $hqcon;
		
		print "Sync Clocking Log ...\n";
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		$table = 'pos_transaction_clocking_log';
		$q1 = $con->query("select * from $table where branch_id=$bid and counter_id=$counter_id and counter_date between ".ms($date)." and ".ms($date." 23:59:59")." and sync=0 order by id");
		$total_count = mi($con->sql_numrows($q1));
		if($total_count > 0){
			// get column
			$cols = $this->get_table_cols($table);
			
			while($r = $con->sql_fetchassoc($q1)){
				$old_id = $r['id'];
				
				// check hq server data
				$q2 = $hqcon->query("select id from $table where branch_id=$bid and counter_id=$counter_id and counter_date=".ms($r['counter_date']));
				$tmp = $hqcon->sql_fetchassoc($q2);
				$hqcon->sql_freeresult($q2);
				
				if($tmp){	// row already exist in HQ
					$r['id'] = mi($tmp['id']);
				}else{	// find max id
					$q_max = $hqcon->query("select max(id) as id from $table where branch_id=$bid and counter_id=$counter_id");
					$tmp = $hqcon->sql_fetchassoc($q_max);
					$hqcon->sql_freeresult($q_max);
					
					if($tmp){
						$r['id'] = mi($tmp['id'])+1;
					}else{
						$r['id'] = 1;
					}
				}
				
				$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $cols));
				if ($num_rows<=0){
					// show error
					print $hqcon->exec_sql_error();
					$this->store_sync_server_error($bid, $date, "Unable to Insert $table: ".$date." Branch ID#".$bid.", Counter ID#".$counter_id.", Counter Time#".$r['counter_date'], true);
					return;
				}
				// set sync=1
				$con->exec("update $table set sync=1 where branch_id=$bid and counter_id=$counter_id and id=".mi($old_id));
			}
		}
		$con->sql_freeresult($q1);
		print "Done.\n";
	}
	
	private function delete_invalid_sku(){
		global $con2;
		
		print "Delete Expired Invalid SKU...\n";
		$this->connect_down_sync_db();
		
		$con2->exec("create table if not exists tmp_invalid_sku (branch_id integer ,counter_id integer,open_by integer,barcode char(50),sku_description char(35),unit_price double,lastupdate timestamp, primary key(branch_id,barcode))");
		$con2->exec("create table if not exists tmp_invalid_sku_history (branch_id integer,counter_id integer,open_by integer,barcode char(50),sku_description char(35),unit_price double,added_date timestamp, primary key(branch_id,barcode,added_date))");
		
		$ret = $con2->query("select * from tmp_invalid_sku");
		//Select data from invalid SKU and the SKU if more than 1 month
		$i=0;
		while($r = $con2->sql_fetchassoc($ret))
		{
			$diff_month = (time()- strtotime($r['lastupdate']))/(60*60*24*30);
			if($diff_month > 1)
			{
				$num_rows = $con2->exec("Delete from tmp_invalid_sku where branch_id=".mi($r['branch_id'])." and barcode=".ms($r['barcode']));				
				if ($num_rows<=0){
					// show error
					print $con2->exec_sql_error();
					$bid = $r['branch_id'];
					$date = date("Y-m-d", strtotime($r['lastupdate']));
					$this->store_sync_server_error($bid, $date, "Unable to delete tmp_invalid_sku: ".$date." Branch ID#".$bid.", Barcode#".$r['barcode'], true);
					break;
				}else{
					$i++;
				}
			}
		}
		print "$i invalid items have been deleted.\n";
		
		$con2->sql_freeresult($ret);
		unset($con2);
		
		$this->upload_sync_server_error_using_append();
		
		print "Done.\n";
	}
	
	private function update_sales_record($table, $bid, $date, $counter_id)
	{
		global $con,$hqcon;
		
		print "Update Sales Record...";
		
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		$columnFields = array("branch_id","counter_id","date","tablename","total_record","synced_record","missing_record","added","lastupdate");
		/*if($table=="pos_deposit_status_history")
			$datefields = "pos_date";
		else
			$datefields = "date";*/
		
		$cond = array();
		$cond[] = "branch_id = ".$bid;
		$cond[] = "counter_id = ".$counter_id;
		$cond[] = "date = ".ms($date);
		//$cond[] = "$datefields = ".ms($date);
		$where =  "where ". implode(" and ",$cond);
		
		// additional checking for pos table in sync server
		$str_pos_additional_filter = "";
		if($table == "pos") $str_pos_additional_filter = "and transaction_sync = 1";
		
	
		// Total Record
		$q1 = $con->query("select count(*) as total_record from $table $where $str_pos_additional_filter");
		$tmp = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$total_record = mi($tmp['total_record']);
		
		// Synced Record
		$q2 = $con->query("select count(*) as synced_record from $table $where $str_pos_additional_filter and sync=1");
		$tmp = $con->sql_fetchassoc($q2);
		$con->sql_freeresult($q2);
		$synced_record = mi($tmp['synced_record']);
		
		// Missing Record
		$q3 = $hqcon->query("select count(*) as c from $table $where");
		$tmp = $con->sql_fetchassoc($q3);
		$con->sql_freeresult($q3);
		$missing_record = $synced_record-mi($tmp['c']);
		if($missing_record<0)	$missing_record = 0;
		
		$upd = array();
		$upd['branch_id'] = $bid;	
		$upd['counter_id'] = $counter_id;
		$upd['date'] = $date;	
		$upd['tablename'] = $table;		
		$upd['total_record'] = $total_record;					
		$upd['synced_record'] = $synced_record;			
		$upd['missing_record'] = $missing_record;	
		$upd['added'] = 'CURRENT_TIMESTAMP';
		$upd['lastupdate'] = 'CURRENT_TIMESTAMP';
		$hqcon->exec("insert into pos_transaction_counter_sales_record ".mysql_insert_by_field($upd)." on duplicate key update
		total_record=".mi($upd['total_record']).",
		synced_record=".mi($upd['synced_record']).",
		missing_record=".mi($upd['missing_record']).",
		lastupdate=CURRENT_TIMESTAMP");
		
		print "Done.\n";
	}
	
	/*private function reset_sync_server_error($bid, $date, $counter_id=0){
		global $con,$hqcon;
		
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		if($counter_id>0){
			$hqcon->query("update pos_transaction_sync_server_counter_tracking set error_message='', lastupdate = CURRENT_TIMESTAMP where branch_id=$bid and counter_id=$counter_id and date = ".ms($date));
		}else{
			$hqcon->query("update pos_transaction_sync_server_tracking set error_message='', lastupdate = CURRENT_TIMESTAMP where branch_id=$bid and date=".ms($date));
		}
	}*/
	
	private function is_finalized($bid, $date){
		global $con,$hqcon;
		
		$bid = mi($bid);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return true;
		}
		if(!$date){
			print "Invalid Date.\n";
			return true;
		}
		
		$q1 = $hqcon->query("select * from pos_finalized where branch_id=$bid and date = ".ms($date)." and finalized = 1");
		$tmp = $hqcon->sql_fetchassoc($q1);
		$hqcon->sql_freeresult($q1);
		
		return $tmp ? true : false;
	}
	
	private function insert_finalised_error($bid, $date, $counter_id_list, $msg=""){
		global $con,$hqcon;
		
		$bid = mi($bid);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		if(!$counter_id_list || !is_array($counter_id_list)){
			print "Invalid Counter.\n";
			return;
		}
		if(!$msg)	$msg = "Please re-finalize $date sales.";
		
		foreach($counter_id_list as $counter_id){
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['counter_id'] = $counter_id;
			$upd['date'] = $date;
			$upd['error_msg'] = $msg;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$hqcon->exec("insert into pos_finalised_error ".mysql_insert_by_field($upd)." on duplicate key update
			error_msg=".ms($upd['error_msg']));
		}
	}
	
	private function get_table_cols($table){
		global $con;
		
		if(isset($this->table_cols_definition[$table]))	return $this->table_cols_definition[$table];
		
		// get column list from mysql
		$curr_tbl_col_list = array();
		$q1 = $con->query("explain $table");
		while($r = $con->sql_fetchassoc($q1)){
			// skip sync and transaction_sync
			if ($r['Field'] == "sync" || $r['Field'] == "transaction_sync")	continue;
            $curr_tbl_col_list[] = $r['Field'];
		}	
		$this->table_cols_definition[$table] = $curr_tbl_col_list;
		return $this->table_cols_definition[$table];
	}
	
	private function get_pos_id_from_server($bid, $counter_id, $date, $ss_pos_id, $receipt_no=0){
		global $con,$hqcon;
		
		$bid = mi($bid);
		$ss_pos_id = mi($ss_pos_id);	// Sync Server POS ID
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}

		// no provide receipt_no, need to manually go select in sync server mysql
		if (!$receipt_no){
			if(!$ss_pos_id){
				print "Require Sync Server POS ID or Receipt_no.\n";
				return;
			}
			$q1 = $con->query("select receipt_no from pos where branch_id=".mi($bid)." and counter_id=".mi($counter_id)." and date=".ms($date)." and id=".mi($ss_pos_id));
			$r = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			if(!$r){
				print "POS ID '$ss_pos_id' Not Found in Sync Server.\n";
				return;
			}
			$receipt_no = $r['receipt_no'];	
			unset($r);
		}
		
		// select from main server using receipt_no
		$q2 = $hqcon->query("select id from pos where branch_id=".mi($bid)." and counter_id=".mi($counter_id)." and date=".ms($date)." and receipt_no=".mi($receipt_no));
		$tmp = $hqcon->sql_fetchassoc($q2);
		$hqcon->sql_freeresult($q2);
		
		$pos_id = mi($tmp['id']);
		if(!$pos_id){
			//print "Receipt_no '$receipt_no' Not Found in Main Server.\n";
			return;
		}
		return $pos_id;
	}
	
	private function get_new_pos_id_from_server($bid, $counter_id, $date){
		global $hqcon;
		
		//print "Get New POS ID.\n";
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		$q1 = $hqcon->query("select max(id) as max_id from pos where branch_id=".mi($bid)." and counter_id=".mi($counter_id)." and date=".ms($date));
		$tmp = $hqcon->sql_fetchassoc($q1);
		$hqcon->sql_freeresult($q1);
		return mi($tmp['max_id'])+1;
	}
	
	private function sync_data_without_check_finalise($bid, $date, &$branch_counter_id_list){
		global $con, $hqcon;
		
		$all_counter_id_list = array();
		$sql = "select distinct counter_id from pos_error where sync = 0 and branch_id=$bid and date=".ms($date)." union
				select distinct counter_id from pos_transaction_audit_log where sync = 0 and branch_id=$bid and date=".ms($date)." union
				select distinct counter_id from pos_transaction_ejournal where sync = 0 and branch_id=$bid and date=".ms($date)." union
				select distinct counter_id from pos_transaction_clocking_log where sync = 0 and branch_id=$bid and counter_date between ".ms($date)." and ".ms($date." 23:59:59")." union
				select distinct counter_id from pos_user_log where sync = 0 and branch_id=$bid and date=".ms($date)." union
				select distinct counter_id from pos_counter_collection_tracking where sync = 0 and branch_id=$bid and date=".ms($date)." union
				select distinct counter_id from pos_day_start where sync = 0 and branch_id=$bid and date=".ms($date)." union
				select distinct counter_id from pos_day_end where sync = 0 and branch_id=$bid and date=".ms($date)."
				order by counter_id";
		$q_all = $con->query($sql);
		while($r = $con->sql_fetchassoc($q_all)){
			$all_counter_id_list[] = $r['counter_id'];
			$branch_counter_id_list[$r['counter_id']] = $r['counter_id'];
		}
		$con->sql_freeresult($q_all);
		
		// sync other pos related table, which is not under pos table
		if($all_counter_id_list){
			foreach($all_counter_id_list as $counter_id){
				// Begin
				$con->beginTransaction();
				$hqcon->beginTransaction();
		
				$this->sync_other_pos_related_table('pos_user_log', true, $bid, $date, $counter_id);
				
				$this->sync_pos_error($bid, $date, $counter_id);
				
				$this->sync_ejournal_audit_log($bid, $date, $counter_id);
				
				$this->sync_clocking_log($bid, $date, $counter_id);
				
				$this->sync_pos_counter_collection_tracking($bid, $date, $counter_id);
				
				$this->sync_pos_day_start($bid, $date, $counter_id);

				$this->sync_pos_day_end($bid, $date, $counter_id);
				// Commit
				$hqcon->commit();
				$con->commit();
			}
		}
	}
	
	private function sync_data_with_check_finalise($bid, $date, &$branch_counter_id_list){
		global $con, $hqcon;
		
		// get counter_id_list
		$all_counter_id_list = array();
		$sql = "select distinct counter_id from pos where sync=0 and transaction_sync = 1 and branch_id=$bid and date=".ms($date)." union
				select distinct counter_id from pos_drawer where sync = 0 and branch_id=$bid and date=".ms($date)." union
				select distinct counter_id from pos_cash_history where sync = 0 and branch_id=$bid and date=".ms($date)." union
				select distinct counter_id from pos_cash_domination where sync = 0 and branch_id=$bid and date=".ms($date)."
				order by counter_id";
		$q_all = $con->query($sql);
		while($r = $con->sql_fetchassoc($q_all)){
			$all_counter_id_list[] = $r['counter_id'];
			$branch_counter_id_list[$r['counter_id']] = $r['counter_id'];
		}
		$con->sql_freeresult($q_all);
		if(!$all_counter_id_list){
			print "No Counter POS data to sync.\n";
			return;
		}
		
		// check whether this date already finalised
		if($this->is_finalized($bid, $date)){	// found finalised
			//print "Counter Collection already finalised.\n";
			$msg = "Got Un-sync record, please unfinalise $date counter collection.";
			print $msg."\n";
			$this->insert_finalised_error($bid, $date, $all_counter_id_list, $msg);
			return;
		}
		
		// select pos
		$pos_counter_id_list = array();
		$q1 = $con->query("select counter_id, count(*) as c 
			from pos 
			where branch_id=$bid and date=".ms($date)." and sync=0 and transaction_sync=1 
			group by counter_id
			order by counter_id, id");
		while($r = $con->sql_fetchassoc($q1)){	// loop by counter
			// update sales record count
			$this->update_sales_record('pos', $bid, $date, $r['counter_id']);
			
			$pos_counter_id_list[] = $r['counter_id'];
		}
		$con->sql_freeresult($q1);
		
		/////////// POS /////////////
		if($pos_counter_id_list){
			// get columns of POS
			$pos_cols = $this->get_table_cols('pos');
			
			// loop counter
			foreach($pos_counter_id_list as $counter_id){
				print "Sync Counter ID: $counter_id";
				
				// Get all unsync pos from this counter
				$q_pos = $con->query("select * from pos where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and sync=0 and transaction_sync=1 order by id");
				$total_pos_count = mi($con->sql_numrows($q_pos));
				$curr_count = 0;
				print "\tTotal POS Found: $total_pos_count\n";
				print "Uploading...\n";
				
				// loop pos
				while($r = $con->sql_fetchassoc($q_pos)){
					// Begin
					$con->beginTransaction();
					$hqcon->beginTransaction();
				
					$curr_count++;
					
					print "\r$curr_count / $total_pos_count. . . . .";
					
					// check POS data from main server to see if existed but contains different GUID
					$hq_pos = $hqcon->query("select * from pos where branch_id=".mi($bid)." and counter_id=".mi($counter_id)." and date=".ms($date)." and receipt_no = ".ms($r['receipt_no']));
					$result = $hqcon->sql_fetchassoc($hq_pos);
					$hqcon->sql_freeresult($hq_pos);
					
					if ($result['id']){ // found got similar receipt no between sync server and backend
						if($result['pos_guid'] != $r['pos_guid']){ // found it is not using the same GUID but same receipt no, prompt error
							$error_msg = "Error: ".$r['date'].": POS Receipt#".$r['receipt_no'].", found duplicated receipt_no with different GUID.";
							print $error_msg."\n";
							$this->store_counter_error($bid, $date, $counter_id, $error_msg, false);
							// Roll Back
							$con->rollback();
							$hqcon->rollback();
							break;
						}
					}
					
					$ss_pos_id = mi($r['id']);	// Sync Server POS ID
					$server_pos_id = mi($this->get_pos_id_from_server($bid, $counter_id, $date, $ss_pos_id, $r['receipt_no']));
					if(!$server_pos_id){
						$server_pos_id = mi($this->get_new_pos_id_from_server($bid, $counter_id, $date));
						print "New POS ID: $server_pos_id\n";
					}
					
					if(!$server_pos_id){
						print "Cant get POS ID.\n";
						$this->store_counter_error($bid, $date, $counter_id, "Unable to get Server POS ID: Branch ID#".$bid." Counter ID#".$counter_id." Date#".$date." Receipt#".$r['receipt_no']." Invoice#".$r['receipt_ref_no'], true);
						// Roll Back
						$con->rollback();
						$hqcon->rollback();
						break;
					}
					
					$r['id'] = $server_pos_id;	//replace POS ID using main server POS ID
					
					// insert POS into Main Server
					$num_rows = $hqcon->exec("replace into pos ".mysql_insert_by_field($r, $pos_cols));	
					if($num_rows<=0){
						// show error
						print $hqcon->exec_sql_error();
						$this->store_counter_error($bid, $date, $counter_id, "Unable to insert POS. Branch ID#".$bid." Counter ID#".$counter_id." Date#".$date." Receipt#".$r['receipt_no']." Invoice#".$r['receipt_ref_no'], true);
						// Roll Back
						$con->rollback();
						$hqcon->rollback();
						break;
					}
					
					// sync table under pos
					$this->sync_under_pos_tables('pos_items', $r, $ss_pos_id, $server_pos_id);
					$this->sync_under_pos_tables('pos_payment', $r, $ss_pos_id, $server_pos_id);
					$this->sync_under_pos_tables("pos_delete_items", $r, $ss_pos_id, $server_pos_id);
					$this->sync_under_pos_tables("pos_mix_match_usage", $r, $ss_pos_id, $server_pos_id);
					$this->sync_under_pos_tables("pos_deposit", $r, $ss_pos_id, $server_pos_id);
					$this->sync_under_pos_tables("pos_member_point_adjustment", $r, $ss_pos_id, $server_pos_id);
					$this->sync_under_pos_tables("pos_items_changes", $r, $ss_pos_id, $server_pos_id);
					$this->sync_under_pos_tables("pos_credit_note", $r, $ss_pos_id, $server_pos_id);
					$this->sync_under_pos_tables("pos_goods_return", $r, $ss_pos_id, $server_pos_id);
					$this->sync_under_pos_tables("membership_promotion_mix_n_match_items", $r, $ss_pos_id, $server_pos_id);
					
					// member promotion data need to use other function, delete the entry then insert back
					$this->membership_promotion_data($r, $ss_pos_id, $server_pos_id);				
					
					// deposit need to write in separate function
					$this->sync_pos_deposit_status($r, $ss_pos_id, $server_pos_id);
					
					// sync_pos_receipt_cancel
					$this->sync_pos_receipt_cancel($r, $ss_pos_id, $server_pos_id);
					
					if($this->got_counter_error($bid, $date, $counter_id)){
						// this counter got error
						// Roll Back
						$con->rollback();
						$hqcon->rollback();
						break;
					}else{
						$con->exec("update pos set sync=1 where branch_id=$bid and counter_id=$counter_id and date=".ms($date)." and id=$ss_pos_id");
					}
					
					// Commit
					$hqcon->commit();
					$con->commit();
				}
				$con->sql_freeresult($q_pos);
				
				// update sales record
				$this->update_sales_record('pos', $bid, $date, $counter_id);
				
				
			}
		}
		
		// sync other pos related table, which is not under pos table
		foreach($all_counter_id_list as $counter_id){
			// Begin
			$con->beginTransaction();
			$hqcon->beginTransaction();
				
			$this->sync_other_pos_related_table('pos_drawer', true, $bid, $date, $counter_id);
			$this->sync_other_pos_related_table('pos_cash_history', true, $bid, $date, $counter_id);
			$this->sync_other_pos_related_table('pos_cash_domination', true, $bid, $date, $counter_id);
			
			// Commit
			$hqcon->commit();
			$con->commit();
		}
		
		// check whether this date already finalised
		if($this->is_finalized($bid, $date)){	// found finalised
			//print "Counter Collection already finalised.\n";
			$msg = "Counter Collection $date has been finalised while the sales is syncing, the reports may show wrong data, please refinalise it.";
			print $msg."\n";
			$this->insert_finalised_error($bid, $date, $all_counter_id_list, $msg);
			return;
		}
	}
	
	private function sync_pos_counter_collection_tracking($bid, $date, $counter_id){
		global $con, $hqcon;
		
		print "Sync POS Counter Collection Tracking ...\n";
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		$table = 'pos_counter_collection_tracking';
		
		// data
		$q1 = $con->query("select * from $table where branch_id=$bid and counter_id=$counter_id and date = ".ms($date)." and sync=0");
		$r = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$r){	// no data			
			return;	
		}
		
		// Check HQ Data
		$q2 = $hqcon->query("select * from $table where branch_id=$bid and counter_id=$counter_id and date = ".ms($date));
		$hq_data = $con->sql_fetchassoc($q2);
		$con->sql_freeresult($q2);
		
		$need_update = true;
		
		if($hq_data){	// HQ got Data 
			if(!$r['error']){	// Counter no error
				// Delete from Main Server, since counter no error
				$hqcon->exec("delete from $table where branch_id=$bid and counter_id=$counter_id and date=".ms($date));
				$need_update = false;
			}elseif($r['error'] == $hq_data['error']){	// same error, no need update
				$need_update = false;
			}
		}
		
		if($need_update){
			// get column
			$cols = $this->get_table_cols($table);
			
			$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $cols));
			if ($num_rows<=0){
				// show error
				print $hqcon->exec_sql_error();
				$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert $table: ".$date." Branch ID#".$bid." Counter ID#".$counter_id, true);
			}
		}
		
		// set sync=1
		$con->exec("update $table set sync=1 where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id");		

		print "Done.\n";
	}

	public function sync_pos_day_start($bid, $date, $counter_id){
		global $con, $hqcon;
		
		print "Sync POS Day Start...\n";
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		$table = 'pos_day_start';
		
		// data
		$q1 = $con->query("select * from $table where branch_id=$bid and counter_id=$counter_id and date = ".ms($date)." and sync=0");
		$r = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$r){	// no data			
			return;	
		}
		
		// get column
		$cols = $this->get_table_cols($table);
		
		$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $cols));
		if ($num_rows<=0){
			// show error
			print $hqcon->exec_sql_error();
			$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert $table: ".$date." Branch ID#".$bid." Counter ID#".$counter_id, true);
		}
		
		// set sync=1
		$con->exec("update $table set sync=1 where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id");		

		print "Done.\n";
	}

	public function sync_pos_day_end($bid, $date, $counter_id){
		global $con, $hqcon;
		
		print "Sync POS Day End...\n";
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		
		if(!$bid){
			print "Invalid Branch.\n";
			return;
		}
		if(!$counter_id){
			print "Invalid Counter.\n";
			return;
		}
		if(!$date){
			print "Invalid Date.\n";
			return;
		}
		
		$table = 'pos_day_end';
		
		// data
		$q1 = $con->query("select * from $table where branch_id=$bid and counter_id=$counter_id and date = ".ms($date)." and sync=0");
		$r = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$r){	// no data			
			return;	
		}
		
		// get column
		$cols = $this->get_table_cols($table);
		
		$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $cols));
		if ($num_rows<=0){
			// show error
			print $hqcon->exec_sql_error();
			$this->store_counter_error($bid, $date, $counter_id, "Unable to Insert $table: ".$date." Branch ID#".$bid." Counter ID#".$counter_id, true);
		}
		
		// set sync=1
		$con->exec("update $table set sync=1 where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id");		

		print "Done.\n";
	}
	
	public function clear_old_data(){
		global $con;
		
		// Check Last Clear Data Date
		$filename = dirname(__FILE__)."/clear_old_data.txt";
		//print "filename = $filename\n";
		
		$last_clear_date = '';
		if(file_exists($filename)){
			$last_clear_date = trim(file_get_contents($filename));
			if($last_clear_date){
				$last_clear_date = date("Y-m-d", strtotime($last_clear_date));
			}
		}
		
		print "Last Clear Date: ".$last_clear_date."\n";
		if($last_clear_date && strtotime($last_clear_date) >= strtotime(date("Y-m-d")))	return;
		
		// Check min data
		$con->query("select min(date) as min_pos_date from pos");
		$p = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($p){
			$min_pos_date = $p['min_pos_date'];
			print "Min POS Date: $min_pos_date\n";
			
			$clear_before_date = $this->clear_before_date;
			if(strtotime($min_pos_date) < strtotime($clear_before_date)){
				$clear_before_date = date("Y-m-d", strtotime("+1 month", strtotime($min_pos_date)));
				
				if(strtotime($clear_before_date) > strtotime($this->clear_before_date)){
					$clear_before_date = $this->clear_before_date;
				}
				
			}
			
			print "Clear Data before ".$clear_before_date."\n";
			
			// Delete Global Data
		
			// Begin
			//$con->beginTransaction();
			
			$con->exec("delete from membership_points where date<".ms($clear_before_date));
			$con->exec("delete from sku_items_temp_price where lastupdate<".ms($clear_before_date));
			$con->exec("delete from sku_items_temp_price_history where added_date<".ms($clear_before_date));
			
			// Commit
			//$con->commit();
			
			// Delete Data without need check finalise
			// Begin
			//$con->beginTransaction();
			
			$con->exec("delete from pos_error where date<".ms($clear_before_date));
			$success = $con->exec("delete from pos_transaction_audit_log where date<".ms($clear_before_date));
			//if(!$success)	print $con->exec_sql_error();
			
			$con->exec("delete from pos_transaction_ejournal where date<".ms($clear_before_date));
			$con->exec("delete from pos_transaction_clocking_log where counter_date<".ms($clear_before_date));
			$con->exec("delete from pos_user_log where counter_date<".ms($clear_before_date));
			$con->exec("delete from pos_counter_collection_tracking where date<".ms($clear_before_date));
			
			// Commit
			//$con->commit();
			
			// Delete Data with need check finalise
			// Begin
			//$con->beginTransaction();
			$con->exec("delete from pos_delete_items where date<".ms($clear_before_date));
			$con->exec("delete from pos_mix_match_usage where date<".ms($clear_before_date));
			$con->exec("delete from pos_deposit where date<".ms($clear_before_date));
			$con->exec("delete from pos_member_point_adjustment where date<".ms($clear_before_date));
			$con->exec("delete from pos_items_changes where date<".ms($clear_before_date));
			$con->exec("delete from pos_credit_note where date<".ms($clear_before_date));
			$con->exec("delete from pos_goods_return where date<".ms($clear_before_date));
			$con->exec("delete from membership_promotion_mix_n_match_items where date<".ms($clear_before_date));
			$con->exec("delete from membership_promotion_items where date<".ms($clear_before_date));
			$con->exec("delete from pos_deposit_status where date<".ms($clear_before_date)." and deposit_date<".ms($clear_before_date));
			$con->exec("delete from pos_deposit_status_history where pos_date<".ms($clear_before_date)." and deposit_pos_date<".ms($clear_before_date));
			$con->exec("delete from pos_receipt_cancel where date<".ms($clear_before_date));
			$con->exec("delete from pos_drawer where date<".ms($clear_before_date));
			$con->exec("delete from pos_cash_history where date<".ms($clear_before_date));
			$con->exec("delete from pos_cash_domination where date<".ms($clear_before_date));
			$con->exec("delete from pos_payment where date<".ms($clear_before_date));
			$con->exec("delete from pos_items where date<".ms($clear_before_date));
			$con->exec("delete from pos where date<".ms($clear_before_date));
			// Commit
			//$con->commit();
		}
		
		file_put_contents($filename, date("Y-m-d"));
		print "Clear Data Done.\n";
	}
	
	private function upload_pos_counter_collection_configuration(){
		global $con, $hqcon;
		
		print "Sync Counter Configuration ...\n";
		// Begin
		$con->beginTransaction();
		$hqcon->beginTransaction();
		
		$q1 = $con->query("select * from pos_counter_collection_configuration where sync = 0");
		
		while($r = $con->sql_fetchassoc($q1)){
			unset($r['sync']); // main server doesn't have this field
			
			$num_rows = $hqcon->exec("replace into pos_counter_collection_configuration ".mysql_insert_by_field($r));
			
			if($num_rows > 0){
				$con->exec("update pos_counter_collection_configuration set sync=1 where branch_id = ".mi($r['branch_id'])." and counter_id = ".mi($r['counter_id']));
			}
		}
		
		$hqcon->commit();
		$con->commit();
		
		print "Done.\n";
	}
}
?>