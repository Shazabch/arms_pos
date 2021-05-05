<?php
/*
1/3/2013 3:43 PM Justin
- Bug fixed on system do not generate proper approval history item while the GRN being rejected on backend.

1/3/2013 5:37 PM Justin
- Enhanced to have cancel function.

1/8/2013 11:50 AM Justin
- Bug fixed on passing empty ID after save/cofirmed a new GRN.

1/16/2013 10:22 AM Justin
- Enhanced to have sorting feature by Category or SKU Description.

1/17/2013 9:48 AM Justin
- Enhanced show level 4 category instead of level 3.

1/18/2013 10:50 AM Justin
- Enhanced to capture department ID from first item while found department ID is empty at first.
- Enhanced sorting for category to have sorting for SKU Description as well.

1/18/2013 4:49 PM Justin
- Enhanced to show department.

1/21/2013 10:56 AM Justin
- Enhanced to capture the correct cost price from last grn from similar vendor.
- Bug fixed on system doesnt mark cost to recalculate for confirm / cancel the GRN.

1/21/2013 3:54 PM Justin
- Bug fixed on system capturing wrong info for log.

1/30/2013 4:52 PM Justin
- Enhanced to have export and import item list.

2/1/2013 5:07 PM Justin
- Enhanced to show artno, mcode and old code columns for import file list.

2/18/2013 4:28 PM Justin
- Bug fixed on system can't do pagination.

2/25/2013 2:02 PM Justin
- Bug fixed on putting wrong location between SKU Description and Category while exporting item.

2017-09-13 17:07 PM Qiu Ying
- Bug fixed on treating special characters as wildcard character

12/20/2019 10:03 AM Justin
- Enhanced to insert ID manually for some tables that uses auto increment.

4/2/2020 4:30 PM William
- Enhanced to capture print log to log_vp.
*/
include('include/common.php');
 $maintenance->check(170);

