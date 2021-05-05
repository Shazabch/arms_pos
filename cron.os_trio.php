<?php
/*
4/16/2019 5:07 PM Andy
- New Accounting Integration - OS Trio

5/27/2019 3:37 PM Andy
- Fixed cs_batch_no bug.

5/28/2019 6:02 PM Andy
- Fixed CCT_Vendor and CCT_Debtor ID wrong.
- Fixed date_to error.

5/29/2019 3:57 PM Andy
- Fixed Mix & Match Discount error in Cash Sales.

6/4/2019 3:44 PM Andy
- Fixed AP TotalInv always zero bug.
- Change AdjustAmt to always round to 2 decimal.

11/27/2019 11:15 AM Andy
- Enhance POS Cash Sales to pass variances.

12/2/2019 11:33 AM Andy
- Changed "TransDate" format from "d/m/Y" to "m/d/Y".

12/6/2019 9:32 AM Andy
- Fixed POS Cash Sales to skip zero variance payment type.

1/24/2020 1:11 PM Andy
- Enhanced to send cash sales payment by receipt if it is not cash.
- Add memory_limit 2G.
- Added closeCursor after every $con2 query.
- Fixed sync debtor query error.

5/8/2020 3:32 PM Andy
- Enhanced to append the word "VARIANCE" for payment type variances in Cash Sales.

6/15/2020 3:27 PM Andy
- Fixed $variances_amt to 2 decimal.

6/18/2020 4:27 PM Andy
- Added AR Integration.
- Move Credit Sales DO from CS to AR.
- Enhance Cash Sales DO to able to send sales by department.
- Enhance Cash Sales DO to able to send rounding.
- Fixed to send POS to Cash Sales after finalised 20 minutes.

1/20/2021 10:35 AM Andy
- Fixed sync_vendor and sync_debtor unable to update ImportFlag=1 if there are more than 1 record.

4/27/2021 2:22 PM Andy
- Fixed to round2 the POS rounding amount and discount amount.
*/

/*
php cron.os_trio.php -branch=all

php cron.os_trio.php -branch=hq -type=debtor
*/
define('TERMINAL',1);
define('armshq_2002',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '2G');
set_time_limit(0);

$argv = $_SERVER['argv'];
$OS_TRIO = new OS_TRIO();
$OS_TRIO->start();

class OS_TRIO {
	var $b_list = array();
	var $fp_path = '';
	var $integration_start_date = '';
	var $date_from;
	var $date_to;
	var $resend = false;
	var $allowed_integration_type = array('vendor', 'debtor', 'ap', 'gl', 'cs', 'ar');
	var $cs_payment_by_receipt = 0;
	
	function __construct(){
	    global $con, $sessioninfo;

		$this->fp_path = dirname(__FILE__)."/".basename(__FILE__, '.php').".running";	// use this prevent wrong "include" path
		
		$this->fp = fopen($this->fp_path, "w");
		chmod($this->fp_path, 0777);
		
		$this->mark_start_process();
	}
	
	function __destruct() {
        $this->mark_close_process();
    }
	
