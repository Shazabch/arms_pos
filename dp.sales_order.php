<?php
/*
4/10/2013 5:08 PM Andy
- Change to default tick "Use Promotion Price".
- Remove batch code.

5/14/2013 3:07 PM Andy
- Remove use promotion price from debtor sales order.
- Enhance when reload item list, it will use mprice for selling price if debtor got set mprice type and the item got mprice selling.

5/29/2013 2:09 PM Andy
- Enhance to allow user to manually add item.
- Enhanced to have delivered tab.
*/
include('include/common.php');
$maintenance->check(198);

include_once('dp.sales_order.include.php');

if(!$dp_session)	js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class SALES_ORDER extends Module{
	var $order_list_size = 30;
	
	function __construct($title){
		global $dp_session, $config, $smarty;
		
		init_so_selection();
		
		$this->bid = $dp_session['branch_id'];
		
		parent::__construct($title);
	}
	
	function _default(){
		global $dp_session, $smarty;
		
		
		$this->display();
	}
	
	function ajax_list_sel(){
        global $con, $dp_session, $smarty, $LANG;
        
        $t = mi($_REQUEST['t']);
		$p = mi($_REQUEST['p']);
		$size = $this->order_list_size;
		$start = $p*$size;
		
		$filter = array();
		$get_receipt_no = false;
		switch($t){
			case 1:	// saved order
				$filter[] = "so.status=0 and so.active=1";
				break;
			case 2: // waiting for approve
				$filter[] = "so.status=1 and so.approved=0 and so.active=1";
				break;
			case 3: // cancelled / terminted
				$filter[] = "(so.status=4 or so.status=5) and so.active=1";
				break;
			case 4: // approved
			    $filter[] = "so.status=1 and so.approved=1 and so.active=1 and so.delivered=0";
			    break;
			case 5: // rejected
			    $filter[] = "so.status=2 and so.approved=0 and so.active=1 and so.delivered=0";
			    break;
			case 6: // delivered
			    $filter[] = "so.status=1 and so.approved=1 and so.active=1 and so.delivered=1 and so.exported_to_pos=0";
			    break;
			case 8: // exported to PO
			    $filter[] = "so.status=1 and so.approved=1 and so.active=1 and so.delivered=0 and so.exported_to_pos=1"; //change this later
				$get_receipt_no = true;
			    break;
			case 99: // search items
				$str = $_REQUEST['search_str'];
				if(!$str)	die('Cannot search empty string');
				$filter_or[] = "so.batch_code=".ms($str);
				$filter_or[] = "so.order_no=".ms($str);
				$filter_or[] = "so.cust_po=".ms($str);
				$filter_or[] = "so.id=".ms($str);
				$filter[] = "(".join(' or ',$filter_or).")";
				break;
			default:
				die('Invalid Page');
		}
		//if(BRANCH_CODE!='HQ')	
		$filter[] = "so.branch_id=$dp_session[branch_id]";
		$filter[] = "so.debtor_id=$dp_session[id]";
		
		$filter = "where ".join(' and ',$filter);
		
		$con->sql_query("select count(*) from sales_order so
left join branch on branch.id=so.branch_id
left join debtor on debtor.id=so.debtor_id
left join user on user.id=so.user_id
left join branch_approval_history bah on bah.id = so.approval_history_id and bah.branch_id = so.branch_id
$filter") or die(mysql_error());
		$total_rows = $con->sql_fetchfield(0);

		if($start>=$total_rows){
			$start = 0;
			$_REQUEST['p'] = 0;
		}
		$limit = "limit $start, $size";
		$order = "order by so.last_update desc";

		$total_page = ceil($total_rows/$size);

		$sql = "select so.*,user.u as username, branch.report_prefix as branch_prefix, branch.code as branch_code,bah.approvals, bah.approval_order_id,debtor.code as debtor_code,debtor.description as debtor_description, (select sum(soi.do_qty) from sales_order_items soi where soi.branch_id=so.branch_id and soi.sales_order_id=so.id) as delivered_qty
from sales_order so
left join branch on branch.id=so.branch_id
left join debtor on debtor.id=so.debtor_id
left join user on user.id=so.user_id
left join branch_approval_history bah on bah.id = so.approval_history_id and bah.branch_id = so.branch_id
$filter $order $limit";
		//print $sql;
		$q1 = $con->sql_query($sql);
		$order_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			/*if($r['po_used'] && $r['po_ref']){
				$tmp_arr = explode("|", $r['po_ref']);
				if($tmp_arr){
					foreach($tmp_arr as $po_ref){
						if(!$po_ref)	continue;
						
						list($tmp_bid, $tmp_po_id) = explode("-", $po_ref);
						if($tmp_bid && $tmp_po_id){
							$tmp_bcode = get_branch_code($tmp_bid);
							
							$r['po_list'][] = array(
								'bid' => $tmp_bid,
								'po_id' => $tmp_po_id,
								'code' => $tmp_bcode.sprintf("%05d", $tmp_po_id)
							);
						}
					}
				}
			}
			if ($get_receipt_no) {
				// get sales order exported to pos
				$r['receipt_details'] = get_sales_order_receipt_list($r['branch_id'], $r['id']);
			}*/
			$order_list[] = $r;
		}
		$con->sql_freeresult($q1);
		//print_r($order_list);
		$smarty->assign('order_list', $order_list);
		$smarty->assign('total_page',$total_page);
		$smarty->display("dp.sales_order.list.tpl");
		
	}
	
	function view(){
	    global $smarty;

		$this->readonly = 1;
	    $smarty->assign('readonly', $this->readonly);
		$this->open();
				
	}
	
	function open(){
		global $con, $dp_session, $smarty, $LANG;
		
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		if(!$branch_id)	$branch_id = $dp_session['branch_id'];
		
		if($branch_id != $dp_session['branch_id'])	js_redirect("Invalid Sales Order Branch.", "/index.php");
		
		if($id&&$branch_id){    // exists order
            $form = load_order_header($branch_id, $id, true);

            if(($form['approved']||!$form['active'] || ($form['status'] && $form['status']!=2))&& $_REQUEST['a']!='view'){

	            header("Location: $_SERVER[PHP_SELF]?a=view&id=$id&branch_id=$branch_id");
				exit;
			}
			
			$form['item_list'] = load_order_items($branch_id, $id);				
		}else{  // new order
			if(!$id){
                $id=time();
				$form['id']=$id;
				$form['branch_id'] = $dp_session['branch_id'];
				//$form['use_promo_price'] = 1;
				$form['selling_type'] = $dp_session['debtor_mprice_type'];
			}
		}
		
		// load sku group
		if(!$this->readonly){
			$sku_group_list = array();
			$con->sql_query("select * from sku_group order by code,description");
			while($r = $con->sql_fetchassoc()){
				$sku_group_list[] = $r;
			}
			$con->sql_freeresult();
			$smarty->assign('sku_group_list', $sku_group_list);
		}
		
		
		$smarty->assign('PAGE_TITLE', $this->title.' - '.(is_new_id($id)?'New Order':$form['order_no']));
		$smarty->assign('form', $form);
		$smarty->display('dp.sales_order.open.tpl');
	}
	
	function ajax_save($is_confirm = false){
		global $con, $dp_session, $smarty, $LANG;
		
		$form = $_REQUEST;

		// checking
		//if(!trim($form['batch_code'])) die($LANG['SO_NO_BATCH_CODE']);
		if(!trim($form['order_date'])) die($LANG['SO_INVALID_DATE']);
		
		if(is_new_id($form['id'])){
			if(!$form['user_id'])	die('Please select Assigned Owner');
		}
		
		if(!$form['item_list'])	die("No item in the list");
		
		// save sales order
		$upd = array();
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['batch_code'] = $form['batch_code'];
		$upd['order_date'] = $form['order_date'];
		$upd['cust_po'] = $form['cust_po'];
		$upd['total_ctn'] = $form['total_ctn'];
		$upd['total_pcs'] = $form['total_pcs'];
		$upd['total_amount'] = $form['total_amount'];
		$upd['total_qty'] = $form['total_qty'];
		$upd['remark'] = $form['remark'];
		//$upd['use_promo_price'] = $form['use_promo_price'];
		$upd['sheet_discount'] = $form['sheet_discount'];
		$upd['sheet_discount_amount'] = $form['sheet_discount_amount'];
		$upd['selling_type'] = $form['selling_type'];
		
		$upd['status'] = 0;
		//if($is_confirm)	$upd['status'] = 1;
		$upd['approved'] = 0;
		
		if(is_new_id($form['id'])){	// new sales order
			$upd['added'] = 'CURRENT_TIMESTAMP';
            $upd['user_id'] = $form['user_id'];
            $upd['debtor_id'] = $upd['create_by_debtor_id'] = $dp_session['id'];
            $bid = $upd['branch_id'] = $dp_session['branch_id'];
            
            $con->sql_query("insert into sales_order ".mysql_insert_by_field($upd));
            $so_id = $con->sql_nextid();
		}else{	// existing sales order
			$bid = $form['branch_id'];
			$so_id = $form['id'];
			
			$con->sql_query("update sales_order set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$so_id");
		}
		
		if(!$form['order_no']){
            $formatted = sprintf("%05d",$so_id);
		    //select report prefix from branch
			$con->sql_query("select report_prefix from branch where id = ".mi($bid));
			$b = $con->sql_fetchrow();
			$form['order_no'] = $b['report_prefix'].$formatted;
			$con->sql_query("update sales_order set order_no=".ms($form['order_no'])." where branch_id=$bid and id=$so_id");
		}
		
		// save items
		$first_so_item_id = 0;
		foreach($form['item_list'] as $so_item_id => $r){
			
			$upd2 = array();
			$upd2['branch_id'] = $bid;
			$upd2['sales_order_id'] = $so_id;
			$upd2['sku_item_id'] = $r['sku_item_id'];
			$upd2['selling_price'] = $r['selling_price'];
			$upd2['uom_id'] = $r['uom_id'];
			$upd2['ctn'] = $r['ctn'];
			$upd2['pcs'] = $r['pcs'];
			$upd2['cost_price'] = $r['cost_price'];
			$upd2['stock_balance'] = $r['stock_balance'];
			$upd2['item_discount'] = $r['item_discount'];
			$upd2['item_discount_amount'] = $r['item_discount_amount'];
			$upd2['bom_ref_num'] = $r['bom_ref_num'];
			$upd2['bom_qty_ratio'] = $r['bom_qty_ratio'];
			$upd2['do_qty'] = $r['do_qty'];
			
			$con->sql_query("insert into sales_order_items ".mysql_insert_by_field($upd2));
			if(!$first_so_item_id)	$first_so_item_id = $con->sql_nextid();
		}
		
		if($first_so_item_id){
			$con->sql_query("delete from sales_order_items where branch_id=$bid and sales_order_id=$so_id and id<$first_so_item_id");
		}
		
		$ret = array();
		$ret['ok'] = 1;
		
		if ($is_confirm){
			$msg = "Sales Order ID#$so_id confirmed";
		}else{
			$msg = "Sales Order ID#$so_id saved";
		}
		
		log_dp($dp_session['id'], 'SALES ORDER', $so_id, $msg);
		
		//$ret['msg'] = $msg;
		$ret['id'] = $so_id;
		
		print json_encode($ret);
	}
	
	function ajax_reload_item_list_by_sku_group(){
		global $con, $dp_session, $smarty, $LANG;
		
		$sku_group_id = trim($_REQUEST['sku_group_id']);
		if(!$sku_group_id)	die("Please select SKU Group first.");
		
		list($bid, $sgid) = explode(",", $sku_group_id);
		if(!$bid || !$sgid)	die("Invalid SKU Group ID.");
		
		$params = array();
		$params['branch_id'] = $_REQUEST['branch_id'];
		$params['sales_order_id'] = $_REQUEST['sales_order_id'];
		$params['date'] = $_REQUEST['date'];
		$params['use_promo_price'] = mi($_REQUEST['use_promo_price']);
		
		$q1 = $con->sql_query("select si.id as sid
from sku_group_item sgi
join sku_items si on si.sku_item_code=sgi.sku_item_code
where sgi.branch_id=$bid and sgi.sku_group_id=$sgid
order by sgi.sku_item_code");
		
		$ret = array();
		
		while($r = $con->sql_fetchassoc($q1)){
			$sid = mi($r['sid']);
			
			if(!$item = $this->construct_new_item_row($sid, $params))	die("Got error on getting sku information: SKU Item ID#$sid");
		
			$smarty->assign('item', $item);
			$ret['html'] .= $smarty->fetch('dp.sales_order.open.sheet.item_row.tpl');	
		}
		$con->sql_freeresult($q1);
		
		$ret['ok'] = 1;
		$ret['mprice_type'] = $dp_session['debtor_mprice_type'];
		
		print json_encode($ret);
	}
	
	function ajax_show_item_list_by_sku_group(){
		global $con, $dp_session, $smarty, $LANG;
		
		$sku_group_id = trim($_REQUEST['sku_group_id']);
		if(!$sku_group_id)	die("Please select SKU Group first.");
		
		list($bid, $sgid) = explode(",", $sku_group_id);
		if(!$bid || !$sgid)	die("Invalid SKU Group ID.");
		
		$q1 = $con->sql_query("select si.id as sid, si.sku_item_code, si.mcode, si.artno, si.description
from sku_group_item sgi
join sku_items si on si.sku_item_code=sgi.sku_item_code
where sgi.branch_id=$bid and sgi.sku_group_id=$sgid
order by sgi.sku_item_code");
		
		$ret = array();
		$item_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$item_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		
		$ret['ok'] = 1;
		
		$smarty->assign('item_list', $item_list);
		$ret['html'] = $smarty->fetch('dp.sales_order.open.sku_group_item_list.tpl');
		
		print json_encode($ret);
	}
	
	private function construct_new_item_row($sid, $params = array()){
		global $con, $dp_session, $smarty, $LANG;
		
		$sid = mi($sid);
		$sales_order_id = mi($params['sales_order_id']);
		$bid = mi($params['branch_id']);
		$date = $params['date'];
		$use_promo_price = mi($params['use_promo_price']);
		if(!$date)	$date = date("Y-m-d");
		
		if(!$sid)	return false;
		
		if(!$this->new_item_id)	$this->new_item_id = time();
		
		
		$con->sql_query("select si.id as sku_item_id,si.sku_item_code, ifnull(si.artno,si.mcode) as artno_mcode, si.description as sku_description, si.doc_allow_decimal, puom.code as packing_uom_code, ifnull(sic.grn_cost, si.cost_price) as cost_price, sic.qty as stock_balance, ifnull(sip.price,si.selling_price) as selling_price, simp.price as mprice
		from sku_items si
		left join uom puom on puom.id=si.packing_uom_id
		left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
		left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
		left join sku_items_mprice simp on simp.branch_id=$bid and simp.sku_item_id=si.id and simp.type=".ms($dp_session['debtor_mprice_type'])."
		where si.id=$sid");
		$item = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$item)	return false;
		
		$this->new_item_id++;
		
		$new_item_id = $this->new_item_id;
		
		$item['id'] = $new_item_id;
		$item['uom_id'] = 1;
		$item['uom_code'] = 'EACH';
		$item['uom_fraction'] = 1;
		$item['branch_id'] = $bid;
		$item['sales_order_id'] = $sales_order_id;
		
		if($item['mprice']>0)	$item['selling_price'] = $item['mprice'];	// overwrite selling price to mprice
		/*if($use_promo_price){
			$item['selling_price'] = get_lowest_price($bid, $sid, $date);
		}*/
		
		return $item;
	}
	
	function reload_selling_price(){
		global $con, $dp_session, $smarty, $LANG;
		
		$form = $_REQUEST;
		
		//print_r($form);
		
		if(!$form['item_list'])	die('No item in the list.');
		
		$ret = array();
		foreach($form['item_list'] as $so_item_id => $r){
			$selling = 0;
			
			if($form['use_promo_price']){
				$selling = get_lowest_price($form['branch_id'], $r['sku_item_id'], $form['order_date']);
			}else{
				$con->sql_query("select ifnull(sip.price,si.selling_price) as selling_price
				from sku_items si
				left join sku_items_price sip on sip.branch_id=".mi($form['branch_id'])." and sip.sku_item_id=si.id
				where si.id=".mi($r['sku_item_id']));
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				$selling = $tmp['selling_price'];
			}
			
			$ret['item_list'][$so_item_id]['selling_price'] = $selling;
		}
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_delete_so(){
		global $con, $dp_session, $smarty, $LANG;
		
		$bid = mi($_REQUEST['branch_id']);
		$id = mi($_REQUEST['id']);
		$reason = trim($_REQUEST['reason']);
		
		$con->sql_query("select id from sales_order where debtor_id=$dp_session[id] and id=$id and branch_id=$bid");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$tmp)	die("Invalid Sales Order");
		
		$upd = array();
		$upd['reason'] = $reason;
		$upd['status'] = 4;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
	    $con->sql_query("update sales_order set ".mysql_update_by_field($upd)." where id=$id and branch_id=$bid");
	    
		log_dp($dp_session['id'], 'SALES ORDER', $so_id, "ID#$id Deleted.");
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_add_item(){
		global $con, $dp_session, $smarty, $LANG;
		
		$form = $_REQUEST;
		
		//print_r($form);
		
		$sid_list = $form['sid_list'];
		
		if(!$sid_list || !is_array($sid_list))	die("Invalid item");
		
		$params = array();
		$params['branch_id'] = $form['branch_id'];
		$params['sales_order_id'] = $form['id'];
		$params['date'] = $form['order_date'];
		$params['use_promo_price'] = mi($form['use_promo_price']);
		
		$ret = array();
		$ret['html'] = '';
		
		foreach($sid_list as $sid){	
			if(!$item = $this->construct_new_item_row($sid, $params))	die("Got error on getting sku information: SKU Item ID#$sid");
		
			$smarty->assign('item', $item);
			$ret['html'] .= $smarty->fetch('dp.sales_order.open.sheet.item_row.tpl');	
		}
		$con->sql_freeresult($q1);
		
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
}

$SALES_ORDER = new SALES_ORDER('Sales Order');
?>
