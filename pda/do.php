<?php
/*
3/2/2011 10:53:36 AM Andy
- Fix scan product class bugs

6/24/2011 5:07:13 PM Andy
- Make all branch default sort by sequence, code.

3/15/2012 11:05:32 AM Justin
- Added "/pda" to redirect user back to pda login menu page.

6/14/2012 4:31:34 PM Justin
- Added new function to auto add item when got check "Add item when match one result" from module.

7/27/2012 4:42:34 PM Justin
- Added new function to allow item can scan by GRN barcoder.

8/7/2012 5:57 PM Justin
- Enhanced to accept new grn barcode scanning format.

8/29/2012 1:36 PM Andy
- Add privilege checking for DO, GRR, GRN, Adj, Stock Take and Voucher.

3/26/2013 5:12 PM Justin
- Bug fixed on system straight die the page with error, causing some PDA hardware cannot run properly.

4/12/2013 4:33 PM Andy
- Enhance when user add/edit/delete item, it will mark DO amount need to update.

4/1/2014 2:46 PM Justin
- Enhanced to have DO checklist feature.

5/22/2014 11:45 AM Justin
- Enhanced the search DO to search for DO No as well.

2/9/2015 3:01 PM Andy
- Enhance to capture GST data.

4/29/2015 10:09 AM Andy
- Enhanced to have "Display Cost" features.

8/3/2017 9:47 AM Justin
- Enhanced to update qty to existing DO item if found config "do_item_allow_duplicate" is turned off.
- Enhanced to disable user for editting Transfer DO that contains multiple delivery branch.	

6/19/2018 1:30 PM Andy
- Fixed to only search active sku.

9/18/2020 1:02 PM William
- Enhanced to block closed month document create and save when config "monthly_closing" and "monthly_closing_block_document_action" is active.

*/
include("common.php");
include("class.scan_product.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/pda/index.php");
if (!privilege('DO')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DO', BRANCH_CODE), "/pda");

$maintenance->check(196);

class DO_Module extends Scan_Product{

    function __construct($title){
        global $sessioninfo;
		
		if(preg_match("/checklist/", $_REQUEST['a'])){
			$_SESSION['scan_product']['type'] = 'DO Checklist';
			$_SESSION['scan_product']['name'] = isset($_REQUEST['id']) ? 'DO#'.$_REQUEST['id']."(DD)" : '';
		}else{
			$_SESSION['scan_product']['type'] = 'DO';
			$_SESSION['scan_product']['name'] = isset($_SESSION['do']['id']) ? 'DO#'.$_SESSION['do']['id']."(DD)" : '';
		}

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

		if($_SESSION['do']['do_type'])	$smarty->assign('do_type',$_SESSION['do']['do_type']);
		elseif($_REQUEST['do_type'])    $smarty->assign('do_type',$_REQUEST['do_type']);
		else    $smarty->assign('do_type','transfer');
		$do_type = $smarty->get_template_vars('do_type');

		if($do_type=='open')    $smarty->assign('module_name','Cash Sales DO');
		elseif($do_type=='credit_sales')    $smarty->assign('module_name','Credit Sales DO');
		else    $smarty->assign('module_name','Transfer DO');

		$smarty->assign('PAGE_TITLE','DO');
		$smarty->assign('top_include','do.top_include.tpl');
		$smarty->assign('btm_include','do.btm_include.tpl');
	}
	
	function default_(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		
		if($id&&$branch_id){
			$this->reset_session_do($id,$branch_id);
		}else{
            $id = mi($_SESSION['do']['id']);
            $branch_id = mi($_SESSION['do']['branch_id']);
		}
		
		if($id>0){
			$this->show_scan_product();
		}else{
			$this->show_setting();
		}
	}
	
	function new_do(){
		unset($_SESSION['do']);
		if($_REQUEST['do_type'])	$_SESSION['do']['do_type'] = $_REQUEST['do_type'];
		header("Location: do.php");
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
		$branches_group = array();
		// load header
		$con->sql_query("select * from branch_group",false,false);
		// if got branches group, load items
		if($con->sql_numrows()>0){
            while($r = $con->sql_fetchrow()){
	            $branches_group['header'][$r['id']] = $r;
			}
			// load items
			$con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 order by branch.sequence, branch.code",false,false);
			while($r = $con->sql_fetchrow()){
		        $branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
		        $branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
			}
			
			$smarty->assign('branches_group',$branches_group);
		}
		
		$con->sql_query("select * from debtor where active=1 order by code",false,false);
		if($con->sql_numrows()>0){
			while($r = $con->sql_fetchrow()){
				$debtors[$r['id']] = $r;
			}
			$smarty->assign('debtors',$debtors);
		}
	}
	
	function show_setting(){
		global $con, $smarty;
		
		$this->default_load();
		
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		
		if(!$id||!$branch_id){
            $id = mi($_SESSION['do']['id']);
            $branch_id = mi($_SESSION['do']['branch_id']);
		}
		
		if($id>0&&$branch_id>0){
		    $this->reset_session_do($id,$branch_id);
			$con->sql_query("select * from do where id=$id and branch_id=$branch_id") or die(mysql_error());
			$form = $con->sql_fetchrow();
			$form['open_info'] = unserialize($form['open_info']);
			$smarty->assign('form', $form);
		}
		$_REQUEST['do_tab'] = 'setting';
		$smarty->display('do.index.tpl');
	}
	
	function save_setting(){
		global $con, $smarty, $sessioninfo, $config, $appCore, $LANG;
		
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$do_branch_id = mi($_REQUEST['do_branch_id']);
		$do_date = $_REQUEST['do_date'];
		$do_type = $_REQUEST['do_type'];
		$open_info = $_REQUEST['open_info'];
		
        $upd = $err = $form = array();
        
        $upd['do_branch_id'] = $do_branch_id;
		$upd['do_date'] = $do_date;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['user_id'] = $sessioninfo['id'];
		$upd['do_type'] = $do_type;
		$upd['debtor_id'] = mi($_REQUEST['debtor_id']);
		$upd['open_info'] = $open_info;
		
		$form = $upd;
		if($id && $branch_id){
			$form['id'] = $id;
			$form['branch_id'] = $branch_id;
		}
		
		//check is_month_closed
		if($config['monthly_closing'] && $config['monthly_closing_block_document_action']){
			$is_month_closed = $appCore->is_month_closed($do_date);
			if($is_month_closed)  $err[] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];
		}
		
		// validating
		if($do_type=='open'){
			if(!trim($upd['open_info']['name']))  $err[] = "Invalid Company Name";
			if(!trim($upd['open_info']['address']))   $err[] = "Invalid Address";
		}elseif($do_type=='credit_sales'){
            if(!$upd['debtor_id'])   $err[] = "Invalid Debtor";
		}else{  // transfer
            if(!$upd['do_branch_id'])   $err[] = "Invalid Deliver To Branch";
		}
		
		
		if($err){
			$this->default_load();
			$_REQUEST['do_tab'] = 'setting';
			$smarty->assign('form',$form);
			$smarty->assign('err',$err);
			$smarty->display('do.index.tpl');
			exit;
		}
		$upd['open_info'] = serialize($upd['open_info']);
		
		if($config['enable_gst'] && !$config['consignment_modules']){
			$prm = $upd;
			$prm['branch_id'] = $branch_id;
			
			$upd['is_under_gst'] = $this->check_do_gst_status($prm);
		}
		
		// old DO
		if($id>0){
            $con->sql_query("update do set ".mysql_update_by_field($upd)." where id=$id and branch_id=$branch_id") or die(mysql_error());
		}else{  // new DO
			$upd['branch_id'] = $branch_id;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			if($config['do_default_price_from']=='cost')    $upd['price_indicate'] = 1;
			elseif($config['do_default_price_from']=='last_do')    $upd['price_indicate'] = 3;
			else    $upd['price_indicate'] = 2;
			
			$con->sql_query("insert into do ".mysql_insert_by_field($upd)) or die(mysql_error());
			$id = $con->sql_nextid();
		}
		
		$this->reset_session_do($id,$branch_id);
		header("Location: do.php");
	}
	
	private function reset_session_do($id,$branch_id){
	    global $con;
	    
	    $con->sql_query("select id,branch_id,do_branch_id,do_type,debtor_id from do where id=$id and branch_id=$branch_id") or die(mysql_error());
		$form = $con->sql_fetchrow();
		if(!$form)	js_redirect('Invalid DO', "index.php");
	    
        $_SESSION['do'] = $form;
	}
	
	function show_scan_product(){
		global $con, $smarty;
		
		$id = mi($_SESSION['do']['id']);
        $branch_id = mi($_SESSION['do']['branch_id']);
		// check DO exists or not
		$con->sql_query("select * from do where id=$id and branch_id=$branch_id and active=1") or die(mysql_error());
		$form = $con->sql_fetchrow();
		
		if(!$form){
            js_redirect('Invalid DO', "index.php");
            exit;
		}
		
		$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
		$report_prefix = $con->sql_fetchfield(0);
		$do_no = $report_prefix.sprintf('%05d',$id);
		
		//$_SESSION['scan_product']['type'] = 'DO';
		//$_SESSION['scan_product']['name'] = 'DO#'.$id."(DD)";

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
			$_REQUEST['item_qty'][$sku_info['id']] = 1;
			$this->add_items();
			unset($_REQUEST);
			$smarty->assign("auto_add", 1);
		}
		
		// get item info
		$con->sql_query("select count(*) as total_item,sum(pcs) as total_pcs from do_items where do_id=$id and branch_id=$branch_id") or die(mysql_error());
		$smarty->assign('items_details',$con->sql_fetchrow());
		
		$this->search_product();
	}
	
	function add_items(){
		global $con,$config,$sessioninfo,$smarty;
        $id = mi($_SESSION['do']['id']);
        $branch_id = mi($_SESSION['do']['branch_id']);
        $do_branch_id = mi($_SESSION['do']['do_branch_id']);
        
		$con->sql_query("select * from do where branch_id=$branch_id and id=$id");
		$do = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
        $upd = array();
        $upd['do_id'] = $id;
        $upd['branch_id'] = $branch_id;
        $upd['uom_id'] = 1;
        $upd['user_id'] = $sessioninfo['id'];
        
		//check is_month_closed
		if($config['monthly_closing'] && $config['monthly_closing_block_document_action']){
			$err = $this->check_closed_month($id, $branch_id);
			if($err){
				if($_REQUEST['auto_add_item'])   $this->err = $err;
				else   $ret['error'] = $err;
				
				// get item info
				$con->sql_query("select count(*) as total_item,sum(pcs) as total_pcs from do_items where do_id=$id and branch_id=$branch_id") or die(mysql_error());
				$smarty->assign('items_details',$con->sql_fetchrow());
				
				return $ret;
			}
		}
		
		//print_r($_REQUEST);
		$items = $_REQUEST['item_qty'];
		if($items){
		    $total_qty = 0;
			foreach($items as $sid=>$qty){
				if($qty>0 || ($qty==0 && $_REQUEST['empty_decimal_points'])){
					$item_need_add[$sid] = round($qty, $config['global_qty_decimal_points']);
					$total_qty += $qty;
				}
			}

			if($total_qty<=0 && !$_REQUEST['empty_decimal_points']){
                $ret['error'][] = "Invalid quantity";
			}else{
			    // get sku items info
			    $sql = "select * from sku_items where id in (".join(',',array_keys($item_need_add)).")";
			    $q_s = $con->sql_query($sql) or die(mysql_error());
			    while($r = $con->sql_fetchrow($q_s)){
					if(!isset($_REQUEST['item_cp'][$r['id']])){
						// get cost price
						$price_type = $config['do_default_price_from'] ? $config['do_default_price_from']:'selling';
						if($price_type=='last_do'){
							// get last DO price
							$q_p = $con->sql_query("select cost_price as cost_price, display_cost_price_is_inclusive, display_cost_price
							from do_items 
							left join do on do_id = do.id and do_items.branch_id = do.branch_id 
							where do.active=1 and do_items.sku_item_id=".mi($r['id'])." and do_items.branch_id=$branch_id order by do_items.id desc limit 1");
						}
						elseif($price_type=='selling'){
							// get selling price
							$q_p = $con->sql_query("select price as cost_price from sku_items_price where sku_item_id=".mi($r['id'])." and branch_id=$branch_id");
						}
						elseif($price_type=='cost'){
							// cost
							$q_p = $con->sql_query("select grn_cost as cost_price from sku_items_cost where sku_item_id=".mi($r['id'])." and branch_id=$branch_id");
						}
						$temp_p = $con->sql_fetchrow($q_p);
						if(!$temp_p){
							if ($price_type=='last_do' or $price_type=='cost'){ // DO or GRN selected
								$q_m = $con->sql_query("select if(grn_cost is null, cost_price, grn_cost) as cost_price from  sku_items left join sku_items_cost on sku_item_id=sku_items.id and branch_id=$branch_id where id=".mi($r['id']));
							}
							else
							{
								$q_m = $con->sql_query("select if(price is null, selling_price, price) as cost_price from sku_items left join sku_items_price on sku_item_id=sku_items.id and branch_id=$branch_id where id=".mi($r['id']));
							}
							$temp_p = $con->sql_fetchrow($q_m);
						}
						
						$r['do_cost_price'] = $temp_p['cost_price'];
						if(isset($temp_p['display_cost_price_is_inclusive'])) $r['display_cost_price_is_inclusive'] = $temp_p['display_cost_price_is_inclusive'];
						if(isset($temp_p['display_cost_price'])) $r['display_cost_price'] = $temp_p['display_cost_price'];
						
						// find price before gst
						if($config['enable_gst'] && $price_type != 'last_do' && $price_type != 'cost'){
							// get sku is inclusive
							$is_sku_inclusive = get_sku_gst("inclusive_tax", $r['id']);
							// get sku original output gst
							$sku_original_output_gst = get_sku_gst("output_tax", $r['id']);
							
							if($is_sku_inclusive == 'yes'){
								// is inclusive tax
								$price_included_gst = $r['do_cost_price'];
								$r['display_cost_price_is_inclusive'] = 1;
								$r['display_cost_price'] = $price_included_gst;
								
								// find the price before tax
								$gst_tax_price = $r['do_cost_price'] / ($sku_original_output_gst['rate']+100) * $sku_original_output_gst['rate'];
								$r['do_cost_price'] = $price_included_gst - $gst_tax_price;
							}
						}
					}else{
						if(!$_REQUEST['item_cp'][$r['id']]){
							$this->err[] = "Unable to find Trade Discount for SKU Item [".$r['sku_item_code']."], whereas cost auto set to empty.";
						}
						$r['do_cost_price'] = $_REQUEST['item_cp'][$r['id']];
					}
					
					if($_REQUEST['item_sp'][$r['id']]) $r['selling_price'] = $_REQUEST['item_sp'][$r['id']];
					
					if($config['enable_gst'] && $do['is_under_gst']){
						$output_gst = get_sku_gst("output_tax", $r['id']);
						//print_r($output_gst);exit;
						if($output_gst){
							$r['gst_id'] = $output_gst['id'];
							$r['gst_code'] = $output_gst['code'];
							$r['gst_rate'] = $output_gst['rate'];
						}
					}
		
					// get stock balance
					$sql = "select branch_id,sku_item_id,qty from sku_items_cost where sku_item_id=$r[id] and branch_id in ($branch_id,$do_branch_id)";
					$q_b = $con->sql_query($sql) or die(mysql_error());
					while($b = $con->sql_fetchrow($q_b)){
						if($b['branch_id']==$branch_id) $col = 'stock_balance1';
						else    $col = 'stock_balance2';
						$r[$col] = $b['qty'];
					}
					$sku_items[$r['id']] = $r;
				}
				
				foreach($item_need_add as $sid=>$qty){
					$upd['sku_item_id'] = $sid;
					$upd['artno_mcode'] = $sku_items[$sid]['artno']?$sku_items[$sid]['artno']:$sku_items[$sid]['mcode'];
					$upd['cost_price'] = $sku_items[$sid]['do_cost_price'];
					$upd['selling_price'] = $sku_items[$sid]['selling_price'];
					$upd['pcs'] = $qty;
					$upd['stock_balance1'] = $sku_items[$sid]['stock_balance1'];
					$upd['stock_balance2'] = $sku_items[$sid]['stock_balance2'];
					
					if($config['enable_gst'] && $do['is_under_gst']){
						$upd['gst_id'] = $sku_items[$sid]['gst_id'];
						$upd['gst_code'] = $sku_items[$sid]['gst_code'];
						$upd['gst_rate'] = $sku_items[$sid]['gst_rate'];
					}
					$upd['display_cost_price_is_inclusive'] = $sku_items[$sid]['display_cost_price_is_inclusive'];
					$upd['display_cost_price'] = $sku_items[$sid]['display_cost_price'];
					
					$di_info = array();
					if(!$config['do_item_allow_duplicate']){
						$q1 = $con->sql_query("select * from do_items where do_id = ".mi($id)." and branch_id = ".mi($branch_id)." and sku_item_id = ".mi($sid));
						$di_info = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
					}
					
					if(!$config['do_item_allow_duplicate'] && $di_info['id']){
						$di_upd = array();
						$con->sql_query("update do_items set pcs = pcs+".mf($qty)." where id = ".mi($di_info['id'])." and do_id = ".mi($di_info['do_id'])." and branch_id = ".mi($di_info['branch_id'])." and sku_item_id = ".mi($di_info['sku_item_id']));
					}else{
						$con->sql_query("insert into do_items ".mysql_insert_by_field($upd)) or die(mysql_error());
					}
					
					$total_pcs += $qty;
					$total_amount += mf($qty*$upd['cost_price']);
				}
				
				// update main do
				//$con->sql_query("update do set total_pcs=if(total_pcs is null,$total_pcs,total_pcs+$total_pcs), total_qty=if(total_qty is null,$total_pcs,total_qty+$total_pcs), total_amount=if(total_amount is null,$total_amount,total_amount+$total_amount) where id=$id and branch_id=$branch_id") or die(mysql_error());
				$this->mark_do_amt_need_update($branch_id, $id);
				
				$ret['success'] = true;
			}
		}else{
            $ret['error'][] = "No items found";
		}
		
		return $ret;
	}
	
	function open($is_checklist=false){
		global $con, $smarty, $sessioninfo;
		
		if(isset($_REQUEST['do_no'])){
			$branch_id = mi($sessioninfo['branch_id']);
			$id = mi($_REQUEST['do_no']);
			$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
			$report_prefix = $con->sql_fetchfield(0);
			
			$filters = array();
			if($is_checklist){
				$filters[] = "do.do_no is not null and do.approved=1 and do.status=1";
			}else{
				$filters[] = "do.approved<>1 and do.status=0";
			}
			$filter = join(" and ", $filters);

			$sql = "select do.*,branch.code as do_branch_code, branch.description as do_branch_description,debtor.code as debtor_code from do
			left join branch on branch.id=do.do_branch_id
			left join debtor on debtor.id=do.debtor_id
			where do.branch_id=$branch_id and (do.id=$id or do.do_no = ".ms($_REQUEST['do_no']).") and do.active=1 and do.checkout!=1 and $filter";
			$q1 = $con->sql_query($sql) or die(mysql_error());
			if($con->sql_numrows()<=0){
				$err[] = "No DO Found with $id.";
			}else{
				while($r = $con->sql_fetchassoc($q1)){
					$r['do_no2'] = $report_prefix.sprintf('%05d',$r['id']);
					$r['open_info'] = unserialize($r['open_info']);
					$r['deliver_branch'] = unserialize($r['deliver_branch']);
					$do_list[] = $r;
				}
				$con->sql_freeresult($q1);
				
				$smarty->assign('do_list',$do_list);
			}
		}
		$smarty->assign('err',$err);
		$smarty->display('do.search.tpl');
	}
	
	function view_items(){
		global $con, $smarty;
		$id = mi($_SESSION['do']['id']);
        $branch_id = mi($_SESSION['do']['branch_id']);
        
        if(!$id||!$branch_id){
			header("Location: do.php");
			exit;
		}
		
        $con->sql_query("select di.*,si.sku_item_code,si.description as sku_description, si.doc_allow_decimal from do_items di left join sku_items si on si.id=di.sku_item_id where di.do_id=$id and di.branch_id=$branch_id order by di.id") or die(mysql_error());
        $smarty->assign('items',$con->sql_fetchrowset());
        
		$_REQUEST['do_tab'] = 'view_items';
		$smarty->display('do.view_items.tpl');
	}
	
	function delete_items(){
		global $con, $smarty, $config;
		$id = mi($_SESSION['do']['id']);
        $branch_id = mi($_SESSION['do']['branch_id']);

        if(!$id||!$branch_id){
			header("Location: do.php");
			exit;
		}
		
		//check monthly closed
		$err = array();
		if($config['monthly_closing'] && $config['monthly_closing_block_document_action']) $err = $this->check_closed_month($id, $branch_id);
		if($err){
			$_REQUEST['do_tab'] = 'view_items';
			$con->sql_query("select di.*,si.sku_item_code,si.description as sku_description, si.doc_allow_decimal from do_items di left join sku_items si on si.id=di.sku_item_id where di.do_id=$id and di.branch_id=$branch_id order by di.id") or die(mysql_error());
			$smarty->assign('items',$con->sql_fetchrowset());
			$smarty->assign('err',$err);
			$smarty->display('do.view_items.tpl');
			exit;
		}
		
		if($_REQUEST['item_chx']){
            $con->sql_query("delete from do_items where do_id=$id and branch_id=$branch_id and id in (".join(',',array_keys($_REQUEST['item_chx'])).")") or die(mysql_error());
		}
		
		$this->mark_do_amt_need_update($branch_id, $id);
		
		header("Location: do.php?a=view_items");
	}
	
	function save_items(){
        global $con, $smarty, $config;
		$id = mi($_SESSION['do']['id']);
        $branch_id = mi($_SESSION['do']['branch_id']);

        if(!$id||!$branch_id){
			header("Location: do.php");
			exit;
		}
		
		//check monthly closed
		$err = array();
		if($config['monthly_closing'] && $config['monthly_closing_block_document_action']) $err = $this->check_closed_month($id, $branch_id);
		if($err){
			$_REQUEST['do_tab'] = 'view_items';
			$con->sql_query("select di.*,si.sku_item_code,si.description as sku_description, si.doc_allow_decimal from do_items di left join sku_items si on si.id=di.sku_item_id where di.do_id=$id and di.branch_id=$branch_id order by di.id") or die(mysql_error());
			$smarty->assign('items',$con->sql_fetchrowset());
			$smarty->assign('err',$err);
			$smarty->display('do.view_items.tpl');
			exit;
		}
		
        if($_REQUEST['item_qty']){
			foreach($_REQUEST['item_qty'] as $diid=>$qty){
				$con->sql_query("update do_items set pcs=".mf($qty)." where do_id=$id and branch_id=$branch_id and id=$diid") or die(mysql_error());
			}
		}
		
		$this->mark_do_amt_need_update($branch_id, $id);
		
		header("Location: do.php?a=view_items");
	}
	
	function change_do(){
	    global $con,$smarty;
	    
        $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		
		if(!$id||!$branch_id){
           js_redirect('Invalid DO', "index.php");
           exit;
		}else{
			$this->reset_session_do($id,$branch_id);
		}

		header("Location: do.php?a=view_items");
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
			$con->sql_query("select count(*) as total_item,sum(pcs) as total_pcs from do_items where do_id=".mi($_SESSION['do']['id'])." and branch_id=".mi($_SESSION['do']['branch_id'])) or die(mysql_error());
			$smarty->assign('items_details',$con->sql_fetchrow());
		}elseif($code) $this->err[] = "The item (".$code.") not found!";
	}
	
	private function mark_do_amt_need_update($branch_id, $id){
		global $con;
		
		$upd_do = array();
		$upd_do['amt_need_update'] = 1;
		$upd_do['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update do set ".mysql_update_by_field($upd_do)." where id=$id and branch_id=$branch_id") or die(mysql_error());
	}
	
	function open_checklist(){
		global $con, $smarty;
	
		$smarty->assign("is_checklist", true);
		$this->open(true);
	}
	
	function view_checklist_items(){
		global $con, $smarty;	
		
		$form = $_REQUEST;
		$id = mi($form['id']);
		$bid = mi($form['branch_id']);
		
		if(!$id || !$bid){
			header("Location: do.php");
			exit;
		}
		
		$q1 = $con->sql_query("select dbi.*, si.sku_id, si.doc_allow_decimal
							   from do_barcode_items dbi
							   left join sku_items si on si.id = dbi.sku_item_id
							   where dbi.do_id = ".mi($id)." and dbi.branch_id = ".mi($bid)."
							   order by dbi.id");
		
		while($r = $con->sql_fetchassoc($q1)){
			$row++;
			$items[$row] = $r;
		}
		
		$smarty->assign("items", $items);
		$smarty->assign("form", $_REQUEST);
		$smarty->assign("do_tab", "view_checklist");
		$smarty->display("do.checklist.view_items.tpl");
	}
	
	function save_checklist_items(){
		global $con, $smarty, $sessioninfo, $LANG;
		
		$form = $_REQUEST;
		$id = mi($form['id']);
		$bid = mi($form['branch_id']);
		
		if(!$id || !$bid){
			header("Location: do.php");
			exit;
		}
		
		$invalid_barcode = $this->checklist_validate($form);
		
		// found having invalid barcodes, present error message
		if($invalid_barcode){	
			$err[] = sprintf($LANG["DO_CHECKOUT_INVALID_BARCODE"], join(",", $invalid_barcode));
			$smarty->assign("form", $form);
			//$smarty->assign("items", $items);
			$smarty->assign("err", $err);
			$smarty->assign("do_tab", "view_checklist");
			$smarty->display("do.checklist.view_items.tpl");
			exit;
		}
		
		$con->sql_query("delete from do_barcode_items where do_id = ".mi($id)." and branch_id = ".mi($bid));
		
		foreach($form['barcode'] as $item_id=>$barcode){
			$barcode = trim($barcode);
			if(!$barcode) continue;

			$upd = array();
			$upd['barcode'] = $barcode;
			$upd['qty'] = $form['qty'][$item_id];
			$upd['last_update'] = "CURRENT_TIMETSAMP";
			
			$con->sql_query("update do_barcode_items set ".mysql_update_by_field($upd)." where id = ".mi($item_id)." and branch_id = ".mi($bid));
		}
		
		$smarty->assign("form", $_REQUEST);
		$smarty->assign("do_tab", "add_checklist");
		$smarty->display("do.checklist.add_item.tpl");
	}
	
	function delete_checklist_items(){
		global $con, $smarty;

		$id = mi($_REQUEST['id']);
		$bid = mi($_REQUEST['branch_id']);

        if(!$id || !$bid){
			header("Location: do.php");
			exit;
		}
		
		if($_REQUEST['item_chx']){
            $con->sql_query("delete from do_barcode_items where do_id=$id and branch_id=$bid and id in (".join(',',array_keys($_REQUEST['item_chx'])).")");
		}
		
		$this->view_checklist_items();
	}
	
	function scan_checklist_item(){
		global $con, $smarty, $LANG;
		
		$form = $_REQUEST;
		$id = mi($form['id']);
		$bid = mi($form['branch_id']);
		
		if(!$id || !$bid){
			header("Location: do.php");
			exit;
		}
		
		// get item info
		$q1 = $con->sql_query("select count(*) as total_item,sum(qty) as total_pcs from do_barcode_items where do_id=$id and branch_id=$bid");
		$smarty->assign('items_details',$con->sql_fetchassoc($q1));
		$con->sql_freeresult($q1);
		
		unset($_REQUEST['barcode']);
		unset($_REQUEST['qty']);

		$smarty->assign("form", $_REQUEST);
		$smarty->assign("do_tab", "add_checklist");
		$smarty->display("do.checklist.add_item.tpl");
	}
	
	function add_checklist_item(){
		global $con, $smarty, $sessioninfo, $LANG;

		$id = mi($_REQUEST['id']);
		$bid = mi($_REQUEST['branch_id']);
		
		if(!$id || !$bid){
			header("Location: do.php");
			exit;
		}
		
		$form = array();
		$form['id'] = $id;
		$form['branch_id'] = $bid;
		$form['barcode'][1] = $_REQUEST['barcode'];
		$form['qty'][1] = $_REQUEST['qty'];
		
		$invalid_barcode = $this->checklist_validate($form);
		
		// found having invalid barcodes, present error message
		if($invalid_barcode){	
			$err[] = sprintf($LANG["DO_CHECKOUT_INVALID_BARCODE"], join(",", $invalid_barcode));
			$smarty->assign("form", $_REQUEST);
			//$smarty->assign("items", $items);
			$smarty->assign("err", $err);
			$this->scan_checklist_item();
			exit;
		}
		
		foreach($form['barcode'] as $row=>$barcode){
			$barcode = trim($barcode);
			if(!$barcode) continue;

			$ins = array();
			$ins['do_id'] = $id;
			$ins['branch_id'] = $bid;
			$ins['sku_item_id'] = $form['sku_item_id'][$row];
			$ins['user_id'] = $sessioninfo['id'];
			$ins['barcode'] = $barcode;
			$ins['qty'] = $form['qty'][$row];
			$ins['last_update'] = $ins['added'] = "CURRENT_TIMETSAMP";
			
			$con->sql_query("replace into do_barcode_items ".mysql_insert_by_field($ins));
		}
		
		$smarty->assign("item_added", true);
		$this->scan_checklist_item();
	}
	
	function checklist_validate(&$form){
		global $con, $smarty;

		if(!$form['barcode']) return;

		// validate barcode
		foreach($form['barcode'] as $row=>$barcode){
			$barcode = trim($barcode);
			if(!$barcode) continue;
			
			$filter = "(sku_item_code = ".ms($barcode)." or artno = ".ms($barcode)." or link_code = ".ms($barcode)." or mcode = ".ms($barcode).")";
			$q1 = $con->sql_query("select * from sku_items where ".$filter." limit 1");
			
			// found it is not a valid SKU item
			if($con->sql_numrows($q1) == 0){
				$invalid_barcode[] = $barcode;
			}else{
				$si_info = $con->sql_fetchassoc($q1);
				
				// check if it is existed in DO items
				$q2 = $con->sql_query("select * from do_items where do_id = ".mi($form['id'])." and branch_id = ".mi($form['branch_id'])." and sku_item_id = ".mi($si_info['id']));
				
				if($con->sql_numrows($q2) == 0) $invalid_barcode[] = $barcode;
				else $form['sku_item_id'][$row] = $si_info['id'];
				
				$con->sql_freeresult($q2);
			}
			$con->sql_freeresult($q1);
			$tmp = array();
			$tmp['barcode'] = $barcode;
			$tmp['qty'] = $form['qty'][$row];
			$items[$row] = $tmp;
		}
		
		$smarty->assign("items", $items);

		return $invalid_barcode;
	}
	
	function check_do_gst_status($form){
		global $config;
		
		$is_under_gst = 0;
		// check whether this do is under gst
		if($config['enable_gst'] && !$config['consignment_modules']){
			$params = array();
			$params['date'] = $form['do_date'];
			$params['branch_id'] = $form['branch_id'];
			
			if($form['do_type']=='transfer'){
				// Transfer DO
				if($form['do_branch_id']){
					// single branch
					$params['to_branch_id'] = $form['do_branch_id'];
					$is_under_gst = check_gst_status($params);
				}elseif($form['deliver_branch']){
					// multi branch
					
					foreach($form['deliver_branch'] as $bid){
						$params['to_branch_id'] = $bid;
						$tmp_is_under_gst = check_gst_status($params);
						if($tmp_is_under_gst){
							$is_under_gst = 1;
						}else{
							$is_under_gst = 0;
							break;
						}
					}
				}
			}else{
				// cash sales & credit sales no need check gst interbranch
				$is_under_gst = check_gst_status($params);
			}
		
		}else{
			$is_under_gst = 0;
		}
		
		if($is_under_gst){
			construct_gst_list();
		}
		return $is_under_gst;
	}
	
	
	function check_closed_month($id, $branch_id){
		global $con, $smarty, $sessioninfo, $appCore, $LANG;
		
		$err = array();
		$id = mi($id);
		$branch_id = mi($branch_id);
		
		if($id && $branch_id){
			$q1=$con->sql_query("select do_date from do where id=$id and branch_id=$branch_id");
			$r = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$is_month_closed = $appCore->is_month_closed($r['do_date']);
			if($is_month_closed)  $err[] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];
		}
		
		return $err;
	}
}

//print_r($_SESSION);
$do_module = new DO_Module('DO');

?>
