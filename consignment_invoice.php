<?php
/*
13/3/2009 4:32:00 PM Andy
- create new type of invoice , consignment lost invoice
	table modified
	- alter table ci add type enum('sales','lost')
	- alter table ci_items add discount double
	- alter table tmp_ci_items add discount double

16/3/2009 4:32:00 PM Andy
- add config[ci_print_item_per_page] , config[ci_alt_print_template] checking

19/3/2009 12:16:54 PM yinsee
- update sales cache when export pos

23/3/2009 1:24:32 PM yinsee
- update changed=1 when pos export

23/3/2009 4:30:00 PM Andy
- Add total selling price column, add sub total row to show sub total of selling price and cost price

3/30/2009 1:00 PM Andy
- add session to save the last ci_date to be use in new ci
	- table modified
		- alter table ci add total_selling double default 0 after total_amount
		- update ci set total_selling=(select sum(pcs*selling_price) from ci_items where ci_id=ci.id and ci_items.branch_id=ci.branch_id)
		- alter table ci_items add stock_balance double
		- alter table tmp_ci_items add stock_balance double

3/31/2009 11:34:00 AM Andy
- change to save sub total amount and display in the ci list
    - table modified
        - alter table ci add sub_total_amt double default 0 after total_pcs

5/6/2009 10:42:00 AM Andy
- add export invoice function

5/13/2009 10:09:00 Am Andy
- add privilege checking - CON_INVOICE

6/23/2009 3:32 PM Andy
- add checking on $config['ci_checkout_alt_print_template'] to print own format of checkout

6/24/2009 3:00 PM Andy
- change export pos to use cost price

7/29/2009 4:02:59 PM Andy
- check send to branch allow edit selling price or not

8/4/2009 2:14:56 PM Andy
- add reset function

2/8/2010 4:52:59 PM Andy
- Add new consignment type: consignment over invoice

4/1/2010 5:40:40 PM Andy
- Monthly report change to only show active branch

5/31/2010 2:54:17 PM Andy
- Disable Cosignment Lost/Over Invoice
- CN/DN/Invoice/DO (Markup/Discount) now can implement new consignment discount format.

6/9/2010 4:56:40 PM Andy
- Fix print multiple invoice middle line cannot be hide even config already set.
- Fix stock balance calculation to ignore inactive grr.
- Remove pos_transaction import modules.
- All report which use pos_transaction will change to use pos and pos_items.
- SKU items, category, pwp and member sales cache will directly generate once counter collection is finalized.
- All Sales cache will be delete once counter collection is un-finalized.
- cron to calculate pwp and member sales cache is retired.
- Counter collection finalize status will change to store in a new table.

7/9/2010 10:44:07 AM yinsee
- add reexport pos to auto regenerate all the export-pos for exporeted invoice
- because after removing pos_transaction usage, we need to reexport the invoice to POS tabble

7/12/2010 3:31:26 PM Andy
- Automatically add pos finalized status for all exported pos.
- New Consignment invoice will automatically create pos finalized entry when export pos.

7/14/2010 2:52:23 PM Andy
- Add settings for consignment invoice.
- Able to control whether use item discount or not.
- Able to control whether split invoice by price type or not when confirm.

7/19/2010 4:54:33 PM Andy
- Invoice change to directly export to sales when approved.

7/23/2010 11:19:02 AM Andy
- Fix Consignment Invoice when multiple print will not show the printing by user name.

10/8/2010 3:44:15 PM Andy
- Make consignment invoice list auto sort base on last update and invoice no.

10/12/2010 6:13:32 PM Andy
- Add new setting: Auto bring item discount to sheet discount when confirm. (Only if there is no split and all items have same discount percent). (can control by config to turn it default on)

11/8/2010 11:31:27 AM Andy
- Change financial year dropdown to sort by descending.

11/9/2010 10:45:51 AM Andy
- Add checking for canceled/deleted and prevent it to be edit.

11/15/2010 10:26:12 AM Alex
- add branch searching

12/7/2010 6:40:14 PM Alex
- fix branch searching no data

6/6/2011 6:13:11 PM Justin
- Added the insert/update for exchange rate.

6/20/2011 6:27:33 PM Justin
- Added the update for all foreign fields into ci table.

7/5/2011 4:17:33 PM Justin
- Added the missing checking of currency code during add items.

8/1/2011 6:05:20 PM Andy
- Add config to allow consignment invoice to add duplicate items.
- Change split() to use explode()

11/9/2011 5:20:43 PM Justin
- Added to replace cost price with selling price when found config cm_use_deliver_branch_sp.

12/15/2011 2:47:43 PM Justin
- Fixed bug that system never reset foreign cost price, discount amount and total amount if it is not foreign type.

3/26/2012 2:16:43 PM Justin
- Added new functions "all_update_mst_price_type" and "all_update_dtl_price_type".
- Renamed "price_type_id" into "price_type".

4/3/2012 4:13:49 PM Alex
- add reset_inv to reset exported invoice

8/2/2012 5:38 PM Andy
- Add checking at printing stage, if found invoice got tick "use branch discount" will see whether item got discount

9/18/2012 10:50 AM Drkoay
- add 2 new column discount_selling_price_percent and discount_item_row_percent in ci_save
- bring_item_disc_to_sheet_disc call if only discount_selling_price_percent and discount_item_row_percent is not exist

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

7/3/2013 11:32 AM Fithri
- pm notification standardization

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window
- use $_SESSION to prevent clash of new document id if it is created at the same second

8/1/2013 3:37 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

1/17/2015 11:23 AM Justin
- Enhanced to have GST calculation.

3/24/2015 11:17 AM Justin
- Enhanced to have checking and pickup Export GST Type from GST Settings while found the branch deliver to is out of country.

3/31/2015 5:46 PM Andy
- Fix when change "To branch" will load the wrong selling price.
- Temporary remove the feature is_export checkbox.
- Enhanced to check branch is_export to either load export type gst or designated area gst.
- Fix multiple add item din't get the correct selling price.

4/3/2015 2:25 PM Justin
- Bug fixed on GST indicator does not capture properly.
- Enhanced to pickup GST summary.

4/21/2015 3:34 PM Justin
- Bug fixed on taking wrong GST ID.

4/28/2015 10:55 AM Justin
- Enhanced to pickup currency description while printing invoice.

5/5/2015 11:43 AM Justin
- Enhanced to round up cost and selling price base on config.

5/7/2015 3:57 PM Andy
- Remove the owner checking when open to edit.
- Fix the wrong title when pronpt error on edit.
- Remove the junk code related to checkout and markup.
- Add print summary.
- Remove invoice type filter on multiple print.

5/13/2015 4:08 PM Andy
- Change the selling price to be always gst inclusive.
- Fix get wrong stock balance on add item.
- Change the ajax add item to use json instead of xml.
- Enhanced to have display cost price feature.
- Enhanced to check branch foreign or designated areas.

5/19/2015 10:04 AM Andy
- Change to always show latest artno/mcode for item.

5/20/2015 10:43 AM Andy
- Add function recalculate_amount_with_date_range to able to recalculate invoice amount by date range.

6/9/2015 2:00 PM Andy
- Enhanced to always store amt2 even no gst.

6/10/2015 4:07 PM Andy
- Fix multiple add rounding issue.

8/25/2015 10:49 AM Andy
- Fix re_export_pos bugs.

8/27/2015 4:04 PM Andy
- Fix change branch reload foreign price bugs.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('CON_INVOICE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CON_INVOICE', BRANCH_CODE), "/index.php");
include('consignment.include.php');
include("consignment_invoice.include.php");
$maintenance->check(271);
$smarty->assign("PAGE_TITLE", "Consignment Invoice");

init_selection();
$branch_id = mi($_REQUEST['branch_id']);
if ($branch_id ==0){
	$branch_id = $sessioninfo['branch_id'];
}
$id = intval($_REQUEST['id']);
if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
		case 'open':
			// delete old tmp items
			$con->sql_query("delete from tmp_ci_items where (ci_id>1000000000 and ci_id<".strtotime('-1 day').") and user_id = $sessioninfo[id]");
		case 'refresh':
		    load_branch();
			ci_open($id, $branch_id);
			exit;

		case 'view':
		    load_branch();
			ci_view($id, $branch_id);
			exit;

		case 'print':
			ci_print($id, $branch_id);
			exit;

		case 'confirm':
		case 'save':
			ci_save($id, $branch_id, ($_REQUEST['a']=='confirm'));
			exit;

		case 'delete':
			ci_delete($id, $branch_id);
			exit;

		case 'ajax_load_invoice_list':
		    load_invoice_list();
		    exit;

		case 'ajax_refresh_cost':
			ci_ajax_refresh_cost($id, $branch_id);
			exit;

		case 'ajax_add_grn_barcode_item':
			ci_ajax_add_grn_barcode_item($id, $branch_id);
			exit;

		case 'ajax_add_item':
			ci_ajax_add_item($id, $branch_id);
			exit;

		case 'ajax_delete_item':
	        $con->sql_query("delete from tmp_ci_items where id=$id and branch_id=$branch_id");
   			print "$branch_id, $id, OK";
			exit;

		case 'update_zero_amount':
			$con->sql_query("update ci set total_amount = (select sum((ctn*fraction+pcs)*cost_price) from ci_items
			left join uom on uom_id = uom.id
			where ci_items.branch_id=ci.branch_id and ci_items.ci_id=ci.id)
			where ci.total_amount=0") or die(mysql_error());
			print $con->sql_affectedrows()." records updated.";
			exit;

	    case 're_export_pos':
			re_export_pos();
			exit;

		case 'export_pos':
		    export_pos();
		    exit;

		case 'change_lost_inv_branch':
		    change_lost_inv_branch();
		    exit;

		case 'multi_add':
		    multi_add();
		    exit;

		case 'save_multi_add':
		    save_multi_add();
		    exit;

		case 'export_inv':
		    export_inv();
		    exit;

		case 'reset_inv':
		    reset_inv();
		    exit;

		case 'toggle_export_ubs_status':
		    toggle_export_ubs_status();
		    exit;

        case 'do_reset':
			if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($_REQUEST['curr_date'],$branch_id,'ci')) {
				$errm['top'][] = $LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED'];
				load_branch();
				ci_view($id, $branch_id);
				exit;
			}
		    do_reset($id,$branch_id);
		    exit;
		case 'ajax_search_inv_no':
		    ajax_search_inv_no();
		    exit;
		case 'multiple_print':
		    multiple_print();
		    exit;
		case 'show_per_changed':
		    show_per_changed();
		    exit;
		case 'all_update_mst_price_type':
			all_update_mst_price_type();
			exit;
		case 'all_update_dtl_price_type':
			all_update_dtl_price_type();
			exit;
		case 'check_tmp_item_exists':
			check_tmp_item_exists();
			exit;
		case 'recalculate_ci_gst_amount':
			recalculate_ci_gst_amount($_REQUEST['id'], $_REQUEST['branch_id']);
			exit;
		case 'recalculate_amount_with_date_range':
			recalculate_amount_with_date_range($_REQUEST['date_from'], $_REQUEST['date_to']);
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;

	}
}

$con->sql_query("delete from tmp_ci_items where user_id=$sessioninfo[id]");

// set default financial month year
list($dummy,$f_month,$f_date) = explode("-",$config['financial_start_date']);
if(date('m')<$f_month)  $f_year = date('Y')-1;
else    $f_year = date('Y');
// get year list
$con->sql_query("select distinct(year(ci_date)) as y from ci order by y desc") or die(mysql_error());
$smarty->assign('ci_year_list',$con->sql_fetchrowset());

load_branch();
load_branch_group();

$smarty->assign('financial_date',array('m'=>$f_month,'y'=>$f_year));
$smarty->display("consignment_invoice.home.tpl");
exit;

function ci_save($ci_id, $branch_id, $is_confirm){
	global $con, $LANG, $smarty, $sessioninfo, $config;
	//... validate, save, check confirm, send pm, set approval.....
	$form=$_REQUEST;
	
	if(!is_new_id($ci_id)){ // not new invoice
		// check can be edit or not
		check_must_can_edit($branch_id, $ci_id);
	}
	
	save_tmp_items();

	//VALIDATE DATA
	$errm = array();
	if(!$form['uom_id']) $errm['top'] = sprintf($LANG['CI_EMPTY']);
	$_SESSION['ci_date'] = $form['ci_date'];

	$arr=explode("-",$form['ci_date']);
	$yy=$arr[0];
	$mm=$arr[1];
	$dd=$arr[2];
	if(!checkdate($mm,$dd,$yy)){
	   	$errm['top'][] = $LANG['CI_INVALID_DATE'];
		$form['ci_date']='';
	}
	
	if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($form['ci_date'],$branch_id,'ci')) {
		$errm['top'][] = $LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED'];
	}

	if($form['delivery_type']==2){
		if($form['open_info']['name']=='')
	   		$errm['top'][] = $LANG['CI_OPEN_INFO_NAME_EMPTY'];
		if($form['open_info']['address']=='')
	   		$errm['top'][] = $LANG['CI_OPEN_INFO_ADDRESS_EMPTY'];
	}

	if(!$errm && $is_confirm){
		// check and create branch_approval_history data
		$params = array();
		$params['type'] = 'INVOICE';
		$params['reftable'] = 'ci';
		$params['user_id'] = $sessioninfo['id'];
		$params['branch_id'] = $branch_id;
		$params['sku_type'] = 'consign';
		$params['doc_amt'] = $form['total_amount'];
		
		if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id']; // use back the same id if already have
		
		$astat = check_and_create_approval2($params, $con);
		if (!$astat) $errm['top'][] = $LANG['CI_NO_APPROVAL_FLOW'];
		else{
			$form['approval_history_id'] = $astat[0];
			if ($astat[1] == '|'){
				$last_approval = true;
				if($astat['direct_approve_due_to_less_then_min_doc_amt'])	$direct_approve_due_to_less_then_min_doc_amt = 1;	// direct approve because no qualify for min doc amt
			} 
		}
	}
	if($errm){
		$smarty->assign("errm", $errm);
		load_branch_group();
		load_branch();
		ci_open($ci_id,$branch_id);
		exit;
	}
	else{
		if ($is_confirm) $form['status'] = 1;
	    if ($last_approval) $form['approved'] = 1;

		$form['id']=$ci_id;
		$form['branch_id']=$branch_id;
		$form['last_update'] = 'CURRENT_TIMESTAMP';
		$form['added'] = 'CURRENT_TIMESTAMP';
		$form['user_id'] = $sessioninfo['id'];

		if($form['delivery_type']==2){
			$form['open_info'] = serialize($form['open_info']);
			$form['deliver_branch']='';
			$form['ci_branch_id']=0;
		}
		else{
		    if(isset($form['deliver_branch'])){
				$form['ci_branch_id']=$form['deliver_branch'];
				$form['deliver_branch']='';
			}
			else{
				$form['deliver_branch'] = serialize($form['deliver_branch']);
			}
			$form['open_info']='';
		}

		// for consignment customer that using exchange rate only...
		if(is_array($config['masterfile_branch_region']) && is_array($config['consignment_multiple_currency'])){
			//print $_REQUEST['do_branch_id'];
			$con->sql_query("select region from branch where id = ".mi($form['ci_branch_id']));
			$to_branch_info = $con->sql_fetchrow();
			$con->sql_freeresult();

			$currency_code = $config['masterfile_branch_region'][$to_branch_info['region']]['currency'];
			
			// physically unset all foreign amount if found it is not foreign type
			if(!$currency_code || $currency_code == "RM"){
				$form['exchange_rate'] = 1;
				$form['total_foreign_amount'] = 0;
				$form['foreign_discount_amount'] = 0;
				$form['sub_total_foreign_amt'] = 0;
			}
		}

		if (is_new_id($ci_id)){
			if(!$form['create_type'])$form['create_type']=1;

			$con->sql_query("insert into ci " . mysql_insert_by_field($form, array('branch_id', 'user_id', 'dept_id', 'status', 'approved', 'ci_date', 'added', 'deliver_branch','total_pcs', 'total_ctn', 'total_amount', 'total_foreign_amount', 'remark','approval_history_id', 'ci_branch_id', 'open_info','price_indicate','discount_percent','discount_selling_price_percent','discount_item_row_percent','discount_amount','foreign_discount_amount','type','total_selling','sub_total_amt','sub_total_foreign_amt','show_per','auto_split_by_price_type','bring_item_disc_to_sheet_disc', 'exchange_rate', 'is_under_gst', 'total_gross_amt', 'sheet_gst_discount', 'total_gst_amt', 'gross_discount_amount', 'sub_total_gross_amt', 'total_foreign_gross_amt', 'sheet_foreign_gst_discount', 'total_foreign_gst_amt', 'gross_foreign_discount_amount', 'sub_total_foreign_gross_amt', 'is_export')));
			$form['id'] = $con->sql_nextid();
		}
		else{
		    $form['last_update'] = 'CURRENT_TIMESTAMP';

		    $con->sql_query("update ci set " . mysql_update_by_field($form, array('dept_id', 'ci_branch_id','status', 'approved', 'ci_date', 'deliver_branch','total_ctn', 'total_pcs', 'total_amount', 'total_foreign_amount', 'remark','approval_history_id', 'open_info','price_indicate','discount_percent','discount_selling_price_percent','discount_item_row_percent','discount_amount','foreign_discount_amount','type','total_selling','sub_total_amt','sub_total_foreign_amt', 'last_update','show_per','auto_split_by_price_type','bring_item_disc_to_sheet_disc', 'exchange_rate', 'is_under_gst', 'total_gross_amt', 'sheet_gst_discount', 'total_gst_amt', 'gross_discount_amount', 'sub_total_gross_amt', 'total_foreign_gross_amt', 'sheet_foreign_gst_discount', 'total_foreign_gst_amt', 'gross_foreign_discount_amount', 'sub_total_foreign_gross_amt', 'is_export'))." where branch_id=$branch_id and id=$ci_id");

		}

		//copy tmp table to ci_items table
		$q1=$con->sql_query("select * from tmp_ci_items
where ci_id=$ci_id and branch_id=$branch_id and user_id=$sessioninfo[id] order by id") or die(mysql_error());
		$first_id = 0;
		$discount_arr = array();
		while($r=$con->sql_fetchrow($q1)){
		    if($r['discount'] && !in_array($r['discount'], $discount_arr)){
                $discount_arr[] = $r['discount'];
			}
			$upd['ci_id']=$form['id'];
			$upd['branch_id']=$r['branch_id'];
			$upd['sku_item_id']=$r['sku_item_id'];
			$upd['artno_mcode']=$r['artno_mcode'];
			$upd['cost_price']=$r['cost_price'];
			$upd['selling_price']=$r['selling_price'];
			$upd['uom_id']=$r['uom_id'];
			$upd['ctn']=$r['ctn'];
			$upd['pcs']=$r['pcs'];
			$upd['ctn_allocation']=$r['ctn_allocation'];
			$upd['pcs_allocation']=$r['pcs_allocation'];
			$upd['selling_price_allocation']=$r['selling_price_allocation'];
			$upd['discount']=$r['discount'];
			$upd['stock_balance']=$r['stock_balance'];
			$upd['price_type'] = $r['price_type'];
			$upd['item_amt'] = $r['item_amt'];

			if(!$currency_code || $currency_code == "RM") $upd['foreign_cost_price'] = 0;
			else $upd['foreign_cost_price'] = $r['foreign_cost_price'];
			
			$upd['item_disc_amt'] = $r['item_disc_amt'];
			$upd['item_disc_amt2'] = $r['item_disc_amt2'];
			
			$upd['item_gst_amt'] = $r['item_gst_amt'];
			
			$upd['item_amt2'] = $r['item_amt2'];
			$upd['item_gst_amt2'] = $r['item_gst_amt2'];
			
			$upd['item_foreign_amt'] = $r['item_foreign_amt'];
			$upd['item_foreign_gst_amt'] = $r['item_foreign_gst_amt'];
			
			$upd['item_foreign_amt2'] = $r['item_foreign_amt2'];
			
			$upd['item_foreign_disc_amt'] = $r['item_foreign_disc_amt'];
			$upd['item_foreign_disc_amt2'] = $r['item_foreign_disc_amt2'];
				
			$upd['item_foreign_gst_amt2'] = $r['item_foreign_gst_amt2'];
			
			if($form['is_under_gst']){
				$upd['gst_id'] = $r['gst_id'];
				$upd['gst_code'] = $r['gst_code'];
				$upd['gst_rate'] = $r['gst_rate'];
				
				$upd['item_gst'] = $r['item_gst'];
				$upd['item_gst2'] = $r['item_gst2'];
				
				$upd['item_foreign_gst'] = $r['item_foreign_gst'];
				$upd['item_foreign_gst2'] = $r['item_foreign_gst2'];
				
				$upd['display_cost_price_is_inclusive'] = $r['display_cost_price_is_inclusive'];
				$upd['display_cost_price'] = $r['display_cost_price'];
			}
			
			$con->sql_query("insert into ci_items ".mysql_insert_by_field($upd)) or die(mysql_error());
			//print "insert into ci_items ".mysql_insert_by_field($upd)."<br>";
			if ($first_id==0) $first_id = $con->sql_nextid();
		}
		//print_r($discount_arr);

		if ($first_id>0) {
			if(!is_new_id($ci_id)){
				$con->sql_query("delete from ci_items where branch_id=$branch_id and ci_id=$ci_id and id<$first_id") or die(mysql_error());
			}

			$con->sql_query("delete from tmp_ci_items where ci_id=$ci_id and branch_id = $branch_id and user_id = $sessioninfo[id]") or die(mysql_error());
		}
		/*else{
			die("System error: Insert ci_items failed. Please contact ARMS technical support.");
		}*/

		update_ci_sheet_price_type($form['branch_id'], $form['id']);
		
		$t = $form['a'];
		if ($is_confirm){
			$con->sql_query("update branch_approval_history set ref_id=$form[id] where id=$form[approval_history_id] and branch_id = $branch_id");
			
	        log_br($sessioninfo['id'], 'Consignment Invoice', $form['id'], "Consignment Invoice Confirmed (ID#$form[id], Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:$form[total_amount])");
		    if ($last_approval){
		    	if($direct_approve_due_to_less_then_min_doc_amt)	$_REQUEST['direct_approve_due_to_less_then_min_doc_amt'] = 1;
            	ci_approval($form['id'], $branch_id, $form['status'], true, false);
            	$t = 'approve';
			}
			else {
				$to = get_pm_recipient_list2($form['id'],$form['approval_history_id'],0,'confirmation',$branch_id,'ci');
				send_pm2($to, "Consignment Invoice Approval (ID#$form[id])", "/consignment_invoice.php?a=view&id=$form[id]&branch_id=$branch_id", array('module_name'=>'ci'));
			}
				
            $split_success = false;
			if($form['auto_split_by_price_type']){
                $split_success = split_by_price_type($branch_id, $form['id']);
			}
			
			if(!$form['discount_percent']&&!$split_success && $form['bring_item_disc_to_sheet_disc'] && count($discount_arr)==1){
              if(!$form['discount_selling_price_percent']&&!$form['discount_item_row_percent']){
                bring_item_disc_to_sheet_disc($branch_id, $form['id']);
              }
			}
		}
		else
	        log_br($sessioninfo['id'], 'Consignment Invoice', $form['id'], "Consignment Invoice Saved (ID#$form[id], Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:$form[total_amount])");
	}
	header("Location: /consignment_invoice.php?t=$t&save_id=$form[id]");
	exit;
}


