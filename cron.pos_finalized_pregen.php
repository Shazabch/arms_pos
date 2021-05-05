<?php
/*
5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.

3/29/2017 5:09 PM Andy
- Fixed multi server branch checking.

6/16/2017 5:07 PM Andy
- Fixed config single_server_mode not found.
*/
define("TERMINAL",1);
include("default_config.php");
include("config.php");
if((version_compare(PHP_VERSION, '7.0.0', '>='))){
	require("include/mysqli.php");
}else{
	require("include/mysql.php");
}
require_once('include/functions.php');

ini_set('memory_limit', '256M');
set_time_limit(0);
error_reporting (E_ALL ^ E_NOTICE);

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

$arg = $_SERVER['argv'];
array_shift($arg);

if($config['single_server_mode']){
	$BRANCH_CODE = strtolower(array_shift($arg));
	if(!$BRANCH_CODE)	die("Please provide BRANCH CODE.\n");
}else{
	$BRANCH_CODE = BRANCH_CODE;
}


while($a = strtolower(array_shift($arg)))
{
	if($a == '-force'){
		$force_run = true;
	}else{
		die("Invalid Parameter $a\n");
	}
}

$branch_info_list = array();
if($BRANCH_CODE == 'all'){
	if(!$config['single_server_mode']){
		die("Multi Server Mode cannot run for all branch.\n");
	}
	
	$con->sql_query("select id,code from branch where active=1 order by sequence,code");
	while($r = $con->sql_fetchassoc()){
		$branch_info_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
}else{
	$con->sql_query("select id,code from branch where active=1 and code=".ms($BRANCH_CODE));
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$tmp)	die("Invalid Branch Code ($BRANCH_CODE)\n");
	
	$branch_info_list[$tmp['id']] = $tmp;
}

$POS_FINALIZED_PREGEN = new POS_FINALIZED_PREGEN();
if($force_run)	$POS_FINALIZED_PREGEN->force_run = true;

foreach($branch_info_list as $bid => $b_info){
	$POS_FINALIZED_PREGEN->run_pregen($bid);
}

class POS_FINALIZED_PREGEN {
	var $force_run = false;	// use to ignore day count checking
	
	function run_pregen($bid){
		global $con, $branch_info_list;
		
		$b_info = $branch_info_list[$bid];
		if(!$b_info)	die("Incorrect branch id#$bid\n");
		
		print "Running $b_info[code]. . .\n";
		
		// get min/max pos date
		$con->sql_query("select min(date) as min_date, max(date) as max_date from pos where branch_id=$bid");
		$pos_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// totally no pos
		if(!$pos_info['min_date'] && !$pos_info['max_date']){
			print "No POS.\n";
			return;
		}
		
		$pos_info['max_date'] = date("Y-m-d");	// manually change max date to latest date
		
		if($pos_info['min_date'] == '0000-00-00'){	// min date is 0000-00-00
			$pos_info['min_date'] = '2013-01-01';
			print "Min POS Date is 0000-00-00, auto start from 2013-01-01\n";
		}		
		
		$curr_time = $start_time = strtotime($pos_info['min_date']);
		$end_time = strtotime($pos_info['max_date']);
		
		$pos_info['total_day'] = floor(($end_time - $start_time)/3600/24)+1;
		
		print "Checking from ".$pos_info['min_date']." to ".$pos_info['max_date'].", Total ".$pos_info['total_day']." days.\n";
		
		// get finalized date info
		$q_pf = $con->sql_query("select date from pos_finalized where branch_id=$bid and date between ".ms($pos_info['min_date'])." and ".ms($pos_info['max_date'])." order by date");
		$num_rows = $con->sql_numrows($q_pf);
		$day_match = false;
		$date_list = array();
		
		if($this->force_run || $num_rows != $pos_info['total_day']){
			while($r = $con->sql_fetchassoc($q_pf)){
				list($y, $m, $d) = explode("-", $r['date']);
				
				if(!mi($y) || !mi($m) || !mi($d)){
					print "Clear invalid date.".$r['date']."\n";
					$con->sql_query("delete from pos_finalized where branch_id=$bid and date=".ms($r['date']));
					continue;
				}
				$date_list[] = $r['date'];
			}
		}else{
			$day_match = true;
		}
		$con->sql_freeresult($q_pf);
		
		// match, no need pregen
		if($day_match){
			print "Day count match, skipped.\n";
			return;
		}
		
		$i = 0;
		
		$added_count = 0;
		while($curr_time <= $end_time){
			$i++;
			$tmp_date = date("Y-m-d", $curr_time);
			
			if(!in_array($tmp_date, $date_list)){
				$upd = array();
				$upd['branch_id'] = $bid;
				$upd['date'] = $tmp_date;
				$upd['finalized'] = 0;
				$upd['finalize_timestamp'] = 0;
				$con->sql_query("insert ignore into pos_finalized ".mysql_insert_by_field($upd));
				
				print "$tmp_date added.\n";
				$added_count++;
			}
			
			$curr_time+=86400;
		}
		print "$added_count day added.\n";
	}
}


?>
