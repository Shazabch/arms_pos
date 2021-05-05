<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MST_DEBTOR_PRICE_LIST')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_DEBTOR_PRICE_LIST', BRANCH_CODE), "/index.php");
$maintenance->check(360);

class DEBTOR_PRICE extends Module{
	var $branch_list;
	var $limit_per_page = 20;
	
	function __construct($title){
		global $con, $smarty;
		
		$this->init_load();
		
		parent::__construct($title);
	}
	
	function _default(){
		if($_REQUEST['debtor_id']){
			$this->load_debtor_data();
		}
	    $this->display();
	}
	
	private function init_load(){
		global $con, $appCore, $smarty;
		
		// Branch
		$this->branch_list = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign('branch_list', $this->branch_list);
	}
	
	private function load_debtor_data(){
		global $con, $smarty, $sessioninfo;
		
		$form = array();
		$err = array();
		$debtor_id = mi($_REQUEST['debtor_id']);
		$sku_item_id = mi($_REQUEST['sku_item_id']);
		$p = mi($_REQUEST['p']);
		
		// Select Debtor
		$con->sql_query("select d.id, d.code, d.description
			from debtor d
			where d.id=$debtor_id");
		$form['info'] = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Debtor Not Found
		if(!$form['info']){	
			$err[] = "Debtor ID#$debtor_id Not Found.";
		}
		
		// Got Error
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$form['debtor_id'] = $debtor_id;
		
		
		$filter = array();
		$filter[] = "sidp.debtor_id=$debtor_id";
		if(BRANCH_CODE != 'HQ')	$filter[] = "sidp.branch_id=".mi($sessioninfo['branch_id']);
		
		if($sku_item_id>0){	// filter sku
			$filter[] = "sidp.sku_item_id=$sku_item_id";
		}
		$str_filter = join(' and ', $filter);
		// count total row
		$con->sql_query("select count(distinct sidp.sku_item_id) as total_count
			from sku_items_debtor_price sidp
			where $str_filter");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$form['sku_matched'] = mi($tmp['total_count']);
		
		if($form['sku_matched'] > 0){
			$form['total_page'] = ceil($form['sku_matched'] / $this->limit_per_page);
			$str_order = "order by si.sku_id desc";
			$str_limit = "limit ".($p*$this->limit_per_page).",".$this->limit_per_page;
			
			$sid_list = array();
			// get sku item id in this page
			$con->sql_query("select distinct sidp.sku_item_id as sid
				from sku_items_debtor_price sidp
				join sku_items si on sidp.sku_item_id=si.id
				where $str_filter
				$str_order
				$str_limit");
			while($r = $con->sql_fetchassoc()){
				$sid_list[] = mi($r['sid']);
			}
			$con->sql_freeresult();
			
			$q1 = $con->sql_query($sql = "select sidp.branch_id, sidp.debtor_id, si.id as sid, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description, sidp.price
				from sku_items_debtor_price sidp
				join sku_items si on sidp.sku_item_id=si.id
				where $str_filter and sidp.sku_item_id in (".join(',', $sid_list).")
				$str_order");
			//print $sql;
			while($r = $con->sql_fetchassoc($q1)){
				if(!$form['sku_list'][$r['sid']]){
					$form['sku_list'][$r['sid']]['info']['sku_item_code'] = $r['sku_item_code'];
					$form['sku_list'][$r['sid']]['info']['mcode'] = $r['mcode'];
					$form['sku_list'][$r['sid']]['info']['artno'] = $r['artno'];
					$form['sku_list'][$r['sid']]['info']['link_code'] = $r['link_code'];
					$form['sku_list'][$r['sid']]['info']['description'] = $r['description'];
					$form['sku_list'][$r['sid']]['price_not_set'] = 1;
				}
				
				if(!isset($this->branch_list[$r['branch_id']])){
					continue;
				}
				$form['sku_list'][$r['sid']]['b_list'][$r['branch_id']]['price'] = $r['price'];
				
				if($r['price']>0){	// Price got set
					unset($form['sku_list'][$r['sid']]['price_not_set']);
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
		
		$form = $this->load_debtor_data();
		$smarty->assign('form', $form);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('masterfile_debtor_price.sku_list.tpl');
		$ret['found'] = $form['sku_matched'];
		
		die(json_encode($ret));
	}
	
	function ajax_open_sku(){
		global $con, $smarty, $sessioninfo, $appCore;
		
		$debtor_id = mi($_REQUEST['debtor_id']);
		$sid = mi($_REQUEST['sku_item_id']);
		$selected_bid = BRANCH_CODE == 'HQ' ? mi($_REQUEST['bid']) : $sessioninfo['branch_id'];
		$data = array();
		$data['debtor_id'] = $debtor_id;
		$data['sid'] = $sid;
		
		if(BRANCH_CODE == 'HQ' && !$selected_bid){	// all branch
			$data['bid_list'] = array_keys($this->branch_list);
		}else{
			$data['bid_list'] = array($selected_bid);
		}
		
		// Get SKU General Info
		$data['info'] = $appCore->skuManager->getSKUItemsInfo($sid);
		
		foreach($data['bid_list'] as $bid){
			$data['b_info'][$bid]['normal_price'] = $appCore->skuManager->getSKUItemPrice($bid, $sid);
			$data['b_info'][$bid]['debtor_price'] = $appCore->skuManager->getSKUItemDebtorPrice($bid, $sid, $debtor_id);
		}
		
		// SKU Not Found
		if(!$data['info'])	die("Invalid SKU ITEM ID#$sid");
		
		$smarty->assign('data', $data);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('masterfile_debtor_price.open_sku.tpl');
		
		die(json_encode($ret));
	}
	
	function ajax_update_debtor_price(){
		global $con, $smarty, $sessioninfo, $appCore;
		
		$debtor_id = mi($_REQUEST['debtor_id']);
		$sid = mi($_REQUEST['sid']);
		$debtor_price = $_REQUEST['debtor_price'];
		
		if(!$debtor_id)	die("Invalid Debtor ID#$debtor_id");
		if(!$sid)	die("Invalid SKU ITEM ID#$sid");
		if(!$debtor_price)	die("Invalid Debtor Price Data");
		
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		$params['debtor_id'] = $debtor_id;
		$params['sid'] = $sid;
		
		$update_success = 0;
		foreach($debtor_price as $bid => $price){
			$params['bid'] = $bid;
			$params['price'] = $price;
			$result = $appCore->skuManager->updateDebtorPrice($params);
			if($result['ok'])	$update_success = 1;
		}
		
		$ret = array();
		$ret['ok'] = $update_success;
		if(!$ret['ok'])	$ret['failed_reason'] = 'Nothing to update';
		
		die(json_encode($ret));
	}
	
	function ajax_load_price_history(){
		global $con, $smarty, $sessioninfo, $appCore;
		
		$debtor_id = mi($_REQUEST['debtor_id']);
		$sid = mi($_REQUEST['sid']);
		$bid = BRANCH_CODE == 'HQ' ? mi($_REQUEST['bid']) : $sessioninfo['branch_id'];
		
		$sku_price_his = array();
		$filter = array();
		$filter[] = "sidph.branch_id=$bid";
		$filter[] = "sidph.sku_item_id=$sid";
		$filter[] = "sidph.debtor_id=$debtor_id";
		$str_filter = join(' and ', $filter);
		
		$q1 = $con->sql_query("select sidph.* , user.u
			from sku_items_debtor_price_history sidph
			left join user on user.id=sidph.user_id
			where $str_filter
			order by sidph.added desc");
		while($r = $con->sql_fetchassoc($q1)){
			$sku_price_his[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('sku_price_his', $sku_price_his);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('masterfile_debtor_price.price_history.tpl');
		
		die(json_encode($ret));
	}
}

$DEBTOR_PRICE = new DEBTOR_PRICE('Debtor Price List');
?>