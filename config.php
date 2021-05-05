<?php

	$db_default_connection = array("localhost", "root", "root", "armshq");

	// define constants

	//die($_SERVER['HTTP_HOST']);
	if (!$_SERVER['HTTP_HOST'])
	{

		//cli mode
		define("HQ_IP", "localhost");
		define("HQ_MYSQL", "localhost");
		define('BRANCH_CODE','HQ');
	//		$db_default_connection = array("localhost", "root", "", "arms_cm");
	}
	else
	{
		define("HQ_IP", "localhost");//akadgurun.no-ip.org:5000");
		define("HQ_MYSQL", "localhost");//akadgurun.no-ip.org:5001");

		// testing as HQ
		if ($_SERVER['SERVER_PORT'] == '8888')
		{
//			define("BRANCH_CODE", "GURUN");
			define("BRANCH_CODE", "HQ");
//			$config['consignment_modules'] = 1;
			$config['counter_collection_simple']=true;
		}		
		elseif ($_SERVER['SERVER_PORT'] == '2004')
		{
			define("BRANCH_CODE", "HQ");
			$config['consignment_modules'] = 1;
			$db_default_connection = array("localhost", "root", "", "arms_cm");
			//$config['counter_collection_simple']=false;
//			$db_default_connection = array("localhost", "root", "", "arms_cm");
            $config['do_auto_split_by_price_type'] = 1;
		}
		else
		{
			print "Invalid port";
			exit;
		}
		define("BRANCH_HTTP_PORT", 4000);
	}
	
	/*
	// configuration for single server mode
	switch ($_SERVER['SERVER_PORT'])
	{
		case '80':
		case '2000':
			$this_branch = 'HQ'; break;
		case '2001': $this_branch = 'SP'; break;
		case '2002': $this_branch = 'SPE'; break;
		case '2003': $this_branch = 'SAS'; break;
		case '2004': $this_branch = 'SBGN'; break;
		case '2005': $this_branch = 'SKU'; break;
		case '2006': $this_branch = 'JN'; break;
		case '2007': $this_branch = 'SBM'; break;
		default:
		    die("Invalid connection from $_SERVER[SERVER_PORT]");
	}
	define("BRANCH_CODE", $this_branch);
	define("BRANCH_HTTP_PORT", $_SERVER['SERVER_PORT']);
	*/
	
	//define("VALID_MEMBERSHIP_CARDNO", "/^\d{16,16}$/");
	define('CARD_SCAN_CONTROL_CHAR', ''); //chr(160));
	define('PRINTER_OPEN_DRAWER', chr(27).'p'.chr(0).chr(100).chr(250));
	define('PRINTER_CHAR_DOUBLE_WIDTH', chr(14));
	define('PRINTER_CHAR_REVERSE', chr(20));
	define('PRINTER_CHAR_COMPRESS', chr(15));
	define('PRINTER_CHAR_NORMAL', chr(12));
	define('PRINTER_PORT', "xLPT1");
	define('MAX_ITEMS_PER_PO', 15);
	define('ARMS_SKU_CODE_PREFIX', '28%06d');
	
	date_default_timezone_set("Asia/Kuala_Lumpur");

	//if (!$_SERVER['HTTP_HOST'])
	//	$db_default_connection = array("akadjitra.no-ip.org:4001", "arms_slave", "arms_slave", "armshq");
	//else
	//$db_default_connection = array("citymarthq.no-ip.org:4001", "arms", "4383659", "armshq");
	//$db_default_connection = array("akadhq.no-ip.org:3306", "arms", "793505", "armshq");
	//$db_default_connection = array("smarthq.no-ip.org:4001", "arms", "4383659", "armshq");
	//$db_default_connection = array("hiwaybs.no-ip.org:4001", "arms", "4383659", "armshq");
	//$db_default_connection = array("hq.12shoppkt.com:3306", "arms_pkt", "914381", "arms_pkt");	
	//$db_default_connection = array("cwmhq.no-ip.org:4001", "arms", "sc440", "armshq");

	$MIN_USERNAME_LENGTH = 4;
	$MIN_PASSWORD_LENGTH = 8;
	$MAX_ACTIVE_USER = 10000;
	$SKU_MIN_PHOTO_REQUIRED = 2;

	if (!defined('TERMINAL'))
	{
		$client_ip = $_SERVER['REMOTE_ADDR'];
		if ($smarty)
		{
			$smarty->assign("BRANCH_CODE", BRANCH_CODE);
			$smarty->assign("MIN_USERNAME_LENGTH", $MIN_USERNAME_LENGTH);
			$smarty->assign("MIN_PASSWORD_LENGTH", $MIN_PASSWORD_LENGTH);
		}
		else
		{
			print "<p>Warning: Smarty not initialized</p>";
		}
	}
	else
		$client_ip = $_SERVER['COMPUTERNAME'];
		
	// membership setting
	$config['scanned_ic_path'] = "icfiles.save/";
	/*$config['membership_cardname'] = 'Bonus-Link';
	$config['membership_valid_cardno'] = '/^\d{16}$/';
	$config['membership_length'] = 16;
	$config['membership_cardimg'] = '/ui/icons/feed.png';
	$config['membership_cardtype'] = array(
		'N' => array(
		'pattern' => '/^\d{16,16}$/',
		'valid_pattern' => '/^\d{16,16}$/',
		'charges' => 0,
		'description' => 'Bonus-Link'
		)
	);*/
	$config['membership_allow_add_at_backend'] = 1;
	$config['membership_cardname'] = 'AKAD';
	$config['membership_valid_cardno'] = '/^AK\d{7,8}$/';
	$config['membership_length'] = 9;
	$config['membership_type'] = array('member1','member2','member3');
	$config['membership_cardimg'] = '/images/akad-%s.gif';
	$config['membership_cardtype'] = array (
		'N' => array (
		'pattern' => '/^AK4/',
		'valid_pattern' => '/^AK\d{7,8}$/',
		'charges' => 0,
		'description' => 'New'
		),
		'B' => array (
		'pattern' => '/^AK\d\d1/',
		'valid_pattern' => '/^AK\d\d1\d{4,4}/',
		'charges' => 0,
		'description' => 'Blue'
		),
		'R' => array (
		'pattern' => '/^AK\d\d6/',
		'valid_pattern' => '/^AK\d\d6\d{4,4}/',
		'charges' => 0,
		'description' => 'Red'
		),
		'G' => array (
		'pattern' => '/^AK\d\d8/',
		'valid_pattern' => '/^AK\d\d8\d{4,4}/',
		'description' => 'Green'
		)
	);
	
	// configuration variables
	$config['sku_artno_allow_specialchars'] = 1;
	$config['sku_application_artno_allow_duplicate'] = 1;
