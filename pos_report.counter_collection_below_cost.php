<?php
/*
10/20/2011 5:07:57 PM Alex
- change use report server

10/16/2014 1:45 PM Justin
- Bug fixed on report using the wrong cost to compare.

10/29/2014 11:36 AM Justin
- Bug fixed on some items that were below cost but did not shows out.

11/27/2014 5:05 PM Andy
- Enhance report to show the sales amount using the amount-discount-discount2-tax_amount.(discount2 is receipt and mix & match discount, tax_amount is gst)

2/24/2020 5:00 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
ini_set('memory_limit', '256M');

set_time_limit(0);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

if(!isset($_REQUEST['date_from'])&&!isset($_REQUEST['date_to'])){
	$_REQUEST['date_from'] = date('Y-m-d',strtotime('-1 month',time()));
	$_REQUEST['date_to'] = date('Y-m-d');
}
// limit to 1 month range
$max_date = date('Y-m-d',strtotime('+1 month',strtotime($_REQUEST['date_from'])));
if($_REQUEST['date_to']>$max_date)  $_REQUEST['date_to'] = $max_date;

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	    case 'load_table':
			/*$con_multi= new mysql_multi();
			if(!$con_multi){
				die("Error: Fail to connect report server");
			}*/
	        load_table();
			//$con_multi->close_connection();
			break;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;

	}
}

load_branches();
load_cashier();

$smarty->assign('PAGE_TITLE', 'Counter Collection below Cost Report');
$smarty->display("pos_report.counter_collection_below_cost.tpl");
exit;

function load_cashier(){
	global $con,$smarty,$sessioninfo,$con_multi;

	if(BRANCH_CODE!='HQ'){
		$filter[] = "user_privilege.branch_id=".mi($sessioninfo['branch_id']);
	}

	$filter[] = "user_privilege.privilege_code='POS_LOGIN'";
	$filter[] = "user_privilege.allowed=1";
	$filter[] = "user.active=1";

	$filter = join(' and ',$filter);

	$sql = "select user_privilege.*,user.u from user_privilege left join user on user.id=user_privilege.user_id where $filter group by user_id";
	$q_u = $con_multi->sql_query($sql) or die(mysql_error());
	while($r = $con_multi->sql_fetchrow($q_u)){
		$cashier[$r['user_id']] = $r;
	}
	$con_multi->sql_freeresult($q_u);
	$smarty->assign('cashier',$cashier);
}

function load_branches(){
	global $con,$smarty,$con_multi;

	$q_b = $con_multi->sql_query("select id,code from branch order by sequence, code") or die(mysql_error());
	while($r = $con_multi->sql_fetchassoc($q_b)){
		$branches[$r['id']] = $r['code'];
	}
	$con_multi->sql_freeresult($q_b);
	$smarty->assign('branches',$branches);
	
	return $branches;
}

function load_table(){
	global $con_multi,$smarty,$sessioninfo;
	$cashier_id = $_REQUEST['cashier_id'];
    $date_from = $_REQUEST['date_from'];
    $date_to = $_REQUEST['date_to'];
    
    if(BRANCH_CODE!='HQ'){
        $branch_id = $sessioninfo['branch_id'];
    }else{
        $branch_id = $_REQUEST['branch_id'];
	}
	
	if($branch_id!='all'){
		//$filter[] = "pos.branch_id=".mi($branch_id);
		$branch_list[$branch_id] = get_branch_code($branch_id);
	}else{
		$branch_list = load_branches();
	}
	if($cashier_id!='all'){
		$filter[] = "pos.cashier_id=".mi($cashier_id);
	}
	$filter[] = "pos.date between ".ms($date_from)." and ".ms($date_to);
	$filter[]= "pos.cancel_status=0";
	$filter = join(' and ',$filter);

	foreach($branch_list as $bid=>$bcode){
		$sql = "select pos.pos_time as timestamp,pos.cashier_id,pos.counter_id as count_id,cs.network_name as counter_id, sku_items.description, user.u,round((pi.price-pi.discount-pi.discount2-pi.tax_amount)/pi.qty,2) as sell,sku_items.sku_item_code, round(sisc.cost/sisc.qty,2) as grn_cost, pi.branch_id
		from pos_items pi
		left join pos on pos.id=pi.pos_id and pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id
		left join counter_settings cs on pi.counter_id=cs.id and pi.branch_id=cs.branch_id
		left join sku_items on sku_items.id=pi.sku_item_id
		left join user on pos.cashier_id=user.id
		left join sku_items_sales_cache_b$bid sisc on sisc.sku_item_id = pi.sku_item_id and sisc.date = pi.date
		join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date and pf.finalized=1
		where $filter and pi.branch_id = ".mi($bid)."
		group by pos.branch_id,pos.counter_id,pos.pos_time,pos.id,pi.sku_item_id
		having sell<grn_cost
		order by pos.pos_time desc";
		$q1 = $con_multi->sql_query($sql) or die(mysql_error());

		while($r = $con_multi->sql_fetchassoc($q1)){		
			$r['different'] = $r['sell']-$r['grn_cost'];
			$table[$r['branch_id']][] = $r;
			
			$total[$r['branch_id']]['sell'] += $r['sell'];
			$total[$r['branch_id']]['grn_cost'] += $r['grn_cost'];
			$total[$r['branch_id']]['different'] += $r['different'];
		}
		$con_multi->sql_freeresult($q1);
	}
    
	//print_r($table);
	$smarty->assign('table',$table);
	$smarty->assign('total',$total);
}
?>
