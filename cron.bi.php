<?php
/*
1/22/2019 5:22 PM Andy
- New cron for Business Intelligent.

====================
Command
php cron.bi.php -branch=all -recent_day=7

*/
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '1024M');
set_time_limit(0);

$argv = $_SERVER['argv'];
$CRON_BI = new CRON_BI();
$CRON_BI->start();

class CRON_BI {
	
	var $b_list = array();
	var $fp_path = '';
	var $date_from;
	var $date_to;
	var $valid_sales_type = array('hourly_branch_dept');
	var $selected_sales_type_list = array();
	var $date_list = array();
	var $regen = false;
	var $purge_day = 7;
	var $purge_date = '';
	
	function __construct(){
	    global $con;

		$this->fp_path = dirname(__FILE__)."/".basename(__FILE__, '.php').".running";	// use this prevent wrong "include" path
		
		$this->fp = fopen($this->fp_path, "w");
		chmod($this->fp_path, 0777);
		
		$this->mark_start_process();
	}
	
	function __destruct() {
        $this->mark_close_process();
    }
	
	private function mark_start_process(){		
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
	
	function start(){
		print "Start\n";
		
		$this->filter_argv();
		
		$this->start_generate();
	}
	
	function filter_argv(){
		global $argv, $con, $config;
		
		//print_r($argv);
		
		$dummy = array_shift($argv);
		$this->b_list = array();
		$selected_sales_type_list = array();

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

				$con->sql_query("select id,code,description from branch $branch_filter order by sequence,code");
				while($r = $con->sql_fetchassoc()){
					if(isset($config['sales_report_branches_exclude']) && is_array($config['sales_report_branches_exclude']) && in_array($r['code'], $config['sales_report_branches_exclude'])){
						continue;	// this branch no need show in sales report
					}
					$this->b_list[$r['id']] = $r;
				}
				$con->sql_freeresult();
			}elseif($cmd_head == "-sales_type"){	// Sales Type
				$sales_type_list = explode(',', strtolower(trim($cmd_value)));
				
				foreach($sales_type_list as $sales_type){
					if(!in_array($sales_type, $this->valid_sales_type)){
						die("Invalid sales_type '$sales_type'.\n");
					}
					$selected_sales_type_list[] = $sales_type;
				}				
			}elseif($cmd_head == '-date'){	// date
				$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
				if(!$tmp)	die("Invalid Date.\n");
				if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
				$date = $tmp;
			}elseif($cmd_head == '-date_from'){	// date_from
				$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
				if(!$tmp)	die("Invalid Date From.\n");
				if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
				$date_from = $tmp;
			}elseif($cmd_head == '-date_to'){	// date_to
				$tmp = date("Y-m-d", strtotime(trim($cmd_value)));
				if(!$tmp)	die("Invalid Date To.\n");
				if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
				$date_to = $tmp;
			}elseif($cmd_head == '-recent_day'){	// use yesterday
				$num = mi($cmd_value);
				if($num<=0)	die("Recent Day must more than zero.\n");
				
				$date_to = date("Y-m-d");
				$date_from = date("Y-m-d", strtotime("-".($num-1)." day", strtotime($date_to)));
			}elseif($cmd_head == '-regen'){	// use yesterday
				$this->regen = true;
			}elseif($cmd_head == '-purge_day'){	// use yesterday
				$this->purge_day = mi($cmd_value);
			}else{
				print "Unknown command $cmd\n";
				exit;
			}
		}
		
		// Branch
		if(!$this->b_list)	die("Branch not found.\n");
		
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
		
		// loop date
		for($d1 = strtotime($this->date_from),$d2 = strtotime($this->date_to); $d1 <= $d2; $d1+=86400){
			$this->date_list[] = date("Y-m-d", $d1);
		}
		
		// Sales Type
		$this->selected_sales_type_list = $selected_sales_type_list ? $selected_sales_type_list : $this->valid_sales_type;
		
