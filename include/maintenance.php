<?php
/*
1/20/2016 4:35 PM Andy
- truncate old maintenance version code.

3/14/2016 10:44 AM Andy
- v286
- Preparation for Accounting Export.

3/18/2016 10:51 AM Andy
- v287
- Preparation for DO Checkout.

3/22/2016 5:54 PM Andy
- v288
- Preparation for Accounting Export.

4/5/2016 5:36 PM Andy
- v289
- Preparation for Accounting Export.

4/18/2016 10:10 AM Andy
- v290
- Preparation for Sales Order/DO parent stock balance.

7/18/2016 1:41 PM Andy
- v291
- Preparation for User Status Locked.

7/26/2016 1:44 PM Andy
- v292
- Preparation for Purchase Agreement Enhancement
- Fixed missing gst code for TX-FR.

8/1/2016 5:48 PM Andy
- v293
- Preparation for ARMS POS BETA 297.
- Upload POS Ejournal and Audit Log.

8/11/2016 9:50 AM Andy
- v294
- Preparation for DO Printed.
- Fixed category discount when category level more than 3.

8/18/2016 9:47 AM Andy
- v295
- Change admin and arms password.

9/5/2016 3:54 PM Andy
- Fixed v292 bug, which causing gst listing become only got 1 row for new customer.

9/6/2016 10:53 AM Andy
- v296
- Preparation for SKU old code extend to 20 char.

9/9/2016 11:59 AM Andy
- v297
- Preparation for Purchase Agreement Remark.

9/27/2016 11:51 AM Andy
- v298
- Preparation for ARMS User.

9/28/2016 4:03 PM Andy
- v299
- Grant all branch privilege to arms personal user.

11/1/2016 1:44 PM Andy
- v300
- Preparation for Debtor GST.

11/21/2016 2:28 PM Andy
- v301
- Backup & Remove the POS Settings Currency.

11/23/2016 10:00 AM Andy
- v302
- Preparation for Counter Collection Finalise.

11/24/2016 2:40 PM Andy
- v303
- Preparation for POS Counter BETA v300.

12/1/2016 4:12 PM Andy
- v304
- Preparation for Vendor Internal Code.

12/19/2016 10:05 AM Andy
- Enhanced to skip run tmp table when found defined SYNC_SERVER.

12/28/2016 10:25 AM Andy
- v305
- Fixed POS Credit Note Primary Key.

2/14/2017 4:11 PM Andy
- v307
- Add index for vendor_sku_history.

2/17/2017 10:35 AM Andy
- v308
- Auto add security_deposit_type in gst_settings if found gst is active.

2/23/2017 11:27 AM Andy
- v309
- Fix Payment Voucher table not exists.

3/2/2017 11:11 AM Andy
- v310
- Preparation for PO Enhancement.

3/3/2017 9:25 AM Andy
- v311
- Preparation for ARMS POS v190.

3/22/2017 5:07 PM Andy
- v312
- Preparation for PO Enhancement.

3/27/2017 11:00 AM Andy
- v313
- Preparation for Custom Accounting Export.

4/3/2017 11:50 AM Andy
- v314
- Preparation for Counter Sync Record.
- Remove user arms_zaini.

4/6/2017 3:55 PM Andy
- v315
- Fixed counter_sales_record data type error.

4/10/2017 10:10 AM Andy
- v316
- Fixed member_sales_cache_b% collate.
- Preparation for Sync Server Error Log.

4/25/2017 2:53 PM Andy
- v317
- Preparation for Item "Not Allow Discount".

5/16/2017 5:12 PM Andy
- v318
- Preparation for GRN FOC Qty and Discount.

5/19/2017 10:46 AM Andy
- v319
- Preparation for ARMS POS Clocking System (v192).

5/22/2017 10:00 AM Andy
- v320
- Fixed ARMS POS v191 table name problem.

6/2/2017 9:58 AM Andy
- v321
- Preparation for Credit Note Multiple Invoice Return.

6/16/2017 12:13 PM Andy
- v322
- Preparation for DO BOM Package.

6/28/2017 11:53 AM Andy
- v323
- Drop counter_sales_record, pos_sync_server_tracking and pos_sync_server_counter_tracking.

7/5/2017 1:40 PM Andy
- v324
- Preparation for Vendor PO with multiple department.

7/11/2017 1:57 PM Andy
- v325
- Fixed Payment Voucher module table missing.

8/4/2017 5:42 PM Andy
- v326
- Preparation for GST Second Tax Code and Custom Export Accounting Templates.

8/8/2017 2:36 PM Andy
- v327
- Auto insert Sales Order Privileges for all users. SO_EDIT and SO_REPORT.

8/15/2017 1:22 PM Andy
- Change arms_tommy password.

9/6/2017 5:03 PM Andy
- v328
- Preparation for Credit Sales DO Debtor Block List.

9/12/2017 1:50 PM Andy
- v329
- Set to recalculate all sku after 2017-09-01.

9/19/2017 6:01 PM Andy
- Inactive user arms_kimseng.

9/21/2017 5:11 PM Andy
- v331
- Inactive user arms_qiuying.

9/25/2017 3:29 PM Andy
- v332
- Alter table for Clocking System.

10/9/2017 10:02 AM Andy
- v333
- Fix pos_transaction_clocking_log primary key.

10/9/2017 10:36 AM Andy
- v334
- Set to recalculate all sku after 2017-09-01.

10/25/2017 2:58 PM Andy
- v335
- Delete old pos_transaction_sync_server_counter_tracking errors.

11/8/2017 2:15 PM Andy
- v336
- Preparation for Special Exemption Relief Claus Remark.

11/15/2017 4:09 PM Andy
- v337
- Preparation for GRR allow different department.

11/23/2017 5:16 PM Andy
- v338
- Preparation for Cash Advance Reason.
- Fixed some table timestamp column bug.

11/28/2017 11:08 AM Andy
- v339
- Add arms user 'arms_tom'.

12/1/2017 2:36 PM Andy
- v340
- Inactive user arms_yihc.

12/12/2017 3:06 PM Andy
- v341
- Preparation for DO Delivery Address.
- Preparation for member sales cache.

12/18/2017 3:01 PM Andy
- v342
- Preparation for Accounting Export Fixes

12/22/2017 11:15 AM Andy
- v343
- Add arms user 'arms_soonkeat'.

1/15/2017 1:13 PM Andy
- v344
- Add arms user 'arms_yctan'.

2/22/2018 2:25 PM Andy
- v345
- Fix some new customer or branch sales cache table is not up to date.

2/27/2018 9:30 AM Andy
- v346
- Inactive user arms_keekee.

3/8/2018 4:40 PM
- v347
- Sales Agent Cache Table modified to store data by date.

3/22/2018 5:52 PM Andy
- v348
- Inactive user arms_soonkeat.

3/27/2018 5:54 PM Andy
- v349
- Preparation for Work Order.

4/2/2018 5:42 PM Andy
- v350
- Inactive user arms_tom
- Add arms user 'arms_ashlyn'.

4/9/2018 10:32 AM Andy
- v351
- Preparation for Sales Agent Cache Enhancement.

5/2/2018 12:01 PM Andy
- v352
- Add arms user 'arms_gwc'.

6/5/2018 11:28 AM Andy
- v353
- Preparation for Foreign Currency Feature.

6/6/2018 6:04 PM Justin
- Fixed v353 error.

6/8/2018 5:45 PM Andy
- v354
- Modified Foreign Currency Table.

7/13/2018 3:39 PM Andy
- Tmp v354.
- Fixed BOM SKU.

7/31/2018 10:24 AM Andy
- v355
- Preparation for Send Email Module.

8/15/2018 2:26 PM Andy
- v356
- Preparation for Stock Reorder / Sales Order / DO intergration.

8/29/2018 3:29 PM Andy
- v357
- Preparation for Vendor, GRR, GRN SST Tax.

9/3/2018 5:36 PM Andy
- v358
- Inactive user "arms_lan".

9/6/2018 2:25 PM Andy
- v359
- Change counter_status revision to char(10).

9/14/2018 11:13 AM Andy
- v360
- Preparation for Debtor Price.

9/26/2018 Andy
- v361 and v362
- Inactive user "arms_wc".
- Fixed Batch Selling Price Change QPrice Min Qty cannot exceed 127.

9/27/2018 5:55 PM Andy
- v363
- Preparation for Announcement Manager. 

10/1/2018 10:34 AM Andy
- v364
- Added arms user "arms_nava".

10/2/2018 10:28 AM Andy
- v365
- Preparation for Front End POS Foreign Currency.
- Convert counter_status to innoDB.

10/2/2018 4:06 PM Andy
- v366
- Fixed branch_approval_history primary key and added.

10/9/2018 12:11 PM Andy
- v367
- Added arms user "arms_lam" and "arms_mark".

10/12/2018 5:19 PM Andy
- v368
- Preparation for DO Checkout Shipment Method and Tracking Code.
- Preparation for GRR Override Expiry PO.

10/18/2018 11:13 AM Andy
- v369
- Alter Table to innoDB Batch 8.1

10/19/2018 2:31 PM Andy
- v370
- Added arms user "arms_lam" and "arms_hocklee".

10/22/2018 11:09 AM Andy
- v371
- Added arms user "arms_lam" and "arms_brandon".

11/9/2018 2:24 PM Andy
- v372
- Inactive user "arms_lam".

11/12/2018 5:46 PM Andy
- v373
- Preparation for SKU Future Price Change Remark.

11/23/2018 3:58 PM Andy
- TMP v355
- Preparation for Create Multiple Transfer DO by CSV.

12/04/2018 2:47 PM Andy
- v374
- Preparation for SKU Type Concessionaire and Quotation Cost.

12/06/2018 11:59 AM Andy
- v375
- Fixed daily_sales_cache_b table lack of currency column.

12/06/2018 5:39 PM Andy
- v376
- Preparation for GRA Rounding Adjustment.

12/18/2018 4:56 PM Andy
- v377
- Preparation for Suite Device and ARMS Internal API.

1/22/2019 11:34 AM Andy
- v378
- Added index for sku_items.
- Preparation for Masterfile UOM Enhancement.

2/1/2019 10:36 AM Andy
- v379
- Preparation for Replica Status Server Remark.
- Preparation for Business Intelligent App.

2/12/2019 10:42 AM Andy
- v380
- Added index for pos.receipt_ref_no

2/15/2019 11:16 AM Andy
- v381
- Inactive user "arms_hocklee" and "arms_mark".

2/18/2019 9:13 AM Andy
- TMP v380
- Add do_date and remark for tmp_generate_do.

2/20/2019 9:40 AM Andy
- Removed "Partitioning" in v379 due to Aneka MySQL 5.0 is not supporting partition feature.

3/7/2019 2:35 PM Andy
- v382
- Added arms user "arms_sllee".

3/14/2019 5:23 PM Andy
- v383
- Preparation for DO Invoice Amount Adjustment.

3/19/2019 9:26 AM Andy
- v384
- Preparation for eWallet Payment.

3/28/2019 10:17 AM Andy
- v385
- Preparation for POS GUID.
- innodb batch 10.

4/16/2019 9:29 AM Andy
- v386
- Preparation for ARMS Accounting Integration - AP
- innodb batch 8.

4/17/2019 4:02 PM Andy
- v387
- Preparation for eWallet paydibs.

4/18/2019 2:00 PM Andy
- v388
- Preparation for ARMS Accounting Integration - CS

5/3/2019 10:16 AM Andy
- v389
- Preparation for Consignment Sales Entry.
- innoDB batch 2.

5/7/2019 3:42 PM Andy
- v390
- Preparation for Debtor Default Sales Agent.

5/17/2019 2:09 PM Andy
- v391
- Fixed Debit Note Total Amount zero issue.
- innodb batch 6.1

5/22/2019 2:53 PM Andy
- v392
- Preparation for OS Trio Integration.

5/28/2019 5:34 PM Andy
- Fixed ostrio_vendor_mapping.ostrio_vendor_id should be char.
- Fixed ostrio_debtor_mapping.ostrio_debtor_id should be char.

5/29/2019 10:43 AM Andy
- v393
- Fixed ostrio_gl_mapping.doc_id should be bigint.
- innodb batch 6.2

5/29/2019 4:38 PM Andy
- v394
- Preparation for ARMS Accounting Integration AR

5/30/2019 4:07 PM Andy
- v395
- Preparation for DO Relationship Link

6/7/2019 10:00 AM Andy
- v396
- Preparation for Stock Reorder MOQ.

6/10/2019 4:17 PM Andy
- v397
- Added arms user "arms_joe".

6/26/2019 3:28 PM Andy
- v398
- Create table "system_settings".

6/28/2019 2:36 PM Andy
- v399
- Preparation for Branch Vertical Logo

7/4/2019 11:37 PM Andy
- v400
- Preparation for DO Checkout Checklist.
- innodb batch 6.3

7/10/2019 2:47 PM Andy
- v401
- Preparation for Membership Mobile App.
- Preparation for Self Checkout Counter.

7/12/2019 9:21 AM Andy
- v402
- Insert data for membership mobile advertisement.

7/26/2019 2:40 PM Andy
- v403
- Inactive user 'arms_zae'.
- Fixed eWallet Payment.
- Removed unused membership column.

7/26/2019 2:40 PM Andy
- v404
- Added arms user 'arms_andrew' and 'arms_eekean'.

8/9/2019 2:27 PM Andy
- v405
- Preparation for Cycle Count.

8/29/2019 2:05 PM Andy
- v406
- Preparation for Membership Notice Board.

9/10/2019 3:10 PM Andy
- v407
- Preparation for DO Custom Column.
- innodb batch 4.

9/19/2019 3:55 PM Andy
- v408
- Fixed DO Custom Column for Open Item.

9/24/2019 10:22 AM Andy
- v409
- Preparation for Coupon Enhancement.
- Fixed pos_items.sku_description to char(40).

9/24/2019 3:10 PM Andy
- v410
- Preparation for Membership Profile Image
- Preparation for PDA PO.

10/2/2019 9:09 AM Andy
- v411
- Preparation for SKU Model, Height, Width and Length
- Add index for sku_items.sku_apply_items_id

10/3/2019 11:37 AM Andy
- v412
- Preparation for SKU POS Photo can download to POS Counter.

10/7/2019 1:05 PM Andy
- v413
- Preparation for DO Confirm Timestamp.

10/8/2019 12:43 PM Andy
- TMP v410
- Preparation for Generate DO with Open Items.

10/15/2019 4:39 PM Andy
- v414
- Added arms user 'arms_aaron'.

10/17/2019 5:16 PM Andy
- v415
- Preparation for DO Checklist enhancement.

10/22/2019 3:08 PM Andy
- v416
- Preparation for Sales Agent Photo.
- Preparation for Membership Package.

10/25/2019 1:56 PM Andy
- v417
- Inactive user 'arms_nava'.
- Change arms user password to md5 encoded.

10/29/2019 2:01 PM Andy
- v418
- Preparation for Membership Remark.

10/30/2019 5:30 PM Andy
- v419
- innodb batch 3.

11/4/2019 5:02 PM Andy
- v420
- Inactive user 'arms_eekean'.

11/8/2019 4:10 PM Andy
- v421
- Inactive user 'arms_andrew'.
- Added arms user 'arms_jesse'.

11/14/2019 4:45 PM Andy
- v422
- Preparation for Sales Agent Ratio and Sales Range Enhancement.
- TMP v422
- Preparation for Create DO with Discount.

11/19/2019 3:42 PM Andy
- v423
- Preparation for ARMS Marketplace.
- innodb batch 9.
- v424
- Preparation for Time Attendance.

11/25/2019 1:51 PM Andy
- v425
- Update all bom sku_type to default outright if sku_type is empty.

11/26/2019 10:11 AM Andy
- v426
- Preparation for ARMS Marketplace Auto Login.

11/28/2019 3:46 PM Andy
- v427
- innodb batch 5.
- Modified Serial Number code to 30 char.

12/5/2019 10:39 AM Andy
- v428
- innodb batch 1.0

12/9/2019 4:49 PM Andy
- v429
- Preparation for Member Mobile App OTP Setup.
- Preparation for Branch Photo, Operation Time, longitude and latitude.
- Preparation for Coupon Enhancement.
- Preparation for Membership Push Notification.

12/13/2019 5:45 PM Andy
- v430
- Fixed sa table primary key.
- innodb batch 1.1

12/17/2019 3:42 PM Andy
- v431
- convert sku_group to innoDB.

12/19/2019 9:56 AM Andy
- v432
- Preparation for Sales Agent KPI.

12/19/2019 1:20 PM Andy
- v433
- Preparation for Counter Setup Information.

12/20/2019 5:06 PM Andy
- v434
- Inactive user 'arms_joe'.

12/24/2019 2:21 PM Andy
- v435
- Preparation for Category Sales Trend Cache
- Preparation for ARMS POS privilege "POS_SCAN_MULTIPLY_QTY".
- v436
- convert sku_items to innodb.

1/7/2020 11:49 AM Andy
- v437
- Alter Branch, Vendor and Debtor company_no to char(30).
- innodb batch 11.1
- Added function "is_innodb".

1/15/2020 2:04 PM Andy
- v438
- Fixed sku_items_po_reorder primary key issue.
- Fixed table "log" timestamp.
- Added index for "stock_take_pre", "user" and "do_request_items".
- Preparation for Membership GUID.

1/20/2020 3:28 PM Andy
- Fixed v438 sql error.

1/22/2020 5:53 PM Andy
- v439
- Preparation for Time Attendance Enhancement.

2/5/2020 2:12 PM Andy
- v440
- Preparation for pos.membership_guid.

2/10/2020 2:12 PM Andy
- v441
- Preparation for User Department.
- Preparation for User Profile Photo.
- InnoDB Batch 13a.

2/11/2020 2:32 PM Andy
- v442
- Preparation for Cashier Setup User Department.

2/18/2020 11:55 AM Andy
- v443
- InnoDB Batch 13b.

2/18/2020 4:20 PM Andy
- v444
- Alter table "attendance_user_scan_record_modify_history" to add ocounter_id.

2/26/2020 9:26 AM Andy
- v445
- InnoDB Batch 11.2, 11.3, 13d.

3/2/2020 3:59 PM Andy
- v446
- Preparation for SKU Marketplace Description.

3/2/2020 4:00 PM Andy
- v447
- InnoDB Batch 12.

3/10/2020 11:13 AM Andy
- v448
- Preparation for Membership Coupon Referral Program.

3/12/2020 5:52 PM Andy
- v449
- InnoDB Batch 13e, 13f, 13g and 13l.

3/19/2020 3:57 PM Andy
- v450
- InnoDB Batch 13w and 13r.

3/23/2020 2:40 PM Andy
- v451
- Preparation for Custom Report.

3/25/2020 5:19 PM Andy
- v452
- InnoDB Batch 13u.

4/1/2020 1:59 PM Andy
- v453
- InnoDB Batch 13.2m.

4/8/2020 4:12 PM Andy
- v454
- InnoDB Batch 13.1m.

4/9/2020 5:22 PM Andy
- v455
- Added arms user 'arms_xinhui'.
- Inactive user 'arms_aaron'.

4/10/2020 2:32 PM Andy
- v456
- Add active and status for custom_report.

4/14/2020 11:21 AM Andy
- v457
- Preparation for Marketplace DO Enhancements.

4/15/2020 4:33 PM Andy
- TMP v457
- Fixed tmp_bom_items primary key.

5/5/2020 4:27 PM Andy
- v458
- InnoDB Batch 13p.
- Remove unique from user.ic_no

5/14/2020 12:17 PM Andy
- v459
- InnoDB Batch 13v.

5/18/2020 6:57 PM Andy
- v460
- Inactive user 'arms_gwc', 'arms_yctan', 'arms_justin', 'arms_xinhui.

5/21/2020 6:47 PM Andy
- v461
- InnoDB Batch 13t.

6/10/2020 3:47 PM Andy
- v462
- InnoDB Batch 13s.

6/19/2020 2:33 PM Andy
- v463
- New table "sku_items_finalised_cache".

6/24/2020 12:23 PM Andy
- v464
- InnoDB Batch 13.2c.

7/3/2020 4:51 PM Andy
- v465
- Added column "skip_dongle_checking" in table "suite_device".

7/15/2020 2:46 PM Andy
- v466
- Convert table ri_items to innoDB.
- Preparation for Monthly Closing Modules.
- Preparation for SKU Items Additional Description Prompt at POS Counter.

7/22/2020 2:25 PM Andy
- v467
- Preparation for Monthly Closing Module.
- Preparation for DO Paid & Payment.
- InnoDB Batch 13.1c.

7/24/2020 1:15 PM Andy
- v468
- Preparation for OSTrio AR Integration.

8/11/2020 3:18 PM Andy
- v469
- Added arms user 'arms_thooi'.

9/3/2020 1:55 PM Andy
- v470
- Convert stock_take_pre to innoDB.
- Preparation for Promotion Pop Cards.

9/17/2020 5:07 PM Andy
- v471
- Added arms user 'arms_jun'.

10/1/2020 2:16 PM Andy
- v472
- Added arms user 'arms_shane'.

10/12/2020 2:51 PM Andy
- v473
- Added arms user 'arms_darrell'.

10/13/2020 3:31 PM Andy
- v474
- Preparation for Membership Self Registration eForm.

10/22/2020 12:56 PM Andy
- v475
- Preparation for ARMS Fnb Suite Device and Upload POS API.

10/27/2020 5:27 PM Andy
- v476
- Preparation for PDA Batch Barcode Quantity.

11/17/2020 4:11 PM Andy
- v477
- Preparation for POS Announcement.
- Alter adjustment related tables to innoDB.

11/26/2020 5:34 PM Andy
- v478
- Preparation for SKU RSP.

12/8/2020 10:05 AM Andy
- v479
- Alter table for POS Announcement.

12/15/2020 11:21 AM Andy
- v480
- Activate arms user 'arms_yctan'.

12/21/2020 3:15 PM Andy
- v481
- Preparation for Custom Report's Report Settings.

1/7/2021 3:16 PM Andy
- v482
- Preparation for Speed99 Enhancements.

1/11/2021 3:50 PM Andy
- v483
- Preparation for POS Day Start / Day End.

1/18/2021 11:08 AM Andy
- v484
- Fixed pos_day_start and pos_day_end time.

1/19/2021 1:00 PM Andy
- v485
- Preparation for Membership "Patient Medical Record".

1/19/2021 1:45 PM Andy
- v486
- Preparation for ARMS Suite POS Device Management.

2/4/2021 4:59 PM Andy
- v487
- Alter counter_status.revision_type to char(100)
- Preparation for Sales Order Items Remark.

2/5/2021 1:08 PM Andy
- v488
- Preparation for Category POS Image.

3/2/2021 4:30 PM Andy
- v489
- Added index for membership.phone_3.

3/3/2021 12:58 PM Andy
- v490
- Preparation for User Application eForm.

3/10/2021 4:40 PM Andy
- v491
- Added index for branch.active.
- Added more column for eform_user.

3/11/2021 4:18 PM Andy
- v492
- Added speed99_warehouse.

3/15/2021 5:05 PM Andy
- v493
- Preparation for Work Order Transfer Type "Weight to Pcs".

4/5/2021 5:04 PM Andy
- v494
- Preparation for Category Hide at POS Counter.

4/16/2021 3:35 PM Andy
- v495
- Preparation for POS Counter Backend Mode.

4/19/2021 10:14 AM Andy
- v496
- Preparation for Komaiso Integration.
- Enhanced POS Device Management.

4/20/2021 10:33 AM Andy
- v497
- Add Ipay88 intergrator list.

4/26/2021 11:04 AM Andy
- v498
- Added pos_day_end.eod_data.

4/28/2021 9:15 AM Andy
- v499
- Added sku_items_smark.

5/3/2021 11:26 AM Andy
- v500
- Added eform_user.activated_by
*/
class Maintenance
{
	var $ver = 0;
	var $init_ver = 0;
	var $tmp_ver_file = 'tmp_version.txt';
	var $tmp_ver = 0;
	var $fp;	// file pointer
	var $fp_path = "include/maintenance.running";
	//var $sync_server_ver_file = 'sync_server_version.txt';
	//var $sync_server_ver = 0;
	
	function __construct(){
	    global $con, $sessioninfo;

		$this->fp_path = dirname(__FILE__)."/maintenance.running";	// use this prevent wrong "include" path
		
		// assign tmp version
		$this->get_tmp_version();

	    // assign version
		$this->init_ver = $this->ver = $this->get();

		$this->fp = fopen($this->fp_path, "w");
		chmod($this->fp_path, 0777);
		
		// only HQ run, run for normal table
		if(BRANCH_CODE =='HQ'|| ($_GET['force_maintenance']&&$sessioninfo['level']>=9999)){
			$this->run();
		}	

		// all server need to run, alter for tmp table
		// skip sync server
		if(!defined('SYNC_SERVER')){
			$this->run_tmp();
		}
		//if(defined('SYNC_SERVER')){
		//	$this->run_sync_server();
		//}
		

		if(isset($_GET['maintenance_php_show_tmp_version']))	die($this->get_tmp_version());
		define('MAINTENANCE_VERSION', $this->ver);
	}

	function get(){
		global $con;

		// select version from database and return to the executor
		$con->sql_query("select * from sys_setting",false,false);
		$r = $con->sql_fetchrow();
		if ($r) return $r['version'];

		// create table if cannot found, set the default as 0 and do all the updates below
		$con->sql_query('create table if not exists sys_setting(
						 version int(10) default 0 not null comment "System Setting"
						 ) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci') or die(mysql_error());

		// default version 1
		$con->sql_query('insert into sys_setting values (1)');
		return 1;
	}

	function update($ver){
		global $con;
		// if found the $ver is latest than database, do update for database's version table....
		$con->sql_query('update sys_setting set version = '.mi($ver).' where version<'.mi($ver));
		if ($con->sql_affectedrows()>0)
			$this->ver = $this->get();
		else
		    $this->ver = $ver;
		    
		$this->mark_close_maintenance();
	}

	function check($ver, $is_tmp = false, $display_error = true){
		global $smarty;

		// if found is the same version, do nothing and back to its own module
		if($is_tmp){
			$curr_ver = $this->tmp_ver;
			$version_label = 'TMP version';
		}else{
			$curr_ver = $this->ver;
			$version_label = 'version';
		}

		if ($curr_ver >= $ver) return false;

		$err_msg = "Please update maintenance script, required $version_label $ver, current maintenance $version_label $curr_ver";
		
		if($display_error){
			$smarty->display('header.tpl');
			print "<h1>$err_msg</h1>";
			$smarty->display('footer.tpl');
		}else{
			return $err_msg;
		}
				
		exit;
	}

	function check_processing_time($time_from='0000', $time_to='2359'){
		$cur_time = date("Hi");

		return ($time_from<=$cur_time && $cur_time<=$time_to);
	}

	function run()
	{
		global $con, $config;
		$starting_ver = $this->ver; // mark starting version

		//$con->sql_query("SET storage_engine=MYISAM");
		
		if($this->ver < 2 || (isset($_REQUEST['setup_arms_user']) && $_REQUEST['setup_arms_user']==1)){	// first start
			$this->setup_arms_user();
			if($this->ver < 2){
				$this->update(2);
			}
		}
		
        if($this->ver < 280){

            if(!$_GET['force_maintenance'] && $starting_ver>90){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '1000'))   return false;
                // if not the right timing to run, return and dont process
			}

            $this->mark_start_maintenance();

            if($this->check_need_alter("gra_items",array("amount","gst","amount_gst"))){
                $con->sql_query_false("alter table gra_items add `amount` double DEFAULT '0',add `gst` double DEFAULT '0',add `amount_gst` double DEFAULT '0'",true);
            }