if(!$vp_session)	js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class GRN extends Module{
	var $bid = 0;
	
	function __construct($title){
		global $vp_session, $config, $smarty, $con;
		
		$sort_type = array(1=>"Category", 2=>"SKU Description");
		$smarty->assign("sort_type", $sort_type);
		$this->bid = $vp_session['branch_id'];
		
		if(!isset($_REQUEST['t'])) $_REQUEST['t'] = 1;

		$q1 = $con->sql_query("select * from category where active=1 and level=2 order by description");
		while($r = $con->sql_fetchassoc($q1)){
			$departments[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("departments", $departments);
		
		parent::__construct($title);
	}
	
	function _default(){
		global $vp_session, $smarty;
		
		/*if($_REQUEST['submit_type']=='excel'){	// export excel
			include_once("include/excelwriter.php");
			log_vp($vp_session['id'], "OPERATIONS", 0, "Goods Receiving Note");

			Header('Content-Type: application/msexcel');
			Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
			print ExcelWriter::GetHeader();
			$smarty->assign('no_header_footer', 1);
		}*/
		$this->display();
	}
	
	function view(){
		global $smarty;
		$smarty->assign("is_view_mode", true);
		$this->open();
	}
	
	function open(){
		global $con, $vp_session, $smarty, $config;
		
		$form = $_REQUEST;
		
		// it is new GRN
		if(!$form['id'] && !$form['branch_id']){
			$grn['rcv_date'] = date("Y-m-d");
			$smarty->assign("form", $grn);
		}else{ // existing GRN
			// load grn info
			$grn = $this->load_grn_info($form['id'], $form['branch_id']);
			$smarty->assign("form", $grn);
			
			// load grn items info
			$grn_items = $this->load_grn_items_info($form['id'], $form['branch_id']);
		}

		if($form['a'] != "view"){
			$gi_list = $this->load_sku_group_items($grn_items);
		}
		
		if($grn_items){
			if($gi_list) $grn_items = array_merge($grn_items, $gi_list);
			asort($grn_items);
		}else $grn_items = $gi_list;

		usort($grn_items,array($this, 'sort_cat'));
		$smarty->assign("grn_items", $grn_items);
		$smarty->display("vp.goods_receiving_note.new.tpl");
	}
	
	function load_grn_info($grn_id, $bid){
		global $con, $vp_session, $smarty;

		// get grn info
		$sql = $con->sql_query("select grn.id, grn.branch_id, grn.grr_id, b.code as branch_code, grn.approved, 
								grn.approval_history_id, u.u as grn_by, grn.active
								from grn 
								left join branch b on b.id = grn.branch_id
								left join user u on u.id = grn.user_id
								where grn.id = ".mi($grn_id)." and grn.branch_id = ".mi($bid));
		$grn = $con->sql_fetchassoc($sql);
		$con->sql_freeresult($sql);
		
		if($grn['approval_history_id']>0){
			$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u 
								from branch_approval_history_items i 
								left join branch_approval_history h on i.approval_history_id=h.id and i.branch_id=h.branch_id 
								left join user on i.user_id = user.id 
								where h.ref_table = 'grn' and i.branch_id=".mi($bid)." and (h.ref_id=".mi($grn_id)." or h.id = ".mi($grn['approval_history_id']).")
								order by i.timestamp");

			$smarty->assign("approval_history", $con->sql_fetchrowset($q2));
			$con->sql_freeresult($q2);
		}
		
		
		// get grr info
		$sql = $con->sql_query("select grr.rcv_date, grr.added, grr.grr_amount, grr.grr_ctn, grr.grr_pcs, grr.department_id,
								gi.doc_no, gi.type, c.description as department
								from grr 
								left join grr_items gi on gi.grr_id = grr.id and gi.branch_id = grr.branch_id
								left join category c on c.id = grr.department_id
								where grr.id = ".mi($grn['grr_id'])." and grr.branch_id = ".mi($bid));
		$grr = $con->sql_fetchassoc($sql);
		$con->sql_freeresult($sql);
		
		$grn = array_merge($grn, $grr);
		
		return $grn;
	}
	
	function load_grn_items_info($grn_id, $bid){
		global $con;
		
		$sql = $con->sql_query("select si.id, si.sku_item_code, si.description, si.artno, si.mcode, si.doc_allow_decimal,
								u.code as packing_uom_code, gi.selling_price, gi.cost as cost_price, gi.pcs as qty, 
								c.description as category
								from grn_items gi
								left join sku_items si on si.id = gi.sku_item_id
								left join sku on si.sku_id = sku.id
								left join category_cache cc on cc.category_id = sku.category_id
								left join category c on c.id = cc.p4
								left join uom u on u.id = si.packing_uom_id
								where gi.grn_id = ".mi($grn_id)." and gi.branch_id = ".mi($bid)."
								order by si.id");
		
		while($r = $con->sql_fetchassoc($sql)){
			$gi_list[$r['id']] = $r;
		}
		
		return $gi_list;
	}
	
	function load_sku_group_items($grn_items=array()){
		global $vp_session, $con;

		$sql = $con->sql_query("select si.*, sgi.branch_id, u.code as packing_uom_code,c.description as category
								from sku_group_item sgi
								join sku_items si on si.sku_item_code = sgi.sku_item_code
								join uom u on u.id = si.packing_uom_id
								join sku_group_vp_date_control vpdc on vpdc.branch_id=sgi.branch_id and vpdc.sku_group_id=sgi.sku_group_id and vpdc.sku_item_id=si.id and curdate() between vpdc.from_date and vpdc.to_date
								left join sku on si.sku_id = sku.id
								left join category_cache cc on cc.category_id = sku.category_id
								left join category c on c.id = cc.p4
								where sgi.sku_group_id = ".mi($vp_session['sku_group_id'])." and sgi.branch_id = ".mi($vp_session['sku_group_bid'])."
								order by si.id");
		
		while($r = $con->sql_fetchassoc($sql)){
			if($grn_items[$r['id']]) continue;
			$r['cost_price'] = $this->get_cost($r['id']);
			$r['selling_price'] = $this->get_selling_price($r['id'], $r['branch_id']);
			$gi_list[$r['id']] = $r;
		}
		$con->sql_freeresult($sql);
		
		return $gi_list;
	}
	
	function get_cost($sid){
		global $con, $vp_session;
		
		// get last cost from GRN
		$sql = $con->sql_query("select gi.cost 
								from grn
								left join grn_items gi on gi.grn_id = grn.id and gi.branch_id = grn.branch_id
								left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
								where grn.vendor_id = ".mi($vp_session['id'])." and grn.branch_id = ".mi($vp_session['branch_id'])." and gi.sku_item_id = ".mi($sid)." 
								order by grr.rcv_date desc, grr.id desc
								limit 1");
		$cp_info = $con->sql_fetchassoc($sql);
		$con->sql_freeresult($sql);

		if(!$cp_info['cost']){ // if not found then only get from masterfile
			$sql = $con->sql_query("select cost_price
									from sku_items si
									where si.id = ".mi($sid));
			$cp_info = $con->sql_fetchassoc($sql);
			$con->sql_freeresult($sql);
			
			return $cp_info['cost_price'];
		}else return $cp_info['cost'];
	}
	
	function get_selling_price($sid, $bid){
		global $con;
		
		$con->sql_query("select ifnull(sip.price, si.selling_price) as selling_price
						 from sku_items si
						 left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($bid)."
						 where si.id = ".mi($sid));
		$sp_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $sp_info['selling_price'];
	}
	
	function ajax_load_grn_list(){
		global $con, $vp_session, $smarty, $config;

		$link_user_id = $vp_session['vp']['link_user_id'];
		$branch_id = $vp_session['branch_id'];

		if (isset($_REQUEST['t'])){
			$t = intval($_REQUEST['t']);
			// if t used and s unused, this is for prompt (cancel, confirm etc)
			// we will show "saved GRN"
			if ($t==0 && !isset($_REQUEST['search'])) $t=1;
		}
		$no_owner_check = false;
		
		switch ($t){
			case 0: // search
				$str = trim($_REQUEST['search']);
				if(!$str) die('Cannot search empty string');

				$where = array();
				if($str){
					$where[] = "(grn.id=".ms(preg_replace("/[^0-9]/","", $str))." or (select gri.id from grr_items gri where gri.doc_no like ".ms("%".replace_special_char($str)."%")." and gri.grr_id = grn.grr_id and gri.branch_id = grn.branch_id group by gri.grr_id))";
				}
				
				$where = join(" and ", $where);
				break;

			case 1: // show saved
				$where = "grn.active = 1 and grn.status = 0";
				break;
			case 2: // show confirmed
				$where = "grn.active = 1 and grn.status = 1 and grn.approved = 1";
				break;
		}

		$owner_check = "grn.user_id = $link_user_id and ";
		
		if(!$config['consignment_modules']){
			if ($t != 0 || BRANCH_CODE != "HQ") $where .= " and grn.branch_id = $branch_id";
		}

		if (isset($t)){
			// pagination
			$start = intval($_REQUEST['s']);
			if (isset($_REQUEST['sz']))
				$sz = intval($_REQUEST['sz']);
			else{
				if(isset($config['document_page_size'])) $sz=$config['document_page_size'];
				else $sz = 25;
			}
			$limit =  "limit $start, $sz";
		
			$con->sql_query("select count(*) from grn
							left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
							left join grr_items on grn.grr_item_id = grr_items.id and grn.branch_id = grr_items.branch_id
							where $owner_check $where");

			$r = $con->sql_fetchrow();
			$total = $r[0];
			if ($total > $sz){
				if ($start > $total) $start = 0;
				// create pagination
				$pg = "<b>Goto Page</b> <select onchange=\"GRN.list_sel($t,this.value);\">";
				for ($i=0,$p=1;$i<$total;$i+=$sz,$p++){
					$pg .= "<option value=$i";
					if ($i == $start) $pg .= " selected";
					$pg .= ">$p</option>";
				}
				$pg .= "</select>";
				$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
			}
		}

		$sql = "select grn.*,
				branch.report_prefix,grr_items.doc_no,grr_items.type,grn.approval_history_id,vendor.code as vendor_code, branch.code as branch_code,
				if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id
				from grn
				left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
				left join grr_items on grn.grr_item_id = grr_items.id and grn.branch_id = grr_items.branch_id
				left join vendor on grn.vendor_id = vendor.id
				left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = grn.branch_id
				left join branch on grn.branch_id = branch.id
				where $owner_check $where 
				order by grn.last_update desc $limit";
		//print $sql;
		$ql = $con->sql_query($sql);

		while($r1=$con->sql_fetchrow($ql)){
			/*if($r1['is_future']){
				$sql2 = $con->sql_query("select group_concat(distinct doc_no separator ', ') as doc_no, type, case when type = 'PO' then 1 when type = 'INVOICE' then 2 when type = 'DO' then 3 else 4 end as type_asc from grr_items where grr_id = ".ms($r1['grr_id'])." and branch_id = ".ms($r1['branch_id'])." group by type_asc order by type_asc ASC limit 1");

				while($r2=$con->sql_fetchrow($sql2)){
					$r1['doc_no'] = $r2['doc_no'];
					$r1['type'] = $r2['type'];
				}
			}
			
			if($config['grn_summary_show_related_invoice'] && $r1['type'] == "PO"){
				$q1 = $con->sql_query("select group_concat(gi.doc_no order by 1 separator ', ') as related_invoice from grr_items gi where gi.type='INVOICE' and gi.grr_id=".mi($r1['grr_id'])." and gi.branch_id=".mi($r1['branch_id']));
				
				$tmp = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				$r1['related_invoice'] = $tmp['related_invoice'];
			}*/

			$grn_list[] = $r1;
		}
		
		$smarty->assign("grn_list", $grn_list);
		$smarty->display("vp.goods_receiving_note.list.tpl");
	}
	
	function confirm(){
		$this->save(true);
	}
	
	function save($is_confirm=false){
		global $con, $smarty, $vp_session, $config, $appCore;

		$this->validate_data();
		
		$form = $_REQUEST;
		if($this->err){ // found errors
			if($form['id']){
				// load grn info
				$grn = $this->load_grn_info($form['id'], $form['branch_id']);
				$grn['rcv_date'] = $form['rcv_date'];
				$grn['department_id'] = $form['department_id'];
				$smarty->assign("form", $grn);
			}else{
				$grn['rcv_date'] = $form['rcv_date'];
				$grn['department_id'] = $form['department_id'];
				$smarty->assign("form", $grn);
			}

			$smarty->assign("errm", $this->err);
			$smarty->assign("grn_items", $this->gi_list);
			$smarty->display("vp.goods_receiving_note.new.tpl");
			exit;
		}
		
		// loop to pickup department ID from first item
		if(!$form['department_id']){
			foreach($form['qty'] as $sid=>$qty){
				if($qty <= 0) continue;
				else{
					$q1 = $con->sql_query("select c.department_id
										  from sku_items si
										  left join sku on sku.id = si.sku_id
										  left join category c on c.id = sku.category_id
										  where si.id = ".mi($sid));
					
					if($con->sql_numrows($q1) > 0){
						$si_info = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
						$form['department_id'] = $si_info['department_id'];
						break;
					}else continue;
				}
			}
		}
		
		if($form['id']){ // is existing GRN
			$grn_id = $form['id'];
			
			$grr_upd = array();
			$grr_upd['rcv_date'] = $form['rcv_date'];
			$grr_upd['department_id'] = $form['department_id'];
			$grr_upd['grr_pcs'] = $form['total_qty'];
			$grr_upd['grr_amount'] = $form['total_amount'];
			$grr_id = $form['grr_id'];
			$con->sql_query("update grr set ".mysql_update_by_field($grr_upd)." where id = ".mi($grr_id)." and branch_id = ".mi($form['branch_id']));
			
			$grr_upd = array();
			$grr_upd['pcs'] = $form['total_qty'];
			$grr_upd['amount'] = $form['total_amount'];
			$con->sql_query("update grr_items set ".mysql_update_by_field($grr_upd)." where grr_id = ".mi($grr_id)." and branch_id = ".mi($form['branch_id'])." and type = 'OTHER'");
			
			$con->sql_query("delete from grn_items where grn_id = ".mi($grn_id)." and branch_id = ".mi($form['branch_id']));
		}else{ // is new GRN
			// call appCore to generate new ID
			unset($new_id);
			$new_id = $appCore->generateNewID("grr", "branch_id = ".mi($form['branch_id']));
			
			if(!$new_id) die("Unable to generate new ID from appCore!");
			
			// insert new GRR
			$ins = array();
			$ins['id'] = $new_id;
			$ins['branch_id'] = $form['branch_id'];
			$ins['user_id'] = $ins['rcv_by'] = $vp_session['vp']['link_user_id'];
			$ins['vendor_id'] = $vp_session['id'];
			$ins['rcv_date'] = $form['rcv_date'];
			$ins['grr_amount'] = $form['total_amount'];
			$ins['grr_pcs'] = $form['total_qty'];
			$ins['active'] = 1;
			$ins['status'] = 1;
			$ins['last_update'] = $ins['added'] = "CURRENT_TIMESTAMP";
			$ins['department_id'] = $form['department_id'];
			
			$con->sql_query("insert into grr ".mysql_insert_by_field($ins));
			$grr_id = $con->sql_nextid();
			
			// call appCore to generate new ID
			unset($new_id);
			$new_id = $appCore->generateNewID("grr_items", "branch_id = ".mi($form['branch_id']));
			
			if(!$new_id) die("Unable to generate new ID from appCore!");
			
			// insert new GRR item
			$ins = array();
			$ins['id'] = $new_id;
			$ins['grr_id'] = $grr_id;
			$ins['branch_id'] = $form['branch_id'];
			$ins['doc_no'] = "VP".substr(time(), -5, 5);
			$ins['type'] = "OTHER";
			$ins['amount'] = $form['total_amount'];
			$ins['pcs'] = $form['total_qty'];
			$ins['remark'] = "Generated by Vendor Portal";
			$ins['grn_used'] = 1;
			
			$con->sql_query("insert into grr_items ".mysql_insert_by_field($ins));
			$grr_item_id = $con->sql_nextid();
			
			// call appCore to generate new ID
			unset($new_id);
			$new_id = $appCore->generateNewID("grn", "branch_id = ".mi($form['branch_id']));
			
			if(!$new_id) die("Unable to generate new ID from appCore!");
			
			// insert new GRN
			$ins = array();
			$ins['id'] = $new_id;
			$ins['branch_id'] = $form['branch_id'];
			$ins['user_id'] = $vp_session['vp']['link_user_id'];
			$ins['grr_id'] = $grr_id;
			$ins['grr_item_id'] = $grr_item_id;
			$ins['vendor_id'] = $vp_session['id'];
			$ins['department_id'] = $form['department_id'];
			$ins['active'] = 1;
			
			// is using grn future, need insert extra fields
			if($config['use_grn_future']){
				$ins['div1_approved_by'] = $ins['div2_approved_by'] = $ins['div3_approved_by'] = $ins['div4_approved_by'] = $vp_session['vp']['link_user_id'];
				$ins['is_future'] = 1;
			}

			$ins['last_update'] = $ins['added'] = "CURRENT_TIMESTAMP";
			
			$con->sql_query("insert into grn ".mysql_insert_by_field($ins));
			$grn_id = $con->sql_nextid();
		}
	
		$upd['department_id'] = $form['department_id'];
		$upd['last_update'] = "CURRENT_TIMESTAMP";
		$upd['amount'] = $upd['final_amount'] = $form['total_amount'];
		$upd['total_selling'] = $form['total_selling'];

		// is cofirmed, need to auto generate approval history
		if($is_confirm){
			$upd['status'] = $upd['approved'] = $upd['authorized'] = 1;
			$upd['approval_history_id'] = $this->generate_approval_history($grn_id, $form['approval_history_id']);

			$upd['by_account'] = $vp_session['vp']['link_user_id'];
			$upd['account_amount'] = $form['total_amount'];
			$upd['account_update'] = "CURRENT_TIMESTAMP";
			
			log_br($vp_session['vp']['link_user_id'], 'GRN', $grn_id, "Confirmed: GRN".sprintf("%05d", $grn_id));
			log_vp($vp_session['id'], "GRN", $grn_id, "Confirmed: GRN".sprintf("%05d", $grn_id));
		}else{
			log_br($vp_session['vp']['link_user_id'], 'GRR', $grr_id, "Saved: GRR".sprintf("%05d", $grr_id));
			log_vp($vp_session['id'], "GRR", $grr_id, "Saved: GRR".sprintf("%05d", $grr_id));
			log_br($vp_session['vp']['link_user_id'], 'GRN', $grn_id, "Saved: GRN".sprintf("%05d", $grn_id));
			log_vp($vp_session['id'], 'GRN', $grn_id, "Saved: GRN".sprintf("%05d", $grn_id));
		}
		
		$con->sql_query("update grn set ".mysql_update_by_field($upd)." where id = ".mi($grn_id)." and branch_id = ".mi($form['branch_id']));

		foreach($form['qty'] as $sid=>$qty){
			if($qty <= 0) continue;
			
			// call appCore to generate new ID
			unset($new_id);
			$new_id = $appCore->generateNewID("grn_items", "branch_id = ".mi($form['branch_id']));
			
			if(!$new_id) die("Unable to generate new ID from appCore!");
			
			$ins = array();
			$ins['id'] = $new_id;
			$ins['branch_id'] = $form['branch_id'];
			$ins['grn_id'] = $grn_id;
			$ins['sku_item_id'] = $sid;
			$con->sql_query("select * from sku_items si where si.id = ".mi($sid));
			$si_info = $con->sql_fetchassoc();
			$con->sql_freeresult();
			if($si_info['mcode']) $artno_mcode = $si_info['mcode'];
			else $artno_mcode = $si_info['artno'];
			$ins['artno_mcode'] = $artno_mcode;
			$ins['uom_id'] = $ins['selling_uom_id'] = 1;
			$ins['cost'] = $form['cost_price'][$sid];
			$ins['selling_price'] = $form['selling_price'][$sid];
			$ins['pcs'] = $qty;
			$ins['item_group'] = 3;
			
			$con->sql_query("insert into grn_items ".mysql_insert_by_field($ins));
			
			if($is_confirm) $con->sql_query("update sku_items_cost set changed=1 where branch_id=".mi($form['branch_id'])." and sku_item_id = ".mi($sid));
		}
		
		header("Location: /vp.goods_receiving_note.php?msg=$form[a]&id=$grn_id");
	}
	
	function cancel(){
		global $con, $smarty, $vp_session;
		
		$grn_id = $_REQUEST['id'];
		$branch_id = $_REQUEST['branch_id'];
		$con->sql_query("update grn 
						left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
						left join grr_items gi on gi.grr_id = grr.id and gi.branch_id = grr.branch_id
						set grn.active=0, gi.grn_used=0, grr.active=0
						where grn.id = ".mi($grn_id)." and grn.branch_id = ".mi($branch_id));

		$con->sql_query("update grn_items gi
						 left join sku_items_cost sic on sic.sku_item_id = gi.sku_item_id and sic.branch_id = gi.branch_id
						 set sic.changed=1
						 where gi.grn_id = ".mi($grn_id)." and gi.branch_id = ".mi($branch_id));
						
		log_br($vp_session['vp']['link_user_id'], 'GRR', $grr_id, "Deleted: GRR".sprintf("%05d", $grr_id));
		log_vp($vp_session['id'], "GRR", $grr_id, "Deleted: GRR".sprintf("%05d", $grr_id));
		log_br($vp_session['vp']['link_user_id'], 'GRN', $grn_id, "Cancelled: GRN".sprintf("%05d", $grn_id));
		log_vp($vp_session['id'], 'GRN', $grn_id, "Cancelled: GRN".sprintf("%05d", $grn_id));			
						
		header("Location: /vp.goods_receiving_note.php?msg=$_REQUEST[a]&id=$grn_id");
	}
	
	function generate_approval_history($rid, $approval_history_id=0){
		global $con, $vp_session;
		
		$form = $_REQUEST;
		
		// insert branch approval history
		$ah_ins = array();
		$ah_ins['approval_flow_id'] = 0;
		$ah_ins['ref_table'] = "grn";
		$ah_ins['ref_id'] = $rid;
		$ah_ins['active'] = 1;
		$ah_ins['flow_approvals'] = $ah_ins['approved_by'] = "|".$vp_session['vp']['link_user_id']."|";
		$ah_ins['approvals'] = "|";
		$ah_ins['notify_users'] = "||";
		$ah_ins['status'] = 1;
		$ah_ins['approval_order_id'] = 4;
		
		if(!$approval_history_id){	// need to create approval history
			$ah_ins['branch_id'] = $form['branch_id'];
			$ah_ins['added'] = "CURRENT_TIMESTAMP";
			
			$con->sql_query("insert into branch_approval_history ".mysql_insert_by_field($ah_ins));
			$approval_history_id = $con->sql_nextid();
		}else{
			$con->sql_query("update branch_approval_history set ".mysql_update_by_field($ah_ins)."where branch_id=".mi($form['branch_id'])." and id=".mi($approval_history_id));
		}
		
		$ah_ins = array();
		$ah_ins['approval_history_id'] = $approval_history_id;
		$ah_ins['user_id'] = $vp_session['vp']['link_user_id'];
		$ah_ins['log'] = "Approved";
		$ah_ins['timestamp'] = "CURRENT_TIMESTAMP";
		$ah_ins['status'] = 1;
		$ah_ins['branch_id'] = $form['branch_id'];
		
		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($ah_ins));
		
		return $approval_history_id;
	}
	
	function validate_data(){
		global $con, $LANG;

		$form = $_REQUEST;
		$have_qty = false;
		$this->err = $this->gi_list = array();

		if(!$form['rcv_date']) $this->err[] = $LANG['VP_GRN_INVALID_DATE'];

		foreach($form['qty'] as $sid=>$qty){
			$sql = $con->sql_query("select si.*, u.code as packing_uom_code, c.description as category
									from sku_items si
									left join sku on sku.id = si.sku_id
									left join uom u on u.id = si.packing_uom_id
									left join category_cache cc on cc.category_id = sku.category_id
									left join category c on c.id = cc.p4
									where si.id = ".mi($sid));
			$item = $con->sql_fetchassoc($sql);
			
			$item['cost_price'] = $form['cost_price'][$sid];
			$item['qty'] = $qty;
			
			$this->gi_list[$sid] = $item;
			if($qty > 0) $have_qty = true;
		}
		
		if(!$have_qty) $this->err[] = $LANG['VP_GRN_INVALID_QTY'];
	}
	
	function do_print(){
		global $con, $smarty, $config, $vp_session;
		$form=$_REQUEST;

		$con->sql_query("select * from branch where id=".mi($form['branch_id']));
		$smarty->assign("branch", $con->sql_fetchrow());

		$grn = $this->load_grn_info($form['id'], $form['branch_id']);
		$con->sql_query("select u from user where id = ".mi($vp_session['vp']['link_user_id']));
		$grn['printed_by'] = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		$smarty->assign("form", $grn);

		
		if($form['print_grn_report']){
			$items = $this->load_grn_items_info($form['id'], $form['branch_id']);

			// It is A4 paper and landscape
			$item_per_page= $config['grn_report_print_item_per_page']?$config['grn_report_print_item_per_page']:23;
			$item_per_lastpage = $config['grn_report_print_item_last_page']>0 ? $config['grn_report_print_item_last_page'] : $item_per_page-5;
			$totalpage = 1 + ceil((count($items)-$item_per_lastpage)/$item_per_page);

			for($i=0,$page=1;$page<=$totalpage;$i+=$item_per_page,$page++){
				if($page == $totalpage) $smarty->assign("is_last_page", 1);
				$smarty->assign("page", "Page $page of $totalpage");
				$smarty->assign("start_counter", $i);
				$smarty->assign("PAGE_SIZE", ($page < $totalpage) ? $item_per_page : $item_per_lastpage);
				$grn_items = array_slice($items,$i,$item_per_page);
				$smarty->assign("grn_items", $grn_items);
				$smarty->display("vp.goods_receiving_note.print.tpl");
				$smarty->assign("skip_header",1);
			}
			log_vp($vp_session['id'], "GRN", $form['id'], "Print: GRN".sprintf("%05d", $form['id']));
		}
	}
	
	function ajax_refresh_items_list(){
		global $smarty, $con, $vp_session;
		
		$form = $_REQUEST;
		// it is new GRN
		if($form['id'] && $form['branch_id']){
			// load grn items info
			$grn_items = $this->load_grn_items_info($form['id'], $form['branch_id']);
		}

		if($form['action'] != "view"){
			$gi_list = $this->load_sku_group_items($grn_items);
		}else $smarty->assign("disable", true);
		
		if($grn_items){
			if($gi_list) $grn_items = array_merge($grn_items, $gi_list);
		}else $grn_items = $gi_list;

		if($form['sort_by'] == 1) usort($grn_items, array($this,'sort_cat'));
		else  usort($grn_items, array($this,'sort_si'));
		
		foreach($grn_items as $r=>$item){
			if($form['qty'][$item['id']]) $item['qty'] = $form['qty'][$item['id']];
			$smarty->assign("item", $item);
			$smarty->assign("row_no", $r+1);
			$tmp['html'] .= "<tr bgcolor=\"#ffee99\" onmouseover=\"this.bgColor='#ffffcc';\" onmouseout=\"this.bgColor='';\" class=\"titem\" id=\"titem_".$item['id']."\">".$smarty->fetch("vp.goods_receiving_note.new.row.tpl")."</tr>";
		}
		$ret[] = $tmp;
		
		print json_encode($ret);
	}
	
	function sort_cat($a,$b) {
		if($a['category'] == $b['category']){
			return $this->sort_si($a, $b);
		}
		return ($a['category'] < $b['category']) ? -1 : 1;
	}
	
	function sort_si($a,$b) {
		if($a['description'] == $b['description'])	return 0;
		return ($a['description'] < $b['description']) ? -1 : 1;
	}
	
	function export_item_list(){
		global $con, $smarty, $vp_session;

		$sg_list = $this->load_sku_group_items();
		
		if($_REQUEST['sort_by'] == 1) usort($sg_list, array($this,'sort_cat'));
		else  usort($sg_list, array($this,'sort_si'));
		
		foreach($sg_list as $row=>$r){
			$r['description'] = "\"".$r['description']."\"";
			$content[] = $r['sku_item_code'].",".$r['artno'].",".$r['mcode'].",".$r['link_code'].",".$r['description'].",".$r['category'].",".$r['cost_price'].",";
		}
		
		if($content){
			$header = "SKU_ITEM_CODE,ARTNO,MCODE,OLD_CODE,DESCRIPTION,CATEGORY,COST,QTY\r\n";
			$contents = $header.join("\r\n", $content);
			header("Content-type: text/plain");
			header('Content-Disposition: attachment;filename=vp_item_list_'.time().'.csv');
			log_vp($vp_session['id'], "GRN", 0, "Export Item List");
			print $contents;
		}
	}
	
	function import_item_list(){
		global $con, $smarty, $vp_session, $LANG;
		//print_r($_FILES['csv_file']);
		$file_type_pattern = "/.csv$|.CSV$/D";
		
		if(preg_match($file_type_pattern, $_FILES['csv_file']['name'])){
			$ttl_count = $imported_count = 0;
			$f = fopen($_FILES['csv_file']['tmp_name'],"rt");
			$line = fgetcsv($f);

			while($r = fgetcsv($f)){
				$sku_item_code = trim($r[0]);
				$sql = $con->sql_query("select si.*, sgi.branch_id, u.code as packing_uom_code,c.description as category
										from sku_group_item sgi
										join sku_items si on si.sku_item_code = sgi.sku_item_code
										join uom u on u.id = si.packing_uom_id
										join sku_group_vp_date_control vpdc on vpdc.branch_id=sgi.branch_id and vpdc.sku_group_id=sgi.sku_group_id and vpdc.sku_item_id=si.id and curdate() between vpdc.from_date and vpdc.to_date
										left join sku on si.sku_id = sku.id
										left join category_cache cc on cc.category_id = sku.category_id
										left join category c on c.id = cc.p4
										where sgi.sku_group_id = ".mi($vp_session['sku_group_id'])." and sgi.branch_id = ".mi($vp_session['sku_group_bid'])." and si.sku_item_code = ".ms($sku_item_code)."
										order by si.id");
				
				while($r1 = $con->sql_fetchassoc($sql)){
					$r1['cost_price'] = mf(trim($r[6]));
					$r1['selling_price'] = $this->get_selling_price($r1['id'], $r1['branch_id']);
					$r1['qty'] = mf(trim($r[7]));
					$grn_items[$r1['id']] = $r1;
				}
			}
				
			$smarty->assign("grn_items", $grn_items);
			$smarty->display("vp.goods_receiving_note.new.tpl");
		}else{
			$err[] = $LANG['VP_GRN_INVALID_FORMAT'];
			$smarty->assign("error_from_import", true);
			$smarty->assign("err", $err);
			$smarty->display("vp.goods_receiving_note.tpl");
		}
	}
}

$GRN = new GRN('Goods Receiving Note');
?>
