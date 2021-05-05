<?php
/*
2/17/2020 4:39 PM Andy
- Fixed php time "h:i" should be "H:i".
*/
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '512M');
set_time_limit(0);

//shell_exec("echo \"testing\" | mail andy@arms.my");

$argv = $_SERVER['argv'];
$CRON_MEMBER_PACKAGE = new CRON_MEMBER_PACKAGE();
$CRON_MEMBER_PACKAGE->start();

class CRON_MEMBER_PACKAGE {
	var $b_list = array();
	var $fp_path = '';
	var $back_hour = 3;
	
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
		global $config;
		
		print "Start\n";
		print date("Y-m-d H:i:s")."\n";
		
		if(!($config['membership_mobile_settings'] && $config['enable_push_notification'])){
			die("No Membership mobile or Push Notification Config.\n");
		}
		
		$this->filter_argv();
		$this->check_argv();
		
		foreach($this->b_list as $b){
			$this->check_member_package_by_branch($b);
		}
				
		print date("Y-m-d H:i:s")."\n";
		print "Done\n";
	}
	
	private function check_member_package_by_branch($b){
		global $con, $appCore, $config;
		
		$bid = mi($b['id']);
		print "Branch: ".$b['code']."\n";
		print "Branch ID: ".$bid."\n";
		
		$filter = array();
		$filter[] = "mppir.branch_id=$bid and mppir.notify_member_to_rate=0 and mppir.overall_rating=0";
		
		if($config['membership_package_notify_rate_back_hour']){
			$back_hour = mi($config['membership_package_notify_rate_back_hour']);
		}
		if($back_hour<=0)	$back_hour = $this->back_hour;
		$filter[] = "mppir.added<".ms(date("Y-m-d H:i:s", strtotime("-".$back_hour." hour")));
		
		$str_filter = "where ".join(' and ', $filter);
		
		// Begin Transaction
		$con->sql_begin_transaction();
			
		$sql = "select mppir.*, mpp.card_no
			from memberships_purchased_package_items_redeem mppir
			join memberships_purchased_package_items mppi on mppi.guid=mppir.purchased_package_items_guid
			join memberships_purchased_package mpp on mpp.guid=mppi.purchased_package_guid
			$str_filter
			order by mppir.added
			for update";
		//print "$sql\n";
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			print "GUID: ".$r['guid']."\n";
			
			$appCore->memberManager->sendPushNotificationToMember($r['card_no'], 'How was the services?', 'Please rate our last services in membership package.', array('branch_id'=>$bid));
			
			$upd = array();
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$upd['notify_member_to_rate'] = 1;
			$con->sql_query("update memberships_purchased_package_items_redeem set ".mysql_update_by_field($upd)." where guid=".ms($r['guid']));
		}
		$con->sql_freeresult($q1);
		
		// Commit Transaction
		$con->sql_commit();
	}
}
?>