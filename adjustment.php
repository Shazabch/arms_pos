<?php
/*
REVISION HISTORY
================
1/30/2008 2:17:29 PM gary
- fix the searching adjustment bug.


ADJUSTMENT STATUS :
0= Saved Adj
1= Confirm Adj
2= Reject Adj
3= KIV Adj
4= Cancel/terminate Adj 
5= Delete Adj

9/15/2008 12:56:33 PM yinsee
- add costing

3/13/2009 1:00:00 PM Andy
- add $config[adjustment_branch_selection] and config[single_server_mode] checking

3/19/2009 3:40:00 PM Andy
- Add stock balance and total selling price
	- Table modified
		- alter table adjustment_items add stock_balance double
		- alter table tmp_adjustment_items add stock_balance double

4/11/09 yinsee
- fix bug when save become duplicate (cutemaree)

29/9/2009 10:00:00 PM jeff
- change sku autocomplete to multiple add

11/4/2009 12:42:03 PM edward
- add report_prefix for log_br

5/19/2010 9:41:17 AM Andy
- Branch dropdown change to only show active branch if is consignment mode

5/31/2010 4:12:03 PM Alex
- add config['upper_date_limit'] and config['lower_date_limit']

7/2/2010 4:11:39 PM Alex
- Add $config['document_page_size'] to set limit items per page and fix search bugs

7/6/2010 10:11:56 AM Justin, Alex
- Replaced the invalid error message define while found items that without qty. (Justin)
- Solved the search bugs. (Alex)

10/12/2010 10:09:10 AM Andy
- Disable the change branch feature once adjustment was saved.

11/8/2010 12:39:46 PM Andy
- Add checking for canceled/deleted adjustment and prevent it to be edit.

11/8/2010 1:23:42 PM Alex
- add branch searching for consignment modules only

4/12/2011 2:52:32 PM Andy
- Make adjustment can search by adjustment no. (with report prefix)

6/24/2011 2:50:03 PM Andy
- Make all branch default sort by sequence, code.

7/5/2011 1:27:31 PM Andy
- Change split() to use explode()

7/6/2011 10:45:02 AM Andy
- Fix when save, item missing if user open multiple adjustment.
- Fix auto clear all temp items when user enter adjustment list page.

7/27/2011 4:19:23 PM Justin
- Added to pick up sku item's doc decimal point.

8/16/2011 11:17:21 AM Justin
- Fixed the bugs while listing between Adjustment that being approved and canceled.

10/14/2011 10:36:32 AM Andy
- Fix adjustment duplicate items if user open multiple tab to create new adjustment.

10/27/2011 3:53:32 PM Justin
- Fixed the bugs when user key in positive and negative both 20 qty, system still allow to save adjustment item.
- Fixed the system load adjustment items wrongly while login within the same user and add adjustment items without save, system will pass those unsaved items into the next adjustment which open by the same user.
- Added to capture timer id while open adjustment.

11/9/2011 11:32:44 AM Andy
- Add need user to click continue before they can insert adjustment item.
- Add to block user to change adjustment branch after they click "continue".

11/9/2011 12:06:43 PM Justin
- Modified the qty round up to base on config set.

4/20/2012 5:55:12 PM Alex
- add packing_uom_code => get_sku_items_details()

8/14/2012 11:35 AM Justin
- Enhanced the add item process to include GRN barcoder scanning.
- Enhanced to use json_encode to present the item row instead of XML.

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

12/20/2012 2:19 PM Andy
- Fix adjustment item cannot get the lastest cost.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/24/2013 11:16 AM Andy
- Enhance to check adjustment total cost for approval when confirm.
- Enhance to check approval settings when confirm/approve.
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

1/28/2014 11:39 AM Justin
- Enhanced to have serial no feature.

3/31/2014 4:38 PM Justin
- Enhanced to check inactive SKU when scan barcode.

8/28/2014 1:27 PM Justin
- Bug fixed on store wrong info of logs when do reset.

2/10/2015 5:49 PM Andy
- Enhance to can add attachment. (jpg/pdf/zip)

7/27/2015 5:09 PM Andy
- Fix attachment store based on the adjustment branch_id.

8/3/2015 11:01 AM Andy
- Add assign $can_edit to templates when open to edit document.

11/13/2015 9:00 AM Qiu Ying
- Add attachment file size limit 1 mb.

03/08/2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

07/01/2016 15:30 Edwin
- Enhanced on user able to view although they don't have official module.

2/3/2017 5:38 PM Andy
- Fixed to only load own adjustment when login at branch.

2/10/2017 4:57 PM Andy
- Fixed if got config adjustment_branch_selection, no need to check branch and dept.

4/25/2017 5:31 PM Justin
- Enhanced branch selection to filter by user's adjustment access permission.

6/2/2017 4:01 PM Justin
- Bug fixed on branch couldn't see from view mode if user doesn't have privilege for that branch.

6/7/2017 2:51 PM Justin
- Bug fixed on branch group will filter out all branches for consignment modules customers.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

1/12/2018 3:11 PM Andy
- Enhanced to check work order when load adjustment.

6/19/2019 1:20 PM William
- Pick up "vertical_logo" and "vertical_logo_no_company_name" from Branch for logo and hide company name setting.
- Pick up "setting_value" from system_settings for logo setting.

9/3/2019 9:10 AM William
- Enhanced "Attachment" can upload unlimited image.

1/31/2020 9:19 AM Andy
- Fixed function "sn_validate" $err string used as array bug.

1/8/2020 3:30 PM William
- Enhanced to insert id manually for adjustment tables that uses auto increment.

4/15/2020 1:14 PM William
- Enhanced to block confirm and reset when got config "monthly_closing" and document date has closed.
- Enhanced to block create and save when got config "monthly_closing_block_document_action" and document date has closed.

10/28/2020 8:42 AM William
- Enhanced to let adjustment item can add item by csv.

11/6/2020 9:10 AM William
- Bug fixed upload csv duplicate sku checking not check the difference item code.

11/13/2020 3:01 PM William
- Change adjustment item export file name to adjustment_export_(Adjustment no).
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ADJ')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ADJ', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules'] && $_REQUEST['a'] != 'view') js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
include("adjustment.include.php");

$smarty->assign("PAGE_TITLE", "Adjustment");

init_selection();

$con->sql_query("select * from branch where active=1 order by sequence, code");

while($r = $con->sql_fetchrow()){
	$branches[$r['id']] = $r;
}

$smarty->assign("branches", $branches);

$branch_id = mi($_REQUEST['branch_id']);
if ($branch_id ==''){
	$branch_id = $sessioninfo['branch_id'];
}

$headers = array(
	'1' => array("item_code" => "Item Code", "qty" => "Qty")
);

$sample = array(
	'1' => array(
		array("285020940000", "5"),
		array("284357220000", "3")
	)
);

if (isset($_REQUEST['a'])){

	switch($_REQUEST['a']){
		
		case 'print':
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
			
			load_adj(false, true, false);
			//$smarty->display("adjustment.print.tpl");
			exit;

		case 'delete':
		    $id = mi($_REQUEST['id']);

	        if($config['adjustment_branch_selection']&&$config['single_server_mode']){  // check branch id
			    $default_bid = intval($_REQUEST['default_branch_id']);
			    if($default_bid==0) $default_bid = $branch_id;
		        $where_branch = "branch_id=".mi($default_bid);
		        $check_bid = $default_bid;
			}else{
		        $where_branch = "branch_id=".mi($branch_id);
		        $check_bid = $branch_id;
			}
			check_must_can_edit($check_bid, $id);

			
			//$branch_id = mi($_REQUEST['branch_id']);
			//$branch_id = mi($_REQUEST['open_branch_id']);
			$status=5;
			$reason=ms($_REQUEST['reason']);
			$cancelled = 'CURRENT_TIMESTAMP';
						
	    	$con->sql_query("update adjustment set cancelled_by=$sessioninfo[id], cancelled=$cancelled, reason=$reason, status=$status where id =$id and $where_branch");
		    header("Location: /adjustment.php?t=$_REQUEST[a]&id=$id");
		    exit;

		case 'do_confirm':
		    if($config['adjustment_branch_selection']&&$config['single_server_mode']){
                load_branch();
		    	$branch_group = load_branch_group();
			}
			do_save(1);
		case 'save':
			do_save();
		case 'ajax_delete_row':
	        $id = intval($_REQUEST['id']);
			if(isset($_REQUEST['bid']))    $branch_id = mi($_REQUEST['bid']);
	        //$con->sql_query("delete from tmp_adjustment_items where id=$id and branch_id=$branch_id");
	        $con->sql_query("delete from tmp_adjustment_items where id=$id");
			exit;
			
		case 'ajax_add_item_row':
			ajax_add_item_row();
			exit;
		case 'ajax_add_item':
			$form=$_REQUEST;			
			save_adjust_items($form['id']);//save all items details.			    	
			$r = get_sku_items_details($branch_id,$form['sku_item_id']);
			$ret=add_temp_item($r);
			if ($ret==-1){
				fail($LANG['SKU_ITEM_ALREADY_IN_ADJUSTMENT']);			
			}						  			
		    $smarty->assign("item", $r);
		    
			$arr = array();
			$rowdata = $smarty->fetch("adjustment.new.row.tpl");		

	    	$arr[] = array("id" => $r['id'], "rowdata" => $rowdata);
			$smarty->assign("form", $form);
				    	
			header('Content-Type: text/xml');
	        print array_to_xml($arr);
			exit;
	
		case 'ajax_load_adjust_list':
			load_adjustment_list();
		    $smarty->display("adjustment.list.tpl");
			exit;
		case 'refresh':
			$_REQUEST['a']='open';	// cause template only check 'open', else will disable all input
		case 'view':
		case 'open':

			//select report prefix from branch
			$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
			$r=$con->sql_fetchrow();
		    $formatted=sprintf("%05d",$_REQUEST['id']);
		    
		    if($config['adjustment_branch_selection']&&$config['single_server_mode']){
                load_branch();
		    	$branch_group = load_branch_group();
			}
		    
		    if ($_REQUEST['id']){
 				log_br($sessioninfo['id'], 'ADJUSTMENT', $_REQUEST['id'], "Load Adj.No:".' ('.$r['report_prefix'].$formatted.') ');
			}else{
				$con->sql_query("delete from tmp_adjustment_items where user_id = $sessioninfo[id] and adjustment_id=0");
			}
			
			if($_REQUEST['a']=='view'){
				load_adj(false, true, false);
			}
			else{
				$form = load_adj();
				if($form['module_type'] == 'work_order'){
					// work order not allow to edit
					display_redir($_SERVER['PHP_SELF']."?a=view&branch_id=$form[branch_id]&id=$form[id]", "Adjustment", $LANG['ADJUSTMENT_WORK_ORDER_NOT_ALLOW_EDIT']);
				}
				$smarty->assign('can_edit', 1);
			}

			$smarty->display("adjustment.new.tpl");
			exit;
		case 'get_sku_selling_price':
		    get_sku_selling_price();
		    exit;
		case 'do_reset':
			$form = $_REQUEST;

			if(!$form['skip_sn_error']){
				$params = array();
				$params['id'] = $form['id'];
				$params['branch_id'] = $form['branch_id'];
				$params['skip_sn_error'] = false;
				$params['use_tmp'] = false;
				$sn_error = manage_serial_no($params);
				if($sn_error){
					$errm['top'][] = sprintf($LANG['SN_CONFIRMATION']);
				}
				$smarty->assign("form_name", "f_do_reset");
				$smarty->assign("sn_error", $sn_error);
				$smarty->assign("reset_reason", $form['reason']);
			}

			if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($form['adjustment_date'],$branch_id,'adjustment')) {
				$errm['top'][] = $LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED'];
			}
			
			//
			/*if($config['monthly_closing']){
				$is_month_closed = $appCore->is_month_closed($form['adjustment_date']);
				if($is_month_closed){
					$errm['top'][] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];
				}
			}*/
			
			if($errm){
				load_adj();
				$smarty->assign("errm", $errm);
				$smarty->display("adjustment.new.tpl");
				exit;
			}
			
			$fail = do_reset($form['id'],$branch_id);
			if ($fail){
				if($form['a']=='view'){
					load_adj(false, true, false);
				}
				else{
					load_adj();
				}

				if ($form['id']){
					log_br($sessioninfo['id'], 'ADJUSTMENT', $form['id'], "Reset Adj.No:".' ('.$r['report_prefix'].$formatted.') ');
				}

				$smarty->display("adjustment.new.tpl");

			}
		    exit;
		case 'ajax_add_grn_barcode_item':
			ajax_add_grn_barcode_item();
			exit;
		case 'mark_adj_attachment':
			mark_adj_attachment();
			exit;
		case 'download_adj_attachment':
			download_adj_attachment();
			exit;
		case 'ajax_open_csv_popup':
			ajax_open_csv_popup();
			exit;
		case 'show_result':
			show_result();
			exit;
		case 'ajax_import_adjustment':
			ajax_import_adjustment();
			exit;
		case 'download_sample_adjustment':
			download_sample_adjustment();
			exit;
		case 'ajax_get_uploaded_csv_result':
			ajax_get_uploaded_csv_result();
			exit;
		case 'export_adjustment_item':
			export_adjustment_item();
			exit;
        default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

