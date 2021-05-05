<?php
/*
php cron.gst_batch_price_change.php -branch=dev -month=12 -sku_type=all -category=6994,5385 -is_run
===================
5/21/2018 4:04 PM Andy
- New Script to generate base price change (due to GST reduced to 0% at 2018-06-01)

5/22/2018 11:21 AM Andy
- Add -rounding_method=payment_method
- Add -export_zip
- Change default month filter to 12.
- Enhanced to skip zero selling price.

5/30/2018 10:28 AM Andy
- Enhanced to check recently created sku.

5/31/2018 2:22 PM Andy
- Enhanced to have rounding_method=round_1_decimal.

6/1/2018 1:29 PM Andy
- Change to only change for tax code 'SR','DS','GS','AJS'.
- Enhanced to have -with_stock
- Enhanced to always check parent child sku.

6/5/2018 11:06 AM Andy
- Enhanced to have -change_now
*/
define("TERMINAL",1);
include("config.php");
include_once("default_config.php");
if((version_compare(PHP_VERSION, '7.0.0', '>='))){
	require("include/mysqli.php");
}else{
	require("include/mysql.php");
}

require_once('include/functions.php');

ini_set('memory_limit', '1024M');
set_time_limit(0);
error_reporting (E_ALL ^ E_NOTICE);