//	$config['sku_application_require_multics'] = 1;
	$config['sku_application_enable_variety'] = 1;
	$config['sku_application_allow_no_artno_mcode'] = 0;
	$config['sku_variety_start_from_zero'] = 1;
	$config['sku_matrix_start_from_zero'] = 0;
	$config['sku_application_softline_outright_matrix_only'] = 0;
	$config['sku_autocomplete_hide_variety'] = 1;
	$config['link_code_name'] = 'CM Code';
	$config['no_ip_string'] = 'http://akad%s.no-ip.org:4000/';
	$config['sku_application_valid_mcode'] = '/^([0-9]{14}|3[0-9]{11})$/';
	$config['sku_allow_move_sku_parent']=1;
	$config['sku_special_approver']='cslo';
	

	$config['ajax_autocomplete_hide_vendor_code']=1;

	// SKU&PO - PERIOD TO AUTOApprove
	$config['special_auto_approve']=5;
	
	// PO
  $config['mkt_started_month'] = 4;	//added for started month (mkt_annual.php)
	$config['po_allow_vendor_request']=1; //allow supplier to login to open PO
	$config['po_vendor_ticket_expiry']="10";//access code period of supplier
	$config['po_show_last_po']=1;
	$config['po_internal_copy_3signatures']=0;
	$config['po_external_copy_3signatures']=0;
	$config['po_selling_price_readonly']=0;
	$config['po_no_of_item_perpage'] = 15;
