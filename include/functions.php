<?php
/*
2/9/2011 11:34:55 AM Andy
- Add function initial_branch_sb_table()

3/22/2011 6:19:20 PM Andy
- Move get_sku_item_photos() and get_branch_file_url() to functions.php

3/25/2011 1:56:10 PM Andy
- Add function get_pos_settings_value() and get_membership_card_prefix()

3/30/2011 11:38:17 AM Andy
- Add function parse_privilege_manager_ini() and config_master_override()

4/8/2011 10:40:57 AM Andy
- Added new function to take latest cost and selling price.

4/11/2011 3:01:23 PM Alex
- add encrypt_for_verification() function for voucher and coupon verification code module

4/11/2011 3:48:03 PM Andy
- Fix get_sku_item_cost_selling() checking on wrong array value.

4/14/2011 5:37:27 PM Andy
- move function get_sku_apply_item_photos() to functions.php
- Add checking for sku photo path and change path to show the image. use_new_sku_photo_path() and get_image_path()

4/27/2011 5:05:53 PM Andy
- Add can get trade_discount_code from function get_sku_item_cost_selling().

5/11/2011 5:50:58 PM Andy
- Add checking for stock balance table exists or not before create table statement. 

5/17/2011 12:01:39 PM Andy
- Add new function check_and_create_dir($dir)

5/20/2011 10:01:17 AM Andy
- Fix branch cannot load master file SKU due to wrong config checking.
- Add new function load_region_branch_array()

5/23/2011 6:15:57 PM Andy
- Fix sku loading path.

6/1/2011 10:43:54 AM Andy
- Add new function branch_change_sku_region_selling($bid, $sid)
- Add new function check_image_exists($imgname)

6/24/2011 4:22:00 PM Andy
- Make all branch default sort by sequence, code.

7/5/2011 3:36:16 PM Andy
- Fix config override bugs for type "select".

7/7/2011 2:01:29 PM Andy
- Change SKU Master File to load photo from ajax if found multi server mode and photo is not store at own server.

8/5/2011 5:20:52 PM Andy
- Add function validate_discount_format() and get_discount_amt

8/19/2011 5:32:32 PM Justin
- Fixed the bugs when generating branch file URL for multiple server mode.

9/6/2011 11:24:34 AM Andy
- Add new function is_using_terminal()

9/7/2011 12:59:45 PM Andy
- Change minimum stock balance table year from 2000 to 2007.

11/17/2011 3:59:00 PM Andy
- Fix config_master_override() not to set zero value but to unset the config if found config has been disabled. (fix bugs in JS)

1/30/2012 5:32:45 PM Alex
- add get_sku_items_details to get sku items details by barcode
- add $config['pos_check_barcode'] to filter check barcode

1/31/2012 10:06:40 AM Andy
- Add new function get_cat_tree_info($cat_id, $tree_str = '') and get_sku_sales_trend($bid, $sid)

2/13/2012 4:41:10 PM Andy
- Add new function load_branch_list()
- Add get category discount by branch info for function get_category_info()

2/15/2012 2:39:04 PM Alex
- add new function is_ajax() to check is ajax call

2/29/2012 4:25:17 PM Alex
- add get_grn_barcode_info() to check grn barcode

3/9/2012 12:59:43 PM Andy
- Add new function write_process_status($modulename, $taskname, $statusname, $status, $is_clear = false)

3/26/2012 11:43:03 AM Andy
- Move a lot of function from common.php to functions.php
- Change when update sales cache getting last grn vendor method.

3/27/2012 12:18:30 PM Andy
- Modify function load_region_branch_array() to accept parameter "inactive_change_price", so it will return those branch which need to change price.

3/29/2012 3:37:43 PM Justin
- Added new function to send notification by sms to user when found config on & user having phone no.

4/9/2012 3:15:16 PM Andy
- Change function get_lowest_price() at sales order to check for sku/category discount by branch/global.

4/19/2012 2:48:20 PM Andy
- Add new function create_blank_approval_data($params, $mysql_connection = '')

4/23/2012 4:39:02 PM Andy
- Add when finalize counter collection will exclude those write-off SKU from trade in.

7/13/2012 5:42 PM Andy
- Add new function log_vp()

8/7/2012 5:54 PM Justin
- Enhanced get grn barcode function to accept barcode prefix scans.

9/5/2012 11.04AM Drkoay
-remove comment in function load_region_branch_array to accept inactive_change_price

9/6/2012 4:16 PM Andy
- Add new function fix_terminal_smarty()

9/13/2012 4:10 PM Justin
- Added new function member_points_changed()

9/25/2012 2:08:00 PM Fithri
- when add new branch, all the per-branch settings (eg: selling price, block po, discount %, point) can copy from other branch

11:59 AM 10/3/2012 Justin
- Added new function get_vp_sales_report_profit_by_date() and get_vp_sales_bonus_per().

10/8/2012 3:46 PM Justin
- Added new function get_member_type().

10/17/2012 5:27 PM Andy
- Add new function get_branch_counter_name().

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

11/15/2012 12:03 PM Andy
- Add new function get_vp_global_si_info_list() and get_vp_global_cat_info_list().
- Enhance function get_vp_sales_report_profit_by_date() to accept 3rd params and return % by sku or category.

12/17/2012 5:16 PM Justin
- Enhanced Vendor Portal to use previous month's bonus if found do not have for current month.

1/24/2013 2:51 PM Andy
- Modified some sendmail code to compatible with new phpmailer.

1/25/2013 10:33 AM Andy
- Modified get_vp_sales_report_profit_by_date() to return an array contain type, value and per.
- Add new function got_vp_sales_bonus_set()

2/5/2013 11:42 AM Andy
- Fix wrong primary key on new created member_sales_cache table.

4/3/2013 5:21 PM Andy
- Add new function log_dp().

5/14/2013 11:42 AM Andy
- fix function get_lowest_price() cannot get category discount.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/8/2013 4:44 PM Justin
- Enhanced to add new table "gra" to send pm for next approver.

7/12/2013 11:25 PM Justin
- Enhanced to generate table "return_policy_sales_cache".

07/23/2013 03:39 PM Justin
- Enhanced to add new function "relocate_sn_pos_info".

7/24/2013 2:41 PM Andy
- Enhance check approval flow to check min document amount.

07/31/2013 10:04 AM Justin
- Enhanced to add new function "generate_pos_cashier_finalize".

9/4/2013 3:18 PM Andy
- Remove script manually assign noreply@localhost for mailer->From.

10/2/2013 3:19 PM Andy
- Change function is_using_terminal() to check using php_sapi_name()

10/16/2013 4:24 PM Fithri
- if search value is 13 chars (armscode/mcode/linkcode/artno) if not found then use only the first 12 character - this is because barcoder generate 1 extra character

10/1/2013 6:01 PM Fithri
- make all email field not compulsary
- when sending email, check to trigger send function only when email is set

10/18/2013 4:09 PM Andy
- Add new function phpmailer_send() to catch error when send email, use to prevent script termination when call $mailer->send().
- Change all $mailer->send() to to call phpmailer_send().

11/5/2013 2:56 PM Justin
- Enhanced to have offline connect and validations for pending documents.

11/6/2013 4:10 PM Andy
- Enhance the send_pm2() to check config "main_server_url" and if found will provide both the link (LAN/WAN) for user in email.

12/2/2013 3:19 PM Andy
- Enhance the multi server mode to allow some sub-branch to working in HQ server.

12/2/2013 5:30 PM Justin
- Enhanced to take off config checking on send_pm2.

12/17/2013 3:19 PM Andy
- change get_branch_file_url() to return server ip when using multi server mode.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

1/14/2014 3:44 PM Andy
- Remove clear_drawer=1 checking for pos_cash_domination.

2/26/2014 10:53 AM Fithri
- includes mprice, qprice & mqprice when copy selling price in Masterfile Branch

3/7/2014 5:35 PM Justin
- Added new function get_counter_info.

3/21/2014 10:07 AM Justin
- Bug fixed on sms notification shows error even no ID and password from config is assigned.

4/3/2014 11:39 AM Andy
- Add new function calculate_duration_by_second()

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

6/9/2014 11:10 AM Justin
- Enhanced to accept to load "JPEG" image file.

7/8/2014 2:20 PM Justin
- Bug fixed on grn barcode validation that return wrong result.

1/28/2015 11:25 AM Andy
- Enhance the generate sales cache to generate service charge cache as well
- Add function "process_gst_sp_rounding_condition", "get_sku_gst", "get_category_gst", "get_gst_settings", "check_gst_status", "construct_gst_list", "check_and_extend_gst_list", "MYR_rounding", "get_special_exemption_gst".

3:54 PM 1/30/2015 Andy
- Add own category must need to recalculate when got category changed.

2/16/2015 3:59 PM Andy
- Enhance to capture daily sales data cache.
- Move the rebuild category cache function (build_category_cache, sync_cat_inheritance, sync_cat_inheritance_using_id) to functions.php

3/5/2015 11:54 AM Andy
- Enhanced check_gst_status() function to accept new params 'check_only_need_active' which will only perform simple gst checking.

3/6/2015 3:24 PM Andy
- Added new function "is_using_force_zero_rate_before_start_date".
- Enhance function "construct_gst_list", "get_category_gst" and "get_sku_gst" to get params array. and check the variable "no_check_use_zero_rate" to determine whether to use force zero rate before start date.

3/24/2015 10:15 AM Andy
- Change to only update sku items cost when category change is fresh market.

3/26/2015 11:54 AM Andy
- Move function "get_global_gst_settings" to functions.php
- Enhance function "is_using_force_zero_rate_before_start_date" to check terminal info when sessioninfo is not found.

3/26/2015 4:02 PM Justin
- Bug fixed on GST info from category_cache will not update if it is not fresh market.

3/28/2015 10:59 AM Andy
- Fix the create sku/category sales cache table query.

4/3/2015 3:37 PM Andy
- Change to update sku items cost when category change "no inventory".

4/23/2015 12:11 PM Andy
- Fix regen fresh market category cache din't get gst and discount.

5/5/2015 3:33 PM Andy
- Added function "split_receipt_ref_no".

5/7/2015 5:12 PM Justin
- Bug fixed on calculate gst selling price do not round up to 2 decimal points.

5/13/2015 2:13 PM Justin
- Bug fixed on copy selling price will cause mysql errors.

5/25/2015 4:03 PM Justin
- Enhanced to auto update SKU items to use master selling price if found update from existing region to another.

6/19/2015 2:49 PM Eric
- Enhanced to show by items and sales agent, add new function get_pos_by_receipt_ref_no()

7/24/2015 11:05 AM Joo Chia
- Added functions "copy_trade_discount_by_branch", "copy_approval_flow_by_branch", and "copy_block_grn_by_branch".

8/6/2015 10:35 AM Joo Chia
- Added functions "get_sku_items_stock_balance" to get sku item stock balance.

8/25/2015 10:49 AM Andy
- Fix update_sales_cache to only write to process file when params was provided.

10/21/2015 4:31 PM Andy
- Enhanced the send pm to support cnote.

11/23/2015 10:30 AM Qiu Ying
- Added function "resize_photo"

12/23/2015 5:11 PM Andy
- Enhanced send_pm2 to have custom_from.

05/31/2016 15:00 Edwin
- Removed "return_policy_sales_cache".

7/5/2016 4:17 PM Andy
- Added function "get_vendor_info".

12/13/2016 5:47 PM Andy
- Enhanced get_cat_tree_info() to get category code.

1/23/2017 5:11 PM Andy
- Add new function is_exceed_max_mysql_timestamp()

2/3/2017 4:32 PM Andy
- Fixed get_pos_by_receipt_ref_no() should not mi() the ref_no.

3/3/2017 4:32 PM Andy
- Enhanced to delete pos_finalised_error when finalise/unfinalise counter collection.

4/27/2017 3:38 PM Justin
- Enhanced to use cash_domination_notes from config instead of pos_config.

7/17/2017 1:14 PM Andy
- Fixed generate_pos_cashier_finalize() not to get adjusted pos_payment.
- Changed daily sales cache deposit calculation. (not sure did by who)

2017-08-23 16:13 PM Qiu Ying 
- Bug fixed on showing wrong variance

4:52 PM 9/6/2017 Justin
- Added new function "get_matrix_color" and "get_matrix_size".

2017-09-08 11:07 AM Qiu Ying
- Added new function "replace_special_char"

9/14/2017 11:15 AM Andy
- Added new function "generate_receipt_ref_no".

10/2/2017 4:40 PM Justin
- Enhanced the sales trend function can accept multiple branch and SKU items.
- Enhanced the sales trend script to calculate base on date not year & month.

10/17/2017 11:06 AM Andy
- Fixed custom payment type unable to show, due to case sensitive issue. need to convert to like "Member Point".

12/5/2017 4:36 PM Andy
- Enhanced to store member and non-member sales into cache table when finalise counter collection.

1/30/2018 5:15 PM Andy
- Fixed member sales cache data wrong.

12/18/2017 4:24 PM Justin
- Enhanced to create sales agent sales cache table while updating sales cache.

2/12/2018 2:03 PM Andy
- Fixed daily_sales_cache_tbl member data wrong.

2/27/2018 9:12 AM Andy
- Fixed table daily_sales_cache_b dun hv member column when create.

12/18/2017 4:24 PM Justin
- Enhanced to create sales agent sales cache table while updating sales cache.

3/13/2018 10:12 AM Andy
- Fixed calculate fresh market member data error.
- Temporary hide create sales agent cache table.

3/30/2018 3:38 PM Justin
- Enhanced to insert date into new table for sales agent cronjob to calculate the sales cache.

4/9/2018 4:09 PM Justin
- Bug fixed on scan barcode function did not check against inactive SKU item.

04/20/2018 3:27 PM Brandon
- Add max_height variable in resize_photo function for upload top right banner function. 

7/18/2018 10:02 AM Justin
- Enhanced filter with GRN barcoder while config "enable_grn_barcoder" is turned on.

7/31/2018 12:30 PM Andy
- Added function "fputcsv_eol" to make fputcsv compatible to windows.

9/21/2018 5:05 PM Andy
- Added function "replace_ms_quotes".

10/2/2018 2:01 PM Andy
- Change branch_approval_history always get max_id+1 to insert.

6/20/2018 1:54 PM Justin
- Enhanced exchange rate to round base on config instead of hardcoded 3 digits.
- Enhanced to round pos payment amount to 2 digits when the payment is paid by foreign currency.
- Enhanced daily sales cache table to have currency adjust amount.

10/23/2018 11:35 AM Justin
- Added new function "load_sku_type_list".
- Enhanced to add "CONCESS" into SKU Type ENUM list when creating new table.

12/5/2018 9:19 AM Andy
- Fixed resize_photo maxheight checking bug.

12/17/2018 5:33 PM Justin
- Bug fixed on adding +8 hours to get last cashier had caused issue on getting wrong Cash Denomination.

1/4/2019 5:54 PM Justin
- Enhanced the calculation for cash denomination by casher to include foreign currency data.

1/11/2019 11:34 AM Andy
- Enhanced function get_cat_tree_info() to have 3rd parameter $include_own_cat.
- Added checking if function getallheaders() not exists.

2/13/2019 1:03 PM Andy
- Enhanced function get_sku_item_cost_selling() to check config.sku_always_show_trade_discount
- Fixed generate_pos_cashier_finalize() to calculate special cash refund.

3/15/2019 11:48 AM Andy
- Enhanced counter collection variances calculation to include ewallet sales and ewallet collection.
- Changed generate_pos_cashier_finalize() to posManager->generatePosCashierFinalize()

3/21/2019 11:28 AM Andy
- Change get_branch_file_url() 3rd parameter to become no long use and is always empty.

4/4/2019 1:20 PM Justin
- Enhanced resize_photo function to be able to process png image.

4/29/2019 2:37 PM Andy
- Added function "mssql_insert_by_field" and "mssql_update_by_field".

6/3/2019 2:42 PM Andy
- Added new approval flow "CYCLE COUNT".

6/27/2019 10:09 AM William
- Added function "get_sku_promotion_photos" for get sku promotion image dir.

8/9/2019 1:24 PM Andy
- Fixed "member_points_changed" failed to mark member points need recalculate.

11/7/2019 10:32 AM Andy
- Enhanced log_br() to can pass 5th params $bid.

11/14/2019 4:33 PM Justin
- Enhanced to create sales agent sales cache table for commission made by sales / qty range while updating sales cache.

12/3/2019 5:20 PM Justin
- Added new function "log_sa".

12/11/2019 4:36 PM Andy
- Fixed sales cache table create command missing innoDB.
- Modified insert new log to always get max id+1 and set timestamp as CURRENT_TIMESTAMP. 

12/19/2019 10:40 AM Justin
- Bug fixed send email function couldn't send out the email.

12/31/2019 1:58 PM Andy
- Change "sql_query_skip_logbin" to use back "sql_query" due to there maybe have report server feature in future.

1/6/2020 10:39 AM Andy
- Removed round2 from "get_discount_amt".

1/7/2019 5:22 PM Andy
- Removed IsMail when send email.

2/3/2020 3:22 PM Andy
- Fixed vendor_sku_history_b table create command missing innoDB.

3/5/2020 3:26 PM William
- Added new function "check_can_access_custom_report" for custom report.

3/19/2020 10:58 AM William
- Enhanced to call "update_pos_membership_guid" on function "update_sales_cache".

4/1/2020 3:29 PM William
- Enhanced to insert id manually for ri_items table that uses auto increment.

6/11/2020 3:22 PM Andy
- Enhanced to call posManager->generateSKUItemFinalisedCache when finalise sales.

10/12/2020 4:09 PM Andy
- Removed the checking of returned pos when in "update_sales_cache".

10/16/2020 2:48 PM William
- Added function "get_tax_settings" and "check_tax_status".

12/2/2020 7:59 PM Shane
- Added function "pp"

1/20/2021 2:22 PM Andy
- Fixed "update_sales_cache" should not call "update_pos_membership_guid" if date = -1.

2/3/2021 6:12 PM Andy
- Change "generate_receipt_ref_no" the branch_id now is 4 digits.
*/

function mysql_insert_by_field($arr, $fields = false, $null_if_empty=0)
{

	$ret = '';

	if (!is_array($fields))
	{
		$fields = array_keys($arr);
	}

	foreach ($fields as $f)
	{
		if (is_numeric($f)) continue;
		$newf[] = "`$f`";
		$v = $arr[$f];
		if ($ret != '') $ret .= ',';
		if (strstr($v, 'CURRENT_TIMESTAMP'))
		    $ret .= $v;
		else
			$ret .= ms($v,$null_if_empty);
	}

	$ret = '(' . join(",", $newf) . ') values (' . $ret . ')';
	return $ret;
}

function mysql_update_by_field($arr, $fields = false,$null_if_empty=0)
{

	$ret = '';

	if (!is_array($fields))
	{
		$fields = array_keys($arr);
	}

	foreach ($fields as $f)
	{
		if (is_numeric($f)) continue;
		$v = $arr[$f];
		if ($ret != '') $ret .= ', ';
		if (strstr($v, 'CURRENT_TIMESTAMP'))
		    $ret .= "`$f` = " . $v;
		else
			$ret .= "`$f` = " . ms($v,$null_if_empty);
	}

//	$ret = '(' . join(",", $fields) . ') values (' . $ret . ')';
	return $ret;
}

function mssql_insert_by_field($arr, $fields = false, $null_if_empty=0)
{

	$ret = '';

	if (!is_array($fields))
	{
		$fields = array_keys($arr);
	}

	foreach ($fields as $f)
	{
		if (is_numeric($f)) continue;
		$newf[] = "$f";
		$v = $arr[$f];
		if ($ret != '') $ret .= ',';
		if (strstr($v, 'CURRENT_TIMESTAMP'))
		    $ret .= $v;
		else
			$ret .= ms($v,$null_if_empty);
	}

	$ret = '(' . join(",", $newf) . ') values (' . $ret . ')';
	return $ret;
}

function mssql_update_by_field($arr, $fields = false,$null_if_empty=0)
{

	$ret = '';

	if (!is_array($fields))
	{
		$fields = array_keys($arr);
	}

	foreach ($fields as $f)
	{
		if (is_numeric($f)) continue;
		$v = $arr[$f];
		if ($ret != '') $ret .= ', ';
		if (strstr($v, 'CURRENT_TIMESTAMP'))
		    $ret .= "$f = " . $v;
		else
			$ret .= "$f = " . ms($v,$null_if_empty);
	}

//	$ret = '(' . join(",", $fields) . ') values (' . $ret . ')';
	return $ret;
}

