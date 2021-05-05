<?php
/*
6/24/2011 5:09:32 PM Andy
- Make all branch default sort by sequence, code.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.

10/11/2011 1:43:22 PM Justin
- Fixed the bugs where ctn and pcs round up into integer value even it is allow decimal points.

3/15/2012 11:05:32 AM Justin
- Added "/pda" to redirect user back to pda login menu page.

6/14/2012 4:31:34 PM Justin
- Added new function to auto add item when got check "Add item when match one result" from module.

7/25/2012 6:04:34 PM Justin
- Enhanced to have UOM control.

7/27/2012 4:42:34 PM Justin
- Added new function to allow item can scan by GRN barcoder.

8/7/2012 5:57 PM Justin
- Enhanced to accept new grn barcode scanning format.

8/17/2012 10:15 AM Justin
- Enhanced to use parent cost instead of PO cost.

8/29/2012 1:36 PM Andy
- Add privilege checking for DO, GRR, GRN, Adj, Stock Take and Voucher.

9/7/2012 4:04 PM Andy
- Fix module won't auto tick on search ARMS Code if create new GRN or continue last GRN.

9/18/2012 10:10 AM Justin
- Enhanced to recapture selling price and PO cost base on the item UOM fraction.

9/19/2012 10:42 AM Justin
- Bug fixed on system to capture po_qty = 0 instead of po_qty = null.

11/1/2012 5:53 PM Justin
- Enhance when user change ctn, qty or uom, will divide to get the ratio and apply to all sku in same bom package list.
- Enhance when user delete one of the bom package sku, all related sku will be delete at the same time.
- Add a legend [BOM] after sku description.

11/7/2012 5:35 PM Justin
- Bug fixed on selling price calculated wrongly.

12/26/2012 12:01 PM Justin
- Enhanced to memorize the current barcode type.

12/27/2012 11:17 AM Justin
- Bug fixed on capture empty selling price.

2/20/2013 1:48 PM Justin
- Enhanced to copy DO items to GRN items while it matched the conditions.

3/26/2013 5:12 PM Justin
- Bug fixed on system straight die the page with error, causing some PDA hardware cannot run properly.

4/19/2013 2:36 PM Justin
- Bug fixed on system capturing wrong item group for po item matching.

5/9/2013 2:03 PM Justin
- Bug fixed on system capturing wrong po_item_id while found got more than 1 sku item came from same family.

2/9/2015 3:01 PM Andy
- Enhance to capture GST data.

11/25/2015 9:20 AM Qiu Ying
- PDA GRN can search GRR

7/6/2017 2:41 PM Justin
- Enhanced to search actual document type from GRR.

12/17/2019 3.24 PM William
- Fixed grn module not checking block item in grn block list.

1/15/2020 10:35 AM Justin
- Enhanced to insert ID manually for some tables that uses auto increment.

9/10/2020 11:32 AM Andy
- Fixed add grn item doesn't get the correct last grn cost.
*/
include("common.php");
include("class.scan_product.php");
if($config['use_grn_future']) include("../goods_receiving_note2.include.php");
else include("../goods_receiving_note1.include.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/pda/index.php");
if (!privilege('GRN')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRN', BRANCH_CODE), "/pda");

class GRN_Module extends Scan_Product{
    function __construct($title){
        global $sessioninfo, $smarty;
        
        $_SESSION['scan_product']['type'] = 'GRN';
		$_SESSION['scan_product']['name'] = isset($_SESSION['grn']['id']) ? 'GRN#'.$_SESSION['grn']['id'] : '';
		
	    if(isset($_REQUEST['branch_id'])){
			if($_REQUEST['branch_id'] != $sessioninfo['branch_id']){    // prevent edit other branch
				header("Location: $_SERVER[PHP_SELF]");
				exit;
			}
		}
		
		if(!isset($_SESSION['grn']['barcode_type'])) $smarty->assign('is_grn_module', 1);
		parent::__construct($title);
	}
	
    function init_module(){
	    global $con, $smarty;
		// alter any default value, such as $this->scan_templates and $this->result_templates
		//$this->scan_templates = 'abc.tpl';
		//$this->result_templates = 'abc.tpl';

		$smarty->assign('PAGE_TITLE', $this->title);
		$smarty->assign('module_name', $this->title);
		$smarty->assign('top_include','goods_receiving_note.top_include.tpl');
		$smarty->assign('btm_include','goods_receiving_note.btm_include.tpl');
	}
	
	function default_(){
		global $con, $smarty;

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if($id && $branch_id){
			$this->reset_module_session($id,$branch_id);
		}else{
            $id = mi($_SESSION['grn']['id']);
            $branch_id = mi($_SESSION['grn']['branch_id']);
		}

		if($id>0){
			$this->show_scan_product();
		}else{
			$this->show_setting();
		}
	}
	
	function show_scan_product(){
		global $con, $smarty, $config, $sessioninfo, $LANG;

		$id = mi($_SESSION['grn']['id']);
        $branch_id = mi($_SESSION['grn']['branch_id']);
        
		// check GRN exists or not
		$con->sql_query("select *
						 from grn
						 where id = ".mi($id)." and branch_id = ".mi($branch_id)." and active=1");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();

		if(!$form){
            js_redirect('Invalid GRN', "index.php");
            exit;
		}else{
			// get item info
			if($config['use_grn_future']){
				$ttl_nsi_qty = 0;
				$ttl_nsi_count = 0;
				$con->sql_query("select non_sku_items from grn where id = ".mi($id)." and branch_id = ".mi($branch_id));
				$non_sku_items = $con->sql_fetchfield(0);
				$con->sql_freeresult();
				if(unserialize($non_sku_items)){
					$non_si = unserialize($non_sku_items);
					$ttl_nsi_count = count($non_si['code']);
					foreach($non_si['code'] as $row=>$code){
						$ttl_nsi_qty += $non_si['qty'][$row];
					}
				}
				$extra_filter = " and gi.item_group != 0";
			}
			
			if($_SESSION['grn']['find_grn']) $form['find_grn'] = $_SESSION['grn']['find_grn'];
			elseif($_REQUEST['find_grn']) $form['find_grn'] = $_REQUEST['find_grn'];
			
			if($form['find_grn']){
				$form['search_var'] = $form['find_grn'];
				$smarty->assign("form", $form);
			}
		}

		/*$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
		$report_prefix = $con->sql_fetchfield(0);
		$so_no = $report_prefix.sprintf('%05d',$id);*/

		$_SESSION['scan_product']['type'] = 'GRN';
		$_SESSION['scan_product']['name'] = 'GRN#'.mi($id);

		if($_REQUEST['product_code']){
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
				
				//check the Block item in GRN
				$doc_block_list = unserialize($sku_info['doc_block_list']);
				if($doc_block_list['grn'][$sessioninfo['branch_id']]){
					$_SESSION['grn']['err'][] = sprintf($LANG['DOC_ITEM_IS_BLOCKED'],"GRN");
				}else{
					$_REQUEST['pcs'][$sku_info['id']] = 1;
					$this->add_items();
				}
				unset($_REQUEST);
				$smarty->assign("auto_add", 1);
			}
			$con->sql_freeresult($sql);
		}

		$con->sql_query("select count(*) as total_item, sum(gi.ctn) as total_ctn, sum(gi.pcs) as total_pcs
		from grn_items gi where gi.grn_id = ".mi($id)." and gi.branch_id = ".mi($branch_id).$extra_filter);
		
		$grn_items = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$grn_items['total_item'] += $ttl_nsi_count;
		$grn_items['total_pcs'] += $ttl_nsi_qty;
		$smarty->assign('items_details',$grn_items);
		
		if($_SESSION['grn']['err']){
			$this->err = array_merge($this->err, $_SESSION['grn']['err']);
			unset($_SESSION['grn']['err']);
		}
		
		$smarty->assign('err',$this->err);

		$this->search_product();
	}

	function new_grn(){
		unset($_SESSION['grn']);
		header("Location: $_SERVER[PHP_SELF]");
	}
	
	function show_setting(){
		global $con, $smarty, $config;

		//$this->default_load();

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$grr_id = mi($_SESSION['grn']['grr_id']);
		$grr_item_id = mi($_SESSION['grn']['grr_item_id']);
		//$find_grr = mi();
		
		if(!$id || !$branch_id){
            $id = mi($_SESSION['grn']['id']);
            $branch_id = mi($_SESSION['grn']['branch_id']);
		}


		if($id>0 && $branch_id>0){
			if($_SESSION['grn']['find_grn']) $find_grn = $_SESSION['grn']['find_grn'];
			elseif($_REQUEST['find_grn']) $find_grn = $_REQUEST['find_grn'];
		    $this->reset_module_session($id, $branch_id);
			$con->sql_query("select * from grn where id = ".$id." and branch_id = ".$branch_id);
			$form = $con->sql_fetchrow();
			$con->sql_freeresult();
			$form['find_grn'] = $find_grn;
			if($config['use_grn_future']) $grr = load_grr_item_header($grr_id, $branch_id);
			else $grr = load_grr_item_header($grr_item_id, $branch_id);

		}else{ // means it is create from GRR
			if($_SESSION['grn']['find_grr']) $form['find_grr'] = $_SESSION['grn']['find_grr'];
			elseif($_REQUEST['find_grr']) $form['find_grr'] = $_REQUEST['find_grr'];
			if($config['use_grn_future']) $grr = load_grr_item_header($grr_id, $branch_id);
			else $grr = load_grr_item_header($grr_item_id, $branch_id);
			$form['branch_id']=$branch_id;
			$form['vendor_id']=$grr['vendor_id'];
			$form['grr_id']=$grr['grr_id'];
			$form['grr_item_id']=$grr['grr_item_id'];
			$form['department_id']=$grr['department_id'];
		}

		$smarty->assign('form', $form);
		$smarty->assign('grr', $grr);
		$smarty->assign('grn_tab', 'setting');
		$smarty->display('goods_receiving_note.index.tpl');
	}

	function show_grr_list(){
		global $con, $smarty, $sessioninfo, $config;

		/*$this->default_load();

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(!$id || !$branch_id){
            $id = mi($_SESSION['grn']['id']);
            $branch_id = mi($_SESSION['grn']['branch_id']);
		}*/
		
		if ($_REQUEST['find_grr'] != ''){
			// strip "grr#####" prefix
			if (preg_match("/^grr/i", $_REQUEST['find_grr'])){
				$grrid = intval(substr($_REQUEST['find_grr'],3));
				$findstr = "and grr.id = $grrid";
			}
			else{
				// search documents
				$con->sql_query("select distinct(grr_id) from grr_items where branch_id=".mi($sessioninfo['branch_id'])." and doc_no like ".ms("%".$_REQUEST['find_grr']."%")." or grr_id = ".mi($_REQUEST['find_grr']));

				// return if no match
				if (!$con->sql_numrows()){
					$no_items = true;
					$err[] = "No GRR Found with ".$_REQUEST['find_grr'];
				}
				$idlist = array();
				while($r=$con->sql_fetchrow()){
					$idlist[] = $r[0];
				}
				$con->sql_freeresult();
				$findstr = "and grr.id in (".join(",",$idlist).")";
			}
		}else $status_filter = "and grr_items.grn_used = 0";

		if(!$no_items){
			// show current grr
			$con->sql_query("select grr.*, grr_items.*, grr.id as id, grr_items.id as grr_item_id,
							 grr.rcv_date, vendor.description as vendor, user.u, 
							 user2.u as rcv_u, category.description as department
							 from grr_items
							 left join grr on (grr_id = grr.id and grr_items.branch_id = grr.branch_id)
							 left join user on grr.user_id = user.id
							 left join user user2 on grr.rcv_by = user2.id
							 left join vendor on grr.vendor_id = vendor.id
							 left join category on grr.department_id = category.id
							 where grr.active=1 and grr.branch_id=".mi($sessioninfo['branch_id'])." $findstr $status_filter
							 order by grr.rcv_date desc, grr_items.id
							 limit 100") or die(mysql_error());
			
			if($con->sql_numrows()==0) $no_items = true;
			else $grr_list = $con->sql_fetchrowset();
			$con->sql_freeresult();
		}

		$smarty->assign('err', $err);
		$smarty->assign('grr_list', $grr_list);
		$smarty->display('goods_receiving_note.grr_list.tpl');
	}
	
	private function default_load(){
		global $con,$smarty;

		/*/ all branches
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
		}*/
		$con->sql_query("select id, code, fraction from uom where active=1 order by code");
		$smarty->assign("uom", $con->sql_fetchrowset());
	}
	
	function save_setting(){
		global $con, $smarty, $sessioninfo, $config, $appCore;

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$grr_id = mi($_REQUEST['grr_id']);
		$grr_item_id = mi($_REQUEST['grr_item_id']);

		if(!$grr_id || !$branch_id) $err['top'][] = "Invalid GRR";

		if($err){
			$smarty->assign('form',$_REQUEST);
			$smarty->assign('err',$err);
			$smarty->display('goods_receiving_note.index.tpl');
			exit;
		}

		//print_r($upd);exit;
		// GRN needs check again for GST status
		if($config['enable_gst']){
			$prms = array();
			$prms['vendor_id'] = $_REQUEST['vendor_id'];
			$prms['date'] = $_REQUEST['rcv_date'];
			$is_under_gst = check_gst_status($prms);
		}
		
		if($id){ // is existing GRN
			$grn_upd = array();
			$grn_upd['grn_tax'] = $_REQUEST['grn_tax'];
			$grn_upd['last_update'] = "CURRENT_TIMESTAMP";
			if(isset($is_under_gst))	$grn_upd['is_under_gst'] = $is_under_gst;
			$con->sql_query("update grn set ".mysql_update_by_field($grn_upd)." where id=".mi($id)." and branch_id=".mi($branch_id)) or die(mysql_error());
			
			// update total selling, amount and variance.
			// $this->recalc_grn_value($grn_id, $branch_id);
			update_total_selling($id, $branch_id); // update total selling
			update_total_amount($id, $branch_id); // update total amount
			update_total_variance($id, $branch_id); // update have variance
		}else{ // is new GRN
			$grn_ins = array();
			if(isset($is_under_gst))	$grn_ins['is_under_gst'] = $is_under_gst;
			
			// call appCore to generate new ID
			unset($new_id);
			$new_id = $appCore->generateNewID("grn", "branch_id = ".mi($branch_id));
			
			if(!$new_id) die("Unable to generate new ID from appCore!");
			
			$grn_ins['id'] = mi($new_id);
			$grn_ins['branch_id'] = mi($branch_id);
			$grn_ins['grr_id'] = mi($grr_id);
			$grn_ins['grr_item_id'] = mi($grr_item_id);
			$grn_ins['user_id'] = mi($sessioninfo['id']);
			$grn_ins['vendor_id'] = mi($_REQUEST['vendor_id']);
			$grn_ins['department_id'] = mi($_REQUEST['department_id']);
			$grn_ins['added'] = "CURRENT_TIMESTAMP";
			$grn_ins['grn_tax'] = mf($_REQUEST['grn_tax']);
			if($config['use_grn_future']) $grn_ins['is_future'] = 1;
			else $grn_ins['authorized'] = 1;
			
		    $con->sql_query("insert into grn ".mysql_insert_by_field($grn_ins));
		    $id = $con->sql_nextid();
		    //$form['id']=$con->sql_nextid();

			if(!$config['use_grn_future']) $grr_filter = " and gi.id = ".mi($grr_item_id);
			
			//$con->sql_query("update grr set status=1 where branch_id=$branch_id and id=".mi($form['grr_id']));
			$con->sql_query("update grr_items gi left join grr on grr.id = gi.grr_id and grr.branch_id = gi.branch_id set gi.grn_used=1, grr.status=1 where gi.grr_id = ".mi($grr_id)." and gi.branch_id = ".mi($branch_id).$grr_filter);

			// copy items from po
			$con->sql_query("select doc_no from grr_items gi where gi.type = 'PO' and gi.grr_id = ".mi($grr_id)." and gi.branch_id = ".mi($branch_id).$grr_filter);
			
			if($config['use_grn_future']){
				while($r = $con->sql_fetchrow()){
					$grp_doc_no[] = ms($r['doc_no']);
				}
			}else{
				$gi_info = $con->sql_fetchrow();
				$grp_doc_no[] = $gi_info['doc_no'];
			}
			$con->sql_freeresult();
			
			if(count($grp_doc_no) == 0 && $config['use_grn_future'] && ($config['do_skip_generate_grn'] || $sessioninfo['branch_type'] == "franchise")){
				$is_from_do = false;
				$q1 = $con->sql_query("select doc_no from grr_items gi where gi.type = 'DO' and gi.grr_id = ".mi($grr_id)." and gi.branch_id = ".mi($branch_id));
				
				while($r = $con->sql_fetchassoc($q1)){
					if($sessioninfo['branch_type'] == "franchise") $filter = "debtor_id = ".mi($sessioninfo['debtor_id'])." and do_type = 'credit_sales'";
					else $filter = "do_branch_id = ".mi($branch_id)." and do_type = 'transfer'";
					$q2 = $con->sql_query("select *, id as do_id from do where do_no = ".ms($r['doc_no'])." and ".$filter);
					if($con->sql_numrows($q2) > 0){  // means is IBT DO
						$grr_do = $con->sql_fetchrow($q2);
						$grr = array_merge($r, $grr_do);
						if($grr_do['do_no']) $grp_doc_no[] = ms($grr_do['do_no']);
						$is_from_do = true;
					}
					$con->sql_freeresult($q2);
				}
				$con->sql_freeresult($q1);
			}
			
			$doc_no = join(",", $grp_doc_no);
			if($doc_no){
				if($is_from_do){
					copy_do_items($doc_no, $id, $branch_id, false);
				}else{
					copy_po_items($doc_no, $id, $branch_id, false);
				}
			}
		}

		$this->reset_module_session($id, $branch_id);
		header("Location: $_SERVER[PHP_SELF]");
	}

	private function reset_module_session($id, $branch_id){
	    global $con, $config, $sessioninfo;

	    $con->sql_query("select id, grr_id, grr_item_id, branch_id, vendor_id from grn where id = ".mi($id)." and branch_id = ".mi($branch_id));
		$form = $con->sql_fetchrow();
		$con->sql_freeresult();
		if(!$form)	js_redirect('Invalid GRN', "index.php");

		if($config['use_grn_future']){
			$q1 = $con->sql_query("select gi.type, gi.doc_no 
								   from grr_items gi
								   where gi.grr_id = ".mi($form['grr_id'])." and gi.branch_id = ".mi($form['branch_id']));

			$is_from_do = false;
			while($r = $con->sql_fetchassoc($q1)){
				if(!$grp_doc[$r['type']][$r['doc_no']]){
					$grp_doc[$r['type']][$r['doc_no']] = $r['doc_no'];
				}
				
				if($r['type']=='DO' && $r['doc_no']!='' && !$is_from_do){
					if($r['type'] == "DO" && ($config['do_skip_generate_grn'] || $sessioninfo['branch_type'] == "franchise")){
						if($sessioninfo['branch_type'] == "franchise") $filter = "debtor_id = ".mi($sessioninfo['debtor_id'])." and do_type = 'credit_sales'";
						else $filter = "do_branch_id = ".mi($form['branch_id'])." and do_type = 'transfer'";
						$q3 = $con->sql_query("select *, id as do_id from do where do_no = ".ms($r['doc_no'])." and ".$filter);
						if($con->sql_numrows($q3) > 0){  // means it is IBT DO
							while($grr_do = $con->sql_fetchassoc($q3)){
								if($grr_do['do_no']) $grp_do_no[] = ms($grr_do['do_no']);
								$is_from_do = true;
							}
						}
						$con->sql_freeresult($q3);
					}
				}
			}
			$con->sql_freeresult($q1);

			if($is_from_do){
				$doc_type = "DO";
				//$grr['doc_no'] = join(", ", $grp_doc['DO']);
				//$grr['is_ibt_do'] = true;
				//if($grp_doc['INVOICE']) $grr['invoice_no'] = join(", ", $grp_doc['INVOICE']);
			}elseif($grp_doc['PO']){
				$doc_type = "PO";
				//$grr['doc_no'] = join(", ", $grp_doc['PO']);
				//if($grp_doc['INVOICE']) $grr['invoice_no'] = join(", ", $grp_doc['INVOICE']);
			}elseif($grp_doc['INVOICE']){
				$doc_type = "INVOICE";
				//$grr['doc_no'] = join(", ", $grp_doc['INVOICE']);
			}elseif($grp_doc['DO']){
				$doc_type = "DO";
				//$grr['doc_no'] = join(", ", $grp_doc['DO']);
			}else{
				$doc_type = "OTHER";
				//$grr['doc_no'] = join(", ", $grp_doc['OTHER']);
			}

			$form['type'] = $doc_type;
		}

        $_SESSION['grn'] = $form;
	}
	
	function view_items(){
		global $con, $smarty, $config;
		$id = mi($_SESSION['grn']['id']);
        $branch_id = mi($_SESSION['grn']['branch_id']);
		
        if(!$id || !$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}

		if($config['use_grn_future']){
			$con->sql_query("select non_sku_items from grn where id = ".mi($id)." and branch_id = ".mi($branch_id));
			$non_sku_items = $con->sql_fetchfield(0);

			if(unserialize($non_sku_items)) $smarty->assign("non_sku_items", unserialize($non_sku_items));
			$extra_filter = " and gi.item_group != 0";
		}

		// load item list
        $con->sql_query("select gi.*, si.sku_item_code, si.description as sku_description, uom.code as uom_code, uom.fraction as uom_fraction, si.doc_allow_decimal, pkuom.fraction as master_uom_fraction
						 from grn_items gi
						 left join sku_items si on si.id = gi.sku_item_id
						 left join uom on uom.id = gi.uom_id
						 left join uom pkuom on pkuom.id = si.packing_uom_id
						 where gi.grn_id = ".mi($id)." and gi.branch_id = ".mi($branch_id).$extra_filter."
						 order by gi.id");

		$items = $con->sql_fetchrowset();
		$con->sql_freeresult();

		$this->default_load();
		
        $smarty->assign('items',$items);
		$smarty->assign('grn_tab', 'view_items');
		$smarty->display('goods_receiving_note.view_items.tpl');
	}
	
	function add_items(){
		global $con,$smarty,$config,$sessioninfo,$LANG,$appCore;

		//print_r($_REQUEST);exit;
	    $id = $_SESSION['grn']['id'];
        $branch_id = $_SESSION['grn']['branch_id'];

		$items = $_REQUEST['pcs'];
		$sku_items = $invalid_bom = array();

		if($items || $_REQUEST['is_isi']){
		    $total_ctn = 0;
		    $total_pcs = 0;
			foreach($items as $sid=>$pcs){
				if($_REQUEST['ctn'][$sid]>0 || $pcs>0 || ($pcs==0 && $_REQUEST['empty_decimal_points'])){
					$sku_items[$sid]['ctn'] = round($_REQUEST['ctn'][$sid], $config['global_qty_decimal_points']);
					$sku_items[$sid]['pcs'] = round($pcs, $config['global_qty_decimal_points']);
					$sku_items[$sid]['bom_ref_num'] = $_REQUEST['bom_ref_num'][$sid];
					$sku_items[$sid]['bom_qty_ratio'] = $_REQUEST['bom_qty_ratio'][$sid];
					$total_ctn += $_REQUEST['ctn'][$sid];
					$total_pcs += $_REQUEST['pcs'][$sid];
				}
			}

			if($_REQUEST['isi_pcs']) $total_pcs += $_REQUEST['isi_pcs'];
			
			if($total_ctn<0 && $total_pcs <=0  && !$_REQUEST['empty_decimal_points']){
                $ret['error'][] = "Invalid quantity";
			}elseif($_REQUEST['is_isi']){
				
				$con->sql_query("select non_sku_items from grn where id = ".mi($id)." and branch_id = ".mi($branch_id));
				$non_sku_items = $con->sql_fetchfield(0);
				$con->sql_freeresult();

				if(unserialize($non_sku_items)) $non_si = unserialize($non_sku_items);

				$non_si['code'][] = $_REQUEST['product_code'];
				$non_si['description'][] = $_REQUEST['isi_desc'];
				$non_si['qty'][] = round($_REQUEST['isi_pcs'], $config['global_qty_decimal_points']);
				$non_si['cost'][] = 0;
				$non_si['i_c'][] = 0;
			
				if(count($non_si) > 0){
					$non_si = serialize($non_si);
					$con->sql_query("update grn set non_sku_items = ".ms($non_si)." where id = ".mi($id)." and branch_id = ".mi($branch_id));
				}
				
				$ret['success'] = true;
			}else{
				$con->sql_query("select * from grn where id = ".mi($id)." and branch_id = ".mi($branch_id));
				$grn = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				foreach($sku_items as $sid=>$dummy){
					$item['item_group'] = 0;
					$item['selling_uom_id'] = 1;

					// load current sku item info
					$con->sql_query("select sku_items.sku_id, uom.id, uom.fraction as sku_fraction, sku_item_code
									 from sku_items 
									 left join uom on uom.id = sku_items.packing_uom_id 
									 where sku_items.id = ".mi($sid));
					$sku_id = $con->sql_fetchfield(0);
					$uom_id = $con->sql_fetchfield(1);
					$sku_uom = $con->sql_fetchfield(2);
					$sku_item_code = $con->sql_fetchfield(3);
					
					
					if(!$config['use_grn_future']){ // if it is old version
						$con->sql_query("update grn_items 
										 set ctn = ctn+".mf($sku_items[$sid]['ctn']).", pcs = pcs+".mf($sku_items[$sid]['pcs'])."
										 where grn_id = ".mi($id)." and branch_id = ".mi($branch_id)." 
										 and sku_item_id = ".mi($sid)." and po_item_id != 0 and item_group = 0");

						if($con->sql_affectedrows() > 0) continue; // skip with no insert new item
						else{
							$q1 = $con->sql_query("select si.id as sku_item_id,
												   ifnull(si.artno,si.mcode) as artno_mcode,
												   ifnull(sic.grn_cost,si.cost_price) as cost, 
												   ifnull(sip.price,si.selling_price) as selling_price,
												   si.packing_uom_id as uom_id,
												   uom.fraction as uom_fraction
												   from sku_items si
												   left join sku on sku_id = sku.id
												   left join sku_items_cost sic on sic.branch_id = ".mi($branch_id)." and sic.sku_item_id=si.id
												   left join sku_items_price sip on sip.branch_id = ".mi($branch_id)." and sip.sku_item_id=si.id
												   left join uom on uom.id = si.packing_uom_id
												   where si.id = ".mi($sid));

							$tmp_item = $con->sql_fetchrow($q1);
							$item = array_merge($tmp_item, $item);
						}
					}else{
						//if($_SESSION['grn']['type'] == 'PO' && !$this->existed_gi_info[$sid]){

						// load PO item to see whether it is existed
						$q2 = $con->sql_query("select gi.*, uom.id as uom_id, if(puom.fraction=1,uom.fraction,puom.fraction) as uom_fraction, gi.cost, si.sku_id, puom.fraction as mst_uom_fraction
											 from grn_items gi
											 left join sku_items si on si.id = gi.sku_item_id
											 left join sku on sku.id = si.sku_id
											 left join uom on uom.id = gi.uom_id
											 left join uom puom on puom.id = si.packing_uom_id
											 where gi.branch_id = ".mi($branch_id)."
											 and gi.grn_id = ".mi($id)."
											 and (gi.item_group = 0 or gi.item_group = 1)
											 and gi.po_item_id != 0
											 and sku.id = ".mi($sku_id)."
											 order by gi.id");
						
						while($r=$con->sql_fetchassoc($q2)){
							if($r['sku_item_id'] == $sid && $r['item_group'] <= 1){
								$item['item_group'] = 1; // it is matched with PO
								$item['cost'] = $r['cost'];
								$item['selling_price'] = $r['selling_price'];
								$item['po_cost'] = $r['po_cost'];
								$item['uom_fraction'] = $r['uom_fraction'];
								$item['uom_id'] = $r['uom_id'];
								$item['artno_mcode'] = $r['artno_mcode'];
								$item['selling_uom_id'] = $r['selling_uom_id'];
								$item['po_item_id'] = $r['po_item_id'];
								$item['po_qty'] = 0;
							}elseif($r['sku_item_id'] != $sid && $item['item_group'] != 1){
								$item['item_group'] = 2; // it is under item's SKU child
								$item['cost'] = round($r['cost']*($sku_uom/$r['uom_fraction']), $config['global_cost_decimal_points']);
								if($r['mst_uom_fraction'] == 1 && $r['cost'] > $r['selling_price']){
									$selling_uom_fraction = $r['mst_uom_fraction'];
								}else{
									$selling_uom_fraction = $r['uom_fraction'];
								}
								
								$item['selling_price'] = $r['selling_price'] * ($sku_uom / $selling_uom_fraction);
								$item['po_cost'] = round($r['po_cost'] * ($sku_uom / $r['uom_fraction']), $config['global_cost_decimal_points']);
								$item['uom_fraction'] = $sku_uom;
								$item['uom_id'] = $uom_id;
								$item['artno_mcode'] = $r['artno_mcode'];
								$item['selling_uom_id'] = $r['selling_uom_id'];
								$item['po_item_id'] = $r['po_item_id'];
								$item['po_qty'] = 0;
							}
						}

						$con->sql_freeresult($q2);
						//}
						
						if($invalid_bom[$sku_items[$sid]['bom_ref_num']]) continue;
						else{
							$q3 = $con->sql_query("select * from grn_items where grn_id = ".mi($id)." and branch_id = ".mi($branch_id)." and item_group >= 3 and sku_item_id = ".mi($sid));

							if($con->sql_numrows($q3) > 0){ // found already got add the following bom package
								$grn_error = false;
								while($r = $con->sql_fetchassoc($q3)){
									if($r['bom_ref_num'] > 0 && $r['bom_ref_num'] == $sku_items[$sid]['bom_ref_num']){ // BOM already existed in this GRN
										$tmp = $con->sql_query("select sku_item_code from sku_items where id = ".mi($sku_items[$sid]['bom_ref_num']));
										$tmp_info = $con->sql_fetchrow($tmp);
										$con->sql_freeresult($tmp);
										$this->err[] = sprintf($LANG['GRN_BOM_PACKAGE_EXISTED'], $tmp_info['sku_item_code']);
										$grn_error = true;
										$invalid_bom[$sku_items[$sid]['bom_ref_num']] = true;
										continue;
									}elseif($r['bom_ref_num'] > 0){ // existed with other items
										$this->err[] = sprintf($LANG['GRN_BOM_ITEM_EXISTED'], $sku_item_code);
										$grn_error = true;
										$invalid_bom[$sku_items[$sid]['bom_ref_num']] = true;
										continue;
									}
								}

								$con->sql_freeresult($q3);
								if($grn_error) continue;
							}
						}
						
						if(!$item['item_group']) $item['item_group'] = 3;
						
						if($item['item_group'] == 3){ // if found it is item not in PO/received item
							// if found have this item before, just do update
							if($this->existed_gi_info[$sid]['id']){
								$filter = "id = ".mi($this->existed_gi_info[$sid]['id']);
							}else{
								$filter = "sku_item_id = ".mi($sid)." and item_group = ".mi($item['item_group']);
							}
							
							$con->sql_query("update grn_items 
											 set ctn = ctn+".mf($sku_items[$sid]['ctn']).", pcs = pcs+".mf($sku_items[$sid]['pcs'])."
											 where grn_id = ".mi($id)." and branch_id = ".mi($branch_id)." 
											 and $filter
											 limit 1");

							// if found it is existed and updated, stop insert new item
							if($con->sql_affectedrows() > 0) continue;
							else{ // get info and insert
								unset($item);
								
								$_REQUEST['rcv_date'] = $_SESSION['grn']['rcv_date'];
								$_REQUEST['vendor_id'] = $_SESSION['grn']['vendor_id'];
								$item = get_items_details(intval($sid), '');
								$item['item_group'] = 3;
							}
						}
					}
					
								// call appCore to generate new ID
					unset($new_id);
					$new_id = $appCore->generateNewID("grn_items", "branch_id = ".mi($branch_id));
					
					if(!$new_id) die("Unable to generate new ID from appCore!");

				    $item['id'] = $new_id;
				    $item['branch_id'] = $branch_id;
				    $item['grn_id'] = $id;
					$item['sku_item_id'] = $sid;
					$item['bom_ref_num'] = $sku_items[$sid]['bom_ref_num'];
					$item['bom_qty_ratio'] = $sku_items[$sid]['bom_qty_ratio'];
					
					if(isset($_REQUEST['item_cp'][$sid])){
						if(!$_REQUEST['item_cp'][$sid]){
							$this->err[] = sprintf($LANG['GRN_SCALE_ITEM_INVALID_TD'], $sku_item_code);
						}
						$item['cost'] = $_REQUEST['item_cp'][$sid];
					}
					
					if($_REQUEST['item_sp'][$sid]) $item['selling_price'] = $_REQUEST['item_sp'][$sid];
					
					if(!$config['doc_allow_edit_uom'] && $sku_uom > 1){
						$item['uom_id'] = 1;
						$item['uom_fraction'] = 1;
					}
					
					$item['pcs'] = $sku_items[$sid]['pcs'];
					if($item['uom_fraction'] == 1){
						$item['pcs'] += $sku_items[$sid]['ctn'];
					}else $item['ctn'] = $sku_items[$sid]['ctn'];
					
					// find price before gst
					if($config['enable_gst']){
						// get sku is inclusive
						$is_sku_inclusive = get_sku_gst("inclusive_tax", $item['sku_item_id']);
						// get sku original output gst
						$sku_original_output_gst = get_sku_gst("output_tax", $item['sku_item_id']);
						
						if($is_sku_inclusive == 'yes'){
							// is inclusive tax
							$item['gst_selling_price'] = $item['selling_price'];
							
							// find the price before tax
							$sp = $item['selling_price'];
							$gst_tax_price = round($sp / ($sku_original_output_gst['rate']+100) * $sku_original_output_gst['rate'], 2);
							$price_included_gst = $sp;
							$sp = $price_included_gst - $gst_tax_price;
							$item['selling_price'] = $sp;
						}else{
							// is exclusive tax
							$gst_amt = round($item['selling_price'] * $sku_original_output_gst['rate'] / 100, 2);
							$item['gst_selling_price'] = round($item['selling_price'] + $gst_amt, 2);
						}
						
						if($grn['is_under_gst']){
							// get gst output tax
							$output_tax = get_sku_gst("output_tax", $item['sku_item_id']);
							if($output_tax){
								$item['selling_gst_id'] = $output_tax['id'];
								$item['selling_gst_code'] = $output_tax['code'];
								$item['selling_gst_rate'] = $output_tax['rate'];
							}
							
							// input tax
							$input_tax = get_sku_gst("input_tax", $item['sku_item_id']);
							if($input_tax){
								$item['gst_id'] = $input_tax['id'];
								$item['gst_code'] = $input_tax['code'];
								$item['gst_rate'] = $input_tax['rate'];
							}
						}
					}
					
					$con->sql_query("insert into grn_items ".mysql_insert_by_field($item, array('id','branch_id','grn_id','sku_item_id','artno_mcode','cost','selling_price','uom_id','selling_uom_id','ctn','pcs','po_cost',
					'po_item_id','item_group','bom_ref_num','bom_qty_ratio',
					'gst_selling_price','selling_gst_id','selling_gst_code','selling_gst_rate','gst_id','gst_code','gst_rate')));
				}
				$con->sql_freeresult($q1);

				update_total_selling($id, $branch_id); // update total selling
				update_total_amount($id, $branch_id); // update total amount
				update_total_variance($id, $branch_id); // update have variance

				$ret['success'] = true;
			}
		}else{
            $ret['error'][] = "No items found";
		}

		return $ret;
	}
	
	function save_items(){
        global $con, $smarty;
		$id = $_SESSION['grn']['id'];
        $branch_id = $_SESSION['grn']['branch_id'];

        if(!$id || !$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}

        if($_REQUEST['uom']){ // from valid items
			foreach($_REQUEST['uom'] as $gi_id=>$uom){
				$ctn = $_REQUEST['ctn'][$gi_id];
				$pcs = $_REQUEST['pcs'][$gi_id];
			    /*if(!$_REQUEST['pcs'][$gi_id] && !$_REQUEST['pcs'][$gi_id]){  // remove item
					$con->sql_query("delete from sales_order_items where branch_id=$branch_id and sales_order_id=$id and id=".mi($gi_id));
				}else{*/
				$con->sql_query("update grn_items set uom_id = ".mi($uom).", ctn=".mf($ctn).", pcs=".mf($pcs)." where branch_id = ".mi($branch_id)." and grn_id = ".mi($id)." and id=".mi($gi_id));
				//}
			}
			
			update_total_selling($id, $branch_id); // update total selling
			update_total_amount($id, $branch_id); // update total amount
			update_total_variance($id, $branch_id); // update have variance
		}
		
		if($_REQUEST['isi_code']){ // from invalid items
			foreach($_REQUEST['isi_code'] as $row=>$code){
				$nsi['code'][] = $code;
				$nsi['description'][] = $_REQUEST['isi_desc'][$row];
				$nsi['qty'][] = $_REQUEST['isi_qty'][$row];
				$nsi['cost'][] = 0;
				$nsi['i_c'][] = 0;
			}

			if(count($nsi) > 0){
				$non_si = serialize($nsi);
				$con->sql_query("update grn set non_sku_items = ".ms($non_si)." where id = ".mi($id)." and branch_id = ".mi($branch_id));
			}
		}

		header("Location: $_SERVER[PHP_SELF]?a=view_items&find_grn=".$_REQUEST['find_grn']);
	}
	
	function delete_items(){
		global $con, $smarty;
		$id = $_SESSION['grn']['id'];
        $branch_id = $_SESSION['grn']['branch_id'];

        if(!$id || !$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}

		if($_REQUEST['item_chx']){
            $con->sql_query("delete from grn_items
			where grn_id = ".mi($id)." and branch_id = ".mi($branch_id)." and id in (".join(',',array_keys($_REQUEST['item_chx'])).")");

			update_total_selling($id, $branch_id); // update total selling
			update_total_amount($id, $branch_id); // update total amount
			update_total_variance($id, $branch_id); // update have variance
		}

		if($_REQUEST['isi_item_chx']){
			foreach($_REQUEST['isi_code'] as $row=>$code){
				if($_REQUEST['isi_item_chx'][$row]) continue;
				$nsi['code'][] = $code;
				$nsi['description'][] = $_REQUEST['isi_desc'][$row];
				$nsi['qty'][] = $_REQUEST['isi_qty'][$row];
				$nsi['cost'][] = 0;
				$nsi['i_c'][] = 0;
			}

			if(count($nsi) > 0) $non_si = serialize($nsi);
			$con->sql_query("update grn set non_sku_items = ".ms($non_si)." where id = ".mi($id)." and branch_id = ".mi($branch_id));
		}
		
		header("Location: $_SERVER[PHP_SELF]?a=view_items");
	}
	
	function open(){
		global $con, $smarty, $sessioninfo, $config;

		if(isset($_REQUEST['find_grn'])){
			$branch_id = $sessioninfo['branch_id'];
			$str = $_REQUEST['find_grn'];
			$filter = array();

			if($sessioninfo['level']<9999) $filter[] = "grn.user_id = ".mi($sessioninfo['id']);

			if($config['use_grn_future']){
				$filter[] = "(grn.id=".ms(preg_replace("/[^0-9]/","", $str))." or (select gri.id from grr_items gri where gri.doc_no like ".ms("%".$str."%")." and gri.grr_id = grn.grr_id and gri.branch_id = grn.branch_id group by gri.grr_id) or grn.grr_id=".ms(preg_replace("/[^0-9]/","", $str)).")";
			}else{
				$filter[] = "(grn.id = ".mi($str)." or grr_items.doc_no like ".ms("%$str%")." or grn.grr_id = ".mi($str).")";
			}

			$filter[] = "grn.user_id = ".mi($sessioninfo['id']);
			
			$sql = "select grn.*,vendor.code as vendor_code, vendor.description as vendor_desc
					from grn
					left join grr_items on grn.grr_item_id = grr_items.id and grn.branch_id = grr_items.branch_id
					left join vendor on vendor.id=grn.vendor_id
					where grn.branch_id = ".mi($branch_id)." and grn.active=1 and grn.authorized=0
					and grn.approved=0 and grn.status=0 and ".join(" and ", $filter);

			$con->sql_query($sql);
			if($con->sql_numrows()<=0){
				$err[] = "No GRN Found with $str.";
			}else{
				while($r = $con->sql_fetchrow()){
					$grn_list[] = $r;
				}
				$smarty->assign('grn_list',$grn_list);
			}
			$con->sql_freeresult();
		}
		$smarty->assign('err',$err);
		$smarty->display('goods_receiving_note.search.tpl');
	}
	
	function change_grn(){
	    global $con, $smarty;

        $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(!$id || !$branch_id){
           js_redirect('Invalid GRN', "index.php");
           exit;
		}else{
			$this->reset_module_session($id, $branch_id);
			if($_REQUEST['find_grn']) $_SESSION['grn']['find_grn'] = $_REQUEST['find_grn'];
		}

		header("Location: $_SERVER[PHP_SELF]");
	}

	function change_grr(){
	    global $con, $smarty;

        $grr_id = mi($_REQUEST['grr_id']);
        $grr_item_id = mi($_REQUEST['grr_item_id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(!$grr_id || !$grr_item_id || !$branch_id){
           js_redirect('Invalid GRR', "index.php");
           exit;
		}else{
			unset($_SESSION['grn']);
			$_SESSION['grn']['grr_id'] = $grr_id;
			$_SESSION['grn']['grr_item_id'] = $grr_item_id;
			$_SESSION['grn']['branch_id'] = $branch_id;
			if($_REQUEST['find_grr']) $_SESSION['grn']['find_grr'] = $_REQUEST['find_grr'];
		}

		header("Location: $_SERVER[PHP_SELF]");
	}
	
	function add_item_by_grn_barcode(){
		global $con, $smarty, $config, $LANG;

		$code = trim($_REQUEST['product_code']);
		$si_info=get_grn_barcode_info($code,false);
	
		if ($si_info['sku_item_id']){
			$sku_item_id = $si_info['sku_item_id'];
			$pcs = mf($si_info['qty_pcs']);
			$sku_info_arr[$sku_item_id]['selling_price'] = $selling_price = mf($si_info['selling_price']);
			if(isset($si_info['new_cost_price'])) $sku_info_arr[$sku_item_id]['cost_price'] = $cost_price = $si_info['new_cost_price'];
			
			$this->existed_gi_info[$sku_item_id] = $this->is_item_existed($sku_item_id, $sku_info_arr[$sku_item_id]);
		}
		
		if($si_info && !$si_info['err']){
			if(ceil($pcs) != $pcs && !$si_info['doc_allow_decimal']){
				$_REQUEST['pcs'][$sku_item_id] = 0;
				$_REQUEST['empty_decimal_points'] = true;
				$this->err[] = sprintf($LANG['GRN_SCALE_ITEM_INVALID_DP'], $si_info['sku_item_code']);
			}else $_REQUEST['pcs'][$sku_item_id] = $pcs;
			$_REQUEST['item_sp'][$sku_item_id] = $selling_price;
			if(isset($cost_price)) $_REQUEST['item_cp'][$sku_item_id] = $cost_price;
			$this->add_items();

			if($config['use_grn_future']) $extra_filter = " and gi.item_group != 0";;
			
			// get item info
			$con->sql_query("select count(*) as total_item, sum(gi.ctn) as total_ctn, sum(gi.pcs) as total_pcs from grn_items gi where gi.grn_id = ".mi($_SESSION['grn']['id'])." and gi.branch_id = ".mi($_SESSION['grn']['branch_id']).$extra_filter);
			
			$grn = $con->sql_fetchrow();
			$con->sql_freeresult();
			
			if($config['use_grn_future']){
				$con->sql_query("select non_sku_items from grn where id = ".mi($_SESSION['grn']['id'])." and branch_id = ".mi($_SESSION['grn']['branch_id']));
				$non_sku_items = $con->sql_fetchfield(0);
				$con->sql_freeresult();
				if(unserialize($non_sku_items)){
					$non_si = unserialize($non_sku_items);
					$grn['total_item'] += count($non_si['code']);
					foreach($non_si['code'] as $row=>$code){
						$grn['total_pcs'] += $non_si['qty'][$row];
					}
				}
			}

			$smarty->assign('items_details', $grn);
		}elseif($code) $this->err[] = "The item (".$code.") not found!";
	}
	
	function is_item_existed($sid, $si_info){
		global $con;
		
		$form = $_REQUEST;
		
		$filter = array();
		if($si_info['selling_price']) $filter[] = "tgi.selling_price = ".mf($si_info['selling_price']);
		if(isset($si_info['cost_price'])) $filter[] = "tgi.cost = ".mf($si_info['cost_price']);
		
		if($filter) $filters = " and ".join(" and ", $filter);
		
		$q1 = $con->sql_query("select tgi.id, si.sku_item_code, tgi.item_group
							   from grn_items tgi 
							   left join sku_items si on si.id = tgi.sku_item_id
							   where tgi.sku_item_id = ".mi($sid)."
							   and tgi.grn_id = ".mi($_SESSION['grn']['id'])." and tgi.branch_id = ".mi($_SESSION['grn']['branch_id'])." and tgi.item_group >= 3".$filters);
							   
		if($con->sql_numrows($q1) > 0){
			$gi = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
		}elseif($filters){
			$gi['id'] = -1;
		}
		return $gi;
	}
}

$GRN_Module = new GRN_Module('GRN');
?>
