<?php
/*
1/6/2017 5:12 PM Andy
- Make config.masterfile_disable_auto_explode_artno default on.

2/7/2017 10:28 AM Andy
- Add define ARMS_SKU_CODE_PREFIX.

4/25/2017 3:44 PM Andy
- Add default config "arms_currency" and "cash_domination_notes".

8/14/2017 2:16 PM Andy
- Change master password.

9/11/2017 2:20 PM Andy
- Bug fix on "global_cost_decimal_points" and "global_qty_decimal_points" missing in default config.

9/22/2017 10:08 AM Andy
- Change master password.

10/31/2017 4:11 PM Justin
- Enhanced to have default value for config "se_relief_claus_remark".

11/2/2017 5:41 PM Andy
- Added default config "pos_cash_advance_reason_list".

11:21 AM 2/1/2018 Justin
- Added default config "global_weight_decimal_points".

2/23/2018 10:05 AM Andy
- Change master password.

6/4/2018 2:26 PM Justin
- Added default config "sku_consign_selling_deduct_discount_as_cost".

6/8/2018 4:03 PM Justin
- Added default config "foreign_currency_decimal_points".

8/27/2018 11:51 AM Andy
- Added default config "arms_tax_settings".

10/4/2018 1:50 PM Justin
- Added default config "grr_tax_amt_var_percent".

10/22/2018 10:03 AM Andy
- Change master password.

10/23/2018 3:19 PM Andy
- Fixed php Undefined index warning message.

12/26/2018 05:04 PM Justin
- Added default config "print_document_barcode".

2/12/2019 2:39 PM Andy
- Added default config "arms_pic_email".

2/25/2019 11:44 AM Andy
- Change Quick SQL to check the ID in default config.
- Change master password.

3/8/2019 4:34 PM Andy
- Added default config "ewallet_list", "ewallet_arms_setting" and "pp_group_type_list".

3/21/2019 3:09 PM Andy
- Added default config "do_request_show_sku_photo".

4/4/2019 1:38 PM Justin
- Added new eWallet type: Paydibs.
- Modified eWallet default config to include paydibs info.

10/1/2019 10:51 AM Andy
- Added default config "membership_mobile_new_register_expiry_duration_year".

10/25/2019 11:37 AM Andy
- Added default config "marketplace_sku_mprice_type".
- Changed master password.
- Changed Quick SQL password.

10/25/2019 5:58 PM William
- Added default config "membership_state_settings".

10/30/2019 3:30 PM William
- Added default config "reserve_login_id".

12/2/2019 9:37 AM Andy
- Fixed to remove config "consignment_modules" undefine warning.

5/18/2020 7:04 PM Andy
- Changed master password.
- Changed Quick SQL password.

10/15/2020 3:38 PM Andy
- Added default config "po_sticky_column".

10/22/2020 5:51 PM Andy
- Added default config "adj_print_col_list".

12/14/2020 5:06 PM William
- Added default config "do_print_col_list".

1/25/2020 11:37 AM William
- Added default config "membership_pmr_name".

3/10/2021 3:46 PM William
- Added new "ipay88" ewallet list.
*/
$config['doc_reset_level'] = 9999;
$config['dat_format'] = '%d/%m/%Y';

if(!isset($config['link_code_name']))	$config['link_code_name'] = 'Old Code';
if(!isset($config['membership_cardname']))	$config['membership_cardname'] = 'ARMS';
if(!isset($config['scanned_ic_path']))	$config['scanned_ic_path'] = "icfiles.save/";

//if (!defined('MASTER_PASSWORD')) 
define('MASTER_PASSWORD', 'eq518.ap');
//if (!defined('MASTER_QUIK_UID')) 
define('MASTER_QUIK_UID', 'q2020ne');

if (!defined('MAX_ITEMS_PER_PO')){
	define('MAX_ITEMS_PER_PO', 15);	// look like no place use this
	//die("<h2>Please define MAX_ITEMS_PER_PO in config.php</h2>");
} 
if(!defined('ARMS_SKU_CODE_PREFIX'))	define('ARMS_SKU_CODE_PREFIX', '28%06d');

