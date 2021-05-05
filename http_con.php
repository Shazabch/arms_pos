<?php
/*
1/11/2010 10:35:41 AM Andy
- Change to only read config and use built in mysql function
4/7/2010 3:46:51 PM edward
- add function check_member_info
11/29/2010 3:59:28 PM Justin
- Added new function to check S/N.
1/7/2011 2:17:42 PM Alex
- add check_voucher_info() and check_coupon_info() functions
3/17/2011 5:58:38 PM Alex
- fix date compare bugs on coupon and voucher
4/15/2011 6:29:02 PM Andy
- Add function get_sku_item_photo_list() to get sku photo array from remote.
- Add checking and if found $_REQUEST['SKIP_CONNECT_MYSQL'] will skip connect to MySQL.
5/23/2011 6:16:48 PM Andy
- Fix sku loading path.
6/16/2011 5:27:31 PM Alex
- only check 12 digit voucher remark for ARMS code only
6/30/2011 5:17:39 PM Alex
- fix join branch_id and show which branch can be used for the voucher => check_voucher_info()
7/7/2011 1:57:58 PM Andy
- Add function ajax_load_sku_item_photo() in http_con
8/3/2011 11:39:43 AM Alex
- add barcode checking for goods return
8/4/2011 10:25:16 AM Andy
- Add function check_mmpromotion_limit()
8/18/2011 11:10:26 AM Alex
- add return time or date and branches to counter => check_coupon_info() || check_voucher_info() 
10/11/2011 1:48:47 PM Alex
- enhance speed of loading voucher
10/20/2011 6:05:00 PM Kee Kee
- Add validate user account
10/21/2011 9:26:00 PM Kee Kee
- Update user password;
10/21/2011 11:10:24 AM Andy
- Change connection to use mysql class $con
10/21/2011 4:11:21 PM Alex
- fix voucher checking all branch 
10/24/2011 9:31:00 AM Kee Kee
- Add get_return_qty to get goods return quantity(goods return can do many time in same receipt with same item event the items was return)
11/29/2011 3:49:00 PM Kee Kee
- get stock balance
12/02/2011 2:55:01 PM Kee Kee
- get sku items with art no.
02/07/2012 10:42:00 AM Kee Kee
- get Pos deposit information

3/9/2012 1:01:19 PM Andy
- Add new http_con function get_process_status($uid, $modulename, $taskname, $statusname)

4/9/2012 6:57:24 PM Alex
- add new funcotion to delete sku photo => delete_sku_photo

06/29/2012 3:59:00 PM Kee Kee
- Set member type as member 1 if member type is null or empty

07/09/2012 2:10:00 PM Kee Kee
- Check voucher code is has been use or not 

07/31/2012 2:53 PM Kee Kee
- Get POS detail by receipt no

02/18/2013 11:35 PM Kee Kee
- Get pos items qty to get lastest stock balance

02/26/2013 12:12 PM Kee Kee
- Get Sales order information

07/29/2013 5:23 PM Kee Kee
- Added get Member Detail (Like Address,email,contact_no)

07/30/2013 01:51 PM Kee Kee
- Bug fixed on check_sn getting wrong info.

12/27/2013 11:51 AM Andy
- Fix delete sku application photo bug.

04/01/2013 10:06 AM Kee Kee
- search_doc_detail(PO, DO document)
- Search vendor list

4/18/2014 11:13 AM Andy
- Add new function get_branch_list()

05/13/2014 5:38 PM Kee Kee
- Fixed Get Multiple deposit (Ticket #744004)

04/06/2016 10:22 AM Kee Kee
- Added new function get_member_purchase_history()
- Hide toggle_customer_status() and get_config_list()

5/19/2016 1:57 PM Andy
- Enhanced to compatible with php7.

9/1/2016 15:00 Qiu Ying
- Add receipt_ref_no in get_pos_deposit_info()

9/15/2016 10:20 AM
- Fixed stock balance calculation

12/13/2016 4:12 PM Andy
- Enhanced get_sku_item_photo_list() to able to get sku apply photo.

02/23/2017 11:12 AM Kee Kee
- Update message into pos_finalised_error table after sync sales and found counter collection has finalized.(func: update_pos_finalize_error())

03/07/2017 16:55 PM Kee Kee
- Fixed Item Serial No status not updated after sell items issue 

3/23/2017 3:47 PM Justin
- Enhanced S/N validation to check against item on "Transition".

3/31/2017 10:31 AM Kee Kee
- Added update_counter_record()
- Fixed Update SN Info

04/19/2017 11:39 AM Kee Kee
- Ignore inactive counter when check counter login status

05/05/2017 14:00 PM Kee Kee
- Fixed Check pos_deposit_status record error

05/11/2017 10:23 AM Kee Kee
- Fixed get wrong member purchase history record

05/22/2017 9:36 AM Kee Kee
- Change "counter_sales_record" to "pos_transaction_counter_sales_record"

08/02/2017 14:19 PM Kee Kee
- Added 2 function get_counter_by_branch() & get_counter_status()

10/16/2017 13:19 PM Kee Kee
- Filter update missing record into pos_counter_collection_tracking table

5/4/2018 9:53 AM Andy
- Fixed get_pos_id_from_server bug.

7/25/2018 10:59 AM Andy
- Remove "desc counter_status".
- Change "upload_error".
- Enhanced to include "http_con.xtra.php" on top of program.
- Enhanced to check counter_status and pos_counter_collection_tracking data before update.

3/19/2019 4:52 PM Justin
- Added new function "get_max_receipt_no", use to search receipt no from backend for the first POS record when using receipt running number.

3/21/2019 4:13 PM Justin
- Bug fixed on get_stock_balance function some times will return PHP error and causing POS counter couldn't load out the stock balance by branch.

3/28/2019 2:54 Justin
- Fixed get_stock_balance error.

6/28/2019 10:38 AM William
- Added new get_sku_item_promo_photo_list function.

11/20/2020 1:18 PM Andy
- Added "advanced_mode".
- Added function "get_configs".
- Added function "get_price_change_sku_list".

1/8/2021 2:34 PM Shane
- Added function "get_time_attendance_list".

1/14/2021 4:10 PM Shane
- Added "pos_day_start_pw" and "pos_day_end_pw" at "get_configs" function

1/21/2021 1:29 PM Shane
- Added "link_code_name" at "get_configs" function

2/10/2021 10:33 AM Shane
- Added "membership_pmr" and "membership_pmr_name" at "get_configs" function

3/2/2021 12:54 PM Shane
- Enhanced "check_member_expired" function to be able to search by "phone_3" (mobile no).

3/12/2021 9:55 AM Shane
- Removed "phone_3" (mobile no) searched from "check_member_expired" for default exact match search.

3/12/2021 6:43 PM Shane
- Added "invoice_date_conversion_anchor" and "receipt_cashier_display" at "get_configs" function.

3/16/2021 5:00 PM Shane
- Added "pos_settings_pos_backend_tab" at "get_configs" function

3/25/2021 2:26 PM Andy
- Fixed "get_price_change_sku_list" old price didn't filter by sku_item_id.

4/7/2021 1:29 PM Shane
- Added "pos_multiple_login_with_same_user" at "get_configs" function.

4/13/2021 9:10 AM Shane
- Added "check_begin_of_day_status" function.

4/14/2021 3:00 PM Shane
- Added "check_nonpb_counters_status" function.

4/15/2021 11:21 AM Shane
- Added "check_end_of_day_status" function.

4/27/2021 4:40 PM Shane
- Added "get_items_cost" function.

4/28/2021 1:37 AM Shane
- Added "receive_file_from_counter" function.

5/3/2021 12:03 PM Shane
- Fixed issue where "receive_file_from_counter" unable to receive a large length of str.
*/

// Any extra code to filter
@include_once('http_con.xtra.php');

if(isset($_REQUEST['advanced_mode']) && $_REQUEST['advanced_mode']){
	include("include/common.php");
}else{
	define('TERMINAL',1);	// have to put this to avoid "Warning: Smarty not initialized" in config.php
	require("config.php");
	include("language.php");
	ini_set('memory_limit', '256M');
	set_time_limit(0);
	//error_reporting (E_ERROR);
	//error_reporting (E_ALL ^ E_NOTICE);

	if(!$_REQUEST['SKIP_CONNECT_MYSQL']){
		//$link = mysql_connect($db_default_connection[0], $db_default_connection[1],$db_default_connection[2]);
		//$db_selected = mysql_select_db($db_default_connection[3], $link);
		
		require_once('include/db.php');
	}
}

if(!function_exists('mysql_insert_by_field')){
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
}

if(!function_exists('mysql_update_by_field')){
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
}

