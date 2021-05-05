<?php

/*
REVISION HISTORY
================

5/31/2010 4:13:44 PM Alex
- Add $config['reset_date_limit']

6/27/2011 10:12:35 AM Andy
- Make all branch default sort by sequence, code.

11/23/2011 2:42:43 PM Justin
- Amended to have multiple report printing features.

4/3/2012 3:37:18 PM Andy
- Change function init_selection to init_so_selection().
- Add show relationship between PO and SO.

4/20/2012 6:08:10 PM Alex
- add add packing uom code => load_order_items()

3/4/2013 2:03 PM Andy
- Add get receipt list when load the sales order which has been exported to POS.

4/11/2013 11:15 AM Andy
- Add can print sales order checklist if got config "sales_order_print_checklist_template".

4/16/2013 3:35 PM Andy
- Change to check maintenance version 196.

5/14/2013 11:14 AM Andy
- Add selling type for sales order.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/29/2013 5:35 PM Andy
- Enhance to load more_info when select approval history.
- Enhance to check approval settings when confirm/approve.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

4/22/2014 5:06 PM Justin
- Enhanced to have filter on mprice type by user.

7/17/2014 10:46 AM Fithri
- when select debtor, automatically select mprice if the debtor's mprice is set & user cannot change it

2/9/2015 3:37 PM Andy
- GST Enhancements.

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

04/07/2016 14:00 Edwin
- Bug fixed on Sales Order item arrange in reverse order after sales order item is added and saved.

10/7/2016 11:46 AM Andy
- Fixed stucked approval redirect to wrong php.

4/3/2017 10:56 AM Qiu Ying
- Enhanced to add Sales Order Print Quotation
- Add default template to represent ARMS Sales Order Quotation

4/7/2017 9:53 AM Andy
- Fixed print quotation to use sales order config by default, then override it using print quotation config.

4/10/2017 16:02 Qiu Ying
- Bug fixed on print quotation item not follow default value when config not set

3/30/2018 4:13 PM HockLee
- create new function recalculate_sales_order() for Sales Order by upload csv

8/15/2018 2:40 PM Andy
- Increase maintenance version checking to 356.

8/27/2018 4:00PM HockLee
- Bugs fixed: recalculate_sales_order().

9/3/2018 4:00PM HockLee
- Fixed recalculate_sales_order() error.

10/10/2019 10:12 AM William
- Add new printing "Picking List" for sales order.

11/29/2019 1:43 PM William
- Enhanced to display sku item photo to sales order module.

7/14/2020 4:22 PM William
- Enhanced to change checkbox print document to use radio button.

2/1/2021 10:35 AM William
- Added new function "get_reserve_qty" for reserve_qty.

2/4/2021 5:13 PM Andy
- Increase maintenance version checking to 487.

3/5/2021 11:00 AM Sin Rou
- Enhance to config and display out RSP and RSP Discount.
- Modify the sql by adding selection to RSP and RSP Discount.

*/
$maintenance->check(487);
$maintenance->check(487, true);

$mprice_type_list = array();
if($config['sku_multiple_selling_price']){
	foreach($config['sku_multiple_selling_price'] as $mprice_type){
		$mprice_type_list[] = $mprice_type;
	}
}
$smarty->assign('mprice_type_list', $mprice_type_list);

