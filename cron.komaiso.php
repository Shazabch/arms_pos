<?php
/*
4/7/2021 9:40 AM Andy
- Added New Integration "KOMAISO".

4/27/2021 3:10 PM Andy
- Fixed check finalised query.

4/29/2021 11:57 AM Andy
- Enhanced to download sku image based on sequence JPG, jpg, jpeg, png.
- Enhanced to sync sku by branch.
- Added -skip_photo and -skip_branch.
- Enhanced mcode checking and error message.
*/
define('armshq_komaiso',1);
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '2G');
set_time_limit(0);

include_once("komaiso.include.php");

class CRON_KOMAISO {
	var $b_list = array();
	var $skip_b_list = array();
	var $fp_path = '';
	var $Komaiso = false;
	var $sync_type_list = array('sku', 'sales');
	var $selected_sync_type = '';
	var $ftp_conn_id = false;
	
	var $attch_file_folder = "attch/komaiso";
	var $attch_file_folder_master = "attch/komaiso/master";
	var $attch_file_folder_sku_image = "attch/komaiso/master/sku_image";
	var $attch_file_folder_sales = "attch/komaiso/sales";
	var $attch_file_folder_others = "attch/komaiso/others";
	
	// For Sales
	var $date_from = '';
	var $date_to = '';
	var $regen_sales = false;
	var $skip_ftp = false;
	var $category_line_id = 0;
	var $skip_photo = false;
	var $sku_image_ext_list = array('JPG', 'jpg', 'jpeg', 'png');
	
	function __construct(){
	    global $con, $sessioninfo;

		// use this prevent wrong "include" path
		$this->fp_path = dirname(__FILE__)."/".basename(__FILE__, '.php').".running";	
		
		$this->fp = fopen($this->fp_path, "w");
		chmod($this->fp_path, 0777);
		
		$this->mark_start_process();
	}
	
	function __destruct() {
		if($this->ftp_conn_id){
			// close the connection
			ftp_close($this->ftp_conn_id);
		}
		
        $this->mark_close_process();
    }
	
	private function mark_start_process(){
		global $smarty;
		
		if(!is_writable(dirname($this->fp_path))){
			print "The folder '".dirname($this->fp_path)."' permission not allow this process to be run, please contact system admin.\n";
			exit;
		}
		
		if(!flock($this->fp, LOCK_EX | LOCK_NB)){
			print "Other process is running, please wait for them to finish.\n";
			exit;
		}
	}
	
	private function mark_close_process(){
		flock($this->fp, LOCK_UN);
	}
	