// delete tmp
//$con->sql_query("delete from tmp_adjustment_items where user_id = $sessioninfo[id]");

$smarty->display("adjustment.home.tpl");
exit;

function do_save($is_confirm=0){
	global $con, $sessioninfo, $config, $smarty, $branch_id, $LANG, $appCore;

	if ($_REQUEST['id']>0){ // check adjustment latest status
		if($config['adjustment_branch_selection']&&$config['single_server_mode']){  // check branch id
			$default_bid = intval($_REQUEST['default_branch_id']);
			if($default_bid==0) $default_bid = $branch_id;
			$where_branch = "branch_id=".mi($default_bid);
			$check_bid = $default_bid;
		}else{
			$where_branch = "branch_id=".mi($branch_id);
			$check_bid = $branch_id;
		}
		check_must_can_edit($check_bid, $_REQUEST['id']);
	}
	$form=$_REQUEST;
	//print_r($form);exit;
	save_adjust_items($form['id']);
	$errm=validate_data($form,$is_confirm);
	
	//check is_month_closed
	/*if ($config['monthly_closing']){
		$is_month_closed = $appCore->is_month_closed($form['adjustment_date']);
		if($is_month_closed && ($config['monthly_closing_block_document_action'] || $is_confirm)){
			$errm['top'][] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];
		}
	}*/
	
	// check SN when do confirm
	if($is_confirm && $form['sn']){
		$sn_errm = sn_validate();
		if($sn_errm){
			$errm['top'][] = sprintf($LANG['DO_SN_ERROR']);
			$errm['sn'] = $sn_errm['sn'];
		}elseif(!$form['skip_sn_error']){
			$params = array();
			if(!$form['id']) $params['timer_id'] = $form['timer_id'];
			else $params['id'] = $form['id'];
			$params['branch_id'] = $branch_id;
			$params['skip_sn_error'] = false;
			$params['use_tmp'] = true;
			$sn_error = manage_serial_no($params);
			if($sn_error){
				$errm['top'][] = sprintf($LANG['SN_CONFIRMATION']);
			}
			$smarty->assign("form_name", "f_a");
		}
	}

	if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($_REQUEST['adjustment_date'],$branch_id,'adjustment')) {
		$errm['top'][] = $LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED'];
	}
	
	if (!$errm && $is_confirm){
		$params = array();
		$params['branch_id'] = $form['branch_id'];
		$params['type'] = 'ADJUSTMENT';
		$params['user_id'] = $sessioninfo['id'];
		$params['reftable'] = 'ADJUSTMENT'; 
		$params['doc_amt'] = $form['sheet_total_cost'];

		$old_branch_id = mi($_REQUEST['open_branch_id']);
		if($config['adjustment_branch_selection']&&$config['single_server_mode']&&$old_branch_id!=$form['branch_id']){ // branch id changed
		$_REQUEST['old_approval_history_id'] = $form['approval_history_id'];
		}else{
		if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id']; // use back the same id if already have  approval_history_id
		}
		//print_r($params);exit;
		//die();
		$astat = check_and_create_approval2($params, $con);
		//$astat = check_and_create_branch_approval('ADJUSTMENT',$branch_id, 'ADJUSTMENT');
		if (!$astat){
			$errm['top'][] = $LANG['ADJUSTMENT_NO_APPROVAL_FLOW'];
		}
		else{
			$form['approval_history_id'] = $astat[0];
			if ($astat[1] == '|'){
				$last_approval = true;
				if($astat['direct_approve_due_to_less_then_min_doc_amt'])	$direct_approve_due_to_less_then_min_doc_amt = 1;	// direct approve because no qualify for min doc amt
			} 
		}
	}

	if($errm){
		if($config['adjustment_branch_selection']&&$config['single_server_mode']){
			load_branch();
			$branch_group = load_branch_group();
		}
		$_REQUEST['a'] = 'open';
		//print_r($form);
		load_adj(true, false, true);
		$smarty->assign("can_edit", 1);
		$smarty->assign("errm", $errm);
		$smarty->assign("sn_error", $sn_error);
		$smarty->assign("form", $form);
		$smarty->display("adjustment.new.tpl");
		exit;
	}
	else{
		if ($is_confirm) $form['status'] = 1;
		if ($last_approval) $form['approved'] = 1;	
		
		$adj_id = save_adjustment($form);
		$formatted=sprintf("%05d",$adj_id);


		if ($is_confirm){
			//select report prefix from branch
			$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
			$r=$con->sql_fetchrow();
			log_br($sessioninfo['id'], 'ADJUSTMENT', $adj_id, "Confirmed Adj.No:".' ('.$r['report_prefix'].$formatted.') ');
			$to = get_pm_recipient_list2($adj_id,$form['approval_history_id'],0, 'confirmation',$branch_id,'adjustment');
			
			$con->sql_query("update branch_approval_history set ref_id = $adj_id where id = $form[approval_history_id] and branch_id = $branch_id");
			
			if ($last_approval){
				$params = array();
				if($direct_approve_due_to_less_then_min_doc_amt)	$params['direct_approve_due_to_less_then_min_doc_amt'] = 1;
				auto_adj_approval($adj_id,$branch_id, $params);
				
				send_pm2($to, "Adjustment Confirmed (ID#$adj_id) $approval_status[$status]", "adjustment.php?a=view&id=$adj_id&branch_id=$branch_id");
			}
			else{
				send_pm2($to, "Adjustment Approval (ID#$adj_id)", "adjustment.php?a=view&id=$adj_id&branch_id=$branch_id", array('module_name'=>'adjustment'));
			}
		}
		else{
			//select report from branch
			$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
			$r=$con->sql_fetchrow();
			log_br($sessioninfo['id'], 'ADJUSTMENT', $adj_id, "Saved Adj.No:".' ('.$r['report_prefix'].$formatted.') ');
		}
	}
	header("Location: /adjustment.php?t=$_REQUEST[a]&id=$adj_id");
	exit;
}

