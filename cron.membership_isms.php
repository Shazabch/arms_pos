<?php
/*
1/21/2014 10:04 AM Justin
- Enhanced to check process can run once at a time.

2/7/2014 10:46 AM Andy
- Fix the sms always show sent completed but there is actually no sms was sent.

2/12/2014 10:59 AM Justin
- Bug fixed while sending SMS by using cron, system will have errors at around 4.5k records and causing the SMS to re-send to those numbers already received.
- Enhanced to filter off all other characters for phone numbers except 0-9.

7/30/2014 1:34:41 PM Andy
- Enhance the script to accept both "runall" and "-runall" command.
*/

define('TERMINAL',1);
define('QUERY_PER_CALL', 2000);
include("include/common.php");
include("include/class.isms.php");
ob_end_clean();
//$maintenance->check(1);

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
 
if (count($exec)>1){
	print date("[H:i:s m.d.y]")." Another process is already running\n";
	print_r($exec);
	exit;
}

$arg = $_SERVER['argv'];
$branch = BRANCH_CODE;
$datefilter = "";
$allbranch = false;

array_shift($arg);
while($a = array_shift($arg)){
	switch ($a){
		case '-branch':
			$branch = array_shift($arg);
			break;
		case '-allbranch':
			$allbranch = true;
			break;
      case 'runall':
		case '-runall':
			$datefilter = "all";
			break;
		case '-date':
			$dt = array_shift($arg);
			if (strtotime($dt)!==false){
				$datefilter = $dt;
			}
			else{
				die("Error: Invalid date $dt.");
			}
			break;
		case '-hour':
			$hour = array_shift($hour);
			if ($hour >= 0 && $hour <= 23){
				$hour_filter = $hour;
			}
			else{
				die("Error: Invalid hour $min.");
			}
			break;
		case '-minute':
			$min = array_shift($arg);
			if ($min >= 0 && $min <= 59){
				$min_filter = $min;
			}
			else{
				die("Error: Invalid minute $min.");
			}
			break;
		default:
			die("Unknown option: $a\n");
	}
}

if ($allbranch){
	// run all branch
	$br = $con->sql_query("select code from branch");
	while($r=$con->sql_fetchrow($br)){
		run($r[0]);
	}
}else{
	run($branch);
}

//$con->sql_query("optimize table category_sales_cache");
//$con->sql_query("analyze table category_sales_cache");

