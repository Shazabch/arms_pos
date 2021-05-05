<?php
/*
API for subalipack

12/03/2015 5:30 PM Qiu Ying
- update product and stock qty
	- generate pmos_sku_items
	- get_products and print json obejct, need validation user/pass/branch
	- product_updated, delete from pmos_sku_items. (user/pass/branch/sku_item_code), return 'OK'
	
12/23/2015 5:15 PM Andy
- Fix sku apply item photo not found.
- Enhanced color split by ";" to get color code.
- Enhanced create_order to take delivery_method.
- Enhanced to send notification once order updated.
- Enhanced check_order to return order_not_found by order.

12/24/2015 4:38 PM Andy
- Enhanced view log to hide the actual file path.
- Enhanced to auto turn on is_debug if server name is maximus.
- Enhanced to record IP Address for view log.

3/10/2016 1:57 PM Andy
- Change gross_selling_price and gross_special_price to become selling price inclusive gst.
- Add column selling_price_before_gst and special_price_before_gst, it is the selling price before gst.

12/12/2019 5:19 PM Andy
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

class API_PMOS extends Module{
	var $is_debug = 0;
	var $log_folder = "attch/api.pmos/";
	var $err_list = array(
		'login_failed' => "Error: Login Failed.",
		"invalid_branch" => "Error: Invalid branch/outlet %s.",
		"incorrect_branch", "Error: Incorrect branch/outlet.",
		"no_product_found" => "Error: No data found.",
		"no_order_id" => "Error: No Order Id.",
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
		$con->sql_query("create table if not exists pmos_settings(
			branch_id int,
			setting_name char(20),
			setting_value text, 
			last_update timestamp default 0,
			primary key(branch_id, setting_name)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
		$con->sql_query("create table if not exists pmos_sku_items(
			branch_id int,
			sku_item_id int,
			sku_item_code char(12),
			last_update text,
			primary key(branch_id, sku_item_id),
			index sku_item_code (sku_item_code)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
		$con->sql_query("create table if not exists pmos_do(
			pmos_order_id char(50) primary key,
			branch_id int,
			do_id int,
			added timestamp default 0,
			last_checking timestamp default 0,
			unique(branch_id, do_id)
			) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
		check_and_create_dir($this->log_folder);
	}
	
	private function get_pmos_setting($bid, $setting_name){
		global $con;
		
		$con->sql_query("select * from pmos_settings where branch_id=".mi($bid)." and setting_name=".ms($setting_name));
		$pmos_settings = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $pmos_settings;
	}
	
	private function set_pmos_setting($bid, $setting_name, $setting_value){
		global $con;
		
		$upd = array();
		$upd['branch_id'] = mi($bid);
		$upd['setting_name'] = $setting_name;
		$upd['setting_value'] = $setting_value;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("replace into pmos_settings ".mysql_insert_by_field($upd));
	}
	
	private function is_valid_user($u, $p, $type = 'api'){
		global $config;
		
		$setting_info = $config['pmos_api_setting'][$type];
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
	
	private function get_pmos_do($pmos_order_id){
		global $con;
		
		$q1 = $con->sql_query("select * from pmos_do where pmos_order_id=".ms($pmos_order_id));
		$pmos_do = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		return $pmos_do;
	}
	
	private function get_sku_items_from_sku_item_code($sku_item_code){
		global $con;
		
		$con->sql_query("select * from sku_items where sku_item_code=".ms($sku_item_code));
		$si = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $si;
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
		
		$this->put_log("Request get_product, IP: ".$_SERVER['REMOTE_ADDR']);
		if ($bcode = $this->is_valid_user($user, $pass)){
			$bid = get_branch_id($bcode);
			if(!$bid)	$this->error_die(sprintf($this->err_list["invalid_branch"], $bcode));
		
			$this->put_log("Getting products.");
			//$where = "where psi.branch_id = $bid";
			
			// testing purpose
			if($this->is_debug){
				$where = "where si.sku_id = 402423";	
				$limit = "limit 10";
			}
			
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
			
			$sql = "select si.id,si.sku_id,si.sku_apply_items_id,si.sku_item_code,si.description as name,si.internal_description,si.weight,si.size,si.color,	round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(p.price,si.selling_price)/(100+output_gst.rate)*100,ifnull(p.price,si.selling_price)),4)
			as selling_price_before_gst,
			round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(p.price,si.selling_price),ifnull(p.price,si.selling_price)*(100+output_gst.rate)/100),2)
			as gross_selling_price,
		
			sic.qty as stock_qty,
			(select group_concat(si2.additional_description, \"MIDDLE_SEPARTOR\") from sku_items si2 where si2.sku_id=si.sku_id) as size_chart
			from sku_items si
			left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = $bid
			left join sku_items_price p on p.sku_item_id = si.id and p.branch_id = $bid
			left join sku on sku.id=si.sku_id
			left join category c on c.id=sku.category_id
			left join category_cache cc on cc.category_id=sku.category_id
			left join gst input_gst on input_gst.id=if(if(si.input_tax<0,sku.mst_input_tax,si.input_tax)<0,cc.input_tax,if(si.input_tax<0,sku.mst_input_tax,si.input_tax))
			left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
			$where
			order by si.sku_id,si.sku_item_code
			$limit";
			
			$q1 = $con->sql_query($sql);
			
			while($si = $con->sql_fetchassoc($q1)){
				$r = $si;
				
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
				
				$r['size_chart_content'] = "";
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
				if(!$r['color'])	$this->put_log("This item will not be sync, $si[sku_item_code], reason: color is empty.");
				
				$si_list[$r['id']] = $r;
			}
			$con->sql_freeresult($q1);
		
			foreach ($si_list as $key => $item){
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
			}
			
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
	
	/*function product_updated(){
		global $con,$config;
		
		$setting_info = $config['pmos_api_setting'];
		
		$user = $_REQUEST['username'];
		$pass = $_REQUEST['pass'];
		
		if ($user == $setting_info["username"] && $pass == $setting_info["pass"]){
			$bcode = $_REQUEST['bcode'];
			$si_code = $_REQUEST['sku_item_code'];
			
			$q2 = $con->sql_query("select id from branch where code = ".ms($bcode));
			$row = $con->sql_fetchrow($q2);
			$con->sql_freeresult($q2);
			
			if(!$row)	die("Invalid $bcode\n");
			$bid = mi($row["id"]);
			
			$q3 = $con->sql_query("select * from sku_items where sku_item_code = ".ms($si_code));
			$row = $con->sql_fetchrow($q3);
			$con->sql_freeresult($q3);
			
			if(!$row)	die("Invalid $si_code\n");
			
			$q1 = $con->sql_query("delete from pmos_sku_items where sku_item_code = $si_code and branch_id = $bid");
			if ($con->sql_affectedrows() > 0)	print "OK";
			else	print "Fail";
			
		}else	print "Login Failed";
	}
	
	function product_description(){
		global $con;
		
		if ($user == $setting_info["username"] && $pass == $setting_info["pass"]){
			$bcode = $_REQUEST['bcode'];
			$q2 = $con->sql_query("select id from branch where code = ".ms($bcode));
			$row = $con->sql_fetchrow($q2);
			$con->sql_freeresult($q2);
			
			if(!$row)	die("Invalid $bcode\n");
			$bid = mi($row["id"]);
			
			$q1 = $con->sql_query("select distinct sku_id from sku_items where active = 1");
			
			while($r = $con->sql_fetchrow($q1)){
				$q3 = $con->sql_query("select * from pmos_sku_items psi
				left join sku_items si on psi.sku_item_id = si.id
				where branch_id = $bid ");
			}
			$con->sql_freeresult($q1);
		}
	}
	
	// php api.pmos.php generate_si -branch=HQ,GURUN
	function generate_si(){ 
		global $con, $arg,$is_http;
		
		$is_All = 0;
		
		if(!$is_http){
			$str = $arg[2];
			if (strpos($str, "-branch=") == 0){
				list($dummy, $branch_code) = explode("=", $str, 2);
				if (strpos($branch_code, "all") == 0) $is_All = 1;
				else	$branch_code_list = explode(",", $branch_code);
			}
	
			$bid_list = array();
			if ($is_All){
				$q1 = $con->sql_query("select id from branch");
				while ($row = $con->sql_fetchassoc($q1)){
					$bid_list[] = mi($row["id"]);
				}
				$con->sql_freeresult($q1);
			}else{
				for ($i = 0; $i < count($branch_code_list); $i++){
					$bcode = $branch_code_list[$i];
					$q1 = $con->sql_query("select id from branch where code = ".ms($bcode));
					$row = $con->sql_fetchrow($q1);
					$con->sql_freeresult($q1);
					
					if(!$row)	die("Invalid $bcode\n");
					$bid_list[] = mi($row["id"]);
				}
			}
			
			if(!$bid_list)	die("No branch.\n");
			
			// get sku items last check timestamp
			foreach($bid_list as $bid){
				$new_lastupdate = '';
				
				//sku_items
				$q1 = $con->sql_query("select si.id , si.sku_item_code,
				if(si.lastupdate > psi.last_update, si.lastupdate, ifnull(psi.last_update,si.lastupdate)) as 'lastupdate' 
				from sku_items si
				left join pmos_sku_items psi on si.id = psi.sku_item_id and psi.branch_id =$bid
				order by lastupdate");
				
				while($r = $con->sql_fetchassoc($q1)){
					$temp_arr = $r;
					$upd = array();
					$upd['branch_id'] = $bid;
					$upd['sku_item_id'] = $r['id'];
					$upd['sku_item_code'] = $r['sku_item_code'];
					$upd['last_update'] = $r['lastupdate'];
					
					$con->sql_query("replace into pmos_sku_items ".mysql_insert_by_field($upd));
					
					if(strtotime($r['lastupdate']) > strtotime($new_lastupdate)){
						$new_lastupdate = $r['lastupdate'];
					}
				}
				$con->sql_freeresult($q1);
				
				// sku_items_price
				$q2 = $con->sql_query("select si.id , si.sku_item_code,
				if(sip.last_update > psi.last_update, sip.last_update, ifnull(psi.last_update,sip.last_update)) as 'lastupdate' 
				from sku_items_price sip
				left join pmos_sku_items psi on sip.sku_item_id = psi.sku_item_id and sip.branch_id = psi.branch_id
				left join sku_items si on sip.sku_item_id = si.id
				where sip.branch_id = $bid 
				order by lastupdate"); 
				
				while($r = $con->sql_fetchassoc($q2)){
					$upd = array();
					$upd['branch_id'] = $bid;
					$upd['sku_item_id'] = $r['id'];
					$upd['sku_item_code'] = $r['sku_item_code'];
					$upd['last_update'] = $r['lastupdate'];
					
					$con->sql_query("replace into pmos_sku_items ".mysql_insert_by_field($upd));
					
					if(strtotime($r['lastupdate']) > strtotime($new_lastupdate)){
						$new_lastupdate = $r['lastupdate'];
					}
				}
				$con->sql_freeresult($q2);
				
				// sku_items_cost
				$q3 = $con->sql_query("select si.id , si.sku_item_code,
				if(sic.last_update > psi.last_update, sic.last_update, ifnull(psi.last_update,sic.last_update)) as 'lastupdate' 
				from sku_items_cost sic
				left join pmos_sku_items psi on sic.sku_item_id = psi.sku_item_id and sic.branch_id = psi.branch_id
				left join sku_items si on sic.sku_item_id = si.id
				where sic.branch_id = $bid 
				order by lastupdate");
				
				while($r = $con->sql_fetchassoc($q3)){
					$upd = array();
					$upd['branch_id'] = $bid;
					$upd['sku_item_id'] = $r['id'];
					$upd['sku_item_code'] = $r['sku_item_code'];
					$upd['last_update'] = $r['lastupdate'];
					
					$con->sql_query("replace into pmos_sku_items ".mysql_insert_by_field($upd));
					
					if(strtotime($r['lastupdate']) > strtotime($new_lastupdate)){
						$new_lastupdate = $r['lastupdate'];
					}
				}
				$con->sql_freeresult($q3);
				if (!$new_lastupdate) $new_lastupdate = $lastupdate;
					
				$this->set_pmos_setting($bid, 'si_lastupdate', $new_lastupdate);
			}
			print "Done ";
		}
	}*/
	
	function test_submit(){
		global $con, $smarty;
		
		$this->display("api.pmos.test_submit.tpl");
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
		
		$pmos_order_id = trim($form['order_id']);
		if(!$pmos_order_id)	$this->error_die($this->err_list["no_order_id"]);
		
		$this->put_log("PMOS Order ID: $pmos_order_id");
		
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
		$pmos_do = $this->get_pmos_do($pmos_order_id);
		
		$upd = array();
		
		if($pmos_do['branch_id'] > 0 && $pmos_do['do_id'] > 0){	// have
			if($pmos_do['branch_id'] != $bid)	$this->error_die($this->err_list["incorrect_branch"]);
			
			$q1 = $con->sql_query("select * from do where branch_id=$bid and id=".mi($pmos_do['do_id']));
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
		$upd['do_date'] = date("Y-m-d", strtotime($form['order_date']));
		$upd['price_indicate'] = 2;
		$upd['amt_need_update'] = 1;
		$upd['do_type'] = 'open';
		$upd['active'] = 1;
		$upd['status'] = 0;
		$upd['approved'] = 0;
		$upd['open_info']['name'] = $form['purchaser_name'];
		$upd['open_info']['address'] = $form['shipping_address'];
		$upd['remark'] = "Billing Address\n".$form['billing_address']."\nPMOS Order ID: ".$pmos_order_id;
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
		if(!$items_list)	$this->error_die($this->err_list["no_sku_founddie"]);
		
		//print_r($upd);
		//print_r($items_list);
		
		// create header
		$upd['open_info'] = serialize($upd['open_info']);
		if($do_id){	// update
			$con->sql_query("update do set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$do_id");
			
			$con->sql_query("delete from do_items where branch_id=$bid and do_id=$do_id");
			$con->sql_query("delete from do_open_items where branch_id=$bid and do_id=$do_id");
			
			$upd_pmos = array();
			$upd_pmos['last_checking'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("update pmos_do set ".mysql_update_by_field($upd_pmos)." where pmos_order_id=".ms($pmos_order_id));
		}else{	// insert
			$con->sql_query("insert into do ".mysql_insert_by_field($upd));
			$do_id = $con->sql_nextid();
			
			// create relationship
			$upd_pmos = array();
			$upd_pmos['pmos_order_id'] = $pmos_order_id;
			$upd_pmos['branch_id'] = $bid;
			$upd_pmos['do_id'] = $do_id;
			$upd_pmos['added'] = 'CURRENT_TIMESTAMP';
			$upd_pmos['last_checking'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("replace into pmos_do ".mysql_insert_by_field($upd_pmos));
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
			send_pm2($to_list, "Delivery Order POSTED from PMOS (ID#$do_id)", "do.php?&a=view&id=$do_id&branch_id=$bid", $others);
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
		// loop each pmos order id
		foreach($form['order_id'] as $pmos_order_id){
			$upd = array();
			
			$pmos_order_id = trim($pmos_order_id);
			
			$this->put_log("Check PMOS Order ID: $pmos_order_id");
			
			if(!$pmos_order_id)	continue;
			
			// try to get whether already got data
			$pmos_do = $this->get_pmos_do($pmos_order_id);
			if(!$pmos_do){
				//$this->error_die(sprintf($this->err_list["order_not_found"], $pmos_order_id));
				$upd['error'] = sprintf($this->err_list["order_not_found"], $pmos_order_id);
				$this->put_log(sprintf($this->err_list["order_not_found"], $pmos_order_id));
			}else{
				if($pmos_do['branch_id'] != $bid)	$this->error_die($this->err_list["incorrect_branch"]);
				
				$q1 = $con->sql_query("select * from do where branch_id=$bid and id=".mi($pmos_do['do_id']));
				$do = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				if(!$do)	$this->error_die($this->err_list["arms_do_not_found"]);
				
				$upd['arms_order_id'] = $do['id'];
				$upd['shipment_status'] = $this->return_shipment_status_string($do);
				
				if($upd['shipment_status'] == 'Completed'){
					list($shipment_method, $tracking_code) = explode(";", $do['checkout_remark']);
					$upd['shipment_method'] = trim($shipment_method);
					$upd['tracking_code'] = trim($tracking_code);
				}
				$this->put_log("ARMS Order ID: ".$do['id'].", Status: ".$upd['shipment_status']);
			}
			
			$ret[$pmos_order_id] = $upd;
			
			
		}
		
		if(!$ret){
			$this->error_die($this->err_list["no_order_id"]);
		}
		
		$this->put_log("Sending result to PMOS");
		print json_encode($ret);
		$this->put_log("Result sent.");
		exit;
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
}

$API_PMOS = new API_PMOS('API for PMOS');
?>