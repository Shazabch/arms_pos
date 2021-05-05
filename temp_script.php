<?php
/*
10/1/2015 2:11 PM Andy
- Enhanced to reset dnote in "clear_docs" functions.

11/23/2015 3:00 PM Qiu Ying
-  Auto run all sku photos & ic and resize them

1/29/2016 10:00 AM Qiu Ying
- Enhance "fix_gra_items_amount" functions

08/09/2016 17:30 Edwin
- Bug fixed on "gst_selling_price" always zero when generate po via Vendor Login.

8/23/2016 2:18 PM Andy
- Added function "fix_pos_cn_number" to fixed pos counter cn number duplicate bug.

9/5/2016 9:05 Qiu Ying
- Add function "revert_gst_code" to revert all AJP to TX and AJS to SR

10/11/2016 9:49 AM Andy
- Enhanced clear_docs() to clear more pos related tables.

10/31/2016 5:17 PM Andy
- Enhanced clear_docs() to skip is_arms_user.

1/6/2017 3:56 PM Andy
- Enhanced fix_category_sales_cache() to check pos finalised error.

3/2/2017 11:04 AM Andy
- Add function "update_all_po_amt_old" to auto calculate all po amount using old method.

3/7/2017 4:43 PM Andy
- Fixed "update_all_po_amt_old" to only run for own branch for multi server mode.

3/10/2017 3:19 PM Andy
- Fixed "fix_pos_discount2" to check deposit payment.

9/13/2017 6:02 PM Andy
- Fixed clear_docs master no clean the cost history and change price history.

10/31/2017 4:17 PM Andy
- Enhanced clear_docs to include new pos table.

11/22/2017 4:28 PM Andy
- Added "generate_stock_check_as_cutoff".

12/4/2017 1:17 PM Andy
- Added "check_multi_server_sp_with_hq".

12/22/2017 1:57 PM Justin
- Added new function "recalc_sa_sales_cache".

3/2/2018 3:10 PM Andy
- Enhanced fix_pos_item_missing_gst to check taxcode null.

5/3/2018 3:56 PM Andy
- Added new function "fix_deposit_used_missing_pos_id".

5/22/2018 3:56 PM Andy
- Added new function "june_gst_to_zero".

6/12/2018 9:34 AM Andy
- Enhanced to automatically check is using SSH, no longer require to pass is_http when using browser.

6/28/2018 2:32 PM Andy
- Added new function "fix_aneka_stock_take".

7/2/2018 4:48 PM Andy
- Added new function "change_cmaree_sp" to fix cutemaree selling price.

7/5/2018 3:49 PM Andy
- Added new function "exec_send_email".

7/9/2018 5:14 PM Andy
- Enhanced clear_docs() to truncate log.

7/10/2018 11:39 AM Andy
- Fixed clear_docs() din't clear promotion data.

7/17/2018 2:54 PM Andy
- Enhanced "fix_aneka_stock_take".

8/7/2018 11:38 AM Andy
- Added new function "change_metrohouse_sp" to change metrohouse selling price.

10/8/2018 3:41 PM Andy
- Added new function "fix_branch_approval_history" and "fix_metrohouse_price_type".

11/9/2018 10:39 AM Justin
- Added new function "fix_price_history_fpi_id".

12/18/2018 11:17 AM Justin
- Added new functions "regenerate_pos_cashier_finalise" and "change_array_value_to_string".

3/15/2019 1:30 PM Andy
- Changed generate_pos_cashier_finalize() to posManaer->generatePosCashierFinalize()
- Added new function "patch_pp_group_type".

4/11/2019 3:11 PM Justin
- Added new function "fix_196_member_points".

5/8/2019 2:33 PM Andy
- Added new function "link_huaho_deleted_sku" to link back huaho deleted sku.

9/30/2019 5:15 PM Andy
- Added new temp function "copy_sku_photo_to_pos_photo" to copy first apply or actual photo to promotion photo.
- Added new temp function "fix_promotion_photo_path" to fixed promotion photo path to become always 1.jpg

12/18/2019 2:42 PM Andy
- Fixed "patch_pp_group_type" array issue.

1/2/2020 4:14 PM William
- Generate membership_guid to table membership, membership_points and membership_history when column "membership_guid" is empty value.

3/16/2020 10:25 AM William
- Added new temp function "update_pos_membership_guid" to update pos table membership_guid.

6/11/2020 3:53 PM Andy
- Added new temp function "generate_sku_items_finalised_cache" to generate sku_items_finalised_cache.

11/3/2020 3:38 PM Andy
- Added new temp function "delele_wls_member".
- Enhanced "clear_docs" to clear some missing membership table.

*/
$arg = $_SERVER['argv'];

//print_r($_SERVER);exit;

if( empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) {
	$a = $arg[1];
	define("TERMINAL",1);	
}else{
	$a = $_REQUEST['a'];
}
/*if($_REQUEST['is_http']){
  	$a = $_REQUEST['a'];
}elseif($arg){
	$a = $arg[1];
	define("TERMINAL",1);	
}*/

include("include/common.php");
//$db_default_connection = array("localhost", "root", "", "yy");
//$db_default_connection = array("localhost", "root", "", "arms_segi");
//$db_default_connection = array(":/tmp/mysql.sock3", "root", "", "armstest");
//$db_default_connection = array("localhost", "root", "", "arms_cm");
//$db_default_connection = array("10.1.1.202", "arms", "Arms54321.", "armshq_cwm");
//$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);
error_reporting (E_ALL ^ E_NOTICE);
ini_set("display_errors", 1);
ini_set('memory_limit', '512M');
set_time_limit(0);

$maintenance->check(1);
//if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
switch ($a)
{
	case 'revert_gst_code': // php temp_script.php revert_gst_code 0/1
		revert_gst_code($arg[2]);
		exit;
	case 'test':
		test();
		exit;
	case 'fix_gst_info':
		fix_gst_info();
		exit;
	case 'fix_category_gst_info':
		fix_category_gst_info();
		exit;
	case 'fix_sales_cat_cache':	// temp_script.php?is_http=1&a=fix_sales_cat_cache
		fix_sales_cat_cache();
		exit;
	case 'generate_consign_designated':	// temp_script.php?is_http=1&a=generate_consign_designated
		generate_consign_designated();
		exit;
	case 'fix_sku_description_encoding':	// php temp_script.php fix_sku_description_encoding
		fix_sku_description_encoding();
		exit;
	case 'fix_uom_encoding':	// php temp_script.php fix_uom_encoding
		fix_uom_encoding();
		exit;
	case 'fix_segi_sku_group_date_control':	// php temp_script.php fix_segi_sku_group_date_control
		fix_segi_sku_group_date_control();
		exit;
	case 'remove_sageUBS':	// temp_script.php?is_http=1&a=remove_sageUBS
		remove_sageUBS();
		exit;
	case 'fix_gst_zero_rate':	// temp_script.php?is_http=1&a=fix_gst_zero_rate
		fix_gst_zero_rate();
		exit;
	case 'fix_gst_sp':	// temp_script.php?is_http=1&a=fix_gst_sp
		fix_gst_sp();
		exit;
	case 'fix_pos_change_amt':	// temp_script.php?is_http=1&a=fix_pos_change_amt
		fix_pos_change_amt();
		exit;
	case 'fix_segi_sku_group':	// php temp_script.php fix_segi_sku_group
		fix_segi_sku_group();
		exit;
	case 'fix_gra_return_ctn':	// php temp_script.php fix_segi_sku_group
		fix_gra_return_ctn();
		exit;
	case 'fix_dnote_gst_info':	// php temp_script.php fix_segi_sku_group
		fix_dnote_gst_info();
		exit;
	case 'clone_sku_group_items_to':	// temp_script.php?is_http=1&a=clone_sku_group_items_to&from_code=1022&to_code=1000
		clone_sku_group_items_to();
		exit;
	case 'fix_category_sales_cache':	// php temp_script.php fix_category_sales_cache -branch=all -is_run
		fix_category_sales_cache();
		exit;
	case 'reset_database':	// php temp_script.php reset_database -is_run
		reset_database();
		exit;
	case 'fix_pos_items_price_type':
		fix_pos_items_price_type();	// php temp_script.php fix_pos_items_price_type -branch=hq -is_run
		exit;
	case 'fix_pos_rounding':
		fix_pos_rounding();
		exit;
	case 'check_and_resync_sku':
		check_and_resync_sku();
		exit;
	case 'fix_pos_item_missing_gst':
		fix_pos_item_missing_gst();
		exit;
	case 'move_sku_cat':
		move_sku_cat();
		exit;
	case 'clear_docs':	// php temp_script.php clear_docs all
	    array_shift($arg);
		while($opt = array_shift($arg)){
		    $params[] = $opt;
		}
	    clear_docs();
	    exit;
	case 'check_do_config':
		check_do_config();
		exit;
	case 'fix_dn_gra_gst_amt':
		fix_dn_gra_gst_amt();
		exit;
	case 'check_config_value':
		check_config_value();
		exit;
	case 'refinalized_pos':
	    $branch_code = $arg[2];
	    $from_date = $arg[3];
	    $to_date = $arg[4];
	    refinalized_pos();
	    exit;
	case 'fix_do_info_missing_from_grn':
		fix_do_info_missing_from_grn();
		exit;
	case 'fix_pos_item_tax_amount':
		fix_pos_item_tax_amount();
		exit;
	case 'fix_grn_gst_selling_price':
		fix_grn_gst_selling_price();
		exit;
	case 'kill_mysql_dump':
		kill_mysql_dump();
		exit;
	case 'fix_sales_cache':
		$ci_date = $_REQUEST['date'];
		if(!$ci_date) die("Please assign a date");
		$tmp = $con->sql_query("select * from branch where active=1");
		while($r = $con->sql_fetchassoc($tmp)){
			update_sales_cache($r['id'], $ci_date);
		}
		$con->sql_freeresult($sql);
		exit;
	case 'test_send_email':
		test_send_email();
		exit;
	case 'delete_sock2_unused_data':
		delete_sock2_unused_data();
		exit;
	case 'fix_trigger':
		fix_trigger();
		exit;
	case 'fix_pos_discount2':
		fix_pos_discount2();
		exit;
	case 'delete_invalid_sb_table': // andy
		$prms = array();
		$prms['branch_code'] = $arg[2];
		$prms['is_run'] = mi($arg[3]);
	    delete_invalid_sb_table($prms);
	    exit;
	case 'fix_pos_item_discount':
		fix_pos_item_discount();
		exit;
	case 'fix_sku_items_price_history':
		fix_sku_items_price_history();
		exit;
	case 'fix_gra_items_amount':
		fix_gra_items_amount();
		exit;
	case 'check_and_fix_all_image_size':
		$folder=array("icfiles.save","sku_photos");
		for($i=0;$i<count($folder);$i++)
			check_and_fix_all_image_size($folder[$i]); 
		echo "Done\n";
		exit;
	case 'fix_dnote_amount':
		fix_dnote_amount();
		exit;
	case 'clean_pos_edit':
		clean_pos_edit();
		exit;
	case 'fix_segi_bc_wrong_stock_balance_table':
		fix_segi_bc_wrong_stock_balance_table();
		exit;
	case 'insert_aneka_member':
		insert_aneka_member();
		exit;
	case 'update_segi_price':
		update_segi_price($arg[2], $arg[3]);
		exit;
	case 'stock_check_reset_cost':	// php temp_script.php stock_check_reset_cost test 
		stock_check_reset_cost($arg[2], $arg[3]);
		exit;
	case 'fix_member_last_purchase_branch':
		fix_member_last_purchase_branch();
		exit;
	case 'fix_intipos_receipt_description':
		fix_intipos_receipt_description();
		exit;
	case 'fix_po_sp':	// temp_script.php?is_http=1&a=fix_po_sp&branch_id=X&po_id=Y
		fix_po_sp();
		exit;
	case 'fix_all_po_sp':
		fix_all_po_sp();
		exit;
	case 'fix_pos_cn_number':
		/*
			php temp_script.php fix_pos_cn_number -branch=all -date=yesterday -force_update -is_run
			temp_script.php?is_http=1&a=fix_pos_cn_number&branch_id=1
		*/
		fix_pos_cn_number();
		exit;
	case 'fix_duplicate_vendor':	// temp_script.php?is_http=1&a=fix_duplicate_vendor
		fix_duplicate_vendor();
		exit;
	case 'restore_aneka_dungun_stock_check':	// php temp_script.php restore_aneka_dungun_stock_check 1
		restore_aneka_dungun_stock_check($arg[2]);
		exit;
	case 'restore_db_cutoff_sku_history':
		restore_db_cutoff_sku_history($arg[2]);
		exit;
	case 'restore_db_cutoff_sku_history2':
		restore_db_cutoff_sku_history2($arg[2]);
		exit;
	case 'update_all_po_amt_old':	// php temp_script.php update_all_po_amt_old
		update_all_po_amt_old($arg[2]);
		exit;
	case 'fix_grn_zero_selling_price':
		$prms = array();
		$prms['branch_id'] = $arg[2];
		if($arg[3]) $prms['grn_id'] = $arg[3];
		fix_grn_zero_selling_price($prms);
		exit;
	case 'fix_membership_receipt_no_gst':
		$prms = array();
		$prms['branch_id'] = $arg[2];
		$prms['date'] = $arg[3];
		fix_membership_receipt_no_gst($prms);
		exit;
	case 'generate_stock_check_as_cutoff':	// php temp_script.php generate_stock_check_as_cutoff GURUN 2017-09-01
		generate_stock_check_as_cutoff($arg[2], $arg[3]);
		exit;
	case 'check_multi_server_sp_with_hq':	// php temp_script.php check_multi_server_sp_with_hq -date=2017-09-01
		check_multi_server_sp_with_hq();
		exit;
	case 'recalc_sa_sales_cache':
		recalc_sa_sales_cache();
		exit;
	case 'fix_deposit_used_missing_pos_id':	// php temp_script.php fix_deposit_used_missing_pos_id 47 2018-05-03 2018-05-03
		fix_deposit_used_missing_pos_id($arg[2], $arg[3], $arg[4]);
		exit;
	case 'june_gst_to_zero':	// php temp_script.php june_gst_to_zero
		june_gst_to_zero();
		exit;
	case 'fix_aneka_stock_take':	// php temp_script.php fix_aneka_stock_take GURUN 2018-03-20
		fix_aneka_stock_take($arg[2], $arg[3], $arg[4]);
		exit;
	case 'change_cmaree_sp':	// php temp_script.php change_cmaree_sp
		change_cmaree_sp($arg[2]);
		exit;
	case 'exec_send_email':	// temp_script.php?a=exec_send_email
		exec_send_email();
		exit;
	case 'change_metrohouse_sp':	// php temp_script.php change_metrohouse_sp
		change_metrohouse_sp($arg[2], $arg[3]);
		exit;
	case 'fix_membership_history_nric':
		fix_membership_history_nric();
		exit;
	case 'prepend_membership_nric':
		prepend_membership_nric();
		exit;
	case 'fix_branch_approval_history':
		fix_branch_approval_history();
		exit;
	case 'fix_metrohouse_price_type':	// php temp_script.php fix_metrohouse_price_type
		fix_metrohouse_price_type();
		exit;
	case 'fix_price_history_fpi_id':	// php temp_script.php fix_price_history_fpi_id HQ		
		fix_price_history_fpi_id($arg[2]);
		exit;
	case 'test_api':
		test_api();
		exit;
	case 'answer_api':
		answer_api();
		exit;
	case 'test_boost_api':
		test_boost_api();
		exit;
	case 'regenerate_pos_cashier_finalise': // php temp_script.php regenerate_pos_cashier_finalise HQ 2018-11-01 2018-11-30 103
	    $prms = array();
		$prms['branch_code'] = $arg[2];
	    $prms['from_date'] = $arg[3];
	    $prms['to_date'] = $arg[4];
	    $prms['counter_name'] = $arg[5];
	    regenerate_pos_cashier_finalise($prms);
	    exit;
	case 'patch_pp_group_type':
		//php temp_script.php patch_pp_group_type -branch=all -date=2019-03-14
		//php temp_script.php patch_pp_group_type -branch=dev -recent_days=7
		
		patch_pp_group_type($arg);
		exit;
	case 'test_paydibs_api':
		test_paydibs_api();
		exit;
	case 'fix_196_member_points':
		fix_196_member_points();
		exit;
	case 'link_huaho_deleted_sku':	// php temp_script.php link_huaho_deleted_sku -branch=dev -is_run
		link_huaho_deleted_sku($arg);
		exit;
	case 'test_generate_pdf':
		test_generate_pdf();
		exit;
	case 'test_generate_pdf2':
		test_generate_pdf2();
		exit;
	case 'test_push_notification':
		test_push_notification();
		exit;
	case 'copy_sku_photo_to_pos_photo':	// php temp_script.php copy_sku_photo_to_pos_photo -is_run
		copy_sku_photo_to_pos_photo($arg);
		exit;
	case 'fix_promotion_photo_path':	// php temp_script.php fix_promotion_photo_path -is_run
		fix_promotion_photo_path($arg);
		exit;
	case 'convert_innodb':	// php temp_script.php convert_innodb
		convert_innodb();
		exit;
	case 'generate_membership_guid': //to generate membership guid for empty column
		generate_membership_guid();
		exit;
	case 'test_email_now':
		test_email_now();
		exit;
	case 'update_pos_membership_guid':
		update_pos_membership_guid();
		exit;
	case 'generate_sku_items_finalised_cache':	// php temp_script.php generate_sku_items_finalised_cache -branch=all -date_from=2020-05-01
		generate_sku_items_finalised_cache();
		exit;
	case 'delele_wls_member':	// php temp_script.php delele_wls_member 1
		delele_wls_member();
		exit;
	default:
		print "<h3>Unhandled Request</h3>";
		print_r($_REQUEST);
		exit;
}

print "Invalid action, please provide parameter 'a'";

function test(){
	global $con, $smarty, $appCore;

	//print date("Y-m-d H:i:s");
	//$referral_his_guid = 'ca0d36c9-cd24-4649-9790-3e630c075c9a';
	//$result = $appCore->couponManager->checkReferralProgramCouponByReferralHistory($referral_his_guid);
	//var_dump($result);
	
}

function fix_gst_info(){
	global $con;

	// lookup from category
	$q1 = $con->sql_query("select * from category where (inclusive_tax is null or inclusive_tax = 0) or (input_tax = 0 or input_tax is null or input_tax = '') or (output_tax = 0 or output_tax is null or output_tax = '') limit 2000");

	$item_count = 0;
	while($r = $con->sql_fetchassoc($q1)){
		$upd = array();
		if(!$r['inclusive_tax']) $upd['inclusive_tax'] = "inherit";
		if(!$r['input_tax']) $upd['input_tax'] = -1;
		if(!$r['output_tax']) $upd['output_tax'] = -1;

		if(!$upd) continue;

		$con->sql_query("update category set ".mysql_update_by_field($upd)." where id = ".mi($r['id']));
		$item_count++;
	}
	$con->sql_freeresult($q1);

	print "Total $item_count Categories have been updated.<br />";

	// lookup from sku
	$q1 = $con->sql_query("select * from sku where (mst_inclusive_tax is null or mst_inclusive_tax = 0) or (mst_input_tax = 0 or mst_input_tax is null or mst_input_tax = '') or (mst_output_tax = 0 or mst_output_tax is null or mst_output_tax = '') limit 2000");

	$item_count = 0;
	while($r = $con->sql_fetchassoc($q1)){
		$upd = array();
		if(!$r['mst_inclusive_tax']) $upd['mst_inclusive_tax'] = "inherit";
		if(!$r['mst_input_tax']) $upd['mst_input_tax'] = -1;
		if(!$r['mst_output_tax']) $upd['mst_output_tax'] = -1;

		if(!$upd) continue;

		$upd['timestamp'] = "CURRENT_TIMESTAMP";

		$con->sql_query("update sku set ".mysql_update_by_field($upd)." where id = ".mi($r['id']));
		$item_count++;
	}
	$con->sql_freeresult($q1);

	print "Total $item_count SKU have been updated.<br />";

	// lookup from sku_items
	$q1 = $con->sql_query("select * from sku_items where (inclusive_tax is null or inclusive_tax = 0) or (input_tax = 0 or input_tax is null or input_tax = '') or (output_tax = 0 or output_tax is null or output_tax = '') limit 2000");

	$item_count = 0;
	while($r = $con->sql_fetchassoc($q1)){
		$upd = array();
		if(!$r['inclusive_tax']) $upd['inclusive_tax'] = "inherit";
		if(!$r['input_tax']) $upd['input_tax'] = -1;
		if(!$r['output_tax']) $upd['output_tax'] = -1;

		if(!$upd) continue;

		$upd['lastupdate'] = "CURRENT_TIMESTAMP";

		$con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id = ".mi($r['id']));
		$item_count++;
	}
	$con->sql_freeresult($q1);

	print "Total $item_count SKU Items have been updated.<br />";
}

function fix_category_gst_info(){
	global $con, $config;

	// update no_inventory
	if($config['enable_no_inventory_sku']){
        $q_i = $con->sql_query("select id, no_inventory from category where no_inventory<>'inherit'");
		while($r = $con->sql_fetchrow($q_i)){
	        sync_cat_inheritance('no_inventory', $r['id'], $r['no_inventory'], false);
		}
		$con->sql_freeresult($q_i);
	}

	if($config['enable_gst']){
		// update input tax id
		$q_it = $con->sql_query("select id, input_tax from category where input_tax<>-1");
		while($r = $con->sql_fetchrow($q_it)){
	        sync_cat_inheritance_using_id('input_tax', $r['id'], $r['input_tax'], false);
		}
		$con->sql_freeresult($q_it);

		// update output tax id
		$q_ot = $con->sql_query("select id, output_tax from category where output_tax<>-1");
		while($r = $con->sql_fetchrow($q_ot)){
	        sync_cat_inheritance_using_id('output_tax', $r['id'], $r['output_tax'], false);
		}
		$con->sql_freeresult($q_ot);

		// update inclusive tax
		$q_inc = $con->sql_query("select id, inclusive_tax from category where (inclusive_tax<>'inherit' or (level=1 and inclusive_tax='inherit'))");
		while($r = $con->sql_fetchrow($q_inc)){
	        sync_cat_inheritance('inclusive_tax', $r['id'], $r['inclusive_tax'], false);
		}
		$con->sql_freeresult($q_inc);
	}

	print "Done.";
}

function fix_sales_cat_cache(){
	global $con;

	// cache file
	$q1 = $con->sql_query("show tables");
	while($r = $con->sql_fetchrow($q1)){
		if(strpos($r[0],'sku_items_sales_cache_b')!==false){
			$q_success = $con->sql_query("alter table $r[0] add tax_amount double, add disc_amt2 double",false,false);
			if($q_success)	print "$r[0] altered<br>";
		}elseif(strpos($r[0],'category_sales_cache_b')!==false){
			$q_success = $con->sql_query("alter table $r[0] add tax_amount double, add disc_amt double, add disc_amt2 double",false,false);
			if($q_success)	print "$r[0] altered<br>";
		}
	}
	$con->sql_freeresult($q1);

	print "<br>Done.";
}

