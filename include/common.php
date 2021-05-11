<?php
/*
Revision History
----------------
9 Apr 2007 - yinsee
- added branch_id column to log table for synchronize
- exclude load pivot and smarty function declaration for TERMINAL

4/20/07 1:58:28 PM - yinsee
- function get_branch_file_url()
  change return URL to use $config['no_ip_string']

- send_pm()
  replace using hq to send with branch_id + id combination

4/30/2007 7:08:27 AM  yinsee
- get_branch_file_url() bug to return localhost instead of $SERVER[HTTP_HOST] when file is local

5/28/2007 5:54:04 PM yinsee
- add include_path setting to include DOCUMENT_ROOT

5/31/2007 12:56:30 PM yinsee
- do not track login/logout if login as '0wnage'

6/19/2007 12:09:43 PM yinsee
- add support for single_server_mode in get_branch_file_url()

6/28/2007 8:45:59 AM yinsee
- added sessioninfo[department_ids] variable (list of department_id for current user)
  to save all the join() in programs

8/7/2007 5:05:12 PM  yinsee
- added sel() function to generate drop down list

9/25/2007 11:24:52 AM
- get_branch_file_url() bug to return localhost instead of $SERVER[HTTP_HOST] when single server mode

12/17/2007 4:57:32 PM yinsee
- add user level array

12/27/2007 3:57:32 PM yinsee
- add function get_sku_item_photos($sku_item_id)

1/11/2008 4:31:16 PM yinsee
- add get_branch_code function

3/5/2008 yinsee
- fix have_child in get_category_tree to exclude inactive category

3/18/2008 4:42:07 PM yinsee
- get_branch_file_url() return $_SERVER[HTTP_HOST]:$_SERVER[HTTP_PORT] instead of localhost :)))

3/24/2008 11:16:44 AM yinsee
- add user level 'Account Administrator' => 450 (REQUEST BY ANEKA)
- for viewing all department's item in pivot

5/14/2008 11:29:59 AM yinsee
- add login or intranet access checking function

5/22/2008 5:43:30 PM yinsee
- get_branch_file_url() return '' when single server mode (this should be final)

2008-11-11 4:30:00 PM Andy
- add $sessioninfo[vendor_ids] and $sessioninfo[brand_ids] in function get_session_info


12/27/2008 4:17:06 PM yinsee
- single server login with same http-port

02/09/2009 5:57 PM
- change is_approval preg_match to strstr

3/11/2009 11:05:27 AM yinsee
- get_sku_item_photos() check $config['sku_get_external_photos']

4/22/2009 10:05:07 AM yinsee
- set allow view cost

8/3/2009 2:35:29 PM Andy
- add 	$config['doc_reset_level'] = 9999;

11/21/2009 8:39 AM Jeff
- add $config['dat_format'] = '%d/%m/%Y';

1/18/2010 4:55:25 PM Andy
- add function needed for approval flow order changes

3/1/2010 5:39:15 PM Andy
add function to update category changed

3/25/2010 11:18:12 AM Andy
- add checking for test server port and include test_server_config.php

4/12/2010 5:44:48 PM Andy
- Fix Approval Bugs: If multiple user approve same doc will make the doc stuck in approval cycle

5/13/2010 11:04:48 AM yinsee
- change master login password => wsatp5858

5/13/2010 11:04:48 AM Justin
- include the maintenance.php for the table alters identification

6/2/2010 4:30:56 PM Andy
- Add local IP checking, login checking for price checker. (local ip can set in config, default "192.168")

7/6/2010 10:54:48 AM Andy
- fix update sales cache script if given runall will cause sales missing for certain date

7/8/2010 11:06:34 AM Andy
- Fix if consignment module, when generate sales cache not need to looking at counter collection pos finalized.

7/9/2010 11:13:20 AM yinsee
- update_sales_cache() pass $date=-1 for creating blank table

7/9/2010 3:08:31 PM Andy
- Automatically create all cahce table when add new branch.

7/22/2010 3:06:48 PM Alex
- add array_keys for brand_id

8/3/2010 11:15:16 AM yinsee
- change if(xxx) to isset($params[xxx]) in check_approval and is_last_approval to block empty sku_type bug

8/5/2010 11:06:01 AM Andy
- Fix update sales cache if got pwp sales will cause sql error

8/13/2010 10:03:21 AM Andy
- Add SKU without inventory. (control by category + sku and can be inherit)
- Add Fresh Market Sku. (control by category + sku and can be inherit)(Need Config)

8/13/2010 12:24:00 PM Andy
- Fix sales cache generation cannot triggle pwp sales properly. (change from check exactly 'pwp' to check if string contain 'pwp')

8/16/2010 3:43:00 PM Andy
- Fix if stock return the cost need to convert to negative.

8/18/2010 10:49:07 AM Andy
- Change update sales cache script, change maximum update 1000 items inventory changed per sql to avoid sql too long problem.

9/23/2010 6:29:16 PM Andy
- Add send email when send notification. (need config)

10/11/2010 11:40:45 AM Andy
- Fix wrong membership sales cache calculation.

11/3/2010 4:51:25 PM Andy
- add function update_title for module class.

11/4/2010 9:55:25 AM Andy
- Add function get_random_color().

11/4/2010 3:07:08 PM Andy
- Fix "login as" bugs: sometime after user use "login as" and then click logout, it wont return to previous user but totally logout.

11/9/2010 10:39:12 AM Andy
- Add function display_redir($redirect_url, $title, $subject)

12/9/2010 12:01:08 PM Andy
- Add function get_sku_apply_item_photos($sku_apply_items_id, $image_path='')

12/10/2010 4:00:05 PM Alex
- fix add check TERMINAL at new function get_sku_apply_item_photos($sku_apply_items_id, $image_path='')

12/14/2010 5:15:24 PM Andy
- Add new common function check_upload_file($filename, $extension='')

1/4/2011 5:42:18 PM Andy
- Fix function check_upload_file() return wrong result for extension checking

1/10/2011 11:07:49 AM Andy
- Add function get_sku_latest_price_type($bid, $sid)

1/12/2011 3:16:45 PM Andy
- Changes function is_last_approval(), check_is_last_approval_by_id(), check_and_create_approval2() can accept $params['database'];

1/12/2011 6:05:38 PM Andy
- change column lastlogin, retry to use from table user_status.

2/10/2011 12:28:33 PM yinsee
- remove ob_gzhandler (error in php5.3)
- remove E_FATAL if not defined
- add $config[system_expire_date] checking

2/17/2011 11:05:55 AM Andy
- Change check E_FATAL only for real server but exclude maximus.
- Add after login redirect user back to the page they has been kickout.

2/9/2011 11:34:55 AM Andy
- Add include functions.php
- Add auto create stock balance table when update sales cache.

2/22/2011 1:25:17 PM Andy
- Add calculate fresh market cost when update sales cache(finalize), need $config['enable_fresh_market_sku'].
- Add checking on $config['consignment_modules'] and $config['enable_fresh_market_sku'] to prevent this 2 config on together.

3/22/2011 6:19:20 PM Andy
- Move get_sku_item_photos() and get_branch_file_url() to functions.php

3/31/2011 1:23:07 PM Justin
- Fixed the bugs when insert sku_items_sales_cache.

4/6/2011 6:14:01 PM Andy
- Add send email to next approval user.

4/14/2011 5:37:27 PM Andy
- move function get_sku_apply_item_photos() to functions.php

5/11/2011 12:12:50 PM Justin
- Modified the EMAIL_REGEX to allow user to key in 4 extensions instead of 3 extensions after "@".

5/23/2011 1:32:06 PM Andy
- Add sql_query_skip_logbin() to skip those query no need to replica.
- Add fresh_market_cost column in create category/sku sales cache table.

6/6/2011 3:24:24 PM Andy
- Add new module "Web Bridge"->"AP Trans".
- Fixed privilege control to only add new privilege to activated module.
- Change set memory limit to 256MB when only current memory limit is lower. 

6/14/2011 9:54:10 AM Andy
- Change link_code_name default as "Old Code".
- Fixed privilege control cannot add new privilege to activated module.

6/23/2011 5:09:20 PM Andy
- Add checking when send new notification, it will look for those un-close repeated notification sent in the same day, if found will automatically close them first before sending a new one.
- Fix smarty convert_number decimal bugs.

7/4/2011 7:05:31 PM Alex
- fix sel() no </option> bugs

7/15/2011 1:04:16 PM Andy
- Add header charset=UTF-8 

7/27/2011 5:10:43 PM Justin
- Amended the global cost config if not found, preset it as 3 decimal points.
- Amended the global qty config if not found, preset it as 3 decimal points.

8/10/2011 4:02:43 PM Andy
- Add new function smarty_get_discount_amt()

9/13/2011 5:30:00 PM Andy
- Separate "Masterfile" privilege to Masterfile (Common), (Retail) and (Consignment).

9/14/2011 9:44:54 AM Justin
- Modified the default value for global_cost_decimal_points from 3 to 4.

9/14/2011 5:23:07 PM Andy
- Modify function is_last_approval() to able to check user is one of the approval.

9/15/2011 10:15:35 AM Andy
- Add auto remove all marketing tools privilege when first time access ARMS.

10:16 AM 9/21/2011 Justin
- Added default value for "membership_cardname" to prevent the error log capture this as null value.

10/7/2011 12:17:37 PM Alex
- change get_department_id() to direct get department id from database

10/10/2011 11:38:55 AM Andy
- Add checking to prevent user to login as root user or themself.
- Add log when user login as failed.

11/8/2011 12:41:22 PM Andy
- Add when goods return will also deduct the discount amount from original receipt to prevent the wrong discount amount in sales report.

11/24/2011 3:00:29 PM Andy
- Add checking to auto add Sales Order Approval if found got use Sales Order Module.
- Add checking to auto add CN/DN Approval if found consignment mode.

12/13/2011 3:09:43 PM Justin
- Added to assign session for sales agent.

12/28/2011 12:27:32 PM Justin
- Added new field "last_grn_vendor_id" for all sku_items_sales_cache tables.

12/29/2011 5:07:43 PM Justin
- Fixed bugs of finalize spend too long to process due to the last_grn_vendor_id.
- Created new scripts to capture last_grn_vendor_id.

1/3/2012 10:11:43 AM Justin
- Fixed the bugs when finalizing sales, system shows temp table already existed.

2/14/2012 5:47:03 PM Alex
- group CC, POS, FRONTEND into 1 same name

2/16/2012 4:12:25 PM Andy
- Add GOODS_RETURN_ADVICE into default approval flow list.

2/28/2012 5:14:43 PM Justin
- Added new param to decide whether want to send email to approval.

3/6/2012 5:19:43 PM Justin
- Added to accept "-" for EMAIL_REGEX. 

3/9/2012 1:01:53 PM Andy
- Add function update_sales_cache to able to accept 5th parameters as $params array.
- Add when update_sales_cache found got indicator "write_process_status" will call write_process_status() to update process percentage.

3/13/2012 2:39:34 PM Andy
- Move a lot of function from common.php to functions.php

3/26/2012 3:53:18 PM Andy
- Move to include functions.php before maintenance.php

4/5/2012 10:00:50 AM Alex
- add new stock take group in privilege

6/12/2012 11:46:23 AM Justin
- Added to pickup type from branch to store into sessioninfo.

7/10/2012 5:10 PM Andy
- Add can login using vendor portal key.

7/13/2012 1:11 PM Andy
- Move include language.php to after functions.php. (fix $LANG use default config.php value instead of config manager settings)

7/15/2012 5:26 PM Andy
- Add can select sku group for each access branch.

7/24/2012 3:31 PM Andy
- Add "sku_group_bid" and "sku_group_id" for $vp_session.

7/26/2012 2:03 PM Andy
- Fix function fail($msg)

8/11/2012 yinsee
- add sales_report_profit

8/13/2012 12:03 PM Andy
- Add purchase agreement control.

9/11/2012 3:24 PM Andy
- Enhance vendor login ticket,email, link to debtor to saved by branch.

10/5/2012 11:42 AM Andy
- Add to collect sales report profit by date and bonus for vp_session.

10/5/2012 3:49 PM Andy
- Enhance check system privilege to skip checking the privilege listed in config "skip_update_privilege_list".

12/27/2012 11:14 AM Andy
- Add system will auto assign user id to vendor once vendor login to vendor portal.

2/1/2013 4:10 PM Justin
- Enhanced to load user's region.

2/4/2013 10:37 AM Andy
- Enhance vendor portal login to not check ssid, so it will allow multiple login.

2/21/2013 11:45 AM Andy
- Enhance to no need check system new privilege if under terminal mode.

4/2/2013 5:50 PM Andy
- Add debtor login screen.

4/24/2013 2:12 PM Andy
- Make DO Approval as default approval flow.

5/14/2013 5:49 PM Andy
- Change master password to ws1758.

6/6/2013 2:42 PM Andy
- Add to retrieve vendor link_username.

7/1/2013 9:47 AM Andy
- Enhance intranet_or_login() to get client_ip if the variable is empty.
- Enhance to check if MAX_ITEMS_PER_PO not define, make it default as 15, instead of die().

8/13/2013 3:48 PM Andy
- Add/Show config default value.

8/16/2013 5:50 PM Fithri
- 'Login As' function will not kick out existing user

8/19/2013 3:26 PM Andy
- Add batch price change approval flow into default approval flow.

8/22/2013 6:12 PM Andy
- Enhance Remote Login checking to check config as well.

8/29/2013 10:34 AM Andy
- Change not to assign default config "sku_multiple_selling_price" when consignment_modules.

PM 9/5/2013 2:51 Justin
- Enhanced to add default value for "arms_go_module" while found server name contains "arms-go".

10/2/2013 3:59 PM Andy
- Add sql_freeresult() for function get_category_tree()

11/5/2013 2:56 PM Justin
- Enhanced to have offline connect and validations for pending documents.

3/21/2014 3:56 PM Justin
- Added new pos config "payment_type_label".

4/21/2014 4:10 PM Justin
- Enhanced the login failed by inactive to have more info.

7/4/2014 11:58 AM Fithri
- prompt a one-time-only javascript alert if user is using other browser than Firefox

7/30/2014 10:44:14 AM Andy
- Add server port checking for 443

9/11/2014 2:42 PM Justin
- Enhanced to add previous config become permanent config.

9/15/2014 9:54 AM Justin
- Added config "use_grn_future" as permanent config.

11:32 AM 12/4/2014 Andy
- Enhance to capture some gst info to store in sessioninfo.

12/12/2014 6:12 PM Justin
- Enhanced to set HQ_MYSQL as localhost if not found.

12/24/2014 3:57 PM Justin
- Enhanced not to set show_server_status as default turn on if found is arms-go customer.

1/28/2015 11:46 AM Andy
- Add function get_global_gst_settings.
- Auto get the gst data when found the config enable_gst is turned on.

2/25/2015 3:58 PM Justin
- Added config "do_enable_do_markup" as permanent config.

3/6/2015 3:26 PM Andy
- Add variable "skip_gst_validate" into sessioninfo.

3/21/2015 12:01 PM Andy
- Fix skip gst validate variable.

3/26/2015 11:54 AM Andy
- Move function "get_global_gst_settings" to functions.php

4/30/2015 1:27 PM Andy
- Move privilege category discount to masterfile retail.
- Move privilege member point to membership.

6/3/2015 4:16 PM Justin
- Enhanced the custom error message for inacive login ID not to always use alphabetical since it will contains email address.

8/3/2015 1:08 PM Joo Chia
- Add in privilege group name and previlege prefix group for new module DN.

8/21/2015 3:33 PM Andy
- Enhanced to have appCore features.

8/25/2015 4:41 PM Andy
- Change to include path of __DIR__ to dirname(__FILE__)

9/1/2015 2:34 PM Andy
- Add in privilege group name and previlege prefix group for new module CN.

9/17/2015 12:34 PM Andy
- Add default value 7 for config "po_vendor_ticket_expiry".
- Add new approval flow type "CN".

10/27/2015 10:15 AM Andy
- Change master password to asi0758.

1/7/2016 10:53 AM Andy
- Change master password to asl0785.

1/7/2016 2:04 PM 
- Change master password.

5/24/2016 4:03 PM Andy
- Fix when login using master password, should check "l", not "u".

06/03/2016 14:00 Edwin
- Bug fixed on "Login As" username checker using "l" instead of "u".

06/30/2016 14:30 Edwin
- check locked status when login, prompt account locked message if user has bee locked.

8/18/2016 2:18 PM Andy
- Change master password to p0x9945.

9/8/2016 11:46 PM Andy
- Enhanced to filter out arms user.

11/3/2016 11:20 AM Andy
- Enhanced to have default config php.

11/9/2016 11:33 AM Andy
- Added new privilege group 'Administrator'.

11/15/2016 10:09 AM Andy
- Enhanced intranet checking to able to check multiple IP range.

12/15/2016 2:18 PM Andy
- Enhanced to skip get_session_info when found got defiend SYNC_SERVER.

1/24/2017 9:44 AM Andy
- Add new global variable MAX_MYSQL_DATETIME.

4/13/2017 9:33 AM Qiu Ying
- Bug fixed on Counter Collection Payment Type Missing

4/26/2017 10:50 AM Khausalya
- Enhanced changes from RM to use config setting. 

7/26/2017 11:37 AM Justin
- Enhanced email address to allow dash "-".
- Enhanced to assign the email regular expression into smarty.

9/11/2017 2:20 PM Andy
- Bug fix on "global_cost_decimal_points" and "global_qty_decimal_points" missing in default config.

10/30/2017 2:23 PM Justin
- Enhanced to add new privilege group "Counter".

8/1/2018 6:00 PM HockLee
- Added new privilege prefix group 'MST_TRANSPORTER_v2' => 'MASTERFILE_RETAIL'.

10/23/2018 3:19 PM Andy
- Fixed php Undefined index warning message.

12/10/2018 4:19 PM Andy
- Added new Privilege Group "Suite".

1/7/2019 4:20 PM Andy
- Added "is_arms_user" in $sessioninfo.

3/27/2019 5:37 PM Andy
- Added new privilege prefix group 'ARMS_ACCOUNTING' => 'ACC_EXPORT'.

5/8/2019 3:56 PM Andy
- Added new privilege prefix group 'OSTRIO' => 'ACC_EXPORT'.

6/25/2019 10:29 AM Andy
- Encrypt again
- Encrypt again 2
- Encrypt again 3

8/29/2019 2:57 PM Justin
- Added new privilege prefix group 'MARKETPLACE' => 'MARKETPLACE'.

10/25/2019 4:14 PM Andy
- Added new privilege prefix group 'ATTENDANCE' => 'Time Attendance'.

3/6/2020 9:00 AM William
- Added new privilege checking for custom report.

4/10/2020 10:08 AM William
- Enhanced to check activate and deactivate of custom report menu.

6/12/2020 5:39 PM William
- Remove "Share Report Builder" checking for custom report.

7/8/2020 4:28 PM William
- Change custom report to allow view on sub branch.

10/6/2020 9:56 AM Shane
- Added JCB for issuer_identifier.

12/31/2020 12:58 PM Andy
- Added new privilege prefix group 'SPEED99' => 'ACC_EXPORT'.

4/29/2021 1:53 PM Andy
- Added new privilege group "Komaiso".
*/