function auto_adj_approval($adj_id,$branch_id, $params = array()){
	global $con, $smarty, $sessioninfo, $LANG;
	$q1=$con->sql_query("select adj.*, bah.approvals 
from adjustment adj 
left join branch_approval_history bah on bah.id = adj.approval_history_id and bah.branch_id = adj.branch_id
where adj.id=$adj_id and adj.branch_id=$branch_id");
	$r1 = $con->sql_fetchrow($q1);
	
	$status=1;
	$approved = 1;
	$comment="Approved";
	
	$aid = mi($r1['approval_history_id']);
	$approvals = $r1['approvals'];
	//$approvals = str_replace("|$sessioninfo[id]|","|",$approvals);

	$upd = array();
	$upd['approval_history_id'] = $aid;
	$upd['branch_id'] = $branch_id;
	$upd['user_id'] = $sessioninfo['id'];
	$upd['status'] = $status;
	$upd['log'] = $comment;
	
	if($params['direct_approve_due_to_less_then_min_doc_amt'])	$upd['more_info']['direct_approve_due_to_less_then_min_doc_amt'] = 1;
	if($upd['more_info'])	$upd['more_info'] = serialize($upd['more_info']);
	
	$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd));
	
	//$con->sql_query("update branch_approval_history set status=$status, approvals = ".ms($approvals)." where id = $aid and branch_id = $branch_id");
	
	$con->sql_query("update adjustment set status=$status, approved=$approved where id=$adj_id and branch_id=$branch_id");

	// serial no handler
	$params = array();
	$params['id'] = $adj_id;
	$params['branch_id'] = $branch_id;
	$params['skip_sn_error'] = true;
	$params['use_tmp'] = false;
	manage_serial_no($params);

	update_sku_item_cost($adj_id,$branch_id);						
}

