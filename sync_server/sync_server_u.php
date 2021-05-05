<?php
/*
10/21/2011 10:47:42 AM Alex
- created

11/9/2011 10:47:58 AM Alex
- match data sync with frontend

12/14/2011 10:22:47 AM Alex
- change match with latest database column

12/16/2011 11:55:45 AM Alex
- fix set sync=1 when pos_id changed
- fix pos_goods_return unable to get pos_id from hq server

2/3/2012 3:55:53 PM Alex
- change all table to update data according to table column name except 'sync' name
- simplify the duplicate function 

2/09/2012 12:02:00 PM Kee Kee
- sync pos_deposit table and pos_deposit_status table

3/6/2012 4:04:42 PM Alex
- add transaction_sync when sync pos

3/7/2012 12:16:17 PM Alex
- temptorarily fix transaction_sync problem until 146 version 

3/16/2012 2:42:26 PM Alex
- add checking to avoid duplicate task of cronjob
- fix pos_goods_return get id bugs

5/18/2012 1:42:00 PM Kee Kee
- Compatible with 151 version

07/06/2012 11:39:00 AM Kee Kee
- COmpatible with 153 beta version

08/08/2012 9:18 AM Kee Kee
- Compatible with 166 beta version

11/01/2012 4:45 PM Kee Kee
- after sync the pos to sync server or server check again cancel status (to avoid the pos after cancel not sync to server)

11/27/2012 3:21 PM Kee Kee
- check sys_setting table is it exists in sync_server database or not, if not exists, create tables for example (pos, pos_items,counter_status,etc)

03/05/2013 10:55 AM Kee Kee
- sync pos_error(counter error)

7/1/2013 5:26 PM Andy
- Enhance to able to connect to mysql.sock2

03/06/2015 3:28 PM Dingren
- add sync_under_pos_tables("pos_items_changes");
- add sync_under_pos_tables("pos_credit_note");

03/14/2016 5:29PM Kee Kee
- Sync pos_transaction_audit_log

03/17/2016 10:44AM Kee Kee
- Sync pos_transaction_ejournal

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.

02/23/2017 11:13 AM Kee Kee
- Update message into pos_finalised_error table after sync sales and found counter collection has finalized.

03/06/2017 13:44 PM Kee Kee
- Fixed Item has sold at counter but in the serial number listing still show 'Available' issue

04/07/2017 10:55 AM
- Update total Record into counter_sales_record into server
- Update Error Message/Warning Message to server
- Fixed missing customer information into pos_items_sn

05/17/2017 16:30 PM Kee Kee
Fixed missing sync pos_mix_match_usage data from Sync Server to counter

05/18/2017 10:57 AM Kee Kee
- Sync pos_transaction_clocking_log date to HQ Server

05/22/2017 9:38 AM Kee Kee
- Changed "counter_sales_record" to "pos_transaction_counter_sales_record

06/05/2017 11:39 AM Kee Kee
- Fixed get pos_id with 0 when sync pos_goods_return
- Fixed get a num rows > 0  after failed insert pos_goods_return into HQ server 
- Fixed get a num rows > 0  after failed insert sn_info into HQ server 
- Fixed missing column id when sync pos_transaction_ejournal and pos_transaction_audit_log

06/15/2017 9:18 AM Kee Kee
- Fixed failed to sync goods return when found return_branch_id = null value

6/20/2017 4:29 PM Justin
- Enhanced change sync audit log and ejournal sync every 30 minutes instead of every mintue.
- Enhanced to change branch BDE to sync minimum from 3 to 6 hours and maximum 5 to 8 hours.
- Enhanced to always sync up sales after 10PM for branch BDE.

8/28/2017 1:22 PM Keekee
- Sync pos_transaction_clocking_log date to HQ Server

09/25/2017 11:42 PM Kee Kee
- Enable sync clocking log
*/

//localhost/Sync Server => $con
//Main Server = $hqcon
define('TERMINAL',1);
define('DISP_ERR',1);

ini_set('memory_limit', '512M');
print "Sync Date:".date("Y-m-d H:i:s")."\n";
print "Starting memory:".memory_get_usage()."\n";

require("pdo_db.php");
require("function.php");
require("config.php");

@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
if (count($exec)>1)
{
	print date("[H:i:s m.d.y]")." Another process is already running\n";
	print_r($exec);
	exit;
}

$arg = $_SERVER['argv'];
array_shift($arg);

while($a = array_shift($arg))
{
	switch ($a)
	{
		case "-setup":
			prepare_database();
			exit;
		case "-fix":
			fix_transaction_sync_problem();
			exit;
		default:
			die("Unknown option: $a\n");
	}
}
prepare_database(true);

if(strpos($db_default_connection[0], 'unix_socket') !== false){
	$con=connect_db("mysql:dbname=sync_server;".$db_default_connection[0], $db_default_connection[1], $db_default_connection[2]);	// use by soc2
}else{
	$con=connect_db("mysql:dbname=sync_server;host=$db_default_connection[0]", $db_default_connection[1], $db_default_connection[2]);	// normal use
}

$hqcon=connect_db("mysql:dbname=$hq_db_default_connection[3];host=$hq_db_default_connection[0]", $hq_db_default_connection[1],$hq_db_default_connection[2]); 
if (!$con || !$hqcon)	die("Unable connect to server");

//start update tables
@include_once('maintenance.php');

$sync_server = new sync_server();
print "\nEnding memory:".memory_get_usage()."\n";
print "\nFinish Sync....\n";

function connect_db($server, $u, $p)
{
	$conn = new pdo_db($server, $u, $p);
	if(!$conn->resource_obj)
	{
		print date("[H:i:s m.d.y] ");
		print("Error: Could not connect to database $db@$server\n");
		return false;
	}
	return $conn;
}

function check_processing_time($time_from='0000', $time_to='2359'){
	//sample
	$cur_time = date("Hi");
	
	return ($time_from<=$cur_time && $cur_time<=$time_to);
}

function fix_transaction_sync_problem(){
	global $con,$db_default_connection;
	$con=connect_db("mysql:dbname=sync_server;host=$db_default_connection[0]", $db_default_connection[1], $db_default_connection[2]);
	$con->beginTransaction();
	$row = $con->exec("update pos set transaction_sync=1 where transaction_sync=0");
	print "$row effected.\n";
	$con->commit();
}

