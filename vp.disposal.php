<?php

/*
1/16/2013 9:39:00 AM Fithri
- add column to show items category (level 3)
- can sort by category or description

1/17/2013 9:55:00 AM Fithri
- enhanced show level 4 category instead of level 3.

1/18/2013 10:50 AM Justin
- Enhanced to capture department ID from first item while found department ID is empty at first.
- Enhanced sorting for category to have sorting for SKU Description as well.

1/21/2013 3:40 PM Justin
- Enhanced to do checking to prompt user an error message when save/confirm that having zero qty for all items.
- Bug fixed on adjustment listing does not order by the latest.

2/4/2013 10:13 AM Fithri
- Disposal type, to select between Disposal or Return
- Allow key in decimal quantity

2017-09-13 17:07 PM Qiu Ying
- Bug fixed on treating special characters as wildcard character

10/30/2018 5:28 PM Justin
- Enhanced to get company no when loading adjustment.

1/8/2020 5:23 PM William
- Enhanced to insert id manually for adjustment tables that uses auto increment.

4/2/2020 4:46 PM William
- Enhanced to capture print log to log_vp.
*/

include("include/common.php");

if (!$vp_login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class Disposal extends Module{

	function __construct($title){
		global $vp_session, $config, $smarty, $con;
		
		$this->bid = $vp_session['branch_id'];
		
		if(!isset($_REQUEST['t'])) $_REQUEST['t'] = 1;

		$con->sql_query($abc="select id, description from category where active=1 and level = 2 order by description");//print $abc;
		$smarty->assign("dept", $con->sql_fetchrowset());
		parent::__construct($title);
		
	}
	
	function _default(){
		global $vp_session, $config, $smarty, $con;
		$smarty->display("vp.disposal.home.tpl");
	}
	
	function delete() {
		global $vp_session, $config, $smarty, $con;
		
		$cancelled_by = $vp_session['vp']['link_user_id'];
		$status=5;
		$reason=ms($_REQUEST['reason']);
		$cancelled = 'CURRENT_TIMESTAMP';
					
		$con->sql_query("update adjustment set cancelled_by=$cancelled_by, cancelled=$cancelled, reason=$reason, status=$status where id=$_REQUEST[id] and branch_id=$vp_session[branch_id] limit 1") or die(mysql_error());
		log_vp($vp_session['id'], "VP DISPOSAL", $_REQUEST[id], "Cancel Adjustment ID $_REQUEST[id] , Branch $vp_session[branch_id]");
		log_br($vp_session['vp']['link_user_id'], "VP DISPOSAL", $_REQUEST[id], "Cancel Adjustment ID $_REQUEST[id] , Branch $vp_session[branch_id]");
		header("Location: /vp.disposal.php");
		exit;
	}
	
	function confirm() {
		global $vp_session, $config, $smarty, $con;
		$this->save(true);
	}
	
	function save($is_confirm = false) {
	
		global $vp_session, $config, $smarty, $con, $appCore;
		$have_qty = false;
		foreach ($_REQUEST['n_qty'] as $ni => $nv) {
			if($nv > 0){
				$have_qty = true;
			}
		}
		
		if(!$have_qty){
			$errm['item'][] = "Please key in at least one qty for a row";
			//print_r($form);
			$_REQUEST['a'] = 'open';
			//print_r($form);
			$this->load_adj(true, false, true);
			$smarty->assign("errm", $errm);
			$smarty->assign("form", $_REQUEST);
			$smarty->display("vp.disposal.new.tpl");
			exit;
		}
		
		if(!$_REQUEST['dept_id']){
			foreach ($_REQUEST['n_qty'] as $ni => $nv){
				if($qty <= 0) continue;
				else{
					$q1 = $con->sql_query("select c.department_id
										  from sku_items si
										  left join sku on sku.id = si.sku_id
										  left join category c on c.id = sku.category_id
										  where si.id = ".mi($ni));
					
					if($con->sql_numrows($q1) > 0){
						$si_info = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
						$_REQUEST['dept_id'] = $si_info['department_id'];
						break;
					}else continue;
				}
			}
		}
		
		$ins = array(
			'branch_id'			=>	$vp_session['branch_id'],
			'user_id'			=>	$vp_session['vp']['link_user_id'],
			'dept_id'			=>	$_REQUEST['dept_id'],
			'adjustment_date'	=>	$_REQUEST['adjustment_date'],
			'adjustment_type'	=>	$_REQUEST['adjustment_type'],
			'remark'			=>	$_REQUEST['remark'],
			'status'			=>	0,
		);
		
		if ($is_confirm) {
			$ins['status'] = 1;
			$ins['approved'] = 1;
		}
		
		//existing
		if ($_REQUEST['id'] > 0){
			$con->sql_query("update adjustment set " . mysql_update_by_field($ins) . " where id=$_REQUEST[id] and branch_id=$vp_session[branch_id] limit 1") or die(mysql_error());
			log_vp($vp_session['id'], "VP DISPOSAL", $_REQUEST[id], "Update Adjustment ID $_REQUEST[id] , Branch $vp_session[branch_id]");
			log_br($vp_session['vp']['link_user_id'], "VP DISPOSAL", $_REQUEST[id], "Update Adjustment ID $_REQUEST[id] , Branch $vp_session[branch_id]");
			
			foreach ($_REQUEST['n_qty'] as $ni => $nv) {
				if (empty($nv)) {
					$con->sql_query("delete from adjustment_items where id=$ni and adjustment_id=$_REQUEST[id] and branch_id=$_REQUEST[branch_id] limit 1") or die(mysql_error());
					//log_vp($vp_session['id'], "VP DISPOSAL", $_REQUEST[id], "Delete Adjustment Item - Adjustment $_REQUEST[id] , Item ID $ni, Branch $vp_session[branch_id]");
					continue;
				}
				
				$nv = mf($nv);
				$nv = -abs($nv);
				$id = $appCore->generateNewID("adjustment_items", "branch_id=".mi($vp_session['branch_id']));
				$adj_id = $_REQUEST['id'];
				if ($_REQUEST['is_new_item'][$ni] == '1') {
					$ins2 = array(
						'id' => $id,
						'adjustment_id'	=>	$adj_id,
						'branch_id'		=>	$vp_session['branch_id'],
						'user_id'		=>	$vp_session['vp']['link_user_id'],
						'sku_item_id'	=>	$ni,
						'qty'			=>	$nv,
						'cost'			=>	$_REQUEST['cost'][$ni],
						'selling_price'	=>	$_REQUEST['selling_price'][$ni],
						'stock_balance'	=>	$_REQUEST['stock_balance'][$ni],
					);
					$con->sql_query("insert into adjustment_items " . mysql_insert_by_field($ins2)) or die(mysql_error());
				}
				else {
					$con->sql_query("update adjustment_items set qty = $nv where id=$ni and adjustment_id=$_REQUEST[id] and branch_id=$_REQUEST[branch_id] limit 1") or die(mysql_error());
				}
			}
		}
		//is new
		else {
			$ins['id'] =  $appCore->generateNewID("adjustment", "branch_id=".mi($vp_session['branch_id']));
			$ins['added'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into adjustment " . mysql_insert_by_field($ins)) or die(mysql_error());
			$adj_id = $ins['id'];
			log_vp($vp_session['id'], "VP DISPOSAL", $adj_id, "Insert New Disposal - Adjustment $adj_id , Branch $vp_session[branch_id]");
			log_br($vp_session['vp']['link_user_id'], "VP DISPOSAL", $adj_id, "Insert New Disposal - Adjustment $adj_id , Branch $vp_session[branch_id]");
			
			foreach ($_REQUEST['n_qty'] as $ni => $nv) {
				if (empty($nv)) continue;
				
				$nv = mf($nv);
				$nv = -abs($nv);
				$adj_item_id = $appCore->generateNewID("adjustment_items", "branch_id=".mi($vp_session['branch_id']));
				$ins2 = array(
					'id'			=>	$adj_item_id,
					'adjustment_id'	=>	$adj_id,
					'branch_id'		=>	$vp_session['branch_id'],
					'user_id'		=>	$vp_session['vp']['link_user_id'],
					'sku_item_id'	=>	$ni,
					'qty'			=>	$nv,
					'cost'			=>	$_REQUEST['cost'][$ni],
					'selling_price'	=>	$_REQUEST['selling_price'][$ni],
					'stock_balance'	=>	$_REQUEST['stock_balance'][$ni],
				);
				$con->sql_query("insert into adjustment_items " . mysql_insert_by_field($ins2)) or die(mysql_error());
			}
		}
		
		if ($is_confirm) {
			$this->update_sku_item_cost($adj_id,$vp_session['branch_id']);
			$approval_history_id = $this->generate_approval_history($adj_id);
			$con->sql_query("update adjustment set approval_history_id = $approval_history_id where id=$adj_id and branch_id = $vp_session[branch_id] limit 1") or die(mysql_error());
			$con->sql_query("update branch_approval_history set ref_id = $adj_id where id = $approval_history_id and branch_id = $vp_session[branch_id]");
		}
		
		//echo"<pre>";print_r($_REQUEST);echo"</pre>";
		header("Location: /vp.disposal.php");
		exit;
		
		/*
		if($errm){
			$_REQUEST['a'] = 'open';
			//print_r($form);
			load_adj(true, false, true);
			$smarty->assign("errm", $errm);
			$smarty->assign("form", $form);
			$smarty->display("vp.disposal.new.tpl");

			exit;
		}
		*/
	}
	
	function ajax_load_adjust_list(){
		global $vp_session, $config, $smarty, $con;

		if (!$t) $t = intval($_REQUEST['t']);
		
		$where = " user_id = " .$vp_session['vp']['link_user_id']. " and (adjustment_type = 'DISPOSAL' or adjustment_type = 'RETURN') and ";

		switch ($t)
		{
			case 0:
				$str = trim($_REQUEST['search']);
				if(!$str)	die('Cannot search empty string');
				if(preg_match("/\d{5}$/", $str) && strlen($str) >=7 ){   // adj no
					$tmp_report_prefix = substr($str, 0, -5);
					$tmp_id = substr($str, -5);
					
					$con->sql_query("select * from branch where report_prefix=".ms($tmp_report_prefix));
					$tmp_bid = mi($con->sql_fetchfield(0));
					$con->sql_freeresult();
					
					if(!$tmp_bid){
						die("Cannot find branch report prefix with '$tmp_report_prefix'");
					}
					$where .= "adj.branch_id=$tmp_bid and adj.id=".mi($tmp_id);
				}else{  // other
					$where .= '(adj.id = '.mi($str) . ' or adj.id like ' . ms('%'.replace_special_char($str)).')';
				}

	//	        $_REQUEST['s']='';
				break;

			case 1: // show saved Adj
				$where .= "adj.status = 0 and adj.active = 1 and adj.branch_id=$vp_session[branch_id]";
				break;

			case 4: // show approved
				$where .= "adj.approved = 1 and adj.active = 1 and adj.branch_id=$vp_session[branch_id]";
				break;

		}

		$con->sql_query($abc="select count(*) from adjustment adj left join branch b1 on b1.id=adj.branch_id where $where");//print $abc;
		$r = $con->sql_fetchrow();
		$total = $r[0];
		$start = intval($_REQUEST['s']);
		if (isset($_REQUEST['sz']))
			$sz = intval($_REQUEST['sz']);
		else{
			if (isset($config['document_page_size'])) $sz=$config['document_page_size'];
				else	$sz = 25;
		}

		if ($total > $sz){
			if ($start > $total) $start = 0;
			// create pagination
			$pg = "<b>Goto Page</b> <select onchange=\"DSP.list_sel($t,this.value);\">";
			for ($i=0,$p=1;$i<$total;$i+=$sz,$p++){
				$pg .= "<option value=$i";
				if ($i == $start){
					$pg .= " selected";
				}
				$pg .= ">$p</option>";
			}
			$pg .= "</select>";
			$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
		}

		$q2 = $con->sql_query($abc="select adj.id, adj.branch_id, adj.status, adjustment_date, adjustment_type, last_update, dept.description as department from adjustment adj left join category dept on dept_id = dept.id where $where order by adj.last_update desc");
		//print $abc;

		while($r2=$con->sql_fetchrow($q2)){
			$list[]=$r2;
		}
		//echo"<pre>";print_r($list);echo"</pre>";
		$smarty->assign("list", $list);
		$smarty->display("vp.disposal.list.tpl");
		exit;

	}
	
	function refresh() {
		global $vp_session, $config, $smarty, $con;
		$_REQUEST['a']='open';
	}
	
	function view() {
		global $vp_session, $config, $smarty, $con;
		$this->load_adj(false, true, false);
		$smarty->display("vp.disposal.new.tpl");
		exit;
	}
	
	function open() {
		global $vp_session, $config, $smarty, $con;
		$this->load_adj();
		$smarty->display("vp.disposal.new.tpl");
		exit;
	}
	
	function refresh_table() {
		global $vp_session, $config, $smarty, $con;
		$this->sort_by = $_REQUEST['sort_by'];
		$this->load_adj();
		$smarty->display("vp.disposal.new.row.tpl");
		exit;
	}
	
	function get_sku_selling_price() {
		global $vp_session, $config, $smarty, $con;
		
		$sku_item_id_list = $_REQUEST['sku_item_id'];
		$branch_id = mi($_REQUEST['branch_id']);
		
		if(!$sku_item_id_list||!$branch_id) return;
		// selling price
		$sql = "select si.id,if(sp.price,sp.price,si.selling_price) as selling_price from sku_items si left join sku_items_price sp on si.id=sp.sku_item_id and sp.branch_id=$branch_id where si.id in (".join(',',$sku_item_id_list).")";
		$con->sql_query($sql);
		while($r = $con->sql_fetchrow()){
			$ret[$r['id']]['selling_price'] = number_format($r['selling_price'], 2);
		}
		
		// stock balance
		$sql = "select sku_item_id,qty from sku_items_cost where branch_id=$branch_id and sku_item_id in (".join(',',$sku_item_id_list).")";
		$con->sql_query($sql);
		while($r = $con->sql_fetchrow()){
			$ret[$r['sku_item_id']]['stock_balance'] = round($r['qty'], $config['global_qty_decimal_points']);
		}
		
		print json_encode($ret);
		exit;
	}
	
	function load_adj($use_tmp = true, $load = true, $check_owner = true,$approval_screen = false){
		
		global $vp_session, $config, $smarty, $con;
		
		$branch_id = $vp_session['branch_id'];

		$adj_id=mi($_REQUEST['id']);
		$link_user_id = $vp_session['vp']['link_user_id'];
		
		if($adj_id>0 && $load){
			$q1=$con->sql_query("select adj.*, user.u as user, bah.approvals, b1.report_prefix, b1.description as b_description, b1.address as b_address, b1.phone_1 as b_phone_1, b1.phone_2 as b_phone_2, b1.phone_3 as b_phone_3, cat.description as dept, b1.company_no as b_company_no
			from adjustment adj 
			left join branch_approval_history bah on bah.id = adj.approval_history_id and bah.branch_id = adj.branch_id
			left join branch b1 on b1.id=adj.branch_id
			left join user on user.id=adj.cancelled_by
			left join category cat on cat.id=dept_id
			where adj.id=$adj_id and adj.branch_id=$branch_id");
			$r1 = $con->sql_fetchrow($q1);		
		
			$sk=$con->sql_query("select sku_items.sku_item_code,sku_items.mcode,sku_items.description,sku_items.artno from sku_items left join adjustment_items on adjustment_items.sku_item_id = sku_items.id where adjustment_items.adjustment_id =".ms($adj_id)." and adjustment_items.branch_id=".ms($branch_id));
			$ex = $con->sql_fetchrow($sk);
			$smarty->assign("ex",$ex);
			
			if ($r1['approval_history_id']>0){
				$q0=$con->sql_query("select i.timestamp, i.log, i.status, user.u
	from branch_approval_history_items i
	left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
	left join user on i.user_id = user.id
	where h.ref_table = 'ADJUSTMENT' and i.branch_id = $branch_id and i.approval_history_id = $r1[approval_history_id] 
	order by i.timestamp");
				$smarty->assign("approval_history", $con->sql_fetchrowset($q0));
			}		
			if($approval_screen){
				$r1['approval_screen']=1;	
			}			
			
		}else{
			
			//print 'new one';
			// get the refresh data
			$refresh_data = array('branch_id','adjustment_date','adjustment_type','dept_id','remark');
			foreach($refresh_data as $tmp_col){
				if(isset($_REQUEST[$tmp_col]))	$r1[$tmp_col] = $_REQUEST[$tmp_col];
			}
			
		}
		$smarty->assign("form", $r1);
		
		$sql = "select si.id,si.mcode, si.sku_item_code, si.artno, si.description, if(si.scale_type=-1, sku.scale_type, si.scale_type) as scale_type, if(sip.price is null, si.selling_price, sip.price) as price, sip.trade_discount_code,
			si.link_code, puom.code as packing_uom_code,c.description as category, si.doc_allow_decimal
			from sku_group_item sgi
			left join sku_items si on si.sku_item_code=sgi.sku_item_code
			left join uom puom on puom.id = si.packing_uom_id
			left join sku on sku.id=si.sku_id
			left join category_cache cc on cc.category_id = sku.category_id
			left join category c on c.id = cc.p4
			left join sku_items_price sip on si.id = sip.sku_item_id and sip.branch_id=".mi($branch_id)."
			join sku_group_vp_date_control vpdc on vpdc.branch_id=sgi.branch_id and vpdc.sku_group_id=sgi.sku_group_id and vpdc.sku_item_id=si.id and curdate() between vpdc.from_date and vpdc.to_date
			where sgi.branch_id=".mi($vp_session['sku_group_bid'])." and sgi.sku_group_id=".mi($vp_session['sku_group_id']);//print $sql;
		$q4=$con->sql_query($sql);
		$group_items = $con->sql_fetchrowset();

		if($adj_id > 0) {
			$sql = "select ai.*, sku_items.sku_item_code, sku_items.description as description, sku_items.artno, sku_items.mcode, sku_items.doc_allow_decimal, puom.code as packing_uom_code,c.description as category
			from adjustment_items ai
			left join sku_items on ai.sku_item_id=sku_items.id
			left join sku on sku.id=sku_items.sku_id
			left join category_cache cc on cc.category_id = sku.category_id
			left join category c on c.id = cc.p4
			left join uom puom on puom.id = sku_items.packing_uom_id
			where adjustment_id = $adj_id and ai.branch_id = $branch_id order by ai.id";//print $sql;
			$q3=$con->sql_query($sql);
			$items = $con->sql_fetchrowset();
			
			if ($r1['status'] == '0') { // only add rows if this adj not confirmed yet
				$curr_sku_item_id = array();
				foreach ($items as $ikey => $ivalue) {
					$curr_sku_item_id[] = $ivalue['sku_item_id'];
				}
				
				foreach ($group_items as $gikey => $givalue) {
					if (!in_array($givalue['id'],$curr_sku_item_id)) {
						$givalue['new_item'] = 1;
						$items[] = $givalue;
					}
				}
			}
			
		}
		
		else {
			$items = $group_items;
		}
		//echo"<pre>";print_r($r1);echo"</pre>";
		if ($items) {
			foreach ($items as $ikey => $ivalue) {
				// selling price
				$sql = "select si.id,if(sp.price,sp.price,si.selling_price) as selling_price from sku_items si left join sku_items_price sp on si.id=sp.sku_item_id and sp.branch_id=$branch_id where si.id = $ivalue[id] limit 1";
				$con->sql_query($sql);
				$items[$ikey]['selling_price'] = number_format($con->sql_fetchfield(0),2);
				
				// stock balance
				$sql = "select qty,grn_cost from sku_items_cost where branch_id=$branch_id and sku_item_id = $ivalue[id] limit 1";
				$con->sql_query($sql);
				while($r = $con->sql_fetchrow()){
					$items[$ikey]['stock_balance'] = round($r['qty'], $config['global_qty_decimal_points']);
					$items[$ikey]['cost'] = $r['grn_cost'];
				}
			}
			if (!$_REQUEST['sort_by']) $this->sort_by = 'category'; //default sort by - on list loading
			
			if($this->sort_by == 'category') usort($items,array($this,'sort_cat'));
			else usort($items,array($this,'sort_si'));
		}
		
		//echo"<pre>";print_r($items);echo"</pre>";
		
		$smarty->assign("adjust_items", $items);
		$item_per_page = $config['vp_disposal_print_item_per_page']?$config['vp_disposal_print_item_per_page']:35;
	  
		$con->sql_query("select * from branch where id=$branch_id");
		$smarty->assign('from_branch', $con->sql_fetchrow());

		if($config['adjustment_use_custom_print']){
			$smarty->assign('cost_enable', $_REQUEST['cost_enable']);
			$smarty->assign('sp_enable', $_REQUEST['sp_enable']);
		}

		if($_REQUEST['a']=='print_disposal'){
			$smarty->assign("item_per_page", $item_per_page);
			$totalpage = ceil(count($items)/$item_per_page);
			for ($i=0,$page=1;$i<count($items);$i+=$item_per_page,$page++){
				$smarty->assign("page", "Page $page of $totalpage");
				$smarty->assign("start_counter", $i);
				$smarty->assign("adjust_items", array_slice($items,$i,$item_per_page));
				$smarty->display("vp.disposal.print.tpl");
				$smarty->assign("skip_header",1);
			}  
		}
		return $r1;
	}

	function update_sku_item_cost($id,$branch_id){
		global $vp_session, $config, $smarty, $con;
		$con->sql_query("update sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (select sku_item_id from adjustment_items adj_items left join adjustment adj on adj.id=adj_items.adjustment_id and adj.branch_id=adj_items.branch_id where adj_items.adjustment_id=$id and adj_items.branch_id=$branch_id)");
	}

	function generate_approval_history($adj_id){
		global $vp_session, $config, $smarty, $con;
		
		// insert branch approval history
		$ah_ins = array();
		$ah_ins['approval_flow_id'] = 0;
		$ah_ins['ref_table'] = "ADJUSTMENT";
		$ah_ins['active'] = 1;
		$ah_ins['flow_approvals'] = $ah_ins['approved_by'] = "|".$vp_session['vp']['link_user_id']."|";
		$ah_ins['approvals'] = "|";
		$ah_ins['notify_users'] = "||";
		$ah_ins['added'] = "CURRENT_TIMESTAMP";
		$ah_ins['status'] = 1;
		$ah_ins['approval_order_id'] = 4;
		
		$con->sql_query("select approval_history_id from adjustment where id = $adj_id and branch_id = $vp_session[branch_id] limit 1");
		if ($approval_history_id = $con->sql_fetchfield(0)) {
			$con->sql_query("update branch_approval_history set ".mysql_update_by_field($ah_ins). " where id = $approval_history_id and branch_id = $vp_session[branch_id]");
		}
		else {
			$ah_ins['branch_id'] = $vp_session['branch_id'];
			$con->sql_query("insert into branch_approval_history ".mysql_insert_by_field($ah_ins));
			$approval_history_id = $con->sql_nextid();
			log_vp($vp_session['id'], "VP DISPOSAL", $adj_id, "Insert Approval History - ID $approval_history_id , Adjustment $adj_id , Branch $vp_session[branch_id]");
			log_br($vp_session['vp']['link_user_id'], "VP DISPOSAL", $adj_id, "Insert Approval History - ID $approval_history_id , Adjustment $adj_id , Branch $vp_session[branch_id]");
		}
		
		$ah_ins = array();
		$ah_ins['approval_history_id'] = $approval_history_id;
		$ah_ins['user_id'] = $vp_session['vp']['link_user_id'];
		$ah_ins['log'] = "Approved";
		$ah_ins['timestamp'] = "CURRENT_TIMESTAMP";
		$ah_ins['status'] = 1;
		$ah_ins['branch_id'] = $vp_session['branch_id'];
		
		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($ah_ins));
		
		return $approval_history_id;
	}
	
	function print_disposal(){
		global $vp_session;
		$this->load_adj(false, true, false);
		log_vp($vp_session['id'], "VP DISPOSAL", $_REQUEST['id'], "Print: No.".sprintf("%05d", $_REQUEST['id']));
		exit;
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
}

$disposal = new Disposal('Disposal');

?>