if(!isset($config['financial_start_date']))	$config['financial_start_date'] = '-01-01';
if(!isset($config['do_default_price_from']))	$config['do_default_price_from'] = 'selling';
if(!isset($config['consignment_modules']) || !$config['consignment_modules']){
	// Not using Consignment
	if(!isset($config['sku_multiple_selling_price'])){
		$config['sku_multiple_selling_price'] = array('member1','member2','member3', 'wholesale1','wholesale2','coldprice','pwp');
	}
}

if(!isset($config['adjustment_type_list']))	$config['adjustment_type_list'] = array (
  array ('name' => 'Debit Adjust'),
  array ('name' => 'Credit Adjust'),
  array ('name' => 'Write Off'),
  array ('name' => 'Own use')
);
if(!isset($config['grr_incomplete_notification']))	$config['grr_incomplete_notification'] = 3;
if(!isset($config['sku_application_enable_variety']))	$config['sku_application_enable_variety'] = 1;
if(!isset($config['membership_length']))	$config['membership_length'] = 20;
if(isset($_SERVER['SERVER_NAME']) && !isset($config['arms_go_module']) && preg_match("/arms-go/", $_SERVER['SERVER_NAME'])) $config['arms_go_module'] = 1;
if(!isset($config['global_cost_decimal_points'])) $config['global_cost_decimal_points'] = 4;
if(!isset($config['global_qty_decimal_points'])) $config['global_qty_decimal_points'] = 3;
if(!isset($config['global_weight_decimal_points'])) $config['global_weight_decimal_points'] = 3;
	
$config['use_grn_future'] = 1;
if(!isset($config['arms_go_module']) || !$config['arms_go_module']) $config['show_server_status'] = 1;
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
$config['po_vendor_ticket_expiry'] = 7;
if(!isset($config['masterfile_disable_auto_explode_artno']))	$config['masterfile_disable_auto_explode_artno'] = 1;
if(!isset($config['sku_consign_selling_deduct_discount_as_cost']))	$config['sku_consign_selling_deduct_discount_as_cost'] = 1;
if(!isset($config['foreign_currency_decimal_points']))	$config['foreign_currency_decimal_points'] = 8;
if(!isset($config['grr_tax_amt_var_percent']))	$config['grr_tax_amt_var_percent'] = 10;
if(!isset($config['arms_currency']))	$config['arms_currency'] = array('code'=>'MYR', 'symbol'=>'RM', 'name'=>'Ringgit Malaysia', 'country'=>'Malaysia','rounding'=>0.05);
if(!isset($config['cash_domination_notes']))	$config['cash_domination_notes'] = array(
 '1 Cts'=>array('value'=>0.01,'label'=>'1 Cts','active'=>1),
 '5 Cts'=>array('value'=>0.05,'label'=>'5 Cts','active'=>1),
 '10 Cts'=>array('value'=>0.10,'label'=>'10 Cts','active'=>1),
 '20 Cts'=>array('value'=>0.20,'label'=>'20 Cts','active'=>1),
 '50 Cts'=>array('value'=>0.50,'label'=>'50 Cts','active'=>1),
 'RM 1'=>array('value'=>1,'label'=>'RM 1','active'=>1),
 'RM 2'=>array('value'=>2,'label'=>'RM 2','active'=>1),
 'RM 5'=>array('value'=>5,'label'=>'RM 5','active'=>1),
 'RM 10'=>array('value'=>10,'label'=>'RM 10','active'=>1),
 'RM 20'=>array('value'=>20,'label'=>'RM 20','active'=>1),
 'RM 50'=>array('value'=>50,'label'=>'RM 50','active'=>1),
 'RM 100'=>array('value'=>100,'label'=>'RM 100','active'=>1)
 );

