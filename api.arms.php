<?php
/*
Standard ARMS API

10/20/2017 5:31 PM Andy
- Change get_product to sort by sku_items.id
- Enhanced get_product to able to pass in start_from and limit_count.

11/22/2017 11:31 AM Andy
- Enhanced to have "create_order" and "check_order".

3/7/2018 11:26 AM Justin
- Enhanced to have category_id, category_description and category_tree for get_product function.
- Enhanced to have SKU item ID filter for get_product function.

4/19/2018 10:39 AM Justin
- Enhanced to have uncheckout_do_qty.

10/5/2018 3:05 PM Justin
- Enhanced to get Shipment Method and Tracking Code from its own field, otherwise get it from remark.

10/8/2018 3:45 PM Andy
- Enhanced get_product to accept new parameter "barcode" and return new value "selling_price" and "link_code".

12/12/2019 3:14 PM Andy
- Changed initialize table to innodb.
*/
include("include/common.php");
include("include/price_checker.include.php");
ini_set('memory_limit', '1024M');
set_time_limit(0);

//print_r($_SERVER);
if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']){
	$is_http = 1;
	$_REQUEST['a'] = $_REQUEST['action'];
}else{
	define('TERMINAL', 1);
	$arg = $_SERVER['argv'];
	$_REQUEST['a'] = $arg[1];
}

class API_ARMS extends Module{
	var $is_debug = 0;
	var $log_folder = "attch/api.arms/";
	var $err_list = array(
		'login_failed' => "Error: Login Failed.",
		"invalid_branch" => "Error: Invalid branch/outlet %s.",
		"no_product_found" => "Error: No data found.",
		"no_order_id" => "Error: No Order Id.",
		"incorrect_branch", "Error: Incorrect branch/outlet.",
		"order_cannot_edit" => "Error: The Order cannot be edit right now. Status: %s",
		"invalid_qty_for_item" => "Error: Invalid qty for item '%s'.",
		"sku_item_not_found" => "Error: sku_item_code '%s' not found.",
		"no_sku_found" => "Error: No item found.",
		"order_id_need_array" => "Error: order_id need to be in array.",
		"order_not_found" => "Error: Order Not Found. %s",
		"arms_do_not_found" => "Error: ARMS could not find the DO, Please re-create the order.",
	);
	
	function __construct($title){
		if($_SERVER['SERVER_NAME'] == 'maximus')	$this->is_debug = 1;
		
		$this->prepareDB();
		parent::__construct($title);
	}
	
	function _default(){
		
	}
	
