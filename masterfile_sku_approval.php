<?
/*
Revision History
=================
5/16/2007 11:59:26 AM   yinsee
- added config[sku_variety_start_from_zero] if set to true, first variety begin as 0000. otherwise 0000 is parent.
- added config[sku_matrix_start_from_zero] if set to true, Matrix will NOT have 0000 as parent.
- moved create_sku_items() function to masterfile_sku_application.include.php

7/23/2007 5:29:56 PM yinsee
- fix the bug when mutliple select in "Reject" reason, the array passed is double-urlencoded. (scriptaculous bug??)

9/19/2007 1:37:16 PM gary
- if last approval, verify the multics details have been completed??
- asking for re-submit if the imcomplete from multics details.

9/28/2007 12:27:33 PM gary
- under sku approval, show items with same artno in same category (to prevent duplicate application)

10/1/2007 10:38:10 AM gary
- COMMENT OUT GET SAME ARTNO LIST FUNCTION.

11/16/2007 1:55:39 PM gary
- add UOM for variety.

7/28/2009 3:11:52 PM Andy
- add ctn 1 and ctn 2

1/7/2010 5:02:20 PM Andy
- Fix sku application approval & status scrren din't show mcode problem

1/18/2010 5:45:52 PM Andy
- approval order changes

8/13/2010 10:02:34 AM Andy
- Add SKU without inventory. (control by category + sku and can be inherit)
- Add Fresh Market Sku. (control by category + sku and can be inherit)(Need Config)

10/6/2010 3:44:47 PM Andy
- Fix a bugs which system failed to detect whether user is last approval.

7/6/2011 12:05:01 PM Andy
- Change split() to use explode()

9/14/2011 11:15:00 AM Alex
- Add article size data

5/3/2012 5:56:54 PM Andy
- Add Member Category Discount and Category Reward Point can be set by member type, by branch.

5/16/2012 2:09:34 PM Justin
- Fixed bugs of never unserialize po reorder qty by branch.

6/25/2012 2:48 PM Andy
- Add feature to allow customize SKU information.

9/4/2012 5:04 PM Drkoay
- call save_sku_items_price() during approve sku if $config['masterfile_update_sku_items_price_on_approve']=1

9/6/2012 9:21 AM Drkoay
- config masterfile_update_sku_items_price_on_approve change to consignment_new_sku_use_currency_table

5/17/2013 11:11 AM Justin
- Enhanced to manage additional description while config is turned on.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/30/2013 1:44 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

8/21/2014 2:38 PM Justin
- Enhanced to have calculation on GST (%) and selling price after/before GST while viewing application.

9/22/2014 3:48 PM Justin
- Bug fixed on inclusive tax get wrong info.

11/8/2014 11:34 AM Justin
- Enhanced to add config checking while loading gst list.

3/6/2015 3:27 PM Andy
- Enhanced the modules to check when get sku/category gst, no need to check force zero rate.

3/12/2015 2:37 PM Andy
- Enhanced to able to change input tax, output tax, inclusive tax when under approval screen.

4/10/2015 10:40 AM Andy
- Enhance to get trade discount percent when on sku approval screen.
- Fix sku inclusive tax use selecting real inclusive tax when inclusive tax is inherit.
- Enhance to update cost when user submit sku approval.

7/28/2015 3:14 PM Justin
- Bug fixed on missing of GST information.

10/21/2015 3:35 PM DingRen
- fix aku approval list only load on status 0 and 1

10/7/2016 11:46 AM Andy
- Fixed stucked approval redirect to wrong php.

5/11/2017 11:04 AM Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long

11/13/2019 3:52 PM William
- Enhanced to display promotion photo to sku approval.

11/10/2020 5:02 PM Andy
- Enhanced to have sql_begin_transaction() and sql_commit() when add sku.
*/

