<?
/*
revision history
================
20.03.07 yinsee
- fix single branch and view-all total amount error (add group by po.po_branch_id in SQL)

1/8/2008 2:10:51 PM gary
- add vendor filter.
- add PO by deparetment details.

1/14/2008 1:30:43 PM yinsee
- fix branch selection error 

3/5/2008 11:21:27 AM gary
- change old purchase_order link to new po (po.).

5/14/2009 1:07:44 PM yinsee
- filter active=1 only

4/28/2010 5:06:38 PM Andy
- Fix invalid deliver & cancel date bugs.
- Fix show wrong actual & proforma PO bugs.
- Fix wrong PO amount for actual & proforma PO bugs.
- Fix PO show all department will get wrong total selling bugs if have draft or proforma PO.

7/6/2011 12:15:04 PM Andy
- Change split() to use explode()

2/21/2012 4:10:18 PM Andy
- Fix branch cannot view report data.

7/24/2012 11:30 AM Justin
- Added to pickup Account ID and Code.

12/11/2014 3:40 PM Justin
- Enhanced to have GST information.

03/08/2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

07/01/2016 15:30 Edwin
- Enhanced on user able to view although they don't have official module.

12/6/2016 9:39 AM Andy
- Hide ARMS user from user list.

4/20/2018 2:19 PM Andy
- Added Foreign Currency feature.

7/3/2018 4:21 PM Andy
- If all department total selling and gst wrong.

7/31/2019 11:07 AM William
- Added new "Deliver GRN Status" filter.

02/17/2021 10:46 AM Rayleen
- Enchance to export PO in excel file
*/

include("include/common.php");
include("include/excelwriter.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_REPORT', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules'] && $_REQUEST['a'] != 'show') js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
include("po.include.php");
init_selection();
//include("purchase_order.include.php");

$smarty->assign("PAGE_TITLE", "Purchase Order Summary");

if($sessioninfo['id'] != 1){
	$user_filter = "where (user.is_arms_user=0 or po.user_id=".mi($sessioninfo['id']).")";
}
$con->sql_query("select distinct(user.id) as id, user.u from po left join user on user_id = user.id $user_filter group by id");
$smarty->assign("user", $con->sql_fetchrowset());

//show vendor option
if ($sessioninfo['vendors']){
	$vd = "and id in (".join(",",array_keys($sessioninfo['vendors'])).")";
}
$con->sql_query("select id, description from vendor where active $vd order by description");
$smarty->assign("vendor", $con->sql_fetchrowset());

if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));

if($config['foreign_currency']){
	// Get Currency Code Listing
	$currency_code_list = $appCore->currencyManager->getCurrencyCodes();
	$smarty->assign('currency_code_list', $currency_code_list);
}


if(isset($_REQUEST['output_excel'])){ 
  	export_excel();
} else {
	$smarty->display("purchase_order.summary.tpl");
}