function generate_consign_designated(){
	global $con, $config;

	// load default info
	$q1 = $con->sql_query("select *
						   from gst_settings");
	while($r = $con->sql_fetchassoc($q1)){
		$form[$r['setting_name']] = $r['setting_value'];
	}
	$con->sql_freeresult($q1);

	if($config['consignment_modules']){
		// get GST code "ZRE"
		if(!$form['export_gst_type']){
			$q1 = $con->sql_query("select * from gst where active=1 and type='supply' and code='ZRE' order by code limit 1");
			$gst_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			$upd = array();
			$upd['setting_name'] = 'export_gst_type';
			$upd['setting_value'] = mi($gst_info['id']);
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("replace into gst_settings ".mysql_insert_by_field($upd));
			print "export_gst_type replaced.<br>";
		}

		// get GST code "ZRL"
		if(!$form['designated_gst_type']){
			$q1 = $con->sql_query("select * from gst where active=1 and type='supply' and code='ZRL' order by code limit 1");
			$gst_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			$upd = array();
			$upd['setting_name'] = 'designated_gst_type';
			$upd['setting_value'] = mi($gst_info['id']);
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("replace into gst_settings ".mysql_insert_by_field($upd));
			print "designated_gst_type replaced.<br>";
		}
	}
	print "Done.";
}

function fix_sku_description_encoding(){
	global $con;

	$q1 = $con->sql_query("select id,description,receipt_description,artno from sku_items where id=1 order by id");
	$total_count = $con->sql_numrows($q1);
	$curr_count = 0;
	while($r = $con->sql_fetchassoc($q1)){
		$upd = array();
		//$upd['description'] = iconv("UTF-8", "ISO-8859-1", $r['description']);
		$upd['description'] = trim(utf8_encode($r['description']));
		//$upd['receipt_description'] = iconv("UTF-8", "ISO-8859-1", $r['receipt_description']);
		$upd['receipt_description'] = trim(utf8_encode($r['receipt_description']));
		//$upd['artno'] = trim(utf8_encode($r['artno']));

		$con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id=".$r['id']);

		$curr_count ++;
		print "\r$curr_count / $total_count. . . .";
	}
	$con->sql_freeresult(q1);
	print "\nDone.\n";
}

function fix_uom_encoding(){
	global $con;

	$q1 = $con->sql_query("select * from uom order by id");
	$total_count = $con->sql_numrows($q1);
	$curr_count = 0;
	while($r = $con->sql_fetchassoc($q1)){
		$upd = array();
		//$upd['code'] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $r['code']);
		$upd['code'] = trim(utf8_encode($r['code']));
		$upd['description'] = trim(utf8_encode($r['description']));

		$con->sql_query("update uom set ".mysql_update_by_field($upd)." where id=".$r['id']);

		$curr_count ++;
		print "\r$curr_count / $total_count. . . .";
	}
	$con->sql_freeresult(q1);
	print "\nDone.\n";
}

function fix_segi_sku_group_date_control(){
	global $con;

	$sku_group_id = mi($_REQUEST['sku_group_id']);
	if(!$sku_group_id)	die('No Group');

	$sku_group_id_list = array($sku_group_id);
	//$sku_group_id_list = array(1);
	foreach($sku_group_id_list as $sku_group_id){
		print "Checking ID#$sku_group_id\n";
		$con->sql_query("select max(from_date) as from_date, max(to_date) as to_date from sku_group_vp_date_control where branch_id=1 and sku_group_id=$sku_group_id");
		$date_info = $con->sql_fetchassoc();
		$con->sql_freeresult();

		if(!$date_info['from_date'] || !$date_info['to_date']){
			print "No data.\n";
			continue;
		}

		// select all row without date
		$q1 = $con->sql_query("select sgi.*,si.id as sid
from sku_group_item sgi
join sku_items si on si.sku_item_code=sgi.sku_item_code
left join sku_group_vp_date_control sgivp on sgivp.branch_id=sgi.branch_id and sgivp.sku_group_id=sgi.sku_group_id and sgivp.sku_item_id=si.id
where sgi.branch_id=1 and sgi.sku_group_id=$sku_group_id and sgivp.sku_item_id is null");
		$insert_count = 0;
		while($r = $con->sql_fetchassoc($q1)){
			$upd = array();
			$upd['branch_id'] = $r['branch_id'];
			$upd['sku_group_id'] = $r['sku_group_id'];
			$upd['sku_item_id'] = $r['sid'];
			$upd['from_date'] = $date_info['from_date'];
			$upd['to_date'] = $date_info['to_date'];

			$con->sql_query("replace into sku_group_vp_date_control ".mysql_insert_by_field($upd));
			$insert_count++;
			print "\r$insert_count item insert.....";
		}
		$con->sql_freeresult($q1);
	}

	print "Done.\n";
}

function remove_sageUBS(){
	$filename = 'export_account/SageUBS.php';

	if(!file_exists($filename)){
		print "File Not Exists";
		exit;
	}

	if(unlink($filename)){
		print "Delete Successfully.";
	}else{
		print "Delete Failed.";
	}
}

function fix_gst_zero_rate(){
	global $con;

	$q1 = $con->sql_query("select * from gst");

	while($r = $con->sql_fetchassoc($q1)){
		$gst_list[$r['id']] = $r;
	}
	$con->sql_freeresult($q1);

	$ttl_count = 0;
	$q1 = $con->sql_query("select gi.*
						   from grn
						   left join grn_items gi on gi.grn_id = grn.id and gi.branch_id = grn.branch_id
						   left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
						   left join gst on gst.id = gi.gst_id
						   where ((gi.gst_id > 0 and gi.gst_rate = 0) or (gi.acc_gst_id > 0 and gi.acc_gst_rate = 0)) and grr.rcv_date >= '2015-04-01' and gi.gst_rate != gst.rate
						   order by gi.id");
	while($r = $con->sql_fetchassoc($q1)){
		$upd = array();
		$is_inclusive_tax = get_sku_gst("inclusive_tax", $r['sku_item_id']);

		if($r['gst_id'] && !$r['gst_rate'] && $gst_list[$r['gst_id']]['rate'] > 0){
			$upd['gst_rate'] = $gst_list[$r['gst_id']]['rate'];
		}

		if($r['selling_gst_id'] && !$r['selling_gst_rate'] && $gst_list[$r['selling_gst_id']]['rate'] > 0){
			$r['selling_gst_rate'] = $upd['selling_gst_rate'] = $gst_list[$r['selling_gst_id']]['rate'];
		}

		if($r['acc_gst_id'] && !$r['acc_gst_rate'] && $gst_list[$r['acc_gst_id']]['rate'] > 0){
			$upd['acc_gst_rate'] = $gst_list[$r['acc_gst_id']]['rate'];
		}

		if($upd['selling_gst_rate']){
			$prms['selling_price'] = $r['selling_price'];
			$prms['inclusive_tax'] = $is_inclusive_tax;
			$prms['gst_rate'] = $upd['selling_gst_rate'];
			$gst_sp_info = calculate_gst_sp($prms);

			if($is_inclusive_tax == "yes"){
				$upd['gst_selling_price'] = $r['selling_price'];
				$upd['selling_price'] = $gst_sp_info['gst_selling_price'];
			}else{
				$upd['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
			}
		}

		if($upd){
			//print "update grn_items set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])." <br />";
			$con->sql_query("update grn_items set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
			$ttl_count++;
		}
	}
	$con->sql_freeresult($q1);

	Print "Total $ttl_count record(s) GRN items have been updated.<br />";

	$ttl_count = 0;
	$q1 = $con->sql_query("select pi.*, po.deliver_to
						   from po
						   left join po_items pi on pi.po_id = po.id and pi.branch_id = po.branch_id
						   left join gst on gst.id = pi.cost_gst_id
						   where pi.cost_gst_id > 0 and pi.cost_gst_rate = 0 and po.po_date >= '2015-04-01' and pi.cost_gst_rate != gst.rate
						   order by id");

	while($r = $con->sql_fetchassoc($q1)){
		$upd = array();
		$is_inclusive_tax = get_sku_gst("inclusive_tax", $r['sku_item_id']);
		$deliver_to = unserialize($r['deliver_to']);
		$selling_price_allocation = unserialize($r['selling_price_allocation']);

		if($r['cost_gst_id'] && !$r['gst_rate'] && $gst_list[$r['cost_gst_id']]['rate'] > 0){
			$upd['cost_gst_rate'] = $gst_list[$r['cost_gst_id']]['rate'];
		}

		if($r['selling_gst_id'] && !$r['selling_gst_rate'] && $gst_list[$r['selling_gst_id']]['rate'] > 0){
			$r['selling_gst_rate'] = $upd['selling_gst_rate'] = $gst_list[$r['selling_gst_id']]['rate'];
		}

		if($upd['selling_gst_rate']){
			if(is_array($deliver_to)){ // is deliver to multiple branches
				foreach($deliver_to as $k=>$v){
					$selling_price=$selling_price_allocation[$v];
					$prms['selling_price'] = $selling_price;
					$prms['inclusive_tax'] = $is_inclusive_tax;
					$prms['gst_rate'] = $upd['selling_gst_rate'];
					$gst_sp_info = calculate_gst_sp($prms);

					if($is_inclusive_tax == "yes"){
						$temp['gst_selling_price_allocation'][$v] = $selling_price_allocation[$v];
						$temp['selling_price_allocation'][$v] = round($gst_sp_info['gst_selling_price'], 2);
					}else{
						$temp['gst_selling_price_allocation'][$v] = $gst_sp_info['gst_selling_price'];
					}
				}
				if($temp['selling_price_allocation']) $upd['selling_price_allocation'] = serialize($temp['selling_price_allocation']);
				if($temp['gst_selling_price_allocation']) $upd['gst_selling_price_allocation'] = serialize($temp['gst_selling_price_allocation']);
			}else{ // deliver to one branch
				$prms['selling_price'] = $r['selling_price'];
				$prms['inclusive_tax'] = $is_inclusive_tax;
				$prms['gst_rate'] = $upd['selling_gst_rate'];
				$gst_sp_info = calculate_gst_sp($prms);

				if($is_inclusive_tax == "yes"){
					$upd['gst_selling_price'] = $r['selling_price'];
					$upd['selling_price'] = $gst_sp_info['gst_selling_price'];
				}else{
					$upd['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
				}
			}
		}

		//print "update po_items set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])."<br />";
		$con->sql_query("update po_items set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
		$ttl_count++;
	}
	$con->sql_freeresult($q1);

	Print "Total $ttl_count record(s) PO items have been updated.<br />";
}

function fix_gst_sp(){
	global $con, $config;

	$form = $_REQUEST;
	$q1 = $con->sql_query("select * from branch where active=1");
	while($r = $con->sql_fetchassoc($q1)){
		$blist[$r['id']] = $r;
	}
	$con->sql_freeresult($q1);

	$q1 = $con->sql_query("select si.*, sku.category_id, sku.mst_output_tax, sku.mst_inclusive_tax, sku.default_trade_discount_code,
						   if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit', cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)) as real_inclusive_tax
						   from sku
						   left join sku_items si on si.sku_id = sku.id
						   left join category cat on cat.id = sku.category_id
						   left join category_cache cc on cc.category_id=cat.id
						   where si.active=1 and selling_price > 0 and cc.p3 in (18, 66)
						   having real_inclusive_tax='yes'");

	if($form['is_print_result']){
		header('Content-type: text/csv');
		header('Content-disposition: attachment;filename=price_change.csv');
		header("Content-Transfer-Encoding: UTF-8");
		header('Pragma: no-cache');
		header("Expires: 0");

		$f = fopen("php://output", "w");

		$tmp_row = array("SKU ITEM CODE", "DESCRIPTION", "BRANCH", "CURRENT PRICE", "CURRENT PRICE INC. GST");

		foreach($config['sku_multiple_selling_price'] as $s){
			$tmp_row[] = strtoupper($s)." CURRENT PRICE";
			$tmp_row[] = strtoupper($s)." CURRENT PRICE INC. GST";
		}

		fputcsv($f, $tmp_row);
		unset($tmp_row);
	}

	while($r = $con->sql_fetchassoc($q1)){
		foreach($blist as $bid=>$b){
			if($form['is_print_result']){
				$tmp_row = array();
				$tmp_row = array($r['sku_item_code'], $r['description'], $b['code']);
			}
			// check normal price
			$q2 = $con->sql_query("select * from sku_items_price where sku_item_id = ".mi($r['id'])." and branch_id = ".mi($bid). " and date(last_update) >= '2015-04-01'");
			$price_changed = $con->sql_numrows($q2);
			$con->sql_freeresult($q2);

			if($price_changed > 0) continue;

			// get normal price
			$q2 = $con->sql_query("select * from sku_items_price where sku_item_id = ".mi($r['id'])." and branch_id = ".mi($bid). " and date(last_update) < '2015-04-01' order by last_update desc limit 1");
			$price_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);

			if(!$price_info){
				$r['trade_discount_code'] = $r['default_trade_discount_code'];
				$selling_price = $r['selling_price'];
			}else{
				$r['trade_discount_code'] = $price_info['trade_discount_code'];
				$selling_price = $price_info['price'];
			}

			$output_tax = get_sku_gst("output_tax", $r['id']);
			$inclusive_tax = $r['real_inclusive_tax'];
			$gst_rate = $output_tax['rate'];
			unset($output_tax);

			if($inclusive_tax != "yes" || !$gst_rate) continue; // if found selling price after gst, skip it

			$proposed_selling_price = $selling_price + (round($selling_price * $gst_rate / 100, 2));
			$proposed_selling_price = process_gst_sp_rounding_condition($proposed_selling_price);

			if($proposed_selling_price == 0) $proposed_selling_price = $r['selling_price'];

			if($proposed_selling_price != $r['selling_price']){
				if(!$form['is_print_result']){
					$upd = array();
					$upd['branch_id'] = $bid;
					$upd['sku_item_id'] = $r['id'];
					$upd['price'] = $proposed_selling_price;
					// get last cost from GRN
					$prm = array();
					$prm['branch_id'] = $bid;
					$prm['sku_item_id'] = $r['id'];
					$temp = get_last_cost($prm);
					$upd['cost'] = $temp['cost'];
					$upd['trade_discount_code'] = $r['trade_discount_code'];
					$upd['source'] = $temp['source'];
					$upd['user_id'] = 1;
					$upd['last_update'] = $upd['added'] = "CURRENT_TIMESTAMP";

					// insert price history
					$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "price", "cost", "trade_discount_code", "source", "user_id", "added")));
					//$a="replace into sku_items_price_history ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "price", "cost", "trade_discount_code", "source", "user_id", "added"));

					//print $a."<br />";

					// replace into normal selling price
					$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "price", "cost", "trade_discount_code", "last_update")));

					//$b="replace into sku_items_price ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "price", "cost", "trade_discount_code", "last_update"));

					//print $b."<br />";
				}else{
					$tmp_row[] = $selling_price;
					$tmp_row[] = $proposed_selling_price;
				}
			}else{
				if($form['is_print_result']){
					$tmp_row[] = "-";
					$tmp_row[] = "-";
				}
			}

			foreach($config['sku_multiple_selling_price'] as $s){
				// get multiple selling price
				$q2 = $con->sql_query("select * from sku_items_mprice where branch_id = ".mi($bid)." and sku_item_id = ".mi($r['id'])." and type = ".ms($s)." and date(last_update) >= '2015-04-01'");
				$mprice_changed = $con->sql_numrows($q2);
				$con->sql_freeresult($q2);

				if($mprice_changed > 0) continue;

				// get multiple price
				$q2 = $con->sql_query("select * from sku_items_mprice where sku_item_id = ".mi($r['id'])." and branch_id = ".mi($bid)." and type = ".ms($s)." and date(last_update) < '2015-04-01' order by last_update desc limit 1");
				$mprice_info = $con->sql_fetchassoc($q2);
				$con->sql_freeresult($q2);

				if(!$mprice_info){
					$r['trade_discount_code'] = $r['default_trade_discount_code'];
					$selling_price = $r['selling_price'];
				}else{
					$r['trade_discount_code'] = $mprice_info['trade_discount_code'];
					$selling_price = $mprice_info['price'];
				}

				$proposed_selling_price = $selling_price + (round($selling_price * $gst_rate / 100, 2));
				$proposed_selling_price = process_gst_sp_rounding_condition($proposed_selling_price);

				if($proposed_selling_price == 0) $proposed_selling_price = $r['selling_price'];

				if($proposed_selling_price != $r['selling_price']){
					if(!$form['is_print_result']){
						$upd = array();
						$upd['branch_id'] = $bid;
						$upd['sku_item_id'] = $r['id'];
						$upd['type'] = $s;
						$upd['price'] = $proposed_selling_price;
						$upd['trade_discount_code'] = $r['trade_discount_code'];
						$upd['user_id'] = 1;
						$upd['last_update'] = $upd['added'] = "CURRENT_TIMESTAMP";

						// insert mprice history
						$con->sql_query("replace into sku_items_mprice_history ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "type", "price", "trade_discount_code", "user_id", "added")));
						//$a = "replace into sku_items_mprice_history ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "type", "price", "trade_discount_code", "user_id", "added"));
						//print $a."<br />";

						// replace into multiple selling price
						$con->sql_query("replace into sku_items_mprice ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "price", "trade_discount_code", "type", "last_update")));
						//$b = "replace into sku_items_mprice ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "price", "trade_discount_code", "type", "last_update"));
						//print $b."<br />";
					}else{
						$tmp_row[] = $selling_price;
						$tmp_row[] = $proposed_selling_price;
					}
				}else{
					if($form['is_print_result']){
						$tmp_row[] = "-";
						$tmp_row[] = "-";
					}
				}
			}
			fputcsv($f, $tmp_row);

			// capture log
			//log_br(1, "GST PRICE WIZARD", 0, "Price Change for ".$form['sku_item_code'][$sid]." to ".mf($new_sp)." (Discount: ".$price_info['trade_discount_code'].", Branch ".get_branch_code($bid).")");
		}
	}
	$con->sql_freeresult($q1);
}

function get_last_cost($prm){
	global $con;

	// todo: if cost 0, find last cost from GRN/PO
	$form = array();

	$q1 = $con->sql_query("select round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/uom.fraction,3) as cost
	from grn_items
	left join uom on uom_id = uom.id
	left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
	left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
	where grn_items.branch_id = ".mi($prm['branch_id'])." and grn.approved and sku_item_id=".mi($prm['sku_item_id'])."
	having cost > 0
	order by grr.rcv_date desc limit 1");
	$c = $con->sql_fetchrow($q1);
	$con->sql_freeresult($q1);
	//print "using GRN $c[0]";
	if ($c){
		$form['cost'] = $c[0];
		$form['source'] = 'GRN';
	}

	if ($form['cost']==0){
		$q1 = $con->sql_query("select round(po_items.order_price/po_items.order_uom_fraction,3) as cost
		from po_items
		left join po on po_id = po.id and po.branch_id = po.branch_id
		where po.active and po.approved and po_items.branch_id = ".mi($prm['branch_id'])." and sku_item_id=".mi($prm['sku_item_id'])."
		having cost > 0
		order by po.po_date desc limit 1");
		$c = $con->sql_fetchrow($q1);
		$con->sql_freeresult($q1);
		//print "using PO $c[0]";
		if ($c)
		{
			$form['cost'] = $c[0];
			$form['source'] = 'PO';
		}
	}

	if ($form['cost']==0){
		$q1 = $con->sql_query("select cost_price from sku_items where id=".mi($prm['sku_item_id']));
		$c = $con->sql_fetchrow($q1);
		$con->sql_freeresult($q1);
		//print "using MASTER $c[0]";
		if ($c)
		{
			$form['cost'] = $c[0];
			$form['source'] = 'MASTER SKU';
		}
	}

	return $form;
}

function fix_pos_change_amt(){
	global $con;

	$is_update = mi($_REQUEST['is_update']);
	$bid = mi($_REQUEST['branch_id']);

	$branch_got_return_amt = array();
	$con->sql_query("select * from pos_settings where setting_name='return_amount' and setting_value>0");
	while($r = $con->sql_fetchassoc()){
		$branch_got_return_amt[$r['branch_id']] = 1;
	}
	$con->sql_freeresult();

	$filter = array();
	$filter[] = "p.date>'2015-04-01' and round(p.amount,2) != round (p.amount_tender-p.amount_change, 2) and pf.finalized=0 and p.cancel_status=0 and p.amount<0 and p.amount_tender=0";
	if($bid>0)	$filter[] = "p.branch_id=$bid";

	$filter = join(' and ', $filter);
	$sql = "select p.*
		from pos p
		join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date
		where $filter
		order by p.branch_id,p.date,p.id";

	//print "$sql<br>";

	$q1 = $con->sql_query($sql);
	$update_count = 0;
	$problem_found = 0;
	while($r = $con->sql_fetchassoc($q1)){
		$amt_change = 0;
		if($branch_got_return_amt[$r['branch_id']]){
			$amt_change = $r['amount'];
		}

		$upd = array();
		$upd['amount_change'] = $amt_change*-1;

		if($is_update){
			$sql = "update pos set ".mysql_update_by_field($upd)." where branch_id=".$r['branch_id']." and counter_id=".$r['counter_id']." and date=".ms($r['date'])." and id=".$r['id'];
			$con->sql_query($sql);
			print $sql."<br>";
			$update_count ++;
		}

		print "Branch ID#$r[branch_id], Date#$r[date], Receipt #$r[receipt_no] update change amount from $r[amount_change] to ".$upd['amount_change']."<br>";

		$problem_found++;
	}
	$con->sql_freeresult($q1);

	print "$problem_found problem found, $update_count updated.";
}

function fix_segi_sku_group(){
	global $con;

	$connection2 = array("segiarms.dyndns.org:33060", "arms", "segi7344", "armshq");
	$con2 = connect_db($connection2[0], $connection2[1], $connection2[2], $connection2[3]);
	if(!$con2)  die("cant connect to con2\n");

	$sku_group_id_list = array(39,37);
	$str_id_list = join(',', $sku_group_id_list);

	$q1 = $con->sql_query("select * from sku_group_item where branch_id=1 and sku_group_id in ($str_id_list)");
	print "count: ".$con->sql_numrows($q1)."\n";
	while($r = $con->sql_fetchassoc($q1)){
		$con2->sql_query("replace into sku_group_item ".mysql_insert_by_field($r));
	}
	$con->sql_freeresult($q1);

	$q2 = $con->sql_query("select * from sku_group_vp_date_control where branch_id=1 and sku_group_id in ($str_id_list)");
	while($r = $con->sql_fetchassoc($q2)){
		$con2->sql_query("replace into sku_group_vp_date_control ".mysql_insert_by_field($r));
	}
	$con->sql_freeresult($q2);

	print "Done.\n";
}

function fix_gra_return_ctn(){
	global $con, $config;

	$q1 = $con->sql_query("select * from dnote where ref_table = 'gra' and active=1");

	if($con->sql_numrows($q1) > 0){

		while($r = $con->sql_fetchassoc($q1)){
			$q2 = $con->sql_query("select * from dnote_items where dnote_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));

			// get all returned qty
			$ttl_return_ctn = 0;
			while($r1 = $con->sql_fetchassoc($q2)){
				$ttl_return_ctn += $r1['qty'];
			}
			$con->sql_freeresult($q2);

			// update into misc_info
			if($ttl_return_ctn){
				$q2 = $con->sql_query("select * from gra where id = ".mi($r['ref_id'])." and branch_id = ".mi($r['branch_id']));
				$gra_info = $con->sql_fetchassoc($q2);
				$con->sql_freeresult($q2);

				$misc_info = unserialize($gra_info['misc_info']);
				$misc_info['return_ctn'] = strval(round($ttl_return_ctn, $config['global_qty_decimal_points']));

				$upd = array();
				$upd['misc_info'] = serialize($misc_info);

				$con->sql_query("update gra set ".mysql_update_by_field($upd)." where id = ".mi($r['ref_id'])." and branch_id = ".mi($r['branch_id']));
				//print "update grr set ".mysql_update_by_field($upd)." where id = ".mi($r['ref_id'])." and branch_id = ".mi($r['branch_id'])."<br />";
				print "Updated GRA#".mi($r['ref_id'])." BID#".mi($r['branch_id'])."<br />";
			}
		}
		$con->sql_freeresult($q1);
	}

	Print "Done.";
}

function fix_dnote_gst_info(){
	global $con, $config;

	// select all from dnote
	$q1 = $con->sql_query("select * from dnote where active=1 and is_under_gst = 1");

	$diff_rate_count = $upd_count = 0;
	if($con->sql_numrows($q1) > 0){
		while($r = $con->sql_fetchassoc($q1)){
			$q2 = $con->sql_query("select * from dnote_items where dnote_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));

			while($r1 = $con->sql_fetchassoc($q2)){
				if($r1['gst_id'] > 0){
					$gst_info = get_sku_gst("input_tax", $r1['sku_item_id']);
					$upd = array();
					$upd['gst_id'] = $gst_info['id'];
					$upd['gst_code'] = $gst_info['code'];
					$upd['gst_rate'] = $gst_info['rate'];
					$upd['gst_indicator'] = $gst_info['indicator_receipt'];

					if($gst_info['rate'] != $r1['gst_rate']){ // found not the same rate, don't update?
						print "Type:".$r['ref_table']." ID:".$r['ref_id']." BID:".$r1['branch_id']." Current Rate: ".$r1['gst_rate']." New Rate: ".$gst_info['rate']."<br />";
						$diff_rate_count++;
					}else{
						$con->sql_query("update dnote_items set ".mysql_update_by_field($upd)." where id = ".mi($r1['id'])." and branch_id = ".mi($r1['branch_id']));
						$upd_count++;
					}
				}
			}

		}
		if($diff_rate_count) print "Total got ".mi($diff_rate_count)." items that GST rate is unmatched.<br />";
		print "Updated ".mi($upd_count)." items.<br />";
	}
}

function clone_sku_group_items_to(){
	global $con;

	/*
		temp_script.php?is_http=1&a=clone_sku_group_items_to&from_code=19307&to_code=19301
		temp_script.php?is_http=1&a=clone_sku_group_items_to&from_code=19307&to_code=19303
	*/
	$from_code = trim($_REQUEST['from_code']);
	$to_code = trim($_REQUEST['to_code']);

	if(!$from_code)	die("Please provide 'from_code'.");
	if(!$to_code)	die("Please provide 'to_code'.");

	$con->sql_query("select * from sku_group where code=".ms($from_code));
	$from_group = $con->sql_fetchassoc();
	$con->sql_freeresult();

	if(!$from_group)	die("SKU Group $from_code not found.");

	$con->sql_query("select * from sku_group where code=".ms($to_code));
	$to_group = $con->sql_fetchassoc();
	$con->sql_freeresult();

	if(!$to_group)	die("SKU Group $to_code not found.");

	$bid_1 = mi($from_group['branch_id']);
	$sg_id_1 = mi($from_group['sku_group_id']);

	$bid_2 = mi($to_group['branch_id']);
	$sg_id_2 = mi($to_group['sku_group_id']);

	// select item from group 1
	$con->sql_query("select sgi.*, si.id as sid
		from sku_group_item sgi
		join sku_items si on si.sku_item_code=sgi.sku_item_code
		where sgi.branch_id=$bid_1 and sgi.sku_group_id=$sg_id_1 order by sgi.sku_item_code");
	$item_list = array();
	while($r = $con->sql_fetchassoc()){
		$item_list[] = $r;
	}
	$con->sql_freeresult();

	// delete data from group 2
	$con->sql_query("delete from sku_group_item where branch_id=$bid_2 and sku_group_id=$sg_id_2");
	$con->sql_query("delete from sku_group_vp_date_control where branch_id=$bid_2 and sku_group_id=$sg_id_2");

	$clone_count = 0;

	// loop to insert items
	foreach($item_list as $r){
		$sid = mi($r['sid']);
		if($sid<=0)	continue;

		$upd = array();
		$upd['branch_id'] = $bid_2;
		$upd['sku_group_id'] = $sg_id_2;
		$upd['user_id'] = $r['user_id'];
		$upd['sku_item_code'] = $r['sku_item_code'];
		$upd['added_by'] = $r['added_by'];
		$upd['added_timestamp'] = $r['added_timestamp'];
		$con->sql_query("replace into sku_group_item ".mysql_insert_by_field($upd));

		// vp date control
		$q_vp = $con->sql_query("select * from sku_group_vp_date_control where branch_id=$bid_1 and sku_group_id=$sg_id_1 and sku_item_id=$sid order by from_date");
		while($vp_r = $con->sql_fetchassoc($q_vp)){
			$upd_vp = array();
			$upd_vp['branch_id'] = $bid_2;
			$upd_vp['sku_group_id'] = $sg_id_2;
			$upd_vp['sku_item_id'] = $sid;
			$upd_vp['from_date'] = $vp_r['from_date'];
			$upd_vp['to_date'] = $vp_r['to_date'];

			$con->sql_query("replace into sku_group_vp_date_control ".mysql_insert_by_field($upd_vp));
		}
		$con->sql_freeresult($q_vp);

		$clone_count++;
	}

	print "$clone_count items cloned. Done.";
}

function fix_category_sales_cache(){
	global $con, $arg, $config;

	print "Start Fix Category Sales Cache\n";
	$dummy = array_shift($arg);
	$dummy = array_shift($arg);
	$b_list = array();

	while($cmd = array_shift($arg)){
		list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
		if($cmd_head == "-branch"){
			$branch_filter = '';
			if($cmd_value == 'all'){
				if(!$config['single_server_mode']){
					$bcode = BRANCH_CODE;
					$branch_filter = 'where code='.ms($bcode);
				}
			}else{
				$bcode = trim($cmd_value);
				$branch_filter = 'where code='.ms($bcode);
			}

			$con->sql_query("select id,code from branch $branch_filter order by sequence,code");
			while($r = $con->sql_fetchassoc()){
				$b_list[] = $r;
			}
			$con->sql_freeresult();

			if(!$b_list && $bcode)	die("Branch $bcode not found.\n");

		}elseif($cmd_head == "-date"){
			$date = date("Y-m-d", strtotime($cmd_value));
		}elseif($cmd_head == "-date_from"){
			$date_from = date("Y-m-d", strtotime($cmd_value));
		}elseif($cmd_head == "-date_to"){
			$date_to = date("Y-m-d", strtotime($cmd_value));
		}elseif($cmd_head == "-is_run"){
			$is_run=1;
		}else{
			print "Unknown command $cmd\n";
			exit;
		}
	}
	if(!$b_list)	die("No branch is selected\n");

	//print_r($b_list);exit;
	foreach($b_list as $b_info){
		$bid = $b_info['id'];
		$bcode = $b_info['code'];
		$date_list = array();

		print "Branch: ID#$bid, $bcode\n";
		if($date){
			print "Date: $date\n";
			$date_list[] = $date;
		}elseif($date_from || $date_to){
			if($date_from)	print "Date From: $date_from\n";
			if($date_to)	print "Date To: $date_to\n";
		}else{
			print "Date: All\n";
		}

		$cat_cache_tbl = "category_sales_cache_b".$bid;
		$sku_cache_tbl = "sku_items_sales_cache_b".$bid;
		$dept_trans_cache = "dept_trans_cache_b".$bid;
		
		if(!$date_list){
			$d_filter = array();
			if($date_from)	$d_filter[] = "date>=".ms($date_from);
			if($date_to)	$d_filter[] = "date<=".ms($date_to);

			if($d_filter)	$d_filter = "where ".join(' and ', $d_filter);
			else	$d_filter = '';

			$con->sql_query("select distinct(date) as d from $sku_cache_tbl $d_filter order by d");
			while($r = $con->sql_fetchassoc()){
				$date_list[] = $r['d'];
			}
			$con->sql_freeresult();
		}

		print "Total Day(s): ".count($date_list)."\n";

		if(!$date_list){
			print "Nothing to run.\n";
			continue;
		}

		// loop each date
		foreach($date_list as $d){
			$pos_count_matched = false;
			$figure_matched = false;
			
			// check pos count first
			$con->sql_query("select count(distinct pi.branch_id, pi.counter_id, pi.date, pi.pos_id) as pos_count from
				pos p
				join pos_items pi on pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id
				where pi.branch_id=$bid and pi.date=".ms($d)." and p.cancel_status=0");
			$pos_info = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$con->sql_query("select count(distinct dt.date,dt.counter_id,dt.pos_id) as pos_count from $dept_trans_cache dt where dt.date=".ms($d));
			$tran_info = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($pos_info['pos_count'] == $tran_info['pos_count']){
				$pos_count_matched = true;
			}
			
			if($pos_count_matched){
				// compare sales and gst
				$con->sql_query("select round(sum(amount),2) as amount, round(sum(disc_amt),2) as disc_amt,round(sum(disc_amt2)) as disc_amt2,round(sum(cost),5) as cost,round(sum(qty),5) as qty,
				round(sum(fresh_market_cost),2) as fresh_market_cost,round(sum(tax_amount),2) as tax_amount
				from $cat_cache_tbl where date=".ms($d));
				$cat_info = $con->sql_fetchassoc();
				$cat_info['amount'] = round($cat_info['amount'], 2);
				$cat_info['disc_amt'] = round($cat_info['disc_amt'], 2);
				$cat_info['disc_amt2'] = round($cat_info['disc_amt2'], 2);
				$cat_info['cost'] = round($cat_info['cost'], 5);
				$cat_info['qty'] = round($cat_info['qty'], 5);
				$cat_info['fresh_market_cost'] = round($cat_info['fresh_market_cost'], 2);
				$cat_info['tax_amount'] = round($cat_info['tax_amount'], 2);
				
				$con->sql_freeresult();

				$con->sql_query("select round(sum(amount),2) as amount, round(sum(disc_amt),2) as disc_amt,round(sum(disc_amt2)) as disc_amt2,round(sum(cost),5) as cost,round(sum(qty),5) as qty,
				round(sum(fresh_market_cost),2) as fresh_market_cost,round(sum(tax_amount),2) as tax_amount
				from $sku_cache_tbl where date=".ms($d));
				$sku_info = $con->sql_fetchassoc();
				$sku_info['amount'] = round($sku_info['amount'], 2);
				$sku_info['disc_amt'] = round($sku_info['disc_amt'], 2);
				$sku_info['disc_amt2'] = round($sku_info['disc_amt2'], 2);
				$sku_info['cost'] = round($sku_info['cost'], 5);
				$sku_info['qty'] = round($sku_info['qty'], 5);
				$sku_info['fresh_market_cost'] = round($sku_info['fresh_market_cost'], 2);
				$sku_info['tax_amount'] = round($sku_info['tax_amount'], 2);
				
				$con->sql_freeresult();

				if($cat_info['amount'] == $sku_info['amount'] && $cat_info['disc_amt'] == $sku_info['disc_amt'] && $cat_info['disc_amt2'] == $sku_info['disc_amt2']
				&& $cat_info['cost'] == $sku_info['cost'] && $cat_info['qty'] == $sku_info['qty'] && $cat_info['fresh_market_cost'] == $sku_info['fresh_market_cost']
				&& $cat_info['tax_amount'] == $sku_info['tax_amount']){
					// figure match - no need regen
					$figure_matched = true;
					//print "$d figure matched.\n";
					//continue;
				}else{
					if($cat_info['amount'] != $sku_info['amount'])	print "amount: ".$cat_info['amount']." vs ".$sku_info['amount'];
					if($cat_info['disc_amt'] != $sku_info['disc_amt'])	print "disc_amt: ".$cat_info['disc_amt']." vs ".$sku_info['disc_amt'];
					if($cat_info['disc_amt2'] != $sku_info['disc_amt2'])	print "disc_amt2: ".$cat_info['disc_amt2']." vs ".$sku_info['disc_amt2'];
					if($cat_info['cost'] != $sku_info['cost'])	print "cost: ".$cat_info['cost']." vs ".$sku_info['cost'];
					if($cat_info['qty'] != $sku_info['qty'])	print "qty: ".$cat_info['qty']." vs ".$sku_info['qty'];
					if($cat_info['fresh_market_cost'] != $sku_info['fresh_market_cost'])	print "fresh_market_cost: ".$cat_info['fresh_market_cost']." vs ".$sku_info['fresh_market_cost'];
					if($cat_info['tax_amount'] != $sku_info['tax_amount'])	print "tax_amount: ".$cat_info['tax_amount']." vs ".$sku_info['tax_amount'];
				}
			}
			
			if($pos_count_matched && $figure_matched){
				print "$d no problem found.\n";
				continue;
			}
			
			if($is_run){
				print "Regen for $d...";

				if(!$pos_count_matched){	// need refinalise
					update_sales_cache($bid, $d);
				}elseif(!$figure_matched){	// just need replace data
					// delete cat cache
					$con->sql_query("delete from $cat_cache_tbl where date=".ms($d));

					// replace with sku sales
					$con->sql_query("replace into $cat_cache_tbl
						(date, category_id, sku_type, year, month, amount, cost, qty, fresh_market_cost, tax_amount, disc_amt, disc_amt2)
						select date, sku.category_id, sku.sku_type, year, month, sum(amount), sum(cost), sum(qty), sum(fresh_market_cost), sum(tax_amount), sum(disc_amt), sum(disc_amt2)
						from $sku_cache_tbl pos
						left join sku_items si on si.id=pos.sku_item_id
						left join sku on sku.id=si.sku_id
						where date=".ms($d)." group by date, category_id, sku_type");
				}				
				print "OK\n";
			}else{
				//print "$d need regen.\n";
				if(!$pos_count_matched){
					print "$d pos count not match, need refinalise.\n";
				}elseif(!$figure_matched){
					print "$d sales not match, need regen.\n";
				}
			}

		}
	}


	print "Done.\n";
}

function my_encode($number) {
	$alphabet = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	//$alphabet = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

	$base = strlen($alphabet);
	$result = '';
	while (bccomp($number, 0) == 1) {
	$result = $alphabet{bcmod($number, $base)}.$result;
	$number = bcdiv($number, $base, 0);
	}
	return $result;
}

function my_decode($number) {
	$alphabet = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	//$alphabet = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$base = strlen($alphabet);
	$alphabet = array_flip(str_split($alphabet));
	$c = strlen($number)-1;
	$result = 0;
	for ($i = 0; $i <= $c; $i++) {
	$temp = bcmul($alphabet[$number{$i}], bcpow($base, $c-$i));
	$result = bcadd($result, $temp);
	}
	return $result;
}

function generate_unique_ref_code($date, $bid, $cid){
	$d = (strtotime(date("Y-m-d", strtotime($date)))-strtotime("2009-12-31"))/86400;

	$ret = $d;

	return $ret;
}

function reset_database(){
	global $con, $arg;

	$dummy = array_shift($arg);
	$dummy = array_shift($arg);

	while($cmd = array_shift($arg)){
		list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
		if($cmd_head == "-is_run"){
			$is_run=1;
		}else{
			print "Unknown command $cmd\n";
			exit;
		}
	}

	$skip_tbl_list = array('user','branch','user_privilege'.'privilege','privilege_master','approval_order','vendortype');
	$q1 = $con->sql_query("show tables");
	while($r = $con->sql_fetchrow($q1)){
		$tbl_name = trim($r[0]);

		if(!in_array($tbl_name, $skip_tbl_list)){
			if($is_run){
				print "Truncate $tbl_name\n";
				$con->sql_query("truncate $tbl_name");
			}else{
				print "$tbl_name will be truncate\n";
			}

		}
	}
	$con->sql_freeresult($q1);

	if($is_run){
		$con->sql_query("replace into uom (id,code,description,fraction,active) values (1,'EACH','EACH',1,1)");
		$con->sql_query("insert into category (code, description) values ('LINE', 'LINE');");
	}

	print "Done.\n";
}

function fix_pos_items_price_type(){
	global $con, $arg;

	$dummy = array_shift($arg);
	$dummy = array_shift($arg);

	while($cmd = array_shift($arg)){
		list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
		if($cmd_head == "-branch"){
			$branch_filter = '';
			if($cmd_value == 'all'){
				if(!$config['single_server_mode']){
					$bcode = BRANCH_CODE;
					$branch_filter = 'where code='.ms($bcode);
				}
			}else{
				$bcode = trim($cmd_value);
				$branch_filter = 'where code='.ms($bcode);
			}

			$con->sql_query("select id,code from branch $branch_filter order by sequence,code");
			while($r = $con->sql_fetchassoc()){
				$b_list[] = $r;
			}
			$con->sql_freeresult();

			if(!$b_list && $bcode)	die("Branch $bcode not found.\n");
		}elseif($cmd_head == "-is_run"){
			$is_run=1;
		}else{
			print "Unknown command $cmd\n";
			exit;
		}
	}

	if(!$b_list)	die("No branch is selected\n");

	//print_r($b_list);exit;
	foreach($b_list as $b_info){
		$bid = $b_info['id'];
		$bcode = $b_info['code'];

		print "Checking $bcode\n";

		$filter = array();
		$filter[] = "p.branch_id=$bid";
		$filter[] = "p.date>'2015-03-01' and sku.sku_type='consign' and pi.trade_discount_code=''";
		$filter[] = "p.cancel_status=0";

		$filter = "where ".join(' and ', $filter);

		// select date first
		$sql = "select distinct p.date as date
		from pos_items pi
		join pos p on p.branch_id=pi.branch_id and p.date=pi.date and p.counter_id=pi.counter_id and p.id=pi.pos_id
		join sku_items si on si.id=pi.sku_item_id
		join sku on sku.id=si.sku_id
		$filter
		order by p.date";
		$con->sql_query($sql);
		$date_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$date_list[] = $r['date'];
		}
		$con->sql_freeresult($q1);

		print count($date_list)." date found.\n";

		if($date_list){
			foreach($date_list as $d){
				print "$d\n";
				$sql = "select sku.default_trade_discount_code, sip.trade_discount_code as latest_trade_discount_code, pi.*
				from pos_items pi
				join pos p on p.branch_id=pi.branch_id and p.date=pi.date and p.counter_id=pi.counter_id and p.id=pi.pos_id
				join sku_items si on si.id=pi.sku_item_id
				join sku on sku.id=si.sku_id
				left join sku_items_price sip on sip.branch_id=pi.branch_id and sip.sku_item_id=pi.sku_item_id
				$filter and p.date=".ms($d)."
				order by p.counter_id,p.id,pi.id";
				$q1 = $con->sql_query($sql);
				$total_count = $con->sql_numrows($q1);
				$curr_count = 0;
				if($is_run){
					while($r = $con->sql_fetchassoc($q1)){
						$curr_count++;

						$upd['trade_discount_code'] = $r['latest_trade_discount_code'] ? $r['latest_trade_discount_code'] : $r['default_trade_discount_code'];
						$con->sql_query("update pos_items set ".mysql_update_by_field($upd)." where branch_id=$r[branch_id] and date=".ms($r['date'])." and counter_id=$r[counter_id] and pos_id=$r[pos_id]
						and id=$r[id]");
						print "\r$curr_count / $total_count . . . .";
					}
					$con->sql_freeresult($q1);

					// recalculate sales cache
					update_sales_cache($bid, $d);
					print "\n";
				}else{
					print "$total_count item found.\n";
				}
			}
		}
	}

	print "Done.\n";

}

function fix_pos_rounding($branch_id,$d=null){
	global $con, $arg;

	if($d!=null){
		echo "fix_pos_rounding\n";
		$start = $d;
		$end = $d;
		$branch_id=' and branch_id='.mi($branch_id);
	}
	else{
		$start = $arg[2];
		$end = $arg[3];

		if(!strtotime($start)||!strtotime($end))  die("Invalid Date.\n");

		if(isset($arg[4])) $branch_id=' and branch_id='.mi($arg[4]);
		else $branch_id="";
	}

	$sql = "select p.id, p.branch_id, p.counter_id, p.receipt_no, p.amount, p.date,
round((select sum(if(pp.type='Cash',pp.amount-p.amount_change,pp.amount)) from pos_payment pp where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type not in ('Rounding') and pp.adjust=0),2) as pp_amt,
ifnull(round((select sum(pi.price-pi.discount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0) as pi_amt,
ifnull(round((select sum(amount) from pos_payment pp where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type in ('Rounding') and pp.adjust=0),2),0) as pp_rounding,
round(p.amount-ifnull(round((select sum(pi.price-pi.discount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0),2) as correct_rounding,
(ifnull(round((select sum(amount) from pos_payment pp where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type in ('Rounding') and pp.adjust=0),2),0)-round(p.amount-ifnull(round((select sum(pi.price-pi.discount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0),2)) as diff,
(round((select sum(if(pp.type='Cash',pp.amount-p.amount_change,pp.amount)) from pos_payment pp where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type not in ('Rounding') and pp.adjust=0),2)-(ifnull(round((select sum(pi.price-pi.discount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0)+round(p.amount-ifnull(round((select sum(pi.price-pi.discount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0),2))) as amt_diff_after_correction
from pos p
where p.date >= ".ms($start)."
and p.date <= ".ms($end)."
$branch_id
and p.cancel_status=0
having diff <> 0 and amt_diff_after_correction=0 and pi_amt!=0 and abs(diff) <= 0.02
order by p.branch_id, p.date, p.counter_id, p.id";

	$q1=$con->sql_query($sql);

    $arr=array();

	$count=0;
	while($r = $con->sql_fetchassoc($q1)){
		$sql2="select * from pos_payment where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and date=".ms($r['date'])." and pos_id=".mi($r['id'])." and type='Rounding'";
		$q2=$con->sql_query($sql2);

		if($con->sql_numrows($q2)>0){
			$sql3="update pos_payment set amount=".ms($r['correct_rounding'])." where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and date=".ms($r['date'])." and pos_id=".mi($r['id'])." and type='Rounding'";
		}
		else{
			$sql3="select max(id) as max from pos_payment where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and date=".ms($r['date']);
			$q3=$con->sql_query($sql3);
			$max=$con->sql_fetchassoc(0,$q3);
			$con->sql_freeresult($q3);
            $max=$max['max']+1;

			$sql3="INSERT INTO pos_payment (branch_id, counter_id, id, pos_id, type, remark, amount, date, changed, adjust, approved_by, more_info, is_abnormal)
VALUES(".mi($r['branch_id']).",".mi($r['counter_id']).",".$max.",".mi($r['id']).",'Rounding','',".ms($r['correct_rounding']).",".ms($r['date']).",0,0,0,'s:0:\"\";',0)";
		}

		//echo $sql3."<br/><br/>";
        $con->sql_query($sql3);

        if(!isset($arr[$r['branch_id']])) $arr[$r['branch_id']]=array();

        if(!in_array($r['date'], $arr[$r['branch_id']])) $arr[$r['branch_id']][]=$r['date'];
		$count++;
	}
    $con->sql_freeresult($q1);

	if($d!=null && $count<=0) return true;

	foreach($arr as $bid=>$dates){
      foreach($dates as $date){
        $sql="select * from pos_finalized where branch_id=".mi($bid)." and date=".ms($date);
        $q1=$con->sql_query($sql);
        $r = $con->sql_fetchassoc($q1);
        if($r['finalized']){
          //echo "update_sales_cache($bid, $date));"."<br/>";
          update_sales_cache($bid, $date);
          echo "Finalize Complete: branch_id:".$bid,"  date:".$date."\n";
        }
		$con->sql_freeresult($q1);
      }
    }

	if($d!=null) return false;
	echo "Done";
}

function check_and_resync_sku(){
	global $con, $arg, $db_default_connection;

	$ank_server_list = array(
		'BSERAI' => array('akadbserai.aneka.com.my:4001', 'arms', '793505'),
		'BSERAI_SYNC' => array('akadbserai.aneka.com.my:4011', 'arms', 'pos123'),
		'GURUN' => array('akadgurun.aneka.com.my:4001', 'arms', '793505'),
		'GURUN_SYNC' => array('akadgurun.aneka.com.my:4011', 'arms', 'pos123'),
		'BALING' => array('akadbaling.aneka.com.my:4001', 'arms', '793505'),
		'BALING_SYNC' => array('akadbaling.aneka.com.my:4011', 'arms', 'pos123'),
		'TMERAH' => array('akadtmerah.aneka.com.my:4001', 'arms', '793505'),
		'TMERAH_SYNC' => array('akadtmerah.aneka.com.my:4011', 'arms', 'pos123'),
		'DUNGUN' => array('akaddungun.aneka.com.my:4001', 'arms', '793505'),
		'DUNGUN_SYNC' => array('akaddungun.aneka.com.my:4011', 'arms', 'pos123'),
		'KANGAR' => array('akadkangar.aneka.com.my:4001', 'arms', '793505'),
		'KANGAR_SYNC' => array('akadkangar.aneka.com.my:4011', 'arms', 'pos123'),
		'JITRA' => array('akadjitra.aneka.com.my:4001', 'arms', '793505'),
		'JITRA_SYNC' => array('akadjitra.aneka.com.my:4011', 'arms', 'pos123'),
	);

	$dummy = array_shift($arg);
	$dummy = array_shift($arg);

	while($cmd = array_shift($arg)){
		list($cmd_head, $cmd_value) = explode("=", $cmd, 2);

		if($cmd_head == "-reset"){
			$is_reset=1;
		}elseif($cmd_head == "-is_sync"){
			$is_sync=1;
		}elseif($cmd_head == "-server"){
			$server_name = strtoupper($cmd_value);
			if(!isset($ank_server_list[$server_name])){
				die("Invalid Server '$server_name'\n");
			}
		}else{
			print "Unknown command $cmd\n";
			exit;
		}
	}

	$tmp_sku_tbl = "tmp_resync_sku_items";
	$con->sql_query("create table if not exists $tmp_sku_tbl(
		sku_item_id int primary key
	)");

	if(($is_reset || $is_sync) && $server_name){
		die("This mode does not support -server.\n");
	}

	if($is_reset){
		$con->sql_query("truncate $tmp_sku_tbl");
		print "Data Reset.\n";
		exit;
	}

	if($is_sync){
		$q1 = $con->sql_query("select si.*
		from $tmp_sku_tbl tmp_si
		join sku_items si on si.id=tmp_si.sku_item_id
		order by si.id");
		$total_row_count = $con->sql_numrows($q1);
		$curr_count = 0;
		while($r = $con->sql_fetchassoc($q1)){
			$curr_count++;
			print "\r$curr_count / $total_row_count. . .";
			$con->sql_query("replace into sku_items ".mysql_insert_by_field($r));
		}
		$con->sql_freeresult($q1);
		print "\nDone.\n";
		exit;
	}
	foreach($ank_server_list as $key => $server){
		if(isset($server_name) && $server_name != $key)	continue;

		print "Running server $key\n";
		$con2 = connect_db($server[0], $server[1], $server[2], $db_default_connection[3]);

		$con->sql_query("select count(*) from sku_items");
		$total_row_count = $con->sql_fetchfield(0);
		$con->sql_freeresult();

		$start_id = 0;
		$limit_size = 100;

		$curr_page = 0;
		$total_page = ceil($total_row_count / $limit_size);
		$need_resync_count = 0;

		do{
			$curr_page++;


			$sid_list = array();
			$got_item = false;
			$con->sql_query("select id,input_tax,output_tax,inclusive_tax from sku_items order by id limit $start_id, $limit_size");
			while($r = $con->sql_fetchassoc()){
				$got_item = true;
				$sid_list[$r['id']] = $r;
			}
			$con->sql_freeresult();

			if($sid_list){
				$con2->sql_query("select id,input_tax,output_tax,inclusive_tax from sku_items where id in (".join(',', array_keys($sid_list)).")");
				while($r = $con2->sql_fetchassoc()){
					// check input_tax, output_tax and inclusive_tax
					if($r['input_tax']==$sid_list[$r['id']]['input_tax'] && $r['output_tax']==$sid_list[$r['id']]['output_tax'] && $r['inclusive_tax']==$sid_list[$r['id']]['inclusive_tax']){
						unset($sid_list[$r['id']]);	//remove from the list if all match
					}
				}
				$con2->sql_freeresult();

				//print_r($sid_list);
				foreach($sid_list as $sid => $r){
					$need_resync_count++;

					$upd = array();
					$upd['sku_item_id'] = $sid;

					$con->sql_query_false("insert into $tmp_sku_tbl ".mysql_insert_by_field($upd));
				}
			}
			$start_id += $limit_size;

			if($curr_page > $total_page)	$curr_page = $total_page;	// fix display error at last page
			print "\r$curr_page / $total_page . . .";
		}while($got_item);
	}

	// check total
	$con->sql_query("select count(*) from $tmp_sku_tbl");
	$total_need_resync = mi($con->sql_fetchfield(0));
	$con->sql_freeresult();

	print "\nDone. Total need resync $total_need_resync.\n";
}

function fix_pos_item_missing_gst(){	// php temp_script.php fix_pos_item_missing_gst 2016-10-09 7
  global $con, $arg;

  $date=$arg[2];

  if(!strtotime($date))  die("Invalid Date.\n");

  $cond = 'and date = "'.$date.'"';
  if(isset($arg[3])) $cond.=' and branch_id='.mi($arg[3]);

  $sql='select * from pos_items where (tax_code="" or tax_code is null or (tax_code="SR" and tax_rate = 0)) '.$cond;
  //echo $sql."\n";
  //die("test\n");
  $q1=$con->sql_query($sql);

  $arr=array();

  while($r = $con->sql_fetchassoc($q1)){
	$q2 = $con->sql_query("select inclusive_tax from sku_items where id=".mi($r['sku_item_id']));
	$c = $con->sql_fetchassoc($q2);
	$con->sql_freeresult($q2);

	if($c['inclusive_tax']){
	  $output_tax=get_sku_gst("output_tax",$r['sku_item_id'], array('no_check_use_zero_rate'=>1));

	  $tax_code=$output_tax['code'];
	  $tax_indicator=$output_tax['indicator_receipt'];
	  $rate=$output_tax['rate'];

	  $price=$r['price']-$r['discount']-$r['discount2'];

	  if($rate>0){
		$after_tax_price = $price;
		$before_tax_price = round((($price/($rate+100))*100),2);
		$tax_amount=$after_tax_price-$before_tax_price;
	  }
	  else{
		$tax_amount=0;
		$before_tax_price=$price;
	  }

	  $upd=array();
	  $upd['inclusive_tax'] = 1;
	  $upd['tax_code'] = $tax_code;
	  $upd['tax_indicator'] = $tax_indicator;
	  $upd['tax_amount'] = $tax_amount;
	  $upd['tax_rate'] = $rate;
	  $upd['before_tax_price'] = $before_tax_price;

	  $upd_query="update pos_items set ".mysql_update_by_field($upd)."
					  where branch_id = ".mi($r['branch_id'])."
					  and counter_id=".mi($r['counter_id'])."
					  and date=".ms($r['date'])."
					  and pos_id=".mi($r['pos_id'])."
					  and id=".mi($r['id']);
	  //echo $upd_query."\n";
	  //die("test\n");
	  $con->sql_query($upd_query);

	  if(!isset($arr[$r['date']])) $arr[$r['date']]=array();
	  if(!isset($arr[$r['date']][$r['branch_id']])) $arr[$r['date']][$r['branch_id']]=array();
	  if(!isset($arr[$r['date']][$r['branch_id']][$r['counter_id']])) $arr[$r['date']][$r['branch_id']][$r['counter_id']]=array();

	  if(!in_array($r['pos_id'], $arr[$r['date']][$r['branch_id']][$r['counter_id']])) $arr[$r['date']][$r['branch_id']][$r['counter_id']][]=$r['pos_id'];
	}
  }
  $con->sql_freeresult($q1);

  foreach($arr as $date=>$branches){
	foreach($branches as $branch_id=>$counters){
	  foreach($counters as $counter_id=>$pos){
		foreach($pos as $pos_id){
		  $sql='select round(sum(tax_amount),2) as total from pos_items
				where date='.ms($date).'
				and branch_id='.mi($branch_id).'
				and counter_id='.mi($counter_id).'
				and pos_id='.mi($pos_id);
		  $q2 = $con->sql_query($sql);
		  $c = $con->sql_fetchassoc($q2);
		  $con->sql_freeresult($q2);

		  $upd=array();
		  $upd['total_gst_amt']=$c['total'];
		  $upd['is_gst']=1;

		  $upd_query="update pos set ".mysql_update_by_field($upd)."
						where branch_id = ".mi($branch_id)."
						and counter_id=".mi($counter_id)."
						and date=".ms($date)."
						and id=".mi($pos_id);
		  //echo $upd_query."\n";
		  //die("test\n");
		  $con->sql_query($upd_query);
		}
	  }

	  $sql="select * from pos_finalized where branch_id=".mi($branch_id)." and date=".ms($date);
	  $q1=$con->sql_query($sql);
	  $r = $con->sql_fetchassoc($q1);
	  if($r['finalized']){
		//echo "update_sales_cache($branch_id, $date));"."\n";
		update_sales_cache($branch_id, $date);
		echo "Finalize Complete: branch_id:".$branch_id,"  date:".$date."\n";
	  }
	  $con->sql_freeresult($q1);
	}
  }

  echo "Done\n";
}

function fix_pos_item_tax_amount(){
  global $con, $arg;

  $start=$arg[2];
  $end=$arg[3];
  $branch_id=intval($arg[4]);

  if(!strtotime($start)||!strtotime($end))  die("Invalid Date.\n");
  if($branch_id>0) $branch_id=" and p.branch_id=$branch_id";
  else $branch_id="";

  $sql="select p.receipt_no,p.branch_id,p.counter_id,p.id as pos_id,p.date,p.amount, round((select sum(if(pp.type='Cash',pp.amount-p.amount_change,pp.amount)) from pos_payment pp where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type not in ('Rounding') and pp.adjust=0),2) as pp_amt,
  ifnull(round((select sum(pi.price-pi.discount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0) as pi_amt,
  ifnull(round((select sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0) as nett_price,
  ifnull(round((select sum(pi.before_tax_price) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0) as before_tax_price,
  ifnull(round((select sum(pi.tax_amount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0) as pi_gst_amt,p.total_gst_amt,
  ifnull(round((select sum(pi.discount2) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0) as total_discount2,
  ifnull(round((select sum(pp.amount) from pos_payment pp where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type in ('Mix & Match Total Disc','Discount') and pp.adjust=0),2),0) as total_receipt_mix_discount
  from pos p
  where p.date >= ".ms($start)."
  and p.date <= ".ms($end)."
  $branch_id
  having (abs(pp_amt-pi_amt)>0.02) or (nett_price<>before_tax_price) or (pi_gst_amt<>total_gst_amt) or (total_discount2<> total_receipt_mix_discount)";

  $q1=$con->sql_query($sql);

  echo $con->sql_numrows($q1)." row found\n";

  $arr=array();

  while($r = $con->sql_fetchassoc($q1)){
	$sql='select * from pos_items
	where branch_id='.mi($r['branch_id']).'
	and counter_id='.mi($r['counter_id']).'
	and date='.ms($r['date']).'
	and pos_id='.mi($r['pos_id']);

	$q2 = $con->sql_query($sql);
	while($r2 = $con->sql_fetchassoc($q2)){
	  if(mf($r2['tax_rate'])>0){
		$after_tax_price = round(($r2['price']-$r2['discount']-$r2['discount2']),2);
		$before_tax_price = round((($after_tax_price/(mf($r2['tax_rate'])+100))*100),2);
		$tax_amount=$after_tax_price-$before_tax_price;
	  }
	  else{
		$tax_amount=0;
		$before_tax_price=round(($r2['price']-$r2['discount']-$r2['discount2']),2);
	  }

	  $upd=array();
	  $upd['tax_amount']=$tax_amount;
	  $upd['before_tax_price']=$before_tax_price;

	  $upd_query="update pos_items set ".mysql_update_by_field($upd)."
					  where branch_id = ".mi($r2['branch_id'])."
					  and counter_id=".mi($r2['counter_id'])."
					  and date=".ms($r2['date'])."
					  and pos_id=".mi($r2['pos_id'])."
					  and id=".mi($r2['id']);
	  //echo $upd_query."\n";
	  $con->sql_query($upd_query);
	}

	if(!isset($arr[$r['date']])) $arr[$r['date']]=array();
	if(!isset($arr[$r['date']][$r['counter_id']])) $arr[$r['date']][$r['counter_id']]=array();
	if(!in_array($r['pos_id'], $arr[$r['date']][$r['counter_id']])) $arr[$r['date']][$r['counter_id']][]=$r['pos_id'];
	//echo $r['pos_id']."\n";
  }

  foreach($arr as $date=>$counters){
	foreach($counters as $counter_id=>$pos){
		foreach($pos as $pos_id){
		  $sql='select round(sum(tax_amount),2) as total from pos_items
				where date='.ms($date).'
				and branch_id='.mi($branch_id).'
				and counter_id='.mi($counter_id).'
				and pos_id='.mi($pos_id);
		  $q2 = $con->sql_query($sql);
		  $c = $con->sql_fetchassoc($q2);
		  $con->sql_freeresult($q2);

		  $upd=array();
		  $upd['total_gst_amt']=$c['total'];

		  $upd_query="update pos set ".mysql_update_by_field($upd)."
						where branch_id = ".mi($branch_id)."
						and counter_id=".mi($counter_id)."
						and date=".ms($date)."
						and id=".mi($pos_id);
		  //echo $upd_query."\n";
		  //die("test\n");
		  $con->sql_query($upd_query);
		}
	}

	$ret=fix_pos_rounding($branch_id,$date);

	if($ret){
		$sql="select * from pos_finalized where branch_id=".mi($branch_id)." and date=".ms($date);
		$q1=$con->sql_query($sql);
		$r = $con->sql_fetchassoc($q1);
		if($r['finalized']){
		  //echo "update_sales_cache($branch_id, $date));"."\n";
		  update_sales_cache($branch_id, $date);
		  echo "Finalize Complete: branch_id:".$branch_id,"  date:".$date."\n";
		}
		$con->sql_freeresult($q1);
	}
  }

  echo "Done\n";
}

function move_sku_cat(){
	global $con, $arg;
	
	$dummy = array_shift($arg);
	$dummy = array_shift($arg);
	
	while($cmd = array_shift($arg)){
		list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
		
		if($cmd_head == "-is_run"){
			$is_run = 1;
		}else{
			print "Unknown command $cmd\n";
			exit;
		}
	}
	
	$f = fopen('attch/move_cat2.csv', 'r');
	
	$row_count = 0;
	while($r = fgetcsv($f)){
		$row_count++;
		//print "\r$row_count. . .";
		
		$from_cat_id = mi($r[5]);
		$to_cat_id = mi($r[12]);
		
		if($from_cat_id<=0 && $to_cat_id <=0)	continue;
		
		$upd = array();
		$upd['category_id'] = $to_cat_id;
		
		$sql = "update sku set ".mysql_update_by_field($upd)." where category_id=$from_cat_id";
		print "$row_count) $sql\n";
		if($is_run){
			$con->sql_query($sql);
			print mi($con->sql_affectedrows())." rows updated.\n";
		}
		//print_r($r);
	}
	print "\nDone.\n";
}

// php temp_script.php clear_docs all
// php temp_script.php clear_docs master
// php temp_script.php clear_docs master skip_uom skip_approval skip_user
// php temp_script.php clear_docs master skip_user
function clear_docs(){
	global $con, $params;

	$is_all = false;
	$is_master = false;
	
	if(in_array('all', $params))	$is_all = true;
	if(in_array('master', $params)){
		// master will include all
		$is_all = true;
		$is_master = true;
	}
	if(in_array('skip_user', $params))	$skip_user = true;
	if(in_array('skip_approval', $params))	$skip_approval = true;	
	if(in_array('skip_uom', $params))	$skip_uom = true;	
	
	if(!$skip_user){
		if($skip_approval)	die("Using skip_approval must also define skip_user.\n");
	}
	if(in_array('on_stock_check', $params) || $is_all){
        print "Truncate Stock Check...\n";
		$con->sql_query("truncate stock_check");
		$con->sql_query("truncate stock_take_pre");
	}
 
	print "Truncate Adjustment...\n";
	$con->sql_query("truncate adjustment");
	$con->sql_query("truncate adjustment_items");
	
	print "Truncate Consignment Invoice...\n";
	$con->sql_query("truncate ci");
	$con->sql_query("truncate ci_items");
	
	print "Truncate CN...\n";
	$con->sql_query("truncate cn");
	$con->sql_query("truncate cn_items");
	
	print "Truncate DN...\n";
	$con->sql_query("truncate dn");
	$con->sql_query("truncate dn_items");
	
	print "Truncate DO...\n";
	$con->sql_query("truncate do");
	$con->sql_query("truncate do_items");
	$con->sql_query("truncate do_open_items");
	$con->sql_query("truncate do_request_items");
	
	print "Truncate GRA ...\n";
	$con->sql_query("truncate gra");
	$con->sql_query("truncate gra_items");
	
	print "Truncate GRN ...\n";
	$con->sql_query("truncate grn");
	$con->sql_query("truncate grn_items");
	
	print "Truncate GRR ...\n";
	$con->sql_query("truncate grr");
	$con->sql_query("truncate grr_items");
	
	print "Truncate PO ...\n";
	$con->sql_query("truncate po");
	$con->sql_query("truncate po_items");
	$con->sql_query("truncate po_request_items");
	
	print "Truncate Sales Order ...\n";
	$con->sql_query("truncate sales_order");
	$con->sql_query("truncate sales_order_items");
	
	print "Truncate DNote ...\n";
	$con->sql_query("truncate dnote");
	$con->sql_query("truncate dnote_items");
	
	print "Truncate Notification…\n";
	$con->sql_query("truncate pm");
	
	if($is_all){
	    print "Truncate Consignment Monthly Report ...\n";
	    $con->sql_query("truncate consignment_report");
		$con->sql_query("truncate consignment_report_export_history");
		$con->sql_query("truncate consignment_report_page_info");
		$con->sql_query("truncate consignment_report_sku");
		$con->sql_query("truncate monthly_report_list");
		
		print "Truncate Promotion ...\n";
		$con->sql_query("truncate promotion");
		$con->sql_query("truncate promotion_items");
		$con->sql_query("truncate promotion_mix_n_match_items");
		$con->sql_query("truncate membership_promotion_items");
		$con->sql_query("truncate membership_promotion_mix_n_match_items");
		
        print "Truncate POS ...\n";
		$con->sql_query("truncate pos");
		$con->sql_query("truncate pos_items");
		$con->sql_query("truncate pos_payment");
		$con->sql_query("truncate pos_cash_domination");
		$con->sql_query("truncate pos_cash_history");
		$con->sql_query("truncate pos_counter_collection");
		$con->sql_query("truncate pos_counter_collection_tracking");
		$con->sql_query("truncate pos_drawer");
		$con->sql_query("truncate pos_finalized");
		$con->sql_query("truncate pos_goods_return");
		$con->sql_query("truncate pos_receipt_cancel");
		$con->sql_query("truncate pos_deposit");
		$con->sql_query("truncate pos_deposit_status");
		$con->sql_query("truncate pos_deposit_status_history");
		$con->sql_query("truncate pos_cashier_finalize");
		$con->sql_query("truncate pos_counter_finalize");
		$con->sql_query("truncate pos_credit_note");
		$con->sql_query("truncate pos_delete_items");
		$con->sql_query("truncate pos_error");
		$con->sql_query("truncate pos_items_changes");
		$con->sql_query("truncate pos_member_point_adjustment");
		$con->sql_query("truncate pos_mix_match_usage");
		$con->sql_query("truncate pos_transaction_audit_log");
		$con->sql_query("truncate pos_transaction_ejournal");
		$con->sql_query("truncate pos_user_log");
		$con->sql_query("truncate pos_transaction_counter_sales_record");
		$con->sql_query("truncate pos_transaction_sync_server_tracking");
		$con->sql_query("truncate pos_transaction_sync_server_counter_tracking");
		$con->sql_query("truncate pos_transaction_clocking_log");
		$con->sql_query("truncate membership_drawer_history");
		$con->sql_query("truncate membership_points");
		$con->sql_query("update membership set points=0");
		$con->sql_query("truncate membership_receipt");
		$con->sql_query("truncate membership_receipt_items");
		$con->sql_query("truncate membership_redemption");
		$con->sql_query("truncate membership_redemption_items");
		$con->sql_query("truncate membership_redemption_sku");
		$con->sql_query("truncate memberships_notice_board_items");
		$con->sql_query("truncate memberships_otp");
		$con->sql_query("truncate memberships_pn");
		$con->sql_query("truncate memberships_pn_items");
		$con->sql_query("truncate memberships_purchased_package");
		$con->sql_query("truncate memberships_purchased_package_items");
		$con->sql_query("truncate memberships_purchased_package_items_redeem");
		$con->sql_query("truncate memberships_purchased_package_log");
		$con->sql_query("truncate memberships_push_notification_history");
		
		
		$q1 = $con->sql_query("show tables");
		while($r = $con->sql_fetchrow($q1)){
			if(strpos($r[0], 'category_sales_cache_')!==false || strpos($r[0], 'dept_trans_cache_')!==false || strpos($r[0], 'member_sales_cache_')!==false || strpos($r[0], 'pwp_sales_cache_')!==false || strpos($r[0], 'sku_items_sales_cache_')!==false || strpos($r[0], 'stock_balance_b')!==false || strpos($r[0], 'daily_sales_cache_b')!==false){
				$con->sql_query("truncate $r[0]");
			}elseif(strpos($r[0], 'archive_')!==false || strpos($r[0], 'stock_closing_')!==false ){
                $con->sql_query("drop table $r[0]");
			}
		}
		
		if($is_master){
			// clear master
			print "Start Truncate Master\n";
			print "Truncate SKU ...\n";
			$con->sql_query("truncate sku");
			$con->sql_query("truncate sku_items");
			$con->sql_query("truncate sku_items_price");
			$con->sql_query("truncate sku_items_price_history");
			$con->sql_query("truncate sku_items_cost");
			$con->sql_query("truncate sku_items_cost_history");
			$con->sql_query("truncate bom_items");
			$con->sql_query("truncate sku_group");
			$con->sql_query("truncate sku_group_item");
			$con->sql_query("truncate sku_group_vp_date_control");
			//$con->sql_query("truncate sku_items_po_reorder");
			
			print "Truncate Category ...\n";
			$con->sql_query("delete from category where id > 2");
			$con->sql_query("ALTER TABLE category AUTO_INCREMENT = 1;");
			
			print "Truncate Approval Flow ...\n";
			if(!$skip_approval){
				$con->sql_query("truncate approval_flow");
			}			
			$con->sql_query("truncate approval_history");
			$con->sql_query("truncate approval_history_items");
			$con->sql_query("truncate branch_approval_history");
			$con->sql_query("truncate branch_approval_history_items");
			
			print "Truncate Brand ...\n";
			$con->sql_query("truncate brand");
			$con->sql_query("truncate brand_brgroup");
			
			print "Truncate Branch Group ...\n";
			$con->sql_query("truncate branch_group");
			$con->sql_query("truncate branch_group_items");
			
			print "Truncate Vendor ...\n";
			$con->sql_query("truncate vendor");
			$con->sql_query("truncate branch_vendor");
			$con->sql_query("truncate login_tickets");
			
			if(!$skip_uom){				
				print "Truncate UOM ...\n";
				$con->sql_query("delete from uom where id != 1");
				$con->sql_query("alter table uom auto_increment=1");
			}
			
			print "Truncate Membership ...\n";
			$con->sql_query("truncate membership");
			$con->sql_query("truncate membership_extra_info");
			$con->sql_query("truncate membership_fav_items");
			$con->sql_query("truncate membership_history");
			$con->sql_query("truncate membership_inventory_history");
			$con->sql_query("truncate membership_isms");
			$con->sql_query("truncate membership_isms_items");
			$con->sql_query("truncate membership_mobile_ads_banner");
			$con->sql_query("truncate membership_package");
			$con->sql_query("truncate membership_package_items");
			$con->sql_query("truncate memberships_referral_history");
			$con->sql_query("truncate tmp_membership_points_trigger");
			
			print "Truncate Debtor ...\n";
			$con->sql_query("truncate debtor");
			
			print "Truncate Serial Number ...\n";
			$con->sql_query("truncate pos_items_sn");
			$con->sql_query("truncate pos_items_sn_history");
			$con->sql_query("truncate sn_info");
			
			print "Truncate SKU Batch ...\n";
			$con->sql_query("truncate sku_batch_items");
			
			print "Truncate Consignment Transporter ...\n";
			$con->sql_query("truncate consignment_transporter");
			$con->sql_query("truncate consignment_transporter_history");
			
			print "Truncate Consignment Bearing ...\n";
			$con->sql_query("truncate consignment_bearing");
			$con->sql_query("truncate consignment_bearing_items");
			
			print "Truncate Coupon ...\n";
			$con->sql_query("truncate coupon");
			
			print "Truncate Sales Agent ...\n";
			$con->sql_query("truncate sa");
			$con->sql_query("truncate sa_commission");
			$con->sql_query("truncate sa_sales_target");
			$con->sql_query("truncate sa_commission_items");
			$con->sql_query("truncate sa_commission_settings");
			
			print "Truncate Voucher ...\n";
			$con->sql_query("truncate mst_voucher");
			$con->sql_query("truncate mst_voucher_batch");
			$con->sql_query("truncate voucher_auto_redemp_master");
			$con->sql_query("truncate voucher_auto_redemp_history");
			
			//print "Truncate Return Policy ...\n";
			//$con->sql_query("truncate return_policy");
			//$con->sql_query("truncate return_policy_sales_cache");
			//$con->sql_query("truncate return_policy_setup");
			
			print "Truncate Log ...\n";
			$con->sql_query("truncate log");
			$con->sql_query("truncate log_dp");
			$con->sql_query("truncate log_vp");
			
			if(!$skip_user){
				print "Truncate User ...\n";
				$con->sql_query("select id from user where is_arms_user=1");
				$user_id_list = array(1,2);
				while($r = $con->sql_fetchassoc()){
					$user_id_list[] = mi($r['id']);
				}
				$con->sql_freeresult();
				
				$con->sql_query("delete from user where id not in (".join(',', $user_id_list).")");
				$con->sql_query("alter table user auto_increment=1");
				$con->sql_query("delete from user_privilege where user_id not in (".join(',', $user_id_list).")");
				$con->sql_query("truncate user_status");
			}
			
			// search and truncate other tables
			print "Truncate All others master ...\n";
			$q1 = $con->sql_query("show tables");
			while($r = $con->sql_fetchrow($q1)){
				if(strpos($r[0], 'vendor_')!==false || strpos($r[0], 'sku_item')!==false || strpos($r[0], 'brand_')!==false || strpos($r[0], 'branch_')!==false || strpos($r[0], 'membership_')!==false || strpos($r[0], 'category_')!==false || strpos($r[0], 'sa_sales_cache_b')!==false){
					$con->sql_query("truncate $r[0]");
				}
			}
		}
	}
	
	print "Mark Inventory Changes...\n";
	$con->sql_query("update sku_items_cost set changed=1");
	print "Done.";
}

function check_do_config(){
	global $config;
	
	print "do_transfer_have_discount: ".($config['do_transfer_have_discount'] ? 'Yes' : 'No')."<br>";
	print "do_cash_sales_have_discount: ".($config['do_cash_sales_have_discount'] ? 'Yes' : 'No')."<br>";
	print "do_credit_sales_have_discount: ".($config['do_credit_sales_have_discount'] ? 'Yes' : 'No')."<br>";
}

function fix_dn_gra_gst_amt(){
	global $config, $smarty, $con;
	
	// select master
	$q1 = $con->sql_query("select * from dnote where ref_table = 'gra' and active = 1 and is_under_gst = 1");
	
	if($con->sql_numrows($q1) == 0){ // if no record found, end of query
		print "No D/N was generated for GRA.";
	}else{ // found records, proceed to recalculate
		print "Found ".$con->sql_numrows($q1)." record(s) D/N generated for GRA, processing...<br />";
		
		while($r = $con->sql_fetchassoc($q1)){
			$total_gst_amount = $total_amount = 0;
			// select items
			$q2 = $con->sql_query("select * from dnote_items where dnote_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
			
			if($con->sql_numrows($q2) > 0){
				while($r2 = $con->sql_fetchassoc($q2)){ // recalculate gst amount and do updates
					$row_gst_amount = round($r2['item_gross_amount'] * $r2['gst_rate'] / 100, $config['global_cost_decimal_points']);
					$row_amt = $r2['item_gross_amount'] + $row_gst_amount;
					
					$upd = array();
					$upd['item_gst_amount'] = $row_gst_amount;
					$upd['item_amount'] = $row_amt;
					
					$con->sql_query("update dnote_items set ".mysql_update_by_field($upd)." where id = ".mi($r2['id'])." and branch_id = ".mi($r2['branch_id']));
					//print "update dnote_items set ".mysql_update_by_field($upd)." where id = ".mi($r2['id'])." and branch_id = ".mi($r2['branch_id'])."<br />";
					$total_gst_amount += $row_gst_amount;
					$total_amount += $row_amt;
				}
				$con->sql_freeresult($q2);
			}
			
			$upd = array();
			$upd['total_gst_amount'] = $total_gst_amount;
			$upd['total_amount'] = $total_amount;
			$upd['last_update'] = $r['last_update'];
			
			$con->sql_query("update dnote set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
			//print "update dnote set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])."<br />";
		}
		print "Done.";
	}
	$con->sql_freeresult($q1);
}

function check_config_value(){
	global $config;
	
	$config_name = trim($_REQUEST['config_name']);
	if(!$config_name)	die("Please  provide 'config_name'");
	
	$cf = $config[$config_name];
	
	if(!$cf){
		print "False";
	}else{
		if(is_array($cf)){
			print "<pre>";
			print_r($cf);
			print "</pre>";
		}else{
			print $cf;
		}
	}
}

// php temp_script.php refinalized_pos HQ from_date to_date
function refinalized_pos(){
	global $con, $from_date, $to_date, $branch_code;
    $con->sql_query("select id from branch where code=".ms($branch_code));
    $bid = mi($con->sql_fetchfield(0));
    $con->sql_freeresult();
    if(!$bid)   die("Invalid Branch Code.\n");
    
    if(!strtotime($from_date)||!strtotime($to_date))  die("Invalid Date.\n");
    print "$from_date to $to_date\n";
    update_sales_cache($bid, '', $from_date, $to_date);
}

function fix_do_info_missing_from_grn(){
	global $con, $config, $smarty;
	
	$q1=$con->sql_query("select grr_items.*, grr.*, vendor.*, grr.id as grr_id, grr_items.id as grr_item_id, 
						 vendor.description as vendor, vendor.allow_grn_without_po, dept.grn_get_weight, 
						 dept.description as department, user.u, rcv.u as rcv_u, vendor.code as vendor_code,
						 if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id,
						 grn.id as grn_id
						 from grr_items
						 left join grr on grr_items.grr_id = grr.id and grr_items.branch_id = grr.branch_id
						 left join grn on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
						 left join user on grr.user_id = user.id
						 left join user rcv on grr.rcv_by = rcv.id
						 left join vendor on grr.vendor_id = vendor.id
						 left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = grr_items.branch_id
						 left join category dept on grr.department_id = dept.id
						 where grr.active=1 and grr_items.type = 'DO' and grn.id > 0
						 order by grr_items.type, grr_items.id");

	while($r1=$con->sql_fetchassoc($q1)){
		$grp_do_no = array();

		$filter = "do_branch_id = ".mi($r1['branch_id'])." and do_type = 'transfer'";
		$q3 = $con->sql_query($abc="select di.*, do.do_markup, u.fraction as uom_fraction
							   from do 
							   left join do_items di on di.do_id = do.id and di.branch_id = do.branch_id
							   left join uom u on u.id = di.uom_id
							   where do_no = ".ms($r1['doc_no'])." and ".$filter);
		
		if($con->sql_numrows($q3) > 0){  // means it is IBT DO
			while($grr_do = $con->sql_fetchassoc($q3)){
				// select from grn_items
				$q4 = $con->sql_query("select * from grn_items where grn_id = ".mi($r1['grn_id'])." and branch_id = ".mi($r1['branch_id'])." and sku_item_id = ".mi($grr_do['sku_item_id'])." and po_item_id = 0 limit 1");
				
				if($con->sql_numrows($q4) > 0){
					$grn_info = $con->sql_fetchassoc($q4);
					print "GRN affected: #".mi($grn_info['grn_id'])."<br />";
					
					if($grr_do['do_markup']){
						$temp = array();
						$temp['do_markup_arr'] = explode("+", $grr_do['do_markup']);
						if($grr_do['markup_type']=='down'){
							$temp['do_markup_arr'][0] *= -1;
							$temp['do_markup_arr'][1] *= -1;
						}
						if($temp['do_markup_arr'][0]){
							$grr_do['cost_price'] = $grr_do['cost_price'] * (1+($temp['do_markup_arr'][0]/100));
						}
						if($temp['do_markup_arr'][1]){
							$grr_do['cost_price'] = $grr_do['cost_price'] * (1+($temp['do_markup_arr'][1]/100));
						}
					}
					
					$upd = array();
					$upd['po_qty'] = $grr_do['ctn']*$grr_do['uom_fraction']+$grr_do['pcs'];
					$upd['po_cost'] = $grr_do['cost_price'];
					$upd['po_item_id'] = $grr_do['id'];
					$upd['item_group'] = 1;
					
					$con->sql_query("update grn_items set ".mysql_update_by_field($upd)." where id = ".mi($grn_info['id'])." and grn_id = ".mi($grn_info['grn_id'])." and branch_id = ".mi($grn_info['branch_id']));
					//print "update grn_items set ".mysql_update_by_field($upd)." where id = ".mi($grn_info['id'])." and grn_id = ".mi($grn_info['grn_id'])." and branch_id = ".mi($grn_info['branch_id'])."<br />";
				}
				$con->sql_freeresult($q4);
			}
		}
		$con->sql_freeresult($q3);
	}
	$con->sql_freeresult($q1);
}

function fix_grn_gst_selling_price(){
	global $con, $config;
	
	if(!$config['enable_gst']) die("Not GST customer");
	
	$q1 = $con->sql_query("select gi.*
						from grn 
						join grn_items gi on gi.grn_id = grn.id and gi.branch_id = grn.branch_id
						where grn.is_under_gst = 1 and (gi.gst_selling_price is null or gi.gst_selling_price = 0)
						order by grn.added desc");
	
	while($r = $con->sql_fetchassoc($q1)){
		$is_inclusive_tax = get_sku_gst("inclusive_tax", $r['sku_item_id']);
		
		$upd = array();
		$prms = array();
		$prms['selling_price'] = $r['selling_price'];
		$prms['inclusive_tax'] = $is_inclusive_tax;
		$prms['gst_rate'] = $r['selling_gst_rate'];
		$gst_sp_info = calculate_gst_sp($prms);
		$upd['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
		
		if($is_inclusive_tax == "yes"){
			$upd['selling_price'] = $gst_sp_info['gst_selling_price'];
			$upd['gst_selling_price'] = $r['selling_price'];
		}
		
		$con->sql_query("update grn_items set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
		//print "update grn_items set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])."<br />";
	}
}

function kill_mysql_dump(){
	global $con;
	
	while(true){
		$q1 = $con->sql_query('SELECT * FROM information_schema.processlist where (info like "%SELECT /*!40001 SQL_NO_CACHE */ * FROM%") and host!="localhost";');
		//print_r($q1);
		$i = 0;
		while($r = $con->sql_fetchassoc($q1)){
			//print_r($r);
			if($i > 0){
				$con->sql_query("kill ".$r['id']);
				print "Date: ".date("Y-m-d H:i:s").", Host: ".$r['host'].", Info: ".$r['info'].", PROCESS ID: ".$r['id']."\n<br />";
			}
			
			$i++;
		}
		$con->sql_freeresult($q1);
		sleep(2);
	}
}

function test_send_email(){
	global $config, $con, $appCore;
	
	$email_address = trim($_REQUEST['email']);
	if(!$email_address)	die('What is your email address.');
	
	include_once("include/class.phpmailer.php");
	
    $mailer = new PHPMailer(true);
    //$mailer->From = "noreply@arms.com.my";
    $mailer->FromName = "ARMS Notification";
    $mailer->Subject = "ARMS TESTING EMAIL";
    $mailer->IsHTML(true);
    //$mailer->IsMail();
	//$mailer->SMTPDebug=1;
	//print_r($mailer);
	
	// gmail thingy...
	/*$mailer->CharSet = 'UTF-8';
	$mailer->IsSMTP(); // enable SMTP
	//$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
	$mailer->SMTPAuth = true; // authentication enabled
	$mailer->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for GMail
	$mailer->Host = "smtp.gmail.com";
	$mailer->Port = 587; // or 587

	$mailer->Username = "weboxcms@gmail.com";
	$mailer->Password = "webox123";

	$mailer->CharSet = 'UTF-8';
	$mailer->IsSMTP(); // enable SMTP
	//$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
	$mailer->SMTPAuth = true; // authentication enabled
	$mailer->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for GMail
	$mailer->Host = "smtp.mandrillapp.com";
	$mailer->Port = 587; // or 587
	
	$mailer->Username = "noreply@arms.com.m";
	$mailer->Password = "BoMz1MvZlN2aJ_xPPZk3dw";
	*/
	
	//$mailer->addReplyTo('justin@arms.my', 'Roti');

	//$mailer->AddEmbeddedImage('ui/google_play_badge/128x128.png', 'google_play_badge');


    $email_msg_sample = "<h2><u>ARMS Testing Email</u></h2>";
	
	//$encoded_comp_logo = base64_encode(file_get_contents("ui/google_play_badge/128x128.png"));
	//print $encoded_comp_logo;exit;
	$email_msg_sample .= "Google: <a href='".$_SERVER['HTTP_HOST']."/membership.eform.php'><img src=\"maximus.ddns.my:2001/ui/google_play_badge/128x128.png\" height='128' width='128' alt='Get from Google Play' /></a>";
	$mailer->AddAddress($email_address);
	//$mailer->AddAddress('tommy_lts@yahoo.com');
	//$mailer->AddAddress('tommy@arms.my');
	$mailer->AddAddress("chingharn@hotmail.com");
	//$mailer->AddAddress("nava@arms.my");
    $mailer->Body = $email_msg_sample;
    // send the mail
	
    print "send email to $email_address ";
	//print_r($mailer);
    if($send_success = phpmailer_send($mailer, $mailer_info)){
    	print ": OK";
    }else{
    	print ": Failed";
		print "> ".$mailer_info['err'];
    }

	$mailer->ClearAddresses();
}

function exec_send_email(){
	global $con;
	
	//exec('echo "whoami" | at now');
	print shell_exec("php cron.send_email.php -branch=all -send > exec_send_email.log &");
	print "Cron Executed.";
}

function delete_sock2_unused_data(){
	
	$db_default_connection = array(":/tmp/mysql.sock2", "root", "", "armshq");
	$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);
	
	// tables that need to truncate
	$table_list = array("pos","pos_cash_domination%","pos_cash_history%","pos_counter_collection%","pos_drawer%","pos_goods_return%","pos_items%","pos_payment%","pos_receipt_cancel%","pos_finalize%","sales_target%","session%","pivot%","debtor%","voucher%","gr%","log%","%approval%","%backup%","pm%","po","po_%","adj%","gr%","do%","ri%","sales_order%","cni%","dn%","ci%","sku_items_cost%","stock_%","vendor_sku%","consignment%","%cache%","%history%","card_nric","membership_points","membership_redemption%","purchase_agreement%");
	
	foreach($table_list as $tables){
		$q1 = $con->sql_query("show tables like '".$tables."'");
		
		while($t = $con->sql_fetchrow($q1)){
			$table = $t[0];
			if(!$table) continue;
		
			$con->sql_query("truncate $table");
			print "truncated $table <br />";
		}
		$con->sql_freeresult($q1);
	}
}

function fix_trigger(){
	global $con;

$triggers=array(
"pos_trigger_branch_insert"=>"CREATE TRIGGER `pos_trigger_branch_insert` AFTER insert ON `branch`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('branch', NEW.id, @new_id);
delete from tmp_trigger where tablename='branch' and id=NEW.id;
END;",
"pos_trigger_branch_update"=>"CREATE TRIGGER `pos_trigger_branch_update` AFTER update ON `branch`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('branch', NEW.id, @new_id);
delete from tmp_trigger where tablename='branch' and id=NEW.id;
END;",
"pos_trigger_sku_update"=>"CREATE TRIGGER `pos_trigger_sku_update` AFTER UPDATE ON `sku`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku', NEW.id, @new_id);
delete from tmp_trigger where tablename='sku' and id=NEW.id;
END;",
"pos_trigger_sku_insert"=>"CREATE TRIGGER `pos_trigger_sku_insert` AFTER INSERT ON `sku`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku', NEW.id, @new_id);
delete from tmp_trigger where tablename='sku' and id=NEW.id;
END;",
"pos_trigger_sku_items_update"=>"CREATE TRIGGER `pos_trigger_sku_items_update` AFTER UPDATE ON `sku_items`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku_items', NEW.id, @new_id);
delete from tmp_trigger where tablename='sku_items' and id=NEW.id;
END;",
"pos_trigger_sku_items_insert"=>"CREATE TRIGGER `pos_trigger_sku_items_insert` AFTER INSERT ON `sku_items`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku_items', NEW.id, @new_id);
delete from tmp_trigger where tablename='sku_items' and id=NEW.id;
END;",
"pos_trigger_category_insert"=>"CREATE TRIGGER `pos_trigger_category_insert` AFTER INSERT ON `category`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('category', NEW.id, @new_id);
delete from tmp_trigger where tablename='category' and id=NEW.id;
END;",
"pos_trigger_category_update"=>"CREATE TRIGGER `pos_trigger_category_update` AFTER update ON `category`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('category', NEW.id, @new_id);
delete from tmp_trigger where tablename='category' and id=NEW.id;
END;",
"pos_trigger_counter_settings_insert"=>"CREATE TRIGGER `pos_trigger_counter_settings_insert` AFTER INSERT ON `counter_settings`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('counter_settings', NEW.id, @new_id);
delete from tmp_trigger where tablename='counter_settings' and id=NEW.id;
END;",
"pos_trigger_counter_settings_update"=>"CREATE TRIGGER `pos_trigger_counter_settings_update` AFTER update ON `counter_settings`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('counter_settings', NEW.id, @new_id);
delete from tmp_trigger where tablename='counter_settings' and id=NEW.id;
END;",
"pos_trigger_membership_insert"=>"CREATE TRIGGER `pos_trigger_membership_insert` AFTER INSERT ON `membership`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_member_trigger_log);
replace into tmp_member_trigger_log (nric, row_index) values (NEW.nric, @new_id);
delete from tmp_member_trigger where nric=NEW.nric;
END;",
"pos_trigger_membership_update"=>"CREATE TRIGGER `pos_trigger_membership_update` AFTER update ON `membership`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_member_trigger_log);
replace into tmp_member_trigger_log (nric, row_index) values (NEW.nric, @new_id);
delete from tmp_member_trigger where nric=NEW.nric;
END;",
"pos_trigger_membership_redemption_sku_insert"=>"CREATE TRIGGER `pos_trigger_membership_redemption_sku_insert` AFTER INSERT ON `membership_redemption_sku`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('membership_redemption_sku', NEW.id, @new_id);
delete from tmp_trigger where tablename='membership_redemption_sku' and id=NEW.id;
END;",
"pos_trigger_membership_redemption_sku_update"=>"CREATE TRIGGER `pos_trigger_membership_redemption_sku_update` AFTER update ON `membership_redemption_sku`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('membership_redemption_sku', NEW.id, @new_id);
delete from tmp_trigger where tablename='membership_redemption_sku' and id=NEW.id;
END;",
"pos_trigger_promotion_insert"=>"CREATE TRIGGER `pos_trigger_promotion_insert` AFTER INSERT ON `promotion`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('promotion', NEW.id*1000+NEW.branch_id, @new_id);
delete from tmp_trigger where tablename='promotion' and id=(NEW.id*1000+NEW.branch_id);
END;",
"pos_trigger_promotion_update"=>"CREATE TRIGGER `pos_trigger_promotion_update` AFTER update ON `promotion`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('promotion', NEW.id*1000+NEW.branch_id, @new_id);
delete from tmp_trigger where tablename='promotion' and id=(NEW.id*1000+NEW.branch_id);
END;",
"pos_trigger_uom_insert"=>"CREATE TRIGGER `pos_trigger_uom_insert` AFTER INSERT ON `uom`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('uom', NEW.id, @new_id);
delete from tmp_trigger where tablename='uom' and id=NEW.id;
END;",
"pos_trigger_uom_update"=>"CREATE TRIGGER `pos_trigger_uom_update` AFTER update ON `uom`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('uom', NEW.id, @new_id);
delete from tmp_trigger where tablename='uom' and id=NEW.id;
END;",
"pos_trigger_user_insert"=>"CREATE TRIGGER `pos_trigger_user_insert` AFTER INSERT ON `user`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('user', NEW.id, @new_id);
delete from tmp_trigger where tablename='user' and id=NEW.id;
END;",
"pos_trigger_user_update"=>"CREATE TRIGGER `pos_trigger_user_update` AFTER update ON `user`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('user', NEW.id, @new_id);
delete from tmp_trigger where tablename='user' and id=NEW.id;
END;",
"pos_trigger_sa_insert"=>"CREATE TRIGGER `pos_trigger_sa_insert` AFTER INSERT ON `sa`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sa', NEW.id, @new_id);
delete from tmp_trigger where tablename='sa' and id=NEW.id;
END;",
"pos_trigger_sa_update"=>"CREATE TRIGGER `pos_trigger_sa_update` AFTER update ON `sa`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sa', NEW.id, @new_id);
delete from tmp_trigger where tablename='sa' and id=NEW.id;
END;",
"pos_trigger_return_policy_insert"=>"CREATE TRIGGER `pos_trigger_return_policy_insert` AFTER INSERT ON `return_policy`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('return_policy', NEW.id, @new_id);
delete from tmp_trigger where tablename='return_policy' and id=NEW.id;
END;",
"pos_trigger_return_policy_update"=>"CREATE TRIGGER `pos_trigger_return_policy_update` AFTER update ON `return_policy`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('return_policy', NEW.id, @new_id);
delete from tmp_trigger where tablename='return_policy' and id=NEW.id;
END;",
"pos_trigger_sku_group_item_insert"=>"CREATE TRIGGER `pos_trigger_sku_group_item_insert` AFTER INSERT ON `sku_group_item`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku_group_item', NEW.sku_group_id, @new_id);
delete from tmp_trigger where tablename='sku_group_item' and id=NEW.sku_group_id;
END;",
"pos_trigger_sku_group_item_update"=>"CREATE TRIGGER `pos_trigger_sku_group_item_update` AFTER update ON `sku_group_item`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku_group_item', NEW.sku_group_id, @new_id);
delete from tmp_trigger where tablename='sku_group_item' and id=NEW.sku_group_id;
END;",
"pos_trigger_return_policy_setup_insert"=>"CREATE TRIGGER `pos_trigger_return_policy_setup_insert` AFTER INSERT ON `return_policy_setup`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('return_policy_setup', NEW.id, @new_id);
delete from tmp_trigger where tablename='return_policy_setup' and id=NEW.id;
END;",
"pos_trigger_return_policy_setup_update"=>"CREATE TRIGGER `pos_trigger_return_policy_setup_update` AFTER update ON `return_policy_setup`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('return_policy_setup', NEW.id, @new_id);
delete from tmp_trigger where tablename='return_policy_setup' and id=NEW.id;
END;",
"pos_trigger_sku_items_future_price_insert"=>"CREATE TRIGGER `pos_trigger_sku_items_future_price_insert` AFTER INSERT ON `sku_items_future_price`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku_items_future_price', NEW.id, @new_id);
delete from tmp_trigger where tablename='sku_items_future_price' and id=NEW.id;
END;",
"pos_trigger_sku_items_future_price_update"=>"CREATE TRIGGER `pos_trigger_sku_items_future_price_update` AFTER update ON `sku_items_future_price`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku_items_future_price', NEW.id, @new_id);
delete from tmp_trigger where tablename='sku_items_future_price' and id=NEW.id;
END;",
"pos_trigger_sku_items_future_price_items_insert"=>"CREATE TRIGGER `pos_trigger_sku_items_future_price_items_insert` AFTER INSERT ON `sku_items_future_price_items`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku_items_future_price_items', NEW.id, @new_id);
delete from tmp_trigger where tablename='sku_items_future_price_items' and id=NEW.id;
END;",
"pos_trigger_sku_items_future_price_items_update"=>"CREATE TRIGGER `pos_trigger_sku_items_future_price_items_update` AFTER update ON `sku_items_future_price_items`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku_items_future_price_items', NEW.id, @new_id);
delete from tmp_trigger where tablename='sku_items_future_price_items' and id=NEW.id;
END;",
"pos_trigger_coupon_update"=>"CREATE TRIGGER `pos_trigger_coupon_update` AFTER update ON `coupon`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('coupon', NEW.id, @new_id);
delete from tmp_trigger where tablename='coupon' and id=NEW.id;
END;",
"pos_trigger_coupon_insert"=>"CREATE TRIGGER `pos_trigger_coupon_insert` AFTER insert ON `coupon`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('coupon', NEW.id, @new_id);
delete from tmp_trigger where tablename='coupon' and id=NEW.id;
END;",
"pos_trigger_gpm_broadcast_msg_insert"=>"CREATE TRIGGER `pos_trigger_gpm_broadcast_msg_insert` AFTER insert ON `gpm_broadcast_msg`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('gpm_broadcast_msg', NEW.id, @new_id);
delete from tmp_trigger where tablename='gpm_broadcast_msg' and id=NEW.id;
END;",
"pos_trigger_gpm_broadcast_msg_update"=>"CREATE TRIGGER `pos_trigger_gpm_broadcast_msg_update` AFTER update ON `gpm_broadcast_msg`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('gpm_broadcast_msg', NEW.id, @new_id);
delete from tmp_trigger where tablename='gpm_broadcast_msg' and id=NEW.id;
END;",
"pos_trigger_gpm_broadcast_trade_offer_insert"=>"CREATE TRIGGER `pos_trigger_gpm_broadcast_trade_offer_insert` AFTER insert ON `gpm_broadcast_trade_offer`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('gpm_broadcast_trade_offer', NEW.id, @new_id);
delete from tmp_trigger where tablename='gpm_broadcast_trade_offer' and id=NEW.id;
END;",
"pos_trigger_gpm_broadcast_trade_offer_update"=>"CREATE TRIGGER `pos_trigger_gpm_broadcast_trade_offer_update` AFTER update ON `gpm_broadcast_trade_offer`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('gpm_broadcast_trade_offer', NEW.id, @new_id);
delete from tmp_trigger where tablename='gpm_broadcast_trade_offer' and id=NEW.id;
END;",
"pos_trigger_gst_insert"=>"CREATE TRIGGER `pos_trigger_gst_insert` AFTER INSERT ON `gst`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('gst', NEW.id, @new_id);
delete from tmp_trigger where tablename='gst' and id=NEW.id;
END;",
"pos_trigger_gst_update"=>"CREATE TRIGGER `pos_trigger_gst_update` AFTER update ON `gst`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('gst', NEW.id, @new_id);
delete from tmp_trigger where tablename='gst' and id=NEW.id;
END;",
"pos_trigger_gst_settings_insert"=>"CREATE TRIGGER `pos_trigger_gst_settings_insert` AFTER INSERT ON `gst_settings`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('gst_settings', 1, @new_id);
END;",
"pos_trigger_gst_settings_update"=>"CREATE TRIGGER `pos_trigger_gst_settings_update` AFTER update ON `gst_settings`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('gst_settings', 1, @new_id);
END;",
"membership_points_pos_insert_trigger"=>"CREATE TRIGGER `membership_points_pos_insert_trigger`
AFTER INSERT ON `pos`
FOR EACH ROW BEGIN
delete from tmp_membership_points_trigger where card_no = NEW.member_no ;
END;",
"membership_points_pos_update_trigger"=>"CREATE TRIGGER `membership_points_pos_update_trigger`
AFTER UPDATE ON `pos`
FOR EACH ROW BEGIN
delete from tmp_membership_points_trigger where card_no = NEW.member_no or card_no = OLD.member_no ;
END;",
"membership_points_pos_delete_trigger"=>"CREATE TRIGGER `membership_points_pos_delete_trigger`
AFTER DELETE ON `pos`
FOR EACH ROW BEGIN
delete from tmp_membership_points_trigger where card_no = OLD.member_no ;
END;");

	foreach($triggers as $trigger=>$sql){
		$q1 = $con->sql_query("show triggers where `Trigger` like '".$trigger."'");
		$t = $con->sql_fetchassoc($q1);

		if(!$t){
			echo $trigger."<br/>";
			try{
				$con->sql_query($sql);
				echo "Trigger ".$trigger." added<br/>";
			}catch (Exception $e){
				echo '<pre>';
				print_r($e);
				echo '</pre>';
			}
		}
	}
}

function fix_pos_discount2(){
	global $con, $arg;

	$start=$arg[2];
	$end=$arg[3];
	$branch_id=intval($arg[4]);

	if(!strtotime($start)||!strtotime($end))  die("Invalid Date.\n");
	if($branch_id>0) $branch_id=" and p.branch_id=$branch_id";
	else $branch_id="";

	fix_mix_n_match_missing($start, $end, $branch_id);
	
	$sql="select p.branch_id,p.counter_id,p.date,p.id as pos_id,p.receipt_no,p.amount, round((select sum(if(pp.type='Cash',pp.amount-p.amount_change,pp.amount)) from pos_payment pp where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type not in ('Rounding') and pp.adjust=0),2) as pp_amt,
	ifnull(round((select sum(pi.price-pi.discount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0) as pi_amt,
	ifnull(round((select sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0) as nett_price,
	ifnull(round((select sum(pi.before_tax_price) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0) as before_tax_price,
	ifnull(round((select sum(pi.tax_amount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0) as pi_gst_amt,
	round(ifnull((select sum(pd.gst_amount)
	from pos_deposit_status pds
	join pos_deposit pd on pd.branch_id=pds.deposit_branch_id and pd.counter_id=pds.deposit_counter_id and pd.date=pds.deposit_date and pd.pos_id=pds.deposit_pos_id
	where pds.branch_id=p.branch_id and pds.counter_id=p.counter_id and pds.date=p.date and pds.pos_id=p.id 
	),0),2) as deposit_gst_amt,
	p.total_gst_amt,
	ifnull(round((select sum(pi.discount2) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2),0) as total_discount2,
	ifnull(round((select sum(pp.amount) from pos_payment pp where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type in ('Mix & Match Total Disc','Discount') and pp.adjust=0),2),0) as total_receipt_mix_discount
	from pos p
	where p.date >= ".ms($start)."
	and p.date <= ".ms($end)."
	and p.cancel_status=0
	and p.is_gst=1 and p.deposit=0
	$branch_id
	having (abs(pp_amt-pi_amt)>0.02) or (nett_price<>before_tax_price) or (round(pi_gst_amt-deposit_gst_amt,2)<>total_gst_amt) or (total_discount2<> total_receipt_mix_discount)";

	$q1=$con->sql_query($sql);

	//echo $sql."\n\n";

	echo $con->sql_numrows($q1)." row found\n";
	$fixed = $notfixed = 0;
	$arr=array();

	while($r = $con->sql_fetchassoc($q1)){
		if($r['total_discount2'] > 0 && $r['total_receipt_mix_discount']>0 && $r['total_discount2']!=$r['total_receipt_mix_discount']){
			$ratio=$r['total_discount2'] / $r['total_receipt_mix_discount'];
			if(ctype_digit(strval($ratio))){
				$q2=$con->sql_query("select * from pos_items where branch_id=".$r['branch_id']." and counter_id=".$r['counter_id']." and date='".$r['date']."' and pos_id=".$r['pos_id']);
				while($r2 = $con->sql_fetchassoc($q2)){
					if($r2['discount2']>0){
						$discount2=$r2['discount2']/$ratio;

						if(mf($r2['tax_rate'])>0){
							$after_tax_price = round(($r2['price']-$r2['discount']-$discount2),2);
							$before_tax_price = round((($after_tax_price/(mf($r2['tax_rate'])+100))*100),2);
							$tax_amount=$after_tax_price-$before_tax_price;
						}
						else{
							$tax_amount=0;
							$before_tax_price=round(($r2['price']-$r2['discount']-$discount2),2);
						}

						$upd=array();
						$upd['discount2']=$discount2;
						$upd['tax_amount']=$tax_amount;
						$upd['before_tax_price']=$before_tax_price;

						$upd_query="update pos_items set ".mysql_update_by_field($upd)."
										  where branch_id = ".mi($r2['branch_id'])."
										  and counter_id=".mi($r2['counter_id'])."
										  and date=".ms($r2['date'])."
										  and pos_id=".mi($r2['pos_id'])."
										  and id=".mi($r2['id']);
						//echo $upd_query."\n";
						$con->sql_query($upd_query);
					}
				}

				if(!isset($arr[$r['date']])) $arr[$r['date']]=array();
				if(!isset($arr[$r['date']][$r['branch_id']])) $arr[$r['date']][$r['branch_id']]=array();
				if(!isset($arr[$r['date']][$r['branch_id']][$r['counter_id']])) $arr[$r['date']][$r['branch_id']][$r['counter_id']]=array();
				if(!in_array($r['pos_id'], $arr[$r['date']][$r['branch_id']][$r['counter_id']])) $arr[$r['date']][$r['branch_id']][$r['counter_id']][]=$r['pos_id'];

				$fixed++;
			}
			else{

			}
			/*
			$q2=$con->sql_query("select * from pos where branch_id=".$r['branch_id']." and counter_id=".$r['counter_id']." and date='".$r['date']."' and id=".$r['pos_id']);
			$q3=$con->sql_query("select * from pos_mix_match_usage where branch_id=".$r['branch_id']." and counter_id=".$r['counter_id']." and date='".$r['date']."' and pos_id=".$r['pos_id'];
			*/
		}
		else{
			if($r['pi_gst_amt']!=$r['total_gst_amt']){
				if(!isset($arr[$r['date']])) $arr[$r['date']]=array();
				if(!isset($arr[$r['date']][$r['branch_id']])) $arr[$r['date']][$r['branch_id']]=array();
				if(!isset($arr[$r['date']][$r['branch_id']][$r['counter_id']])) $arr[$r['date']][$r['branch_id']][$r['counter_id']]=array();
				if(!in_array($r['pos_id'], $arr[$r['date']][$r['branch_id']][$r['counter_id']])) $arr[$r['date']][$r['branch_id']][$r['counter_id']][]=$r['pos_id'];
				$fixed++;
			}
			else{
				if(($r['nett_price']!=$r['before_tax_price']) || ($r['total_discount2'] != 0 && $r['total_receipt_mix_discount'] == 0)){

					$sql='select * from pos_items
					where branch_id='.mi($r['branch_id']).'
					and counter_id='.mi($r['counter_id']).'
					and date='.ms($r['date']).'
					and pos_id='.mi($r['pos_id']);

					$q2 = $con->sql_query($sql);
					while($r2 = $con->sql_fetchassoc($q2)){
						if($r['total_discount2'] != 0 && $r['total_receipt_mix_discount'] == 0) $r2['discount2'] = 0;
						
						if(mf($r2['tax_rate'])>0){
							$after_tax_price = round(($r2['price']-$r2['discount']-$r2['discount2']),2);
							$before_tax_price = round((($after_tax_price/(mf($r2['tax_rate'])+100))*100),2);
							$tax_amount=$after_tax_price-$before_tax_price;
						}
						else{
							$tax_amount=0;
							$before_tax_price=round(($r2['price']-$r2['discount']-$r2['discount2']),2);
						}

						$upd=array();
						$upd['discount2']=$r2['discount2'];
						$upd['tax_amount']=$tax_amount;
						$upd['before_tax_price']=$before_tax_price;

						$upd_query="update pos_items set ".mysql_update_by_field($upd)."
									  where branch_id = ".mi($r2['branch_id'])."
									  and counter_id=".mi($r2['counter_id'])."
									  and date=".ms($r2['date'])."
									  and pos_id=".mi($r2['pos_id'])."
									  and id=".mi($r2['id']);
						//echo $upd_query."\n";
						$con->sql_query($upd_query);
					}

					if(!isset($arr[$r['date']])) $arr[$r['date']]=array();
					if(!isset($arr[$r['date']][$r['branch_id']])) $arr[$r['date']][$r['branch_id']]=array();
					if(!isset($arr[$r['date']][$r['branch_id']][$r['counter_id']])) $arr[$r['date']][$r['branch_id']][$r['counter_id']]=array();
					if(!in_array($r['pos_id'], $arr[$r['date']][$r['branch_id']][$r['counter_id']])) $arr[$r['date']][$r['branch_id']][$r['counter_id']][]=$r['pos_id'];

					$fixed++;
				}
				else{
					$notfixed++;
				}
			}
		}
	}
	unset($branch_id);

	foreach($arr as $date=>$branches){
		foreach($branches as $branch_id=>$counters){
			foreach($counters as $counter_id=>$pos){
				foreach($pos as $pos_id){
				  $sql='select round(sum(tax_amount),2) as total from pos_items
						where date='.ms($date).'
						and branch_id='.mi($branch_id).'
						and counter_id='.mi($counter_id).'
						and pos_id='.mi($pos_id);
				  $q2 = $con->sql_query($sql);
				  $pi_gst = $con->sql_fetchassoc($q2);
				  $con->sql_freeresult($q2);
				  
				  $con->sql_query("select round(sum(pd.gst_amount),2) as total
					from pos_deposit_status pds
					join pos_deposit pd on pd.branch_id=pds.deposit_branch_id and pd.counter_id=pds.deposit_counter_id and pd.date=pds.deposit_date and pd.pos_id=pds.deposit_pos_id
					where pds.branch_id=$branch_id and pds.counter_id=$counter_id and pds.date=".ms($date)." and pds.pos_id=$pos_id
					");
					$pd_gst = $con->sql_fetchassoc();
					$con->sql_freeresult();

				  $upd=array();
				  $upd['total_gst_amt'] = round($pi_gst['total']-$pd_gst['total'], 2);

				  $upd_query="update pos set ".mysql_update_by_field($upd)."
								where branch_id = ".mi($branch_id)."
								and counter_id=".mi($counter_id)."
								and date=".ms($date)."
								and id=".mi($pos_id);
				  //echo $upd_query."\n";
				  //die("test\n");
				  $con->sql_query($upd_query);
				}
			}

			$sql="select * from pos_finalized where branch_id=".mi($branch_id)." and date=".ms($date);
			$q1=$con->sql_query($sql);
			$r = $con->sql_fetchassoc($q1);
			if($r['finalized']){
			  //echo "update_sales_cache($branch_id, $date));"."\n";
			  update_sales_cache($branch_id, $date);
			  echo "Finalize Complete: branch_id:".$branch_id,"  date:".$date."\n";
			}
			$con->sql_freeresult($q1);
		}
	}


	echo $fixed." is fixed and ".$notfixed." not fix.\n";
	echo "Done\n";
}

// temp_script.php?is_http=1&a=delete_invalid_sb_table
// php temp_script.php delete_invalid_sb_table gurun
// php temp_script.php delete_invalid_sb_table gurun 1
function delete_invalid_sb_table($prms){
	global $con;
	
	$bid = "";
	if($prms['branch_code']){
		$q1 = $con->sql_query("select * from branch where code = ".ms($prms['branch_code']));
		
		if($con->sql_numrows($q1) == 0){
			print "Invalid Branch Code: ".$prms['branch_code']."\n";
			exit;
		}
		
		$branch_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$bid = $branch_info['id']."\\\_";
	}
	
	$q1 = $con->sql_query("show tables like '%stock_balance_b$bid%'");
	while($r = $con->sql_fetchrow($q1)){
		list($dummy, $dummy2, $dummy3, $year) = explode("_", $r[0]);
		
		if($year<2007){
			if($prms['is_run']){
				$con->sql_query("drop table $r[0]");
				print $r[0]." dropped.\n";
			}else{
				print $r[0]." need drop.\n";
			}			
		}
	}
	$con->sql_freeresult($q1);
}

function fix_pos_item_discount(){
	global $con, $arg;

	$start=$arg[2];
	$end=$arg[3];
	$branch_id=intval($arg[4]);
	$counter_id=intval($arg[5]);

	if(!strtotime($start)||!strtotime($end))  die("Invalid Date.\n");
	if($branch_id>0) $branch_id=" and branch_id=$branch_id";
	else $branch_id="";

	if($counter_id>0) $counter_id=" and counter_id=$counter_id";
	else $counter_id="";

	$sql="select * from pos_items
	where date>='".$start."' and date<='".$end."'
	$branch_id
	and discount>0
	$counter_id
	and promotion_id=0
	order by date, branch_id, counter_id, pos_id";

	//echo $sql."\n\n";

	$q1=$con->sql_query($sql);

    $arr=array();
    $pos=array();
	while($r = $con->sql_fetchassoc($q1)){
		$r['more_info']=unserialize($r['more_info']);

		if(isset($r['more_info']['discount_str'])){
			$discount_str=$r['more_info']['discount_str'];
			if (preg_match("/^(\d+(\.\d+)?)%$/",trim($discount_str), $matches)){
				$discount = round($r['price']*($matches[1]/100),2);

				if(floatval($r['discount'])!=floatval($discount)){
					if(!isset($arr[$r['date']])) $arr[$r['date']]=array();
					if(!isset($arr[$r['date']][$r['branch_id']])) $arr[$r['date']][$r['branch_id']]=array();
					if(!isset($arr[$r['date']][$r['branch_id']][$r['counter_id']])) $arr[$r['date']][$r['branch_id']][$r['counter_id']]=array();
					if(!in_array($r['pos_id'], $arr[$r['date']][$r['branch_id']][$r['counter_id']])) $arr[$r['date']][$r['branch_id']][$r['counter_id']][]=$r['pos_id'];
				}
			} 
		}
	}
	$con->sql_freeresult($q1);

	$i=0;
	$rounding_diff=0;
	$old_pos_rounding_total = $new_pos_rounding_total = 0;
	foreach($arr as $date=>$branches){
		foreach($branches as $branch_id=>$counters){
			foreach($counters as $counter_id=>$sales){
				foreach($sales as $pos_id){
					$item_price=array();
					$fixed_price=array();
					$new_pos_total=0;
					$old_pos_rounding=0;
					$pos_payment=false;

					$sql="select * from pos
					where branch_id=".mi($branch_id)."
					and counter_id=".mi($counter_id)."
					and date=".ms($date)."
					and id=".mi($pos_id);

					$q2=$con->sql_query($sql);
					$pos = $con->sql_fetchassoc($q2);
					$con->sql_freeresult($q2);

					$sql="select * from pos_payment
					where branch_id=".mi($branch_id)."
					and counter_id=".mi($counter_id)."
					and date=".ms($date)."
					and pos_id=".mi($pos_id)."
					and type='Rounding'";

					$q2=$con->sql_query($sql);
					if($con->sql_numrows($q2)){
						$pos_payment = $con->sql_fetchassoc($q2);
						$old_pos_rounding=$pos_payment['amount'];
					}
					$con->sql_freeresult($q2);

					$sql="select * from pos_items
					where branch_id=".mi($branch_id)."
					and counter_id=".mi($counter_id)."
					and date=".ms($date)."
					and pos_id=".mi($pos_id);

					$q2=$con->sql_query($sql);
					
					while($r = $con->sql_fetchassoc($q2)){
						$r['more_info']=unserialize($r['more_info']);
						if(isset($r['more_info']['discount_str'])){
							$discount_str=$r['more_info']['discount_str'];
							if (preg_match("/^(\d+(\.\d+)?)%$/",trim($discount_str), $matches)){
								$discount = round($r['price']*($matches[1]/100),2);
								if(floatval($r['discount'])!=floatval($discount)){
									$price=$r['price']-$discount;

									if($r['inclusive_tax']){
										$output_tax=get_sku_gst("output_tax",$r['sku_item_id'], array('no_check_use_zero_rate'=>1));

										$tax_code=$output_tax['code'];
										$tax_indicator=$output_tax['indicator_receipt'];
										$rate=$output_tax['rate'];

										if($rate>0){
										  $after_tax_price = $price;
										  $before_tax_price = round((($price/($rate+100))*100),2);
										  $tax_amount=$after_tax_price-$before_tax_price;
										}
										else{
										  $tax_amount=0;
										  $before_tax_price=$price;
										}

										$upd=array();
										$upd['discount'] = $discount;
										$upd['tax_amount'] = $tax_amount;
										$upd['before_tax_price'] = $before_tax_price;

										$upd_query="update pos_items set ".mysql_update_by_field($upd)."
														where branch_id = ".mi($branch_id)."
														and counter_id=".mi($counter_id)."
														and date=".ms($date)."
														and pos_id=".mi($pos_id)."
														and id=".mi($r['id']);
										//$con->sql_query($upd_query);

										$item_price[]= "-----------------------------------------\n";
										$item_price[]= $r['item_id']."\t\tOld\tNew\n";
										$item_price[]= "Price:\t\t".$r['price']."\t".$r['price']."\n";
										$item_price[]= "Discount:\t".$r['discount']."\t".$discount."\n";
										$item_price[]= "After:\t\t".($r['price']-$r['discount'])."\t".$price."\n";
										$item_price[]= "Before:\t\t".$r['before_tax_price']."\t".$before_tax_price."\n";
										$item_price[]= "Tax:\t\t".$r['tax_amount']."\t".$tax_amount."\n";
									}
								}
								else{
									$price=$r['price']-$r['discount'];
								}
							}
							else{
								$price=$r['price']-$r['discount'];
							}
						}
						else{
							$price=$r['price']-$r['discount'];
						}
						
						$fixed_price[]=$r['item_id']."\t\t".($r['price']-$r['discount'])."\t".$price."\n";

						$new_pos_total+=$price;
					}
					$con->sql_freeresult($q2);

					$old_pos_amount=$pos["amount"];
					$old_pos_total=$old_pos_amount-$old_pos_rounding;
					$new_pos_rounding=round($old_pos_amount-$new_pos_total,2);

					if($new_pos_rounding==-0) $new_pos_rounding=0;

					$new_pos_amount=$new_pos_total+$new_pos_rounding;

					$fixed_price[]=".....................................\n";
					$fixed_price[]="Total:\t\t".$old_pos_total."\t".$new_pos_total."\n";
					$fixed_price[]="Rounding:\t".$old_pos_rounding."\t".$new_pos_rounding."\n";
					$fixed_price[]="Amount:\t\t".$old_pos_amount."\t".$new_pos_amount."\n";
					$fixed_price[]=".....................................\n";

					//if($new_pos_rounding<=-0.03){
					if($new_pos_rounding>-0.03){
						$i++;

						echo "Date: ".$pos['date']."    Branch: ".$pos['branch_id']."    Counter: ".$pos['counter_id']."    Receipt No.: ".$pos['receipt_no']."\n";

						foreach($item_price as $ip){
							echo $ip;
						}
						echo "=========================================\n";
						foreach($fixed_price as $fp){
							echo $fp;
						}
						echo "\n\n";

						$sql='select round(sum(tax_amount),2) as total from pos_items
							where branch_id='.mi($branch_id).'
							and counter_id='.mi($counter_id).'
							and date='.ms($date).'
							and pos_id='.mi($pos_id);
						$q2 = $con->sql_query($sql);
						$c = $con->sql_fetchassoc($q2);
						$con->sql_freeresult($q2);

						$upd=array();
						$upd['total_gst_amt']=$c['total'];

						$upd_query="update pos set ".mysql_update_by_field($upd)."
									where branch_id = ".mi($branch_id)."
									and counter_id=".mi($counter_id)."
									and date=".ms($date)."
									and id=".mi($pos_id);
						//$con->sql_query($upd_query);

						if($pos_payment){
							$upd=array();
							$upd['amount']=$new_pos_rounding;

							$upd_query="update pos_payment set ".mysql_update_by_field($upd)."
									where branch_id = ".mi($branch_id)."
									and counter_id=".mi($counter_id)."
									and date=".ms($date)."
									and pos_id=".mi($pos_id)."
									and id=".mi($pos_payment["id"]);
						}
						else{
							if($new_pos_rounding!=0){
								$sql3="select max(id) as max from pos_payment
								where branch_id=".mi($branch_id)."
								and counter_id=".mi($counter_id)."
								and date=".ms($date);
								$q3=$con->sql_query($sql3);
								$max=$con->sql_fetchassoc($q3);
								$con->sql_freeresult($q3);
								$max=$max['max']+1;

								$upd=array();
								$upd['branch_id']=$branch_id;
								$upd['counter_id']=$counter_id;
								$upd['id']=$max;
								$upd['pos_id']=$pos_id;
								$upd['type']='Rounding';
								$upd['remark']='';
								$upd['amount']=$new_pos_rounding;
								$upd['date']=$date;
								$upd['changed']=0;
								$upd['adjust']=0;
								$upd['approved_by']=0;
								$upd['more_info']='s:0:\"\";';
								$upd['is_abnormal']=0;

								$upd_query="insert into pos_payment ".mysql_insert_by_field($upd);
							}
						}
						//echo "\n\n".$upd_query."\n\n";
						//$con->sql_query($upd_query);
					}


					//$old_pos_rounding_total+=$old_pos_rounding;
					//$new_pos_rounding_total+=$new_pos_rounding;
				}
			}

			$sql="select * from pos_finalized where branch_id=".mi($branch_id)." and date=".ms($date);
			$q1=$con->sql_query($sql);
			$r = $con->sql_fetchassoc($q1);
			if($r['finalized']){
			  //echo "update_sales_cache($branch_id, $date);"."\n";
			  //update_sales_cache($branch_id, $date);
			  //echo "Finalize Complete: branch_id:".$branch_id,"  date:".$date."\n";
			}
			$con->sql_freeresult($q1);

		}
	}
	//echo $old_pos_rounding_total."\t".$new_pos_rounding_total."\n";
	//echo $new_pos_rounding_total+$old_pos_rounding_total."\n";
	echo "Rows: ".$i."\n";
}

function fix_sku_items_price_history(){
	global $con, $arg;

	$sql='select count(*) as c,added,branch_id
	from sku_items_price_history
	where added>="2015-07-23"
	and added<"2015-07-24" group by added,branch_id having c > 100';

	$q1=$con->sql_query($sql);
	while($r = $con->sql_fetchassoc($q1)){
		$sql='select branch_id, sku_item_id, count(*) as ttl_count
		from sku_items_price_history
		where added ="'.$r['added'].'"
		and branch_id="'.$r['branch_id'].'"
		group by sku_item_id, branch_id having ttl_count > 1';

		$q2=$con->sql_query($sql);
		while($r2 = $con->sql_fetchassoc($q2)){
			$sql="select * from sku_items_price where sku_item_id=".mi($r2['sku_item_id'])." and branch_id=".mi($r2['branch_id']);
			$q3=$con->sql_query($sql);
			$sku_items_price = $con->sql_fetchassoc($q3);
			$con->sql_freeresult($q3);

			/*
			$sql="select count(*) as count from sku_items_price_history
			where sku_item_id=".mi($r2['sku_item_id'])."
			and branch_id=".mi($r2['branch_id'])."
			and price=".ms($sku_items_price['price']);
			$q3=$con->sql_query($sql);
			$r3 = $con->sql_fetchassoc($q3);
			$con->sql_freeresult($q3);

			if($r3['count']>1){
				print_r($sku_items_price);
			}
			*/

			$sql="select * from sku_items_price_history
			where sku_item_id=".mi($r2['sku_item_id'])."
			and branch_id=".mi($r2['branch_id'])."
			order by added desc";

			$q3=$con->sql_query($sql);
			while($r3 = $con->sql_fetchassoc($q3)){
				if($r3['price']==$sku_items_price['price']){
					$added=$r3['added'];
					$new_added=date("Y-m-d H:i:s",strtotime($added." + 1 second"));

					$upd=array();
					$upd['added']=$new_added;
					$upd_query="update sku_items_price_history set ".mysql_update_by_field($upd)."
								where branch_id = ".mi($r3['branch_id'])."
								and sku_item_id=".mi($r3['sku_item_id'])."
								and added=".ms($added)."
								and price=".ms($r3['price'])."
								limit 1";

					$con->sql_query($upd_query);
					unset($added,$new_added,$upd);
					break;
				}
			}
			$con->sql_freeresult($q3);
		}
		$con->sql_freeresult($q2);
	}
	$con->sql_freeresult($q1);
	echo "Done\n";
}

//php temp_script.php fix_gra_items_amount
function fix_gra_items_amount(){
	global $con, $arg;

	$sql="select gi.*,g.sku_type,g.is_under_gst from gra_items gi
    left join gra g on g.id=gi.gra_id and g.branch_id=gi.branch_id
    where (gi.amount=0 or gi.amount_gst=0) and g.is_under_gst=1";

	$q1=$con->sql_query($sql);

	while($gra_item = $con->sql_fetchassoc($q1)){

		$gst=$gra_item['gst'];
		$amount_gst=$gra_item['amount_gst'];

		if($gra_item['sku_type']=='CONSIGN'){
			$amount = $gra_item['qty'] * $gra_item['selling_price'];
			$val = "sum(qty*selling_price)";
		}else{
			$amount = $gra_item['qty'] * $gra_item['cost'];
			$val = "sum(qty*cost)";
		}
		
		if($gra_item['is_under_gst']){
			$amount_gst=round($amount * ((100+$gra_item['gst_rate'])/100),2);
			$gst=$amount_gst-round($amount, 2);
		}
		$amount=round($amount,2);
		
		$con->sql_query("update gra_items set added=added, amount=$amount, gst=$gst, amount_gst=$amount_gst
						where id=".$gra_item['id']." and gra_id = ".$gra_item['gra_id']." and branch_id = ".$gra_item['branch_id']);
		
		//get total amount from gra_items
		$q2 = $con->sql_query("select $val from gra_items where gra_id = ".$gra_item['gra_id']." and branch_id = ".$gra_item['branch_id']);
		$total_amount = $con->sql_fetchfield(0);
		$con->sql_freeresult($q2);
		
		//total gra_items's total amount in gra
		$con->sql_query("update gra set last_update=last_update,amount = $total_amount 
		where id = ".$gra_item['gra_id']." and branch_id = ".$gra_item['branch_id']);
	}
	$con->sql_freeresult($q1);
	
	echo "Done\n";
}

// php temp_script.php check_and_fix_all_image_size
function check_and_fix_all_image_size($directory, array $exts = array('jpeg', 'jpg', 'gif', 'png')){
	$fileList = scandir($directory);
	foreach($fileList as $file){
		if($file != '.' && $file != '..'){
			if (in_array(strtolower(end(explode('.', $file))), $exts)) {
				print $directory.'/'.$file . "\n";
				resize_photo($directory.'/'.$file, $directory.'/'.$file);
 			}else{
				if(is_dir($directory.'/'.$file)) 
					check_and_fix_all_image_size($directory.'/'.$file);
			}
		}
	}
}

function fix_dnote_amount(){
	global $con, $arg;
	
	if(!$_REQUEST['is_http']) die("is_http only");
	
	$id=intval($_REQUEST['id']);
	$branch_id=intval($_REQUEST['branch_id']);
	
	if(!$id) die("ID not found");
	if(!$branch_id) die("Branch ID not found");
	
	$q1 = $con->sql_query("select * from dnote where id = ".mi($id)." and branch_id = ".mi($branch_id));
	$dnote_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	$gra_id=$dnote_info['ref_id'];
	
	$con->sql_query("select * from gra where branch_id=$branch_id and id=$gra_id");
	$gra = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	$q1 = $con->sql_query("select gi.* 
						   from gra_items gi
						   where gi.branch_id=$branch_id and gi.gra_id=$gra_id
						   order by gi.id");

	if($con->sql_numrows($q1) > 0){
		$total_gross_amount=0;
		$total_gst_amount=0;
		$total_amount=0;
		while($r = $con->sql_fetchassoc($q1)){
			$ins=array();
			$row_amount = $r['amount'];
			
			$total_gross_amount += $row_amount;
			$ins['item_gross_amount'] = $row_amount;
			
			if($gra['is_under_gst']){
				$total_gst_amount += $r['gst'];
				$total_amount += $r['amount_gst'];
				
				$ins['item_gst_amount'] = $r['gst'];
				$ins['item_amount'] = $r['amount_gst'];
			}
			
			$con->sql_query("update dnote_items set ".mysql_update_by_field($ins)."
							where dnote_id = ".mi($id)."
							and branch_id = ".mi($branch_id)."
							and sku_item_id=".mi($r['sku_item_id'])."
							and qty=".ms($r['qty']));
		}
		
		$ins=array();
		$ins['total_gross_amount']=$total_gross_amount;
		if($gra['is_under_gst']){
			$ins['total_gst_amount']=$total_gst_amount;
			$ins['total_amount']=$total_amount;
		}
		
		$con->sql_query("update dnote set ".mysql_update_by_field($ins)." where id = ".mi($id)." and branch_id = ".mi($branch_id));
	}
	die("Done");
}

//  php temp_script.php clean_pos_edit
function clean_pos_edit(){
	global $con;

	$drop_count = 0;
	$q1 = $con->sql_query("show tables like 'tmp_pos_%'");
	while($r = $con->sql_fetchrow($q1)){
		if(strpos($r[0], 'tmp_pos_')!==false){
			// drop table
			$con->sql_query("drop table $r[0]");
			print "$r[0] dropped.\n";
			$drop_count++;
		}
	}
	$con->sql_freeresult($q1);
	
	$q1 = $con->sql_query("show tables like 'pos_mix_match_usage_%'");
	while($r = $con->sql_fetchrow($q1)){
		if(strpos($r[0], 'pos_mix_match_usage_')!==false){
			// drop table
			$con->sql_query("drop table $r[0]");
			print "$r[0] dropped.\n";
			$drop_count++;
		}
	}
	$con->sql_freeresult($q1);
	
	print "Done. $drop_count dropped.\n";
}

// php temp_script.php fix_segi_bc_wrong_stock_balance_table
function fix_segi_bc_wrong_stock_balance_table(){
	global $con;
	
	$y = 2017;
	$drop_count = 0;
	
	while($y <= 2999){
		$tbl = "stock_balance_b15_".$y;
		$q_success = $con->sql_query_false("desc $tbl");
		if($q_success){
			$con->sql_query("select count(*) from $tbl");
			$tbl_count = $con->sql_fetchfield(0);
			$con->sql_freeresult();
			
			if($tbl_count <= 0){
				$con->sql_query("drop table $tbl");
				print "Dropped $tbl\n";
				$drop_count++;
			}
		}
		$y++;
	}
	print "Done. $drop_count table dropped.\n";
}

function insert_aneka_member(){
	global $con;
	
	$start_num = 266012;
	$end_num = 267011;
	$total_count = $end_num - $start_num + 1;
	$curr_count = $pass = $fail = 0;
	for($i=$start_num; $i<=$end_num; $i++){
		$curr_count++;
		print "\r$curr_count / $total_count. . . .";
		
		$m_ins = array();
		$m_ins['card_no'] = $m_ins['nric'] = "AK".$i;
		$m_ins['name'] = "KK Membership Promotion";
		$m_ins['member_type'] = "";
		$m_ins['apply_branch_id'] = 10;
		$m_ins['verified_date'] = "2016-06-01";
		$m_ins['verified_by'] = 1;
		$m_ins['issue_date'] = "2016-06-01";
		$m_ins['next_expiry_date'] = "2017-05-31";
		
		
		$con->sql_query("insert into `membership` ".mysql_insert_by_field($m_ins), false, false);

		if($con->sql_affectedrows() > 0){
			$mh_ins['nric'] = $m_ins['nric'];
			$mh_ins['card_no'] = $m_ins['card_no'];
			$mh_ins['branch_id'] = $m_ins['apply_branch_id'];
			$mh_ins['card_type'] = "N";
			$mh_ins['issue_date'] = $m_ins['issue_date'];
			$mh_ins['expiry_date'] = $m_ins['next_expiry_date'];
			$mh_ins['remark'] = "N";
			$mh_ins['added'] = "CURRENT_TIMESTAMP";
			$mh_ins['action_date'] = "CURRENT_TIMESTAMP";
			$mh_ins['user_id'] = 1;

			$con->sql_query("insert into `membership_history` ".mysql_insert_by_field($mh_ins, false, true));

			$pass++;
		}else $fail++;
	}
	print "\nDone. $pass member created.\n";
}

// php temp_script.php update_segi_price BALING segi_price.csv
function update_segi_price($bcode, $filename){
	global $con;
	
	if(!$bcode)	die("No Branch Code.\n");
	if(!file_exists($filename))	die("File '$filename' not exists.\n");
	
	$bid = get_branch_id($bcode);
	if(!$bid)	die("Invalid Branch Code $bcode\n");
	
	$update_count = $failed_count = 0;
	$f = fopen($filename,"rt");
	$line = fgetcsv($f);	// skip header
	while($r = fgetcsv($f)){
		
		$mcode = trim($r[0]);
		$price = mf($r[1]);
		
		
		$con->sql_query("select id from sku_items where mcode=".ms($mcode)." order by id limit 1");
		$si = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$si){
			print "MCode '$mcode' not found.\n";
			$failed_count++;
			continue;
		}else{
			$sid = mi($si['id']);
			
			$con->sql_query("select * from sku_items_price where branch_id=$bid and sku_item_id=$sid");
			$sip = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$upd_si = array();
			if($sip){
				$upd_sip = array();
				$upd_sip['price'] = $price;
				$con->sql_query("update sku_items_price set ".mysql_update_by_field($upd_sip)." where branch_id=$bid and sku_item_id=$sid");
			}else{
				$upd_si['selling_price'] = $price;
			}
			
			$upd_si['lastupdate'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("update sku_items set ".mysql_update_by_field($upd_si)." where id=$sid");
			
			print $mcode." => ".$price."\n";
		}
		$update_count++;
	}
	fclose($f);
	
	print "$update_count item updated. $failed_count failed.\n";
}

function stock_check_reset_cost($bcode, $date){
	global $con;
	
	if(!$bcode)	die("No Branch Code.\n");
	$bid = get_branch_id($bcode);
	if(!$bid)	die("Invalid Branch Code $bcode\n");
	
	if(!$date)	die("No Date.\n");
	$date = date("Y-m-d", strtotime($date));
	$date_before = date("Y-m-d", strtotime("-1 day", strtotime($date)));
	
	print "Branch: $bcode, Date: ".$date."\n";
	
	$curr_count = 0;
	
	$q1 = $con->sql_query("select id,sku_item_code from sku_items order by id limit 10");
	$total_count = $con->sql_numrows($q1);
	while($si = $con->sql_fetchassoc($q1)){
		$curr_count++;
		print "\r$curr_count / $total_count . . .";
		
		$sc1 = array();
		$sc1['date'] = $date_before;
		$sc1['branch_id'] = $bid;
		$sc1['sku_item_code'] = $si['sku_item_code'];
		$sc1['scanned_by'] = 'admin';
		$sc1['location'] = $sc1['shelf_no'] = 1;
		$sc1['item_no'] = $curr_count;
		$sc1['qty'] = 1;
		$sc1['cost'] = 0.01;
		
		$sc2 = array();
		$sc2['date'] = $date;
		$sc2['branch_id'] = $bid;
		$sc2['sku_item_code'] = $si['sku_item_code'];
		$sc2['scanned_by'] = 'admin';
		$sc2['location'] = $sc2['shelf_no'] = 1;
		$sc2['item_no'] = $curr_count;
		$sc2['qty'] = 0;
		$sc2['cost'] = 0;
		
		$con->sql_query("replace into stock_check ".mysql_insert_by_field($sc1));
		$con->sql_query("replace into stock_check ".mysql_insert_by_field($sc2));
		$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id=".mi($si['id']));
		
	}
	$con->sql_freeresult($q1);
	
	print "\nDone.\n";
}

// php temp_script.php fix_member_last_purchase_branch
function fix_member_last_purchase_branch(){
	global $con;
	$ttl_count = $curr_count = $update_count = 0;
	
	$q1 = $con->sql_query("select nric,card_no,lp_branch_id from membership where lp_branch_id>0 and card_no is not null and card_no<>'' order by nric");
	$ttl_count = $con->sql_numrows($q1);
	while($m = $con->sql_fetchassoc($q1)){
		$curr_count++;
		print "\r$curr_count / $ttl_count. . .";
		
		$card_list = array(trim($m['card_no']));
		$q2 = $con->sql_query("select distinct(mh.card_no) as card_no 
			from membership_history mh
			where mh.nric=".ms($m['nric']));
		while($mh = $con->sql_fetchassoc($q2)){
			$card_list[] = trim($mh['card_no']);
		}
		$con->sql_freeresult($q2);
		
		$q3 = $con->sql_query($sql = "select p.branch_id
		from pos p
		where p.member_no in (".join(',', array_map("ms", $card_list)).") and p.cancel_status=0
		order by p.date desc, p.end_time desc limit 1");
		$tmp = $con->sql_fetchassoc($q3);
		$con->sql_freeresult($q3);
		
		$upd = array();
		$upd['lp_branch_id'] = mi($tmp['branch_id']);
		
		$con->sql_query("update membership set ".mysql_update_by_field($upd)." where nric=".ms($m['nric']));
		if($con->sql_affectedrows()>0){
			$update_count++;
			print $m['nric']. " last purchase change from '$m[lp_branch_id]' to '$upd[lp_branch_id]'.\n";
		}
	}
	$con->sql_freeresult($q1);
	
	print "Total Process: $ttl_count, Updated: $update_count\n";
}

// php temp_script.php fix_intipos_receipt_description
function fix_intipos_receipt_description(){
	global $con;
	
	$curr_count = $update_count = 0;
	$f = fopen('migration/intipos/wrong_receipt_description.csv',"rt");
	while($r = fgetcsv($f)){
		$curr_count++;
		print "\r$curr_count . . .";
		
		$code = trim($r[0]);
		$receipt_description = trim($r[1]);
		
		if(!$code || !$receipt_description)	continue;
		
		$upd = array();
		$upd['receipt_description'] = $receipt_description;
		
		$con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where artno=".ms($code));
		if($con->sql_affectedrows()){
			print "\n$code updated to $receipt_description\n";
			$update_count++;
		}
		//if($update_count >=1)	break;
	}
	fclose($f);
	print "Total Process: $curr_count, Updated: $update_count\n";
}

function fix_po_sp(){
	global $con;
	
	$bid = $_REQUEST['branch_id'];
	$po_id = $_REQUEST['po_id']; 
	
	if(!$bid) die("Branch ID is empty.");
	if(!$po_id) die("PO ID is empty.");
			
	$q1 = $con->sql_query("select pi.*, po.po_no, po.added
						  from po_items pi
						  left join po on po.branch_id=pi.branch_id and po.id=pi.po_id
						  where po.branch_id=".mi($bid)." and po.id=".mi($po_id)." and po.login_ticket_ac <> '' and po.added >= '2015-04-01' and pi.gst_selling_price = 0");
	
	if($con->sql_numrows($q1) == 0){
		print "Item not found.";
		exit;
	}
	
	$price_list = array();
	$po_count = $grn_count = $po_no = 0;
	while($r = $con->sql_fetchassoc($q1)){
		if(!isset($branch_is_under_gst)){
			$prms = array();
			$prms['branch_id'] = $bid;
			$prms['date'] = $r['added'];
			$branch_is_under_gst = check_gst_status($prms);
		}
		
		if($branch_is_under_gst){
			$inclusive_tax = get_sku_gst("inclusive_tax", $r['sku_item_id']);
			
			$prms = array();
			$prms['selling_price'] = $r['selling_price'];
			$prms['inclusive_tax'] = $inclusive_tax;
			$prms['gst_rate'] = $r['selling_gst_rate'];
			$gst_sp_info = calculate_gst_sp($prms);
			$r['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
			
			if($inclusive_tax == "yes") {
				$r['gst_selling_price'] = $r['selling_price'];
				$r['selling_price'] = $gst_sp_info['gst_selling_price'];
			}
			
			$price_list[$r['sku_item_id']]['selling_price'] = $r['selling_price'];
			$price_list[$r['sku_item_id']]['gst_selling_price'] = $r['gst_selling_price'];
			$price_list[$r['sku_item_id']]['selling_gst_id'] = $r['selling_gst_id'];
			$price_list[$r['sku_item_id']]['selling_gst_code'] = $r['selling_gst_code'];
			$price_list[$r['sku_item_id']]['selling_gst_rate'] = $r['selling_gst_rate'];
			
			$con->sql_query("update po_items
							set ".mysql_update_by_field($r, array('selling_price', 'gst_selling_price'))."
							where id=".$r['id']." and branch_id=".$r['branch_id']);
			if($con->sql_affectedrows())	$po_count++;
			
			$po_no = $r['po_no'];
		}
	}
	$con->sql_freeresult($q1);
	print "$po_count PO items updated.<br>";
	
	if(!$po_no) exit;
	
	$q2 = $con->sql_query("select gi.id, gi.branch_id, gi.sku_item_id, gi.selling_price
						  from grr_items gri
						  join grr on grr.branch_id=gri.branch_id and grr.id=gri.grr_id
						  join grn on grn.branch_id=grr.branch_id and grn.grr_id=grr.id
						  join grn_items gi on gi.branch_id=grn.branch_id and gi.grn_id=grn.id
						  where gri.type='PO' and gri.doc_no=".ms($po_no)." and grn.active=1 and grr.active=1 and grn.is_under_gst=1 and gi.gst_selling_price=0");
	
	while($r = $con->sql_fetchassoc($q2)){
		$price = $price_list[$r['sku_item_id']];
		if(!$price){
			$inclusive_tax = get_sku_gst("inclusive_tax", $r['sku_item_id']);
			$output_tax = get_sku_gst("output_tax", $r['sku_item_id']);
			
			$price['selling_gst_id'] = $output_tax['id'];
			$price['selling_gst_code'] = $output_tax['code'];
			$price['selling_gst_rate'] = $output_tax['rate'];
									
			$prms = array();
			$prms['selling_price'] = $r['selling_price'];
			$prms['inclusive_tax'] = $inclusive_tax;
			$prms['gst_rate'] = $price['selling_gst_rate'];
			$gst_sp_info = calculate_gst_sp($prms);
			$r['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
			
			if($inclusive_tax == "yes") {
				$r['gst_selling_price'] = $r['selling_price'];
				$r['selling_price'] = $gst_sp_info['gst_selling_price'];
			}
			
			$price['selling_price'] = $r['selling_price'];
			$price['gst_selling_price'] = $r['gst_selling_price'];
		}
		
		$con->sql_query("update grn_items
						set ".mysql_update_by_field($price)."
						where id=".$r['id']." and branch_id=".$r['branch_id']);
		if($con->sql_affectedrows())	$grn_count++;
	}
	$con->sql_freeresult($q2);
	print "$grn_count GRN items updated.";	
}

function fix_all_po_sp(){
	global $con;
	
	$branch_list = array();
	$con->sql_query("select * from branch where active=1 order by sequence,code");
	while($r = $con->sql_fetchassoc()){
		if($r['gst_register_no'] != '' && $r['gst_start_date'] > 0){
			$branch_list[$r['id']]['code'] = $r['code'];
			$branch_list[$r['id']]['gst_start_date'] = $r['gst_start_date'];
		}
	}
	$con->sql_freeresult();
	
	foreach($branch_list as $bid => $b_info){
		if($b_info['gst_start_date'] > '2015-04-01') $gst_date = $b_info['gst_start_date'];
		else $gst_date = '2015-04-01';
		
		$q1 = $con->sql_query("select pi.*, po.po_no, po.added
							  from po
							  left join po_items pi on pi.branch_id=po.branch_id and pi.po_id=po.id
							  where po.branch_id=".mi($bid)." and po.login_ticket_ac <> '' and po.added >= $gst_date and pi.gst_selling_price = 0");
		
		if($con->sql_numrows($q1) == 0)	continue;
		
		$po_count = $grn_count = 0;
		$price_list = $po_no_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$inclusive_tax = get_sku_gst("inclusive_tax", $r['sku_item_id']);
			
			$prms = array();
			$prms['selling_price'] = $r['selling_price'];
			$prms['inclusive_tax'] = $inclusive_tax;
			$prms['gst_rate'] = $r['selling_gst_rate'];
			$gst_sp_info = calculate_gst_sp($prms);
			$r['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
			
			if($inclusive_tax == "yes") {
				$r['gst_selling_price'] = $r['selling_price'];
				$r['selling_price'] = $gst_sp_info['gst_selling_price'];
			}
			
			$price_list[$r['id']]['selling_price'] = $r['selling_price'];
			$price_list[$r['id']]['gst_selling_price'] = $r['gst_selling_price'];
			$price_list[$r['id']]['selling_gst_id'] = $r['selling_gst_id'];
			$price_list[$r['id']]['selling_gst_code'] = $r['selling_gst_code'];
			$price_list[$r['id']]['selling_gst_rate'] = $r['selling_gst_rate'];
			
			$con->sql_query("update po_items
							set ".mysql_update_by_field($r, array('selling_price', 'gst_selling_price'))."
							where id=".$r['id']." and branch_id=".$r['branch_id']);
			if($con->sql_affectedrows())	$po_count++;
			
			if($r['po_no'] && !in_array($r['po_no'], $po_no_list))	$po_no_list[] = $r['po_no'];
		}
		$con->sql_freeresult($q1);
		
		print "Branch ".$b_info['code'].":<br>";
		print "$po_count PO items updated.<br>";
		
		if(!$po_no_list){
			print "No GRN items updated.<br><br>";
			continue;
		}
		
		foreach($po_no_list as $po_no){
			$q2 = $con->sql_query("select gi.id, gi.branch_id, gi.po_item_id, gi.sku_item_id, gi.selling_price
								  from grr_items gri
								  join grr on grr.branch_id=gri.branch_id and grr.id=gri.grr_id
								  join grn on grn.branch_id=grr.branch_id and grn.grr_id=grr.id
								  join grn_items gi on gi.branch_id=grn.branch_id and gi.grn_id=grn.id
								  where gri.type='PO' and gri.doc_no=".ms($po_no)." and grn.active=1 and grr.active=1 and grn.is_under_gst=1 and gi.gst_selling_price=0");
			
			if($con->sql_numrows($q2) == 0)	continue;
			
			while($r = $con->sql_fetchassoc($q2)){
				$price = $price_list[$r['po_item_id']];
				if(!$price){
					$inclusive_tax = get_sku_gst("inclusive_tax", $r['sku_item_id']);
					$output_tax = get_sku_gst("output_tax", $r['sku_item_id']);
					
					$price['selling_gst_id'] = $output_tax['id'];
					$price['selling_gst_code'] = $output_tax['code'];
					$price['selling_gst_rate'] = $output_tax['rate'];
											
					$prms = array();
					$prms['selling_price'] = $r['selling_price'];
					$prms['inclusive_tax'] = $inclusive_tax;
					$prms['gst_rate'] = $price['selling_gst_rate'];
					$gst_sp_info = calculate_gst_sp($prms);
					$r['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					
					if($inclusive_tax == "yes") {
						$r['gst_selling_price'] = $r['selling_price'];
						$r['selling_price'] = $gst_sp_info['gst_selling_price'];
					}
					
					$price['selling_price'] = $r['selling_price'];
					$price['gst_selling_price'] = $r['gst_selling_price'];
				}
				
				$con->sql_query("update grn_items
								set ".mysql_update_by_field($price)."
								where id=".$r['id']." and branch_id=".$r['branch_id']);
				if($con->sql_affectedrows())	$grn_count++;
			}
			$con->sql_freeresult($q2);
		}
		print "$grn_count GRN items updated.<br><br>";	
	}
}

function fix_pos_cn_number(){
	global $con, $arg, $config;
	
	$bid_list = $b_list = array();
	
	if(isset($_REQUEST['is_http']) && $_REQUEST['is_http']){
		$br = "<br>";
		$bid = mi($_REQUEST['branch_id']);
		$date = trim($_REQUEST['date']);
		if($_REQUEST['date_from'])	$date_from = date("Y-m-d", strtotime(trim($_REQUEST['date_from'])));
		if($_REQUEST['date_to'])	$date_to = date("Y-m-d", strtotime(trim($_REQUEST['date_to'])));
		$is_run = mi($_REQUEST['is_run']);
		$force_update = mi($_REQUEST['force_update']);
		
		$branch_filter = '';
		if($bid>0){
			$branch_filter = 'where id='.ms($bid);
		}
		$con->sql_query("select id,code from branch $branch_filter order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$b_list[] = $r;
		}
		$con->sql_freeresult();
	}else{
		$dummy = array_shift($arg);
		$dummy = array_shift($arg);

		while($cmd = array_shift($arg)){
			list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
			if($cmd_head == "-branch"){
				$branch_filter = '';
				if($cmd_value == 'all'){
					if(!$config['single_server_mode']){
						$bcode = BRANCH_CODE;
						$branch_filter = 'where code='.ms($bcode);
					}
				}else{
					$bcode = trim($cmd_value);
					$branch_filter = 'where code='.ms($bcode);
				}

				$con->sql_query("select id,code from branch $branch_filter order by sequence,code");
				while($r = $con->sql_fetchassoc()){
					$b_list[] = $r;
				}
				$con->sql_freeresult();

				if(!$b_list && $bcode)	die("Branch $bcode not found.\n");
			}elseif($cmd_head == "-date"){
				if($cmd_value=="yesterday"){
					$date = date("Y-m-d", strtotime("-1 day"));
				}else{
					$date = date("Y-m-d", strtotime($cmd_value));
				}				
			}elseif($cmd_head == "-date_from"){
				$date_from = date("Y-m-d", strtotime($cmd_value));
			}elseif($cmd_head == "-date_to"){
				$date_to = date("Y-m-d", strtotime($cmd_value));
			}elseif($cmd_head == "-is_run"){
				$is_run=1;
			}elseif($cmd_head == "-force_update"){
				$force_update=1;
			}else{
				print "Unknown command $cmd\n";
				exit;
			}
		}
	}
	
	if(!$b_list)	die("No branch is selected.\n");
	if(!$date && (!$date_from || !$date_to))	die("Date Error. (Require date or date_from/date_to)\n");
	if($date_from && $date_to){
		if(strtotime($date_to) < strtotime($date_from))	die("Date from/to error.\n");
	}
	
	$bcode_str = '';
	foreach($b_list as $b){
		if($bcode_str)	$bcode_str .= ', ';
		$bcode_str .= $b['code'];
		$bid_list[] = $b['id'];
	}
	print "Branch: $bcode_str\n".$br;
	
	if($date){
		print "Date: $date\n".$br;
	}else{
		print "Date from $date_from to $date_to\n".$br;
	}
	
	/*$duplicate_counter_ids = array();
	$con->sql_query("select id, count(*) as c from counter_settings group by id having c>1 order by id");
	while($r = $con->sql_fetchassoc()){
		$duplicate_counter_ids[] = $r['id'];
	}
	$con->sql_freeresult();
	if(!$duplicate_counter_ids)	die("No Counter have problem.\n".$br);
	print "Counter IDs: ".join(', ', $duplicate_counter_ids)."\n".$br;*/
	
	/*
		///////// Select ////////////
		select branch_id, counter_id, date, credit_note_ref_no,
		lpad(credit_note_ref_no,'14','0') as newcnNo,
		lpad(branch_id,'3','0') as new_branch_id,
		concat (lpad(branch_id,'3','0'), lpad(credit_note_ref_no,'14','0')) as new_credit_notes
		from pos_credit_note 
		where date between "2015-01-01" and "2015-12-31" and  length(credit_note_ref_no) = 13 or length(credit_note_ref_no) = 14
	*/
	
	/*
		//////// Update ///////////
		update pos_credit_note set credit_note_ref_no=concat (lpad(branch_id,'3','0'), lpad(credit_note_ref_no,'14','0'))
		where branch_id=xxx and date between xxx and xxx and length(credit_note_ref_no) = 13 or length(credit_note_ref_no) = 14";
	*/
	$filter = array();
	$filter[] = "branch_id in (".join(',', $bid_list).")";
	//$filter[] = "counter_id in (".join(',', $duplicate_counter_ids).")";
	//$filter[] = "(length(credit_note_ref_no) = 13 or length(credit_note_ref_no) = 14)";
	if($date_from && $date_to)	$filter[] = "date between ".ms($date_from)." and ".ms($date_to);
	else	$filter[] = "date = ".ms($date);
	
	$filter = join(' and ', $filter);
	$having = '';
	if(!$force_update)	$having = "having cn_no_count>1";
	print "\n".$br;
	$cn_count = 0;
	$sql = "select pcn.*, (select count(*) from pos_credit_note pcn2 where pcn2.credit_note_ref_no=pcn.credit_note_ref_no) as cn_no_count
		from pos_credit_note pcn
		where $filter
		$having
		order by pcn.branch_id,pcn.date, pcn.counter_id,pcn.credit_note_ref_no";
		
	//print "$sql\n".$br;
	$q1 = $con->sql_query($sql);
	while($r = $con->sql_fetchassoc($q1)){
		$new_credit_note_ref_no = generate_new_cn_ref_no($r);

		if($r['credit_note_ref_no'] != $new_credit_note_ref_no){
			print "Branch ID#$r[branch_id], Date#$r[date], Counter ID#$r[counter_id] : ".$r['credit_note_ref_no'] . " => ".$new_credit_note_ref_no."\n".$br;
			$cn_count++;
			
			if($is_run){
				$con->sql_query("update pos_credit_note set credit_note_ref_no=".ms($new_credit_note_ref_no)." where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and date=".ms($r['date'])." and credit_note_ref_no=".ms($r['credit_note_ref_no']));
			}
		}
	}
	$con->sql_freeresult($q1);
	
	print "Total $cn_count ".($is_run ? 'fixed' : 'found')."\n".$br;
}

function generate_new_cn_ref_no($r){
	$date_time_receipt_ref = '2000-01-01 00:00:00';
	$diffDateTime = (strtotime($r['date']) - strtotime($date_time_receipt_ref))/(60*60*24);
	return sprintf("%03d%04d%04d%06d",$r['branch_id'],$r['counter_id'],$diffDateTime,$r['credit_note_no']);
}

function revert_gst_code($is_run=0){
	global $con;
	
	// search from browser mode
	if($_REQUEST['is_http'] && isset($_REQUEST['is_run'])) $is_run = $_REQUEST['is_run'];
	
	print "Start revert GST Code and GST ID (AJP to TX or AJS to SR)\n\n";
	
	$table_arr = array("cnote_items", "dnote_items", "do_items", "do_open_items", "gra_items", "grn_items", "grr_items", "membership_history", "membership_receipt_items", "membership_redemption_items", "po_items", "sales_order_items", "sku_items_future_price_items", "tmp_ci_items", "tmp_cnote_items", "tmp_do_items", "tmp_grn_items", "tmp_po_items", "tmp_sales_order_items", "pos_deposit", "pos_items", "pos_items_changes", "pos", "sku", "sku_items", "category");
	
	$sql = "select id, code, description from gst where code = 'AJP' or code = 'AJS' or code = 'TX' or code = 'SR'";
	$q1 = $con->sql_query($sql);
	while($r = $con->sql_fetchassoc($q1)){
		if($r['code'] == 'AJP'){
			$id_AJP = $r['id'];
			$desc_AJP = $r['description'];
			$code_AJP = $r['code'];
		}elseif($r['code'] == 'AJS'){
			$id_AJS = $r['id'];
			$desc_AJS = $r['description'];
			$code_AJS = $r['code'];
		}elseif($r['code'] == 'TX'){
			$id_TX = $r['id'];
			$desc_TX = $r['description'];
			$code_TX = $r['code'];
		}elseif($r['code'] == 'SR'){
			$id_SR = $r['id'];
			$desc_SR = $r['description'];
			$code_SR = $r['code'];
		}
	}
	$con->sql_freeresult($q1);
	
	foreach($table_arr as $table){
		if($table == 'sku_items' || $table == 'sku' || $table == 'category'){
			$num_row = 0;
			if ($table == 'sku'){
				$input = "mst_input_tax";
				$output = "mst_output_tax";
			}else{
				$input = "input_tax";
				$output = "output_tax";
			}
			$sql1 = "select * from $table where $input = $id_AJP or $output = $id_AJS";
			
			$q2 = $con->sql_query($sql1);
			while($r = $con->sql_fetchassoc($q2)){
				$upd = array();
				if($is_run){
					if ($r[$input] == $id_AJP)
						$upd[$input] = $id_TX;
					
					if ($r[$output] == $id_AJS)
						$upd[$output] = $id_SR;
					$con->sql_query("update $table set ".mysql_update_by_field($upd)." where id = ".mi($r['id']));
					$num_row += mi($con->sql_affectedrows());
				}else{
					$num_row++;
				}
			}
			$con->sql_freeresult($q2);
			if ($is_run){
				print $table . ": " . $num_row . " rows updated.\n";
			}else{
				print $table . ": " . $num_row . " rows need to revert.\n";
			}
		}
		elseif ($table == 'po_items' || $table == 'tmp_po_items'){
			$num_row = 0;
			$sql1 = "select * from $table where (cost_gst_id = $id_AJP or cost_gst_code = ". ms($code_AJP). ") or (cost_gst_id = $id_AJS or cost_gst_code = " . ms($code_AJS). ") 
			or  (selling_gst_id = $id_AJP or selling_gst_code = " . ms($code_AJP) . ") or (selling_gst_id = $id_AJS or selling_gst_code = " . ms($code_AJS) . ")";

			$q2 = $con->sql_query($sql1);
			while($r = $con->sql_fetchassoc($q2)){
				if($is_run){
					$sql2 = "update $table 
					set cost_gst_id = case when cost_gst_id = $id_AJP then $id_TX
					else cost_gst_id
					end,
					cost_gst_code = case when cost_gst_code = " . ms($code_AJP) . " then " . ms($code_TX) . "
					else cost_gst_code
					end,
					selling_gst_id = case when selling_gst_id = $id_AJS then $id_SR
					else selling_gst_id
					end,
					selling_gst_code = case when selling_gst_code = " . ms($code_AJS) . " then " . ms($code_SR) . "
					else selling_gst_code
					end
					where branch_id = " . mi($r['branch_id']) . " and id = " . mi($r['id']) . " and po_id = " . mi($r['po_id']);
					
					$con->sql_query($sql2);
					$num_row += mi($con->sql_affectedrows());
				}else{
					$num_row++;
				}
			}
			$con->sql_freeresult($q2);
			if ($is_run){
				print $table . ": " . $num_row . " rows updated.\n";
			}else{
				print $table . ": " . $num_row . " rows need to revert.\n";
			}
		}elseif ($table == 'pos_deposit'){
			$num_row = 0;
			
			$sql1 = "select * from $table
			where item_list like '%$code_AJP%' or item_list like '%$code_AJS%' or gst_info like '%$code_AJP%' or gst_info like '%$code_AJS%'";
				
			$q2 = $con->sql_query($sql1);
			while($r = $con->sql_fetchassoc($q2)){
				$upd = array();
				$item_list = unserialize($r['item_list']);
				$gst_info = unserialize($r['gst_info']);
				
				if ($item_list){
					foreach($item_list as $key=>$item){
						if ($item['item']['tax_detail']['id'] == $id_AJP){
							$item_list[$key]['item']['tax_detail']['id'] = $id_TX;
						}elseif ($item['item']['tax_detail']['id'] == $id_AJS){
							$item_list[$key]['item']['tax_detail']['id'] = $id_SR;
						}
						
						if ($item['item']['tax_detail']['code'] == $code_AJP){
							$item_list[$key]['item']['tax_detail']['code'] = $code_TX;
						}elseif ($item['item']['tax_detail']['code'] == $code_AJS){
							$item_list[$key]['item']['tax_detail']['code'] = $code_SR;
						}
						
						if ($item['item']['tax_detail']['description'] == $desc_AJP){
							$item_list[$key]['item']['tax_detail']['description'] = $desc_TX;
						}elseif ($item['item']['tax_detail']['description'] == $desc_AJS){
							$item_list[$key]['item']['tax_detail']['description'] = $desc_SR;
						}
						
						if ($item['tax_code'] == $code_AJP){
							$item_list[$key]['tax_code'] = $code_TX;
						}elseif ($item['tax_code'] == $code_AJS){
							$item_list[$key]['tax_code'] = $code_SR;
						}
					}
					$upd['item_list'] = serialize($item_list);
				}
				
				if($gst_info){
					if ($gst_info['id'] == $id_AJP){
						$gst_info['id'] = $id_TX;
					}elseif ($gst_info['id'] == $id_AJS){
						$gst_info['id'] = $id_SR;
					}
					
					if ($gst_info['code'] == $code_AJP){
						$gst_info['code'] = $code_TX;
					}elseif ($gst_info['code'] == $code_AJS){
						$gst_info['code'] = $code_SR;
					}
					
					if ($gst_info['description'] == $desc_AJP){
						$gst_info['description'] = $desc_TX;
					}elseif ($gst_info['description'] == $desc_AJS){
						$gst_info['description'] = $desc_SR;
					}
					$upd['gst_info'] = serialize($gst_info);
				}
				if (!$upd)	continue;
				
				if($is_run){
					$con->sql_query("update $table set ".mysql_update_by_field($upd)." where pos_id = ".mi($r['pos_id']) . " and branch_id = " . mi($r['branch_id']) . " and counter_id = " . mi($r['counter_id']) . " and date = " . ms($r['date']));
					$num_row += mi($con->sql_affectedrows());
				}else{
					$num_row++;
				}
			}
			$con->sql_freeresult($q2);
			if ($is_run){
				print $table . ": " . $num_row . " rows updated.\n";
			}else{
				print $table . ": " . $num_row . " rows need to revert.\n";
			}
		}elseif ($table == 'pos_items'){
			$num_row = 0;
			$sql1 = "select * from $table where more_info like '%$code_AJP%' or more_info like '%$code_AJS%' or tax_code like '%$code_AJP%' or tax_code like '%$code_AJS%'";

			$q2 = $con->sql_query($sql1);
			while($r = $con->sql_fetchassoc($q2)){
				$upd = array();
				$more_info = unserialize($r['more_info']);
				
				if ($r['tax_code'] == $code_AJP){
					$upd['tax_code'] = $code_TX;
				}elseif ($r['tax_code'] == $code_AJS){
					$upd['tax_code'] = $code_SR;
				}
				
				if($more_info){
					if ($more_info['tax_detail']['id'] == $id_AJP){
						$more_info['tax_detail']['id'] = $id_TX;
					}elseif ($more_info['tax_detail']['id'] == $id_AJS){
						$more_info['tax_detail']['id'] = $id_SR;
					}
					
					if ($more_info['tax_detail']['code'] == $code_AJP){
						$more_info['tax_detail']['code'] = $code_TX;
					}elseif ($more_info['tax_detail']['code'] == $code_AJS){
						$more_info['tax_detail']['code'] = $code_SR;
					}
					$upd['more_info'] = serialize($more_info);
				}
				
				if (!$upd)	continue;
				
				if ($is_run){
					$con->sql_query("update $table set ".mysql_update_by_field($upd)." where pos_id = ".mi($r['pos_id']) . " and branch_id = " . mi($r['branch_id']) . " and counter_id = " . mi($r['counter_id']) . " and date = " . ms($r['date']) . " and item_id = " . mi($r['item_id']) . " and id = " . mi($r['id']));
					$num_row += mi($con->sql_affectedrows());
				}else{
					$num_row++;
				}
			}
			$con->sql_freeresult($q2);
			if ($is_run){
				print $table . ": " . $num_row . " rows updated.\n";
			}else{
				print $table . ": " . $num_row . " rows need to revert.\n";
			}
		}elseif ($table == 'pos_items_changes'){
			$num_row = 0;
			$sql1 = "select * from $table where info like '%$code_AJP%' or info like '%$code_AJS%'";
			$q2 = $con->sql_query($sql1);
			while($r = $con->sql_fetchassoc($q2)){
				$info = unserialize($r['info']);
				
				if ($info){
					$is_change = false;
					foreach($info['org_tax_detail'] as $item){
						if ($info['org_tax_detail']['id'] == $id_AJP){
							$info['org_tax_detail']['id'] = $id_TX;
							$is_change = true;
						}elseif ($info['org_tax_detail']['id'] == $id_AJS){
							$info['org_tax_detail']['id'] = $id_SR;
							$is_change = true;
						}
						
						if ($info['org_tax_detail']['code'] == $code_AJP){
							$info['org_tax_detail']['code'] = $code_TX;
							$is_change = true;
						}elseif ($info['org_tax_detail']['code'] == $code_AJS){
							$info['org_tax_detail']['code'] = $code_SR;
							$is_change = true;
						}
						
						if ($info['org_tax_detail']['description'] == $desc_AJP){
							$info['org_tax_detail']['description'] = $desc_TX;
							$is_change = true;
						}elseif ($info['org_tax_detail']['description'] == $desc_AJS){
							$info['org_tax_detail']['description'] = $desc_SR;
							$is_change = true;
						}
					}
					
					foreach($info['new_tax_detail'] as $key=>$item1){
						if ($info['new_tax_detail']['id'] == $id_AJP){
							$info['new_tax_detail']['id'] = $id_TX;
							$is_change = true;
						}elseif ($info['new_tax_detail']['id'] == $id_AJS){
							$info['new_tax_detail']['id'] = $id_SR;
							$is_change = true;
						}
						
						if ($info['new_tax_detail']['code'] == $code_AJP){
							$info['new_tax_detail']['code'] = $code_TX;
							$is_change = true;
						}elseif ($info['new_tax_detail']['code'] == $code_AJS){
							$info['new_tax_detail']['code'] = $code_SR;
							$is_change = true;
						}
						
						if ($info['new_tax_detail']['description'] == $desc_AJP){
							$info['new_tax_detail']['description'] = $desc_TX;
							$is_change = true;
						}elseif ($info['new_tax_detail']['description'] == $desc_AJS){
							$info['new_tax_detail']['description'] = $desc_SR;
							$is_change = true;
						}
					}
					
					if($is_change){
						if ($is_run){
							$upd['info'] = serialize($info);
							$con->sql_query("update $table set ".mysql_update_by_field($upd)." where pos_id = ".mi($r['pos_id']) . " and branch_id = " . mi($r['branch_id']) . " and counter_id = " . mi($r['counter_id']) . " and date = " . ms($r['date']) . " and item_id = " . mi($r['item_id']) . " and type = " . ms($r['type']));
							$num_row += mi($con->sql_affectedrows());
						}
						else{
							$num_row++;
						}
					}
				}
			}
			$con->sql_freeresult($q2);
			if ($is_run){
				print $table . ": " . $num_row . " rows updated.\n";
			}else{
				print $table . ": " . $num_row . " rows need to revert.\n";
			}
		}elseif ($table == 'pos'){
			$num_row = 0;
			$sql1 = "select * from $table where pos_more_info like '%$code_AJP%' or pos_more_info like '%$code_AJS%'";
			$q2 = $con->sql_query($sql1);
			while($r = $con->sql_fetchassoc($q2)){
				$is_change = false;
				$pos_more_info = unserialize($r['pos_more_info']);
				
				if ($pos_more_info['deposit']){
					foreach($pos_more_info['deposit'] as $key=>$item){
						if($item['gst_info']){
							$gst_info = unserialize($item['gst_info']);
							
							if ($gst_info['id'] == $id_AJP){
								$gst_info['id'] = $id_TX;
								$is_change = true;
							}elseif ($gst_info['id'] == $id_AJS){
								$gst_info['id'] = $id_SR;
								$is_change = true;
							}
							
							if ($gst_info['code'] == $code_AJP){
								$gst_info['code'] = $code_TX;
								$is_change = true;
							}elseif ($gst_info['code'] == $code_AJS){
								$gst_info['code'] = $code_SR;
								$is_change = true;
							}
							
							if ($gst_info['description'] == $desc_AJP){
								$gst_info['description'] = $desc_TX;
								$is_change = true;
							}elseif ($gst_info['description'] == $desc_AJS){
								$gst_info['description'] = $desc_SR;
								$is_change = true;
							}
							$pos_more_info['deposit'][$key]['gst_info'] = serialize($gst_info);
						}
					}
				}
				
				if ($pos_more_info['service_charges']){
					foreach($pos_more_info['service_charges'] as $key=>$item){
						if ($key == 'sc_gst_detail'){
							if ($item['id'] == $id_AJP){
								$pos_more_info['service_charges'][$key]['id'] = $id_TX;
								$is_change = true;
							}elseif ($item['id'] == $id_AJS){
								$pos_more_info['service_charges'][$key]['id'] = $id_SR;
								$is_change = true;
							}
							
							if ($item['code'] == $code_AJP){
								$pos_more_info['service_charges'][$key]['code'] = $code_TX;
								$is_change = true;
							}elseif ($item['code'] == $code_AJS){
								$pos_more_info['service_charges'][$key]['code'] = $code_SR;
								$is_change = true;
							}
						}						
					}
				}
				
				if($is_change){
					if($is_run){
						$upd['pos_more_info'] = serialize($pos_more_info);
						$con->sql_query("update $table set ".mysql_update_by_field($upd)." where branch_id = " . mi($r['branch_id']) . " and counter_id = " . mi($r['counter_id']) . " and date = " . ms($r['date']) . " and id = " . mi($r['id']));
						$num_row += mi($con->sql_affectedrows());
					}else{
						$num_row++;
					}
				}
			}
			$con->sql_freeresult($q2);
			if ($is_run){
				print $table . ": " . $num_row . " rows updated.\n";
			}else{
				print $table . ": " . $num_row . " rows need to revert.\n";
			}
		}
		elseif($table == 'grn_items' || $table == 'tmp_grn_items'){
			$num_row = 0;
			$sql1 = "select * from $table where  (gst_id = $id_AJP or gst_code = " . ms($code_AJP). ") or (gst_id = $id_AJS or gst_code = " . ms($code_AJS) . ") 
			or (acc_gst_id = $id_AJP or acc_gst_code = " . ms($code_AJP) . ") or (acc_gst_id = $id_AJS or acc_gst_code = " . ms($code_AJS). ") 
			or (selling_gst_id = $id_AJP or selling_gst_code = " . ms($code_AJP) . ") or (selling_gst_id = $id_AJS or selling_gst_code = " . ms($code_AJS) . ")";
			
			$q2 = $con->sql_query($sql1);
			while($r = $con->sql_fetchassoc($q2)){
				if($is_run){
					$sql2 = "update $table 
					set gst_id = case when gst_id = $id_AJP then $id_TX
					when gst_id = $id_AJS then $id_SR
					else gst_id
					end,
					gst_code = case when gst_code = " . ms($code_AJP) . " then " . ms($code_TX) . "
					when gst_code = " . ms($code_AJS) . " then " . ms($code_SR) . "
					else gst_code
					end,
					acc_gst_id = case when acc_gst_id = $id_AJP then $id_TX
					else acc_gst_id
					end,
					acc_gst_code = case when acc_gst_code = " . ms($code_AJP) . " then " . ms($code_TX) . "
					else acc_gst_code
					end,
					selling_gst_id = case when selling_gst_id = $id_AJS then $id_SR
					else selling_gst_id
					end,
					selling_gst_code = case when selling_gst_code = " . ms($code_AJS) . " then " . ms($code_SR) . "
					else selling_gst_code
					end
					where branch_id = " . mi($r['branch_id']) . " and id = " . mi($r['id']);
					$con->sql_query($sql2);
					$num_row += mi($con->sql_affectedrows());
				}else{
					$num_row++;
				}
			}
			$con->sql_freeresult($q2);
			if ($is_run){
				print $table . ": " . $num_row . " rows updated.\n";
			}else{
				print $table . ": " . $num_row . " rows need to revert.\n";
			}
		}
		else{
			$num_row = 0;
			if($is_run){
				$sql2 = "update $table 
				set gst_id = case when gst_id = $id_AJP then $id_TX
				when gst_id = $id_AJS then $id_SR
				else gst_id
				end,
				gst_code = case when gst_code = " . ms($code_AJP) . " then " . ms($code_TX) . "
				when gst_code = " . ms($code_AJS) . " then " . ms($code_SR) . "
				else gst_code
				end
				where (gst_id = $id_AJP and gst_code = " . ms($code_AJP) . ") or (gst_id = $id_AJS and gst_code = " . ms($code_AJS) . ")";
				$con->sql_query($sql2);
				$num_row += mi($con->sql_affectedrows());
				print $table . ": " . $num_row . " rows updated.\n";
			}else{
				$sql1 = "select * from $table where  (gst_id = $id_AJP or gst_code = " . ms($code_AJP) .") or (gst_id = $id_AJS or gst_code = " . ms($code_AJS) . ")";

				$q2 = $con->sql_query($sql1);
				while($r = $con->sql_fetchassoc($q2)){
					$num_row++;
				}
				$con->sql_freeresult($q2);
				print $table . ": " . $num_row . " rows need to revert.\n";
			}
		}
	}
	print "\nEnd revert GST Code and GST ID (AJP to TX or AJS to SR)\n\n";
}

function fix_duplicate_vendor(){
	global $con;
	
	$fixed_counter = 0;
	// get all affected vendor
	$q1 = $con->sql_query("select min(id) as min_id, description, count(*) as c from vendor group by description having c>1 order by description");
	while($dup_vendor = $con->sql_fetchassoc($q1)){
		$real_vendor_id = $dup_vendor['min_id'];
		
		// find all other with same vendor id
		$other_vid_list = array();
		$q2 = $con->sql_query("select id from vendor where description=".ms($dup_vendor['description'])." and id<>$real_vendor_id");
		while($r = $con->sql_fetchassoc($q2)){
			$other_vid_list[] = $r['id'];
		}
		$con->sql_freeresult($q2);
		
		$str_vid = join(',', $other_vid_list);
		// sku
		$con->sql_query("update sku set vendor_id=$real_vendor_id where vendor_id in ($str_vid)");
		// grr/grn
		$con->sql_query("update grr set vendor_id=$real_vendor_id where vendor_id in ($str_vid)");
		$con->sql_query("update grn set vendor_id=$real_vendor_id where vendor_id in ($str_vid)");
		// po
		$con->sql_query("update po set vendor_id=$real_vendor_id where vendor_id in ($str_vid)");
		
		// delete vendor
		$con->sql_query("delete from vendor where id in ($str_vid)");
		
		$fixed_counter++;
	}
	$con->sql_freeresult($q1);
	
	
	print "$fixed_counter vendor fixed.";
}

function restore_aneka_dungun_stock_check($is_run = 0){
	global $con;
	
	//$db_default_connection = array("localhost", "root", "", "armshq_hasani");
	$con_bck = connect_db("localhost:/tmp/mysql.sock", "arms", "793505", "armshq_backup_20130401");
	
	$bid = 3;
	$curr_count = $si_count = $exists_count = $update_count = 0;
	$sid_list = array();
	
	/*$con_bck->sql_query("select count(*) from stock_check where branch_id=$bid and date>='2013-4-1'");
	$tmp = $con_bck->sql_fetchassoc();
	$con_bck->sql_freeresult();
	print_r($tmp);	
	exit;*/
	
	//$q1 = $con_bck->sql_query("select * from stock_check where branch_id=$bid and date>='2013-4-1' and sku_item_code in ('280000010000','280000020000') order by sku_item_code, date");
	$q1 = $con_bck->sql_query("select * from stock_check where branch_id=$bid and date>='2013-4-1' order by sku_item_code, date");
	$total_count = $con_bck->sql_numrows($q1);
	$last_sku_item_code = "";
	while($r = $con_bck->sql_fetchassoc($q1)){
		$curr_count++;
		
		// check whether the row already exists
		$con->sql_query("select * from stock_check where branch_id=".mi($r['branch_id'])." and date=".ms($r['date'])." and sku_item_code=".ms($r['sku_item_code'])." and location=".ms($r['location'])." and shelf_no=".ms($r['shelf_no'])." and item_no=".ms($r['item_no']). " limit 1");
		$exists_row = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($exists_row){
			$exists_count++;	// the row already exists, nothing to do
		}	
		else{	// the row not exists, need insert
			if($is_run){	// is real run mode
				// replace the row
				$con->sql_query("replace into stock_check ".mysql_insert_by_field($r));
				$update_count++;
				
				if(!isset($sid_list[$r['sku_item_code']])){
					// get sku item id by sku item code
					$con->sql_query("select id from sku_items where sku_item_code=".ms($r['sku_item_code']));
					$si = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					$sid = mi($si['id']);
					
					if($sid){
						$sid_list[$r['sku_item_code']] = $sid;
					}else{
						print "\n".$r['sku_item_code']." cannot found sid.\n";
					}
				}				
			}			
		}	
		
		if($last_sku_item_code != $r['sku_item_code']){
			if($last_sku_item_code){				
				$si_count++;
				
				if($is_run){
					if(count($sid_list)>=1000){	// update sku_items_cost when the array holding more than 1000 sku
						$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (".join(',', $sid_list).")");
						$sid_list = array();
					}				
				}
			}
			
			$last_sku_item_code = $r['sku_item_code'];
			
		}
		
		print "\r$curr_count / $total_count . . .";
	}
	$con_bck->sql_freeresult($q1);
	
	if($last_sku_item_code){
		$si_count++;
	}
	
	if($sid_list && $is_run){
		$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (".join(',', $sid_list).")");
		$sid_list = array();
	}
		
	print "\nItem Count: $si_count";
	print "\nTotal Row: $total_count";
	print "\nRow Existed: $exists_count";
	print "\nRow Inserted: $update_count";	
	print "\nDone.\n";
}

function restore_db_cutoff_sku_history($is_restore = false){
	global $con;
	
	$cutoff_date = '2014-01-01';
	$source_label = 'ARC_MA_SCR';
	$one_day_b4_cutoff = date("Y-m-d", strtotime("-1 day", strtotime($cutoff_date)));
	$own_table_checking_list = array();
	
	$live_db_default_connection = array("cwm-hq.arms.com.my:4001", "arms", "sc440", "armshq");
	//$live_db_default_connection = array("10.1.1.202", "arms", "Arms54321.", "armshq_cwm2");
	$con_live = connect_db($live_db_default_connection[0], $live_db_default_connection[1], $live_db_default_connection[2], $live_db_default_connection[3]);
	
	// get all branch id
	$bid_list = array();
	$con->sql_query("select id from branch order by id");	
	while($r = $con->sql_fetchassoc()){
		$bid_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	// SKU
	print "\nMARK SKU_ITEMS HISTORY DATA…";
	
	$q_si = $con->sql_query("select si.*, sku.default_trade_discount_code,if(sku.is_fresh_market='inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market
	from sku_items si
	left join sku on sku.id=si.sku_id 
	left join category_cache cc on cc.category_id=sku.category_id
	order by si.id");
	$row_count = $con->sql_numrows($q_si);
	print "\r sku_items have $row_count rows.";
	print "\nChecking selling price, cost and stock balance...";
	
	$curr_row = 0;
	print "\n";
	
	while($si = $con->sql_fetchassoc($q_si)){
		$curr_row++;
		print "\r$curr_row / $row_count";
		
		$sid = mi($si['id']);
		
		foreach($bid_list as $bid => $b){
			// selling price
			$sql = "select siph.* 
			from sku_items_price_history siph 
			where siph.branch_id=$bid and siph.sku_item_id=$sid and siph.added<=".ms($cutoff_date)." order by siph.added desc limit 1";
			$q2 = $con->sql_query($sql);
			$siph = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			if($is_restore && $siph){
				$upd = array();
				$upd = $siph;
				$upd['user_id'] = 1;
				$upd['source'] = $source_label;
				$upd['added'] = $cutoff_date;
				$con_live->sql_query("select count(*) from sku_items_price_history where branch_id=$bid and sku_item_id=$sid and source=".ms($source_label)." and user_id=1 and added=".ms($upd['added']));
				$price_updated = $con_live->sql_fetchfield(0);
				$con_live->sql_freeresult();
				
				if(!$price_updated){
					$con_live->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($upd));
				}				
			}
			
			// cost price
			$sql = "select sich.* 
			from sku_items_cost_history sich 
			where sich.branch_id=$bid and sich.sku_item_id=$sid and sich.date<".ms($cutoff_date)." order by sich.date desc limit 1";
			$q3 = $con->sql_query($sql);
			$sich = $con->sql_fetchassoc($q3);
			$con->sql_freeresult($q3);
			
			if($is_restore && $sich){
				$upd = array();
				$upd = $sich;
				$upd['user_id'] = 1;
				$upd['source'] = $source_label;
				$upd['date'] = $cutoff_date;
				$con_live->sql_query("replace into sku_items_cost_history ".mysql_insert_by_field($upd));
			}
			
			// stock balance & stock check
			$q_sc = $con->sql_query("select qty from stock_check where branch_id=$bid and date=".ms($cutoff_date)." and sku_item_code=".ms($si['sku_item_code'])." limit 1");
			$sc_rows = $con->sql_numrows($q_sc);
			$con->sql_freeresult($q_sc);
			
			if(!$sc_rows){
				
				$sb = array();
				$sb_tbl = "stock_balance_b".$bid."_".date("Y", strtotime($one_day_b4_cutoff));
				
				// use cache first
				if(isset($own_table_checking_list[$sb_tbl])){
					$got_tbl = $own_table_checking_list[$sb_tbl];
				}else{
					// check mysql
					$own_table_checking_list[$sb_tbl] = $got_tbl = $con->sql_query("explain $sb_tbl",false,false);
					$con->sql_freeresult($got_tbl);
				}
		
				if($got_tbl){
					$sql = "select * from $sb_tbl where sku_item_id=$sid and ".ms($one_day_b4_cutoff)." between from_date and to_date limit 1";
					$q4 = $con->sql_query($sql);
					$sb = $con->sql_fetchassoc($q4);
					$con->sql_freeresult($q4);
				}
				
				if($is_restore){
					$upd = array();
					$upd['date'] = $cutoff_date;
					$upd['branch_id'] = $bid;
					$upd['sku_item_code'] = $si['sku_item_code'];
					$upd['scanned_by'] = 'ARMS';
					$upd['location'] = $source_label;
					$upd['shelf_no'] = $upd['item_no'] = 1;
					$upd['selling'] = $siph['price'] ? $siph['price'] : $si['selling_price'];
					$upd['qty'] = $sb['qty'];
					$upd['cost'] = $sich['grn_cost'] ? $sich['grn_cost'] : $si['cost_price'];
					$upd['is_fresh_market'] = $si['is_fresh_market'] == 'yes' ? 1 : 0;
					$con_live->sql_query("replace into stock_check ".mysql_insert_by_field($upd));
					
					
					$con_live->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id=$sid");
				}
			}			
		}
	}
	$con->sql_freeresult($q_si);
	print "\nDone.\n";
}

function restore_db_cutoff_sku_history2($is_restore = false){
	global $con;
	
	$cutoff_date = '2014-01-01';
	$source_label = 'ARC_MA_SCR';
	$one_day_b4_cutoff = date("Y-m-d", strtotime("-1 day", strtotime($cutoff_date)));
	$own_table_checking_list = array();
	
	//$live_db_default_connection = array("cwm-hq.arms.com.my:4001", "arms", "sc440", "armshq");
	$live_db_default_connection = array("10.1.1.202", "arms", "Arms54321.", "armshq_cwm2");
	$con_live = connect_db($live_db_default_connection[0], $live_db_default_connection[1], $live_db_default_connection[2], $live_db_default_connection[3]);
	
	// get all branch id
	$bid_list = array();
	$con->sql_query("select id from branch order by id");	
	while($r = $con->sql_fetchassoc()){
		$bid_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	// SKU
	print "\nMARK SKU_ITEMS HISTORY DATA…";
	
	$q_si = $con->sql_query("select si.*, sku.default_trade_discount_code,if(sku.is_fresh_market='inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market
	from sku_items si
	left join sku on sku.id=si.sku_id 
	left join category_cache cc on cc.category_id=sku.category_id
	order by si.id");
	$row_count = $con->sql_numrows($q_si);
	print "\r sku_items have $row_count rows.";
	print "\nChecking selling price, cost and stock balance...";
	
	$curr_row = 0;
	print "\n";
	
	while($si = $con->sql_fetchassoc($q_si)){
		$curr_row++;
		print "\r$curr_row / $row_count";
		
		$sid = mi($si['id']);
		
		foreach($bid_list as $bid => $b){
			// selling price
			$sql = "select siph.* 
			from sku_items_price_history siph 
			where siph.branch_id=$bid and siph.sku_item_id=$sid and siph.added<=".ms($cutoff_date)." order by siph.added desc limit 1";
			$q2 = $con->sql_query($sql);
			$siph = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			if($is_restore && $siph){
				$upd = array();
				$upd = $siph;
				$upd['user_id'] = 1;
				$upd['source'] = $source_label;
				$upd['added'] = $cutoff_date;
				$con_live->sql_query("select count(*) from sku_items_price_history where branch_id=$bid and sku_item_id=$sid and source=".ms($source_label)." and user_id=1 and added=".ms($upd['added']));
				$price_updated = $con_live->sql_fetchfield(0);
				$con_live->sql_freeresult();
				
				if(!$price_updated){
					$con_live->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($upd));
				}				
			}
			
			// cost price
			$sql = "select sich.* 
			from sku_items_cost_history sich 
			where sich.branch_id=$bid and sich.sku_item_id=$sid and sich.date<".ms($cutoff_date)." order by sich.date desc limit 1";
			$q3 = $con->sql_query($sql);
			$sich = $con->sql_fetchassoc($q3);
			$con->sql_freeresult($q3);
			
			if($is_restore && $sich){
				$upd = array();
				$upd = $sich;
				$upd['user_id'] = 1;
				$upd['source'] = $source_label;
				$upd['date'] = $cutoff_date;
				$con_live->sql_query("replace into sku_items_cost_history ".mysql_insert_by_field($upd));
			}
			
			// stock balance & stock check
			$q_sc = $con->sql_query("select qty from stock_check where branch_id=$bid and date=".ms($cutoff_date)." and sku_item_code=".ms($si['sku_item_code'])." limit 1");
			$sc_rows = $con->sql_numrows($q_sc);
			$con->sql_freeresult($q_sc);
			
			if(!$sc_rows){
				
				$sb = array();
				$sb_tbl = "stock_balance_b".$bid."_".date("Y", strtotime($one_day_b4_cutoff));
				
				// use cache first
				if(isset($own_table_checking_list[$sb_tbl])){
					$got_tbl = $own_table_checking_list[$sb_tbl];
				}else{
					// check mysql
					$own_table_checking_list[$sb_tbl] = $got_tbl = $con->sql_query("explain $sb_tbl",false,false);
					$con->sql_freeresult($got_tbl);
				}
		
				if($got_tbl){
					$sql = "select * from $sb_tbl where sku_item_id=$sid and ".ms($one_day_b4_cutoff)." between from_date and to_date limit 1";
					$q4 = $con->sql_query($sql);
					$sb = $con->sql_fetchassoc($q4);
					$con->sql_freeresult($q4);
				}
				
				if($is_restore){
					$upd = array();
					$upd['date'] = $cutoff_date;
					$upd['branch_id'] = $bid;
					$upd['sku_item_code'] = $si['sku_item_code'];
					$upd['scanned_by'] = 'ARMS';
					$upd['location'] = $source_label;
					$upd['shelf_no'] = $upd['item_no'] = 1;
					$upd['selling'] = $siph['price'] ? $siph['price'] : $si['selling_price'];
					$upd['qty'] = $sb['qty'];
					$upd['cost'] = $sich['grn_cost'] ? $sich['grn_cost'] : $si['cost_price'];
					$upd['is_fresh_market'] = $si['is_fresh_market'] == 'yes' ? 1 : 0;
					$con_live->sql_query("replace into stock_check ".mysql_insert_by_field($upd));
					
					
					$con_live->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id=$sid");
				}
			}			
		}
	}
	$con->sql_freeresult($q_si);
	print "\nDone.\n";
}

function update_all_po_amt_old($bid = 0){
	global $con, $appCore, $config;
	
	if($bid > 0){
		$branch_check = "and branch_id=$bid";
	}else{
		if(!$config['single_server_mode']){
			$bid = mi(get_branch_id(BRANCH_CODE));
			$branch_check = "and branch_id=$bid";
		}
	}	
	
	$po_updated = 0;
	$q1 = $con->sql_query($q = "select * from po where old_po_amt_updated=0 and new_po_amt_updated=0 and branch_id>0 and id>0 $branch_check");
	
	while($r = $con->sql_fetchassoc($q1)){
		$appCore->poManager->reCalcatePOUsingOldMethod($r['branch_id'], $r['id']);
		$po_updated++;
	}
	$con->sql_freeresult($q1);
	
	print "\n$po_updated PO updated.\n";
	
}

function fix_mix_n_match_missing($date_from, $date_to, $branch_id=0){
	global $con;

	if(!strtotime($date_from)||!strtotime($date_to))  die("Invalid Date.\n");
	$branch_id = mi($branch_id);
	
	//if($branch_id>0) $branch_id=" and p.branch_id=$branch_id";
	//else $branch_id="";
	/*
		select p.branch_id,p.date,p.counter_id,p.id as pos_id, round((select round(sum(pmm.amount),2) from pos_mix_match_usage pmm where pmm.branch_id=p.branch_id and pmm.date=p.date and pmm.counter_id=p.counter_id and pmm.pos_id=p.id and pmm.amount>0),2) as real_mm_discount, ifnull(round((select sum(pp.amount) from pos_payment pp where pp.branch_id=p.branch_id and pp.date=p.date and pp.counter_id=p.counter_id and pp.pos_id=p.id and pp.type='Mix & Match Total Disc' and pp.adjust=0 and pp.amount>0),2),0) as pp_mm_discount
		from pos p
		where p.branch_id=47 and p.date='2017-3-2' and p.cancel_status=0
		having real_mm_discount>0 and pp_mm_discount=0
	*/
	
	$filter = array();
	$filter[] = "p.date between ".ms($date_from)." and ".ms($date_to);
	$filter[] = "p.cancel_status=0";
	if($branch_id)	$filter[] = "p.branch_id=$branch_id";
	
	$where = "where ".join(' and ', $filter);
	
	$sql = "select p.branch_id,p.date,p.counter_id,p.id as pos_id, round((select round(sum(pmm.amount),2) from pos_mix_match_usage pmm where pmm.branch_id=p.branch_id and pmm.date=p.date and pmm.counter_id=p.counter_id and pmm.pos_id=p.id and pmm.amount>0),2) as real_mm_discount, ifnull((select count(*) from pos_payment pp where pp.branch_id=p.branch_id and pp.date=p.date and pp.counter_id=p.counter_id and pp.pos_id=p.id and pp.type='Mix & Match Total Disc' and pp.adjust=0 and pp.amount>0),0) as pp_mm_count
		from pos p
		$where
		having real_mm_discount>0 and pp_mm_count=0";
	$q1 = $con->sql_query($sql);
	$fixed = 0;
	while($r = $con->sql_fetchassoc($q1)){
		$upd = array();
		$upd['branch_id'] = $r['branch_id'];
		$upd['counter_id'] = $r['counter_id'];
		$upd['pos_id'] = $r['pos_id'];
		$upd['type'] = 'Mix & Match Total Disc';
		$upd['remark'] = 'Mix & Match';
		$upd['amount'] = round($r['real_mm_discount'],2);
		$upd['date'] = $r['date'];
		
		$con->sql_query("select max(id) from pos_payment where branch_id=".mi($r['branch_id'])." and counter_id=".ms($r['counter_id'])." and date=".ms($r['date']));
		$upd['id'] = mi($con->sql_fetchfield(0))+1;
		$con->sql_freeresult();
		
		$con->sql_query("insert into pos_payment ".mysql_insert_by_field($upd));
		$fixed++;
	}
	$con->sql_freeresult($q1);
	
	print "\n$fixed Mix & Match pos_payment restore.\n";
}

function fix_grn_zero_selling_price($prms){
	global $con, $config;
	
	if(!$prms['branch_id']) die("Please provide a branch ID.");
	
	$filters = array();
	$filters[] = "grn.branch_id = ".mi($prms['branch_id']);
	if($prms['grn_id']) $filters[] = "grn.id = ".mi($prms['grn_id']);
	
	$filter = " and ".join(" and ", $filters);
	
	$q1 = $con->sql_query($sql="select gi.*, grr.rcv_date, ifnull(sip.price, si.selling_price) as selling_price
						   from grn 
						   left join grn_items gi on gi.grn_id = grn.id and gi.branch_id = grn.branch_id
						   left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
						   left join sku_items si on si.id = gi.sku_item_id
						   left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($prms['branch_id'])."
						   where (gi.selling_price = 0 or gi.selling_price is null) and grn.is_under_gst = 1 and grn.active = 1".$filter);

	while($r = $con->sql_fetchassoc($q1)){
		// get selling price from history
		$price_date = date("Y-m-d",strtotime("+1 day",strtotime($r['rcv_date'])));
		$q2 = $con->sql_query("select * from sku_items_price_history where added <= ".ms($price_date)." and sku_item_id = ".mi($r['sku_item_id'])." and branch_id = ".mi($r['branch_id'])." order by added desc limit 1");
		$siph = $con->sql_fetchassoc($q2);
		$con->sql_freeresult($q2);
	
		if($siph['price']){ // get selling price from history 
			$selling_price = $siph['price'];
		}else{ // otherwise get it from latest / master selling price
			$selling_price = $r['selling_price'];
		}
		
		$upd = array();
		$upd['selling_price'] = $selling_price;
		$con->sql_query("update grn_items set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and grn_id = ".mi($r['grn_id'])." and branch_id = ".mi($r['branch_id']));
		
		if($con->sql_affectedrows() > 0) $grn_list[$r['grn_id']] = $r['grn_id'];
	}
	$con->sql_freeresult($q1);
	
	// recalculate the total selling
	if(!$config['use_grn_future_allow_generate_gra']) $return_pcs=" - (ifnull(grn_items.return_ctn * rcv_uom.fraction,0) + ifnull(grn_items.return_pcs,0))";

	if($grn_list){
		foreach($grn_list as $grn_id){
			$total_sell = 0;
			$q1 = $con->sql_query("select sum((if(grn_items.acc_ctn is null and grn_items.acc_pcs is null, ifnull(grn_items.ctn,0) * rcv_uom.fraction + ifnull(grn_items.pcs,0), ifnull(grn_items.acc_ctn,0) * rcv_uom.fraction + ifnull(grn_items.acc_pcs,0))$return_pcs) * grn_items.selling_price / sell_uom.fraction) as sell
							 from grn_items
							 left join uom sell_uom on grn_items.selling_uom_id=sell_uom.id
							 left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
							 where grn_id = ".mi($grn_id)." and branch_id = ".mi($prms['branch_id'])." and item_check=0
							 group by grn_items.id") or die(mysql_error());

			while($r = $con->sql_fetchassoc($q1)){
				$total_sell += round($r['sell'], 2);
			}
			$con->sql_freeresult($q1);

			$con->sql_query("update grn set last_update=last_update,total_selling=".mf($total_sell)." where id = ".mi($grn_id)." and branch_id = ".mi($prms['branch_id']));
		}
		
		print "Total of ".count($grn_list)." GRN have been updated.\n";
	}
}

function fix_membership_receipt_no_gst($prms){ // php temp_script.php fix_membership_receipt_no_gst 15 2017-07-12
	global $con;
	
	if(!$prms['branch_id']) die("Please provide a branch ID.");
	if(!$prms['date']) die("Please provide a date.");
	
	$filters = array();
	$filters[] = "is_under_gst = 0";
	$filters[] = "branch_id = ".mi($prms['branch_id']);
	$filters[] = "date_format(timestamp, '%Y-%m-%d') = ".ms($prms['date']);

	$q1 = $con->sql_query($sql="select * from membership_receipt where ".join(" and ", $filters));
	
	$count = 0;
	while($r = $con->sql_fetchassoc($q1)){
		// update main
		$upd = array();
		$upd['amount'] = round($r['amount'] * 1.06, 2);
		$upd['change'] = 0;
		$upd['is_under_gst'] = 1;
		$upd['gross_amount'] = round($r['amount'], 2);
		$upd['gst_amount'] = round($upd['amount'] - $upd['gross_amount'], 2);
		
		$con->sql_query("update membership_receipt set timestamp = timestamp, ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])." and counter_id = ".mi($r['counter_id']));
		
		// update details
		$upd = array();
		$upd['gst_id'] = 14;
		$upd['gst_code'] = "SR";
		$upd['gst_rate'] = "6";
		$upd['gst_indicator'] = "S";
		$upd['amount'] = round($r['amount'] * 1.06, 2);
		$upd['item_gross_amount'] = round($r['amount'], 2);
		$upd['item_gst_amount'] = round($upd['amount'] - $upd['item_gross_amount'], 2);
		
		$con->sql_query("update membership_receipt_items set ".mysql_update_by_field($upd)." where receipt_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])." and counter_id = ".mi($r['counter_id']));

		if($con->sql_affectedrows() > 0) $count++;
	}
	$con->sql_freeresult($q1);
	
	print "Updated ".$count." records(s)\n";
}

function generate_stock_check_as_cutoff($bcode, $date){
	global $con;
	
	if(!$bcode)	die("No Branch Code.\n");
	$bid = get_branch_id($bcode);
	if(!$bid)	die("Invalid Branch Code $bcode\n");
	
	if(!$date)	die("No Date.\n");
	$date = date("Y-m-d", strtotime($date));
	$date_before = date("Y-m-d", strtotime("-1 day", strtotime($date)));
	$sb_tbl = "stock_balance_b".$bid."_".date("Y", strtotime($date_before));
	
	print "Branch: $bcode, Date: ".$date."\n";
	
	$curr_count = 0;
	//$limit = "limit 1";
	$q1 = $con->sql_query("select si.id, si.sku_item_code, if((select sc.sku_item_code from stock_check sc where sc.branch_id=$bid and date=".ms($date)." and sc.sku_item_code=si.sku_item_code limit 1) is null, 0, 1) as got_sc,
	if(sku.is_fresh_market='inherit',cc.is_fresh_market,sku.is_fresh_market) as is_fresh_market
	from sku_items si
	left join sku on sku.id=si.sku_id
	left join category_cache cc on sku.category_id = cc.category_id
	having got_sc=0
	order by si.id 
	$limit");
	$total_count = $con->sql_numrows($q1);
	while($si = $con->sql_fetchassoc($q1)){
		$curr_count++;
		print "\r$curr_count / $total_count . . .";
		
		// get stock balance and cost
		$q2 = $con->sql_query("select qty, cost from $sb_tbl where sku_item_id=".mi($si['id'])." and ".ms($date_before)." between from_date and to_date limit 1");
		$sb = $con->sql_fetchassoc($q2);
		$con->sql_freeresult($q2);
		
		$sc = array();
		$sc['date'] = $date;
		$sc['branch_id'] = $bid;
		$sc['sku_item_code'] = $si['sku_item_code'];
		$sc['scanned_by'] = 'admin';
		$sc['location'] = $sc['shelf_no'] = 1;
		$sc['item_no'] = $curr_count;
		$sc['qty'] = $sb['qty'];
		$sc['cost'] = $sb['cost'];
		$sc['is_fresh_market'] = $si['is_fresh_market'] == 'yes' ? 1 : 0;
		
		$con->sql_query("replace into stock_check ".mysql_insert_by_field($sc));
		$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id=".mi($si['id']));
		
	}
	$con->sql_freeresult($q1);
	
	print "\nDone.\n";
}

function check_multi_server_sp_with_hq(){
	global $con, $config, $arg;
	
	if($config['single_server_mode']){
		die("This program is for multi server mode only.\n");
	}
	
	$dummy = array_shift($arg);
	$dummy = array_shift($arg);
	
	while($cmd = array_shift($arg)){
		list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
		if($cmd_head == "-date"){
			$date = date("Y-m-d", strtotime($cmd_value));
		}elseif($cmd_head == "-is_run"){
			$is_run=1;
		}elseif($cmd_head == "-limit"){
			$limit_count = mi($cmd_value);
			if($limit_count<=0)	die("Invalid value for limit.\n");
		}else{
			print "Unknown command $cmd\n";
			exit;
		}
	}
	if(!$date)	die("Require -date=\n");
	
	$start_time = microtime(true);
	print "Start Time: ".date("Y-m-d H:i:s")."\n";
	
	$con->sql_query("select id from branch where code=".ms(BRANCH_CODE));
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	$bid = mi($tmp['id']);
	if(!$bid)	die("Invalid branch '".BRANCH_CODE."'.\n");
	
	print "Branch: ".BRANCH_CODE.", Branch ID: $bid\n";
	print "Date: ".$date."\n";
	
	print "Connecting HQ Database... ";
	$hqcon = connect_hq();
	print "HQ Connected.\n";
	
	print "Getting Records...\n";
	
	if($limit_count>0)	$limit = "limit $limit_count";
	
	$total_count = $curr_count = $need_replica_count = $fixed_count = 0;
	
	// select all sku with price change
	$sql = "select si.id as sid, (select siph.added from sku_items_price_history siph where siph.branch_id=$bid and siph.sku_item_id=si.id and siph.added<".ms($date)." order by siph.added desc limit 1) as siph_added
	from sku_items si
	having siph_added>0
	order by si.id
	$limit";
	$q1 = $con->sql_query($sql);
	$total_count = $con->sql_numrows();
	
	print "Total Rows: $total_count\n";
	
	while($r = $con->sql_fetchassoc($q1)){
		$curr_count++;
		print "\r$curr_count / $total_count . . . .";
		
		$sid = mi($r['sid']);
		
		// get the price change record
		$q2 = $con->sql_query("select * from sku_items_price_history where branch_id=$bid and sku_item_id=$sid and added=".ms($r['siph_added']));
		$siph = $con->sql_fetchassoc($q2);
		$con->sql_freeresult($q2);
		
		if(!$siph)	continue;
		
		// get the record from HQ
		$q2 = $hqcon->sql_query("select * from sku_items_price_history where branch_id=$bid and sku_item_id=$sid and added=".ms($r['siph_added'])." and price=".mf($siph['price']));
		$siph2 = $hqcon->sql_fetchassoc($q2);
		$hqcon->sql_freeresult($q2);
		
		// no data at HQ
		if(!$siph2){
			$need_replica_count++;
			//print_r($siph);exit;
			
			if($is_run){
				// real fix
				$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($siph));
				$fixed_count++;
			}else{
				// just checking
				print "SKU Item ID: $sid, Added: ".$r['siph_added'].", Price: ".$siph['price']."\n";
			}
		}
		
	}
	$con->sql_freeresult($q1);
	
	print "\n";
	
	$end_time = microtime(true);
	print "End Time: ".date("Y-m-d H:i:s")."\n";
	print "Time Used: ".round($end_time - $start_time, 4)."s\n";
	print "Item Need Resync: $need_replica_count\n";
	if($fixed_count)	print "Item Resynced: $fixed_count\n";
	print "Done.\n";
}

function recalc_sa_sales_cache(){
	global $con;
	// check need to recalculate if file exists
	if(!file_exists("recalc_sa_sales_cache.txt")){
		die("File 'recalc_sa_sales_cache.txt' does not exists, process terminated.\n");
	}
	
	$q1 = $con->sql_query("show tables like 'sa_sales_cache_b%'");
	while($t = $con->sql_fetchrow($q1)){
		$table = $t[0];
		if(!$table) continue;
		
		$con->sql_query("truncate $table");
	}
	$con->sql_freeresult($q1);
	unlink("last_calc_sa_sales_cache.txt"); // ensure need to recalculate from starting
	
	echo passthru('php cron.calc_sa_sales.php -allbranch > calc_sa_sales.log');
	unlink("recalc_sa_sales_cache.txt");
}

function fix_deposit_used_missing_pos_id($bid, $date_from, $date_to){
	global $con;
	
	$bid = mi($bid);
	if($bid<=0)	die("Invalid Date.\n");
	
	$fix_count = $not_fix_count = 0;
	
	$q1 = $con->sql_query("select * from pos_deposit_status where branch_id=$bid and date between ".ms($date_from)." and ".ms($date_to)." and pos_id=0 and receipt_no>0");
	$num = $con->sql_numrows($q1);
	print "Record Found: ".$num."\n";
	while($r = $con->sql_fetchassoc($q1)){
		print "Receipt No: ".$r['receipt_no']."\n";
		
		// Get the Used Receipt
		$con->sql_query("select id,pos_more_info from pos where branch_id=$r[branch_id] and counter_id=$r[counter_id] and date=".ms($r['date'])." and receipt_no=$r[receipt_no]");
		$pos = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$pos){
			print "POS Not Found\n";
			$not_fix_count++;
			continue;
		}
		
		$upd = array();
		$upd['pos_id'] = $pos['id'];
		$con->sql_query("update pos_deposit_status set ".mysql_update_by_field($upd)." where branch_id=$r[branch_id] and counter_id=$r[counter_id] and date=".ms($r['date'])." and receipt_no=$r[receipt_no] and pos_id=0");
		$con->sql_query("update pos_deposit_status_history set ".mysql_update_by_field($upd)." where branch_id=$r[branch_id] and counter_id=$r[counter_id] and pos_date=".ms($r['date'])." and receipt_no=$r[receipt_no] and pos_id=0");
		$fix_count++;
	}
	$con->sql_freeresult($q1);
	
	print "Fixed: $fix_count, Not Fix: $not_fix_count\n";
}

function june_gst_to_zero(){
	global $con;
	
	$date = date("Y-m-d");
	$allowed_date = '2018-06-01';
	print "Today is $date\n";
	
	if($date!=$allowed_date){
		print "Date is not $allowed_date, skipped.\n";
		exit;
	}
	
	$con->sql_query("update gst set rate=0 where code in ('tx','sr')");
	print "TX and SR updated to 0%.\n";
	
}

function fix_aneka_stock_take($bcode, $stock_take_date, $selected_sid = 0){
	global $con;
	
	$selected_sid = mi($selected_sid);
	if(!$bcode)	die("No Branch Code.\n");
	if(!$stock_take_date)	die("No Stock Take Date.\n");
	
	$stock_take_date = date("Y-m-d", strtotime($stock_take_date));
	
	$con->sql_query("select id,code from branch where code=".ms($bcode));
	$b = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$b)	die("Invalid branch code '$bcode'\n");
	
	$bid = mi($b['id']);
	
	print "Branch: ".$b['code']."\n";
	print "Branch ID: $bid\n";
	print "Stock Date: $stock_take_date\n";
	
	$curr_y = date("Y", strtotime($stock_take_date));
	
	$sb_tbl = 'stock_balance_b'.$bid.'_'.$curr_y;
	$sb2_tbl = 'stock_balance_b'.$bid.'_'.($curr_y-1);	// last year
	
	$sb_tbl_list = array($sb2_tbl, $sb_tbl);
	
	$year_first_day = date("Y-01-01", strtotime($stock_take_date));
	$one_day_b4 = date('Y-m-d',strtotime('-1 day',strtotime($stock_take_date)));
	
	$tbl = 'tmp_fix_sb';
	$con->sql_query("create temporary table $tbl(
		sid int primary key
	)");
	
	$filter = array();
	if($selected_sid > 0){
		$filter[] = "sb.sku_item_id=$selected_sid";
	}else{
		$filter[] = "((select count(*) from $sb_tbl sb2 where sb2.sku_item_id=sb.sku_item_id and sb2.to_date=".ms($one_day_b4).")>1 or to_date < from_date)";
	}
	
	$filter = "where ".join(' and ', $filter);
	
	// insert sid into tmp table using sb table
	$con->sql_query("insert IGNORE into $tbl 
	(sid)
	(select distinct sku_item_id as sid 
		from $sb_tbl sb
		$filter
		order by sid)");
	
	// insert sid into tmp table using stock check table	
	$con->sql_query("insert IGNORE into $tbl 
	(sid)
	(select si.id
from stock_check sc
join sku_items si on si.sku_item_code=sc.sku_item_code
where sc.branch_id=$bid and sc.date=".ms($stock_take_date)." and 
(select count(*) from $sb_tbl sb where ".ms($one_day_b4)." between sb.from_date and sb.to_date and sb.sku_item_id=si.id)>1)");
		
	$q1 = $con->sql_query("select sid
		from $tbl
		order by sid");
	$total_sku_count = mi($con->sql_numrows($q1));
	print "Total SKU Need To Fix: $total_sku_count\n";
	
	while($r = $con->sql_fetchassoc($q1)){	// Loop SKU
		$sid = mi($r['sid']);
		
		foreach($sb_tbl_list as $tbl){
			$q2 = $con->sql_query("select * from $tbl where sku_item_id=$sid order by from_date");
			while($r2 = $con->sql_fetchassoc($q2)){	// Loop Date
				$from_date = $r2['from_date'];
				
				$con->sql_query("select count(*) as c from $tbl where sku_item_id=$sid and from_date=".ms($from_date));
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				// Same date but more than one row of data, need delete
				if($tmp['c']>1){
					$total_duplicated_count = mi($tmp['c']);
					$delete_limit = $total_duplicated_count - 1;
					
					$con->sql_query("delete from $tbl where sku_item_id=$sid and from_date=".ms($from_date)." order by to_date desc, qty desc limit $delete_limit");
					$con->sql_query("delete from $tbl where sku_item_id=$sid and to_date < from_date");
					$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id=$sid");
				}
			}
			$con->sql_freeresult($q2);
			
			$con->sql_query("select count(*) as c from $tbl where sku_item_id=$sid and ".ms($one_day_b4)." between from_date and to_date");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
				
			if($tmp['c'] > 1){
				$con->sql_query("delete from  $tbl where sku_item_id=$sid and ".ms($one_day_b4)." between from_date and to_date order by from_date desc limit 1");
				$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id=$sid");
			}
			
			// fix those data got duplicate data at one day before the stock take date
			if($tbl == $sb_tbl){
				$con->sql_query("select count(*) as c from $tbl where sku_item_id=$sid and to_date=".ms($one_day_b4));
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($tmp['c']>1){
					$total_duplicated_count = mi($tmp['c']);
					$delete_limit = $total_duplicated_count - 1;
					
					$con->sql_query("delete from $tbl where sku_item_id=$sid and to_date=".ms($one_day_b4)." order by to_date desc, qty desc limit $delete_limit");
				}
			}
		}
	}
	$con->sql_freeresult($q1);
	
	print "Done.\n";
}

function change_cmaree_sp($bcode){
	global $con;
	
	$price_date = '2018-05-31';
	$price_datetime = $price_date.' 23:59:59';
	$price_timestamp = strtotime($price_datetime);
	$source = 'SCRIPT';
	$total_updated_count = 0;
	
	$b_list = array();
	
	if($bcode != 'all')	$str_branch_filter = " and code=".ms($bcode);
	$con->sql_query("select id,code from branch where active=1 $str_branch_filter order by id");
	while($r = $con->sql_fetchassoc()){
		$b_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	if(!$b_list)	die("No Branch Found.\n");
	
	$item_tax_code_list = array('SR','DS','GS','AJS');
	$sku_filter = array();
	$sku_filter[] = "if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit', cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes'";
	$sku_filter[] = "output_gst.code in (".join(',', array_map("ms", $item_tax_code_list)).")";
	$sku_filter[] = "si.added<=".ms($price_date)." and si.active=1";
	
	$str_sku_filter = '';
	if($sku_filter){
		$str_sku_filter = join(' and ', $sku_filter);
	}
		
	foreach($b_list as $bid => $b){
		print "Branch: ".$b['code'].", ID: $bid\n";
		
		$q_si = $con->sql_query("select si.id as sid, si.selling_price, si.cost_price, sku.default_trade_discount_code, sip.last_update as sip_last_update
			from sku_items si
			join sku on sku.id=si.sku_id
			left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
			join category_cache cc on cc.category_id=sku.category_id
			join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
			where $str_sku_filter
			order by sid");
		$total_sku_count = $con->sql_numrows($q_si);
		$curr_count = 0;
		$updated_count = 0;
		while($si = $con->sql_fetchassoc($q_si)){
			$curr_count++;
			$sid = mi($si['sid']);
			
			print "\r$curr_count / $total_sku_count . . .";
			
			// Get last price
			$con->sql_query("select * from sku_items_price_history where branch_id=$bid and sku_item_id=$sid and added<=".ms($price_datetime)." order by added desc limit 1");
			$siph = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$need_change = false;
			
			if(!$siph){	// never change price, need change
				$need_change = true;
			}else{
				if($siph['added'] != $price_datetime){
					if($siph['source'] != $source)	$need_change = true;
				}				
			}
			
			$old_price = $siph ? $siph['price'] : $si['selling_price'];
			$trade_discount_code = $siph['trade_discount_code'] ? $siph['trade_discount_code'] : $si['default_trade_discount_code'];
			$cost = $siph ? $siph['cost'] : $si['cost_price'];
			
			$new_selling_price = round(floor(($old_price / (6 + 100) * 100)*10)/10, 2);
			//print "$old_price = $new_selling_price\n";
			
			if(!$need_change)	continue;	// No Need Change
			
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['sku_item_id'] = $sid;
			$upd['added'] = $price_datetime;
			$upd['price'] = $new_selling_price;
			$upd['cost'] = $cost;
			$upd['source'] = $source;
			$upd['user_id'] = 1;
			$upd['trade_discount_code'] = $trade_discount_code;
			
			$upd2 = array();
			
			$lastest_price_timestamp = strtotime($si['sip_last_update']);
			if($price_timestamp >= $lastest_price_timestamp){
				$upd2['branch_id'] = $upd['branch_id'];
				$upd2['sku_item_id'] = $upd['sku_item_id'];
				$upd2['price'] = $upd['price'];
				$upd2['cost'] = $upd['cost'];
				$upd2['trade_discount_code'] = $upd['trade_discount_code'];
				$upd2['last_update'] = $price_datetime;
			}
			
			if($upd2){
				$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($upd2));
			}
			$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($upd));
			
			$updated_count++;
			$total_updated_count++;
		}
		$con->sql_freeresult($q_si);
		
		print "Done. $updated_count sku updated.\n\n";
	}
	
	print "Finish! Total $total_updated_count Updated.\n";
}

function change_metrohouse_sp($bcode, $run_type){
	global $con;
	
	if(!$run_type)	$run_type = "check";
	if(!in_array($run_type, array("check", "export", "update"))){
		die("Invalid Run Type\n");
	}
	print "Run Type: ".$run_type."\n";
	
	$price_date = '2018-05-31';
	$price_datetime = $price_date.' 23:59:59';
	$price_timestamp = strtotime($price_datetime);
	$source = 'SCRIPT';
	$total_updated_count = 0;
	
	
	$b_list = array();
	
	if($bcode != 'all')	$str_branch_filter = " and code=".ms($bcode);
	$sql = "select id,code from branch where active=1 $str_branch_filter order by id";
	//print $sql;exit;
	$con->sql_query($sql);
	while($r = $con->sql_fetchassoc()){
		$b_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	if(!$b_list)	die("No Branch Found.\n");
	
	$item_tax_code_list = array('SR','DS','GS','AJS');
	$sku_filter = array();
	$sku_filter[] = "if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit', cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes'";
	$sku_filter[] = "output_gst.code in (".join(',', array_map("ms", $item_tax_code_list)).")";
	$sku_filter[] = "si.added<=".ms($price_date)." and si.active=1";
	
	$str_sku_filter = '';
	if($sku_filter){
		$str_sku_filter = join(' and ', $sku_filter);
	}
	
	$time = time();
	$file_name_prefix = "metrohouse_change_selling_".$time;
	$export_header = array("Branch Code","ARMS Code","Mcode","Art No","Description","Old Price","New Price","New Price After Round");
		
	foreach($b_list as $bid => $b){
		print "Branch: ".$b['code'].", ID: $bid\n";
		
		$q_si = $con->sql_query("select si.id as sid, si.selling_price, si.cost_price, sku.default_trade_discount_code, sip.last_update as sip_last_update, si.sku_item_code, si.mcode, si.artno, si.description
			from sku_items si
			join sku on sku.id=si.sku_id
			left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
			join category_cache cc on cc.category_id=sku.category_id
			join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
			where $str_sku_filter
			order by sid");
		$total_sku_count = $con->sql_numrows($q_si);
		$curr_count = 0;
		$updated_count = 0;
		$need_update_count = 0;
		while($si = $con->sql_fetchassoc($q_si)){
			$curr_count++;
			$sid = mi($si['sid']);
			
			print "\r$curr_count / $total_sku_count . . .";
			
			// Get last price
			$con->sql_query("select * from sku_items_price_history where branch_id=$bid and sku_item_id=$sid and added<=".ms($price_datetime)." order by added desc limit 1");
			$siph = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$need_change = false;
			
			if(!$siph){	// never change price, need change
				$need_change = true;
			}else{
				if($siph['added'] != $price_datetime){
					if($siph['source'] != $source)	$need_change = true;
				}				
			}
			
			$old_price = $siph ? $siph['price'] : $si['selling_price'];
			$trade_discount_code = $siph['trade_discount_code'] ? $siph['trade_discount_code'] : $si['default_trade_discount_code'];
			$cost = $siph ? $siph['cost'] : $si['cost_price'];
			
			$new_selling_price_before_round = ($old_price / (6 + 100) * 100);
			//print "$old_price = $new_selling_price = ";
			$new_selling_price = round(floor($new_selling_price_before_round*2*10)/10/2, 2);
			//print "$new_selling_price\n";
			
			//if($curr_count>10)	break;
			
			if(!$need_change)	continue;	// No Need Change
			
			if($run_type == 'update'){
				$upd = array();
				$upd['branch_id'] = $bid;
				$upd['sku_item_id'] = $sid;
				$upd['added'] = $price_datetime;
				$upd['price'] = $new_selling_price;
				$upd['cost'] = $cost;
				$upd['source'] = $source;
				$upd['user_id'] = 1;
				$upd['trade_discount_code'] = $trade_discount_code;
				
				$upd2 = array();
				
				$lastest_price_timestamp = strtotime($si['sip_last_update']);
				if($price_timestamp >= $lastest_price_timestamp){
					$upd2['branch_id'] = $upd['branch_id'];
					$upd2['sku_item_id'] = $upd['sku_item_id'];
					$upd2['price'] = $upd['price'];
					$upd2['cost'] = $upd['cost'];
					$upd2['trade_discount_code'] = $upd['trade_discount_code'];
					$upd2['last_update'] = $price_datetime;
				}
				
				if($upd2){
					$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($upd2));
				}
				$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($upd));
				
				$updated_count++;
				$total_updated_count++;
			}else{
				$need_update_count++;
				
				if($run_type == 'export'){
					if(!$fp){	// open write file
						$file_name = $file_name_prefix."_".str_replace('/', '', $b['code']).".csv";
						$fp = fopen("/tmp/".$file_name, 'w');
						fputcsv($fp, array_values($export_header));
					}
					
					$data = array($b['code'], $si['sku_item_code'], $si['mcode'], $si['artno'], $si['description'], $old_price, $new_selling_price_before_round, $new_selling_price);
					fputcsv($fp, array_values($data));
				}
			}
		}
		$con->sql_freeresult($q_si);
		
		if($run_type == 'export' && $fp){
			fclose($fp);
			unset($fp);
		}
		
		print "\nDone. $updated_count sku updated.\n\n";
	}
	
	if($run_type == 'export' && $need_update_count>0){
		$parent_zip = "Metrohouse_".$time;
		exec("cd /tmp; zip -9 $parent_zip.zip $file_name_prefix*.csv");
		print "Zip File: /tmp/$parent_zip.zip\n";
	}
	
	if($need_update_count>0)	print "Total $need_update_count sku need update.\n";
	print "Finish! Total $total_updated_count Updated.\n";
}

function fix_membership_history_nric(){
	global $con;
	
	// select those members which having NRIC starts with "0...", equal or more than 8 digits and having empty membership renewal history
	//$q1 = $con->sql_query("select m.nric from membership m left join membership_history mh on mh.nric = m.nric where (mh.nric is null or mh.nric = '') and length(m.nric) >= 8 and m.nric like '0%'");
	$q1 = $con->sql_query("select m.nric, mh.nric as his_nric, mh.card_no from membership m left join membership_history mh on mh.card_no = m.card_no where mh.nric != m.nric");
	
	while($r = $con->sql_fetchassoc($q1)){
		$int_nric = mi($r['nric']);
		
		// try to locate the membership renewal history with integer NRIC
		$q2 = $con->sql_query("select * from membership_history where nric = ".mi($int_nric));
		
		while($r1 = $con->sql_fetchassoc($q2)){
			$upd = array();
			$upd['nric'] = $r['nric'];
			
			$sql = "update membership_history set nric = ".ms($r['nric'])." where id = ".mi($r1['id'])." and branch_id = ".mi($r1['branch_id']);
			$con->sql_query($sql);
		}
		$con->sql_freeresult($q2);
	}
	$con->sql_freeresult($q1);
}

function prepend_membership_nric(){
	global $con;

	// affected tables
	$tbl_list = array("membership_extra_info"=>"nric", "membership_isms_items"=>"nric", "membership_points"=>"nric", "membership_receipt"=>"reference", "membership_redemption"=>"nric", "membership_history"=>"nric", "membership"=>"nric");
	
	$q1 = $con->sql_query("select * from membership where length(nric) < 8");
	
	print "Total ".$con->sql_numrows($q1)." records. <br /><br />";
	$error_list = array();
	while($r = $con->sql_fetchassoc($q1)){
		$new_nric = str_pad($r['nric'], 8, "0", STR_PAD_LEFT);
		
		// check again if the new nric is existed on system
		$q2 = $con->sql_query("select nric, card_no, name from membership where nric = ".ms($new_nric));
		
		if($con->sql_numrows($q2) == 0){
			foreach($tbl_list as $tbl_name=>$field_name){
				if(!$field_name) $field_name = "nric";
				
				$upd = array();
				$upd[$field_name] = $new_nric;
				
				$con->sql_query("update $tbl_name set ".mysql_update_by_field($upd)." where $field_name = ".ms($r['nric']));
			}
		}else{
			$member_info = $con->sql_fetchassoc($q2);
			$error_list[] = $member_info;
		}
		$con->sql_freeresult($q2);
	}
	$con->sql_freeresult($q1);
	
	// need to generate a csv file as if there are duplicated member's nric
	if($error_list) {
		check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/attch/membership");
		$fp = fopen($_SERVER['DOCUMENT_ROOT']."/attch/membership/duplicate_members.csv", 'w');
		$headers = array(0=>"NRIC", 1=>"Card No", 2=>"Name");
		fputcsv($fp, array_values($headers));
		
		foreach($error_list as $r){
			fputcsv($fp, array_values($r));
		}
		fclose($fp);
		
		chmod($_SERVER['DOCUMENT_ROOT']."/attch/membership/duplicate_members.csv", 0777);
		
		print "Found duplicated members, please download it via ".$_SERVER['DOCUMENT_ROOT']."/attch/membership/duplicate_members.csv";
	}
}

function fix_branch_approval_history(){
	global $con;
	
	$q1 = $con->sql_query("select * from branch_approval_history where id > 1000000000 order by id asc");
	
	while($r = $con->sql_fetchassoc($q1)){
		$tbl = strtolower($r['ref_table']);
		$tbl_id = $r['ref_id'];
		$bid = $r['branch_id'];
		
		$filters = array();
		$filters[] = "branch_id = ".mi($bid);
		if($tbl_id) $filters[] = "id = ".mi($tbl_id); // filter ID if found any
		else $filters[] = "approval_history_id = ".mi($r['id']); // otherwise filter approval history id
		
		// check if have this table and able to get the data
		$q2 = $con->sql_query("select * from $tbl where ".join(" and ", $filters)." order by id asc limit 1",false,false);
		$tbl_info = $con->sql_fetchassoc($q2);
		$con->sql_freeresult($q2);
		
		if($tbl_info){ // found having the table which using wrong approval history ID
			// load max ID which lesser than the wrong ID
			$q2 = $con->sql_query("select max(id) as mid from branch_approval_history where id < 1000000000");
			$id_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			$new_ah_id = $id_info['mid']+1;
			
			//print "Table: ".$tbl."<br />";
			// update the table which use the approval history first
			$con->sql_query("update $tbl set approval_history_id=".mi($new_ah_id).", last_update=last_update where id = ".mi($tbl_info['id'])." and branch_id = ".mi($tbl_info['branch_id']));
			//print "update $tbl set approval_history_id=".mi($new_ah_id).", last_update=last_update where id = ".mi($tbl_info['id'])." and branch_id = ".mi($tbl_info['branch_id'])."<br />";
			
			// update approval history item
			$con->sql_query("update branch_approval_history_items set approval_history_id=".mi($new_ah_id)." where approval_history_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
			//print "update branch_approval_history_items set approval_history_id=".mi($new_ah_id).", added=added where approval_history_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])."<br />";
			
			// update approval history itself
			$con->sql_query("update branch_approval_history set id=".mi($new_ah_id).", added=added where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
			//print "update branch_approval_history set id=".mi($new_ah_id)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])."<br /><br />";
		}
	}
	$con->sql_freeresult($q1);
	
	// select again to see if have any invalid ID still there
	// otherwise alter the table ID
	$q1 = $con->sql_query("select max(id) as mid from branch_approval_history");
	$ah_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	if($ah_info['mid'] < 1000000000){
		//$next_ah_id = $ah_info['mid'] + 1;
		//$con->sql_query("alter table branch_approval_history auto_increment = ".mi($next_ah_id));
		print "Approval History ID has been fixed.";
	}else print "Still have issue on the approval history ID....<br />";
}

function fix_metrohouse_price_type(){
	global $con;
	
	// sku_items_future_price
	$q1 = $con->sql_query("select * from sku_items_future_price where active=1 and status=1 and approved=1 and id in (5,6,7) order by id");
	while($fp = $con->sql_fetchassoc($q1)){
		$fp_id = mi($fp['id']);
		print "FP ID: $fp_id\n";
		$fp['effective_branches'] = unserialize($fp['effective_branches']);
		
		// sku_items_future_price_items
		$q2 = $con->sql_query("select fpi.*, sku.default_trade_discount_code
			from sku_items_future_price_items fpi
			left join sku_items si on si.id=fpi.sku_item_id
			left join sku on sku.id=si.sku_id
			where fpi.branch_id=".mi($fp['branch_id'])." and fpi.fp_id=$fp_id 
			order by fpi.id");
		$total_items = $con->sql_numrows($q2);
		$curr_count = $updated_count = 0;
		while($fpi = $con->sql_fetchassoc($q2)){
			$curr_count++;
			print "\r$curr_count / $total_items";
			
			$sid = mi($fpi['sku_item_id']);
			
			foreach($fp['effective_branches'] as $bid){
				$bid = mi($bid);
				
				// get update time
				$con->sql_query("select * from sku_items_price_history where sku_item_id=$sid and branch_id=$bid and fp_id=$fp_id 
				order by added desc limit 1");
				$fp_data = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($fp_data){
					// Get data before update
					$con->sql_query("select * from sku_items_price_history where sku_item_id=$sid and branch_id=$bid and added<".ms($fp_data['added'])." and fp_id=0 order by added desc limit 1");
					$prev_data = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					$trade_discount_code = $prev_data['trade_discount_code'] ? $prev_data['trade_discount_code'] : $fpi['default_trade_discount_code'];
					
					// check need update price history
					if($fp_data['trade_discount_code'] != $trade_discount_code){
						//print "BID: $bid, SID: $sid, Need Update Price Type History from ".$fp_data['trade_discount_code']." to ".$trade_discount_code."\n";
						$con->sql_query("update sku_items_price_history set trade_discount_code=".ms($trade_discount_code).", added=added where sku_item_id=$sid and branch_id=$bid and fp_id=$fp_id");
					}
					
					// Get Latest Price
					$con->sql_query("select * from sku_items_price where sku_item_id=$sid and branch_id=$bid");
					$latest_data = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($latest_data['trade_discount_code'] != $trade_discount_code){
						//print "BID: $bid, SID: $sid, Need Update Latest Price Type from ".$latest_data['trade_discount_code']." to ".$trade_discount_code."\n";
						$con->sql_query("update sku_items_price set trade_discount_code=".ms($trade_discount_code).", last_update=last_update where sku_item_id=$sid and branch_id=$bid");
						$updated_count++;
					}
				}
			}
		}
		$con->sql_freeresult($q2);
	}
	$con->sql_freeresult($q1);
	print "\n";
	print "Done. $updated_count Updated.\n";
}

function fix_price_history_fpi_id($bcode){
	global $con, $prms;
	
	$tbl_list = array("sku_items_price_history", "sku_items_mprice_history", "sku_items_qprice_history");
	
	if($bcode) $bfilter = " and code = ".ms($bcode);
	$q1 = $con->sql_query("select * from branch where active=1".$bfilter);
	
	if($con->sql_numrows($q1) == 0) die("Branch Not Found.\n");
	
	while($r = $con->sql_fetchassoc($q1)){
		$branch_list[$r['id']] = $r['code'];
	}
	$con->sql_freeresult($q1);
	
	foreach($branch_list as $bid=>$bcode){
		foreach($tbl_list as $tbl_name){
			$q1 = $con->sql_query("select his.* 
								   from $tbl_name his
								   left join sku_items_future_price_items sifpi on sifpi.fp_id = his.fp_id and sifpi.branch_id = his.fp_branch_id and sifpi.id = his.fpi_id and sifpi.sku_item_id = his.sku_item_id
								   where his.branch_id = ".mi($bid)." and his.fp_id > 0 and his.fp_branch_id > 0 and added >= '2018-01-01 00:00:00' and sifpi.id is null 
								   order by his.fp_branch_id, his.fp_id");
			
			if($con->sql_numrows($q1) == 0){
				print "No record found for $tbl_name table ($bcode)\n";
				continue;
			}
			
			print "Found ".$con->sql_numrows($q1)." records needs to update for $tbl_name table ($bcode)...\n";
			
			while($r = $con->sql_fetchassoc($q1)){
				$filters = array();
				$filters[] = "fp_id = ".mi($r['fp_id'])." and branch_id = ".mi($r['fp_branch_id'])." and sku_item_id = ".mi($r['sku_item_id']);
				$filters[] = "future_selling_price = ".mf($r['price']);
				if($tbl_name == "sku_items_price_history"){
					$filters[] = "type = 'normal'";
				}elseif($tbl_name == "sku_items_mprice_history"){
					$filters[] = "type = ".ms($r['type']);
				}else{
					$filters[] = "min_qty = ".mf($r['min_qty'])." and type = 'qprice'";
				}
				$q2 = $con->sql_query("select * from sku_items_future_price_items where ".join(" and ",$filters)." order by id");
				
				if($con->sql_numrows($q2) > 1){ // found matches more than one result within a change price record, show errors
					print "Error: FP#".$r['fp_id'].", BID#".$r['fp_branch_id'].", SID#".$r['sku_item_id']." contains more than one change price items.\n";
				}elseif($con->sql_numrows($q2) == 0){ // no record found
					print "Error: FP#".$r['fp_id'].", BID#".$r['fp_branch_id'].", SID#".$r['sku_item_id']." is not found.\n";
				}else{ // found only one record, proceed to update
					$fpi_info = $con->sql_fetchassoc($q2);
					$filters = array();
					$filters[] = "fp_id = ".mi($r['fp_id'])." and fp_branch_id = ".mi($r['fp_branch_id'])." and sku_item_id = ".mi($r['sku_item_id'])." and branch_id = ".mi($r['branch_id']);
					$filters[] = "price = ".mf($r['price']);
					
					if($tbl_name == "sku_items_price_history"){
						$msg = "Type: Normal";
						//$filters[] = "type = 'normal'";
					}elseif($tbl_name == "sku_items_mprice_history"){
						$msg = "Type: ".$r['type'];
						$filters[] = "type = ".ms($r['type']);
					}else{
						$msg = "Type: QPrice";
						$filters[] = "min_qty = ".mf($r['min_qty']);
					}
					
					$q3 = $con->sql_query("update $tbl_name set fpi_id = ".mi($fpi_info['id']).", added=added where ".join(" and ", $filters));
					
					if($con->sql_affectedrows($q3) > 0){
						print "Success: FP#".$r['fp_id'].", BID#".$r['branch_id'].", SID#".$r['sku_item_id'].", $msg has been updated.\n";
					}else{
						print "Failed: FP#".$r['fp_id'].", BID#".$r['branch_id'].", SID#".$r['sku_item_id'].", $msg failed to update.\n";
					}
				}
				$con->sql_freeresult($q2);
			}
			$con->sql_freeresult($q1);
		}
	}
}

function test_api(){
	//print "Testing API<br>===============================================<br>";
	//$encrypt_token = md5('Arms2018'.'5698cbnjmk'); // HQ02
	$encrypt_token = md5('Arms2018'.'3344556677'); // DEV02
	$s = curl_init();
	$headers = array(
		 'X_ENCRYPT_TOKEN: '.$encrypt_token,
		 //'X_DEVICE_ID: 123', // HQ02
		 'X_DEVICE_ID: 456', // DEV02
		 'X_APP_VERSION: 1.0'
	  );
	curl_setopt($s, CURLOPT_URL, "10.1.1.200:2001/api.arms.internal.php");
	curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
	$data = array(
		//'a' => 'pair_device'
		
		//'a' => 'validate_device',
		
		//'a' => 'get_product_count',
		
		/*'a' => 'get_branches_info',
		'branch_id_list[0]' => 1,
		'branch_id_list[1]' => 47,*/
		
		/*'a' => 'get_products',
		'limit' => 2,
		'barcode' => '285095080000',*/
		//'last_change' => '2018-11-1'
		
		'a' => 'get_product_details',
		//'barcode' => '285095080000',
		'barcode' => '280159770000',
	);
	curl_setopt($s, CURLOPT_POSTFIELDS, $data);
	$ret = curl_exec($s);
	curl_close($s);
	
	print_r($ret);
	//print "<br>===============================================<br>Done";
}

function answer_api(){
	print_r($_SERVER);
}

function test_boost_api(){
	print "Testing API<br>===============================================<br>";
	$s = curl_init();
	/*$headers = array(
		 'X_ENCRYPT_TOKEN: '.$encrypt_token,
		 'X_DEVICE_ID: 123'
	  );*/
	curl_setopt($s, CURLOPT_URL, "https://stage-wallet.boostorium.com/authentication");
	//curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
	$data = array(
		'apiKey' => 'CJPHFRD7FPQ6NR86BZY1XC4OYX',
		'apiSecret' => '800ca23a-b730-4156-905d-7fddd7746458',
	);
	curl_setopt($s, CURLOPT_POSTFIELDS, $data);
	$ret = curl_exec($s);
	curl_close($s);
	print_r($ret);
	print "<br>===============================================<br>Done";
}

function regenerate_pos_cashier_finalise($prms=array()){
	global $con, $appCore;
	
	if(!$prms) die("Please specify the filter.\n");
	
    $con->sql_query("select id from branch where code=".ms($prms['branch_code']));
    $bid = mi($con->sql_fetchfield(0));
    $con->sql_freeresult();
    if(!$bid)   die("Invalid Branch Code.\n");
    
    if(!strtotime($prms['from_date'])||!strtotime($prms['to_date']))  die("Invalid Date.\n");
    print $prms['from_date']." to ".$prms['to_date']."\n";
	
	$counter_list = array();
	if($prms['counter_name']) $cfilter = "where network_name = ".ms($prms['counter_name']);
	$con->sql_query("select id, network_name from counter_settings ".$cfilter);
    while($r = $con->sql_fetchassoc($q1)){
		$counter_list[$r['id']] = $r;
	}
    $con->sql_freeresult($q1);
    if(!$counter_list) die("Invalid Counter Name.\n");

	// get the date range between 2 dates
	$from_time = new DateTime($prms['from_date']);
	$to_time = new DateTime($prms['to_date']);
	
	for($i = $from_time; $i <= $to_time; $i->modify('+1 day')){
		$curr_date = $i->format("Y-m-d");
		foreach($counter_list as $cid => $ci){
			$con->sql_query("delete from pos_cashier_finalize where branch_id=".mi($bid)." and date=".ms($curr_date)." and counter_id = ".mi($cid));
			print "Processing Date ".$curr_date." for Counter ".$ci['network_name']."\n";
			$appCore->posManager->generatePosCashierFinalize($curr_date, $cid, $bid);
		}
	}
}

function change_array_value_to_string(&$arr){
	foreach	($arr as &$other){
		if (is_array($other))	change_array_value_to_string($other);
		else	$other=strval($other);
	}
}

function patch_pp_group_type($arg){
	global $con, $config, $pos_config;

	if(!$config['pp_group_type_list']) die("config 'pp_group_type_list' is not being configured\n");
	
	$filters = array();
	$dummy = array_shift($arg);
	$need_regen = false;
	$branch_list = array();
	$is_run = false;
	
	while($cmd = array_shift($arg)){
		list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
				
		if($cmd_head == "-branch"){
			$branch_filter = 'where active=1';
			if($cmd_value == 'all'){
				if(!$config['single_server_mode']){
					$bcode = BRANCH_CODE;
					$branch_filter .= ' and code='.ms($bcode);
				}
			}else{
				$bcode_list = array_map("ms", explode(",", trim($cmd_value)));
				$branch_filter .= ' and code in ('.join(",", $bcode_list).")";
			}

			$con->sql_query("select id,code,description from branch $branch_filter order by sequence,code");
			while($r = $con->sql_fetchassoc()){
				if(isset($config['sales_report_branches_exclude']) && is_array($config['sales_report_branches_exclude']) && in_array($r['code'], $config['sales_report_branches_exclude'])){
					continue;	// this branch no need show in sales report
				}
				$branch_list[$r['id']] = $r;
			}
			$con->sql_freeresult();
		}elseif($cmd_head == '-date'){	// date
			$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
			if(!$tmp)	die("Invalid Date.\n");
			if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
			$date = $tmp;
		}elseif($cmd_head == '-date_from'){	// date_from
			$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
			if(!$tmp)	die("Invalid Date From.\n");
			if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
			$date_from = $tmp;
		}elseif($cmd_head == '-date_to'){	// date_to
			$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
			if(!$tmp)	die("Invalid Date To.\n");
			if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
			$date_to = $tmp;
		}elseif($cmd_head == '-recent_day'){	// use recently day
			$num = mi($cmd_value);
			if($num<=0)	die("Recent Day must more than zero.\n");
			
			$date_to = date("Y-m-d", strtotime("-1 day"));
			$date_from = date("Y-m-d", strtotime("-".($num-1)." day", strtotime($date_to)));
		}elseif($cmd_head == '-regen'){	// force regenerate
			$need_regen = true;
		}elseif($cmd_head == '-is_run'){	// force regenerate
			$is_run = true;
		}
	}

	//print_r($branch_list);
	
	if(!$branch_list)	die("Please provide -branch=\n");
	
	// check date
	if(!$date && !$date_from && !$date_to){
		die("Please provide date by using -date= or -date_from= or -date_to=\n");
	}
	if(!$date && ($date_from || $date_to)){
		if(!$date_from){
			die("Please provide -date_from=\n");
		}
		if(!$date_to){
			die("Please provide -date_to=\n");
		}
		if(strtotime($date_to) < strtotime($date_from)){
			die("'Date To' cannot earlier than 'Date From'.\n");
		}
	}else{
		$date_from = $date_to = $date;
	}
		
	// Convert all payment type in pos_config to lower case
	$pos_config_payment_type = array_map('strtolower', $pos_config['payment_type']);
	
	// Convert all custom payment type to lower case
	$counter_collection_extra_payment_type = array();
	if($config['counter_collection_extra_payment_type'])	$counter_collection_extra_payment_type = array_map('strtolower', $config['counter_collection_extra_payment_type']);
	
	$foreign_currency_list = array();
	if($config['foreign_currency']){
		$foreign_currency_list = array_map('strtolower', array_keys($config['foreign_currency']));
	}
	//print_r($counter_collection_extra_payment_type);exit;
	//print_r($foreign_currency_list);exit;
	
	foreach($branch_list as $bid => $b){
		print "Branch: ".$b['code'].", ID: $bid\n";
		print "Date: $date_from to $date_to.\n";
		
		$filter = array();
		$filter[] = "pp.branch_id=$bid and pp.date between ".ms($date_from)." and ".ms($date_to);
		if(!$need_regen){
			$filter[] = "pp.group_type = ''";
		}
		$str_filter = join(' and ', $filter);
		
		$sql = "select pp.*
			from pos_payment pp
			where $str_filter
			order by pp.date, pp.counter_id, pp.pos_id, pp.id";
		//print $sql;
		
		$q1 = $con->sql_query($sql);
		$total_row = $con->sql_numrows($q1);
		$upd_count = $total_updated_count = 0;
		print "Total Rows need to update: $total_row\n";
		
		while($r = $con->sql_fetchassoc($q1)){
			$group_type = "";
			
			// no payment type?
			if(!$r['type']) continue;
			$payment_type = $r['type'];
			
			// fixed multiple version of credit card word
			$tmp_cc = ucwords(str_replace("_", " ",$payment_type));
			if($tmp_cc == "Credit Card") $payment_type = "credit cards";
			
			// payment type must be small letter
			$payment_type = strtolower($payment_type);

			// Check eWallet
			if(preg_match('/^ewallet_/', $payment_type)){
				$group_type = "ewallet";
			}
			
			// Check config group type
			if(!$group_type){
				// loop this config to get the group type - either standard, discount, rounding or deposit
				foreach($config['pp_group_type_list'] as $tmp_group_type=>$gt_list){
					if(in_array($payment_type, $gt_list)){
						$group_type = $tmp_group_type;
						break;
					}
				}
			}
			
			
			// Check pos_config for standard payment
			if(!$group_type){
				if(in_array($payment_type, $pos_config_payment_type)){
					$group_type = 'standard';
				}
			}
			
			// if cannot find it from standard config list, check with custom payment
			if(!$group_type && $counter_collection_extra_payment_type){
				if(in_array($payment_type, $counter_collection_extra_payment_type)){
					$group_type = 'custom';
				}
			}
			
			if(!$group_type && $foreign_currency_list){
				if(in_array($payment_type, $foreign_currency_list)){
					$group_type = 'currency';
				}
			}
			
			print "$payment_type = $group_type\n";
			
			// Found group_type
			if($group_type){
				$upd = array();
				$upd['group_type'] = $group_type;
				$upd['more_info'] = unserialize($r['more_info']);
				if(!$upd['more_info'])	$upd['more_info'] = array();
				
				// Store Old Group Type
				if(!isset($upd['more_info']['group_type_info']['old_group_type'])){
					$upd['more_info']['group_type_info']['old_group_type'] = trim($r['group_type']);
				}
				$upd['more_info']['group_type_info']['last_update'] = date("Y-m-d H:i:s");
				
				$upd['more_info'] = serialize($upd['more_info']);
				
				if($is_run){
						$q2 = $con->sql_query("update pos_payment set ".mysql_update_by_field($upd)." where branch_id = ".mi($r['branch_id'])." and counter_id = ".mi($r['counter_id'])." and date = ".ms($r['date'])." and pos_id = ".mi($r['pos_id'])." and id = ".mi($r['id']));
					if($con->sql_affectedrows($q2) > 0){
						$upd_count++;
						$total_updated_count++;
					}
				}				
			}
		}
		$con->sql_freeresult($q1);
		
		print "$upd_count records updated.\n";
		
		print "\n";
	}
	
	print "Total $total_updated_count records updated.\n";
	print "Done.\n";
}

function test_paydibs_api(){
	if(!$_REQUEST['user_qr_code']) die("Please provide QR Code");
	
	$receipt_no = "00200017026010000";
	
	print "Testing API<br>===============================================<br>";
	
	if($_REQUEST['is_real']){
		$merchant_key = "Tpa3b92Mulw89Wq";
		$api_sign_key = "EJ~jZ!4z?yOaee,vz;oo";
		$login_id = "BF99970002";
		$login_password = md5(112233);
		$url = "https://mcashbizapi.paydibs.com/version1";
	}else{
		$merchant_key = "Tpa3b34Mulw98WW";
		$api_sign_key = "TJ~jZ!4z?yObsDd,vz;oo";
		$login_id = "BY91690002";
		$login_password = md5(112233);
		$url = "https://merchanttest.paydibs.com/version1";
	}
	// =============================================================================================
	// get Auth Token
	// =============================================================================================
	$s = curl_init();
	$headers = array(
		 'Content-Type: application/x-www-form-urlencoded',
		 'Key: '.$merchant_key
	);
	curl_setopt($s, CURLOPT_URL, $url."/token/api-token");
	curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
	
	// build up the data
	$time = time();
	$data = array();
	$data['login_id'] = $login_id;
	$data['login_password'] = $login_password;
	$data['time'] = $time;
	$data['sign'] = getSign($data, $api_sign_key);

	curl_setopt($s, CURLOPT_POST, true);
	curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($data));
	$ret = curl_exec($s);
	curl_close($s);
	$auth_token_info = json_decode($ret, true);
	
	// =============================================================================================
	// generate paydibs transaction ID
	// =============================================================================================
	if($auth_token_info['data']['status'] == 1){
		$s = curl_init();
		$headers = array(
			 'Content-Type: application/x-www-form-urlencoded',
			 'Key: '.$merchant_key,
			 'Auth-Token: '.$auth_token_info['data']['Auth_Token']
		);
		curl_setopt($s, CURLOPT_URL, $url."/payment/create-trade");
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		
		// build up the data
		$data = array();
		$data['integrator_id'] = 3;
		$data['amount'] = 0.01;
		$data['time'] = $time;
		$data['trx_id'] = $receipt_no;
		$data['pay_type'] = "MSU";
		$data['sign'] = getSign($data, $api_sign_key);

		curl_setopt($s, CURLOPT_POST, true);
		curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($data));
		$ret = curl_exec($s);
		curl_close($s);
		$trans_info = json_decode($ret, true);
	}else print $auth_token_info['data']['msg'];
	
	// =============================================================================================
	// do status checking
	// =============================================================================================
	if($trans_info['data']['trans_id']){
		$s = curl_init();
		$headers = array(
			 'Content-Type: application/x-www-form-urlencoded',
			 'Key: '.$merchant_key,
			 'Auth-Token: '.$auth_token_info['data']['Auth_Token']
		);
		curl_setopt($s, CURLOPT_URL, $url."/payment/check-status");
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		
		// build up the data
		$data = array();
		$data['time'] = $time;
		$data['integrator_id'] = 3;
		$data['trans_id'] = "20190329150700338216";
		$data['sign'] = getSign($data, $api_sign_key);
		
		// get Auth Token
		curl_setopt($s, CURLOPT_POST, true);
		curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($data));
		$ret = curl_exec($s);
		curl_close($s);
		print_r($ret);
	}else print_r($trans_info['data']['msg']);
	
	// =============================================================================================
	// do payment
	// =============================================================================================
	/*if($trans_info['data']['trans_id']){
		$s = curl_init();
		$headers = array(
			 'Content-Type: application/x-www-form-urlencoded',
			 'Key: '.$merchant_key,
			 'Auth-Token: '.$auth_token_info['data']['Auth_Token']
		);
		curl_setopt($s, CURLOPT_URL, $url."/payment/payment");
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		
		// build up the data
		$data = array();
		$data['time'] = $time;
		$data['user_qr_code'] = $_REQUEST['user_qr_code'];
		$data['integrator_id'] = 3;
		$data['amount'] = 0.01;
		$data['trans_id'] = $trans_info['data']['trans_id'];
		$data['sign'] = getSign($data, $api_sign_key);
		
		// get Auth Token
		curl_setopt($s, CURLOPT_POST, true);
		curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($data));
		$ret = curl_exec($s);
		curl_close($s);
		print_r($ret);
	}else print_r($trans_info['data']['msg']);*/

	print "<br>===============================================<br>Done";
}

function getSign($compare_arr=array(), $api_sign_key){
	if(!$compare_arr || !$api_sign_key) return;
	
	$compare_key = array_keys($compare_arr);
	sort($compare_key);
	$compare_sign = $request_sign = $request_time = '';
	foreach ( $compare_key as $api_key => $api_value ){
		if ( $api_value == 'sign' ){
			$request_sign = $compare_arr[$api_value] ;
		}elseif ( $api_value == 'time' ){
			$request_time = $compare_arr[$api_value] ;
		}else{
			$compare_sign .= $compare_arr[$api_value] ;
		}
	}
	$compare_sign .= $request_time . $api_sign_key;
	$sign = md5($compare_sign);
	return $sign;
}


// fix version 196 and above member points issue by justin
function fix_196_member_points(){
	global $con;
	
	$q1 = $con->sql_query("select * from pos where date >= '2019-03-27' and member_no is not null and member_no != '' and floor(amount) != point");
	print "Found ".$con->sql_numrows($q1)." records, processing...\n";
	$upd_count = 0;
	
	while($r = $con->sql_fetchassoc($q1)){
		// select items
		$q2 = $con->sql_query("select * from pos_items where pos_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])." and counter_id = ".mi($r['counter_id'])." and date = ".ms($r['date']));
		
		$ttl_points = 0;
		while($r1 = $con->sql_fetchassoc($q2)){
			$nett_price = $r1['price'] - $r1['discount'] - $r1['discount2'];
			//print $nett_price."\n";
			$member_points_setting = array();
			$member_points_setting = unserialize($r1['member_point']);			
			
			// wrong calculation for category discount will have different in between nett price with before tax price
			if($nett_price != $r1['before_tax_price'] || (!$member_points_setting['settings'] && $member_points_setting['point'] < 0)){
				if($member_points_setting['settings']){
					$point_ratio = $member_points_setting['settings'];
					$member_points_setting['amount'] = strval($nett_price);
					$member_points_setting['point'] = strval($nett_price / $point_ratio);
				}else $member_points_setting['point'] = 0;
				
				// update pos items
				$upd = array();
				$upd['before_tax_price'] = $nett_price;
				$upd['member_point'] = serialize($member_points_setting);
				
				$q3 = $con->sql_query("update pos_items set ".mysql_update_by_field($upd)." where pos_id = ".mi($r1['pos_id'])." and branch_id = ".mi($r1['branch_id'])." and counter_id = ".mi($r1['counter_id'])." and date = ".ms($r1['date'])." and id = ".mi($r1['id']));
			}
			$ttl_points += $member_points_setting['point'];
		}
		$con->sql_freeresult($q2);
		
		// found pos having different points with calculated points from itemise
		if($ttl_points != $r['point']){
			$upd = array();
			$upd['point'] = floor($ttl_points);
			$q2 = $con->sql_query("update pos set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])." and counter_id = ".mi($r['counter_id'])." and date = ".ms($r['date']));
			
			if($con->sql_affectedrows($q2) > 0){
				$con->sql_query("delete from tmp_membership_points_trigger where card_no = ".ms($r['member_no']));
				$upd_count++;
			}
		}
	}
	$con->sql_freeresult($q1);
	
	print "Total ".$upd_count." being updated.\n";
}

function link_huaho_deleted_sku($arg){
	global $con, $config, $pos_config;

	$filters = array();
	$dummy = array_shift($arg);
	$branch_list = array();
	$is_run = false;
	
	while($cmd = array_shift($arg)){
		list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
				
		if($cmd_head == "-branch"){
			$branch_filter = 'where active=1';
			if($cmd_value == 'all'){
				if(!$config['single_server_mode']){
					$bcode = BRANCH_CODE;
					$branch_filter .= ' and code='.ms($bcode);
				}
			}else{
				$bcode_list = array_map("ms", explode(",", trim($cmd_value)));
				$branch_filter .= ' and code in ('.join(",", $bcode_list).")";
			}

			$con->sql_query("select id,code,description from branch $branch_filter order by sequence,code");
			while($r = $con->sql_fetchassoc()){
				if(isset($config['sales_report_branches_exclude']) && is_array($config['sales_report_branches_exclude']) && in_array($r['code'], $config['sales_report_branches_exclude'])){
					continue;	// this branch no need show in sales report
				}
				$branch_list[$r['id']] = $r;
			}
			$con->sql_freeresult();
		}elseif($cmd_head == '-date'){	// date
			$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
			if(!$tmp)	die("Invalid Date.\n");
			if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
			$date = $tmp;
		}elseif($cmd_head == '-date_from'){	// date_from
			$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
			if(!$tmp)	die("Invalid Date From.\n");
			if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
			$date_from = $tmp;
		}elseif($cmd_head == '-date_to'){	// date_to
			$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
			if(!$tmp)	die("Invalid Date To.\n");
			if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
			$date_to = $tmp;
		}elseif($cmd_head == '-recent_day'){	// use recently day
			$num = mi($cmd_value);
			if($num<=0)	die("Recent Day must more than zero.\n");
			
			$date_to = date("Y-m-d", strtotime("-1 day"));
			$date_from = date("Y-m-d", strtotime("-".($num-1)." day", strtotime($date_to)));
		}elseif($cmd_head == '-is_run'){	// force regenerate
			$is_run = true;
		}
	}
	
	if(!$branch_list)	die("Please provide -branch=\n");
	
	foreach($branch_list as $bid => $b){
		print "Branch ID#$bid, ".$b['code']."\n";

		$last_date = '';
		$total_day = 0;
		$affected_count = 0;
		$filter = array();
		$filter[] = "p.branch_id=$bid";
		if($date)	$filter[] = "p.date=".ms($date);
		elseif($date_from && $date_to)	$filter[] = "p.date between ".ms($date_from)." and ".ms($date_to);
		
		$str_filter = "where ".join(' and ', $filter);
		
		$sql = "select pi.branch_id, pi.counter_id, pi.date, pi.pos_id, pi.item_id, pi.sku_item_id, t.old_sku_item_id, t.new_sku_item_id
			from pos_items pi 
			join pos p on p.branch_id=pi.branch_id and p.counter_id=pi.counter_id and p.date=pi.date and p.id=pi.pos_id
			join tmp_huaho_sku_map t on t.old_sku_item_id=pi.sku_item_id
			$str_filter
			order by p.date, p.counter_id, p.id, pi.item_id";
		//print "$sql\n";
		$q1 = $con->sql_query($sql);
		$total_row = $con->sql_numrows($q1);
		while($r = $con->sql_fetchassoc($q1)){
			$d = $r['date'];
			if($last_date && $last_date != $d){
				if($affected_count>0){
					//print "Finalise\n";	
					update_sales_cache($bid, $last_date);
				}
			}
			
			if($is_run){
				$con->sql_query("update pos_items set sku_item_id=".$r['new_sku_item_id']." where branch_id=$r[branch_id] and counter_id=$r[counter_id] and date=".ms($r['date'])." and pos_id=$r[pos_id] and item_id=$r[item_id] and sku_item_id=$r[old_sku_item_id]");
				$affected_count += $con->sql_affectedrows();
			}
			
			if($last_date != $d){
				print "$d\n";
				$total_day++;
			}
			$last_date = $d;
		}
		$con->sql_freeresult($q1);
		
		if($last_date){
			if($affected_count>0){
				//print "Finalise\n";			
				update_sales_cache($bid, $last_date);
			}
		}
		
		print "$total_row rows.\n";
		print "$total_day days.\n";
	}
}

function test_generate_pdf(){
	require("include/wkhtmltopdf/Command.php");
	require("include/wkhtmltopdf/Image.php");
	require("include/wkhtmltopdf/File.php");
	require("include/wkhtmltopdf/Pdf.php");
	define('DIR_ROOT',realpath(dirname(__file__)).'/');	// full path to the root
	//print_r($_SERVER);exit;
	// make the pdf and return the tmp-filename
	$htmlFile = tempnam('/tmp/', 'html-');
	$pdfFile = tempnam('/tmp/', 'pdf-');

	//file_put_contents($htmlFile, $this->fetch('print.tpl'));
	//print file_get_contents('test_po.html');exit;
	file_put_contents($htmlFile, file_get_contents('test_po.html'));
	//$cmd = "html2ps -i0.5 -f ".DIR_ROOT."/templates/print.css -U $htmlFile | ps2pdf - > $pdfFile";
	//$cmd = "html2ps -i0.5 -U $htmlFile | ps2pdf - > $pdfFile";
	//$cmd = "html2ps -i0.5 test_po.html | ps2pdf - > $pdfFile";
	$cmd = "html2ps -i0.5 test_po.html | ps2pdf - > $pdfFile";
	//$cmd = "html2ps -i0.5 -f ".DIR_ROOT."/templates/html2ps.conf -U $htmlFile | ps2pdf - > $pdfFile";
	//$cmd = "html2ps -i0.5 -b http://".$_SERVER['HTTP_HOST']."/test_po.html | ps2pdf - > $pdfFile";
	//print $cmd;exit;
	system($cmd);
	unlink($htmlFile);
	//return $pdfFile;
	
	header("Content-type: pdf/application");
	header("Content-disposition:inline; filename=test_po.pdf");
	readfile($pdfFile);
	//print "Test";
}

function test_generate_pdf2(){
	//print_r($_SERVER);exit;
	$file_name = "test_po.html";
	$dest_file_name = "test_po.pdf";
	$command = "wkhtmltopdf";
	$pdf_dir = "http://".$_SERVER['HTTP_HOST']."/".$file_name;
	$ex_cmd = "$command -T 0 -B 0 -L 0 -R 0 --disable-javascript $pdf_dir $dest_file_name";
	//print $ex_cmd;
	$output = shell_exec($ex_cmd);
	header("Content-type: pdf/application");
	header("Content-disposition:inline; filename=test_po.pdf");
	readfile($dest_file_name);
}

function test_push_notification(){
	global $appCore;
	
	$payload = $appCore->createPayloadJson("Test Push", "I know how to send push notifications liao!");
	
	/*$mobile_type = 'android';
	$token = 'frdJPCGggXc:APA91bF02zzFUUlKYzSsSAcxBHqiruIo4wVGDcDeY_0CqJ3Icapvq8qxOWoSR_z3n5q1-hfB4I-Ax_nw-EDiK6fHh7gOW2-HhgEuf7Nf8nmZU5EaOuWAT4LBBG7xhJv4KU56qkEVxiJT';
	$success = $appCore->sendMobilePushNotification($mobile_type, $token, $payload);
	if($success){
		print "Android Push Success!<br />";
	}else{
		print "Android Push Failed!<br />";
	}*/
	
	$mobile_type = 'ios';
	$token = '0a76bf8543c25607ee2f28a366a35af2fe4cab51bdbf9a9cbc405294e3bf5f4c';
	$success = $appCore->sendMobilePushNotification($mobile_type, $token, $payload);
	if($success){
		print "IOS Push Success!<br />";
	}else{
		print "IOS Push Failed!<br />";
	}
}

function copy_sku_photo_to_pos_photo($arg){
	global $con, $copied_count, $is_run, $need_copy_count;
	$copied_count = $need_copy_count = 0;
	
	function copy_apply_photo_to_pos_photo($sku_apply_items_id, $source_img){
		global $con, $copied_count, $is_run, $need_copy_count;
		
		$sku_apply_items_id = mi($sku_apply_items_id);
		if($sku_apply_items_id<=0){
			die("sku_apply_items_id = $sku_apply_items_id\n");
			return;
		}
		
		$con->sql_query("select id from sku_items where sku_apply_items_id=$sku_apply_items_id");
		$si = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$sid = mi($si['id']);
		if($sid<=0){
			print ("sku_apply_items_id=$sku_apply_items_id, sid = $sid\n");
			return;
		}
		
		$group_num = ceil($sid/10000);
		$promo_photo_path = "sku_photos/promo_photo/".$group_num."/".$sid."/1.jpg";
		if(file_exists($promo_photo_path))	return;
		
		if($is_run){
			print "Copy SKU APPLY ITEM ID: $sku_apply_items_id, Source: $source_img, Destination: $promo_photo_path\n";
		
			// Check and Create Path Folder
			check_and_create_dir("sku_photos/promo_photo/");
			check_and_create_dir("sku_photos/promo_photo/$group_num");
			check_and_create_dir("sku_photos/promo_photo/$group_num/$sid");
			
			// Copy File
			if(copy($source_img, $promo_photo_path)){
				$copied_count++;
				$upd = array();
				$upd['got_pos_photo'] = 1;
				$upd['lastupdate'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id=$sid");
			}
		}else{
			$need_copy_count++;
		}		
	}
	
	function copy_actual_photo_to_pos_photo($sid, $source_img){
		global $con, $copied_count, $is_run, $need_copy_count;
		
		$sid = mi($sid);
		if($sid<=0){
			print ("sid=$sid\n");
			return;
		}
		
		$group_num = ceil($sid/10000);
		$promo_photo_path = "sku_photos/promo_photo/".$group_num."/".$sid."/1.jpg";
		if(file_exists($promo_photo_path))	return;
		
		if($is_run){
			print "Copy SKU ITEM ID: $sid, Source: $source_img, Destination: $promo_photo_path\n";
		
			// Check and Create Path Folder
			check_and_create_dir("sku_photos/promo_photo/");
			check_and_create_dir("sku_photos/promo_photo/$group_num");
			check_and_create_dir("sku_photos/promo_photo/$group_num/$sid");
			
			// Copy File
			if(copy($source_img, $promo_photo_path)){
				$copied_count++;
				$upd = array();
				$upd['got_pos_photo'] = 1;
				$upd['lastupdate'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id=$sid");
			}
		}else{
			$need_copy_count++;
		}		
	}
	
	$filters = array();
	$dummy = array_shift($arg);
	$dummy = array_shift($arg);
	$is_run = false;
	
	while($cmd = array_shift($arg)){
		list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
				
		if($cmd_head == '-is_run'){	// real run
			$is_run = true;
		}else{
			die("Unknown $cmd");
		}
	}
	
	print "Start\n";
	print "Time: ".date("Y-m-d h:i:s")."\n";
	
	
	// check whether photo got move to use new structure or not
	$use_new_sku_photo_path = file_exists("sku_photos/use_new_sku_photo_path.txt") ? true : false;
	//$use_new_sku_photo_path = false;
	Print "Use NEW SKU Photo Path: ".($use_new_sku_photo_path ? "Yes": "No")."\n";
	
	// Apply Photo
	print "\nApply Photo\n";
	$apply_photo_list = array();
	if($use_new_sku_photo_path){
		$group_folder_list = glob("sku_photos/apply_photo/*", GLOB_ONLYDIR);
		//print_r($group_folder_list);
		if($group_folder_list){
			// Loop Group Folder
			$total_group_count = count($group_folder_list);
			$curr_group_count = 0;
			foreach($group_folder_list as $group_folder){
				$curr_group_count++;
				print "Group: $curr_group_count / $total_group_count\n";
				$folder_list = glob("$group_folder/*", GLOB_ONLYDIR);
				if($folder_list){
					// Loop SKU APPLY ITEM ID folder
					foreach($folder_list as $folder){
						$sku_apply_items_id = mi(basename($folder));
						if($sku_apply_items_id <= 0)	continue;
						
						$source_img = $folder."/1.jpg";
						if(file_exists($source_img)){
							// Got photo - copy it
							copy_apply_photo_to_pos_photo($sku_apply_items_id, $source_img);
						}
					}
				}
			}
		}else{
			print "No Group Folder\n";
		}
	}else{
		// Get All Folder List
		$folder_list = glob("sku_photos/*", GLOB_ONLYDIR);
		//print_r($folder_list);
		if($folder_list){
			$total_count = count($folder_list);
			$curr_count = 0;
			
			// Loop Folder
			foreach($folder_list as $folder){
				$curr_count++;
				print "\r$curr_count / $total_count . . .";
				
				// sku_apply_items_id must be integer
				$folder_name = basename($folder);
				if(!is_numeric($folder_name))	continue;
				//print "Folder Name: ".$folder_name."\n";
				
				// Check only the first jpg
				$source_img = $folder."/1.jpg";
				if(file_exists($source_img)){
					// Got photo - copy it
					$sku_apply_items_id = mi($folder_name);
					copy_apply_photo_to_pos_photo($sku_apply_items_id, $source_img);
				}
			}
			print "\n";
		}else{
			print "No Folder\n";
		}
	}
	
	// Actual Photo
	print "\nActual Photo\n";
	if($use_new_sku_photo_path){		
		$group_folder_list = glob("sku_photos/actual_photo/*", GLOB_ONLYDIR);
		if($group_folder_list){
			// Loop Group Folder
			$total_group_count = count($group_folder_list);
			$curr_group_count = 0;
			foreach($group_folder_list as $group_folder){
				$curr_group_count++;
				print "Group: $curr_group_count / $total_group_count\n";
				$folder_list = glob("$group_folder/*", GLOB_ONLYDIR);
				if($folder_list){
					// Loop SKU ITEM ID folder
					foreach($folder_list as $folder){
						$sid = mi(basename($folder));
						if($sid <= 0)	continue;
						
						$source_img = '';
						foreach  (array_merge(glob("$folder/*.jpg"),glob("$folder/*.JPG"),glob("$folder/*.jpeg"),glob("$folder/*.JPEG")) as $f){
							$source_img = $f;	// Take only one image
							break;
						}
						//print "sid = $sid, source_img = $source_img\n";
						if(file_exists($source_img)){
							// Got photo - copy it
							copy_actual_photo_to_pos_photo($sid, $source_img);
						}
					}
				}
			}
		}else{
			print "No Group Folder\n";
		}
	}else{
		// $abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/a/".$sku_item_id."/";
		$folder_list = glob("sku_photos/a/*", GLOB_ONLYDIR);
		if($folder_list){
			$total_count = count($folder_list);
			$curr_count = 0;
			
			// Loop Folder
			foreach($folder_list as $folder){
				$curr_count++;
				print "\r$curr_count / $total_count . . .";
				
				// sku_apply_items_id must be integer
				$sid = mi(basename($folder));
				if($sid<=0)	continue;
				
				$source_img = '';
				foreach  (array_merge(glob("$folder/*.jpg"),glob("$folder/*.JPG"),glob("$folder/*.jpeg"),glob("$folder/*.JPEG")) as $f){
					$source_img = $f;	// Take only one image
					break;
				}
				//print "sid = $sid, source_img = $source_img\n";
				if($source_img){
					copy_actual_photo_to_pos_photo($sid, $source_img);
				}
			}
			print "\n";
		}else{
			print "No Folder\n";
		}
	}
	
	print "\nTotal Copied: $copied_count\n";
	print "Need Copy: $need_copy_count\n";
	print "Finished\n";
	print "Time: ".date("Y-m-d h:i:s")."\n";
	
	print "Done.\n";
}

function fix_promotion_photo_path($arg){
	global $con;
	
	$filters = array();
	$dummy = array_shift($arg);
	$dummy = array_shift($arg);
	$is_run = false;
	
	while($cmd = array_shift($arg)){
		list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
				
		if($cmd_head == '-is_run'){	// real run
			$is_run = true;
		}else{
			die("Unknown $cmd");
		}
	}
	
	print "Start\n";
	print "Time: ".date("Y-m-d h:i:s")."\n";
	$fixed_count = $need_fix_count = 0;
	
	$group_folder_list = glob("sku_photos/promo_photo/*", GLOB_ONLYDIR);
	//print_r($group_folder_list);
	if($group_folder_list){
		// Loop Group Folder
		$total_group_count = count($group_folder_list);
		$curr_group_count = 0;
		foreach($group_folder_list as $group_folder){
			$curr_group_count++;
			print "Group: $curr_group_count / $total_group_count\n";
			$folder_list = glob("$group_folder/*", GLOB_ONLYDIR);
			if($folder_list){
				//print_r($folder_list);
				// Loop SKU ITEM ID folder
				foreach($folder_list as $folder){
					$sid = mi(basename($folder));
					if($sid <= 0)	continue;
					
					$need_fix = false;
					$got_img = false;
					
					// Get sku_items
					$con->sql_query("select got_pos_photo from sku_items where id=$sid");
					$si = $con->sql_fetchassoc();
					$con->sql_freeresult();
					if(!$si)	continue;	// item not found
					
					if(file_exists($folder."/1.jpg")){
						$got_img = true;
						if(!$si['got_pos_photo']){
							$need_fix = true;
						}
						//continue;	// Already have file, no need fix
					}else{
						if($si['got_pos_photo']){
							$need_fix = true;
						}
					}
					
					$source_img = '';
					if(!$got_img){
						// check got other image name
						foreach  (array_merge(glob("$folder/*.jpg"),glob("$folder/*.JPG"),glob("$folder/*.jpeg"),glob("$folder/*.JPEG")) as $f){
							$source_img = $f;	// Take only one image
							break;
						}
						if(!$source_img){
							//continue;	// no photo
						}else{
							$need_fix = true;
						}
					}
					
					if(!$need_fix)	continue;	// nothing to fix
					
					print "sid = $sid\n";
					
					if($is_run){
						$upd = array();
						$upd['lastupdate'] = 'CURRENT_TIMESTAMP';
						
						if($source_img){
							rename($source_img, $folder."/1.jpg");
							
							$upd['got_pos_photo'] = 1;
						}else{
							$upd['got_pos_photo'] = $got_img ? 1 : 0;
						}
						$con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id=$sid");
						$fixed_count++;
					}else{
						$need_fix_count++;
					}
				}
			}
		}
	}else{
		print "No Group Folder\n";
	}
	
	print "\nTotal Fixed: $fixed_count\n";
	print "Need Fix: $need_fix_count\n";
	print "Finished\n";
	print "Time: ".date("Y-m-d h:i:s")."\n";
	
	print "Done.\n";
}

function convert_innodb(){
	global $con;
	
	// alter all sales cache and stock balance to InnoDB
	/*$tbl_list = array("sku_items_sales_cache_b", "category_sales_cache_b", "member_sales_cache_b", "pwp_sales_cache_b", "dept_trans_cache_b", "sales_target_b", "daily_sales_cache_b", "sa_sales_cache_b", "sa_range_sales_cache_b", "stock_balance_b");
	foreach($tbl_list as $tbl_name){
		$q1 = $con->sql_query_false("show tables like '".$tbl_name."%'", true);
		while($t = $con->sql_fetchrow($q1)){
			$table = $t[0];
			if(!$table) continue;
			print "Altering $table...";
			$con->sql_query_false("ALTER TABLE $table ENGINE=InnoDB", true);
			print "Done.\n";
		}
		$con->sql_freeresult($q1);
	}
	unset($tbl_list);*/
	
	$tbl_list = array("log", "log_vp", "log_dp");
	foreach($tbl_list as $tbl_name){
		print "Altering $tbl_name... ";
		// need to drop fulltext index
		$q1 = $con->sql_query("show index from $tbl_name where Index_type='FULLTEXT'");
		while($r = $con->sql_fetchassoc($q1)){
			// Drop index
			$con->sql_query("ALTER TABLE $tbl_name drop index ".$r['Key_name']);
			$con->sql_query("ALTER TABLE $tbl_name add index ".$r['Column_name']." (".$r['Column_name']."(200))");
		}
		$con->sql_freeresult($q1);
		
		$con->sql_query("ALTER TABLE $tbl_name modify id int not null default 0");
		$con->sql_query("ALTER TABLE $tbl_name ENGINE=InnoDB");
		print "Done.\n";
	}
	unset($tbl_list);
}

function generate_membership_guid(){
	global $con, $appCore; 
	
	print "Start\n";
	$updated_count = 0;
	$q1 = $con->sql_query("select nric from membership where membership_guid='' ");
	if($con->sql_numrows($q1) > 0){
		
		while($r = $con->sql_fetchassoc($q1)){
			$upd = array();
			$upd['membership_guid'] = $appCore->newGUID();
			$nric = $r['nric'];
			$con->sql_query("update membership set ".mysql_update_by_field($upd)." where nric=".ms($r['nric']));
			$con->sql_query("update membership_history set ".mysql_update_by_field($upd)." where nric=".ms($r['nric']));
			$con->sql_query("update membership_points set ".mysql_update_by_field($upd)." where nric=".ms($r['nric']));
			
			$updated_count++;
		}
		
	}
	$con->sql_freeresult($q1);
	
	print "$updated_count membership_guid updated.\n";
	print "Done.\n";
}

function test_email_now(){
	global $config, $con, $appCore;
	
	$email_address = trim($_REQUEST['email']);
	if(!$email_address)	die('What is your email address.');
	$smtp_mail_settings = $config['smtp_mail_settings'];
	//unset($config['smtp_mail_settings']);
	
	include_once("include/class.phpmailer.php");
	
    $mailer = new PHPMailer(true);
    //$mailer->From = "noreply@arms.com.my";
    $mailer->FromName = "ARMS Notification";
    $mailer->Subject = "ARMS TESTING EMAIL";
    $mailer->IsHTML(true);
	//$mailer->SMTPSecure = 'tls';
	//$mailer->Port = 587;
	$mailer->SMTPDebug = 1;
	$mailer->SMTPKeepAlive = 1;
	
	//$mailer->isSMTP();
	//$mailer->Host = trim($smtp_mail_settings['host']);
	//$mailer->Username = 'arms';
	//$mailer->Password = trim($smtp_mail_settings['pass']);
	
	//if($smtp_mail_settings['from'])	$mailer->From = $smtp_mail_settings['from'];
	//else	$mailer->From = $smtp_mail_settings['username'];
	
	//if(isset($smtp_mail_settings['port']))	$mailer->Port = $smtp_mail_settings['port'];
	//if(isset($smtp_mail_settings['SMTPSecure']))	$mailer->SMTPSecure = $smtp_mail_settings['SMTPSecure'];
	//if(isset($smtp_mail_settings['SMTPAuth']))	$mailer->SMTPAuth = true;
			
    //$mailer->IsMail();
	//$mailer->SMTPDebug=1;
	//print_r($mailer);
	
	// gmail thingy...
	/*$mailer->CharSet = 'UTF-8';
	$mailer->IsSMTP(); // enable SMTP
	//$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
	$mailer->SMTPAuth = true; // authentication enabled
	$mailer->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for GMail
	$mailer->Host = "smtp.gmail.com";
	$mailer->Port = 587; // or 587

	$mailer->Username = "weboxcms@gmail.com";
	$mailer->Password = "webox123";

	$mailer->CharSet = 'UTF-8';
	$mailer->IsSMTP(); // enable SMTP
	//$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
	$mailer->SMTPAuth = true; // authentication enabled
	$mailer->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for GMail
	$mailer->Host = "smtp.mandrillapp.com";
	$mailer->Port = 587; // or 587
	
	$mailer->Username = "noreply@arms.com.m";
	$mailer->Password = "BoMz1MvZlN2aJ_xPPZk3dw";
	*/
	
	//$mailer->addReplyTo('justin@arms.my', 'Roti');

	//$mailer->AddEmbeddedImage('ui/google_play_badge/128x128.png', 'google_play_badge');


    $email_msg_sample = "<h2><u>ARMS Testing Email</u></h2>";
	
	//$encoded_comp_logo = base64_encode(file_get_contents("ui/google_play_badge/128x128.png"));
	//print $encoded_comp_logo;exit;
	$email_msg_sample .= "Google: <a href='".$_SERVER['HTTP_HOST']."/membership.eform.php'><img src=\"maximus.ddns.my:2001/ui/google_play_badge/128x128.png\" height='128' width='128' alt='Get from Google Play' /></a>";
	$mailer->AddAddress($email_address);
	//$mailer->AddAddress('tommy_lts@yahoo.com');
	//$mailer->AddAddress('tommy@arms.my');
	$mailer->AddAddress("chingharn@hotmail.com");
	//$mailer->AddAddress("nava@arms.my");
    $mailer->Body = $email_msg_sample;
    // send the mail
	
    print "send email to $email_address ";
	print "<pre>";
	print_r($mailer);
	print "</pre>";
    try {
		if($mailer->Send()){
			print "OK";
		}else{
			$ret['err'] = 'EMAIL_SENDING_UNKNOWN_ERROR';
		}
	} catch (phpmailerException $e) {
		$ret['err'] = $e->errorMessage(); //Pretty error messages from PHPMailer
	} catch (Exception $e) {
		$ret['err'] = $e->getMessage(); //Boring error messages from anything else!
	}
	
	if($ret['err']){
		print "Error: ".$ret['err'];
	}

	$mailer->ClearAddresses();
}

function update_pos_membership_guid(){
	global $con, $config, $arg, $appCore;
	
	$bid_list = array();
	while($cmd = array_shift($arg)){
		list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
		if($cmd_head == "-branch"){
			$branch_filter = 'where active=1';
			if($cmd_value == 'all'){
				if(!$config['single_server_mode']){
					$bcode = BRANCH_CODE;
					$branch_filter .= ' and code='.ms($bcode);
				}
			}else{
				$bcode_list = array_map("ms", explode(",", trim($cmd_value)));
				$branch_filter .= ' and code in ('.join(",", $bcode_list).")";
			}
			
			//get branch list 
			$con->sql_query("select id,code from branch $branch_filter order by sequence,code");
			while($r = $con->sql_fetchassoc()){
				$bid_list[] = $r;
			}
			$con->sql_freeresult();
		}elseif($cmd_head == "-date"){
			$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
			if(!$tmp)	die("Invalid Date.\n");
			$date = $tmp;
		}elseif($cmd_head == '-date_from'){
			$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
			if(!$tmp)	die("Invalid Date From.\n");
			$date_from = $tmp;
		}elseif($cmd_head == '-date_to'){
			$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
			if(!$tmp)	die("Invalid Date To.\n");
			$date_to = $tmp;
		}
	}
	
	//checking if no branch
	if(!$bid_list)	die("Please provide -branch=\n");
	
	//checking if no date from or date to
	if($date_from || $date_to){
		if($date_from && !$date_to)  die("Please provide -date_to=\n");
		elseif(!$date_from && $date_to)  die("Please provide -date_from=\n");
	}
	
	print "Start\n";
	foreach($bid_list as $b){	//update by branch
		print "\n";
		print "Branch: ".$b['code']."\n";
		print "Branch ID: ".$b['id']."\n";
	
		$bid = mi($b['id']);
		$filter = $date_list = array();
		$filter[] =	"pos.member_no <> ''";
		$filter[] =	"pos.membership_guid = ''";
		$filter[] = "pos.branch_id = $bid";
		
		if($date)  $filter[] = "pos.date=".ms($date);
		elseif($date_from && $date_to)  $filter[] = "pos.date between ".ms($date_from)." and ".ms($date_to);
		else{  //update 30 day pos membership_guid 
			$limit = "limit 30";
		}
		
		$str_filter = "where ".join(' and ', $filter);
		$q1 = $con->sql_query("select distinct pos.date from pos $str_filter order by pos.date desc $limit");
		while($r=$con->sql_fetchassoc($q1)){	//get pos date
			$date_list[] = $r['date'];
		}
		$con->sql_freeresult($q1);

		$updated_count = 0;
		foreach($date_list as $update_date){
			$upd_date = $update_date;
			$updated = $appCore->posManager->update_pos_membership_guid($bid, $upd_date);
			$updated_count+= $updated;
		}
		print "Total: $updated_count rows updated.\n";
		print "\n";
	}
	print "Done.\n";
}

function generate_sku_items_finalised_cache(){
	global $con, $appCore, $arg;
	
	$bid_list = array();
	while($cmd = array_shift($arg)){
		list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
		if($cmd_head == "-branch"){
			$branch_filter = 'where active=1';
			if($cmd_value == 'all'){
				//if(!$config['single_server_mode']){
				//	$bcode = BRANCH_CODE;
				//	$branch_filter .= ' and code='.ms($bcode);
				//}
			}else{
				$bcode_list = array_map("ms", explode(",", trim($cmd_value)));
				$branch_filter .= ' and code in ('.join(",", $bcode_list).")";
			}
			
			//get branch list 
			$con->sql_query("select id,code from branch $branch_filter order by sequence,code");
			while($r = $con->sql_fetchassoc()){
				$bid_list[] = $r;
			}
			$con->sql_freeresult();
		}elseif($cmd_head == "-date"){
			$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
			if(!$tmp)	die("Invalid Date.\n");
			$date = $tmp;
		}elseif($cmd_head == '-date_from'){
			$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
			if(!$tmp)	die("Invalid Date From.\n");
			$date_from = $tmp;
		}elseif($cmd_head == '-date_to'){
			$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
			if(!$tmp)	die("Invalid Date To.\n");
			$date_to = $tmp;
		}
	}
	$limit_day = 30;
	$total_updated_count = 0;
	
	print "Start\n";
	foreach($bid_list as $b){	//update by branch
		print "\n";
		print "Branch: ".$b['code']."\n";
		print "Branch ID: ".$b['id']."\n";
	
		$bid = mi($b['id']);
		$date_list = array();
		$filter = array();
		
		$tbl = "sku_items_sales_cache_b".$bid;
		
		
		if($date)  $filter[] = "tbl.date=".ms($date);
		elseif($date_from && $date_to)  $filter[] = "tbl.date between ".ms($date_from)." and ".ms($date_to);
		elseif($date_from)  $filter[] = "tbl.date >= ".ms($date_from);
		elseif($date_to)  $filter[] = "tbl.date <= ".ms($date_to);
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		$q1 = $con->sql_query("select distinct tbl.date from $tbl tbl $str_filter order by tbl.date desc $limit");
		while($r=$con->sql_fetchassoc($q1)){	//get pos date
			// Check already got data or not
			$q2 = $con->sql_query("select * from sku_items_finalised_cache where branch_id=$bid and date=".ms($r['date'])." limit 1");
			$tmp = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			if(!$tmp){
				// No data in cache, need generate
				$date_list[] = $r['date'];
				
				// max 30 days per run
				if(count($date_list) >= $limit_day)	break;
			}
		}
		$con->sql_freeresult($q1);

		print_r($date_list);
		$updated_count = 0;
		if($date_list){
			foreach($date_list as $update_date){
				// insert sku item finalised cache
				$con->sql_begin_transaction();
				$updated = $appCore->posManager->generateSKUItemFinalisedCache($bid, $update_date);
				$con->sql_commit();
				if($updated){
					$updated_count++;
					$total_updated_count++;
				}
				
			}
		}
		
		print "Branch: $updated_count day updated.\n";
		print "\n";
	}
	print "Total: $total_updated_count day updated.\n";
	print "Done.\n";
}

function delele_wls_member(){
	global $con, $appCore, $arg;
		
	$keep_bid = 13; // TPC
	exit;	// stop here in case wrongly call next time
	$con->sql_begin_transaction();
	
	$con->sql_query("truncate membership_points");
	$con->sql_query("truncate membership_receipt");
	$con->sql_query("truncate membership_receipt_items");
	$con->sql_query("truncate membership_redemption");
	$con->sql_query("truncate membership_redemption_items");
	$con->sql_query("truncate membership_redemption_sku");
	$con->sql_query("truncate memberships_notice_board_items");
	$con->sql_query("truncate memberships_otp");
	$con->sql_query("truncate memberships_pn");
	$con->sql_query("truncate memberships_pn_items");
	$con->sql_query("truncate memberships_purchased_package");
	$con->sql_query("truncate memberships_purchased_package_items");
	$con->sql_query("truncate memberships_purchased_package_items_redeem");
	$con->sql_query("truncate memberships_purchased_package_log");
	$con->sql_query("truncate memberships_push_notification_history");
	
	$con->sql_query("delete from membership where apply_branch_id<>$keep_bid");
	$con->sql_query("update membership set points=0");
	$con->sql_query("truncate membership_extra_info");
	$con->sql_query("truncate membership_fav_items");
	$con->sql_query("truncate membership_history");
	$con->sql_query("truncate membership_inventory_history");
	$con->sql_query("truncate membership_isms");
	$con->sql_query("truncate membership_isms_items");
	$con->sql_query("truncate membership_mobile_ads_banner");
	$con->sql_query("truncate membership_package");
	$con->sql_query("truncate membership_package_items");
	$con->sql_query("truncate memberships_referral_history");
			
	$con->sql_commit();
		
	print "\nDone.\n";
}
?>