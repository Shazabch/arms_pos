<?php
/*
5/22/2008 6:00:02 PM yinsee
- return "0" and silence instead of popup message saying "No Last PO found for this item";
6/11/2008 1:19:16 PM yinsee
- fix po_ajax_refresh_foc_annotations bug (calling with $_REQUEST[po_id] instead of $id)
6/24/2008 12:56:01 PM yinsee
- fix po bug when single-branch PO issued by HQ got rejected and resubmit, qty become zero. 
9/6/2008 12:50:06 PM  yinsee
- undo the po.ajax_view.tpl changes :))
9/8/2008 4:41:59 PM yinsee
- call update delivered items when view from grr report (ajax=1) --- commented out, dont do yet ^_^
12/31/2008 1:39:15 PM yinsee
- print multiple branch bug -- qty show incorrect
1/6/2009 7:04:06 PM yinsee
- fix approval history (reuse old approval flow)
1/13/2009 1:19:39 PM yinsee
- fix serialize error in branch selling allocation
6/23/2009 5:10 PM Andy
- add checking on $config['po_alt_print_template'] and $config['po_distribution_alt_print_template'] to allow custom print
7/9/2009 12:32:00 PM yinsee
- load sales trend when copy-to-tmp if sales trend is blank
7/30/2009 5:16:33 PM Andy
- Add Reset function
20/10/2009 9:52:00 AM jeff
- add hq purchase po option set in config
2/11/2009 4:25 PM jeff
- add in ini_set('memory_limit', '256M'); set_time_limit(0);
11/9/2009 5:29:31 PM edward
- add report prefix for all log_br
12/14/2009 10:48:08 AM Andy
- add checking on config to allow duplicate PO Item
1/14/2010 4:45:42 PM Andy
- Fix some printing method don't have alternative print config
2/10/2010 5:23:43 PM Andy
- Fix PO cannot confirm problem (bug from got approval history id but no approval history data)
- Fix when adding PO item sometime the line no will get wrong number 
6/17/2010 12:52:38 PM Alex
- Add size color matrix
7/2/2010 4:11:39 PM Alex
- Add $config['document_page_size'] to set limit items per page and fix search bugs

8/5/2010 5:59:37 PM Andy
- PO add IBT checkbox. (Need config)

11/4/2010 6:01:05 PM Alex
- add allow_edit variable while add new row

11/9/2010 12:08:49 PM Andy
- Add checking for canceled/deleted and prevent it to be edit.

2/28/2011 11:42:29 AM Alex
- add checking PO perminission while viewing PO at function po_open()

6/3/2011 1:16:18 PM Andy
- Add show photo at PO.

6/9/2011 6:24:11 PM Justin
- Fixed the bugs where found two fields of "deliver_to" between PO and Branch tables.

9/13/2011 1:16:17 PM Andy
- Fix missing "A.Bal" and "P.Bal" when confirm PO.

10/4/2011 11:47:43 AM Justin
- Added new printing option "GRN Performance Report".

10/6/2011 2:35:35 PM Andy
- Add checking for PO privilege and user active when change PO owner.

11/24/2011 4:48:49 PM Andy
- Add show "Delivered GRN" for those delivered PO.

1/13/2012 6:01:43 PM Justin
- Added an ability to print Size and Color while found user has ticked it.

1/20/2012 - 11:28:43 AM Justin
- Removed the function to retrieve all size & color.
- Modified the size & color report only shows all related items only.

2/28/2012 5:42:22 PM Alex
- add parent stock balance and parent sales trend

3/1/2012 11:42:29 AM Alex
- add scan grn barcode into po

3/21/2012 4:23:43 PM Justin
- Added to filter off those inactive users for user selection.

3/30/2012 5:55:32 PM Justin
- Modified the ajax call when add po item, use JSON instead of XML.
- Modified the ajax call to add last po item during add po item instead of call another ajax to insert last po item.

4/10/2012 10:39:03 AM Andy
- Add show relationship between PO and SO.

4/12/2012 12:01:04 PM Andy
- Fix cancel PO no record at approval history.

4/19/2012 2:47:39 PM Andy
- Add to record approval history when delete PO.

4/24/2012 6:06:32 PM Justin
- Modified to accept "Send Email to Vendor" for those approved PO.

7/24/2012 11:30 AM Justin
- Added to pickup Account ID and Code.

7/25/2012 2:59 PM Justin
- Enhanced to reset account ID from branch Vendor if found is empty.

7/31/2012 11:42:34 AM Justin
- Enhanced to have vendor search on GRN list.

8/10/2012 11:12 AM Andy
- Add purchase agreement control.

8/13/2012 5:38 PM Andy
- Add put po_create_type = 2 for po when generate PO from Purchase Agreement.

10/17/2012 4:47 PM Andy
- Enhance PO to checking when user add BOM Package SKU, it will add the item in bom sku list instead of the bom sku.

10/29/2012 5:15:00 PM Fithri
- PO when save without sku - will show a proper error msg

12/13/2012 3:01 PM Justin
- Bug fixed on system always pickup the existing data while user had already changed it during create New PO with similar vendor and department.

3/1/2013 10:59 AM Fithri
- Bugfix: PO add vendor item, the edited qty will missing
- add vendor item change to ajax method

3/11/2013 4:21 PM Andy
- Add checking to prompt error if no item was selected to add from sku vendor list.

4/4/2013 4:20 PM Fithri
- item last po row, enhance to check < (po date+1 day) and < added
- cost indicate in last po row just show "-"

5/15/2013 1:56 PM Fithri
- scan barcode only allow sku in same department
- scan barcode also check blocked po items

5/22/2013 11:51 AM Justin
- Enhanced to do checking on Uncheckout GRA that will return it as error by vendor settings.

5/28/2013 3:59 PM Justin
- Enhanced to prevent user to add item via barcode as if it is inactive.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/10/2013 10:06 AM Justin
- Enhanced gra script to pickup those waiting for approval items.

7/25/2013 5:04 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window
- use $_SESSION to prevent clash of new document id if it is created at the same second

9/4/2013 3:09 PM Andy
- Remove script manually assign noreply@localhost for mailer->From.

10/1/2013 2:32 PM Justin
- Enhanced to allow user can maintain and send email custom message to vendor.

10/7/2013 1:34 PM Justin
- Bug fixed on delivery and cancellation date could not show out properly while show in multiple delivery branches.
- Bug fixed on PO printing will sum up wrongly for qty and cost when print with multiple delivery branches.

10/1/2013 6:01 PM Fithri
- when sending email, check to trigger send function only when email is set

10/18/2013 4:09 PM Andy
- Change all $mailer->send() to to call phpmailer_send().

11/19/2013 10:06 AM Justin
- Enhanced to remove the skip header on email PO.

12/27/2013 10:29 AM Fithri
- when create PO at branch allow to have user selection to send PM/email

3/19/2014 5:56 PM Justin
- Bug fixed on block and inactive barcode scan did not function properly.

6/3/2014 12:00 PM Fithri
- able to set report logo by branch (use config)

6/6/2014 6:08 PM Justin
- Enhanced to auto assign the sender as current logged on user while send PO to vendor.

7/25/2014 11:30 AM Justin
- Bug fixed on add item by barcode will not check user's department.

7/31/2014 5:39 PM Justin
- Enhanced PO to use mandrillapp.com to send email.

8/20/2014 2:12 PM Justin
- Enhanced the po_send_email to use current logged-in user as sender (so vendor can reply according to this email) while found it is set on email config.

9/17/2014 11:17 AM Justin
- Bug fixed on sku add from vendor will cause unable to add remark or additional remark.

11/8/2014 10:40 AM Justin
- Enhanced to have GST calculation and settings.

12/9/2014 1:38 PM Justin
- Bug fixed while add item from vendor SKU and the list is exceeded maximum items, system had notified user with wrong message.

3/10/2015 11:54 AM Justin
- Bug fixed on total amount from report sum up twice.

3/11/2015 10:31 AM Justin
- Bug fixed on GST selling price capture as zero while approve single branch delivery.
- Bug fixed on GST amount sum up twice while printing report.

3/30/2015 3:53 PM Justin
- Enhanced the report to show N.S.P and S.S.P when PO under GST status.

4/6/2015 10:39 AM Justin
- Bug fixed on revoke feature does not pickup GST info.

4/7/2015 4:40 PM Andy
- Fix add item from vendor don't have S.S.P.

1:44 PM 4/8/2015 Andy
- Fix normal add item cannot get SSP and NSP.
- Fix create po from same vendor no GST info.

4/15/2015 2:17 PM Justin
- Bug fixed on GST selling price does not update to tmp table once the page have been refreshed.

4/17/2015 1:47 PM Justin
- Bug fixed on selling price will get lesser and lesser if keep swap between vendor with GST.

4/22/2015 3:40 PM Justin
- Bug fixed on report printing some times will sum up double for total amount.

4/24/2015 2:30 PM Justin
- Bug fixed on PO will not calculate GST while the PO is from not GST become GST status.

5/15/2015 10:52 AM Justin
- Enhanced to calculate total selling include GST.
- Bug fixed on total amount could not calculate separately by branch.

5/20/2015 3:38 PM Justin
- Bug fixed on total amount did not round to 2 decimal points.

5/22/2015 10:01 AM Justin
- Enhanced to filter off those po items with zero qty while printing report.
- Bug fixed for from branch information show wrongly.

7/27/2015 9:26 AM Joo Chia
- Change to show page with error message instead of alert if print PO with total quantity zero.

8/21/2015 10:10 AM Justin
- Bug fixed on GRN performance report will print empty rows if GRR contains PO.

8/28/2015 4:28 PM Andy
- Fix "Send Email To Vendor" bug when po is multiple branches.

11/11/2015 13:35 AM DingRen
- Fix PO HQ Payment error when only one branch selected

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

01/29/2016 14:17 Edwin
- Bug fixed on NSP and SSP value error when add item in PO

03/08/2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

4/13/2016 3:33 PM Andy
- Fix revoked PO does not have last po row.

6/3/2016 12:00 PM Andy
- Enhanced to compatible with php7.

06/22/2016 15:50 Edwin
- Show price before tax in S.S.P when vendor is not under GST

07/01/2016 15:30 Edwin
- Enhanced on user able to view although they don't have official module.

07/18/2016 16:30 Edwin
- Enhanced on delivery date format changed to YYYY-MM-DD.

12/6/2016 9:39 AM Andy
- Hide ARMS user from user list.

1/11/2017 3:11 PM Andy
- Enhanced to check gst selling price when branch is under gst.

1/17/2017 10:05 AM Andy
- Fixed HQ Distribution List load wrong po.

2/8/2017 4:04 PM Andy
- Fixed to auto reload additional delivery branch selling price when user click refresh.

3/2/2017 10:57 AM Andy
- Enhanced to auto recalculate all PO Amount using reCalcatePOUsingOldMethod() when saved, revoke and approve multiple branch PO.

3/27/2017 3:01 PM Andy
- Change reCalcatePOUsingOldMethod() to reCalcatePOAmt().

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

11/8/2017 10:05 AM Justin
- Bug fixed on PO doesn't show "SSP" and some general information doesn't carry forward while using the "Create New PO with similar vendor and department".
- Bug fixed on qty and foc does not insert when adding PO item using matrix from single branch delivery.

12/21/2017 10:12 AM Andy
- Fixed scan barcode check department bug.

4/4/2018 11:38 AM Andy
- Added Foreign Currency feature.

6/26/2018 12:24 PM Andy
- Fixed NSP and SSP column bug.

10/25/2018 4:12 PM Andy
- Change the Document email to vendor to have PO no.

11/7/2018 3:06 PM Justin
- Enhanced to display remarks and total at last page instead of every page.

5/21/2019 11:13 AM Andy
- Fixed send po by email item_no bugs.
- Enhanced send po by email to attach po document as pdf.

6/19/2019 11:42 AM William
- Pick up "vertical_logo" and "vertical_logo_no_company_name" from Branch for logo and hide company name setting.
- Pick up "setting_value" from system_settings for logo setting.

9/24/2019 10:36 AM William
- Enhanced po module row amount show warning when use pda add or edit po.

11/28/2019 4:31 PM William
- Fixed bug when change the url "view" to "open" and the po is not able to edit, system will show error message and redirect to home page.

12/6/2019 2:34 PM William
- Change error message "This PO belongs to other branch" to "You cannot edit other branch PO"

12/6/2019 3:41 PM William
- Added new "Category Sales Trend" and display when PO has po item.

2/4/2020 9:40 AM William
- Fixed po_branch_id not save when using sub branch.

2/11/2020 10:21 AM Andy
- Enhanced to include server name in PO Send Email to Vendor's title.

4/15/2020 2:01 PM William
- Enhanced to block confirm when got config "monthly_closing" and document date has closed.
- Enhanced to block save, cancel and revoke when got config "monthly_closing_block_document_action" and document date has closed.

11/4/2020 9:00 AM William
- Enhance to have upload po item by csv feature.

11/13/2020 3:17 PM William
- Change po item export file name to po_export_(po no).

01/08/2021 1:40 PM Rayleen
- Modified export_po_item() 
- Fix bug when PO Option = HQ purchase (HQ Payment), ctn and pcs is zero - use po_items.ctn and po_items.qty_loose_allocation
- Add option to merge all delivery branch in one csv file or export each branch in a csv file then download them as zip file
- Add delivery branch column in csv file

1/15/2021 2:05 PM Andy
- Fixed print po checklist blank if po only have ctn but no pcs.

3/3/2021 2:02 PM Andy
- Fixed print PO ctn same as qty bug.

3/23/2021 3:25 PM Ian
-Enhanced to check Block GRN, if got block grn, then not allow to add item & foc item/unable to enter qty.
-Enhanced to check Block PO on SKU input.
-Enhanced to Check Block GRN/PO and show error (blocked Item) when adding item using csv.
-Retain the value of blocking state(disabled qty) if page is refreshed
*/

// po_create_type = 1; // create from sales order
// po_create_type = 2; // create from purchase agreement

ini_set('memory_limit', '256M');
set_time_limit(0);
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO') && !privilege('PO_VIEW_ONLY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO/PO_VIEW_ONLY', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules'] && $_REQUEST['a'] != 'view') js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
include("po.include.php");
$smarty->assign("PAGE_TITLE", "Purchase Order");
$branch_id = intval($_REQUEST['branch_id']);
if ($branch_id ==''){
	$branch_id = $sessioninfo['branch_id'];
}
init_selection();
get_allowed_user_list();
$id = intval($_REQUEST['id']);

$headers = array(
	'1' => array("item_code" => "Item Code", "order_cost" => "Order Cost", "pcs" => "Pcs"),
	'2' => array("item_code" => "Item Code", "order_cost" => "Order Cost", "hq_pcs" => "HQ-PCS", "dev_pcs" => "DEV-PCS")
);

