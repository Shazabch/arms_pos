<?php
/*
23/9/2019 11:38 AM William 
- Added new module Purchase Order.

21/10/2019 5:26 PM William
- Enhanced to add Delivery date and Cancellation date.
- Add new checking for check config "enable_po_agreement".

12/10/2019 2:00 PM William
- Fixed pda po module not checking block item in PO block list.
*/
include("common.php");
include("class.scan_product.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/pda/index.php");
if (!privilege('PO')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO', BRANCH_CODE), "/pda");

class PO_Module extends Scan_Product{
    function __construct($title){
        global $sessioninfo;
		
		$_SESSION['scan_product']['type'] = 'PO';
		$_SESSION['scan_product']['name'] = isset($_SESSION['po']['id']) ? 'PO#'.$_SESSION['po']['id'] : '';
	    if(isset($_REQUEST['branch_id'])){
			if($_REQUEST['branch_id'] != $sessioninfo['branch_id']){
				header("Location: $_SERVER[PHP_SELF]");
				exit;
			}
		}
		parent::__construct($title);
	}
	
	function init_module(){
	    global $con, $sessioninfo, $smarty;
		
		$id = mi($_SESSION['po']['id']);
		$branch_id = mi($_SESSION['po']['branch_id']);
		
		//for scan product result page scan multi branch
		if($id && $branch_id && $sessioninfo['branch_id'] == 1){
			$q3 = $con->sql_query("select po_branch_id,deliver_to from po where id=$id and branch_id=$branch_id");
			$r3 = $con->sql_fetchassoc($q3);
			$con->sql_freeresult($q3);
			if((!$r3['po_branch_id'] || $r3['po_branch_id'] == 0)&& $r3['deliver_to']){ // for multi branch
				$deliver_bid = $this->get_po_branch($id, $bid);
				if($deliver_bid){
					foreach($deliver_bid as $bid){
						$branch_code[] = get_branch_code($bid);
					}
				}
				$multi_branch['branch_code'] = $branch_code;
				$multi_branch['branch_id'] = $deliver_bid;
				$smarty->assign('deliver_to',$multi_branch);
			}
		}
		$smarty->assign('module_name','Purchase Order');
		$smarty->assign('PAGE_TITLE','PO');
		$smarty->assign('top_include','po.top_include.tpl');
		// for display po items and pcs
		$smarty->assign('btm_include','po.btm_include.tpl');
	}
	
	function default_(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		
		if($id&&$branch_id){
			$this->reset_session_po($id,$branch_id);
		}else{
            $id = mi($_SESSION['po']['id']);
            $branch_id = mi($_SESSION['po']['branch_id']);
		}
		
		if($id>0){
			$this->show_scan_product();
		}else{
			$this->show_setting();
		}
	}
	
	function new_po(){
		unset($_SESSION['po']);
		if($_REQUEST['po_type'])	$_SESSION['po']['po_type'] = $_REQUEST['po_type'];
		header("Location: po.php");
	}
	
	private function default_load(){
		global $con, $smarty, $sessioninfo;
		// all branches
		$con->sql_query("select * from branch where active=1 order by sequence,code") or die(mysql_error());
		while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$branches);
		
		//load vendor 
		$con->sql_query("select * from vendor where active=1 order by code",false,false);
		if($con->sql_numrows()>0){
			while($r = $con->sql_fetchrow()){
				$vendor[$r['id']] = $r;
			}
			$con->sql_freeresult();
			$smarty->assign('vendor',$vendor);
		}
		
		//load department
		if ($sessioninfo['level'] < 9999){
			if (!$sessioninfo['departments']) $depts = "id in (0)";
			else $depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
		}else{
			$depts = 1;
		}
		$con->sql_query("select id, description from category where active = 1 and level = 2 and $depts order by description");
		$dept = $con->sql_fetchrowset();
		$con->sql_freeresult();
		$smarty->assign("dept",$dept);
	}
	
	function show_setting(){
		global $con, $smarty;
		
		$this->default_load();
		
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['po_branch_id']);
		
		if(!$id||!$branch_id){
            $id = mi($_SESSION['po']['id']);
            $branch_id = mi($_SESSION['po']['branch_id']);
		}
		
		if($id>0&&$branch_id>0){
		    $this->reset_session_po($id,$branch_id);
			$con->sql_query("select * from po where id=$id and branch_id=$branch_id") or die(mysql_error());
			$form = $con->sql_fetchassoc();
			if($form['po_branch_id']){
				$po_bid = mi($form['po_branch_id']);
				$form['po_branch_code'] = get_branch_code($po_bid);
			}else{
				$bid = mi($form['branch_id']);
				$form['po_branch_code'] = get_branch_code($bid);
			}
			$con->sql_freeresult();
			$form['deliver_to'] = unserialize($form['deliver_to']);
			
			if(is_array($form['deliver_to'])){ // for multi branch
				$form['delivery_date']= unserialize($form['delivery_date']);
				$form['cancel_date']= unserialize($form['cancel_date']);
				foreach($form['deliver_to'] as $k=>$v){
					if($form['delivery_date'][$v]) $form['delivery_date'][$v] = dmy_to_sqldate($form['delivery_date'][$v]);
					if($form['cancel_date'][$v]) $form['cancel_date'][$v] = dmy_to_sqldate($form['cancel_date'][$v]);
				}
			}else{
				$form['delivery_date'] = dmy_to_sqldate($form['delivery_date']);
				$form['cancel_date'] = dmy_to_sqldate($form['cancel_date']);
			}
			$form['dept_id'] = $form['department_id'];
			$smarty->assign('form', $form);
		}
		if(!empty($form)){
			$smarty->assign('disable_sett','1');
		}
		$_REQUEST['po_tab'] = 'setting';
		$smarty->display('po.index.tpl');
	}
	
	//create new po, not include po items
	function save_setting(){
		global $con, $smarty, $sessioninfo, $config, $LANG;
		$form = $_REQUEST;
		$id = mi($form['id']);
		$branch_id = mi($form['branch_id']);
		$vendor_id = mi($form['vendor_id']);
		$dept_id = mi($form['dept_id']);
		$po_date = $form['po_date'];
		$delivery_date = $form['delivery_date'];
		$cancel_date = $form['cancel_date'];
		
		$upd = array();
		$err = array();
		$upd['vendor_id'] = $vendor_id;
		$upd['department_id'] = $dept_id;
		$upd['po_date'] = $po_date;
		$upd['user_id'] = $sessioninfo['id'];
		if($sessioninfo['branch_id'] == 1){
			$upd['po_option'] = 2;
			$upd['deliver_to'] = serialize($form['deliver_to']);
			foreach($delivery_date as $k=>$date){
				if(in_array($k,$form['deliver_to'])){
					$delivery_date[$k] = date('d/m/Y',strtotime($delivery_date[$k]));
					$cancel_date[$k] = date('d/m/Y',strtotime($cancel_date[$k]));
					//check delivery date and cancellation date
					if($delivery_date[$k]=='' || dmy_to_time($delivery_date[$k])<=0) $err[] = sprintf($LANG['PO_INVALID_DATE'], 'Delivery Date');
					if($cancel_date[$k]=='' || dmy_to_time($cancel_date[$k])<=0) $err[] = sprintf($LANG['PO_INVALID_DATE'], 'Cancellation Date');
					if(dmy_to_time($delivery_date[$k]) < strtotime($upd['po_date'])) $err[] = sprintf($LANG['PO_INVALID_DATE'], 'Delivery is before PO Date');
					if(dmy_to_time($delivery_date[$k]) > dmy_to_time($cancel_date[$k])) $err[] = sprintf($LANG['PO_INVALID_DATE'], 'Delivery is after Cancellation Date');
				}
			}
		}else{
			$delivery_date = date('d/m/Y',strtotime($delivery_date));
			$cancel_date = date('d/m/Y',strtotime($cancel_date));
			$upd['po_branch_id'] = $sessioninfo['branch_id'];
			//check delivery date and cancellation date
			if($delivery_date == '' || dmy_to_time($delivery_date)<=0)  $err[] = sprintf($LANG['PO_INVALID_DATE'], 'Delivery Date');
			if($cancel_date == '' || dmy_to_time($cancel_date)<=0) $err[] = sprintf($LANG['PO_INVALID_DATE'], 'Cancellation Date');
			if(dmy_to_time($delivery_date) < strtotime($upd['po_date'])) $err[] = sprintf($LANG['PO_INVALID_DATE'], 'Delivery is before PO Date');
			if(dmy_to_time($delivery_date) > dmy_to_time($cancel_date)) $err[] = sprintf($LANG['PO_INVALID_DATE'], 'Delivery is after Cancellation Date');
		}
		
		// validating
		if(!$upd['vendor_id']) $err[] = "Invalid Vendor";
		if(!$upd['department_id']) $err[] = "Invalid Department";
		if(!$upd['po_date']) $err[] = "Invalid PO Date";
		if(!$form['deliver_to'] && $branch_id==1) $err[] = "Please select branch";
		if($err){
			$this->default_load();
			$err = array_unique($err);
			$smarty->assign("form",$form);
			$smarty->assign("err",$err);
			$smarty->display("po.index.tpl");
			exit;
		}
		//add new PO
		if(is_array($form['deliver_to'])){
			$delivery_date = serialize($delivery_date);
			$cancel_date = serialize($cancel_date);
		}
		$upd['delivery_date'] = $delivery_date;
		$upd['cancel_date'] = $cancel_date;
		$upd['branch_id'] = $branch_id;
		$upd['added'] = 'CURRENT_TIMESTAMP';
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into po ".mysql_insert_by_field($upd)) or die(mysql_error());
		$id = $con->sql_nextid();
		
		$this->reset_session_po($id,$branch_id);
		header("Location: po.php");
	}
	
	private function reset_session_po($id,$branch_id){
	    global $con;
	    
	    $con->sql_query("select id,branch_id,po_branch_id from po where id=$id and branch_id=$branch_id") or die(mysql_error());
		$form = $con->sql_fetchrow();
		if(!$form)	js_redirect('Invalid PO', "index.php");
        $_SESSION['po'] = $form;
	}
	
	function show_scan_product(){
		global $con, $smarty, $sessioninfo, $config, $LANG;
		$err = array();
		$id = mi($_SESSION['po']['id']);
        $branch_id = mi($_SESSION['po']['branch_id']);
		// check PO exists or not
		$con->sql_query($q2 = "select * from po where id=$id and branch_id=$branch_id and active=1") or die(mysql_error());
		$form = $con->sql_fetchrow();
		$con->sql_freeresult();
		
		if(!$form){
			js_redirect('Invalid PO', "index.php");
            exit;
		}
		
		$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
		$report_prefix = $con->sql_fetchfield(0);
		$po_no = $report_prefix.sprintf('%05d',$id);
		
		$_SESSION['scan_product']['type'] = 'PO';
		$_SESSION['scan_product']['name'] = 'PO#'.$id;

		$product_code = strtoupper($_REQUEST['product_code']);
		// cut last digit
		$product_code2 = strtoupper(substr($product_code,0,strlen($product_code)-1));
		
		$filter = array();
		$filter_or[] = "(si.mcode=".ms($product_code)." or si.mcode=".ms($product_code2).")";
		$filter_or[] = "(si.link_code=".ms($product_code)." or si.link_code=".ms($product_code2).")";
		$filter_or[] = "(si.sku_item_code=".ms($product_code)." or si.sku_item_code=".ms($product_code2).")";
		$filter_or[] = "(si.artno=".ms($product_code)." or si.artno=".ms($product_code2).")";
		$filter[] = "(".join(' or ',$filter_or).")";
		$filter[] = "si.active=1";
		$filter = join(' and ', $filter);

		$sql = $con->sql_query("select si.* from sku_items si where $filter");

		if($con->sql_numrows($sql) == 1 && $_REQUEST['auto_add_item']){
			$sku_info = $con->sql_fetchassoc($sql);
			$con->sql_freeresult($sql);
			$q3 = $con->sql_query("select po_branch_id,deliver_to from po where id=$id and branch_id=$branch_id");
			$r3 = $con->sql_fetchassoc($q3);
			$con->sql_freeresult($q3);
			
			if($config['enable_po_agreement']){
				// do not allow to add item if got purchase agreement
				$con->sql_query("select pai.id 
				from purchase_agreement_items pai
				left join purchase_agreement pa on pa.branch_id=pai.branch_id and pa.id=pai.purchase_agreement_id
				where pai.sku_item_id=".mi($sku_info['id'])." and pa.active=1 and pa.status=1 and pa.approved=1 limit 1");
				$pai = $con->sql_fetchassoc();
				$con->sql_freeresult();
				if($pai){
					$err[] = $LANG['PO_NOT_ALLOW_OPEN_BUY_ITEM_WITH_PURCHASE_AGREEMENT'];
				}else{
					// check foc item 
					$pa_sql = "select pafi.id
					from purchase_agreement_foc_items pafi
					join purchase_agreement pa on pa.branch_id=pafi.branch_id and pa.id=pafi.purchase_agreement_id
					where pafi.sku_item_id=".mi($sku_info['id'])." and pa.active=1 and pa.status=1 and pa.approved=1 limit 1";
					$con->sql_query($pa_sql);
					$pai2 = $con->sql_fetchassoc();
					$con->sql_freeresult();
					if($pai2) $err[] = $LANG['PO_NOT_ALLOW_OPEN_BUY_ITEM_WITH_PURCHASE_AGREEMENT'];
				}
			}
			
			//check the Block item in PO
			$block_list = unserialize($sku_info['block_list']);
			if($block_list[$sessioninfo['branch_id']]){
				$err[] = $LANG['PO_ITEM_IS_BLOCKED'];
			}
			
			if($sessioninfo['branch_id'] == 1 && !$r3['po_branch_id'] && $r3['deliver_to']){ // for multi branch
				$deliver_bid = $this->get_po_branch($id,$branch_id);
				foreach($deliver_bid as $key=>$bid){
					$_REQUEST['item_qty'][$sku_info['id']][$bid] = 1;
				}
			}else{
				$_REQUEST['item_qty'][$sku_info['id']] = 1;
			}
			if(!$err){
				$this->add_items();
				unset($_REQUEST);
			}
			$this->err = array_merge($this->err, $err);
			$smarty->assign("auto_add", 1);
		}
		
		$this->get_item_info();
		$this->search_product();
	}
	
	function get_item_info(){ // get item info for count total item and qty
		global $con, $smarty, $sessioninfo;
		$id = mi($_SESSION['po']['id']);
        $branch_id = mi($_SESSION['po']['branch_id']);
		
		// get item info
		$item_qty = array();
		$q3 = $con->sql_query("select po_branch_id,deliver_to from po where id=$id and branch_id=$branch_id");
		$r3 = $con->sql_fetchassoc($q3);
		$con->sql_freeresult($q3);
		if($branch_id == 1 && !$r3['po_branch_id'] && $r3['deliver_to']){
			$q1 = $con->sql_query("select qty_loose_allocation from po_items where po_id=$id and branch_id=$branch_id and (cost_indicate<> 'PA' or cost_indicate is null)") or die(mysql_error());
			$item_qty['total_item'] = $con->sql_numrows($q1);
			while($r1 = $con->sql_fetchassoc($q1)){
				$qty_pcs = unserialize($r1['qty_loose_allocation']);
				if($qty_pcs){
					foreach($qty_pcs as $bid=>$qty){
						$item_qty['total_pcs'] += $qty;
					}
				}
			}
			$con->sql_freeresult($q1);
		}else{
			$q1 = $con->sql_query("select count(*) as total_item,sum(qty_loose) as total_pcs from po_items where po_id=$id and branch_id=$branch_id  and (cost_indicate<> 'PA' or cost_indicate is null)") or die(mysql_error());
			$r1 = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$item_qty['total_item'] = $r1['total_item'];
			$item_qty['total_pcs'] = $r1['total_pcs'];
		}
		$smarty->assign('items_details',$item_qty);
	}
	
	function add_items(){
		global $con, $config, $sessioninfo, $gst_list;
        $id = mi($_SESSION['po']['id']);
        $branch_id = mi($_SESSION['po']['branch_id']);
        $po_branch_id = mi($_SESSION['po']['po_branch_id']);
        $upd = array();
        $upd['po_id'] = $id;
        $upd['branch_id'] = $branch_id;
        $upd['user_id'] = $sessioninfo['id'];
		
		$items = $_REQUEST['item_qty'];
		$foc_qty = $_REQUEST['foc_qty'];
		if($items || $foc_qty){
		    $total_qty = 0;
			$total_foc = 0;
			$q3 = $con->sql_query("select po_branch_id,deliver_to from po where id=$id and branch_id=$branch_id");
			$r3 = $con->sql_fetchassoc($q3);
			$con->sql_freeresult($q3);
			if($sessioninfo['branch_id'] == 1 && !$r3['po_branch_id'] && $r3['deliver_to']){ // for multi branch
				foreach($items as $sid=>$branch_list){
					if($branch_list){
						foreach($branch_list as $bid=>$qty){
							$deliver_bid = array_keys($branch_list);
							if($items[$sid][$bid] > 0 ||($items[$sid][$bid]==0 && $_REQUEST['empty_decimal_points'])){   //check qty
								$sku_item_id[] = $sid;
								$total_qty += $qty;
								$item_need_add[$sid]['qty']= $branch_list;
							}
							if($foc_qty[$sid][$bid] > 0 ||($foc_qty[$sid][$bid]==0 && $_REQUEST['empty_decimal_points'])){  //check foc qty
								$sku_item_id[] = $sid;
								$total_foc += $foc_qty[$sid][$bid];
								$item_need_add[$sid]['foc_qty']= $foc_qty[$sid];
							}
						}
					}
				}
			}else{   //for single branch
				$deliver_bid[] = $branch_id;
				foreach($items as $sid=>$qty){
					if($qty>0 || ($qty==0 && $_REQUEST['empty_decimal_points'])){   //check qty
						$sku_item_id[] = $sid;
						$item_need_add[$sid]['qty'] = $qty;
						$total_qty += $qty;
					}
					if($foc_qty[$sid]>0 ||($foc_qty[$sid]==0 && $_REQUEST['empty_decimal_points'])){   //check foc qty
						$sku_item_id[] = $sid;
						$item_need_add[$sid]['foc_qty'] = $foc_qty[$sid];
						$total_foc += $foc_qty[$sid];
					}
				}
			}
			
			if(($total_qty<=0 && !$_REQUEST['empty_decimal_points']) && $total_foc<=0){
                $ret['error'][] = "Invalid quantity";
			}else{		//get po info
				$qry_po = $con->sql_query("select * from po where id=$id and branch_id=$branch_id") or die(mysql_error());
				$r_po = $con->sql_fetchassoc($qry_po);
				$con->sql_freeresult($qry_po);
				$r_po['deliver_to'] = unserialize($r_po['deliver_to']);
				
				if(BRANCH_CODE!='HQ'){ // create from sub branch - single branch delivery
					$branch_chk=" grn_items.branch_id=$branch_id and ";
					$grn_filter[] = "grn_items.branch_id=$branch_id"; 
					$qc_filters[] ="sivqch.branch_id = ".mi($branch_id);
				}
				elseif($r_po['po_branch_id'] && !is_array($r_po['deliver_to'])){ // single delivery branch 
					$bid=intval($r_po['po_branch_id']);
					$branch_chk=" grn_items.branch_id=$bid and ";
					$grn_filter[] = "grn_items.branch_id=$bid";
					$qc_filters[] ="sivqch.branch_id = ".mi($bid);
				}else{ // multi delivery branch
					if(is_array($r_po['deliver_to']) && count($r_po['deliver_to']) == 1) $qc_filters[] ="sivqch.branch_id = ".mi($r_po['deliver_to'][0]);
					else $qc_filters[] ="sivqch.branch_id = ".mi($branch_id);
				}
			
				$qc_filters[] = "sivqch.vendor_id = ".mi($r_po['vendor_id']);
				$qc_filters[] = "sivqch.added <= ".ms($r_po['po_date']." 23:59:59");
				$grn_filter[] = "grn.approved=1 and grn.active=1 and grr.active=1";
				$grn_filter[] = "grr_items.type<>'DO'";
			
				$grn_filter = join(' and ', $grn_filter);
				$filter = join(" and ", $qc_filters);
				
				//get sku item detail
				foreach($sku_item_id as $key=>$sid){
					//get quotation cost if exist
					$q1 = $con->sql_query($qry2="select sivqch.* from sku_items_vendor_quotation_cost_history sivqch join sku_items si on sivqch.sku_item_id=si.id where $filter and sivqch.sku_item_id = ".mi($sid)." order by sivqch.added desc limit 1");
					$qc_info = $con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);
					if($qc_info['cost']>0){
						$quotation[$sid] = $qc_info['cost'];
					}
					
					//get sku item from grn
					$sql = "select si.*, grn.vendor_id as vendor_id, if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost) as order_price, grn_items.uom_id as uom_id, si.packing_uom_id as selling_uom_id, uom.fraction as order_uom_fraction, u1.fraction as selling_uom_fraction,si.packing_uom_id as master_uom_id,u1.code as packing_uom, grr_items.type as grr_item_type,do.do_type,sai.photo_count, u1.fraction as master_uom_fraction, sku.category_id, grn_items.gst_id as cost_gst_id, grn_items.gst_rate as cost_gst_rate, grn_items.gst_code as cost_gst_code, grn.branch_id as grn_bid, grn.vendor_id as grn_vd_id
					from grn_items
					left join sku_items si on sku_item_id = si.id
					left join sku on sku.id = si.sku_id
					left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
					left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
					left join uom u1 on si.packing_uom_id = u1.id
					left join uom on grn_items.uom_id = uom.id
					left join grr_items on grr_items.id=grn.grr_item_id and grr_items.branch_id=grn.branch_id
					left join do on do.do_no=grr_items.doc_no
					left join sku_apply_items sai on sai.id=si.sku_apply_items_id
					where $grn_filter and sku_item_id=".mi($sid)."
					having order_price>0
					order by grr.rcv_date desc, grr.id desc limit 1";
					$result=$con->sql_query($sql);
					$selection = "GRN";
					
					// if found grn item, get quotation cost base on grn data
					if($con->sql_numrows($result)==0){
						if (BRANCH_CODE!='HQ'){
							$branch_chk = " po_items.branch_id=$branch_id and ";
						}
						elseif ($form['po_branch_id'] && !is_array($form['deliver_to'])){
							$bid=intval($form['po_branch_id']);
							$branch_chk = " po_items.branch_id=$bid and ";
						}
						
						//get from po
						$result=$con->sql_query("select si.*, po.vendor_id as vendor_id, po_items.order_price as order_price, po_items.order_uom_id as uom_id, si.packing_uom_id as selling_uom_id, uom.fraction as order_uom_fraction, u1.fraction as selling_uom_fraction,si.packing_uom_id as master_uom_id,u1.code as packing_uom, sai.photo_count, u1.fraction as master_uom_fraction, sku.category_id, po_items.selling_gst_id, po_items.selling_gst_rate, 
						po_items.selling_gst_code, po_items.cost_gst_id, po_items.cost_gst_rate, po_items.cost_gst_code	
						from po_items
						left join sku_items si on sku_item_id = si.id
						left join sku on sku.id = si.sku_id
						left join po on po_id = po.id and po.branch_id = po_items.branch_id
						left join uom u1 on si.packing_uom_id = u1.id
						left join uom on order_uom_id = uom.id
						left join sku_apply_items sai on sai.id=si.sku_apply_items_id
						where po.active=1 and po.approved=1 and $branch_chk sku_item_id=".mi($sid)." 
						having order_price>0 
						order by po.po_date desc, po.id desc limit 1");
						$selection="PO";
					}
					if($con->sql_numrows($result)==0){//get from master
						$result=$con->sql_query("select sku.vendor_id, si.*, si.cost_price as order_price, si.cost_price as resell_price, u1.fraction as selling_uom_fraction, si.packing_uom_id as selling_uom_id ,si.packing_uom_id as master_uom_id,u1.code as packing_uom, sai.photo_count, u1.fraction as master_uom_fraction, sku.category_id
						from sku_items si
						left join sku on si.sku_id = sku.id 
						left join uom u1 on si.packing_uom_id = u1.id
						left join sku_apply_items sai on sai.id=si.sku_apply_items_id where si.id = ".mi($sid));
						$selection = "SKU";
					}
					$r = $con->sql_fetchassoc($result);
					$con->sql_freeresult($result);
					$sku_items[$sid] = $r;
					$bid_list= array();
					if($r_po['deliver_to']){ 
						$bid_list = $r_po['deliver_to'];
					}else{
						$bid_list[] = $r_po['po_branch_id'];
					}
					
					foreach($bid_list as $key=>$bid){
						$q_p =$con->sql_query("select price from sku_items_price where sku_item_id=".mi($sid)." and branch_id=".mi($bid));
						$r_p = $con->sql_fetchassoc($q_p);
						if($r_p){
							$sku_items[$sid]['b_selling_price'][$bid] = $r_p['price'];
						}
						$con->sql_freeresult($q_p);
					}
					if($qc_info['cost']>0){
						$sku_items[$sid]['order_price'] = $qc_info['cost'];
						$selection = "QUOTATION";
					}
					$sku_items[$sid]['cost_indicate'] = $selection;
				}
				
				if($sessioninfo['branch_id'] == 1 && !$r3['po_branch_id'] && $r3['deliver_to']){  //for multi branch
					foreach($item_need_add as $sid=>$qty_type){
						foreach($qty_type as $type=>$branch_list){
							$upd['qty_loose_allocation'] = serialize($item_need_add[$sid]['qty']);
							$upd['foc_loose_allocation'] = serialize($item_need_add[$sid]['foc_qty']);
							foreach($branch_list as $bid=>$value){
								if($sku_items[$sid]['b_selling_price'][$bid]){
									$selling_price[$bid] = $sku_items[$sid]['b_selling_price'][$bid];
								}else{
									$selling_price[$bid] = $sku_items[$sid]['selling_price'];
								}
							}
						}
						$upd['selling_price_allocation'] = serialize($selling_price);
						$upd['sku_item_id'] = $sid;
						if($sku_items[$sid]['uom_id']){
							$upd['order_uom_id'] = $sku_items[$sid]['uom_id'];
						}else{
							$upd['order_uom_id'] = '1';
						}			
						$upd['selling_uom_id'] = $sku_items[$sid]['selling_uom_id'];
						if($sku_items[$sid]['selling_uom_fraction'] == 0) $upd['selling_uom_fraction'] = 1;
						if($sku_items[$sid]['order_uom_fraction']==0) $upd['order_uom_fraction'] = 1;
						if($sku_items[$sid]['artno']){
							$upd['artno_mcode'] = $sku_items[$sid]['artno'];
						}else{
							$upd['artno_mcode'] = $sku_items[$sid]['mcode'];
						}
						$upd['order_price'] = $sku_items[$sid]['order_price'];
						$upd['cost_indicate'] = $sku_items[$sid]['cost_indicate'];
						$pi_info = array();
						if(!$config['po_item_allow_duplicate']){
							$q1 = $con->sql_query("select id,po_id,sku_item_id,qty_loose_allocation,foc_loose_allocation,branch_id,qty_loose,foc_loose from po_items where po_id=$id and branch_id=$branch_id and sku_item_id=".mi($sid));
							$pi_info = $con->sql_fetchassoc($q1);
							$con->sql_freeresult($q1);
						}
						
						//check config
						if(!$config['po_item_allow_duplicate'] && $pi_info['id']){
							//get exist qty and foc value
							$po_items['qty_loose_allocation'] = unserialize($pi_info['qty_loose_allocation']);
							$po_items['foc_loose_allocation'] = unserialize($pi_info['foc_loose_allocation']);
							//current po item addded value
							$qty_loose_allocation = unserialize($upd['qty_loose_allocation']);
							$foc_loose_allocation = unserialize($upd['foc_loose_allocation']);
							foreach($po_items as $qty_type=>$bidlist){
								foreach($bidlist as $bid2=>$qty){
									$qty_loose_allocation2[$bid2] = $qty_loose_allocation[$bid2]+$po_items['qty_loose_allocation'][$bid2];
									$foc_loose_allocation2[$bid2] = $foc_loose_allocation[$bid2]+$po_items['foc_loose_allocation'][$bid2];
									//Remove 0 qty from serialize data
									if($qty_loose_allocation2[$bid2] == 0)  $qty_loose_allocation2[$bid2] ='';
									if($foc_loose_allocation2[$bid2] == 0)  $foc_loose_allocation2[$bid2] ='';
								}
								$upd2['qty_loose_allocation'] = serialize($qty_loose_allocation2);
								$upd2['foc_loose_allocation'] = serialize($foc_loose_allocation2);
								$con->sql_query("update po_items set ".mysql_update_by_field($upd2)." where id=".mi($pi_info['id'])." and po_id=".mi($pi_info['po_id'])." and branch_id=".mi($pi_info['branch_id']));
							}
						}else{
							if($config['po_set_max_items']){
								$con->sql_query("select count(*) from po_items where po_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id]");
								$curr_count = $con->sql_fetchfield(0);
								$con->sql_freeresult();
								if(($curr_count + count($upd['sku_item_id'])) > $config['po_set_max_items']) {
									$ret['error'][] = "Can't add item, maximum Items per this PO is ".$config['po_set_max_items'];
									$this->get_item_info();
									return $ret;
								}									
							}
							$con->sql_query("insert into po_items " .mysql_insert_by_field($upd));
						}
					}
					$this->po_amt_need_update($branch_id, $id);
					$ret['success'] = true;
				}else{ //for single branch
					foreach($item_need_add as $sid=>$qty_type){
						foreach($qty_type as $type=>$value){
							$upd['qty_loose'] = $item_need_add[$sid]['qty'];
							$upd['foc_loose'] = $item_need_add[$sid]['foc_qty'];
						}
						if($sku_items[$sid]['b_selling_price'][$branch_id]){
							$upd['selling_price'] = $sku_items[$sid]['b_selling_price'][$branch_id];
						}else{
							$upd['selling_price'] = $sku_items[$sid]['selling_price'];
						}
						$upd['sku_item_id'] = $sid;
						$upd['order_price'] = $sku_items[$sid]['order_price'];
						$upd['cost_indicate'] = $sku_items[$sid]['cost_indicate'];
						$upd['selling_uom_id'] = $sku_items[$sid]['selling_uom_id'];

						if($sku_items[$sid]['uom_id']){
							$upd['order_uom_id'] = $sku_items[$sid]['uom_id'];
						}else{
							$upd['order_uom_id'] = '1';
						}			
						if($sku_items[$sid]['selling_uom_fraction'] == 0) $upd['selling_uom_fraction'] = 1;
						if($sku_items[$sid]['order_uom_fraction']==0) $upd['order_uom_fraction'] = 1;
						if($sku_items[$sid]['artno']){
							$upd['artno_mcode'] = $sku_items[$sid]['artno'];
						}else{
							$upd['artno_mcode'] = $sku_items[$sid]['mcode'];
						}
						
						$pi_info = array();
						if(!$config['po_item_allow_duplicate']){
							$q1 = $con->sql_query("select id,po_id,sku_item_id,qty_loose,foc_loose,branch_id from po_items where po_id=$id and branch_id=$branch_id and sku_item_id=".mi($sid));
							$pi_info = $con->sql_fetchassoc($q1);
							$con->sql_freeresult($q1);
						}
						
						if(!$config['po_item_allow_duplicate'] && $pi_info['id']){
							$upd2['qty_loose'] = $upd['qty_loose']+$pi_info['qty_loose'];
							$upd2['foc_loose'] = $upd['foc_loose']+$pi_info['foc_loose'];
							$con->sql_query("update po_items set ".mysql_update_by_field($upd2)." where id=".mi($pi_info['id'])." and po_id=".mi($pi_info['po_id'])." and branch_id=".mi($pi_info['branch_id']));
						}else{
							if($config['po_set_max_items']){
								$con->sql_query("select count(*) from po_items where po_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id]");
								$curr_count = $con->sql_fetchfield(0);
								$con->sql_freeresult();
								if(($curr_count + count($upd['sku_item_id'])) > $config['po_set_max_items']) {
									$ret['error'][] = "Can't add item, maximum Items per this PO is ".$config['po_set_max_items'];
									$this->get_item_info();
									return $ret;
								}									
							}
							$con->sql_query("insert into po_items " .mysql_insert_by_field($upd));
						}
					}
					$this->po_amt_need_update($branch_id, $id);
					$ret['success'] = true;
				}
			}
		}else{
            $ret['error'][] = "No items found";
		}
		return $ret;
	}
	
	function open(){
		global $con, $smarty, $sessioninfo;
		
		if(isset($_REQUEST['po_no'])){
			$branch_id = mi($sessioninfo['branch_id']);
			$id = mi($_REQUEST['po_no']);
			$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
			$report_prefix = $con->sql_fetchfield(0);
			
			$filters = array();
			$filters[] = "po.approved<>1 and po.status=0";
			$filter = join(" and ", $filters);

			$sql = "select po.* from po
			left join branch on branch.id=po.branch_id
			left join vendor on vendor.id=po.vendor_id
			where po.branch_id=$branch_id and (po.id=$id or po.po_no=".ms($_REQUEST['po_no']).") and po.active=1 and $filter";
			$q1 = $con->sql_query($sql) or die(mysql_error());
			
			if($con->sql_numrows()<=0){
				$err[] = "No PO Found with $id.";
			}else{
				while($r = $con->sql_fetchassoc($q1)){
					if($r['po_branch_id']){
						$r['branch_code'] = get_branch_code($r['po_branch_id']);
					}else{
						$r['branch_code'] = get_branch_code($r['branch_id']);
					}
					if($branch_id == 1 && $r['deliver_to']){
						$deliver_bid = unserialize($r['deliver_to']);
						foreach($deliver_bid as $bid){
							$deliver_to[] = get_branch_code($bid);
						}
						if(is_array($deliver_to)) $r['deliver_to'] = implode(", ",$deliver_to);
					}
					$po_list[] = $r;
				}
				$con->sql_freeresult($q1);
				$smarty->assign('po_list',$po_list);
			}
		}
		$smarty->assign('err',$err);
		$smarty->display('po.search.tpl');
	}
	
	function view_items(){
		global $con, $sessioninfo, $smarty;
		$id = mi($_SESSION['po']['id']);
        $branch_id = mi($_SESSION['po']['branch_id']);
        
        if(!$id||!$branch_id){
			header("Location: po.php");
			exit;
		}
		
        $q1 =$con->sql_query("select pi.*,si.sku_item_code,si.description as sku_description, si.doc_allow_decimal from po_items pi 
		left join sku_items si on si.id=pi.sku_item_id where pi.po_id=$id and pi.branch_id=$branch_id and (cost_indicate <> 'PA' or cost_indicate is null) order by pi.id") or die(mysql_error());
		while($r1 = $con->sql_fetchassoc($q1)){
			$q3 = $con->sql_query($qry ="select po_branch_id,deliver_to from po where id=$id and branch_id=".mi($branch_id));
			$r3 = $con->sql_fetchassoc($q3);
			$con->sql_freeresult($q3);
			if($sessioninfo['branch_id'] == 1 && !$r3['po_branch_id'] && $r3['deliver_to']){ //for hq branch
				$q2= $con->sql_query("select deliver_to from po where id=$id and branch_id=".mi($branch_id));
				$r2= $con->sql_fetchassoc($q2);
				$con->sql_freeresult($q2);
				if($r2['deliver_to']){
					$branch_id = unserialize($r2['deliver_to']);
					foreach($branch_id as $bid){
						$branch_code[] = get_branch_code($bid);
						$multi_bid[] = $bid;
					}
					$r1['branch_code'] = $branch_code;
					$r1['multi_bid'] = $multi_bid;
				}
				
				if($r1['qty_loose_allocation']){
					$qty_loose_allocation = unserialize($r1['qty_loose_allocation']);
					if($qty_loose_allocation){
						foreach($qty_loose_allocation as $bid=>$value){
							$qty_pcs[$bid] = $value;
						}
					}
					$r1['qty_pcs'] = $qty_pcs;
				}
				
				if($r1['foc_loose_allocation']){
					$foc_loose_allocation = unserialize($r1['foc_loose_allocation']);
					if($foc_loose_allocation){
						foreach($foc_loose_allocation as $bid=>$value){
							$foc_pcs[$bid] = $value;
						}
					}
					$r1['foc_pcs'] = $foc_pcs;
				}
				unset($branch_code,$multi_bid,$qty_pcs,$foc_pcs);
			}
			$items[] = $r1;
		}
		$con->sql_freeresult($q1);
        $smarty->assign('items',$items);
		$_REQUEST['po_tab'] = 'view_items';
		$smarty->display('po.view_items.tpl');
	}
	
	//delete po items
	function delete_items(){
		global $con, $smarty;
		$id = mi($_SESSION['po']['id']);
        $branch_id = mi($_SESSION['po']['branch_id']);

        if(!$id||!$branch_id){
			header("Location: po.php");
			exit;
		}
		if($_REQUEST['item_chx']){
            $con->sql_query("delete from po_items where po_id=$id and branch_id=$branch_id and id in (".join(',',array_keys($_REQUEST['item_chx'])).")") or die(mysql_error());
		}
		
		$this->po_amt_need_update($branch_id, $id);
		header("Location: po.php?a=view_items");
	}
	
	//Save changed po items
	function save_items(){
        global $con, $sessioninfo, $smarty;
		$form = $_REQUEST;
		$id = mi($_SESSION['po']['id']);
        $branch_id = mi($_SESSION['po']['branch_id']);
		
        if(!$id||!$branch_id){
			header("Location: po.php");
			exit;
		}
		
		if($form){
			$q3 = $con->sql_query("select po_branch_id,deliver_to from po where id=$id and branch_id=$branch_id");
			$r3 = $con->sql_fetchassoc($q3);
			$con->sql_freeresult($q3);
			if($sessioninfo['branch_id'] == 1 && !$r3['po_branch_id'] && $r3['deliver_to']){ //for hq branch
				foreach($form as $qty_type=>$pilist){
					if($qty_type == 'item_qty' || $qty_type == 'foc_qty' ){
						foreach($pilist as $piid=>$bid){
							$upd['qty_loose_allocation']=serialize($form['item_qty'][$piid]);
							$upd['foc_loose_allocation']=serialize($form['foc_qty'][$piid]);
							$con->sql_query("update po_items set ".mysql_update_by_field($upd)." where po_id=$id and branch_id=$branch_id and id=$piid")or die(mysql_error());
						}
					}
				}
			}else{ //for single branch
				foreach($form as $qty_type=>$pilist){
					if($qty_type == 'qty_loose' || $qty_type == 'foc_loose' ){
						foreach($pilist as $piid=>$value){
							$upd['qty_loose'] = $form['qty_loose'][$piid];
							$upd['foc_loose'] = $form['foc_loose'][$piid];
							$con->sql_query("update po_items set ".mysql_update_by_field($upd)." where po_id=$id and branch_id=$branch_id and id=$piid")or die(mysql_error());
						}
					}
				}
			}
		}
		$this->po_amt_need_update($branch_id, $id);
		header("Location: po.php?a=view_items");
	}
	
	function change_po(){
	    global $con,$smarty;
	    
        $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		
		if(!$id||!$branch_id){
           js_redirect('Invalid PO', "index.php");
           exit;
		}else{
			$this->reset_session_po($id,$branch_id);
		}
		header("Location: po.php?a=view_items");
	}
	
	function add_item_by_grn_barcode(){
		global $con, $smarty;
		
		$code = trim($_REQUEST['product_code']);
		$si_info=get_grn_barcode_info($code,false);
	
		if ($si_info['sku_item_id']){
			$sku_item_id = $si_info['sku_item_id'];
			$pcs = mf($si_info['qty_pcs']);
			$selling_price = mf($si_info['selling_price']);
			if(isset($si_info['new_cost_price'])) $cost_price = $si_info['new_cost_price'];
		}
		
		if($si_info && !$si_info['err']){
			if(ceil($pcs) != $pcs && !$si_info['doc_allow_decimal']){
				$_REQUEST['item_qty'][$sku_item_id] = 0;
				$_REQUEST['empty_decimal_points'] = true;
				$this->err[] = "SKU Item [".$si_info['sku_item_code']."] is not decimal points item, whereas qty auto set to empty.";
			}else $_REQUEST['item_qty'][$sku_item_id] = $pcs;
			$_REQUEST['item_sp'][$sku_item_id] = $selling_price;
			if(isset($cost_price)) $_REQUEST['item_cp'][$sku_item_id] = $cost_price;
			$this->add_items();
			// get item info
			$this->get_item_info();
		}elseif($code) $this->err[] = "The item (".$code.") not found!";
	}
	
	private function po_amt_need_update($branch_id, $id){
		global $con;
		$upd_po = array();
		$upd_po['last_update'] = 'CURRENT_TIMESTAMP';
		$upd_po['amt_need_update'] = 1;
		$con->sql_query("update po set ".mysql_update_by_field($upd_po)." where id=$id and branch_id=$branch_id") or die(mysql_error());
	}
	
	function get_po_branch($id, $bid){	//get current po multi branch id
		global $con, $smarty, $sessioninfo;
		$id = mi($_SESSION['po']['id']);
        $branch_id = mi($_SESSION['po']['branch_id']);
		$q2 = $con->sql_query("select deliver_to from po where id=$id and branch_id=$branch_id");
		$r2 = $con->sql_fetchassoc($q2);
		$con->sql_freeresult($q2);
		$deliver_bid = unserialize($r2['deliver_to']);
		return $deliver_bid;
	}
}
$po_module = new PO_Module('PO');
?>