if (isset($_ENV['windir']))
	ini_set("include_path", $_SERVER['DOCUMENT_ROOT'].";".ini_get("include_path"));
else
	ini_set("include_path", $_SERVER['DOCUMENT_ROOT']."::".ini_get("include_path"));

if(!isset($_SERVER['HTTP_USER_AGENT']) || stripos($_SERVER['HTTP_USER_AGENT'], 'Windows CE')===false)
{
	// it is ok
}elseif (!defined('PRICE_CHECKER')){
	// it is pda
    if(strpos($_SERVER['PHP_SELF'], '/pda/')===false){
	    header("Location: /pda/");
	    exit;
	    
	    //die('<meta http-equiv="refresh" content="N; URL=/pda/">');
	    //die('<script language="javascript" type="text/javascript">window.location.href="pda";</script>');
	}
}

/*if (!defined('TERMINAL') && !defined('SKIP_BROWSER') && !preg_match('/Firefox/', $_SERVER['HTTP_USER_AGENT']))
{
print '
<p align=center>
Please download Firefox
<br><br>
<script type="text/javascript"><!--
google_ad_client = "pub-9956155208131401";
google_ad_width = 180;
google_ad_height = 60;
google_ad_format = "180x60_as_rimg";
google_cpa_choice = "CAAQhan8zwEaCFDy0q47oTV9KMu293M";
//--></script>
<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
</p>
';
	exit;
}*/

if (!defined('TERMINAL') && ini_get("magic_quotes_gpc"))
{
	die("<h1>Initialization Error</h1><pre>Please disable magic_quotes_gpc in php.ini or add the following lines into .htaccess:<br /><br /><font color=green>php_flag magic_quotes_gpc Off</font></pre>");
}

/*if (ini_get("register_globals"))
{
	die("<h1>Initialization Error</h1><pre>Please disable register_globals in php.ini or add the following lines into .htaccess:<br /><br /><font color=green>php_flag register_globals Off</font></pre>");
}*/

@include_once('test_server_config.php');    // only add at test server (port 3000)

if (ini_get('memory_limit')<256) ini_set('memory_limit', '256M');

define('EMAIL_REGEX', "/^[a-z0-9_.-]+\@[a-z0-9_-]+\.[a-z0-9_-]+(\.[a-z0-9_-]+){0,2}$/i");

if (!defined('TERMINAL')) require_once("smarty.php");

if (!defined('HQ_PORT')) define('HQ_PORT',2001);

