<?php
/*
5/22/2019 3:44 PM Andy
- Added stockTakeManager.

11/28/2019 1:46 PM Andy
- Fixed Cycle Count not to list inactive sku.

12/19/2019 10:40 AM Justin
- Bug fixed send email function couldn't send out the email.

1/7/2020 5:41 PM Justin
- Removed the IsMail since it causes customers who are using smtp couldn't send out email.

3/27/2020 10:44 AM William
- Enhanced to insert id manually for stock_take_pre table that use auto increment.
*/

class stockTakeManager{
	public $cycleCountContentTypeList = array(
		'cat_vendor_brand' => array('desc' => 'Category + Vendor + Brand'),
		'sku_group' => array('desc' => 'SKU Group'),
	);
	public $cycleCountTooManySKUCount = 200;
	
	function __construct(){
		
	}
	
	
	
	// function to get estimate sku list according to stock take content
	public function getCycleCountEstimateSKUListing($cc = false, $params = array()){
		global $con, $appCore;
		
		if($params['count_only'])	$count_only = 1;
		if($params['limit'])	$limit = mi($params['limit']);
		if($params['into_tmp_table'])	$into_tmp_table = 1;
		
		if($cc){
			if($cc['st_content_type'] == 'sku_group'){
				$st_content_type = 'sku_group';
				$sku_group_bid = mi($cc['sku_group_bid']);
				$sku_group_id = mi($cc['sku_group_id']);
			}elseif($cc['st_content_type'] == 'cat_vendor_brand'){
				$st_content_type = 'cat_vendor_brand';
				$category_id = mi($cc['category_id']);
				$vendor_id = mi($cc['vendor_id']);
				$brand_id = mi($cc['brand_id']);
			}
		}else{
			if($params['st_content_type'] == 'sku_group'){
				$st_content_type = 'sku_group';
				$sku_group_bid = mi($params['sku_group_bid']);
				$sku_group_id = mi($params['sku_group_id']);
			}elseif($params['st_content_type'] == 'cat_vendor_brand'){
				$st_content_type = 'cat_vendor_brand';
				$category_id = mi($params['category_id']);
				$vendor_id = mi($params['vendor_id']);
				$brand_id = mi($params['brand_id']);
			}
		}
		
		if($st_content_type != 'sku_group' && $st_content_type != 'cat_vendor_brand'){
			return array('error' => 'Invalid Stock Take Content Type');
		}
		
		$filter = array();
		$filter[] = "si.active=1";
		$xtra_join = '';
		if($st_content_type == 'sku_group'){
			// SKU Group
			$xtra_join = "join sku_group_item sgi on sgi.branch_id=$sku_group_bid and sgi.sku_group_id=$sku_group_id and sgi.sku_item_code=si.sku_item_code";
		}elseif($st_content_type == 'cat_vendor_brand'){
			// Cat + Vendor + Brand			
			if($category_id>0){
				$cat_info = $appCore->categoryManager->getCategoryInfo($category_id);
				if(!$cat_info){
					return array('error' => 'Invalid Category ID');
				}
				$filter[] = "cc.p".mi($cat_info['level'])."=$category_id";
			}
			if($vendor_id>0){
				$filter[] = "sku.vendor_id=$vendor_id";
			}
			if($brand_id>=0){
				$filter[] = "sku.brand_id=$brand_id";
			}
		}
		
		// Exclude No inventory
		$filter[] = "if(sku.no_inventory='inherit', cc.no_inventory,sku.no_inventory)<>'yes'";
		if($count_only){
			$str_filter = '';
			if($filter)	$str_filter = 'where '.join(' and ', $filter);
			
			$sql = "select count(*) as c
				from sku_items si
				join sku on sku.id=si.sku_id
				left join category c on c.id=sku.category_id
				left join category_cache cc on cc.category_id=c.id
				$xtra_join
				$str_filter";
			//print $sql;exit;
			$con->sql_query($sql);
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$result = array();
			$result['ok'] = 1;
			$result['sku_count'] = $tmp['c'];
			
			return $result;
		}else{
			$str_filter = '';
			if($filter)	$str_filter = 'where '.join(' and ', $filter);
			
			$str_limit = '';
			if($limit>0){
				$str_limit = "limit $limit";
			}
			$str_order = "order by si.description";
			
			$result = array();
			$result['ok'] = 1;
			
			if(!$into_tmp_table){
				$xtra_col .= ", si.sku_item_code, si.mcode, si.artno, si.link_code, si.description";
			}
			
			$sql = "select si.id as sid $xtra_col
				from sku_items si
				join sku on sku.id=si.sku_id
				left join category c on c.id=sku.category_id
				left join category_cache cc on cc.category_id=c.id
				$xtra_join
				$str_filter
				$str_order
				$str_limit";
			//print $sql;exit;
			if($into_tmp_table){
				// Put Data into TMP Table
				$result['tmp_tablename'] = $tmp_tablename = 'tmp_cycle_count_sku_list_'.time();
				$con->sql_query("create temporary table if not exists $tmp_tablename (
					id int not null primary key auto_increment,
					sku_item_id int not null default 0 unique
				)");
				$con->sql_query("insert into $tmp_tablename
				(sku_item_id)
				($sql)");
			}else{
				// Return Data as array
				$result['item_list'] = array();
				$con->sql_query($sql);
				while($r = $con->sql_fetchassoc()){
					$result['item_list'][$r['sid']] = $r;
				}
				$con->sql_freeresult();
			}
			
			
			return $result;
		}
	}
	
	public function loadCycleCount($bid, $cc_id){
		global $con, $LANG;
		
		$bid = mi($bid);
		$cc_id = mi($cc_id);
		
		if(!$bid || !$cc_id){
			return array(false, sprintf($LANG['CYCLE_COUNT_INVALID_ID'], $cc_id));
		}
		
		$con->sql_query("select cc.*, u_pic.u as pic_username, bah.approvals
			from cycle_count cc
			left join user u_pic on u_pic.id=cc.pic_user_id
			left join branch_approval_history bah on bah.branch_id=cc.branch_id and bah.id=cc.approval_history_id
			where cc.branch_id=$bid and cc.id=$cc_id");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$form){
			return array(false, $LANG['CYCLE_COUNT_NOT_FOUND']);
		}
		
		$form['audit_user_list'] = unserialize($form['audit_user_list']);
		$form['notify_user_list'] = unserialize($form['notify_user_list']);
		
		if($form['sku_group_bid'] && $form['sku_group_id']){
			$form['tmp_sku_group_id'] = $form['sku_group_bid'].'_'.$form['sku_group_id'];
		}
		
		if ($form['approval_history_id']>0){
			$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u, i.more_info
	from branch_approval_history_items i
	left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
	left join user on i.user_id=user.id
	where h.ref_table='cycle_count' and i.branch_id=$bid and i.approval_history_id=$form[approval_history_id]
	order by i.timestamp");
			$form['approval_history'] = array();
			while($r = $con->sql_fetchassoc($q2)){
				$r['more_info'] = unserialize($r['more_info']);
				$form['approval_history'][] = $r;
			}
			$con->sql_freeresult($q2);
			//$smarty->assign("approval_history", $form['approval_history']);
		}
		
		if($form['printed']){
			// Get Total Page
			$con->sql_query("select max(page_num) as totalpage from cycle_count_items where branch_id=$bid and cc_id=$cc_id");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			$form['item_totalpage'] = mi($tmp['totalpage']);
		}
		
		if($form['wip']){
			// Get WIP Percent
			$con->sql_query("select count(*) as c from cycle_count_items where branch_id=$bid and cc_id=$cc_id and (backend_qty is not null or app_qty is not null)");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			$form['st_sku_count'] = mi($tmp['c']);
			$form['st_sku_per'] = round($form['st_sku_count'] / $form['estimate_sku_count'] * 100, 2);
		}
	
		return array($form);
	}
	
	public function cycleCountApproval($bid, $cc_id, $status_type, $params = array()){
		global $con, $sessioninfo, $smarty, $config, $approval_on_behalf;

		$approved=0;
		$status = 0;
		$approval_status = array(1 => "Approved", 2 => "Rejected", 4 => "Terminated");
		list($form, $err_msg) = $this->loadCycleCount($bid, $cc_id);
		//print_r($form);exit;
		///////////////////////////////////////
		$aid = $form['approval_history_id'];
		//$approvals = $form['approvals'];
		//die("aid = ".$aid);
		
		
		if($status_type == 'approve'){
			$comment="Approved";
			$status = 1;
			$params = array();
			$params['approve'] = 1;
			$params['user_id'] = $sessioninfo['id'];
			$params['id'] = $aid;
			$params['branch_id'] = $bid;
			$params['update_approval_flow'] = true;
			//$params['auto_approve'] = true;
			$is_last = check_is_last_approval_by_id($params, $con);	
			if($is_last)  $approved = 1;
		}
		else{
			$upd = array();
			if($status_type == 'reject'){
				$status = 2;
			}elseif($status_type == 'cancel'){
				$status = 5;
			}
			$comment = trim($params['comment']);
			
			$upd['status'] = $status;
			$con->sql_query("update branch_approval_history set ".mysql_update_by_field($upd)." where id = $aid and branch_id = $bid");
		}

		//if ($approval_on_behalf) {
		//	$comment .= " (by ".$approval_on_behalf['on_behalf_by_u']." on behalf of ".$approval_on_behalf['on_behalf_of_u'].")";
		//}

		$upd = array();
		$upd['approval_history_id'] = $aid;
		$upd['branch_id'] = $bid;
		$upd['user_id'] = $sessioninfo['id'];
		$upd['status'] = $status;
		$upd['log'] = $comment;
		
		if($upd['more_info'])	$upd['more_info'] = serialize($upd['more_info']);
		
		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
		
		$upd2 = array();
		$upd2['status'] = $status;
		$upd2['approved'] = $approved;
		if($status == 5)	$upd2['active'] = 0;
		$upd2['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update cycle_count set ".mysql_update_by_field($upd2)." where id=$cc_id and branch_id=$bid");
		
		$to = get_pm_recipient_list2($cc_id, $aid, $status, 'approval', $bid, 'cycle_count');
		$status_str = ($is_last || $status != 1) ? $approval_status[$status] : '';
		send_pm2($to, "Cycle Count Approval (ID#$cc_id) $status_str", "admin.cycle_count.assignment.php?a=view&id=$cc_id&branch_id=".$bid, array('module_name'=>'cycle_count'));

		if ($approved)
			$status_msg="Fully Approved";
		elseif ($status==1)
			$status_msg="Approved";
		elseif ($status==2)
			$status_msg="Rejected";
		elseif ($status==5)
			$status_msg="Cancelled/Terminated";
		else
			die("WTF?");

		log_br($sessioninfo['id'], 'CYCLE COUNT', $cc_id, "Cycle Count $status_msg by $sessioninfo[u] (ID#$cc_id)");
	}
	
	public function loadCycleCountItems($bid, $cc_id, $params = array()){
		global $con;
		
		$bid = mi($bid);
		$cc_id = mi($cc_id);
		
		if(isset($params['page_num']))	$page_num = mi($params['page_num']);
		
		$filter = array();
		$filter[] = "cci.branch_id=$bid and cci.cc_id=$cc_id";
		if($page_num>0)	$filter[] = "cci.page_num=$page_num";
		
		$item_list = array();
		$str_filter = "where ".join(' and ', $filter );
		$sql = "select cci.*, si.sku_id, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description, uom.code as packing_uom_code, uom.fraction as packing_uom_fraction, si.doc_allow_decimal, if(sku.is_fresh_market='inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market
			from cycle_count_items cci
			join sku_items si on si.id=cci.sku_item_id
			left join sku on sku.id=si.sku_id
			left join uom on uom.id=si.packing_uom_id
			left join category_cache cc on cc.category_id=sku.category_id
			$str_filter
			order by cci.page_num, cci.row_num, cci.item_id";
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
			$item_list[$r['item_id']] = $r;
		}
		$con->sql_freeresult();
		
		return $item_list;
	}
	
	public function startCycleCount($bid, $cc_id, $params = array()){
		global $con, $LANG;
		
		$bid = mi($bid);
		$cc_id = mi($cc_id);
		
		// Load Cycle Count
		list($form, $err_msg) = $this->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			return array('error' => $err_msg);
		}
		
		// Check Status
		if($form['active'] != 1 || $form['status'] != 1 || $form['approved'] != 1 || $form['printed'] != 1 || $form['wip'] != 0){
			return array('error' => $LANG['CYCLE_COUNT_NOT_ALLOW_TO_START']);
		}
		
		// Got pass user, so need to check user
		$user_id = 1;
		if($params['user_id']){
			if($params['user_id'] != $form['pic_user_id']){
				return array('error' => $LANG['CYCLE_COUNT_ONLY_PIC_CAN_START']);
			}
			$user_id = mi($params['user_id']);
		}
		
		$upd = array();
		$upd['st_date'] = date("Y-m-d");
		$upd['wip'] = 1;
		$upd['last_update'] = $upd['wip_start_time'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("update cycle_count set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$cc_id");
		log_br($user_id, 'CYCLE COUNT', $cc_id, "Cycle Count Start ($form[doc_no])");
		
		$ret = array();
		$ret['ok'] = 1;
		return $ret;
	}
	
	public function resetCycleCountToSave($bid, $cc_id, $user_id, $params = array()){
		global $con, $LANG, $config;
		
		$bid = mi($bid);
		$cc_id = mi($cc_id);
		$user_id = mi($user_id);
		$reason = trim($params['reason']);
		
		if(!$user_id){
			return array('error' => 'User ID Required for Reset.');
		}
		
		// Load Cycle Count
		list($form, $err_msg) = $this->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			return array('error' => $err_msg);
		}
		
		// Check Status
		if($form['active'] != 1 || $form['status'] != 1 || $form['approved'] != 1 || $form['printed'] != 0){
			return array('error' => $LANG['CYCLE_COUNT_RESET_STATUS_WRONG']);
		}
		
		$aid = mi($form['approval_history_id']);
		
		if($aid){
			$upd = array();
			$upd['approval_history_id'] = $aid;
			$upd['branch_id'] = $bid;
			$upd['user_id'] = $user_id;
			$upd['status'] = 0;
			$upd['log'] = $reason;

			$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd));
			$con->sql_query("update branch_approval_history set status=0,approved_by='' where id = $aid and branch_id = $bid");
		}
		
		$upd = array();
		$upd['active'] = 1;
		$upd['status'] = 0;
		$upd['approved'] = 0;
		$upd['printed'] = 0;
		$upd['wip'] = 0;
		$upd['completed'] = 0;
		$upd['sent_to_stock_take'] = 0;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("update cycle_count set ".mysql_update_by_field($upd)." where id=$cc_id and branch_id=$bid");

		log_br($user_id, 'CYCLE COUNT', $cc_id, "Cycle Count Reset ($form[doc_no])");

		return array('ok'=>1);
	}
	
	public function processCycleCountComplete($bid, $cc_id, $params = array()){
		global $con, $LANG, $config;
		
		$bid = mi($bid);
		$cc_id = mi($cc_id);
		
		// Load Cycle Count
		list($form, $err_msg) = $this->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			return array('error' => $err_msg);
		}
		
		// Generate POS Qty
		$result = $this->generateCycleCountPOSQty($bid, $cc_id);
		if(!$result['ok'])	return array('error' => $result['error']);
		
		return array('ok'=>1);
	}
	
	public function generateCycleCountPOSQty($bid, $cc_id, $params = array()){
		global $con, $LANG, $config;
		
		$bid = mi($bid);
		$cc_id = mi($cc_id);
		
		// Load Cycle Count
		list($form, $err_msg) = $this->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			return array('error' => $err_msg);
		}
		
		if(!($form['wip'] && $form['completed'] && !$form['sent_to_stock_take'])){
			return array('error' => $LANG['CYCLE_COUNT_REGEN_POS_QTY_STATUS_WRONG']);
		}
		
		$sql = "select cci.*,
			(
			select sum(pi.qty)
			from pos_items pi
			join pos p on p.branch_id=pi.branch_id and p.counter_id=pi.counter_id and p.date=pi.date and p.id=pi.pos_id
			where p.cancel_status=0 and p.branch_id=cc.st_branch_id and pi.sku_item_id=cci.sku_item_id and p.date >= cc.st_date and p.date<= cci.st_time and p.pos_time<=cci.st_time 
			) as new_pos_qty
			from cycle_count_items cci
			join cycle_count cc on cc.branch_id=cci.branch_id and cc.id=cci.cc_id
			where cci.branch_id=$bid and cci.cc_id=$cc_id
			order by cci.item_id";
		
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$upd = array();
			$upd['pos_qty'] = mi($r['new_pos_qty']);
			
			// Calculate Stock Take Qty
			$upd['calculated_st_qty'] = '';
			if(trim($r['backend_qty'])!==''){
				$upd['calculated_st_qty'] += mf($r['backend_qty']);
			}
			if(trim($r['app_qty'])!==''){
				$upd['calculated_st_qty'] += mf($r['app_qty']);
			}
			if($upd['calculated_st_qty']!==''){
				$upd['calculated_st_qty'] += $upd['pos_qty'];
			}
			
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("update cycle_count_items set ".mysql_update_by_field($upd, false, true)." where item_guid=".ms($r['item_guid']));
		}
		$con->sql_freeresult($q1);
		
		$upd2 = array();
		$upd2['generated_pos_qty_time'] = 'CURRENT_TIMESTAMP';
		$upd2['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update cycle_count set ".mysql_update_by_field($upd2)." where branch_id=$bid and id=$cc_id");
		
		return array('ok'=>1);
	}
	
	public function reopenCycleCountWIP($bid, $cc_id, $user_id, $params = array()){
		global $con, $LANG, $config;
		
		$bid = mi($bid);
		$cc_id = mi($cc_id);
		$user_id = mi($user_id);
		
		// Load Cycle Count
		list($form, $err_msg) = $this->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			return array('error' => $err_msg);
		}
		
		if(!($form['wip'] && $form['completed'] && !$form['sent_to_stock_take'])){
			return array('error' => $LANG['CYCLE_COUNT_REOPEN_STATUS_WRONG']);
		}
		
		$upd = array();
		$upd['completed'] = 0;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("update cycle_count set ".mysql_update_by_field($upd)." where id=$cc_id and branch_id=$bid");
		
		log_br($user_id, 'CYCLE COUNT', $cc_id, "Cycle Count Re-open to WIP ($form[doc_no])");
		
		return array('ok'=>1);
	}
	
	public function sendCycleCountToStoreStockTake($bid, $cc_id, $params = array()){
		global $con, $LANG, $config, $appCore;
		
		$bid = mi($bid);
		$cc_id = mi($cc_id);
		
		// Load Cycle Count
		list($form, $err_msg) = $this->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			return array('error' => $err_msg);
		}
		
		if(!($form['wip'] && $form['completed'] && !$form['sent_to_stock_take'])){
			return array('error' => $LANG['CYCLE_COUNT_SEND_ST_STATUS_WRONG']);
		}
		
		// Zero-lise no stock take sku
		if($params['zerolise_non_st'])	$zerolise_non_st = mi($params['zerolise_non_st']);
		$user_id = 1;
		if($params['user_id'])	$user_id = mi($params['user_id']);
		
		// Total Page
		$totalpage = mi($form['item_totalpage']);
		for($page_num = 1; $page_num <= $totalpage; $page_num++){
			// Get Item in this Page
			$item_list = $this->loadCycleCountItems($bid, $cc_id, array('page_num'=>$page_num));
			
			// Loop Items
			foreach($item_list as $item_id => $r){
				$upd = array();
				
				if(trim($r['calculated_st_qty']) === ''){	// Empty, no stock take
					if(!$zerolise_non_st)	continue;	// Skip this item
				}
				$sid = mi($r['sku_item_id']);
				
				// Fresh Market SKU
				if($r['is_fresh_market']=='yes'){
					// Find Parent SKU
					$con->sql_query("select id from sku_items where sku_id=".mi($r['sku_id'])." and is_parent=1 order by id limit 1");
					$parent_si = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($parent_si){
						$sid = $parent_si['id'];
					}
					$upd['is_fresh_market'] = 1;
				}				
				
				$upd['id'] = $appCore->generateNewID("stock_take_pre", "branch_id=".mi($form['st_branch_id']));
				$upd['date'] = $form['st_date'];
				$upd['location'] = 'CC_'.$form['doc_no'];
				$upd['shelf'] = 'Page'.$page_num;
				$upd['qty'] = mf($r['calculated_st_qty']);
				$upd['branch_id'] = $form['st_branch_id'];
				$upd['user_id'] = $user_id;
				$upd['sku_item_id'] = $sid;
				$upd['imported'] = 0;
				$upd['cycle_count_doc_no'] = $form['doc_no'];
				$con->sql_query("insert into stock_take_pre ".mysql_insert_by_field($upd));
			}
			
		}
		
		$upd = array();
		$upd['sent_to_stock_take'] = 1;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['sent_to_stock_take_time'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("update cycle_count set ".mysql_update_by_field($upd)." where id=$cc_id and branch_id=$bid");
		
		log_br($user_id, 'CYCLE COUNT', $cc_id, "Cycle Count Sent to Store Stock Take ($form[doc_no]), Zero-lise Non-Stock Take SKU: ".($zerolise_non_st?'Yes':'No'));
		
		return array('ok'=>1);
	}
	
	public function recallCycleCountFromStoreStockTake($bid, $cc_id, $params = array()){
		global $con, $LANG, $config;
		
		$bid = mi($bid);
		$cc_id = mi($cc_id);
		$user_id = 1;
		if($params['user_id'])	$user_id = mi($params['user_id']);
		
		// Load Cycle Count
		list($form, $err_msg) = $this->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			return array('error' => $err_msg);
		}
		
		if(!($form['wip'] && $form['completed'] && $form['sent_to_stock_take'])){
			return array('error' => $LANG['CYCLE_COUNT_RECALL_ST_STATUS_WRONG']);
		}
		
		// Check whether already imported
		$con->sql_query("select count(*) as c from stock_take_pre where branch_id=".mi($form['st_branch_id'])." and cycle_count_doc_no=".ms($form['doc_no'])." and imported=1");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($tmp['c']){
			return array('error' => $LANG['CYCLE_COUNT_RECALL_ST_ALREADY_IMPORTED']);
		}
		
		$con->sql_query("delete from stock_take_pre where branch_id=".mi($form['st_branch_id'])." and cycle_count_doc_no=".ms($form['doc_no'])." and imported=0");
		
		$upd = array();
		$upd['sent_to_stock_take'] = 0;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['sent_to_stock_take_time'] = '0';
		
		$con->sql_query("update cycle_count set ".mysql_update_by_field($upd)." where id=$cc_id and branch_id=$bid");
		
		log_br($user_id, 'CYCLE COUNT', $cc_id, "Cycle Count Recall from Store Stock Take ($form[doc_no])");
		
		return array('ok'=>1);
	}
	
	public function sendCycleCountDueNotification($bid, $cc_id){
		global $con, $LANG, $config;
		
		$bid = mi($bid);
		$cc_id = mi($cc_id);
		
		// Load Cycle Count
		list($form, $err_msg) = $this->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			return array('error' => $err_msg);
		}
		
		$to_user_id_list = array();
		
		// Person in Charge
		$to_user_id_list []= mi($form['pic_user_id']);
		
		// Audit User
		if($form['audit_user_list']){
			foreach($form['audit_user_list'] as $tmp_user_id){
				$to_user_id_list []= mi($tmp_user_id);
			}
		}
		
		// Notify User
		if($form['notify_user_list']){
			foreach($form['notify_user_list'] as $tmp_user_id){
				$to_user_id_list []= mi($tmp_user_id);
			}
		}
		
		$sent_count = 0;
		if($to_user_id_list){
			$msg = "Cycle Count (".$form['doc_no'].") is due at next month (".$form['propose_st_date'].")";
			$url = "admin.cycle_count.assignment.php?a=view&id=$cc_id&branch_id=$bid";
			
			include_once("include/class.phpmailer.php");
			$mailer = new PHPMailer(true);
			//$mailer->From = "noreply@localhost";
			$mailer->FromName = "ARMS Notification";
			$mailer->Subject = "Cycle Count (".$form['doc_no'].") Due Date is Near";
			$mailer->IsHTML(true);

			$email_msg_header = "<h2><u>ARMS Notification</u></h2>";
			$email_msg_header .= "$msg<br />";
	
			foreach($to_user_id_list as $tmp_user_id){	
				// PM
				$upd = array();
				$upd['branch_id'] = $bid;
				$upd['from_user_id'] = 1;
				$upd['to_user_id'] = $tmp_user_id;
				$upd['msg'] = $msg;
				$upd['url'] = $url;
				$upd['added'] = 'CURRENT_TIMESTAMP';
				
				$con->sql_query("insert into pm ".mysql_insert_by_field($upd));
	            $new_pm_id = $con->sql_nextid();
				
				// Get Email Address
				$email_address = get_user_info_by_colname($tmp_user_id, "email");
				if($mailer->ValidateAddress($email_address)){
					$mailer->AddAddress($email_address);
					
					// PM
					$pm_path = "pm.php?a=view_pm&branch_id=$bid&id=$new_pm_id";
					
					$email_msg_sample = $email_msg_header;
					
					if($config['main_server_url']){
						$email_msg_sample .= "<b>LAN</b>: <a href='http://".$config['main_server_url']['lan']."/$pm_path'>View PM</a>";
						$email_msg_sample .= "<br><b>WAN</b>: <a href='http://".$config['main_server_url']['wan']."/$pm_path'>View PM</a>";
					}else{
						$server_url = '';
						if($config['server_url']){
							$server_url = trim($config['server_url']);
						}
						if(!$server_url && isset($_SERVER['HTTP_HOST'])){
							$server_url = "http://".$_SERVER['HTTP_HOST'];
						}
						
						if($server_url){
							$url2 = $server_url."/$pm_path";
							$email_msg_sample .= "<a href=\"".$url2."\">Click here to view the message.</a>";
						}
					}
					
					$mailer->Body = $email_msg_sample;
					
					// send the mail
					//$send_success = $mailer->Send();
					$send_success = phpmailer_send($mailer, $mailer_info);
					//$mailer->to = array();  // clear the address list
					$mailer->ClearAddresses();
					
					if($send_success)	$sent_count++;
				}
			}
			
			$upd = array();
			$upd['notify_sent'] = 1;
			$upd['notify_sent_time'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("update cycle_count set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$cc_id");
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['sent_count'] = $sent_count;
		
		return $ret;
	}
	
	public function cloneCycleCount($bid, $cc_id, $clone_type='normal', $params=array()){
		global $con, $LANG, $config, $sessioninfo, $appCore;
		
		$bid = mi($bid);
		$cc_id = mi($cc_id);
		$user_id = $sessioninfo ? mi($sessioninfo['id']) : 1;
		
		// Load Cycle Count
		list($form, $err_msg) = $this->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			return array('error' => $err_msg);
		}
		
		$propose_st_date_list = array();
		if($clone_type == 'advanced'){
			if(!$params['propose_st_date_list'] || !is_array($params['propose_st_date_list'])){
				return array('error' => $LANG['CYCLE_COUNT_INVALID_CLONE_DATE']);
			}
			$propose_st_date_list = $params['propose_st_date_list'];
			
			$series_doc_no = $form['series_doc_no'] ? $form['series_doc_no'] : $form['doc_no'];
		}else{
			// Clone using the same date
			$propose_st_date_list[] = $form['propose_st_date'];
		}
		
		$branch_info = $appCore->branchManager->getBranchInfo($bid);
		$report_prefix = trim($branch_info['report_prefix']);
		
		
		// Construct clone data
		$new_form = array();
		$new_form['branch_id'] = $form['branch_id'];
		$new_form['user_id'] = $user_id;
		$new_form['st_branch_id'] = $form['st_branch_id'];
		$new_form['st_content_type'] = $form['st_content_type'];
		$new_form['category_id'] = $form['category_id'];
		$new_form['vendor_id'] = $form['vendor_id'];
		$new_form['brand_id'] = $form['brand_id'];
		$new_form['sku_group_bid'] = $form['sku_group_bid'];
		$new_form['sku_group_id'] = $form['sku_group_id'];
		//$new_form['propose_st_date'] = $new_form['st_date'] = $form['propose_st_date'];
		$new_form['remark'] = $form['remark'];
		$new_form['pic_user_id'] = $form['pic_user_id'];
		$new_form['audit_user_list'] = serialize($form['audit_user_list']);
		$new_form['notify_user_list'] = serialize($form['notify_user_list']);
		$new_form['notify_day'] = $form['notify_day'];
		$new_form['estimate_sku_count'] = $form['estimate_sku_count'];
		$new_form['added'] = $new_form['last_update'] = 'CURRENT_TIMESTAMP';
		
		if($clone_type == 'advanced'){
			$new_form['series_doc_no'] = $series_doc_no;
			
			// Current cycle count don hv series_doc_no
			if(!$form['series_doc_no']){
				$upd = array();
				$upd['series_doc_no'] = $series_doc_no;
				$upd['last_update'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("update cycle_count set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$cc_id");
			}
		}
		$id_list = array();
		// Loop each date
		foreach($propose_st_date_list as $propose_st_date){
			$new_form['propose_st_date'] = $new_form['st_date'] = $propose_st_date;
			
			// Get Max ID
			$con->sql_query("select max(id) as max_id from cycle_count where branch_id=$bid FOR UPDATE");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$new_id = mi($tmp['max_id'])+1;
			$new_form['id'] = $new_id;
			$new_form['doc_no'] = $report_prefix.sprintf("%05d", $new_id);
			
			$con->sql_query("insert into cycle_count ".mysql_insert_by_field($new_form));
			log_br($user_id, 'CYCLE COUNT', $new_id, "Cloned: (ID#$new_id, Doc No: ".$new_form['doc_no'].") from ".$form['doc_no']);
			
			$id_list[] = $new_id;
		}
		
		if(!$id_list){
			return array('error' => $LANG['CYCLE_COUNT_CLONE_FAILED']);
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['id_list'] = $id_list;
		
		return $ret;
	}
	
	public function loadCycleCountSeriesDoc($series_doc_no){
		global $con, $LANG, $config, $sessioninfo, $appCore;
		
		if(!$series_doc_no)	return false;
		
		$filter = array();
		$filter[] = "cc.active=1 and cc.series_doc_no=".ms($series_doc_no);
		
		$str_filter = "where ".join(' and ', $filter);
		$cc_list = array();
		$q1 = $con->sql_query("select cc.branch_id, cc.id, cc.doc_no, cc.propose_st_date, cc.last_update
			from cycle_count cc
			$str_filter
			order by cc.propose_st_date desc");
		while($r = $con->sql_fetchassoc($q1)){
			$cc_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		return $cc_list;
	}
}
?>