		// Purge Data
		$this->purge_date = date("Y-m-d", strtotime("-".$this->purge_day." day"));
	}
	
	private function start_generate(){
		print "Start\n";
		
		// loop branch
		foreach($this->b_list as $bid => $b){
			$this->generate_data_by_branch($bid);
		}
		
		// Update Last Refresh
		$this->update_bi_last_refresh();
		
		print "All Done.\n";
	}
	
	private function generate_data_by_branch($bid){
		global $con;
		
		print "Generating data for Branch ID#$bid (".$this->b_list[$bid]['code'].")\n";
		
		// Purge Old Data
		$this->purge_data_by_branch($bid);
		
		foreach($this->selected_sales_type_list as $sales_type){
			if($sales_type == 'hourly_branch_dept'){
				$this->generate_hourly_branch_dept($bid);
			}else{
				print "Invalid Sales Type '$sales_type'";
			}
		}
		
		print "\n";
	}
	
	private function purge_data_by_branch($bid){
		global $con;
		
		print " - Purge Data: Use Date#".$this->purge_date."\n";
		
		$con->sql_begin_transaction();
		
		// Check Record
		$q1 = $con->sql_query("select date from pos_transaction_bi_record where branch_id=$bid and last_update<=".ms($this->purge_date." 23:59:59"));
		while($r = $con->sql_fetchassoc($q1)){
			$con->sql_query("delete from pos_transaction_bi_branch_dept_sales where branch_id=$bid and date=".ms($r['date']));
			$con->sql_query("delete from pos_transaction_bi_record where branch_id=$bid and date=".ms($r['date']));
			print "  - ".$r['date']." purged.\n";
		}
		$con->sql_freeresult($q1);
		
		$con->sql_commit();
	}
	
	private function generate_hourly_branch_dept($bid){
		global $con;
		
		$bid = mi($bid);
		print "\n - Hourly Branch Department Sales...\n";
		
		$branch_desc = $this->b_list[$bid]['code'].' - '.$this->b_list[$bid]['description'];
		
		foreach($this->date_list as $d){
			print "  - $d";
			
			// Get Finalise Time
			$con->sql_query("select * from pos_finalized where branch_id=$bid and date=".ms($d));
			$pf = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$need_update = false;
			if($this->regen)	$need_update = true;	// always update if got regen
			
			if(!$need_update){
				if(!$pf['finalized']){
					$need_update = true;	// Not Yet Finalise, need update
				}else{
					// Get BI Record
					$con->sql_query("select * from pos_transaction_bi_record where branch_id=$bid and date=".ms($d));
					$bi_record = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					if($pf['finalize_timestamp'] != $bi_record['finalize_timestamp']){
						$need_update = true;	// Finalise time changed, need update
					}
				}
			}			
			
			if(!$need_update){
				print "- Skipped.\n";
				continue;
			}
			
			// Get current time
			$update_time = date("Y-m-d H:i:s");
			
			$con->sql_begin_transaction();
			
			// Delete Old Data
			$con->sql_query("delete from pos_transaction_bi_branch_dept_sales where branch_id=$bid and date=".ms($d));
			
			$sql = "select p.branch_id,p.date,p.counter_id,p.id as pos_id,hour(p.pos_time) as h, max(pos_time) as last_pos_time, sum(pi.price) as price, sum(pi.discount) as discount, sum(pi.discount2) as discount2, sum(pi.tax_amount) as tax_amount,c.department_id as dept_id,dept.description as dept_desc
				from pos p
				join pos_items pi on pi.branch_id=p.branch_id and p.date=pi.date and p.counter_id=pi.counter_id and p.id=pi.pos_id
				join sku_items si on si.id=pi.sku_item_id
				left join sku on sku.id=si.sku_id
				left join category c on c.id=sku.category_id
				left join category dept on dept.id=c.department_id
				where p.branch_id=$bid and p.cancel_status=0 and p.date=".ms($d)."
				group by p.branch_id,p.date,dept.id,h
				order by h";
			$q1 = $con->sql_query($sql);
			while($r = $con->sql_fetchassoc($q1)){
				$upd = array();
				$upd['branch_id'] = $r['branch_id'];
				$upd['date'] = $r['date'];
				$upd['dept_id'] = $r['dept_id'];
				$upd['h'] = $r['h'];
				$upd['last_pos_time'] = $r['last_pos_time'];
				$upd['sales_amt'] = round($r['price'], 2);
				$upd['tax_amt'] = round($r['tax_amount'], 2);
				$upd['disc_amt'] = round($r['discount']+$r['discount2'], 2);
				$upd['last_update'] = $update_time;
				$upd['branch_desc'] = $branch_desc;
				$upd['dept_desc'] = $r['dept_desc'];
				
				$con->sql_query("replace into pos_transaction_bi_branch_dept_sales ".mysql_insert_by_field($upd));
			}
			$con->sql_fetchassoc($q1);
			
			$upd_bi = array();
			$upd_bi['branch_id'] = $bid;
			$upd_bi['date'] = $d;
			$upd_bi['finalized'] = mi($pf['finalized']);
			if($upd_bi['finalized'])	$upd_bi['finalize_timestamp'] = $pf['finalize_timestamp'];
			$upd_bi['last_update'] = $update_time;
			
			$con->sql_query("replace into pos_transaction_bi_record ".mysql_insert_by_field($upd_bi));

			$con->sql_commit();
			
			print " - OK\n";
		}
	}
	
	private function update_bi_last_refresh(){
		global $con;
		
		$upd = array();
		$upd['name'] = 'last_refresh';
		$upd['value'] = $upd['update_time'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("replace into pos_transaction_bi_status ".mysql_insert_by_field($upd));
	}
}
?>