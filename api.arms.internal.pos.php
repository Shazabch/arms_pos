<?php
/*
11/12/2020 9:12 AM William
- Enhanced to add new api "get_popular_item_list".

11/18/2020 9:00 AM William
- Enhanced to add new api "get_banner_list".
- Enhanced to add new api "get_pos_settings".
- Enhanced to add new api "get_finalise_status".
- Enhanced api "upload_pos" to upload "member_no", "membership_guid", "point", "barcode", "member_point" and "more_info".

2/4/2021 12:37 PM William
 - Enhanced api "upload_pos" to receive receipt_ref_no and pos_guid data.
 - Added new api "upload_error".
 
 2/1/2021 2:35 PM Andy
- Enhanced to support ARMS Internal API in Sync Server.	
- Added api "get_mm_promotion_count" and "get_mm_promotion_list".

3/2/2021 12:42 PM William
- Enhanced api "upload_pos" to get pos_config.issuer_identifier payment_type.

4/2/2021 10:36 AM William
- Enhanced api "upload_pos" to auto get arms ewallet payment_type.

4/14/2021 10:43 AM William
- Enhanced api "get_banner_list" to return extra data.
- Enhanced to add api "update_void_pos" and "upload_pos_pdf_receipt".

4/20/2021 3:47 PM Andy
- Enhanced api "upload_cash_denomination" to compatible in sync server.

4/22/2021 3:50 PM William
- Enhanced api "upload_pos" to able upload pos.more_info and pos_payment.more_info.
- Enhanced api "upload_pos" to return pos date when return error message.

4/30/2021 5:19 PM Andy
- Enhanced api "upload_pos" to able to upload data_list.mm_discount.

5/3/2021 1:45 PM Andy
- Fixed api "upload_pos" pos.amount and pos.amount_tender need to include mm_discount.
*/
class API_ARMS_INTERNAL_POS {
	var $main_api = false;
	// Error List
	var $err_list = array(
		"no_doc_found" => 'No %s Found.',
		"counter_not_found" => 'Counter of device not found.',
		"pos_date_not_found"=> 'Pos Date not found.',
		"connect_sync_server_db_failed" => 'Could not connect to database %s@%s',
		"pos_duplicate" => 'POS Duplicate with Different GUID: Branch ID: %s, Counter ID: %s, Date: %s, Receipt No: %s',
		"no_mm_promo_found" => "No Mix & Match Promotion is found.",
		"create_folder_failed" => "Failed to create %s folder.",
		"invalid_format" => "Invalid %s Format.",
		"no_pos_data_found" => "No Pos Data Found.",
		"not_allow_cancel_pos" => "Cutoff Time Over, No Allow to Cancel Pos.",
		"pos_already_cancelled" => "Pos Already Cancelled",
		"pos_already_finalised" => "Counter Collection of date %s already finalise.",
	);
	
	var $sync_server_compatible_api = array('get_pos_settings', 'get_banner_list', 'upload_pos', 'upload_error', 'get_mm_promotion_count', 'get_mm_promotion_list', 'update_void_pos', 'upload_cash_denomination');
	
	function __construct($main_api){
		$this->main_api = $main_api;
	}
	
	function is_api_support_sync_server($api_name){
		if(in_array($api_name, $this->sync_server_compatible_api)){
			return true;
		}
		return false;
	}
	