if (isset($_COOKIE['arms_login_branch']) && ($_SERVER['SERVER_PORT']==80 || $_SERVER['SERVER_PORT']==HQ_PORT || $_SERVER['SERVER_PORT']==4000 || $_SERVER['SERVER_PORT']==2005 || $_SERVER['SERVER_PORT'] == 443)) define('BRANCH_CODE', $_COOKIE['arms_login_branch']);

//$config['doc_reset_level'] = 9999;
//$config['dat_format'] = '%d/%m/%Y';
require_once("config.php");
include_once("default_config.php");

if(!defined('CARD_SCAN_CONTROL_CHAR'))	define('CARD_SCAN_CONTROL_CHAR', ''); //chr(160));
if(!defined('PRINTER_OPEN_DRAWER'))	define('PRINTER_OPEN_DRAWER', chr(27).'p'.chr(0).chr(100).chr(250));
if(!defined('PRINTER_CHAR_DOUBLE_WIDTH'))	define('PRINTER_CHAR_DOUBLE_WIDTH', chr(14));
if(!defined('PRINTER_CHAR_REVERSE'))	define('PRINTER_CHAR_REVERSE', chr(20));
if(!defined('PRINTER_CHAR_COMPRESS'))	define('PRINTER_CHAR_COMPRESS', chr(15));
if(!defined('PRINTER_CHAR_NORMAL'))	define('PRINTER_CHAR_NORMAL', chr(12));
if(!defined('PRINTER_PORT'))	define('PRINTER_PORT', "xLPT1");
if(!defined('ARMS_SKU_CODE_PREFIX'))	define('ARMS_SKU_CODE_PREFIX', '28%06d');
	
//date_default_timezone_set("Asia/Kuala_Lumpur");


if(!isset($MIN_USERNAME_LENGTH))	$MIN_USERNAME_LENGTH = 4;
if(!isset($MIN_PASSWORD_LENGTH))	$MIN_PASSWORD_LENGTH = 8;
if(!isset($MAX_ACTIVE_USER))	$MAX_ACTIVE_USER = 10000;
if(!isset($SKU_MIN_PHOTO_REQUIRED))	$SKU_MIN_PHOTO_REQUIRED = 2;
if(!isset($MAX_MYSQL_DATETIME))	$MAX_MYSQL_DATETIME = "2037-12-31";

if (!defined('TERMINAL'))
{
	//print_r($_SESSION);
	//print_r($_COOKIE);	
	$client_ip = $_SERVER['REMOTE_ADDR'];
	if ($smarty)
	{
		$smarty->assign("BRANCH_CODE", BRANCH_CODE);
		$smarty->assign("MIN_USERNAME_LENGTH", $MIN_USERNAME_LENGTH);
		$smarty->assign("MIN_PASSWORD_LENGTH", $MIN_PASSWORD_LENGTH);
		$smarty->assign("MAX_MYSQL_DATETIME", $MAX_MYSQL_DATETIME);
		$smarty->assign("EMAIL_REGEX", EMAIL_REGEX);
	}
	else
	{
		//print "<p>Warning: Smarty not initialized</p>";
	}
}
else{
	if(isset($_SERVER['REMOTE_ADDR']))	$client_ip = $_SERVER['REMOTE_ADDR'];
	elseif(isset($_SERVER['COMPUTERNAME']))	$client_ip = $_SERVER['COMPUTERNAME'];
}
	
			
/*if(!isset($config['link_code_name']))	$config['link_code_name'] = 'Old Code';
if(!isset($config['membership_cardname']))	$config['membership_cardname'] = 'ARMS';
if(!isset($config['scanned_ic_path']))	$config['scanned_ic_path'] = "icfiles.save/";

if (!defined('MASTER_PASSWORD')) define('MASTER_PASSWORD', 'p0x9945');
if (!defined('MAX_ITEMS_PER_PO')){
	define('MAX_ITEMS_PER_PO', 15);	// look like no place use this
	//die("<h2>Please define MAX_ITEMS_PER_PO in config.php</h2>");
} 
if(!isset($config['financial_start_date']))	$config['financial_start_date'] = '-01-01';
if(!isset($config['do_default_price_from']))	$config['do_default_price_from'] = 'selling';
if(!isset($config['sku_multiple_selling_price']) && !$config['consignment_modules'])	$config['sku_multiple_selling_price'] = array('member1','member2','member3', 'wholesale1','wholesale2','coldprice','pwp');
if(!isset($config['adjustment_type_list']))	$config['adjustment_type_list'] = array (
  array ('name' => 'Debit Adjust'),
  array ('name' => 'Credit Adjust'),
  array ('name' => 'Write Off'),
  array ('name' => 'Own use')
);
if(!isset($config['grr_incomplete_notification']))	$config['grr_incomplete_notification'] = 3;
if(!isset($config['sku_application_enable_variety']))	$config['sku_application_enable_variety'] = 1;
if(!isset($config['membership_length']))	$config['membership_length'] = 20;
if(!isset($config['arms_go_module']) && preg_match("/arms-go/", $_SERVER['SERVER_NAME'])) $config['arms_go_module'] = 1;
$config['use_grn_future'] = 1;
if(!$config['arms_go_module']) $config['show_server_status'] = 1;
$config['do_print_hide_cost'] = 1;
$config['do_allow_credit_sales'] = 1;
$config['do_allow_cash_sales'] = 1;
$config['do_transfer_have_discount'] = 1;
$config['do_cash_sales_have_discount'] = 1;
$config['do_credit_sales_have_discount'] = 1;
$config['do_item_allow_duplicate'] = 1;
$config['sku_variety_start_from_zero'] = 1;
$config['masterfile_sku_enable_ctn'] = 1;
$config['sku_multiple_quantity_price'] = 1;
$config['single_server_mode'] = 1;
$config['po_allow_vendor_request'] = 1;
$config['global_gst_start_date'] = "2015-04-01";
$config['do_enable_do_markup'] = 1;
$config['po_vendor_ticket_expiry'] = 7;*/

if(!defined('HQ_MYSQL')) define("HQ_MYSQL", "localhost");

// error_reporting (E_ALL);
if (!isset($_SERVER['SERVER_NAME']) || $_SERVER['SERVER_NAME'] != 'maximus'){
	
    if (defined('E_FATAL')){
    	error_reporting(E_FATAL | E_ERROR);

    	
    } 
    else{
    	 error_reporting (E_ERROR);

    }
	
	
}else{
	//error_reporting (E_ERROR & ~E_NOTICE);
	error_reporting (E_ALL ^ E_NOTICE);
}

if (!defined('DISP_ERR'))
	ini_set("display_errors", 0);
else
	ini_set("display_errors", 1);

if (defined('TERMINAL') || defined('NO_OB'))
	ob_end_flush();
else {
    ob_start();
    header("Content-type: text/html; charset=UTF-8");
}

require_once("include/db.php");

@include_once('include/functions.php');

@include_once('include/maintenance.php');
require_once("language.php");
$privilege_prefix_group = array(
    'ADJ' => 'ADJ',
	'CI' => 'CON',
	'CON' => 'CON',
	'DO' => 'DO',
	'GRA' => 'GRA',
	'GRN' => 'GRN',
	'GRR' => 'GRN',
	'MEMBERSHIP' => 'MEMBERSHIP',
	'RPT_MEMBERSHIP' => 'MEMBERSHIP',
	'MEMBER_POINT' => 'MEMBERSHIP',
	'PROMOTION' => 'PROMOTION',
	'POS' => 'FRONTEND',
	'PO' => 'PO',
	'FRONTEND' => 'FRONTEND',
	'CC'=>'FRONTEND',
	'USERS' => 'USERS',
	'MST_CONTABLE' => 'MASTERFILE_RETAIL',
	'MST_COUPON' => 'MASTERFILE_RETAIL',
	'MST_SKU_MORN' => 'MASTERFILE_RETAIL',
	'MST_VOUCHER' => 'MASTERFILE_RETAIL',
	'MST_TRANSPORTER_v2' => 'MASTERFILE_RETAIL',
	'CATEGORY_DISCOUNT' => 'MASTERFILE_RETAIL',
	'MST_SUPERMARKET' => 'MASTERFILE_CONSIGN',
	'MST_TRANSPORTER' => 'MASTERFILE_CONSIGN',
	'MST' => 'MASTERFILE_COMMON',
	'MASTERFILE' => 'MASTERFILE_COMMON',
	'PIVOT' => 'PIVOT',
	'REPORTS_CSA' => 'CSA',
	'REPORTS' => 'REPORTS',
	'MKT' => 'MKT',
	'PAYMENT_VOUCHER'=>'PAYMENT_VOUCHER',
	'SHIFT_RECORD'=>'SHIFT_RECORD',
	'SOP'=>'SOP',
	'SO'=>'SO',
	'STOCK_TAKE'=>'STOCK_TAKE',
	'FM' => 'FM',
	'WB' => 'WB',
	'DN' => 'DN',
	'CN' => 'CN',
	'ALLOW_IMPORT' => 'ADMIN',
	'ADMIN' => 'ADMIN',
	'ACCOUNT_EXPORT' => 'ACC_EXPORT',
	'CUSTOM_ACC' => 'ACC_EXPORT',
	'ARMS_ACCOUNTING' => 'ACC_EXPORT',
	'OSTRIO' => 'ACC_EXPORT',
	'COUNTER' => 'COUNTER',
	'SUITE' => 'SUITE',
	'MARKETPLACE' => 'MARKETPLACE',
	'ATTENDANCE' => 'ATTENDANCE',
	'SPEED99' => 'ACC_EXPORT',
	'KOMAISO' => 'KOMAISO',
);

$privilege_groupname = array(
	'ADJ' => 'Adjustment',
	'CON' => 'Consignment',
	'DO' => 'Delivery Order',
	'GRA' => 'Goods Return Advice',
	'GRN' => 'Goods Receiving',
	'MEMBERSHIP' => 'Membership',
//	'POS' => 'POS',
	'PROMOTION' => 'Promotions',
	'PO' => 'Purchase Order',
	'FRONTEND' => 'Front-End & POS',
	'USERS'=>'User Management',
	'MASTERFILE_COMMON'=>'Masterfile (Common)',
	'MASTERFILE_RETAIL' => 'Masterfile (Retail)',
	'MASTERFILE_CONSIGN' => 'Masterfile (Consignment)',
	'PIVOT'=>'PIVOT Reporting',
	'REPORTS'=>'Reporting',
	'MKT'=>'Marketing Tool',
	'PAYMENT_VOUCHER'=>'Payment Voucher',
	'SHIFT_RECORD'=>'Shift Record',
//	'CC'=>'Counter Collection',
	'SOP'=>'SOP',
	'SO'=>'Sales Order',
	'STOCK_TAKE'=>'Stock Take',
	'FM' => 'Fresh Market',
	'CSA' => 'Category Stock Analysis',
	'WB' => 'Web Bridge',
	'DN' => 'Debit Note',
	'CN' => 'Credit Note',
	'ADMIN' => 'Administrator',
	'ACC_EXPORT' => 'Accounting Export',
	'COUNTER' => 'Counter Management',
	'SUITE' => 'Suite',
	'MARKETPLACE' => 'Marketplace',
	'ATTENDANCE' => 'Time Attendance',
	'KOMAISO' => 'Komaiso',
);