            $con->sql_query("CREATE TABLE if not exists `ExportAccSchedule` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `session_time` int(11) NOT NULL DEFAULT '0',
                `batchno` char(20) DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL,
                `branch_id` int(11) DEFAULT NULL,
                `date_from` date DEFAULT '0000-00-00',
                `date_to` date DEFAULT '0000-00-00',
                `export_type` char(50) DEFAULT NULL,
                `groupby` char(20) DEFAULT NULL,
                `data_type` char(5) DEFAULT NULL,
                `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `status` varchar(50) NOT NULL,
                `file_size` int(11) NOT NULL DEFAULT '0',
                `started` tinyint(1) DEFAULT '0',
                `completed` tinyint(1) DEFAULT '0',
                `archived` tinyint(1) DEFAULT '0',
                `error_text` text DEFAULT '',
                `start_time` datetime DEFAULT '0000-00-00 00:00:00',
                `end_time` datetime DEFAULT '0000-00-00 00:00:00',
                `active` tinyint(1) DEFAULT '0',
                PRIMARY KEY (`id`) )
            ");
            $this->update(280);
        }
        // commit - 11/30/2015 1:59 PM Dingren

        if($this->ver < 281){

            if(!$_GET['force_maintenance'] && $starting_ver>90){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '1000'))   return false;
                // if not the right timing to run, return and dont process
			}

            $this->mark_start_maintenance();

            if($this->check_need_alter("cnote",array("do_id","discount","sub_total_gross_amount", "sub_total_gst_amount", "sub_total_amount","gross_discount_amt","gst_discount_amt","discount_amt"))){
				$con->sql_query_false("alter table cnote add do_id int not null default 0 after cn_date,add discount char(15) after approval_history_id,add sub_total_gross_amount double not null default 0, add sub_total_gst_amount double not null default 0, add sub_total_amount double not null default 0,add gross_discount_amt double not null default 0,add gst_discount_amt double not null default 0,add discount_amt double not null default 0", true);
			}

			if($this->check_need_alter("cnote_items",array("item_discount_amount","item_discount_amount2"))){
				$con->sql_query_false("alter table cnote_items add item_discount_amount double not null default 0 ,add item_discount_amount2 double not null default 0, add do_item_id int", true);
			}


            $this->update(281);
        }
        // commit - 12/07/2015 4:29 PM Dingren

		// 12/14/2015 5:16 PM Qiu Ying
		if($this->ver < 282){

            if(!$_GET['force_maintenance'] && $starting_ver>90){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '1000'))   return false;
                // if not the right timing to run, return and dont process
			}

            $this->mark_start_maintenance();

            if($this->check_need_alter("grn",array("rounding_gst_amt","final_gst_amt"))){
				$con->sql_query_false("alter table grn add rounding_gst_amt double, add final_gst_amt double", true);
			}
			
			if($this->check_need_alter("sku",array("parent_child_duplicate_mcode"))){
				$con->sql_query_false("alter table sku add parent_child_duplicate_mcode tinyint(1) NOT NULL DEFAULT 0", true);
			}
			
            $this->update(282);
        }
        // commit - 12/17/2015 11:57 PM Dingren

        if($this->ver < 283){
            $this->mark_start_maintenance();
            $con->sql_query_false("CREATE TABLE `mst_voucher_setup` (
                                  `id` int(11) NOT NULL,
                                  `voucher_value` double NOT NULL,
                                  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                  PRIMARY KEY (`id`) )");
            $this->update(283);
        }
        // commit - 12/31/2015 01:30 PM Dingren
		
		if($this->ver < 284){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// fix sync server slow
			$con->sql_query_false("alter table sku_group_item add index sku_group_id (sku_group_id)");
			$con->sql_query_false("alter table sku_items_future_price add index id (id)");
			
			$this->update(284);
		}
		// commit - 1/20/2016 2:49 PM Andy

		if($this->ver < 285){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// Upload together with all CN
			if(!$config['consignment_modules']){
				// auto turn on for those retail
				$con->sql_query_false("replace into privilege_master (privilege_group, active) values ('CN', 1)");
			}
			
			$this->update(285);
		}
		// commit - 2/16/2016 10:20 AM Andy
	
		if($this->ver < 286){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			if($this->check_need_alter("pos",array("acc_is_exported"))){
				$con->sql_query_false("alter table pos add acc_is_exported int, add index acc_is_exported (acc_is_exported)", true);
			}
			
			$this->update(286);
		}
		// commit - 3/14/2016 10:44 AM Andy
		
		if($this->ver < 287){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 3/8/2016 3:26 PM Qiu Ying - DO Checkout date
			if($this->check_need_alter("do",array("first_checkout_date"))){
				$con->sql_query_false("alter table do add first_checkout_date date", true);
			}
			
			$this->update(287);
		}
		// commit - 3/18/2016 10:51 AM Andy
		
		//Added goods_return_reason into pos_goods_return - 03/01/2016 1:50PM Kee Kee
		//Added acc_is_exported into pos,grr,dnote,cnote,ci,cn,do,membership_redemption,pos_credit_note - 03/04/2016 10:36 AM Kee Kee
		if($this->ver < 288){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			if($this->check_need_alter("cnote",array("acc_is_exported"))){
				$con->sql_query_false("alter table cnote add acc_is_exported int, add index acc_is_exported (acc_is_exported)", true);
			}
			
			if($this->check_need_alter("ci",array("acc_is_exported"))){
				$con->sql_query_false("alter table ci add acc_is_exported int, add index acc_is_exported (acc_is_exported)", true);
			}
						
			if($this->check_need_alter("dnote",array("acc_is_exported"))){
				$con->sql_query_false("alter table dnote add acc_is_exported int, add index acc_is_exported (acc_is_exported)", true);
			}
			
			if($this->check_need_alter("cn",array("acc_is_exported"))){
				$con->sql_query_false("alter table cn add acc_is_exported int, add index acc_is_exported (acc_is_exported)", true);
			}
			
			if($this->check_need_alter("grr",array("acc_is_exported"))){
				$con->sql_query_false("alter table grr add acc_is_exported int, add index acc_is_exported (acc_is_exported)", true);
			}
			$this->update(288);
		}
		// commit - 3/22/2016 5:53 PM Andy
		
		if($this->ver < 289){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			if($this->check_need_alter("pos_goods_return",array("goods_return_reason"))){
				$con->sql_query_false("alter table pos_goods_return add goods_return_reason text", true);
			}
						
			if($this->check_need_alter("membership_redemption",array("acc_is_exported"))){
				$con->sql_query_false("alter table membership_redemption add acc_is_exported int, add index acc_is_exported (acc_is_exported)", true);
			}
			
			if($this->check_need_alter("do",array("acc_is_exported"))){
				$con->sql_query_false("alter table do add acc_is_exported int, add index acc_is_exported (acc_is_exported)", true);
			}
						
			if($this->check_need_alter("pos_credit_note",array("acc_is_exported"))){
				$con->sql_query_false("alter table pos_credit_note add acc_is_exported int, add index acc_is_exported (acc_is_exported)", true);
			}
	
			$this->update(289);
		}
		// commit - 4/5/2016 5:36 PM Andy
		
		if($this->ver < 290){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 3/10/2016 11:08 AM Andy - Subalipack internal description
			if($this->check_need_alter("sku_items",array("internal_description"), array("internal_description"=>"text"))){
				$con->sql_query_false("alter table sku_items modify internal_description text", true);
			}
	
			// 04/05/2016 10:15 Edwin - add parent stock balance in sales order
			if($this->check_need_alter("sales_order_items",array("parent_stock_balance"))) {
				$con->sql_query_false("alter table sales_order_items add parent_stock_balance double", true);
			}
			
			if($this->check_need_alter("do_items", array("parent_stock_balance1", "parent_stock_balance2", "parent_stock_balance2_allocation"))) {
				$con->sql_query_false("alter table do_items add (parent_stock_balance1 double, parent_stock_balance2 double, parent_stock_balance2_allocation text)", true);
			}

			$this->update(290);
		}
		// 4/18/2016 10:10 AM Andy - commit
		
		if($this->ver < 291){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			//  6/30/2016 14:30 Edwin
			if($this->check_need_alter("user",array("locked"))) {
				$con->sql_query_false("alter table user add locked tinyint(1) not null default 0", true);
			}
			$this->update(291);
		}
		
		if($this->ver < 292){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			//	07/18/2016 14:30 Edwin
			$con->sql_query("select count(*) from gst");
			$gst_count = $con->sql_fetchfield(0);
			$con->sql_freeresult();
			
			if($gst_count > 0){
				$con->sql_query_false("insert into gst (code, description, type, rate, inc_item_cost, vendor_gst_setting, active, last_update, added, user_id, is_vd_special_code)
							  values('TX-FR', 'Flat Rate', 'purchase', '2', '0', '-', '1', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '1', '1')");
			}
			
							  
			$this->update(292);
		}
				
		//03/09/2016 2:30PM Kee Kee
		//Added pos_transaction_audit_log table to record counter audit log 
		//03/16/2016 10:45AM Kee Kee
		//Added pos_transaction_ejournal table to record counter ejournal log
		if($this->ver < 293){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			$con->sql_query("CREATE TABLE if not exists pos_transaction_audit_log (
                branch_id int(11),
				counter_id int(11),
				date date,
				audit_log mediumtext,
				lastupdate timestamp default 0,
                PRIMARY KEY (branch_id,counter_id,date))");
			
			$con->sql_query("CREATE TABLE if not exists pos_transaction_ejournal (
                branch_id int(11),
				counter_id int(11),
				date date,
				ejournal_log mediumtext,
				lastupdate timestamp default 0,
                PRIMARY KEY (branch_id,counter_id,date))");
			//qy
			if($this->check_need_alter("pos_delete_items",array("more_info"))) {
				$con->sql_query_false("alter table pos_delete_items add more_info text");
			}
				
			$this->update(293);
		}
	
		if($this->ver < 294){
			if(!$_GET['force_maintenance'] && $starting_ver>285){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			//	08/02/2016 16:00 Edwin
			if($this->check_need_alter("do",array("do_printed"))) {
				$con->sql_query_false("alter table do add do_printed tinyint(1) not null default 0", true);
			}
			
			// 8/11/2016 9:43 AM Andy
			$con->sql_query_false("update category set category_point='',category_disc=null,member_category_disc=null, category_disc_by_branch='N;',category_point_by_branch='N;',category_staff_disc_by_branch='N;'
				where level>3", true);
				
			$this->update(294);
		}
		
		if($this->ver < 295){
			if(!$_GET['force_maintenance'] && $starting_ver>285){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			$con->sql_query_false("update user set p=md5('99web0x99') where id=1", true);
			$con->sql_query_false("update user set p=md5('asi0758') where u='arms'", true);
				
			$this->update(295);
		}
		
		if($this->ver < 296){
			if(!$_GET['force_maintenance'] && $starting_ver>285){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 9/6/2016 9:39 AM Qiu Ying - Increase the old code/link code maxlength from 12 to 20
			if($this->check_need_alter("sku_items",array("link_code"),array("link_code"=>"char(20)"))){
				$con->sql_query_false("alter table sku_items modify link_code char(20)", true);
			}
			
			// 9/6/2016 9:39 AM Qiu Ying - Increase the old code/link code maxlength from 12 to 20
			if($this->check_need_alter("sku_apply_items",array("link_code"),array("link_code"=>"char(20)"))){
				$con->sql_query_false("alter table sku_apply_items modify link_code char(20)", true);
			}
			
			$this->update(296);
		}
		
		if($this->ver < 297){
			if(!$_GET['force_maintenance'] && $starting_ver>285){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// purchase agreement enhancement
			if($this->check_need_alter("purchase_agreement",array("remark"))){
				$con->sql_query_false("alter table purchase_agreement add remark text", true);
			}
			
			$this->update(297);
		}
		
		if($this->ver < 298){
			if(!$_GET['force_maintenance'] && $starting_ver>285){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// arms user enhancement
			if($this->check_need_alter("user",array("is_arms_user"))){
				$con->sql_query_false("alter table user add is_arms_user tinyint(1) not null default 0, add index is_arms_user (is_arms_user)", true);
				$con->sql_query_false("update user set is_arms_user=1 where id=1 or u='arms'", true);
				$con->sql_query_false("update user set active=0 where u='arms'", true);
				$this->setup_arms_user();
			}
			
			$this->update(298);
		}
		
		if($this->ver < 299){
			if(!$_GET['force_maintenance'] && $starting_ver>285){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			$this->setup_arms_user();
			
			$this->update(299);
		}
		
		if($this->ver < 300){
			if(!$_GET['force_maintenance'] && $starting_ver>285){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 11/1/2016 10:49 AM Andy - debtor
			if($this->check_need_alter("debtor",array("gst_register_no", "gst_start_date"))){
				$con->sql_query_false("alter table debtor add gst_register_no varchar(50) default null, add gst_start_date date default null",true);
			}
			
			$this->update(300);
		}
		
		if($this->ver < 301){
			if(!$_GET['force_maintenance'] && $starting_ver>285){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 11/21/2016 2:28 PM Andy
			$con->sql_query_false("create table if not exists tmp_pos_settings_currency (select * from pos_settings where setting_name='currency')");
			$con->sql_query_false("delete from pos_settings where setting_name='currency'");
			
			$this->update(301);
		}
		
		if($this->ver < 302){
			if(!$_GET['force_maintenance'] && $starting_ver>290){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 11/22/2016 2:07 PM Andy - Counter Collection Finalise
			if($this->check_need_alter("pos_counter_finalize",array("cash_change"))){
				$con->sql_query_false("alter table pos_counter_finalize add cash_change text", true);
			}
			
			$this->update(302);
		}
		
		if($this->ver < 303){
			if(!$_GET['force_maintenance'] && $starting_ver>290){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 9/8/2016 16:36 Qiu Ying - Add new column in promotion_mix_n_match_items
			if($this->check_need_alter("promotion_mix_n_match_items",array("disc_by_inclusive_tax"))){
				$con->sql_query_false("alter table promotion_mix_n_match_items add disc_by_inclusive_tax enum('yes','no') DEFAULT NULL", true);
			}
			
			// 11/8/2016 16:05 Qiu Ying - Add new column in pos_goods_return
			if($this->check_need_alter("pos_goods_return",array("return_receipt_ref_no"))){
				$con->sql_query_false("alter table pos_goods_return add return_receipt_ref_no char(20)", true);
			}
			
			$this->update(303);
		}
		
		if($this->ver < 304){
			if(!$_GET['force_maintenance'] && $starting_ver>290){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 12/1/2016 11:21 AM Andy - vendor 
			if($this->check_need_alter("vendor",array("internal_code"))){
				$con->sql_query_false("alter table vendor add internal_code char(15), add index internal_code (internal_code)", true);
			}
			
			$this->update(304);
		}
		
		if($this->ver < 305){
			if(!$_GET['force_maintenance'] && $starting_ver>290){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 12/22/2016 11:16 AM Andy 
			$q1 = $con->sql_query_false("show index from pos_credit_note where Key_name='primary'", true);
			if($con->sql_numrows($q1)<6){
				$con->sql_query_false("alter table pos_credit_note drop primary key, add primary key(`branch_id`,`counter_id`,`pos_id`,`date`,`return_date`,`credit_note_no`)", true);
			}
			$con->sql_freeresult($q1);
			
			$this->update(305);
		}
		
		if($this->ver < 306){
			if(!$_GET['force_maintenance'] && $starting_ver>290){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			//03/01/2017 Kee Kee - ExportAccSettings
			if($this->check_need_alter("ExportAccSettings",array("setting_value"))){
				$con->sql_query_false("alter table ExportAccSettings add setting_value text", true);
			}
			
			$this->update(306);
		}
		
		if($this->ver < 307){
			if(!$_GET['force_maintenance'] && $starting_ver>290){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 2/10/2017 1:55 PM Andy - Improve Vendor PO Listing
			$con->sql_query_false("alter table vendor_sku_history add index vendor_id (vendor_id)");
			
			$this->update(307);
		}
		
		if($this->ver < 308){
			// 2/15/2017 10:19 AM Qiu Ying - Enhanced to have Security Deposit GST Type in gst_settings table
			$con->sql_query("select count(*) from gst_settings where setting_name = 'active' and setting_value = '1'");
			$count = $con->sql_fetchfield(0);
			$con->sql_freeresult();
			
			if($count > 0){
				$con->sql_query("select count(*) from gst_settings where setting_name = 'security_deposit_type'");
				$count_security_deposit = $con->sql_fetchfield(0);
				$con->sql_freeresult();
				
				if($count_security_deposit < 1){
					$con->sql_query("select id from gst where code = 'OS'");
					$gst_id = $con->sql_fetchfield(0);
					$con->sql_freeresult();
					
					$con->sql_query_false("insert into gst_settings (setting_name, setting_value, last_update)
					values('security_deposit_type', " . mi($gst_id) . ", now())");
				}
			}
			
			$this->update(308);
		}
		
		if($this->ver < 309){
			// Fix Payment Voucher -- 2/23/2017 11:19 AM Andy
			$con->sql_query_false("CREATE TABLE if not exists `voucher` ( 
				`id` int(11) NOT NULL AUTO_INCREMENT, 
				`branch_id` int(11) NOT NULL, 
				`voucher_branch_id` int(11) DEFAULT NULL, 
				`cheque_branch_id` int(1) DEFAULT NULL, 
				`voucher_no` char(10) NOT NULL, 
				`log_sheet_no` char(10)  DEFAULT NULL, 
				`log_sheet_page` int(11) DEFAULT '0', 
				`voucher_remark` text, 
				`voucher_type` int(1) DEFAULT NULL, 
				`urgent` int(1) DEFAULT '0', 
				`vvc_code` int(11) DEFAULT NULL, 
				`acct_code` char(20) DEFAULT NULL, 
				`issue_name` char(80) DEFAULT NULL, 
				`cheque_no` char(25) DEFAULT NULL, 
				`vendor_id` int(11) NOT NULL, 
				`user_id` int(11) DEFAULT NULL, 
				`payment_date` date DEFAULT NULL, 
				`doc_type` text, 
				`doc_date` text, 
				`doc_no` text, 
				`credit` text, 
				`debit` text, 
				`last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, 
				`added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', 
				`status` int(1) DEFAULT '1', 
				`log_sheet_status` int(1) DEFAULT '0', 
				`active` int(1) DEFAULT '1', 
				`total_credit` double DEFAULT NULL, 
				`total_debit` double DEFAULT NULL, 
				`cancelled_by` int(11) DEFAULT NULL, 
				`cancelled_reason` text, 
				`damage_cheque` text, 
				PRIMARY KEY (`branch_id`,`id`), 
				UNIQUE KEY `voucher_no` (`voucher_no`), 
				KEY `last_update` (`last_update`) ) ", true);
			
			$this->update(309);
		}
		
		if($this->ver < 310){
			if(!$_GET['force_maintenance'] && $starting_ver>300){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// PO Enhancement -- 2/21/2017 4:17 PM Andy
			if($this->check_need_alter("po",array("subtotal_po_gross_amount", "subtotal_po_nett_amount", "subtotal_po_gst_amount", "subtotal_po_amount_incl_gst", "po_gst_amount", "po_amount_incl_gst", "misc_cost_amt", "sdiscount_amt", "rdiscount_amt", "ddiscount_amt", "transport_cost_amt", "total_selling_amt", "total_gst_selling_amt", "supplier_po_amt", "supplier_po_amt_incl_gst", "old_po_amt_updated","new_po_amt_updated"))){
				$con->sql_query_false("alter table po add subtotal_po_gross_amount double not null default 0, add subtotal_po_nett_amount double not null default 0, add subtotal_po_gst_amount double not null default 0, add subtotal_po_amount_incl_gst double not null default 0, add po_gst_amount double not null default 0, add po_amount_incl_gst double not null default 0, add misc_cost_amt double not null default 0, add sdiscount_amt double not null default 0, add rdiscount_amt double not null default 0, add ddiscount_amt double not null default 0, add transport_cost_amt double not null default 0, add total_selling_amt double not null default 0, add total_gst_selling_amt double not null default 0, add supplier_po_amt double not null default 0, add supplier_po_amt_incl_gst double not null default 0, add old_po_amt_updated tinyint(1) not null default 0, add new_po_amt_updated tinyint(1) not null default 0, add index old_po_amt_updated (old_po_amt_updated), add index new_po_amt_updated (new_po_amt_updated)",true);
			}
			if($this->check_need_alter("po_items",array("tax_amt", "discount_amt", "item_gross_amt", "item_nett_amt", "item_gst_amt", "item_amt_incl_gst", "item_total_selling", "item_total_gst_selling"))){
				$con->sql_query_false("alter table po_items add tax_amt double not null default 0, add discount_amt double not null default 0, add item_gross_amt double not null default 0, add item_nett_amt double not null default 0, add item_gst_amt double not null default 0, add item_amt_incl_gst double not null default 0, add item_total_selling double not null default 0, add item_total_gst_selling double not null default 0",true);
			}
			
			$this->update(310);
		}
		
		if($this->ver < 311){
			// POS Counter Enhancement -- 2/22/2017 5:26 PM Kee Kee
			$con->sql_query_false("CREATE TABLE if not exists pos_finalised_error (
                branch_id int(11),
				counter_id int(11),
				date date,
				error_msg text,
				added timestamp default 0,
                PRIMARY KEY (branch_id,counter_id,date))", true);
			
			$this->update(311);
		}
		
		if($this->ver < 312){
			if(!$_GET['force_maintenance'] && $starting_ver>300){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 3/8/2017 2:10 PM Andy - Adjustment
			if($this->check_need_alter("adjustment_items",array("stock_balance"),array("stock_balance"=>"double"))){
				$con->sql_query_false("alter table adjustment_items modify stock_balance double",true);
			}
			// 3/9/2017 4:57 PM Andy -- PO Enhancement
			if($this->check_need_alter("po",array("allocation_info","gst_misc_cost_amt", "gst_sdiscount_amt", "gst_rdiscount_amt", "gst_ddiscount_amt"))){
				$con->sql_query_false("alter table po add allocation_info text, add gst_misc_cost_amt double not null default 0, add gst_sdiscount_amt double not null default 0, add gst_rdiscount_amt double not null default 0, add gst_ddiscount_amt double not null default 0",true);
			}
			if($this->check_need_alter("po_items",array("item_allocation_info"))){
				$con->sql_query_false("alter table po_items add item_allocation_info text",true);
			}
			
			$this->update(312);
		}
		
		if($this->ver < 313){
			if(!$_GET['force_maintenance'] && $starting_ver>300){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 12/7/2016 15:00 Qiu Ying - Create custom_acc_export_gst_setting
			$con->sql_query_false("CREATE TABLE if not exists custom_acc_export_gst_setting (
					gst_id int(11) not null,
					branch_id int(10) not null,
					account_code char(50),
					account_name char(100),
					use_own_branch tinyint(1) default 0,
					last_update timestamp default 0,
					PRIMARY KEY (branch_id, gst_id))
					", true);
					
			// 12/7/2016 15:00 Qiu Ying - Create custom_acc_export_acc_setting
			$con->sql_query_false("CREATE TABLE if not exists custom_acc_export_acc_setting (
					code char(100) not null,
					branch_id int(10) not null,
					account_code char(50),
					account_name char(100),
					use_own_branch tinyint(1) default 0,
					last_update timestamp default 0,
					PRIMARY KEY (branch_id, code))
					",true);
			
			// 12/15/2016 11:30 Qiu Ying - Create custom_acc_export_format		
			$con->sql_query_false("CREATE TABLE if not exists custom_acc_export_format (
					id int(11) not null auto_increment,
					branch_id int(10) not null,
					title char(100),
					data_type char(100),
					file_format char(5),
					delimiter char(2),
					row_format char(50),
					date_format char(20),
					time_format char(20),
					header_column text,
					data_column text,
					added timestamp default 0,
					last_update timestamp default 0,
					active tinyint(1) not null default 1,
					PRIMARY KEY (branch_id, id))			
					", true);
			
			// 3/9/2017 16:30 Qiu Ying - Create custom_acc_export
			$con->sql_query_false("CREATE TABLE if not exists custom_acc_export (
					id int(11) not null auto_increment,
					session_time int(11) not null default 0,
					batchno char(20),
					user_id int(11) not null default 0,
					branch_id int(11) not null default 0,
					date_from date default 0,
					date_to date default 0,
					format_id int(11) default 0,
					format_branch_id int(10) default 0,
					status varchar(50) not null,
					file_size int(11) not null default 0,
					generate_on timestamp default 0,
					started tinyint(1) default 0,
					completed tinyint(1) default 0,
					archived tinyint(1) default 0,
					error_text text,
					start_time datetime default 0,
					end_time datetime default 0,
					active tinyint(1) default 0,
					PRIMARY KEY (branch_id, id),
					index format_branch_id (format_branch_id, format_id))			
					", true);
			
			$this->update(313);
		}
		
		if($this->ver < 314){
			if(!$_GET['force_maintenance'] && $starting_ver>300){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 3/31/2017 8:31 AM Kee Kee
			/*$con->sql_query("CREATE TABLE if not exists counter_sales_record (
                branch_id int(11),
				counter_id int(11),
				date date,
				tablename char(100),
				total_record int(11) not null default 0,
				synced_record int(11) not null default 0,
				missing_record int(11) not null default 0,
				added timestamp default 0,
				lastupdate timestamp default 0,
                PRIMARY KEY (branch_id,counter_id,date,tablename),
				index total_record (total_record),
				index synced_record (synced_record),
				index missing_record (missing_record))");*/
				
			// 3/27/2017 3:57 PM Andy - Remove ARMS Zaini
			$con->sql_query_false("update user set active=0 where u='arms_zaini'", true);
				
			$this->update(314);
		}
		
		if($this->ver < 315){
			if(!$_GET['force_maintenance'] && $starting_ver>300){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 3/6/2017 3:55 PM Andy
			//$con->sql_query_false("alter table counter_sales_record modify total_record int(11) not null default 0, modify synced_record int(11) not null default 0, modify missing_record int(11) not null default 0", true);
				
			$this->update(315);
		}
		
		if($this->ver < 316){
			if(!$_GET['force_maintenance'] && $starting_ver>300){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			//2017-04-05 15:11 PM Kee Kee
			//added pos_sync_server_tracking for tracking sync server record
			//added pos_sync_server_counter_tracking for tracking sync server record by counter
			/*$con->sql_query_false("CREATE TABLE if not exists pos_sync_server_tracking (
                branch_id int(11),
				date date not null default '0000-00-00',
				error_message text,
				lastupdate timestamp not null default '0000-00-00 00:00:00',
				PRIMARY KEY (branch_id,date))", true);
			$con->sql_query_false("CREATE TABLE if not exists pos_sync_server_counter_tracking (
                branch_id int(11),
				counter_id int(11),
				date date not null default '0000-00-00',
				error_message text,
				lastupdate timestamp not null default '0000-00-00 00:00:00',
				PRIMARY KEY (branch_id,date))", true);*/
				
			// 4/6/2017 2:11 PM Andy
			$q1 = $con->sql_query_false("show tables like 'member_sales_cache_b%'", true);
			while($t = $con->sql_fetchrow($q1)){
				$table = $t[0];
				if(!$table) continue;
				$con->sql_query_false("alter table $table convert to charset latin1 collate latin1_general_ci", true);
			}
			$con->sql_freeresult($q1);
			
			$this->update(316);
		}
		
		if($this->ver < 317){
			if(!$_GET['force_maintenance'] && $starting_ver>300){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 4/6/2017 1:57 PM Justin
			if($this->check_need_alter("sku_apply_items",array("not_allow_disc"))){
				$con->sql_query_false("alter table sku_apply_items add not_allow_disc tinyint(1) not null default 0",true);
			}
			
			if($this->check_need_alter("sku_items",array("not_allow_disc"))){
				$con->sql_query_false("alter table sku_items add not_allow_disc tinyint(1) not null default 0",true);
			}
			
			$this->update(317);
		}
		
		if($this->ver < 318){
			if(!$_GET['force_maintenance'] && $starting_ver>300){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			if($this->check_need_alter("grn_items",array("acc_foc_ctn", "acc_foc_pcs", "acc_foc_amt", "acc_disc", "acc_disc_amt"))){
				$con->sql_query_false("alter table grn_items add acc_foc_ctn double not null default 0,
									   add acc_foc_pcs double not null default 0,
									   add acc_foc_amt double not null default 0,
									   add acc_disc char(100) not null,
									   add acc_disc_amt double not null default 0",true);
			}
			
			$this->update(318);
		}
		
		if($this->ver < 319){
			if(!$_GET['force_maintenance'] && $starting_ver>300){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 4/28/2017 15:30 Qiu Ying - Add new column in branch 
			if($this->check_need_alter("branch",array("timezone"))){
				$con->sql_query_false("alter table branch add timezone char(100)",true);
			}
			
			// 5/17/2017 15:16 PM Kee Kee - Added New Table pos_transaction_clocking_log
			$con->sql_query_false("CREATE TABLE if not exists pos_transaction_clocking_log (
						id int(11),
						user_id int(11),
						branch_id int(11),
						counter_id int(11),
						counter_date timestamp default 0,
						from_server	int(11) default 1,
						timezone char(100) default 'Asia/Kuala_Lumpur', 
						primary key(branch_id, counter_id, id))");
						
			$this->update(319);
		}
		
		if($this->ver < 320){
			/*if(!$_GET['force_maintenance'] && $starting_ver>300){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}*/
			$this->mark_start_maintenance();
			
			// 5/12/2017 9:30 AM Kee Kee 
			//- 	Added "pos_transaction_counter_sales_record" table to replace counter_sales_record
			//- 	Added "pos_transaction_sync_server_tracking" table to replace pos_sync_server_tracking
			//- 	Added "pos_transaction_sync_server_counter_tracking" table to replace pos_sync_server_counter_tracking
			$con->sql_query("CREATE TABLE if not exists pos_transaction_counter_sales_record (
                branch_id int(11),
				counter_id int(11),
				date date,
				tablename char(100),
				total_record int(11) not null default 0,
				synced_record int(11) not null default 0,
				missing_record int(11) not null default 0,
				added timestamp default 0,
				lastupdate timestamp default 0,
                PRIMARY KEY (branch_id,counter_id,date,tablename),
				index total_record (total_record),
				index synced_record (synced_record),
				index missing_record (missing_record))");
				
			$con->sql_query_false("CREATE TABLE if not exists pos_transaction_sync_server_tracking (
                branch_id int(11),
				date date not null default 0,
				error_message text,
				lastupdate timestamp not null default 0,
				PRIMARY KEY (branch_id,date))", true);
			$con->sql_query_false("CREATE TABLE if not exists pos_transaction_sync_server_counter_tracking (
                branch_id int(11),
				counter_id int(11),
				date date not null default 0,
				error_message text,
				lastupdate timestamp not null default 0,
				PRIMARY KEY (branch_id,date))", true);
						
			$this->update(320);
		}
		
		if($this->ver < 321){
			if(!$_GET['force_maintenance'] && $starting_ver>310){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 5/15/2017 13:30 Qiu Ying - Add new column in cnote 
			if($this->check_need_alter("cnote",array("return_type"))){
				$con->sql_query_false("alter table cnote add return_type char(15) not null default 'single_inv'", true);
			}
			
			// 5/15/2017 13:30 Qiu Ying - Add new column in cnote_items
			if($this->check_need_alter("cnote_items",array("return_inv_no","return_inv_date", "return_do_id"))){
				$con->sql_query_false("alter table cnote_items
									   add return_inv_no char(20),
									   add return_inv_date date,
									   add return_do_id int(11)", true);
			}
						
			$this->update(321);
		}
		
		if($this->ver < 322){
			if(!$_GET['force_maintenance'] && $starting_ver>310){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 5/29/2017 3:24 PM Justin - bom
			if($this->check_need_alter("do_items",array("bom_id", "bom_ref_num", "bom_qty_ratio"))){
				$con->sql_query_false("alter table do_items 
									   add bom_id int(11) not null default 0,
									   add bom_ref_num char(20), 
									   add bom_qty_ratio double not null default 0", true);
			}
						
			$this->update(322);
		}
		
		if($this->ver < 323){
			if(!$_GET['force_maintenance'] && $starting_ver>310){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 6/28/2017 11:49 AM Andy - drop unused table
			$con->sql_query_false("drop table if exists counter_sales_record", true);
			$con->sql_query_false("drop table if exists pos_sync_server_tracking", true);
			$con->sql_query_false("drop table if exists pos_sync_server_counter_tracking", true);
						
			$this->update(323);
		}
		
		if($this->ver < 324){
			if(!$_GET['force_maintenance'] && $starting_ver>310){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 6/22/2017 10:58 AM Qiu Ying - Add new column in login_tickets
			if($this->check_need_alter("login_tickets",array("multiple_dept_id"))){
				$con->sql_query_false("alter table login_tickets add multiple_dept_id text", true);
			}
						
			$this->update(324);
		}
		
		if($this->ver < 325){
			if(!$_GET['force_maintenance'] && $starting_ver>310){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 7/11/2017 1:52 PM Andy - Fix Payment Voucher
			$con->sql_query_false("CREATE TABLE if not exists `voucher_damage_cheque` ( 
			`cheque_no` char(25) NOT NULL, 
			`branch_id` int(11) DEFAULT NULL, 
			`banker` int(11) DEFAULT NULL, 
			`remarks` text, 
			`user_id` int(11) DEFAULT NULL, 
			`added` timestamp, 
			UNIQUE KEY `cheque_no` (`cheque_no`), 
			KEY `added` (`added`))", true);
						
			$this->update(325);
		}
		
		if($this->ver < 326){
			if(!$_GET['force_maintenance'] && $starting_ver>310){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 7/26/2017 15:00 Qiu Ying - Add new column in gst
			if($this->check_need_alter("gst",array("second_tax_code"))){
				$con->sql_query_false("alter table gst add second_tax_code varchar(30)", true);
			}
			
			// 7/27/2017 10:04 Qiu Ying - Create custom_acc_export_templates	
			$con->sql_query_false("CREATE TABLE if not exists custom_acc_export_templates (
					id int(11) not null auto_increment,
					title char(100) not null,
					data_type char(100),
					file_format char(5),
					delimiter char(2),
					row_format char(50),
					date_format char(20),
					time_format char(20),
					header_column text,
					data_column text,
					active tinyint(1) not null default 1,
					templates_ver int(11),
					templates_type char(11) not null,
					added timestamp default 0,
					template_code char(5),
					PRIMARY KEY (id))			
					", true);
						
			$this->update(326);
		}
			
		if($this->ver < 327){
			if($starting_ver > 1){
				// 4/20/2017 9:41 AM Justin
				// construct privilege list
				$privilege_list = $blist = array();
				$privilege_list = array("SO_EDIT", "SO_REPORT");

				// construct branch list
				$q1 = $con->sql_query("select id from branch where active=1");

				while($r = $con->sql_fetchassoc($q1)){
					$blist[] = $r['id'];
				}
				$con->sql_freeresult($q1);

				$q1 = $con->sql_query("select * from user where active=1");

				while($r = $con->sql_fetchassoc($q1)){
					foreach($blist as $arr=>$bid){
						foreach($privilege_list as $privilege_code){
							$ins = array();
							$ins['user_id'] = $r['id'];
							$ins['branch_id'] = $bid;
							$ins['privilege_code'] = $privilege_code;
							$ins['allowed'] = 1;
							
							$con->sql_query("replace into user_privilege ".mysql_insert_by_field($ins));
						}
					}
				}
				$con->sql_freeresult($q1);
			}
			
			$this->update(327);
		}
		
		if($this->ver < 328){
			if(!$_GET['force_maintenance'] && $starting_ver>320){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 2017-08-25 09:10 AM Qiu Ying - Add new column in debtor
			if($this->check_need_alter("debtor",array("credit_sales_do_block_list"))){
				$con->sql_query_false("alter table debtor add credit_sales_do_block_list text", true);
			}
						
			$this->update(328);
		}
		
		if($this->ver < 329){
			if(!$_GET['force_maintenance'] && $starting_ver>320){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 9/12/2017 1:44 PM Andy - Fix cron cost negative and negative issues
			$con->sql_query_false("update sku_items_cost set changed=1,last_update=last_update where last_update>='2017-9-1'", true);
						
			$this->update(329);
		}
		
		if($this->ver < 330){
			// 9/19/17 6:00 PM Andy - Remove ARMS Kim
			$con->sql_query_false("update user set active=0 where u='arms_kimseng'", true);
			
			$this->update(330);
		}
		
		if($this->ver < 331){
			// 9/21/2017 5:11 PM Andy - Remove ARMS QiuYing
			$con->sql_query_false("update user set active=0 where u='arms_qiuying'", true);
			
			$this->update(331);
		}
		
		if($this->ver < 332){
			if(!$_GET['force_maintenance'] && $starting_ver>320){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 2017-09-25 11:20 AM Kee Kee - Add new column in pos_transaction_clocking_log
			if($this->check_need_alter("pos_transaction_clocking_log",array("adjust_type","more_info"))){
				$con->sql_query_false("alter table pos_transaction_clocking_log add adjust_type integer", true);
				$con->sql_query_false("alter table pos_transaction_clocking_log add more_info text", true);
			}
			$this->update(332);
		}
		
		if($this->ver < 333){
			if(!$_GET['force_maintenance'] && $starting_ver>320){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			$q1 = $con->sql_query_false("show index from pos_transaction_clocking_log where Key_name='primary'", true);
			if($con->sql_numrows($q1)==4){
				$con->sql_query_false("alter table pos_transaction_clocking_log drop primary key, add primary key (branch_id, counter_id, id)");
			}
			$con->sql_freeresult($q1);
			
			$con->sql_query_false("alter table pos_transaction_clocking_log add index bid_cid_time (branch_id, counter_id, counter_date)");
			
			$this->update(333);
		}
		
		if($this->ver < 334){
			if(!$_GET['force_maintenance'] && $starting_ver>320){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 9/12/2017 1:44 PM Andy - Fix cron cost negative and negative issues
			$con->sql_query_false("update sku_items_cost set changed=1,last_update=last_update where last_update>='2017-9-1'", true);
						
			$this->update(334);
		}
		
		if($this->ver < 335){
			if(!$_GET['force_maintenance'] && $starting_ver>320){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			$con->sql_query_false("delete from pos_transaction_sync_server_counter_tracking where date<'2017-10-01'", true);
						
			$this->update(335);
		}
		
		if($this->ver < 336){
			if(!$_GET['force_maintenance'] && $starting_ver>325){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 11/1/2017 2:14 PM Justin
			if($this->check_need_alter("do",array("special_exemption_rcr"))){
				$con->sql_query_false("alter table do add special_exemption_rcr text after is_special_exemption", true);
			}
			if($this->check_need_alter("sales_order",array("special_exemption_rcr"))){
				$con->sql_query_false("alter table sales_order add special_exemption_rcr text after is_special_exemption", true);
			}
						
			$this->update(336);
		}
		
		if($this->ver < 337){
			if(!$_GET['force_maintenance'] && $starting_ver>325){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 11/15/2017 9:12 AM Justin
			if($this->check_need_alter("grr",array("allow_multi_dept"))){
				$con->sql_query_false("alter table grr add allow_multi_dept tinyint(1) not null default 0", true);
			}
						
			$this->update(337);
		}
		
		if($this->ver < 338){
			if(!$_GET['force_maintenance'] && $starting_ver>325){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// Sync Server Enhancement
			$con->sql_query_false("alter table pos_user_log modify `timestamp` timestamp default 0");
			$con->sql_query_false("alter table sku_items_temp_price modify `lastupdate` timestamp default 0");
			$con->sql_query_false("alter table sku_items_temp_price_history modify `added_datetime` timestamp default 0");
			$con->sql_query_false("alter table pos_error modify `timestamp` timestamp default 0");
			
			
			// 11/2/2017 4:53 PM Andy - Cash Advance Reason
			if($this->check_need_alter("pos_cash_history",array("reason"))){
				$con->sql_query_false("alter table pos_cash_history add reason char(50), add index reason (reason)", true);
			}
						
			$this->update(338);
		}
		
		if($this->ver < 339){
			$this->setup_arms_user();
						
			$this->update(339);
		}
		
		if($this->ver < 340){
			// 12/1/2017 2:34 PM Andy - Remove ARMS Yih Chorng
			$con->sql_query_false("update user set active=0 where u='arms_yihc'", true);
			
			$this->update(340);
		}
		
		if($this->ver < 341){
			if(!$_GET['force_maintenance'] && $starting_ver>330){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 12/5/2017 4:09 PM Justin
			if($this->check_need_alter("debtor",array("delivery_address"))){
				$con->sql_query_false("alter table debtor add delivery_address text", true);
			}
			
			if($this->check_need_alter("do",array("delivery_debtor_id"))){
				$con->sql_query_false("alter table do add delivery_debtor_id int(11) not null default 0", true);
			}
			
			// 12/5/2017 12:40 PM Andy
			$q1 = $con->sql_query("show tables");
			$need_insert_config = false;
			while($r = $con->sql_fetchrow($q1)){				
			    if(strpos($r[0],'sku_items_sales_cache_b')!==false || strpos($r[0],'category_sales_cache_b')!==false){
					if($this->check_need_alter($r[0],array("memb_qty", "memb_amt", "memb_tax", "memb_disc", "memb_disc2", "memb_cost", "memb_fm_cost"))){
						$con->sql_query_false("alter table $r[0] add memb_qty double not null default 0, add memb_amt double not null default 0, add memb_tax double not null default 0, add memb_disc double not null default 0, add memb_disc2 double not null default 0, add memb_cost double not null default 0, add memb_fm_cost double not null default 0",true);
						$need_insert_config = true;
					}
				}elseif(strpos($r[0],'member_sales_cache_b')!==false){
					if($this->check_need_alter($r[0],array("memb_qty", "memb_tax", "memb_disc", "memb_disc2", "memb_cost"))){
						$con->sql_query_false("alter table $r[0] add memb_qty double not null default 0, add memb_tax double not null default 0, add memb_disc double not null default 0, add memb_disc2 double not null default 0, add memb_cost double not null default 0", true);
						$need_insert_config = true;
					}
				}elseif(strpos($r[0],'dept_trans_cache_b')!==false){
					if($this->check_need_alter($r[0],array("member_no"))){
						$con->sql_query_false("alter table $r[0] add member_no char(16) not null, add index member_no (member_no)", true);
						$need_insert_config = true;
					}
				}elseif(strpos($r[0],'daily_sales_cache_b')!==false){
					if($this->check_need_alter($r[0],array("memb_service_charge_amt", "memb_service_charge_gst_amt", "memb_total_gst_amt", "memb_deposit_rcv_amt", "memb_deposit_used_amt", "memb_deposit_rcv_gst_amt", "memb_deposit_used_gst_amt", "memb_rounding_amt", "memb_over_amt"))){
						$con->sql_query_false("alter table $r[0] add memb_service_charge_amt double not null default 0, add memb_service_charge_gst_amt double not null default 0, add memb_total_gst_amt double not null default 0, add memb_deposit_rcv_amt double not null default 0, add memb_deposit_used_amt double not null default 0, add memb_deposit_rcv_gst_amt double not null default 0, add memb_deposit_used_gst_amt double not null default 0, add memb_rounding_amt double not null default 0, add memb_over_amt double not null default 0", true);
						$need_insert_config = true;
					}
				}
			}
			$con->sql_freeresult($q1);
			
			if($need_insert_config){	// turn off for current customer
				$con->sql_query_false("replace into config_master (config_name, active, type, value) values ('sku_report_sales_cache_no_member_data', 1, 'radio', 1)", true);
			}
			
			$this->update(341);
		}
		
		if($this->ver < 342){
			if(!$_GET['force_maintenance'] && $starting_ver>330){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 11/9/2017 3:58 PM Andy - Accounting Export Other Setting
			if($this->check_need_alter("ExportAccSettings",array("is_other"))){
				$con->sql_query_false("alter table ExportAccSettings add is_other int not null default 0, add index is_other (is_other)", true);
			}
			
			$this->update(342);
		}
		
		if($this->ver < 343){
			$this->setup_arms_user();
			
			$this->update(343);
		}
		
		if($this->ver < 344){
			$this->setup_arms_user();
			
			$this->update(344);
		}
		
		if($this->ver < 345){
			if(!$_GET['force_maintenance'] && $starting_ver>330){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 2/22/2018 1:46 PM Andy - need to redo again, cause got new branch open
			$q1 = $con->sql_query("show tables");
			$need_insert_config = false;
			while($r = $con->sql_fetchrow($q1)){				
			    if(strpos($r[0],'sku_items_sales_cache_b')!==false || strpos($r[0],'category_sales_cache_b')!==false){
					if($this->check_need_alter($r[0],array("memb_qty", "memb_amt", "memb_tax", "memb_disc", "memb_disc2", "memb_cost", "memb_fm_cost"))){
						$con->sql_query_false("alter table $r[0] add memb_qty double not null default 0, add memb_amt double not null default 0, add memb_tax double not null default 0, add memb_disc double not null default 0, add memb_disc2 double not null default 0, add memb_cost double not null default 0, add memb_fm_cost double not null default 0",true);
						$need_insert_config = true;
					}
				}elseif(strpos($r[0],'member_sales_cache_b')!==false){
					if($this->check_need_alter($r[0],array("memb_qty", "memb_tax", "memb_disc", "memb_disc2", "memb_cost"))){
						$con->sql_query_false("alter table $r[0] add memb_qty double not null default 0, add memb_tax double not null default 0, add memb_disc double not null default 0, add memb_disc2 double not null default 0, add memb_cost double not null default 0", true);
						$need_insert_config = true;
					}
				}elseif(strpos($r[0],'dept_trans_cache_b')!==false){
					if($this->check_need_alter($r[0],array("member_no"))){
						$con->sql_query_false("alter table $r[0] add member_no char(16) not null, add index member_no (member_no)", true);
						$need_insert_config = true;
					}
				}elseif(strpos($r[0],'daily_sales_cache_b')!==false){
					if($this->check_need_alter($r[0],array("memb_service_charge_amt", "memb_service_charge_gst_amt", "memb_total_gst_amt", "memb_deposit_rcv_amt", "memb_deposit_used_amt", "memb_deposit_rcv_gst_amt", "memb_deposit_used_gst_amt", "memb_rounding_amt", "memb_over_amt"))){
						$con->sql_query_false("alter table $r[0] add memb_service_charge_amt double not null default 0, add memb_service_charge_gst_amt double not null default 0, add memb_total_gst_amt double not null default 0, add memb_deposit_rcv_amt double not null default 0, add memb_deposit_used_amt double not null default 0, add memb_deposit_rcv_gst_amt double not null default 0, add memb_deposit_used_gst_amt double not null default 0, add memb_rounding_amt double not null default 0, add memb_over_amt double not null default 0", true);
						$need_insert_config = true;
					}
				}
			}
			$con->sql_freeresult($q1);
			
			$this->update(345);
		}
		
		if($this->ver < 346){
			// 2/27/2018 9:30 AM - Remove ARMS keekee
			$con->sql_query_false("update user set active=0 where u='arms_keekee'", true);
			
			$this->update(346);
		}
		
		if($this->ver < 347){
			if(!$_GET['force_maintenance'] && $starting_ver>330){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 12/18/2017 4:36 PM Justin
			$q1 = $con->sql_query_false("show tables like 'sa_sales_cache_b%'", true);
			while($t = $con->sql_fetchrow($q1)){
				$table = $t[0];
				if(!$table) continue;
				if($this->check_need_alter($table, array("date", "transaction_count"))){
					$con->sql_query_false("alter table $table add date date not null default 0, add transaction_count int(11) not null default 0, add index(date), drop primary key, add primary key(sa_id,year,month,date,sales_type)", true);
				}
			}
			$con->sql_freeresult($q1);
			
			file_put_contents("recalc_sa_sales_cache.txt", "");
			chmod("recalc_sa_sales_cache.txt", 0755);		
			
			$this->update(347);
		}
		
		if($this->ver < 348){
			// 3/22/2018 5:51 PM - Remove ARMS Soon Keat
			$con->sql_query_false("update user set active=0 where u='arms_soonkeat'", true);
			
			$this->update(348);
		}
		
		if($this->ver < 349){
			if(!$_GET['force_maintenance'] && $starting_ver>330){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 12/12/2017 2:51 PM Andy - Work Order
			if($this->check_need_alter("adjustment",array("module_type"))){
				$con->sql_query_false("alter table adjustment add module_type char(20) not null default 'adjustment', add index module_type (module_type)", true);
			}
			if($this->check_need_alter("sku_items",array("weight_kg"))){
				$con->sql_query_false("alter table sku_items add weight_kg double not null default 0, add index weight_kg (weight_kg)", true);
			}
			// 1/19/2018 10:07 AM Justin
			if($this->check_need_alter("sku_apply_items",array("weight_kg"))){
				$con->sql_query_false("alter table sku_apply_items add weight_kg double not null default 0, add index weight_kg (weight_kg)",true);
			}
			
			$con->sql_query_false("create table if not exists work_order(
				branch_id int,
				id int auto_increment,
				adj_id int not null default 0,
				adj_date date not null default 0,
				dept_id int not null default 0,
				wo_no char(20) not null default 0,
				remark text,
				branch_is_under_gst tinyint(1) not null default 0,
				user_id int not null default 0,
				notify_users_in text,
				out_total_qty double not null default 0,
				out_total_weight double not null default 0,
				out_total_cost double not null default 0,
				out_actual_received_weight double not null default 0,
				out_shrinkage_weight double not null default 0,
				out_actual_cost_per_kg double not null default 0,
				in_transfer_updated tinyint(1) not null default 0,
				in_total_expect_qty double not null default 0,
				in_total_expect_cost double not null default 0,
				in_total_expect_weight double not null default 0,
				in_total_actual_qty double not null default 0,
				in_total_actual_cost double not null default 0,
				in_total_actual_weight double not null default 0,
				expect_cost_per_kg double not null default 0,
				actual_cost_per_kg double not null default 0,
				expect_shrinkage_weight double not null default 0,
				shrinkage_weight double not null default 0,
				labour_cost double not null default 0,
				packaging_cost double not null default 0,
				total_cost double not null default 0,
				final_cost_per_kg double not null default 0,
				active tinyint(1) not null default 1,
				status tinyint(1) not null default 0,
				completed tinyint(1) not null default 0,
				deleted_by int not null default 0,
				deleted_reason char(100),
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				primary key (branch_id, id),
				index adj_date (adj_date),
				index dept_id (dept_id),
				index user_id (user_id),
				index active_status_completed (active, status, completed),
				index adj_id_bid (adj_id, branch_id),
				index wo_no (wo_no),
				index deleted_by (deleted_by)
			)", true);
			
			$con->sql_query_false("create table if not exists work_order_items_out(
				branch_id int,
				id int auto_increment,
				work_order_id int,
				sku_item_id int not null default 0,
				cost double not null default 0,
				price double not null default 0,
				weight_kg double not null default 0,
				stock_balance double not null default 0,
				qty double not null default 0,
				gst_id int not null default 0,
				gst_code char(30) not null,
				gst_rate double not null default 0,
				display_price_is_inclusive tinyint(1) not null default 0,
				display_price double not null default 0,
				line_total_cost double not null default 0,
				line_exptected_weigth double not null default 0,
				line_actual_received_weigth double not null default 0,
				line_shrinkage_weigth double not null default 0,
				cost_per_weight double not null default 0,
				primary key (branch_id, id),
				index wo_id_bid (work_order_id, branch_id),
				index sku_item_id (sku_item_id)
			)", true);
			
			$con->sql_query_false("create table if not exists work_order_items_in(
				branch_id int,
				id int auto_increment,
				work_order_id int,
				sku_item_id int not null default 0,
				cost double not null default 0,
				price double not null default 0,
				weight_kg double not null default 0,
				stock_balance double not null default 0,
				gst_id int not null default 0,
				gst_code char(30) not null,
				gst_rate double not null default 0,
				display_price_is_inclusive tinyint(1) not null default 0,
				display_price double not null default 0,
				expect_qty double not null default 0,
				expect_cost double not null default 0,
				line_total_expect_cost double not null default 0,
				line_total_expect_weight double not null default 0,
				actual_qty double not null default 0,
				actual_cost double not null default 0,
				line_total_actual_cost double not null default 0,
				line_total_actual_weight double not null default 0,
				finish_cost double not null default 0,
				line_total_finish_cost double not null default 0,
				primary key (branch_id, id),
				index wo_id_bid (work_order_id, branch_id),
				index sku_item_id (sku_item_id)
			)", true);
			
			$this->update(349);
		}
		
		if($this->ver < 350){
			// inactive tom
			$con->sql_query_false("update user set active=0 where u='arms_tom'", true);
			
			$this->setup_arms_user();
			
			$this->update(350);
		}
		
		if($this->ver < 351){
			// 3/30/2018 2:45 PM Justin
			$con->sql_query_false("create table if not exists sa_sales_cache_monitoring(
				branch_id int,
				date date not null default 0,
				primary key (branch_id, date)
			)", true);
			
			$this->update(351);
		}
		
		if($this->ver < 352){
			$this->setup_arms_user();
			
			$this->update(352);
		}
		
		if($this->ver < 353){
			if(!$_GET['force_maintenance'] && $starting_ver>340){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// Foreign Currency - Andy
			$con->sql_query_false("create table if not exists foreign_currency_rate(
				code char(20) not null,
				rate double not null default 0,
				date date not null default 0,
				user_id int(11) not null default 0,
				last_update timestamp not null default 0,
				primary key (code),
				index user_id (user_id)
			)", true);
			
			$con->sql_query_false("create table if not exists foreign_currency_rate_history(
				code char(20) not null,
				rate double not null default 0,
				date_from date not null default 0,
				date_to date not null default 0,
				user_id int(11) not null default 0,
				primary key (code, date_from, date_to),
				index user_id (user_id)
			)", true);
			
			$con->sql_query_false("create table if not exists foreign_currency_rate_history_record(
				code char(20) not null,
				rate double not null default 0,
				date date not null default 0,
				user_id int(11) not null default 0,
				timestamp timestamp not null default 0,
				primary key (code, date),
				index date (date)
			)", true);
			
			if($this->check_need_alter("po",array("currency_code", "currency_rate","pending_currency_rate","can_change_currency_rate"))){
				$con->sql_query_false("alter table po add currency_code char(10), add currency_rate double not null default 1, add pending_currency_rate double not null default -1, add can_change_currency_rate tinyint(1) not null default 0, add index currency_code (currency_code)",true);
			}
			
			$con->sql_query_false("create table if not exists po_currency_rate_history (
				branch_id int,
				id int auto_increment,
				po_id int not null default 0,
				user_id int not null default 0,
				override_by_user_id int not null default 0,
				old_rate double not null default 0,
				new_rate double not null default 0,				
				timestamp timestamp not null default 0,
				primary key (branch_id, id),
				index po_id_bid (po_id, branch_id),
				index user_id (user_id),
				index override_by_user_id (override_by_user_id)
			)", true);
			
			// Justin
			if($this->check_need_alter("grr",array("currency_code", "currency_rate","use_po_currency","currency_rate_override_by_user_id"))){
				$con->sql_query_false("alter table grr add currency_code char(10), add currency_rate double not null default 1, add use_po_currency tinyint(1) not null default 0, add currency_rate_override_by_user_id int(11) not null default 0, add index currency_code(currency_code), add index currency_rate_override_by_user_id (currency_rate_override_by_user_id)",true);
			}
			
			if($this->check_need_alter("gra",array("currency_code", "currency_rate", "override_by_user_id"))){
				$con->sql_query_false("alter table gra add currency_code char(10), add currency_rate double not null default 1, add override_by_user_id int not null default 0, add index currency_code(currency_code), add index override_by_user_id (override_by_user_id)",true);
			}
			
			if($this->check_need_alter("gra_items",array("currency_code"))){
				$con->sql_query_false("alter table gra_items add currency_code char(10), add index currency_code(currency_code)",true);
			}
			
			
			
			if($this->check_need_alter("dnote",array("currency_code", "currency_rate"))){
				$con->sql_query_false("alter table dnote add currency_code char(10), add currency_rate double not null default 1, add index currency_code(currency_code)",true);
			}
			
			// Andy - Fix error message cant show
			if($this->check_need_alter("counter_status",array("lasterr"), array("lasterr"=>"char(200)"))){
				$con->sql_query_false("alter table counter_status modify lasterr char(200)", true);
			}
			
			// 5/17/2018 2:22 PM Andy - Fix index problem, sync speed slow
			$con->sql_query_false("alter table pos_user_log add index bid_cid_date (branch_id, counter_id, date)");
			
			$this->update(353);
		}
		
		if($this->ver < 354){
			// 6/8/2018 10:42 AM Justin - for counter foreign currency conversion
			if($this->check_need_alter("foreign_currency_rate",array("base_rate"))){
				$con->sql_query_false("alter table foreign_currency_rate add base_rate double not null default 0 after rate",true);
			}
			
			if($this->check_need_alter("foreign_currency_rate_history",array("base_rate"))){
				$con->sql_query_false("alter table foreign_currency_rate_history add base_rate double not null default 0 after rate",true);
			}
			
			if($this->check_need_alter("foreign_currency_rate_history_record",array("base_rate"))){
				$con->sql_query_false("alter table foreign_currency_rate_history_record add base_rate double not null default 0 after rate",true);
			}
			
			$this->update(354);
			
		}
		
		if($this->ver < 355){
			// 6/12/2018 3:05 PM Andy - Email Management
			$con->sql_query("create table if not exists email_list(
				guid char(36) primary key,
				branch_id int,
				user_id int not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				subject char(200),
				mailer_data text,
				active tinyint(1) not null default 1,
				sent tinyint(1) not null default 0,
				sent_time timestamp default 0,
				index active (active),
				index sent (sent),
				index branch_id (branch_id),
				index user_id (user_id),
				index added (added)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists email_list_log(
				email_guid char(36),
				user_id int not null default 0,
				type char(20) not null,
				row_sequence int not null default 1,
				log_time timestamp default 0,
				index email_guid (email_guid),
				index user_id (user_id),
				index type (type),
				index log_time (log_time),
				index row_sequence (row_sequence)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(355);
		}
		
		if($this->ver < 356){
			if(!$_GET['force_maintenance'] && $starting_ver>340){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 7/23/2018 - HockLee
			// packing - HockLee
			$con->sql_query_false("create table if not exists packing(
				id int(11) not null auto_increment,
				branch_id int(11) not null,
				user_id int(11) not null,
				do_items_id int(11) not null,
				carton int(5) null default null,
				weight_kg double null default null,
				pack_date date null default null,			
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				active int(1) null default '1',
				primary key (branch_id, id),
				index do_items_id (do_items_id)
			)", true);

			// do_assignment - HockLee
			$con->sql_query_false("create table if not exists do_assignment(
				id int(4) not null auto_increment,
				branch_id int(11) not null,
				user_id int(11) not null,
				transporter_id int(4) not null,
				route_id int(3) not null,
				vehicle_id int(4) not null,
				driver_id int(4) not null,
				do_id int(11) not null,
				active int(1) null default 1,
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				primary key (branch_id, id),
				index do_id (do_id),
				index transporter_id (transporter_id),
				index vehicle_id (vehicle_id),
				index driver_id (driver_id)
			)", true);

			// transporter - HockLee
			$con->sql_query_false("create table if not exists transporter(
				id int(4) not null auto_increment,
				user_id int(11) not null,
				type_id int(2) null default 0,
				code char(20) not null collate 'latin1_general_ci',
				company_name char(100) not null collate 'latin1_general_ci',
				address text null collate 'latin1_general_ci',
				phone_1 char(20) null default null collate 'latin1_general_ci',
				phone_2 char(20) null default null collate 'latin1_general_ci',
				fax char(20) null default null collate 'latin1_general_ci',
				contact_person char(100) null default null collate 'latin1_general_ci',
				contact_email char(40) null default null collate 'latin1_general_ci',
				active enum('1','0') not null default '0' collate 'latin1_general_ci',
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				primary key (id),
				index type_id (type_id)
			)", true);

			// transporter_area - HockLee
			$con->sql_query_false("create table if not exists transporter_area(
				id int(4) not null auto_increment,
				user_id int(11) not null,
				name char(35) null default null collate 'latin1_general_ci',
				active int(1) null default '1',
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				primary key (id)
			)", true);

			// transporter_route_area - HockLee
			$con->sql_query_false("create table if not exists transporter_route_area(
				id int(4) not null auto_increment,
				user_id int(11) not null,
				area_id int(4) null default null,
				sequence int(3) not null,
				route_id int(3) null default null,
				active int(1) null default 1,
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				primary key (id),
				index area_id (area_id),
				index sequence (sequence),
				index route_id (route_id)
			)", true);

			// transporter_driver - HockLee
			$con->sql_query_false("create table if not exists transporter_driver(
				id int(4) not null auto_increment,
				user_id int(11) not null,
				name char(50) not null collate 'latin1_general_ci',
				ic_no char(20) null default '0' collate 'latin1_general_ci',
				address text null collate 'latin1_general_ci',
				phone_1 char(20) null default '0' collate 'latin1_general_ci',
				phone_2 char(20) null default '0' collate 'latin1_general_ci',
				vehicle_id int(4) null default '0',
				assigned int(1) null default '0',
				active enum('1','0') not null default '0' collate 'latin1_general_ci',
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				primary key (id),
				index ic_no (ic_no),
				index vehicle_id (vehicle_id)
			)", true);

			// transporter_route - HockLee
			$con->sql_query_false("create table if not exists transporter_route(
				id int(3) not null auto_increment,
				user_id int(11) not null,
				name char(35) null default null collate 'latin1_general_ci',
				active int(1) null default 1,
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				primary key (id),
				index active (active)
			)", true);

			// transporter_type - HockLee
			$con->sql_query_false("create table if not exists transporter_type(
				id int(2) not null auto_increment,
				user_id int(11) not null,
				name char(25) not null collate 'latin1_general_ci',
				active enum('1','0') not null default '0' collate 'latin1_general_ci',
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				primary key (id),
				index active (active)
			)", true);

			// transporter_vehicle - HockLee
			$con->sql_query_false("create table if not exists transporter_vehicle(
				id int(4) not null auto_increment,
				user_id int(11) not null,
				plate_no char(10) not null collate 'latin1_general_ci',
				brand_id int(2) null default 0,
				type_id int(2) null default 0,
				route_id int(2) null default 0,
				status_id int(2) null default 0,
				max_load int(5) null default 0,
				transporter_id int(2) null default 0,
				occupied int(1) null default 0,
				active enum('1','0') not null default '0' collate 'latin1_general_ci',
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				primary key (id),
				unique index plate_no (plate_no),
				index active (active)
			)", true);

			// transporter_vehicle_brand - HockLee
			$con->sql_query_false("create table if not exists transporter_vehicle_brand(
				id int(2) not null auto_increment,
				user_id int(11) not null,
				name char(50) not null collate 'latin1_general_ci',
				active enum('1','0') not null default '1' collate 'latin1_general_ci',
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				primary key (id),
				unique index name (name),
				index active (active)
			)", true);

			// transporter_vehicle_status - HockLee
			$con->sql_query_false("create table if not exists transporter_vehicle_status(
				id int(2) not null auto_increment,
				user_id int(11) not null,
				name char(25) not null collate 'latin1_general_ci',
				active enum('1','0') not null default '1' collate 'latin1_general_ci',
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				primary key (id),
				unique index name (name),
				index active (active)
			)", true);

			// transporter_vehicle_type - HockLee
			$con->sql_query_false("create table if not exists transporter_vehicle_type(
				id int(2) not null auto_increment,
				user_id int(11) not null,
				name char(25) not null collate 'latin1_general_ci',
				active enum('1','0') not null default '0' collate 'latin1_general_ci',
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				primary key (id),
				unique index name (name),
				index active (active)
			)", true);

			if($this->check_need_alter("branch",array("integration_code"))){
				$con->sql_query_false("alter table branch add integration_code char(10), add index integration_code (integration_code)",true);
			}

			if($this->check_need_alter("debtor",array("integration_code"))){
				$con->sql_query_false("alter table debtor add integration_code char(10), add index integration_code (integration_code)",true);
			}

			if($this->check_need_alter("sales_order",array("integration_code"))){
				$con->sql_query_false("alter table sales_order add integration_code char(10), add index integration_code (integration_code)",true);
			}

			if($this->check_need_alter("do",array("batch_code", "packed", "integration_code"))){
				$con->sql_query_false("alter table do add batch_code char(30) after branch_id, add packed int(1) default 0 after checkout_info, add integration_code char(10), add index batch_code (batch_code), add index packed (packed), add index integration_code (integration_code)",true);
			}
			
			$this->update(356);
		}
		
		if($this->ver < 357){
			if(!$_GET['force_maintenance'] && $starting_ver>340){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 8/27/2018 11:35 AM Andy - Vendor / GRR / GRN SST
			if($this->check_need_alter("vendor",array("tax_register", "tax_percent"))){
				$con->sql_query_false("alter table vendor add tax_register tinyint(1) not null default 0, add tax_percent double not null default 0",true);
			}
			if($this->check_need_alter("grr",array("tax_register", "tax_percent", "grr_tax"))){
				$con->sql_query_false("alter table grr add tax_register tinyint(1) not null default 0, add tax_percent double not null default 0, add grr_tax double not null default 0, add index tax_register (tax_register)",true);
			}
			if($this->check_need_alter("grr_items",array("tax"))){
				$con->sql_query_false("alter table grr_items add tax double not null default 0",true);
			}
			
			$this->update(357);
		}
		
		if($this->ver < 358){
			// inactive tom
			$con->sql_query_false("update user set active=0 where u='arms_lan'", true);
			
			$this->update(358);
		}
		
		if($this->ver < 359){
			if(!$_GET['force_maintenance'] && $starting_ver>340){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// Andy
			if($this->check_need_alter("counter_status",array("revision"),array("revision"=>"char(10)"))){
				$con->sql_query_false("alter table counter_status modify revision char(10)",true);
			}
			
			$this->update(359);
		}
		
		if($this->ver < 360){
			if(!$_GET['force_maintenance'] && $starting_ver>340){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// Debtor Price - 8/23/2018 11:36 AM Andy
			$con->sql_query_false("create table if not exists sku_items_debtor_price(
				branch_id int,
				sku_item_id int,
				debtor_id int,
				price double not null default 0,
				user_id int not null default 0,
				last_update timestamp not null default 0,
				primary key (branch_id, debtor_id, sku_item_id),
				index debtor_id (debtor_id),
				index sku_item_id_n_debtor_id (sku_item_id, debtor_id),
				index user_id (user_id)
			)", true);
			
			$con->sql_query_false("create table if not exists sku_items_debtor_price_history(
				branch_id int,
				sku_item_id int,
				debtor_id int,
				price double not null default 0,
				user_id int not null default 0,
				added timestamp not null default 0,
				primary key (branch_id, debtor_id, sku_item_id, added),
				index debtor_id (debtor_id),
				index sku_item_id_n_debtor_id (sku_item_id, debtor_id),
				index user_id (user_id),
				index added (added)
			)", true);
			
			if($this->check_need_alter("debtor",array("use_debtor_price"))){
				$con->sql_query_false("alter table debtor add use_debtor_price tinyint(1) not null default 0",true);
			}
			
			if($this->check_need_alter("sales_order",array("use_debtor_price"))){
				$con->sql_query_false("alter table sales_order add use_debtor_price tinyint(1) not null default 0",true);
			}
			
			if($this->check_need_alter("do",array("use_debtor_price"))){
				$con->sql_query_false("alter table do add use_debtor_price tinyint(1) not null default 0",true);
			}
			
			$this->update(360);
		}
		
		if($this->ver < 361){
			// inactive tom
			$con->sql_query_false("update user set active=0 where u='arms_wc'", true);
			
			$this->update(361);
		}
		
		if($this->ver < 362){
			if(!$_GET['force_maintenance'] && $starting_ver>340){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 9/26/2018 1:13 PM Andy
			if($this->check_need_alter("sku_items_future_price_items",array("min_qty"),array("min_qty"=>"int(11)"))){
				$con->sql_query_false("alter table sku_items_future_price_items modify min_qty int default 0",true);
			}
			
			$this->update(362);
		}
		
		if($this->ver < 363){
			// 9/27/2018 5:03 PM Andy - Announcement List
			$con->sql_query_false("create table if not exists user_announcement_status(
				code char(10) not null,
				user_id int not null default 0,
				opened tinyint(1) not null default 0,
				added timestamp default 0,
				primary key(code, user_id),
				index user_id (user_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$this->update(363);
		}
		
		if($this->ver < 364){
			$this->setup_arms_user();
			
			$this->update(364);
		}
		
		if($this->ver < 365){
			if(!$_GET['force_maintenance'] && $starting_ver>350){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// Andy - Convert innoDB
			$con->sql_query_false("ALTER TABLE counter_status ENGINE=InnoDB", true);			
			
			// Justin - Foreign Currency
			$q1 = $con->sql_query_false("show tables like 'daily_sales_cache_b%'", true);
			while($t = $con->sql_fetchrow($q1)){
				$table = $t[0];
				if(!$table) continue;
				
				if($this->check_need_alter($table, array("curr_adj_amt", "memb_curr_adj_amt"))){
					$con->sql_query_false("ALTER TABLE $table add curr_adj_amt double not null default 0, add memb_curr_adj_amt double not null default 0", true);
				}
			}
			$con->sql_freeresult($q1);
			
			$this->update(365);
		}
		
		if($this->ver < 366){
			if(!$_GET['force_maintenance'] && $starting_ver>350){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 10/2/2018 2:13 PM Andy - Fixed table ID
			$q1 = $con->sql_query("show index from branch_approval_history where Key_name='primary'");
			$r = $con->sql_fetchassoc($q1);	// get first primary key
			$con->sql_freeresult($q1);
			
			if($r['Column_name'] != 'branch_id'){
				$con->sql_query_false("alter table branch_approval_history modify added timestamp default CURRENT_TIMESTAMP", true);
				$con->sql_query_false("alter table branch_approval_history drop primary key, add primary key (branch_id, id)", true);
			}
			
			$this->update(366);
		}
		
		if($this->ver < 367){
			$this->setup_arms_user();
			
			$this->update(367);
		}
		
		if($this->ver < 368){
			if(!$_GET['force_maintenance'] && $starting_ver>350){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// Justin - DO Shipment
			if($this->check_need_alter("do", array("shipment_method", "tracking_code"))){
				$con->sql_query_false("alter table do add shipment_method char(50), add tracking_code char(30)", true);
			}
			// Justin - GRR
			if($this->check_need_alter("grr_items", array("po_override_by_user_id"))){
				$con->sql_query_false("alter table grr_items add po_override_by_user_id int(11) not null default 0", true);
			}
			$this->update(368);
		}
		
		if($this->ver < 369){
			if(!$_GET['force_maintenance'] && $starting_ver>350){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// list of tables to be alter become InnoDB
			// ========================== Batch 8.1 ==========================
			$tbl_list = array("counter_trigger_log", "pos_transaction_audit_log", "pos_transaction_clocking_log", "pos_user_log");
			foreach($tbl_list as $tbl_name){
				$con->sql_query_false("SHOW TABLE STATUS where Name = ".ms($tbl_name), true);
				$r = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($r['Engine'] != 'InnoDB'){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
				}				
			}
			unset($tbl_list);
			
			$this->update(369);
		}
		
		if($this->ver < 370){
			$this->setup_arms_user();
			
			$this->update(370);
		}

		if($this->ver < 371){
			$this->setup_arms_user();
			
			$this->update(371);
		}
		
		if($this->ver < 372){
			// inactive arms_lam
			$con->sql_query_false("update user set active=0 where u='arms_lam'", true);
			
			$this->update(372);
		}
		
		if($this->ver < 373){
			if(!$_GET['force_maintenance'] && $starting_ver>350){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			if($this->check_need_alter("sku_items_future_price", array("remark"))){
				$con->sql_query_false("alter table sku_items_future_price add remark text", true);
			}
			
			$this->update(373);
		}
		
		if($this->ver < 374){
			if(!$_GET['force_maintenance'] && $starting_ver>350){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// Vendor Quotation Cost - 11/23/2018 4:24 PM Justin
			$con->sql_query_false("create table if not exists sku_items_vendor_quotation_cost(
				branch_id int,
				sku_item_id int,
				vendor_id int,
				cost double not null default 0,
				user_id int not null default 0,
				last_update timestamp not null default 0,
				primary key (branch_id, vendor_id, sku_item_id),
				index vendor_id (vendor_id),
				index sku_item_id_n_debtor_id (sku_item_id, vendor_id),
				index user_id (user_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$con->sql_query_false("create table if not exists sku_items_vendor_quotation_cost_history(
				branch_id int,
				sku_item_id int,
				vendor_id int,
				cost double not null default 0,
				user_id int not null default 0,
				added timestamp not null default 0,
				primary key (branch_id, vendor_id, sku_item_id, added),
				index vendor_id (vendor_id),
				index sku_item_id_n_vendor_id (sku_item_id, vendor_id),
				index user_id (user_id),
				index added (added)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			if($this->check_need_alter("po_items",array("cost_indicate"),array("cost_indicate"=>"char(20)"))){
				$con->sql_query_false("alter table po_items modify cost_indicate char(20)",true);
			}
			
			
			
			// need to alter this because sales target use ENUM for sku type
			$tbl_list = array("sales_target_b");
			foreach($tbl_list as $tbl_name){
				$q1 = $con->sql_query_false("show tables like '".$tbl_name."%'", true);
				while($t = $con->sql_fetchrow($q1)){
					$table = $t[0];
					if(!$table) continue;
					$con->sql_query_false("ALTER TABLE $table modify sku_type enum('CONSIGN','OUTRIGHT','CONCESS') not null default 'CONSIGN'", true);
				}
				$con->sql_freeresult($q1);
			}
			unset($tbl_list);
			
			$this->update(374);
		}
		
		if($this->ver < 375){
			if(!$_GET['force_maintenance'] && $starting_ver>360){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// Justin - Foreign Currency
			$q1 = $con->sql_query_false("show tables like 'daily_sales_cache_b%'", true);
			while($t = $con->sql_fetchrow($q1)){
				$table = $t[0];
				if(!$table) continue;
				
				if($this->check_need_alter($table, array("curr_adj_amt", "memb_curr_adj_amt"))){
					$con->sql_query_false("ALTER TABLE $table add curr_adj_amt double not null default 0, add memb_curr_adj_amt double not null default 0", true);
				}
			}
			$con->sql_freeresult($q1);
			
			$this->update(375);
		}
		
		if($this->ver < 376){
			if(!$_GET['force_maintenance'] && $starting_ver>360){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			if($this->check_need_alter("gra",array("rounding_adjust"))){
				$con->sql_query_false("alter table gra add rounding_adjust double not null default 0",true);
			}
			
			$this->update(376);
		}
		
		if($this->ver < 377){
			if(!$_GET['force_maintenance'] && $starting_ver>360){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// do we actually need to add for all customers? what if certain customers doesn't want this new SKU type???
			$con->sql_query_false("replace into sku_type (code, description, active) values ('CONCESS', 'Concessionaire', 1)", true);
			
			$con->sql_query_false("create table if not exists suite_device (
				guid char(36) primary key,
				branch_id int not null default 0,
				device_code char(20) not null,
				device_name char(200) not null,
				device_type char(50) not null,
				device_access_token char(20) not null,
				allowed_branches text,
				active tinyint(1) not null default 0,
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				index device_code (device_code),
				index device_access_token (device_access_token),
				index added (added),
				index last_update (last_update),
				index active (active),
				index branch_id (branch_id),
				index device_type (device_type)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query_false("create table if not exists suite_device_status (
				device_guid char(36) primary key,
				encrypt_token char(32) not null,
				paired tinyint(1) not null default 0,
				paired_device_id char(100) not null,
				last_access timestamp not null default 0,
				ip char(15) not null,
				app_type char(50) not null,
				app_version char(10) not null,
				last_user_id int not null default 0,
				index paired (paired),
				index paired_device_id (paired_device_id),
				index encrypt_token (encrypt_token),
				index app_type (app_type),
				index last_user_id (last_user_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query_false("CREATE TABLE `suite_session` ( 
			`ssid` char(32) not null unique, 
			`user_id` int NOT NULL default 0 primary key, 
			`last_active` timestamp NOT NULL
			) ENGINE=MEMORY DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(377);
		}
		
		if($this->ver < 378){
			if(!$_GET['force_maintenance'] && $starting_ver>360){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			$con->sql_query_false("ALTER TABLE sku_items add index packing_uom_id (packing_uom_id)");
			
			$this->update(378);
		}
		
		if($this->ver < 379){
			if(!$_GET['force_maintenance'] && $starting_ver>360){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 1/31/2019 5:24 PM Andy - Replica Status
			if($this->check_need_alter("sql_slaves", array("remark"))){
				$con->sql_query_false("alter table sql_slaves add remark char(200)", true);
			}
			
			// 1/22/2019 4:40 PM Andy - Business Intelligent Cron
			$con->sql_query_false("create table if not exists pos_transaction_bi_status(
				name char(50) primary key,
				value char(50) not null,
				update_time timestamp default 0
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$con->sql_query_false("create table if not exists pos_transaction_bi_record(
				branch_id int,
				date date,
				finalized tinyint(1) default 0,
				finalize_timestamp timestamp default 0,
				last_update timestamp default 0,
				primary key (branch_id, date),
				index (date)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$con->sql_query_false("create table if not exists pos_transaction_bi_branch_dept_sales(
				branch_id int,
				date date,
				dept_id int,
				h int,
				last_pos_time timestamp default 0,
				sales_amt double default 0,
				tax_amt double default 0,
				disc_amt double default 0,
				last_update timestamp default 0,
				branch_desc char(150),
				dept_desc char(150),
				primary key (branch_id, date, dept_id, h),
				index (dept_id),
				index (date),
				index (h)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$this->update(379);
		}
		
		if($this->ver < 380){
			if(!$_GET['force_maintenance'] && $starting_ver>370){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 1/7/2019 3:07 PM Andy - Fixed pos table index
			$con->sql_query_false("ALTER TABLE pos add index receipt_ref_no (receipt_ref_no)");
			
			$this->update(380);
		}
		
		if($this->ver < 381){
			// inactive tom
			$con->sql_query_false("update user set active=0 where u='arms_hocklee'", true);
			$con->sql_query_false("update user set active=0 where u='arms_mark'", true);
			
			$this->update(381);
		}
		
		if($this->ver < 382){
			$this->setup_arms_user();
						
			$this->update(382);
		}
		
		if($this->ver < 383){
			if(!$_GET['force_maintenance'] && $starting_ver>370){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// DO Invoice Adjust -- Andy
			if($this->check_need_alter("do", array("inv_sheet_adj_amt"))){
				$con->sql_query_false("alter table do add inv_sheet_adj_amt double not null default 0",true);
			}
						
			$this->update(383);
		}
		
		if($this->ver < 384){
			if(!$_GET['force_maintenance'] && $starting_ver>370){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// eWallet -- Andy
			$con->sql_query_false("create table if not exists pos_transaction_ewallet_payment (
				guid char(36) primary key,
				branch_id int not null default 0,
				counter_id int not null default 0,
				date date,
				receipt_ref_no char(20) not null,
				user_id int not null default 0,
				ewallet_type char(20),
				amount double not null default 0,
				remark char(200),
				success tinyint(1) default 0,
				success_ref_no char(100) not null,
				added timestamp default 0,
				last_update timestamp default 0,
				more_info text,
				failed_info text,
				unique receipt_ref_no_n_ewallet_type (receipt_ref_no, ewallet_type),
				index bid_date_cid(branch_id, date, counter_id),
				index user_id (user_id),
				index ewallet_type (ewallet_type),
				index success (success),
				index added (added),
				index success_ref_no (success_ref_no)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			if($this->check_need_alter("pos_payment",array("group_type"))){
				$con->sql_query_false("alter table pos_payment add group_type char(30) not null, add index group_type (group_type)",true);
			}
						
			$this->update(384);
		}
		
		if($this->ver < 385){
			if(!$_GET['force_maintenance'] && $starting_ver>370){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// innodb
			// ========================== Batch 10 ==========================
			$tbl_list = array("vendor_sku_history_b");
			foreach($tbl_list as $tbl_name){
				$q1 = $con->sql_query_false("show tables like '".$tbl_name."%'", true);
				while($t = $con->sql_fetchrow($q1)){
					$table = $t[0];
					if(!$table) continue;
					$con->sql_query_false("ALTER TABLE $table ENGINE=InnoDB", true);
				}
				$con->sql_freeresult($q1);
			}
			unset($tbl_list);
			
			// 3/28/2019 - Andy
			if($this->check_need_alter("pos", array("pos_guid"))){
				$con->sql_query_false("alter table pos add pos_guid char(36) not null, add index pos_guid (pos_guid)", true);
			}
						
			$this->update(385);
		}
		
		if($this->ver < 386){
			if(!$_GET['force_maintenance'] && $starting_ver>370){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// InnoDB
			// ========================== Batch 8 ==========================
			$tbl_list = array("sku_obsolete", "sku_supermarket_code", "vendor_sku_history", "vendor_stock_reorder_sku");
			foreach($tbl_list as $tbl_name){
				$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
			}
			unset($tbl_list);	
			
			// ARMS Accounting Integration 
			// 3/28/2019 5:32 PM - Andy
			$con->sql_query("create table if not exists arms_acc_settings (
				branch_id int not null default 0,
				type char(100) not null,
				account_code char(50) not null,
				account_name char(100) not null,
				last_update timestamp default 0,
				primary key(branch_id, type)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists arms_acc_gst_settings (
				branch_id int not null default 0,
				gst_id int,
				account_code char(50) not null,
				account_name char(100) not null,
				last_update timestamp default 0,
				primary key(branch_id, gst_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists arms_acc_other_settings (
				branch_id int not null default 0,
				setting_name char(100) not null,
				setting_value char(100) not null,
				last_update timestamp default 0,
				primary key(branch_id, setting_name)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists arms_acc_batch_no (
				branch_id int not null default 0,
				type char(20) not null,
				id int not null default 0,
				batch_id char(20) not null,
				status tinyint(1) not null default 0,
				tax_amount double not null default 0,
				amount double not null default 0,
				inv_list_data MEDIUMTEXT,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key(type, branch_id, id),
				index bid_id (branch_id, id),
				index batch_id_type_bid (batch_id, type, branch_id),
				index status (status)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists ap_arms_acc_info (
				branch_id int not null default 0,
				grr_id int not null default 0,
				batch_id char(20) not null,
				acc_doc_no char(30) not null,
				status tinyint(1) not null default 0,
				tax_amount double not null default 0,
				amount double not null default 0,
				inv_data text,
				failed_reason char(100) not null,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key(branch_id, grr_id),
				index batch_id (batch_id),
				index acc_doc_no (acc_doc_no),
				index status (status)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists cs_arms_acc_info (
				branch_id int not null default 0,
				type char(20) not null,
				inv_no char(50) not null,
				date date not null,
				batch_id char(20) not null,
				acc_tran_id char(30) not null,
				acc_doc_no char(30) not null,
				tax_amount double not null default 0,
				amount double not null default 0,
				status tinyint(1) not null default 0,
				failed_reason char(100) not null,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key(branch_id, type, inv_no),
				index date (date),
				index type (type),
				index inv_no (inv_no),
				index batch_id (batch_id),
				index acc_doc_no (acc_doc_no),
				index status (status),
				index acc_tran_id (acc_tran_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			if($this->check_need_alter("grr", array("arms_acc_exported"))){
				$con->sql_query_false("alter table grr add arms_acc_exported tinyint(1) not null default 0, add index arms_acc_exported (arms_acc_exported)", true);
			}
			
			if($this->check_need_alter("do", array("arms_acc_exported"))){
				$con->sql_query_false("alter table do add arms_acc_exported tinyint(1) not null default 0, add index arms_acc_exported (arms_acc_exported)", true);
			}
			
			if($this->check_need_alter("pos", array("arms_acc_exported"))){
				$con->sql_query_false("alter table pos add arms_acc_exported tinyint(1) not null default 0, add index arms_acc_exported (arms_acc_exported)", true);
			}
			
			$this->update(386);
		}
		
		if($this->ver < 387){
			if(!$_GET['force_maintenance'] && $starting_ver>370){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// Justin
			$con->sql_query("create table if not exists ewallet_integrator_list (
				ewallet_type char(30) not null,
				integrator_id int not null default 0,
				integrator_type char(30) not null,
				integrator_logo_link text,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key(integrator_type, integrator_id, ewallet_type),
				index ewallet_type (ewallet_type),
				index integrator_id (integrator_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			if($this->check_need_alter("pos_transaction_ewallet_payment", array("void_success", "void_success_ref_no"))){
				$con->sql_query_false("alter table pos_transaction_ewallet_payment add void_success tinyint(1) not null default 0 after success_ref_no, add void_success_ref_no char(100) not null after void_success, add index void_success (void_success), add index void_success_ref_no (void_success_ref_no)", true);
			}
			
			$this->update(387);
		}
		
		if($this->ver < 388){
			if(!$_GET['force_maintenance'] && $starting_ver>370){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 4/16/2019 2:56 PM - Andy
			$con->sql_query("create table if not exists arms_acc_payment_settings (
				branch_id int not null default 0,
				payment_type char(100) not null,
				account_code char(50) not null,
				account_name char(100) not null,
				last_update timestamp default 0,
				primary key(branch_id, payment_type)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(388);
		}
		
		if($this->ver < 389){
			if(!$_GET['force_maintenance'] && $starting_ver>370){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 4/19/2019 10:20 AM Andy
			$con->sql_query("create table if not exists suite_consignment_report_data (
				branch_id int not null default 0,
				device_guid char(36) not null,
				batch_id char(30),
				date date,
				sku_item_id int not null default 0,
				y int not null default 0,
				m int not null default 0,
				sales_qty int not null default 0,
				last_update timestamp default 0,
				primary key(branch_id, device_guid, batch_id, sku_item_id),
				index ym_bid (y, m, branch_id),
				index sku_item_id (sku_item_id),
				index date (date)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			// ========================== Batch 2 ==========================
			$tbl_list = array("sku_group_item", "sku_group_vp_date_control", "sku_type");
			foreach($tbl_list as $tbl_name){
				$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
			}
			unset($tbl_list);
			
			$this->update(389);
		}
		
		if($this->ver < 390){
			if(!$_GET['force_maintenance'] && $starting_ver>380){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 6/25/2012 11:31 AM Andy - remember to include in next version
			$con->sql_query_false("create table if not exists sku_extra_info(
				sku_item_id int primary key
			)", true);
			if($this->check_need_alter("sku_apply_items",array("extra_info"))){
				$con->sql_query_false("alter table sku_apply_items add extra_info text", true);
			}
			
			// 5/3/2019 11:10 AM Andy - Debtor Default Sales Agent
			if($this->check_need_alter("debtor", array("sa_id"))){
				$con->sql_query_false("alter table debtor add sa_id int not null default 0", true);
			}
			
			$this->update(390);
		}
		
		if($this->ver < 391){
			if(!$_GET['force_maintenance'] && $starting_ver>380){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 5/17/2019 2:08 PM Andy -Fix Debit NOTE total_amount zero
			$con->sql_query("update dnote set total_amount=round(total_gross_amount+total_gst_amount,2) where total_amount=0");
			$con->sql_query("update dnote_items set item_amount=round(item_gross_amount+item_gst_amount,2) where item_amount=0");
			
			// InnoDB
			// ========================== Batch 6.1 ==========================
			$tbl_list = array("sku_items_mqprice", "sku_items_mqprice_history");
			foreach($tbl_list as $tbl_name){
				$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
			}
			unset($tbl_list);
			
			$this->update(391);
		}
		
		if($this->ver < 392){
			if(!$_GET['force_maintenance'] && $starting_ver>380){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 4/26/2019 1:53 PM Andy - OS Trio Integration
			$con->sql_query("create table if not exists ostrio_integration_status (
				branch_id int not null default 0,
				integration_type char(20) not null,
				sub_type char(20) not null,
				status tinyint(1) not null default 0,
				got_error tinyint(1) not null default 0,
				error_msg text,
				start_time timestamp default 0,
				end_time timestamp default 0,
				last_update timestamp default 0,
				primary key(branch_id, integration_type, sub_type),
				index status (status),
				index got_error (got_error),
				index integration_type (integration_type),
				index sub_type (sub_type)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists ostrio_vendor_mapping (
				vendor_id int not null default 0 primary key,
				ostrio_vendor_id char(50) not null,
				last_update timestamp default 0,
				index ostrio_vendor_id(ostrio_vendor_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists ostrio_debtor_mapping (
				debtor_id int not null default 0 primary key,
				ostrio_debtor_id char(50) not null,
				last_update timestamp default 0,
				index ostrio_debtor_id(ostrio_debtor_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists ostrio_ap_mapping (
				branch_id int not null default 0,
				doc_id int not null default 0,
				acc_doc_no char(30) not null,
				doc_date date not null,
				ap_type char(20) not null,
				dept_id int not null default 0,
				amount double not null default 0,
				ostrio_ap_id int not null default 0,
				api_data text,
				last_update timestamp default 0,
				primary key (branch_id, ap_type, doc_id),
				index ap_type (ap_type),
				index ostrio_ap_id (ostrio_ap_id),
				index doc_date (doc_date),
				index dept_id (dept_id),
				index acc_doc_no (acc_doc_no)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists ostrio_gl_mapping (
				branch_id int not null default 0,
				doc_id bigint not null default 0,
				acc_doc_no char(30) not null,
				doc_date date not null,				
				gl_type char(20) not null,
				dept_id int not null default 0,
				amount double not null default 0,
				ostrio_gl_id int not null default 0,
				api_data text,
				last_update timestamp default 0,
				primary key (branch_id, gl_type, doc_id),
				index gl_type (gl_type),
				index ostrio_gl_id (ostrio_gl_id),
				index doc_date (doc_date),
				index dept_id (dept_id),
				index acc_doc_no (acc_doc_no)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists ostrio_cs_batch (
				branch_id int not null default 0,
				date date,
				id int not null default 0,
				cs_batch_no char(50) not null unique,
				acc_doc_no char(30) not null,
				ostrio_cs_id int not null default 0,
				api_data text,
				last_update timestamp default 0,
				primary key (branch_id, date, id),
				index date (date),
				index id (id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists ostrio_cs_mapping (
				branch_id int not null default 0,
				doc_date date not null,	
				cs_batch_no char(50) not null,
				doc_no char(30) not null,
				cs_type char(20) not null,
				amount double not null default 0,
				last_update timestamp default 0,
				primary key (branch_id, doc_no, cs_type),
				index cs_type (cs_type),
				index cs_batch_no (cs_batch_no),
				index doc_date (doc_date),
				index doc_no (doc_no)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(392);
		}
		
		if($this->ver < 393){
			if(!$_GET['force_maintenance'] && $starting_ver>380){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 5/29/2019 10:42 AM Andy - OS Trio
			if($this->check_need_alter("ostrio_gl_mapping",array("doc_id"), array("doc_id"=>"bigint(20)"))){
				$con->sql_query_false("alter table ostrio_gl_mapping modify doc_id bigint not null default 0", true);
			}
			
			// ========================== Batch 6.2 ==========================
			$tbl_list = array("sku_items_qprice","sku_items_qprice_history");
			foreach($tbl_list as $tbl_name){
				$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
			}
			unset($tbl_list);
			
			$this->update(393);
		}
		
		if($this->ver < 394){
			if(!$_GET['force_maintenance'] && $starting_ver>380){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 5/17/2019 4:53 PM Andy - ARMS Accounting Integration - AR
			$con->sql_query("create table if not exists ar_arms_acc_info (
				branch_id int not null default 0,
				do_id int not null default 0,
				batch_id char(20) not null,
				acc_doc_no char(30) not null,
				status tinyint(1) not null default 0,
				tax_amount double not null default 0,
				amount double not null default 0,
				inv_data text,
				failed_reason char(100) not null,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key(branch_id, do_id),
				index batch_id (batch_id),
				index acc_doc_no (acc_doc_no),
				index status (status)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(394);
		}
		
		if($this->ver < 395){
			if(!$_GET['force_maintenance'] && $starting_ver>380){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 5/28/2019 3:05 PM Andy - DO
			if($this->check_need_alter("do", array("relationship_guid"))){
				$con->sql_query_false("alter table do add relationship_guid char(36) not null, add index relationship_guid (relationship_guid)", true);
			}
			
			$this->update(395);
		}
		
		if($this->ver < 396){
			if(!$_GET['force_maintenance'] && $starting_ver>390){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 5/28/2019 3:05 PM Andy - Stock Reorder Minimum Order Quantity
			if($this->check_need_alter("sku", array("po_reorder_moq"))){
				$con->sql_query_false("alter table sku add po_reorder_moq int not null default 0 after po_reorder_qty_max", true);
			}
			if($this->check_need_alter("sku_items", array("po_reorder_moq"))){
				$con->sql_query_false("alter table sku_items add po_reorder_moq int not null default 0 after po_reorder_qty_max", true);
			}
			if($this->check_need_alter("sku_apply_items", array("po_reorder_moq"))){
				$con->sql_query_false("alter table sku_apply_items add po_reorder_moq int not null default 0 after po_reorder_qty_max", true);
			}
			
			$this->update(396);
		}
		
		if($this->ver < 397){
			$this->setup_arms_user();
						
			$this->update(397);
		}
		
		if($this->ver < 398){			
			$con->sql_query("create table if not exists system_settings (
				setting_name char(100) not null,
				setting_value text not null,
				last_update timestamp default 0,
				primary key(setting_name)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(398);
		}
		
		if($this->ver < 399){
			if(!$_GET['force_maintenance'] && $starting_ver>390){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 6/17/2019 3:10 PM Andy - Vertical Logo
			if($this->check_need_alter("branch", array("is_vertical_logo", "vertical_logo_no_company_name"))){
				$con->sql_query_false("alter table branch add is_vertical_logo tinyint(1) not null default 0, add vertical_logo_no_company_name tinyint(1) not null default 0",true);
			}
			
			$this->update(399);
		}
		
		if($this->ver < 400){
			if(!$_GET['force_maintenance'] && $starting_ver>390){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 6.3 ==========================
			$tbl_list = array("sku_items_rprice", "sku_items_rprice_history");
			foreach($tbl_list as $tbl_name){
				$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
			}
			unset($tbl_list);
			
			// DO Checkout Checklist
			if($this->check_need_alter("do ", array("checklist_disable_parent_child"))){
				$con->sql_query_false("alter table do add checklist_disable_parent_child tinyint(1) not null default 0",true);
			}
			
			$this->update(400);
		}
		
		if($this->ver < 401){
			if(!$_GET['force_maintenance'] && $starting_ver>390){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 6/26/2019 5:49 PM Andy - Mobile Membership App
			if($this->check_need_alter("membership", array("mobile_registered", "mobile_registered_time", "mobile_registered_verify_code", "memb_password", "last_recalculate_time"))){
				$con->sql_query_false("alter table membership 
					add last_recalculate_time timestamp not null default 0,
					add mobile_registered tinyint(1) not null default 0, 
					add mobile_registered_time timestamp not null default 0,
					add mobile_registered_verify_code char(64),
					add memb_password char(64) default null, 
					add index mobile_registered(mobile_registered),
					add index mobile_registered_verify_code (mobile_registered_verify_code)", true);
			}
			
			$con->sql_query("create table if not exists member_app_session (
				nric char(20) not null,
				device_id char(100) not null,
				session_token char(36) not null,
				mobile_type char(20) not null,
				push_notification_token char(200) not null,
				ip char(15) not null,
				app_type char(50) not null,
				app_version char(10) not null,
				last_access timestamp default 0,
				primary key (nric, device_id),
				index session_token (session_token),
				index push_notification_token (push_notification_token),
				index mobile_type (mobile_type)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			
			if($this->check_need_alter("mst_voucher ", array("member_card_no"))){
				$con->sql_query_false("alter table mst_voucher add member_card_no char(20) not null, add index member_card_no (member_card_no)", true);
			}
			
			if($this->check_need_alter("promotion ", array("show_in_member_mobile", "enable_special_for_you", "special_for_you_info"))){
				$con->sql_query_false("alter table promotion add show_in_member_mobile tinyint(1) not null default 0, add enable_special_for_you tinyint(1) not null default 0, add special_for_you_info text, add index show_in_member_mobile (show_in_member_mobile), add index enable_special_for_you (enable_special_for_you)", true);
			}
			
			if($this->check_need_alter("promotion_items ", array("show_in_member_mobile"))){
				$con->sql_query_false("alter table promotion_items add show_in_member_mobile tinyint(1) not null default 0, add index show_in_member_mobile (show_in_member_mobile)", true);
			}
			
			$con->sql_query("create table if not exists membership_fav_items (
				card_no char(20) not null,
				branch_id int not null default 0,
				sku_item_id int not null default 0,
				date date,
				qty double not null default 0,
				amount double not null default 0,
				disc_amt double not null default 0,
				disc_amt2 double not null default 0,
				tax_amt double not null default 0,
				last_update timestamp not null default 0,
				primary key (card_no, branch_id, sku_item_id, date),
				index branch_id (branch_id),
				index sku_item_id (sku_item_id),
				index date (date)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists membership_mobile_ads_banner (
				banner_name char(100) not null,
				banner_description char(200) not null,
				screen_name char(50) not null,
				screen_description char(200) not null,
				sequence integer not null default 0,
				banner_width integer not null default 0,
				banner_height integer not null default 0,
				max_photo_count integer not null default 1,
				wireframe_url text,
				active tinyint(1) not null default 1,
				banner_info text,
				last_update timestamp not null default 0,
				primary key (banner_name),
				index screen_name (screen_name),
				index active (active)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			// 7/8/2019 11:59 AM Justin - Self Checkout
			if($this->check_need_alter("pos_items ", array("is_valid_weight"))){
				$con->sql_query_false("alter table pos_items add is_valid_weight tinyint(1) not null default 1, add index is_valid_weight (is_valid_weight)", true);
			}
			
			if($this->check_need_alter("pos", array("pos_is_valid_weight", "use_scale_machine"))){
				$con->sql_query_false("alter table pos add pos_is_valid_weight tinyint(1) not null default 1, add use_scale_machine tinyint(1) not null default 0, add index use_scale_machine_valid_weight (use_scale_machine, pos_is_valid_weight)", true);
			}
			
			$this->update(401);
		}
		
		if($this->ver < 402){
			$upd = array();
			$upd['banner_name'] = 'login_top_vertical';
			$upd['banner_description'] = 'Login Screen Top Vertical Banner';
			$upd['screen_name'] = 'login';
			$upd['screen_description'] = 'Login Screen';
			$upd['sequence'] = 1;
			$upd['banner_width'] = 320;
			$upd['banner_height'] = 100;
			$upd['max_photo_count'] = 3;
			$upd['wireframe_url'] = 'ui/membership_mobile/login_top_vertical.png';
			$con->sql_query_false("insert into membership_mobile_ads_banner ".mysql_insert_by_field($upd));
			
			$upd = array();
			$upd['banner_name'] = 'home_btm_vertical';
			$upd['banner_description'] = 'Home Screen Bottom Vertical Banner';
			$upd['screen_name'] = 'home';
			$upd['screen_description'] = 'Home Screen';
			$upd['sequence'] = 2;
			$upd['banner_width'] = 320;
			$upd['banner_height'] = 100;
			$upd['max_photo_count'] = 3;
			$upd['wireframe_url'] = 'ui/membership_mobile/home_bottom_vertical.png';
			$con->sql_query_false("insert into membership_mobile_ads_banner ".mysql_insert_by_field($upd));
			
			$upd = array();
			$upd['banner_name'] = 'voucher_top_vertical';
			$upd['banner_description'] = 'Voucher Screen Top Vertical Banner';
			$upd['sequence'] = 3;
			$upd['screen_name'] = 'voucher';
			$upd['screen_description'] = 'Voucher Screen';			
			$upd['banner_width'] = 320;
			$upd['banner_height'] = 100;
			$upd['max_photo_count'] = 3;
			$upd['wireframe_url'] = 'ui/membership_mobile/voucher_top_vertical.png';
			$con->sql_query_false("insert into membership_mobile_ads_banner ".mysql_insert_by_field($upd));
			
			$upd = array();
			$upd['banner_name'] = 'promo_top_vertical';
			$upd['banner_description'] = 'Promotion Screen Top Vertical Banner';
			$upd['sequence'] = 4;
			$upd['screen_name'] = 'promo';
			$upd['screen_description'] = 'Promotion Screen';			
			$upd['banner_width'] = 320;
			$upd['banner_height'] = 100;
			$upd['max_photo_count'] = 3;
			$upd['wireframe_url'] = 'ui/membership_mobile/promo_top_vertical.png';
			$con->sql_query_false("insert into membership_mobile_ads_banner ".mysql_insert_by_field($upd));
			
			$upd = array();
			$upd['banner_name'] = 'promo_product_top_vertical';
			$upd['banner_description'] = 'Promotion Product Screen Top Vertical Banner';
			$upd['sequence'] = 5;
			$upd['screen_name'] = 'promo_product';
			$upd['screen_description'] = 'Promotion Product Screen';			
			$upd['banner_width'] = 320;
			$upd['banner_height'] = 100;
			$upd['max_photo_count'] = 3;
			$upd['wireframe_url'] = 'ui/membership_mobile/promo_product_top_vertical.png';
			$con->sql_query_false("insert into membership_mobile_ads_banner ".mysql_insert_by_field($upd));
			
			$this->update(402);
		}
		
		if($this->ver < 403){
			if(!$_GET['force_maintenance'] && $starting_ver>390){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 7/17/2019 11:24 AM Andy - Fixed eWallet Payment
			$con->sql_query_false("alter table pos_transaction_ewallet_payment drop index receipt_ref_no, add unique receipt_ref_no_n_ewallet_type (receipt_ref_no, ewallet_type)");
			
			// 7/19/2019 4:11 PM Andy - Fixed membership mobile
			$con->sql_query_false("alter table membership drop mobile_last_login_time");
			
			$con->sql_query_false("update user set active=0 where u='arms_zae'", true);
			
			$this->update(403);
		}
		
		if($this->ver < 404){
			$this->setup_arms_user();
						
			$this->update(404);
		}
		
		if($this->ver < 405){
			if(!$_GET['force_maintenance'] && $starting_ver>390){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 5/22/2019 5:16 PM Andy - Cycle Count
			$con->sql_query("create table if not exists cycle_count (
				branch_id int not null default 0,
				id int not null default 0,
				doc_no char(20) not null,
				user_id int not null default 0,
				approval_history_id int not null default 0,
				st_branch_id int not null default 0,
				st_content_type char(20) not null,
				category_id int not null default 0,
				vendor_id int not null default 0,
				brand_id int not null default 0,
				sku_group_bid int not null default 0,
				sku_group_id int not null default 0,
				propose_st_date date,
				st_date date,
				remark text,
				active tinyint(1) not null default 1,
				status tinyint(1) not null default 0,
				approved tinyint(1) not null default 0,
				printed tinyint(1) not null default 0,
				wip tinyint(1) not null default 0,
				completed tinyint(1) not null default 0,
				sent_to_stock_take tinyint(1) not null default 0,
				pic_user_id int not null default 0,
				audit_user_list text,
				notify_user_list text,
				notify_day int not null default 0,
				notify_sent tinyint(1) not null default 0,
				estimate_sku_count int not null default 0,
				cancel_reason char(100) not null,
				cancelled_by int not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				print_time timestamp default 0,
				wip_start_time timestamp default 0,
				complete_time timestamp default 0,
				generated_pos_qty_time timestamp default 0,
				sent_to_stock_take_time timestamp default 0,
				notify_sent_time timestamp default 0,
				series_doc_no char(20) not null,
				primary key(branch_id, id),
				index doc_no (doc_no),
				index user_id (user_id),
				index approval_history_id (approval_history_id),
				index st_branch_id (st_branch_id),
				index st_content_type (st_content_type),
				index category_id (category_id),
				index vendor_id (vendor_id),
				index brand_id (brand_id),
				index sku_group (sku_group_bid, sku_group_id),
				index propose_st_date (propose_st_date),
				index st_date (st_date),
				index active_status_completed (active, status, approved),
				index printed (printed),
				index wip (wip),
				index completed (completed),
				index sent_to_stock_take (sent_to_stock_take),
				index pic_user_id (pic_user_id),
				index cancelled_by (cancelled_by),
				index notify_sent (notify_sent),
				index series_doc_no (series_doc_no)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists cycle_count_items(
				item_guid char(36) not null primary key,
				branch_id int not null default 0,
				cc_id int not null default 0,
				item_id int not null default 0,
				page_num int not null default 0,
				row_num int not null default 0,
				sku_item_id int not null default 0,
				pos_qty double not null default 0,
				backend_qty double,
				app_qty double,
				calculated_st_qty double,
				st_time timestamp default 0,
				last_update timestamp default 0,
				index bid_cc_id (branch_id, cc_id),
				index page_row (page_num, row_num),
				index item_id (item_id),
				index sku_item_id (sku_item_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			if($this->check_need_alter("stock_take_pre",array("cycle_count_doc_no"))){
				$con->sql_query_false("alter table stock_take_pre add cycle_count_doc_no char(20) not null, add index cycle_count_doc_no (cycle_count_doc_no)",true);
			}
			
			$this->update(405);
		}
		
		
		if($this->ver < 406){
			if(!$_GET['force_maintenance'] && $starting_ver>400){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 8/21/2019 1:39 PM Andy - Membership Notice Board
			$con->sql_query("create table if not exists memberships_notice_board_items(
				id int not null auto_increment primary key,
				item_type char(20) not null,
				image_click_link char(200) not null,
				item_url char(200) not null,
				active tinyint(1) not null default 0,
				video_site char(20) not null,
				video_link char(100) not null,
				sequence int not null default 0,
				more_info text,
				added timestamp default 0,
				last_update timestamp default 0,
				index item_type (item_type),
				index video_site (video_site),
				index video_link (video_link),
				index sequence (sequence),
				index active (active)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(406);
		}
		
		if($this->ver < 407){
			if(!$_GET['force_maintenance'] && $starting_ver>400){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 8/23/2019 4:52 PM Andy - DO Custom Column
			if($this->check_need_alter("do_items", array("custom_col"))){
				$con->sql_query_false("alter table do_items add custom_col text", true);
			}
			
			// ========================== Batch 4 ==========================
			$tbl_list = array("sku_items_mprice", "sku_items_mprice_history");
			foreach($tbl_list as $tbl_name){
				$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
			}
			unset($tbl_list);
			
			$this->update(407);
		}
		
		if($this->ver < 408){
			if(!$_GET['force_maintenance'] && $starting_ver>400){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			if($this->check_need_alter("do_open_items", array("custom_col"))){
				$con->sql_query_false("alter table do_open_items add custom_col text", true);
			}
			
			$this->update(408);
		}
		
		if($this->ver < 409){
			if(!$_GET['force_maintenance'] && $starting_ver>400){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 8/27/2019 2:10 PM Andy - Coupon
			if($this->check_need_alter("coupon", array("min_amt", "min_receipt_amt", "discount_by", "member_limit_type", "member_limit_info", "member_limit_count", "last_member_used_update"))){
				$con->sql_query_false("alter table coupon add min_amt double not null default 0, add min_receipt_amt double not null default 0, add discount_by char(10) not null default 'amt', add member_limit_type char(20) not null, add member_limit_info text, add member_limit_count int not null default 0, add last_member_used_update timestamp not null default 0, add index member_limit_type (member_limit_type)",true);
			}
			$con->sql_query_false("alter table coupon add index active (active)");
			$con->sql_query_false("alter table coupon add index valid_from (valid_from), add index valid_to (valid_to)");
			
			$con->sql_query("create table if not exists coupon_items(
				coupon_code char(20) not null primary key,
				branch_id int not null default 0,
				coupon_id int not null default 0,
				full_coupon_code char(20) not null,
				print_qty int not null default 0,
				print_value double not null default 0,
				print_format char(20) not null,
				remark char(100) not null,
				user_id int not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				index branch_id_coupon_id (branch_id, coupon_id),
				index user_id (user_id),
				index full_coupon_code (full_coupon_code)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists coupon_items_member(
				coupon_code char(20) not null,
				branch_id int not null default 0,
				coupon_id int not null default 0,
				card_no char(20) not null,
				used_count int not null default 0,
				more_info text,
				active tinyint(1) not null default 1,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key(coupon_code, card_no),
				index card_no (card_no),
				index active (active),
				index branch_id_n_coupon_id (branch_id, coupon_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			// 9/20/2019 5:47 PM Justin - SKU Description
			if($this->check_need_alter("pos_items",array("sku_description"),array("sku_description"=>"char(40)"))){
				$con->sql_query_false("alter table pos_items modify sku_description char(40)");
			}
			
			$this->update(409);
		}
		
		if($this->ver < 410){
			if(!$_GET['force_maintenance'] && $starting_ver>400){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// Membership
			if($this->check_need_alter("membership", array("profile_image_url"))){
				$con->sql_query_false("alter table membership add profile_image_url char(200) not null", true);
			}
			
			// PDA PO
			if($this->check_need_alter("po", array("amt_need_update"))){
				$con->sql_query_false("alter table po add amt_need_update tinyint(1) not null default 0", true);
			}
			
			$this->update(410);
		}
		
		if($this->ver < 411){
			if(!$_GET['force_maintenance'] && $starting_ver>400){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 10/3/2019 11:33 AM Andy - added from v412 to prevent multiple alter for sku_items
			if($this->check_need_alter("sku_items",array("model", "length", "height", "width", "got_pos_photo"))){
				$con->sql_query_false("alter table sku_items add model char(50) not null, add length double not null default 0, add height double not null default 0, add width double not null default 0, add got_pos_photo tinyint(1) not null default 0, add index got_pos_photo (got_pos_photo)",true);
			}
			
			// 8/26/2019 5:14 PM Justin - SKU Listing
			if($this->check_need_alter("sku_items",array("model", "length", "height", "width"))){
				$con->sql_query_false("alter table sku_items add model char(50) not null, add length double not null default 0, add height double not null default 0, add width double not null default 0",true);
			}
			
			if($this->check_need_alter("sku_apply_items",array("model", "length", "height", "width"))){
				$con->sql_query_false("alter table sku_apply_items add model char(50) not null, add length double not null default 0, add height double not null default 0, add width double not null default 0",true);
			}
			$con->sql_query_false("alter table sku_items add index sku_apply_items_id (sku_apply_items_id)");
			
			$this->update(411);
		}
		
		if($this->ver < 412){
			if(!$_GET['force_maintenance'] && $starting_ver>400){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			if($this->check_need_alter("sku_items", array("got_pos_photo"))){
				$con->sql_query_false("alter table sku_items add got_pos_photo tinyint(1) not null default 0, add index got_pos_photo (got_pos_photo)", true);
			}
			
			$this->update(412);
		}
		
		if($this->ver < 413){
			if(!$_GET['force_maintenance'] && $starting_ver>410){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			if($this->check_need_alter("do", array("confirm_timestamp"))){
				$con->sql_query_false("alter table do add confirm_timestamp timestamp not null default 0", true);
			}
			
			$this->update(413);
		}
		
		if($this->ver < 414){
			$this->setup_arms_user();
						
			$this->update(414);
		}
		
		if($this->ver < 415){
			if(!$_GET['force_maintenance'] && $starting_ver>410){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 10/15/2019 5:50 PM Andy - DO Checklist
			if($this->check_need_alter("do", array("check_variance"))){
				$con->sql_query_false("alter table do add check_variance tinyint(1) not null default 0", true);
			}
			
			$this->update(415);
		}
		
		if($this->ver < 416){
			if(!$_GET['force_maintenance'] && $starting_ver>410){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 10/1/2019 1:44 PM Andy - Membership Package
			if($this->check_need_alter("sku_items", array("membership_package_unique_id"))){
				$con->sql_query_false("alter table sku_items add membership_package_unique_id int not null default 0, add index membership_package_unique_id (membership_package_unique_id)", true);
			}
			
			$con->sql_query("create table if not exists membership_package (
				branch_id int not null default 0,
				id int not null default 0,
				unique_id int not null default 0,
				doc_no char(30) not null,
				title char(100) not null,
				valid_from date,
				valid_to date,
				remark text,
				allowed_branches text,
				link_sku_item_id int not null default 0,
				total_entry_earn int not null default 0,
				user_id int not null default 0,
				active tinyint(1) not null default 1,
				status tinyint(1) not null default 0,
				added timestamp default 0, 
				confirm_timestamp timestamp default 0, 
				cancel_reason char(100) not null,
				cancelled_by int not null default 0,
				last_update timestamp default 0,
				primary key(branch_id, id),
				index title (title),
				index unique_id (unique_id),
				index doc_no (doc_no),
				index valid_from (valid_from),
				index valid_to (valid_to),
				index link_sku_item_id (link_sku_item_id),
				index active_status (active, status)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists membership_package_items (
				guid char(36) primary key,
				branch_id int not null default 0,
				package_id int not null default 0,
				package_unique_id int not null default 0,
				title char(100) not null,
				description char(200) not null,
				remark text,
				entry_need int not null default 0,
				max_redeem int not null default 0,
				sequence int not null default 0,
				index title (title),
				index description (description),
				index package_id_bid (package_id, branch_id),
				index package_unique_id (package_unique_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists memberships_purchased_package (
				guid char(36) primary key,
				card_no char(20) not null,
				ref_no char(20) not null,
				pos_branch_id int not null default 0,
				pos_receipt_no int not null default 0,
				pos_receipt_ref_no char(20) not null,
				date date,
				package_unique_id int not null default 0,
				qty int not null default 0,
				earn_entry int not null default 0,
				used_entry int not null default 0,
				remaining_entry int not null default 0,
				remark text,
				active tinyint(1) not null default 1,
				added timestamp default 0, 
				last_update timestamp default 0,
				index card_no (card_no),
				index ref_no (ref_no),
				index pos_receipt_no (pos_receipt_no),
				index pos_receipt_ref_no (pos_receipt_ref_no),
				index pos_branch_id (pos_branch_id),
				index package_unique_id (package_unique_id),
				index remaining_entry (remaining_entry),
				index active (active),
				index date (date)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists memberships_purchased_package_items (
				guid char(36) primary key,
				purchased_package_guid char(36) not null,
				membership_package_items_guid char(36) not null,
				title char(100) not null,
				description char(200) not null,
				remark text,
				entry_need int not null default 0,
				max_redeem int not null default 0,
				sequence int not null default 0,
				index purchased_package_guid (purchased_package_guid),
				index membership_package_items_guid (membership_package_items_guid)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists memberships_purchased_package_items_redeem (
				guid char(36) primary key,
				branch_id int not null default 0,
				purchased_package_items_guid char(36) not null,
				date date,
				used_entry int not null default 0,
				user_id int not null default 0,
				notify_member_to_rate tinyint(1) not null default 0,
				sa_info text,
				service_rating double not null default 0,
				overall_rating double not null default 0,
				added timestamp default 0, 
				last_update timestamp default 0,
				index purchased_package_items_guid (purchased_package_items_guid),
				index date (date),
				index notify_member_to_rate (notify_member_to_rate),
				index branch_id (branch_id),
				index added (added),
				index overall_rating (overall_rating)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists memberships_purchased_package_log (
				guid char(36) primary key,
				card_no char(20) not null,
				branch_id int not null default 0,
				user_id int not null default 0,
				purchased_package_guid char(36) not null,
				purchased_package_items_guid char(36) not null,
				log text,
				added timestamp default 0, 
				index card_no (card_no),
				index branch_id (branch_id),
				index user_id (user_id),
				index purchased_package_guid (purchased_package_guid),
				index purchased_package_items_guid (purchased_package_items_guid),
				index added (added)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists memberships_push_notification_history (
				guid char(36) primary key,
				branch_id int not null default 0,
				nric char(20) not null,
				title char(50) not null,
				message char(100) not null,
				device_sent int not null default 0,
				success_sent int not null default 0,
				more_info text,
				added timestamp default 0,				
				index nric (nric),
				index branch_id (branch_id),
				index title (title),
				index success_sent (success_sent),
				index added (added)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			if($this->check_need_alter("sa", array("photo_url"))){
				$con->sql_query_false("alter table sa add photo_url char(200) not null", true);
			}
			
			$this->update(416);
		}
		
		if($this->ver < 417){			
			$con->sql_query_false("update user set active=0 where u='arms_nava'", true);
			
			$this->update(417);
		}
		
		if($this->ver < 418){			
			if(!$_GET['force_maintenance'] && $starting_ver>410){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 10/25/2019 10:14 AM William - Membership Remark
			if($this->check_need_alter("membership", array("remark"))){
				$con->sql_query_false("alter table membership add column remark text",true);
			}
			
			$this->update(418);
		}
		
		if($this->ver < 419){			
			if(!$_GET['force_maintenance'] && $starting_ver>410){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 3 ==========================
			$tbl_list = array("sku_items_cost","sku_items_cost_history"); 
			foreach($tbl_list as $tbl_name){
				$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
			}
			unset($tbl_list);
			
			$this->update(419);
		}
		
		if($this->ver < 420){			
			$con->sql_query_false("update user set active=0 where u='arms_eekean'", true);
			
			$this->update(420);
		}
		
		if($this->ver < 421){			
			$con->sql_query_false("update user set active=0 where u='arms_andrew'", true);
			
			$this->setup_arms_user();
			
			$this->update(421);
		}
		
		if($this->ver < 422){			
			if(!$_GET['force_maintenance'] && $starting_ver>410){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
		
			// 11/8/2019 5:55 PM Justin
			$q1 = $con->sql_query_false("show tables like 'sa_sales_cache_b%'", true);
			while($t = $con->sql_fetchrow($q1)){
				$table = $t[0];
				if(!$table) continue;
				if($this->check_need_alter($table, array("use_commission_ratio"))){
					$con->sql_query_false("alter table $table add use_commission_ratio tinyint(1) default 0", true);
				}
			}
			$con->sql_freeresult($q1);
			
			// create new sales cache table for sales agent for commission by qty/sales range
			/*$q1 = $con->sql_query("select id from branch");
			while($r = $con->sql_fetchassoc($q1)){
				$bid = $r['id'];
				
				$con->sql_query("create table if not exists sa_range_sales_cache_b$bid (
					sa_id int(11),
					year int(4),
					month int(2),
					amount double,
					cost double,
					commission_amt double,
					qty double,
					transaction_count int(11) not null default 0,
					use_commission_ratio tinyint(1) default 0,
					primary key(sa_id,year,month),
					index(sa_id)) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci"
				);
			}
			$con->sql_freeresult($q1);*/
			// create in functions.php
			
			$this->update(422);
		}
		
		if($this->ver < 423){			
			if(!$_GET['force_maintenance'] && $starting_ver>410){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 8/21/2019 4:21 PM Justin - Marketplace - Manage SKU
			$con->sql_query("create table if not exists marketplace_sku_items(
				sku_item_id int not null primary key,
				active tinyint(1) not null default 1,
				added timestamp default 0,
				last_update timestamp default 0,
				index active (active)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
						
			$con->sql_query("create table if not exists marketplace_order(
				id int not null primary key auto_increment,
				order_no char(20) not null unique,
				order_date date not null,
				cust_name char(100) not null,
				cust_address char(200) not null,
				total_amount double not null default 0,
				total_qty double not null default 0,
				active tinyint(1) not null default 1,
				completed tinyint(1) not null default 0,
				api_data text,
				added timestamp default 0,
				last_update timestamp default 0,
				index active (active),
				index completed (completed)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists marketplace_order_do(
				id int not null primary key auto_increment,
				marketplace_order_id int not null,
				do_sequence int not null default 0,
				branch_id int not null default 0,
				do_id int not null default 0,
				shipping_fee double not null default 0,
				tracking_code char(50) not null,
				shipping_provider char(50) not null,
				total_do_amount double not null default 0,
				total_do_qty double not null default 0,
				discount double not null default 0,
				active tinyint(1) not null default 1,
				added timestamp default 0,
				last_update timestamp default 0,
				unique (marketplace_order_id, do_sequence),
				index branch_id_do_id (branch_id, do_id),
				index active (active)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists marketplace_order_do_items(
				id int not null primary key auto_increment,
				marketplace_order_id int not null,
				do_sequence int not null default 0,
				item_id int not null default 0,
				qty double not null default 0,
				unit_price double not null default 0,
				sku_item_id int not null default 0,
				active tinyint(1) not null default 1,
				added timestamp default 0,
				last_update timestamp default 0,
				unique (marketplace_order_id, do_sequence, item_id),
				index item_id (item_id),
				index sku_item_id (sku_item_id),
				index active (active)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists marketplace_order_cancel(
				id int not null primary key auto_increment,
				order_no char(20) not null,
				cancel_success tinyint(1) not null default 0,
				api_data text,
				added timestamp default 0,
				last_update timestamp default 0,
				index cancel_success (cancel_success)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
				
			$con->sql_query("create table if not exists marketplace_settings (
				setting_name char(100) not null,
				setting_value char(100) not null,
				last_update timestamp default 0,
				primary key(setting_name)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			// ========================== Batch 9 ==========================
			// alter sku_items_sales_cache and vendor_sku_history become InnoDB
			$tbl_list = array("sku_items_sales_cache_b");
			foreach($tbl_list as $tbl_name){
				$q1 = $con->sql_query_false("show tables like '".$tbl_name."%'", true);
				while($t = $con->sql_fetchrow($q1)){
					$table = $t[0];
					if(!$table) continue;
					$con->sql_query_false("ALTER TABLE $table ENGINE=InnoDB", true);
				}
				$con->sql_freeresult($q1);
			}
			unset($tbl_list);
			
			$this->update(423);
		}
		
		if($this->ver < 424){			
			if(!$_GET['force_maintenance'] && $starting_ver>420){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 10/25/2019 5:45 PM Andy - Time Attendance
			$con->sql_query("create table if not exists attendance_shift (
				id int not null primary key auto_increment,
				code char(10) not null,
				description char(100) not null,
				shift_color char(10) not null,
				start_time time not null default 0,
				end_time time not null default 0,
				break_1_start_time time not null default 0,
				break_1_end_time time not null default 0,
				break_2_start_time time not null default 0,
				break_2_end_time time not null default 0,
				active tinyint(1) not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				index code (code),
				index start_time (start_time),
				index end_time (end_time),
				index active (active)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists attendance_shift_user (
				branch_id int not null default 0,
				user_id int not null default 0,
				date date,
				y int not null default 0,
				m int not null default 0,
				shift_id int not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key (branch_id, user_id, date),
				index user_id (user_id),
				index date (date),
				index shift_id (shift_id),
				index ym (y, m)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists attendance_clock_sign (
				branch_id int not null default 0,
				counter_id int not null default 0,
				sign char(50),
				time timestamp,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key(branch_id, counter_id),
				index sign (sign),
				index time (time)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists attendance_user_daily_record (
				branch_id int not null default 0,
				user_id int not null default 0,
				date date,
				shift_id int not null,
				shift_code char(10) not null,
				shift_description char(100) not null,
				shift_color char(10) not null,
				start_time timestamp not null default 0,
				break_1_start_time timestamp not null default 0,
				break_1_end_time timestamp not null default 0,
				break_2_start_time timestamp not null default 0,
				break_2_end_time timestamp not null default 0,
				end_time timestamp not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key(branch_id, user_id, date),
				index user_id (user_id),
				index date (date),
				index shift_id (shift_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists attendance_user_scan_record (
				branch_id int not null default 0,
				user_id int not null default 0,
				date date,
				counter_id int not null default 0,
				scan_time timestamp default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key(branch_id, user_id, date, scan_time),
				index date (date),
				index user_id (user_id),
				index scan_time (scan_time)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(424);
		}
		
		if($this->ver < 425){			
			if(!$_GET['force_maintenance'] && $starting_ver>420){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 11/5/2019 2:09 PM William - Update all current BOM sku to outright.
			$con->sql_query_false("update sku set sku_type='OUTRIGHT' where is_bom=1 and sku_type=''",true);
			
			$this->update(425);
		}
		
		if($this->ver < 426){
			if(!$_GET['force_maintenance'] && $starting_ver>420){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// Marketplace
			$con->sql_query_false("create table if not exists `marketplace_login_data` (
				user_id int(11) not null default 0 primary key,
				code char(32) not null,
				added timestamp default 0,
				index code (code)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(426);
		}
		
		if($this->ver < 427){
			if(!$_GET['force_maintenance'] && $starting_ver>420){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 5 ==========================
			$tbl_list = array("sku_items_price", "sku_items_price_history");
			foreach($tbl_list as $tbl_name){
				$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
			}
			unset($tbl_list);
			
			// 11/26/2019 2:43 PM Justin - modify serial no to have 30 characters
			if($this->check_need_alter("pos_items_sn",array("serial_no"),array("serial_no"=>"char(30)"))){
				$con->sql_query_false("alter table pos_items_sn modify serial_no char(30)",true);
			}
			
			if($this->check_need_alter("sn_info",array("serial_no"),array("serial_no"=>"char(30)"))){
				$con->sql_query_false("alter table sn_info modify serial_no char(30)",true);
			}
			
			$this->update(427);
		}
		
		if($this->ver < 428){
			if(!$_GET['force_maintenance'] && $starting_ver>420){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 1.0 ==========================
			$tbl_list = array("sku_apply_items");
			foreach($tbl_list as $tbl_name){
				$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
			}
			unset($tbl_list);
			
			$this->update(428);
		}
		
		if($this->ver < 429){
			if(!$_GET['force_maintenance'] && $starting_ver>420){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// Membership
			$con->sql_query_false("create table if not exists `isms_history` (
					guid char(36) primary key,
					branch_id int not null default 0,
					user_id int not null default 0,
					member_card_no char(20) not null,
					mobile_num char(20) not null,
					message text not null,
					added timestamp default 0,
					last_update timestamp default 0,
					success tinyint(1) not null default 0,
					index branch_id (branch_id),
					index user_id (user_id),
					index member_card_no (member_card_no),
					index mobile_num (mobile_num),
					index success (success),			
					index added (added)
				) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
				
			$con->sql_query_false("create table if not exists `memberships_otp` (
					guid char(36) primary key,
					card_no char(20) not null,
					otp_code char(10) not null,
					branch_id int not null default 0,
					user_id int not null default 0,
					mobile_num char(20) not null,
					added timestamp default 0,
					last_update timestamp default 0,
					used tinyint(1) not null default 0,
					index branch_id (branch_id),
					index user_id (user_id),
					index card_no (card_no),
					index mobile_num (mobile_num),
					index used (used),			
					index added (added)
				) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
				
			//11/25/2019 9:39 AM William  - Add new column outlet_photo_url,operation_time,longitude,latitude to branch
			if($this->check_need_alter("branch", array("outlet_photo_url", "operation_time", "longitude", "latitude"))){
				$con->sql_query_false("Alter table branch
									add column outlet_photo_url char(200) not null,
									add column operation_time text not null,
									add column longitude char(20) not null,
									add column latitude char(20) not null
								", true);
			}
								
			
			// 11/28/2019 4:39 PM Andy - Coupon
			if($this->check_need_alter("coupon", array("member_limit_mobile_day_start", "member_limit_mobile_day_end", "member_limit_profile_info"))){
				$con->sql_query_false("alter table coupon 
					add member_limit_mobile_day_start int not null default 0, 
					add member_limit_mobile_day_end int not null default 0, 
					add member_limit_profile_info text not null", true);
			}
			
			// 12/5/2019 11:33 AM Andy - Membership Push Notification
			$con->sql_query_false("create table if not exists `memberships_pn` (
					guid char(36) primary key,
					branch_id int not null default 0,
					user_id int not null default 0,
					pn_title char(200) not null,
					pn_msg char(200) not null,
					screen_tag char(30) not null,
					active tinyint(1) not null default 0,
					completed tinyint(1) not null default 0,
					err_msg char(200) not null,
					added timestamp default 0,
					last_update timestamp default 0,
					index branch_id (branch_id),
					index user_id (user_id),
					index pn_title (pn_title),
					index pn_msg (pn_msg),
					index active (active),			
					index completed (completed),			
					index added (added),
					index err_msg (err_msg),
					index screen_tag (screen_tag)
				) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
				
			$con->sql_query_false("create table if not exists `memberships_pn_items` (
					guid char(36) primary key,
					memberships_pn_guid char(36) not null,
					nric char(20) not null,
					completed tinyint(1) not null default 0,
					success tinyint(1) not null default 0,
					added timestamp default 0,
					last_update timestamp default 0,
					index memberships_pn_guid (memberships_pn_guid),
					index nric (nric),
					index completed (completed),			
					index success (success),			
					index added (added)
				) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$this->update(429);
		}
		
		if($this->ver < 430){
			if(!$_GET['force_maintenance'] && $starting_ver>420){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 12/12/2019 5:44 PM Andy - Fixed sa wrong primary key
			$q1 = $con->sql_query_false("show index from sa where Key_name='primary'", true);
			if($con->sql_numrows($q1)>1){
				$con->sql_query_false("alter table sa drop primary key, add primary key (id)", true);
			}
			$con->sql_freeresult($q1);
			
			// ========================== Batch 1.1 ==========================
			$tbl_list = array("sku");
			foreach($tbl_list as $tbl_name){
				$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
			}
			unset($tbl_list);
			
			$this->update(430);
		}
		
		if($this->ver < 431){
			if(!$_GET['force_maintenance'] && $starting_ver>420){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 12/13/2019 10:28 AM William - Remove sku_group auto_increment and change engine to innoDB
			$con->sql_query_false("ALTER TABLE sku_group modify sku_group_id int(11) not null default 0", true);
			$con->sql_query_false("ALTER TABLE sku_group ENGINE=InnoDB", true);
			
			$this->update(431);
		}
		
		if($this->ver < 432){
			if(!$_GET['force_maintenance'] && $starting_ver>430){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 11/19/2019 4:39 PM Justin - Sales Agent KPI Table
			$con->sql_query_false("create table if not exists `sa_kpi_items` (
					id int(11) not null,
					position_id int(11) not null,
					description text not null,
					additional_description text not null,
					scores double not null default 0,
					active tinyint(1) not null default 1,
					user_id int(11) not null,
					added timestamp default 0,
					last_update timestamp default 0,
					primary key (position_id, id),
					index user_id (user_id),
					index active (active)
					) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
					
			// 11/22/2019 4:30 PM Justin - Masterfile Sales Agent - Leader
			$con->sql_query_false("create table if not exists `sa_leader` (
					sa_id int(11) not null,
					sa_leader_id int(11) not null,
					primary key (sa_id, sa_leader_id),
					index sa_leader_id (sa_leader_id)
					) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
					
			$con->sql_query_false("create table if not exists `sa_position` (
					id int(11) not null,
					code char(30) not null,
					description text not null,
					active tinyint(1) not null default 1,
					user_id int(11) not null,
					added timestamp default 0,
					last_update timestamp default 0,
					primary key (id),
					index user_id (user_id),
					index active (active)
					) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query_false("create table if not exists `sa_kpi_rating` (
					sa_id int(11) not null,
					sa_leader_id int(11) not null,
					year int(4) not null,
					month int(2) not null,
					kpi_data text not null,
					status tinyint(1) not null default 0,
					added timestamp default 0,
					last_update timestamp default 0,
					primary key (sa_id, sa_leader_id, year, month),
					index sa_leader_id (sa_leader_id),
					index status (status)
					) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");

			$con->sql_query_false("create table if not exists log_sa (
					branch_id int,
					id int(11) not null,
					timestamp timestamp default 0,
					sa_id int,
					type char(30),
					rid int default 0,
					log text,
					primary key (branch_id, id),
					index timestamp (timestamp),
					index sa_id_n_type_n_rid (sa_id, type, rid),
					index type_n_rid_n_timetamp (type, rid, timestamp)
					) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");

			if($this->check_need_alter("sa",array("position_id"))){
				$con->sql_query_false("alter table sa add position_id int(11), add index position_id (position_id)",true);
			}
			
			$this->update(432);
		}
		
		if($this->ver < 433){
			if(!$_GET['force_maintenance'] && $starting_ver>430){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 12/13/2019 11:53 AM Justin - Counter Setup Information
			$con->sql_query_false("CREATE TABLE if not exists `pos_counter_collection_configuration` (
				branch_id int(11),
				counter_id int(11),
				pos_image_server text not null,
				hq_server text not null,
				masterfile_sync_server tinyint(1) default 0,
				sales_sync_server tinyint(1) default 0,
				sync_server text not null,
				sync_server_up_sales text not null,
				last_update timestamp default 0,
				primary key(branch_id, counter_id)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$this->update(433);
		}
		
		if($this->ver < 434){			
			$con->sql_query_false("update user set active=0 where u='arms_joe'", true);
			
			$this->update(434);
		}
		
		if($this->ver < 435){			
			if(!$_GET['force_maintenance'] && $starting_ver>430){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 12/6/2019 1:51 PM William - Create Category Sales Trend Table
			$con->sql_query_false("create table if not exists `category_sales_trend_cache` (
					category_id int not null default 0, 
					branch_id int not null default 0, 
					recent_month int(3) not null, 
					qty double not null default 0, 
					amount double not null default 0, 
					cost double not null default 0,
					primary key (branch_id, category_id, recent_month),
					index category_id (category_id),
					index recent_month (recent_month)
				) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			// 12/9/2019 3:27 PM Justin ARMS POS scan item with multiply quantity 
			// - add privilege to all users who has POS_LOGIN to use multply quantity feature.
			$q1 = $con->sql_query("select * from user_privilege where privilege_code = 'POS_LOGIN' order by user_id, branch_id");
			while($r = $con->sql_fetchassoc($q1)){
				$upd = array();
				$upd['user_id'] = $r['user_id'];
				$upd['branch_id'] = $r['branch_id'];
				$upd['privilege_code'] = "POS_SCAN_MULTIPLY_QTY";
				$upd['allowed'] = 1;
				
				// insert new privilege "POS_SCAN_MULTIPLY_QTY" for users who has "POS_LOGIN" privilege
				$con->sql_query_false("replace into user_privilege ".mysql_insert_by_field($upd),true);
				unset($upd);
				
				// update the timestamp for table "user" to let it sync to all counters
				$con->sql_query_false("update user set last_update = CURRENT_TIMESTAMP where id = ".mi($r['user_id']));
			}
			$con->sql_freeresult($q1);
			
			$this->update(435);
		}
		
		if($this->ver < 436){			
			if(!$_GET['force_maintenance'] && $starting_ver>430){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 5/21/2018 5:30 PM Justin
			// list of tables to be alter become InnoDB
			// ========================== Batch 1.2 ==========================
			$tbl_list = array("sku_items");
			foreach($tbl_list as $tbl_name){
				// need to drop fulltext index for sku_items
				if($tbl_name == "sku_items"){
					$con->sql_query_false("ALTER TABLE $tbl_name drop index description_2");
				}
				$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
			}
			unset($tbl_list);
			
			$this->update(436);
		}
		
		if($this->ver < 437){			
			if(!$_GET['force_maintenance'] && $starting_ver>430){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			//12/17/2019 3:57 PM William - Enhanced to Change company_no to char(30)
			if($this->check_need_alter("branch", array("company_no"),array("company_no"=>"char(30)"))){
				$con->sql_query_false("alter table branch modify company_no char(30) not null", true);
			}
			if($this->check_need_alter("vendor", array("company_no"),array("company_no"=>"char(30)"))){
				$con->sql_query_false("alter table vendor modify company_no char(30) not null", true);
			}
			if($this->check_need_alter("debtor", array("company_no"),array("company_no"=>"char(30)"))){
				$con->sql_query_false("alter table debtor modify company_no char(30) not null", true);
			}
			
			// ========================== Batch 11.1 ==========================
			// alter all sales cache and stock balance to InnoDB - upload again once functions.php uploaded
			$tbl_list = array("pwp_sales_cache_b", "dept_trans_cache_b", "sales_target_b", "daily_sales_cache_b", "sa_sales_cache_b", "sa_range_sales_cache_b", "stock_balance_b");
			foreach($tbl_list as $tbl_name){
				$q1 = $con->sql_query_false("show tables like '".$tbl_name."%'", true);
				while($t = $con->sql_fetchrow($q1)){
					$table = $t[0];
					if(!$table) continue;
					
					if(!$this->is_innodb($table)){
						$con->sql_query_false("ALTER TABLE $table ENGINE=InnoDB", true);
					}
				}
				$con->sql_freeresult($q1);
			}
			unset($tbl_list);
			
			$this->update(437);
		}
		
		if($this->ver < 438){			
			if(!$_GET['force_maintenance'] && $starting_ver>430){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			//12/17/2019 9:53 AM William  - Remove sku_items_po_reorder id and add moq qty.
			if($con->sql_query_false("select id from sku_items_po_reorder limit 1", false)){
				$con->sql_query_false("create table tmp_sku_items_po_reorder (select * from sku_items_po_reorder)", true);
				$con->sql_query_false("drop table if exists sku_items_po_reorder ", true);
				$con->sql_query_false("create Table if not exists sku_items_po_reorder(
						branch_id int(11) not null default 0, 
						sku_item_id int(11) not null default 0, 
						user_id int(11) not null, 
						min_qty double not null, 
						max_qty double not null, 
						moq_qty double not null,
						notify_user_id int(11) not null, 
						added timestamp not null default 0, 
						last_update timestamp not null default 0,
						primary key (branch_id, sku_item_id),
						index branch_id (branch_id),
						index sku_item_id (sku_item_id),
						index user_id (user_id)
					) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
				//$con->sql_query_skip_logbin("Insert ignore into sku_items_po_reorder (branch_id, sku_item_id, user_id, min_qty, max_qty, notify_user_id, added, last_update) select branch_id, sku_item_id, user_id, min_qty, max_qty, notify_user_id, added, last_update from tmp_sku_items_po_reorder order by last_update desc");
				
				$q1 = $con->sql_query("select * from tmp_sku_items_po_reorder order by last_update desc");
				while($r = $con->sql_fetchassoc($q1)){
					$upd = array();
					$upd['branch_id'] = $r['branch_id'];
					$upd['sku_item_id'] = $r['sku_item_id'];
					$upd['user_id'] = $r['user_id'];
					$upd['min_qty'] = $r['min_qty'];
					$upd['max_qty'] = $r['max_qty'];
					$upd['notify_user_id'] = $r['notify_user_id'];
					$upd['added'] = $r['added'];
					$upd['last_update'] = $r['last_update'];
					$con->sql_query("insert ignore into sku_items_po_reorder ".mysql_insert_by_field($upd));
				}
				$con->sql_freeresult($q1);
			}
			
			// 12/12/2019 10:44 AM Andy - Clear Timestamp Auto Update
			$con->sql_query_false("alter table log modify timestamp timestamp not null default 0", true);
			
			// 1/3/2020 1:15 PM Justin - Fix sabasun stock take report Use Pre Stock Take slowness issue
			$con->sql_query_false("alter table stock_take_pre add index `branch_sku_stock_date` (`branch_id`,`sku_item_id`,`date`)");
			
			// 1/9/2020 5:57 PM Andy - WMS API
			$con->sql_query_false("ALTER TABLE do_request_items add index added (added)");
			
			// 1/15/2020 1:56 PM Andy - view log
			$con->sql_query_false("alter table user add index template (template)");
			
			// 12/26/2019 4:14 PM William  - Enhanced to add new membership_guid to membership, membership_points and membership_history table
			if($this->check_need_alter('membership', array("membership_guid"))){
				$con->sql_query_false("alter table membership add column membership_guid char(36) not null first, add index membership_guid (membership_guid)",true);
			}
			
			if($this->check_need_alter('membership_points', array("membership_guid"))){
				$con->sql_query_false("alter table membership_points add column membership_guid char(36) not null first, add index membership_guid (membership_guid)",true);
			}
			if($this->check_need_alter('membership_history', array("membership_guid"))){
				$con->sql_query_false("alter table membership_history add column membership_guid char(36) not null after id, add index membership_guid (membership_guid)",true);
			}
			
			$this->update(438);
		}
		
		if($this->ver < 439){			
			if(!$_GET['force_maintenance'] && $starting_ver>430){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 12/20/2019 3:38 PM Andy - Time Attendance
			$con->sql_query_false("create table if not exists attendance_ph (
				id int not null primary key auto_increment,
				code char(10) not null,
				description char(100) not null,
				active tinyint(1) not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				index code (code),
				index active (active)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$con->sql_query_false("create table if not exists attendance_ph_year (
				id int not null primary key auto_increment,
				y int not null unique default 0,
				added timestamp default 0,
				last_update timestamp default 0
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$con->sql_query_false("create table if not exists attendance_ph_year_items (
				id int not null primary key auto_increment,
				ph_year_id int not null default 0,
				ph_id int not null default 0,
				date_from date,
				date_to date,
				show_in_report tinyint(1) not null default 0,
				index ph_year_id (ph_year_id),
				index ph_id (ph_id),
				index date_from (date_from),
				index date_to (date_to),
				index show_in_report (show_in_report)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$con->sql_query_false("create table if not exists attendance_leave (
				id int not null primary key auto_increment,
				code char(10) not null,
				description char(100) not null,
				active tinyint(1) not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				index code (code),
				index active (active)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$con->sql_query_false("create table if not exists attendance_user_leave_record (
				guid char(36) primary key,
				user_id int not null default 0,
				branch_id int not null default 0,
				leave_id int not null default 0,
				date_from date,
				date_to date,
				active tinyint(1) not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				index user_id (user_id),
				index branch_id (branch_id),
				index active (active),
				index date_from (date_from),
				index date_to (date_to)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			if($this->check_need_alter('attendance_user_scan_record', array("ip"))){
				$con->sql_query_false("alter table attendance_user_scan_record add column ip char(15) not null, add index ip (ip)", true);
			}
			
			$con->sql_query_false("create table if not exists attendance_user_daily_record_modify_history (
				guid char(36) primary key,
				branch_id int not null default 0,
				user_id int not null default 0,
				date date,
				edit_by_user_id int not null default 0,
				is_new tinyint(1) not null default 0,
				is_deleted tinyint(1) not null default 0,
				added timestamp default 0,
				odata text,
				ndata text,
				index branch_id (branch_id),
				index user_id (user_id),
				index date (date),
				index added (added),
				index is_new (is_new),
				index is_deleted (is_deleted),
				index edit_by_user_id (edit_by_user_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$con->sql_query_false("create table if not exists attendance_user_scan_record_modify_history (
				guid char(36) primary key,
				odata_guid char(36) not null,
				branch_id int not null default 0,
				user_id int not null default 0,
				date date,
				oscan_time timestamp default 0,
				oip char(15) not null,
				nscan_time timestamp default 0,
				nip char(15) not null,
				is_new tinyint(1) not null default 0,
				is_deleted tinyint(1) not null default 0,
				edit_by_user_id int not null default 0,
				added timestamp default 0,
				index date (date),
				index user_id (user_id),
				index branch_id (branch_id),
				index added (added),
				index odata_guid (odata_guid),
				index edit_by_user_id (edit_by_user_id),
				index is_new (is_new),
				index is_deleted (is_deleted),
				index oip (oip),
				index nip (nip)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$this->update(439);
		}
		
		if($this->ver < 440){			
			if(!$_GET['force_maintenance'] && $starting_ver>430){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 1/16/2020 1:45 PM Justin - POS Membership GUID			
			if($this->check_need_alter("pos", array("membership_guid"))){
				$con->sql_query_false("alter table pos add membership_guid char(36) not null, add index membership_guid (membership_guid)", true);
			}
			
			$this->update(440);
		}
		
		if($this->ver < 441){			
			if(!$_GET['force_maintenance'] && $starting_ver>430){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			//1/30/2020 10:38 AM William  - add new column user department
			if($this->check_need_alter('user', array("user_dept"))){
				$con->sql_query_false("ALTER TABLE user add column user_dept char(100) not null after position", true);
			}
			
			// 2/6/2020 1:07 PM Andy - User Profile Photo
			if($this->check_need_alter('user', array("profile_photo_url"))){
				$con->sql_query_false("alter table user add profile_photo_url char(200) not null", true);
			}
			
			// ========================== Batch 13a ==========================
			// Other tables
			$tbl_list = array("api_do", "approval_flow", "approval_history", "approval_history_items", "approval_order", "arms_api_settings", "atp_promotion_table");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			$this->update(441);
		}
		
		if($this->ver < 442){			
			if(!$_GET['force_maintenance'] && $starting_ver>430){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			//2/11/2020 11:54 AM William  - add new column user department
			if($this->check_need_alter('user_draft', array("user_dept"))){
				$con->sql_query_false("ALTER TABLE user_draft add column user_dept char(100) not null after position", true);
			}
			
			$this->update(442);
		}
		
		if($this->ver < 443){			
			if(!$_GET['force_maintenance'] && $starting_ver>430){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 13b ==========================
			// Other tables
			$tbl_list = array("bank_interest", "bom_items", "branch", "branch_additional_sp", "branch_extra_info", "branch_group", "branch_group_items", "branch_region_additional_sp", "branch_status", "branch_trade_discount", "branch_vendor", "brand", "brand_brgroup", "brgroup", "brand_commission", "brand_commission_history");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			$this->update(443);
		}
		
		if($this->ver < 444){			
			if(!$_GET['force_maintenance'] && $starting_ver>440){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			if($this->check_need_alter('attendance_user_scan_record_modify_history', array("ocounter_id"))){
				$con->sql_query_false("alter table attendance_user_scan_record_modify_history add column ocounter_id int not null default 0, add index ocounter_id (ocounter_id)", true);
			}
			
			$this->update(444);
		}
		
		if($this->ver < 445){			
			if(!$_GET['force_maintenance'] && $starting_ver>440){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 11.2 ==========================
			// alter all sales cache and stock balance to InnoDB - upload again once functions.php uploaded
			$tbl_list = array("sku_items_sales_cache_b", "category_sales_cache_b", "member_sales_cache_b");
			foreach($tbl_list as $tbl_name){
				$q1 = $con->sql_query_false("show tables like '".$tbl_name."%'", true);
				while($t = $con->sql_fetchrow($q1)){
					$table = $t[0];
					if(!$table) continue;
					if(!$this->is_innodb($table)){
						$con->sql_query_false("ALTER TABLE $table ENGINE=InnoDB", true);
					}
				}
				$con->sql_freeresult($q1);
			}
			unset($tbl_list);
			
			// ========================== Batch 11.3 ==========================
			// alter vendor_sku_history_b to InnoDB - upload again once functions.php uploaded
			$tbl_list = array("vendor_sku_history_b");
			foreach($tbl_list as $tbl_name){
				$q1 = $con->sql_query_false("show tables like '".$tbl_name."%'", true);
				while($t = $con->sql_fetchrow($q1)){
					$table = $t[0];
					if(!$table) continue;
					if(!$this->is_innodb($table)){
						$con->sql_query_false("ALTER TABLE $table ENGINE=InnoDB", true);
					}
				}
				$con->sql_freeresult($q1);
			}
			unset($tbl_list);
			
			// ========================== Batch 13d ==========================
			// Other tables
			$tbl_list = array("debtor");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			$this->update(445);
		}
		
		if($this->ver < 446){			
			if(!$_GET['force_maintenance'] && $starting_ver>440){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 2/28/2020 11:25 AM William - Enhanced to added new column marketplace_description
			if($this->check_need_alter('sku_items', array("marketplace_description"))){
				$con->sql_query_false("alter table sku_items add column marketplace_description text not null after internal_description", true);
			}
			if($this->check_need_alter('sku_apply_items', array("marketplace_description"))){
				$con->sql_query_false("alter table sku_apply_items add column marketplace_description text not null after internal_description", true);
			}
			
			$this->update(446);
		}
		
		if($this->ver < 447){			
			if(!$_GET['force_maintenance'] && $starting_ver>440){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
						
			// ========================== Batch 12 ==========================
			// All Logs
			$tbl_list = array("log", "log_vp", "log_dp");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					// need to drop fulltext index
					$q1 = $con->sql_query_false("show index from $tbl_name where Index_type='FULLTEXT'", true);
					while($r = $con->sql_fetchassoc($q1)){
						// Drop index
						$con->sql_query_false("ALTER TABLE $tbl_name drop index ".$r['Key_name'], true);
						$con->sql_query_false("ALTER TABLE $tbl_name add index ".$r['Column_name']." (".$r['Column_name']."(200))", true);
					}
					$con->sql_freeresult($q1);
					
					// Remove auto_increment from ID
					$con->sql_query_false("ALTER TABLE $tbl_name modify id int not null default 0", true);
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
				}
			}
			unset($tbl_list);
			
			$this->update(447);
		}
		
		if($this->ver < 448){			
			if(!$_GET['force_maintenance'] && $starting_ver>440){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
		
			// 2/12/2020 5:11 PM Andy - Coupon Referral Program
			if($this->check_need_alter('coupon', array("referrer_coupon_get", "referrer_count_need", "referee_coupon_get", "referee_day_limit"))){
				$con->sql_query_false("alter table coupon add column referrer_coupon_get int not null default 0, add column referrer_count_need int not null default 0, add column referee_coupon_get int not null default 0, add column referee_day_limit int not null default 0, add index referrer_coupon_get (referrer_coupon_get), add index referee_coupon_get (referee_coupon_get)", true);
			}
			
			if($this->check_need_alter('coupon_items_member', array("referrer_count", "referrer_max_use", "referee_max_use"))){
				$con->sql_query_false("alter table coupon_items_member add column referrer_count int not null default 0, add column referrer_max_use int not null default 0, add column referee_max_use int not null default 0", true);
			}
			
			if($this->check_need_alter('membership', array("referral_code", "referral_code_generate_time", "refer_by_referral_code", "refer_by_added"))){
				$con->sql_query_false("alter table membership add column referral_code char(20) not null, add column referral_code_generate_time timestamp not null, add column refer_by_referral_code char(20) not null, add column refer_by_added timestamp not null, add index referral_code (referral_code), add index refer_by_referral_code (refer_by_referral_code), add index referral_code_generate_time (referral_code_generate_time), add index refer_by_added (refer_by_added)", true);
			}
			
			$con->sql_query_false("create table if not exists memberships_referral_history (
				guid char(36) primary key,
				referee_membership_guid char(36) not null,
				referrer_membership_guid char(36) not null,
				referral_code char(20) not null,
				added timestamp not null default 0,
				index added (added),
				index referee_membership_guid (referee_membership_guid),
				index referrer_membership_guid (referrer_membership_guid),
				index referral_code (referral_code)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$con->sql_query_false("create table if not exists coupon_items_member_referral_history (
				guid char(36) primary key,
				memberships_referral_history_guid char(36) not null,
				coupon_code char(20) not null,
				added timestamp not null default 0,
				index added (added),
				index memberships_referral_history_guid (memberships_referral_history_guid),
				unique (coupon_code, memberships_referral_history_guid)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$this->update(448);
		}
		
		if($this->ver < 449){			
			if(!$_GET['force_maintenance'] && $starting_ver>440){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
		
			// ========================== Batch 13e ==========================
			// Other tables
			$tbl_list = array("ExportAccSchedule", "ExportAccSettings");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
				}
			}
			unset($tbl_list);
			
			// ========================== Batch 13f ==========================
			// Other tables
			$tbl_list = array("foreign_currency_rate", "foreign_currency_rate_history", "foreign_currency_rate_history_record");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
				}
			}
			unset($tbl_list);
			
			// ========================== Batch 13g ==========================
			// Other tables
			$tbl_list = array("gpm_broadcast_msg", "gpm_broadcast_trade_offer", "gpm_broadcast_trade_offer_items", "gpm_broadcast_trade_offer_summary", "gpm_broadcast_trade_offer_summary_items", "gst", "gst_interbranch");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
				}
			}
			unset($tbl_list);
			
			// ========================== Batch 13l ==========================
			// Other tables
			$tbl_list = array("login_tickets");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB", true);
				}
			}
			unset($tbl_list);
			
			$this->update(449);
		}
		
		if($this->ver < 450){			
			if(!$_GET['force_maintenance'] && $starting_ver>440){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
						
			// ========================== Batch 13w ==========================
			// Other tables
			$tbl_list = array("web_bridge_ap_settings", "web_bridge_ar_settings", "web_bridge_cc_settings");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			// ========================== Batch 13r ==========================
			// Other tables
			$tbl_list = array("report_server", "ri", "rpt_group");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			$this->update(450);
		}
		
		if($this->ver < 451){			
			if(!$_GET['force_maintenance'] && $starting_ver>440){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
						
			//2/27/2020 9:15 AM William - Added new table for custom report
			$con->sql_query_false("Create table if not exists custom_report(
				id int(11) not null primary key auto_increment,
				report_title char(100) not null,
				report_group char(50) not null,
				user_id int(11) not null,
				report_shared tinyint(1) not null default 0,
				report_shared_additional_control_user text not null,
				page_filter text not null,
				report_fields text not null,
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				index user_id (user_id),
				index report_shared (report_shared),
				index report_group (report_group)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$this->update(451);
		}
		
		if($this->ver < 452){			
			if(!$_GET['force_maintenance'] && $starting_ver>450){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 13u ==========================
			// Other tables
			$tbl_list = array("uom", "user", "user_privilege", "user_status");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			$this->update(452);
		}
		
		if($this->ver < 453){			
			if(!$_GET['force_maintenance'] && $starting_ver>450){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 13.2m ==========================
			// Other tables
			$tbl_list = array("monthly_report_list", "mst_staff_quota", "mst_staff_quota_history", "mst_voucher_batch", "mst_voucher_setup");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			$this->update(453);
		}
		
		if($this->ver < 454){			
			if(!$_GET['force_maintenance'] && $starting_ver>450){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 13.1m ==========================
			// Other tables
			$tbl_list = array("membership", "membership_extra_info", "membership_points");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			$this->update(454);
		}
		
		if($this->ver < 455){
			if(!$_GET['force_maintenance'] && $starting_ver>450){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			$this->setup_arms_user();
			
			$con->sql_query_false("update user set active=0 where u='arms_aaron'", true);
			
			$this->update(455);
		}
		
		if($this->ver < 456){
			if(!$_GET['force_maintenance'] && $starting_ver>450){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 4/9/2020 6:02 PM William  - add new active, status
			if($this->check_need_alter("custom_report", array("active", "status"))){
				$con->sql_query_false("alter table custom_report add active tinyint(1) not null default 1 after report_fields,add status tinyint(1) not null default 0 after active, add index active (active), add index (status)",true);
			}
			
			$this->update(456);
		}
		
		if($this->ver < 457){
			if(!$_GET['force_maintenance'] && $starting_ver>450){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			//3/23/2020 9:15 AM William - add new mkt_inv_no, is_mkt column to do 
			if($this->check_need_alter('do', array("mkt_inv_no", "is_mkt"))){
				$con->sql_query_false("alter table do add column mkt_inv_no char(30) not null, add column is_mkt tinyint(1) not null default 0", true);
			}
			if($this->check_need_alter('marketplace_order_do', array("mkt_inv_no"))){
				$con->sql_query_false("alter table marketplace_order_do add column mkt_inv_no char(30) not null after discount", true);
			}
			if($this->check_need_alter('marketplace_order', array("marketplace_name"))){
				$con->sql_query_false("alter table marketplace_order add column marketplace_name char(50) not null after cust_address", true);
			}
			
			$this->update(457);
		}
		
		if($this->ver < 458){
			if(!$_GET['force_maintenance'] && $starting_ver>450){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 13p ==========================
			// Other tables
			$tbl_list = array("pivot_table", "pivot_table_sc", "pmos_do", "pmos_settings", "pmos_sku_items", "pos_counter_collection", "pos_error", "pos_items_changes", "pos_receipt_cancel", "pos_transaction_sync_server_counter_tracking", "pos_transaction_sync_server_tracking", "privilege", "privilege_master");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			// 5/5/2020 4:11 PM Andy - Remove Unique from User IC
			$con->sql_query("show index from user where Key_name='ic_no' and Non_unique=0");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			if($tmp){
				$con->sql_query("alter table user drop index ic_no");
				$con->sql_query("alter table user add index ic_no(ic_no)");
			}
			
			$this->update(458);
		}
		
		if($this->ver < 459){
			if(!$_GET['force_maintenance'] && $starting_ver>450){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 13v ==========================
			// Other tables
			$tbl_list = array("vendor", "vendor_commission", "vendor_commission_history", "vendor_portal_branch_info", "vendor_portal_info", "vendor_sku", "vendortype", "voucher_banker_details", "voucher_damage_cheque");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			$this->update(459);
		}
		
		if($this->ver < 460){
			$this->mark_start_maintenance();
			
			$con->sql_query_false("update user set active=0 where u='arms_gwc'", true);
			$con->sql_query_false("update user set active=0 where u='arms_yctan'", true);
			$con->sql_query_false("update user set active=0 where u='arms_justin'", true);
			$con->sql_query_false("update user set active=0 where u='arms_xinhui'", true);
			
			$this->update(460);
		}
		
		if($this->ver < 461){
			if(!$_GET['force_maintenance'] && $starting_ver>450){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 13t ==========================
			// Other tables
			$tbl_list = array("trade_discount_type", "transporter", "transporter_area", "transporter_driver", "transporter_route", "transporter_route_area", "transporter_type", "transporter_vehicle", "transporter_vehicle_brand", "transporter_vehicle_status", "transporter_vehicle_type");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			$this->update(461);
		}
		
		if($this->ver < 462){
			if(!$_GET['force_maintenance'] && $starting_ver>450){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 13s ==========================
			// Other tables
			$tbl_list = array("sa", "sa_sales_cache_monitoring", "shift_record", "sku_items_debtor_price", "sku_items_debtor_price_history", "sku_items_temp_price", "sku_items_temp_price_history", "sku_items_vendor_quotation_cost", "sku_items_vendor_quotation_cost_history", "sql_slaves", "staff_quota_used_history", "stock_balance_latest_entry", "stock_check", "sys_setting");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			$this->update(462);
		}
		
		if($this->ver < 463){
			if(!$_GET['force_maintenance'] && $starting_ver>450){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 6/11/2020 2:51 PM Andy - SKU Finalised Cost
			$con->sql_query_false("create table if not exists sku_items_finalised_cache (
				branch_id int not null default 0,
				date date,
				sku_item_id int not null default 0,
				unit_cost double not null default 0,
				primary key(branch_id, date, sku_item_id),
				index date (date),
				index sku_item_id (sku_item_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
						
			$this->update(463);
		}
		
		if($this->ver < 464){
			if(!$_GET['force_maintenance'] && $starting_ver>460){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// ========================== Batch 13.2c ==========================
			// Other tables
			$tbl_list = array("co2", "co2_items", "compare_sb", "config_master", "consignment_currency_table", "consignment_currency_table_history", "consignment_forex", "consignment_forex_history", "consignment_report", "consignment_report_export_history", "consignment_report_page_info", "consignment_report_sku", "consignment_transporter", "consignment_transporter_history", "counter_inventory", "csa_report", "custom_acc_export_acc_setting", "custom_acc_export_gst_setting", "custom_acc_export_templates");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			$con->sql_query_false("alter table ostrio_cs_mapping drop primary key, add primary key(branch_id, doc_no, cs_type)", true);
			
			$this->update(464);
		}
		
		if($this->ver < 465){
			if(!$_GET['force_maintenance'] && $starting_ver>460){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 7/2/2020 3:25 PM Andy - ARMS Suite Barcoder
			if($this->check_need_alter('suite_device', array("skip_dongle_checking"))){
				$con->sql_query_false("alter table suite_device add column skip_dongle_checking tinyint(1) not null default 0", true);
			}
			
			$this->update(465);
		}
		
		if($this->ver < 466){
			if(!$_GET['force_maintenance'] && $starting_ver>460){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 4/1/2020 3:15 PM William - Remove auto increment of ri_items table id and change the engine to innoDB
			if(!$this->is_innodb('ri_items')){
				$con->sql_query_false("ALTER TABLE ri_items modify id int(11) not null default 0", true);
				$con->sql_query_false("ALTER TABLE ri_items ENGINE=InnoDB", true);
			}
			
			
			
			// 7/13/2020 3:12 PM William - Added new column "additional_description_prompt_at_counter" to sku_items and sku_apply_items table.
			if($this->check_need_alter('sku_items', array("additional_description_prompt_at_counter"))){
				$con->sql_query_false("Alter table sku_items add column additional_description_prompt_at_counter tinyint(1) not null default 0", true);
			}
			
			if($this->check_need_alter('sku_apply_items', array("additional_description_prompt_at_counter"))){
				$con->sql_query_false("Alter table sku_apply_items add column additional_description_prompt_at_counter tinyint(1) not null default 0", true);
			}
			
			$this->update(466);
		}
		
		if($this->ver < 467){
			if(!$_GET['force_maintenance'] && $starting_ver>460){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 4/16/2020 11:33 AM William  -Added new table monthly_closing and monthly_closing_history
			$con->sql_query_false("create table if not exists monthly_closing (
				id int(11) not null primary key auto_increment,
				year int(4) not null default 0,
				month int(2) not null default 0,
				closed tinyint(1) not null default 0,
				branch_data_calculated blob not null,
				data_calculated tinyint(1) not null default 0,
				user_id int(11) not null default 0,
				close_date timestamp not null default 0,
				last_update timestamp not null default 0,
				added timestamp not null default 0,
				index year (year),
				index month (month),
				index closed (closed),
				index data_calculated (data_calculated)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$con->sql_query_false("create table if not exists monthly_closing_history (
				id int(11) not null,
				monthly_closing_id int(11) not null,
				user_id int(11) not null,
				reason char(50) not null,
				type char(10) not null,
				added timestamp default 0,
				primary key(id),
				index monthly_closing_id (monthly_closing_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			// 7/13/2020 9:53 AM William - Added new column "payment_date", "payment_type", "remark" to do table.
			if($this->check_need_alter("do", array("payment_date", "payment_type", "payment_remark"))){
				$con->sql_query_false("alter table do add column payment_date date not null, add column payment_type char(100) not null, add column payment_remark text",true);
			}
			
			// ========================== Batch 13.1c ==========================
			// Other tables
			$tbl_list = array("category", "category_cache", "category_changed", "category_markup", "category_sales_cache");
			foreach($tbl_list as $tbl_name){
				if(!$this->is_innodb($tbl_name)){
					$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
				}
			}
			unset($tbl_list);
			
			$this->update(467);
		}
		
		if($this->ver < 468){
			if(!$_GET['force_maintenance'] && $starting_ver>460){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 6/18/2020 4:38 PM Andy - OSTrio AR Integration
			$con->sql_query("create table if not exists ostrio_ar_mapping (
				branch_id int not null default 0,
				doc_id int not null default 0,
				acc_doc_no char(30) not null,
				doc_date date not null,
				ar_type char(20) not null,
				amount double not null default 0,
				ostrio_ar_id int not null default 0,
				api_data text,
				last_update timestamp default 0,
				primary key (branch_id, ar_type, doc_id),
				index ar_type (ar_type),
				index ostrio_ar_id (ostrio_ar_id),
				index doc_date (doc_date),
				index acc_doc_no (acc_doc_no)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(468);
		}
		
		if($this->ver < 469){
			$this->mark_start_maintenance();
			
			$this->setup_arms_user();
						
			$this->update(469);
		}
		
		if($this->ver < 470){
			if(!$_GET['force_maintenance'] && $starting_ver>460){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			$this->mark_start_maintenance();
			
			// 3/27/2020 11:50 AM William - Remove auto increment of stock_take_pre table id and change the engine to innoDB
			if(!$this->is_innodb('stock_take_pre')){
				$con->sql_query_false("ALTER TABLE stock_take_pre modify id int(11) not null default 0", true);
				$con->sql_query_false("ALTER TABLE stock_take_pre ENGINE=InnoDB", true);
			}
			
			// 8/14/2020 9:00 AM William - Add new column to promotion
			if($this->check_need_alter('promotion', array("last_pop_card_print_settings"))){
				$con->sql_query_false("alter table promotion add column last_pop_card_print_settings text not null", true);
			}
			
			$this->update(470);
		}
		
		if($this->ver < 471){
			$this->mark_start_maintenance();
			
			$this->setup_arms_user();
			
			$this->update(471);
		}
		
		if($this->ver < 472){
			$this->mark_start_maintenance();
			
			$this->setup_arms_user();
			
			$this->update(472);
		}
		
		if($this->ver < 473){
			if(!$_GET['force_maintenance'] && $starting_ver>470){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			$this->setup_arms_user();
			
			$this->update(473);
		}
		
		if($this->ver < 474){
			if(!$_GET['force_maintenance'] && $starting_ver>470){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 10/13/2020 1:41 PM Andy - Membership eForm
			if($this->check_need_alter("membership", array("eform_registered", "eform_registered_time", "eform_registered_verify_code"))){
				$con->sql_query_false("alter table membership 
					add eform_registered tinyint(1) not null default 0, 
					add eform_registered_time timestamp not null default 0,
					add eform_registered_verify_code char(64),
					add index eform_registered (eform_registered),
					add index eform_registered_verify_code (eform_registered_verify_code)", true);
			}
			
			$this->update(474);
		}
		
		if($this->ver < 475){
			if(!$_GET['force_maintenance'] && $starting_ver>470){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			//9/29/2020 9:12 AM William - Add new column device_guid to counter_settings table
			if($this->check_need_alter('counter_settings', array("suite_device_guid"))){
				$con->sql_query_false("alter table counter_settings add column suite_device_guid char(36) not null, add index suite_device_guid (suite_device_guid)", true);
			}
			
			//9/29/2020 9:12 AM William - Add new column fnb_username to user table
			if($this->check_need_alter('user', array("fnb_username"))){
				$con->sql_query_false("alter table user add column fnb_username char(100) not null after departments, add index fnb_username (fnb_username)", true);
			}
			
			//10/7/2020 9:12 AM William - Add new column is_tax_registered to pos table
			if($this->check_need_alter('pos', array("is_tax_registered"))){
				$con->sql_query_false("alter table pos add column is_tax_registered tinyint(1) not null default 0, add index is_tax_registered (is_tax_registered)", true);
			}
			
			//10/7/2020 9:12 AM William - Add new table "tax"
			$con->sql_query_false("create table if not exists tax (
				id int not null primary key auto_increment,
				code char(30) not null,
				description char(200) not null,
				rate double not null default 0,
				indicator_receipt char(10) not null,
				active tinyint(1) not null default 1,
				tax_apply_to text,
				type char(30) not null,
				user_id int not null default 0,
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				index code (code)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			
			//10/13/2020 10:33 AM William - Add new table "tax_settings"
			$con->sql_query_false("create table if not exists tax_settings (
				setting_name char(100) not null primary key,
				setting_value text,
				last_update timestamp not null default 0
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$this->update(475);
		}
		
		if($this->ver < 476){
			if(!$_GET['force_maintenance'] && $starting_ver>470){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			//10/21/2020 4:36 PM William - Add new column qty to batch_barcode_items table
			if($this->check_need_alter('batch_barcode_items ', array("qty"))){
				$con->sql_query_false("alter table batch_barcode_items add column qty int not null default 1", true);
			}
			
			$this->update(476);
		}
		
		if($this->ver < 477){
			if(!$_GET['force_maintenance'] && $starting_ver>470){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			//11/10/2020 5:05 PM Shane - Front End Announcement
			$con->sql_query_false("create table if not exists pos_announcement (
				id int not null,
				branch_id int not null default 0,
				user_id int not null default 0,
				title char(200) not null,
				content text,
				announcement_branch_id text,
				date_from date,
				date_to date,
				time_from time,
				time_to time,
				allowed_day text,
				status tinyint(1) not null default 0,
				active tinyint(1) not null default 1,
				cancel_by int,
				cancelled timestamp default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key(branch_id, id),
				index user_id (user_id),
				index status (status),
				index title (title),
				index date_from (date_from),
				index date_to (date_to),
				index last_update (last_update),
				index active (active)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			// 1/7/2020 9:35 AM William - Remove auto_increment of adjustment tables id and change the engine to innoDB
			if(!$this->is_innodb('adjustment')){
				$con->sql_query_false("ALTER TABLE adjustment modify id int(11) not null default 0", true);
				$con->sql_query_false("ALTER TABLE adjustment ENGINE=InnoDB", true);
			}
			
			if(!$this->is_innodb('adjustment')){
				$con->sql_query_false("ALTER TABLE adjustment_items modify id int(11) not null default 0", true);
				$con->sql_query_false("ALTER TABLE adjustment_items ENGINE=InnoDB", true);
			}
			
			$this->update(477);
		}
		
		if($this->ver < 478){
			if(!$_GET['force_maintenance'] && $starting_ver>470){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			if($this->check_need_alter('sku_apply_items', array("use_rsp", "rsp_price", "rsp_discount"))){
				$con->sql_query_false("alter table sku_apply_items add use_rsp tinyint(1) not null default 0, add rsp_price double not null default 0, add rsp_discount char(15) not null, add index use_rsp (use_rsp)", true);
			}
			if($this->check_need_alter('sku_items', array("use_rsp", "rsp_price", "rsp_discount"))){
				$con->sql_query_false("alter table sku_items add use_rsp tinyint(1) not null default 0, add rsp_price double not null default 0, add rsp_discount char(15) not null, add index use_rsp (use_rsp)", true);
			}
			if($this->check_need_alter('sku_items_price', array("rsp_discount"))){
				$con->sql_query_false("alter table sku_items_price add rsp_discount char(15) not null", true);
			}
			if($this->check_need_alter('sku_items_price_history', array("rsp_discount"))){
				$con->sql_query_false("alter table sku_items_price_history add rsp_discount char(15) not null", true);
			}
			
			$this->update(478);
		}
		
		if($this->ver < 479){
			if(!$_GET['force_maintenance'] && $starting_ver>470){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			//12/3/2020 10:45 AM Shane - Add new column "announcement_branch_group_id" and "announcement_user_id" to pos_announcement
			if($this->check_need_alter('pos_announcement', array("announcement_branch_group_id", "announcement_counter_id", "announcement_user_id"))){
				$con->sql_query_false("
				alter table pos_announcement 
				add column announcement_branch_group_id text after content,
				add column announcement_counter_id text after announcement_branch_id,
				add column announcement_user_id text after announcement_counter_id
				", true);
			}
			
			$this->update(479);
		}
		
		if($this->ver < 480){
			if(!$_GET['force_maintenance'] && $starting_ver>470){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			$con->sql_query_false("update user set active=1 where u='arms_yctan'", true);
			
			$this->update(480);
		}
		
		if($this->ver < 481){
			if(!$_GET['force_maintenance'] && $starting_ver>475){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			//12/16/2020 1:18 PM William - Add new column "report_settings" to custom_report
			if($this->check_need_alter('custom_report', array("report_settings"))){
				$con->sql_query_false("alter table custom_report add column report_settings text not null after report_fields", true);
			}
			
			$this->update(481);
		}
		
		if($this->ver < 482){
			if(!$_GET['force_maintenance'] && $starting_ver>475){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 12/23/2020 4:52 PM Andy - Speed99 Vendor
			if($this->check_need_alter('vendor', array("delivery_type"))){
				$con->sql_query_false("alter table vendor add column delivery_type char(15) not null, add index delivery_type (delivery_type)", true);
			}
			
			if($this->check_need_alter('category', array("code"), array("code"=>"char(15)"))){
				$con->sql_query_false("alter table category modify code char(15)", true);
			}
			
			if($this->check_need_alter('branch', array("warehouse_number", "warehouse_name"))){
				$con->sql_query_false("alter table branch add warehouse_number char(30) not null, add warehouse_name char(100) not null", true);
			}
			
			$con->sql_query("create table if not exists speed99_cron_status (
				sync_type char(20) not null,
				sub_type char(20) not null,
				total_record int not null default 0,
				new_record int not null default 0,
				update_record int not null default 0,
				error_record int not null default 0,
				error_list text,
				status tinyint(1) not null default 0,
				start_time timestamp not null default 0,
				end_time timestamp not null default 0,
				primary key (sync_type, sub_type),
				index status (status)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query("create table if not exists speed99_branch_mapping (
				branch_id int not null primary key,
				outlet_code char(30) not null,
				index outlet_code (outlet_code)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(482);
		}
		
		if($this->ver < 483){
			if(!$_GET['force_maintenance'] && $starting_ver>475){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			//1/7/2021 5:09 PM Shane Add new table pos_day_start and pos_day_end
			$con->sql_query_false("create table if not exists pos_day_start (
				branch_id int not null default 0,
				counter_id int not null default 0,
				date date not null,
				user_id int,
				time time,
				primary key(branch_id, counter_id, date),
				index counter_id (counter_id),
				index date (date),
				index user_id (user_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");

			$con->sql_query_false("create table if not exists pos_day_end (
				branch_id int not null default 0,
				counter_id int not null default 0,
				date date not null,
				user_id int,
				time time,
				primary key(branch_id, counter_id, date),
				index counter_id (counter_id),
				index date (date),
				index user_id (user_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(483);
		}
		
		if($this->ver < 484){
			if(!$_GET['force_maintenance'] && $starting_ver>475){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			//1/15/2021 2:48 PM Shane modified pos_day_start.time and pos_day_end.time to timestamp type
			$con->sql_query_false("alter table pos_day_start modify time timestamp default 0");
			$con->sql_query_false("alter table pos_day_end modify time timestamp default 0");
			
			$this->update(484);
		}
		
		if($this->ver < 485){
			if(!$_GET['force_maintenance'] && $starting_ver>475){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 1/18/2021 11:25 AM William  Add new column "pmr" Patient Medical Record to membership table.
			if($this->check_need_alter("membership", array("pmr"))){
				$con->sql_query_false("alter table membership add column pmr text",true);
			}
			
			$this->update(485);
		}
		
		if($this->ver < 486){
			if(!$_GET['force_maintenance'] && $starting_ver>475){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			$con->sql_query("create table if not exists suite_pos_banner (
				banner_name char(100) not null,
				banner_description char(200) not null,
				screen_name char(50) not null,
				screen_description char(200) not null,
				sequence integer not null default 0,
				banner_width integer not null default 0,
				banner_height integer not null default 0,
				max_photo_count integer not null default 1,
				wireframe_url text,
				active tinyint(1) not null default 1,
				banner_info text,
				last_update timestamp not null default 0,
				primary key (banner_name),
				index screen_name (screen_name),
				index active (active)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");

			$upd = array();
			$upd['banner_name'] = 'slideshow_vertical';
			$upd['banner_description'] = 'Slideshow Screen Vertical Banner';
			$upd['screen_name'] = 'slideshow';
			$upd['screen_description'] = 'Slideshow Screen';
			$upd['sequence'] = 1;
			$upd['banner_width'] = 1080;
			$upd['banner_height'] = 1920;
			$upd['max_photo_count'] = 3;
			$upd['wireframe_url'] = 'ui/suite_pos_banner/slideshow_vertical.png';
			$con->sql_query_false("insert into suite_pos_banner ".mysql_insert_by_field($upd));
			
			$upd = array();
			$upd['banner_name'] = 'logo';
			$upd['banner_description'] = 'Logo photo';
			$upd['screen_name'] = 'logo';
			$upd['screen_description'] = 'Logo photo';
			$upd['sequence'] = 1;
			$upd['banner_width'] = 600;
			$upd['banner_height'] = 600;
			$upd['max_photo_count'] = 1;
			$upd['wireframe_url'] = 'ui/suite_pos_banner/logo.png';
			$con->sql_query_false("insert into suite_pos_banner ".mysql_insert_by_field($upd));
			
			$this->update(486);
		}
		
		if($this->ver < 487){
			if(!$_GET['force_maintenance'] && $starting_ver>480){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 1/25/2020 2:25 PM William - Change revision_type to char(100)
			if($this->check_need_alter("counter_status", array("revision_type"), array("revision_type"=>"char(100)"))){
				$con->sql_query_false("alter table counter_status modify revision_type char(100)",true);
			}
			
			// 1/27/2020 9:00 AM William - Added new column remark to sales_order_items and tmp_sales_order_items table.
			if($this->check_need_alter("sales_order_items", array("remark"))){
				$con->sql_query_false("alter table sales_order_items add remark text", true);
			}
			
			$this->update(487);
		}
		
		if($this->ver < 488){
			if(!$_GET['force_maintenance'] && $starting_ver>480){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 2/2/2021 4:44 PM Shane - Added new column got_pos_photo to category table.
			if($this->check_need_alter("category", array("got_pos_photo"))){
				$con->sql_query_false("alter table category  
								   add got_pos_photo tinyint(1) not null default 0, 
								   add index got_pos_photo (got_pos_photo)", true);
			}
			$this->update(488);
		}
		
		if($this->ver < 489){
			if(!$_GET['force_maintenance'] && $starting_ver>480){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			//3/2/2021 12:27 PM Shane - Add index to membership.phone_3
			$con->sql_query_false("alter table membership add index membership_phone_3 (phone_3)");
			
			$this->update(489);
		}
		
		if($this->ver < 490){
			if(!$_GET['force_maintenance'] && $starting_ver>480){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 03/01/2021 5:23 PM Rayleen - Add new table "eform_user"
			$con->sql_query("create table if not exists eform_user(
				id int not null primary key auto_increment,
				branch_id int not null default 0,
				template int not null default 0,
				username varchar(50) not null,
				ic_no varchar(20),
				fullname varchar(200) not null,
				position varchar(200) not null,
				login_id varchar(16) not null,
				password varchar(64) not null,
				email varchar(50) not null,
				status tinyint(1) not null default 0,
				approved_by int,
				approved_date timestamp default 0,
				added_by int,
				added timestamp default 0,
				last_update timestamp default 0,
				index branch_id (branch_id),
				index template (template),
				index username (username),
				index ic_no (ic_no),
				index login_id (login_id),
				index email (email),
				index status (status)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(490);
		}
		
		if($this->ver < 491){
			if(!$_GET['force_maintenance'] && $starting_ver>480){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 3/5/2021 1:50 PM Andy - Add index to branch
			$con->sql_query_false("alter table branch add index active (active)");
			
			// 3/10/2021 10:18 AM Rayleen - eform additional fields
			if($this->check_need_alter('eform_user', array("department", "photo", "address", "mobile_number", "resume", "remarks", "actual_user_id"))){
				$con->sql_query_false("alter table eform_user 
										add column department varchar(200),
										add column photo varchar(200), 
										add column address text not null,
										add column mobile_number varchar(20) not null,
										add column resume varchar(200),
										add column remarks text, 
										add column actual_user_id int not null default 0,
										add index actual_user_id (actual_user_id)",
								 true);
			}
			$this->update(491);
		}
		
		if($this->ver < 492){
			if(!$_GET['force_maintenance'] && $starting_ver>480){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 1/20/2021 1:12 PM Andy - Speed99 Integration
			$con->sql_query("create table if not exists speed99_warehouse (
				warehouse_number char(20) not null primary key,
				warehouse_name char(100) not null,
				added timestamp not null default 0,
				last_update timestamp not null default 0,				
				index warehouse_name (warehouse_name),
				index added (added),
				index last_update (last_update)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(492);
		}
		
		if($this->ver < 493){
			if(!$_GET['force_maintenance'] && $starting_ver>490){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 2/25/2021 3:32 PM Andy - Work Order
			if($this->check_need_alter('work_order', array("transfer_type", "final_cost_per_qty"))){
				$con->sql_query_false("alter table work_order 
					add column transfer_type char(10) not null default 'w2w', 
					add column final_cost_per_qty double not null default 0, 
					add index transfer_type (transfer_type)", true);
			}
			
			if($this->check_need_alter('work_order_items_in', array("uom_id", "actual_adj_qty"))){
				$con->sql_query_false("alter table work_order_items_in
					add column uom_id int not null default 0, 
					add column actual_adj_qty double not null default 0, 
					add index uom_id (uom_id)", true);
			}
			
			
			$this->update(493);
		}
		
		if($this->ver < 494){
			if(!$_GET['force_maintenance'] && $starting_ver>490){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 4/5/2021 3:50 PM Shane - Add "hide_at_pos" for category table
			if($this->check_need_alter('category', array("hide_at_pos"))){
				$con->sql_query_false("alter table category add column hide_at_pos tinyint(1) not null default 0, add index hide_at_pos (hide_at_pos)", true);
			}
			
			$this->update(494);
		}
		
		if($this->ver < 495){
			if(!$_GET['force_maintenance'] && $starting_ver>490){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			//4/12/2021 11:07 AM Shane - Add new column counter_pb_mode in counter_settings table
			if($this->check_need_alter('counter_settings', array("counter_pb_mode"))){
				$con->sql_query_false("alter table counter_settings
					add column counter_pb_mode tinyint(1) not null default 0,
					add index counter_pb_mode (counter_pb_mode)", true);
			}
			
			
			$this->update(495);
		}
		
		if($this->ver < 496){
			if(!$_GET['force_maintenance'] && $starting_ver>490){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 4/7/2021 10:25 AM Andy
			$con->sql_query("create table if not exists komaiso_cron_status (
				sync_type char(20) not null,
				sub_type char(20) not null,
				error_list mediumtext,
				status tinyint(1) not null default 0,
				start_time timestamp not null default 0,
				end_time timestamp not null default 0,
				primary key (sync_type, sub_type),
				index status (status)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			
			// 4/8/2021 1:21 PM William  -Added new suite_pos_banner_items table for kiosk slideshow
			$con->sql_query("create table if not exists suite_pos_banner_items(
				id int not null auto_increment primary key,
				banner_name char(100) not null,
				item_type char(20) not null,
				image_click_link char(200) not null,
				item_url char(200) not null,
				active tinyint(1) not null default 0,
				sequence int not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				index banner_name (banner_name),
				index item_type (item_type),
				index sequence (sequence),
				index active (active)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->update(496);
		}
		
		if($this->ver < 497){
			if(!$_GET['force_maintenance'] && $starting_ver>490){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			//3/13/2021 9:41 AM William - Added new payment type for ipay88 integration
			$upd = array();
			$upd['ewallet_type'] = 'ipay88';
			$upd['integrator_id'] = '234';
			$upd['integrator_type'] = 'alipay';
			$upd['integrator_logo_link'] = 'ui/ewallet-ipay88_alipay.png';
			$upd['added'] = $upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query_false("insert into ewallet_integrator_list ".mysql_insert_by_field($upd));

			$upd = array();
			$upd['ewallet_type'] = 'ipay88';
			$upd['integrator_id'] = '373';
			$upd['integrator_type'] = 'alipay_pre_auth';
			$upd['integrator_logo_link'] = 'ui/ewallet-ipay88_alipay_pre_auth.png';
			$upd['added'] = $upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query_false("insert into ewallet_integrator_list ".mysql_insert_by_field($upd));

			$upd = array();
			$upd['ewallet_type'] = 'ipay88';
			$upd['integrator_id'] = '320';
			$upd['integrator_type'] = 'boost';
			$upd['integrator_logo_link'] = 'ui/ewallet-ipay88_boost.png';
			$upd['added'] = $upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query_false("insert into ewallet_integrator_list ".mysql_insert_by_field($upd));

			$upd = array();
			$upd['ewallet_type'] = 'ipay88';
			$upd['integrator_id'] = '354';
			$upd['integrator_type'] = 'maybankqr';
			$upd['integrator_logo_link'] = 'ui/ewallet-ipay88_maybankqr.png';
			$upd['added'] = $upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query_false("insert into ewallet_integrator_list ".mysql_insert_by_field($upd));

			$upd = array();
			$upd['ewallet_type'] = 'ipay88';
			$upd['integrator_id'] = '329';
			$upd['integrator_type'] = 'mcash';
			$upd['integrator_logo_link'] = 'ui/ewallet-ipay88_mcash.png';
			$upd['added'] = $upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query_false("insert into ewallet_integrator_list ".mysql_insert_by_field($upd));

			$upd = array();
			$upd['ewallet_type'] = 'ipay88';
			$upd['integrator_id'] = '336';
			$upd['integrator_type'] = 'tng';
			$upd['integrator_logo_link'] = 'ui/ewallet-ipay88_tng.png';
			$upd['added'] = $upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query_false("insert into ewallet_integrator_list ".mysql_insert_by_field($upd));

			$upd = array();
			$upd['ewallet_type'] = 'ipay88';
			$upd['integrator_id'] = '338';
			$upd['integrator_type'] = 'unionpay';
			$upd['integrator_logo_link'] = 'ui/ewallet-ipay88_unionpay.png';
			$upd['added'] = $upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query_false("insert into ewallet_integrator_list ".mysql_insert_by_field($upd));

			$upd = array();
			$upd['ewallet_type'] = 'ipay88';
			$upd['integrator_id'] = '343';
			$upd['integrator_type'] = 'wechatpay_my';
			$upd['integrator_logo_link'] = 'ui/ewallet-ipay88_wechatpay_my.png';
			$upd['added'] = $upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query_false("insert into ewallet_integrator_list ".mysql_insert_by_field($upd));

			$upd = array();
			$upd['ewallet_type'] = 'ipay88';
			$upd['integrator_id'] = '305';
			$upd['integrator_type'] = 'wechatpay_cn';
			$upd['integrator_logo_link'] = 'ui/ewallet-ipay88_wechatpay_cn.png';
			$upd['added'] = $upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query_false("insert into ewallet_integrator_list ".mysql_insert_by_field($upd));

			$upd = array();
			$upd['ewallet_type'] = 'ipay88';
			$upd['integrator_id'] = '164';
			$upd['integrator_type'] = 'prestopay';
			$upd['integrator_logo_link'] = 'ui/ewallet-ipay88_prestopay.png';
			$upd['added'] = $upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query_false("insert into ewallet_integrator_list ".mysql_insert_by_field($upd));

			$upd = array();
			$upd['ewallet_type'] = 'ipay88';
			$upd['integrator_id'] = '379';
			$upd['integrator_type'] = 'grabpay';
			$upd['integrator_logo_link'] = 'ui/ewallet-ipay88_grabpay.png';
			$upd['added'] = $upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query_false("insert into ewallet_integrator_list ".mysql_insert_by_field($upd));

			$upd = array();
			$upd['ewallet_type'] = 'ipay88';
			$upd['integrator_id'] = '19';
			$upd['integrator_type'] = 'shopeepay';
			$upd['integrator_logo_link'] = 'ui/ewallet-ipay88_shopeepay.png';
			$upd['added'] = $upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query_false("insert into ewallet_integrator_list ".mysql_insert_by_field($upd));
			
			$this->update(497);
		}
		
		if($this->ver < 498){
			if(!$_GET['force_maintenance'] && $starting_ver>490){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			//4/22/2021 2:39 PM Shane - Added new column pos_day_end.eod_data
			if($this->check_need_alter('pos_day_end', array("eod_data"))){
				$con->sql_query_false("alter table pos_day_end add column eod_data text", true);
			}
			
			$this->update(498);
		}
		
		if($this->ver < 499){
			if(!$_GET['force_maintenance'] && $starting_ver>490){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 4/28/2021 9:13 AM Andy - SKU Items SMark
			$con->sql_query_false("create table if not exists sku_items_smark (
				sku_item_id int not null default 0,
				smark char(15) not null,
				primary key(sku_item_id, smark),
				index smark (smark)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			$this->update(499);
		}
		
		if($this->ver < 500){
			if(!$_GET['force_maintenance'] && $starting_ver>490){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 26/04/2021 5:27 PM Rayleen - Add new column "activated_by" to eform_user
			if($this->check_need_alter('eform_user', array("activated_by"))){
				$con->sql_query_false("alter table eform_user add column activated_by int not null default 0", true);
			}
			
			$this->update(500);
		}
		
		/*       
        if($this->ver < 22){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '1000'))   return false;
                // if not the right timing to run, return and dont process
			}

			///////////////// old //////////////////
			// 6/19/2012 9:22:08 AM dingren
			$con->sql_query_false("create table if not exists `eform` (
					id int(11) not null auto_increment,
	                user_id int(11) not null,
	                branch_id int(11) not null,
					title varchar(100) not null,
	                description text,
	                fields text,
					added timestamp default 0,
					last_update timestamp default 0,
					active tynyint(1) NOT NULL DEFAULT '0',
					deleted tinyint(1) default 0,
					primary key (branch_id,id)
					)");
					
			$con->sql_query_false("create table if not exists `eform_datas` (
					id int(11) not null auto_increment,
					user_id int(11) not null,
					branch_id int(11) not null,
					eform_id int(11) not null,
					approval_history_id int(11) null,
					title varchar(100) not null,
					description text,
					fields text,
					data text,
					status tinyint(1) not null default 0,
					approved tinyint(1) not null default 0,
					active tinyint(1) default 1,
					added timestamp default 0,
					last_update timestamp default 0,
					primary key (branch_id,id)
					)");
			
			
			////////////////////////////
			
			// ========================= for offline server ============================
			if($this->check_need_alter("do",array("offline_id"))){
				$con->sql_query_false("alter table do add offline_id int, add index offline_id_branch_id (offline_id, branch_id)",true);
			}

			if($this->check_need_alter("grr",array("offline_id"))){
				$con->sql_query_false("alter table grr add offline_id int(11), add index offline_id_branch_id (offline_id, branch_id)",true);
			}

			if($this->check_need_alter("grn",array("offline_id"))){
				$con->sql_query_false("alter table grn add offline_id int(11), add index offline_id_branch_id (offline_id, branch_id)",true);
			}
			
			if($this->check_need_alter("po",array("offline_id"))){
				$con->sql_query_false("alter table po add offline_id int(11), add index offline_id_branch_id (offline_id, branch_id)",true);
			}
			
			if($this->check_need_alter("gra",array("offline_id"))){
				$con->sql_query_false("alter table gra add offline_id int(11), add index offline_id_branch_id (offline_id, branch_id)",true);
			}
			
			if($this->check_need_alter("adjustment",array("offline_id"))){
				$con->sql_query_false("alter table adjustment add offline_id int(11), add index offline_id_branch_id (offline_id, branch_id)",true);
			}
			
			if($this->check_need_alter("sku",array("offline_id"))){
				$con->sql_query_false("alter table sku add offline_id int, add index offline_id_apply_branch_id (offline_id, apply_branch_id)",true);
			}
			
			// ========================= end of offline server ============================
			// initial plan is to use for vendor alternative contact, but now stopped
			if($this->check_need_alter("branch_vendor",array("grn_qty_no_over_po_qty", "allow_po_without_checkout_gra"))){
				$con->sql_query_false("alter table branch_vendor add grn_qty_no_over_po_qty tinyint(1) default 0, add allow_po_without_checkout_gra tinyint(1) default 1",true);
			}
			
			// going to drop in future, but must let all customer update their counter version first
			$con->sql_query_false("alter table sales_order_items drop gst_indicator");
			$con->sql_query_false("alter table tmp_sales_order_items drop gst_indicator");
			
			////////
			
			
			
			// ========================== Batch 14 ==========================
			// TMP tables
			$tbl_list = array("tmp_consignment_report", "tmp_cron_cost_history_info", "tmp_csa_report", "tmp_invalid_sku", "tmp_invalid_sku_history", "tmp_member_trigger", "tmp_member_trigger_log", "tmp_membership_points_trigger", "tmp_report_data_info", "tmp_trigger", "tmp_trigger_log");
			foreach($tbl_list as $tbl_name){
				$con->sql_query_false("ALTER TABLE $tbl_name ENGINE=InnoDB");
			}
			unset($tbl_list);
			
			//12/20/2019 10:16 AM Justin
			// GRR & GRN - Remove auto_increment from ID 
			$con->sql_query_false("ALTER TABLE grr modify id int not null default 0", true);
			$con->sql_query_false("ALTER TABLE grr ENGINE=InnoDB", true);
			$con->sql_query_false("ALTER TABLE grr_items modify id int not null default 0", true);
			$con->sql_query_false("ALTER TABLE grr_items ENGINE=InnoDB", true);
			$con->sql_query_false("ALTER TABLE grn modify id int not null default 0", true);
			$con->sql_query_false("ALTER TABLE grn ENGINE=InnoDB", true);
			$con->sql_query_false("ALTER TABLE grn_items modify id int not null default 0", true);
			$con->sql_query_false("ALTER TABLE grn_items ENGINE=InnoDB", true);
			$con->sql_query_false("ALTER TABLE tmp_grn_items modify id int not null default 0", true);
			$con->sql_query_false("ALTER TABLE tmp_grn_items ENGINE=InnoDB", true);
						
			
			
			// 2/5/2020 5:45 PM Andy - User Finger Print
			$con->sql_query_false("create table if not exists user_finger_print (
				user_id int primary key,
				fingerprint1 varchar(1000) not null,
				fingerprint2 varchar(1000) not null,
				fingerprint3 varchar(1000) not null,
				fingerprint4 varchar(1000) not null,
				allowed_time_attendance_branch text,
				added timestamp not null default 0,
				last_update timestamp not null default 0,
				index added (added),
				index last_update (last_update)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			// 2/21/2020 9:19 AM Andy Mobile Suite Time Attendance
			if($this->check_need_alter('attendance_user_scan_record', array("suite_device_guid"))){
				$con->sql_query_false("alter table attendance_user_scan_record add column suite_device_guid char(36) not null, add index suite_device_guid (suite_device_guid)", true);
			}
			
			if($this->check_need_alter('attendance_user_scan_record_modify_history', array("osuite_device_guid"))){
				$con->sql_query_false("alter table attendance_user_scan_record_modify_history add column osuite_device_guid char(36) not null, add index osuite_device_guid (osuite_device_guid)", true);
			}
			
			// 2/25/2020 1:14 PM Andy SKU Tag
			$con->sql_query_false("create table if not exists sku_tag (
				id int not null primary key auto_increment,
				code char(30) not null,
				description char(100) not null,
				active tinyint(1) not null default 1,
				added timestamp not null default 0,
				last_update  timestamp not null default 0,
				index code (code),
				index description (description)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
		
			$con->sql_query_false("create table if not exists sku_tag_items (
				sku_tag_id int not null default 0,
				sku_item_id int not null default 0,
				po_reorder_qty_min int not null default 0,
				po_reorder_qty_max int not null default 0,
				po_reorder_moq int not null default 0,
				po_qty int not null default 0,
				do_qty int not null default 0,
				added timestamp not null default 0,
				last_update  timestamp not null default 0,
				primary key (sku_tag_id, sku_item_id),
				index sku_item_id (sku_item_id),
				index added (added)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci", true);
			
			
			
			//3/20/2020 11:29 AM Justin - Membership Credit Promotion
			$con->sql_query_false("create table if not exists membership_credit_promotion (
				id int not null default 0,
				branch_id int not null default 0,
				unique_id int not null default 0,
				doc_no char(30) not null,
				title char(100) not null,
				valid_from date,
				valid_to date,
				remark text,
				allowed_branches text,
				topup_amount double default 0,
				topup_credit double default 0,
				allowed_member_type text,
				expiry_type char(30) not null,
				expiry_date date,
				duration_expiry_type char(10) not null,
				duration_expiry_value int(11),
				user_id int not null default 0,
				active tinyint(1) not null default 1,
				status tinyint(1) not null default 0,
				added timestamp default 0, 
				confirm_timestamp timestamp default 0, 
				cancel_reason char(100) not null,
				cancelled_by int not null default 0,
				last_update timestamp default 0,
				primary key(branch_id, id),
				index title (title),
				index unique_id (unique_id),
				index doc_no (doc_no),
				index valid_from (valid_from),
				index valid_to (valid_to),
				index expiry_type (expiry_type),
				index active_status (active, status)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
		
			
			
			//3/26/2020 11:29 AM Justin - Membership Credit Settings
			$con->sql_query("create table if not exists membership_credit_settings (
				id int not null default 0,
				branch_id int not null default 0,
				disable_credit_topup tinyint(1) not null default 1,
				use_branch_settings tinyint(1) default 0,
				topup_amount double default 0,
				topup_credit double default 0,
				topup_amount_limit double default 0,
				user_id int not null default 0,
				added timestamp default 0, 
				last_update timestamp default 0,
				primary key(branch_id, id),
				index disable_credit_topup (disable_credit_topup),
				index use_branch_settings (use_branch_settings)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			//5/4/2020 2:00 PM Justin - Membership Credit Balance
			$con->sql_query_false("alter table membership 
								   credit_topup_bal double default 0, 
								   add payment_pin_no char(20) not null, 
								   add index guid_pin (membership_guid, payment_pin_no)");

			//5/4/2020 2:00 PM Justin - Membership Credit Top Up
			$con->sql_query_false("create table if not exists pos_credit_member_topup (
				id int not null,
				branch_id int not null default 0,
				counter_id int not null default 0,
				date date,
				membership_guid char(36) not null,
				card_no char(16) not null,
				promo_unique_id int not null default 0,
				ref_no char(30) not null,
				topup_amount double default 0,
				topup_credit double default 0,
				topup_ratio double default 0,
				expiry_date date,
				active tinyint(1) not null default 1,
				status tinyint(1) not null default 0,
				user_id int not null default 0,
				approved_by int not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key(branch_id, date, counter_id, id),
				index user_id (user_id),
				index approved_by (approved_by),
				index membership_guid (membership_guid),
				index card_no (card_no),
				index promo_unique_id (promo_unique_id),
				index ref_no (ref_no)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query_false("create table if not exists pos_credit_member_topup_payment (
				guid char(36) not null,
				branch_id int not null default 0,
				counter_id int not null default 0,
				date date,
				membership_guid char(36) not null,
				card_no char(16) not null,
				promo_unique_id int not null default 0,
				ref_no char(30) not null,
				topup_amount double default 0,
				topup_credit double default 0,
				topup_ratio double default 0,
				expiry_date date,
				active tinyint(1) not null default 1,
				success tinyint(1) not null default 0,
				user_id int not null default 0,
				approved_by int not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key(guid),
				index bid_date_cid (branch_id, date, counter_id),
				index user_id (user_id),
				index approved_by (approved_by),
				index membership_guid (membership_guid),
				index card_no (card_no),
				index promo_unique_id (promo_unique_id),
				index ref_no (ref_no)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query_false("create table if not exists pos_payment_member_credit (
				guid char(36) not null,
				branch_id int not null default 0,
				counter_id int not null default 0,
				date date,
				membership_guid char(36) not null,
				card_no char(16) not null,
				receipt_ref_no char(20) not null,
				credit_amount double default 0,
				success tinyint(1) not null default 0,
				user_id int not null default 0,
				approved_by int not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key(guid),
				index bid_date_cid (branch_id, date, counter_id),
				index user_id (user_id),
				index approved_by (approved_by),
				index membership_guid (membership_guid),
				index card_no (card_no),
				index receipt_ref_no (receipt_ref_no)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			
			
			//11/10/2020 9:30 AM William - Add new column "show_in_suite_pos" to category
			if($this->check_need_alter('category', array("show_in_suite_pos"))){
				$con->sql_query_false("alter table category add column show_in_suite_pos tinyint(1) not null default 0, add index show_in_suite_pos (show_in_suite_pos)", true);
			}
			
			//11/10/2020 11:30 AM William - Add new table pos_popular_sku_items
			$con->sql_query_false("create table if not exists pos_popular_sku_items (
				id int not null primary key auto_increment,
				branch_id int not null default 0,
				sku_item_id int not null default 0,
				active tinyint(1) not null default 0,
				user_id int not null default 0,
				sequence int not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				index branch_id (branch_id),
				index sku_item_id (sku_item_id),
				index user_id (user_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$con->sql_query_false("create table if not exists pos_popular_settings (
				branch_id int not null default 0,
				setting_name char(100) not null,
				setting_value char(100) not null,
				last_update timestamp default 0,
				primary key(branch_id, setting_name)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			// will not release, only for new customers, due to old customers db too large
			//5/3/2021 9:09 AM William - Changed pos table member_no char(16) to char(30)
			if($this->check_need_alter("pos",array("member_no"),array("member_no"=>"char(30)"))){
				$con->sql_query_false("alter table pos modify member_no char(30)",true);
			}
			
			
			
			// 3/19/2021 3:31 PM Andy - Changed counter counter_settings to engine innoDB			
			if(!$this->is_innodb('counter_settings')){
				$con->sql_query_false("ALTER TABLE counter_settings ENGINE=InnoDB", true);
			}

			

			// 4/30/2021 5:03 PM Andy - Coupon Allow for membership app
			if($this->check_need_alter('coupon', array("show_in_member_app"))){
				$con->sql_query_false("alter table coupon add column show_in_member_app tinyint(1) not null default 1, add index show_in_member_app (show_in_member_app)", true);
			}
			
			
		}
		
		*/
	}

	function get_tmp_version(){
	    $tmp_ver = 0;

	    $file_path = dirname(__FILE__)."/".$this->tmp_ver_file;
        if(file_exists($file_path)){
            $tmp_ver = file_get_contents($file_path);
		}
		$this->tmp_ver = $tmp_ver;
		return $this->tmp_ver;
	}

	function set_tmp_version($ver){
		$file_path = dirname(__FILE__)."/".$this->tmp_ver_file;
	    if($ver > $this->get_tmp_version()){    // only update if version is latest than file
            file_put_contents($file_path, $ver);
			chmod($file_path, 0777);
        	$this->tmp_ver = $ver;
		}
	}

	private function run_tmp(){
		global $con, $config;
		$starting_ver = $this->ver; // mark starting version

        if($this->get_tmp_version() < 280){
            if($this->check_need_alter("tmp_cnote_items",array("item_discount_amount","item_discount_amount2"))){
				$con->sql_query_false("alter table tmp_cnote_items add item_discount_amount double not null default 0 ,add item_discount_amount2 double not null default 0, add do_item_id int", true);
			}
            $this->set_tmp_version(280);
		}
		
		if($this->get_tmp_version() < 284){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// fix add po item slow
			$con->sql_query_false("alter table tmp_po_items add index branchID_n_poID_n_userID (branch_id, po_id, user_id)");
			
			$this->set_tmp_version(284);
		}
		
		if($this->get_tmp_version() < 290){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			if($this->check_need_alter("tmp_sales_order_items",array("parent_stock_balance"))) {
				$con->sql_query_false("alter table tmp_sales_order_items add parent_stock_balance double", true);
			}
			
			if($this->check_need_alter("tmp_do_items", array("parent_stock_balance1", "parent_stock_balance2", "parent_stock_balance2_allocation"))) {
				$con->sql_query_false("alter table tmp_do_items add (parent_stock_balance1 double, parent_stock_balance2 double, parent_stock_balance2_allocation text)", true);
			}
			
			$this->set_tmp_version(290);
		}
		
		if($this->get_tmp_version() < 292){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			//  7/5/2016 11:34 AM Andy
			$con->sql_query_false("create table if not exists tmp_purchase_agreement_info(
				branch_id int,
				id int auto_increment,
				header_info text,
				items_info text,
				user_id int,
				added timestamp default 0,
				primary key(branch_id, id),
				index user_id (user_id)
			)", true);
			
			$this->set_tmp_version(292);
		}
		
		if($this->get_tmp_version() < 303){
			if(!$_GET['force_maintenance'] && $starting_ver>280){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 9/8/2016 16:36 Qiu Ying - Add new column in tmp_promotion_mix_n_match_items
			if($this->check_need_alter("tmp_promotion_mix_n_match_items",array("disc_by_inclusive_tax"))){
				$con->sql_query_false("alter table tmp_promotion_mix_n_match_items add disc_by_inclusive_tax enum('yes','no') DEFAULT NULL", true);
			}
			
			$this->set_tmp_version(303);
		}
		
		if($this->get_tmp_version() < 310){
			if(!$_GET['force_maintenance'] && $starting_ver>300){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// PO Enhancement -- 2/21/2017 4:17 PM Andy
			if($this->check_need_alter("tmp_po_items",array("tax_amt", "discount_amt", "item_gross_amt", "item_nett_amt", "item_gst_amt", "item_amt_incl_gst", "item_total_selling", "item_total_gst_selling"))){
				$con->sql_query_false("alter table tmp_po_items add tax_amt double not null default 0, add discount_amt double not null default 0, add item_gross_amt double not null default 0, add item_nett_amt double not null default 0, add item_gst_amt double not null default 0, add item_amt_incl_gst double not null default 0, add item_total_selling double not null default 0, add item_total_gst_selling double not null default 0",true);
			}
			
			$this->set_tmp_version(310);
		}
		
		if($this->get_tmp_version() < 312){
			if(!$_GET['force_maintenance'] && $starting_ver>300){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// Andy
			if($this->check_need_alter("tmp_adjustment_items",array("stock_balance"),array("stock_balance"=>"double"))){
				$con->sql_query_false("alter table tmp_adjustment_items modify stock_balance double",true);
			}
			// 3/9/2017 4:57 PM Andy -- PO Enhancement
			if($this->check_need_alter("tmp_po_items",array("item_allocation_info"))){
				$con->sql_query_false("alter table tmp_po_items add item_allocation_info text",true);
			}
			
			$this->set_tmp_version(312);
		}
		
		if($this->get_tmp_version() < 318){
			if(!$_GET['force_maintenance'] && $starting_ver>300){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			if($this->check_need_alter("tmp_grn_items",array("acc_foc_ctn", "acc_foc_pcs", "acc_foc_amt", "acc_disc", "acc_disc_amt"))){
				$con->sql_query_false("alter table tmp_grn_items add acc_foc_ctn double not null default 0,
									   add acc_foc_pcs double not null default 0,
									   add acc_foc_amt double not null default 0,
									   add acc_disc char(100) not null,
									   add acc_disc_amt double not null default 0",true);
			}
			
			$this->set_tmp_version(318);
		}
		
		if($this->get_tmp_version() < 321){
			if(!$_GET['force_maintenance'] && $starting_ver>310){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 5/16/2017 11:41 AM Qiu Ying - - Add new column in tmp_cnote_items
			if($this->check_need_alter("tmp_cnote_items",array("return_inv_no","return_inv_date", "return_do_id"))){
				$con->sql_query_false("alter table tmp_cnote_items 
									  add return_inv_no char(20),
									  add return_inv_date date,
									  add return_do_id int(11)", true);
			}
			
			$this->set_tmp_version(321);
		}
		
		if($this->get_tmp_version() < 322){
			if(!$_GET['force_maintenance'] && $starting_ver>310){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			// 5/29/2017 3:24 PM Justin - bom			
			if($this->check_need_alter("tmp_do_items",array("bom_id", "bom_ref_num", "bom_qty_ratio"))){
				$con->sql_query_false("alter table tmp_do_items 
									   add bom_id int(11) not null default 0,
									   add bom_ref_num char(20), 
									   add bom_qty_ratio double not null default 0", true);
			}
			
			// 4/10/2017 10:08 AM Andy - PO Missing Purchase Agreement Fields
			if($this->check_need_alter("tmp_po_items",array("pa_branch_id", "pa_item_id", "pa_foc_item_id"))){
				$con->sql_query_false("alter table tmp_po_items add pa_branch_id int, add pa_item_id int, add pa_foc_item_id int, add index pa_bid_n_pa_item_id (pa_branch_id, pa_item_id), add index pa_bid_n_pa_foc_item_id (pa_branch_id, pa_foc_item_id)", true);
			}
			
			$this->set_tmp_version(322);
		}
		
		if($this->get_tmp_version() < 338){
			if(!$_GET['force_maintenance'] && $starting_ver>330){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			$con->sql_query_false("alter table tmp_trigger_log add index tablename_n_row_index (tablename, row_index)");
			
			$this->set_tmp_version(338);
		}
		
		if($this->get_tmp_version() < 349){
			if(!$_GET['force_maintenance'] && $starting_ver>330){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$this->mark_start_maintenance();
			
			$con->sql_query_false("create table if not exists tmp_work_order_items_out(
				branch_id int,
				id int auto_increment,
				work_order_id int,
				user_id int not null default 0,
				edit_time int not null default 0,
				sku_item_id int not null default 0,
				cost double not null default 0,
				price double not null default 0,
				weight_kg double not null default 0,
				stock_balance double not null default 0,
				qty double not null default 0,
				gst_id int not null default 0,
				gst_code char(30) not null,
				gst_rate double not null default 0,
				display_price_is_inclusive tinyint(1) not null default 0,
				display_price double not null default 0,
				line_total_cost double not null default 0,
				line_exptected_weigth double not null default 0,
				line_actual_received_weigth double not null default 0,
				line_shrinkage_weigth double not null default 0,
				cost_per_weight double not null default 0,
				primary key (branch_id, id),
				index wo_id_bid (work_order_id, branch_id),
				index sku_item_id (sku_item_id),
				index user_id_edit_time (user_id, edit_time)
			)", true);
			
			$con->sql_query_false("create table if not exists tmp_work_order_items_in(
				branch_id int,
				id int auto_increment,
				work_order_id int,
				user_id int not null default 0,
				edit_time int not null default 0,
				sku_item_id int not null default 0,
				cost double not null default 0,
				price double not null default 0,
				weight_kg double not null default 0,
				stock_balance double not null default 0,
				gst_id int not null default 0,
				gst_code char(30) not null,
				gst_rate double not null default 0,
				display_price_is_inclusive tinyint(1) not null default 0,
				display_price double not null default 0,
				expect_qty double not null default 0,
				expect_cost double not null default 0,
				line_total_expect_cost double not null default 0,
				line_total_expect_weight double not null default 0,
				actual_qty double not null default 0,
				actual_cost double not null default 0,
				line_total_actual_cost double not null default 0,
				line_total_actual_weight double not null default 0,
				finish_cost double not null default 0,
				line_total_finish_cost double not null default 0,
				primary key (branch_id, id),
				index wo_id_bid (work_order_id, branch_id),
				index sku_item_id (sku_item_id)
			)", true);
			
			$this->set_tmp_version(349);
		}
		
		if($this->get_tmp_version() < 354){
			if(!$_GET['force_maintenance'] && $starting_ver>340){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			// 7/13/2018 3:38 PM Andy - BOM SKU
			if($this->check_need_alter('tmp_bom_items', array('edit_time'))){
				$con->sql_query("alter table tmp_bom_items add edit_time int not null default 0, add index edit_time_n_user_id (edit_time, user_id)", false);
			}
			
			if($this->check_need_alter("tmp_gra_items",array("currency_code"))){
				$con->sql_query_false("alter table tmp_gra_items add currency_code char(10), add index currency_code(currency_code)",true);
			}
			
			$this->set_tmp_version(354);
		}
		
		if($this->get_tmp_version() < 355){
			if(!$_GET['force_maintenance'] && $starting_ver>340){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			// 11/22/2018 2:40 PM Table to Generate DO
			$con->sql_query_false("create table if not exists `tmp_generate_do` (
					guid char(36) primary key,
					branch_id int(11) not null default 0,
					do_type char(20) not null,
					do_branch_id int not null default 0,
					debtor_id int not null default 0,
					open_info text,
					user_id int(11) not null,
					added timestamp default 0,
					index added (added)
				) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
				
			$con->sql_query_false("create table if not exists `tmp_generate_do_items` (
					guid char(36) primary key,
					gen_do_guid char(36),
					sku_item_id int(11) not null default 0,
					uom_id int not null default 0,
					ctn double not null default 0,
					pcs double not null default 0,
					added timestamp default 0,
					sequence int not null default 0,
					index added (added)
				) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->set_tmp_version(355);
		}
		
		if($this->get_tmp_version() < 374){
			if(!$_GET['force_maintenance'] && $starting_ver>340){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			if($this->check_need_alter("tmp_po_items",array("cost_indicate"),array("cost_indicate"=>"char(20)"))){
				$con->sql_query_false("alter table tmp_po_items modify cost_indicate char(20)",true);
			}
			
			$this->set_tmp_version(374);
		}
		
		if($this->get_tmp_version() < 380){
			if(!$_GET['force_maintenance'] && $starting_ver>370){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			if($this->check_need_alter("tmp_generate_do",array("do_date", "remark"))){
				$con->sql_query_false("alter table tmp_generate_do add do_date date, add remark text", true);
			}
			
			$this->set_tmp_version(380);
		}
		
		if($this->get_tmp_version() < 395){
			if(!$_GET['force_maintenance'] && $starting_ver>380){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			// 5/28/2019 3:05 PM Andy - DO
			if($this->check_need_alter("tmp_generate_do", array("relationship_guid"))){
				$con->sql_query_false("alter table tmp_generate_do add relationship_guid char(36) not null, add index relationship_guid (relationship_guid)", true);
			}
			
			$this->set_tmp_version(395);
		}
		
		if($this->get_tmp_version() < 407){
			if(!$_GET['force_maintenance'] && $starting_ver>400){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			// 8/23/2019 4:52 PM Andy - DO Custom Column
			if($this->check_need_alter("tmp_do_items", array("custom_col"))){
				$con->sql_query_false("alter table tmp_do_items add custom_col text",true);
			}
			
			$this->set_tmp_version(407);
		}
		
		if($this->get_tmp_version() < 409){
			if(!$_GET['force_maintenance'] && $starting_ver>400){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			$con->sql_query("create table if not exists tmp_member_pos_trigger(
				card_no char(20) not null,
				receipt_ref_no char(20) not null,
				last_update timestamp,
				primary key(card_no, receipt_ref_no),
				index receipt_ref_no (receipt_ref_no)
				) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->set_tmp_version(409);
		}
		
		if($this->get_tmp_version() < 410){
			if(!$_GET['force_maintenance'] && $starting_ver>400){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			if($this->check_need_alter("tmp_generate_do_items", array("cost_price"))){
				$con->sql_query_false("alter table tmp_generate_do_items add cost_price double not null default 0",true);
			}
			$con->sql_query_false("alter table tmp_generate_do_items add index gen_do_guid (gen_do_guid)");
			
			$con->sql_query_false("create table if not exists `tmp_generate_do_open_items` (
					guid char(36) primary key,
					gen_do_guid char(36),
					artno_mcode char(30) not null,
					description char(100) not null,
					pcs double not null default 0,
					added timestamp default 0,
					sequence int not null default 0,
					cost_price double not null default 0,
					index added (added),
					index gen_do_guid (gen_do_guid)
				) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
			$this->set_tmp_version(410);
		}
		
		
		
		if($this->get_tmp_version() < 422){
			if(!$_GET['force_maintenance'] && $starting_ver>400){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			if($this->check_need_alter("tmp_generate_do", array("discount"))){
				$con->sql_query_false("alter table tmp_generate_do add discount char(15) not null", true);
			}
			
			$this->set_tmp_version(422);
		}
		
		if($this->get_tmp_version() < 457){
			if(!$_GET['force_maintenance'] && $starting_ver>400){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			// 4/2/2020 9:00 AM William - Drop branch_id of tmp_bom_items and change the engine to innoDB
			$con->sql_query_false("ALTER TABLE tmp_bom_items drop column branch_id");
			if(!$this->is_innodb('tmp_bom_items')){
				$con->sql_query_false("ALTER TABLE tmp_bom_items ENGINE=InnoDB", true);
			}
			
			$this->set_tmp_version(457);
		}
		
		if($this->get_tmp_version() < 477){
			if(!$_GET['force_maintenance'] && $starting_ver>400){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			// 1/7/2020 9:35 AM William - Remove auto_increment of adjustment tables id and change the engine to innoDB			
			if(!$this->is_innodb('adjustment')){
				$con->sql_query_false("ALTER TABLE tmp_adjustment_items modify id int(11) not null default 0", true);
				$con->sql_query_false("ALTER TABLE tmp_adjustment_items ENGINE=InnoDB", true);
			}
			
			$this->set_tmp_version(477);
		}
		
		if($this->get_tmp_version() < 487){
			if(!$_GET['force_maintenance'] && $starting_ver>480){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			// 1/27/2020 9:00 AM William - Added new column remark to sales_order_items and tmp_sales_order_items table.
			if($this->check_need_alter("tmp_sales_order_items", array("remark"))){
				$con->sql_query_false("alter table tmp_sales_order_items add remark text", true);
			}
			
			$this->set_tmp_version(487);
		}
		
		if($this->get_tmp_version() < 493){
			if(!$_GET['force_maintenance'] && $starting_ver>490){
		        // check whether is the right timing to run this maintenance
                if(!$this->check_processing_time('0000', '0600'))   return false;
                // if not the right timing to run, return and dont process
			}
			
			// 2/25/2021 3:32 PM Andy - Work Order
			if($this->check_need_alter('tmp_work_order_items_in', array("uom_id", "actual_adj_qty"))){
				$con->sql_query_false("alter table tmp_work_order_items_in
					add column uom_id int not null default 0, 
					add column actual_adj_qty double not null default 0, 
					add index uom_id (uom_id)", true);
			}
			
			$this->set_tmp_version(493);
		}	
	}

	private function check_need_alter($tbl, $col_list = array(), $col_type_list = array()){
		global $con;

		if(!$tbl || !$col_list) die('Invalid table or column name.');

		// get column list
		$curr_tbl_col_list = array();
		$q1 = $con->sql_query_false("explain $tbl", true);
		while($r = $con->sql_fetchrow($q1)){
			// this column need to check
			if(in_array($r[0], $col_list) && $col_type_list[$r[0]]){
				$type = $col_type_list[$r[0]];
				if($r[1] != $type)	return true;	// need alter
			}
            $curr_tbl_col_list[] = $r[0];
		}
		$con->sql_freeresult($q1);

		foreach($col_list as $col){
			if(!in_array($col, $curr_tbl_col_list)){
				return true;    // need alter
			}
		}
		return false;   // all column need to alter already exists, so no need to alter again
	}
	
	private function mark_start_maintenance(){
		global $smarty;
		
		if(!is_writable(dirname($this->fp_path))){
			$smarty->display('header.tpl');
			print "<h1>The folder '".dirname($this->fp_path)."' permission not allow maintenance to be run, please contact system admin.</h1>";
			$smarty->display('footer.tpl');
			exit;
		}
		
		if(!flock($this->fp, LOCK_EX | LOCK_NB)){
			$smarty->display('header.tpl');
			print "<h1>Other process of maintenance is running, please wait for them to finish.</h1>";
			$smarty->display('footer.tpl');
			exit;
		}
	}
	
	private function mark_close_maintenance(){
		flock($this->fp, LOCK_UN);
	}
	
	private function setup_arms_user(){
		//$this->add_arms_user('arms2','asi0758');
		$this->add_arms_user('arms_tommy','54a9fefc6ebcd3b767fd5f2c28a83562');
		//$this->add_arms_user('arms_justin','02fc4597a9a69be20ec85a47b6bbe708');
		//$this->add_arms_user('arms_qiuying','ying1234');
		//$this->add_arms_user('arms_keekee','keekee1234');
		//$this->add_arms_user('arms_yihc','yihchorng1234');
		//$this->add_arms_user('arms_zae','arms1234');
		//$this->add_arms_user('arms_wc','arms1234');
		//$this->add_arms_user('arms_kimseng','kimseng');
		//$this->add_arms_user('arms_zaini','aina1781');
		//$this->add_arms_user('arms_lan','lan231100');
		//$this->add_arms_user('arms_tom','tomlee1234');
		//$this->add_arms_user('arms_soonkeat','skng@123');
		$this->add_arms_user('arms_yctan','de128229515f974284ad87b26dbb863c');
		$this->add_arms_user('arms_ashlyn','f40e62eadb64309a0a9ccd6757164419');
		//$this->add_arms_user('arms_gwc','61d8d54d7a808ca655cd66627c27741a');
		//$this->add_arms_user('arms_nava','arms2234');
		//$this->add_arms_user('arms_lam','cslam108');
		//$this->add_arms_user('arms_mark','810mark');
		//$this->add_arms_user('arms_hocklee','1234hock');
		$this->add_arms_user('arms_brandon','90abb1193a658b7e2c5fdce8fee4dc73');
		//$this->add_arms_user('arms_joe','63eb4646e8c93c335bdc9542cbf80b41');
		//$this->add_arms_user('arms_adam','991919d887738ba5a02fb7bd223cf156'); - demo only
		//$this->add_arms_user('arms_aaron','1da91584d07f73b5056478af0354293f');
		$this->add_arms_user('arms_sllee','d7f2ca5e551b1adb955ae8d2f257c354');
		$this->add_arms_user('arms_william','359a4a5132e28039730b83ba20607412');	// maximus only
		//$this->add_arms_user('arms_liew','sk19arms');	// maximus only
		//$this->add_arms_user('arms_andrew','89d361adeb2cfac06ed3f11f466cc00e');
		//$this->add_arms_user('arms_eekean','db5896700e6dff58654138f30d15872b');
		//$this->add_arms_user('arms_farhana','06cbf18339e4a727c52e3fc4530b3cb0');	// maximus only
		$this->add_arms_user('arms_jesse','1f87c6f9782cf2d7380ccc0b705a8e31');
		//$this->add_arms_user('arms_xinhui','F23B285931746AD4D7A1F6D54A822D08');
		$this->add_arms_user('arms_thooi','acda7803c87201ae32b07a9c83e972a0');
		$this->add_arms_user('arms_jun','f36163ec60637e4b0d7f2a3c7033db01');
		$this->add_arms_user('arms_shane','d1ef4f95bfb22261e0c2ad8fa1cdfc3f');
		$this->add_arms_user('arms_darrell','09455919a75b837eba59816eaf726103');
	}
	
	private function add_arms_user($u, $md5_p){
		global $con;
		
		$con->sql_query("select id from user where u=".ms($u));
		$user = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$user){
			$con->sql_query_false("insert into user (u,l,p,active,default_branch_id,level,is_arms_user) values (".ms($u).",".ms($u).",".ms($md5_p).",1,1,9999,1)",true);
			$user_id = $con->sql_nextid();
		}else{
			$user_id = mi($user['id']);
		}		
		
		if(!$user_id)	return;
		
		// give privileges to all branch
		$q1 = $con->sql_query("select id from branch order by id");
		while($b = $con->sql_fetchassoc($q1)){
			$bid = mi($b['id']);
			$con->sql_query_false("replace into user_privilege (user_id, branch_id, privilege_code, allowed) (select $user_id, $bid, code, 1 from privilege)",true);
		}
		$con->sql_freeresult($q1);
		
	}
	
	private function is_innodb($tbl_name){
		global $db_default_connection, $con;
		
		//$con->sql_query("SHOW TABLE STATUS WHERE Name = ".ms($tbl_name));
		//$tmp = $con->sql_fetchassoc();
		//$con->sql_freeresult();
		
		// Get table engine type
		$con->sql_query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ".ms($db_default_connection[3])." AND TABLE_NAME = ".ms($tbl_name));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(strtolower($tmp['ENGINE']) == 'innodb'){	// is innodb
			return true;
		}
		return false;
	}
	
	/*function get_sync_server_version(){
	    $sync_server_ver = 0;

	    $file_path = dirname(__FILE__)."/".$this->sync_server_ver_file;
        if(file_exists($file_path)){
            $sync_server_ver = file_get_contents($file_path);
		}
		$this->sync_server_ver = $sync_server_ver;
		return $this->sync_server_ver;
	}

	function set_sync_server_version($ver){
		$file_path = dirname(__FILE__)."/".$this->sync_server_ver_file;
	    if($ver > $this->get_sync_server_version()){    // only update if version is latest than file
            file_put_contents($file_path, $ver);
			chmod($file_path, 0777);
        	$this->sync_server_ver = $ver;
		}
	}
	
	function run_sync_server(){
		global $con, $config;
		$starting_ver = $this->sync_server_ver; // mark starting version

        if($this->get_sync_server_version() < 1){
            $con->sql_query_false("alter table tmp_trigger_log add index tablename_n_row_index (tablename, row_index)");
            $this->set_sync_server_version(1);
		}
	}*/
}

$maintenance = new Maintenance();

?>