function load_adjustment_list(){
	global $con, $sessioninfo, $smarty, $depts, $config;

	if (!$t) $t = intval($_REQUEST['t']);

    if($config['adjustment_branch_selection']&&$config['single_server_mode']&&BRANCH_CODE=='HQ'){

	}else{
		if (!$sessioninfo['departments']){
			$depts = "(0)";
		}
		else{
			$depts = "(" . join(",", array_keys($sessioninfo['departments'])) . ")";
		}
		if ($sessioninfo['level']>=9999){
			$owner_check = "";
		}
		elseif ($sessioninfo['level']>=800){
			$owner_check = "(adj.dept_id in $depts) and ";
		}
		elseif ($sessioninfo['level']>=400){
			$owner_check = "(adj.branch_id = $sessioninfo[branch_id] and adj.dept_id in $depts) and ";
		}
		else{
			$owner_check = " adj.user_id = $sessioninfo[id] and";
		}
        if(BRANCH_CODE != 'HQ'){
	    	$where = " adj.branch_id=$sessioninfo[branch_id] and ";
		}
	}
	
	
	

	switch ($t)
	{
	    case 0:
	        $str = trim($_REQUEST['search']);
			if(!$str)	die('Cannot search empty string');
			if(preg_match("/\d{5}$/", $str) && strlen($str) >=7 ){   // adj no
				$tmp_report_prefix = substr($str, 0, -5);
				$tmp_id = substr($str, -5);
				
				$con->sql_query("select * from branch where report_prefix=".ms($tmp_report_prefix));
				$tmp_bid = mi($con->sql_fetchfield(0));
				$con->sql_freeresult();
				
				if(!$tmp_bid){
					die("Cannot find branch report prefix with '$tmp_report_prefix'");
				}
				$where .= "adj.branch_id=$tmp_bid and adj.id=".mi($tmp_id);
			}else{  // other
                $where .= '(adj.id = '.mi($str) . ' or adj.id like ' . ms('%'.replace_special_char($str)).' or b1.code='.ms($str).')';
			}

//	        $_REQUEST['s']='';
	        break;

		case 1: // show saved Adj
        	$where .= "adj.status = 0 and adj.approved = 0 and adj.active = 1";
        	break;

		case 2: // show waiting for approval (and Keep In View)
		    $where .= "(adj.status = 1 or adj.status = 3) and adj.approved = 0 and adj.active = 1";
		    break;

		case 3: // show inactive
			$where .= "(adj.status between 3 and 5 or adj.active = 0)";
		    break;

		case 4: // show approved
		    $where .= "adj.approved = 1 and adj.active = 1";
		    break;

		case 5: // show rejected
		    $where .= "adj.status = 2 and adj.approved = 0 and adj.active = 1";
		    break;

		case 6: //search branch for consignment modules only
		    if (BRANCH_CODE == "HQ" && $config['consignment_modules']){
				$where .= "adj.branch_id=".$_REQUEST['search'];
			}
		    break;
	}

	$con->sql_query("select count(*) from adjustment adj left join branch b1 on b1.id=adj.branch_id where $where");
	$r = $con->sql_fetchrow();
	$total = $r[0];
	$start = intval($_REQUEST['s']);
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else{
		if (isset($config['document_page_size'])) $sz=$config['document_page_size'];
			else	$sz = 25;
	}

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
	
	
	
	$q2=$con->sql_query("select adj.*, b1.report_prefix as prefix, dept.description as department, user.u as u, b1.code as branch, bah.approvals,bah.approval_order_id as aorder_id
from adjustment adj
left join branch b1 on b1.id=adj.branch_id
left join branch_approval_history bah on bah.id = adj.approval_history_id and bah.branch_id = adj.branch_id
left join category dept on dept_id = dept.id
left join user on user_id = user.id 
where $owner_check $where order by last_update desc limit $start, $sz");
    
	while($r2=$con->sql_fetchrow($q2)){
		$list[]=$r2;
	}
	//echo"<pre>";print_r($list);echo"</pre>";
	$smarty->assign("list", $list);	
}

function save_adjustment($form){
	global $con, $smarty, $sessioninfo, $LANG, $branch_id, $config, $item_timer_id, $adj_attachment_folder_name, $appCore;

	$form['branch_id'] =$branch_id;
	$form['user_id'] = $sessioninfo['id'];
	$form['added'] = 'CURRENT_TIMESTAMP';
	$form['last_update'] = 'CURRENT_TIMESTAMP';
	$adj_id=mi($form['id']);
	
	//delete the old unselect image files
	$new_filepath = "new";
	$photo_list = load_attachment_image($branch_id,$adj_id);
	$adj_attachment_filename = $form['adj_attachment_filename'];
	if($photo_list){
		foreach($photo_list as $file){
			if(!in_array($file,$adj_attachment_filename)){
				unlink($adj_attachment_folder_name."/".$branch_id."/".$adj_id."/".$new_filepath."/".$file);
			}
		}
	}
	if ($form['id'] == 0){
		$form['id'] = $appCore->generateNewID("adjustment", "branch_id=".mi($branch_id));
	    $con->sql_query("insert into adjustment " . mysql_insert_by_field($form, array('id', 'branch_id', 'user_id', 'adjustment_date', 'adjustment_type', 'remark', 
		'added', 'dept_id','status','approved','approval_history_id')));
	    //$filter_items = "where adjustment_id=$adj_id and branch_id = $branch_id and user_id = $sessioninfo[id]";
	}
	else{
	    $upd = array('user_id','adjustment_date','adjustment_type', 'remark', 'last_update', 'dept_id','status','approved','approval_history_id');
	    
	    $old_branch_id = mi($_REQUEST['open_branch_id']);
	    if($config['adjustment_branch_selection']&&$config['single_server_mode']&&$old_branch_id!=$form['branch_id']){ // branch id changed
	        // clone approval history
	        if($_REQUEST['old_approval_history_id']!=$form['approval_history_id']){
	          $old_approval_history_id = mi($_REQUEST['old_approval_history_id']);
            $con->sql_query("select * from branch_approval_history where id=$old_approval_history_id and branch_id=$old_branch_id");
            $bah = $con->sql_fetchrow();
            if($bah){ // clone approval history items
              $con->sql_query("select * from branch_approval_history_items where approval_history_id=$old_approval_history_id and branch_id=$old_branch_id");
              $bah_items = $con->sql_fetchrowset();
            }
          }
          
	        // to prevent duplicate key, delete only insert
          $con->sql_query("delete from adjustment where id = $form[id] and branch_id = $old_branch_id");
          // insert into new adjustment
			$form['id'] = $appCore->generateNewID("adjustment", "branch_id=".mi($branch_id));
	        $con->sql_query("insert into adjustment " . mysql_insert_by_field($form, array('id', 'branch_id', 'user_id', 'adjustment_date', 'adjustment_type', 'remark', 'added', 'dept_id','status','approved','approval_history_id')));
	    	
	        // delete items
	        $con->sql_query("delete from adjustment_items where branch_id = $old_branch_id and adjustment_id=$adj_id");
	        
	        if($bah&&$old_approval_history_id){            
            if($bah_items){
              foreach($bah_items as $r){
                $r['approval_history_id'] = $form['approval_history_id'];
                $r['branch_id'] = $form['branch_id'];
                $con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($r, array('approval_history_id','user_id','log','timestamp','status','branch_id')));
              }
            }
            
            $con->sql_query("delete from branch_approval_history where id=$old_approval_history_id and branch_id=$old_branch_id");
            $con->sql_query("delete from branch_approval_history_items where approval_history_id=$old_approval_history_id and branch_id=$old_branch_id");
          }
		}else{
            $con->sql_query("update adjustment set " . mysql_update_by_field($form, $upd)." where id = $form[id] and branch_id = $branch_id");
            // delete items
	        $con->sql_query("delete from adjustment_items where branch_id = $branch_id and adjustment_id=$form[id]");
	        
		}
	}
	
	// select items
    //$q2 = $con->sql_query("select * from tmp_adjustment_items $filter_items order by id");
    $filter_tmp_items = array();
    $filter_tmp_items[] = "adjustment_id=$adj_id and user_id = $sessioninfo[id]";
    if($item_timer_id)	$filter_tmp_items[] = "timer_id=".mi($item_timer_id);
    $filter_tmp_items = "where ".join(' and ', $filter_tmp_items);
    $sql = "select * from tmp_adjustment_items $filter_tmp_items order by id";
    $q2 = $con->sql_query($sql);
		
    //update items
	while($r2=$con->sql_fetchrow($q2)){
	    if($config['adjustment_branch_selection']&&$config['single_server_mode']){
	        $r2['branch_id'] = $branch_id;
	    }
		
	    $r2['id'] = $appCore->generateNewID("adjustment_items", "branch_id=".mi($branch_id));
	    $r2['adjustment_id'] = $form['id'];
		
		$con->sql_query("insert into adjustment_items " . mysql_insert_by_field($r2, array('id', 'adjustment_id', 'branch_id', 'user_id','sku_item_id', 'cost', 'qty','selling_price','stock_balance', 'serial_no')));
	}
	$con->sql_query("delete from tmp_adjustment_items where user_id = $sessioninfo[id] and adjustment_id=$adj_id");
	
	// attachment
	if($form['tmp_adj_attachment_name'] && $form['adj_attachment_filename']){
		// make folder
		if (!is_dir($adj_attachment_folder_name))
		{
			mkdir($adj_attachment_folder_name);
			chmod($adj_attachment_folder_name,0777);
		}
		
		if (!is_dir($adj_attachment_folder_name."/".$branch_id))
		{
			mkdir($adj_attachment_folder_name."/".$branch_id);
			chmod($adj_attachment_folder_name."/".$branch_id,0777);
		}
		
		if (!is_dir($adj_attachment_folder_name."/".$branch_id."/".$form['id']))
		{
			mkdir($adj_attachment_folder_name."/".$branch_id."/".$form['id']);
			chmod($adj_attachment_folder_name."/".$branch_id."/".$form['id'],0777);
		}
		
		if (!is_dir($adj_attachment_folder_name."/".$branch_id."/".$form['id']."/".$new_filepath))
		{
			mkdir($adj_attachment_folder_name."/".$branch_id."/".$form['id']."/".$new_filepath);
			chmod($adj_attachment_folder_name."/".$branch_id."/".$form['id']."/".$new_filepath,0777);
		}
		
		if($form['tmp_adj_attachment_name']){
			foreach($form['tmp_adj_attachment_name'] as $key=>$val){
				if($val){
					rename($val, $adj_attachment_folder_name."/".$branch_id."/".$form['id']."/".$new_filepath."/".$adj_attachment_filename[$key]);
					chmod($adj_attachment_folder_name."/".$branch_id."/".$form['id']."/".$new_filepath."/".$adj_attachment_filename[$key], 0777);
				}
			}
		}
	}
	
    return $form['id'];
}


function validate_data(&$form){
	global $con, $smarty, $sessioninfo, $LANG, $config;
	$err = array();
	if(!$form['p_qty']){
		$err['top'][] = $LANG['ADJUSTMENT_NO_ITEM'];
	}
	
//	$form['adjustment_date']=dmy_to_sqldate($form['adjustment_date']);

	//check the date either valid or not
	$arr= explode("-",$form['adjustment_date']);
	$yy=$arr[0];
	$mm=$arr[1];
	$dd=$arr[2];
	if(!checkdate($mm,$dd,$yy)){
	   	$err['top'][] = $LANG['ADJUSTMENT_INVALID_DATE'];
		$form['adjustment_date']='';							
	}

	$check_date = strtotime($form['adjustment_date']);

	if (isset($config['upper_date_limit']) && $config['upper_date_limit'] >= 0){
		$upper_limit = $config['upper_date_limit'];
		$upper_date = strtotime("+$upper_limit day" , strtotime("now"));

		if ($check_date>$upper_date){
		   	$err['top'][] = $LANG['ADJUSTMENT_INVALID_DATE'];
//			$form['adjustment_date']='';
		}
	}



	if (isset($config['lower_date_limit']) && $config['lower_date_limit'] >= 0){
		$lower_limit = $config['lower_date_limit'];
		$lower_date = strtotime("-1 day",strtotime("-$lower_limit day" , strtotime("now")));


		if ($check_date<$lower_date){
		   	$err['top'][] = $LANG['ADJUSTMENT_DATE_OVER_LIMIT'];
//			$form['adjustment_date']='';
		}

	}

	
	if(!$form['adjustment_type']){
		$err['top'][] = $LANG['ADJUSTMENT_TYPE_EMPTY'];
	}	

	if ($form['p_qty']){
		foreach($form['p_qty'] as $k=>$v){
			$v1=$form['n_qty'][$k];
			$qty = $v - $v1;
			if(!$qty){
				$err['item'][] = $LANG['ADJUSTMENT_ITEMS_NO_QTY'];
				break;
			}
		}
	}
	if($form['tmp_adj_attachment_name']){
		foreach($form['tmp_adj_attachment_name'] as $key=>$val){
			if (filesize($val) > 1048576){
				$err['top'][] = $LANG['ADJUSTMENT_FILE_SIZE_LIMIT'];
				$smarty->assign("adj_attachment_err_name", $form['adj_attachment_filename'][$key]);
				$form['tmp_adj_attachment_name'][$key] = null;
				$form['adj_attachment_filename'][$key] = null;
			}
		}
	}
	return $err;	
}

