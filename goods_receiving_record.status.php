<?
/*
REVISION HISTORY
===============
11/22/2007 3:13:35 PM gary
- from -1 month from now.

1/24/2008 3:46:13 PM gary
- add all doc_no from grr_items, add all status (incomplete/completed) for grr.

6/24/2011 4:16:02 PM Andy
- Make all branch default sort by sequence, code.

10/10/2011 6:28:11 PM Justin
- Fixed the GRR notification not to pick up those IBT items.

3/5/2012 3:33:31 PM Justin
- Added to pickup GRN list.

7/24/2012 11:30 AM Justin
- Added to pickup Account ID.

3/3/2016 1:51 PM Andy
- Fix default init selection din't load.

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

5/24/2019 9:19 AM William
- Pickup report_prefix for enhance "GRR".

8/26/2019 3:42 PM Andy
- Fixed report to only show active GRR.

8/27/2019 11:35 AM Andy
- Fixed document type DO and Other unable to show if filter by status 'incomplete'.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRR_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRR_REPORT', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
if($config['use_grn_future'])
	include("goods_receiving_note2.include.php");
else
	include("goods_receiving_note1.include.php");

init_selection();
$smarty->assign("PAGE_TITLE", "GRR Status Report");

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'view':
			$id = mi($_REQUEST['id']);
			$bid = mi($_REQUEST['branch_id']);
			
			$items = array();
			$rs1 = $con->sql_query("select grr.*, grr_items.*, grr.id as grr_id, grr.rcv_date, vendor.description as vendor, user.u as keyin_u, user2.u as rcv_u, category.description as department,branch.report_prefix from grr_items 
			left join grr on (grr_id = grr.id and grr_items.branch_id = grr.branch_id) 
			left join branch on grr.branch_id = branch.id 
			left join user on grr.user_id = user.id 
			left join user user2 on grr.rcv_by = user2.id 
			left join vendor on grr.vendor_id = vendor.id 
			left join category on grr.department_id = category.id where grr.id = $id and grr.branch_id=$bid and grr.vendor_id != 0 order by grr_items.id");
			while($r = $con->sql_fetchrow($rs1))
			{
				if ($r['type'] == 'PO')
				{
					// get_po();
					//$po_detail[$r['doc_no']] = get_po($r['doc_no']);
					$con->sql_query("select id, branch_id from po where po_no = ".ms($r['doc_no']));
					$po=$con->sql_fetchrow();
					$r['po_id'] = $po['id'];
					$r['po_branch_id'] = $po['branch_id'];
				}
				
				$items[] = $r;
			}

			$q2 = $con->sql_query("select grn.id, grn.branch_id from grn where grn.grr_id = ".mi($id)." and grn.branch_id = ".mi($bid)." and grn.active = 1");
			
			while($r1 = $con->sql_fetchrow($q2)){
				$grn_list[] = $r1;
			}

			$items[0]['grn_list'] = $grn_list;
			$smarty->assign("PAGE_TITLE", "GRR Detail");
			$smarty->assign("items", $items);
			$smarty->display("goods_receiving_record.view.tpl");
			exit;
			
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}
// show branch option
$con->sql_query("select id, code from branch order by sequence,code");
$smarty->assign("branch", $con->sql_fetchrowset());

// get vendor
$con->sql_query("select distinct vendor.id, vendor.description from grr left join vendor on grr.vendor_id = vendor.id ".(BRANCH_CODE == 'HQ' ? "" : " where grr.branch_id = $sessioninfo[branch_id]")." order by description");
$smarty->assign("vendor", $con->sql_fetchrowset());

// set default date
if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
if (!isset($_REQUEST['to'])) { $_REQUEST['to'] = date("Y-m-d"); }
$smarty->display("goods_receiving_record.status.tpl");

function show_report()
{
	global $con, $sessioninfo, $smarty;
	
	$pf = array();
	if ($_REQUEST['branch_id'])
	    $pf[] = "grr.branch_id = " .mi($_REQUEST['branch_id']);
	else
	    $pf[] = "grr.branch_id = $sessioninfo[branch_id]";

	$pf[] = "grr.rcv_date >= ".ms($_REQUEST['from']);
	$pf[] = "grr.rcv_date <= ".ms($_REQUEST['to']);
	$pf[] = "grr.vendor_id != 0 and grr.active=1";
	
	if ($_REQUEST['department_id']){
		$pf[] = "grr.department_id = ".mi($_REQUEST['department_id']);
	} 
	if ($_REQUEST['vendor_id']){
 		$pf[] = "grr.vendor_id = ".mi($_REQUEST['vendor_id']);	
	}
	$where = join(" and ", $pf);
	
	// grr status = 2 >> grr was manually marked as checked 
	$sql = "select sum(if(type='PO',1,0)) as po_count,sum(if(type='PO' and grn_used,1,0)) as po_used_count, 
			sum(if(type='INVOICE',1,0)) as inv_count,sum(if(type='INVOICE' and grn_used,1,0)) as inv_used_count, 
			sum(if(type='DO',1,0)) as do_count, sum(if(type='DO' and grn_used,1,0)) as do_used_count, 
			sum(if(type='OTHER',1,0)) as misc_count,sum(if(type='OTHER' and grn_used,1,0)) as misc_used_count, grr_id,
			(select branch.report_prefix from branch where branch.id =grr.branch_id ) as report_prefix,
			grr.branch_id, grr.rcv_date, grr.last_update, grr.status, vendor.description as vendor, user.u as user, 
			group_concat(grr_items.doc_no order by 1 separator ', ') as all_doc_no, vendor.code as vendor_code,
			if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id
			from grr_items 
			left join grr on grr_items.grr_id = grr.id and grr_items.branch_id = grr.branch_id 
			left join vendor on grr.vendor_id = vendor.id 
			left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = grr_items.branch_id
			left join user on user.id=grr.user_id 
			where $where ";	
	
	if ($_REQUEST['status']=='incomplete'){
		$sql .= 'and grr.status <> 2 group by grr_id 
			having po_count>po_used_count or (po_count=0 and inv_count>inv_used_count) or (po_count=0 and inv_count=0 and do_count>do_used_count) or (po_count=0 and inv_count=0 and do_count=0 and misc_count>misc_used_count)';
	}elseif ($_REQUEST['status']=='completed'){
		$sql .= 'group by grr_id having status = 2 or ((po_count>0 and po_count=po_used_count) or (po_count=0 and inv_count>0 and inv_count=inv_used_count) or (po_count=0 and inv_count=0 and do_count>0 and do_count=do_used_count) or (po_count=0 and inv_count=0 and do_count=0 and misc_count>0 and misc_count=misc_used_count))';
	}else{
		$sql .= 'group by grr_id';
	}
	
	/*
	if ($_REQUEST['status']=='incomplete'){
		$sql .= 'and grr.status <> 2 group by grr_id having po_count>po_used_count or (po_count=0 and inv_count>inv_used_count)';
	}
	else{
		$sql .= 'group by grr_id having status = 2 or (po_count>0 and po_count=po_used_count) or (po_count=0 and inv_count>0 and inv_count=inv_used_count)';
	}
	*/
		
	$q1 = $con->sql_query($sql);
	
	while($r = $con->sql_fetchassoc($q1)){
		$q2 = $con->sql_query("select id, branch_id from grn where grn.grr_id = ".mi($r['grr_id'])." and grn.branch_id = ".mi($r['branch_id'])." and grn.active = 1");
		
		while($r1 = $con->sql_fetchrow($q2)){
			$r['grn_list'][] = $r1;
			
		}
		$grr[] = $r;
	}
  /*> with PO == all PO must have GRN
  > without PO == all INV must have GRN (outright)*/
	//$grr=$con->sql_fetchrowset();
	//echo"<pre>";print_r($grr);echo"</pre>";
	$smarty->assign("grr", $grr);
	//$smarty->display("goods_receiving_record..table.tpl");
	
}


function get_po($pono)
{
	global $con;
	
	$con->sql_query("select po_items.* from po_items left join po on po_items.po_id = po.id and po_items.branch_id = po.branch_id where po_no = ".ms($pono)." order by po_items.id");
	return $con->sql_fetchrowset();
//	return $a;
}
?>