function prepare_database($check_database=false){
	global $con,$db_default_connection;
	
	print "Checking database in server ($db_default_connection[0])\n";
	
	if(strpos($db_default_connection[0], 'unix_socket') !== false){
		$con=new PDO("mysql:".$db_default_connection[0], $db_default_connection[1], $db_default_connection[2]);	// use by sock2
	}else{
		$con=new PDO("mysql:host=$db_default_connection[0]", $db_default_connection[1], $db_default_connection[2]);	// normal use
		
	}
	
	
	if ($db_default_connection[1] == "root"){
		$con->exec("create database if not exists sync_server");	//prefix database name
		$con->exec("grant all privileges on sync_server.* to 'arms_slave'@'localhost' identified by 'arms_slave'");	//prefix database name
		$con->exec("grant all privileges on sync_server.* to 'arms_slave'@'%' identified by 'arms_slave'");	//prefix database name
		$con->exec("FLUSH PRIVILEGES");	//prefix database name
	}
	$con->exec("use sync_server");	//prefix database name
	if($check_database){
		$rid = $con->query("explain sys_setting");
		if($rid){
			return;
		}
	}
	$con->beginTransaction();
	//start from maintenance 100
	//=========================================================================use Pos primary key
	$pos_db="CREATE TABLE if not exists `pos` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `id` int(11) NOT NULL DEFAULT '0', `cashier_id` int(11) DEFAULT NULL, `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `amount` double DEFAULT NULL, `member_no` char(16) COLLATE latin1_general_ci DEFAULT NULL, `race` char(5) COLLATE latin1_general_ci DEFAULT NULL, `receipt_no` int(11) DEFAULT NULL, `cancel_status` int(11) DEFAULT '0', `pos_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `date` date NOT NULL DEFAULT '0000-00-00', `amount_tender` double DEFAULT NULL, `amount_change` double DEFAULT NULL, `point` int(11) DEFAULT '0', `redeem_points` int(11) DEFAULT '0', `receipt_remark` text COLLATE latin1_general_ci, `prune_status` tinyint(1) DEFAULT '0',`sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`,`id`), KEY `pos_time` (`pos_time`), KEY `member_no` (`member_no`), KEY `cancel_status` (`cancel_status`), KEY `date` (`date`), KEY `branch_date_active` (`branch_id`,`date`,`cancel_status`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($pos_db);
	
	$pos_items_db="	CREATE TABLE if not exists `pos_items` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `id` int(11) NOT NULL DEFAULT '0', `pos_id` int(11) NOT NULL DEFAULT '0', `item_id` int(11) NOT NULL DEFAULT '0', `sku_item_id` int(11) DEFAULT NULL, `barcode` char(13) COLLATE latin1_general_ci DEFAULT NULL, `qty` double DEFAULT NULL, `price` double DEFAULT NULL, `discount` double DEFAULT NULL, `return_by` int(11) DEFAULT NULL, `return_qty` int(11) DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `open_price_by` int(11) DEFAULT '0', `item_discount_by` int(11) DEFAULT NULL, `mprice_type` char(10) COLLATE latin1_general_ci DEFAULT NULL, `trade_discount_code` char(6) COLLATE latin1_general_ci DEFAULT NULL, `redeem_points` int(11) DEFAULT '0', `promotion_id` int(11) DEFAULT NULL, `cb_code` char(6) COLLATE latin1_general_ci NOT NULL, `cb_profit` double NOT NULL, `cb_discount` char(10) COLLATE latin1_general_ci NOT NULL, `cb_use_net` enum('yes','no') COLLATE latin1_general_ci DEFAULT 'no', `cb_net_bearing` double NOT NULL, `remark` text COLLATE latin1_general_ci, PRIMARY KEY (`branch_id`,`counter_id`,`date`,`pos_id`,`item_id`), KEY `sku_item_id` (`sku_item_id`), KEY `branch_id_n_date_n_open_price_by` (`branch_id`,`date`,`open_price_by`), KEY `branch_id_n_date_n_item_discount_by` (`branch_id`,`date`,`item_discount_by`), KEY `branch_id_n_sku_item_id_n_date` (`branch_id`,`sku_item_id`,`date`), KEY `branch_id_and_sku_item_id` (`branch_id`,`sku_item_id`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($pos_items_db);
	
	$pos_payment_db="CREATE TABLE if not exists `pos_payment` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `id` int(11) NOT NULL DEFAULT '0', `pos_id` int(11) NOT NULL DEFAULT '0', `type` char(30) COLLATE latin1_general_ci DEFAULT NULL, `remark` char(50) COLLATE latin1_general_ci DEFAULT NULL, `amount` double DEFAULT NULL, `date` date NOT NULL DEFAULT '0000-00-00', `changed` int(11) DEFAULT '0', `adjust` int(11) DEFAULT '0', `approved_by` int(11) NOT NULL DEFAULT '0', `more_info` text COLLATE latin1_general_ci, PRIMARY KEY (`branch_id`,`counter_id`,`date`,`pos_id`,`id`), KEY `date` (`date`), KEY `approved_by_n_date_n_branch_n_counter` (`approved_by`,`date`,`branch_id`,`counter_id`), KEY `type_n_remark_n_branch` (`type`,`remark`,`branch_id`), KEY `payment_type` (`branch_id`,`date`,`counter_id`,`pos_id`,`type`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($pos_payment_db);
	
	$pos_delete_items_db="CREATE TABLE `pos_delete_items` ( `branch_id` int(11) DEFAULT NULL, `counter_id` int(11) DEFAULT NULL, `date` date DEFAULT NULL, `id` int(11) NOT NULL DEFAULT '0', `pos_id` int(11) NOT NULL DEFAULT '0', `sku_item_id` int(11) DEFAULT NULL, `barcode` char(13) COLLATE latin1_general_ci DEFAULT NULL, `qty` double DEFAULT NULL, `price` double DEFAULT NULL, `delete_by` int(11) DEFAULT NULL, PRIMARY KEY (`branch_id`,`counter_id`,`date`,`pos_id`,`id`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($pos_delete_items_db);

	$pos_mix_match_usage_db="CREATE TABLE if not exists `pos_mix_match_usage` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `pos_id` int(11) NOT NULL DEFAULT '0', `id` int(11) NOT NULL DEFAULT '0', `remark` char(50) COLLATE latin1_general_ci DEFAULT NULL, `amount` double DEFAULT NULL, `group_id` int(11) DEFAULT NULL, `promo_id` int(11) DEFAULT NULL, `more_info` text COLLATE latin1_general_ci, `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`date`,`counter_id`,`pos_id`,`id`), KEY `remark` (`remark`), KEY `group_id_n_promo_id_n_date` (`group_id`,`promo_id`,`date`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($pos_mix_match_usage_db);

//============================================================special case: use own sync not use pos primary key
	$pos_goods_return_db="CREATE TABLE `pos_goods_return` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `pos_id` int(11) NOT NULL DEFAULT '0', `item_id` int(11) NOT NULL DEFAULT '0', `return_counter_id` int(11) DEFAULT NULL, `return_date` date DEFAULT NULL, `return_pos_id` int(11) DEFAULT NULL, `return_item_id` int(11) DEFAULT NULL, `return_receipt_no` char(50) COLLATE latin1_general_ci DEFAULT NULL, `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`,`pos_id`,`item_id`), KEY `date` (`date`), KEY `return_info` (`branch_id`,`return_counter_id`,`return_date`,`return_pos_id`,`return_item_id`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($pos_goods_return_db);

	$membership_promotion_items_db="CREATE TABLE if not exists `membership_promotion_items` ( `id` int(11) NOT NULL AUTO_INCREMENT, `branch_id` int(11) NOT NULL DEFAULT '0', `card_no` char(20) DEFAULT NULL, `promo_id` int(11) DEFAULT NULL, `pos_id` int(11) DEFAULT NULL, `counter_id` int(11) NOT NULL DEFAULT '0', `sku_item_id` int(11) DEFAULT NULL, `qty` double DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`,`id`), KEY `card_no` (`card_no`), KEY `promo_id` (`promo_id`), KEY `sku_item_id` (`sku_item_id`), KEY `date` (`date`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
	$con->exec($membership_promotion_items_db);

	$pos_items_sn_db="CREATE TABLE if not exists `pos_items_sn` ( `id` int(11) NOT NULL AUTO_INCREMENT, `branch_id` int(11) NOT NULL DEFAULT '0', `pos_id` int(11) DEFAULT NULL, `pos_item_id` int(11) DEFAULT NULL, `pos_branch_id` int(11) DEFAULT NULL, `date` date DEFAULT NULL, `counter_id` int(11) DEFAULT NULL, `sku_item_id` int(11) DEFAULT NULL, `located_branch_id` int(11) DEFAULT NULL, `serial_no` varchar(20) DEFAULT NULL, `status` tinyint(1) DEFAULT '0', `active` tinyint(1) DEFAULT '1', `added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `created_by` int(11) DEFAULT NULL, `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', PRIMARY KEY (`branch_id`,`id`), UNIQUE KEY `sku_item_id` (`sku_item_id`,`serial_no`), KEY `serial_no_idx` (`serial_no`), KEY `lbid_sid_sn_idx` (`located_branch_id`,`sku_item_id`,`serial_no`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
	$con->exec($pos_items_sn_db);
		
	$sn_info_db="CREATE TABLE if not exists `sn_info` ( `pos_id` int(11) NOT NULL DEFAULT '0', `item_id` int(11) NOT NULL DEFAULT '0', `branch_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `counter_id` int(11) NOT NULL DEFAULT '0', `sku_item_id` int(11) DEFAULT NULL, `serial_no` varchar(20) DEFAULT NULL, `nric` varchar(20) NOT NULL, `name` varchar(100) DEFAULT NULL, `address` text, `contact_no` varchar(15) DEFAULT NULL, `email` varchar(40) DEFAULT NULL, `warranty_expired` date DEFAULT NULL, `active` tinyint(1) DEFAULT '1', `approved_by` int(11) DEFAULT NULL, `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`pos_id`,`item_id`,`branch_id`,`date`,`counter_id`), KEY `serial_no_idx` (`serial_no`), KEY `sid_sn_idx` (`sku_item_id`,`serial_no`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
	$con->exec($sn_info_db);

	$membership_promotion_mix_n_match_items_db="CREATE TABLE if not exists `membership_promotion_mix_n_match_items` ( `id` int(11) NOT NULL AUTO_INCREMENT, `branch_id` int(11) NOT NULL DEFAULT '0', `card_no` char(20) COLLATE latin1_general_ci NOT NULL, `real_promo_id` int(11) DEFAULT NULL, `promo_id` int(11) DEFAULT NULL, `group_id` int(11) DEFAULT NULL, `pos_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `qty` double DEFAULT NULL, `amount` double DEFAULT NULL, `date` date NOT NULL DEFAULT '0000-00-00', `used` tinyint(1) DEFAULT NULL, `added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`date`,`counter_id`,`pos_id`,`id`), KEY `branch_n_counter_n_date_n_pos` (`branch_id`,`counter_id`,`date`,`pos_id`), KEY `branch_id_n_promo_id_n_group_id_n_cardno_n_date` (`branch_id`,`promo_id`,`group_id`,`card_no`,`date`) ) ENGINE=MyISAM AUTO_INCREMENT=155 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($membership_promotion_mix_n_match_items_db);
	
//=========================================================================No Pos id
	$pos_drawer_db="CREATE TABLE if not exists `pos_drawer` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `id` int(11) NOT NULL DEFAULT '0', `user_id` int(11) DEFAULT NULL, `timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `date` date NOT NULL DEFAULT '0000-00-00', `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`,`id`), KEY `date` (`date`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($pos_drawer_db);
	
	$pos_cash_domination_db="CREATE TABLE if not exists `pos_cash_domination` ( `branch_id` int(11) NOT NULL DEFAULT '0', `id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `user_id` int(11) DEFAULT NULL, `data` text COLLATE latin1_general_ci, `odata` text COLLATE latin1_general_ci, `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, `date` date NOT NULL DEFAULT '0000-00-00', `clear_drawer` tinyint(1) DEFAULT '0', `curr_rate` text COLLATE latin1_general_ci, `ocurr_rate` text COLLATE latin1_general_ci, `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`,`id`), KEY `date` (`date`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($pos_cash_domination_db);
	
	$pos_receipt_cancel_db="CREATE TABLE if not exists `pos_receipt_cancel` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `id` int(11) NOT NULL DEFAULT '0', `receipt_no` int(11) DEFAULT NULL, `cancelled_by` int(11) DEFAULT NULL, `cancelled_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `verified_by` int(11) DEFAULT NULL, `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`,`id`), KEY `date` (`date`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($pos_receipt_cancel_db);
	
	$pos_cash_history_db ="CREATE TABLE if not exists `pos_cash_history` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) DEFAULT NULL, `collected_by` int(11) DEFAULT NULL, `type` char(10) COLLATE latin1_general_ci DEFAULT NULL, `amount` double DEFAULT NULL, `oamount` double DEFAULT NULL, `timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `remark` char(16) COLLATE latin1_general_ci DEFAULT NULL, `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`,`id`), KEY `date` (`date`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($pos_cash_history_db);
	
	$pos_counter_collection_tracking_db ="CREATE TABLE if not exists `pos_counter_collection_tracking` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `finalized` tinyint(1) DEFAULT '0', `error` char(100) COLLATE latin1_general_ci DEFAULT NULL, `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, `solved` tinyint(1) DEFAULT '0', `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`), KEY `date` (`date`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($pos_counter_collection_tracking_db);
	
	$counter_status_db ="CREATE TABLE if not exists `counter_status` ( `branch_id` int(11) NOT NULL DEFAULT '0', `id` int(11) NOT NULL DEFAULT '0', `ip` char(15) COLLATE latin1_general_ci DEFAULT NULL, `status` char(20) COLLATE latin1_general_ci DEFAULT NULL, `lastping` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, `user_id` int(11) DEFAULT NULL, `lasterr` char(50) COLLATE latin1_general_ci DEFAULT NULL, `revision` int(11) DEFAULT NULL, `min_tmp_trigger_log_row` int(11) NOT NULL DEFAULT '0', `min_tmp_member_trigger_log_row` int(11) NOT NULL DEFAULT '0', `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`id`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($counter_status_db);
	
	$tmp_check_finalized_db ="CREATE TABLE if not exists `tmp_check_finalized` ( `branch_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', PRIMARY KEY (`branch_id`,`date`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
	$con->exec($tmp_check_finalized_db);
	
	$sys_setting_db="CREATE TABLE `sys_setting` ( `version` int(10) DEFAULT '0' COMMENT 'System Setting' ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
	$con->exec($sys_setting_db);
	
	$con->commit();

	print "Finish create table\n";
}