// check if myself is running, exit if yes
if (!preg_match('/(root|arms|admin|wsatp)/', `whoami`) || $config['arms_go_modules']){
	@exec('ps x | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
	print "Checking other process using ps x\n";
}else{
	@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
	print "Checking other process using ps ax\n";
}
  
  
if (count($exec)>1)
{
	print date("[H:i:s m.d.y]")." Another process is already running\n";
	print_r($exec);
	exit;
}

$argv = $_SERVER['argv'];
$CRON_GST_BATCH_PRICE = new CRON_GST_BATCH_PRICE();
$CRON_GST_BATCH_PRICE->start();

class CRON_GST_BATCH_PRICE {
	var $b_list = array();
	var $month_filter = 12;
	var $first_date = 0;
	//var $limit = 3500;
	var $sku_type = 'outright';
	var $item_per_batch = 1000;
	var $effective_date = '2018-06-01';
	var $cat_id_list = array();
	var $cat_id_info = array();
	var $is_run = false;
	var $is_export_zip = false;
	var $file_folder = 'tmp/CRON_GST_BATCH_PRICE';
	var $custom_rounding_method = '';
	var $allowed_rounding_method = array('payment_method', 'round_1_decimal', 'no_round');
	var $with_stock = false;
	var $item_tax_code_list = array('SR','DS','GS','AJS');
	var $change_now = false;
	var $sku_filter = array();
	var $with_qprice = false;
	
	function filter_argv(){
		global $argv, $con, $config;
		
		//print_r($argv);
		
		$dummy = array_shift($argv);
		$this->b_list = array();

		while($cmd = array_shift($argv)){
			list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
			if($cmd_head == "-branch"){
				$branch_filter = 'where active=1';
				if($cmd_value == 'all'){
					if(!$config['single_server_mode']){
						$bcode = BRANCH_CODE;
						$branch_filter .= ' and code='.ms($bcode);
					}
				}else{
					$bcode_list = array_map("ms", explode(",", trim($cmd_value)));
					$branch_filter .= ' and code in ('.join(",", $bcode_list).")";
				}

				$con->sql_query("select id,code from branch $branch_filter order by sequence,code");
				while($r = $con->sql_fetchassoc()){
					$this->b_list[] = $r;
				}
				$con->sql_freeresult();
			}elseif($cmd_head == "-month"){
				$this->month_filter = mi($cmd_value);
			}elseif($cmd_head == "-sku_type"){
				$sku_type = trim($cmd_value);
				if($sku_type == 'all'){
					$this->sku_type = '';
				}else{
					$this->sku_type = $sku_type;
				}
			}elseif($cmd_head == "-category"){
				$this->cat_id_list = explode(",", trim($cmd_value));
			}elseif($cmd_head == "-export_zip"){
				$this->is_export_zip = true;
			}elseif($cmd_head == "-rounding_method"){
				$this->custom_rounding_method = trim($cmd_value);
				if($this->custom_rounding_method == '' || !in_array($this->custom_rounding_method, $this->allowed_rounding_method)){
					die("Invalid Rounding Method.\n");
				}
			}elseif($cmd_head == "-with_stock"){
				$this->with_stock = true;
			}elseif($cmd_head == "-is_run"){
				$this->is_run = true;
			}elseif($cmd_head == "-change_now"){
				$this->change_now = true;
			}elseif($cmd_head == "-with_qprice"){
				$this->with_qprice = true;
			}elseif($cmd_head == "export_change_price"){
				$this->export_change_price_all();
			}else{
				print "Unknown command $cmd\n";
				exit;
			}
		}
	}
	
	function check_argv(){
		// Branch
		if(!$this->b_list)	die("Branch not found.\n");
		
		// Month
		if($this->month_filter <=0 ){
			die("Invalid Month Filter.\n");
		}else{
			$this->first_date = date("Y-m-d", strtotime("-".$this->month_filter." month"));
		}
		
		// Category
		if($this->cat_id_list){
			foreach($this->cat_id_list as $cat_id){
				$cat_info = get_category_info($cat_id);
				if(!$cat_info['id']){
					die("Invalid Category ID '$cat_id'.\n");
				}
				$this->cat_id_info[$cat_id] = $cat_info;
			}
		}
		
	}
	
	function start(){
		print "Start\n";
		
		$this->filter_argv();
		
		$this->check_argv();
		
		if($this->is_export_zip){
			$this->export_zip();
		}else{
			$this->run_all();
		}
		
	}
	
	private function run_all(){
		global $con;
		
		print "Branch: ";
		$str = '';
		foreach($this->b_list as $b){
			if($str)	$str .= ", ";
			$str .= $b['code'];
		}
		print $str."\n";
		
		print "From Date: ".$this->first_date."\n";
		
		foreach($this->b_list as $b){
			$this->run_by_branch($b);
		}
	}
	
	private function run_by_branch($b){
		global $con, $config;
		
		$bid = mi($b['id']);
		print "\nChecking Branch ".$b['code']."\n";
		print "Branch ID: $bid\n";
		
		if($this->with_stock){
			print "Check Got Stock: TRUE\n";
		}
		
		if($bid <=0){
			print "Invalid Branch ID.\n";
			return;
		}
		
		$prms = array();
		$prms['branch_id'] = $bid;
		$prms['date'] = date("Y-m-d");
		$branch_is_under_gst = check_gst_status($prms);
		
		if(!$branch_is_under_gst){
			print "Branch is not under GST. Skipped\n";
			return;
		}
		
		// Construct SKU Filter
		// SKU Type
		$this->sku_filter = array();
		if($this->sku_type){
			if($this->sku_type == 'outright'){
				$this->sku_filter[] = "sku.sku_type in (".ms($this->sku_type).",'')";
			}else{
				$this->sku_filter[] = "sku.sku_type=".ms($this->sku_type);
			}
			
			print "SKU Type: ".strtoupper($this->sku_type)."\n";
		}
		
		// Category
		if($this->cat_id_list){
			print "Category: \n";
			foreach($this->cat_id_list as $cat_id){
				print " > ".$this->cat_id_info[$cat_id]['description']."\n";
				
				$cat_lv = $this->cat_id_info[$cat_id]['level'];
				$cat_filter[] = "cc.p".$cat_lv."=".$cat_id;
			}
			$this->sku_filter[] = "(".join(' or ', $cat_filter).")";
		}
		
		
		
		$this->sku_filter[] = "if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit', cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes'";
		$this->sku_filter[] = "output_gst.code in (".join(',', array_map("ms", $this->item_tax_code_list)).")";
		
		if($this->change_now){
			$this->change_price_now($b);
			return;
		}
		
		
		$tbl = "tmp_gst_batch_price_b".$bid;
		$con->sql_query("drop table if exists $tbl");
		
		$con->sql_query("create table if not exists $tbl(
			sku_item_id int primary key,
			gst_id int,
			gst_code char(30),
			gst_rate double not null default 0,
			index (gst_rate)
		)");
		
		$str_sku_filter = '';
		if($this->sku_filter){
			$str_sku_filter = ' and '.join(' and ', $this->sku_filter);
		}
		
		// Get Recently POS Data
		print "- Get POS Data... ";
		$con->sql_query("insert IGNORE into $tbl
		(sku_item_id)
		(select distinct pi.sku_item_id
			from pos_items pi
			join sku_items si on pi.sku_item_id=si.id
			join sku on sku.id=si.sku_id
			join category_cache cc on cc.category_id=sku.category_id
			where pi.branch_id=$bid and pi.date>=".ms($this->first_date)." and pi.sku_item_id>0 $str_sku_filter)");
		print mi($con->sql_affectedrows())." sku.\n";
		
		// Get Recently DO Data
		print "- Get DO Data... ";
		$con->sql_query("insert IGNORE into $tbl
		(sku_item_id)
		(select distinct di.sku_item_id
			from do_items di
			join do on do.branch_id=di.branch_id and do.id=di.do_id
			join sku_items si on di.sku_item_id=si.id
			join sku on sku.id=si.sku_id
			join category_cache cc on cc.category_id=sku.category_id
			where do.branch_id=$bid and do.do_date>=".ms($this->first_date)." and sku_item_id>0 $str_sku_filter)");
		print mi($con->sql_affectedrows())." sku.\n";
		
		// Get Recently Created SKU
		print "- Get Recently Created SKU... ";
		$con->sql_query("insert IGNORE into $tbl
		(sku_item_id)
		(select si.id
			from sku_items si
			join sku on sku.id=si.sku_id
			join category_cache cc on cc.category_id=sku.category_id
			where si.added>=".ms($this->first_date)." and si.added<=".ms($this->effective_date." 23:59:59")." $str_sku_filter)");
		print mi($con->sql_affectedrows())." sku.\n";
		
		if($this->with_stock){
			// Check With Stock
			print "- Check Got Stock... ";
			$con->sql_query("insert IGNORE into $tbl
			(sku_item_id)
			(select distinct(si.id) as sid
				from sku_items si
				join sku on sku.id=si.sku_id
				join sku_items_cost sic on sic.sku_item_id=si.id
				join category_cache cc on cc.category_id=sku.category_id
				where sic.qty<>0 $str_sku_filter)");
			print mi($con->sql_affectedrows())." sku.\n";
		}
		
		// Insert all Parent / Child SKU
		print "- Get Parent Child SKU... ";
		$con->sql_query("insert IGNORE into $tbl
		(sku_item_id)
		(select si.id
			from sku_items si
			join sku on sku.id=si.sku_id
			join category_cache cc on cc.category_id=sku.category_id
			where si.sku_id in (select si2.sku_id from $tbl tbl join sku_items si2 on si2.id=tbl.sku_item_id) $str_sku_filter)");
		print mi($con->sql_affectedrows())." sku.\n";
		
		// update gst rate into table
		print "- Get GST Rate...\n";
		$con->sql_query("update $tbl tbl 
			join sku_items si on si.id=tbl.sku_item_id
			join sku on sku.id=si.sku_id
			left join category cat on cat.id = sku.category_id
			left join category_cache cc on cc.category_id=cat.id
			join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
			set tbl.gst_id=output_gst.id, tbl.gst_code=output_gst.code, tbl.gst_rate=output_gst.rate");
		
		// Delete zero rated item
		print "- Remove Zero Rated SKU... ";
		$con->sql_query("delete from $tbl where gst_code not in (".join(',', array_map("ms", $this->item_tax_code_list)).")");
		$affected_rows = $con->sql_affectedrows();
		print "$affected_rows removed.\n";
		
		print "- Remove Previously Generated Items... ";
		$con->sql_query("delete tbl
			from $tbl tbl
			join sku_items_future_price_items fpi on fpi.sku_item_id=tbl.sku_item_id and fpi.branch_id=$bid
			join sku_items_future_price fp on fp.branch_id=fpi.branch_id and fp.id=fpi.fp_id
			where fp.branch_id=$bid and date=".ms($this->effective_date)." and hour=0 and minute=0 and effective_branches like '%i:".$bid.";%' and fp.active=1 and fp.status=1 and fp.approved=1");
		$affected_rows = $con->sql_affectedrows();
		print "$affected_rows removed.\n";
		
		// Get Total SKU Count
		$con->sql_query("select count(*) as c from $tbl");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$total_row = mi($tmp['c']);
		print "- Total Item: ".$total_row."\n";
		
		if($this->limit){
			$str_limit = "limit ".$this->limit;
			print "- Testing $this->limit SKU only\n";
		}
		
		// Loop SKU
		print "- Processing...\n";
		$curr_row = 0;
		$fp_id = 0;
		$fp_item_count = 0;
		$fp_id_list = array();
		$skip_zero_price = 0;
		
		$q1 = $con->sql_query("select tbl.*, ifnull(sip.price, si.selling_price) as curr_sp, ifnull(sip.trade_discount_code, sku.default_trade_discount_code) as curr_trade_discount_code, ifnull(sic.grn_cost, si.cost_price) as cost  
			from $tbl tbl
			join sku_items si on tbl.sku_item_id=si.id
			join sku on sku.id=si.sku_id
			left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=tbl.sku_item_id
			left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=tbl.sku_item_id
			order by tbl.sku_item_id
			$str_limit");
		while($r = $con->sql_fetchassoc($q1)){
			$curr_row++;
			print "\r$curr_row / $total_row . . .";
			$sid = mi($r['sku_item_id']);
			
			$r['gst_rate'] = 6;
			
			if($fp_item_count >= $this->item_per_batch){
				$fp_id = 0;
				$fp_item_count = 0;
			}
			
			if($r['curr_sp'] <= 0){
				$skip_zero_price ++;
				continue;	// skip zero price
			}	
			
			// Normal Price
			$price_data = array();
			$tmp = array();
			$tmp['type'] = 'normal';
			$tmp['trade_discount_code'] = $r['curr_trade_discount_code'];
			$tmp['selling_price'] = $r['curr_sp'];
			$future_selling_price = round($tmp['selling_price'] / ($r['gst_rate'] + 100) * 100, 2);
			if($this->custom_rounding_method == 'payment_method'){	// Use Payment Rounding Method
				$future_selling_price = $this->payment_round($future_selling_price);
			}elseif($this->custom_rounding_method == 'round_1_decimal'){
				$future_selling_price = round($future_selling_price, 1);
			}else{
				$future_selling_price = process_gst_sp_rounding_condition($future_selling_price);
			}
			$tmp['future_selling_price'] = $future_selling_price;
			
			$price_data[] = $tmp;
			
			// MPrice
			if($config['sku_multiple_selling_price']){
				foreach($config['sku_multiple_selling_price'] as $mprice_type){
					$con->sql_query("select * from sku_items_mprice where branch_id=$bid and sku_item_id=$sid and type=".ms($mprice_type));
					$simp = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($simp){
						$tmp = array();
						$tmp['type'] = $mprice_type;
						$tmp['trade_discount_code'] = $simp['curr_trade_discount_code'];
						$tmp['selling_price'] = $simp['price'];
						
						if($tmp['selling_price'] <= 0){
							$skip_zero_price ++;
							continue;	// skip zero price
						}
						
						$future_selling_price = round($tmp['selling_price'] / ($r['gst_rate'] + 100) * 100, 2);
						
						if($this->custom_rounding_method == 'payment_method'){	// Use Payment Rounding Method
							$future_selling_price = $this->payment_round($future_selling_price);
						}else{
							$future_selling_price = process_gst_sp_rounding_condition($future_selling_price);
						}
						
						$tmp['future_selling_price'] = $future_selling_price;
						
						$price_data[] = $tmp;
					}
				}
			}
			
			
			if($this->is_run){
				if(!$fp_id){
					$ins = array();
					$ins['branch_id'] = $bid;
					$ins['date_by_branch'] = 0;
					$ins['date'] = $this->effective_date;
					$ins['hour'] = 0;
					$ins['minute'] = 0;
					$ins['effective_branches'] = serialize(array($bid=>$bid));
					$ins['user_id'] = 1;
					$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
					$ins['active'] = 1;
					$ins['approved'] = $ins['status'] = 0;
					
					$con->sql_query("select max(id) from sku_items_future_price where branch_id = $bid");
					$ins['id'] = $con->sql_fetchfield(0);
					$ins['id'] += 1;
					$con->sql_freeresult();
					
					$con->sql_query("insert into sku_items_future_price ".mysql_insert_by_field($ins));
					$fp_id = $con->sql_nextid();
					
					$fp_id_list[] = $fp_id;
				}
				
				foreach($price_data as $data){
					$ins = array();
					$ins['branch_id'] = $bid;
					$ins['fp_id'] = $fp_id;
					$ins['sku_item_id'] = $sid;
					$ins['cost'] = $r['cost'];
					$ins['selling_price'] = $data['selling_price'];
					$ins['type'] = $data['type'];
					$ins['trade_discount_code'] = $data['trade_discount_code'];
					$ins['future_selling_price'] = mf($data['future_selling_price']);
					$ins['gst_rate'] = $r['gst_rate'];
					$ins['gst_id'] = $r['gst_id'];
					$ins['gst_code'] = $r['gst_code'];
					
					$con->sql_query("select max(id) from sku_items_future_price_items where branch_id = $bid");
					$ins['id'] = $con->sql_fetchfield(0);
					$ins['id'] += 1;
					$con->sql_freeresult();
					
					$con->sql_query("insert into sku_items_future_price_items ".mysql_insert_by_field($ins));
				}
			}

			$fp_item_count++;
		}
		$con->sql_freeresult($q1);
		
		print "\n";
		
		print $skip_zero_price." Zero Selling Price Skipped.\n";
		
		if($fp_id_list){
			print "- ".count($fp_id_list)." Future Price Batched Created.\n";
			
			// Mark Future Price Batch as Approved
			print "- Mark Future Price Batch as Approved...\n";
			$con->sql_query("update sku_items_future_price set status=1, approved=1 where branch_id=$bid and id in (".join(',', $fp_id_list).")");
		}
		
		print "Done.\n";
	}
	
	private function payment_round($amount){
		return round($amount * 2, 1)/2;
	}
	
	private function export_zip(){
		if(!is_dir($this->file_folder))	check_and_create_dir($this->file_folder);
		
		print "Branch: ";
		$str = '';
		foreach($this->b_list as $b){
			if($str)	$str .= ", ";
			$str .= $b['code'];
		}
		print $str."\n";
		
		foreach($this->b_list as $b){
			$this->export_zip_by_branch($b);
		}
	}
	
	private function export_change_price_all(){
		global $con;
	}
	
	private function export_zip_by_branch($b){
		global $con, $config;
		
		$bid = mi($b['id']);
		print "\nChecking Branch ".$b['code']."\n";
		print "Branch ID: $bid\n";
		
		if($bid <=0){
			print "Invalid Branch ID.\n";
			return;
		}
		
		$file_name_prefix = $b['code']."_".time();
		$zipfilename = $file_name_prefix.".zip";
		
		$header_line = array('Branch Code', 'Batch ID', 'ARMS Code', 'MCode', 'ArtNo', 'SKU Description', 'Type', 'OLD Price', 'New Price');
					
		// Get Batch Price Change
		print "- Loading Batch Price Change... ";
		$q1 = $con->sql_query("select fp.id 
			from sku_items_future_price fp
			where fp.branch_id=$bid and fp.date=".ms($this->effective_date)." and fp.active=1 and fp.status=1 and fp.approved=1
			order by fp.id");
		$total_row = mi($con->sql_numrows($q1));
		print "$total_row Batch Found.\n";
		
		$curr_row = 0;
		// Loop Batch
		while($r = $con->sql_fetchassoc($q1)){
			$curr_row++;
			print "\r$curr_row / $total_row . . .";
			
			$fp_id = mi($r['id']);
			
			// Create New CSV
			$file_name = $file_name_prefix."_".$fp_id.".csv";
			$fp = fopen($this->file_folder. "/".$file_name, 'w');
			
			// Insert Header Line
			fputcsv($fp, array_values($header_line));
			
			// Get Items
			$q2 = $con->sql_query("select fpi.type, si.sku_item_code, si.mcode, si.artno, si.description, fpi.selling_price, fpi.future_selling_price
				from sku_items_future_price_items fpi
				join sku_items si on si.id=fpi.sku_item_id
				where fpi.branch_id=$bid and fpi.fp_id=$fp_id
				order by si.sku_item_code, if(type='normal',0,type)");
			while($item = $con->sql_fetchassoc($q2)){
				$tmp = array($b['code'], $fp_id, $item['sku_item_code'], $item['mcode'], $item['artno'], $item['description'], $item['type'], $item['selling_price'], $item['future_selling_price']);
				fputcsv($fp, $tmp);
			}
			$con->sql_freeresult($q2);
			
			fclose($fp);
			chmod($this->file_folder. "/".$file_name, 0777);
		}
		$con->sql_freeresult($q1);
		
		print "\n";
		
		if($total_row > 0){
			exec("cd " . $this->file_folder."; zip -9 $zipfilename $file_name_prefix*.csv");
		}
		
		print "Done. File = ".$this->file_folder."/".$zipfilename."\n";
	}
	
	private function change_price_now($b){
		global $con, $config;
		
		$bid = mi($b['id']);
		if($bid<=0){
			"Invalid Branch ID.\n";
			return;
		}
		print "Using Change NOW!\n";
		
		$tbl = "tmp_gst_change_price_b".$bid;
		$con->sql_query("drop table if exists $tbl");
		$con->sql_query("create temporary table if not exists $tbl(
			sku_item_id int primary key
		)");
		
		$tbl_export_data = $tbl."_data";
		$con->sql_query("create table if not exists $tbl_export_data(
			sku_item_id int primary key,
			type char(20),
			old_price double not null default 0,
			new_price double not null default 0,
			update_time timestamp default 0,
			index type (type)
		)");
		
		$sku_filter = $this->sku_filter;
		$sku_filter[] = "si.added<".ms($this->effective_date);
		$sku_filter[] = "if(sip.last_update is null or sip.last_update<".ms($this->effective_date).",1,0)=1";
		$sku_filter[] = "if(sip.price is null,si.selling_price,sip.price)>0";
		$str_sku_filter = '';
		
		if($sku_filter){
			$str_sku_filter = join(' and ', $sku_filter);
		}
		
		
		// Put All SKU
		print "- Get All SKU... ";
		$con->sql_query($sql = "insert IGNORE into $tbl
		(sku_item_id)
		(select si.id
			from sku_items si
			join sku on sku.id=si.sku_id
			left join category cat on cat.id = sku.category_id
			left join category_cache cc on cc.category_id=cat.id
			join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
			left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
			where $str_sku_filter)");
		print mi($con->sql_affectedrows())." sku.\n";
		//print "$sql\n";
		
		// Get Total SKU Count
		$con->sql_query("select count(*) as c from $tbl");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$total_row = mi($tmp['c']);
		print "- Total Item: ".$total_row."\n";
		
		// Loop SKU
		print "- Processing...\n";
		$curr_row = 0;
		$skip_zero_price = 0;
		$skip_zero_mprice = 0;
		$skip_zero_qprice = 0;
		$sku_updated = 0;
		
		$q1 = $con->sql_query("select tbl.*, ifnull(sip.price, si.selling_price) as curr_sp, ifnull(sip.trade_discount_code, sku.default_trade_discount_code) as curr_trade_discount_code, ifnull(sic.grn_cost, si.cost_price) as cost, ifnull(sip.selling_price_foc, si.selling_foc) as selling_price_foc
			from $tbl tbl
			join sku_items si on tbl.sku_item_id=si.id
			join sku on sku.id=si.sku_id
			left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=tbl.sku_item_id
			left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=tbl.sku_item_id
			order by tbl.sku_item_id");
		while($r = $con->sql_fetchassoc($q1)){
			$curr_row++;
			print "\r$curr_row / $total_row . . .";
			$sid = mi($r['sku_item_id']);
			
			$simp_list = array();
			$siqp_list = array();
			$export_data_list = array();
			
			if($r['curr_sp'] <= 0){
				$skip_zero_price ++;
				continue;	// skip zero price
			}
			
			$new_selling_price = $this->calculate_new_price($r['curr_sp']);
						
			// sku_items_price
			$sip = array();
			$sip['branch_id'] = $bid;
			$sip['sku_item_id'] = $sid;
			$sip['last_update'] = 'CURRENT_TIMESTAMP';
			$sip['price'] = $new_selling_price;
			$sip['cost'] = $r['cost'];
			$sip['trade_discount_code'] = $r['curr_trade_discount_code'];
			$sip['selling_price_foc'] = $r['selling_price_foc'];
			
			// sku_items_price_history
			$siph = array();
			$siph['branch_id'] = $sip['branch_id'];
			$siph['sku_item_id'] = $sip['sku_item_id'];
			$siph['added'] = 'CURRENT_TIMESTAMP';
			$siph['price'] = $sip['price'];
			$siph['cost'] = $sip['cost'];
			$siph['source'] = 'SCRIPT';
			$siph['user_id'] = 1;
			$siph['trade_discount_code'] = $sip['trade_discount_code'];
			$siph['selling_price_foc'] = $sip['selling_price_foc'];
			
			// Export Data
			$export_data = array();
			$export_data['sku_item_id'] = $sid;
			$export_data['type'] = 'normal';
			$export_data['old_price'] = $r['curr_sp'];
			$export_data['new_price'] = $new_selling_price;
			$export_data_list[] = $export_data;
			
			
			// MPrice
			if($config['sku_multiple_selling_price']){
				foreach($config['sku_multiple_selling_price'] as $mprice_type){
					$con->sql_query("select * from sku_items_mprice where branch_id=$bid and sku_item_id=$sid and type=".ms($mprice_type)." and last_update<".ms($this->effective_date));
					$curr_simp = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($curr_simp){	// Currently Got MPrice
						if($curr_simp['price'] <= 0){
							$skip_zero_mprice ++;
							continue;	// skip zero price
						}
						
						$new_mprice = $this->calculate_new_price($curr_simp['price']);
						
						// sku_items_mprice
						$simp = array();
						$simp['branch_id'] = $bid;
						$simp['sku_item_id'] = $sid;
						$simp['type'] = $mprice_type;
						$simp['last_update'] = 'CURRENT_TIMESTAMP';
						$simp['price'] = $new_mprice;
						$simp['trade_discount_code'] = $curr_simp['trade_discount_code'];
						
						// sku_items_mprice_history
						$simph = array();
						$simph['branch_id'] = $simp['branch_id'];
						$simph['sku_item_id'] = $simp['sku_item_id'];
						$simph['type'] = $simp['type'];
						$simph['added'] = 'CURRENT_TIMESTAMP';
						$simph['price'] = $simp['price'];
						$simph['user_id'] = 1;
						$simph['trade_discount_code'] = $simp['trade_discount_code'];
						
						$simp_list[] = array('simp' => $simp, 'simph' => $simph);
						
						// Export Data
						$export_data = array();
						$export_data['sku_item_id'] = $sid;
						$export_data['type'] = $mprice_type;
						$export_data['old_price'] = $curr_simp['price'];
						$export_data['new_price'] = $new_mprice;
						$export_data_list[] = $export_data;
					}
				}
			}
			
			// QPrice
			if($this->with_qprice){
				$q_qprice = $con->sql_query("select * from sku_items_qprice where branch_id=$bid and sku_item_id=$sid and last_update<".ms($this->effective_date)." order by min_qty");
				
				while($curr_qprice = $con->sql_fetchassoc($q_qprice)){
					if($curr_qprice['price'] <= 0){
						$skip_zero_qprice ++;
						continue;	// skip zero price
					}
					
					$new_qprice = $this->calculate_new_price($curr_qprice['price']);
					
					// sku_items_qprice
					$siqp = array();
					$siqp['branch_id'] = $bid;
					$siqp['sku_item_id'] = $sid;
					$siqp['min_qty'] = $curr_qprice['min_qty'];
					$siqp['price'] = $new_qprice;
					$siqp['last_update'] = 'CURRENT_TIMESTAMP';
					
					// sku_items_qprice_history
					$siqph = array();
					$siqph['branch_id'] = $siqp['branch_id'];
					$siqph['sku_item_id'] = $siqp['sku_item_id'];
					$siqph['min_qty'] = $siqp['min_qty'];
					$siqph['price'] = $siqp['price'];
					$siqph['added'] = 'CURRENT_TIMESTAMP';
					$siqph['user_id'] = 1;
					
					$siqp_list[] = array('siqp' => $siqp, 'siqph' => $siqph);
					
					// Export Data
					$export_data = array();
					$export_data['sku_item_id'] = $sid;
					$export_data['type'] = "qprice(".$curr_qprice['min_qty'].")";
					$export_data['old_price'] = $curr_qprice['price'];
					$export_data['new_price'] = $new_qprice;
					$export_data_list[] = $export_data;
				}
				$con->sql_freeresult($q_qprice);
			}
			
			if($this->is_run){
				// sku_items_price
				$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($sip));
				// sku_items_price_history
				$con->sql_query("insert into sku_items_price_history ".mysql_insert_by_field($siph));
				
				if($simp_list){
					foreach($simp_list as $r){
						// sku_items_mprice
						if($r['simp'] && is_array($r['simp'])){
							$con->sql_query("replace into sku_items_mprice ".mysql_insert_by_field($r['simp']));
						}
						// sku_items_mprice_history
						if($r['simph'] && is_array($r['simph'])){
							$con->sql_query("insert into sku_items_mprice_history ".mysql_insert_by_field($r['simph']));
						}
					}
				}
				
				if($this->with_qprice && $siqp_list){
					foreach($siqp_list as $r){
						// sku_items_qprice
						if($r['siqp'] && is_array($r['siqp'])){
							$con->sql_query("replace into sku_items_qprice ".mysql_insert_by_field($r['siqp']));
						}
						// sku_items_qprice_history
						if($r['siqph'] && is_array($r['siqph'])){
							$con->sql_query("insert into sku_items_qprice_history ".mysql_insert_by_field($r['siqph']));
						}
					}
				}
				
				foreach($export_data_list as $export_data){
					$export_data['update_time'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("replace into $tbl_export_data ".mysql_insert_by_field($export_data));
				}
				
				$sku_updated++;
			}
			
		}
		$con->sql_freeresult($q1);
		
		print "\n";
		print $skip_zero_price." Zero Selling Price Skipped.\n";	
		print $skip_zero_mprice." Zero MPrice Skipped.\n";
		if($this->with_qprice){
			print $skip_zero_qprice." Zero QPrice Skipped.\n";
		}
		if($this->is_run){
			print $sku_updated." sku updated.\n";
		}
		print "Done.\n";
	}
	
	private function calculate_new_price($price){
		$gst_rate = 6;
		$new_selling_price = round($price / ($gst_rate + 100) * 100, 2);
		
		if($this->custom_rounding_method != 'no_round'){	// No Round
			if($this->custom_rounding_method == 'payment_method'){	// Use Payment Rounding Method
				$new_selling_price = $this->payment_round($new_selling_price);
			}elseif($this->custom_rounding_method == 'round_1_decimal'){	// Round to 1 decimal
				$new_selling_price = round($new_selling_price, 1);
			}else{	// Round Using GST Setting
				$new_selling_price = process_gst_sp_rounding_condition($new_selling_price);
			}
		}
		
		return round($new_selling_price, 2);
	}
}


?>