	// function to create all db
	private function prepareDB(){
		global $con;
		
		//setting value =store sku_items_last_update_date
		$con->sql_query("create table if not exists arms_api_settings(
			branch_id int,
			setting_name char(20),
			setting_value text, 
			last_update timestamp default 0,
			primary key(branch_id, setting_name)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
		$con->sql_query("create table if not exists api_do(
			order_id char(50) primary key,
			branch_id int,
			do_id int,
			added timestamp default 0,
			last_checking timestamp default 0,
			unique(branch_id, do_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
		check_and_create_dir($this->log_folder);
	}
	
	private function get_api_setting($bid, $setting_name){
		global $con;
		
		$con->sql_query("select * from arms_api_settings where branch_id=".mi($bid)." and setting_name=".ms($setting_name));
		$arms_api_settings = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $arms_api_settings;
	}
	
	private function set_api_setting($bid, $setting_name, $setting_value){
		global $con;
		
		$upd = array();
		$upd['branch_id'] = mi($bid);
		$upd['setting_name'] = $setting_name;
		$upd['setting_value'] = $setting_value;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("replace into arms_api_settings ".mysql_insert_by_field($upd));
	}
	
	private function is_valid_user($u, $p, $type = 'api'){
		global $config;
		
		$setting_info = $config['arms_api_setting'][$type];
		if(!$setting_info)	return false;
		
		foreach($setting_info as $bcode => $branch_setting){
			if ($u == $branch_setting["username"] && $p == $branch_setting["pass"])	return $bcode;
		}
	}
	
	private function get_branch_id($bcode){
		global $con;
		
		$q2 = $con->sql_query("select id from branch where code = ".ms($bcode));
		$row = $con->sql_fetchrow($q2);
		$con->sql_freeresult($q2);
		
		return mi($row["id"]);
	}	
	
	private function put_log($log){
		$filename = date("Y-m-d").".txt";
		$str = date("Y-m-d H:i:s")."; ".$log;
		file_put_contents($this->log_folder."/".$filename, $str."\r\n", FILE_APPEND);
	}
	
	private function error_die($err_msg){
		$this->put_log($err_msg);
		die($err_msg);
	}
	
	function get_product(){
		global $con, $config;
		
		$user = $_REQUEST['username'];
		$pass = $_REQUEST['pass'];
		$start_from = mi($_REQUEST['start_from']);
		$limit_count = mi($_REQUEST['limit']);
		$sid = mi($_REQUEST['sku_item_id']);
		$barcode = trim($_REQUEST['barcode']);
		
		$this->put_log("Request get_product, IP: ".$_SERVER['REMOTE_ADDR']);
		if ($bcode = $this->is_valid_user($user, $pass)){
			$bid = get_branch_id($bcode);
			if(!$bid)	$this->error_die(sprintf($this->err_list["invalid_branch"], $bcode));
		
			$this->put_log("Getting products.");
			//$where = "where psi.branch_id = $bid";
			$filter = $category_list = array();
			$filter[] = "si.active=1";
			
			// testing purpose
			if(!$sid && !$barcode){
				if($this->is_debug){
					$filter[] = "si.sku_id = 402423";	
					$limit = "limit 3";
				}
			}else{
				if($sid){
					$filter[] = "si.id = ".mi($sid);
				}elseif($barcode){
					$filter[] = "(si.sku_item_code=".ms($barcode)." or si.mcode=".ms($barcode)." or si.artno=".ms($barcode)." or si.link_code=".ms($barcode).")";
				}
			}
			
			if(!$sid && $start_from >= 0 && $limit_count > 0){
				$limit = "limit $start_from, $limit_count";
			}

			$filter = "where ".join(' and ', $filter);
			
			/*$sql = "select si.id,si.sku_id,si.sku_apply_items_id,si.sku_item_code,si.mcode,si.artno,si.link_code,
			si.description,si.receipt_description,si.active,si.added,psi.last_update,si.weight,si.size,si.color,si.flavor,
			si.selling_foc,if (sic.grn_cost is null,si.cost_price,sic.grn_cost) as cost,
			round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(p.price,si.selling_price)/(100+output_gst.rate)*100,ifnull(p.price,si.selling_price)),2)
			as gross_selling_price,
			round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(p.price,si.selling_price),ifnull(p.price,si.selling_price)*(100+output_gst.rate)/100),2)
			as selling_price_inclusive_gst,input_gst.code as input_gst_code,input_gst.rate as input_gst_rate,output_gst.code as output_gst_code,output_gst.rate as output_gst_rate,
			sic.qty as stock_qty
			from pmos_sku_items psi
			left join sku_items si on psi.sku_item_id = si.id
			left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = psi.branch_id
			left join sku_items_price p on p.sku_item_id = si.id and p.branch_id = psi.branch_id
			left join sku on sku.id=si.sku_id
			left join category c on c.id=sku.category_id
			left join category_cache cc on cc.category_id=sku.category_id
			left join gst input_gst on input_gst.id=if(if(si.input_tax<0,sku.mst_input_tax,si.input_tax)<0,cc.input_tax,if(si.input_tax<0,sku.mst_input_tax,si.input_tax))
			left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
			$where
			order by si.id
			limit 3";*/
			
			// select max level from category
			$con->sql_query("select max(level) as max_lvl from category");
			$cat_max_lvl = $con->sql_fetchfield("max_lvl");
			$con->sql_freeresult();
			
			// select extra column from category_cache
			$xtra_cols = "";
			for($i = 1; $i <= $cat_max_lvl; $i++){
				$xtra_cols .= ", p".$i;
			}
			
			$sql = "select si.id,si.sku_id,si.sku_apply_items_id,si.sku_item_code,si.mcode,si.artno,si.link_code,si.description as product_description,si.receipt_description, si.internal_description,si.weight,si.size,si.color,	round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(p.price,si.selling_price)/(100+output_gst.rate)*100,ifnull(p.price,si.selling_price)),2)
			as selling_price_before_gst,
			round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(p.price,si.selling_price),ifnull(p.price,si.selling_price)*(100+output_gst.rate)/100),2)
			as selling_price_inclusive_gst,
			round(ifnull(p.price, si.selling_price), 2) as selling_price,
		
			sic.qty as stock_qty, c.id as category_id, c.description as category_description $xtra_cols
			from sku_items si
			left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = $bid
			left join sku_items_price p on p.sku_item_id = si.id and p.branch_id = $bid
			left join sku on sku.id=si.sku_id
			left join category c on c.id=sku.category_id
			left join category_cache cc on cc.category_id=sku.category_id
			left join gst input_gst on input_gst.id=if(if(si.input_tax<0,sku.mst_input_tax,si.input_tax)<0,cc.input_tax,if(si.input_tax<0,sku.mst_input_tax,si.input_tax))
			left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
			$filter
			order by si.id
			$limit";
			
			$q1 = $con->sql_query($sql);
			
			while($si = $con->sql_fetchassoc($q1)){
				$r = $si;
				
				$prms = $do_info = array();
				$prms['sid'] = $r['id'];
				$prms['bid'] = $bid;
				$do_info = $this->load_uncheckout_do($prms);
				$r['uncheckout_do_qty'] = mf($do_info['uncheckout_do_qty']);
				unset($prms, $do_info);
				
				// color
				list($dummy, $r['color']) = explode(";", $r['color']);
				
				$apply_photo_list = get_sku_apply_item_photos($si['sku_apply_items_id']);
				$photo_list = get_sku_item_photos($si['id'],$si);
				
				if(!$apply_photo_list)	$apply_photo_list = array();
				if(!$photo_list)	$photo_list = array();
				$all_photo_list = array_merge($apply_photo_list, $photo_list);
				if($all_photo_list){
					foreach($all_photo_list as $photo_path){
						$photo_details = array();
						$photo_details['abs_path'] = $photo_path;
						if(file_exists($photo_path)){
							$photo_details['last_update'] = date("Y-m-d H:i:s", filemtime($photo_path));
							//$photo_details['name'] = basename($photo_path);			
							$r['photo_list'][] = $photo_details;
						}
					}
				}
				
				// loop and construct category tree list
				for($i = 1; $i <= $cat_max_lvl; $i++){
					if($r['p'.$i]){
						$cat_id = $r['p'.$i];
						if(!$category_list[$cat_id]){ // category not in the list, need to select
							$q2 = $con->sql_query("select id, description from category where id = ".mi($cat_id));
							$category_list[$cat_id] = $con->sql_fetchassoc($q2);
							$con->sql_freeresult($q2);
						}
						
						if(!$category_list[$cat_id]) continue; // if still couldn't get, something wrong on the category table
						
						$r['category_tree'][$i-1] = $category_list[$cat_id];
						
					}

					unset($r['p'.$i]); // always need to unset so that it won't become part of the serialisation
				}
				
				/*$r['size_chart_content'] = "";
				if($r['size_chart']){
					$size_chart_arr = explode("MIDDLE_SEPARTOR,", $r['size_chart']);
					$size_chart_str = "";
					foreach($size_chart_arr as $size_chart){
						$raw_sc = unserialize($size_chart);
						if($raw_sc){
							if($size_chart_str)	$size_chart_str .= "\n";
							$size_chart_str .= join("\n", $raw_sc);
						}
					}
					if($size_chart_str)	$r['size_chart_content'] = $size_chart_str;
				}
				unset($r['size_chart']);
				
				if(!$r['size'])	$this->put_log("This item will not be sync, $si[sku_item_code], reason: size is empty.");
				if(!$r['color'])	$this->put_log("This item will not be sync, $si[sku_item_code], reason: color is empty.");*/
				
				$si_list[$r['id']] = $r;
			}
			$con->sql_freeresult($q1);
		
			/*foreach ($si_list as $key => $item){
				$param = array();
				$param["code"] =  $si_list[$key]["sku_item_code"];
				$param["branch_id"] = $bid;
				
				$sku = check_price($param);
				//$si_list[$key]["gross_special_price"] = $sku["non_member_price_before_gst"];
				//$si_list[$key]["special_price_inclusive_gst"] = $sku["non_member_price_include_gst"];
				$si_list[$key]["gross_special_price"] = $sku["non_member_price_include_gst"];
				$si_list[$key]["special_price_before_gst"] = $sku["non_member_price_before_gst"];
				
				$si_list[$key]["special_price_start_date"] = $sku["non_member_date_from"];
				$si_list[$key]["special_price_end_date"] = $sku["non_member_date_to"];
			}*/
			
			if ($si_list){
				$this->put_log("Sending products.");
				print json_encode($si_list);
				$this->put_log("Products sent.");
			}	
			else{
				$this->error_die($this->err_list["no_product_found"]);
			}
		}else{
			$this->error_die($this->err_list["login_failed"]);
		}
	}
	
	function test_submit(){
		global $con, $smarty;
		
		$this->display("api.arms.test_submit.tpl");
	}
		
	function view_log(){
		//print_r($_SERVER);
		$this->put_log("Request view_log. IP: ".$_SERVER['REMOTE_ADDR']);
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			$this->put_log("Verification Required.");
			header('WWW-Authenticate: Basic realm="Verification Required"');
			header('HTTP/1.0 401 Unauthorized');
			$this->error_die($this->err_list["login_failed"]);
		}
		
		$user = $_SERVER['PHP_AUTH_USER'];
		$pass = $_SERVER['PHP_AUTH_PW'];
		
		//print_r($_SERVER);
			
		$bcode = $this->is_valid_user($user, $pass, 'admin');
		if (!$bcode){
			$this->error_die($this->err_list["login_failed"]);
		}
		
		$bid = $this->get_branch_id($bcode);
		if(!$bid)	$this->error_die(sprintf($this->err_list["invalid_branch"], $bcode));
		
		$this->put_log("Login Successful");
		
		if($_REQUEST['f']){
			$f = $_REQUEST['f'];
			$this->put_log("View File $f");
			header("Content-type: text/plain");
			readfile($this->log_folder."".$f);
		}else{
			// load file list
			$this->put_log("Display File List.");
			$filelist = glob($this->log_folder."*.txt");
		
			//print_r($filelist);
			print "<h1>Log Files</h1>";
			print "<ul>";
			foreach($filelist as $f){
				print "<li><a href=\"?action=view_log&f=".basename($f)."\" target='_blank'>".basename($f)."</li>";
			}
			print "</ul>";
		}
	}
	
