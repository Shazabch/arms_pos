<?php
/*
1/21/2013 4:32 PM Andy
- Remove department dropdown, auto use the first item to become department if found repacking still dont have dept id.

2/4/2013 11:12 AM Justin
- Enhanced to pickup doc_allow_decimal.

4/2/2013 10:34 AM Andy
- Fix grn cannot be generate after confirm.

12/20/2019 10:03 AM Justin
- Enhanced to insert ID manually for some tables that uses auto increment.

1/8/2020 10:20 AM William
- Enhanced to insert id manually for adjustment tables that uses auto increment.

4/2/2020 4:49 PM William
- Enhanced to capture print log to log_vp.
*/
include('include/common.php');
$maintenance->check(177);

if(!$vp_session)	js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class REPACKING extends Module{
	var $bid;
	var $page_size = 30;
	
	function __construct($title){
		global $vp_session, $config, $smarty;
		
		$this->bid = $vp_session['branch_id'];
		
		parent::__construct($title);
	}
	
	function _default(){
		global $vp_session, $smarty;
		
		$this->display();
	}
	
	function ajax_list_sel(){
		global $vp_session, $smarty, $con, $config;
		
		$t = mi($_REQUEST['tab_num']);
		$p = mi($_REQUEST['page_num']);
		$size = $this->page_size;
		$start = $p*$size;
		
		$filter = array();
		switch($t){
			case 1:	// saved 
				$filter[] = "rep.active=1 and rep.status=0 and rep.approved=0";
				break;
			case 2: // completed
				$filter[] = "rep.active=1 and rep.status=1 and rep.approved=1";
				break;
			case 999: // search items
				$str = $_REQUEST['search_str'];
				if(!$str)	die('Cannot search empty string');
				$filter_or[] = "rep.repacking_date=".ms($str);
				$filter_or[] = "rep.id=".ms($str);
				$filter[] = "(".join(' or ',$filter_or).")";
				break;
			default:
				die('Invalid Page');
		}
		$filter[] = "rep.branch_id=$this->bid";
		$filter[] = "rep.vendor_id=".mi($vp_session['id']);
		$filter = "where ".join(' and ',$filter);
		
		// check count first
		$con->sql_query("select count(*) 
		from vp_repacking rep
		$filter");
		//print $sql;
		$total_rows = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		
		if($start>=$total_rows){
			$start = 0;
			$_REQUEST['page_num'] = 0;
		}
		
		// start query to get item
		
		$limit = "limit $start, $size";
		$order = "order by rep.last_update desc";

		$total_page = ceil($total_rows/$size);

		$sql = "select rep.*, c.description as dept_desc
		from vp_repacking rep
		left join category c on c.id=rep.dept_id
		$filter $order $limit";
		//print $sql;
		$q1 = $con->sql_query($sql);
		$repacking_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$repacking_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('total_page',$total_page);
		$smarty->assign('repacking_list',$repacking_list);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('vp.repacking.list.tpl');
		
		print json_encode($ret);
	}
	
	function view(){
		global $vp_session, $smarty, $con, $config;
		
		$bid = mi($_REQUEST['branch_id']);
		$id = mi($_REQUEST['id']);
		
		$this->show_form($bid, $id);
	}
	
	function open(){
		global $vp_session, $smarty, $con, $config;
		
		$bid = mi($_REQUEST['branch_id']);
		$id = mi($_REQUEST['id']);
		
		$smarty->assign('can_edit', 1);
		$this->show_form($bid, $id);
	}
	
	private function show_form($bid, $id){
		global $vp_session, $smarty, $con, $config;
		
		if(!$bid)	$bid = $this->bid;
		
		if($id>0){	// load form
			$form = $this->load_repacking_header($bid, $id);
			
			if(!$form){
				js_redirect("Repacking ID#$id Not Found", $_SERVER['PHP_SELF']);
			}
			
			$form['group_list'] = $this->load_repacking_group_items($bid, $id);
			
			if($_REQUEST['a']!='view' && !($form['active'] && !$form['status'] && !$form['approved'])){
				header("Location: $_SERVER[PHP_SELF]?a=view&branch_id=$bid&id=$id");
				exit;
			}
		}else{	// new
			$form = array();
			$form['id'] = time();
			$form['repacking_date'] = date("Y-m-d");
		}
		
		// load department list
		/*$dept_list = array();
		$con->sql_query("select id,code,description,active from category where level=2 order by description");
		while($r = $con->sql_fetchassoc()){
			if($r['active'] || ($form['dept_id'] && $form['dept_id'] == $r['id'])){
				$dept_list[$r['id']] = $r;
			}
		}
		$con->sql_freeresult();
		$smarty->assign('dept_list', $dept_list);*/

		//print_r($form);		
		$smarty->assign('form', $form);
		$smarty->display('vp.repacking.open.tpl');
	}
	
	private function get_item_details($bid, $sid){
		global $vp_session, $smarty, $con, $config;
		
		$con->sql_query("select si.id as sku_item_id, si.sku_id, si.sku_item_code, si.artno, si.mcode, si.description, ifnull(sic.grn_cost,si.cost_price) as cost, ifnull(sip.price, si.selling_price) as latest_selling_price, uom.code as packing_uom_code, si.doc_allow_decimal
		from sku_items si 
		left join sku_items_cost sic on sic.branch_id=".mi($bid)." and sic.sku_item_id=si.id
		left join sku_items_price sip on sip.branch_id=".mi($bid)." and sip.sku_item_id=si.id
		left join uom on uom.id=si.packing_uom_id
		where si.id=$sid");
		$item = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$item)	return -1;
		
		return $item;
	}
	
	function ajax_add_new_lose_item(){
		global $vp_session, $smarty, $con, $config;
		
		$curr_max_row_id = mi($_REQUEST['curr_max_row_id']);
		$group_id = mi($_REQUEST['group_id']);
		$sid = mi($_REQUEST['sid']);
		
		$item = $this->get_item_details($this->bid, $sid);
		
		if($item== -1)	die("Invalid Item");
		
		$new_row_id = $curr_max_row_id + 1;
		
		$ret = array();
		$ret['ok'] = 1;
		
		$smarty->assign('can_edit', 1);
		$smarty->assign('group_id', $group_id);
		$smarty->assign('row_id', $new_row_id);
		$smarty->assign('item', $item);
		$ret['html'] = $smarty->fetch('vp.repacking.open.item_group.lose_item_row.tpl');
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		
		print json_encode($ret);
	}
	
	function ajax_add_new_pack_item(){
		global $vp_session, $smarty, $con, $config;
		
		$curr_max_row_id = mi($_REQUEST['curr_max_row_id']);
		$group_id = mi($_REQUEST['group_id']);
		$sid = mi($_REQUEST['sid']);
		
		$item = $this->get_item_details($this->bid, $sid);
		
		if($item== -1)	die("Invalid Item");
		
		$new_row_id = $curr_max_row_id + 1;
		
		$ret = array();
		$ret['ok'] = 1;
		
		$smarty->assign('can_edit', 1);
		$smarty->assign('group_id', $group_id);
		$smarty->assign('row_id', $new_row_id);
		$smarty->assign('item', $item);
		$ret['html'] = $smarty->fetch('vp.repacking.open.item_group.pack_item_row.tpl');
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		
		print json_encode($ret);
	}
	
	function ajax_confirm(){
		$this->ajax_save(true);
	}
	
	function ajax_save($is_confirm = false){
		global $vp_session, $smarty, $con, $config;
		
		$bid = $this->bid;
		$form = $_REQUEST;
		//print_r($form);
		
		if($form['branch_id'] != $bid)	die("Invalid Repacking Branch.");
		if(!$form['repacking_date'])	die("Please select date.");
		
		// check got group or not
		if(!$form['items'])	die("You must have at least 1 group got data.");
		
		foreach($form['items'] as $group_id => $group_items){
			if(!$group_items['lose'])	die("Every group must have at least 1 Lose Item.");
			if(!$group_items['pack'])	die("Every group must have 1 Pack Item.");
		}
		
		if(!$form['dept_id']){
			// loop pack item to get department for first item
			foreach($form['items'] as $group_id => $group_items){			
				foreach($group_items['pack'] as $row_id => $pack_item){				
					$q1 = $con->sql_query("select c.department_id
										  from sku_items si
										  left join sku on sku.id = si.sku_id
										  left join category c on c.id = sku.category_id
										  where si.id = ".mi($pack_item['sku_item_id']));
					$tmp = $si_info = $con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);
					$form['dept_id'] = $si_info['department_id'];
				}
			}
		}
		
		$upd = array();
		$upd['repacking_date'] = $form['repacking_date'];
		$upd['dept_id'] = $form['dept_id'];
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['remark'] = $form['remark'];
		
		// no problem, proceed to update
		if(is_new_id($form['id'])){
			$upd['branch_id'] = $bid;	
			$upd['vendor_id'] = $vp_session['id'];
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into vp_repacking ".mysql_insert_by_field($upd));
			$id = $con->sql_nextid();
		}else{
			$id = $form['id'];	
			
			$this->check_must_can_edit($bid, $id);
			
			$con->sql_query("update vp_repacking set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$id");
		}
		
		$first_lose_id = 0;
		$first_pack_id = 0;
		foreach($form['items'] as $group_id => $group_items){
			// loop for each lose item
			foreach($group_items['lose'] as $row_id => $lose_item){	
				$upd = array();
				$upd['branch_id'] = $bid;
				$upd['repacking_id'] = $id;
				$upd['group_id'] = $group_id;
				$upd['sku_item_id'] = $lose_item['sku_item_id'];
				$upd['cost'] = $lose_item['cost'];
				$upd['qty'] = $lose_item['qty'];
				
				$con->sql_query("insert into vp_repacking_lose_items ".mysql_insert_by_field($upd));
				if(!$first_lose_id)	$first_lose_id = $con->sql_nextid();
			}
			
			// loop for each pack item
			foreach($group_items['pack'] as $row_id => $pack_item){	
				$upd = array();
				$upd['branch_id'] = $bid;
				$upd['repacking_id'] = $id;
				$upd['group_id'] = $group_id;
				$upd['sku_item_id'] = $pack_item['sku_item_id'];
				$upd['cost'] = $pack_item['cost'];
				$upd['qty'] = $pack_item['qty'];
				$upd['misc_cost'] = $pack_item['misc_cost'];
				$upd['calc_cost'] = $pack_item['calc_cost'];
				
				$con->sql_query("insert into vp_repacking_pack_items ".mysql_insert_by_field($upd));
				if(!$first_pack_id)	$first_pack_id = $con->sql_nextid();
			}
		}
		
		if($first_lose_id){
			$con->sql_query("delete from vp_repacking_lose_items where branch_id=$bid and repacking_id=$id and id<$first_lose_id");
		}
		if($first_pack_id){
			$con->sql_query("delete from vp_repacking_pack_items where branch_id=$bid and repacking_id=$id and id<$first_pack_id");
		}
		
		$str = ($is_confirm ? 'Confirm':'Save').": Repacking ID#$id";
		log_br($vp_session['vp']['link_user_id'], 'REPACKING', $id, $str);
		log_vp($vp_session['id'], "REPACKING", $id, $str);
		
		// confirm
		if($is_confirm){
			$con->sql_query("select * from vp_repacking where branch_id=$bid and id=$id");
			$vp_repacking = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$approval_history_id = mi($vp_repacking['approval_history_id']);
			
			$ah_ins = array();
			$ah_ins['approval_flow_id'] = 0;
			$ah_ins['ref_table'] = "vp_repacking";
			$ah_ins['ref_id'] = $id;
			$ah_ins['active'] = 1;
			$ah_ins['flow_approvals'] = $ah_ins['approved_by'] = "|".$vp_session['vp']['link_user_id']."|";
			$ah_ins['approvals'] = "|";
			$ah_ins['notify_users'] = "||";
			$ah_ins['status'] = 1;
			$ah_ins['approval_order_id'] = 4;
				
			if(!$approval_history_id){	// need to create approval history
				$ah_ins['branch_id'] = $bid;
				$ah_ins['added'] = "CURRENT_TIMESTAMP";
				
				$con->sql_query("insert into branch_approval_history ".mysql_insert_by_field($ah_ins));
				$approval_history_id = $con->sql_nextid();
			}else{
				$con->sql_query("update branch_approval_history set ".mysql_update_by_field($ah_ins)."where branch_id=$bid and id=$approval_history_id");
			}
			
			// insert approval history item
			$ah_ins = array();
			$ah_ins['approval_history_id'] = $approval_history_id;
			$ah_ins['user_id'] = $vp_session['vp']['link_user_id'];
			$ah_ins['log'] = "Approved";
			$ah_ins['timestamp'] = "CURRENT_TIMESTAMP";
			$ah_ins['status'] = 1;
			$ah_ins['branch_id'] = $bid;
			
			$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($ah_ins));
				
			// update repacking status
			$upd = array();
			$upd['active'] = 1;
			$upd['status'] = 1;
			$upd['approved'] = 1;	// direct approved
			$upd['approval_history_id'] = $approval_history_id;
			$con->sql_query("update vp_repacking set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$id");
			
			// generate adjustment, grn and etc.
			$this->generate_repacking_data($bid, $id);
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['repacking_id'] = $id;
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		
		print json_encode($ret);
	}
	
	private function load_repacking_header($bid, $id){
		global $vp_session, $smarty, $con, $config;
		
		$con->sql_query("select * from vp_repacking where branch_id=$bid and id=$id and vendor_id=$vp_session[id]");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if ($form['approval_history_id']>0){
			$q0=$con->sql_query("select i.timestamp, i.log, i.status, user.u
from branch_approval_history_items i
left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
left join user on i.user_id = user.id
where h.ref_table = 'vp_repacking' and i.branch_id = $bid and i.approval_history_id = $form[approval_history_id] 
order by i.timestamp");
			$smarty->assign("approval_history", $con->sql_fetchrowset($q0));
			$con->sql_freeresult($q0);
		}
			
		return $form;
	}
	
	private function load_repacking_group_items($bid, $id){
		global $vp_session, $smarty, $con, $config;
		
		$group_list = array();
		
		// lose item
		$q1 = $con->sql_query("select li.*, si.sku_item_code, si.sku_id, si.artno, si.mcode, si.description,uom.code as packing_uom_code, si.doc_allow_decimal
		from vp_repacking_lose_items li 
		left join sku_items si on si.id=li.sku_item_id
		left join uom on uom.id=si.packing_uom_id
		where li.branch_id=$bid and li.repacking_id=$id 
		order by li.id");
		while($r = $con->sql_fetchassoc($q1)){
			$group_list[$r['group_id']]['lose'][] = $r;
		}
		$con->sql_freeresult($q1);
		
		// pack item
		$q1 = $con->sql_query("select pi.*, si.sku_item_code, si.sku_id, si.artno, si.mcode, si.description,uom.code as packing_uom_code, ifnull(sip.price, si.selling_price) as latest_selling_price, si.doc_allow_decimal
		from vp_repacking_pack_items pi 
		left join sku_items si on si.id=pi.sku_item_id
		left join uom on uom.id=si.packing_uom_id
		left join sku_items_price sip on sip.branch_id=pi.branch_id and sip.sku_item_id=pi.sku_item_id
		where pi.branch_id=$bid and pi.repacking_id=$id 
		order by pi.id");
		while($r = $con->sql_fetchassoc($q1)){
			$group_list[$r['group_id']]['pack'][] = $r;
		}
		$con->sql_freeresult($q1);
		
		return $group_list;
	}
	
	private function check_must_can_edit($bid, $id){
		global $vp_session, $smarty, $con, $config;
	
	    $con->sql_query("select active, status, approved from vp_repacking where branch_id=".mi($bid)." and id=".mi($id));
	
		if($r = $con->sql_fetchrow()){  // invoice exists
			if(!$r['active']){  // inactive
	            display_redir($_SERVER['PHP_SELF'], "Repacking", "Repacking ID#$id is in-active.");
			}else{
				if($r['status'] || $r['approved']){
					display_redir($_SERVER['PHP_SELF'], "Repacking", "Repacking ID#$id cannot be change.");
				}
			}
		}
		$con->sql_freeresult();
	}
	
	private function generate_repacking_data($bid, $id){
		global $vp_session, $smarty, $con, $config, $appCore;
		
		// get header
		$form = $this->load_repacking_header($bid, $id);
		$form['group_list'] = $this->load_repacking_group_items($bid, $id);
		
		// get lose item
		$lose_item_list = array();
		$q1 = $con->sql_query("select li.*, ifnull(sip.price, si.selling_price) as latest_selling_price,sic.qty as latest_stock_balance, si.doc_allow_decimal
		from vp_repacking_lose_items li 
		left join sku_items si on si.id=li.sku_item_id
		left join sku_items_cost sic on sic.branch_id=li.branch_id and sic.sku_item_id=li.sku_item_id
		left join sku_items_price sip on sip.branch_id=li.branch_id and sip.sku_item_id=li.sku_item_id
		where li.branch_id=$bid and li.repacking_id=$id 
		order by li.id");
		while($r = $con->sql_fetchassoc($q1)){
			$lose_item_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$remark = "GENERATE BY VENDOR PORTAL REPACKING ID#$id";
		
		// create adjustment
		$adj = array();
		$adj['id'] = $appCore->generateNewID("adjustment", "branch_id = ".mi($bid));
		$adj['branch_id'] = $bid;
		$adj['user_id'] = $vp_session['vp']['link_user_id'];
		$adj['dept_id'] = $form['dept_id'];
		$adj['adjustment_date'] = $form['repacking_date'];
		$adj['adjustment_type'] = 'REPACKING';
		$adj['status'] = $adj['active'] = $adj['approved'] = 1;
		$adj['added'] = $adj['last_update'] = 'CURRENT_TIMESTAMP';
		$adj['remark'] = $remark;
		
		$con->sql_query("insert into adjustment ".mysql_insert_by_field($adj));
		$adj_id = $adj['id'];
		
		log_br($vp_session['vp']['link_user_id'], 'ADJUSTMENT', $adj_id, "Generated: ID#$adj_id");
		log_vp($vp_session['id'], "ADJUSTMENT", $adj_id, "Generated: ID#$adj_id");
		
		foreach($lose_item_list as $item){	// loop for each lose item
			$ai = array();
			$ai['id'] = $appCore->generateNewID("adjustment_items", "branch_id = ".mi($bid));
			$ai['branch_id'] = $bid;
			$ai['adjustment_id'] = $adj_id;
			$ai['user_id'] = $vp_session['vp']['link_user_id'];
			$ai['sku_item_id'] = $item['sku_item_id'];
			$ai['qty'] = $item['qty']*-1;
			$ai['cost'] = $item['cost'];
			$ai['selling_price'] = $item['latest_selling_price'];
			$ai['stock_balance'] = $item['latest_stock_balance'];
			
			// create adjustment item
			$con->sql_query("insert into adjustment_items ".mysql_insert_by_field($ai));
			
			$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id=".mi($item['sku_item_id']));
		}
		
		// update approval flow into adjustment
		$ah_ins = array();
		$ah_ins['approval_flow_id'] = 0;
		$ah_ins['ref_table'] = "adjustment";
		$ah_ins['ref_id'] = $adj_id;
		$ah_ins['active'] = 1;
		$ah_ins['flow_approvals'] = $ah_ins['approved_by'] = "|".$vp_session['vp']['link_user_id']."|";
		$ah_ins['approvals'] = "|";
		$ah_ins['notify_users'] = "||";
		$ah_ins['status'] = 1;
		$ah_ins['approval_order_id'] = 4;		
		$ah_ins['branch_id'] = $form['branch_id'];
		$ah_ins['added'] = "CURRENT_TIMESTAMP";

		// insert branch approval history		
		$con->sql_query("insert into branch_approval_history ".mysql_insert_by_field($ah_ins));
		$adj_approval_history_id = $con->sql_nextid();
		
		$ah_ins = array();
		$ah_ins['approval_history_id'] = $adj_approval_history_id;
		$ah_ins['user_id'] = $vp_session['vp']['link_user_id'];
		$ah_ins['log'] = "Approved";
		$ah_ins['timestamp'] = "CURRENT_TIMESTAMP";
		$ah_ins['status'] = 1;
		$ah_ins['branch_id'] = $bid;
		
		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($ah_ins));
		
		// update back the appoval history id to adjustment
		$con->sql_query("update adjustment set approval_history_id=".mi($adj_approval_history_id)." where branch_id=$bid and id=$adj_id");
		
		// get pack item
		$pack_item_list = array();
		$q1 = $con->sql_query("select pi.*, ifnull(sip.price, si.selling_price) as latest_selling_price,si.artno,si.mcode, si.doc_allow_decimal
		from vp_repacking_pack_items pi
		left join sku_items si on si.id=pi.sku_item_id
		left join sku_items_price sip on sip.branch_id=pi.branch_id and sip.sku_item_id=pi.sku_item_id
		where pi.branch_id=$bid and pi.repacking_id=$id 
		order by pi.id");
		
		$total_cost = 0;
		$total_qty = 0;
		$total_selling = 0;
		while($r = $con->sql_fetchassoc($q1)){
			$total_cost += round($r['qty'] * $r['calc_cost'], $config['global_cost_decimal_points']);
			$total_qty += $r['qty'];
			$total_selling += $r['qty'] * $r['latest_selling_price'];
			
			$pack_item_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		// insert new GRR
		$ins = array();
		
		// call appCore to generate new ID
		$new_id = $appCore->generateNewID("grr", "branch_id = ".mi($bid));
		
		if(!$new_id) die("Unable to generate new ID from appCore!");
		
		$ins['id'] = $new_id;
		$ins['branch_id'] = $bid;
		$ins['user_id'] = $ins['rcv_by'] = $vp_session['vp']['link_user_id'];
		$ins['vendor_id'] = $vp_session['id'];
		$ins['rcv_date'] = $form['repacking_date'];
		$ins['grr_amount'] = $total_cost;
		$ins['grr_pcs'] = $total_qty;
		$ins['active'] = 1;
		$ins['status'] = 1;
		$ins['last_update'] = $ins['added'] = "CURRENT_TIMESTAMP";
		$ins['department_id'] = $form['dept_id'];
		
		$con->sql_query("insert into grr ".mysql_insert_by_field($ins));
		$grr_id = $con->sql_nextid();
		
		// insert new GRR item
		$ins = array();
		
		// call appCore to generate new ID
		$new_id = $appCore->generateNewID("grr_items", "branch_id = ".mi($bid));
		
		if(!$new_id) die("Unable to generate new ID from appCore!");
		
		$ins['id'] = $new_id;
		$ins['grr_id'] = $grr_id;
		$ins['branch_id'] = $bid;
		$ins['doc_no'] = "VP".substr(time(), -5, 5);
		$ins['type'] = "OTHER";
		$ins['amount'] = $total_cost;
		$ins['pcs'] = $total_qty;
		$ins['remark'] = $remark;
		$ins['grn_used'] = 1;

		$con->sql_query("insert into grr_items ".mysql_insert_by_field($ins));
		$grr_item_id = $con->sql_nextid();
		
		// insert new GRN
		$ins = array();
		
		// call appCore to generate new ID
		$new_id = $appCore->generateNewID("grn", "branch_id = ".mi($bid));
		
		if(!$new_id) die("Unable to generate new ID from appCore!");
		
		$ins['id'] = $new_id;
		$ins['branch_id'] = $bid;
		$ins['user_id'] = $vp_session['vp']['link_user_id'];
		$ins['grr_id'] = $grr_id;
		$ins['grr_item_id'] = $grr_item_id;
		$ins['vendor_id'] = $vp_session['id'];
		$ins['department_id'] = $form['dept_id'];
		$ins['active'] = 1;
		
		// is using grn future, need insert extra fields
		if($config['use_grn_future']){
			$ins['div1_approved_by'] = $ins['div2_approved_by'] = $ins['div3_approved_by'] = $ins['div4_approved_by'] = $vp_session['vp']['link_user_id'];
			$ins['is_future'] = 1;
		}

		$ins['last_update'] = $ins['added'] = "CURRENT_TIMESTAMP";
		
		$con->sql_query("insert into grn ".mysql_insert_by_field($ins));
		$grn_id = $con->sql_nextid();
		
		// insert branch approval history for gen
		$ah_ins = array();
		$ah_ins['approval_flow_id'] = 0;
		$ah_ins['ref_table'] = "grn";
		$ah_ins['ref_id'] = $grn_id;
		$ah_ins['active'] = 1;
		$ah_ins['flow_approvals'] = $ah_ins['approved_by'] = "|".$vp_session['vp']['link_user_id']."|";
		$ah_ins['approvals'] = "|";
		$ah_ins['notify_users'] = "||";
		$ah_ins['status'] = 1;
		$ah_ins['approval_order_id'] = 4;
		$ah_ins['branch_id'] = $form['branch_id'];
		$ah_ins['added'] = "CURRENT_TIMESTAMP";
		
		$con->sql_query("insert into branch_approval_history ".mysql_insert_by_field($ah_ins));
		$grn_approval_history_id = $con->sql_nextid();
		
		$ah_ins = array();
		$ah_ins['approval_history_id'] = $grn_approval_history_id;
		$ah_ins['user_id'] = $vp_session['vp']['link_user_id'];
		$ah_ins['log'] = "Approved";
		$ah_ins['timestamp'] = "CURRENT_TIMESTAMP";
		$ah_ins['status'] = 1;
		$ah_ins['branch_id'] = $bid;
		
		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($ah_ins));
		
		$upd = array();
		$upd['department_id'] = $form['dept_id'];
		$upd['last_update'] = "CURRENT_TIMESTAMP";
		$upd['amount'] = $upd['final_amount'] = $total_cost;
		$upd['total_selling'] = $total_selling;
		$upd['status'] = $upd['approved'] = $upd['authorized'] = 1;
		$upd['approval_history_id'] = $grn_approval_history_id;
		$upd['by_account'] = $vp_session['vp']['link_user_id'];
		$upd['account_amount'] = $total_cost;
		$upd['account_update'] = "CURRENT_TIMESTAMP";
		
		log_br($vp_session['vp']['link_user_id'], 'GRN', $grn_id, "Generated: GRN".sprintf("%05d", $grn_id));
		log_vp($vp_session['id'], "GRN", $grn_id, "Generated: GRN".sprintf("%05d", $grn_id));
		
		$con->sql_query("update grn set ".mysql_update_by_field($upd)." where id = ".mi($grn_id)." and branch_id=$bid");
		
		// loop for each pack item
		foreach($pack_item_list as $item){
			$ins = array();
			
			// call appCore to generate new ID
			$new_id = $appCore->generateNewID("grn_items", "branch_id = ".mi($bid));
			
			if(!$new_id) die("Unable to generate new ID from appCore!");
			
			$ins['id'] = $new_id;
			$ins['branch_id'] = $bid;
			$ins['grn_id'] = $grn_id;
			$ins['sku_item_id'] = $item['sku_item_id'];		
			$ins['artno_mcode'] = $item['mcode'] ? $item['mcode'] : $item['artno'];
			$ins['uom_id'] = $ins['selling_uom_id'] = 1;
			$ins['cost'] = $item['calc_cost'];
			$ins['selling_price'] = $item['latest_selling_price'];
			$ins['pcs'] = $item['qty'];
			$ins['item_group'] = 3;
			
			$con->sql_query("insert into grn_items ".mysql_insert_by_field($ins));
			
			$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id=".mi($item['sku_item_id']));
		}
	}
	
	function delete_repacking(){
		global $vp_session, $smarty, $con, $config;
		
		$bid = mi($_REQUEST['branch_id']);
		$id = mi($_REQUEST['id']);
		
		if(!$bid || !$id)	js_redirect("Invalid Repacking ID", $_SERVER['PHP_SELF']);
		
		if($bid != $this->bid)	js_redirect("Invalid Repacking Branch", $_SERVER['PHP_SELF']);
		
		$upd = array();
		$upd['active'] = 0;
		$upd['status'] = 4;
		$upd['approved'] = 0;
		$con->sql_query("update vp_repacking set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$id");
		
		$str = "Delete: Repacking ID#$id";
		log_br($vp_session['vp']['link_user_id'], 'REPACKING', $id, $str);
		log_vp($vp_session['id'], "REPACKING", $id, $str);
		
		header("Location: $_SERVER[PHP_SELF]?t=delete&save_id=$id");
		exit;
	}
	
	function print_repacking(){
		global $vp_session, $smarty, $con, $config;
		
		//print_r($vp_session);
		
		$bid = mi($_REQUEST['branch_id']);
		$id = mi($_REQUEST['id']);
		
		if(!$bid || !$id)	js_redirect("Invalid Repacking ID", $_SERVER['PHP_SELF']);
		
		if($bid != $this->bid)	js_redirect("Invalid Repacking Branch", $_SERVER['PHP_SELF']);

		$con->sql_query("select * from branch where id =".mi($bid));
		$smarty->assign("from_branch", $con->sql_fetchrow());
	
		$form = $this->load_repacking_header($bid, $id);
		$form['group_list'] = $this->load_repacking_group_items($bid, $id);
		$item_list = array();
		
		$total_item_count = 0;
		if($form['group_list']){
			foreach($form['group_list'] as $group_id => $group_list){
				if($group_list['lose']){
					foreach($group_list['lose'] as $lose_item){
						$lose_item['type'] = 'Lose';
						$item_list[] = $lose_item;
						$total_item_count++;
					}
				}
				
				if($group_list['pack']){
					foreach($group_list['pack'] as $pack_item){
						$pack_item['type'] = 'Pack';
						$item_list[] = $pack_item;
						$total_item_count++;
					}
				}
			}
		}
		
		
		$item_per_page = $config['vp_repacking_print_item_per_page']>5 ? $config['vp_repacking_print_item_per_page'] : 25;
	    $item_per_lastpage = $item_per_page;
	    
	    $totalpage = 1 + ceil(($total_item_count - $item_per_lastpage)/ $item_per_page);
	    
	    $smarty->assign('form', $form);
	    
	    for ($i=0,$page=1;$page<=$totalpage;$i+=$item_per_page,$page++){
			$smarty->assign("PAGE_SIZE", ($page < $totalpage) ? $item_per_page : $item_per_lastpage);
			$smarty->assign("is_lastpage", ($page >= $totalpage));
			$smarty->assign("page", "Page $page of $totalpage");
			$smarty->assign("start_counter", $i);
			$smarty->assign("items", array_slice($item_list,$i,$item_per_page));
			
			$smarty->display("vp.repacking.print.tpl");
			$smarty->assign("skip_header",1);
		}
		log_vp($vp_session['id'], "REPACKING", $id, "Print: Repacking ID#".$id);
	}
}

$REPACKING = new REPACKING('Repacking');

?>