include("include/common.php");
include("masterfile_sku_application.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MST_SKU_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_APPROVAL', BRANCH_CODE), "/index.php");

$approval_status = array(1 => "Approved", 2 => "Rejected", 3 => "KIV (Pending)", 4 => "Terminated");
$hqcon = connect_hq(); // if not HQ, connect to HQ

if ($_REQUEST['on_behalf_of'] && $_REQUEST['on_behalf_by']) {
	$hqcon->sql_query("select group_concat(u separator ', ') as u from user where id in (".str_replace('-',',',$_REQUEST['on_behalf_of']).")");
	$on_behalf_of_u = $hqcon->sql_fetchfield(0);
	$hqcon->sql_query("select u from user where id = ".mi($_REQUEST['on_behalf_by'])." limit 1");
	$on_behalf_by_u = $hqcon->sql_fetchfield(0);
	$approval_on_behalf = array(
		'on_behalf_of' => str_replace('-',',',$_REQUEST['on_behalf_of']),
		'on_behalf_by' => mi($_REQUEST['on_behalf_by']),
		'on_behalf_of_u' => $on_behalf_of_u,
		'on_behalf_by_u' => $on_behalf_by_u,
	);
}
else {
	$approval_on_behalf = false;
}
$smarty->assign('approval_on_behalf', $approval_on_behalf);

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'ajax_load_sku':
			load_sku_details();
			$smarty->display("masterfile_sku_approval.sku.tpl");
			exit;

		case 'kiv_approval':
		    $aid = intval($_REQUEST['approval_history_id']);
		    $skuid = intval($_REQUEST['id']);
		    $hqcon->sql_query("update approval_history set status = 3 where id = $aid");
		    $hqcon->sql_query("update sku set status = 3 where id = $skuid");
		    //msg to user
			echo"
			<script>
			alert('SKU Application $approval_status[3]');
			sel_next_tab();
			</script>";	
      		//print "SKU Application $approval_status[3]";
			exit;

		case 'terminate_approval':
		case 'all_approval':
		case 'save_approval':		
			//print_r($_REQUEST);exit;
		 	// save approval status (1 = approve, 2 = rejected. 4 = Terminate)
			$approve = 1;
			$approval = array();
		    if ($_REQUEST['a'] == 'terminate_approval')
		    {
				$approve = 4;
				foreach ($_REQUEST['approval'] as $k=>$v)
				{
					$approval[$k] = $_REQUEST['reason2'];
				}
			}
			elseif ($_REQUEST['a'] == 'all_approval')
			{
				foreach ($_REQUEST['approval'] as $k=>$v)
				{
					$approval[$k] = 'Approve';
				}
			}
			else
			{
			 	foreach ($_REQUEST['approval'] as $k=>$v)
				{
					$approval[$k] = $v;
					if ($v == 'Reject')
					{
						$approve = 2;
						$approval[$k] = array();
						// when mutliple select, the array passed is urlencoded. (scriptaculous bug??)
						foreach($_REQUEST['reason'][$k] as $d)
						{
							$approval[$k][] = urldecode(urldecode($d));
						}
						//$approval[$k] = $_REQUEST['reason'][$k];
						if ($_REQUEST['approval_other'][$k] != '') $approval[$k]['others'] = $_REQUEST['approval_other'][$k];
					}
				}
			}
			// save approval status
			$sz = sz($approval);
			$aid = intval($_REQUEST['approval_history_id']);
			$skuid = intval($_REQUEST['id']);
			
			if ($aid > 0)
			{
			    // double check approval
				$hqcon->sql_query("select approvals from approval_history where id = $aid");
				if ($app = $hqcon->sql_fetchrow())
				{
				    // not allowed
					if ($approval_on_behalf) {
						$u = explode(',',$approval_on_behalf['on_behalf_of']);
						$search_approval = $u[0];
					}
					else {
						$search_approval = $sessioninfo['id'];
					}
				    if (!strstr($app[0], "|$search_approval|"))
				    {
				    	fail(sprintf($LANG['SKU_NOT_APPROVAL'], $skuid));
				    	exit;
				    }
				}
				
				//checking the multics details that submitted
				if ($config['sku_application_require_multics'] && $_REQUEST['last_approval'] && $approve =='1'){
					if ($_REQUEST['multics_dept'] == '' || $_REQUEST['multics_section'] == '' || $_REQUEST['multics_category'] == '' || $_REQUEST['multics_brand'] == '' || $_REQUEST['multics_pricetype'] == '')
					{
					    	$err['top'][] = $LANG['SKU_EMPTY_LINK_CODE'];
					}
			
					if (!find_multics_code(file("MDEPT.dat"), $_REQUEST['multics_dept']))
					    $err['top'][] = sprintf($LANG['SKU_INVALID_LINK_CODE'], "Department");
					if (!find_multics_code(file("MSECT.dat"), $_REQUEST['multics_section']))
					    $err['top'][] = sprintf($LANG['SKU_INVALID_LINK_CODE'], "Section");
					if (!find_multics_code(file("MCAT.dat"), $_REQUEST['multics_category']))
					    $err['top'][] = sprintf($LANG['SKU_INVALID_LINK_CODE'], "Category");
					if (!find_multics_code(file("MBRAND.dat"), $_REQUEST['multics_brand']))
				    	$err['top'][] = sprintf($LANG['SKU_INVALID_LINK_CODE'], "Brand");
				}
				
				if ($_REQUEST['description']){
					foreach ($_REQUEST['description'] as $id=>$v){
						$ret_desc = check_receipt_desc_max_length($_REQUEST['receipt_description'][$id]);
						if($ret_desc["err"]){
							$err['top'][] = $ret_desc["err"];
						}
					}
				}
				
				if(!$err){
					$hqcon->sql_begin_transaction();
					
					// update item's description
					if (isset($_REQUEST['description']))
					{
						foreach ($_REQUEST['description'] as $id=>$v)
						{
						    $v2 = $_REQUEST['receipt_description'][$id];
						    $hqcon->sql_query("update sku_apply_items set description=".ms($v).", receipt_description = " . ms($v2) . " where id = ".mi($id));
						}
					}
					
					$form = $_REQUEST;
					
					$upd_sku = array();
					
					// got gst
					if($config['enable_gst']){
						if(isset($form['mst_input_tax']))	$upd_sku['mst_input_tax'] = $form['mst_input_tax'];
						if(isset($form['mst_output_tax']))	$upd_sku['mst_output_tax'] = $form['mst_output_tax'];
						if(isset($form['mst_inclusive_tax']))	$upd_sku['mst_inclusive_tax'] = $form['mst_inclusive_tax'];
					}
					
					if (isset($_REQUEST['brand_id']))
					{
						//$hqcon->sql_query("update sku set brand_id = " . mi($_REQUEST['brand_id']) . " where id = $skuid");
						$upd_sku['brand_id'] = mi($_REQUEST['brand_id']);
					}
	
					if (isset($_REQUEST['category_id']))
					{
						//$hqcon->sql_query("update sku set category_id = " . mi($_REQUEST['category_id']) . " where id = $skuid");
						$upd_sku['category_id'] = mi($_REQUEST['category_id']);
					}
	
					// update SKU status
					$upd_sku['status'] = $approve;
					if(isset($_REQUEST['multics_dept']))	$upd_sku['multics_dept'] = trim($_REQUEST['multics_dept']);
					if(isset($_REQUEST['multics_section']))	$upd_sku['multics_section'] = trim($_REQUEST['multics_section']);
					if(isset($_REQUEST['multics_brand']))	$upd_sku['multics_brand'] = trim($_REQUEST['multics_brand']);
					if(isset($_REQUEST['multics_category']))	$upd_sku['multics_category'] = trim($_REQUEST['multics_category']);
					if(isset($_REQUEST['multics_pricetype']))	$upd_sku['multics_pricetype'] = trim($_REQUEST['multics_pricetype']);
					
					$hqcon->sql_query("update sku set ".mysql_update_by_field($upd_sku)." where id=$skuid");
					log_br($sessioninfo['id'], 'MASTERFILE', $skuid, "New SKU Approval (ID#$skuid, Status: $approval_status[$approve])");
	
					// update approval items
					$hqcon->sql_query("insert into approval_history_items (approval_history_id, user_id, status, log) values ($aid, $sessioninfo[id], $approve, $sz)");
	
					/*
					// get the PM list
					$hqcon->sql_query("select flow_approvals, approvals, sku.apply_by, notify_users from approval_history left join sku on approval_history.ref_id = sku.id where approval_history.id = $aid");
					$r = $hqcon->sql_fetchrow();
	
					$recipients = $r[3]; 
					//str_replace($r[1], "|", $r[0]) . $r[3];
					// if reject, no need to send to apply person
					//if ($approve != 2) $recipients .= $r[2];
         	$recipients = str_replace("|$sessioninfo[id]|", "|", $recipients);
         	$to = preg_split("/\|/", $recipients);
			*/
					// update sku items
					if(isset($form['item_id_list']) && $form['item_id_list']){
						// loop each sku apply item id
						foreach($form['item_id_list'] as $row_index => $item_id){
							$upd_sai = array();
							
							$q1 = $hqcon->sql_query("select * from sku_apply_items where id=".mi($item_id));
							$sai = $hqcon->sql_fetchassoc($q1);
							$hqcon->sql_freeresult($q1);
							
							if($sai['product_matrix']){
								$sai['product_matrix'] = unserialize($sai['product_matrix']);
								
								if(isset($form['tbcost'][$item_id])){
									foreach($form['tbcost'][$item_id] as $rid => $new_cost){
										if(isset($sai['product_matrix']['tbcost'][$rid]))	$sai['product_matrix']['tbcost'][$rid] = $new_cost;
									}
								}
								
								$upd_sai['product_matrix'] = serialize($sai['product_matrix']);
							}
							
							if(isset($form['dtl_input_tax'][$item_id]))	$upd_sai['input_tax'] = $form['dtl_input_tax'][$item_id];
							if(isset($form['dtl_output_tax'][$item_id]))	$upd_sai['output_tax'] = $form['dtl_output_tax'][$item_id];
							if(isset($form['dtl_inclusive_tax'][$item_id]))	$upd_sai['inclusive_tax'] = $form['dtl_inclusive_tax'][$item_id];
							if(isset($form['cost_price'][$item_id]))	$upd_sai['cost_price'] = $form['cost_price'][$item_id];
							
							if($upd_sai){
								$hqcon->sql_query("update sku_apply_items set ".mysql_update_by_field($upd_sai)." where id = ".mi($item_id));
							}
						}
					}
	
	
					// remove current user from the approval list
					$params = array();
					$params['approve'] = $approve;
					$params['user_id'] = $sessioninfo['id'];
					$params['id'] = $aid;
					$params['tbl'] = 'approval_history';
					$params['update_approval_flow'] = true;
					
					$flow_completed = check_is_last_approval_by_id($params, $hqcon);
					// send pm
					$to = get_pm_recipient_list2($skuid,$aid,$approve,'approval',0,'sku');
					$status_str = ($flow_completed || $approve != 1) ? $approval_status[$approve] : '';
					
					
					
					//$hqcon->sql_query("update approval_history set status = $approve, approvals = replace(approvals, '|$sessioninfo[id]|', '|') where id = $aid");
	
					// check if completed
					//$hqcon->sql_query("select approvals from approval_history where id = $aid");
					//$r = $hqcon->sql_fetchrow();
					//$flow_completed = ($r[0] == '|' || $r[0] == '');
				
				}
				else{
					//send bck to resubmit if multics details incomplete
					load_sku_details();
					$smarty->assign("form", $_REQUEST);
					$smarty->assign("errm", $err);
					$smarty->display("masterfile_sku_approval.sku.tpl");
					exit;			
				}

			}

			if (!$err){
				if($flow_completed){
					if ($approve == 1){ 
						save_sku_items($skuid);
						if($config['consignment_new_sku_use_currency_table']){	
							save_sku_items_price($skuid);
						}
					}elseif ($approve == 4){ // inactivate
						$hqcon->sql_query("update sku set active=0 where id = $skuid");
					}
				}
					
				send_pm2($to, "New SKU Application (ID#$skuid) $status_str", "masterfile_sku_application.php?a=view&id=$skuid", array('module_name'=>'sku'));
				
				$hqcon->sql_commit();
			}
			
			if ($approval_on_behalf) {
				print '
				<script>
				window.location = "/stucked_document_approvals.php?m=sku";
				</script>
				';
				exit;
			}
			
			//msg to user
			echo"
			<script>
			alert('SKU Application $approval_status[$approve]');
			sel_next_tab();
			</script>";	
			//print "SKU Application $approval_status[$approve]";
		    exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    fail("<h1>Unhandled Request</h1>");
		    exit;
	}
}
	
