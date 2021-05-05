<?php

define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '1024M');
set_time_limit(0);

$argv = $_SERVER['argv'];
$CRON_CYCLE_COUNT = new CRON_CYCLE_COUNT();
$CRON_CYCLE_COUNT->start();

class CRON_CYCLE_COUNT {
	var $is_send = false;
	
	function __construct(){
	    global $con, $sessioninfo;

		$this->fp_path = dirname(__FILE__)."/".basename(__FILE__, '.php').".running";	// use this prevent wrong "include" path
		
		$this->fp = fopen($this->fp_path, "w");
		chmod($this->fp_path, 0777);
		
		$this->mark_start_process();
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
		unlink($this->fp_path);
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
					$this->b_list[] = $r;
				}
				$con->sql_freeresult();
			}elseif($cmd_head == "-send"){
				$this->is_send = true;
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
	
	function __destruct() {
        $this->mark_close_process();
    }
	
	function start(){
		print "Start\n";
		
		//print "gethostname = ".gethostname()."\n";
		//print_r($_SERVER);exit;
		$this->filter_argv();
		$this->check_argv();
		
		$this->process_cycle_count();
		
		print "Done.\n";
	}
	
	private function process_cycle_count(){
		global $con, $appCore;
		
		foreach($this->b_list as $b){
			$this->process_cycle_count_by_branch($b);
		}
	}
	
	private function process_cycle_count_by_branch($b){
		global $con, $appCore;
		
		$bid = mi($b['id']);
		print "Branch: ".$b['code']."\n";
		print "Branch ID: ".$bid."\n";
		
		$today = date("Y-m-d");
		$y = mi(date("Y"));
		$m = mi(date("m"));
		$m++;	// next month
		if($m>12){
			$y++;
			$m=1;
		}
		
		$first_day_of_month = $y."-".$m."-1";
		$last_day_of_month = $y."-".$m."-".days_of_month($m, $y);
		
		print "First Day: $first_day_of_month\n";
		
		$filter = array();
		$filter[] = "cc.branch_id=$bid";
		$filter[] = "cc.active=1 and cc.status=1 and cc.approved=1";
		$filter[] = "cc.wip=0 and cc.completed=0 and cc.sent_to_stock_take=0 and cc.notify_sent=0";
		$filter[] = ms($today).">=DATE_SUB(DATE_ADD(DATE_ADD(LAST_DAY(propose_st_date),
            INTERVAL 1 DAY),
        INTERVAL - 1 MONTH), INTERVAL cc.notify_day day)";
		
		$str_filter = "where ".join(' and ', $filter);
		
		$sql = "select cc.*
			from cycle_count cc
			$str_filter";
		//print $sql."\n";
		$q1 = $con->sql_query($sql);
		while($cc = $con->sql_fetchassoc($q1)){
			print "Processing Branch ID#".$cc['branch_id'].", ID#".$cc['id']."...";
			
			if($this->is_send){
				$result = $appCore->stockTakeManager->sendCycleCountDueNotification($cc['branch_id'], $cc['id']);
				
				if($result['ok']){
					print "OK, ".mi($result['sent_count'])." email sent.";
				}else{
					print "Error: ".$result['error'];
				}
			}
			
			print "\n";
		}
		$con->sql_freeresult($q1);
	}
	
}
?>