<?php
/*
2/3/2012 3:54:57 PM Alex
- created and fixed for version problems

3/6/2012 4:07:45 PM Alex
- add transaction_sync column for pos

5/18/2012 1:42:00 PM Kee Kee
- Compatible with 152 version

07/02/2012 11:21:00 AM Kee Kee
- Compatiable with 153 version

7/13/2012 5:35 PM Andy
- Up version to 114 to fix barcode character.

08/08/2012 9:18 AM Kee Kee
- Compatible with 166 beta version

01/16/2012 10:10 AM Kee Kee
- add "quota_used" column into pos and pos_items
- add "quota_over_by","staff_approve_by"
- add "pos_more_info" into pos

03/01/2013 9:48 AM Kee Kee
- add sales_order_id and sales_order_branch_id in pos table

12/04/2013 3:18 PM Kee Kee
- add 'type' into pos_drawer table

01/16/2014 4:29 PM Kee Kee
- Add 'is_abnormal' into pos_payment

09/09/2014 11:13 PM Kee Kee
- Add "revision_type" into counter_status

03/01/2016 1:54 PM Kee Kee
- Add "reason_code","reason_desc" into pos_goods_return

03/10/2016 9:51AM Kee Kee
- Add pos_transaction_audit_log table

11/10/02016 09:27 AM Qiu Ying
- Add return_receipt_ref_no in pos_goods_return table

11/25/2016 9:48 AM Andy
- Release version 207.

1/3/2017 10:07 AM Andy
- Modify pos_credit_note primary key.
- Release version 208.

5/17/2017 5:10 PM Andy
- Release version 209.

5/22/2017 11:20 AM Andy
- Hide create counter sales record.

9/25/2017 3:22 PM Andy
- Add new column in pos_transaction_clocking_log

10/9/2017 10:02 AM Andy
- v212
- Fix pos_transaction_clocking_log primary key.

10/11/2017 3:39 PM Andy
- v213
- Enhanced to auto create all sync server table at version 1.
- Optimise all table to have needed index.
- Fixed some table timestamp wrong structure.

11/20/2017 11:53 AM Andy
- Change create table default use innodb and COLLATE=latin1_general_ci.

1/29/2018 3:24 PM Andy
- v214
- Added new column "reason" into pos_cash_history

2/23/2018 5:44 PM Andy
- Fixed pos_delete_items primary cannot be null, in order to compatible to mysql 5.7

3/8/2018 2:44 PM Andy
- Fixed pos_delete_items column date default value error, date cannot 0, must put 0000-00-00.

3/19/2019 9:44 AM Andy
- v215
- Alter pos_payment to add 'group_type'.

3/29/2019 9:20 AM Andy
- v216
- Alter pos to add 'pos_guid'.

7/8/2019 5:55 PM Justin
- v217
- Alter pos_items to add 'is_valid_weight'.
- Alter pos to add 'pos_is_valid_weight' and 'use_scale_machine'.

9/23/2019 9:38 AM Justin
- v218
- Alter pos_items 'sku_description' to char(40).

11/6/2019 2:54 PM Andy
- Added column "sync" for create table query for "pos_transaction_audit_log" and "pos_transaction_ejournal".

12/19/2019 1:31 PM Andy
- v219
- Fixed pos_receipt_cancel primary key.
- Added "pos_counter_collection_configuration".

2/4/2020 2:24 Justin
- v220
- Add pos.membership_guid.

10/8/2020 3:12 PM Andy
- Fixed 'pos_receipt_cancel'.receipt_no should be not null.
- v221
- Fixed pos_receipt_cancel primary key.

1/12/2021 12:50 PM Andy
- v222
- Added new table pos_day_start and pos_day_end.

1/18/2021 11:14 AM Andy
- Fixed pos_day_start and pos_day_end time.

2/5/2021 2:23 PM Andy
- v223
- Added pos.is_tax_registered.

4/26/2021 11:22 AM Andy
- v224
- Added pos_day_end.eod_data.

5/3/2021 9:55 AM Andy
- v225
- Increased pos.member_no to char(30).
*/

class Maintenance
{
	var $ver = 0;
	var $init_ver = 0;
	
	function __construct(){
	    global $con;

	    // assign version
		$this->init_ver = $this->ver = $this->get();

		$this->run();

		define('SYNC_MAINTENANCE_VERSION', $this->ver);
	}

	function get(){
		global $con;

		// select version from database and return to the executor
		$rid=$con->query("select * from sys_setting", false, false);
		$r = $con->sql_fetchassoc($rid);
		$con->sql_freeresult($rid);

		if ($r) return $r['version'];
		// create table if cannot found, set the default as 0 and do all the updates below
		$con->exec('create table if not exists sys_setting(
						 version int(10) default 0 not null comment "System Setting"
						 )');

		// default version 1
		$count = $con->exec('insert into sys_setting values (1)');
	
		return 1;
	}

	function update($ver){
		global $con;
		// if found the $ver is latest than database, do update for database's version table....
		$count = $con->exec('update sys_setting set version = '.mi($ver).' where version<'.mi($ver));
		if ($count>0)
			$this->ver = $this->get();
		else
		    $this->ver = $ver;
	}

	function check($ver, $is_tmp = false){
		global $smarty;

		// if found is the same version, do nothing and back to its own module
		if($is_tmp){
			$curr_ver = $this->tmp_ver;
			$version_label = 'TMP version';
		}else{
			$curr_ver = $this->ver;
			$version_label = 'version';
		}
		
		if ($curr_ver >= $ver) return;

		$smarty->display('header.tpl');
		print "<h1>Please update maintenance script, required $version_label $ver, current maintenance $version_label $curr_ver</h1>";
		$smarty->display('footer.tpl');
		exit;
	}
	
	function check_processing_time($time_from='0000', $time_to='2359'){
		$cur_time = date("Hi");
		
		return ($time_from<=$cur_time && $cur_time<=$time_to);
	}