//	$config['po_set_max_items']=15;
	$config['po_special_approver']='SLLEE';
	$config['po_block_consignment_sku']=1;
	$config['po_allow_hq_purchase']=1;
	$config['po_item_allow_duplicate']=1;
	$config['po_printing_no_item_line'] = 1;
  //$config['po_alt_print_template'] = 'aneka/po.print.tpl';
  //$config['po_distribution_alt_print_template'] = 'aneka/po.print_distribution.tpl';
  //$config['po_checklist_alt_print_template'] = 'aneka/po.checklist.print.tpl';
     		
	//allow ubs vendor maintenance
	$config['payment_voucher_vendor_maintenance']=1;
	$config['payment_voucher_no_banker']=3;	
	$config['payment_voucher_no_acct_code']=10;
	
	// GRR - PERIOD TO NOTIFY
	$config['grr_incomplete_notification']=3;
	
	//GRN CORRECTION
	$config['grn_always_require_correction'] = 0;
	$config['grn_verification_allow_qty_variance']=1;	
	$config['grn_summary_show_related_invoice']=1;	
	$config['grn_group_same_item']=0;	
	$config['grn_do_branch_update_cost'] = 1;
	$config['pivot_dropdown_show_all']=0;
	
    // if single server mode
	$config['single_server_mode'] = 1;
	$config['single_server_port_begin'] = 2000;
	
	// stock take setting
	$config['stock_take_cost'] = 'grn'; // (grn or avg)
		
//    if ($_SERVER['REMOTE_ADDR']=='192.168.1.7') $config['single_server_mode'] = 0;
	//Customize Approval Flow
	$config['customize_approval_flow'] = array(
		array("type" => "DO"),
		array("type" => "INVOICE"),
		array("type" => "MKT1"),
		array("type" => "MKT3"),
		array("type" => "MKT5"),
		array("type"=>"SALES_ORDER")
	);
	if($config['consignment_modules']){
        $config['customize_approval_flow'][] = array("type"=>"CREDIT_NOTE");
        $config['customize_approval_flow'][] = array("type"=>"DEBIT_NOTE");
	}
	
	//set the adjustment type list
	$config['adjustment_type_list'] = array (
		array("name" => "Reduce to clear"),
		array("name" => "Disposal/write off"),
		array("name" => "Store use - Foodcourt BJ"),
		array("name" => "Store use - Foodcourt GP"),
		array("name" => "Store use - Bakery BJ"),
		array("name" => "Interdepartment - Citymart"),
		array("name" => "Wrongly keyin - PO"),
		array("name" => "Wrongly keyin - GRA")
	);
	
	// Adjustment
	//$config['adj_print_item_per_page'] = 10;
  //$config['adj_printing_no_item_line'] = 1;
  
	$config['show_tracker']=1;
	$config['sku_multiple_selling_price'] = array('member1','member2','member3','wholesale1','wholesale2','coldprice');
	
	$config['sku_multiple_quantity_price'] = 1;
	#$config['menu_hide_bom_application'] = true;
	$config['sku_bom_show_mcode']=1;
	$config['financial_start_date'] = '-04-01';
	$config['dat_format'] = '%d/%m/%Y';
	
	$config['adjustment_branch_selection'] = 1;
