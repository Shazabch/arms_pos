<?php
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '512M');
set_time_limit(0);

$argv = $_SERVER['argv'];
$CRON_MEMBER_PN = new CRON_MEMBER_PN();
$CRON_MEMBER_PN->start();

class CRON_MEMBER_PN {
	var $b_list = array();
	var $fp_path = '';
	var $monitor_fp_path = '';
	var $pn_guid = '';
	
	function __construct(){
	    global $con, $sessioninfo;

		$this->fp_path = dirname(__FILE__)."/".basename(__FILE__, '.php').".running";	// use this prevent wrong "include" path
		$this->monitor_fp_path = dirname(__FILE__)."/".basename(__FILE__, '.php').".monitor";	// use this prevent wrong "include" path
		
		$this->fp = fopen($this->fp_path, "w");
		chmod($this->fp_path, 0777);
		
		$this->mark_start_process();
	}
	
	function __destruct() {
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
			}elseif($cmd_head == '-pn_guid'){
				$this->pn_guid = trim($cmd_value);
				if(!$this->pn_guid)	die("Invalid PN GUID\n");
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
	
	function start(){
		print "Start\n";
		print date("Y-m-d H:i:s")."\n";
		
		$this->filter_argv();
		$this->check_argv();
		
		foreach($this->b_list as $b){
			$this->check_member_pn_branch($b);
		}
		
		print date("Y-m-d H:i:s")."\n";
		print "Done\n";
	}
	
	private function update_monitor_file($monitor){
		$monitor['timestamp'] = date("Y-m-d H:i:s");
		
		// Update Monitor File
		file_put_contents($this->monitor_fp_path, serialize($monitor));
	}
	
	private function check_member_pn_branch($b){
		global $con, $appCore, $config;
		
		$bid = mi($b['id']);
		print "Branch: ".$b['code']."\n";
		print "Branch ID: ".$bid."\n";
		
		$filter = array();
		$filter[] = "branch_id=".mi($bid);
		$filter[] = "active=1 and err_msg=''";
		if($this->pn_guid)	$filter[] = "guid=".ms($this->pn_guid);
		
		$str_filter = "where ".join(' and ', $filter);
		
		$sql = "select guid from memberships_pn
			$str_filter
			order by added";
		//print $sql."\n";
		$q1 = $con->sql_query($sql);
		
		while($pn = $con->sql_fetchassoc($q1)){
			$this->send_pn($pn['guid']);
		}
		$con->sql_freeresult($q1);
	}
	
	private function send_pn($pn_guid){
		global $con, $appCore, $config;
		
		// Get Push Notification
		$q1 = $con->sql_query("select * from memberships_pn where guid=".ms($pn_guid));
		$pn = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		// Not Found
		if(!$pn){
			print "Push Notification GUID Not Found: $pn_guid";
			return;
		}
		
		// Mark Start Running
		$monitor = array();
		$monitor['running_guid'] = $pn_guid;
		$this->update_monitor_file($monitor);
		
		// Get Member List
		$q2 = $con->sql_query("select * from memberships_pn_items where memberships_pn_guid=".ms($pn_guid)." and completed=0 order by nric");
		$total_count = mi($con->sql_numrows($q2));
		$monitor['total_count'] = $total_count;
		$curr_count = 0;
		while($pn_items = $con->sql_fetchassoc($q2)){
			$curr_count++;
			$nric = trim($pn_items['nric']);
			
			// Begin Transaction
			$con->sql_begin_transaction();
		
			// Send Push Notification to Member
			$success_count = $appCore->memberManager->sendPushNotificationToMember($nric, $pn['pn_title'], $pn['pn_msg'], array('screen_tag'=>$pn['screen_tag']));
			
			$upd = array();
			$upd['completed'] = 1;
			$upd['success'] = $success_count > 0 ? 1 : 0;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("update memberships_pn_items set ".mysql_update_by_field($upd)." where guid=".ms($pn_items['guid']));
			
			// Commit Transaction
			$con->sql_commit();
			
			$monitor['curr_count'] = $curr_count;
			$this->update_monitor_file($monitor);
		}
		$con->sql_freeresult($q2);
		
		// Mark Send Completed
		$upd = array();
		$upd['completed'] = 1;
		$upd['err_msg'] = '';
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update memberships_pn set ".mysql_update_by_field($upd)." where guid=".ms($pn_guid));
	}
}
?>