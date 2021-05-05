<?php
/*
6/27/2018 11:34 AM Justin
- Bug fixed on total amount is sum up double.

8/9/2018 12:00 PM Justin
- Added new function "load_grr_images".

9/3/2018 4:22 PM Justin
- Bug fixed on system showing wrong selling price when GRN is no longer under GST status.

11/22/2018 4:58 PM Justin
- Enhanced to get link_code from sku_items due to need it for printing purpose.

11/29/2018 5:29 PM Justin
- Enhanced to show Invoice Date beside of Invoice No.

12/21/2018 11:13 AM Justin
- Bug fixed on showing PHP warning errors.

1/15/2020 2:40 PM William
- Move function "update_sku_item_cost", "update_total_selling", "update_sku_vendor_history", "items_return_handler", "update_total_amount" to grnManager.

4/27/2020 3:12 PM Justin
- Enhanced to have "update_total_variance", "get_item_details" and "load_available_po_qty" functions that used for mobile app.
*/
class grnManager{
	// public var
	
	
	// private var
	
	function __construct(){
		global $smarty, $con, $appCore;

	
	}
	
	function print_grn_performance($grn_id, $branch_id){
		global $con, $smarty, $sessioninfo, $config;

		$form = $_REQUEST;
		
		$q1 = $con->sql_query("select * from branch where id=$branch_id");
		$smarty->assign("branch", $con->sql_fetchrow($q1));
		$con->sql_freeresult($q1);
		
		$q1=$con->sql_query("select grn.*, vendor.description as vendor, category.description as department, 
							 user.u, user2.u as acc_u  
							 from grn 
							 left join user on user_id = user.id 
							 left join user user2 on by_account = user2.id 
							 left join vendor on vendor_id = vendor.id 
							 left join category on grn.department_id = category.id 
							 where grn.id=".mi($grn_id)." and grn.branch_id=".mi($branch_id));
		$grn = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$grn['is_future']) $filter = " and grr_items.id = ".intval($grn['grr_item_id']);
		
		$sql = $con->sql_query("select grr_items.*, grr.*, vendor.*, grr.id as grr_id, grr_items.id as grr_item_id, 
								vendor.description as vendor, dept.grn_get_weight, dept.description as department, user.u, rcv.u as rcv_u, vendor.code as vendor_code,
								if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id
								from grr_items 
								left join grr on grr_items.grr_id = grr.id and grr_items.branch_id = grr.branch_id 
								left join user on grr.user_id = user.id 
								left join user rcv on grr.rcv_by = rcv.id 
								left join vendor on grr.vendor_id = vendor.id 
								left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = grr_items.branch_id
								left join category dept on grr.department_id = dept.id 
								where grr.branch_id = ".mi($branch_id)." and grr.id = ".intval($grn['grr_id']).$filter."
								order by grr_items.id");

		$doc_no_list = $doc_type_list = array();
		while($r1=$con->sql_fetchassoc($sql)){
			if(!$doc_no_list[$r1['type']][$r1['doc_no']]){
				if($r1['type'] == "INVOICE" && $r1['doc_date']) $doc_no = $r1['doc_no']." (".$r1['doc_date'].")";
				else $doc_no = $r1['doc_no'];
				$doc_no_list[$r1['type']][$r1['doc_no']] = $doc_no;
			}

			if(!in_array($r1['type'], $doc_type_list)){
				$doc_type_list[] = $r1['type'];
			}

			if (($r1['type']=='DO' || $r1['type']=='PO') && $r1['doc_no']!=''){
			//if ($r1['type']=='PO' && $r1['doc_no']!=''){
				if($config['do_skip_generate_grn']){
					if($sessioninfo['type'] == "franchise") $filter = "debtor_id = ".mi($sessioninfo['debtor_id']);
					else $filter = "do_branch_id = ".mi($branch_id);
					$q1 = $con->sql_query("select *, id as do_id from do where do_no = ".ms($r1['doc_no'])." and ".$filter);
					if($con->sql_numrows($q1) > 0){  // means is IBT DO
						$grr_do = $con->sql_fetchassoc($q3);
						$grr = array_merge($r1, $grr_do);
						if($grr_do['do_no']) $grp_do_no[] = ms($grr_do['do_no']);
						$is_from_do = true;
					}
					$con->sql_freeresult($q1);
				}
				
				if(!$is_from_do){
					// get additional PO information if po is not empty
					$q2=$con->sql_query('select po.*, po.remark as po_remark1, po.remark2 as po_remark2, 
										 branch_approval_history.flow_approvals,user.u as po_u 
										 from po 
										 left join user on po.user_id = user.id 
										 left join branch_approval_history on po.approval_history_id = branch_approval_history.id and branch_approval_history.branch_id = po.branch_id
										 where po_no = '.ms($r1['doc_no']));
					$grr_po = $con->sql_fetchassoc($q2);
					$con->sql_freeresult($q2);
					unset($grr_po['added']);
					if($grr_po['po_no']) $grp_po_no[] = ms($grr_po['po_no']);
					
					if(!$grr_po['partial_delivery']) $non_pd_po[] = $grr_po['po_no']; 
					
					$grr_po['sdiscount']=unserialize($grr_po['sdiscount']);
					$grr_po['rdiscount']=unserialize($grr_po['rdiscount']);
					$grr_po['po_remark1']=unserialize($grr_po['po_remark1']);
					$grr_po['po_remark2']=unserialize($grr_po['po_remark2']);
					$ttl_po_amt += $grr_po['po_amount'];
					// merge array
					$grr = array_merge($r1, $grr_po);
				}
			}else{
				$grr = $r1;
			}
		}
		$con->sql_freeresult($q1);

		$grr['doc_no'] = '';
		$grr['type'] = '';
		
		if($is_from_do){
			$grr['type'] = "DO";
			$grr['doc_no'] = join(", ", $doc_no_list['DO']);
			$grr['is_ibt_do'] = true;
		}elseif(in_array("PO", $doc_type_list)){
			$grr['type'] = "PO";
			$grr['doc_no'] = join(", ", $doc_no_list['PO']);
		}elseif(!$grr['doc_no'] && in_array("INVOICE", $doc_type_list)){
			$grr['type'] = "INVOICE";
			$grr['doc_no'] = join(", ", $doc_no_list['INVOICE']);
		}elseif(!$grr['doc_no'] && in_array("DO", $doc_type_list)){
			$grr['type'] = "DO";
			$grr['doc_no'] = join(", ", $doc_no_list['DO']);
		}else{
			$grr['type'] = "OTHER";
			$grr['doc_no'] = join(", ", $doc_no_list['OTHER']);
		}
		
		if($is_from_do || $grr['type'] == "PO"){
			if($doc_no_list['INVOICE']) $grr['invoice_no'] = join(", ", $doc_no_list['INVOICE']);
		}
		
		if($config['grn_summary_show_related_invoice'] && $grr['type'] == "PO"){
			$grr['related_invoice'] = join(", ", $doc_no_list['INVOICE']);
		}
		
		$price_date = date("Y-m-d",strtotime("+1 day",strtotime($grr['rcv_date'])));
		$grn['price_date']=$price_date;

		$items = array();
		
		if($config['grn_future_report_order_sequence_by_po']) $order_by = "if(grn_items.po_item_id > 0, grn_items.po_item_id, 100000000+grn_items.id), grn_items.item_group,";
		
		$rs1 = $con->sql_query("select grn_items.*, if(grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *u1.fraction + grn_items.pcs, grn_items.acc_ctn *u1.fraction + grn_items.acc_pcs) as qty , round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/u1.fraction, ".mi($config['global_cost_decimal_points']).") as grn_cost, sku_items.mcode, sku_items.sku_item_code, sku_items.description, sku_items.additional_description, u1.code as order_uom, u2.code as sell_uom, u1.fraction as uom_fraction, u2.fraction as selling_uomf, sku_items.artno, grn_items.selling_price/u2.fraction as grn_price, sku_items.selling_price as master_price, if(grn_items.acc_gst_id is null, grn_items.gst_rate, grn_items.acc_gst_rate) as gst_rate, if(grn_items.acc_gst_id is null, grn_items.gst_code, grn_items.acc_gst_code) as gst_code, grn_items.gst_selling_price/u2.fraction as gst_selling_price, sku_items.link_code
								from grn_items 
								left join sku_items on grn_items.sku_item_id=sku_items.id 
								left join uom u1 on grn_items.uom_id=u1.id 
								left join uom u2 on grn_items.selling_uom_id=u2.id 
								where grn_id = ".mi($grn['id'])." and grn_items.branch_id = ".mi($grn['branch_id'])."
								order by $order_by grn_items.id") or die(mysql_error());

		while($r=$con->sql_fetchassoc($rs1)){
			//get selling price for GRN
			$query2=$con->sql_query("select siph.price as curr_selling_price
									from sku_items_price_history siph
									left join sku_items on sku_items.id=sku_item_id
									where sku_item_id = ".mi($r['sku_item_id'])." and siph.branch_id = ".mi($branch_id)." and siph.added < ".ms($price_date)." order by siph.added desc limit 1");

			$r2=$con->sql_fetchassoc($query2);		
			$con->sql_freeresult($query2);
			
			$prms = array();
			$prms['branch_id'] = $branch_id;
			$prms['date'] = $grn['added'];
			$grn['branch_is_under_gst'] = check_gst_status($prms);
			   
			if($grn['branch_is_under_gst'] || $grn['is_under_gst']){
				$need_recalc_price = false;
				// branch no gst, need recalc to make grn_price as gst_selling_price
				if(!$grn['branch_is_under_gst'] && $grn['is_under_gst']){
					$need_recalc_price = true;
				}
				// no gst_selling_price, need recalc
				if(!$r['gst_selling_price']){
					$need_recalc_price = true;
				}
				
				// if already have gst_selling_price, no need do further calculation
				if($need_recalc_price){
					$inclusive_tax = get_sku_gst("inclusive_tax", $r['sku_item_id']);
					$output_tax = get_sku_gst("output_tax", $r['sku_item_id']);
						
					if($r['grn_price']){ // sp from GRN
						if($grn['branch_is_under_gst'] && $grn['is_under_gst']){
							$inclusive_tax = "no";      //direct skip, because all price have been calculated
							// should not reach here
						}elseif(!$grn['branch_is_under_gst'] && $grn['is_under_gst']) {
							// make grn_price as gst_selling_price
							$r['grn_price'] = $r['gst_selling_price'];
							$inclusive_tax = "no";      //skip calculate gst, since branch gst is 0
							$need_recalc_price = false;												
						}
					}else {
						if($r2['curr_selling_price']) $r['grn_price'] = $r2['curr_selling_price'];
						else $r['grn_price'] = $r['master_price'];
					}
					
					if($need_recalc_price){
						$prms = array();
						$prms['selling_price'] = $r['grn_price'];
						$prms['inclusive_tax'] = $inclusive_tax;
						$prms['gst_rate'] = $output_tax['rate'];
						$gst_sp_info = calculate_gst_sp($prms);
						   
						if($inclusive_tax == "yes") {
							$r['gst_selling_price'] = $r['grn_price'];
							$r['grn_price'] = round($gst_sp_info['gst_selling_price'], 2);                        
						}else {
							$r['grn_price'] = $r['grn_price'];
							$r['gst_selling_price'] = round($gst_sp_info['gst_selling_price'], 2);
						}
					}				
				}
			}else{
				if($r['gst_selling_price']) $r['grn_price'] = $r['gst_selling_price'];
				elseif($r2['curr_selling_price']) $r['grn_price'] = $r2['curr_selling_price'];
				else $r['grn_price'] = $r['master_price'];
			}
			   
			$r['total_cost']=$r['grn_cost']*$r['qty'];
			
			if(!$temp[$r['sku_item_code']])
				$temp[$r['sku_item_code']]=$r;
			else{
				$temp[$r['sku_item_code']]['qty']=$items[$r['sku_item_code']]['qty']+$r['qty'];
				$temp[$r['sku_item_code']]['total_cost']=$items[$r['sku_item_code']]['total_cost']+$r['total_cost'];
				if($temp[$r['sku_item_code']]['total_cost'] && $temp[$r['sku_item_code']]['qty'])
					$temp[$r['sku_item_code']]['grn_cost']=$temp[$r['sku_item_code']]['total_cost']/$temp[$r['sku_item_code']]['qty'];
				else $temp[$r['sku_item_code']]['grn_cost'] = 0;
			}		
			$items = $temp;
		}
		$con->sql_freeresult($rs1);

		//IF FROM PO GET THE FOC.
		if($grr['type']=='PO'){
			if(strpos($grr['doc_no'], ",") == true){
				$splt_doc_no = explode(",", $grr['doc_no']);
				for($i=0; $i<count($splt_doc_no); $i++){
					$splt_doc_no[$i] = trim($splt_doc_no[$i]); 
				}
				$doc_no = join("','",$splt_doc_no);
			}else $doc_no = $grr['doc_no'];
		
			$q0=$con->sql_query("select if(po_items.foc is null, sum(po_items.foc_loose),sum(po_items.foc))*uom.fraction as po_foc, sku_items.sku_item_code, po.po_no, po.partial_delivery
								 from po_items 
								 left join po on po.id=po_items.po_id and po.branch_id=po_items.branch_id 
								 left join uom on uom.id=po_items.order_uom_id 
								 left join sku_items on sku_items.id=sku_item_id 
								 where po_no in ('$doc_no') 
								 group by po_items.id");
			$non_pd_po = array();
			while ($r0=$con->sql_fetchassoc($q0)){
				if(!$r0['po_foc'])	continue;
				$items[$r0['sku_item_code']]['po_foc']=abs($r0['po_foc']);
				if(!$r0['partial_delivery']){
					if(strpos(join(",", $non_pd_po), "$r0[po_no]") === false) $non_pd_po[] = $r0['po_no'];
				}
			}
			$con->sql_freeresult($q0);
		}

		if($non_pd_po) $grr['pd_po'] = join(",", $non_pd_po);

		if ($items)
			$where =" sku_item_code in ('" . join("','", array_keys($items)) . "')";
		else
			die("Items in this GRN are invalid");
		
		//FROM POS
		$q3 = $con->sql_query("select si.sku_item_code, sum(qty) as sold_qty from
	sku_items_sales_cache_b".$branch_id." tbl
	left join sku_items si on si.id=tbl.sku_item_id
	where tbl.date>=".ms($grr['rcv_date'])." and $where group by si.sku_item_code");
		while ($r3=$con->sql_fetchrow($q3)){
			$pos_qty[$r3['sku_item_code']]=$r3;
		}	
		$con->sql_freeresult($q3);

		//FROM DO
		$q4=$con->sql_query("select sku_item_code, sum(do_items.ctn *uom.fraction + do_items.pcs) as qty  
							from do_items 
							left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
							left join sku_items on sku_item_id = sku_items.id 
							left join uom on do_items.uom_id=uom.id
							where $where and do_items.branch_id = ".mi($branch_id)." and do.approved and do.checkout and do.status<2 and do_date >= ".ms($grr['rcv_date'])." 
							group by sku_item_code", false, false);
		while ($r4=$con->sql_fetchassoc($q4)){
			$do_qty[$r4['sku_item_code']]=$r4;
		}
		$con->sql_freeresult($q4);	
			
		$smarty->assign("grn", $grn);
		$smarty->assign("pos_qty", $pos_qty);
		$smarty->assign("do_qty", $do_qty);
		$smarty->assign("grr", $grr);
		
		$item_per_page= $config['grn_report_print_item_per_page']?$config['grn_report_print_item_per_page']:23;
		$item_per_lastpage = $config['grn_report_print_item_last_page']>0 ? $config['grn_report_print_item_last_page'] : $item_per_page-5;

		$totalpage = 1 + ceil((count($items)-$item_per_lastpage)/$item_per_page);
		
		$item_index = -1;
		$item_no = -1;
		$page = 1;
		
		$page_item_list = array();
		$page_item_info = array();
		
		//print_r($items);
		foreach($items as $r){	// loop for each item
			if($item_index+1>=$item_per_page){
				$page++;
				$item_index = -1;
			}
			
			$item_no++;
			$item_index++;
			$r['item_no'] = $item_no;
			
			$page_item_list[$page][$item_index] = $r;	// add item to this page
			
			if($config['sku_enable_additional_description'] && $r['additional_description']){
				$r['additional_description'] = unserialize($r['additional_description']);
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
		if(count($page_item_list[$page]) > $item_per_lastpage){
			$page++;
			$page_item_list[$page] = array();
		}
		
		$totalpage = count($page_item_list);
		
		// print from GRN may print 2 reports at the same time, so need to repeat
		if($form['print_grn_perform_report']){
			// reset all total
			$smarty->assign("total_qty", 0);
			$smarty->assign("total_sold_sell", 0);
			$smarty->assign("total_sold_cost", 0);
			$smarty->assign("total_sell", 0);
			$smarty->assign("total_cost", 0);
			$smarty->assign("total_bal", 0);
			$smarty->assign("total_bal_qty", 0);
			$smarty->assign("total_gst", 0);
			$smarty->assign("total_gst_cost", 0);
			$smarty->assign("total_bal_qty", 0);
			$smarty->assign("total_gst_sell", 0);

			foreach($page_item_list as $page => $item_list){
				$this_page_num = ($page < $totalpage) ? $item_per_page : $item_per_lastpage;
				$smarty->assign("is_last_page", ($page >= $totalpage));
				$smarty->assign("page", "Page $page of $totalpage");
				$smarty->assign("start_counter",$item_list[0]['item_no']);
				$smarty->assign("PAGE_SIZE", $this_page_num);
				$smarty->assign("grn_items", $item_list);
				$smarty->assign("page_item_info", $page_item_info[$page]);
				if($config['grn_perform_alt_print_template'])   $smarty->display($config['grn_perform_alt_print_template']);
				else $smarty->display('goods_receiving_note.perform_print.tpl');
				$smarty->assign("skip_header",1);
			}
		}

		if($form['print_gra_report']){
			// reset all total
			$smarty->assign("total_qty", 0);
			$smarty->assign("total_sold_sell", 0);
			$smarty->assign("total_sold_cost", 0);
			$smarty->assign("total_sell", 0);
			$smarty->assign("total_cost", 0);
			$smarty->assign("total_bal", 0);
			$smarty->assign("total_bal_qty", 0);
			$smarty->assign("total_gst", 0);
			$smarty->assign("total_gst_cost", 0);
			$smarty->assign("total_bal_qty", 0);
			$smarty->assign("total_gst_sell", 0);

			$smarty->assign("is_gra", 1);
			foreach($page_item_list as $page => $item_list){
				$this_page_num = ($page < $totalpage) ? $item_per_page : $item_per_lastpage;
				$smarty->assign("is_last_page", ($page >= $totalpage));
				$smarty->assign("page", "Page $page of $totalpage");
				$smarty->assign("start_counter",$item_list[0]['item_no']);
				$smarty->assign("PAGE_SIZE", $this_page_num);
				$smarty->assign("grn_items", $item_list);
				$smarty->assign("page_item_info", $page_item_info[$page]);
				if($config['grn_perform_alt_print_template'])   $smarty->display($config['grn_perform_alt_print_template']);
				else $smarty->display('goods_receiving_note.perform_print.tpl');
				$smarty->assign("skip_header",1);
			}
		}
	}
	
	function load_grr_images($prms=array()){
		global $smarty, $sessioninfo;
		
		if(is_array($prms) && $prms['is_grn'] == true){
			$grr_id = $prms['grr_id'];
			$branch_id = $prms['branch_id'];
		}else{
			$grr_id = isset($_REQUEST["grr_id"])?$_REQUEST["grr_id"]:$_REQUEST["id"];
			$branch_id = (isset($_REQUEST["branch_id"])? $_REQUEST["branch_id"]:$sessioninfo["branch_id"]);
		}
		
		if(isset($_REQUEST["tmp"]) && $_REQUEST["tmp"] != ""){
			$tmp_time = $_REQUEST["tmp"];
		}else{
			$tmp_time = strtotime(date("Y-m-d H:i:s"));	
		}
			
		if($_REQUEST["t"] == ""){
			$real_dir_path = $_SERVER['DOCUMENT_ROOT']."/attch/grr/$branch_id/$grr_id";
			$tmp_dir_path = $_SERVER['DOCUMENT_ROOT']."/attch/grr/tmp/" . $sessioninfo["branch_id"] ."/" . $sessioninfo["id"] ."/" . $tmp_time;

			if($_REQUEST["tmp"] == ""){
				if(file_exists($real_dir_path)){
						
					check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/attch/grr");
					check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/attch/grr/tmp");
					check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/attch/grr/tmp/" . $sessioninfo["branch_id"]);
					check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/attch/grr/tmp/" . $sessioninfo["branch_id"] . "/" . $sessioninfo["id"]);
					check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/attch/grr/tmp/" . $sessioninfo["branch_id"] . "/" . $sessioninfo["id"] ."/" . $tmp_time);
					check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/attch/grr/tmp/" . $sessioninfo["branch_id"] . "/" . $sessioninfo["id"] ."/" . $tmp_time . "/pdf");
					
					$have_files = glob($real_dir_path."/*.{jpg,JPG,jpeg,JPEG,png,PNG,pdf,PDF}", GLOB_BRACE);
					
					if(!empty($have_files)){
						$files = scandir($real_dir_path);
						foreach($files as $file){
							if($file != "." && $file != ".."){
								if(!is_dir($real_dir_path."/".$file)){
									$tmp_arr = array();
									$have_pdf_files =  glob($real_dir_path . "/pdf/" . str_replace(".jpg", "", $file) . ".{pdf,PDF}", GLOB_BRACE);
									if($have_pdf_files){
										$tmp_files = scandir($real_dir_path. "/pdf");
										foreach($tmp_files as $tmp_file){
											if($tmp_file != "." && $tmp_file != ".."){
												if(str_replace(".jpg", "", $file) == str_replace(".pdf", "", $tmp_file)){
													copy($real_dir_path."/pdf/" . $tmp_file, $tmp_dir_path."/pdf/".$tmp_file);
													$tmp_arr["download_file"] = str_replace("$_SERVER[DOCUMENT_ROOT]/", "", $tmp_dir_path."/pdf/".$tmp_file);
												}
											}
										}
									}
									copy($real_dir_path."/".$file, $tmp_dir_path."/".$file);
									$tmp_arr["image_file"] = $f = str_replace("$_SERVER[DOCUMENT_ROOT]/", "", $tmp_dir_path."/".$file);
									if(!isset($tmp_arr["download_file"])) $tmp_arr["download_file"] = str_replace("$_SERVER[DOCUMENT_ROOT]/", "", $tmp_dir_path."/".$file);
									$pitems[] = $tmp_arr;
								}
							}
						}
						$smarty->assign("photo_items", $pitems);
					}
				}
			}
		}
		$smarty->assign("tmp", $tmp_time);
	}
	
	function update_total_selling($id, $bid){
		global $con,$config;

		//print "<li> updating $bid,$id";
		$total_sell = 0;
		$return_pcs="";
		if(!$config['use_grn_future_allow_generate_gra']) $return_pcs=" - (ifnull(grn_items.return_ctn * rcv_uom.fraction,0) + ifnull(grn_items.return_pcs,0))";

		$con->sql_query("select sum((if(grn_items.acc_ctn is null and grn_items.acc_pcs is null, ifnull(grn_items.ctn,0) * rcv_uom.fraction + ifnull(grn_items.pcs,0), ifnull(grn_items.acc_ctn,0) * rcv_uom.fraction + ifnull(grn_items.acc_pcs,0))$return_pcs) * grn_items.selling_price / sell_uom.fraction) as sell
						 from grn_items
						 left join uom sell_uom on grn_items.selling_uom_id=sell_uom.id
						 left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
						 where grn_id = ".mi($id)." and branch_id = ".mi($bid)." and item_check=0
						 group by grn_items.id") or die(mysql_error());

		while($r = $con->sql_fetchassoc()){
			$total_sell += round($r['sell'], 2);
		}
		$con->sql_freeresult();
		
		//if ($t[0]==0) return;

		$con->sql_query("update grn set last_update=last_update,total_selling=".mf($total_sell)." where id = ".mi($id)." and branch_id = ".mi($bid));
	}
	
	function update_total_amount($id, $bid){
		global $con,$config;

		//print "<li> updating $bid,$id";
		$return_pcs="";
		$return_pcs="(ifnull(grn_items.return_ctn * rcv_uom.fraction,0) + ifnull(grn_items.return_pcs,0))";

		$grn_tax = $ttl_gross_amt = $ttl_gst_amt = $ttl_amt = 0;
		$q1 = $con->sql_query("select rcv_uom.fraction, grn.grn_tax, grn_items.gst_rate, grn.is_under_gst,
							 if(grn_items.acc_ctn is null and grn_items.acc_pcs is null, (grn_items.ctn * rcv_uom.fraction) + grn_items.pcs, (grn_items.acc_ctn * rcv_uom.fraction) + grn_items.acc_pcs) as rcv_qty,
							 $return_pcs as returned_qty,
							 if(grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost) as cost_price
							 from grn_items
							 left join grn on grn.id = grn_items.grn_id and grn.branch_id = grn_items.branch_id
							 left join uom sell_uom on grn_items.selling_uom_id=sell_uom.id
							 left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
							 where grn_items.grn_id = ".mi($id)." and grn_items.branch_id = ".mi($bid)." and grn_items.item_check=0 
							 group by grn_items.id") or die(mysql_error());
		
		while($r = $con->sql_fetchassoc($q1)){
			if($r['grn_tax']) $grn_tax = $r['grn_tax'];
			$qty = (!$config['use_grn_future_allow_generate_gra'])?(($r['rcv_qty'] - $r['returned_qty']) / $r['fraction']):($r['rcv_qty'] / $r['fraction']);

			$item_gross_amt = round($qty * $r['cost_price'], 2);
			/* // grn_tax will adjust into the total grn amount instead of item amount
			$item_gross_tax_amt = round($item_gross_amt * $r['grn_tax'] / 100, 2);
			$item_gross_amt += $item_gross_tax_amt;*/
			
			$ttl_gross_amt += $item_gross_amt;
			
			//if found it is under gst
			if($r['is_under_gst']){
				$item_gst_amt = round($qty * $r['cost_price'] * $r['gst_rate'] / 100, 2);
				$item_amt = round($qty * $r['cost_price'], 2) + $item_gst_amt;
				
				/* // grn_tax will adjust into the total grn amount instead of item amount
				$item_gst_tax_amt = round($item_gst_amt * $r['grn_tax'] / 100, 2);
				$item_tax_amt = round($item_amt * $r['grn_tax'] / 100, 2);
				$item_gst_amt += $item_gst_tax_amt;
				$item_amt += $item_tax_amt;*/
			
				$ttl_gst_amt += $item_gst_amt;
				$ttl_amt += $item_amt;
			}
			
		}
		$con->sql_freeresult($q1);
		//if ($t[0]==0) return;
		
		// calculate grn tax for gross amount, gst and total amount
		$ttl_gross_tax_amt = $ttl_gst_tax_amt = $ttl_tax_amt = 0;
		if($grn_tax){
			$ttl_gross_tax_amt = round($ttl_gross_amt * $grn_tax / 100, 2);
			$ttl_gst_tax_amt = round($ttl_gst_amt * $grn_tax / 100, 2);
			$ttl_tax_amt = round($ttl_amt * $grn_tax / 100, 2);
			$ttl_gross_amt += $ttl_gross_tax_amt;
			$ttl_gst_amt += $ttl_gst_tax_amt;
			$ttl_amt += $ttl_tax_amt;
		}

		$con->sql_query("update grn set last_update=last_update,amount=".mf($ttl_gross_amt).",tax=".mf($ttl_gross_tax_amt)." where id = ".mi($id)." and branch_id = ".mi($bid));

		$ret = array();
		$ret['ttl_gross_amt'] = $ttl_gross_amt;
		$ret['ttl_gst_amt'] = $ttl_gst_amt;
		$ret['ttl_amt'] = $ttl_amt;
		return $ret;
	}
	
	function update_sku_vendor_history($grn_id, $branch_id){
		global $con, $config;

		$con->sql_query("delete from vendor_sku_history where branch_id = ".mi($branch_id)." and source= 'GRN' and ref_id = ".mi($grn_id));

		$sql = "select 
		grn.branch_id, grn.vendor_id, gi.sku_item_id, gi.selling_price, round(if (gi.acc_cost, gi.acc_cost, gi.cost)/uom.fraction, ".mi($config['global_cost_decimal_points']).") as cost_price, 'GRN' as source, grn.id as ref_id, gi.artno_mcode as artno, grn.added, ifnull(gi.acc_ctn, gi.ctn) as ctn, ifnull(gi.acc_pcs, gi.pcs) as pcs
		from grn_items gi
		left join grn on gi.grn_id = grn.id and gi.branch_id = grn.branch_id 
		left join uom on uom_id = uom.id 
		where grn.id = ".mi($grn_id)." and grn.branch_id = ".mi($branch_id)." and gi.selling_price > 0
		having cost_price > 0 and (ctn > 0 or pcs > 0)";
		$q_1 = $con->sql_query($sql);

		while($r = $con->sql_fetchrow($q_1)){
			$con->sql_query("insert into vendor_sku_history ".mysql_insert_by_field($r, array('branch_id', 'vendor_id', 'sku_item_id', 'selling_price', 'cost_price', 'source', 'ref_id', 'artno', 'added')));

		}

		$con->sql_freeresult($q_1);
	}
	
	function update_sku_item_cost($id,$branch_id){
		global $con;

		//$con->sql_query("update sku_items_cost set changed=1 where branch_id = ".mi($branch_id)." and sku_item_id in (select sku_item_id from grn_items left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id where grn_items.grn_id = ".mi($id)." and grn_items.branch_id = ".mi($branch_id)." and grn.authorized=1 and grn.approved=1 and grn.status<2 and grn.active=1)");
		
		//update sku_item_cost
		$sql = "select distinct(sku_item_id) as sid
		from grn_items
		left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
		where grn_items.grn_id = ".mi($id)." and grn_items.branch_id = ".mi($branch_id);
		$q1 = $con->sql_query($sql);
		$sid_list = array();
		while($r = $con->sql_fetchrow($q1)){
			$sid_list[] = mi($r['sid']);
			if(count($sid_list)>=1000){ // maximum 1000 items per query
				$con->sql_query("update sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',',$sid_list).")");
				$sid_list = array();
			}
		}
		if($sid_list){ // still got some items need to update
			$con->sql_query("update sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',',$sid_list).")");
		}
	}
	
	function items_return_handler($grn_id, $branch_id){
		global $con, $sessioninfo, $config;
		
		$con->sql_query("select grn.*, grr.rcv_date, grr.currency_code, grr.currency_rate
			from grn 
			left join grr on grr.branch_id=grn.branch_id and grr.id=grn.grr_id
			where grn.id = ".mi($grn_id)." and grn.branch_id = ".mi($branch_id));
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();

		if(!$form)	return;

		if($config['enable_gst']){
			$prms = array();
			$prms['branch_id'] = $branch_id;
			$prms['date'] = date("Y-m-d");
			$form['branch_is_under_gst'] = check_gst_status($prms);
		}
		
		// select "NR" gst info
		$q1 = $con->sql_query("select * from gst where code = 'NR' and type = 'purchase'");
		$gst_nr_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		// need to select one invoice information for gra usage
		if($form['is_under_gst']){
			$q1 = $con->sql_query("select *, case when type = 'INVOICE' then 1 when type = 'DO' then 2 else 3 end as type_asc from grr_items where grr_id = ".mi($form['grr_id'])." and branch_id = ".mi($form['branch_id'])." and type != 'PO' and doc_date != '' and doc_date is not null order by type_asc limit 1");
			$grri_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			// need to select default gst code OP if gst from GST couldn't be found 
			if(!$grri_info){
				$q1 = $con->sql_query("select id as gst_id, code as gst_code, rate as gst_rate from gst where code = 'OP' and type = 'purchase'");
				$grri_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}
		}
		
		$generate_gra = $form['generate_gra'];
		$non_sku_items = $form['non_sku_items'];
		$nsi = array();

		$q1 = $con->sql_query("select gi.*, si.sku_item_code, si.description, u.fraction, ifnull(sip.price, si.selling_price) as latest_selling_price, sku.sku_type,
							   gi.gst_id, gi.gst_code, gi.gst_rate
							   from grn_items gi
							   left join grn on grn.id = gi.grn_id and grn.branch_id = gi.branch_id
							   left join sku_items si on si.id = gi.sku_item_id
							   left join sku on sku.id=si.sku_id
							   left join uom u on u.id = gi.uom_id
							   left join sku_items_price sip on sip.branch_id=gi.branch_id and sip.sku_item_id=gi.sku_item_id
							   where gi.grn_id = ".mi($grn_id)." and gi.branch_id = ".mi($branch_id)." 
							   and (gi.item_check = 1 or gi.return_pcs > 0 or gi.return_ctn > 0)");
		
		// use to hold the item list to generate to gra
		$gra_item_list = array();
		$extra_amt = 0;

		while($r = $con->sql_fetchassoc($q1)){
			if($r['item_group'] == 1 || $r['item_group'] == 2){
				$qty = doubleval($r['return_ctn'] * $r['fraction'] + $r['return_pcs']);
			}else{
				$qty = doubleval($r['ctn'] * $r['fraction'] + $r['pcs']);
			}
			$pcs_cost = round($r['cost'] / $r['fraction'], $config['global_cost_decimal_points']);
			
			if($generate_gra){
				// need to generate to gra
				$tmp = array();
				$tmp['gra_item_id'] = $r['id'];
				$tmp['sku_item_id'] = $r['sku_item_id'];
				$tmp['qty'] = $qty;
				$tmp['cost'] = $pcs_cost;
				$tmp['latest_selling_price'] = $r['latest_selling_price'];
				if($form['is_under_gst']){
					$tmp['gst_id'] = $r['gst_id'];
					$tmp['gst_code'] = $r['gst_code'];
					$tmp['gst_rate'] = $r['gst_rate'];
				}
				$gra_item_list[$r['sku_type']][] = $tmp;	// by sku type
			}else{
				// no generate to gra, store to non sku items
				$nsi['code'][] = $r['sku_item_code'];
				$nsi['description'][] = $r['description'];
				$nsi['qty'][] = $qty;
				$nsi['cost'][] = $pcs_cost;
				$nsi['i_c'][] = 1;
		
				if($form['is_under_gst']){				
					$nsi['gst_id'][] = $r['gst_id'];
					$nsi['gst_code'][] = $r['gst_code'];
					$nsi['gst_rate'][] = $r['gst_rate'];
					if($grri_info){
						$nsi['doc_no'][] = $grri_info['doc_no'];
						$nsi['doc_date'][] = $grri_info['doc_date'];
					}
				}
				$nsi['reason'][] = "from GRN";
				
				$curr_amt = $pcs_cost * $qty;
				$extra_amt += $curr_amt;
			}
		}
		$con->sql_freeresult($q1);
		
		if($non_sku_items){
			$nsi_list = unserialize($non_sku_items);
			
			foreach($nsi_list['code'] as $key=>$nsi_code){
				$nsi['code'][] = $nsi_code;
				$nsi['description'][] = $nsi_list['description'][$key];
				$nsi['cost'][] = doubleval($nsi_list['cost'][$key]);
				$nsi['qty'][] = $nsi_list['qty'][$key];
				$nsi['i_c'][] = 1;
				
				// just in case the invalid items did not capture GST information
				if($form['is_under_gst']){
					if(!$nsi_list['gst_id'][$key]){
						$nsi['gst_id'][] = $grri_info['gst_id'];
						$nsi['gst_code'][] = $grri_info['gst_code'];
						$nsi['gst_rate'][] = $grri_info['gst_rate'];
						if($grri_info['doc_no']) $nsi['doc_no'][] = $grri_info['doc_no'];
						if($grri_info['doc_date']) $nsi['doc_date'][] = $grri_info['doc_date'];
					}else{
						$nsi['gst_id'][] = $nsi_list['gst_id'][$key];
						$nsi['gst_code'][] = $nsi_list['gst_code'][$key];
						$nsi['gst_rate'][] = $nsi_list['gst_rate'][$key];
						if($nsi_list['doc_no'][$key]) $nsi['doc_no'][] = $nsi_list['doc_no'][$key];
						if($nsi_list['doc_date'][$key]) $nsi['doc_date'][] = $nsi_list['doc_date'][$key];
					}
				}
				$nsi['reason'][] = "from GRN";
				
				$curr_amt = $nsi_list['cost'][$key] * $nsi_list['qty'][$key];
				$extra_amt += $curr_amt;
			}
			unset($nsi_list, $non_sku_items);
		}

		if($generate_gra){
			$gra_id_list = array();
			$nsi_inserted = false;
			
			// load SKU type from database
			$sku_type_list = array();
			$q1 = $con->sql_query("select * from sku_type where active=1 order by code");
			while($st = $con->sql_fetchassoc($q1)){
				$sku_type_list[$st['code']] = $st;
			}
			
			$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
			$report_prefix = $con->sql_fetchfield(0);
			$con->sql_freeresult();

			// got item need to generate to gra
			foreach ($sku_type_list as $sku_type=>$st_info){
				if($gra_item_list[$sku_type] || ($nsi && !$nsi_inserted)){
					$gra_amt = 0;
					$grn_doc_no = $report_prefix.sprintf('%05d', $form['id']);

					$gra = array();
					$gra['branch_id'] = $branch_id;
					$gra['user_id'] = $form['user_id'];
					$gra['vendor_id'] = $form['vendor_id'];
					$gra['dept_id'] = $form['department_id'];
					$gra['added'] = 'CURRENT_TIMESTAMP';
					$gra['sku_type'] = $sku_type;
					if($form['branch_is_under_gst']) $gra['is_under_gst'] = 1;
					else $gra['is_under_gst'] = 0;
					//$gra['return_timestamp'] = 'CURRENT_TIMESTAMP';
					$gra['remark2'] = 'Rejected from '.$report_prefix.sprintf('%05d', $grn_id);
					$gra['currency_code'] = $form['currency_code'];
					$gra['currency_rate'] = $form['currency_rate'];
					
					// non sku items will insert once for the first gra only
					if(!$nsi_inserted){
						$gra['extra'] = serialize($nsi);
						$gra['extra_amount'] = $extra_amt;
						unset($extra_amt); // need to unset it to ensure it will not add up to next gra
						$nsi_inserted = true;
					}
					//$gra['status'] = 0;
					//$gra['returned'] = 1;

					$con->sql_query("insert into gra ".mysql_insert_by_field($gra));
					$gra_id = $con->sql_nextid();
					$gra_id_list[] = $gra_id;

					// loop items in this sku type
					foreach ($gra_item_list[$sku_type] as $r) {
						$upd = array();
						$upd['branch_id'] = $branch_id;
						$upd['gra_id'] = $gra_id;
						$upd['user_id'] = $gra['user_id'];
						$upd['selling_price'] = $r['latest_selling_price'];
						$upd['qty'] = $r['qty'];
						$upd['cost'] = $r['cost'];
						$upd['doc_no'] = $grn_doc_no;
						$upd['doc_date'] = $form['rcv_date'];
						$upd['sku_item_id'] = $r['sku_item_id'];
						$upd['vendor_id'] = $gra['vendor_id'];
						$upd['return_type'] = 'Other';
						$upd['currency_code'] = $form['currency_code'];

						if($sku_type=='CONSIGN')
							$upd['amount'] = $upd['qty'] * $upd['selling_price'];
						else
							$upd['amount'] = $upd['qty'] * $upd['cost'];

						if($form['branch_is_under_gst']){
							// need to check if the GRN itself is not under GST, then have to choose "NR" as gst code
							if(!$form['is_under_gst']){
								$upd['gst_id'] = $gst_nr_info['id'];
								$upd['gst_code'] = $gst_nr_info['code'];
								$gst_rate = $upd['gst_rate'] = $gst_nr_info['rate'];
							}else{
								$upd['gst_id'] = $r['gst_id'];
								$upd['gst_code'] = $r['gst_code'];
								$gst_rate = $upd['gst_rate'] = $r['gst_rate'];
							}
							
							$prms = array();
							$is_inclusive_tax = get_sku_gst("inclusive_tax", $upd['sku_item_id']);
							//$input_gst = get_sku_gst("input_tax", $upd['sku_item_id']);

							$prms['selling_price'] = $upd['selling_price'];
							$prms['inclusive_tax'] = $is_inclusive_tax;
							$prms['gst_rate'] = $gst_rate;
							$gst_sp_info = calculate_gst_sp($prms);

							$upd['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
							
							if($is_inclusive_tax == "yes"){
								$upd['gst_selling_price'] = $upd['selling_price'];
								$upd['selling_price'] = $gst_sp_info['gst_selling_price'];
							}

							$upd['amount_gst']=round($upd['amount'] * ((100+$gst_rate)/100),2);
							$upd['gst']=$upd['amount_gst']-round($upd['amount'], 2);
						}

						$upd['amount']=round($upd['amount'],2);
						
						$gra_amt += $upd['amount'];

						$con->sql_query("insert into gra_items ".mysql_insert_by_field($upd));
					}
					
					// update gra amount
					$upd = array();
					$upd['amount'] = $gra_amt + $gra['extra_amount'];
					$con->sql_query("update gra set ".mysql_update_by_field($upd)." where id = ".mi($gra_id)." and branch_id = ".mi($branch_id));	
				}
			}
		}else{
			// delete from grn items for those returned SKU items
			$con->sql_query("delete
							 from grn_items
							 where grn_id = ".mi($grn_id)." 
							 and branch_id = ".mi($branch_id)." 
							 and item_check = 1");

			// update those po items that having returned qty
			$q1 = $con->sql_query("select gi.*
								 from grn_items gi
								 where gi.grn_id = ".mi($grn_id)." and gi.branch_id = ".mi($branch_id)." 
								 and (gi.item_group = 1 or gi.item_group = 2) and (gi.return_ctn != 0 or gi.return_pcs != 0)");
			
			while($r = $con->sql_fetchassoc($q1)){
				$upd = array();
				$upd['ctn'] = doubleval($r['ctn'] - $r['return_ctn']);
				$upd['pcs'] = doubleval($r['pcs'] - $r['return_pcs']);
				$upd['return_ctn'] = 0;
				$upd['return_pcs'] = 0;
				$con->sql_query("update grn_items set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and grn_id = ".mi($grn_id)." and branch_id = ".mi($branch_id));
			}
			$con->sql_freeresult($q1);
		}

		
		$upd = array();
		if($nsi){
			$upd['non_sku_items'] = serialize($nsi);	
		}
		if($gra_id_list){
			$upd['return_gra_id_list'] = serialize($gra_id_list);
		}
		
		if($upd){
			// update grn
			$con->sql_query("update grn set ".mysql_update_by_field($upd)." where id = ".mi($grn_id)." and branch_id = ".mi($branch_id));	
		}
	}
	
	function update_total_variance($id, $bid){
		global $con, $sessioninfo, $config;

		//print "<li> updating $bid,$id";
		$q1 = $con->sql_query("select sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,(grn_items.ctn * rcv_uom.fraction) + grn_items.pcs, (grn_items.acc_ctn * rcv_uom.fraction) + grn_items.acc_pcs)-grn_items.po_qty) as variance
						 from grn_items
						 left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
						 where grn_id = ".mi($id)." and branch_id = ".mi($bid)." and item_check=0
						 group by grn_items.id") or die(mysql_error());

		while ($r=$con->sql_fetchassoc()){
			$ttl_variance += abs($r['variance']);
		}
		$con->sql_freeresult($q1);
		//if ($t[0]==0) return;

		$con->sql_query("update grn set last_update=last_update,have_variance=".doubleval($ttl_variance)." where id=$id and branch_id=$bid");
	}
	
	function get_item_details($prms=array()){
		global $con, $config;
		
		// do not proceed if doesn't pass in the SKU item ID, Branch ID or GRR received date
		if(!$prms['branch_id'] || !$prms['sku_item_id'] || !$prms['rcv_date'] || !$prms['vendor_id']) return;
		
		$filters = array();
		$filters[] = "tgi.branch_id = ".mi($prms['branch_id'])." and grn.approved=1 and tgi.sku_item_id = ".mi($prms['sku_item_id'])." and grr.rcv_date <= ".ms($prms['rcv_date']);
		
		if($prms['branch_id'] == 1){ // do below if it was HQ
			if(!$config['grn_do_branch2hq_update_cost']){
				$filters[] = "gri.type<>'DO'";	// exclude DO
			}
		}else{
			if(!$config['grn_do_hq2branch_update_cost']){
				$filters[] = "not (gri.type='DO' and do.branch_id=1)";
			}
			if(!$config['grn_do_branch2branch_update_cost']){
				$filters[] = "not (gri.type='DO' and do.branch_id>1)";
			}
		}
		
		// get last grn cost
		$items=$con->sql_query("select si.*, tgi.*, si.packing_uom_id as master_uom_id, if (tgi.acc_cost is null, tgi.cost, tgi.acc_cost) as cost, 
								tgi.uom_id as uom_id, uom.fraction as uom_fraction, uom.code as uom_code, sku.is_bom,
								ifnull(sip.price, si.selling_price) as curr_selling_price, tgi.selling_price, puom.code as packing_uom_code, puom.fraction as packing_uom_fraction, tgi.id as grn_item_id, sku.mst_input_tax, sku.category_id, si.id as sku_item_id, grn.is_under_gst as history_is_under_gst
								from grn_items tgi
								left join sku_items si on tgi.sku_item_id=si.id
								left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = tgi.branch_id
								left join sku on sku.id = si.sku_id
								left join uom on uom.id = tgi.uom_id
								left join uom puom on puom.id=si.packing_uom_id
								left join grn on tgi.grn_id=grn.id and tgi.branch_id=grn.branch_id
								left join grr on grr_id=grr.id and grn.branch_id=grr.branch_id
								left join grr_items gri on gri.branch_id=grn.branch_id and gri.id=grn.grr_item_id
								left join do on do.do_no=gri.doc_no and gri.type='DO' and do.do_type='transfer' and do.do_branch_id=gri.branch_id
								where ".join(" and ", $filters)."
								having cost > 0
								order by grr.rcv_date desc limit 1");

		if($con->sql_numrows()==0){
			//get from po	
			$items=$con->sql_query("select si.*, po_items.*, po_items.id as po_item_id, po.po_no,
									round(po_items.order_price, ".mi($config['global_cost_decimal_points']).") as cost, 
									ifnull(sip.price, si.selling_price) as curr_selling_price, po_items.selling_price, 
									uom2.id as uom_id, po_items.selling_uom_id as selling_uom_id, uom2.fraction as uom_fraction,
									si.packing_uom_id as master_uom_id, uom2.code as uom_code, puom.code as packing_uom_code, puom.fraction as packing_uom_fraction, sku.is_bom, sku.mst_input_tax, sku.category_id, po.is_under_gst as history_is_under_gst
									from po_items
									left join sku_items si on po_items.sku_item_id = si.id
									left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = po_items.branch_id
									left join sku on sku.id = si.sku_id
									left join uom puom on puom.id=si.packing_uom_id
									left join uom uom2 on uom2.id = po_items.order_uom_id
									left join po on po_items.po_id = po.id and po.branch_id = po_items.branch_id
									where po.branch_id = ".mi($prms['branch_id'])." and po.active and po.approved and po_items.sku_item_id = ".mi($prms['sku_item_id'])." and po.po_date <= ".ms($prms['rcv_date'])."
									having cost >0 
									order by po.po_date desc limit 1");
		}

		if($con->sql_numrows()==0){
			//get from master
			$items=$con->sql_query("select si.*, si.id as sku_item_id, si.cost_price as cost, 
									ifnull(sip.price, si.selling_price) as selling_price, uom.id as uom_id,
									si.packing_uom_id as master_uom_id, uom.fraction as uom_fraction, uom.code as uom_code,
									ifnull(si.artno,si.mcode) as artno_mcode, puom.code as packing_uom_code, puom.fraction as packing_uom_fraction, sku.is_bom, sku.mst_input_tax, sku.category_id
									from sku_items si
									left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($prms['branch_id'])."
									left join sku on sku_id = sku.id
									left join uom on uom.id = si.packing_uom_id
									left join uom puom on puom.id=si.packing_uom_id
									where si.id = ".mi($prms['sku_item_id']));
		}
		$get = $con->sql_fetchassoc($items);
		
		// get quotation cost when it is not matched with PO/DO
		// check if have quotation cost, need to use it
		$qc_info = $qc_filters = array();
		$qc_filters[] = "sivqch.branch_id = ".mi($prms['branch_id']);
		$qc_filters[] = "sivqch.vendor_id = ".mi($prms['vendor_id']);
		$qc_filters[] = "sivqch.sku_item_id = ".mi($prms['sku_item_id']);
		$qc_filters[] = "sivqch.added <= ".ms($prms['rcv_date']." 23:59:59");
		
		// get quotation cost
		$q1 = $con->sql_query("select sivqch.*
							   from sku_items_vendor_quotation_cost_history sivqch
							   join sku_items si on sivqch.sku_item_id=si.id
							   where ".join(" and ", $qc_filters)."
							   order by sivqch.added desc 
							   limit 1");
		$qc_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($qc_info['cost']>0){
			$get['cost'] = $qc_info['cost'];
		}
		unset($qc_info, $qc_filters);

		// get selling price from history
		$price_date = date("Y-m-d",strtotime("+1 day",strtotime($prms['rcv_date'])));
		$r1 = $con->sql_query("select * from sku_items_price_history where added <= ".ms($price_date)." and sku_item_id = ".mi($prms['sku_item_id'])." and branch_id = ".mi($prms['branch_id'])." order by added desc limit 1");
		$siph = $con->sql_fetchassoc($r1);
	
		if($siph['price']){
			$get['selling_price']=$siph['price'];
		}

		if($get['po_item_id'] && $get['po_qty'] && !$get['available_po_qty']){
			$prms = array();
			$prms['po_item_id'] = $get['po_item_id'];
			$prms['branch_id'] = $prms['branch_id'];
			$prms['po_no'] = $get['po_no'];
			$prms['po_qty'] = $get['po_qty'] * $get['packing_uom_fraction'];
			$get['available_po_qty'] = $this->load_available_po_qty($prms);
		}
		
		if($get['return_ctn'])	$get['return_ctn'] = '';
		if($get['return_pcs'])	$get['return_pcs'] = '';

		return $get;
	}
	
	function load_available_po_qty($prms){
		global $con;
		
		if(!$prms) return;
		
		$po_item_id = $prms['po_item_id'];
		$bid = $prms['branch_id'];
		$po_qty = $prms['po_qty'];
		$po_no = $prms['po_no'];
		$q1 = $con->sql_query("select gi.*, u.fraction, pkuom.fraction as packing_uom_fraction
							   from grn_items gi
							   left join grn on grn_id = grn.id and gi.branch_id = grn.branch_id 
							   left join sku_items si on si.id = gi.sku_item_id
							   left join uom u on u.id = gi.uom_id 
							   left join uom pkuom on pkuom.id = si.packing_uom_id 
							   left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
							   left join grr_items gri on gri.grr_id = grr.id and gri.branch_id = grr.branch_id and gri.type = 'PO'
							   where grn.active=1 and grn.status=1 and grn.approved=1 and gi.po_item_id = ".mi($po_item_id)." and gi.branch_id = ".mi($bid)." and gri.doc_no = ".ms($po_no));
		
		while($r=$con->sql_fetchassoc($q1)){
			if($r['acc_pcs']>0 || $r['acc_ctn']>0) $rcv_qty += ($r['acc_pcs'] + $r['fraction'] * $r['acc_ctn']) * $r['packing_uom_fraction'];
			else $rcv_qty += ($r['pcs'] + $r['fraction'] * $r['ctn']) * $r['packing_uom_fraction'];
		}
		$con->sql_freeresult($q1);
		return mf($po_qty-$rcv_qty);
	}
}
?>