function load_order_header($branch_id, $id, $redirect_if_not_found = false){
	global $con,$smarty, $LANG, $sessioninfo, $config;
	
	$con->sql_query("select so.*,user.u as username
	from sales_order so
	left join user on user.id=so.user_id
	where so.branch_id=".mi($branch_id)." and so.id=".mi($id));
	$form = $con->sql_fetchassoc();
	if(!$form){
		if($redirect_if_not_found)  js_redirect($LANG['INVALID_SO_ID'], "$_SERVER[PHP_SELF]");
		else return false;
	}
	$con->sql_freeresult();
	
	if ($form['approval_history_id']>0){
		$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u, i.more_info
from branch_approval_history_items i
left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
left join user on i.user_id=user.id
where h.ref_table='sales_order' and i.branch_id=$branch_id and i.approval_history_id=$form[approval_history_id]
order by i.timestamp");
		$approval_history = array();
		while($r = $con->sql_fetchassoc($q2)){
			$r['more_info'] = unserialize($r['more_info']);
			$approval_history[] = $r;
		}
		$con->sql_freeresult($q2);
		$smarty->assign("approval_history", $approval_history);
	}
	
	if($form['delivered']){
		// get DO - unsaved, waiting approve, approved and rejected
		$con->sql_query("select do.id,do.branch_id,do.do_no,branch.report_prefix
		from do
		left join branch on branch.id=do.branch_id
		where do.ref_tbl='sales_order' and do.ref_no=".ms($form['order_no'])." and do.active=1 and do.status in (0,1,2)");
		$smarty->assign("do_list", $con->sql_fetchrowset());
		$con->sql_freeresult();
	}elseif($form['exported_to_pos']){
		if($form['active'] && $form['status'] == 1 && $form['approved']){
			// get sales order exported to pos
			$form['receipt_details'] = get_sales_order_receipt_list($branch_id, $id);
		}	
	}
	
	$params = array();
	$params['user_id'] = $sessioninfo['id'];
	$params['id'] = $form['approval_history_id'];
	$params['branch_id'] = $branch_id;
	$params['check_is_approval'] = true;
	$is_approval = check_is_last_approval_by_id($params, $con);
	if($is_approval)  $form['is_approval'] = 1;

	// check user's mprice
	sku_multiple_selling_price_handler($form);
	
	if($form['active'] == 1 && ($form['status']==0 || $form['status'] ==2) && !$form['approved']){
		// check whether this do is under gst
		if($config['enable_gst']){
			$params = array();
			$params['date'] = $form['order_date'];
			$params['branch_id'] = $branch_id;
			$form['is_under_gst'] = check_gst_status($params);
			
			if($form['is_under_gst']){
				construct_gst_list();
			}
		}else{
			$form['is_under_gst'] = 0;
		}
	}
	
	//print_r($form);
	return $form;
}

function load_order_items($branch_id, $id, $load_from_tmp = false){
    global $con,$smarty, $LANG, $sessioninfo, $gst_list,$config, $appCore;

	// generate gst list
	//if(!$gst_list)	construct_gst_list();
	construct_gst_list();
	
    $filter = array();
    if($load_from_tmp){
		$tbl = 'tmp_sales_order_items';
		$filter[] = "oi.user_id=$sessioninfo[id]";
	}else   $tbl = 'sales_order_items';

	$filter[] = "oi.branch_id=".mi($branch_id)." and sales_order_id=".mi($id);
	$filter = "where ".join(' and ', $filter);
	
	$sql = "select oi.*,si.sku_item_code, si.link_code, si.location, ifnull(si.artno,si.mcode) as artno_mcode,si.additional_description, si.description as sku_description,uom.code as uom_code,uom.fraction as uom_fraction, si.doc_allow_decimal, puom.code as packing_uom_code,
			si.artno, si.mcode, si.use_rsp, if(sip.price is null, si.rsp_discount, sip.rsp_discount) as rsp_discount,si.rsp_price
			from $tbl oi
			left join sku_items si on si.id=oi.sku_item_id
			left join sku_items_price sip on sip.sku_item_id =oi.sku_item_id and sip.branch_id=oi.branch_id
			left join uom on uom.id=oi.uom_id
			left join uom puom on puom.id=si.packing_uom_id
			$filter order by oi.id";
		
	$q1 = $con->sql_query($sql);
	$items = array();
	
	while($r = $con->sql_fetchassoc($q1)){
		if($item_index+1>=$item_per_page){
			$page++;
			$item_index = -1;
		}
				
		if(!$load_from_tmp){
			$po_info = get_sales_order_items_po_info($r['branch_id'], $r['id']);
			
			if(is_array($po_info) && $po_info)	$r = array_merge($r, $po_info);
		}
		
		// gst
		if($r['gst_id'] > 0){
			check_and_extend_gst_list($r);
		}
		if($config['sales_order_show_photo']){
			$sku_item_id = mi($r['sku_item_id']);
			$sku_item_photo = $appCore->skuManager->getSKUItemPhotos($sku_item_id);
			if(count($sku_item_photo['photo_list'])> 0){
				$r['photo'] = $sku_item_photo['photo_list'][0];
			}
		}
		
		$r['reserve_qty'] = get_reserve_qty($branch_id, $id, $r['sku_item_id']);
		$items[] = $r;
	}
	$con->sql_freeresult($q1);
	
	return $items;
}

function sales_order_approval($order_id, $branch_id, $status, $auto_approve=false, $redirect=true){
    global $con, $sessioninfo, $smarty, $config, $approval_on_behalf;

 	$form=$_REQUEST;
 	$approved=0;
	$approval_status = array(1 => "Approved", 2 => "Rejected", 3 => "KIV (Pending)", 4 => "Terminated");
 	if($auto_approve) $form=load_order_header($branch_id, $order_id);

	$aid=$form['approval_history_id'];
	$approvals=$form['approvals'];

	if($status==1){
		$comment="Approved";
		$params = array();
		$params['approve'] = 1;
		$params['user_id'] = $sessioninfo['id'];
		$params['id'] = $aid;
		$params['branch_id'] = $branch_id;
		$params['update_approval_flow'] = true;
		if($auto_approve) $params['auto_approve'] = true;
    	$is_last = check_is_last_approval_by_id($params, $con);	
    	if($is_last)  $approved = 1;
	}
	else{
	  	$comment= trim($form['comment']);
    	$con->sql_query("update branch_approval_history set status=$status, approvals = ".ms($approvals)." where id = $aid and branch_id = $branch_id") or die(mysql_error());
  	}

	if ($approval_on_behalf) {
		$comment .= " (by ".$approval_on_behalf['on_behalf_by_u']." on behalf of ".$approval_on_behalf['on_behalf_of_u'].")";
	}

	$upd = array();
	$upd['approval_history_id'] = $aid;
	$upd['branch_id'] = $branch_id;
	$upd['user_id'] = $sessioninfo['id'];
	$upd['status'] = $status;
	$upd['log'] = $comment;
	
	if($_REQUEST['direct_approve_due_to_less_then_min_doc_amt'])	$upd['more_info']['direct_approve_due_to_less_then_min_doc_amt'] = 1;
	if($upd['more_info'])	$upd['more_info'] = serialize($upd['more_info']);
	
	$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
	
	$con->sql_query("update sales_order set status=".mi($status).", approved=".mi($approved)." where id=$order_id and branch_id=$branch_id");
	//send_pm_to_user($order_id, $branch_id, $aid, $status);
	$to = get_pm_recipient_list2($order_id,$aid,$status,'approval',$branch_id,'sales_order');
	$status_str = ($is_last || $status != 1) ? $approval_status[$status] : '';
	send_pm2($to, "Sales Order Approval (ID#$order_id) $status_str", "sales_order.php?a=view&id=$order_id&branch_id=$branch_id", array('module_name'=>'sales_order'));

	if ($approved)
		$status_msg="Fully Approved";
	elseif ($status==1)
		$status_msg="Approved";
	elseif ($status==2)
		$status_msg="Rejected";
	elseif ($status==5)
		$status_msg="Cancelled/Terminated";
	else
	    die("WTF?");

	log_br($sessioninfo['id'], 'SALES_ORDER', $order_id, "Sales Order $status_msg by $sessioninfo[u] (ID#$order_id)");
	if($redirect){
        if($auto_approve)
			header("Location: /sales_order.php?t=approve&save_id=$order_id");
		else{
		
			if ($approval_on_behalf) {
				header("Location: /stucked_document_approvals.php?m=sales_order");
				exit;
			}
		
		    header("Location: /sales_order_approval.php?t=$form[a]&id=$order_id");
		}
		exit;
	}
}
/*

function send_pm_to_user($order_id, $branch_id, $aid, $status){
	global $con, $sessioninfo, $smarty, $approval_status;
	// get the PM list
	$con->sql_query("select notify_users
from branch_approval_history where id=$aid and branch_id = $branch_id");
	$r = $con->sql_fetchrow();

	$recipients = $r[0];
	$recipients = str_replace("|$sessioninfo[id]|", "|", $recipients);
	$to = preg_split("/\|/", $recipients);

	// send pm
	send_pm($to, "Sales Order Approval (ID#$order_id) $approval_status[$status]", "sales_order.php?a=view&id=$order_id&branch_id=$branch_id");
}
*/

function reset_order($order_id, $branch_id){
    global $con,$sessioninfo,$config, $LANG, $smarty;
	$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;

	if($sessioninfo['level']<$required_level){
        js_redirect(sprintf('Forbidden', 'SALES_ORDER', BRANCH_CODE), "/sales_order.php");
	}

	$form = load_order_header($branch_id, $order_id);

	if(!$form['approved']){
		header("Location: /sales_order.php?save_id=$order_id&err_msg=Sales Order Already Reset");
		exit;
	}

	//add reset config
	$check_date = strtotime($form['order_date']);

	if (isset($config['reset_date_limit']) && $config['reset_date_limit'] >= 0){
		$reset_limit = $config['reset_date_limit'];
		$reset_limit = strtotime("-1 day",strtotime("-$reset_limit day" , strtotime("now")));


		if ($check_date<$reset_limit){
  	   		$errm['top'][] = $LANG['SO_DATE_RESET_LIMIT'];
		}

	}
	
	if($errm){
		$smarty->assign("errm", $errm);
		return true;
	}
	
	if($form['delivered']){
	    // check do_qty
	    $con->sql_query("select sum(do_qty) from sales_order_items where branch_id=$branch_id and sales_order_id=$order_id");
	    if($con->sql_fetchfield(0)>0){
            js_redirect($LANG['SO_ALREADY_USED_IN_DO'], "$_SERVER[PHP_SELF]?a=view&branch_id=$branch_id&id=$order_id");
		}
		$con->sql_freeresult();
	}
	
	// check got send to PO
	if($form['approved'] && $form['po_used']){
		$con->sql_query("select poi.id
		from po_items poi
		left join po on po.branch_id=poi.branch_id and po.id=poi.po_id
		join sales_order_items soi on soi.branch_id=poi.so_branch_id and soi.id=poi.so_item_id
		join sales_order so on so.branch_id=soi.branch_id and so.id=soi.sales_order_id
		where po.active=1 and so.branch_id=$branch_id and so.id=$order_id limit 1");
		if($con->sql_fetchfield(0)>0){
            js_redirect($LANG['SO_ALREADY_USED_IN_PO'], "$_SERVER[PHP_SELF]?a=view&branch_id=$branch_id&id=$order_id");
		}
		$con->sql_freeresult();
	}
	
	$aid=$form['approval_history_id'];
	$approvals=$form['approvals'];
	$status = 0;

	$upd = array();
	$upd['approval_history_id'] = $aid;
	$upd['branch_id'] = $branch_id;
	$upd['user_id'] = $sessioninfo['id'];
	$upd['status'] = $status;
	$upd['log'] = $_REQUEST['reason'];

	$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
	$con->sql_query("update branch_approval_history set status=$status,approved_by='' where id = $aid and branch_id = $branch_id") or die(mysql_error());
	
	$upd = array();
	$upd['status'] = $status;
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	$upd['approved'] = 0;
	$upd['active'] = 1;
	$upd['delivered'] = 0;
	$upd['po_used'] = 0;
	$upd['po_ref'] = '';
	$upd['can_generate_po'] = 1;
	
	$con->sql_query("update sales_order set ".mysql_update_by_field($upd)." where id=$order_id and branch_id=$branch_id");

	log_br($sessioninfo['id'], 'SALES_ORDER', $order_id, sprintf("Sales Order Reset ($form[order_no])",$order_id));

	header("Location: /sales_order.php?t=reset&save_id=$order_id");
}

function init_so_selection(){
	global $con, $sessioninfo, $smarty, $config;

	// debtor
	$con->sql_query("select * from debtor where active=1 order by code",false,false);
	while($r = $con->sql_fetchrow()){
		$debtor[$r['id']] = $r;
	}
	$smarty->assign('debtor', $debtor);
	$con->sql_freeresult();

	// uom
	$con->sql_query("select * from uom where active=1 order by code");
	while($r = $con->sql_fetchrow()){
		$uom[$r['id']] = $r;
	}
	$smarty->assign('uom', $uom);
	$con->sql_freeresult();

	// branch
	$con->sql_query("select * from branch order by sequence,code");
	while($r = $con->sql_fetchrow()){
		$branches[$r['id']] = $r;
	}
	$smarty->assign('branches', $branches);
	$con->sql_freeresult();
}

function print_order($branch_id, $id){
    global $con, $sessioninfo, $smarty, $config, $LANG;
    
    $form = load_order_header($branch_id, $id, true);
    $items = load_order_items($branch_id, $id);

	$con->sql_query("select * from branch where id =".mi($branch_id));
	$smarty->assign("from_branch", $con->sql_fetchrow());
    
    $smarty->assign('form', $form);
			
	if($_REQUEST['print_type'] == 'sales_order' && ($_REQUEST['title_print'] == 'sales_order' || $_REQUEST['title_print'] == 'other' || $_REQUEST['title_print']=='proforma_invoice')){
		$item_per_page = $config['sales_order_print_item_per_page']>5 ? $config['sales_order_print_item_per_page'] : 25;
		$item_per_lastpage = $config['sales_order_print_item_per_last_page']>0 ? $config['sales_order_print_item_per_last_page'] : $item_per_page-5;
		$totalpage = 1 + ceil((count($items)-$item_per_lastpage)/$item_per_page);
		// start print sales order
		$item_index = -1;
		$item_no = -1;
		$page = 1;
		$sales_order_template = 'sales_order.print.tpl';
		
		if($_REQUEST['title_print'] == 'other'){
			$title_print = $_REQUEST['title_other_print'];
		}elseif($_REQUEST['title_print'] == 'sales_order'){
			$title_print = "Sales Order";
		}elseif($_REQUEST['title_print']=='proforma_invoice'){
			$title_print = "Proforma Invoice";
			if($config['sales_order_proforma_inv_alt_print_template'])  $sales_order_template = $config['sales_order_proforma_inv_alt_print_template'];
			$smarty->assign("print_proforma_invoice",1);
		}
		
		$page_item_list = array();
		$page_item_info = array();
			
		foreach($items as $r){	// loop for each item
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
					$desc_row['sku_description'] = $desc;
					
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
	
		foreach($page_item_list as $page => $item_list){
			$this_page_num = ($page < $totalpage) ? $item_per_page : $item_per_lastpage;
			$smarty->assign("PAGE_SIZE", $this_page_num);
			$smarty->assign("is_lastpage", ($page >= $totalpage));
			$smarty->assign("page", "Page $page of $totalpage");
			$smarty->assign("start_counter",$item_list[0]['item_no']);
			$smarty->assign("items", $item_list);
			$smarty->assign("page_item_info", $page_item_info[$page]);
			$smarty->assign("title_print", $title_print);
			$smarty->display($sales_order_template);
			$smarty->assign("skip_header",1);
			
		}
		unset($page_item_list,$page_item_info);
	}
	
	if($_REQUEST['print_type'] == 'sales_order' && $_REQUEST['title_print']=='quotation'){
		if(!$config['sales_order_quotation_alt_print_template']){
			$quotation_templates = "sales_order.print.tpl";
		}else{
			$quotation_templates = $config['sales_order_quotation_alt_print_template'];
		}
		// use sales order config by default
		$item_per_page = $config['sales_order_print_quotation_item_per_page']>5 ? $config['sales_order_print_quotation_item_per_page'] : 25;
		$item_per_lastpage = $config['sales_order_print_quotation_item_per_last_page']>0 ? $config['sales_order_print_quotation_item_per_last_page'] : $item_per_page-5;
		
		// override it using quotation config
		if($config['sales_order_print_quotation_item_per_page']>5){
			$item_per_page = $config['sales_order_print_quotation_item_per_page'];
		}
		if($config['sales_order_print_quotation_item_per_last_page']>0){
			$item_per_lastpage = $config['sales_order_print_quotation_item_per_last_page'];
		}	
		
		$totalpage = 1 + ceil((count($items)-$item_per_lastpage)/$item_per_page);
		// start print sales order
		$item_index = -1;
		$item_no = -1;
		$page = 1;
		
		$page_item_list = array();
		$page_item_info = array();
			
		foreach($items as $r){	// loop for each item
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
					$desc_row['sku_description'] = $desc;
					
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
		
		foreach($page_item_list as $page => $item_list){
			$this_page_num = ($page < $totalpage) ? $item_per_page : $item_per_lastpage;
			$smarty->assign("PAGE_SIZE", $this_page_num);
			$smarty->assign("is_lastpage", ($page >= $totalpage));
			$smarty->assign("page", "Page $page of $totalpage");
			$smarty->assign("start_counter",$item_list[0]['item_no']);
			$smarty->assign("items", $item_list);
			$smarty->assign("print_quotation",1);
			$smarty->assign("page_item_info", $page_item_info[$page]);
			$smarty->display($quotation_templates);
			$smarty->assign("skip_header",1);
			
		}
		unset($page_item_list,$page_item_info);
	}
	
	if($_REQUEST['print_type'] == 'picking_list'){
		if(!$config['sales_order_picking_list_alt_print_template']){
			$picking_list_templates = "sales_order.print_picking_list.tpl";
		}else{
			$picking_list_templates = $config['sales_order_picking_list_alt_print_template'];
		}
		
		$item_per_page = $config['sales_order_print_picking_list_item_per_page']>5 ? $config['sales_order_print_picking_list_item_per_page'] : 25;
		$item_per_lastpage = $config['sales_order_print_picking_list_item_per_last_page']>0 ? $config['sales_order_print_picking_list_item_per_last_page'] : $item_per_page-5;
		$totalpage = 1 + ceil((count($items)-$item_per_lastpage)/$item_per_page);
		// start print Picking List
		$item_index = -1;
		$item_no = -1;
		$page = 1;
		
		$page_item_list = array();
		$page_item_info = array();
			
		foreach($items as $r){	// loop for each item
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
					$desc_row['sku_description'] = $desc;
					
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

		foreach($page_item_list as $page => $item_list){
			$this_page_num = ($page < $totalpage) ? $item_per_page : $item_per_lastpage;
			$smarty->assign("PAGE_SIZE", $this_page_num);
			$smarty->assign("is_lastpage", ($page >= $totalpage));
			$smarty->assign("page", "Page $page of $totalpage");
			$smarty->assign("start_counter",$item_list[0]['item_no']);
			$smarty->assign("items", $item_list);
			$smarty->assign("page_item_info", $page_item_info[$page]);
			$smarty->assign("extra_empty_row", $this_page_num-count($item_list));
			$smarty->display($picking_list_templates);  
			$smarty->assign("skip_header",1);
		}
		unset($page_item_list,$page_item_info);
	}
	if($_REQUEST['print_type'] == 'checklist' && $config['sales_order_print_checklist_template'] && !$form['approved']){
		$smarty->display($config['sales_order_print_checklist_template']);
	}
}

function get_sales_order_items_po_info($bid, $item_id){
	global $con;
	
	if(!$bid || !$item_id)	return array();
	$q_poi = $con->sql_query("select poi.branch_id as pbid, poi.po_id, pobranch.code as po_bcode,poi.id as poi_id,(((poi.qty*poi.order_uom_fraction)+poi.qty_loose)+((poi.foc*poi.order_uom_fraction)+poi.foc_loose)) as total_purchase_qty, poi.order_uom_fraction,poi.selling_uom_fraction,(poi.order_price/poi.order_uom_fraction) as porder_price, 
	(poi.selling_price/poi.selling_uom_fraction) as pselling_price, po.po_no,po.po_branch_id
		from po_items poi
		left join po on po.branch_id=poi.branch_id and po.id=poi.po_id
		left join branch pobranch on pobranch.id=poi.branch_id
		where po.active=1 and poi.so_branch_id=".mi($bid)." and poi.so_item_id=".mi($item_id)."
		order by po.id desc limit 1");
	$po_info = $con->sql_fetchassoc($q_poi);
	$con->sql_freeresult($q_poi);
	
	return $po_info;
}

function get_sales_order_receipt_list($bid, $id){
	global $con;
	
	$receipt_sql = "select id as pos_id,branch_id,counter_id,cashier_id,date,receipt_no from pos where sales_order_id = ".mi($id)." and sales_order_branch_id = ".mi($bid);
	$q1 = $con->sql_query($receipt_sql);
	$receipt_details = array();
	while($r = $con->sql_fetchassoc($q1)){
		$receipt_details[] = $r;
	}
	$con->sql_freeresult($q1);
	
	return $receipt_details;
}

function sku_multiple_selling_price_handler($form=array()){
	global $mprice_type_list, $con, $smarty, $sessioninfo;
	
	$q1 = $con->sql_query("select allow_mprice from user where id = ".mi($sessioninfo['id']));
	$user_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	$debtor_mprice_type = '';
	if ($form['id'] && $form['branch_id']) {
		$q1 = $con->sql_query("select debtor.debtor_mprice_type from sales_order so left join debtor on so.debtor_id = debtor.id where so.id = ".mi($form['id'])." and so.branch_id = ".mi($form['branch_id'])." limit 1");
		$r1 = $con->sql_fetchassoc($q1);
		if ($r1) $debtor_mprice_type = $r1['debtor_mprice_type'];
		$con->sql_freeresult($q1);
	}
	
	$allow_mprice = unserialize($user_info['allow_mprice']);
	
	foreach ($mprice_type_list as $row=>$data){
		if(preg_match("/member/", $data) || $form['selling_type'] == $data) continue;
		if ($data == $debtor_mprice_type) continue;

		$mprice_matched = 0;
		if($allow_mprice){
			foreach($allow_mprice as $user_mprice=>$dummy){
				if(preg_match("/$user_mprice/", $data)){
					$mprice_matched = 1;
					break;
				}
			}
		}
		
		if(!$mprice_matched){
			unset($mprice_type_list[$row]);
		}
		if ($allow_mprice) {
			if (array_key_exists('not_allow',$allow_mprice)) {
				unset($mprice_type_list[$row]);
			}
		}
	}
	
	$disallowed_mprice = '';
	if ($debtor_mprice_type && $allow_mprice) {
		if (array_key_exists('not_allow',$allow_mprice) || !array_key_exists($debtor_mprice_type,$allow_mprice)) {
			$disallowed_mprice = $debtor_mprice_type;
		}
	}
	if (!$allow_mprice) {
		$disallowed_mprice = $debtor_mprice_type;
	}
	if (preg_match("/member/", $disallowed_mprice)) $disallowed_mprice = '';
	
	$smarty->assign('disallowed_mprice', $disallowed_mprice);
	
	$smarty->assign('mprice_type_list', $mprice_type_list);
}

// recalculate_sales_item
function recalculate_sales_order($branch_id, $so_id){
	global $con, $config;

	$query = $con->sql_query("select soi.id as soi_id,soi.*,u.* 
		from sales_order_items soi 
		left join uom u on u.id = soi.uom_id 
		where soi.sales_order_id = $so_id and branch_id = $branch_id and u.active = 1");

	while($so = $con->sql_fetchassoc($query)){
		$id = $so['soi_id'];
		$cost_price = $so['cost_price'];
		$selling_price = $so['selling_price'];;
		$ctn = $so['ctn'];
		$pcs = $so['pcs'];
		$gst_rate = $so['gst_rate'];
		$fraction = $so['fraction'];

		$cost_price = $cost_price * $fraction;
		$sell_price = $selling_price * $fraction;

		$total_qty = round(($ctn*$fraction) + $pcs);
		$amt = $total_qty * $selling_price;

		$gross_amt = $amt;

		if($config['enable_gst']){
			$gst_amt = $gross_amt * $gst_rate / 100;
			$gst_amt = round($gst_amt, 2);

			$amt = $gross_amt + $gst_amt;
		}

		if($fraction > 1){
			$qty_ctn = $ctn;
			$qty_pcs = 0;
		}else{
			$qty_ctn = 0;
			$qty_pcs = $ctn;
		}

		$upd['cost_price'] = $cost_price;
		$upd['selling_price'] = $sell_price;
		$upd['ctn'] = $qty_ctn;
		$upd['pcs'] = $qty_pcs;
		$upd['line_gross_amt'] = $gross_amt;
		$upd['line_gst_amt'] = $gst_amt;
		$upd['line_amt'] = $amt;
		$upd['line_gross_amt2'] = $gross_amt;
		$upd['line_gst_amt2'] = $gst_amt;
		$upd['line_amt2'] = $amt;

		$con->sql_query("update sales_order_items set ".mysql_update_by_field($upd)." where id = $id and sales_order_id = $so_id and branch_id = $branch_id");
	}
	$con->sql_freeresult($query);

	unset($ctn, $pcs, $fraction, $total_qty, $gross_amt, $gst_amt, $amt);

	$q1 = $con->sql_query("select ctn, pcs, line_gross_amt, line_gst_amt, line_amt, fraction 
		from sales_order_items soi 
		left join uom u on u.id = soi.uom_id 
		where soi.sales_order_id = $so_id and soi.branch_id = $branch_id and u.active = 1");
	
	while($so_item = $con->sql_fetchassoc($q1)){
		$ctn += $so_item['ctn'];
		$pcs += $so_item['pcs'];
		$fraction = $so_item['fraction'];
		$total_qty += round(($ctn*$fraction) + $pcs);
		$gross_amt += $so_item['line_gross_amt'];
		$gst_amt += $so_item['line_gst_amt'];
		$amt += $so_item['line_amt'];
	}
	$con->sql_freeresult($q1);
	
	$upd = array();
	$upd['total_ctn'] = $ctn;
	$upd['total_pcs'] = $pcs;
	$upd['total_amount'] = $amt;
	$upd['total_qty'] = $total_qty;
	$upd['total_gross_amt'] = $gross_amt;
	$upd['total_gst_amt'] = $gst_amt;
	
	$con->sql_query("update sales_order 
		set ".mysql_update_by_field($upd)."
		where id = $so_id and branch_id = $branch_id");	

	$so_info = array("ctn"=>$ctn, "pcs"=>$pcs, "amt"=>$amt, "qty"=>$total_qty);
	return $so_info;
}

//get Reserve Qty
function get_reserve_qty($branch_id, $sales_order_id, $sku_item_id){
	global $con, $config;
	
	$reserve_qty = 0;
	
	$q1=$con->sql_query("select sum(soi.pcs + (uom.fraction * soi.ctn)) as  reserve_qty
	from sales_order_items soi 
	left join sales_order so on so.id = soi.sales_order_id and soi.branch_id = so.branch_id
	left join uom on uom.id = soi.uom_id
	where so.approved = 1 and so.delivered = 0 and so.exported_to_pos = 0 and so.active = 1 
	and so.status =1 and soi.sku_item_id = ".mi($sku_item_id)." and so.branch_id=".mi($branch_id)."
	and so.id <> ".mi($sales_order_id)." group by sku_item_id");
	$r1=$con->sql_fetchassoc($q1);
	$reserve_qty = mf($r1['reserve_qty']);
	$con->sql_freeresult($q1);
	
	return $reserve_qty;
}
?>
