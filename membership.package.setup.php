<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP_PACK_SETUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_PACK_SETUP', BRANCH_CODE), "/index.php");
$maintenance->check(410);

class MEMBERSHIP_PACKAGE_SETUP extends Module{
	var $branches_list = array();
	var $package_list_size = 20;
	
	function __construct($title, $template='')
	{
		global $smarty;
				
		$this->init_load();
		
		parent::__construct($title, $template='');
	}
	
	function _default(){
		global $con, $smarty, $appCore;
		
		$this->display();
	}
	
	private function init_load(){
		global $con, $smarty, $appCore;
		
		// Load Branch
		$this->branches_list = $appCore->branchManager->getBranchesList(array('active'=>1));
		
		$smarty->assign('branches_list', $this->branches_list);
	}
	
	private function check_can_edit($package_unique_id, $form = array()){
		global $LANG, $sessioninfo, $appCore;
		
		// Manually Get Package if no provide
		if(!$form){
			$result = $appCore->memberManager->getMembershipPackageByUniqueID($package_unique_id);
			$form = $result['data'];
			if(!$form)	return $result['error'];
		}
		
		if(!$form)	return sprintf($LANG['MEMBERSHIP_PACKAGE_NOT_FOUND'], $package_unique_id);
		
		// Different Branch
		if($form['branch_id'] != $sessioninfo['branch_id']){	// Not HQ and Own Branch
			return $LANG['MEMBERSHIP_PACKAGE_NOT_ALLOW_OTHER_BRANCH'];
		}
			
		if(!($form['active']==1 && $form['status']==0)){
			return $LANG['MEMBERSHIP_PACKAGE_NOT_ALLOW_EDIT'];
		}
	}
	
	function view(){
		$this->open();
	}
	
	function open(){
		global $con, $smarty, $appCore, $sessioninfo, $LANG;
		
		$package_unique_id = mi($_REQUEST['package_unique_id']);
		
		$can_edit = 0;
		if($_REQUEST['a'] == 'open')	$can_edit = 1;
		
		if($package_unique_id > 0){
			// Edit
			$result = $appCore->memberManager->getMembershipPackageByUniqueID($package_unique_id);
			if(!$result['data'] || $result['error']){
				display_redir($_SERVER['PHP_SELF'], $this->title, $result['error']);
			}
			$form = $result['data'];
			
			// Check Can Edit
			if($can_edit){
				$err_msg = $this->check_can_edit($package_unique_id, $form);
				if($err_msg){
					display_redir($_SERVER['PHP_SELF'], $this->title, $err_msg);
				}
			}
			
			// Load Linked SKU Info
			if($form['link_sku_item_id']){
				$form['linked_sku_info'] = $this->load_linked_sku_info($form['branch_id'], $form['link_sku_item_id']);
			}
			
			$result = $appCore->memberManager->getMembershipPackageItemsByUniqueID($package_unique_id);
			if($result['data']){
				$form['package_items'] = $result['data'];
			}
		}else{
			// New
			$form = array();
			$form['branch_id'] = $sessioninfo['branch_id'];
			$form['active'] = 1;
			$form['valid_from'] = date("Y-m-d");
			$form['valid_to'] = date("Y-m-d", strtotime("+1 year"));
		}
		
		
		
		$smarty->assign('can_edit', $can_edit);
		$smarty->assign('form', $form);
		$smarty->display("membership.package.setup.open.tpl");
	}
	
	function ajax_add_item(){
		global $con, $smarty, $appCore, $sessioninfo;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$item_guid = $appCore->newGUID();
		$item = array();
		$item['guid'] = $item_guid;
		$item['active'] = 1;
		
		$smarty->assign('item_guid', $item_guid);
		$smarty->assign('item', $item);
		$smarty->assign('can_edit', 1);
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('membership.package.setup.open.item.tpl');
		
		print json_encode($ret);
	}
	
