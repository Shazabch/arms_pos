<?php
/*
7/27/2018
- Added Sunway Giza Mall format.

12/19/2019 5:29 PM Andy
- Changed initialize table to innodb.

10/12/2020 5:47 PM William
- Added new tax checking.
*/

class SUNWAY_GIZA{
	var $branch_info = array();
	var $generate_info_filename = 'H%s_%s.txt';
	var $bid = 0;
	var $folder_path = "data/";
	var $date_from;
	var $date_to;
	var $regen = false;
	var $config_name = 'sunway_giza_setting';
	var $mall_name = 'Sunway Giza Mall';
	var $configuration = array();
	var $batch_table_name = "sunway_giza_batch";
	
	// sFTP
	var $conn_id;
	var $sftp;
	
	function __construct()
	{
		global $config;
		
		// Reconstruct actual folder path
		$this->folder_path = dirname(__FILE__)."/".$this->folder_path;
		
		if(!isset($config[$this->config_name]) || !$config[$this->config_name])	die("No ".$this->mall_name." Setting.\n");
		
		$this->configuration = $config[$this->config_name];
		
		if(!is_dir($this->folder_path)) check_and_create_dir($this->folder_path);
		
		$this->init_database();
	}
	
	private function init_database(){
		global $con;
		
		$con->sql_query("create table if not exists ".$this->batch_table_name."(
			branch_id int,
			date date,
			batch_id int not null default 0,
			primary key(branch_id, date)
		) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
	}
	
	function checkArgs($arg){
		global $con;
		
		if(!isset($arg) || count($arg)<=2)	die("Arguments Required.\n");
		
		// remove 1st and 2nd arguments.
		$a = array_shift($arg);
		$a = array_shift($arg);
		
		while($a = array_shift($arg)){
			if(preg_match('/^-date=/', $a)){	// date
				$tmp = date("Y-m-d", strtotime(str_replace('-date=', '', $a)));
				if(!$tmp)	die("Invalid Date.\n");
				if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
				$date = $tmp;
			}elseif(preg_match('/^-date_from=/', $a)){	// date_from
				$tmp = date("Y-m-d", strtotime(str_replace('-date_from=', '', $a)));
				if(!$tmp)	die("Invalid Date From.\n");
				if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
				$date_from = $tmp;
			}elseif(preg_match('/^-date_to=/', $a)){	// date_to
				$tmp = date("Y-m-d", strtotime(str_replace('-date_to=', '', $a)));
				if(!$tmp)	die("Invalid Date To.\n");
				if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
				$date_to = $tmp;
			}elseif(preg_match('/^-branch=/', $a)){	// branch
				$tmp = trim(str_replace('-branch=', '', $a));
				if(!$tmp)	die("Invalid branch.\n");
				$bcode = $tmp;
			}elseif(preg_match('/^-yesterday$/', $a)){	// use yesterday
				$date = date("Y-m-d", strtotime("-1 day"));
			}elseif(preg_match('/^-regen$/', $a)){	// regenerate
				$this->regen = true;
			}elseif(preg_match('/^-recent_day=/', $a)){	// use yesterday
				$num = mi(str_replace('-recent_day=', '', $a));
				if($num<=0)	die("Recent Day must more than zero.\n");
				
				$date_to = date("Y-m-d", strtotime("-1 day"));
				$date_from = date("Y-m-d", strtotime("-".$num." day"));
			}
			else{
				die("Unknown option $a\n");
			}
		}
		
		// check branch
		if(!$bcode){
			die("Please provide branch code by using -branch=\n");
		}
		$con->sql_query("select id,code from branch where code=".ms($bcode));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$tmp){
			die("Invalid branch code '$bcode'.\n");
		}
		$this->branch_info = $tmp;
		$this->bid = mi($this->branch_info['id']);
		$this->bcode = strtoupper($this->branch_info['code']);
		
		if(!isset($this->configuration[$this->bcode])){
			die("No config found for branch '$this->bcode'.\n");
		}
		
		// check date
		if(!$date && !$date_from && !$date_to){
			die("Please provide date by using -date= or -date_from= or -date_to=\n");
		}
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

	function start(){
		global $con;
		
		print "\n";
		// Connect to Remote Server
		$this->connect_remote_server();
		print "\n";
		
		// Generate Sales File
		$this->generate_file();
		print "\n";
		
		// Upload Sales File
		$this->upload_file();
		
		// close the connection
		//ftp_close($this->conn_id);
		print "Done.\n";
	}
	