function run($branch){
	global $con, $datefilter, $hour_filter, $min_filter, $config;

	$filters = array();
	
	if($datefilter != "all"){
		$filters[] = "date = ".ms($datefilter);
		
		if($hour_filter){
			$filters[] = "send_hour = ".mi($datefilter);
		}
		
		if($min_filter){
			$filters[] = "send_min = ".mi($min_filter);
		}
	}
	
	$curr_date = date("Y-m-d H:i:s");
	
	$filters[] = "approved = 1 and active = 1 and cron_status = 0";
	$filter = join(" and ", $filters);
	
	$q1 = $con->sql_query("select * from membership_isms where $filter order by added");

	$isms = new iSMS();
	
	while($r = $con->sql_fetchassoc($q1)){
		$send_date = $r['send_date']." ".$r['send_hour'].":".$r['send_min'].":00";
		
		// found it is future sms, skip for now
		if(strtotime($curr_date) < strtotime($send_date)) continue;
	
		$r['filters'] = unserialize($r['filters']);
		if($r['filters']) $m_filter = "where ".join(" and ", $r['filters']);
		
		if(!$r['item_copy']){
			$con->sql_query("delete from membership_isms_items where m_isms_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
		}
		
		$q2 = $con->sql_query("select * from membership m ".$m_filter." order by nric");
		
		$numbers = array();
		if(!$r['item_copy']){
			while($r1=$con->sql_fetchassoc($q2)){
				$mobile = trim($r1['phone_3']);
				//$mobile = str_replace("-", "", $mobile);
				$mobile = preg_replace("/[^0-9]/", "", $mobile); 

				// go to phone 2, then phone 1 if blank
				if (!preg_match('/^01\d{8,9}$/',$mobile)) $mobile = trim($r1['phone_2']); 
				$mobile = preg_replace("/[^0-9]/", "", $mobile); 
				//$mobile = str_replace("-", "", $mobile);
				if (!preg_match('/^01\d{8,9}$/',$mobile)) $mobile = trim($r1['phone_1']); 
				$mobile = preg_replace("/[^0-9]/", "", $mobile); 
				//$mobile = str_replace("-", "", $mobile);
				
				// check valid numbers
				if (preg_match('/^01\d{8,9}$/',$mobile)){
					$numbers[] = '6'.$mobile;
				
					// copy to items table for send purpose
					$ins = array();
					$ins['branch_id'] = $r['branch_id'];
					$ins['m_isms_id'] = $r['id'];
					$ins['nric'] = $r1['nric'];
					$ins['number'] = '6'.$mobile;
					$ins['sms_sent'] = 0;
					
					$con->sql_query("insert into membership_isms_items ".mysql_insert_by_field($ins));
				}

			}
			$con->sql_freeresult($q2);

			// update item copy
			$con->sql_query("update membership_isms set item_copy = 1 where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));			
		}else{
			$q2 = $con->sql_query("select * from membership_isms_items where m_isms_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])." and sms_sent = 0");
			
			while($r1 = $con->sql_fetchassoc($q2)){
				$numbers[] = $r1['number'];
			}
		}
		
		if($numbers){
			$org_numbers = $numbers;
			$success_count = 0;
	
			// get total numbers and update into main info
			$q2 = $con->sql_query("select count(*) as ttl_count from membership_isms_items where m_isms_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
			$tmp = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			// update total recipient to avoid got changes during the filter
			$con->sql_query("update membership_isms set total_recipient = ".mi($tmp['ttl_count'])." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));

			while($numbers){
				$n = array_splice($numbers, $n, 100);
				$success_count = $isms->send_sms($n, $r['msg']);
				//$success_count = count($n);
				
				if($success_count){
					// update sms that being sent out for the following numbers
					$con->sql_query("update membership_isms_items set sms_sent = 1 where number in (".join(",", $n).") and m_isms_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
					
					// get total success count
					$q2 = $con->sql_query("select count(*) as ttl_sent from membership_isms_items where m_isms_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])." and sms_sent = 1");
					$sms_info = $con->sql_fetchassoc($q2);
					$con->sql_freeresult($q2);
					
					$con->sql_query("update membership_isms set last_update = CURRENT_TIMESTAMP, total_run = ".mi($sms_info['ttl_sent'])." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
				
					$con->sql_query("update membership_isms_items set sms_sent = 1 where m_isms_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])." and number in (".join(",", $n).")");
					
					print "Message [".$r['msg']."] sent to number(s)".join(",", $n)."\n";
				}
			}
			
			// get total success count
			$q2 = $con->sql_query("select count(*) as ttl_sent from membership_isms_items where m_isms_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])." and sms_sent = 1");
			$sms_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			// update one more time to make sure the total run is fully completed
			$con->sql_query("update membership_isms set last_update = CURRENT_TIMESTAMP, total_run = ".mi($sms_info['ttl_sent'])." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));

			// get credit balance
			$cc = $isms->get_credit();
			
			// get total success count
			$q2 = $con->sql_query("select count(*) as ttl_sent from membership_isms_items where m_isms_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])." and sms_sent = 1");
			$sms_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			$failed_count = count($org_numbers) - $sms_info['ttl_sent'];
			$failed_msg = "";
			if($failed_count != 0) $failed_msg = "and failed to send to $failed_count member(s).";
			log_br(1, 'Membership', $r['id'], "Send SMS to ".mi($sms_info['ttl_sent'])." member(s) $failed_msg (Remaining credit: $cc)");
			
			// update cron status and item copy to done
			$con->sql_query("update membership_isms set cron_status = 1 where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
		}
	}
	$con->sql_freeresult($q1);
	
	print "Done\n";
}

?>