function save_adjust_items($adj_id){
	global $con, $smarty, $sessioninfo, $branch_id, $config, $item_timer_id;
	if($config['adjustment_branch_selection']&&$config['single_server_mode']){
	    $default_bid = intval($_REQUEST['default_branch_id']);
	    if($default_bid==0) $default_bid = $branch_id;
        $where_branch = "branch_id=".mi($default_bid);
	}else{
        $where_branch = "branch_id=".mi($branch_id);
	}
	//print_r($_REQUEST);exit;
	
	if($_REQUEST['timer_id']) $item_timer_id = $_REQUEST['timer_id'];
	else $item_timer_id = time();	// mark current time
	if($_REQUEST['row_id']){
		foreach($_REQUEST['row_id'] as $k=>$v){
			$update = array();
			$p_val = $_REQUEST['p_qty'][$k];
		    $n_val = $_REQUEST['n_qty'][$k];
		    $update['qty']=$p_val-$n_val;
		    $update['cost']=$_REQUEST['unit_cost'][$k];
		    $update['selling_price'] = $_REQUEST['selling_price'][$k];
		    $update['stock_balance'] = $_REQUEST['stock_balance'][$k];
		    
		    //$con->sql_query("update tmp_adjustment_items set " . mysql_update_by_field($update) . " where id = $k");
			
			if($_REQUEST['sn'][$k]) $update['serial_no'] = serialize($_REQUEST['sn'][$k]);
		    
		    // when user replace need this column
		    $update['id'] = $k;
		    $update['adjustment_id'] = mi($adj_id);
		    $update['branch_id'] = $default_bid? $default_bid : $branch_id;
		    $update['user_id'] = $sessioninfo['id'];
		    $update['sku_item_id'] = mi($_REQUEST['item_sku_item_id'][$k]);
			
			$update['timer_id'] = $item_timer_id;
			$con->sql_query("replace into tmp_adjustment_items " . mysql_insert_by_field($update));
		}	
	}
}

function add_temp_item(&$r){
	global $con, $smarty, $sessioninfo, $branch_id, $appCore;

	$r['id'] = $appCore->generateNewID("tmp_adjustment_items", "branch_id=".mi($branch_id));
	$r['adjustment_id']=mi($_REQUEST['id']);
	$r['branch_id']=$branch_id;
	$r['user_id']=mi($sessioninfo['id']);
	$r['sku_item_id']=mi($r['sku_item_id']);
	$r['timer_id']=mi($_REQUEST['timer_id']);

	//$con->sql_query("select id from tmp_adjustment_items where adjustment_id = $r[adjustment_id] and branch_id = $r[branch_id] and sku_item_id= $r[sku_item_id] and user_id = $r[user_id]");
	$con->sql_query("select id from tmp_adjustment_items where adjustment_id = $r[adjustment_id] and sku_item_id= $r[sku_item_id] and user_id = $r[user_id] and timer_id = ".mi($r['timer_id']));
	if ($con->sql_numrows() > 0){
	    return -1;
	}
	
	// get selling price
	$sql = "select if(sp.price,sp.price,si.selling_price) as selling_price from sku_items si left join sku_items_price sp on si.id=sp.sku_item_id and sp.branch_id=$r[branch_id] where si.id=$r[sku_item_id]";
	$con->sql_query($sql);
	$r['selling_price'] = floatval($con->sql_fetchfield(0));
	
	// get stock balance - latest
	$sql = "select qty from sku_items_cost where branch_id=$r[branch_id] and sku_item_id=$r[sku_item_id]";
	$con->sql_query($sql);
	$r['stock_balance'] = floatval($con->sql_fetchfield(0));
	
    $con->sql_query("insert into tmp_adjustment_items " . mysql_insert_by_field($r, array('id', 'adjustment_id', 'branch_id', 'user_id', 'sku_item_id', 'cost', 'selling_price','stock_balance', 'timer_id')));
 	
 	return $r['id'];
}