	private function connect_remote_server(){		
		if(!isset($this->configuration[$this->bcode]['server_ftp_info']))	die("Server FTP Info not found.\n");
		
		$server_ftp_info = $this->configuration[$this->bcode]['server_ftp_info'];
		
		print "Connecting to Remote Server at '".$server_ftp_info['ip']."'\n";
		
		// set up basic connection
		
		//$this->conn_id = ftp_connect($server_ftp_info['ip'],$server_ftp_info['port']);
		$this->conn_id = ssh2_connect($server_ftp_info['ip'], $server_ftp_info['port']);
		if(!$this->conn_id){
			die("Remote Server Cannot be connect.\n");
		}
		print "Connected.\n";
		
		// login with username and password
		print "Attemp to Login...\n";
		//$login_result = ftp_login($this->conn_id, $server_ftp_info['username'], $server_ftp_info['pass']);
		$login_result = ssh2_auth_password($this->conn_id, $server_ftp_info['username'], $server_ftp_info['pass']);
		if(!$login_result){
			print "Failed to login.\n";
			//ftp_close($this->conn_id);
			exit;
		}
		// Create SFTP session
		$this->sftp = ssh2_sftp($this->conn_id);

		print "Login Success.\n";
        //ftp_pasv($this->conn_id, true);
		
		// no need to change directory
		//ftp_chdir($this->conn_id, $server_ftp_info['path']);
	}
	
	private function generate_file(){
		print "Generate Files...\n";
		// loop date
		for($d1 = strtotime($this->date_from),$d2 = strtotime($this->date_to); $d1 <= $d2; $d1+=86400){
			$this->generate_file_by_date(date("Y-m-d", $d1));
		}
	}
	
	private function upload_file(){
		print "Upload Files...\n";
		// loop date
		for($d1 = strtotime($this->date_from),$d2 = strtotime($this->date_to); $d1 <= $d2; $d1+=86400){
			$this->upload_file_by_date(date("Y-m-d", $d1));
		}
	}
	
	private function get_folder_path($date){
		// check & create folder by branch
		$folder_path = $this->folder_path.str_replace('/', '', $this->bcode)."/";
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
	
		$year = date('Y',strtotime($date));
		$month = mi(date('m',strtotime($date)));
		
		// check & create folder by year
		$folder_path = $folder_path.$year;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		// check & create folder by month
		$folder_path=$folder_path."/".$month;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		return $folder_path;
	}
	
	private function get_uploaded_folder_path($date){
		$folder_path = $this->get_folder_path($date);
		
		// check & create uploaded folder
		$uploaded_folder_path = $folder_path."/uploaded";
		if(!is_dir($uploaded_folder_path)) check_and_create_dir($uploaded_folder_path);
		
		return $uploaded_folder_path;
	}
	
	private function get_filename($date){
		$settings = $this->configuration[$this->bcode];
		$filename = sprintf($this->generate_info_filename, $settings['tenant_code'], date('Ymd',strtotime($date)));
		
		return $filename;
	}
	
