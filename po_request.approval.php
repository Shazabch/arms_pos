<?
/*
7/3/2009 5:36 PM Andy
- add unserialize to get sales trend data

6/24/2011 5:13:03 PM Andy
- Make all branch default sort by sequence, code.

9/12/2011 3:32:03 PM Alex
- add log

2/4/2013 2:06 PM Fithri
- sort item by old item at top

01/27/2016 13:41 Edwin
- Bug fixed on Cost and Sell price show differently in PO Request Approval after PO Request is created.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO_REQUEST_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_REQUEST_APPROVAL', BRANCH_CODE), "/index.php");

$branch_id = $sessioninfo['branch_id'];
$user_id = $sessioninfo['id'];

$smarty->assign("PAGE_TITLE", "PO Request Approval");

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'show_items':
			$dept_id = intval($_REQUEST['department_id']);
			get_request_items("category.department_id = $dept_id");
			$smarty->display('po_request.approval.items.tpl');
			exit;
			
  		case 'delete_one':
  		    $delete_id = intval($_REQUEST['delete_id']);
  		    $branch= intval($_REQUEST['branch']);
  		    $reject_comment = ms($_REQUEST['reject_comment']);
            $con->sql_query("update po_request_items set approve_by='$user_id',reject_comment=$reject_comment,status='2',approve_time=NOW(),added=added where id='$delete_id' and branch_id='$branch'");
            
			//add log
			log_br($user_id, 'PO REQUEST APPROVAL', $delete_id, "Delete Branch ".get_branch_code($branch).", ID #$delete_id, Comment=".$reject_comment);
			unset($log);
			break;
			
		case 'delete':
  		    $reject_comment = ms($_REQUEST['reject_comment']);
            foreach($_REQUEST['sel'] as $bid=>$bsel){
                foreach($bsel as $id=>$dummy){
					$bsel_arr[]=mi($id);
            	}
            	
				$ids=implode(",",$bsel_arr);

            	$con->sql_query("update po_request_items set approve_by='$user_id',reject_comment=$reject_comment,status='2',approve_time=NOW(),added=added where id in (".$ids.") and branch_id=".mi($bid));

				$log[]="Branch ".get_branch_code(mi($bid)).", ID #(".$ids.")";
            	
				unset($ids,$bsel_arr);
			}

			//add log
			log_br($user_id, 'PO REQUEST APPROVAL', 0, "Delete ".implode(" | ", $log).", Comment=".$reject_comment);
			unset($log);
		    break;
		    
		case 'approve':
            foreach($_REQUEST['sel'] as $bid=>$bsel) {
                foreach($bsel as $id=>$dummy){
					$propose_qty=ms($_REQUEST['propose_qty'][$bid][$id]);
					$con->sql_query("update po_request_items set approve_by='$user_id',propose_qty=$propose_qty,status='1',approve_time=NOW(),added=added where id=".mi($id)." and branch_id=".mi($bid));

					$log[]="Branch ".get_branch_code(mi($bid)).", ID #".mi($id).", Propose Qty=".$propose_qty;
            	}
			}
			//add log
			log_br($user_id, 'PO REQUEST APPROVAL', 0, "Update ".implode(" | ", $log));
			unset($log);
		   	break;
		
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

// show department option
if ($sessioninfo['level'] < 9999){
	if (!$sessioninfo['departments'])
		$depts = "id in (0)";
	else
		$depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
}
else{
	$depts = 1;
}
$con->sql_query("select id, description from category where active and level = 2 and $depts order by description");
$smarty->assign("dept", $con->sql_fetchrowset());
$con->sql_freeresult();
$con->sql_query("select id, code from branch order by sequence,code");
$smarty->assign("branches", $con->sql_fetchrowset());
$con->sql_freeresult();

$smarty->display("po_request.approval.tpl");
//get_request_items();

function get_request_items($filter)
{
	global $con, $smarty, $branch_id, $sessioninfo;
	
	// HQ allow to create po for all requests
	if (BRANCH_CODE!='HQ'){
		$filter .= " and po_request_items.branch_id = $branch_id";
		$po_filter = "and (po.branch_id=$branch_id or po.po_branch_id=$branch_id)";
	}
	elseif ($_REQUEST['branch_id']>0){
		$filter .= " and po_request_items.branch_id = ".mi($_REQUEST['branch_id']);
		$po_filter = "and (po.branch_id=".mi($_REQUEST['branch_id'])." or po.po_branch_id=".mi($_REQUEST['branch_id']).")";
	}

	$r1=$con->sql_query("select po_request_items.*, sku_items.artno, sku_items.mcode, sku_items.sku_item_code, sku_items.description as sku, user.u, branch.code as branch, uom.fraction, uom.code as uom_code, puom.code as packing_uom_code
						from po_request_items
						left join sku_items on po_request_items.sku_item_id = sku_items.id
						left join sku on sku_items.sku_id = sku.id
						left join category on sku.category_id = category.id
						left join user on po_request_items.user_id = user.id
						left join branch on po_request_items.branch_id = branch.id
						left join uom on uom.id=po_request_items.uom_id
						left join uom puom on puom.id=sku_items.packing_uom_id
						where po_request_items.active=1 and po_request_items.status=0 and $filter order by po_request_items.added asc");

	//$form=$con->sql_fetchrowset();
	//echo "<pre>";
    //print_r($form);
	//echo "</pre>";
 
	while($r = $con->sql_fetchassoc($r1)){
	
		$r2 = $con->sql_query("select po_items.order_price, po_items.order_uom_id, po_items.order_uom_fraction, po_items.selling_price, po.added as po_added, po.vendor_id, po.po_no, po.id as po_id, vendor.description as vendor, uom.code as uom_code, uom.fraction
							   from po_items
							   left join po on po_items.po_id = po.id and po_items.branch_id = po.branch_id
							   left join uom on po_items.order_uom_id = uom.id
							   left join vendor on vendor.id = po.vendor_id and vendor.active=1
							   where po.status=1 and po.approved=1 $po_filter and po_items.sku_item_id = ".ms($r['sku_item_id'])." and po.po_no is not null order by po.added desc");
		
		while($pi = $con->sql_fetchassoc($r2)){
			//print "<br />=>PO st:$pi[status] $pi[po_id]/$pi[po_no] #cost:$pi[order_price] vd:$pi[vendor] $pi[po_added]";
			$r = $r + $pi;
		}
		$con->sql_freeresult($r2);
		
        $r['sales_trend'] = unserialize($r['sales_trend']);
        $form[] = $r;
	}
	
	$con->sql_freeresult($r1);
	
	$smarty->assign("form", $form);
	//$smarty->assign("form", $con->sql_fetchrowset());
}
?>
