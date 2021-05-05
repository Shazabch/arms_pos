<?php
/*
10/15/2019 4:28 PM Andy
- Added cronManager.runCronMemberPurchasedPackage()

12/9/2019 2:47 PM Andy
- Added cronManager.runCronMemberPushNotification()
*/
class cronManager {
	
	function __construct(){
		
	}
	
	public function checkCronToRun(){
		global $config;
		
		$this->runCronMemberPOS();
		
		if($config['membership_mobile_settings'] && $config['enable_push_notification']){
			// Membership Package
			$this->runCronMemberPurchasedPackage();	// Run every 20 min
			
			// Membership Push Notification
			$this->runCronMemberPushNotification();	// Run every 20 min
		}
	}
	
	private function runCronMemberPOS(){
		global $config;
				
		$phpfile = 'cron.check_member_pos.php';
		$logfile = 'cron.check_member_pos.log';
		
		$logfile_path = dirname(__FILE__)."/../".$logfile;
		
		$run = false;
		if(!file_exists($logfile_path)){
			$run = true;
		}else{
			$diff = time() - filemtime($logfile_path);
			if($diff >= 60){	// last run is more than 1 minute ago
				$run = true;	
			}
		}
		
		if(!$run)	return;	// no need run
		
		$command = "php ".$phpfile;
		if($config['single_server_mode']){
			$command .= " -branch=all";
		}else{
			$command .= " -branch=".BRANCH_CODE;
		}
		
		$command .= " > ".$logfile_path." &";	// using character '&' will make this job run in background
		
		$str = shell_exec($command);
		@chmod($logfile_path, 0777);
	}
	
	private function runCronMemberPurchasedPackage(){
		global $config;
				
		$phpfile = 'cron.check_member_package.php';
		$logfile = 'cron.check_member_package.log';
		
		$logfile_path = dirname(__FILE__)."/../".$logfile;
		
		$run = false;
		if(!file_exists($logfile_path)){
			$run = true;
		}else{
			$diff = time() - filemtime($logfile_path);
			if($diff >= 1200){	// last run is more than 20 minute ago
				$run = true;	
			}
		}
		
		if(!$run)	return;	// no need run
		
		$command = "php ".$phpfile;
		if($config['single_server_mode']){
			$command .= " -branch=all";
		}else{
			$command .= " -branch=".BRANCH_CODE;
		}
		
		$command .= " > ".$logfile_path." &";	// using character '&' will make this job run in background
		
		$str = shell_exec($command);
		@chmod($logfile_path, 0777);
	}
	
	public function runCronMemberPushNotification($run_now = false, $extend_command = ''){
		global $config;
				
		$phpfile = 'cron.check_member_push_notification.php';
		$logfile = 'cron.check_member_push_notification.log';
		
		$logfile_path = dirname(__FILE__)."/../".$logfile;
		
		$run = false;
		if(!file_exists($logfile_path) || $run_now){
			$run = true;
		}else{
			$diff = time() - filemtime($logfile_path);
			if($diff >= 1200){	// last run is more than 20 minute ago
				$run = true;	
			}
		}
		
		if(!$run)	return;	// no need run
		
		$command = "php ".$phpfile;
		if($config['single_server_mode']){
			$command .= " -branch=all";
		}else{
			$command .= " -branch=".BRANCH_CODE;
		}
		if($extend_command)	$command .= ' '.$extend_command;	// Extend Command
		
		$command .= " > ".$logfile_path." &";	// using character '&' will make this job run in background
		
		$str = shell_exec($command);
		@chmod($logfile_path, 0777);
	}
}
?>