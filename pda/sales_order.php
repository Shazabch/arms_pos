<?php
/*
6/24/2011 5:09:32 PM Andy
- Make all branch default sort by sequence, code.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.

3/15/2012 11:05:32 AM Justin
- Added "/pda" to redirect user back to pda login menu page.

6/14/2012 4:31:34 PM Justin
- Added new function to auto add item when got check "Add item when match one result" from module.

7/27/2012 4:42:34 PM Justin
- Added new function to allow item can scan by GRN barcoder.

4/12/2013 4:33 PM Andy
- Enhance when user add/edit/delete item, it will mark SO amount need to update.

2/9/2015 3:01 PM Andy
- Enhance to capture GST data.

4/28/2015 3:52 PM Andy
- Enhanced to check whether debtor is special exemption when save settings.
- Enhanced to check "Use Promotion Price", "MPrice" and "GST Special Exemption" when add item.

1/29/2021 5:18 PM William
- Enhanced to add selling price, discount, ctn, uom, remark to search scan result and view sales order item screen.

8/11/2021 10:59 AM William
- Bug fixed sales order view item screen title incorrect.

*/
include("common.php");
include("class.scan_product.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/pda/index.php");

$maintenance->check(196);

class SO_Module extends Scan_Product{
    function __construct($title){
        global $sessioninfo;
        
        $_SESSION['scan_product']['type'] = 'SO';
		$_SESSION['scan_product']['name'] = isset($_SESSION['so']['id']) ? 'SO#'.$_SESSION['so']['id']."(DD)" : '';
		
	    if(isset($_REQUEST['branch_id'])){
			if($_REQUEST['branch_id'] != $sessioninfo['branch_id']){    // prevent edit other branch
				header("Location: $_SERVER[PHP_SELF]");
				exit;
			}
		}
		parent::__construct($title);
	}
	
    function init_module(){
	    global $con, $smarty;
		// alter any default value, such as $this->scan_templates and $this->result_templates
		//$this->scan_templates = 'abc.tpl';
		//$this->result_templates = 'abc.tpl';

		$smarty->assign('PAGE_TITLE', $this->title);
		$smarty->assign('module_name', $this->title);
		$smarty->assign('top_include','sales_order.top_include.tpl');
		$smarty->assign('btm_include','sales_order.btm_include.tpl');
	}
	
	function default_(){
		global $con, $smarty;

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if($id&&$branch_id){
			$this->reset_session_so($id,$branch_id);
		}else{
            $id = mi($_SESSION['so']['id']);
            $branch_id = mi($_SESSION['so']['branch_id']);
		}

		if($id>0){
			$this->show_scan_product();
		}else{
			$this->show_setting();
		}
	}
	