function load_branch($id=0){
	global $con,$smarty, $config, $sessioninfo, $branch_id;
	
	if($id>0)   $filter[] = "id=".mi($id);
	//if($config['consignment_modules'])  $filter[] = "active=1";
	
	// have to filter with user's permission
	if(!$config['consignment_modules']){
		$q1 = $con->sql_query("select * 
							   from user_privilege 
							   where user_id = ".mi($sessioninfo['id'])." and privilege_code = 'ADJ' and allowed=1
							   group by branch_id");
		
		while($r = $con->sql_fetchassoc($q1)){
			$blist[] = $r['branch_id'];
		}
		
		if($branch_id && !in_array($branch_id, $blist)){
			$blist[] = $branch_id;
		}
		
		$con->sql_freeresult($q1);
		
		if($blist) $filter[] = "id in (".join(",", $blist).")";
		
	}
	if($filter) $filter = "where ".join(' and ', $filter);
	
	$q_b = $con->sql_query("select * from branch $filter order by sequence, code") or die(mysql_error());
	while($r = $con->sql_fetchassoc($q_b)){
		$branches[$r['id']] = $r;
	}
	$con->sql_freeresult($q_b);
	//print_r($branches);
	$smarty->assign('branches',$branches);
	return $branches;
}

function get_sku_selling_price(){
	global $con, $config;
	
	$_REQUEST['sku_item_id'] = $_REQUEST['sku_item_id'];
	
	$sku_item_id_list = $_REQUEST['sku_item_id'];
	$branch_id = mi($_REQUEST['branch_id']);
	
	if(!$sku_item_id_list||!$branch_id) return;
	// selling price
	$sql = "select si.id,if(sp.price,sp.price,si.selling_price) as selling_price from sku_items si left join sku_items_price sp on si.id=sp.sku_item_id and sp.branch_id=$branch_id where si.id in (".join(',',$sku_item_id_list).")";
 	$con->sql_query($sql);
 	while($r = $con->sql_fetchrow()){
		$ret[$r['id']]['selling_price'] = number_format($r['selling_price'], 2);
	}
	
	// stock balance
	$sql = "select sku_item_id,qty from sku_items_cost where branch_id=$branch_id and sku_item_id in (".join(',',$sku_item_id_list).")";
	$con->sql_query($sql);
	while($r = $con->sql_fetchrow()){
		$ret[$r['sku_item_id']]['stock_balance'] = round($r['qty'], $config['global_qty_decimal_points']);
	}
	
	print json_encode($ret);
}

function get_sku_items_details($branch_id,$sku_item_id){
	global $con, $sessioninfo;
	$q1=$con->sql_query("select si.sku_item_code, si.description as description, si.artno, si.mcode, si.id as sku_item_id, si.doc_allow_decimal,
						 if (sic.grn_cost is null or sic.grn_cost = '', si.cost_price, sic.grn_cost) as cost, uom.code as packing_uom_code, sku.have_sn
						 from sku_items si
						 left join sku on sku.id = si.sku_id
						 left join sku_items_cost sic on (sic.sku_item_id = si.id and sic.branch_id=".mi($branch_id).")
						 left join uom on uom.id=si.packing_uom_id
						 where si.id=".mi($sku_item_id));			
	$r = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	return $r; 
}

function ajax_add_grn_barcode_item(){
	ajax_add_item_row(true);
}

function ajax_add_item_row($is_barcode=false){
	global $con, $smarty, $sessioninfo, $config, $LANG, $branch_id;

	$form=$_REQUEST;
	save_adjust_items($form['id']);//save all items details.
	$row_item = array();

	if($is_barcode){    // add item by using scan barcode
		$grn_barcode = trim($_REQUEST['grn_barcode']);
		$sku_info_arr = array();
		$sku_info=get_grn_barcode_info($grn_barcode,true);
	
		if ($sku_info['sku_item_id']){
			// is inactive item
			$q1 = $con->sql_query("select active from sku_items where id = ".mi($sku_info['sku_item_id'])." limit 1");
			$tmp_si_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			if(!$tmp_si_info['active']){
				fail($LANG['PO_ITEM_IS_INACTIVE']);
			}

			$sku_item_id = $sku_info['sku_item_id'];
			$pcs = mf($sku_info['qty_pcs']);
			$selling_price = mf($sku_info['selling_price']);
			if(isset($sku_info['new_cost_price'])) $cost_price = $sku_info['new_cost_price'];
		}

		$sku_code_list[] = $sku_item_id;
		$sku_info_arr[$sku_item_id]['pcs'] = $pcs;
		$sku_info_arr[$sku_item_id]['selling_price'] = $selling_price;
		if(isset($cost_price)) $sku_info_arr[$sku_item_id]['cost_price'] = $cost_price;
	}else $sku_code_list = $_REQUEST['sku_code_list'];
	
	$duplicate = 0;
	foreach($sku_code_list as $row=>$sku_item_id)
	{	    	
		$q1=$con->sql_query("select tai.*, si.sku_item_code
							 from tmp_adjustment_items tai
							 left join sku_items si on si.id = tai.sku_item_id
							 where tai.adjustment_id = ".mi($_REQUEST['id'])." and tai.user_id = ".mi($sessioninfo['id'])." and tai.sku_item_id = ".mi($sku_item_id)." and tai.timer_id = ".mi($form['timer_id']));
		$r = $con->sql_fetchrow($q1);

		if ($con->sql_numrows($q1) > 0){
			$temp = array();
			$temp['duplicate'] = true;
			$temp['existed_item_id'] = $r['id'];
			$temp['existed_si_code'] = $r['sku_item_code'];
			$temp['qty'] = $sku_info_arr[$sku_item_id]['pcs'];
			$temp['is_config_adj_type'] = $form['is_config_adj_type'];
			$row_item[] = $temp;
			unset($sku_code_list[$row]);
		}
		$con->sql_freeresult($q1);
	}

	$smarty->assign("form", $form);

	$con->sql_query("select count(*) as count from tmp_adjustment_items where adjustment_id = ".mi($_REQUEST['id'])." and user_id = ".mi($sessioninfo['id'])." and timer_id = ".mi($form['timer_id']));
	$r = $con->sql_fetchrow();
	$count = mi($r['count'])+1;

	foreach($sku_code_list as $sku_item_id)
	{
		$temp = array();
		$r = get_sku_items_details($branch_id, $sku_item_id);
		
		if($sku_info_arr[$sku_item_id]['pcs']){
			$r['qty'] = $sku_info_arr[$sku_item_id]['pcs'];
			if($form['is_config_adj_type'] == "-") $r['qty'] *= -1;
		}
		
		$ret=add_temp_item($r);
		$smarty->assign("is_config_adj_type", $form['is_config_adj_type']);
		$smarty->assign("item", $r);
		$smarty->assign("count",$count);
		$count++;

		$tpl = $smarty->fetch("adjustment.new.row.tpl");
		$temp['rowdata'] = "<tr id=titem".$ret.">".$tpl."</tr>";

			// if found have serial no
		if($r['have_sn'] != 0) $temp['sn'] = $smarty->fetch("adjustment.sn.new.tpl");
		
		$row_item[] = $temp;
	}

	print json_encode($row_item);
}

function sn_validate(){
	global $con, $sessioninfo, $config, $LANG;
	$form=$_REQUEST;
	$err = array();

	if(!$form['sn']) return;

	foreach($form['sn'] as $id=>$sn){
		$sn_list = array();
		$bal_qty = $form['p_qty'][$id] - $form['n_qty'][$id];
		
		$sku_item_id = $form['sn_sku_item_id'][$id];

		$sn_list = explode("\n", $sn);
		$duplicate_list = array();
		$db_sn_existed_list = array();
		$db_sn_ms_list = array();
		$db_sold_list = array();
		$db_inactive_list = array();
		$all_duplicate_list = array();
		$db_sn_list = array();
		$tmp_sn_list = $sn_list;
		$curr_sn_list = array();
		$ttl_sn=0;

		// check total S/N keyed in whether matched with rcv qty
		for($i=0; $i<count($sn_list); $i++){
			//$sn = preg_replace("/[^A-Za-z0-9]/","",trim($sn_list[$i]));
			$sn = trim($sn_list[$i]);
			if(!$sn) continue;
			$is_duplicated = "";
			for($j=0; $j<count($tmp_sn_list); $j++){
				//$tmp_sn = preg_replace("/[^A-Za-z0-9]/","",trim($tmp_sn_list[$j]));
				$tmp_sn = trim($tmp_sn_list[$j]);
				if($i == $j || !$tmp_sn) continue;
				if($sn == $tmp_sn) $is_duplicated = 1;
			}
			if($is_duplicated) $duplicate_list[$sn] = $sn; // found it is duplicated in the list
			$db_sn_list[$sn] = $sn; // to be use for the filter to S/N from database
			$curr_sn_list[] = $sn;
			$all_sn[$id][] = $sn;
			$ttl_sn++;
		}

		$r['ttl_sn'] = $ttl_sn;

		if($db_sn_list) $sn_list = join("', '", $db_sn_list);

		if($bal_qty < 0){
			// check S/N against database
			$sql = $con->sql_query("select * from pos_items_sn where sku_item_id = ".mi($sku_item_id)." and serial_no in ('".$sn_list."') and located_branch_id = ".mi($form['branch_id']));

			if($con->sql_numrows($sql)>0){
				while($r=$con->sql_fetchrow($sql)){
					//if($r['status'] == 1) $db_sold_list[$r['serial_no']] = $r['serial_no'];
					//elseif($r['active'] == 0) $db_inactive_list[$r['serial_no']] = $r['serial_no'];
					$db_sn_existed_list[$r['serial_no']] = $r['serial_no'];
				}
				
				// if found all S/N for this sku item not all existed
				if(count($db_sn_existed_list) != count($db_sn_list)){
					for($i=0; $i<count($curr_sn_list); $i++){
						if(!in_array($curr_sn_list[$i], $db_sn_existed_list)){
							$db_sn_ms_list[$curr_sn_list[$i]] = $curr_sn_list[$i];
						}
					}
				}
			}else $db_sn_ms_list = $db_sn_list; // straight treat all S/N for this sku item as not existed
		}
		
		if(count($duplicate_list)>0){
			$err['sn'][$id][] = sprintf($LANG['DO_SN_DUPLICATE'], $branch_code, "<br />".join(", ", $duplicate_list));
		}

		if(count($db_sn_ms_list)>0){
			$err['sn'][$id][] = sprintf($LANG['DO_SN_INVALID'], $branch_code, "<br />".join(", ", $db_sn_ms_list));
		}

		/*if(count($db_sold_list)>0){
			$err['sn'][$id][] = sprintf($LANG['DO_SN_SOLD'], $branch_code, "<br />".join(", ", $db_sold_list));
		}

		if(count($db_inactive_list)>0){
			$err['sn'][$id][] = sprintf($LANG['DO_SN_INACTIVE'], $branch_code, "<br />".join(", ", $db_inactive_list));
		}*/

		// check between rcv qty and S/N qty matching or not
		if($form['sn_rcv_qty'][$id] != 0 && $ttl_sn == 0) $err['sn'][$id][] = sprintf($LANG['DO_SN_EMPTY'], $branch_code);
		else{
			if($form['sn_rcv_qty'][$id] != $ttl_sn) $err['sn'][$id][] = sprintf($LANG['DO_SN_INVALID_QTY'], $branch_code);
		}

		// this will proceed if found config set for allowing duplicated sku item
		if($config['do_item_allow_duplicate']){
			if(count($all_sn_by_si[$sku_item_id]) > 0){
				for($i=0; $i<count($curr_sn_list); $i++){
					if($all_sn_by_si[$sku_item_id]){
						$sn_by_si = explode(",", $all_sn_by_si[$sku_item_id]['sn_list']);
						if(in_array($curr_sn_list[$i], $sn_by_si)){
							$all_duplicate_list[$curr_sn_list[$i]] = $curr_sn_list[$i];
						}
					}
				}
			}

			if(count($all_duplicate_list)>0){
				$err['sn'][$id][] = sprintf($LANG['DO_SN_SKU_DUPLICATE'], $branch_code, join(", ", $all_duplicate_list));
			}

			if($all_sn_by_si[$sku_item_id]['sn_list']) $all_sn_by_si[$sku_item_id]['sn_list'] .= ",";
			$all_sn_by_si[$sku_item_id]['sn_list'] .= join(",", $db_sn_list);
		}

		if($all_sn[$id]) $_REQUEST['sn'][$id] = join("\n", $all_sn[$id]);
	}

	return $err;
}

function mark_adj_attachment(){
	global $con, $sessioninfo, $config;
	
	//print_r($_FILES);
	
	if(!$_FILES || !isset($_FILES['adj_attachment']))	return;
	
	if($_FILES['adj_attachment']['error'])	return;
	
	$adj_attachment_filename = $_FILES['adj_attachment']['name'];
	$tmp_adj_attachment_name = $_FILES['adj_attachment']['tmp_name'];
	$new_tmp_adj_attachment_name = "/tmp/".$_REQUEST['id']."_".time();
	
	$adj_attachment_name_added = $_REQUEST['adj_attachment_filename'];
	if($adj_attachment_name_added){
		if(in_array($adj_attachment_filename,$adj_attachment_name_added)){
			print "<script>parent.window.upload_filename_deplicate();</script>";
			return;
		}
	}
	rename($tmp_adj_attachment_name, $new_tmp_adj_attachment_name);
	$str = "<script>";
	$str .= "parent.window.mark_adj_attachment_callback('".jsstring($adj_attachment_filename)."', '".$new_tmp_adj_attachment_name."')";
	$str .= "</script>";
	print $str;
}

function download_adj_attachment(){
	global $con, $sessioninfo, $config, $adj_attachment_folder_name;
	
	$bid = mi($_REQUEST['branch_id']);
	$adj_id = mi($_REQUEST['adj_id']);
	$adj_attachment_filename = $_REQUEST['adj_attachment_filename'];
	if(!$bid || !$adj_id || !$adj_attachment_filename)	js_redirect("Invalid parameters", $_SERVER['PHP_SELF']);
	
	// same branch, no need server path
	if($sessioninfo['branch_id'] == $bid || $config['single_server_mode']){
		$server_path = '';
	}else{
		// manually get server path if user does not provide
		$server_path = get_image_path($bid);
	}
	if($server_path)	$server_path .= "/";
	
	
	
	$filepath = $server_path.$adj_attachment_folder_name."/".$bid."/".$adj_id."/new/".$adj_attachment_filename;
	
	header("Location: $filepath");
}

function ajax_open_csv_popup(){
	global $smarty, $headers, $sample;
	
	//create file if not exist
	if (!is_dir("attachments"))	check_and_create_dir("attachments");
	if (!is_dir("attachments/import_adjustment"))	check_and_create_dir("attachments/import_adjustment");
	
	$form =array();
	$form['id'] = $_REQUEST['id'];
	$form['branch_id'] = $_REQUEST['branch_id'];
	$form['timer_id'] = $_REQUEST['timer_id'];
	$form['is_config_adj_type'] = $_REQUEST['is_config_adj_type'];
	
	$smarty->assign("form", $form);
	$smarty->assign("sample_headers", $headers);
	$smarty->assign("sample", $sample);
	
	$ret = array();
	$ret['ok'] = 1;
	$ret['html'] = $smarty->fetch('adjustment.upload_csv.tpl');
	print json_encode($ret);
}

function show_result(){
	global $con, $smarty, $config, $headers, $sample, $sessioninfo;
	
	$form = $_REQUEST;
	$id= mi($form['id']);
	$branch_id = mi($form['branch_id']);
	$timer_id = mi($form['timer_id']);
	$file = $_FILES['import_csv'];
	$is_config_adj_type= $form['is_config_adj_type'];
	
	$f = fopen($file['tmp_name'], "rt");
	$line = fgetcsv($f);
	
	$item_lists = $code_list = array();
	if(count($line) == count($headers[$form['method']])) { 
		$item_list = array();
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
					$ins['qty'] = mf($r[1]);
					break;
			}
			
			if($ins['item_code']) {
				$con->sql_query("select id, doc_allow_decimal from sku_items where active=1 and (sku_item_code = ".ms($ins['item_code'])." or mcode = ".ms($ins['item_code'])." or link_code = ".ms($ins['item_code'])." or artno = ".ms($ins['item_code']).")");
				$item_info = $con->sql_fetchassoc();
				$count = $con->sql_numrows();
				$con->sql_freeresult();
				
				if($count <= 0 && strlen($ins['item_code']) == 13){
					$ins['item_code'] = substr($ins['item_code'], 0, 12);
					$con->sql_query("select id from sku_items where active=1 and (sku_item_code = ".ms($ins['item_code'])." or mcode = ".ms($ins['item_code'])." or link_code = ".ms($ins['item_code'])." or artno = ".ms($ins['item_code']).")");
					$item_info = $con->sql_fetchassoc();
					$count = $con->sql_numrows();
					$con->sql_freeresult();
				}
				
				$sku_item_id = mi($item_info['id']);
				$doc_allow_decimal = $item_info['doc_allow_decimal'];
				if(!$sku_item_id) $error[] = 'Item Code('.$ins['item_code'].') not found';
				if($count > 1)  $error[] = 'Item Code('.$ins['item_code'].') match result more than 1';
				if(!$form['allow_duplicate']){
					$con->sql_query("select * from tmp_adjustment_items where sku_item_id=$sku_item_id and adjustment_id=$id and branch_id=$branch_id and timer_id=$timer_id and user_id=".mi($sessioninfo['id']));
					$duplicate_row2 = $con->sql_numrows();
					$con->sql_freeresult();
					if($duplicate_row2 > 0)  $error[] = "Item Code(".$ins['item_code'].") is duplicated";
				}
				
				//check sku item doc_allow_decimal
				if($sku_item_id && $count == 1){
					//check difference item code same sku item
					if(!$form['allow_duplicate']){
						if(in_array($sku_item_id, $item_list)){
							$error[] = "Item Code(".$ins['item_code'].") is duplicated";
						}else{
							$item_list[] = $sku_item_id;
						}
					}
					
					if($doc_allow_decimal){
						$ins['qty'] = round($ins['qty'], $config['global_qty_decimal_points']);
					}else{
						$ins['qty'] = mi($ins['qty']);
					}
				}
			}else   $error[] = "Empty Item Code";
			
			if($ins['qty'] == 0){
				$error[] = "Invalid Qty";
			}else{
				if($is_config_adj_type =='+' && mf($ins['qty']) < 0){
					$error[] = "Only allow positive Qty";
				}elseif($is_config_adj_type =='-' && mf($ins['qty']) > 0){
					$error[] = "Only allow negative Qty";
				}
			}
			
			$error = array_unique($error);
			if($error)	$ins['error'] = join(', ', $error);
			
			$item_lists[] = $ins;
			
			if($ins['error'])	$result['error_row']++;
			else				$result['import_row']++;
		}
		
		if($item_lists){
			$header = $headers[$form['method']];
			if($result['error_row'] > 0)	$header[] = 'Error';
			
			$file_name = "adjustment_".time().".csv";
			
			$fp = fopen("attachments/import_adjustment/".$file_name, 'w');
			fputcsv($fp, array_values($header));
			foreach($item_lists as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			chmod("attachments/import_adjustment/".$file_name, 0777);
			
			print "<script>parent.window.ADJUSTMENT_UPLOAD_CSV.ajax_show_result('$file_name', '');</script>";
		}else{
			print "<script>parent.window.ADJUSTMENT_UPLOAD_CSV.ajax_show_result('', 'No data found on the file.');</script>";
		}
	}else {
		print "<script>parent.window.ADJUSTMENT_UPLOAD_CSV.ajax_show_result('', 'Column not match. Please re-check import file.');</script>";
	}
}

function ajax_get_uploaded_csv_result(){
	global $smarty, $headers, $sample;
	
	$form = $_REQUEST;
	if(!$form['file_name'] || !file_exists("attachments/import_adjustment/".$form['file_name'])){
		die("File no found.");
		exit;
	}
	
	$f = fopen("attachments/import_adjustment/".$form['file_name'], "rt");
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
				$data_list['qty'] = $r[1];
				if(!$r[$error_index]) $result['import_row']++;
				else{
					$data_list['error'] = $r[2];
					$result['error_row']++;
				}
				break;
		}
		$item_lists[] = $data_list;
	}
	
	$ret = array();
	if($item_lists){
		$header = $headers[1];
		if($result['error_row'] > 0)	$header[] = 'Error';
		
		$smarty->assign("result", $result);
		$smarty->assign("file_name", $form['file_name']);
		$smarty->assign("item_header", array_values($header));
		$smarty->assign("item_lists", $item_lists);
		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('adjustment.upload_csv.result.tpl');
	}else{
		die("Result not found.");
	}	
	
	print json_encode($ret);
}

