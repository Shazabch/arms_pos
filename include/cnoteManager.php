<?php
/*
10/22/2015 9:55 AM Andy
- Enhanced to have branch filter on load listing.

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

5/29/2017 16:50 Qiu Ying
- Enhanced to return multiple invoice

6/20/2017 13:33 Qiu Ying
- Bug fixed on viewing wrong credit note adjustment when in HQ

2017-08-21 11:17 AM Qiu Ying
- Enhanced to load reason list from config
- Enhanced to load the item price, uom and gst code from DO invoice when add item

8/22/2017 1:24 PM Andy
- Change to use GST Code 'OS' if return invoice have no gst.

4/27/2018 11:31 AM Justin
- Bug fixed on loading Credit Note listing which will not exactly extract 15 C/N per page.

3/20/2019 11:03 AM Justin
- Enhanced the Credit Note printing to have last page row setup base on config if got set.

11/21/2019 10:16 AM Andy
- Fixed deleted credit note unable to show in cancelled tab.

1/9/2020 10:15 AM William
- Enhanced to insert id manually for adjustment tables that uses auto increment.

4/16/2020 11:18 AM William
- Enhanced to block reset and confirm when got config "monthly_closing" and document date has closed.
- Enhanced to block create/save when got config "monthly_closing_block_document_action" and document date has closed.

10/27/2020 10:12 AM Andy
- Fixed Credit Note Multiple Invoice Return Type cannot add item when no GST.
*/
class cnoteManager{
	// common variable
	public $moduleName = "Credit Note";
	public $itemReasonList = array('DAMAGED','EXPIRED');
	public $approvalTypeName = "CN";
	
	
	// private variable
	private $listSize = 15;
	private $printItemPerPage = 15;
	
	function __construct(){
		global $smarty, $con, $appCore;

		
	}