if(!function_exists('ms')){
	function ms($str,$null_if_empty=0)
	{
		if (trim($str) === '' && $null_if_empty) return "null";
		settype($str, 'string');
		$str = str_replace("'", "''", $str);
		return "'" . (trim($str)) . "'";
	}
}

  
switch($_REQUEST['a']){
	case 'validate_login':
	    validate_login();
	    exit;
	case 'get_goods_return':
	     get_goods_return();
	    exit;
	case 'check_member_expired':
		check_member_expired();
		exit;
	case 'check_promotion_limit':
	    check_promotion_control_limit();
	    exit;
	case 'check_sn':
		check_sn();
		exit;
	case 'check_voucher_info':
	    check_voucher_info();
	    exit;
	case 'check_coupon_info':
	    check_coupon_info();
	    exit;
	case 'get_sku_item_photo_list':
	    get_sku_item_photo_list();
	    exit;
	case 'get_sku_item_promo_photo_list':
		get_sku_item_promo_photo_list();
		exit;
	case 'ajax_load_sku_item_photo':
		ajax_load_sku_item_photo();
		exit;
	case 'check_mmpromotion_limit':
		check_mmpromotion_limit();
		exit;
	case 'validate_user':
		validate_user();
		exit;
	case 'update_user_account':
		update_user_account();
		exit;
	case "get_goods_return_qty":
		get_goods_return_qty();
		exit;
	case 'stock_balance':
		get_stock_balance();
		exit;
	case 'get_item_by_art_no':
		get_item_by_art_no();
		exit;
	case 'get_pos_deposit_info':
		get_pos_deposit_info();
		exit;
	case 'get_pos_deposit_status':
		get_pos_deposit_status();
		exit;
	case 'ajax_get_cc_finalize_status':
		ajax_get_cc_finalize_status();
		exit;
	case 'check_pos_info':
		//check pos is exists not after cannot get data from get_pos_deposit_info
		check_pos_info();
		exit;
	case 'get_counter_name':
		get_other_branch_counter_name();
		exit;
	case 'delete_sku_photo':
		delete_sku_photo();
		exit;
	case 'get_use_member_point':
		get_use_member_point();
		exit;
	case 'get_voucher_info':
		get_voucher_info();
		exit;
	case 'check_voucher_normal_info':
		check_voucher_normal_info();
		exit;
	case 'getSalesByReceiptNo':
		getSalesByReceiptNo();
		exit;
	case 'search_sales_order':
		search_sales_order();
		exit;
	case 'update_sales_order':
		update_sales_order();
		exit;
	case 'get_sn_info_gr':
		get_sn_info_gr();
		exit;
	case 'get_membership_detail':
		get_membership_detail();
		exit;
	case 'get_vendor_list':
		get_vendor_list();
		exit;
	case 'search_doc_detail':
		search_doc_detail();
		exit;
	case 'update_grn':
		update_grn();
		exit;
	case 'get_branch_list':
		get_branch_list();
		exit;
	case 'get_server_list':
		get_server_list();
		exit;
	case 'update_counter_sync_log':
		update_counter_sync_log();
		exit;
	case 'check_server_pos_deposit_status_last_update':
		check_server_pos_deposit_status_last_update();
		exit;
	case 'upload_error':
		upload_error();
		exit;
	case 'update_counter_tmp_log':
		update_counter_tmp_log();
		exit;
	case 'get_pos_id_from_server':
		get_pos_id_from_server();
		exit;
	case 'update_pos_counter_collection_tracking':	// This no longer need
		//update_pos_counter_collection_tracking();	
		exit;
	case 'get_pos_finalized':
		get_pos_finalized();
		exit;
	case 'check_so_exported_to_pos':
		check_so_exported_to_pos();
		exit;
	case 'get_db_backup_list':
		get_db_backup_list();
		exit;
	case 'get_counter_list':
		get_counter_list();
		exit;
	/*case 'toggle_customer_status':
		toggle_customer_status();
		exit;
	case 'get_config_list':
		get_config_list();
		exit;*/
	case 'get_member_purchase_history':
		get_member_purchase_history();
		exit;
	case 'update_pos_finalize_error':
		update_pos_finalize_error();
		exit;
	case 'update_item_sn':
		update_item_sn();
		exit;
	case 'update_counter_sales_record':
		update_counter_sales_record();
		exit;
	case 'get_total_sales_record':
		get_total_sales_record();
		exit;
	case "get_counter_by_branch":
		get_counter_by_branch();
		exit;
	case "get_counter_status":
		get_counter_status();
		exit;
	case "get_max_receipt_no":
		get_max_receipt_no();
		exit;
	case "check_sku_item_promo_image":
		check_sku_item_promo_image();
		exit;
	case "get_pos_credit_member_topup_used":
		get_pos_credit_member_topup_used();
		exit;
	case 'get_configs':
		get_configs();
		exit;
	case 'get_price_change_sku_list':
		get_price_change_sku_list();
		exit;
	case 'get_time_attendance_list':
		get_time_attendance_list();
		exit;
	case 'check_begin_of_day_status':
		check_begin_of_day_status();
		exit;
	case 'check_nonpb_counters_status':
		check_nonpb_counters_status();
		exit;
	case 'check_end_of_day_status':
		check_end_of_day_status();
		exit;
	case 'get_items_cost':
		get_items_cost();
		exit;
	case 'receive_file_from_counter':
		receive_file_from_counter();
		exit;
}