	function run()
	{
		global $con,$hqcon;
		$con->beginTransaction();
			
		$starting_ver = $this->ver; // mark starting version
		
		if($this->ver <= 1){	// First time Setup
			$con->exec("CREATE TABLE if not exists `pos` ( 
			`branch_id` int(11) NOT NULL DEFAULT '0', 
			`counter_id` int(11) NOT NULL DEFAULT '0', 
			`id` int(11) NOT NULL DEFAULT '0', 
			`cashier_id` int(11) DEFAULT NULL, 
			`start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', 
			`end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', 
			`amount` double DEFAULT NULL, 
			`member_no` char(16) COLLATE latin1_general_ci DEFAULT NULL, 
			`race` char(5) COLLATE latin1_general_ci DEFAULT NULL, 
			`receipt_no` int(11) DEFAULT NULL, 
			`cancel_status` int(11) DEFAULT '0', 
			`pos_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', 
			`date` date NOT NULL DEFAULT '0000-00-00', 
			`amount_tender` double DEFAULT NULL, 
			`amount_change` double DEFAULT NULL, 
			`point` int(11) DEFAULT '0', 
			`redeem_points` int(11) DEFAULT '0', 
			`receipt_remark` text COLLATE latin1_general_ci, 
			`prune_status` tinyint(1) DEFAULT '0',
			`sync` tinyint(1) DEFAULT '0', 
			receipt_sa blob default null,
			transaction_sync integer(1) default 0,
			deposit integer(1) default 0,
			receipt_ref_no char(20),
			PRIMARY KEY (`branch_id`,`counter_id`,`date`,`id`), 
			KEY `pos_time` (`pos_time`), 
			KEY `member_no` (`member_no`), 
			KEY `cancel_status` (`cancel_status`), 
			KEY `date` (`date`), 
			KEY `branch_date_active` (`branch_id`,`date`,`cancel_status`),
			index sync (sync),
			index transaction_sync (transaction_sync)
			) ENGINE=innoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE if not exists `pos_items` ( 
			`branch_id` int(11) NOT NULL DEFAULT '0', 
			`counter_id` int(11) NOT NULL DEFAULT '0', 
			`id` int(11) NOT NULL DEFAULT '0', 
			`pos_id` int(11) NOT NULL DEFAULT '0', 
			`item_id` int(11) NOT NULL DEFAULT '0', 
			`sku_item_id` int(11) DEFAULT NULL, 
			`barcode` char(13) COLLATE latin1_general_ci DEFAULT NULL, 
			`qty` double DEFAULT NULL, 
			`price` double DEFAULT NULL, `discount` double DEFAULT NULL, `return_by` int(11) DEFAULT NULL, `return_qty` int(11) DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `open_price_by` int(11) DEFAULT '0', `item_discount_by` int(11) DEFAULT NULL, `mprice_type` char(10) COLLATE latin1_general_ci DEFAULT NULL, `trade_discount_code` char(6) COLLATE latin1_general_ci DEFAULT NULL, `redeem_points` int(11) DEFAULT '0', `promotion_id` int(11) DEFAULT NULL, `cb_code` char(6) COLLATE latin1_general_ci NOT NULL, `cb_profit` double NOT NULL, `cb_discount` char(10) COLLATE latin1_general_ci NOT NULL, `cb_use_net` enum('yes','no') COLLATE latin1_general_ci DEFAULT 'no', `cb_net_bearing` double NOT NULL, `remark` text COLLATE latin1_general_ci, PRIMARY KEY (`branch_id`,`counter_id`,`date`,`pos_id`,`item_id`), KEY `sku_item_id` (`sku_item_id`), KEY `branch_id_n_date_n_open_price_by` (`branch_id`,`date`,`open_price_by`), KEY `branch_id_n_date_n_item_discount_by` (`branch_id`,`date`,`item_discount_by`), KEY `branch_id_n_sku_item_id_n_date` (`branch_id`,`sku_item_id`,`date`), KEY `branch_id_and_sku_item_id` (`branch_id`,`sku_item_id`) ) ENGINE=innoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE if not exists `pos_payment` (
			  `branch_id` int(11) NOT NULL DEFAULT '0',
			  `counter_id` int(11) NOT NULL DEFAULT '0',
			  `id` int(11) NOT NULL DEFAULT '0',
			  `pos_id` int(11) NOT NULL DEFAULT '0',
			  `type` char(30) COLLATE latin1_general_ci DEFAULT NULL,
			  `remark` char(50) COLLATE latin1_general_ci DEFAULT NULL,
			  `amount` double DEFAULT NULL,
			  `date` date NOT NULL DEFAULT '0000-00-00',
			  `changed` int(11) DEFAULT '0',
			  `adjust` int(11) DEFAULT '0',
			  `approved_by` int(11) NOT NULL DEFAULT '0',
			  `more_info` text COLLATE latin1_general_ci,
			  `is_abnormal` int(11) DEFAULT '0',
			  `group_type` char(30) COLLATE latin1_general_ci NOT NULL,
			  PRIMARY KEY (`branch_id`,`counter_id`,`date`,`pos_id`,`id`),
			  KEY `date` (`date`),
			  KEY `approved_by_n_date_n_branch_n_counter` (`approved_by`,`date`,`branch_id`,`counter_id`),
			  KEY `type_n_remark_n_branch` (`type`,`remark`,`branch_id`),
			  KEY `payment_type` (`branch_id`,`date`,`counter_id`,`pos_id`,`type`),
			  KEY `type_date_cancel_branch_remark` (`type`,`date`,`remark`,`branch_id`),
			  KEY `group_type` (`group_type`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE `pos_delete_items` ( 
			`branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `id` int(11) NOT NULL DEFAULT '0', `pos_id` int(11) NOT NULL DEFAULT '0', `sku_item_id` int(11) DEFAULT NULL, `barcode` char(13) COLLATE latin1_general_ci DEFAULT NULL, `qty` double DEFAULT NULL, `price` double DEFAULT NULL, `delete_by` int(11) DEFAULT NULL, PRIMARY KEY (`branch_id`,`counter_id`,`date`,`pos_id`,`id`) ) ENGINE=innoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE if not exists `pos_mix_match_usage` ( 
			`branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `pos_id` int(11) NOT NULL DEFAULT '0', `id` int(11) NOT NULL DEFAULT '0', `remark` char(50) COLLATE latin1_general_ci DEFAULT NULL, `amount` double DEFAULT NULL, `group_id` int(11) DEFAULT NULL, `promo_id` int(11) DEFAULT NULL, `more_info` text COLLATE latin1_general_ci, `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`date`,`counter_id`,`pos_id`,`id`), KEY `remark` (`remark`), KEY `group_id_n_promo_id_n_date` (`group_id`,`promo_id`,`date`) ) ENGINE=innoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE `pos_goods_return` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `pos_id` int(11) NOT NULL DEFAULT '0', `item_id` int(11) NOT NULL DEFAULT '0', `return_counter_id` int(11) DEFAULT NULL, `return_date` date DEFAULT NULL, `return_pos_id` int(11) DEFAULT NULL, `return_item_id` int(11) DEFAULT NULL, `return_receipt_no` char(50) COLLATE latin1_general_ci DEFAULT NULL, `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`,`pos_id`,`item_id`), KEY `date` (`date`), KEY `return_info` (`branch_id`,`return_counter_id`,`return_date`,`return_pos_id`,`return_item_id`) ) ENGINE=innoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE if not exists `membership_promotion_items` ( `id` int(11) NOT NULL AUTO_INCREMENT, `branch_id` int(11) NOT NULL DEFAULT '0', `card_no` char(20) DEFAULT NULL, `promo_id` int(11) DEFAULT NULL, `pos_id` int(11) DEFAULT NULL, `counter_id` int(11) NOT NULL DEFAULT '0', `sku_item_id` int(11) DEFAULT NULL, `qty` double DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`,`id`), KEY `card_no` (`card_no`), KEY `promo_id` (`promo_id`), KEY `sku_item_id` (`sku_item_id`), KEY `date` (`date`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE if not exists `pos_items_sn` ( `id` int(11) NOT NULL AUTO_INCREMENT, `branch_id` int(11) NOT NULL DEFAULT '0', `pos_id` int(11) DEFAULT NULL, `pos_item_id` int(11) DEFAULT NULL, `pos_branch_id` int(11) DEFAULT NULL, `date` date DEFAULT NULL, `counter_id` int(11) DEFAULT NULL, `sku_item_id` int(11) DEFAULT NULL, `located_branch_id` int(11) DEFAULT NULL, `serial_no` varchar(20) DEFAULT NULL, `status` tinyint(1) DEFAULT '0', `active` tinyint(1) DEFAULT '1', `added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `created_by` int(11) DEFAULT NULL, `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', PRIMARY KEY (`branch_id`,`id`), UNIQUE KEY `sku_item_id` (`sku_item_id`,`serial_no`), KEY `serial_no_idx` (`serial_no`), KEY `lbid_sid_sn_idx` (`located_branch_id`,`sku_item_id`,`serial_no`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE if not exists `sn_info` ( `pos_id` int(11) NOT NULL DEFAULT '0', `item_id` int(11) NOT NULL DEFAULT '0', `branch_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `counter_id` int(11) NOT NULL DEFAULT '0', `sku_item_id` int(11) DEFAULT NULL, `serial_no` varchar(20) DEFAULT NULL, `nric` varchar(20) NOT NULL, `name` varchar(100) DEFAULT NULL, `address` text, `contact_no` varchar(15) DEFAULT NULL, `email` varchar(40) DEFAULT NULL, `warranty_expired` date DEFAULT NULL, `active` tinyint(1) DEFAULT '1', `approved_by` int(11) DEFAULT NULL, `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`pos_id`,`item_id`,`branch_id`,`date`,`counter_id`), KEY `serial_no_idx` (`serial_no`), KEY `sid_sn_idx` (`sku_item_id`,`serial_no`) ) ENGINE=innoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
						
			$con->exec("CREATE TABLE if not exists `membership_promotion_mix_n_match_items` ( `id` int(11) NOT NULL AUTO_INCREMENT, `branch_id` int(11) NOT NULL DEFAULT '0', `card_no` char(20) COLLATE latin1_general_ci NOT NULL, `real_promo_id` int(11) DEFAULT NULL, `promo_id` int(11) DEFAULT NULL, `group_id` int(11) DEFAULT NULL, `pos_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `qty` double DEFAULT NULL, `amount` double DEFAULT NULL, `date` date NOT NULL DEFAULT '0000-00-00', `used` tinyint(1) DEFAULT NULL, `added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`date`,`counter_id`,`pos_id`,`id`), KEY `branch_n_counter_n_date_n_pos` (`branch_id`,`counter_id`,`date`,`pos_id`), KEY `branch_id_n_promo_id_n_group_id_n_cardno_n_date` (`branch_id`,`promo_id`,`group_id`,`card_no`,`date`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE if not exists `pos_drawer` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `id` int(11) NOT NULL DEFAULT '0', `user_id` int(11) DEFAULT NULL, `timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `date` date NOT NULL DEFAULT '0000-00-00', `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`,`id`), KEY `date` (`date`), 
			index sync (sync)
			) ENGINE=innoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE if not exists `pos_cash_domination` ( `branch_id` int(11) NOT NULL DEFAULT '0', `id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `user_id` int(11) DEFAULT NULL, `data` text COLLATE latin1_general_ci, `odata` text COLLATE latin1_general_ci, `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, `date` date NOT NULL DEFAULT '0000-00-00', `clear_drawer` tinyint(1) DEFAULT '0', `curr_rate` text COLLATE latin1_general_ci, `ocurr_rate` text COLLATE latin1_general_ci, `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`,`id`), KEY `date` (`date`), index sync (sync)) ENGINE=innoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE if not exists `pos_receipt_cancel` ( 
`branch_id` int(11) NOT NULL DEFAULT '0', 
`counter_id` int(11) NOT NULL DEFAULT '0', 
`date` date NOT NULL DEFAULT '0000-00-00', 
`id` int(11) NOT NULL DEFAULT '0', 
`receipt_no` int(11) NOT NULL DEFAULT '0',  
`cancelled_by` int(11) DEFAULT NULL, `cancelled_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `verified_by` int(11) DEFAULT NULL, `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`,`id`, `receipt_no`), KEY `date` (`date`) 
) ENGINE=innoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE if not exists `pos_cash_history` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) DEFAULT NULL, `collected_by` int(11) DEFAULT NULL, `type` char(10) COLLATE latin1_general_ci DEFAULT NULL, `amount` double DEFAULT NULL, `oamount` double DEFAULT NULL, `timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `remark` char(16) COLLATE latin1_general_ci DEFAULT NULL, `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`,`id`), KEY `date` (`date`), index sync (sync) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE if not exists `pos_counter_collection_tracking` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', `finalized` tinyint(1) DEFAULT '0', `error` char(100) COLLATE latin1_general_ci DEFAULT NULL, `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, `solved` tinyint(1) DEFAULT '0', `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`counter_id`,`date`), KEY `date` (`date`) ) ENGINE=innoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE if not exists `counter_status` ( `branch_id` int(11) NOT NULL DEFAULT '0', `id` int(11) NOT NULL DEFAULT '0', `ip` char(15) COLLATE latin1_general_ci DEFAULT NULL, `status` char(20) COLLATE latin1_general_ci DEFAULT NULL, `lastping` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, `user_id` int(11) DEFAULT NULL, `lasterr` char(50) COLLATE latin1_general_ci DEFAULT NULL, `revision` int(11) DEFAULT NULL, `min_tmp_trigger_log_row` int(11) NOT NULL DEFAULT '0', `min_tmp_member_trigger_log_row` int(11) NOT NULL DEFAULT '0', `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`id`) ) ENGINE=innoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->exec("CREATE TABLE if not exists `tmp_check_finalized` ( `branch_id` int(11) NOT NULL DEFAULT '0', `date` date NOT NULL DEFAULT '0000-00-00', PRIMARY KEY (`branch_id`,`date`) ) ENGINE=innoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(1);
		}
		
		if($this->ver < 101){

			// 11/10/2011 6:24:34 PM Alex
			$con->exec("alter table pos_payment add index type_date_cancel_branch_remark (type, date, remark, branch_id)",false,false);
			
			$this->update(101);
		}
		// 11/15/2011 11:34:52 AM Andy - commit

		if ($this->ver < 106){

			// 10/28/2011 5:16:22 PM Kee Kee
			if($this->check_need_alter('pos_items', array('open_code_by','verify_code_by','verify_timestamp'))){
				$con->exec("alter table pos_items add open_code_by int, add verify_code_by int, add verify_timestamp timestamp DEFAULT '0000-00-00 00:00:00'", true);
			}

			$this->update(106);
		}
		// 12/15/2011 4:29:11 PM Justin - commit

		if ($this->ver < 108){

			// 10/27/2011 2:04:22 PM Kee Kee
			if($this->check_need_alter('pos', array('receipt_sa'))){
				$con->exec("alter table pos add receipt_sa blob default null", true);
			}
		
			// 10/27/2011 2:06:22 PM Kee Kee
			if($this->check_need_alter('pos_items', array('item_sa'))){
				$con->exec("alter table pos_items add item_sa blob default null", true);
			}
			
			$this->update(108);
		}
		
		if($this->ver < 110)
		{			
			// 12/07/2011 11:42:00 AM Kee Kee
			if($this->check_need_alter('pos_items', array('temp_price'))){
				$con->exec("alter table pos_items add temp_price boolean default 0", true);
			}			

			// 12/19/2011 2:45:00 PM Kee Kee
			if($this->check_need_alter('pos_items', array('sku_description'))){
				$con->exec("alter table pos_items add sku_description char(35)", true);
			}
					
			$this->update(110);
		}
		// 1/11/2012 5:44:53 PM Justin - commit


		if($this->ver < 111){
			// 12/12/2011 11:51:00 AM Kee Kee
			$con->exec("create table if not exists sku_items_temp_price(
						branch_id integer,
						counter_id integer,
						temp_by integer,
						sku_item_id integer,
						temp_price double,
						reason text,
						active boolean default 1,
						lastupdate timestamp,
						sync integer(1) default 0,
						primary key(branch_id,sku_item_id)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			$con->exec("create table if not exists sku_items_temp_price_history(
						branch_id integer,
						counter_id integer,
						temp_by integer,
						sku_item_id integer,
						temp_price double,
						reason text,
						active boolean default 1,
						added_datetime timestamp,
						added_date date,
						sync integer(1) default 0,
						primary key(branch_id,sku_item_id,added_datetime)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");		
		
			$this->update(111);
		}

		if($this->ver < 112){
			if($this->check_need_alter("pos",array("transaction_sync"))){
				$con->exec("alter table pos add transaction_sync integer(1) default 0");
			}
			$this->update(112);
		}		
		
		//Commit By Kee Kee on 07/02/2012 11:38:00 AM
		if($this->ver < 113){
		  //Create Pos Deposit, Pos Deposit Status and Pos Deposit Status History
		  $con->exec("create table if not exists pos_deposit(
					pos_id integer,
					counter_id integer,
					branch_id integer,
					pos_time integer,
					receipt_no char(15),
					date date,
					item_list text,
					deposit_amount double,
					cashier_id integer,
					approved_by integer, 
					primary key(pos_id,counter_id,branch_id,date)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
		  $con->exec("create table if not exists pos_deposit_status(
					branch_id integer,
					counter_id integer,
					pos_id integer,
					date date,
					receipt_no integer,
					deposit_branch_id integer,
					deposit_counter_id integer,
					deposit_pos_id integer,
					deposit_date date,
					deposit_receipt_no integer,
					verified_by integer,
					status integer,
					cancel_reason text,
					sync integer(1) default 0,
					primary key(deposit_branch_id,deposit_counter_id,deposit_pos_id,deposit_date,deposit_receipt_no)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");		
		
		$con->exec("create table if not exists 
				pos_deposit_status_history(
					branch_id int,
					counter_id int,
					pos_id int,
					pos_date date,
					receipt_no int,
					deposit_branch_id int,
					deposit_counter_id int,
					deposit_pos_id int,
					deposit_pos_date date,
					deposit_receipt_no int,
					user_id int,
					type varchar(10),
					remark text,
					added timestamp,
					sync int,
					primary key(branch_id, counter_id, pos_date, pos_id),
					index used_bcpr(branch_id, counter_id, pos_date, receipt_no),
					index deposit_bcdp(deposit_branch_id, deposit_counter_id, deposit_pos_date, deposit_pos_id),
					index deposit_bcdr(deposit_branch_id, deposit_counter_id, deposit_pos_date, deposit_receipt_no)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
				
        if($this->check_need_alter("pos",array("deposit","receipt_ref_no"))){
			     $con->exec("alter table pos add deposit integer(1) default 0");
			    //5/02/2012 6:06:00 PM Kee Kee
			     $con->exec("alter table pos add receipt_ref_no char(20)");
		    }		

			if($this->check_need_alter("pos_items",array("is_return_policy","expired_return_policy","more_info","trade_in_by","got_return_policy"))){
				$con->exec("alter table pos_items add is_return_policy integer default 0");
				$con->exec("alter table pos_items add expired_return_policy int default 0");
				$con->exec("alter table pos_items add more_info text");
				$con->exec("alter table pos_items add trade_in_by int");  
				$con->exec("alter table pos_items add got_return_policy tinyint(1) default 0");
			}

			if($this->check_need_alter("pos_goods_return",array("return_branch_id"))){
			   	$con->exec("alter table pos_goods_return add return_branch_id int");
			}
			  
			if($this->check_need_alter("pos_cash_domination",array("ref_no"))){
				$con->exec("alter table pos_cash_domination add ref_no char(30)");
			}
			
			//5/07/2012 10:59:00 AM Kee Kee
			$con->query("show index from pos_receipt_cancel where Key_name='primary'");
			if($con->sql_numrows()<5){
				$con->exec("alter table pos_receipt_cancel drop primary key, add primary key (branch_id,counter_id,date,id,receipt_no)", true);
			}

			//5/07/2012 11:27:00 AM Kee Kee
			if($this->check_need_alter("pos_cash_history",array("ref_no"))){
				$con->exec("alter table pos_cash_history add ref_no char(30)");
			}
			
			//5/07/2012 11:44:00 AM Kee Kee
			if($this->check_need_alter("pos_drawer",array("ref_no"))){
				$con->exec("alter table pos_drawer add ref_no char(30)");
			}
			
			$this->update(113);
		}
		
		if($this->ver < 114){
		
			if($this->check_need_alter("pos_items",array("barcode"),array("barcode"=>"char(20)"))){
				
				$con->exec("alter table pos_items modify barcode char(20)", true);
			}
			$this->update(114);
		}
		
		
		if($this->ver < 115){
			//1/08/2012 1:30 pm Kee Kee
			$con->exec("create table if not exists pos_member_point_adjustment(
				pos_id int(11),
				counter_id int(11),
				branch_id int(11),
				nric char(30),
				card_no char(30),
				adjust_date timestamp default 0,
				points int(11),
				reason text,
				remark text,
				type char(30),
				date date default 0,
				is_delivery integer default 0,
				delivery_date timestamp default 0,
				delivery_name char(100),
				delivery_address char(100),
				delivery_phone char(50),
				ref_receipt_ref_no varchar(20),
				user_id integer,
				primary key(counter_id,branch_id,pos_id,date,type)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			//3/08/2012 10:10 AM Kee Kee
			$con->exec("create table if not exists membership_points (	
				nric varchar(20),	
				card_no varchar(20),			
				branch_id int(11),	
				date timestamp,
				points int(11),
				remark char(50),
				type enum('POS','REDEEM','ADJUST','CANCELED','AUTO_REDEEM') default 'ADJUST',			
				user_id int(11), 
				point_source varchar(30) not null default 'backend',
				sync integer default 0,
				primary key(card_no,branch_id,date,type)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			$this->update(115);
		}
		
		if($this->ver < 116){
			//04/10/2012 5:50 PM Kee Kee
			$con->exec("create table if not exists pos_user_log(
				id integer,
				branch_id integer,
				counter_id integer,
				date date,
				cashier_id integer,
				type char(20),
				timestamp timestamp,
				ref_no char(20),
				primary key(id,branch_id,counter_id,date)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
				
			//08/10/2012 2:03 PM Kee Kee
			if($this->check_need_alter("pos",array("mix_n_match_point","refund_by"))){
				$con->exec("alter table pos add mix_n_match_point blob", true);
				$con->exec("alter table pos add refund_by integer", true);	
			}
			
			if($this->check_need_alter("pos_items",array("member_point"))){
				$con->exec("alter table pos_items add member_point blob", true);
			}
			
			$this->update(116);
		}
		//Commit by Kee Kee on 10/15/2012 10:45 AM
		
		if($this->ver < 117){
			if($this->check_need_alter("pos_user_log",array("sync"))){
				$con->exec("alter table pos_user_log add sync integer default 0", true);
			}
			
			$this->update(117);
		}
		//Commit by Kee Kee on 10/22/2012 10:45 AM
		
		if($this->ver < 118){
			if($this->check_need_alter("pos",array("quota_used","quota_over_by","staff_approved_by","pos_more_info","sales_order_id","sales_order_branch"))){
				$con->exec("alter table pos add quota_used double not null default 0",true);
				$con->exec("alter table pos add quota_over_by int default 0",true);
				$con->exec("alter table pos add staff_approved_by int default 0",true);
				$con->exec("alter table pos add pos_more_info text",true);
				$con->exec("alter table pos add sales_order_id int",true);
				$con->exec("alter table pos add sales_order_branch_id int",true);
			}
			
			if($this->check_need_alter("pos_items",array("quota_used"))){
				$con->exec("alter table pos_items add quota_used double not null default 0",true);
			}		
			$this->update(118);
		}
		//Commit by Kee Kee on 03/05/2013 9:46 AM		
		
		if($this->ver < 119){
			//03-18-2013 11:47 AM Kee Kee Add sync error
			if($this->check_need_alter("counter_status",array("sync_error"))){
				$con->exec("alter table counter_status add sync_error text",true);
			}	
			
			$con->exec("create table if not exists pos_error(id integer,
			branch_id integer,
			counter_id integer,
			date date,
			error_type char(20),
			error_message text,
			timestamp timestamp, 
			sync tinyint(1) default 0, 
			primary key(id,branch_id,counter_id,date)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			$this->update(119);
		}
		//Commit by Kee Kee on 04/04/2013 11:02 AM	
		
		if($this->ver < 200){
			//08-22-2013 11:06 AM Kee Kee Add cancel_date into pos_deposit_status, pos_deposit_status_history
			if($this->check_need_alter("pos_deposit_status",array("cancel_date","last_update"))){
				$con->exec("alter table pos_deposit_status add cancel_date date",true);
				$con->exec("alter table pos_deposit_status add last_update timestamp default 0",true);
			}			
			
			if($this->check_need_alter("pos_deposit_status_history",array("cancel_date","approved_by"))){
				$con->exec("alter table pos_deposit_status_history add cancel_date date",true);
				$con->exec("alter table pos_deposit_status_history add approved_by integer",true);
			}	
			
			$con->exec("alter table pos_deposit_status_history drop primary key, add primary key  (`branch_id`,`counter_id`,`pos_date`,`pos_id`, added)",true);
			
			
			$this->update(200);
		}
		//Commit by Kee Kee on 09/11/2013 4:14 PM
		
		if($this->ver < 201)
		{
			if($this->check_need_alter("pos_drawer",array('type'))){				
				$con->exec("alter table pos_drawer add type char(20) default 'POS', add index type (type)");
			}
			$this->update(201);
		}		
		//Commit by Kee Kee on 01/07/2014 2:47 PM
		if($this->ver < 202)
		{
			if($this->check_need_alter("pos_payment",array('is_abnormal'))){				
				$con->exec("alter table pos_payment add is_abnormal integer default 0");
			}
			$this->update(202);
		}
		//Commit by Kee Kee on 27/2/2014 4:21PM

		if($this->ver < 203)
		{
			if($this->check_need_alter("pos_cash_domination",array('is_from_backend'))){				
				$con->exec("alter table pos_cash_domination add is_from_backend tinyint(1) default 0");
			}
			if($this->check_need_alter("membership_promotion_items",array('cancelled'))){				
				$con->exec("alter table membership_promotion_items add cancelled tinyint(1) default 0");
			}

			if($this->check_need_alter("pos",array("is_print_full_tax_invoice","print_full_tax_invoice_remark","is_special_exemption","special_exempt_approve_by","special_exempt_remark"))){
				$con->exec("alter table pos add is_print_full_tax_invoice integer default 0, add print_full_tax_invoice_remark text, add is_special_exemption integer default 0, add special_exempt_approve_by integer, add special_exempt_remark text, add service_charges double, add service_charges_gst_amt double, add total_gst_amt double, add is_gst integer(1) default 0");
			}

			if($this->check_need_alter("pos_items",array("inclusive_tax","is_foc"))){
				$con->exec("alter table pos_items add inclusive_tax integer default 0, add tax_code char(10), add tax_indicator char(10), add tax_amount double, add tax_rate double, add before_tax_price double, add discount2 double default 0 after discount, add is_foc integer(1) default 0");
			}

			if($this->check_need_alter("pos_deposit",array("gst_amount","gst_info"))){
			    $con->exec("alter table pos_deposit add gst_info text, add gst_amount double");
		    }

			$con->exec("create table if not exists
				pos_credit_note(
					id integer,
					credit_note_no char(30),
					credit_note_ref_no char(30),
                    branch_id integer NOT NULL default '0',
					counter_id integer default 1,
					pos_id integer,
					date date,
					return_receipt_no char(30),
					return_date date,
					company_name char(100),
					address text,
					gst_register_number char(30),
					customer_infor text,
					amount double,
					item_infor text,
					primary key(id,date,counter_id)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci
			");

			if($this->check_need_alter("pos_credit_note",array("amount","item_infor"))){
				$con->exec("alter table pos_credit_note add amount double, add item_infor text");
			}

			$this->update(203);
		}

		if($this->ver < 204)
		{
			$con->exec("create table if not exists pos_items_changes(
					branch_id int,
					counter_id int,
					date date,
					pos_id int,
					item_id int,
					type varchar(20),
					change_by int,
					info text,
					primary key(branch_id,counter_id,date,pos_id,item_id,type),
					index type (type)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");

			$con->query("show index from pos_credit_note where Key_name='primary'");
			if($con->sql_numrows()<5){
				$con->exec("ALTER TABLE pos_credit_note DROP PRIMARY KEY", true);
				$con->exec("ALTER TABLE pos_credit_note ADD PRIMARY KEY (branch_id, counter_id, pos_id, date, return_date)");
			}

			if(!$this->check_column_exist("pos_credit_note","id")){
				$con->exec("ALTER TABLE pos_credit_note DROP id", true);
			}

			if($this->check_need_alter("pos_deposit",array("gst_amount","gst_info"))){
			    $con->exec("alter table pos_deposit add gst_info text, add gst_amount double");
		    }

			$this->update(204);
		}

		if($this->ver < 205)
		{
			if($this->check_column_exist("pos_credit_note","id")){
				$con->exec("ALTER TABLE pos_credit_note DROP id", true);
			}

			$this->update(205);
		}
		
		if($this->ver < 206)
		{
			if($this->check_need_alter("pos_goods_return",array("goods_return_reason"))){
			    $con->exec("alter table pos_goods_return add goods_return_reason text");
		    }
			
			//03/10/2016 9:54AM Kee Kee
			$con->exec("CREATE TABLE if not exists pos_transaction_audit_log (
                branch_id int(11),
				counter_id int(11),
				date date,
				audit_log mediumtext,
				lastupdate timestamp default 0,
				sync int(11) default 0,
				index sync (sync),
                PRIMARY KEY (branch_id,counter_id,date)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			//03/17/2016 9:49AM Kee Kee
			$con->exec("CREATE TABLE if not exists pos_transaction_ejournal (
                branch_id int(11),
				counter_id int(11),
				date date,
				ejournal_log mediumtext,
				lastupdate timestamp default 0,
				sync int(11) default 0,
				index sync (sync),
                PRIMARY KEY (branch_id,counter_id,date)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");		

			if($this->check_need_alter('pos_delete_items', array('more_info'))){
				$con->exec("alter table pos_delete_items add more_info text", true);
			}
			$this->update(206);
		}
		
		if($this->ver < 207){
			
			// 11/8/2016 16:05 Qiu Ying - Add new column in pos_goods_return
			if($this->check_need_alter("pos_goods_return",array("return_receipt_ref_no"))){
				$con->exec("alter table pos_goods_return add return_receipt_ref_no char(20)", true);
			}
			
			$this->update(207);
		}
		
		if($this->ver < 208){			
			//2016-12-28 9:34 AM Kee Kee
			$q1 = $con->query("show index from pos_credit_note where Key_name='primary'", true);
			$field_rows = $con->sql_numrows($q1);
			$con->sql_freeresult($q1);
			if($field_rows<6){
				$con->exec("alter table pos_credit_note drop primary key, add primary key(`branch_id`,`counter_id`,`pos_id`,`date`,`return_date`,`credit_note_no`)", true);
			}			
			
			$this->update(208);
		}
		
		if($this->ver < 209){
			$con->exec("alter table pos_deposit_status_history modify added timestamp default 0");
			
			// Update all record into counter_sales_record table	
			/*if($hqcon)
			{
				$table = array("pos","pos_cash_domination","pos_cash_history","pos_deposit_status","pos_deposit_status_history","pos_drawer","pos_goods_return","pos_mix_match_usage","pos_receipt_cancel","sn_info","membership_promotion_items","membership_promotion_mix_n_match_items");
				foreach($table as $t)
				{
					if($t=="pos_deposit_status_history")
						$datefields = "pos_date";
					else
						$datefields = "date";
					$q1 = $con->query("select count(*) as total,branch_id,counter_id,$datefields from $t group by branch_id,counter_id,$datefields");
					while($r = $con->sql_fetchassoc($q1))
					{
						$q2 = $con->query("select count(*) as total from $t where branch_id = ".ms($r['branch_id'])." and counter_id = ".ms($r['counter_id'])." and $datefields=".ms($r['date'])." and sync = 1");
						while($r1 = $con->sql_fetchassoc($q2))
						{
							$upd = array();
							$upd['branch_id'] = $r['branch_id'];	
							$upd['counter_id'] = $r['counter_id'];
							$upd['date'] = $r['date'];	
							$upd['tablename'] = $t;		
							$upd['total_record'] = $r['total'];					
							$upd['synced_record'] = $r1['total'];			
							$upd['missing_record'] = "0";	
							$upd['added'] = "CURRENT_TIMESTAMP";
							$upd['lastupdate'] = "CURRENT_TIMESTAMP";
							$hqcon->exec("replace into counter_sales_record ".mysql_insert_by_field($upd));
							unset($upd);
						}
						$con->sql_freeresult($q2);
					}
					$con->sql_freeresult($q1);
				}
			}*/				

			$this->update(209);
		}
		
		if($this->ver < 210){
			/* Alter table and create table run in maximus, when need to commit the file please to check version */	
			
			//05/19/2017 10:59 AM Kee Kee - Added new table "pos_transaction_clocking_log"
			$con->exec("CREATE TABLE if not exists pos_transaction_clocking_log (
							id int(11),
							user_id int(11),
							branch_id int(11),
							counter_id int(11),
							counter_date timestamp default 0,
							from_server int(11) default 1,
							timezone char(100) default 'Asia/Kuala_Lumpur', 
							sync int(11) default 0,
							primary key(branch_id, counter_id, id)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			if($this->check_need_alter("counter_status",array("revision_type")))
			{
				$con->exec("alter table counter_status add revision_type char(30)");
			}		
			
			$this->update(210);
		}
		
		if($this->ver < 211){
			
			//05/19/2017 11:36 AM Kee Kee - Added new column "pos_transaction_clocking_log"
			if($this->check_need_alter("pos_transaction_clocking_log",array("adjust_type","more_info"))){
				$con->exec("alter table pos_transaction_clocking_log add adjust_type integer");
				$con->exec("alter table pos_transaction_clocking_log add more_info text");
			}
			
			$this->update(211);
		}
		
		if($this->ver < 212){
			$q1 = $con->query("show index from pos_transaction_clocking_log where Key_name='primary'");
			if($con->sql_numrows($q1)==4){
				$con->exec("alter table pos_transaction_clocking_log drop primary key, add primary key (branch_id, counter_id, id)");
			}
			$con->sql_freeresult($q1);
			
			$con->exec("alter table pos_transaction_clocking_log add index bid_cid_time (branch_id, counter_id, counter_date)");
			
			$this->update(212);
		}
		
		if($this->ver < 213){
			// alter table from myisam to innoDB, some table will unable to convert due to using multiple primary key with auto_increment
			$q1 = $con->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA ='sync_server' and engine ='myisam'");
			while($r = $con->sql_fetchassoc($q1)){
				// try to convert every table, some will failed
				//print "\nalter table ".$r['TABLE_NAME']." ENGINE=InnoDB\n";
				$success = $con->exec("alter table ".$r['TABLE_NAME']." ENGINE=InnoDB");
				
				// ignore if failed
				//if(!$success)	print $con->exec_sql_error();
			}
			$con->sql_freeresult($q1);
			
			// Sync Server Enhancement - Improve Speed and Performance
			$con->exec("alter table tmp_trigger_log add index tablename_n_row_index (tablename, row_index)");
			
			// add index
			$con->exec("alter table pos add index sync (sync), add index transaction_sync (transaction_sync)");
			$con->exec("alter table pos_deposit_status add index sync (sync)");
			$con->exec("alter table pos_deposit_status_history add index sync (sync)");
			$con->exec("alter table pos_drawer add index sync (sync)");
			$con->exec("alter table pos_error add index sync (sync)");
			$con->exec("alter table pos_transaction_audit_log add index sync (sync)");
			$con->exec("alter table pos_transaction_ejournal add index sync (sync)");
			$con->exec("alter table pos_transaction_clocking_log add index sync (sync)");
			$con->exec("alter table pos_user_log add index sync (sync)");
			$con->exec("alter table pos_cash_history add index sync (sync)");
			$con->exec("alter table pos_cash_domination add index sync (sync)");
			$con->exec("alter table membership_points add index sync (sync)");
			$con->exec("alter table sku_items_temp_price add index sync (sync)");
			$con->exec("alter table sku_items_temp_price_history add index sync (sync)");
			
			// Fix timestamp bug - default need to be zero
			$con->exec("alter table pos_user_log modify `timestamp` timestamp default 0");
			$con->exec("alter table sku_items_temp_price modify `lastupdate` timestamp default 0");
			$con->exec("alter table sku_items_temp_price_history modify `added_datetime` timestamp default 0");
			$con->exec("alter table pos_error modify `timestamp` timestamp default 0");
			
			$this->update(213);
		}
		
		if($this->ver < 214){
			//11/07/2017 17:51 PM Kee Kee - Added new column "reason" into pos_cash_history
			if($this->check_need_alter("pos_cash_history",array("reason"))){
				$con->exec("alter table pos_cash_history add reason char(50),add index reason (reason)");
			}
			$this->update(214);
		}
		
		if($this->ver < 215){
			// 3/13/2019 10:31 AM Andy - eWallet Feature
			if($this->check_need_alter("pos_payment", array("group_type"))){
				$con->exec("alter table pos_payment add group_type char(30) not null, add index group_type (group_type)");
			}
			$this->update(215);
		}
		
		if($this->ver < 216){
			// 3/13/2019 10:31 AM Andy - eWallet Feature
			if($this->check_need_alter("pos", array("pos_guid"))){
				$con->exec("alter table pos add pos_guid char(36) not null, add index pos_guid (pos_guid)");
			}
			$this->update(216);
		}
		
		if($this->ver < 217){
			// 7/8/2019 11:42 AM Justin - Self Checkout Feature
			if($this->check_need_alter("pos_items", array("is_valid_weight"))){
				$con->exec("alter table pos_items add is_valid_weight tinyint(1) not null default 1, add index is_valid_weight (is_valid_weight)");
			}
			
			if($this->check_need_alter("pos", array("pos_is_valid_weight", "use_scale_machine"))){
				$con->exec("alter table pos add pos_is_valid_weight tinyint(1) not null default 1, add use_scale_machine tinyint(1) not null default 0, add index use_scale_machine_valid_weight (use_scale_machine, pos_is_valid_weight)");
			}
			
			$this->update(217);
		}
		
		if($this->ver < 218){
			// 9/20/2019 5:47 PM Justin - SKU Description
			if($this->check_need_alter("pos_items",array("sku_description"),array("sku_description"=>"char(40)"))){
				$con->exec("alter table pos_items modify sku_description char(40)");
			}
			$this->update(218);
		}
		
		if($this->ver < 219){
			// 12/12/2019 5:34 PM Andy
			$con->query("show index from pos_receipt_cancel where Key_name='primary'");
			if($con->sql_numrows()<5){
				$con->exec("alter table pos_receipt_cancel drop primary key, add primary key (branch_id,counter_id,date,id,receipt_no)", true);
			}
			
			// 12/13/2019 11:53 AM Justin - Counter Setup Information
			$con->exec("CREATE TABLE if not exists `pos_counter_collection_configuration` (
				branch_id int(11),
				counter_id int(11),
				pos_image_server text not null,
				hq_server text not null,
				masterfile_sync_server tinyint(1) default 0,
				sales_sync_server tinyint(1) default 0,
				sync_server text not null,
				sync_server_up_sales text not null,
				last_update timestamp default 0,
				sync tinyint(1) default 0,
				primary key(branch_id, counter_id),
				index sync (sync)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci
			");
			$this->update(219);
		}
		
		if($this->ver < 220){
			// 1/16/2020 1:45 PM Justin - POS Membership GUID			
			if($this->check_need_alter("pos", array("membership_guid"))){
				$con->exec("alter table pos add membership_guid char(36) not null, add index membership_guid (membership_guid)");
			}
			
			$this->update(220);
		}
		
		if($this->ver < 221){
			$con->query("show index from pos_receipt_cancel where Key_name='primary'");
			if($con->sql_numrows()<5){
				$con->exec("alter table pos_receipt_cancel drop primary key, modify receipt_no int(11) NOT NULL DEFAULT '0', add primary key (branch_id,counter_id,date,id,receipt_no)", true);
			}
			
			$this->update(221);
		}
		
		if($this->ver < 222){
			//1/8/2021 11:01 AM Shane - Add pos_day_start and pos_day_end table
			//1/15/2021 2:55 PM Shane - Changed pos_day_start.time and pos_day_end.time timestamp
			$con->exec("create table if not exists pos_day_start (
				branch_id int not null default 0,
				counter_id int not null default 0,
				date date not null,
				user_id int,
				time timestamp default 0,
				sync tinyint(1) default 0,
				primary key(branch_id, counter_id, date),
				index counter_id (counter_id),
				index date (date),
				index user_id (user_id),
				index sync (sync)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");

			$con->exec("create table if not exists pos_day_end (
				branch_id int not null default 0,
				counter_id int not null default 0,
				date date not null,
				user_id int,
				time timestamp default 0,
				sync tinyint(1) default 0,
				primary key(branch_id, counter_id, date),
				index counter_id (counter_id),
				index date (date),
				index user_id (user_id),
				index sync (sync)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(222);
		}
		
		if($this->ver < 223){
			// 2/4/2021 12:22 PM Andy - Tax
			if($this->check_need_alter("pos", array("is_tax_registered"))){
				$con->exec("alter table pos add column is_tax_registered tinyint(1) not null default 0, add index is_tax_registered (is_tax_registered)");
			}
			
			$this->update(223);
		}
		
		if($this->ver < 224){
			// 4/22/2021 2:35 PM Shane - Added new column pos_day_end.eod_data
			if($this->check_need_alter("pos_day_end", array("eod_data"))){
				$con->exec("alter table pos_day_end add column eod_data text");
			}
			$this->update(224);
		}
		
		if($this->ver < 225){
			// 5/3/2021 9:48 AM Andy - Increase pos.member_no to char(30)
			if($this->check_need_alter("pos",array("member_no"),array("member_no"=>"char(30)"))){
				$con->exec("alter table pos modify member_no char(30)");
			}
			$this->update(225);
		}
		
		/*End Alter table and create table*/
		/*
			
		*/
		
		$con->commit();
	}
	
	/*private function check_need_alter($tbl, $col_list = array()){
		global $con;
		
		if(!$tbl || !$col_list) die('Invalid table or column name.');
		
		// get column list
		$curr_tbl_col_list = array();
		$q1 = $con->query("explain $tbl", true);
		while($r = $con->sql_fetchassoc($q1)){
            $curr_tbl_col_list[] = $r['Field'];
		}
		$con->sql_freeresult($q1);
		
		foreach($col_list as $col){
			if(!in_array($col, $curr_tbl_col_list)){
				return true;    // need alter
			}
		}
		return false;   // all column need to alter already exists, so no need to alter again
	}*/
	
	private function check_need_alter($tbl, $col_list = array(), $col_type_list = array()){
		global $con;

		if(!$tbl || !$col_list) die('Invalid table or column name.');

		// get column list
		$curr_tbl_col_list = array();
		$q1 = $con->query("explain $tbl", true);
		
		while($r = $con->sql_fetchassoc($q1)){
			// this column need to check
			if(in_array($r['Field'], $col_list) && isset($col_type_list[$r['Field']])){
				$type = $col_type_list[$r['Field']];
				if($r['Type'] != $type)	return true;	// need alter
			}
            $curr_tbl_col_list[] = $r['Field'];
		}
		$con->sql_freeresult($q1);

		foreach($col_list as $col){
			if(!in_array($col, $curr_tbl_col_list)){
				return true;    // need alter
			}
		}
		return false;   // all column need to alter already exists, so no need to alter again
	}

	private function check_column_exist($tbl,$col=""){
		global $con;

		$q1 = $con->query("explain $tbl", true);

		while($r = $con->sql_fetchassoc($q1)){
			$curr_tbl_col_list[] = $r['Field'];
		}
		$con->sql_freeresult($q1);

		if(in_array($col, $curr_tbl_col_list)){
			return true;
		}

		return false;
	}
}

$maintenance = new Maintenance();

?>