	private function generate_file_by_date($date){
		global $con;
		
		print "Generating file for $date";
		
		$settings = $this->configuration[$this->bcode];
		$folder_path = $this->get_folder_path($date);
		$uploaded_folder_path = $this->get_uploaded_folder_path($date);
		$filename = $this->get_filename($date);
		
		if(!$this->regen){	// no regenerate, check whether file exists
			if(file_exists($uploaded_folder_path."/".$filename) && filesize($uploaded_folder_path."/".$filename) > 0){
				print " - File Uploaded\n";
				return;
			}
		}
		
		// delete the file
		if(file_exists($folder_path."/".$filename)) unlink($folder_path."/".$filename);
		
		$sql = "select hour(p.pos_time) h, count(*) as pos_count, sum(round((select sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id)+ifnull(p.service_charges-p.service_charges_gst_amt,0),2)) as cash_sales, sum(total_gst_amt) as total_gst_amt,
sum(round((select sum(pi.discount+pi.discount2) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2)) as discount, p.is_gst, p.is_tax_registered
			from pos p 
			join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date and pf.finalized=1
			where p.branch_id=$this->bid and p.cancel_status=0 and p.date=".ms($date)."
			group by h";
			
		//print $sql;
		$q1 = $con->sql_query($sql);
		$gst_registered = 'N';
		
		//print "num rows = ".$con->sql_numrows($q1);
		$data_by_hour = array();
		if($con->sql_numrows($q1) > 0){
			// Store data by hour
			while($r = $con->sql_fetchassoc($q1)){	// Loop Hour
				$data_by_hour[$r['h']] = $r;
				
				if($r['is_gst'] || $r['is_tax_registered'])	$gst_registered = 'Y';
			}
		}else{
			print " - No Data.\n";
		}
		
		$con->sql_freeresult($q1);
		
		// Get Batch ID
		$batch_id = $this->get_batch_id($this->bid, $date);
		$date_dmy = date("dmY", strtotime($date));
		
		// Create file
		$fp = fopen($folder_path."/".$filename, 'w');
		
		// loop 0 to 23
		for($i = 0; $i <= 23; $i++){
			$receipt_count = 0;
			$gto_sales = 0;
			$gst = 0;
			$discount = 0;
			
			if(isset($data_by_hour[$i])){	// This Hour got sales
				$receipt_count = mi($data_by_hour[$i]['pos_count']);
				$gto_sales = mf($data_by_hour[$i]['cash_sales']);
				$gst = mf($data_by_hour[$i]['total_gst_amt']);
				$discount = mf($data_by_hour[$i]['discount']);
				/*
					$sales_amt = 0;
					while($r = $con->sql_fetchassoc($q1)){
						$sales_amt += round($r['cash_sales'],2);
					}
					
					if($sales_amt){
						$data = array($this->configuration[$this->bcode]['tenant_code'], date("dmY", strtotime($date)),$sales_amt);
						
						$fp = fopen($folder_path."/".$filename, 'w');
						$success = fputcsv($fp, $data);
						if(!$success)	die("Failed to write to file.\n");
					}else{
						print " - Sales Amount Zero.\n";
					}
				*/
			}
			
			$data = array(
				$settings['tenant_code'],
				$batch_id,
				$date_dmy,
				sprintf("%02d", $i),
				$receipt_count,
				number_format($gto_sales, 2),
				number_format($gst, 2),
				number_format($discount, 2),
				'0.00',
				0,
				number_format($gto_sales, 2),
				'0.00', '0.00', '0.00', '0.00', '0.00', '0.00',
				$gst_registered
			);
			$success = $this->fputcsv_eol($fp, $data, "|");
			if(!$success)	die("Failed to write to file.\n");
		}
		
		if(isset($fp) && $fp){
			fclose($fp);
			chmod($folder_path."/".$filename,0777);
			print " - Done. ".$folder_path."/".$filename."\n";
		}
	}
	
	private function upload_file_by_date($date){
		$settings = $this->configuration[$this->bcode];
		$folder_path = $this->get_folder_path($date);
		$uploaded_folder_path = $this->get_uploaded_folder_path($date);
		$filename = $this->get_filename($date);
		
		print "Checking ".$folder_path."/".$filename;
		if(!file_exists($folder_path."/".$filename)){
			print " - No file to upload.\n";
			return;
		}
		
		// Upload
		$full_filepath = $folder_path."/".$filename;
		print " - Uploading...";
		
		$srcFile = $full_filepath;
		$dstFile = "/".$filename;

		$sftpStream = fopen('ssh2.sftp://'.$this->sftp.$dstFile, 'w');
		try {
		 
			if (!$sftpStream) {
				throw new Exception("Could not open remote file: $dstFile");
			}
		 
			$data_to_send = file_get_contents($srcFile);
		 
			if ($data_to_send === false) {
				throw new Exception("Could not open local file: $srcFile.");
			}
		 
			if (fwrite($sftpStream, $data_to_send) === false) {
				throw new Exception("Could not send data from file: $srcFile.");
			}
		 
			fclose($sftpStream);
			print "successfully uploaded.\n";
		} catch (Exception $e) {
			error_log('Exception: ' . $e->getMessage()."\n");
			fclose($sftpStream);
		}
		
		
		///////////////
		//if (ftp_put($this->conn_id, $filename, $full_filepath, FTP_BINARY)) {
		/*if (ssh2_scp_send($this->conn_id, $full_filepath, "/".$filename, 0644)) {
			print "successfully uploaded.\n";
		} else {
			$error=error_get_last();
			print "There was a problem while uploading.\n";
			print_r($error);
			return;
		}
		*/
		// Move to uploaded folder
		rename($full_filepath, $uploaded_folder_path."/".$filename);
	}
	
	private function get_batch_id($bid, $date){
		global $con;
		
		$batch_id = 0;
		
		$con->sql_query("select batch_id from ".$this->batch_table_name." tbl where tbl.branch_id=$bid and tbl.date=".ms($date));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($tmp)	$batch_id = mi($tmp['batch_id']);
		
		if($batch_id > 0)	return $batch_id;
		
		// get Max batch_id
		$con->sql_query("select max(batch_id) as batch_id from ".$this->batch_table_name." tbl where tbl.branch_id=$bid");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$batch_id = mi($tmp['batch_id'])+1;
		
		$upd = array();
		$upd['branch_id'] = $bid;
		$upd['date'] = $date;
		$upd['batch_id'] = $batch_id;
		$con->sql_query("replace into ".$this->batch_table_name." ".mysql_insert_by_field($upd));
		
		return $batch_id;
	}
	
	private function fputcsv_eol($handle, $array, $delimiter = ',', $enclosure = '"', $eol = "\r\n") {
		$return = fputcsv($handle, $array, $delimiter, $enclosure);
		if($return !== FALSE && "\n" != $eol && 0 === fseek($handle, -1, SEEK_CUR)) {
			fwrite($handle, $eol);
		}
		return $return;
	}
}

$SUNWAY_GIZA = new SUNWAY_GIZA();
$SUNWAY_GIZA->checkArgs($arg);
$SUNWAY_GIZA->start();
			
?>