//	$config['sku_always_show_trade_discount'] = 1;
	$config['sku_get_external_photos'] = array('path'=>$_SERVER['DOCUMENT_ROOT'].'/sku_photos/actual', 'method'=>'artno_heading');
	
	// CONSIGNMENT
	//$config['ci_use_split_artno'] = 1;
	
	
	// consignment invoice
	$config['ci_alt_print_template'] = '../cutemaree/consignment_invoice.print.cutemaree.tpl';
	$config['ci_print_item_per_page'] = 30;
	//$config['ci_toggle_ubs_status_level'] = 1000;
	$config['ci_printing_no_item_line'] = 1;
	
	// masterfile sku
	$config['sku_listing_hide_zero_balance'] = 1;
	$config['sku_listing_show_hq_cost'] = 1;
	$config['masterfile_sku_enable_ctn'] = 1;
	
	// notification
	$config['notification_price_change_show_artno '] = 0;
	
	// DO
	//Customize DO printing format
	// WS
	//$config['do_alt_print_template'] = 'ws/do.print.tpl';
	//$config['do_checkout_alt_print_template'] = 'ws/do_checkout.print.tpl';
	//$config['do_checkout_invoice_alt_print_template'] = 'ws/do_checkout.print_invoice.tpl';
	// Gmark
	//$config['do_checkout_invoice_alt_print_template'] = 'gmark/do_checkout.print_invoice.tpl';
	
    
	//$config['do_print_hide_cost']=1;
	$config['do_invoice_markup']=10;
	$config['do_print_hide_company_logo']=1;
	$config['do_set_max_items']=60;
	$config['do_accept_grn_barcode']=1;
	//$config['do_print_item_per_page'] = 25;
	//$config['do_print_item_per_last_page'] = 17;
	//$config['do_checkout_print_item_per_last_page'] = 12;
	//$config['do_checkout_invoice_print_item_per_last_page'] = 15;
	
	$config['do_checkout_no_need_lorry_info'] = 1;
	//$config['do_print_invoice_item_grouping']=1;
	
	//$config['do_use_rcv_pcs'] = 1;
	$config['do_invoice_separate_number'] = 1;
    // DO Price indicator - cost, selling, last_do
	$config['do_default_price_from'] = 'selling';
	$config['do_allow_credit_sales'] = 1;
	$config['do_allow_cash_sales'] = 1;
	$config['do_print_combine_same_item'] = 1;
	$config['do_transfer_have_discount'] = 1;
	$config['do_cash_sales_have_discount'] = 1;
	$config['do_credit_sales_have_discount'] = 1;
	
	//$config['do_item_allow_duplicate'] = 1;
	//$config['do_cash_sales_alt_print_item_per_page'] = 14;
	//$config['do_cash_sales_alt_print_item_per_lastpage'] = 9;
	$config['do_printing_no_item_line'] = 1;
	
	// DO Request
	$config['do_request_print_picking_list_size'] = 30;
	
	// GRR
	$config['grr_process_do'] = 1;
	
	// grn
	$config['grn_have_tax'] = 1;
	//$config['grn_print_item_per_page'] = 15;
	
	// gra
	$config['gra_print_item_per_page'] = 18;
	$config['gra_printing_no_item_line'] = 1;
	
	// PO Request
	$config['po_request_show_sales_trend'] = 1;
	
	// all doc
	$config['doc_reset_level'] = 9999;
	//$config['doc_uom_control'] = 1;

	//$config['grn_do_dont_update_cost'] = 1;
	//$config['grn_do_transfer_update_cost'] = 1;
	$config['grn_printing_no_item_line'] = 1;
	
	// pos
	$config['pos_server'] =  array('demo.arms.com.my','arms','pos123','armsdemo');
	$config['pos_image_server'] =  '10.1.1.200:2001';
	$config['pos_login_bg'] = 'ui/pos_login_bg.png';
	$config['pos_main_bg'] = 'ui/pos_main_bg.png';
	$config['pos_main_banner'] = 'ui/pos_main_banner.gif';
	
	
	//$config['do_print_hide_cost'] = 1;
	
	// redemption
	$config['redemption_print_item_per_page'] = 25;
	
	//check code
    $config['check_code_show_balance'] = 1;
	
	$config['approval_flow_use_all_order'] = 1;
	
	// Metrohouse
  	/*$config['do_alt_print_template'] = 'metrohouse/do.print.tpl';
	$config['do_checkout_alt_print_template'] = 'metrohouse/do_checkout.print.tpl';
	$config['do_checkout_invoice_alt_print_template'] = 'metrohouse/do_checkout.print_invoice.tpl';
	$config['do_print_item_per_page'] = 26;
	$config['do_print_item_per_last_page'] = 22;
	$config['do_checkout_print_item_per_last_page'] = 22;
	$config['do_checkout_invoice_print_item_per_last_page'] = 22;
	$config['adj_alt_print_template'] = 'metrohouse/adjustment.print.tpl';
	$config['ci_alt_print_template'] = 'metrohouse/consignment_invoice.print.tpl';
	$config['gra_alt_print_template'] = 'metrohouse/goods_return_advice.print.tpl';
	$config['grn_alt_print_template'] = 'metrohouse/goods_receiving_note.print.tpl';
	$config['grn_perform_alt_print_template'] = 'metrohouse/goods_receiving_note.perform_print.tpl';
	$config['ci_printing_no_item_line'] = 1;
	$config['do_printing_no_item_line'] = 1;
	$config['adj_print_item_per_page'] = 28;
	$config['adj_printing_no_item_line'] = 1;
	$config['ci_print_item_per_page'] = 26;
	$config['ci_monthly_report_print_item_per_page'] = 25;
	$config['consignment_transport_note_alt_print_template'] = 'metrohouse/consignment.transport_note.print.tpl';
	$config['ci_monthly_report_print_item_per_page'] = 28;
	$config['ci_monthly_report_alt_print_template'] = 'metrohouse/consignment.print_monthly_report.blank_form.tpl';
	$config['masterfile_branch_allow_print_envelope'] = 1;
	$config['masterfile_branch_envelope_alt_print_template'] = 'metrohouse/masterfile_branch.print_envelope.tpl';*/
