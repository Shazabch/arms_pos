<?php
/*
4/24/2015 10:54 AM Justin
- Enhanced to capture Document No. and GST information.

11/30/2015 9:43 PM DingRen
- when insert gra item calculate amount_gst, gst and amount and change to decimal 2

9/27/2016 1:56 PM Andy
- Enhanced to sort vendor by code & description.

7/21/2017 11:51 AM Justin
- Bug fixed on item amount never recalculate while save the GRA items with new qty.

8/7/2017 2:30 PM Justin
- Bug fixed on selling price keep deducting after user update the items.

8/15/2017 11:19 AM Justin
- Bug fixed on GRA searching will also allowing user to edit cancelled, approved or checkout GRA.
*/

include("common.php");
include("class.scan_product.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/pda/index.php");

class GRA_Module extends Scan_Product{
    function __construct($title){
        global $sessioninfo;
        
        $_SESSION['scan_product']['type'] = 'GRA';
		$_SESSION['scan_product']['name'] = isset($_SESSION['GRA']['id']) ? 'GRA#'.$_SESSION['gra']['id'] : '';
		
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
		$smarty->assign('top_include','goods_return_advice.top_include.tpl');
		$smarty->assign('btm_include','goods_return_advice.btm_include.tpl');
	}
	
	function default_(){
		global $con, $smarty;

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if($id&&$branch_id){
			$this->reset_session_gra($id,$branch_id);
		}else{
            $id = mi($_SESSION['gra']['id']);
            $branch_id = mi($_SESSION['gra']['branch_id']);
		}

		if($id>0){
			$this->show_scan_product();
		}else{
			$this->show_setting();
		}
	}
	
