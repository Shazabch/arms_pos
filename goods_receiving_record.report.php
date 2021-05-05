<?php
/*
REVISION HISTORY
================
11/21/2007 11:04:27 AM gary
- split out by branch if the branch selection is ALL.

11/22/2007 2:47:51 PM gary
- from -1 month from now.

1/24/2008 3:54:48 PM gary
- get grr owner and all grr_item doc_no.

7/24/2012 11:30 AM Justin
- Added to pickup Account ID and Code.

4/14/2015 10:17 AM Justin
- Enhanced to have GST information.

7/22/2015 4:31 PM Joo Chia
- Assign grr_items GST info into tpl to show.
- Add in export to excel function.

7/23/2015 10:35 AM Andy
- Fix wrong data when select all branch.

12/10/2015 4:12 AM DingRen
- Enhance to include filter with grn status

12/18/2015 5:17 PM DingRen
- fix query error

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

3/31/2016 3:19 PM Andy
- Fix to only load active GRN.

04/27/2016 10:30 Edwin
- Added on sorting feature

10/20/2016 11:42 AM Qiu Ying
- Enhanced to filter by invoice date and show with invoice only

4/24/2018 10:02 AM Justin
- Enhanced to show foreign currency.

5/31/2018 5:31 PM Justin
- Bug fixed on open tag for PHP was missing.
- Bug fixed on using array() instead of "" when the data to be stored as array.

5/15/2019 4:48PM William
- Pickup report_prefix for enhance "GRR".
*/
include("include/common.php");
include("include/excelwriter.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRR_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRR_REPORT', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
if($config['use_grn_future'])
	include("goods_receiving_note2.include.php");
else
	include("goods_receiving_note1.include.php");

$branch_id = $sessioninfo['branch_id'];

$smarty->assign("PAGE_TITLE", "GRR Report");

init_selection();
// get vendor
$con->sql_query("select distinct vendor.id, vendor.description from grr left join vendor on grr.vendor_id = vendor.id ".(BRANCH_CODE == 'HQ' ? "" : " where grr.branch_id = $sessioninfo[branch_id]")." order by description");
$smarty->assign("vendor", $con->sql_fetchrowset());

//sort field and sort order
$sort_field_list = array("grr.id"=>'GRR No', "vendor_desc"=>'Vendor', "grr.rcv_date"=>'Received Date');
$sort_order_list = array("asc"=>'Ascending', "desc"=>'Descending');
$smarty->assign("sort_field_list", $sort_field_list);
$smarty->assign("sort_order_list", $sort_order_list);

// set default date
if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));

if(isset($_REQUEST['output_excel'])){
	$smarty->assign('is_export_excel', 1);
	export_excel();
}else
	$smarty->display("goods_receiving_record.report.tpl");

exit;