function ms($str,$null_if_empty=0)
{
	if (trim($str) === '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", $str);
	$str = str_replace("\\", "\\\\", $str);
	return "'" . (trim($str)) . "'";
}

function mi($intv,$null_if_empty=0)
{
	if ($intv == '' && $null_if_empty) return "null";
	$intv = str_replace(",","",$intv);
	settype($intv, 'int');
	return $intv;
}

function mf($floatv,$null_if_empty=0)
{
	if ($floatv == '' && $null_if_empty) return "null";
	$floatv = str_replace(",","",$floatv);
	settype($floatv, 'float');
	return $floatv;
}

function mb($boolv,$null_if_empty=0)
{
	if ($boolv == '' && $null_if_empty) return "null";
	settype($boolv, 'bool');
	return ($boolv ? 1 : 0);
}

function sz($val)
{
	return ms(serialize($val));
}

function ucase($str)
{
	return trim(strtoupper($str));
}

function lcase($str)
{
	return trim(strtolower($str));
}

function zintval($v, $empty='&nbsp;')
{
	if (!$v)
	    return $empty;
	return intval($v);
}

function zsprintf($f, $v, $empty='&nbsp;')
{
	if (!$v)
	    return $empty;
	return sprintf($f, $v);
}

function jsstring($string)
{
	return preg_replace("/[\n\r]+/", '\n', strtr($string, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/')));
}

function getmicrotime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

function connect_other_db($uri)
{
	global $db_default_connection;
	return connect_db($uri, $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);
}

function connect_hq()
{
	global $db_default_connection;
    $hqcon = connect_db(HQ_MYSQL, $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);
    return $hqcon;
}

function str_month($m)
{
	$mthname = array("-", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

	return $mthname[intval($m)];
}

function days_of_month($m,$y)
{
	$dom = array(0,31,($y%4==0?29:28),31,30,31,30,31,31,30,31,30,31);
	return $dom[intval($m)];
}

function log_br($uid, $type, $ref, $log, $bid=0){
	global $con, $sessioninfo;
	if(!$bid)	$bid = $sessioninfo['branch_id'];
	// if not login, find the branch id from db
	if (!$bid)
	{
		$con->sql_query("select id from branch where code = ".ms(BRANCH_CODE));
		$r = $con->sql_fetchrow();
		$bid = $r[0];
	}
	$bid = mi($bid);
	
	// Get Max ID
	$con->sql_query("select max(id) as max_id from log where branch_id=$bid for update");
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	$new_id = mi($tmp['max_id'])+1;
	
	$upd = array();
	$upd['id'] = $new_id;
	$upd['branch_id'] = $bid;
	$upd['user_id'] = $uid;
	$upd['type'] = $type;
	$upd['rid'] = $ref;
	$upd['log'] = $log;
	$upd['timestamp'] = 'CURRENT_TIMESTAMP';
	
	$con->sql_query("insert into log ".mysql_insert_by_field($upd)) or die(mysql_error());
}

function send_pm($to, $msg, $url, $from = -1, $can_send_to_myself = false, $branch_id = 0)
{
    global $sessioninfo, $con, $config;

	if(defined('TERMINAL')){
	    $sessioninfo['id'] = $from;
	    $sessioninfo['branch_id'] = $branch_id;
		$subject =  "ARMS";
	}else{
		$subject =  $sessioninfo['u'];
	}
	
    if($config['notification_send_email']){
        include_once("include/class.phpmailer.php");
	    $mailer = new PHPMailer(true);
	    //$mailer->From = "noreply@localhost";
	    $mailer->FromName = "ARMS Notification";
	    $mailer->Subject = "You have received a message from ".$subject;
	    $mailer->IsHTML(true);
		//$mailer->IsMail();

	    $url2 = "http://".$_SERVER['HTTP_HOST']."/pm.php?a=view_pm&branch_id=$sessioninfo[branch_id]&id=%d";
	    $email_msg_sample = "<h2><u>ARMS Notification</u></h2>";
		$email_msg_sample .= "Title: $msg<br />";
	    $email_msg_sample .= "<a href=\"".$url2."\">Click here to view the message.</a>";
	    //$mailer->Body = $email_msg;
	}
	
    if ($from == -1) $from = $sessioninfo['id'];
    $send = $numbers = array();
    $today = date("Y-m-d");
	$sms_user_count = 0;
    if (is_array($to))
    {
	    foreach ($to as $id)
        {
            if (intval($id) > 0 && !$send[$id] && ($id != $sessioninfo['id'] || ($id == $sessioninfo['id'] && $can_send_to_myself))) {         	
                $send[$id] = 1;
                    
                $con->sql_query("select * from pm where to_user_id=".mi($id)." and url=".ms($url)." and status=0 and added between ".ms($today)." and date_add(".ms($today).", INTERVAL 1 day)");
                $old_pm = $con->sql_fetchassoc();
                $con->sql_freeresult();
                
                if($old_pm){
                	// update old pm to closed
					$con->sql_query("update pm set status=1 where branch_id=".mi($old_pm['branch_id'])." and id=".mi($old_pm['id']));
				}
				
				$upd = array();
				$upd['branch_id'] = $sessioninfo['branch_id'];
				$upd['from_user_id'] = $from;
				$upd['to_user_id'] = $id;
				$upd['msg'] = $msg;
				$upd['url'] = $url;
				$upd['added'] = 'CURRENT_TIMESTAMP';
                
                $con->sql_query("insert into pm ".mysql_insert_by_field($upd));
                $new_pm_id = $con->sql_nextid();
                
                
                
                if($config['notification_send_email']){

                    $email_address = get_user_info_by_colname($id, "email");
                	if($mailer->ValidateAddress($email_address)){
                        $mailer->AddAddress($email_address);
                        $mailer->Body = sprintf($email_msg_sample, $new_pm_id);
                        // send the mail
                        //$send_success = $mailer->Send();
                        $send_success = phpmailer_send($mailer, $mailer_info);
                        
						//$mailer->to = array();  // clear the address list
						$mailer->ClearAddresses();
						
					}
				}
                

				if($config['notification_send_sms']){
					$sms_q = $con->sql_query("select phone_1, sms_notification from user where id = ".mi($id)." and phone_1 is not null and phone_1 != '' and active = 1");
					$sms_info = $con->sql_fetchrow($sms_q);
					$con->sql_freeresult($sms_q);

					if($sms_info['sms_notification'] && $sms_info['phone_1'] && preg_match('/^01\d{8,9}/',$sms_info['phone_1'])) $numbers[] = '6'.$sms_info['phone_1'];
				}
            }
	    }
		
		if($config['isms_user'] && $config['isms_pass'] && $config['notification_send_sms'] && count($numbers) > 0){
			// proceed to send sms stage
			include_once("include/class.isms.php");
			$isms = new iSMS();
			$sms_msg = "ARMS: ".$msg;
			$isms->send_sms($numbers,$sms_msg);
			$remaining_cc = $isms->get_credit();
			log_br($sessioninfo['id'],'Notification','SMS',"Send SMS to ".count($numbers)." user(s) (Remaining credit: $remaining_cc)");
		}
	}
	elseif ($to != $sessioninfo['id'] || ($to == $sessioninfo['id'] && $can_send_to_myself))
	{
		
	    $con->sql_query("select * from pm where to_user_id=".mi($to)." and url=".ms($url)." and status=0 and added between ".ms($today)." and date_add(".ms($today).", INTERVAL 1 day)");
	    $old_pm = $con->sql_fetchassoc();
	    $con->sql_freeresult();
	    
	    if($old_pm){
	    	// update old pm to closed
			$con->sql_query("update pm set status=1 where branch_id=".mi($old_pm['branch_id'])." and id=".mi($old_pm['id']));
		}
		
		$upd = array();
		$upd['branch_id'] = $sessioninfo['branch_id'];
		$upd['from_user_id'] = $from;
		$upd['to_user_id'] = $to;
		$upd['msg'] = $msg;
		$upd['url'] = $url;
		$upd['added'] = 'CURRENT_TIMESTAMP';
		
	    $con->sql_query("insert into pm ".mysql_insert_by_field($upd));
	    $new_pm_id = $con->sql_nextid();
	    
	    // send email
	    if($config['notification_send_email']){
	        $email_address = get_user_info_by_colname($to, "email");
	    	if($mailer->ValidateAddress($email_address)){
	            $mailer->AddAddress($email_address);
	            $mailer->Body = sprintf($email_msg_sample, $new_pm_id);
	            // send the mail
				//$send_success = $mailer->Send();
				$send_success = phpmailer_send($mailer, $mailer_info);
				//$mailer->to = array();  // clear the address list
				$mailer->ClearAddresses();
			}
		}
		

		// send sms
		if($config['isms_user'] && $config['isms_pass'] && $config['notification_send_sms']){
			$sms_q = $con->sql_query("select phone_1, sms_notification from user where id = ".mi($to)." and phone_1 is not null and phone_1 != ''");
			$sms_info = $con->sql_fetchassoc($sms_q);
			$con->sql_freeresult($sms_q);
			
			if($sms_info['sms_notification'] && $sms_info['phone_1'] && preg_match('/^01\d{8,9}/',$sms_info['phone_1'])) $numbers[] = '6'.$sms_info['phone_1'];
		
			include_once("include/class.isms.php");
			$isms = new iSMS();
			$sms_msg = "ARMS: ".$msg;
			$isms->send_sms($numbers,$sms_msg);
			$remaining_cc = $isms->get_credit();
			log_br($sessioninfo['id'],'Notification','SMS',"Send SMS to ".count($numbers)." user(s) (Remaining credit: $remaining_cc)");
		}
	}
}

function send_pm2($to, $msg, $url, $others = array())
{
    global $sessioninfo, $con, $config;

	$from = -1;
	$can_send_to_myself = false;
	
	if($others){
		if(isset($others['from']))	$from = mi($others['from']);
		if(isset($others['can_send_to_myself']))	$can_send_to_myself = $others['can_send_to_myself'];
		if(isset($others['module_name']))	$module_name = $others['module_name'];
		
	}
	
	if(defined('TERMINAL')){
	    $sessioninfo['id'] = $from;
	    $sessioninfo['branch_id'] = mi($others['branch_id']);
		$subject =  "ARMS";
	}elseif($others['custom_from']){
		$sessioninfo['id'] = $others['custom_from'];
	    $sessioninfo['branch_id'] = mi($others['branch_id']);
		$subject =  "ARMS";
	}
	else{
		$subject =  $sessioninfo['u'];
	}
	
	if(!is_array($to))	die('send_pm2() must accept variable $to as array.');
	
	$branch_id = mi($sessioninfo['branch_id']);
	
	include_once("include/class.phpmailer.php");
	$mailer = new PHPMailer(true);
	//$mailer->From = "noreply@localhost";
	$mailer->FromName = "ARMS Notification";
	$mailer->Subject = "You have received a message from ".$subject;
	$mailer->IsHTML(true);
	//$mailer->IsMail();

	$email_msg_header = "<h2><u>ARMS Notification</u></h2>";
	$email_msg_header .= "Title: $msg<br />";
	
	//$mailer->Body = $email_msg;
	
	$bcode = get_branch_code($branch_id);
	if($module_name){
		switch(strtolower($module_name)){
			case 'adjustment':
				$module_name = 'ADJUSTMENT';
				$phpfile = "login.php?server=$bcode&redir=".urlencode("adjustment_approval.php?branch_id=$branch_id");
				break;
			case 'sku':
				$module_name = "SKU";
				$phpfile = "masterfile_sku_approval.php";
				break;
			case 'grn':
				$module_name = 'GRN';
				if(!$config['use_grn_future'])
					$phpfile = "login.php?server=$bcode&redir=goods_receiving_note_approval.account.php";
				else
					$phpfile = "login.php?server=$bcode&redir=goods_receiving_note_approval.php";
				break;
			case 'po':
				$module_name = 'PO';
				$phpfile = "login.php?server=$bcode&redir=po_approval.php";
				break;
			case 'do':
				$module_name = "DO";
				$phpfile = "login.php?server=$bcode&redir=".urlencode("do_approval.php?branch_id=$branch_id");
				break;
			case 'adjustment':
				$module_name = "Adjustment";
				$phpfile = "adjustment_approval.php?branch_id=$branch_id";
				break;
			case 'promotion':
				$module_name = "Promotion";
				$phpfile = "login.php?server=$bcode&redir=promotion_approval.php";
				break;
			case 'ci':
				$module_name = "Consignment Invoice";
				$phpfile = "consignment_invoice_approval.php";
				break;
			case 'sales_order':
				$module_name = "Sales Order";
				$phpfile = "login.php?server=$bcode&redir=sales_order_approval.php";
				break;
			case 'future_price':
				$module_name = "Batch Price Change";
				$phpfile = "login.php?server=$bcode&redir=masterfile_sku_items.future_price_approval.php";
				break;
			case 'eform':
				$module_name = "eForm";
				$phpfile = "login.php?server=$bcode&redir=eform.approval.php";
				break;
			case 'gra':
				$module_name = "GRA";
				$phpfile = "login.php?server=$bcode&redir=goods_return_advice.approval.php";
				break;
			case 'purchase_agreement':
				$module_name = "Purchase Agreement";
				$phpfile = "login.php?server=$bcode&redir=po.po_agreement.approval.php";
				break;
			case 'membership_redemption':
				$module_name = "Membership Redemption Verification";
				$phpfile = "login.php?server=$bcode&redir=".urlencode("membership.redemption_history.php?do_verify=1&branch_id=$branch_id");
				break;
			case 'cn':
				$module_name = "Credit Note";
				$phpfile = "consignment.credit_note.approval.php";
				break;
			case 'dn':
				$module_name = "Debit Note";
				$phpfile = "consignment.debit_note.approval.php";
				break;
			case 'cycle_count':
				$module_name = "Cycle Count";
				$phpfile = "admin.cycle_count.approval.php";
				break;
			default:
				// module php not found, cannot send
				break;
		}
		$approval_php = $phpfile;
	}
	//print_r($to);
	//print_r($others);exit;


    if ($from == -1) $from = $sessioninfo['id'];
    $send = $numbers = array();
    $today = date("Y-m-d");
	$sms_user_count = 0;
	
    if ($to)
    {
	    foreach ($to as $user_settings)
        {
        	$email_msg_sample = $email_msg_header;
        	$id = mi($user_settings['user_id']);	// user id
        	if(!$id)	continue;
        	
            if (intval($id) > 0 && !$send[$id] && ($id != $sessioninfo['id'] || ($id == $sessioninfo['id'] && $can_send_to_myself))) {         	
                $send[$id] = 1;
                
                if($user_settings['approval_settings']['pm'] || $user_settings['approval_settings']['email']){	// can send pm, or send email must send pm
	                // check if user still have same link of pm at today and still no close
	                $con->sql_query("select * from pm where to_user_id=".mi($id)." and url=".ms($url)." and status=0 and added between ".ms($today)." and date_add(".ms($today).", INTERVAL 1 day)");
	                $old_pm = $con->sql_fetchassoc();
	                $con->sql_freeresult();
	                
	                if($old_pm){
	                	// update old pm to closed
						$con->sql_query("update pm set status=1 where branch_id=".mi($old_pm['branch_id'])." and id=".mi($old_pm['id']));
					}
					
					$upd = array();
					$upd['branch_id'] = $sessioninfo['branch_id'];
					$upd['from_user_id'] = $from;
					$upd['to_user_id'] = $id;
					$upd['msg'] = $msg;
					$upd['url'] = $url;
					$upd['added'] = 'CURRENT_TIMESTAMP';
	                
	                if($user_settings['approval_settings']['email'] && !$user_settings['approval_settings']['pm']){	// got send email but no send pm
	                	// mark pm as read
	                	$upd['status'] = 1;
	                
	                }
	                $con->sql_query("insert into pm ".mysql_insert_by_field($upd));
	                $new_pm_id = $con->sql_nextid();
	                
	                if($user_settings['approval_settings']['email']){
	                    $email_address = get_user_info_by_colname($id, "email");
	                	if($mailer->ValidateAddress($email_address)){
	                        $mailer->AddAddress($email_address);
	                        
	                        // PM
	                        $pm_path = "pm.php?a=view_pm&branch_id=$branch_id&id=$new_pm_id";
							if($config['main_server_url']){		
								$email_msg_sample .= "<b>LAN</b>: <a href='http://".$config['main_server_url']['lan']."/$pm_path'>View PM</a>";
								$email_msg_sample .= "<br><b>WAN</b>: <a href='http://".$config['main_server_url']['wan']."/$pm_path'>View PM</a>";
							}else{
								$url2 = "http://".$_SERVER['HTTP_HOST']."/$pm_path";
								$email_msg_sample .= "<a href=\"".$url2."\">Click here to view the message.</a>";
							}
	                        $mailer->Body = $email_msg_sample;
	                        
	                        // APPROVAL FLOW
	                        if($user_settings['type'] == 'approval' && $module_name && $approval_php){
	                        	$mailer->Body .= "<br /><br />You have $module_name ($bcode) waiting for approval<br />";
	                        	
	                        	if($config['main_server_url']){
	                        		$mailer->Body .= "<b>LAN</b>: <a href='http://".$config['main_server_url']['lan']."/$approval_php'>Approval Screen</a>";
									$mailer->Body .= "<br><b>WAN</b>: <a href='http://".$config['main_server_url']['wan']."/$approval_php'>Approval Screen</a>";
	                        	}else{
	                        		$mailer->Body .= "<a href='http://".$_SERVER['HTTP_HOST']."/".$approval_php."'>Click here to go to approval screen.</a>";
	                        	}
	                        }
	                        // send the mail
	                        //$send_success = $mailer->Send();
	                        $send_success = phpmailer_send($mailer, $mailer_info);
							//$mailer->to = array();  // clear the address list
							$mailer->ClearAddresses();
						}
					}
                }

				$sms_q = $con->sql_query("select phone_1, sms_notification from user where id = ".mi($id)." and phone_1 is not null and phone_1 != '' and active = 1");
				$sms_info = $con->sql_fetchrow($sms_q);
				$con->sql_freeresult($sms_q);

				if(($user_settings['approval_settings']['sms'] || $sms_info['sms_notification']) && $sms_info['phone_1'] && preg_match('/^01\d{8,9}/',$sms_info['phone_1'])) $numbers[] = '6'.$sms_info['phone_1'];
            }
	    }
		
		if($config['isms_user'] && $config['isms_pass'] && count($numbers) > 0){
			// proceed to send sms stage
			include_once("include/class.isms.php");
			$isms = new iSMS();
			$sms_msg = "ARMS: ".$msg;
			$isms->send_sms($numbers,$sms_msg);
			$remaining_cc = $isms->get_credit();
			log_br($sessioninfo['id'],'Notification','SMS',"Send SMS to ".count($numbers)." user(s) (Remaining credit: $remaining_cc)");
		}

	}
}



function js_redirect($alert, $url)
{
	global $login;
	if(!$login){    // it is redirect due to no login
	    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']))    $_SESSION['restore_request_uri'] = $_SERVER['PHP_SELF'];
		else	$_SESSION['restore_request_uri'] = $_SERVER['REQUEST_URI'];

		// if it is call by ajax, only redirect to referer, else will redirect to full path
		$_SESSION['restore_request_uri'] = (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) ? $_SERVER['HTTP_REFERER'] : $_SERVER['REQUEST_URI'];
	}

    if(is_ajax()){
      print $alert;
    }
    else{
      $alert = jsstring($alert);
      $url = jsstring($url);

      print "
          <script>
          alert('$alert');
          parent.window.location = '$url';
          </script>\n";
    }
	exit;
}

function get_department($cat_id, $tree_str = '')
{
	global $con;
	/*if ($tree_str == '')
	{
     	$con->sql_query("select tree_str from category where id = ".mi($cat_id));
	    $r = $con->sql_fetchrow();
	    if ($r)
			$tree_str = $r[0];
	    else
	        return '';
	}
	if (!preg_match("/^\(\d+\)\(\d+\)\((\d+)\)/", $tree_str, $matches))
	{
	    return '';
	}*/
	$dept_id = mi(get_department_id($cat_id));
	if(!$dept_id)	return '';
	
    //$con->sql_query("select description from category where id = ".mi($matches[1]));
    $con->sql_query("select description from category where id = ".mi($dept_id));
   	$r = $con->sql_fetchrow();
    if ($r)
		return $r[0];
    else
        return '';
}


function get_department_id($cat_id, $tree_str = '')
{
	global $con;
	//if ($tree_str == '')
	//{
     	$con->sql_query("select department_id from category where id = ".mi($cat_id)." and level>1");
	    $r = $con->sql_fetchrow();
	    if ($r)
			return $r[0];
	    else
	        return 0;
	//}
/*
	if (!preg_match("/^\(\d+\)\(\d+\)\((\d+)\)/", $tree_str, $matches))
	{
	    return 0;
	}
	return intval($matches[1]);
*/
}

// return branch code from id give
function get_branch_code($id)
{
	global $con, $branch_code_list;

	if (!is_array($branch_code_list))
	{
		$con->sql_query("select id, code from branch");
		while($r=$con->sql_fetchrow())
		{
			$branch_code_list[$r['id']] = $r['code'];
		}
	}
	return $branch_code_list[$id];
}

function is_new_id($id)
{
	return ($id > 1000000000);
}


function is_last_approval($params){
  global $con;

  if(!$params)  return false;
  $branch_id = mi($params['branch_id']);
  $type = $params['type'];
  $sku_type = $params['sku_type'];
  $dept_id = mi($params['dept_id']);
  $user_id = mi($params['user_id']);
  $check_is_approval = $params['check_is_approval'];
  if($params['database'])	$database_link = $params['database'].".";

  $filter[] = "branch_id=".mi($branch_id);
  $filter[] = "type=".ms($type);
  if(isset($params['dept_id']))  $filter[] = "sku_category_id=".mi($dept_id);
  if(isset($params['sku_type'])) $filter[] = "sku_type=".ms($sku_type);
  $filter[] = "active=1";
  $filter = join(' and ', $filter);

  $approval_flow_btl = $database_link.'approval_flow';
  $con->sql_query("select * from $approval_flow_btl where $filter");

	$r = $con->sql_fetchrow();
	if (!$r) return false; // no approval flow found

	switch($r['aorder']){
    case 1: // Follow Sequences
      	if (preg_match("/\|$user_id\|$/", $r['approvals'])){	// check is last approval
    		return true;
    	}
    	if ($check_is_approval && preg_match("/\^|$user_id\|/", $r['approvals'])){	// check is one of approval
      		return true;
      	}
      break;
    case 2: // All (No Sequences)
      	if (preg_replace("/\|$user_id\|/",'|', $r['approvals'])=='|'){	// check is last approval
    		return true;
    	}
    	if ($check_is_approval && preg_match("/\|$user_id\|/", $r['approvals'])){	// check is one of approval
      		return true;
      	}
      break;
    case 3: // Anyone
      if (preg_match("/\|$user_id\|/", $r['approvals'])){
    		return true;
    	}
      break;
    case 4: // No Approver
      return true;
      break;
  }
	return false;
}

function check_is_last_approval_by_id($params, $mysql_con){
  if(!$params)  return false;
  
  if ($_REQUEST['on_behalf_of'] && $_REQUEST['on_behalf_by']) {
	$approval_on_behalf = array(
		'on_behalf_of' => str_replace('-',',',$_REQUEST['on_behalf_of']),
		'on_behalf_by' => mi($_REQUEST['on_behalf_by']),
	);
  }
  else {
	$approval_on_behalf = false;
  }
  
  $approve = mi($params['approve']);
  $user_id = mi($params['user_id']);
  if ($approval_on_behalf) {
	$users = explode(',',$approval_on_behalf['on_behalf_of']);
	$first_uid = mi($users[0]);
	$last_uid = mi(end($users));
	$user_id = $last_uid;
  }
  $id = mi($params['id']);
  $branch_id = mi($params['branch_id']);
  $auto_approve = $params['auto_approve'];
  $check_is_approval = $params['check_is_approval'];
  $tbl = $params['tbl'] ? $params['tbl'] : 'branch_approval_history';
  $update_approval_flow = isset($params['update_approval_flow']) ? $params['update_approval_flow'] : false;
	if($params['database'])	$database_link = $params['database'].".";

  if(!$tbl) return false;
  if($branch_id)  $filter[] = "branch_id=".mi($branch_id);
  $filter[] = "id=".mi($id);
  $filter = join(' and ', $filter);

    $tbl = $database_link.$tbl;

  $mysql_con->sql_query("select * from $tbl where $filter");
	$r = $mysql_con->sql_fetchrow();

	if (!$r) return false; // no approval flow found
	if($r['approvals']=='|')    return true;
	$is_last = false;
	$is_one_of_approver = false;
	
	if($auto_approve){ // no need checking, just approve
	    $is_last = true;
	    $is_one_of_approver = true;
	}else{
	    switch($r['approval_order_id']){
	      case 1: // Follow Sequences
	        if (preg_match("/\|$user_id\|$/", $r['approvals'])){
	      		$is_last = true;
	      		$is_one_of_approver = true;
	      	}
	      	if (preg_match("/\^|$user_id\|/", $r['approvals'])){
	      		$is_one_of_approver = true;
	      	}
	        break;
	      case 2: // All (No Sequences)
	        if (preg_match("/\|$user_id\|/", $r['approvals'])){
	      		$is_one_of_approver = true;
	      	}
			if ($approval_on_behalf) {
				$tmp_approvals = $r['approvals'];
				foreach ($users as $u) {
					$tmp_approvals = preg_replace("/\|$u\|/",'|', $tmp_approvals);
				}
				if ($tmp_approvals == '|') $is_last = true;
			}
			else {
				if (preg_replace("/\|$user_id\|/",'|', $r['approvals'])=='|'){
					$is_last = true;
				}
			}
	        break;
	      case 3: // Anyone
	        if (preg_match("/\|$user_id\|/", $r['approvals'])){
	      		$is_last = true;
	      		$is_one_of_approver = true;
	      	}
	        break;
	      case 4: // No Approver
	        $is_last = true;
	        $is_one_of_approver = true;
	        break;
	    }
	  }

	//die("$update_approval_flow - $is_one_of_approver");

  if($update_approval_flow&&$is_one_of_approver){
	$user_id = mi($params['user_id']);
     if($is_last){ // is last approval
        $mysql_con->sql_query("update $tbl set status = $approve, approvals ='|',approved_by=".ms($r['approved_by']."|$user_id|")." where $filter");
        return true;
     }
	 else{
		$new_approvals = $r['approvals'];
		if ($approval_on_behalf) {
			foreach ($users as $u) {
				$new_approvals = str_replace("|$u|","|",$new_approvals);
			}
		}
		else {
			$new_approvals = str_replace("|$user_id|","|",$new_approvals);
		}
        $mysql_con->sql_query("update $tbl set status = $approve, approvals = ".ms($new_approvals).", approved_by=".ms($r['approved_by']."|$user_id|")." where $filter");
        
        // send notification to next approval
        //send_msg_to_next_approval($params, $mysql_con);
        
        if($check_is_approval&&$is_one_of_approver) return true;
        else  return false;
     }
  }
  else{
    if($check_is_approval) return $is_one_of_approver;
    else  return $is_last;
  }
}

function check_and_create_approval2($params, $mysql_connection){

  $branch_id = mi($params['branch_id']);
  $type = $params['type'];
  $sku_type = $params['sku_type'];
  $dept_id = mi($params['dept_id']);
  $user_id = mi($params['user_id']);
  $reftable = $params['reftable'];
  $extra_sql = $params['extra_sql'];
  $save_as_branch_id = $params['save_as_branch_id'];
  $curr_flow_id = $params['curr_flow_id'];
  $skip_approve = $params['skip_approve'];
  $force_use_app_his = mi($params['force_use_app_his']);
	
	if(isset($params['doc_amt']))	$doc_amt = mf($params['doc_amt']);
	
  if($params['database'])	$database_link = $params['database'].".";

  $filter[] = "branch_id=".mi($branch_id);
  $filter[] = "type=".ms($type);
  if(isset($params['dept_id']))  $filter[] = "sku_category_id=".mi($dept_id);
  if(isset($params['sku_type'])) $filter[] = "sku_type=".ms($sku_type);
  $filter[] = "active=1";
  $filter = join(' and ', $filter);
  if ($extra_sql != '') $filter .= "and $extra_sql";

  $approval_flow_tbl = $database_link.'approval_flow';

	// check if we need approval, if yes, create one and store the ID
	$sql = "select * from $approval_flow_tbl where $filter";
	//print $sql;
	$mysql_connection->sql_query($sql);
	// if have flow for the type+branch, create new history entry and return the ID
	if ($r = $mysql_connection->sql_fetchrow())
	{
		$mysql_connection->sql_freeresult();
		
		$r['approval_settings'] = unserialize($r['approval_settings']);
		
		if($skip_approve){  // skip approval, keep all approval in list
            $approvals = $r['approvals'];
		}else{
            switch($r['aorder']){
		        case 1: // Follow Sequences
		          // if user is one of the approvals, skip uptil next person
		          if(preg_match("/^.*\|$user_id\|/", $r['approvals']))  $approved_by = "|$user_id|";  // is one of the approval
		          $approvals = preg_replace("/^.*\|$user_id\|/", "|", $r['approvals']);

		          break;
		        case 2: // All (No Sequences)
		          if(preg_match("/\|$user_id\|/", $r['approvals']))  $approved_by = "|$user_id|";  // is one of the approval
		          $approvals = preg_replace("/\|$user_id\|/", "|", $r['approvals']);
		          break;
		        case 3: // Anyone
		          if (preg_match("/\|$user_id\|/", $r['approvals'])){
		        		$approvals = '|';
		        		$approved_by = "|$user_id|";
		        	}else  $approvals = $r['approvals'];
		          break;
		        case 4: // No Approver
		          $approvals = '|';
		          $approved_by = "|$user_id|";
		          break;
		     }
		}
		
		//print "doc amt = $doc_amt";exit;
		if(isset($doc_amt) && $approvals != '|'){	// got approval
			$tmp_user_id_list = explode("|", $approvals);
			$user_id_list = array();
			if($tmp_user_id_list){
				foreach($tmp_user_id_list as $tmp_user_id){
					$tmp_user_id = mi($tmp_user_id);
					if($tmp_user_id){
						$user_id_list[] = $tmp_user_id;
					}
				}
			}
			//print_r($user_id_list);exit;
			unset($tmp_user_id_list);
			
			if($user_id_list){
				switch($r['aorder']){
					case 1:	// Follow Sequences
						/*foreach($user_id_list as $tmp_user_id){
							if(!$r['approval_settings']['approval'][$tmp_user_id]['min_doc_amt'])	break;
							if($r['approval_settings']['approval'][$tmp_user_id]['min_doc_amt'] > $doc_amt){
								// skip this approval
								$approvals = preg_replace("/\|$tmp_user_id\|/", "|", $approvals);
								$approved_by = $tmp_user_id;
							}else	break;
						}
						break;*/
					case 2: // All (No Sequences)
					case 3: // Anyone
						foreach($user_id_list as $tmp_user_id){
							if(!$r['approval_settings']['approval'][$tmp_user_id]['min_doc_amt'])	continue;	// no set min doc amount or set as zero, mean wont filter
							if($r['approval_settings']['approval'][$tmp_user_id]['min_doc_amt'] > $doc_amt){
								// skip this approval
								$approvals = preg_replace("/\|$tmp_user_id\|/", "|", $approvals);
								$approved_by = $tmp_user_id;
							}
						}
						break;
				}
				
				if($approvals == '|'){
					$approved_by = "|$user_id|";	// direct approvde due to no one qualify for min doc amt
					$direct_approve_due_to_less_then_min_doc_amt = true;
				}
			}
		}
		
		if($approvals == '||' || !$approvals)	$approvals = '|';

	   if($force_use_app_his || $type=='SKU_APPLICATION' || $type=='YEARLY_MARKETING_PLAN'){
	   
	    $approval_history_tbl = $database_link.'approval_history';
	    $approval_history_items_tbl = $database_link.'approval_history_items';
	    
	    $upd = array();
	    $upd['approval_flow_id'] = $r['id'];
	    $upd['ref_table'] = $reftable;
	    $upd['flow_approvals'] = $r['approvals'];
	    $upd['approvals'] = $approvals;
	    $upd['approved_by'] = $approved_by;
	    $upd['notify_users'] = $r['notify_users'];
	    $upd['active'] = 1;
	    $upd['approval_order_id'] = $r['aorder'];
	    
	    if($r['approval_settings'])	$upd['approval_settings'] =serialize($r['approval_settings']);
	    
        if($curr_flow_id){  // use back the same approval history id
        	$upd['id'] = $curr_flow_id;
          $mysql_connection->sql_query("replace into $approval_history_tbl ".mysql_insert_by_field($upd));
  		    $aid = $curr_flow_id;
        }else{
        
          $mysql_connection->sql_query("insert into $approval_history_tbl ".mysql_insert_by_field($upd));
  		    $aid = $mysql_connection->sql_nextid();
        }

  		  $app = array($aid, $approvals, $r['notify_users']);
  		  if($direct_approve_due_to_less_then_min_doc_amt)	$app['direct_approve_due_to_less_then_min_doc_amt'] = 1;
  		  
  		  if($aid&&$approvals=='|'){  // last approval
          //$log['general'] = 'Approve';
          
          $upd2 = array();
          $upd2['approval_history_id'] = $aid;
          $upd2['user_id'] = $user_id;
          $upd2['status'] = 1;
          $upd2['log'] = 'Approve';
          
          if($direct_approve_due_to_less_then_min_doc_amt){
          	$upd2['more_info']['direct_approve_due_to_less_then_min_doc_amt'] = 1;
          }
          
          if($upd2['more_info'])	$upd2['more_info'] = serialize($upd2['more_info']);
          
          $mysql_connection->sql_query("insert into $approval_history_items_tbl ".mysql_insert_by_field($upd2));
        }
        
        // send notification to next approval
        $params2 = array();
        $params2['tbl'] = 'approval_history';
        $params2['database'] = $params['database'];
        $params2['id'] = $aid;
        //if(!$params['skip_send_email']) send_msg_to_next_approval($params2, $mysql_connection);
        
  	    return $app;
     }else{ // others doc
     	
        $use_bid = $save_as_branch_id ? $save_as_branch_id : $branch_id;
        $branch_approval_history_tbl = $database_link.'branch_approval_history';

		$upd = array();
	    $upd['approval_flow_id'] = $r['id'];
	    $upd['branch_id'] = $use_bid;
	    $upd['ref_table'] = $reftable;
	    $upd['flow_approvals'] = $r['approvals'];
	    $upd['approvals'] = $approvals;
	    $upd['approved_by'] = $approved_by;
	    $upd['notify_users'] = $r['notify_users'];
	    $upd['active'] = 1;
	    $upd['approval_order_id'] = $r['aorder'];
	    
	     if($r['approval_settings'])	$upd['approval_settings'] =serialize($r['approval_settings']);
	     
        if($curr_flow_id){  // use back the same approval history id
        	$upd['id'] = $curr_flow_id;
          $mysql_connection->sql_query("replace into $branch_approval_history_tbl ".mysql_insert_by_field($upd));
          $aid = $curr_flow_id;
        }else{
			// Lock table so other session cant access this table
			$mysql_connection->sql_query("lock table $branch_approval_history_tbl write");
			
			// Get Max ID
			$mysql_connection->sql_query("select max(id) as max_id from $branch_approval_history_tbl where branch_id=".mi($upd['branch_id']));
			$tmp = $mysql_connection->sql_fetchassoc();
			$mysql_connection->sql_freeresult();
			
			$upd['id'] = mi($tmp['max_id'])+1;
			
			// insert data
			$mysql_connection->sql_query("insert into $branch_approval_history_tbl ".mysql_insert_by_field($upd));
			$aid = $mysql_connection->sql_nextid();
			
			// unlock table
			$mysql_connection->sql_query("unlock tables");
        }
        
        // send notification to next approval
        $params2 = array();
        $params2['tbl'] = 'branch_approval_history';
        $params2['database'] = $params['database'];
        $params2['id'] = $aid;
        $params2['branch_id'] = $use_bid;
        
		//if(!$params['skip_send_email']) send_msg_to_next_approval($params2, $mysql_connection);

		$ret = array($aid, $approvals, $r['notify_users']);
		if($direct_approve_due_to_less_then_min_doc_amt)	$ret['direct_approve_due_to_less_then_min_doc_amt'] = 1;
		
        return $ret;
     }
	}
	return false;
}

function send_msg_to_next_approval($params, $mysql_con){
	global $config, $con, $sessioninfo;
	//print_r($params);exit;
	if(!$config['notification_send_email']) return false;
	if(!$params)  return false;
	
	$id = mi($params['id']);
	$branch_id = mi($params['branch_id']);
	$tbl = $params['tbl'] ? $params['tbl'] : 'branch_approval_history';
	if($params['database'])	$database_link = $params['database'].".";

	if(!$tbl) return false;
	if($branch_id)  $filter[] = "branch_id=".mi($branch_id);
	$filter[] = "id=".mi($id);
	$filter = join(' and ', $filter);

	$tbl = $database_link.$tbl;

	$sql = "select * from $tbl where $filter";
	//print $sql;exit;
    $mysql_con->sql_query($sql);
    if ($r = $mysql_con->sql_fetchrow()){
        if(!$r['approvals'] || $r['approvals']=='|')    return false;   // already no more approval
        
        $tmp_to = preg_split("/\|/", $r['approvals']);
        if(!$tmp_to)    return false;   // invalid approval list
        $tmp_to2 = array();
        // clear some empty value
        foreach($tmp_to as $key=>$uid){
			if(!$uid)   continue;
			$tmp_to2[] = mi($uid);  
		}
		if(!$tmp_to2)   return false;
		//print_r($tmp_to2);exit;
        $to = array();
        
	    switch($r['approval_order_id']){
			case 1: // Follow Sequences
				// if user is one of the approvals, only take the next
				$to[] = $tmp_to2[0];
				break;
			case 2: // All (No Sequences)
				$to = $tmp_to2;  // send to all
				break;
			case 3: // Anyone
				$to = $tmp_to2;  // send to all
				break;
			case 4: // No Approver
				return false;   // impossible to get here
				break;
		}
		if(!$to)    return false;   // no receiver
		//print_r($to);exit;
		if($branch_id)	$bcode = get_branch_code($branch_id);   // get branch code
		
		switch(strtolower($r['ref_table'])){
			case 'sku':
			    $module_name = "SKU";
			    $phpfile = "masterfile_sku_approval.php";
			    break;
			case 'grn':
			    $module_name = 'GRN';
			    if(!$config['use_grn_future'])
			    	$phpfile = "login.php?server=$bcode&redir=goods_receiving_note_approval.account.php";
				else
				    $phpfile = "login.php?server=$bcode&redir=goods_receiving_note_approval.php";
			    break;
			case 'po':
			    $module_name = 'PO';
			    $phpfile = "login.php?server=$bcode&redir=po_approval.php";
			    break;
			case 'do':
			    $module_name = "DO";
			    $phpfile = "login.php?server=$bcode&redir=do_approval.php";
			    break;
			case 'adjustment':
			    $module_name = "Adjustment";
			    $phpfile = "adjustment_approval.php?branch_id=$branch_id";
			    break;
			case 'promotion':
			    $module_name = "Promotion";
			    $phpfile = "login.php?server=$bcode&redir=promotion_approval.php";
			    break;
			case 'ci':
			    $module_name = "Consignment Invoice";
			    $phpfile = "consignment_invoice_approval.php";
			    break;
			case 'sales_order':
			    $module_name = "Sales Order";
			    $phpfile = "login.php?server=$bcode&redir=sales_order_approval.php";
			    break;
			case 'membership_redemptio':
			    $module_name = "Membership Redemption";
			    $phpfile = "membership.redemption_history.php?t=1&do_verify=1&branch_id=$branch_id";
			    break;
			default:
			    // module php not found, cannot send
			    return false;
		}
		$url = "http://".$_SERVER['HTTP_HOST']."/".$phpfile;
		
		if($config['notification_send_email']){
			include_once("include/class.phpmailer.php");
			$mailer = new PHPMailer(true);
			//$mailer->From = "noreply@localhost";
			$mailer->FromName = "ARMS Notification";
			//$mailer->SetFrom('noreply@arms.localhost', 'ARMS Notification');
			$mailer->Subject = "You have $module_name ($bcode) waiting for approval ";
			$mailer->IsHTML(true);
			//$mailer->IsMail();

			$email_msg_sample = "<h2><u>ARMS Notification</u></h2>";
			$email_msg_sample .= "You have $module_name ($bcode) waiting for approval<br />";
			$email_msg_sample .= "<a href=\"".$url."\">Click here to go to approval screen.</a>";
			//$mailer->Body = $email_msg;
		}
			
		foreach($to as $uid){
			if($config['notification_send_email']){
				$email_address = get_user_info_by_colname($uid, "email");
				if($mailer->ValidateAddress($email_address)){
					$mailer->AddAddress($email_address);
					$mailer->Body = $email_msg_sample;
					
					// send the mail
					//$send_success = $mailer->Send();
					$send_success = phpmailer_send($mailer, $mailer_info);
					//$mailer->to = array();  // clear the address list
					$mailer->ClearAddresses();
				}
			}
			
			$sms_q = $con->sql_query("select phone_1, sms_notification from user where id = ".mi($uid)." and phone_1 is not null and phone_1 != '' and active = 1");
			$sms_info = $con->sql_fetchrow($sms_q);
			$con->sql_freeresult($sms_q);

			if($sms_info['sms_notification'] && $sms_info['phone_1'] && preg_match('/^01\d{8,9}/',$sms_info['phone_1'])) $numbers[] = '6'.$sms_info['phone_1'];
		}
		
		if($config['isms_user'] && $config['isms_pass'] && count($numbers) > 0){
			include_once("include/class.isms.php");
			$isms = new iSMS();
			$sms_msg = "ARMS: You have $module_name ($bcode) waiting for approval";
			$isms->send_sms($numbers,$sms_msg);
			$remaining_cc = $isms->get_credit();
			log_br($sessioninfo['id'],'Notification','SMS',"Send SMS to ".count($numbers)." user(s) (Remaining credit: $remaining_cc)");
		}
	}
	return false;   // failed to send
}

function update_category_changed($cat_id){
	global $con;
	$con->sql_query("select * from category_cache where category_id=$cat_id");
	$cc = $con->sql_fetchrow();
	$cat_id_array = array($cat_id => $cat_id);
	
	$lv = 0;
	$p = 'p'.$lv;
	do{
	  $cid = $cc[$p];
	  if($cid) $cat_id_array[$cid] = $cid;
	  $lv++;
	  $p = 'p'.$lv;
	  if($cid==$cat_id) break;
	}while($cc[$p]);
	
	if($cat_id_array){
		$added = date("Y-m-d H:i:s");
		
		// get branch id list
		$q1 = $con->sql_query("select id from branch order by id");
		while($r = $con->sql_fetchassoc($q1)){
			foreach($cat_id_array as $tmp_cat_id){
				$upd = array();
				$upd['branch_id'] = $r['id'];
				$upd['category_id'] = $tmp_cat_id;
				$upd['added'] = $added;
				$con->sql_query("replace into category_changed ".mysql_insert_by_field($upd));
			}
		}
		$con->sql_freeresult($q1);
		
	  //$con->sql_query("update category set changed=1 where id in(".join(',',$cat_id_array).")");
	}
}

function get_lowest_price($branch_id, $sid, $date, $is_member = false){
	global $con;


	// default selling price
	$con->sql_query("select price from sku_items_price_history where branch_id=".mi($branch_id)." and sku_item_id=".mi($sid)." order by added desc limit 1 ");
	if($con->sql_numrows()>0){
		$default_price = $con->sql_fetchfield(0);
	}else{  // get from master
		$con->sql_query("select selling_price from sku_items where id=".mi($sid));
		$default_price = $con->sql_fetchfield(0);
	}
	$use_price = $default_price;

	$con->sql_query("select code from branch where id=".mi($branch_id));
	$bcode = $con->sql_fetchfield(0);
	if(!$bcode) return $use_price;

	// find promotion price
	$sql = "select pi.*
from promotion_items pi
left join promotion p on p.id=pi.promo_id and p.branch_id=pi.branch_id
where p.promo_branch_id like '%".$bcode."%' and pi.sku_item_id=".mi($sid)." and p.active=1 and p.approved=1 and ".ms($date)." between p.date_from and p.date_to";
	$con->sql_query($sql);
	while($r = $con->sql_fetchrow()){

		if($is_member){
			$disc_a = mf($r['member_disc_a']);
			$disc_p = $r['member_disc_p'];
		}else{
            $disc_a = mf($r['non_member_disc_a']);
			$disc_p = $r['non_member_disc_p'];
		}

		if($disc_a){
            $price_for_this_promo = $disc_a;    // use selected amount
		}else{
			if(strpos($disc_p, '%')){
			    $disc_p = str_replace("%", "", $disc_p);
                $price_for_this_promo = (1-($disc_p/100)) * $default_price; // calc by using discount percent
			}else{
                $price_for_this_promo = $default_price - $disc_p;   // calc by using discount amount
			}
		}
		if($price_for_this_promo<$use_price)    $use_price = $price_for_this_promo;
	}
	$con->sql_freeresult();

	// check sku/category discount
	$sql = "select sku.category_id, si.cat_disc_inherit, si.category_disc_by_branch_inherit, c.root_id as root_cat_id, c.category_disc_by_branch
	from sku_items si
	left join sku on sku.id=si.sku_id
	left join category c on c.id=sku.category_id
	where si.id=".mi($sid);
	$con->sql_query($sql);
	$sku = $con->sql_fetchrow();
	// use master price if local price is not defined
	if ($sku['price']==0) $sku['price'] = $sku['master_price'];
	$sku['default_price'] = $sku['price'];
	$sku['category_price'] = $sku['price'];	
	
	$sku['category_disc_by_branch_inherit'] = unserialize($sku['category_disc_by_branch_inherit']);
	$sku['category_disc_by_branch'] = unserialize($sku['category_disc_by_branch']);
	
	// sku first
	$mem_category_discount = $category_discount = '';
	if($sku['cat_disc_inherit']=='set'){	// sku override		
		// check sku items category discount if array exists
		if($is_member && isset($sku['category_disc_by_branch_inherit'][$branch_id]['member']) || isset($sku['category_disc_by_branch_inherit'][0]['member'])){
			
			
			// check member discount (not by member type)
			if($mem_category_discount === ''){
				$tmp_disc = '';
				
				// check global member discount - by branch
				if($tmp_disc==='' && isset($sku['category_disc_by_branch_inherit'][$branch_id]['member']['global'])){
					$tmp_disc = trim($sku['category_disc_by_branch_inherit'][$branch_id]['member']['global']);
				}
				
				// check global member discount - all branch
				if($tmp_disc==='' && isset($sku['category_disc_by_branch_inherit'][0]['member']['global'])){
					$tmp_disc = trim($sku['category_disc_by_branch_inherit'][0]['member']['global']);
				}
				
				$mem_category_discount = $tmp_disc;
			}
		}
		
		// check sku items non-member category discount
		if(isset($sku['category_disc_by_branch_inherit'][$branch_id]['nonmember']) || isset($sku['category_disc_by_branch_inherit'][0]['nonmember'])){
			// try to get own branch discount first
			if(isset($sku['category_disc_by_branch_inherit'][$branch_id]['nonmember']['global'])){
				$category_discount = trim($sku['category_disc_by_branch_inherit'][$branch_id]['nonmember']['global']);
			}
			
			// get from all branch
			if($category_discount==='' && isset($sku['category_disc_by_branch_inherit'][0]['nonmember']['global'])){
				$category_discount = trim($sku['category_disc_by_branch_inherit'][0]['nonmember']['global']);
			}
		}
		
		// member discount less than non-member?
		if($is_member && $mem_category_discount<$category_discount)	$mem_category_discount = $category_discount;
	}
	
	if($sku['cat_disc_inherit']=='none'){
		$use_cat_disc = 0;
	}elseif($sku['category_id'] && ($mem_category_discount==='' || $category_discount==='')){
		$cat_id = mi($sku['category_id']);
		while(($mem_category_discount==='' || $category_discount==='') && $cat_id>0){
			// get category info
			$con->sql_query("select * from category where id=".mi($cat_id));
			$cat_info = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			// unserialize discount info
			$cat_info['category_disc_by_branch'] = unserialize($cat_info['category_disc_by_branch']);
			
			// non-member
			if($category_discount===''){
				// try to get own branch discount first
				if(isset($cat_info['category_disc_by_branch'][$branch_id]['nonmember']['global'])){
					$category_discount = trim($cat_info['category_disc_by_branch'][$branch_id]['nonmember']['global']);
				}
				
				// own branch not set on this level, check all branch
				if($category_discount===''){
					// if it is a new version, check for array
					if(isset($cat_info['category_disc_by_branch'][0]['nonmember']['global'])){
						$category_discount = trim($cat_info['category_disc_by_branch'][0]['nonmember']['global']);
					}else{
						// for old version, direct check column
						$category_discount = trim($cat_info['category_disc']);
					}
				}
			}
			
			// member
			if($is_member && $mem_category_discount===''){
				// try to get own branch discount first
				if(isset($cat_info['category_disc_by_branch'][$branch_id]['member'])){	
					// need to check member discount (not by member type)
					if($mem_category_discount === ''){
						$tmp_disc = '';
						
						// check global member discount - by branch
						if($tmp_disc==='' && isset($cat_info['category_disc_by_branch'][$branch_id]['member']['global'])){
							$tmp_disc = trim($cat_info['category_disc_by_branch'][$branch_id]['member']['global']);
						}
						
						// check global member discount - all branch
						if($tmp_disc==='' && isset($cat_info['category_disc_by_branch'][0]['member']['global'])){
							$tmp_disc = trim($cat_info['category_disc_by_branch'][0]['member']['global']);
						}
						
						$mem_category_discount = $tmp_disc;
					}
				}
			}
	
			// get parent category id
			$cat_id = $cat_info['root_id'];
			
			// member discount less than non-member?
			if($is_member && $mem_category_discount<$category_discount)	$mem_category_discount = $category_discount;
		}	
	}
	
	if($is_member)	$use_cat_disc = $mem_category_discount;
	else	$use_cat_disc = $category_discount;
	
	$price_for_this_promo = $default_price * (1 - mf($use_cat_disc)/100);
	if($price_for_this_promo<$use_price)    $use_price = $price_for_this_promo;
	
	$con->sql_freeresult();
	return $use_price;
}

function get_sku_no_inventory($sku_id){
	global $con;

	// get sku
	$con->sql_query("select * from sku where id=".mi($sku_id));
	$sku = $con->sql_fetchrow();
	$con->sql_freeresult();

	if($sku['no_inventory']!='inherit') return $sku['no_inventory'];
	else{   // is inherit, find by category
		$con->sql_query("select no_inventory from category_cache where category_id=".mi($sku['category_id']));
		$cc = $con->sql_fetchrow();
		$con->sql_freeresult();
		return $cc['no_inventory'];
	}
}

function change_item_replacement_group($ri_id, $sid){
	global $hqcon, $appCore;
	if(!$sid)   return; // invalid sku item id
	if(!$hqcon) $hqcon = connect_hq();  // establish connection to hq

	$hqcon->sql_query("select * from ri_items where sku_item_id=".mi($sid));    // check current group
	$form = $hqcon->sql_fetchrow();
	$prms = array();
	if(!$form){ // currently hv no assign to any group
	    // insert new row
	    if(!$ri_id) return; // assign to no group
		$id = $appCore->generateNewID("ri_items", "ri_id=".mi($ri_id), $prms["hq_con"]);
		$hqcon->sql_query("insert into ri_items (id, ri_id, sku_item_id) values (".$id.", ".mi($ri_id).", ".mi($sid).")");
	}else{
	    if($ri_id){
            if($form['ri_id']!=$ri_id){ // update to new group
				$id = $appCore->generateNewID("ri_items", "ri_id=".mi($ri_id), $prms["hq_con"]);
				$hqcon->sql_query("update ri_items set id=".mi($id).",ri_id=".mi($ri_id)." where sku_item_id=".mi($sid));
			}
		}else{  // remove from group
            $hqcon->sql_query("delete from ri_items where sku_item_id=".mi($sid));
		}
	}
}

function get_user_info_by_colname($user_id, $colname = '*'){
	global $con;

	$success = $con->sql_query("select $colname from user where id=".mi($user_id), false, false);
	if(!$success)   return false;

	if($colname == '*') $ret = $con->sql_fetchrow();
	else    $ret = $con->sql_fetchfield(0);

	$con->sql_freeresult();

	return $ret;
}

function get_random_color()
{
	$r = rand()%106+150;
	$g = rand()%106+150;
	$b = rand()%106+150;

	return array(sprintf("#%02x%02x%02x", $r,$g,$b), $r,$g,$b);
}

function check_upload_file($filename, $extension=''){
    $err = array();

	if($extension){ // got extension checking
		// not the extension we want
	    if (!preg_match('/\.'.$extension.'$/i',$_FILES[$filename]['name'])) {
		 	$err[] = "Please upload a $extension file. not ".$_FILES[$filename]['type'];
		}
	}
	if(!$err){
	    // file cannot be read
        $fp = fopen($_FILES[$filename]['tmp_name'], "r");
		if (!$fp) {
			$err[] = "Faild to read uploaded file";
		}
		fclose($fp);
	}
	return $err;
}

function get_sku_latest_price_type($bid, $sid){
	global $con;

	// escape integer
	$bid = mi($bid);
	$sid = mi($sid);

	if(!$bid || !$sid)  return '';

	$con->sql_query("select if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as price_type
				from
				sku_items si
				left join sku on sku.id=si.sku_id
				left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
				where si.id=$sid");
   $price_type = $con->sql_fetchfield(0);
   $con->sql_freeresult();
   return $price_type;
}

function initial_branch_sb_table($params){
	global $con;

	$bid = $params['branch_id'];
	$year = $params['year']>2000 ? $params['year'] : date('Y');
	$tbl = $params['tbl'];
	$use_con = $params['mysql_con'] ? $params['mysql_con'] : $con;

	// no provide table name
	if(!$tbl){
	    // must provide branch id
        if(!$bid || $year<2007) die('Invalid Branch or year');
        $tbl = 'stock_balance_b'.$bid.'_'.$year;
	}

	// check only run the query if table not exists
	if(!$use_con->sql_query("explain $tbl", false, false)){
		$use_con->sql_query("create table if not exists $tbl (
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
		) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
	}
}

function update_sales_cache_fresh_market_cost($params){
	global $con;
	
	$sku_item_id = mi($params['sku_item_id']);
	$branch_id = mi($params['branch_id']);
	$fresh_market_cost = mf($params['fresh_market_cost']);
	$date_from = $params['date_from'];
	$date_to = $params['date_to'];
	$use_con = $params['mysql_con'] ? $params['mysql_con'] : $con;

	if(!$sku_item_id || !$branch_id)    return;
	
	// get category id
	$con->sql_query("select sku.category_id, sku.sku_type
	from sku_items si
	left join sku on sku.id=si.sku_id
	where si.id=$sku_item_id");
	$temp = $con->sql_fetchrow();
	$con->sql_freeresult();
	$cat_id = mi($temp['category_id']);
	$sku_type = $temp['sku_type'];
	
	$sales_cache_tbl = 'sku_items_sales_cache_b'.$branch_id;
	$cat_cache_tbl = 'category_sales_cache_b'.$branch_id;
	
	$filter = array();
	$filter[] = "sku_item_id=$sku_item_id";
	if($date_from)  $filter[] = "date>=".ms($date_from);
	if($date_to)    $filter[] = "date<=".ms($date_to);
	$filter = "where ".join(' and ', $filter);
	
	$sql = "update $sales_cache_tbl set fresh_market_cost=round(amount*".mf($fresh_market_cost).",2),
	memb_fm_cost=round(memb_amt*".mf($fresh_market_cost).", 2)
	$filter";
	$use_con->sql_query($sql);
	
	// insert cateogry sales
	$filter2 = array();
	$filter2[] = "sku.category_id=$cat_id and sku.sku_type=".ms($sku_type);
	if($date_from)  $filter2[] = "date>=".ms($date_from);
	if($date_to)    $filter2[] = "date<=".ms($date_to);
	$filter2 = "where ".join(' and ', $filter2);
	
	$sql = "replace into $cat_cache_tbl (date, category_id, sku_type, year, month, amount, cost, qty, fresh_market_cost, tax_amount, disc_amt, disc_amt2, memb_qty, memb_amt, memb_tax, memb_disc, memb_disc2, memb_cost, memb_fm_cost) 
	select date, sku.category_id, sku.sku_type, year, month, sum(amount), sum(cost), sum(qty), sum(fresh_market_cost), sum(tax_amount), sum(disc_amt), sum(disc_amt2), sum(memb_qty), sum(memb_amt), sum(memb_tax), sum(memb_disc), sum(memb_disc2), sum(memb_cost), sum(memb_fm_cost)
	from $sales_cache_tbl pos
	left join sku_items si on si.id=pos.sku_item_id
	left join sku on sku.id=si.sku_id
	$filter2
	group by date, category_id, sku_type";
	$use_con->sql_query($sql);
}

function parse_privilege_manager_ini(){
	global $con, $privilege_groupname, $privilege_prefix_group;
	
    // try to parse INI file
    $pv_list = array();
    if($temp_pv_list = @parse_ini_file("admin.privilege_manager.ini", true)){
        //print_r($temp_pv_list);
        $match_str = join("|",array_keys($privilege_prefix_group));

		foreach($temp_pv_list as $pv_code => $data){
		    // find group
            if (preg_match("/^(".$match_str.")/", $pv_code, $match)){
				$grp = $privilege_prefix_group[$match[1]];
			}
			else{
				$grp = "others";
			}

			// split info
		    $r = array();
	        list($r['hq_only'], $r['branch_only'], $r['desc']) = explode(",", $data, 3);
	        $pv_list[$grp][$pv_code] = $r;
		}
	}
	return $pv_list;
}

function config_master_override(){
	global $con, $config_master, $config, $smarty, $default_config, $db_default_connection;
	if(!$default_config)	$default_config = $config;  // store default config
	
	if (!defined('TERMINAL'))	$smarty->assign('default_config', $default_config);
	if(!$con){
        $con = new sql_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3], false);
		if(!$con->db_connect_id) {
		 	//print mysql_error();
			return;
	  	}
	}
	$config_master = array();
	$sql = "select * from config_master";
	$q1 = $con->sql_query($sql, false, false);
	if(!$q1)    return; // no this table

	while($r = $con->sql_fetchassoc($q1)){
	    $config_name = trim($r['config_name']);
	    $v = trim($r['value']);

	    if($r['active']){   // only override if it is active
            switch($r['type']){
				case 'radio':
				    $config[$config_name] = mi($v);
				    if(!$config[$config_name])	unset($config[$config_name]);
				    break;
				case 'str':
				    $config[$config_name] = trim($v);
				    break;
				case 'array':
				    eval('$config[$config_name] = '.$v);
				    break;
				case 'select':
					$config[$config_name] = trim($v);
					if(!$config[$config_name])	unset($config[$config_name]);
				    break;
			}
		}

		$config_master[$config_name] = $r;
	}
	$con->sql_freeresult($q1);
	//print_r($config);
	if (!defined('TERMINAL'))	$smarty->assign('config_master', $config_master);
}

function get_category_info($cat_id){
	global $con, $cache_cat_info;
	$cat_id = mi($cat_id);
	if(!$cat_id)   return false;

	if($cache_cat_info[$cat_id])    return $cache_cat_info[$cat_id];
	$con->sql_query("select c.*, cc.*
	from category c
	left join category_cache cc on cc.category_id=c.id
	where c.id=$cat_id");
	$r = $con->sql_fetchassoc();
	$r['category_disc_by_branch'] = unserialize($r['category_disc_by_branch']);
	$cache_cat_info[$cat_id] = $r;
	$con->sql_freeresult();

	return $cache_cat_info[$cat_id];
}

function get_image_path($bid){
	global $con;
	
	 $con->sql_query("select code, ip from branch where id=".mi($bid));
	 $r = $con->sql_fetchassoc();
	 $con->sql_freeresult();
	 
	 $image_path = get_branch_file_url($r['code'], $r['ip']);
	 return $image_path;
}

function use_new_sku_photo_path($image_path=""){
	if($image_path) $image_path.="/";
	else	$image_path = $_SERVER['DOCUMENT_ROOT']."/";
	return (file_exists($image_path."sku_photos/use_new_sku_photo_path.txt")) ? true : false;
}

function get_sku_apply_item_photos($sku_apply_items_id, $image_path=''){
	global $con, $sessioninfo, $config;

	// escape integer
	$sku_apply_items_id = mi($sku_apply_items_id);

	if(!$sku_apply_items_id)    return array();  // no id = no photo

	// get items info
    $con->sql_query("select sku.apply_branch_id, sai.photo_count
		from sku_apply_items sai
		left join sku on sku.id = sai.sku_id
		where sai.id=$sku_apply_items_id");
	$sku_apply_items = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if($sku_apply_items['photo_count']<=0)  return array(); // no apply items
	
	// same branch, no need image path
	if($sessioninfo['branch_id'] == $sku_apply_items['apply_branch_id'] || $config['single_server_mode']){
		$image_path = '';
	}else{
		// manually get image path if user does not provide
		if(!$image_path){
			$image_path = get_image_path($sku_apply_items['apply_branch_id']);
		}
	}
	
	// check whether photo got move to use new structure or not
	if(use_new_sku_photo_path()){    // use new structure
	    $group_num = ceil($sku_apply_items_id/10000);
		$abs_path = ($image_path ? $image_path:$_SERVER['DOCUMENT_ROOT'])."/sku_photos/apply_photo/".$group_num."/".$sku_apply_items_id."/";
	}else{
        $abs_path = ($image_path ? $image_path:$_SERVER['DOCUMENT_ROOT'])."/sku_photos/".$sku_apply_items_id."/";
	}
	
	$photos = array();
	for($i = 1; $i <= $sku_apply_items['photo_count']; $i++){
		$f = $abs_path.$i.".jpg";
		$f = str_replace("$_SERVER[DOCUMENT_ROOT]/", "", $f);
        $photos[] = $f;
	}
	return $photos;

	//$folder_path = ($image_path?$image_path."/":'')."sku_photos/".$sku_apply_items_id."/";
	//return glob(($image_path?$image_path."/":'')."sku_photos/".$sku_apply_items_id."/*.jpg");
}
	
// child item only can add at HQ, so we only check HQ server for multi server mode
if(!function_exists('get_sku_item_photos')){
	function get_sku_item_photos($sku_item_id, &$item, $skip_remote = false){
		global $config;
		$ret = array();
		$sku_item_id = mi($sku_item_id);
		if(!$item)	$item = array();
		
		if(BRANCH_CODE == 'HQ' || $config['single_server_mode']){
	        if(use_new_sku_photo_path()){
				$group_num = ceil($sku_item_id/10000);
				$abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/actual_photo/".$group_num."/".$sku_item_id."/";
			}else{
				$abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/a/".$sku_item_id."/";
			}
	
			$photo_list = array();
			foreach  (array_merge(glob("$abs_path/*.jpg"),glob("$abs_path/*.JPG"),glob("$abs_path/*.jpeg"),glob("$abs_path/*.JPEG")) as $f){
				$f = str_replace("$_SERVER[DOCUMENT_ROOT]/", "", $f);
				$photo_list[] = $f;
			}
		}else{
		    $hq_path = get_image_path(1);	// get HQ url
		    $HTTP_HOST = $_SERVER['HTTP_HOST'];
		    if(strpos($HTTP_HOST, ':')===false)	$HTTP_HOST .= ":$_SERVER[HTTP_PORT]";
		    
		    $url_to_get_photo = ($hq_path?$hq_path:"http://$HTTP_HOST")."/http_con.php?a=get_sku_item_photo_list&sku_item_id=$sku_item_id&SKIP_CONNECT_MYSQL=1";	        
	        $item['url_to_get_photo'] = $url_to_get_photo;
	        
	        if(!$skip_remote){
				$tmp_photo_list = file_get_contents($url_to_get_photo);
	        	$photo_list = unserialize($tmp_photo_list);
			}
		}
		
		/*foreach  (array_merge(
				glob("$_SERVER[DOCUMENT_ROOT]/sku_photos/a/$sku_item_id/*.jpg"),
				glob("$_SERVER[DOCUMENT_ROOT]/sku_photos/a/$sku_item_id/*.JPG")
			) as $f)
		{
			$ret[] = str_replace("$_SERVER[DOCUMENT_ROOT]/", "", $f);
		}*/
	
		if (isset($config['sku_get_external_photos']))
		{
			if ($config['sku_get_external_photos']['method']=='artno_heading')
			{
				// chop the artno head
				$artno = preg_split('/\s+/',$item['artno']);
				foreach (glob($config['sku_get_external_photos']['path'] . '/' .strtoupper($artno[0]).'*.*') as $f)
				{
					$photo_list[] = str_replace("$_SERVER[DOCUMENT_ROOT]/", "", $f);
				}
			}
		}
		return $photo_list;
	}
}

// get sku promotion items

function get_sku_promotion_photos($sku_item_id, &$item, $skip_remote = false){
	global $config;
	$ret = array();
	$sku_item_id = mi($sku_item_id);
	if(!$item)	$item = array();
	
	if(BRANCH_CODE == 'HQ' || $config['single_server_mode']){
		$group_num = ceil($sku_item_id/10000);
		$abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/promo_photo/".$group_num."/".$sku_item_id."/";
		
		$photo_list = array();
		foreach  (array_merge(glob("$abs_path/*.jpg"),glob("$abs_path/*.JPG"),glob("$abs_path/*.jpeg"),glob("$abs_path/*.JPEG")) as $f){
			$f = str_replace("$_SERVER[DOCUMENT_ROOT]/", "", $f);
			$photo_list[] = $f;
		}
	}else{
		$hq_path = get_image_path(1);	// get HQ url
		$HTTP_HOST = $_SERVER['HTTP_HOST'];
		if(strpos($HTTP_HOST, ':')===false)	$HTTP_HOST .= ":$_SERVER[HTTP_PORT]";
		
		$url_to_get_photo = ($hq_path?$hq_path:"http://$HTTP_HOST")."/http_con.php?a=get_sku_item_promo_photo_list&sku_item_id=$sku_item_id&SKIP_CONNECT_MYSQL=1";	        
		$item['url_to_get_photo'] = $url_to_get_photo;
		
		if(!$skip_remote){
			$tmp_photo_list = file_get_contents($url_to_get_photo);
			$photo_list = unserialize($tmp_photo_list);
		}
	}
	
	return $photo_list;
}


function get_branch_file_url($target_branch_code, $target_branch_ip='')
{
	global $con, $config, $sessioninfo;
	
	$use_curr_url = false;
	
	if ($config['single_server_mode'])
	{
		return "";
	}else{
		if(isset($config['branch_at_hq'])){
			if(BRANCH_CODE == 'HQ' || $config['branch_at_hq'][BRANCH_CODE]){	// currently at HQ, or the branch in hq
				if($target_branch_code == 'HQ' || $config['branch_at_hq'][$target_branch_code]){	// this branch should login to HQ, or is login to hq, no need to change server
					$use_curr_url = true;
				}
			}else{	// currently at other branch which is not in hq server
				if($config['branch_at_hq'][$target_branch_code]){	// this branch should login to HQ
					$target_branch_code = 'HQ';	// use hq url
				}
			}
		}
	}

	if (BRANCH_CODE == $target_branch_code || $use_curr_url)
	{
		$HTTP_HOST = $_SERVER['HTTP_HOST'];
		if(strpos($HTTP_HOST, ':')===false)	$HTTP_HOST .= ":$_SERVER[HTTP_PORT]";
	    return "http://$HTTP_HOST/";
	}
	else
	{
		//if($sessioninfo['u']=='wsatp'){
			$con->sql_query("select bs.ip 
	from branch
	left join branch_status bs on bs.branch_id=branch.id
	where branch.code=".ms($target_branch_code));
			$ip = $con->sql_fetchfield(0);
			$con->sql_freeresult();
			
			if($ip)	return "http://".$ip;
		//}
		
		
		return sprintf($config['no_ip_string'], strtolower($target_branch_code));
	}
}

function get_pos_settings_value($bid, $setting_name){
	global $con;
	
	if(!$bid || !$setting_name) return;
	
	$con->sql_query("select setting_value from pos_settings where branch_id=".mi($bid)." and setting_name=".ms($setting_name));
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	$v = @unserialize($tmp['setting_value']);
	if($v === false){   // cannot be unserialize
		return trim($tmp['setting_value']);
	}else return $v;    // success unserialize
}

function get_membership_card_prefix($bid){
	global $con, $config;
	
	if(!$config['membership_use_card_prefix'] || !$bid)  return '';
	return get_pos_settings_value($bid, 'membership_card_prefix');
}

function get_sku_item_cost_selling($bid, $sid, $date, $params = array()){
  	global $con, $config;

	$ret = array();

	if(!$params || !is_array($params))    die('Invalid Params');
	
	if(in_array('cost', $params)){
	    // cost
        $con->sql_query("select cost_price, grn_cost, avg_cost, date
			from sku_items si
  			left join sku_items_cost_history sich on (sich.sku_item_id = si.id and date < ".ms($date)." and branch_id=".mi($bid).")
  			where si.id = ".mi($sid)."
  			order by sich.date desc limit 1");
	    $r = $con->sql_fetchrow();
	  	$con->sql_freeresult();

	  	$ret['cost'] = ($r['grn_cost']>0) ? $r['grn_cost'] : $r['cost_price'];
	}
    
    if(in_array('selling', $params) || in_array('trade_discount_code', $params)){
        // selling
	  	$con->sql_query("select ifnull(siph.price, si.selling_price) as selling_price, if(siph.price=null, sku.default_trade_discount_code,siph.trade_discount_code) as trade_discount_code, sku.default_trade_discount_code
		  	from sku_items si
		  	left join sku on sku.id=si.sku_id
			left join sku_items_price_history siph on (siph.sku_item_id=si.id and siph.branch_id=".mi($bid)." and  siph.added < ".ms($date).")
			where si.id = ".mi($sid)."
			order by siph.added desc limit 1");
		$r = $con->sql_fetchrow();
		$con->sql_freeresult();
		$ret['selling'] = $r['selling_price'];
		$ret['trade_discount_code'] = $r['trade_discount_code'];
		if($config['sku_always_show_trade_discount'] && !$ret['trade_discount_code'])  $ret['trade_discount_code'] = $r['default_trade_discount_code'];
    }
  	
	return $ret;
}

function encrypt_for_verification($barcode){
	//make sure the key is same as frontend::WARNING==>case sensitive
	$key="wsatp";

    $crypttext = strtoupper(md5(crypt($barcode, $key)));
	//print $barcode.": ".$crypttext."<br/>";
	return $crypttext;
}

function check_and_create_dir($dir, $permission=0777){
	if (!is_dir($dir)){
		if(!mkdir($dir))	return false;	// create folder failed
		chmod($dir,$permission);
	}
	return true;
}

function load_region_branch_array($params = array()){
	global $con, $config, $smarty;
	
	// get those branch hv no region
	$region_branch = array();
	if($config['sku_use_region_price'] && $config['masterfile_branch_region']){
		$tmp_region_code_list = array();
		foreach($config['masterfile_branch_region'] as $region_code=>$rg){
			$tmp_region_code_list[] = $region_code;
		}
		$filter = array();
		$filter[] = "id>1";
		if(isset($params['active']))	$filter[] = "active=".mi($params['active']);

		if(isset($params['inactive_change_price']))	$filter[] = "(active=1 or (active=0 and inactive_change_price=1))";
		else{
			if(isset($params['active']))	$filter[] = "active=".mi($params['active']);
		}
		
		if($filter)	$filter = "where ".join(' and ', $filter);
		else $filter = '';
		
		$con->sql_query("select * from branch $filter order by branch.sequence, branch.code");
		while($r = $con->sql_fetchassoc()){
			$region_code = trim($r['region']);
			if(in_array($region_code, $tmp_region_code_list)){
				$region_branch['got_region'][$region_code][$r['id']] = $r;
			}else{
				$region_branch['no_region'][$r['id']] = $r;
			}
		}
		$con->sql_freeresult();
	}
	if(!defined('TERMINAL'))	$smarty->assign('region_branch', $region_branch);
	return $region_branch;
}

function branch_change_sku_region_selling($prms){
	global $con, $sessioninfo, $config;
	
	
	if(BRANCH_CODE != 'HQ')	die('Only HQ can change branch region selling price.');
	$bid = mi($prms['branch_id']);
	$bcode = get_branch_code($bid);
	$date_added = date("Y-m-d H:i:s", time());
	
	if(!$config['masterfile_branch_region'] || !$config['sku_use_region_price'])	die('Invalid function call');
	
	if($bid<=1)	die('Invalid Branch ID');
	
	$b_info = array();
	$con->sql_query("select * from branch where id=$bid");
	$b_info = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$b_info)	die('Invalid Branch ID');
	
	$region_code = trim($b_info['region']);
	$old_region_code = trim($prms['old_region']); // when do branch update for region
	if(!$region_code)	return false;	// no region, so cannot update
	
	// cant find this region in config
	if(!$config['masterfile_branch_region'][$region_code]){
		die('Invalid Region');
	}
	
	$sid_list = $prms['sid_list'];
	if($sid_list) $filter_sid = " and sirp.sku_item_id in (".join(',', $sid_list).")";
	
	// update normal price
	$sql = "select sirp.*,si.sku_item_code , ifnull(sip.price,si.selling_price) as curr_selling_price, if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as curr_price_type, ifnull(sic.grn_cost, si.cost_price) as curr_cost
	from sku_items_rprice sirp 
	left join sku_items si on si.id=sirp.sku_item_id
	left join sku on sku.id=si.sku_id
	left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
	left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
	where sirp.region_code=".ms($region_code)." and sirp.mprice_type='normal' $filter_sid";
	$q1 = $con->sql_query($sql);
	
	while($r = $con->sql_fetchassoc($q1)){
		$sid = mi($r['sku_item_id']);
		
		// same price and price type
		if ($r['price'] == $r['curr_selling_price'] && $r['trade_discount_code'] == $r['curr_price_type']) continue;
		
		// get cost source
		$q_source = $con->sql_query("select source from sku_items_cost_history where branch_id=$bid and sku_item_id=$sid order by date desc limit 1");
		$tmp = $con->sql_fetchassoc($q_source);
		$con->sql_freeresult($q_source);
		
		$cost_source = $tmp ? $tmp['source'] : 'MASTER SKU';
		
		//branch_id, sku_item_id, added, price, cost, source
		$upd = array();
		$upd['branch_id'] = $bid;
		$upd['sku_item_id'] = $sid;
		$upd['price'] = $r['price'];
		$upd['cost'] = $r['curr_cost'];
		$upd['trade_discount_code'] = $r['trade_discount_code'];
		$upd['last_update'] = $date_added;
		
		$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($upd));
		
		unset($upd['last_update']);
		$upd['user_id'] = $sessioninfo['id'];
		$upd['source'] = $cost_source;
		$upd['added'] = $date_added;
		
		$con->sql_query("insert into sku_items_price_history ".mysql_insert_by_field($upd));
		
		
		$con->sql_query("update sku_items set lastupdate = CURRENT_TIMESTAMP where id = $sid");  
		
		// continue to show detail
		log_br($sessioninfo['id'], "MASTERFILE", $sid, "Price Change for $r[sku_item_code] to $upd[price] (Discount: $upd[trade_discount_code], Branch: $bcode)");
	}
	$con->sql_freeresult($q1);
	
	if($config['masterfile_branch_region'][$old_region_code]){
		// search to get back all sku items from old region code to update it to use master selling price
		$sql = "select sirp.*,si.sku_item_code, si.selling_price as master_price, ifnull(sip.price,si.selling_price) as curr_price, sku.default_trade_discount_code as master_price_type, if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as curr_price_type, ifnull(sic.grn_cost, si.cost_price) as curr_cost
		from sku_items_rprice sirp 
		left join sku_items si on si.id=sirp.sku_item_id
		left join sku on sku.id=si.sku_id
		left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
		left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
		where sirp.region_code=".ms($old_region_code)." and sirp.mprice_type='normal' and sip.last_update != ".ms($date_added)." $filter_sid";
		$q1 = $con->sql_query($sql);
		
		while($r = $con->sql_fetchassoc($q1)){
			$sid = mi($r['sku_item_id']);
			
			// same price and price type
			if ($r['master_price'] == $r['curr_price'] && $r['master_price_type'] == $r['curr_price_type']) continue;
			
			// get cost source
			$q_source = $con->sql_query("select source from sku_items_cost_history where branch_id=$bid and sku_item_id=$sid order by date desc limit 1");
			$tmp = $con->sql_fetchassoc($q_source);
			$con->sql_freeresult($q_source);
			
			$cost_source = $tmp ? $tmp['source'] : 'MASTER SKU';
			
			//branch_id, sku_item_id, added, price, cost, source
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['sku_item_id'] = $sid;
			$upd['price'] = $r['master_price'];
			$upd['cost'] = $r['curr_cost'];
			$upd['trade_discount_code'] = $r['master_price_type'];
			
			$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($upd));
			
			$upd['user_id'] = $sessioninfo['id'];
			$upd['source'] = $cost_source;
			
			$con->sql_query("insert into sku_items_price_history ".mysql_insert_by_field($upd));
			
			
			$con->sql_query("update sku_items set lastupdate = CURRENT_TIMESTAMP where id = $sid");  
			
			// continue to show detail
			log_br($sessioninfo['id'], "MASTERFILE", $sid, "Price Change for $r[sku_item_code] to $upd[price] (Discount: $upd[trade_discount_code], Branch: $bcode)");
		}
		$con->sql_freeresult($q1);
	}
	
	
	// MPRICE
	if (isset($config['sku_multiple_selling_price'])){
		// loop for each mprice
		foreach($config['sku_multiple_selling_price'] as $mprice_type){
			$sql = "select sirp.*,si.sku_item_code , ifnull(simp.price,si.selling_price) as curr_selling_price, if(simp.price is null, sku.default_trade_discount_code, simp.trade_discount_code) as curr_price_type, ifnull(sic.grn_cost, si.cost_price) as curr_cost
	from sku_items_rprice sirp 
	left join sku_items si on si.id=sirp.sku_item_id
	left join sku on sku.id=si.sku_id
	left join sku_items_mprice simp on simp.branch_id=$bid and simp.sku_item_id=si.id and simp.type=sirp.mprice_type
	left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
	where sirp.region_code=".ms($region_code)." and sirp.mprice_type=".ms($mprice_type)." $filter_sid";
			$q_mp = $con->sql_query($sql);
			while($r = $con->sql_fetchassoc($q_mp)){
				$sid = mi($r['sku_item_id']);
		
				// same price and price type
				if ($r['price'] == $r['curr_selling_price'] && $r['trade_discount_code'] == $r['curr_price_type']) continue;
				
				//branch_id, sku_item_id, added, price, cost, source
				$upd = array();
				$upd['branch_id'] = $bid;
				$upd['sku_item_id'] = $sid;
				$upd['price'] = $r['price'];
				$upd['trade_discount_code'] = $r['trade_discount_code'];
				$upd['type'] = $mprice_type;
				$upd['last_update'] = $date_added;
				
				$con->sql_query("replace into sku_items_mprice ".mysql_insert_by_field($upd));
				
				unset($upd['last_update']);
				$upd['added'] = $date_added;
				$upd['user_id'] = $sessioninfo['id'];
				$con->sql_query("insert into sku_items_mprice_history ".mysql_insert_by_field($upd));
				
				$con->sql_query("update sku_items set lastupdate = CURRENT_TIMESTAMP where id=$sid");
				
				// continue to show detail
				log_br($sessioninfo['id'], "MASTERFILE", $sid, "Price Change for $r[sku_item_code] to $upd[price]  (Discount: $r[trade_discount_code] Type: $mprice_type, Branch: $bcode");
			}
			$con->sql_freeresult($q_mp);
			
			// search to get back all sku items from old region code to update it to use master selling price
			if($config['masterfile_branch_region'][$old_region_code]){
				$sql = "select sirp.*,si.sku_item_code , si.selling_price as master_price, ifnull(simp.price,si.selling_price) as curr_price, if(simp.price is null, sku.default_trade_discount_code, simp.trade_discount_code) as master_price_type, simp.trade_discount_code as curr_price_type, ifnull(sic.grn_cost, si.cost_price) as curr_cost
				from sku_items_rprice sirp 
				left join sku_items si on si.id=sirp.sku_item_id
				left join sku on sku.id=si.sku_id
				left join sku_items_mprice simp on simp.branch_id=$bid and simp.sku_item_id=si.id and simp.type=sirp.mprice_type
				left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
				where sirp.region_code=".ms($old_region_code)." and sirp.mprice_type=".ms($mprice_type)." and simp.last_update != ".ms($date_added)." $filter_sid";
				$q_mp = $con->sql_query($sql);
				
				
				while($r = $con->sql_fetchassoc($q_mp)){
					$sid = mi($r['sku_item_id']);

					// same price and price type
					if ($r['master_price'] == $r['curr_price'] && $r['master_price_type'] == $r['curr_price_type']) continue;
					
					//branch_id, sku_item_id, added, price, cost, source
					$upd = array();
					$upd['branch_id'] = $bid;
					$upd['sku_item_id'] = $sid;
					$upd['price'] = $r['master_price'];
					$upd['trade_discount_code'] = $r['master_price_type'];
					$upd['type'] = $mprice_type;
					$upd['last_update'] = $date_added;
					
					$con->sql_query("replace into sku_items_mprice ".mysql_insert_by_field($upd));
					
					unset($upd['last_update']);
					$upd['added'] = $date_added;
					$upd['user_id'] = $sessioninfo['id'];
					$con->sql_query("insert into sku_items_mprice_history ".mysql_insert_by_field($upd));
					
					$con->sql_query("update sku_items set lastupdate = CURRENT_TIMESTAMP where id=$sid");
					
					// continue to show detail
					log_br($sessioninfo['id'], "MASTERFILE", $sid, "Price Change for $r[sku_item_code] to $upd[price]  (Discount: $r[trade_discount_code] Type: $mprice_type, Branch: $bcode");
				}
				$con->sql_freeresult($q_mp);
			}
		}
	}
}

function check_image_exists($imgname){
	global $con;
	
	// check remote image
	if(strpos($imgname, 'http://')){
		$exists = file_exists($imgname);
		if($exists)	return true;	// file exists
	
		// try other method	
		if (preg_match("/\.jpg$/i", $imgname)) $im = @imagecreatefromjpeg($imgname);
		elseif (preg_match("/\.png$/i", $imgname)) $im = @imagecreatefrompng($imgname);
		elseif (preg_match("/\.gif$/i", $imgname)) $im = @imagecreatefromgif($imgname);
		
		if(!$im){	// if cannot access, try fix the spacing and reconnect
			$oldimgname = $imgname;
			// fix spacing problem, convert "+" to "%2520"
			$imgname = urlencode($imgname);
			$imgname = str_replace("+", "%2520", $imgname);
			$imgname = urldecode($imgname);
			
			if($oldimgname != $imgname){	// only try if path got changed
				if (preg_match("/\.jpg$/i", $imgname)) $im = @imagecreatefromjpeg($imgname);
				elseif (preg_match("/\.png$/i", $imgname)) $im = @imagecreatefrompng($imgname);
				elseif (preg_match("/\.gif$/i", $imgname)) $im = @imagecreatefromgif($imgname);
			}
		}
		
		return $im ? true : false;
	}else{
		// check local image
		return file_exists($imgname);
	}
}

function validate_discount_format($discount_pattern){
	if(!$discount_pattern)	return '';
	
	$discount_pattern = preg_replace("/[^0-9\.%+]/", "", $discount_pattern);
    $discount_pattern = preg_replace("/\+$/",'', $discount_pattern);
    return $discount_pattern;
}

function get_discount_amt($amt, $discount_pattern, $params = array()){
	$discount_amt = 0;
	$total_discount_amt = 0;
	
	if($discount_pattern == '')	return 0;
	if($params['currency_multiply']>0)	$currency_multiply = mf($params['currency_multiply']);
	if($params['discount_by_value_multiply'])	$discount_by_value_multiply = mf($params['discount_by_value_multiply']);
	
	// check discount pattern
	$discount_pattern = validate_discount_format($discount_pattern);
    $original_amt = $amt;
    
	if ($discount_pattern != ''){
		$disc_list = explode("+", $discount_pattern);
		if(!$disc_list)	return 0;
		
		foreach($disc_list as $disc){
			if(strpos($disc, '%')!==false){	// discount by %
				$discount_amt = mf($amt * mf($disc)/100);
			}else{	// discount by value
				$discount_amt = mf($disc);
				if($currency_multiply>0){	// multiply currency rate
					$discount_amt = mf($discount_amt*$currency_multiply);
				}
				
				if($discount_by_value_multiply>0){	// maybe more than 1 branch
					$discount_amt = mf($discount_amt*$discount_by_value_multiply);
				}
			}
			
			$total_discount_amt += $discount_amt;
			$amt -= $discount_amt;
		}
	}
	
	if($total_discount_amt > $original_amt)	$total_discount_amt = $original_amt;	// cannot discount more than amt
	//print "total_discount_amt = $total_discount_amt<br />";exit;
	return $total_discount_amt;
}

function check_can_access_custom_report($user_id, $params=array()){
	$control_type = 0;
	$form = $params;
	if(!$form)	return $control_type;	
	
	// is owner
	if($user_id == $form['user_id']){
		$control_type = 2;
		return $control_type;
	}	
	
	// got tick check additional control
	if($form['report_shared'] == 3){
		if(isset($form['report_shared_additional_control_user'][$user_id])){
			if($form['report_shared_additional_control_user'][$user_id]['control_type'] == 'edit')	$control_type = 2;	//custom report can edit
			else  $control_type = 1;
		}
	}else{
		if($form['report_shared'])	$control_type = $form['report_shared'];  //follow the Share report setting
	}
	
	return $control_type;
}

function is_using_terminal(){
	if(defined('TERMINAL'))	return true;
	
	if(php_sapi_name() == 'cli'){
		define("TERMINAL",1);
		error_reporting (E_ALL ^ E_NOTICE);
		return true;
	}
	
	return false;
	
	/*if(!isset($_SERVER['SSH_CONNECTION']) && !isset($_SERVER['SHELL'])){ // from backend
	  	return false;
	}else{ // is from putty/crontab
		define("TERMINAL",1);
		error_reporting (E_ALL ^ E_NOTICE);
		return true;
	}*/
}

function get_sku_sales_trend($bid, $sid, $prms=array()){
	global $con, $sessioninfo, $config;
		
	//$bid = mi($bid);
	//$sid = mi($sid);
	
	if(!$bid || !$sid)	return;
	
	if($prms['qty_sum_method']) $qty_sum_method = $prms['qty_sum_method'];
	else $qty_sum_method = "sum(sc.qty)";
	
	$filters = array();
	$filters[] = "sc.date > ".ms($dt);
	
	if(is_array($bid)) $bid_list = $bid;
	else $bid_list[] = $bid;
	
	if(is_array($sid)) $filters[] = "sc.sku_item_id in (".join(",", $sid).")";
	else $filters[] = "sc.sku_item_id = ".mi($sid);
	
	$data=array();
	$dt = date('Y-m-d', strtotime('-1 year'));
	$curr_times = strtotime(date("Y-m-d"));
	foreach($bid_list as $bid){
		$sql="select sc.date, sc.year, sc.month, $qty_sum_method as qty 
			  from sku_items_sales_cache_b".mi($bid)." sc 
			  left join sku_items si on si.id=sc.sku_item_id
			  left join uom on uom.id=si.packing_uom_id
			  where ".join(" and ", $filters)."
			  group by sc.date";

		$q1 = $con->sql_query($sql);
		
		while($r=$con->sql_fetchassoc($q1)){
			$sales_times = strtotime($r['date']);
			$times_diff = $curr_times - $sales_times;
			foreach(array(1,3,6,12) as $mm){
				$st_times = $mm * 30 * strtotime("+1 day", 0); // convert sales trend month into seconds
				if ($times_diff <= $st_times){
					$data['sales_trend']['qty'][$mm]+=$r['qty'];
				}
			}
		}
	}

	if($data) ksort($data['sales_trend']['qty']);
	/*{
		// take away devide by branch request by tommy		
		if($divide){
			foreach($data['sales_trend']['qty'] as $k=>$v)
				$data['sales_trend']['qty'][$k]=$v/$total_branch;
		}
	}*/

	return $data;
}

function get_sku_items_details_by_barcode($barcode){
	global $con,$config;
	
	$barcode=ms($barcode);
	
	if ($config['pos_check_barcode']){
		foreach($config['pos_check_barcode'] as $col_name){
			$filter_or[]="$col_name=$barcode";
		}
	}else{
		$filter_or[]="sku_item_code=$barcode";
		$filter_or[]="link_code=$barcode";
		$filter_or[]="mcode=$barcode";
		//$filter_or[]="artno=$barcode";
	}

	$filter = implode(" or ", $filter_or);
	
	$rid=$con->sql_query("select id as sku_item_id, sku_item_code, artno, mcode, link_code, receipt_description, cost_price, selling_price from sku_items 
					where ($filter) limit 100");
	$total = $con->sql_numrows($rid);

	if ($total>0){
		while($r=$con->sql_fetchassoc($rid)){
			$result[]=$r;
		}
		
	}

	$con->sql_freeresult($rid);
	unset($r,$rid);
	return $result;
}

function get_cat_tree_info($cat_id, $tree_str = '', $include_own_cat = false){
	global $con;
	
	if(!$tree_str){
		$cat_info = get_category_info($cat_id);
		if(!$cat_info)	return array();
		$tree_str = $cat_info['tree_str'];
	}
	
	$cat_tree_info = array();
	$temp = str_replace(")(", ",",  str_replace("(0)", "", $tree_str));
	if($temp){
        $con->sql_query("select id,description,code from category where id in $temp order by level");
        while ($r = $con->sql_fetchassoc()){
            $cat_tree_info[] = $r;
		}
		$con->sql_freeresult();
	}
	
	if($include_own_cat && $cat_id){
		$q2 = $con->sql_query("select id, code, description from category where id = ".mi($cat_id));
		$tmp_cat = $con->sql_fetchassoc($q2);
		$con->sql_freeresult($q2);
		$cat_tree_info[] = $tmp_cat;
	}
	
	return $cat_tree_info;
}

function load_branch_list(){
	global $con, $smarty;
	
	$branch_list = array();
	$con->sql_query("select * from branch where active=1 order by sequence,code");
	while($r = $con->sql_fetchassoc()){
		$branch_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	if(!defined('TERMINAL'))	$smarty->assign('branch_list', $branch_list);
	return $branch_list;
}

function is_ajax(){
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? true : false;
}

function get_grn_barcode_info($grn_barcode,$print_error=false){
	global $con,$LANG,$config,$sessioninfo;
	
	// do not check category privilege
	if ($grn_barcode){
		$grn_barcode_type = mi($_REQUEST['grn_barcode_type']);
		if(!$grn_barcode_type && $config['enable_grn_barcoder']){	// default	
			if (preg_match("/^00/", $grn_barcode)){	// form ARMS' GRN barcoder
				$sku_item_id=mi(substr($grn_barcode,0,8));
				$qty_pcs=mi(substr($grn_barcode,8,4));
				$con->sql_query("select id as sku_item_id,sku_item_code,receipt_description,active from sku_items where id = ".mi($sku_item_id));
				$si_info=$con->sql_fetchassoc();
				
				if (!$si_info['sku_item_id']){ // invalid item
					$si_info['err'] = sprintf($LANG['DO_INVALID_ITEM'],$sku_item_id);
					if($print_error) fail($si_info['err']);
				}elseif(!$si_info['active'] && !$_REQUEST['show_inactive']){ // is inactive item
					$si_info['err'] = $LANG['PO_ITEM_IS_INACTIVE'];
					if($print_error) fail($si_info['err']);
				}
				
				$si_info['qty_pcs']=$qty_pcs;
				$con->sql_freeresult();
			}elseif(strlen($grn_barcode) == 12 || strlen($grn_barcode) == 13){
				$config['barcode_unit_code_prefix'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_unit_code_prefix");
				$config['barcode_unit_code_mcode_length'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_unit_code_mcode_length");
				if(!$config['barcode_unit_code_mcode_length']) $config['barcode_unit_code_mcode_length'] = 5;
				$config['barcode_price_code_prefix'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_price_code_prefix");
				$config['barcode_price_code_mcode_length'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_price_code_mcode_length");
				if(!$config['barcode_price_code_mcode_length']) $config['barcode_price_code_mcode_length'] = 5;
				
				if ($config['barcode_unit_code_prefix']){
					if (preg_match("/^".$config['barcode_unit_code_prefix']."/", $grn_barcode, $unit_matches)){
						$mcode = substr($grn_barcode, strlen($unit_matches[0]), $config['barcode_unit_code_mcode_length']);
						$qty = substr($grn_barcode, strlen($unit_matches[0]) + $config['barcode_unit_code_mcode_length'], 12-strlen($unit_matches[0])-$config['barcode_unit_code_mcode_length']);
					}
				}

				if ($config['barcode_price_code_prefix']){
					if (preg_match("/^".$config['barcode_price_code_prefix']."/", $grn_barcode, $price_matches)){
						$mcode = substr($grn_barcode, strlen($price_matches[0]), $config['barcode_price_code_mcode_length']);
						$price = substr($grn_barcode, strlen($price_matches[0]) + $config['barcode_price_code_mcode_length'], 12-strlen($price_matches[0])-$config['barcode_price_code_mcode_length']);
						$selling_price = $price/100;
					}
				}

				if ($mcode){
					$q1 = $con->sql_query("select si.*, si.id as sku_item_id, ifnull(sip.price, si.selling_price) as selling_price
										   from sku_items si
										   left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($sessioninfo['branch_id'])."
										   where si.active=1 and si.mcode = ".ms($mcode));

					$si_info = $con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);

					if($si_info){
						if($si_info['scale_type']=="-1"){
							$q2 = $con->sql_query("select * from sku where id=".mi($si_info['sku_id']));
							$r = $con->sql_fetchrow($q2);
							$con->sql_freeresult($q2);
							$si_info['scale_type'] = $r['scale_type'];
							unset($r);
						}
						$con->sql_freeresult($q1);

						if ($qty && !$price){
							$si_info['qty_pcs'] = $qty/100;// the unit for $qty is in g, so $qty/100 to make the unit in 100g.
						}elseif (isset($selling_price)){
							// todo: what if selling price is zero? what qty should i carry?
							if ($si_info['selling_price'] > 0)
								$si_info['qty_pcs'] = round($selling_price/$si_info['selling_price'], 2);
							else
								$si_info['qty_pcs'] = 1;
						}
					}else{
						$si_info = array();
						$si_info['err'] = sprintf($LANG['DO_INVALID_ITEM'], $grn_barcode);
						if($print_error) fail($si_info['err']);
					}
				}else{
					$si_info = array();
					$si_info['err'] = sprintf($LANG['DO_INVALID_ITEM'], $grn_barcode);
					if($print_error) fail($si_info['err']);
				}
			}elseif(strlen($grn_barcode) == 17 || strlen($grn_barcode) == 18){
				$config['barcode_price_n_unit_code_prefix'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_price_n_unit_code_prefix");
				$config['barcode_price_n_unit_mcode_length'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_price_n_unit_mcode_length");
				if(!$config['barcode_price_n_unit_mcode_length']) $config['barcode_price_n_unit_mcode_length'] = 5;
				$config['barcode_total_price_n_unit_code_prefix'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_total_price_n_unit_code_prefix");
				$config['barcode_total_price_n_unit_mcode_length'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_total_price_n_unit_mcode_length");
				if(!$config['barcode_total_price_n_unit_mcode_length']) $config['barcode_total_price_n_unit_mcode_length'] = 5;
				$config['weight_fraction'] = get_pos_settings_value($sessioninfo['branch_id'], "weight_fraction");

				if(isset($config['barcode_price_n_unit_code_prefix']) && $config['barcode_price_n_unit_code_prefix'] == substr($grn_barcode,0,2)){
					if (preg_match("/^".$config['barcode_price_n_unit_code_prefix']."/", $grn_barcode, $price_unit_matches)){
						$mcode = substr($grn_barcode, strlen($price_unit_matches[0]), $config['barcode_price_n_unit_mcode_length']);
						
						$q1 = $con->sql_query("select *, id as sku_item_id from sku_items where active=1 and mcode = ".ms($mcode));
						$si_info = $con->sql_fetchrow($q1);
						if($si_info['scale_type']=="-1"){
							$q2 = $con->sql_query("select * from sku where id=".mi($si_info['sku_id']));
							$r = $con->sql_fetchrow($q2);
							$con->sql_freeresult($q2);
							$si_info['scale_type'] = $r['scale_type'];
							unset($r);
						}
						$con->sql_freeresult($q1);

						if ($si_info){
							$price_length = 5;
							$mcode_length = strlen($config['barcode_price_n_unit_code_prefix'])+$config['barcode_price_n_unit_mcode_length'];
							$price_unit_code = substr($grn_barcode,$mcode_length);	
							$price = substr($price_unit_code,0,5);
							$selling_price = $price/100;
							$qty = substr($price_unit_code,5,5);
							
							if($si_info['scale_type']==1) $unit_qty = intval($qty);
							else{
								if(intval($qty)<20){
									$si_info = array();
								}else{
									// the unit is base on scale type or weight fraction (if scale type is "weight" then use weight_fraction divide
									$unit_qty = $qty/$config['weight_fraction'];
								}
							}
								
							if($si_info){
								if($si_info['scale_type']==0) $si_info = array();
								else{
									if($selling_price <= 0.01) $si_info = array();
									else{
										$si_info['selling_price'] = $selling_price;
										$si_info['qty_pcs'] = $unit_qty;
									}
								}
							}
						}else{
							$si_info = array();
						}
					}
				}elseif(isset($config['barcode_total_price_n_unit_code_prefix']) && $config['barcode_total_price_n_unit_code_prefix'] == substr($grn_barcode,0,2)){
					if (preg_match("/^".$config['barcode_total_price_n_unit_code_prefix']."/", $grn_barcode, $total_price_unit_matches)){
						$mcode = substr($grn_barcode, strlen($total_price_unit_matches[0]), $config['barcode_total_price_n_unit_mcode_length']);
						$q1 = $con->sql_query("select *, id as sku_item_id from sku_items where active=1 and mcode = ".ms($mcode));
						$si_info = $con->sql_fetchrow($q1);
						if($si_info['scale_type']=="-1"){
							$q2 = $con->sql_query("select * from sku where id=".mi($si_info['sku_id']));
							$r = $con->sql_fetchrow($q2);
							$con->sql_freeresult($q2);
							$si_info['scale_type'] = $r['scale_type'];
							unset($r);
						}
						$con->sql_freeresult($q1);

						if (is_array($si_info)){
							$price = substr($grn_barcode,(strlen($config['barcode_total_price_n_unit_code_prefix'])+$config['barcode_total_price_n_unit_mcode_length']),5);
							$selling_price = $price/100;
							$qty = substr($grn_barcode,(strlen($config['barcode_total_price_n_unit_code_prefix'])+$config['barcode_total_price_n_unit_mcode_length']+5),5);
							
							if($si_info['scale_type']==1) $unit_qty = intval($qty);
							else{
								if(intval($qty)<20) $si_info = array();
								else $unit_qty = $qty/$config['weight_fraction'];  // the unit is base on scale type or weight fraction (if scale type is "weight" then use weight_fraction divide
							}
							
							if($si_info){
								if($si_info['scale_type']==0) $si_info = array();
								else{
									if($selling_price<= 0.01) $si_info = array();
									else{
										$si_info['selling_price'] = $selling_price/$unit_qty;
										$si_info['qty_pcs'] = $unit_qty;
									}
								}
							}
						}else{
							$si_info = array();
						}
					}
				}

				// calculate cost price
				if($si_info['selling_price']){
					$q1 = $con->sql_query("select sku.*, c.department_id from sku left join category c on c.id = sku.category_id where sku.id = ".mi($si_info['sku_id']));
					$sku = $con->sql_fetchassoc($q1);
					
					if($sku){
						if ($sku['trade_discount_type']==1){
							/// use brand table
							$q1 = $con->sql_query("select rate from brand_commission where department_id=".mi($sku['department_id'])." and brand_id = ".mi($sku['brand_id'])." and branch_id = ".mi($sessioninfo['branch_id'])." and skutype_code = ".ms($sku['default_trade_discount_code'])) or die(mysql_error());
						}elseif ($sku['trade_discount_type']==2){
							// use vendor table
							$q1 = $con->sql_query("select rate from vendor_commission where department_id=".mi($sku['department_id'])." and vendor_id = ".mi($sku['vendor_id'])." and branch_id = ".mi($sessioninfo['branch_id'])." and skutype_code = ".ms($sku['default_trade_discount_code'])) or die(mysql_error());
						}
						$tdc = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
						
						if($tdc){
							$si_info['new_cost_price'] = $si_info['selling_price'] - ($si_info['selling_price']*$tdc['rate']/100);
						}else{
							$si_info['new_cost_price'] = "";
						}
					}
				}

				if(!$si_info){
					$si_info['err'] = sprintf($LANG['DO_INVALID_ITEM'], $grn_barcode);
					if($print_error) fail($si_info['err']);
				}
			}else{	// from ATP GRN Barcode, try to search the link-code 
				$linkcode=substr($grn_barcode,0,7);
				$qty_pcs=mi(substr($grn_barcode,7,5));
				$con->sql_query("select id as sku_item_id,sku_item_code,receipt_description,active from sku_items where link_code = ".ms($linkcode));
				$si_info=$con->sql_fetchassoc();
				$si_info['qty_pcs']=$qty_pcs;
				$con->sql_freeresult();
				if (!$si_info['sku_item_id']){
					$si_info['err'] = sprintf($LANG['DO_INVALID_ITEM'],$linkcode);	
					if($print_error) fail($si_info['err']);
				}elseif(!$si_info['active'] && !$_REQUEST['show_inactive']){ // is inactive item
					$si_info['err'] = $LANG['PO_ITEM_IS_INACTIVE'];
					if($print_error) fail($si_info['err']);
				}
			}
		}else{
			switch($grn_barcode_type){
				case 1:	// arms code, mcode, link code
					if (strlen($grn_barcode) == 13) {
						$grn_barcode2 = substr($grn_barcode,0,12);
						$in_str = ms($grn_barcode).','.ms($grn_barcode2);
						$con->sql_query("select id as sku_item_id,sku_item_code,receipt_description,active from sku_items where sku_item_code in ($in_str) or mcode in ($in_str) or artno in ($in_str) or link_code in ($in_str) limit 1");
					}
					else {
						$con->sql_query("select id as sku_item_id,sku_item_code,receipt_description,active from sku_items where sku_item_code=".ms($grn_barcode)." or mcode=".ms($grn_barcode)." or artno=".ms($grn_barcode)." or link_code=".ms($grn_barcode)." limit 1");
					}
					$si_info=$con->sql_fetchassoc();
					$con->sql_freeresult();
					if (!$si_info['sku_item_id']){
						$si_info['err'] = sprintf($LANG['DO_INVALID_ITEM'],$grn_barcode);	
						if($print_error) fail($si_info['err']);
					}elseif(!$si_info['active'] && !$_REQUEST['show_inactive']){ // is inactive item
						$si_info['err'] = $LANG['PO_ITEM_IS_INACTIVE'];
						if($print_error) fail($si_info['err']);
					}

					break;
				default:
					$si_info['err'] = "Invalid GRN Barcode Type";	
					if ($print_error)	fail("Invalid GRN Barcode Type");
					break;
			}
		}
	}

	return $si_info;
}

function write_process_status($modulename, $taskname, $statusname, $status, $is_clear = false){
	global $sessioninfo;
	
	$txt = dirname(__FILE__)."/process_status.txt";
	$str = file_get_contents($txt);
	
	$arr = unserialize($str);
	if($is_clear){
		if($modulename==='' && $taskname==='' && $statusname === ''){
			unset($arr[$sessioninfo['id']]);
		}elseif($modulename!=='' && $taskname==='' && $statusname === ''){
			unset($arr[$sessioninfo['id']][$modulename]);
		}elseif($modulename!=='' && $taskname!=='' && $statusname === ''){
			unset($arr[$sessioninfo['id']][$modulename][$taskname]);
		}elseif($modulename!=='' && $taskname!=='' && $statusname !== ''){
			unset($arr[$sessioninfo['id']][$modulename][$taskname][$statusname]);
		}
	}else{
		$arr[$sessioninfo['id']][$modulename][$taskname][$statusname] = strval($status);
	}
	
	$str = serialize($arr);
	file_put_contents($txt, $str);
}

function initial_branch_vsh_table($bid, $tbl = ''){
	global $con;

	// no provide table name
	if(!$tbl){
	    // must provide branch id
        $tbl = 'vendor_sku_history_b'.$bid;
	}

	// check only run the query if table not exists
	if(!$con->sql_query("explain $tbl", false, false)){
		$con->sql_query("create table if not exists $tbl(
				sku_item_id int,
				from_date date default 0,
				to_date date default 0,
				vendor_id int,
				index sid_n_from_date_n_to_date (sku_item_id, from_date, to_date),
				index from_date_n_to_date_n_vendor_id (from_date, to_date, vendor_id),
				index vendor_id_n_date (vendor_id,from_date,to_date)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");

	}
}

function get_branch_id($BRANCH_CODE){
	global $con;
	$con->sql_query("select id from branch where code = ".ms($BRANCH_CODE));
	$bid = mi($con->sql_fetchfield(0));
	$con->sql_freeresult();
	
	return $bid;
}

function create_blank_approval_data($params, $mysql_connection = ''){
	global $con;
	if(!$mysql_connection)	$mysql_connection = $con;
	
	$branch_id = mi($params['branch_id']);
	$reftable = $params['reftable'];
	$ref_id = mi($params['ref_id']);
	$force_use_app_his = mi($params['force_use_app_his']);
	
	$upd = array();
	$tbl = 'branch_approval_history';
	if(!$force_use_app_his){
		$upd['branch_id'] = $branch_id;
		if(!$upd['branch_id'])	die("Invalid branch to create approval history.");
	}else	$tbl = 'approval_history';
	
	$upd['ref_table'] = $reftable;
	$upd['ref_id'] = $ref_id;
	$upd['active'] = 1;
	$upd['approvals'] = '|';
	$upd['added'] = 'CURRENT_TIMESTAMP';
	
	$mysql_connection->sql_query("insert into $tbl ".mysql_insert_by_field($upd));
	$app_id = $mysql_connection->sql_nextid();
	
	$ret = array();
	$ret['id'] = $app_id;
	return $ret;
}

// pass $date=-1 for creating blank table
function update_sales_cache($bid, $date = '', $date_from = '', $date_to = '', $params = array()){
	global $con, $config, $appCore;
	$bid = mi($bid);
	// check parameters
	if(!$bid)   die("Invalid Branch ID");
	if(!$date&&!$date_from&&!$date_to)  die("Invalid Date");
	$write_process_status = $params['write_process_status'];
	
	$starttime = microtime(true);
	$changed_sku = array();
	//list($y,$m,$d) = split("-", $date);
	if($date){
        $date_filter[] = "pos.date=".ms($date);
        $sb_tbl_1 = 'stock_balance_b'.$bid.'_'.date('Y', strtotime($date));
		if($date != -1){
			$appCore->posManager->update_pos_membership_guid($bid, $date);
		}
	}
	else{
		if($date_from){
		    $date_filter[] = "pos.date>=".ms($date_from);
            $sb_tbl_1 = 'stock_balance_b'.$bid.'_'.date('Y', strtotime($date_from));
		}
		if($date_to){
            $date_filter[] = "pos.date<=".ms($date_to);
            $sb_tbl_2 = 'stock_balance_b'.$bid.'_'.date('Y', strtotime($date_to));
		}
		if($date_from && $date_to) $appCore->posManager->update_pos_membership_guid($bid, '', $date_from, $date_to);
	}
    $date_filter = join(" and ", $date_filter);

	// create sku sales cache table
	$sales_cache_tbl = "sku_items_sales_cache_b$bid";
	$cat_cache_tbl = "category_sales_cache_b$bid";
	$member_cache_tbl = "member_sales_cache_b$bid";
	$pwp_cache_tbl = "pwp_sales_cache_b$bid";
	$dept_tran_cache_tbl = "dept_trans_cache_b$bid";
	$sales_target_tbl = "sales_target_b$bid";
	$daily_sales_cache_tbl = "daily_sales_cache_b$bid";
	$sa_sales_cache_tbl = "sa_sales_cache_b$bid";
	$sa_range_sales_cache_tbl = "sa_range_sales_cache_b$bid";
	
	$con->sql_query("create table if not exists $sales_cache_tbl (
		sku_item_id int,
		date date,
		year integer,
		month integer,
		amount double,
		disc_amt double,
		cost double,
		qty double,
		fresh_market_cost double,
		last_grn_vendor_id int,
		tax_amount double,
		disc_amt2 double,
		memb_qty double not null default 0,
		memb_amt double not null default 0,
		memb_tax double not null default 0,
		memb_disc double not null default 0,
		memb_disc2 double not null default 0,
		memb_cost double not null default 0,
		memb_fm_cost double not null default 0,
		primary key (date,sku_item_id),
		index(sku_item_id),
		index(year, month),
		index(last_grn_vendor_id)) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");

    $con->sql_query("create table if not exists $cat_cache_tbl (
		category_id int,
		date date not null,
		sku_type char(10),
		year integer,
		month integer,
		amount double,
		cost double,
		qty double,
		fresh_market_cost double,
		tax_amount double,
		disc_amt double,
		disc_amt2 double,
		memb_qty double not null default 0,
		memb_amt double not null default 0,
		memb_tax double not null default 0,
		memb_disc double not null default 0,
		memb_disc2 double not null default 0,
		memb_cost double not null default 0,
		memb_fm_cost double not null default 0,
		primary key (date, category_id, sku_type),
		index(category_id), index(sku_type), index(year, month)) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");

    $con->sql_query("create table if not exists $member_cache_tbl (
		date date,
		year int(4),
		month int(2),
		day int(2),
		hour int,
		card_no char(20),
		race enum('C','I','M','O') default 'O',
		transaction_count int,
		amount double,
		memb_qty double not null default 0,
		memb_tax double not null default 0,
		memb_disc double not null default 0,
		memb_disc2 double not null default 0,
		memb_cost double not null default 0,
		primary key(date,hour,card_no, race),
		index(year),index(month),index(day),index(race),index(transaction_count,amount)) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
    $con->sql_query("create table if not exists $pwp_cache_tbl (
		date date,
		year int(4),
		month int(2),
		day int(2),
		transaction_count int not null default 0,
		qty double,
		amount double not null default 0,
		cost double not null default 0,
		sku_item_code char(12),
		primary key(date,sku_item_code),
		index(year),index(month),index(day)) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
	$con->sql_query("create table if not exists $dept_tran_cache_tbl (
		date date,
		year integer(4),
		month integer(2),
		pos_id int,
		counter_id int,
		department_id int,
		member_no char(16) not null,
		primary key (date, pos_id, counter_id, department_id),
		index(department_id, year, month),
		index member_no (member_no)) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
	$con->sql_query("create table if not exists $sales_target_tbl (
        date date,
		year int NOT NULL DEFAULT 0,
		month int NOT NULL DEFAULT 0,
		sku_type enum('CONSIGN','OUTRIGHT','CONCESS') NOT NULL DEFAULT 'CONSIGN',
		department_id int NOT NULL DEFAULT 0,
		target double DEFAULT NULL,
		PRIMARY KEY (date,year,month,department_id,sku_type), 
		index sku_type(sku_type,year,month)) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
        
        // 4/4/2012 1:53:43 PM Justin
        /*$con->sql_query("create table if not exists `return_policy_sales_cache` (
                sku_item_id int(11) not null,
                branch_id int(11) not null,
                date date not null,
                count int(11) default 0,
                refund double default 0,
                expired_count int(11) default 0,
                charges double default 0,
                primary key (branch_id, sku_item_id),
                index sku_item_id(sku_item_id)
                )");*/
	$con->sql_query("create table if not exists $daily_sales_cache_tbl(
					date date,
					year int,
					month int,
					service_charge_amt double default 0,
					service_charge_gst_amt double default 0,
					total_gst_amt double default 0,
					deposit_rcv_amt double default 0,
					deposit_used_amt double default 0,
					deposit_rcv_gst_amt double default 0,
					deposit_used_gst_amt double default 0,
					rounding_amt double default 0,
					curr_adj_amt double default 0,
					over_amt double default 0,
					memb_service_charge_amt double not null default 0,
					memb_service_charge_gst_amt double not null default 0,
					memb_total_gst_amt double not null default 0,
					memb_deposit_rcv_amt double not null default 0,
					memb_deposit_used_amt double not null default 0,
					memb_deposit_rcv_gst_amt double not null default 0,
					memb_deposit_used_gst_amt double not null default 0,
					memb_rounding_amt double not null default 0,
					memb_curr_adj_amt double not null default 0,
					memb_over_amt double not null default 0,
					primary key(date),
					index ym(year, month)) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");

	$con->sql_query("create table if not exists $sa_sales_cache_tbl (
					 sa_id int(11),
					 date date not null default 0,
					 year int(4),
					 month int(2),
					 amount double,
					 cost double,
					 commission_amt double,
					 qty double,
					 transaction_count int(11) not null default 0,
					 sales_type enum('pos','open', 'credit_sales'),
					 use_commission_ratio tinyint(1) default 0,
					 primary key(sa_id,year,month,date,sales_type),
					 index(sa_id), index(sales_type), index(date)) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
	
	$con->sql_query("create table if not exists $sa_range_sales_cache_tbl (
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
					 index(sa_id)) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
				
	if($sb_tbl_1)   initial_branch_sb_table(array('tbl'=>$sb_tbl_1));
	if($sb_tbl_2 && $sb_tbl_1 != $sb_tbl_2)   initial_branch_sb_table(array('tbl'=>$sb_tbl_2));
	if(function_exists('initial_branch_vsh_table'))	initial_branch_vsh_table($bid);

	if ($date==-1){	// just for creating blank table
		// create all stock balance table using the oldest hq year, but more than 2010
		$min_year = 0;
		$con->sql_query("show tables like 'stock_balance_b1\\_%'");
		while($r = $con->sql_fetchrow()){
			list($dum1, $dum2, $dum3, $tmp_year) = explode("_", $r[0]);
			if($tmp_year >= 2010 && ($min_year==0 || $min_year > $tmp_year))	$min_year = $tmp_year;
		}
		$con->sql_freeresult();
		if($min_year >= 2010){
			$max_year = mi(date("Y"));
			for($tmp_year = $min_year; $tmp_year <= $max_year; $tmp_year++){
				$tmp_sb_tbl = 'stock_balance_b'.$bid.'_'.$tmp_year;
				initial_branch_sb_table(array('tbl'=>$tmp_sb_tbl));
			}
		}
		
		return;
	} 

	// write percentage
	if($write_process_status)	write_process_status('counter_collection','finalize','per', 0);
	
	// clear data for selected date
	$con->sql_query("delete from $sales_cache_tbl where ".str_replace("pos.", "", $date_filter));
    $con->sql_query("delete from $cat_cache_tbl where ".str_replace("pos.", "", $date_filter));
    $con->sql_query("delete from $member_cache_tbl where ".str_replace("pos.", "", $date_filter));
    $con->sql_query("delete from $pwp_cache_tbl where ".str_replace("pos.", "", $date_filter));
  	$con->sql_query("delete from $dept_tran_cache_tbl where ".str_replace("pos.", "", $date_filter));
	$con->sql_query("delete from $daily_sales_cache_tbl where ".str_replace("pos.", "", $date_filter));
	$con->sql_query("delete from pos_finalised_error where branch_id=$bid and ".str_replace("pos.", "", $date_filter));
	
	if($config['masterfile_enable_sa']){
		$con->sql_query("delete from $sa_sales_cache_tbl where ".str_replace("pos.", "", $date_filter));
		
		if($date){
			$sa_date_from = $date;
			$sa_date_to = $date;
		}else{
			if($date_from) $sa_date_from = $date_from;
			else $sa_date_from = date("Y-m-d", strtotime("-1 day")); // pick yesterday as date from if doesn't assign
			if(date_to) $sa_date_to = $date_to;
			else $sa_date_to = date("Y-m-d"); // pick current date as date to if doesn't assign
		}
		
		// mark the date for sales agent to recalculate the sales cache
		for($i = strtotime($sa_date_from); $i <= strtotime($sa_date_to); $i = $i+86400) {
			$tmp_sa_date = date('Y-m-d', $i);
			$sa_ins = array();
			$sa_ins['branch_id'] = $bid;
			$sa_ins['date'] = $tmp_sa_date;
			$con->sql_query("replace into sa_sales_cache_monitoring ".mysql_insert_by_field($sa_ins));
		}
	}
	
    if($config['enable_sn_bn']){
        $con->sql_query("delete from pos_items_sn_history where ".str_replace("pos.", "", $date_filter)." and branch_id = ".mi($bid));
    }

	// get all sales items
	if(defined('TERMINAL')){
	    print "\nRetrieving data... Branch ID#$bid";
	}
	
	$con->sql_query("create temporary table if not exists tmp_last_grn_vendor_id_b$bid (date date, sku_item_id int, last_grn_vendor_id int, primary key(date, sku_item_id))");
	
	if(!$config['consignment_modules']) $filter_pos_finalized = " and pf.finalized=1";
	
	$sql = "select pos.id as pos_id,pos.counter_id,pos.date,pi.sku_item_id,year(pos.date) as year,month(pos.date) as month,day(pos.date) as day,pi.price,pi.discount,pi.qty,pi.trade_discount_code,sku.trade_discount_type,
			sku.sku_type,c.department_id,sku.brand_id,sku.vendor_id, si.cost_price as master_cost,pi.mprice_type,si.sku_item_code,pos.pos_time,pos.member_no,pi.item_id,
	if(pos.race='' and pos.member_no<>'', (select if(m.race is null or m.race = '', 'O', substring(m.race,1,1))
										 from membership_history mh
										 left join membership m on m.nric = mh.nric
										 where mh.card_no=pos.member_no limit 1), pos.race) as race,
										 (select sum(pp.amount)
											from pos_payment pp 
											where pp.branch_id=pos.branch_id and pp.counter_id=pos.counter_id and pp.date=pos.date and pp.pos_id=pos.id and pp.type='Rounding') as rounding_amt,
										 (select sum(pp.amount)
											from pos_payment pp 
											where pp.branch_id=pos.branch_id and pp.counter_id=pos.counter_id and pp.date=pos.date and pp.pos_id=pos.id and pp.type='Currency_Adjust') as ca_amt,
	if(sku.is_fresh_market='inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market,pi.trade_in_by,pi.writeoff_by, pos.cashier_id,pi.discount2,pi.tax_amount,
	pos.service_charges, pos.service_charges_gst_amt,pos.total_gst_amt,pos.pos_more_info,pos.amount as pos_amt,pos.deposit as is_deposit,pos.amount_tender,pos.amount_change
from pos 
left join pos_items pi on pos.id=pi.pos_id and pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id
left join sku_items si on si.id=pi.sku_item_id
left join sku on sku.id=si.sku_id
left join category c on c.id=sku.category_id
left join category_cache cc on cc.category_id=sku.category_id
left join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date
where pos.branch_id=$bid and $date_filter and pos.cancel_status=0 $filter_pos_finalized order by pi.date,pi.pos_id,pi.counter_id";
	//die($sql);
	$q1 = $con->sql_query($sql);

	$total_rows = mi($con->sql_numrows());
	$total_rows2 = $total_rows;

	if(defined('TERMINAL')){
        print "\n$total_rows rows to process...\n";
	}

	$last_pwp_tran = '';
	$last_mem_tran = '';
	$last_tran_key = '';
	
	while($r = $con->sql_fetchrow($q1)){
		// this item already write-off
		if($r['trade_in_by'] && $r['writeoff_by'])	continue;
		
	    $upd = array();
	    $pwp = array();
	    $mem = array();
	    $dept_tran = array();
	    $sku_items_cost_history = array();

	    $dd = $r['date'];
	    $y = mi($r['year']);
	    $m = mi($r['month']);
	    $d = mi($r['day']);

	    $tran_key = $dd."_".$r['pos_id']."_".$r['counter_id'];

		// got item
		if($r['item_id']){
			$sku_item_id = mi($r['sku_item_id']);
			$changed_sku[$sku_item_id] = 1;
			$hour = mi(date("H", strtotime($r['pos_time'])));
			$trade_discount_type = mi($r['trade_discount_type']);
			$counter_id = mi($r['counter_id']);
			
			if(!$r['race']) $r['race'] = 'O';   // if race empty, put 'O' as other
			else    $r['race'] = strtoupper($r['race']{0}); // only get first character and make it uppercase

			$upd['sku_item_id'] = $sku_item_id;
			$upd['date'] = $dd;
			$upd['year'] = $y;
			$upd['month'] = $m;
			$upd['amount'] = mf($r['price']-$r['discount']-$r['discount2']-$r['tax_amount']);
			$upd['disc_amt'] = mf($r['discount']);
			$upd['disc_amt2'] = mf($r['discount2']);
			$upd['qty'] = mf($r['qty']);
			$upd['tax_amount'] = mf($r['tax_amount']);
			
			$tmp_lgv_id = array();
			$tmp_q = $con->sql_query("select last_grn_vendor_id from tmp_last_grn_vendor_id_b$bid where date = ".ms($dd)." and sku_item_id = ".mi($sku_item_id));
			$tmp_lgv_id = $con->sql_fetchassoc($tmp_q);
			$con->sql_freeresult($tmp_q);
			
			if($tmp_lgv_id) $upd['last_grn_vendor_id'] = $tmp_lgv_id['last_grn_vendor_id'];
			else{
				//$q2 = $con->sql_query("select ivsh.vendor_id from vendor_sku_history ivsh where ivsh.sku_item_id=".mi($sku_item_id)." and ivsh.branch_id = ".mi($bid)." and ivsh.added <= ".ms($dd)." order by ivsh.added desc limit 1");
				$q2 = $con->sql_query("select vsh.vendor_id 
				from vendor_sku_history_b$bid vsh 
				where vsh.sku_item_id=".mi($sku_item_id)." and ".ms($dd)." between vsh.from_date and vsh.to_date limit 1");

				$tmp = $con->sql_fetchrow($q2);
				$upd['last_grn_vendor_id'] = $tmp['vendor_id'];
				$con->sql_freeresult($q2);
				
				$tmp_lgv_id_ins = array();
				$tmp_lgv_id_ins['date'] = $dd;
				$tmp_lgv_id_ins['sku_item_id'] = $sku_item_id;
				$tmp_lgv_id_ins['last_grn_vendor_id'] = $tmp['vendor_id'];
				$con->sql_query("replace into tmp_last_grn_vendor_id_b$bid ".mysql_insert_by_field($tmp_lgv_id_ins));
			}

			// Find Cost
			if($config['sku_consign_selling_deduct_discount_as_cost']&&$r['trade_discount_code']&&$trade_discount_type&&$r['sku_type']=='CONSIGN'){ // check whether using discount percent as cost : Consign Method
				$price_type = $r['trade_discount_code'];
				$department_id = mi($r['department_id']);
				$brand_id = mi($r['brand_id']);
				$vendor_id = mi($r['vendor_id']);

				if($r['price']&&$r['qty']){   // got selling amount
					if($trade_discount_type==1){    // use brand
						$q_rate = $con->sql_query("select rate from brand_commission where branch_id=$bid and brand_id=$brand_id and department_id=$department_id and skutype_code=".ms($price_type));
					}elseif($trade_discount_type==2){   // use vendor
						$q_rate = $con->sql_query("select rate from vendor_commission where branch_id=$bid and vendor_id=$vendor_id and department_id=$department_id and skutype_code=".ms($price_type));
					}
					$discount_rate = mf($con->sql_fetchfield(0, -1, $q_rate));
					$con->sql_freeresult($q_rate);
					$consign_cost = round(($upd['amount'] * ((100-$discount_rate)*0.01))/$r['qty'],3);
					$r['cost'] = $consign_cost;
				}else{
					$r['cost'] = 0;
				}
			}else{  // normal method
				$q_sic = $con->sql_query("select * from sku_items_cost_history where branch_id=$bid and sku_item_id=$sku_item_id and date<=".ms($dd)." order by date desc limit 1");
				$sku_items_cost_history = $con->sql_fetchassoc($q_sic);
				$con->sql_freeresult($q_sic);
				if(!$sku_items_cost_history){
					$r['cost'] = $r['master_cost'];
				}else{
					$r['cost'] = $sku_items_cost_history['grn_cost'];
				}
				$con->sql_freeresult($q_sic);
			}

			$upd['cost'] = $r['cost']*$upd['qty'];
			if($upd['qty']<0){
				// for return, make price negative if it is positive
				if ($upd['amount']>0){
					$upd['amount'] *= -1;
					$upd['disc_amt'] *= -1;
				}
				// if return, the cost should be -ve (this will give us zero cost)
				//$upd['cost'] *= -1;
				
				// get the return info
				/*$q_pgr = $con->sql_query("select pi.qty as purchased_qty,pi.price,pi.discount
	from pos_goods_return pgr
	left join pos_items pi on pi.branch_id=pgr.branch_id and pi.counter_id=pgr.return_counter_id and pi.date=pgr.return_date and pi.pos_id=pgr.return_pos_id and pi.item_id=pgr.return_item_id
	where pgr.branch_id=$bid and pgr.counter_id=$counter_id and pgr.date=".ms($dd)." and pgr.pos_id=".mi($r['pos_id'])." and pgr.item_id=".mi($r['item_id']));
				$return_item = $con->sql_fetchassoc($q_pgr);
				$con->sql_freeresult($q_pgr);
				
				if($return_item['discount']>0 && $return_item['purchased_qty']>0){	// return info got data
					// minus back the discount amt ,using qty portion
					$return_discount = round(($upd['qty']*-1/$return_item['purchased_qty'])*$return_item['discount'],2);
					$upd['disc_amt'] -= $return_discount;
				}*/
			}

			if($r['is_fresh_market'] && $config['enable_fresh_market_sku']){
				if(!$sku_items_cost_history){
					$q_sic = $con->sql_query("select * from sku_items_cost_history where branch_id=$bid and sku_item_id=$sku_item_id and date<=".ms($dd)." order by date desc limit 1");
					$sku_items_cost_history = $con->sql_fetchassoc($q_sic);
					$con->sql_freeresult($q_sic);
				}
				$upd['fresh_market_cost'] = $sku_items_cost_history['fresh_market_cost']*$upd['amount'];
			}

			$upd['memb_qty'] = 0;
			$upd['memb_amt'] = 0;
			$upd['memb_tax'] = 0;
			$upd['memb_disc'] = 0;
			$upd['memb_disc2'] = 0;
			$upd['memb_cost'] = 0;
			$upd['memb_fm_cost'] = 0;
			
			if($r['member_no']){
				// is member
				$upd['memb_qty'] = $upd['qty'];
				$upd['memb_amt'] = $upd['amount'];
				$upd['memb_tax'] = $upd['tax_amount'];
				$upd['memb_disc'] = $upd['disc_amt'];
				$upd['memb_disc2'] = $upd['disc_amt2'];
				$upd['memb_cost'] = $upd['cost'];
				$upd['memb_fm_cost'] = $upd['fresh_market_cost'];
			}
			
			// insert sku_items_sales_cache
			$sql = "insert into $sales_cache_tbl ".mysql_insert_by_field($upd)." on duplicate key update
			amount=amount+".mf($upd['amount']).",
			cost=cost+".mf($upd['cost']).",
			disc_amt=disc_amt+".mf($upd['disc_amt']).",
			fresh_market_cost=fresh_market_cost+".mf($upd['fresh_market_cost']).",
			qty=qty+".mf($upd['qty']).",
			last_grn_vendor_id=".mi($upd['last_grn_vendor_id']).",
			tax_amount=tax_amount+".mf($upd['tax_amount']).",
			disc_amt2=disc_amt2+".mf($upd['disc_amt2']).",
			memb_qty=memb_qty+".mf($upd['memb_qty']).",
			memb_amt=memb_amt+".mf($upd['memb_amt']).",
			memb_tax=memb_tax+".mf($upd['memb_tax']).",
			memb_disc=memb_disc+".mf($upd['memb_disc']).",
			memb_disc2=memb_disc2+".mf($upd['memb_disc2']).",
			memb_cost=memb_cost+".mf($upd['memb_cost']).",
			memb_fm_cost=memb_fm_cost+".mf($upd['memb_fm_cost']);
			$con->sql_query($sql);

			// insert pwp sales
			if(strpos(strtoupper($r['mprice_type']), 'PWP')!==false){   // is pwp selling
				if($last_pwp_tran!=$tran_key){
					$pwp['transaction_count'] = 1;
				}
				$pwp['date'] = $dd;
				$pwp['year'] = $y;
				$pwp['month'] = $m;
				$pwp['day'] = $d;
				$pwp['qty'] = $upd['qty'];
				$pwp['amount'] = $upd['amount'];
				$pwp['cost'] = $upd['cost'];
				$pwp['sku_item_code'] = $r['sku_item_code'];

				$con->sql_query("insert into $pwp_cache_tbl ".mysql_insert_by_field($pwp)." on duplicate key update
				qty=qty+".$pwp['qty'].",
				amount=amount+".$pwp['amount'].",
				cost=cost+".$pwp['cost'].",
				transaction_count=transaction_count+".mi($pwp['transaction_count']));
				$last_pwp_tran = $tran_key;
			}

			// insert member sales
			if($last_mem_tran!=$tran_key){
				$mem['transaction_count'] = 1;
			}
			$mem['date'] = $dd;
			$mem['year'] = $y;
			$mem['month'] = $m;
			$mem['day'] = $d;
			$mem['hour'] = $hour;
			$mem['card_no'] = $r['member_no'];
			$mem['race'] = $r['race'];
			$mem['amount'] = $upd['amount'];
			$mem['memb_qty'] = $upd['qty'];
			$mem['memb_tax'] = $upd['tax_amount'];
			$mem['memb_disc'] = $upd['disc_amt'];
			$mem['memb_disc2'] = $upd['disc_amt2'];
			$mem['memb_cost'] = $upd['cost'];

			$con->sql_query("insert into $member_cache_tbl ".mysql_insert_by_field($mem)." on duplicate key update
			amount=amount+".$mem['amount'].",
			transaction_count=transaction_count+".mi($mem['transaction_count']).",
			memb_qty=memb_qty+".mf($mem['memb_qty']).",
			memb_tax=memb_tax+".mf($mem['memb_tax']).",
			memb_disc=memb_disc+".mf($mem['memb_disc']).",
			memb_disc2=memb_disc2+".mf($mem['memb_disc2']).",
			memb_cost=memb_cost+".mf($mem['memb_cost']));
			$last_mem_tran = $tran_key;

			$dept_tran['date'] = $dd;
			$dept_tran['year'] = $y;
			$dept_tran['month'] = $m;
			$dept_tran['pos_id'] = $r['pos_id'];
			$dept_tran['counter_id'] = $r['counter_id'];
			$dept_tran['department_id'] = $r['department_id'];
			$dept_tran['member_no'] = $r['member_no'];
			$con->sql_query("replace into $dept_tran_cache_tbl ".mysql_insert_by_field($dept_tran));

			// found have SN enabled, need to auto generate history and updates        
			if($config['enable_sn_bn']){
				$sn = $con->sql_query("select sni.*, pisn.id as pisn_id from sn_info sni left join pos_items_sn pisn on pisn.serial_no = sni.serial_no and pisn.sku_item_id = sni.sku_item_id where sni.pos_id = ".mi($r['pos_id'])." and sni.item_id = ".mi($r['item_id'])." and sni.date = ".ms($dd)." and sni.branch_id = ".mi($bid)." and sni.counter_id = ".mi($r['counter_id'])." and sni.sku_item_id = ".mi($sku_item_id));
				
				if($con->sql_numrows($sn) > 0){ // found this pos items contains S/N
					$sn_info = $con->sql_fetchassoc($sn);
					$sn_his_ins = array();
					if($r['qty']<0){ // means it is goods return
						$sn_his_ins['remark'] = "Goods Returned from POS";
						$status = "Available";
					}else{ // means it is sold out, need to update into sn_info
						$sn_his_ins['remark'] = "Goods Sold from POS";
						$status = "Sold";
					}
					
					$sn_his_ins['pisn_id'] = $sn_info['pisn_id'];
					$sn_his_ins['branch_id'] = $bid;
					$sn_his_ins['sku_item_id'] = $sku_item_id;
					$sn_his_ins['located_branch_id'] = $bid;
					$sn_his_ins['serial_no'] = $sn_info['serial_no'];
					$sn_his_ins['status'] = $status;
					$sn_his_ins['active'] = 1;
					$sn_his_ins['added'] = $r['pos_time'];
					$sn_his_ins['pos_id'] = $r['pos_id'];
					$sn_his_ins['item_id'] = $r['item_id'];
					$sn_his_ins['counter_id'] = $r['counter_id'];
					$sn_his_ins['date'] = $dd;
					$sn_his_ins['user_id'] = $r['cashier_id'];
					
					$con->sql_query("insert into pos_items_sn_history ".mysql_insert_by_field($sn_his_ins));
					
					/*$sn_upd = array();
					$sn_upd['pos_id'] = $r['pos_id'];
					$sn_upd['pos_item_id'] = $r['item_id'];
					$sn_upd['pos_branch_id'] = $bid;
					$sn_upd['date'] = $r['date'];
					$sn_upd['counter_id'] = $r['counter_id'];
					$sn_upd['status'] = $status;
					
					$con->sql_query("update pos_items_sn set ".mysql_update_by_field($sn_upd)." where id = ".mi($sn_info['pisn_id'])." and branch_id = ".mi($bid));*/

					$params = array();
					$params['serial_no'] = $sn_info['serial_no'];
					$params['sid'] = $sku_item_id;
					
					relocate_sn_pos_info($params);
				}
				$con->sql_freeresult($sn);
			}
		}
	    
		// is new transaction
		if($last_tran_key != $tran_key){
			// daily sales cache
			$dc = array();
			
			if($r['pos_more_info'] && !is_array($r['pos_more_info'])){
				$r['pos_more_info'] = unserialize($r['pos_more_info']);
				
				if($r['pos_more_info']['deposit']){
					foreach($r['pos_more_info']['deposit'] as $dp){
						$dc['deposit_used_amt'] += round($dp['amount']-$dp['gst_amount'],2);
						$dc['deposit_used_gst_amt'] += $dp['gst_amount'];
					}
				}
			}
			if($r['is_deposit']){
				// is deposit receive
				$dc['deposit_rcv_amt'] = round($r['pos_amt']-$r['total_gst_amt'],2);
				$dc['deposit_rcv_gst_amt'] = $r['total_gst_amt'];
			}
			
			$dc['date'] = $dd;
			$dc['year'] = $y;
			$dc['month'] = $m;
			$dc['service_charge_amt'] = round($r['service_charges']-$r['service_charges_gst_amt'],2);
			$dc['service_charge_gst_amt'] = $r['service_charges_gst_amt'];
			$dc['total_gst_amt'] = $r['total_gst_amt'];
			$dc['over_amt'] = round($r['amount_tender'] - $r['amount_change'] - $r['service_charges'] - $r['pos_amt'],2);
			$dc['rounding_amt'] = round($r['rounding_amt'], 2);
			$dc['curr_adj_amt'] = round($r['ca_amt'], 2);
			
			$dc['memb_service_charge_amt'] = 0;
			$dc['memb_service_charge_gst_amt'] = 0;
			$dc['memb_total_gst_amt'] = 0;
			$dc['memb_deposit_rcv_amt'] = 0;
			$dc['memb_deposit_used_amt'] = 0;
			$dc['memb_deposit_rcv_gst_amt'] = 0;
			$dc['memb_deposit_used_gst_amt'] = 0;
			$dc['memb_rounding_amt'] = 0;
			$dc['memb_curr_adj_amt'] = 0;
			$dc['memb_over_amt'] = 0;
				
			if($r['member_no']){
				// is member
				$dc['memb_service_charge_amt'] = $dc['service_charge_amt'];
				$dc['memb_service_charge_gst_amt'] = $dc['service_charge_gst_amt'];
				$dc['memb_total_gst_amt'] = $dc['total_gst_amt'];
				$dc['memb_deposit_rcv_amt'] = $dc['deposit_rcv_amt'];
				$dc['memb_deposit_used_amt'] = $dc['deposit_used_amt'];
				$dc['memb_deposit_rcv_gst_amt'] = $dc['deposit_rcv_gst_amt'];
				$dc['memb_deposit_used_gst_amt'] = $dc['deposit_used_gst_amt'];
				$dc['memb_rounding_amt'] = $dc['rounding_amt'];
				$dc['memb_curr_adj_amt'] = $dc['curr_adj_amt'];
				$dc['memb_over_amt'] = $dc['over_amt'];
			}
			//print_r($dc);
			$con->sql_query($sql="insert into $daily_sales_cache_tbl ".mysql_insert_by_field($dc)." on duplicate key update
			service_charge_amt=round(service_charge_amt+".mf($dc['service_charge_amt']).",2),
			service_charge_gst_amt=round(service_charge_gst_amt+".mf($dc['service_charge_gst_amt']).",2),
			deposit_rcv_amt=round(deposit_rcv_amt+".mf($dc['deposit_rcv_amt']).",2),
			deposit_used_amt=round(deposit_used_amt+".mf($dc['deposit_used_amt']).",2),
			deposit_rcv_gst_amt=round(deposit_rcv_gst_amt+".mf($dc['deposit_rcv_gst_amt']).",2),
			deposit_used_gst_amt=round(deposit_used_gst_amt+".mf($dc['deposit_used_gst_amt']).",2),
			total_gst_amt=round(total_gst_amt+".mf($dc['total_gst_amt']).",2),
			over_amt=round(over_amt+".mf($dc['over_amt']).",2),
			rounding_amt=round(rounding_amt+".mf($dc['rounding_amt']).",2),
			curr_adj_amt=round(curr_adj_amt+".mf($dc['curr_adj_amt']).",2),
			memb_service_charge_amt=round(memb_service_charge_amt+".mf($dc['memb_service_charge_amt']).",2),
			memb_service_charge_gst_amt=round(memb_service_charge_gst_amt+".mf($dc['memb_service_charge_gst_amt']).",2),
			memb_total_gst_amt=round(memb_total_gst_amt+".mf($dc['memb_total_gst_amt']).",2),
			memb_deposit_rcv_amt=round(memb_deposit_rcv_amt+".mf($dc['memb_deposit_rcv_amt']).",2),
			memb_deposit_used_amt=round(memb_deposit_used_amt+".mf($dc['memb_deposit_used_amt']).",2),
			memb_deposit_rcv_gst_amt=round(memb_deposit_rcv_gst_amt+".mf($dc['memb_deposit_rcv_gst_amt']).",2),
			memb_deposit_used_gst_amt=round(memb_deposit_used_gst_amt+".mf($dc['memb_deposit_used_gst_amt']).",2),
			memb_rounding_amt=round(memb_rounding_amt+".mf($dc['memb_rounding_amt']).",2),
			memb_over_amt=round(memb_over_amt+".mf($dc['memb_over_amt']).",2),
			memb_curr_adj_amt=round(memb_curr_adj_amt+".mf($dc['memb_curr_adj_amt']).",2)");
			//print "$sql\n";
		}
		
		$last_tran_key = $tran_key;

        $total_rows--;
        if(defined('TERMINAL')){
	        print "$total_rows rows to process...\r";
		}else{
			if($total_rows%100==0){	// every 1000 row update status
				// write percentage
				if($write_process_status)	write_process_status('counter_collection','finalize','per', ($total_rows2-$total_rows)/$total_rows2*100);
			}
		}
		// set changed=1 for cron to recalculate stock balance
		if(count($changed_sku)>1000){
		    // update inventory changed
			$con->sql_query("update LOW_PRIORITY sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (".join(",", array_keys($changed_sku)).")");
			$changed_sku = array();
		}
	}
	$con->sql_freeresult($q1);

	// insert cateogry sales
	$con->sql_query("replace into $cat_cache_tbl (date, category_id, sku_type, year, month, amount, cost, qty, fresh_market_cost, tax_amount, disc_amt, disc_amt2, memb_qty, memb_amt, memb_tax, memb_disc, memb_disc2, memb_cost, memb_fm_cost) 
	select date, sku.category_id, sku.sku_type, year, month, sum(amount), sum(cost), sum(qty), sum(fresh_market_cost), sum(tax_amount), sum(disc_amt), sum(disc_amt2), sum(memb_qty), sum(memb_amt), sum(memb_tax), sum(memb_disc), sum(memb_disc2), sum(memb_cost), sum(memb_fm_cost)
	from $sales_cache_tbl pos
	left join sku_items si on si.id=pos.sku_item_id
	left join sku on sku.id=si.sku_id
	where $date_filter group by date, category_id, sku_type");
	
	// insert sku item finalised cache
	$appCore->posManager->generateSKUItemFinalisedCache($bid, $date, $date_from, $date_to);

	// set changed=1 for cron to recalculate stock balance
	if($changed_sku){
	    // update inventory changed
		$con->sql_query("update LOW_PRIORITY sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (".join(",", array_keys($changed_sku)).")");
	}
	$endtime = microtime(true);
	if(defined('TERMINAL')){
        print "\nTotal $total_rows2 rows processed. ".($endtime-$starttime)." seconds used.\n";
	}else{
		if($write_process_status)	write_process_status('counter_collection','finalize','per', false, true);
	}
}
	

function log_vp($vid, $type, $ref, $log){
	global $con, $vp_session;
	
	$bid = $vp_session['branch_id'];
	if (!$bid)
	{
		$con->sql_query("select id from branch where code = ".ms(BRANCH_CODE));
		$r = $con->sql_fetchrow();
		$bid = $r[0];
		$con->sql_freeresult();
	}
	$bid = mi($bid);
	
	// Get Max ID
	$con->sql_query("select max(id) as max_id from log_vp where branch_id=$bid for update");
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	$new_id = mi($tmp['max_id'])+1;
	
	$upd = array();
	$upd['id'] = $new_id;
	$upd['branch_id'] = $bid;
	$upd['timestamp'] = 'CURRENT_TIMESTAMP';
	$upd['vendor_id'] = $vid;
	$upd['type'] = $type;
	$upd['rid'] = $ref;
	$upd['log'] = $log;
	
	$con->sql_query("insert into log_vp ".mysql_insert_by_field($upd));
}

function log_dp($debtor_id, $type, $ref, $log){
	global $con, $dp_session;
	
	$bid = $dp_session['branch_id'];
	if (!$bid)
	{
		$con->sql_query("select id from branch where code = ".ms(BRANCH_CODE));
		$r = $con->sql_fetchrow();
		$bid = $r[0];
		$con->sql_freeresult();
	}
	$bid = mi($bid);
	
	// Get Max ID
	$con->sql_query("select max(id) as max_id from log_dp where branch_id=$bid for update");
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	$new_id = mi($tmp['max_id'])+1;
	
	$upd = array();
	$upd['id'] = $new_id;
	$upd['branch_id'] = $bid;
	$upd['timestamp'] = 'CURRENT_TIMESTAMP';
	$upd['debtor_id'] = $debtor_id;
	$upd['type'] = $type;
	$upd['rid'] = $ref;
	$upd['log'] = $log;
	
	$con->sql_query("insert into log_dp ".mysql_insert_by_field($upd));
}

function get_consignment_discount_rate($bid, $date, $trade_discount_code, $dept_id, $brand_vendor_commission, $brand_vendor_id){
	global $con;
	
	$bid= mi($bid);
	$dept_id = mi($dept_id);
	$brand_vendor_id = mi($brand_vendor_id);
	
	if(!$bid || !$dept_id || !$brand_vendor_id)	return 0;
	if($brand_vendor_commission=='brand'){
		$sql = "select if(bch.rate is null, bc.rate, bch.rate) as rate 
		from brand_commission bc
		left join brand_commission_history bch on bch.branch_id=bc.branch_id and bch.skutype_code=bc.skutype_code and bch.department_id=bc.department_id and bch.brand_id=bc.brand_id and ".ms($date)." between bch.date_from and bch.date_to
		where bc.branch_id=$bid and bc.skutype_code=".ms($trade_discount_code)." and bc.department_id=$dept_id and bc.brand_id=$brand_vendor_id limit 1";
	}else{
		$sql = "select if(vch.rate is null, vc.rate, vch.rate) as rate 
		from vendor_commission vc
		left join vendor_commission_history vch on vch.branch_id=vc.branch_id and vch.skutype_code=vc.skutype_code and vch.department_id=vc.department_id and vch.vendor_id=vc.vendor_id and ".ms($date)." between vch.date_from and vch.date_to
		where vc.branch_id=$bid and vc.skutype_code=".ms($trade_discount_code)." and vc.department_id=$dept_id and vc.vendor_id=$brand_vendor_id limit 1";
	}
	$con->sql_query($sql);
	$rate = mf($con->sql_fetchfield(0));
	$con->sql_freeresult();
	
	return $rate;
}

function fix_terminal_smarty(){
	global $smarty;
	
	$smarty->template_dir = dirname(__FILE__).'/../templates';
	$smarty->compile_dir = dirname(__FILE__).'/../templates_c';
	$smarty->config_dir = dirname(__FILE__).'/../templates';
}

function member_points_changed(){
	global $con, $LANG;
	
	$hqcon = connect_hq();

	if(!$hqcon->db_connect_id){
		print "Membership ".$LANG['HQ_OFFLINE'];
	}
	
	if(!$_REQUEST['nric']){
		print $LANG['MEMBERSHIP_NRIC_NOT_IN_DATABASE'];
		exit;
	}
	
	$q1 = $hqcon->sql_query("select * from membership_history where nric = ".ms($_REQUEST['nric']));
	
	$points_changed = false;
	while($r = $hqcon->sql_fetchassoc($q1)){
		$q2 = $hqcon->sql_query("delete from tmp_membership_points_trigger where card_no = ".ms($r['card_no']));
		
		if($hqcon->sql_affectedrows($q2)>0){
			$points_changed = true;
		}
	}
	$hqcon->sql_freeresult($q1);
	
	if($points_changed){
		$con->sql_query("update membership set points_changed=1 where nric = ".ms($_REQUEST['nric']));
		
		/*if($con->sql_affectedrows()>0){
			print sprintf($LANG['MEMBERSHIP_MEMBER_POINTS_CHANGED'], $_REQUEST['nric']);
		}else{
			print "Nothing to update!";
		}*/
		print "OK";
	}else{
		$q1 = $con->sql_query("select * from membership where points_changed=1 and nric = ".ms($_REQUEST['nric']));
		
		if($con->sql_numrows($q1) > 0) print "This member [".$_REQUEST['nric']."] already marked as point recalculate.";
		else print "This member [".$_REQUEST['nric']."] does not have any renewal history, therefore points can't be updated.";
	}
	
}

function copy_selling_price_by_branch($from_bid=0,$to_bid=0) {

	global $con,$sessioninfo,$config;
	
	if (!$from_bid || !$to_bid) {
		print 'invalid branch ID to copy from/to';
		exit;
	}
	
	$additional_sp = 0;
	if ($config['masterfile_branch_enable_additional_sp']) {
		$q1 = $con->sql_query("select * from branch_additional_sp where branch_id = ".mi($to_bid));
		$basp_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		if ($basp_info) $additional_sp = $basp_info['additional_sp'];
	}
	
	$tmp = "copy_".session_id()."_".time();
	$sqls = array();
	
	// normal price
	$bcode = get_branch_code($from_bid);
	$sqls[] = "create temporary table $tmp (select $to_bid as branch_id, si.id as sku_item_id, CURRENT_TIMESTAMP as last_update, (ifnull(sip.price, si.selling_price) +".mf($additional_sp).") as price, ifnull(sip.cost, si.cost_price) as cost, ifnull(sip.trade_discount_code, sku.default_trade_discount_code) as trade_discount_code, sip.selling_price_foc, sip.selling_price_more_info from sku_items si left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = $from_bid left join sku on sku.id=si.sku_id where 1)";
	$sqls[] = "replace into sku_items_price (branch_id, sku_item_id, last_update, price, cost, trade_discount_code, selling_price_foc, selling_price_more_info) select branch_id, sku_item_id, last_update, price, cost, trade_discount_code, selling_price_foc, selling_price_more_info from $tmp";
	$sqls[] = "replace into sku_items_price_history (branch_id,sku_item_id,added,price,cost,trade_discount_code,source,user_id) select branch_id,sku_item_id,last_update,price,cost,trade_discount_code,'$bcode',$sessioninfo[id] from $tmp";
	$sqls[] = "drop table $tmp";
	
	// mprice
	$sqls[] = "create temporary table $tmp (select branch_id, sku_item_id, type, last_update, (price+".mf($additional_sp).") as price, trade_discount_code from sku_items_mprice where branch_id=$from_bid)";
	$sqls[] = "update $tmp set branch_id=$to_bid, last_update = CURRENT_TIMESTAMP";
	$sqls[] = "replace into sku_items_mprice (branch_id,sku_item_id,type,last_update,price,trade_discount_code) select branch_id,sku_item_id,type,last_update,price,trade_discount_code from $tmp";
	$sqls[] = "replace into sku_items_mprice_history (branch_id,sku_item_id,type,added,price,trade_discount_code,user_id) select branch_id,sku_item_id,type,last_update,price,trade_discount_code,$sessioninfo[id] from $tmp";
	$sqls[] = "drop table $tmp";
	
	// qprice
	$sqls[] = "create temporary table $tmp (select * from sku_items_qprice where branch_id=$from_bid)";
	$sqls[] = "update $tmp set branch_id=$to_bid, last_update=CURRENT_TIMESTAMP";
	$sqls[] = "replace into sku_items_qprice (branch_id,sku_item_id,min_qty,price,last_update) select branch_id,sku_item_id,min_qty,price,last_update from $tmp";
	$sqls[] = "replace into sku_items_qprice_history (branch_id,sku_item_id,min_qty,price,added,user_id) select branch_id,sku_item_id,min_qty,price,last_update,$sessioninfo[id] from $tmp";
	$sqls[] = "drop table $tmp";
	
	// mqprice
	$sqls[] = "create temporary table $tmp (select * from sku_items_mqprice where branch_id=$from_bid)";
	$sqls[] = "update $tmp set branch_id=$to_bid, last_update=CURRENT_TIMESTAMP";
	$sqls[] = "replace into sku_items_mqprice (branch_id,sku_item_id,min_qty,type,price,last_update) select branch_id,sku_item_id,min_qty,type,price,last_update from $tmp";
	$sqls[] = "replace into sku_items_mqprice_history (branch_id,sku_item_id,min_qty,type,price,added,user_id) select branch_id,sku_item_id,min_qty,type,price,last_update,$sessioninfo[id] from $tmp";
	$sqls[] = "drop table $tmp";
	
	foreach ($sqls as $sql1) $con->sql_query($sql1);
	//die(join("<br /><br />",$sqls));
}

function copy_block_po_by_branch($from_bid=0,$to_bid=0) {

	global $con,$sessioninfo;

	if (!$from_bid || !$to_bid) {
		print 'invalid branch ID to copy from/to';
		exit;
	}
	
	$q1 = $con->sql_query($abc="select id, block_list from sku_items where block_list like '%i:$from_bid;%'");//print "$abc\n\n";
	while ($r1 = $con->sql_fetchassoc($q1)) {
		$upd = array();
		$branches = unserialize($r1['block_list']);
		if ($branches) {
			$branches[$to_bid] = 'on';
			$upd['block_list'] = serialize($branches);
			$sql_upd = "update sku_items set " . mysql_update_by_field($upd) . " where id = ".mi($r1['id'])." limit 1";
			$con->sql_query($sql_upd);
		}
	}
	$con->sql_freeresult($q1);
	
}

function copy_pos_settings_by_branch($from_bid=0,$to_bid=0) {

	global $con,$sessioninfo;
	
	if (!$from_bid || !$to_bid) {
		print 'invalid branch ID to copy from/to';
		exit;
	}
	
	$q1 = $con->sql_query($abc="select * from pos_settings where branch_id = ".mi($from_bid));//print "$abc\n\n";
	while ($r1 = $con->sql_fetchassoc($q1)) {
		$ins = array();
		$ins['branch_id'] = $to_bid;
		$ins['setting_name'] = $r1['setting_name'];
		$ins['setting_value'] = $r1['setting_value'];
		$ins['last_update'] = 'CURRENT_TIMESTAMP';
		$sql_ins = "replace into pos_settings " . mysql_insert_by_field($ins);
		$con->sql_query($sql_ins);
	}
	$con->sql_freeresult($q1);
}

function copy_discount_by_branch($from_bid=0,$to_bid=0) {

	global $con,$sessioninfo;

	if (!$from_bid || !$to_bid) {
		print 'invalid branch ID to copy from/to';
		exit;
	}
	
	$q1 = $con->sql_query($abc="select id,category_disc_by_branch_inherit from sku_items where cat_disc_inherit = 'set' and category_disc_by_branch_inherit like '%i:$from_bid;%'");//print "$abc\n\n";
	while ($r1 = $con->sql_fetchassoc($q1)) {
		$upd = array();
		$discounts = unserialize($r1['category_disc_by_branch_inherit']);
		
		if ($discounts) {
			if(isset($discounts[$from_bid]) && $discounts[$from_bid]) {
				$discounts[$to_bid] = $discounts[$from_bid];
				$upd['category_disc_by_branch_inherit'] = serialize($discounts);
				$sql_upd = "update sku_items set " . mysql_update_by_field($upd) . " where id = ".mi($r1['id'])." limit 1";
				$con->sql_query($sql_upd);
			}
		}
	}
	$con->sql_freeresult($q1);
	
	$q1 = $con->sql_query($abc="select id,category_disc_by_branch from category where category_disc_by_branch like '%i:$from_bid;%'");//print "$abc\n\n";
	while ($r1 = $con->sql_fetchassoc($q1)) {
		$upd = array();
		$discounts = unserialize($r1['category_disc_by_branch']);
		
		if ($discounts) {
			if(isset($discounts[$from_bid]) && $discounts[$from_bid]) {
				$discounts[$to_bid] = $discounts[$from_bid];
				$upd['category_disc_by_branch'] = serialize($discounts);
				$sql_upd = "update category set " . mysql_update_by_field($upd) . " where id = ".mi($r1['id'])." limit 1";
				$con->sql_query($sql_upd);
			}
		}
	}
	$con->sql_freeresult($q1);
	
}

function copy_point_by_branch($from_bid=0,$to_bid=0) {

	global $con,$sessioninfo;

	if (!$from_bid || !$to_bid) {
		print 'invalid branch ID to copy from/to';
		exit;
	}
	
	$q1 = $con->sql_query($abc="select id,category_point_by_branch_inherit from sku_items where category_point_inherit = 'set' and category_point_by_branch_inherit like '%i:$from_bid;%'");//print "$abc\n\n";
	while ($r1 = $con->sql_fetchassoc($q1)) {
		$upd = array();
		$points = unserialize($r1['category_point_by_branch_inherit']);
		
		if ($points) {
			if(isset($points[$from_bid]) && $points[$from_bid]) {
				$points[$to_bid] = $points[$from_bid];
				$upd['category_point_by_branch_inherit'] = serialize($points);
				$sql_upd = "update sku_items set " . mysql_update_by_field($upd) . " where id = ".mi($r1['id'])." limit 1";
				$con->sql_query($sql_upd);
			}
		}
	}
	$con->sql_freeresult($q1);
	
	$q1 = $con->sql_query($abc="select id,category_point_by_branch from category where category_point_by_branch like '%i:$from_bid;%'");//print "$abc\n\n";
	while ($r1 = $con->sql_fetchassoc($q1)) {
		$upd = array();
		$points = unserialize($r1['category_point_by_branch']);
		
		if ($points) {
			if(isset($points[$from_bid]) && $points[$from_bid]) {
				$points[$to_bid] = $points[$from_bid];
				$upd['category_point_by_branch'] = serialize($points);
				$sql_upd = "update category set " . mysql_update_by_field($upd) . " where id = ".mi($r1['id'])." limit 1";
				$con->sql_query($sql_upd);
			}
		}
	}
	$con->sql_freeresult($q1);
	
}

function copy_trade_discount_by_branch($from_bid=0,$to_bid=0) {

	global $con,$sessioninfo;
	
	if (!$from_bid || !$to_bid) {
		print 'invalid branch ID to copy from/to';
		exit;
	}
	
	$q1 = $con->sql_query($abc="select * from brand_commission where branch_id = ".mi($from_bid));//print "$abc\n\n";
	while ($r1 = $con->sql_fetchassoc($q1)) {
		$ins = array();
		$ins['branch_id'] = $to_bid;
		$ins['brand_id'] = $r1['brand_id'];
		$ins['skutype_code'] = $r1['skutype_code'];
		$ins['rate'] = $r1['rate'];
		$ins['department_id'] = $r1['department_id'];
		$sql_ins = "replace into brand_commission " . mysql_insert_by_field($ins);
		$con->sql_query($sql_ins);
	}
	$con->sql_freeresult($q1);
	
	$q1 = $con->sql_query($abc="select * from vendor_commission where branch_id = ".mi($from_bid));//print "$abc\n\n";
	while ($r1 = $con->sql_fetchassoc($q1)) {
		$ins = array();
		$ins['branch_id'] = $to_bid;
		$ins['vendor_id'] = $r1['vendor_id'];
		$ins['skutype_code'] = $r1['skutype_code'];
		$ins['rate'] = $r1['rate'];
		$ins['department_id'] = $r1['department_id'];
		$sql_ins = "replace into vendor_commission " . mysql_insert_by_field($ins);
		$con->sql_query($sql_ins);
	}
	$con->sql_freeresult($q1);
}

function copy_approval_flow_by_branch($from_bid=0,$to_bid=0) {

	global $con,$sessioninfo;
	
	if (!$from_bid || !$to_bid) {
		print 'invalid branch ID to copy from/to';
		exit;
	}
	
	$q1 = $con->sql_query($abc="select * from approval_flow where branch_id = ".mi($from_bid));//print "$abc\n\n";
	while ($r1 = $con->sql_fetchassoc($q1)) {
		$ins = array();
		$ins['branch_id'] = $to_bid;
		$ins['type'] = $r1['type'];
		$ins['aorder'] = $r1['aorder'];
		$ins['approvals'] = $r1['approvals'];
		$ins['notify_users'] = $r1['notify_users'];
		$ins['active'] = $r1['active'];
		$ins['sku_category_id'] = $r1['sku_category_id'];
		$ins['sku_type'] = $r1['sku_type'];
		$ins['approval_settings'] = $r1['approval_settings'];
		$sql_ins = "replace into approval_flow " . mysql_insert_by_field($ins);
		$con->sql_query($sql_ins);
	}
	$con->sql_freeresult($q1);
	
}

function copy_block_grn_by_branch($from_bid=0,$to_bid=0) {

	global $con,$sessioninfo;

	if (!$from_bid || !$to_bid) {
		print 'invalid branch ID to copy from/to';
		exit;
	}
	
	$q1 = $con->sql_query($abc="select id, doc_block_list from sku_items where doc_block_list like '%grn%'");//print "$abc\n\n";
	while ($r1 = $con->sql_fetchassoc($q1)) {
		$upd = array();
		$grn_branch = unserialize($r1['doc_block_list']);
		if ($grn_branch['grn'][$from_bid] == 'on') {
			$grn_branch['grn'][$to_bid] = 'on';
			$upd['doc_block_list'] = serialize($grn_branch);
			$sql_upd = "update sku_items set " . mysql_update_by_field($upd) . " where id = ".mi($r1['id'])." limit 1";
			$con->sql_query($sql_upd);
		}
	}
	$con->sql_freeresult($q1);
	
}

function get_vp_sales_report_profit_by_date($date, $sales_report_profit_by_date_list = array(), $params = array()){
	global $vp_session;
	
	$ret = array();
	$ret['type'] = 'NORMAL_GLOBAL';
	$ret['per'] = 0;
	
	if(!$sales_report_profit_by_date_list) $sales_report_profit_by_date_list = $vp_session['vp']['sales_report_profit_by_date'];

	if(!$sales_report_profit_by_date_list) return $ret;
	
	foreach($sales_report_profit_by_date_list as $r=>$info){
		if(strtotime($date) <= strtotime($info['date_to'])){
			$ret['per'] = $info['profit_per'];
			
			if($info['profit_per_by_type'] && $params['other_type_info']){
				$type = $params['other_type_info']['type'];
				$v = $params['other_type_info']['value'];
				$found_per = false;
				$cat_id = 0;
				
				// find profit percent by sku
				if($type == 'SKU'){
					foreach($info['profit_per_by_type'] as $type_row_no => $type_row_info){
						if($type_row_info['type'] == 'SKU' && $type_row_info['value'] == $v){
							$found_per = true;
							
							$ret['type'] = 'SKU';
							$ret['value'] = $v;
							$ret['per'] = $type_row_info['per'];
							break;
						}
					}
					
					// cant get the percent by sku, look for category from lowest to highest
					if(!$found_per){
						$si_info = get_vp_global_si_info_list($v);
						$cat_id = $si_info['category_id'];
					}
				}elseif($type == 'CATEGORY'){	// find profit by category
					$cat_id = $v;
				}
				
				// try to find profit percent by category
				if($cat_id && !$found_per){
					// get current cat info
					$cat_info = get_vp_global_cat_info_list($cat_id);
					
					// current cat level
					$clvl = $cat_info['level'];

					while($clvl > 0){
						$check_cat_id = $cat_info['p'.$clvl];
						
						foreach($info['profit_per_by_type'] as $type_row_no => $type_row_info){	// loop from lowest cat to highest cat
							if($type_row_info['type'] == 'CATEGORY' && $type_row_info['value'] == $check_cat_id){
								$found_per = true;
								
								$ret['type'] = 'CATEGORY';
								$ret['value'] = $check_cat_id;
								$ret['per'] = $type_row_info['per'];
								break 2;
							}
						}
					
						$clvl--;	// not found, move up 1 level
					}
				}
			}
			
			break;
		}
	}
	
	return $ret;
}

function get_vp_sales_bonus_per($y, $m, $amt, $sales_bonus_by_step = array(), $params = array()){
	global $vp_session;
	
	$ret = array();
	$ret['type'] = 'NORMAL_GLOBAL';
	$ret['per'] = 0;
	
	// no amt, no bonus
	if($amt<=0)	return $ret;
	
	$tmp_date = $y."-".$m."-01";
	$date = strtotime("-1 day", strtotime("+1 month", strtotime($tmp_date)));
	
	if(!$sales_bonus_by_step) $sales_bonus_by_step = $vp_session['vp']['sales_bonus_by_step'];
	if(!$sales_bonus_by_step) return $ret;
	//print_r($sales_bonus_by_step);
	
	$bonus_info = $sales_bonus_by_step[$y][$m];	// directly get the bonus info for this date
	
	if(!$bonus_info){	// the selected month have no set bonus
		foreach($sales_bonus_by_step as $b_y=>$m_list){	// loop from the first bonus y/m until the latest
			foreach($m_list as $b_m=>$r_list){
				$tmp_bonus_date = $b_y."-".$b_m."-01";
				$bonus_date = strtotime("-1 day", strtotime("+1 month", strtotime($tmp_bonus_date)));
				
				if($date >= $bonus_date){	// found the bonus to use
					$bonus_info = $r_list;
					break 2;
				}
			}
		}
	}
	
	// still not found bonus
	if(!$bonus_info)	return $ret;
	//print_r($bonus_info);
	
	foreach($bonus_info as $amt_from_list){	// loop for each amount from list
		if(mf($amt) >= mf($amt_from_list['amt_from'])){
			$ret['per'] = mf($amt_from_list['bonus_per']);	// get the normal global %
			
			// need to find bonus by sku or cat
			if($amt_from_list['bonus_per_by_type'] && $params['other_type_info']){
				$type = $params['other_type_info']['type'];
				$v = $params['other_type_info']['value'];
				$found_per = false;
				$cat_id = 0;
				
				// find bonus by sku
				if($type == 'SKU'){
					foreach($amt_from_list['bonus_per_by_type'] as $type_row_no => $type_row_info){
						if($type_row_info['type'] == 'SKU' && $type_row_info['value'] == $v){
							$found_per = true;
							
							$ret['type'] = 'SKU';
							$ret['value'] = $v;
							$ret['per'] = $type_row_info['per'];
							break;
						}
					}
					
					// cant get the percent by sku, look for category from lowest to highest
					if(!$found_per){
						$si_info = get_vp_global_si_info_list($v);
						$cat_id = $si_info['category_id'];
					}
				}elseif($type == 'CATEGORY'){	// find profit by category
					$cat_id = $v;
				}
				
				// try to find bonus percent by category
				if($cat_id && !$found_per){
					// get current cat info
					$cat_info = get_vp_global_cat_info_list($cat_id);
					
					// current cat level
					$clvl = $cat_info['level'];

					while($clvl > 0){
						$check_cat_id = $cat_info['p'.$clvl];
						
						foreach($amt_from_list['bonus_per_by_type'] as $type_row_no => $type_row_info){	// loop from lowest cat to highest cat
							if($type_row_info['type'] == 'CATEGORY' && $type_row_info['value'] == $check_cat_id){
								// get current cat info
								$tmp_cat_info = get_vp_global_cat_info_list($check_cat_id);
								
								$found_per = true;
								
								$ret['type'] = 'CATEGORY';
								$ret['value'] = $check_cat_id;
								$ret['per'] = $type_row_info['per'];
								break 2;
							}
						}
					
						$clvl--;	// not found, move up 1 level
					}
				}
			}
		}
	}
	//print_r($ret);
	return $ret;
}

function got_vp_sales_bonus_set($y, $m, $sales_bonus_by_step = array(), $params = array()){
	global $vp_session;
	
	if(!$sales_bonus_by_step) $sales_bonus_by_step = $vp_session['vp']['sales_bonus_by_step'];
	if(!$sales_bonus_by_step) return false;	// sales bonus no set
	
	$bonus_info = $sales_bonus_by_step[$y][$m];
	if(!$bonus_info)	return false;	// no sales bonus for this month
	
	$type = $params['other_type_info']['type'];
	$v = $params['other_type_info']['value'];
	$found_per = false;
	$cat_id = 0;
	
	if(!$type || !$v)	return false;
	
	foreach($bonus_info as $amt_from_list){	// loop for each amount from list
		//if(mf($amt) >= mf($amt_from_list['amt_from'])){	// no need check amount qualify or not
			if($amt_from_list['bonus_per_by_type']){
				// find bonus by sku
				if($type == 'SKU'){
					foreach($amt_from_list['bonus_per_by_type'] as $type_row_no => $type_row_info){
						if($type_row_info['type'] == 'SKU' && $type_row_info['value'] == $v){
							//$found_per = true;
							return true;
							break;
						}
					}
					
					// cant get the percent by sku, look for category from lowest to highest
					if(!$found_per){
						$si_info = get_vp_global_si_info_list($v);
						$cat_id = $si_info['category_id'];
					}
				}elseif($type == 'CATEGORY'){	// find profit by category
					$cat_id = $v;
				}
				
				// try to find bonus percent by category
				if($cat_id && !$found_per){
					// get current cat info
					$cat_info = get_vp_global_cat_info_list($cat_id);
					
					// current cat level
					$clvl = $cat_info['level'];

					while($clvl > 0){
						$check_cat_id = $cat_info['p'.$clvl];
						
						foreach($amt_from_list['bonus_per_by_type'] as $type_row_no => $type_row_info){	// loop from lowest cat to highest cat
							if($type_row_info['type'] == 'CATEGORY' && $type_row_info['value'] == $check_cat_id){
								//$found_per = true;
								return true;
								break 2;
							}
						}
					
						$clvl--;	// not found, move up 1 level
					}
				}
			}
		//}
	}
	
	return $found_per;
}

function get_member_type($card_no, $pattern_check){
	global $config;

	foreach($config['membership_cardtype'] as $t => $ci){
		if(preg_match($ci[$pattern_check], $card_no)) return $ci['member_type'];
	}
	
	return false;
}

function get_branch_counter_name($bid, $cid){
	global $con;
	
	$con->sql_query("select cs.network_name from counter_settings cs where cs.branch_id=".mi($bid)." and cs.id=".mi($cid));
	$network_name = $con->sql_fetchfield(0);
	$con->sql_freeresult();
	
	return $network_name;
}

function get_vp_global_si_info_list($sid){
	global $con, $vp_global_si_info_list;
	
	$sid = mi($sid);
	
	if(!$sid)	die('Please provide sku item id.');
	
	if(!$vp_global_si_info_list)	$vp_global_si_info_list = array();
	if(isset($vp_global_si_info_list[$sid]))	return $vp_global_si_info_list[$sid];
	
	// don store too many to eat system memory
	if(count($vp_global_si_info_list) >= 1000)	$vp_global_si_info_list = array();
	
	// get sku item info
	$con->sql_query("select si.id as sid, si.sku_id, sku.category_id, si.sku_item_code
	from sku_items si 
	left join sku on sku.id=si.sku_id
	where si.id=$sid");
	$vp_global_si_info_list[$sid] = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	return $vp_global_si_info_list[$sid];	
}

function get_vp_global_cat_info_list($cat_id){
	global $con, $vp_global_cat_info_list;
	
	$cat_id = mi($cat_id);
	
	if(!$vp_global_cat_info_list)	$vp_global_cat_info_list = array();
	if(isset($vp_global_cat_info_list[$cat_id]))	return $vp_global_cat_info_list[$cat_id];
	
	// don store too many to eat system memory
	if(count($vp_global_cat_info_list) >= 1000)	$vp_global_cat_info_list = array();
	
	// get sku item info
	$con->sql_query("select cc.*,c.description,c.level 
from category c 
left join category_cache cc on cc.category_id=c.id
where c.id=$cat_id");
	$vp_global_cat_info_list[$cat_id] = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	return $vp_global_cat_info_list[$cat_id];
	
}

function is_monthly_report_printed($date,$bid=0,$module='') {
	global $con;
	$date_arr = explode('-',$date);
	
	if (!empty($module)) {
		$con->sql_query("select mr_year,mr_month from $module where mr_branch_id= ".mi($bid)." and mr_year = ".mi($date_arr[0])." and mr_month = ".mi($date_arr[1])." limit 1");
		if ($con->sql_fetchrow()) {
			return false; //allow
		}
	}
	
	$con->sql_query("select year,month from monthly_report_list where status=1 and branch_id = ".mi($bid)." and year = ".mi($date_arr[0])." and month = ".mi($date_arr[1])." limit 1");
	if ($con->sql_fetchrow()) {
		return true;
	}
	
	return false;
	/*
	return true;
	*/
}

function check_user_regions($bid){
	global $con, $sessioninfo;
	
	if(!$sessioninfo['regions']) return true;
	
	$q1 = $con->sql_query("select * from branch where id = ".mi($bid));
	$b_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);

	if($b_info['region']){
		if($sessioninfo['regions'] && $sessioninfo['regions'] != "N;"){
			foreach($sessioninfo['regions'] as $code=>$val){
				if($code == $b_info['region']) return true;
			}
			return false;
		}else return true;
	}elseif($sessioninfo['regions'] && $sessioninfo['regions'] != "N;") return false;
	else return true;
}

function get_pm_recipient_list($id,$aid,$status,$action,$branch_id=0,$module){
	
	global $con, $sessioninfo, $smarty;
	
	$modules = array(
		'adjustment'			=>	'adjustment',
		'po'					=>	'po',
		'promotion'				=>	'promotion',
		'do'					=>	'do',
		'future_price'			=>	'sku_items_future_price',
		'sales_order'			=>	'sales_order',
		'sku'					=>	'sku',
		'eform'					=>	'eform_data',
		'grn'					=>	'grn',
		'gra'					=>	'gra',
		'purchase_agreement'	=>	'purchase_agreement',
		'ci'					=>	'ci',
		'cn'					=>	'cn',
		'dn'					=>	'dn',
		'membership_redemption'	=>	'membership_redemption',
		//''=>	'',
	);
	
	if (!isset($modules[$module])) {
		die("unknown module to send pm ($module)");
	}
	else $table = $modules[$module];
	
	if ($module == 'sku')
		$con->sql_query("select * from approval_history where id = $aid");
	else
		$con->sql_query("select * from branch_approval_history where id = $aid and branch_id = $branch_id");
	$r = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	$approval_order_id = $r['approval_order_id'];
	
	if ($module == 'sku') { //sku bit different col name, and no branch
		$con->sql_query("select apply_by from sku where id = $id limit 1");
		$r1 = $con->sql_fetchassoc();
		if ($r1) $owner[0] = $r1['apply_by'];
		$con->sql_freeresult();
	}
	else {
		$con->sql_query("select user_id from $table where id = $id and branch_id = $branch_id limit 1");
		$r1 = $con->sql_fetchassoc();
		if ($r1) $owner[0] = $r1['user_id'];
		$con->sql_freeresult();
	}
	
	$notify_users = $r['notify_users'];
	$notify_users = str_replace("|$sessioninfo[id]|", "|",$notify_users); //dont send to self
	$notify_users = preg_split("/\|/", $notify_users);
	
	$approvals = $r['approvals'];
	$approvals = str_replace("|$sessioninfo[id]|", "|", $approvals); //dont send to self
	$approvals = preg_split("/\|/", $approvals);
	
	$approved_by = $r['approved_by'];
	$approved_by = str_replace("|$sessioninfo[id]|", "|", $approved_by); //dont send to self
	$approved_by = preg_split("/\|/", $approved_by);
	
	$to = array();
	
	if ($action == 'confirmation') {
		if ($approval_order_id == '1') { //follow sequence
			// and send to the next guy in approval flow
			foreach ($approvals as $a) {
				if ($a) {
					$next_approval_guy = $a;
					break;
				}
			}
			$to[] = $next_approval_guy;
		}
		if ($approval_order_id == '2' || $approval_order_id == '3') { //all, any
			$to = array_merge($to,$approvals); // sends to all approvals
		}
		//if this flow no need approver(4), sends to notify_user when confirm
		if ($approval_order_id == '4') {
			$to = array_merge($to,$notify_users);
		}
	}
	elseif ($action == 'approval') {
		if ($approval_order_id == '1') { //follow sequence
			
			if ($status == 2 || $status == 4 || $status == 5) { //rejected
				$to = array_merge($to,$approved_by); // sends to previous approvals
				$to = array_merge($to,$notify_users); // sends to notify users
				$to = array_merge($to,$owner); // sends to owner
			}
			else { //approved
				
				//send to next approval
				foreach ($approvals as $a) {
					if ($a) {
						$next_approval_guy = $a;
						break;
					}
				}
				if ($next_approval_guy) {
					$to[] = $next_approval_guy;
				}
				else { //this is the last approval
					$to = array_merge($to,$notify_users); // sends to notify users
					$to = array_merge($to,$owner); // sends to owner
				}
			}
			
		}
		if ($approval_order_id == '2') { //all
			if ($status == 2 || $status == 4 || $status == 5) { //rejected
				//$to = array_merge($to,$approvals); // if reject, no need to notify other approvals
				$to = array_merge($to,$notify_users); // sends to notify users
				$to = array_merge($to,$owner); // sends to owner
			}
			else { //approve
				//for now, when one of approvals approve, no need to notify other approvals
				//$to = array_merge($to,$approvals);
				
				$fully_approved = true;
				foreach ($approvals as $a) {
					if ($a) {
						$fully_approved = false;
						break;
					}
				}
				
				if ($fully_approved) {
					$to = array_merge($to,$notify_users); // sends to notify users
					$to = array_merge($to,$owner); // sends to owner
				}
			}
		}
		if ($approval_order_id == '3') { //any
			$to = array_merge($to,$notify_users); // sends to notify users
			$to = array_merge($to,$owner); // sends to owner
		}
		if($approval_order_id == 4){	// no approver
			$to = array_merge($to,$notify_users); // sends to notify users
		}
	}
	else {
		$to = array_merge($to,$notify_users); //not supposed to come here
	}
	
	return $to;
	
}

function get_pm_recipient_list2($id,$aid,$status,$action,$branch_id=0,$module){
	
	global $con, $sessioninfo, $smarty;
	
	$modules = array(
		'adjustment'			=>	'adjustment',
		'po'					=>	'po',
		'promotion'				=>	'promotion',
		'do'					=>	'do',
		'future_price'			=>	'sku_items_future_price',
		'sales_order'			=>	'sales_order',
		'sku'					=>	'sku',
		'eform'					=>	'eform_data',
		'grn'					=>	'grn',
		'gra'					=>	'gra',
		'purchase_agreement'	=>	'purchase_agreement',
		'ci'					=>	'ci',
		'cn'					=>	'cn',
		'dn'					=>	'dn',
		'membership_redemption'	=>	'membership_redemption',
		'cnote' => 'cnote',
		'cycle_count' => 'cycle_count',
		//''=>	'',
	);
	
	if (!isset($modules[$module])) {
		die("unknown module to send pm ($module)");
	}
	else $table = $modules[$module];
	
	if ($module == 'sku')
		$con->sql_query("select * from approval_history where id = $aid");
	else
		$con->sql_query("select * from branch_approval_history where id = $aid and branch_id = $branch_id");
	$r = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	$notify_users = $approvals = $approved_by = array();
	if($r){
		$r['approval_settings'] = unserialize($r['approval_settings']);
		
		$notify_users = $r['notify_users'];
		$notify_users = str_replace("|$sessioninfo[id]|", "|",$notify_users); //dont send to self
		$notify_users = preg_split("/\|/", $notify_users);
		
		$approvals = $r['approvals'];
		$approvals = str_replace("|$sessioninfo[id]|", "|", $approvals); //dont send to self
		$approvals = preg_split("/\|/", $approvals);
		
		$approved_by = $r['approved_by'];
		$approved_by = str_replace("|$sessioninfo[id]|", "|", $approved_by); //dont send to self
		$approved_by = preg_split("/\|/", $approved_by);
	}
	
	$approval_order_id = $r['approval_order_id'];
	
	if ($module == 'sku') { //sku bit different col name, and no branch
		$con->sql_query("select apply_by from sku where id = $id limit 1");
		$r1 = $con->sql_fetchassoc();
		if ($r1) $owner_id = $r1['apply_by'];
		$con->sql_freeresult();
	}
	else {
		$con->sql_query("select user_id from $table where id = $id and branch_id = $branch_id limit 1");
		$r1 = $con->sql_fetchassoc();
		if ($r1) $owner_id = $r1['user_id'];
		$con->sql_freeresult();
	}
	
	$to = array();
	
	$need_send_to_notify = false;
	$need_send_to_owner = false;
	$need_send_approval_user_id_list = array();
	
	if ($action == 'confirmation') {
		if ($approval_order_id == '1') { //follow sequence
			// and send to the next guy in approval flow
			if($approvals){
				foreach ($approvals as $tmp_user_id) {
					if ($tmp_user_id) {
						$need_send_approval_user_id_list[$tmp_user_id] = $tmp_user_id;
						break;
					}
				}				
			}
		}
		if ($approval_order_id == '2' || $approval_order_id == '3') { //all, any
			if($approvals){
				foreach ($approvals as $tmp_user_id) {
					// sends to all approvals
					if ($tmp_user_id) {
						$need_send_approval_user_id_list[$tmp_user_id] = $tmp_user_id;
					}
				}
			}
		}
		//if this flow no need approver(4), sends to notify_user when confirm
		if ($approval_order_id == '4' || count($need_send_approval_user_id_list)<=0) {
			$need_send_to_notify = true;
		}
	}
	elseif ($action == 'approval') {
		if ($approval_order_id == '1') { //follow sequence
			
			if ($status == 2 || $status == 4 || $status == 5) { //rejected
				// sends to previous approvals
				if($approved_by){
					foreach($approved_by as $tmp_user_id){
						if($tmp_user_id){
							$need_send_approval_user_id_list[$tmp_user_id] = $tmp_user_id;
						}
					}
				}
				
				// sends to notify users
				$need_send_to_notify = true;
				
				// sends to owner				
				$need_send_to_owner = true;
			}
			else { //approved
				$next_approval_guy = 0;
				//send to next approval
				foreach ($approvals as $tmp_user_id) {
					if ($tmp_user_id) {
						$next_approval_guy = $tmp_user_id;
						break;
					}
				}
				if ($next_approval_guy) {
					$need_send_approval_user_id_list[$next_approval_guy] = $next_approval_guy;
				}
				else { //this is the last approval
					// sends to notify users
					$need_send_to_notify = true;
					
					// sends to owner				
					$need_send_to_owner = true;
				}
			}		
		}
		if ($approval_order_id == '2') { //all
			if ($status == 2 || $status == 4 || $status == 5) { //rejected
				//$to = array_merge($to,$approvals); // if reject, no need to notify other approvals
				
				// sends to notify users
				$need_send_to_notify = true;
				
				// sends to owner				
				$need_send_to_owner = true;
			}
			else { //approve
				//for now, when one of approvals approve, no need to notify other approvals
				//$to = array_merge($to,$approvals);
				
				$fully_approved = true;
				foreach ($approvals as $a) {
					if ($a) {
						$fully_approved = false;
						break;
					}
				}
				
				if ($fully_approved) {
					// sends to notify users
					$need_send_to_notify = true;
					
					// sends to owner				
					$need_send_to_owner = true;
				}
			}
		}
		if ($approval_order_id == '3') { //any
			// sends to notify users
			$need_send_to_notify = true;
			
			// sends to owner				
			$need_send_to_owner = true;
		}
		if($approval_order_id == 4){	// no approver
			// sends to notify users
			$need_send_to_notify = true;
		}
	}
	else {
		//not supposed to come here
		// sends to notify users
		$need_send_to_notify = true;
	}
	
	// need send to approval user
	if($need_send_approval_user_id_list){
		foreach($need_send_approval_user_id_list as $tmp_user_id){
			$tmp = array();
			$tmp['user_id'] = $tmp_user_id;
			$tmp['approval_settings'] = $r['approval_settings']['approval'][$tmp_user_id];	
			$tmp['type'] = 'approval';	
			$to[$tmp_user_id] = $tmp;
		}
	}
	
	// need send to notify user
	if($need_send_to_notify){
		if($notify_users){
			foreach($notify_users as $tmp_user_id){
				if($tmp_user_id){
					$tmp = array();
					$tmp['user_id'] = $tmp_user_id;
					$tmp['approval_settings'] = $r['approval_settings']['notify'][$tmp_user_id];
					$tmp['type'] = 'notify';
					$to[$tmp_user_id] = $tmp;
				}
			}
		}
	}
	
	// need send to owner
	if($need_send_to_owner){
		$tmp = array();
		$tmp['user_id'] = $owner_id;
		$tmp['approval_settings'] = $r['approval_settings']['owner'];
		$tmp['type'] = 'owner';
		$to[$owner_id] = $tmp;
	}
	
	return $to;
	
}

function relocate_sn_pos_info($params){ 
    global $con, $sessioninfo;
    
    if(!$params['serial_no'] || !$params['sid']) return false;
    
    $q1 = $con->sql_query("select *
                          from pos_items_sn_history
                          where serial_no = ".ms($params['serial_no'])." and sku_item_id = ".mi($params['sid'])."
                          and pos_id > 0
                          order by added desc
                          limit 1");

    $pisnh = $con->sql_fetchassoc($q1);
    
    if(!$pisnh){
        $sn_upd = array();
        $sn_upd['pos_id'] = 0;
        $sn_upd['pos_item_id'] = 0;
        $sn_upd['pos_branch_id'] = 0;
        $sn_upd['date'] = 0;
        $sn_upd['counter_id'] = 0;
        $sn_upd['status'] = "Available";
   
        $con->sql_query("update pos_items_sn set ".mysql_update_by_field($sn_upd)." where serial_no = ".mi($params['serial_no'])." and sku_item_id = ".mi($params['sid']));
    }else{
        $sn_upd = array();
        // means it was goods returned by cashier and need to set it become empty
        if($pisnh['remark'] == "Goods Returned from POS" && $pisnh['status'] == "Available"){
            $sn_upd['pos_id'] = 0;
            $sn_upd['pos_item_id'] = 0;
            $sn_upd['pos_branch_id'] = 0;
            $sn_upd['date'] = 0;
            $sn_upd['counter_id'] = 0;
            $sn_upd['status'] = "Available";
        }else{ // it is a sales, need to update into master S/N table
            $sn_upd['pos_id'] = $pisnh['pos_id'];
            $sn_upd['pos_item_id'] = $pisnh['item_id'];
            $sn_upd['pos_branch_id'] = $pisnh['branch_id'];
            $sn_upd['date'] = $pisnh['date'];
            $sn_upd['counter_id'] = $pisnh['counter_id'];
            $sn_upd['status'] = "Sold";
        }
        $sn_upd['last_update'] = "CURRENT_TIMESTAMP";
        
        $con->sql_query("update pos_items_sn set ".mysql_update_by_field($sn_upd)." where id = ".mi($pisnh['pisn_id'])." and branch_id = ".mi($pisnh['branch_id']));
        
        return true;
    }
}

function phpmailer_send($mailer, &$params = array()){
	global $config, $sessioninfo, $appCore;
	
	if(!is_array($params))	$params = array();
	if(!$mailer){
		$params['err'] = 'Mailer object is null.';
		return false;
	}
	
	$send_now = false;
	if($params['send_now'] || $config['send_email_direct_send'])	$send_now = true;
	
	// Store into Send Email Management
	$params2 = array();
	$params2['branch_id'] = get_branch_id(BRANCH_CODE);
	$params2['user_id'] = mi($sessioninfo['id']);
	$data = $appCore->emailManager->addEmail($mailer, $params2);
	
	if($data['ok']){
		$email_guid = $data['guid'];
	}else{
		//print "Failed";
		$params['err'] = $data['err'];
		return false;
	}
		
	if(!$send_now){	
		// Send Email in Background
		$appCore->emailManager->execTerminalSendEmail();
		return true;
	}
	
	// Send Immediately
	$params3 = array();
	$params3['user_id'] = mi($sessioninfo['id']);
	$data = $appCore->emailManager->sendEmail($email_guid, $params3);
	if($data['ok']){
		return true;
	}else{
		$params['err'] = $data['err'];
		return false;
	}
}

function connect_offline($bcode)
{
	global $offline_db_default_connection;
	
	if(!$offline_db_default_connection[$bcode]) return;

	$offline_connection = $offline_db_default_connection[$bcode]['con'];
	
    $offline = connect_db($offline_connection[0], $offline_connection[1], $offline_connection[2], $offline_connection[3], false);
    return $offline;
}

function check_offline_data($id){
	global $config, $LANG;
	
	$tables = array("SKU" => "sku", "Adjustment" => "adjustment", "Delivery Order" => "do", "Goods Receiving Note" => "grn", "Purchase Order" => "po", "Membership" => "membership", "Membership Renewal" => "membership_history", "Goods Return Advice" => "gra");
	
	// need to filter branch if multi server mode
	if($config['single_server_mode']){
		$bid = get_branch_id($_REQUEST['login_branch']);
	}else{
		$bid = get_branch_id(BRANCH_CODE);
	}
	$filters[0] = "branch_id = ".mi($bid);

	$off_con = connect_offline($_REQUEST['login_branch']);
	
	if(!$off_con) return;
	
	// check data
	foreach($tables as $desc => $table){
		$filters = array();
		if($table == "sku"){
			//if(!$config['single_server_mode']){
			$filters[] = "apply_branch_id = ".mi($bid);
			//}
			$filters[] = "apply_by = ".mi($id);
		}else{
			//if(!$config['single_server_mode']){
			$filters[] = "branch_id = ".mi($bid);
			//}
			$filters[] = "user_id = ".mi($id);
		}
		
		$filter = join(" and ", $filters);
		$q1 = $off_con->sql_query("select * from $table where active=1 and status in (0,1) and uploaded=0 and ".$filter);
		
		if($off_con->sql_numrows($q1) > 0){
			return array("err" => sprintf($LANG['OFFLINE_DATA_UNSYNCED'], $desc));
		}
		$off_con->sql_freeresult($q1);
	}
}

function get_counter_info($bid, $cid){
	global $con, $all_counter_info;
	
	if(!isset($all_counter_info[$bid][$cid])){
		$q1 = $con->sql_query("select cs.* from counter_settings cs where cs.branch_id=".mi($bid)." and cs.id=".mi($cid));
		$all_counter_info[$bid][$cid] = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
	}
	
	return $all_counter_info[$bid][$cid];
}

function calculate_duration_by_second($total_sec){
	$sec = $total_sec%60;
	$min = floor($total_sec/60);
	if($min>=60){
		$hour = floor($min/60);
		$min = $min%60;
		if($hour>=24){
			$day = floor($hour/24);
			$hour = $hour%24;
		}
	}
	
	$ret = array();
	$ret['sec'] = $sec;
	$ret['min'] = $min;
	if($hour)	$ret['hour'] = $hour;
	if($day)	$ret['day'] = $day;
	
	return $ret;
}

function get_brand_group() {
	global $con;
	$brand_group = array();
	$r_brand_group = $con->sql_query('select id, code from brgroup where active = 1 order by code');
	while ($r = $con->sql_fetchassoc($r_brand_group)) $brand_group['brandgroup'.mi($r['id'])] = $r['code'];
	return $brand_group;
}

function process_brand_id($id) {
	global $con;
	if (!$id) return array(0);
	if (stripos($id, 'brandgroup') === false) return array(mi($id)); //it's an actual brand id (not brand group)
	$id = str_replace('brandgroup','',$id);
	$id = mi($id);
	$brand_group_items = array();
	$r_brand_group_items = $con->sql_query("select brand_id from brand_brgroup where brgroup_id = $id");
	while ($r = $con->sql_fetchassoc($r_brand_group_items)) $brand_group_items[] = mi($r['brand_id']);
	return $brand_group_items;
}

function get_brand_title($id) {
	global $con;
	if ($id == '') return 'All';
	if ($id == '0') return 'UNBRANDED';
	if (!$id) return '';
	if (stripos($id, 'brandgroup') === false) { //it's an actual brand id (not brand group)
		$tb = 'brand';
	}
	else {
		$tb = 'brgroup';
		$id = str_replace('brandgroup','',$id);
	}
	
	$con->sql_query("select description from $tb where id = ".mi($id)." limit 1");
	$b = $con->sql_fetchassoc();
	return $b['description'];
}

function process_gst_sp_rounding_condition($val){
	global $con, $gst_settings_list;

	$val = mf($val);
	
	if(!$val) return 0;
	
	if(!isset($gst_settings_list['sp_rounding_condition'])){
		$q1 = $con->sql_query("select * from gst_settings where setting_name = 'sp_rounding_condition'");
		$gst_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		$gst_settings_list[$gst_info['setting_name']] = $gst_info['setting_value'];
	}
	
	if(!$gst_settings_list['sp_rounding_condition']) return $val;
	
	if($gst_settings_list['sp_rounding_condition'] != 0.09) $new_val = ceil($val / $gst_settings_list['sp_rounding_condition']) * $gst_settings_list['sp_rounding_condition'];
	else{ // 0.09
		$mult = pow(10, 1);
		$new_val = (floor($val * $mult) / $mult) + $gst_settings_list['sp_rounding_condition'];
	}
	
	return round($new_val, 2);
}

function get_sku_gst($field, $sid, $params = array()){
	global $con, $global_gst_settings;
	
	$sid = mi($sid);
	if($sid <= 0)	return;

	// get from sku items and sku
	$con->sql_query("select if(si.input_tax<=-1,sku.mst_input_tax,si.input_tax) as input_tax,
					if(si.output_tax<=-1, sku.mst_output_tax, si.output_tax) as output_tax ,
					if(si.inclusive_tax= 'inherit', sku.mst_inclusive_tax, si.inclusive_tax) as inclusive_tax, sku.category_id
	from sku_items si
	left join sku on sku.id=si.sku_id
	where si.id=$sid");
	$ret = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if($ret[$field] == -1 || $ret[$field] == 'inherit'){
		// inherit
		if($ret['category_id'] > 0){
			// get from category
			return get_category_gst($field, $ret['category_id'], $params);
		}
	}else{
		// got result
		switch($field){
	      case 'input_tax':
	      case 'output_tax':
	        $gst_id=$ret[$field];
	
	        if($gst_id > 0){
				$gst_settings = get_gst_settings($gst_id);
				if($gst_settings && !$params['no_check_use_zero_rate'] && is_using_force_zero_rate_before_start_date()){
					// use zero rate
					$gst_settings['rate'] = 0;
				}
	          return $gst_settings;
	        }
	        else{
			  if(isset($global_gst_settings['global_'.$field]) && $global_gst_settings['global_'.$field]>0){
				$gst_settings = get_gst_settings($global_gst_settings['global_'.$field]);
					if($gst_settings && !$params['no_check_use_zero_rate'] && is_using_force_zero_rate_before_start_date()){
						// use zero rate
						$gst_settings['rate'] = 0;
					}
				return $gst_settings;
			  }
			  else return null;
			}
	      break;
	      case 'inclusive_tax':
	        return $ret[$field];
	      break;
	    }
	}
}

function get_category_gst($field,$id, $params = array()){
  global $con, $config, $global_gst_settings;

  $con->sql_query("select root_id, $field from category where id = ".mi($id));
  $ret = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
  $root_id=$ret['root_id'];

  if($root_id > 0 && ($ret[$field]==-1 || $ret[$field]=="inherit")){
    return get_category_gst($field,$root_id, $params);
  }
  else{
    switch($field){
      case 'input_tax':
      case 'output_tax':
        $gst_id=$ret[$field];

        if($gst_id > 0){
			$gst_settings = get_gst_settings($gst_id);
				if($gst_settings && !$params['no_check_use_zero_rate'] && is_using_force_zero_rate_before_start_date()){
					// use zero rate
					$gst_settings['rate'] = 0;
				}
			return $gst_settings;
        }
        else{
		  if(isset($global_gst_settings['global_'.$field]) && $global_gst_settings['global_'.$field]>0){
			$gst_settings = get_gst_settings($global_gst_settings['global_'.$field]);
				if($gst_settings && !$params['no_check_use_zero_rate'] && is_using_force_zero_rate_before_start_date()){
					// use zero rate
					$gst_settings['rate'] = 0;
				}
			return $gst_settings;
		  }
		  else return null;
		}
      break;
      case 'inclusive_tax':
        if($root_id==0 && $ret[$field] == "inherit"){ // get inclusive tax from masterfile GST
			return $global_gst_settings['inclusive_tax'];
		}elseif($root_id==0 && !$ret[$field]) return "yes";
        else return $ret[$field];
      break;
    }
  }
}

function get_gst_settings($gst_id){
  global $con, $config;

  $con->sql_query("select * from gst where id = ".mi($gst_id));
  $info = $con->sql_fetchassoc();
  $con->sql_freeresult();

  return $info;
}



function check_gst_status($prms=array()){
	global $con, $config;

	if(!$config['enable_gst']) return false;
	
	// check general if the GST is active
	$q1 = $con->sql_query("select setting_value from gst_settings where setting_name = 'active' and setting_value = 1");
	$gst_status = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);

	if(!$gst_status) return false;
	
	// some module only need to check is config enabled and is gst active, no need other checking
	if($prms['check_only_need_active'])	return true;
	
	
	// pickup and see if GST general settings only needs to check GST active status
	$q1 = $con->sql_query("select setting_value from gst_settings where setting_name = 'skip_gst_validate' and setting_value = 1");
	$skip_gst_validate = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	// if found config to enable GST, GST is active & skip other validations, always return true
	//if($skip_gst_validate) return true;
	
	if(isset($prms['vendor_id'])){
		// check vendor
		$q1 = $con->sql_query("select * from vendor where id = ".mi($prms['vendor_id']));
		$vd_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$vd_info['gst_register_no'] = trim($vd_info['gst_register_no']);
		
		// if found got set gst code for vendor (farmer), no need to check vendor's register no and date
		if($vd_info['gst_register'] > 0 && ($skip_gst_validate['setting_value'] || (!$skip_gst_validate['setting_value'] && strtotime($prms['date']) >= strtotime($config['global_gst_start_date'])))) return true;
		// check if this PO under is GST
		elseif(($skip_gst_validate['setting_value'] && $vd_info['gst_register'] == -1 && $vd_info['gst_register_no']) || ($vd_info['gst_register'] == -1 && $vd_info['gst_register_no'] && strtotime($prms['date']) >= strtotime($vd_info['gst_start_date']) && strtotime($prms['date']) >= strtotime($config['global_gst_start_date']))) return true;
		else return false;
	}else if(isset($prms['branch_id'])){
		// check branch
		$q1 = $con->sql_query("select * from branch where id = ".mi($prms['branch_id']));
		$branch_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		$branch_info['gst_register_no'] = trim($branch_info['gst_register_no']);
		
		// check if this branch under is GST
		if(($skip_gst_validate['setting_value'] && $branch_info['gst_register_no']) || ($branch_info['gst_register_no'] && strtotime($prms['date']) >= strtotime($branch_info['gst_start_date']) && strtotime($prms['date']) >= strtotime($config['global_gst_start_date']))){
			if($prms['to_branch_id']){
				$branch_id_1 = mi($prms['branch_id']);
				$branch_id_2 = mi($prms['to_branch_id']);
				// check gst interbranch
				$con->sql_query("select * from gst_interbranch where (branch_id_1=$branch_id_1 and branch_id_2=$branch_id_2) or (branch_id_1=$branch_id_2 and branch_id_2=$branch_id_1)");
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				return $tmp ? true : false;
			}else{
				return true;
			}
		}
		else return false;
	}else{
		if($skip_gst_validate['setting_value']) return true;

		if(!$prms['date']){ // need to pickup current date to compare gst start date
			$prms['date'] = date("Y-m-d");
		}
		
	    if(isset($prms['date'])){
	      if(strtotime($prms['date']) >= strtotime($config['global_gst_start_date'])) return true;
			  else return false;
	    }else{
	      return true;
	    }
	}
}

function get_tax_settings($tax_id){
  global $con, $config;

  $con->sql_query("select * from tax where id = ".mi($tax_id));
  $info = $con->sql_fetchassoc();
  $con->sql_freeresult();

  return $info;
}

function check_tax_status($prms=array()){
	global $con, $config;
	
	if(!$config['enable_tax']) return false;
	
	// check general if the Tax is active
	$q1 = $con->sql_query("select setting_value from tax_settings where setting_name = 'active' and setting_value = 1");
	$tax_status = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);

	if(!$tax_status) return false;
	
	if($prms['check_only_need_active'])	return true;
	
	//get tax start date
	$q2 = $con->sql_query("select setting_value from tax_settings where setting_name = 'tax_start_date'");
	$tax_date = $con->sql_fetchassoc($q2);
	$tax_start_date = $tax_date['setting_value'];
	$con->sql_freeresult($q2);
	
	
	if(isset($prms['vendor_id'])){
		return false;
	}else if(isset($prms['branch_id'])){
		return false;
	}else{
		if(!$prms['date']){ // need to pickup current date to compare tax start date
			$prms['date'] = date("Y-m-d");
		}
		
	    if(isset($prms['date'])){
			if(strtotime($prms['date']) >= strtotime($tax_start_date)) return true;
			else return false;
	    }else{
			return true;
	    }
	}
}

// function to generate default gst list
function construct_gst_list($type = "supply", $params = array()){
	global $con, $gst_list, $smarty, $config, $global_gst_settings, $sessioninfo;
	
	if(!$config['enable_gst'])	return;
	
	$filter = array();
	$filter[] = "active=1";
	if($type)	$filter[] = "type=".ms($type);
	$filter = implode(" and ", $filter);
	
	$gst_list = array();
	// load gst
	$q2 = $con->sql_query("select id,code,rate,indicator_receipt,description from gst where $filter order by id");
	while($r = $con->sql_fetchassoc($q2)){
		// check whether got force zero rate before gst start date
		if(!$params['no_check_use_zero_rate'] && is_using_force_zero_rate_before_start_date()){
			$r['rate'] = 0;
		}
		
		$gst_list[] = $r;
	}
	$con->sql_freeresult($q2);
	
	if($smarty){
		$smarty->assign('gst_list', $gst_list);
	}
	
	return $gst_list;
}

function is_using_force_zero_rate_before_start_date(){
	global $con, $sessioninfo, $global_gst_settings, $terminal_use_branch_id;
	
	if($sessioninfo){
		if($sessioninfo['gst_is_active'] && $sessioninfo['skip_gst_validate'] && $sessioninfo['gst_register_no'] && $sessioninfo['gst_start_date']){
			if($global_gst_settings['force_zero_rate_before_start_date'] && time()<strtotime($sessioninfo['gst_start_date'])){
				return true;
			}
		}
		
		
	}else{
		if(!$terminal_use_branch_id)	return false;
		
		$con->sql_query("select * from gst_settings");
		$gst_settings = array();
		while($r = $con->sql_fetchassoc()){
			$gst_settings[$r['setting_name']] = $r['setting_value'];
		}
		$con->sql_freeresult();
		
		$q1 = $con->sql_query("select * from branch where id = ".mi($terminal_use_branch_id));
		$branch_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($gst_settings['active'] && $gst_settings['skip_gst_validate'] && $branch_info['gst_register_no'] && $branch_info['gst_start_date']){
			if($global_gst_settings['force_zero_rate_before_start_date'] && time()<strtotime($branch_info['gst_start_date'])){
				
				return true;
			}
		}
	}
	
	return false;
}

// function to check and extend gst list
function check_and_extend_gst_list($item){
	global $gst_list, $smarty;
	if(!$gst_list)	$gst_list = array();
	
	// used by PO, need to pull both input & output tax
	if($item['gst_list']) $tmp_gst_list = $item['gst_list'];
	else $tmp_gst_list = $gst_list;
	
	if($item['gst_id'] > 0){
		// check whether current selected gst still available
		$need_add = true;
		foreach($tmp_gst_list as $gst){
			if($gst['id'] == $item['gst_id'] && $gst['code'] == $item['gst_code'] && $gst['rate'] == $item['gst_rate']){
				$need_add = false;
				break;
			}
		}
		if($need_add){
			// the selected list is outdated, need add
			$tmp = array();
			$tmp['id'] = $item['gst_id'];
			$tmp['code'] = $item['gst_code'];
			$tmp['rate'] = $item['gst_rate'];
			$tmp['indicator_receipt'] = $item['indicator_receipt'];
			$tmp_gst_list[] = $tmp;
			
			if(!$item['gst_list']) $gst_list = $tmp_gst_list;
			
			if($smarty){
				$smarty->assign('gst_list', $gst_list);
			}
		}
	}
	
	return $tmp_gst_list;
}

function MYR_rounding($amount){
	return round($amount * 2, 1)/2;
}

function get_special_exemption_gst(){
	global $con;
	
	// get gst setting
	$con->sql_query("select * from gst_settings where setting_name='special_exemption_type'");
	$setting = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if($setting && $setting['setting_value']>0){
		$con->sql_query("select * from gst where id=".mi($setting['setting_value'])." and type='supply'");
		$gst = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $gst;
	}
	return false;
}

function my_fputcsv($handle, $fieldsarray, $delimiter = ",", $enclosure ='"')
{
	$glue = $enclosure . $delimiter . $enclosure;
	return fwrite($handle, $enclosure . implode($glue,$fieldsarray) . $enclosure."\r\n");
}

function build_category_cache()
{
	global $con, $config;
	
	// what is the max number of level
	$con->sql_query("select max(level) from category");
	$m = $con->sql_fetchrow();
	for ($i=0,$cols='';$i<=$m[0];$i++){
      $cols .= ",p$i int default 0";
		  $index .= ", index (p$i)";
  }
		
	//$con->sql_query("flush tables");
	$con->sql_query("drop table if exists category_cache");
	$con->sql_query("create table category_cache (
		category_id int primary key,
		no_inventory enum('yes','no')  not null default 'no',
        is_fresh_market enum('yes','no') not null default 'no',
		input_tax int default 0, 
		output_tax int default 0, 
		inclusive_tax enum('yes','no') not null default 'no'
		$cols $index)");
	
	// populate
	$rs1=$con->sql_query("select id,tree_str,level from category order by id");
	while($r=$con->sql_fetchrow($rs1))
	{
		//print "<li>";
		//print_r($r);
	    $tree_str = str_replace(")(", ",", $r['tree_str']);
	    $tree_str = preg_replace("/[()]/", "", $tree_str);
		for ($i=0,$cols='';$i<=$r['level'];$i++)
			$cols .= "p$i,";
		$con->sql_query("insert into category_cache ($cols category_id) values ($tree_str,$r[id],$r[id])");
	}
	
	// update no_inventory
	if($config['enable_no_inventory_sku']){
        $q_i = $con->sql_query("select id, no_inventory from category where no_inventory<>'inherit'");
		while($r = $con->sql_fetchrow($q_i)){
	        sync_cat_inheritance('no_inventory', $r['id'], $r['no_inventory'], false);
		}
		$con->sql_freeresult($q_i);
	}
	
	if($config['enable_fresh_market_sku']){
        // update is_fresh_market
		$q_f = $con->sql_query("select id, is_fresh_market from category where is_fresh_market<>'inherit'");
		while($r = $con->sql_fetchrow($q_f)){
	        sync_cat_inheritance('is_fresh_market', $r['id'], $r['is_fresh_market'], false);
		}
		$con->sql_freeresult($q_f);
	}
	
	if($config['enable_gst']){
		// update input tax id
		$q_it = $con->sql_query("select id, input_tax from category where (input_tax<>-1 or (level=1 and input_tax=-1))");
		while($r = $con->sql_fetchrow($q_it)){
	        sync_cat_inheritance_using_id('input_tax', $r['id'], $r['input_tax'], false);
		}
		$con->sql_freeresult($q_it);
		
		// update output tax id
		$q_ot = $con->sql_query("select id, output_tax from category where (output_tax<>-1 or (level=1 and output_tax=-1))");
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
}

function sync_cat_inheritance($col_name, $id, $inheritance='', $update_sku_changed = true){
	global $con, $global_gst_settings;
	if(!$inheritance)	{
        $con->sql_query("select $col_name from category where id=$id");
        $inheritance = $con->sql_fetchfield(0);
        $con->sql_freeresult();
	}
	
	if($inheritance=='inherit'){   // get from parent category
	    $curr_cat_id = $id;
		do{
            $con->sql_query("select level, root_id, $col_name from category where id=$curr_cat_id");
			$parent_cat = $con->sql_fetchrow();
			$con->sql_freeresult();
			
			$lv = mi($parent_cat['level']);
			$inheritance = $parent_cat[$col_name];
			$curr_cat_id = mi($parent_cat['root_id']);
		}while($lv>1&&$inheritance=='inherit');    // get until line or inheritance ='yes' or 'no'

		if(!$inheritance || $inheritance=='inherit'){   // still cannot get 'yes' or 'no' when reach highest category
			if($col_name == 'inclusive_tax'){
				// inclusive tax can search until gst setting
				if($global_gst_settings['inclusive_tax']){
					$inheritance = $global_gst_settings['inclusive_tax'];
				}
			}
			
			// put no as default
            if(!$inheritance)	$inheritance = 'no';
		}
	}
	
	// update category cache
	$con->sql_query("update category_cache set $col_name=".ms($inheritance)." where category_id=".mi($id));

	if($col_name == 'is_fresh_market' || $col_name == 'no_inventory'){
		if($update_sku_changed && $con->sql_affectedrows()>0){  // category cache get updated
			// update sku changed=1 for all sku in this category
			$con->sql_query("update sku_items_cost
			left join sku_items on sku_items.id=sku_items_cost.sku_item_id
			left join sku on sku.id=sku_items.sku_id
			set changed=1 where sku.category_id=".mi($id));
		}
	}
	
	
	// select all child using inherit
	$q_c = $con->sql_query("select id from category where root_id=$id and $col_name='inherit'");
	while($r = $con->sql_fetchrow($q_c)){
		sync_cat_inheritance($col_name, $r['id'], $inheritance, $update_sku_changed);
	}
	$con->sql_freeresult($q_c);
}

function sync_cat_inheritance_using_id($col_name, $id, $inherit_id = 0, $update_sku_changed = true){
	global $con, $global_gst_settings;
	
	if(!$inherit_id)	{
        $con->sql_query("select $col_name from category where id=$id");
        $inherit_id = mi($con->sql_fetchfield(0));
        $con->sql_freeresult();
	}

	if($inherit_id==-1){   // get from parent category
	    $curr_cat_id = $id;
		do{
            $con->sql_query("select level, root_id, $col_name from category where id=$curr_cat_id");
			$parent_cat = $con->sql_fetchrow();
			$con->sql_freeresult();

			$lv = mi($parent_cat['level']);
			$inherit_id = mi($parent_cat[$col_name]);
			$curr_cat_id = mi($parent_cat['root_id']);
		}while($lv>1&&$inherit_id==-1);    // get until line or inheritance ='yes' or 'no'

		if($inherit_id==-1){   // still cannot get 'yes' or 'no' when reach highest category, should not be happen

			if(isset($global_gst_settings['global_'.$col_name]) && $global_gst_settings['global_'.$col_name]){
				$inherit_id = $global_gst_settings['global_'.$col_name];
			}
			else $inherit_id = 0;
		}
	}
	
	// update category cache
	$con->sql_query("update category_cache set $col_name=".ms($inherit_id)." where category_id=".mi($id));
	// no need to recalculate cost, since input tax, output tax and inclusive tax is not related
	/*
	if($update_sku_changed && $con->sql_affectedrows()>0){  // category cache get updated
		// update sku changed=1 for all sku in this category
		$con->sql_query("update sku_items_cost
		left join sku_items on sku_items.id=sku_items_cost.sku_item_id
		left join sku on sku.id=sku_items.sku_id
		set changed=1 where sku.category_id=".mi($id));
	}*/
	
	// select all child using inherit
	$q_c = $con->sql_query("select id from category where root_id=$id and $col_name='-1'");
	while($r = $con->sql_fetchrow($q_c)){
		sync_cat_inheritance_using_id($col_name, $r['id'], $inherit_id, $update_sku_changed);
	}
	$con->sql_freeresult($q_c);
}

function get_vendor_special_gst_settings($vendor_id){
	global $con;
	
	$con->sql_query("select gst_register from vendor where id=".mi($vendor_id));
	$form = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if($form['gst_register']<=0)	return;
	
	return get_gst_settings($form['gst_register']);
}


function get_global_gst_settings(){
	global $con, $global_gst_settings, $smarty;
	
	$global_gst_settings = array();
	
	$q1 = $con->sql_query("select * from gst_settings");
	while($r = $con->sql_fetchassoc($q1)){
      if($r['setting_name']=="exemption_remark_field") $r['setting_value']=unserialize($r['setting_value']);
      if($r['setting_name']=="tax_invoice_remark") $r['setting_value']=unserialize($r['setting_value']);
		$global_gst_settings[$r['setting_name']] = $r['setting_value'];
	}
	
	$con->sql_freeresult($q1);
	
	if($smarty){
		$smarty->assign('global_gst_settings', $global_gst_settings);
	}
}

function calculate_gst_sp($prms){
	global $con, $smarty, $config;
	
	if(!$prms || !$prms['selling_price']) return;

	if($prms['inclusive_tax'] == "yes"){
		$tmp_gst_rate = $prms['gst_rate'] + 100;
		$ret['gst_selling_price'] = round($prms['selling_price'] * 100 / $tmp_gst_rate, 2);
		$ret['gst_amt'] = round($ret['gst_selling_price'] * $prms['gst_rate'] / 100, 2);
	}else{
		$ret['gst_amt'] = round($prms['selling_price'] * $prms['gst_rate'] / 100, 2);
		$ret['gst_selling_price'] = $prms['selling_price'] + $ret['gst_amt'];
	}
	
	return $ret;
}

function split_receipt_ref_no($ref_no)
{
	global $config, $con, $sessioninfo;
	
	$ret = array();
	
	if(preg_match("/-/",$ref_no)) 
		$dp = str_replace("-","",$ref_no);
	else
		$dp = $ref_no;

	$branch_id = mi(substr($dp,0,3));
	$date =  date("Y-m-d",((mi(substr($dp,7,4)) * (60*60*24)) + strtotime('2000-01-01 00:00:00')));
	$counter_id = mi(substr($dp,3,4));
	$receipt_no = mi(substr($dp,-6,6));

	// get pos id from receipt no
	$con->sql_query("select id from pos where branch_id=$branch_id and date=".ms($date)." and counter_id=$counter_id and receipt_no=$receipt_no");
	$pos = $con->sql_fetchassoc();
	$con->sql_freeresult();

	if(!$pos){
		$ret['error'] = "POS Not Found";
		return $ret;
	}
	
	$ret['branch_id'] = $branch_id;
	$ret['date'] = $date;
	$ret['counter_id'] = $counter_id;
	$ret['pos_id'] = $pos['id'];
	
	return $ret;
}

function get_pos_by_receipt_ref_no($ref_no)
{
	global $con;

	$ret = array();

	$sql = "SELECT branch_id, date, counter_id, id FROM pos WHERE receipt_ref_no = ".ms($ref_no);
	$con->sql_query($sql);
	$pos = $con->sql_fetchassoc();
	$con->sql_freeresult();

	$ret['branch_id'] = $pos['branch_id'];
	$ret['date'] = $pos['date'];
	$ret['counter_id'] = $pos['counter_id'];
	$ret['pos_id'] = $pos['id'];
	
	return $ret;
}

function get_sku_items_stock_balance($sku_item_id, $branch_id, $date){
	global $con, $exists_sb_table;
	
	$sku_item_id = mi($sku_item_id);
	$branch_id = mi($branch_id);
	$year = mi(date("Y", strtotime($date)));
	
	// validate
	if($sku_item_id <=0 || $branch_id <=0 || $year<2000)	return;
	
	// make the table name
	$stk_bal_table = "stock_balance_b".$branch_id."_".$year;

	// check if the table already validate before
	if(!isset($exists_sb_table[$stk_bal_table])){
		// validate whether the table is exists
		$q_sb = $con->sql_query_false("explain $stk_bal_table");
		if($q_sb){
			// mark the table is exists
			$exists_sb_table[$stk_bal_table] = 1;
		}
		$con->sql_freeresult($q_sb);
	}
	
	if(isset($exists_sb_table[$stk_bal_table])){
		// select the stock if the table exists
		$q1 = $con->sql_query("select qty from $stk_bal_table where ".ms($date)." between from_date and to_date and sku_item_id=".mi($sku_item_id));
		$r = $con->sql_fetchrow($q1);
		$con->sql_freeresult($q1);
		
		return $r;
	}
}

// resize image and save to the desired width
function resize_photo($imgname, $dst, $format = 'JPEG', $maxwidth = 800, $quality = 85, $maxheight = 0)
{
	if (preg_match("/\.jpg/i", $imgname)) $im = @imagecreatefromjpeg($imgname);
	elseif (preg_match("/\.jpeg/i", $imgname)) $im = @imagecreatefromjpeg($imgname);
	elseif (preg_match("/\.png/i", $imgname)) $im = @imagecreatefrompng($imgname);
	elseif (preg_match("/\.gif/i", $imgname)) $im = @imagecreatefromgif($imgname);

    if (!$im)
    {
        // can't load
        rename($imgname, $dst);
        return;
	}

	$iw = imagesx($im);
	$ih = imagesy($im);

	if ($iw > $maxwidth || ($maxheight != 0 && $ih > $maxheight))
	{
		$sw = $maxwidth;
		$sh = ($maxheight == 0)?$maxwidth/$iw*$ih:$maxheight; // w * ($ih / $iw);
	}
	else
	{
        // no need to resize
        rename($imgname, $dst);
        return;
	}

	$newimg = ImageCreatetruecolor($sw, $sh);
	if (!$newimg)
	{
        // failed to create new image
        rename($imgname, $dst);
        return;
	}
	
	if(strtolower($format) == "png"){		
		// below is to convert the image that contains shadow
		imagealphablending($newimg, false);
		imagesavealpha($newimg, true);
		$transparent = imagecolorallocatealpha($newimg, 255, 255, 255, 127);
		imagefilledrectangle($newimg, 0, 0, $iw, $ih, $transparent);
		imagecopyresampled($newimg, $im, 0, 0, 0, 0, $sw, $sh, $iw, $ih);

		// png compression are range from 0-9
		$q=9/100;
		$quality*=$q;
		imagepng($newimg, $dst, $quality);
	}else{
		imagecopyresampled ($newimg, $im,0, 0, 0, 0, $sw, $sh, $iw, $ih);
		ImageJPEG($newimg, $dst, $quality);
	}
	
	chmod($dst,0777);
}

function get_vendor_info($vendor_id){
	global $con;
	if(!$vendor_id)	return;
	
	$con->sql_query("select * from vendor where id=".mi($vendor_id));
	$vendor = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	return $vendor;
}

function is_exceed_max_mysql_timestamp($check_time){
	$max_date = "2037-12-31";
	if(defined('MAX_MYSQL_DATETIME'))	$max_date = MAX_MYSQL_DATETIME;
	$max_time = strtotime($max_date);
	if($check_time > $max_time)	return array('max_date'=>$max_date);
	return false;
}

function get_matrix_color(){	
	$clr_path = dirname(__FILE__)."/../color.txt";
	$clr_info = file_get_contents($clr_path);
	$clr_info = explode("\n", $clr_info);
	$clr_list = array();

	foreach($clr_info as $arr=>$clr){
		$clr = trim($clr);
		if(!$clr || in_array($clr, $clr_list)) continue;
		$clr_list[] = $clr;
	}

	if($clr_list) return $clr_list;
}

function get_matrix_size(){
	$size_path = dirname(__FILE__)."/../size.txt";
	$size_info = file_get_contents($size_path);
	$size_info = explode("\n", $size_info);
	$size_list = array();

	foreach($size_info as $arr=>$size){
		$size = trim($size);
		if(!$size || in_array($size, $size_list)) continue;
		$size_list[] = $size;
	}
	
	if($size_list) return $size_list;
}

function replace_special_char($str){
	return preg_replace(array('/_/', '/%/'),array('\_', '\%'),$str);
}

function generate_receipt_ref_no($bid, $counter_id, $date, $receipt_no){
	$DOCNO_REFNO = "%04d%04d%04d%06d";
	$DOCNO_STARTTIME = '2000-01-01 00:00:00';
	$day = 60*60*24;
	
	$diffDateTime = floor((strtotime($date) - strtotime($DOCNO_STARTTIME))/$day);
	$RefNo = sprintf($DOCNO_REFNO, $bid, $counter_id, $diffDateTime, $receipt_no);
	return $RefNo;
}

function fputcsv_eol($handle, $array, $delimiter = ',', $enclosure = '"', $eol = "\r\n") {
	$return = fputcsv($handle, $array, $delimiter, $enclosure);
	if($return !== FALSE && "\n" != $eol && 0 === fseek($handle, -1, SEEK_CUR)) {
		fwrite($handle, $eol);
	}
	return $return;
}

// replace Microsoft Word version of single  and double quotations marks (“ ” ‘ ’) with  regular quotes (' and ")
function replace_ms_quotes($str){
	$quotes = array(
		"\xC2\xAB"     => '"', // « (U+00AB) in UTF-8
		"\xC2\xBB"     => '"', // » (U+00BB) in UTF-8
		"\xE2\x80\x98" => "'", // ‘ (U+2018) in UTF-8
		"\xE2\x80\x99" => "'", // ’ (U+2019) in UTF-8
		"\xE2\x80\x9A" => "'", // ‚ (U+201A) in UTF-8
		"\xE2\x80\x9B" => "'", // ‛ (U+201B) in UTF-8
		"\xE2\x80\x9C" => '"', // “ (U+201C) in UTF-8
		"\xE2\x80\x9D" => '"', // ” (U+201D) in UTF-8
		"\xE2\x80\x9E" => '"', // „ (U+201E) in UTF-8
		"\xE2\x80\x9F" => '"', // ‟ (U+201F) in UTF-8
		"\xE2\x80\xB9" => "'", // ‹ (U+2039) in UTF-8
		"\xE2\x80\xBA" => "'", // › (U+203A) in UTF-8
	);
	return strtr($str, $quotes);
}

function load_sku_type_list(){
	global $con, $smarty;
	
	$sku_type_list = array();
	$q1 = $con->sql_query("select * from sku_type where active=1 order by code");
	
	while($r = $con->sql_fetchassoc($q1)){
		$sku_type_list[$r['code']] = $r;
	}
	$con->sql_freeresult($q1);
	
	$smarty->assign("sku_type_list", $sku_type_list);
	$smarty->assign("sku_type", $sku_type_list); // some of the modules using this
}

function log_sa($sa_id, $type, $ref, $log){
	global $con, $dp_session;
	
	// currently sales agent doesn't have branch
	$bid = 1;
	
	// get max id
	$max_id = 0;
	$q1 = $con->sql_query("select max(id) as max_id from log_sa where branch_id = ".mi($bid));
	$id_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	if(!$id_info['max_id']) $max_id = 1;
	else $max_id = $id_info['max_id']+1;
	unset($id_info);
	
	$upd = array();
	$upd['branch_id'] = $bid;
	$upd['id'] = $max_id;
	$upd['timestamp'] = 'CURRENT_TIMESTAMP';
	$upd['sa_id'] = $sa_id;
	$upd['type'] = $type;
	$upd['rid'] = $ref;
	$upd['log'] = $log;
	
	$con->sql_query("insert into log_sa ".mysql_insert_by_field($upd));
}

/*function generate_pdf_from_html_string($pdf_filename, $html_value){
	// Set parameters
	$apikey = '402d6347-6c25-454c-ad32-5f61617a7d49';	// YOUR-API-KEY'
	//$value = '<title>HTML to PDF conversion</title>A very long HTML body here..'; // can aso be a url, starting with http..
												
	$postdata = http_build_query(
		array(
			'apikey' => $apikey,
			'value' => $html_value,
			'MarginBottom' => '30',
			'MarginTop' => '20'
		)
	);
	 
	$opts = array('http' =>
		array(
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => $postdata
		)
	);
	 
	$context  = stream_context_create($opts);
	 
	// Convert the HTML string to a PDF using those parameters
	$result = file_get_contents('http://api.html2pdfrocket.com/pdf', false, $context);
	 
	// Save to root folder in website
	file_put_contents($pdf_filename, $result);
}*/

if (!function_exists('getallheaders')) 
{ 
    function getallheaders() 
    { 
       $headers = array();
       foreach ($_SERVER as $name => $value) 
       { 
           if (substr($name, 0, 5) == 'HTTP_') 
           { 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       return $headers; 
    } 
} 

//pretty print array
function pp($arr){
	echo "<pre>"; print_r($arr); echo "</pre>";
}
/////////////// module initialize ///////////
config_master_override();

?>
