<?php
/*
1/8/2020 4:25 PM William
- Enhanced to insert id manually for adjustment tables that uses auto increment.

3/2/2021 3:44 PM Andy
- Enhanced Work Order to can transfer by Weight to Pcs.
*/
class workOrderManager{
	// common variable
	public $moduleName = "Work Order";
	public $adj_type = "Work Order";
	public $adj_module_type = "work_order";
	public $transfer_type_list = array('w2w'=>'Weight to Weight', 'w2p'=>'Weight to Pcs');
	
	// private variable
	private $listSize = 15;
	private $printItemPerPage = 15;
	
	function __construct(){
		global $smarty, $con, $appCore;

		
	}
	
	// function to load cnote list
	// return array data
	public function loadWorkOrderListing($params = array()){
		global $con;

		$type = trim($params['type']);
		$p = mi($_REQUEST['p']);	// page
		$bid = mi($params['branch_id']);
		
		$size = $this->listSize;
		$start = $p*$size;
		
		$data = array();
		$filter = array();
		if($bid)	$filter[] = "wo.branch_id=$bid";
		
		switch($type){
			case 'transfer_out':
				$filter[] = "wo.active=1 and wo.status=0 and wo.completed=0";
				break;
			case 'transfer_in':
				$filter[] = "wo.active=1 and wo.status=1 and wo.completed=0";
				break;
			case 'cancelled':
				$filter[] = "wo.active=0";
				break;
			case 'completed':
				$filter[] = "wo.active=1 and wo.status=1 and wo.completed=1";
				break;
			case 'search':
				//$filter[] = "wo.active=1";
				$str = replace_special_char(trim($params['search_str']));
				$filter[] = "(wo.id=".mi($str)." or wo.wo_no like ".ms('%'.$str.'%').")";
				break;
			default:
				return;
		}
		
		$filter = "where ".join(' and ', $filter);
		
		$con->sql_query("select count(*) 
			from work_order wo
			$filter");
		$total_rows = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		
		if($start>=$total_rows){
			$start = 0;
			$_REQUEST['p'] = 0;
		}
		$limit = "limit $start, $size";
		$order = "order by wo.last_update desc";

		$total_page = ceil($total_rows/$size);
		
		$data['woList'] = array();
		
		$q1 = $con->sql_query("select wo.*, user.u as created_u, dept.description as dept_desc
			from work_order wo
			left join user on user.id=wo.user_id
			left join category dept on dept.id=wo.dept_id
			$filter $order $limit");
		while($r = $con->sql_fetchassoc($q1)){
			$key = $r['branch_id'].'_'.$r['id'];
			$data['woList'][$key] = $r;
		}
		$con->sql_freeresult($q1);

		$data['total_page'] = $total_page;
				
		return $data;
	}
	
	// function to generate temporary new work order
	// return array $data
	public function generateTempNewWorkOrder($params = array()){
		global $con, $appCore, $config;

		$data = array();
		$bid = mi($params['branch_id']);
		$user_id = mi($params['user_id']);

		if(!$bid){
			$data['err'][] = "No branch id";
		}
		if(!$user_id){
			$data['err'][] = "No user id";	
		}

		$data['header'] = array();
		$data['items'] = array();

		// header
		$data['header']['branch_id'] = $bid;
		$data['header']['branch_code'] = BRANCH_CODE;
		$data['header']['user_id'] = $user_id;
		$data['header']['id'] = $appCore->generateTempID();
		$data['header']['edit_time'] = $appCore->generateEditTime();
		$data['header']['active'] = 1;
		$data['header']['status'] = 0;
		$data['header']['completed'] = 0;
		$data['header']['adj_date'] = date("Y-m-d");
		$data['header']['transfer_type'] = 'w2w';
		
		// check Branch GST
		if($config['enable_gst']){
			$prms = array();
			$prms['branch_id'] = $bid;
			$prms['date'] = $data['header']['adj_date'];
			$data['header']['branch_is_under_gst'] = check_gst_status($prms);
		}
	
		if($data['err']){
			return $data;
		}

		return $data;
	}
	
	// function to add temporary items
	// return array $ret
	public function addTempItems($params){
		global $con, $LANG, $appCore;
		
		$bid = mi($params['branch_id']);
		$wo_id = mi($params['wo_id']);
		$allow_non_weight = mi($params['allow_non_weight']);
		
		$ret = array();
		
		if(!$bid){
			$ret['error'] = "Invalid Branch ID";
			return $ret;
		}
		if(!$wo_id){
			$ret['error'] = "Invalid Work Order ID";
			return $ret;
		}

		if(!$ret['error']){
			if(!$params['sid_list'])	$ret['error'] = $LANG['ITEM_NOT_FOUND'];
			else{
				// loop for validation first
				if(!$allow_non_weight){
					foreach($params['sid_list'] as $key => $sid){
						// make sure this sku got weight_kg
						$con->sql_query("select weight_kg from sku_items where id=".mi($sid));
						$tmp = $con->sql_fetchassoc();
						$con->sql_freeresult();
						
						if($tmp['weight_kg']<=0){
							$ret['error'] = $LANG['WORK_ORDER_WEIGHT_ITEM_ONLY'];
							break;
						}
					}
				}
				
				
				if(!$ret['error']){
					// start loop to add sku items
					$items_list = array();
					foreach($params['sid_list'] as $key => $sid){
						// make sure this sku got weight_kg
						//$con->sql_query("select weight_kg from sku_items where id=".mi($sid));
						//$tmp = $con->sql_fetchassoc();
						//$con->sql_freeresult();
						
						// generate item
						$item = $this->generateTempSkuItems($bid, $sid, $params);
						if(!$item)	continue;
						
						// check is scan barcode item
						if(isset($params['qty_list'][$key])){
							// get the qty from barcode
							$item['qty'] = mi($params['qty_list'][$key]);
						}
						
						// insert into temp table
						$params['reloadItem'] = true;
						$item = $this->insertOrReplaceTempItem($bid, $wo_id, $item, $params);
						
						if(!$item)	continue;	// add failed
						
						// add into item list
						$ret['items_list'][$item['id']] = $item;
					}
				}
			}
		}
		
		if(!$ret['error'] && !$ret['items_list']){
			$ret['error'] = $LANG['ADD_ITEM_FAILED'];
		}
		
		// no error
		if(!$ret['error']){
			$ret['ok'] = 1;
		}		
		
		return $ret;
	}
	
	// function to generate temporary items
	// return array $item
	public function generateTempSkuItems($bid, $sid, $params = array()){
		global $appCore, $con, $config;
		
		$bid = mi($bid);
		$sid = mi($sid);
		if(!$bid || !$sid)	return;
		
		if($config['enable_gst']){
			$itemIsInclusiveTaxString = $appCore->gstManager->itemIsInclusiveTaxString;
			$leftJoinOutputGSTString = $appCore->gstManager->leftJoinOutputGSTString;
			$gst_col_select = ", $itemIsInclusiveTaxString as sku_inclusive_tax, output_gst.id as gst_id, output_gst.code as gst_code, output_gst.rate as gst_rate";
		}
		
		$q1 = $con->sql_query($sql = "select si.id as sku_item_id, ifnull(sic.grn_cost,si.cost_price) as cost, ifnull(sip.price,si.selling_price) as selling_price, si.weight_kg, sic.qty as stock_balance $gst_col_select
			from sku_items si
			left join sku on si.sku_id = sku.id
			left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
			left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
			left join category_cache cc on cc.category_id=sku.category_id
			$leftJoinOutputGSTString
			where si.id=$sid");
		$item = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$item)	return;	// item not found
		
		$item['price'] = $item['selling_price'];
		
		// GST
		if($config['enable_gst']){
			if($params['branch_is_under_gst']){
				$item['display_price'] = $item['price'];
				$item['display_price_is_inclusive'] = $item['sku_inclusive_tax']=='yes'? 1 : 0;
				
				if(!$item['gst_id']){	// return invoice is not under gst
					// gst GST OS
					$gstOS = $appCore->gstManager->getGstOS();
					$item['gst_id'] = $gstOS['id'];
					$item['gst_code'] = $gstOS['code'];
					$item['gst_rate'] = $gstOS['rate'];
				}
				
				if($item['display_price_is_inclusive']){	// is inclusive tax
					$gst_amt = $item['display_price'] / ($item['gst_rate']+100) * $item['gst_rate'];
					$item['price'] = $item['display_price'] - $gst_amt;
				}
			}
		}		
		
		return $item;
	}
	
	// function to insert cnote item
	// return array cnote_items
	public function insertOrReplaceTempItem($bid, $wo_id, $item, $params = array()){
		global $appCore, $con, $config;
		
		if(!$bid || !$wo_id)	return;
		
		$upd = array();
		$upd['user_id'] = mi($params['user_id']);
		$upd['edit_time'] = mi($params['edit_time']);
		$upd['branch_id'] = $bid;
		$upd['work_order_id'] = $wo_id;
		$upd['sku_item_id'] = $item['sku_item_id'];
		$upd['cost'] = $item['cost'];
		$upd['price'] = $item['price'];
		$upd['weight_kg'] = mf($item['weight_kg']);
		$upd['stock_balance'] = mf($item['stock_balance']);
		if(isset($item['gst_id']))	$upd['gst_id'] = $item['gst_id'];
		if(isset($item['gst_code']))	$upd['gst_code'] = $item['gst_code'];
		if(isset($item['gst_rate']))	$upd['gst_rate'] = $item['gst_rate'];
		
		if($config['enable_gst'] && $params['branch_is_under_gst']){
			$upd['display_price_is_inclusive'] = mi($item['display_price_is_inclusive']);
		}
		$upd['display_price'] = $item['display_price'];
		
		if($params['action'] == 'in'){	// transfer in
			$tblName = "tmp_work_order_items_in";
			
			$upd['expect_qty'] = mf($item['expect_qty']);
			$upd['expect_cost'] = round($item['expect_cost'], $config['global_cost_decimal_points']);
			$upd['line_total_expect_cost'] = round($item['line_total_expect_cost'], $config['global_cost_decimal_points']);
			$upd['line_total_expect_weight'] = round($item['line_total_expect_weight'], $config['global_cost_decimal_points']);
			
			$upd['actual_qty'] = mf($item['actual_qty']);
			$upd['actual_cost'] = round($item['actual_cost'], $config['global_cost_decimal_points']);
			$upd['line_total_actual_cost'] = round($item['line_total_actual_cost'], $config['global_cost_decimal_points']);
			$upd['line_total_actual_weight'] = round($item['line_total_actual_weight'], $config['global_cost_decimal_points']);
			
			$upd['finish_cost'] = round($item['finish_cost'], $config['global_cost_decimal_points']);
			$upd['line_total_finish_cost'] = round($item['line_total_finish_cost'], $config['global_cost_decimal_points']);
			
			$upd['uom_id'] = mi($item['uom_id']);
			$upd['actual_adj_qty'] = mf($item['actual_adj_qty']);
		}else{	// transfer out
			$tblName = "tmp_work_order_items_out";
			
			if(isset($item['qty']))	$upd['qty'] = $item['qty'];
			
			$upd['line_total_cost'] = mf($item['line_total_cost']);
			$upd['line_exptected_weigth'] = mf($item['line_exptected_weigth']);
			$upd['line_actual_received_weigth'] = mf($item['line_actual_received_weigth']);
			$upd['line_shrinkage_weigth'] = mf($item['line_shrinkage_weigth']);
			$upd['cost_per_weight'] = round($item['cost_per_weight'], $config['global_cost_decimal_points']);
		}
				
		if(!$item['id']){
			$con->sql_query("insert into $tblName ".mysql_insert_by_field($upd));
			$item_id = mi($con->sql_nextid());
		}else{
			$item_id = $upd['id'] = $item['id'];
			$con->sql_query("replace into $tblName ".mysql_insert_by_field($upd));
		}
		
		if($params['reloadItem']){
			// get the inserted item and return
			$items_list = $this->getItems($bid, $wo_id, $item_id, true, $params);
			return $items_list[$item_id];
		}else{
			return $item_id;
		}
	}
	
	// function to get work_order_items_out or work_order_items_in
	// return array $items_list
	public function getItems($bid, $wo_id, $item_id = 0, $isTmp = false, $params = array()){
		global $con, $appCore, $config;
		
		$xtra_join = $xtra_col = '';
		if($params['action'] == 'in'){	// transfer in
			$tblName = "work_order_items_in";
			
			$xtra_join = "left join uom on uom.id=woi.uom_id";
			$xtra_col = ", uom.fraction as uom_fraction";
		}else{	// transfer out
			$tblName = "work_order_items_out";
		}
		
		if($isTmp)	$tblName = 'tmp_'.$tblName;
		
		if(!$bid || !$wo_id)	return;
		
		$filter = array();
		$filter[] = "woi.branch_id=$bid";
		$filter[] = "woi.work_order_id=$wo_id";
		if($item_id>0)	$filter[] = "woi.id=$item_id";
		if($isTmp && $params['edit_time'])	$filter[] = "woi.edit_time=".mi($params['edit_time']);
		
		$filter = "where ".join(' and ', $filter);
		
		$items_list = array();
		
		// select the inserted object
		$q1 = $con->sql_query("select woi.*, si.mcode,si.sku_item_code,si.artno, si.description, si.weight_kg, si.doc_allow_decimal, 
			master_uom.code as master_uom_code $xtra_col
			from $tblName woi
			join sku_items si on si.id=woi.sku_item_id
			left join sku on sku.id=si.sku_id
			left join uom master_uom on master_uom.id=si.packing_uom_id
			$xtra_join
			$filter
			order by woi.id");

		while($r = $con->sql_fetchassoc($q1)){
			if($params['action'] == 'in'){	// transfer in
				if($r['actual_qty']>0){					
					// GP
					$r['line_total_finish_gp'] = round($r['price'] - $r['finish_cost'], $config['global_cost_decimal_points']);
					
					// GP Percent
					$r['line_total_finish_gp_per'] = 0;
					if($r['price'])	$r['line_total_finish_gp_per'] = round($r['line_total_finish_gp'] / $r['price'] * 100, 2);
				}
				
			}else{	// transfer out
				
			}
			
			$items_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		return $items_list;
	}
	
	// function to delete tmp item
	// return array $ret
	public function deleteTempItem($bid, $wo_id, $params){
		global $con;
		
		if(!$bid || !$wo_id)	return array('err' => 'Invalid ID');
		
		$filter = array();
		$filter[] = "branch_id=$bid";
		$filter[] = "work_order_id=$wo_id";
		$filter[] = "id=".mi($params['item_id']);
		$filter[] = "edit_time=".mi($params['edit_time']);
		if($params['user_id'])	$filter[] = "user_id=".mi($params['user_id']);
		
		$filter = join(' and ', $filter);
		
		if($params['action'] == 'in'){	// transfer in
			$tblName = "tmp_work_order_items_in";
			
		}else{	// transfer out
			$tblName = "tmp_work_order_items_out";
		}
		
		$con->sql_query("delete from $tblName where $filter");
		$affected_count = $con->sql_affectedrows();
		
		if($affected_count > 0)	return array('ok'=>1);
		return array('err'=>'Delete Failed');
	}
	
	// function to check whether this CN is now allow to edit
	// return array $ret
	public function isWorkOrderAllowToEdit($bid, $wo_id, $params = array()){
		global $con, $LANG;
		
		$bid = mi($bid);
		$wo_id = mi($wo_id);
		
		if(!$bid || !$wo_id)	return array('err' => $LANG['WORK_ORDER_INVALID_FORM']);
		
		$ret = array();

		// cannot edit other branch
		if($params['branch_id'] != $bid){
			$ret['err'] = sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], $this->moduleName);
		}
		
		// check whether still can edit
		if(!is_new_id($wo_id)){
			$con->sql_query("select wo.* from work_order wo where wo.branch_id=$bid and wo.id=$wo_id");
			$form = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($form['active'] != 1){
				$ret['err'] = $LANG['WORK_ORDER_NOT_ALLOW_TO_EDIT'];
			}
			
			if($params['action'] == 'out'){
				if(!($form['active'] == 1 && $form['status'] == 0 && $form['completed'] == 0)){
					$ret['err'] = $LANG['WORK_ORDER_CANT_SAVE_DUE_TO_MODIFIED'];
				}
			}elseif($params['action'] == 'in'){
				if(!($form['active'] == 1 && $form['status'] == 1 && $form['completed'] == 0)){
					$ret['err'] = $LANG['WORK_ORDER_CANT_SAVE_DUE_TO_MODIFIED'];
				}
			}
		}
		
		return $ret;
	}
	
	// function to validate data before save
	// return array $ret
	public function validateData($form, $items_list, $params = array()){
		global $appCore, $LANG;
		
		if(!$form)	return array('err' => $LANG['WORK_ORDER_INVALID_FORM']);
		
		// Check Items
		if($params['action'] == 'in'){	// transfer in
			if(!$items_list[$params['action']])	return array('err' => $LANG['WORK_ORDER_NEED_AT_LEAST_ONE_IN_ITEM']);
		}else{	// transfer out
			if(!$items_list[$params['action']])	return array('err' => $LANG['WORK_ORDER_NEED_AT_LEAST_ONE_OUT_ITEM']);
		}
		
		// Adjustment date
		if(!$form['adj_date'] || !$appCore->isValidDateFormat($form['adj_date']))	return array('err' => $LANG['WORK_ORDER_INVALID_DATE']);
				
		return array('ok' => 1);
	}
	
	// function to save work order
	// return array $ret
	public function saveWorkOrder($form, $items_list, $params = array()){
		global $con, $appCore, $LANG, $config;
		
		$action = trim($params['action']);
		
		// check can edit or not
		$checkParams = array();
		$checkParams['branch_id'] = $params['branch_id'];
		$checkParams['user_id'] = $params['user_id'];
		$checkParams['action'] = $action;
		$checkResult = $this->isWorkOrderAllowToEdit($form['branch_id'], $form['id'], $checkParams);
		if($checkResult['err'])	return $checkResult;
		
		// validate
		$checkResult = $this->validateData($form, $items_list, $params);
		
		// got error 
		if($checkResult['err'])	return $checkResult;
		
		// save into tmp item
		$this->saveTempItems($params['user_id'], $form, $items_list, $params);
				
		// save work order
		$form['active'] = 1;
		if($action == 'in'){
			$form['status'] = 1;
			$form['completed'] = 0;
			$form['in_transfer_updated'] = 1;
			
			// check confirm
			if($params['is_confirm']){
				// check anything when confirm?
				$form['completed'] = 1;
			}
		}else{
			$form['status'] = 0;
			$form['completed'] = 0;
			$form['in_transfer_updated'] = 0;
			
			// check confirm
			if($params['is_confirm']){
				// check anything when confirm?
				$form['status'] = 1;
			}
		}
		
		// insert or update work order
		$wo_id = $this->insertOrUpdateWorkOrder($form, $params);
		
		// save items
		$first_item_id = 0;
		foreach($items_list[$action] as $item){
			// insert item
			
			$item_id = $this->insertItem($form['branch_id'], $wo_id, $item, $form, $params);
			if(!$first_item_id)	$first_item_id = $item_id;
		}
		
		if($action == 'in'){
			$tblName = 'work_order_items_in';
		}else{
			$tblName = 'work_order_items_out';
		}
		
		if($first_item_id>0){
			// delete old item
			$con->sql_query("delete from $tblName where branch_id=".mi($form['branch_id'])." and id<$first_item_id and work_order_id=$wo_id");
		}
		
		// delete tmp items
		$con->sql_query("delete from tmp_".$tblName." where branch_id=".mi($form['branch_id'])." and work_order_id=$form[id] and edit_time=$form[edit_time]");
		
		/////////// todo: recalculate all amount ///////////
		// recalculate all amount
		//$this->recalculateWorkOrder($form['branch_id'], $wo_id);
		///////////
		
		// is confirm
		if($params['is_confirm']){
			if($form['status'] == 1){
				/////// action to do when confirmed and send to transfer in ///////////
								
				///////////
			}
			
			if($form['completed'] == 1){
				/////// action to do when confirmed and mark as completed ///////////
				
				////////////
			}
			
			log_br($params['user_id'], $this->moduleName, $wo_id, $this->moduleName." ID#$wo_id Confirm ($action)");
		}else{
			log_br($params['user_id'], $this->moduleName, $wo_id, $this->moduleName." ID#$wo_id saved ($action)");
			$params['is_save'] = 1;
		}
		
		// generate adjustment for in item and out item
		$this->updateAdjustment($form['branch_id'], $wo_id, $params);
		
		// Send Notify Users
		if($params['is_confirm']){
			$this->sendNotifications($form['branch_id'], $wo_id, $params);
		}
		
			
		// return ok
		$ret = array();
		$ret['ok'] = 1;
		$ret['wo_id'] = $wo_id;
		$ret['branch_id'] = $form['branch_id'];
		if($form['status'] == 1)	$ret['confirm'] = 1;
		if($form['completed'] == 1)	$ret['approve'] = 1;
		
		return $ret;
	}
	
	// function to save all tmp items
	// return null
	public function saveTempItems($user_id, $form, $items_list, $params = array()){
		global $con;
		
		$action = $params['action'];
		if(!$items_list[$action])	return;
		
		$form['user_id'] = mi($user_id);
		
		// loop all items
		foreach($items_list[$action] as $item){
			$this->insertOrReplaceTempItem($form['branch_id'], $form['id'], $item, $form);
		}
	}
	
	// function to insert work order items
	// return item_id
	public function insertItem($bid, $wo_id, $item, $params = array()){
		global $appCore, $con, $config;
		
		$action = trim($params['action']);
		
		if(!$bid || !$wo_id)	return;
		
		$upd = array();
		$upd['branch_id'] = $bid;
		$upd['work_order_id'] = $wo_id;
		$upd['sku_item_id'] = $item['sku_item_id'];
		$upd['cost'] = $item['cost'];
		$upd['price'] = $item['price'];
		$upd['weight_kg'] = $item['weight_kg'];
		$upd['stock_balance'] = $item['stock_balance'];
		
		if(isset($item['gst_id']))	$upd['gst_id'] = $item['gst_id'];
		if(isset($item['gst_code']))	$upd['gst_code'] = $item['gst_code'];
		if(isset($item['gst_rate']))	$upd['gst_rate'] = $item['gst_rate'];
		
		$upd['display_price_is_inclusive'] = mi($item['display_price_is_inclusive']);
		$upd['display_price'] = $item['display_price'];
		
		if($action == 'in'){
			$tblName = 'work_order_items_in';
			
			$upd['expect_qty'] = $item['expect_qty'];
			$upd['expect_cost'] = round($item['expect_cost'], $config['global_cost_decimal_points']);
			$upd['line_total_expect_cost'] = round($item['line_total_expect_cost'], $config['global_cost_decimal_points']);
			$upd['line_total_expect_weight'] = $item['line_total_expect_weight'];
			
			$upd['actual_qty'] = $item['actual_qty'];
			$upd['actual_cost'] = round($item['actual_cost'], $config['global_cost_decimal_points']);
			$upd['line_total_actual_cost'] = round($item['line_total_actual_cost'], $config['global_cost_decimal_points']);
			$upd['line_total_actual_weight'] = $item['line_total_actual_weight'];
			$upd['finish_cost'] = round($item['finish_cost'], $config['global_cost_decimal_points']);
			$upd['line_total_finish_cost'] = round($item['line_total_finish_cost'], $config['global_cost_decimal_points']);
			
			$upd['uom_id'] = mi($item['uom_id']);
			$upd['actual_adj_qty'] = mf($item['actual_adj_qty']);
		}else{
			$tblName = 'work_order_items_out';
			
			$upd['qty'] = $item['qty'];
			$upd['line_total_cost'] = round($item['line_total_cost'], $config['global_cost_decimal_points']);
			$upd['line_exptected_weigth'] = $item['line_exptected_weigth'];
			$upd['line_actual_received_weigth'] = $item['line_actual_received_weigth'];
			$upd['line_shrinkage_weigth'] = $item['line_shrinkage_weigth'];
			$upd['cost_per_weight'] = round($item['cost_per_weight'], $config['global_cost_decimal_points']);
		}
		
		$con->sql_query("insert into $tblName ".mysql_insert_by_field($upd));
		$item_id = mi($con->sql_nextid());
		
		return $item_id;
	}
	
	// function to load work order
	// return array $data
	public function loadWorkOrder($bid, $wo_id, $params = array()){
		global $con, $appCore;
		
		$bid = mi($bid);
		$wo_id = mi($wo_id);
		$isEdit = mi($params['isEdit']);
		$loadItems = mi($params['loadItems']);
		
		if(!$bid || !$wo_id)	return false;
		
		$form = array();
		
		// load header
		$con->sql_query("select wo.*, user.u as owner_username, b.code as branch_code
			from work_order wo
			left join user on user.id=wo.user_id
			left join branch b on b.id=wo.branch_id
			where wo.branch_id=$bid and wo.id=$wo_id");
		$form = $con->sql_fetchassoc();
		$form['notify_users_in'] = unserialize($form['notify_users_in']);
		$con->sql_freeresult();
		
		if($form['notify_users_in']){
			$con->sql_query("select u.id, u.u
			from user u
			where u.id in (".join(',', $form['notify_users_in']).")");
			while($r = $con->sql_fetchassoc()){
				$form['notify_users_in_obj'][$r['id']] = $r;
			}
			$con->sql_freeresult();
			
		}
				
		if($loadItems){
			// load items
			$itemsList['out'] = $this->getItems($bid, $wo_id, 0, false, array('action'=>'out'));
			$itemsList['in'] = $this->getItems($bid, $wo_id, 0, false, array('action'=>'in'));
			//print_r($itemsList);
		}
			
		if($isEdit){
			$form['edit_time'] = $appCore->generateEditTime();
			
			if($loadItems){				
				// insert into tmp
				$itemParams = array();
				$itemParams['user_id'] = $params['user_id'];
				$itemParams['edit_time'] = $form['edit_time'];
				$itemParams['return_type'] = $form['return_type'];
				$itemParams['branch_is_under_gst'] = $form['branch_is_under_gst'];
				foreach($itemsList as $action => $tmpItemList){
					$itemParams['action'] = $action;
					foreach($tmpItemList as $item){
						unset($item['id']);
						$this->insertOrReplaceTempItem($bid, $wo_id, $item, $itemParams);
					}
					// load back from tmp
					$itemsList[$action] = $this->getItems($bid, $wo_id, 0, true, $itemParams);
				}
				
				
				
				//print_r($itemsList);
			}
		}
		
		if($loadItems){
			$data = array();
			$data['header'] = $form;
			$data['items_list'] = $itemsList;
			
			return $data;
		}else{
			return $form;
		}
	}
	
	// function to delete Work Order
	// return array $ret
	public function deleteWorkOrder($bid, $wo_id, $params = array()){
		global $con;
		
		$bid = mi($bid);
		$wo_id = mi($wo_id);
		
		// check can edit or not
		$checkResult = $this->isWorkOrderAllowToEdit($bid, $wo_id, $params);
		if($checkResult['err'])	return $checkResult;
		
		$upd = array();
		$upd['deleted_by'] = $params['user_id'];
		$upd['deleted_reason'] = trim($params['deleted_reason']);
		$upd['active'] = 0;
		$upd['status'] = 0;
		$upd['completed'] = 0;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("update work_order set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$wo_id");
		
		//cancel adjustment
		$this->updateAdjustment($bid, $wo_id, $params);
		
		log_br($params['user_id'], $this->moduleName, $wo_id, $this->moduleName." ID#$wo_id Deleted");
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['branch_id'] = $bid;
		$ret['wo_id'] = $wo_id;
		return $ret;
	}
	
	// function to update adjustment
	// return boolean
	public function updateAdjustment($bid, $wo_id, $params = array()){
		global $con, $config, $appCore;
		
		$bid = mi($bid);
		$wo_id = mi($wo_id);
		
		if(!$bid || !$wo_id)	return false;
		
		// get data
		$data = $this->loadWorkOrder($bid, $wo_id, array('loadItems'=>1));
		$adj_id = mi($data['header']['adj_id']);
		$transfer_type = trim($data['header']['transfer_type']);
		
		if($adj_id){
			$adj_upd = array();
			$adj_upd['last_update'] = 'CURRENT_TIMESTAMP';
			$adj_upd['dept_id'] = mi($data['header']['dept_id']);
			$adj_upd['adjustment_date'] = $data['header']['adj_date'];
			if(!$data['header']['active']){	// Work Order Deleted
				$adj_upd['status'] = 5;
				$adj_upd['cancelled_by'] = mi($params['user_id']);
				$adj_upd['cancelled'] = 'CURRENT_TIMESTAMP';
				$adj_upd['reason'] = $this->moduleName." Deleted";				
			}else{
				$adj_upd['active'] = 1;
				$adj_upd['cancelled_by'] = 0;
				if($data['header']['status'] >= 1){
					$adj_upd['status'] = 1;
					$adj_upd['approved'] = 1;
				}else{
					$adj_upd['status'] = 0;
					$adj_upd['approved'] = 0;
				}
			}
			
			if($adj_upd){	
				$con->sql_query("update adjustment set ".mysql_update_by_field($adj_upd)." where branch_id=$bid and id=".mi($adj_id));
			}
		}
		
		// no items
		if(!$data['items_list']['in'] && !$data['items_list']['out'])	return false;
		
		$first_item_id = 0;
		// work order still active
		if($data['header']['active']){
			// only generate adjustment and adjustment_items when it is confirm
			// need to update adjustment items also when is reset
			if($params['is_confirm'] || $params['is_reset']){
				// no adjustment?
				if(!$adj_id && $params['is_confirm']){
					// need to create adjustment
					$adj = array();
					$adj['id'] =  $appCore->generateNewID("adjustment","branch_id=".mi($bid));
					$adj['branch_id'] = $bid;
					$adj['user_id'] = $data['header']['user_id'];
					$adj['dept_id'] = $data['header']['dept_id'];
					$adj['adjustment_date'] = $data['header']['adj_date'];
					$adj['adjustment_type'] = $this->adj_type;
					$adj['remark'] = 'Created from '.$this->moduleName;
					$adj['active'] = 1;
					$adj['status'] = $adj['approved'] = 1;
					$adj['added'] = $adj['last_update'] = 'CURRENT_TIMESTAMP';
					$adj['module_type'] = $this->adj_module_type;
					
					$con->sql_query("insert into adjustment ".mysql_insert_by_field($adj));
					$adj_id = $adj['id'];
					
					$con->sql_query("update work_order set adj_id=$adj_id where branch_id=$bid and id=$wo_id");
				}
				
				if($adj_id){
					if($data['header']['status'] == 1){
						// insert item for out
						foreach($data['items_list']['out'] as $r){
							/////// insert item for out ////////////
							$upd = array();
							$upd['id'] = $appCore->generateNewID("adjustment_items","branch_id=".mi($bid));
							$upd['branch_id'] = $bid;
							$upd['adjustment_id'] = $adj_id;
							$upd['user_id'] = $data['header']['user_id'];
							$upd['sku_item_id'] = $r['sku_item_id'];
							$upd['qty'] = $r['qty']*-1;
							$upd['cost'] = $r['cost'];
							$upd['selling_price'] = $r['display_price'];
							$upd['stock_balance'] = $r['stock_balance'];
							
							$con->sql_query("insert into adjustment_items ".mysql_insert_by_field($upd));
							$item_id = $upd['id'];
							if(!$first_item_id)	$first_item_id = $item_id;
						}
					}
					
					if($data['header']['completed'] == 1){
						// insert item for in
						if($data['header']['completed'] == 1 && $data['items_list']['in']){
							foreach($data['items_list']['in'] as $r){
								/////// insert item for in ////////////
								$upd = array();
								$upd['id'] = $appCore->generateNewID("adjustment_items","branch_id=".mi($bid));
								$upd['branch_id'] = $bid;
								$upd['adjustment_id'] = $adj_id;
								$upd['user_id'] = $data['header']['user_id'];
								$upd['sku_item_id'] = $r['sku_item_id'];
								$upd['qty'] = $transfer_type == 'w2p' ? $r['actual_adj_qty'] : $r['actual_qty'];
								$upd['cost'] = $r['cost'];
								$upd['selling_price'] = $r['display_price'];
								$upd['stock_balance'] = $r['stock_balance'];
								
								$con->sql_query("insert into adjustment_items ".mysql_insert_by_field($upd));
								$item_id = $upd['id'];
								if(!$first_item_id)	$first_item_id = $item_id;
								/////////
							}
						}
					}
				}
			}
		}
		
		if($adj_id){
			// get all sku from adjustment
			$sid_list = array();
			$con->sql_query("select distinct(sku_item_id) as sid from adjustment_items where branch_id=$bid and adjustment_id=$adj_id");
			while($r = $con->sql_fetchassoc()){
				$sid_list[] = mi($r['sid']);
			}
			$con->sql_freeresult();
			
			if($first_item_id>0){
				$con->sql_query("delete from adjustment_items where branch_id=$bid and adjustment_id=$adj_id and id<$first_item_id");
			}
			
			$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (".join(',', $sid_list).")");
		}
		
		
		return true;
	}
	
	// function to reset cnote
	// return array $ret
	public function resetWorkOrder($bid, $wo_id, $params = array()){
		global $con, $config, $appCore, $LANG;
		
		// invalid id
		$bid = mi($bid);
		$wo_id = mi($wo_id);
		$to_action = trim($params['to_action']);
		$params['is_reset'] = 1;
		
		if(!$bid || !$wo_id)	return array('err' => $LANG['WORK_ORDER_INVALID_FORM']);
		if($to_action != 'out' && $to_action != 'in')	return array('err' => $LANG['WORK_ORDER_INVALID_RESET_ACTION']);
		
		// form cant load
		$form = $this->loadWorkOrder($bid, $wo_id);
		
		if(!$form)	return array('err' => $LANG['WORK_ORDER_INVALID_FORM']);
		
		// check user
		if(!$appCore->userManager->isUserAllowToResetDocument($params['user_id'])){
			return array('err' => $LANG['USER_LEVEL_NO_REACH']);
		}
		
		//add reset config
		$check_date = strtotime($form['adj_date']);

		if (isset($config['reset_date_limit']) && $config['reset_date_limit'] >= 0){
			$reset_limit = $config['reset_date_limit'];
			$reset_limit = strtotime("-1 day",strtotime("-$reset_limit day" , strtotime("now")));

			if ($check_date<$reset_limit){
				return array('err' => $LANG['WORK_ORDER_DATE_RESET_LIMIT']);
			}
		}
		
		$adj_id = mi($form['adj_id']);
		
		$upd = array();
		if($to_action == 'out'){
			$upd['status'] = 0;
			$upd['completed'] = 0;
			$upd['in_transfer_updated'] = 0;
		}else{
			$upd['status'] = 1;
			$upd['completed'] = 0;
		}
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update work_order set ".mysql_update_by_field($upd)." where id=$wo_id and branch_id=$bid");
		
		// update adjustment
		$this->updateAdjustment($bid, $wo_id, $params);
		
		log_br($params['user_id'], $this->moduleName, $wo_id, $this->moduleName." Reset ($form[wo_no]) to Transfer ".ucwords($to_action));

		// Send Notification
		$this->sendNotifications($bid, $wo_id, $params);
		
		$ret = array();
		$ret['ok'] = 1;
		
		return $ret;
	}
	
	// function to load allowed transfer in/out users list
	// return array $userList
	public function loadAllowedTransferUsers($bid, $action){
		global $con;
		$bid = mi($bid);
		if($bid<=0)	return;
		
		$privilege_code = '';
		if($action == 'in')	$privilege_code = 'ADJ_WORK_ORDER_IN';
		elseif($action == 'out')	$privilege_code = 'ADJ_WORK_ORDER_OUT';
		else return;
		
		$userList = array();
		$con->sql_query("select u.id, u.u
		from user u
		join user_privilege up on up.branch_id=$bid and up.user_id=u.id and up.privilege_code=".ms($privilege_code)." and up.allowed=1
		where u.active=1");
		while($r = $con->sql_fetchassoc()){
			$userList[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		return $userList;
	}
	
	// function to send notification to users
	// return null
	public function sendNotifications($bid, $wo_id, $params = array()){
		global $con, $appCore, $config;
		
		$bid = mi($bid);
		$wo_id = mi($wo_id);
		$to = array();
		
		// load Work Order
		$wo = $this->loadWorkOrder($bid, $wo_id);
		
		$notify_owner = false;
		$notify_approval_users = false;
		$notify_transfer_in_users = false;
		
		$url = "work_order.php?a=view&id=$wo_id&branch_id=$bid";
		
		if($params['is_reset']){	// is reset
			$notify_owner = true;
			$notify_approval_users = true;
			
			if($wo['status'] == 1){	// at transfer in status
				$notify_transfer_in_users = true;
			}
			
			$title = $this->moduleName." $wo[wo_no] (ID#$wo_id) Reset";
		}else{
			if($wo['completed']){	// completed
				$notify_owner = true;
				$notify_approval_users = true;
			
				$title = $this->moduleName." $wo[wo_no] (ID#$wo_id) Completed";
			}elseif($wo['status'] == 1){	// transfer in
				$notify_transfer_in_users = true;
				
				$title = $this->moduleName." $wo[wo_no] (ID#$wo_id) Sent to Transfer In";
			}
		}
		
		if($notify_owner || $notify_approval_users){
			// get adjustment approval flow 
			$con->sql_query("select notify_users, approval_settings from approval_flow where type='adjustment' and branch_id=".mi($bid)." order by id");
			$approval_flow = $con->sql_fetchassoc();
			$approval_flow['approval_settings'] = unserialize($approval_flow['approval_settings']);
			$con->sql_freeresult();
			
			if($notify_owner){
				// Check Owner
				if($wo['user_id'] != $params['user_id']){	// skip owner if sender is owner
					if($approval_flow['approval_settings']['owner']){
						$tmp = array();
						$tmp['user_id'] = $wo['user_id'];
						$tmp['approval_settings'] = $approval_flow['approval_settings']['owner'];
						$tmp['type'] = 'owner';
						$to[$wo['user_id']] = $tmp;
					}
				}
			}
			
			if($notify_approval_users){
				// Check Notify Users
				if($approval_flow['notify_users']){
					$notify_users = trim($approval_flow['notify_users']);
					if($params['user_id'])	$notify_users = str_replace("|$params[user_id]|", "|", $notify_users); // don't send to self
					$notify_users = preg_split("/\|/", $notify_users);
					
					if($notify_users){
						foreach($notify_users as $tmp_user_id){
							if($tmp_user_id){
								$tmp = array();
								$tmp['user_id'] = $tmp_user_id;
								$tmp['approval_settings'] = $approval_flow['approval_settings']['notify'][$tmp_user_id];
								$tmp['type'] = 'notify';
								$to[$tmp_user_id] = $tmp;
							}
						}
					}
				}
			}
		}
		
		if($notify_transfer_in_users){
			// Send pm to notify users
			if($wo['notify_users_in'] && is_array($wo['notify_users_in'])){
				foreach($wo['notify_users_in'] as $tmp_user_id){
					$to[$tmp_user_id] = array('user_id'=>$tmp_user_id, 'approval_settings'=>array('pm'=>1), 'type'=>'notify');
				}
			}
		}
		
		if($to){
			send_pm2($to, $title, $url);
		}
	}
	
	//////////////////// private function ///////////////////////
	// function to insert or update cnote
	// return int $wo_id
	private function insertOrUpdateWorkOrder($form, $params = array()){
		global $con, $appCore, $config;
		
		if(!$form)	return false;
		
		$upd = array();
		$upd['adj_date'] = trim($form['adj_date']);
		$upd['dept_id'] = mi($form['dept_id']);
		$upd['remark'] = trim($form['remark']);
		$upd['branch_is_under_gst'] = mf($form['branch_is_under_gst']);
		$upd['in_transfer_updated'] = mi($form['in_transfer_updated']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		if($params['action'] == 'in'){
			$upd['in_total_expect_qty'] = mf($form['in_total_expect_qty']);
			$upd['in_total_expect_cost'] = round($form['in_total_expect_cost'], $config['global_cost_decimal_points']);
			$upd['in_total_expect_weight'] = mf($form['in_total_expect_weight']);
			$upd['in_total_actual_qty'] = mf($form['in_total_actual_qty']);
			$upd['in_total_actual_cost'] = round($form['in_total_actual_cost'], $config['global_cost_decimal_points']);
			$upd['in_total_actual_weight'] = mf($form['in_total_actual_weight']);
			
			$upd['expect_cost_per_kg'] = round($form['expect_cost_per_kg'], $config['global_cost_decimal_points']);
			$upd['actual_cost_per_kg'] = round($form['actual_cost_per_kg'], $config['global_cost_decimal_points']);
			
			$upd['expect_shrinkage_weight'] = mf($form['expect_shrinkage_weight']);
			$upd['shrinkage_weight'] = mf($form['shrinkage_weight']);
			$upd['labour_cost'] = round($form['labour_cost'], $config['global_cost_decimal_points']);
			$upd['packaging_cost'] = round($form['packaging_cost'], $config['global_cost_decimal_points']);
			$upd['total_cost'] = round($form['total_cost'], $config['global_cost_decimal_points']);
			$upd['final_cost_per_kg'] = round($form['final_cost_per_kg'], $config['global_cost_decimal_points']);
			$upd['final_cost_per_qty'] = round($form['final_cost_per_qty'], $config['global_cost_decimal_points']);
		}else{
			$upd['transfer_type'] = trim($form['transfer_type']);
			if(!$upd['transfer_type'])	$upd['transfer_type'] = 'w2w';	// Default = Weight to Weight
			
			$upd['out_total_qty'] = mf($form['out_total_qty']);
			$upd['out_total_weight'] = mf($form['out_total_weight']);
			$upd['out_total_cost'] = round($form['out_total_cost'], $config['global_cost_decimal_points']);
			$upd['out_actual_received_weight'] = mf($form['out_actual_received_weight']);
			$upd['out_shrinkage_weight'] = mf($form['out_shrinkage_weight']);
			$upd['out_actual_cost_per_kg'] = round($form['out_actual_cost_per_kg'], $config['global_cost_decimal_points']);
			
			$upd['notify_users_in'] = serialize($form['notify_users_in']);
		}
		
		
		if(isset($form['active']))	$upd['active'] = $form['active'];
		if(isset($form['status']))	$upd['status'] = $form['status'];
		if(isset($form['completed']))	$upd['completed'] = $form['completed'];
		
		if(is_new_id($form['id'])){
			// insert
			$upd['branch_id'] = mi($form['branch_id']);
			$upd['user_id'] = mi($params['user_id']);
			$upd['active'] = 1;
			if(!isset($upd['status']))	$upd['status'] = 0;
			if(!isset($upd['completed']))	$upd['completed'] = 0;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into work_order ".mysql_insert_by_field($upd));
			$wo_id = $con->sql_nextid();
			
			// get branch info
			$branchInfo = $appCore->branchManager->getBranchInfo($upd['branch_id']);
			$upd2 = array();
			$upd2['wo_no'] = $branchInfo['report_prefix'].sprintf("%05d", $wo_id);
			
			// update back cnote
			$con->sql_query("update work_order set ".mysql_update_by_field($upd2)." where branch_id=".mi($upd['branch_id'])." and id=$wo_id");
		}else{
			// update
			$wo_id = mi($form['id']);
			$con->sql_query("update work_order set ".mysql_update_by_field($upd)." where branch_id=".mi($form['branch_id'])." and id=$wo_id");
		}
		
		return $wo_id;
	}
}
?>