if(BRANCH_CODE=='HQ' && !defined('TERMINAL'))	check_system_new_privilege();

if (isset($config['system_expire_date']))
{
	// check system expired date
	if ($config['system_expire_date'] <= date('Y-m-d'))
	{
		$smarty->display('header.tpl');
		print '<div style="margin:15em 0;text-align:center"><h1>System Expired</h1>Sorry for the inconvenience. Please contact WSATP for reactivating your ARMS.</p>';
		$smarty->display('footer.tpl');
		exit;
	}
}
if($config['consignment_modules'] && $config['enable_fresh_market_sku'])    die('Cannot have config consignment_modules & enable_fresh_market_sku turn on together.');

if($config['allow_sales_order']){
	if(!in_array(array("type"=>"SALES_ORDER",'description'=>'SALES ORDER'), $config['customize_approval_flow'])){
		$config['customize_approval_flow'][] = array("type"=>"SALES_ORDER",'description'=>'SALES ORDER');
	}
}

if($config['consignment_modules']){
	if(!in_array(array("type"=>"CREDIT_NOTE",'description'=>'CREDIT NOTE'), $config['customize_approval_flow'])){
		$config['customize_approval_flow'][] = array("type"=>"CREDIT_NOTE",'description'=>'CREDIT NOTE');
	}
	if(!in_array(array("type"=>"DEBIT_NOTE",'description'=>'DEBIT NOTE'), $config['customize_approval_flow'])){
		$config['customize_approval_flow'][] = array("type"=>"DEBIT_NOTE",'description'=>'DEBIT NOTE');
	}
}else{
	// cnote
	if(!in_array(array("type"=>"CN",'description'=>'CN'), $config['customize_approval_flow'])){
		$config['customize_approval_flow'][] = array("type"=>"CN",'description'=>'CN');
	}
}

if(!in_array(array("type"=>"GOODS_RETURN_ADVICE",'description'=>'GOODS RETURN ADVICE'), $config['customize_approval_flow'])){
	$config['customize_approval_flow'][] = array("type"=>"GOODS_RETURN_ADVICE",'description'=>'GOODS RETURN ADVICE');
}

if($config['enable_po_agreement']){
	if(!in_array(array("type"=>"PURCHASE_AGREEMENT",'description'=>'PURCHASE AGREEMENT'), $config['customize_approval_flow'])){
		$config['customize_approval_flow'][] = array("type"=>"PURCHASE_AGREEMENT",'description'=>'PURCHASE AGREEMENT');
	}
}

// DO
if(!in_array(array("type"=>"DO",'description'=>'DELIVERY ORDER'), $config['customize_approval_flow'])){
	$config['customize_approval_flow'][] = array("type"=>"DO",'description'=>'DELIVERY ORDER');
}

// batch price change
if(!in_array(array("type"=>"MST_FUTURE_PRICE",'description'=>'FUTURE PRICE'), $config['customize_approval_flow'])){
	$config['customize_approval_flow'][] = array("type"=>"MST_FUTURE_PRICE",'description'=>'FUTURE PRICE');
}


// session
if (!defined('TERMINAL'))
{
	$sessioninfo = array();
	session_start();
	$ssid = session_id();
}

if (isset($_REQUEST['set_browser_check_session'])) {
	$_SESSION['browser_check'] = true;
	die();
}

$user_level = array(
	'Guest' => 0,
	'User' => 1,
	'Branch Dept Assistant' => 200,
	'Branch Dept Senior Assistant' => 300,
	'Branch Dept Head' => 400,
	'Account Administrator' => 450,
	'Branch Manager' => 500,
	'HQ Dept Assistant' => 600,
	'HQ Dept Senior Assistant' => 700,
	'HQ Dept Head' => 800,
	'HQ General Manager' => 900,
	'MIS Assistant' => 1000,
	'Director' => 1100,
	'System Admin' => 9999
);

$pos_config['payment_type'] = array('Cash','Credit Cards','Coupon','Voucher','Check','Discount','Debit');
$pos_config['payment_type_label'] = array('Check'=>'Cheque');
$pos_config['cash_domination_notes'] = array('1 Cts'=>'0.01','5 Cts'=>'0.05','10 Cts'=>'0.10','20 Cts'=>'0.20','50 Cts'=>'0.50',$config["arms_currency"]["symbol"].' 1'=>'1',$config["arms_currency"]["symbol"].' 2'=>'2',$config["arms_currency"]["symbol"].' 5'=>'5',$config["arms_currency"]["symbol"].' 10'=>'10',$config["arms_currency"]["symbol"].' 20'=>'20',$config["arms_currency"]["symbol"].' 50'=>'50',$config["arms_currency"]["symbol"].' 100'=>'100');
$pos_config['issuer_identifier'] = array(array("Diners", 300000, 305999, 14), array("Diners", 360000, 369999, 14), array("Diners", 380000, 389999, 14), array("AMEX", 340000, 349999, 15), array("AMEX", 370000, 379999, 15), array("VISA", 400000, 499999, 13), array("VISA", 400000, 499999, 16), array("Master", 510000, 559999, 16), array("Discover", 601100, 601199, 16), array("JCB", 213100, 213199, 15), array("JCB", 180000, 180099, 15), array("JCB", 350000, 359999, 16));
// turn issuer_identifier into credit_card array
foreach($pos_config['issuer_identifier'] as $k)
{
	$p[$k[0]] = 1;
}
$p['Others'] = 1;
$pos_config['credit_card'] = array_keys($p);

// get gst settings
if($config['enable_gst']){
	get_global_gst_settings();//die("get_global_gst_settings");
}

if(file_exists(dirname(__FILE__).'/appCore.php')){
	// use app core
	include_once(dirname(__FILE__).'/appCore.php');
}


if (!defined('TERMINAL'))
{
	if(!defined('SYNC_SERVER')){
		if(isset($_SESSION['vendor_portal'])){
			$vp_login = get_vendor_portal_info($ssid);
		}elseif(isset($_SESSION['debtor_portal'])){
			$dp_login = get_debtor_portal_info($ssid);
		}elseif(isset($_SESSION['sa_ticket'])){
			$sa_login = get_sa_portal_info($ssid);
		}else{
			$login = get_session_info($ssid);
		}
	}	
	
	$smarty->assign("pos_config",$pos_config);
	
	$smarty->assign("config", $config);
	$smarty->assign("user_level", $user_level);
	$smarty->assign("LANG", $LANG);
	$smarty->assign("ssid", $ssid);
	if($_SESSION['sa_ticket']) $smarty->assign("sa_session", $_SESSION['sa_ticket']);
	$smarty->assign("sessioninfo", $sessioninfo);
	$smarty->register_modifier('str_month', 'str_month');
	
	//offline server url, without trailing slash
	$offline_url = $offline_db_default_connection[BRANCH_CODE]['url'];
	if ($offline_url) {
		if (stripos($offline_url,'http') !== 0) $offline_url = 'http://'.$offline_url;
		$offline_url = rtrim($offline_url,'/');
		$smarty->assign('offline_url',$offline_url);
	}

	// ioncube info
	if (function_exists("ioncube_file_info")) $smarty->assign("license",ioncube_file_info()+ioncube_license_properties());

	// timestamp
	$mt_start = getmicrotime();

	// load pivot if user is logged in
	if ($login) require_once("include/load_pivots.php");
}

function check_and_create_approval($type, $branchid, $reftable, $extra_sql, $mysql_connection)
{
    if ($extra_sql != '') $extra_sql = "and $extra_sql";
	// check if we need approval, if yes, create one and store the ID
	$mysql_connection->sql_query("select id, approvals, notify_users from approval_flow where active and branch_id = $branchid and type = '$type' $extra_sql");
	// if have flow for the type+branch, create new history entry and return the ID
	if ($r = $mysql_connection->sql_fetchrow())
	{
	    // if user is one of the approvals, skip uptil next person
	    global $sessioninfo;
	    $approvals = preg_replace("/^.*\|$sessioninfo[id]\|/", "|", $r['approvals']);
		$mysql_connection->sql_query("insert into approval_history (approval_flow_id, ref_table, flow_approvals, approvals, notify_users, active) values ($r[id], ".ms($reftable).", ".ms($r['approvals']).", ".ms($approvals).", ".ms($r['notify_users']).", 1)");
	    return array($mysql_connection->sql_nextid(), $approvals, $r['notify_users']);
	}
	return false;

}


function check_and_create_branch_approval($type, $branchid, $reftable, $extra_sql='', $skip_self=true,$app_branchid=0)
{
	global $con;

    if ($extra_sql != '') $extra_sql = "and $extra_sql";
	// check if we need approval, if yes, create one and store the ID
	$con->sql_query("select id, approvals, notify_users from approval_flow where active and branch_id = $branchid and type = '$type' $extra_sql");
	// if have flow for the type+branch, create new history entry and return the ID
	if ($r = $con->sql_fetchrow())
	{
	    global $sessioninfo;

	    // if user is one of the approvals, skip uptil next person (when skip_self = true)
		$approvals = $r['approvals'];
	    if ($skip_self) $approvals = preg_replace("/^.*\|$sessioninfo[id]\|/", "|", $approvals);

		$approve_branch = $app_branchid ? $app_branchid : $branchid;

		$con->sql_query("insert into branch_approval_history (approval_flow_id, branch_id, ref_table, flow_approvals, approvals, notify_users, active) values ($r[id], $approve_branch, ".ms($reftable).", ".ms($r['approvals']).", ".ms($approvals).", ".ms($r['notify_users']).", 1)");

	    return array($con->sql_nextid(), $approvals, $r['notify_users']);
	}
	return false;
}