	private function load_linked_sku_info($branch_id, $sku_item_id){
		global $con, $smarty, $appCore, $sessioninfo;
		
		$branch_id = mi($branch_id);
		$sku_item_id = mi($sku_item_id);
		if(!$branch_id)	die("Invalid Branch ID");
		if(!$sku_item_id)	die("Invalid SKU ITEM ID");
		
		// Load sku_items
		$si = $appCore->skuManager->getSKUItemsInfo($sku_item_id);
		if(!$si)	die("Item Not Found");
		$si['selling_by_branch'] = array();
		
		if($branch_id == 1){
			// HQ can load all branch selling
			foreach($this->branches_list as $bid => $b){
				// Load branch selling
				$si['selling_by_branch'][$bid] = round($appCore->skuManager->getSKUItemPrice($bid, $sku_item_id), 2);
			}
		}else{
			// Sub branch load only own selling
			$si['selling_by_branch'][$branch_id] = round($appCore->skuManager->getSKUItemPrice($branch_id, $sku_item_id), 2);
		}
		
		return $si;
	}
	
	function ajax_load_sku_info(){
		global $con, $smarty, $appCore, $sessioninfo;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$branch_id = mi($form['branch_id']);
		$link_sku_item_id = mi($form['link_sku_item_id']);
		
		if(!$branch_id)	die("Invalid Branch ID");
		if(!$link_sku_item_id)	die("Invalid SKU ITEM ID");
		
		// Load SKU Info
		$linked_sku_info = $this->load_linked_sku_info($branch_id, $link_sku_item_id);
		//print_r($linked_sku_info);
		
		$smarty->assign('form', $form);
		$smarty->assign('linked_sku_info', $linked_sku_info);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('membership.package.setup.open.linked_sku_info.tpl');
		
		print json_encode($ret);
	}
	
	private function validate_data($package_unique_id, $form, $is_confirm){
		global $LANG, $appCore;
		
		// Title is empty
		if(!$form['title'])	return sprintf($LANG['MEMBERSHIP_PACKAGE_INVALID_DATA'], 'Title');
		
		// Valid From
		if(!$appCore->isValidDateFormat($form['valid_from']))	return sprintf($LANG['MEMBERSHIP_PACKAGE_INVALID_DATA'], 'Valid From');
		
		// Valid To
		if(!$appCore->isValidDateFormat($form['valid_to']))	return sprintf($LANG['MEMBERSHIP_PACKAGE_INVALID_DATA'], 'Valid To');
		
		// Total Entry Earn
		if($form['total_entry_earn']<=0)	return sprintf($LANG['MEMBERSHIP_PACKAGE_INVALID_DATA'], 'Total Entry Earn');
		
		// Allowed Branches
		if(!$form['allowed_branches'] || !is_array($form['allowed_branches']))	return sprintf($LANG['MEMBERSHIP_PACKAGE_INVALID_DATA'], 'Allowed Branches');
		
		// Linked SKU
		if($form['link_sku_item_id']<=0)	return sprintf($LANG['MEMBERSHIP_PACKAGE_INVALID_DATA'], 'Linked SKU');
		
		// Check If this sku already linked to other package
		$si_info = $appCore->skuManager->getSKUItemsInfo($form['link_sku_item_id']);
		if(!$si_info)	return sprintf($LANG['MEMBERSHIP_PACKAGE_INVALID_DATA'], 'Linked SKU');
		if($si_info['membership_package_unique_id'] && $si_info['membership_package_unique_id'] != $package_unique_id){
			return $LANG['MEMBERSHIP_PACKAGE_LINKED_SKU_USED'];
		}
	}
	