	private function get_api_do($order_id){
		global $con;
		
		$q1 = $con->sql_query("select * from api_do where order_id=".ms($order_id));
		$api_do = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		return $api_do;
	}
	
	private function return_shipment_status_string($do){
		$shipment_status = 'Waiting for process';
		
		if(!$do['active']){
			$shipment_status = "Cancelled";
		}else{
			if($do['status'] == 1 && $do['checkout'] == 0){
				$shipment_status = "In Process";
			}elseif($do['status'] == 1 && $do['checkout'] == 1){
				$shipment_status = "Completed";
			}else{
				if($do['status'] != 0 && $do['status'] != 2){
					$shipment_status = "Cancelled";
				}
			}
		}
		return $shipment_status;
	}
	
	private function get_sku_items_from_sku_item_code($sku_item_code){
		global $con;
		
		$con->sql_query("select * from sku_items where sku_item_code=".ms($sku_item_code));
		$si = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $si;
	}
	
	// receive order
	function create_order(){
		global $con, $config;
		
		$this->put_log("Request create_order. IP: ".$_SERVER['REMOTE_ADDR']);
		
		$user = $_REQUEST['username'];
		$pass = $_REQUEST['pass'];
		$bcode = $this->is_valid_user($user, $pass);
		if (!$bcode){
			$this->error_die($this->err_list["login_failed"]);
		}
		
		$bid = $this->get_branch_id($bcode);
		if(!$bid)	$this->error_die(sprintf($this->err_list["invalid_branch"], $bcode));
		
		$form = $_REQUEST;
		
		$order_id = trim($form['order_id']);
		if(!$order_id)	$this->error_die($this->err_list["no_order_id"]);
		
		$this->put_log("Order ID: $order_id");
		
		// get approval flow
		$con->sql_query("select * from approval_flow where branch_id=$bid and type='do' order by id limit 1");
		$approval_flow = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($approval_flow){
			//print_r($approval_flow);exit;
			// construct notification list
			$approval_flow['approval_settings'] = unserialize($approval_flow['approval_settings']);
			if($approval_flow['approval_settings']['notify']){
				$to_list = array();
				foreach($approval_flow['approval_settings']['notify'] as $user_id => $notify_info){
					$user_settings = array();
					$user_settings['user_id'] = $user_id;
					if($notify_info['pm'])	$user_settings['approval_settings']['pm'] = 1;
					if($notify_info['email'])	$user_settings['approval_settings']['email'] = 1;
					
					if($user_settings['approval_settings'])	$to_list[] = $user_settings;
				}
			}
			//print_r($to_list);exit;
		}
		
		// try to get whether already got data
		$api_do = $this->get_api_do($order_id);
		
		$upd = array();
		
		if($api_do['branch_id'] > 0 && $api_do['do_id'] > 0){	// have
			if($api_do['branch_id'] != $bid)	$this->error_die($this->err_list["incorrect_branch"]);
			
			$q1 = $con->sql_query("select * from do where branch_id=$bid and id=".mi($api_do['do_id']));
			$do = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			$this->put_log("Already have order in ARMS, try to re-use it. ARMS Order ID: ".$do['id']);
			
			$shipment_status = $this->return_shipment_status_string($do);
			//die($shipment_status);
			if($shipment_status == 'Waiting for process'){	// still can edit
				$do_id = $do['id'];
			}else{
				if($shipment_status == 'In Process' || $shipment_status == 'Completed'){
					$this->error_die(sprintf($this->err_list["order_cannot_edit"], $shipment_status));
				}
				$this->put_log("Re-use is not possible, creating a new one.");
			}
		}
		
		// need to create new
		if(!$do_id){
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$upd['branch_id'] = $bid;
		}
		
		// info for DO
		$upd['user_id'] = 1;
		$upd['do_date'] = date("Y-m-d", strtotime($form['order_date']));
		$upd['price_indicate'] = 2;
		$upd['amt_need_update'] = 1;
		$upd['do_type'] = 'open';
		$upd['active'] = 1;
		$upd['status'] = 0;
		$upd['approved'] = 0;
		$upd['open_info']['name'] = $form['purchaser_name'];
		$upd['open_info']['address'] = $form['shipping_address'];
		$upd['remark'] = "Billing Address\n".$form['billing_address']."\nOrder ID: ".$order_id;
		if($form['purchaser_email'])	$upd['remark'] .= ", Email: ".$form['purchaser_email'];
		if($form['purchaser_phone'])	$upd['remark'] .= ", Phone: ".$form['purchaser_phone'];
		if($form['delivery_method'])	$upd['remark'] .= "\nDelivery Method: ".$form['delivery_method'];
		
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		// gst status
		$gst_params = array();
		$gst_params['branch_id'] = $bid;
		$gst_params['date'] = $upd['do_date'];
		$upd['is_under_gst'] = $is_under_gst = check_gst_status($gst_params);
		
		// info for DO_ITEMS
		$items_list = array();
		if($form['sku_item_code']){
			foreach($form['sku_item_code'] as $idx => $sku_item_code){
				if(!$sku_item_code)	continue;
				
				$qty = mf($form['sku_quantity'][$idx]);
				if($qty <= 0)	$this->error_die(sprintf($this->err_list["invalid_qty_for_item"], $sku_item_code));
				$item = array();
				
				$si = $this->get_sku_items_from_sku_item_code($sku_item_code);
				if(!$si)	$this->error_die(sprintf($this->err_list["sku_item_not_found"], $sku_item_code));
				
				$item['sku_item_id'] = $sid = mi($si['id']);
				$item['mcode'] = $si['mcode'];
				$item['artno'] = $si['artno'];
				$item['pcs'] = $qty;
				
				// get cost price
				$q_p = $con->sql_query("select price as cost_price from sku_items_price where sku_item_id=$sid and branch_id=$bid");
				$sip = $con->sql_fetchassoc($q_p);
				$con->sql_freeresult($q_p);
				
				$item['cost_price'] = $sip ? $sip['price'] : $si['selling_price'];
				
				// get selling price
				$item['selling_price'] = $item['cost_price'];
				
				// find price before gst
				if($config['enable_gst']){
					// get sku is inclusive
					$is_sku_inclusive = get_sku_gst("inclusive_tax", $sid);
					// get sku original output gst
					$sku_original_output_gst = get_sku_gst("output_tax", $sid);
					
					if($is_sku_inclusive == 'yes'){
						// is inclusive tax
						$price_included_gst = $item['cost_price'];
						if($is_under_gst){
							$item['display_cost_price_is_inclusive'] = 1;
							$item['display_cost_price'] = $price_included_gst;
						}
						
						// find the price before tax
						$gst_tax_price = $item['cost_price'] / ($sku_original_output_gst['rate']+100) * $sku_original_output_gst['rate'];
						$item['cost_price'] = $price_included_gst - $gst_tax_price;
					}
				}
				
				// get gst code
				if($config['enable_gst'] && $is_under_gst){
					$output_gst = get_sku_gst("output_tax", $sid);
					
					if($output_gst){
						$item['gst_id'] = $output_gst['id'];
						$item['gst_code'] = $output_gst['code'];
						$item['gst_rate'] = $output_gst['rate'];
					}
				}
	
				// get stock balance
				$con->sql_query("select qty from sku_items_cost where sku_item_id=$sid and branch_id=$bid");
				$sb = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				$item['stock_balance1'] = $sb['qty'];
				
				$items_list[$sid] = $item;
			}
		}
		if(!$items_list)	$this->error_die($this->err_list["no_sku_found"]);
		
		//print_r($upd);
		//print_r($items_list);
		
		// create header
		$upd['open_info'] = serialize($upd['open_info']);
		if($do_id){	// update
			$con->sql_query("update do set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$do_id");
			
			$con->sql_query("delete from do_items where branch_id=$bid and do_id=$do_id");
			$con->sql_query("delete from do_open_items where branch_id=$bid and do_id=$do_id");
			
			$upd_api_do = array();
			$upd_api_do['last_checking'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("update api_do set ".mysql_update_by_field($upd_api_do)." where order_id=".ms($order_id));
		}else{	// insert
			$upd['checkout_remark'] = 'ShippingMethod ; TrackingCode ;';
			$con->sql_query("insert into do ".mysql_insert_by_field($upd));
			$do_id = $con->sql_nextid();
			
			// create relationship
			$upd_api_do = array();
			$upd_api_do['order_id'] = $order_id;
			$upd_api_do['branch_id'] = $bid;
			$upd_api_do['do_id'] = $do_id;
			$upd_api_do['added'] = 'CURRENT_TIMESTAMP';
			$upd_api_do['last_checking'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("replace into api_do ".mysql_insert_by_field($upd_api_do));
		}
		
		// create item
		foreach($items_list as $item){
			$upd_item = array();
			$upd_item['branch_id'] = $bid;
			$upd_item['do_id'] = $do_id;
			$upd_item['sku_item_id'] = $item['sku_item_id'];
			$upd_item['artno_mcode'] = $item['artno']?$item['artno']:$item['mcode'];
			$upd_item['cost_price'] = $item['cost_price'];
			$upd_item['selling_price'] = $item['selling_price'];
			$upd_item['pcs'] = $item['pcs'];
			$upd_item['stock_balance1'] = $item['stock_balance1'];
			
			if($config['enable_gst'] && $is_under_gst){
				$upd_item['gst_id'] = $item['gst_id'];
				$upd_item['gst_code'] = $item['gst_code'];
				$upd_item['gst_rate'] = $item['gst_rate'];
				$upd_item['display_cost_price_is_inclusive'] = $item['display_cost_price_is_inclusive'];
				$upd_item['display_cost_price'] = $item['display_cost_price'];
			}
			
			unset($upd_item['artno']);
			unset($upd_item['mcode']);
			$con->sql_query("insert into do_items ".mysql_insert_by_field($upd_item));
		}
		
		$this->put_log("Order saved, ARMS Order ID: $do_id");
		
		// send pm
		if($to_list){
			$others = array();
			$others['custom_from'] = 1;
			$others['branch_id'] = $bid;
			$others['module_name'] = 'do';
			send_pm2($to_list, "Delivery Order POSTED from E-Com (ID#$do_id)", "do.php?&a=view&id=$do_id&branch_id=$bid", $others);
		}
			
		$ret = array();
		$ret['result'] = 'OK';
		$ret['arms_order_id'] = $do_id;
		print json_encode($ret);
		exit;
	}
	
	function check_order(){
		global $con, $config;
		
		$this->put_log("Request check_order. IP: ".$_SERVER['REMOTE_ADDR']);
		
		$user = $_REQUEST['username'];
		$pass = $_REQUEST['pass'];
		$bcode = $this->is_valid_user($user, $pass);
		if (!$bcode){
			$this->error_die($this->err_list["login_failed"]);
		}
		
		$bid = $this->get_branch_id($bcode);
		if(!$bid)	$this->error_die(sprintf($this->err_list["invalid_branch"], $bcode));
		
		$form = $_REQUEST;
		
		if(!$form['order_id'])	$this->error_die($this->err_list["no_order_id"]);
		
		if(!is_array($form['order_id'])){
			$this->error_die($this->err_list["order_id_need_array"]);
		}
		
		$ret = array();
		// loop each order id
		foreach($form['order_id'] as $order_id){
			$upd = array();
			
			$order_id = trim($order_id);
			
			if(!$order_id)	continue;
			
			$this->put_log("Check Order ID: $order_id");
			
			// try to get whether already got data
			$api_do = $this->get_api_do($order_id);
			if(!$api_do){
				$upd['error'] = sprintf($this->err_list["order_not_found"], $order_id);
				$this->put_log(sprintf($this->err_list["order_not_found"], $order_id));
			}else{
				if($api_do['branch_id'] != $bid)	$this->error_die($this->err_list["incorrect_branch"]);
				
				$q1 = $con->sql_query("select * from do where branch_id=$bid and id=".mi($api_do['do_id']));
				$do = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				if(!$do)	$this->error_die($this->err_list["arms_do_not_found"]);
				
				$upd['arms_order_id'] = $do['id'];
				$upd['shipment_status'] = $this->return_shipment_status_string($do);
				
				if($upd['shipment_status'] == 'Completed'){
					if(trim($do['shipment_method']) || trim($do['tracking_code'])){
						$upd['shipment_method'] = trim($do['shipment_method']);
						$upd['tracking_code'] = trim($do['tracking_code']);
					}else{
						list($shipment_method, $tracking_code) = explode(";", $do['checkout_remark']);
						$upd['shipment_method'] = trim($shipment_method);
						$upd['tracking_code'] = trim($tracking_code);
					}
				}
				$this->put_log("ARMS Order ID: ".$do['id'].", Status: ".$upd['shipment_status']);
			}
			
			$ret[$order_id] = $upd;
			
			
		}
		
		if(!$ret){
			$this->error_die($this->err_list["no_order_id"]);
		}
		
		$this->put_log("Sending result back.");
		print json_encode($ret);
		$this->put_log("Result sent.");
		exit;
	}
	
	private function load_uncheckout_do($prms=array()){
		global $con;
		
		if(!$prms['bid'] || !$prms['sid']) return;
		
		$q2 = $con->sql_query($sql="select di.*, do.deliver_branch, u.fraction as uom_fraction
							   from do 
							   left join do_items di on di.do_id = do.id and di.branch_id = do.branch_id
							   left join uom u on u.id = di.uom_id
							   where do.active=1 and do.status in (0,1) and do.checkout=0
							   and di.branch_id = ".mi($prms['bid'])." and di.sku_item_id = ".mi($prms['sid']));
		
		$uncheckout_do_qty = 0;
		while($r = $con->sql_fetchassoc($q2)){
			$ctn = $pcs = 0;
			$r['deliver_branch'] = unserialize($r['deliver_branch']);
			// is multi branch
			if(is_array($r['deliver_branch']) && $r['deliver_branch']){
				$ctn_allocation = unserialize($r['ctn_allocation']);
				$pcs_allocation = unserialize($r['pcs_allocation']);
				foreach($r['deliver_branch'] as $tmp_bid){
					$ctn += $ctn_allocation[$tmp_bid];
					$pcs += $pcs_allocation[$tmp_bid];
				}
			}else{
				$ctn = $r['ctn'];
				$pcs = $r['pcs'];
			}
			
			$curr_qty = ($ctn * $r['uom_fraction']) + $pcs;
			$uncheckout_do_qty += $curr_qty;
		}
		$con->sql_freeresult($q2);
		
		$ret = array();
		$ret['uncheckout_do_qty'] = $uncheckout_do_qty;
		
		return $ret;
	}
}

$API_ARMS = new API_ARMS('ARMS API');
?>