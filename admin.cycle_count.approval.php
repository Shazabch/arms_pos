<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('STOCK_TAKE_CYCLE_COUNT_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_TAKE_CYCLE_COUNT_APPROVAL', BRANCH_CODE), "/index.php");
$maintenance->check(405);

class CC_APPROVAL extends Module {
	function __construct($title)
	{
		// load all initial data
		$this->init_load();
		
		parent::__construct($title);
	}
	
	private function init_load(){
		global $appCore, $smarty;
		
		$smarty->assign('st_content_type_list', $appCore->stockTakeManager->cycleCountContentTypeList);
		$smarty->assign('cycle_count_too_many_sku_count', $appCore->stockTakeManager->cycleCountTooManySKUCount);
		
	}
	
	function _default()
	{
		global $smarty, $sessioninfo, $config;

		$this->load_approval_list();
		
		$this->display();
	}
	
	function ajax_load_cycle_count(){
		global $con, $appCore, $smarty;
		
		$id=mi($_REQUEST['id']);
		$bid=mi($_REQUEST['branch_id']);
		
		list($form, $err_msg) = $appCore->stockTakeManager->loadCycleCount($bid, $id);
		if(!$form){	// Not Found
			die($err_msg);
		}
		$this->load_required_data($form);
		
		$form['approval_screen']=1;
		$smarty->assign('form', $form);
		$this->display('admin.cycle_count.assignment.open.tpl');
	}
	
	 function load_approval_list(){
		global $smarty, $LANG, $sessioninfo, $con, $config;
	
		$bid = mi($sessioninfo['branch_id']);
		$user_id = mi($sessioninfo['id']);
		
		$search_approval = $user_id;

		$sql = "select cc.*, bah.approvals, bah.flow_approvals as org_approvals, user.u as user_name, b2.code as b2_code
	from cycle_count cc
	join branch_approval_history bah on cc.approval_history_id = bah.id and cc.branch_id=$bid
	left join user on user.id=cc.user_id
	left join branch b2 on cc.st_branch_id = b2.id
	where (
	(bah.approvals like '|$search_approval|%' and bah.approval_order_id=1) or
	(bah.approvals like '%|$search_approval|%' and bah.approval_order_id in (2,3))
	) and cc.active=1 and cc.status=1 and cc.approved=0";
		//print $sql;
		$con->sql_query($sql);
		$cc_list = array();
		while($r = $con->sql_fetchassoc()){
			$cc_list[] = $r;
		}
		$con->sql_freeresult();
		
		$smarty->assign("cc_list", $cc_list);
	}
	
	private function load_required_data($form){
		global $con, $sessioninfo, $smarty, $appCore;
		
		// Branch
		$this->branches = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign('branches', $this->branches);
		
		// SKU Group
		$params = array();
		$params['get_all'] = 1;
		$this->sku_group_list = $appCore->skuManager->getSKUGroupList($params);
		$smarty->assign('sku_group_list', $this->sku_group_list);
		
		// Vendor
		$this->vendor_list = $appCore->vendorManager->getVendorList(array('active'=>1));
		$smarty->assign('vendor_list', $this->vendor_list);
		
		// Brand
		$this->brand_list = $appCore->brandManager->getBrandList(array('active'=>1));
		$smarty->assign('brand_list', $this->brand_list);
		
		// Cycle Count Users
		if($form['id']){
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
	
	function submit_approval(){
		global $appCore;
		
		$form = $_REQUEST;
		
		//print_r($form);
		
		$cc_id = mi($form['id']);
		$bid = mi($form['branch_id']);
					
		$appCore->stockTakeManager->cycleCountApproval($bid, $cc_id, $form['status_type']);
		
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;
	}
}

$CC_APPROVAL = new CC_APPROVAL('Cycle Cout Approval');
?>