function show_report(){
	global $con, $sessioninfo, $smarty, $sort_field_list, $sort_order_list;
	
	$pf = array();
	$total_gst_list = $total_non_gst = $total_gst_error = array();

	if(BRANCH_CODE == 'HQ'){
		if(!isset($_REQUEST['branch_id']))	$_REQUEST['branch_id'] = $sessioninfo['branch_id'];
		
		if ($_REQUEST['branch_id']>0){
			$pf[] = "grr.branch_id = " .mi($_REQUEST['branch_id']);
		}
	}else{
		$pf[] = "grr.branch_id = $sessioninfo[branch_id]";
	}
	
	if ($_REQUEST['date_type'] == "inv_date"){
		$pf[] = "grr_i.doc_date >= ".ms($_REQUEST['from']);
		$pf[] = "grr_i.doc_date <= ".ms($_REQUEST['to']);
		$pf[] = "grr_i.type = 'INVOICE'";
	}else{
		$pf[] = "grr.rcv_date >= ".ms($_REQUEST['from']);
		$pf[] = "grr.rcv_date <= ".ms($_REQUEST['to']);
		
		if ($_REQUEST["inv_only"]){
			$pf[] = "grr_i.type = 'INVOICE'";
		}
	}
	
	$pf[] = "grr.active = 1";
	
	if ($_REQUEST['department_id']) $pf[] = "grr.department_id = ".mi($_REQUEST['department_id']);
	if ($_REQUEST['vendor_id']) $pf[] = "grr.vendor_id = ".mi($_REQUEST['vendor_id']);
	//if ($_REQUEST['status']!=-1) $pf[] = "grr.status = ".mi($_REQUEST['status']);

	$where = join(" and ", $pf);
	
	$sort_field = $_REQUEST['sort_field'];
	$sort_order = $_REQUEST['sort_order'];
	$sort_by = "grr.id";
	if($sort_field && isset($sort_field_list[$sort_field])){
		$sort_by = $sort_field . " " . (($sort_order && isset($sort_order_list[$sort_order])) ? $sort_order : "");
		if($sort_field != 'grr.id')	$sort_by .= ", grr.id";
	}

	$is_under_gst = $have_fc = 0;
	$q1 = $con->sql_query("select grr.*,b1.report_prefix, vendor.description as vendor_desc, category.description as department, b1.code as branch, 
					user.u as user, group_concat(grr_i.doc_no order by 1 separator ', ') as all_doc_no, 
					vendor.code as vendor_code, if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id, grr_i.doc_date, grr_i.doc_no, grr_i.type,
					group_concat(
					  distinct concat(grr_i.doc_date,' (',grr_i.doc_no, ')') 
					  order by 1
					  separator ',<br/>'
					) as all_inv_date
					from grr 
					left join grr_items grr_i on grr_i.grr_id=grr.id and grr_i.branch_id=grr.branch_id 
					left join vendor on grr.vendor_id = vendor.id 
					left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = grr.branch_id
					left join category on grr.department_id = category.id
					left join branch b1 on b1.id=grr.branch_id
					left join user on user.id=grr.user_id
					where $where 
					group by grr.branch_id,grr.id 
					order by grr.branch_id, $sort_by");
		
	while ($grr = $con->sql_fetchassoc($q1)){
		$sql3="select * from grn where grr_id=".mi($grr['id'])." and branch_id=".mi($grr['branch_id'])." and active=1";
		$q3=$con->sql_query($sql3);
		$grn = $con->sql_fetchrow($q3);
		$con->sql_freeresult($q3);

		if($_REQUEST['status']){
			if($_REQUEST['status']==1 && $grn) continue;
			elseif($_REQUEST['status']==2 && (!$grn || ($grn['active'] && $grn['status'] && $grn['approved']))) continue;
			elseif($_REQUEST['status']==3 && !($grn['active'] && $grn['status'] && $grn['approved'])) continue;
		}

		$form['vendor']=$grr['vendor_desc'];
		$form['branch']=$grr['branch'];
		if($grr['is_under_gst']) {
			$is_under_gst = 1;

			$grr['gst_detail'] = array();

			$where2 = "where grr.id = ".mi($grr['id'])." and grr.branch_id = ".mi($grr['branch_id'])." and grr.is_under_gst";

			$sql2 = "select gi.gst_id, gi.gst_code, gi.gst_rate, sum(gi.amount) as ttl_amt, sum(gi.gst_amount) as ttl_gst_amt from grr_items gi
					left join grr on gi.grr_id = grr.id and gi.branch_id = grr.branch_id
					$where2 group by gi.gst_id";	

			$q2=$con->sql_query($sql2);
			while ($r2= $con->sql_fetchrow($q2)){

				if($r2['gst_id'] && $r2['gst_id'] != 0){

					$grr['gst_detail'][$r2['gst_id']]['gst_code'] = $r2['gst_code'];
					$grr['gst_detail'][$r2['gst_id']]['gst_rate'] = $r2['gst_rate'];
					$grr['gst_detail'][$r2['gst_id']]['ttl_amt'] = $r2['ttl_amt'];
					$grr['gst_detail'][$r2['gst_id']]['ttl_gst_amt'] = $r2['ttl_gst_amt'];

					if(!isset($total_gst_list[$grr['branch_id']][$r2['gst_id']])) {
						$total_gst_list[$grr['branch_id']][$r2['gst_id']]['g_gst_code'] = $r2['gst_code'];
						$total_gst_list[$grr['branch_id']][$r2['gst_id']]['g_gst_rate'] = $r2['gst_rate'];
					}

					$total_gst_list[$grr['branch_id']][$r2['gst_id']]['g_ttl_amt'] += $r2['ttl_amt'];
					$total_gst_list[$grr['branch_id']][$r2['gst_id']]['g_ttl_gst_amt'] += $r2['ttl_gst_amt'];

				} else {
					
					if ($r2['ttl_amt'] > 0) {
						$grr['gst_detail']['error']['gst_code'] = 'GST Error';
						$grr['gst_detail']['error']['gst_rate'] = '';
						$grr['gst_detail']['error']['ttl_amt'] += $r2['ttl_amt'];
						$grr['gst_detail']['error']['ttl_gst_amt'] += $r2['ttl_gst_amt'];
						
						$total_gst_error[$grr['branch_id']]['g_ttl_amt'] += $r2['ttl_amt'];
						$total_gst_error[$grr['branch_id']]['g_ttl_gst_amt'] += $r2['ttl_gst_amt'];
					}
					
				}
			}

			$con->sql_freeresult($q2);

		} else {
			if(!$grr['currency_rate']) $grr['currency_rate'] = 1;
			$total_non_gst[$grr['branch_id']]['grr_amt'] += $grr['grr_amount'] * $grr['currency_rate'];
		}

		if($grn){
			if($grn['active'] && $grn['status'] && $grn['approved']) $grn['completed']=1;
			$grr['grn']=$grn;
		}

		// here is to check whether grr contains foreign currency
		if($grr['currency_code']) $have_fc = true;
		
		$grr_list[]=$grr;
	}
	$con->sql_freeresult($q1);
	
	$smarty->assign("form", $form);
	$smarty->assign("grr", $grr_list);
	$smarty->assign("have_fc", $have_fc);
	$smarty->assign("total_gst_error", $total_gst_error);
	$smarty->assign("total_gst_list", $total_gst_list);
	$smarty->assign("total_non_gst", $total_non_gst);
	$smarty->assign("is_under_gst", $is_under_gst);

	//$smarty->assign("grr", $con->sql_fetchrowset());
}

function export_excel()
{
    global $smarty,$sessioninfo;

    Header('Content-Type: application/msexcel');
  	Header('Content-Disposition: attachment;filename=arms'.$file.'.xls');
  	$smarty->assign("no_header_footer",'1');
  	print ExcelWriter::GetHeader();
	$smarty->display("goods_receiving_record.report.tpl");
	print ExcelWriter::GetFooter();
 
    readfile($file);
    
    exit;
}

?>