<?php

/*
4/22/2020 11:47 AM Justin
- Bug fixed on IBT DO does not return vendor_id to mobile app.

4/27/2020 11:26 AM Justin
- Bug fixed on certain error message did not return to mobile app.
*/

class API_ARMS_INTERNAL_GRR {
	var $main_api = false;
	
	// Error List
	var $err_list = array(
		"invalid_search_input" => 'Cannot search GRR with empty string.',
		"no_doc_found" => 'No %s Found.',
		"invalid_rcv_date" => 'Invalid GRR received date %s.',
		"rcv_date_over_limit" => 'Receive Date is over limit or less than required date',
		"field_is_required" => '%s is required.',
		"diff_vendor_from_po" => 'Vendor different from PO.',
		"exceeded_po_cancel_date" => 'PO Cancellation Date is %s. Goods Receiving not allow for this PO.',
		"po_from_diff_dept" => 'PO was from different department.',
		"grr_create_failed" => "Failed to Create GRR.",
		"grr_doc_no_duplicated" => "The %s (%s) was existed in GRR%05d",
		"grr_item_incomplete" => "%s cannot be empty.",
		"grr_invalid_doc" => "The %s %s is invalid, not approved or checkout.",
		"grr_inactive_doc" => "The %s %s is inactive (Rejected or Cancelled).",
		"grr_doc_invalid_branch" => "Invalid receiving branch for this %s.",
		"grr_po_delivered" => "PO already delivered (does not allow partial delivery).",
		"grr_doc_required" => "GRR must at least one document of Invoice, DO or Others.",
		"grr_has_more_inv" => "Could not have more than one Invoice at GRR",
		"grr_ibt_error" => "GRR contains non-IBT and IBT from DO/PO",
		"grr_ibt_disabled" => "IBT DO is disabled due to auto GRR & GRN generation was being enabled.",
		"grr_invalid_image" => "Invalid GRR Image.",
		"grr_invalid_item_id" => "Invalid GRR Item ID.",
		"grr_po_required" => "Required to have at least one PO for this Vendor."
	);
	
	function __construct($main_api){
		$this->main_api = $main_api;
	}
	