	function show_scan_product(){
		global $con, $smarty;

		$id = mi($_SESSION['so']['id']);
        $branch_id = mi($_SESSION['so']['branch_id']);
        
		// check DO exists or not
		$con->sql_query("select *
		from sales_order so
		where so.id=$id and so.branch_id=$branch_id and so.active=1");
		$form = $con->sql_fetchrow();

		if(!$form){
            js_redirect('Invalid Sales Order', "index.php");
            exit;
		}

		/*$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
		$report_prefix = $con->sql_fetchfield(0);
		$so_no = $report_prefix.sprintf('%05d',$id);*/

		$_SESSION['scan_product']['type'] = 'SO';
		$_SESSION['scan_product']['name'] = 'SO#'.$id."(DD)";

		$product_code = strtoupper($_REQUEST['product_code']);
		// cut last digit
		$product_code2 = strtoupper(substr($product_code,0,strlen($product_code)-1));
		
		$filter[] = "(si.mcode=".ms($product_code)." or si.mcode=".ms($product_code2).")";
		$filter[] = "(si.link_code=".ms($product_code)." or si.link_code=".ms($product_code2).")";
		$filter[] = "(si.sku_item_code=".ms($product_code)." or si.sku_item_code=".ms($product_code2).")";
		$filter[] = "(si.artno=".ms($product_code)." or si.artno=".ms($product_code2).")";
		$filter = join(' or ',$filter);

		$sql = $con->sql_query("select si.* from sku_items si where $filter");

		if($con->sql_numrows($sql) == 1 && $_REQUEST['auto_add_item']){
			$sku_info = $con->sql_fetchassoc($sql);
			$con->sql_freeresult($sql);
			$_REQUEST['so_item'][$sku_info['id']] = 1;
			$_REQUEST['pcs'][$sku_info['id']] = 1;
			
			$this->add_items();
			unset($_REQUEST);
			$smarty->assign("auto_add", 1);
		}
		
		// get item info
		$con->sql_query("select count(*) as total_item,sum(pcs) as total_pcs, sum(ctn) as total_ctn
		from sales_order_items soi where soi.sales_order_id=$id and soi.branch_id=$branch_id");
		$smarty->assign('items_details',$con->sql_fetchrow());
		
		$this->search_product();
	}

	function new_so(){
		unset($_SESSION['so']);
		header("Location: $_SERVER[PHP_SELF]");
	}
	
	function show_setting(){
		global $con, $smarty;

		$this->default_load();

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(!$id||!$branch_id){
            $id = mi($_SESSION['so']['id']);
            $branch_id = mi($_SESSION['so']['branch_id']);
		}

		if($id>0&&$branch_id>0){
		    $this->reset_module_session($id,$branch_id);
			$con->sql_query("select * from sales_order so where so.id=$id and so.branch_id=$branch_id");
			$form = $con->sql_fetchrow();
			$smarty->assign('form', $form);
		}
		$smarty->assign('so_tab', 'setting');
		$smarty->display('sales_order.index.tpl');
	}
	
	private function default_load(){
		global $con,$smarty;

		// all branches
		$con->sql_query("select * from branch where active=1 order by sequence,code") or die(mysql_error());
		while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}
		$smarty->assign('branches',$branches);

		// branches group
		//$this->load_branch_group();
		
		$con->sql_query("select * from debtor where active=1 order by code",false,false);
		if($con->sql_numrows()>0){
			while($r = $con->sql_fetchrow()){
				$debtors[$r['id']] = $r;
			}
			$smarty->assign('debtors',$debtors);
		}
	}
	
	function save_setting(){
		global $con, $smarty, $sessioninfo, $config;

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$debtor_id = mi($_REQUEST['debtor_id']);
		$order_date = $_REQUEST['order_date'];

        $upd = array();

        $upd['debtor_id'] = $debtor_id;
		$upd['order_date'] = $order_date;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['user_id'] = $sessioninfo['id'];
		$upd['batch_code'] = trim($_REQUEST['batch_code']);
		$upd['cust_po'] = trim($_REQUEST['cust_po']);
		
		// validating
        if(!$upd['debtor_id'])   $err[] = "Invalid Deliver To Debtor";
        if(date("Y", strtotime($upd['order_date']))<2010)   $err[] = "Order date cannot less than 2010";
		if(!$upd['batch_code'])  $err[] = "Invalid Batch Code";
		
		if($err){
			$this->default_load();
			$smarty->assign('form',$upd);
			$smarty->assign('err',$err);
			$smarty->display('sales_order.index.tpl');
			exit;
		}
		
		// get debtor info
		$con->sql_query("select * from debtor where id=$debtor_id");
		$debtor_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($config['enable_gst']){
			$prm = $upd;
			$prm['branch_id'] = $branch_id;
			$prm['date'] = $order_date;
			$upd['is_under_gst'] = check_gst_status($prm);
			
			if($upd['is_under_gst']){
				// check special exemption
				$upd['is_special_exemption'] = $debtor_info['special_exemption'];
			}
		}

		// old SO
		if($id>0){
            $con->sql_query("update sales_order set ".mysql_update_by_field($upd)." where id=$id and branch_id=$branch_id");
		}else{  // new SO
			$upd['branch_id'] = $branch_id;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into sales_order ".mysql_insert_by_field($upd));
			$id = $con->sql_nextid();
			
			$formatted = sprintf("%05d", $id);
		    //select report prefix from branch
			$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
			$b = $con->sql_fetchrow();
			$order_no = $b['report_prefix'].$formatted;
			$con->sql_query("update sales_order set order_no=".ms($order_no)." where branch_id=$branch_id and id=".mi($id));
		}

		$this->reset_module_session($id,$branch_id);
		header("Location: $_SERVER[PHP_SELF]");
	}
	
	private function reset_module_session($id,$branch_id){
	    global $con;

	    $con->sql_query("select id,branch_id,debtor_id from sales_order
		where id=$id and branch_id=$branch_id");
		$form = $con->sql_fetchrow();
		if(!$form)	js_redirect('Invalid SO', "index.php");

        $_SESSION['so'] = $form;
	}
	
	function view_items(){
		global $con, $smarty;
		$id = mi($_SESSION['so']['id']);
        $branch_id = mi($_SESSION['so']['branch_id']);

        if(!$id||!$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}

		// load item list
		$items = array();
        $con->sql_query("select soi.*,si.sku_item_code,si.description as sku_description, si.doc_allow_decimal,
		si.link_code as link_code, uom.fraction as uom_fraction
		from sales_order_items soi
		left join sku_items si on si.id=soi.sku_item_id
		left join uom on uom.id = soi.uom_id
		where soi.sales_order_id=$id and soi.branch_id=$branch_id order by soi.id");
		$item_list = $con->sql_fetchrowset();
		foreach($item_list as $key=>$r){
			$so_sql=$con->sql_query("select sum(soi.pcs + (uom.fraction * soi.ctn)) as  reserve_qty
			from sales_order_items soi 
			left join sales_order so on so.id = soi.sales_order_id and soi.branch_id = so.branch_id
			left join uom on uom.id = soi.uom_id
			where so.approved = 1 and so.delivered = 0 and so.exported_to_pos = 0 and so.active = 1 and so.status = 1 and
			soi.sku_item_id =".mi($r['sku_item_id'])." and so.branch_id=$branch_id and so.id <> $id");
			$soi_data = $con->sql_fetchassoc($so_sql);
			$con->sql_freeresult($soi_data);
			$r['reserve_qty'] = $soi_data['reserve_qty'];
			
			$items[] = $r;
		}
		$con->sql_freeresult();
		
        $smarty->assign('items', $items);
		$this->get_uom_list();
		
		$smarty->assign('so_tab', 'view_items');
		$smarty->display('sales_order.view_items.tpl');
	}
	
	function add_items(){
		global $con,$config,$sessioninfo;

	    $id = mi($_SESSION['so']['id']);
        $branch_id = mi($_SESSION['so']['branch_id']);
		$form = $_REQUEST;
		
		$items = $form['so_item'];
		if($items){
		    $total_qty = 0;
			$item_need_add = array();
			
			foreach($items as $sid=>$val){
				$ctn = $form['ctn'][$sid];
				$pcs = $form['pcs'][$sid];
				
				if($ctn > 0 || $pcs > 0){
					$uom_data = explode(",", $form['sel_uom'][$sid]);
					$fraction = $uom_data[1];
					$item_need_add[$sid]['ctn'] = $ctn;
					$item_need_add[$sid]['pcs'] = round($pcs, $config['global_qty_decimal_points']);
					$item_need_add[$sid]['selling_price'] = mf($form['selling_price'][$sid]);
					$item_need_add[$sid]['item_discount'] = $form['item_discount'][$sid];
					$item_need_add[$sid]['item_discount_amount'] = mf($form['item_discount_amount'][$sid]);
					$item_need_add[$sid]['uom_id'] = mi($uom_data[0]);
					$item_need_add[$sid]['remark'] = $form['remark'][$sid];
					$item_need_add[$sid]['cost_price'] = mf($form['cost_price'][$sid]);
					
					$total_qty += (($ctn*$fraction) + $item_need_add[$sid]['pcs']);
				}
			}

			if($total_qty<=0){
                $ret['error'][] = "Invalid quantity";
			}else{
				$con->sql_query("select * from sales_order where branch_id=$branch_id and id=$id");
				$so = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
			    // get items info
				$q1 = $con->sql_query($qry="select si.id as sku_item_id, si.sku_item_code, si.description as sku_description, 
				ifnull(si.artno,si.mcode) as artno_mcode, 1 as uom_id, 1 as uom_fraction,si.packing_uom_id as master_uom_id, 
				sic.qty as stock_balance,ifnull(sic.grn_cost,si.cost_price) as cost_price, ifnull(sip.price,si.selling_price) as selling_price
				from sku_items si
				left join sku on sku_id = sku.id
				left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
				left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
				where si.id in (".join(',',array_keys($item_need_add)).")");
				//$total_pcs = 0;
				//$total_amount = 0;
				
				while($item = $con->sql_fetchassoc($q1)){
				    $sid = $item['sku_item_id'];
					$ctn = $item_need_add[$sid]['ctn'];
				    $pcs = $item_need_add[$sid]['pcs'];
					
				    $item['ctn'] = $ctn;
                    $item['pcs'] = $pcs;
				    $item['branch_id'] = $branch_id;
				    $item['sales_order_id'] = $id;
				    $item['user_id'] = $sessioninfo['id'];
					$item['uom_id'] = $item_need_add[$sid]['uom_id'];
					
					$item['item_discount'] = $item_need_add[$sid]['item_discount'];
					$item['item_discount_amount'] = $item_need_add[$sid]['item_discount_amount'];
					$item['remark'] = $item_need_add[$sid]['remark'];
					if($item_need_add[$sid]['cost_price']){
						$item['cost_price'] = $item_need_add[$sid]['cost_price'];
					}
					
					if($item_need_add[$sid]['selling_price']){
						$item['selling_price'] = mf($item_need_add[$sid]['selling_price']);
					}else{
						if($so['selling_type']){
							$con->sql_query("select * from sku_items_mprice where branch_id=$branch_id and sku_item_id=$sid and type=".ms($so['selling_type']));
							$mprice_info = $con->sql_fetchassoc();
							$con->sql_freeresult();
							
							if($mprice_info)	$item['selling_price'] = $mprice_info['price'];
						}
					}
					// find price before gst
					if($config['enable_gst']){
						// get sku is inclusive
						$is_sku_inclusive = get_sku_gst("inclusive_tax", $item['sku_item_id']);
						// get sku original output gst
						$sku_original_output_gst = get_sku_gst("output_tax", $item['sku_item_id']);
						
						if($is_sku_inclusive == 'yes'){
							// is inclusive tax
							// find the price before tax
							$gst_tax_price = round($item['selling_price'] / ($sku_original_output_gst['rate']+100) * $sku_original_output_gst['rate'], 2);
							$price_included_gst = $item['selling_price'];
							$item['selling_price'] = $price_included_gst - $gst_tax_price;
						}
						
						if($so['is_under_gst']){
							$use_gst = array();
							if($so['is_special_exemption']){
								// is special exemption
								$use_gst = get_special_exemption_gst();
							}else{
								// normal debtor
								if($sku_original_output_gst){
									$use_gst = $sku_original_output_gst;
								}
							}
							if($use_gst){
								$item['gst_id'] = $use_gst['id'];
								$item['gst_code'] = $use_gst['code'];
								$item['gst_rate'] = $use_gst['rate'];
							}else{
								$item['gst_id'] = $gst_list[0]['id'];
								$item['gst_code'] = $gst_list[0]['code'];
								$item['gst_rate'] = $gst_list[0]['rate'];
							}
						}
					}
						
					$con->sql_query("insert into sales_order_items ".mysql_insert_by_field($item, array('branch_id','sales_order_id','user_id','sku_item_id','cost_price','selling_price','uom_id','stock_balance','pcs',
					'gst_id','gst_code','gst_rate', 'ctn', 'item_discount', 'item_discount_amount', 'remark')));
					
					//$total_pcs += mi($pcs);
					//$total_amount += mf($pcs*$item['selling_price']);
				}
				$con->sql_freeresult($q1);

				// update main sales order
				//$con->sql_query("update sales_order set total_pcs=if(total_pcs is null,$total_pcs,total_pcs+$total_pcs), total_qty=if(total_qty is null,$total_pcs,total_qty+$total_pcs), total_amount=if(total_amount is null,$total_amount,total_amount+$total_amount) where id=$id and branch_id=$branch_id");
                $this->update_so_total($branch_id, $id);
				$ret['success'] = true;
			}
		}else{
            $ret['error'][] = "No items found";
		}

		return $ret;
	}
	
	function save_items(){
        global $con, $smarty, $config;
		$id = mi($_SESSION['so']['id']);
        $branch_id = mi($_SESSION['so']['branch_id']);
		
		$form = $_REQUEST;
        if(!$id||!$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
		
		if($_REQUEST['so_item']){
			//print_r($form); exit;
			foreach($_REQUEST['so_item'] as $soi_id=>$val){
				$upd = array();
				$ctn = mf($form['ctn'][$soi_id]);
				$pcs = mf($form['pcs'][$soi_id]);
				
				if(!$ctn && !$pcs){
					$con->sql_query("delete from sales_order_items where branch_id=$branch_id and sales_order_id=$id and id=".mi($soi_id));
				}elseif($ctn > 0 || $pcs > 0){
					$upd = array();
					
					$uom_data = explode(",", $form['sel_uom'][$soi_id]);
					$fraction = $uom_data[1];
					$upd['ctn'] = $ctn;
					$upd['pcs'] = round($pcs, $config['global_qty_decimal_points']);
					$upd['selling_price'] = mf($form['selling_price'][$soi_id]);
					$upd['item_discount'] = $form['item_discount'][$soi_id];
					$upd['item_discount_amount'] = mf($form['item_discount_amount'][$soi_id]);
					$upd['uom_id'] =  mi($uom_data[0]);
					$upd['remark'] =  $form['remark'][$soi_id];
					$upd['cost_price'] = mf($form['cost_price'][$soi_id]);
					
					$con->sql_query("update sales_order_items set ".mysql_update_by_field($upd)." where branch_id=$branch_id and sales_order_id=$id and id=".mi($soi_id));
				}
			}
			
			$this->update_so_total($branch_id, $id);
		}
		header("Location: $_SERVER[PHP_SELF]?a=view_items");
	}
	
	private function update_so_total($branch_id, $id){
	    global $con;
	    
	    $this->mark_so_amt_need_update($branch_id, $id);
	    
        /*$con->sql_query("select sum(pcs) as total_pcs, sum(pcs*selling_price) as total_amt from sales_order_items
			where branch_id=$branch_id and sales_order_id=$id");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$upd['total_qty'] = $upd['total_pcs'] = $form['total_pcs'];
		$upd['total_amount'] = $form['total_amt'];
		
		$con->sql_query("update sales_order set ".mysql_update_by_field($upd)." where branch_id=$branch_id and id=$id");*/
	}
	
	function delete_items(){
		global $con, $smarty;
		$id = mi($_SESSION['so']['id']);
        $branch_id = mi($_SESSION['so']['branch_id']);

        if(!$id||!$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
		if($_REQUEST['item_chx']){
            $con->sql_query("delete from sales_order_items
			where sales_order_id=$id and branch_id=$branch_id and id in (".join(',',array_keys($_REQUEST['item_chx'])).")");
			$this->update_so_total($branch_id, $id);
		}
		header("Location: $_SERVER[PHP_SELF]?a=view_items");
	}
	
	function open(){
		global $con, $smarty, $sessioninfo;

		if(isset($_REQUEST['order_no'])){
			$branch_id = mi($sessioninfo['branch_id']);
			$id = mi($_REQUEST['order_no']);
			//$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
			//$report_prefix = $con->sql_fetchfield(0);

			$sql = "select so.*,debtor.code as debtor_code, debtor.description as debtor_desc
			from sales_order so
			left join debtor on debtor.id=so.debtor_id
			where so.branch_id=$branch_id and so.id=$id and so.active=1 and so.approved=0 and so.status=0";
			$con->sql_query($sql);
			if($con->sql_numrows()<=0){
				$err[] = "No Sales Order Found with $id.";
			}else{
				while($r = $con->sql_fetchrow()){
					$so_list[] = $r;
				}

				$smarty->assign('so_list',$so_list);
			}
		}
		$smarty->assign('err',$err);
		$smarty->display('sales_order.search.tpl');
	}
	
	function change_so(){
	    global $con,$smarty;

        $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(!$id||!$branch_id){
           js_redirect('Invalid Sales Order', "index.php");
           exit;
		}else{
			$this->reset_module_session($id,$branch_id);
		}

		header("Location: $_SERVER[PHP_SELF]");
	}

	function add_item_by_grn_barcode(){
		global $con, $smarty;

		$code = trim($_REQUEST['product_code']);
		if (preg_match("/^00/", $code))	// form ARMS' GRN barcoder
		{
			$sku_item_id=mi(substr($code,0,8));
			$qty = mi(substr($code,8,4));
			$sql = "select id from sku_items where id = ".mi($sku_item_id);
		}
		else	// from ATP GRN Barcode, try to search the link-code 
		{
			$linkcode=substr($code,0,7);
			$qty = mi(substr($code,7,5));
			$sql = "select id from sku_items where link_code = ".ms($linkcode);
		}
		$q1 = $con->sql_query($sql);
		$r1 = $con->sql_fetchrow($q1);
		$con->sql_freeresult($q1);
		
		if($r1){
			$_REQUEST['item_qty'][$r1['id']] = $qty;
			$this->add_items();
			// get item info
			$con->sql_query("select count(*) as total_item, sum(pcs) as total_pcs, sum(ctn) as total_ctn from sales_order_items where sales_order_id=".mi($_SESSION['so']['id'])." and branch_id=".mi($_SESSION['so']['branch_id'])) or die(mysql_error());
			$smarty->assign('items_details',$con->sql_fetchrow());
		}else $this->err[] = "The item (".$code.") not found!";
	}
	
	private function mark_so_amt_need_update($branch_id, $id){
		global $con;
		
		$upd_do = array();
		$upd_do['amt_need_update'] = 1;
		$upd_do['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update sales_order set ".mysql_update_by_field($upd_do)." where id=$id and branch_id=$branch_id") or die(mysql_error());
	}
	
	//get sku uom
	function get_uom_list(){
		global $con, $smarty;
		
		// uom
		$con->sql_query("select * from uom where active=1 order by code");
		while($r = $con->sql_fetchrow()){
			$uom[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		$smarty->assign('uom', $uom);
	}
}

$SO_Module = new SO_Module('Sales Order');
?>