	function connect_sync_server_db($die_on_failed = true){
		global $con_ss;
		
		// Already connected
		if($con_ss)	return true;
		
		include_once("sync_server/config.php");
		
		$server = $db_default_connection[0];
		$u = $db_default_connection[1];
		$p = $db_default_connection[2];
		$db = 'sync_server';
		$con_ss = new sql_db($server, $u, $p, $db, true);
	
		if(!$con_ss->db_connect_id){
			if($die_on_failed){
				//die("Error: Could not connect to database $db@$server\n" . mysql_error()."\n");
				$this->main_api->error_die(sprintf($this->err_list["connect_sync_server_db_failed"], $db, $server), "connect_sync_server_db_failed");
			}else return false;
		}
		
		return true;
	}											
	function upload_pos(){
		global $con, $appCore, $con_ss;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Check Branch
		$branch_id = mi($this->main_api->app_branch_id);
		if(!$branch_id)  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Branch Id"), "no_doc_found");
		
		// Check and Connect Sync Server
		$this->check_and_connect_sync_server();						  
		$success_list = array();
		if($_REQUEST['data_list']){
			$_REQUEST['data_list'] = json_decode($_REQUEST['data_list'], true);
			
			$suite_device_guid = $this->main_api->suite_device['guid'];
			
			//get counter id
			$counter_id = $this->get_counter_id($branch_id, $suite_device_guid);
			if(!$counter_id){
				$this->main_api->error_die(sprintf($this->err_list["counter_not_found"]), "counter_not_found");
			}
			
			$pos = $pos_payment = array();
			foreach($_REQUEST['data_list'] as $key=>$data){
				$con_ss->sql_begin_transaction();
				
				$ins_pos = array();
				$ref_id = mi($data['ref_id']);
				$receipt_no = $data['receipt_no'];
				$pos_guid = trim($data['pos_guid']);						
				$date = $data['date'];
				
				// Live Server Need Check Finalise
				if(!$this->main_api->is_sync_server){
					//check pos_finalized 
					$q4 =$con->sql_query("select * from pos_finalized  where finalized=1 and branch_id=$branch_id and date=".ms($date));
					if($con->sql_numrows($q4) > 0)  $this->return_err($success_list, "Counter Collection of date $date already finalise.");
					$con->sql_freeresult($q4);
				}				  
				if(!$ref_id)  $this->return_err($success_list, "Date: $date, Ref ID No not found.");
				if(!$receipt_no)  $this->return_err($success_list, "ID# $ref_id, Date: $date, Receipt No not found.");
				if(!$date)  $this->return_err($success_list, "ID# $ref_id, Date: $date, Receipt No: $receipt_no, Date not found.");

				// Check Duplicate POS
				$con_ss->sql_query("select id, pos_guid from pos where branch_id=$branch_id and counter_id=$counter_id and date=".ms($date)." and receipt_no=".ms($receipt_no));
				$existing_pos = $con_ss->sql_fetchassoc();
				$con_ss->sql_freeresult();
				
				if($existing_pos){
					// Found Duplicated POS
					if(!$pos_guid || !$existing_pos['pos_guid'] || $existing_pos['pos_guid'] != $pos_guid){
						$this->return_err($success_list, sprintf($this->err_list['pos_duplicate'], $branch_id, $counter_id, $date, $receipt_no));
					}
				}		  
				
				if($data['cashier_id']){
					$cashier_id = mi($data['cashier_id']);
				}elseif($data['user_name']){
					$cashier_id = $this->fnb_get_user_id($data['user_name']);
				}
				
				if(!$cashier_id)  $this->return_err($success_list, "ID# $ref_id, Date: $date, Receipt No: $receipt_no, Invalid Cashier ID.");
				// Get New ID
				//$pos_id = $appCore->generateNewID("pos", "branch_id=$branch_id and counter_id=$counter_id and date=".ms($date));
				
				if($existing_pos){
					$pos_id = $existing_pos['id'];
				}else{
					$pos_id = $this->generateNewIDFromSyncServer("pos", "branch_id=$branch_id and counter_id=$counter_id and date=".ms($date));
				}
				//insert pos data
				$ins_pos['branch_id'] = $branch_id;
				$ins_pos['counter_id'] = $counter_id;
				$ins_pos['cashier_id'] = $cashier_id;
				$ins_pos['receipt_ref_no'] = trim($data['receipt_ref_no']) ? trim($data['receipt_ref_no']) : generate_receipt_ref_no($branch_id, $counter_id, $date, $receipt_no);
				$ins_pos['id'] = $pos_id;
				$ins_pos['start_time'] = $data['start_time'];
				$ins_pos['end_time'] = $data['end_time'];
				$ins_pos['amount'] = ($data['amount'] + $data['receipt_discount'] + $data['mm_discount']);
				if($data['member_no'])   $ins_pos['member_no'] = $data['member_no'];
				if($data['membership_guid'])   $ins_pos['membership_guid'] = $data['membership_guid'];
				$ins_pos['amount_tender'] = ($data['amount']+$data['receipt_discount']+$data['mm_discount']+$data['service_charges']+$data['amount_change']);
				$ins_pos['receipt_no'] = $receipt_no;
				$ins_pos['cancel_status'] = $data['cancel_status'];
				if($data['cancel_status'] == 1){
					$ins_pos['prune_status'] = 1;
				}
				$ins_pos['point'] = $data['point'] ? mi($data['point']) : 0;
				$ins_pos['pos_time'] = $data['pos_time'];
				$ins_pos['receipt_remark'] = $data['remark'];
				$ins_pos['date'] = $date;
				$ins_pos['service_charges'] = $data['service_charges'];
				$ins_pos['amount_change'] = $data['amount_change'];
				$ins_pos['pos_guid'] = $pos_guid ? $pos_guid : $appCore->newGUID();
				
				$pos_more_info = array();
				if($data['tax_code']){   //for fnb
					//get tax info from backend 
					$q2 = $con->sql_query("select * from tax where active=1 and code=".ms($data['tax_code']));
					$tax_info = $con->sql_fetchrow($q2);
					$con->sql_freeresult($q2);
					
					if($tax_info){
						$pos_more_info['service_charges']['service_charges_rate'] = $tax_info['rate'];
						$pos_more_info['service_charges']['sc_gst_detail']['id'] = $tax_info['id'];
						$pos_more_info['service_charges']['sc_gst_detail']['code'] = $tax_info['code'];
						$pos_more_info['service_charges']['sc_gst_detail']['rate'] = $tax_info['rate'];
						$pos_more_info['service_charges']['sc_gst_detail']['indicator_receipt'] = $tax_info['indicator_receipt'];
					}else   $this->return_err($success_list, "ID# $ref_id, Date: $date, Receipt No: $receipt_no, Invalid Tax Code.");
				}
				
				$pos_more_info_allow_list=array("aeon_member_info");
				//for any pos
				if($data['pos_more_info']){
					foreach($data['pos_more_info'] as $more_info_key=>$pmi){
						if(in_array($more_info_key, $pos_more_info_allow_list)){
							$pos_more_info[$more_info_key] = $pmi;
						}
					}
				}
				
				$ins_pos['pos_more_info'] = serialize($pos_more_info);
				$ins_pos['sales_order_id'] = 0;
				$ins_pos['sales_order_branch_id'] = 0;
				$ins_pos['special_exempt_approve_by'] = 0;
				$ins_pos['total_gst_amt'] = $data['tax_amount'];
				$ins_pos['is_tax_registered'] = $data['tax_amount'] ? 1 : 0;
				$ins_pos['service_charges_gst_amt'] = $data['service_charges_tax_amt'] ? $data['service_charges_tax_amt'] : 0;
				$con_ss->sql_query("replace into pos ".mysql_insert_by_field($ins_pos));
				
				
				//insert pos payment data
				if($data['payment']){
					$payment_type = $this->get_pos_payment_type($branch_id, false);
					$pos_payment = array();
					foreach($data['payment'] as $k=>$dt){
						$p_type = '';
						$pos_payment['branch_id'] = $branch_id;
						$pos_payment['counter_id'] = $counter_id;
						$pos_payment['pos_id'] = $pos_id;
						//$pos_payment['id'] = $appCore->generateNewID("pos_payment", "branch_id=$branch_id and counter_id=$counter_id and date=".ms($date));
						$pos_payment['id'] = $this->generateNewIDFromSyncServer("pos_payment", "branch_id=$branch_id and counter_id=$counter_id and date=".ms($date));
						
						foreach($payment_type as $type=>$group_type){
							$strtolower_type = strtolower($type);
							if($strtolower_type == strtolower($dt['type'])){
								$p_type = $type;
							}
						}
						if($p_type == ''){
							$this->return_err($success_list, "ID# $ref_id, Date: $date, Receipt No: $receipt_no, Pos Payment Type(".$dt['type'].") not found.");
						}
						$pos_payment_more_info_allow_list=array("cc_info");
						$pos_payment_more_info = array();
						if($dt['more_info']){
							foreach($dt['more_info'] as $pos_payment_more_info_key=>$ppmi){
								if(in_array($pos_payment_more_info_key, $pos_payment_more_info_allow_list)){
									$pos_payment_more_info[$pos_payment_more_info_key] = $ppmi;
								}
							}
						}
						
						$pos_payment['type'] = $p_type;
						$pos_payment['date'] = $date;
						$pos_payment['amount'] = $dt['amount'];
						$pos_payment['group_type'] = $payment_type[$p_type];
						$pos_payment['remark'] = $dt['remark'];
						$pos_payment['more_info'] = serialize($pos_payment_more_info);
						$con_ss->sql_query("replace into pos_payment ".mysql_insert_by_field($pos_payment));
					}
				}
				
				if($data['receipt_discount']){
					$pos_payment_dic = array();
					$pos_payment_dic['branch_id'] = $branch_id;
					$pos_payment_dic['counter_id'] = $counter_id;
					$pos_payment_dic['pos_id'] = $pos_id;
					//$pos_payment_dic['id'] = $appCore->generateNewID("pos_payment", "branch_id=$branch_id and counter_id=$counter_id and date=".ms($date));
					$pos_payment_dic['id'] = $this->generateNewIDFromSyncServer("pos_payment", "branch_id=$branch_id and counter_id=$counter_id and date=".ms($date));
					$pos_payment_dic['type'] = 'Discount';
					$pos_payment_dic['date'] = $date;
					if($data['discount_pattern'])  $pos_payment_dic['remark'] = $data['discount_pattern'];
					$pos_payment_dic['group_type'] = 'discount';
					$pos_payment_dic['amount'] = $data['receipt_discount'];
					$pos_payment_dic['approved_by'] = $counter_id;
					$con_ss->sql_query("replace into pos_payment ".mysql_insert_by_field($pos_payment_dic));
				}
				
				if($data['mm_discount']){
					$pos_payment_dic = array();
					$pos_payment_dic['branch_id'] = $branch_id;
					$pos_payment_dic['counter_id'] = $counter_id;
					$pos_payment_dic['pos_id'] = $pos_id;
					//$pos_payment_dic['id'] = $appCore->generateNewID("pos_payment", "branch_id=$branch_id and counter_id=$counter_id and date=".ms($date));
					$pos_payment_dic['id'] = $this->generateNewIDFromSyncServer("pos_payment", "branch_id=$branch_id and counter_id=$counter_id and date=".ms($date));
					$pos_payment_dic['type'] = 'Mix & Match Total Disc';
					$pos_payment_dic['date'] = $date;
					$pos_payment_dic['remark'] = 'Mix & Match';
					$pos_payment_dic['group_type'] = 'discount';
					$pos_payment_dic['amount'] = $data['mm_discount'];
					$pos_payment_dic['approved_by'] = $counter_id;
					$con_ss->sql_query("replace into pos_payment ".mysql_insert_by_field($pos_payment_dic));
				}
				
				if($data['round']){
					$pos_payment_rounding = array();
					$pos_payment_rounding['branch_id'] = $branch_id;
					$pos_payment_rounding['counter_id'] = $counter_id;
					$pos_payment_rounding['pos_id'] = $pos_id;
					//$pos_payment_rounding['id'] = $appCore->generateNewID("pos_payment", "branch_id=$branch_id and counter_id=$counter_id and date=".ms($date));
					$pos_payment_rounding['id'] = $this->generateNewIDFromSyncServer("pos_payment", "branch_id=$branch_id and counter_id=$counter_id and date=".ms($date));
					$pos_payment_rounding['type'] = 'Rounding';
					$pos_payment_rounding['date'] = $date;
					$pos_payment_rounding['group_type'] = 'rounding';
					$pos_payment_rounding['amount'] = $data['round'];
					$con_ss->sql_query("replace into pos_payment ".mysql_insert_by_field($pos_payment_rounding));
				}
				
				//insert pos items
				if($data['items_list']){
					$pos_items = array();
					foreach($data['items_list'] as $key=>$items){
						$product_ref_id = mi($items['ref_id']);
						if(!$product_ref_id)  $this->return_err($success_list, "ID# $ref_id, Date: $date, POS Item Ref ID No not found.");
						if(!$items['sku_item_id'] && !$items['arms_code'])  $this->return_err($success_list, "ID# $ref_id, Date: $date, Item ID# $product_ref_id, Receipt No: $receipt_no, SKU Item not found.");
						
						$filter = array();
						if($items['sku_item_id']){
							$filter[] = "si.id=".ms($items['sku_item_id']);
						}
						if($items['arms_code']){
							$filter[] = "si.sku_item_code=".ms($items['arms_code']);
						}
						$filter[] = "active=1";
						$filter = implode(" and ", $filter);
						
						$q3 =$con->sql_query("select si.id, si.description from sku_items si where $filter limit 1");
						if($con->sql_numrows($q3) > 0){
							$r = $con->sql_fetchrow($q3);
							$sku_item_id = mi($r['id']);
							$description = $r['description'];
						}
						$con->sql_freeresult($q3);
						
						//sku item not found
						if(!$sku_item_id)  $this->return_err($success_list, "ID# $ref_id, Date: $date, Receipt No: $receipt_no, SKU Item not found.");
						
						$pos_items['sku_item_id'] = $sku_item_id;
						$pos_items['sku_description'] = $description;
						//$pos_items['id'] = $appCore->generateNewID("pos_items", "branch_id=$branch_id and pos_id=$pos_id and counter_id=$counter_id and date=".ms($date));
						$pos_items['id'] = $this->generateNewIDFromSyncServer("pos_items", "branch_id=$branch_id and pos_id=$pos_id and counter_id=$counter_id and date=".ms($date));
						$pos_items['barcode'] = $items['barcode'] ? $items['barcode']: '';
						$pos_items['item_id'] = ($key+1);
						$pos_items['branch_id'] = $branch_id;
						$pos_items['counter_id'] = $counter_id;
						$pos_items['pos_id'] = $pos_id;
						$pos_items['date'] = $date;
						$pos_items['qty'] = $items['qty'];
						$pos_items['price'] = $items['price'];
						if($items['tax_amount']){
							$pos_items['inclusive_tax'] = 1;
							$pos_items['tax_amount'] = $items['tax_amount'];
							if(trim($items['tax_code'])){
								$sql_tax = $con->sql_query("select * from tax where active=1 and code=".ms($items['tax_code']));
								$tax_info2 = $con->sql_fetchrow($sql_tax);
								$con->sql_freeresult($sql_tax);
								
								$pos_items['tax_code'] = $tax_info2['code'];
								$pos_items['tax_indicator'] = $tax_info2['indicator_receipt'];
								$pos_items['tax_rate'] = $tax_info2['rate'];
							}elseif($tax_info){
								//for fnb only
								$pos_items['tax_code'] = $tax_info['code'];
								$pos_items['tax_indicator'] = $tax_info['indicator_receipt'];
								$pos_items['tax_rate'] = $tax_info['rate'];
							}
						}
						if($items['member_point_list']){
							$member_point_list = array();
							if($items['member_point_list']['type'])  $member_point_list['type'] = $items['member_point_list']['type'];
							if($items['member_point_list']['settings'])  $member_point_list['settings'] = $items['member_point_list']['settings'];
							$member_point_list['amount'] = $items['member_point_list']['amount'];
							$member_point_list['point'] = $items['member_point_list']['point'];
							
							if($member_point_list) $pos_items['member_point'] = serialize($member_point_list);
						}
						if($items['item_more_info']){
							$item_more_info = array();
							if($items['item_more_info']['discount_str'])  $item_more_info['discount_str'] = $items['item_more_info']['discount_str'];
							if($item_more_info)  $pos_items['more_info'] = serialize($item_more_info);
						}
						$pos_items['discount'] = $items['discount'];
						$pos_items['discount2'] = $items['discount2'];
						$pos_items['item_discount_by'] = ($pos_items['discount2'] > 0 || $pos_items['discount'] > 0) ? $cashier_id :0;
						$pos_items['promotion_id'] = 0;
						$pos_items['return_by'] = 0;
						$pos_items['open_code_by'] = 0;
						$pos_items['verify_code_by'] = 0;
						$pos_items['before_tax_price'] = ($pos_items['price']-$pos_items['discount']-$pos_items['discount2']-$pos_items['tax_amount']);
						$pos_items['remark'] = $items['remark'];
						
						$con_ss->sql_query("replace into pos_items ".mysql_insert_by_field($pos_items));
					}
				}
				
				// Sync Server
				if($this->main_api->is_sync_server){
					$con_ss->sql_query("update pos set transaction_sync=1 where branch_id=$branch_id and counter_id=$counter_id and date=".ms($date)." and id=$pos_id");
				}
				
				$con_ss->sql_commit();
				$success_list[] = $ref_id;
			}
			
		}
		$ret = array();
		$ret['result'] = 1;
		$ret['success_id_list'] = $success_list;
		
		unset($success_list);
		
		// return data
		$this->main_api->respond_data($ret);
	}
	
