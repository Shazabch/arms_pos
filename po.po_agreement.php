<?php
/*
8/13/2012 5:38 PM Andy
- Add put po_create_type = 2 for po when generate PO from Purchase Agreement.

8/14/2012 10:46 AM Justin
- Bug fixed on subbranch when generating new PO.

8/15/2012 5:45 PM Justin
- Enhanced to have alphabet array.

8/22/2012 11:57 AM Andy
- Fix rule alphabet running bugs.

9/13/2012 5:29 PM Andy
- Add new purchase agreement type, Seasonal.

9/19/2012 1:58 PM Justin
- Enhanced to update foc qty into similar item while found sku item, cost and selling are the same.

9/27/2012 10:52 AM Justin
- Enhanced to skip department checking while found user tick "Allow items from all departments".

10/29/2012 10:53 AM Justin
- Bug fixed on get empty branch id.

11/8/2012 12:03 PM Justin
- Enhanced to use add on days from config (default 14) for PO cancellation date.

3/8/2013 10:53 AM Fithri
- bugfix : when partial delivery checkbox is checked, reopen the saved PO number, the partial delivery checkbox become unchecked

01/06/2016 11:51 PM DingRen
- fix missing is_under_gst

7/5/2016 4:17 PM Andy
- Enhanced to able to create from tmp_purchase_agreement_info.

8/23/2016 5:05 PM Andy
- Enhanced to check gst when generate po.

9/8/2016 4:23 PM Andy
- Enhanced to have remark for purchase agreement.
- Enhanced to bring purchase agreement remark to po when the item was selected.

3/24/2017 11:16 AM Andy
- Enhanced to auto recalculate all PO Amount using reCalcatePOUsingOldMethod() when generate or update po.

3/27/2017 3:01 PM Andy
- Change reCalcatePOUsingOldMethod() to reCalcatePOAmt().

10/3/2017 10:12 AM Justin
- Enhanced to call sales trend from skuManager.php. 
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if(!$config['enable_po_agreement'])	js_redirect($LANG['NEED_CONFIG'], "/index.php");
//if (!privilege('PO_SETUP_AGREEMENT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_SETUP_AGREEMENT', BRANCH_CODE), "/index.php");
//if(BRANCH_CODE != 'HQ')	js_redirect($LANG['HQ_ONLY'], "/index.php");

include("po.include.php");

class PURCHASE_AGREEMENT extends Module{
	var $allow_edit = 0;
	var $bid = 0;
	var $ori_alphabet_list = array(1=>"A", 2=>"B", 3=>"C", 4=>"D", 5=>"E", 6=>"F", 7=>"G", 8=>"H", 9=>"I", 10=>"J", 11=>"K", 12=>"L", 13=>"M", 14=>"N", 15=>"O", 16=>"P", 17=>"Q", 18=>"R", 19=>"S", 20=>"T", 21=>"U", 22=>"V", 23=>"W", 24=>"X", 25=>"Y", 26=>"Z");
	
	function __construct($title){
		global $con, $config, $smarty, $sessioninfo;
	
		$this->bid = mi($sessioninfo['branch_id']);

		init_selection();
		
		
		$this->alphabet_list = $this->ori_alphabet_list;	// clone original alp

		parent::__construct($title);
	}
	
	function _default(){
		global $con, $config, $smarty, $sessioninfo;
		
		$this->display();
	}
	
	function open($branch_id = 0, $id = 0, $tmp_pa_id = 0){
        global $con, $sessioninfo, $smarty, $LANG, $config;
   
        $form = $_REQUEST;
		if($tmp_pa_id > 0){	// load from tmp_purchase_agreement_info
			$pa_data = $this->load_tmp_purchase_agreeement($branch_id, $tmp_pa_id);
			//print_r($pa_data);exit;
			$form['po_branch_id'] = $pa_data['header_info']['po_branch_id'];
			$form['dept_id'] = $pa_data['header_info']['department_id'];
			$form['vendor_id'] = $pa_data['header_info']['vendor_id'];
			$form['date'] = $pa_data['header_info']['po_date'];
			$form['is_ibt'] = $pa_data['header_info']['is_ibt'];
			$form['partial_delivery'] = $pa_data['header_info']['partial_delivery'];
			$vendor_info = get_vendor_info($form['vendor_id']);
			$form['vendor_desc'] = $vendor_info['description'];
		}else{
			if(!$id){
				$id = mi($_REQUEST['id']);
				$branch_id = mi($_REQUEST['branch_id']);
			}
		}
        
		
        if($_REQUEST['a']=='view'){
			$this->allow_edit = 0;
			$smarty->assign('allow_edit', 0);
		}	
        
		if(!$id)	$id=time();
		$form['id']=$id;
		$form['branch_id'] = $sessioninfo['branch_id'];
		$form['first_time'] = 1;
		$this->allow_edit = 1;
		$smarty->assign('allow_edit', $this->allow_edit);
		
		//print_r($form);
		$items = array();
		$items = $this->load_matched_pa_items_list($form);
		if($tmp_pa_id && $items){
			$this->process_tmp_pa_items($form, $pa_data, $items);
		}
		//print_r($items);
		$smarty->assign('form', $form);
		$smarty->assign('items', $items);
		$smarty->assign('tmp_pa_data', $pa_data);
		
		$this->display('po.po_agreement.tpl');
	}
	
	function refresh(){
		global $con;
		
	    $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
        $this->open($branch_id, $id);
	}
	
	private function extend_alphabet_list(){
		$max_length = count($this->ori_alphabet_list);
		$curr_length = count($this->alphabet_list);
		
		if($curr_length>=$max_length*($max_length+1)){
			$find_index = $curr_length-($max_length*($max_length))+1;
			$ext_prefix = $this->alphabet_list[$find_index];
		}
		$prefix = $this->ori_alphabet_list[$curr_length/$max_length];
		
		for($i = 1; $i <= $max_length; $i++){
			$new_alp = $ext_prefix.$prefix.$this->ori_alphabet_list[$i];
			$this->alphabet_list[] = $new_alp;
		}
	}
	
	function load_matched_pa_items_list($form){
		global $con, $sessioninfo, $smarty;
		
		//$form = $_REQUEST;
		
		if(!$form['all_depts']) $dept_filter = "and dept_id = ".mi($form['dept_id']);
		
		$q1 = $con->sql_query("select *
							   from purchase_agreement pa
							   where active = 1 and status = 1 and approved = 1 and vendor_id = ".mi($form['vendor_id'])." and ".ms($form['date'])." between date_from and date_to $dept_filter
							   order by pa.id, branch_id");
		if($form['po_option'] == 3) $deliver_bid = 1;
		else $deliver_bid = $form['po_branch_id'];

		$rule_group_num = 0;
		$got_seasonal = false;
		
		while($pa = $con->sql_fetchassoc($q1)){
			$item_list = $item_rules = $rules_to_alp = array();
			$rule_group_alp = '';
			
			$item_list = load_purhcase_agreement_items_list($pa['branch_id'], $pa['id']);
			
			if($item_list['item']){
				foreach($item_list['item'] as $key=>$f){
					$item_is_allowed = true;
					
					if(!$f['allowed_branches'][$deliver_bid]){
						$item_is_allowed = false;
					}

					if(!$item_is_allowed) unset($item_list['item'][$key]);
					else{
						if(!$rule_group_alp){
							$rule_group_num++;
							if(!isset($this->alphabet_list[$rule_group_num]))	$this->extend_alphabet_list();
							$rule_group_alp = $this->alphabet_list[$rule_group_num];
						}
						
						
						$tmp = $item_list['item'][$key];
						$tmp['rule_group_alp'] = $rule_group_alp;
						$tmp['pa_type'] = $pa['pa_type'];
						$tmp['remark'] = $pa['remark'];
						
						if($pa['pa_type']=='seasonal')	$got_seasonal = true;
						
						$items['item'][] = $tmp;
						$item_rules[$f['rule_num']] = $f['rule_num'];
						
						$rules_to_alp[$f['rule_num']] = $tmp['rule_group_alp'];
					}
				}
			}
			
			if($item_list['foc_item']){
				foreach($item_list['foc_item'] as $key=>$f){
					$foc_item_is_allowed = true;
					
					if(!$f['allowed_branches'][$deliver_bid]){
						$foc_item_is_allowed = false;
					}

					foreach($f['ref_rule_num'] as $r1=>$rule){
						if(!$item_rules[$rule]) $foc_item_is_allowed = false;
					}

					if(!$foc_item_is_allowed) unset($item_list['foc_item'][$key]);
					else{
						$tmp = $item_list['foc_item'][$key];
						$tmp['rule_group_alp'] = $rule_group_alp;
						
						$items['foc_item'][] = $tmp;
					} 
				}
			}
		}
		$con->sql_freeresult($q1);
		
		// check seasonal and remove those same item but not seasonal
		if($got_seasonal && $items['item']){
			$got_item_removed = false;
			foreach($items['item'] as $key => $pai){	// loop for each item
				if($pai['pa_type']=='seasonal'){	// need check this seasonal item					
					foreach($items['item'] as $key2 => $pai2){
						if($pai2['pa_type'] != 'seasonal' && $pai2['sku_item_id'] == $pai['sku_item_id']){
							unset($items['item'][$key2]);
							$got_item_removed = true;
						}
					}
				}
			}
			
			// check whether need to re-construct foc item list
			if($got_item_removed && $items['foc_item']){
				foreach($items['foc_item'] as $key => $pafi){	// loop for each foc items
					$pass_list = array();
					$ref_rule_nu_count = count($pafi['ref_rule_num']);
					
					foreach($items['item'] as $key2 => $pai){	// loop for each item
						if($pafi['branch_id'] == $pai['branch_id'] && $pafi['purchase_agreement_id'] == $pai['purchase_agreement_id']){	// only check if same doc
							foreach($pafi['ref_rule_num'] as $rule_num){	// loop for required rule
								if($rule_num == $pai['rule_num']){	// found required rule matched
									$pass_list[$rule_num] = $rule_num;
									break;
								}
							}
						}
						if(count($pass_list) >= $ref_rule_nu_count)	break;
					}
					
					if(count($pass_list) < $ref_rule_nu_count){
						unset($items['foc_item'][$key]);	// this item not qualified
					}
				}
			}
		}
		
		$smarty->assign('alphabet_list', $this->alphabet_list);
		
		//print_r($items);
		
		return $items;
	}
	
	function save($is_confirm = false){
		global $con, $smarty, $sessioninfo, $LANG,$config, $appCore;
		$form = $_REQUEST;
		//print_r($form);exit;
		
		if(!$form['item_check']){
			$err[] = "No item checked!";
			$smarty->assign("err", $err);
			$smarty->assign("form", $form);
			exit;
		}
		
		$po = array();
		$po['branch_id'] = $sessioninfo['branch_id'];
	    $po['po_branch_id'] = $form['po_branch_id'];
	    $po['user_id'] = $sessioninfo['id'];
	    $po['vendor_id'] = $form['vendor_id'];
	    $po['department_id'] = $form['dept_id'];
		if($config['po_enable_ibt']) $po['is_ibt'] = $form['is_ibt'];
	    //$po['po_option'] = $form['po_option'];
	    $po['po_date'] = $form['date'];
	    $po['delivery_date'] = date("d/m/Y", strtotime($form['date']));
		if($config['po_agreement_cancellation_days']) $cancellation_days = $config['po_agreement_cancellation_days'];
		else $cancellation_days = 14;
		$po['cancel_date'] = date("d/m/Y", strtotime("+$cancellation_days days", strtotime($form['date'])));
	    $po['partial_delivery'] = $form['partial_delivery'];
	    $po['remark2'] = serialize('GENERATED FROM PURCHASE AGREEMENT');
	    $po['added'] = $po['last_update'] = 'CURRENT_TIMESTAMP';
		$po['po_create_type'] = 2;

		if($config['enable_gst']){
			$q1 = $con->sql_query("select * from vendor where gst_register not in (0, -1)");
			while($r = $con->sql_fetchassoc($q1)){
				$vendor_gst_list[$r['id']] = $r;
			}
			$con->sql_freeresult($q1);
			
			$prms = array();
			$prms['vendor_id'] = $po['vendor_id'];
			$prms['date'] = $po['po_date'];
			$po['is_under_gst'] = check_gst_status($prms);
			if($po['is_under_gst'] && !$input_gst_list) $input_gst_list = construct_gst_list('purchase');
			
			$prms = array();
			$prms['branch_id'] = $po['branch_id'];
			$prms['date'] = $po['po_date'];
			$branch_is_under_gst = check_gst_status($prms);
		}
		
		if($form['tmp_purchase_agreement_info_id'] > 0) $pa_data = $this->load_tmp_purchase_agreeement($po['branch_id'], $form['tmp_purchase_agreement_info_id']);

		// remark
		if($form['remarks_on']){
			$on_counter = 0;
			foreach($form['remarks_on'] as $tmp_bid => $tmp_pa_id_list){
				foreach($tmp_pa_id_list as $tmp_pa_id => $is_on){
					if($is_on){
						$on_counter++;
						$po['remark'] .= "$on_counter) ".$form['remarks'][$tmp_bid][$tmp_pa_id]."\n";
					}
				}
			}
			if($po['remark'])	$po['remark'] = serialize($po['remark']);
		}
		//print_r($po);exit;
		$con->sql_query("insert into po ".mysql_insert_by_field($po));
	    $po_id = $con->sql_nextid();

		// select uom
		$con->sql_query("select * from uom where fraction = 1 and active = 1 order by id limit 1");
		$uom = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
   		$total_po_amount = 0;
		$item_id_list = $new_pi_list = array();
		
		//////// old insert method //////////
		// insert items
		/*foreach($form['item_check']['item'] as $pai_id=>$pai_bid_list){
			foreach($pai_bid_list as $r=>$pai_bid){
				$po_items = array();
				$po_items['branch_id'] = $po['branch_id'];
				$po_items['po_id'] = $po_id;
				$po_items['user_id'] = $po['user_id'];
				$po_items['sku_item_id'] = $form['sku_item_id']['item'][$pai_id][$pai_bid];
				$po_items['selling_price'] = $form['suggest_selling_price']['item'][$pai_id][$pai_bid];
				$po_items['selling_uom_id'] = $uom['id'];
				$po_items['order_uom_id'] = $uom['id'];
				$po_items['order_uom_fraction'] = $uom['fraction'];
				$po_items['order_price'] = $form['purchase_price']['item'][$pai_id][$pai_bid];
				$po_items['selling_uom_fraction'] = $uom['fraction'];
				$po_items['discount'] = $form['discount']['item'][$pai_id][$pai_bid];
				$po_items['cost_indicate'] = "PA";
				$po_items['pa_item_id'] = $pai_id;
				$po_items['pa_branch_id'] = $pai_bid;
				
				$balance=get_stock_balance($po_items['sku_item_id']);
				$po_items['stock_balance'] = $balance['stock_balance'];
				
				// get sales trend
				$sales_trend = get_sales_trend($po_items['sku_item_id']);
				$po_items['sales_trend'] =  $sales_trend['sales_trend'];

				$po_items['qty_loose'] = $form['qty']['item'][$pai_id][$pai_bid];
				$po_amount += $po_items['qty_loose'] * $po_items['order_price'];

				$po_items['sales_trend'] = serialize($po_items['sales_trend']);
				$con->sql_query("insert into po_items ".mysql_insert_by_field($po_items));
				$new_pi_id = $con->sql_nextid();

				$pa_id = $form['pa_id']['item'][$pai_id][$pai_bid];
				$rule_num = $form['rule_num']['item'][$pai_id][$pai_bid];
				$new_pi_list[$pa_id][$pai_bid][$rule_num] = $new_pi_id;
			}
		}
		
		// insert foc items
		if($form['item_check']['foc_item']){
			foreach($form['item_check']['foc_item'] as $pai_id=>$pai_bid_list){
				foreach($pai_bid_list as $r=>$pai_bid){
					$sku_item_id = $form['sku_item_id']['foc_item'][$pai_id][$pai_bid];
					$selling_price = $form['suggest_selling_price']['foc_item'][$pai_id][$pai_bid];
					$order_price = $form['purchase_price']['foc_item'][$pai_id][$pai_bid];
					$foc_loose = $form['qty']['foc_item'][$pai_id][$pai_bid];

					// found the only one rule required for pa item
					if(count($form['ref_rule_num']['foc_item'][$pai_id][$pai_bid]) == 1){
						$rule = $form['ref_rule_num']['foc_item'][$pai_id][$pai_bid][0];
						$pi_id = $new_pi_list[$pa_id][$pai_bid][$rule];
						if($pi_id){
							$q1 = $con->sql_query("select * from po_items where po_id = ".mi($po_id)." and branch_id = ".mi($po['branch_id'])." and sku_item_id = ".mi($sku_item_id)." and order_price = ".mf($order_price)." and selling_price = ".mf($selling_price)." and id = ".mi($pi_id));
							
							// found that system has similar item
							if($con->sql_numrows($q1) > 0){
								$con->sql_freeresult($q1);
								$con->sql_query("update po_items set foc_loose = ".mf($foc_loose)." where id = ".mi($pi_id)." and po_id = ".mi($po_id)." and branch_id = ".mi($po['branch_id']));
								continue;
							}
						}
					}
				
					$po_items = array();
					$po_items['branch_id'] = $po['branch_id'];
					$po_items['po_id'] = $po_id;
					$po_items['user_id'] = $po['user_id'];
					$po_items['sku_item_id'] = $sku_item_id;
					$po_items['selling_price'] = $selling_price;
					$po_items['selling_uom_id'] = $uom['id'];
					$po_items['order_uom_id'] = $uom['id'];
					$po_items['order_uom_fraction'] = $uom['fraction'];
					$po_items['order_price'] = $order_price;
					$po_items['selling_uom_fraction'] = $uom['fraction'];
					//$po_items['discount'] = $form['discount']['foc_item'][$pai_id][$pai_bid];
					$po_items['cost_indicate'] = "PA";
					$po_items['is_foc'] = 1;
					$po_items['pa_foc_item_id'] = $pai_id;
					$po_items['pa_branch_id'] = $pai_bid;
					
					$balance=get_stock_balance($po_items['sku_item_id']);
					$po_items['stock_balance'] = $balance['stock_balance'];
					
					// get sales trend
					$sales_trend = get_sales_trend($po_items['sku_item_id']);
					$po_items['sales_trend'] =  $sales_trend['sales_trend'];

					$po_items['foc_loose'] = $foc_loose;
					$po_amount += $po_items['foc_loose'] * $po_items['order_price'];

					$po_items['sales_trend'] = serialize($po_items['sales_trend']);
					
					// setup foc share cost...
					$pa_id = $form['pa_id']['foc_item'][$pai_id][$pai_bid];
					$foc_share_cost_list = array();
					foreach($form['ref_rule_num']['foc_item'][$pai_id][$pai_bid] as $r=>$rule){
						$pi_id = $new_pi_list[$pa_id][$pai_bid][$rule];
						if($pi_id) $foc_share_cost_list[$pi_id] = "on";
					}
					if($foc_share_cost_list) $po_items['foc_share_cost'] = serialize($foc_share_cost_list);
					
					$con->sql_query("insert into po_items ".mysql_insert_by_field($po_items));
				}
			}
		}*/
		
		$si_info_list = $po_items_list = $po_foc_items_list = $normal_items_list = array();
		
		/////// new insert method ///////////
		//$_REQUEST['po_branch_id'] = $po['po_branch_id'];
		$bom_ref_num = time();
		$pa_to_bom_ref_num = array();
		
		foreach($form['item_check']['item'] as $pai_id=>$pai_bid_list){
			$po_items = array();
			
			foreach($pai_bid_list as $r=>$pai_bid){
				$sid = mi($form['sku_item_id']['item'][$pai_id][$pai_bid]);
				$pa_id = $form['pa_id']['item'][$pai_id][$pai_bid];
				
				if(!$sid)	continue;
				
				$qty = $form['qty']['item'][$pai_id][$pai_bid];
				$order_price = $form['purchase_price']['item'][$pai_id][$pai_bid];
				$selling_price = $form['suggest_selling_price']['item'][$pai_id][$pai_bid];
				$discount = $form['discount']['item'][$pai_id][$pai_bid];
				$rule_num = $form['rule_num']['item'][$pai_id][$pai_bid];
				
				if(!isset($si_info_list[$sid])){
					$si_info_list[$sid] = $this->load_si_info($po['branch_id'], $sid);
				}
				
				// get the sku items info
				$si_info = $si_info_list[$sid];
				
				$need_add = true;
				
				// only go to retrieve sku info if got bom package
				if($config['sku_bom_additional_type']){
					// is bom package sku
					if($si_info['is_bom'] && $si_info['bom_type']=='package'){
						$bom_ref_num++;
						$pa_to_bom_ref_num[$pai_bid][$pa_id][$sid][$rule_num] = $bom_ref_num;
						
						// get item list in bom sku
						$q_bi = $con->sql_query("select bi.sku_item_id as sid,bi.qty from bom_items bi where bi.bom_id=".mi($sid)." order by bi.sku_item_id");
						while($r = $con->sql_fetchassoc($q_bi)){
							$tmp_sid = mi($r['sid']);
								
							$pcs = $r['qty'] * $qty;	// multiply bom sku qty
							
							if(!isset($si_info_list[$tmp_sid]))	$si_info_list[$tmp_sid] = $this->load_si_info($po['branch_id'], $tmp_sid);
							
							$tmp_si_info = $si_info_list[$tmp_sid];
							
							$tmp = array();
							$tmp['pa_item_id'] = $pai_id;
							$tmp['pa_branch_id'] = $pai_bid;
					
							$tmp['sku_item_id'] = $tmp_sid;
							$tmp['qty_loose'] = $pcs;
							
							$tmp['selling_price'] = $tmp_si_info['items_detail']['selling_price'];
							$tmp['selling_uom_id'] = $uom['id'];
							$tmp['selling_uom_fraction'] = $uom['fraction'];
							
							
							$tmp['order_price'] = $tmp_si_info['items_detail']['order_price'];
							$tmp['order_uom_id'] = $uom['id'];
							$tmp['order_uom_fraction'] = $uom['fraction'];
							
							$tmp['cost_indicate'] = $tmp_si_info['items_detail']['cost_indicate'];
							
							$tmp['stock_balance'] = $tmp_si_info['stock_balance'];
							$tmp['sales_trend'] =  $tmp_si_info['sales_trend'];
							
							$tmp['discount'] = $discount;
							
							$tmp['bom_ref_num'] = $bom_ref_num;
							$tmp['bom_qty_ratio'] = $r['qty'];
							
							$row_amt = $tmp['qty_loose'] * $tmp['order_price'];
							if ($tmp['discount']){
								$disc_amount = get_discount_amt($row_amt, $tmp['discount']);
								$row_amt = round($row_amt - $disc_amount, 5);
							}
					
							$total_po_amount += $row_amt;
							
							// the value later need to delete before insert
							$tmp['rule_num'] = $rule_num;
							$tmp['pa_id'] = $pa_id;
							
							$po_items_list[] = $tmp;
							unset($tmp);
						}
						$con->sql_freeresult($q_bi);
						$need_add = false;	// already add by bom list, no need add parent bom sku
					}
				}
				
				if($need_add){
					$tmp = array();
					$tmp['pa_item_id'] = $pai_id;
					$tmp['pa_branch_id'] = $pai_bid;
					
					$tmp['sku_item_id'] = $sid;
					$tmp['qty_loose'] = $qty;
					
					$tmp['selling_price'] = $selling_price;
					$tmp['selling_uom_id'] = $uom['id'];
					$tmp['selling_uom_fraction'] = $uom['fraction'];
					
					$tmp['order_price'] = $order_price;
					$tmp['order_uom_id'] = $uom['id'];
					$tmp['order_uom_fraction'] = $uom['fraction'];
					
					$tmp['cost_indicate'] = 'PA';
					
					$tmp['stock_balance'] = $si_info['stock_balance'];
					$tmp['sales_trend'] =  $si_info['sales_trend'];
					
					$tmp['discount'] = $discount;
					
					$row_amt = $tmp['qty_loose'] * $tmp['order_price'];
					if ($tmp['discount']){
						$disc_amount = get_discount_amt($row_amt, $tmp['discount']);
						$row_amt = round($row_amt - $disc_amount, 5);
					}
			
					$total_po_amount += $row_amt;
					
					// the value later need to delete before insert
					$tmp['rule_num'] = $rule_num;
					$tmp['pa_id'] = $pa_id;
					
					$po_items_list[] = $tmp;
					unset($tmp);
				}
				
				/*$po_items['branch_id'] = $po['branch_id'];
				$po_items['po_id'] = $po_id;
				$po_items['user_id'] = $po['user_id'];
				$po_items['sku_item_id'] = $form['sku_item_id']['item'][$pai_id][$pai_bid];
				$po_items['selling_price'] = $form['suggest_selling_price']['item'][$pai_id][$pai_bid];
				$po_items['selling_uom_id'] = $uom['id'];
				$po_items['order_uom_id'] = $uom['id'];
				$po_items['order_uom_fraction'] = $uom['fraction'];
				$po_items['order_price'] = $form['purchase_price']['item'][$pai_id][$pai_bid];
				$po_items['selling_uom_fraction'] = $uom['fraction'];
				$po_items['discount'] = $form['discount']['item'][$pai_id][$pai_bid];
				$po_items['cost_indicate'] = "PA";
				$po_items['pa_item_id'] = $pai_id;
				$po_items['pa_branch_id'] = $pai_bid;
				
				$balance=get_stock_balance($po_items['sku_item_id']);
				$po_items['stock_balance'] = $balance['stock_balance'];
				
				// get sales trend
				$sales_trend = get_sales_trend($po_items['sku_item_id']);
				$po_items['sales_trend'] =  $sales_trend['sales_trend'];

				$po_items['qty_loose'] = $form['qty']['item'][$pai_id][$pai_bid];
				$po_amount += $po_items['qty_loose'] * $po_items['order_price'];

				$po_items['sales_trend'] = serialize($po_items['sales_trend']);
				$con->sql_query("insert into po_items ".mysql_insert_by_field($po_items));
				$new_pi_id = $con->sql_nextid();

				// store ref num list for later add foc use
				$pa_id = $form['pa_id']['item'][$pai_id][$pai_bid];
				$rule_num = $form['rule_num']['item'][$pai_id][$pai_bid];
				$new_pi_list[$pa_id][$pai_bid][$rule_num] = $new_pi_id;*/
			}
		}
		
		if($config['enable_gst'] && $po_items_list){
			foreach($po_items_list as $k => $r){
				// check branch is under gst
				if($branch_is_under_gst){
					$output_gst = get_sku_gst("output_tax", $r['sku_item_id']);
					$r['selling_gst_id'] = $output_gst['id'];
					$r['selling_gst_code'] = $output_gst['code'];
					$r['selling_gst_rate'] = $output_gst['rate'];
				
					$prms = array();
					$prms['selling_price'] = $r['selling_price'];
					$inclusive_tax = get_sku_gst("inclusive_tax", $r['sku_item_id']);
					$prms['inclusive_tax'] = $inclusive_tax;
					$prms['gst_rate'] = $r['selling_gst_rate'];
					$gst_sp_info = calculate_gst_sp($prms);
					$r['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					
					if($inclusive_tax == "yes"){
						$r['gst_selling_price'] = $r['selling_price'];
						$r['selling_price'] = $gst_sp_info['gst_selling_price'];
					}else{
						$r['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					}
				}
				
				if($po['is_under_gst']){
					// if found got set special vendor gst code, then all items must default choose it
					if($vendor_gst_list[$po['vendor_id']]['gst_register'] > 0){
						$vd_gst = $vendor_gst_list[$form['vendor_id']]['gst_register'];
						foreach($input_gst_list as $tmp_gst_info){
							if($tmp_gst_info['id'] == $vd_gst){
								$r['cost_gst_id'] = $tmp_gst_info['id'];
								$r['cost_gst_code'] = $tmp_gst_info['code'];
								$r['cost_gst_rate'] = $tmp_gst_info['rate'];
								break;
							}
						}
					}else{ // check to get cost GST info
						$input_gst = get_sku_gst("input_tax", $r['sku_item_id']);
						if($input_gst){
							$r['cost_gst_id'] = $input_gst['id'];
							$r['cost_gst_code'] = $input_gst['code'];
							$r['cost_gst_rate'] = $input_gst['rate'];
						}else{
							$r['cost_gst_id'] = $input_gst_list[0]['id'];
							$r['cost_gst_code'] = $input_gst_list[0]['code'];
							$r['cost_gst_rate'] = $input_gst_list[0]['rate'];
						}
					}
				}

				$po_items_list[$k] = $r;
			}
		}
		//print_r($po_items_list);exit;
		
		// insert foc items
		if($form['item_check']['foc_item']){
			foreach($form['item_check']['foc_item'] as $pai_id=>$pai_bid_list){
				foreach($pai_bid_list as $r=>$pai_bid){
					$sid = mi($form['sku_item_id']['foc_item'][$pai_id][$pai_bid]);
					$pa_id = $form['pa_id']['foc_item'][$pai_id][$pai_bid];
					
					if(!$sid)	continue;
					
					$qty = $form['qty']['foc_item'][$pai_id][$pai_bid];
					$selling_price = $form['suggest_selling_price']['foc_item'][$pai_id][$pai_bid];
					$order_price = $form['purchase_price']['foc_item'][$pai_id][$pai_bid];
					
					if(!isset($si_info_list[$sid])){
						$si_info_list[$sid] = $this->load_si_info($po['branch_id'], $sid);
					}
					
					// get the sku items info
					$si_info = $si_info_list[$sid];
				
					$need_add = true;
					
					// only go to retrieve sku info if got bom package
					if($config['sku_bom_additional_type']){
						// is bom package sku
						if($si_info['is_bom'] && $si_info['bom_type']=='package'){							
							$bom_ref_num = 0;
							
							// find bom ref num
							if($pa_to_bom_ref_num[$pai_bid][$pa_id][$sid]){
								foreach($form['ref_rule_num']['foc_item'][$pai_id][$pai_bid] as $tmp_rule){
									if($pa_to_bom_ref_num[$pai_bid][$pa_id][$sid][$tmp_rule]){
										$bom_ref_num = $pa_to_bom_ref_num[$pai_bid][$pa_id][$sid][$tmp_rule];
										break;
									}
								}
							}
							if(!$bom_ref_num)	$bom_ref_num++;
							
							// get item list in bom sku
							$q_bi = $con->sql_query("select bi.sku_item_id as sid,bi.qty from bom_items bi where bi.bom_id=".mi($sid)." order by bi.sku_item_id");
							while($r = $con->sql_fetchassoc($q_bi)){
								$tmp_sid = mi($r['sid']);
									
								$pcs = $r['qty'] * $qty;	// multiply bom sku qty
								
								// load sku items info
								if(!isset($si_info_list[$tmp_sid]))	$si_info_list[$tmp_sid] = $this->load_si_info($po['branch_id'], $tmp_sid);
								
								$tmp_si_info = $si_info_list[$tmp_sid];
								$item_added_to_foc = false;
								// only 1 item foc 1, check whether got same item purchase
								if(count($form['ref_rule_num']['foc_item'][$pai_id][$pai_bid]) == 1){
									// get the ref rule
									$rule = $form['ref_rule_num']['foc_item'][$pai_id][$pai_bid][0];
									
									foreach($po_items_list as $key => $po_items){
										if($po_items['pa_branch_id'] == $pai_bid && $po_items['pa_id'] == $pa_id && $po_items['rule_num'] == $rule && $po_items['sku_item_id'] == $tmp_sid && $po_items['order_price'] == $tmp_si_info['items_detail']['order_price'] && $po_items['selling_price'] == $tmp_si_info['items_detail']['selling_price']){
											$po_items_list[$key]['foc_loose'] += $pcs;
											$item_added_to_foc = true;
											break;
										}
									}
								}
								if($item_added_to_foc)	continue;
								
								$tmp = array();
								$tmp['pa_foc_item_id'] = $pai_id;
								$tmp['pa_branch_id'] = $pai_bid;
						
								$tmp['sku_item_id'] = $tmp_sid;
								$tmp['foc_loose'] = $pcs;
								
								$tmp['selling_price'] = $tmp_si_info['items_detail']['selling_price'];
								$tmp['selling_uom_id'] = $uom['id'];
								$tmp['selling_uom_fraction'] = $uom['fraction'];
								
								$tmp['order_price'] = $tmp_si_info['items_detail']['order_price'];
								$tmp['order_uom_id'] = $uom['id'];
								$tmp['order_uom_fraction'] = $uom['fraction'];
								
								$tmp['cost_indicate'] = $tmp_si_info['items_detail']['cost_indicate'];
								
								$tmp['stock_balance'] = $tmp_si_info['stock_balance'];
								$tmp['sales_trend'] =  $tmp_si_info['sales_trend'];
								
								$tmp['is_foc'] = 1;
								$tmp['bom_ref_num'] = $bom_ref_num;
								$tmp['bom_qty_ratio'] = $r['qty'];
								
								// the value later need to delete before insert
								$tmp['ref_rule_num_list'] = $form['ref_rule_num']['foc_item'][$pai_id][$pai_bid];
								$tmp['pa_id'] = $pa_id;
								
								$po_foc_items_list[] = $tmp;
								unset($tmp);
							}
							$con->sql_freeresult($q_bi);
							$need_add = false;	// already add by bom list, no need add parent bom sku
						}
					}
				
					if($need_add){
						// only 1 item foc 1, check whether got same item purchase
						$item_added_to_foc = false;
						if(count($form['ref_rule_num']['foc_item'][$pai_id][$pai_bid]) == 1){
							// get the ref rule
							$rule = $form['ref_rule_num']['foc_item'][$pai_id][$pai_bid][0];
							
							foreach($po_items_list as $key => $po_items){
								if($po_items['pa_branch_id'] == $pai_bid && $po_items['pa_id'] == $pa_id && $po_items['rule_num'] == $rule && $po_items['sku_item_id'] == $sid && $po_items['order_price'] == $order_price && $po_items['selling_price'] == $selling_price){
									$po_items_list[$key]['foc_loose'] += $qty;
									$item_added_to_foc = true;
									break;
								}
							}
						}
						
						if($item_added_to_foc)	continue;
								
						$tmp = array();
						$tmp['pa_foc_item_id'] = $pai_id;
						$tmp['pa_branch_id'] = $pai_bid;
						
						$tmp['sku_item_id'] = $sid;
						$tmp['foc_loose'] = $qty;
						
						$tmp['selling_price'] = $selling_price;
						$tmp['selling_uom_id'] = $uom['id'];
						$tmp['selling_uom_fraction'] = $uom['fraction'];
						
						$tmp['order_price'] = $order_price;
						$tmp['order_uom_id'] = $uom['id'];
						$tmp['order_uom_fraction'] = $uom['fraction'];
						
						$tmp['cost_indicate'] = 'PA';
						
						$tmp['stock_balance'] = $si_info['stock_balance'];
						$tmp['sales_trend'] =  $si_info['sales_trend'];
						
						$tmp['is_foc'] = 1;
						
						// the value later need to delete before insert
						$tmp['ref_rule_num_list'] = $form['ref_rule_num']['foc_item'][$pai_id][$pai_bid];
						$tmp['pa_id'] = $pa_id;
						
						$po_foc_items_list[] = $tmp;
						unset($tmp);
					}
				}
			}
		}
		
		if($config['enable_gst'] && $po_foc_items_list){
			foreach($po_foc_items_list as $k => $r){
				// check branch is under gst
				if($branch_is_under_gst){
					$output_gst = get_sku_gst("output_tax", $r['sku_item_id']);
					$r['selling_gst_id'] = $output_gst['id'];
					$r['selling_gst_code'] = $output_gst['code'];
					$r['selling_gst_rate'] = $output_gst['rate'];
				
					$prms = array();
					$prms['selling_price'] = $r['selling_price'];
					$inclusive_tax = get_sku_gst("inclusive_tax", $r['sku_item_id']);
					$prms['inclusive_tax'] = $inclusive_tax;
					$prms['gst_rate'] = $r['selling_gst_rate'];
					$gst_sp_info = calculate_gst_sp($prms);
					$r['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					
					if($inclusive_tax == "yes"){
						$r['gst_selling_price'] = $r['selling_price'];
						$r['selling_price'] = $gst_sp_info['gst_selling_price'];
					}else{
						$r['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					}
				}
				
				if($po['is_under_gst']){
					// if found got set special vendor gst code, then all items must default choose it
					if($vendor_gst_list[$po['vendor_id']]['gst_register'] > 0){
						$vd_gst = $vendor_gst_list[$form['vendor_id']]['gst_register'];
						foreach($input_gst_list as $tmp_gst_info){
							if($tmp_gst_info['id'] == $vd_gst){
								$r['cost_gst_id'] = $tmp_gst_info['id'];
								$r['cost_gst_code'] = $tmp_gst_info['code'];
								$r['cost_gst_rate'] = $tmp_gst_info['rate'];
								break;
							}
						}
					}else{ // check to get cost GST info
						$input_gst = get_sku_gst("input_tax", $r['sku_item_id']);
						if($input_gst){
							$r['cost_gst_id'] = $input_gst['id'];
							$r['cost_gst_code'] = $input_gst['code'];
							$r['cost_gst_rate'] = $input_gst['rate'];
						}else{
							$r['cost_gst_id'] = $input_gst_list[0]['id'];
							$r['cost_gst_code'] = $input_gst_list[0]['code'];
							$r['cost_gst_rate'] = $input_gst_list[0]['rate'];
						}
					}
				}

				$po_foc_items_list[$k] = $r;
			}
		}
		//print_r($po_foc_items_list);exit;
					
		if($pa_data['items_info']){
			foreach($pa_data['items_info'] as $r){
				if($r['pa_item_id'])	continue;	// skip purchase agreement item
				
				$sid = mi($r['sku_item_id']);
				
				if(!$sid)	continue;
				
				$qty = $r['qty_loose'];
				$selling_price = $r['selling_price'];
				$order_price = $r['order_price'];
				
				if(!isset($si_info_list[$sid])){
					$si_info_list[$sid] = $this->load_si_info($po['branch_id'], $sid);
				}
				
				// get the sku items info
				$si_info = $si_info_list[$sid];
				
				$tmp = array();				
				$tmp['sku_item_id'] = $sid;
				$tmp['qty_loose'] = $qty;
				$tmp['selling_price'] = $selling_price;
				$tmp['selling_uom_id'] = $r['selling_uom_id'];
				$tmp['selling_uom_fraction'] = $r['selling_uom_fraction'];
				
				$tmp['order_price'] = $order_price;
				$tmp['order_uom_id'] = $r['order_uom_id'];
				$tmp['order_uom_fraction'] = $r['order_uom_fraction'];
				
				//$tmp['cost_indicate'] = 'PA';
				
				$tmp['stock_balance'] = $si_info['stock_balance'];
				$tmp['sales_trend'] =  $si_info['sales_trend'];
				
				$row_amt = $tmp['qty_loose'] * $tmp['order_price'];
				//if ($tmp['discount']){
				//	$disc_amount = get_discount_amt($row_amt, $tmp['discount']);
				//	$row_amt = round($row_amt - $disc_amount, 5);
				//}
				
				// check branch is under gst
				if($branch_is_under_gst){
					$output_gst = get_sku_gst("output_tax", $sid);
					$tmp['selling_gst_id'] = $output_gst['id'];
					$tmp['selling_gst_code'] = $output_gst['code'];
					$tmp['selling_gst_rate'] = $output_gst['rate'];
				
					$prms = array();
					$prms['selling_price'] = $tmp['selling_price'];
					$inclusive_tax = get_sku_gst("inclusive_tax", $sid);
					$prms['inclusive_tax'] = $inclusive_tax;
					$prms['gst_rate'] = $tmp['selling_gst_rate'];
					$gst_sp_info = calculate_gst_sp($prms);
					$tmp['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					
					if($inclusive_tax == "yes"){
						$tmp['gst_selling_price'] = $tmp['selling_price'];
						$tmp['selling_price'] = $gst_sp_info['gst_selling_price'];
					}else{
						$tmp['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					}
				}
				
				if($po['is_under_gst']){
					// if found got set special vendor gst code, then all items must default choose it
					if($vendor_gst_list[$po['vendor_id']]['gst_register'] > 0){
						$vd_gst = $vendor_gst_list[$form['vendor_id']]['gst_register'];
						foreach($input_gst_list as $tmp_gst_info){
							if($tmp_gst_info['id'] == $vd_gst){
								$tmp['cost_gst_id'] = $tmp_gst_info['id'];
								$tmp['cost_gst_code'] = $tmp_gst_info['code'];
								$tmp['cost_gst_rate'] = $tmp_gst_info['rate'];
								break;
							}
						}
					}else{ // check to get cost GST info
						$input_gst = get_sku_gst("input_tax", $sid);
						if($input_gst){
							$tmp['cost_gst_id'] = $input_gst['id'];
							$tmp['cost_gst_code'] = $input_gst['code'];
							$tmp['cost_gst_rate'] = $input_gst['rate'];
						}else{
							$tmp['cost_gst_id'] = $input_gst_list[0]['id'];
							$tmp['cost_gst_code'] = $input_gst_list[0]['code'];
							$tmp['cost_gst_rate'] = $input_gst_list[0]['rate'];
						}
					}
				}
		
				$total_po_amount += $row_amt;
				// the value later need to delete before insert
				
				$normal_items_list[] = $tmp;
				unset($tmp);
			}
		}
		/*if($form['item_check']['foc_item']){
			foreach($form['item_check']['foc_item'] as $pai_id=>$pai_bid_list){
				foreach($pai_bid_list as $r=>$pai_bid){		
					///// old /////
					$sku_item_id = $form['sku_item_id']['foc_item'][$pai_id][$pai_bid];
					$selling_price = $form['suggest_selling_price']['foc_item'][$pai_id][$pai_bid];
					$order_price = $form['purchase_price']['foc_item'][$pai_id][$pai_bid];
					$foc_loose = $form['qty']['foc_item'][$pai_id][$pai_bid];

					// found the only one rule required for pa item
					if(count($form['ref_rule_num']['foc_item'][$pai_id][$pai_bid]) == 1){
						$rule = $form['ref_rule_num']['foc_item'][$pai_id][$pai_bid][0];
						$pi_id = $new_pi_list[$pa_id][$pai_bid][$rule];
						if($pi_id){
							$q1 = $con->sql_query("select * from po_items where po_id = ".mi($po_id)." and branch_id = ".mi($po['branch_id'])." and sku_item_id = ".mi($sku_item_id)." and order_price = ".mf($order_price)." and selling_price = ".mf($selling_price)." and id = ".mi($pi_id));
							
							// found that system has similar item
							if($con->sql_numrows($q1) > 0){
								$con->sql_freeresult($q1);
								$con->sql_query("update po_items set foc_loose = ".mf($foc_loose)." where id = ".mi($pi_id)." and po_id = ".mi($po_id)." and branch_id = ".mi($po['branch_id']));
								continue;
							}
						}
					}
				
					$po_items = array();
					$po_items['branch_id'] = $po['branch_id'];
					$po_items['po_id'] = $po_id;
					$po_items['user_id'] = $po['user_id'];
					$po_items['sku_item_id'] = $sku_item_id;
					$po_items['selling_price'] = $selling_price;
					$po_items['selling_uom_id'] = $uom['id'];
					$po_items['order_uom_id'] = $uom['id'];
					$po_items['order_uom_fraction'] = $uom['fraction'];
					$po_items['order_price'] = $order_price;
					$po_items['selling_uom_fraction'] = $uom['fraction'];
					//$po_items['discount'] = $form['discount']['foc_item'][$pai_id][$pai_bid];
					$po_items['cost_indicate'] = "PA";
					$po_items['is_foc'] = 1;
					$po_items['pa_foc_item_id'] = $pai_id;
					$po_items['pa_branch_id'] = $pai_bid;
					
					$balance=get_stock_balance($po_items['sku_item_id']);
					$po_items['stock_balance'] = $balance['stock_balance'];
					
					// get sales trend
					$sales_trend = get_sales_trend($po_items['sku_item_id']);
					$po_items['sales_trend'] =  $sales_trend['sales_trend'];

					$po_items['foc_loose'] = $foc_loose;
					$po_amount += $po_items['foc_loose'] * $po_items['order_price'];

					$po_items['sales_trend'] = serialize($po_items['sales_trend']);
					
					// setup foc share cost...
					$pa_id = $form['pa_id']['foc_item'][$pai_id][$pai_bid];
					$foc_share_cost_list = array();
					foreach($form['ref_rule_num']['foc_item'][$pai_id][$pai_bid] as $r=>$rule){
						$pi_id = $new_pi_list[$pa_id][$pai_bid][$rule];
						if($pi_id) $foc_share_cost_list[$pi_id] = "on";
					}
					if($foc_share_cost_list) $po_items['foc_share_cost'] = serialize($foc_share_cost_list);
					
					$con->sql_query("insert into po_items ".mysql_insert_by_field($po_items));
				}
			}
		}*/
		
		//print_r($po_items_list);
		//print_r($po_foc_items_list);
		//exit;
		
		foreach($po_items_list as $key => $po_items){
			// unset the column which cannot be insert
			unset($po_items['rule_num'], $po_items['pa_id']);
			
			$po_items['branch_id'] = $po['branch_id'];
			$po_items['po_id'] = $po_id;
			$po_items['user_id'] = $po['user_id'];
			$po_items['sales_trend'] = serialize($po_items['sales_trend']);
					
			$con->sql_query("insert into po_items ".mysql_insert_by_field($po_items));
			$po_items_list[$key]['po_item_id'] = $con->sql_nextid();
		}
		
		if($po_foc_items_list){
			foreach($po_foc_items_list as $key => $po_foc_items){
				$foc_share_cost_list = array();
				
				foreach($po_foc_items['ref_rule_num_list'] as $rule){
					foreach($po_items_list as $key2 => $po_items){
						if($po_items['pa_id'] == $po_foc_items['pa_id'] && $po_items['rule_num'] == $rule && $po_items['pa_branch_id'] == $po_foc_items['pa_branch_id']){
							$foc_share_cost_list[$po_items['po_item_id']] = "on";
						}
					}
				}
				
				// unset the column which cannot be insert
				unset($po_foc_items['ref_rule_num_list'], $po_foc_items['pa_id']);
				
				$po_foc_items['foc_share_cost'] = $po_foc_items_list[$key]['foc_share_cost'] = serialize($foc_share_cost_list);
				$po_foc_items['branch_id'] = $po['branch_id'];
				$po_foc_items['po_id'] = $po_id;
				$po_foc_items['user_id'] = $po['user_id'];
				$po_foc_items['sales_trend'] = serialize($po_foc_items['sales_trend']);
				
				$con->sql_query("insert into po_items ".mysql_insert_by_field($po_foc_items));
			}
		}
		
		if($normal_items_list){
			foreach($normal_items_list as $r){
				$r['branch_id'] = $po['branch_id'];
				$r['po_id'] = $po_id;
				$r['user_id'] = $po['user_id'];
				$r['sales_trend'] = serialize($r['sales_trend']);
				
				$con->sql_query("insert into po_items ".mysql_insert_by_field($r));
			}
		}
		
		
		
		// update po amount
		//$con->sql_query("update po set po_amount=".ms($total_po_amount)." where branch_id=".mi($po['branch_id'])." and id=".mi($po_id));
		//$appCore->poManager->reCalcatePOUsingOldMethod($po['branch_id'], $po_id);
		$appCore->poManager->reCalcatePOAmt($po['branch_id'], $po_id);
		
		//log_br($sessioninfo['id'], 'Purchase Agreement', $id, "Purchase Order Saved (ID#$po_id)");
		header("Location: ".$_SERVER['PHP_SELF']."?type=save&id=$po_id");
	}
	
	private function load_si_info($bid, $sid){
		global $con, $appCore;
		
		$data = array();
		
		$con->sql_query("select sku.is_bom, si.bom_type
		from sku_items si
		join sku on sku.id=si.sku_id
		where si.id=$sid");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// get stock balance
		$balance=get_stock_balance($sid);
		$data['stock_balance'] = $balance['stock_balance'];
		unset($balance);
		
		// get sales trend
		//$sales_trend = get_sales_trend($sid);
		
		$data['sales_trend'] = $appCore->skuManager->getSKUSalesTrend($bid,$sid); // call it from skuManager.php
		//unset($sales_trend);
		
		// get item details
		$data['items_detail'] = get_items_detail($sid, $bid);
		
		return $data;
	}
	
	function create_from_tmp(){
		global $con;
		
		$bid = mi($_REQUEST['branch_id']);
		$id = mi($_REQUEST['tmp_pa_id']);
		
		// open using tmp_purchase_agreement_info id
		$this->open($bid, 0 , $id);
	}
	
	private function load_tmp_purchase_agreeement($branch_id, $tmp_pa_id){
		global $con;
		
		$con->sql_query("select * from tmp_purchase_agreement_info where branch_id=".mi($branch_id)." and id=".mi($tmp_pa_id));
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$data)	return;
		$data['header_info'] = unserialize($data['header_info']);
		$data['items_info'] = unserialize($data['items_info']);
		
		return $data;
	}
	
	private function process_tmp_pa_items($form, $pa_data, &$items){
		if(!$pa_data['items_info'] || !$items['item'])	return;
		
		foreach($pa_data['items_info'] as $tmp_po_items){
			if(!$tmp_po_items['pa_item_id'])	continue;
			foreach($items['item'] as $key => $r){
				if($tmp_po_items['pa_item_id'] == $r['id']){	// purchase agreement item id matched
					$items['item'][$key]['checked'] = 1;
					$items['item'][$key]['qty'] = $tmp_po_items['qty_loose'];
				}
			}
		}
	}
}

$PURCHASE_AGREEMENT = new PURCHASE_AGREEMENT('Purchase Agreement');
?>
