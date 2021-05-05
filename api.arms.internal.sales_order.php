<?php
/**/
class API_ARMS_INTERNAL_SALES_ORDER{
	var $main_api = false;
	
	// Error List
	var $err_list = array(
		"no_doc_found" => 'No %s Found.',
		"sales_order_create_failed" => "Failed to Create Sales Order.",
		"sales_order_item_create_failed" => "Failed to Create Sales Order Item.",
		"sales_order_item_qty_update_failed" => "Failed to Update Sales Order Items Qty.",
		"not_allow_to_modify_sales_order" => "Not allow to modify this sales order.",
		"sales_order_item_update_failed"=> "Failed to update sales order, some sales order item has been delete by another user on backend.",
	);
	
	function __construct($main_api){
		$this->main_api = $main_api;
	}
	
	/* get sales order data */
	function get_sales_order_data(){
		global $con;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$form = $_REQUEST;
		if(!$form['id']) $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Sales Order"), "no_doc_found");
		$id = ms($form['id']);
		$branch_id = $this->main_api->app_branch_id;
		
		// search sales order list
		$q1 = $con->sql_query("select so.id, so.order_no, so.order_date, so.batch_code, so.cust_po, so.debtor_id, debtor.description
		from sales_order so 
		left join debtor on debtor.id=so.debtor_id
		where so.branch_id=$branch_id and (so.id=$id or so.order_no=$id) and so.active=1 and so.approved=0 and so.status=0");
		$sales_order_list = $con->sql_fetchrow($q1);
		$con->sql_freeresult($q1);
		
		$ret = array();
		if($sales_order_list){
			$ret['result'] = 1;
			$ret['sales_order_list'] = $sales_order_list;
		}else{
			$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Sales Order"), "no_doc_found");
		}
		unset($sales_order_list);
		
		// return data
		$this->main_api->respond_data($ret);
	}
	
	/* get sales order items */
	function get_sales_order_item_list(){
		global $con;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$form = $_REQUEST;
		if(!$form['sales_order_id']) $this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Sales Order Items"), "no_doc_found");
		$branch_id = $this->main_api->app_branch_id;
		$sales_order_id = mi($form['sales_order_id']);
		
		// search sales order list
		$sales_order_item_list = $ret = array();
		$q1 = $con->sql_query("select soi.id, soi.pcs, soi.ctn, si.sku_item_code, si.description as description
		from sales_order_items soi
		left join sku_items si on si.id=soi.sku_item_id
		where soi.sales_order_id=$sales_order_id and soi.branch_id=$branch_id order by soi.id");
		while($r = $con->sql_fetchassoc($q1)){
			$sales_order_item_list[] =$r;
		}
		$con->sql_freeresult($q1);
		
		if($sales_order_item_list){
			$ret['result'] = 1;
			$ret['sales_order_item_list'] = $sales_order_item_list;
		}else{
			$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Sales Order Items"), "no_doc_found");
		}
		
		// return data
		$this->main_api->respond_data($ret);
	}
	
	/* save sales order */
	function save_sales_order(){
		global $con, $config;
	
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$form = $_REQUEST;
		$id = mi($form['id']);
		$branch_id = $this->main_api->app_branch_id;
		$debtor_id = mi($form['debtor_id']);
		$order_date = $form['order_date'];
		
		$upd = array();
		$upd['debtor_id'] = $debtor_id;
		$upd['order_date'] = $order_date;
		$upd['batch_code'] = trim($form['batch_code']);
		$upd['cust_po'] = trim($form['cust_po']);
		$upd['user_id'] = $this->main_api->user['id'];
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		// get debtor info
		$con->sql_query("select * from debtor where id=$debtor_id");
		$debtor_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($config['enable_gst']){
			$prm['branch_id'] = $branch_id;
			$prm['date'] = $order_date;
			$upd['is_under_gst'] = check_gst_status($prm);
			
			if($upd['is_under_gst']){
				// check special exemption
				$upd['is_special_exemption'] = $debtor_info['special_exemption'];
			}
		}
		
		//select report prefix from branch
		$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
		$b = $con->sql_fetchrow();
		$con->sql_freeresult();
		
		if($id > 0){  // old sales order
			$con->sql_query("select id from sales_order where id=$id and branch_id=$branch_id and active=1 and approved=0 and status=0");
			$so = $con->sql_fetchrow();
			$con->sql_freeresult();
			if($so['id']){
				$formatted = sprintf("%05d", $id);
				$order_no = $b['report_prefix'].$formatted;
				$con->sql_query("update sales_order set ".mysql_update_by_field($upd)." where id=$id and branch_id=$branch_id");
			}else{
				$this->main_api->error_die($this->err_list["not_allow_to_modify_sales_order"], "not_allow_to_modify_sales_order");
			}
		}else{	// new sales order
			$upd['branch_id'] = $branch_id;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into sales_order ".mysql_insert_by_field($upd));
			$id = $con->sql_nextid();
			
			$formatted = sprintf("%05d", $id);
			$order_no = $b['report_prefix'].$formatted;
			$con->sql_query("update sales_order set order_no=".ms($order_no)." where branch_id=$branch_id and id=".mi($id));
		}
		
		$ret = array();
		if($id){
			$ret['result'] = 1;
			$ret['id'] = $id;
			$ret['order_no'] = $order_no;
		}else{
			$this->main_api->error_die($this->err_list["sales_order_create_failed"], "sales_order_create_failed");
		}
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function save_sales_order_items(){
		global $con, $config;
	
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$form = $_REQUEST;
		$sales_order_id = mi($form['sales_order_id']);
		$branch_id = $this->main_api->app_branch_id;
		
        if(!$sales_order_id || !$branch_id){
			$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Sales Order"), "no_doc_found");
		}
		
		$q1=$con->sql_query("select * from sales_order where branch_id=$branch_id and id=$sales_order_id");
		$so = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$ret = array();
		if($form['items']){
			$form['items'] = json_decode($form['items'], true);
			foreach($form['items'] as $row_id=>$item){
				if($item['sku_item_id'] && $item['pcs']){
					$sku_item_id = mi($item['sku_item_id']);
					$upd = array();
					$upd['branch_id'] = mi($branch_id);
					$upd['sales_order_id'] = mi($sales_order_id);
					$upd['user_id'] = $this->main_api->user['id'];
					$upd['sku_item_id'] = $item['sku_item_id'];
					$upd['pcs'] = $item['pcs'];
					
					$q2=$con->sql_query("select si.id as sku_item_id, 1 as uom_id,sic.qty as stock_balance, ifnull(sic.grn_cost,si.cost_price) as cost_price, ifnull(sip.price,si.selling_price) as selling_price 
					from sku_items si
					left join sku on sku_id = sku.id
					left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
					left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
					left join uom on uom.id=si.packing_uom_id
					where si.id=$sku_item_id");
					$r2 = $con->sql_fetchassoc($q2);
					$con->sql_freeresult($q2);
					
					$upd['cost_price'] = $r2['cost_price'];
					$upd['selling_price']= $r2['selling_price'];
					$upd['uom_id'] = $r2['uom_id'];
					$upd['stock_balance'] = $r2['stock_balance'];
			
					// find price before gst
					if($config['enable_gst']){
						// get sku is inclusive
						$is_sku_inclusive = get_sku_gst("inclusive_tax", $sku_item_id);
						// get sku original output gst
						$sku_original_output_gst = get_sku_gst("output_tax", $sku_item_id);
						
						if($is_sku_inclusive == 'yes'){
							// is inclusive tax
							// find the price before tax
							$gst_tax_price = round($upd['selling_price'] / ($sku_original_output_gst['rate']+100) * $sku_original_output_gst['rate'], 2);
							$price_included_gst = $upd['selling_price'];
							$upd['selling_price'] = $price_included_gst - $gst_tax_price;
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
								$upd['gst_id'] = $use_gst['id'];
								$upd['gst_code'] = $use_gst['code'];
								$upd['gst_rate'] = $use_gst['rate'];
							}else{
								$upd['gst_id'] = $gst_list[0]['id'];
								$upd['gst_code'] = $gst_list[0]['code'];
								$upd['gst_rate'] = $gst_list[0]['rate'];
							}
						}
					}
					
					$q3 = $con->sql_query("select id from sales_order_items where branch_id=$branch_id and sales_order_id=$sales_order_id and sku_item_id=$sku_item_id");
					$r3 = $con->sql_fetchassoc($q3);
					$con->sql_freeresult($q3);
					
					if($r3['id']){
						$con->sql_query("update sales_order_items set pcs= pcs+".mf($item['pcs'])." where branch_id=$branch_id and sales_order_id=$sales_order_id and sku_item_id=$sku_item_id");
						$ret['result'] = 1;
					}else{
						$con->sql_query("insert into sales_order_items ".mysql_insert_by_field($upd));
						$ret['result'] = 1;
					}
					$this->mark_so_amt_need_update($branch_id, $sales_order_id);
				}else{
					$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "SKU Items"), "no_doc_found");
				}
			}
		}else{
			$this->main_api->error_die($this->err_list["sales_order_item_create_failed"], "sales_order_item_create_failed");
		}
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	//update sales order item qty/ delete item
	function update_sales_order_items(){
        global $con;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		// Must already login by user
		if(!$this->main_api->user){
			$this->main_api->error_die($this->main_api->err_list["need_user_session"], "need_user_session");
		}
		
		$form = $_REQUEST;
		$sales_order_id = mi($form['sales_order_id']);
		$branch_id = $this->main_api->app_branch_id;
        if(!$sales_order_id || !$branch_id){
			$this->main_api->error_die(sprintf($this->err_list["no_doc_found"], "Sales Order Item"), "no_doc_found");
		}
		
		$ret = array();
		if($form['items']){
			$form['items'] = json_decode($form['items'], true);
			foreach($form['items'] as $action=>$item_list){
				foreach($item_list as $key=>$val){
					if($action == 'update' && $val){
						$q1 = $con->sql_query("select id from sales_order_items where branch_id=$branch_id and sales_order_id=$sales_order_id and id=".mi($val['id']));
						if($con->sql_numrows($q1)<=0){
							$this->main_api->error_die($this->err_list["sales_order_item_update_failed"], "sales_order_item_update_failed");
						}
						$con->sql_freeresult($q1);
						$con->sql_query("update sales_order_items set pcs=".mf($val['pcs'])." where branch_id=$branch_id and sales_order_id=$sales_order_id and id=".mi($val['id']));
					}else if($action == 'delete' && $val){
						$con->sql_query("delete from sales_order_items where branch_id=$branch_id and sales_order_id=$sales_order_id and id=".mi($val['id']));
					}
				}
			}
			$ret['result'] = 1;
		}else{
			$this->main_api->error_die($this->err_list["sales_order_item_qty_update_failed"], "sales_order_item_qty_update_failed");
		}
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	// mark sales order amount need to update
	function mark_so_amt_need_update($branch_id, $id){
		global $con;
		
		$upd = array();
		$upd['amt_need_update'] = 1;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update sales_order set ".mysql_update_by_field($upd)." where id=$id and branch_id=$branch_id");
	}
}
?>