	//upload cash advance 
	function upload_cash_advance(){
		global $con;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Check Branch
		$branch_id = mi($this->main_api->app_branch_id);
		if(!$branch_id)  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Branch Id"), "no_doc_found");
		
		$success_list = array();
		if($_REQUEST['data_list']){
			$dt_list = json_decode($_REQUEST['data_list'], true);
			
			$suite_device_guid = $this->main_api->suite_device['guid'];
			
			//get counter id
			$counter_id = $this->get_counter_id($branch_id, $suite_device_guid);
			if(!$counter_id){
				$this->main_api->error_die(sprintf($this->err_list["counter_not_found"]), "counter_not_found");
			}
			
			foreach($dt_list['data_list'] as $key=>$val){
				$con->sql_begin_transaction();
				
				$upd = array();
				$ref_id = mi($val['ref_id']);
				$date = $val['date'];
				
				if(!$ref_id)  $this->return_err($success_list, "Cash Advance Ref ID No not found.");
				if(!$date)  $this->return_err($success_list, "ID# $ref_id, Invalid Cash Advance Date.");
				
				// Live Server Need Check Finalise
				if(!$this->main_api->is_sync_server){
					//check pos_finalized 
					$q_f = $con->sql_query("select * from pos_finalized  where finalized=1 and branch_id=$branch_id and date=".ms($date));
					if($con->sql_numrows($q_f) > 0)  $this->return_err($success_list, "Counter Collection of date $date already finalise.");
					$con->sql_freeresult($q_f);
				}	
				
				if($val['user_id']){
					$user_id = mi($val['user_id']);
				}elseif($val['user_name']){
					$user_id = $this->fnb_get_user_id($val['user_name']);
				}
				
				if(!$user_id)  $this->return_err($success_list, "ID# $ref_id, Invalid Cash Advance User ID.");
				if($val['collected_by_user_id']){
					$collected_by = mi($val['collected_by_user_id']);
				}elseif($val['collected_by_username']){
					$collected_by = $this->fnb_get_user_id($val['collected_by_username']);
				}
				
				if(!$collected_by)  $this->return_err($success_list, "ID# $ref_id, Invalid Cash Advance Collected By ID.");
				
				$upd['branch_id'] = $branch_id;
				$upd['counter_id'] = $counter_id;
				$upd['user_id'] = $user_id;
				$upd['date'] = $date;
				$upd['collected_by'] = $collected_by;
				$upd['type'] = 'ADVANCE';
				$upd['amount'] = $val['amount'];
				$upd['oamount'] = $val['oamount'];
				$upd['timestamp'] = $val['time'];
				$upd['remark'] = $val['remark'];
				$con->sql_query("insert into pos_cash_history ".mysql_insert_by_field($upd));
				
				//check pos_finalized 
				$q1 =$con->sql_query("select * from pos_finalized  where finalized=1 and branch_id=$branch_id and date=".ms($date));
				if($con->sql_numrows($q1) > 0)  $this->return_err($success_list, "Counter Collection of date $date already finalise.");
				$con->sql_freeresult($q1);
				
				$con->sql_commit();
				$success_list[] = $ref_id;
			}
		}
			
		$ret = array();
		$ret['result'] = 1;
		$ret['success_id_list'] = $success_list;
		
		unset($success_list);
		
		// return data
		$this->main_api->respond_data($ret);
	}
	
