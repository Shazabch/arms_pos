<?
/*
5/3/2018 11:35 AM Andy
- Added Foreign Currency feature.

2/26/2020 9:39 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
error_reporting(0);
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_REPORT', BRANCH_CODE), "/index.php");
if (!$con_multi)  $con_multi = $appCore->reportManager->connectReportServer();
$smarty->assign("PAGE_TITLE", "Vendor Purchase Ranking");

if (BRANCH_CODE == 'HQ'){
	$con_multi->sql_query("select id, code from branch");
	$smarty->assign('branch',$con_multi->sql_fetchrowset());
	$con_multi->sql_freeresult();
}

//show vendor option
if ($sessioninfo['vendors']){
	$vd = "and id in (".join(",",array_keys($sessioninfo['vendors'])).")";
}
$con_multi->sql_query("select id, description from vendor where active $vd order by description");
$smarty->assign("vendor", $con_multi->sql_fetchrowset());
$con_multi->sql_freeresult();

if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));

$smarty->display("vendor_po.summary.tpl");

function show_report(){
	global $con, $smarty, $sessioninfo, $con_multi;

	//for printing purpose
	$from_Date=$_REQUEST['from'];
	$to_Date=$_REQUEST['to'];
	
	$title="Date: $from_Date - $to_Date";	
	$title .= " / ";
	if ($_REQUEST['branch_id']){
		$con_multi->sql_query("select description from branch where id=".mi($_REQUEST['branch_id']));
		$v = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		$title .= "Branch: $v[0]";
	}
	else{
		$title .= "Branch: All";
	}
	$title .= " / ";	
	if ($_REQUEST['vendor_id']){
		$con_multi->sql_query("select description from vendor where id=".mi($_REQUEST['vendor_id']));
		$v = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		$title .= "Vendor: $v[0]";
	}
	else{
		$title .= "Vendor: All";
	}
	$smarty->assign("title", "($title)");	

	
	$where = array();
	$where[] = "po_date between ".ms($_REQUEST['from'])." and ".ms($_REQUEST['to']);	

	$bid = mi($_REQUEST['branch_id']);
	if ($bid =='' && BRANCH_CODE!='HQ'){
		$bid = $sessioninfo['branch_id'];
	}	
	if($bid){ 
		$where[] = " po.branch_id=".mi($bid); 
	}
	
	if ($_REQUEST['vendor_id']){
		$where[] = " po.vendor_id = ".mi($_REQUEST['vendor_id']);
		$by_vendor=1;
	}
		
	$where = join(" and ", $where);
	if (!$where) $where = "1";	
		
	if(!$by_vendor){
		function sort_vendor_po_list($a, $b){
			if ($a['base_po_amount']==$b['base_po_amount']) return 0;
        	return ($a['base_po_amount']>$b['base_po_amount']) ? -1 : 1;
		}
		
		$con_multi->sql_query("select po.currency_code, sum(po_amount) as ori_po_amount, sum(po_amount*if(po.currency_rate<0,1,po.currency_rate)) as base_po_amount , vendor.description as vendor_desc, count(po_no) as no_of_po, vendor.id as vendor_id		 
		from po 
		left join vendor on vendor.id=po.vendor_id 
		where $where and po.active=1 and po.approved=1 
		group by po.vendor_id, po.currency_code");
		$vendor_po_list = array();
		$currency_code_list = array();
		$total = array();
		while($r = $con_multi->sql_fetchassoc()){
			if($r['currency_code']){
				$currency_code_list[$r['currency_code']] = $r['currency_code'];
				$vendor_po_list[$r['vendor_id']]['currency'][$r['currency_code']]['amt'] += $r['ori_po_amount'];
				$vendor_po_list[$r['vendor_id']]['got_currency'] = 1;
				
				$total['currency'][$r['currency_code']]['amt'] += $r['ori_po_amount'];
				$total['got_currency'] = 1;
			}else{
				$vendor_po_list[$r['vendor_id']]['base_currency']['amt'] += $r['ori_po_amount'];
				
				$total['base_currency']['amt'] += $r['ori_po_amount'];
			}
			
			$vendor_po_list[$r['vendor_id']]['vendor_desc'] = $r['vendor_desc'];
			$vendor_po_list[$r['vendor_id']]['base_po_amount'] += $r['base_po_amount'];
			$vendor_po_list[$r['vendor_id']]['no_of_po'] += $r['no_of_po'];
			
			$total['base_po_amount'] += $r['base_po_amount'];
			$total['no_of_po'] += $r['no_of_po'];
		}
		$r=$con_multi->sql_fetchrowset();
		$con_multi->sql_freeresult();
		//print_r($vendor_po_list);
		if($vendor_po_list){
			uasort($vendor_po_list, "sort_vendor_po_list");
		}
		$smarty->assign("currency_code_list", $currency_code_list);
		$smarty->assign("vendor_po_list", $vendor_po_list);
		$smarty->assign("total", $total);
		$smarty->display("vendor_po.summary.top.tpl");
	}
	else{
		$con_multi->sql_query("select po.*, po.id as po_id, branch.report_prefix , branch.code as branch, cat.description as department, user.u as user, bah.flow_approvals as approvals, (po.po_amount*if(po.currency_rate<0,1,po.currency_rate)) as base_po_amount
from po 
left join branch on po.branch_id = branch.id
left join category cat on cat.id=po.department_id
left join user on user.id=po.user_id  
left join branch_approval_history bah on bah.id = po.approval_history_id and bah.branch_id = po.branch_id 
where $where and po.active=1 and po.approved=1 
order by base_po_amount desc");
		$po_list = array();
		while ($r=$con_multi->sql_fetchrow()){
			if($r['currency_code']){
				$got_currency = 1;
			}
			$po_list[] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign("got_currency", $got_currency);
		$smarty->assign("po_list", $po_list);
		$smarty->display("vendor_po.summary.detail.tpl");	
	}

}