	// function to load cnote list
	// return array data
	public function loadCNoteListing($params = array()){
		global $con;

		$type = trim($params['type']);
		$p = mi($_REQUEST['p']);	// page
		$bid = mi($params['branch_id']);
		
		$size = $this->listSize;
		$start = $p*$size;
		
		$data = array();
		$filter = array();
		if($bid)	$filter[] = "cn.branch_id=$bid";
		
		switch($type){
			case 'saved':
				$filter[] = "cn.active=1 and cn.status=0 and cn.approved=0";
				break;
			case 'waiting_approval':
				$filter[] = "cn.active=1 and cn.status=1 and cn.approved=0";
				break;
			case 'rejected':
				$filter[] = "cn.active=1 and cn.status=2 and cn.approved=0";
				break;
			case 'cancelled':
				$filter[] = "cn.active=0 or (cn.status=5 and cn.approved=0)";
				break;
			case 'approved':
				$filter[] = "cn.active=1 and cn.status=1 and cn.approved=1";
				break;
			case 'search':
				//$filter[] = "cn.active=1";
				$str = trim($params['search_str']);
				$filter[] = "(cn.id=".mi($str)." or cn.cn_no like ".ms('%'.$str.'%')." or cn.inv_no like ".ms('%'.$str.'%')." or cn.cust_name like ".ms('%'.$str.'%')." or cn.cust_brn like ".ms('%'.$str.'%').")";
				break;
			default:
				return;
		}
		
		$filter = "where ".join(' and ', $filter);
		
		$con->sql_query("select count(*) 
			from cnote cn
			$filter");
		$total_rows = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		
		if($start>=$total_rows){
			$start = 0;
			$_REQUEST['p'] = 0;
		}
		$limit = "limit $start, $size";
		$order = "order by cn.last_update desc";

		$total_page = ceil($total_rows/$size);
		
		$data['cnList'] = array();
		
		$q1 = $con->sql_query($sql = "select cn.*, user.u as created_u, bah.approvals, bah.approval_order_id
			from cnote cn
			left join user on user.id=cn.user_id
			left join branch_approval_history bah on bah.id = cn.approval_history_id and bah.branch_id = cn.branch_id
			$filter $order $limit");
		//print $sql;
		while($r = $con->sql_fetchassoc($q1)){
			$r['adj_id_list'] = unserialize($r['adj_id_list']);
			$key = $r['branch_id'].'_'.$r['id'];
			$data['cnList'][$key] = $r;
		}
		$con->sql_freeresult($q1);

		$data['total_page'] = $total_page;
		
		$q1 = $con->sql_query("select ci.return_inv_no,  ci.return_inv_date, ci.cnote_id
			from cnote cn
			left join cnote_items ci on cn.branch_id = ci.branch_id and cn.id = ci.cnote_id
			$filter and cn.return_type = 'multiple_inv'
			group by ci.cnote_id, ci.return_inv_no, ci.return_inv_date
			order by ci.cnote_id, ci.return_inv_no, ci.return_inv_date");
		
		while($r = $con->sql_fetchassoc($q1)){
			$data['cnItemList'][$r["cnote_id"]][] = $r["return_inv_no"] . " (" . $r["return_inv_date"] . ")";
		}
		$con->sql_freeresult($q1);
		
		return $data;
	}

	// function to generate temporary new cn
	// return array $data
	public function generateTempNewCN($params = array()){
		global $con, $appCore;

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
		$data['header']['user_id'] = $user_id;
		$data['header']['id'] = $appCore->generateTempID();
		$data['header']['edit_time'] = $appCore->generateEditTime();
		$data['header']['active'] = 1;
		$data['header']['cn_date'] = date("Y-m-d");
		
		// check gst
		$data['header']['is_under_gst'] = $this->checkCnGstStatus($data['header']);

		if($data['err']){
			return $data;
		}

		return $data;
	}

	// function to check whether this CN got gst
	// return boolean
	public function checkCnGstStatus($cn){
		if(!$cn)	return false;

		$params = array();
		$params['branch_id'] = $cn['branch_id'];
		$params['date'] = $cn['cn_date'];
		return check_gst_status($params);
	}
	
	// function to add temporary cnote items
	// return array $ret
	public function addTempCNoteItems($params){
		global $con, $LANG, $appCore;
		
		$bid = mi($params['branch_id']);
		$cn_id = mi($params['cn_id']);
		
		$ret = array();
		
		if(!$bid){
			$ret['error'] = "Invalid Branch ID";
			return $ret;
		}
		if(!$cn_id){
			$ret['error'] = "Invalid CN ID";
			return $ret;
		}

		$sid_list = array();
		if($params['grn_barcode']){
			// scan barcode
			$barcode_sku_info = get_grn_barcode_info($params['grn_barcode']);
			if(!$barcode_sku_info)	$ret['error'] = "Scan barcode error.";
			elseif($barcode_sku_info['err'])	{
				$ret['error'] = $barcode_sku_info['err'];
			}else{
				$sid_list[] = $barcode_sku_info['sku_item_id'];
			}
		}else{
			// normal search
			$sid_list = $params['sid_list'];
		}
		//print_r($params);
		
		if(!$ret['error']){
			if(!$sid_list)	$ret['error'] = $LANG['ITEM_NOT_FOUND'];
			else{
				// start loop sku items
				$items_list = array();
				foreach($sid_list as $sid){
					// generate item
					$item = $this->generateTempCNoteSkuItems($bid, $sid, $params);
					if(!$item)	continue;
					
					// check is scan barcode item
					if($barcode_sku_info && $sid == $barcode_sku_info['sku_item_id']){
						// get the qty from barcode
						$item['pcs'] = $barcode_sku_info['qty_pcs'];
					}
					
					// insert into temp table
					$params['reloadItem'] = true;
					$item = $this->insertOrReplaceTempCNoteItem($bid, $cn_id, $item, $params);
					
					if(!$item)	continue;	// add failed
					
					// add into item list
					$ret['items_list'][$item['id']] = $item;
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
	
	// function to generate cnote items
	// return array $item
	public function generateTempCNoteSkuItems($bid, $sid, $params = array()){
		global $appCore, $con, $config;
		
		$bid = mi($bid);
		$sid = mi($sid);
		if(!$bid || !$sid)	return;
		
		if($config['enable_gst']){
			$itemIsInclusiveTaxString = $appCore->gstManager->itemIsInclusiveTaxString;
			$leftJoinOutputGSTString = $appCore->gstManager->leftJoinOutputGSTString;
			$gst_col_select = ", $itemIsInclusiveTaxString as sku_inclusive_tax";
		}
		
		if($params["return_type"] == "multiple_inv"){
			$fields_str = ", ifnull(sip.price,si.selling_price) as selling_price";
			if($config['enable_gst']){
				$fields_str .= ", output_gst.id as gst_id, output_gst.code as gst_code, output_gst.rate as gst_rate";
			}
			$from_str = "sku_items si";
			$where_str = "si.id=$sid";
		}else{
			$fields_str = ", if(do.is_under_gst,di.display_cost_price, di.cost_price) as selling_price, 
							di.uom_id, di.gst_id as gst_id, di.gst_code as gst_code, di.gst_rate as gst_rate";
			$from_str = "do_items di left join do on di.do_id = do.id and di.branch_id = do.branch_id left join sku_items si on di.sku_item_id = si.id";
			$where_str = "di.sku_item_id=$sid and di.branch_id = " . mi($params["branch_id"]) . " and di.do_id = " . mi($params["do_id"]);
		}
		
		$q1 = $con->sql_query($sql = "select si.id as sku_item_id, ifnull(sic.grn_cost,si.cost_price) as cost $fields_str 
			$gst_col_select
			from $from_str
			left join sku on si.sku_id = sku.id
			left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
			left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
			left join category_cache cc on cc.category_id=sku.category_id
			$leftJoinOutputGSTString
			where $where_str");
		$item = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$item)	return;	// item not found
		 
		
		if($params["return_type"] != "multiple_inv"){	// single invoice
			$item['price'] = $item['selling_price'];
			
			// GST
			if($config['enable_gst']){
				if($params['is_under_gst']){
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
		}else{	// multi invoice
			$item['uom_id'] = $appCore->uomManager->uomIdForEACH;
		}
		
		return $item;
	}
	
	// function to insert cnote item
	// return array cnote_items
	public function insertOrReplaceTempCNoteItem($bid, $cn_id, $item, $params = array()){
		global $appCore, $con, $config;
		
		if(!$bid || !$cn_id)	return;
		
		$upd = array();
		$upd['branch_id'] = $bid;
		$upd['cnote_id'] = $cn_id;
		$upd['sku_item_id'] = $item['sku_item_id'];
		$upd['cost'] = $item['cost'];
		$upd['price'] = $item['price'];
		$upd['uom_id'] = $item['uom_id'];
		if(isset($item['ctn']))	$upd['ctn'] = $item['ctn'];
		if(isset($item['pcs']))	$upd['pcs'] = $item['pcs'];
		if(isset($item['gst_id']))	$upd['gst_id'] = $item['gst_id'];
		if(isset($item['gst_code']))	$upd['gst_code'] = $item['gst_code'];
		if(isset($item['gst_rate']))	$upd['gst_rate'] = $item['gst_rate'];
		
		if($config['enable_gst']){
			$upd['display_price_is_inclusive'] = mi($item['display_price_is_inclusive']);
			$upd['display_price'] = $item['display_price'];
		}
		
		$upd['user_id'] = mi($params['user_id']);
		$upd['edit_time'] = mi($params['edit_time']);
		$upd['reason'] = $item['reason'];
	
		if($params['return_type'] == "multiple_inv"){
			$return_inv_no = $item['return_inv_no'];
			$return_inv_date = $item['return_inv_date'];
			$return_do_id = $item['return_do_id'];
		}else{
			$return_inv_no = $params['inv_no'];
			$return_inv_date = $params['inv_date'];
			$return_do_id = $params['do_id'];
		}
		$upd['return_inv_no'] = $return_inv_no;
		$upd['return_inv_date'] = $return_inv_date;
		$upd['return_do_id'] = $return_do_id;
		
		// line amt
		$upd['line_gross_amt'] = round($item['line_gross_amt'], 2);
		$upd['line_gst_amt'] = round($item['line_gst_amt'], 2);
		$upd['line_amt'] = round($item['line_amt'], 2);
		$upd['line_gross_amt2'] = round($item['line_gross_amt2'], 2);
		$upd['line_gst_amt2'] = round($item['line_gst_amt2'], 2);
		$upd['line_amt2'] = round($item['line_amt2'], 2);
		$upd['item_discount_amount'] = round($item['item_discount_amount'], 2);
		$upd['item_discount_amount2'] = round($item['item_discount_amount2'], 2);
		$upd['do_item_id'] = mi($item['do_item_id']);
		
		if(!$item['id']){
			$con->sql_query("insert into tmp_cnote_items ".mysql_insert_by_field($upd));
			$item_id = mi($con->sql_nextid());
		}else{
			$item_id = $upd['id'] = $item['id'];
			$con->sql_query("replace into tmp_cnote_items ".mysql_insert_by_field($upd));
		}
		
		if($params['reloadItem']){
			// get the inserted item and return
			$items_list = $this->getCNoteItems($bid, $cn_id, $item_id, true, $params);
			return $items_list[$item_id];
		}else{
			return $item_id;
		}
	}
	
	// function to insert cnote_items
	// return item_id
	public function insertCNoteItem($bid, $cn_id, $item, $params = array()){
		global $appCore, $con, $config;
		
		if(!$bid || !$cn_id)	return;
		
		$upd = array();
		$upd['branch_id'] = $bid;
		$upd['cnote_id'] = $cn_id;
		$upd['sku_item_id'] = $item['sku_item_id'];
		$upd['cost'] = $item['cost'];
		$upd['price'] = $item['price'];
		$upd['uom_id'] = $item['uom_id'];
		if(isset($item['ctn']))	$upd['ctn'] = $item['ctn'];
		if(isset($item['pcs']))	$upd['pcs'] = $item['pcs'];
		if(isset($item['gst_id']))	$upd['gst_id'] = $item['gst_id'];
		if(isset($item['gst_code']))	$upd['gst_code'] = $item['gst_code'];
		if(isset($item['gst_rate']))	$upd['gst_rate'] = $item['gst_rate'];
		
		if($config['enable_gst']){
			$upd['display_price_is_inclusive'] = mi($item['display_price_is_inclusive']);
			$upd['display_price'] = $item['display_price'];
		}
		
		$upd['reason'] = $item['reason'];
		$upd['return_inv_no'] = $item['return_inv_no'];
		$upd['return_inv_date'] = $item['return_inv_date'];
		$upd['return_do_id'] = $item['return_do_id'];
		
		// line amt
		$upd['line_gross_amt'] = round($item['line_gross_amt'], 2);
		$upd['line_gst_amt'] = round($item['line_gst_amt'], 2);
		$upd['line_amt'] = round($item['line_amt'], 2);
		$upd['line_gross_amt2'] = round($item['line_gross_amt2'], 2);
		$upd['line_gst_amt2'] = round($item['line_gst_amt2'], 2);
		$upd['line_amt2'] = round($item['line_amt2'], 2);
		$upd['item_discount_amount'] = round($item['item_discount_amount'], 2);
		$upd['item_discount_amount2'] = round($item['item_discount_amount2'], 2);
		$upd['do_item_id'] = mi($item['do_item_id']);
		
		$con->sql_query("insert into cnote_items ".mysql_insert_by_field($upd));
		$item_id = mi($con->sql_nextid());
		
		return $item_id;
		
	}
	
	// function to get cnote_items
	// return array $items_list
	public function getCNoteItems($bid, $cn_id, $item_id = 0, $isTmp = false, $params = array()){
		global $con, $appCore;
		
		$tblName = $isTmp ? 'tmp_cnote_items' : 'cnote_items';
		
		if(!$bid || !$cn_id)	return;
		
		$filter = array();
		$filter[] = "cni.branch_id=$bid";
		$filter[] = "cni.cnote_id=$cn_id";
		if($item_id>0)	$filter[] = "cni.id=$item_id";
		if($isTmp && $params['edit_time'])	$filter[] = "cni.edit_time=".mi($params['edit_time']);
		
		$filter = "where ".join(' and ', $filter);
		
		$items_list = array();
		
		// select the inserted object
		$q1 = $con->sql_query("select cni.*, si.mcode,si.sku_item_code,si.artno,si.additional_description, si.description, uom.fraction as uom_fraction, si.doc_allow_decimal, ((cni.ctn*uom.fraction)+cni.pcs) as total_qty,
			master_uom.code as master_uom_code, uom.code as uom_code, c.department_id, cn.do_id, cn.inv_no, cn.return_type
			from $tblName cni
			join sku_items si on si.id=cni.sku_item_id
			left join sku on sku.id=si.sku_id
			left join category c on c.id=sku.category_id
			left join uom on uom.id=cni.uom_id
			left join uom master_uom on master_uom.id=si.packing_uom_id
			left join cnote cn on cni.cnote_id = cn.id and cni.branch_id = cn.branch_id
			$filter
			order by cni.id");

		while($r = $con->sql_fetchassoc($q1)){
			$filter1 = array();
			if($r["return_type"] == "multiple_inv"){
				$filter1[] = "do.id=" .  ms($r["return_do_id"]);
				$filter1[] = "do.inv_no=" .  ms($r["return_inv_no"]);
				$filter1[] = "tdi.branch_id=" . mi($r["branch_id"]);
				$filter1[] = "tdi.sku_item_id=" . mi($r["sku_item_id"]);
			}else{
				$filter1[] = "do.id=" .  ms($r["do_id"]);
				$filter1[] = "do.inv_no=" .  ms($r["inv_no"]);
				$filter1[] = "tdi.branch_id=" . mi($r["branch_id"]);
				$filter1[] = "tdi.sku_item_id=" . mi($r["sku_item_id"]);
			}
			$filter_str = "where ".join(' and ', $filter1);
			$q2 = $con->sql_query("select ((tdi.ctn*uom.fraction)+tdi.pcs) as do_total_qty
								from do_items tdi
								left join sku_items on tdi.sku_item_id=sku_items.id
								left join sku on sku_items.sku_id = sku.id
								left join uom on uom.id=tdi.uom_id
								left join uom si_uom on si_uom.id = sku_items.packing_uom_id
								left join do on do.id = tdi.do_id and do.branch_id = tdi.branch_id
								$filter_str");
								
			while($r2=$con->sql_fetchassoc($q2)){
				$r["do_total_qty"] = $r2["do_total_qty"];
			}
			$con->sql_freeresult($q2);
			$items_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		return $items_list;
	}
	
	// function to save all tmp items
	// return null
	public function saveTempCNoteItems($user_id, $form, $items_list){
		global $con;
		
		if(!$items_list)	return;
		
		$form['user_id'] = mi($user_id);
		
		// loop all items
		foreach($items_list as $cn_item){
			$this->insertOrReplaceTempCNoteItem($form['branch_id'], $form['id'], $cn_item, $form);
		}
	}
	
	// function to delete tmp cnote item
	// return array $ret
	public function deleteTempCNoteItem($bid, $cn_id, $params){
		global $con;
		
		if(!$bid || !$cn_id)	return array('err' => 'Invalid CN');
		
		$filter = array();
		$filter[] = "branch_id=$bid";
		$filter[] = "cnote_id=$cn_id";
		$filter[] = "id=".mi($params['item_id']);
		$filter[] = "edit_time=".mi($params['edit_time']);
		if($params['user_id'])	$filter[] = "user_id=".mi($params['user_id']);
		
		$filter = join(' and ', $filter);
		$con->sql_query("delete from tmp_cnote_items where $filter");
		$affected_count = $con->sql_affectedrows();
		
		if($affected_count > 0)	return array('ok'=>1);
		return array('err'=>'Delete Failed');
	}
	
	// function to save cnote
	// return array $ret
	public function saveCNote($form, $items_list, $params = array()){
		global $con, $appCore, $LANG, $config;
		
		// check can edit or not
		$checkParams = array();
		$checkParams['branch_id'] = $params['branch_id'];
		$checkParams['user_id'] = $params['id'];
		$checkResult = $this->isCNoteAllowToEdit($form['branch_id'], $form['id'], $checkParams);
		if($checkResult['err'])	return $checkResult;
		
		// validate
		$checkResult = $this->validateData($form, $items_list, $params['is_confirm']);
		
		// got error 
		if($checkResult['err'])	return $checkResult;
		
		
		/*if($config['monthly_closing']){
			$is_month_closed = $appCore->is_month_closed($form['cn_date']);
			if($is_month_closed &&($params['is_confirm'] || $config['monthly_closing_block_document_action'])){
				return array('err' => $LANG['MONTH_DOCUMENT_IS_CLOSED']);
			}
		}*/
		
		// save into tmp item
		$this->saveTempCNoteItems($params['user_id'], $form, $items_list, $params);
		
		// check approval flow
		if($params['is_confirm']){
			$tmpParams = array();
			$haveApprovalFlow = $this->haveApprovalFlow($form['branch_id'], $tmpParams);
			if(!$haveApprovalFlow)	return array('err' => $LANG['CNOTE_NO_APPROVAL_FLOW']);
		}
		
		// save cnote
		$form['active'] = 1;
		$form['status'] = 0;
		$form['approved'] = 0;
		$cn_id = $this->insertOrUpdateCNote($form, $params);
		
		// save items
		$first_item_id = 0;
		$form["return_type"] = $params["return_type"];
		foreach($items_list as $cn_item){
			// insert item
			if($params['return_type'] == "single_inv"){
				$cn_item['return_inv_no'] = $params['inv_no'];
				$cn_item['return_inv_date'] = $params['inv_date'];
				$cn_item['return_do_id'] = $params['do_id'];
			}
			
			$item_id = $this->insertCNoteItem($form['branch_id'], $cn_id, $cn_item, $form);
			if(!$first_item_id)	$first_item_id = $item_id;
		}
		
		if($first_item_id>0){
			// delete old item
			$con->sql_query("delete from cnote_items where branch_id=".mi($form['branch_id'])." and id<$first_item_id and cnote_id=$cn_id");
		}
		
		// delete tmp items
		$con->sql_query("delete from tmp_cnote_items where branch_id=$form[branch_id] and cnote_id=$form[id] and edit_time=$form[edit_time]");
		
		// recalculate all amount
		$this->recalculateCNote($form['branch_id'], $cn_id);
		
		// is confirm
		if($params['is_confirm']){
			$confirmResult = $this->confirmCNote($form['branch_id'], $cn_id, $params);
			if($confirmResult['err'])	return $confirmResult;
			if($confirmResult['approved'])	$approved = 1;
		}else{
			log_br($params['user_id'], $this->moduleName, $cn_id, "CN #$cn_id saved");
		}
		
		// return ok
		$ret = array();
		$ret['ok'] = 1;
		$ret['cn_id'] = $cn_id;
		$ret['branch_id'] = $form['branch_id'];
		if($approved)	$ret['approved'] = 1;
		
		return $ret;
	}
	
	// function to check whether this module have approval flow
	// return boolean
	public function haveApprovalFlow($bid, $params = array()){
		global $appCore;
		return $appCore->approvalFlowManager->isModuleHaveApprovalFlow($bid, $this->approvalTypeName, $params);
	}
	
	// function to validate data before save
	// return array $ret
	public function validateData($form, $items_list, $is_confirm = false){
		global $appCore, $LANG;
		
		if(!$form)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
		if(!$items_list)	return array('err' => $LANG['CNOTE_NEED_AT_LEAST_ONE_ITEM']);
		
		// cn date
		if(!$form['cn_date'] || !$appCore->isValidDateFormat($form['cn_date']))	return array('err' => $LANG['CNOTE_INVALID_CN_DATE']);
		
		if($form["return_type"] == "multiple_inv"){
			$count = 0;
			foreach($items_list as $key => $item){
				if(!trim($item["return_inv_no"])){
					return array('err' => $LANG["CNOTE_INVALID_INV_NO"]);
				}
			}
		}else{
			// inv no
			if(!trim($form['inv_no']))	return array('err' => $LANG['CNOTE_INVALID_INV_NO']);
			// inv date
			if(!$form['inv_date'] || !$appCore->isValidDateFormat($form['inv_date']))	return array('err' => $LANG['CNOTE_INVALID_INV_DATE']);
		}
		
		if($is_confirm){
			foreach($items_list as $key => $item){
				$ctn = ($item["ctn"]?mf($item["ctn"]):0);
				$pcs = mf($item["pcs"]);
				$uom_fraction = mf($item["uom_fraction"]);
				$total_qty = (($ctn*$uom_fraction)+$pcs);
				if($total_qty <= 0){
					return array('err' => sprintf($LANG["CNOTE_MIN_QTY"], 1));
				}
			}
		}
		
		// cust name
		if(!trim($form['cust_name']))	return array('err' => $LANG['CNOTE_NEED_CUST_NAME']);
		
		return array('ok' => 1);
	}
	
	//////////////////// private function ///////////////////////
	// function to insert or update cnote
	// return int $cn_id
	private function insertOrUpdateCNote($form, $params = array()){
		global $con, $appCore;
		
		if(!$form)	return false;
		
		$upd = array();
		$upd['cn_date'] = trim($form['cn_date']);
		$upd['do_id'] = mi($form['do_id']);
		$upd['inv_no'] = trim($form['inv_no']);
		$upd['inv_date'] = trim($form['inv_date']);
		$upd['remark'] = trim($form['remark']);
		$upd['cust_name'] = trim($form['cust_name']);
		$upd['cust_address'] = trim($form['cust_address']);
		$upd['cust_brn'] = trim($form['cust_brn']);
		$upd['total_ctn'] = mf($form['total_ctn']);
		$upd['total_pcs'] = mf($form['total_pcs']);
		$upd['total_qty'] = mf($form['total_qty']);
		$upd['sub_total_gross_amount'] = mf($form['sub_total_gross_amount']);
		$upd['sub_total_gst_amount'] = mf($form['sub_total_gst_amount']);
		$upd['sub_total_amount'] = mf($form['sub_total_amount']);
		$upd['gross_discount_amt'] = mf($form['gross_discount_amt']);
		$upd['gst_discount_amt'] = mf($form['gst_discount_amt']);
		$upd['discount_amt'] = mf($form['discount_amt']);
		$upd['total_gross_amount'] = mf($form['total_gross_amount']);
		$upd['total_gst_amount'] = mf($form['total_gst_amount']);
		$upd['total_amount'] = mf($form['total_amount']);
		$upd['is_under_gst'] = mf($form['is_under_gst']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['discount'] = trim($form['discount']);
		$upd['return_type'] = trim($form['return_type']);
		
		if(isset($form['active']))	$upd['active'] = $form['active'];
		if(isset($form['status']))	$upd['status'] = $form['status'];
		if(isset($form['approved']))	$upd['approved'] = $form['approved'];
		
		if(is_new_id($form['id'])){
			// insert
			$upd['branch_id'] = mi($form['branch_id']);
			$upd['user_id'] = mi($params['user_id']);
			$upd['active'] = 1;
			$upd['status'] = 0;
			$upd['approved'] = 0;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into cnote ".mysql_insert_by_field($upd));
			$cn_id = $con->sql_nextid();
			
			// get branch info
			$branchInfo = $appCore->branchManager->getBranchInfo($upd['branch_id']);
			$upd2 = array();
			$upd2['cn_no'] = $branchInfo['report_prefix'].sprintf("%05d", $cn_id);
			
			// update back cnote
			$con->sql_query("update cnote set ".mysql_update_by_field($upd2)." where branch_id=".mi($upd['branch_id'])." and id=$cn_id");
		}else{
			// update
			$cn_id = mi($form['id']);
			$con->sql_query("update cnote set ".mysql_update_by_field($upd)." where branch_id=".mi($form['branch_id'])." and id=$cn_id");
		}
		
		return $cn_id;
	}
	
	// function to load cnote
	// return array $data
	public function loadCNote($bid, $cn_id, $params = array()){
		global $con, $appCore;
		
		$bid = mi($bid);
		$cn_id = mi($cn_id);
		$isEdit = mi($params['isEdit']);
		$loadItems = mi($params['loadItems']);
		
		if(!$bid || !$cn_id)	return false;
		
		$form = array();
		
		// load header
		$con->sql_query("select cn.*, user.u as owner_username
			from cnote cn
			left join user on user.id=cn.user_id
			where cn.branch_id=$bid and cn.id=$cn_id");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$form["branch_id"] = $bid;
		$form['adj_id_list'] = unserialize($form['adj_id_list']);
		
		if ($form['approval_history_id']>0){
			$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u, i.more_info
	from branch_approval_history_items i
	left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
	left join user on i.user_id=user.id
	where h.ref_table='cnote' and i.branch_id=$bid and i.approval_history_id=$form[approval_history_id]
	order by i.timestamp");
			$form['approval_history'] = array();
			while($r = $con->sql_fetchassoc($q2)){
				$r['more_info'] = unserialize($r['more_info']);
				$form['approval_history'][] = $r;
			}
			$con->sql_freeresult($q2);

			// check is approval or not
			if($params['user_id']){
				$params2 = array();
				$params2['user_id'] = $params['user_id'];
				$params2['id'] = $form['approval_history_id'];
				$params2['branch_id'] = $bid;
				$params2['check_is_approval'] = true;
				$is_approval = check_is_last_approval_by_id($params2, $con);
				if($is_approval)  $form['is_approval'] = 1;
			}
		}
		
		if($loadItems){
			// load items
			$itemsList = $this->getCNoteItems($bid, $cn_id);
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
				foreach($itemsList as $cn_item){
					unset($cn_item['id']);
					$this->insertOrReplaceTempCNoteItem($bid, $cn_id, $cn_item, $itemParams);
				}

				// load back from tmp
				$itemsList = $this->getCNoteItems($bid, $cn_id, 0, true, $itemParams);
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
	
	// function to check whether this CN is now allow to edit
	// return array $ret
	public function isCNoteAllowToEdit($bid, $cn_id, $params = array()){
		global $con, $LANG;
		
		$bid = mi($bid);
		$cn_id = mi($cn_id);
		
		if(!$bid || !$cn_id)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
		
		$ret = array();

		// cannot edit other branch
		if($params['branch_id'] != $bid){
			$ret['err'] = sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], $this->moduleName);
		}
		
		// check whether still can edit
		if(!is_new_id($cn_id)){
			$con->sql_query("select cn.* from cnote cn where cn.branch_id=$bid and cn.id=$cn_id");
			$form = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($form['active'] != 1 || $form['approved'] != 0 || ($form['status'] != 0 && $form['status'] != 2)){
				$ret['err'] = $LANG['CNOTE_NOT_ALLOW_TO_EDIT'];
			}
		}
		
		return $ret;
	}
	
	// function to clear all old tmp_cnote_items
	// return null
	public function clearAllOldTempCNoteItems(){
		global $con, $appCore;
		
		$expiredTime = $appCore->getExpiredEditTime();
		$con->sql_query("delete from tmp_cnote_items where edit_time<".mi($expiredTime));
	}
	
	// function to delete cnote
	// return array $ret
	public function deleteCNote($bid, $cn_id, $params = array()){
		global $con;
		
		$bid = mi($bid);
		$cn_id = mi($cn_id);
		
		// check can edit or not
		$checkResult = $this->isCNoteAllowToEdit($bid, $cn_id, $params);
		if($checkResult['err'])	return $checkResult;
		
		$upd = array();
		$upd['deleted_by'] = $params['user_id'];
		$upd['deleted_reason'] = trim($params['deleted_reason']);
		$upd['active'] = 0;
		$upd['status'] = 0;
		$upd['approved'] = 0;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("update cnote set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$cn_id");
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['branch_id'] = $bid;
		$ret['cn_id'] = $cn_id;
		return $ret;
	}
	
	// function to confirm cnote
	// return array $ret
	public function confirmCNote($bid, $cn_id, $params = array()){
		global $con, $LANG;
		
		// invalid id
		$bid = mi($bid);
		$cn_id = mi($cn_id);
		if(!$bid || !$cn_id)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
		
		// form cant load
		$form = $this->loadCNote($bid, $cn_id);
		if(!$form)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
		$approval_history_id = $form['approval_history_id'];
		
		$tmpParams = array();
		$haveApprovalFlow = $this->haveApprovalFlow($bid, $tmpParams);
		if(!$haveApprovalFlow)	return array('err' => $LANG['CNOTE_NO_APPROVAL_FLOW']);
		
		// confirm
		$params2 = array();
		$params2['type'] = 'CN';
		$params2['reftable'] = 'cnote';
		$params2['user_id'] = $params['user_id'];
		$params2['branch_id'] = $bid;
		$params2['doc_amt'] = $form['total_amount'];
		
		if($approval_history_id) $params2['curr_flow_id'] = $approval_history_id; // use back the same id if already have
		$astat = check_and_create_approval2($params2, $con);

		if(!$astat) return array('err' => $LANG['CNOTE_NO_APPROVAL_FLOW']);
		else{
			$approval_history_id = mi($astat[0]);			 
			if ($astat[1] == '|'){
				$last_approval = true;
				if($astat['direct_approve_due_to_less_then_min_doc_amt'])	$direct_approve_due_to_less_then_min_doc_amt = 1;	// direct approve because no qualify for min doc amt
			}
		}
		
		// update ref id
		$upd = array();
		$upd['ref_id'] = $cn_id;
		$con->sql_query("update branch_approval_history set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$approval_history_id");

		// mark confirm
		$upd = array();
		$upd['active'] = 1;
		$upd['status'] = 1;
		$upd['approved'] = 0;
		$upd['approval_history_id'] = $approval_history_id;
		$con->sql_query("update cnote set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$cn_id");
			
		log_br($params['user_id'], $this->moduleName, $cn_id, "CN #$cn_id confirmed");
		
		if(!$last_approval){
			// send pm
			$to = get_pm_recipient_list2($cn_id, $approval_history_id, 1, 'confirmation', $bid, 'cnote');
			send_pm2($to, "Credit Note Approval (ID#$cn_id) confirmed", "cnote.php?a=view&id=$cn_id&branch_id=$bid", array('module_name'=>'cnote'));
	
			// confirmed but not yet approved
			$ret = array();
			$ret['ok'] = 1;
			return $ret;
		}else{
			// confirmed and get approved
			if($direct_approve_due_to_less_then_min_doc_amt)	$params['direct_approve_due_to_less_then_min_doc_amt'] = 1;
			$params['status'] = 1;
			return $this->approveCNote($bid, $cn_id, $params);
		}
	}
	
	// function to approve cnote
	// return array $ret
	public function approveCNote($bid, $cn_id, $params = array()){
		global $con, $LANG;
		
		// invalid id
		$bid = mi($bid);
		$cn_id = mi($cn_id);
		if(!$bid || !$cn_id)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
		
		// form cant load
		$form = $this->loadCNote($bid, $cn_id);
		if(!$form)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
			
		// check still got next approval
		$params2 = array();
		$params2['user_id'] = $params['user_id'];
		$params2['id'] = $form['approval_history_id'];
		$params2['branch_id'] = $bid;
		$params2['update_approval_flow'] = true;
		$is_last = check_is_last_approval_by_id($params2, $con);	
		if($is_last)  $approved = 1;	// already last approval
	
		// update approval history
		$upd = array();
		$upd['status'] = $approved ? 1 : 0;
		
		$con->sql_query("update branch_approval_history set ".mysql_update_by_field($upd)." where id = $form[approval_history_id] and branch_id = $bid");
		
		// insert approval history item
		$upd = array();
		$upd['approval_history_id'] = $form['approval_history_id'];
		$upd['branch_id'] = $bid;
		$upd['user_id'] = $params['user_id'];
		$upd['status'] = 1;
		$upd['log'] = 'Approved';

		if($params['direct_approve_due_to_less_then_min_doc_amt'])	$upd['more_info']['direct_approve_due_to_less_then_min_doc_amt'] = 1;
		if($upd['more_info'])	$upd['more_info'] = serialize($upd['more_info']);

		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd));
		
		// update cnote
		$upd = array();
		$upd['active'] = 1;
		$upd['status'] = 1;
		$upd['approved'] = $approved ? 1 : 0;
		$con->sql_query("update cnote set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$cn_id");
		
		// send pm
		$to = get_pm_recipient_list2($cn_id, $form['approval_history_id'], 1, 'approval', $bid, 'cnote');
		send_pm2($to, "Credit Note Approval (ID#$cn_id) approval", "cnote.php?a=view&id=$cn_id&branch_id=$bid", array('module_name'=>'cnote'));
			
		// log
		log_br($params['user_id'], $this->moduleName, $cn_id, "CN #$cn_id approved");
		
		$ret = array();
		$ret['ok'] = 1;
		if($approved)	$ret['approved'] = 1;
		if($params['direct_approve_due_to_less_then_min_doc_amt'])	$ret['direct_approve_due_to_less_then_min_doc_amt'] = 1;
		
		if($approved){
			// fully approved, generate other data
			$result = $this->generateApprovedCNoteData($bid, $cn_id);
			if($result['err'])	return $result;
		}
		
		return $ret;
	}
	
	// function to reject cnote approval
	// return array $ret
	public function rejectCNote($bid, $cn_id, $params = array()){
		global $con, $LANG;
		
		// invalid id
		$bid = mi($bid);
		$cn_id = mi($cn_id);
		$reason = 'Reject';
		if($params['reason'])	$reason = trim($params['reason']);
		
		if(!$bid || !$cn_id)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
		
		// form cant load
		$form = $this->loadCNote($bid, $cn_id);
		if(!$form)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
			
		// update approval history
		$upd = array();
		$upd['status'] = 2;
		
		$con->sql_query("update branch_approval_history set ".mysql_update_by_field($upd)." where id = $form[approval_history_id] and branch_id = $bid");
		
		// insert approval history item
		$upd = array();
		$upd['approval_history_id'] = $form['approval_history_id'];
		$upd['branch_id'] = $bid;
		$upd['user_id'] = $params['user_id'];
		$upd['status'] = 2;
		$upd['log'] = $reason;

		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd));
		
		// update cnote
		$upd = array();
		$upd['active'] = 1;
		$upd['status'] = 2;
		$upd['approved'] = 0;
		$con->sql_query("update cnote set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$cn_id");
		
		// send pm
		$to = get_pm_recipient_list2($cn_id, $form['approval_history_id'], 2, 'approval', $bid, 'cnote');
		send_pm2($to, "Credit Note Approval (ID#$cn_id) reject", "cnote.php?a=view&id=$cn_id&branch_id=$bid", array('module_name'=>'cnote'));
			
		// log
		log_br($params['user_id'], $this->moduleName, $cn_id, "CN #$cn_id rejected");
		
		$ret = array();
		$ret['ok'] = 1;
		
		return $ret;
	}
	
	// function to cancel/terminate cnote in approval
	// return array $ret
	public function cancelCNote($bid, $cn_id, $params = array()){
		global $con, $LANG;
		
		// invalid id
		$bid = mi($bid);
		$cn_id = mi($cn_id);
		$reason = 'Terminate';
		if($params['reason'])	$reason = trim($params['reason']);
		
		if(!$bid || !$cn_id)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
		
		// form cant load
		$form = $this->loadCNote($bid, $cn_id);
		if(!$form)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
			
		// update approval history
		$upd = array();
		$upd['status'] = 5;
		
		$con->sql_query("update branch_approval_history set ".mysql_update_by_field($upd)." where id = $form[approval_history_id] and branch_id = $bid");
		
		// insert approval history item
		$upd = array();
		$upd['approval_history_id'] = $form['approval_history_id'];
		$upd['branch_id'] = $bid;
		$upd['user_id'] = $params['user_id'];
		$upd['status'] = 5;
		$upd['log'] = $reason;

		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd));
		
		// update cnote
		$upd = array();
		$upd['active'] = 1;
		$upd['status'] = 5;
		$upd['approved'] = 0;
		$con->sql_query("update cnote set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$cn_id");
		
		// send pm
		$to = get_pm_recipient_list2($cn_id, $form['approval_history_id'], 5, 'approval', $bid, 'cnote');
		send_pm2($to, "Credit Note Approval (ID#$cn_id) terminate", "cnote.php?a=view&id=$cn_id&branch_id=$bid", array('module_name'=>'cnote'));
			
		// log
		log_br($params['user_id'], $this->moduleName, $cn_id, "CN #$cn_id terminate");
		
		$ret = array();
		$ret['ok'] = 1;
		
		return $ret;
	}
	
	// function to reset cnote
	// return array $ret
	public function resetCNote($bid, $cn_id, $params = array()){
		global $con, $config, $appCore, $LANG;
		
		// invalid id
		$bid = mi($bid);
		$cn_id = mi($cn_id);
		if(!$bid || !$cn_id)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
		
		// form cant load
		$form = $this->loadCNote($bid, $cn_id);
		if(!$form)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
		
		// check user
		if(!$appCore->userManager->isUserAllowToResetDocument($params['user_id'])){
			return array('err' => $LANG['USER_LEVEL_NO_REACH']);
		}
		
		//add reset config
		$check_date = strtotime($form['cn_date']);
		
		/*if($config['monthly_closing']){
			$is_month_closed = $appCore->is_month_closed($form['cn_date']);
			if($is_month_closed){
				return array('err' => $LANG['MONTH_DOCUMENT_IS_CLOSED']);
			}
		}*/

		if (isset($config['reset_date_limit']) && $config['reset_date_limit'] >= 0){
			$reset_limit = $config['reset_date_limit'];
			$reset_limit = strtotime("-1 day",strtotime("-$reset_limit day" , strtotime("now")));

			if ($check_date<$reset_limit){
				return array('err' => $LANG['CNOTE_DATE_RESET_LIMIT']);
			}
		}
		
		$aid=$form['approval_history_id'];
		
		// insert approval history items
		$upd = array();
		$upd['approval_history_id'] = $aid;
		$upd['branch_id'] = $bid;
		$upd['user_id'] = $params['user_id'];
		$upd['status'] = 0;
		$upd['log'] = $params['reason'];

		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd));
		
		// update branch approval history
		$upd = array();
		$upd['status'] = 0;
		$upd['approved_by'] = '';
		$con->sql_query("update branch_approval_history set ".mysql_update_by_field($upd)." where id = $aid and branch_id = $bid");
		
		// update cnote
		$upd = array();
		$upd['active'] = 1;
		$upd['status'] = 0;
		$upd['approved'] = 0;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("update cnote set ".mysql_update_by_field($upd)." where id=$cn_id and branch_id=$bid");

		// cancel all generated adj
		$this->cancelAllGeneratedAdjustment($bid, $cn_id);
		
		log_br($params['user_id'], $this->moduleName, $cn_id, sprintf("CN Reset ($form[cn_no])",$cn_id));

		$ret = array();
		$ret['ok'] = 1;
		
		return $ret;
	}
	
	// function to load cnote in approval cycle
	// return array $data
	public function loadApprovalCNoteList($bid, $user_id){
		global $con;
		
		$bid = mi($bid);
		$user_id = mi($user_id);
		
		if(!$bid || !$user_id)	return false;
		
		$data = array();
		$search_approval = $user_id;
		$sql = "select cn.*, bah.approvals, bah.flow_approvals as org_approvals, branch.code as branch_name, user.u as user_name
			from cnote cn
			left join branch_approval_history bah on cn.approval_history_id = bah.id and cn.branch_id=$bid
			left join user on user.id=cn.user_id
			left join branch on cn.branch_id = branch.id
			where (
			(bah.approvals like '|$search_approval|%' and bah.approval_order_id=1) or
			(bah.approvals like '%|$search_approval|%' and bah.approval_order_id in (2,3))
			) and cn.status=1 and cn.approved=0 and cn.active=1 $doc_filter ";
		
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
			$data['cnList'][$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		return $data;
	}
	
	// function to load all cnote_items required data for display_price
	// return null
	public function loadItemsRequiredData($is_under_gst = 0, $items_list = array()){
		global $con, $smarty, $appCore, $config;
		
		if(!$appCore->haveSmarty)	return;
		
		// load uom
		$smarty->assign('uomList', $appCore->uomManager->getUOMList(array('active'=>1)));
		
		// item reason list
		$smarty->assign('itemReasonList', ($config["cnote_reason_list"])?$config["cnote_reason_list"]:$this->itemReasonList);
		
		if($is_under_gst){
			// load gst
			construct_gst_list();
			
			if($items_list){
				foreach($items_list as $r){
					// gst
					if($r['gst_id'] > 0){
						check_and_extend_gst_list($r);
					}
				}
			}
		}
	}
	
	// function to auto generate all contain after cnote approved
	// return array $ret
	public function generateApprovedCNoteData($bid, $cn_id){
		global $con, $LANG, $appCore;
		
		// invalid id
		$bid = mi($bid);
		$cn_id = mi($cn_id);
		if(!$bid || !$cn_id)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
		
		// form cant load
		$params2 = array();
		$params2['loadItems'] = 1;
		$data = $this->loadCNote($bid, $cn_id, $params2);
		$form = $data['header'];
		if(!$form)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
		
		if($form['active'] != 1 && $form['status'] != 1 && $form['approved'] != 1){
			return array('err' => $LANG['CNOTE_NOT_ALLOW_GENERATE_DATA']);
		}
		
		// cancel all adjustment first
		$this->cancelAllGeneratedAdjustment($bid, $cn_id);
		
		// get current available adjustment id list, try to re-use it
		$available_adj_id_list = array();
		$used_id_list = array();
		$con->sql_query("select id from adjustment where branch_id=$bid and cnote_id=$cn_id order by id");
		while($r = $con->sql_fetchassoc()){
			$available_adj_id_list[] = $r['id'];
		}
		$con->sql_freeresult();
		
		if($data['items_list']){
			// construct items into department
			$itemsByDeptID = array();
			
			// loop all cnote items
			foreach($data['items_list'] as $r){
				// categorise into dept id
				$itemsByDeptID[$r['department_id']][] = $r;
			}
			
			// loop items by department id
			$loop_index = 0;
			$sid_list = array();
			foreach($itemsByDeptID as $deptID => $itemsList){
				$adj_id = $available_adj_id_list[$loop_index];
				
				// generate adjustment
				$upd = array();
				$upd['cancelled'] = 0;
				$upd['cancelled_by'] = 0;
				$upd['adjustment_date'] = $form['cn_date'];
				$upd['adjustment_type'] = 'ADJUST BY CREDIT NOTE';
				$upd['remark'] = 'From CN '.$form['cn_no'];
				$upd['active'] = 1;
				$upd['status'] = 1;
				$upd['approved'] = 1;
				$upd['last_update'] = 'CURRENT_TIMESTAMP';
				$upd['dept_id'] = $deptID;
				$upd['cnote_id'] = $cn_id;
				
				if($adj_id > 0){
					// re-use
					// delete all old adjustment items
					$con->sql_query("delete from adjustment_items where branch_id=$bid and adjustment_id=$adj_id");
					
					// update back to old adjustment
					$con->sql_query("update adjustment set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$adj_id");
				}else{
					// create
					$upd['id'] = $appCore->generateNewID("adjustment","branch_id=".mi($bid));
					$upd['branch_id'] = $bid;
					$upd['user_id'] = $form['user_id'];
					$upd['added'] = 'CURRENT_TIMESTAMP';
					
					// create new adjustment
					$con->sql_query("insert into adjustment ".mysql_insert_by_field($upd));
					$adj_id = $upd['id'];
				}
				
				
				foreach($itemsList as $r){
					$upd2 = array();
					$upd2['id'] = $appCore->generateNewID("adjustment_items","branch_id=".mi($bid));
					$upd2['branch_id'] = $bid;
					$upd2['adjustment_id'] = $adj_id;
					$upd2['user_id'] = $form['user_id'];
					$upd2['sku_item_id'] = $r['sku_item_id'];
					$upd2['qty'] = ($r['ctn']*$r['uom_fraction'])+$r['pcs'];
					$upd2['cost'] = $r['cost'];
					$upd2['selling_price'] = round($r['price']/$r['uom_fraction'], 2);
					// get stock balance
					$stockBalance = $appCore->skuManager->getStockBalance($r['sku_item_id'], $bid, $form['cn_date']);
					$upd2['stock_balance'] = $stockBalance['qty'];
					
					$con->sql_query("insert into adjustment_items ".mysql_insert_by_field($upd2));
					$sid_list[] = mi($r['sku_item_id']);
				}
				
				$used_id_list[] = $adj_id;
				$loop_index++;
			}
			
			$upd3 = array();
			$upd3['adj_id_list'] = serialize($used_id_list);
			$con->sql_query("update cnote set ".mysql_update_by_field($upd3)." where branch_id=$bid and id=$cn_id");
			
			if($sid_list){
				// update cost changes
				$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (".join(',', $sid_list).")");
			}
			
		}
		
		$ret = array();
		$ret['ok'] = 1;
		return $ret;
	}
	
	// function to auto cancel all generated adjustment
	// return null
	public function cancelAllGeneratedAdjustment($bid, $cn_id){
		global $con, $appCore;
		
		$bid = mi($bid);
		$cn_id = mi($cn_id);
		if(!$bid || !$cn_id)	return;
		
		// get all adjustment generated by this cnote
		$q1 = $con->sql_query("select id from adjustment where branch_id=$bid and cnote_id=$cn_id order by id");
		
		// loop adjustment
		while($r = $con->sql_fetchassoc($q1)){
			$adj_id = $r['id'];
			
			$upd = array();
			$upd['cancelled'] = 1;
			$upd['cancelled_by'] = 0;
			$upd['active'] = 0;
			$upd['status'] = 5;
			$upd['approved'] = 0;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			
			// cancel adjustment
			$con->sql_query("update adjustment set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$adj_id");
			
			// get all adjustment items
			$q2 = $con->sql_query("select distinct(sku_item_id) as sid from adjustment_items where branch_id=$bid and adjustment_id=$adj_id");
			$sid_list = array();
			while($ai = $con->sql_fetchassoc($q2)){
				$sid_list[] = mi($ai['sid']);
			}
			$con->sql_freeresult($q2);
			
			// update cost changes
			$con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (".join(',', $sid_list).")");
		}
		$con->sql_freeresult($q1);
	}
	
	// function to generate cnote printing
	// return array $ret
	public function generateCNotePrinting($bid, $cn_id, $params = array()){
		global $con, $smarty, $config, $appCore, $LANG;
		
		// must have smarty
		if(!$appCore->haveSmarty)	return array('err' => $LANG['SMARTY_MODULE_NOT_FOUND']);
		
		// invalid id
		$bid = mi($bid);
		$cn_id = mi($cn_id);
		if(!$bid || !$cn_id)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
		
		// form cant load
		$params2 = array();
		$params2['loadItems'] = 1;
		$data = $this->loadCNote($bid, $cn_id, $params2);
		$form = $data['header'];
		if(!$form)	return array('err' => $LANG['CNOTE_INVALID_FORM']);
		
		$form = $data['header'];
		$items_list = $data['items_list'];
		
		// not allow to print
		if($form['active'] != 1 || $form['status'] != 1 || $form['approved'] != 1){
			return array('err' => $LANG['CNOTE_NOT_ALLOW_PRINT']);
		}
		
		if(!$items_list)	return array('err' => $LANG['NO_ITEM_TO_PRINT']);
		
		// calculate total page
		$item_per_page= $config['cnote_print_item_per_page'] ? $config['cnote_print_item_per_page'] : $this->printItemPerPage;
		$item_per_lastpage = $config['cnote_print_item_last_page'] ? $config['cnote_print_item_last_page'] : $item_per_page-5;
		$totalpage = 1 + ceil((count($items_list)-$item_per_lastpage)/$item_per_page);
		
		// load custom template
		if($config['cnote_print_template']) $tpl = $config['cnote_print_template'];
		else $tpl = "cnote.print.tpl";
		
		// load from branch
		$fromBranch = $appCore->branchManager->getBranchInfo($bid);
		$smarty->assign('branch', $fromBranch);
		
		$smarty->assign('form', $form);
		
		// start print cnote
		$item_index = -1;
		$item_no = -1;
		$page = 1;
		
		$page_item_list = array();
		$page_item_info = array();
			
		foreach($items_list as $r){	// loop for each item
			if($item_index+1>=$item_per_page){
				$page++;
				$item_index = -1;
			}
			
			$item_no++;
			$item_index++;
			$r['item_no'] = $item_no;
			
			$page_item_list[$page][$item_index] = $r;	// add item to this page
			$r['additional_description'] = unserialize($r['additional_description']);
			if($config['sku_enable_additional_description'] && $r['additional_description']){
				foreach($r['additional_description'] as $desc){
					if($item_index+1>=$item_per_page){
						$page++;
						$item_index = -1;
					}
			
					$item_index++;
					$desc_row = array();
					$desc_row['description'] = $desc;
					
					$page_item_list[$page][$item_index] = $desc_row;
					
					$page_item_info[$page][$item_index]['not_item'] = 1;
				}
			}
		}
	
		// fix last page
		if(count($page_item_list[$page]) > $item_per_lastpage){	// last page item too many
			$page++;
			$page_item_list[$page] = array();
		}
		
		$totalpage = count($page_item_list);
		foreach($page_item_list as $page => $item_list){
			$this_page_num = ($page < $totalpage) ? $item_per_page : $item_per_lastpage;
			$smarty->assign("PAGE_SIZE", $this_page_num);
			$smarty->assign("is_last_page", ($page >= $totalpage));
			$smarty->assign("page", "Page $page of $totalpage");
			$smarty->assign("start_counter",$item_list[0]['item_no']);
			$smarty->assign("items", $item_list);
			$smarty->assign("page_item_info", $page_item_info[$page]);
			$ret['html'] .= $smarty->fetch($tpl);
			$smarty->assign("skip_header",1);
			
		}
		return $ret;
	}
	
	////////////////////////////////////////////////////// private function ////////////////////////////////////////////////////////
	// function to auto recalculate all cnote amount
	// return null
	private function recalculateCNote($bid, $cn_id){
		global $con;
		
		
	}
	
}
?>