	//upload cash denomination
	function upload_cash_denomination(){
		global $con, $appCore, $config, $con_ss;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Check Branch
		$branch_id = mi($this->main_api->app_branch_id);
		if(!$branch_id)  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Branch Id"), "no_doc_found");
		
		// Check and Connect Sync Server
		$this->check_and_connect_sync_server();	
		
		$success_list = array();
		if($_REQUEST['data_list']){
			$dt_list = json_decode($_REQUEST['data_list'], true);
			
			$suite_device_guid = $this->main_api->suite_device['guid'];
			
			//get counter id
			$counter_id = $this->get_counter_id($branch_id, $suite_device_guid);
			if(!$counter_id){
				$this->main_api->error_die(sprintf($this->err_list["counter_not_found"]), "counter_not_found");
			}
			
			$normal_payment_type = $this->get_pos_payment_type($branch_id, true);
			
			foreach($dt_list['data_list'] as $key=>$val){
				$con_ss->sql_begin_transaction();
				
				$upd = array();
				$ref_id = mi($val['ref_id']);
				$date = $val['date'];
				
				if(!$ref_id)  $this->return_err($success_list, "Cash Denomination Ref ID No not found.");
				if(!$date)  $this->return_err($success_list, "ID# $ref_id, Invalid Cash Denomination Date.");
				
				// Live Server Need Check Finalise
				if(!$this->main_api->is_sync_server){
					//check pos_finalized 
					$q_f = $con->sql_query("select * from pos_finalized  where finalized=1 and branch_id=$branch_id and date=".ms($date));
					if($con->sql_numrows($q_f) > 0)  $this->return_err($success_list, "Counter Collection of date $date already finalise.");
					$con->sql_freeresult($q_f);
				}
				
				if($val['user_id']){
					$user_id = mi($val['user_id']);
				}elseif($val['user_name']){
					$user_id = $this->fnb_get_user_id($val['user_name']);
				}
				
				if(!$user_id)  $this->return_err($success_list, "ID# $ref_id, Date($date), Cash Denomination User ID not found.");
				$upd['id'] = $this->generateNewIDFromSyncServer("pos_cash_domination", "branch_id=$branch_id and counter_id=$counter_id and date=".ms($date));
				$upd['branch_id'] = $branch_id;
				$upd['counter_id'] = $counter_id;
				$upd['user_id'] = $user_id;
				
				$dt_list2 = array();
				if($val['denomination_list']){
					if($val['denomination_list']['note']){
						if($config['cash_domination_notes']){
							$cash_domination_notes = array();
							foreach($config['cash_domination_notes'] as $type=>$dt){
								$cash_domination_notes[$type] = mf($dt['value']);
							}
							
							if($cash_domination_notes){
								foreach($val['denomination_list']['note'] as $key_val=>$v){
									if(in_array(mf($key_val), $cash_domination_notes)){
										$note_type = array_search($key_val, $cash_domination_notes);
										$dt_list2[$note_type] = $v;
									}else{
										$this->return_err($success_list, "ID# $ref_id, Date($date), Cash Denomination cash payment type ($key_val) not found.");
									}
								}
							}
						}else{
							$this->return_err($success_list, "ID# $ref_id, Date($date), Backend Config(cash_domination_notes) not active.");
						}
					}
					
					if($val['denomination_list']['payment_type']){
						foreach($val['denomination_list']['payment_type'] as $type2=>$v2){
							$payment_type = ucwords(strtolower($type2));
							
							$p_type = '';
							foreach($normal_payment_type as $type=>$group_type){
								$strtolower_type = strtolower($type);
								if($strtolower_type == strtolower($type2)){
									$p_type = $type;
								}
							}
							
							if($p_type != ''){
								$dt_list2[$p_type] = $v2;
							}elseif($config['counter_collection_extra_payment_type'] && in_array($payment_type, $config['counter_collection_extra_payment_type'])){
								$dt_list2[$payment_type] = $v2;
							}else{
								$this->return_err($success_list, "ID# $ref_id, Date($date), Cash Denomination payment type ($type2) not found.");
							}
						}
					}
				}
				
				$upd['data'] = serialize($dt_list2);
				$upd['date'] = $date;
				$upd['timestamp'] = $val['time'];
				
				$con_ss->sql_query("insert into pos_cash_domination ".mysql_insert_by_field($upd));
				
				//check pos_finalized 
				//$q1 =$con->sql_query("select * from pos_finalized  where finalized=1 and branch_id=$branch_id and date=".ms($date));
				//if($con->sql_numrows($q1) > 0)  $this->return_err($success_list, "Counter Collection of date $date already finalise.");
				//$con->sql_freeresult($q1);
				
				$con_ss->sql_commit();
				$success_list[] = $ref_id;
			}
		}
		
		$ret = array();
		$ret['result'] = 1;
		$ret['success_id_list'] = $success_list;
		
		unset($success_list);
		
		$this->main_api->respond_data($ret);
	}
	
