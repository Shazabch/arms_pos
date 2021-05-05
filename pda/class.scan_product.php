<?php
/*
6/24/2011 5:06:26 PM Andy
- Make all branch default sort by sequence, code.

7/26/2011 1:06:26 PM Justin
- Modified to syncronize with GRN future while adding items.

7/27/2012 4:42:34 PM Justin
- Added new function to allow item can scan by GRN barcoder.

11/1/2012 5:53 PM Justin
- Enhance when user change ctn, qty or uom, will divide to get the ratio and apply to all sku in same bom package list.
- Enhance when user delete one of the bom package sku, all related sku will be delete at the same time.
- Add a legend [BOM] after sku description.

11/30/2012 2:52:PM Fithri
- PDA - GRA Module

12/26/2012 12:01 PM Justin
- Enhanced to memorize the current barcode type.

3/7/2013 4:17 PM Justin
- Enhanced to show scan menu while found one item and it is auto add item.
- Enhanced not to go through all searching where found errors in the first place.

4/24/2015 10:39 AM Justin
- Enhanced can insert document no and choose GST code when add item from GRA.

4/22/2016 11:53 AM Andy
- Change get gra items po cost to get latest po only.
- Enhance scan gra items to filter vendor.

7/6/2017 2:41 PM Justin
- Enhanced to show message while adding items that is matched with PO/DO.

7/21/2017 11:49 AM Justin
- Bug fixed on error message show redundant while search SKU items for GRA returned more than 1 record.

6/14/2018 5:13 PM Andy
- Fixed to only search active sku.

10/23/2019 3:26 PM William
- Add new checking for check config "enable_po_agreement".

12/10/2019 2:00 PM William
- Fixed po module not checking block item in PO block list.

12/17/2019 1:33 PM William
- Fixed grn module not checking block item in grn block list.

12/30/2020 10:12 AM Rayleen
- Add checking, if config do_block_zero_stock_bal_items is enabled, block users to add items with zero stock

2/1/2021 4:19 PM William
- Enhanced to show "Selling Price", "Stock Balance", "Amt", "Disc", "Cost", "UOM" and "Remark" at Search Result & Item List.
*/
ini_set('DEBUG_MODE','1');

abstract class Scan_Product
{
	var $title;
	var $err = array();
	var $scan_templates = 'scan_product.tpl';
	var $result_templates = 'scan_product.result.tpl';
	
	abstract function default_();
	abstract function init_module();
	abstract function show_scan_product();
	abstract function add_items();  // return array('succes'=>true) for success added, or return array('error'=>array('err1','erro')) for failed
	
	function __construct($title)
	{
		$this->title = $title;
		$this->init_module();
		// if scan product
		if (isset($_REQUEST['scan_product']))
		{
			$this->search_product();
			exit;
		}
		
	    // if call other action
	    $action = $_REQUEST['a'];
		if ($action=='')	$action = 'default_';
		if (!method_exists($this, $action))
		{
			print "<h1>Error</h1>";
			print "<font color=red>".get_class($this)."::$action is not defined</font><br /><br />";
			print_r($_REQUEST);
			exit;
		}
		$this->$action();
	}
	