function ci_open($id, $branch_id){
	global $con, $LANG, $sessioninfo, $smarty, $config;

	$form = $_REQUEST;// keep the passed header

	//is new ci
	if ($id==0){
		$id=time();
		if($id <= $_SESSION['ci_last_create_time']) {$id = $_SESSION['ci_last_create_time']+1;}
		$_SESSION['ci_last_create_time'] = $id;
		$form['id']=$id;
	}

	//if the action is open and is not a NEW ci
	if ($form['a']=='open' && !is_new_id($id)){
		//get Existing ci header
		$form=load_ci_header($id, $branch_id);
		if(!$form){
		    $smarty->assign("url", "/consignment_invoice.php");
		    $smarty->assign("title", "Consignment Invoice");
		    $smarty->assign("subject", sprintf($LANG['CI_NOT_FOUND'], $id));
		    $smarty->display("redir.tpl");
		    exit;
		}

		// check ci permission
		/*if ($form['user_id']!= $sessioninfo['id'] && $sessioninfo['level']<9999){
			//if(checking department)
		    $smarty->assign("url", "/consignment_invoice.php");
		    $smarty->assign("title", "Delivery Order");
		    $smarty->assign("subject", sprintf($LANG['CI_NO_ACCESS'], $id));
		    $smarty->display("redir.tpl");
		    exit;
		}
		//if the ci oredi submit and not the reject ci, goto view only.
		else*/if($form['status'] && $form['status']!=2){
			ci_view($id, $branch_id);
			exit;
		}
		copy_to_tmp($id, $branch_id);
	}else{ //IF THE INVOICE IS NEW
		save_tmp_items();
		$form['id']=$id;
		if(!$form['ci_date']){
			if($_SESSION['ci_date']) $form['ci_date'] = $_SESSION['ci_date'];
			else $form['ci_date'] = date("Y-m-d", time());
		}
		
		if($config['enable_gst'] && $form['ci_date']){
			$_REQUEST['is_under_gst'] = $form['is_under_gst'] = check_gst_status(array('date'=>$form['ci_date'], 'branch_id'=>$branch_id));
			if($form['is_under_gst']) construct_gst_list('supply');
		}
	}
	//print "load_ci_items($id, $branch_id, true))";
	$smarty->assign("ci_items", load_ci_items($id, $branch_id, true));
	//echo"<pre>";print_r($form);echo"</pre>";
	// load branches group
	load_branch_group();
	//print_r($form);

	$smarty->assign("form", $form);
	$smarty->display("consignment_invoice.new.tpl");
}