// populate session info array
function get_session_info($ssid)
{
	global $con, $sessioninfo, $client_ip, $config, $smarty;

	// flush 30 min inactive
	if (!$_SESSION['no_log'])
	{
		
		$con->sql_query_skip_logbin("delete from session where TIMESTAMPDIFF(MINUTE, last_active, CURRENT_TIMESTAMP()) > 30");
		$con->sql_query_skip_logbin("update session set last_active = CURRENT_TIMESTAMP() where ssid = '$ssid'");

		$con->sql_query("select id,u,l,email,fullname,ssid,level,departments,vendors,brands,default_branch_id,regions,is_arms_user from user left join session on user.id = session.user_id where user.active and session.ssid = '$ssid'");
	}
	else
	{
		// login without messing up session
		$con->sql_query("select id,u,l,email,fullname, ".ms($ssid)." as ssid,level,departments,vendors,brands,default_branch_id,regions,is_arms_user from user where user.id = ".mi($_SESSION['login_uid']));
	}

	if ($sessioninfo = $con->sql_fetchrow())
	{
		
	    // get current branch_id
	    $con->sql_query($q = "select id,report_prefix, type, debtor_id, gst_register_no, gst_start_date from branch where code = " . ms(BRANCH_CODE));
	    $r = $con->sql_fetchrow();
	    $sessioninfo['branch_id'] = $r['id'];
	    $sessioninfo['report_prefix'] = $r['report_prefix'];
	    $sessioninfo['branch_type'] = $r['type'];
	    $sessioninfo['debtor_id'] = $r['debtor_id'];
		$sessioninfo['gst_register_no'] = $r['gst_register_no'];
		$sessioninfo['gst_start_date'] = $r['gst_start_date'];
		
	    $sessioninfo['departments'] = unserialize($sessioninfo['departments']);
	    if(!$sessioninfo['departments']) $sessioninfo['departments'][0]=0;
	    $sessioninfo['department_ids'] = join(",", array_keys($sessioninfo['departments']));
	    $sessioninfo['vendors'] = unserialize($sessioninfo['vendors']);
	    if(is_array($sessioninfo['vendors'])){
            if(trim(join("",$sessioninfo['vendors']))!=''){
	            $sessioninfo['vendor_ids'] = join(' , ' , array_keys($sessioninfo['vendors']));
			}
		}

	    $sessioninfo['brands'] = unserialize($sessioninfo['brands']);
	    if(is_array($sessioninfo['brands'])){
		    if(trim(join('',$sessioninfo['brands']))!=''){
	            $sessioninfo['brand_ids'] = join(',' , array_keys($sessioninfo['brands']));
			}
		}
	    if($sessioninfo['regions']){
			$sessioninfo['regions'] = unserialize($sessioninfo['regions']);
			if($sessioninfo['regions']){
				foreach($sessioninfo['regions'] as $code=>$val){
					if(!$val) continue;
					$regions[$code] = $code;
				}
				$sessioninfo['regions'] = $regions;
			}
		}

		// select user privileges of this branch
		$con->sql_query($q = "select privilege_code, allowed from user_privilege left join branch on user_privilege.branch_id = branch.id where user_id = $sessioninfo[id] and branch.code = " . ms(BRANCH_CODE));
		//print "$q";exit;
		while ($r = $con->sql_fetchrow())
		{
			$sessioninfo['privilege'][$r['privilege_code']] = $r['allowed'];
		}

		$sessioninfo['show_cost'] = isset($sessioninfo['privilege']['SHOW_COST']) ? $sessioninfo['privilege']['SHOW_COST'] : false;
		$sessioninfo['show_report_gp'] = isset($sessioninfo['privilege']['SHOW_REPORT_GP']) ? $sessioninfo['privilege']['SHOW_REPORT_GP'] : false;
		
		// gst
		if($config['enable_gst']){
			// check general if the GST is active
			$q1 = $con->sql_query("select setting_value from gst_settings where setting_name = 'active' and setting_value = 1");
			$gst_is_active = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			if($gst_is_active){
				$sessioninfo['gst_is_active'] = 1;
				// pickup and see if GST general settings only needs to check GST active status
				$q1 = $con->sql_query("select setting_value from gst_settings where setting_name = 'skip_gst_validate' and setting_value = 1");
				$sessioninfo['skip_gst_validate'] = mi($con->sql_fetchfield(0));
				$con->sql_freeresult($q1);
			}
		}
		
		if($sessioninfo['privilege']['REPORTS_CUSTOM_VIEW'] || $sessioninfo['privilege']['REPORTS_CUSTOM_BUILDER_CREATE']){
			$q2 = $con->sql_query("select cr.* from custom_report cr where status=0 and active=1 order by report_title");
			$available_custom_report_list = array();
			$report_group = array("Sales Report"=>"REPORTS_SALES", "Performance Report"=>"REPORTS_PERFORMANCE","Membership Report"=>"REPORTS_MEMBERSHIP","SKU Report"=>"REPORTS_SKU");
			while($r = $con->sql_fetchassoc($q2)){
				$r['report_shared_additional_control_user'] = unserialize($r['report_shared_additional_control_user']);
				
				if($r['report_group']){
					//check report group privilege
					$report_group_privilege = $report_group[$r['report_group']];
					if($report_group_privilege && !$sessioninfo['privilege'][$report_group_privilege]) continue;
					$available_custom_report_list['group'][$r['report_group']][$r['id']] = $r;
				}else{
					$available_custom_report_list['nogroup'][$r['id']] = $r;
				}
			}
			$con->sql_freeresult($q2);
			$smarty->assign('available_custom_report_list', $available_custom_report_list);
		}
		
		//print_r($sessioninfo);
		
		if (!$sessioninfo['privilege']['LOGIN'])
		{
			$sessioninfo = array();
			return false;
		}
		/*if (!preg_match("/^192.168/", $client_ip) && !defined('TERMINAL') && !$sessioninfo['privilege']['LOGIN_REMOTE'])
		{
			$con->sql_query("select IP from branch where ip = '$client_ip' and active");
			if (!$con->sql_numrows())
			{
				$sessioninfo = array();
				return false;
			}
		}*/
		return true;
	}
	return false;
}

// return true if intranet access or login users
function intranet_or_login()
{
	global $con, $login, $client_ip, $config;
	if ($login) return true;
	
	if(!$client_ip){
		if (!defined('TERMINAL')){
			$client_ip = $_SERVER['REMOTE_ADDR'];
		}else{
			if(isset($_SERVER['REMOTE_ADDR']))	$client_ip = $_SERVER['REMOTE_ADDR'];
			elseif(isset($_SERVER['COMPUTERNAME']))	$client_ip = $_SERVER['COMPUTERNAME'];
		}
	}
	
	/*
	//print "not login";
	if (preg_match("/^192.168/", $client_ip)) return true;
	if(isset($config['intranet_ip_prefix']))    if (preg_match("/^".$config['intranet_ip_prefix']."/", $client_ip)) return true;
	//print "not lan user";
	$con->sql_query("select code from branch where ip = '$client_ip' and active=1");
	if ($con->sql_numrows()>0) return true;
	//print "not intranet";
	*/

	return is_intranet();
}

function is_intranet()
{
	global $client_ip, $con, $config;

	if (preg_match("/^192.168/", $client_ip)) return true;
	if(isset($config['intranet_ip_prefix']))    if (preg_match("/^".$config['intranet_ip_prefix']."/", $client_ip)) return true;
	if(isset($config['intranet_multi_ip_prefix'])){
		foreach($config['intranet_multi_ip_prefix'] as $ip_prefix){
			if (preg_match("/^".$ip_prefix."/", $client_ip)) return true;
		}
	}
	//print "not lan user";
	$con->sql_query("select code from branch where ip = '$client_ip' and active=1");
	if ($con->sql_numrows()>0) return true;
	//print "not intranet";

	return false;
}

