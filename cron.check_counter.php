<?php
/*
php cron.check_counter.php -branch=all

3/8/2016 2:32 PM Andy
- Enhance to check multi server mode.
*/
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '512M');
set_time_limit(0);

// check if myself is running, exit if yes
if (!preg_match('/(root|arms|admin|wsatp)/', `whoami`) || $config['arms_go_modules']){
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
}

$branch_list = array();
$arg = $_SERVER['argv'];
array_shift($arg);

if(!$config['single_server_mode']){	
	// no single server
	$force_bcode = BRANCH_CODE;
	print "Multi Server Mode found: Change to Use $force_bcode\n";
	
	$con->sql_query("select * from branch where code=".ms($force_bcode));
	$b = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$b)	die("Invalid branch '$force_bcode'\n");
	
	$branch_list[$b['id']] = $b;
}
		
while($a = array_shift($arg))
{
	if(preg_match("/^-branch=/", $a)){
		if($force_bcode){
			print "All Branch is not allowed, already force to use $force_bcode.\n";
			continue;
		}
		
		list($dummy, $bcode_list) = explode("=", $a, 2);
		if(!$bcode_list)	die("No branch.\n");
		
		if($bcode_list!="all"){
			$bcode_list = explode(",", $bcode_list);
			foreach($bcode_list as $bcode){
				$con->sql_query("select * from branch where code=".ms($bcode));
				$b = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$b)	die("Invalid branch '$bcode'\n");
				
				$branch_list[$b['id']] = $b;
			}
		}else{
			$con->sql_query("select * from branch where active=1 order by sequence,code");
			while($b = $con->sql_fetchassoc()){
				$branch_list[$b['id']] = $b;
			}
			$con->sql_freeresult();
		}
	}else{
		die("Unknown option: $a\n");
	}
}

if(!$branch_list)	die("No branch.\n");

$COUNTER_CRON = new COUNTER_CRON(); 

foreach($branch_list as $b){	// loop each branch
	// check counter
	$COUNTER_CRON->check_counter_by_branch($b);
	
}

class COUNTER_CRON{
	function check_counter_by_branch($b){
		global $con;
		
		print "Checking ".$b['code'].". . .\n";
		
		$bid = mi($b['id']);
		
		$counter_limit = mi($b['counter_limit']);
		
		if(!$bid)	return;
		
		$q1 = $con->sql_query("select id,network_name,pos_settings from counter_settings where branch_id=$bid and active=1");
		while($r = $con->sql_fetchassoc($q1)){
			$r['pos_settings'] = unserialize($r['pos_settings']);
			
			if($r['pos_settings']['temporary_counter']['allow']){
				// check whether this counter already expired
				if(strtotime($r['pos_settings']['temporary_counter']['date_to']) < time()){
					// need de-activate
					$con->sql_query("update counter_settings set active=0 where branch_id=$bid and id=".mi($r['id']));
					print "Counter $r[network_name] de-activated.\n";
					
					$counter_limit--;
				}
			}
		}
		$con->sql_freeresult($q1);
		if($counter_limit <=0)	$counter_limit = 0;
		
		if($b['counter_limit'] != $counter_limit){
			$con->sql_query("update branch set counter_limit=".mi($counter_limit)." where id=$bid");
			print "Counter limit change from ".$b['counter_limit']." to $counter_limit.\n";
		}
	}
}
?>