function alter_table(){
	global $con;
	$con->beginTransaction();
	//start alter table
	$con->query("alter table pos_goods_return add return_receipt_no integer",false,false);
	$con->commit();
}

class sync_server{

	var $old_pos_id;
	var $branch_id;
	var $counter_id;
	var $receipt_no;
	var $pos_db;
	var $new_pos_id;
	var $sync_server_filter;
	var $got_error=false;
	var $erromessage = array();

	function __construct()
	{
		global $con, $hqcon;
		print "\nAltering table...";
//		alter_table();
		
		// check if this is jkb sync server
		$jkb_bid = 36; // actual branch ID get from main server
		//$jkb_bid = 47; // maximus dev bid
		// check if this server got the sales for jkb
		$q1 = $con->query("select * from pos where branch_id = ".mi($jkb_bid)." limit 1");
		$got_sales = $con->sql_numrows($q1);
		$con->sql_freeresult($q1);
		
		// means it got sales but not sure whether it is jkb or not, connect back to main server to verify the bid
		$is_jkb_ss = false;
		if($got_sales){
			$q1 = $hqcon->query("select * from branch where id = ".mi($jkb_bid)." and code = 'JKB' and (company_no = '317041-W' or added = '2016-11-21 10:19:02')");
			//$q1 = $hqcon->query("select * from branch where id = ".mi($jkb_bid)." and code = 'DEV' and (company_no = '23423' or added = '2015-09-21 16:07:16')");
			if($hqcon->sql_numrows($q1) > 0) $is_jkb_ss = true;
			$hqcon->sql_freeresult($q1);
		}
		
		if($is_jkb_ss){
			$this->run_bid = 36; // jkb sales
			//$this->run_bid = 47; // maximus dev bid
			$this->branch_filter = " and branch_id = ".mi($this->run_bid);
			$this->sync_up_all();
			
			//return;	// stop sync sales for BDE
			
			$this->run_bid = 45; // bde sales
			//$this->run_bid = 42; // maximus gurun bid
			
			$curr_hour = mi(date("H"));
			
			if($curr_hour < 22){ // do below while it is not 10PM
				// mark current time
				$curr_time = time();
				// mark current time - 6 hours
				$min_sync_time = strtotime("-6 hour", $curr_time);
				// check whether got sales is older than 6 hours and not yet sync up
				$q1 = $con->query("select min(pos_time) as min_pos_time from pos where branch_id=".mi($this->run_bid)." and sync=0 and pos_time<".ms(date("Y-m-d H:i:s", $min_sync_time))." limit 1");
				$tmp = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				// no sales is before 6 hours
				if(!$tmp['min_pos_time'])	return;
				
				// get the pos time from this pos
				$min_pos_time = strtotime($tmp['min_pos_time']);
				$allow_sync = false;
				if($curr_time - $min_pos_time > 28800){
					// sales cannot sync up more than 8 hours, need to force sync
					$allow_sync = true;
				}
				
				// not allow sync, need roll
				if(!$allow_sync){
					// roll dice
					$n = rand(0, 20);
					
					// we only allow to sync if result is 10
					if($n != 10)	return;
				}
			}
			
			$this->branch_filter = " and branch_id = ".mi($this->run_bid);
			$prms = array();
			$prms['force_sync'] = 1;
			$this->sync_up_all($prms);
		}else{ // else just sync all record regardless branch filter
			$this->run_bid = "";
			$this->branch_filter = "";
			$this->sync_up_all();
		}
		
	}
	
	function sync_pos()
	{
		global $con,$hqcon;
		
		$table="pos";
		$sql="select * from $table where sync=0 and transaction_sync=1".$this->branch_filter;
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal POS: ".$total_rows."\n";
		$this->update_sales_record($table);
		if ($total_rows<=0){ 
			print "Skip...\n";
			$this->checking_missing_sales_record($table);
			return;
		}
		
		$column_names=$this->field_names_without_sync($table);
		$check_dateFinalize = array();		
		while($r=$con->sql_fetchassoc($rid))
		{
			print $total_rows."\r";

			$this->old_pos_id = $r['id'];
			$this->branch_id = $r['branch_id'];
			$this->counter_id = $r['counter_id'];
			$this->receipt_no = $r['receipt_no'];
			$this->invoice_no = $r['receipt_ref_no'];
			$this->pos_db = $r['date'];
			if(!isset($check_dateFinalize[$r['date']][$r['branch_id']][$r['counter_id']])) $check_dateFinalize[$r['date']][$r['branch_id']][$r['counter_id']] = 1;
			if($server_pos_id = $this->get_pos_id_from_server($this->branch_id,$this->counter_id, $this->pos_db, $this->old_pos_id, $this->receipt_no)){
	            $r['id'] = $server_pos_id;
			}else{
	            $r['id'] = $this->get_new_pos_id_from_server($this->branch_id,$this->counter_id,$this->pos_db);
			}
			$this->new_pos_id = $r['id'];
			
			$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r, $column_names));	