//  $config['cn_alt_print_template'] = 'metrohouse/consignment.credit_note.print.tpl';
	$config['enable_transporter_masterfile'] = 1;
	$config['enable_consignment_transport_note'] = 1;
	$config['ci_monthly_report_check_inventory_updated'] = 1;
	
	
	// Minotex
	/*$config['do_cash_sales_alt_print_item_per_page'] = 13;
    $config['do_cash_sales_alt_print_item_per_lastpage'] = 8;
    $config['do_print_item_per_page'] = 30;
	$config['do_cash_sales_alt_print_template'] = 'minotex/do.print.tpl';
	$config['do_checkout_cash_sales_alt_print_template'] = 'minotex/do_checkout.print.tpl';
  	$config['do_checkout_invoice_cash_sales_alt_print_template'] = 'minotex/do_checkout.print_invoice.tpl';
  	$config['do_alt_print_template'] = 'minotex/do.print2.tpl';
  	$config['do_checkout_alt_print_template'] = 'minotex/do_checkout.print2.tpl';
  	$config['do_checkout_invoice_alt_print_template'] = 'minotex/do_checkout.print_invoice2.tpl';
  	$config['do_printing_no_item_line'] = 1;*/

  // gmark
  //$config['stock_balance_item_per_page'] = 40;
    //$config['do_alt_print_template'] = 'gmark/do.print.tpl';
	//$config['do_checkout_alt_print_template'] = 'gmark/do_checkout.print.tpl';
	//$config['do_checkout_invoice_alt_print_template'] = 'gmark/do_checkout.print_invoice.tpl';
	
  // SMO
  	//$config['do_print_item_per_page'] = 30;
  	//$config['do_alt_print_template'] = 'smo/do.print.tpl';
	//$config['do_checkout_alt_print_template'] = 'smo/do_checkout.print.tpl';
	//$config['do_checkout_invoice_alt_print_template'] = 'smo/do_checkout.print_invoice.tpl';
	$config['allow_sales_order'] = 1;
	$config['sales_order_printing_no_item_line'] = 1;
	
	$config['grn_do_hq2branch_update_cost'] = 1;
	//$config['grn_do_branch2branch_update_cost'] =1;
	//$config['grn_do_branch2hq_update_cost'] = 1;
	
	$config['membership_redemption_module'] = 1;
    //$config['counter_collection_server'] = 'http://demo.arms.com.my';
	//$config['sales_maintenance_server'] = 'http://demo.arms.com.my/pos.sales_maintenance.php';