function ajax_import_adjustment(){
	global $con, $smarty, $headers, $sessioninfo, $appCore;
	
	$form = $_REQUEST;
	$id= mi($form['id']);
	$branch_id = mi($form['branch_id']);
	$timer_id = mi($form['timer_id']);
	$item_checked = $form['adj_tmp_item'];
	$is_config_adj_type = $form['is_config_adj_type'];
	
	if(!$form['file_name'] || !file_exists("attachments/import_adjustment/".$form['file_name'])){
		die('File not found.');
	}
	
	$f = fopen("attachments/import_adjustment/".$form['file_name'], "rt");
	$line = fgetcsv($f);
	if(in_array('Error', $line)) {
		$error_index = array_search("Error", $line);
	}else{
		$error_index = count($line);
	}
    
	$con->sql_query("select count(*) as count from tmp_adjustment_items where adjustment_id = ".mi($_REQUEST['id'])." and user_id = ".mi($sessioninfo['id'])." and timer_id = ".mi($form['timer_id']));
	$r = $con->sql_fetchrow();
	$con->sql_freeresult();
	$count2 = mi($r['count'])+1;
	
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
					if($r[0]) {
						$con->sql_query("select * from sku_items where active=1 and (sku_item_code=".ms($r[0])." or mcode=".ms($r[0])." or link_code=".ms($r[0])." or artno=".ms($r[0]).") limit 1");
						
						$item_info = $con->sql_fetchassoc();
						$con->sql_freeresult();
					}
					$sku_item_id = mi($item_info['id']);
					$num =0;
					
					$con->sql_query("select * from tmp_adjustment_items where sku_item_id =$sku_item_id and adjustment_id =$id and branch_id=$branch_id and timer_id=$timer_id and user_id = $sessioninfo[id]");
					$count = $con->sql_numrows();
					$exist_adj_info = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($count > 0){
						$con->sql_query("update tmp_adjustment_items set qty=qty+".mf($r[1])." where sku_item_id =$sku_item_id and adjustment_id =$id and branch_id=$branch_id and timer_id=$timer_id and user_id=$sessioninfo[id]");
						$num = $con->sql_affectedrows();
						
						$temp = array();
						$temp['duplicate'] = true;
						$temp['existed_item_id'] = $exist_adj_info['id'];
						$temp['existed_si_code'] = $item_info['sku_item_code'];
						$temp['qty'] = $r[1];
						$temp['is_config_adj_type'] = $is_config_adj_type;
						$row_item[] = $temp;
					}else{
						$adj_item_id = $appCore->generateNewID("tmp_adjustment_items", "branch_id=".mi($branch_id));
						$upd = array();
						$upd['id'] = mi($adj_item_id);
						$upd['adjustment_id'] = $id;
						$upd['branch_id'] = $branch_id;
						$upd['user_id'] = $sessioninfo['id'];
						$upd['sku_item_id'] = $sku_item_id;
						$upd['qty'] = mf($r[1]);
						$upd['timer_id'] = $timer_id;
						
						
						$items_details = get_sku_items_details($branch_id, $sku_item_id);
						$upd['cost'] = $items_details['cost'];
						
						// get selling price
						$sql = "select if(sp.price,sp.price,si.selling_price) as selling_price from sku_items si left join sku_items_price sp on si.id=sp.sku_item_id and sp.branch_id=$branch_id where si.id=$sku_item_id";
						$con->sql_query($sql);
						$upd['selling_price'] = floatval($con->sql_fetchfield(0));
						$con->sql_freeresult();
						
						// get stock balance - latest
						$sql = "select qty from sku_items_cost where branch_id=$branch_id and sku_item_id=$sku_item_id";
						$con->sql_query($sql);
						$upd['stock_balance'] = floatval($con->sql_fetchrow(0));
						$con->sql_freeresult();
						
						$con->sql_query("insert into tmp_adjustment_items ".mysql_insert_by_field($upd));
						$num = $con->sql_affectedrows();
						
						//after insert get adjustment item details
						$q1=$con->sql_query("select tai.*, tai.id as tmp_id, si.sku_item_code, si.description as description, si.artno, si.mcode, si.doc_allow_decimal, 
											puom.code as packing_uom_code, sku.have_sn, si.link_code
											from tmp_adjustment_items tai
											left join sku_items si on tai.sku_item_id=si.id
											left join sku on sku.id = si.sku_id
											left join uom puom on puom.id = si.packing_uom_id
											where tai.id=".mi($adj_item_id)." and adjustment_id = $id and tai.branch_id=$branch_id and user_id = $sessioninfo[id] and timer_id=$timer_id order by tai.id");
						while($r2 = $con->sql_fetchassoc($q1)){
							$temp = array();
							$smarty->assign("is_config_adj_type", $is_config_adj_type);
							$smarty->assign("item", $r2);
							$smarty->assign("count",$count2);
							$count2++;

							$tpl = $smarty->fetch("adjustment.new.row.tpl");
							$temp['rowdata'] = "<tr id=titem".mi($r2['tmp_id']).">".$tpl."</tr>";

							// if found have serial no
							if($r2['have_sn'] != 0) $temp['sn'] = $smarty->fetch("adjustment.sn.new.tpl");
							
							$row_item[] = $temp;
						}
						$con->sql_freeresult($q1);
					}
					
					if ($num > 0)	$num_row++;
				}else{
					if($r[$error_index])  $error_list[] = $r;
				}
				break;
		}
	}
        
	if($error_list) {
		$fp = fopen("attachments/import_adjustment/invalid_".$form['file_name'], 'w');
		fputcsv($fp, array_values($line));
		
		foreach($error_list as $r){
			fputcsv($fp, $r);
		}
		fclose($fp);
		
		chmod("attachments/import_adjustment/invalid_".$form['file_name'], 0777);
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
		$ret['msg'] = "Failed to add adjustment items.";
	}
	
	print json_encode($ret);
}