			if ($num_rows<=0){
				$this->_die_("POS: Unable connect to server",true,array('branch_id'=>$this->branch_id,"counter_id"=>$this->counter_id,"date"=>$this->pos_db));	
			}

			$this->sync_server_filter="where branch_id=$this->branch_id and counter_id=$this->counter_id and pos_id=$this->old_pos_id and date=".ms($this->pos_db);
			$this->main_server_filter="where branch_id=$this->branch_id and counter_id=$this->counter_id and pos_id=$this->new_pos_id and date=".ms($this->pos_db);

			$this->sync_under_pos_tables("pos_items");
			$this->sync_under_pos_tables("pos_payment");
			$this->sync_under_pos_tables("pos_delete_items");
			$this->sync_under_pos_tables("pos_mix_match_usage");
			$this->sync_under_pos_tables("pos_deposit");
			$this->sync_under_pos_tables("pos_member_point_adjustment");
			$this->sync_under_pos_tables("pos_items_changes");
			$this->sync_under_pos_tables("pos_credit_note");

			if (!$this->got_error)
			{
				$pid = $con->query("select * from pos where branch_id=".mi($this->branch_id)." and counter_id=".mi($this->counter_id)." and date=".ms($this->pos_db)." and id = ".$this->old_pos_id);
				$pos = $con->sql_fetchassoc($pid);
				$con->sql_freeresult($pid);
				if($r['cancel_status']==$pos['cancel_status'])
				{
					$num_rows = $con->exec("update $table set sync = 1 where branch_id=".mi($this->branch_id)." and counter_id=".mi($this->counter_id)." and date=".ms($this->pos_db)." and id = ".$this->old_pos_id);
				}				
			}
			else
				$this->got_error=0;

