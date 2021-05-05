<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MST_VENDOR_QUOTATION_COST')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VENDOR_QUOTATION_COST', BRANCH_CODE), "/index.php");
$maintenance->check(360);

class VENDOR_QUOTATION_COST extends Module{
	var $branch_list;
	var $limit_per_page = 20;
	
	function __construct($title){
		global $con, $smarty;
		
		$this->init_load();
		
		parent::__construct($title);
	}
	
	function _default(){
		if($_REQUEST['vendor_id']){
			$this->load_vendor_data();
		}
	    $this->display();
	}
	
	private function init_load(){
		global $con, $appCore, $smarty;
		
		// Branch
		$this->branch_list = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign('branch_list', $this->branch_list);
	}
	
	private function load_vendor_data(){
		global $con, $smarty, $sessioninfo;
		
		$form = array();
		$err = array();
		$vendor_id = mi($_REQUEST['vendor_id']);
		$sku_item_id = mi($_REQUEST['sku_item_id']);
		$p = mi($_REQUEST['p']);
		
		// Select Vendor
		$q1 = $con->sql_query("select v.id, v.code, v.description
							   from vendor v
							   where v.id=$vendor_id");
		$form['info'] = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		// Vendor Not Found
		if(!$form['info']){	
			$err[] = "Vendor ID#$vendor_id Not Found.";
		}
		
		// Got Error
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$form['vendor_id'] = $vendor_id;
		
		
		$filter = array();
		$filter[] = "sivqc.vendor_id=$vendor_id";
		if(BRANCH_CODE != 'HQ')	$filter[] = "sivqc.branch_id=".mi($sessioninfo['branch_id']);
		
		if($sku_item_id>0){	// filter sku
			$filter[] = "sivqc.sku_item_id=$sku_item_id";
		}
		$str_filter = join(' and ', $filter);
		// count total row
		$q1 = $con->sql_query("select count(distinct sivqc.sku_item_id) as total_count
							   from sku_items_vendor_quotation_cost sivqc
							   where $str_filter");
		$tmp = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$form['sku_matched'] = mi($tmp['total_count']);
		
		if($form['sku_matched'] > 0){
			$form['total_page'] = ceil($form['sku_matched'] / $this->limit_per_page);
			$str_order = "order by si.sku_id desc";
			$str_limit = "limit ".($p*$this->limit_per_page).",".$this->limit_per_page;
			
			$sid_list = array();
			// get sku item id in this page
			$con->sql_query("select distinct sivqc.sku_item_id as sid
							from sku_items_vendor_quotation_cost sivqc
							join sku_items si on sivqc.sku_item_id=si.id
							where $str_filter
							$str_order
							$str_limit");
			while($r = $con->sql_fetchassoc()){
				$sid_list[] = mi($r['sid']);
			}
			$con->sql_freeresult();
			
			$q1 = $con->sql_query("select sivqc.branch_id, sivqc.vendor_id, si.id as sid, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description, sivqc.cost
								   from sku_items_vendor_quotation_cost sivqc
								   join sku_items si on sivqc.sku_item_id=si.id
								   where $str_filter and sivqc.sku_item_id in (".join(',', $sid_list).")
								   $str_order");
			//print $sql;
			while($r = $con->sql_fetchassoc($q1)){
				if(!$form['sku_list'][$r['sid']]){
					$form['sku_list'][$r['sid']]['info']['sku_item_code'] = $r['sku_item_code'];
					$form['sku_list'][$r['sid']]['info']['mcode'] = $r['mcode'];
					$form['sku_list'][$r['sid']]['info']['artno'] = $r['artno'];
					$form['sku_list'][$r['sid']]['info']['link_code'] = $r['link_code'];
					$form['sku_list'][$r['sid']]['info']['description'] = $r['description'];
					$form['sku_list'][$r['sid']]['cost_not_set'] = 1;
				}
				
				if(!isset($this->branch_list[$r['branch_id']])){
					continue;
				}
				$form['sku_list'][$r['sid']]['b_list'][$r['branch_id']]['cost'] = $r['cost'];
				
				if($r['cost']>0){	// Cost got set
					unset($form['sku_list'][$r['sid']]['cost_not_set']);
				}
				
			}
			$con->sql_freeresult($q1);
		}
		
		//print_r($form);
		$smarty->assign('form', $form);
		return $form;
	}
	
	function ajax_reload_sku_list(){
		global $con, $smarty, $sessioninfo;
		
		$form = $this->load_vendor_data();
		$smarty->assign('form', $form);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('masterfile_vendor.quotation_cost.sku_list.tpl');
		$ret['found'] = $form['sku_matched'];
		
		die(json_encode($ret));
	}
	
	function ajax_open_sku(){
		global $con, $smarty, $sessioninfo, $appCore;
		
		$vendor_id = mi($_REQUEST['vendor_id']);
		$sid = mi($_REQUEST['sku_item_id']);
		$selected_bid = BRANCH_CODE == 'HQ' ? mi($_REQUEST['bid']) : $sessioninfo['branch_id'];
		$data = array();
		$data['vendor_id'] = $vendor_id;
		$data['sid'] = $sid;
		
		if(BRANCH_CODE == 'HQ' && !$selected_bid){	// all branch
			$data['bid_list'] = array_keys($this->branch_list);
		}else{
			$data['bid_list'] = array($selected_bid);
		}
		
		// Get SKU General Info
		$data['info'] = $appCore->skuManager->getSKUItemsInfo($sid);
		
		foreach($data['bid_list'] as $bid){
			$data['b_info'][$bid]['normal_cost'] = $appCore->vendorManager->getSKUItemCost($bid, $sid);
			$data['b_info'][$bid]['quotation_cost'] = $appCore->vendorManager->getSKUItemQuotationCost($bid, $sid, $vendor_id);
		}
		
		// SKU Not Found
		if(!$data['info'])	die("Invalid SKU ITEM ID#$sid");
		
		$smarty->assign('data', $data);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('masterfile_vendor.quotation_cost.open_sku.tpl');
		
		die(json_encode($ret));
	}
	
	function ajax_update_quotation_cost(){
		global $con, $smarty, $sessioninfo, $appCore;
		
		$vendor_id = mi($_REQUEST['vendor_id']);
		$sid = mi($_REQUEST['sid']);
		$quotation_cost = $_REQUEST['quotation_cost'];
		
		if(!$vendor_id)	die("Invalid Vendor ID#$vendor_id");
		if(!$sid)	die("Invalid SKU ITEM ID#$sid");
		if(!$quotation_cost)	die("Invalid Quotation Cost Data");
		
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		$params['vendor_id'] = $vendor_id;
		$params['sid'] = $sid;
		
		$update_success = 0;
		foreach($quotation_cost as $bid => $cost){
			$params['bid'] = $bid;
			$params['cost'] = $cost;
			$result = $appCore->vendorManager->updateVendorQuotationCost($params);
			if($result['ok'])	$update_success = 1;
		}
		
		$ret = array();
		$ret['ok'] = $update_success;
		if(!$ret['ok'])	$ret['failed_reason'] = 'Nothing to update';
		
		die(json_encode($ret));
	}
	
	function ajax_load_quotation_cost_history(){
		global $con, $smarty, $sessioninfo, $appCore;
		
		$vendor_id = mi($_REQUEST['vendor_id']);
		$sid = mi($_REQUEST['sid']);
		$bid = BRANCH_CODE == 'HQ' ? mi($_REQUEST['bid']) : $sessioninfo['branch_id'];
		
		$sku_cost_his = array();
		$filter = array();
		$filter[] = "sivqch.branch_id=$bid";
		$filter[] = "sivqch.sku_item_id=$sid";
		$filter[] = "sivqch.vendor_id=$vendor_id";
		$str_filter = join(' and ', $filter);
		
		$q1 = $con->sql_query("select sivqch.* , user.u
							   from sku_items_vendor_quotation_cost_history sivqch
							   left join user on user.id=sivqch.user_id
							   where $str_filter
							   order by sivqch.added desc");
		while($r = $con->sql_fetchassoc($q1)){
			$sku_cost_his[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('sku_cost_his', $sku_cost_his);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('masterfile_vendor.quotation_cost.history.tpl');
		
		die(json_encode($ret));
	}
}

$VENDOR_QUOTATION_COST = new VENDOR_QUOTATION_COST('Quotation Cost');
?>