$sample = array(
	'1' => array(
		array("285020940000", "0.9500", "5"),
		array("284357220000", "","3")
	),
	'2' => array(
		array("285020940000", "0.9500", "3", "1"),
		array("284357220000", "","3", "2")
	)
);

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
		
		case 'open':
			$con->sql_query("delete from tmp_po_items where (po_id>1000000000 and po_id<".strtotime('-1 day').") and user_id=$sessioninfo[id]");
		case 'refresh':
			if (!privilege('PO')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO', BRANCH_CODE), "/index.php");
			save_and_update_po_items();
			po_open($id, $branch_id);
			exit;
		case 'view':		
			po_view($id, $branch_id);
			exit;
		
		case 'ajax_load_po_list':
			load_po_list();
			exit;
		case 'cancel':
			po_cancel($id, $branch_id);
			break;
		case 'delete':			
			po_delete($id, $branch_id);
		    break;
		    
		case 'ajax_add_last_po_row':
			po_ajax_add_last_po_row();		
			exit;
		case 'ajax_add_foc_row':						
		case 'ajax_add_po_row':
			po_ajax_add_po_row($id, $branch_id, ($_REQUEST['a']=='ajax_add_foc_row'));
			exit;

		case 'ajax_sel_foc_cost':
			po_ajax_sel_foc_cost();
		    exit;
		case 'ajax_update_foc_row':
			po_ajax_update_foc_row();
			exit;
		case 'ajax_refresh_foc_annotations':
			po_ajax_refresh_foc_annotations(mi($_REQUEST['po_id']), $branch_id);
			exit;

		case 'ajax_add_vendor_sku':
			po_ajax_add_vendor_sku($id, $branch_id);
			exit;
		case 'ajax_show_related_sku':
			po_ajax_show_related_sku($id, $branch_id);
			exit;
		case 'ajax_add_size_color':
		    po_ajax_add_size_color($id,$branch_id);
		    exit;
		case 'chown':
			po_chown($id, $branch_id);
			exit;
		case 'revoke':
			po_revoke($id, $branch_id);
			break;

	    case 'ajax_delete_po_row':
	    	ajax_delete_po_row();
			exit;

		case 'ajax_expand_sku':
			if (!isset($_REQUEST['showheader'])) $smarty->assign("hideheader",1);
			$smarty->assign("show_varieties",0);
		    expand_sku(intval($_REQUEST['sku_id']));
		    exit;

		case 'ajax_show_vendor_sku':
			$smarty->assign("show_varieties",1);
		    get_vendor_sku("po.new.show_sku.tpl");
		    exit;
        case 'print_distribution':
			po_print_distribution($id, $branch_id);
			exit;

      	case 'print':
      		po_print($id, $branch_id);
      		exit;

		case 'confirm':
		case 'save':
			po_save($id, $branch_id, ($_REQUEST['a']=='confirm'));
			exit;	
        case 'do_reset':
		    do_reset($id,$branch_id);
		    exit;
        case 'check_tmp_item_exists':
		    check_tmp_item_exists();
		    exit;
		case 'po_recalculate_old':
			$appCore->poManager->reCalcatePOUsingOldMethod($_REQUEST['branch_id'], $_REQUEST['po_id']);
			exit;
		case 'ajax_reload_currency_rate':
			ajax_reload_currency_rate();
			exit;
		/*case 'send_rate_his':
			$result = $appCore->poManager->sendCurrencyRateChangedNotification(1, 707);
			print_r($result);
			exit;*/
		case 'ajax_open_add_item_by_csv_popup':
			ajax_open_add_item_by_csv_popup();
			exit;
		case 'download_sample_po':
			download_sample_po();
			exit;
		case 'show_result':
			show_result();
			exit;
		case 'ajax_get_uploaded_csv_result':
			ajax_get_uploaded_csv_result();
			exit;
		case 'ajax_import_po_items':
			ajax_import_po_items();
			exit;
		case 'export_po_item':
			export_po_item();
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;	
	}
}
$smarty->display("po.home.tpl");
exit;
function po_open($po_id, $branch_id){
	global $con, $smarty, $LANG, $sessioninfo, $config, $input_gst_list, $output_gst_list, $vendor_gst_list, $appCore;
	$form=$_REQUEST;

	//is new PO
	if ($po_id==0){
		if($config['enable_po_agreement']){	// got use purchase agreement
			if(!privilege('PO_AGREEMENT_OPEN_BUY')){
				js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_AGREEMENT_OPEN_BUY', BRANCH_CODE), "/index.php");
			}
		}
		$po_id=time();
		if($po_id <= $_SESSION['po_last_create_time']) {$po_id = $_SESSION['po_last_create_time']+1;}
		$_SESSION['po_last_create_time'] = $po_id;
		$form['id']=$po_id;
		if(BRANCH_CODE != 'HQ') $form['po_branch_id'] = $branch_id;
	}
	
	//if($config['enable_po_agreement'] && !privilege('PO_AGREEMENT_OPEN_BUY')){
	//	$smarty->assign("readonly", 1);
	//}
	//if the action is open and is not a NEW PO
	if ($form['a']=='open' && !is_new_id($po_id)){		
		//get Existing PO header
		$form=load_po_header($po_id, $branch_id);
		
		//invalid PO
		if (!$form){
		    $smarty->assign("url", "/po.php");
		    $smarty->assign("title", "Purchase Order");
		    $smarty->assign("subject", sprintf($LANG['PO_NOT_FOUND'], $po_id));
		    $smarty->display("redir.tpl");
		    exit;
		}

		// check PO permission
		if(!$sessioninfo['departments'])
			$depts="(0)";
		else
			$depts="(" . join(",", array_keys($sessioninfo['departments'])) . ")";

		if($sessioninfo['level']>=9999)
			$owner_check="";
		elseif($sessioninfo['level']>=800)
			$owner_check=" and (po.department_id in $depts)";
		elseif($sessioninfo['level']>=400)
			$owner_check=" and ((po.branch_id=$sessioninfo[branch_id] or po_branch_id=$sessioninfo[branch_id]) and po.department_id in $depts)";
		else
			$owner_check=" and (user_id=$sessioninfo[id] or allowed_user like '%$sessioninfo[id]%')";
		//print "select * from po where id=$po_id $owner_check<br/>";
		$con->sql_query("select * from po where id=$po_id $owner_check");

		if ($con->sql_numrows()>0)  $can_open=true;
		else    $can_open=false;

		if (!$can_open){
		    $smarty->assign("url", "/po.php");
		    $smarty->assign("title", "Purchase Order");
		    $smarty->assign("subject", sprintf($LANG['PO_NO_ACCESS'], $po_id));
		    $smarty->display("redir.tpl");
		    exit;
		}
		//if the PO oredi submit and not the reject PO, goto view only.
		elseif($form['status'] && $form['status']!=2){
			po_view($po_id, $branch_id);
			exit;
		}
		
		//check saved po
		if($form['status']== 0){
			if($form['branch_id']!= $sessioninfo['branch_id']){
				$err_msg = sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "PO");
				js_redirect($err_msg, "/index.php");
			}
			if($form['user_id']!= $sessioninfo['id']){
				$err_msg = "This PO is only can edit by onwner";
				js_redirect($err_msg, "/index.php");
			}
		}
		copy_to_tmp($po_id, $branch_id);
	}
	//IF THE PO IS NEW OR REFRESH
	else{
		$form['id']=$po_id;
		$form['branch_id']=$branch_id;
		$po_branch_id_list = array();
		if(BRANCH_CODE != 'HQ'){
			$po_branch_id_list[] = $sessioninfo['branch_id'];
		}elseif($form['deliver_to']){
			//get po deliver_to branch id
			$po_branch_id_list = $form['deliver_to'];
		}
		if($form['department_id'] && $po_branch_id_list){
			$form['category_sales_trend'] = load_category_sales_trend($form['department_id'], $po_branch_id_list);
		}

		if(!$form['po_date'])	$form['po_date'] = date("Y-m-d");
		$form['is_under_gst'] = 0;
		if(!isset($form['pending_currency_rate']) && $config['foreign_currency'])	$form['pending_currency_rate'] = -1;
		
		// check gst status
		if($config['enable_gst'] && !$form['currency_code']){
			$prms = array();
			$prms['vendor_id'] = $form['vendor_id'];
			$prms['date'] = $form['po_date'];
			$form['is_under_gst'] = check_gst_status($prms);
		}
		
//		$form['po_date']=dmy_to_sqldate($form['po_date']);
		
		$branch_is_under_gst = 0;
		if($config['enable_gst']){
			$prms = array();
			$prms['branch_id'] = $branch_id;
			$prms['date'] = $form['po_date'];
			$branch_is_under_gst = $form['branch_is_under_gst'] = check_gst_status($prms);
		}
		//Ian editting 
		

		//COPY from existing PO (same header)
		if (isset($form['copy_id'])){
		    $copy_id=intval($form['copy_id']);
		    
		    // copy header from old PO			
		    $q_form=$con->sql_query("select po.branch_id, po.vendor_id, po.department_id, po.po_option, po.allowed_user, po.deliver_to, po.delivery_vendor, vendor.description as vendor, branch.code as branch, branch.report_prefix, po.is_under_gst, po.po_branch_id, po.delivery_date, po.cancel_date
from po 
left join vendor on vendor_id=vendor.id  
left join branch on branch_id=branch.id 
where po.id=$copy_id and branch_id=$branch_id");
			$form=$con->sql_fetchassoc($q_form);
			$con->sql_freeresult($q1);
			$form['branch_is_under_gst'] = $branch_is_under_gst;
			
			if(isset($_REQUEST['dept_id'])) $form['department_id'] = $_REQUEST['dept_id'];
			if(isset($_REQUEST['is_ibt'])) $form['is_ibt'] = $_REQUEST['is_ibt'];
			if(isset($_REQUEST['vendor_id'])) $form['vendor_id'] = $_REQUEST['vendor_id'];
			if(isset($_REQUEST['po_date'])) $form['po_date'] = $_REQUEST['po_date'];
			else	$form['po_date'] = date("Y-m-d");
			
			if(isset($_REQUEST['po_option'])) $form['po_option'] = $_REQUEST['po_option'];
			if(isset($_REQUEST['deliver_to'])) $form['deliver_to'] = $_REQUEST['deliver_to'];
   			else{
				$form['deliver_to']=unserialize($form['deliver_to']);
				if(!is_array($form['deliver_to'])){ // it is single branch delivery, need to get branch code 
					$form['po_branch'] = get_branch_code($form['po_branch_id']);
				}
			}
			if(isset($_REQUEST['allowed_user'])) $form['allowed_user'] = $_REQUEST['allowed_user'];
			else $form['allowed_user']=unserialize($form['allowed_user']);
			if(isset($_REQUEST['delivery_date'])) $form['delivery_date'] = $_REQUEST['delivery_date'];
			elseif(is_array($form['deliver_to'])) $form['delivery_date']=unserialize($form['delivery_date']);
			if(isset($_REQUEST['cancel_date'])) $form['cancel_date'] = $_REQUEST['cancel_date'];
			elseif(is_array($form['deliver_to'])) $form['cancel_date']=unserialize($form['cancel_date']);
			$form['id']=$po_id;
			if(isset($_REQUEST['delivery_vendor'])) $form['delivery_vendor'] = $_REQUEST['delivery_vendor'];
			else $form['delivery_vendor']=unserialize($form['delivery_vendor']);
			
			if(is_array($form['deliver_to'])){
				foreach($form['deliver_to'] as $k=>$v){
					$form['delivery_date'][$v] = dmy_to_sqldate($form['delivery_date'][$v]);
					$form['cancel_date'][$v] = dmy_to_sqldate($form['cancel_date'][$v]);
					
					$q_usr=$con->sql_query("select user_id,u from user_privilege
left join user on user_id=user.id
where privilege_code='PO_VIEW_ONLY' and branch_id=$v and user.active = 1 and user.is_arms_user=0");
					while($usr=$con->sql_fetchrow($q_usr)){
						$temp['user'][]=$usr['u'];
						$temp['user_id'][]=$usr['user_id'];
					}
					$user_list[$v]=$temp;
					$temp='';
				}
				$smarty->assign("user_list",$user_list);
			}
			else {
				$form['delivery_date'] = dmy_to_sqldate($form['delivery_date']);
				$form['cancel_date'] = dmy_to_sqldate($form['cancel_date']);
				
				$user_list = array();
				$q_usr=$con->sql_query("select user_id,u from user_privilege left join user on user_id=user.id where privilege_code='PO_VIEW_ONLY' and branch_id=".mi($branch_id)." and user.active = 1 and user.is_arms_user=0");
				while($usr=$con->sql_fetchrow($q_usr)){
					$temp['user'][]=$usr['u'];
					$temp['user_id'][]=$usr['user_id'];
				}
				$user_list[$branch_id] = $temp;
				$temp='';
				$smarty->assign("user_list",$user_list);
			}

			if (is_array($form['delivery_vendor'])){
				foreach ($form['delivery_vendor'] as $bid=>$vid){
					$q_ven=$con->sql_query("select description from vendor where id = " . mi($vid));
					$ven=$con->sql_fetchrow($q_ven);
					$form['delivery_vendor_name'][$bid]=$ven[0];
				}
			}
		}
		
		//get the latest sales trend and selling_price when REFRESH
		$q1=$con->sql_query("select * from tmp_po_items where po_id=$po_id and branch_id=$branch_id and user_id=$sessioninfo[id]");
		while($r1=$con->sql_fetchrow($q1)){
			$temp['sales_trend'] = serialize(get_sales_trend($r1['sku_item_id']));
			$temp['stock_balance'] = serialize(get_stock_balance($r1['sku_item_id']));	
			$con->sql_query("update tmp_po_items set sales_trend='$temp[sales_trend]', stock_balance='$temp[stock_balance]' where id = $r1[id]");
			
			if($form['branch_is_under_gst']){
				if(!$r1['selling_gst_id'] || !$config['enable_get_last_gst_info']){ // check to get selling GST info
					$output_gst = get_sku_gst("output_tax", $r1['sku_item_id']);
					if($output_gst){
						$r1['selling_gst_id'] = $output_gst['id'];
						$r1['selling_gst_code'] = $output_gst['code'];
						$r1['selling_gst_rate'] = $output_gst['rate'];
					}else{
						$r1['selling_gst_id'] = $output_gst_list[0]['id'];
						$r1['selling_gst_code'] = $output_gst_list[0]['code'];
						$r1['selling_gst_rate'] = $output_gst_list[0]['rate'];
					}
				}
				
				//// if found got set special vendor gst code, then all items must default choose it
				//if($vendor_gst_list[$form['vendor_id']]['gst_register'] > 0){
				//	$vd_gst = $vendor_gst_list[$form['vendor_id']]['gst_register'];
				//	foreach($input_gst_list as $tmp_gst_info){
				//		if($tmp_gst_info['id'] == $vd_gst){
				//			$r1['cost_gst_id'] = $tmp_gst_info['id'];
				//			$r1['cost_gst_code'] = $tmp_gst_info['code'];
				//			$r1['cost_gst_rate'] = $tmp_gst_info['rate'];
				//			break;
				//		}
				//	}
				//}
				//
				//if(!$r1['cost_gst_id'] || !$config['enable_get_last_gst_info']){ // check to get cost GST info
				//	$input_gst = get_sku_gst("input_tax", $r1['sku_item_id']);
				//	if($input_gst){
				//		$r1['cost_gst_id'] = $input_gst['id'];
				//		$r1['cost_gst_code'] = $input_gst['code'];
				//		$r1['cost_gst_rate'] = $input_gst['rate'];
				//	}else{
				//		$r1['cost_gst_id'] = $input_gst_list[0]['id'];
				//		$r1['cost_gst_code'] = $input_gst_list[0]['code'];
				//		$r1['cost_gst_rate'] = $input_gst_list[0]['rate'];
				//	}
				//}
			}
			
			if (is_array($form['deliver_to'])){
				unset($temp['selling_price_allocation']);
				$r1['selling_price_allocation']=unserialize($r1['selling_price_allocation']);
				if($form['branch_is_under_gst']){
					unset($temp['gst_selling_price_allocation']);
					$r1['gst_selling_price_allocation']=unserialize($r1['gst_selling_price_allocation']);			
				}
				foreach($form['deliver_to'] as $k=>$v){
					$s_price=$r1['selling_price_allocation'][$v];
					if(!$s_price){
						$r2=get_selling_price($r1['sku_item_id'],$v);
						if($r2['price_sip'])
							$temp['selling_price_allocation'][$v]=strval($r2['price_sip']);
						else
							$temp['selling_price_allocation'][$v]=strval($r2['price_si']);
					}
					else{
						$temp['selling_price_allocation'][$v]=strval($s_price);
					}

					if($form['branch_is_under_gst']){
						$temp['gst_selling_price_allocation'][$v] = $r1['gst_selling_price_allocation'][$v];
						if($r1['gst_selling_price_allocation'][$v] <= 0){
							if($temp['selling_price_allocation'][$v]){
								$prms = array();
								$prms['selling_price'] = $temp['selling_price_allocation'][$v];
								$prms['inclusive_tax'] = get_sku_gst("inclusive_tax", $r1['sku_item_id']);
								$prms['gst_rate'] = $r1['selling_gst_rate'];
								$gst_sp_info = calculate_gst_sp($prms);
								//$temp['gst_selling_price_allocation'][$v] = ms(round($gst_sp_info['gst_selling_price'], 2));
								
								if($prms['inclusive_tax'] == "yes"){
									$temp['gst_selling_price_allocation'][$v] = strval($temp['selling_price_allocation'][$v]);
									$temp['selling_price_allocation'][$v] = strval(round($gst_sp_info['gst_selling_price'], 2));
								}else{
									$temp['gst_selling_price_allocation'][$v] = strval($gst_sp_info['gst_selling_price']);
								}
							}
						}
					}
				}
				
				$upd = array();
				$upd['selling_price_allocation']=serialize($temp['selling_price_allocation']);
				if($temp['gst_selling_price_allocation']) $upd['gst_selling_price_allocation']=serialize($temp['gst_selling_price_allocation']);
				//print_r($upd);
				$con->sql_query("update tmp_po_items set ".mysql_update_by_field($upd)." where id=".mi($r1['id'])." and branch_id = ".mi($branch_id));
			}else{
				if($form['branch_is_under_gst'] && !$r1['gst_selling_price']){
					$prms = array();
					$prms['selling_price'] = $r1['selling_price'];
					$prms['inclusive_tax'] = get_sku_gst("inclusive_tax", $r1['sku_item_id']);
					$prms['gst_rate'] = $r1['selling_gst_rate'];
					$gst_sp_info = calculate_gst_sp($prms);
					
					$upd = array();
					if($prms['inclusive_tax'] == "yes"){
						$upd['gst_selling_price'] = $r1['selling_price'];
						$upd['selling_price'] = $gst_sp_info['gst_selling_price'];
					}else{
						$upd['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					}

					$con->sql_query("update tmp_po_items set ".mysql_update_by_field($upd)." where id=".mi($r1['id'])." and branch_id = ".mi($branch_id));
				}
			}
		}		
		// got turn on currency
		if($config['foreign_currency']){
			// load Currency Code List
			$appCore->poManager->loadPOCurrencyCodeList($form, array('smarty_assign'=>'foreignCurrencyCodeList'));
		}
		
		// IS NEW
		$smarty->assign('can_change_currency_code', 1);
		
		//print_r($form);
	}
	
	if(is_array($form['deliver_to']) && $form['po_option'] == 3){
		foreach($form['deliver_to'] as $k=>$v){
			$form['delivery_date'][$v] = $form['hq_delivery_date'];
			$form['cancel_date'][$v] = $form['hq_cancel_date'];
			$form['partial_delivery'][$v] = $form['hq_partial_delivery'];
		}
	}
	
	//print_r($form);exit;
	//echo"<pre>";print_r($form);echo"</pre>";	
	$smarty->assign("po_items", load_po_items($form, true));
	$smarty->assign("form", $form);
	$smarty->display("po.new.tpl");
}
function po_view($po_id, $branch_id){
	global $smarty, $LANG;

	$form=load_po_header($po_id, $branch_id);			
	if (!$form){
		if ($_REQUEST['ajax']) die(sprintf($LANG['PO_NOT_FOUND'], $po_id));
	    $smarty->assign("url", "/po.php");
	    $smarty->assign("title", "Purchase Order");
	    $smarty->assign("subject", sprintf($LANG['PO_NOT_FOUND'], $po_id));
	    $smarty->display("redir.tpl");
	    exit;
	}
	// var_dump('<pre>');
	// var_dump(load_po_items($form));
	// var_dump('</pre>');

	$smarty->assign("readonly", 1);
	$smarty->assign("po_items", load_po_items($form));
	$smarty->assign("form", $form);
	if ($_REQUEST['ajax']) 
	{

		/*global $con;
		require_once('goods_receiving_note.include.php');
		$con->sql_query("select po_no from po where id=".mi($po_id)." and branch_id=".mi($branch_id));
		if ($po = $con->sql_fetchrow()) update_po_receiving_count($po[0]);*/
		$smarty->display("po.ajax_view.tpl");	
	}
	else		
		$smarty->display("po.new.tpl");	
}
function po_print($po_id, $branch_id){
	global $con, $smarty, $LANG,$config,$sessioninfo, $appCore;
	
	if (isset($_REQUEST['load'])){
		$form=load_po_header($po_id, $branch_id);
		$po_items=load_po_items($form);
	}
	else{
		$form=$_REQUEST;
		if($form['readonly']){
			$po_items=load_po_items($form);
		}
		else{
			save_and_update_po_items();
			$po_items=load_po_items($form, true);
		}
	}

	//get admin logo system_settings and branch logo setting 
	$system_settings = array();
	$setting_list = array('logo_vertical', 'verticle_logo_no_company_name');
	foreach($setting_list as $setting_name){
		$q1 = $con->sql_query("select setting_value from system_settings where setting_name=".ms($setting_name));
		$r = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$system_settings[$setting_name] = $r['setting_value'];
	}
	$qry1 = $con->sql_query("select is_vertical_logo,vertical_logo_no_company_name from branch where id=$branch_id");
	$r1 = $con->sql_fetchassoc($qry1);
	$con->sql_freeresult($qry1);
	if($r1['is_vertical_logo'] == 1){
		$system_settings['verticle_logo_no_company_name'] = $r1['vertical_logo_no_company_name'];
		$system_settings['logo_vertical'] = $r1['is_vertical_logo'];
	}
	$smarty->assign("system_settings",$system_settings);

	if($_REQUEST['print_vendor_copy'] || $_REQUEST['print_branch_copy'] || $_REQUEST['print_sz_clr']){
		//get vendor payment term
		$form['payment_term']=get_payment_term($form['vendor_id'],$branch_id);			
		
		$total=$smarty->get_template_vars("total");
		if($total['qty']+$total['foc']<=0){
			//print "<script>alert('$LANG[PO_PRINT_ZERO_QTY]');</script>\n";
			display_redir($_SERVER['PHP_SELF'], "Purchase Order", sprintf($LANG['PO_PRINT_ZERO_QTY']));
			exit;
		}
		
		$con->sql_query("select description from category where id = " . mi($form['department_id']));
		$r=$con->sql_fetchrow();
		$form['department'] = $r[0];
		$con->sql_query("select fullname from user where id = " . mi($form['user_id']));
		$r = $con->sql_fetchrow();
		$form['fullname'] = $r[0];
		if (!$form['approved']){
			$con->sql_query("select report_prefix from branch where id = $branch_id");
			$report_prefix = $con->sql_fetchrow();
			$smarty->assign("report_prefix", $report_prefix[0]);
			if ($form['status']==0)
				$form['po_no'] = sprintf("%s%05d(DP)",$report_prefix[0],$form['id']);		
			else
				$form['po_no'] = sprintf("%s%05d(PP)",$report_prefix[0],$form['id']);		
		}
		
		if (!is_array($form['deliver_to'])){
			$smarty->assign("form", $form);
			$con->sql_query("select * from vendor where id = " . mi($form['vendor_id']));
			$vd = $con->sql_fetchrow();
			$con->sql_query("select * from branch_vendor where vendor_id = " . mi($form['vendor_id']) . " and branch_id = " . mi($form['branch_id']));
			if ($vdb = $con->sql_fetchrow()){
				if(!$vdb['account_id']) unset($vdb['account_id']);
				$vd = array_merge($vd, $vdb);
			} 
			$smarty->assign("vendor", $vd);
			if ($form['po_branch_id']==0){
				$con->sql_query("select * from branch where id=".mi($form['branch_id']));
				$smarty->assign("billto", $con->sql_fetchrow());
				$con->sql_query("select * from branch where id=".mi($form['branch_id']));
				$smarty->assign("deliver", $con->sql_fetchrow());
			}
			else{
				$con->sql_query("select * from branch where id=".mi($form['branch_id']));
				$smarty->assign("billto", $con->sql_fetchrow());
				$con->sql_query("select * from branch where id=".mi($form['po_branch_id']));
				$smarty->assign("deliver", $con->sql_fetchrow());
			}
			$smarty->assign("print", array("vendor_copy"=>isset($_REQUEST['print_vendor_copy']), "branch_copy"=>isset($_REQUEST['print_branch_copy'])));
			//split items to print.
			if($config['po_alt_print_template'])    $print_tpl = $config['po_alt_print_template'];
			else    $print_tpl = "po.print.tpl";
			
			if ($config['report_logo_by_branch']['po'][get_branch_code($branch_id)]) $smarty->assign("alt_logo_img", $config['report_logo_by_branch']['po'][get_branch_code($branch_id)]);
			
			/*if($_REQUEST['send_by_email']){
				po_send_email($form, $po_items, $print_tpl);
			}else*/ 
				po_sheet_print($form, $po_items, $print_tpl);
			
			if($_REQUEST['print_sz_clr'] && $config['po_sz_clr_print_template'] && $po_items){
				$sz_clr_list = $parent_info = array();
				foreach($po_items as $id=>$pi){
					if(!$pi['size'] || !$pi['color']) continue;
					$sz_clr_list[$pi['sku_id']]['size'][$pi['size']] = $pi['size'];
					$sz_clr_list[$pi['sku_id']]['color'][$pi['color']] = $pi['color'];

					if(!$parent_info[$pi['sku_id']]){
						$q1 = $con->sql_query("select *
											   from sku_items
											   where is_parent = 1 and sku_id = ".mi($pi['sku_id'])." limit 1");
						$parent_info[$pi['sku_id']] = $con->sql_fetchrow($q1);
						$con->sql_freeresult($q1);
					}
					
					$sz_clr_list[$pi['sku_id']]['sku_item_code'] = $parent_info[$pi['sku_id']]['sku_item_code'];
					$sz_clr_list[$pi['sku_id']]['description'] = $parent_info[$pi['sku_id']]['description'];
					$sz_clr_list[$pi['sku_id']]['list'][$pi['size']][$pi['color']][$pi['order_uom']]['ctn'] += $pi['qty'] + $r['foc'];
					$sz_clr_list[$pi['sku_id']]['list'][$pi['size']][$pi['color']][$pi['order_uom']]['pcs'] += $pi['qty_loose'] + $pi['foc_loose'];
					$sz_clr_list[$pi['sku_id']][$pi['size']][$pi['order_uom']]['ctn'] += $pi['qty'] + $r['foc'];
					$sz_clr_list[$pi['sku_id']][$pi['size']][$pi['order_uom']]['pcs'] += $pi['qty_loose'] + $pi['foc_loose'];
					$sz_clr_list[$pi['sku_id']][$pi['color']][$pi['order_uom']]['ctn'] += $pi['qty'] + $r['foc'];
					$sz_clr_list[$pi['sku_id']][$pi['color']][$pi['order_uom']]['pcs'] += $pi['qty_loose'] + $pi['foc_loose'];
					$sz_clr_list[$pi['sku_id']][$pi['order_uom']]['total']['ctn'] += $pi['qty'] + $r['foc'];
					$sz_clr_list[$pi['sku_id']][$pi['order_uom']]['total']['pcs'] += $pi['qty_loose'] + $pi['foc_loose'];
					$sz_clr_list[$pi['sku_id']]['uom_list'][$pi['order_uom']] = $pi['order_uom'];
				}
				
				if($sz_clr_list){
					$smarty->assign("sz_clr_items", $sz_clr_list);
					$smarty->display($config['po_sz_clr_print_template']);
				}
			}
		}
		else{
			$org_po_items=$po_items;
			$org_total=$smarty->get_template_vars("total");
			$org_form=$form;
			$i=0;
			$delivery_date = $form['delivery_date'];
			$cancel_date = $form['cancel_date'];

			foreach($form['deliver_to'] as $dummy=>$bid){
			
				if ($org_form['po_option'] != 3 || $_REQUEST['checklist'])
				{
					$form = $org_form;
					$total = array();
					$po_items = array();
				}
				
				if ($org_form['po_option'] == 3 && $_REQUEST['checklist'])
				{
					if (!in_array($bid,$_REQUEST['print_branch_id']))
					{
						continue;
						$i++;
					}
				}
				
				foreach ($org_po_items as $r){
					//print_r($r);
					if(!$form['tmp_pi_id']) $form['tmp_pi_id'] = $r['id'];
					$r_org = $r;
					$r['qty'] = $r['qty_allocation'][$bid];
					$r['qty_loose'] = $r['qty_loose_allocation'][$bid];
					$r['foc'] = $r['foc_allocation'][$bid];
					$r['foc_loose'] = $r['foc_loose_allocation'][$bid];
					$r['selling_price'] = $r['selling_price_allocation'][$bid];
					$r['gst_selling_price'] = $r['gst_selling_price_allocation'][$bid];
					$r['row_qty'] = $r['qty']*$r1['order_uom_fraction']+$r['qty_loose'];
					$r['row_foc'] = $r['foc']*$r1['order_uom_fraction']+$r['foc_loose'];
					if ($r['order_uom_fraction']==0) $r['order_uom_fraction'] = 1;
					if ($org_form['po_option'] != 3 || $_REQUEST['checklist'])
					{
						$total['qty'] += $r['qty'] * $r['order_uom_fraction'] + $r['qty_loose'];
						$total['foc'] += $r['foc'] * $r['order_uom_fraction'] + $r['foc_loose'];
						$total['ctn'] += $r['qty'] + $r['foc'];
						//$r['gamount'] = ($r['qty']+($r['qty_loose']/$r['order_uom_fraction']))*$r['order_price'];
						//$r['total_selling'] = ($r['row_qty']+$r['row_foc'])/$r['selling_uom_fraction']*$r['selling_price'];
						//$total['sell'] += $r['total_selling'];
						//if (!$r['is_foc']){
							//$total['gamount'] += round($r['gamount'], 2);
							//$total['amount'] += round($r['gamount'], 2);
						//}
						//$r['amount'] = $r['gamount'];
					}
					/*else
					{
						$total['qty'] += $r['qty'] * $r['order_uom_fraction'];
						$total['foc'] += $r['foc'] * $r['order_uom_fraction'];
						$total['ctn'] += $r['qty'] + $r['foc'];
						$r['gamount'] = $r['qty']*$r['order_price'];
						$r['total_selling'] = ($r['qty']*$r['order_uom_fraction']+$r['foc']*$r['order_uom_fraction'])/$r['selling_uom_fraction']*$r['selling_price'];
					}*/
					
										
					// if ($r['tax']>0){
						// $r['amount'] *= ($r['tax']+100)/100;				
					// }
					// if ($r['discount']){
						// $camt = $r['amount'];
						// $r['disc_amount'] = $r_org['disc_amount']*($r['gamount']/$r_org['gamount']);
						// $r['amount'] = $camt - $r['disc_amount'];
					// }
					
					// $r['amount'] = round($r['amount'], 2);
					
					// if($form['is_under_gst']){
						// // calculate gst amount
						// $order_price = round($r['amount'] / ($r['row_qty']+$r['row_foc']), $config['global_cost_decimal_points']);
						// $r['unit_gst_amount'] = round($order_price * $r['cost_gst_rate'] / 100, $config['global_cost_decimal_points']);
						// $r['row_cost_gst'] = round($r['unit_gst_amount'] * ($r['row_qty']+$r['row_foc']), 2);
						// $r['row_cost_gst_amt'] = $r['amount'] + $r['row_cost_gst'];
						// $r['gst_order_price'] = round($order_price + $r['unit_gst_amount'], 2);
						// if ($org_form['po_option'] != 3 || $_REQUEST['checklist']){
							// $r['total_gst_selling'] = ($r['row_qty']+$r['row_foc'])/$r['selling_uom_fraction']*$r['gst_selling_price'];
							// $total['gst_sell'] += $r['total_gst_selling'];
							
							// $total['gst_rate_amount'] += round($r['row_cost_gst'], 2);
							// $total['gst_amount'] += round($r['row_cost_gst_amt'], 2);
						// }
					// }
					
					$r['tax_amt'] = $r['item_allocation_info'][$bid]['tax_amt'];
					$r['discount_amt'] = $r['item_allocation_info'][$bid]['discount_amt'];
					$r['item_gross_amt'] = $r['item_allocation_info'][$bid]['item_gross_amt'];
					$r['item_nett_amt'] = $r['item_allocation_info'][$bid]['item_nett_amt'];
					$r['item_gst_amt'] = $r['item_allocation_info'][$bid]['item_gst_amt'];
					$r['item_amt_incl_gst'] = $r['item_allocation_info'][$bid]['item_amt_incl_gst'];
					$r['item_total_selling'] = $r['item_allocation_info'][$bid]['item_total_selling'];
					$r['item_total_gst_selling'] = $r['item_allocation_info'][$bid]['item_total_gst_selling'];
					
					if($r['qty']+$r['qty_loose']+$r['foc']+$r['foc_loose']){
						if ($org_form['po_option'] == 3 || $_REQUEST['checklist'])
						{
							//print "sum qty = ".$po_items[$r['id']]['ctn']."<br />";
							$r['foc_loose'] += $po_items[$r['id']]['foc_loose'];
							//$r['selling_price'] += $po_items[$r['id']]['selling_price'];
							
							if($_REQUEST['checklist']){
								$r['qty'] += $po_items[$r['id']]['qty'];
							}else{
								$r['qty'] = $po_items[$r['id']]['ctn'];
							}
							
							//$r['gamount'] += $po_items[$r['id']]['gamount'];
							//$r['amount'] += $po_items[$r['id']]['amount'];
							$r['qty_loose'] += $po_items[$r['id']]['qty_loose'];
							//$r['disc_amount'] += $po_items[$r['id']]['disc_amount'];
							//$r['total_selling'] += $po_items[$r['id']]['total_selling'];
						}
						$po_items[$r['id']] = $r;			
					}
				}
				
				//print_r($po_items);
				
				// calculate grand total
				/*foreach ($total as $k=>$dummy){
					// calculate grand total
					$weight = $total['amount']/$org_total['amount'];
					$a = $total['amount'];
					$a = parse_formula($a,$form['misc_cost'],true, $weight,$z);
					$form['misc_cost_amount'] = sprintf(" (%.2f)",$z);
							
					$a = parse_formula($a,$form['sdiscount'],false, $weight,$z);
					$total['sdiscount_amount'] = -$z;
					
					$b = $a;
					$a = parse_formula($a,$form['rdiscount'],false, $weight,$z);
					$form['rdiscount_amount']= sprintf(" (%.2f)",-$z);
					
					$a = parse_formula($a,$form['ddiscount'],false, $weight,$z);
					$form['ddiscount_amount'] = sprintf(" (%.2f)",-$z);
					
					$a += $form['transport_cost']*$weight;
					$b += $form['transport_cost']*$weight;
					$form['transport_cost_amount']=sprintf(" (%.2f)",$form['transport_cost']*$weight);
					$total['final_amount2'] = $b;
					$total['final_amount'] = $a;
					
					if($form['is_under_gst']){
						// calculate gst grand total amount
						$weight = $total['gst_amount']/$org_total['gst_amount'];
						$a = $total['gst_amount'];
						$a = parse_formula($a,$form['misc_cost'],true, $weight,$z);
						$form['misc_cost_gst_amount'] = sprintf(" (%.2f)",$z);
								
						$a = parse_formula($a,$form['sdiscount'],false, $weight,$z);
						$total['sdiscount_gst_amount'] = -$z;
						
						$b = $a;
						$a = parse_formula($a,$form['rdiscount'],false, $weight,$z);
						$form['rdiscount_gst_amount']= sprintf(" (%.2f)",-$z);
						
						$a = parse_formula($a,$form['ddiscount'],false, $weight,$z);
						$form['ddiscount_gst_amount'] = sprintf(" (%.2f)",-$z);
						
						$a += $form['transport_cost']*$weight;
						$b += $form['transport_cost']*$weight;
						$total['final_gst_amount2'] = $b;
						$total['final_gst_amount'] = $a;
					}
				}*/
				$form['subtotal_po_gross_amount'] = $form['allocation_info'][$bid]['subtotal_po_gross_amount'];
				$form['subtotal_po_nett_amount'] = $form['allocation_info'][$bid]['subtotal_po_nett_amount'];
				$form['subtotal_po_gst_amount'] = $form['allocation_info'][$bid]['subtotal_po_gst_amount'];
				$form['subtotal_po_amount_incl_gst'] = $form['allocation_info'][$bid]['subtotal_po_amount_incl_gst'];
				$form['misc_cost_amt'] = $form['allocation_info'][$bid]['misc_cost_amt'];
				$form['gst_misc_cost_amt'] = $form['allocation_info'][$bid]['gst_misc_cost_amt'];
				$form['sdiscount_amt'] = $form['allocation_info'][$bid]['sdiscount_amt'];
				$form['gst_sdiscount_amt'] = $form['allocation_info'][$bid]['gst_sdiscount_amt'];
				$form['rdiscount_amt'] = $form['allocation_info'][$bid]['rdiscount_amt'];
				$form['gst_rdiscount_amt'] = $form['allocation_info'][$bid]['gst_rdiscount_amt'];
				$form['ddiscount_amt'] = $form['allocation_info'][$bid]['ddiscount_amt'];
				$form['gst_ddiscount_amt'] = $form['allocation_info'][$bid]['gst_ddiscount_amt'];
				$form['transport_cost_amt'] = $form['allocation_info'][$bid]['transport_cost_amt'];
				$form['total_selling_amt'] = $form['allocation_info'][$bid]['total_selling_amt'];
				$form['total_gst_selling_amt'] = $form['allocation_info'][$bid]['total_gst_selling_amt'];
				$form['supplier_po_amt'] = $form['allocation_info'][$bid]['supplier_po_amt'];
				$form['supplier_po_amt_incl_gst'] = $form['allocation_info'][$bid]['supplier_po_amt_incl_gst'];
				$form['po_amount'] = $form['allocation_info'][$bid]['po_amount'];
				$form['po_gst_amount'] = $form['allocation_info'][$bid]['po_gst_amount'];
				$form['po_amount_incl_gst'] = $form['allocation_info'][$bid]['po_amount_incl_gst'];
				
				
				$smarty->assign("total", $total);
				$form['delivery_date'] = $delivery_date[$bid];
				$form['cancel_date'] = $cancel_date[$bid];
				
				$smarty->assign("form", $form);
				
				if ($form['delivery_vendor'][$bid] > 0)
					$vid = $form['delivery_vendor'][$bid];
				else
					$vid = $form['vendor_id'];
				$con->sql_query("select * from vendor where id = " . mi($vid));
				$vd = $con->sql_fetchrow();
				$con->sql_query("select * from branch_vendor where vendor_id = " . mi($vid) . " and branch_id = " . mi($form['branch_id']));
				if ($vdb = $con->sql_fetchrow()){
					if(!$vdb['account_id']) unset($vdb['account_id']);
					$vd = array_merge($vd, $vdb);
				}
				$smarty->assign("vendor", $vd);
				if ($form['po_option']==1 || ($form['po_option']==3 && !$_REQUEST['checklist'])) {
					$con->sql_query("select * from branch where id = " . mi($form['branch_id']));
					$smarty->assign("billto", $con->sql_fetchrow());
				}
				else {
					$con->sql_query("select * from branch where id = " . mi($bid));
					$smarty->assign("billto", $con->sql_fetchrow());
				}
				
				if ($form['po_option']==3 && !$_REQUEST['checklist']) {
				$con->sql_query("select * from branch where id = ".mi($form['branch_id']));
				$smarty->assign("deliver", $con->sql_fetchrow());
				}
				else{
				$con->sql_query("select * from branch where id = ".mi($bid));
				$smarty->assign("deliver", $con->sql_fetchrow());
				}
				$smarty->assign("print", array("vendor_copy"=>isset($_REQUEST['print_vendor_copy']), "branch_copy"=>isset($_REQUEST['print_branch_copy'])));
				//split items to print.
				
				if($config['po_alt_print_template']) $print_tpl = $config['po_alt_print_template'];
				else $print_tpl = "po.print.tpl";
				
				if ($config['report_logo_by_branch']['po'][get_branch_code($branch_id)]) $smarty->assign("alt_logo_img", $config['report_logo_by_branch']['po'][get_branch_code($branch_id)]);
				
				if($org_form['po_option'] == 3 && $_REQUEST['checklist']){
					if($config['po_checklist_alt_print_template']) $print_tpl = $config['po_checklist_alt_print_template'];
					else $print_tpl = 'po.checklist.print.tpl';
				}
				
				if (count($org_form['deliver_to'])-1 == $i || $org_form['po_option'] != 3 || $_REQUEST['checklist']){
					//if($_REQUEST['send_by_email']){
					//	po_send_email($form, $po_items, $print_tpl);
					//}else{
						po_sheet_print($form, $po_items, $print_tpl);	
					//}
				}

				if($_REQUEST['print_sz_clr'] && $config['po_sz_clr_print_template'] && $po_items){
					$sz_clr_list = $parent_info = array();
					foreach($po_items as $id=>$pi){
						if(!$pi['size'] || !$pi['color']) continue;
						$sz_clr_list[$pi['sku_id']]['size'][$pi['size']] = $pi['size'];
						$sz_clr_list[$pi['sku_id']]['color'][$pi['color']] = $pi['color'];

						if(!$parent_info[$pi['sku_id']]){
							$q1 = $con->sql_query("select *
												   from sku_items
												   where is_parent = 1 and sku_id = ".mi($pi['sku_id'])." limit 1");
							$parent_info[$pi['sku_id']] = $con->sql_fetchrow($q1);
							$con->sql_freeresult($q1);
						}
						
						$sz_clr_list[$pi['sku_id']]['sku_item_code'] = $parent_info[$pi['sku_id']]['sku_item_code'];
						$sz_clr_list[$pi['sku_id']]['description'] = $parent_info[$pi['sku_id']]['description'];
						$sz_clr_list[$pi['sku_id']]['list'][$pi['size']][$pi['color']][$pi['order_uom']]['ctn'] += $pi['qty'] + $r['foc'];
						$sz_clr_list[$pi['sku_id']]['list'][$pi['size']][$pi['color']][$pi['order_uom']]['pcs'] += $pi['qty_loose'] + $pi['foc_loose'];
						$sz_clr_list[$pi['sku_id']][$pi['size']][$pi['order_uom']]['ctn'] += $pi['qty'] + $r['foc'];
						$sz_clr_list[$pi['sku_id']][$pi['size']][$pi['order_uom']]['pcs'] += $pi['qty_loose'] + $pi['foc_loose'];
						$sz_clr_list[$pi['sku_id']][$pi['color']][$pi['order_uom']]['ctn'] += $pi['qty'] + $r['foc'];
						$sz_clr_list[$pi['sku_id']][$pi['color']][$pi['order_uom']]['pcs'] += $pi['qty_loose'] + $pi['foc_loose'];
						$sz_clr_list[$pi['sku_id']][$pi['order_uom']]['total']['ctn'] += $pi['qty'] + $r['foc'];
						$sz_clr_list[$pi['sku_id']][$pi['order_uom']]['total']['pcs'] += $pi['qty_loose'] + $pi['foc_loose'];
						$sz_clr_list[$pi['sku_id']]['uom_list'][$pi['order_uom']] = $pi['order_uom'];
					}
					
					if($sz_clr_list){
						$smarty->assign("sz_clr_items", $sz_clr_list);
						$smarty->display($config['po_sz_clr_print_template']);
					}
				}
				$i++;
			}
	  
		}

		$con->sql_query("update po set print_counter=print_counter+1, last_update=last_update where id=$form[id] and branch_id=$form[branch_id]");
		log_br($sessioninfo['id'], 'PURCHASE ORDER', $form['id'], "Print : ".'('.$form[po_no].')');
	}

	if($_REQUEST['print_grn_perform_report']){
		if($form['po_branch_id']==0) $grn_bid = $form['branch_id'];
		else $grn_bid = $form['po_branch_id'];

		$q1 = $con->sql_query("select grn.* from grr_items gi left join grr on grr.id = gi.grr_id and grr.branch_id = gi.branch_id left join grn on grn.grr_id = grr.id and grn.branch_id = grr.branch_id where gi.branch_id = ".mi($grn_bid)." and gi.doc_no = ".ms($form['po_no'])." and type = 'PO' and grn.active = 1 and grr.active = 1");
		
		if($con->sql_numrows($q1)>0){
			$grn = $con->sql_fetchassoc($q1);
			$appCore->grnManager->print_grn_performance($grn['id'], $grn['branch_id']);
		}else{
			print "<script>alert(\"There is no GRN found to print GRN Performance Report\");</script>";
		}
		$con->sql_freeresult($q1);
	}
}

/*function po_send_email($form, $po_items, $print_tpl){
	global $sessioninfo, $con, $smarty, $config;

	include_once("include/class.phpmailer.php");
	$mailer = new PHPMailer(true);
	
	// get the current logged on user's email who send this PO
	$q1 = $con->sql_query("select * from user where id = ".mi($sessioninfo['id']));
	$u_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	if($u_info['email']){
		if(is_array($config['smtp_mail_settings']) && $config['smtp_mail_settings']){
			$mailer->From = $u_info['email'];
			$mailer->FromName = $sessioninfo['u'];
			if($config['smtp_mail_settings']['reply_to_user']) $mailer->addReplyTo($u_info['email'], $sessioninfo['u']);
		}else{
			$mailer->From = "noreply@arms.com.my";
			$mailer->FromName = "ARMS Notification";
			$mailer->addReplyTo($u_info['email'], $sessioninfo['u']);
		}
	}else{
		$mailer->From = "noreply@arms.com.my";
		$mailer->FromName = "ARMS Notification";
	}
	$mailer->Subject = "ARMS - Purchase Order (".$form['po_no'].")";
	$mailer->IsHTML(true);
	$mailer->SMTPDebug=1;

	$q1 = $con->sql_query("select contact_email from vendor where id = ".mi($form['vendor_id']));
	$vd_info = $con->sql_fetchassoc($q1);
	
	$form['custom_msg'] = $_REQUEST['custom_msg'];
	$form['save_msg_as_default'] = $_REQUEST['save_msg_as_default'];

	if($mailer->ValidateAddress($vd_info['contact_email'])){
		if(!$form['custom_msg']) $form['custom_msg'] = "Kindly please refer to attachment.";
		elseif($form['save_msg_as_default']){
			file_put_contents("custom_email_msg.txt", $form['custom_msg']);
			chmod("custom_email_msg.txt", 0777);
		}
		$form['custom_msg'] = str_replace("\n", "<br />", $form['custom_msg']);
	
		$smarty->assign("po_items", $po_items);
		//$smarty->assign("skip_header", 1);
		$print['vendor_copy'] = true;
		$mailer->AddAddress($vd_info['contact_email']);
		$file = "po.html";
		file_put_contents($file, "");
		chmod($file, 0777);
		$p = array('mod'=>'po');
		$lg = smarty_get_logo_url($p, $smarty);
		$imagedata = file_get_contents($lg);
		$encoded_comp_logo = base64_encode($imagedata);
		$smarty->assign("encoded_comp_logo", $encoded_comp_logo);
		file_put_contents($file, $smarty->fetch($print_tpl));
		$mailer->AddAttachment($file, 'po_'.$form['po_no'].".html");
		$mailer->Body = $form['custom_msg'];
		// send the mail
		//$send_success = $mailer->Send();
		$send_success = phpmailer_send($mailer, $mailer_info);
		//$mailer->to = array();  // clear the address list
		print "<script>alert(\"Email has been successfully sent to Vendor\");</script>";
		unlink($file);
	}else{
		print "No email found!";
	}
}*/

function po_send_email2($form, $html){
	global $sessioninfo, $con, $smarty, $config, $appCore;

	include_once("include/class.phpmailer.php");
	$mailer = new PHPMailer(true);
	
	// get the current logged on user's email who send this PO
	$q1 = $con->sql_query("select * from user where id = ".mi($sessioninfo['id']));
	$u_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	if($u_info['email']){
		if(is_array($config['smtp_mail_settings']) && $config['smtp_mail_settings']){
			$mailer->From = $u_info['email'];
			$mailer->FromName = $sessioninfo['u'];
			if($config['smtp_mail_settings']['reply_to_user']) $mailer->addReplyTo($u_info['email'], $sessioninfo['u']);
		}else{
			$mailer->From = "noreply@arms.com.my";
			$mailer->FromName = "ARMS Notification";
			$mailer->addReplyTo($u_info['email'], $sessioninfo['u']);
		}
	}else{
		$mailer->From = "noreply@arms.com.my";
		$mailer->FromName = "ARMS Notification";
	}
	$server_name = trim($config['server_name']);
	if(!$server_name)	$server_name = 'ARMS';
	$mailer->Subject = $server_name." - Purchase Order (".$form['po_no'].")";
	$mailer->IsHTML(true);
	$mailer->SMTPDebug=1;

	$q1 = $con->sql_query("select contact_email from vendor where id = ".mi($form['vendor_id']));
	$vd_info = $con->sql_fetchassoc($q1);
	
	$form['custom_msg'] = $_REQUEST['custom_msg'];
	$form['save_msg_as_default'] = $_REQUEST['save_msg_as_default'];

	if($mailer->ValidateAddress($vd_info['contact_email'])){
		if(!$form['custom_msg']) $form['custom_msg'] = "Kindly please refer to attachment.";
		elseif($form['save_msg_as_default']){
			file_put_contents("custom_email_msg.txt", $form['custom_msg']);
			chmod("custom_email_msg.txt", 0777);
		}
		$form['custom_msg'] = str_replace("\n", "<br />", $form['custom_msg']);
	
		$smarty->assign("po_items", $po_items);
		//$smarty->assign("skip_header", 1);
		//$smarty->assign("send_email", 1);
		//$smarty->assign("DOCUMENT_ROOT", `pwd`);
		$print['vendor_copy'] = true;
		$mailer->AddAddress($vd_info['contact_email']);
		$file = "po.html";
		file_put_contents($file, '');
		chmod($file, 0777);
		//$p = array('mod'=>'po');
		//$lg = smarty_get_logo_url($p, $smarty);
		//$imagedata = file_get_contents($lg);
		//$encoded_comp_logo = base64_encode($imagedata);
		//$smarty->assign("encoded_comp_logo", $encoded_comp_logo);
		file_put_contents($file, $html);
		$tmp_pdfFile = tempnam('/tmp/', 'pdf-');
		$pdfFile = $appCore->makePDF($file, $tmp_pdfFile);
		if($pdfFile){
			// Attach PDF
			$mailer->AddAttachment($pdfFile, 'po_'.$form['po_no'].".pdf");
		}else{
			// Attach HTML
			$mailer->AddAttachment($file, 'po_'.$form['po_no'].".html");
		}
		$mailer->Body = $form['custom_msg'];
		// send the mail
		//$send_success = $mailer->Send();
		$send_success = phpmailer_send($mailer, $mailer_info);
		//$mailer->to = array();  // clear the address list
		print "<script>alert(\"Email has been successfully sent to Vendor\");</script>";
		unlink($file);
	}else{
		print "No email found!";
	}
}

/*function print_grn_performance_report($grn_id, $branch_id){
	global $con, $smarty, $config;
	$con->sql_query("select * from branch where id=$branch_id");
	$smarty->assign("branch", $con->sql_fetchrow());

	$q1=$con->sql_query("select grn.*, vendor.description as vendor, category.description as department, 
	user.u, user2.u as acc_u  
	from grn 
	left join user on user_id = user.id 
	left join user user2 on by_account = user2.id 
	left join vendor on vendor_id = vendor.id 
	left join category on grn.department_id = category.id 
	where grn.id=".mi($grn_id)." and grn.branch_id=".mi($branch_id));
	$grn = $con->sql_fetchrow($q1);

	// load GRR header info
	$grr_amt_by_type = array();
    if(!$grn['is_future'] && $grn['grr_item_id']) $filter = " and grr_items.id = ".mi($grn['grr_item_id']);
	$q1=$con->sql_query("select grr_items.*, grr.*, vendor.*, grr.id as grr_id, grr_items.id as grr_item_id, vendor.description as vendor, dept.grn_get_weight, dept.description as department, user.u, rcv.u as rcv_u
					from grr_items 
					left join grr on grr_items.grr_id = grr.id and grr_items.branch_id = grr.branch_id 
					left join user on grr.user_id = user.id 
					left join user rcv on grr.rcv_by = rcv.id 
					left join vendor on grr.vendor_id = vendor.id 
					left join category dept on grr.department_id = dept.id 
					where grr.branch_id = ".mi($branch_id)." and grr.id = ".intval($grn['grr_id']).$filter."
					order by grr_items.id");

	//$grr = $con->sql_fetchrow($q1);
	while($r1=$con->sql_fetchrow($q1)){
		if($grn['is_future']){
			$grr_amt_by_type[$r1['type']] += $r1['amount'];
			if(!preg_match("{^".$temp_doc."$}", $r1['doc_no'])){
				$grp_doc[$r1['type']][] = $r1['doc_no'];
			}
			$temp_doc = join("|", $grp_doc);

			if(!preg_match("{^".$temp_type."$}", $r1['type'])){
				$grp_type[] = $r1['type'];
			}
			$temp_type = join("|", $grp_type);
		}

		if ($r1['type']=='PO' && $r1['doc_no']!=''){
			// get additional PO information if po is not empty
			$q2=$con->sql_query('select po.*, po.remark as po_remark1, po.remark2 as po_remark2, 
								 branch_approval_history.flow_approvals,user.u as po_u 
								 from po 
								 left join user on po.user_id = user.id 
								 left join branch_approval_history on po.approval_history_id = branch_approval_history.id and branch_approval_history.branch_id = po.branch_id
								 where po_no = '. ms($r1['doc_no']));
			$grr_po = $con->sql_fetchrow($q2);

			if($grn['is_future']){
				if($grr_po['po_no']) $grp_po_no[] = ms($grr_po['po_no']);	
				if(!$grr_po['partial_delivery']) $non_pd_po[] = $grr_po['po_no']; 
			}
			
			$grr_po['sdiscount']=unserialize($grr_po['sdiscount']);
			$grr_po['rdiscount']=unserialize($grr_po['rdiscount']);
			$grr_po['po_remark1']=unserialize($grr_po['po_remark1']);
			$grr_po['po_remark2']=unserialize($grr_po['po_remark2']);
			$ttl_po_amt += $grr_po['po_amount'];
			// merge array
			$grr = array_merge($r1, $grr_po);
		}else{
			$grr = $r1;
		}
	}
	$con->sql_freeresult($q1);
	
	if($ttl_po_amt) $grr['po_amount'] = $ttl_po_amt;
	if($non_pd_po) $grr['pd_po'] = join(", ", $non_pd_po);

	if($grn['is_future']){
		$grr['doc_no'] = '';
		$grr['type'] = '';
		if(preg_match("{^".$temp_type."$}", "PO")){
			$grr['type'] = "PO";
			$grr['doc_no'] = join(", ", $grp_doc['PO']);
		}elseif(!$grr['doc_no'] && preg_match("{^".$temp_type."$}", "INVOICE")){
			$grr['type'] = "INVOICE";
			$grr['doc_no'] = join(", ", $grp_doc['INVOICE']);
		}elseif(!$grr['doc_no'] && preg_match("{^".$temp_type."$}", "DO")){
			$grr['type'] = "DO";
			$grr['doc_no'] = join(", ", $grp_doc['DO']);
		}else{
			$grr['type'] = "OTHER";
			$grr['doc_no'] = join(", ", $grp_doc['OTHER']);
		}

		if(count($grr_amt_by_type) > 0){
			$grr['grr_amount'] = 0;
			if($grr['type'] == "INVOICE" || $grr['type'] == "PO") $grr['grr_amount'] += $grr_amt_by_type['INVOICE'];
			elseif($grr['type'] == "DO") $grr['grr_amount'] += $grr_amt_by_type['DO'];
			$grr['grr_amount'] += $grr_amt_by_type['OTHER'];
			unset($grr_amt_by_type);
		}
		if(is_array($grp_po_no)) $grr['grp_po_no'] = join(",", $grp_po_no);
	}
	
	$price_date = date("Y-m-d",strtotime("+1 day",strtotime($grr['rcv_date'])));
	$grn['price_date']=$price_date;

	$items = array();
	$rs1 = $con->sql_query("select grn_items.*, if(grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *u1.fraction + grn_items.pcs, grn_items.acc_ctn *u1.fraction + grn_items.acc_pcs) as qty , round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/u1.fraction, ".mi($config['global_cost_decimal_points']).") as grn_cost, sku_items.mcode, sku_items.sku_item_code, sku_items.description, u1.code as order_uom, u2.code as sell_uom, u1.fraction as uom_fraction, u2.fraction as selling_uomf, sku_items.artno, grn_items.selling_price/u2.fraction as grn_price, sku_items.selling_price as master_price 
							from grn_items 
							left join sku_items on grn_items.sku_item_id=sku_items.id 
							left join uom u1 on grn_items.uom_id=u1.id 
							left join uom u2 on grn_items.selling_uom_id=u2.id 
							where grn_id = ".mi($grn['id'])." and grn_items.branch_id = ".mi($grn['branch_id'])."
							order by grn_items.id") or die(mysql_error());

	

	while($r=$con->sql_fetchrow($rs1)){
		//get selling price for GRN
		$query2=$con->sql_query("select siph.price as grn_price 
from sku_items_price_history siph 
left join sku_items on sku_items.id=sku_item_id 
where sku_item_id = ".mi($r['sku_item_id'])." and siph.branch_id = ".mi($grn['branch_id'])." and siph.added < ".ms($price_date)." order by siph.added desc limit 1");

		$r2=$con->sql_fetchrow($query2);
		if($r2) $r=array_merge($r, $r2);
		
		$r['total_cost']=$r['grn_cost']*$r['qty'];
		
		if(!$temp[$r['sku_item_code']])
			$temp[$r['sku_item_code']]=$r;
		else{
			$temp[$r['sku_item_code']]['qty']=$items[$r['sku_item_code']]['qty']+$r['qty'];
			$temp[$r['sku_item_code']]['total_cost']=$items[$r['sku_item_code']]['total_cost']+$r['total_cost'];
			if($temp[$r['sku_item_code']]['total_cost'] && $temp[$r['sku_item_code']]['qty'])
				$temp[$r['sku_item_code']]['grn_cost']=$temp[$r['sku_item_code']]['total_cost']/$temp[$r['sku_item_code']]['qty'];
			else $temp[$r['sku_item_code']]['grn_cost'] = 0;
		}		
		$items = $temp;
	}
	$con->sql_freeresult($rs1);

	//IF FROM PO GET THE FOC.
	if($grr['type']=='PO'){
		if(strpos($grr['doc_no'], ",") == true){
			$splt_doc_no = explode(",", $grr['doc_no']);
			for($i=0; $i<count($splt_doc_no); $i++){
				$splt_doc_no[$i] = trim($splt_doc_no[$i]); 
			}
			$doc_no = join("','",$splt_doc_no);
		}else $doc_no = $grr['doc_no'];
	
		$q0=$con->sql_query("select if(po_items.foc is null, sum(po_items.foc_loose),sum(po_items.foc))*uom.fraction as po_foc, sku_items.sku_item_code, po.po_no, po.partial_delivery
from po_items 
left join po on po.id=po_items.po_id and po.branch_id=po_items.branch_id 
left join uom on uom.id=po_items.order_uom_id 
left join sku_items on sku_items.id=sku_item_id 
where po_no in ('$doc_no') group by po_items.id");
		$non_pd_po = array();
		while ($r0=$con->sql_fetchrow($q0)) $items[$r0['sku_item_code']]['po_foc']=abs($r0['po_foc']);
		$con->sql_freeresult($q0);
	}

	if ($items) $where =" sku_item_code in ('" . join("','", array_keys($items)) . "')";
	else die("Items in this GRN are invalid");
	
	//FROM POS
	$q3 = $con->sql_query("select si.sku_item_code, sum(qty) as sold_qty from
sku_items_sales_cache_b".$grn['branch_id']." tbl
left join sku_items si on si.id=tbl.sku_item_id
where tbl.date>=".ms($grr['rcv_date'])." and $where group by si.sku_item_code");
	while ($r3=$con->sql_fetchrow($q3))	$pos_qty[$r3['sku_item_code']]=$r3;
	$con->sql_freeresult($q3);

	//FROM DO
	$q4=$con->sql_query("select sku_item_code, sum(do_items.ctn *uom.fraction + do_items.pcs) as qty  
from do_items 
left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
left join sku_items on sku_item_id = sku_items.id 
left join uom on do_items.uom_id=uom.id
where $where and do_items.branch_id = ".mi($grn['branch_id'])." and do.approved and do.checkout and do.status<2 and do_date >= ".ms($grr['rcv_date'])." group by sku_item_code", false, false);
	while ($r4=$con->sql_fetchrow($q4)) $do_qty[$r4['sku_item_code']]=$r4;
	$con->sql_freeresult($q4);
		
	$smarty->assign("grn", $grn);
	$smarty->assign("pos_qty", $pos_qty);
	$smarty->assign("do_qty", $do_qty);
	$smarty->assign("grr", $grr);
	
	$item_per_page= $config['grn_report_print_item_per_page']?$config['grn_report_print_item_per_page']:23;
    $item_per_lastpage = $config['grn_report_print_item_last_page']>0 ? $config['grn_report_print_item_last_page'] : $item_per_page-5;

	$totalpage = 1 + ceil((count($items)-$item_per_lastpage)/$item_per_page);

	for ($i=0,$page=1;$page<=$totalpage;$i+=$item_per_page,$page++){
		if($page == $totalpage) $smarty->assign("is_last_page", 1);
		$smarty->assign("page", "Page $page of $totalpage");
        $smarty->assign("start_counter", $i);
        $smarty->assign("PAGE_SIZE", ($page < $totalpage) ? $item_per_page : $item_per_lastpage);
        $grn_items = array_slice($items,$i,$item_per_page);
        $smarty->assign("grn_items", $grn_items);
        if($config['grn_perform_alt_print_template'])   $smarty->display($config['grn_perform_alt_print_template']);
		else	$smarty->display('goods_receiving_note.perform_print.tpl');

		$smarty->assign("skip_header",1);
	}
}*/

function po_print_distribution($po_id, $branch_id){
	global $con, $smarty,$sessioninfo,$config;
	$form=load_po_header($po_id, $branch_id);
	$po_items=load_po_items($form);
	$con->sql_query("select u from user where id=$form[user_id]");
	$tmp=$con->sql_fetchrow();	
	$form['username']=$tmp[0];
	
	$con->sql_query("select description from category where id=$form[department_id]");
	$tmp=$con->sql_fetchrow();
	$form['department']=$tmp[0];
	$dd=$form['delivery_date'];
	$cd=$form['cancel_date'];
	
	foreach($form['deliver_to'] as $bid){
		$con->sql_query("select id, code, description from branch where id=$bid");
		$tmp = $con->sql_fetchrow();
		$form['branches'][] = array("id" => $tmp[0], "code"=>$tmp[1],"description"=>$tmp[2],"delivery"=>$dd[$bid],"cancel"=>$cd[$bid]);
	}
    $smarty->assign('from_request', $_REQUEST);
	$smarty->assign('form', $form);
	//split items to print.
	if($config['po_distribution_alt_print_template'])    $print_tpl =    $config['po_distribution_alt_print_template'];
	else    $print_tpl = "po.print_distribution.tpl";
	po_sheet_print($form, $po_items, $print_tpl);
	exit;	
}
function po_revoke($po_id, $branch_id){
	global $con, $smarty, $sessioninfo, $config, $appCore, $LANG;
	
	/*if($config['monthly_closing'] && $config['monthly_closing_block_document_action']){
		$con->sql_query("select po_date from po where id=$po_id and branch_id=$branch_id");
		$r_date = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$is_month_closed = $appCore->is_month_closed($r_date['po_date']);
		if($is_month_closed)  js_redirect($LANG['MONTH_DOCUMENT_IS_CLOSED'], "/po.php");
	}*/
	
	$con->sql_query("insert into po 
(branch_id, user_id, vendor_id, department_id, po_date, po_option, deliver_to, delivery_vendor, delivery_date, cancel_date, partial_delivery, sdiscount, misc_cost, remark, remark2, transport_cost, rdiscount, ddiscount, po_amount, allowed_user, po_branch_id, is_ibt, is_under_gst, added, currency_code, currency_rate, pending_currency_rate)
select 
branch_id, user_id, vendor_id, department_id, po_date, po_option, deliver_to, delivery_vendor, delivery_date, cancel_date, partial_delivery, sdiscount, misc_cost, remark, remark2, transport_cost, rdiscount, ddiscount, po_amount, allowed_user, po_branch_id, is_ibt, is_under_gst, CURRENT_TIMESTAMP, currency_code, currency_rate, pending_currency_rate
from po where id=$po_id and branch_id=$branch_id");			
	$new_po_id = $con->sql_nextid();
	$con->sql_query("update po set revoke_id=$new_po_id where id=$po_id and branch_id=$branch_id");
	
    //get po_item_id to update foc share cost field
	$q1=$con->sql_query("select id, sku_item_id from po_items where po_id=$po_id and branch_id=$branch_id order by id");
	while($r1=$con->sql_fetchrow($q1)){
		$tmp_id[$r1['id']]=$r1['sku_item_id'];
	}
	
	$cols = array('po_id', 'branch_id', 'user_id', 'sku_item_id', 'qty', 'selling_price', 'selling_price_allocation', 'qty_allocation', 'is_foc', 'foc_share_cost', 'foc_noprint', 'order_uom_id', 'order_price', 'resell_price', 'order_uom_fraction', 'qty_loose_allocation', 'tax', 'discount', 'qty_loose', 'remark', 'remark2', 'foc_allocation', 'foc_loose_allocation', 'foc', 'foc_loose', 'artno_mcode', 'disc_remark', 'delivered', 'balance', 'cost_indicate', 'selling_uom_fraction', 'selling_uom_id','sales_trend', 'stock_balance');
	if($config['enable_po_agreement']){
		$cols[] = "pa_branch_id";
		$cols[] = "pa_item_id";
		$cols[] = "pa_foc_item_id";
	}
	
	if($config['enable_gst']){
		$cols[] = "selling_gst_id";
		$cols[] = "selling_gst_code";
		$cols[] = "selling_gst_rate";
		$cols[] = "cost_gst_id";
		$cols[] = "cost_gst_code";
		$cols[] = "cost_gst_rate";
		$cols[] = "gst_selling_price";
		$cols[] = "gst_selling_price_allocation";
	}
	
    //insert new po items to the revoked po
	$q2=$con->sql_query("select * from po_items where po_id=$po_id and branch_id=$branch_id order by id");	
	while($r2=$con->sql_fetchrow($q2)){				
	    $r2['branch_id']=$branch_id;
	    $r2['po_id']=$new_po_id;
	    $r2['user_id']=$sessioninfo['id'];
				    
		$con->sql_query("insert into po_items " . mysql_insert_by_field($r2, $cols));
		$po_item_id= $con->sql_nextid();		
		if(!$r2['is_foc']){
			$is_not_foc[$r2['sku_item_id']]=$po_item_id;		
		}	
	}
	
    //find foc item and update the foc_share_cost field
	$q3 = $con->sql_query("select id, foc_share_cost from po_items where po_id=$new_po_id and branch_id=$branch_id and is_foc order by id");	
	while($r3=$con->sql_fetchrow($q3)){
	    $r3['foc_share_cost'] = unserialize($r3['foc_share_cost']);
	    if ($r3['foc_share_cost']){
	    	foreach($r3['foc_share_cost'] as $i => $dummy){
				foreach($tmp_id as $k=>$v){
					if($i==$k){
						$foc_share_cost[$r3['id']][$is_not_foc[$v]]="on";
					}
				}
			}		
		}
		$foc_sz=serialize($foc_share_cost[$r3['id']]);
		$con->sql_query("update po_items set foc_share_cost='$foc_sz' where po_id=$new_po_id and branch_id=$branch_id and id=$r3[id]");				
	}
	
	// update all amount
	$appCore->poManager->reCalcatePOAmt($branch_id, $new_po_id);
	
	$formatted=sprintf("%05d",$po_id);
	$formatted_new=sprintf("%05d",$new_po_id);
	//select report prefix from branch
	$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
  	$r4=$con->sql_fetchrow();
    $smarty->assign("id", $new_po_id);
    $smarty->assign("type", "revoke");
  	
    log_br($sessioninfo['id'], 'PURCHASE ORDER', $new_po_id, "Revoked: ".' ('.$r4['report_prefix'].$formatted."->".$r4['report_prefix'].$formatted_new.') ');
}
function po_chown($po_id, $branch_id){
	global $con, $sessioninfo, $LANG;
	$form=$_REQUEST;
	
	$filter = array();
	$filter[] = "up.branch_id=$sessioninfo[branch_id] and up.privilege_code in ('PO','PO_VIEW_ONLY')";
	$filter[] = "user.u=".ms($form['new_owner'])." and user.active=1";
	$filter = "where ".join(' and ', $filter);
	
	$sql = "select user.id from user 
	left join user_privilege up on user.id=up.user_id 
	$filter
	limit 1";
	//die($sql);
	$q1=$con->sql_query($sql);
	$r1=$con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	// user not found or no privilege
	if(!$r1)	die(printf($LANG['PO_CHOWN_FAILED'], $form['new_owner']));
	
	$con->sql_query("update po set user_id=$r1[id] where id=$po_id and branch_id=$branch_id");
		
    if($con->sql_affectedrows()>0){
        $con->sql_query("update po_items set user_id=$r1[id] where po_id=$po_id and branch_id=$branch_id");
		printf($LANG['PO_CHOWN_SUCCESS'], $form['new_owner']);
	}else{
		die("Update Failed. The user already is the owner or PO not exists.");
	}
}
function po_delete($po_id, $branch_id){
	global $sessioninfo, $con, $smarty;
	
	check_must_can_edit($branch_id, $po_id);   // check available
	
	$formatted=sprintf("%05d",$po_id);
	
    if ($sessioninfo['level']<9999)
		$usrcheck=" and user_id=$sessioninfo[id]";
    if ($po_id==0){
	    $con->sql_query("delete from tmp_po_items 
where po_id=$po_id and branch_id=$branch_id and user_id=$sessioninfo[id]");
	}
	else{
		// load form
		$form = load_po_header($po_id, $branch_id);
		
		if(!$form['approval_history_id']){
			$params = array();
			$params['branch_id'] = $branch_id;
			$params['reftable'] = 'po';
			$params['ref_id'] = $po_id;
		
			$app_his = create_blank_approval_data($params);
		}
		
		// this PO is create by sales order, need update back sales order
		if($form['po_create_type']==1){
			$po_ref_key = $branch_id.'-'.$po_id;
			
			// select those item got sales order relationship
			$q_poi = $con->sql_query("select poi.*, soi.sales_order_id, so.po_ref
			from po_items poi
			join sales_order_items soi on soi.branch_id=poi.so_branch_id and soi.id=poi.so_item_id
			join sales_order so on so.branch_id=soi.branch_id and so.id=soi.sales_order_id
			where poi.branch_id=$branch_id and poi.po_id=$po_id and poi.so_branch_id>0 and poi.so_item_id>0");
			while($r = $con->sql_fetchassoc($q_poi)){
				$so_bid = mi($r['so_branch_id']);
				$so_item_id = mi($r['so_item_id']);
				$so_id = mi($r['sales_order_id']);
				
				$po_ref = str_replace($po_ref_key.'|', '', $r['po_ref']);
				if($po_ref=='|')	$po_ref = '';
				if($po_ref != $r['po_ref']){
					$upd = array();
					$upd['po_ref'] = $po_ref;
					$upd['can_generate_po'] = 1;	// mark this sales order can generate po
					if($upd['po_ref']=='')	$upd['po_used'] = 0;
					
					// update sales order
					$con->sql_query("update sales_order set ".mysql_update_by_field($upd)." where branch_id=$so_bid and id=$so_id");
				}
				
				$con->sql_query("update po_items set so_branch_id=0, so_item_id=0 where branch_id=".mi($r['branch_id'])." and id=".mi($r['id']));
			}
			$con->sql_freeresult($q_poi);
		}
		
		$po_upd = array();
		$po_upd['cancel_by'] = $sessioninfo['id'];
		$po_upd['cancelled'] = 'CURRENT_TIMESTAMP';
		$po_upd['last_update'] = $form['last_update'];
		$po_upd['status'] = 5;
		$po_upd['active'] = 0;
		if($app_his['id']){
			$form['approval_history_id'] = $po_upd['approval_history_id'] = $app_his['id'];
		}	
	    $con->sql_query("update po set ".mysql_update_by_field($po_upd)." where id=$po_id and branch_id=$branch_id $usrcheck");
	}			
	if ($con->sql_affectedrows()>0){
	    $smarty->assign("id", $po_id);
	    $smarty->assign("type", "delete");
	    
	    if($form['approval_history_id']){
			$upd = array();
			$upd['approval_history_id'] = $form['approval_history_id'];
			$upd['branch_id'] = $branch_id;
			$upd['user_id'] = $sessioninfo['id'];
			$upd['status'] = 5;
			$upd['log'] = 'DELETE';
		
			$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
			$con->sql_query("update branch_approval_history set status=5 where id = ".mi($upd['approval_history_id'])." and branch_id = $branch_id") or die(mysql_error());
		}
		
	    //select report prefix from branch
		$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
  		$r4=$con->sql_fetchrow();
		log_br($sessioninfo['id'], 'PURCHASE ORDER', $po_id, "Deleted: ".' ('.$r4['report_prefix'].$formatted.')');
    }
}

function po_cancel($po_id, $branch_id){
	global $sessioninfo, $con, $LANG, $smarty;
	$formatted=sprintf("%05d",$po_id);
	if ($sessioninfo['level']<9999)
		$usrcheck=" and user_id=$sessioninfo[id]";
	
	$con->sql_query("select * from po where branch_id=".mi($branch_id)." and id=".mi($po_id)." $usrcheck");
	$form = $con->sql_fetchassoc();
	$con->sql_freeresult();
	if(!$form){
		print "<script>alert('$LANG[PO_CANNOT_CANCEL_DELIVERED_PO]')</script>";
    	return;
	}
    $con->sql_query("update po 
set cancel_by=$sessioninfo[id], cancelled=CURRENT_TIMESTAMP(), last_update=last_update, status=5, active=0
where delivered=0 and id=$po_id and branch_id=$branch_id $usrcheck");
    if (!$con->sql_affectedrows()){
    	print "<script>alert('$LANG[PO_CANNOT_CANCEL_DELIVERED_PO]')</script>";
    	return;
	}
	
	if ($con->sql_affectedrows()>0){
		if($form['approval_history_id']){
			$upd = array();
			$upd['approval_history_id'] = $form['approval_history_id'];
			$upd['branch_id'] = $branch_id;
			$upd['user_id'] = $sessioninfo['id'];
			$upd['status'] = 5;
			$upd['log'] = 'CANCEL';
		
			$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
			$con->sql_query("update branch_approval_history set status=5 where id = ".mi($upd['approval_history_id'])." and branch_id = $branch_id") or die(mysql_error());
		}
		
	
	    $smarty->assign("id", $po_id);
	    $smarty->assign("type", "cancel");
		$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
  		$r4=$con->sql_fetchrow();
		log_br($sessioninfo['id'], 'PURCHASE ORDER', $po_id, "Cancelled: ".' ('.$r4['report_prefix'].$formatted.')');
    }
}
function po_save($po_id, $branch_id, $is_confirm){
	global $con, $LANG, $smarty, $sessioninfo, $config, $gst_list, $appCore;
	
	if(!is_new_id($po_id))	check_must_can_edit($branch_id, $po_id);   // check available
	
	$form=$_REQUEST;
	
    $last_approval=false;
    save_and_update_po_items();
    
    //validate data
    $err=array();
	$form['id']=$po_id;
	$form['branch_id']=$branch_id;
	
	if($form['vendor_id']==0) 
		$err['top'][]=$LANG['PO_INVALID_VENDOR'];
	if($form['department_id']==0) 
		$err['top'][]=$LANG['PO_INVALID_DEPARTMENT'];
//	$form['po_date']=str_replace("-", "/", $form['po_date']);
	if($form['po_date']=='' || strtotime($form['po_date'])<=0)
		$err['top'][]=$LANG['PO_INVALID_PO_DATE'];
	if($is_confirm && $form['total_check']<=0)
		$err['top'][]=sprintf($LANG['PO_CONFIRM_TOTAL_QTY_IS_ZERO']);
	if(!$_REQUEST['artno_mcode'])
		$err['top'][]=sprintf($LANG['PO_NO_ITEM']);
			
	//multiple deliver branches
	if(isset($form['deliver_to'])){
		if(!$form['po_option'])
			$err['top'][]=$LANG['PO_INVALID_PO_OPTION'];
		if(!$form['deliver_to'])
			$err['top'][]=$LANG['PO_INVALID_DELIVER_TO'];
	    foreach($form['delivery_date'] as $k=>$dummy){
	    	if(in_array($k,$form['deliver_to'])){
                $form['delivery_date'][$k] = date('d/m/Y', strtotime($form['delivery_date'][$k]));
                $form['cancel_date'][$k] = date('d/m/Y', strtotime($form['cancel_date'][$k]));
				if($form['delivery_date'][$k]=='' || dmy_to_time($form['delivery_date'][$k])<=0)
					$err['top'][]=sprintf($LANG['PO_INVALID_DATE'], 'Delivery Date');
				if($form['cancel_date'][$k]=='' || dmy_to_time($form['cancel_date'][$k])<=0)
					$err['top'][]=sprintf($LANG['PO_INVALID_DATE'], 'Cancellation Date');
		        if(dmy_to_time($form['delivery_date'][$k])<strtotime($form['po_date']))
				    $err['top'][]=sprintf($LANG['PO_INVALID_DATE'], 'Delivery is before PO Date');
				if(dmy_to_time($form['delivery_date'][$k])>dmy_to_time($form['cancel_date'][$k]))
				    $err['top'][]=sprintf($LANG['PO_INVALID_DATE'], 'Delivery is after Cancellation Date');
			}
		}
	}
	//if single deliver branch
	else{		
        $form['delivery_date'] = date('d/m/Y', strtotime($form['delivery_date']));
        $form['cancel_date'] = date('d/m/Y', strtotime($form['cancel_date']));
		if($form['delivery_date']=='' || dmy_to_time($form['delivery_date'])<=0)
		    $err['top'][]=sprintf($LANG['PO_INVALID_DATE'], 'Delivery Date');
		if($form['cancel_date']=='' || dmy_to_time($form['cancel_date'])<=0)
		    $err['top'][]=sprintf($LANG['PO_INVALID_DATE'], 'Cancellation Date');
        if(dmy_to_time($form['delivery_date'])<strtotime($form['po_date']))
		    $err['top'][]=sprintf($LANG['PO_INVALID_DATE'], 'Delivery is before PO Date');
		if(dmy_to_time($form['delivery_date'])>dmy_to_time($form['cancel_date']))
		    $err['top'][]=sprintf($LANG['PO_INVALID_DATE'], 'Delivery is after Cancellation Date');
	}
	
	//check month closed when Approve, Reset and Confirm.
	/*if($config['monthly_closing']){
		$is_month_closed = $appCore->is_month_closed($form['po_date']);
		if($is_month_closed && ($is_confirm || $config['monthly_closing_block_document_action'])){
			$err['top'][] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];
		}
	}*/

	// validate if this vendor that have GRA which must checkout
	if($is_confirm){
		$pwcg_err = validate_po_without_checkout_gra($branch_id);
		if($pwcg_err) $err['top'][] = $pwcg_err;
	}
	
	if($config['foreign_currency']){
		$form['currency_code'] = trim($form['currency_code']);
		$form['currency_rate'] = mf($form['currency_rate']);
		$form['pending_currency_rate'] = mf($form['pending_currency_rate']);
		
		if(!$form['currency_code']){
			$form['currency_rate'] = 1;	// base currency rate always 1
		}else{
			if($form['currency_rate']<=0){
				$err['top'][] = $LANG['CURRENCY_RATE_ZERO'];
			}
		}
	}

	//if PO confirm, check approval flow
    if(!$err && $is_confirm){
        /*if ($form['approval_history_id']){  // if already got approval flow, use back the same flow
	        $con->sql_query("update branch_approval_history set approvals = flow_approvals where id = ".mi($form['approval_history_id'])." and branch_id=$branch_id");
  		    $astat = $con->sql_fetchrow();
            
  		    $con->sql_query("select id,approvals from branch_approval_history where id = ".mi($form['approval_history_id'])." and branch_id=$branch_id");
  				$astat = $con->sql_fetchrow();
  				if($astat){
            $params = array();
  			    $params['user_id'] = $sessioninfo['id'];
            $params['id'] = $astat['id'];
            $params['branch_id'] = $branch_id; 
  			    $last_approval = check_is_last_approval_by_id($params, $con);
          }
  			}*/
  			
  		$params = array(); 
  		if($form['is_request']){  // PO Request
        	//$astat=check_and_create_branch_approval('PURCHASE_ORDER_REQUEST', $branch_id, 'po', "sku_category_id=$form[department_id]");
	    	$params['type'] = 'PURCHASE_ORDER_REQUEST';
	        $params['branch_id'] = $branch_id;
	        $params['user_id'] = $sessioninfo['id'];
	        $params['reftable'] = 'po';
	        $params['dept_id'] = $form['department_id'];
          				
		}else{ // Normal PO
        	//if(!$astat) 	$astat=check_and_create_branch_approval('PURCHASE_ORDER', $branch_id, 'po', "sku_category_id=$form[department_id]");
          	$params['type'] = 'PURCHASE_ORDER';
          	$params['branch_id'] = $branch_id;
          	$params['user_id'] = $sessioninfo['id'];
          	$params['reftable'] = 'po';
          	$params['dept_id'] = $form['department_id'];
    	}
    	
        if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id']; // use back the same id if already have
        
        $params['doc_amt'] = $form['po_amount'];
        
        $astat = check_and_create_approval2($params, $con);	
        
        if(!$astat){
    			$err['top'][]=$form['is_request'] ? $LANG['PO_REQUEST_NO_APPROVAL_FLOW'] : $LANG['PO_NO_APPROVAL_FLOW'];
    		}
    		else{
    			$form['approval_history_id']=$astat[0];
       			if($astat[1]=='|'){
       				$last_approval=true;
       				if($astat['direct_approve_due_to_less_then_min_doc_amt'])	$direct_approve_due_to_less_then_min_doc_amt = 1;	// direct approve because no qualify for min doc amt
       			} 
    		}  
        if($last_approval){                			         	
        	$upd = array();
			$upd['approval_history_id'] = $astat[0];
			$upd['branch_id'] = $branch_id;
			$upd['user_id'] = $sessioninfo['id'];
			$upd['status'] = 1;
			$upd['log'] = 'Approved';
	
        	if($direct_approve_due_to_less_then_min_doc_amt)	$upd['more_info']['direct_approve_due_to_less_then_min_doc_amt'] = 1;
			if($upd['more_info'])	$upd['more_info'] = serialize($upd['more_info']);
          $con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd));         
        }
  	}
    //make PO actual
	if(!$err){
		// vendor info
		$con->sql_query("select description from vendor where id=".mi($form['vendor_id']));
		$vendor_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// dept info
		$con->sql_query("select description from category where id=".mi($form['department_id']));
		$dept_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
				
	    if($is_confirm) $form['status']=1;
		
		if(!$form['user_id']) $form['user_id']=$sessioninfo['id'];
//		$form['po_date']=dmy_to_sqldate($form['po_date']);
		$form['added']='CURRENT_TIMESTAMP';
		//set single deliver branch PO as REAL PO when submit to approve.
		$po_deliver_to_singlebranch = false;
		if($is_confirm && is_array($form['deliver_to']) && count($form['deliver_to'])==1 && $form['branch_id']==1 && $form['po_option']!=3){
			$po_branch_id=$form['deliver_to'][0];
			$form['po_branch_id']=$po_branch_id;
			$form['delivery_date']=$form['delivery_date'][$po_branch_id];
			$form['cancel_date']=$form['cancel_date'][$po_branch_id];
			$form['partial_delivery']=$form['partial_delivery'][$po_branch_id];
			$form['delivery_vendor']=$form['delivery_vendor'][$po_branch_id];
			$form['deliver_to']='';
			$po_deliver_to_singlebranch = true;
			$form['po_option']=0;
			//$con->sql_query("update po set ".mysql_update_by_field($update)." where id=$form[id] and branch_id=$form[branch_id]");
		}
		
		if(is_array($form['deliver_to'])){
			$form['delivery_date']=serialize($form['delivery_date']);
			$form['cancel_date']=serialize($form['cancel_date']);
			$form['partial_delivery']=serialize($form['partial_delivery']);
			$form['deliver_to']=serialize($form['deliver_to']);
		}
	
		$form['allowed_user']=serialize($form['allowed_user']);
		$form['delivery_vendor']=serialize($form['delivery_vendor']);
		$form['sdiscount']=serialize($form['sdiscount']);
		$form['rdiscount']=serialize($form['rdiscount']);
		$form['ddiscount']=serialize($form['ddiscount']);
		$form['misc_cost']=serialize($form['misc_cost']);
		$form['transport_cost']=serialize($form['transport_cost']);
		$form['remark']=serialize($form['remark']);
		$form['remark2']=serialize($form['remark2']);
		$form['po_create_type'] = mi($form['po_create_type']);
		$form['is_under_gst'] = mi($form['is_under_gst']);
		
		
		
		$fields_arr = array('user_id', 'approval_history_id', 'status', 'vendor_id', 'department_id', 'po_date', 'po_option', 'deliver_to', 'delivery_vendor', 'delivery_date', 'cancel_date', 'partial_delivery', 'sdiscount', 'rdiscount', 'ddiscount', 'misc_cost', 'transport_cost', 'remark', 'remark2','po_amount','allowed_user' , 'po_branch_id','is_ibt','po_create_type','is_under_gst');
		//insert NEW PO
		if(is_new_id($po_id)){
			$fields_arr = array_merge($fields_arr, array('branch_id', 'added'));
			if($config['foreign_currency']){
				$fields_arr = array_merge($fields_arr, array('currency_code', 'currency_rate','pending_currency_rate'));
			}
	    	$con->sql_query("insert into po " . mysql_insert_by_field($form, $fields_arr));
	    	$form['id'] = $con->sql_nextid();
		}
		//update EXIST PO
		else{
			if($config['foreign_currency']){
				$form['can_change_currency_rate'] = 0;
				$fields_arr = array_merge($fields_arr, array('currency_rate', 'pending_currency_rate', 'can_change_currency_rate'));
				
				// need to check if got change currency rate
				if($form['currency_code']){
					$con->sql_query("select * from po where branch_id=$form[branch_id] and id=$form[id]");
					$ori_form = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($ori_form['currency_rate'] != $form['currency_rate']){	// Currency Rate Changed
						// Create Rate History array
						$rate_history = array();
						$rate_history['user_id'] = $sessioninfo['id'];
						$rate_history['override_by_user_id'] = $form['currency_rate_override_by_user_id'] ? $form['currency_rate_override_by_user_id'] : $sessioninfo['id'];
						$rate_history['old_rate'] = strval($ori_form['currency_rate']);
						$rate_history['new_rate'] = strval($form['currency_rate']);
						$rate_history['timestamp'] = 'CURRENT_TIMESTAMP';
						$rate_history['branch_id'] = $form['branch_id'];
						$rate_history['po_id'] = $form['id'];
					}
				}
			}
			$form['amt_need_update'] = 0;
			$fields_arr[] = "amt_need_update";
		    $con->sql_query("update po set " . mysql_update_by_field($form, $fields_arr) . " where id=$form[id] and branch_id=$form[branch_id]");
			if($rate_history){
				$con->sql_query("insert into po_currency_rate_history ".mysql_insert_by_field($rate_history));
				$rate_history_id = $con->sql_nextid();
			}	
		}
			
	    //get temporary item_id
		$q1=$con->sql_query("select id, sku_item_id from tmp_po_items where po_id=$po_id and branch_id=$branch_id and user_id=$sessioninfo[id] order by id");
		while($r1=$con->sql_fetchrow($q1)){
			$tmp_id[$r1['id']]=$r1['sku_item_id'];
		}
        
        $prms = array();
        $prms['branch_id'] = $branch_id;
        $prms['date'] = $form['po_date'];
        $branch_is_under_gst = check_gst_status($prms);
    
		//update items
		$q2=$con->sql_query("select * from tmp_po_items where po_id=$po_id and branch_id=$branch_id and user_id=$sessioninfo[id] order by id");
		$first_id=0;
		$form['deliver_to']=unserialize($form['deliver_to']);
		while($r2=$con->sql_fetchrow($q2)){
		    $r2['branch_id']=$branch_id;
		    $r2['po_id']=$form['id'];
			
			if($po_deliver_to_singlebranch){
		
				$r2['selling_price_allocation']=unserialize($r2['selling_price_allocation']);
				$r2['selling_price']=$r2['selling_price_allocation'][$po_branch_id];
				$r2['selling_price_allocation']='';
				
				$r2['qty_loose_allocation']=unserialize($r2['qty_loose_allocation']);
				$r2['qty_loose']=$r2['qty_loose_allocation'][$po_branch_id];
				$r2['qty_loose_allocation']='';
				
				$r2['qty_allocation']=unserialize($r2['qty_allocation']);	
				$r2['qty']=$r2['qty_allocation'][$po_branch_id];
				$r2['qty_allocation']='';
	
				$r2['foc_loose_allocation']=unserialize($r2['foc_loose_allocation']);				
				$r2['foc_loose']=$r2['foc_loose_allocation'][$po_branch_id];
				$r2['foc_loose_allocation']='';	
	
				$r2['foc_allocation']=unserialize($r2['foc_allocation']);					
				$r2['foc']=$r2['foc_allocation'][$po_branch_id];
				$r2['foc_allocation']='';	
				
				$r2['stock_balance'] = unserialize($r2['stock_balance']);
				$r2['stock_balance'] = serialize($r2['stock_balance'][$po_branch_id]);
				
				$r2['parent_stock_balance'] = unserialize($r2['parent_stock_balance']);
				$r2['parent_stock_balance'] = serialize($r2['parent_stock_balance'][$po_branch_id]);
				
				$r2['balance'] = unserialize($r2['balance']);
				$r2['balance'] = $r2['balance'][$po_branch_id];
				
				if($branch_is_under_gst){
					$r2['gst_selling_price_allocation']=unserialize($r2['gst_selling_price_allocation']);
					$r2['gst_selling_price']=$r2['gst_selling_price_allocation'][$po_branch_id];
					$r2['gst_selling_price_allocation']='';
				}
			}
			
			$cols = array('po_id', 'branch_id', 'user_id', 'sku_item_id', 'qty', 'selling_price', 'selling_price_allocation', 'qty_allocation', 'is_foc', 'foc_share_cost', 'foc_noprint', 'order_uom_id', 'order_price', 'resell_price', 'order_uom_fraction', 'qty_loose_allocation', 'tax', 'discount', 'qty_loose', 'remark', 'remark2', 'foc_allocation', 'foc_loose_allocation', 'foc', 'foc_loose', 'artno_mcode', 'disc_remark', 'delivered', 'balance', 'cost_indicate', 'selling_uom_fraction', 'selling_uom_id','sales_trend', 'stock_balance', 'parent_stock_balance', 'selling_gst_id', 'selling_gst_code', 'selling_gst_rate', 'cost_gst_id', 'cost_gst_code', 'cost_gst_rate', 'gst_selling_price', 'gst_selling_price_allocation');
			if($config['allow_sales_order']){
				$cols[] = 'so_branch_id';
				$cols[] = 'so_item_id';
			}
			
			if($config['enable_po_agreement']){
				$cols[] = 'pa_branch_id';
				$cols[] = 'pa_item_id';
				$cols[] = 'pa_foc_item_id';
			}
			
			if($config['sku_bom_additional_type']){
				$cols[] = 'bom_ref_num';
				$cols[] = 'bom_qty_ratio';
			}
			
			$con->sql_query("insert into po_items " . mysql_insert_by_field($r2, $cols));
		
			$po_item_id=$con->sql_nextid();
			if($first_id==0) $first_id=$po_item_id;	
			if(!$r2['is_foc']) $is_not_foc[$r2['sku_item_id']]=$po_item_id;							
		}
		
		if($first_id>0){
			if(!is_new_id($po_id)){
				$con->sql_query("delete from po_items where branch_id=$branch_id and po_id=$po_id and id<$first_id") or die(mysql_error());			
			}
			$con->sql_query("delete from tmp_po_items where po_id=$po_id and branch_id=$branch_id and user_id=$sessioninfo[id]") or die(mysql_error());	
		}
		else{
			die("System error: Insert po_items failed. Please contact ARMS technical support.");
		}
		
	    //find foc item and update the foc_share_cost field
		$q3=$con->sql_query("select id, foc_share_cost from po_items where po_id=$form[id] and branch_id=$branch_id and is_foc order by id");	
		while($r3=$con->sql_fetchrow($q3)){
		    $r3['foc_share_cost']=unserialize($r3['foc_share_cost']);
		    if ($r3['foc_share_cost']){
		    	foreach($r3['foc_share_cost'] as $i=>$dummy){
					foreach($tmp_id as $k=>$v){
						if($i==$k){
							$foc_share_cost[$r3[id]][$is_not_foc[$v]]="on";
						}
					}
				}		
			}
			$foc_sz=serialize($foc_share_cost[$r3['id']]);
			$con->sql_query("update po_items set foc_share_cost='$foc_sz' where po_id=$form[id] and branch_id=$branch_id and id=$r3[id]");
		}
		
		// update all amount fields
		$appCore->poManager->reCalcatePOAmt($branch_id, $form['id']);
				
		if($config['foreign_currency'] && $rate_history_id){
			$appCore->poManager->sendCurrencyRateChangedNotification($branch_id, $form['id'], $rate_history_id);
		}
		
    	if($is_confirm){
    	    $formatted=sprintf("%05d",$form[id]);
    	    $con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
  		    $r4=$con->sql_fetchrow();
	        
	        log_br($sessioninfo['id'], 'PURCHASE ORDER', $form['id'], "Confirmed: ".' ('.$r4['report_prefix'].$formatted.')');
			
			$pm_to = get_pm_recipient_list2($form['id'],$form['approval_history_id'],0, 'confirmation',$branch_id,'po');
					
		    if ($last_approval){				
				/*
				// get approval data
				$con->sql_query("select * from 
				branch_approval_history 
				where id = ".mi($form['approval_history_id'])." and branch_id=$branch_id");
				$app_data = $con->sql_fetchassoc();
				$con->sql_freeresult();
			
				$notify_users = str_replace("|$sessioninfo[id]|", "|", $app_data['notify_users']);
				$pm_to = preg_split("/\|/", $notify_users);
				
				if(is_array($pm_to) && $pm_to){
					send_pm($pm_to, "Purchase Order Direct Approved (ID#$form[id], Dept:$dept_info[description], Vendor:$vendor_info[description]) Approved", "po.php?a=view&id=$form[id]&branch_id=$branch_id");
				}
				*/
				$po_no=post_process_po($form['id'], $branch_id);
				
				send_pm2($pm_to, "Purchase Order Direct Approved (ID#$form[id], Dept:$dept_info[description], Vendor:$vendor_info[description])", "po.php?a=view&id=$form[id]&branch_id=$branch_id");
		    	
			    header("Location: /po.php?type=approved&id=$form[id]&pono=$po_no");
			}
			else{
				$con->sql_query("update branch_approval_history set ref_id=$form[id] where id=$form[approval_history_id] and branch_id=$branch_id");
				
				send_pm2($pm_to, "Purchase Order Approval (ID#$form[id], Dept:$dept_info[description], Vendor:$vendor_info[description])", "po.php?a=view&id=$form[id]&branch_id=$branch_id",array('module_name'=>'po'));
				
			    $con->sql_query("select report_prefix from branch where id=$branch_id");
			    $report_prefix=$con->sql_fetchrow();
 				$po_no=sprintf("%s%06d(PP)", $report_prefix[0], $form['id']);
			    header("Location: /po.php?type=confirm&id=$form[id]&pono=$po_no");
		    }
    	}
		else{
		    $formatted=sprintf("%05d",$form[id]);
    	    $con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
  		    $r4=$con->sql_fetchrow();
			log_br($sessioninfo['id'], 'PURCHASE ORDER', $form['id'], "Saved: ".' ('.$r4['report_prefix'].$formatted.')');
		    header("Location: /po.php?type=save&id=$form[id]");
	    }
	}
    else{
		$smarty->assign("errm", $err);
		po_open($po_id, $branch_id);
	}
	exit;
}
			
function po_ajax_show_related_sku($po_id, $branch_id){
	global $con, $sessioninfo, $smarty;
	$form=$_REQUEST;
	$sku_item_id=intval($form['sku_item_id']);

	if (isset($po_id)){
		$idlist=array();
		$q1=$con->sql_query("select sku_item_id from tmp_po_items where branch_id=$branch_id and po_id=$po_id and user_id=$sessioninfo[id]");
		while($r1=$con->sql_fetchrow($q1)){
			$idlist[]=$r1[0];
		}
		if ($idlist)
			$idlist="and sku_items.id not in (".join(",",$idlist).")";
		else
			$idlist='';
	}
	else $idlist='';
	
	$items=array();
	$q2=$con->sql_query("select sku_id from sku_items where id=$sku_item_id");
	$r2=$con->sql_fetchrow($q2);			
	$sku_id=$r2['sku_id'];
	
	$q3=$con->sql_query("select sku_id, sku_item_code, sku_items.id, sku_items.description, sku_items.mcode, sku.varieties, sku_items.artno, sku_items.selling_price, sku_items.cost_price
from sku_items
left join sku on sku_id = sku.id 
left join category on sku.category_id = category.id 
where sku_id=$sku_id $idlist
group by sku_items.id
order by sku_items.id, sku_items.description, sku_items.artno, sku_items.mcode");
	while ($r3=$con->sql_fetchrow($q3)){
		$items[]=$r3;
	}
	$smarty->assign("items", $items);
	$smarty->assign("related_sku", '1');
	$smarty->display('po.new.show_sku.tpl');
}
function po_ajax_add_vendor_sku($po_id, $branch_id){
	global $con, $LANG, $smarty, $config;
	$form=$_REQUEST;
	$inserted_id = array();
	
	if(!$form['sel']) fail("No Item to Add.");
	
	foreach (array_keys($form['sel']) as $sku_item_id){
		$r=get_items_detail($sku_item_id, $branch_id);
		$sales=get_sales_trend($sku_item_id);
		$balance=get_stock_balance($sku_item_id);
		$r=array_merge($r, $sales,$balance);
		$ret=add_temp_row($r, $po_id, $branch_id);
		if ($ret==-2){
		    print "<script>alert('".jsstring(sprintf($LANG['PO_MAX_ITEM_CANT_ADD'], $config['po_set_max_items']))."');</script>\n";
			break;
		}		
		else {
			$inserted_id[] = $ret;
		}
	}
	
	$smarty->assign("form", $form);
	$smarty->assign("po_items", load_po_items($form, true, $inserted_id));
	$smarty->assign("po_ajax_row", true);
	$smarty->assign("allow_edit", 1);
	$smarty->display("po.new.ajax_add_po_row.tpl");
	
	//print "<script>refresh_tables();</script>";
}
function po_ajax_add_size_color($po_id, $branch_id){

	global $con, $LANG, $config;
	$form=$_REQUEST;

	foreach ($form['qty'] as $sku_item_id =>$quantity){
	    foreach ($quantity as $b_id => $qty){
        	if ($qty || $form['foc'][$sku_item_id][$b_id]){
                $no_input=false;
				break;
			}
        	else{
				$no_input=true;
			}
		}

		if ($no_input)  continue;

		$r=get_items_detail($sku_item_id, $branch_id);
		$sales=get_sales_trend($sku_item_id);
		$balance=get_stock_balance($sku_item_id);
		if(BRANCH_CODE=='HQ' && is_array($form['deliver_to'])) $unit['qty_loose_allocation']=serialize($quantity);
		else $unit['qty_loose'] = $quantity[$form['branch_id']];
		if(BRANCH_CODE=='HQ' && is_array($form['deliver_to'])) $unit['foc_loose_allocation']=serialize($form['foc'][$sku_item_id]);
		else $unit['foc_loose'] = $form['foc'][$sku_item_id][$form['branch_id']];
		$r=array_merge($r, $sales,$balance,$unit);
		$ret=add_temp_row($r, $po_id, $branch_id);
		if ($ret==-2){
		    print "<script>alert('".jsstring(sprintf($LANG['PO_MAX_ITEM_CANT_ADD'], $config['po_set_max_items']))."');</script>\n";
			break;
		}
	}
	print "<script>refresh_tables();</script>";
}



function po_ajax_refresh_foc_annotations($po_id, $branch_id){
	global $con, $sessioninfo;
    $foc_id=0;
	$arr=array();  
	// regenerate foc annotations and return as XML for update
	$q1=$con->sql_query("select id, is_foc, foc_share_cost 
from tmp_po_items 
where po_id=$po_id and branch_id=$branch_id and user_id=$sessioninfo[id] 
order by id");
	while($r1=$con->sql_fetchrow($q1)){
	    if($r1['is_foc']){
			$foc_id++;
			$arr[$r1['id']]['fid']=$foc_id;
		}
		else{
 			$arr[$r1['id']]['fid']='';		
		}			
	    $r1['foc_share_cost']=unserialize($r1['foc_share_cost']);
	    if($r1['foc_share_cost']){
			foreach($r1['foc_share_cost'] as $i => $dummy){
			    if ($arr[$i]['tag'] != '') $arr[$i]['tag'] .= "/";
				$arr[$i]['tag'] .= "$foc_id";
			}	
		}
	}
	$arr2=array();
	foreach($arr as $k=>$c){
	    $arr2[]=array("id"=>$k, "fid"=>$c['fid'], "tag"=>$c['tag']);
	}
	
	header('Content-Type: text/xml');
    print array_to_xml($arr2);
}
function po_ajax_update_foc_row(){
	global $con, $LANG, $branch_id;	
	$form=$_REQUEST;
	$item_id=intval($form['id']);
		
    if (!isset($form['sel_foc']) && !isset($form['no_item'])){
	    fail($LANG['PO_NO_ITEM_SELECTED']);
	}
	validate_duplicate_foc('sid');
	$foc_sz=serialize($form['sel_foc']);
	$con->sql_query("update tmp_po_items 
set foc_share_cost=".ms($foc_sz)." where id=$item_id and branch_id=$branch_id");
	print $LANG['PO_FOC_TABLE_UPDATED'];
}

function po_ajax_sel_foc_cost(){
	global $con, $branch_id, $sessioninfo, $smarty;
	$form=$_REQUEST;	
    $sid=intval($form['sid']);
    $id=intval($form['id']);
    $po_id=intval($form['po_id']);
    
    $foc_sz=array();
    if ($id>0){
		$q1=$con->sql_query("select foc_share_cost 
from tmp_po_items where id=$id and branch_id=$branch_id");
		$r1=$con->sql_fetchrow($q1);
		$foc_sz=unserialize($r1[0]);
	}
    $q2=$con->sql_query("select p1.*, sku_items.sku_item_code, sku_items.description from tmp_po_items p1 
left join sku_items on sku_item_id = sku_items.id 
where is_foc=0 and branch_id=$branch_id and po_id=$po_id ".(($po_id==0)? " and user_id= $sessioninfo[id]" : "") . " order by id");
    $smarty->assign("po_items", $con->sql_fetchrowset($q2));
    $smarty->assign("foc_item_id", $id);
    $smarty->assign("sid", $sid);
    $smarty->assign("foc_sel", $foc_sz);
    $smarty->display("po.new.sel_foc_cost.tpl");
}

function po_ajax_add_po_row($po_id, $branch_id, $is_foc){
	global $con, $LANG, $config, $sessioninfo, $smarty, $gst_list;
	$form=$_REQUEST;
	if($is_foc){
	    if (!isset($form['sel_foc']) && !isset($form['no_item']))
		    fail($LANG['PO_NO_ITEM_SELECTED']);
		validate_duplicate_foc('sku_item_id');
		$foc_sz=serialize($form['sel_foc']);	
	}
	
	save_and_update_po_items();				
	$grn_barcode = $form['grn_barcode'];
	if ($grn_barcode){
		$sku_info=get_grn_barcode_info($grn_barcode,true);
		if ($sku_info['err'])	return;
		else{
			$sku_item_id = $sku_info['sku_item_id'];
			$qty_pcs = $sku_info['qty_pcs'];
		} 
		
		//filter by department by default
		$cid = $_REQUEST['department_id'];
		if (!$_REQUEST['search_other_department']){ // item must belongs to current selected department
			$con->sql_query("select level from category where id=$cid");
			$cat_level=$con->sql_fetchfield(0);
			
			$sql = "select cc.* from sku_items si left join sku s on si.sku_id = s.id left join category c on s.category_id = c.id left join category_cache cc on c.id = cc.category_id where si.id = ".mi($sku_item_id)." limit 1";
			$q5 = $con->sql_query($sql);
			$r5 = $con->sql_fetchassoc($q5);
			$field = 'p'.$cat_level;
			
			if($form['department_id'] != $r5[$field]) fail('The SKU is not in this department');
		}elseif($sessioninfo['departments']){ // otherwise, need to check user's department
			if(!isset($sessioninfo['departments'][$cid])) fail('The SKU is not in this department');
		}	
	}else{
		$sku_item_id=intval($form['sku_item_id']);	
	}

	//check for block item
	$con->sql_query("select block_list,doc_block_list, active from sku_items where id = $sku_item_id limit 1");
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	$block_list_array = unserialize($tmp['block_list']);
	$doc_block_list_array = unserialize($tmp['doc_block_list']);
	$active = $tmp['active'];
	$sel_branch =$form['deliver_to'];
	
	if(BRANCH_CODE=='HQ')
	{
		if(!$active)
		{
			fail($LANG['PO_ITEM_IS_INACTIVE']);
		}
		//When Only 1 branch is selected
		if(count($sel_branch)==1)
		{				
			if ($block_list_array) {	
				//if select branch is HQ	
				if($sel_branch[0]==1)
				{
					$in_block_list = isset($block_list_array[$sel_branch[0]]);
					if ($in_block_list) fail($LANG['PO_ITEM_IS_BLOCKED']);
				}
				
			}
			elseif($doc_block_list_array){
				$in_grn_block_list = isset($doc_block_list_array['grn'][$sel_branch[0]]);
				if ($in_grn_block_list) fail($LANG['PO_ITEM_IS_BLOCKED']);
			}
		}
		//when multiple branches is selected
		elseif(count($sel_branch) >1)
		{
			//assign an array
			$blocked_branch=array();
			$block_counter=0;

			//Check how many items is blocked
			foreach($sel_branch as $br_id)
			{
				if ($block_list_array || $doc_block_list_array) {
					if($br_id==1)$in_block_list = isset($block_list_array[$br_id]);
					else $in_block_list =false;
				
				$in_grn_block_list = isset($doc_block_list_array['grn'][$br_id]);
				//either block po/grn will add 1 to counter
				if ($in_block_list||$in_grn_block_list)
				{
					$block_counter++;
					$blocked_branch[$br_id]=true;
				} 		
			}			
			
			}
			//When all item are blocked (po/grn) , deny item entry
			if($block_counter == count($sel_branch))
			{
				fail($LANG['PO_ITEM_IS_BLOCKED']);
			}
		}		
	}
	else
	{
		//Branches other than HQ
		if ($block_list_array) {
			$in_block_list = isset($block_list_array[$sessioninfo['branch_id']]);
			if ($in_block_list) fail($LANG['PO_ITEM_IS_BLOCKED']);
		}
		elseif($doc_block_list_array){
			$in_grn_block_list = isset($doc_block_list_array['grn'][$sessioninfo['branch_id']]);
			if ($in_grn_block_list) fail($LANG['PO_ITEM_IS_BLOCKED']);
		}
		elseif(!$active){ // is inactive item
			fail($LANG['PO_ITEM_IS_INACTIVE']);
		}
	}

	if(!$sku_item_id)	fail($LANG['PO_NO_ITEM_SELECTED']);
	
	$si_info_list = array();
	$si_info_list['sid_list'] = array();
	$is_bom_package = array();
	
	$bom_ref_num = time();
	
	// if got config bom additional type
	if($config['sku_bom_additional_type']){
		// check is bom package or not
		$con->sql_query("select sku.is_bom, si.bom_type
		from sku_items si
		join sku on sku.id=si.sku_id
		where si.id=".mi($sku_item_id));
		$bom_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($bom_info['is_bom'] && $bom_info['bom_type']=='package'){
			$is_bom_package = true;
			$bom_ref_num++;
			
			$con->sql_query("select bi.sku_item_id as sid,bi.qty from bom_items bi where bi.bom_id=".mi($sku_item_id)." order by bi.sku_item_id");
			while($r = $con->sql_fetchassoc()){
				$tmp_sid = mi($r['sid']);
				
				if(!$config['po_item_allow_duplicate']){
					if(in_array($tmp_sid, $si_info_list['sid_list']))	fail($LANG['PO_ITEM_ALREADY_IN_PO']);
				}
				
				$pcs = $r['qty'] * $qty_pcs;
				//if($qty_pcs)	$pcs *= $qty_pcs;
				
				$si_info_list['sid_list'][] = $tmp_sid;
				$si_info_list['list'][] = array(
					'sid'=> $tmp_sid,
					'pcs'=> $pcs,
					'bom_ref_num' => $bom_ref_num,
					'bom_qty_ratio' => $r['qty']
				);
			}
			$con->sql_freeresult();
		}
	}
	
	if(!$is_bom_package){
		$si_info_list['sid_list'][] = $sku_item_id;
		$si_info_list['list'][] = array(
			'sid'=>$sku_item_id,
			'pcs'=>$qty_pcs
		);
	}
	
	if(!$si_info_list['list'])	fail($LANG['PO_NO_ITEM_SELECTED']);	// no item?
	
	// check total items if created PO
	if($config['po_set_max_items']){
		$con->sql_query("select count(*) from tmp_po_items 
where po_id=$po_id and branch_id=$branch_id and user_id=$sessioninfo[id]");
		$curr_count = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		
		if($curr_count + count($si_info_list['list']) > $config['po_set_max_items']) fail(sprintf($LANG['PO_MAX_ITEM_CANT_ADD'], $config['po_set_max_items']));	
	}
	
	// loop for checking purpose
	foreach($si_info_list['list'] as $tmp_pi){
		$sid = mi($tmp_pi['sid']);
		$need_get_si_info = false;
		
		// need to check po agreement
		if($config['enable_po_agreement']){
			// do not allow to add item if got purchase agreement
			$con->sql_query("select pai.id 
			from purchase_agreement_items pai
			left join purchase_agreement pa on pa.branch_id=pai.branch_id and pa.id=pai.purchase_agreement_id
			where pai.sku_item_id=".mi($sid)." and pa.active=1 and pa.status=1 and pa.approved=1 limit 1");
			$pai = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($pai){
				fail($LANG['PO_NOT_ALLOW_OPEN_BUY_ITEM_WITH_PURCHASE_AGREEMENT']);
			}else{
				// check foc item as well
				$pa_sql = "select pafi.id
				from purchase_agreement_foc_items pafi
				join purchase_agreement pa on pa.branch_id=pafi.branch_id and pa.id=pafi.purchase_agreement_id
				where pafi.sku_item_id=".mi($sid)." and pa.active=1 and pa.status=1 and pa.approved=1 limit 1";
				$con->sql_query($pa_sql);
				$pai = $con->sql_fetchassoc();
				$con->sql_freeresult();
				if($pai)	fail($LANG['PO_NOT_ALLOW_OPEN_BUY_ITEM_WITH_PURCHASE_AGREEMENT']);
			}
		}

		// need to check duplicate po items		
		if(!$config['po_item_allow_duplicate']){
			// allow multiple row for same FOC item, NON-FOC not allowed multiple.
			if(!$is_foc){
				$con->sql_query("select id from tmp_po_items
				where po_id=$po_id and branch_id=$branch_id and sku_item_id=$sid and user_id=$sessioninfo[id] and is_foc=".mi($is_foc));
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($tmp) fail($LANG['PO_ITEM_ALREADY_IN_PO']);
			}
		}
		
		if($tmp_pi['pcs'] && mi($tmp_pi['pcs']) != $tmp_pi['pcs']){
			$need_get_si_info = true;
		}
		
		$tmp_si_info = array();
		if($need_get_si_info){
			$con->sql_query("select si.doc_allow_decimal
			from sku_items si
			where si.id=$sid");
			$tmp_si_info = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($tmp_pi['pcs'] && mi($tmp_pi['pcs']) != $tmp_pi['pcs']){
				if(!$tmp_si_info['doc_allow_decimal'])	fail('Item cannot contain decimal qty on non-decimal item.');
			}
		}
	}
	
	// loop to insert
	foreach($si_info_list['list'] as $tmp_pi){
		$sid = mi($tmp_pi['sid']);
		
		$r = array();
		$r = get_items_detail($sid,$branch_id);
		$sales = get_sales_trend($sid);
		$balance = get_stock_balance($sid);
		$r = array_merge($r, $sales,$balance);
		$r['bom_ref_num'] = $tmp_pi['bom_ref_num'];
		$r['bom_qty_ratio'] = $tmp_pi['bom_qty_ratio'];
		$r['blocked_branch']=$blocked_branch;
		$ret = add_temp_row($r, $po_id, $branch_id, $foc_sz, $tmp_pi['pcs']);		
		if ($ret==-1)
			fail($LANG['PO_ITEM_ALREADY_IN_PO']);
		elseif ($ret==-2)
			fail(sprintf($LANG['PO_MAX_ITEM_CANT_ADD'], $config['po_set_max_items']));

		if($config['enable_gst'] && $form['branch_is_under_gst']){
			if(BRANCH_CODE=='HQ'){
				if(!$r['gst_selling_price_allocation']){	            
					// get sku inclusive tax
					$is_sku_inclusive = get_sku_gst("inclusive_tax", $r['sku_item_id']);
					// get sku original output gst
					$output_gst = get_sku_gst("output_tax", $r['sku_item_id']);
				
					if($output_gst){
						if($is_sku_inclusive == 'yes'){
							// find the selling price before tax
							$r['selling_price_allocation'][1] = round($r['selling_price_allocation'][1]/((100+$output_gst['rate'])/100),2);
						}
					}
				}    
			}
			else{
				 if(!$r['gst_selling_price']){	            
					// get sku inclusive tax
					$is_sku_inclusive = get_sku_gst("inclusive_tax", $r['sku_item_id']);
					// get sku original output gst
					$output_gst = get_sku_gst("output_tax", $r['sku_item_id']);
				
					if($output_gst){
						if($is_sku_inclusive == 'yes'){
							// find the selling price before tax
							$r['selling_price'] = round($r['selling_price']/((100+$output_gst['rate'])/100),2);
						}
					}
				}    
			}
		}
        
		// if got use GST and no GST id found
		/*if($form['is_under_gst']){
			// get GST info from category
			$output_gst = get_sku_gst("output_tax", $sid);
			$input_gst = get_sku_gst("input_tax", $sid);
			
			if($output_gst){
				$r['selling_gst_id'] = $output_gst['id'];
				$r['selling_gst_code'] = $output_gst['code'];
				$r['selling_gst_rate'] = $output_gst['rate'];
			}
			
			if($input_gst){
				$r['cost_gst_id'] = $input_gst['id'];
				$r['cost_gst_code'] = $input_gst['code'];
				$r['cost_gst_rate'] = $input_gst['rate'];
			}
		}*/
	
	    $smarty->assign("item", $r);
		$arr=array();
		//start here
		//$smarty->assign("form", array('po_option'=>$form['po_option'], 'deliver_to'=>$form['deliver_to'], 'po_branch_id'=>$form['po_branch_id'], 'branch_id'=>$form['branch_id'],'po_create_type'=>$form['po_create_type'], 'is_under_gst'=>$form['is_under_gst'])); 
		$smarty->assign('form', $form);
		
		$rid=$con->sql_query("select count(*) from tmp_po_items where po_id=$po_id and branch_id =$branch_id and user_id=$sessioninfo[id]");	    	
		$item_count=$con->sql_fetchrow($rid);
		$con->sql_freeresult($rid);
		$smarty->assign("item_n", $item_count[0]);
		$smarty->assign("allow_edit", 1);
	
		$ret = array();
		$ret['id'] = $r['id'];
		$ret['html'] = $smarty->fetch("po.new.po_row.tpl");
		
		// check if got last po row
		//$ret['last_po_html'] = po_ajax_add_last_po_row($r['id'], $sku_item_id);
		$last_po_html = po_ajax_add_last_po_row($r['id'], $sku_item_id);
		if($last_po_html)	$ret['html'] .= $last_po_html;
	
		$row_item[] = $ret;
		//print_r($r);exit;
	}

	print json_encode($row_item);
	//$rowdata=$smarty->fetch("po.new.po_row.tpl");
	//$arr[]=array("id" => $r['id'], "rowdata" => $rowdata);
	//header('Content-Type: text/xml');
    //print array_to_xml($arr);
}

function po_ajax_add_last_po_row($pi_id, $sku_item_id){
	
	global $branch_id, $smarty;
	$form=$_REQUEST;
	
	//$sku_item_id=intval($form['sku_item_id']);
	//$po_branch_id=intval($form['po_branch_id']);
							
	$r=get_last_po_item($sku_item_id, $branch_id, $po_branch_id, $form['po_date'], $form['id']);
	if($r){
		$r['pi_id'] = $pi_id;
	    $smarty->assign("l_item", $r);
		$arr = array();
										
		// Check and dont overwrite if form already exists
		$curr_form = $smarty->get_template_vars("form");
		if(!$curr_form){
			//$smarty->assign("form", array('po_option'=>$form['po_option'], 'deliver_to'=>$form['deliver_to'], 'po_branch_id'=>$form['po_branch_id'], 'branch_id'=>$form['branch_id'], 'is_under_gst'=>$form['is_under_gst']));
			$smarty->assign("form", $form);
		}
		
		
		return $smarty->fetch("po.new.last_po_row.tpl");
		/*$rowdata = $smarty->fetch("po.new.last_po_row.tpl");	
    	$arr[] = array("rowdata" => $rowdata);
    	
		header('Content-Type: text/xml');
        print array_to_xml($arr);	*/	
	}
	else{
		return false; // fail('No Last PO Record Found for this SKU.');	
	}
}

function copy_to_tmp($po_id, $branch_id){
	global $con, $sessioninfo, $config;
	
	//delete ownself PO items in tmp table
	$con->sql_query("delete from tmp_po_items where po_id=$po_id and branch_id=$branch_id and user_id=$sessioninfo[id]");
    //get po_item_id	
	$q1 = $con->sql_query("select id, sku_item_id from po_items where po_id=$po_id and branch_id=$branch_id order by id");
	while($r1=$con->sql_fetchrow($q1)){
		$tmp_id[$r1['id']]=$r1['sku_item_id'];
	}	
		
    //update items
    $cols = array('po_id', 'branch_id', 'user_id', 'sku_item_id', 'qty', 'selling_price', 'selling_price_allocation', 'qty_allocation', 'is_foc', 'foc_share_cost', 'foc_noprint', 'order_uom_id', 'order_price', 'resell_price', 'order_uom_fraction', 'qty_loose_allocation', 'tax', 'discount', 'qty_loose', 'remark', 'remark2', 'foc_allocation', 'foc_loose_allocation', 'foc', 'foc_loose', 'artno_mcode', 'disc_remark', 'delivered', 'balance', 'cost_indicate', 'selling_uom_fraction', 'selling_uom_id','sales_trend','stock_balance','parent_stock_balance','so_branch_id','so_item_id','selling_gst_id', 'selling_gst_code', 'selling_gst_rate','cost_gst_id', 'cost_gst_code', 'cost_gst_rate', 'gst_selling_price', 'gst_selling_price_allocation','tax_amt','discount_amt','item_gross_amt','item_nett_amt','item_gst_amt','item_amt_incl_gst','item_total_selling','item_total_gst_selling','item_allocation_info');
    if($config['enable_po_agreement']){
    	$cols[] = 'pa_branch_id';
    	$cols[] = 'pa_item_id';
    	$cols[] = 'pa_foc_item_id';
    }
    
    if($config['sku_bom_additional_type']){
    	$cols[] = 'bom_ref_num';
    	$cols[] = 'bom_qty_ratio';
    }
    
	$q2 = $con->sql_query("select * from po_items where po_id=$po_id and branch_id = $branch_id order by id");	
	while($r2=$con->sql_fetchrow($q2)){
		// if sales trend empty, get it 				
		if(!$r2['sales_trend']) $r2['sales_trend'] = serialize(get_sales_trend($r2['sku_item_id']));
	    $r2['branch_id']=$branch_id;
	    $r2['po_id']=$po_id;
	    $r2['user_id']=$sessioninfo['id'];
		
		$con->sql_query("insert into tmp_po_items " . mysql_insert_by_field($r2, $cols));
		$po_item_id= $con->sql_nextid();		
		if(!$r2['is_foc']){
			$is_not_foc[$r2['sku_item_id']]=$po_item_id;		
		}	
	}
				
    //find foc item and update the foc_share_cost field
	$q3 = $con->sql_query("select id, foc_share_cost from tmp_po_items where po_id=$po_id and branch_id=$branch_id and is_foc order by id");	
	while($r3=$con->sql_fetchrow($q3)){
	    $r3['foc_share_cost']=unserialize($r3['foc_share_cost']);
	    if ($r3['foc_share_cost']){
	    	foreach($r3['foc_share_cost'] as $i => $dummy){
				foreach($tmp_id as $k=>$v){
					if($i==$k){
						$foc_share_cost[$r3[id]][$is_not_foc[$v]]="on";
					}
				}
			}		
		}
		$foc_sz=serialize($foc_share_cost[$r3[id]]);
		$con->sql_query("update tmp_po_items set foc_share_cost='$foc_sz' where po_id=$po_id and branch_id=$branch_id and id=$r3[id]");				
	}	
}

function po_sheet_print($form, $po_items, $tpl){
	global $smarty, $config;
	$send_by_email = $_REQUEST['send_by_email'];
	
	// filter off those po items with zero qty
	foreach($po_items as $item_id=>$r){
		$row_qty = $r['qty']+$r['qty_loose']+$r['foc']+$r['foc_loose'];
		if($row_qty == 0) unset($po_items[$item_id]);
	}
	$page_items=(isset($config['po_no_of_item_perpage'])?$config['po_no_of_item_perpage']:15);
	//$smarty->assign("page_items",$page_items);
	$totalpage=ceil(count($po_items)/$page_items);

	$item_per_page = (isset($config['po_no_of_item_perpage'])?$config['po_no_of_item_perpage']:15);
    $item_per_lastpage = (isset($config['po_no_of_item_perpage'])?$config['po_no_of_item_perpage']:15);
    $totalpage = 1 + ceil((count($po_items)-$item_per_lastpage)/$item_per_page);
	$smarty->assign("page_items", $item_per_page);
	
	// start print po
	$item_index = -1;
	$item_no = -1;
	$page = 1;
	
	$page_item_list = array();
	$page_item_info = array();
		
	foreach($po_items as $r){	// loop for each item
		if($item_index+1>=$item_per_page){
			$page++;
			$item_index = -1;
		}
		
		$item_no++;
		$item_index++;
		$r['item_no'] = $item_no;
		
		$page_item_list[$page][$item_index] = $r;	// add item to this page
		$r['additional_description'] = unserialize($r['additional_description']);
		if($config['sku_enable_additional_description'] && $r['additional_description']){
			foreach($r['additional_description'] as $desc){
				if($item_index+1>=$item_per_page){
					$page++;
					$item_index = -1;
				}
				
				$item_index++;
				$desc_row = array();
				$desc_row['description'] = $desc;
				$desc_row['qty'] = $r["qty"];
				$desc_row['qty_loose'] = $r["qty_loose"];
				$desc_row['foc'] = $r["foc"];
				$desc_row['foc_loose'] = $r["foc_loose"];
				$page_item_list[$page][$item_index] = $desc_row;
				$page_item_info[$page][$item_index]['not_item'] = 1;
			}
		}
	}

	// fix last page
	if(count($page_item_list[$page]) > $item_per_lastpage){	// last page item too many
		$page++;
		$page_item_list[$page] = array();
	}
	$totalpage = count($page_item_list);

	if($send_by_email){
		$smarty->assign("send_email", 1);
		//$smarty->assign("DOCUMENT_ROOT", `pwd`);
		
		$p = array('mod'=>'po');
		$lg = smarty_get_logo_url($p, $smarty);
		$imagedata = file_get_contents($lg);
		$encoded_comp_logo = base64_encode($imagedata);
		$smarty->assign("encoded_comp_logo", $encoded_comp_logo);
	}
	$html = '';
	foreach($page_item_list as $page => $item_list){
		$this_page_num = ($page < $totalpage) ? $item_per_page : $item_per_lastpage;
		$smarty->assign("PAGE_SIZE", $this_page_num);
		$smarty->assign("page", "Page $page of $totalpage");
		$smarty->assign("start_counter",$item_list[0]['item_no']);
		$smarty->assign("is_lastpage", ($page >= $totalpage));
		$smarty->assign("po_items", $item_list);
		$smarty->assign("page_item_info", $page_item_info[$page]);
		if($send_by_email){
			$html .= $smarty->fetch("$tpl");
		}else{
			$smarty->display("$tpl");
		}
		$smarty->assign("skip_header",1);
	}
	
	if($send_by_email){
		po_send_email2($form, $html);
	}
}
//add new item in temporary table
function add_temp_row(&$r, $po_id, $branch_id, $foc_sz='',$qty_pcs=''){	
	global $con, $sessioninfo, $LANG, $config;
	
 	if($r['artno'])
		$r['artno_mcode']=$r['artno'];
	else
		$r['artno_mcode']=$r['mcode'];

	$r['po_id']=$po_id;
	$r['branch_id']=$branch_id;
	$r['sku_item_id']=$r['id'];
    $r['user_id']=$sessioninfo['id'];
    $r['sku']=$r['description'];
	$r['sales_trend']=serialize($r['sales_trend']);
	$r['stock_balance']=serialize($r['stock_balance']);
	$r['parent_stock_balance']=serialize($r['parent_stock_balance']);
    if($r['order_uom_fraction'])
    	$r['order_uom_fraction']=$r['order_uom_fraction'];
	else
		$r['order_uom_fraction']='1';
	
    if($r['uom_id'])
    	$r['order_uom_id']=$r['uom_id'];
	else
		$r['order_uom_id']='1';
	   
    if(!isset($r['resell_price']))
 		$r['resell_price']=$r['selling_price'];	
    if(!isset($r['order_price']))
 		$r['order_price']=$r['cost_price'];
	
    if($foc_sz){
        $r['is_foc']=1;
        $r['foc_share_cost']=$foc_sz;
	}
	else{
		$r['is_foc']=0;
	}
	
	if ($qty_pcs){
		if ($_REQUEST['deliver_to']){
			//multi branch
			foreach($_REQUEST['deliver_to'] as $bid){
				$r['qty_loose_allocation'][$bid] = $qty_pcs; 
			}
			$r['qty_loose_allocation'] = serialize($r['qty_loose_allocation']);
		}else{
			//single branch
			$r['qty_loose'] = $qty_pcs;
		}
	}

	if($r['selling_price_allocation']) $r['selling_price_allocation'] = serialize($r['selling_price_allocation']);
	if($r['gst_selling_price_allocation']) $r['gst_selling_price_allocation'] = serialize($r['gst_selling_price_allocation']);
	
    if(!$config['po_item_allow_duplicate']){
		// allow multiple row for same FOC item, NON-FOC not allowed multiple.
		if(!$r['is_foc']){
			$con->sql_query("select id from tmp_po_items
	where po_id=$r[po_id] and branch_id=$r[branch_id] and sku_item_id=$r[sku_item_id] and user_id=$sessioninfo[id] and is_foc=$r[is_foc]");
			if($con->sql_numrows()>0) return -1;
		}
	}
	
	// check total items if created PO
	if($config['po_set_max_items']){
		$con->sql_query("select count(*) from tmp_po_items 
where po_id=$r[po_id] and branch_id=$r[branch_id] and user_id=$sessioninfo[id]");
		$t=$con->sql_fetchrow();
		if($t[0]>=$config['po_set_max_items']) return -2;		
	}
	//print "insert into tmp_po_items ".mysql_insert_by_field($r, array('po_id', 'branch_id', 'user_id', 'sku_item_id', 'artno_mcode', 'order_uom_fraction', 'is_foc', 'foc_share_cost', 'selling_price', 'resell_price', 'order_price', 'order_uom_id', 'cost_indicate', 'sales_trend', 'stock_balance','parent_stock_balance','qty_loose','qty_loose_allocation','foc_loose_allocation'));
	
	$col_list = array('po_id', 'branch_id', 'user_id', 'sku_item_id', 'artno_mcode', 'order_uom_fraction', 'is_foc', 'foc_share_cost', 'selling_price', 'resell_price', 'order_price', 'order_uom_id', 'cost_indicate', 'sales_trend', 'stock_balance','parent_stock_balance','qty_loose','qty_loose_allocation','foc_loose','foc_loose_allocation','selling_price_allocation');
	if($config['sku_bom_additional_type']){
		$col_list[] = "bom_ref_num";
		$col_list[] = "bom_qty_ratio";
	}
	
	if($config['enable_gst']){
		$col_list[] = "selling_gst_id";
		$col_list[] = "selling_gst_code";
		$col_list[] = "selling_gst_rate";
		$col_list[] = "cost_gst_id";
		$col_list[] = "cost_gst_code";
		$col_list[] = "cost_gst_rate";
		$col_list[] = "gst_selling_price";
		$col_list[] = "gst_selling_price_allocation";
	}
	
    $con->sql_query("insert into tmp_po_items ".mysql_insert_by_field($r, $col_list));
 	$r['id']=$con->sql_nextid();
	$r['sales_trend']=unserialize($r['sales_trend']);
	$r['stock_balance']=unserialize($r['stock_balance']);
	$r['parent_stock_balance']=unserialize($r['parent_stock_balance']);
	$r['qty_loose_allocation']=unserialize($r['qty_loose_allocation']);
	$r['selling_price_allocation']=unserialize($r['selling_price_allocation']);
	$r['gst_selling_price_allocation']=unserialize($r['gst_selling_price_allocation']);
	
 	return $r['id'];
}
function save_and_update_po_items(){
	global $con, $branch_id, $config, $gst_list;
	$form=$_REQUEST;
	
	$pfield='';
 	if (isset($form['is_foc'])) $pfield='is_foc'; 
	if($pfield){
		foreach($form[$pfield] as $k=>$v){
			$update=array();
			$update['artno_mcode']=$form['artno_mcode'][$k];
		    $update['remark']=$form['item_remark'][$k];
		    $update['remark2']=$form['item_remark2'][$k];
		    $update['selling_price']=$form['selling_price'][$k];
		    $update['order_price']=$form['order_price'][$k];
			$update['cost_indicate']=$form['cost_indicate'][$k];
		    $update['resell_price']=$form['resell_price'][$k];
		    $update['order_uom_id']=$form['order_uom_id'][$k];
		    $update['order_uom_fraction']=$form['order_uom_fraction'][$k];
		    $update['selling_uom_id']=$form['selling_uom_id'][$k];
		    $update['selling_uom_fraction']=$form['selling_uom_fraction'][$k];
		    $update['sales_trend']=serialize($form['sales_trend'][$k]);
		    $update['stock_balance']=serialize($form['stock_balance'][$k]);
			if($form['branch_is_under_gst']) $update['gst_selling_price']=$form['gst_selling_price'][$k];
					    
		    if(BRANCH_CODE=='HQ' && is_array($form['deliver_to'])){
				$update['qty_allocation']=serialize($form['qty_allocation'][$k]);
		    	$update['qty_loose_allocation']=serialize($form['qty_loose_allocation'][$k]);
		    	$update['foc_allocation']=serialize($form['foc_allocation'][$k]);
		    	$update['foc_loose_allocation']=serialize($form['foc_loose_allocation'][$k]);
		    	$update['selling_price_allocation']=serialize($form['selling_price_allocation'][$k]);
				if($form['branch_is_under_gst']) $update['gst_selling_price_allocation']=serialize($form['gst_selling_price_allocation'][$k]);
				$update['qty']='';
		    	$update['qty_loose']='';
				$update['foc']='';
		    	$update['foc_loose']='';
			}
			else{
				$update['qty_allocation']='';
		    	$update['qty_loose_allocation']='';
		    	$update['foc_allocation']='';
		    	$update['foc_loose_allocation']='';
		    	$update['selling_price_allocation']='';		    	
		    	$update['gst_selling_price_allocation']='';		    	
				$update['qty']=$form['qty'][$k];
		    	$update['qty_loose']=$form['qty_loose'][$k];
				$update['foc']=$form['foc'][$k];
		    	$update['foc_loose']=$form['foc_loose'][$k];
		    }
		    $update['tax']=$form['tax'][$k];
		    $update['discount']=$form['discount'][$k];
			
			if($config['allow_sales_order']){
				$update['so_branch_id'] = $form['so_branch_id'][$k];
				$update['so_item_id'] = $form['so_item_id'][$k];
			}
			
			if($config['enable_po_agreement']){
				$update['pa_branch_id'] = $form['pa_branch_id'][$k];
				$update['pa_item_id'] = $form['pa_item_id'][$k];
				$update['pa_foc_item_id'] = $form['pa_foc_item_id'][$k];
			}
			
			if($form['branch_is_under_gst']){
				$update['selling_gst_id'] = $form['selling_gst_id'][$k];
				$update['selling_gst_code'] = $form['selling_gst_code'][$k];
				$update['selling_gst_rate'] = $form['selling_gst_rate'][$k];
			}
			
			if($form['is_under_gst']){
				$update['cost_gst_id'] = $form['cost_gst_id'][$k];
				$update['cost_gst_code'] = $form['cost_gst_code'][$k];
				$update['cost_gst_rate'] = $form['cost_gst_rate'][$k];
			}
			
			$con->sql_query("update tmp_po_items set " . mysql_update_by_field($update) . " where id=$k and branch_id=$branch_id");			
		}
	}
}
function get_payment_term($vendor_id, $branch_id){
	global $con, $sessioninfo;
	$q=$con->sql_query("select term from branch_vendor where vendor_id=".mi($vendor_id)." and branch_id=".mi($branch_id));		
	$r=$con->sql_fetchrow($q);
	
	if(!$r){
		$q=$con->sql_query("select term from vendor where id=".mi($vendor_id));		
		$r=$con->sql_fetchrow($q);
	}
	$term=$r[0];
	return $term;	
}
//checking duplicate foc items
function validate_duplicate_foc($var){
	global $LANG;
	$form=$_REQUEST;	
	$total_foc=count($form['sel_foc']);
	if($total_foc==1){
		foreach($form['sel_foc'] as $k=>$v){
			if($form[$var]==$form['foc_items'][$k])
				fail($LANG['PO_DUPLICATE_FOC']);
		}
	}
}
function load_po_list($t=0){
	global $con, $sessioninfo, $smarty, $config;
	if(!$t) $t=intval($_REQUEST['t']);
	if(!$sessioninfo['departments'])
		$depts="(0)";
	else
		$depts="(" . join(",", array_keys($sessioninfo['departments'])) . ")";
	if($sessioninfo['level']>=9999)
		$owner_check="";
	elseif($sessioninfo['level']>=800)
		$owner_check="(po.department_id in $depts) and ";
	elseif($sessioninfo['level']>=400)
		$owner_check="((po.branch_id=$sessioninfo[branch_id] or po_branch_id=$sessioninfo[branch_id]) and po.department_id in $depts) and ";
	else
		$owner_check="(user_id=$sessioninfo[id] or allowed_user like '%$sessioninfo[id]%') and";

	switch($t){
		//search PO 
	    case 0:
			$str = $_REQUEST['search'];
			$vendor_id = $_REQUEST['vendor_id'];
			if(!$str && !$vendor_id) die('Cannot search empty string');

			$where = array();
			if($str){
				$where[] = '(po.id='.mi($_REQUEST['search']).' or po.po_no like '.ms('%'.replace_special_char($_REQUEST['search'])).')';
			}
			
			if($vendor_id){
				$where[] = "po.vendor_id = ".mi($vendor_id);
			}
			
			$where = join(" and ", $where);
	        break;
		// show saved PO
		case 1:
		    $owner_check="po.user_id=$sessioninfo[id] and";
        	$where="po.status=0 and not po.approved and po.active=1 ";
        	break;
		// show waiting for approval (and KIV)
		case 2:
		    $where="(po.status=1 or po.status=3) and not po.approved and po.active=1";
		    break;
		// show inactive
		case 3: 
		   	$where="(po.status=4 or po.status=5)";
		    break;
		// show approved
		case 4:
		    $where="po.approved=1 and po.active=1";
		    break;
		// show rejected	
		case 5:
		    $where="po.status=2 and not po.approved and po.active=1";
		    break;
		// show approved HQ PO 
		case 6:
        	$where="po.branch_id=1 and po.approved=1 and po.status=1 and not po.active and po.deliver_to<>''";
        	break;
	}
	// pagination
	$start = intval($_REQUEST['s']);
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else{
		if (isset($config['document_page_size'])) $sz=$config['document_page_size'];
			else	$sz = 25;
	}
	$con->sql_query("select count(*) from po where $owner_check $where");

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


/*
	print "select po.*, branch_approval_history.approvals, user.u, vendor.description as vendor, category.description as dept, b1.code as branch, b2.code as po_branch, b1.report_prefix
from po
left join vendor on vendor_id = vendor.id
left join category on po.department_id = category.id
left join branch b1 on po.branch_id = b1.id
left join branch b2 on po_branch_id = b2.id
left join user on user.id = po.user_id
left join branch_approval_history on (po.approval_history_id = branch_approval_history.id and po.branch_id = branch_approval_history.branch_id)
where $owner_check $where
order by po.last_update desc limit $start, $sz";
*/
	$q1=$con->sql_query("select po.*, branch_approval_history.approvals, user.u, vendor.description as vendor, 
						category.description as dept, b1.code as branch, b2.code as po_branch, 
						b1.report_prefix,branch_approval_history.approval_order_id, vendor.code as vendor_code, 
						if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id
from po
left join vendor on vendor_id = vendor.id
left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = po.branch_id
left join category on po.department_id = category.id
left join branch b1 on po.branch_id = b1.id
left join branch b2 on po_branch_id = b2.id
left join user on user.id = po.user_id
left join branch_approval_history on (po.approval_history_id = branch_approval_history.id and po.branch_id = branch_approval_history.branch_id)
where $owner_check $where
order by po.last_update desc limit $start, $sz");
	while ($r1=$con->sql_fetchrow($q1)){
		if($r1['branch_id']=='1' && $r1['approved']){
			$q2=$con->sql_query("select po_no, po_branch_id, po.id as po_id, branch_id, b1.report_prefix as b_name
	from po
	left join branch b1 on b1.id=po_branch_id
	where hq_po_id = $r1[id]");		
			$r2=$con->sql_fetchrowset($q2);
			$r1['po_no_list']=$r2;
		}
		$r1['deliver_to'] = unserialize($r1['deliver_to']);
		$r1['deliver_to_count'] = $r1['deliver_to'] ? count($r1['deliver_to']) : 0;

		
		if($r1['active'] && $r1['status']==1 && $r1['approved'] && $r1['delivered'] && $r1['po_no']){
			$r1['delivered_grn_list'] = find_delivered_grn($r1['branch_id'], $r1['id'], $r1['po_no']);
		}
		$po_list[]=$r1;
		//print"<pre>";
		//print_r($r1['delivered_grn_list']);
		//print"</pre>";
	}
	
	//print_r($po_list);
	$con->sql_freeresult($q1);
	$smarty->assign("po_list", $po_list);
	$smarty->display("po.list.tpl");
}

function ajax_delete_po_row(){
	global $con, $branch_id, $LANG;
	
	$delete_id_list = $_REQUEST['delete_id_list'];
	
	if(!is_array($delete_id_list) || !$delete_id_list)	fail($LANG['PO_NO_ITEM_SELECTED']);
	
	foreach($delete_id_list as $po_item_id){
		$con->sql_query("delete from tmp_po_items where id=".mi($po_item_id)." and branch_id=$branch_id");
	}
}

// new function to check PO is allowed to save/confirm while vendor got uncheckout GRA
function validate_po_without_checkout_gra($bid){
	global $con, $sessioninfo, $LANG;
	
	$form = $_REQUEST;
	if(!$form['vendor_id']) return; // no vendor ID selected, return
	
	$q1 = $con->sql_query("select * from vendor where id = ".mi($form['vendor_id']));
	$v_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	// found it is allowed, return immediately
	if($v_info['allow_po_without_checkout_gra']) return;
	
	//$filter = " and category.id in (".join(",",array_keys($sessioninfo['departments'])).")";
	
	$q1 = $con->sql_query("select * 
						   from gra 
						   left join category c on c.id = gra.dept_id
						   where gra.status in (0,2) and gra.returned=0 and gra.vendor_id = ".mi($form['vendor_id'])." and gra.branch_id = ".mi($bid).$filter);
	
	if($con->sql_numrows($q1) == 0) return; // no GRA record found, return
	
	return $LANG['PO_CHECKOUT_GRA_REQUIRED'];
}

function check_tmp_item_exists() {
	global $con, $sessioninfo;
	
	if ($_REQUEST['master_uom_id']) {
		$sql = "select count(*) as c from tmp_po_items where id in (".join(',',array_keys($_REQUEST['master_uom_id'])).") and branch_id = ".mi($_REQUEST['branch_id'])." limit 1";
		$con->sql_query($sql);
		if ($con->sql_fetchfield('c') == count($_REQUEST['master_uom_id'])) print 'OK';
		else print "Error saving document : Probably it is opened & saved before in other window/tab";
		exit;
	}
	else {
		print 'OK';
		exit;
	}
}

function ajax_reload_currency_rate(){
	global $appCore;
	
	$currency_code = trim($_REQUEST['currency_code']);
	$po_date = trim($_REQUEST['po_date']);
	
	$ret = array();
	$result = $appCore->currencyManager->loadCurrencyRateByDate($po_date, $currency_code);
	if($result['err'])	$ret['err'] = $result['err'];
	else{
		$ret['ok'] = 1;
		$ret['rate'] = mf($result['rate']);
	}
	
	die(json_encode($ret));
}

function ajax_open_add_item_by_csv_popup(){
	global $smarty, $headers, $sample;
	
	//create file if not exist
	if (!is_dir("attachments"))	check_and_create_dir("attachments");
	if (!is_dir("attachments/import_po_item"))	check_and_create_dir("attachments/import_po_item");
	
	$form =array();
	$form = $_REQUEST;
	$form['sample_format'] = $form['po_branch_id'] ? 1 : 2;
	
	$smarty->assign("form", $form);
	$smarty->assign("sample_headers", $headers);
	$smarty->assign("sample", $sample);
	
	$ret = array();
	$ret['ok'] = 1;
	$ret['html'] = $smarty->fetch('po.upload_csv.tpl');
	print json_encode($ret);
}

function download_sample_po(){
	global $headers, $sample;
	
	header("Content-type: application/msexcel");
	header("Content-Disposition: attachment; filename=sample_import_po_item.csv");
	
	print join(",", array_values($headers[$_REQUEST['sample_format']]));
	foreach($sample[$_REQUEST['sample_format']] as $sample) {
		$data = array();
		foreach($sample as $d) {
			$data[] = $d;
		}
		print "\n\r".join(",", $data);
	}
}

function show_result(){
	global $con, $smarty, $config, $headers, $sample, $sessioninfo, $LANG;
	
	$form = $_REQUEST;
	$id= mi($form['id']);
	$branch_id = mi($form['branch_id']);
	$po_branch_id = mi($form['po_branch_id']);
	$department_id = mi($form['department_id']);
	$file = $_FILES['import_csv'];
	$count_deliver_to = count($form['deliver_to']);
	$multi_delivery_branch = $count_deliver_to > 0 ? true : false;
	$max_column = $count_deliver_to > 0 ? (2+$count_deliver_to) : 3;
	
	$f = fopen($file['tmp_name'], "rt");
	$line = fgetcsv($f);
	
	$item_lists = $code_list = array();
	if((!$multi_delivery_branch && count($line)==$max_column) || ($multi_delivery_branch && count($line) > 2 && count($line) <= $max_column )) {
		$branches = $item_list = array();
		if($multi_delivery_branch){
			$deliver_to_list = implode(",", array_values($form['deliver_to']));
			$con->sql_query("select * from branch where active=1 and id in(".$deliver_to_list.")");
			while($r1=$con->sql_fetchassoc()){
				$branches[$r1['code']] = $r1['id'];
			}
			$con->sql_freeresult();
		}
		
		$total_row = 0;
		while($r = fgetcsv($f)){
			$error = array();
			$result['ttl_row']++;
			
			foreach($r as $tmp_row => $val){
				$r[$tmp_row] = utf8_encode(trim($val));
			}
			
			$ins = array();
			switch($form['method']) {
				case '1':
					$ins['item_code'] = trim($r[0]);
					$ins['order_cost'] = mf($r[1]);
					if($multi_delivery_branch){
						for($k=2; $k < count($line); $k++){
							$col= $k-2;
							$header_info = explode("-", $line[$k]);
							$branch_code = strtoupper(trim($header_info[0]));
							if(!$branches[$branch_code]){
								$error[] = "Invalid Branch Code($branch_code)";
							}
							$ins['pcs_'.$col] = mf($r[$k]);
						}
					}else{
						$ins['pcs'] = mf($r[2]);
					}
					break;
			}
			
			if($ins['item_code']) {
				$con->sql_query("select id, doc_allow_decimal from sku_items where (sku_item_code = ".ms($ins['item_code'])." or mcode = ".ms($ins['item_code'])." or link_code = ".ms($ins['item_code'])." or artno = ".ms($ins['item_code']).")");
				$item_info = $con->sql_fetchassoc();
				$count = $con->sql_numrows();
				$con->sql_freeresult();
				
				if($count <= 0 && strlen($ins['item_code']) == 13){
					$ins['item_code'] = substr($ins['item_code'], 0, 12);
					$con->sql_query("select id from sku_items where (sku_item_code = ".ms($ins['item_code'])." or mcode = ".ms($ins['item_code'])." or link_code = ".ms($ins['item_code'])." or artno = ".ms($ins['item_code']).")");
					$item_info = $con->sql_fetchassoc();
					$count = $con->sql_numrows();
					$con->sql_freeresult();
				}
				
				$sku_item_id = mi($item_info['id']);
				$doc_allow_decimal = $item_info['doc_allow_decimal'];
				if(!$sku_item_id) $error[] = 'Item Code('.$ins['item_code'].') not found';
				if($count > 1)  $error[] = 'Item Code('.$ins['item_code'].') match result more than 1';
				
				//filter by department by default
				if (!$_REQUEST['search_other_department']){ // item must belongs to current selected department
					$con->sql_query("select level from category where id=$department_id");
					$cat_level=$con->sql_fetchfield(0);
					
					$sql = "select cc.* from sku_items si left join sku s on si.sku_id = s.id left join category c on s.category_id = c.id left join category_cache cc on c.id = cc.category_id where si.id = ".mi($sku_item_id)." limit 1";
					$q5 = $con->sql_query($sql);
					$r5 = $con->sql_fetchassoc($q5);
					$field = 'p'.$cat_level;
					
					if($form['department_id'] != $r5[$field])  $error[] = 'The SKU is not in this department';
				}elseif(!$sessioninfo['departments'][$department_id]){ // otherwise, need to check user's department
					$error[] = 'You do not have department selected under user-profile';
				}
				
				//check block items
				$con->sql_query("select block_list,doc_block_list,active from sku_items where id = $sku_item_id limit 1");
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				$block_list = unserialize($tmp['block_list']);
				$sel_branch =$form['deliver_to'];
				$doc_block_list = unserialize($tmp['doc_block_list']);
				$active = $tmp['active'];
				
				if(BRANCH_CODE=='HQ')
				{
					if(!$active)
					{
						$error[] =$LANG['PO_ITEM_IS_INACTIVE'];
					}
					//When Only 1 branch is selected
					if(count($sel_branch)==1)
					{				
						if ($block_list) {
							$in_block_list = isset($block_list[$sel_branch[0]]);
							if ($in_block_list) $error[] =$LANG['PO_ITEM_IS_BLOCKED'];
						}

						elseif($doc_block_list){
						$in_grn_block_list = isset($doc_block_list['grn'][$sel_branch[0]]);
						if ($in_grn_block_list) $error[] =$LANG['PO_ITEM_IS_BLOCKED'];
						}
					}
					//when multiple branches is selected
					elseif(count($sel_branch) >1)
					{
						//assign an array
						$blocked_branch=array();
						$blocked_branch_code=array();
						$block_counter=0;
						
						//Check how many items is blocked
						foreach($sel_branch as $br_id)
						{
					
							if ($block_list || $doc_block_list) {
								
								if($br_id==1)$in_block_list = isset($block_list[$br_id]);
								else $in_block_list =false;

								$in_grn_block_list = isset($doc_block_list['grn'][$br_id]);
								//either block po/grn will add 1 to counter
								if ($in_block_list||$in_grn_block_list)
								{	
									$con->sql_query("select code from branch where id=".mi($br_id));
									$branch = $con->sql_fetchrow();
									$con->sql_freeresult();						
									$blocked_branch[$br_id]=true;				
									$blocked_branch_code[$block_counter]=$branch['code'];
									$block_counter++;																	
								} 		
							}			
			
						}
						//When all item are blocked (po/grn) , deny item entry
						if($block_counter == count($sel_branch))
						{
							$error[] =$LANG['PO_ITEM_IS_BLOCKED'];
						}
						elseif($block_counter <count($sel_branch))
						{	
							//This is to prevent when import item from csv the selected blocked item added before become edittable after refresh		
							$smarty->assign("blocked_branch",$blocked_branch );
									
							foreach($blocked_branch_code as $bb_code)
							{
								$error[] = $bb_code.' is GRN/PO blocked';
							}
							
						}
					}		
				}
				else
				{
					//Branches other than HQ
					if ($block_list) {
						$in_block_list = isset($block_list[$sessioninfo['branch_id']]);
						if ($in_block_list) $error[] =$LANG['PO_ITEM_IS_BLOCKED'];
					}
					elseif($doc_block_list){
						$in_grn_block_list = isset($doc_block_list['grn'][$sessioninfo['branch_id']]);
						if ($in_grn_block_list) $error[] =$LANG['PO_ITEM_IS_BLOCKED'];
					}
					elseif(!$active){ // is inactive item
						$error[] = $LANG['PO_ITEM_IS_INACTIVE'];
					}
				}


				//check duplicate
				$con->sql_query("select * from tmp_po_items where sku_item_id=$sku_item_id and po_id=$id and branch_id=$branch_id");
				$tmp_po_info = $con->sql_fetchassoc();
				$duplicate_row = $con->sql_numrows();
				$con->sql_freeresult();
				if($duplicate_row > 0){
					if(!$form['allow_duplicate'] && !$config['po_item_allow_duplicate'])    $error[] = "Item Code(".$ins['item_code'].") is duplicated";
				}
				
				//get order cost
				if($ins['order_cost']){
					$order_price = $ins['order_cost'];
				}else{
					$item_detail = get_items_detail($sku_item_id, $branch_id);
					$order_price = $item_detail['order_price'];
				}
				
				//check csv file item duplicate  
				if($sku_item_id && $count == 1){
					if(in_array($sku_item_id, array_keys($item_list))){
						if($form['allow_duplicate'] && $item_list[$sku_item_id]!= $order_price)  $error[] = "Cannot duplicate difference Order Cost with same order item";
						if(!$form['allow_duplicate'] && !$config['po_item_allow_duplicate'])  $error[] = "Item Code(".$ins['item_code'].") is duplicated";
					}else{
						$item_list[$sku_item_id] = $order_price;
					}
				}
				
				if($form['allow_duplicate']){
					$con->sql_query("select count(distinct(order_price)) as count from tmp_po_items where sku_item_id=$sku_item_id and po_id=$id and branch_id=$branch_id");
					$order_cost = $con->sql_fetchassoc();
					$con->sql_freeresult();
					if($order_cost['count'] > 1 || ($order_cost['count'] == 1 && $tmp_po_info['order_price'] != $order_price)){
						$error[] = "Cannot duplicate difference Order Cost with same order item";
					}
				}
				
				//check sku item doc_allow_decimal
				if($sku_item_id && $count == 1){
					if($multi_delivery_branch){
						for($k1=2; $k1 < count($line); $k1++){
							$col1=$k1-2;
							if($doc_allow_decimal)  $ins['pcs_'.$col1] = round($ins['pcs_'.$col1], $config['global_qty_decimal_points']);
							else   $ins['pcs_'.$col1] = mi($ins['pcs_'.$col1]);
						}
					}else{
						if($doc_allow_decimal)  $ins['pcs'] = round($ins['pcs'], $config['global_qty_decimal_points']);
						else   $ins['pcs'] = mi($ins['pcs']);
					}
				}
			}else   $error[] = "Empty Item Code";
			
			$total_pcs = 0;
			if($multi_delivery_branch){
				for($k2=0; $k2 < $max_column; $k2++){
					$total_pcs += mf($ins['pcs_'.$k2]);
				}
			}else{
				$total_pcs += mf($ins['pcs']);
			}
			if($total_pcs <= 0)   $error[] = "Invalid Pcs";
			
			
			// check total items if created PO
			if($config['po_set_max_items']){
				$con->sql_query("select count(*) from tmp_po_items where po_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id]");
				$curr_count = $con->sql_fetchfield(0);
				$con->sql_freeresult();
				
				if(!$error && !$form['allow_duplicate'])  $total_row++;
				if(($curr_count + $total_row) > $config['po_set_max_items']){
					$error[] = sprintf($LANG['PO_MAX_ITEM_CANT_ADD'], $config['po_set_max_items']);
				}
			}
			
			$error = array_unique($error);
			if($error)	$ins['error'] = join(', ', $error);
			
			$item_lists[] = $ins;
			
			if($ins['error'])	$result['error_row']++;
			else  $result['import_row']++;
		}
		
		if($item_lists){
			if($multi_delivery_branch){
				$header = $line;
			}else{
				$header = $headers[$form['method']];
				if($result['error_row'] > 0)	$header[] = 'Error';
			}
			
			$file_name = "po_item_".time().".csv";
			
			$fp = fopen("attachments/import_po_item/".$file_name, 'w');
			fputcsv($fp, array_values($header));
			foreach($item_lists as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			chmod("attachments/import_po_item/".$file_name, 0777);
			
			print "<script>parent.window.PO_UPLOAD_CSV.ajax_show_result('$file_name', '');</script>";
		}else{
			print "<script>parent.window.PO_UPLOAD_CSV.ajax_show_result('', 'No data found on the file.');</script>";
		}
	}else {
		print "<script>parent.window.PO_UPLOAD_CSV.ajax_show_result('', 'Column not match. Please re-check import file.');</script>";
	}
}

function ajax_get_uploaded_csv_result(){
	global $smarty, $headers, $sample;
	
	$form = $_REQUEST;
	$multi_delivery_branch = count($form['deliver_to']) > 0 ? true : false;
	
	if(!$form['file_name'] || !file_exists("attachments/import_po_item/".$form['file_name'])){
		die("File no found.");
		exit;
	}
	
	$f = fopen("attachments/import_po_item/".$form['file_name'], "rt");
	$line = fgetcsv($f);
	
	if(in_array('Error', $line))  $error_index = array_search("Error", $line);
	else  $error_index = count($line);
	
	$item_lists = $result = array();
	$num_row = 0;
	while($r = fgetcsv($f)){
		$result['ttl_row']++;
		foreach($r as $tmp_row => $val){
			$r[$tmp_row] = utf8_encode(trim($val));
		}
		
		$data_list = array();
		switch ($form['method']) {
			case '1':
				$data_list['item_code'] = $r[0];
				$data_list['order_cost'] = $r[1];
				if($multi_delivery_branch && (count($line)-2) > 0){
					for($k=0; $k < (count($line)-2); $k++){
						$col= 2+$k;
						$data_list['pcs_'.$k] = mf($r[$col]);
					}
				}else{
					$data_list['pcs'] = $r[2];
				}
				
				if(!$r[$error_index]) $result['import_row']++;
				else{
					$data_list['error'] = $r[$error_index];
					$result['error_row']++;
				}
				break;
		}
		$item_lists[] = $data_list;
	}
	
	$ret = array();
	if($item_lists){
		if($multi_delivery_branch)  $header = $line;
		else  $header = $headers[1];
		
		if($result['error_row'] > 0)	$header[] = 'Error';
		
		$smarty->assign("result", $result);
		$smarty->assign("file_name", $form['file_name']);
		$smarty->assign("item_header", array_values($header));
		$smarty->assign("item_lists", $item_lists);
		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('po.upload_csv.result.tpl');
	}else{
		die("Result not found.");
	}	
	
	print json_encode($ret);
}

function ajax_import_po_items(){
	global $con, $LANG, $config, $sessioninfo, $smarty;

	$form = $_REQUEST;
	$branch_id = mi($form['branch_id']) ? mi($form['branch_id']) : $sessioninfo['branch_id'];
	$po_id= mi($form['id']);
	$po_branch_id = mi($form['po_branch_id']);
	$count_deliver_to = count($form['deliver_to']);
	$multi_delivery_branch = $count_deliver_to > 0 ? true : false;
	$item_checked = $form['po_tmp_item'];
	$user_id= mi($sessioninfo['id']);
	if(!$form['file_name'] || !file_exists("attachments/import_po_item/".$form['file_name'])){
		die('File not found.');
	}
	
	$f = fopen("attachments/import_po_item/".$form['file_name'], "rt");
	$line = fgetcsv($f);
	if(in_array('Error', $line)) {
		$error_index = array_search("Error", $line);
	}else{
		$error_index = count($line);
	}
	
	$branches = array();
	if($multi_delivery_branch){
		$deliver_to_list = implode(",", array_values($form['deliver_to']));
		$con->sql_query("select * from branch where active=1 and id in(".$deliver_to_list.")");
		while($r1=$con->sql_fetchassoc()){
			$branches[$r1['code']] = $r1['id'];
		}
		$con->sql_freeresult();
	}
	
	$error_list = $row_item = array();
	$num_row = $i = 0;
	while($r = fgetcsv($f)){
		$i++;
		foreach($r as $tmp_row => $val){
			$r[$tmp_row] = utf8_encode(trim($val));
		}
		
		switch ($form['method']) {
			case '1':
				if(!$r[$error_index] && in_array($i, $item_checked)) {
					//get sku item info
					$con->sql_query("select * from sku_items where active=1 and (sku_item_code=".ms($r[0])." or mcode=".ms($r[0])." or link_code=".ms($r[0])." or artno=".ms($r[0]).") limit 1");
					$item_info = $con->sql_fetchassoc();
					$con->sql_freeresult();
					$sku_item_id = mi($item_info['id']);
					
					$qty_loose = 0;
					$qty_loose_allocation = array();
					if($multi_delivery_branch){
						for($k=0; $k < (count($line)-2); $k++){
							$col= 2+$k;
							$header_info = explode("-", $line[$col]);
							$branch_code = strtoupper(trim($header_info[0]));
							$bid = $branches[$branch_code];
							$qty_loose_allocation[$bid] = $r[$col] ? $r[$col] :'';
						}
					}else{
						$qty_loose = mf($r[2]);
					}
					
					//get exist tmp_po_items
					$con->sql_query("select * from tmp_po_items where sku_item_id =$sku_item_id and po_id =$po_id and branch_id=$branch_id order by id desc limit 1");
					$tmp_po_info = $con->sql_fetchassoc();
					$count = $con->sql_numrows();
					$con->sql_freeresult();
					
					$num =0;
					if($count > 0 && $form['allow_duplicate']){
						$update = $temp = array();
						$tmp_po_item_id = mi($tmp_po_info['id']);
						$temp['duplicate'] = true;
						$temp['existed_item_id'] = $tmp_po_item_id;
						
						
						if($multi_delivery_branch){
							$qty_loose_allocation_total = array();
							$qty_loose_allocation_exist = unserialize($tmp_po_info['qty_loose_allocation']);
							foreach($branches as $bcode=>$bid){
								$qty_loose_allocation_total[$bid] = $qty_loose_allocation_exist[$bid] + $qty_loose_allocation[$bid];
							}
							
							$update['qty_loose_allocation'] = serialize($qty_loose_allocation_total);
							$temp['multi_delivery_branch'] = true;
							$temp['qty_loose_allocation'] = $qty_loose_allocation;
						}else{
							$update['qty_loose'] = $qty_loose+$tmp_po_info['qty_loose'];
							$temp['qty_loose'] = $qty_loose;
						}
						
						//update tmp po item
						$con->sql_query("update tmp_po_items set ". mysql_update_by_field($update)." where id=$tmp_po_item_id and branch_id=$branch_id");
						$num = $con->sql_affectedrows();
					}else{
						$upd= array();
						
						$item_detail = get_items_detail($sku_item_id,$branch_id);
						$order_uom_id = 1;
						$artno_mcode = $item_detail['artno'] ? $item_detail['artno'] : $item_detail['mcode'];
						$upd['po_id'] = $po_id;
						$upd['branch_id'] = $branch_id;
						$upd['sku_item_id'] = $sku_item_id;
						$upd['user_id'] = $user_id;
						$upd['order_price'] = mf($r[1]) ? mf($r[1]) : $item_detail['order_price'];
						$upd['resell_price'] = $item_detail['resell_price'];
						$upd['order_uom_fraction'] = $item_detail['order_uom_fraction'] ? $item_detail['order_uom_fraction'] : 1;
						$upd['cost_indicate'] = $item_detail['cost_indicate'];
						$upd['selling_uom_id'] = $item_detail['selling_uom_id'];
						$upd['artno_mcode'] = $artno_mcode;
						$upd['order_uom_id'] = $order_uom_id;
						
						//check bom type
						if($config['sku_bom_additional_type']){
							// check is bom package or not
							$con->sql_query("select sku.is_bom, si.bom_type from sku_items si
							join sku on sku.id=si.sku_id
							where si.id=".mi($sku_item_id));
							$bom_info = $con->sql_fetchassoc();
							$con->sql_freeresult();
				
							if($bom_info['is_bom'] && $bom_info['bom_type']=='package'){
								$is_bom_package = true;
								$bom_ref_num++;
								
								$con->sql_query("select bi.sku_item_id as sid,bi.qty from bom_items bi where bi.bom_id=".mi($sku_item_id)." order by bi.sku_item_id");
								while($r = $con->sql_fetchassoc()){
									$bom_qty_ratio = $r['qty'];
								}
								$con->sql_freeresult();
							}
							$upd['bom_ref_num'] = $bom_ref_num;
							$upd['bom_qty_ratio'] = $bom_qty_ratio;
						}
						$upd['selling_price'] = $item_detail['selling_price'];
						$upd['is_foc'] = 0;
						
						//get sales trend
						$sales = get_sales_trend($sku_item_id);
						$upd['sales_trend'] = serialize($sales);
						
						//get stock balance
						$balance = get_stock_balance($sku_item_id);
						$upd['stock_balance'] = serialize($balance['stock_balance']);
						$upd['parent_stock_balance']=serialize($balance['parent_stock_balance']);
						
						if($multi_delivery_branch){
							$upd['qty_loose_allocation'] = serialize($qty_loose_allocation);
							$upd['selling_price_allocation'] =serialize($item_detail['selling_price_allocation']);
						}else{
							$upd['qty_loose'] = $qty_loose;
						}
						
						//insert tmp po items
						$con->sql_query("insert into tmp_po_items ".mysql_insert_by_field($upd));
						$tmp_po_item_id = $con->sql_nextid();
						$item_detail['id'] = $tmp_po_item_id;
						$item_detail['order_price'] = $upd['order_price'];
						$num = $con->sql_affectedrows();
						
						//pass data to tpl file
						$addition_info =array();
						$addition_info['bom_ref_num'] = $bom_ref_num;
						$addition_info['bom_qty_ratio'] = $bom_qty_ratio;
						$addition_info['artno_mcode'] = $artno_mcode;
						$addition_info['po_id'] = $po_id;
						$addition_info['branch_id'] = $branch_id;
						$addition_info['sku_item_id'] = $sku_item_id;
						$addition_info['branch_id'] = $branch_id;
						$addition_info['user_id'] = $user_id;
						$addition_info['sku'] = $item_detail['description'];
						$addition_info['order_uom_id'] = $order_uom_id;
						$addition_info['is_foc'] = 0;
						$addition_info['qty_loose'] = $qty_loose;
						$addition_info['qty_loose_allocation'] = $qty_loose_allocation;
						
						$item = array_merge($item_detail, $sales, $balance, $addition_info);
						$smarty->assign("item", $item);
						$smarty->assign('form', $form);
						
						$rid=$con->sql_query("select count(*) from tmp_po_items where po_id=$po_id and branch_id =$branch_id and user_id=$sessioninfo[id]");	    	
						$item_count=$con->sql_fetchrow($rid);
						$con->sql_freeresult($rid);
						
						$smarty->assign("item_n", $item_count[0]);
						$smarty->assign("allow_edit", 1);

						$temp = array();
						$temp['rowdata'] = $smarty->fetch("po.new.po_row.tpl");
						
						// check if got last po row
						$last_po_html = po_ajax_add_last_po_row($tmp_po_item_id, $sku_item_id);
						if($last_po_html)	$temp['rowdata'] .= $last_po_html;
					}
					
					if ($num > 0){
						$num_row++;
						$row_item[] = $temp;
					}
				}else{
					if($r[$error_index]) $error_list[] = $r;
				}
				break;
		}
	}
	
	if($error_list) {
		$fp = fopen("attachments/import_po_item/invalid_".$form['file_name'], 'w');
		$line[] = 'Error';
		fputcsv($fp, array_values($line));
		
		foreach($error_list as $r){
			fputcsv($fp, $r);
		}
		fclose($fp);
		
		chmod("attachments/import_po_item/invalid_".$form['file_name'], 0777);
	}
	
	$ret = array();
	$ret['ok'] = 1;
	if ($num_row > 0) {
		$ret['html'] = $row_item;
		$ret['file'] = '';
		if($error_list){
			$ret['file'] = "invalid_".$form['file_name'];
		}
		$ret['msg'] = "$num_row item(s) added.";
	}else{
		$ret['msg'] = "Failed to add PO items.";
	}

	print json_encode($ret);
}

//export po item
function export_po_item(){
	global $con, $smarty, $sessioninfo, $appCore, $config;
	
	$got_item = false;
	$form = $_REQUEST;
	$deliver_to  = $form['print_branch_id'];
	$branch_id= mi($form['branch_id']);
	$id= mi($form['id']);
	$merge = mi($form['merge_all_branch']); 
	//header
	$link_code_name = $config['link_code_name'] ? $config['link_code_name'] : 'Link Code';
	$header_array = array('Delivery Branch', 'ARMS Code', 'Mcode', 'Art-no', $link_code_name, 'UOM', 'Order Ctn', 'Order Pcs', 'Order cost');
	
	//select report prefix from branch
	$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
	$prefix=$con->sql_fetchrow();
	$con->sql_freeresult();
	$report_prefix = $prefix['report_prefix'];
	
	$formatted=sprintf("%05d",$id);
	
	$con->sql_query("select po_no from po where id=".mi($id)." and branch_id=".mi($branch_id));
	$r1=$con->sql_fetchrow();
	$con->sql_freeresult();
	
	if($r1['po_no'] == '')  $document_no = $report_prefix.$formatted;
	else  $document_no = $r1['po_no'];
	
	$arr = array();
	if($branch_id && $id){
		$qry = "select si.sku_item_code, si.mcode, si.artno, si.link_code, uom.code as code, pi.qty, pi.qty_loose, pi.order_price, pi.qty_allocation, po.po_option, pi.qty_loose_allocation, po.deliver_to, pi.id, po.po_branch_id, pi.foc_allocation
		from po_items pi 
		left join po on po.id=pi.po_id and po.branch_id=pi.branch_id
		left join sku_items si on si.id = pi.sku_item_id
		left join uom on uom.id = pi.order_uom_id
		where pi.po_id=$id and pi.branch_id=$branch_id";
		$q1 = $con->sql_query($qry);
		if ($con->sql_numrows($q1)>0) {
			$got_item = true;
			// $r1['ctn'] += $r1['qty_allocation'][$k] + $r1['foc_allocation'][$k];
			while($r = $con->sql_fetchassoc($q1)){
				$po_id = $r['id'];
				$item = array();
				$con->sql_query("select code from branch where id=".mi($r['po_branch_id']));
				$branch = $con->sql_fetchrow();
				$item['delivery'] = $branch['code'];
				$item['sku_item_code'] = $r['sku_item_code'];
				$item['mcode'] = $r['mcode'];
				$item['artno'] = $r['artno'];
				$item['link_code'] = $r['link_code'];
				$item['code'] = $r['code'];
				$item['qty'] = $r['qty'];
				$item['qty_loose'] = $r['qty_loose'];
				$item['order_price'] = $r['order_price'];

				if(count(unserialize($r['deliver_to'])) > 1){
					foreach (unserialize($r['deliver_to']) as $bid) {
						$con->sql_query("select code from branch where id=".mi($bid));
						$branch = $con->sql_fetchrow();
						$item['delivery'] = $branch['code'];
						$qty_loose_allocation = unserialize($r['qty_loose_allocation']);
						$qty_allocation = unserialize($r['qty_allocation']);
						$foc_allocation = unserialize($r['foc_allocation']);
						$item['qty_loose'] = $qty_loose_allocation[$bid];
						$item['qty'] = $qty_allocation[$bid] + $foc_allocation[$bid];
						$arr[$po_id][$bid] = $item;
					}
				}else{
					$bid = $branch_id;
					$arr[$po_id][$bid] = $item;
				}
			}
			$con->sql_freeresult($q1);
		}
		
	}

	if (!is_dir("tmp"))	check_and_create_dir("tmp");
	if (!is_dir("tmp/po_export"))	check_and_create_dir("tmp/po_export");

	if ($got_item) {
		if($merge || empty($deliver_to) || count($deliver_to) == 1){

			$filename = 'po_export_'.$document_no.'.csv';
			$dir = "tmp/po_export/".$filename;
			$fp = fopen($dir, 'w');

			fputcsv($fp, $header_array);
			foreach ($arr as $key => $value) {
				if(count($deliver_to)==1){
					$deliver_id = $deliver_to[0];
					fputcsv($fp, $value[$deliver_id]);
				}else{
					foreach ($value as $val) {
						fputcsv($fp, $val);
					}
				}
			}

			log_br($sessioninfo['id'], 'PURCHASE ORDER', $id, "Export PO Items to CSV File");
			header('Content-Type: application/msexcel');
			header('Content-Disposition: attachment;filename='.$filename);
			print file_get_contents($dir);

			fclose($fp);
			chmod($dir, 0777);
			unlink($dir);
		}else{

			$files = array();
			foreach($deliver_to as $deliver)
			{
				$con->sql_query("select code from branch where id=".mi($deliver));
				$branch = $con->sql_fetchrow();
				$deliver_name = $branch['code'];
				$filename = "po_".$document_no."_".$deliver_name."_".time().".csv";
				$dir = "tmp/po_export/".$filename;
				$fp = fopen($dir, 'w');

				fputcsv($fp, $header_array);

				foreach ($arr as $value) {
					fputcsv($fp, $value[$deliver]);
				}

				log_br($sessioninfo['id'], 'PURCHASE ORDER', $id, "Export PO Items to CSV File");

				fclose($fp);
				chmod($dir, 0777);

				$files[] = $dir;
			}

			$zipname = "po_export_".$document_no."_".time().".zip";
			$zip_dir = "tmp/po_export/".$zipname;
			$zip = new ZipArchive;
			$zip->open($zip_dir, ZipArchive::CREATE);
			foreach ($files as $file) {
			  	$zip->addFile($file, basename($file));
			}
		    $zip->close();
			
			ob_clean();
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header('Content-Type: application/x-download');
			header('Content-disposition: attachment; filename='.$zipname);
			readfile($zip_dir);
		}
	}
	
	if (!$got_item){
		js_redirect("No po items data.", $_SERVER['PHP_SELF']);
	}
	exit;
}
?>