function ci_view($id, $branch_id){
	global $smarty, $LANG, $errm;
	//get Existing ci header
	$form=load_ci_header($id, $branch_id);
	if(!$form){
	    $smarty->assign("url", "/consignment_invoice.php");
	    $smarty->assign("title", "Consignment Invoice");
	    $smarty->assign("subject", sprintf($LANG['CI_NOT_FOUND'], $id));
	    $smarty->display("redir.tpl");
	    exit;
	}
	load_branch_group();

	$smarty->assign("readonly", 1);
	//print_r(load_ci_items($id, $branch_id));
	$smarty->assign("ci_items", load_ci_items($id, $branch_id));
	//print_r($form);
	$smarty->assign("form", $form);
	$smarty->assign("errm", $errm);
	$smarty->display("consignment_invoice.new.tpl");
}

function ci_print($id, $branch_id){
	global $con, $smarty, $config;

	$form=load_ci_header($id, $branch_id);
	$ci_items = load_ci_items($id, $branch_id);

    $con->sql_query("select * from branch where id = $form[branch_id]");
	$smarty->assign("from_branch", $con->sql_fetchrow());

	$con->sql_query("select * from branch where id = $form[ci_branch_id]");
	$to_branch = $con->sql_fetchrow();

	if($config['consignment_modules'] && $config['masterfile_branch_region'] && $to_branch['region']){
		$to_branch['currency_code'] = strtoupper($config['masterfile_branch_region'][$to_branch['region']]['currency']);
	}
	if(!$to_branch['currency_code']) $to_branch['currency_code'] = "RM";
	else{ // get description from forex table
		$q1 = $con->sql_query("select * from consignment_forex where currency_code = ".ms($to_branch['currency_code']));
		$curr_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$to_branch['currency_description'] = $curr_info['currency_description'];
	}
	
	if(!$to_branch['currency_description']) $to_branch['currency_description'] = "Ringgit Malaysia";

	if($form['show_per'] && $ci_items){	// if got tick show %
		// check item got % or not
		foreach($ci_items as $r){
			if($r['discount']){
				$form['got_item_discount'] = 1;
				break;
			}
		}
	}
	
	$smarty->assign("to_branch", $to_branch);
	$smarty->assign("form", $form);

	$item_per_page = $config['ci_print_item_per_page']>0?$config['ci_print_item_per_page']:35;
	$item_per_lastpage = $item_per_page - 5;

	$totalpage = 1 + ceil((count($ci_items)-$item_per_lastpage)/$item_per_page);

	// load GST summary info
	if($form['is_under_gst']){
		load_gst_summary($ci_items);
	}
	
	if($_REQUEST['print_ci']){
		for ($i=0,$page=1;$page<=$totalpage;$i+=$item_per_page,$page++){
		  //print "<h2>page size = ".(($page < $totalpage)?$item_per_page:$item_per_lastpage)."</h2>";
			$smarty->assign("PAGE_SIZE", ($page < $totalpage)?$item_per_page:$item_per_lastpage);
			$smarty->assign("is_lastpage", ($page >= $totalpage));
	        $smarty->assign("page", "Page $page of $totalpage");
	        $smarty->assign("start_counter", $i);
	        $smarty->assign("ci_items", array_slice($ci_items,$i,$item_per_page));
			/*if ($form['checkout'])
				$smarty->display("consignment_invoice_checkout.print.tpl");
			else
				$smarty->display("consignment_invoice.print.tpl");*/
			if($config['ci_alt_print_template'])    $smarty->display($config['ci_alt_print_template']);
			else    $smarty->display("consignment_invoice.print.tpl");
			$smarty->assign("skip_header",1);
		}
	}
	
	if($_REQUEST['print_summary']){
		//print_r($form);
		//print_r($ci_items);
		$ci_summary = array();
		
		// loop for each ci items
		foreach($ci_items as $r){
			$item_key = $r['price_type'].'-'.$r['discount']."-".$r['indicator_receipt'].'-'.$r['gst_rate'];
			
			if(!isset($ci_summary['items'][$item_key])){
				$ci_summary['items'][$item_key]['price_type'] = $r['price_type'];
				$ci_summary['items'][$item_key]['discount'] = $r['discount'];
				$ci_summary['items'][$item_key]['indicator_receipt'] = $r['indicator_receipt'];
				$ci_summary['items'][$item_key]['gst_code'] = $r['gst_code'];
				$ci_summary['items'][$item_key]['gst_rate'] = $r['gst_rate'];
			}
			$ci_summary['items'][$item_key]['item_disc_amt'] += $r['item_disc_amt'];
			$ci_summary['items'][$item_key]['item_amt'] += $r['item_amt'];
			$ci_summary['items'][$item_key]['item_gst'] += $r['item_gst'];
			$ci_summary['items'][$item_key]['item_gst_amt'] += $r['item_gst_amt'];
			
			if($to_branch['currency_code'] && $to_branch['currency_code'] != 'RM'){
				$ci_summary['items'][$item_key]['item_foreign_gst_amt'] += $r['item_foreign_gst_amt'];
			}
			
			
			$ci_summary['items'][$item_key]['pcs'] += $r['pcs'];
		}
		//print_r($ci_summary);
		$smarty->assign('ci_summary', $ci_summary);
		
		if($config['ci_summary_alt_print_template'])    $smarty->display($config['ci_summary_alt_print_template']);
		else    $smarty->display("consignment_invoice.print_summary.tpl");
		$smarty->assign("skip_header",1);
	}
}