	//return error message with success_id_list
	function return_err($success_list = array(), $err){
		$ret = array();
		$ret['result'] = 1;
		$ret['success_id_list'] = $success_list;
		$ret['err'] = $err;
		
		unset($err, $success_list);
		
		$this->main_api->respond_data($ret);
	}
	
	//get all payment type, include extra payment type
	function get_pos_payment_type($branch_id, $denomination=false){
		global $con, $config, $pos_config;
		
		$payment_type = array();
		
		if(!$denomination) $payment_type['Cash'] = 'standard';
		if($denomination) $payment_type['Float'] = 'standard';
		$payment_type['Others'] = 'standard';
		
		$q1 = $con->sql_query("select * from pos_settings where setting_name = 'payment_type' and branch_id = ".mi($branch_id));
		$ps_info = $con->sql_fetchrow($q1);
		$ps_payment_type = unserialize($ps_info['setting_value']);
		$con->sql_freeresult($q1);
		
		if($ps_payment_type){
			foreach($ps_payment_type as $ptype=>$val){
				
				$ori_ptype = $ptype;
				
				if(strpos(strtolower($ptype), "credit_card")===0){
					$ptype = str_replace("_", " ",$ptype);	// only replace "_" to " " if it is credit card
				}
				$ptype = ucwords($ptype);
				if($ptype == "Credit Card") $ptype = "Credit Cards";
				
				if(!$val) continue;	// in-active
				
				$payment_type[$ptype] = 'standard';
			}
		}
		
		if($pos_config['issuer_identifier']){
			foreach($pos_config['issuer_identifier'] as $key1=>$dt_list){
				if($dt_list){
					foreach($dt_list as $key=> $val2){
						if($dt_list[0]){
							$payment_type[$dt_list[0]] = 'standard';
						}
					}
				}
			}
		}
		
		if($config['ewallet_settings']){
			foreach($config['ewallet_settings'] as $integration_type=>$data_list){
				if($data_list['active']){
					$has_integrator_list = $data_list['has_integrator_list'];
					if($has_integrator_list){
						$q2 = $con->sql_query("select * from pos_settings where setting_name = 'ewallet_type' and branch_id = ".mi($branch_id));
						$ewallet_list = $con->sql_fetchrow($q2);
						$ewallet_payment_type = unserialize($ewallet_list['setting_value']);
						$con->sql_freeresult($q2);
						if($ewallet_payment_type){
							foreach($ewallet_payment_type as $ewallet_type=>$val){
								if($val == 1){
									$ewallet_type_data = explode("_", $ewallet_type);
									if($ewallet_type_data[0] == $integration_type){
										$payment_type["ewallet_".$ewallet_type] = 'ewallet';
									}
								}
							}
							$payment_type["ewallet_ipay88_all"] = 'ewallet';
						}
					}else{
						$ewallet_type = "ewallet_".$integration_type;
						$payment_type[$ewallet_type] = 'ewallet';
					}
				}
			}
		}
		
		if($config['counter_collection_extra_payment_type']){
			foreach($config['counter_collection_extra_payment_type'] as $k=>$val){
				$extra_ptype = ucwords(strtolower($val));
				
				$payment_type[$extra_ptype] = 'custom';
			}
		}
		return $payment_type;
	}
	
	//get counter id by suite_device_guid
	function get_counter_id($branch_id, $suite_device_guid){
		global $con, $appCore;
		
		$q = $con->sql_query("select * from counter_settings where branch_id=".mi($branch_id)." and suite_device_guid=".ms($suite_device_guid)." and active=1");
		$r = $con->sql_fetchrow($q);
		$con->sql_freeresult($q);
		$counter_id = mi($r['id']);
		
		return $counter_id;
	}
	
	//for fnb only
	function fnb_get_user_id($user_name){
		global $con, $appCore;
		
		$user_name = ms($user_name);
		if($user_name == '') return false;
		
		$q = $con->sql_query("select id from user where active=1 and fnb_username=$user_name");
		$r = $con->sql_fetchrow($q);
		$con->sql_freeresult($q);
		$user_id = mi($r['id']);
		
		return $user_id;
	}
	
	/*function get_popular_item_list(){
		global $con, $config;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Check Branch
		$branch_id = mi($this->main_api->app_branch_id);
		if(!$branch_id)  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Branch Id"), "no_doc_found");
		
		$start_from = mi($_REQUEST['start_from']);
		$limit_count = mi($_REQUEST['limit']);
		
		$popular_item_branch_id = 1;
		if($branch_id != 1){
			$pos_popular_settings = array();
			$con->sql_query("select * from pos_popular_settings where branch_id=$branch_id");
			$setting_data = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$pos_popular_settings['use_own_listing'] = $setting_data['setting_value'];
			if($pos_popular_settings['use_own_listing'] == 1){
				$popular_item_branch_id = $branch_id;
			}
		}
		
		$filter = array();
		$filter[] = "ppsi.active=1";
		$filter[] = "ppsi.branch_id=$popular_item_branch_id";
		
		if($start_from >= 0 && $limit_count > 0){
			$limit = "limit $start_from, $limit_count";
		}
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Construct Data to return
		$ret = array();
		$ret['result'] = 1;
		
		$sql = "select ppsi.id, ppsi.sequence, ppsi.sku_item_id, si.description, si.selling_price
			from pos_popular_sku_items ppsi
			left join sku_items si on ppsi.sku_item_id=si.id
			$str_filter
			order by sequence
			$limit";
			
		//print $sql;exit;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$ret['popular_item_data'][] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($ret['popular_item_data']){
			// Return Data				
			$this->main_api->respond_data($ret);
		}else{
			$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Popular Item Data"), "no_doc_found");
		}	
	}*/
	
