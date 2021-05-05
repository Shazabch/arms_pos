<?php
/*
3/2/2021 3:44 PM Andy
- Enhanced Work Order to can transfer by Weight to Pcs.
- increased maintenance checking to v493.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ADJ')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ADJ', BRANCH_CODE), "/index.php");
if (!privilege('SHOW_COST')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SHOW_COST', BRANCH_CODE), "/index.php");
if (!privilege('ADJ_WORK_ORDER')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ADJ_WORK_ORDER', BRANCH_CODE), "/index.php");

if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules'] && $_REQUEST['a'] != 'view') js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

$maintenance->check(493);
$maintenance->check(493, true);

class WORK_ORDER extends Module{
	var $is_view = false;
	
	function __construct(){
		global $con, $smarty, $appCore;
		
		$smarty->assign('transfer_type_list', $appCore->workOrderManager->transfer_type_list);
		
		parent::__construct($appCore->workOrderManager->moduleName);
	}
	
	function _default(){
	    $this->display();
	}
	
	// function when webpage request to change listing
	function ajax_reload_list(){
		global $appCore, $smarty, $sessioninfo;

		$params = array();
		$params['p'] = mi($_REQUEST['p']);
		if(BRANCH_CODE != 'HQ'){
			$params['branch_id'] = $sessioninfo['branch_id'];
		}
		
		switch($_REQUEST['t']){
			case 1:
				$params['type'] = 'transfer_out';
				break;
			case 2:
				$params['type'] = 'transfer_in';
				break;
			case 3:
				$params['type'] = 'cancelled';
				break;
			case 4:
				$params['type'] = 'completed';
				break;
			default:
				$params['type'] = 'search';
				$params['search_str'] = trim($_REQUEST['search_str']);
				break;
		}
		
		$ret = array();

		// load the list
		$data = $appCore->workOrderManager->loadWorkOrderListing($params);
		
		// show the list in html
		$smarty->assign('woList', $data['woList']);
		$smarty->assign('total_page', $data['total_page']);
		$ret['html'] = $smarty->fetch('work_order.list.tpl');
		

		if($ret['html']){
			$ret['ok'] = 1;
		}else{
			$ret['failed_reason'] = 'Failed to load data.';
		}
		
		print json_encode($ret);
	}
	
	function view(){
		$this->is_view = true;
		$this->open();
	}
	
	// function when user click add/edit cn
	function open(){
		global $sessioninfo, $appCore, $smarty, $LANG, $con;

		$wo_id = mi($_REQUEST['id']);

		$params = array();

		if($wo_id > 0){
			// edit
			if(BRANCH_CODE == 'HQ'){
				$bid = mi($_REQUEST['branch_id']);
			}
			if(!$bid)	$bid = $sessioninfo['branch_id'];
			
			// not view, meaning is edit mode
			if(!$this->is_view){
				// check can edit or not
				$checkParams = array();
				$checkParams['branch_id'] = $sessioninfo['branch_id'];
				$checkParams['user_id'] = $sessioninfo['id'];
				$checkRet = $appCore->workOrderManager->isWorkOrderAllowToEdit($bid, $wo_id, $checkParams);
				
				// cannot edit, prompt error
				if($checkRet['err']){
					$data['err'][0] = $checkRet['err'];
				}
			}
			
			if(!$data['err']){
				// load
				$tmp = array();
				$tmp['loadItems'] = 1;
				$tmp['user_id'] = $sessioninfo['id'];
				if(!$this->is_view)	$tmp['isEdit'] = 1;
				$data = $appCore->workOrderManager->loadWorkOrder($bid, $wo_id, $tmp);
			}
		}else{
			// new
			$bid = $params['branch_id'] = $sessioninfo['branch_id'];	// must use login branch id
			$params['user_id'] = $sessioninfo['id'];

			// create temporary new cn
			$data = $appCore->workOrderManager->generateTempNewWorkOrder($params);
		}
		
		// check privileges
		if(!$this->is_view){
			$action = $data['header']['status']==1 ? 'in' : 'out';
			
			if($action == 'out' && !privilege("ADJ_WORK_ORDER_OUT")){
				$data['err'][0] = sprintf($LANG['NO_PRIVILEGE'], 'ADJ_WORK_ORDER_OUT', BRANCH_CODE);
			}
			
			if($action == 'in' && !privilege("ADJ_WORK_ORDER_IN")){
				$data['err'][0] = sprintf($LANG['NO_PRIVILEGE'], 'ADJ_WORK_ORDER_IN', BRANCH_CODE);
			}
		}
		
		if($data['err']){
			// show the first error
			display_redir($_SERVER['PHP_SELF'], $this->title, $data['err'][0]);
		}
		
		// load department list
		$this->load_dept_list($data);
		
		if($data['header']['transfer_type'] == 'w2p'){
			// Get UOM List
			$uom_list = $appCore->uomManager->getUOMList(array('active'=>1));
			$smarty->assign('uom_list', $uom_list);
		}
		
		//print_r($data);
		$smarty->assign('form', $data['header']);
		$smarty->assign('items_list', $data['items_list']);
		
		
		if(!$this->is_view){
			$smarty->assign('action', $action);
			$smarty->assign('can_edit', 1);
			if($action == 'out'){
				$transferInUserList = $appCore->workOrderManager->loadAllowedTransferUsers($bid, 'in');
				$smarty->assign('transferInUserList', $transferInUserList);
			}
		}
		
		$this->display('work_order.open.tpl');
	}
	
	private function load_dept_list($data){
		global $con, $sessioninfo, $smarty;
		
		$filter = array();
		$filter[] = "c.active=1 and c.level=2";
		
		// manager and above can see all department
		if ($sessioninfo['level'] < 9999){
			if (!$sessioninfo['department_ids'])
				$filter[] = "c.id in (0)";
			else
				$filter[] = "c.id in (" . $sessioninfo['department_ids'] . ")";
		}
	
		$filter[] = "c.id in (".$sessioninfo['department_ids'].")";
		$filter = join(' and ', $filter);
		
		if($data['header']['dept_id'])	$filter = "(".$filter.") or c.id=".mi($data['header']['dept_id']);
		
		// show department option
		$con->sql_query("select c.id, c.description from category c where $filter order by c.description");
		$dept_list = array();
		while($r = $con->sql_fetchassoc()){
			$dept_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign("dept_list", $dept_list);
	}
	
	function ajax_add_item_row(){
		global $appCore, $smarty, $LANG, $sessioninfo, $con;
		
		//print_r($_REQUEST);
		
		$action = trim($_REQUEST['action']);
		$transfer_type = trim($_REQUEST['transfer_type']);
		
		if($action != 'out' && $action != 'in')	die("Invalid Action!");
		
		// add new item
		$params = array();
		$params['branch_id']  = mi($_REQUEST['branch_id']);
		$params['wo_id'] = mi($_REQUEST['id']);
		$params['branch_is_under_gst'] = $branch_is_under_gst = mi($_REQUEST['branch_is_under_gst']);
		$params['edit_time'] = mi($_REQUEST['edit_time']);
		$params['user_id'] = $sessioninfo['id'];
		$params['adj_date'] = $_REQUEST['adj_date'];
		$params['action'] = $action;
		$params['sid_list'] = $params['qty_list'] = array();
				
		$grn_barcode = trim($_REQUEST['grn_barcode']);
		if($grn_barcode){
			// scan barcode
			$params['grn_barcode'] = $grn_barcode;			
			$grn_barcode_info = get_grn_barcode_info($params['grn_barcode']);
			if($grn_barcode_info['err'])	die($grn_barcode_info['err']);	// got error
			
			$params['sid_list'] = array(mi($grn_barcode_info['sku_item_id']));
			$params['qty_list'] = array(mi($grn_barcode_info['qty_pcs']));
		}else{
			// sku item id
			$params['sid_list'] = $_REQUEST['sku_code_list'];
			foreach($params['sid_list'] as $k=>$v){
				if(intval($v)<=0) unset($params['sid_list'][$k]);
			}
			$params['sid_list'] = array_values($params['sid_list']);
			if(!$params['sid_list'])	die("No item to add.");
		}

		// get current sku item id array
		$current_sid_list = array();
		if($_REQUEST['items_list'][$action]) {
			foreach($_REQUEST['items_list'][$action] as $r) {
				$current_sid_list[] = mi($r['sku_item_id']);
			}
		}
	
		// check duplicate sku
		$duplicated = false;
		foreach($params['sid_list'] as $sid) {	
			if(in_array($sid, $current_sid_list)) {
				$duplicated = true;
			}
		}
		
		if($duplicated)	die($LANG['WORK_ORDER_ITEM_DUPLICATED']);
		
		if($action == 'in' && $transfer_type == 'w2p'){
			// Allow non weight sku for weight to pcs when transfer in
			$params['allow_non_weight'] = 1;
		}
		
		// call function to add temporary item
		$data = $appCore->workOrderManager->addTempItems($params);
		$ret = array();
		
		// got error
		if($data['error'])	$ret['failed_reason'] = $data['error'];
		else{
			// check got item
			if($data['items_list']){
				if($action == 'in' && $transfer_type == 'w2p'){
					// Get UOM List
					$uom_list = $appCore->uomManager->getUOMList(array('active'=>1));
					$smarty->assign('uom_list', $uom_list);
					
					// Get UOM Each
					$uom_each = $appCore->uomManager->getUOMForEach();
				}
				
				$smarty->assign('form', $_REQUEST);
				$smarty->assign('can_edit', 1);
				$smarty->assign('action', $action);


				$ret['html'] = '';
				// loop item and create html
				if($action == 'in'){
					$tpl = 'work_order.open.in.item_row.tpl';
				}else{
					$tpl = 'work_order.open.out.item_row.tpl';
				}
				foreach($data['items_list'] as $item){
					if($action == 'in' && $transfer_type == 'w2p'){
						// default as EACH
						$item['uom_id'] = $uom_each['id'];
						$item['uom_fraction'] = $uom_each['fraction'];
					}
					
					$smarty->assign('item', $item);
					$ret['html'] .= $smarty->fetch($tpl);
					$ret['item_id_list'][] = $item['id'];
				}
				$ret['ok'] = 1;
				if(isset($params['qty_list']) && $params['qty_list'])	$ret['need_recalc'] = 1;
			}else{
				// no item found
				$ret['failed_reason'] = $LANG['ITEM_NOT_FOUND'];
			}
		}
		
		print json_encode($ret);
	}
	
	function ajax_delete_item(){
		global $con, $smarty, $appCore, $config, $sessioninfo;
		$ret = array();
		
		$form = $_REQUEST;
		
		$action = trim($_REQUEST['action']);
		if($action != 'out' && $action != 'in')	die("Invalid Action!");
		
		$params = array();
		$params['branch_id']  = mi($form['branch_id']);
		$params['wo_id'] = mi($form['id']);
		$params['edit_time'] = mi($form['edit_time']);
		$params['user_id'] = $sessioninfo['id'];
		$params['item_id'] = $form['delete_item_id'];
		$params['action'] = $action;
		
		// call function to delete
		$data = $appCore->workOrderManager->deleteTempItem($form['branch_id'], $form['id'], $params);
		
		if($data['ok']){
			$ret['ok'] = 1;
		}else{
			$ret['failed_reason'] = $data['err'];
		}
		
		print json_encode($ret);
	}
	
	function ajax_confirm(){
		$this->ajax_save(true);
	}
	
	function ajax_save($is_confirm = false){
		global $appCore, $sessioninfo;
		
		$action = trim($_REQUEST['action']);
		if($action != 'out' && $action != 'in')	die("Invalid Action!");
		
		$form = $_REQUEST;
		//print_r($form);exit;
		
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		$params['branch_id'] = $sessioninfo['branch_id'];
		$params['action'] = $action;
		
		if($is_confirm)	$params['is_confirm'] = 1;
		$data = $appCore->workOrderManager->saveWorkOrder($form, $form['items_list'], $params);
		if($data['err'])	$data['failed_reason'] = $data['err'];
		
		print json_encode($data);
	}
	
	function ajax_delete(){
		global $appCore, $sessioninfo;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		$params['branch_id'] = $sessioninfo['branch_id'];
		$params['deleted_reason'] = trim($form['deleted_reason']);
		
		$data = $appCore->workOrderManager->deleteWorkOrder($form['branch_id'], $form['id'], $params);
		if($data['err'])	$data['failed_reason'] = $data['err'];
		
		print json_encode($data);
	}
	
	function do_reset(){
		global $appCore, $sessioninfo;
		
		$form = $_REQUEST;
		//print_r($form);exit;
		
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		$params['branch_id'] = $sessioninfo['branch_id'];
		$params['reason'] = trim($form['reason']);
		$params['to_action'] = trim($form['to_action']);
		
		$data = $appCore->workOrderManager->resetWorkOrder($form['branch_id'], $form['id'], $params);
		if($data['err']){
			display_redir($_SERVER['PHP_SELF'], $this->title, $data['err']);
		}	
		
		if($data['ok']){
			header("Location: $_SERVER[PHP_SELF]?t=reset&wo_id=$form[id]");
		}else{
			display_redir($_SERVER['PHP_SELF'], $this->title, "Reset Failed<br>".$data['err']);
		}
		
		exit;
	}
}

$WORK_ORDER = new WORK_ORDER();
?>