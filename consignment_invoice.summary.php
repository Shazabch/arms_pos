<?php
/*
1/4/2011 4:39:19 PM Andy
- Fix a bug when select only show "draft / waiting for approval", it will also show rejected and terminated.

1/21/2011 9:58:45 AM Andy
- Add can search rejected, canceled/terminated for invoice and CN/DN.

6/24/2011 3:51:09 PM Andy
- Make all branch default sort by sequence, code.

3/26/2012 5:05:32 PM Justin
- Added to pickup trade discount type list.
- Added new filter "price_type".

1/17/2015 12:12 PM Justin
- Enhanced to have GST information.

3/8/2019 12:04 PM Andy
- Fixed query slow issue.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('CON_INVOICE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CON_INVOICE', BRANCH_CODE), "/index.php");

$smarty->assign("PAGE_TITLE", "Consignment Invoice Summary");

$con->sql_query("select distinct(user.id) as id, user.u from ci left join user on user_id = user.id group by id");
$smarty->assign("user", $con->sql_fetchrowset());

$con->sql_query("select id,code from branch order by sequence,code");
$smarty->assign("branch", $con->sql_fetchrowset());

$con->sql_query("select * from trade_discount_type order by code");
$smarty->assign('pt_list', $con->sql_fetchrowset());
$con->sql_freeresult();

if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));

if (isset($_REQUEST['submit'])) generate_report();

$smarty->display("consignment_invoice.summary.tpl");

function generate_report()
{
	global $smarty, $con;
	//print_r($_REQUEST);
	
	//$bid = get_request_branch(true);
	//if ($bid) $where[] = 'ci.branch_id = '.$bid;
	
	// user filter
	if ($_REQUEST['user_id']>0) $where[] = 'ci.user_id = '.mi($_REQUEST['user_id']);
	
	
	if ($_REQUEST['inv_to']=='Open')
		$where[] = "ci.ci_branch_id = 0 and ci.deliver_branch = ''";
	elseif ($_REQUEST['inv_to']!='')
		$where[] = "ci.ci_branch_id = ".mi($_REQUEST['inv_to']);
		
/*	if ($_REQUEST['status']==1)//draft
		$where[] = "do.status = 0";
	elseif ($_REQUEST['status']==2)//approved
		$where[] = "do.approved = 1 and do.checkout = 0 and do.active=1";
	elseif ($_REQUEST['status']==3)//checkout
		$where[] = "do.approved = 1 and do.checkout = 1 and do.active=1";
*/

	//if ($_REQUEST['p'] == 'do') $order = 'order by do.do_date';
	//if ($_REQUEST['p'] == 'invoice') $order = 'order by do.do_no desc';
	$order = 'order by ci_date';
	
	$where[] = "ci.active=1";
	switch ($_REQUEST['status'])
	{
		case 1: // show saved / waiting approval
        	$where[] = "ci.status in (0,1) and ci.approved=0";
        	break;
		case 2: // show approved
		    $where[] = "ci.status=1 and ci.approved=1";
		    break;
        case 3: // rejected
		    $where[] = "ci.status=2 and ci.approved=0";
		    break;
        case 4: // terminated
		    $where[] = "ci.status in (4,5) and ci.approved=0";
		    break;
		default:
		    //$where[] = "ci.status in (0,1)";
		    break;
		/*case 3: // show checkout
		    $where[] = "do.approved=1 and do.checkout=1 ";
		    break;*/
	}

	if($_REQUEST['price_type']){
		$where[] = "((ci.sheet_price_type != '' and ci.sheet_price_type is not null and ci.sheet_price_type = ".ms($_REQUEST['price_type']).") or (cii.price_type = ".ms($_REQUEST['price_type'])."))";
	}
    
	$where[] = "(ci_date between ".ms($_REQUEST['from'])." and ".ms($_REQUEST['to']).")";
	
	if ($where)
	{
		$where = "where " . join(" and ", $where);
	}
	
	$sql =  "select ci.*, category.description as dept_name, branch.report_prefix as branch_prefix, branch.code as branch_name_1,b2.code as branch_name_2, bah.approvals, user.u as user_name, b2.description as ci_branch_description,bah.approval_order_id
from ci
left join ci_items cii on cii.ci_id = ci.id and cii.branch_id = ci.branch_id
left join category on ci.dept_id = category.id
left join branch on ci.branch_id = branch.id
left join branch b2 on ci.ci_branch_id = b2.id
left join user on user.id = ci.user_id
left join branch_approval_history bah on bah.id = ci.approval_history_id and bah.branch_id=ci.branch_id
$where group by ci.id, ci.branch_id $order";
	
	$q2=$con->sql_query($sql);
	while ($r2= $con->sql_fetchassoc($q2)){
 		$r2['open_info'] = unserialize($r2['open_info']);	
		$r2['deliver_branch']=unserialize($r2['deliver_branch']);
		/*
		if($r2['deliver_branch']){
			foreach ($r2['deliver_branch'] as $k=>$v){
				$q3=$con->sql_query("select code from branch where id=$v");
				$r3 = $con->sql_fetchrow($q3);
				$r2['d_branch']['id'][$k]=$v;
				$r2['d_branch']['name'][$k]=$r3['code'];
			}
		}*/
		if($r2['is_under_gst']) $is_under_gst = true;
		$temp2[]=$r2;
	}
	$con->sql_freeresult($q2);
	$ci_list=$temp2;
	$smarty->assign("ci_list", $ci_list);
	$smarty->assign("is_under_gst", $is_under_gst);
	//print_r($ci_list);
}