	function show_scan_product(){
		global $con, $smarty;

		$id = mi($_SESSION['gra']['id']);
        $branch_id = mi($_SESSION['gra']['branch_id']);
        
		// check exists or not
		$q1 = $con->sql_query("select *
							  from gra
							  where gra.id=$id and gra.branch_id=$branch_id"); //TBR active=1
		$form = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		if(!$form){
            js_redirect('Invalid Goods Return Advice', "index.php");
            exit;
		}

		/*$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
		$report_prefix = $con->sql_fetchfield(0);
		$so_no = $report_prefix.sprintf('%05d',$id);*/

		$_SESSION['scan_product']['type'] = 'GRA';
		$_SESSION['scan_product']['name'] = 'GRA#'.$id;

		$product_code = strtoupper($_REQUEST['product_code']);
		// cut last digit
		$product_code2 = strtoupper(substr($product_code,0,strlen($product_code)-1));
		
		if (!empty($product_code) || !empty($product_code2)) {
			$filter[] = "(si.mcode=".ms($product_code)." or si.mcode=".ms($product_code2).")";
			$filter[] = "(si.link_code=".ms($product_code)." or si.link_code=".ms($product_code2).")";
			$filter[] = "(si.sku_item_code=".ms($product_code)." or si.sku_item_code=".ms($product_code2).")";
			$filter[] = "(si.artno=".ms($product_code)." or si.artno=".ms($product_code2).")";
			$filter = join(' or ',$filter);

			$sql = $con->sql_query("select si.* from sku_items si where $filter");

			if($con->sql_numrows($sql) == 1 && $_REQUEST['auto_add_item']){
				$sku_info = $con->sql_fetchassoc($sql);
				$con->sql_freeresult($sql);
				$_REQUEST['item_qty'][$sku_info['id']] = 1;
				$this->add_items();
				unset($_REQUEST);
				$smarty->assign("auto_add", 1);
			}
		}
		
		// get item info
		$q1 = $con->sql_query("select count(*) as total_item,sum(qty) as total_pcs from gra_items gi where gi.gra_id=$id and gi.branch_id=$branch_id");
		$items_details = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		$smarty->assign('items_details', $items_details);
		$smarty->assign('form', $form);
		
		$gst_list = array();
		if($form['is_under_gst']){
			$this->is_under_gst = 1;
			$gst_list = construct_gst_list("purchase");
			$smarty->assign("gst_list", $gst_list);
		}else $this->is_under_gst = 0;
		
		$this->search_product();
	}

	function new_gra(){
		unset($_SESSION['gra']);
		header("Location: $_SERVER[PHP_SELF]");
	}
	
	function show_setting(){
		global $con, $smarty;

		$this->default_load();

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(!$id||!$branch_id){
            $id = mi($_SESSION['gra']['id']);
            $branch_id = mi($_SESSION['gra']['branch_id']);
		}

		if($id>0&&$branch_id>0){
		    $this->reset_module_session($id,$branch_id);
			$con->sql_query("select * from gra where gra.id=$id and gra.branch_id=$branch_id");
			$form = $con->sql_fetchrow();
			$smarty->assign('form', $form);
		}
		$smarty->assign('gra_tab', 'setting');
		$smarty->display('goods_return_advice.index.tpl');
	}
	
	private function default_load(){
		global $con,$smarty,$sessioninfo;
		
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(!$id||!$branch_id){
            $id = mi($_SESSION['gra']['id']);
            $branch_id = mi($_SESSION['gra']['branch_id']);
		}

		//$con->sql_query("select vendor.id, vendor.code as code ,vendor.description, category.description as dept_code from gra left join vendor on gra.vendor_id = vendor.id left join category on gra.dept_id = category.id where gra.id = $id and branch_id = $branch_id");
		$con->sql_query("select id,code,description from vendor where active=1 order by code, description");
		while($r = $con->sql_fetchrow()){
			$vendors[$r['id']] = $r;
		}
		$smarty->assign('vendors',$vendors);
		
		$depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
		$con->sql_query("select id, description from category where active and level = 2 and $depts order by description");
		while($r = $con->sql_fetchrow()){
			$departments[$r['id']] = $r;
		}
		$smarty->assign('departments',$departments);
		
		//no need
		// all branches
		$con->sql_query("select * from branch where active=1 order by sequence,code") or die(mysql_error());
		while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}
		$smarty->assign('branches',$branches);

		// branches group
		//$this->load_branch_group();
		//no need
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

        $upd = array();

		// validating
        //if(!$upd['debtor_id'])   $err[] = "Invalid Deliver To Debtor"; //later
		
		if($err){
			$this->default_load();
			$smarty->assign('form',$upd);
			$smarty->assign('err',$err);
			$smarty->display('goods_return_advice.index.tpl');
			exit;
		}

		// old
		if($id>0){
			$upd['dept_id'] = $_REQUEST['dept_id'];
			$upd['vendor_id'] = $_REQUEST['vendor_id'];
			$upd['sku_type'] = $_REQUEST['sku_type'];
            $con->sql_query("update gra set ".mysql_update_by_field($upd)." where id=$id and branch_id=$branch_id");
		}else{  // new
			$upd['branch_id'] = $branch_id;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$upd['dept_id'] = $_REQUEST['dept_id'];
			$upd['vendor_id'] = $_REQUEST['vendor_id'];
			$upd['sku_type'] = $_REQUEST['sku_type'];
			$upd['user_id'] = $sessioninfo['id'];
			
			// check gst status
			$prms = array();
			$prms['branch_id'] = $sessioninfo['branch_id'];
			$prms['date'] = date("Y-m-d");
			$upd['is_under_gst'] = check_gst_status($prms);
			
			$con->sql_query("insert into gra ".mysql_insert_by_field($upd));
			$id = $con->sql_nextid();
			
			//later
			/*
			$formatted = sprintf("%05d", $id);
		    //select report prefix from branch
			$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
			$b = $con->sql_fetchrow();
			$order_no = $b['report_prefix'].$formatted;
			$con->sql_query("update sales_order set order_no=".ms($order_no)." where branch_id=$branch_id and id=".mi($id));
			*/
		}

		$this->reset_module_session($id,$branch_id);
		header("Location: $_SERVER[PHP_SELF]");
	}
	