//	$config['hide_from_menu'] = "STOCK_CHECK_REPORT";

	// pkt
	//$config['grr_worksheet_alt_print_template'] = 'pkt/goods_receiving_record.print.worksheet.tpl';
	//$config['grr_worksheet_show_additional_foc_row'] = 1;
	$config['do_alt_print_template'] = 'pkt/do.print.tpl';
	$config['do_checkout_alt_print_template'] = 'pkt/do_checkout.print.tpl';
	$config['do_checkout_invoice_alt_print_template'] = 'pkt/do_checkout.print_invoice.tpl';
	$config['do_checkout_print_item_per_last_page'] = 30;
	$config['do_checkout_invoice_print_item_per_last_page'] = 30;
	$config['do_print_item_per_last_page'] = 30;
	$config['do_print_item_per_page'] = 30;
	
	$config['membership_listing_printing_no_item_line'] = 1;
	//$config['membership_listing_printing_item_per_page'] = 10;
	//$config['membership_listing_print_mailing_item_per_page'] = 8;
	
	//wshqkb
	//$config['do_checkout_alt_print_template'] = 'wshqkb/do_checkout.print.tpl';
	//$config['do_checkout_invoice_alt_print_template'] = 'wshqkb/do_checkout.print_invoice.tpl';
	//$config['do_alt_print_template'] = 'wshqkb/do.print.tpl';
	//$config['grn_alt_print_template'] = 'wshqkb/goods_receiving_note.print.tpl';
	//$config['grn_print_item_per_page'] = 25;
	
	//cutemaree
	//$config['do_checkout_alt_print_template'] = 'cutemaree/do_checkout.print.tpl';
	//$config['do_checkout_invoice_alt_print_template'] = 'cutemaree/do_checkout.print_invoice.tpl';
	//$config['do_alt_print_template'] = 'cutemaree/do.print.tpl';
    $config['cn_alt_print_template'] = 'cutemaree/consignment.credit_note.print.tpl';

	$config['sku_consign_selling_deduct_discount_as_cost'] = 1;
	$config['masterfile_branch_allow_print_discount_table'] = 1;
	$config['do_request_picking_list_show_remark'] = 1;
	$config['stock_check_arms_stock_format'] = 1;
	$config['stock_check_artno_stock_format'] = 1;
	
	$config['membership_listing_can_print_and_export'] = 1;
	$config['do_remark_add_profit'] = 1;
	$config['stock_balance_by_dept_show_turnover'] = 1;
	$config['do_can_multiple_print'] = 1;
	$config['stock_take_variance_report_show_dept'] = 1;
	$config['category_search_include_code'] = 1;
	$config['do_enable_do_markup'] = 1;
	$config['do_credit_sales_show_sales_person_name'] = 1;
	$config['do_cash_sales_show_sales_person_name'] = 1;
	$config['do_split_auto_add_do_discount'] = 1;
	
	//Date limit
//	$config['upper_date_limit'] = 7;    //number count as future days
//	$config['lower_date_limit'] = 30;    //number count as passed days
//	$config['reset_date_limit'] = 23;   //number count as passed days
	
	
	// weight scale code
//	$config['sku_weight_code_length']=6;
	
	
	if(strpos($_SERVER['HTTP_USER_AGENT'],'Ubuntu')>0)	$config['stock_take_print_item_row_per_page'] = 22; // print 22 rows for ubuntu
	else    $config['stock_take_print_item_row_per_page'] = 25;
	
	$config['cn_print_item_per_page'] = 20;
	$config['cn_print_item_per_last_page'] = 15;
	$config['cn_printing_no_item_line'] = 1;
	$config['dn_printing_no_item_line'] = 1;
	$config['allow_secondary_discount'] = 1;
	$config['do_approval_by_department'] = 1;	// allow to select department from Approval Flow and DO
	$config['do_allow_open_item'] = 1;	// allow to add open item in DO

    //$config['po_disable_hq_get_all_branches_sales_trend'] = 1;
    $config['intranet_ip_prefix'] = "10.1";
// debug
if ($_SERVER['SERVER_NAME'] == 'maximus')
{
	define('DISP_ERR',1);
	define('NO_OB', 1);
	define('HQ_HAVE_SALES',1);
}
?>