function show_report(){
	global $con, $smarty, $sessioninfo;
	$where = array();
	
	if ($_REQUEST['a']!='show') return;
	
	if ($_REQUEST['from']) $where[] = " po.po_date >= ".ms($_REQUEST['from']);
	if ($_REQUEST['to']) $where[] = " po.po_date <= ".ms($_REQUEST['to']);
	if ($_REQUEST['user_id']) $where[] = " po.user_id = ".mi($_REQUEST['user_id']);
	
	$bid = get_request_branch(true);
	if ($bid){
		$where[] = " ((po.branch_id=1 and po.po_branch_id = $bid) or po.branch_id=$bid)";	
	}
	
	if ($_REQUEST['department_id']) {
		$where[] = " po.department_id = ".mi($_REQUEST['department_id']);
		$by_department=1;
	}
	elseif ($sessioninfo['level']<9999){
		$where[] = " po.department_id in (" . join(",", array_keys($sessioninfo['departments'])) . ")"; 
	}
	$where[] = "po.active=1";
	switch ($_REQUEST['status']){
		case 0: 
			break;
		case 1: $where[] = "po.approved=0 and po.status=0";
			break;
		case 2: $where[] = "po.approved=0 and po.status=1";
			break;
		case 3: $where[] = "po.approved=1";
			break;
	}
	
	switch ($_REQUEST['delivery_grn_status']){
		case 0:
			break;
		case 1: $where[] = "po.delivered = 1";
			break;
		case 2: $where[] = "po.delivered = 0";
			break;
	}
	
	if($_REQUEST['vendor_id']){
		$where[] = " po.vendor_id=".mi($_REQUEST['vendor_id']);	
	}
	
	$currency_code = trim($_REQUEST['currency_code']);
	if($currency_code){
		if($currency_code=='base_currency'){
			$where[] = "po.currency_code=''";
		}else{
			$where[] = "po.currency_code=".ms($currency_code);
		}
	}
	
	$where = join(" and ", $where);
	if (!$where) $where = "1";
	
	$con->sql_query("select * from branch");
	while($r = $con->sql_fetchrow()){
		$branches[$r['id']] = $r;
	}
	
	if ($by_department){
		$po = array();
		$sql = "select grr.rcv_date,grr_items.grr_id,po.*,po.id as po_id, vendor.description as vendor, branch.report_prefix,
				branch2.code as po_branch, branch.code as branch, po.delivered, vendor.code as vendor_code, 
				if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id,
				po.is_under_gst,po.currency_code, po.currency_rate,po.total_selling_amt,po.po_gst_amount
				from po
				left join branch on po.branch_id = branch.id
				left join branch branch2 on po.po_branch_id = branch2.id
				left join category on po.department_id = category.id
				left join vendor on po.vendor_id = vendor.id
				left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = po.branch_id
				left join grr_items on grr_items.doc_no = po.po_no and grr_items.type = 'PO'
				left join grr on grr.id = grr_items.grr_id and grr.branch_id = grr_items.branch_id and grr.active = '1'
				where $where order by po_amount desc";
		//print $sql;
		$q1 = $con->sql_query($sql);
    
		while ($r=$con->sql_fetchassoc($q1)){
			if(!$r['currency_rate'])	$r['currency_rate'] = 1;
			$r['base_po_amount'] = $r['po_amount'] * $r['currency_rate'];
			
		    $key = $r['branch_id']."_".$r['po_id'];
		    $r['key'] = $key;
		    $r['expired_date'] = $r['cancel_date'];
		    
			//if(is_array($r['deliver_to'])){
			if(unserialize($r['deliver_to'])){
			    $r['deliver_to'] = unserialize($r['deliver_to']);
			    $delivery_date = unserialize($r['delivery_date']);
			    $cancel_date = unserialize($r['cancel_date']);
			    $expired_date = unserialize($r['cancel_date']);
			    $r['delivery_date'] = '';
			    $r['cancel_date'] = '';
			    $r['expired_date'] = '';

				foreach ($r['deliver_to'] as $v=>$k){					
					if($r['delivery_date']) $r['delivery_date'].="<br>";
					$r['delivery_date'].= $branches[$k]['code'].": ".$delivery_date[$k];
					
					if($r['cancel_date']) $r['cancel_date'].="<br>";
					$r['cancel_date'].= $branches[$k]['code'].": ".$cancel_date[$k];
		    		
		    		// split the expired date due to some dates cannot be using date() function
		    		list($day,$month,$year) = explode("/", $expired_date[$k]);
		    		// set the day and month to become two digit in case found one digit only
		    		$day = str_pad($day, 2, "0", STR_PAD_LEFT);
		    		$month = str_pad($month, 2, "0", STR_PAD_LEFT);
					if(($year.$month.$day) < date(Ymd) && $r['delivered'] == 0){
						$r['expired'] = "Expired";
					}
				}
			}
			
			if($r['expired_date']){
				// split the expired date due to some dates cannot be using date() function
				list($day,$month,$year) = explode("/", $r['expired_date']);
		    	$day = str_pad($day, 2, "0", STR_PAD_LEFT);
		    	$month = str_pad($month, 2, "0", STR_PAD_LEFT);
	    		if(($year.$month.$day) < date(Ymd) && $r['delivered'] == 0){
					$r['expired'] = "Expired";
				}
			}
			
			if($r['is_under_gst']){
				$is_under_gst = 1;
			}
			if($r['currency_code']){
				$got_currency_code = 1;
			}

			$po[$key]=$r;
			if($r['active'] && $r['status']==1 && $r['approved'] && $r['delivered'] && $r['po_no']){
				$po[$key]['delivered_grn_list'] = find_delivered_grn($r['branch_id'], $r['id'], $r['po_no']);
			}
		}
		$con->sql_freeresult($q1);

	  	//$smarty->assign("new_po_nom", $new_po_nom);
 		$smarty->assign("po", $po);
  		$smarty->assign("curr_date", date(Ymd));
		$smarty->assign("is_under_gst", $is_under_gst);
		$smarty->assign("got_currency_code", $got_currency_code);
		$smarty->display("purchase_order.summary.detail.tpl");
	}else{
		$q1 = $con->sql_query("select po.deliver_to,sum(po_amount) as amt, po.branch_id, po_branch_id, branch.code as branch, branch2.code as po_branch, 
							   category.description as dept, po.department_id as dept_id,po.is_under_gst,po.currency_code, po.currency_rate, sum(po.total_selling_amt) as total_selling_amt, sum(po.po_gst_amount) as po_gst_amount
							   from po 
							   left join branch on po.branch_id = branch.id 
							   left join branch branch2 on po.po_branch_id = branch2.id 
							   left join category on po.department_id = category.id
							   where $where 
							   group by po.po_branch_id, po.branch_id, po.currency_code, po.currency_rate, po.department_id");
		
		$total = array();
		while($r=$con->sql_fetchassoc($q1)){
			if(!$r['currency_rate'])	$r['currency_rate'] = 1;
			$base_po_amt = $r['amt'] * $r['currency_rate'];
			
			if ($r['po_branch']){
				$r['branch'] = $r['po_branch'];
				$r['branch_id'] = $r['po_branch_id'];
			}
			
			// Total by Branch by Department
			$tb[$r['branch']][$r['dept']]['total_cost'] += $base_po_amt;
			$col_total[$r['branch']]['total_cost'] += $base_po_amt;
			
			// Currency by Branch by Department
			$tb[$r['branch']][$r['dept']]['currency'][$r['currency_code']]['total_cost'] += $r['amt'];
			//$tb[$r['branch']][$r['dept']]['currency'][$r['currency_code']]['base_total_cost'] += $base_po_amt;
			
			// Branch Total
			$col_total[$r['branch']]['currency'][$r['currency_code']]['total_cost'] += $r['amt'];
			
			
			$uq_br[$r['branch']]['branch_id'] = $r['branch_id'];
			$uq_br[$r['branch']]['currency'][$r['currency_code']] = $r['currency_code'];
			
			$uq_dp[$r['dept']] = $r['dept_id'];
			
			$row_total[$r['dept']]['total_cost'] += $base_po_amt;
			$row_total[$r['dept']]['currency'][$r['currency_code']]['total_cost'] += $r['amt'];
			
			$total['total_cost'] += $base_po_amt;
			$total['currency'][$r['currency_code']]['total_cost'] += $r['amt'];
			
			// Selling
			$row_total[$r['dept']]['total_selling'] += $r['total_selling_amt'];
			
			// GST
			$row_total[$r['dept']]['total_gst'] += $r['po_gst_amount'];
			
			if($r['is_under_gst']){
				$is_under_gst = 1;
			}
		}
		$con->sql_freeresult($q1);

		@asort($uq_br);
		@ksort($uq_dp);
		$smarty->assign("tb", $tb);
		$smarty->assign("total", $total);
		$smarty->assign("uq_br", $uq_br);
		$smarty->assign("uq_dp", $uq_dp);
		$smarty->assign("col_total", $col_total);
		$smarty->assign("row_total", $row_total);
		$smarty->assign("is_under_gst", $is_under_gst);
		$smarty->display("purchase_order.summary.top.tpl");	
	}
}

function export_excel()
{
    global $smarty,$sessioninfo;
    $file = 'po_summary_'.time();
    Header('Content-Type: application/msexcel');
  	Header('Content-Disposition: attachment;filename='.$file.'.xls');
  	print ExcelWriter::GetHeader();
  	$smarty->assign("is_export", 1);
    show_report();
   	print ExcelWriter::GetFooter();
    readfile($file);

    exit;
}