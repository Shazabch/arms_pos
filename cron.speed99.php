<?php
/*
1/20/2021 1:15 PM Andy
- Added sync_warehouse and sync_branch.
- Changed sku_price to commit after update each sku.
- Modified sync_sku_price code to sync by branch.

4/14/2021 2:46 PM Andy
- Enhanced to check if is_same_arms_code_old_code then use the link_code as arms_code.

4/23/2021 6:33 PM Shane
- Added vbal and staff ftp upload. New sync_type: eod, vbal, staff

4/27/2021 10:49 AM Andy
- Enhanced to add sku_items_smark.

4/28/2021 1:37 AM Shane
- Added denso transaction ftp upload. New sync_type: densotrn
- Added branch_id folder to vbal and staff files.

5/2/2021 4:04 PM Shane
- Changed arev and navision sales ftp folder to "Shared/Daily/$y/$m/$d"

5/3/2021 4:56 PM Shane
- Changed recent_day to include today's date.

5/4/2021 9:15 AM Shane
- Changed vbal filename prefix to 'V'.
*/
define('armshq_speed99',1);
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '2G');
set_time_limit(0);

include_once("speed99.include.php");

class CRON_SPEED99 {
	var $b_list = array();
	var $fp_path = '';
	var $Speed99 = false;
	var $sync_type_list = array('master', 'sales', 'vendor', 'uom', 'cat', 'sku', 'sku_price', 'sales_arev', 'sales_navision', 'warehouse', 'branch', 'eod', 'vbal', 'staff','densotrn');
	var $selected_sync_type = '';
	var $ftp_conn_id = false;
	var $sales_arev_ftp_conn_id = false;
	var $sales_navision_ftp_conn_id = false;
	var $vbal_ftp_conn_id = false;
	var $staff_ftp_conn_id = false;
	var $denso_ftp_conn_id = false;
	
	var $attch_file_folder = "attch/speed99";
	var $attch_file_folder_master = "attch/speed99/master";
	var $attch_file_folder_sales = "attch/speed99/sales";
	var $attch_file_folder_eod = "attch/speed99/eod";
	var $attch_file_folder_others = "attch/speed99/others";
	
	var $need_check_branch = true;
	var $is_same_arms_code_old_code = false;
	
	// For Sales
	var $date_from = '';
	var $date_to = '';
	var $regen_sales = false;
	
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
	
	function start(){
		print "Start\n";
		
		$this->init_speed99();
		$this->filter_argv();
		$this->check_argv();
		
		if($this->selected_sync_type == 'warehouse'){
			// Sync Warehouse
			$this->sync_warehouse();
		}
		
		if($this->selected_sync_type == 'branch'){
			// Sync Branch
			$this->sync_branch();
		}
		
		if(!$this->selected_sync_type || $this->selected_sync_type == 'master' || $this->selected_sync_type == 'vendor' || $this->selected_sync_type == 'uom' || $this->selected_sync_type == 'cat' || $this->selected_sync_type == 'sku' || $this->selected_sync_type == 'sku_price'){
			// Connect Master FTP Server
			$this->connect_master_ftp();
			
			// Sync Masterfile
			$this->sync_master();
		}
		
		if(!$this->selected_sync_type || $this->selected_sync_type == 'sales' || $this->selected_sync_type == 'sales_arev' || $this->selected_sync_type == 'sales_navision'){
			// Upload Sales for Arev
			if(!$this->selected_sync_type || $this->selected_sync_type == 'sales' || $this->selected_sync_type == 'sales_arev'){
				$this->sync_sales_arev();
			}
			
			// Upload Sales for Navision
			if(!$this->selected_sync_type || $this->selected_sync_type == 'sales' || $this->selected_sync_type == 'sales_navision'){
				$this->sync_sales_navision();
			}
		}

		if(!$this->selected_sync_type || $this->selected_sync_type == 'eod' || $this->selected_sync_type == 'vbal' || $this->selected_sync_type == 'staff' || $this->selected_sync_type == 'densotrn'){
			// Upload View Balance (vbal)
			if(!$this->selected_sync_type || $this->selected_sync_type == 'eod' || $this->selected_sync_type == 'vbal'){
				$this->sync_vbal();
			}
			
			// Upload Staff Attendance
			if(!$this->selected_sync_type || $this->selected_sync_type == 'eod' || $this->selected_sync_type == 'staff'){
				$this->sync_staff();
			}

			// Upload Denso Transaction
			if(!$this->selected_sync_type || $this->selected_sync_type == 'eod' || $this->selected_sync_type == 'densotrn'){
				$this->sync_densotrn();
			}
		}
		
		print "Sync Completed\n";
	}
	
