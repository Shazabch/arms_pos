<?php
/*
6/12/2018 11:38 AM Andy
- New Module to Manage Email Sending.

*/
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '512M');
set_time_limit(0);

// check if myself is running, exit if yes
/*if (!preg_match('/(root|arms|admin|wsatp)/', `whoami`) || $config['arms_go_modules']){
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
}*/

$argv = $_SERVER['argv'];
$CRON_EMAIL = new CRON_EMAIL();
$CRON_EMAIL->start();

class CRON_EMAIL {
	var $is_send = false;
	var $b_list = array();
	var $fp_path = '';
	
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
		print "Start\n";
		
		$this->filter_argv();
		$this->check_argv();
		
		if($this->is_send){
			$this->send_email();
		}
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
	
	function send_email(){
		global $con, $appCore;
		
		foreach($this->b_list as $b){
			$this->send_email_by_branch($b);
		}
		
		//$this->fake_sleep();
		print "Done.\n";
	}
	
	function send_email_by_branch($b){
		global $con, $appCore;
		
		$bid = mi($b['id']);
		print "Branch: ".$b['code']."\n";
		print "Branch ID: ".$bid."\n";
		
		$params = array();
		$params['branch_id'] = $bid;
		$params['order_by'] = array('e.added'=>'asc');
		
		$ret = $appCore->emailManager->getUnSendEmails($params);
		if($ret['ok'] && is_array($ret['email_list'])){
			$email_list = $ret['email_list'];
			print "New Email: ".count($email_list)."\n";
		}else{
			print "Failed to retrieve emails.\n";
			return;
		}
		
		// No Email to send
		if(!$email_list)	return;
		
		// Loop Email
		$num = 0;
		foreach($email_list as $email_guid => $email){
			$num++;
			// Send Email
			print "$num) Sending ".$email_guid.": ";
			$data = $appCore->emailManager->sendEmail($email_guid);
			if($data['ok']){
				print "OK\n";
			}else{
				print $data['err']."\n";
			}
		}
		
		print "\n";
	}
	
	function fake_sleep(){
		for($i=0; $i<10; $i++){
			print $i."\n";
			sleep(1);
		}
	}
}
?>