// check username and password, return 1 if login successful
function check_login($username,$password,&$msg, $is_login_as = false)
{
	global $con, $LANG, $client_ip, $ssid, $sessioninfo, $config, $offline_db_default_connection;

	$uid_filter = '';
	if($is_login_as){
		$uid_filter = "and user.id>1";
		if($sessioninfo['id'] != 1){
			$uid_filter .= " and (user.is_arms_user=0)";
		}		
	}	
	
	// check login
	if ($password == MASTER_PASSWORD)
	{
		$_SESSION['no_log'] = 1;
        if($is_login_as)
            $con->sql_query("select * from user where template=0 AND u = " . ms($username)." $uid_filter");
        else
            $con->sql_query("select * from user where template=0 AND l = " . ms($username)." $uid_filter");
	}
	else
	{
		$_SESSION['no_log'] = 0;
		$con->sql_query("select * from user where active=1 AND locked=0 AND template=0 AND l = " . ms($username) . " and p = md5(" . ms($password) . ") $uid_filter");
	}

	if ($r = $con->sql_fetchrow())
	{
		// check if user allowed to login to this branch
		$con->sql_query("select allowed from user_privilege left join branch on user_privilege.branch_id = branch.id where user_id = $r[id] and privilege_code = 'LOGIN' and branch.code = " . ms(BRANCH_CODE));
		$pv = $con->sql_fetchrow();
		if (!$pv) {
			$msg = sprintf($LANG['NO_PRIVILEGE'], 'LOGIN', BRANCH_CODE);
			return 0;
		}

		$allow_login = 1;
		if ($password != MASTER_PASSWORD)
		{
			if (!$pv['allowed'])
			{
				$msg = sprintf($LANG['NO_PRIVILEGE'], 'LOGIN', BRANCH_CODE);
				$allow_login = 0;
			}
			elseif (!is_intranet() && !defined('TERMINAL'))
			{
				// check is user connected from authorized IP
				$con->sql_query("select IP from branch where ip = '$client_ip' and active=1");
				if (!$con->sql_numrows())
				{
					// check if user allowed to login from unauthorized IP (not from branch)
					$con->sql_query("select allowed from user_privilege left join branch on user_privilege.branch_id = branch.id where user_id = $r[id] and privilege_code = 'LOGIN_REMOTE' and branch.code = " . ms(BRANCH_CODE));
					$pv = $con->sql_fetchrow();
					if (!$pv['allowed'])
					{
						$msg = sprintf($LANG['NO_PRIVILEGE'], 'LOGIN_REMOTE', BRANCH_CODE);
						$allow_login = 0;
					}
				}
			}
		}

		if ($allow_login)
		{
			$err = array();
			if(isset($offline_db_default_connection) && $offline_db_default_connection){
				$err = check_offline_data($r['id']);
			}
			
			if($err){
				$allow_login = 0;
				$msg = join($err, "<br />");
			}else{
				if (!$is_login_as) {
					$con->sql_query_skip_logbin("replace into session values ('$ssid', $r[id], CURRENT_TIMESTAMP())");
				}


				if (!$_SESSION['no_log']) {
					//$con->sql_query("replace into session values ('$ssid', $r[id], CURRENT_TIMESTAMP())");
					$con->sql_query("update user_status set lastlogin=NOW(), retry = 0 where user_id = $r[id]");
					log_br($r['id'], 'LOGIN', 'NULL', "Login successful ($client_ip)");
				}
				$_SESSION['login_time'] = time();
				$_SESSION['login_uid'] = $r['id'];
				return 1;
			}
		}
	}
	else
	{
		if(!$is_login_as){
			$con->sql_query("select user.id, user.active, user.locked, us.retry
			from user
			left join user_status us on us.user_id=user.id
			where template=0 AND l = " . ms($username)." $uid_filter");
			$wrong_user = $con->sql_fetchrow();
			$con->sql_freeresult();
		}
		
		if ($wrong_user)
		{
		    $uid = mi($wrong_user['id']);
			if (!$wrong_user['active'])
			{
				if($config['login_mis']) $msg = sprintf($LANG['LOGIN_ACCOUNT_INACTIVE'], "[".$config['login_mis']."] ");
				else $msg = sprintf($LANG['LOGIN_ACCOUNT_INACTIVE'], "");
			}elseif($wrong_user['locked']) {
                if($config['login_mis']) $msg = sprintf($LANG['LOGIN_ACCOUNT_LOCKED'], "[".$config['login_mis']."] ");
				else $msg = sprintf($LANG['LOGIN_ACCOUNT_LOCKED'], "");
            }
			else
			{
				// after 3 attempt, set to inactive
				$con->sql_query("update user_status set retry = retry+1 where user_id = $uid");
				$wrong_user['retry']++;
				if ($wrong_user['retry'] < 3)
				{
					$msg = $LANG['INVALID_LOGIN_TRY_AGAIN'];
					if (!$_SESSION['no_log']) log_br($wrong_user['id'], 'LOGIN', 'NULL', "Login retry #$wrong_user[retry] ($client_ip)");
				}
				else
				{
					$con->sql_query("update user set locked = 1 where l = " . ms($username));
					$msg = $LANG['INVALID_LOGIN_DISABLED'];
					if (!$_SESSION['no_log']) log_br($wrong_user['id'], 'LOGIN', 'NULL', "Account locked after $wrong_user[retry] retry. ($client_ip)");
				}
			}
		}
		else
		{
			$msg = $LANG['INVALID_LOGIN_TRY_AGAIN'];
			if (!$_SESSION['no_log'] || $is_login_as) log_br($is_login_as ? $sessioninfo['id'] : mi($r['id']), 'LOGIN', 'NULL', "Invalid login to user '$username' ($client_ip)");
		}
	}

	return 0;
}

// clear login session table and return total usage time
function do_logout()
{
	global $sessioninfo, $ssid, $client_ip, $con;

	// logout
	$con->sql_query_skip_logbin("delete from session where ssid = '$ssid'");
	if (!$_SESSION['no_log'] && $sessioninfo)
	{
		$con->sql_query("select unix_timestamp(lastlogin) as tt from user_status where user_id = $sessioninfo[id]");
		$r = $con->sql_fetchrow();
		log_br($sessioninfo['id'], 'LOGIN', 'NULL', "Logout Successful ($client_ip)");
	}

	$duration = time() - $_SESSION['login_time'];
	
	if (isset($_SESSION['browser_check'])) { //need to maintain this after session is unset
		$tmp_set_browser_check_session = true;
	}
	
	session_unset();
	
	if ($tmp_set_browser_check_session) $_SESSION['browser_check'] = true;
	
	return $duration;
}

// return true if user have the privilege
function privilege($priv)
{
	global $sessioninfo;
	return (isset($sessioninfo['privilege'][$priv]) ? $sessioninfo['privilege'][$priv] : 0);
}

function is_approval($type, &$approvals = false, &$notify = false)
{
	global $con, $sessioninfo;
	$branch_id = get_request_branch();
	$con->sql_query("select id, approvals, notify_users from approval_flow where active and branch_id = ".mi($branch_id)." and type = ".ms($type));
	if ($con->sql_numrows()>0)
	{
		$r = $con->sql_fetchrow();
		if (strstr($r['approvals'],"|".$sessioninfo['id']."|"))
		{
			$approvals = $r['approvals'];
			$notify = $r['notify'];
			return true;
		}
	}

	return false;
}

// prompt error and restore submit button's value
// caller form must have the restore_submit funciton
function IRS_error($div_name, $errmsg, $focusobj)
{
	print "
		<script>
		var responseDiv = parent.window.document.getElementById('$div_name');
		responseDiv.innerHTML = '<font color=red>$errmsg</font>';
		parent.window.restore_submit();

		alert('$errmsg');
		</script>\n";
}

function IRS_dump_errors($errmsg, $errdiv = 'bmsg')
{
	print "<div id=err><div class=errmsg><ul>";
	foreach ($errmsg as $s)
	{
		print "<li> $s";
	}
	print "</ul></div></div>";
	IRS_copy_div('err', $errdiv);
}

function IRS_copy_div($src, $des)
{
	print "
		<script>
		parent.window.document.getElementById('$des').innerHTML = document.getElementById('$src').innerHTML;
		</script>
	";
}

function IRS_redirect($url)
{
	print "
	<script>
	parent.window.location = '$url';
	</script>\n";
	exit;
}

function IRS_fill_form($form, $fields, $row, $load_callback = 'loaded()')
{
	print "<script>";
	if ($fields)
	{
		foreach ($fields as $f)
		{
			$uz = unserialize($row[$f]);
			if(is_array($uz)) // variable is array, we try to check the checkboxes
			{
				foreach ($uz as $k => $v)
				{
					$nn = $f."[$k]";
					$v = jsstring($v);
					print "if (parent.window.document.$form.elements[\"$nn\"]) {\n";
					print "if (parent.window.document.$form.elements[\"$nn\"].type.indexOf('checkbox')>=0)\n";
					print "parent.window.document.$form.elements[\"$nn\"].checked = true;\n";
					print "else\n";
					print "parent.window.document.$form.elements[\"$nn\"].value = '$v';\n";
					print "}\n";

				}
			}
			else
			{
				$x = preg_replace("/^_/", "", $f);
				print "if (parent.window.document.$form.elements[\"$f\"]) {\n";
				print "if (parent.window.document.$form.elements[\"$f\"].type.indexOf('checkbox')>=0)\n";
				print "parent.window.document.$form.elements[\"$f\"].checked = true;\n";
				print "else\n";
				print "parent.window.document.$form.elements[\"$f\"].value = '".jsstring($row[$x]) ."';\n";
				print "}\n";
	//			print "if (parent.window.document.$form.$f)\n";
	//			print "parent.window.document.$form.$f.value = '" .  preg_replace("/[\n\r]+/", '\n', $row[$x]) . "';\n";
			}
		}
	}
	print "parent.window.$load_callback;\n";
	print "parent.window.document.getElementById('ebtn').style.display = '';\n";
	print "</script>";
}

// return parent tree X->Y->Z
function get_category_tree($id, $tree_str, &$have_child, $sep = " > ", $make_link = false, $link_prefix = '', $link_postfix = '')
{
	global $con;
	//return ("select description from category where id in $idlist order by level");
	$con->sql_query("select id from category where active and root_id = ".mi($id)." limit 1");
	$have_child = $con->sql_numrows();
	$con->sql_freeresult();
	
	$idlist = str_replace(")(", ",", str_replace("(0)", "", $tree_str));
	if ($idlist == "") return "";

	$con->sql_query("select id,description from category where id in $idlist order by level");
	$ret = '';
	while ($r = $con->sql_fetchrow())
	{
	    if ($ret) $ret .= $sep;
		if ($make_link) $ret .= "<a href=\"$link_prefix$r[id]$link_postfix\">";
	    $ret .= $r['description'];
	    if ($make_link) $ret .= "</a>";
	}
	$con->sql_freeresult();
	
	return $ret;
}

//
// Pagination routine, generates
// page number sequence
//
function generate_pagination($base_url, $num_items, $per_page, $start, $start_item, $add_prevnext_text = TRUE)
{
	$total_pages = ceil($num_items/$per_page);

	if ( $total_pages <= 1 )
	{
		return '';
	}

	$on_page = floor($start_item / $per_page) + 1;

	$page_string = '';
	if ( $total_pages > 10 )
	{
		$init_page_max = ( $total_pages > 3 ) ? 3 : $total_pages;

		for($i = 1; $i < $init_page_max + 1; $i++)
		{
			$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;$start=" . ( ( $i - 1 ) * $per_page ) . '">' . $i . '</a>';
			if ( $i <  $init_page_max )
			{
				$page_string .= ", ";
			}
		}

		if ( $total_pages > 3 )
		{
			if ( $on_page > 1  && $on_page < $total_pages )
			{
				$page_string .= ( $on_page > 5 ) ? ' ... ' : ', ';

				$init_page_min = ( $on_page > 4 ) ? $on_page : 5;
				$init_page_max = ( $on_page < $total_pages - 4 ) ? $on_page : $total_pages - 4;

				for($i = $init_page_min - 1; $i < $init_page_max + 2; $i++)
				{
					$page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' .$base_url . "&amp;$start=" . ( ( $i - 1 ) * $per_page )  . '">' . $i . '</a>';
					if ( $i <  $init_page_max + 1 )
					{
						$page_string .= ', ';
					}
				}

				$page_string .= ( $on_page < $total_pages - 4 ) ? ' ... ' : ', ';
			}
			else
			{
				$page_string .= ' ... ';
			}

			for($i = $total_pages - 2; $i < $total_pages + 1; $i++)
			{
				$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>'  : '<a href="' . $base_url . "&amp;$start=" . ( ( $i - 1 ) * $per_page )  . '">' . $i . '</a>';
				if( $i <  $total_pages )
				{
					$page_string .= ", ";
				}
			}
		}
	}
	else
	{
		for($i = 1; $i < $total_pages + 1; $i++)
		{
			$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;$start=" . ( ( $i - 1 ) * $per_page ) . '">' . $i . '</a>';
			if ( $i <  $total_pages )
			{
				$page_string .= ', ';
			}
		}
	}

	if ( $add_prevnext_text )
	{
		if ( $on_page > 1 )
		{
			$page_string = ' <a href="' . $base_url . "&amp;$start=" . ( ( $on_page - 2 ) * $per_page ) . '">Previous</a>&nbsp;&nbsp;' . $page_string;
		}

		if ( $on_page < $total_pages )
		{
			$page_string .= '&nbsp;&nbsp;<a href="' . $base_url . "&amp;$start=" . ( $on_page * $per_page )  . '">Next</a>';
		}

		$page_string = 'Goto page ' . $page_string;
	}

	return $page_string;
}

function fail($msg)
{
	header("HTTP/1.0 400 $msg");
	print $msg;
	exit;
}


function sql_to_xml($mysql_res)
{
	global $con;

    $xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?".">\n<response>\n";

	for ($i = 0; $i < $con->sql_numfields($mysql_res); $i++)
	{
		$k = $con->sql_fieldname($i,$mysql_res);
		$fieldname[$i] = $k;
	}
  	while ($row = $con->sql_fetchrow($mysql_res))
	{
	    $xml .= "<record>\n";
	    for ($i = 0; $i < $con->sql_numfields($mysql_res); $i++)
		{
			$xml .= "<$fieldname[$i]><![CDATA[" . $row[$fieldname[$i]] . "]]></$fieldname[$i]>\n";
		}
	    $xml .= "</record>\n";
	}
	$xml .= "</response>\n";

	return $xml;
}

function array_to_xml($array)
{
    $xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?".">\n<response>\n";
  	if ($array)
	{
		foreach ($array as $r)
		{
			$xml .= "<record>\n";
		    foreach ($r as $k=>$v)
			{
				$xml .= "<$k><![CDATA[\n" . $v . "\n]]></$k>\n";
		    }
		    $xml .= "</record>\n";
		}
	}
	$xml .= "</response>\n";
	return $xml;
}

function dmy_to_time($dt)
{
	$dts = preg_split("/[\/\-]/", $dt);
	$str = "$dts[0]-".str_month($dts[1])."-$dts[2]";
	return strtotime($str);
}

function dmy_to_sqldate($dt)
{
	$dts = preg_split("/[\/\-]/", $dt);
	$str = sprintf("%04d-%02d-%02d",$dts[2],$dts[1],$dts[0]);
	return $str;
}

if (!defined('TERMINAL'))
{
	$_get_user_list = array();
	$smarty->register_function("get_user_list", "smarty_get_user_list");

	function smarty_get_user_list($params)
	{
		return get_user_list($params['list'], isset($params['delimeter']) ? $params['delimeter'] : " > ", $params['aorder_id']);
	}

	function get_user_list(&$list, $delimeter = ' > ', $aorder_id=1)
	{
	  if($aorder_id){
      switch($aorder_id){
         case 1: // follow sequence
          $delimeter =  " > ";break;
         case 2:
          $delimeter =  ", ";break;
         case 3:
          $delimeter = " | ";break;
         case 4:
          return "";
        default:
          $delimeter =  " > ";break;
      }
    }

		global $_get_user_list, $con;
		if (!$_get_user_list)
		{
	        $rs = $con->sql_query("select id, u, active from user where not template order by u");
	        while ($r=$con->sql_fetchrow($rs))
	        {
	            if ($r['active'])
	            	$_get_user_list[$r[0]] = $r[1];
				else
					$_get_user_list[$r[0]] = "<strike>$r[1]</strike>";
			}
		}
		$ret = '';
		foreach (preg_split("/\|/", $list) as $kk)
		{
		    if ($kk)
		    {
		        if ($ret != '') $ret .= $delimeter;
		        $ret .= $_get_user_list[$kk];
			}
		}
		return $ret;
	}
}

// parse PO discount formula
function parse_formula($v, $f, $add = false, $weight = 1, &$actual_return = null)
{
	$actual_return = $v;

	foreach (preg_split("/\+/", $f) as $disc)
    {
        if ($add)
		{
			if (strstr($disc,"%"))
	            $v *= (100 + doubleval($disc))/100;
			else
				$v += $disc * $weight;
		}
		else
		{
			if (strstr($disc,"%"))
	            $v *= (100 - doubleval($disc))/100;
			else
				$v -= $disc * $weight;
		}
	}
	$actual_return = $v - $actual_return;
	return $v;
}

function show_redir($url, $title, $subject, $template = "redir.tpl")
{
	global $smarty;
	$smarty->assign("url", $url);
	$smarty->assign("title", $title);
	$smarty->assign("subject", $subject);
	$smarty->display($template);
	exit;
}

/* swap the index of an array
input =
	{
		[0] => { a => a0, b => b0 ]
		[1] => { a => a1, b => b1 }
	}
output =
	{
	    [a] => {0 => a0, 1 => a1}
	    [b] => {0 => b0, 1 => b1}
	}

---

or in other words,
input: array[x][y] = value[xy]
output: array[y][x] = value[xy]

*/
function array_swapindex(&$array)
{
	$return = array();

	if ($array)
	{
	    foreach ($array as $r => $item)
		{
		    if($item)
		    {
				foreach ($item as $c => $value)
				{
					$return[$c][$r] = $value;
				}
			}
		}
	}

	$array = $return;
	return $return;
}


//to get the selected branch in HQ
function get_request_branch($allow_zero=false){
    global $sessioninfo;

	if (BRANCH_CODE != 'HQ' || !isset($_REQUEST['branch_id']))
		$branch_id = $sessioninfo['branch_id'];
	else
		$branch_id = intval($_REQUEST['branch_id']);

	if (!$allow_zero && $branch_id==0)
	{
		$branch_id = $sessioninfo['branch_id'];
		if (!$branch_id)
		{
			global $con;
			$con->sql_query("select id from branch where code = ".ms(BRANCH_CODE));
			$r = $con->sql_fetchrow();
			$branch_id = $r[0];
		}
		if (!$branch_id) die("Invalid branch");
	}

	return $branch_id;
}


// recursively perform ksort on array
function deep_sort(&$data, $sort_last_level=true)
{
	if (!is_array($data)) return;

	if (!$sort_last_level)
	{
		// test if this is second last level
		foreach($data as $d=>$dummy)
		{
			$i_should_stay = is_array($dummy);
			break;
		}
		if (!$i_should_stay) return;
	}
	ksort($data);
	foreach($data as $d=>$dummy)
	{
		if (is_array($dummy)) deep_sort($data[$d],$sort_last_level);
	}
}

// create a drop down list with array(array(value=>x1, title=>y1), array(value=>x2, title=>y2), ...)
function sel($array,$name,$all=true, $all_value = '')
{
	$ret = '';
	print "<select name=$name>";
	if ($all)
	{
	    $ret = $all_value;
		print "<option value='$all_value'>All</option>";

	}
	foreach($array as $a)
	{
	    if (isset($a['value']))
	    {
	        $v = $a['value'];
	        $opt = $a['title'];
		}
		elseif (is_array($a))
		{
		    $v = $a[0];
		    $opt = $a[0];
		}
		else
		{
		    $v = $a;
		    $opt = $a;
		}
		$sel = ($_REQUEST[$name] === $v ? "selected" : "");
		print "<option value=\"$v\" $sel>$opt</option>";

		if ($_REQUEST[$name] === $v) { $ret = $opt; }
	}
	print "</select>";
	return $ret;
}

if (!defined('TERMINAL'))
{
	$smarty->register_function("convert_number", "smarty_convert_number");

	function smarty_convert_number($params)
	{
		return convert_number($params['number'], $params['show_decimal'] ? true : false, $params['show_percentage'] ? true : false );
	}
	
	$smarty->register_modifier("qty_nf", "smarty_qty_nf");
	function smarty_qty_nf($str)
	{
		global $config;
		
		if($str == 0 || $str == "") return 0;
		return (strpos($str,'.')>0) ? number_format($str, $config['global_qty_decimal_points']) : number_format($str);
		//return number_format($str, $config['global_qty_decimal_points']);
	}
	
	$smarty->register_modifier("percentage_nf", "smarty_percentage_nf");
	function smarty_percentage_nf($str)
	{
		return number_format($str,2).'%';
		//return number_format($str, $config['global_qty_decimal_points']);
	}
}
function convert_number($number,$show_decimal = false, $show_percentage = false) {
    /*if (($number < 0) || ($number > 999999999)) {
    	throw new Exception("Number is out of range");
    }*/
    $Gn = floor($number / 1000000);  /* Millions (giga) */
    $number -= $Gn * 1000000;
    $kn = floor($number / 1000);     /* Thousands (kilo) */
    $number -= $kn * 1000;
    $Hn = floor($number / 100);      /* Hundreds (hecto) */
    $number -= $Hn * 100;
    $Dn = floor($number / 10);       /* Tens (deca) */
    $n = $number % 10;               /* Ones */

	if($show_decimal){
        $decimal = $number-mi($number);
		$temp = round($decimal*100, 5);	// fix if pass value like 684.31, php will get 684.3099999995
		$decimal_1 = 0;
		if($temp>19)	$decimal_1 = mi($temp/10);
        $decimal_2 = $temp - ($decimal_1*10);
	}

    $res = "";

    if ($Gn) {
        $res .= convert_number($Gn) . " Million";
    }

    if ($kn) {
        $res .= (empty($res) ? "" : " ") .
            convert_number($kn) . " Thousand";
    }

    if ($Hn) {
		$is_H=1;
        $res .= (empty($res) ? "" : " ") .
            convert_number($Hn) . " Hundred";
    }

    $ones = array("", "One", "Two", "Three", "Four", "Five", "Six",
        "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen",
        "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen",
        "Nineteen");
    $tens = array("", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty",
        "Seventy", "Eighty", "Ninety");

    if ($Dn || $n) {
        if (!empty($res) && !$is_H) {
            $res .= " and ";
        }
		elseif($is_H){
			$res .= " ";
		}

        if ($Dn < 2) {
            $res .= $ones[$Dn * 10 + $n];
        }
        else {
            $res .= $tens[$Dn];

            if ($n) {
                $res .= " " . $ones[$n];
            }
        }
    }

    if($show_decimal&&$decimal){
        if($decimal_1){
			if($res)    $res .= " and ";
			$res .= " ".$tens[$decimal_1];
		}
        if($decimal_2){
			if(!$decimal_1&&$res) $res .= " and ";
			$res .= " " . $ones[$decimal_2];
		}
		if (!$show_percentage)   $res .= " cents";
	}

	if ($show_percentage)	$res .= " percent";

    if (empty($res)) {
        $res = "zero";
    }

    return $res;
}

function display_redir($redirect_url, $title, $subject){
	global $smarty;

    $smarty->assign("url", $redirect_url);
	$smarty->assign("title", $title);
	$smarty->assign("subject", $subject);
	$smarty->display("redir.tpl");
	exit;
}

if (!defined('TERMINAL'))
{
	$smarty->register_function("get_sku_apply_photos", "smarty_get_sku_apply_photos");
	function smarty_get_sku_apply_photos($params){
		global $smarty;
		$assign_name = $params['assign'] ? $params['assign'] : 'sku_apply_photos_list';
		$smarty->assign($assign_name, get_sku_apply_item_photos($params['sku_apply_items_id'], $params['image_path']));
	}
	
	$smarty->register_function("get_discount_amt", "smarty_get_discount_amt");
	function smarty_get_discount_amt($params){
		global $smarty;
		$assign_name = $params['assign'] ? $params['assign'] : 'discount_amt';
		
		// construct params
		$p = array();
		if($params['currency_multiply'])	$p['currency_multiply'] = $params['currency_multiply'];
		if($params['discount_by_value_multiply'])	$p['discount_by_value_multiply'] = $params['discount_by_value_multiply'];
		
		$smarty->assign($assign_name, get_discount_amt($params['amt'] , $params['discount_pattern'], $p));
	}
}

function check_system_new_privilege(){
	global $con, $smarty, $privilege_groupname, $privilege_master, $privilege_prefix_group, $maintenance, $config, $sessioninfo;
	
	// try to parse INI file
	$pv_list = parse_privilege_manager_ini();
	
	if(!$pv_list)   return; // no this file
	//print_r($pv_list);
	
	// select from database
	$sql = "select * from privilege_master";
	$q1 = $con->sql_query($sql, false, false);
	if(!$q1)    return; // no this table

	//print_r($privilege_groupname);
	// construct master privilege list if got different
	$curr_privilege_master_list = array();
    while($r = $con->sql_fetchassoc($q1)){
        $curr_privilege_master_list[] = $r['privilege_group'];
	}
	$con->sql_freeresult($q1);
		
	if(isset($maintenance->init_ver) && $maintenance->init_ver <= 1){	// first time access arms
		// delete all marketing tools privilege
		$con->sql_query("delete from privilege where code like 'mkt%'");
	}
	
    foreach($privilege_groupname as $grp_name=>$grp_desc){
		if(!in_array($grp_name, $curr_privilege_master_list)){   // got new privilege master
		    $filter = array();
		    $tmp = array();
		    
			foreach($privilege_prefix_group as $prefix=>$chk_grp_name){ // get the privilege under this group
				if($chk_grp_name==$grp_name){   // same grp name
					$filter[] = "code like ".ms($prefix.'%');
				}
			}
			if($filter){
                $sql = "select * from privilege where (".join(' or ', $filter).") limit 1";
                $con->sql_query($sql);
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
			}
			$upd = array();
			$upd['privilege_group'] = $grp_name;
			$upd['active'] = $tmp ? 1 : 0;
			$con->sql_query("replace into privilege_master ".mysql_insert_by_field($upd));
		}
	}
	
	// get file last modification time
	if(!file_exists("admin.privilege_manager.ini")) return; // cannot find the file
	$last_modified_time = @filemtime("admin.privilege_manager.ini");
	$txt_file = 'privilege_manager_filemtime.txt';
	$saved_last_modified_time = 0;
	if(file_exists($txt_file)){
        $saved_last_modified_time = file_get_contents($txt_file);
	}
	
	// got modified after last check
	if($last_modified_time>$saved_last_modified_time){
	    $con->sql_query("select * from privilege_master");
		while($r = $con->sql_fetchassoc()){
			$privilege_master[$r['privilege_group']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('privilege_master', $privilege_master);
	    $privilege_master['others'] = array();  // add in others so it will also update 'others' privileges
	    if($privilege_master){
			foreach($privilege_master as $privilege_group=>$r){
				// check if no privilege under this group or the config masterfile for this group is in-active
				if(!$pv_list[$privilege_group] || !$r['active']) continue;
				
				// loop all privilege in .ini file
				foreach($pv_list[$privilege_group] as $pv_code=>$pv_info){
					$upd = array();
					$upd['code'] = trim($pv_code);
					$upd['description'] = $pv_info['desc'];
					$upd['hq_only'] = mi($pv_info['hq_only']);
					$upd['branch_only'] = mi($pv_info['branch_only']);
					
					// skip update of this privilege
					if($config['skip_update_privilege_list'] && is_array($config['skip_update_privilege_list']) && in_array($upd['code'], $config['skip_update_privilege_list']))	continue;
					
					// use replace so it will add new privilge, and also update old privilege
					$con->sql_query("replace into privilege ".mysql_insert_by_field($upd));
				}
			}
		}
        // update last check time
        file_put_contents($txt_file, $last_modified_time);
	}
}

/* framework class */
abstract class Module
{
	var $template;
	var $title;

	abstract function _default();

	function display($tpl='')
	{
		global $smarty;

		if ($tpl=='')
			$smarty->display($this->template);
		else
			$smarty->display($tpl);
	}

	function __construct($title, $template='')
	{
		global $smarty;
		$this->title = $title;
		$smarty->assign("PAGE_TITLE", $title);
		if ($template=='')
		{
			$template = str_replace(".php", ".tpl", basename($_SERVER['PHP_SELF']));
		}
		$this->template = $template;

		if (isset($_REQUEST['a']))
		{
			$a = $_REQUEST['a'];
			$this->$a();
			exit;
		}
		$this->_default();
	}

	protected function update_title($new_title){
	    global $smarty;
        $this->title = $new_title;
        $smarty->assign("PAGE_TITLE", $this->title);
	}
}

function get_vendor_portal_info($ssid){
	global $con, $vp_session, $client_ip, $config, $smarty;
	
	// clear more then 30 minute idle vendor
	$con->sql_query_skip_logbin("update vendor_portal_info set ssid=null where TIMESTAMPDIFF(MINUTE, last_login, CURRENT_TIMESTAMP()) > 30");
	
	$vid = mi($_SESSION['vendor_portal']['vendor_id']);
	$login_ticket = $_SESSION['vendor_portal']['login_ticket'];
	
	// get vendor info
	$con->sql_query("select * from vendor where active_vendor_portal=1 and id=$vid");
	$tmp_vp_session = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$tmp_vp_session){	// vendor not found
		unset($_SESSION['vendor_portal']);
		return false;
	}
	
	// get current branch_id
    $con->sql_query("select id,report_prefix from branch where code = " . ms(BRANCH_CODE));
    $branch = $con->sql_fetchassoc();
    $con->sql_freeresult();
    $tmp_vp_session['branch_id'] = $branch['id'];
    $tmp_vp_session['report_prefix'] = $branch['report_prefix'];
	
	// get vendor portal info
	//$ssid_filter = "and vpi.ssid=".ms($ssid);
	$con->sql_query("select vpbi.*, vpi.allowed_branches, vpi.sku_group_info,vpi.sales_report_profit,vpi.link_user_id, user.u as link_username
	from vendor_portal_info vpi
	join vendor_portal_branch_info vpbi on vpi.vendor_id = vpbi.vendor_id and vpbi.branch_id=".mi($branch['id'])."
	left join user on user.id=vpi.link_user_id
	where vpbi.vendor_id=$vid and vpbi.login_ticket=".ms($login_ticket)." $ssid_filter");
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$tmp){	// vendor portal information not found
		unset($_SESSION['vendor_portal']);
		return false;
	}
	
	$tmp['contact_email'] = trim($tmp['contact_email']);
	$tmp['contact_email_list'] = array();
	if($tmp['contact_email']){
		$tmp['contact_email_list'] = explode(",", $tmp['contact_email']);
		if($tmp['contact_email_list']){
			foreach($tmp['contact_email_list'] as $key=>$tmp_email){
				$tmp['contact_email_list'][$key] = trim($tmp_email);	// fixed white space
			}
		}
	}
	
	// check allowed branch
	$tmp['allowed_branches'] = unserialize($tmp['allowed_branches']);
	if(!$tmp['allowed_branches'][$tmp_vp_session['branch_id']]){	// not allow to login this branch
		unset($_SESSION['vendor_portal']);
		return false;
	}
	$tmp['sku_group_info'] = unserialize($tmp['sku_group_info']);
	$tmp['sales_report_profit'] = unserialize($tmp['sales_report_profit']);
	$tmp['sales_report_profit_by_date'] = unserialize($tmp['sales_report_profit_by_date']);
	$tmp['sales_bonus_by_step'] = unserialize($tmp['sales_bonus_by_step']);
	$tmp_vp_session['vp'] = $tmp;

	// this branch sku group bid and id	
	$sku_group_ids = $tmp_vp_session['vp']['sku_group_info'][$tmp_vp_session['branch_id']];
	if($sku_group_ids){
		list($sku_group_bid, $sku_group_id) = explode("|", $sku_group_ids);
		$tmp_vp_session['sku_group_bid'] = $sku_group_bid;
		$tmp_vp_session['sku_group_id'] = $sku_group_id;
		//if(!$sku_group_bid || !$sku_group_id)	$err[] = "Error on SKU Group setup, please contact admin to solve this.";
	}
		
	// keep update last login	
	$upd = array();
	$upd['last_login'] = 'CURRENT_TIMESTAMP';
	
	// gst
	if($config['enable_gst']){
		// check general if the GST is active
		$q1 = $con->sql_query("select setting_value from gst_settings where setting_name = 'active' and setting_value = 1");
		$gst_is_active = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		if($gst_is_active){
			$tmp_vp_session['gst_is_active'] = 1;
			
			$params = array();
			$params['vendor_id'] = $vid;
			$params['date'] = date("Y-m-d");
			$tmp_vp_session['is_under_gst'] = check_gst_status($params);
		}
	}
		
	if(!$tmp_vp_session['vp']['link_user_id']){	// auto add link user id
		$new_user = array();
		$new_user['active'] = 0;
		$new_user['level'] = 0;
		$new_user['fullname'] =  $tmp_vp_session['description'];
		$new_user['default_branch_id'] = 1;
		
		$vp_no = 0;
		
		// get max username start with vp%
		$con->sql_query("select max(u) from user where u like 'vp%'");
		$max_vp_u = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		
		if($max_vp_u){	// got number
			$vp_no = mi(substr($max_vp_u, 2));			
		}
		
		do{
			$vp_no++;
			$new_user['u'] = $link_username = sprintf("vp%06d", $vp_no);
			$new_user['l'] = $new_user['u'];
			$new_user['p'] = md5($new_user['u'].'8899');
		}while(!$con->sql_query("insert into user ".mysql_insert_by_field($new_user),false,false));	// try insert
		
		$link_user_id = $con->sql_nextid();	// get the inserted id 
		$upd['link_user_id'] = $tmp_vp_session['vp']['link_user_id'] = $link_user_id;
		$tmp_vp_session['vp']['link_username'] = $link_username;
	}
	
	$con->sql_query("update vendor_portal_info set ".mysql_update_by_field($upd)." where vendor_id=".mi($tmp_vp_session['id']));
			
	$vp_session = $tmp_vp_session;
	$smarty->assign('vp_session', $vp_session);
	
	return true;
}

function get_debtor_portal_info($ssid){
	global $con, $dp_session, $client_ip, $config, $smarty;
	
	$debtor_id = mi($_SESSION['debtor_portal']['debtor_id']);

	// get debtor info
	$con->sql_query("select * from debtor where active=1 and id=$debtor_id");
	$tmp_dp_session = $con->sql_fetchassoc();
	$con->sql_freeresult();

	if(!$tmp_dp_session){	// vendor not found
		unset($_SESSION['debtor_portal']);
		return false;
	}
	
	// get current branch_id
    $con->sql_query("select id,report_prefix from branch where code = " . ms(BRANCH_CODE));
    $branch = $con->sql_fetchassoc();
    $con->sql_freeresult();
    $tmp_dp_session['branch_id'] = $branch['id'];
    $tmp_dp_session['report_prefix'] = $branch['report_prefix'];
    
	$upd = array();
	$upd['last_dp_login'] = 'CURRENT_TIMESTAMP';
	$con->sql_query("update debtor set ".mysql_update_by_field($upd)." where id=$debtor_id");
	print_r($dp_session);
	
	$dp_session = $tmp_dp_session;
	$smarty->assign('dp_session', $dp_session);
	
	return true;
}

function get_sa_portal_info($ssid){
	global $con, $sa_session, $client_ip, $config, $smarty;
	
	$sa_id = mi($_SESSION['sa_ticket']['id']);

	// get debtor info
	$con->sql_query("select * from sa where active=1 and id=$sa_id");
	$tmp_sa_session = $con->sql_fetchassoc();
	$con->sql_freeresult();

	if(!$tmp_sa_session){	// sales agent not found
		unset($_SESSION['sa_ticket']);
		return false;
	}
	
	$sa_session = $tmp_sa_session;
	$smarty->assign('sa_session', $sa_session);
	
	return true;
}
?>