	function get_banner_list(){
		global $con, $config, $appCore, $con_ss;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		$filter = array();
		
		$last_update = trim($_REQUEST['last_update']);
		$banner_name = trim($_REQUEST['banner_name']);
		
		if($banner_name)	$filter[] = "spbi.banner_name=".ms($banner_name);
		if($filter) $str_filter = join(' and ', $filter);
		else  $str_filter = 1;
		
		// Check and Connect Sync Server
		$this->check_and_connect_sync_server();
		
		// Got provide last update, can check got changes or not
		$con->sql_query("select max(last_update) as latest_update from suite_pos_banner_items spbi where $str_filter");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$latest_update = $tmp['latest_update'];
		
		$ret = array();
		$ret['result'] = 1;
		$ret['last_update'] = $latest_update;
		$ret['banner_list'] = array();
		
		if($latest_update){	// Got Data
			if($last_update){	// client got provide last update
				if(strtotime($last_update) == strtotime($latest_update)){
					unset($ret['banner_list']);
					$ret['no_change'] = 1;
				}
			}
			
			if(!isset($ret['no_change'])){
				$item_list = array();
				$con->sql_query("select *
					from suite_pos_banner_items spbi
					where $str_filter and spbi.active = 1
					order by sequence");
				while($r = $con->sql_fetchassoc()){
					$item_list[$r['id']] = $r;
				}
				$con->sql_freeresult();
				
				if($item_list){
					$required_fields = array('id', 'item_type', 'image_click_link', 'item_url', 'sequence');
					foreach($item_list as $r){
						$tmp = array();
						foreach($required_fields as $field){
							$tmp[$field] = $r[$field];
						}
						$ret['banner_list'][] = $tmp;
					}
				}
			}
		}
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function get_pos_settings(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
			
		
		// Check Branch
		$branch_id = mi($this->main_api->app_branch_id);
		if(!$branch_id)  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Branch Id"), "no_doc_found");
		
		if($_REQUEST['setting_list']){
			$setting_list_data = json_decode($_REQUEST['setting_list'], true);

			$ret = array();
			$ret['result'] = 1;
			$ret['data'] = array();
			
			//
			if(in_array('receipt_header', $setting_list_data) ||  in_array('receipt_footer', $setting_list_data)){
				$con->sql_query("select receipt_header, receipt_footer, lastping from branch where id=$branch_id");
				$receipt_header_footer = $con->sql_fetchassoc();
				
				if(in_array('receipt_header', $setting_list_data)){
					$header = array();
					$header['setting_name'] = 'receipt_header';
					$header['setting_value'] = $receipt_header_footer['receipt_header'];
					$header['last_update'] = $receipt_header_footer['lastping'];
					$ret['data'][] = $header;
				}
				
				if(in_array('receipt_footer', $setting_list_data)){
					$footer = array();
					$footer['setting_name'] = 'receipt_footer';
					$footer['setting_value'] = $receipt_header_footer['receipt_footer'];
					$footer['last_update'] = $receipt_header_footer['lastping'];
					$ret['data'][] = $footer;
				}
				$con->sql_freeresult();
			}
			$setting_list = array_map('ms', $setting_list_data);
			$setting_list = implode(',', $setting_list);
			
			
			$filter = array();
			$filter[] = "ps.setting_name in(".$setting_list.")";
			$filter[] = "ps.branch_id=$branch_id";
			
			$str_filter = 'where '.join(' and ', $filter);
			$con->sql_query("select ps.*
				from pos_settings ps
				$str_filter
				order by ps.last_update");
			while($r = $con->sql_fetchassoc()){
				$tmp = array();
				$tmp['setting_name'] = $r['setting_name'];
				$setting_value = @unserialize($r['setting_value']);
				$tmp['setting_value'] = $setting_value === false ? $r['setting_value'] : unserialize($r['setting_value']);
				$tmp['last_update'] = $r['last_update'];
				
				$ret['data'][] = $tmp;
			}
			$con->sql_freeresult();
		}else{
			$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Settings Name"), "no_doc_found");
		}
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function get_finalise_status(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
			
		
		// Check Branch
		$branch_id = mi($this->main_api->app_branch_id);
		if(!$branch_id)  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Branch Id"), "no_doc_found");
		if(!$_REQUEST['date'])  $this->main_api->error_die(sprintf($this->err_list["pos_date_not_found"]), "pos_date_not_found");
		
		$ret = array();
		$ret['result'] = 1;
		
		$filter = array();
		$filter[] = "pf.branch_id=$branch_id";
		$filter[] = "pf.date=".ms($_REQUEST['date']);
		
		$str_filter = 'where '.join(' and ', $filter);
		$con->sql_query("select pf.finalized, pf.finalize_timestamp
			from pos_finalized pf $str_filter");
		$r = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$ret['finalized'] = $r['finalized'];
		$ret['finalize_timestamp'] = $r['finalize_timestamp'];
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function upload_error(){
		global $con, $config, $appCore, $con_ss;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Check Branch
		$branch_id = mi($this->main_api->app_branch_id);
		if(!$branch_id)  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Branch Id"), "no_doc_found");
		
		//check date
		$date = $_REQUEST['date'];
		if(!$date)  $this->main_api->error_die(sprintf($this->err_list["pos_date_not_found"]), "pos_date_not_found");
	
		//get counter id
		$suite_device_guid = $this->main_api->suite_device['guid'];
		$counter_id = $this->get_counter_id($branch_id, $suite_device_guid);
		if(!$counter_id){
			$this->main_api->error_die(sprintf($this->err_list["counter_not_found"]), "counter_not_found");
		}
		
		// Check and Connect Sync Server
		$this->check_and_connect_sync_server();						  
		$err = trim($_REQUEST['err']);
		
		$con_ss->sql_query("select * from pos_counter_collection_tracking where branch_id=$branch_id and counter_id=$counter_id and date=".ms($date));
		$data = $con_ss->sql_fetchassoc();
		$con_ss->sql_freeresult();
		
		$ret = array();
		$ret['result'] = 1;
		
		if(!$data && !$err){
			$this->main_api->respond_data($ret);
		}
		
		if($data['error'] == $err){	// Same Error
			$this->main_api->respond_data($ret);
		}
		
		if($err || $this->main_api->is_sync_server){
			$upd = array();
			$upd['error'] = $err;
			$upd['branch_id'] = $branch_id;
			$upd['counter_id'] = $counter_id;
			$upd['date'] = $date;
			$upd['finalized'] = 1;
			$con_ss->sql_query("replace into pos_counter_collection_tracking ".mysql_insert_by_field($upd));
		}else{
			$con_ss->sql_query("delete from pos_counter_collection_tracking where branch_id = ".mi($branch_id)." and counter_id = ".mi($counter_id)." and date = ".ms($date));
		}
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	function check_and_connect_sync_server(){
		global $con_ss, $con;
		
		if($this->main_api->is_sync_server){
			// Connection for Sync Server
			if(!$con_ss)	$this->connect_sync_server_db();
		}else{
			$con_ss = $con;
		}
	}
	
	function generateNewIDFromSyncServer($tbl_name, $filters="", $prms=array()){
		global $con_ss, $con;
		
		if(!$tbl_name) return; // stop if no table name provided
		
		// Check and Connect Sync Server
		$this->check_and_connect_sync_server();
		
		$filter = $for_update = "";
		// if found filters included from the params
		if(isset($filters) && $filters){
			if(is_array($filters)){ // join it if the filter was setup in array style
				$filter = "where ".join(" and ", $filters);
			}else $filter = "where ".$filters; // otherwise straight put the filter without joining it
		}
		
		// lock the table by default if no set to skip it
		if(!$prms['skip_update']) $for_update = " for update";
		
		// if got set different column name to get the ID
		$col_name = "id"; // default as "id"
		if($prms['col_name']) $col_name = $prms['col_name'];
		
		$q1 = $con_ss->sql_query("select max($col_name) as max_id from $tbl_name ".$filter.$for_update);
		$tmp = $con_ss->sql_fetchassoc($q1);
		$con_ss->sql_freeresult($q1);
		$new_id = mi($tmp['max_id'])+1;
		
		return $new_id;
	}
	
	function get_mm_promotion_count(){
		global $con;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		//if(!$this->main_api->user){
		//	$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		//}
		
		$unique_promo_id_list = $_REQUEST['unique_promo_id_list'];
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		$filter = array();
		$filter[] = "p.promo_type='mix_and_match'";
		if($unique_promo_id_list){ // it is filter by debtor id
			$filter_or = array();
			foreach($unique_promo_id_list as $unique_promo_id){
				$search_promo_id = mi(substr($unique_promo_id, 0, -3));
				$search_promo_bid = mi(substr($unique_promo_id, -3));
				$filter_or[] = "(p.id=$search_promo_id and p.branch_id=$search_promo_bid)";
			}
			$filter[] = "(".join(' or ', $filter_or).")";
		}
		
		if($min_changes_row_index > 0){
			$extra_join = "left join tmp_trigger_log tmp on tmp.tablename='promotion' and tmp.id=(p.id*1000+p.branch_id)";
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}
		
		// Check Branch
		$branch_id = mi($this->main_api->app_branch_id);
		if($branch_id>0){
			$bcode = get_branch_code($branch_id);
			$filter[] = "p.promo_branch_id like '%\"".$bcode."\"%'";
		}
		
		$today = date("Y-m-d");
		$filter[] = "p.date_to>=".ms($today);
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Count all Brand
		$this->main_api->put_log("Checking Brand Count.");
		$q1 = $con->sql_query("select count(*) as c 
							   from promotion p
							   $extra_join
							   $str_filter");
		$tmp = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
		$ret['count'] = $tmp['c'];
		unset($tmp);
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function get_mm_promotion_list(){
		global $con, $config;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		$start_from = mi($_REQUEST['start_from']);
		$limit_count = mi($_REQUEST['limit']);
		$unique_promo_id_list = $_REQUEST['unique_promo_id_list'];
		$min_changes_row_index = mi($_REQUEST['min_changes_row_index']);
		
		$filter = array();
		//$filter[] = "d.active=1";
		
		if($unique_promo_id_list){ // it is filter by debtor id
			$filter_or = array();
			foreach($unique_promo_id_list as $unique_promo_id){
				$search_promo_id = mi(substr($unique_promo_id, 0, -3));
				$search_promo_bid = mi(substr($unique_promo_id, -3));
				$filter_or[] = "(p.id=$search_promo_id and p.branch_id=$search_promo_bid)";
			}
			$filter[] = "(".join(' or ', $filter_or).")";
		}
		
		if(!$unique_promo_id_list && $start_from >= 0 && $limit_count > 0){
			$limit = "limit $start_from, $limit_count";
		}
		
		if($min_changes_row_index>0){
			$filter[] = "tmp.row_index>".mi($min_changes_row_index);
		}

		// Check Branch
		$branch_id = mi($this->main_api->app_branch_id);
		if($branch_id>0){
			$bcode = get_branch_code($branch_id);
			$filter[] = "p.promo_branch_id like '%\"".$bcode."\"%'";
		}
		
		$today = date("Y-m-d");
		$filter[] = "p.date_to>=".ms($today);
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		// Construct Data to return
		$ret = array();
		$ret['result'] = 1;
		
		$sql = "select p.*, ifnull(tmp.row_index,0) as changes_row_index
			from promotion p
			left join tmp_trigger_log tmp on tmp.tablename='promotion' and tmp.id=(p.id*1000+p.branch_id)
			$str_filter
			order by changes_row_index
			$limit";
			
		//print $sql;exit;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$data = array();
			
			$promo_bid = mi($r['branch_id']);
			$promo_id = mi($r['id']);
			
			// Header
			$data['branch_id'] = $promo_bid;
			$data['promo_id'] = $promo_id;
			$data['unique_promo_id'] = ($r['id']*1000)+$r['branch_id'];
			$data['title'] = trim($r['title']);
			$data['date_from'] = trim($r['date_from']);
			$data['date_to'] = trim($r['date_to']);
			$data['time_from'] = trim($r['time_from']);
			$data['time_to'] = trim($r['time_to']);
			$data['active'] = mi($r['active']);
			$data['status'] = mi($r['status']);
			$data['approved'] = mi($r['approved']);
			$data['changes_row_index'] = mi($r['changes_row_index']);
			$data['group_list'] = array();
			
			// Items
			$q2 = $con->sql_query($sql = "select pi.* 
				from promotion_mix_n_match_items pi
				where pi.branch_id=$promo_bid and pi.promo_id=$promo_id
				order by pi.group_id, pi.sequence_num");
			//print $sql;exit;
			while($pi = $con->sql_fetchassoc($q2)){
				$group_id = mi($pi['group_id']);
				
				if(!isset($data['group_list'][$group_id])){
					// header
					$data['group_list'][$group_id]['group_id'] = $group_id;
					$data['group_list'][$group_id]['header']['receipt_limit'] = mi($pi['receipt_limit']);
					$data['group_list'][$group_id]['header']['disc_prefer_type'] = mi($pi['disc_prefer_type']);
					$data['group_list'][$group_id]['header']['for_member'] = mi($pi['for_member']);
					$data['group_list'][$group_id]['header']['for_non_member'] = mi($pi['for_non_member']);
					$data['group_list'][$group_id]['header']['follow_sequence'] = mi($pi['follow_sequence']);
					$data['group_list'][$group_id]['header']['control_type'] = mi($pi['control_type']);
					//$data['group_list'][$group_id]['header']['item_category_point_inherit_data'] = unserialize($pi['item_category_point_inherit_data']);
					$data['group_list'][$group_id]['header']['for_member_type'] = unserialize($pi['for_member_type']);
					//$data['group_list'][$group_id]['header']['prompt_available'] = mi($pi['prompt_available']);
					//$data['group_list'][$group_id]['header']['extra_info'] = unserialize($pi['extra_info']);
				}
				// item list
				$item = array();
				$item['disc_condition'] = unserialize($pi['disc_condition']);
				$pi['disc_target_info'] =unserialize($pi['disc_target_info']);
				
				$item['disc_target_type'] = trim($pi['disc_target_type']);
				$item['disc_target_value'] = trim($pi['disc_target_value']);
				$item['disc_target_desc'] = trim($pi['disc_target_info']['description']);
				$item['disc_target_sku_type'] = trim($pi['disc_target_sku_type']);
				$item['disc_target_price_type'] = trim($pi['disc_target_price_type']);
				$item['disc_target_price_range_from'] = trim($pi['disc_target_price_range_from']);
				$item['disc_target_price_range_to'] = trim($pi['disc_target_price_range_to']);
				
				$item['disc_by_type'] = trim($pi['disc_by_type']);
				$item['disc_by_value'] = trim($pi['disc_by_value']);
				$item['disc_by_qty'] = trim($pi['disc_by_qty']);
				$item['qty_from'] = trim($pi['qty_from']);
				$item['disc_limit'] = trim($pi['disc_limit']);
				$item['loop_limit'] = trim($pi['loop_limit']);
				//$item['item_remark'] = trim($pi['item_remark']);
				$item['receipt_description'] = trim($pi['receipt_description']);
				//get_complete_disc_condition_info($pi['disc_condition']);
				//$item['item_info'] = get_disc_condition_item_info($pi['disc_target_type'], $disc_target_value);
									
				$data['group_list'][$group_id]['item_list'][$pi['id']] = $item;
			}
			$con->sql_freeresult($q2);
			
			//$ret['promo_data'][] = $r;
			$ret['promo_data'][] = $data;
		}
		$con->sql_freeresult($q1);
		
		if($ret['promo_data']){
			// Return Data
			$this->main_api->respond_data($ret);
		}else{
			$this->main_api->error_die($this->err_list["no_mm_promo_found"], "no_mm_promo_found");
		}
	}
	
	function update_void_pos(){
		global $con, $appCore, $con_ss;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Check Branch
		$branch_id = mi($this->main_api->app_branch_id);
		if(!$branch_id)  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Branch Id"), "no_doc_found");
		
		if(!$_REQUEST['cashier_id'])  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Cashier Id"), "no_doc_found");
		if(!$_REQUEST['receipt_no'])  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Receipt No"), "no_doc_found");
		if(!$_REQUEST['verified_by'])  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Verified By"), "no_doc_found");
		if(!$_REQUEST['date'])  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Date"), "no_doc_found");
		if(!$_REQUEST['cancelled_time'])  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Cancelled Time"), "no_doc_found");
		if(!$_REQUEST['counter_id'])  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Counter Id"), "no_doc_found");
		
		// Check and Connect Sync Server
		$this->check_and_connect_sync_server();
		
		$current_date = date('Y-m-d');
		if(strtotime($current_date) > strtotime(date("Y-m-d", strtotime($_REQUEST['date'])))){
			if(strtotime(date("Y-m-d", strtotime($_REQUEST['date']))) == strtotime(date("Y-m-d", strtotime("-1 day", strtotime($current_date))))){
				//cutoff time
				$pos_setting_data = array();
				$con->sql_query($q1="select * from pos_settings where branch_id=".mi($branch_id)." and setting_name in('hour_start', 'minute_start')");
				while($r1 =$con->sql_fetchassoc()){
					$pos_setting_data[$r1['setting_name']] = $r1['setting_value'];
				}
				$con->sql_freeresult();
				
				$cutoff_time = $pos_setting_data['hour_start'].":".$pos_setting_data['minute_start'];
				$current_time = date('H:i');
				if(strtotime($current_time) >  strtotime(date("H:i", strtotime($cutoff_time)))){
					$this->main_api->error_die($this->err_list["not_allow_cancel_pos"], "not_allow_cancel_pos");
				}
			}else{
				$this->main_api->error_die($this->err_list["not_allow_cancel_pos"], "not_allow_cancel_pos");
			}
		}
		
		// Live Server Need Check Finalise
		if(!$this->main_api->is_sync_server){
			//check pos_finalized 
			$q_f = $con->sql_query("select * from pos_finalized  where finalized=1 and branch_id=$branch_id and date=".ms($_REQUEST['date']));
			if($con->sql_numrows($q_f) > 0){
				$this->main_api->error_die(sprintf($this->err_list["pos_already_finalised"], $_REQUEST['date']), "pos_already_finalised");
			}
			$con->sql_freeresult($q_f);
		}
		
		$con_ss->sql_begin_transaction();
		
		$filter = array();
		$filter[] = "p.branch_id=".mi($branch_id);
		$filter[] = "p.counter_id=".mi($_REQUEST['counter_id']);
		$filter[] = "p.date =".ms($_REQUEST['date']);
		$filter[] = "p.receipt_no =".ms($_REQUEST['receipt_no']);
		
		$str_filter = "where ".join(' and ', $filter);
		
		$con_ss->sql_query("select p.*
		from pos p
		$str_filter limit 1");
		$r = $con_ss->sql_fetchassoc();
		$con_ss->sql_freeresult();
		
		if(!$r){
			$this->main_api->error_die($this->err_list["no_pos_data_found"], "no_pos_data_found");
		}
		
		if($r['cancel_status']){
			$this->main_api->error_die($this->err_list["pos_already_cancelled"], "pos_already_cancelled");
		}
		$id = $this->generateNewIDFromSyncServer("pos_receipt_cancel", "branch_id=$branch_id and counter_id=".mi($_REQUEST['counter_id'])." and date=".ms($_REQUEST['date'])." and receipt_no=".ms($_REQUEST['receipt_no']));
		
		$upd = array();
		$upd['branch_id'] = $branch_id;
		$upd['counter_id'] = $_REQUEST['counter_id'];
		$upd['date'] = $_REQUEST['date'];
		$upd['id'] = $id;
		$upd['receipt_no'] = $_REQUEST['receipt_no'];
		$upd['cancelled_by'] = $_REQUEST['cashier_id'];
		$upd['cancelled_time'] = $_REQUEST['cancelled_time'];
		$upd['verified_by'] = $_REQUEST['verified_by'];
		$con_ss->sql_query("insert into pos_receipt_cancel ".mysql_insert_by_field($upd));
		
		$upd = array();
		$upd['cancel_status'] = 1;
		if($this->main_api->is_sync_server){
			$upd['sync'] = 0;
		}
		$con_ss->sql_query("update pos set ".mysql_update_by_field($upd)." where id=".mi($r['id'])." and branch_id=".mi($branch_id)." and counter_id=".mi($_REQUEST['counter_id'])." and date=".ms($_REQUEST['date']));
		
		$con_ss->sql_commit();
		
		$ret = array();
		$ret['result'] = 1;

		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function upload_pos_pdf_receipt(){
		global $con, $appCore, $con_ss;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Check Branch
		$branch_id = mi($this->main_api->app_branch_id);
		if(!$branch_id)  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Branch Id"), "no_doc_found");
		if(!$_REQUEST['date'])  $this->main_api->error_die(sprintf($this->err_list["pos_date_not_found"]), "pos_date_not_found");
		if(!$_REQUEST['receipt_ref_no'])  $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Receipt Ref No"), "no_doc_found");
		
		// No File was Uploaded
		if(!isset($_FILES['pdf_receipt']))	$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'pdf_receipt'), "invalid_data");
		
		// Create Folder
		$folder = "attch/pos_pdf_invoice";
		if(!file_exists($folder)){
			$success = check_and_create_dir($folder);
			if(!$success){
				$this->main_api->error_die(sprintf($this->err_list["create_folder_failed"], 'pos_pdf_invoice'), "create_folder_failed");
			}
		}
		
		$folder = $folder."/".$branch_id;
		if(!file_exists($folder)){
			$success = check_and_create_dir($folder);
			if(!$success){
				$this->main_api->error_die(sprintf($this->err_list["create_folder_failed"], $branch_id), "create_folder_failed");
			}
		}
		
		$folder = $folder."/".$_REQUEST['date'];
		if(!file_exists($folder)){
			$success = check_and_create_dir($folder);
			if(!$success){
				$this->main_api->error_die(sprintf($this->err_list["create_folder_failed"], $_REQUEST['date']), "create_folder_failed");
			}
		}
		
		if($_FILES['pdf_receipt']['error'] == 0 && preg_match("/\.(pdf)$/i",$_FILES['pdf_receipt']['name'], $ext)){
			$filename = $_REQUEST['receipt_ref_no'].$ext[0];
			$final_path = $folder."/".$filename;
			
			// Move File to Actual Folder
			if(move_uploaded_file($_FILES['pdf_receipt']['tmp_name'], $final_path)){
				$file_uploaded = true;
			}else{
				$file_uploaded = false;
			}
		}else{
			$this->main_api->error_die(sprintf($this->err_list["invalid_format"], "PDF"), "invalid_format");
		}
		
		$ret = array();
		$ret['result'] = 1;
		if($file_uploaded){
			$ret['uploaded'] = 1;
		}else{
			$ret['uploaded'] = 0;
		}

		// Return Data
		$this->main_api->respond_data($ret);
	}
}
?>