	private function reset_module_session($id,$branch_id){
	    global $con;

	    $con->sql_query("select id,branch_id,sku_type,dept_id,vendor_id from gra
		where id=$id and branch_id=$branch_id");
		$form = $con->sql_fetchrow();
		if(!$form)	js_redirect('Invalid GRA', "index.php");

        $_SESSION['gra'] = $form;
	}
	
	function view_items(){
		global $con, $smarty;
		$id = mi($_SESSION['gra']['id']);
        $branch_id = mi($_SESSION['gra']['branch_id']);

        if(!$id||!$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
		
		// load header
		$q1 = $con->sql_query("select * from gra where id = ".mi($id)." and branch_id = ".mi($branch_id));
		$form = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		// load item list
        $q1 = $con->sql_query("select gi.*,si.sku_item_code,si.description as sku_description, si.doc_allow_decimal
							   from gra_items gi
							   left join sku_items si on si.id=gi.sku_item_id
							   where gi.gra_id=".mi($id)." and gi.branch_id=".mi($branch_id)."
							   order by gi.id");
        $smarty->assign('items',$con->sql_fetchrowset($q1));
		
		$gst_list = array();
		if($form['is_under_gst']){
			$this->is_under_gst = 1;
			$gst_list = construct_gst_list("purchase");
			$smarty->assign("gst_list", $gst_list);
		}

		$smarty->assign('form', $form);
		$smarty->assign('gra_tab', 'view_items');
		$smarty->display('goods_return_advice.view_items.tpl');
	}
	
	function add_items(){
		global $con,$config,$sessioninfo, $smarty;
		
	    $id = mi($_SESSION['gra']['id']);
        $branch_id = mi($_SESSION['gra']['branch_id']);

		// load gst info
		$gst_list = array();
		if($config['enable_gst']){
			$q1 = $con->sql_query("select * from gst where active=1 and type = 'purchase'");
			while($r = $con->sql_fetchassoc($q1)){
				$gst_list[$r['id']] = $r;
			}
			$con->sql_freeresult($q1);
		}


		//$this->dumpdata($_REQUEST);
		$items = $_REQUEST['item_qty'];
		if($items){
		    $total_qty = 0;
			foreach($items as $sid=>$qty){
				if($qty>0){
					$item_need_add[$sid] = round($qty, $config['global_qty_decimal_points']);
					$total_qty += $qty;
				}
			}

			if($total_qty<=0){
                $ret['error'][] = "Invalid quantity";
			}else{
			    // get items info
			    $q1 = $con->sql_query("select si.id as sku_item_id, si.sku_item_code, si.description as sku_description, ifnull(si.artno,si.mcode) as artno_mcode, 1 as uom_id, 1 as uom_fraction,si.packing_uom_id as master_uom_id,sic.qty as stock_balance,ifnull(sic.grn_cost,si.cost_price) as cost_price, ifnull(sip.price,si.selling_price) as selling_price
from sku_items si
left join sku on sku_id = sku.id
left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
where si.id in (".join(',',array_keys($item_need_add)).")");
				$total_pcs = 0;
				$total_amount = 0;
				
				while($item = $con->sql_fetchassoc($q1)){
				    $sid = $item['sku_item_id'];
				    $pcs = $item_need_add[$sid];
				    
				    $item['branch_id'] = $branch_id;
				    $item['user_id'] = $sessioninfo['id'];
				    $item['gra_id'] = $id;
				    $item['vendor_id'] = $_SESSION['gra']['vendor_id'];
				    $item['qty'] = $items[$sid];
				    $item['cost'] = isset($_REQUEST['item_price'][$sid]) ? $_REQUEST['item_price'][$sid] : $this->get_gra_cost_price($sid, $_SESSION['gra']['branch_id'], $_SESSION['gra']['vendor_id']);
				    //$item['selling_price'] = $_REQUEST['item_price'][$sid];
				    $item['selling_price'] = $this->get_gra_selling_price($sid,$_SESSION['gra']['branch_id']);
					$item['added'] = 'CURRENT_TIMESTAMP';
					$item['return_type'] = $_REQUEST['return_type'];
					$item['doc_no'] = $_REQUEST['doc_no'][$sid];
					
					if($_SESSION['gra']['sku_type']=='CONSIGN')
						$item['amount'] = $item['qty'] * $item['selling_price'];
					else
						$item['amount'] = (int)$item['qty'] * (int)$item['cost'];
					$item['gst']=0;
					$item['amount_gst']=0;

					// capture gst info
					$gst_id = $_REQUEST['gst_id'][$sid];
					if($gst_id){
						$item['gst_id'] = $gst_id;
						$item['gst_code'] = $gst_list[$gst_id]['code'];
						$item['gst_rate'] = $gst_list[$gst_id]['rate'];
						
						// calculate gst selling price
						$is_inclusive_tax = get_sku_gst("inclusive_tax", $sid);

						$prms = array();
						$prms['selling_price'] = $item['selling_price'];
						$prms['inclusive_tax'] = $is_inclusive_tax;
						$prms['gst_rate'] = $item['gst_rate'];
						$gst_sp_info = calculate_gst_sp($prms);
						$item['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
						//$r['gst_amt'] = $gst_sp_info['gst_amt'];
						
						if($is_inclusive_tax == "yes"){
							$item['gst_selling_price'] = $item['selling_price'];
							$item['selling_price'] = $gst_sp_info['gst_selling_price'];
						}

						$item['amount_gst']=round($item['amount'] * ((100+$item['gst_rate'])/100),2);
                        $item['gst']=$item['amount_gst']-round($item['amount'], 2);
					}
					$item['amount']=round($item['amount'],2);

					$con->sql_query("insert into gra_items ".mysql_insert_by_field($item, array('branch_id','user_id','sku_item_id','gra_id','vendor_id','qty','cost','selling_price','added','return_type','doc_no','gst_id','gst_code','gst_rate','gst_selling_price','amount','gst','amount_gst')));
					
				}
				$con->sql_freeresult($q1);

                $this->update_gra_total($branch_id, $id);
				$ret['success'] = true;
			}
		}else{
            $ret['error'][] = "No items found";
		}
		return $ret;
	}
	
	function save_items(){
        global $con, $smarty, $config;
		$id = mi($_SESSION['gra']['id']);
        $branch_id = mi($_SESSION['gra']['branch_id']);

        if(!$id||!$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
		
		// load gst info
		$gst_list = array();
		if($config['enable_gst']){
			$q1 = $con->sql_query("select * from gst where active=1 and type = 'purchase'");
			while($r = $con->sql_fetchassoc($q1)){
				$gst_list[$r['id']] = $r;
			}
			$con->sql_freeresult($q1);
		}
		
        if($_REQUEST['item_qty']){
			foreach($_REQUEST['item_qty'] as $gi_id=>$qty){
			    if(!$qty){  // remove item
					$con->sql_query("delete from gra_items where branch_id=$branch_id and gra_id=$id and id=".mi($gi_id));
				}else{
					$upd = array();
					$upd['qty'] = $qty;
					$upd['cost'] = $_REQUEST['item_price'][$gi_id];
					$upd['doc_no'] = $_REQUEST['doc_no'][$gi_id];
					$curr_gst_id = $old_gst = $_REQUEST['old_gst_id'][$gi_id];
					$new_gst = $_REQUEST['gst_id'][$gi_id];
					if($new_gst && $old_gst != $new_gst){ // get new info if gst have been changed
						$upd['gst_id'] = $curr_gst_id = $new_gst;
						$upd['gst_code'] = $gst_list[$new_gst]['code'];
						$upd['gst_rate'] = $gst_list[$new_gst]['rate'];
					}
					
					// calculate gst
					$upd['gst']=0;
					$upd['amount_gst']=0;
					$curr_selling_price = $_REQUEST['selling_price'][$gi_id];
					if($curr_gst_id){
						$curr_gst_rate = $gst_list[$curr_gst_id]['rate'];
						
						// calculate gst selling price
						$is_inclusive_tax = get_sku_gst("inclusive_tax", $_REQUEST['sku_item_id'][$gi_id]);

						$prms = array();
						if($is_inclusive_tax == "yes" && $_REQUEST['gst_selling_price'][$gi_id] > 0) $prms['selling_price'] = $_REQUEST['gst_selling_price'][$gi_id];
						else  $prms['selling_price'] = $curr_selling_price;
						$prms['inclusive_tax'] = $is_inclusive_tax;
						$prms['gst_rate'] = $curr_gst_rate;
						$gst_sp_info = calculate_gst_sp($prms);
						
						$upd['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
						
						if($is_inclusive_tax == "yes"){
							if($_REQUEST['gst_selling_price'][$gi_id] > 0) $upd['gst_selling_price'] = $_REQUEST['gst_selling_price'][$gi_id];
							else $upd['gst_selling_price'] = $curr_selling_price;
							$upd['selling_price'] = $gst_sp_info['gst_selling_price'];
						}else{
							$upd['selling_price'] = $curr_selling_price;
						}
					}else{
						$upd['selling_price'] = $curr_selling_price;
					}
					
					if($_SESSION['gra']['sku_type']=='CONSIGN')
						$upd['amount'] = $upd['qty'] * $upd['selling_price'];
					else
						$upd['amount'] = $upd['qty'] * $upd['cost'];
					
					if($curr_gst_id){
						$upd['amount_gst']=round($upd['amount'] * ((100+$curr_gst_rate)/100),2);
                        $upd['gst']=$upd['amount_gst']-round($upd['amount'], 2);
					}
					
					$upd['amount']=round($upd['amount'],2);
					
                    $con->sql_query("update gra_items set ".mysql_update_by_field($upd)." where branch_id=".mi($branch_id)." and id=".mi($gi_id));
				}
			}
			
			$this->update_gra_total($branch_id, $id);
		}
		header("Location: $_SERVER[PHP_SELF]?a=view_items");
	}
	
	private function update_gra_total($branch_id, $id){
	    global $con;
        $con->sql_query("select sum(gi.qty*if(gra.sku_type = 'CONSIGN', gi.selling_price, gi.cost)) as total_amt, gra.extra_amount
						 from gra_items gi
						 left join gra on gra.id = gi.gra_id and gra.branch_id = gi.branch_id
						 where gi.branch_id=$branch_id and gi.gra_id=$id");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$upd['amount'] = $form['total_amt']+$form['extra_amount'];
		
		$con->sql_query("update gra set ".mysql_update_by_field($upd)." where branch_id=$branch_id and id=$id");
	}
	
	function delete_items(){
		global $con, $smarty;
		$id = mi($_SESSION['gra']['id']);
        $branch_id = mi($_SESSION['gra']['branch_id']);

        if(!$id||!$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
		if($_REQUEST['item_chx']){
            $con->sql_query("delete from gra_items where gra_id=$id and branch_id=$branch_id and id in (".join(',',array_keys($_REQUEST['item_chx'])).")");
			$this->update_gra_total($branch_id, $id);
		}
		header("Location: $_SERVER[PHP_SELF]?a=view_items");
	}
	
	function open(){
		global $con, $smarty, $sessioninfo;

		if(isset($_REQUEST['gra_no'])){
			$branch_id = mi($sessioninfo['branch_id']);
			$id = mi($_REQUEST['gra_no']);
			//$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
			//$report_prefix = $con->sql_fetchfield(0);

			$sql = "select gra.*,vendor.code as vendor_code, vendor.description as vendor_desc
			from gra
			left join vendor on vendor.id=gra.vendor_id
			where gra.branch_id=$branch_id and gra.id=$id and gra.status=0 and gra.returned=0 and gra.approved = 0";
			$q1 = $con->sql_query($sql);
			if($con->sql_numrows($q1)<=0){
				$err[] = "No GRA Found with $id.";
			}else{
				while($r = $con->sql_fetchassoc($q1)){
					$gra_list[] = $r;
				}

				$smarty->assign('gra_list',$gra_list);
			}
			$con->sql_freeresult($q1);
		}
		$smarty->assign('err',$err);
		$smarty->display('goods_return_advice.search.tpl');
	}
	
	function change_gra(){
	    global $con,$smarty;

        $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(!$id||!$branch_id){
           js_redirect('Invalid GRA', "index.php");
           exit;
		}else{
			$this->reset_module_session($id,$branch_id);
		}

		header("Location: $_SERVER[PHP_SELF]");
	}

	function add_item_by_grn_barcode(){
		global $con, $smarty;

		$code = trim($_REQUEST['product_code']);
		if (empty($code)) return;
		
		
		if (preg_match("/^00/", $code))	// form ARMS' GRN barcoder
		{
			//test code 0010001001
			$sku_item_id=mi(substr($code,0,8));
			$qty = mi(substr($code,8,4));
			$sql = "select id from sku_items where id = ".mi($sku_item_id);
		}
		else	// from ATP GRN Barcode, try to search the link-code 
		{
			//test code 100301500013
			$linkcode=substr($code,0,7);
			$qty = mi(substr($code,7,5));
			$sql = "select id from sku_items where link_code = ".ms($linkcode);
		}
		$q1 = $con->sql_query($sql);
		$r1 = $con->sql_fetchrow($q1);
		$con->sql_freeresult($q1);
		
		if($r1 && $qty){
			$_REQUEST['item_qty'][$r1['id']] = $qty;
			$this->add_items();
			// get item info
			$con->sql_query("select count(*) as total_item, sum(qty) as total_pcs from gra_items where gra_id=".mi($_SESSION['gra']['id'])." and branch_id=".mi($_SESSION['gra']['branch_id'])) or die(mysql_error());
			$smarty->assign('items_details',$con->sql_fetchrow());
		}
		elseif (!$r1) $this->err[] = "The item (".$code.") not found!";
		elseif (!$qty) $this->err[] = "Invalid quantity!";;
	}
	
	function dumpdata($what) {
		echo '<pre>';
		print_r($what);
		echo '</pre>';
	}
}

$GRA_Module = new GRA_Module('GRA');
?>