$config['se_relief_claus_remark'] = "Relieved from Charging GST for supply to a person given relief under item 3, First Schedule of GST (Relief) Order 2014";
if(!isset($config['pos_cash_advance_reason_list']))	$config['pos_cash_advance_reason_list'] = array('Cash Out', 'Pay Bill');

if(!isset($config['arms_tax_settings']))	$config['arms_tax_settings'] = array('name'=>'SST', 'percent'=>10);

if(!isset($config['print_document_barcode']))	$config['print_document_barcode'] = 1;


if(!isset($config['arms_pic_email']))	$config['arms_pic_email'] = 'andy@arms.my';

if(!isset($config['ewallet_list'])){
	// eWallet Default List
	$config['ewallet_list'] = array(
		'boost' => array('desc' => 'Boost'),
		'paydibs' => array('desc' => 'Paydibs'),
		//'barrel2u' => array('desc'=>'Barrel2u'),
		'ipay88' => array('desc' => 'IPay88'),
	);
}

if(!isset($config['ewallet_arms_setting'])){
	// ARMS Default Settings for eWallet
	$config['ewallet_arms_setting'] = array(
		'boost' => array(
			'stage' => array(
				'id' => 'MCM0012845',
				'api_key' => 'CJPHFRD7FPQ6NR86BZY1XC4OYX',
				'api_secret' => '800ca23a-b730-4156-905d-7fddd7746458'
			),
			'production' => array(
				'id' => 'MCM0044443',
				'api_key' => 'FXV3UGROUQMDOOB20VY9HZT6WV',
				'api_secret' => '6ec3542f-4887-4b68-8fbc-ca74f25df3f1'
			)
		),
		'paydibs' => array(
			'stage' => array(
				'id' => 'teh@arms.my',
				'api_key' => 'Tpa3b34Mulw98WW',
				'api_secret' => 'TJ~jZ!4z?yObsDd,vz;oo'
			),
			'production' => array(
				'id' => 'teh@arms.my',
				'api_key' => 'Tpa3b92Mulw89Wq',
				'api_secret' => 'EJ~jZ!4z?yOaee,vz;oo'
			)
		),
	);
}

if(!isset($config['pp_group_type_list'])){
	$config['pp_group_type_list'] = array(
		"standard"=> array(
			"cash", "credit cards", "voucher", "coupon", "check", "cheque", "debit", "credit", "others", "discover", "diners", "amex", "visa", "master"
		),
		"discount"=> array(
			"discount", "mix & match total disc"
		),
		"rounding"=> array(
			"rounding", "currency_adjust"
		),
		"deposit"=> array(
			"deposit"
		),
	);
}

if(!isset($config['do_request_show_sku_photo']))	$config['do_request_show_sku_photo'] = 1;
if(!isset($config['membership_mobile_new_register_expiry_duration_year']))	$config['membership_mobile_new_register_expiry_duration_year'] = 1;

// ARMS Marketplace MPrice
if(!isset($config['marketplace_sku_mprice_type'])){
	$config['marketplace_sku_mprice_type'] = array('lazada_price', 'shopee_price');
}

//membership state settings
if(!isset($config['membership_state_settings'])){
	$config['membership_state_settings'] = array(
		'Johor','Kedah','Kuala Lumpur','Kelantan',
		'Melaka','Negeri Sembilan','Penang',
		'Pahang','Perak','Perlis','Selangor',
		'Terengganu','Sabah','Sarawak'
	);
}

//Username and Login ID reserve
if(!isset($config['reserve_login_id'])){
	$config['reserve_login_id'] = array(
		'arms','admin'
	);
}

// PO Sticky Column
$config['po_sticky_column'] = 1;

// Adjustment Default Print SKU Code
$config['adj_print_col_list'] = array(
	'sku_item_code'=>1,
	'mcode'=>1,
	'artno'=>1
);

// DO Default Print SKU Code
$config['do_print_col_list'] = array(
	'sku_item_code'=>1,
	'mcode'=>1,
	'artno'=>1
);

// membership_pmr_name Default name
$config['membership_pmr_name'] = "Patient Medical Record";
?>