			$total_rows--;
		}

		if($check_dateFinalize)
		{
			foreach($check_dateFinalize as $date=>$branch)
			{
				foreach($branch as $branchId=>$counter)
				{
					$ret = $hqcon->query("select * from pos_finalized where branch_id = ".ms($branchId)." and date = ".ms($date)." and finalized = 1");
					$finalize = $hqcon->sql_numrows($ret);
					$hqcon->sql_freeresult($ret);
					if($finalize>0)
					{
						foreach($counter as $counterId=>$idx)
						{
							$upd['branch_id'] = $branchId;
							$upd['counter_id'] = $counterId;
							$upd['error_msg'] = "Please re-finalize $date sales.";
							$upd['date'] = $date;
							$upd['added'] = date("Y-m-d H:i:s");
							$hqcon->exec("replace into pos_finalised_error ".mysql_insert_by_field($upd));
							unset($upd);
						}
					}
				}
			}
			unset($check_dateFinalize);
		}
		print "POS Sync Finish";
		$this->checking_missing_sales_record("pos");
		unset($sql,$r);
	}
	
	function sync_under_pos_tables($table)
	{
		global $con,$hqcon;
		if ($this->got_error)	return;
		
		$sql="select * from $table $this->sync_server_filter";
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		if ($total_rows<=0)	return;
		
		$column_names=$this->field_names_without_sync($table);
		
		while($r=$con->sql_fetchassoc($rid)){
			$r['pos_id'] = $this->new_pos_id;
			//$column_name=array_keys($r);
			$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r,$column_names));
			if ($num_rows<=0)	{
				$this->_die_("Failed: ".$this->pos_db.": upload ".$table." Branch ID#".$this->branch_id." Counter ID#".$this->counter_id." Receipt#".$this->receipt_no." Invoice#".$this->invoice_no.", item ID#".$r['id']."\n", false, array('branch_id'=>$this->branch_id,"counter_id"=>$this->counter_id,"date"=>$this->pos_db));
			}
		}
		$con->sql_freeresult($rid);
		
		// verify no. of records written
		$sql="select count(*) as total from $table $this->main_server_filter";
		$rid=$hqcon->query($sql);
		$r=$hqcon->sql_fetchassoc($rid);

		if ($r['total']!=$total_rows) {
			$this->_die_("Error: ".$this->pos_db.": ".$table." Branch ID#".$this->branch_id." Counter ID#".$this->counter_id."  Receipt#".$this->receipt_no." Invoice#".$this->receipt_no.", Written rows not same.",false,array('branch_id'=>$this->branch_id,"counter_id"=>$this->counter_id,"date"=>$this->pos_db));
		}
		
		$hqcon->sql_freeresult($rid);
		unset($sql,$r);
	}
	
	function sync_sn_info()
	{
		global $con,$hqcon;

		$table="sn_info";
		$sql="select * from $table where sync=0".$this->branch_filter;
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal $table: ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}
		
		$column_names=$this->field_names_without_sync($table);
		
		while($r=$con->sql_fetchassoc($rid)){
			$posInfo = $this->get_pos_receipt_no_from_local($r['branch_id'],$r['counter_id'], $r['date'], $r['pos_id']);
			if(!is_array($posInfo) && $posInfo==0)
			{
				$this->_die_("Error: ".$r['date'].": sn_info Branch ID#".$r['branch_id']." Counter ID#".$r['counter_id']." POS ID#:".$r['pos_id']." cannot get receipt number",true,array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r['date']));
			}
			
			$r['pos_id'] = $this->get_pos_id_from_server($r['branch_id'],$r['counter_id'], $r['date'], $r['pos_id'],$posInfo['receipt_no']);
			
			if($r['pos_id']==0){
				$this->_die_("Error: ".$r['date'].": sn_info Branch ID#".$r['branch_id']." Counter ID#".$r['counter_id']." POS ID#:".$tmp_pos_id." Receipt No# ".$posInfo['receipt_no']." Invoice No# ".$posInfo['receipt_ref_no']." cannot found pos_id at Server\n",true,array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r['date']));
			}
			
			$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r,$column_names));
			if ($num_rows<=0)	{
				$this->_die_("Failed: ".$r['date'].": upload ".$table." Branch ID#".$r['branch_id']." POS ID#".$r['pos_id'].", item ID#".$r['id']."\n", false, array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r['date']));
			}
			else{
				$rp = $con->query("select prune_status, cancel_status from pos where branch_id = ".ms($r['branch_id'])." and counter_id = ".ms($r['counter_id'])." and date=".ms($r['date'])." and id = ".ms($r['pos_id']));
				$rpos = $con->sql_fetchassoc($rp);
				$con->sql_freeresult($rp);
				unset($rp);
				if($r['prune_status'] && $r['cancel_status'])
				{
					//SKIP to update
				}
				else{
					if($r['active'])
					{
						$hqcon->exec("update pos_items_sn set status = ".ms("Sold").",pos_id=".ms($r['pos_id']).",pos_item_id=".ms($r['item_id']).",pos_branch_id=".ms($r['branch_id']).", date=".ms($r['date']).", counter_id=".ms($r['counter_id']).", last_update = CURRENT_TIMESTAMP where located_branch_id = ".mi($r['branch_id'])." and sku_item_id = ".mi($r['sku_item_id'])." and serial_no = ".ms($r['serial_no']));
					}
					else{
						
						$hqcon->exec("update pos_items_sn set status = ".ms("Available").",pos_id='',pos_item_id='',pos_branch_id='', date='', counter_id='', last_update = CURRENT_TIMESTAMP where located_branch_id = ".mi($r['branch_id'])." and sku_item_id = ".mi($r['sku_item_id'])." and serial_no = ".ms($r['serial_no']));
					}
				}
				
				$con->exec("update $table set sync=1 where branch_id=".mi($r['branch_id'])." and date=".ms($r['date'])." and counter_id=".mi($r['counter_id'])." and pos_id=".mi($r['pos_id'])." and item_id=".mi($r['item_id']));
			}
		}
		$con->sql_freeresult($rid);
  }
	
	function sync_pos_goods_return()
	{
		global $con,$hqcon;
		$table="pos_goods_return";
		$sql="select * from $table where sync=0".$this->branch_filter;
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal $table: ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}
		
		$column_names=$this->field_names_without_sync($table);
		
		while($r=$con->sql_fetchassoc($rid))
		{
			$posInfo = $this->get_pos_receipt_no_from_local($r['branch_id'],$r['counter_id'], $r['date'], $r['pos_id']);
			if(!is_array($posInfo) && $posInfo==0)
			{
				$this->_die_("Error: ".$r['date'].": pos_goods_return Branch ID#".$r['branch_id']." Counter ID#".$r['counter_id']." POS ID#:".$r['pos_id']." cannot get receipt number",true,array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r['date']));
			}
			
			$tmp_pos_id=$r['pos_id'];
		
			$r['pos_id'] = $this->get_pos_id_from_server($r['branch_id'],$r['counter_id'], $r['date'], $r['pos_id'],$posInfo['receipt_no']);

			if($r['pos_id']==0){
				$this->_die_("Error: ".$r['date'].": pos_goods_return Branch ID#".$r['branch_id']." Counter ID#".$r['counter_id']." POS ID#:".$tmp_pos_id." Receipt No# ".$posInfo['receipt_no']." Invoice No# ".$posInfo['receipt_ref_no']." cannot found pos_id at Server\n",true,array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r['date']));
			}
			$return_bid = (trim($r['return_branch_id'])=="" || trim($r['return_branch_id'])==0)?$r['branch_id']:$r['return_branch_id'];
			$r['return_pos_id'] = $this->get_pos_id_from_server($return_bid,$r['return_counter_id'], $r['return_date'], $r['return_pos_id'],$r['return_receipt_no']);
			if(!$r['return_pos_id'])
			{
				$this->_die_("Warning: ".$r['date'].": ".$table." Branch ID#".$r['branch_id']." Counter ID#".$r['counter_id']." POS ID#".$r['pos_id']." cannot found return_pos_id at server.\n",false,array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r['date']));
				continue;
			}
			
			$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r,$column_names));
			$q1 = $hqcon->query("select * from pos_goods_return where branch_id = ".ms($r['branch_id'])." and counter_id = ".ms($r['counter_id'])." and item_id = ".ms($r['item_id'])." and date = ".ms($r['date'])." and pos_id=".ms($r['pos_id']));
			$hqresult = $hqcon->sql_fetchassoc($q1);
			$hqcon->sql_freeresult($q1);
			if (!$hqresult)	
			{
				$this->_die_("Failed: ".$r['date'].": upload ".$table." POS ID#".$r['pos_id'].",Branch ID#".$r['branch_id']." Counter ID#".$r['counter_id']." item ID#".$r['item_id'].", Return Receipt#".$r['return_receipt_no']."\n", false,array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r['date']));
			}
			else{
				// update S/N become inactive and available for next payment
				$sn = $hqcon->query("select * from sn_info where pos_id = ".mi($r['return_pos_id'])." and item_id = ".mi($r['return_item_id'])." and branch_id = ".mi($r['branch_id'])." and counter_id = ".mi($r['return_counter_id'])." and date = ".ms($r['return_date']));
	
				// here is where we do update on serial no. from frontend database and backend server
				if ($hqcon->sql_numrows($sn) > 0){
					while($sn_item = $hqcon->sql_fetchassoc($sn)){
	
						$hqcon->exec("update pos_items_sn set status = ".ms("Available").", pos_id='',pos_item_id='',pos_branch_id='', date='', counter_id='' where located_branch_id = ".mi($r['branch_id'])." and sku_item_id = ".mi($sn_item['sku_item_id'])." and serial_no = ".ms($sn_item['serial_no']));
						
						// update the S/N information become available from backend
						$hqcon->exec("update sn_info set active=0 where pos_id = ".mi($sn_item['pos_id'])." and item_id = ".mi($r['return_item_id'])." and branch_id = ".mi($r['branch_id'])." and counter_id = ".mi($r['return_counter_id'])." and date = ".ms($r['return_date']));
		
						// update the S/N information become available from frontend if it is return within the same counter
						$con->exec("update sn_info set active=0 where pos_id = ".mi($sn_item['pos_id'])." and item_id = ".mi($r['return_item_id'])." and branch_id = ".mi($r['branch_id'])." and counter_id = ".mi($r['return_counter_id'])." and date = ".ms($r['return_date']));
					}
					$hqcon->sql_freeresult($sn);
				}
				
				$con->exec("update $table set sync=1 where branch_id=".mi($r['branch_id'])." and date=".ms($r['date'])." and counter_id=".mi($r['counter_id'])." and pos_id=".mi($tmp_pos_id)." and item_id=".mi($r['item_id']));
			}
		}
		$con->sql_freeresult($rid);
	}
	
	function sync_pos_deposit_status()
	{
		global $con,$hqcon;
		$table = "pos_deposit_status";
		$sql="select * from $table where sync=0".$this->branch_filter;
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal $table: ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}
		
		$column_names=$this->field_names_without_sync($table);
		
		while($r=$con->sql_fetchassoc($rid)){
			if($r['branch_id'] && $r['counter_id'] && $r['pos_id'] && $r['receipt_no'] && $r['date'])
			{
				$r['pos_id'] = $this->get_pos_id_from_server($r['branch_id'],$r['counter_id'],$r['date'],$r['pos_id'],$r['receipt_no']);
			}
			$tmp_pos_id=$r['deposit_pos_id'];
			$r['deposit_pos_id'] =  $this->get_pos_id_from_server($r['deposit_branch_id'],$r['deposit_counter_id'],$r['deposit_date'],$r['deposit_pos_id'],$r['deposit_receipt_no']);
			
			if(!$r['deposit_pos_id']){
					$this->_die_("Warning: ".$r['deposit_date'].": ".$table." Deposit Branch ID#".$r["deposit_branch_id"]." Deposit Counter ID#".$r['deposit_counter_id']." Deposit POS ID#".$r['deposit_pos_id']." cannot found deposit_pos_id at server.\n",false,array('branch_id'=>$r['deposit_branch_id'],"counter_id"=>$r['deposit_counter_id'],"date"=>$r['deposit_date']));
					continue;
			}
			$is_last = false;
			$hqcon->query("select * from $table where deposit_branch_id = ".ms($r['deposit_branch_id'])." and deposit_counter_id = ".ms($r['deposit_counter_id'])." and  deposit_pos_id = ".ms($r['deposit_pos_id'])." and deposit_counter_id = ".ms($r['deposit_counter_id'])." and deposit_date = ".ms($r['deposit_date'])." and deposit_receipt_no = ".ms($r['deposit_receipt_no']));
			if($hqcon->sql_numrows()>0)
			{
				$tmp = $hqcon->sql_fetchassoc();
				if(strtotime($tmp['last_update'])>strtotime($r['last_update'])){
					$is_last = true;
				}
				//$hqcon->sql_freeresult();
			}
			
			if(!$is_last)
			{
				$num_rows =	$hqcon->exec("replace into $table ".mysql_insert_by_field($r,$column_names));
			}
			else{
				$num_rows = 1;
			}
			
			if ($num_rows<=0)
			{
				$this->_die_("Failed: ".$r['deposit_date'].": upload ".$table." Deposit Branch ID#".$r['deposit_branch_id']." Deposit Counter ID#".$r['deposit_counter_id']."  Deposit POS ID#".$r['deposit_pos_id'].", Deposit Receipt#".$r['deposit_receipt_no']."\n", false,array('branch_id'=>$r['deposit_branch_id'],"counter_id"=>$r['deposit_counter_id'],"date"=>$r['deposit_date']));
			}
			else
			{
				$con->exec("update $table set sync=1 where deposit_branch_id=".mi($r['deposit_branch_id'])." and deposit_date=".ms($r['deposit_date'])." and deposit_counter_id=".mi($r['deposit_counter_id'])." and deposit_pos_id=".mi($tmp_pos_id));
			}
		}
		$con->sql_freeresult($rid);
	}

	function sync_pos_deposit_status_history()
	{
		global $con,$hqcon;
		$table = "pos_deposit_status_history";
		$sql="select * from $table where sync=0".$this->branch_filter;
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal $table: ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}

		$column_names=$this->field_names_without_sync($table);
		while($r=$con->sql_fetchassoc($rid)){
			$ds = $r;
			$ds['pos_id'] = $this->get_pos_id_from_server($r['branch_id'],$r['counter_id'],$r['pos_date'],$r['pos_id'],$r['receipt_no']);
				
			$ds['deposit_pos_id'] = $this->get_pos_id_from_server($r['deposit_branch_id'],$r['deposit_counter_id'],$r['deposit_pos_date'],$r['deposit_pos_id'],$r['deposit_receipt_no']);
			
			if(!$ds['pos_id']){
					$this->_die_("Warning: ".$r['pos_date'].": ".$table." Branch ID#".$r['branch_id']." Counter ID#".$r['counter_id']." POS ID#".$r['pos_id']." cannot found pos_id at server.\n",false,array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r['pos_date']));
					continue;
			}

			$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($ds,$column_names));
			
			if ($num_rows<=0)
			{
				$this->_die_("Failed: ".$r['pos_date'].": ".$table." Branch ID#".$r['branch_id']." Counter ID#".$r['counter_id']." POS ID#".$r['pos_id']."\n", false,array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r['pos_date']));
			}
			else
			{
				$con->exec("update $table set sync=1 where branch_id=".mi($r['branch_id'])." and pos_date=".ms($r['pos_date'])." and counter_id=".mi($r['counter_id'])." and pos_id=".mi($r['pos_id'])." and added=".ms($r['added']));
			}
		}
		$con->sql_freeresult($rid);	
	}
	
	function sync_other_pos_with_latest_pos_id($table){
		global $con,$hqcon;
		$sql="select * from $table where sync=0".$this->branch_filter;
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal $table: ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}
		
		$column_names=$this->field_names_without_sync($table);
		
		while($r=$con->sql_fetchassoc($rid)){
			$tmp_pos_id=$r['pos_id'];
			$r['pos_id'] = $this->get_pos_id_from_server($r['branch_id'], $r['counter_id'], $r['date'], $r['pos_id']);

			$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r,$column_names));
			if ($num_rows<=0)	
			{
				$this->_die_("Failed: ".$r['date'].": upload ".$table." Branch ID#".$r['branch_id']." Counter ID#".$r['counter_id']." POS ID#".$r['pos_id'].", item ID#".$r['id']."\n", false,array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r['date']));
			}
			else	$con->exec("update $table set sync=1 where branch_id=".mi($r['branch_id'])." and date=".ms($r['date'])." and counter_id=".mi($r['counter_id'])." and pos_id=".mi($tmp_pos_id)." and id=".mi($r['id']));
		}
		$con->sql_freeresult($rid);
		unset($sql,$r);	
	}
	
	function sync_pos_other_tables($table){
		global $con,$hqcon;
		$sql="select * from $table where sync=0".$this->branch_filter;
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal $table: ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}
		
		$column_names=$this->field_names_without_sync($table);
		
		while($r=$con->sql_fetchassoc($rid))
		{
			if(isset($r['id'])) $old_id = $r['id'];
			//|| 
			if($table=="pos_cash_domination" || $table=="pos_cash_history" || $table=="pos_drawer" || $table=="pos_user_log")
			{
				$ret = $hqcon->query("select * from $table where date=".ms($r['date'])." and ref_no=".ms($r['ref_no'])." and (ref_no != \"\" || ref_no is not null) and counter_id=".mi($r['counter_id'])." and branch_id=".mi($r['branch_id']));
				if($hqcon->sql_numrows($ret)<=0){
					$ret1 = $hqcon->query("select max(id) as id from $table where date=".ms($r['date'])." and counter_id=".mi($r['counter_id'])." and branch_id=".mi($r['branch_id']));
					if($hqcon->sql_numrows($ret1)>0){
						$result = $hqcon->sql_fetchassoc($ret1);
						$r['id'] = $result['id']+1;
					}else{
						if(!$r['ref_no']) {
							$ret1 = $hqcon->query("select max(id) as id from $table where date=".ms($r['date'])." and counter_id=".mi($r['counter_id'])." and branch_id=".mi($r['branch_id']));
							if($hqcon->sql_numrows($ret1)>0){
								$result = $hqcon->sql_fetchassoc($ret1);
								$r['id'] = $result['id']+1;
							}else{
								if(!$r['ref_no']) $r['ref_no'] = true;
							}
						}
					}
				}
				else{
					if(!$r['ref_no']){
						$ret1 = $hqcon->query("select max(id) as id from $table where date=".ms($r['date'])." and counter_id=".mi($r['counter_id'])." and branch_id=".mi($r['branch_id']));
						if($hqcon->sql_numrows($ret1)>0){
							$result = $hqcon->sql_fetchassoc($ret1);
							$r['id'] = $result['id']+1;
						}else{
							if(!$r['ref_no']) $r['ref_no'] = true;
						}
					}
					else{
						
						$result = $hqcon->sql_fetchassoc($ret);
						$r['id'] = $result['id'];
					}
				}
			}
		
			$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r,$column_names));
			if ($num_rows<=0) 
			{
				$this->_die_("Failed: ".$r['date'].": upload ".$table." Branch ID#".$r['branch_id']." Counter ID#".$r['counter_id'].(isset($r['id'])?", ID#".$r['id']:"")."\n", false, array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r['date']));
			}
			else{
				if($table!="pos_transaction_audit_log" && $table != "pos_transaction_ejournal")
				{
					$con->exec("update $table set sync=1 where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and date=".ms($r['date'])." and id=".mi($old_id));
				}
				else{					
					$con->exec("update $table set sync=1 where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and date=".ms($r['date']));
				}
			}
		}
		$con->sql_freeresult($rid);
		unset($sql,$r);
	}

	function sync_pos_receipt_cancel(){
		global $con,$hqcon;
		$table="pos_receipt_cancel";
		$sql="select * from $table where sync=0".$this->branch_filter;
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal $table: ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}

		$column_names=$this->field_names_without_sync($table);

		while($r=$con->sql_fetchassoc($rid)){
			$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r,$column_names));
			if ($num_rows<=0) {
				$this->_die_("Failed: ".$r['date'].": upload ".$table." Branch ID#".$r['branch_id']." Counter ID#".$r['counter_id'].", ID#".$r['id']."\n", false, array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r['date']));
			}
			else{	
				$con->exec("update $table set sync=1 where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and date=".ms($r['date'])." and id=".mi($r['id'])." and receipt_no=".ms($r['receipt_no']));
			}
		}
		$con->sql_freeresult($rid);
		unset($sql,$r);
	}
	
	function sync_pos_counter_collection_tracking(){
		global $con,$hqcon;
		
		//upload
		$table="pos_counter_collection_tracking";
		$sql="select * from $table where sync=0";
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal Pos Counter Collection Tracking Uploading:  ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}
		while($r=$con->sql_fetchassoc($rid)){
			$hqcon->exec("update $table set error = ".ms($err)." where branch_id = ".mi($branch_id)." and counter_id = ".mi($counter_id)." and date = ".ms($pos_db)." and finalized = 1");
			$con->exec("update $table set sync=1 where branch_id=".mi($r['branch_id'])." and id=".mi($r['id']));
		}
		$con->sql_freeresult($rid);
		unset($sql,$r);
	
		//download
		$sql="select * from tmp_check_finalized";
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal Pos Counter Collection Tracking Downloading:  ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}
		while($r=$con->sql_fetchassoc($rid)){
			$rid2=$hqcon->query("select * from $table where branch_id=".mi($r['branch_id'])." and date=".ms($r['date']));
			while ($r=$hqcon->sql_fetchassoc($rid2)){
				$con->exec("replace into $table ".mysql_insert_by_field($r,explode(",","branch_id,counter_id,date,finalized")));
			}
			$hqcon->sql_freeresult($rid2);

			$con->exec("delete from tmp_check_finalized where branch_id=".mi($r['branch_id'])." and date=".ms($r['date']));
		}
	}
	
	function sync_counter_status(){
		global $con,$hqcon;
		$table="counter_status";
		$sql="select * from $table where sync=0".$this->branch_filter;
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal Counter Status:  ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}

		$column_names=$this->field_names_without_sync($table);
		
		while($r=$con->sql_fetchassoc($rid)){
			$hqcon->exec("update $table set ".mysql_update_by_field($r,$column_names)." where branch_id=".mi($r['branch_id'])." and id=".mi($r['id']));
			$con->exec("update $table set sync=1 where branch_id=".mi($r['branch_id'])." and id=".mi($r['id']));
		}
		$con->sql_freeresult($rid);
		unset($sql,$r);
	}
		
	function sync_sku_items_temp_price(){
		global $con,$hqcon;
		$table="sku_items_temp_price";
		$sql="select * from $table where sync=0".$this->branch_filter;
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal $table: ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}

		$column_names=$this->field_names_without_sync($table);

		while($r=$con->sql_fetchassoc($rid)){
			$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r,$column_names));
			if ($num_rows<=0) {
				$this->_die_("Failed: $r[date]: upload $table Branch ID#$r[branch_id], item ID#$r[sku_item_id]\n", false, array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r['date']));
			}
			else{	
				$con->exec("update $table set sync=1 where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and sku_item_id=".mi($r['sku_item_id']));
			}
		}
		$con->sql_freeresult($rid);
		unset($sql,$r);
	}
	
	function sync_sku_items_temp_price_history(){
		global $con,$hqcon;	
		$table="sku_items_temp_price_history";
		$sql="select * from $table where sync=0".$this->branch_filter;
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal $table: ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}

		$column_names=$this->field_names_without_sync($table);

		while($r=$con->sql_fetchassoc($rid)){
			$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r,$column_names));
			if ($num_rows<=0)	$this->_die_("Failed: $r[date]: upload $table Branch ID#$r[branch_id], item ID#$r[sku_item_id]\n", false);
			else{	
				$con->exec("update $table set sync=1 where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and sku_item_id=".mi($r['sku_item_id'])." and added_date=".ms($r['added_date']));
			}
		}
		$con->sql_freeresult($rid);
		unset($sql,$r);
	}
	
	function delete_invalid_sku()
	{
		//if not between 10pm to 11pm, return
		//if(!$this->check_processing_time('0000', '0200'))   return false;
		
		include("../config.php");
		$con2 = connect_db("mysql:dbname=$db_default_connection[3];host=$db_default_connection[0]", $db_default_connection[1], $db_default_connection[2]);
		$con2->query("create table if not exists tmp_invalid_sku (branch_id integer ,counter_id integer,open_by integer,barcode char(50),sku_description char(35),unit_price double,lastupdate timestamp, primary key(branch_id,barcode))");
		$con2->query("create table if not exists tmp_invalid_sku_history (branch_id integer,counter_id integer,open_by integer,barcode char(50),sku_description char(35),unit_price double,added_date timestamp, primary key(branch_id,barcode,added_date))");
		$filter = "";
		if($this->run_bid > 0) $filter = " where branch_id = ".mi($this->run_bid);
		$ret = $con2->query("select * from tmp_invalid_sku".$filter);
		//Select data from invalid SKU and the SKU if more than 1 month
		$i=0;
		while($r = $con2->sql_fetchassoc($ret))
		{
			$diff_month = (time()- strtotime($r['lastupdate']))/(60*60*24*30);
			if($diff_month > 1)
			{
				$con2->exec("Delete from tmp_invalid_sku where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and barcode=".ms($r['barcode'])." and lastupdate=".ms($r['lastupdate']));
				$i++;
			}
		}
		print "$i invalid items have been deleted";
		$con2->sql_freeresult();
		unset($con2);
	}

	function sync_membership_points_history()
	{
		global $con, $hqcon;
		$table="membership_points";
		$sql="select * from $table where sync=0".$this->branch_filter;
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal $table: ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}

		$column_names=$this->field_names_without_sync($table);

		while($r=$con->sql_fetchassoc($rid)){
			//print "replace into $table ".mysql_insert_by_field($r,$column_names)."\n\n";
			$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r,$column_names));
			if ($num_rows<=0)	{
				$this->_die_("Failed: ".$r['date'].": upload ".$table." Branch ID#".$r['branch_id'].", Membership Card No#".$r['card_no']."\n", false, array("branch_id"=>$r['branch_id'],"counter_id"=>0,"date"=>$r['date']));
			}
			else{	
				$con->exec("update $table set sync=1 where branch_id=".mi($r['branch_id'])." and card_no=".ms($r['card_no'])." and type=".ms($r['type'])." and date=".ms($r['date']));
			}
		}
		$con->sql_freeresult($rid);
		unset($sql,$r);
	}
	
	function sync_counter_error()
	{
		global $con,$hqcon;
		$table="pos_error";
		$sql="select * from $table where sync=0".$this->branch_filter;
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal $table: ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}

		$column_names=$this->field_names_without_sync($table);

		while($r=$con->sql_fetchassoc($rid)){
			$num_rows = $hqcon->exec("replace into $table ".mysql_insert_by_field($r,$column_names));
			if ($num_rows<=0)	$this->_die_("Failed: $r[date]: upload $table Branch ID#$r[branch_id]\n", false);
			else{	
				$con->exec("update $table set sync=1 where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and date=".ms($r['date'])." and id=".mi($r['id']));
			}
		}
		$con->sql_freeresult($rid);
		unset($sql,$r);
	}
	
	function get_pos_id_from_server($bid,$cid, $date, $pos_id, $receipt_no=0){
		global $con,$hqcon;
		if(!$date)  return 0;	
//		if($cid != $this->counter_id) return $pos_id; // different counter, pos id should be correct
		if (!$receipt_no){
			$rid=$hqcon->query("select receipt_no from pos where branch_id=".mi($bid)." and counter_id=".mi($cid)." and date=".ms($date)." and id=".mi($pos_id));
			$r = $hqcon->sql_fetchassoc($rid);
			$hqcon->sql_freeresult($rid);
			if(!$r){
				return 0;
			}
			$receipt_no=$r['receipt_no'];	
			unset($r);
		}
		
		$sql = "select id from pos where branch_id=".mi($bid)." and counter_id=".mi($cid)." and date=".ms($date)." and receipt_no=".mi($receipt_no);
		
		$rid=$hqcon->query($sql);
		$tmp = $hqcon->sql_fetchassoc($rid);
		$hqcon->sql_freeresult($rid);
		
		return mi($tmp['id']);
	}
	
	function get_new_pos_id_from_server($bid,$cid,$date){
		global $hqcon;
		if(!$date)  return 0;
	
		$rid=$hqcon->query("select max(id) as max_id from pos where branch_id=".mi($bid)." and counter_id=".mi($cid)." and date=".ms($date));
		$max_id = $hqcon->sql_fetchassoc($rid);
		$hqcon->sql_freeresult($rid);
		return $max_id['max_id']+1;
	}	
	
	function _die_($msg,$die=true,$params)
	{
		file_put_contents("armspos.err", "Timestamp: ".date("Y-m-d H:i:s")."|".$msg);
		$this->got_error=1;
		$this->erromessage[$params['date']][$params['branch_id']][$params['counter_id']][] = $msg;
		if ($die) 
			die($msg);
		else
			print "$msg\n";
	}

	function field_names_without_sync($table){
		global $con;
		// get column list
		$curr_tbl_col_list = array();
		$q1 = $con->query("explain $table", true);
		
		while($r = $con->sql_fetchassoc($q1)){
			if ($r['Field'] == "sync" || $r['Field'] == "transaction_sync")	continue;
            $curr_tbl_col_list[] = $r['Field'];
		}	
	
		return $curr_tbl_col_list;
	}
	
	function update_sales_record($table,$salesRecordInfo=false,$missingRecord=0)
	{
		global $con,$hqcon;
		
		$columnFields = array("branch_id","counter_id","date","tablename","total_record","synced_record","missing_record","added","lastupdate");
		if($table=="pos_deposit_status_history")
			$datefields = "pos_date";
		else
			$datefields = "date";
		
		//$cond[] = "$datefields between (NOW() - INTERVAL 30 DAY) and NOW()";
		$cond[] = "$datefields = ".ms(date("Y-m-d"));
		if($table == "pos") $cond[] = "transaction_sync = 1";
		if($salesRecordInfo)
		{
			$cond[] = "branch_id = ".$salesRecordInfo['branch_id'];
			$cond[] = "counter_id = ".$salesRecordInfo['counter_id'];
			$cond[] = "$datefields = ".ms($salesRecordInfo[$datefields]);
		}
		
		if($this->run_bid > 0) $cond[] = "branch_id = ".mi($this->run_bid);
		
		$where =  "where ". implode(" and ",$cond);
	
		$q1 = $con->query("select count(*) as total,branch_id,counter_id,$datefields from $table $where group by branch_id,counter_id,$datefields");
		while($r = $con->sql_fetchassoc($q1))
		{
			$q2 = $con->query("select count(*) as total from $table where branch_id = ".ms($r['branch_id'])." and counter_id = ".ms($r['counter_id'])." and $datefields=".ms($r[$datefields])." and sync = 1");
			while($r1 = $con->sql_fetchassoc($q2))
			{
				$ret = $hqcon->query("select * from pos_transaction_counter_sales_record  where branch_id=".ms($r['branch_id'])." and counter_id=".ms($r['counter_id'])." and date=".ms($r[$datefields])." and tablename=".ms($table));
				if($hqcon->sql_numrows($ret)>0)
				{
					$hqcon->query("update pos_transaction_counter_sales_record  set total_record=".ms($r['total']).",synced_record=".ms($r1['total']).",missing_record=".ms($missingRecord).",lastupdate=CURRENT_TIMESTAMP where branch_id=".ms($r['branch_id'])." and counter_id=".ms($r['counter_id'])." and date=".ms($r[$datefields])." and tablename=".ms($table));
				}
				else{
					$upd = array();
					$upd['branch_id'] = $r['branch_id'];	
					$upd['counter_id'] = $r['counter_id'];
					$upd['date'] = $r[$datefields];	
					$upd['tablename'] = $table;		
					$upd['total_record'] = $r['total'];					
					$upd['synced_record'] = $r1['total'];			
					$upd['missing_record'] = $missingRecord;	
					$upd['added'] = 'CURRENT_TIMESTAMP';
					$upd['lastupdate'] = 'CURRENT_TIMESTAMP';

					$hqcon->exec("replace into pos_transaction_counter_sales_record  ".mysql_insert_by_field($upd));
				}
				unset($upd);
			}
			$con->sql_freeresult($q2);
			
			if(!$salesRecordInfo && $table=="pos")
			{
				$hqcon->query("update pos_transaction_sync_server_tracking set error_message='', lastupdate = CURRENT_TIMESTAMP where branch_id = ".ms($r['branch_id'])." and date = ".ms($r['date']));
				$hqcon->query("update pos_transaction_sync_server_counter_tracking set error_message='', lastupdate = CURRENT_TIMESTAMP where branch_id = ".ms($r['branch_id'])." and counter_id = ".ms($r['counter_id'])." and date = ".ms($r['date']));
			}
		}
		$con->sql_freeresult($q1);
	}
	
	function checking_missing_sales_record($table)
	{
		global $con,$hqcon;
		
		if($table=="pos_deposit_status_history")
			$datefields = "pos_date";
		else
			$datefields = "date";
		//$where = "where $datefields = ".date("Y-m-d");
		$where = "where $datefields = ".ms(date("Y-m-d"));
		$where .= " and sync = 1";
		if($this->run_bid) $where .= " and branch_id = ".mi($this->run_bid);
	
		$q1 = $con->query("select count(*) as total,branch_id,counter_id,$datefields from $table $where group by branch_id,counter_id,$datefields");
		while($r = $con->sql_fetchassoc($q1))
		{
			$q2 = $hqcon->query("select count(*) as total from $table where branch_id=".ms($r['branch_id'])." and counter_id=".ms($r['counter_id'])." and $datefields=".ms($r[$datefields]));
			$r2 = $hqcon->sql_fetchassoc($q2);
			$hqcon->sql_freeresult($q2);
			$missingRecord = $r['total'] - $r2['total'];
			if($missingRecord>0)
			{
				$this->update_sales_record($table,$r,$missingRecord);
				$error = "Error: ".$r[$datefields].": $table Branch ID#".$r['branch_id']." Counter ID#".$r['counter_id'].", Missing Record(".$missingRecord.").";
				$this->_die_($error, false, array("branch_id"=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>$r[$datefields]));
			}
			else{
				$this->update_sales_record($table,$r,0);
			}
			
		}
		$con->sql_freeresult($q1);
	}
	
	function update_error_message()
	{
		global $hqcon;
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
		}
	}

	function sync_clocking_log()
	{
		global $con,$hqcon;
		$table = "pos_transaction_clocking_log";
		$sql="select * from $table where sync=0";	
		$rid=$con->query($sql);
		$total_rows=$con->sql_numrows($rid);
		print "\nTotal $table: ".$total_rows."\n";
		if ($total_rows<=0){ print "Skip...\n";return;}
		while($r=$con->sql_fetchassoc($rid))
		{
			$oldID = $r['id'];
			$id = $this->get_pos_transaction_from_server($table,$r['counter_date'],$r['branch_id'],$r['counter_id']);
			if($id)
			{
				$r['id'] = $id;
			}
			unset($r['sync']);
			
			$num_rows =	$hqcon->exec("replace into $table ".mysql_insert_by_field($r));
			
			if ($num_rows<=0)	{
				$this->_die_("Clocking: Unable connect to server",true,array('branch_id'=>$r['branch_id'],"counter_id"=>$r['counter_id'],"date"=>date("Y-m-d", strtotime($r['counter_date']))));	
			}
			else{
				$con->exec("update $table set sync = 1 where branch_id=".$r['branch_id']." and counter_id=".$r['counter_id']." and counter_date= ".ms($r['counter_date'])."and id=".ms($oldID));
			}
		}
		$con->sql_freeresult($rid);
	}
	
	function get_pos_transaction_from_server($table,$timestamp,$bid,$cid)
	{
		global $hqcon;
		
		$ret = $hqcon->query("select * from $table where counter_date=".ms($timestamp)." and branch_id=".mi($bid)." and counter_id=".mi($cid));

		if($hqcon->sql_numrows($ret)<=0)
		{
			$r = $hqcon->query("select max(id) as id from $table where branch_id=".mi($bid)." and counter_id=".mi($cid));
			if($hqcon->sql_numrows($r)>0){
				$result = $hqcon->sql_fetchassoc($r);
				$id = $result['id'] + 1;
				unset($result);
			}else{
				$id = 1;
			}
			$hqcon->sql_freeresult($r);
		}
		else{		
			$result = $hqcon->sql_fetchassoc($ret);
			$id = $result['id'];
			unset($result);
		}
		$hqcon->sql_freeresult($ret);
		unset($ret);
		return $id;
	}
	
	function sync_up_all($prms=array()){
		print "\nStart sync POS...";
		$this->sync_pos();

		print "\nStart sync POS Goods Return...";
		$this->update_sales_record("pos_goods_return");
		$this->sync_pos_goods_return();
		$this->checking_missing_sales_record("pos_goods_return");
		
		print "\nStart sync Sn Info...";
		$this->update_sales_record("sn_info");
		$this->sync_sn_info();
		$this->checking_missing_sales_record("sn_info");

		print "\nStart sync Membership Promotion Items...";
		$this->update_sales_record("membership_promotion_items");
		$this->sync_other_pos_with_latest_pos_id("membership_promotion_items");
		$this->checking_missing_sales_record("membership_promotion_items");

		print "\nStart sync Membership Promotion Mix N Match Items...";
		$this->update_sales_record("membership_promotion_mix_n_match_items");
		$this->sync_other_pos_with_latest_pos_id("membership_promotion_mix_n_match_items");
		$this->checking_missing_sales_record("membership_promotion_items");
	
		print "\nStart sync POS Drawer...";
		$this->update_sales_record("pos_drawer");
		$this->sync_pos_other_tables("pos_drawer");
		$this->checking_missing_sales_record("pos_drawer");
		
		print "\nStart sync POS Cash Domination...";
		$this->update_sales_record("pos_cash_domination");
		$this->sync_pos_other_tables("pos_cash_domination");
		$this->checking_missing_sales_record("pos_cash_domination");
			
		print "\nStart sync POS Cash History...";
		$this->update_sales_record("pos_cash_history");
		$this->sync_pos_other_tables("pos_cash_history");
		$this->checking_missing_sales_record("pos_cash_history");
		
		print "\nStart sync POS Mix & Match Usage...";
		$this->update_sales_record("pos_mix_match_usage");
		$this->sync_pos_other_tables("pos_mix_match_usage");
		$this->checking_missing_sales_record("pos_mix_match_usage");

		print "\nStart sync POS Receipt Cancel...";
		$this->update_sales_record("pos_receipt_cancel");
		$this->sync_pos_receipt_cancel();
		$this->checking_missing_sales_record("pos_receipt_cancel");

		//print "\nStart sync POS Counter Collection Tracking...";
		//$this->sync_pos_counter_collection_tracking();

		print "\nStart sync Counter Status...";
		$this->sync_counter_status();

		print "\nStart sync POS Deposit Status...";
		$this->update_sales_record("pos_deposit_status");
		$this->sync_pos_deposit_status();
		$this->checking_missing_sales_record("pos_deposit_status");

		print "\nStart sync POS Deposit Status History...";
		$this->update_sales_record("pos_deposit_status_history");
		$this->sync_pos_deposit_status_history();
		$this->checking_missing_sales_record("pos_deposit_status_history");

		print "\nStart sync POS User Log...";
		$this->sync_pos_other_tables("pos_user_log");
		
		print "\nStart sync Sku Items Temp Price...";
		$this->sync_sku_items_temp_price();
		
		print "\nStart sync Sku Items Temp Price History...";
		$this->sync_sku_items_temp_price_history();
		
		print "\nDelete Invalid SKU Table...";
		$this->delete_invalid_sku();
		
		print "\nStart sync membership_point...";
		$this->sync_membership_points_history();
		
		print "\nStart sync pos error(counter error)";
		$this->sync_counter_error();
		
		// do not sync every minute, sync when found it is force_sync or minutes reaches 0 and 30
		$curr_minute = mi(date("i"));
		if((isset($prms['force_sync']) && $prms['force_sync']) || ($curr_minute == 0 || $curr_minute == 30)){
			print "\nStart sync POS Audit Log...";
			$this->sync_pos_other_tables("pos_transaction_audit_log");
			
			print "\nStart sync POS Ejournal...";
			$this->sync_pos_other_tables("pos_transaction_ejournal");
		}
		
		print "\nStart sync Clocking Log...";
		$this->sync_clocking_log();
		
		$this->update_error_message();
	}
	
	function get_pos_receipt_no_from_local($bid,$cid,$date,$pid)
	{
		global $con;
		$rid = $con->query("select receipt_no,receipt_ref_no from pos where branch_id = ".ms($bid)." and counter_id = ".ms($cid)." and date=".ms($date)." and id = ".ms($pid));
		$r = $con->sql_fetchassoc($rid);
		$con->sql_freeresult($rid);
		if(!$r){
			return 0;
		}
		return $r;
	}
}
?>
