<?
/*
revision history
=================
3/12/2008 2:36:14 PM gary
- split after reach max sku ietms in one po.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO', BRANCH_CODE), "/index.php");
include("po.include.php");
set_time_limit(0);

$smarty->assign("PAGE_TITLE", "Purchase Order");

$branch_id = intval($_REQUEST['branch_id']);
if ($branch_id ==''){
	$branch_id = $sessioninfo['branch_id'];
}

init_selection();
get_allowed_user_list();

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
		case 'ajax_load_sku':
			$smarty->assign("show_varieties",1);
			get_vendor_sku("po.from_vendor.show_sku.tpl");
			exit;
					    
		case 'ajax_expand_sku':
		    $smarty->assign("hideheader",1);
		    $smarty->assign("show_varieties",0);
		    expand_sku(intval($_REQUEST['sku_id']), "po.from_vendor.show_sku.tpl");
		    exit;
		    
		case 'ajax_generate_po':
			generate_po();
			exit;

		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}


$smarty->display("po.from_vendor.tpl");
exit;

function generate_po(){
	global $con, $sessioninfo, $smarty, $branch_id, $config;
	
	$counter = 0;
	$vid = intval($_REQUEST['vendor_id']);
	$deptid = intval($_REQUEST['department_id']);
	
	$po_array = array();
	$err = array();
	
	foreach (array_keys($_REQUEST['sel']) as $sid){	
		if($config['po_set_max_items']){
			if ($counter % $config['po_set_max_items'] == 0){
				$con->sql_query("insert into po (branch_id, user_id, vendor_id, department_id, added) values ($branch_id, $sessioninfo[id], $vid, $deptid, CURRENT_TIMESTAMP)");
				$po_id = $con->sql_nextid();
				log_br($sessioninfo['id'], 'PURCHASE ORDER', $po_id, "Create PO from Vendor SKU (ID#$po_id)");
				$po_array[] = $po_id;
			}	
		}
		else{
			if ($counter==0){
				$con->sql_query("insert into po (branch_id, user_id, vendor_id, department_id, added) values ($branch_id, $sessioninfo[id], $vid, $deptid, CURRENT_TIMESTAMP)");
				$po_id = $con->sql_nextid();
				log_br($sessioninfo['id'], 'PURCHASE ORDER', $po_id, "Create PO from Vendor SKU (ID#$po_id)");
				$po_array[] = $po_id;
			}		
		}

		$r=get_items_detail($sid,$branch_id);
		$sales = get_sales_trend($sid);
		$balance=get_stock_balance($sid);
		$r=array_merge($r, $sales,$balance);
		
		$item['po_id'] = $po_id;
		$item['branch_id'] = $branch_id;
		$item['user_id'] = $sessioninfo['id'];
		$item['sku_item_id'] = $sid;
		$item['sales_trend'] = serialize($r['sales_trend']);
		$item['stock_balance'] = serialize($r['stock_balance']);
		$item['cost_indicate']=$r['cost_indicate'];
		$item['selling_price']=$r['selling_price'];	
	    $item['order_price'] = $r['order_price'];
					
	 	if ($r['artno']) 
			$item['artno_mcode'] = $r['artno'];
		else 
			$item['artno_mcode'] = $r['mcode'];
	
	    if($r['order_uom_fraction']) 
			$item['order_uom_fraction'] = $r['order_uom_fraction'];	
		else 
			$item['order_uom_fraction'] = '1';
		
	    if($r['uom_id'])
			$item['order_uom_id'] = $r['uom_id'];	
		else 
			$item['order_uom_id'] = '1';
		   
	    if (!isset($r['resell_price']))	
			$item['resell_price'] = $r['resell_price'];
			
    	$con->sql_query("insert into po_items " . mysql_insert_by_field($item, array('po_id', 'branch_id', 'user_id', 'sku_item_id', 'artno_mcode', 'order_uom_fraction', 'selling_price', 'resell_price', 'order_price', 'order_uom_id', 'cost_indicate', 'sales_trend', 'stock_balance')));		
		$counter++;
	}	
	$smarty->assign("err", $err);
	$smarty->assign("po", $po_array);
	$smarty->display("po.from_vendor.result.tpl");
}
?>