	function init_speed99(){
		$this->Speed99 = new Speed99();
		
		$result = $this->Speed99->check_config();
		if(!$result['ok'] || $result['error']){
			print $result['error']."\n";
			exit;
		}
		
		check_and_create_dir($this->attch_file_folder);
		check_and_create_dir($this->attch_file_folder_master);
		check_and_create_dir($this->attch_file_folder_sales);
		check_and_create_dir($this->attch_file_folder_eod);
		check_and_create_dir($this->attch_file_folder_others);
		
		// init variables
		$this->is_same_arms_code_old_code = $this->Speed99->is_same_arms_code_old_code();
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

				$con->sql_query("select id,code,warehouse_number,warehouse_name from branch $branch_filter order by sequence,code");
				while($r = $con->sql_fetchassoc()){
					$this->b_list[$r['id']] = $r;
				}
				$con->sql_freeresult();
			}elseif($cmd_head == "-send"){
				$this->is_send = true;
			}elseif($cmd_head == "-sync_type"){
				if(!in_array($cmd_value, $this->sync_type_list)){
					print "Invalid Sync Type\n";
					exit;
				}
				$this->selected_sync_type = $cmd_value;
				if($cmd_value == 'branch' || $cmd_value == 'warehouse')	$this->need_check_branch = false;
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
			}elseif($cmd_head == "-recent_day"){	// use today
				$num = mi($cmd_value);
				if($num<0)	die("Recent Day must more than zero.\n");
				
				$date_to = date("Y-m-d");
				if($num == 0){
					$date_from = date("Y-m-d");
				}else{
					$date_from = date("Y-m-d", strtotime("-".$num." day"));
				}
			}elseif($cmd_head == "-regen_sales"){	// regenerate
				$this->regen_sales = true;
			}else{
				print "Unknown command $cmd\n";
				exit;
			}
		}
		
		if(!$this->selected_sync_type || $this->selected_sync_type == 'sales' || $this->selected_sync_type == 'sales_arev' || $this->selected_sync_type == 'sales_navision' || $this->selected_sync_type == 'eod' || $this->selected_sync_type == 'vbal' || $this->selected_sync_type == 'staff' || $this->selected_sync_type == 'densotrn'){
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
		}
		
		
	}
	
	function check_argv(){
		// Branch
		if($this->need_check_branch){
			if(!$this->b_list)	die("Branch not found.\n");
		}
		
		// Sales Date
		if(!$this->selected_sync_type || $this->selected_sync_type == 'sales' || $this->selected_sync_type == 'sales_arev' || $this->selected_sync_type == 'sales_navision' || $this->selected_sync_type == 'eod' || $this->selected_sync_type == 'vbal' || $this->selected_sync_type == 'staff' || $this->selected_sync_type == 'densotrn'){
			if(!$this->date_from){
				die("Please provide -date_from=\n");
			}
			if(!$this->date_to){
				die("Please provide -date_to=\n");
			}
		}
		
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
	
	private function connect_master_ftp(){
		global $config;
		
		print "Connecting to Speed99 Master FTP Server. . . ";

		// set up basic connection
		$this->ftp_conn_id = ftp_connect($this->Speed99->server_ftp_info['ip'], $this->Speed99->server_ftp_info['port']);
		if(!$this->ftp_conn_id){
			print "Server Cannot be connect.\n";
			exit;
		}
		print "Connected.\n";
		print "Login to the server. . . ";

		// login with username and password
		$login_result = ftp_login($this->ftp_conn_id, $this->Speed99->server_ftp_info['username'], $this->Speed99->server_ftp_info['pass']);
		if(!$login_result){
			print "Failed to login to the Server.\n";
			ftp_close($this->ftp_conn_id);
			exit;
		}
		print "Success.\n";
		
		// Turn on Passive Mode
        ftp_pasv($this->ftp_conn_id, true);
	}
	
	private function connect_sales_arev_ftp(){
		global $config;
		
		print "Connecting to Speed99 Sales Arev FTP Server. . . ";

		// set up basic connection
		$this->sales_arev_ftp_conn_id = ftp_connect($this->Speed99->arev_sales_server_ftp_info['ip'], $this->Speed99->arev_sales_server_ftp_info['port']);
		if(!$this->sales_arev_ftp_conn_id){
			print "Server Cannot be connect.\n";
			exit;
		}
		print "Connected.\n";
		print "Login to the server. . . ";

		// login with username and password
		$login_result = ftp_login($this->sales_arev_ftp_conn_id, $this->Speed99->arev_sales_server_ftp_info['username'], $this->Speed99->arev_sales_server_ftp_info['pass']);
		if(!$login_result){
			print "Failed to login to the Server.\n";
			ftp_close($this->sales_arev_ftp_conn_id);
			exit;
		}
		print "Success.\n";
		
		// Turn on Passive Mode
        ftp_pasv($this->sales_arev_ftp_conn_id, true);
	}
	
	private function connect_sales_navision_ftp(){
		global $config;
		
		print "Connecting to Speed99 Sales Navision FTP Server. . . ";

		// set up basic connection
		$this->sales_navision_ftp_conn_id = ftp_connect($this->Speed99->navision_sales_server_ftp_info['ip'], $this->Speed99->navision_sales_server_ftp_info['port']);
		if(!$this->sales_navision_ftp_conn_id){
			print "Server Cannot be connect.\n";
			exit;
		}
		print "Connected.\n";
		print "Login to the server. . . ";

		// login with username and password
		$login_result = ftp_login($this->sales_navision_ftp_conn_id, $this->Speed99->navision_sales_server_ftp_info['username'], $this->Speed99->navision_sales_server_ftp_info['pass']);
		if(!$login_result){
			print "Failed to login to the Server.\n";
			ftp_close($this->sales_navision_ftp_conn_id);
			exit;
		}
		print "Success.\n";
		
		// Turn on Passive Mode
        ftp_pasv($this->sales_navision_ftp_conn_id, true);
	}
	
	private function set_cron_status($sync_type, $sub_type, $status = 'start', $params = array()){
		global $con;
		
		$upd = array();
		
		if($status == 'start'){
			$upd['sync_type'] = $sync_type;
			$upd['sub_type'] = $sub_type;
			$upd['total_record'] = 0;
			$upd['new_record'] = 0;
			$upd['update_record'] = 0;
			$upd['error_record'] = 0;
			$upd['error_list'] = '';
			$upd['start_time'] = 'CURRENT_TIMESTAMP';
			$upd['end_time'] = 0;
			$upd['status'] = 0;
			
			$con->sql_query_skip_logbin("replace into speed99_cron_status ".mysql_insert_by_field($upd));
		}elseif($status == 'end'){
			$upd['total_record'] = mi($params['total_record']);
			$upd['new_record'] = mi($params['new_record']);
			$upd['update_record'] = mi($params['update_record']);
			$upd['error_record'] = mi($params['error_record']);
			$upd['error_list'] = serialize($params['error_list']);
			$upd['end_time'] = 'CURRENT_TIMESTAMP';
			if($upd['error_record']>0 || (is_array($params['error_list']) && count($params['error_list'])>0)){
				$upd['status'] = 2;	// finish with error
			}else{
				$upd['status'] = 1;	// finish without error
			}
			
			$con->sql_query_skip_logbin("update speed99_cron_status set ".mysql_update_by_field($upd)." where sync_type=".ms($sync_type)." and sub_type=".ms($sub_type));
		}else{
			die("Unknown status.\n");
			return;
		}
		
	}
	
	private function sync_master(){
		print "=============== Sync Master ===============\n";
		
		// Sync Vendor
		if(!$this->selected_sync_type || $this->selected_sync_type == 'master' || $this->selected_sync_type == 'vendor'){
			$this->sync_vendor();
		}
		
		// Sync UOM
		if(!$this->selected_sync_type || $this->selected_sync_type == 'master' || $this->selected_sync_type == 'uom'){
			$this->sync_uom();
		}
		
		// Sync Category
		if(!$this->selected_sync_type || $this->selected_sync_type == 'master' || $this->selected_sync_type == 'cat'){
			$this->sync_category();
		}
		
		// Sync SKU
		if(!$this->selected_sync_type || $this->selected_sync_type == 'master' || $this->selected_sync_type == 'sku'){
			$this->sync_sku();
		}
		
		// Sync SKU Price
		if(!$this->selected_sync_type || $this->selected_sync_type == 'master' || $this->selected_sync_type == 'sku_price'){
			$this->sync_sku_price();
		}
	}
	
	private function sync_vendor(){
		global $con;
		
		print ">>>>> Sync Vendor...\n";
		// Mark Sync Status = Start
		$this->set_cron_status('master', 'vendor' , 'start');
		
		// Create Table to store speed99 records
		$con->sql_query_skip_logbin("create table if not exists tmp_speed99_supplier(
			code char(10) primary key,
			description char(100) not null,
			company_no char(30) not null,
			delivery_type char(15) not null
		) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
		$con->sql_query_skip_logbin("truncate tmp_speed99_supplier");
		
		// Change Directory to Masterfile Path
		ftp_chdir($this->ftp_conn_id, $this->Speed99->server_ftp_info['master_path']);
		
		$server_filename = 'SUPPLIER.TXT';
		$local_filename = $this->attch_file_folder_master.'/SUPPLIER.TXT';
		
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
			$this->set_cron_status('master', 'vendor' , 'end', $params);
			return;
		}
		
		// Start MySQL Transaction
		$con->sql_begin_transaction();
		
		// Open File
		$f = fopen($local_filename, "rt");
		$total_record = 0;
		$new_record = 0;
		$update_record = 0;
		$error_record = 0;
		$error_list = array();
		while($line = fgets($f)){
			$total_record++;
			
			//print "$total_record) ".$line;
			print "\rChecking line $total_record";
			$data = explode(",", $line);
			//print_r($data);
			
			$code = trim($data[0]);
			$description = trim($data[1]);
			$company_no = trim($data[2]);
			$delivery_type = trim($data[3]) == "D" ? "direct" : "";
			
			// Code cannot empty
			if(!$code){
				// Vendor Code is empty
				$error_list[] = "Line ($total_record): Vendor Code is Empty.";
				$error_record++;
				continue;
			}
			
			// Store a record in arms table
			$ins = array();
			$ins['code'] = $code;
			$ins['description'] = $description;
			$ins['company_no'] = $company_no;
			$ins['delivery_type'] = $delivery_type;
			$con->sql_query_skip_logbin("insert into tmp_speed99_supplier ".mysql_insert_by_field($ins));
			
			// Get current vendor by vendor code
			$con->sql_query("select * from vendor where code=".ms($code));
			$vendor_info = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$vendor_info){
				// New Vendor
				$new_data = array();
				$new_data['code'] = $code;
				$new_data['description'] = $description;
				$new_data['company_no'] = $company_no;
				$new_data['delivery_type'] = $delivery_type;
				$new_data['active'] = 1;
				
				$con->sql_query("insert into vendor ".mysql_insert_by_field($new_data));
				$new_record++;
			}else{
				// Existing Vendor
				if($vendor_info['description'] != $description || $vendor_info['company_no'] != $company_no || $vendor_info['delivery_type'] != $delivery_type){
					$upd_data = array();
					$upd_data['description'] = $description;
					$upd_data['company_no'] = $company_no;
					$upd_data['delivery_type'] = $delivery_type;
					
					$con->sql_query("update vendor set ".mysql_update_by_field($upd_data)." where id=".mi($vendor_info['id']));
					$update_record++;
				}
			}
		}
		
		// Construct Summaru for Sync Status
		$params = array();
		$params['total_record'] = $total_record;
		$params['new_record'] = $new_record;
		$params['update_record'] = $update_record;
		$params['error_record'] = $error_record;
		$params['error_list'] = $error_list;
		
		// Mark Sync Status = End
		$this->set_cron_status('master', 'vendor' , 'end', $params);
		
		// Commit MySQL Changes
		$con->sql_commit();
		
		print "\n";
		print "Total Record: $total_record\n";
		print "New Record: $new_record\n";
		print "Update Record: $update_record\n";
		print "Error Record: $error_record\n";
		print "Done.\n";
	}
	
	private function sync_uom(){
		global $con;
		
		print ">>>>> Sync UOM...\n";
		
		// Mark Sync Status = Start
		$this->set_cron_status('master', 'uom' , 'start');
		
		// Create Table to store speed99 records
		$con->sql_query_skip_logbin("create table if not exists tmp_speed99_uom(
			code char(6) primary key,
			description char(100) not null,
			fraction double not null default 1
		) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
		$con->sql_query_skip_logbin("truncate tmp_speed99_uom");
		
		// Change Directory to Masterfile Path
		ftp_chdir($this->ftp_conn_id, $this->Speed99->server_ftp_info['master_path']);
		
		$server_filename = 'UOM.TXT';
		$local_filename = $this->attch_file_folder_master.'/UOM.TXT';
		
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
			$this->set_cron_status('master', 'uom' , 'end', $params);
			return;
		}
		
		// Start MySQL Transaction
		$con->sql_begin_transaction();
		
		// Open File
		$f = fopen($local_filename, "rt");
		$total_record = 0;
		$new_record = 0;
		$update_record = 0;
		$error_record = 0;
		$error_list = array();
		while($line = fgets($f)){
			$total_record++;
			
			//print "$total_record) ".$line;
			print "\rChecking line $total_record";
			$data = explode(",", $line);
			//print_r($data);
			
			$code = trim($data[0]);
			$description = trim($data[1]);
			$fraction = mf($data[2]);
			if($this->is_same_arms_code_old_code)	$fraction = 1;
			
			// Code cannot empty
			if(!$code){
				// UOM Code is empty
				$error_list[] = "Line ($total_record): UOM Code is Empty.";
				$error_record++;
				continue;
			}
			
			// Store a record in arms table
			$ins = array();
			$ins['code'] = $code;
			$ins['description'] = $description;
			$ins['fraction'] = $fraction;
			$con->sql_query_skip_logbin("insert into tmp_speed99_uom ".mysql_insert_by_field($ins));
			
			// Get current uom by uom code
			$con->sql_query("select * from uom where code=".ms($code));
			$uom_info = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$uom_info){
				// New UOM
				$new_data = array();
				$new_data['code'] = $code;
				$new_data['description'] = $description;
				$new_data['fraction'] = $fraction;
				$new_data['active'] = 1;
				
				$con->sql_query("insert into uom ".mysql_insert_by_field($new_data));
				$new_record++;
			}else{
				// Existing UOM
				if($uom_info['description'] != $description || $uom_info['fraction'] != $fraction){
					$upd_data = array();
					$upd_data['description'] = $description;
					$upd_data['fraction'] = $fraction;
					
					$con->sql_query("update uom set ".mysql_update_by_field($upd_data)." where id=".mi($uom_info['id']));
					$update_record++;
				}
			}
		}
		
		// Construct Summaru for Sync Status
		$params = array();
		$params['total_record'] = $total_record;
		$params['new_record'] = $new_record;
		$params['update_record'] = $update_record;
		$params['error_record'] = $error_record;
		$params['error_list'] = $error_list;
		
		// Mark Sync Status = End
		$this->set_cron_status('master', 'uom' , 'end', $params);
		
		// Commit MySQL Changes
		$con->sql_commit();
		
		print "\n";
		print "Total Record: $total_record\n";
		print "New Record: $new_record\n";
		print "Update Record: $update_record\n";
		print "Error Record: $error_record\n";
		print "Done.\n";
	}
	
	private function sync_category(){
		global $con;
		
		print ">>>>> Sync Category...\n";
		
		// Mark Sync Status = Start
		$this->set_cron_status('master', 'category' , 'start');
		
		// Create Table to store speed99 records
		$con->sql_query_skip_logbin("create table if not exists tmp_speed99_category(
			code char(15) primary key,
			description char(100) not null
		) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
		$con->sql_query_skip_logbin("truncate tmp_speed99_category");
		
		
		// Change Directory to Masterfile Path
		ftp_chdir($this->ftp_conn_id, $this->Speed99->server_ftp_info['master_path']);
			
		$filename_list = array('DEPT.TXT', 'GROUP.TXT', 'CLASS.TXT');
		foreach($filename_list as $filename){
			$server_filename = $filename;
			$local_filename = $this->attch_file_folder_master.'/'.$filename;
			
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
				$this->set_cron_status('master', 'category' , 'end', $params);
				return;
			}
		}
		
		// Start MySQL Transaction
		$con->sql_begin_transaction();
		
		$total_record = 0;
		$new_record = 0;
		$update_record = 0;
		$error_record = 0;
		$error_list = array();
		
		foreach($filename_list as $filename){
			$local_filename = $this->attch_file_folder_master.'/'.$filename;
			$line_num = 0;
			
			// Open File
			$f = fopen($local_filename, "rt");
			
			while($line = fgets($f)){
				$total_record++;
				$line_num++;
				
				//print "$total_record) ".$line;
				print "\rChecking '$filename' line $line_num";
				$data = explode(",", $line);
				//print_r($data);
				
				$code = trim($data[0]);
				$description = trim($data[1]);
				
				// Code cannot empty
				if(!$code){
					// UOM Code is empty
					$error_list[] = "$filename, Line ($line_num): Code is Empty.";
					$error_record++;
					continue;
				}
				
				// Store a record in arms table
				$ins = array();
				$ins['code'] = $code;
				$ins['description'] = $description;
				$con->sql_query_skip_logbin("insert into tmp_speed99_category ".mysql_insert_by_field($ins));
				
				// Get current uom by uom code
				$con->sql_query("select * from category where code=".ms($code));
				$cat_info = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$cat_info){
					// New Category
					// Construct Category Tree
					$dept_code = '';
					$cat_code = '';
					
					// More than 3 Digits is at least 3rd Level Category
					if(strlen($code)>3)	$dept_code = substr($code, 0, 3);
					
					// More than 6 Digits is 4th Category
					if(strlen($code)>6)	$cat_code = substr($code, 0, 6);
					
					if($cat_code){
						$cat_lv = 4;
						
						// Get Parent Category
						$con->sql_query("select * from category where level=3 and code=".ms($cat_code));
					}
					elseif($dept_code){
						$cat_lv = 3;
						
						// Get Parent Category
						$con->sql_query("select * from category where level=2 and code=".ms($dept_code));
					}
					else{
						$cat_lv = 2;
						
						// Get Parent Category
						$con->sql_query("select * from category where id=1 and level=1");
					}
					
					$parent_cat = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					// Parent Category Not Found
					if(!$parent_cat){
						$error_list[] = "Code: $code, Cant find parent category.";
						$error_record++;
						continue;
					}
				
					$new_data = array();
					$new_data['code'] = $code;
					$new_data['description'] = $description;
					$new_data['active'] = 1;
					$new_data['root_id'] = $parent_cat['id'];
					$new_data['level'] = $cat_lv;
					$new_data['department_id'] = $parent_cat['department_id'];
					$new_data['tree_str'] = $parent_cat['tree_str'].'('.$parent_cat['id'].')';
					
					$con->sql_query("insert into category ".mysql_insert_by_field($new_data));
					$new_cat_id = $con->sql_nextid();
					
					if($cat_lv == 2){
						// Update own id as department_id
						$con->sql_query("update category set department_id=$new_cat_id where id=$new_cat_id");
					}
					$new_record++;
				}else{
					// Existing Category
					if($cat_info['description'] != $description){
						$upd_data = array();
						$upd_data['description'] = $description;
						
						$con->sql_query("update category set ".mysql_update_by_field($upd_data)." where id=".mi($cat_info['id']));
						$update_record++;
					}
				}
			}
			print "\n";
		}
		
		// create category_cache
		if($new_record>0 || $update_record>0)	build_category_cache();
		
		// Construct Summaru for Sync Status
		$params = array();
		$params['total_record'] = $total_record;
		$params['new_record'] = $new_record;
		$params['update_record'] = $update_record;
		$params['error_record'] = $error_record;
		$params['error_list'] = $error_list;
		
		// Mark Sync Status = End
		$this->set_cron_status('master', 'category' , 'end', $params);
		
		// Commit MySQL Changes
		$con->sql_commit();
		
		print "\n";
		print "Total Record: $total_record\n";
		print "New Record: $new_record\n";
		print "Update Record: $update_record\n";
		print "Error Record: $error_record\n";
		print "Done.\n";
	}
	
	private function sync_sku(){
		global $con, $config;
		
		print ">>>>> Sync SKU...\n";
		
		// Mark Sync Status = Start
		$this->set_cron_status('master', 'sku' , 'start');
		
		// Create Table to store speed99 records
		$con->sql_query_skip_logbin("drop table if exists tmp_speed99_sku");
		$con->sql_query_skip_logbin("create table if not exists tmp_speed99_sku(
			link_code char(20) primary key,
			description char(200) not null,
			receipt_description char(40) not null,
			uom_code char(6) not null,
			cost_price double not null default 0,
			selling_price double not null default 0,
			parent_link_code char(20) not null,
			dept_code char(15) not null,
			cat1_code char(15) not null,
			cat2_code char(15) not null,
			status char(5) not null,
			is_parent tinyint(1) not null default 0,
			cat_id int not null default 0,
			uom_id int not null default 0,
			mcode char(15) not null,
			vendor_code char(10) not null,
			vendor_id int not null default 0,
			uom_fraction double not null default 0,
			actual_parent_link_code char(20) not null,
			index is_parent (is_parent),
			index parent_link_code (parent_link_code),
			index actual_parent_link_code (actual_parent_link_code)
		) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
		//$con->sql_query_skip_logbin("truncate tmp_speed99_sku");
		$con->sql_query_skip_logbin("drop table if exists tmp_speed99_sku_items_smark");
		$con->sql_query_skip_logbin("create table if not exists tmp_speed99_sku_items_smark(
			link_code char(20) not null,
			smark char(15) not null,
			index link_code (link_code),
			index smark (smark)
		) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
		
		// Change Directory to Masterfile Path
		ftp_chdir($this->ftp_conn_id, $this->Speed99->server_ftp_info['master_path']);
			
		$filename_list = array('SKU.TXT', 'SMARK.TXT', 'SKUSUPP.TXT');
		foreach($filename_list as $filename){
			$server_filename = $filename;
			$local_filename = $this->attch_file_folder_master.'/'.$filename;
			
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
				$this->set_cron_status('master', 'sku' , 'end', $params);
				return;
			}
		}
		
		// Start MySQL Transaction
		$con->sql_begin_transaction();
		
		$total_record = 0;
		$new_record = 0;
		$update_record = 0;
		$error_record = 0;
		$error_list = array();
		
		// SKU.TXT
		$filename = 'SKU.TXT';
		$local_filename = $this->attch_file_folder_master.'/'.$filename;
		$line_num = 0;
		
		// Open File
		$f = fopen($local_filename, "rt");
		
		while($line = fgets($f)){
			$line_num++;
			$total_record++;
			
			print "\rChecking '$filename' line $line_num";
			$data = explode(",", $line);
			//print_r($data);
			
			$link_code = trim($data[0]);
			$description = trim($data[1]);
			$receipt_description = trim($data[2]);
			$uom_code = trim($data[3]);
			$cost_price = mf($data[4]);
			$selling_price = mf($data[5]);
			$parent_link_code = trim($data[6]);
			$dept_code = trim($data[7]);
			$cat1_code = trim($data[8]);
			$cat2_code = trim($data[9]);
			$status = trim($data[10]);
			$is_parent = $parent_link_code ? 0 : 1;
			
			// Code cannot empty
			if(!$link_code){
				$error_list[] = "$filename, Line ($line_num): SKU Old Code is Empty.";
				$error_record++;
				continue;
			}
			
			// Old code must start with 28 if same with arms code
			if($this->is_same_arms_code_old_code && !preg_match('/^28/', $link_code)){
				$error_list[] = "$filename, Line ($line_num): SKU Old Code must start with 28.";
				$error_record++;
				continue;
			}
			
			// Check Category Code
			$cat_code = $dept_code.$cat1_code.$cat2_code;
			$cat_info = $this->get_category_by_code($cat_code);
			if(!$cat_info){
				$error_list[] = "$filename, Line ($line_num): Category Code '$cat_code' Not Found.";
				$error_record++;
				continue;
			}
			
			// Check UOM Code
			$uom_info = $this->get_uom_by_code($uom_code);
			if(!$uom_info){
				$error_list[] = "$filename, Line ($line_num): UOM Code '$uom_code' Not Found.";
				$error_record++;
				continue;
			}else{
				$uom_fraction = mf($uom_info['fraction']);
				// Parent must fraction = 1
				//if($is_parent && $uom_info['fraction'] != 1){
					//$is_parent = 0;
					//	$error_list[] = "$filename, Line ($line_num): SKU Code '$link_code' is parent and required uom fraction = 1.";
					//	$error_record++;
					//	continue;
				//}
			}
			
			
			
			// Store a record in arms table
			$ins = array();
			$ins['link_code'] = $link_code;
			$ins['description'] = $description;
			$ins['receipt_description'] = $receipt_description;
			$ins['uom_code'] = $uom_code;
			$ins['cost_price'] = $cost_price;
			$ins['selling_price'] = $selling_price;
			$ins['actual_parent_link_code'] = $ins['parent_link_code'] = $parent_link_code;
			if(!$ins['actual_parent_link_code'])	$ins['actual_parent_link_code'] = $link_code;
			$ins['dept_code'] = $dept_code;
			$ins['cat1_code'] = $cat1_code;
			$ins['cat2_code'] = $cat2_code;
			$ins['status'] = $status;
			$ins['is_parent'] = $is_parent;
			$ins['cat_id'] = $cat_info['id'];
			$ins['uom_id'] = $uom_info['id'];
			$ins['uom_fraction'] = $uom_fraction;
			
			$con->sql_query_skip_logbin("insert into tmp_speed99_sku ".mysql_insert_by_field($ins));
		}
		print "\n";
		
		// SMARK.TXT
		$filename = 'SMARK.TXT';
		$local_filename = $this->attch_file_folder_master.'/'.$filename;
		$line_num = 0;
		
		// Open File
		$f = fopen($local_filename, "rt");
		
		while($line = fgets($f)){
			$line_num++;
			$total_record++;
			
			print "\rChecking '$filename' line $line_num";
			$data = explode(",", $line);
			//print_r($data);
			
			$smark = trim($data[0]);
			$link_code = trim($data[1]);
			
			// MCode cannot empty
			if(!$smark){
				$error_list[] = "$filename, Line ($line_num): SKU SMark is Empty.";
				$error_record++;
				continue;
			}
			
			// Old Code cannot empty
			if(!$link_code){
				$error_list[] = "$filename, Line ($line_num): SKU Old Code is Empty.";
				$error_record++;
				continue;
			}
			
			// Get the record
			$con->sql_query("select * from tmp_speed99_sku where link_code=".ms($link_code));
			$sp9_sku = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$sp9_sku){
				$error_list[] = "$filename, Line ($line_num): SKU Old Code '$link_code' Not Found.";
				$error_record++;
				continue;
			}
			
			// Update SKU MCode if it is empty
			$upd = array();
			$upd['mcode'] = $smark;
			$con->sql_query_skip_logbin("update tmp_speed99_sku set ".mysql_update_by_field($upd)." where link_code=".ms($link_code)." and mcode=''");
			
			$ins = array();
			$ins['link_code'] = $link_code;
			$ins['smark'] = $smark;
			$con->sql_query_skip_logbin("replace into tmp_speed99_sku_items_smark ".mysql_insert_by_field($ins));
		}
		
		print "\n";
		
		// SKUSUPP.TXT
		$filename = 'SKUSUPP.TXT';
		$local_filename = $this->attch_file_folder_master.'/'.$filename;
		$line_num = 0;
		
		// Open File
		$f = fopen($local_filename, "rt");
		
		while($line = fgets($f)){
			$line_num++;
			$total_record++;
			
			print "\rChecking '$filename' line $line_num";
			$data = explode(",", $line);
			//print_r($data);
			
			$link_code = trim($data[0]);
			$vendor_code = trim($data[1]);
			
			// Old Code cannot empty
			if(!$link_code){
				$error_list[] = "$filename, Line ($line_num): SKU Old Code is Empty.";
				$error_record++;
				continue;
			}
			
			// Vendor Code cannot empty
			if(!$vendor_code){
				$error_list[] = "$filename, Line ($line_num): Vendor Code is Empty.";
				$error_record++;
				continue;
			}
			
			// Get the record
			$con->sql_query("select * from tmp_speed99_sku where link_code=".ms($link_code));
			$sp9_sku = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$sp9_sku){
				$error_list[] = "$filename, Line ($line_num): SKU Old Code '$link_code' Not Found.";
				$error_record++;
				continue;
			}
			
			// Get Vendor by Vendor Code
			$con->sql_query("select id from vendor where code=".ms($vendor_code));
			$vendor = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$vendor){
				$error_list[] = "$filename, Line ($line_num): Vendor '$vendor_code' Not Found.";
				$error_record++;
				continue;
			}
			
			// Store a record in arms table
			$ins = array();
			$ins['vendor_code'] = $vendor_code;
			$ins['vendor_id'] = $vendor['id'];
			$con->sql_query_skip_logbin("update tmp_speed99_sku set ".mysql_update_by_field($ins)." where link_code=".ms($link_code));
		}
		print "\n";
		
		$line_num = 0;
		
		// Fix parent issue, due to some speed99 parent uom fraction is not 1, we need to find other sku to mark as parent
		$q1 = $con->sql_query("select * from tmp_speed99_sku where is_parent=1 and uom_fraction<>1 order by link_code");
		$total_problem_count = $con->sql_numrows();
		$fixed_count = 0;
		while($r = $con->sql_fetchassoc($q1)){
			$line_num++;
			print "\rFix parent & child: $line_num / $total_problem_count";
			$link_code = trim($r['link_code']);
			
			// Find another sku which fraction = 1
			$con->sql_query("select * from tmp_speed99_sku where parent_link_code=".ms($link_code)." and uom_fraction=1 order by link_code limit 1");
			$sp9_sku = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			// Found other sku can switch to parent
			if($sp9_sku){
				// Change this sku as parent
				$con->sql_query("update tmp_speed99_sku set is_parent=1,actual_parent_link_code=".ms($sp9_sku['link_code'])." where link_code=".ms($sp9_sku['link_code']));
				
				// Change the original parent to child
				$con->sql_query("update tmp_speed99_sku set is_parent=0,actual_parent_link_code=".ms($sp9_sku['link_code'])." where link_code=".ms($link_code));
				
				// Change all other child to link to this new sku parent
				$con->sql_query("update tmp_speed99_sku set is_parent=0,actual_parent_link_code=".ms($sp9_sku['link_code'])." where actual_parent_link_code=".ms($link_code));
				
				$fixed_count++;
			}
			
		}
		$con->sql_freeresult($q1);
		
		if($total_problem_count > 0){
			print "\n";
			print "Total Fixed: $fixed_count\n";
			print "Not Fixed: ".($total_problem_count-$fixed_count)."\n";
		}
		
		$q1 = $con->sql_query("select * from tmp_speed99_sku order by is_parent desc, link_code");
		$total_sku_count = $con->sql_numrows($q1);
		$line_num = 0;
		while($r = $con->sql_fetchassoc($q1)){
			$line_num++;
			
			print "\rAdd / Update sku: $line_num / $total_sku_count";
			
			$link_code = trim($r['link_code']);
			$is_parent = mi($r['is_parent']);
			$cat_id = mi($r['cat_id']);
			$uom_id = mi($r['uom_id']);
			$vendor_id = mi($r['vendor_id']);
			
			$sku_id = 0;
			$sku_item_code = '';
			$sku_ins = array();
			$si_ins = array();
			$sid = 0;
			
			// Get sku_items
			$con->sql_query("select * from sku_items where link_code=".ms($link_code)." order by id");
			$si = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			// sku exists
			if($si){
				// check parent sku
				$con->sql_query("select id from sku_items where sku_id=".mi($si['sku_id'])." and is_parent=1");
				$parent_si = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($parent_si['id'] == $si['id']){
					$is_parent = 1;
				}else{
					$is_parent = 0;
				}
				$sid = mi($si['id']);
			}
			
			$is_new = false;
			$need_check_update = false;
			$is_updated = false;
			
			// Check Parent
			if($is_parent){
				// Is Parent
				if(!$si){
					// SKU Not Exists - Need to add new 'sku'
					$sku_ins['category_id'] = $cat_id;
					//$sku_ins['uom_id'] = $uom_id;
					$sku_ins['vendor_id'] = $vendor_id;
					//$sku_ins['brand_id'] = $brand_id;
					$sku_ins['status'] = 1;
					$sku_ins['active'] = 1;
					$sku_ins['sku_type'] = 'OUTRIGHT';
					$sku_ins['apply_branch_id'] = 1;
					$sku_ins['added'] = 'CURRENT_TIMESTAMP';
					$sku_ins['scale_type'] = 0;
					
					// Create new SKU
					$con->sql_query("insert into sku ".mysql_insert_by_field($sku_ins));
					$sku_id = mi($con->sql_nextid());
					$sku_code = sprintf(ARMS_SKU_CODE_PREFIX, $sku_id);
					$con->sql_query("update sku set sku_code=".ms($sku_code)." where id=$sku_id");
					
					if($this->is_same_arms_code_old_code){
						$sku_item_code = $link_code;
					}else{
						// Generate new ARMS Code
						$sku_item_code = sprintf(ARMS_SKU_CODE_PREFIX, $sku_id)."0000";
					}
					
					$si_ins['is_parent'] = 1;
					$si_ins['sku_item_code'] = $sku_item_code;
					$si_ins['sku_id'] = $sku_id;
					
					// Found the parent uom fraction is not 1, need to manually create one dummy parent sku
					if($r['uom_fraction'] != 1 && !$this->is_same_arms_code_old_code){
						$si_ins['packing_uom_id'] = 1;
						$si_ins['description'] =  "Dummy Parent for $link_code";
						$si_ins['receipt_description'] =  "$link_code Parent";
						$si_ins['hq_cost'] = $si_ins['cost_price'] = round($r['cost_price'] / $r['uom_fraction'], $config['global_cost_decimal_points']);
						$si_ins['selling_price'] = round($r['selling_price'] / $r['uom_fraction'], 2);
						$si_ins['active'] = 1;
						$si_ins['added'] = 'CURRENT_TIMESTAMP';
						
						/*if($this->is_same_arms_code_old_code){
							// Generate new ARMS Code for parent
							$si_ins['sku_item_code'] = substr($sku_item_code, 0, 8)."9000";
						}*/
						
						$sql = "insert into sku_items ".mysql_insert_by_field($si_ins);
						//print "\n => $sql\n";
						$con->sql_query($sql);
						//$sid = $con->sql_nextid();
						
						// The original sku will no longer a parent
						$si_ins['is_parent'] = 0;
						$si_ins['sku_item_code']++;
						/*if($this->is_same_arms_code_old_code){
							$si_ins['sku_item_code'] = $sku_item_code;
						}else{
							$si_ins['sku_item_code']++;
						}*/
						
					}
					
					$is_new = true;
				}else{
					$need_check_update = true;
				}
			}else{
				// Get Parent
				if(!$si){
					// Get sku_id
					$con->sql_query("select sku_id from sku_items where link_code=".ms($r['actual_parent_link_code'])." order by id");
					$parent_si = $con->sql_fetchassoc();
					$con->sql_freeresult();
					$sku_id = mi($parent_si['sku_id']);
					
					if($this->is_same_arms_code_old_code){
						$si_ins['sku_item_code'] = $link_code;
					}else{
						// Get new ARMS CODE
						$q2 = $con->sql_query("select max(sku_item_code) as sku_item_code from sku_items where sku_id = $sku_id");
						$tmp = $con->sql_fetchassoc($q2);
						$con->sql_freeresult($q2);
						$si_ins['sku_item_code'] = $tmp['sku_item_code']+1;
					}
					
					$si_ins['sku_id'] = $sku_id;
					
					$is_new = true;
				}else{
					$need_check_update = true;
				}
			}
			
			if($is_new){
				if(!$sku_id){
					$error_list[] = "Failed to add new SKU '$link_code'.";
					$error_record++;
					continue;
				}
				
				$si_ins['packing_uom_id'] = $uom_id;
				$si_ins['mcode'] = $r['mcode'];
				$si_ins['link_code'] = $link_code;
				$si_ins['description'] =  $r['description'];
				$si_ins['receipt_description'] =  $r['receipt_description'];
				$si_ins['hq_cost'] = $si_ins['cost_price'] = round($r['cost_price'], $config['global_cost_decimal_points']);
				$si_ins['selling_price'] = round($r['selling_price'], 2);
				$si_ins['active'] = $r['status'] == 'S' ? 0 : 1;
				$si_ins['added'] = 'CURRENT_TIMESTAMP';
				
				$sql = "insert into sku_items ".mysql_insert_by_field($si_ins);
				//print "\n ==>> $sql\n";
				$con->sql_query($sql);
				$sid = $con->sql_nextid();
				
				$new_record++;
			}elseif($need_check_update){
				$si_need_update = false;
				$sku_need_update = false;
				
				// Get sku
				$con->sql_query("select * from sku where id=".mi($si['sku_id']));
				$sku = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				// Check 'sku_items'
				if($si['packing_uom_id'] != $uom_id){
					// UOM Changed
					if($si['is_parent'] && $r['uom_fraction'] != 1){
						$error_list[] = "SKU '$link_code' cannot change to UOM '".$r['uom_code']."' due to it is Parent SKU.";
						$error_record++;
						continue;
					}
					$si_need_update = true;
				}
				
				// Check Master Cost
				if(!$si_need_update && $si['cost_price'] != round($r['cost_price'], $config['global_cost_decimal_points'])){
					$si_need_update = true;
				}
				
				// Check Master Selling
				if(!$si_need_update && $si['selling_price'] != round($r['selling_price'], 2)){
					$si_need_update = true;
				}
				
				// Check Active
				$si_active = $r['status'] == 'S' ? 0 : 1;
				if(!$si_need_update && $si['active'] != $si_active){
					$si_need_update = true;
				}
				
				$arr_check_by_str = array('mcode', 'description', 'receipt_description');
				foreach($arr_check_by_str as $field_name){
					if(trim($si[$field_name]) != trim($r[$field_name])){
						$si_need_update = true;
						break;
					}
				}
				
				// Need Update sku_items
				if($si_need_update){
					$upd = array();
					$upd['packing_uom_id'] = $uom_id;
					$upd['mcode'] = $r['mcode'];
					$upd['description'] = $r['description'];
					$upd['receipt_description'] = $r['receipt_description'];
					$upd['hq_cost'] = $upd['cost_price'] = round($r['cost_price'], $config['global_cost_decimal_points']);
					$upd['selling_price'] = round($r['selling_price'], 2);
					$upd['active'] = $si_active;
					$con->sql_query($sql = "update sku_items set ".mysql_update_by_field($upd)." where id=".mi($si['id']));
					$is_updated = true;
					
					//print $sql;exit;
				}
				
				
				
				// Check 'sku'
				if($is_parent){	 // ignore sku change for child
					// Check Vendor
					if($sku['vendor_id'] != $vendor_id){
						$sku_need_update = true;
					}
					
					// Check Category
					if($sku['category_id'] != $cat_id){
						$sku_need_update = true;
					}
					
					// Need update sku
					if($sku_need_update){
						$upd = array();
						$upd['vendor_id'] = $vendor_id;
						$upd['category_id'] = $cat_id;
						$con->sql_query($sql = "update sku set ".mysql_update_by_field($upd)." where id=".mi($sku['id']));
						//print $sql;exit;
						// Got Change Category
						if($sku['category_id'] != $cat_id){
							update_category_changed($sku['category_id']);
							update_category_changed($cat_id);
						}
						$is_updated = true;
					}
				}
			}
			
			// Update SKU SMark
			$q_sm = $con->sql_query("select * from tmp_speed99_sku_items_smark where link_code=".ms($link_code));
			while($sm = $con->sql_fetchassoc($q_sm)){
				// Check SMark Already Exist
				$con->sql_query("select * from sku_items_smark where sku_item_id=$sid and smark=".ms($sm['smark']));
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$tmp){
					// Not Exist, Need to add
					$ins = array();
					$ins['sku_item_id'] = $sid;
					$ins['smark'] = $sm['smark'];
					$con->sql_query("replace into sku_items_smark ".mysql_insert_by_field($ins));
					$is_updated = true;
				}
			}
			$con->sql_freeresult($q_sm);
			
			// Got any updates
			if($is_updated){
				$con->sql_query("update sku_items_cost set changed=1 where sku_item_id=".mi($sid));
				$update_record++;
			}
		}
		$con->sql_freeresult($q1);
		
		// Construct Summaru for Sync Status
		$params = array();
		$params['total_record'] = $total_record;
		$params['new_record'] = $new_record;
		$params['update_record'] = $update_record;
		$params['error_record'] = $error_record;
		$params['error_list'] = $error_list;
		
		// Mark Sync Status = End
		$this->set_cron_status('master', 'sku' , 'end', $params);
		
		// Commit MySQL Changes
		$con->sql_commit();
		
		print "\n";
		print "Total Record: $total_record\n";
		print "New Record: $new_record\n";
		print "Update Record: $update_record\n";
		print "Error Record: $error_record\n";
		print "Done.\n";
	}
	
	private function sync_sku_price(){
		global $con, $config;

		print ">>>>> Sync SKU Price...\n";
		
		// Mark Sync Status = Start
		$this->set_cron_status('master', 'sku_price' , 'start');
		
		$con->sql_query_skip_logbin("drop table if exists tmp_speed99_sku_price");
		$con->sql_query_skip_logbin("create table if not exists tmp_speed99_sku_price(
			link_code char(20) not null,
			date date,
			cost_price double not null default 0,
			selling_price double not null default 0,
			primary key (link_code, date),
			index date (date)
		) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
		
		// Change Directory to Masterfile Path
		ftp_chdir($this->ftp_conn_id, $this->Speed99->server_ftp_info['master_path']);
		
		$server_filename = 'PCHANGE.TXT';
		$local_filename = $this->attch_file_folder_master.'/PCHANGE.TXT';
		
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
			$this->set_cron_status('master', 'sku_price' , 'end', $params);
			return;
		}
		
		// Start MySQL Transaction
		$con->sql_begin_transaction();
		
		// Open File
		$f = fopen($local_filename, "rt");
		$total_record = 0;
		$new_record = 0;
		$update_record = 0;
		$error_record = 0;
		$error_list = array();
		while($line = fgets($f)){
			$total_record++;
			
			//print "$total_record) ".$line;
			print "\rChecking line $total_record";
			$data = explode(",", $line);
			//print_r($data);
			
			$link_code = trim($data[0]);
			$date = dmy_to_sqldate(trim($data[1]));
			$cost_price = mf($data[2]);
			$selling_price = mf($data[3]);
			
			// Code cannot empty
			if(!$link_code){
				$error_list[] = "Line ($total_record): Old Code is Empty.";
				$error_record++;
				continue;
			}
			
			// Store a record in arms table
			$ins = array();
			$ins['link_code'] = $link_code;
			$ins['date'] = $date;
			$ins['cost_price'] = $cost_price;
			$ins['selling_price'] = $selling_price;
			$con->sql_query_skip_logbin("replace into tmp_speed99_sku_price ".mysql_insert_by_field($ins));
		}
		// Commit MySQL Changes
		$con->sql_commit();
		print "\n";
		
		// Loop Branch
		$total_branch_count = count($this->b_list);
		$curr_branch_count = 0;
		foreach($this->b_list as $b){
			$curr_branch_count++;
			$bid = mi($b['id']);
			
			print "Checking Branch ID#$bid, $curr_branch_count / $total_branch_count . .\n";
			
			// Check which item not yet insert price change
			$q1 = $con->sql_query("select tbl.*, si.id as sid, siph.added, siph.price
				from tmp_speed99_sku_price tbl
				join sku_items si on si.link_code=tbl.link_code
				left join sku_items_price_history siph on siph.branch_id=$bid and siph.sku_item_id=si.id and siph.added=tbl.date
				where siph.price is null
				order by tbl.link_code, tbl.date desc 
				");
			$curr_sku_count = 0;
			$total_sku_count = $con->sql_numrows($q1);
			while($r = $con->sql_fetchassoc($q1)){
				$curr_sku_count ++;
				$is_latest = true;
				$last_link_code = '';
				$link_code = trim($r['link_code']);
				$sid = mi($r['sid']);
				$d = $r['date'];
				$selling_price = round($r['selling_price'], 2);
				$cost_price = round($r['cost_price'], $config['global_cost_decimal_points']);
				
				print "\rSKU $curr_sku_count / $total_sku_count . .";
				
				if($last_link_code != $link_code){
					// Changed sku, need to check latest price
					// Need to update as latest selling price
					$con->sql_query("select * from sku_items_price where branch_id=$bid and sku_item_id=$sid");
					$sip = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if(!$sip || $sip['price'] != $selling_price || strtotime($sip['last_update'])<strtotime($d)){
						$ins = array();
						$ins['branch_id'] = $bid;
						$ins['sku_item_id'] = $sid;
						$ins['last_update'] = $d;
						$ins['price'] = $selling_price;
						$ins['cost'] = $cost_price;
						$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($ins));
					}
				}
				
				// price history
				$ins = array();
				$ins['branch_id'] = $bid;
				$ins['sku_item_id'] = $sid;
				$ins['added'] = $d;
				$ins['price'] = $selling_price;
				$ins['cost'] = $cost_price;
				$ins['source'] = 'CRON';
				$ins['user_id'] = -1;
				$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($ins));
				
				$new_record++;
				
				$last_link_code = $link_code;
			}
			$con->sql_freeresult($q1);
			print "\n";
		}
		// Loop by link_code
		/*$q1 = $con->sql_query("select distinct(link_code) as link_code, count(*) as c
			from tmp_speed99_sku_price
			group by link_code
			order by link_code");
		$updated_count = 0;
		$total_sku_count = $con->sql_numrows($q1);
		$curr_sku_count = 0;
		
		while($r = $con->sql_fetchassoc($q1)){
			// Start MySQL Transaction
			//$con->sql_begin_transaction();
		
			$curr_sku_count++;
			print "\rProcessing SKU $curr_sku_count / $total_sku_count";
			$link_code = trim($r['link_code']);
			
			// Get sku_items
			$con->sql_query("select id from sku_items where link_code=".ms($link_code));
			$si = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			// sku_items not found
			if(!$si){
				$error_list[] = "SKU '$link_code' Not Found.";
				$error_record += $r['c'];
				continue;
			}
			
			$sid = mi($si['id']);
			
			// Get Date List
			$q2 = $con->sql_query("select * from tmp_speed99_sku_price where link_code=".ms($link_code)." order by date desc");
			$is_latest = true;
			while($r2 = $con->sql_fetchassoc($q2)){
				$selling_price = round($r2['selling_price'], 2);
				$cost_price = round($r2['cost_price'], $config['global_cost_decimal_points']);
				$d = $r2['date'];
				$got_update = false;
				$got_new_record = false;
				
				// Loop Branch
				foreach($this->b_list as $b){
					$bid = mi($b['id']);
					$need_update = false;
					$is_new = false;
					
					// Get Price Change History
					$con->sql_query("select * from sku_items_price_history where branch_id=$bid and sku_item_id=$sid and added=".ms($d));
					$siph = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($siph){
						// Records exists - check if selling price different
						if($siph['price'] != $selling_price){
							$need_update = true;
						}
					}else{
						// No record - need add
						$need_update = true;
						$is_new = true;
					}
					
					// Need insert / update
					if($need_update){
						$ins = array();
						$ins['branch_id'] = $bid;
						$ins['sku_item_id'] = $sid;
						$ins['added'] = $d;
						$ins['price'] = $selling_price;
						$ins['cost'] = $cost_price;
						$ins['source'] = 'CRON';
						$ins['user_id'] = -1;
						$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($ins));
						
						if($is_new){
							$got_new_record = true;
						}
						$updated_count++;
						$got_update = true;
					}
					
					if($is_latest){
						// Need to update as latest selling price
						$con->sql_query("select * from sku_items_price where branch_id=$bid and sku_item_id=$sid");
						$sip = $con->sql_fetchassoc();
						$con->sql_freeresult();
						
						if(!$sip || $sip['price'] != $selling_price || strtotime($sip['last_update'])<strtotime($d)){
							$ins = array();
							$ins['branch_id'] = $bid;
							$ins['sku_item_id'] = $sid;
							$ins['last_update'] = $d;
							$ins['price'] = $selling_price;
							$ins['cost'] = $cost_price;
							$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($ins));
							$got_update = true;
						}
					}
				}
				
				if($got_update){
					if($got_new_record){
						$new_record++;
					}else{
						$update_record++;
					}
				}
				
				$is_latest = false;
			}
			$con->sql_freeresult($q2);
			
			// Commit MySQL Changes
			//$con->sql_commit();
		}
		$con->sql_freeresult($q1);*/
		
		
		// Construct Summaru for Sync Status
		$params = array();
		$params['total_record'] = $total_record;
		$params['new_record'] = $new_record;
		$params['update_record'] = $update_record;
		$params['error_record'] = $error_record;
		$params['error_list'] = $error_list;
		
		// Mark Sync Status = End
		$this->set_cron_status('master', 'sku_price' , 'end', $params);
		
		// Commit MySQL Changes
		//$con->sql_commit();
		
		print "\n";
		print "Total Record: $total_record\n";
		print "New Record: $new_record\n";
		print "Update Record: $update_record\n";
		print "Error Record: $error_record\n";
		print "Done.\n";
	}
	
	private function get_category_by_code($cat_code){
		global $con;
		
		$cat_code = trim($cat_code);
		if(!$cat_code)	return false;
		
		// Get category
		$con->sql_query("select * from category where code=".ms($cat_code));
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $data;
	}
	
	private function get_uom_by_code($uom_code){
		global $con;
		
		$uom_code = trim($uom_code);
		if(!$uom_code)	return false;
		
		// Get uom
		$con->sql_query("select * from uom where code=".ms($uom_code));
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $data;
	}
	
	private function get_sales_folder_path($sales_type, $date){
		// check & create folder by branch
		//$folder_path = $this->folder_path.str_replace('/', '', $this->bcode)."/";
		//if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		$folder_path = $this->attch_file_folder_sales;
		
		$year = date('Y',strtotime($date));
		$month = mi(date('m',strtotime($date)));
		
		// check & create folder by year
		$folder_path = $folder_path."/".$year;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		// check & create folder by month
		$folder_path = $folder_path."/".$month;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		// check & create folder by sales type
		$folder_path = $folder_path."/".$sales_type;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		return $folder_path;
	}
	
	private function get_uploaded_sales_folder_path($sales_type, $date){
		$folder_path = $this->get_sales_folder_path($sales_type, $date);
		
		// check & create uploaded folder
		$uploaded_folder_path = $folder_path."/uploaded";
		if(!is_dir($uploaded_folder_path)) check_and_create_dir($uploaded_folder_path);
		
		return $uploaded_folder_path;
	}
	
	private function sync_sales_arev(){
		global $con;
		
		print "=============== Sync Sales for Arev ===============\n";
		
		// Connect Sales Arev FTP Server
		$this->connect_sales_arev_ftp();
		
		print ">>>>> Sync POS ...\n";
		// Mark Sync Status = Start
		$this->set_cron_status('sales', 'pos_arev' , 'start');
		$this->error_list = array();
		
		// Generate Sales File
		foreach($this->b_list as $b){
			$this->generate_sales_arev_file($b);
			print "\n";
		}
		
		$params['error_list'] = $this->error_list;
		// Mark Sync Status = End
		$this->set_cron_status('sales', 'pos_arev' , 'end', $params);
		
		// close the connection
		if($this->sales_arev_ftp_conn_id)	ftp_close($this->sales_arev_ftp_conn_id);
		print "Done.\n";
	}
	
	private function generate_sales_arev_file($b){
		print "Generate Sales Files for ".$b['code']."...\n";
		
		$got_error = false;
		
		// Check Speed99 Outlet Code
		$sp9_outlet_code = $this->Speed99->get_outlet_code($b);
		if(!$sp9_outlet_code){
			$err_msg = "Branch (".$b['code'].") No Speed99 Outlet Code!";
			print $err_msg."\n";
			$this->error_list[] = $err_msg;
			$got_error = true;
		}else{
			print "Speed99 Outlet Code: $sp9_outlet_code\n";
		}
		
		// Check Warehouse Number
		$warehouse_number = trim($b['warehouse_number']);
		if(!$warehouse_number){
			$err_msg = "Branch (".$b['code'].") No Warehouse Number!";
			print $err_msg."\n";
			$this->error_list[] = $err_msg;
			$got_error = true;
		}else{
			print "Warehouse Number: $warehouse_number\n";
		}
		
		// Got Error
		if($got_error){
			return;
		}
		
		// loop date
		for($d1 = strtotime($this->date_from), $d2 = strtotime($this->date_to); $d1 <= $d2; $d1+=86400){
			$this->generate_sales_arev_file_by_date($b, date("Y-m-d", $d1));
		}
	}
	
	private function get_sales_filename($sales_type, $b, $date){
		$bid = mi($b['id']);
		//$settings = $this->configuration[$this->bcode];
		//$filename = sprintf($this->generate_info_filename, $settings['tenant_code'], date('Ymd',strtotime($date)));
		$prefix = $sales_type == 'arev' ? 'A' : 'S';	// A = Arev, S = Navision
		$warehouse_number = trim($b['warehouse_number']);
		$converted_date_value = $this->Speed99->invoice_date_conversion($date);
		$sp9_outlet_code = $this->Speed99->get_outlet_code($b);
		$sp9_outlet_code = $this->Speed99->trim_outlet_code($sp9_outlet_code);

		$point_after_digit = $this->Speed99->get_point_after_digit();

		$tmp = $prefix.$warehouse_number.$converted_date_value.$sp9_outlet_code;
		$fpart1 = substr($tmp,0,$point_after_digit);
		$fpart2 = substr($tmp,$point_after_digit);
		$filename = $fpart1.'.'.$fpart2;
		print "Filename = $filename\n";
		return $filename;
	}
	
	private function generate_sales_arev_file_by_date($b, $date){
		global $con;
		
		$bid = mi($b['id']);
		print "Generating sales file for Arev, Branch ID:$bid, Date: $date\n";
		
		//$settings = $this->configuration[$this->bcode];
		$folder_path = $this->get_sales_folder_path('arev', $date);
		$uploaded_folder_path = $this->get_uploaded_sales_folder_path('arev', $date);
		$filename = $this->get_sales_filename('arev', $b, $date);
		$sp9_outlet_code = $this->Speed99->get_outlet_code($b);
		$converted_date_value = $this->Speed99->invoice_date_conversion($date);
		$invoice_no_prefix = $this->Speed99->get_invoice_no_prefix($bid);
		
		if(!$this->regen_sales){	// no regenerate, check whether file exists
			if(file_exists($uploaded_folder_path."/".$filename) && filesize($uploaded_folder_path."/".$filename) > 0){
				print " - File Uploaded\n";
				return;
			}
		}
		
		
		// Day End Checking
		$ret = $this->check_branch_can_send_sales($bid, $date);
		if($ret['error']){
			print "There was a problem while checking day end.\n";
			print_r($ret['error']);
			foreach($ret['error'] as $e){
				$this->error_list[] = $e;
			}
			return;
		}
		
		
		// Check Finalise
		/*$con->sql_query("select * from pos_finalized where branch_id=$this->bid and finalized=1");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$tmp){
			print " - Not Finalised Yet.\n";
			return;
		}*/
		
		// delete the file
		if(file_exists($folder_path."/".$filename)) unlink($folder_path."/".$filename);
		
		// Create file
		$fp = fopen($folder_path."/".$filename, 'w');
		
		// START Indicator
		//fwrite($fp, "START\n");
		fputcsv_eol($fp, array("START"));
		
		// Branch Starting Line
		$data = array();
		$data[] = "S@".$sp9_outlet_code;
		$data[] = "AA";
		fputcsv_eol($fp, $data);
		//fwrite($fp, "S@".$sp9_outlet_code.",AA");
		
		$sql = "select p.*, user.u as cashier_name, cs.network_name
			from pos p 
			left join user on user.id=p.cashier_id
			left join counter_settings cs on cs.branch_id=p.branch_id and cs.id=p.counter_id
			where p.branch_id=$bid and p.date=".ms($date);
			
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($p = $con->sql_fetchassoc($q1)){	// Loop By Receipt
			$counter_id = mi($p['counter_id']);
			$pos_id = mi($p['id']);
			
			//print $p['receipt_ref_no']."\n";
			
			// Receipt Header
			$data = array();
			$data[] = "H@".$converted_date_value."/".$p['network_name']."/".$invoice_no_prefix.$p['receipt_no'];
			$data[] = $p['cashier_name'];
			$data[] = date("d/m/Y", strtotime($p['pos_time']));
			$data[] = date("H:i:s", strtotime($p['pos_time']));
			$data[] = $p['cancel_status'] == 1 ? 'R' : '';
			fputcsv_eol($fp, $data);
			
			// POS Items
			$q2 = $con->sql_query("select pi.*, si.link_code
				from pos_items pi
				left join sku_items si on si.id=pi.sku_item_id
				where pi.branch_id=$bid and pi.counter_id=$counter_id and pi.date=".ms($date)." and pi.pos_id=$pos_id
				order by pi.item_id");
			while($pi = $con->sql_fetchassoc($q2)){
				$data = array();
				$data[] = "D@".$pi['barcode'];
				$data[] = $pi['link_code'];
				$data[] = $pi['sku_description'];
				$data[] = $pi['qty'];
				$data[] = round($pi['price']-$pi['discount'], 2);
				$data[] = "";
				$data[] = "";
				fputcsv_eol($fp, $data);
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
				$data[] = "R@".round($data_round['amount'], 2);
				fputcsv_eol($fp, $data);
			}
				
			// POS Payment
			$q3 = $con->sql_query("select pp.*
				from pos_payment pp
				where pp.branch_id=$bid and pp.counter_id=$counter_id and pp.date=".ms($date)." and pp.pos_id=$pos_id and pp.adjust=0 and pp.type not in ('Rounding')
				order by pp.id");
			while($pp = $con->sql_fetchassoc($q3)){
				$ptype = $pp['type'];
				if($pp['type'] == 'Mix & Match Total Disc')	$ptype = 'Discount';
				
				$sp9_payment_type = $this->Speed99->get_sp9_payment_type($ptype);
				if(!$sp9_payment_type)	$sp9_payment_type = strtoupper($pp['type']);
				
				$amt = $pp['amount'];
				if($pp['type'] == 'Cash')	$amt -= $p['amount_change'];
				$amt = round($amt, 2);
				
				$data = array();
				$data[] = "P@".$sp9_payment_type;
				$data[] = $amt;
				$data[] = $pp['remark'];
				fputcsv_eol($fp, $data);
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
		
		// Upload
		$full_filepath = $folder_path."/".$filename;
		

		// Create Folder
		//if (ftp_nlist($this->sales_arev_ftp_conn_id, $remote_folder) === false) {
		$remote_folder = 'Shared';
		ftp_mkdir($this->sales_arev_ftp_conn_id, $remote_folder);
		//}
		$remote_folder .= '/Daily';
		ftp_mkdir($this->sales_arev_ftp_conn_id, $remote_folder);
		
		// Year
		$remote_folder .= "/".date("Y", strtotime($date));
		ftp_mkdir($this->sales_arev_ftp_conn_id, $remote_folder);
		
		// Month
		$remote_folder .= "/".date("m", strtotime($date));
		ftp_mkdir($this->sales_arev_ftp_conn_id, $remote_folder);
		
		// Day
		$remote_folder .= "/".date("d", strtotime($date));
		ftp_mkdir($this->sales_arev_ftp_conn_id, $remote_folder);
		
		$remote_filename = $remote_folder."/".$filename;
		print " - Uploading... ";
		
		if (ftp_put($this->sales_arev_ftp_conn_id, $remote_filename, $full_filepath, FTP_BINARY)) {
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
	
	private function sync_sales_navision(){
		global $con;
		
		print "=============== Sync Sales for Navision ===============\n";
		
		// Connect Sales Navision FTP Server
		$this->connect_sales_navision_ftp();
		
		print ">>>>> Sync POS ...\n";
		// Mark Sync Status = Start
		$this->set_cron_status('sales', 'pos_navision' , 'start');
		$this->error_list = array();
		
		// Generate Sales File
		foreach($this->b_list as $b){
			$this->generate_sales_navision_file($b);
			print "\n";
		}
		
		$params['error_list'] = $this->error_list;
		// Mark Sync Status = End
		$this->set_cron_status('sales', 'pos_navision' , 'end', $params);
		
		// close the connection
		if($this->sales_navision_ftp_conn_id)	ftp_close($this->sales_navision_ftp_conn_id);
		print "Done.\n";
	}
	
	private function generate_sales_navision_file($b){
		print "Generate Sales Files for ".$b['code']."...\n";
		
		$got_error = false;
		
		// Check Speed99 Outlet Code
		$sp9_outlet_code = $this->Speed99->get_outlet_code($b);
		if(!$sp9_outlet_code){
			$err_msg = "Branch (".$b['code'].") No Speed99 Outlet Code!";
			print $err_msg."\n";
			$this->error_list[] = $err_msg;
			$got_error = true;
		}else{
			print "Speed99 Outlet Code: $sp9_outlet_code\n";
		}
		
		// Check Warehouse Number
		$warehouse_number = trim($b['warehouse_number']);
		if(!$warehouse_number){
			$err_msg = "Branch (".$b['code'].") No Warehouse Number!";
			print $err_msg."\n";
			$this->error_list[] = $err_msg;
			$got_error = true;
		}else{
			print "Warehouse Number: $warehouse_number\n";
		}
		
		// Got Error
		if($got_error){
			return;
		}
		
		// loop date
		for($d1 = strtotime($this->date_from), $d2 = strtotime($this->date_to); $d1 <= $d2; $d1+=86400){
			$this->generate_sales_navision_file_by_date($b, date("Y-m-d", $d1));
		}
	}
	
	private function generate_sales_navision_file_by_date($b, $date){
		global $con;
		
		$bid = mi($b['id']);
		print "Generating sales file for Navision, Branch ID:$bid, Date: $date\n";
		
		//$settings = $this->configuration[$this->bcode];
		$folder_path = $this->get_sales_folder_path('navision', $date);
		$uploaded_folder_path = $this->get_uploaded_sales_folder_path('navision', $date);
		$filename = $this->get_sales_filename('navision', $b, $date);
		$sp9_outlet_code = $this->Speed99->get_outlet_code($b);
		$converted_date_value = $this->Speed99->invoice_date_conversion($date);
		$invoice_no_prefix = $this->Speed99->get_invoice_no_prefix($bid);
		
		if(!$this->regen_sales){	// no regenerate, check whether file exists
			if(file_exists($uploaded_folder_path."/".$filename) && filesize($uploaded_folder_path."/".$filename) > 0){
				print " - File Uploaded\n";
				return;
			}
		}
		
		// Day End Checking
		$ret = $this->check_branch_can_send_sales($bid, $date);
		if($ret['error']){
			print "There was a problem while checking day end.\n";
			print_r($ret['error']);
			foreach($ret['error'] as $e){
				$this->error_list[] = $e;
			}
			return;
		}
		
		// Check Finalise
		/*$con->sql_query("select * from pos_finalized where branch_id=$this->bid and finalized=1");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$tmp){
			print " - Not Finalised Yet.\n";
			return;
		}*/
		
		// delete the file
		if(file_exists($folder_path."/".$filename)) unlink($folder_path."/".$filename);
		
		// Create file
		$fp = fopen($folder_path."/".$filename, 'w');
		$dmy_date = date("d/m/Y", strtotime($date));
		
		$sql = "select pi.sku_item_id, si.link_code, sum(pi.qty) as qty, sum(pi.price-pi.discount-pi.discount2) as amt
			from pos p
			join pos_items pi on pi.branch_id=p.branch_id and pi.date=p.date and pi.counter_id=p.counter_id and pi.pos_id=p.id
			left join sku_items si on si.id=pi.sku_item_id
			where p.branch_id=$bid and p.date=".ms($date)." and p.cancel_status=0
			group by pi.sku_item_id
			order by si.link_code";
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			// Receipt Header
			$data = array();
			$data[] = $sp9_outlet_code;
			$data[] = $dmy_date;
			$data[] = $r['link_code'];
			$data[] = $r['qty'];
			$data[] = round($r['amt'], 2);
			fputcsv_eol($fp, $data);
		}
		$con->sql_freeresult($q1);
		
		fclose($fp);
		chmod($folder_path."/".$filename,0777);
		print " - Done. ".$folder_path."/".$filename."\n";
		
		// Upload
		$full_filepath = $folder_path."/".$filename;
		
		// Create Folder
		$remote_folder = 'Shared';
		ftp_mkdir($this->sales_navision_ftp_conn_id, $remote_folder);
		
		$remote_folder .= '/Daily';
		ftp_mkdir($this->sales_navision_ftp_conn_id, $remote_folder);

		// Year
		$remote_folder .= "/".date("Y", strtotime($date));
		ftp_mkdir($this->sales_navision_ftp_conn_id, $remote_folder);
		
		// Month
		$remote_folder .= "/".date("m", strtotime($date));
		ftp_mkdir($this->sales_navision_ftp_conn_id, $remote_folder);
		
		// Day
		$remote_folder .= "/".date("d", strtotime($date));
		ftp_mkdir($this->sales_navision_ftp_conn_id, $remote_folder);
		
		$remote_filename = $remote_folder."/".$filename;
		print " - Uploading... ";
		
		if (ftp_put($this->sales_navision_ftp_conn_id, $remote_filename, $full_filepath, FTP_BINARY)) {
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
	
	private function check_branch_can_send_sales($bid, $date){
		global $con, $appCore;
		
		$today_date_ymd = date("Ymd");
		$date_ymd = date("Ymd", strtotime($date));
		if($today_date_ymd - $date_ymd < 2){
			$ret = array();
			$ret['error'] = array();
			
			// Less than 2 days, need to check day end
			/*$q1 = $con->sql_query("select distinct(counter_id) from pos where branch_id=$bid and date=".ms($date));
			while($r = $con->sql_fetchassoc($q1)){
				$counter_id = mi($r['counter_id']);
				
				// Get Day End Record
				$con->sql_query("select * from pos_day_end where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id");
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$tmp){
					$counter_info = $appCore->posManager->getCounter($bid, $counter_id);
					$ret['error'][] = "Branch: ".$this->b_list[$bid]['code'].", Counter: ".$counter_info['network_name'].", required Day End.";
				}
			}
			$con->sql_freeresult($q1);*/
			$rs = $con->sql_query("select * from counter_settings where branch_id = $bid and counter_pb_mode = 1");
			$r = $con->sql_fetchassoc($rs);
			$con->sql_freeresult($rs);
			$counter_id = mi($r['id']);

			// Get Day End Record
			if($counter_id){
				$con->sql_query("select * from pos_day_end where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id");
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$tmp){
					$counter_info = $appCore->posManager->getCounter($bid, $counter_id);
					$ret['error'][] = "Branch: ".$this->b_list[$bid]['code'].", Counter: ".$counter_info['network_name'].", Date: $date required Day End.";
				}
			}else{
				$ret['error'][] = "Branch: ".$this->b_list[$bid]['code'].", No POS Backend Counter is set.";
			}
			

			// Got Error
			if($ret['error'])	return $ret;
		}
		
		// Already more than 2 days, no need check day end
		return array('ok'=>1);
	}
	
	private function sync_warehouse(){
		global $con;
		
		print "=============== Sync Warehouse ===============\n";
		
		// Warehouse
		$local_filename = $this->attch_file_folder_others.'/warehouse.csv';
		if(!file_exists($local_filename)){
			print $local_filename." not exists.\n";
			exit;
		}
		
		// Start MySQL Transaction
		$con->sql_begin_transaction();
		
		// Open File
		$f = fopen($local_filename, "rt");
		// skip header
		$header = fgetcsv($f);
		
		$total_record = 0;
		$new_record = 0;
		$update_record = 0;
		$error_record = 0;
		$error_list = array();
		while($r = fgetcsv($f)){
			$total_record++;
			
			//print "$total_record) ".$line;
			print "\rChecking line $total_record . . ";
			$need_update = false;
			$is_new = false;
			//print_r($r);
			
			$warehouse_number = trim($r[0]);
			$warehouse_name = trim($r[1]);
			
			if(!$warehouse_number){
				print "No Warehouse Number.\n";
				$error_record++;
				continue;
			}
			
			if(!$warehouse_name){
				print "No Warehouse Name.\n";
				$error_record++;
				continue;
			}
			
			$con->sql_query("select * from speed99_warehouse where warehouse_number=".ms($warehouse_number));
			$curr_data = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$curr_data){
				// New Record
				$need_update = true;
				$is_new = true;
			}else{
				// Existing Record
				if($curr_data['warehouse_name'] != $warehouse_name){
					$need_update = true;
				}
			}
			
			if($need_update){
				if($is_new){
					$ins = array();
					$ins['warehouse_number'] = $warehouse_number;
					$ins['warehouse_name'] = $warehouse_name;
					$ins['added'] = $ins['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("insert into speed99_warehouse ".mysql_insert_by_field($ins));
					$new_record++;
				}else{
					$upd = array();
					$upd['warehouse_name'] = $warehouse_name;
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("update speed99_warehouse set ".mysql_update_by_field($upd)." where warehouse_number=".ms($warehouse_number));
					$update_record++;
				}
				
			}
		}
		
		// Commit MySQL Changes
		$con->sql_commit();
		
		print "\n";
		print "Total Record: $total_record\n";
		print "New Record: $new_record\n";
		print "Update Record: $update_record\n";
		print "Error Record: $error_record\n";
		print "Done.\n";
	}
	
	private function sync_branch(){
		global $con;
		
		print "=============== Sync Branch ===============\n";
		
		// Branch
		$local_filename = $this->attch_file_folder_others.'/branch.csv';
		if(!file_exists($local_filename)){
			print $local_filename." not exists.\n";
			exit;
		}
		
		// Start MySQL Transaction
		$con->sql_begin_transaction();
		
		// Open File
		$f = fopen($local_filename, "rt");
		// skip header
		$header = fgetcsv($f);
		
		$total_record = 0;
		$new_record = 0;
		$update_record = 0;
		$error_record = 0;
		$error_list = array();
		while($r = fgetcsv($f)){
			$total_record++;
			
			//print "$total_record) ".$line;
			print "\rChecking line $total_record . . ";
			$need_update = false;
			$is_new = false;
			//print_r($r);
			
			$sp9_outlet_code = trim($r[0]);
			$branch_desc = trim($r[1]);
			$warehouse_number = trim($r[2]);
			
			if(!$sp9_outlet_code){
				print "No Outlet Code.\n";
				$error_record++;
				continue;
			}
			
			if(!$branch_desc){
				print "No Branch Description.\n";
				$error_record++;
				continue;
			}
			
			if(!$warehouse_number){
				print "No Warehouse Number.\n";
				$error_record++;
				continue;
			}
			
			// Get from branch mapping
			$con->sql_query("select * from speed99_branch_mapping where outlet_code=".ms($sp9_outlet_code));
			$b_map = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$branch_info = array();
			
			if(!$b_map){
				// Check whether got same branch code
				$con->sql_query("select * from branch where code=".ms($sp9_outlet_code));
				$branch_info = $con->sql_fetchassoc();
				$con->sql_freeresult();
			}else{
				// Get branch according to mapping
				$con->sql_query("select * from branch where id=".ms($b_map['branch_id']));
				$branch_info = $con->sql_fetchassoc();
				$con->sql_freeresult();
			}
			
			if($branch_info){
				// Existing Record
				//if($branch_info['description'] != $branch_desc || $branch_info['warehouse_number'] != $warehouse_number){
					$need_update = true;
				//}
			}else{
				// New Record
				$need_update = true;
				$is_new = true;
			}
			
			// Get Warehouse info
			$warehouse_info = $this->Speed99->getWarehouseByNumber($warehouse_number);
			if(!$warehouse_info){
				print "Warehouse Record not found.\n";
				$error_record++;
				continue;
			}
			
			if($need_update){
				if($is_new){
					// Add New Branch
					$ins = array();
					$ins['code'] = $sp9_outlet_code;
					$ins['report_prefix'] = $sp9_outlet_code;
					$ins['description'] = $branch_desc;
					$ins['warehouse_number'] = $warehouse_number;
					$ins['warehouse_name'] = $warehouse_info['warehouse_name'];
					$ins['added'] = 'CURRENT_TIMESTAMP';
					$ins['active'] = 1;
					$con->sql_query("insert into branch ".mysql_insert_by_field($ins));
					$bid = $con->sql_nextid();  // get the new branch id
					
					// create branch status row
					$branch_status = array();
					$branch_status['branch_id'] = $bid;
					$branch_status['lastping'] = '';
					$con->sql_query("insert into branch_status ".mysql_insert_by_field($branch_status));
					
					// Log
					log_br(-1, 'MASTERFILE', 0, 'Branch create ' . $sp9_outlet_code);  // record log
					
					// create all cache table
					update_sales_cache($bid, -1);    

					$new_record++;
				}else{
					// Update Existing Branch
					$bid = $branch_info['id'];
					
					$upd = array();
					//$upd['report_prefix'] = $sp9_outlet_code;
					$upd['description'] = $branch_desc;
					$upd['warehouse_number'] = $warehouse_number;
					$upd['warehouse_name'] = $warehouse_info['warehouse_name'];
					$con->sql_query("update branch set ".mysql_update_by_field($upd)." where id=".mi($bid));
					$update_record++;
				}
				
				// Speed99 Branch Mapping
				$ins = array();
				$ins['branch_id'] = $bid;
				$ins['outlet_code'] = $sp9_outlet_code;
				$con->sql_query("replace into speed99_branch_mapping ".mysql_insert_by_field($ins));
			}
		}
		
		// Commit MySQL Changes
		$con->sql_commit();
		
		print "\n";
		print "Total Record: $total_record\n";
		print "New Record: $new_record\n";
		print "Update Record: $update_record\n";
		print "Error Record: $error_record\n";
		print "Done.\n";
	}

	private function sync_vbal(){
		global $con;
		
		print "=============== Sync View Balance ===============\n";
		
		// Connect View Balance FTP Server
		$this->connect_vbal_ftp();
		
		print ">>>>> Sync View Balance ...\n";
		// Mark Sync Status = Start
		$this->set_cron_status('eod', 'pos_vbal' , 'start');
		$this->error_list = array();
		
		// Generate Sales File
		foreach($this->b_list as $b){
			$this->generate_vbal_file($b);
			print "\n";
		}
		
		$params['error_list'] = $this->error_list;
		// Mark Sync Status = End
		$this->set_cron_status('eod', 'pos_vbal' , 'end', $params);
		
		// close the connection
		if($this->vbal_ftp_conn_id)	ftp_close($this->vbal_ftp_conn_id);
		print "Done.\n";
	}

	private function connect_vbal_ftp(){
		global $config;
		
		print "Connecting to Speed99 View Balance FTP Server. . . ";

		// set up basic connection
		$this->vbal_ftp_conn_id = ftp_connect($this->Speed99->vbal_server_ftp_info['ip'], $this->Speed99->vbal_server_ftp_info['port']);
		if(!$this->vbal_ftp_conn_id){
			print "Server Cannot be connect.\n";
			exit;
		}
		print "Connected.\n";
		print "Login to the server. . . ";

		// login with username and password
		$login_result = ftp_login($this->vbal_ftp_conn_id, $this->Speed99->vbal_server_ftp_info['username'], $this->Speed99->vbal_server_ftp_info['pass']);
		if(!$login_result){
			print "Failed to login to the Server.\n";
			ftp_close($this->vbal_ftp_conn_id);
			exit;
		}
		print "Success.\n";
		
		// Turn on Passive Mode
        ftp_pasv($this->vbal_ftp_conn_id, true);
	}

	private function generate_vbal_file($b){
		print "Generate View Balance Files for ".$b['code']."...\n";
		
		$got_error = false;
		
		// Check Speed99 Outlet Code
		$sp9_outlet_code = $this->Speed99->get_outlet_code($b);
		if(!$sp9_outlet_code){
			$err_msg = "Branch (".$b['code'].") No Speed99 Outlet Code!";
			print $err_msg."\n";
			$this->error_list[] = $err_msg;
			$got_error = true;
		}else{
			print "Speed99 Outlet Code: $sp9_outlet_code\n";
		}
		
		// Got Error
		if($got_error){
			return;
		}
		
		// loop date
		for($d1 = strtotime($this->date_from), $d2 = strtotime($this->date_to); $d1 <= $d2; $d1+=86400){
			$this->generate_vbal_file_by_date($b, date("Y-m-d", $d1));
		}
	}

	private function generate_vbal_file_by_date($b,$date){
		global $con;
		
		$bid = mi($b['id']);
		print "Generating vbal file, Branch ID:$bid, Date: $date\n";
		
		$folder_path = $this->get_eod_folder_path('vbal', $bid, $date);
		$uploaded_folder_path = $this->get_uploaded_eod_folder_path('vbal', $bid, $date);
		$filename = $this->get_eod_filename('vbal', $b, $date);
		$sp9_outlet_code = $this->Speed99->get_outlet_code($b);
		
		if(!$this->regen_sales){	// no regenerate, check whether file exists
			if(file_exists($uploaded_folder_path."/".$filename) && filesize($uploaded_folder_path."/".$filename) > 0){
				print " - File Uploaded\n";
				return;
			}
		}
		
		// Day End Checking
		$ret = $this->get_eod_info($bid, $date);
		if($ret['error']){
			print "There was a problem while checking day end.\n";
			print_r($ret['error']);
			foreach($ret['error'] as $e){
				$this->error_list[] = $e;
			}
			return;
		}

		$eod_data = unserialize($ret['eod_data']);
		
		// delete the file
		if(file_exists($folder_path."/".$filename)) unlink($folder_path."/".$filename);
		
		// Create file
		$fp = fopen($folder_path."/".$filename, 'w');
		
		$sales_data = $this->get_xz_report_data($bid, $date);

		$str = array();
		$str[] = $sp9_outlet_code;
		$str[] = date('d/m/Y',strtotime($date));
		$str[] = round($eod_data['ccpublic_bank'], 2);
		$str[] = round($eod_data['dcpublic_bank'], 2);
		$str[] = round($eod_data['exmanual_greturn'], 2);
		$str[] = round($eod_data['excoin'], 2);
		$str[] = round($eod_data['exexpenses'], 2);
		$str[] = round($eod_data['exother'], 2);
		$str[] = '';//Speed Point
		$str[] = round($eod_data['spvoid'], 2);

		$str[] = date('H:i:s',strtotime($eod['time']));
		$str[] = round($sales_data['sales_summary']['balance'], 2);
		$str[] = round($sales_data['sales_summary']['total_reject'], 2);
		$str[] = round($sales_data['payment']['Cash'], 2);
		$str[] = round($sales_data['payment']['Credit Card'], 2);
		$str[] = round($sales_data['sales_by_tax']['Rounding']['amt'], 2);
		$str[] = ''; //Other??
		$str[] = ''; //Blank

		$str[] = $sales_data['total_transaction'];
		$str[] = round($sales_data['sales_summary']['total_gst'], 2);
		$str[] = round($sales_data['sales_summary']['total_reject_gst'], 2);
		$str[] = round($sales_data['goods_return']['total_amount'], 2);
		$str[] = ''; //Woopit
		$str[] = ''; //Speed Pay
		$str[] = ''; //Google Pay
		$str[] = '===';

		fputcsv_eol($fp, $str);
		fclose($fp);
		chmod($folder_path."/".$filename,0777);
		print " - Done. ".$folder_path."/".$filename."\n";
		
		// Upload
		$full_filepath = $folder_path."/".$filename;
		
		// Create Folder
		$remote_folder = 'Shared';
		ftp_mkdir($this->vbal_ftp_conn_id, $remote_folder);
		
		$remote_folder .= '/vbal';
		ftp_mkdir($this->vbal_ftp_conn_id, $remote_folder);
		
		// Year
		$remote_folder .= "/".date("Y", strtotime($date));
		ftp_mkdir($this->vbal_ftp_conn_id, $remote_folder);
		
		// Month
		$remote_folder .= "/".date("m", strtotime($date));
		ftp_mkdir($this->vbal_ftp_conn_id, $remote_folder);
		
		// Day
		$remote_folder .= "/".date("d", strtotime($date));
		ftp_mkdir($this->vbal_ftp_conn_id, $remote_folder);
		
		$remote_filename = $remote_folder."/".$filename;
		print " - Uploading... ";
		
		if (ftp_put($this->vbal_ftp_conn_id, $remote_filename, $full_filepath, FTP_BINARY)) {
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

	private function get_eod_folder_path($eod_type, $bid, $date){
		// check & create folder by branch
		$folder_path = $this->attch_file_folder_eod;
		
		$year = date('Y',strtotime($date));
		$month = date('m',strtotime($date));
		$day = date('d',strtotime($date));
		
		// check & create folder by eod type
		$folder_path = $folder_path."/".$eod_type;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);

		// check & create folder by branch
		$folder_path = $folder_path."/".$bid;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);

		// check & create folder by year
		$folder_path = $folder_path."/".$year;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		// check & create folder by month
		$folder_path = $folder_path."/".$month;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		// check & create folder by day
		$folder_path = $folder_path."/".$day;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		return $folder_path;
	}

	private function get_uploaded_eod_folder_path($eod_type, $bid, $date){
		$folder_path = $this->get_eod_folder_path($eod_type, $bid, $date);
		
		// check & create uploaded folder
		$uploaded_folder_path = $folder_path."/uploaded";
		if(!is_dir($uploaded_folder_path)) check_and_create_dir($uploaded_folder_path);
		
		return $uploaded_folder_path;
	}

	private function get_eod_filename($eod_type, $b, $date){
		$bid = mi($b['id']);

		$copy_file = 0;
		switch($eod_type){
			case 'vbal':
				$prefix = 'V';
				$warehouse_number = trim($b['warehouse_number']);
				break;
			case 'staff':
				$prefix = 'S';
				$warehouse_number = '';
				break;
			case 'densotrn':
				$copy_file = 1;
				$y = date('Y',strtotime($date));
				$m = date('m',strtotime($date));
				$d = date('d',strtotime($date));
				$folder = "attch/speed99/eod/densotrn/$bid/$y/$m/$d";
				$filename = glob($folder.'/*');
				break;
			default:
				$prefix = '';
				$warehouse_number = '';
				break;
		}

		if($copy_file){
			return $filename;
		}

		$converted_date_value = $this->Speed99->invoice_date_conversion($date);
		$sp9_outlet_code = $this->Speed99->get_outlet_code($b);
		$sp9_outlet_code = $this->Speed99->trim_outlet_code($sp9_outlet_code);

		$point_after_digit = $this->Speed99->get_point_after_digit();

		$tmp = $prefix.$warehouse_number.$converted_date_value.$sp9_outlet_code;
		$fpart1 = substr($tmp,0,$point_after_digit);
		$fpart2 = substr($tmp,$point_after_digit);
		$filename = $fpart1.'.'.$fpart2;
		print "Filename = $filename\n";
		return $filename;
	}

	function get_eod_info($bid, $date){
		global $con, $appCore;
		
		$bid = mi($bid);
		$ret = array();
		$ret['error'] = array();

		$rs = $con->sql_query("select * from counter_settings where branch_id = $bid and counter_pb_mode = 1");
		$r = $con->sql_fetchassoc($rs);
		$con->sql_freeresult($rs);
		$counter_id = mi($r['id']);

		// Get Day End Record
		if($counter_id){
			$con->sql_query("select * from pos_day_end where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$tmp){
				$counter_info = $appCore->posManager->getCounter($bid, $counter_id);
				$ret['error'][] = "Branch: ".$this->b_list[$bid]['code'].", Counter: ".$counter_info['network_name'].", Date: $date required Day End.";
			}
		}else{
			$ret['error'][] = "Branch: ".$this->b_list[$bid]['code'].", Date: $date No POS Backend Counter is set.";
		}
		

		// Got Error
		if($ret['error'])	return $ret;
		
		//Return eod info
		return $tmp;
	}

	private function get_xz_report_data($bid, $date){
		global $con;
		$data = array();

		$cond = 'branch_id = '.$bid.' and date = '.ms($date);
		$cond2 = 'p.branch_id = '.$bid.' and p.date = '.ms($date);

		//Sales Summary
		$sql = "select amount, total_gst_amt, cancel_status, prune_status, cashier_id from pos where $cond";
		$rs = $con->sql_query($sql);
		$total_sales = 0;
		$total_sales_gst = 0;
		$total_reject = 0;
		$total_reject_gst = 0;
		$total_transaction = 0;
		
		while($r = $con->sql_fetchassoc($rs)){
			$total_sales += $r['amount'];
			$total_sales_gst += $r['total_gst_amt'];
			if($r['cancel_status'] || $r['prune_status']){
				$total_reject += $r['amount'];
				$total_reject_gst += $r['total_gst_amt'];
			}else{
				//Cashier Collection Info
				if(isset($data['collection'][$r['cashier_id']])){
					$data['collection'][$r['cashier_id']] += $r['amount'] + $r['total_gst_amt'];
				}else{
					$data['collection'][$r['cashier_id']] = $r['amount'] + $r['total_gst_amt'];
				}
			}
			//Include Good Returns and cancelled
			$total_transaction++;
		}
		$con->sql_freeresult($rs);
		$balance = $total_sales + $total_gst - $total_reject - $total_reject_gst;
		$data['sales_summary']['total_sales'] = $total_sales;
		$data['sales_summary']['total_sales_gst'] = $total_sales_gst;
		$data['sales_summary']['total_reject'] = $total_reject;
		$data['sales_summary']['total_reject_gst'] = $total_reject_gst;
		$data['sales_summary']['balance'] = $balance;
		$data['total_transaction'] = $total_transaction;

		//Sales Detail (By Tax)
		$sales_by_tax = array();
		$sql = "select i.sku_item_id,i.qty,i.tax_indicator,i.tax_rate,i.before_tax_price,i.tax_amount,si.link_code from pos_items i join pos p on(i.pos_id = p.id) join sku_items si on (i.sku_item_id = si.id) where p.cancel_status = 0 and p.prune_status = 0 and $cond2";
		$rs = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($rs)){
			if($r['tax_indicator']){
				$tax_indicator = $r['tax_indicator'];
			}else{
				$tax_indicator = 'no_tax_code';
			}
			if(isset($sales_by_tax[$tax_indicator])){
				$sales_by_tax[$tax_indicator]['sales'] += $r['before_tax_price'];
				$sales_by_tax[$tax_indicator]['tax_amt'] += $r['tax_amount'];
				$sales_by_tax[$tax_indicator]['amt'] += $r['before_tax_price'] + $r['tax_amount'];
			}else{
				$sales_by_tax[$tax_indicator]['tax_rate'] = $r['tax_rate'];
				$sales_by_tax[$tax_indicator]['sales'] = $r['before_tax_price'];
				$sales_by_tax[$tax_indicator]['tax_amt'] = $r['tax_amount'];
				$sales_by_tax[$tax_indicator]['amt'] = $r['before_tax_price'] + $r['tax_amount'];
			}

			//Get total by sku_item_id, for Category Sales purpose
			if(isset($total_by_sku[$r['sku_item_id']])){
				$total_by_sku[$r['sku_item_id']]['amt'] += $r['before_tax_price'] + $r['tax_amount'];
				$total_by_sku[$r['sku_item_id']]['qty'] += $r['qty'];
			}else{
				$total_by_sku[$r['sku_item_id']]['amt'] = $r['before_tax_price'] + $r['tax_amount'];
				$total_by_sku[$r['sku_item_id']]['qty'] += $r['qty'];
				$total_by_sku[$r['sku_item_id']]['link_code'] = $r['link_code'];
			}
		}
		//Roundings
		$sql = "select sum(amount) as amount from pos_payment where $cond and type = ".ms('Rounding');
		$rs = $con->sql_query($sql);
		$r = $con->sql_fetchassoc($rs);
		$sales_by_tax['Rounding']['sales'] = $r['amount'];
		$sales_by_tax['Rounding']['tax_amt'] = 0;
		$sales_by_tax['Rounding']['amt'] = $r['amount'];
		$data['sales_by_tax'] = $sales_by_tax;

		$payment_type = array();
		//Get ewallet type
		$rs = $con->sql_query("select * from pos_settings where branch_id = $bid and setting_name = ".ms('ewallet_type'));
		$r = $con->sql_fetchassoc($rs);
		$con->sql_freeresult($rs);
		$arewallet_type = unserialize($r['setting_value']);
		if($arewallet_type){
			foreach($arewallet_type as $ewallet_type => $val){
				if($val != 1) continue;
				$tmp_ewallet_type = "ewallet_".$ewallet_type;
				$payment_type['ewallet'][$tmp_ewallet_type] = 1;
			}
		}
		
		//Get payment type
		$rs = $con->sql_query("select * from pos_settings where branch_id = $bid and setting_name = ".ms('payment_type'));
		$r = $con->sql_fetchassoc($rs);
		$con->sql_freeresult($rs);
		$arpayment_type = unserialize($r['setting_value']);	
		if($arpayment_type){
			foreach($arpayment_type as $type => $val){
				if($val != 1) continue;
				if($type=='credit_card'){
					foreach($config['issuer_identifier_id'] as $cc_type){
						$payment_type['cc'][$cc_type] = 1;
					}
				}else{
					$type = ucfirst($this->change_interface_wording($type));
					$payment_type['other'][$type] = 1;
				}
			}
		}
		
		$payment_type['other']['Discount'] = 1;
		$payment_type['other']['Cash'] = 1;

		if($payment_type){
			foreach($payment_type as $group => $arr){
				foreach($arr as $type => $dummy){
					$sql = "select sum(amount) as amount from pos_payment where $cond and type=".ms($type);
					$rs = $con->sql_query($sql);
					$r = $con->sql_fetchassoc($rs);
					$con->sql_freeresult($rs);
					if($group == 'other'){
						$ptype = $type;
					}elseif($group == 'cc'){
						$ptype = 'Credit Card';
					}elseif($group == 'ewallet'){
						$ptype = str_replace('ewallet_','',$type);
						$ptype = str_replace('_',' ',$ptype);
					}

					if($r['amount']){
						if(isset($payment[$ptype])){
							$payment[$ptype] += $r['amount'];
						}else{
							$payment[$ptype] = $r['amount'];
						}
					}
				}
			}
		}
		$data['payment'] = $payment;

		//Goods Return
		$tmp_counter = array();
		$sql = "select sum(p.amount) as total_amount from pos_goods_return r join pos p on (r.pos_id = p.id and r.branch_id = p.branch_id and r.counter_id = p.counter_id) where p.cancel_status = 0 and $cond2";
		$rs = $con->sql_query($sql);
		$r = $con->sql_fetchassoc();
		$data['goods_return']['total_amount'] = $r['total_amount'];

		//Category Sales
		$category_sales = array();
		if(file_exists('category.txt') && isset($total_by_sku)){
			$cat_list = file_get_contents('category.txt');
			$cat_list = explode("\n",$cat_list);
			unset($cat_list[0]);
			foreach($total_by_sku as $sku_item_id => $item){
				foreach($cat_list as $arr){
					$cl = explode(',',$arr);
					$sku = trim($cl[3]);
					$desc = trim($cl[2]);
					$cat_code = trim($cl[1]);
					$group = trim($cl[0]);
					$sku_len = strlen($sku);

					if(substr($item['link_code'],0,$sku_len) == $sku){
						if(isset($category_sales[$group][$cat_code])){
							$category_sales[$group][$cat_code]['qty'] += $item['qty'];
							$category_sales[$group][$cat_code]['amt'] += $item['amt'];
						}else{
							$category_sales[$group][$cat_code]['desc'] = $desc;
							$category_sales[$group][$cat_code]['qty'] = $item['qty'];
							$category_sales[$group][$cat_code]['amt'] = $item['amt'];
						}
						break;
					}
				}
			}
		}
		$data['category_sales'] = $category_sales;

		//Cash Voucher List
		$voucher_list = array();
		$sql = "select amount,remark from pos_payment where $cond and type=".ms('Voucher');
		$rs = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($rs)){
			$voucher_list[] = array('remark'=>$r['remark'],'amt'=>$r['amount']);
		}
		$con->sql_freeresult($rs);
		$data['voucher_list'] = $voucher_list;

		//eWallet List
		$ewallet_list = array();
		if(isset($payment_type['ewallet'])){
			foreach($payment_type['ewallet'] as $type => $dummy){
				$ptype = str_replace('ewallet_','',$type);
				$ptype = str_replace('_',' ',$ptype);
				$sql = "select amount,remark from pos_payment where $cond and type=".ms($type);
				$rs = $con->sql_query($sql);
				while($r = $con->sql_fetchassoc($rs)){
					$ewallet_list[$ptype][] = array('remark'=>$r['remark'],'amt'=>$r['amount']);
					if(isset($data['ewallet_total_amount'])){
						$data['ewallet_total_amount'] += $r['amount'];
					}else{
						$data['ewallet_total_amount'] = $r['amount'];
					}
				}
				$con->sql_freeresult($rs);
			}
		}
		$data['ewallet_list'] = $ewallet_list;

		return $data;
	}

	private function sync_staff(){
		global $con;
		
		print "=============== Sync Staff Attendance ===============\n";
		
		// Connect View Balance FTP Server
		$this->connect_staff_ftp();
		
		print ">>>>> Sync Staff Attendance ...\n";
		// Mark Sync Status = Start
		$this->set_cron_status('eod', 'pos_staff' , 'start');
		$this->error_list = array();
		
		// Generate Sales File
		foreach($this->b_list as $b){
			$this->generate_staff_file($b);
			print "\n";
		}
		
		$params['error_list'] = $this->error_list;
		// Mark Sync Status = End
		$this->set_cron_status('eod', 'pos_staff' , 'end', $params);
		
		// close the connection
		if($this->staff_ftp_conn_id)	ftp_close($this->staff_ftp_conn_id);
		print "Done.\n";
	}

	private function generate_staff_file($b){
		print "Generate Staff Files for ".$b['code']."...\n";
		
		$got_error = false;
		
		// Check Speed99 Outlet Code
		$sp9_outlet_code = $this->Speed99->get_outlet_code($b);
		if(!$sp9_outlet_code){
			$err_msg = "Branch (".$b['code'].") No Speed99 Outlet Code!";
			print $err_msg."\n";
			$this->error_list[] = $err_msg;
			$got_error = true;
		}else{
			print "Speed99 Outlet Code: $sp9_outlet_code\n";
		}
		
		// Got Error
		if($got_error){
			return;
		}
		
		// loop date
		for($d1 = strtotime($this->date_from), $d2 = strtotime($this->date_to); $d1 <= $d2; $d1+=86400){
			$this->generate_staff_file_by_date($b, date("Y-m-d", $d1));
		}
	}

	private function generate_staff_file_by_date($b,$date){
		global $con, $appCore;
		
		$bid = mi($b['id']);
		print "Generating staff file, Branch ID:$bid, Date: $date\n";
		
		$folder_path = $this->get_eod_folder_path('staff', $bid, $date);
		$uploaded_folder_path = $this->get_uploaded_eod_folder_path('staff', $bid, $date);
		$filename = $this->get_eod_filename('staff', $b, $date);
		$sp9_outlet_code = $this->Speed99->get_outlet_code($b);
		
		if(!$this->regen_sales){	// no regenerate, check whether file exists
			if(file_exists($uploaded_folder_path."/".$filename) && filesize($uploaded_folder_path."/".$filename) > 0){
				print " - File Uploaded\n";
				return;
			}
		}
		
		// Day End Checking
		$ret = $this->check_branch_can_send_sales($bid, $date);
		if($ret['error']){
			print "There was a problem while checking day end.\n";
			print_r($ret['error']);
			foreach($ret['error'] as $e){
				$this->error_list[] = $e;
			}
			return;
		}
		
		// delete the file
		if(file_exists($folder_path."/".$filename)) unlink($folder_path."/".$filename);
		
		// Create file
		$fp = fopen($folder_path."/".$filename, 'w');
		
		$attendance = $this->get_staff_attendance($bid, $date);

		if(is_array($attendance) && $attendance){
			foreach($attendance as $cashier_id => $arr){
				$user = $appCore->userManager->getUser($cashier_id);
				$login_barcode = $user['barcode'];
				foreach($arr as $datetime){
					$str = array();
					$adate = date('d/m/Y',strtotime($datetime));
					$atime = date('h:i:s',strtotime($datetime));
					$str[] = $adate;
					$str[] = $atime;
					$str[] = $login_barcode;
					fputcsv_eol($fp, $str);
				}
			}
		}
		fclose($fp);
		chmod($folder_path."/".$filename,0777);
		print " - Done. ".$folder_path."/".$filename."\n";
		
		// Upload
		$full_filepath = $folder_path."/".$filename;
		
		// Create Folder
		$remote_folder = 'Shared';
		ftp_mkdir($this->staff_ftp_conn_id, $remote_folder);
		
		$remote_folder .= '/staff';
		ftp_mkdir($this->staff_ftp_conn_id, $remote_folder);
		
		// Year
		$remote_folder .= "/".date("Y", strtotime($date));
		ftp_mkdir($this->staff_ftp_conn_id, $remote_folder);
		
		// Month
		$remote_folder .= "/".date("m", strtotime($date));
		ftp_mkdir($this->staff_ftp_conn_id, $remote_folder);
		
		// Day
		$remote_folder .= "/".date("d", strtotime($date));
		ftp_mkdir($this->staff_ftp_conn_id, $remote_folder);
		
		$remote_filename = $remote_folder."/".$filename;
		print " - Uploading... ";
		
		if (ftp_put($this->staff_ftp_conn_id, $remote_filename, $full_filepath, FTP_BINARY)) {
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

	private function get_staff_attendance($bid, $date){
		global $con;
		$bid = mi($bid);

		$ret = array();
		$sql = "select user_id, scan_time from attendance_user_scan_record where branch_id = $bid and date = ".ms($date);
		$rs = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($rs)){
			$ret[$r['user_id']][] = $r['scan_time'];
		}
		$con->sql_freeresult($rs);

		return $ret;
	}

	private function connect_staff_ftp(){
		global $config;
		
		print "Connecting to Speed99 Staff FTP Server. . . ";

		// set up basic connection
		$this->staff_ftp_conn_id = ftp_connect($this->Speed99->staff_server_ftp_info['ip'], $this->Speed99->staff_server_ftp_info['port']);
		if(!$this->staff_ftp_conn_id){
			print "Server Cannot be connect.\n";
			exit;
		}
		print "Connected.\n";
		print "Login to the server. . . ";

		// login with username and password
		$login_result = ftp_login($this->staff_ftp_conn_id, $this->Speed99->staff_server_ftp_info['username'], $this->Speed99->staff_server_ftp_info['pass']);
		if(!$login_result){
			print "Failed to login to the Server.\n";
			ftp_close($this->staff_ftp_conn_id);
			exit;
		}
		print "Success.\n";
		
		// Turn on Passive Mode
        ftp_pasv($this->staff_ftp_conn_id, true);
	}

	function change_interface_wording(&$type)
	{
		$orgtype = $type;
		switch(strtolower($orgtype))
		{
			case 'check':
				$type = "Cheque";
				break;	
			case 'receipt':
				$type = "Invoice";
				break;
			case 'currency_adjust':
				$type = "Currency Adjust";
				break;
		}
		
		if(preg_match("/^ewallet_/i", $orgtype)) $type = ucwords(str_replace("ewallet_", "", $orgtype));
		
		return $type;
	}

	private function sync_densotrn(){
		global $con;
		
		print "=============== Sync Denso Transaction ===============\n";
		
		// Connect View Balance FTP Server
		$this->connect_denso_ftp();
		
		print ">>>>> Sync Denso Transaction  ...\n";
		// Mark Sync Status = Start
		$this->set_cron_status('eod', 'pos_densotrn' , 'start');
		$this->error_list = array();
		
		// Generate Sales File
		foreach($this->b_list as $b){
			$this->generate_densotrn_file($b);
			print "\n";
		}
		
		$params['error_list'] = $this->error_list;
		// Mark Sync Status = End
		$this->set_cron_status('eod', 'pos_densotrn' , 'end', $params);
		
		// close the connection
		if($this->denso_ftp_conn_id)	ftp_close($this->denso_ftp_conn_id);
		print "Done.\n";
	}

	private function generate_densotrn_file($b){
		print "Generate Denso Transaction Files for ".$b['code']."...\n";
		
		$got_error = false;
		
		// Check Speed99 Outlet Code
		$sp9_outlet_code = $this->Speed99->get_outlet_code($b);
		if(!$sp9_outlet_code){
			$err_msg = "Branch (".$b['code'].") No Speed99 Outlet Code!";
			print $err_msg."\n";
			$this->error_list[] = $err_msg;
			$got_error = true;
		}else{
			print "Speed99 Outlet Code: $sp9_outlet_code\n";
		}
		
		// Got Error
		if($got_error){
			return;
		}
		
		// loop date
		for($d1 = strtotime($this->date_from), $d2 = strtotime($this->date_to); $d1 <= $d2; $d1+=86400){
			$this->generate_densotrn_file_by_date($b, date("Y-m-d", $d1));
		}
	}

	private function generate_densotrn_file_by_date($b,$date){
		global $con;
		
		$bid = mi($b['id']);
		print "Generating densotrn file, Branch ID:$bid, Date: $date\n";
		
		// $folder_path = $this->get_eod_folder_path('densotrn', $bid, $date);
		$uploaded_folder_path = $this->get_uploaded_eod_folder_path('densotrn', $bid, $date);
		$files = $this->get_eod_filename('densotrn', $b, $date);
		$sp9_outlet_code = $this->Speed99->get_outlet_code($b);

		foreach($files as $file){
			if(is_dir($file)) continue;
			$filename = basename($file);
			if(!$this->regen_sales){	// no regenerate, check whether file exists
				if(file_exists($uploaded_folder_path."/".$filename) && filesize($uploaded_folder_path."/".$filename) > 0){
					print " - File Uploaded ($filename)\n";
					unlink($file);
					continue;
				}
			}

			// Day End Checking
			$ret = $this->check_branch_can_send_sales($bid, $date);
			if($ret['error']){
				print "There was a problem while checking day end.\n";
				print_r($ret['error']);
				foreach($ret['error'] as $e){
					$this->error_list[] = $e;
				}
				continue;
			}

			chmod($file,0777);

			// Upload
			$full_filepath = $file;

			// Create Folder
			$remote_folder = 'Shared';
			ftp_mkdir($this->denso_ftp_conn_id, $remote_folder);
			
			$remote_folder .= '/Denso';
			ftp_mkdir($this->denso_ftp_conn_id, $remote_folder);
			
			// Year
			$remote_folder .= "/".date("Y", strtotime($date));
			ftp_mkdir($this->denso_ftp_conn_id, $remote_folder);
			
			// Month
			$remote_folder .= "/".date("m", strtotime($date));
			ftp_mkdir($this->denso_ftp_conn_id, $remote_folder);
			
			// Day
			$remote_folder .= "/".date("d", strtotime($date));
			ftp_mkdir($this->denso_ftp_conn_id, $remote_folder);
			
			$remote_filename = $remote_folder."/".$filename;
			print " - Uploading... ";
			
			if (ftp_put($this->denso_ftp_conn_id, $remote_filename, $full_filepath, FTP_BINARY)) {
				print "successfully uploaded to remote location at $remote_filename.\n";
			} else {
				$error = error_get_last();
				print "There was a problem while uploading.\n";
				print_r($error);
				foreach($error as $e){
					$this->error_list[] = $e;
				}
				continue;
			}

			// Move to uploaded folder
			rename($full_filepath, $uploaded_folder_path."/".$filename);
		}
	}

	private function connect_denso_ftp(){
		global $config;
		
		print "Connecting to Speed99 Denso Transaction FTP Server. . . ";

		// set up basic connection
		$this->denso_ftp_conn_id = ftp_connect($this->Speed99->denso_server_ftp_info['ip'], $this->Speed99->denso_server_ftp_info['port']);
		if(!$this->denso_ftp_conn_id){
			print "Server Cannot be connect.\n";
			exit;
		}
		print "Connected.\n";
		print "Login to the server. . . ";

		// login with username and password
		$login_result = ftp_login($this->denso_ftp_conn_id, $this->Speed99->denso_server_ftp_info['username'], $this->Speed99->denso_server_ftp_info['pass']);
		if(!$login_result){
			print "Failed to login to the Server.\n";
			ftp_close($this->denso_ftp_conn_id);
			exit;
		}
		print "Success.\n";
		
		// Turn on Passive Mode
        ftp_pasv($this->densotrn_ftp_conn_id, true);
	}
}

$argv = $_SERVER['argv'];
$CRON_SPEED99 = new CRON_SPEED99();
$CRON_SPEED99->start();
?>