function validate_login(){
	global $con;
	
	$user_id = intval($_REQUEST['user_id']);
	$branch_id = intval($_REQUEST['branch_id']);
	$counter_id = intval($_REQUEST['counter_id']);

	//$query_id = $con->sql_query("select * from counter_status where user_id=$user_id and branch_id=$branch_id and id != $counter_id limit 1");
	$query_id = $con->sql_query("select cs.* from counter_settings join counter_status cs 
								on cs.id=counter_settings.id and cs.branch_id = counter_settings.branch_id 
								where cs.user_id=".ms($user_id)." and 
								cs.branch_id=".ms($branch_id)." and 
								cs.id != ".ms($counter_id)." and counter_settings.active = 1 limit 1");
	
	while($r = $con->sql_fetchassoc($query_id))
	{
		$result[] = $r;
	}	
	$con->sql_freeresult($query_id);
	print serialize($result);
}



function get_goods_return(){        
	global $con;
	
	$receipt_no = $_REQUEST['receipt_no'];
	$date = $_REQUEST['date'];
	$network_name = $_REQUEST['network_name'];
	$branch_code = $_REQUEST['branch_code'];
	if ($_REQUEST['barcode']) $barcode = ms($_REQUEST['barcode']);

	if ($barcode){
	$filter=" AND (si.mcode=$barcode OR si.link_code=$barcode OR si.sku_item_code=$barcode OR pi.barcode=$barcode)";
	}

	$ccard_type  = array(ms("Diners"),ms("AMEX"),ms("VISA"),ms("Master"),ms("Discover"),ms("Others"));
	$branch_sql = "Select id from branch where code=".ms($branch_code);
	$query_branch = $con->sql_query($branch_sql);
	$branch_id = $con->sql_fetchrow($query_branch);
	$is_cc_payment = 0;
	$con->sql_freeresult($query_branch);

	$nn_sql = "Select id from counter_settings Where network_name=".ms($network_name)." AND branch_id=".ms($branch_id[0]);
	$query_nn = $con->sql_query($nn_sql);
	$net_name = $con->sql_fetchrow($query_nn);
	$con->sql_freeresult($query_nn);
	
	$ps_sql = "select * from pos_settings where branch_id=".ms($branch_id[0]);
	$query_ps = $con->sql_query($ps_sql);
	while($r = $con->sql_fetchrow($query_ps))
	{
		$ps_setting[$r['setting_name']] = $r['setting_value'];
	}
	$con->sql_freeresult($query_ps);
	
	$sql = "select pp.type from pos p
	left join pos_payment pp ON p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.id = pp.pos_id and p.date = pp.date
	where p.receipt_no=".ms($receipt_no)." AND p.date = ".ms($date)." and p.counter_id=".ms($net_name[0])." and p.branch_id=".ms($branch_id[0])." and pp.type in (".join(",",$ccard_type).")";

	$query_id = $con->sql_query($sql);
	if($con->sql_numrows()>0)
	{
		$is_cc_payment = 1;
	}
	$con->sql_freeresult($query_id);
	
	$sql = "select pi.item_id, pi.price as selling_price, pi.discount as old_discount, p.*, pi.*, si.mcode, si.link_code, si.receipt_description, si.sku_item_code,si.artno, si.decimal_qty from pos p
	left join pos_items pi ON p.branch_id = pi.branch_id and p.counter_id = pi.counter_id and p.id = pi.pos_id and p.date = pi.date
	left join sku_items si on pi.sku_item_id = si.id Where pi.qty>0 AND p.receipt_no=".ms($receipt_no)." AND p.date = ".ms($date)." AND p.counter_id=".ms($net_name[0])." AND p.branch_id=".ms($branch_id[0]).$filter;	
	
	

	$query_id = $con->sql_query($sql);
	
	while($r = $con->sql_fetchrow($query_id))
	{
		$r['pos_time'] = strtotime($r['pos_time']);
		$r['is_pay_credit_card'] = $is_cc_payment;
		$more_info = unserialize($r['more_info']);
		if(isset($more_info['is_sn'])){			
			$r['is_sn'] = $more_info['is_sn'];
		}else{			
			$ret = $con->sql_query("select * from sn_info where pos_id = ".ms($r['pos_id'])." and branch_id = ".ms($r['branch_id'])." and counter_id = ".ms($r['counter_id'])." and item_id = ".ms($r['item_id'])." and sku_item_id = ".ms($r['sku_item_id'])." and date=".ms($r['date']));
			
			if($con->sql_numrows($ret)>0)
			{	
				$r['is_sn'] = 1;
			}
			$con->sql_freeresult($ret);
		}
		
		if($more_info['sku_barcode']=='normal' && $r['barcode']!=$r['mcode'] && $r['barcode']!=$r['sku_item_code'] && $r['barcode']!=$r['link_code'] && $r['barcode']!=$r['artno'] && weight_scale_code($ps_setting,$r['barcode']))
		{
			$more_info['sku_barcode']='weight_scale_code';
			$r['more_info'] = serialize($more_info);
		}

		$result[] = $r;
	}

	$con->sql_freeresult($query_id);
	print serialize($result);
}

function check_member_expired(){
	global $con;
	
	$member_info = $_REQUEST['member_info'];
	$nric_name_card_no = $_REQUEST['nric_name_card_no'];
	
	//$membership = array();
	if ($nric_name_card_no){
        $member_info = ms('%'.$member_info.'%');
		$member_sql = $con->sql_query("select * from membership where nric like $member_info or name like $member_info or card_no like $member_info or phone_3 like $member_info order by card_no limit 50");
	}
	else{
		if($_REQUEST['search_type'] =='nric'){
			$where = "where nric=".ms($member_info);
		}elseif($_REQUEST['search_type'] =='ic_card'){
			$where = "where nric=".ms($member_info)." or card_no=".ms($member_info);
		}else{
			$where = "where card_no=".ms($member_info);
		}
		$member_sql = $con->sql_query("select * from membership ".$where);
	}

  	while($r = $con->sql_fetchrow($member_sql)){
		if($r['member_type']==""){
			$r['member_type'] = 'member1';
		}
		$result[] = $r;
  	}
	$con->sql_freeresult($member_sql);
	
  	print serialize($result);
}

function check_promotion_control_limit(){
	global $con;
	
	$member_no = ms($_REQUEST['member_no']);
	$promo_id = intval($_REQUEST['promo_id']);
	$sku_item_id = intval($_REQUEST['sku_item_id']);
	$start_date = ms($_REQUEST['start_date']);
	$end_date = ms($_REQUEST['end_date']);
	$date = ms($_REQUEST['date']);
	$type = $_REQUEST['type'];
	$branch_id = $_REQUEST['branch_id'];
	
	if ($type=='day'){		
        $promotion_control_sql = $con->sql_query("select sum(qty) as total_qty,sku_item_id from membership_promotion_items where card_no=$member_no and promo_id=$promo_id and sku_item_id=$sku_item_id and branch_id=$branch_id and date=$date and cancelled=0 group by sku_item_id");
	}elseif ($type=='period'){
        $promotion_control_sql = $con->sql_query("select sum(qty) as total_qty,sku_item_id from membership_promotion_items where card_no=$member_no and promo_id=$promo_id and sku_item_id=$sku_item_id and branch_id=$branch_id and date between $start_date and $end_date and cancelled=0 group by sku_item_id");
	}

	while($r = $con->sql_fetchrow($promotion_control_sql)){
		$result[] = $r;
  	}
	$con->sql_freeresult($promotion_control_sql);
  	print serialize($result);
}

function check_sn(){
	global $con;
	
	$sku_item_id = $_REQUEST['sku_item_id'];
	$branch_id = $_REQUEST['branch_id'];
	$sn = $_REQUEST['sn'];
	$ret = "";
	$item = array();
	
	// check whether S/N avalaible or not
	$sql = $con->sql_query("select * from pos_items_sn where sku_item_id = '$sku_item_id' and located_branch_id = '$branch_id' and serial_no = '$sn'");
	
	while($r = $con->sql_fetchassoc($sql)){
		$item = $r;
		if($r['active'] == 0) $ret = 5; // the S/N are inactive
		else{
			// check if this S/N has been sold
			$sql1 = $con->sql_query("select sni.*,p.cancel_status
									 from sn_info sni
									 join pos p on p.id = sni.pos_id and p.branch_id = sni.branch_id and p.date = sni.date and p.counter_id = sni.counter_id 
									 where sni.sku_item_id = '$sku_item_id' and sni.serial_no = '$sn' and p.branch_id=$branch_id
									 order by p.end_time desc
									 limit 1");
			$sn_info = $con->sql_fetchassoc($sql1);
			$con->sql_freeresult($sql1);

			if(($sn_info['active'] == 1 && $sn_info['cancel_status']==0) || (!$ret && trim(strtolower($r['status'])) == "Sold")) $ret = 1; // the S/N are being sold
		}
	}
	if(!$ret && count($item) > 0){
		if($item['status'] == "Transition") $ret = 3; // S/N are not available, it is on transition to other branch
		else $ret = 2; // S/N are available
	}elseif(!$ret && count($item) == 0) $ret = 3; // S/N is not found
	
	$con->sql_freeresult($sql);
	
	print $ret;
}

function check_voucher_info(){
	global $con;
	
	$branch_id = $_REQUEST['branch_id'];	
	$voucher_no = $_REQUEST['voucher_no'];
	$voucher_amount = $_REQUEST['voucher_amount'];
	$now_date = strtotime(date("Y-m-d H:i:s"));

	$sql_code = "select mv.*, branch.code as branch_code from mst_voucher mv 
				left join branch on mv.branch_id=branch.id 
				where mv.code=".ms($voucher_no)." and mv.cancel_status=0";
	$q_voucher = $con->sql_query($sql_code);

	if ($con->sql_numrows($q_voucher)>0){
		while($r = $con->sql_fetchrow($q_voucher)){
		 	$result['branch_id'] = $r['branch_id'];
		 	$result['branch_code'] = $r['branch_code'];
		 	$result['voucher_value'] = $r['voucher_value'];
		 	$result['active'] = $r['active'];
			$result['valid_from'] = strtotime($r['valid_from']);
			$result['valid_to'] = strtotime($r['valid_to']);
			$result['allow_interbranch']= unserialize($r['allow_interbranch']);
			$result['disallow_disc_promo'] = $r['disallow_disc_promo'];
			$result['disallow_other_voucher'] = $r['disallow_other_voucher'];
	  	}
				
		if (!$result['active']){
			print "inactive";
			return;
		}elseif (!$result['allow_interbranch'][$branch_id]){
			if (!is_array($result['allow_interbranch'])){
				print "not_allow|?????";
				return;
			}
			
			$str='';
			while ($a=array_splice($result['allow_interbranch'],8)){
				$str.="\n".implode(", ",$result['allow_interbranch']);
				$result['allow_interbranch']=$a;
				if ($result['allow_interbranch']) $str.=", ";	
			}
			if ($result['allow_interbranch'])	$str.="\n".implode(", ",$result['allow_interbranch']);
			
			print "not_allow|".$str;
			return;
		}

		$sql_pos = "select pp.* from pos_payment pp
				left join pos on pp.counter_id=pos.counter_id and pp.branch_id=pos.branch_id and pp.pos_id=pos.id and pp.date=pos.date
				where pos.cancel_status=0 and pp.type='VOUCHER' and length(pp.remark)=12 and pp.remark like ".ms($_REQUEST['voucher_no'].'%');
	    $q_pos = $con->sql_query($sql_pos) or die('offline');
	    
		if ($con->sql_numrows($q_pos)>0)	print "used";
		elseif	($now_date > $result['valid_to'])	print "expired";
		elseif	($now_date < $result['valid_from']){
			$date_from=date("d/m/Y",$result['valid_from']);
			print "havent_start|$date_from";
		}elseif	($voucher_amount != $result['voucher_value'])	print "amount_not_match";
		else    print "ok";
	}else{
		print "code_not_match";
	}
}

function check_coupon_info(){
	global $con;
	
	$branch_id = $_REQUEST['branch_id'];
	$coupon_no = $_REQUEST['coupon_no'];
	/*$now_date = strtotime(date("Y-m-d"));
	$now_time = strtotime(date("H:i:s"));*/

	$sql = "select * from coupon where code=".ms($coupon_no);
    $q_coupon = $con->sql_query($sql);

	if ($con->sql_numrows($q_coupon)>0){
		$r = $con->sql_fetchrow($q_coupon);
		print serialize($r);
	 	//while(){
		 	/*$result['active'] = $r['active'];
			$result['valid_from'] = strtotime($r['valid_from']);
			$result['valid_to'] = strtotime($r['valid_to']);
			$result['time_from'] = strtotime($r['time_from']);
			$result['time_to'] = strtotime($r['time_to']);*/
	  	//}

	  	/*if (!$result['active']) print "inactive";
		elseif ($now_date > $result['valid_to'])	print "expired";
		elseif ($now_date < $result['valid_from']){    
			$date_from=date("d/m/Y",$result['valid_from']);
			print "havent_start|$date_from";
		}elseif ($now_date == $result['valid_to'] && $now_time > $result['time_to'])	print "expired";
		elseif ($now_time > $result['time_to'] || $now_time < $result['time_from']){    
			$timestamp = date("h:i A",$result['time_from'])." to ".date("h:i A",$result['time_to']); 
			print "time_not_reach|$timestamp";
		}else    print "ok";*/
	}else{
		print "code_not_match";
	}
}

function get_sku_item_photo_list(){
	global $con;
	
    $sku_item_id = intval($_REQUEST['sku_item_id']);
	$sku_apply_items_id = intval($_REQUEST['sku_apply_items_id']);
	$server_path = $_SERVER['HTTP_HOST'];
	if(strpos($server_path, ":")===false){
		$server_path .= ":$_SERVER[HTTP_PORT]";
	}
	$photo_list = array();
	
    //print_r($_SERVER);
	$use_new_sku_photo_path = file_exists($_SERVER['DOCUMENT_ROOT']."/sku_photos/use_new_sku_photo_path.txt") ? true : false;
	
	// get apply photo
	if($sku_apply_items_id){
		// check whether photo got move to use new structure or not
		if($use_new_sku_photo_path){    // use new structure
			$group_num = ceil($sku_apply_items_id/10000);
			$abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/apply_photo/".$group_num."/".$sku_apply_items_id."/";
		}else{
			$abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/".$sku_apply_items_id."/";
		}
		
		foreach(array_merge(glob("$abs_path/*.jpg"),glob("$abs_path/*.JPG")) as $f){
			$f = str_replace("$_SERVER[DOCUMENT_ROOT]/", "", $f);
			$photo_list[] = "http://$server_path/".$f;
		}
	}
	
	// get upload photo
	if($use_new_sku_photo_path){
		$group_num = ceil($sku_item_id/10000);
		$abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/actual_photo/".$group_num."/".$sku_item_id."/";
	}else{
		$abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/a/".$sku_item_id."/";
	}
	
	foreach(array_merge(glob("$abs_path/*.jpg"),glob("$abs_path/*.JPG")) as $f){
		$f = str_replace("$_SERVER[DOCUMENT_ROOT]/", "", $f);
		$photo_list[] = "http://$server_path/".$f;
	}
	//print_r($photo_list);
	print serialize($photo_list);
}
function get_sku_item_promo_photo_list(){
	global $con;
	
	$sku_item_id = intval($_REQUEST['sku_item_id']);
	$server_path = $_SERVER['HTTP_HOST'];
	if(strpos($server_path, ":")===false){
		$server_path .= ":$_SERVER[HTTP_PORT]";
	}
	$photo_list = array();
	// get upload photo
	$group_num = ceil($sku_item_id/10000);
	$abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/promo_photo/".$group_num."/".$sku_item_id."/";
	
	foreach(array_merge(glob("$abs_path/*.jpg"),glob("$abs_path/*.JPG")) as $f){
		$f = str_replace("$_SERVER[DOCUMENT_ROOT]/", "", $f);
		$photo_list[] = "http://$server_path/".$f;
	}
	//print_r($photo_list);
	print serialize($photo_list);
}

function ajax_load_sku_item_photo(){
	$sku_item_id = intval($_REQUEST['sku_item_id']);
	$url_to_get_photo = trim($_REQUEST['url_to_get_photo']);
	
	if(!$sku_item_id || !$url_to_get_photo)	exit;
	
	$tmp_photo_list = file_get_contents($url_to_get_photo);
	$photo_list = unserialize($tmp_photo_list);
	if(!$photo_list)	exit;
	
	$ret = array('photo_list'=>$photo_list);
	print json_encode($ret);
}

function check_mmpromotion_limit(){
	global $con;
	
	$bid = intval($_REQUEST['branch_id']);
	$date = $_REQUEST['date'];
	$member_no = $_REQUEST['member_no'];
	$promo_group_id_list = $_REQUEST['p'];
	
	if(!is_array($promo_group_id_list) || !$promo_group_id_list)	die();
	
	$ret = array();
	foreach($promo_group_id_list as $promo_group_id){
		list($counter_promo_id, $group_id, $control_type, $date_from, $date_to) = explode(",", $promo_group_id);
		
		if($control_type!= 1 && $control_type != 2)	continue;
		
		$date_filter = '';
		if($control_type==1){	// limit by day
			$date_filter = " and date=".ms($date);
		}else{	// limit by period
			$date_filter = " and date between ".ms($date_from)." and ".ms($date_to);
		}
		$sql = "select sum(used) as total_used 
		from membership_promotion_mix_n_match_items
		where branch_id=$bid and promo_id=".ms($counter_promo_id)." and card_no=".ms($member_no)." and group_id=".ms($group_id)." $date_filter";
		$query_id = $con->sql_query($sql);
		$data = $con->sql_fetchrow($query_id);
		if($data['total_used']>0){
			$ret[$counter_promo_id.",".$group_id]['used'] = $data['total_used'];
		}
	}
	
	//print_r($ret);
	print serialize($ret);
}

function validate_user()
{
	global $con;
	
	$ret = array();
	$username = $_REQUEST['username'];
	if(isset($_REQUEST['password'])) {
		$password = $_REQUEST['password'];
		$sql = "Select * from user Where active = 1 AND l=".ms($username)." AND p=".ms(md5($password));
	}
	else{
		$sql = "Select * from user Where active = 1 AND u=".ms($username);
	}
	
	$query_id = $con->sql_query($sql);
	while($user = @$con->sql_fetchrow($query_id))
	{
		$ret[] = $user;
	}
	print serialize($ret);
}

function update_user_account()
{
	global $con;
	
	$user_id = ms($_REQUEST['id']);
	$p = ms(md5($_REQUEST['password']));
	$sql = "Update user set p=".$p." Where id=".$user_id;
	$con->sql_query($sql);
	if($con->sql_affectedrows()<=0)
	{
		print "Failed.";
	}
	else
	{
		print "Success.";
	}
}

function get_goods_return_qty()
{
	global $con;
	$receipt_no = $_REQUEST['receipt_no'];
	$date = $_REQUEST['date'];
	$network_name = $_REQUEST['network_name'];
	$branch_code = $_REQUEST['branch_code'];
	$item_id = $_REQUEST['item_id'];
	//Get Branch ID && Counter ID
	$branch_sql = "Select * from branch Where code=".ms($branch_code);
	$counter_sql = "Select * from counter_settings Where network_name=".ms($network_name);
	$con->sql_query($branch_sql);
	$ret = $con->sql_fetchrow();
	$branch_id = $ret['id'];
	$con->sql_query($counter_sql);
	$ret = $con->sql_fetchrow();
	$counter_id = $ret['id'];
	
	//Get pos ID
	$pos_sql = "Select * from pos Where receipt_no=".ms($receipt_no)." AND branch_id=".ms($branch_id)." AND counter_id=".ms($counter_id)." AND date=".ms(date("Y-m-d",strtotime($date)));
	$con->sql_query($pos_sql);
	$ret = $con->sql_fetchrow();
	$pos_id = $ret['id'];
	
	//Get return Quantity
	$ret = false;
	$sql = "Select pi.* from pos_goods_return pgr
			JOIN pos_items pi ON pgr.branch_id=pi.branch_id and pgr.counter_id=pi.counter_id and pgr.date=pi.date and pgr.pos_id=pi.pos_id and pgr.item_id=pi.item_id
			JOIN pos p on p.branch_id=pi.branch_id and p.counter_id=pi.counter_id and p.date=pi.date and p.id=pi.pos_id
			where pgr.branch_id=".ms($branch_id)." and pgr.return_date=".ms(date("Y-m-d",strtotime($date)))." and pgr.return_counter_id=".ms($counter_id)." and pgr.return_pos_id=".ms($pos_id)." and pgr.return_item_id=".ms($item_id)." and p.cancel_status=0";
	$query_id = $con->sql_query($sql);
	while($pos = $con->sql_fetchrow($query_id))
	{
		$ret[] = $pos;
	}
	print serialize($ret);
}

function get_stock_balance()
{
	global $con;
	$stock_balance = array();
		
	$sku_item_id = $_REQUEST['item'];
	$sql = "select max(date) as last_finalize_date, branch_id from pos_finalized where finalized = 1 group by branch_id";
	$query_id = $con->sql_query($sql);
	if($con->sql_numrows($query_id)>0)
	{
		while($sb = $con->sql_fetchrow($query_id))
		{
			$sql = "select sum(pi.qty) as total_qty, pi.branch_id,branch.code as bname from 
									pos_items as pi join pos as p on 
									pi.branch_id=p.branch_id and 
									pi.counter_id=p.counter_id and 
									pi.date=p.date and 
									pi.pos_id=p.id join branch on 
									p.branch_id=branch.id 
									where p.cancel_status = 0 and 
									pi.sku_item_id=".ms($sku_item_id)." and 
									p.branch_id=".ms($sb['branch_id'])." and 
									(p.date >".ms($sb['last_finalize_date'])." and p.date <=".ms(date("Y-m-d")).")";
			$qid = $con->sql_query($sql);
			$result = $con->sql_fetchrow($qid);
			if($result['total_qty'])
			{
				$stock_balance[$result['branch_id']]['qty'] = (0-$result['total_qty']);
				$stock_balance[$result['branch_id']]['branch_name'] = $result['bname'];
			}
		}
	}

	$sql = "select sku_items_cost.*, branch.code as branch_name,branch.id as branch_id from 
			sku_items_cost join branch on 
			sku_items_cost.branch_id=branch.id 
			where sku_item_id=".ms($sku_item_id)." and 
			branch.active=1 order by sku_items_cost.branch_id";
	
	$query_id = $con->sql_query($sql);
	if($con->sql_numrows($query_id)>0)
	{
		while($sb = $con->sql_fetchassoc($query_id))
		{
			if(!isset($stock_balance[$sb['branch_id']]['qty'])) $stock_balance[$sb['branch_id']]['qty'] = $sb['qty'];
			else $stock_balance[$sb['branch_id']]['qty'] += $sb['qty'];
			$stock_balance[$sb['branch_id']]['branch_name'] = $sb['branch_name'];
			//$ret[] = $sb;
		}
	}

	if(!$stock_balance){
		$stock_balance['error_message'] = "Cannot found stock balance.";
	}

	print serialize($stock_balance);
}

function get_item_by_art_no()
{
	$ret = false;
	global $con;
	$art_no = $_REQUEST['art_no'];
	$sql = "Select id, sku_item_code,mcode,link_code,receipt_description,selling_price,open_price from sku_items Where artno=".ms($art_no)." AND active=1 order by sku_item_code";
	$query_id = $con->sql_query($sql);
	while($sku_item = $con->sql_fetchrow($query_id))
	{
		$ret[] = $sku_item;
	}
	
	if($ret == false)
		print "false";
	else
		print serialize($ret);
}

function get_pos_deposit_info()
{
	$ret = false;
	global $con;
	$branch_id = $_REQUEST['branch_id'];
	if(!isset($_REQUEST['search']))
	{
		$counter_id = $_REQUEST['counter_id'];
		$date = $_REQUEST['date'];
		$receipt_no = $_REQUEST['receipt_no'];
		$cond = " where p.branch_id=".ms($branch_id)." and p.counter_id=".ms($counter_id)." and p.receipt_no=".ms($receipt_no)." and p.date=".ms($date);
	}
	else
	{
		$cond = " where p.deposit=1 and p.cancel_status=0";
		
		if($branch_id!="All"){
			$cond .= " and p.branch_id=".ms($branch_id);
		}
		
		if($_REQUEST['exclude_date']) {
			$cond .= " and p.date!=".ms($_REQUEST['exclude_date']);
		}
		
		if($_REQUEST['member_no']!=""){
			$cond .= " and p.member_no=".ms($_REQUEST['member_no']);
		}
		else{
			$cond .= " and p.receipt_remark like ".ms("%:\"".$_REQUEST['name']."\"%")." and p.receipt_remark like ".ms("%:\"".$_REQUEST['ic_no']."\"%");
		}
	}
	$sql = "select pd.*,p.start_time,p.end_time,p.amount,p.deposit,p.cancel_status, p.member_no,pds.status as deposit_status, pp.type, pp.remark, p.receipt_ref_no from pos_deposit as pd join pos as p on p.id = pd.pos_id and p.branch_id=pd.branch_id and p.counter_id = pd.counter_id and p.date = pd.date join pos_payment as pp on pp.pos_id = p.id and pp.date=p.date and pp.counter_id=p.counter_id and p.branch_id=pp.branch_id  join pos_deposit_status as pds on pd.branch_id = pds.deposit_branch_id and pd.counter_id = pds.deposit_counter_id and pd.date = pds.deposit_date and pd.receipt_no = pds.deposit_receipt_no".$cond;
	
	$query_id = $con->sql_query($sql);
	if($con->sql_numrows()>0)
	{
		if($_REQUEST['search']==1)
		{
			while($r = $con->sql_fetchrow($query_id)){
				$ret[] = $r;
			}
		}
		else
		{
			$ret = $con->sql_fetchrow($query_id);
			
			if($ret['deposit_status']!=0)
			{
				$ret['error'] = "This ".$receipt_no." deposit has beed ".($ret['deposit_status']==2?"used":"cancelled");
			}
		}
	}

	if($ret==false)
		print "No deposit data has been found";	
	else
		print serialize($ret);
}

function get_pos_deposit_status()
{
	$ret = false;
	global $con;
	$branch_id = $_REQUEST['branch_id'];
	$counter_id = $_REQUEST['counter_id'];
	$date = $_REQUEST['date'];
	$receipt_no = $_REQUEST['receipt_no'];
	$sql = "select * from pos_deposit_status where deposit_branch_id=".ms($branch_id)." and deposit_counter_id=".ms($counter_id)." and deposit_date=".ms($date)." and deposit_receipt_no=".ms($receipt_no);
	$query_id = $con->sql_query($sql);
	if($con->sql_numrows()>0)
	{
		$ret = $con->sql_fetchrow($query_id);
	}
	
	if($ret==false || !$ret)
		print "false";
	else
		print serialize($ret);
}

function check_pos_info()
{
	$ret = false;
	global $con;
	$branch_id = $_REQUEST['branch_id'];
	if(!isset($_REQUEST['search']))
	{
		$counter_id = $_REQUEST['counter_id'];
		$date = $_REQUEST['date'];
		$receipt_no = $_REQUEST['receipt_no'];
		$cond = " where p.branch_id=".ms($branch_id)." and p.counter_id=".ms($counter_id)." and p.receipt_no=".ms($receipt_no)." and p.date=".ms($date);
	}
	
	$sql = "select *from pos as p".$cond;

	$query_id = $con->sql_query($sql);
	if($con->sql_numrows()>0)
	{
		$ret = $con->sql_fetchrow($query_id);
	}

	if($ret==false)
		print "false";
	else
		print serialize($ret);
}

function ajax_get_cc_finalize_status(){
	$uid = $_REQUEST['uid'];
	$modulename = $_REQUEST['modulename'];
	$taskname = $_REQUEST['taskname'];
	$statusname = $_REQUEST['statusname'];
	
	print get_process_status($uid, $modulename, $taskname, $statusname);
}

function get_process_status($uid, $modulename, $taskname, $statusname){	
	$txt = "include/process_status.txt";
	$str = file_get_contents($txt);
	
	$arr = unserialize($str);
	return $arr[$uid][$modulename][$taskname][$statusname];
}

function get_other_branch_counter_name(){
	global $con;
	$counter_id = $_REQUEST['counter_id'];
	$branch_id = $_REQUEST['branch_id'];
	$nn_sql = "Select network_name from counter_settings where id=".ms($counter_id)." AND branch_id=".ms($branch_id);
	$query_nn = $con->sql_query($nn_sql);
	$net_name = $con->sql_fetchrow($query_nn);
	print $net_name[0];
}

function delete_sku_photo(){
	global $con;

	$file_pattern = "/[0-9]*.jpg|[0-9]*.jpeg|[0-9]*.png/";
	$file_type_pattern = "/.jpg$|.jpeg$|.png$/D";

	$file_path = urldecode($_REQUEST['file_path']);
	$sku_apply_items_id = intval($_REQUEST['sku_apply_items_id']);

	if(!$file_path || !$sku_apply_items_id) die('Delete failed');

	$file_path_split = preg_split("/\/\//",$file_path);
	
	$dir_path = preg_replace($file_pattern,"",$file_path);

	if (file_exists($file_path)){
		if (@unlink($file_path)){
			$con->sql_query("select photo_count from sku_apply_items where id=$sku_apply_items_id");
			$sai = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($sai){
				$upd = array();
				$upd['photo_count'] = $sai['photo_count']-1;
				if($upd['photo_count']<0)	$upd['photo_count'] = 0;
				
				$con->sql_query("update sku_apply_items set photo_count=".intval($upd['photo_count'])." where id=$sku_apply_items_id");
			}
		    
	
			//get default file name 
			if(preg_match($file_pattern,$file_path,$matches)){
				$default_file_no = preg_replace($file_type_pattern,"",$matches[0]);
			}
		    
			//renaming other files
			if (is_dir($dir_path)) {
			    if ($dh = opendir($dir_path)) {
			        while (($file = readdir($dh)) !== false) {
			        	if (filetype($dir_path . $file) == "file"){
			        		if (preg_match($file_type_pattern,$file,$matches)){
								$file_key = intval(preg_replace($file_type_pattern,"",$file));
								$file_array[$file_key]['name'] = $file;
								$file_array[$file_key]['extension'] = $matches[0];
							}
						}
			        }
			        closedir($dh);
			    }
			}
			if ($file_array){
				@ksort($file_array);
				$i=$default_file_no;
				foreach ($file_array as $key => $other){
					//rename file name more than removed file
					if ($i<=$key){
						$before_change = $dir_path . $other['name']; 
						$after_change = $dir_path . $i . $other['extension']; 
						@rename($before_change,$after_change);
						@touch($after_change);
						@chmod($after_change,0777);
					}
					$i++;
				}
			}
	        print "OK";
		}else{
	        print "Delete failed";
		}
	}else{
		print "File not existed";
	}
}

function get_voucher_info()
{
	global $con;
	
	$branch_id = $_REQUEST['branch_id'];	
	$voucher_no = $_REQUEST['voucher_no'];
	$voucher_amount = $_REQUEST['voucher_amount'];
	$now_date = strtotime(date("Y-m-d H:i:s"));

	$sql_code = "select mv.*, branch.code as branch_code from mst_voucher mv 
				left join branch on mv.branch_id=branch.id 
				where mv.code=".ms($voucher_no)." and mv.cancel_status=0";
	$q_voucher = $con->sql_query($sql_code);

	if ($con->sql_numrows($q_voucher)>0){
		while($r = $con->sql_fetchrow($q_voucher)){
			$result['allow_interbranch']= unserialize($r['allow_interbranch']);
			$result['disallow_disc_promo'] = $r['disallow_disc_promo'];
			$result['disallow_other_voucher'] = $r['disallow_other_voucher'];
	  	}
		print serialize($result);
	}else{
		print false;
	}
}

function check_voucher_normal_info()
{
	global $con;
	$voucher_no = $_REQUEST['voucher_no'];
	
	$sql_pos = "select pp.* from pos_payment pp
		left join pos on pp.counter_id=pos.counter_id and pp.branch_id=pos.branch_id and pp.pos_id=pos.id and pp.date=pos.date
		where pos.cancel_status=0 and pp.type='VOUCHER' and pp.remark like ".ms($_REQUEST['voucher_no'].'%');
	
	$q_pos = $con->sql_query($sql_pos) or die('offline');
	if ($con->sql_numrows($q_pos)>0)	
		print "used";
	else
		print "ok";
}

function getSalesByReceiptNo()
{
	global $con;
	$ret = false;
	$receipt_no = $_REQUEST['receipt_no'];
	$counter_code = $_REQUEST['network_name'];
	$branch_code = $_REQUEST['branch_code'];
	$date = $_REQUEST['date'];
	
	$con->sql_query("select pos.* from pos join branch on pos.branch_id=branch.id join counter_settings on pos.counter_id = counter_settings.id where pos.receipt_no=".ms($receipt_no)." and pos.date=".ms($date)." and branch.code=".ms($branch_code)." and counter_settings.network_name=".ms($counter_code));
	
	if($con->sql_numrows()>0)
	{
		$ret = $con->sql_fetchassoc();
	}
	$con->sql_freeresult();
	if($ret){
		print serialize($ret);
	}
	else{
		print $ret;
	}
}

function search_sales_order($by_id=false)
{
	global $con;
	$ret = false;
	
	$branch = $_REQUEST['branch'];

	if($by_id){
	  $id=$_REQUEST['id'];
	  $where=" and sales_order.id=".ms($id);
	}
	else{
	  $order_no = $_REQUEST['order_no'];
	  $where=" and sales_order.order_no=".ms($order_no);
	}

	$sql = "select sales_order.* from sales_order join branch on sales_order.branch_id=branch.id
			where branch.code=".ms($branch).$where."
			and sales_order.active=1 and sales_order.status=1 and sales_order.approved=1";
	
	$query_id = $con->sql_query($sql);
	if($con->sql_numrows()>0)
	{
		$r = $con->sql_fetchrow($query_id);
		
		$ret['sales_order_detail'] = $r;
		$sql = "select soi.*,uom.fraction,si.bom_type from sales_order_items as soi join sku_items as si on soi.sku_item_id=si.id join uom on uom.id=soi.uom_id where soi.branch_id=".$r['branch_id']." and soi.sales_order_id=".$r['id'];
		$query_id1 = $con->sql_query($sql);
		while($r1 = $con->sql_fetchrow($query_id1))
		{
			$ret['items'][] = $r1;
		}
		unset($r,$r1);
		
	}
	else{
		$ret['error_message'] = "Cannot found this ".$_REQUEST['order_no']." sales order no.";
	}
	$con->sql_freeresult();	
	print serialize($ret);
}

function update_sales_order()
{
	global $con;
	$branch = $_REQUEST['branch'];
	$id = $_REQUEST['order_id'];
	$sql = "select exported_to_pos from sales_order where branch_id=".ms($branch)." and id=".ms($id)." and exported_to_pos=1";
	$con->sql_query($sql);
	if($con->sql_numrows()>0)
	{
		print "Success";
	}
	else
	{
		$sql = "update sales_order set exported_to_pos=1 where branch_id=".ms($branch)." and id=".ms($id);
		
		$con->sql_query($sql);
		
		if($con->sql_affectedrows()<=0 || !$con->sql_affectedrows())
		{
			print "Failed";
		}
		else
		{
			print "Success";
		}
	}
}

function get_sn_info_gr()
{
	global $con;
	$pos_id = $_REQUEST['pos_id'];
	$sku_item_id = $_REQUEST['sku_item_id'];
	$date = $_REQUEST['date'];
	$branch = $_REQUEST['branch'];
	$counter = $_REQUEST['counter'];
	$serial_no = $_REQUEST['serial_no'];
	//$sql = "select * from sn_info  where pos_id = ".ms($pos_id)." and sku_item_id = ".ms($sku_item_id)." and date = ".ms($date)." and branch_id = ".ms($branch)." and counter_id = ".ms($counter)." and serial_no = ".ms($serial_no);
	$sql = "select sn.* from sn_info sn left join pos p on p.id = sn.pos_id and p.branch_id = sn.branch_id and p.date = sn.date and p.counter_id = sn.counter_id  where p.cancel_status=0 and sn.pos_id = ".ms($pos_id)." and sn.sku_item_id = ".ms($sku_item_id)." and sn.date = ".ms($date)." and sn.branch_id = ".ms($branch)." and sn.counter_id = ".ms($counter)." and sn.serial_no = ".ms($serial_no)." order by p.end_time desc limit 1";
	
	$con->sql_query($sql);
	if($con->sql_numrows()>0)
	{
		$ret = $con->sql_fetchrow();		
		if($ret['active']==0){
			$ret = array("error_message"=>"This $serial_no item was return.");
		}
	}
	else{
		$ret['error_message'] = "Cannot find serial no from SN info sold list.";
	}
	
	$con->sql_freeresult();
	
	print serialize($ret);
}

function get_membership_detail()
{
	global $con;
	$nric = $_REQUEST['nric'];
	$card_no = $_REQUEST['card_no'];
	$sql = "select address,postcode,city,state, phone_1 as home_p, phone_2 as office_p, phone_3 as mobile_p, email from membership where nric=".ms($nric)." and card_no=".ms($card_no);
	$con->sql_query($sql);
	if($con->sql_numrows()>0)
	{
		$ret = $con->sql_fetchrow();
	}else{
		$ret['error_message'] = "Cannot found member detail for this $card_no.";
	}

	$con->sql_freeresult();
	print serialize($ret);
}

function weight_scale_code($ps_setting,$code)
{			
	if(isset($ps_setting['barcode_unit_code_prefix']) && preg_match("/^".$ps_setting['barcode_unit_code_prefix']."/", $code, $unit_matches))
	{			
		return true;		
	}
	elseif(isset($ps_setting['barcode_price_code_prefix']) && preg_match("/^".$ps_setting['barcode_price_code_prefix']."/", $code, $price_matches))
	{			
		return true;		
	}	
	elseif(isset($ps_setting['barcode_price_n_unit_code_prefix']) && preg_match("/^".$ps_setting['barcode_price_n_unit_code_prefix']."/", $code, $ucode_matches))
	{			
		return true;		
	}	
	elseif(isset($ps_setting['barcode_total_price_n_unit_code_prefix']) && preg_match("/^".$ps_setting['barcode_total_price_n_unit_code_prefix']."/", $code, $price_ucode_matches))
	{			
		return true;		
	}
	else{		
		return false;
	}
}

function get_vendor_list()
{
	global $con;
	$ret = array();
	$search_text = $_REQUEST['search_text'];
	$con->sql_query("select id, code,description from vendor where id=".ms($search_text)." or code = ".ms($search_text)." or description like ".ms("$search_text%")." or description like ".ms("% $search_text%")." limit 50");
	if($con->sql_numrows()>0)
	{
		while($r=$con->sql_fetchrow())
		{
			$ret[] = $r;
		}
	}
	else{
		$ret['error'] = "Cannot found '$search_text' vendor.";
	}
	$con->sql_numrows();
	
	print serialize($ret);
	
}

function search_doc_detail()
{
	global $con;
	$ret = false;
	$type = $_REQUEST['type'];
	$doc_no = $_REQUEST['doc_no'];
	
	switch($type)
	{
		case "PO":
			$query = "select po.department_id, po.vendor_id, v.code, v.description from po left join vendor v on v.id = po.vendor_id where po.po_no = ".ms($doc_no);
			break;
		case "DO":
			$query = "select do.dept_id as department_id from do where do_no = ".ms($doc_no);
			break;
	}
	
	$con->sql_query($query);
	if($con->sql_numrows()>0)
	{
		$ret = $con->sql_fetchrow();
	}
	else{
		$ret['error'] = sprintf("No record found for %s document[%s]",$type,$doc_no);
	}
	
	print serialize($ret);
}

function get_branch_list(){
	global $con;
	
	$branch_list = array();
	$con->sql_query("select id,code,description,counter_limit,active,added from branch order by id");
	while($r = $con->sql_fetchassoc()){
		$branch_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	$ret = array();
	$ret['ok'] = 1;
	$ret['branch_list'] = $branch_list;
	print serialize($ret);
	exit;
}

function get_server_list(){
	include_once('xtra/server_list.php');
	
	$ret = array();
	
	if($server_list){
		$ret['ok'] = 1;
		$ret['server_list'] = $server_list;
	}
	print serialize($ret);
	exit;
}

function update_counter_sync_log()
{
	global $con;

	$branch_id = intval($_REQUEST["branch_id"]);
	$counter_id = intval($_REQUEST["counter_id"]);
	$sync_error = trim($_REQUEST["sync_error"]);

	$con->sql_query("select sync_error from counter_status where branch_id=$branch_id and id=$counter_id");
	$data = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if($data['sync_error'] == $sync_error)	exit;	// nothing to update

	$con->sql_query("update counter_status set sync_error=".ms($sync_error)." where branch_id=$branch_id and id=$counter_id");	
}

function check_server_pos_deposit_status_last_update(){
	global $con;

	$branch_id=$_REQUEST["branch_id"];
	$counter_id=$_REQUEST["counter_id"];
	$date=$_REQUEST["date"];
	$receipt_no=$_REQUEST["receipt_no"];
	$pos_id=$_REQUEST["pos_id"];

	$sql = "select last_update from pos_deposit_status
	where deposit_branch_id=".ms($branch_id)."
	and deposit_counter_id=".ms($counter_id)."
	and deposit_date=".ms($date)."
	and deposit_receipt_no=".ms($receipt_no)."
	and deposit_pos_id = ".ms($pos_id);
	$con->sql_query($sql);
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();

	print serialize($tmp);
	exit;
}

function upload_error(){
	global $con;

	$branch_id = intval($_REQUEST["branch_id"]);
	$counter_id = intval($_REQUEST["counter_id"]);
	$date = trim($_REQUEST["date"]);
	$err = trim($_REQUEST['err']);
	
	if(trim($err)!="" && preg_match('/Missing Record \(\d+\)./i', $err))
	{
		$err = "";
	}
	
	$con->sql_query("select * from pos_counter_collection_tracking where branch_id=$branch_id and counter_id=$counter_id and date=".ms($date));
	$data = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$data && !$err)	exit;	// No Data and No Error, nothing to do
	
	if($data['error'] == $err){	// Same Error
		exit;
	}
	
	if($err){
		$upd = array();
		$upd['error'] = $err;
		$upd['branch_id'] = $branch_id;
		$upd['counter_id'] = $counter_id;
		$upd['date'] = $date;
		$upd['finalized'] = 1;
		$con->sql_query("replace into pos_counter_collection_tracking ".mysql_insert_by_field($upd));
	}else{
		$con->sql_query("delete from pos_counter_collection_tracking where branch_id = ".ms($branch_id)." and counter_id = ".ms($counter_id)." and date = ".ms($date));
	}
	
	/*$con->sql_query("select * from pos_counter_collection_tracking
				  where branch_id = ".ms($branch_id)."
				  and counter_id = ".ms($counter_id)."
				  and date = ".ms($date)."
				  and finalized = 1");
	if($err!='' && $con->sql_numrows()<=0) {
		$upd['error'] = $err;
		$upd['branch_id'] = $branch_id;
		$upd['counter_id'] = $counter_id;
		$upd['date'] = $date;
		$upd['finalized'] = 1;
		$con->sql_query("insert into pos_counter_collection_tracking ".mysql_insert_by_field($upd)) or die(mysql_error());
		unset($upd);
	}
	else{
		$con->sql_query("update pos_counter_collection_tracking set error = ".ms($err)."
						where branch_id = ".ms($branch_id)."
						and counter_id = ".ms($counter_id)."
						and date = ".ms($date)."
						and finalized = 1") or die(mysql_error());
	}*/

}

function update_counter_tmp_log(){
  global $con;

  $branch_id=$_REQUEST["branch_id"];
  $counter_id=$_REQUEST["counter_id"];
  $tmp_trigger_log_row=$_REQUEST["tmp_trigger_log_row"];
  $tmp_member_trigger_log_row=$_REQUEST["tmp_member_trigger_log_row"];

  $con->sql_query("update counter_status set min_tmp_trigger_log_row=$tmp_trigger_log_row, min_tmp_member_trigger_log_row=$tmp_member_trigger_log_row
					  where branch_id=".ms($branch_id)." and id=".ms($counter_id));
}

function get_pos_id_from_server(){
  global $con;

  $branch_id=$_REQUEST["branch_id"];
  $counter_id=$_REQUEST["counter_id"];
  $date=$_REQUEST['date'];
  $receipt_no=$_REQUEST['receipt_no'];

  $con->sql_query("select id from pos
				  where branch_id=".ms($branch_id)."
				  and counter_id=".ms($counter_id)."
				  and date=".ms($date)."
				  and receipt_no=".ms($receipt_no)." limit 1");
  $tmp = $con->sql_fetchassoc();
  $con->sql_freeresult();

  print serialize($tmp);
  exit;
}

function update_pos_counter_collection_tracking(){
  global $con;

  $branch_id=$_REQUEST["branch_id"];
  $counter_id=$_REQUEST["counter_id"];
  $error=$_REQUEST["error"];

  $con->sql_query("update pos_counter_collection_tracking set error=".ms($error)." where branch_id=".ms($branch_id)." and counter_id=".ms($counter_id));
}

function get_pos_finalized(){
  global $con;

  $branch_id=$_REQUEST["branch_id"];
  $date=$_REQUEST["date"];

  $con->sql_query("select * from pos_finalized where branch_id = ".ms($branch_id)." and date = ".ms($date)." and finalized = 1");

  $tmp = $con->sql_numrows();

  print $tmp;
  exit;
}

function check_so_exported_to_pos(){
  global $con;

  $ret = search_sales_order(true);

  $so = unserialize($ret);

  if(isset($so['sales_order_detail']['exported_to_pos']) && $so['sales_order_detail']['exported_to_pos']) $result=1;
  else $result=0;

  print $result;
  exit;
}

function get_db_backup_list(){
	print "<pre>";
	passthru("ls -ltrhG /backup/*; | grep sql.gz");
	print "</pre>";
}

function get_counter_list(){
	global $con;
	
	$counter_list = array();
	$con->sql_query("select id,branch_id, network_name,location,active,membership_settings, pos_settings from counter_settings order by id");
	while($r = $con->sql_fetchassoc()){
		$counter_list[$r['id']] = $r;
		
		
	}
	$con->sql_freeresult();
	
	$ret = array();
	$ret['ok'] = 1;
	$ret['counter_list'] = $counter_list;
	print serialize($ret);
	exit;
}

function toggle_customer_status(){
	global $con;
	
	$file_name = 'customer_terminate.txt';
	if($_REQUEST['is_terminate']){ // create a file under www folder to set this customer being terminated
		if(file_exists($file_name)) unlink($file_name);
		
		$contents['error_msg'] = "Account Suspended, please contact your Account Manager for assistance.";
		$content = serialize($contents);
		
		// Write the contents back to the file
		file_put_contents($file_name, $content);
		print 1;
	}else{ // remove the file by set this customer to activated
		if(file_exists($file_name)){
			unlink($file_name);
			print 2;
		}
	}
}

function get_config_list(){
	global $config, $con;
	
	$config_list = array();
	$config_list['allow_coupon_voucher'] = 0;
	$config_list['allow_membership'] = 0;
	$config_list['allow_sales_order'] = $config['allow_sales_order'];
	$config_list['masterfile_enable_sa'] = $config['masterfile_enable_sa'];
	$config_list['enable_po_agreement'] = $config['enable_po_agreement'];
	
	// load config
	$con->sql_query("select config_name, active, value from config_master where active=1 and config_name in ('allow_sales_order', 'masterfile_enable_sa', 'enable_po_agreement')");
	while($r = $con->sql_fetchassoc()){
		$config_list[$r['config_name']] = $r['value'];
	}
	$con->sql_freeresult();
	
	// load privilege for membership
	$con->sql_query("select * from privilege_master where active=1 and privilege_group in ('MEMBERSHIP', 'MASTERFILE_RETAIL')");
	
	while($r = $con->sql_fetchassoc()){
		if($r['privilege_group'] == "MEMBERSHIP") $config_list['allow_membership'] = 1;
		elseif($r['privilege_group'] == "MASTERFILE_RETAIL") $config_list['allow_coupon_voucher'] = 1;
	}
	$con->sql_freeresult();
	
	$ret = array();
	$ret['ok'] = 1;
	$ret['config_list'] = $config_list;
	print serialize($ret);
	exit;
}

function get_member_purchase_history()
{
	global $con;
	$member_nric = $_REQUEST['member'];
	$limit = $_REQUEST['limit'];
	$date_from = $_REQUEST['date_from'];
	$date_to = $_REQUEST['date_to'];
	$con->sql_query("select card_no from membership_history where nric = ".ms($member_nric)." group by card_no");	
	if($con->sql_numrows()>0)
	{
		while($r = $con->sql_fetchrow())
		{
			$card_no[ms($r['card_no'])] = 1;
		}
		$con->sql_freeresult();
		
		$where = "where p.cancel_status = 0 and p.prune_status=0 and p.member_no in (".implode(",",array_keys($card_no)).") and p.date between ".ms($date_from)." and ".ms($date_to);
		
		$sql = "select count(*) as total from pos p join pos_items pi on 
				p.id=pi.pos_id and 
				p.branch_id = pi.branch_id and
				p.counter_id = pi.counter_id and
				p.date = pi.date
				$where order by p.start_time desc";		

		$con->sql_query($sql);
		$result = $con->sql_fetchrow();
		$ret['total'] = $result['total'];
		$con->sql_freeresult();
		
		$sql = "select pi.*, p.start_time as date_time,p.receipt_no,p.date from pos p join pos_items pi on 
				p.id=pi.pos_id and 
				p.branch_id = pi.branch_id and
				p.counter_id = pi.counter_id and
				p.date = pi.date $where order by p.start_time desc";
		
		$con->sql_query($sql);
		
		if($con->sql_numrows()>0)
		{	
			$t = 0;		
			while($r = $con->sql_fetchrow())
			{
				if($t>$limit) break;
				$r['total_price'] = round(($r['price']-$r['discount']),2);
				$r['unit_price'] = round((($r['price']-$r['discount'])/$r['qty']),2);		
				$r['receipt_description'] = $r['sku_description'];			
				$ret['history'][] = $r;
				$t++;
			}	
		}
		else{
			$ret['total_records'] = 0;
		}
		$con->sql_freeresult();	
	}
	
	$con->sql_freeresult();	
	print serialize($ret);
	
}

function update_pos_finalize_error()
{
	global $con;
	$upd['branch_id'] = $_REQUEST['branch_id'];
	$upd['counter_id'] = $_REQUEST['counter_id'];
	$upd['error_msg'] = $_REQUEST['err_message'];
	$upd['date'] = $_REQUEST['date'];
	$upd['added'] = date("Y-m-d H:i:s");
	$con->sql_query("replace into pos_finalised_error ".mysql_insert_by_field($upd));
	unset($upd);
	if($con->sql_affectedrows()<=0)
	{
		print "Failed.";
	}
	else
	{
		print "Success.";
	}
}

function update_item_sn()
{
	global $con;
	$con->sql_query("update pos_items_sn set pos_item_id=".ms($_REQUEST['pos_item_id']).", pos_id = ".ms($_REQUEST['pos_id']).", pos_branch_id = ".ms($_REQUEST['pos_branch_id']).", date=".ms($_REQUEST['date']).", counter_id=".ms($_REQUEST['counter_id']).", status = ".ms($_REQUEST['status']).", last_update = CURRENT_TIMESTAMP where located_branch_id = ".ms($_REQUEST['located_branch_id'])." and sku_item_id = ".ms($_REQUEST['sku_item_id'])." and serial_no = ".ms(urldecode($_REQUEST['serial_no'])));
	if($con->sql_affectedrows()<=0)
	{
		print "Failed.";
	}
	else
	{
		print "Success.";
	}
}

function update_counter_sales_record()
{
	global $con;
	$con->sql_query("select * from pos_transaction_counter_sales_record  
					where branch_id = ".ms($_REQUEST['branch_id']).
					" and counter_id = ".ms($_REQUEST['counter_id']).
					" and date = ".ms($_REQUEST['date']).
					" and tablename = ".ms($_REQUEST['tablename']));
	if($con->sql_numrows()>0)
	{
		$r = $con->sql_fetchrow();
		$con->sql_freeresult();
		if(isset($_REQUEST['missing']))
		{
			$con->sql_query("update pos_transaction_counter_sales_record  set total_record=".ms($_REQUEST['total_records']).", synced_record=".ms($_REQUEST['synced']).",lastupdate=".ms(date("Y-m-d H:i:s")).",missing_record=".$_REQUEST['missing']." where branch_id = ".ms($_REQUEST['branch_id'])." and counter_id = ".ms($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and tablename = ".ms($_REQUEST['tablename']));
		}
		else{
			$con->sql_query("update pos_transaction_counter_sales_record  set total_record=".ms($_REQUEST['total_records']).", synced_record=".ms($_REQUEST['synced']).",lastupdate=".ms(date("Y-m-d H:i:s")).",missing_record=0 where branch_id = ".ms($_REQUEST['branch_id'])." and counter_id = ".ms($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and tablename = ".ms($_REQUEST['tablename']));
		}
		
		if($con->sql_affectedrows()<=0)
		{
			print "Failed.";
		}
		else
		{
			print "Success.";
		}
	}
	else{
		$con->sql_freeresult();
		$upd['branch_id'] = $_REQUEST['branch_id']; 
		$upd['counter_id'] = $_REQUEST['counter_id']; 
		$upd['date'] = $_REQUEST['date'];
		$upd['tablename'] = $_REQUEST['tablename'];
		$upd['total_record'] = $_REQUEST['total_records'];		
		$upd['synced_record'] = $_REQUEST['synced'];
		$upd['missing_record'] = isset($_REQUEST['missing'])?$_REQUEST['missing_record']:0;
		$upd['added'] = date("Y-m-d H:i:s");
		$upd['lastupdate'] = date("Y-m-d H:i:s");
		$con->sql_query("insert into pos_transaction_counter_sales_record ".mysql_insert_by_field($upd));
		unset($upd);
		if($con->sql_affectedrows()<=0)
		{
			print "Failed.";
		}
		else
		{
			print "Success.";
		}
	}
}

function get_total_sales_record()
{
	//$ret = array();
	global $con;
	if($_REQUEST['tablename']=='pos_deposit_status_history')
		$con->sql_query("select count(*) as total from ".$_REQUEST['tablename']." where branch_id = ".ms($_REQUEST['branch_id'])." and counter_id = ".ms($_REQUEST['counter_id'])." and pos_date = ".ms($_REQUEST['date']));
	elseif($_REQUEST['tablename']=='pos_deposit_status')
		$con->sql_query("select count(*) as total from ".$_REQUEST['tablename']." where deposit_branch_id = ".ms($_REQUEST['branch_id'])." and deposit_counter_id = ".ms($_REQUEST['counter_id'])." and deposit_date = ".ms($_REQUEST['date']));
	else
		$con->sql_query("select count(*) as total from ".$_REQUEST['tablename']." where branch_id = ".ms($_REQUEST['branch_id'])." and counter_id = ".ms($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date']));
	if($con->sql_numrows()>0)
	{
		$ret = $con->sql_fetchassoc();
	}
	$con->sql_freeresult();
	
	print serialize($ret);
}

function get_counter_by_branch()
{
	global $con;
	$ret = $con->sql_query("select * from counter_settings where branch_id=".ms($_REQUEST['branch_id'])." order by network_name");
	if($con->sql_numrows($ret)>0)
	{
		while($r = $con->sql_fetchassoc($ret))
		{
			$counter_list[$r['id']] = $r;	
		}
	}
	$con->sql_freeresult($ret);
	
	$ret = array();
	$ret['ok'] = 1;
	$ret['counter_list'] = isset($counter_list)?$counter_list:"";
	print serialize($ret);
	exit;
}

function get_counter_status()
{
	global $con;
	$is_exist = 0;
	$ret = $con->sql_query("select * from counter_status where branch_id = ".ms($_REQUEST['branch_id'])." and id=".ms($_REQUEST['counter_id']));
	if($con->sql_numrows($ret)>0)
	{
		$is_exist = 1;
	}
	$con->sql_freeresult($ret);
	
	print $is_exist;
	exit;
}

function get_max_receipt_no(){
	global $con;
	
	$ret = $filters = array();
	$filters[] = "receipt_no is not null and receipt_no != ''";
	$filters[] = "branch_id = ".ms($_REQUEST['branch_id']);
	$filters[] = "counter_id = ".ms($_REQUEST['counter_id']);
	$filters[] = "date = ".ms($_REQUEST['date']);
	$q1 = $con->sql_query("select max(receipt_no) as rn from pos where ".join(" and ", $filters)." order by receipt_no desc limit 1");
	
	if($con->sql_numrows($q1) > 0){
		$ret = $con->sql_fetchassoc($q1);
	}
	$con->sql_freeresult($q1);
	unset($filters);
	
	print serialize($ret);
}

function get_configs(){
	global $config;
	
	$cf_list = array(
		'sku_multiple_selling_price',
		'sku_multiple_quantity_price',
		'coupon_use_percentage',
		'coupon_amount_0_5_cent',
		'membership_use_card_prefix',
		'enable_sn_bn',
		'masterfile_enable_sa',
		'masterfile_sa_code_prefix',
		'membership_enable_staff_card',
		'currency_settings',
		'sku_enable_additional_description',
		'sku_multiple_quantity_mprice',
		'user_profile_show_item_discount_only_allow_percent',
		'mix_and_match_show_prompt_available',
		'tpproperty_setting',
		'receipt_running_no',
		'use_grn_future',
		'grn_group_same_item',
		'do_skip_generate_grn',
		'sku_weight_code_length',
		'no_banner_in_counter',
		'open_disc_entered_disc_amt',
		'membership_type',
		'membership_staff_type',
		'cash_domination_notes',
		'arms_currency',
		'se_relief_claus_remark',
		'pos_cash_advance_reason_list',
		'foreign_currency',
		'foreign_currency_decimal_points',
		'speed99_settings',
		'pos_day_start_pw',
		'pos_day_end_pw',
		'link_code_name',
		'membership_pmr',
		'membership_pmr_name',
		'invoice_date_conversion_anchor',
		'receipt_cashier_display',
		'pos_settings_pos_backend_tab',
		'pos_multiple_login_with_same_user'
	);
	
	$data = array();
	foreach($cf_list as $v){
		if(isset($config[$v]))	$data[$v] = $config[$v];
	}
	//file_put_contents($this->counter_config_file, serialize($data));
	//chmod($this->counter_config_file, 0777);
	print serialize($data);
}

function get_price_change_sku_list(){
	global $con;
	
	$bid = mi($_REQUEST['branch_id']);
	$date = trim($_REQUEST['date']);
	
	if($bid <= 0)	die("Invalid Branch ID");
	if(!$date || date("Y", strtotime($date))<2000)	die("Invalid Date");
	
	// Create Folder
	check_and_create_dir("attch/speed99");
	check_and_create_dir("attch/speed99/price_change");
	
	$branch_folder = "attch/speed99/price_change/".$bid;
	check_and_create_dir($branch_folder);
	
	// Create temporary file
	$tmp_file_path = tempnam("/tmp", "sp9").".csv";
	$f = fopen($tmp_file_path, 'w');
	
	// Header
	$row = array("SKU ITEM ID", "Old Price", "New Price");
	fputcsv_eol($f, $row);
	
	$filter = array();
	$filter[] = "siph.branch_id=$bid";
	$filter[] = "siph.added between ".ms($date)." and ".ms($date." 23:59:59");
	$str_filter = "where ".join(' and ', $filter);
	
	$sql = "select siph.sku_item_id, siph.price, siph.added, si.selling_price as master_price
		from sku_items_price_history siph
		join sku_items si on si.id=siph.sku_item_id
		$str_filter";
	$q1 = $con->sql_query($sql);
	while($r = $con->sql_fetchassoc($q1)){
		// Get Old Price
		$con->sql_query("select price 
			from sku_items_price_history 
			where branch_id=$bid and added<".ms($date)." and sku_item_id=".mi($r['sku_item_id'])."
			order by added desc limit 1");
		$old_data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$old_price = $old_data ? $old_data['price'] : $r['master_price'];
		
		// Write row into file
		$row = array($r['sku_item_id'], round($old_price, 2), round($r['price'], 2));
		fputcsv_eol($f, $row);
	}
	$con->sql_freeresult($q1);
	fclose($f);
	
	// Rename file
	$final_filename = $branch_folder."/".$date.".csv";
	rename($tmp_file_path, $final_filename);
	
	// return the file content to api
	print file_get_contents($final_filename);
}

function get_time_attendance_list(){
	global $con;
	$bid = mi($_REQUEST['branch_id']);
	$counter_id = mi($_REQUEST['counter_id']);
	$date = trim($_REQUEST['date']);
	
	if($bid <= 0)	die("Invalid Branch ID");
	if(!$date || date("Y", strtotime($date))<2000)	die("Invalid Date");

	$ret = array();
	$sql = "select user_id, scan_time from attendance_user_scan_record where branch_id = $bid and counter_id = $counter_id and date = ".ms($date);
	$rs = $con->sql_query($sql);
	while($r = $con->sql_fetchrow($rs)){
		$ret[$r['user_id']][] = $r['scan_time'];
	}
	$con->sql_freeresult($rs);

	print serialize($ret);
}

function check_begin_of_day_status(){
	global $con;
	$bid = mi($_REQUEST['branch_id']);
	$counter_id = mi($_REQUEST['counter_id']);
	$date = trim($_REQUEST['date']);
	
	if($bid <= 0)	die("Invalid Branch ID");
	if(!$date || date("Y", strtotime($date))<2000)	die("Invalid Date");

	$sql = "select * from pos_day_start where branch_id = $bid and counter_id = $counter_id and date = ".ms($date);
	$rs = $con->sql_query($sql);
	if($con->sql_numrows($rs) > 0){
		$proceed_login = 1;
	}else{
		$proceed_login = 0;
	}
	$con->sql_freeresult($rs);

	print $proceed_login;
}

function check_end_of_day_status(){
	global $con;
	$bid = mi($_REQUEST['branch_id']);
	$counter_id = mi($_REQUEST['counter_id']);
	$date = trim($_REQUEST['date']);
	
	if($bid <= 0)	die("Invalid Branch ID");
	if(!$date || date("Y", strtotime($date))<2000)	die("Invalid Date");

	$sql = "select * from pos_day_end where branch_id = $bid and counter_id = $counter_id and date = ".ms($date);
	$rs = $con->sql_query($sql);
	if($con->sql_numrows($rs) > 0){
		$is_eod = 1;
	}else{
		$is_eod = 0;
	}
	$con->sql_freeresult($rs);

	print $is_eod;
}

function check_nonpb_counters_status(){
	global $con;
	$bid = mi($_REQUEST['branch_id']);
	$pb_counter_id = mi($_REQUEST['pb_counter_id']);
	
	if($bid <= 0)	die("Invalid Branch ID");
	$now = time();
	$counter_status = array();
	$sql = "select s.id,s.status,c.network_name,s.lastping from counter_status s join counter_settings c on (s.id = c.id and s.branch_id = c.branch_id) where c.active = 1 and s.branch_id = $bid and s.id != $pb_counter_id";
	$rs = $con->sql_query($sql);
	while($rw = $con->sql_fetchrow($rs)){
		if($now - strtotime($rw['lastping']) > 3600){
			$counter_status[$rw['id']] = 'Counter ('.$rw['network_name'].') is not online for more than 30 minutes. Last status: "'.strtoupper($rw['status']).'"" at '.$rw['lastping'].'.';
		}elseif($rw['status'] == 'login' || $rw['status'] == 'lock'){
			$counter_status[$rw['id']] = 'Counter ('.$rw['network_name'].') is not yet logout.';
		}
	}
	$con->sql_freeresult($rs);

	print serialize($counter_status);
}

function get_items_cost(){
	global $con;
	$bid = mi($_REQUEST['branch_id']);
	if($bid <= 0)	die("Invalid Branch ID");
	$sku_item_ids = json_decode($_REQUEST['sku_item_ids'],true);
	
	$ret = array();
	foreach($sku_item_ids as $sku_item_id){
		$con->sql_query("select grn_cost from sku_items_cost where branch_id = $bid and sku_item_id = $sku_item_id order by last_update desc limit 1");
		$r = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if($r){
			$ret[$sku_item_id] = $r['grn_cost'];
		}else{
			$con->sql_query("select cost_price from sku_items where id = $sku_item_id");
			$r = $con->sql_fetchassoc();
			$con->sql_freeresult();
			$ret[$sku_item_id] = $r['cost_price'];
		}
	}
	
	print serialize($ret);
}

function receive_file_from_counter(){
	$bid = mi($_REQUEST['branch_id']);
	if($bid <= 0)	die("Invalid Branch ID");
	
	$ret = false;
	$type = $_REQUEST['type'];
	$filename = $_REQUEST['filename'];
	if($type == 'densotrn'){
		$date = $_REQUEST['date'];
		$y = date('Y',strtotime($date));
		$m = date('m',strtotime($date));
		$d = date('d',strtotime($date));

		$folder = 'attch';
		check_and_create_dir($folder);
		$folder .= '/speed99';
		check_and_create_dir($folder);
		$folder .= '/eod';
		check_and_create_dir($folder);
		$folder .= '/densotrn';
		check_and_create_dir($folder);
		$folder .= '/'.$bid;
		check_and_create_dir($folder);
		$folder .= '/'.$y;
		check_and_create_dir($folder);
		$folder .= '/'.$m;
		check_and_create_dir($folder);
		$folder .= '/'.$d;
		check_and_create_dir($folder);
		$fullpath = $folder.'/'.$filename;

		if(file_exists($fullpath)) unlink($fullpath);

		$str = $_REQUEST['str'];
		$str = gzinflate(gzinflate($str));
		$ret = file_put_contents($fullpath,$str);
	}
	
	if($ret === false){
		print 0;
	}else{
		print 1;
	}
}
?>