	function search_product(){
	    global $con, $smarty, $config, $LANG, $sessioninfo;
	    
	    $scan_tpl = $this->scan_templates;
	    $result_tpl = $this->result_templates;
		
		$module = strtolower($_SESSION['scan_product']['type']);
		if(isset($_REQUEST['grn_barcode_type'])) $_SESSION[$module]['barcode_type'] = $_REQUEST['grn_barcode_type'];
	    
	    // if it is submit from scan product result, it is qty add
	    if($_REQUEST['scan_product_result']){
	        $ret = $this->add_items();
			if($ret['success']){
				//nset($);
				if($_SESSION['scan_product']['type'] == "GRN" && $this->err) $_SESSION['grn']['err'] = $this->err;
				header("Location: $_SERVER[PHP_SELF]?a=show_scan_product");
				exit;
			}else   $this->err = array_merge($this->err,$ret['error']);
		}
		
		if(!$this->err){
			if($_REQUEST['grn_barcode_type'] == 1){ // scan by arms format
				$product_code = strtoupper($_REQUEST['product_code']);
				// cut last digit
				$product_code2 = strtoupper(substr($product_code,0,strlen($product_code)-1));
				
				if($product_code){

					$filter[] = "(si.mcode=".ms($product_code)." or si.mcode=".ms($product_code2).")";
					$filter[] = "(si.link_code=".ms($product_code)." or si.link_code=".ms($product_code2).")";
					$filter[] = "(si.sku_item_code=".ms($product_code)." or si.sku_item_code=".ms($product_code2).")";
					$filter[] = "(si.artno=".ms($product_code)." or si.artno=".ms($product_code2).")";
					
					if($_SESSION['scan_product']['type'] == "GRN"){
						$fields = ", uom.fraction as uom_fraction";
						$left_join = "left join uom on uom.id = si.packing_uom_id";
					}elseif($_SESSION['scan_product']['type'] == "GRA"){
						$left_join = " left join sku on sku.id = si.sku_id left join category on category.id = sku.category_id ";
						$filtergra[] = "sku.sku_type = ".ms($_SESSION['gra']['sku_type']);
						$filtergra[] = "category.department_id = ".ms($_SESSION['gra']['dept_id']);
					}elseif($_SESSION['scan_product']['type'] == "SO"){
						$left_join = " left join sku on sku.id = si.sku_id 
						left join sku_items_cost sic on sic.branch_id=".mi($_SESSION['so']['branch_id'])." and sic.sku_item_id=si.id
						left join sku_items_price sip on sip.branch_id=".mi($_SESSION['so']['branch_id'])." and sip.sku_item_id=si.id
						left join uom on uom.id = si.packing_uom_id";
						$fields = ", sku.remark as remark, sic.qty as stock_balance, ifnull(sip.price,si.selling_price) as item_selling_price, ifnull(sic.grn_cost,si.cost_price) as so_cost_price, uom.fraction as uom_fraction";
					}
					$filter = join(' or ',$filter);
					
					$nf = array();
					$nf[] = "( $filter )";
					$nf[] = "si.active=1";
					if($_SESSION['scan_product']['type'] == "GRA"){
						foreach ($filtergra as $fgra) $nf[] = $fgra;
					}
					
					$filter = join(' and ',$nf);
					$q1 = $con->sql_query($abc="select si.* $fields from sku_items si $left_join where $filter limit 100");
					//print $abc;

					if($con->sql_numrows($q1)>0 || ($_SESSION['scan_product']['type'] == "GRN" && $config['use_grn_future'])){
						$matched_with_po = array();
						
						if($_SESSION['scan_product']['type'] == "SO"){
							// uom
							$con->sql_query("select * from uom where active=1 order by code");
							while($r = $con->sql_fetchrow()){
								$uom[$r['id']] = $r;
							}
							$con->sql_freeresult();
							
							$smarty->assign('uom', $uom);
						}
						
						if($_SESSION['scan_product']['type'] == "GRN" && $config['use_grn_future']){
							// select grn items that is currently added/copied from PO/DO
							$q2 = $con->sql_query("select gi.sku_item_id, si.sku_id
												   from grn_items gi 
												   left join sku_items si on si.id = gi.sku_item_id 
												   where gi.grn_id = ".mi($_SESSION['grn']['id'])." and gi.branch_id = ".mi($_SESSION['grn']['branch_id'])." and gi.item_group in (0,1)");
							
							while($r1 = $con->sql_fetchassoc($q2)){
								$matched_with_po[$r1['sku_id']][$r1['sku_item_id']] = 1;
							}
							$con->sql_freeresult($q2);

						}
						while($r = $con->sql_fetchassoc($q1)){

							$r['sku_item_code'] = strtoupper($r['sku_item_code']);
							$r['artno'] = strtoupper($r['artno']);
							$r['mcode'] = strtoupper($r['mcode']);
							$r['link_code'] = strtoupper($r['link_code']);
							
							if(strtoupper($r['sku_item_code'])==$product_code||$r['sku_item_code']==$product_code2){
								$r['item_code_remark'] = "ARMS Code: ".$r['sku_item_code'];
							}elseif($r['artno']==$product_code||$r['artno']==$product_code2){
								$r['item_code_remark'] = "Art No: ".$r['artno'];
							}elseif($r['mcode']==$product_code||$r['mcode']==$product_code2){
								$r['item_code_remark'] = "MCode: ".$r['mcode'];
							}else{
								$r['item_code_remark'] = "Link Code: ".$r['link_code'];
							}
							
							if($_SESSION['scan_product']['type'] == "GRN"){
								if($config['sku_bom_additional_type']){
									$q2 = $con->sql_query("select si.*, bi.bom_id, bi.qty
														   from bom_items bi
														   left join sku_items si on si.id = bi.sku_item_id
														   left join sku s on s.id = si.sku_id 
														   left join sku_items msi on msi.id = bi.bom_id
														   left join sku ms on ms.id = msi.sku_id 
														   where bi.bom_id = ".mi($r['id'])." and ms.is_bom = 1 and msi.bom_type = 'package'");

									if($con->sql_numrows($q2) > 0){
										while($r1 = $con->sql_fetchassoc($q2)){
											$r['id'] = $r1['id'];
											$r['sku_item_code'] = strtoupper($r1['sku_item_code']);
											$r['artno'] = strtoupper($r1['artno']);
											$r['mcode'] = strtoupper($r1['mcode']);
											$r['link_code'] = strtoupper($r1['link_code']);
											$r['description'] = $r1['description'];
											$r['doc_allow_decimal'] = $r1['doc_allow_decimal'];
											
											if(strtoupper($r['sku_item_code'])==$product_code||$r['sku_item_code']==$product_code2){
												$r['item_code_remark'] = "ARMS Code: ".$r1['sku_item_code'];
											}elseif($r['artno']==$product_code||$r['artno']==$product_code2){
												$r['item_code_remark'] = "Art No: ".$r1['artno'];
											}elseif($r['mcode']==$product_code||$r['mcode']==$product_code2){
												$r['item_code_remark'] = "MCode: ".$r1['mcode'];
											}else{
												$r['item_code_remark'] = "Link Code: ".$r1['link_code'];
											}
										
											$_REQUEST['pcs'][$r1['id']] = $r1['qty'];
											$r['bom_ref_num'] = $r1['bom_id'];
											$r['bom_qty_ratio'] = $r1['qty'];
											
											if($matched_with_po[$r1['sku_id']][$r1['id']]){ 
												$r['matched_with_po'] = 1; // means matched with PO/DO
											}elseif(!$matched_with_po[$r1['sku_id']][$r1['id']] && $matched_with_po[$r1['sku_id']]){
												$r['matched_with_po_pc'] = 1; // means matched with PO/DO (parent & child)
											}elseif($matched_with_po && !$matched_with_po[$r1['sku_id']]){
												$r['unmatched_with_po'] = 1; // means it is item not in PO
											}
											
											$items[] = $r;
										}
										$con->sql_freeresult($q2);

										continue;
									}
								}
							}
							
							if($_SESSION['scan_product']['type'] == "GRA"){
								$result = $this->get_gra_cost_price($r['id'], $_SESSION['gra']['branch_id'], $_SESSION['gra']['vendor_id']);
								if(isset($result['cost_price'])){
									$r['return_cost'] = $result['cost_price'];
								}else{
									$this->err[] = $r['sku_item_code'].": ".$result['error'];
									continue;
								}
								
							}
							
							if($_SESSION['scan_product']['type'] == "PO" && $config['enable_po_agreement']){
								// do not allow to add item if got purchase agreement
								$con->sql_query("select pai.id 
								from purchase_agreement_items pai
								left join purchase_agreement pa on pa.branch_id=pai.branch_id and pa.id=pai.purchase_agreement_id
								where pai.sku_item_id=".mi($r['id'])." and pa.active=1 and pa.status=1 and pa.approved=1 limit 1");
								$pai = $con->sql_fetchassoc();
								$con->sql_freeresult();
								if($pai){
									$this->err[] = $LANG['PO_NOT_ALLOW_OPEN_BUY_ITEM_WITH_PURCHASE_AGREEMENT'];
									continue;
								}else{
									// check foc item as well
									$pa_sql = "select pafi.id
									from purchase_agreement_foc_items pafi
									join purchase_agreement pa on pa.branch_id=pafi.branch_id and pa.id=pafi.purchase_agreement_id
									where pafi.sku_item_id=".mi($r['id'])." and pa.active=1 and pa.status=1 and pa.approved=1 limit 1";
									$con->sql_query($pa_sql);
									$pai2 = $con->sql_fetchassoc();
									$con->sql_freeresult();
									if($pai2){
										$this->err[] = $LANG['PO_NOT_ALLOW_OPEN_BUY_ITEM_WITH_PURCHASE_AGREEMENT'];
										continue;
									}
								}
							}
							
							if($_SESSION['scan_product']['type'] == "SO"){
								//get reserve_qty
								$so_sql=$con->sql_query($qry="select sum(soi.pcs + (uom.fraction * soi.ctn)) as  reserve_qty
								from sales_order_items soi 
								left join sales_order so on so.id = soi.sales_order_id and soi.branch_id = so.branch_id
								left join uom on uom.id = soi.uom_id
								where so.approved = 1 and so.delivered = 0 and so.exported_to_pos = 0 and so.active = 1 and so.status = 1 and
								soi.sku_item_id = ".mi($r['id'])." and so.branch_id=".mi($_SESSION['so']['branch_id'])."
								and so.id <> ".mi($_SESSION['so']['id']));
								$soi_data = $con->sql_fetchassoc($so_sql);
								$con->sql_freeresult($soi_data);
								$r['reserve_qty'] = $soi_data['reserve_qty'];
								
								//get mprice
								$con->sql_query("select * from sku_items_mprice sip
								left join sales_order so on so.selling_type = sip.type and sip.branch_id = so.branch_id
								where sip.branch_id=".mi($_SESSION['so']['branch_id'])." and sip.sku_item_id=".mi($r['id'])." 
								and so.id =".mi($_SESSION['so']['id']));
								$mprice_info = $con->sql_fetchassoc();
								$con->sql_freeresult();
								
								if($mprice_info['price'])	$r['item_selling_price'] = $mprice_info['price'];
							}
							
							// found module is under GST, load gst info
							if($this->is_under_gst){
								$gst_info = get_sku_gst("input_tax", $r['id']);
								$r['gst_id'] = $gst_info['id'];
							}
							
							if($matched_with_po[$r['sku_id']][$r['id']]) $r['matched_with_po'] = 1;
							elseif($matched_with_po && !$matched_with_po[$r['sku_id']]) $r['unmatched_with_po'] = 1;
							if($matched_with_po[$r['sku_id']][$r['id']]){ 
								$r['matched_with_po'] = 1; // means matched with PO/DO
							}elseif(!$matched_with_po[$r['sku_id']][$r['id']] && $matched_with_po[$r['sku_id']]){
								$r['matched_with_po_pc'] = 1; // means matched with PO/DO (parent & child)
							}elseif($matched_with_po && !$matched_with_po[$r['sku_id']]){
								$r['unmatched_with_po'] = 1; // means it is item not in PO
							}

							// if do_block_zero_stock_bal_items is enabled, do not add items with zero stock
							if($_SESSION['scan_product']['type'] == "DO" && $config['do_block_zero_stock_bal_items']){
								$stock_qry = "select qty as stock_balance
								from sku_items_cost 
								where sku_item_id=".mi($r['id'])." and branch_id=".$_SESSION['do']['branch_id']."";
			
								$con->sql_query($stock_qry);

								$qry_res = $con->sql_fetchassoc();

								$con->sql_freeresult();
								if(!$qry_res || $qry_res['stock_balance'] < 1){
									$this->err[] = "'$product_code' has no stock";
									continue;
								}
							}
							
							$items[] = $r;
						}
						
						if($_SESSION['scan_product']['type'] == "GRA"){
							$smarty->assign('return_type',$_REQUEST['return_type']);
							
							if(count($items) > 1) unset($this->err); // need to reset the error from item listing
						}
						
						// for SKU not in ARMS from GRN future
						if($con->sql_numrows($q1) == 0 && $_SESSION['scan_product']['type'] == "GRN" && $config['use_grn_future']){
							$err = $product_code." is an invalid SKU item.";
							if(is_array($this->err)) $this->err[] = $err;
							$smarty->assign("is_isi", 1);
							$isi['id'] = -1;
							$items[] = $isi;
						}
						
						//check sku items block in po and grn
						if($_SESSION['scan_product']['type'] == "PO" || $_SESSION['scan_product']['type'] == "GRN"){
							foreach($items as $key=>$col){
								//check the Block item in PO
								if($_SESSION['scan_product']['type'] == "PO"){
									$block_list[$col['id']] = unserialize($col['block_list']);
									if($block_list[$col['id']][$sessioninfo['branch_id']]){
										$blocked_po[$col['id']] = 1;
										$smarty->assign('blocked_po',$blocked_po);
									}
								}
								//check the Block item in GRN
								if($_SESSION['scan_product']['type'] == "GRN"){
									$block_list2[$col['id']] = unserialize($col['doc_block_list']);
									if($block_list2[$col['id']]['grn'][$sessioninfo['branch_id']]){
										$blocked_doc[$col['id']] = 1;
										$smarty->assign('blocked_doc',$blocked_doc);
									}
								}
							}
						}				
						$con->sql_freeresult($q1);

						$smarty->assign('items',$items);
						
						/*
						echo '<pre>';
						print_r($_REQUEST);
						echo '</pre>';
						*/
						
						$smarty->assign('err',$this->err);
						if(count($items)>0){
							if(count($items) == 1 && $_REQUEST['auto_add_item']) $smarty->display($scan_tpl);
							else $smarty->display($result_tpl);
							exit;
						}
					}

					if(!$this->err)	$this->err[] = "Item Not Found for '$product_code'";
				}
			}elseif(!$_REQUEST['grn_barcode_type']){ // scan by GRN barcoder
				$this->add_item_by_grn_barcode();
			}
		}
		
		if($_SESSION['scan_product']['type'] == "GRA"){
			$return_type_list = array(
				'Damage',
				'Expiry',
				'Exchange',
				'No Order',
				'Short Supply',
				'Slow Moving',
				'Fair Return',
				'Over Delivery',
				'Consignment Return',
				'Over Qty (Cost Not Billing)',
				'Other'
			);
			$smarty->assign("return_type_list", $return_type_list);
		}
		
		$smarty->assign('err',$this->err);
		$smarty->display($scan_tpl);
	}
	
	function __destruct()
	{
		// nop
	}
	
	function load_branch_group($id=0){
		global $con,$smarty;
	    if(isset($this->branch_group))  return $this->branch_group;
		$branch_group = array();
		
		// check whether select all or specified group
		if($id>0){
			$where = "where id=".mi($id);
			$where2 = "and bgi.branch_group_id=".mi($id);
		}
		// load header
		$con->sql_query("select * from branch_group $where",false,false);
		if($con->sql_numrows()<=0) return;
		while($r = $con->sql_fetchrow()){
            $branch_group['header'][$r['id']] = $r;
		}
		

		// load items
		$con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 $where2 order by branch.sequence, branch.code",false,false);
		while($r = $con->sql_fetchrow()){
	        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		
		$this->branch_group = $branch_group;
		//print_r($this->branch_group);
		$smarty->assign('branch_group',$branch_group);
		return $branch_group;
	}
	
	function get_gra_cost_price($sku_item_id=0, $branch_id=0, $vendor_id=0) {

		global $con,$config;
		$dp = $config['gra_cost_decimal_points'];

		$ret = array();
		
		// select last GRN using vendor_sku_history
		$con->sql_query("select cost_price from vendor_sku_history where source = 'GRN' and branch_id = $branch_id and sku_item_id = $sku_item_id and vendor_id = $vendor_id order by added desc limit 1");
		if ($r = $con->sql_fetchrow()) $ret['cost_price'] = $r['cost_price'];
		$con->sql_freeresult();
		if(isset($ret['cost_price']))	return $ret;
		
		// if no grn, get from PO
		$con->sql_query("select po_items.order_price / po_items.order_uom_fraction as cost_price,u.fraction from po_items
left join po on po_items.po_id = po.id and po_items.branch_id = po.branch_id 
left join vendor on po.vendor_id = vendor.id
left join uom u on u.id = po_items.order_uom_id
where po_items.sku_item_id = $sku_item_id and po_items.branch_id=$branch_id and po.vendor_id=$vendor_id 
order by po.added desc limit 1");
		//if ($r = $con->sql_fetchrow()) $ret['cost_price'] = round($r['cost_price']/$r['fraction'], $dp);
		if ($r = $con->sql_fetchrow()) $ret['cost_price'] = round($r['cost_price'], $dp);
		$con->sql_freeresult();
		if(isset($ret['cost_price']))	return $ret;
		
		// if no po, get latest cost
		$con->sql_query("select ifnull(sic.grn_cost,sku_items.cost_price) as cost_price
from sku_items
left join sku_items_cost sic on sku_items.id=sic.sku_item_id and sic.branch_id=$branch_id
left join sku on sku_items.sku_id = sku.id
where sku_items.id=$sku_item_id and sku.vendor_id=$vendor_id limit 1");
		if ($r = $con->sql_fetchrow()) $ret['cost_price'] = $r['cost_price'];
		$con->sql_freeresult();
		if(isset($ret['cost_price']))	return $ret;
		
		
		// if still no item, get from master
		//$con->sql_query("select sku_items.cost_price from sku_items where sku_items.id = $sku_item_id");
		//if ($r = $con->sql_fetchrow()) $ret['cost_price'] = $r['cost_price'];
		$ret['error'] = "Item is not belongs to this vendor";
		return $ret;
	}
	
	function get_gra_selling_price($sku_item_id=0, $branch_id=0) {
	
		global $con,$config;
		$dp = $config['gra_cost_decimal_points'];
	
		$con->sql_query("select sip.price from sku_items_price sip where sip.sku_item_id = $sku_item_id and sip.branch_id = $branch_id limit 1");
		if ($r = $con->sql_fetchrow()) return round($r['price'],$dp);
		
		// get selling price and decimal points from sku_items
		$con->sql_query("select sku_items.selling_price from sku_items where sku_items.id = $sku_item_id limit 1");
		if ($r = $con->sql_fetchrow()) return round($r['selling_price'],$dp);
	}
	
}
?>