	function get_grr_list(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		if(!$_REQUEST['search_str']) $this->main_api->error_die($this->err_list["invalid_search_input"], "invalid_search_input");
		
		$search_type = strtoupper($_REQUEST['search_type']);
		$search_str = $_REQUEST['search_str'];
		$bid = $this->main_api->app_branch_id;
		
		// get the report prefix
		$q1 = $con->sql_query("select report_prefix from branch where id = ".mi($bid));
		$binfo = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$filters = array();
		$filters[] = "grr.active = 1";
		$filters[] = "gi.branch_id = ".mi($bid);
		//$filters[] = "gi.doc_no like ".ms("%".$search_str."%");
		if($search_type=="GRR"){
			if(preg_match("/^$binfo[report_prefix]/i", $search_str)) $search_str = preg_replace("/^$binfo[report_prefix]/i", "", $search_str);
			$filters[] = "gi.grr_id = ".mi(preg_replace("/[^0-9]/","", $search_str));
		}elseif($search_type=="GRN"){
			if(preg_match("/^$binfo[report_prefix]/i", $search_str)) $search_str = preg_replace("/^$binfo[report_prefix]/i", "", $search_str);
			$filters[] = "grn.id = ".mi(preg_replace("/[^0-9]/","", $search_str));
		}elseif($search_type=="PO"){
			$filters[] = "gi.doc_no = ".ms($search_str)." and gi.type = 'PO'";
		}elseif($search_type=="DO"){
			$filters[] = "gi.doc_no = ".ms($search_str)." and gi.type = 'DO'";
		}
		
		$filter = join(" and ", $filters);
		
		// search GRR list
		$q1 = $con->sql_query("select distinct(gi.grr_id) as grr_id, grr.branch_id, b.report_prefix, grn.id as grn_id, grr.vendor_id, v.description as vendor_name,
							   grr.department_id, c.description as department_name, grr.rcv_date as grr_date, grn.added as grn_date, grr.user_id, u1.u as username,
							   grr.rcv_by, u2.u as rcv_by_username, grr.transport, grr.grr_ctn, grr.grr_pcs, grr.grr_amount, grr.active as grr_active, grr.status, grr.last_update, 
							   grr.added, grr.is_under_gst, grr.grr_gst_amount, grr.currency_code, grr.currency_rate, grr.use_po_currency, grr.tax_percent, grr.tax_register, grr.grr_tax
							   from grr_items gi 
							   left join grr on grr.id = gi.grr_id and grr.branch_id = gi.branch_id
							   left join grn on grn.grr_id = grr.id and grn.branch_id = grr.branch_id and grn.active=1
							   left join branch b on b.id = gi.branch_id
							   left join vendor v on v.id = grr.vendor_id
							   left join category c on c.id = grr.department_id
							   left join user u1 on u1.id = grr.user_id
							   left join user u2 on u2.id = grr.rcv_by
							   where ".$filter."
							   group by grr.id");

		$ret = array();
		if($con->sql_numrows($q1) > 0){
			$grr_list = array();
			while($r = $con->sql_fetchassoc($q1)){
				$r['grr_no'] = $r['report_prefix'].str_pad($r['grr_id'], 5, '0', STR_PAD_LEFT);
				if($r['grn_id']){ // build the grn no and date if the GRN existed
					$r['grn_no'] = $r['report_prefix'].str_pad($r['grn_id'], 5, '0', STR_PAD_LEFT);
					$r['grn_date'] = date("Y-m-d", strtotime($r['grn_date']));
				}
				
				// load GRR Document No and Type
				$prms = array();
				$prms['grr_id'] = $r['grr_id'];
				$prms['branch_id'] = $r['branch_id'];
				$doc_info = $appCore->grrManager->loadGRRDoc($prms);
				$r = array_merge($r, $doc_info); // merge in the doc_no and doc_type
				
				// load GRR image
				$prms = array();
				$prms['grr_id'] = $r['grr_id'];
				$prms['branch_id'] = $r['branch_id'];
				$img_path_info = $appCore->grrManager->loadGRRImage($prms);
				if($img_path_info['ok']) $r['image_url'] = $img_path_info['image_url'];
				unset($img_path_info);
				
				$grr_list[] = $r;
			}
			$ret['result'] = 1;
			$ret['grr_list'] = $grr_list;
			unset($grr_list);
		}
		$con->sql_freeresult($q1);
		
		if($search_type == "PO"){
			// search PO details
			$po_info = $this->load_po_details();
			
			// return error message as if couldn't no GRR using this PO and having difficulty to add PO into new GRR
			if(!$ret['grr_list'] && $po_info['error_code']){
				$this->main_api->error_die($po_info['error_msg'], $po_info['error_code']);
			}
			
			if($po_info){
				$ret['result'] = 1;
				$ret['po_info'] = $po_info;
			}
			unset($po_info);
		}elseif($search_type == "DO"){
			// search PO details
			$do_info = $this->load_do_details();
			
			// return error message as if couldn't no GRR using this PO and having difficulty to add PO into new GRR
			if(!$ret['grr_list'] && $do_info['error_code']){
				$this->main_api->error_die($do_info['error_msg'], $do_info['error_code']);
			}
			
			if($do_info){
				$ret['result'] = 1;
				$ret['do_info'] = $do_info;
			}
			unset($do_info);
		}
		
		if(!$ret){
			$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], $search_type), "no_doc_found");
		}
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function get_grr_items_list(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$form = $_REQUEST;
		$form['branch_id'] = $this->main_api->app_branch_id;
		if(!$form['grr_id'] || !$form['branch_id']) $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "GRR"), "no_doc_found");
		
		// query to get the list of GRR items
		$gi_list = array();
		$q1 = $con->sql_query("select grr_id, branch_id, id as grr_item_id, doc_no, doc_date, type, ctn, pcs, amount, remark, gst_amount, gst_id, gst_code, gst_rate, tax, po_override_by_user_id
							   from grr_items
							   where branch_id = ".mi($form['branch_id'])." and grr_id = ".mi($form['grr_id']));
		
		while($r = $con->sql_fetchassoc($q1)){
			// load GRR image
			$prms = array();
			$prms['grr_id'] = $r['grr_id'];
			$prms['branch_id'] = $r['branch_id'];
			$prms['grr_item_id'] = $r['grr_item_id'];
			$img_path_info = $appCore->grrManager->loadGRRImage($prms);
			if($img_path_info['ok']) $r['image_url'] = $img_path_info['image_url'];
			unset($img_path_info);
			
			
			$gi_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($gi_list){
			$ret['result'] = 1;
			$ret['grr_item_list'] = $gi_list;
		}else{
			$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "GRR Item"), "no_doc_found");
		}
		unset($gi_list);
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function load_po_details(){
		global $con, $config, $appCore;
		
		$search_str = $_REQUEST['search_str'];
		$bid = $this->main_api->app_branch_id;
		$con->sql_query("select id as po_id,active,vendor_id,branch_id,po_branch_id,partial_delivery,
						 delivered,department_id,cancel_date,po_no
						 from po 
						 where approved=1 and po_no = ".ms($search_str));

		$po_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$po_info){
			$reset_doc_no = $appCore->grrManager->searchPONo($search_str, $po_info, $bid);
			$po_info['po_no'] = $reset_doc_no;
		}

		$err_code = $err_msg = "";
		if(!$po_info){ // if the PO cannot be found
			$err_code = "grr_invalid_doc";
			$err_msg = sprintf($this->err_list["grr_invalid_doc"], "PO", $search_str);
		}elseif(!$po_info['active']){ // PO is inactive. prompt PO was Cancelled
			$err_code = "grr_inactive_doc";
			$err_msg = sprintf($this->err_list["grr_inactive_doc"], "PO", $search_str);
		}else{
			// branch was not allowed for receiving this PO
			if(($po_info['po_branch_id']>0 && $po_info['po_branch_id'] != $bid) ||($po_info['po_branch_id']==0 && $po_info['branch_id'] != $bid)){
				$err_code = "grr_doc_invalid_branch";
				$err_msg = sprintf($this->err_list["grr_doc_invalid_branch"], "PO");
			}

			// error if new grr trying re-deliver while PO doe s not allow partial delivery
			if($po_info['delivered'] && !$po_info['partial_delivery']){
				// if found the PO has been delivered and not allowed for partial delivery
				$err_code = "grr_po_delivered";
				$err_msg = $this->err_list["grr_po_delivered"];
			}
			
			if(!$_REQUEST['rcv_date']) $rcv_date = date("Y-m-d"); // get the current date as goods receiving date
			else $rcv_date = $_REQUEST['rcv_date'];

			// received date exceeded PO cancellation date
			if(strtotime($rcv_date) >= dmy_to_time($po_info['cancel_date'])){
				$err_code = "exceeded_po_cancel_date";
				$err_msg = sprintf($this->err_list["exceeded_po_cancel_date"], $po_info['cancel_date']);
			}
		}
		
		// change the value for partial_delivery from "on" become 1, empty become 0
		if($po_info['partial_delivery'] == "on"){
			$po_info['partial_delivery'] = 1;
		}else $po_info['partial_delivery'] = 0;
		
		// if found contains error message, attach into the po_info as well
		if($err_code){
			$po_info['error_code'] = $err_code;
			$po_info['error_msg'] = $err_msg;
		}
		unset($err_code, $err_msg);

		return $po_info;
	}
	
	function load_do_details(){
		global $con, $config, $appCore;
		
		$search_str = $_REQUEST['search_str'];
		$bid = $this->main_api->app_branch_id;
		
		// search for IBT DO as if skip generate GRN config has been enabled
		$err_code = $err_msg = "";
		$do_info = array();
		if($config['do_skip_generate_grn']){
			$q1 = $con->sql_query("select do.id as do_id, do.branch_id, do.active, do.do_branch_id, do.dept_id as department_id, do.do_no, ifnull(v2.id,v.id) as vendor_id
								   from do 
								   left join branch b on b.id=do.branch_id
								   left join vendor v on v.code = b.code
								   left join vendor v2 on v2.internal_code = b.code
								   where do.do_no = ".ms($search_str)." and do.do_type = 'transfer' and do.approved=1 and do.checkout=1");
			
			if($con->sql_numrows($q1) > 0){
				$do_info = $con->sql_fetchassoc($q1);
				
				if(!$do_info['active']){ // DO is inactive. prompt PO was Cancelled
					$err_code = "grr_inactive_doc";
					$err_msg = sprintf($this->err_list["grr_inactive_doc"], "DO", $search_str);
				}elseif($do_info['do_branch_id'] != $bid){			// branch was not allowed for receiving this PO
					$err_code = "grr_doc_invalid_branch";
					$err_msg = sprintf($this->err_list["grr_doc_invalid_branch"], "DO");
				}
			}else{
				$err_code = "grr_invalid_doc";
				$err_msg = sprintf($this->err_list["grr_invalid_doc"], "DO", $search_str);
			}
			
			// if found contains error message, attach into the po_info as well
			if($err_code){
				$do_info['error_code'] = $err_code;
				$do_info['error_msg'] = $err_msg;
			}
			
		}else{ // return error as IBT DO not being enabled
			$do_info['error_code'] = "grr_ibt_disabled";
			$do_info['error_msg'] = $this->err_list["grr_ibt_disabled"];
		}
		unset($err_code, $err_msg);
		
		return $do_info;
	}
	
	function save_grr(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$form = $_REQUEST;
		$grr_id = mi($form['grr_id']);
		$form['branch_id'] = $branch_id = $this->main_api->app_branch_id;
		
		// data validation for both grr and grr items
		$this->validate_data($form);

		$upd = array();
		$upd['rcv_date'] = $form['rcv_date'];
        $upd['vendor_id'] = mi($form['vendor_id']);
        $upd['department_id'] = mi($form['department_id']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['user_id'] = $this->main_api->user['id'];
		$upd['transport'] = $form['transport'];
		$upd['rcv_by'] = mi($form['rcv_by']);
		
		// it was editing, do update
		if($grr_id>0){
            $con->sql_query("update grr set ".mysql_update_by_field($upd)." where id = ".mi($grr_id)." and branch_id = ".mi($branch_id));
		}else{ // else do insertion as new GRR
			// Get Max ID
			unset($new_id);
			$new_id = $appCore->generateNewID("grr", "branch_id = ".mi($branch_id));
			
			$upd['id'] = $new_id;
			$upd['branch_id'] = $branch_id;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			// check gst status
			$prms = array();
			$prms['date'] = $upd['rcv_date'];
			$prms['vendor_id'] = $upd['vendor_id'];
			$upd['is_under_gst'] = check_gst_status($prms);
			
			$con->sql_query("insert into grr ".mysql_insert_by_field($upd));
			$grr_id = $new_id;
		}
		
		// upload GRR image
		if($_FILES['grr_image_info']['name']){
			$prms = array();
			$prms['grr_id'] = $grr_id;
			$prms['branch_id'] = $branch_id;
			$this->upload_grr_image($_FILES['grr_image_info'], $prms);
		}
		
		$this->save_grr_items($grr_id, $branch_id);
		
		// Construct Data to return		
		$ret = array();
		if($grr_id>0){			
			// get report prefix
			$q1 = $con->sql_query("select report_prefix from branch where id=".mi($branch_id));
			$binfo = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$grr_no = $binfo['report_prefix'].str_pad($grr_id, 5, '0', STR_PAD_LEFT);
			
			$ret['result'] = 1;
			$ret['grr_id'] = $grr_id;
			$ret['grr_no'] = $grr_no;
			
			log_br($this->main_api->user['id'], 'GRR', $grr_id, "Saved: ".$grr_no." (Mobile app)", $branch_id);
			unset($binfo, $grr_no);
		}else{
			$this->main_api->error_die($this->err_list["grr_create_failed"], "grr_create_failed");
		}
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function save_grr_items($grr_id, $branch_id){
		global $con, $config, $appCore;
		
		$form = $_REQUEST;
        if(!$grr_id || !$branch_id){
			$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "GRR"), "no_doc_found");
		}

        if($form['items']){
			$form['items'] = json_decode($form['items'], true);
			foreach($form['items'] as $row_id=>$item){
				$upd = array();
				$upd['po_id'] = $item['po_id'];
				$upd['doc_no'] = $item['doc_no'];
				$upd['doc_date'] = $item['doc_date'];
				$upd['type'] = $item['type'];
				$upd['ctn'] = $item['ctn'];
				$upd['pcs'] = $item['pcs'];
				$upd['amount'] = $item['amount'];
				$upd['gst_amount'] = $item['gst_amount'];
				$upd['remark'] = $item['remark'];

			    if($item['doc_no']){
					if($item['id']){
						$con->sql_query("update grr_items set ".mysql_update_by_field($upd)." where branch_id = ".mi($branch_id)." and id = ".mi($item['id']));
						$gi_id = $item['id'];
					}else{
						// Get Max ID
						unset($new_id);
						$new_id = $appCore->generateNewID("grr_items", "branch_id = ".mi($branch_id));
						
						$upd['id'] = $new_id;
						$upd['grr_id'] = $grr_id;
						$upd['branch_id'] = $branch_id;
						$con->sql_query("insert into grr_items ".mysql_insert_by_field($upd));
						$gi_id = $new_id;
					}
					
					/*$logs = join("\n", $item['gi_image_info']);
					$this->main_api->put_log($logs);*/
					
					// upload image by grr items
					if($_FILES['gi_image_info']['name'][$row_id]){
						$img_info = array();
						$img_info['name'] = $_FILES['gi_image_info']['name'][$row_id];
						$img_info['type'] = $_FILES['gi_image_info']['type'][$row_id];
						$img_info['tmp_name'] = $_FILES['gi_image_info']['tmp_name'][$row_id];
						$img_info['error'] = $_FILES['gi_image_info']['error'][$row_id];
						$img_info['size'] = $_FILES['gi_image_info']['size'][$row_id];
						
						$prms = array();
						$prms['grr_id'] = $grr_id;
						$prms['branch_id'] = $branch_id;
						$prms['grr_item_id'] = $gi_id;
						$prms['is_grr_item_image'] = true;
						$this->upload_grr_image($img_info, $prms);
					}
				}

				if ($item['po_id']>0){
					$con->sql_query("update po set delivered = 1 where po_no = ".ms($item['doc_no']));

					// PM to PO owner and FYI if
					if ($con->sql_affectedrows()>0){
						$to = array();
						$q1 = $con->sql_query("select po.user_id, bah.approval_settings
											   from po 
											   left join branch_approval_history bah on bah.branch_id=po.branch_id and bah.id=po.approval_history_id
											   where po.po_no = ".ms($item['doc_no']));
						$t = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
						
						if($t){
							$t['approval_settings'] = unserialize($t['approval_settings']);
							
							$tmp = array();
							$tmp['user_id'] = $t['user_id'];
							$tmp['approval_settings'] = $t['approval_settings']['owner'];
							$tmp['type'] = 'owner';
							$to[$t['user_id']] = $tmp;
						}

						send_pm2($to, "PO Received (".$item['doc_no'].") in GRR (Branch: ".get_branch_code($branch_id).", GRR".sprintf("%05d",$grr_id).")", "/po.php?a=view&id=".mi($item['po_id'])."&branch_id=".mi($item['po_branch_id']));
					}
				}
			}	
		}
		
		// function to re-calculate GRR total amount and qty
		$appCore->grrManager->recalculateGRRAmount($grr_id, $branch_id);
	}
	
	function validate_data(&$form){
		global $con, $config, $appCore;
		
		// GRR validation
        if(!$form['vendor_id']) $this->main_api->error_die(sprintf($this->err_list["field_is_required"], "Vendor"), "field_is_required");
        if(!$form['rcv_date']) $this->main_api->error_die(sprintf($this->err_list["field_is_required"], "Received Date"), "field_is_required");
		else{
			$rcv_date = strtotime($form['rcv_date']);
			// if found receiving date is greater than current date
			if ($rcv_date > time()){
				$this->main_api->error_die(sprintf($this->err_list["invalid_rcv_date"], $form['rcv_date']), "invalid_rcv_date");
			}
			
			if (isset($config['lower_date_limit']) && $config['lower_date_limit'] >= 0){
				$lower_limit = $config['lower_date_limit'];
				$lower_date = strtotime("-1 day",strtotime("-$lower_limit day" , strtotime("now")));

				if ($rcv_date<$lower_date){
					$this->main_api->error_die($this->err_list["rcv_date_over_limit"], "rcv_date_over_limit");
				}
			}
		}
		
		if(!$form['department_id']) $this->main_api->error_die(sprintf($this->err_list["field_is_required"], "Department"), "field_is_required");
		if(!$form['transport']) $this->main_api->error_die(sprintf($this->err_list["field_is_required"], "Lorry No"), "field_is_required");
		if(!$form['rcv_by']) $this->main_api->error_die(sprintf($this->err_list["field_is_required"], "Received By"), "field_is_required");

		// re-check the PO validity as if user trying to update existing PO
		if($form['grr_id']){
			$q1 = $con->sql_query("select gi.* from grr_items gi where gi.grr_id = ".mi($form['grr_id'])." and gi.branch_id = ".mi($form['branch_id'])." and gi.type = 'PO'");

			while($r = $con->sql_fetchassoc($q1)){
				$q2 = $con->sql_query("select id,active,vendor_id,branch_id,po_branch_id,partial_delivery,
									   delivered,department_id,cancel_date,po_no 
									   from po 
									   where approved=1 and po_no = ".ms($r['doc_no']));
				
				if($con->sql_numrows($q2) > 0){
					$po = $con->sql_fetchassoc($q2);

					// vendor ID was different between GRR and PO
					if ($form['vendor_id'] != $po['vendor_id']){
						$this->main_api->error_die($this->err_list["diff_vendor_from_po"], "diff_vendor_from_po");
					}

					// received date exceeded PO cancellation date
					if (strtotime($form['rcv_date']) >= dmy_to_time($po['cancel_date'])){
						$this->main_api->error_die(sprintf($this->err_list["exceeded_po_cancel_date"], $po['cancel_date']), "exceeded_po_cancel_date");
					}
					
					// if Dept ID was different between GRR and PO
					if($form['department_id'] != $po['department_id']){
						$this->main_api->error_die($this->err_list["po_from_diff_dept"], "po_from_diff_dept");
					}
					unset($po);
				}
				$con->sql_freeresult($q2);
			}
			$con->sql_freeresult($q1);
		}
		// end of GRR validation

		// GRR items validation
		$this->ibt_validation();

		/*$q1 = $con->sql_query("select rcv_date, department_id, vendor_id from grr where id = ".mi($form['grr_id'])." and branch_id = ".mi($form['branch_id']));
		$grr = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$form = array_merge($grr, $form);
		unset($grr);*/
		
		$doc_used = array();
		$have_po = $have_do = $have_inv = $have_other = 0;
		if($form['items']){
			$form['items'] = json_decode($form['items'], true);
			foreach($form['items'] as $row_id=>$item){
				if(trim($item['doc_no']) != ""){
					// make sure documents are not duplicated within the same GRR
					if (!isset($doc_used[$item['type']][$item['doc_no']])) $doc_used[$item['type']][$item['doc_no']] = 1;
					else $this->main_api->error_die(sprintf($this->err_list["grr_doc_no_duplicated"], $item['type'], $item['doc_no'], $form['grr_id']), "grr_doc_no_duplicated");
					
					// check document date
					if(!trim($item['doc_date'])) $this->main_api->error_die(sprintf($this->err_list["grr_item_incomplete"], "Document Date"), "grr_item_incomplete");
					
					if ($item['type'] != 'PO'){
						// check against database to see if got duplicated document no
						$q1 = $con->sql_query("select grr_id, gi.id
											   from grr_items gi
											   left join grr on grr_id = grr.id and grr.branch_id=gi.branch_id  
											   where grr.branch_id = ".mi($form['branch_id'])."
											   and grr.vendor_id = ".mi($form['vendor_id'])."
											   and gi.doc_no = ".ms($item['doc_no'])." 
											   and gi.type = ".ms($item['type'])."
											   and gi.id != ".mi($item['id'])."
											   and gi.grr_id != ".mi($form['grr_id'])."
											   and grr.active=1");

						if ($con->sql_numrows($q1)>0){
							$gi_info = $con->sql_fetchassoc($q1);
							$this->main_api->error_die(sprintf($this->err_list["grr_doc_no_duplicated"], $item['type'], $item['doc_no'], $gi_info['grr_id']), "grr_doc_no_duplicated");
							unset($gi_info);
						}
						$con->sql_freeresult($q1);
						
						// if found the ctn and pcs or amount was empty, show errors
						if((!$item['ctn'] && !$item['pcs']) || !$item['amount']) $this->main_api->error_die(sprintf($this->err_list["grr_item_incomplete"], "Ctn or Pcs and Amount"), "grr_item_incomplete");
					}else{ 			// make sure the PO exist
						$q1 = $con->sql_query("select id,active,vendor_id,branch_id,po_branch_id,partial_delivery,
											   delivered,department_id,cancel_date,po_no 
											   from po 
											   where approved=1 and po_no = ".ms($item['doc_no']));

						$p = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);

						if(!$p){
							$reset_doc_no = $appCore->grrManager->searchPONo($item['doc_no'], $p);
							if($reset_doc_no) $item['doc_no']=$reset_doc_no;						
						}

						if(!$p){ // if the PO cannot be found
							$this->main_api->error_die(sprintf($this->err_list["grr_invalid_doc"], "PO", $item['doc_no']), "grr_invalid_doc");
						}elseif(!$p['active']){ // PO is inactive. prompt PO was Cancelled
							$this->main_api->error_die(sprintf($this->err_list["grr_inactive_doc"], "PO", $item['doc_no']), "grr_inactive_doc");
						}else{
							// update the form to have PO and branch ID for save_grr_items usage
							$item['po_id'] = $form['items'][$row_id]['po_id'] = $p['id'];
							$item['po_branch_id'] = $form['items'][$row_id]['po_branch_id'] = $p['branch_id'];
							
							// vendor ID was different between GRR and PO
							if($p['vendor_id'] != $form['vendor_id']){
								$this->main_api->error_die($this->err_list["diff_vendor_from_po"], "diff_vendor_from_po");
							}

							// branch was not allowed for receiving this PO
							if(($p['po_branch_id']>0 && $p['po_branch_id'] != $form['branch_id']) ||($p['po_branch_id']==0 && $p['branch_id'] != $form['branch_id'])){
								$this->main_api->error_die(sprintf($this->err_list["grr_doc_invalid_branch"], "PO"), "grr_doc_invalid_branch");
							}

							// if found the PO has been delivered and not allowed for partial delivery
							if($p['delivered'] && !$p['partial_delivery']){
								if ($form['grr_id']==0){ // error if new grr trying re-deliver while PO does not allow partial delivery
									$this->main_api->error_die($this->err_list["grr_po_delivered"], "grr_po_delivered");
								}
							}
							
							// received date exceeded PO cancellation date
							if(strtotime($form['rcv_date']) >= dmy_to_time($p['cancel_date'])){
								$this->main_api->error_die(sprintf($this->err_list["exceeded_po_cancel_date"], $p['cancel_date']), "exceeded_po_cancel_date");
							}

							// if Dept ID was different between GRR and PO
							if($form['department_id'] != $p['department_id']){
								$this->main_api->error_die($this->err_list["po_from_diff_dept"], "po_from_diff_dept");
							}
						}
					}
					
					// IBT validation
					if($item['type'] == "PO") $have_po = true;
					elseif($item['type'] == "DO") $have_do = true;
					elseif($item['type'] == "INVOICE") $have_inv = true;
					elseif($item['type'] == "OTHER") $have_other = true;
				}elseif(!trim($item['doc_no'])) $this->main_api->error_die(sprintf($this->err_list["grr_item_incomplete"], "Document No"), "grr_item_incomplete");
			}
		}

		// newly enhance, to make sure user key in at least one document for inv, do or other when found is grn future			
		if(!$have_do && !$have_inv && !$have_other){
			$this->main_api->error_die($this->err_list["grr_doc_required"], "grr_doc_required");
		}
		
		// if found GRR having more than one INVOICE, prompt error
		if(count($doc_used['INVOICE']) >= 2){
			$this->main_api->error_die($this->err_list["grr_has_more_inv"], "grr_has_more_inv");
		}
		unset($doc_used);
		
		// check vendor by branch either required to have at least one po
		$vd_info = array();
		$q1 = $con->sql_query("select allow_grr_without_po from branch_vendor where vendor_id = ".mi($form['vendor_id'])." and branch_id = ".mi($form['branch_id']));
		
		if($con->sql_numrows($q1) > 0){
			$vd_info = $con->sql_fetchassoc($q1);
		}else{
			// check masterfile vendor either required to have at least one po
			$q2 = $con->sql_query("select allow_grr_without_po from vendor where id = ".mi($form['vendor_id']));
			$vd_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
		}
		$con->sql_freeresult($q1);
		
		// found this vendor requires at least one PO to be inserted for GRR
		if(!$vd_info['allow_grr_without_po'] && !$have_po){
			$this->main_api->error_die($this->err_list["grr_po_required"], "grr_po_required");
		}
		unset($vd_info, $have_po, $have_do, $have_inv, $have_other);
	}
	
	function ibt_validation(){
		global $con;

		$form = $_REQUEST;
		$is_ibt = $non_ibt = 0;

		if($form['items']){
			$form['items'] = json_decode($form['items'], true);
			foreach($form['items'] as $row_id=>$item){
				if($item['doc_no']!=''){
					if($item['type'] != "DO" && $item['type'] != "PO") continue;
					/*
					search grr item doc no either is below statement:
					GRR doc_type = "DO" + GRR doc_no = DO do_no, update grn column is_ibt = 1
					GRR doc_type = "PO" + GRR doc_no = po po_no, update grn column is_ibt = 1
					*/

					if($item['type'] == "DO"){
						$sql = $con->sql_query("select * from do where do_no = ".ms($item['doc_no'])." and do_branch_id = ".mi($form['branch_id']));
					}elseif($item['type'] == "PO"){
						$sql = $con->sql_query("select * from po where po_no = ".ms($item['doc_no'])." and po_branch_id = ".mi($form['branch_id'])." and is_ibt = 1");
					};

					if($con->sql_numrows($sql) > 0) $is_ibt = 1;
					else $non_ibt = 1;
					$con->sql_freeresult($sql);
				}
				if($is_ibt && $non_ibt) break; // stop the loop and rdy to display error msg
			}
		}

		// found if having both IBT and non IBT in one GRR then display error msg
		if($is_ibt && $non_ibt) $this->main_api->error_die($this->err_list["grr_ibt_error"], "grr_ibt_error");
		unset($is_ibt, $non_ibt);
	}
	
	function upload_grr_image($image_info, $prms=array()){
		global $con, $config, $appCore;
		
		// No File was Uploaded
		if(!$image_info){
			//$this->main_api->error_die($this->err_list["grr_invalid_image"], "grr_invalid_image");
			return;
		}
		
		// if GRR or Branch ID is invalid
		if(!$prms['grr_id'] || !$prms['branch_id']) $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "GRR"), "no_doc_found");
		elseif($prms['is_grr_item_image'] && !$prms['grr_item_id']) $this->main_api->error_die($this->err_list["grr_invalid_item_id"], "grr_invalid_item_id");
		
		// Set Image
		$result = $appCore->grrManager->setGRRImage($image_info, $prms);
		if(!$result['ok']){
			if($result['error_code'] && $result['error']){
				$this->main_api->error_die($result['error'], $result['error_code']);
			}else{
				$this->main_api->error_die($this->err_list["unknown_error"], "unknown_error");
			}
		}
		$image_url = $result['image_url'];
		
		if($image_url){
			log_br($this->main_api->user['id'], 'GRR', $prms['grr_id'], "GRR ID#".mi($prms['grr_id'])." Add Photo (".$result['image_name']." via Mobile app)", $prms['branch_id']);
		}
	
		/*$ret = array();
		$ret['result'] = 1;
		$ret['image_url'] = $image_url;
		
		// Return Data
		$this->main_api->respond_data($ret);*/
	}
}
?>