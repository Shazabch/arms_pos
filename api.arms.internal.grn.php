<?php
/*
8/5/2020 11:47 AM William
- Bug fixed variable name wrong.
*/
include("goods_receiving_note2.include.php");

class API_ARMS_INTERNAL_GRN {
	var $main_api = false;
	
	// Error List
	var $err_list = array(
		"no_doc_found" => 'No %s Found.',
		"grn_create_failed" => "Failed to Create GRN.",
		"grn_no_item_added" => "GRN does not have valid SKU item",
		"grn_zero_qty" => "GRN Total QTY is zero",
		"grn_grr_used" => "This GRR#%s has been used on GRN#%s",
		"grn_invalid_barcode" => "Cannot search Barcode with empty string.",
		"grn_item_is_bom" => "SKU Item [%s] is BOM Package and cannot be added.",
		"grn_contain_bom_package" => "GRN#%s contains BOM package items and cannot be edited."
	);
	
	function __construct($main_api){
		$this->main_api = $main_api;
	}
	
	function show_grn(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$form = $_REQUEST;
		if(!$form['grr_id'] && !$form['grn_id']) $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "GRN"), "no_doc_found");
		
		$bid = $this->main_api->app_branch_id;
		
		$filters = array();
		$filters[] = "grr.active = 1"; // grr must active
		$filters[] = "grr.branch_id = ".mi($bid);
		if($form['grr_id']) $filters[] = "grr.id = ".mi($form['grr_id']);
		else $filters[] = "grn.id = ".mi($form['grn_id']);
		
		$filter = join(" and ", $filters);
		
		// search GRN list
		$q1 = $con->sql_query("select grr.id as grr_id, grn.id as grn_id, grr.branch_id, grr.vendor_id, v.description as vendor_name, grr.department_id, c.description as department_name,
							   grr.rcv_date as grr_date, grn.added as grn_date, grr.grr_amount, grr.rcv_by, u1.u as rcv_by_username, grr.grr_ctn, grr.grr_pcs, grr.transport, b.report_prefix,
							   grn.user_id as grn_user_id, grn.active as is_grn_active, grn.status as grn_status, grn.authorized as grn_is_authorized, grn.approved as grn_is_approved
							   from grr
							   left join grn on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
							   left join branch b on b.id = grr.branch_id
							   left join vendor v on v.id = grr.vendor_id
							   left join category c on c.id = grr.department_id
							   left join user u1 on u1.id = grr.rcv_by
							   where ".$filter);

		$ret = array();
		if($con->sql_numrows($q1) > 0){
			$grn_data = $con->sql_fetchassoc($q1);
			$grn_data['grn_is_editable'] = 1;
			
			// need to prompt error if this GRN contains BOM package items
			// only check this while in edit mode
			if(!$form['is_view'] && $grn_data['grn_id']){
				$q2 = $con->sql_query("select * from grn_items where branch_id = ".mi($grn_data['branch_id'])." and grn_id = ".mi($grn_data['grn_id'])." and bom_ref_num > 0");
				
				// found the GRN items got BOM package, display error
				if($con->sql_numrows($q2) > 0){
					$this->main_api->error_die(sprintf($this->err_list["grn_contain_bom_package"], $grn_data['grn_id']), "grn_contain_bom_package");
				}
				$con->sql_freeresult($q2);
			}
			
			$grn_data['grr_no'] = $grn_data['report_prefix'].str_pad($grn_data['grr_id'], 5, '0', STR_PAD_LEFT);
			if($grn_data['grn_id']){
				$grn_data['grn_no'] = $grn_data['report_prefix'].str_pad($grn_data['grn_id'], 5, '0', STR_PAD_LEFT);
				$grn_data['grn_date'] = date("Y-m-d", strtotime($grn_data['grn_date']));
				
				if(!$grn_data['is_grn_active']){ // means the GRN has been cancelled
					$grn_data['status_name'] = "Cancelled / Terminated";
				}elseif($grn_data['grn_status']){ // found got status
					if($grn_data['grn_status']==1){
						if(!$grn_data['grn_is_approved']) $grn_data['status_name'] = "Waiting for Approval"; // the GRN was confirmed and already send for approval cycle
						else $grn_data['status_name'] = "Approved"; // the GRN is approved
					}else{
						$grn_data['status_name'] = "Cancelled / Terminated"; // the GRN has been cancelled
					}
				}elseif($grn_data['grn_is_authorized']){ // the GRN has been confirmed from draft and send for account verification
					$grn_data['status_name'] = "Send for Account Verification";
				}elseif($grn_data['grn_user_id'] != $this->main_api->user['id']){ // current mobile app logged on user is not the GRN owner
					$grn_data['status_name'] = "Not the GRN Owner";
				}
				
				if($grn_data['status_name']) $grn_data['grn_is_editable'] = 0;
			}else{
				unset($grn_data['grn_id'], $grn_data['grn_date'], $grn_data['grn_user_id'], $grn_data['is_grn_active'], $grn_data['grn_status'], $grn_data['grn_is_authorized']);
			}
			
			// load GRR Document No and Type
			$prms = array();
			$prms['grr_id'] = $grn_data['grr_id'];
			$prms['branch_id'] = $grn_data['branch_id'];
			$doc_info = $appCore->grrManager->loadGRRDoc($prms);
			$grn_data = array_merge($grn_data, $doc_info); // merge in the doc_no and doc_type
			
			// load GRR lorry image
			$prms = array();
			$prms['grr_id'] = $r['grr_id'];
			$prms['branch_id'] = $r['branch_id'];
			$img_path_info = $appCore->grrManager->loadGRRImage($prms);
			if($img_path_info['ok']) $grn_data['image_url'] = $img_path_info['image_url'];
			unset($img_path_info);

			$ret['result'] = 1;
			$ret['grn_data'] = $grn_data;
			unset($grn_data);
		}else{
			$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "GRN"), "no_doc_found");
		}
		$con->sql_freeresult($q1);
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function get_grn_items_list(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$form = $_REQUEST;
		$form['branch_id'] = $this->main_api->app_branch_id;
		if(!$form['grn_id'] || !$form['branch_id']) $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "GRN"), "no_doc_found");
		
		// build up the filters
		$fitlers = array();
		$filters[] = "gi.item_group != 0";
		$filters[] = "gi.branch_id = ".mi($form['branch_id']);
		$filters[] = "gi.grn_id = ".mi($form['grn_id']);
		
		// query to get the list of GRN items
		$gi_list = array();
		$q1 = $con->sql_query("select gi.grn_id, gi.id as grn_item_id, gi.branch_id, gi.sku_item_id, gi.artno_mcode, si.sku_item_code, si.artno, si.link_code, si.description, gi.uom_id, 
							   gi.cost, gi.selling_price, gi.item_group, gi.gst_id, gi.gst_rate, gi.gst_code, gi.selling_gst_id, gi.selling_gst_rate, gi.selling_gst_code, 
							   gi.gst_selling_price, si.sku_apply_items_id, gi.ctn, gi.pcs, u.fraction as uom_fraction
							   from grn_items gi
							   left join sku_items si on si.id = gi.sku_item_id
							   left join uom u on u.id = gi.uom_id
							   where ".join(" and ", $filters)."
							   order by gi.id");
		
		while($r = $con->sql_fetchassoc($q1)){
			// calculate the ctn and pcs to become quantity
			$r['quantity'] = 0;
			if($r['uom_fraction'] > 1){
				$r['quantity'] = ($r['ctn'] * $r['uom_fraction']) + $r['pcs'];
			}else{
				$r['quantity'] = $r['pcs'];
			}
			
			// load POS Photo
			$promo_photo_list = $appCore->skuManager->getSKUItemPromoPhotos($r['sku_item_id']);
			if($promo_photo_list){
				$r['image_url'] = $promo_photo_list[0];
			}
			unset($promo_photo_list);
			
			// search photo from sku item if couldnt find the POS photo
			if(!$r['image_url']){
				$photo_list = get_sku_item_photos($r['sku_item_id'], $r);
				if($photo_list) $r['image_url'] = $photo_list[0]; // we only need get the first photo
				unset($photo_list);
			}
			
			// search photo from sku apply item if couldnt find the SKU photo
			if(!$r['image_url']){
				$apply_photo_list = get_sku_apply_item_photos($r['sku_apply_items_id']);
				if($apply_photo_list) $r['image_url'] = $apply_photo_list[0]; // we only need get the first photo
				unset($apply_photo_list);
			}
			unset($r['artno'], $r['sku_apply_items_id'], $r['ctn'], $r['pcs'], $r['uom_fraction']);  // mobile app doesn't need this
			
			$gi_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($gi_list){
			$ret['result'] = 1;
			$ret['grn_item_list'] = $gi_list;
		}else{
			$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "GRN Item"), "no_doc_found");
		}
		unset($gi_list);
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function search_grn_item(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$form = $_REQUEST;
		$branch_id = $this->main_api->app_branch_id;
		
		// straight return error if doesn't pass in the the barcode
		if(!$form['barcode']) $this->main_api->error_die($this->main_api->err_list["grn_invalid_barcode"], "grn_invalid_barcode");
		
		// return error if user doesn't pass in GRR and GRN ID
		if(!$form['grr_id'] && !$form['grn_id']) $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "GRR"), "no_doc_found");
		
		$filters = array();
		if($form['grr_id']){
			$filters[] = "grr.branch_id = ".mi($branch_id)." and grr.id = ".mi($form['grr_id']);
		}else{
			$filters[] = "grn.branch_id = ".mi($branch_id)." and grn.id = ".mi($form['grn_id']);
		}
		
		// load GRR & GRN info to be used while getting item details
		$q2 = $con->sql_query("select grr.rcv_date, grr.is_under_gst, grr.vendor_id, grr.department_id, grr.id as grr_id
							   from grr
							   left join grn on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
							   where ".join(" and ", $filters));
		$this->grn_info = $con->sql_fetchassoc($q2);
		$con->sql_freeresult($q2);
		
		// load GRR Document No and Type to decide whether mobile app need to show the cost field or not
		$prms = array();
		$prms['grr_id'] = $this->grn_info['grr_id'];
		$prms['branch_id'] = $branch_id;
		$doc_info = $appCore->grrManager->loadGRRDoc($prms);
		unset($prms);
		
		$show_cost = 1; // put it as always need to show first
		if($doc_info['doc_type'] == "PO" || ($doc_info['doc_type'] == "DO" && $doc_info['is_ibt_do'])){
			$show_cost = 0;
		}
		unset($doc_info);
		
		// return error as if couldn't find GRR details
		if(!$this->grn_info) $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "GRR"), "no_doc_found");
		
		// Get POS Settings whether need to search art no
		$artno_as_barcode = mi(get_pos_settings_value($branch_id, 'artno_as_barcode'));
		
		$ret = $filters = $filters_or = array();
		$filters[] = "si.active=1"; // sku item must currently active
		
		if(strlen($form['barcode'])==13){
			$barcode_12 = substr($form['barcode'],0,12);
			
			$filters_or[] = "si.mcode = ".ms($barcode_12);
			$filters_or[] = "si.link_code = ".ms($barcode_12);
			$filters_or[] = "si.sku_item_code = ".ms($barcode_12);
			if($artno_as_barcode)	$filters_or[] = "si.artno = ".ms($barcode_12);
			unset($barcode_12);
		}
		
		$filters_or[] = "si.mcode = ".ms($form['barcode']);
		$filters_or[] = "si.link_code = ".ms($form['barcode']);
		$filters_or[] = "si.sku_item_code = ".ms($form['barcode']);
		if($artno_as_barcode)	$filters_or[] = "si.artno = ".ms($form['barcode']);
		
		$filters[] = "(".join(' or ', $filters_or).")";
		
		$q1 = $con->sql_query("select si.id as sku_item_id, si.sku_apply_items_id, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description,
							   sku.is_bom, si.bom_type
							   from sku_items si
							   left join sku on sku.id = si.sku_id
							   where ".join(" and ", $filters)."
							   limit 1");
		
		if($con->sql_numrows($q1) > 0){
			$si_info = $con->sql_fetchassoc($q1);
			
			// prompt error if it is bom package
			if($si_info['is_bom'] && $si_info['bom_type'] == "package"){
				$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "SKU Item"), "no_doc_found"); 
			}
			
			
			$item_data = array();
			$item_data['show_cost'] = $show_cost;
			$item_data['sku_item_id'] = $si_info['sku_item_id'];
			$item_data['sku_item_code'] = $si_info['sku_item_code'];
			$item_data['mcode'] = $si_info['mcode'];
			$item_data['link_code'] = $si_info['link_code'];
			$item_data['description'] = $si_info['description'];

			if($show_cost){
				// function to process the item to get the related grn item info
				$prms = array();
				$prms['grn_id'] = $form['grn_id'];
				$prms['grr_id'] = $form['grr_id'];
				$prms['branch_id'] = $branch_id;
				$prms['sku_item_id'] = $si_info['sku_item_id'];
				$gi_info = $this->process_grn_item($prms);
			
				// pickup the cost
				$item_data['cost'] = $gi_info['cost'];
				
				unset($prms, $gi_info);
			}
			
			// load POS Photo
			$promo_photo_list = $appCore->skuManager->getSKUItemPromoPhotos($si_info['sku_item_id']);
			if($promo_photo_list){
				$item_data['image_url'] = $promo_photo_list[0];
			}
			unset($promo_photo_list);
			
			// search photo from sku item if couldnt find the POS photo
			if(!$item_data['image_url']){
				$photo_list = get_sku_item_photos($si_info['sku_item_id'], $si_info);
				if($photo_list) $item_data['image_url'] = $photo_list[0]; // we only need get the first photo
				unset($photo_list);
			}
			
			// search photo from sku apply item if couldnt find the SKU photo
			if(!$item_data['image_url']){
				$apply_photo_list = get_sku_apply_item_photos($si_info['sku_apply_items_id']);
				if($apply_photo_list) $item_data['image_url'] = $apply_photo_list[0]; // we only need get the first photo
				unset($apply_photo_list);
			}
			
			$ret['result'] = 1;
			$ret['item_info'] = $item_data;
			
			unset($doc_filters, $prms, $si_info, $item_data);
		}else{
			$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "SKU Item"), "no_doc_found");
		}
		$con->sql_freeresult($q1);
		
		unset($artno_as_barcode, $filters, $filters_or);
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function save_grn(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$form = $_REQUEST;
		$grn_id = mi($form['grn_id']);
		$grr_id = mi($form['grr_id']);
		$form['branch_id'] = $branch_id = $this->main_api->app_branch_id;
		
		// return error if user doesn't pass in GRR and GRN ID
		if(!$grr_id && !$grn_id) $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "GRR"), "no_doc_found");
		
		$filters = array();
		if($grr_id){
			$filters[] = "grr.branch_id = ".mi($branch_id)." and grr.id = ".mi($grr_id);
		}else{
			$filters[] = "grn.branch_id = ".mi($branch_id)." and grn.id = ".mi($grn_id);
		}
		
		// data validation for both grn and grn items
		$this->validate_data($form);
		
		// load GRR & GRN info to be used while getting item details
		$q1 = $con->sql_query("select grr.rcv_date, grr.is_under_gst, grr.vendor_id, grr.department_id, gi.id as grr_item_id, grr.id as grr_id
							   from grr
							   left join grr_items gi on gi.grr_id = grr.id and gi.branch_id = grr.branch_id
							   left join grn on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
							   where ".join(" and ", $filters));
		$this->grn_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		$upd = array();
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		// it was editing, do update
		if($grn_id>0){
            $con->sql_query("update grn set ".mysql_update_by_field($upd)." where id = ".mi($grn_id)." and branch_id = ".mi($branch_id));
			$msg = "Updated";
		}else{ // else do insertion as new GRN
			// Get Max ID
			unset($new_id);
			$new_id = $appCore->generateNewID("grn", "branch_id = ".mi($branch_id));
			
			$upd['id'] = $new_id;
			$upd['branch_id'] = $branch_id;
			$upd['grr_id'] = mi($this->grn_info['grr_id']);
			$upd['grr_item_id'] = mi($this->grn_info['grr_item_id']);
			$upd['vendor_id'] = mi($this->grn_info['vendor_id']);
			$upd['department_id'] = mi($this->grn_info['department_id']);
			$upd['user_id'] = $this->main_api->user['id'];
			$upd['is_future'] = 1;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			// check gst status
			$prms = array();
			$prms['date'] = $upd['rcv_date'];
			$prms['vendor_id'] = $upd['vendor_id'];
			$upd['is_under_gst'] = check_gst_status($prms);
			
			$con->sql_query("insert into grn ".mysql_insert_by_field($upd));
			$grn_id = $new_id;
			$msg = "Inserted";
			
			// loop and store up the PO and DO No
			$po_no_list = $do_no_list = array();
			$q1 = $con->sql_query("select doc_no, type from grr_items gi where gi.type in ('PO', 'DO') and gi.grr_id = ".mi($grr_id)." and gi.branch_id = ".mi($branch_id));
			
			while($r = $con->sql_fetchassoc($q1)){
				if($r['type'] == "PO") $po_no_list[] = ms($r['doc_no']);
				else $do_no_list[] = ms($r['doc_no']);
			}
			$con->sql_freeresult($q1);
			
			// copy items from do
			if(count($po_no_list) > 0){ // found the GRR contains PO
				$doc_no = join(",", $po_no_list);
				copy_po_items($doc_no, $grn_id, $branch_id, false); // copy PO items as GRN items
				unset($doc_no);
			}elseif(count($do_no_list) > 0 && $config['do_skip_generate_grn']){ // do this when system are not using auto generate GRN
				foreach($do_no_list as $do_no){ // loop each DO no to check if it was IBT DO
					$q1 = $con->sql_query("select *, id as do_id from do where do_no = ".ms($do_no)." and do_branch_id = ".mi($branch_id)." and do_type = 'transfer'");
					if($con->sql_numrows($q1) > 0){  // means is IBT DO, copy the DO items as GRN items
						copy_do_items($do_no, $grn_id, $branch_id, false);
					}
					$con->sql_freeresult($q1);
				}
			}
			unset($po_no_list, $do_no_list);
		}
		
		$last_gi_id = $this->save_grn_items($grn_id, $branch_id);
		
		
		// Construct Data to return		
		$ret = array();
		if($grn_id>0){
			// update the GRR to become used
			$con->sql_query("update grr_items gi
							 left join grr on grr.id = gi.grr_id and grr.branch_id = gi.branch_id 
							 left join po on po.po_no = gi.doc_no and gi.type = 'PO'
							 set gi.grn_used=1, grr.status=1, po.delivered=1
							 where grr.branch_id=".mi($branch_id)." and grr.id=".mi($grr_id));
			
			$ret['result'] = 1;
			$ret['grn_id'] = $grn_id;
			$ret['grn_item_id'] = $last_gi_id;
			
			log_br($this->main_api->user['id'], 'GRN', $grn_id, "GRN saved by ".$this->main_api->user['u']." for (ID#".mi($grn_id).")", $branch_id);
		}else{
			$this->main_api->error_die($this->err_list["grn_create_failed"], "grn_create_failed");
		}
		unset($this->grn_info);
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function save_grn_items($grn_id, $branch_id){
		global $con, $config, $appCore;

		$form = $_REQUEST;
        if(!$grn_id || !$branch_id){
			$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "GRN"), "no_doc_found");
		}
		
		$this->existed_sku_items = array();
		$ins_fields = array('id','branch_id','grn_id','sku_item_id','artno_mcode','cost','selling_price','uom_id','selling_uom_id','pcs','po_cost',
							'po_item_id','item_group','bom_ref_num','bom_qty_ratio', 'gst_selling_price','selling_gst_id','selling_gst_code','selling_gst_rate',
							'gst_id','gst_code','gst_rate');
		$upd_fields = array('pcs','cost'); // system won't add item if found SKU item was existed, so we just lookup the GRN item ID and do ctn and pcs updates only

        if($form['items']){
			$form['items'] = json_decode($form['items'], true);
			foreach($form['items'] as $row_id=>$item){
				$upd = array();
				
				// do this if the item wasn't existed
				if(!$item['grn_item_id']){
					if(!$this->existed_sku_items[$item['sku_item_id']]){
						$prms = array();
						$prms['grn_id'] = $grn_id;
						$prms['branch_id'] = $branch_id;
						$prms['sku_item_id'] = $item['sku_item_id'];
						if($item['cost'] > 0) $prms['cost'] = $item['cost'];
						$item_info = $this->process_grn_item($prms);
						unset($prms);
						
						if($item_info) $upd = $item_info;
						unset($item_info);
					}
					
					// found the item was duplicated, just need update the quantity
					if($this->existed_sku_items[$item['sku_item_id']]['grn_item_id']){
						$upd = array(); // reset all the fields for update
						$item['grn_item_id'] = $this->existed_sku_items[$item['sku_item_id']]['grn_item_id'];
						$item['cost'] = $this->existed_sku_items[$item['sku_item_id']]['cost'];
					}
				}
				
				$upd['pcs'] = $item['quantity'];
				if($item['cost'] > 0) $upd['cost'] = $item['cost'];

				if(!$item['grn_item_id']){ // is insert GRN item
					// Get Max ID
					unset($new_id);
					$new_id = $appCore->generateNewID("grn_items", "branch_id = ".mi($branch_id));
					
					$upd['id'] = $new_id;
					$upd['grn_id'] = $grn_id;
					$upd['branch_id'] = $branch_id;
					$upd['selling_uom_id'] = 1;
					$con->sql_query("insert into grn_items ".mysql_insert_by_field($upd, $ins_fields));
					$gi_id = $new_id;
				}else{ // is update GRN item ctn and pcs
					$con->sql_query("update grn_items set ".mysql_update_by_field($upd, $upd_fields)." where branch_id = ".mi($branch_id)." and grn_id = ".mi($grn_id)." and id=".mi($item['grn_item_id']));
					$gi_id = $item['grn_item_id'];
				}
				unset($upd);
			}	
		}
		unset($ins_fields, $upd_fields);
		
		// function to re-calculate GRN total amount, selling and variance
		$appCore->grnManager->update_total_amount($grn_id, $branch_id);
		$appCore->grnManager->update_total_selling($grn_id, $branch_id);
		$appCore->grnManager->update_total_variance($grn_id, $branch_id);
		
		return $gi_id;
	}
	
	function validate_data($form){
		global $con, $config, $appCore;
		
		// check if it was new GRN
		if(!$form['grn_id']){
			$q1 = $con->sql_query("select * from grn where branch_id = ".mi($form['branch_id'])." and grr_id = ".mi($form['grr_id'])." and active=1");
			
			if($con->sql_numrows($q1) > 0){ // found this GRR has been created from other GRN
				$grn_info = $con->sql_fetchassoc($q1);
				$this->main_api->error_die(sprintf($this->err_list["grn_grr_used"], $grn_info['grr_id'], $grn_info['id']), "grn_grr_used");
				unset($grn_info);
			}
			$con->sql_freeresult($q1);
		}else{ // check if the GRN existed or not
			$q1 = $con->sql_query("select * from grn where branch_id = ".mi($form['branch_id'])." and id = ".mi($form['grn_id']));
			
			if($con->sql_numrows($q1) == 0){
				$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "GRN"), "no_doc_found");
			}
			$con->sql_freeresult($q1);
		}
		
		if($form['items']){ // do further checking if got items were added
			$ttl_qty = 0;
			$form['items'] = json_decode($form['items'], true);
			foreach($form['items'] as $row_id=>$item){
				if($item['quantity']) $ttl_qty += $item['quantity'];
			}
			
			// prompt error msg as if no ctn or pcs were inserted 
			if($ttl_qty == 0){
				$this->main_api->error_die($this->err_list["grn_zero_qty"], "grn_zero_qty");
			}
			unset($ttl_qty);
		}else{ // prompt error msg as if no item were added
			$this->main_api->error_die($this->err_list["grn_no_item_added"], "grn_no_item_added");
		}
	}
	
	function process_grn_item($prms=array()){
		global $con, $config, $appCore;
		
		$form = $_REQUEST;
		if(!$prms['sku_item_id']) return;
		$grn_id = $prms['grn_id'];
		$grr_id = $prms['grr_id'];
		$branch_id = $prms['branch_id'];
		$sid = $prms['sku_item_id'];
		
		$ret = array();
		$ret['item_group'] = 0;
		
		// search if this SKU item was added before
		if($grn_id){
			$filters = array();
			$filters[] = "branch_id = ".mi($branch_id);
			$filters[] = "grn_id = ".mi($grn_id);
			$filters[] = "sku_item_id = ".mi($sid);
			$filters[] = "item_group != 0";
			
			$q1 = $con->sql_query("select id as grn_item_id, ctn, pcs, cost, item_group
								   from grn_items 
								   where ".join(" and ", $filters)."
								   limit 1");
								   
			// if found it is existed, pickup the info and return it back
			if($con->sql_affectedrows($q1) > 0){
				$gi_info = $con->sql_fetchassoc($q1);
				$ret = array(); // reset the array since we doesn't need info just to update the ctn and pcs
				$ret = $gi_info;
				$this->existed_sku_items[$sid] = $gi_info;
				unset($gi_info);
				
				return $ret;
			}
			$con->sql_freeresult($q1);
			
			unset($filters);
		}

		// load current sku item info
		$con->sql_query("select sku_items.sku_id, uom.id, uom.fraction as sku_fraction, sku_item_code
						 from sku_items 
						 left join uom on uom.id = sku_items.packing_uom_id 
						 where sku_items.id = ".mi($sid));
		$sku_id = $con->sql_fetchfield(0);
		$uom_id = $con->sql_fetchfield(1);
		$sku_uom = $con->sql_fetchfield(2);
		$sku_item_code = $con->sql_fetchfield(3);

		if($grn_id){ // if found the user was trying to search item from existing GRN
			// load PO item to see whether it is existed
			$q1 = $con->sql_query("select gi.*, uom.id as uom_id, if(puom.fraction=1,uom.fraction,puom.fraction) as uom_fraction, gi.cost, si.sku_id, puom.fraction as mst_uom_fraction
								 from grn_items gi
								 left join sku_items si on si.id = gi.sku_item_id
								 left join sku on sku.id = si.sku_id
								 left join uom on uom.id = gi.uom_id
								 left join uom puom on puom.id = si.packing_uom_id
								 where gi.branch_id = ".mi($branch_id)."
								 and gi.grn_id = ".mi($grn_id)."
								 and (gi.item_group = 0 or gi.item_group = 1)
								 and gi.po_item_id != 0
								 and sku.id = ".mi($sku_id)."
								 order by gi.id");
		}else{ // it was a new GRN, need to check if the GRR contains PO or IBT DO
			// get GRR doc_type and doc_no info
			$tmp = array();
			$tmp['grr_id'] = $grr_id;
			$tmp['branch_id'] = $branch_id;
			
			$grr_info = $appCore->grrManager->loadGRRDoc($tmp);
			
			if($grr_info['doc_type'] == "DO" && $grr_info['is_ibt_do']){ // search DO if the document no was matched with IBT DO
				$q1 = $con->sql_query("select 1 as selling_uom_id, di.selling_price, di.sku_item_id, uom.id as uom_id, if(puom.fraction=1,uom.fraction,puom.fraction) as uom_fraction, 
									   di.cost_price as cost, si.sku_id, puom.fraction as mst_uom_fraction, di.id as po_item_id, di.artno_mcode
									   from do
									   left join do_items di on di.do_id = do.id and di.branch_id = do.branch_id
									   left join sku_items si on si.id = di.sku_item_id
									   left join uom on uom.id = di.uom_id
									   left join uom puom on puom.id = si.packing_uom_id
									   where do.do_no = ".ms($grr_info['doc_no'])." and si.sku_id = ".mi($sku_id));
			}elseif($grr_info['doc_type'] == "PO"){ // search PO if the document was match with PO
				$q1 = $con->sql_query("select pi.selling_uom_id, pi.selling_price, pi.sku_item_id, uom.id as uom_id, if(puom.fraction=1,uom.fraction,puom.fraction) as uom_fraction, 
									   pi.order_price as cost, si.sku_id, puom.fraction as mst_uom_fraction, pi.id as po_item_id, pi.artno_mcode
									   from po
									   left join po_items pi on pi.po_id = po.id and pi.branch_id = po.branch_id
									   left join sku_items si on si.id = pi.sku_item_id
									   left join uom on uom.id = pi.order_uom_id
									   left join uom puom on puom.id = si.packing_uom_id
									   where po.po_no = ".ms($grr_info['doc_no'])." and si.sku_id = ".mi($sku_id));
			}
			
			unset($tmp, $grr_info);
		}
		
		if($q1 && $con->sql_numrows($q1) > 0){
			while($r=$con->sql_fetchassoc($q1)){
				if($r['sku_item_id'] == $sid && $r['item_group'] <= 1){ // it is matched with PO
					$ret['item_group'] = 1;
					$ret['cost'] = $r['cost'];
					$ret['selling_price'] = $r['selling_price'];
					$ret['po_cost'] = $r['po_cost'];
					$ret['uom_fraction'] = $r['uom_fraction'];
					$ret['uom_id'] = $r['uom_id'];
					$ret['artno_mcode'] = $r['artno_mcode'];
					$ret['selling_uom_id'] = $r['selling_uom_id'];
					$ret['po_item_id'] = $r['po_item_id'];
					$ret['po_qty'] = 0;
				}elseif($r['sku_item_id'] != $sid && $ret['item_group'] != 1){ // it was from the same SKU family
					$ret['item_group'] = 2; // it is under item's SKU child
					$ret['cost'] = round($r['cost']*($sku_uom/$r['uom_fraction']), $config['global_cost_decimal_points']);
					if($r['mst_uom_fraction'] == 1 && $r['cost'] > $r['selling_price']){
						$selling_uom_fraction = $r['mst_uom_fraction'];
					}else{
						$selling_uom_fraction = $r['uom_fraction'];
					}
					
					$ret['selling_price'] = $r['selling_price'] * ($sku_uom / $selling_uom_fraction);
					$ret['po_cost'] = round($r['po_cost'] * ($sku_uom / $r['uom_fraction']), $config['global_cost_decimal_points']);
					$ret['uom_fraction'] = $sku_uom;
					$ret['uom_id'] = $uom_id;
					$ret['artno_mcode'] = $r['artno_mcode'];
					$ret['selling_uom_id'] = $r['selling_uom_id'];
					$ret['po_item_id'] = $r['po_item_id'];
					$ret['po_qty'] = 0;
					unset($selling_uom_fraction);
				}
			}
		}
		$con->sql_freeresult($q1);

		
		// if found the item doesn't match with PO/DO, mark it as item not in PO/received item
		if(!$ret['item_group']) $ret['item_group'] = 3;
		
		if($ret['item_group'] == 3){ // if found it is item not in PO/received item
			// get SKU item info
			unset($item);
			$tmp = array();
			$tmp['sku_item_id'] = $sid;
			$tmp['branch_id'] = $branch_id;
			$tmp['rcv_date'] = $this->grn_info['rcv_date'];
			$tmp['vendor_id'] = $this->grn_info['vendor_id'];
			$ret = $appCore->grnManager->get_item_details($tmp);
			$ret['item_group'] = 3;
				
			// use the cost from user input
			if($prms['cost'] > 0) $ret['cost'] = $prms['cost'];
		}
		
		$ret['branch_id'] = $branch_id;
		$ret['grn_id'] = $grn_id;
		$ret['sku_item_id'] = $sid;
		
		if(!$config['doc_allow_edit_uom'] && $sku_uom > 1){
			$ret['uom_id'] = 1;
			$ret['uom_fraction'] = 1;
		}
		
		// find price before gst
		if($config['enable_gst']){
			// get sku is inclusive
			$is_sku_inclusive = get_sku_gst("inclusive_tax", $ret['sku_item_id']);
			// get sku original output gst
			$sku_original_output_gst = get_sku_gst("output_tax", $ret['sku_item_id']);
			
			if($is_sku_inclusive == 'yes'){
				// is inclusive tax
				$ret['gst_selling_price'] = $ret['selling_price'];
				
				// find the price before tax
				$sp = $ret['selling_price'];
				$gst_tax_price = round($sp / ($sku_original_output_gst['rate']+100) * $sku_original_output_gst['rate'], 2);
				$price_included_gst = $sp;
				$sp = $price_included_gst - $gst_tax_price;
				$ret['selling_price'] = $sp;
				unset($gst_tax_price, $price_included_gst);
			}else{
				// is exclusive tax
				$gst_amt = round($ret['selling_price'] * $sku_original_output_gst['rate'] / 100, 2);
				$ret['gst_selling_price'] = round($ret['selling_price'] + $gst_amt, 2);
			}
			unset($is_sku_inclusive, $sku_original_output_gst);
			
			// do this if the GRN is under GST mode
			if($this->grn_info['is_under_gst']){
				// get gst output tax
				$output_tax = get_sku_gst("output_tax", $ret['sku_item_id']);
				if($output_tax){
					$ret['selling_gst_id'] = $output_tax['id'];
					$ret['selling_gst_code'] = $output_tax['code'];
					$ret['selling_gst_rate'] = $output_tax['rate'];
				}
				unset($output_tax);
				
				// input tax
				$input_tax = get_sku_gst("input_tax", $ret['sku_item_id']);
				if($input_tax){
					$ret['gst_id'] = $input_tax['id'];
					$ret['gst_code'] = $input_tax['code'];
					$ret['gst_rate'] = $input_tax['rate'];
				}
				unset($input_tax);
			}
		}
		
		return $ret;
	}
}
?>