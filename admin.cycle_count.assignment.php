<?php
/*
6/15/2020 2:29 PM Andy
- Added sortable to column "Propose Stock Take Date".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('STOCK_TAKE_CYCLE_COUNT') && !privilege('STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_TAKE_CYCLE_COUNT', BRANCH_CODE), "/index.php");
$maintenance->check(405);

class CYCLE_COUNT_ASSIGNMENT extends Module{
	var $branches = array();
	var $sku_group_list = array();
	var $vendor_list = array();
	var $brand_list = array();
	var $user_list = array();
	
	var $cc_list_size = 10;
	var $sample_sku_limit = 15;
	var $cc_sku_per_page = 25;
	
	function __construct($title)
	{
		// load all initial data
		$this->init_load();
		
		parent::__construct($title);
	}
	
	function _default()
	{
		global $smarty, $sessioninfo, $config;

		
		$this->display();
	}
	
	private function init_load(){
		global $appCore, $smarty;
		
		$smarty->assign('st_content_type_list', $appCore->stockTakeManager->cycleCountContentTypeList);
		$smarty->assign('cycle_count_too_many_sku_count', $appCore->stockTakeManager->cycleCountTooManySKUCount);
		$smarty->assign('sample_sku_limit', $this->sample_sku_limit);
	}
	
	function ajax_list_sel(){
		global $con, $sessioninfo, $smarty, $LANG;
        
        $t = mi($_REQUEST['t']);
		$p = mi($_REQUEST['p']);
		$size = $this->cc_list_size;
		$start = $p*$size;
		
		$filter = array();
		switch($t){
			case 1:	// saved
				$filter[] = "cc.active=1 and cc.status=0";
				break;
			case 2: // Waiting for Approval
				$filter[] = "cc.active=1 and cc.status=1 and cc.approved=0";
				break;
			case 3: // Rejected
				$filter[] = "cc.active=1 and cc.status=2 and cc.approved=0";
				break;
			case 4: // Cancelled
				$filter[] = "cc.active=0";
				break;
			case 5: // Approved
				$filter[] = "cc.active=1 and cc.status=1 and cc.approved=1 and cc.printed=0";
				break;
			case 6: // Printed
			    $filter[] = "cc.active=1 and cc.status=1 and cc.approved=1 and cc.printed=1 and cc.wip=0 and cc.completed=0";
			    break;
			case 7: // WIP
				$filter[] = "cc.active=1 and cc.status=1 and cc.approved=1 and cc.printed=1 and cc.wip>=1 and cc.completed=0";
				break;
			case 8: // Completed
			    $filter[] = "cc.active=1 and cc.status=1 and cc.approved=1 and cc.printed=1 and cc.wip>=1 and cc.completed=1 and cc.sent_to_stock_take=0";
			    break;
			case 9: // Sent to Stock Take
			    $filter[] = "cc.active=1 and cc.status=1 and cc.approved=1 and cc.printed=1 and cc.wip>=1 and cc.completed=1 and cc.sent_to_stock_take=1";
			    break;
			case 0: // search items
				$str = $_REQUEST['search_str'];
				if(!$str)	die('Cannot search empty string');
				$filter_or[] = "cc.id=".ms($str);
				$filter_or[] = "cc.doc_no=".ms($str);
				$filter[] = "(".join(' or ',$filter_or).")";
				
				// No privilege, only can see those approved
				if(!privilege('STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT')){
					$filter[] = "cc.active=1 and cc.status=1 and cc.approved=1";
				}
				break;
			default:
				die('Invalid Page');
		}
		if(BRANCH_CODE!='HQ'){
			$filter[] = "(cc.branch_id=$sessioninfo[branch_id] or cc.st_branch_id=$sessioninfo[branch_id])";
		}
		$str_filter = "where ".join(' and ',$filter);
			
		$con->sql_query("select count(*) as c
						from cycle_count cc
						$str_filter");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();

		$total_rows = mi($tmp['c']);
		if($start>=$total_rows){
			$start = 0;
			$_REQUEST['p'] = 0;
		}
		
		$limit = "limit $start, $size";
		
		if(isset($_COOKIE['_tbsort_cycle_count'])){
			$sort_order = isset($_COOKIE['_tbsort_cycle_count_order'])?$_COOKIE['_tbsort_cycle_count_order']:'desc';
			switch($_COOKIE['_tbsort_cycle_count']){
				case 'propose_st_date':
					$order = "order by cc.propose_st_date ".$sort_order;
					break;
			}
		}
		if(!$order)	$order = "order by cc.last_update desc, cc.doc_no desc";
		//$sort_column = isset($_COOKIE['_tbsort_cycle_count'])?$_COOKIE['_tbsort_cycle_count']:' cc.last_update desc, cc.doc_no desc';
		//$sort_order = isset($_COOKIE['_tbsort_masterfile_sku_order'])?$_COOKIE['_tbsort_masterfile_sku_order']:'desc';
		
		$total_page = ceil($total_rows/$size);

		$sql = "select cc.*, b.code as bcode, b2.code as st_bcode, u_pic.u as pic_username, u.u as owner_u, c.description as cat_desc, v.description as vendor_desc, br.description as brand_desc, sg.code sg_code, sg.description as sg_desc, bah.approvals, bah.approval_order_id
				from cycle_count cc
				left join branch b on b.id=cc.branch_id
				left join branch b2 on b2.id=cc.st_branch_id
				left join user u_pic on u_pic.id=cc.pic_user_id
				left join user u on u.id=cc.user_id
				left join category c on c.id=cc.category_id
				left join vendor v on v.id=cc.vendor_id
				left join brand br on br.id=cc.brand_id
				left join sku_group sg on sg.branch_id=cc.sku_group_bid and sg.sku_group_id=cc.sku_group_id
				left join branch_approval_history bah on bah.branch_id=cc.branch_id and bah.id=cc.approval_history_id
				$str_filter 
				$order 
				$limit";
		//print $sql;
		$q1 = $con->sql_query($sql);
		$cc_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$cc_list[] = $r;
		}
		$con->sql_freeresult($q1);
		//print_r($cc_list);
		$smarty->assign('cc_list', $cc_list);
		$smarty->assign('total_page',$total_page);
		$smarty->display("admin.cycle_count.assignment.list.tpl");
	}
	
	private function load_required_data($form){
		global $con, $sessioninfo, $smarty, $appCore;
		
		// Branch
		$this->branches = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign('branches', $this->branches);
		
		// SKU Group
		$params = array();
		if(!$this->can_edit)	$params['get_all'] = 1;
		$this->sku_group_list = $appCore->skuManager->getSKUGroupList($params);
		$smarty->assign('sku_group_list', $this->sku_group_list);
		
		// Vendor
		$this->vendor_list = $appCore->vendorManager->getVendorList(array('active'=>1));
		$smarty->assign('vendor_list', $this->vendor_list);
		
		// Brand
		$this->brand_list = $appCore->brandManager->getBrandList(array('active'=>1));
		$smarty->assign('brand_list', $this->brand_list);
		
		// Cycle Count Users
		if(!is_new_id($form['id'])){
			$this->user_list = array();
			if($form['audit_user_list']){
				foreach($form['audit_user_list'] as $user_id){
					if(isset($this->user_list[$user_id]))	continue;
					$this->user_list[$user_id] = $appCore->userManager->getUser($user_id);
				}
			}
			if($form['notify_user_list']){
				foreach($form['notify_user_list'] as $user_id){
					if(isset($this->user_list[$user_id]))	continue;
					$this->user_list[$user_id] = $appCore->userManager->getUser($user_id);
				}
			}
			$smarty->assign('user_list', $this->user_list);
			
			if($form['category_id']){
				// Selected Category
				$cat_info = $appCore->categoryManager->getCategoryInfo($form['category_id']);
				$str_cat_tree = htmlspecialchars(get_category_tree($form['category_id'], $cat_info['tree_str'], $have_child) . " > ".$cat_info['description']);
				
				$_REQUEST['category_id'] = $form['category_id'];
				$_REQUEST['category'] = $cat_info['description'];
				$_REQUEST['category_tree'] = $str_cat_tree;
			}else{
				// All Category
				$_REQUEST['all_category'] = 1;
			}
		}		
	}
	
	function view(){
		$this->can_edit = 0;
		$this->open();
	}
	
	private function check_can_edit($bid, $cc_id, $form = array()){
		global $LANG, $sessioninfo;
		
		// No Privilege
		if(!privilege('STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT')){
			return sprintf($LANG['NO_PRIVILEGE'], 'STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT', BRANCH_CODE);
		}
		
		// Different Branch
		if($bid != $sessioninfo['branch_id']){	// Not HQ and Own Branch
			return $LANG['CYCLE_COUNT_NOT_ALLOW_OTHER_BRANCH'];
		}
			
		if($form){
			if(!$form['active']){
				// Cancelled
				return $LANG['CYCLE_COUNT_NOT_ALLOW_TO_EDIT'];
			}else{
				if($form['status'] != 0 && $form['status'] != 2){
					// Confirmed
					return $LANG['CYCLE_COUNT_NOT_ALLOW_TO_EDIT'];
				}
			}
		}
	}
	
	function open(){
		global $sessioninfo, $appCore, $smarty, $con, $config, $LANG;
		
		$bid = mi($_REQUEST['branch_id']);
		if(!$bid)	$bid = $sessioninfo['branch_id'];
		$id = mi($_REQUEST['id']);
		if(!isset($this->can_edit))	$this->can_edit = 1;
		
		if(!$this->can_edit && !$id){
			// Required ID for View Mode
			display_redir($_SERVER['PHP_SELF'], "Cycle Count", sprintf($LANG['CYCLE_COUNT_INVALID_ID'], $id));
		}
		
		// Check Privilege
		if($this->can_edit){
			$err_msg = $this->check_can_edit($bid, $id);
			if($err_msg){
				js_redirect($err_msg, "/index.php");
			}
		}
			
		if(!$id){	// New
			$form = array();
			$form['id'] = $appCore->generateTempID();
			$form['branch_id'] = mi($sessioninfo['branch_id']);
			$form['active'] = 1;
			if(BRANCH_CODE != 'HQ'){
				$form['st_branch_id'] = mi($sessioninfo['branch_id']);
			}
		}else{	// Edit
			if(BRANCH_CODE != 'HQ'){
				if($bid != 1 && $bid != $sessioninfo['branch_id']){	// Not HQ and Own Branch
					display_redir($_SERVER['PHP_SELF'], "Cycle Count", $LANG['CYCLE_COUNT_NOT_ALLOW_OTHER_BRANCH']);
				}
			}
			// Load data
			list($form, $err_msg) = $appCore->stockTakeManager->loadCycleCount($bid, $id);
			if(!$form){	// Not Found
				display_redir($_SERVER['PHP_SELF'], "Cycle Count", $err_msg);
			}
			// Cycle Count not for this branch
			if(BRANCH_CODE != 'HQ' && $form['st_branch_id'] != $sessioninfo['branch_id']){
				display_redir($_SERVER['PHP_SELF'], "Cycle Count", $LANG['CYCLE_COUNT_NOT_ALLOW_OTHER_BRANCH']);
			}
		}
		
		// $form error
		if(!$form){
			display_redir($_SERVER['PHP_SELF'], "Cycle Count", sprintf($LANG['CYCLE_COUNT_NOT_FOUND'], $id));
		}
		
		// Check again can edit after got $form
		if($this->can_edit){
			$err_msg = $this->check_can_edit($bid, $id, $form);
			if($err_msg){
				js_redirect($err_msg, "/index.php");
			}
		}
		
		$this->load_required_data($form);
		
		//print_r($form);
		$smarty->assign('form', $form);
		$smarty->assign('can_edit', $this->can_edit);
		$this->display('admin.cycle_count.assignment.open.tpl');
	}
	
	function ajax_add_audit_notify_user(){
		global $con, $smarty, $appCore;
		
		$user_type = trim($_REQUEST['user_type']);
		if($user_type != 'audit' && $user_type != 'notify'){
			die('Invalid User Type.');
		}
		
		$user_id = mi($_REQUEST['user_id']);
		$user = $appCore->userManager->getUser($user_id);
		
		if(!$user){
			die('Invalid User ID');
		}
		
		$smarty->assign('user_type', $user_type);
		$smarty->assign('user', $user);
		$smarty->assign('can_edit', 1);
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('admin.cycle_count.assignment.open.user.tpl');
		
		print json_encode($ret);
	}
	
	function ajax_calculate_estimate_sku_count(){
		global $con, $smarty, $appCore;
		
		$form = $_REQUEST;
		//print_r($form);
		if($form['st_content_type'] != 'sku_group' && $form['st_content_type'] != 'cat_vendor_brand'){
			die('Invalid Stock Take Content Type');
		}
		
		$params = array();
		$params['count_only'] = 1;
		/*if($form['st_content_type'] == 'sku_group'){
			$params['st_content_type'] = 'sku_group';
			list($params['sku_group_bid'], $params['sku_group_id']) = explode("_", $form['tmp_sku_group_id']);
		}elseif($form['st_content_type'] == 'cat_vendor_brand'){
			$params['st_content_type'] = 'cat_vendor_brand';
			$params['category_id'] = mi($form['category_id']);
			$params['vendor_id'] = mi($form['vendor_id']);
			$params['brand_id'] = mi($form['brand_id']);
		}*/
		
		// Get SKU Count
		$result = $appCore->stockTakeManager->getCycleCountEstimateSKUListing($form, $params);
		if($result['error']){
			die($result['error']);
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['estimate_sku_count'] = mi($result['sku_count']);
		print json_encode($ret);
	}
	
	function ajax_save_cycle_count(){
		global $con, $smarty, $appCore, $sessioninfo, $LANG;
		
		$form = $_REQUEST;
		//print_r($form);
		
		if($form['branch_id'] != $sessioninfo['branch_id']){
			die($LANG['CYCLE_COUNT_NOT_ALLOW_OTHER_BRANCH']);
		}
		
		$is_confirm = mi($form['is_confirm']);
		
		$upd = array();
		$upd['st_branch_id'] = mi($form['st_branch_id']);
		$upd['st_content_type'] = trim($form['st_content_type']);
		$upd['category_id'] = 0;
		$upd['vendor_id'] = 0;
		$upd['brand_id'] = 0;
		$upd['sku_group_bid'] = 0;
		$upd['sku_group_id'] = 0;
		
		if($upd['st_content_type'] == 'sku_group'){
			//list($upd['sku_group_bid'], $upd['sku_group_id']) = explode("_", $form['tmp_sku_group_id']);
			$upd['sku_group_bid'] = mi($form['sku_group_bid']);
			$upd['sku_group_id'] = mi($form['sku_group_id']);
		}elseif($upd['st_content_type'] == 'cat_vendor_brand'){
			$upd['category_id'] = mi($form['category_id']);
			$upd['vendor_id'] = mi($form['vendor_id']);
			$upd['brand_id'] = mi($form['brand_id']);
		}
		$upd['st_date'] = $upd['propose_st_date'] = $form['propose_st_date'];
		$upd['pic_user_id'] = mi($form['pic_user_id']);
		$upd['audit_user_list'] = serialize($form['audit_user_list']);
		$upd['notify_user_list'] = serialize($form['notify_user_list']);
		$upd['notify_day'] = mi($form['notify_day']);
		$upd['remark'] = trim($form['remark']);
		$upd['estimate_sku_count'] = mi($form['estimate_sku_count']);
		$upd['active'] = 1;
		$upd['status'] = 0;
		$upd['approved'] = 0;
		$upd['printed'] = 0;
		$upd['wip'] = 0;
		$upd['completed'] = 0;
		$upd['sent_to_stock_take'] = 0;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_begin_transaction();
		
		//print_r($form);exit;
		if($is_confirm){
			$form['status'] = $upd['status'] = 1;
			
            $params = array();
		    $params['type'] = 'CYCLE_COUNT';
		    $params['reftable'] = 'cycle_count';
		    $params['user_id'] = $sessioninfo['id'];
		    $params['branch_id'] = $sessioninfo['branch_id'];
		    
			if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id']; // use back the same id if already have
			$astat = check_and_create_approval2($params, $con);

	  	  	if(!$astat) die($LANG['CYCLE_COUNT_APPROVAL_FLOW']);
	  		else{
	  			 $upd['approval_history_id'] = $form['approval_history_id'] = $astat[0];
	     		 if ($astat[1] == '|'){
	     		 	$last_approval = true;
					$upd['approved'] = 1;
	     		 }
	  		}
		}
		
		if(is_new_id($form['id'])){
			// New
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$upd['user_id'] = mi($sessioninfo['id']);
			$bid = $upd['branch_id'] = mi($sessioninfo['branch_id']);
			
			// Get Max ID
			$con->sql_query("select max(id) as max_id from cycle_count where branch_id=".mi($upd['branch_id'])." FOR UPDATE");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$cc_id = mi($tmp['max_id'])+1;
			
			$upd['id'] = $cc_id;
			$form['doc_no'] = $upd['doc_no'] = $sessioninfo['report_prefix'].sprintf("%05d", $cc_id);
			
			// Insert
			$con->sql_query("insert into cycle_count ".mysql_insert_by_field($upd));
	        
		}else{
			// Update
			$cc_id = mi($form['id']);
			$bid = mi($form['branch_id']);
			
			// Load Cycle Count
			list($cc, $err_msg) = $appCore->stockTakeManager->loadCycleCount($bid, $cc_id);
		
			// Check can edit or not
			if(!$err_msg){
				$err_msg = $this->check_can_edit($bid, $cc_id, $cc);
			}
			if($err_msg){
				die($err_msg);
			}
			
			$con->sql_query("update cycle_count set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$cc_id");
		}
		
		if ($is_confirm){
			$con->sql_query("update branch_approval_history set ref_id=$cc_id where id=".mi($form['approval_history_id'])." and branch_id = $bid");
			log_br($sessioninfo['id'], 'CYCLE COUNT', $cc_id, "Confirmed: (ID#$cc_id, Doc No: ".$form['doc_no'].")");
			
		    if ($last_approval){
				$appCore->stockTakeManager->cycleCountApproval($bid, $cc_id, 'approve');
                $t = 'approved';
			}
			else{    
                $t = 'confirmed';
				$to = get_pm_recipient_list2($cc_id, $form['approval_history_id'], 0, 'confirmation', $bid, 'cycle_count');
				send_pm2($to, "Cycle Count Approval (ID#$cc_id)", "admin.cycle_count.assignment.php?a=view&id=$cc_id&branch_id=".$bid, array('module_name'=>'cycle_count'));
			}
				
		}
		else{
			if($form['approval_history_id']){
				$con->sql_query("update branch_approval_history set approvals='|' where id=".mi($form['approval_history_id'])." and branch_id = $bid");
			}
	        log_br($sessioninfo['id'], 'CYCLE COUNT', $cc_id, "Saved: (ID#$cc_id, Doc No: ".$form['doc_no'].")");
	        $t = 'saved';
		}
		
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['id'] = $cc_id;
		$ret['t'] = $t;
		
		print json_encode($ret);
	}
	
	function ajax_cancel_cycle_count(){
		global $con, $sessioninfo, $appCore, $config;
		
		$bid = mi($_REQUEST['branch_id']);
		$cc_id = mi($_REQUEST['id']);
		$is_reset = mi($_REQUEST['is_reset']);
		$reason = trim($_REQUEST['cancel_reason']);
		
		//print_r($_REQUEST);exit;
		// Load Cycle Count
		list($form, $err_msg) = $appCore->stockTakeManager->loadCycleCount($bid, $cc_id);
		if($err_msg){
			die($err_msg);
		}
		
		if($is_reset){
			// Reset to Save
			$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;

			if($sessioninfo['level']<$required_level){
				die($LANG['USER_LEVEL_NO_REACH']);
			}
			
			// Reset
			$params = array();
			$params['reason'] = $reason;
			$result = $appCore->stockTakeManager->resetCycleCountToSave($bid, $cc_id, $sessioninfo['id'], $params);
			if(!$result['ok'])	die($result['error']);
		}else{
			if(!$err_msg){
				// Check can edit or not
				$err_msg = $this->check_can_edit($bid, $cc_id, $form);
			}
		
			$upd = array();
			$upd['active'] = 0;
			$upd['cancel_reason'] = $reason;
			$upd['cancelled_by'] = $sessioninfo['id'];
			$upd['last_update'] = 'CURRENT_TIMESTAMP';

			$con->sql_query("update cycle_count set ".mysql_update_by_field($upd)." where id=$cc_id and branch_id=$bid");

			log_br($sessioninfo['id'], 'CYCLE COUNT', $cc_id, "Cancelled: (ID#$cc_id, Doc No: ".$form['doc_no'].")");
		}	    
		
	    $ret = array();
		$ret['ok'] = 1;
		$ret['id'] = $cc_id;
		
		print json_encode($ret);
	}
	
	function ajax_show_sample_sku(){
		global $con, $sessioninfo, $appCore, $smarty;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$params = array();
		$params['limit'] = $this->sample_sku_limit;
		
		// Get SKU Count
		$result = $appCore->stockTakeManager->getCycleCountEstimateSKUListing($form, $params);
		if($result['error']){
			die($result['error']);
		}
		
		$smarty->assign('item_list', $result['item_list']);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('admin.cycle_count.assignment.open.sample_sku_list.tpl');
		
		print json_encode($ret);
	}
	
	function ajax_change_pic(){
		global $con, $sessioninfo, $LANG;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$cc_id = mi($form['id']);
		$bid = mi($form['branch_id']);
		$new_owner = trim($form['new_owner']);
		
		$q1 = $con->sql_query("select id 
			from user
			where user.u=".ms($new_owner));
		$user = $con->sql_fetchassoc($q1);
		$con->sql_freeresult();
		
		if(!$user){
			die("User Not Found.");
		}
		
		$user_id = mi($user['id']);
		$con->sql_query("update cycle_count set pic_user_id=$user_id where id=$cc_id and branch_id=$bid");
		
		log_br($sessioninfo['id'], 'CYCLE COUNT', $cc_id, "PIC Changed To: $new_owner");
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	private function create_cycle_count_sku_listing($bid, $cc_id, $form = array()){
		global $con, $sessioninfo, $appCore, $LANG;
		
		if(!$form){
			// Load Cycle Count
			list($form, $err_msg) = $appCore->stockTakeManager->loadCycleCount($bid, $cc_id);
		}
		
		if($err_msg){
			// Got Error
			return $err_msg;
		}
		
		$params = array();
		$params['into_tmp_table'] = 1;
		
		// Get SKU Count
		$result = $appCore->stockTakeManager->getCycleCountEstimateSKUListing($form, $params);
		if($result['error']){
			return $result['error'];
		}
		$tmp_tablename = $result['tmp_tablename'];
		
		$con->sql_query("select count(*) as c from $tmp_tablename");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$total_row = mi($tmp['c']);
		$total_page = ceil($total_row / $this->cc_sku_per_page);
		
		// No Item
		if($total_row <= 0){
			return $LANG['CYCLE_COUNT_NO_ITEM'];
		}
		
		$con->sql_begin_transaction();
		
		// Delete All Items
		$con->sql_query("delete from cycle_count_items where branch_id=$bid and cc_id=$cc_id");
		
		$page_num = 0;		
		$item_id = 0;
		for($i=0; $i < $total_page; $i++){
			$s = $i*$this->cc_sku_per_page;
			$limit = $this->cc_sku_per_page;
			$page_num++;
			$row_num = 0;
			
			$q1 = $con->sql_query("select tbl.sku_item_id as sid 
			from $tmp_tablename tbl
			order by tbl.id
			limit $s, $limit");
			while($r = $con->sql_fetchassoc($q1)){
				$item_id++;
				$row_num++;
				
				$upd = array();
				$upd['item_guid'] = $appCore->newGUID();
				$upd['branch_id'] = $bid;
				$upd['cc_id'] = $cc_id;
				$upd['item_id'] = $item_id;
				$upd['page_num'] = $page_num;
				$upd['row_num'] = $row_num;
				$upd['sku_item_id'] = $r['sid'];
				$upd['last_update'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("insert into cycle_count_items ".mysql_insert_by_field($upd));
			}
			$con->sql_freeresult($q1);
		}
		
		$upd2 = array();
		$upd2['printed'] = 1;
		$upd2['estimate_sku_count'] = $total_row;
		$upd2['last_update'] = $upd2['print_time'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update cycle_count set ".mysql_update_by_field($upd2)." where branch_id=$bid and id=$cc_id");
		
		$con->sql_commit();
		
		//print_r($result);
	}
	
	function print_cycle_count(){
		global $con, $sessioninfo, $appCore, $LANG, $smarty;
		
		$form = $_REQUEST;
		//print_r($form);
		$bid = mi($form['branch_id']);
		$cc_id = mi($form['id']);
		
		// Load Cycle Count
		list($cc, $err_msg) = $appCore->stockTakeManager->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			display_redir($_SERVER['PHP_SELF'], "Cycle Count", $err_msg);
		}
		
		// Not Stock Take Person
		if($cc['pic_user_id'] != $sessioninfo['id']){
			display_redir($_SERVER['PHP_SELF'], "Cycle Count", $LANG['CYCLE_COUNT_ONLY_PIC_CAN_PRINT']);
		}
		
		// Not Stock Take Branch
		if($cc['st_branch_id'] != $sessioninfo['branch_id']){
			display_redir($_SERVER['PHP_SELF'], "Cycle Count", $LANG['CYCLE_COUNT_WRONG_STOCK_TAKE_BRANCH']);
		}
		
		if(!$cc['printed'] || ($_REQUEST['regen'] && !$cc['wip'])){
			// First time Print - Create SKU List
			$err_msg = $this->create_cycle_count_sku_listing($bid, $cc_id, $cc);
			if($err_msg){
				display_redir($_SERVER['PHP_SELF'], "Cycle Count", $err_msg);
			}
			// Reload again after print sku
			list($cc, $err_msg) = $appCore->stockTakeManager->loadCycleCount($bid, $cc_id);
		}
		
		$this->load_required_data($cc);
		
		// Total Page
		$totalpage = mi($cc['item_totalpage']);
		
		$smarty->assign("st_branch", $this->branches[$cc['st_branch_id']]);
		$smarty->assign("form", $cc);
		$smarty->assign("totalpage", $totalpage);
		for($page_num = 1; $page_num <= $totalpage; $page_num++){
			// Get Item in this Page
			$item_list = $appCore->stockTakeManager->loadCycleCountItems($bid, $cc_id, array('page_num'=>$page_num));
			
			$smarty->assign("page_num", $page_num);
			$smarty->assign("items", $item_list);
			$smarty->assign("is_lastpage", ($page_num >= $totalpage));
			$smarty->display("admin.cycle_count.assignment.print.tpl");
			$smarty->assign("skip_header",1);
		}
	}
	
	function show_cycle_count_sheet(){
		global $con, $sessioninfo, $appCore, $LANG, $smarty;
		
		$bid = mi($_REQUEST['branch_id']);
		$cc_id = mi($_REQUEST['id']);
		
		// Load Cycle Count
		list($form, $err_msg) = $appCore->stockTakeManager->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			display_redir($_SERVER['PHP_SELF'], "Cycle Count", $err_msg);
		}
		
		$this->can_edit = 0;
		if(!$form['completed']){
			$this->can_edit = $form['pic_user_id'] == $sessioninfo['id'] ? 1 : 0;
		}
		
		
		// Get Item in 1st Page
		$item_list = $appCore->stockTakeManager->loadCycleCountItems($bid, $cc_id, array('page_num'=>1));
		if($item_list)	$item_list = $this->extend_cycle_count_items($bid, $cc_id, $form, $item_list);
		//print_r($item_list);
		$smarty->assign("can_edit", $this->can_edit);
		$smarty->assign("item_list", $item_list);
		$smarty->assign("form", $form);
		$smarty->display("admin.cycle_count.assignment.sheet.tpl");
	}
	
	function extend_cycle_count_items($bid, $cc_id, $form, $item_list){
		global $con, $sessioninfo, $appCore, $LANG, $smarty;
		
		if($item_list){
			$date = date("Y-m-d", strtotime("-1 day", strtotime($form['st_date'])));
			// Loop Item
			foreach($item_list as $item_id => $r){
				// Load Latest Stock
				$sb = get_sku_items_stock_balance($r['sku_item_id'], $form['st_branch_id'], $date);
				$item_list[$item_id]['stock_balance'] = mf($sb['qty']);
				
				// Calculate Variance
				$item_list[$item_id]['st_variance'] = $r['calculated_st_qty'] - mf($sb['qty']);
			}
		}
		
		return $item_list;
	}
	
	function ajax_change_sheet_page(){
		global $con, $sessioninfo, $appCore, $LANG, $smarty;
		
		$bid = mi($_REQUEST['branch_id']);
		$cc_id = mi($_REQUEST['id']);
		$page_num = mi($_REQUEST['sel_page']);
		
		// Load Cycle Count
		list($form, $err_msg) = $appCore->stockTakeManager->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			display_redir($_SERVER['PHP_SELF'], "Cycle Count", $err_msg);
		}
		
		$this->can_edit = 0;
		if(!$form['completed']){
			$this->can_edit = $form['pic_user_id'] == $sessioninfo['id'] ? 1 : 0;
		}
		
		$item_list = $appCore->stockTakeManager->loadCycleCountItems($bid, $cc_id, array('page_num'=>$page_num));
		if($item_list)	$item_list = $this->extend_cycle_count_items($bid, $cc_id, $form, $item_list);
		
		$smarty->assign("can_edit", $this->can_edit);
		$smarty->assign("item_list", $item_list);
		$smarty->assign("form", $form);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('admin.cycle_count.assignment.sheet.item_list.tpl');
		
		print json_encode($ret);
	}
	
	function ajax_mark_wip(){
		global $con, $sessioninfo, $appCore, $LANG, $smarty;
		
		$bid = mi($_REQUEST['branch_id']);
		$cc_id = mi($_REQUEST['id']);
		
		// Start Cycle Count		
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		$result = $appCore->stockTakeManager->startCycleCount($bid, $cc_id, $params);
		if(!$result['ok']){
			die($result['error']);
		}
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_wip_save(){
		global $con, $sessioninfo, $appCore, $LANG, $smarty;
		
		//print_r($_REQUEST);exit;
		
		$bid = mi($_REQUEST['branch_id']);
		$cc_id = mi($_REQUEST['id']);
		$is_confirm = mi($_REQUEST['is_confirm']);
		
		// Load Cycle Count
		list($form, $err_msg) = $appCore->stockTakeManager->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			die($err_msg);
		}
		
		if($form['wip'] != 1 || $form['completed'] != 0){
			die($LANG['CYCLE_COUNT_CANNOT_SAVE_ST']);
		}
		
		$con->sql_begin_transaction();
		
		// Got change backend_qty
		$got_update = false;
		if($_REQUEST['tmp_backend_qty']){
			// Loop Item
			foreach($_REQUEST['tmp_backend_qty'] as $item_id => $backend_qty){
				$item_id = mi($item_id);
				$con->sql_query("select * from cycle_count_items where branch_id=$bid and cc_id=$cc_id and item_id=$item_id");
				$item = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($item['backend_qty'] !== $backend_qty){
					$upd = array();
					$upd['backend_qty'] = $backend_qty;
					if($item['st_time'] == '0000-00-00 00:00:00')	$upd['st_time'] = 'CURRENT_TIMESTAMP';
					
					$con->sql_query("update cycle_count_items set ".mysql_update_by_field($upd, false, 1)." where branch_id=$bid and cc_id=$cc_id and item_id=$item_id");
					if($con->sql_affectedrows()){
						$got_update = true;
					}
				}
			}			
		}
		
		if($got_update || $is_confirm){
			$upd2 = array();
			$upd2['last_update'] = 'CURRENT_TIMESTAMP';
			
			if($is_confirm){
				$upd2['completed'] = 1;
				$upd2['complete_time'] = 'CURRENT_TIMESTAMP';
			}
			
			$con->sql_query("update cycle_count set ".mysql_update_by_field($upd2)." where branch_id=$bid and id=$cc_id");
		}
		
		if($is_confirm){
			// Process to generate completed data
			$appCore->stockTakeManager->processCycleCountComplete($bid, $cc_id);
		}
		
		log_br($sessioninfo['id'], 'CYCLE COUNT', 0, ($is_confirm ? 'Confirm':'Save')." Cycle Count Stock Take Data (ID#$cc_id, Doc No: ".$form['doc_no'].")");
		
		$con->sql_commit();

		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_find_sku(){
		global $con, $sessioninfo, $appCore, $LANG, $smarty;
		
		//print_r($_REQUEST);exit;
		$bid = mi($_REQUEST['branch_id']);
		$cc_id = mi($_REQUEST['id']);
		$find_by_grn_barcode = mi($_REQUEST['find_by_grn_barcode']);
		$grn_barcode = trim($_REQUEST['grn_barcode']);
		//$grn_barcode_type = trim($_REQUEST['grn_barcode_type']);
		
		
		if($find_by_grn_barcode){
			$sku_info = get_grn_barcode_info($grn_barcode);
			//print_r($sku_info);exit;
			$sku_item_id = mi($sku_info['sku_item_id']);
			
			if($sku_info['err']){
				die($sku_info['err']);
			}
		}else{
			$sku_item_id = mi($_REQUEST['sku_item_id']);
		}
		
		if(!$sku_item_id)	die("No item to search.");
		
		$con->sql_query("select * from cycle_count_items where branch_id=$bid and cc_id=$cc_id and sku_item_id=$sku_item_id");
		$item = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$item)	die("Item Not Found.");
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['page_num'] = $item['page_num'];
		$ret['sku_item_id'] = $item['sku_item_id'];
		print json_encode($ret);
	}
	
	function download_sku_csv(){
		global $con, $sessioninfo, $appCore, $LANG, $smarty;
		
		$bid = mi($_REQUEST['branch_id']);
		$cc_id = mi($_REQUEST['id']);
		
		// Load Cycle Count
		list($form, $err_msg) = $appCore->stockTakeManager->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			display_redir($_SERVER['PHP_SELF'], "Cycle Count", $err_msg);
		}
		
		// Total Page
		$totalpage = mi($form['item_totalpage']);
		$file_prefix = "CYCLE_COUNT_SKU_LIST_".$sessioninfo['id'].'_'.time();
		$zip_filename = $file_prefix.".zip";
		
		$smarty->assign("form", $form);
		for($page_num = 1; $page_num <= $totalpage; $page_num++){
			// Get Item in this Page
			$item_list = $appCore->stockTakeManager->loadCycleCountItems($bid, $cc_id, array('page_num'=>$page_num));
			//print_r($item_list);continue;
			if(!$item_list)	return;
			
			$filename = $file_prefix."_".$page_num.".csv";
			$tmp_file_path = '/tmp/'.$filename;
			
			$f = fopen($tmp_file_path, 'w');
			
			// Header
			$row = array("No.", "ARMS Code", "MCode", "Art No", $config['link_code_name'], "Description");
			fputcsv_eol($f, $row);
			
			// Loop to put items
			foreach($item_list as $r){
				$row = array($r['item_id'], $r['sku_item_code'], $r['mcode'], $r['artno'], $r['link_code'], $r['description']);
				fputcsv_eol($f, $row);
			}
			
			fclose($f);
		}
		
		// Make Zip file
		exec("cd /tmp; zip -9 $zip_filename $file_prefix*.csv");
		
		log_br($sessioninfo['id'], 'CYCLE COUNT', 0, "Download Cycle Count SKU (ID#$cc_id, Doc No: ".$form['doc_no'].")");
		
		header("Content-type: application/zip");
		header("Content-Disposition: attachment; filename=$zip_filename");
		readfile("/tmp/$zip_filename");
		exit;
	}
	
	function ajax_reopen_wip(){
		global $con, $sessioninfo, $appCore, $LANG, $smarty, $config;
		
		$bid = mi($_REQUEST['branch_id']);
		$cc_id = mi($_REQUEST['id']);
		
		// Reset to Save
		$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;

		if($sessioninfo['level']<$required_level){
			die($LANG['USER_LEVEL_NO_REACH']);
		}
		
		$result = $appCore->stockTakeManager->reopenCycleCountWIP($bid, $cc_id, $sessioninfo['id']);
		if(!$result['ok'])	die($result['error']);
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_regen_pos_qty(){
		global $con, $sessioninfo, $appCore, $LANG, $smarty, $config;
		
		$bid = mi($_REQUEST['branch_id']);
		$cc_id = mi($_REQUEST['id']);
		
		$con->sql_begin_transaction();
		$result = $appCore->stockTakeManager->generateCycleCountPOSQty($bid, $cc_id);
		if(!$result['ok'])	die($result['error']);
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_send_to_stock_take(){
		global $con, $sessioninfo, $appCore, $LANG, $smarty, $config;
		
		$bid = mi($_REQUEST['branch_id']);
		$cc_id = mi($_REQUEST['id']);
		$zerolise_non_st = mi($_REQUEST['zerolise_non_st']);
		
		$con->sql_begin_transaction();
		$params = array();
		if($zerolise_non_st)	$params['zerolise_non_st'] = 1;
		$params['user_id'] = $sessioninfo['id'];
		$result = $appCore->stockTakeManager->sendCycleCountToStoreStockTake($bid, $cc_id, $params);
		if(!$result['ok'])	die($result['error']);
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_recall_stock_take(){
		global $con, $sessioninfo, $appCore, $LANG, $smarty, $config;
		
		$bid = mi($_REQUEST['branch_id']);
		$cc_id = mi($_REQUEST['id']);
				
		$con->sql_begin_transaction();
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		$result = $appCore->stockTakeManager->recallCycleCountFromStoreStockTake($bid, $cc_id, $params);
		if(!$result['ok'])	die($result['error']);
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_show_clone_cycle_count(){
		global $con, $sessioninfo, $appCore, $LANG, $smarty, $config;
		
		$bid = mi($_REQUEST['branch_id']);
		$cc_id = mi($_REQUEST['id']);
		
		// Load Cycle Count
		list($form, $err_msg) = $appCore->stockTakeManager->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			display_redir($_SERVER['PHP_SELF'], "Cycle Count", $err_msg);
		}
		
		// Load related doc
		if($form['series_doc_no']){
			$form['series_cc_list'] = $appCore->stockTakeManager->loadCycleCountSeriesDoc($form['series_doc_no']);
			if($form['series_cc_list']){
				$max_series_date = $form['series_cc_list'][0]['propose_st_date'];
				$smarty->assign('max_series_date', $max_series_date);
			}
			
		}
		
		$smarty->assign('form', $form);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('admin.cycle_count.assignment.clone_main.tpl');
		print json_encode($ret);
	}
	
	function ajax_clone_cycle_count(){
		global $con, $sessioninfo, $appCore, $LANG, $smarty, $config;
		
		//print_r($_REQUEST);
		
		$bid = mi($_REQUEST['branch_id']);
		$cc_id = mi($_REQUEST['id']);
		$clone_type = trim($_REQUEST['clone_type']);
		
		if(BRANCH_CODE != 'HQ' && $bid != $sessioninfo['branch_id']){
			die($LANG['CYCLE_COUNT_NOT_ALLOW_OTHER_BRANCH']);
		}
		
		/*list($form, $err_msg) = $appCore->stockTakeManager->loadCycleCount($bid, $cc_id);
		if($err_msg){
			// Got Error
			die($err_msg);
		}*/
		
		if($clone_type != 'normal' && $clone_type != 'advanced'){
			// Invalid Clone Type
			die($LANG['CYCLE_COUNT_INVALID_CLONE_TYPE']);
		}
		
		$params = array();
		if($clone_type == 'advanced'){
			if($_REQUEST['propose_st_date_list']){
				foreach($_REQUEST['propose_st_date_list'] as $tmp_date){
					// Check to make sure is valid date format
					if($tmp_date && $appCore->isValidDateFormat($tmp_date)){
						$params['propose_st_date_list'][] = $tmp_date;
					}
				}
			}
			
			if(!$params['propose_st_date_list']){
				die($LANG['CYCLE_COUNT_INVALID_CLONE_DATE']);
			}
		}
		
		$con->sql_begin_transaction();
		
		// Start Clone
		$result = $appCore->stockTakeManager->cloneCycleCount($bid, $cc_id, $clone_type, $params);
		if(!$result['ok']){
			die($result['error']);
		}
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['id_list'] = $result['id_list'];
		print json_encode($ret);
	}
}

$CYCLE_COUNT_ASSIGNMENT = new CYCLE_COUNT_ASSIGNMENT('Cycle Count Assignment');
?>