//download csv sample
function download_sample_adjustment(){
	global $headers, $sample;
	
	header("Content-type: application/msexcel");
	header("Content-Disposition: attachment; filename=sample_import_adjustment.csv");
	
	print join(",", array_values($headers[$_REQUEST['method']]));
	foreach($sample[$_REQUEST['method']] as $sample) {
		$data = array();
		foreach($sample as $d) {
			$data[] = $d;
		}
		print "\n\r".join(",", $data);
	}
}

//export adjustment item
function export_adjustment_item(){
	global $con, $smarty, $sessioninfo, $appCore, $config;
	
	$got_item = false;
	$form = $_REQUEST;
	$branch_id= mi($form['branch_id']);
	$id= mi($form['id']);
	
	//header
	$link_code_name = $config['link_code_name'] ? $config['link_code_name'] : 'Link Code';
	$header_array = array('ARMS Code', 'Mcode', 'Art-no', $link_code_name, 'UOM', 'qty', 'cost');
	
	//select report prefix from branch
	$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
	$prefix=$con->sql_fetchrow();
	$con->sql_freeresult();
	$report_prefix = $prefix['report_prefix'];
	
	$formatted=sprintf("%05d",$id);
	$document_no = $report_prefix.$formatted;
	
	$filename = 'adjustment_export_'.$document_no.'.csv';
	$fp = fopen($filename, 'w');
	
	if($branch_id && $id){
		$sql = "select si.sku_item_code, si.mcode, si.artno, si.link_code, uom.code as code, ai.qty, ai.cost 
		from adjustment_items ai 
		left join adjustment adj on adj.id=ai.adjustment_id and adj.branch_id=ai.branch_id
		left join sku_items si on si.id = ai.sku_item_id
		left join uom on uom.id = si.packing_uom_id
		where ai.adjustment_id=$id and ai.branch_id=$branch_id";
		$q1 = $con->sql_query($sql);
		if ($con->sql_numrows($q1)>0) {
			fputcsv($fp, $header_array);
			$got_item = true;
			while($r = $con->sql_fetchassoc($q1)){
				$arr = array();
				$arr[] = $r['sku_item_code'];
				$arr[] = $r['mcode'];
				$arr[] = $r['artno'];
				$arr[] = $r['link_code'];
				$arr[] = $r['code'];
				$arr[] = $r['qty'];
				$arr[] = $r['cost'];
				fputcsv($fp, $arr);
			}
		}
		$con->sql_freeresult($q1);
		fclose($fp);
	}
	
	if ($got_item) {
		log_br($sessioninfo['id'], 'ADJUSTMENT', $id, "Export Adjustment Items to CSV File");
		header('Content-Type: application/msexcel');
		header('Content-Disposition: attachment;filename='.$filename);
		print file_get_contents($filename);
	}
	unlink($filename);
	
	if (!$got_item){
		js_redirect("No adjustment items data.", $_SERVER['PHP_SELF']);
	}
	exit;
}

?>