	function start(){
		//print "Start\n";
		print "Start Time: ".date("Y-m-d H:i:s")."\n\n";
		
		// Check
		$this->init_data();
		$this->filter_argv();
		$this->check_argv();
		
		// Download
		if(isset($this->b_list[1])){	// Run this only when got HQ
			$this->sync_master();
		}
		
		// Upload
		if(!$this->integration_type || $this->integration_type=='ap'){
			$this->sync_ap();
		}
		if(!$this->integration_type || $this->integration_type=='gl'){
			$this->sync_gl();
		}
		if(!$this->integration_type || $this->integration_type=='cs'){
			$this->sync_cs();
		}
		if(!$this->integration_type || $this->integration_type=='ar'){
			$this->sync_ar();
		}
		
		print "Finish Time: ".date("Y-m-d H:i:s")."\n";
		print "Done\n";
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

				$con->sql_query("select id,code,report_prefix from branch $branch_filter order by sequence,code");
				while($r = $con->sql_fetchassoc()){
					$this->b_list[$r['id']] = $r;
				}
				$con->sql_freeresult();
			}elseif($cmd_head == "-date"){	// date
				$tmp = date("Y-m-d", strtotime($cmd_value));
				if(!$tmp)	die("Invalid Date.\n");
				if(date("Y", strtotime($tmp))<2010)	die("Date $tmp is invalid. Date must start at 2010-01-01.\n");
				$this->date_from = $this->date_to = $tmp;
			}elseif($cmd_head == "-date_from"){	// date_from
				$tmp = date("Y-m-d", strtotime($cmd_value));
				if(!$tmp)	die("Invalid Date From.\n");
				if(date("Y", strtotime($tmp))<2010)	die("Date From $tmp is invalid. Date must start at 2010-01-01.\n");
				$this->date_from = $tmp;
			}elseif($cmd_head == "-date_to"){	// date_to
				$tmp = date("Y-m-d", strtotime($cmd_value));
				if(!$tmp)	die("Invalid Date To.\n");
				if(date("Y", strtotime($tmp))<2010)	die("Date To $tmp is invalid. Date must start at 2010-01-01.\n");
				$this->date_to = $tmp;
			}elseif($cmd_head == "-type"){	// date_to
				$tmp = trim($cmd_value);
				if(!in_array($tmp, $this->allowed_integration_type)){
					die("Invalid Type '$tmp'\n");
				}
				
				$this->integration_type = $tmp;
			}elseif($cmd_head == '-resend'){
				$this->resend = true;
			}else{
				print "Unknown command $cmd\n";
				exit;
			}
		}
	}
	
	function check_argv(){
		// Branch
		if(!$this->b_list)	die("Branch not found.\n");
		
	}
	
	function init_data(){
		global $config, $appCore;
		
		if(!$config['os_trio_settings']){
			die("Config Not Set\n");
		}
		
		$integration_start_date = trim($config['os_trio_settings']['integration_start_date']);
		if($integration_start_date){
			if(!$appCore->isValidDateFormat($integration_start_date)){
				die("Invalid 'integration_start_date'");
			}
			$this->integration_start_date = date("Y-m-d", strtotime($integration_start_date));
		}
		
		if(isset($config['os_trio_settings']['cs_payment_by_receipt'])){
			$this->cs_payment_by_receipt  = mi($config['os_trio_settings']['cs_payment_by_receipt']);
		}
		
	}
	
	private function connect_os_trio(){
		global $con2, $config;
		
		if($con2)	return;
		
		if(!$config['os_trio_settings']){
			die("Config Not Set\n");
		}
		
		$host = trim($config['os_trio_settings']['db_settings']['host']);
		$u = trim($config['os_trio_settings']['db_settings']['u']);
		$p = trim($config['os_trio_settings']['db_settings']['p']);
		$dbname = trim($config['os_trio_settings']['db_settings']['db']);
		
		print "Connecting MSSQL Server...";
		$con2 = new PDO('dblib:host='.$host.';dbname='.$dbname, $u, $p);
		print " Connected.\n";
		//$con2->exec("SET NOCOUNT ON");
	}
	
	private function set_status($bid, $integration_type, $sub_type, $status, $err_msg_list = array()){
		global $con;
		
		$bid = mi($bid);
		
		$upd = array();
		$upd['status'] = $status;
		$upd['got_error'] = $err_msg_list ? 1 : 0;
		$upd['error_msg'] = $err_msg_list ? serialize($err_msg_list) : '';
		if($status == 1){
			$upd['end_time'] = 'CURRENT_TIMESTAMP';
		}else{
			$upd['start_time'] = 'CURRENT_TIMESTAMP';
			$upd['end_time'] = '';
		}
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("select * from ostrio_integration_status where branch_id=$bid and integration_type=".ms($integration_type)." and sub_type=".ms($sub_type));
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($data){	// update
			$con->sql_query("update ostrio_integration_status set ".mysql_update_by_field($upd)." where branch_id=$bid and integration_type=".ms($integration_type)." and sub_type=".ms($sub_type));
		}else{	// new
			$upd['branch_id'] = $bid;
			$upd['integration_type'] = $integration_type;
			$upd['sub_type'] = $sub_type;
			$con->sql_query("replace into ostrio_integration_status ".mysql_insert_by_field($upd));
		}
	}
	
	function sync_master(){
		print "Master...\n";
		
		if(!$this->integration_type || $this->integration_type=='vendor'){
			// Sync Vendor
			$this->sync_vendor();
		}
		
		if(!$this->integration_type || $this->integration_type=='debtor'){
			// Sync Debtor
			$this->sync_debtor();
		}
		
		print "Master Done.\n\n";
	}
	
	function sync_vendor(){
		global $con, $con2;
		
		$this->connect_os_trio();
		
		// Mark Start
		$integration_type = 'master';
		$sub_type = 'vendor';
		$this->set_status(1, $integration_type, $sub_type, 0);
		
		print "Updating Vendor...\n";
		$ret = $con2->query("select * from CCT_Vendor where ImportFlag=0 order by ID");
		if(!$ret){
			print_r($con2->errorInfo());
			return;
		}
		$row_count = $error_count = 0;
		$err_msg_list = array();
		
		// Store data into array
		$new_data_list = array();
		while($r = $ret->fetch(PDO::FETCH_ASSOC)){
			$new_data_list[] = $r;
		}
		$ret->closeCursor();
		
		if($new_data_list){
			// Loop the array
			foreach($new_data_list as $r){	
				$row_count++;
				$row_err_msg = array();
				print "\rProcessing Row $row_count. . .";
				
				$ostrio_vendor_id = trim($r['code']);
				$vendor_code = trim($r['code']);
				
				if(!$ostrio_vendor_id){
					$row_err_msg[]= "ROW ID: ".$r['ID'].", Vendor Code is Empty\n";
				}
				/*if(!$vendor_code){
					$row_err_msg[]= "ROW ID: ".$r['ID'].", Vendor Code is Empty\n";
				}*/
				
				if(!$row_err_msg){
					// Get Vendor 
					$vendor = $this->get_vendor_by_ostrio_vendor_id($ostrio_vendor_id);
					if(!$vendor){
						// Get vendor by vendor_code
						$vendor = $this->get_vendor_by_vendor_code($vendor_code);
					}
					$vendor_id = mi($vendor['id']);
					
					$upd = array();
					$upd['description'] = trim($r['description']);
					$upd['company_no'] = trim($r['company_no']);
					$upd['vendortype_code'] = trim($r['vendortype_code']);
					$upd['term'] = mi($r['term']);
					$upd['bank_account'] = trim($r['bank_account']);
					$upd['address'] = trim($r['address']);
					$upd['phone_1'] = trim($r['phone_1']);
					$upd['phone_3'] = trim($r['phone_3']);
					$upd['contact_person'] = trim($r['contact_person']);
					$upd['contact_email'] = trim($r['contact_email']);
					$upd['active'] = mi($r['active']) == 1 ? 1 : 0;
					
					if($vendor_id>0){
						$con->sql_query("update vendor set ".mysql_update_by_field($upd)." where id=$vendor_id");
						log_br(1, 'MASTERFILE', $vendor_id, 'Vendor update information ' . "($vendor_code) ".print_r($upd, true));
					}else{
						$upd['code'] = $vendor_code;
						$con->sql_query("insert into vendor ".mysql_insert_by_field($upd));
						$vendor_id = $con->sql_nextid();
						log_br(1, 'MASTERFILE', $vendor_id, 'Vendor create ('. $vendor_code.") ".print_r($upd, true));
					}
					
					// Update Mapping Info
					$upd2 = array();
					$upd2['vendor_id'] = $vendor_id;
					$upd2['ostrio_vendor_id'] = $ostrio_vendor_id;
					$upd2['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("replace into ostrio_vendor_mapping ".mysql_insert_by_field($upd2));
					
					// Mark updated
					$success = $con2->exec($sql = "update CCT_Vendor set ImportFlag=1, ImportDate=GETDATE() where ID=".ms($r['ID']));
					if($success === false){	// Insert Failed
						print "Failed: $sql\n";
						$err_msg = print_r($con2->errorInfo(), true);
						$row_err_msg[] = $err_msg;
					}
				}else{
					print_r($row_err_msg);
				}
				
				if($row_err_msg){
					$error_count++;
					$err_msg_list = array_merge($err_msg_list, $row_err_msg);
				}
			}
		}
		
		
		// Mark Finish
		$this->set_status(1, $integration_type, $sub_type, 1, $err_msg_list);
		
		print "$row_count vendor processed.\n";
		if($error_count>0)	print "$error_count row got error.\n";
	}
	
	private function get_vendor_by_ostrio_vendor_id($ostrio_vendor_id){
		global $con;
		
		if(!$ostrio_vendor_id)	return;
		
		$con->sql_query("select v.* 
			from ostrio_vendor_mapping ov
			join vendor v on v.id=ov.vendor_id
			where ov.ostrio_vendor_id=".ms($ostrio_vendor_id));
		$vendor = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $vendor;
	}
	
	private function get_vendor_by_vendor_code($vendor_code){
		global $con;
		
		if(!$vendor_code)	return;
		
		$con->sql_query("select * from vendor where code=".ms($vendor_code));
		$vendor = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $vendor;
	}
	
	function sync_debtor(){
		global $con, $con2;
		
		$this->connect_os_trio();
		
		// Mark Start
		$integration_type = 'master';
		$sub_type = 'debtor';
		$this->set_status(1, $integration_type, $sub_type, 0);
		
		print "Updating Debtor...\n";
		$ret = $con2->query("select * from CCT_Debtor where ImportFlag=0 order by ID");
		if(!$ret){
			print_r($con2->errorInfo());
			return;
		}
		$row_count = $error_count = 0;
		$err_msg_list = array();
		
		// Store data into array
		$new_data_list = array();
		while($r = $ret->fetch(PDO::FETCH_ASSOC)){
			$new_data_list[] = $r;
		}
		$ret->closeCursor();
		
		if($new_data_list){
			foreach($new_data_list as $r){	
				$row_count++;
				$row_err_msg = array();
				
				$ostrio_debtor_id = trim($r['code']);
				$debtor_code = trim($r['code']);
				
				if(!$ostrio_debtor_id){
					$row_err_msg[]= "ROW ID: ".$r['ID'].", Debtor code is Empty\n";
				}
				/*if(!$debtor_code){
					$row_err_msg[]= "ROW ID: ".$r['ID'].", Debtor Code is Empty\n";
				}*/
				
				if(!$row_err_msg){
					// Get debtor 
					$debtor = $this->get_debtor_by_ostrio_debtor_id($ostrio_debtor_id);
					if(!$debtor){
						// Get debtor by debtor_code
						$debtor = $this->get_debtor_by_debtor_code($debtor_code);
					}
					$debtor_id = mi($debtor['id']);
					
					$upd = array();
					$upd['description'] = trim($r['description']);
					$upd['company_no'] = trim($r['company_no']);
					$upd['address'] = trim($r['address']);
					$upd['area'] = trim($r['area']);
					$upd['phone_1'] = trim($r['phone_1']);
					$upd['phone_3'] = trim($r['phone_3']);
					$upd['contact_person'] = trim($r['contact_person']);
					$upd['contact_email'] = trim($r['contact_email']);
					$upd['term'] = mi($r['term']);
					$upd['credit_limit'] = mf($r['credit_limit']);
					$upd['active'] = mi($r['active'])==1 ? 1 : 0;
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					
					if($debtor_id>0){
						$con->sql_query("update debtor set ".mysql_update_by_field($upd)." where id=$debtor_id");
						
						$upd['last_update'] = date("Y-m-d h:i:s");	// Fix if got CURRENT_TIMESTAMP string will not ms
						log_br(1, 'MASTERFILE', $debtor_id, 'Debtor update information ' . "($debtor_code) ".print_r($upd, true));
					}else{
						$upd['code'] = $debtor_code;
						$con->sql_query("insert into debtor ".mysql_insert_by_field($upd));
						$debtor_id = $con->sql_nextid();
						
						$upd['last_update'] = date("Y-m-d h:i:s");	// Fix if got CURRENT_TIMESTAMP string will not ms
						log_br(1, 'MASTERFILE', $debtor_id, 'Debtor create ('.$debtor_code.") ".print_r($upd, true));
					}
					
					// Update Mapping Info
					$upd2 = array();
					$upd2['debtor_id'] = $debtor_id;
					$upd2['ostrio_debtor_id'] = $ostrio_debtor_id;
					$upd2['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("replace into ostrio_debtor_mapping ".mysql_insert_by_field($upd2));
					
					// Mark updated
					$success = $con2->exec("update CCT_Debtor set ImportFlag=1, ImportDate=GETDATE() where ID=".ms($r['ID']));
					if($success === false){	// Insert Failed
						print "Failed: $sql\n";
						$err_msg = print_r($con2->errorInfo(), true);
						$row_err_msg[] = $err_msg;
					}
				}else{
					print_r($row_err_msg);
				}
				
				if($row_err_msg){
					$error_count++;
					$err_msg_list = array_merge($err_msg_list, $row_err_msg);
				}
			}
		}
		
		// Mark Finish
		$this->set_status(1, $integration_type, $sub_type, 1, $err_msg_list);
		
		print "$row_count debtor processed.\n";
		if($error_count>0)	print "$error_count row got error.\n";
	}
	
	private function get_debtor_by_ostrio_debtor_id($ostrio_debtor_id){
		global $con;
		
		if(!$ostrio_debtor_id)	return;
		
		$con->sql_query("select d.* 
			from ostrio_debtor_mapping od
			join debtor d on d.id=od.debtor_id
			where od.ostrio_debtor_id=".ms($ostrio_debtor_id));
		$debtor = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $debtor;
	}
	
	private function get_debtor_by_debtor_code($debtor_code){
		global $con;
		
		if(!$debtor_code)	return;
		
		$con->sql_query("select * from debtor where code=".ms($debtor_code));
		$debtor = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $debtor;
	}
	
	private function sync_ap(){
		foreach($this->b_list as $bid => $b){
			$this->sync_ap_by_branch($bid);
		}
	}
	
	private function sync_ap_by_branch($bid){
		$bcode = trim($this->b_list[$bid]['code']);
		print "AP (".$bcode.")...\n";
		
		$this->sync_ap_by_branch_grr($bid);
		$this->sync_ap_by_branch_dnote($bid);
		
		print "AP Done.\n\n";
	}
	
	private function sync_ap_by_branch_grr($bid){
		global $con, $con2, $config;
		
		$this->connect_os_trio();
		
		// Mark Start
		$integration_type = 'ap';
		$sub_type = 'grr';
		$this->set_status($bid, $integration_type, $sub_type, 0);
		
		$bcode = trim($this->b_list[$bid]['code']);
		print "Updating GRR (".$bcode.")...\n";
		
		// GRR
		$filter = array();
		$filter[] = "grr.branch_id=$bid and grr.active=1 and grr.status=1";	// GRR
		$filter[] = "gi.type='INVOICE'";	// grr_items
		$filter[] = "grn.active=1 and grn.status=1 and grn.approved=1";	// grn
		
		if(!$this->resend){
			$filter[] = "apm.ostrio_ap_id is null";	// Only new data
		}
		if($this->date_from){
			$filter[] = "gi.doc_date>=".ms($this->date_from);
		}
		if($this->date_to){
			$filter[] = "gi.doc_date<=".ms($this->date_to);
		}
		
		if($this->integration_start_date){
			$filter[] = "gi.doc_date>=".ms($this->integration_start_date);
		}
		
		$str_filter = "where ".join(' and ', $filter);
		$row_count = $error_count = 0;
		$err_msg_list = array();
		
		$sql = "select gi.*, v.code as vendor_code, v.term as vendor_term, v.description as vendor_desc, grr.currency_code, grr.currency_rate, grr.grr_amount, grr.tax_register, grr.tax_percent, grr.grr_tax, grr.is_under_gst, grr.grr_gst_amount, dept.description as dept_name, ov.ostrio_vendor_id, dept.id as dept_id, apm.ostrio_ap_id
					from grr 
					join grr_items gi on grr.id = gi.grr_id and grr.branch_id = gi.branch_id
					join grn on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
					left join vendor v on v.id=grr.vendor_id
					left join ostrio_ap_mapping apm on apm.branch_id=grr.branch_id and apm.doc_id=grr.id and apm.ap_type='grr'
					left join ostrio_vendor_mapping ov on ov.vendor_id=grr.vendor_id
					left join category dept on dept.id=grr.department_id
					$str_filter
					order by grr.id";
					
		//print $sql."\n";
		
		$q1 = $con->sql_query($sql);
		$grr_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$grr_id = mi($r['grr_id']);
			$ostrio_vendor_id = trim($r['ostrio_vendor_id']);
			$ostrio_ap_id = mi($r['ostrio_ap_id']);
			$row_err_msg = array();
			
			if(!$ostrio_vendor_id){
				$row_err_msg[]= "GRR ID#$grr_id: OS Trio Vendor ID Not Found.\n";
			}
			
			if(!$row_err_msg){
				if(!isset($grr_list[$grr_id])){
					$con->sql_query("select * from grr_items where branch_id=$bid and grr_id=$grr_id and type='DO'");
					$grr_do = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					$grr_list[$grr_id]['dept_id'] = $r['dept_id'];
					$grr_list[$grr_id]['BranchCode'] = $bcode;
					$grr_list[$grr_id]['InvoiceNo'] = $r['doc_no'];
					//$grr_list[$grr_id]['InvoiceDate'] = date("d/m/Y", strtotime($r['doc_date']));
					$grr_list[$grr_id]['InvoiceDate'] = $r['doc_date'];
					$grr_list[$grr_id]['VendorID'] = $r['ostrio_vendor_id'];
					$grr_list[$grr_id]['APPCurrencyRate'] = $r['currency_rate'] ? 1/$r['currency_rate'] : 1;
					$grr_list[$grr_id]['DONo'] = trim($grr_do['doc_no']);
					$grr_list[$grr_id]['TranType'] = 'I';
					$grr_list[$grr_id]['Status'] = 'V';
					$grr_list[$grr_id]['DocLineNo'] = 1;
					$grr_list[$grr_id]['TranDesc'] = "Post from ARMS ".$r['vendor_desc'].' '.$r['doc_no'];
					$grr_list[$grr_id]['ProductGroup'] = $r['dept_name'];
					$grr_list[$grr_id]['InvQty'] = 1;
					$grr_list[$grr_id]['UserID'] = 'ARMS';
					$grr_list[$grr_id]['VATPercentage'] = 0;
					$grr_list[$grr_id]['GSTFCY'] = 0;
					
					if($r['tax_register']){
						$grr_list[$grr_id]['VATCode'] = 'SST-P0';
						$grr_list[$grr_id]['VATPercentage'] = $r['tax_percent'];
					}
					
					if($ostrio_ap_id>0){
						$grr_list[$grr_id]['ostrio_ap_id'] = $ostrio_ap_id;
					}
				}
				
				
				$grr_list[$grr_id]['TotalAfterVAT'] += $r['grr_amount'];
				
				if($r['is_under_gst']){
					// GST
					$grr_list[$grr_id]['GSTFCY'] += $r['grr_gst_amount'];
				}elseif($r['tax_register']){
					// SST
					$grr_list[$grr_id]['GSTFCY'] += $r['grr_tax'];	
				}
				$grr_list[$grr_id]['InvUnitPrice'] = $grr_list[$grr_id]['TotalInv'] = $grr_list[$grr_id]['TotalAfterVAT'] - $grr_list[$grr_id]['GSTFCY'];
				
				// Local amount
				$grr_list[$grr_id]['LocalInvUnitPrice'] = round($grr_list[$grr_id]['InvUnitPrice'] / $grr_list[$grr_id]['APPCurrencyRate'], 10);
				$grr_list[$grr_id]['LocalTotalInv'] = $grr_list[$grr_id]['LocalInvUnitPrice'];
				$grr_list[$grr_id]['GSTValueLocal'] = round($grr_list[$grr_id]['GSTFCY'] / $grr_list[$grr_id]['APPCurrencyRate'], 10);
			}
			
			if($row_err_msg){
				print_r($row_err_msg);
				
				$error_count++;
				$err_msg_list = array_merge($err_msg_list, $row_err_msg);
			}
		}
		$con->sql_freeresult($q1);
		
		if($grr_list){
			//print_r($grr_list);exit;
			$data_fields = array('BranchCode', 'InvoiceNo', 'InvoiceDate', 'VendorID', 'APPCurrencyRate', 'DONo', 'TranType', 'Status', 'DocLineNo', 'TranDesc', 'ProductGroup', 'InvQty', 'UserID', 'VATCode', 'VATPercentage', 'TotalAfterVAT', 'GSTFCY', 'InvUnitPrice', 'LocalInvUnitPrice', 'LocalTotalInv', 'GSTValueLocal', 'TotalInv');
			
			foreach($grr_list as $grr_id => $r){
				$row_count++;
				$row_err_msg = array();
				$ostrio_ap_id = $r['ostrio_ap_id'];
				$r['ImportFlag'] = 0;
				$r['ImportDate'] = '';
				print "$row_count - ";
				
				$success = false;
				if($ostrio_ap_id){
					//unset($r['ostrio_ap_id']);
					// Update
					$success = $con2->exec($sql = "update CCT_APTrans set ".mssql_update_by_field($r, $data_fields)." where ID=$ostrio_ap_id");
					if($success === false){
						$err_msg = print_r($con2->errorInfo(), true);
						$row_err_msg[] = $err_msg;
						//print "$sql\n$err_msg";exit;
					}else{
						print "update success\n";
					}
				}else{
					// Insert
					$success = $con2->exec($sql = "insert into CCT_APTrans ".mssql_insert_by_field($r, $data_fields));
					if($success === false){	// Insert Failed
						$err_msg = print_r($con2->errorInfo(), true);
						$row_err_msg[] = $err_msg;
						//print "$sql\n$err_msg";exit;
					}else{					
						// Get inserted data
						$sth = $con2->query($sql = "select ID from CCT_APTrans where BranchCode=".ms($bcode)." and InvoiceNo=".ms($r['InvoiceNo'])." and TranType=".ms($r['TranType'])." order by ID desc");
						$temp = $sth->fetch(PDO::FETCH_ASSOC);
						$sth->closeCursor();
						$ostrio_ap_id = $temp['ID'];
						if(!$ostrio_ap_id){	// Cant Get Inserted ID
							$err_msg = print_r($con2->errorInfo(), true);
							$row_err_msg[] = $err_msg;
							//print "$sql\n$err_msg";exit;
						}else{
							print "insert success\n";
						}
					}
				}
				
				// Update Mapping Info
				if(!$row_err_msg){
					$upd2 = array();
					$upd2['branch_id'] = $bid;
					$upd2['doc_id'] = $grr_id;
					$upd2['acc_doc_no'] = $r['InvoiceNo'];
					$upd2['doc_date'] = $r['InvoiceDate'];
					$upd2['dept_id'] = $r['dept_id'];
					$upd2['ap_type'] = 'grr';
					$upd2['amount'] = $r['TotalAfterVAT'];
					$upd2['ostrio_ap_id'] = $ostrio_ap_id;
					$upd2['last_update'] = 'CURRENT_TIMESTAMP';
					$upd2['api_data'] = serialize($r);
					$con->sql_query("replace into ostrio_ap_mapping ".mysql_insert_by_field($upd2));
				}else{
					print_r($row_err_msg);
				
					$error_count++;
					$err_msg_list = array_merge($err_msg_list, $row_err_msg);
				}
			}
		}
		
		// Mark Finish
		$this->set_status($bid, $integration_type, $sub_type, 1, $err_msg_list);
		
		print "$row_count GRR processed.\n";
		if($error_count>0)	print "$error_count row got error.\n";
	}
	
	private function sync_ap_by_branch_dnote($bid){
		global $con, $con2, $config;
		
		$this->connect_os_trio();
		
		// Mark Start
		$integration_type = 'ap';
		$sub_type = 'dn';
		$this->set_status($bid, $integration_type, $sub_type, 0);
		
		$bcode = trim($this->b_list[$bid]['code']);
		print "Updating Debit Note (".$bcode.")...\n";
		
		// Debit Note
		$row_count = $error_count = 0;
		$err_msg_list = array();
		$filter = array();
		
		if(!$this->resend){
			$filter[] = "apm.ostrio_ap_id is null";	// Only new data
		}
		if($this->date_from){
			$filter[] = "p.dn_date >= ".ms($this->date_from);
		}
		if($this->date_to){
			$filter[] = "p.dn_date <= ".ms($this->date_to);
		}
		
		if($this->integration_start_date){
			$filter[] = "p.dn_date >= ".ms($this->integration_start_date);
		}

		//$filter[]="p.ref_table in('grn','gra')";
		$filter[]="p.active = 1 and p.branch_id=$bid";

		$str_filter = "where ".implode(" and ",$filter);

		$sql = "select p.*, v.description as vendor_desc, ov.ostrio_vendor_id, apm.ostrio_ap_id
			from dnote p 
			left join vendor v on v.id=p.vendor_id
			left join ostrio_vendor_mapping ov on ov.vendor_id=p.vendor_id
			left join ostrio_ap_mapping apm on apm.branch_id=p.branch_id and apm.doc_id=p.id and apm.ap_type='dnote'
			$str_filter order by p.id";
		//print $sql."\n";
		
		$q1 = $con->sql_query($sql);
		$dn_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$dn_id = mi($r['id']);
			$ostrio_vendor_id = trim($r['ostrio_vendor_id']);
			$ostrio_ap_id = mi($r['ostrio_ap_id']);
			$row_err_msg = array();
			
			if(!$ostrio_vendor_id){
				$row_err_msg[] = "DN ID#$dn_id: OS Trio Vendor ID Not Found.\n";
			}
			
			if(!$row_err_msg){
				if(!isset($dn_list[$dn_id])){
					// Get Department
					if($r['ref_table']=='grn'){
						$q_dept = $con->sql_query("select dept.id as dept_id, dept.description as dept_name
							from grr
							join grn on grn.branch_id=grr.branch_id and grn.grr_id=grr.id
							left join category dept on dept.id=grr.department_id
							where grn.branch_id=$bid and grn.id=".mi($r['ref_id']));
					}elseif($r['ref_table']=='gra'){
						$q_dept = $con->sql_query("select dept.id as dept_id, dept.description as dept_name
							from gra
							left join category dept on dept.id=gra.dept_id
							where gra.branch_id=$bid and gra.id=".mi($r['ref_id']));
					}else{
						print "DN ID#$dn_id: Unknown Ref Table (".$r['ref_table'].").\n";
						continue;
					}
					$dept = $con->sql_fetchassoc($q_dept);
					$con->sql_freeresult($q_dept);
					
					$dn_list[$dn_id]['dept_id'] = $dept['dept_id'];
					$dn_list[$dn_id]['BranchCode'] = $bcode;
					$dn_list[$dn_id]['InvoiceNo'] = $r['dn_no'];
					//$dn_list[$dn_id]['InvoiceDate'] = date("d/m/Y", strtotime($r['dn_date']));
					$dn_list[$dn_id]['InvoiceDate'] = $r['dn_date'];
					$dn_list[$dn_id]['VendorID'] = $r['ostrio_vendor_id'];
					$dn_list[$dn_id]['APPCurrencyRate'] = $r['currency_rate'] ? 1/$r['currency_rate'] : 1;
					$dn_list[$dn_id]['DONo'] = '';
					$dn_list[$dn_id]['TranType'] = 'D';
					$dn_list[$dn_id]['Status'] = 'V';
					$dn_list[$dn_id]['DocLineNo'] = 1;
					$dn_list[$dn_id]['TranDesc'] = "Post from ARMS ".$r['vendor_desc'].' '.$r['dn_no'];
					$dn_list[$dn_id]['ProductGroup'] = $dept['dept_name'];
					$dn_list[$dn_id]['InvQty'] = 1;
					$dn_list[$dn_id]['UserID'] = 'ARMS';
					$dn_list[$dn_id]['VATPercentage'] = 0;
					$dn_list[$dn_id]['GSTFCY'] = 0;
					
					//if($r['tax_register']){
						//$dn_list[$dn_id]['VATCode'] = 'SST-P0';
						//$dn_list[$dn_id]['VATPercentage'] = $r['tax_percent'];
					//}
					
					if($ostrio_ap_id>0){
						$dn_list[$dn_id]['ostrio_ap_id'] = $ostrio_ap_id;
					}
					
					$dn_list[$dn_id]['TotalAfterVAT'] += $r['total_amount'];
				
					if($r['is_under_gst']){
						// GST
						$dn_list[$dn_id]['GSTFCY'] += $r['total_gst_amount'];
					}elseif($r['tax_register']){
						// SST
						//$dn_list[$dn_id]['GSTFCY'] += $r['grr_tax'];
					}
					$dn_list[$dn_id]['InvUnitPrice'] = $dn_list[$dn_id]['TotalInv'] = $dn_list[$dn_id]['TotalAfterVAT'] - $dn_list[$dn_id]['GSTFCY'];
					
					// Local amount
					$dn_list[$dn_id]['LocalInvUnitPrice'] = round($dn_list[$dn_id]['InvUnitPrice'] / $dn_list[$dn_id]['APPCurrencyRate'], 10);
					$dn_list[$dn_id]['LocalTotalInv'] = $dn_list[$dn_id]['LocalInvUnitPrice'];
					$dn_list[$dn_id]['GSTValueLocal'] = round($dn_list[$dn_id]['GSTFCY'] / $dn_list[$dn_id]['APPCurrencyRate'], 10);
				}
			}
			
			if($row_err_msg){
				print_r($row_err_msg);
				
				$error_count++;
				$err_msg_list = array_merge($err_msg_list, $row_err_msg);
			}
			
		}
		$con->sql_freeresult($q1);
		
		if($dn_list){
			$data_fields = array('BranchCode', 'InvoiceNo', 'InvoiceDate', 'VendorID', 'APPCurrencyRate', 'DONo', 'TranType', 'Status', 'DocLineNo', 'TranDesc', 'ProductGroup', 'InvQty', 'UserID', 'VATCode', 'VATPercentage', 'TotalAfterVAT', 'GSTFCY', 'InvUnitPrice', 'LocalInvUnitPrice', 'LocalTotalInv', 'GSTValueLocal');
			
			foreach($dn_list as $dn_id => $r){
				$row_count++;
				$row_err_msg = array();
				$ostrio_ap_id = $r['ostrio_ap_id'];
				$r['ImportFlag'] = 0;
				$r['ImportDate'] = '';
				
				if($ostrio_ap_id){
					unset($r['ostrio_ap_id']);
					// Update
					$success = $con2->exec("update CCT_APTrans set ".mssql_update_by_field($r, $data_fields)." where ID=$ostrio_ap_id");
					if($success === false){
						//print "$sql\n";
						$err_msg = print_r($con2->errorInfo(), true);
						$row_err_msg[] = $err_msg;
					}
				}else{
					// Insert
					$success = $con2->exec("insert into CCT_APTrans ".mssql_insert_by_field($r, $data_fields));
					if($success === false){	// Insert Failed
						//print "$sql\n";
						$err_msg = print_r($con2->errorInfo(), true);
						$row_err_msg[] = $err_msg;
					}else{					
						// Get inserted data
						$sth = $con2->query("select ID from CCT_APTrans where BranchCode=".ms($bcode)." and InvoiceNo=".ms($r['InvoiceNo'])." and TranType=".ms($r['TranType'])." order by ID desc");
						$temp = $sth->fetch(PDO::FETCH_ASSOC);
						$sth->closeCursor();
						$ostrio_ap_id = $temp['ID'];
						if(!$ostrio_ap_id){	// Cant Get Inserted ID
							$err_msg = print_r($con2->errorInfo(), true);
							$row_err_msg[] = $err_msg;
						}
					}
				}
				
				if(!$row_err_msg){
					// Update Mapping Info
					$upd2 = array();
					$upd2['branch_id'] = $bid;
					$upd2['doc_id'] = $dn_id;
					$upd2['acc_doc_no'] = $r['InvoiceNo'];
					$upd2['doc_date'] = $r['InvoiceDate'];
					$upd2['dept_id'] = $r['dept_id'];
					$upd2['ap_type'] = 'dnote';
					$upd2['amount'] = $r['TotalAfterVAT'];
					$upd2['ostrio_ap_id'] = $ostrio_ap_id;
					$upd2['last_update'] = 'CURRENT_TIMESTAMP';
					$upd2['api_data'] = serialize($r);
					$con->sql_query("replace into ostrio_ap_mapping ".mysql_insert_by_field($upd2));
				}else{
					print_r($row_err_msg);
				
					$error_count++;
					$err_msg_list = array_merge($err_msg_list, $row_err_msg);
				}
			}
		}
		
		// Mark Finish
		$this->set_status($bid, $integration_type, $sub_type, 1, $err_msg_list);
		
		print "$row_count DN processed.\n";
		if($error_count>0)	print "$error_count row got error.\n";
	}
	
	private function sync_gl(){
		foreach($this->b_list as $bid => $b){
			$this->sync_gl_by_branch($bid);
		}
	}
	
	private function sync_gl_by_branch($bid){
		global $con, $con2, $config;
		
		$this->connect_os_trio();
		$bcode = trim($this->b_list[$bid]['code']);
		print "GL (".$bcode.")...\n";
		
		$this->sync_gl_by_branch_adj($bid);
		$this->sync_gl_by_branch_do($bid);
		$this->sync_gl_by_branch_pos($bid);
		
		print "GL Done.\n\n";
	}
	
	private function sync_gl_by_branch_adj($bid){
		global $con, $con2, $config;
		
		$this->connect_os_trio();
		
		// Mark Start
		$integration_type = 'gl';
		$sub_type = 'adj';
		$this->set_status($bid, $integration_type, $sub_type, 0);
		
		$bcode = trim($this->b_list[$bid]['code']);
		$report_prefix = trim($this->b_list[$bid]['report_prefix']);
		print "Updating Adjustment (".$bcode.")...\n";
		
		// Adjustment
		$filter = array();
		$filter[] = "adj.branch_id=$bid and adj.active=1 and adj.status=1 and adj.approved=1";	// Adjustment
		
		if(!$this->resend){
			$filter[] = "glm.ostrio_gl_id is null";	// Only new data
		}
		if($this->date_from){
			$filter[] = "adj.adjustment_date>=".ms($this->date_from);
		}
		if($this->date_to){
			$filter[] = "adj.adjustment_date<=".ms($this->date_to);
		}
		
		if($this->integration_start_date){
			$filter[] = "adj.adjustment_date>=".ms($this->integration_start_date);
		}
		
		$str_filter = "where ".join(' and ', $filter);
		$row_count = $error_count = 0;
		$err_msg_list = array();
		
		$sql = "select adj.*, dept.id as dept_id, dept.description as dept_name, glm.ostrio_gl_id,
			(
				select sum(ai.qty*ai.cost) 
				from adjustment_items ai
				where ai.branch_id=adj.branch_id and ai.adjustment_id=adj.id
				) as adj_amt
			from adjustment adj 
			left join ostrio_gl_mapping glm on glm.branch_id=adj.branch_id and glm.doc_id=adj.id and glm.gl_type='adjustment'
			left join category dept on dept.id=adj.dept_id
			$str_filter
			order by adj.id";
		//print $sql."\n";
		$q1 = $con->sql_query($sql);
		$adj_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$adj_id = mi($r['id']);
			$ostrio_gl_id = mi($r['ostrio_gl_id']);
			$row_err_msg = array();
			
			if(!isset($adj_list[$adj_id])){
				$adj_list[$adj_id]['dept_id'] = $r['dept_id'];
				$adj_list[$adj_id]['BranchCode'] = $bcode;
				$adj_list[$adj_id]['DocNo'] = 'ADJ-'.$report_prefix.sprintf("%05d", $r['id']);
				$adj_list[$adj_id]['DocDate'] = $r['adjustment_date'];
				$adj_list[$adj_id]['Status'] = 'A';
				$adj_list[$adj_id]['Source'] = 'STA';
				$adj_list[$adj_id]['Adjustment_Type'] = $r['adjustment_type'];
				$adj_list[$adj_id]['ProductGroup'] = $r['dept_name'];
				$adj_list[$adj_id]['GLRemark'] = 'Inventory Post from ARMS';
				$adj_list[$adj_id]['UpdateDate'] = date("Y-m-d");
				$adj_list[$adj_id]['UserID'] = 'ARMS';
				$adj_list[$adj_id]['AdjustAmt'] = round(abs($r['adj_amt']), 2);
				$adj_list[$adj_id]['InvCode'] = $r['adj_amt'] < 0 ? '-' : '+';
				if($ostrio_gl_id>0){
					$adj_list[$adj_id]['ostrio_gl_id'] = $ostrio_gl_id;
				}
			}
		}
		$con->sql_freeresult($q1);
		//print_r($adj_list);
		
		if($adj_list){
			$data_fields = array('BranchCode', 'DocNo', 'DocDate', 'Status', 'Source', 'Adjustment_Type', 'ProductGroup', 'GLRemark', 'UpdateDate', 'UserID', 'AdjustAmt', 'InvCode', 'ImportFlag', 'ImportDate');
			foreach($adj_list as $adj_id => $r){
				$row_count++;
				$row_err_msg = array();
				$ostrio_gl_id = $r['ostrio_gl_id'];
				$r['ImportFlag'] = 0;
				$r['ImportDate'] = '';
				
				if($ostrio_gl_id){
					//unset($r['ostrio_gl_id']);
					// Update
					$success = $con2->exec("update CCT_GLSTA set ".mssql_update_by_field($r, $data_fields)." where ID=$ostrio_gl_id");
					if($success === false){
						//print "$sql\n";
						$err_msg = print_r($con2->errorInfo(), true);
						$row_err_msg[] = $err_msg;
					}
				}else{
					// Insert
					$success = $con2->exec("insert into CCT_GLSTA ".mssql_insert_by_field($r, $data_fields));
					if($success === false){	// Insert Failed
						//print "$sql\n";
						$err_msg = print_r($con2->errorInfo(), true);
						$row_err_msg[] = $err_msg;
					}else{					
						// Get inserted data
						$sth = $con2->query("select ID from CCT_GLSTA where BranchCode=".ms($bcode)." and DocNo=".ms($r['DocNo'])." order by id desc");
						$temp = $sth->fetch(PDO::FETCH_ASSOC);
						$sth->closeCursor();
						$ostrio_gl_id = $temp['ID'];
						if(!$ostrio_gl_id){	// Cant Get Inserted ID
							$err_msg = print_r($con2->errorInfo(), true);
							$row_err_msg[] = $err_msg;
						}
					}
				}
				
				if(!$row_err_msg){
					// Update Mapping Info
					$upd2 = array();
					$upd2['branch_id'] = $bid;
					$upd2['doc_id'] = $adj_id;
					$upd2['acc_doc_no'] = $r['DocNo'];
					$upd2['doc_date'] = $r['DocDate'];
					$upd2['dept_id'] = $r['dept_id'];
					$upd2['gl_type'] = 'adjustment';
					$upd2['amount'] = $r['InvCode']=='-' ? $r['AdjustAmt']*-1 : $r['AdjustAmt'];
					$upd2['ostrio_gl_id'] = $ostrio_gl_id;
					$upd2['last_update'] = 'CURRENT_TIMESTAMP';
					$upd2['api_data'] = serialize($r);
					$con->sql_query("replace into ostrio_gl_mapping ".mysql_insert_by_field($upd2));
				}else{
					print_r($row_err_msg);
				
					$error_count++;
					$err_msg_list = array_merge($err_msg_list, $row_err_msg);
				}
			}
		}
		
		// Mark Finish
		$this->set_status($bid, $integration_type, $sub_type, 1, $err_msg_list);
		
		print "$row_count Adjustment processed.\n";
		if($error_count>0)	print "$error_count row got error.\n";
	}
	
	private function sync_gl_by_branch_do($bid){
		global $con, $con2, $config;
		
		$this->connect_os_trio();
		
		// Mark Start
		$integration_type = 'gl';
		$sub_type = 'do';
		$this->set_status($bid, $integration_type, $sub_type, 0);
		
		$bcode = trim($this->b_list[$bid]['code']);
		$report_prefix = trim($this->b_list[$bid]['report_prefix']);
		print "Updating Delivery Order (".$bcode.")...\n";
		
		// Adjustment
		$filter = array();
		$filter[] = "do.branch_id=$bid and do.active=1 and do.status=1 and do.approved=1 and do.checkout=1";
		
		if(!$this->resend){
			$filter[] = "glm.ostrio_gl_id is null";	// Only new data
		}
		if($this->date_from){
			$filter[] = "do.do_date>=".ms($this->date_from);
		}
		if($this->date_to){
			$filter[] = "do.do_date<=".ms($this->date_to);
		}
		
		if($this->integration_start_date){
			$filter[] = "do.do_date>=".ms($this->integration_start_date);
		}
		
		$str_filter = "where ".join(' and ', $filter);
		$row_count = $error_count = 0;
		$err_msg_list = array();
		
		$sql = "select do.*, dept.id as dept_id, dept.description as dept_name, glm.ostrio_gl_id,
			(
				select sum((di.ctn*uom.fraction + di.pcs)*di.cost) 
				from do_items di
				left join uom on uom.id=di.uom_id
				where di.branch_id=do.branch_id and di.do_id=do.id
			) as do_cost
			from do
			left join ostrio_gl_mapping glm on glm.branch_id=do.branch_id and glm.doc_id=do.id and glm.gl_type='do'
			left join category dept on dept.id=do.dept_id
			$str_filter
			order by do.id";
		//print $sql."\n";
		$q1 = $con->sql_query($sql);
		$do_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$do_id = mi($r['id']);
			$ostrio_gl_id = mi($r['ostrio_gl_id']);
			
			if(!isset($do_list[$do_id])){
				$do_list[$do_id]['dept_id'] = $r['dept_id'];
				$do_list[$do_id]['BranchCode'] = $bcode;
				$do_list[$do_id]['DocNo'] = 'DO-'.$r['do_no'];
				$do_list[$do_id]['DocDate'] = $r['do_date'];
				$do_list[$do_id]['Status'] = 'A';
				$do_list[$do_id]['Source'] = 'STA';
				$do_list[$do_id]['Adjustment_Type'] = 'DELIVERY ORDER';
				$do_list[$do_id]['ProductGroup'] = $r['dept_name'];
				$do_list[$do_id]['GLRemark'] = 'Inventory Post from ARMS';
				$do_list[$do_id]['UpdateDate'] = date("Y-m-d");
				$do_list[$do_id]['UserID'] = 'ARMS';
				$do_list[$do_id]['AdjustAmt'] = round($r['do_cost'], 2);
				$do_list[$do_id]['InvCode'] = '-';
				if($ostrio_gl_id>0){
					$do_list[$do_id]['ostrio_gl_id'] = $ostrio_gl_id;
				}
				
				// DO no Department
				if(!$r['dept_id']){
					// Get department from first item
					$con->sql_query("select dept.description as dept_name
						from do_items di
						join sku_items si on si.id=di.sku_item_id
						join sku on sku.id=si.sku_id
						join category c on c.id=sku.category_id
						join category dept on dept.id=c.department_id
						where di.branch_id=$bid and di.do_id=$do_id order by di.id limit 1");
					$tmp = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($tmp['dept_name'])	$do_list[$do_id]['ProductGroup'] = $tmp['dept_name'];
				}
			}
			
			
		}
		$con->sql_freeresult($q1);
		
		//print_r($do_list);
		
		if($do_list){
			$data_fields = array('BranchCode', 'DocNo', 'DocDate', 'Status', 'Source', 'Adjustment_Type', 'ProductGroup', 'GLRemark', 'UpdateDate', 'UserID', 'AdjustAmt', 'InvCode', 'ImportFlag', 'ImportDate');
			foreach($do_list as $do_id => $r){
				$row_count++;
				$row_err_msg = array();
				$ostrio_gl_id = $r['ostrio_gl_id'];
				$r['ImportFlag'] = 0;
				$r['ImportDate'] = '';
				
				if($ostrio_gl_id){
					unset($r['ostrio_gl_id']);
					// Update
					$success = $con2->exec("update CCT_GLSTA set ".mssql_update_by_field($r, $data_fields)." where ID=$ostrio_gl_id");
					if($success === false){
						//print "$sql\n";
						$err_msg = print_r($con2->errorInfo(), true);
						$row_err_msg[] = $err_msg;
					}
				}else{
					// Insert
					$success = $con2->exec("insert into CCT_GLSTA ".mssql_insert_by_field($r, $data_fields));
					if($success === false){	// Insert Failed
						//print "$sql\n";
						$err_msg = print_r($con2->errorInfo(), true);
						$row_err_msg[] = $err_msg;
					}else{					
						// Get inserted data
						$sth = $con2->query("select ID from CCT_GLSTA where BranchCode=".ms($bcode)." and DocNo=".ms($r['DocNo'])." order by id desc");
						$temp = $sth->fetch(PDO::FETCH_ASSOC);
						$sth->closeCursor();
						$ostrio_gl_id = $temp['ID'];
						if(!$ostrio_gl_id){	// Cant Get Inserted ID
							$err_msg = print_r($con2->errorInfo(), true);
							$row_err_msg[] = $err_msg;
						}
					}
				}
				
				if(!$row_err_msg){
					// Update Mapping Info
					$upd2 = array();
					$upd2['branch_id'] = $bid;
					$upd2['doc_id'] = $do_id;
					$upd2['acc_doc_no'] = $r['DocNo'];
					$upd2['doc_date'] = $r['DocDate'];
					$upd2['dept_id'] = $r['dept_id'];
					$upd2['gl_type'] = 'do';
					$upd2['ostrio_gl_id'] = $ostrio_gl_id;
					$upd2['amount'] = $r['InvCode']=='-' ? $r['AdjustAmt']*-1 : $r['AdjustAmt'];
					$upd2['last_update'] = 'CURRENT_TIMESTAMP';
					$upd2['api_data'] = serialize($r);
					$con->sql_query("replace into ostrio_gl_mapping ".mysql_insert_by_field($upd2));
				}else{
					print_r($row_err_msg);
				
					$error_count++;
					$err_msg_list = array_merge($err_msg_list, $row_err_msg);
				}		
			}
		}
		
		// Mark Finish
		$this->set_status($bid, $integration_type, $sub_type, 1, $err_msg_list);
		
		print "$row_count DO processed.\n";
		if($error_count>0)	print "$error_count row got error.\n";
	}
	
	private function sync_gl_by_branch_pos($bid){
		global $con, $con2, $config;
		
		$this->connect_os_trio();
		
		// Mark Start
		$integration_type = 'gl';
		$sub_type = 'pos';
		$this->set_status($bid, $integration_type, $sub_type, 0);
		
		$bcode = trim($this->b_list[$bid]['code']);
		print "Updating POS (".$bcode.")...\n";
		
		// Adjustment
		$filter = array();
		//$filter[] = "p.category_id>0";
		
		if(!$this->resend){
			$filter[] = "glm.ostrio_gl_id is null";	// Only new data
		}
		if($this->date_from){
			$filter[] = "p.date>=".ms($this->date_from);
		}
		if($this->date_to){
			$filter[] = "p.date<=".ms($this->date_to);
		}
		
		if($this->integration_start_date){
			$filter[] = "p.date>=".ms($this->integration_start_date);
		}
		
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		$row_count = $error_count = 0;
		$err_msg_list = array();
		
		$tbl = 'category_sales_cache_b'.$bid;
		$sql = "select p.date, dept.id as dept_id, dept.description as dept_name, sum(p.cost) as total_cost, glm.ostrio_gl_id
			from $tbl p
			left join category c on c.id=p.category_id
			left join category dept on dept.id=c.department_id
			left join ostrio_gl_mapping glm on glm.branch_id=$bid and glm.doc_date=p.date and glm.gl_type='pos' and glm.dept_id=dept.id
			$str_filter
			group by p.date, dept.id
			order by p.date, dept_name";
		//print $sql."\n";exit;
		$q1 = $con->sql_query($sql);
		$data_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$dept_id = mi($r['dept_id']);
			$doc_id = $dept_id.date("Ymd", strtotime($r['date']));
			$ostrio_gl_id = mi($r['ostrio_gl_id']);
			
			if(!isset($data_list[$doc_id])){
				$data_list[$doc_id]['dept_id'] = $r['dept_id'];
				$data_list[$doc_id]['BranchCode'] = $bcode;
				$data_list[$doc_id]['DocNo'] = 'POS-'.date("Ymd", strtotime($r['date'])).'/'.$dept_id;
				$data_list[$doc_id]['DocDate'] = $r['date'];
				$data_list[$doc_id]['Status'] = 'A';
				$data_list[$doc_id]['Source'] = 'STA';
				$data_list[$doc_id]['Adjustment_Type'] = 'POS';
				$data_list[$doc_id]['ProductGroup'] = $r['dept_name'];
				$data_list[$doc_id]['GLRemark'] = 'Inventory Post from ARMS';
				$data_list[$doc_id]['UpdateDate'] = date("Y-m-d");
				$data_list[$doc_id]['UserID'] = 'ARMS';
				$data_list[$doc_id]['AdjustAmt'] = round($r['total_cost'], 2);
				$data_list[$doc_id]['InvCode'] = '-';
				if($ostrio_gl_id>0){
					$data_list[$doc_id]['ostrio_gl_id'] = $ostrio_gl_id;
				}
			}			
		}
		$con->sql_freeresult($q1);
		
		//print_r($data_list);exit;
		
		if($data_list){
			$data_fields = array('BranchCode', 'DocNo', 'DocDate', 'Status', 'Source', 'Adjustment_Type', 'ProductGroup', 'GLRemark', 'UpdateDate', 'UserID', 'AdjustAmt', 'InvCode', 'ImportFlag', 'ImportDate');
			foreach($data_list as $doc_id => $r){
				$row_count++;
				$row_err_msg = array();
				$ostrio_gl_id = $r['ostrio_gl_id'];
				$r['ImportFlag'] = 0;
				$r['ImportDate'] = '';
				
				if($ostrio_gl_id){
					unset($r['ostrio_gl_id']);
					// Update
					$success = $con2->exec("update CCT_GLSTA set ".mssql_update_by_field($r, $data_fields)." where ID=$ostrio_gl_id");
					if($success === false){
						//print "$sql\n";
						$err_msg = print_r($con2->errorInfo(), true);
						$row_err_msg[] = $err_msg;
					}
				}else{
					// Insert
					$success = $con2->exec("insert into CCT_GLSTA ".mssql_insert_by_field($r, $data_fields));
					if($success === false){	// Insert Failed
						//print "$sql\n";
						$err_msg = print_r($con2->errorInfo(), true);
						$row_err_msg[] = $err_msg;
					}else{					
						// Get inserted data
						$sth = $con2->query("select ID from CCT_GLSTA where BranchCode=".ms($bcode)." and DocNo=".ms($r['DocNo'])." order by id desc");
						$temp = $sth->fetch(PDO::FETCH_ASSOC);
						$sth->closeCursor();
						$ostrio_gl_id = $temp['ID'];
						if(!$ostrio_gl_id){	// Cant Get Inserted ID
							$err_msg = print_r($con2->errorInfo(), true);
							$row_err_msg[] = $err_msg;
						}
					}
				}
				
				if(!$row_err_msg){
					// Update Mapping Info
					$upd2 = array();
					$upd2['branch_id'] = $bid;
					$upd2['doc_id'] = $doc_id;
					$upd2['acc_doc_no'] = $r['DocNo'];
					$upd2['doc_date'] = $r['DocDate'];
					$upd2['dept_id'] = $r['dept_id'];
					$upd2['gl_type'] = 'pos';
					$upd2['ostrio_gl_id'] = $ostrio_gl_id;
					$upd2['amount'] = $r['InvCode']=='-' ? $r['AdjustAmt']*-1 : $r['AdjustAmt'];
					$upd2['last_update'] = 'CURRENT_TIMESTAMP';
					$upd2['api_data'] = serialize($r);
					$con->sql_query("replace into ostrio_gl_mapping ".mysql_insert_by_field($upd2));
					//print "$sql\n";
				}else{
					print_r($row_err_msg);
				
					$error_count++;
					$err_msg_list = array_merge($err_msg_list, $row_err_msg);
				}
				
				
				
			}
		}
		
		// Mark Finish
		$this->set_status($bid, $integration_type, $sub_type, 1, $err_msg_list);
		
		print "$row_count POS processed.\n";
		if($error_count>0)	print "$error_count row got error.\n";
	}
	
	private function generate_new_cs_batch($bid, $date){
		global $con;
		
		$bcode = trim($this->b_list[$bid]['code']);
		
		$con->sql_query("select max(id) as max_id from ostrio_cs_batch where branch_id=$bid and date=".ms($date));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$new_id = mi($tmp['max_id'])+1;
		$cs_batch_no = $bcode.date("Ymd", strtotime($date)).'-'.$new_id;
		$upd = array();
		$upd['branch_id'] = $bid;
		$upd['date'] = $date;
		$upd['id'] = $new_id;
		$upd['cs_batch_no'] = $cs_batch_no; 
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into ostrio_cs_batch ".mysql_insert_by_field($upd));
		
		return $cs_batch_no;
		
	}
	
	private function sync_cs(){
		foreach($this->b_list as $bid => $b){
			$this->sync_cs_by_branch($bid);
		}
	}
	
	private function sync_cs_by_branch($bid){
		global $con, $con2, $config;
		
		$this->connect_os_trio();
		$bcode = trim($this->b_list[$bid]['code']);
		print "Cash Sales (".$bcode.")...\n";
		
		$this->sync_cs_by_branch_pos($bid);
		$this->sync_cs_by_branch_do($bid);
		
		print "Cash Sales Done.\n\n";
	}
	
	private function sync_cs_by_branch_pos($bid){
		global $con, $con2, $config;
		
		$this->connect_os_trio();
		
		$con->sql_begin_transaction();
		
		// Mark Start
		$integration_type = 'cs';
		$sub_type = 'pos';
		$this->set_status($bid, $integration_type, $sub_type, 0);
		
		$bcode = trim($this->b_list[$bid]['code']);
		print "Updating POS (".$bcode.")...\n";
		
		// POS
		$filter = array();
		$filter[] = "p.branch_id=$bid and p.cancel_status=0";
		
		if(!$this->resend){
			$filter[] = "(csb.ostrio_cs_id is null or csb.ostrio_cs_id=0)";	// Only new data
		}
		if($this->date_from){
			$filter[] = "p.date>=".ms($this->date_from);
		}
		if($this->date_to){
			$filter[] = "p.date<=".ms($this->date_to);
		}
		
		if($this->integration_start_date){
			$filter[] = "p.date>=".ms($this->integration_start_date);
		}
		
		// Use 20 minutes to prevent user finalise counter collection and cron run together at the same time
		$finalize_timestamp = date("Y-m-d H:i:s", strtotime("-20 minutes"));
		
		$str_filter = "where ".join(' and ', $filter);
		$row_count = $error_count = 0;
		$err_msg_list = array();
		
		$sql = "select p.date, p.counter_id, p.id as pos_id, p.amount_change, p.receipt_ref_no, csb.ostrio_cs_id, csm.cs_batch_no,
			(p.service_charges-p.service_charges_gst_amt) gross_service_charge_amt, p.service_charges_gst_amt, p.deposit, pd.deposit_amount
			from pos p
			left join ostrio_cs_mapping csm on csm.branch_id=p.branch_id and csm.doc_no=p.receipt_ref_no and csm.cs_type='pos'
			left join ostrio_cs_batch csb on csb.branch_id=csm.branch_id and csb.cs_batch_no=csm.cs_batch_no
			join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date and pf.finalized=1 and finalize_timestamp<=".ms($finalize_timestamp)."
			left join pos_deposit pd on p.branch_id=pd.branch_id and p.date=pd.date and p.counter_id=pd.counter_id and p.id=pd.pos_id and p.deposit=1
			$str_filter
			order by p.date, p.counter_id, p.id";
		//print $sql."\n";exit;
		$data_list = $variances_list = array();
		$q1 = $con->sql_query($sql);
		$total_count = $con->sql_numrows();
		$current_count = 0;
		
		print "Get POS Data...\n";
		while($p = $con->sql_fetchassoc($q1)){
			$current_count++;
			print "\r$current_count / $total_count . . .";
			$pos_id = mi($p['pos_id']);
			$doc_no = $p['receipt_ref_no'];

			if(!isset($data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no])){
				$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no] = array();
				$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['counter_id'] = $p['counter_id'];
				$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['pos_id'] = $p['pos_id'];
				$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['doc_no'] = $doc_no;
				$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['ostrio_cs_id'] = $p['ostrio_cs_id'];
				$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['cs_batch_no'] = $p['cs_batch_no'];
				$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['item_list'] = array();
				$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['discount'] = 0;
				$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['rounding'] = 0;
				$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['gross_service_charge_amt'] += $p['gross_service_charge_amt'];
				$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['service_charges_gst_amt'] += $p['service_charges_gst_amt'];
				$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['amount'] = 0;
				$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['payment_list'] = array();
				if($p['amount_change']){
					$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['payment_list']['CASH']['amt'] -= $p['amount_change'];
				}
				// POS Items
				$q_pi = $con->sql_query("select (pi.price-pi.discount-pi.discount2-pi.tax_amount) as gross_sales, pi.tax_amount, dept.id as dept_id, dept.description as dept_name
					from pos_items pi
					join sku_items si on si.id=pi.sku_item_id
					join sku on sku.id=si.sku_id
					left join category c on c.id=sku.category_id
					left join category dept on dept.id=c.department_id
					where pi.branch_id=$bid and pi.date=".ms($p['date'])." and pi.counter_id=".mi($p['counter_id'])." and pi.pos_id=$pos_id");
				while($pi = $con->sql_fetchassoc($q_pi)){
					// Sales By Transaction
					$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['item_list'][$pi['dept_id']]['dept_name'] = $pi['dept_name'];
					$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['item_list'][$pi['dept_id']]['gross_sales'] += $pi['gross_sales'];
					$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['item_list'][$pi['dept_id']]['tax_amount'] += $pi['tax_amount'];
				}
				$con->sql_freeresult($q_pi);
				
				// POS Payment
				$q_pp = $con->sql_query("select pp.* 
					from pos_payment pp
					where pp.branch_id=$bid and pp.date=".ms($p['date'])." and pp.counter_id=".mi($p['counter_id'])." and pp.pos_id=$pos_id and pp.adjust=0");
				while($pp = $con->sql_fetchassoc($q_pp)){
					$pp_type = strtoupper($pp['type']);
					if($pp_type == 'MIX & MATCH TOTAL DISC')	$pp_type = 'DISCOUNT';	// Convert Mix and Match Discount to Discount
					
					$rm_amt = $pp['amount'];
					if($pp_type=='DISCOUNT'){	// Discount
						$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['discount'] += $rm_amt;
					}elseif($pp_type == 'ROUNDING'){	// Rounding
						$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['rounding'] += $rm_amt;
					}elseif($pp_type == 'CURRENCY_ADJUST'){	// Currency Adjust
						$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['currency_adjust'] += $rm_amt;
					}else{	// Payment
						if($pp['group_type'] == 'currency'){	// Foreign Currency
							// Convert foreign to base
							list($foreign_amt, $rate) = explode('@', $pp['remark']);
							$rm_amt = round($foreign_amt / $rate, 2);
							$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['payment_list'][$pp_type]['amt'] += $rm_amt;
							$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['payment_list'][$pp_type]['currrency'] = 1;
						}else{	// Base Currency
							$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['payment_list'][$pp_type]['amt'] += $rm_amt;
						}
					}
				}
				$con->sql_freeresult($q_pp);
				
				// Got Currency Adjust
				if($data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['currency_adjust']){
					// Loop payment type
					foreach($data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['payment_list'] as $pp_type => $pp){
						if($pp['currrency']){
							// Adjust into currency
							$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['payment_list'][$pp_type]['amt'] += $data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['currency_adjust'];
							unset($data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['currency_adjust']);
							break;
						}
					}
				}
				
				// Deposit Received
				if($p['deposit']){					
					$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['deposit_rcv_amount'] += $p['deposit_amount'];
					$data_list[$p['date']][$p['cs_batch_no']]['doc_list'][$doc_no]['amount'] -= $p['deposit_amount'];
					$data_list[$p['date']][$p['cs_batch_no']]['deposit_rcv_amount'] += $p['deposit_amount'];
				}
			}
			
			// Variances By Day
			if(!isset($variances_list[$p['date']])){
				// Get Variances By branch by day
				$q_pcf = $con->sql_query("select variance from pos_counter_finalize where branch_id=$bid and date=".ms($p['date']));
				while($pcf = $con->sql_fetchassoc($q_pcf)){
					$pcf['variance'] = unserialize($pcf['variance']);
					if($pcf['variance']){
						// Loop each payment type
						foreach($pcf['variance'] as $tmp_pp_type => $variance){
							$pp_type = strtoupper($tmp_pp_type);
							if($pp_type == 'NETT_SALES')	continue;	// skip nett sales, nett sales is total, we need by payment type
							
							if(!$variance['amt'])	continue;	// skip zero denom
							
							$variances_list[$p['date']]['variances'][$pp_type]['amt'] += $variance['amt'];
						}
					}
				}
				$con->sql_freeresult($q_pcf);
			}
		}
		$con->sql_freeresult($q1);
		print "-- Done\n";
		
		//print_r($variances_list);exit;
		
		if($data_list){
			// Loop to Update Mapping
			print "Create Mapping...";
			foreach($data_list as $d => $batch_list){	// Loop Data by Date
				foreach($batch_list as $cs_batch_no => $batch_info){	// Loop Data by Batch
					$new_cs_batch_no = '';
					$doc_line_no = 0;
					$row_err_msg = array();
					if($cs_batch_no){
						$data_list[$d][$cs_batch_no]['use_cs_batch_no'] = $cs_batch_no;
					}
					if(!$cs_batch_no && !$new_cs_batch_no){
						// No Batch - Need to generate new batch
						$new_cs_batch_no = $this->generate_new_cs_batch($bid, $d);
						if($new_cs_batch_no){
							$data_list[$d][$cs_batch_no]['use_cs_batch_no'] = $new_cs_batch_no;
						}
					}
					if(!$new_cs_batch_no && !$cs_batch_no){
						$row_err_msg[] = "POS Date: $d unable to create new batch";
						break;
					}
					
					if(!$row_err_msg){
						foreach($batch_info['doc_list'] as $doc_no => $r){	// Loop Document
							if($r['gross_service_charge_amt']){
								$data_list[$d][$cs_batch_no]['gross_service_charge_amt'] += $r['gross_service_charge_amt'];
								$data_list[$d][$cs_batch_no]['service_charges_gst_amt'] += $r['service_charges_gst_amt'];
								
								$data_list[$d][$cs_batch_no]['gross_sales'] += $r['gross_service_charge_amt'];
								$data_list[$d][$cs_batch_no]['tax_amount'] += $r['service_charges_gst_amt'];
							}
							// Loop Items (Department)
							foreach($r['item_list'] as $dept_id => $dept_sales){
								$data_list[$d][$cs_batch_no]['item_list'][$dept_id]['dept_name'] = $dept_sales['dept_name'];
								$data_list[$d][$cs_batch_no]['item_list'][$dept_id]['gross_sales'] += $dept_sales['gross_sales'];
								$data_list[$d][$cs_batch_no]['item_list'][$dept_id]['tax_amount'] += $dept_sales['tax_amount'];
								
								$data_list[$d][$cs_batch_no]['gross_sales'] += $dept_sales['gross_sales'];
								$data_list[$d][$cs_batch_no]['tax_amount'] += $dept_sales['tax_amount'];
							}
							
							// Loop Payment
							foreach($r['payment_list'] as $pp_type => $pp){
								$data_list[$d][$cs_batch_no]['payment_list'][$pp_type]['amt'] += $pp['amt'];
								$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['amount'] += $pp['amt'];
								
								// Payment store by doc_no if not cash
								if($this->cs_payment_by_receipt && $pp_type != 'CASH'){
									$data_list[$d][$cs_batch_no]['payment_list'][$pp_type]['doc_list'][$doc_no]['amt'] += $pp['amt'];
								}
							}
							
							if($r['discount']){
								$data_list[$d][$cs_batch_no]['discount'] += $r['discount'];
							}
							if($r['rounding']){
								$data_list[$d][$cs_batch_no]['rounding'] += $r['rounding'];
							}
							
							// Update Mapping
							$upd = array();
							$upd['branch_id'] = $bid;
							$upd['doc_date'] = $d;
							$upd['cs_batch_no'] = $new_cs_batch_no ? $new_cs_batch_no : $cs_batch_no;
							$upd['doc_no'] = $doc_no;
							$upd['cs_type'] = 'pos';
							$upd['amount'] = $data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['amount'];
							$upd['last_update'] = 'CURRENT_TIMESTAMP';
							$con->sql_query("replace into ostrio_cs_mapping ".mysql_insert_by_field($upd));
						}
					}
				}
			}
			print "-- Done\n";
			
			//print_r($data_list);exit;
			// Loop to Push Data to OS Trio
			print "Upload Data...";
			foreach($data_list as $d => $batch_list){	// Loop Data by Date
				foreach($batch_list as $cs_batch_no => $batch_info){	// Loop Data by Batch
					$row_count++;
					$row_err_msg = array();
					// Got Error
					if(isset($batch_info['err_msg_list']) && $batch_info['err_msg_list']){
						$row_err_msg = $batch_info['err_msg_list'];
					}
					
					$api_data = array();
					$upd = array();
					
					$total_sales_amt = $total_payment_amt = 0;
					
					if(!$row_err_msg){
						list($ymd, $batch_id) = explode('-', $batch_info['use_cs_batch_no']);
						$upd['BranchCode'] = $bcode;
						$upd['DocNo'] = 'Z'.date("Ymd", strtotime($d)).sprintf("%02d", $batch_id);
						$upd['DocLineNo'] = 0;
						$upd['OutletID'] = $bcode;
						$upd['Location'] = $bcode;
						$upd['TerminalID'] = 'POS';
						$upd['ShiftCode'] = date("Ymd", strtotime($d)).$batch_id;
						$upd['ShiftTime'] = date("H:i:s", strtotime("+$batch_id minute", strtotime($d)));
						$upd['TransDate'] = date("m/d/Y", strtotime($d));
						$upd['TransType'] = 'C';
						$upd['TotalCount'] = 0;
						
						// Items
						$item_no = 0;
						foreach($batch_info['item_list'] as $dept_id => $dept_sales){
							$upd['DocLineNo']++;
							$upd['TotalCount']++;
							$item_no++;
							
							$upd_sales = array();
							$upd_sales['ItemNo'] = $item_no;
							$upd_sales['StockCode'] = $dept_sales['dept_name'];
							$upd_sales['Description'] = $dept_sales['dept_name'];
							$upd_sales['TaxCode'] = $dept_sales['tax_amount'] ? 'SR' : 'ZR';
							$upd_sales['Quantity'] = 1;
							$upd_sales['NetAmt'] = $upd_sales['Price'] = $dept_sales['gross_sales'];
							$upd_sales['GSTAmt'] = $dept_sales['tax_amount'];
							
							if($upd_sales['ItemNo']==1 && $batch_info['gross_service_charge_amt']){	// Add Service into first line only
								$upd_sales['SVCAmt'] += $batch_info['gross_service_charge_amt'];
								$upd_sales['GSTAmt'] += $batch_info['service_charges_gst_amt'];
							}
							
							$upd_sales['NetAmtGST'] = $upd_sales['PriceGST'] = $upd_sales['NetAmt']+$upd_sales['SVCAmt']+$upd_sales['GSTAmt'];
							
							$total_sales_amt += $upd_sales['NetAmt'] + $upd_sales['SVCAmt'];
							$upd_api = array_merge($upd, $upd_sales);
							$api_data[] = $upd_api;
						}
						
						// Deposit Received
						if($batch_info['deposit_rcv_amount']){
							$upd['DocLineNo']++;
							$upd['TotalCount']++;
							$item_no++;
							
							$upd_deposit_rcv = array();
							$upd_deposit_rcv['ItemNo'] = $item_no;
							$upd_deposit_rcv['StockCode'] = 'DEPOSIT';
							$upd_deposit_rcv['Description'] = 'DEPOSIT';
							$upd_deposit_rcv['TaxCode'] = 'ZR';
							$upd_deposit_rcv['Quantity'] = 1;
							$upd_deposit_rcv['NetAmt'] = $upd_deposit_rcv['Price'] = $batch_info['deposit_rcv_amount'];
							$upd_deposit_rcv['PriceGST'] = $upd_deposit_rcv['NetAmtGST'] = $batch_info['deposit_rcv_amount'];
														
							$upd_api = array_merge($upd, $upd_deposit_rcv);
							$api_data[] = $upd_api;
						}
						
						// Rounding
						if($batch_info['rounding']){
							$rounding_amt = round($batch_info['rounding'], 2);
							$upd['DocLineNo']++;
							$upd['TotalCount']++;
							
							$upd_rounding = array();
							$upd_rounding['ItemNo'] = 'TP';
							$upd_rounding['StockCode'] = 'SYSRND';
							$upd_rounding['Description'] = 'ROUNDING';
							$upd_rounding['Quantity'] = 1;
							$upd_rounding['NetAmt'] = $upd_rounding['Price'] = $rounding_amt;
							$upd_rounding['PriceGST'] = $upd_rounding['NetAmtGST'] = $rounding_amt;
							
							$total_sales_amt += $upd_rounding['NetAmt'];
							$upd_api = array_merge($upd, $upd_rounding);
							$api_data[] = $upd_api;
						}
						
						// Tax
						if($batch_info['tax_amount']){
							$upd['DocLineNo']++;
							$upd['TotalCount']++;
							
							$upd_tax = array();
							$upd_tax['ItemNo'] = 'TY';
							$upd_tax['StockCode'] = 'TY';
							$upd_tax['Description'] = 'Tax';
							$upd_tax['Quantity'] = 1;
							$upd_tax['NetAmt'] = $upd_tax['Price'] = $batch_info['tax_amount'];
							$upd_tax['PriceGST'] = $upd_tax['NetAmtGST'] = $batch_info['tax_amount'];
							
							$total_sales_amt += $upd_tax['NetAmt'];
							$upd_api = array_merge($upd, $upd_tax);
							$api_data[] = $upd_api;
						}
						
						// Discount
						if($batch_info['discount']){
							$disc_amt = round($batch_info['discount']*-1, 2);
							$upd['DocLineNo']++;
							$upd['TotalCount']++;
							
							$upd_disc = array();
							$upd_disc['ItemNo'] = 'DC';
							$upd_disc['StockCode'] = 'DC';
							$upd_disc['Description'] = 'Discount';
							$upd_disc['Quantity'] = 1;
							$upd_disc['NetAmt'] = $upd_disc['Price'] = $disc_amt;
							$upd_disc['PriceGST'] = $upd_disc['NetAmtGST'] = $disc_amt;
							
							$upd_api = array_merge($upd, $upd_disc);
							$api_data[] = $upd_api;
						}
						
						// Payment
						foreach($batch_info['payment_list'] as $pp_type => $pp){
							if($pp_type != 'CASH' && $this->cs_payment_by_receipt && isset($pp['doc_list']) && $pp['doc_list']){
								// Payment store by receipt
								foreach($pp['doc_list'] as $tmp_doc_no => $tmp_doc_pp){
									$upd['DocLineNo']++;
									$upd['TotalCount']++;
							
									$upd_pp = array();
									$upd_pp['ItemNo'] = 'ZP';
									$upd_pp['StockCode'] = $pp_type;
									$upd_pp['Description'] = $pp_type.'-'.$tmp_doc_no;
									$upd_pp['Quantity'] = 1;
									$upd_pp['NetAmt'] = $upd_pp['Price'] = $tmp_doc_pp['amt'];
									$upd_pp['PriceGST'] = $upd_pp['NetAmtGST'] = $tmp_doc_pp['amt'];
									
									$total_payment_amt += $upd_pp['NetAmt'];
									$upd_api = array_merge($upd, $upd_pp);
									$api_data[] = $upd_api;
								}
							}else{
								$upd['DocLineNo']++;
								$upd['TotalCount']++;
							
								$upd_pp = array();
								$upd_pp['ItemNo'] = 'ZP';
								$upd_pp['StockCode'] = $pp_type;
								$upd_pp['Description'] = $pp_type;
								$upd_pp['Quantity'] = 1;
								$upd_pp['NetAmt'] = $upd_pp['Price'] = $pp['amt'];
								$upd_pp['PriceGST'] = $upd_pp['NetAmtGST'] = $pp['amt'];
								
								$total_payment_amt += $upd_pp['NetAmt'];
								$upd_api = array_merge($upd, $upd_pp);
								$api_data[] = $upd_api;
							}
							
						}
						
						$total_sales_amt = round($total_sales_amt, 2);
						$total_payment_amt = round($total_payment_amt, 2);
						
						$variances_amt = $total_sales_amt - $total_payment_amt;
						$got_payment_type_variances = false;
						
						//print "total_sales_amt = $total_sales_amt\n";
						//print "total_payment_amt = $total_payment_amt\n";
						//print "variances_amt = $variances_amt\n";exit;
						
						// Variances
						if(isset($variances_list[$d])){
							// Put variance at the first batch of the same day, so it won't have multiple variance in same day
							$tmp_variances_data = $variances_list[$d];	// Clone variance data
							unset($variances_list[$d]); // remove from the list
							
							if(isset($tmp_variances_data['variances'])){
								foreach($tmp_variances_data['variances'] as $pp_type => $variance_data){
									$upd['DocLineNo']++;
									$upd['TotalCount']++;
							
									// Variances by Payment Type
									$upd_var = array();
									$upd_var['ItemNo'] = 'ZP';
									$upd_var['StockCode'] = $pp_type.' VARIANCE';
									$upd_var['Description'] = $pp_type.' VARIANCE';
									$upd_var['Quantity'] = 1;
									$upd_var['NetAmt'] = $upd_var['Price'] = $variance_data['amt'];
									$upd_var['PriceGST'] = $upd_var['NetAmtGST'] = $variance_data['amt'];
									$upd_api = array_merge($upd, $upd_var);
									$api_data[] = $upd_api;
							
									// Total Variances
									$variances_amt += $variance_data['amt'];
									$got_payment_type_variances = true;
								}
							}
						}
						
						if($variances_amt){
							$variances_amt = round($variances_amt, 2);
							$upd['DocLineNo']++;
							$upd['TotalCount']++;
							
							$upd_var = array();
							if($got_payment_type_variances){
								$item_no++;
								$upd_var['ItemNo'] = $item_no;
							}else{
								$upd_var['ItemNo'] = 'TP';
							}							
							
							$upd_var['StockCode'] = 'VARIANCE';
							$upd_var['Description'] = 'VARIANCE';
							$upd_var['TaxCode'] = 'ZR';
							$upd_var['Quantity'] = 1;
							$upd_var['NetAmt'] = $upd_var['Price'] = $variances_amt;
							$upd_var['PriceGST'] = $upd_var['NetAmtGST'] = $variances_amt;
							$upd_api = array_merge($upd, $upd_var);
							$api_data[] = $upd_api;
						}
					}
					
					//print_r($api_data);exit;
					
					if($api_data){
						// Update Total Count
						$total_line = count($api_data);
						$first_ostrio_cs_id = '';
						foreach($api_data as $key => $r){
							$ostrio_cs_id = '';
							if(isset($batch_info['err_msg_list']) && $batch_info['err_msg_list'])	$row_err_msg = $batch_info['err_msg_list'];
							$api_data[$key]['TotalCount'] = $r['TotalCount'] = $total_line;
							
							$sql = "insert into POS_FINAL ".mssql_insert_by_field($r);
							//print "$sql\n";
							$con2->exec($sql);
							if($success === false){	// Insert Failed
								$err_msg = print_r($con2->errorInfo(), true);
								$row_err_msg[] = $err_msg;
							}else{					
								// Get inserted data
								$sth = $con2->query("select top 1 ID from POS_FINAL where BranchCode=".ms($bcode)." and DocNo=".ms($r['DocNo'])." and DocLineNo=".ms($r['DocLineNo'])." order by ID desc");
								$temp = $sth->fetch(PDO::FETCH_ASSOC);
								$sth->closeCursor();
								$ostrio_cs_id = $temp['ID'];
								if(!$ostrio_cs_id){	// Cant Get Inserted ID
									$err_msg = print_r($con2->errorInfo(), true);
									$row_err_msg[] = $err_msg;
								}else{
									if(!$first_ostrio_cs_id){
										$first_ostrio_cs_id = $ostrio_cs_id;
									}
								}
								
							}
						}
					}
					
					if(!$row_err_msg){
						// Update Mapping Info
						$upd2 = array();
						$upd2['acc_doc_no'] = $upd['DocNo'];
						$upd2['ostrio_cs_id'] = $first_ostrio_cs_id;
						$upd2['acc_doc_no'] = $r['DocNo'];
						$upd2['last_update'] = 'CURRENT_TIMESTAMP';
						$upd2['api_data'] = serialize($api_data);
						$con->sql_query("update ostrio_cs_batch set ".mysql_update_by_field($upd2)." where branch_id=$bid and date=".ms($d)." and cs_batch_no=".ms($batch_info['use_cs_batch_no']));
					}else{
						print_r($row_err_msg);
					
						$error_count++;
						$err_msg_list = array_merge($err_msg_list, $row_err_msg);
					}
				}
			}
			print "-- Done\n";
		}
		
		// Mark Finish
		$this->set_status($bid, $integration_type, $sub_type, 1, $err_msg_list);
		
		$con->sql_commit();
		
		print "$row_count POS processed.\n";
		if($error_count>0)	print "$error_count row got error.\n";
	}
	
	private function sync_cs_by_branch_do($bid){
		global $con, $con2, $config;
		
		$this->connect_os_trio();
		
		$con->sql_begin_transaction();
		
		// Mark Start
		$integration_type = 'cs';
		$sub_type = 'do';
		$this->set_status($bid, $integration_type, $sub_type, 0);
		
		$bcode = trim($this->b_list[$bid]['code']);
		print "Updating Transfer & Cash Sales DO (".$bcode.")...\n";
		
		// POS
		$filter = array();
		$filter[] = "do.branch_id=$bid and do.active=1 and do.status=1 and do.approved=1 and do.checkout=1 and do.inv_no<>'' and do.do_type in ('transfer', 'open')";
		
		if(!$this->resend){
			$filter[] = "(csb.ostrio_cs_id is null or csb.ostrio_cs_id=0)";	// Only new data
		}
		if($this->date_from){
			$filter[] = "do.do_date>=".ms($this->date_from);
		}
		if($this->date_to){
			$filter[] = "do.do_date<=".ms($this->date_to);
		}
		
		if($this->integration_start_date){
			$filter[] = "do.do_date>=".ms($this->integration_start_date);
		}
		
		$str_filter = "where ".join(' and ', $filter);
		$row_count = $error_count = 0;
		$err_msg_list = array();
		
		$sql = "select do.*, dept.id as dept_id, dept.description as dept_name, csb.ostrio_cs_id, csm.cs_batch_no
			from do
			left join ostrio_cs_mapping csm on csm.branch_id=do.branch_id and csm.doc_no=do.inv_no and csm.cs_type='do'
			left join ostrio_cs_batch csb on csb.branch_id=csm.branch_id and csb.cs_batch_no=csm.cs_batch_no
			left join category dept on dept.id=do.dept_id
			$str_filter
			order by do.do_date, do.id";
		//print $sql."\n";
		$data_list = array();
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$do_id = mi($r['id']);
			$doc_no = $r['inv_no'];
			$d = $r['do_date'];
			$cs_batch_no = $r['cs_batch_no'];
		
			if(!isset($data_list[$d][$cs_batch_no]['doc_list'][$doc_no])){
				$data_list[$d][$cs_batch_no]['doc_list'][$doc_no] = array();
				$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['doc_no'] = $doc_no;
				$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['ostrio_cs_id'] = $r['ostrio_cs_id'];
				$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['cs_batch_no'] = $r['cs_batch_no'];
				$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['item_list'] = array();
				$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['amount'] = $r['total_inv_amt'];
				$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['payment_list'] = array();
				
				// Get items department
				$q2 = $con->sql_query("select di.*, dept.id as dept_id, dept.description as dept_name
					from do_items di
					left join sku_items si on si.id=di.sku_item_id
					left join sku on sku.id=si.sku_id
					left join category c on c.id=sku.category_id
					left join category dept on dept.id=c.department_id
					where di.branch_id=$bid and di.do_id=$do_id
					order by di.id");
				while($di = $con->sql_fetchassoc($q2)){
					$dept_id = mi($di['dept_id']);
					$dept_name = trim($di['dept_name']);
					
					// Items
					$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['item_list'][$dept_id]['dept_name'] = $dept_name;
					$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['item_list'][$dept_id]['gross_sales'] += $di['inv_line_gross_amt2'];
					$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['item_list'][$dept_id]['tax_amount'] += $di['inv_line_gst_amt2'];
				}
				$con->sql_freeresult($q2);
				
				// Get Open item
				$q3 = $con->sql_query("select di.*
					from do_open_items di
					where di.branch_id=$bid and di.do_id=$do_id
					order by di.id");
				$dept_id = 0;
				$dept_name = 'NO DEPT';
				while($di = $con->sql_fetchassoc($q3)){
					// Items
					$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['item_list'][$dept_id]['dept_name'] = $dept_name;
					$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['item_list'][$dept_id]['gross_sales'] += $di['inv_line_gross_amt2'];
					$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['item_list'][$dept_id]['tax_amount'] += $di['inv_line_gst_amt2'];
				}
				$con->sql_freeresult($q3);
					
				// Items
				//$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['item_list'][$r['dept_id']]['dept_name'] = $r['dept_name'];
				//$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['item_list'][$r['dept_id']]['gross_sales'] += $r['inv_total_gross_amt'];
				//$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['item_list'][$r['dept_id']]['tax_amount'] += $r['inv_total_gst_amt'];
			
				// Payment
				$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['payment_list']['CASH']['amt'] += $r['total_inv_amt'];
				if($r['total_round_inv_amt']){
					$data_list[$d][$cs_batch_no]['doc_list'][$doc_no]['rounding'] += $r['total_round_inv_amt'];
				}
				
			}
		}
		$con->sql_freeresult($q1);
		
		if($data_list){
			// Loop to Update Mapping
			foreach($data_list as $d => $batch_list){	// Loop Data by Date
				foreach($batch_list as $cs_batch_no => $batch_info){	// Loop Data by Batch
					$new_cs_batch_no = '';
					$doc_line_no = 0;
					$row_err_msg = array();
					if($cs_batch_no){
						$data_list[$d][$cs_batch_no]['use_cs_batch_no'] = $cs_batch_no;
					}
					if(!$cs_batch_no && !$new_cs_batch_no){
						// No Batch - Need to generate new batch
						$new_cs_batch_no = $this->generate_new_cs_batch($bid, $d);
						if($new_cs_batch_no){
							$data_list[$d][$cs_batch_no]['use_cs_batch_no'] = $new_cs_batch_no;
						}
					}
					if(!$new_cs_batch_no && !$cs_batch_no){
						$row_err_msg[] = "DO Date: $d unable to create new batch";
						break;
					}
					
					if(!$row_err_msg){
						foreach($batch_info['doc_list'] as $doc_no => $r){	// Loop Document
							// Loop Items (Department)
							foreach($r['item_list'] as $dept_id => $dept_sales){
								$data_list[$d][$cs_batch_no]['item_list'][$dept_id]['dept_name'] = $dept_sales['dept_name'];
								$data_list[$d][$cs_batch_no]['item_list'][$dept_id]['gross_sales'] += $dept_sales['gross_sales'];
								$data_list[$d][$cs_batch_no]['item_list'][$dept_id]['tax_amount'] += $dept_sales['tax_amount'];
								
								$data_list[$d][$cs_batch_no]['gross_sales'] += $dept_sales['gross_sales'];
								$data_list[$d][$cs_batch_no]['tax_amount'] += $dept_sales['tax_amount'];
							}
							
							// Loop Payment
							foreach($r['payment_list'] as $pp_type => $pp){
								$data_list[$d][$cs_batch_no]['payment_list'][$pp_type]['amt'] += $pp['amt'];
							}
							
							// Rounding
							if($r['rounding']){
								$data_list[$d][$cs_batch_no]['rounding'] += $r['rounding'];
							}
							
							// Update Mapping
							$upd = array();
							$upd['branch_id'] = $bid;
							$upd['doc_date'] = $d;
							$upd['cs_batch_no'] = $new_cs_batch_no ? $new_cs_batch_no : $cs_batch_no;
							$upd['doc_no'] = $doc_no;
							$upd['cs_type'] = 'do';
							$upd['amount'] = $r['amount'];
							$upd['last_update'] = 'CURRENT_TIMESTAMP';
							$con->sql_query("replace into ostrio_cs_mapping ".mysql_insert_by_field($upd));
						}
					}
				}
			}
			
			//print_r($data_list);
			// Loop to Push Data to OS Trio
			foreach($data_list as $d => $batch_list){	// Loop Data by Date
				foreach($batch_list as $cs_batch_no => $batch_info){	// Loop Data by Batch
					$row_count++;
					$row_err_msg = array();
					// Got Error
					if(isset($batch_info['err_msg_list']) && $batch_info['err_msg_list']){
						$row_err_msg = $batch_info['err_msg_list'];
					}
					
					$api_data = array();
					$upd = array();
					
					if(!$row_err_msg){
						list($ymd, $batch_id) = explode('-', $batch_info['use_cs_batch_no']);
						$upd['BranchCode'] = $bcode;
						$upd['DocNo'] = 'Z'.date("Ymd", strtotime($d)).sprintf("%02d", $batch_id);
						$upd['DocLineNo'] = 0;
						$upd['OutletID'] = $bcode;
						$upd['Location'] = $bcode;
						$upd['TerminalID'] = 'DO';
						$upd['ShiftCode'] = date("Ymd", strtotime($d)).$batch_id;
						$upd['ShiftTime'] = date("H:i:s", strtotime("+$batch_id minute", strtotime($d)));
						$upd['TransDate'] = date("m/d/Y", strtotime($d));
						$upd['TransType'] = 'C';
						$upd['TotalCount'] = 0;
						
						// Items
						$item_no = 0;
						foreach($batch_info['item_list'] as $dept_id => $dept_sales){
							$upd['DocLineNo']++;
							$upd['TotalCount']++;
							$item_no++;
							
							$upd_sales = array();
							$upd_sales['ItemNo'] = $item_no;
							$upd_sales['StockCode'] = $dept_sales['dept_name'];
							$upd_sales['Description'] = $dept_sales['dept_name'];
							$upd_sales['TaxCode'] = $dept_sales['tax_amount'] ? 'SR' : 'ZR';
							$upd_sales['Quantity'] = 1;
							$upd_sales['NetAmt'] = $upd_sales['Price'] = $dept_sales['gross_sales'];
							$upd_sales['GSTAmt'] = $dept_sales['tax_amount'];							
							$upd_sales['NetAmtGST'] = $upd_sales['PriceGST'] = $upd_sales['NetAmt']+$upd_sales['SVCAmt']+$upd_sales['GSTAmt'];
							
							$upd_api = array_merge($upd, $upd_sales);
							$api_data[] = $upd_api;
						}
						
						// Rounding
						if($batch_info['rounding']){
							$upd['DocLineNo']++;
							$upd['TotalCount']++;
							
							$upd_rounding = array();
							$upd_rounding['ItemNo'] = 'TP';
							$upd_rounding['StockCode'] = 'SYSRND';
							$upd_rounding['Description'] = 'ROUNDING';
							$upd_rounding['Quantity'] = 1;
							$upd_rounding['NetAmt'] = $upd_rounding['Price'] = $batch_info['rounding'];
							$upd_rounding['PriceGST'] = $upd_rounding['NetAmtGST'] = $batch_info['rounding'];
							
							$upd_api = array_merge($upd, $upd_rounding);
							$api_data[] = $upd_api;
						}
						
						// Tax
						if($batch_info['tax_amount']){
							$upd['DocLineNo']++;
							$upd['TotalCount']++;
							
							$upd_tax = array();
							$upd_tax['ItemNo'] = 'TY';
							$upd_tax['StockCode'] = 'TY';
							$upd_tax['Description'] = 'Tax';
							$upd_tax['Quantity'] = 1;
							$upd_tax['NetAmt'] = $upd_tax['Price'] = $batch_info['tax_amount'];
							$upd_tax['PriceGST'] = $upd_tax['NetAmtGST'] = $batch_info['tax_amount'];
							
							$upd_api = array_merge($upd, $upd_tax);
							$api_data[] = $upd_api;
						}
						
						// Payment
						foreach($batch_info['payment_list'] as $pp_type => $pp){
							$upd['DocLineNo']++;
							$upd['TotalCount']++;
							
							$upd_pp = array();
							$upd_pp['ItemNo'] = 'ZP';
							$upd_pp['StockCode'] = $pp_type;
							$upd_pp['Description'] = $pp_type;
							$upd_pp['Quantity'] = 1;
							$upd_pp['NetAmt'] = $upd_pp['Price'] = $pp['amt'];
							$upd_pp['PriceGST'] = $upd_pp['NetAmtGST'] = $pp['amt'];
							
							$upd_api = array_merge($upd, $upd_pp);
							$api_data[] = $upd_api;
						}
					}
					//print_r($api_data);
					
					if($api_data){
						// Update Total Count
						$total_line = count($api_data);
						$first_ostrio_cs_id = '';
						foreach($api_data as $key => $r){
							$ostrio_cs_id = '';
							if(isset($batch_info['err_msg_list']) && $batch_info['err_msg_list'])	$row_err_msg = $batch_info['err_msg_list'];
							$api_data[$key]['TotalCount'] = $r['TotalCount'] = $total_line;
							
							$con2->exec($sql = "insert into POS_FINAL ".mssql_insert_by_field($r));
							if($success === false){	// Insert Failed
								//print "Failed: $sql\n";
								$err_msg = print_r($con2->errorInfo(), true);
								$row_err_msg[] = $err_msg;
							}else{
								//print "Success: $sql\n";
								// Get inserted data
								$sth = $con2->query("select top 1 ID from POS_FINAL where BranchCode=".ms($bcode)." and DocNo=".ms($r['DocNo'])." and DocLineNo=".ms($r['DocLineNo'])." order by ID desc");
								$temp = $sth->fetch(PDO::FETCH_ASSOC);
								$sth->closeCursor();
								$ostrio_cs_id = $temp['ID'];
								if(!$ostrio_cs_id){	// Cant Get Inserted ID
									$err_msg = print_r($con2->errorInfo(), true);
									$row_err_msg[] = $err_msg;
								}else{
									if(!$first_ostrio_cs_id){
										$first_ostrio_cs_id = $ostrio_cs_id;
									}
								}
								
							}
						}
					}
					
					if(!$row_err_msg){
						// Update Mapping Info
						$upd2 = array();
						$upd2['acc_doc_no'] = $upd['DocNo'];
						$upd2['ostrio_cs_id'] = $first_ostrio_cs_id;
						$upd2['acc_doc_no'] = $r['DocNo'];
						$upd2['last_update'] = 'CURRENT_TIMESTAMP';
						$upd2['api_data'] = serialize($api_data);
						$con->sql_query("update ostrio_cs_batch set ".mysql_update_by_field($upd2)." where branch_id=$bid and date=".ms($d)." and cs_batch_no=".ms($batch_info['use_cs_batch_no']));
					}else{
						print_r($row_err_msg);
					
						$error_count++;
						$err_msg_list = array_merge($err_msg_list, $row_err_msg);
					}
				}
			}
		}
		
		// Mark Finish
		$this->set_status($bid, $integration_type, $sub_type, 1, $err_msg_list);
		
		$con->sql_commit();
		
		print "$row_count DO processed.\n";
		if($error_count>0)	print "$error_count row got error.\n";
	}
	
	private function sync_ar(){
		foreach($this->b_list as $bid => $b){
			$this->sync_ar_by_branch($bid);
		}
	}
	
	private function sync_ar_by_branch($bid){
		global $con, $con2, $config;
		
		$this->connect_os_trio();
		$bcode = trim($this->b_list[$bid]['code']);
		print "AR (".$bcode.")...\n";
		
		$this->sync_ar_by_branch_do($bid);
		
		print "AR Done.\n\n";
	}
	
	private function sync_ar_by_branch_do($bid){
		global $con, $con2, $config;
		
		$this->connect_os_trio();
		
		$con->sql_begin_transaction();
		
		// Mark Start
		$integration_type = 'ar';
		$sub_type = 'do';
		$this->set_status($bid, $integration_type, $sub_type, 0);
		
		$bcode = trim($this->b_list[$bid]['code']);
		print "Updating Credit Sales DO (".$bcode.")...\n";
		
		// POS
		$filter = array();
		$filter[] = "do.branch_id=$bid and do.active=1 and do.status=1 and do.approved=1 and do.checkout=1 and do.inv_no<>'' and do.do_type='credit_sales'";
		
		if(!$this->resend){
			$filter[] = "(arm.ostrio_ar_id is null or arm.ostrio_ar_id=0)";	// Only new data
		}
		if($this->date_from){
			$filter[] = "do.do_date>=".ms($this->date_from);
		}
		if($this->date_to){
			$filter[] = "do.do_date<=".ms($this->date_to);
		}
		
		if($this->integration_start_date){
			$filter[] = "do.do_date>=".ms($this->integration_start_date);
		}
		
		// need to exclude those already exported to CS
		$filter[] = "csm.branch_id is null";
		
		$str_filter = "where ".join(' and ', $filter);
		$row_count = $error_count = 0;
		$err_msg_list = array();
		
		$sql = "select do.*, d.description as debtor_desc, od.ostrio_debtor_id, arm.ostrio_ar_id, dept.id as dept_id, dept.description as dept_name
			from do 
			left join debtor d on d.id=do.debtor_id
			left join ostrio_debtor_mapping od on od.debtor_id=do.debtor_id
			left join ostrio_ar_mapping arm on arm.branch_id=do.branch_id and arm.doc_id=do.id and arm.ar_type='do'
			left join ostrio_cs_mapping csm on csm.branch_id=do.branch_id and csm.doc_no=do.inv_no and csm.cs_type='do'
			left join category dept on dept.id=do.dept_id
			$str_filter order by do.id";
		//print $sql."\n";
		
		$q1 = $con->sql_query($sql);
		$do_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$do_id = mi($r['id']);
			$ostrio_debtor_id = trim($r['ostrio_debtor_id']);
			$ostrio_ar_id = mi($r['ostrio_ar_id']);
			$row_err_msg = array();
			//$dept_id = 0;
			//$dept_name = '';
			
			if(!$ostrio_debtor_id){
				$row_err_msg[] = "DO ID#$do_id: OS Trio Debtor ID Not Found.\n";
			}
			//print "DO ID: $do_id\n";
									
			if(!$row_err_msg){
				if(!isset($do_list[$do_id])){
					// Header
					$do_list[$do_id]['ostrio_ar_id'] = $ostrio_ar_id;
					$do_list[$do_id]['BranchCode'] = $bcode;
					$do_list[$do_id]['TranCode'] = $r['inv_no'];
					$do_list[$do_id]['TranDate'] = date("m/d/Y", strtotime($r['do_date']));
					$do_list[$do_id]['do_date'] = $r['do_date'];
					$do_list[$do_id]['CustID'] = $ostrio_debtor_id;
					$do_list[$do_id]['CurrencyRate'] = 1;
					$do_list[$do_id]['DONo'] = $r['do_no'];
					$do_list[$do_id]['TranType'] = 'I';
					$do_list[$do_id]['TranStatus'] = 'A';
					$do_list[$do_id]['Remark'] = $r['remark'];
					$do_list[$do_id]['UpdateDate'] = $r['last_update'];
					$do_list[$do_id]['UserID'] = 'ARMS';
					$do_list[$do_id]['total_inv_amt'] = $r['total_inv_amt'];
							
					$do_list[$do_id]['dept_list'] = array();
					// Get items department
					$q2 = $con->sql_query("select di.inv_line_amt2, dept.id as dept_id, dept.description as dept_name
						from do_items di
						left join sku_items si on si.id=di.sku_item_id
						left join sku on sku.id=si.sku_id
						left join category c on c.id=sku.category_id
						left join category dept on dept.id=c.department_id
						where di.branch_id=$bid and di.do_id=$do_id
						order by di.id");
					while($di = $con->sql_fetchassoc($q2)){
						$dept_id = mi($di['dept_id']);
						$dept_name = trim($di['dept_name']);
						
						if(!isset($do_list[$do_id]['dept_list'][$dept_id])){
							$do_list[$do_id]['dept_list'][$dept_id]['dept_id'] = $dept_id;
							$do_list[$do_id]['dept_list'][$dept_id]['StockCode'] = $dept_name;
							$do_list[$do_id]['dept_list'][$dept_id]['Description'] = $dept_name;
							$do_list[$do_id]['dept_list'][$dept_id]['Quantity'] = 1;
							$do_list[$do_id]['dept_list'][$dept_id]['UMCode'] = 'PCS';
							$do_list[$do_id]['dept_list'][$dept_id]['VATCode'] = 'SST-S0';
							$do_list[$do_id]['dept_list'][$dept_id]['VATPercentage'] = 0;
							$do_list[$do_id]['dept_list'][$dept_id]['GSTFCY'] = 0;
							$do_list[$do_id]['dept_list'][$dept_id]['GSTValueLocal'] = 0;
						}
						
						$new_price = round($do_list[$do_id]['dept_list'][$dept_id]['UnitPrice'] + $di['inv_line_amt2'], 2);
						
						$do_list[$do_id]['dept_list'][$dept_id]['UnitPrice'] = $new_price;						
						$do_list[$do_id]['dept_list'][$dept_id]['TotalPrice'] = $new_price;
						$do_list[$do_id]['dept_list'][$dept_id]['LocalUnitPrice'] = $new_price;
						$do_list[$do_id]['dept_list'][$dept_id]['LocalTotalPrice'] = $new_price;
						$do_list[$do_id]['dept_list'][$dept_id]['TotalAfterVAT'] = $new_price;
					}
					$con->sql_freeresult($q2);
					
					// Get Open item
					$q3 = $con->sql_query("select di.inv_line_amt2
						from do_open_items di
						where di.branch_id=$bid and di.do_id=$do_id
						order by di.id");
					$dept_id = 0;
					$dept_name = 'NO DEPT';
					while($di = $con->sql_fetchassoc($q3)){
						if(!isset($do_list[$do_id]['dept_list'][$dept_id])){
							$do_list[$do_id]['dept_list'][$dept_id]['dept_id'] = $dept_id;
							$do_list[$do_id]['dept_list'][$dept_id]['StockCode'] = $dept_name;
							$do_list[$do_id]['dept_list'][$dept_id]['Description'] = $dept_name;
							$do_list[$do_id]['dept_list'][$dept_id]['Quantity'] = 1;
							$do_list[$do_id]['dept_list'][$dept_id]['UMCode'] = 'PCS';
							$do_list[$do_id]['dept_list'][$dept_id]['VATCode'] = 'SST-S0';
							$do_list[$do_id]['dept_list'][$dept_id]['VATPercentage'] = 0;
							$do_list[$do_id]['dept_list'][$dept_id]['GSTFCY'] = 0;
							$do_list[$do_id]['dept_list'][$dept_id]['GSTValueLocal'] = 0;
						}
						
						$new_price = round($do_list[$do_id]['dept_list'][$dept_id]['UnitPrice'] + $di['inv_line_amt2'], 2);
						
						$do_list[$do_id]['dept_list'][$dept_id]['UnitPrice'] = $new_price;						
						$do_list[$do_id]['dept_list'][$dept_id]['TotalPrice'] = $new_price;
						$do_list[$do_id]['dept_list'][$dept_id]['LocalUnitPrice'] = $new_price;
						$do_list[$do_id]['dept_list'][$dept_id]['LocalTotalPrice'] = $new_price;
						$do_list[$do_id]['dept_list'][$dept_id]['TotalAfterVAT'] = $new_price;
					}
					$con->sql_freeresult($q3);
				}
			}
			
			if($row_err_msg){
				print_r($row_err_msg);
				
				$error_count++;
				$err_msg_list = array_merge($err_msg_list, $row_err_msg);
			}
			
		}
		$con->sql_freeresult($q1);
		//print_r($do_list);
		//return;
				
		if($do_list){
			$data_fields = array('BranchCode', 'TranCode', 'TranDate', 'CustID', 'CurrencyRate', 'DONo', 'TranType', 'TranStatus', 'Remark', 'UpdateDate', 'UserID');
			$dept_data_fields = array('StockCode', 'Description', 'Quantity', 'UMCode', 'VATCode', 'UnitPrice', 'TotalPrice', 'LocalUnitPrice', 'LocalTotalPrice', 'TotalAfterVAT', 'VATPercentage', 'GSTFCY', 'GSTValueLocal');
			
			foreach($do_list as $do_id => $r){
				$row_count++;
				$row_err_msg = array();
				$ostrio_ar_id = 0;//$r['ostrio_ar_id'];
				
				// No item
				if(!isset($r['dept_list']) || !$r['dept_list']){
					$row_err_msg[] = "No Item Found in DO ID#$do_id";
				}
				
				if(!$row_err_msg){
					$upd_list = array();
					// Delete old data in CCT_ARTrans
					$sth = $con2->exec("delete from CCT_ARTrans where BranchCode=".ms($bcode)." and TranCode=".ms($r['TranCode'])." and TranType=".ms($r['TranType']));
					$line_no = 0;
					foreach($r['dept_list'] as $dept_id => $dept_item){
						$line_no++;
						
						$upd = array();
						$upd['ImportFlag'] = 0;
						$upd['ImportDate'] = '';
						$upd['[LineNo]'] = $line_no;
						
						foreach($data_fields as $field){
							$upd[$field] = $r[$field];
						}
						foreach($dept_data_fields as $field){
							$upd[$field] = $dept_item[$field];
						}
						$upd_list[]= $upd;
						//print_r($upd);
					}
					
					// Loop to insert
					foreach($upd_list as $upd){
						// Insert
						$success = $con2->exec($sql = "insert into CCT_ARTrans ".mssql_insert_by_field($upd));
						if($success === false){	// Insert Failed
							print "$sql\n";
							$err_msg = print_r($con2->errorInfo(), true);
							$row_err_msg[] = $err_msg;
							break;
						}else{					
							// Get inserted data
							$sth = $con2->query("select ID from CCT_ARTrans where BranchCode=".ms($bcode)." and TranCode=".ms($upd['TranCode'])." and TranType=".ms($upd['TranType'])." and StockCode=".ms($upd['StockCode'])." order by ID desc");
							$temp = $sth->fetch(PDO::FETCH_ASSOC);
							$sth->closeCursor();
							$tmp_ostrio_ar_id = $temp['ID'];
							if(!$tmp_ostrio_ar_id){	// Cant Get Inserted ID
								$err_msg = print_r($con2->errorInfo(), true);
								$row_err_msg[] = $err_msg;
								break;
							}else{
								// Get first department ostrio_ar_id
								if(!$ostrio_ar_id)	$ostrio_ar_id = $tmp_ostrio_ar_id;
								
							}
							
						}
					}
				}
				
				
				if(!$row_err_msg){
					// Update Mapping Info
					$upd2 = array();
					$upd2['branch_id'] = $bid;
					$upd2['doc_id'] = $do_id;
					$upd2['acc_doc_no'] = $r['TranCode'];
					$upd2['doc_date'] = $r['do_date'];
					$upd2['ar_type'] = 'do';
					$upd2['amount'] = $r['total_inv_amt'];
					$upd2['ostrio_ar_id'] = $ostrio_ar_id;
					$upd2['last_update'] = 'CURRENT_TIMESTAMP';
					$upd2['api_data'] = serialize($upd_list);
					$con->sql_query("replace into ostrio_ar_mapping ".mysql_insert_by_field($upd2));
				}else{
					print_r($row_err_msg);
				
					$error_count++;
					$err_msg_list = array_merge($err_msg_list, $row_err_msg);
				}
			}
		}
		
		// Mark Finish
		$this->set_status($bid, $integration_type, $sub_type, 1, $err_msg_list);
		
		$con->sql_commit();
		
		print "$row_count DO processed.\n";
		if($error_count>0)	print "$error_count row got error.\n";
	}
}
?>