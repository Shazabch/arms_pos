<?php
/*
10/21/2015 5:19 PM Andy
- Add maintenance version check 279.

10/22/2015 9:55 AM Andy
- Enhanced to have branch filter on load listing.

03/08/2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

06/17/2016 15:30 Edwin
- Bugs fixed on added items consists of incomplete info.
- Bugs fixed on prompt error when added multiple items in one time.
- Bugs fixed on grn barcoder does not compare maximum quantity with DO.

5/29/2017 16:50 Qiu Ying
- Enhanced to return multiple invoice

6/15/2017 16:19 Qiu Ying
- Bug fixed on stock balance should be labelled with Branch Code, not currency symbol

6/20/2017 11:14 AM Qiu Ying
- Bug fixed on rejected/cancelled DO still able to do credit note

2017-08-21 11:17 AM Qiu Ying
- Enhanced to load debtor and branch for customer info
- Enhanced to load the item price, uom and gst code from DO invoice when add item

8/22/2017 1:24 PM Andy
- Change to use GST Code 'OS' if return invoice have no gst.
*/
include("include/common.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

if($config['consignment_modules']){	// this module is not for consignment customer
	header("Location: home.php");
	exit;
}

if (!privilege('CN')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CN', BRANCH_CODE), "/index.php");	
include("do.include.php");
$maintenance->check(279);

class ARMS_CN extends Module{
	var $is_refresh = false;
	var $is_view = false;
	
	function __construct(){
		global $con, $smarty, $appCore;
		
		parent::__construct($appCore->cnoteManager->moduleName);
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
				$params['type'] = 'saved';
				break;
			case 2:
				$params['type'] = 'waiting_approval';
				break;
			case 3:
				$params['type'] = 'rejected';
				break;
			case 4:
				$params['type'] = 'cancelled';
				break;
			case 5:
				$params['type'] = 'approved';
				break;
			default:
				$params['type'] = 'search';
				$params['search_str'] = trim($_REQUEST['search_str']);
				break;
		}
		
		$ret = array();

		// load the list
		$data = $appCore->cnoteManager->loadCNoteListing($params);
		
		// show the list in html
		//if($data['cnList']){
			$smarty->assign('cnItemList', $data['cnItemList']);
			$smarty->assign('cnList', $data['cnList']);
			$smarty->assign('total_page', $data['total_page']);
			$ret['html'] = $smarty->fetch('cnote.list.tpl');
		//}

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

		$cn_id = mi($_REQUEST['id']);

		$params = array();

		if($cn_id > 0){
			// edit or refresh
			if($this->is_refresh){
				// refresh
				$bid = $_REQUEST['branch_id'];
				$data['header'] = $_REQUEST;
				
				// use back the old edit time
				$params['edit_time'] = $_REQUEST['edit_time'];

				// save tmp items
				$appCore->cnoteManager->saveTempCNoteItems($sessioninfo['id'], $data['header'], $_REQUEST['items_list']);
				// load all tmp items
				$data['items_list'] = $appCore->cnoteManager->getCNoteItems($bid, $cn_id, 0, true, $params);
				$smarty->assign('is_refresh', 1);
			}else{
				// edit
				if(BRANCH_CODE == 'HQ'){
					$bid = mi($_REQUEST['branch_id']);
				}
				if(!$bid)	$bid = $sessioninfo['branch_id'];
				
				// load cnote
				$tmp = array();
				$tmp['loadItems'] = 1;
				$tmp['user_id'] = $sessioninfo['id'];
				if(!$this->is_view)	$tmp['isEdit'] = 1;
				$data = $appCore->cnoteManager->loadCNote($bid, $cn_id, $tmp);
			}
			
			if(!$this->is_view){
				// check can edit or not
				$checkParams = array();
				$checkParams['branch_id'] = $sessioninfo['branch_id'];
				$checkParams['user_id'] = $sessioninfo['id'];
				$checkRet = $appCore->cnoteManager->isCNoteAllowToEdit($bid, $cn_id, $checkParams);
				
				// cannot edit, prompt error
				if($checkRet['err']){
					$data['err'][0] = $checkRet['err'];
				}
				
			}
			
			// refresh is under gst
			$data['header']['is_under_gst'] = $appCore->cnoteManager->checkCnGstStatus($data['header']);
			
			if(!$this->is_view)	$can_save = 1;
		}else{
			// new
			$params['branch_id'] = $sessioninfo['branch_id'];	// must use login branch id
			$params['user_id'] = $sessioninfo['id'];

			// create temporary new cn
			$data = $appCore->cnoteManager->generateTempNewCN($params);
		}

		if($data['err']){
			// show the first error
			display_redir($_SERVER['PHP_SELF'], $this->title, $data['err'][0]);
		}
		
		// load required data for items
		$appCore->cnoteManager->loadItemsRequiredData($data['header']['is_under_gst'], $data['items_list']);

		//print_r($data);
		
		$con->sql_query("select * from debtor where active=1 order by code");
		while($r = $con->sql_fetchassoc()){
			$debtor[$r['id']] = $r;
		}
		$smarty->assign("debtor", $debtor);
		
		$con->sql_query("select id, code, description, address, company_no from branch where active=1 order by sequence, code");
		while($r = $con->sql_fetchassoc()){
			$branch[] = $r;
		}
		$smarty->assign("branch", $branch);

		$smarty->assign('form', $data['header']);
		$smarty->assign('items_list', $data['items_list']);
		
		if(!$this->is_view){
			$smarty->assign('can_edit', 1);
			$smarty->assign('can_save', $can_save);
		}
		
		$this->display('cnote.open.tpl');
	}
	
	function refresh(){
		$this->is_refresh = true;
		$this->open();
	}
	
	function ajax_add_item_row(){
		global $appCore, $smarty, $LANG, $sessioninfo, $con;
		
		$found = $repeat = false;
		
		// add new item
		$params = array();
		$params['branch_id']  = mi($_REQUEST['branch_id']);
		$params['cn_id'] = mi($_REQUEST['id']);
		$params['is_under_gst'] = $is_under_gst = mi($_REQUEST['is_under_gst']);
		$params['edit_time'] = mi($_REQUEST['edit_time']);
		$params['user_id'] = $sessioninfo['id'];
		$params['inv_no'] = $_REQUEST['inv_no'];
		$params['inv_date'] = $_REQUEST['inv_date'];
		$params['do_id'] = $_REQUEST['do_id'];
		$params['return_type'] = $_REQUEST['return_type'];

		$grn_barcode = trim($_REQUEST['grn_barcode']);
		if($grn_barcode){
			// scan barcode
			$params['grn_barcode'] = $grn_barcode;
			if(!$_REQUEST['grn_barcode_type']) {
				$grn_barcode_info = get_grn_barcode_info($params['grn_barcode']);
				$grn_barcode = mi($grn_barcode_info['sku_item_id']);
				$grn_do_qty = mi($grn_barcode_info['qty_pcs']);
			}
		}else{
			// sku item id
			$params['sid_list'] = $_REQUEST['sku_code_list'];
			foreach($params['sid_list'] as $k=>$v){
				if(intval($v)<=0) unset($params['sid_list'][$k]);
			}
			$params['sid_list']=array_values($params['sid_list']);
		}

		if($_REQUEST['do_id'] && $_REQUEST['return_type'] != "multiple_inv"){
			//$do_items=explode(",",$_REQUEST['do_items']);
			$do=$this->ajax_check_do_no($_REQUEST['do_id'],$_REQUEST['branch_id']);
			
			if($_REQUEST['items_list']) {
				foreach($_REQUEST['items_list'] as $i) {
					$tmp_do_item_id[] = $i['do_item_id'];
				}
			}
		
			if($grn_barcode!=''){
				foreach($do['items'] as $i){
					if($tmp_do_item_id && in_array($i['id'], $tmp_do_item_id))	{
						if($grn_barcode==$i['sku_item_code'] || $grn_barcode==$i['artno'] || $grn_barcode==$i['mcode'] || $grn_barcode==$i['sku_item_id'])
							$repeat = 1;
						continue;
					}
					if($grn_barcode==$i['sku_item_code'] || $grn_barcode==$i['artno'] || $grn_barcode==$i['mcode'] || $grn_barcode==$i['sku_item_id']){
						if($grn_do_qty > $i['pcs']){
							$ret['failed_reason'] = "This item's max pcs is ".$i['pcs'];
							print json_encode($ret);
							return;
						}
						$do_items[]=$i['id'];
						$found=true;
						break;
					}
				}
			}else {
				$do_sid=array();
				foreach($do['items'] as $i) {
					if($tmp_do_item_id && in_array($i['id'], $tmp_do_item_id))	{
						foreach($params['sid_list'] as $sid) {
							if($sid == $i['sku_item_id'])
								$repeat = 1;
						}
						continue;
					}
					$do_sid[]=$i['sku_item_id'];
				}
				
				foreach($params['sid_list'] as $sid) {	
					if(in_array($sid, $do_sid)) {
						foreach($do['items'] as $i) {
							if($sid == $i['sku_item_id'])	$do_items[]=$i['id'];
						}
						$found=true;
					}else {
						$found=false;
						break;
					}
				}
				//if (array_values(array_intersect($do_sid, $params['sid_list'])) == $params['sid_list']) {
				//	$found=true;
				//}
			}
		}else{
			$found = true;
		}

		if(!$found){
			if($repeat)	$ret['failed_reason'] = "Item already added in Credit Note";
			else 		$ret['failed_reason'] = "Item not found in DO";
		}
		else{
			// call function to add
			$data = $appCore->cnoteManager->addTempCNoteItems($params);
			$ret = array();

			// got error
			if($data['error'])	$ret['failed_reason'] = $data['error'];
			else{
				// check got item
				if($data['items_list']){
					$smarty->assign('form', $_REQUEST);
					$smarty->assign('can_edit', 1);

					// load required data for items
					$appCore->cnoteManager->loadItemsRequiredData($is_under_gst);

					$ret['html'] = '';
					// loop item and create html
					$i=0;
					foreach($data['items_list'] as $item){
						if(isset($do_items[$i])){
							$item['do_item_id']=$do_items[$i];
							//$smarty->assign('do_item', $do['items'][$do_items[$i]]);
						}
						
						$smarty->assign('cn_item', $item);
						$ret['html'] .= $smarty->fetch('cnote.open.sheet.item_row.tpl');
						$ret['item_id_list'][] = $item['id'];

						$i++;
					}
					$ret['ok'] = 1;
				}else{
					// no item found
					$ret['failed_reason'] = $LANG['ITEM_NOT_FOUND'];
				}
			}
		}
		
		print json_encode($ret);
	}
	
	function ajax_delete_item(){
		global $con, $smarty, $appCore, $config, $sessioninfo;
		$ret = array();
		
		$form = $_REQUEST;
		
		$params = array();
		$params['branch_id']  = mi($form['branch_id']);
		$params['cn_id'] = mi($form['id']);
		$params['edit_time'] = mi($form['edit_time']);
		$params['user_id'] = $sessioninfo['id'];
		$params['item_id'] = $form['delete_item_id'];
		
		// call function to delete
		$data = $appCore->cnoteManager->deleteTempCNoteItem($form['branch_id'], $form['id'], $params);
		
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
		
		$form = $_REQUEST;
		//print_r($form);
		
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		$params['branch_id'] = $sessioninfo['branch_id'];
		$params['inv_no'] = $_REQUEST['inv_no'];
		$params['inv_date'] = $_REQUEST['inv_date'];
		$params['do_id'] = $_REQUEST['do_id'];
		$params['return_type'] = $_REQUEST['return_type'];
		if($is_confirm)	$params['is_confirm'] = 1;
		$data = $appCore->cnoteManager->saveCNote($form, $form['items_list'], $params);
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
		
		$data = $appCore->cnoteManager->deleteCNote($form['branch_id'], $form['id'], $params);
		if($data['err'])	$data['failed_reason'] = $data['err'];
		
		print json_encode($data);
	}
	
	function ajax_check_do_no($do_id=null,$branch_id=null){
		global $con;

		if($do_id==null) $cond="inv_no=".ms($_REQUEST['do_no']);
		else $cond="id=".mi($do_id)." and branch_id=".mi($branch_id);

		$q1 = $con->sql_query("select * from do where ".$cond . " and active = 1 and approved = 1 and checkout = 1 and status = 1");

		$data = $con->sql_fetchassoc($q1);

		if($data){

			if($data['do_branch_id']){
				$q1 = $con->sql_query("select * from branch where id=".mi($data['do_branch_id']));
				$data['branch_info'] = $con->sql_fetchassoc($q1);
			}
			if($data['do_type']=='open') $data['open_info']=unserialize($data['open_info']);
			if($data['debtor_id']){
				$q1 = $con->sql_query("select * from debtor where id=".mi($data['debtor_id']));
				$data['debtor_info'] = $con->sql_fetchassoc($q1);
			}


			$q1 = $con->sql_query("select tdi.*, sku_items.id as sku_item_id, sku_items.sku_item_code, sku_items.description as description,
								  sku_items.artno, sku_items.mcode, uom.id as uom_id, uom.code as uom_code, ((tdi.ctn*uom.fraction)+tdi.pcs) as do_total_qty
								  from do_items tdi
								  left join sku_items on tdi.sku_item_id=sku_items.id
									left join sku on sku_items.sku_id = sku.id
									left join uom on uom.id=tdi.uom_id
									left join uom si_uom on si_uom.id = sku_items.packing_uom_id
								  where do_id=".mi($data['id'])."
								  and branch_id=".mi($data['branch_id']));

			while($r=$con->sql_fetchassoc($q1)){
				$data['items'][$r['id']]=$r;
			}
		}

		if($do_id==null) print json_encode($data);
		else return $data;
	}

	function ajax_show_do_item(){
		global $con,$smarty;

		$id=$_REQUEST['do_id'];
		$branch_id=$_REQUEST['branch_id'];
		$do_item_id=$_REQUEST['do_item_id'];
		
		$con->sql_query("select id, code, fraction from uom where active order by code");
		$smarty->assign("uom", $con->sql_fetchrowset());

		$form=load_do_header($id, $branch_id);

		$do_items = load_do_items($id, $branch_id,$form);

		foreach($do_items as $k=>$v){
			if($v['id']!=$do_item_id) unset($do_items[$k]);
		}
		
		$branch_list = array();
		$con->sql_query("select id,code from branch");
		while($r = $con->sql_fetchassoc()){
			$branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		$smarty->assign('branch_list', $branch_list);
		$smarty->assign("form", $form);
		$smarty->assign("do_type", $form['do_type']);
		$smarty->assign("do_items", $do_items);

		$smarty->display('cnote.do_item.tpl');
	}

	function do_reset(){
		global $appCore, $sessioninfo;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		$params['branch_id'] = $sessioninfo['branch_id'];
		$params['reason'] = trim($form['reason']);
		
		$data = $appCore->cnoteManager->resetCNote($form['branch_id'], $form['id'], $params);
		if($data['err']){
			display_redir($_SERVER['PHP_SELF'], $this->title, $data['err']);
		}	
		
		if($data['ok']){
			header("Location: $_SERVER[PHP_SELF]?t=reset&id=$form[id]");
		}else{
			display_redir($_SERVER['PHP_SELF'], $this->title, "Reset Failed");
		}
		
		exit;
	}
	
	function print_cn(){
		global $appCore, $sessioninfo, $smarty;
		
		$bid = mi($_REQUEST['branch_id']);
		$cn_id = mi($_REQUEST['id']);
		
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		$data = $appCore->cnoteManager->generateCNotePrinting($bid, $cn_id, $params);
		
		if($data['html']){
			print $data['html'];exit;
		}else{
			$err = trim($data['err']);
			if(!$err)	$err = "Document failed to print.";
			display_redir($_SERVER['PHP_SELF'], $this->title, $err);
		}
	}
	
	function ajax_check_inv_no(){
		global $LANG, $con, $config, $appCore;
		$row_id = $_REQUEST["tmp_row_id"];
		$sku_item_id = $_REQUEST["items_list"][$row_id]["sku_item_id"];
		$return_inv_no = $_REQUEST["return_inv_no"];
		$branch_id = $_REQUEST["branch_id"];
		
		$ret = array();	
		
		if($config['enable_gst']){
			$itemIsInclusiveTaxString = $appCore->gstManager->itemIsInclusiveTaxString;
			$leftJoinOutputGSTString = $appCore->gstManager->leftJoinOutputGSTString;
			$gst_col_select = ",tdi.gst_id as gst_id, tdi.gst_code as gst_code, tdi.gst_rate as gst_rate, $itemIsInclusiveTaxString as sku_inclusive_tax";
		}
		
		$sql="select tdi.id, tdi.do_id, do.do_date, do.inv_no, tdi.pcs, tdi.ctn, ((tdi.ctn*uom.fraction)+tdi.pcs) as do_total_qty,
								tdi.uom_id,uom.fraction as uom_fraction,
								if(do.is_under_gst,tdi.display_cost_price, tdi.cost_price) as selling_price $gst_col_select
								from do_items tdi
								left join sku_items si on tdi.sku_item_id=si.id
								left join sku on si.sku_id = sku.id
								left join uom on uom.id=tdi.uom_id
								left join uom si_uom on si_uom.id = si.packing_uom_id
								left join do on do.id = tdi.do_id and do.branch_id = tdi.branch_id
								left join category_cache cc on cc.category_id=sku.category_id
								$leftJoinOutputGSTString";
		$q1 = $con->sql_query("select * from tmp_cnote_items
								where cnote_id = " . mi($_REQUEST["id"]) . "
								and return_inv_no = " . ms($return_inv_no) . "
								and branch_id = " . mi($branch_id) . "
								and sku_item_id = " . mi($sku_item_id) . "
								and edit_time = " . ms($_REQUEST["edit_time"]));
		if($con->sql_numrows($q1)){
			while($r=$con->sql_fetchassoc($q1)){
				$q2 = $con->sql_query($sql . " where do.inv_no=" . ms($return_inv_no) . "
								and tdi.branch_id=" . mi($branch_id) . "
								and tdi.sku_item_id=" . mi($sku_item_id) . "
								and do.active = 1 and do.approved = 1 and do.checkout = 1 and do.status = 1");
				if($con->sql_numrows($q2)){
					while($item=$con->sql_fetchassoc($q2)){
						$upd = array();
						$upd['price'] = $item['selling_price'];
						$upd['uom_id'] = $item['uom_id'];
						$upd['uom_fraction'] = $item['uom_fraction'];
					
						// GST
						if($config['enable_gst']){
							if($_REQUEST['is_under_gst']){
								if(!$item['gst_id']){	// return invoice is not under gst
									// gst GST OS
									$gstOS = $appCore->gstManager->getGstOS();
									$item['gst_id'] = $gstOS['id'];
									$item['gst_code'] = $gstOS['code'];
									$item['gst_rate'] = $gstOS['rate'];
								}
								
								$upd['gst_id'] = $item["gst_id"];
								$upd['gst_code'] = $item["gst_code"];
								$upd['gst_rate'] = $item["gst_rate"];
								
								$upd['display_price'] = $upd['price'];
								$upd['display_price_is_inclusive'] = $item['sku_inclusive_tax']=='yes'? 1 : 0;
								
								
								if($upd['display_price_is_inclusive']){	// is inclusive tax
									$gst_amt = $upd['display_price'] / ($item['gst_rate']+100) * $item['gst_rate'];
									$upd['price'] = $upd['display_price'] - $gst_amt;
								}
							}
						}
						
						$upd['return_do_id'] = $r["return_do_id"];
						$upd['return_inv_date'] = $r["return_inv_date"];
						$upd['return_inv_no'] = $r["return_inv_no"];
						$upd['do_item_id'] = $item["id"];
						$upd["pcs"]=$item["pcs"];
						$upd["ctn"]=$item["ctn"];
						$upd["do_total_qty"]=$item["do_total_qty"];
						$ret["id"] = $row_id;
						$ret['items'][$row_id]=$upd;
					}
				}else{
					$ret["err"] = $LANG["CNOTE_INVALID_INV_NO"];
				}
				$con->sql_freeresult($q2);
			}
		}else{
			$q2 = $con->sql_query($sql . " where do.inv_no=" . ms($return_inv_no) . "
									and tdi.branch_id=" . mi($branch_id) . "
									and tdi.sku_item_id=" . mi($sku_item_id) . "
									and do.active = 1 and do.approved = 1 and do.checkout = 1 and do.status = 1");
			
			if($con->sql_numrows($q2)){
				while($item=$con->sql_fetchassoc($q2)){
					$upd = array();
					$upd['return_do_id'] = $item["do_id"];
					$upd['return_inv_date'] = $item["do_date"];
					$upd['return_inv_no'] = $item["inv_no"];
					$upd['do_item_id'] = $item["id"];
					$upd['price'] = $item['selling_price'];
					$upd['uom_id'] = $item['uom_id'];

					// GST
					if($config['enable_gst']){
						if($_REQUEST['is_under_gst']){
							if(!$item['gst_id']){	// return invoice is not under gst
								// gst GST OS
								$gstOS = $appCore->gstManager->getGstOS();
								$item['gst_id'] = $gstOS['id'];
								$item['gst_code'] = $gstOS['code'];
								$item['gst_rate'] = $gstOS['rate'];
							}
							
							$upd['gst_id'] = $item["gst_id"];
							$upd['gst_code'] = $item["gst_code"];
							$upd['gst_rate'] = $item["gst_rate"];
								
							$upd['display_price'] = $upd['price'];
							$upd['display_price_is_inclusive'] = $item['sku_inclusive_tax']=='yes'? 1 : 0;
							
							
							if($upd['display_price_is_inclusive']){	// is inclusive tax
								$gst_amt = $upd['display_price'] / ($item['gst_rate']+100) * $item['gst_rate'];
								$upd['price'] = $upd['display_price'] - $gst_amt;
							}
						}	
					}
					
					// update back cnote
					$con->sql_query("update tmp_cnote_items set ".mysql_update_by_field($upd)." where branch_id=".mi($branch_id)." and id=" . mi($row_id));
					$upd["pcs"]=$item["pcs"];
					$upd["ctn"]=$item["ctn"];
					$upd["do_total_qty"]=$item["do_total_qty"];
					$upd['uom_fraction'] = $item['uom_fraction'];
					$ret["id"] = $row_id;
					$ret['items'][$row_id]=$upd;
				}
			}else{
				$ret["err"] = $LANG["CNOTE_INVALID_INV_NO"];
			}
			$con->sql_freeresult($q2);
		}
		$con->sql_freeresult($q1);
		
		print json_encode($ret);
	}
}

$ARMS_CN = new ARMS_CN();
?>