do_approval_all();

function do_approval_all()
{                                                       
	global $hqcon, $smarty, $LANG, $sessioninfo, $approval_on_behalf;
	
	 /*$sql = "select sku.*, approvals, flow_approvals as org_approvals, notify_users, brand.description as brand, category.description as category, category.tree_str, branch.code as branch 
from sku 
left join approval_history on sku.approval_history_id = approval_history.id 
left join brand on sku.brand_id = brand.id left join category on sku.category_id = category.id left join branch on sku.apply_branch_id = branch.id 
where approvals like '|$sessioninfo[id]|%' and sku.status <> 2 order by sku.timestamp";*/

	if ($approval_on_behalf) {
		$u = explode(',',$approval_on_behalf['on_behalf_of']);
		$search_approval = $u[0];
		$doc_filter = ' and sku.id = '.mi($_REQUEST['id']).' ';
	}
	else $search_approval = $sessioninfo['id'];

   	$hqcon->sql_query($sql = "select sku.*, approvals, flow_approvals as org_approvals, notify_users, brand.description as brand, category.description as category, category.tree_str, branch.code as branch 
from sku 
left join approval_history on sku.approval_history_id = approval_history.id 
left join brand on sku.brand_id = brand.id left join category on sku.category_id = category.id left join branch on sku.apply_branch_id = branch.id 
where (
(approvals like '|$search_approval|%' and approval_order_id=1) or
(approvals like '%|$search_approval|%' and approval_order_id in (2,3))
) and sku.status in (0,1) $doc_filter order by sku.timestamp");

	$smarty->assign("PAGE_TITLE", "SKU Approval");
   	$smarty->assign("sku", $hqcon->sql_fetchrowset());
   	$smarty->display("masterfile_sku_approval.index.tpl");
}

function load_sku_details(){
	global $hqcon, $smarty, $LANG, $sessioninfo, $config, $output_tax_list, $input_tax_list;
	
	$id = intval($_REQUEST['id']);
	$q1 = $hqcon->sql_query("select sku.*, user.u as username, branch.code as apply_branch_code, branch.ip as apply_branch_ip, category.description as category, category.tree_str as tree_str,  vendor.description as vendor, brand.code as brand_code, brand.active as brand_active, brand.description as brand from ((((sku left join category on sku.category_id = category.id) left join vendor on sku.vendor_id = vendor.id) left join brand on sku.brand_id = brand.id) left join user on sku.apply_by = user.id) left join branch on sku.apply_branch_id = branch.id where sku.id = $id");
	$sku = $hqcon->sql_fetchassoc($q1);
	$hqcon->sql_freeresult($q1);
	if(!$sku){
	    printf($LANG['SKU_APPLICATION_NOT_EXIST'], $id);
	    exit;
	}

	$sku['listing_fee_remark'] = unserialize($sku['listing_fee_remark']);
	$sku['po_reorder_qty_by_branch'] = unserialize($sku['po_reorder_qty_by_branch']);
	$sku['cat_tree'] = get_category_tree($sku['category_id'], $sku['tree_str'], $dummy)  . " > " . $sku['category'];
	// get approval/reject comment
	$q1 = $hqcon->sql_query("select approval_history_items.status, approval_history_items.timestamp, approval_history_items.log, user.u 
							from approval_history_items 
							left join user on approval_history_items.user_id = user.id
							where approval_history_id = $sku[approval_history_id]
							order by timestamp");
	$approval = array();
	while ($r = $hqcon->sql_fetchassoc($q1)){
		$r['log'] = unserialize($r['log']);
		array_push($approval, $r);
	}
	$hqcon->sql_freeresult($q1);
	$sku['approval_history_items'] = $approval;
	
	// trade discount code
	if(!$config['consignment_modules'] && !$config['sku_always_show_trade_discount'] && $sku['trade_discount_type']>0 && $sku['default_trade_discount_code']){
		$dept_id = mi(get_department_id($sku['category_id'])); //get dept id
		
		if ($sku['trade_discount_type'] == 1){	// use brand table
			$sql = "select skutype_code, rate from brand_commission where branch_id=".mi($sku['apply_branch_id'])." and brand_id=".mi($sku['brand_id'])." and department_id=".$dept_id." and skutype_code=".ms($sku['default_trade_discount_code']);
		}elseif ($sku['trade_discount_type'] == 2){	// use vendor table
			$sql = "select skutype_code, rate from vendor_commission where branch_id=".mi($sku['apply_branch_id'])." and vendor_id=".mi($sku['vendor_id'])." and department_id=".$dept_id." and skutype_code=".ms($sku['default_trade_discount_code']);
		}
		
		// select rate
		$q1 = $hqcon->sql_query($sql);
		$sku['trade_discount_info'] = $hqcon->sql_fetchassoc($q1);
		$hqcon->sql_freeresult($q1);
	}
	$smarty->assign("form", $sku);
	//echo"<pre>";print_r($sku);echo"</pre>";
	
	if($config['enable_gst']){
		// get category tax info
		$cat_input_tax = get_category_gst("input_tax", $sku['category_id'], array('no_check_use_zero_rate'=>1));
		$cat_output_tax = get_category_gst("output_tax", $sku['category_id'], array('no_check_use_zero_rate'=>1));
		$cat_inclusive_tax = get_category_gst("inclusive_tax", $sku['category_id']);
		
		$cat_gst_settings['input_tax'] = $cat_input_tax;	// an array
		$cat_gst_settings['output_tax'] = $cat_output_tax; // an array
		$cat_gst_settings['inclusive_tax'] = $cat_inclusive_tax;	// yes,no
	}
	
	// load items
	$q1 = $hqcon->sql_query("select sku_apply_items.*, uom.code as uom,ri.group_name as ri_group_name
							from sku_apply_items 
							left join uom on uom.id=packing_uom_id
							left join ri on ri.id=sku_apply_items.ri_id
							where sku_id = $id");
	//$hqcon->sql_query("select * from sku_apply_items where sku_id = $id");
	$items = array();
	while ($item = $hqcon->sql_fetchassoc($q1)){
		if($config['enable_gst']){
			// get output tax follow by item > sku > category
			if($item['output_tax'] == -1) $output_tax = $sku['mst_output_tax'];
			else $output_tax = $item['output_tax'];
			
			// get input tax follow by item > sku > category
			if($item['output_tax'] == -1) $input_tax = $sku['mst_input_tax'];
			else $input_tax = $item['input_tax'];
			
			// output gst rate
			if($output_tax == -1) $item['output_tax_rate'] = $cat_output_tax['rate'];
			else $item['output_tax_rate'] = $output_tax_list[$output_tax]['rate'];
			
			// input gst rate
			if($output_tax == -1) $item['input_tax_rate'] = $cat_input_tax['rate'];
			else $item['input_tax_rate'] = $input_tax_list[$input_tax]['rate'];
			
			// get inclusive tax follow by item > sku > category
			if($item['inclusive_tax'] == "inherit") $inclusive_tax = $sku['mst_inclusive_tax'];
			else $inclusive_tax = $item['inclusive_tax'];
		}
		
		if($inclusive_tax == "inherit") $inclusive_tax = $cat_inclusive_tax;
		$item['real_inclusive_tax'] = $inclusive_tax;
	
		$arr = unserialize($item['product_matrix']);
		$item['tb'] = $arr['tb'];
		$item['tbm'] = $arr['tbm'];
		$item['tbprice'] = $arr['tbprice'];
		$item['tbcost'] = $arr['tbcost'];
		$item['description_table'] = unserialize($item['description_table']);
		$item['category_disc_by_branch_inherit'] = unserialize($item['category_disc_by_branch_inherit']);
		$item['category_point_by_branch_inherit'] = unserialize($item['category_point_by_branch_inherit']);
		$item['extra_info'] = unserialize($item['extra_info']);
		
		if($config['sku_enable_additional_description'] && $item['additional_description']) $item['additional_description'] = join("\n", unserialize($item['additional_description']));
		
		//COMMENT OUT THIS FUNCTION
		/*
		if($item['artno']){
			$item['art_list']=get_same_artno_list($sku['id'], $sku['category_id'],$sku['vendor_id'],$sku['brand_id'],$item['artno']);
		}
		*/
		
		$group_num = ceil($item['id']/10000);
		$sku_apply_photo_path = "sku_photos/apply_promo_photo/".$group_num."/".$item['id'];
		if(file_exists("$sku_apply_photo_path/1.jpg")){
			$item['promotion_photo'] = "$sku_apply_photo_path/1.jpg";
		}
		split_artno_size($item);
		array_push($items, $item);
	}
	$hqcon->sql_freeresult($q1);
	//echo"<pre>";print_r($items);echo"</pre>";
	$smarty->assign("items", $items);
	
	$hurl = get_branch_file_url($sku['apply_branch_code'], $sku['apply_branch_ip']);

	$smarty->assign("image_path", $hurl);

	$_REQUEST['a'] = 'approval';
	$aid = $sku['approval_history_id'];
	
	// check whether is last approval
	$params = array();
	$params['user_id'] = $sessioninfo['id'];
	$params['id'] = $aid;
	$params['tbl'] = 'approval_history';
	$is_last_approval = check_is_last_approval_by_id($params, $hqcon);
	$smarty->assign("last_approval", $is_last_approval);
	$smarty->assign("cat_gst_settings", $cat_gst_settings);
	
	/*$hqcon->sql_query("select approvals from approval_history where id = $aid");
	if ($app = $hqcon->sql_fetchrow())
	{
		$smarty->assign("last_approval", ($app[0] == "|$sessioninfo[id]|"));
	}*/
}

function get_same_artno_list($sku_id, $category_id, $vendor_id, $brand_id, $code){
	global $hqcon, $con, $smarty, $sessioninfo;
	
	$q0=$con->sql_query("select department_id from category where id=".ms($category_id));
	$r0 = $con->sql_fetchrow($q0);
	$dept_id=$r0['department_id'];	
	
	$q1=$con->sql_query("select root_id from category where id=".ms($dept_id));
	$r1 = $con->sql_fetchrow($q1);	
	$q2=$con->sql_query("select description from category where id=".ms($r1['root_id']));
	$r2 = $con->sql_fetchrow($q2);
	$line=strtoupper($r2['description']);
	
	$code = strtoupper($code);
	$where="";
	if($line=='SOFTLINE'){$where=" and sku.brand_id=$brand_id";}
		
	$q3=$con->sql_query("select concat('SKU ',sku_id) as id, sku_id as sku_id, artno, c1.department_id
from sku_items 
left join sku on sku_id = sku.id
left join category c1 on c1.id=sku.category_id
left join brand on brand.id=sku.brand_id
where sku_id <> $sku_id and vendor_id = $vendor_id and artno = ".ms($code));

	$r3 = $con->sql_fetchrowset($q3);
		
	$q4=$hqcon->sql_query("select concat('APPLICATION ',sku.id) as id, sku_id as sku_id, artno, product_matrix, c1.department_id
from sku_apply_items 
left join sku on sku_apply_items.sku_id = sku.id
left join category c1 on c1.id=sku.category_id 
left join brand on brand.id=sku.brand_id
where is_new and (sku.status <> 4 and sku.active=0) and vendor_id = $vendor_id and sku_id <> $sku_id and (artno = " . ms($code) . " or product_matrix like ".ms('%:"'.$code.'";%').") group by sku_apply_items.description");

	$r4 = $hqcon->sql_fetchrow($q4);

/*
	$q3=$con->sql_query("select concat('SKU ',sku_id) as id, sku_id as sku_id, artno, c1.department_id
from sku_items 
left join sku on sku_id = sku.id
left join category c1 on c1.id=sku.category_id
left join brand on brand.id=sku.brand_id
where sku_id <> $sku_id and vendor_id = $vendor_id and c1.department_id=$dept_id $where and artno = ".ms($code));

	$r3 = $con->sql_fetchrowset($q3);
		
	$q4=$hqcon->sql_query("select concat('APPLICATION ',sku.id) as id, sku_id as sku_id, artno, product_matrix, c1.department_id
from sku_apply_items 
left join sku on sku_apply_items.sku_id = sku.id
left join category c1 on c1.id=sku.category_id 
left join brand on brand.id=sku.brand_id
where is_new and (sku.status <> 4 and sku.active=0) and vendor_id = $vendor_id and sku_id <> $sku_id and c1.department_id=$dept_id and (artno = " . ms($code) . " or product_matrix like ".ms('%:"'.$code.'";%').") $where  group by sku_apply_items.description");
	$r4 = $hqcon->sql_fetchrow($q4);	
*/
	if($r4){
		$r3 = array_merge($r3, $r4);	
	}
	return $r3;
}

function find_multics_code($lines, $str)
{
	foreach ($lines as $line)
	{
	    $m = explode(",", $line);
	    if (trim($m[0]) == trim($str)) return true;
	}
	return false;
}
?>
