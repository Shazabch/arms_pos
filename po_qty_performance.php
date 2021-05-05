<?php
/*
6/24/2011 5:12:29 PM Andy
- Make all branch default sort by sequence, code.

8/25/2011 3:46:54 PM Andy
- Add checking privilege "PO_REPORT" for PO Qty Performance.

12/11/2014 3:40 PM Justin
- Enhanced to have GST information.

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

12/11/2017 11:07 AM Andy
- Separate item into different row when have multiple PO.
- Show PO Date for each PO.

4/23/2018 5:00 PM Andy
- Added Foreign Currency feature.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
include("po.include.php");
if (!privilege('PO_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_REPORT', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

$smarty->assign("PAGE_TITLE", "Purchase Order Quantity Performance");

if(isset($_REQUEST['subm'])){
	$qty_per = floatval($_REQUEST['qty_per']);

	if($qty_per==0){
		$err[] = 'Please key in Quantity Purchase';
	}
	
	if($err){
		$smarty->assign('err',$err);
	}
}

$date_from = $_REQUEST['date_from'];
$date_to = $_REQUEST['date_to'];
$dept_id = intval($_REQUEST['dept_id']);
$vendor_id = intval($_REQUEST['vendor_id']);
$branch_id  = get_request_branch(true);

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	    case 'showForm':
	        if(!$err){
                showForm();
			}
	        break;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;	
	}
}

load_branches();
load_departments();
load_vendors();

if(!isset($_REQUEST['date_from'])&&!isset($_REQUEST['date_to'])){
	assign_default_date();
}

$smarty->display("po_qty_performance.tpl");
exit;

function load_branches(){
	global $con,$smarty;
	
    $con->sql_query("select * from branch order by sequence,code");
	$smarty->assign("branches", $con->sql_fetchrowset());
}

function load_departments(){
	global $con,$smarty,$sessioninfo;
	
    $con->sql_query("select * from category where id in ($sessioninfo[department_ids]) order by description");
	$smarty->assign("departments", $con->sql_fetchrowset());
}

function load_vendors(){
    global $con,$smarty;
    
    $con->sql_query("select id,description from vendor order by description") or die(mysql_error());
	$smarty->assign("vendors",$con->sql_fetchrowset());
}

function assign_default_date(){
    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-3 month"));
	$_REQUEST['date_to'] = date("Y-m-d");
}

function showForm(){
	global $con,$smarty,$date_from,$date_to,$dept_id,$vendor_id,$branch_id,$qty_per, $sessioninfo;
	
	$filter = array();
	
	$filter[] = "po.po_date between ".ms($date_from)." and ".ms($date_to);
	$filter[] = "po.department_id=".mi($dept_id);
	
	if($vendor_id>0){
        $filter[] = "po.vendor_id=".mi($vendor_id);
	}
	
	if($branch_id>0){
        $filter[] = "po.branch_id=".mi($branch_id);
	}
	
	
	$filter[] = "po.active=1 and po.approved=1";
	
	$filter = join(' and ', $filter);
	//$sql_po = "select id from po where ";
	
	$per = (100+$qty_per)/100;
	$sales_trend_month = array('1','3','6','12');
	$si_info = array();
	$data = array();
	
	$sql = "select po.po_date,po.po_no, po_items.*,si.description,si.sku_item_code, po.currency_code, po.currency_rate
			from po_items 
			left join sku_items si on po_items.sku_item_id=si.id 
			left join po on po_items.po_id=po.id and po_items.branch_id=po.branch_id 
			where $filter
			order by si.sku_item_code";
	//print $sql;
	//die($sql);
	$q1 = $con->sql_query($sql) or die(mysql_error());
	
	while($r = $con->sql_fetchassoc($q1)){
		$row = array();
		$sid = mi($r['sku_item_id']);
		
		if(!$r['currency_rate'])	$r['currency_rate'] = 1;
		$r['sales_trend'] =unserialize($r['sales_trend']);
		$qty = ($r['qty']*$r['order_uom_fraction']) + $r['qty_loose'];
		$qty += ($r['foc']*$r['order_uom_fraction']) + $r['foc_loose'];
		
		$row['currency_code'] = $r['currency_code'];
		$row['base_item_nett_amt'] = $r['item_nett_amt'] * $r['currency_rate'];
		
		$row['po_qty'] = $qty;
		$row['sid'] = $sid;
		
		// record sales trend in po_items
		foreach($sales_trend_month as $m){
			$row['sales_trend']['qty'][$m] = $r['sales_trend']['qty'][$m];
		}
		
		// qty rule: first get m 1 or 3 , see which higher , if zero compare 6 and 12
		$sales_avg_qty = 0;
		if($row['sales_trend']['qty'][1]==0&&$row['sales_trend']['qty'][3]==0){
			if($row['sales_trend']['qty'][6]>$row['sales_trend']['qty'][12])	$sales_avg_qty = $row['sales_trend']['qty'][6];
			else    $sales_avg_qty = $row['sales_trend']['qty'][12];
		}else{
			if($row['sales_trend']['qty'][1]>$row['sales_trend']['qty'][3])	$sales_avg_qty = $row['sales_trend']['qty'][1];
			else    $sales_avg_qty = $row['sales_trend']['qty'][3];
		}

		$row['sales_avg'] = $sales_avg_qty;

		$standard = intval($sales_avg_qty * $per);

		$row['standard'] = $standard;

		if($row['po_qty']<=$standard){
			continue;	// skip this item
		}
		
		// for display
		$row['qty'] += $r['qty'];
		$row['qty_loose'] += $r['qty_loose'];
		$row['foc'] += $r['foc'];
		$row['foc_loose'] += $r['foc_loose'];
		$row['order_price'] += $r['order_price'];
		
		// purchase
		$purchase = ($r['qty']*$r['order_uom_fraction']) + $r['qty_loose'];
		$row['purchase'] +=  $purchase;
		$foc_purchase = ($r['foc']*$r['order_uom_fraction']) + $r['foc_loose'];
		$row['foc_purchase'] += $foc_purchase;
		
		// tax & discount
		//$row['tax'] += $r['tax'];
		//$row['discount'] += $r['discount'];		
		//$row['total_sales'] += ($purchase + $foc_purchase) * ($r['selling_price']/$r['selling_uom_fraction']);
		//$row['nett_amt'] += (($purchase * $r['order_price']) + $r['tax'] - $r['discount'])/$r['order_uom_fraction'];
		$row['total_sales'] += $r['item_total_selling'];
		$row['nett_amt'] += $r['item_nett_amt'];
						
		// po
		$row['po_no'] = $r['po_no'];
		$row['po_id'] = $r['po_id'];
		$row['branch_id'] = $r['branch_id'];
		$row['po_date'] = $r['po_date'];
		
		//$row['gross_amt'] = $purchase * $r['order_price'];
		$row['gross_profit'] = $row['total_sales'] - $row['base_item_nett_amt'];
		if($row['total_sales']!=0){
			$row['gross_profit_per'] = ($row['gross_profit']/$row['total_sales'])*100;
		}
			
		if(!isset($si_info[$sid])){
			$si_info[$sid]['sku_item_code'] = $r['sku_item_code'];
			$si_info[$sid]['description'] = $r['description'];
		}
		
		$data[] = $row;
	}
	$con->sql_freeresult($q1);
	
	//print_r($data);
	$smarty->assign('data',$data);
	$smarty->assign('si_info',$si_info);
}
?>
