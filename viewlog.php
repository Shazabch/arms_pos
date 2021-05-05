<?
/*
Revision History
----------------
9 Apr 2007 - yinsee
- added branch_id column to log table
- added branch dropdown selection

11/11/2009 10:07:24 AM edward
- fix type

1/12/2011 6:11:41 PM Andy
- change column lastlogin to use lastlogin from table user_status.

4/13/2012 3:42:14 PM Alex
- add search keyword features

7/31/2012 5:32:34 PM Justin
- Bug fixed on sql error.

9/11/2012 6:14 Drkoay
- User allow to filter with User All (Branch and type cannot to be all when user is all)
- add from date and to date

11/5/2013 5:02 PM Justin
- Bug fixed on date from and to filters.

9/8/2016 11:46 PM Andy
- Enhanced to filter out arms user.

7/24/2017 10:51 AM Justin
- Bug fixed on the hardcoded "HTTP:\\" will cause problem if the URL is "HTTPS" and using port.

5/22/2018 11:59 AM Justin
- Enhanced to use 'like' to do matching for SKU description instead of using 'match'.

2/14/2020 3:19 PM William
- Enhanced to combine similar log type into one group.
- Enhanced log type can search by group.

12/11/2020 3:57 PM Shane
- Added "POS ANNOUNCEMENT" type under POS group
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('VIEWLOG')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'VIEWLOG', BRANCH_CODE), "/index.php");
$maintenance->check(298);

if ($_REQUEST['a'] == 'fix_type')
{
    $con->sql_query("update log set type = 'USER PROFILE', timestamp=timestamp where type in('USERS_MNG','UPDATE_PROFILE')");
	$con->sql_query("update log set type = 'APPROVAL FLOW', timestamp=timestamp where type='SYSTEM'");
	//$con->sql_query("update log set type = 'SKU',timestamp=timestamp where type='MASTERFILE' and log like '%SKU%' ");
	$con->sql_query("update log set type = 'MASTERFILE', timestamp=timestamp where type='MASTERFILE_SKU_ACT'");
	$con->sql_query("update log set type = 'PURCHASE ORDER', timestamp=timestamp where type='PURCHASE O'");
	$con->sql_query("update log set type = 'PURCHASE ORDER', timestamp=timestamp where type='PO'");
	$con->sql_query("update log set type = 'DELIVERY ORDER', timestamp=timestamp where type='Delivery ORDER'");
	print $con->sql_affectedrows()." updated.";
	
exit;
}


if (BRANCH_CODE == 'HQ')
	$branch_id = intval($_REQUEST['branch_id']);
else
	$branch_id = $sessioninfo['branch_id'];

if ($branch_id) $branch_filter =  "branch_id = $branch_id"; else $branch_filter = 1;


$data_type_list = $array_type_list = array();

//select all log type from database
$q1 = $con->sql_query("select distinct(upper(type)) as type from log where $branch_filter");
while($r = $con->sql_fetchassoc($q1)){
	$data_type_list[] = trim($r['type']);
}
$con->sql_freeresult($q1);

$log_type_list = array(
	"ADJUSTMENT"=>array("ADJUSTMENT"),
	"APPROVAL FLOW"=>array("APPROVAL FLOW"),
	"ARMS_DN"=>array("ARMS_DN"),
	"ATTENDANCE"=>array("ATTENDANCE"),
	"CASHIER SETUP"=>array("Cashier Setup"),
	"CONFIG_MANAGER"=>array("CONFIG_MANAGER"),
	"CONSIGNMENT"=>array("CI", "Consignment Forex", "Consignment Invoice", "Consignment Lost Inv", "Consignment Report", "SHEET_NAME"),
	"CONSIGNMENT_BEARING"=>array("CONSIGNMENT_BEARING"),
	"COPY_SELLING_PRICE"=>array("COPY_SELLING_PRICE"),
	"COUNTER COLLECTION"=>array("Counter Collection", "Counter Collection A", "Counter Collection C"),
	"COUNTER STATUS"=>array("COUNTER STATUS"),
	"COUPON"=>array("COUPON"),
	"CREDIT NOTE"=>array("Credit Note"),
	"CURRENCY_TABLE"=>array("CURRENCY_TABLE"),
	"CUSTOM ACC EXPORT"=>array("CUSTOM ACC EXPORT"),
	"CYCLE COUNT"=>array("CYCLE COUNT"),
	"DEBTOR_PRICE"=>array("DEBTOR_PRICE"),
	"DELIVERY ORDER"=>array("DELIVERY ORDER", "DO", "DO Request"),
	"FRESH MARKET STOCK TAKE"=>array("FRESH MARKET STOCK T"),
	"FRONTEND"=>array("FRONTEND"),
	"FUTURE_PRICE"=>array("FUTURE PRICE", "FUTURE_PRICE"),
	"GRA"=>array("GRA"),
	"GRN"=>array("GRN"),
	"GRR"=>array("GRR"),
	"GST"=>array("GST PRICE WIZARD", "GST_PRICE_WIZARD", "GST_SETTINGS", "MST_GST"),
	"IMPORT_BRAND"=>array("IMPORT_BRAND"),
	"IMPORT_VENDOR"=>array("IMPORT_VENDOR", "IMPORT_VENDOR_QC"),
	"LOGIN"=>array("LOGIN"),
	"MARKETPLACE"=>array("MARKETPLACE"),
	"MASTERFILE"=>array("BRANCH_ADDITIONAL_SP", "DISCOUNT_TABLE_EXPOR", "MASTERFILE", "SUPERMARKET CODE"),
	"MEMBERSHIP"=>array("MEMBERSHIP", "MEMBERSHIP (CD)", "MEMBERSHIP_REDEMPTIO", "MEMBER_POINT_EXPORT", "Redemption"),
	"POS"=>array("Deposit", "IMPORT POS SALES", "POS", "POS_SETTING", "POS ANNOUNCEMENT"),
	"PROMOTION"=>array("Promotion"),
	"PURCHASE ORDER"=>array("PURCHASE AGREEMENT", "PURCHASE ORDER", "PO From Request", "PO REQUEST APPROVAL", "MASTER_PO_REORDER"),
	"REPLACEMENT ITEM"=>array("Replacement Item"),
	"SMS"=>array("Notification"),
	"REPORT_EXPORT"=>array("REPORT_EXPORT"),
	"RETURN_POLICY"=>array("RETURN_POLICY", "RETURN_POLICY_SETUP"),
	"SALES_ORDER"=>array("SALES_ORDER", "SALES ORDER CSV", "SALES ORDER"),
	"SALES TARGET GENERATE"=>array("Sales Target Generat"),
	"SALES_AGENT"=>array("SALES_AGENT", "SA_COMMISSION", "SA_KPI_RESULT", "SA_KPI_SETUP", "SA_POSITION_SETUP"),
	"SERIAL NUMBER"=>array("Serial Number"),
	"SKU"=>array("SKU", "SKU Group", "UPDATE_SKU", "UPDATE_SKU_COST", "SKU GROUP DATE", "SKU Monitoring Group", "SKU_BATCH_NO_SETUP", "SKU_EXPORT", "SKU_MIGRATION", "MASTERFILE_SKU_ACT"),
	"SOP"=>array("SOP", "SOP MST FESTIVAL", "SOP MST USER GROUP", "SOP YMP"),
	"STOCK TAKE"=>array("Stock Take", "Stock Check", "PDA Stock Take"),
	"SUITE_DEVICE"=>array("SUITE_DEVICE"),
	"TRADE IN"=>array("Trade In"),
	"USER"=>array("UPDATE_PROFILE", "USER PROFILE"),
	"VD_QUOTATION_COST"=>array("VD_QUOTATION_COST"),
	"VOUCHER"=>array("VOUCHER", "VOUCHER SETUP", "AUTO_REDEMPTION"),
	"VENDOR PORTAL"=>array("REPACKING", "VP DISPOSAL", "VP STOCK TAKE"),
	"WEB_BRIDGE"=>array("WEB_BRIDGE"),
	"WORK ORDER"=>array("Work Order")
);
$types = array();
$types = $log_type_list;
//remove type, if cannot found on database
foreach($types as $parent_type=>$type_lst){
	foreach($type_lst as $key=>$val){
		if(!in_array(strtoupper($val), $data_type_list)){
			//remove child type, if cannot found on database
			unset($types[$parent_type][$key]);
			//remove parent type if don't have any child
			if(count($types[$parent_type]) <= 0) unset($types[$parent_type]);
		}
		$array_type_list[] = strtoupper($val);
	}
}

//if not in any of the group on types array then add to 'OTHER' group
foreach($data_type_list as $key=>$val){
	if(!in_array(strtoupper($val), $array_type_list)){
		$types['OTHER'][] = trim($val);
		$log_type_list['OTHER'][] = trim($val);	
	}
}
$smarty->assign("types", $types);

$con->sql_query("select id,code from branch order by id");
$smarty->assign("branches", $con->sql_fetchrowset());

$user_filter = array();
$user_filter[] = "not user.template and $branch_filter";

if ($sessioninfo['level']<9999){
	$user_filter[] = "user.level < $sessioninfo[level] or user.id = $sessioninfo[id]";
}

if($sessioninfo['id'] != 1){
	$user_filter[] = "user.id != 1";
	$user_filter[] = "(user.is_arms_user=0 or user.id=".mi($sessioninfo['id']).")";
}
	
$user_filter = join(' and ', $user_filter);

$con->sql_query("select distinct(log.user_id) as id, user.u, user.active, us.lastlogin, branch.code as branch_code
from log
	left join user on log.user_id = user.id
	left join user_status us on us.user_id=user.id
	left join branch on user.default_branch_id = branch.id
	where $user_filter order by u");
$smarty->assign("users", $con->sql_fetchrowset());

$id = intval($_REQUEST['user_id']);

//if ($id > 0)
if($_REQUEST['find'])
{
	$filters = array();
	if($id > 0) $filters[] = "user_id = ".mi($id);
	
	$filters[] = $branch_filter;

	if ($id != $sessioninfo['id'] && !privilege('VIEWLOG_ALL'))
	{
		// can't view others log, ignore selected-id
		$id = $sessioninfo['id'];
	}

	if ($_REQUEST['filter_type'])
	{
		$type = $_REQUEST['filter_type'];
		if(substr($type, 0, 2) == 'G:'){
			$type_list = $filter_type_list = array();
			
			$group_type = substr($type, 2);
			$type_list = $log_type_list[$group_type];
			if($type_list){
				foreach($type_list as $key=>$val){
					$filter_type_list[] = ms($val);
				}
				$filter_type = implode(", ",$filter_type_list);
				$filters[] = "log.type in(".$filter_type.")";
			}
		}else{
			$filters[] = "log.type = " . ms($_REQUEST['filter_type']);
		}
	}

	//search keyword
	$_REQUEST['keyword'] = trim($_REQUEST['keyword']);
	if ($_REQUEST['keyword'])
	{
		$ll = preg_split("/\s+/", $_REQUEST['keyword']);

		$desc_matching = array();
		foreach ($ll as $l) {
			if ($l) $desc_matching[] = "log like " . ms('%'.replace_special_char($l).'%');
		}
		$filters[] = join(" and ", $desc_matching);
	}

	if ($_REQUEST['date_from'])
	{
		$filters[] = "log.timestamp >= " . ms($_REQUEST['date_from']." 00:00:00");
	}
	
	if ($_REQUEST['date_to'])
	{
		$filters[] = "log.timestamp <= " . ms($_REQUEST['date_to']." 23:59:59");
	}
	
	$filter = join(" and ", $filters);
	
	// prepare pagination
	$pg = intval($_REQUEST['pg']);
	$s = intval($_REQUEST['s']);
	if ($pg==0) { $_REQUEST['pg']=50; $pg = 50; } //set default pgsize to 50
	$con->sql_query("select count(*) as t from log where $filter");
	$r = $con->sql_fetchrow();
	$t = $r['t'];
	$querystring = preg_replace('/&*s=\d+/', '', $_SERVER['QUERY_STRING']);
	$smarty->assign("pagination", generate_pagination("?$querystring", $t, $pg, 's', $s));

	//echo "select log.*, branch.code as branch from log left join branch on branch_id = branch.id $filter order by timestamp desc limit $s, $pg";
	// render
	$con->sql_query("select log.*, branch.code as branch,user.u from log left join branch on branch_id = branch.id left join user on log.user_id = user.id where $filter order by timestamp desc limit $s, $pg");
	$logs=$con->sql_fetchrowset();
	$smarty->assign("logs", $logs);
}

$smarty->assign("PAGE_TITLE", "View Logs");
$smarty->display("viewlog.tpl");
?>

