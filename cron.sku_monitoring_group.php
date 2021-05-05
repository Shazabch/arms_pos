<?php
/*
11/17/2010 3:16:56 PM Andy
- Add cron to generate report cache data.
*/
define('TERMINAL',1);
include("include/common.php");
include_once("masterfile_sku_monitoring_group.include.php");

ob_end_clean();

ini_set('memory_limit', '512M');
set_time_limit(0);
@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);

$is_generate_report_data = false;
$allgroup = false;
$allbranch = false;
$branch_code = '';
$sku_monitoring_group_id = 0;

$arg = $_SERVER['argv'];
array_shift($arg);
while($a = array_shift($arg))
{
	switch ($a)
	{
		case '-regenerate_group_batch':
			regenerate_group_batch();
			break;
		case '-generate_report_data':
			$is_generate_report_data = true;
			break;
		case '-group_id':
		    $sku_monitoring_group_id = intval(array_shift($arg));
		    break;
		case '-allgroup':
			$allgroup = true;
			break;
		case '-branch':
		    $branch_code = trim(array_shift($arg));
		    break;
        case '-allbranch':
		    $allbranch = true;
		    break;
		default:
			die("Unknown option: $a\n");

	}
}

if($is_generate_report_data){
    generate_report_data();
}

function regenerate_group_batch(){
	global $con;
    if(BRANCH_CODE != 'HQ') die('This cron can only run in HQ.');

	$q1 = $con->sql_query("select id from sku_monitoring_group where changed=1");
	while($r = $con->sql_fetchrow($q1)){
		print "Generating group ID#$r[0]..";
	    regen_sku_monitoring_group_batch($r[0]);
	    print "Done\n";
	}
	$con->sql_freeresult();
}

function generate_report_data(){
	global $con, $sku_monitoring_group_id, $branch_code, $allbranch, $allgroup;
	
	$branch_id_list = array();
	if($allbranch){    // generate for all branches
		$con->sql_query("select id from branch order by id");
		while($r = $con->sql_fetchrow()){
            $branch_id_list[] = $r['id'];
		}
		$con->sql_freeresult();
	}else{  // single branch
        $con->sql_query("select id from branch where code=".ms($branch_code));
        $temp = $con->sql_fetchrow();
        if(!$temp){
			print "Please provide branch. e.g: -branch HQ\n";
			exit;
		}
		$branch_id_list[] = $temp['id'];
		unset($temp);
		$con->sql_freeresult();
	}
	
	if(!$allgroup){
        $con->sql_query("select id from sku_monitoring_group where id=".mi($sku_monitoring_group_id));
        $temp = $con->sql_fetchrow();
        if(!$temp){
			print "Please provide sku monitoring group id. e.g: -group_id 1\n";
			exit;
		}
		$con->sql_freeresult();
	}
	
	$starttime = microtime(true);
	if($sku_monitoring_group_id)    $where = 'where id='.mi($sku_monitoring_group_id);
	$q1 = $con->sql_query("select id, group_name from sku_monitoring_group $where");
	$total_group_count = $con->sql_numrows($q1);
	$total_row_need_to_run = $total_group_count*count($branch_id_list);
	$total_row_runned = 0;
	print "\nGenerating cache...\n";
	while($r = $con->sql_fetchrow($q1)){
		foreach($branch_id_list as $bid){
			generate_sku_monitoring_group_report_data($bid, $r['id']);
			$total_row_runned++;
			print "\r".round($total_row_runned/$total_row_need_to_run*100, 2)."%.....";
		}
	}
	$con->sql_freeresult($q1);
	$endtime = microtime(true);
	print "\nDone.".($endtime-$starttime)." seconds used.\n";
}
?>