	function ajax_save(){
		global $con, $smarty, $appCore, $sessioninfo;
		
		$form = $_REQUEST;
		//print_r($form);exit;
		$bid = mi($form['branch_id']);
		if($sessioninfo['branch_id'] != $bid){
			die("You have login to the wrong branch.");
		}
			
		$package_id = mi($form['id']);
		$package_unique_id = mi($form['unique_id']);
		$doc_no = trim($form['doc_no']);
		$is_confirm = mi($form['is_confirm']);
		
		// Begin Transaction
		$con->sql_begin_transaction();
		$is_new = false;
		
		$upd = array();
		$upd['title'] = trim($form['title']);
		$upd['valid_from'] = trim($form['valid_from']);
		$upd['valid_to'] = trim($form['valid_to']);
		$upd['remark'] = trim($form['remark']);
		$upd['allowed_branches'] = $form['allowed_branches'];
		$upd['link_sku_item_id'] = $form['link_sku_item_id'];
		$upd['total_entry_earn'] = $form['total_entry_earn'];
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		if($is_confirm){
			// Confirm
			$upd['status'] = 1;
			$upd['confirm_timestamp'] = 'CURRENT_TIMESTAMP';
		}
		
		// Check Data
		$err_msg = $this->validate_data($package_unique_id, $upd, $is_confirm);
		if($err_msg){
			die($err_msg);
		}
		
		if($package_id > 0){
			// Check Can Edit			
			$err_msg = $this->check_can_edit($package_unique_id);
			if($err_msg){
				die($err_msg);
			}
			
			// Update
			$upd['allowed_branches'] = serialize($upd['allowed_branches']);
			$con->sql_query("update membership_package set ".mysql_update_by_field($upd)." where unique_id=$package_unique_id");
			
			$str_log = "Updated Membership Package (".$doc_no.")";
		}else{
			$is_new = true;
			$branch_info = $appCore->branchManager->getBranchInfo($bid);

			// Get the max id
			$con->sql_query("select max(id) as max_id from membership_package where branch_id=$bid for update");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$package_id = mi($tmp['max_id'])+1;
			$package_unique_id = ($package_id*10000)+$bid;
			
			// New
			
			$upd['branch_id'] = $bid;
			$upd['id'] = $package_id;
			$upd['unique_id'] = $package_unique_id;
			$doc_no = $upd['doc_no'] = $branch_info['report_prefix'].sprintf("%05d", $package_id);
			$upd['user_id'] = $sessioninfo['id'];
			$upd['added'] = 'CURRENT_TIMESTAMP';
			//print_r($upd);
			
			$upd['allowed_branches'] = serialize($upd['allowed_branches']);
			$con->sql_query("insert into membership_package ".mysql_insert_by_field($upd));
			
			$str_log = "Added New Membership Package (".$doc_no.")";
		}
		
		$str_log .= ", Status: ".($is_confirm ? 'Confirmed':'Draft');
		
		if($form['package_items']){
			// Delete old items
			$con->sql_query("delete from membership_package_items where package_unique_id=$package_unique_id");
			
			$sequence = 0;
			foreach($form['package_items'] as $item_guid => $r){
				$sequence++;
				
				$upd2 = array();
				$upd2['guid'] = $item_guid;
				$upd2['branch_id'] = $bid;
				$upd2['package_id'] = $package_id;
				$upd2['package_unique_id'] = $package_unique_id;
				$upd2['title'] = trim($r['title']);
				$upd2['description'] = trim($r['description']);
				$upd2['remark'] = trim($r['remark']);
				$upd2['entry_need'] = mi($r['entry_need']);
				$upd2['max_redeem'] = mi($r['max_redeem']);
				$upd2['sequence'] = $sequence;
				$con->sql_query("insert into membership_package_items ".mysql_insert_by_field($upd2));
			}
		}
		
		if($is_confirm){
			// Is Confirm, Need to Link SKU Now
			$upd3 = array();
			$upd3['membership_package_unique_id'] = $package_unique_id;
			$upd3['lastupdate'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("update sku_items set ".mysql_update_by_field($upd3)." where membership_package_unique_id=0 and id=".mi($upd['link_sku_item_id']));
		}
		
		log_br($sessioninfo['id'], 'MEMBERSHIP', $package_unique_id, $str_log);
		
		// Commit changes
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['package_unique_id'] = $package_unique_id;
		$ret['doc_no'] = $doc_no;
		
		print json_encode($ret);
	}
	
	function ajax_list_sel(){
		global $con, $sessioninfo, $smarty, $LANG;
        
        $t = mi($_REQUEST['t']);
		$p = mi($_REQUEST['p']);
		$size = $this->package_list_size;
		$start = $p*$size;
		
		$filter = array();
		switch($t){
			case 1:	// saved
				$filter[] = "mp.active=1 and mp.status=0";
				break;
			case 2: // Confirmed
				$filter[] = "mp.active=1 and mp.status=1";
				break;
			case 3: // Cancelled
				$filter[] = "mp.active=0";
				break;
			case 0: // search items
				$str = $_REQUEST['search_str'];
				if(!$str)	die('Cannot search empty string');
				$filter_or[] = "mp.id=".ms($str);
				$filter_or[] = "mp.doc_no=".ms($str);
				$filter_or[] = "mp.unique_id=".ms($str);
				$filter_or[] = "mp.title like ".ms('%'.$str.'%');
				$filter_or[] = "si.sku_item_code=".ms($str);
				$filter_or[] = "si.mcode=".ms($str);
				$filter_or[] = "si.artno=".ms($str);
				$filter_or[] = "si.link_code=".ms($str);
				$filter[] = "(".join(' or ',$filter_or).")";
				break;
			default:
				die('Invalid Page');
		}
		if(BRANCH_CODE!='HQ'){
			$filter[] = "(mp.branch_id=$sessioninfo[branch_id])";
		}
		$str_filter = "where ".join(' and ',$filter);
			
		$con->sql_query("select count(*) as c
						from membership_package mp
						left join sku_items si on si.id=mp.link_sku_item_id
						$str_filter");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();

		$total_rows = mi($tmp['c']);
		if($start>=$total_rows){
			$start = 0;
			$_REQUEST['p'] = 0;
		}
		
		$limit = "limit $start, $size";
		$order = "order by mp.last_update desc, mp.doc_no desc";
		$total_page = ceil($total_rows/$size);

		$sql = "select mp.*, b.code as bcode, u.u as owner_u
				from membership_package mp
				left join branch b on b.id=mp.branch_id
				left join user u on u.id=mp.user_id
				left join sku_items si on si.id=mp.link_sku_item_id
				$str_filter 
				$order 
				$limit";
		//print $sql;
		$q1 = $con->sql_query($sql);
		$package_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$r['allowed_branches'] = unserialize($r['allowed_branches']);
			$package_list[] = $r;
		}
		$con->sql_freeresult($q1);
		//print_r($package_list);
		$smarty->assign('package_list', $package_list);
		$smarty->assign('total_page',$total_page);
		$smarty->display("membership.package.setup.list.tpl");
	}
	
	function ajax_cancel_package(){
		global $con, $sessioninfo, $appCore, $config, $LANG;
		
		//print_r($_REQUEST);exit;
		
		$package_unique_id = mi($_REQUEST['unique_id']);
		$cancel_reason = trim($_REQUEST['cancel_reason']);
		
		if(!$cancel_reason)	die(sprintf($LANG['MEMBERSHIP_PACKAGE_INVALID_DATA'], 'Cancel Reason'));
		
		// Load Package
		$result = $appCore->memberManager->getMembershipPackageByUniqueID($package_unique_id);
		if(!$result['data'] || $result['error']){
			die($result['error']);
		}
		$form = $result['data'];
		
		// Package is not active, cannot cancel
		if(!($form['active']==1 && $form['status']==1)){
			die($LANG['MEMBERSHIP_PACKAGE_NOT_ALLOW_CANCEL']);
		}
		
		// Reset to Save
		$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;
		if($sessioninfo['level']<$required_level){
			if(!privilege('MEMBERSHIP_PACK_CANCEL')){
				die(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_PACK_CANCEL', BRANCH_CODE));
			}
		}
		
		$upd = array();
		$upd['active'] = 0;
		$upd['cancel_reason'] = $cancel_reason;
		$upd['cancelled_by'] = $sessioninfo['id'];
		$upd['last_update'] = 'CURRENT_TIMESTAMP';

		$con->sql_query("update membership_package set ".mysql_update_by_field($upd)." where unique_id=$package_unique_id");

		log_br($sessioninfo['id'], 'MEMBERSHIP', $package_unique_id, "Cancelled Membership Package (".$form['doc_no'].")");
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['doc_no'] = $form['doc_no'];
		print json_encode($ret);
	}
}

$MEMBERSHIP_PACKAGE_SETUP = new MEMBERSHIP_PACKAGE_SETUP('Package Setup');
?>