function copy_to_tmp($ci_id, $branch_id){

	global $con, $sessioninfo;
	//delete ownself ci items in tmp table
	$con->sql_query("delete from tmp_ci_items where ci_id=$ci_id and branch_id = $branch_id and user_id = $sessioninfo[id]");

	//copy ci_items to tmp table
	$q1=$con->sql_query("insert into tmp_ci_items
(ci_id, branch_id, user_id, sku_item_id, artno_mcode, cost_price, foreign_cost_price, selling_price, uom_id, ctn, pcs, ctn_allocation, pcs_allocation, selling_price_allocation,discount,stock_balance,price_type,gst_id,gst_code,gst_rate,item_disc_amt,item_disc_amt2,item_amt,item_gst,item_gst_amt,item_amt2,item_gst2,item_gst_amt2,item_foreign_disc_amt,item_foreign_disc_amt2,item_foreign_amt,item_foreign_gst,item_foreign_gst_amt,item_foreign_amt2,item_foreign_gst2,item_foreign_gst_amt2,
display_cost_price_is_inclusive, display_cost_price)
select
$ci_id, branch_id, $sessioninfo[id], sku_item_id, artno_mcode, cost_price, foreign_cost_price, selling_price, uom_id, ctn, pcs, ctn_allocation, pcs_allocation, selling_price_allocation,discount,stock_balance,price_type,gst_id,gst_code,gst_rate,item_disc_amt,item_disc_amt2,item_amt,item_gst,item_gst_amt,item_amt2,item_gst2,item_gst_amt2,item_foreign_disc_amt,item_foreign_disc_amt2,item_foreign_amt,item_foreign_gst,item_foreign_gst_amt,item_foreign_amt2,item_foreign_gst2,item_foreign_gst_amt2,
display_cost_price_is_inclusive, display_cost_price
from ci_items where ci_id=$ci_id and branch_id=$branch_id order by id");

}

function ci_delete($id, $branch_id){
	global $con, $sessioninfo;

	check_must_can_edit($branch_id, $id);   // check invoice
	
	$form = $_REQUEST;

    if(!$type) $type='delete';
    if(!$status) $status=4;
	$reason=ms($form['reason']);

    $con->sql_query("update ci set cancelled_by=$sessioninfo[id], reason=$reason, status=$status where id=$id and branch_id=$branch_id");

    $con->sql_query("delete from tmp_ci_items where ci_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id]");

	log_br($sessioninfo['id'], 'Consignment Invoice', $id, sprintf("Consignment Invoice Reset ($form[ci_no])",$id));

    header("Location: /consignment_invoice.php?t=$type&save_id=$id&save_ci_no=$form[ci_no]&");
}

function ci_ajax_refresh_cost($ci_id, $branch_id){
	global $con, $smarty;

	$form = $_REQUEST;
	$price_indicate=$form['price_indicate'];

	if($ci_id)
		$con->sql_query("update ci set price_indicate=".ms($price_indicate)." where id=$ci_id and branch_id = $branch_id");

	save_tmp_items();

	$q1 = $con->sql_query("select tdi.*, uom.fraction as uom_fraction
from tmp_ci_items tdi
left join uom on uom.id=tdi.uom_id
where tdi.ci_id=$ci_id and tdi.branch_id = $branch_id");
	while($r1=$con->sql_fetchrow($q1)){
		$update=get_item_price($r1['sku_item_id'], $branch_id, $price_indicate);
		$new_cost_price=$update['cost_price']*$r1['uom_fraction'];
		$con->sql_query("update tmp_ci_items set cost_price='$new_cost_price' where id = $r1[id] and branch_id=$branch_id");
	}
	$smarty->assign("ci_items", load_ci_items($ci_id, $branch_id, true));
	$smarty->assign("form", $form);
	$smarty->display("ci.new.sheet.tpl");
}

function ci_ajax_add_grn_barcode_item($id, $branch_id){
	ci_ajax_add_item($id, $branch_id, true);
}

function ci_ajax_add_item($id, $branch_id, $is_grn_barcode=false){
	global $con, $smarty, $LANG, $config, $gst_list;

	$form=$_REQUEST;
	$ci_type = $form['type']?$form['type']:'sales';
	if (!$config['ci_accept_grn_barcode'] || !$is_grn_barcode)
	{
		$sku_item_id=mi($form['sku_item_id']);
		$qty_pcs='';
	}
	else
	{
		if (preg_match("/^00/", $form['grn_barcode']))	// form ARMS' GRN barcoder
		{
			$sku_item_id=mi(substr($form['grn_barcode'],0,8));
			$qty_pcs=mi(substr($form['grn_barcode'],8,4));
		}
		else	// from ATP GRN Barcode, try to search the link-code
		{
			$linkcode=substr($form['grn_barcode'],0,7);
			$qty_pcs=mi(substr($form['grn_barcode'],7,5));
			$con->sql_query("select id from sku_items where link_code = ".ms($linkcode));
			$sku_item_id=$con->sql_fetchfield(0);
			if (!$sku_item_id) fail(sprintf($LANG['CI_INVALID_ITEM'],$linkcode));
		}
	}
	save_tmp_items();

	$q1=$con->sql_query("select sku_items.artno, sku_items.mcode, sku_items.sku_item_code, sku_items.description as description, if(sku_items.artno is null,sku_items.mcode, sku_items.artno) as artno_mcode, sku_items.id as sku_item_id, uom.id as uom_id, uom.fraction as uom_fraction
from sku_items
left join sku on sku_id = sku.id
left join uom on sku_items.packing_uom_id = uom.id
where sku_items.id=$sku_item_id");
	$item = $con->sql_fetchrow($q1);
	if (!$item) {
		fail(sprintf($LANG['CI_INVALID_ITEM'],$sku_item_id));
	}
	//if ($form['deliver_branch'])
	//{
		/*foreach($form['deliver_branch'] as $bid)
		{
			$item['pcs_allocation'][$bid] = $qty_pcs;
		}*/
	//	$item['pcs_allocation'][$bid] = $qty_pcs;
	//}
	//else
		$item['pcs'] = $qty_pcs;

		// no longer need
		//$tmp=get_item_price($sku_item_id, $branch_id, $form['price_indicate']);
		//$item = array_merge($item, $tmp);
	
	$tmp_sell=get_item_selling($sku_item_id, $form['deliver_branch'], $form['ci_branch_id']);
	$item = array_merge($item, $tmp_sell);

	//print_r($item);
	//if($config['consignment_modules'] && $config['cm_use_deliver_branch_sp']){
		$item['cost_price'] = $item['selling_price'];
		
		if($config['consignment_modules'] && is_array($config['masterfile_branch_region']) && is_array($config['consignment_multiple_currency']) && $_REQUEST['exchange_rate'] && $_REQUEST['exchange_rate'] != 1){ // if the price is not from cost
			$foreign_cost = $item['cost_price'];
			
			$item['cost_price'] = round($item['cost_price']*$_REQUEST['exchange_rate'], 3);
			$item['foreign_cost_price'] = round($foreign_cost, 3);
		}
		

	//} 
	//if($ci_type=='lost'||$ci_type=='over'){   // if it is lost invoice, get trade discount count
		$t_discount = get_trade_discount($sku_item_id, $branch_id, $form['ci_branch_id']);
		$item = array_merge($item, $t_discount);
	//}
	//print_r($item);

	// GST
	$is_export = 0;
	if($config['enable_gst'] && $form['is_under_gst']){
		// check if having to_branch_id and it is stock to be exported
		if($form['is_export']){
			$is_export = 1;
		}elseif($form['ci_branch_id']){
			$q2 = $con->sql_query("select is_export from branch where id = ".mi($form['ci_branch_id'])." and is_export>0");
			$export_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			if($export_info['is_export']>0){
				$export_gst = get_export_type_gst($export_info['is_export']);
			}
		}
		
		/*if($is_export){
			$q2 = $con->sql_query("select * from gst_settings where setting_name = 'export_gst_type'");
			$gst_settings = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			if($gst_settings){
				$q2 = $con->sql_query("select * from gst where id = ".mi($gst_settings['setting_value']));
				$export_gst = $con->sql_fetchassoc($q2);
				$con->sql_freeresult($q2);
			}else{
				$is_export = 0;
			}
		}*/
	
		construct_gst_list('supply');
		// get sku is inclusive
		$is_sku_inclusive = get_sku_gst("inclusive_tax", $sku_item_id);
		// get sku original output gst
		$gst_info = get_sku_gst("output_tax", $sku_item_id);
		
		if($export_gst){
			$item['gst_id'] = $export_gst['id'];
			$item['gst_code'] = $export_gst['code'];
			$item['gst_rate'] = $export_gst['rate'];
		}elseif($gst_info){
			$item['gst_id'] = $gst_info['id'];
			$item['gst_code'] = $gst_info['code'];
			$item['gst_rate'] = $gst_info['rate'];
		}else{ // pre load gst id, code and rate if not found
			$item['gst_id'] = $gst_list[0]['id'];
			$item['gst_code'] = $gst_list[0]['code'];
			$item['gst_rate'] = $gst_list[0]['rate'];
		}
	
		if($is_sku_inclusive == 'yes')	$item['display_cost_price_is_inclusive'] = 1;
		$item['display_cost_price'] = $item['cost_price'];
		
		if($item['gst_rate']>0){
			if($is_sku_inclusive == 'yes'){
				// is inclusive tax
				// find the price before tax
				$gst_tax_price = $item['selling_price'] / ($item['gst_rate']+100) * $item['gst_rate'];
				//$item['selling_price'] -= $gst_tax_price;
				$item['cost_price'] = $item['selling_price'] - $gst_tax_price;
				$item['display_cost_price'] = $item['selling_price'];
			}else{
				$gst_amt = round($item['selling_price'] * ($item['gst_rate']/100), $config['global_cost_decimal_points']);
				$item['selling_price'] += $gst_amt;
			}
		}
		
	}
	
	$ret=insert_tmp_item($item,$id,$form['ci_branch_id'],true);
	if ($ret==-2){
		fail(sprintf($LANG['CI_MAX_ITEM_CANT_ADD'], $config['ci_set_max_items']));
	}elseif($ret==-1){
	    fail(sprintf($LANG['CI_ITEM_ALREADY_IN_PO']));
	}
	$item['selling_price_allocation']=unserialize($item['selling_price_allocation']);

	// check selling price allow edit or not
    $con->sql_query("select ci_allow_edit_selling_price from branch where id=".mi($form['ci_branch_id'])) or die(mysql_error());
    $form['allow_edit_selling_price'] = $con->sql_fetchfield('ci_allow_edit_selling_price');
	
	$smarty->assign("item", $item);
	$smarty->assign("form", $form);
	//$smarty->assign("show_per", ($form['type']=='over'||$form['type']=='lost')?1:0);
	if(!$form['currency_code'] || $form['currency_code'] == "RM") $smarty->assign("hide_currency_field", 1);

	$ret = array();
	$ret['html'] = $smarty->fetch("consignment_invoice.new.ci_row.single_branch.tpl");
	$ret['ok'] = 1;
	
	print json_encode($ret);
	/*
	$arr = array();
//	if($form['ci_no'] || $form['ci_branch_id'] || $form['open_info']['name'])
		$rowdata = $smarty->fetch("consignment_invoice.new.ci_row.single_branch.tpl");
//	else
//		$rowdata = $smarty->fetch("consignment_invoice.new.ci_row.tpl");
	$arr[] = array("id" => $item['id'], "rowdata" => $rowdata);
	header('Content-Type: text/xml');
	print array_to_xml($arr);*/
}

function get_item_price($sku_item_id, $branch_id, $price_indicate){
	global $con, $smarty, $config;
	//print "price_indicate = $price_indicate";
	$tmp=array();
	//chk the option from user, if get from selling then get from sku_items_price if get from cost then get the price as last time.
	if($price_indicate==3){
		// get last ci price
		$q1=$con->sql_query("select cost_price from ci_items left join ci on ci_id = ci.id and ci_items.branch_id = ci.branch_id where ci.active=1 and ci_items.sku_item_id=".mi($sku_item_id)." and ci_items.branch_id=$branch_id order by ci_items.id desc limit 1");
		$r = $con->sql_fetchrow($q1);
	}
	elseif($price_indicate==2){
		// get selling price
		$q1=$con->sql_query("select price from sku_items_price where sku_item_id=".mi($sku_item_id)." and branch_id=$branch_id");
		$r = $con->sql_fetchrow($q1);
	}
	elseif($price_indicate==1){
		$q2=$con->sql_query("select grn_cost from sku_items_cost where sku_item_id=".mi($sku_item_id)." and branch_id=$branch_id");
		$r = $con->sql_fetchrow($q2);
/*		$q2=$con->sql_query("select round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/uom.fraction,3) as cost
from grn_items
left join uom on uom_id = uom.id
left join sku_items on sku_item_id = sku_items.id
left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
where grn_items.branch_id =$branch_id and grn.approved
and sku_items.id=".mi($sku_item_id)."
having cost > 0
order by grr.rcv_date desc limit 1");
		$r = $con->sql_fetchrow($q2);

		if(!$r){
			$q3=$con->sql_query("select round(po_items.order_price/po_items.order_uom_fraction,3) as cost
from po_items
left join sku_items on sku_item_id = sku_items.id
left join po on po_id = po.id and po.branch_id = po.branch_id
where po.active and po.approved and po_items.branch_id=$branch_id and sku_items.id=".mi($sku_item_id)."
having cost > 0
order by po.po_date desc limit 1");
			$r = $con->sql_fetchrow($q3);
		}

 		if(!$r){
			$q4=$con->sql_query("select cost_price from sku_items where id=".mi($sku_item_id));
			$r = $con->sql_fetchrow($q4);
		}*/
	}
	else{
		$q1=$con->sql_query("select price from sku_items_mprice where sku_item_id=".mi($sku_item_id)." and type=".ms($price_indicate)." and branch_id=$branch_id");
		$r = $con->sql_fetchrow($q1);
	}
	
	if(!$r)	// if no price taken, use default master selling
	{
		$q2=$con->sql_query("select selling_price from sku_items where id=".mi($sku_item_id));
		$r = $con->sql_fetchrow($q2);
	}

	if($config['consignment_modules'] && is_array($config['masterfile_branch_region']) && is_array($config['consignment_multiple_currency']) && $_REQUEST['exchange_rate'] && $_REQUEST['exchange_rate'] != 1){ // if the price is not from cost
		$foreign_cost = $r[0];
		$r[0] = $r[0]*$_REQUEST['exchange_rate'];
	}
	
	$tmp['cost_price'] = round($r[0], 3);
	$tmp['foreign_cost_price'] = round($foreign_cost, 3);
	//echo"<pre>";print_r($tmp);echo"</pre>";
	return $tmp;
}

function insert_tmp_item(&$r,$ci_id, $ci_branch_id, $check=false){
	global $con, $sessioninfo, $branch_id, $config;

	$r['ci_id']=$ci_id;
	$r['branch_id']=$branch_id;
	$r['user_id']=$sessioninfo['id'];
	$r['selling_price_allocation']=serialize($r['selling_price_allocation']);
	$r['selling_price']=doubleval($r['selling_price']);

	// check total items if created PO
	if($config['ci_set_max_items'] && $check){
		$con->sql_query("select count(*) from tmp_ci_items where ci_id = $r[ci_id] and branch_id = $r[branch_id] and user_id = $sessioninfo[id]");
		$t = $con->sql_fetchrow();
		if ($t[0] >= $config['ci_set_max_items']){
	     	return -2;
		}
	}

	// check duplicate items
	if(!$config['ci_allow_duplicate_items'] && $check){
        $con->sql_query("select count(*) from tmp_ci_items where ci_id = $r[ci_id] and branch_id = $r[branch_id] and user_id = $sessioninfo[id] and sku_item_id=$r[sku_item_id]");
        if($con->sql_fetchfield(0)>0)   return -1;
	}

	// get stock balance - latest
	if(!$ci_branch_id)    $ci_branch_id = $branch_id;
	$sql = "select qty from sku_items_cost where branch_id=$ci_branch_id and sku_item_id=$r[sku_item_id]";
	$con->sql_query($sql);
	$r['stock_balance'] = floatval($con->sql_fetchfield(0));

	//print_r($r);die();

    $con->sql_query("insert into tmp_ci_items " . mysql_insert_by_field($r, array('ci_id', 'branch_id', 'user_id', 'sku_item_id','artno_mcode','uom_id','cost_price','foreign_cost_price','ctn','pcs','po_cost', 'selling_price_allocation', 'selling_price','discount','stock_balance','price_type')));
 	$r['id'] = $con->sql_nextid();
 	return $r['id'];
}

function save_tmp_items($alter_to_one_branch=0){
	global $con, $branch_id;
	$form=$_REQUEST;

	if($form['uom_id']){
		foreach($form['uom_id'] as $k=>$v){
			$update = array();
			if($alter_to_one_branch){
				$update['ctn']=mi($form['qty_ctn'][$k][$alter_to_one_branch]);
				$update['pcs']=mi($form['qty_pcs'][$k][$alter_to_one_branch]);
				$update['selling_price']=doubleval($form['selling_price'][$k][$alter_to_one_branch]);
				$update['selling_price_allocation']='';
				$update['ctn_allocation']='';
				$update['pcs_allocation']='';
			}
			else{
/*				if($form['deliver_branch']){
					$update['ctn_allocation']=serialize($form['qty_ctn'][$k]);
					$update['pcs_allocation']=serialize($form['qty_pcs'][$k]);
					//$update['selling_price_allocation']=serialize($form['selling_price'][$k]);
				}
				else{*/

					$update['ctn']=mi($form['qty_ctn'][$k]);
					$update['pcs']=mi($form['qty_pcs'][$k]);
					//$update['selling_price']=doubleval($form['selling_price'][$k]);
					$update['ctn_allocation']='';
					$update['pcs_allocation']='';

				//}
			}
			$update['po_cost']=doubleval($form['po_cost'][$k]);
			$update['cost_price']=$form['cost_price'][$k];
			$update['foreign_cost_price']=$form['foreign_cost_price'][$k];
			$update['uom_id']=$form['uom_id'][$k];
			$update['discount']=$form['discount_per'][$k];
			$update['stock_balance']=$form['stock_balance'][$k];
			$update['selling_price'] = $form['selling_price'][$k];
			$update['price_type'] = $form['price_type'][$k];
			
			// gst
			$update['gst_id'] = $form['gst_id'][$k];
			$update['gst_code'] = $form['gst_code'][$k];
			$update['gst_rate'] = $form['gst_rate'][$k];
			
			// row amount & amount include gst
			$update['item_disc_amt'] = $form['item_disc_amt'][$k];
			$update['item_disc_amt2'] = $form['item_disc_amt2'][$k];
			$update['item_amt'] = $form['item_amt'][$k];
			$update['item_gst'] = $form['item_gst'][$k];
			$update['item_gst_amt'] = $form['item_gst_amt'][$k];
			$update['item_amt2'] = $form['item_amt2'][$k];
			$update['item_gst2'] = $form['item_gst2'][$k];
			$update['item_gst_amt2'] = $form['item_gst_amt2'][$k];
			
			// row foreign amount & amount include gst
			$update['item_foreign_disc_amt'] = $form['item_foreign_disc_amt'][$k];
			$update['item_foreign_disc_amt2'] = $form['item_foreign_disc_amt2'][$k];
			$update['item_foreign_amt'] = $form['item_foreign_amt'][$k];
			$update['item_foreign_gst'] = $form['item_foreign_gst'][$k];
			$update['item_foreign_gst_amt'] = $form['item_foreign_gst_amt'][$k];
			$update['item_foreign_amt2'] = $form['item_foreign_amt2'][$k];
			$update['item_foreign_gst2'] = $form['item_foreign_gst2'][$k];
			$update['item_foreign_gst_amt2'] = $form['item_foreign_gst_amt2'][$k];
			
			$update['display_cost_price_is_inclusive'] = $form['display_cost_price_is_inclusive'][$k];
			$update['display_cost_price'] = $form['display_cost_price'][$k];

			$con->sql_query("update tmp_ci_items set " . mysql_update_by_field($update) . " where id=$k and branch_id=$branch_id");
		}
	}

}

function load_invoice_list($t = 0){
	global $con, $sessioninfo, $smarty, $config;

	if (!$t) $t = intval($_REQUEST['t']);
	if(BRANCH_CODE != 'HQ'){
    	$where = "(ci.ci_branch_id=$sessioninfo[branch_id] or ci.branch_id=$sessioninfo[branch_id] ) and ";
	}

	$start = intval($_REQUEST['s']);
	switch ($t){
	    case 0:
			if ($_REQUEST['s']==''){
				print "<p align=center>I won't search empty string</p>";
				exit;
			}
	        $where .= '(ci.id = ' . mi($_REQUEST['s']) . ' or ci.ci_no like '.ms('%'.$_REQUEST['s']).' or b2.code='.ms($_REQUEST['s']).')';
	        $start = 0;
	        break;

		case 1: // show saved ci
        	$where .= "ci.status = 0 and not ci.approved and ci.active ";
        	break;

		case 2: // show waiting for approval (and Keep In View)
		    $where .= "(ci.status = 1 or ci.status = 3) and not ci.approved and ci.active";
		    break;

		case 3: // show inactive
		   $where .= "(ci.status =4 or ci.status=5) and ci.active";
		    break;

		case 4: // show approved
		    $where .= "ci.approved=1 and ci.active";
		    break;

		case 5: // show rejected
		    $where .= "ci.status = 2 and not ci.approved and ci.active";
		    break;
/*
		case 6: // show checkout
		    $where .= "ci.approved and ci.active";
		    break;
*/
		case 6: // show branch records
			$str = $_REQUEST['str'];

			if (BRANCH_CODE=='HQ' && $config['consignment_modules']){
				$where .= "ci.ci_branch_id=".ms($str);
			}
			break;

	}

	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else
		$sz = 25;
	$con->sql_query("select count(*) from ci left join branch b2 on ci.ci_branch_id = b2.id where $where");
	$r = $con->sql_fetchrow();
	$total = $r[0];

	if ($total > $sz){
	    if ($start > $total) $start = 0;
		// create pagination
		$pg = "<b>Goto Page</b> <select onchange=\"list_sel($t,this.value)\">";
		for ($i=0,$p=1;$i<$total;$i+=$sz,$p++){
			$pg .= "<option value=$i";
			if ($i == $start){
				$pg .= " selected";
			}
			$pg .= ">$p</option>";
		}
		$pg .= "</select>";
		$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
	}

	$q2=$con->sql_query("select ci.*, category.description as dept_name, branch.report_prefix as branch_prefix, branch.code as branch_name_1, b2.code as branch_name_2, bah.approvals, user.u as user_name,bah.approval_order_id
from ci
left join category on ci.dept_id = category.id
left join branch on ci.branch_id = branch.id
left join branch b2 on ci.ci_branch_id = b2.id
left join user on user.id = ci.user_id
left join branch_approval_history bah on bah.id = ci.approval_history_id and bah.branch_id = ci.branch_id
where $where
order by ci.last_update desc, ci.ci_no desc limit $start, $sz");
	
	$is_under_gst = false;
	while ($r2= $con->sql_fetchrow($q2)){
 		$r2['open_info'] = unserialize($r2['open_info']);
		$r2['deliver_branch']=unserialize($r2['deliver_branch']);
		if($r2['deliver_branch']){
			foreach ($r2['deliver_branch'] as $k=>$v){
				$q3=$con->sql_query("select code from branch where id=$v");
				$r3 = $con->sql_fetchrow($q3);
				$r2['d_branch']['id'][$k]=$v;
				$r2['d_branch']['name'][$k]=$r3['code'];
			}
		}
		if($r2['is_under_gst']) $is_under_gst = true;
		$temp2[]=$r2;
	}
	$ci_list=$temp2;
	$smarty->assign("ci_list", $ci_list);
	$smarty->assign("is_under_gst", $is_under_gst);
	$smarty->display("consignment_invoice.list.tpl");
}

function re_export_pos()
{
	// reexport ALL invoice to POS
	global $con;

	set_time_limit(0);
	ini_set('memory_limit', '512M');
	//ob_end_flush();
	$date_from = $_REQUEST['date_from'];
    $date_to = $_REQUEST['date_to'];
    $filter = "where export_pos=1 and active=1 and status=1 and approved=1 and ci_date between ".ms($date_from)." and ".ms($date_to);

    $con->sql_query("select count(*) from ci $filter");
    print "Total ".mi($con->sql_fetchfield(0))." Invoices<br />";
    $con->sql_freeresult();

	$qci = $con->sql_query("select branch_id, id from ci $filter");

	while($a = $con->sql_fetchassoc($qci))
	{
	    print "Exporting $a[branch_id] / $a[id] -- ";
	    export_pos($a['branch_id'], $a['id']);   // call one by one
	    print " -- ".memory_get_usage()."<br />\n";
	}
	print "Done.";
	$con->sql_freeresult($qci);
}

/*function update_inv_sales_cache($bid,$date)
{
	global $con;

	$date = ms($date);

	$con->sql_query("create table if not exists sku_items_sales_cache_b$bid (sku_item_id int, date date, year integer, month integer, amount double, cost double,disc_amt double, qty double, primary key (date,sku_item_id), index(sku_item_id), index(year, month))") or die(mysql_error());
	// sku items
	$con->sql_query("delete from sku_items_sales_cache_b$bid where date=$date") or die(mysql_error());

	$rs = $con->sql_query("select pi.sku_item_id,pi.date,year(pi.date),month(pi.date),sum(price) as amount,sum(sich.grn_cost*abs(pi.qty)) as cost,sum(discount) as disc_amt,sum(pi.qty) as qty
	from pos_items pi
	left join pos on pos.id=pi.pos_id and pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id
	left join sku_items_cost_history sich on sich.branch_id=pi.branch_id and sich.sku_item_id=pi.sku_item_id and sich.date=(select max(date) from sku_items_cost_history sich2 where sich2.branch_id=pi.branch_id and sich2.sku_item_id=pi.sku_item_id and sich2.date<=pi.date)
	where pos.branch_id=$bid and pos.date=$date and pos.cancel_status=0 group by date,sku_item_id");

	$n=0; $query = '';
	$skuitems = array();
    while($r=mysql_fetch_assoc($rs))
	{
		$values = xr($r, $fields);
		if ($n==0)
			$query = "replace into sku_items_sales_cache_b$bid (".join(",", $fields).") values (".join(",",$values).")";
		else
			$query .= ", (".join(",",$values).")";
		$n++;
		$skuitems[] = $r['sku_item_id'];

		if ($n==1000)
		{
	  		print ".";
			$con->sql_query($query) or die(mysql_error());
			$con->sql_freeresult();
	  		$n = 0;
	  		$query = '';
		}
  	}
	if ($query)
	{
		$con->sql_query($query) or die(mysql_error());
		$con->sql_freeresult();
	}

	if ($skuitems) {
		$con->sql_query("update LOW_PRIORITY sku_items_cost set changed=1 where sku_item_id in (".join(",",$skuitems).") and branch_id=$bid");

	}
}*/

function change_lost_inv_branch(){
	global $con, $config;

	$branch_id = mi($_REQUEST['ci_branch_id']);
	//$sku_item_id_list = $_REQUEST['sku_item_id'];
	$inv_type = $_REQUEST['type'];
	//$form_type = mi($_REQUEST['form_type']);
	$is_under_gst = mi($_REQUEST['is_under_gst']);
	
	$con->sql_query("select con_lost_ci_discount,ci_allow_edit_selling_price from branch where id=$branch_id");
	$branch_data = $con->sql_fetchrow();

	$ret['allow_edit_selling_price'] = ($branch_data['ci_allow_edit_selling_price']>0)?1:0;
	if($inv_type=='lost'||$inv_type=='over'){
	//if($_REQUEST['show_per']){
        // get branch lost invoice discount
		$ret['branch_discount'] = $branch_data['con_lost_ci_discount'];
	}


	if($is_under_gst){
		construct_gst_list('supply');
	}

	//if($inv_type=='lost'||$inv_type=='over'){
	        // get sku item discount
	        //$ret['item_discount'] = get_trade_discount($sid,$sessioninfo['branch_id'],$branch_id);
	    //}
	//if(trim(join('',$sku_item_id_list))!=''){
	$form = $_REQUEST;

	if($form['cost_price']){
		foreach($form['cost_price'] as $item_id => $tmp_cost_price){
		    $sid = $form['item_sku_item_id'][$item_id];

		    $item = array();

        	// get selling price
        	$item = get_item_selling($sid, '', $branch_id);

        	// clone selling to price
			$item['cost_price'] = $item['selling_price'];

			// trade discount
			$item_discount = get_trade_discount($sid, $sessioninfo['branch_id'], $branch_id);
			if($item_discount[$sid]){
				$item['item_discount'] = $item_discount[$sid];
			}

	        // stock balance
			$sql = "select sku_item_id,qty from sku_items_cost where branch_id=$branch_id and sku_item_id = $sid";
			$con->sql_query($sql) or die(mysql_error());
			$r = $con->sql_fetchassoc();
			$con->sql_freeresult();

			$item['stock_balance'] = mf($r['qty']);

			// have foreign
			if($config['consignment_modules'] && is_array($config['masterfile_branch_region']) && is_array($config['consignment_multiple_currency']) && $_REQUEST['exchange_rate'] && $_REQUEST['exchange_rate'] != 1){ // if the price is not from cost
				$foreign_cost = $item['cost_price'];
				
				$item['cost_price'] = round($item['cost_price']*$_REQUEST['exchange_rate'], 3);
				$item['foreign_cost_price'] = round($foreign_cost, 3);
			}

        	///////
			// get selling price
            //$sp = get_selling_price($sid,$branch_id);
			
			if($is_under_gst){
				// get sku is inclusive
				$item['is_sku_inclusive'] = $is_sku_inclusive = get_sku_gst("inclusive_tax", $sid);
				//$is_sku_inclusive = $form['display_cost_price_is_inclusive'][$item_id] ? 'yes' : 'no';
				// find original selling price
				// get sku original output gst
				/*$gst_info = get_sku_gst("output_tax", $sid);
				
				$ori_item = array();
				if($gst_info){
					$ori_item['gst_id'] = $gst_info['id'];
					$ori_item['gst_code'] = $gst_info['code'];
					$ori_item['gst_rate'] = $gst_info['rate'];
				}else{ // pre load gst id, code and rate if not found
					$ori_item['gst_id'] = $gst_list[0]['id'];
					$ori_item['gst_code'] = $gst_list[0]['code'];
					$ori_item['gst_rate'] = $gst_list[0]['rate'];
				}

			
				if($is_sku_inclusive == 'yes' && $ori_item['gst_rate']>0){
					// is inclusive tax
					// find the price before tax
					$gst_tax_price = round($item['selling_price'] / ($ori_item['gst_rate']+100) * $ori_item['gst_rate'], 2);
					$item['selling_price'] -= $gst_tax_price;
				}*/

				$item['display_cost_price'] = $item['cost_price'];

				if($form['gst_rate'][$item_id]>0){
					if($is_sku_inclusive == 'yes'){
						// is inclusive tax
						// find the price before tax
						$gst_tax_price = $item['selling_price'] / ($form['gst_rate'][$item_id]+100) * $form['gst_rate'][$item_id];
						//$item['selling_price'] -= $gst_tax_price;
						$item['cost_price'] = $item['selling_price'] - $gst_tax_price;
					}else{
						$gst_amt = round($item['selling_price'] * ($form['gst_rate'][$item_id]/100), $config['global_cost_decimal_points']);
						$item['selling_price'] += $gst_amt;
					}
				}
			}
			
	
			//$ret['selling_price'][$item_id] = $item['selling_price'];
				$ret['item_list'][$item_id] = $item;

		}
	}
	

	print json_encode($ret);
}

function xr($r, &$fields)
{
	$ret = array();
	$fields = array();
	foreach($r as $k=>$v)
	{
	    if (is_numeric($k)) continue;
	    $fields[] = "`$k`";
		$ret[] = ms($v);
	}
	return $ret;
}

function multi_add(){
    global $con, $smarty, $sessioninfo, $config;

    $ci_id = mi($_REQUEST['ci_id']);

    $sku_item_id_list = $_REQUEST['sku_item_id_list'];
    $branch_id = mi($sessioninfo['branch_id']);
    $ci_branch_id = mi($_REQUEST['ci_branch_id']);

    if($sku_item_id_list){
	        $sql = "select si.id,si.sku_item_code,mcode,link_code,description,artno,if(sip.price,sip.price,si.selling_price) as price,if(sip.price,sip.trade_discount_code,sku.default_trade_discount_code) as discount_code,sic.qty
	from sku_items si
	left join sku_items_price sip on si.id=sip.sku_item_id and sip.branch_id=$branch_id
	left join sku_items_cost sic on si.id=sic.sku_item_id and sic.branch_id=$branch_id
	left join sku on sku.id=si.sku_id
	where si.id in (".join(',',$sku_item_id_list).")
	and si.id not in (select sku_item_id from tmp_ci_items where ci_id=$ci_id and user_id=$sessioninfo[id] and branch_id=$branch_id)
	order by description";

	$con->sql_query($sql) or die(mysql_error());

	while($r = $con->sql_fetchrow()){
			$items[$r['id']] = $r;
		}
	}
	$con->sql_freeresult();

	if(is_array($config['masterfile_branch_region']) && is_array($config['consignment_multiple_currency'])){
		$con->sql_query("select region from branch where id = ".mi($_REQUEST['do_branch_id']));
		$branch_info = $con->sql_fetchrow();
		$con->sql_freeresult();

		$smarty->assign('currency_code', $config['masterfile_branch_region'][$branch_info['region']]['currency']);
	}
	
	$smarty->assign('items',$items);
	$smarty->display('consignment_invoice.multi_add.tpl');
}

function save_multi_add(){
   	global $con, $smarty, $LANG, $config,$sessioninfo;
    $branch_id = mi($sessioninfo['branch_id']);

    $form = $_REQUEST;
    $_REQUEST['deliver_branch'] = $form['ci_branch_id'];

    $id = $form['id'];

    $sid_list = $_REQUEST['sid'];

	if(!$sid_list){
	    print "<script>alert('".jsstring($LANG['DO_INVALID_ITEM'])."')</script>";
		exit;
	}

	if($form['is_under_gst']){		
		construct_gst_list('supply');
		
		$q2 = $con->sql_query("select is_export from branch where id = ".mi($form['ci_branch_id'])." and is_export>0");
		$export_info = $con->sql_fetchassoc($q2);
		$con->sql_freeresult($q2);
		
		if($export_info['is_export']>0){
			$export_gst = get_export_type_gst($export_info['is_export']);
		}
	}
	
	init_selection();
	//print_r($form);

	foreach($sid_list as $sku_item_id){
	    $q1=$con->sql_query("select sku_items.artno, sku_items.mcode, sku_items.sku_item_code, sku_items.description as description, if(sku_items.artno is null,sku_items.mcode, sku_items.artno) as artno_mcode, sku_items.id as sku_item_id, uom.id as uom_id, uom.fraction as uom_fraction
from sku_items
left join sku on sku_id = sku.id
left join uom on sku_items.packing_uom_id = uom.id
where sku_items.id=$sku_item_id");
		$item = $con->sql_fetchrow($q1);
		if (!$item) {
			fail(sprintf($LANG['CI_INVALID_ITEM'],$sku_item_id));
		}

		$item['pcs'] = $qty_pcs;

		// no longer need
		//$tmp=get_item_price($sku_item_id, $branch_id, $form['price_indicate']);
		//$item = array_merge($item, $tmp);

		$tmp_sell=get_item_selling($sku_item_id, $form['deliver_branch'], $form['ci_branch_id']);
		$item = array_merge($item, $tmp_sell);

		$item['cost_price'] = $item['selling_price'];
		
		if($config['consignment_modules'] && is_array($config['masterfile_branch_region']) && is_array($config['consignment_multiple_currency']) && $_REQUEST['exchange_rate'] && $_REQUEST['exchange_rate'] != 1){ // if the price is not from cost
			$foreign_cost = $item['cost_price'];
			
			$item['cost_price'] = round($item['cost_price']*$_REQUEST['exchange_rate'], 3);
			$item['foreign_cost_price'] = round($foreign_cost, 3);
		}

		if($form['is_under_gst']){
			// get sku is inclusive
			$is_sku_inclusive = get_sku_gst("inclusive_tax", $sku_item_id);
			// get sku original output gst
			$gst_info = get_sku_gst("output_tax", $sku_item_id);
			
			if($export_gst){
				$item['gst_id'] = $export_gst['id'];
				$item['gst_code'] = $export_gst['code'];
				$item['gst_rate'] = $export_gst['rate'];
			}elseif($gst_info){
				$item['gst_id'] = $gst_info['id'];
				$item['gst_code'] = $gst_info['code'];
				$item['gst_rate'] = $gst_info['rate'];
			}else{ // pre load gst id, code and rate if not found
				$item['gst_id'] = $gst_list[0]['id'];
				$item['gst_code'] = $gst_list[0]['code'];
				$item['gst_rate'] = $gst_list[0]['rate'];
			}
		
			if($is_sku_inclusive == 'yes')	$item['display_cost_price_is_inclusive'] = 1;
			$item['display_cost_price'] = $item['cost_price'];
		
			if($item['gst_rate']>0){
				if($is_sku_inclusive == 'yes'){
					// is inclusive tax
					// find the price before tax
					$gst_tax_price = $item['selling_price'] / ($item['gst_rate']+100) * $item['gst_rate'];
					//$item['selling_price'] -= $gst_tax_price;
					$item['cost_price'] = $item['selling_price'] - $gst_tax_price;
				}else{
					$gst_amt = round($item['selling_price'] * ($item['gst_rate']/100), $config['global_cost_decimal_points']);
					$item['selling_price'] += $gst_amt;
				}
			}
		}
		
		
		//if($form['type']=='lost'||$form['type']=='over'){   // if it is lost invoice, get trade discount count
			$t_discount = get_trade_discount($sku_item_id, $branch_id, $form['ci_branch_id']);
			$item = array_merge($item, $t_discount);
		//}

		$ret=insert_tmp_item($item,$id,$form['ci_branch_id'],true);
		if ($ret==-2){
			fail(sprintf($LANG['CI_MAX_ITEM_CANT_ADD'], $config['ci_set_max_items']));
		}
		$item['selling_price_allocation']=unserialize($item['selling_price_allocation']);

		$smarty->assign("item", $item);
		$smarty->assign("form", $form);
		
		if(!$form['currency_code'] || $form['currency_code'] == "RM") $smarty->assign("hide_currency_field", 1);
    	//$smarty->assign("show_per", ($form['type']=='over'||$form['type']=='lost')?1:0);

		$arr = array();
		//print "<tr bgcolor=\"#ffee99\" onmouseover=\"this.bgColor='#ffffcc';\" onmouseout=\"this.bgColor='';\"  id=\"titem$item[id]\" >";
		$smarty->display("consignment_invoice.new.ci_row.single_branch.tpl");
		//print "</tr>";
	}
}

// UBS Export
function export_inv(){
	global $con,$smarty, $sessioninfo;
	//print_r($_REQUEST);

	$financial_date = sprintf('%04d%02d',$_REQUEST['financial_year'],$_REQUEST['financial_month']);
	//$inv_from = $_REQUEST['inv_from'];
	//$inv_to = $_REQUEST['inv_to'];
	$month = mi($_REQUEST['month']);
	$year = mi($_REQUEST['year']);
	$ci_branch_id = mi($_REQUEST['branch_id']);

	$from_date = $year."-".$month."-1";
	$to_date = $year."-".$month."-".days_of_month($month,$year);

	$filter = array();
	$filter[] = "ci_date between ".ms($from_date)." and ".ms($to_date);
	$filter[] = "ci.active=1 and approved=1 and (export_ubs is null or export_ubs=0)";
	if($ci_branch_id) $filter[] = "ci_branch_id=$ci_branch_id";

	$filter = join(' and ',$filter);

	$sql = "select branch_id,ci_branch_id,ci_date,ci_no,total_amount,1+PERIOD_DIFF(DATE_FORMAT(ci_date,'%Y%m'),$financial_date) as period,branch.code,branch.description, if(ci.exchange_rate is null or ci.exchange_rate = 0 or ci.exchange_rate = '', 1, ci.exchange_rate) as exchange_rate
from ci
left join branch on branch.id=ci.ci_branch_id
where $filter order by ci_no";
	//print $sql;
	
	$con->sql_query($sql) or die(mysql_error());
	$data = $con->sql_fetchrowset();
	$con->sql_freeresult();
	$smarty->assign('data',$data);

	/*header("Content-type: application/msexcel");
	header("Content-Disposition: attachment; filename=glpost9.csv");*/
	if($data){
        if (!is_dir('UBS')) mkdir('UBS',0777);
		file_put_contents('UBS/GLPOST9.CSV',$smarty->fetch('consignment_invoice.export_inv.tpl'));

		$sql = "update ci set export_ubs=1,last_update=last_update
where $filter";
	    $con->sql_query($sql) or die(mysql_error());

	    log_br($sessioninfo['id'], 'Consignment Invoice', 0, "Consignment Invoice Export (Branch id#$_REQUEST[branch_id], Year: $_REQUEST[year], Month: $_REQUEST[month])");

		print "<script>alert('Export successful. (Saved to UBS/GLPOST9.CSV)');</script>";
        print "<script>window.parent.refresh_list_after_ubs_export();</script>";

	}else{
        print "<script>alert('Export Failed. No data to export.');</script>";
	}

	exit;
}

// UBS Reset
function reset_inv(){
	global $con,$smarty, $sessioninfo;

	$month = mi($_REQUEST['month']);
	$year = mi($_REQUEST['year']);
	$ci_branch_id = mi($_REQUEST['branch_id']);

	$from_date = $year."-".$month."-1";
	$to_date = $year."-".$month."-".days_of_month($month,$year);

	$filter = array();
	$filter[] = "ci_date between ".ms($from_date)." and ".ms($to_date);
	$filter[] = "ci.active=1 and approved=1 and export_ubs=1";
	if($ci_branch_id) $filter[] = "ci_branch_id=$ci_branch_id";

	$filter = join(' and ',$filter);

	$sql = "select ci.*
			from ci
			left join branch on branch.id=ci.ci_branch_id
			where $filter order by ci_no";
	//print $sql;
	
	$con->sql_query($sql) or die(mysql_error());
	$data = $con->sql_fetchrowset();
	$con->sql_freeresult();

	if($data){
		$sql = "update ci set export_ubs=0,last_update=last_update where $filter";
	    $con->sql_query($sql) or die(mysql_error());

	    log_br($sessioninfo['id'], 'Consignment Invoice', 0, "Consignment Invoice Reset (Branch id#$_REQUEST[branch_id], Year: $_REQUEST[year], Month: $_REQUEST[month])");

		print "<script>alert('Reset successful.');</script>";
        print "<script>window.parent.refresh_list_after_ubs_export();</script>";
	}else{
        print "<script>alert('Reset Failed. No data to reset.');</script>";
	}

	exit;
}

function toggle_export_ubs_status(){
	global $con, $sessioninfo, $config;
	$id = mi($_REQUEST['id']);
	$update_to_status = mi($_REQUEST['update_to_status']);

	$required_level = $config['ci_toggle_ubs_status_level']? $config['ci_toggle_ubs_status_level']:1000;

	if($sessioninfo['level']<$required_level){
		print "Error: You are not allow to change the status";
	}else{
        $con->sql_query("update ci set export_ubs=$update_to_status ,last_update=last_update where id=$id") or die(mysql_error());
        print "OK";
	}

}

function ajax_search_inv_no(){
	global $con, $sessioninfo, $smarty, $config;

	$inv_no_from = trim($_REQUEST['inv_no_from']);
	$inv_no_to = trim($_REQUEST['inv_no_to']);
	//$sales = mi($_REQUEST['sales']);
	//$lost = mi($_REQUEST['lost']);
	//$over = mi($_REQUEST['over']);

	if(!$inv_no_from||!$inv_no_to) die('No Data');

	$filter = array();
	$filter[] = "ci.active=1 and ci.approved=1";
	$filter[] = "ci.ci_no between ".ms($inv_no_from)." and ".ms($inv_no_to);
	//if($sales)  $type[] = ms('sales');
	//if($lost)  $type[] = ms('lost');
	//if($over)  $type[] = ms('over');
	//$filter[] = "ci.type in (".join(',', $type).")";
	$filter = "where ".join(' and ', $filter);

	$sql = "select ci.*,branch.code as ci_branch_code,branch.description as ci_branch_desc
from ci
left join branch on branch.id=ci.ci_branch_id
$filter order by ci.ci_no";
	$con->sql_query($sql);
	$smarty->assign('ci_list', $con->sql_fetchrowset());
	$con->sql_freeresult();
	$smarty->display('consignment_invoice.multiple_print.list.tpl');
}

function multiple_print(){
	global $con, $sessioninfo, $smarty, $config;

	$ci_list = $_REQUEST['ci_list'];
	if(!$ci_list)   exit;

	foreach($ci_list as $v){
	    $smarty->clear_all_assign();
	    $smarty->assign('config', $config);
	    $smarty->assign('sessioninfo', $sessioninfo);
		list($bid, $id) = explode(",", $v);
		ci_print($id, $bid);
	}
}

function show_per_changed(){
	global $con, $smarty, $sessioninfo;

	$form = $_REQUEST;
	$ci_id = mi($form['id']);
	$branch_id = mi($form['branch_id']);
	$ci_branch_id = mi($form['ci_branch_id']);
	// get sku item_id list
	$con->sql_query("select distinct(sku_item_id) as sid from tmp_ci_items where user_id=".mi($sessioninfo['id'])." and branch_id=$branch_id and ci_id=$ci_id");
	$sku_item_id_arr = array();
	while($r = $con->sql_fetchrow()){
        $sku_item_id_arr[] = $r['sid'];
	}
	//print_r($sku_item_id_arr);
	$con->sql_freeresult();
	$ret = array();
	if($sku_item_id_arr){
		$ret['item_discount'] = get_trade_discount($sku_item_id_arr, $branch_id, $ci_branch_id);
	}
    
    print json_encode($ret);
}

function all_update_mst_price_type(){
	global $con;
	
	$q1 = $con->sql_query("select ci.branch_id,ci.id
						   from ci 
						   join ci_items cii on cii.ci_id = ci.id and cii.branch_id = ci.branch_id
						   where ci.sheet_price_type='' or ci.sheet_price_type is null
						   group by ci.branch_id, ci.id");

	while($r = $con->sql_fetchassoc($q1)){
		update_ci_sheet_price_type($r['branch_id'], $r['id']);
	}

	$con->sql_freeresult($q1);
	print "Done";
}

function all_update_dtl_price_type(){
	global $con;
	
	$q1 = $con->sql_query("select cii.price_type_id,cii.branch_id,cii.id, ci.ci_branch_id, cii.ci_id, cii.sku_item_id
						   from ci_items cii
						   join ci on ci.id = cii.ci_id and ci.branch_id = cii.branch_id
						   where ((cii.price_type='' or cii.price_type is null) or (cii.price_type is not null and cii.price_type != '' and (cii.price_type_id is null or cii.price_type_id = '')))");

	while($r = $con->sql_fetchassoc($q1)){
	    if(!$r['price_type_id']){
			$q2 = $con->sql_query("select if(trade_discount_code,trade_discount_code,default_trade_discount_code) as trade_discount_code
			from sku_items si
			left join sku_items_price sip on branch_id=".mi($r['ci_branch_id'])." and si.id=sip.sku_item_id
			left join sku on sku.id=si.sku_id
			where si.id=".mi($r['sku_item_id']));
			$temp = $con->sql_fetchrow($q2);
			$con->sql_freeresult($q2);

			$con->sql_query("update ci_items set price_type=".ms($temp['trade_discount_code'])." where branch_id=".mi($r['branch_id'])." and ci_id=".mi($r['ci_id'])." and id=".mi($r['id']));
		}else{
			$q2 = $con->sql_query("select * from trade_discount_type where id = ".mi($r['price_type_id']));
			$pt_info = $con->sql_fetchrow($q2);
			$con->sql_freeresult($q2);

			if($pt_info['code']){
				$con->sql_query("update ci_items set price_type = ".ms($pt_info['code'])." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])." and ci_id = ".mi($r['ci_id']));
			}
		}
	}

	$con->sql_freeresult($q1);
	print "Done";
}

function check_tmp_item_exists() {
	global $con, $sessioninfo;
	
	if ($_REQUEST['qty_pcs']) {
		$sql = "select count(*) as c from tmp_ci_items where id in (".join(',',array_keys($_REQUEST['qty_pcs'])).") and branch_id = ".mi($_REQUEST['branch_id'])." limit 1";
		//die($sql);
		$con->sql_query($sql);
		if ($con->sql_fetchfield('c') == count($_REQUEST['qty_pcs'])) print 'OK';
		else print "Error saving document : Probably it is opened & saved before in other window/tab";
		exit;
	}
	else {
		print 'OK';
		exit;
	}
}

?>