	function init_komaiso(){
		$this->Komaiso = new Komaiso();
		
		$result = $this->Komaiso->check_config();
		if(!$result['ok'] || $result['error']){
			print $result['error']."\n";
			exit;
		}
		
		check_and_create_dir($this->attch_file_folder);
		check_and_create_dir($this->attch_file_folder_master);
		check_and_create_dir($this->attch_file_folder_sales);
		check_and_create_dir($this->attch_file_folder_others);
		check_and_create_dir($this->attch_file_folder_sku_image);
	}
	
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
					$this->b_list[$r['id']] = $r;
				}
				$con->sql_freeresult();
			}elseif($cmd_head == "-skip_branch"){
				$bcode_list = array_map("ms", explode(",", trim($cmd_value)));
				$branch_filter .= ' and code in ('.join(",", $bcode_list).")";

				$con->sql_query("select id,code from branch $branch_filter order by sequence,code");
				while($r = $con->sql_fetchassoc()){
					$this->skip_b_list[$r['id']] = $r;
				}
				$con->sql_freeresult();
			}elseif($cmd_head == "-sync_type"){
				if(!in_array($cmd_value, $this->sync_type_list)){
					print "Invalid Sync Type\n";
					exit;
				}
				$this->selected_sync_type = $cmd_value;
			}elseif($cmd_head == "-date"){	// selected date
				if(date("Y", strtotime($cmd_value))<1999)	die("Date $cmd_value is invalid. Date must start at 1999-01-01.\n");
				$date = $cmd_value;
				
			}elseif($cmd_head == "-date_from"){	// date_from
				$cmd_value = date("Y-m-d", strtotime($cmd_value));
				if(!$cmd_value)	die("Invalid Date From.\n");
				if(date("Y", strtotime($cmd_value))<1999)	die("Date $cmd_value is invalid. Date must start at 1999-01-01.\n");
				$date_from = $cmd_value;
			}elseif($cmd_head == "-date_to"){	// date_to
				$cmd_value = date("Y-m-d", strtotime($cmd_value));
				if(!$cmd_value)	die("Invalid Date To.\n");
				if(date("Y", strtotime($cmd_value))<1999)	die("Date $cmd_value is invalid. Date must start at 1999-01-01.\n");
				$date_to = $cmd_value;
			}elseif($cmd_head == "-yesterday"){	// use yesterday
				$date = date("Y-m-d", strtotime("-1 day"));
			}elseif($cmd_head == "-recent_day"){	// use yesterday
				$num = mi($cmd_value);
				if($num<=0)	die("Recent Day must more than zero.\n");
				
				$date_to = date("Y-m-d", strtotime("-1 day"));
				$date_from = date("Y-m-d", strtotime("-".$num." day"));
			}elseif($cmd_head == "-regen_sales"){	// regenerate
				$this->regen_sales = true;
			}elseif($cmd_head == "-skip_ftp"){	// skip connect ftp
				$this->skip_ftp = true;
			}elseif($cmd_head == "-skip_photo"){	// skip download photo
				$this->skip_photo = true;
			}else{
				print "Unknown command $cmd\n";
				exit;
			}
		}
		
		if($this->skip_b_list && $this->b_list){
			// Remove skip branch from branch list
			foreach($this->skip_b_list as $bid => $b){
				if(isset($this->b_list[$bid])){
					unset($this->b_list[$bid]);
				}
			}
		}
			
		// Need Branch
		if(!$this->b_list)	die("Branch not found.\n");
		
		// Sync Sales need to check date
		if(!$this->selected_sync_type || $this->selected_sync_type == 'sales'){
			if(!$date && ($date_from || $date_to)){
				if(!$date_from){
					die("Please provide -date_from=\n");
				}
				if(!$date_to){
					die("Please provide -date_to=\n");
				}
				if(strtotime($date_to) < strtotime($date_from)){
					die("'Date To' cannot earlier than 'Date From'.\n");
				}
				$this->date_from = $date_from;
				$this->date_to = $date_to;
				
			}else{
				$this->date_from = $this->date_to = $date;
			}
			
			// Sales Date
			if(!$this->date_from){
				die("Please provide -date_from=\n");
			}
			if(!$this->date_to){
				die("Please provide -date_to=\n");
			}
		}
	}
	
	function start(){
		print "Start\n";
		
		$this->init_komaiso();
		$this->filter_argv();
		//$this->check_argv();
		
		
		if(!$this->selected_sync_type || $this->selected_sync_type == 'sku'){
			// Connect Master FTP Server
			if(!$this->skip_ftp)	$this->connect_master_ftp();
			
			// Sync Masterfile
			$this->sync_master();
		}
		
		if(!$this->selected_sync_type || $this->selected_sync_type == 'sales'){
			// Connect Master FTP Server
			if(!$this->skip_ftp)	$this->connect_master_ftp();
			
			// Sync Masterfile
			$this->sync_sales();
		}
		
		/*if(!$this->selected_sync_type || $this->selected_sync_type == 'sales' || $this->selected_sync_type == 'sales_arev' || $this->selected_sync_type == 'sales_navision'){
			// Upload Sales for Arev
			if(!$this->selected_sync_type || $this->selected_sync_type == 'sales' || $this->selected_sync_type == 'sales_arev'){
				$this->sync_sales_arev();
			}
			
			// Upload Sales for Navision
			if(!$this->selected_sync_type || $this->selected_sync_type == 'sales' || $this->selected_sync_type == 'sales_navision'){
				$this->sync_sales_navision();
			}
		}*/
		
		print "Sync Completed\n";
	}
	
	private function connect_master_ftp(){
		global $config;
		
		if($this->ftp_conn_id)	return;	// already connected
		
		print "Connecting to Komaiso Master FTP Server. . . ";
		//print_r($this->Komaiso->server_ftp_info);
		
		// set up basic connection
		$this->ftp_conn_id = ftp_connect($this->Komaiso->server_ftp_info['ip'], $this->Komaiso->server_ftp_info['port']);
		if(!$this->ftp_conn_id){
			print "Server Cannot be connect.\n";
			exit;
		}
		print "Connected.\n";
		print "Login to the server. . . ";

		// login with username and password
		$login_result = ftp_login($this->ftp_conn_id, $this->Komaiso->server_ftp_info['username'], $this->Komaiso->server_ftp_info['pass']);
		if(!$login_result){
			print "Failed to login to the Server.\n";
			ftp_close($this->ftp_conn_id);
			exit;
		}
		print "Success.\n";
		
		// Turn on Passive Mode
        ftp_pasv($this->ftp_conn_id, true);
	}
	
	private function set_cron_status($sync_type, $sub_type, $status = 'start', $params = array()){
		global $con;
		
		$upd = array();
		
		if($status == 'start'){
			$upd['sync_type'] = $sync_type;
			$upd['sub_type'] = $sub_type;
			//$upd['total_record'] = 0;
			//$upd['new_record'] = 0;
			//$upd['update_record'] = 0;
			//$upd['error_record'] = 0;
			$upd['error_list'] = '';
			$upd['start_time'] = 'CURRENT_TIMESTAMP';
			$upd['end_time'] = 0;
			$upd['status'] = 0;
			
			$con->sql_query_skip_logbin("replace into komaiso_cron_status ".mysql_insert_by_field($upd));
		}elseif($status == 'end'){
			//$upd['total_record'] = mi($params['total_record']);
			//$upd['new_record'] = mi($params['new_record']);
			//$upd['update_record'] = mi($params['update_record']);
			//$upd['error_record'] = mi($params['error_record']);
			$upd['error_list'] = serialize($params['error_list']);
			$upd['end_time'] = 'CURRENT_TIMESTAMP';
			//if($upd['error_record']>0 || (is_array($params['error_list']) && count($params['error_list'])>0)){
			if((is_array($params['error_list']) && count($params['error_list'])>0)){
				$upd['status'] = 2;	// finish with error
			}else{
				$upd['status'] = 1;	// finish without error
			}
			
			$con->sql_query_skip_logbin("update komaiso_cron_status set ".mysql_update_by_field($upd)." where sync_type=".ms($sync_type)." and sub_type=".ms($sub_type));
		}else{
			die("Unknown status.\n");
			return;
		}
	}
	
	private function sync_master(){
		print "=============== Sync Master ===============\n";
		
		// Sync SKU
		if(!$this->selected_sync_type || $this->selected_sync_type == 'master' || $this->selected_sync_type == 'sku'){
			//$this->sync_sku();
			foreach($this->b_list as $b){
				$this->sync_sku_by_branch($b);
			}
		}
	}
	
	private function sync_sku_by_branch($b){
		global $con, $appCore;
		
		$bid = mi($b['id']);
		$bcode = $b['code'];
		print ">>>>> Sync SKU (".$b['code'].") ...\n";
		
		// Mark Sync Status = Start
		$this->set_cron_status('master', 'sku_'.$bcode , 'start');
		
		// Create Table to store Komaiso records
		$tmp_tbl = "tmp_komaiso_sku_b".$bid;
		$con->sql_query_skip_logbin("drop table if exists $tmp_tbl");
		$con->sql_query_skip_logbin("create table if not exists $tmp_tbl(
			line int not null auto_increment primary key,
			mcode char(15) unique,
			link_code char(20) not null,
			brand_desc char(100) not null,
			sku_description char(100) not null,
			selling_price double not null default 0,
			promo_price double not null default 0,
			promo_start_date date,
			promo_end_date date,
			scale_type tinyint(1) not null default 0,
			uom_code char(6) not null,
			image_filename char(100) not null,
			dept_name char(100) not null,
			cat1_name char(100) not null,
			cat2_name char(100) not null,
			cat3_name char(100) not null,
			sku_type char(10) not null default 'OUTRIGHT'
		) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
		
		$local_filename = $this->attch_file_folder_master.'/sku_'.$bcode.'.csv';
		
		if(!$this->skip_ftp){
			////////// todo: get file from ftp server ///////////////
			// Change Directory to Masterfile Path
			if($this->Komaiso->server_ftp_info['master_path']){
				ftp_chdir($this->ftp_conn_id, $this->Komaiso->server_ftp_info['master_path']);
			}
			$server_filename = ($this->Komaiso->server_ftp_info['sku_filename'] ? $this->Komaiso->server_ftp_info['sku_filename'] : 'sku')."_".$bcode.".csv";
			
			
			// try to download $server_file and save to $local_file
			if (ftp_get($this->ftp_conn_id, $local_filename, $server_filename, FTP_ASCII)) {
				// Download Success
				print "Successfully written to $local_filename\n";
			} else {
				// Download Failed
				$err_msg = "There was a problem to download the file '$server_filename'";
				print $err_msg."\n";
				$params['error_list'] = array($err_msg);
				
				// Mark Sync Status = End (Got Error)
				$this->set_cron_status('master', 'sku_'.$bcode , 'end', $params);
				return;
			}
		}
		
		// Start MySQL Transaction
		$con->sql_begin_transaction();
		
		// Open File
		$f = fopen($local_filename, "rt");
		$header = fgetcsv($f);	// remove header
		
		$total_record = 0;
		$new_record = 0;
		$update_record = 0;
		$error_record = 0;
		$error_list = array();
		while($r = fgetcsv($f)){
			$total_record++;
			
			//print "$total_record) ".$line;
			print "\rChecking line $total_record";
			
			//print_r($r);
			
			$link_code = trim($r[0]);
			
			// Skip Empty row
			if(!$link_code)	continue;
			
			$mcode = trim($r[1]);
			$brand_desc = trim($r[3]);
			$sku_description = trim($r[4]);
			$selling_price = round($r[7], 2);
			$promo_price = round($r[8], 2);
			$promo_start_date = date("Y-m-d", strtotime(trim($r[9])));
			$promo_end_date = date("Y-m-d", strtotime(trim($r[10])));
			$scale_type = strtoupper(trim($r[11])) == 'TRUE' ? 2 : 0;	// 2 = Weighted, 0 = No
			$uom_code = trim($r[14]);
			if(!$uom_code)	$uom_code = 'EACH';
			$image_filename = trim($r[15]);
			$dept_name = trim($r[19]);
			$cat1_name = trim($r[20]);
			if(!$cat1_name)	$cat1_name = 'OTHERS';	// Minimum need to have Lv3 Category
			$cat2_name = trim($r[21]);
			$cat3_name = trim($r[22]);
			$sku_type = strtoupper(trim($r[23])) == 'TRUE' ? 'CONSIGN' : 'OUTRIGHT';
			
			// SKU Desciption cannot empty
			if(!$sku_description){
				$error_list[] = "Line ($total_record): SKU '$link_code' Desciption is Empty.";
				$error_record++;
				continue;
			}
			
			// MCode cannot empty
			if(!$mcode){
				$error_list[] = "Line ($total_record): SKU '$link_code' MCode is Empty.";
				$error_record++;
				continue;
			}
			
			// MCode is null
			if($mcode == "null"){
				$error_list[] = "Line ($total_record): SKU '$link_code' MCode value is 'null'.";
				$error_record++;
				continue;
			}
			
			// Check Duplicate link_code in the file
			$con->sql_query("select line, link_code from $tmp_tbl where link_code=".ms($link_code));
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($tmp){
				$error_list[] = "Line ($total_record): SKU '$link_code' is duplicated, it was already exists at line ".$tmp['line'];
				$error_record++;
				continue;
			}
			
			// Check Duplicate in the file
			$con->sql_query("select line, link_code from $tmp_tbl where mcode=".ms($mcode));
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($tmp){
				$error_list[] = "Line ($total_record): SKU '$link_code' MCode#$mcode is duplicated with line ".$tmp['line']." (SKU ".$tmp['link_code'].").";
				$error_record++;
				continue;
			}
			
			// Check Duplicate in database
			$con->sql_query("select id, link_code from sku_items where mcode=".ms($mcode)." and link_code<>".ms($link_code));
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($tmp){
				$error_list[] = "Line ($total_record): SKU '$link_code' MCode#$mcode is duplicated with database (SKU ".$tmp['link_code'].").";
				$error_record++;
				continue;
			}
			
			// Check Promotion Price
			if($promo_price > $selling_price){
				// Promotion Price is Higher than Normal Selling Price
				$error_list[] = "Line ($total_record): SKU Promotion Price '$promo_price' is higher than Normal Selling Price '$selling_price'.";
				$error_record++;
				continue;
			}
			if($promo_price < $selling_price){	// Got Promotion
				// Check Start Date Format - minimum year 2000
				if(date("Y", strtotime($promo_start_date)) < 2000){
					$error_list[] = "Line ($total_record): Promotion Start Date '$promo_start_date' is invalid.";
					$error_record++;
					continue;
				}
				// Check End Date Format - minimum year 2000
				if(date("Y", strtotime($promo_end_date)) < 2000){
					$error_list[] = "Line ($total_record): Promotion End Date '$promo_end_date' is invalid.";
					$error_record++;
					continue;
				}
				// Check to make sure end date is after start date
				if(strtotime($promo_start_date) > strtotime($promo_end_date)){
					$error_list[] = "Line ($total_record): Promotion End Date '$promo_end_date' earlier than Start Date '$promo_start_date'.";
					$error_record++;
					continue;
				}
			}
			
			// Store a record in arms table
			$ins = array();
			$ins['mcode'] = $mcode;
			$ins['link_code'] = $link_code;
			$ins['brand_desc'] = $brand_desc;
			$ins['sku_description'] = $sku_description;
			$ins['selling_price'] = $selling_price;
			$ins['promo_price'] = $promo_price;
			$ins['promo_start_date'] = $promo_start_date;
			$ins['promo_end_date'] = $promo_end_date;
			$ins['scale_type'] = $scale_type;
			$ins['uom_code'] = $uom_code;
			$ins['image_filename'] = $image_filename;
			$ins['dept_name'] = $dept_name;
			$ins['cat1_name'] = $cat1_name;
			$ins['cat2_name'] = $cat2_name;
			$ins['cat3_name'] = $cat3_name;
			$ins['sku_type'] = $sku_type;
			$con->sql_query_skip_logbin("insert into $tmp_tbl ".mysql_insert_by_field($ins));
			
			// Check and Auto Create New Brand
			$brand_id = $this->check_and_create_brand_by_desc($brand_desc);
			
			// Check and Auto Create New UOM 
			$uom_id = $this->check_and_create_uom_by_code($uom_code);
			
			// Check and Auto Create Category Line (Level 1)
			$cat_line_id = $this->check_and_create_category_line();
			
			// Check and Create Department
			$cat_dept_id = $this->check_and_create_category_by_desc($cat_line_id, 2, $dept_name);
			if($cat_dept_id <= 0){
				$error_list[] = "Line ($total_record): Error on Getting Department ID for Department '$dept_name'.";
				$error_record++;
				continue;
			}
			
			// Check and Create Category 1
			$cat_id_1 = $this->check_and_create_category_by_desc($cat_dept_id, 3, $cat1_name);
			if($cat_id_1 <= 0){
				$error_list[] = "Line ($total_record): Error on Getting Category ID for Category Lv3 '$cat1_name'.";
				$error_record++;
				continue;
			}
			
			$cat_id = $cat_id_1;
			
			if($cat2_name){
				// Check and Create Category 2
				$cat_id_2 = $this->check_and_create_category_by_desc($cat_id_1, 4, $cat2_name);
				if($cat_id_2 <= 0){
					$error_list[] = "Line ($total_record): Error on Getting Category ID for Category Lv4 '$cat2_name'.";
					$error_record++;
					continue;
				}
				$cat_id = $cat_id_2;
				
				if($cat3_name){
					// Check and Create Category 3
					$cat_id_3 = $this->check_and_create_category_by_desc($cat_id_2, 5, $cat3_name);
					if($cat_id_3 <= 0){
						$error_list[] = "Line ($total_record): Error on Getting Category ID for Category Lv5 '$cat3_name'.";
						$error_record++;
						continue;
					}
					$cat_id = $cat_id_3;
				}
			}
			
			// Get current sku_items by mcode
			$con->sql_query("select si.*, ifnull(sip.price, si.selling_price) as latest_price
				from sku_items si
				left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
				where si.mcode=".ms($mcode));
			$curr_si = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$sid = 0;
			$sku_id = 0;
			$promo = array();
			$promo_id = 0;
			if($curr_si){
				// Ezisting SKU
				$sid = mi($curr_si['id']);
				$sku_id = mi($curr_si['sku_id']);
				
				// Check sku Different
				$con->sql_query("select * from sku where id=$sku_id");
				$curr_sku = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				$sku_updated = $si_updated = false;
				if($curr_sku['category_id'] != $cat_id || $curr_sku['brand_id'] != $brand_id || $curr_sku['sku_type'] != $sku_type || $curr_sku['scale_type'] != $scale_type){
					$upd = array();
					$upd['category_id'] = $cat_id;
					$upd['brand_id'] = $brand_id;
					$upd['sku_type'] = $sku_type;
					$upd['scale_type'] = $scale_type;
					$con->sql_query("update sku set ".mysql_update_by_field($upd)." where id=$sku_id");
					$sku_updated = true;
				}
				
				// Check sku_items Different
				if($curr_si['link_code'] != $link_code || $curr_si['description'] != $sku_description || $curr_si['packing_uom_id'] != $uom_id){
					$upd = array();
					$upd['link_code'] = $link_code;
					$upd['description'] = $sku_description;
					//$upd['selling_price'] = $selling_price;
					$upd['packing_uom_id'] = $uom_id;
					$con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id=$sid");
					$si_updated = true;
				}
				
				// Check Selling Price Different
				if($curr_si['latest_price'] != $selling_price){
					// insert sku_items_price
					$ins = array();
					$ins['branch_id'] = $bid;
					$ins['sku_item_id'] = $sid;
					$ins['last_update'] = 'CURRENT_TIMESTAMP';
					$ins['price'] = $selling_price;
					//$ins['cost'] = $cost_price;
					$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($ins));
					
					// price history
					$ins = array();
					$ins['branch_id'] = $bid;
					$ins['sku_item_id'] = $sid;
					$ins['added'] = 'CURRENT_TIMESTAMP';
					$ins['price'] = $selling_price;
					//$ins['cost'] = $cost_price;
					$ins['source'] = 'CRON';
					$ins['user_id'] = -1;
					$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($ins));
					
					$si_updated = true;
				}
				
				if($sku_updated || $si_updated){
					$con->sql_query("update sku_items_cost set changed=1 where sku_item_id=$sid");
					
					if($sku_updated && !$si_updated){
						// need to touch on sku_items in order for it to sync to pos counter
						$con->sql_query("update sku_items set lastupdate=CURRENT_TIMESTAMP where id=$sid");
					}
				}
			}else{
				// New SKU
				// insert sku
				$sku_ins = array();
				$sku_ins['category_id'] = $cat_id;
				$sku_ins['uom_id'] = $uom_id;
				$sku_ins['vendor_id'] = 0;//$vendor_id;
				$sku_ins['brand_id'] = $brand_id;
				$sku_ins['status'] = 1;
				$sku_ins['active'] = 1;
				$sku_ins['sku_type'] = $sku_type;
				$sku_ins['apply_branch_id'] = 1;
				$sku_ins['added'] = 'CURRENT_TIMESTAMP';
				$sku_ins['scale_type'] = $scale_type;
						
				$con->sql_query("insert into sku ".mysql_insert_by_field($sku_ins));
				$sku_id = intval($con->sql_nextid());
				$sku_code = sprintf(ARMS_SKU_CODE_PREFIX, $sku_id);
				$con->sql_query("update sku set sku_code=".ms($sku_code)." where id=$sku_id");
				
				// insert sku_items
				$sku_item_code = sprintf(ARMS_SKU_CODE_PREFIX, $sku_id)."0000";
				$si_ins = array();
				$si_ins['is_parent'] = 1;
				$si_ins['packing_uom_id'] = $uom_id;
				$si_ins['sku_item_code'] = $sku_item_code;
				$si_ins['sku_id'] = $sku_id;
				$si_ins['mcode'] = $mcode;
				$si_ins['link_code'] = $link_code;
				//$si_ins['artno'] = $ins['artno'];
				$si_ins['description'] =  $sku_description;
				$si_ins['receipt_description'] =  $sku_description;				
				$si_ins['hq_cost'] = $si_ins['cost_price'] = 0;	// No Cost
				$si_ins['selling_price'] = $selling_price;
				$si_ins['active'] = 1;
				$si_ins['added'] = 'CURRENT_TIMESTAMP';
				//$si_ins['color'] = $ins['color'];
				//$si_ins['size'] = $ins['size'];
				
				$con->sql_query("insert into sku_items ".mysql_insert_by_field($si_ins));
				$sid = $con->sql_nextid();
			}
			
			// SKU Photo
			if($image_filename && !$this->skip_photo){
				$target_filemtime = 0;
				
				$sku_pos_photo_path = $this->get_sku_pos_photo_path($sid);
				$local_sku_image_filename = $this->attch_file_folder_sku_image."/".$image_filename.".JPG";
				$file_downloaded = false;
				
				foreach($this->sku_image_ext_list as $ext){
					$remote_sku_image_filename = $this->Komaiso->server_ftp_info['sku_image_path']."/".$image_filename.".".$ext;
					
					/*if(!$this->skip_ftp){
						// Check File last modification time at Remote FTP Server
						$target_filemtime = ftp_mdtm($this->ftp_conn_id, $remote_sku_image_filename);
					}else{
						// Check File last modification time at Local Server Attch Folder
						$target_filemtime = filemtime($local_sku_image_filename);
					}*/
					
					
					// Get SKU POS Photo Path
					/*
					$actual_filemtime = 0;
					if(file_exists($sku_pos_photo_path)){
						$actual_filemtime = filemtime($sku_pos_photo_path);
					}*/
					
					//if($target_filemtime){
						// Need to sync photo
						//if($target_filemtime != $actual_filemtime){
							//print " - $target_filemtime != $actual_filemtime";
							if(!$this->skip_ftp){
								// try to download $server_file and save to $local_file
								print " - Downloading $remote_sku_image_filename - ";
								
								// open some file to write to
								//$handle = fopen($local_sku_image_filename, 'w');
								//if (ftp_fget($this->ftp_conn_id, $handle, $remote_sku_image_filename, FTP_ASCII)) {
								if (ftp_get($this->ftp_conn_id, $local_sku_image_filename, $remote_sku_image_filename, FTP_BINARY)) {
								//if (ftp_get($this->ftp_conn_id, $local_sku_image_filename, $remote_sku_image_filename, FTP_ASCII)) {
									// Download Success
									print "Successfully written to $local_sku_image_filename\n";
									$file_downloaded = true;
									break;
								}/* else {
									// Download Failed
									print "Failed\n";
									$error_list[] = "Line ($total_record): Failed to download the file '$remote_sku_image_filename'.";
									$error_record++;
									exit;
								}*/
								//fclose($handle);
							}
						//}
					//}
					//print "\n";
					
				}
				
				if($file_downloaded){
					if(file_exists($local_sku_image_filename)){
						// Need to copy image
						$sku_pos_photo_folder = dirname($sku_pos_photo_path);
						if (!is_dir($sku_pos_photo_folder)){
							if(!mkdir($sku_pos_photo_folder, 0777, true)){
								// Download Failed
								$error_list[] = "Line ($total_record): Failed to create sku image folder '$sku_pos_photo_folder'.";
								$error_record++;
							}
						}
						
						if(is_dir($sku_pos_photo_folder)){
							if(!file_exists($sku_pos_photo_path) || md5_file($local_sku_image_filename) != md5_file($sku_pos_photo_path)){
								copy($local_sku_image_filename, $sku_pos_photo_path);
								chmod($sku_pos_photo_folder, 0777);
								chmod($sku_pos_photo_path, 0777);
								if(!$curr_si['got_pos_photo']){
									$con->sql_query("update sku_items set got_pos_photo=1 where id=$sid");
								}
								print " - Copied to sku folder\n";
							}else{
								print " - no change\n";
							}
						}
					}
				}else{
					// Download Failed
					print "Failed\n";
					$error_list[] = "Line ($total_record): Failed to download the sku imagefile '$image_filename'.";
					$error_record++;
					//exit;
				}
			}
			
			// Check Promotion Price
			$promo_title = "Auto Generated for SKU: $mcode, Branch: $bcode";
			$con->sql_query($sql = "select * from promotion where branch_id=1 and title=".ms($promo_title)." limit 1");
			$promo = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$promo_updated = $promo_items_updated = false;
			$need_add_promo = $need_add_promo_items = false;
			if(!$promo){
				if($promo_price >= $selling_price || $promo_price <= 0){
					// No Promotion Price or Price Same, do nothing
				}elseif($promo_price < $selling_price && $promo_price > 0){
					// No Promotion and Price Different, need to create new promotion
					//print "$mcode need add promotion and promotion_items\n";
					$need_add_promo = true;
					$need_add_promo_items = true;
				}
			}else{
				$promo['promo_branch_id'] = unserialize($promo['promo_branch_id']);
				$promo_id = mi($promo['id']);
				if($promo_price >= $selling_price || $promo_price <= 0){
					// Got Promotion and Now Price become same, need to cancel promo
					$upd = array();
					$upd['status'] = 5;
					$upd['active'] = 0;
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$upd['cancelled'] = 'CURRENT_TIMESTAMP';
					$upd['cancel_by'] = -1;
					
					$con->sql_query("update promotion set ".mysql_update_by_field($upd)." where branch_id=1 and id=$promo_id");
					$promo_updated = true;
				}elseif($promo_price < $selling_price && $promo_price > 0){
					// Got Promotion and Price Different, need to check item price see if update is needed
					// Get Promotion Items
					$con->sql_query("select * from promotion_items where branch_id=1 and promo_id=$promo_id and sku_item_id=$sid order by id limit 1");
					$promo_items = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($promo_items){
						// Price Changed
						if(round($promo_items['non_member_disc_a'], 2) != $promo_price){
							$upd = array();
							$upd['non_member_disc_a'] = $promo_price;
							$con->sql_query("update promotion_items set ".mysql_update_by_field($upd)." where branch_id=1 and promo_id=$promo_id and sku_item_id=$sid and id=".mi($promo_items['id']));
							$promo_items_updated = true;
						}
					}else{
						$need_add_promo_items = true;
					}
					
					// Check if all branches are in the promotion
					$upd_promo = array();
					//foreach($this->b_list as $bid => $b){
						// Need to add branch
						if($promo['promo_branch_id'][$bid] != $bcode){
							if(!isset($upd_promo['promo_branch_id']))	$upd_promo['promo_branch_id'] = $promo['promo_branch_id'];
							
							$upd_promo['promo_branch_id'][$bid] = $bcode;
						}
					//}
					// Promotion Branch Need Update ( Maybe got new branch or current branch code changed)
					if($upd_promo['promo_branch_id']){
						$upd_promo['promo_branch_id'] = serialize($upd_promo['promo_branch_id']);
					}
					
					// Promotion Start Date Changed
					if($promo['date_from'] != $promo_start_date){
						$upd_promo['date_from'] = $promo_start_date;
					}
					// Promotion End Date Changed
					if($promo['date_to'] != $promo_end_date){
						$upd_promo['date_to'] = $promo_end_date;
					}
					
					// Need to update promotion to approved
					if($promo['active'] != 1 || $promo['status'] != 1 || $promo['approved'] != 1){
						$upd_promo['approved'] = 1;
						$upd_promo['status'] = 1;
						$upd_promo['active'] = 1;
						$upd_promo['cancelled'] = '0';
						$upd_promo['cancel_by'] = 0;
					}
					
					// Promotion Need Update
					if($upd_promo){
						$upd_promo['last_update'] = 'CURRENT_TIMESTAMP';
						$con->sql_query("update promotion set ".mysql_update_by_field($upd_promo)." where branch_id=1 and id=$promo_id");
						$promo_updated = true;
					}
				}
			}
			
			if(!$promo && !$promo_id && $need_add_promo){
				//print "\n$mcode add promotion. $promo_title\n";
				// Need to Create New Promotion
				$ins = array();
				$ins['branch_id'] = 1;
				$ins['id'] = $appCore->generateNewID("promotion", "branch_id=1");
				$ins['user_id'] = -1;
				$ins['title'] = $promo_title;
				$ins['date_from'] = $promo_start_date;
				$ins['date_to'] = $promo_end_date;
				$ins['time_from'] = '00:00:00';
				$ins['time_to'] = '23:59:00';
				$ins['approved'] = 1;
				$ins['status'] = 1;
				$ins['active'] = 1;
				$ins['added'] = $ins['last_update'] = 'CURRENT_TIMESTAMP';
				$ins['promo_type'] = 'discount';
				$ins['promo_branch_id'] = array();
				foreach($this->b_list as $bid => $b){
					$ins['promo_branch_id'][$bid] = $b['code'];
				}
				$ins['promo_branch_id'] = serialize($ins['promo_branch_id']);
				$con->sql_query("insert into promotion ".mysql_insert_by_field($ins));
				$promo_id = $ins['id'];
				$promo_updated = true;
			}
			
			if($promo_id && $need_add_promo_items){
				//print "\n$mcode add promotion_items. $promo_title\n";
				// Need to Add New Promotion Items
				$ins = array();
				$ins['branch_id'] = 1;
				$ins['id'] = $appCore->generateNewID("promotion_items", "branch_id=1");
				$ins['promo_id'] = $promo_id;
				$ins['user_id'] = -1;
				$ins['sku_item_id'] = $sid;
				$ins['non_member_disc_a'] = $promo_price;
				$con->sql_query("insert into promotion_items ".mysql_insert_by_field($ins));
				$promo_items_updated = true;
			}
			
			if($promo_items_updated && !$promo_updated){
				$con->sql_query("update promotion set last_update=CURRENT_TIMESTAMP where branch_id=1 and id=$promo_id");
				$promo_updated = true;
			}
		}
		
		// Construct Summaru for Sync Status
		$params = array();
		//$params['total_record'] = $total_record;
		//$params['new_record'] = $new_record;
		//$params['update_record'] = $update_record;
		//$params['error_record'] = $error_record;
		$params['error_list'] = $error_list;
		
		// Mark Sync Status = End
		$this->set_cron_status('master', 'sku_'.$bcode , 'end', $params);
		
		// Commit MySQL Changes
		$con->sql_commit();
		
		print "\n";
		print "Total Record: $total_record\n";
		//print "New Record: $new_record\n";
		//print "Update Record: $update_record\n";
		print "Error Found: ".count($error_list)."\n";
		print "Done.\n";
	}
	
	// private function sync_sku(){
		// global $con, $appCore;
		
		// print ">>>>> Sync SKU ...\n";
		// // Mark Sync Status = Start
		// $this->set_cron_status('master', 'sku' , 'start');
		
		// // Create Table to store Komaiso records
		// $con->sql_query_skip_logbin("drop table if exists tmp_komaiso_sku");
		// $con->sql_query_skip_logbin("create table if not exists tmp_komaiso_sku(
			// line int not null auto_increment primary key,
			// mcode char(15) unique,
			// link_code char(20) not null,
			// brand_desc char(100) not null,
			// sku_description char(100) not null,
			// selling_price double not null default 0,
			// promo_price double not null default 0,
			// promo_start_date date,
			// promo_end_date date,
			// scale_type tinyint(1) not null default 0,
			// uom_code char(6) not null,
			// image_filename char(100) not null,
			// dept_name char(100) not null,
			// cat1_name char(100) not null,
			// cat2_name char(100) not null,
			// cat3_name char(100) not null,
			// sku_type char(10) not null default 'OUTRIGHT'
		// ) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
		// //$con->sql_query_skip_logbin("truncate tmp_komaiso_sku");
		
		// $local_filename = $this->attch_file_folder_master.'/sku.csv';
		
		// if(!$this->skip_ftp){
			// ////////// todo: get file from ftp server ///////////////
			// // Change Directory to Masterfile Path
			// if($this->Komaiso->server_ftp_info['master_path']){
				// ftp_chdir($this->ftp_conn_id, $this->Komaiso->server_ftp_info['master_path']);
			// }
			// $server_filename = $this->Komaiso->server_ftp_info['sku_filename'] ? $this->Komaiso->server_ftp_info['sku_filename'] : 'sku.csv';
			
			
			// // try to download $server_file and save to $local_file
			// if (ftp_get($this->ftp_conn_id, $local_filename, $server_filename, FTP_ASCII)) {
				// // Download Success
				// print "Successfully written to $local_filename\n";
			// } else {
				// // Download Failed
				// $err_msg = "There was a problem to download the file '$server_filename'";
				// print $err_msg."\n";
				// $params['error_list'] = array($err_msg);
				
				// // Mark Sync Status = End (Got Error)
				// $this->set_cron_status('master', 'sku' , 'end', $params);
				// return;
			// }
		// }
		
		// // Start MySQL Transaction
		// $con->sql_begin_transaction();
		
		// // Open File
		// $f = fopen($local_filename, "rt");
		// $header = fgetcsv($f);	// remove header
		
		// $total_record = 0;
		// $new_record = 0;
		// $update_record = 0;
		// $error_record = 0;
		// $error_list = array();
		// while($r = fgetcsv($f)){
			// $total_record++;
			
			// //print "$total_record) ".$line;
			// print "\rChecking line $total_record";
			
			// //print_r($r);
			
			// $link_code = trim($r[0]);
			
			// // Skip Empty row
			// if(!$link_code)	continue;
			
			// $mcode = trim($r[1]);
			// $brand_desc = trim($r[3]);
			// $sku_description = trim($r[4]);
			// $selling_price = round($r[7], 2);
			// $promo_price = round($r[8], 2);
			// $promo_start_date = date("Y-m-d", strtotime(trim($r[9])));
			// $promo_end_date = date("Y-m-d", strtotime(trim($r[10])));
			// $scale_type = strtoupper(trim($r[11])) == 'TRUE' ? 2 : 0;	// 2 = Weighted, 0 = No
			// $uom_code = trim($r[14]);
			// if(!$uom_code)	$uom_code = 'EACH';
			// $image_filename = trim($r[15]);
			// $dept_name = trim($r[19]);
			// $cat1_name = trim($r[20]);
			// if(!$cat1_name)	$cat1_name = 'OTHERS';	// Minimum need to have Lv3 Category
			// $cat2_name = trim($r[21]);
			// $cat3_name = trim($r[22]);
			// $sku_type = strtoupper(trim($r[23])) == 'TRUE' ? 'CONSIGN' : 'OUTRIGHT';
			
			// // SKU Desciption cannot empty
			// if(!$sku_description){
				// $error_list[] = "Line ($total_record): SKU Desciption is Empty.";
				// $error_record++;
				// continue;
			// }
			
			// // Check Duplicate
			// $con->sql_query("select line from tmp_komaiso_sku where mcode=".ms($mcode));
			// $tmp = $con->sql_fetchassoc();
			// $con->sql_freeresult();
			
			// if($tmp){
				// $error_list[] = "Line ($total_record): SKU '$link_code' MCode is duplicated with line ".$tmp['line']." (SKU ".$tmp['link_code'].").";
				// $error_record++;
				// continue;
			// }
			
			// // Check Promotion Price
			// if($promo_price > $selling_price){
				// // Promotion Price is Higher than Normal Selling Price
				// $error_list[] = "Line ($total_record): SKU Promotion Price '$promo_price' is higher than Normal Selling Price '$selling_price'.";
				// $error_record++;
				// continue;
			// }
			// if($promo_price < $selling_price){	// Got Promotion
				// // Check Start Date Format - minimum year 2000
				// if(date("Y", strtotime($promo_start_date)) < 2000){
					// $error_list[] = "Line ($total_record): Promotion Start Date '$promo_start_date' is invalid.";
					// $error_record++;
					// continue;
				// }
				// // Check End Date Format - minimum year 2000
				// if(date("Y", strtotime($promo_end_date)) < 2000){
					// $error_list[] = "Line ($total_record): Promotion End Date '$promo_end_date' is invalid.";
					// $error_record++;
					// continue;
				// }
				// // Check to make sure end date is after start date
				// if(strtotime($promo_start_date) > strtotime($promo_end_date)){
					// $error_list[] = "Line ($total_record): Promotion End Date '$promo_end_date' earlier than Start Date '$promo_start_date'.";
					// $error_record++;
					// continue;
				// }
			// }
			
			// // Store a record in arms table
			// $ins = array();
			// $ins['mcode'] = $mcode;
			// $ins['link_code'] = $link_code;
			// $ins['brand_desc'] = $brand_desc;
			// $ins['sku_description'] = $sku_description;
			// $ins['selling_price'] = $selling_price;
			// $ins['promo_price'] = $promo_price;
			// $ins['promo_start_date'] = $promo_start_date;
			// $ins['promo_end_date'] = $promo_end_date;
			// $ins['scale_type'] = $scale_type;
			// $ins['uom_code'] = $uom_code;
			// $ins['image_filename'] = $image_filename;
			// $ins['dept_name'] = $dept_name;
			// $ins['cat1_name'] = $cat1_name;
			// $ins['cat2_name'] = $cat2_name;
			// $ins['cat3_name'] = $cat3_name;
			// $ins['sku_type'] = $sku_type;
			// $con->sql_query_skip_logbin("insert into tmp_komaiso_sku ".mysql_insert_by_field($ins));
			
			// // Check and Auto Create New Brand
			// $brand_id = $this->check_and_create_brand_by_desc($brand_desc);
			
			// // Check and Auto Create New UOM 
			// $uom_id = $this->check_and_create_uom_by_code($uom_code);
			
			// // Check and Auto Create Category Line (Level 1)
			// $cat_line_id = $this->check_and_create_category_line();
			
			// // Check and Create Department
			// $cat_dept_id = $this->check_and_create_category_by_desc($cat_line_id, 2, $dept_name);
			// if($cat_dept_id <= 0){
				// $error_list[] = "Line ($total_record): Error on Getting Department ID for Department '$dept_name'.";
				// $error_record++;
				// continue;
			// }
			
			// // Check and Create Category 1
			// $cat_id_1 = $this->check_and_create_category_by_desc($cat_dept_id, 3, $cat1_name);
			// if($cat_id_1 <= 0){
				// $error_list[] = "Line ($total_record): Error on Getting Category ID for Category Lv3 '$cat1_name'.";
				// $error_record++;
				// continue;
			// }
			
			// $cat_id = $cat_id_1;
			
			// if($cat2_name){
				// // Check and Create Category 2
				// $cat_id_2 = $this->check_and_create_category_by_desc($cat_id_1, 4, $cat2_name);
				// if($cat_id_2 <= 0){
					// $error_list[] = "Line ($total_record): Error on Getting Category ID for Category Lv4 '$cat2_name'.";
					// $error_record++;
					// continue;
				// }
				// $cat_id = $cat_id_2;
				
				// if($cat3_name){
					// // Check and Create Category 3
					// $cat_id_3 = $this->check_and_create_category_by_desc($cat_id_2, 5, $cat3_name);
					// if($cat_id_3 <= 0){
						// $error_list[] = "Line ($total_record): Error on Getting Category ID for Category Lv5 '$cat3_name'.";
						// $error_record++;
						// continue;
					// }
					// $cat_id = $cat_id_3;
				// }
			// }
			
			// // Get current sku_items by mcode
			// $con->sql_query("select * from sku_items where mcode=".ms($mcode));
			// $curr_si = $con->sql_fetchassoc();
			// $con->sql_freeresult();
			
			// $sid = 0;
			// $sku_id = 0;
			// $promo = array();
			// $promo_id = 0;
			// if($curr_si){
				// // Ezisting SKU
				// $sid = mi($curr_si['id']);
				// $sku_id = mi($curr_si['sku_id']);
				
				// // Check sku Different
				// $con->sql_query("select * from sku where id=$sku_id");
				// $curr_sku = $con->sql_fetchassoc();
				// $con->sql_freeresult();
				
				// $sku_updated = $si_updated = false;
				// if($curr_sku['category_id'] != $cat_id || $curr_sku['brand_id'] != $brand_id || $curr_sku['sku_type'] != $sku_type || $curr_sku['scale_type'] != $scale_type){
					// $upd = array();
					// $upd['category_id'] = $cat_id;
					// $upd['brand_id'] = $brand_id;
					// $upd['sku_type'] = $sku_type;
					// $upd['scale_type'] = $scale_type;
					// $con->sql_query("update sku set ".mysql_update_by_field($upd)." where id=$sku_id");
					// $sku_updated = true;
				// }
				
				// // Check sku_items Different
				// if($curr_si['link_code'] != $link_code || $curr_si['description'] != $sku_description || $curr_si['selling_price'] != $selling_price || $curr_si['packing_uom_id'] != $uom_id){
					// $upd = array();
					// $upd['link_code'] = $link_code;
					// $upd['description'] = $sku_description;
					// $upd['selling_price'] = $selling_price;
					// $upd['packing_uom_id'] = $uom_id;
					// $con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id=$sid");
					// $si_updated = true;
				// }
				
				// if($sku_updated || $si_updated){
					// $con->sql_query("update sku_items_cost set changed=1 where sku_item_id=$sid");
					
					// if($sku_updated && !$si_updated){
						// // need to touch on sku_items in order for it to sync to pos counter
						// $con->sql_query("update sku_items set lastupdate=CURRENT_TIMESTAMP where id=$sid");
					// }
				// }
			// }else{
				// // New SKU
				// // insert sku
				// $sku_ins = array();
				// $sku_ins['category_id'] = $cat_id;
				// $sku_ins['uom_id'] = $uom_id;
				// $sku_ins['vendor_id'] = 0;//$vendor_id;
				// $sku_ins['brand_id'] = $brand_id;
				// $sku_ins['status'] = 1;
				// $sku_ins['active'] = 1;
				// $sku_ins['sku_type'] = $sku_type;
				// $sku_ins['apply_branch_id'] = 1;
				// $sku_ins['added'] = 'CURRENT_TIMESTAMP';
				// $sku_ins['scale_type'] = $scale_type;
						
				// $con->sql_query("insert into sku ".mysql_insert_by_field($sku_ins));
				// $sku_id = intval($con->sql_nextid());
				// $sku_code = sprintf(ARMS_SKU_CODE_PREFIX, $sku_id);
				// $con->sql_query("update sku set sku_code=".ms($sku_code)." where id=$sku_id");
				
				// // insert sku_items
				// $sku_item_code = sprintf(ARMS_SKU_CODE_PREFIX, $sku_id)."0000";
				// $si_ins = array();
				// $si_ins['is_parent'] = 1;
				// $si_ins['packing_uom_id'] = $uom_id;
				// $si_ins['sku_item_code'] = $sku_item_code;
				// $si_ins['sku_id'] = $sku_id;
				// $si_ins['mcode'] = $mcode;
				// $si_ins['link_code'] = $link_code;
				// //$si_ins['artno'] = $ins['artno'];
				// $si_ins['description'] =  $sku_description;
				// $si_ins['receipt_description'] =  $sku_description;				
				// $si_ins['hq_cost'] = $si_ins['cost_price'] = 0;	// No Cost
				// $si_ins['selling_price'] = $selling_price;
				// $si_ins['active'] = 1;
				// $si_ins['added'] = 'CURRENT_TIMESTAMP';
				// //$si_ins['color'] = $ins['color'];
				// //$si_ins['size'] = $ins['size'];
				
				// $con->sql_query("insert into sku_items ".mysql_insert_by_field($si_ins));
				// $sid = $con->sql_nextid();
			// }
			
			// // SKU Photo
			// if($image_filename){
				// $target_filemtime = 0;
				
				// $sku_pos_photo_path = $this->get_sku_pos_photo_path($sid);
				// $local_sku_image_filename = $this->attch_file_folder_sku_image."/".$image_filename.".JPG";
				// $file_downloaded = false;
				
				// foreach($this->sku_image_ext_list as $ext){
					// $remote_sku_image_filename = $this->Komaiso->server_ftp_info['sku_image_path']."/".$image_filename.".".$ext;
					
					// /*if(!$this->skip_ftp){
						// // Check File last modification time at Remote FTP Server
						// $target_filemtime = ftp_mdtm($this->ftp_conn_id, $remote_sku_image_filename);
					// }else{
						// // Check File last modification time at Local Server Attch Folder
						// $target_filemtime = filemtime($local_sku_image_filename);
					// }*/
					
					
					// // Get SKU POS Photo Path
					// /*
					// $actual_filemtime = 0;
					// if(file_exists($sku_pos_photo_path)){
						// $actual_filemtime = filemtime($sku_pos_photo_path);
					// }*/
					
					// //if($target_filemtime){
						// // Need to sync photo
						// //if($target_filemtime != $actual_filemtime){
							// //print " - $target_filemtime != $actual_filemtime";
							// if(!$this->skip_ftp){
								// // try to download $server_file and save to $local_file
								// print " - Downloading $remote_sku_image_filename - ";
								
								// // open some file to write to
								// //$handle = fopen($local_sku_image_filename, 'w');
								// //if (ftp_fget($this->ftp_conn_id, $handle, $remote_sku_image_filename, FTP_ASCII)) {
								// if (ftp_get($this->ftp_conn_id, $local_sku_image_filename, $remote_sku_image_filename, FTP_BINARY)) {
								// //if (ftp_get($this->ftp_conn_id, $local_sku_image_filename, $remote_sku_image_filename, FTP_ASCII)) {
									// // Download Success
									// print "Successfully written to $local_sku_image_filename\n";
									// $file_downloaded = true;
									// break;
								// }/* else {
									// // Download Failed
									// print "Failed\n";
									// $error_list[] = "Line ($total_record): Failed to download the file '$remote_sku_image_filename'.";
									// $error_record++;
									// exit;
								// }*/
								// //fclose($handle);
							// }
						// //}
					// //}
					// //print "\n";
					
				// }
				
				// if($file_downloaded){
					// if(file_exists($local_sku_image_filename)){
						// // Need to copy image
						// $sku_pos_photo_folder = dirname($sku_pos_photo_path);
						// if (!is_dir($sku_pos_photo_folder)){
							// if(!mkdir($sku_pos_photo_folder, 0777, true)){
								// // Download Failed
								// $error_list[] = "Line ($total_record): Failed to create sku image folder '$sku_pos_photo_folder'.";
								// $error_record++;
							// }
						// }
						
						// if(is_dir($sku_pos_photo_folder)){
							// if(!file_exists($sku_pos_photo_path) || md5_file($local_sku_image_filename) != md5_file($sku_pos_photo_path)){
								// copy($local_sku_image_filename, $sku_pos_photo_path);
								// chmod($sku_pos_photo_folder, 0777);
								// chmod($sku_pos_photo_path, 0777);
								// if(!$curr_si['got_pos_photo']){
									// $con->sql_query("update sku_items set got_pos_photo=1 where id=$sid");
								// }
								// print " - Copied to sku folder\n";
							// }else{
								// print " - no change\n";
							// }
						// }
					// }
				// }else{
					// // Download Failed
					// print "Failed\n";
					// $error_list[] = "Line ($total_record): Failed to download the sku imagefile '$image_filename'.";
					// $error_record++;
					// //exit;
				// }
			// }
			
			// // Check Promotion Price
			// $promo_title = "Auto Generated for SKU: $mcode";
			// $con->sql_query($sql = "select * from promotion where branch_id=1 and title=".ms($promo_title)." limit 1");
			// $promo = $con->sql_fetchassoc();
			// $con->sql_freeresult();
			
			// $promo_updated = $promo_items_updated = false;
			// $need_add_promo = $need_add_promo_items = false;
			// if(!$promo){
				// if($promo_price >= $selling_price || $promo_price <= 0){
					// // No Promotion Price or Price Same, do nothing
				// }elseif($promo_price < $selling_price && $promo_price > 0){
					// // No Promotion and Price Different, need to create new promotion
					// //print "$mcode need add promotion and promotion_items\n";
					// $need_add_promo = true;
					// $need_add_promo_items = true;
				// }
			// }else{
				// $promo['promo_branch_id'] = unserialize($promo['promo_branch_id']);
				// $promo_id = mi($promo['id']);
				// if($promo_price >= $selling_price || $promo_price <= 0){
					// // Got Promotion and Now Price become same, need to cancel promo
					// $upd = array();
					// $upd['status'] = 5;
					// $upd['active'] = 0;
					// $upd['last_update'] = 'CURRENT_TIMESTAMP';
					// $upd['cancelled'] = 'CURRENT_TIMESTAMP';
					// $upd['cancel_by'] = -1;
					
					// $con->sql_query("update promotion set ".mysql_update_by_field($upd)." where branch_id=1 and id=$promo_id");
					// $promo_updated = true;
				// }elseif($promo_price < $selling_price && $promo_price > 0){
					// // Got Promotion and Price Different, need to check item price see if update is needed
					// // Get Promotion Items
					// $con->sql_query("select * from promotion_items where branch_id=1 and promo_id=$promo_id and sku_item_id=$sid order by id limit 1");
					// $promo_items = $con->sql_fetchassoc();
					// $con->sql_freeresult();
					
					// if($promo_items){
						// // Price Changed
						// if(round($promo_items['non_member_disc_a'], 2) != $promo_price){
							// $upd = array();
							// $upd['non_member_disc_a'] = $promo_price;
							// $con->sql_query("update promotion_items set ".mysql_update_by_field($upd)." where branch_id=1 and promo_id=$promo_id and sku_item_id=$sid and id=".mi($promo_items['id']));
							// $promo_items_updated = true;
						// }
					// }else{
						// $need_add_promo_items = true;
					// }
					
					// // Check if all branches are in the promotion
					// $upd_promo = array();
					// foreach($this->b_list as $bid => $b){
						// // Need to add branch
						// if($promo['promo_branch_id'][$bid] != $b['code']){
							// if(!isset($upd_promo['promo_branch_id']))	$upd_promo['promo_branch_id'] = $promo['promo_branch_id'];
							
							// $upd_promo['promo_branch_id'][$bid] = $b['code'];
						// }
					// }
					// // Promotion Branch Need Update ( Maybe got new branch or current branch code changed)
					// if($upd_promo['promo_branch_id']){
						// $upd_promo['promo_branch_id'] = serialize($upd_promo['promo_branch_id']);
						
					// }
					
					// // Promotion Start Date Changed
					// if($promo['date_from'] != $promo_start_date){
						// $upd_promo['date_from'] = $promo_start_date;
					// }
					// // Promotion End Date Changed
					// if($promo['date_to'] != $promo_end_date){
						// $upd_promo['date_to'] = $promo_end_date;
					// }
					
					// // Need to update promotion to approved
					// if($promo['active'] != 1 || $promo['status'] != 1 || $promo['approved'] != 1){
						// $upd_promo['approved'] = 1;
						// $upd_promo['status'] = 1;
						// $upd_promo['active'] = 1;
						// $upd_promo['cancelled'] = '0';
						// $upd_promo['cancel_by'] = 0;
					// }
					
					// // Promotion Need Update
					// if($upd_promo){
						// $upd_promo['last_update'] = 'CURRENT_TIMESTAMP';
						// $con->sql_query("update promotion set ".mysql_update_by_field($upd_promo)." where branch_id=1 and id=$promo_id");
						// $promo_updated = true;
					// }
				// }
			// }
			
			// if(!$promo && !$promo_id && $need_add_promo){
				// //print "\n$mcode add promotion. $promo_title\n";
				// // Need to Create New Promotion
				// $ins = array();
				// $ins['branch_id'] = 1;
				// $ins['id'] = $appCore->generateNewID("promotion", "branch_id=1");
				// $ins['user_id'] = -1;
				// $ins['title'] = $promo_title;
				// $ins['date_from'] = $promo_start_date;
				// $ins['date_to'] = $promo_end_date;
				// $ins['time_from'] = '00:00:00';
				// $ins['time_to'] = '23:59:00';
				// $ins['approved'] = 1;
				// $ins['status'] = 1;
				// $ins['active'] = 1;
				// $ins['added'] = $ins['last_update'] = 'CURRENT_TIMESTAMP';
				// $ins['promo_type'] = 'discount';
				// $ins['promo_branch_id'] = array();
				// foreach($this->b_list as $bid => $b){
					// $ins['promo_branch_id'][$bid] = $b['code'];
				// }
				// $ins['promo_branch_id'] = serialize($ins['promo_branch_id']);
				// $con->sql_query("insert into promotion ".mysql_insert_by_field($ins));
				// $promo_id = $ins['id'];
				// $promo_updated = true;
			// }
			
			// if($promo_id && $need_add_promo_items){
				// //print "\n$mcode add promotion_items. $promo_title\n";
				// // Need to Add New Promotion Items
				// $ins = array();
				// $ins['branch_id'] = 1;
				// $ins['id'] = $appCore->generateNewID("promotion_items", "branch_id=1");
				// $ins['promo_id'] = $promo_id;
				// $ins['user_id'] = -1;
				// $ins['sku_item_id'] = $sid;
				// $ins['non_member_disc_a'] = $promo_price;
				// $con->sql_query("insert into promotion_items ".mysql_insert_by_field($ins));
				// $promo_items_updated = true;
			// }
			
			// if($promo_items_updated && !$promo_updated){
				// $con->sql_query("update promotion set last_update=CURRENT_TIMESTAMP where branch_id=1 and id=$promo_id");
				// $promo_updated = true;
			// }
		// }
		
		// // Construct Summaru for Sync Status
		// $params = array();
		// //$params['total_record'] = $total_record;
		// //$params['new_record'] = $new_record;
		// //$params['update_record'] = $update_record;
		// //$params['error_record'] = $error_record;
		// $params['error_list'] = $error_list;
		
		// // Mark Sync Status = End
		// $this->set_cron_status('master', 'sku' , 'end', $params);
		
		// // Commit MySQL Changes
		// $con->sql_commit();
		
		// print "\n";
		// print "Total Record: $total_record\n";
		// //print "New Record: $new_record\n";
		// //print "Update Record: $update_record\n";
		// print "Error Found: ".count($error_list)."\n";
		// print "Done.\n";
	// }
	
	private function check_and_create_brand_by_desc($brand_desc, $auto_add = true){
		global $con;

		// No Description
		if(!$brand_desc) return;

		$con->sql_query("select id from brand where description=".ms($brand_desc));
		$brand_info = $con->sql_fetchassoc();
		$con->sql_freeresult();

		if(!$brand_info){
			// Try again to get by brand code
			$brand_code = substr($brand_desc,0,6);
			$con->sql_query("select id from brand where code=".ms($brand_code));
			$brand_info = $con->sql_fetchrow();
			$con->sql_freeresult();
		}
		
		if(!$brand_info){
			if($auto_add){
				// Still not found, need to create new brand
				$ins = array();		
				$ins['code'] = $brand_desc;
				$ins['description'] = $brand_desc;
				$ins['active'] = 1;
				$con->sql_query("insert into brand ".mysql_insert_by_field($ins));
				$brand_id = $con->sql_nextid();
			}
		}else{
			$brand_id = mi($brand_info['id']);
		}
		
		return $brand_id;
	}
	
	private function check_and_create_uom_by_code($uom_code, $auto_add = true){
		global $con;

		// No UOM Code
		if(!$uom_code) return;
		$uom_code = substr($uom_code,0,6);
		
		// Get UOM
		$con->sql_query("select id from uom where code=".ms($uom_code));
		$uom_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$uom_info){
			if($auto_add){
				// Still not found, need to create new brand
				$ins = array();		
				$ins['code'] = $uom_code;
				$ins['description'] = $uom_code;
				$ins['fraction'] = 1;
				$ins['active'] = 1;
				$con->sql_query("insert into uom ".mysql_insert_by_field($ins));
				$uom_id = $con->sql_nextid();
			}
		}else{
			$uom_id = mi($uom_info['id']);
		}
		
		return $uom_id;
	}
	
	private function check_and_create_category_line(){
		global $con;
		
		if($this->category_line_id>0)	return $this->category_line_id;
		
		// Get Line
		$con->sql_query("select id from category where description = 'LINE' and level = 1 limit 1");
		$line_id = mi($con->sql_fetchfield(0));
		$con->sql_freeresult();
		
		if(!$line_id){
			// Need to Create new Line
			$ins = array();
			$ins['level'] = 1;
			$ins['description'] = 'LINE';
			$ins['active'] = 1;
			$ins['tree_str'] = '(0)';
			$ins['no_inventory'] = 'no';
			$ins['is_fresh_market'] = 'no';
			$con->sql_query("insert into category ".mysql_insert_by_field($ins));
			$line_id = $con->sql_nextid();
			$con->sql_query("update category set code = ".mi($line_id).", department_id = ".mi($line_id)." where id = ".mi($line_id)." and level = 1");
		}
		
		$this->category_line_id = $line_id;
		
		return $this->category_line_id;
	}
	
	private function check_and_create_category_by_desc($root_id, $level, $cat_desc){
		global $con;
		
		//print "\n>> check_and_create_category_by_desc($root_id, $level, $cat_desc)\n";
		// No Desciption
		$cat_desc = trim($cat_desc);
		if(!$cat_desc)	return;
		
		// No Category Level
		$level = mi($level);
		if($level <= 0)	return;
		
		// No Parent Category ID
		$root_id = mi($root_id);
		if($root_id <= 0)	return;
		
		// Get Category
		$con->sql_query($sql="select id from category where description = ".ms($cat_desc)." and level = $level limit 1");
		$cat_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		//print_r($cat_info);
		
		if(!$cat_info){
			// Get Parent Category
			$con->sql_query($sql = "select id,tree_str,department_id from category where id=$root_id and level=".mi($level-1)." limit 1");
			$parent_cat = $con->sql_fetchassoc();
			$con->sql_freeresult();
			//print "$sql\n";
			
			// Cant found the Parent Category
			if(!$parent_cat)	return;
			
			// Create New Category
			$ins = array();
			$ins['root_id'] = $root_id;
			$ins['level'] = $level;
			$ins['description'] = $cat_desc;
			$ins['active'] = 1;
			$ins['tree_str'] = $parent_cat['tree_str'].'('.$root_id.')';
			$con->sql_query("insert into category ".mysql_insert_by_field($ins));
			$cat_id = $con->sql_nextid();
			
			if($level == 2){
				$dept_id = $cat_id;
			}else{
				$dept_id = $parent_cat['department_id'];
			}
			$con->sql_query("update category set code = ".mi($cat_id).", department_id = ".mi($dept_id)." where id = ".mi($cat_id));
		}else{
			$cat_id = $cat_info['id'];
		}
		//print "Cat ID = $cat_id\n";
		return $cat_id;
	}
	
	private function get_sku_pos_photo_path($sid){
		$sid = mi($sid);
		if($sid<=0)	return;
		
		$group_num = ceil($sid/10000);
		$abs_path = dirname(__FILE__)."/sku_photos/promo_photo/".$group_num."/".$sid."/1.jpg";
		return $abs_path;
	}
	
	private function get_sales_folder_path($b, $date){
		$folder_path = $this->attch_file_folder_sales;
		
		// check & create folder by branch
		$folder_path = $folder_path."/".mi($b['id']);
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		$year = date('Y',strtotime($date));
		$month = mi(date('m',strtotime($date)));
		
		// check & create folder by year
		$folder_path = $folder_path."/".$year;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		// check & create folder by month
		$folder_path = $folder_path."/".$month;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		return $folder_path;
	}
	
	private function get_uploaded_sales_folder_path($b, $date){
		$folder_path = $this->get_sales_folder_path($b, $date);
		
		// check & create uploaded folder
		$uploaded_folder_path = $folder_path."/uploaded";
		if(!is_dir($uploaded_folder_path)) check_and_create_dir($uploaded_folder_path);
		
		return $uploaded_folder_path;
	}
	
	private function get_sales_filename($b, $date){
		$bid = mi($b['id']);
		//$settings = $this->configuration[$this->bcode];
		//$filename = sprintf($this->generate_info_filename, $settings['tenant_code'], date('Ymd',strtotime($date)));
		$prefix = 'SE';
		
		$filename = $prefix.date('ymd',strtotime($date))."01".$b['code'].'.DAT';
		print "Filename = $filename\n";
		return $filename;
	}
	
	private function sync_sales(){
		global $con;
		
		print "=============== Sync Sales ===============\n";
				
		print ">>>>> Sync POS ...\n";
		// Mark Sync Status = Start
		$this->set_cron_status('sales', 'pos' , 'start');
		$this->error_list = array();
		
		// Generate Sales File
		foreach($this->b_list as $b){
			$this->generate_sales_file($b);
			print "\n";
		}
		
		$params['error_list'] = $this->error_list;
		// Mark Sync Status = End
		$this->set_cron_status('sales', 'pos' , 'end', $params);
		
		print "Done.\n";
	}
	
	private function generate_sales_file($b){
		print "Generate Sales Files for ".$b['code']."...\n";
				
		// loop date
		for($d1 = strtotime($this->date_from), $d2 = strtotime($this->date_to); $d1 <= $d2; $d1+=86400){
			$this->generate_sales_file_by_date($b, date("Y-m-d", $d1));
		}
	}
	
	private function generate_sales_file_by_date($b, $date){
		global $con;
		
		$bid = mi($b['id']);
		print "Generating sales file, Branch ID:$bid, Date: $date\n";
		
		//$settings = $this->configuration[$this->bcode];
		$folder_path = $this->get_sales_folder_path($b, $date);
		$uploaded_folder_path = $this->get_uploaded_sales_folder_path($b, $date);
		$filename = $this->get_sales_filename($b, $date);
		
		
		if(!$this->regen_sales){	// no regenerate, check whether file exists
			if(file_exists($uploaded_folder_path."/".$filename) && filesize($uploaded_folder_path."/".$filename) > 0){
				print " - File Uploaded\n";
				return;
			}
		}
		
		// Day End Checking
		/*$ret = $this->check_branch_can_send_sales($bid, $date);
		if($ret['error']){
			print "There was a problem while checking day end.\n";
			print_r($ret['error']);
			foreach($ret['error'] as $e){
				$this->error_list[] = $e;
			}
			return;
		}*/
		
		
		// Check Finalise
		$con->sql_query("select * from pos_finalized where branch_id=$bid and date=".ms($date)." and finalized=1");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$tmp){
			print " - Not Finalised Yet.\n";
			return;
		}
		
		// delete the file
		if(file_exists($folder_path."/".$filename)) unlink($folder_path."/".$filename);
		
		// Create file
		$fp = fopen($folder_path."/".$filename, 'w');
		
		// START Indicator
		//fwrite($fp, "START\n");
		//fputcsv_eol($fp, array("H", $b['code'], date("Ymd", strtotime($date)), date("Hi", strtotime($date))), '|');
		
		$sql = "select p.*, user.u as cashier_name, cs.network_name
			from pos p 
			left join user on user.id=p.cashier_id
			left join counter_settings cs on cs.branch_id=p.branch_id and cs.id=p.counter_id
			where p.branch_id=$bid and p.date=".ms($date)." and p.cancel_status=0
			order by p.id";
			
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($p = $con->sql_fetchassoc($q1)){	// Loop By Receipt
			$counter_id = mi($p['counter_id']);
			$pos_id = mi($p['id']);
			
			//print $p['receipt_ref_no']."\n";
			
			// Receipt Header
			$data = array();
			$data[] = "H";
			$data[] = $b['code'];
			$data[] = date("Ymd", strtotime($p['pos_time']));
			$data[] = date("Hi", strtotime($p['pos_time']));
			$data[] = $p['receipt_ref_no'];
			$data[] = $p['member_no'];
			fputcsv_eol($fp, $data, '|');
			
			// POS Items
			$q2 = $con->sql_query("select pi.*, si.mcode, si.link_code
				from pos_items pi
				left join sku_items si on si.id=pi.sku_item_id
				where pi.branch_id=$bid and pi.counter_id=$counter_id and pi.date=".ms($date)." and pi.pos_id=$pos_id
				order by pi.item_id");
			while($pi = $con->sql_fetchassoc($q2)){
				$data = array();
				$data[] = "A";
				$data[] = $pi['link_code'] ? $pi['link_code'] : $pi['mcode'];
				$data[] = $pi['qty'];
				$data[] = round(round($pi['price']-$pi['discount'], 2) / $pi['qty'], 2);
				fputcsv_eol($fp, $data, '|');
			}
			$con->sql_freeresult($q2);
			
			// Rounding
			$con->sql_query("select pp.*
				from pos_payment pp
				where pp.branch_id=$bid and pp.counter_id=$counter_id and pp.date=".ms($date)." and pp.pos_id=$pos_id and pp.adjust=0 and pp.type ='Rounding'
				order by pp.id
				limit 1");
			$data_round = $con->sql_fetchassoc();
			$con->sql_freeresult();
			if($data_round){
				$data = array();
				$data[] = "B";
				$komaiso_ptype = $this->Komaiso->get_komaiso_payment_type($data_round['type']);
				$data[] = $komaiso_ptype;
				$data[] = round($data_round['amount'], 2);
				fputcsv_eol($fp, $data, '|');
			}
				
			// POS Payment
			$q3 = $con->sql_query("select pp.*
				from pos_payment pp
				where pp.branch_id=$bid and pp.counter_id=$counter_id and pp.date=".ms($date)." and pp.pos_id=$pos_id and pp.adjust=0 and pp.type not in ('Rounding')
				order by pp.id");
			while($pp = $con->sql_fetchassoc($q3)){
				$ptype = $pp['type'];
				if($pp['type'] == 'Mix & Match Total Disc')	$ptype = 'Discount';
				
				$komaiso_ptype = $this->Komaiso->get_komaiso_payment_type($ptype);
				
				$amt = $pp['amount'];
				if($pp['type'] == 'Cash')	$amt -= $p['amount_change'];
				$amt = round($amt, 2);
				
				$data = array();
				$data[] = "B";
				$data[] = $komaiso_ptype;
				$data[] = round($amt, 2);
				fputcsv_eol($fp, $data, '|');
			}
			$con->sql_freeresult($q3);
		}
		$con->sql_freeresult($q1);
		fputcsv_eol($fp, array("END"));
		
		//if(isset($fp) && $fp){
			fclose($fp);
			chmod($folder_path."/".$filename,0777);
			print " - Done. ".$folder_path."/".$filename."\n";
		//}
		
		if(!$this->skip_ftp){
			// Upload
			$full_filepath = $folder_path."/".$filename;
			
			$remote_folder = 'sales';
			
			// Create Folder
			//if (ftp_nlist($this->ftp_conn_id, $remote_folder) === false) {
			ftp_mkdir($this->ftp_conn_id, $remote_folder);
			//}
			
			// Year
			$remote_folder .= "/".date("Y", strtotime($date));
			ftp_mkdir($this->ftp_conn_id, $remote_folder);
			
			// Month
			$remote_folder .= "/".date("m", strtotime($date));
			ftp_mkdir($this->ftp_conn_id, $remote_folder);
			
			// Day
			$remote_folder .= "/".date("d", strtotime($date));
			ftp_mkdir($this->ftp_conn_id, $remote_folder);
			
			$remote_filename = $remote_folder."/".$filename;
			print " - Uploading... ";
			
			if (ftp_put($this->ftp_conn_id, $remote_filename, $full_filepath, FTP_BINARY)) {
				print "successfully uploaded to remote location at $remote_filename.\n";
			} else {
				$error = error_get_last();
				print "There was a problem while uploading.\n";
				print_r($error);
				foreach($error as $e){
					$this->error_list[] = $e;
				}
				return;
			}

			// Move to uploaded folder
			rename($full_filepath, $uploaded_folder_path."/".$filename);
		}		
	}
}

$argv = $_SERVER['argv'];
$CRON_KOMAISO = new CRON_KOMAISO();
$CRON_KOMAISO->start();
?>
