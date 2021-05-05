<?php
/*
1/22/2019 11:24 AM Andy
- Enhanced to compatible to Jan 2019 database.

1/8/2020 3:35 PM Andy
- Enhanced to compatible to maintenance version up to v415.

2/26/2021 10:50 AM Andy
- Increased memory_limit to 2G.

3/17/2021 5:07 PM Andy
- Added pos_payment, pos_transaction_audit_log and pos_transaction_ejournal as big table.

///////////////////////////////////////

/opt/lampp/bin/php admin.db_cutoff.php -date=2010-04-01 -branch=jitra skip_apache_check

php admin.db_cutoff.php -date=2013-04-01 -branch=kangar skip_apache_check
php admin.db_cutoff.php -date=2013-04-01 -branch=jitra skip_apache_check -mode=cutoff

php admin.db_cutoff.php -date=2013-04-01 -branch=gurun skip_apache_check skip_big_table
php admin.db_cutoff.php -date=2013-04-01 -branch=gurun skip_apache_check skip_big_table -mode=cutoff

php admin.db_cutoff.php -date=2013-04-01 -branch=tmerah skip_apache_check skip_big_table

php admin.db_cutoff.php -date=2013-04-01 -branch=dungun skip_apache_check skip_big_table

php admin.db_cutoff.php -date=2013-04-01 -branch=baling skip_apache_check skip_big_table

php admin.db_cutoff.php -date=2013-04-01 -branch=jitra skip_apache_check skip_big_table multi_server_force_branch

php admin.db_cutoff.php -date=2013-04-01 -branch=kangar skip_apache_check skip_big_table multi_server_force_branch

php admin.db_cutoff.php -date=2013-04-01 -branch=bserai skip_apache_check skip_big_table

php admin.db_cutoff.php -date=2017-01-01 skip_apache_check

2020-09-23
php admin.db_cutoff.php -date=2015-04-01 skip_apache_check skip_big_table multi_server_force_branch

Test Run
php admin.db_cutoff.php -date=2020-01-01 skip_apache_check
php admin.db_cutoff.php -date=2019-01-01 skip_apache_check
php admin.db_cutoff.php -date=2020-07-01 skip_apache_check

Actual Cutoff
php admin.db_cutoff.php -date=2020-01-01 -mode=cutoff skip_apache_check
php admin.db_cutoff.php -date=2019-01-01 -mode=cutoff skip_big_table
php admin.db_cutoff.php -date=2020-07-01 -mode=cutoff

Big Table Query
delete from pos_items where date<"2020-01-01"; optimize table pos_items;
delete from pos_payment where date<"2020-01-01"; optimize table pos_payment;
delete from log where timestamp<"2020-01-01"; optimize table log;
delete from pos_transaction_audit_log where date<"2020-01-01"; optimize table pos_transaction_audit_log;
delete from pos_transaction_ejournal where date<"2020-01-01"; optimize table pos_transaction_ejournal;
*/

//define('armshq_aneka', 1);define('BRANCH_CODE', 'GURUN');
//define('armshq_gmart', 1);
//define('armshq_smo', 1);
//define('armshq_pkt', 1);
//define('armshq_rakanda', 1);
//define('armshq_growthmart', 1);
define('armshq_segi', 1);

include("include/common.php");

if(defined('armshq_aneka')){
	$config['single_server_mode'] = 0;
}

include("admin.server_maintenance.include.php");


define("TERMINAL",1);
ini_set('memory_limit', '2048M');
set_time_limit(0);

// check don run duplicate
@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
if (count($exec)>1)
{
 	print date("[H:i:s m.d.y]")." Another process is already running\n";
	print_r($exec);
	exit;
}

$arg = $_SERVER['argv'];
$run_mode = 'analysis';
$skip_apache_check = false;
ob_end_flush();
fix_terminal_smarty();
	
array_shift($arg); //drop the first option
while($arg)
{
	$a = strtolower(array_shift($arg));
	if(preg_match("/^-date=/", $a)){	
		// date
		$cutoff_date = date("Y-m-d", strtotime(str_replace("-date=", "", $a)));
	}elseif(preg_match("/^-mode=/", $a)){
		// run mode, if no pass will be default 'analysis'
		$run_mode = str_replace("-mode=", "", $a);
	}elseif($a=='skip_apache_check'){
		$skip_apache_check = true;
	}elseif(preg_match("/^-branch=/", $a)){	
		// date
		$bcode = str_replace("-branch=", "", $a);
	}
	elseif($a == 'skip_big_table'){	
		// skip big table
		$skip_big_table = true;
	}
	elseif($a == 'multi_server_force_branch'){	
		// skip big table
		$multi_server_force_branch = true;
	}
	else{
		$err[] = "Unknown option $a\n";
	}
}

if(!$cutoff_date)	die("No Cutoff date\n");
if($run_mode != 'analysis' && $run_mode != 'cutoff')	die("Invalid Mode.\n");

if(!$config['single_server_mode'] && !$multi_server_force_branch){
	// multi server must run by each branch
	if(!$bcode)	die("No Branch Code.\n");
	//die(BRANCH_CODE .' = '.$bcode);
	if(strtoupper($bcode) != BRANCH_CODE){
		print "Branch Code different with server branch code. ".BRANCH_CODE." \n";exit;
	}
}
// check apache whether it is running
if(!$skip_apache_check){
	$exec = array();
	exec('/etc/init.d/apache2 status', $exec);
	
	if(is_array($exec) && strpos($exec[0], 'is running')){
		print_r($exec);
		print "*** This module cannot be run while apache is running.***\n";
		exit;
	}
}

class ARCHIVE_SALES extends Module{
	var $delete_query = array();
	var $optimize_table_list = array();
	var $source_label = 'ARC_MA_SCR';
	var $bid_list = array();
	var $table_checking_list = array();
	var $max_delete_row = 1000000;
	var $known_big_table_list = array('pos', 'pos_items', 'pos_payment', 'log', 'pos_transaction_audit_log', 'pos_transaction_ejournal');
	var $skip_big_table = false;
	var $is_cutoff = false;
	var $cutoff_id = 0;
	
	function _default(){
		global $con, $cutoff_date, $run_mode, $db_default_connection, $bcode, $skip_big_table;
		
		$starttime = microtime(true);
		
		$this->one_day_b4_cutoff = date("Y-m-d", strtotime("-1 day", strtotime($cutoff_date)));
        $this->date_key = date("Ymd", strtotime($cutoff_date));
        $this->cutoff_date = $cutoff_date;
        if($run_mode == 'cutoff')	$this->is_cutoff = true;
        
		// find y,m for last month for those table only got year and month
		$this->cutoff_y = mi(date("Y", strtotime($cutoff_date)));
        $this->cutoff_m = mi(date("m", strtotime($cutoff_date)));
        $this->cutoff_m--;
        if($this->cutoff_m<1){
            $this->cutoff_m = 12;
            $this->cutoff_y --;
		}
		
		if($skip_big_table)	$this->skip_big_table = true;

		if($bcode){
			// got select by branch
			$con->sql_query("select id from branch where code=".ms($bcode));
			$this->bid = mi($con->sql_fetchfield(0));
			$con->sql_freeresult();
			
			if(!$this->bid)	die("Invalid Branch Code\n");
			
			print "Branch: $bcode ($this->bid), Date: ".$this->cutoff_date."\n";
		}else{
			print "Branch: All, Date: ".$this->cutoff_date."\n";
		}
		
		// get all branch id
		$con->sql_query("select id from branch order by id");
		while($r = $con->sql_fetchassoc()){
			$this->bid_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		// start
		$this->record_db_info();
		
		// po
		$this->archive_PO();
		
		// DO
        $this->archive_DO();
		
		// ADJUSTMENT
        $this->archive_ADJ();
		
		// GRR/GRN
        $this->archive_GRN();
		
		// GRA
        $this->archive_GRA();
		
		// cnote /dnote
		$this->archive_cnote_dnote();
		
		// SALES_ORDER
        $this->archive_SALES_ORDER();
		
		// CI / CN / DN
        $this->archive_CI_CN_DN();
		
		// consignment monthly report
        $this->archive_CON_MONTHLY_REPORT();
		
		// pos
		$this->archive_POS();	// check and backup sales
		
		// LOG
		$this->archive_LOG();
		
		// LOGIN_TICKETS
        $this->archive_LOGIN_TICKETS();
		
		// PICKING_LIST
        $this->archive_PICKING_LIST();
		
		// PM
        $this->archive_PM();
		
		// PROMOTION
        $this->archive_PROMOTION();
		
		// STOCK_TAKE
        $this->archive_STOCK_TAKE();
		
		// member
		$this->archive_MEMBER_DATA();	// check and backup member data
		
		// CACHE
        $this->archive_CACHE();
		
		// SKU
		$this->archive_SKU_DATA();
		
		// approval history
		$this->archive_APPROVAL_HISTORY();
		
		// Foreign Currency
		$this->archive_FOREIGN_CURRENCY();
		
		// OTHERS
        $this->archive_OTHERS();
		
		// Currency
		$this->mark_currency_history();	// mark currency exchange rate cutoff data
		
		$this->mark_member_history();	// mark member point cutoff data
		
		$this->mark_sku_history(); // mark sku cutoff data
		
		if($this->is_cutoff){
			// delete data
			$this->delete_data();
		}
		
		// finish
		$this->finalise_db_info();
		
		$endtime = microtime(true);
		$total_second = round($endtime-$starttime);
        print "\n\nTotal ".mi($this->total['total']['total'])." data found. Total $total_second Seconds Used. ".$this->secondsToTime($total_second)." used.";
        print "\nAll Done!\n";
	}
	
	function secondsToTime($seconds) {
		$h = floor($seconds / 3600);
		$seconds -= $h * 3600;

		$m = floor($seconds / 60);
		$seconds -= $m * 60;

		$s = $seconds;
		return "$h hours, $m minutes and $s seconds";
	}
	
	private function check_table_exists($chk_tbl){
		global $con;
		
		// use cache first
		if(isset($this->table_checking_list[$chk_tbl])){
			return $this->table_checking_list[$chk_tbl];
		}
		
		// the con should be armshq_backup
		$got_tbl = $con->sql_query("explain $chk_tbl",false,false);
		$con->sql_freeresult($got_tbl);
		
		$this->table_checking_list[$chk_tbl] = ($got_tbl ? "$chk_tbl already exists." : false);
		return $this->table_checking_list[$chk_tbl];
	}
	
	private function archive_MEMBER_DATA(){
		global $con;
		
		// membership_drawer_history
		print "\nChecking membership_drawer_history...";
		// get row count of membership_drawer_history
		$sql = "select mdh.* from membership_drawer_history mdh where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
		$q1 = $con->sql_query($sql);
		$row_count = $con->sql_numrows($q1);
		$this->total['membership_drawer_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		$con->sql_freeresult($q1);
		print "\r membership_drawer_history have $row_count rows.";
		
		if($this->is_cutoff){
			// record delete query
			$this->delete_query['membership_drawer_history'] = "delete from membership_drawer_history 
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
			$this->optimize_table_list['membership_drawer_history'][] = 'membership_drawer_history';
		}
		
		// membership_inventory_history
		print "\nChecking membership_inventory_history...";
		// get row count of membership_inventory_history
		$sql = "select mih.* from membership_inventory_history mih where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
		$q1 = $con->sql_query($sql);
		$row_count = $con->sql_numrows($q1);
		$this->total['membership_inventory_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		$con->sql_freeresult($q1);
		print "\r membership_inventory_history have $row_count rows.";
		
		if($this->is_cutoff){			
			// record delete query
			$this->delete_query['membership_inventory_history'] = "delete from membership_inventory_history 
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
			$this->optimize_table_list['membership_inventory_history'][] = 'membership_inventory_history';
		}
		
		// membership_isms
		print "\nChecking membership_isms...";
		// get row count of membership_isms
		$sql = "select mis.* from membership_isms mis where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." send_date<".ms($this->cutoff_date);
		$q1 = $con->sql_query($sql);
		$row_count = $con->sql_numrows($q1);
		$this->total['membership_isms']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		$con->sql_freeresult($q1);
		print "\r membership_isms have $row_count rows.";
				
		// membership_isms_items
		print "\nChecking membership_isms_items...";
		$sql = "select misi.* 
		from membership_isms_items misi
		join membership_isms mis on mis.branch_id=misi.branch_id and mis.id=misi.m_isms_id
		where ".($this->bid?"mis.branch_id=".mi($this->bid)." and ":"")." mis.send_date<".ms($this->cutoff_date);
		$q1 = $con->sql_query($sql);
		$row_count = $con->sql_numrows($q1);
		$this->total['membership_isms_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		$con->sql_freeresult($q1);
		print "\r membership_isms_items have $row_count rows.";
				
		if($this->is_cutoff){			
			// record delete query
			$this->delete_query['membership_isms_items'] = "delete mis,misi
			from membership_isms mis
			left join membership_isms_items misi on mis.branch_id=misi.branch_id and mis.id=misi.m_isms_id
			where ".($this->bid?"mis.branch_id=".mi($this->bid)." and ":"")." mis.send_date<".ms($this->cutoff_date);
			$this->optimize_table_list['membership_isms_items'][] = 'membership_isms';
			$this->optimize_table_list['membership_isms_items'][] = 'membership_isms_items';
		}
		
		// membership_points
		print "\nChecking membership_points...";
		// get row count of membership_points
        $sql = "select mp.* from membership_points mp where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date)." and (mp.remark is null or mp.remark<>".ms($this->source_label).")";
		$q1 = $con->sql_query($sql);
		$row_count = $con->sql_numrows($q1);
		$this->total['membership_points']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		$con->sql_freeresult($q1);
		print "\r membership_points have $row_count rows.";
		
		
		if($this->is_cutoff){			
			// record delete query
			$this->delete_query['membership_points'] = "delete from membership_points 
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date)." and (remark is null or remark<>".ms($this->source_label).")";
			$this->optimize_table_list['membership_points'][] = 'membership_points';
		}
		
		// membership_promotion_items
		print "\nChecking membership_promotion_items...";
		// get row count of membership_promotion_items
        $sql = "select mpi.* from membership_promotion_items mpi where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		$q1 = $con->sql_query($sql);
		$row_count = $con->sql_numrows($q1);
		$this->total['membership_promotion_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		$con->sql_freeresult($q1);
		print "\r membership_promotion_items have $row_count rows.";
		
		
		if($this->is_cutoff){			
			// record delete query
			$this->delete_query['membership_promotion_items'] = "delete from membership_promotion_items 
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['membership_promotion_items'][] = 'membership_promotion_items';
		}
		
		// membership_promotion_mix_n_match_items
		print "\nChecking membership_promotion_mix_n_match_items...";
		// get row count of membership_promotion_mix_n_match_items
        $sql = "select mpi.* from membership_promotion_mix_n_match_items mpi where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		$q1 = $con->sql_query($sql);
		$row_count = $con->sql_numrows($q1);
		$this->total['membership_promotion_mix_n_match_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		$con->sql_freeresult($q1);
		print "\r membership_promotion_mix_n_match_items have $row_count rows.";
		
		
		if($this->is_cutoff){			
			// record delete query
			$this->delete_query['membership_promotion_mix_n_match_items'] = "delete from membership_promotion_mix_n_match_items 
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['membership_promotion_mix_n_match_items'][] = 'membership_promotion_mix_n_match_items';
		}
		
		// membership_receipt
		print "\nChecking membership_receipt...";
		// get row count
        $sql = "select mr.* from membership_receipt mr where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
		$q1 = $con->sql_query($sql);
		$row_count = $con->sql_numrows($q1);
		$this->total['membership_receipt']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		$con->sql_freeresult($q1);
		print "\r membership_receipt have $row_count rows.";
				
		// membership_receipt_items
		print "\nChecking membership_receipt_items...";
		$sql = "select mri.* 
		from membership_receipt_items mri
		join membership_receipt mr on mr.branch_id=mri.branch_id and mr.counter_id=mri.counter_id and mr.id=mri.receipt_id
		where ".($this->bid?"mr.branch_id=".mi($this->bid)." and ":"")." mr.timestamp<".ms($this->cutoff_date);
		$q1 = $con->sql_query($sql);
		$row_count = $con->sql_numrows($q1);
		$this->total['membership_receipt_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		$con->sql_freeresult($q1);
		print "\r membership_receipt_items have $row_count rows.";
				
		if($this->is_cutoff){			
			// record delete query
			$this->delete_query['membership_receipt_items'] = "delete mr,mri
			from membership_receipt mr
			left join membership_receipt_items mri on mr.branch_id=mri.branch_id and mr.counter_id=mri.counter_id and mr.id=mri.receipt_id
			where ".($this->bid?"mr.branch_id=".mi($this->bid)." and ":"")." mr.timestamp<".ms($this->cutoff_date);
			$this->optimize_table_list['membership_receipt_items'][] = 'membership_receipt_items';
			$this->optimize_table_list['membership_receipt_items'][] = 'membership_receipt';
		}
		
		// membership_redemption
		print "\nChecking membership_redemption...";
		// get row count
        $sql = "select mr.* from membership_redemption mr where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		$q1 = $con->sql_query($sql);
		$row_count = $con->sql_numrows($q1);
		$this->total['membership_redemption']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		$con->sql_freeresult($q1);
		print "\r membership_redemption have $row_count rows.";
				
		// membership_redemption_items
		print "\nChecking membership_redemption_items...";
		$sql = "select mri.* 
		from membership_redemption_items mri
		join membership_redemption mr on mr.branch_id=mri.branch_id and mr.id=mri.membership_redemption_id
		where ".($this->bid?"mr.branch_id=".mi($this->bid)." and ":"")." mr.date<".ms($this->cutoff_date);
		$q1 = $con->sql_query($sql);
		$row_count = $con->sql_numrows($q1);
		$this->total['membership_redemption_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		$con->sql_freeresult($q1);
		print "\r membership_redemption_items have $row_count rows.";
				
		if($this->is_cutoff){			
			// record delete query
			$this->delete_query['membership_redemption_items'] = "delete mr,mri
			from membership_redemption mr
			left join membership_redemption_items mri on mr.branch_id=mri.branch_id and mr.id=mri.membership_redemption_id
			where ".($this->bid?"mr.branch_id=".mi($this->bid)." and ":"")." mr.date<".ms($this->cutoff_date);
			$this->optimize_table_list['membership_redemption_items'][] = 'membership_redemption_items';
			$this->optimize_table_list['membership_redemption_items'][] = 'membership_redemption';
		}
		
		// membership_fav_items
		print "\nChecking membership_fav_items";
		$sql_count = "select count(*) from membership_fav_items where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from membership_fav_items where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r membership_fav_items have $row_count rows.";
		
		$this->total['membership_fav_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['membership_fav_items'] = "delete 
			from membership_fav_items
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['membership_fav_items'][] = 'membership_fav_items';
		}
	}
	
	private function archive_POS(){
		global $con;
		
		// POS
		print "\nChecking pos...";
		
		$sql_count = "select count(*) from pos where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pos.date<".ms($this->cutoff_date);
		$q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
        $sql = "select pos.* from pos where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pos.date<".ms($this->cutoff_date);
		print "\r pos have $row_count rows.";
		
		$this->total['pos']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos'] = "delete 
			from pos
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pos.date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos'][] = 'pos';
		}
		
		// POS_ITEMS
		print "\nChecking pos_items...";
		$sql_count = "select count(*) from pos_items pi where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pi.date<".ms($this->cutoff_date);
		$q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select pi.* from pos_items pi where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pi.date<".ms($this->cutoff_date);
		print "\r pos_items have $row_count rows.";
		
		$this->total['pos_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_items'] = "delete 
			from pos_items
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_items'][] = 'pos_items';
		}
		
		// POS_CASH_DOMINATION
		print "\nChecking pos_cash_domination...";
		$sql_count = "select count(*) from pos_cash_domination pcd where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pcd.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select pcd.* from pos_cash_domination pcd where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pcd.date<".ms($this->cutoff_date);
		print "\r pos_cash_domination have $row_count rows.";
		
		$this->total['pos_cash_domination']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_cash_domination'] = "delete 
			from pos_cash_domination
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_cash_domination'][] = 'pos_cash_domination';
		}
		
		// POS_CASH_HISTORY
		print "\nChecking pos_cash_history...";
		$sql_count = "select count(*) from pos_cash_history pch where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pch.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select pch.* from pos_cash_history pch where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pch.date<".ms($this->cutoff_date);
		print "\r pos_cash_history have $row_count rows.";
		
		$this->total['pos_cash_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_cash_history'] = "delete 
			from pos_cash_history
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_cash_history'][] = 'pos_cash_history';
		}
		
		// POS_COUNTER_COLLECTION
		print "\nChecking pos_counter_collection...";
		$sql_count = "select count(*) from pos_counter_collection pcc where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pcc.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select pcc.* from pos_counter_collection pcc where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pcc.date<".ms($this->cutoff_date);
		print "\r pos_counter_collection have $row_count rows.";
		
		$this->total['pos_counter_collection']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_counter_collection'] = "delete 
			from pos_counter_collection
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_counter_collection'][] = 'pos_counter_collection';
		}
		
		// POS_COUNTER_COLLECTION_TRACKING
		print "\nChecking pos_counter_collection_tracking...";
		$sql_count = "select count(*) from pos_counter_collection_tracking pcct where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pcct.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select pcct.* from pos_counter_collection_tracking pcct where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pcct.date<".ms($this->cutoff_date);
		print "\r pos_counter_collection_tracking have $row_count rows.";
		
		$this->total['pos_counter_collection_tracking']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_counter_collection_tracking'] = "delete 
			from pos_counter_collection_tracking
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_counter_collection_tracking'][] = 'pos_counter_collection_tracking';
		}
		
		// POS_DRAWER
		print "\nChecking pos_drawer...";
		$sql_count = "select count(*) from pos_drawer pd where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pd.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select pd.* from pos_drawer pd where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pd.date<".ms($this->cutoff_date);
		print "\r pos_drawer have $row_count rows.";
		
		$this->total['pos_drawer']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_drawer'] = "delete 
			from pos_drawer
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_drawer'][] = 'pos_drawer';
		}
		
		// POS_FINALIZED
		print "\nChecking pos_finalized...";
		$sql_count = "select count(*) from pos_finalized pf where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pf.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);

		$sql = "select pf.* from pos_finalized pf where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pf.date<".ms($this->cutoff_date);
		print "\r pos_finalized have $row_count rows.";
		
		$this->total['pos_finalized']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_finalized'] = "delete 
			from pos_finalized
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_finalized'][] = 'pos_finalized';
		}
		
		// POS_GOODS_RETURN
		print "\nChecking pos_goods_return";
		$sql_count = "select count(*) from pos_goods_return pgr where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pgr.date<".ms($this->cutoff_date);
        $q1 =  $con->sql_query($sql_count);
		$row_count =  mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select pgr.* from pos_goods_return pgr where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pgr.date<".ms($this->cutoff_date);
		print "\r pos_goods_return have $row_count rows.";
		
		$this->total['pos_goods_return']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_goods_return'] = "delete 
			from pos_goods_return
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_goods_return'][] = 'pos_goods_return';
		}
		
		// POS_PAYMENT
		print "\nChecking pos_payment";
		$sql_count = "select count(*) from pos_payment pp where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pp.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select pp.* from pos_payment pp where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pp.date<".ms($this->cutoff_date);
		print "\r pos_payment have $row_count rows.";
		
		$this->total['pos_payment']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_payment'] = "delete 
			from pos_payment
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_payment'][] = 'pos_payment';
		}
		
		// POS_RECEIPT_CANCEL
		print "\nChecking pos_receipt_cancel…";
		$sql_count = "select count(*) from pos_receipt_cancel prc where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." prc.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select prc.* from pos_receipt_cancel prc where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." prc.date<".ms($this->cutoff_date);
		print "\r pos_receipt_cancel have $row_count rows.";
		
		$this->total['pos_receipt_cancel']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_receipt_cancel'] = "delete 
			from pos_receipt_cancel
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_receipt_cancel'][] = 'pos_receipt_cancel';
		}
		
		// pos_cashier_finalize
		print "\nChecking pos_cashier_finalize";
		$sql_count = "select count(*) from pos_cashier_finalize where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_cashier_finalize where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_cashier_finalize have $row_count rows.";
		
		$this->total['pos_cashier_finalize']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_cashier_finalize'] = "delete 
			from pos_cashier_finalize
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_cashier_finalize'][] = 'pos_cashier_finalize';
		}
		
		// pos_counter_finalize
		print "\nChecking pos_counter_finalize";
		$sql_count = "select count(*) from pos_counter_finalize where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_counter_finalize where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_counter_finalize have $row_count rows.";
		
		$this->total['pos_counter_finalize']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_counter_finalize'] = "delete 
			from pos_counter_finalize
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_counter_finalize'][] = 'pos_counter_finalize';
		}
		
		// pos_credit_note
		print "\nChecking pos_credit_note";
		$sql_count = "select count(*) from pos_credit_note where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_credit_note where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_credit_note have $row_count rows.";
		
		$this->total['pos_credit_note']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_credit_note'] = "delete 
			from pos_credit_note
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_credit_note'][] = 'pos_credit_note';
		}
		
		// pos_delete_items
		print "\nChecking pos_delete_items";
		$sql_count = "select count(*) from pos_delete_items where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_delete_items where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_delete_items have $row_count rows.";
		
		$this->total['pos_delete_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_delete_items'] = "delete 
			from pos_delete_items
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_delete_items'][] = 'pos_delete_items';
		}
		
		// pos_deposit
		print "\nChecking pos_deposit";
		$sql_count = "select count(*) from pos_deposit where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_deposit where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_deposit have $row_count rows.";
		
		$this->total['pos_deposit']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_deposit'] = "delete 
			from pos_deposit
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_deposit'][] = 'pos_deposit';
		}
		
		// pos_deposit_status
		print "\nChecking pos_deposit_status";
		$sql_count = "select count(*) from pos_deposit_status where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." deposit_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_deposit_status where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." deposit_date<".ms($this->cutoff_date);
		print "\r pos_deposit_status have $row_count rows.";
		
		$this->total['pos_deposit_status']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_deposit_status'] = "delete 
			from pos_deposit_status
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." deposit_date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_deposit_status'][] = 'pos_deposit_status';
		}
		
		// pos_deposit_status_history
		print "\nChecking pos_deposit_status_history";
		$sql_count = "select count(*) from pos_deposit_status_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." deposit_pos_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_deposit_status_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." deposit_pos_date<".ms($this->cutoff_date);
		print "\r pos_deposit_status_history have $row_count rows.";
		
		$this->total['pos_deposit_status_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_deposit_status_history'] = "delete 
			from pos_deposit_status_history
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." deposit_pos_date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_deposit_status_history'][] = 'pos_deposit_status_history';
		}
		
		// pos_error
		print "\nChecking pos_error";
		$sql_count = "select count(*) from pos_error where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_error where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_error have $row_count rows.";
		
		$this->total['pos_error']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_error'] = "delete 
			from pos_error
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_error'][] = 'pos_error';
		}
		
		// pos_items_changes
		print "\nChecking pos_items_changes";
		$sql_count = "select count(*) from pos_items_changes where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_items_changes where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_items_changes have $row_count rows.";
		
		$this->total['pos_items_changes']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_items_changes'] = "delete 
			from pos_items_changes
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_items_changes'][] = 'pos_items_changes';
		}
		
		// pos_items_sn
		print "\nChecking pos_items_sn";
		$sql_count = "select count(*) from pos_items_sn where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_items_sn where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_items_sn have $row_count rows.";
		
		$this->total['pos_items_sn']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_items_sn'] = "delete 
			from pos_items_sn
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_items_sn'][] = 'pos_items_sn';
		}
		
		// pos_items_sn_history
		print "\nChecking pos_items_sn_history";
		$sql_count = "select count(*) from pos_items_sn_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_items_sn_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
		print "\r pos_items_sn_history have $row_count rows.";
		
		$this->total['pos_items_sn_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_items_sn_history'] = "delete 
			from pos_items_sn_history
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_items_sn_history'][] = 'pos_items_sn_history';
		}
		
		// pos_member_point_adjustment
		print "\nChecking pos_member_point_adjustment";
		$sql_count = "select count(*) from pos_member_point_adjustment where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_member_point_adjustment where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_member_point_adjustment have $row_count rows.";
		
		$this->total['pos_member_point_adjustment']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_member_point_adjustment'] = "delete 
			from pos_member_point_adjustment
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_member_point_adjustment'][] = 'pos_member_point_adjustment';
		}
		
		// pos_mix_match_usage
		print "\nChecking pos_mix_match_usage";
		$sql_count = "select count(*) from pos_mix_match_usage where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_mix_match_usage where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_mix_match_usage have $row_count rows.";
		
		$this->total['pos_mix_match_usage']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_mix_match_usage'] = "delete 
			from pos_mix_match_usage
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_mix_match_usage'][] = 'pos_mix_match_usage';
		}
		
		// pos_user_log
		print "\nChecking pos_user_log";
		$sql_count = "select count(*) from pos_user_log where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_user_log where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_user_log have $row_count rows.";
		
		$this->total['pos_user_log']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_user_log'] = "delete 
			from pos_user_log
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_user_log'][] = 'pos_user_log';
		}
		
		// counter_inventory
		print "\nChecking counter_inventory";
		$sql_count = "select count(*) from counter_inventory where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from counter_inventory where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
		print "\r counter_inventory have $row_count rows.";
		
		$this->total['counter_inventory']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['counter_inventory'] = "delete 
			from counter_inventory
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
			$this->optimize_table_list['counter_inventory'][] = 'counter_inventory';
		}
		
		// pos_transaction_audit_log
		print "\nChecking pos_transaction_audit_log";
		$sql_count = "select count(*) from pos_transaction_audit_log where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_transaction_audit_log where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_transaction_audit_log have $row_count rows.";
		
		$this->total['pos_transaction_audit_log']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_transaction_audit_log'] = "delete 
			from pos_transaction_audit_log
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_transaction_audit_log'][] = 'pos_transaction_audit_log';
		}
		
		// pos_transaction_ejournal
		print "\nChecking pos_transaction_ejournal";
		$sql_count = "select count(*) from pos_transaction_ejournal where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_transaction_ejournal where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_transaction_ejournal have $row_count rows.";
		
		$this->total['pos_transaction_ejournal']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_transaction_ejournal'] = "delete 
			from pos_transaction_ejournal
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_transaction_ejournal'][] = 'pos_transaction_ejournal';
		}
		
		// pos_finalised_error
		print "\nChecking pos_finalised_error";
		$sql_count = "select count(*) from pos_finalised_error where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_finalised_error where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_finalised_error have $row_count rows.";
		
		$this->total['pos_finalised_error']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_finalised_error'] = "delete 
			from pos_finalised_error
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_finalised_error'][] = 'pos_finalised_error';
		}
		
		// pos_transaction_counter_sales_record
		print "\nChecking pos_transaction_counter_sales_record";
		$sql_count = "select count(*) from pos_transaction_counter_sales_record where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_transaction_counter_sales_record where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_transaction_counter_sales_record have $row_count rows.";
		
		$this->total['pos_transaction_counter_sales_record']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_transaction_counter_sales_record'] = "delete 
			from pos_transaction_counter_sales_record
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_transaction_counter_sales_record'][] = 'pos_transaction_counter_sales_record';
		}
		
		// pos_transaction_sync_server_tracking
		print "\nChecking pos_transaction_sync_server_tracking";
		$sql_count = "select count(*) from pos_transaction_sync_server_tracking where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_transaction_sync_server_tracking where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_transaction_sync_server_tracking have $row_count rows.";
		
		$this->total['pos_transaction_sync_server_tracking']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_transaction_sync_server_tracking'] = "delete 
			from pos_transaction_sync_server_tracking
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_transaction_sync_server_tracking'][] = 'pos_transaction_sync_server_tracking';
		}
		
		// pos_transaction_sync_server_counter_tracking
		print "\nChecking pos_transaction_sync_server_counter_tracking";
		$sql_count = "select count(*) from pos_transaction_sync_server_counter_tracking where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_transaction_sync_server_counter_tracking where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_transaction_sync_server_counter_tracking have $row_count rows.";
		
		$this->total['pos_transaction_sync_server_counter_tracking']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_transaction_sync_server_counter_tracking'] = "delete 
			from pos_transaction_sync_server_counter_tracking
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_transaction_sync_server_counter_tracking'][] = 'pos_transaction_sync_server_counter_tracking';
		}
		
		// pos_transaction_ewallet_payment
		print "\nChecking pos_transaction_ewallet_payment";
		$sql_count = "select count(*) from pos_transaction_ewallet_payment where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from pos_transaction_ewallet_payment where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r pos_transaction_ewallet_payment have $row_count rows.";
		
		$this->total['pos_transaction_ewallet_payment']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['pos_transaction_ewallet_payment'] = "delete 
			from pos_transaction_ewallet_payment
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['pos_transaction_ewallet_payment'][] = 'pos_transaction_ewallet_payment';
		}
	}
	
	function archive_PO(){
		global $con;
		
		// po
		print "\nChecking po";
		$sql_count = "select count(*) from po where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." po_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from po where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." po_date<".ms($this->cutoff_date);
		print "\r po have $row_count rows.";
		
		$this->total['po']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
						
		// po_items
		print "\nChecking po_items";
		$sql_count = "select count(*) 
		from po_items pi
		join po on po.branch_id=pi.branch_id and po.id=pi.po_id
		where ".($this->bid?"po.branch_id=".mi($this->bid)." and ":"")." po.po_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select pi.* 
		from po_items pi
		join po on po.branch_id=pi.branch_id and po.id=pi.po_id
		where ".($this->bid?"po.branch_id=".mi($this->bid)." and ":"")." po.po_date<".ms($this->cutoff_date);
		print "\r po_items have $row_count rows.";
		
		$this->total['po_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		// po_currency_rate_history
		print "\nChecking po_currency_rate_history";
		$sql_count = "select count(*) 
		from po_currency_rate_history pcr
		join po on po.branch_id=pcr.branch_id and po.id=pcr.po_id
		where ".($this->bid?"po.branch_id=".mi($this->bid)." and ":"")." po.po_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		print "\r po_currency_rate_history have $row_count rows.";
		
		$this->total['po_currency_rate_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){
			$this->delete_query['po_items'] = "delete po,pi,pcr
			from po
			left join po_items pi on po.id=pi.po_id and po.branch_id=pi.branch_id
			left join po_currency_rate_history pcr on po.id=pcr.po_id and po.branch_id=pcr.branch_id
			where ".($this->bid?"po.branch_id=".mi($this->bid)." and ":"")." po.po_date<".ms($this->cutoff_date);
			$this->optimize_table_list['po_items'][] = 'po_items';
			$this->optimize_table_list['po_items'][] = 'po';
			$this->optimize_table_list['po_items'][] = 'po_currency_rate_history';
		}
		
		// po_request_items
		print "\nChecking po_request_items";
		$sql_count = "select count(*) from po_request_items where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from po_request_items where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
		print "\r po_request_items have $row_count rows.";
		
		$this->total['po_request_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['po_request_items'] = "delete 
			from po_request_items
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
			$this->optimize_table_list['po_request_items'][] = 'po_request_items';
		}
	}
	
	function archive_DO(){
		global $con;
		
		// do
		print "\nChecking do";
		$sql_count = "select count(*) from do where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." do_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from do where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." do_date<".ms($this->cutoff_date);
		print "\r do have $row_count rows.";
		
		$this->total['do']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
						
		// do_items
		print "\nChecking do_items";
		$sql_count = "select count(*)
		from do_items di
		join do on do.branch_id=di.branch_id and do.id=di.do_id
		where ".($this->bid?"do.branch_id=".mi($this->bid)." and ":"")." do.do_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select di.*
		from do_items di
		join do on do.branch_id=di.branch_id and do.id=di.do_id
		where ".($this->bid?"do.branch_id=".mi($this->bid)." and ":"")." do.do_date<".ms($this->cutoff_date);
		print "\r do_items have $row_count rows.";
		
		$this->total['do_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
						
		// do_open_items
		print "\nChecking do_open_items";
		$sql_count = "select count(*)
		from do_open_items di
		join do on do.branch_id=di.branch_id and do.id=di.do_id
		where ".($this->bid?"do.branch_id=".mi($this->bid)." and ":"")." do.do_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select di.*
		from do_open_items di
		join do on do.branch_id=di.branch_id and do.id=di.do_id
		where ".($this->bid?"do.branch_id=".mi($this->bid)." and ":"")." do.do_date<".ms($this->cutoff_date);
		print "\r do_open_items have $row_count rows.";
		
		$this->total['do_open_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		// do_barcode_items
		print "\nChecking do_barcode_items";
		$sql_count = "select count(*)
		from do_barcode_items di
		join do on do.branch_id=di.branch_id and do.id=di.do_id
		where ".($this->bid?"do.branch_id=".mi($this->bid)." and ":"")." do.do_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select di.*
		from do_barcode_items di
		join do on do.branch_id=di.branch_id and do.id=di.do_id
		where ".($this->bid?"do.branch_id=".mi($this->bid)." and ":"")." do.do_date<".ms($this->cutoff_date);
		print "\r do_barcode_items have $row_count rows.";
		
		$this->total['do_barcode_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['do'] = "delete do,di,doi,dbi
			from do
			left join do_items di on do.branch_id=di.branch_id and do.id=di.do_id
			left join do_open_items doi on do.branch_id=doi.branch_id and do.id=doi.do_id
			left join do_barcode_items dbi on do.branch_id=dbi.branch_id and do.id=dbi.do_id
			where ".($this->bid?"do.branch_id=".mi($this->bid)." and ":"")." do.do_date<".ms($this->cutoff_date);
			$this->optimize_table_list['do'][] = 'do';
			$this->optimize_table_list['do'][] = 'do_items';
			$this->optimize_table_list['do'][] = 'do_open_items';
			$this->optimize_table_list['do'][] = 'do_barcode_items';
		}
		
		// do_request_items
		print "\nChecking do_request_items";
		$sql_count = "select count(*) from do_request_items where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." last_update<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from do_request_items where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." last_update<".ms($this->cutoff_date);
		print "\r do_request_items have $row_count rows.";
		
		$this->total['do_request_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['do_request_items'] = "delete 
			from do_request_items
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." last_update<".ms($this->cutoff_date);
			$this->optimize_table_list['do_request_items'][] = 'do_request_items';
		}
	}
	
	function archive_ADJ(){
		global $con;
		
		// adjustment
		print "\nChecking adjustment";
		$sql_count = "select count(*) from adjustment where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." adjustment_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from adjustment where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." adjustment_date<".ms($this->cutoff_date);
		print "\r adjustment have $row_count rows.";
		
		$this->total['adjustment']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
				
		// adjustment_items
		print "\nChecking adjustment_items";
		$sql_count = "select count(*) 
		from adjustment_items adji
		left join adjustment adj on adj.branch_id=adji.branch_id and adj.id=adji.adjustment_id
		where ".($this->bid?"adj.branch_id=".mi($this->bid)." and ":"")." adj.adjustment_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select adji.* 
		from adjustment_items adji
		left join adjustment adj on adj.branch_id=adji.branch_id and adj.id=adji.adjustment_id
		where ".($this->bid?"adj.branch_id=".mi($this->bid)." and ":"")." adj.adjustment_date<".ms($this->cutoff_date);
		print "\r adjustment_items have $row_count rows.";
		
		$this->total['adjustment_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){						
			$this->delete_query['adjustment_items'] = "delete adj,adji
			from adjustment adj
			left join adjustment_items adji on adj.branch_id=adji.branch_id and adj.id=adji.adjustment_id
			where ".($this->bid?"adj.branch_id=".mi($this->bid)." and ":"")." adj.adjustment_date<".ms($this->cutoff_date);
			$this->optimize_table_list['adjustment_items'][] = 'adjustment_items';
			$this->optimize_table_list['adjustment_items'][] = 'adjustment';
		}
		
		// work_order
		print "\nChecking work_order";
		$sql_count = "select count(*) from work_order where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." adj_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		print "\r work_order have $row_count rows.";
		
		$this->total['work_order']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		// work_order_items_in
		print "\nChecking work_order_items_in";
		$sql_count = "select count(*) 
		from work_order_items_in woi
		left join work_order wo on wo.branch_id=woi.branch_id and wo.id=woi.work_order_id
		where ".($this->bid?"wo.branch_id=".mi($this->bid)." and ":"")." wo.adj_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		print "\r work_order_items_in have $row_count rows.";
		
		$this->total['work_order_items_in']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		// work_order_items_out
		print "\nChecking work_order_items_out";
		$sql_count = "select count(*) 
		from work_order_items_out woi
		left join work_order wo on wo.branch_id=woi.branch_id and wo.id=woi.work_order_id
		where ".($this->bid?"wo.branch_id=".mi($this->bid)." and ":"")." wo.adj_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		print "\r work_order_items_out have $row_count rows.";
		
		$this->total['work_order_items_out']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
	}
	
	function archive_GRN(){
		global $con;
		
		// grr
		print "\nChecking grr";
		$sql_count = "select count(*) from grr where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." grr.rcv_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select grr.* from grr where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." grr.rcv_date<".ms($this->cutoff_date);
		print "\r grr have $row_count rows.";
		
		$this->total['grr']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
						
		// grr_items
		print "\nChecking grr_items";
		$sql_count = "select count(*) 
		from grr_items gi
		left join grr on grr.id=gi.grr_id and grr.branch_id=gi.branch_id
		where ".($this->bid?"grr.branch_id=".mi($this->bid)." and ":"")." grr.rcv_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select gi.* 
		from grr_items gi
		left join grr on grr.id=gi.grr_id and grr.branch_id=gi.branch_id
		where ".($this->bid?"grr.branch_id=".mi($this->bid)." and ":"")." grr.rcv_date<".ms($this->cutoff_date);
		print "\r grr_items have $row_count rows.";
		
		$this->total['grr_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		// grn
		print "\nChecking grn";
		$sql_count = "select count(* )
		from grn
		left join grr on grr.id=grn.grr_id and grr.branch_id=grn.branch_id
		where ".($this->bid?"grr.branch_id=".mi($this->bid)." and ":"")." grr.rcv_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select grn.* 
		from grn
		left join grr on grr.id=grn.grr_id and grr.branch_id=grn.branch_id
		where ".($this->bid?"grr.branch_id=".mi($this->bid)." and ":"")." grr.rcv_date<".ms($this->cutoff_date);
		print "\r grn have $row_count rows.";
		
		$this->total['grn']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		// grn_items
		print "\nChecking grn_items";
		$sql_count = "select count(*)
			from grn_items gri
			left join grn on grn.id=gri.grn_id and grn.branch_id=gri.branch_id
			left join grr on grr.id=grn.grr_id and grr.branch_id=grn.branch_id
			where ".($this->bid?"grr.branch_id=".mi($this->bid)." and ":"")." grr.rcv_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select gri.* 
			from grn_items gri
			left join grn on grn.id=gri.grn_id and grn.branch_id=gri.branch_id
			left join grr on grr.id=grn.grr_id and grr.branch_id=grn.branch_id
			where ".($this->bid?"grr.branch_id=".mi($this->bid)." and ":"")." grr.rcv_date<".ms($this->cutoff_date);
		print "\r grn_items have $row_count rows.";
		
		$this->total['grn_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['grn'] = "delete grr,gi,grn,gri
			from grr 
			left join grr_items gi on grr.id=gi.grr_id and grr.branch_id=gi.branch_id
			left join grn on grr.id=grn.grr_id and grr.branch_id=grn.branch_id
			left join grn_items gri on grn.id=gri.grn_id and grn.branch_id=gri.branch_id
			where ".($this->bid?"grr.branch_id=".mi($this->bid)." and ":"")." grr.rcv_date<".ms($this->cutoff_date);
			$this->optimize_table_list['grn'][] = 'grn';
			$this->optimize_table_list['grn'][] = 'grr';
			$this->optimize_table_list['grn'][] = 'grr_items';
			$this->optimize_table_list['grn'][] = 'grn_items';
		}
	}
	
	function archive_GRA(){
		global $con;
		
		// gra
		print "\nChecking gra";
		$sql_count = "select count(*) from gra where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." gra.return_timestamp<".ms($this->cutoff_date)." and gra.return_timestamp>0";
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select gra.* from gra where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." gra.return_timestamp<".ms($this->cutoff_date)." and gra.return_timestamp>0";
		print "\r gra have $row_count rows.";
		
		$this->total['gra']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
						
		// gra_items
		print "\nChecking gra_items";
		$sql_count = "select count(*) 
			from gra_items gi
			left join gra on gra.id=gi.gra_id and gra.branch_id=gi.branch_id
			where ".($this->bid?"gra.branch_id=".mi($this->bid)." and ":"")." gra.return_timestamp<".ms($this->cutoff_date)." and gra.return_timestamp>0";
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select gi.* 
			from gra_items gi
			left join gra on gra.id=gi.gra_id and gra.branch_id=gi.branch_id
			where ".($this->bid?"gra.branch_id=".mi($this->bid)." and ":"")." gra.return_timestamp<".ms($this->cutoff_date)." and gra.return_timestamp>0";
		print "\r gra_items have $row_count rows.";
		
		$this->total['gra_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['gra_items'] = "delete gra,gi
			from gra
			left join gra_items gi on gra.id=gi.gra_id and gra.branch_id=gi.branch_id
			where ".($this->bid?"gra.branch_id=".mi($this->bid)." and ":"")." gra.return_timestamp<".ms($this->cutoff_date)." and gra.return_timestamp>0";
			$this->optimize_table_list['gra_items'][] = 'gra_items';
			$this->optimize_table_list['gra_items'][] = 'gra';
		}
	}
	
	function archive_SALES_ORDER(){
		global $con;
		
		// sales_order
		print "\nChecking sales_order";
		$sql_count = "select count(*) from sales_order so where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." so.order_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select so.* from sales_order so where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." so.order_date<".ms($this->cutoff_date);
		print "\r sales_order have $row_count rows.";
		
		$this->total['sales_order']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		// sales_order_items
		print "\nChecking sales_order_items";
		$sql_count = "select count(*) 
		from sales_order_items soi
		left join sales_order so on so.id=soi.sales_order_id and so.branch_id=soi.branch_id
		where ".($this->bid?"so.branch_id=".mi($this->bid)." and ":"")." so.order_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select soi.* 
		from sales_order_items soi
		left join sales_order so on so.id=soi.sales_order_id and so.branch_id=soi.branch_id
		where ".($this->bid?"so.branch_id=".mi($this->bid)." and ":"")." so.order_date<".ms($this->cutoff_date);
		print "\r sales_order_items have $row_count rows.";
		
		$this->total['sales_order_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){						
			$this->delete_query['sales_order_items'] = "delete so,soi
			from sales_order so
			left join sales_order_items soi on so.id=soi.sales_order_id and so.branch_id=soi.branch_id
			where ".($this->bid?"so.branch_id=".mi($this->bid)." and ":"")." so.order_date<".ms($this->cutoff_date);
			$this->optimize_table_list['sales_order_items'][] = 'sales_order_items';
			$this->optimize_table_list['sales_order_items'][] = 'sales_order';
		}
	}
	
	function archive_CI_CN_DN(){
		global $con;
		
		// ci
		print "\nChecking ci";
		$sql_count = "select count(*) from ci where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ci.ci_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select ci.* from ci where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ci.ci_date<".ms($this->cutoff_date);
		print "\r ci have $row_count rows.";
		
		$this->total['ci']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		// ci_items
		print "\nChecking ci_items";
		$sql_count = "select count(*) 
		from ci_items cii
		left join ci on ci.id=cii.ci_id and ci.branch_id=cii.branch_id
		where ".($this->bid?"ci.branch_id=".mi($this->bid)." and ":"")." ci.ci_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select cii.* 
		from ci_items cii
		left join ci on ci.id=cii.ci_id and ci.branch_id=cii.branch_id
		where ".($this->bid?"ci.branch_id=".mi($this->bid)." and ":"")." ci.ci_date<".ms($this->cutoff_date);
		print "\r ci_items have $row_count rows.";
		
		$this->total['ci_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){						
			$this->delete_query['ci_items'] = "delete ci,cii
			from ci
			left join ci_items cii on ci.id=cii.ci_id and ci.branch_id=cii.branch_id
			where ".($this->bid?"ci.branch_id=".mi($this->bid)." and ":"")." ci.ci_date<".ms($this->cutoff_date);
			$this->optimize_table_list['ci_items'][] = 'ci_items';
			$this->optimize_table_list['ci_items'][] = 'ci';
		}
				
		// cn
		print "\nChecking cn";
		$sql_count = "select count(*) from cn where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." cn.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select cn.* from cn where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." cn.date<".ms($this->cutoff_date);
		print "\r cn have $row_count rows.";
		
		$this->total['cn']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		// cn_items
		print "\nChecking cn_items";
		$sql_count = "select count(*) 
		from cn_items cni
		left join cn on cn.id=cni.cn_id and cn.branch_id=cni.branch_id
		where ".($this->bid?"cn.branch_id=".mi($this->bid)." and ":"")." cn.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select cni.* 
		from cn_items cni
		left join cn on cn.id=cni.cn_id and cn.branch_id=cni.branch_id
		where ".($this->bid?"cn.branch_id=".mi($this->bid)." and ":"")." cn.date<".ms($this->cutoff_date);
		print "\r cn_items have $row_count rows.";
		
		$this->total['cn_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['cn_items'] = "delete cn,cni
			from cn
			left join cn_items cni on cn.id=cni.cn_id and cn.branch_id=cni.branch_id
			where ".($this->bid?"cn.branch_id=".mi($this->bid)." and ":"")." cn.date<".ms($this->cutoff_date);
			$this->optimize_table_list['cn_items'][] = 'cn_items';
			$this->optimize_table_list['cn_items'][] = 'cn';
		}
				
		// dn
		print "\nChecking dn";
		$sql_count = "select count(*) from dn where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." dn.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select dn.* from dn where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." dn.date<".ms($this->cutoff_date);
		print "\r dn have $row_count rows.";
		
		$this->total['dn']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		// dn_items
		print "\nChecking dn_items";
		$sql_count = "select count(*) 
		from dn_items dni
		left join dn on dn.id=dni.dn_id and dn.branch_id=dni.branch_id
		where ".($this->bid?"dn.branch_id=".mi($this->bid)." and ":"")." dn.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select dni.* 
		from dn_items dni
		left join dn on dn.id=dni.dn_id and dn.branch_id=dni.branch_id
		where ".($this->bid?"dn.branch_id=".mi($this->bid)." and ":"")." dn.date<".ms($this->cutoff_date);
		print "\r dn_items have $row_count rows.";
		
		$this->total['dn_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['dn_items'] = "delete dn,dni
			from dn
			left join dn_items dni on dn.id=dni.dn_id and dn.branch_id=dni.branch_id
			where ".($this->bid?"dn.branch_id=".mi($this->bid)." and ":"")." dn.date<".ms($this->cutoff_date);
			$this->optimize_table_list['dn_items'][] = 'dn_items';
			$this->optimize_table_list['dn_items'][] = 'dn';
		}
	}
	
	function archive_CON_MONTHLY_REPORT(){
		global $con;
		
		// consignment_report
		print "\nChecking consignment_report";
		$sql_count = "select count(*) from consignment_report cr where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((cr.year<$this->cutoff_y) or (cr.year=$this->cutoff_y and cr.month<=$this->cutoff_m))";
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select cr.* from consignment_report cr where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((cr.year<$this->cutoff_y) or (cr.year=$this->cutoff_y and cr.month<=$this->cutoff_m))";
		print "\r consignment_report have $row_count rows.";
		
		$this->total['consignment_report']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['consignment_report'] = "delete 
			from consignment_report
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((year<$this->cutoff_y) or (year=$this->cutoff_y and month<=$this->cutoff_m))";
			$this->optimize_table_list['consignment_report'][] = 'consignment_report';
		}
		
		// consignment_report_page_info
		print "\nChecking consignment_report_page_info";
		$sql_count = "select count(*) from consignment_report_page_info crpi 
		where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((crpi.year<$this->cutoff_y) or (crpi.year=$this->cutoff_y and crpi.month<=$this->cutoff_m))";
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select crpi.* from consignment_report_page_info crpi 
		where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((crpi.year<$this->cutoff_y) or (crpi.year=$this->cutoff_y and crpi.month<=$this->cutoff_m))";
		print "\r consignment_report_page_info have $row_count rows.";
		
		$this->total['consignment_report_page_info']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['consignment_report_page_info'] = "delete 
			from consignment_report_page_info
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((year<$this->cutoff_y) or (year=$this->cutoff_y and month<=$this->cutoff_m))";
			$this->optimize_table_list['consignment_report_page_info'][] = 'consignment_report_page_info';
		}
				
		// consignment_report_sku
		print "\nChecking consignment_report_sku";
		$sql_count = "select count(*) from consignment_report_sku crs 
		where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((crs.year<$this->cutoff_y) or (crs.year=$this->cutoff_y and crs.month<=$this->cutoff_m))";
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select crs.* from consignment_report_sku crs 
		where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((crs.year<$this->cutoff_y) or (crs.year=$this->cutoff_y and crs.month<=$this->cutoff_m))";
		print "\r consignment_report_sku have $row_count rows.";
		
		$this->total['consignment_report_sku']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){						
			$this->delete_query['consignment_report_sku'] = "delete 
			from consignment_report_sku
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((year<$this->cutoff_y) or (year=$this->cutoff_y and month<=$this->cutoff_m))";
			$this->optimize_table_list['consignment_report_sku'][] = 'consignment_report_sku';
		}
				
		// monthly_report_list
		print "\nChecking monthly_report_list";
		$sql_count = "select count(*) from monthly_report_list m 
		where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((m.year<$this->cutoff_y) or (m.year=$this->cutoff_y and m.month<=$this->cutoff_m))";
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select m.* from monthly_report_list m 
		where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((m.year<$this->cutoff_y) or (m.year=$this->cutoff_y and m.month<=$this->cutoff_m))";
		print "\r monthly_report_list have $row_count rows.";
		
		$this->total['monthly_report_list']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){						
			$this->delete_query['monthly_report_list'] = "delete 
			from monthly_report_list
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((year<$this->cutoff_y) or (year=$this->cutoff_y and month<=$this->cutoff_m))";
			$this->optimize_table_list['monthly_report_list'][] = 'monthly_report_list';
		}
	}
	
	function archive_cnote_dnote(){
		global $con;
		
		// cnote
		print "\nChecking cnote";
		$sql_count = "select count(*) from cnote where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." cn_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from cnote where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." cn_date<".ms($this->cutoff_date);
		print "\r cnote have $row_count rows.";
		
		$this->total['cnote']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		//  cnote_items
		print "\nChecking  cnote_items";
		$sql_count = "select count(*) 
		from  cnote_items cni
		left join cnote cn on cn.id=cni.cnote_id and cn.branch_id=cni.branch_id
		where ".($this->bid?"cn.branch_id=".mi($this->bid)." and ":"")." cn.cn_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select cni.* 
		from  cnote_items cni
		left join cnote cn on cn.id=cni.cnote_id and cn.branch_id=cni.branch_id
		where ".($this->bid?"cn.branch_id=".mi($this->bid)." and ":"")." cn.cn_date<".ms($this->cutoff_date);
		print "\r cnote_items have $row_count rows.";
		
		$this->total['cnote_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['cnote_items'] = "delete cn,cni
			from cnote cn
			left join cnote_items cni on cn.id=cni.cnote_id and cn.branch_id=cni.branch_id
			where ".($this->bid?"cn.branch_id=".mi($this->bid)." and ":"")." cn.cn_date<".ms($this->cutoff_date);
			$this->optimize_table_list['cnote_items'][] = 'cnote_items';
			$this->optimize_table_list['cnote_items'][] = 'cnote';
		}
		
		// dnote
		print "\nChecking dnote";
		$sql_count = "select count(*) from dnote where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." dn_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from dnote where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." dn_date<".ms($this->cutoff_date);
		print "\r dnote have $row_count rows.";
		
		$this->total['dnote']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		// dnote_items
		print "\nChecking dnote_items";
		$sql_count = "select count(*) 
		from dnote_items dni
		left join dnote dn on dn.id=dni.dnote_id and dn.branch_id=dni.branch_id
		where ".($this->bid?"dn.branch_id=".mi($this->bid)." and ":"")." dn.dn_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select dni.*
		from dnote_items dni
		left join dnote dn on dn.id=dni.dnote_id and dn.branch_id=dni.branch_id
		where ".($this->bid?"dn.branch_id=".mi($this->bid)." and ":"")." dn.dn_date<".ms($this->cutoff_date);
		print "\r dnote_items have $row_count rows.";
		
		$this->total['dnote_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['dnote_items'] = "delete dn,dni
			from dnote dn
			left join dnote_items dni on dn.id=dni.dnote_id and dn.branch_id=dni.branch_id
			where ".($this->bid?"dn.branch_id=".mi($this->bid)." and ":"")." dn.dn_date<".ms($this->cutoff_date);
			$this->optimize_table_list['dnote_items'][] = 'dnote_items';
			$this->optimize_table_list['dnote_items'][] = 'dnote';
		}
	}
	
	function archive_LOG(){
		global $con;
		
		// log
		print "\nChecking log";
		$sql_count = "select count(*) from log where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." log.timestamp<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select log.* from log where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." log.timestamp<".ms($this->cutoff_date);
		print "\r log have $row_count rows.";
		
		$this->total['log']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['log'] = "delete 
			from log
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
			$this->optimize_table_list['log'][] = 'log';
		}
		
		// log_dp
		print "\nChecking log_dp";
		$sql_count = "select count(*) from log_dp where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from log_dp where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
		print "\r log_dp have $row_count rows.";
		
		$this->total['log_dp']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['log_dp'] = "delete 
			from log_dp
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
			$this->optimize_table_list['log_dp'][] = 'log_dp';
		}
		
		// log_vp
		print "\nChecking log_vp";
		$sql_count = "select count(*) from log_vp where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from log_vp where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
		print "\r log_vp have $row_count rows.";
		
		$this->total['log_vp']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['log_vp'] = "delete 
			from log_vp
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
			$this->optimize_table_list['log_vp'][] = 'log_vp';
		}
	}
	
	function archive_LOGIN_TICKETS(){
		global $con;
		
		// login_tickets
		print "\nChecking login_tickets";
		$sql_count = "select count(*) from login_tickets lt where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." lt.last_update<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select lt.* from login_tickets lt where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." lt.last_update<".ms($this->cutoff_date);
		print "\r login_tickets have $row_count rows.";
		
		$this->total['login_tickets']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){						
			$this->delete_query['login_tickets'] = "delete 
			from login_tickets
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." last_update<".ms($this->cutoff_date);
			$this->optimize_table_list['login_tickets'][] = 'login_tickets';
		}
	}
	
	function archive_PICKING_LIST(){
		global $con;
		
		// picking_list
		print "\nChecking picking_list";
		$sql_count = "select count(*) from picking_list pl where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pl.last_update<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select pl.* from picking_list pl where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pl.last_update<".ms($this->cutoff_date);
		print "\r picking_list have $row_count rows.";
		
		$this->total['picking_list']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['picking_list'] = "delete 
			from picking_list
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." last_update<".ms($this->cutoff_date);
			$this->optimize_table_list['picking_list'][] = 'picking_list';
		}
	}
	
	function archive_PM(){
		global $con;
		
		// pm
		print "\nChecking pm";
		$sql_count = "select count(*) from pm where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pm.timestamp<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select pm.* from pm where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." pm.timestamp<".ms($this->cutoff_date);
		print "\r pm have $row_count rows.";
		
		$this->total['pm']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['pm'] = "delete 
			from pm
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." timestamp<".ms($this->cutoff_date);
			$this->optimize_table_list['pm'][] = 'pm';
		}
	}
	
	function archive_PROMOTION(){
		global $con;
		
		// promotion
		print "\nChecking promotion";
		$sql_count = "select count(*) from promotion p where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." p.date_to<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select p.* from promotion p where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." p.date_to<".ms($this->cutoff_date);
		print "\r promotion have $row_count rows.";
		
		$this->total['promotion']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
						
		// promotion_items
		print "\nChecking promotion_items";
		$sql_count = "select count(*) 
			from promotion_items pi
			left join promotion p on p.id=pi.promo_id and p.branch_id=pi.branch_id
			where ".($this->bid?"p.branch_id=".mi($this->bid)." and ":"")." p.date_to<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select pi.* 
			from promotion_items pi
			left join promotion p on p.id=pi.promo_id and p.branch_id=pi.branch_id
			where ".($this->bid?"p.branch_id=".mi($this->bid)." and ":"")." p.date_to<".ms($this->cutoff_date);
		print "\r promotion_items have $row_count rows.";
		
		$this->total['promotion_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		// promotion_mix_n_match_items
		print "\nChecking promotion_mix_n_match_items";
		$sql_count = "select count(*) 
			from promotion_mix_n_match_items pi
			left join promotion p on p.id=pi.promo_id and p.branch_id=pi.branch_id
			where ".($this->bid?"p.branch_id=".mi($this->bid)." and ":"")." p.date_to<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select pi.* 
			from promotion_mix_n_match_items pi
			left join promotion p on p.id=pi.promo_id and p.branch_id=pi.branch_id
			where ".($this->bid?"p.branch_id=".mi($this->bid)." and ":"")." p.date_to<".ms($this->cutoff_date);
		print "\r promotion_mix_n_match_items have $row_count rows.";
		
		$this->total['promotion_mix_n_match_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['promotion_mix_n_match_items'] = "delete p,pi,pmi
			from promotion p
			left join promotion_items pi on p.id=pi.promo_id and p.branch_id=pi.branch_id
			left join promotion_mix_n_match_items pmi on p.id=pmi.promo_id and p.branch_id=pmi.branch_id
			where ".($this->bid?"p.branch_id=".mi($this->bid)." and ":"")." p.date_to<".ms($this->cutoff_date);
			$this->optimize_table_list['promotion_mix_n_match_items'][] = 'promotion_mix_n_match_items';
			$this->optimize_table_list['promotion_mix_n_match_items'][] = 'promotion_items';
			$this->optimize_table_list['promotion_mix_n_match_items'][] = 'promotion';
		}
	}
	
	function archive_STOCK_TAKE(){
		global $con;
		
		// stock_check
		print "\nChecking stock_check";
		$sql_count = "select count(*) from stock_check sc where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." sc.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select sc.* from stock_check sc where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." sc.date<".ms($this->cutoff_date);
		print "\r stock_check have $row_count rows.";
		
		$this->total['stock_check']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['stock_check'] = "delete 
			from stock_check
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." location<>".ms($this->source_label)." and date<".ms($this->cutoff_date);
			$this->optimize_table_list['stock_check'][] = 'stock_check';
		}
		
		// stock_take_pre
		print "\nChecking stock_take_pre";
		$sql_count = "select count(*) from stock_take_pre stp where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." stp.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select stp.* from stock_take_pre stp where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." stp.date<".ms($this->cutoff_date);
		print "\r stock_take_pre have $row_count rows.";
		
		$this->total['stock_take_pre']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){						
			$this->delete_query['stock_take_pre'] = "delete 
			from stock_take_pre
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['stock_take_pre'][] = 'stock_take_pre';
		}
		
		// cycle_count
		print "\nChecking cycle_count";
		$sql_count = "select count(*) from cycle_count where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." st_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		print "\r cycle_count have $row_count rows.";
		
		$this->total['cycle_count']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
						
		// cycle_count_items
		print "\nChecking cycle_count_items";
		$sql_count = "select count(*) 
		from cycle_count_items cci
		join cycle_count cc on cc.branch_id=cci.branch_id and cc.id=cci.cc_id
		where ".($this->bid?"cc.branch_id=".mi($this->bid)." and ":"")." cc.st_date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		print "\r cycle_count_items have $row_count rows.";
		
		$this->total['cycle_count_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
				
		if($this->is_cutoff){
			$this->delete_query['cycle_count_items'] = "delete cc,cci
			from cycle_count cc
			left join cycle_count_items cci on cc.branch_id=cci.branch_id and cc.id=cci.cc_id
			where ".($this->bid?"cc.branch_id=".mi($this->bid)." and ":"")." cc.st_date<".ms($this->cutoff_date);
			$this->optimize_table_list['cycle_count'][] = 'cycle_count';
			$this->optimize_table_list['cycle_count_items'][] = 'cycle_count_items';
		}
	}
	
	function archive_CACHE(){
		global $con;
		
		print "\nChecking CACHE...";
				
		// SKU_ITEMS_SALES_CACHE
		print "\nChecking sku_items_sales_cache_...";
		$tbl_like = 'sku_items_sales_cache_%';
		if($this->bid)	$tbl_like = 'sku_items_sales_cache_b'.$this->bid;
		$q_like = $con->sql_query("show tables like ".ms($tbl_like));
		
		while($r = $con->sql_fetchrow($q_like)){
			$tbl = trim($r[0]);
			if(strpos($tbl, 'sku_items_sales_cache_')!==false){
				$sql_count = "select count(*) from $tbl sc where date<".ms($this->cutoff_date);
				$q1 = $con->sql_query($sql_count);
				$row_count = mi($con->sql_fetchfield(0));
				$con->sql_freeresult($q1);
				
				$sql = "select sc.* from $tbl sc where date<".ms($this->cutoff_date);
				print "\n $tbl have $row_count rows.";
				
				$this->total['sku_items_sales_cache']['total'] += $row_count;
				$this->total['total']['total'] += $row_count;
				
				if($this->is_cutoff){
					$this->delete_query[$tbl] = "delete 
					from $tbl
					where date<".ms($this->cutoff_date);
					$this->optimize_table_list[$tbl][] = $tbl;
				}
			}
		}
		$con->sql_freeresult($q_like);
				
		// CATEGORY_SALES_CACHE
		print "\nChecking category_sales_cache_...";
		$tbl_like = 'category_sales_cache_%';
		if($this->bid)	$tbl_like = 'category_sales_cache_b'.$this->bid;
		$q_like = $con->sql_query("show tables like ".ms($tbl_like));
		
		while($r = $con->sql_fetchrow($q_like)){
			$tbl = trim($r[0]);
			if(strpos($tbl, 'category_sales_cache_')!==false){
				$sql_count = "select count(*) from $tbl sc where date<".ms($this->cutoff_date);
				$q1 = $con->sql_query($sql_count);
				$row_count = mi($con->sql_fetchfield(0));
				$con->sql_freeresult($q1);
				
				$sql = "select sc.* from $tbl sc where date<".ms($this->cutoff_date);
				print "\n $tbl have $row_count rows.";
				
				$this->total['category_sales_cache']['total'] += $row_count;
				$this->total['total']['total'] += $row_count;
				
				if($this->is_cutoff){
					$this->delete_query[$tbl] = "delete 
					from $tbl
					where date<".ms($this->cutoff_date);
					$this->optimize_table_list[$tbl][] = $tbl;
				}
			}
		}
		$con->sql_freeresult($q_like);
		
		// DEPT_TRANS_CACHE
		print "\nChecking dept_trans_cache_...";
		$tbl_like = 'dept_trans_cache_%';
		if($this->bid)	$tbl_like = 'dept_trans_cache_b'.$this->bid;
		$q_like = $con->sql_query("show tables like ".ms($tbl_like));
		
		while($r = $con->sql_fetchrow($q_like)){
			$tbl = trim($r[0]);
			if(strpos($tbl, 'dept_trans_cache_')!==false){
				$sql_count = "select count(*) from $tbl sc where date<".ms($this->cutoff_date);
				$q1 = $con->sql_query($sql_count);
				$row_count = mi($con->sql_fetchfield(0));
				$con->sql_freeresult($q1);
				
				$sql = "select sc.* from $tbl sc where date<".ms($this->cutoff_date);
				print "\n $tbl have $row_count rows.";
				
				$this->total['dept_trans_cache']['total'] += $row_count;
				$this->total['total']['total'] += $row_count;
				
				if($this->is_cutoff){
					$this->delete_query[$tbl] = "delete 
					from $tbl
					where date<".ms($this->cutoff_date);
					$this->optimize_table_list[$tbl][] = $tbl;
				}
			}
		}
		$con->sql_freeresult($q_like);
				
		// MEMBER_SALES_CACHE
		print "\nChecking member_sales_cache_...";
		$tbl_like = 'member_sales_cache_%';
		if($this->bid)	$tbl_like = 'member_sales_cache_b'.$this->bid;
		$q_like = $con->sql_query("show tables like ".ms($tbl_like));
		
		while($r = $con->sql_fetchrow($q_like)){
			$tbl = trim($r[0]);
			if(strpos($tbl, 'member_sales_cache_')!==false){
				$sql_count = "select count(*) from $tbl sc where date<".ms($this->cutoff_date);
				$q1 = $con->sql_query($sql_count);
				$row_count = mi($con->sql_fetchfield(0));
				$con->sql_freeresult($q1);
				
				$sql = "select sc.* from $tbl sc where date<".ms($this->cutoff_date);
				print "\n $tbl have $row_count rows.";
				
				$this->total['member_sales_cache']['total'] += $row_count;
				$this->total['total']['total'] += $row_count;
				
				if($this->is_cutoff){
					$this->delete_query[$tbl] = "delete 
					from $tbl
					where date<".ms($this->cutoff_date);
					$this->optimize_table_list[$tbl][] = $tbl;
				}
			}
		}
		$con->sql_freeresult($q_like);
		
		// PWP_SALES_CACHE
		print "\nChecking pwp_sales_cache_...";
		$tbl_like = 'pwp_sales_cache_%';
		if($this->bid)	$tbl_like = 'pwp_sales_cache_b'.$this->bid;
		$q_like = $con->sql_query("show tables like ".ms($tbl_like));
		
		while($r = $con->sql_fetchrow($q_like)){
			$tbl = trim($r[0]);
			if(strpos($tbl, 'pwp_sales_cache_')!==false){
				$sql_count = "select count(*) from $tbl sc where date<".ms($this->cutoff_date);
				$q1 = $con->sql_query($sql_count);
				$row_count = mi($con->sql_fetchfield(0));
				$con->sql_freeresult($q1);
				
				$sql = "select sc.* from $tbl sc where date<".ms($this->cutoff_date);
				print "\n $tbl have $row_count rows.";
				
				$this->total['pwp_sales_cache']['total'] += $row_count;
				$this->total['total']['total'] += $row_count;
				
				if($this->is_cutoff){
					$this->delete_query[$tbl] = "delete 
					from $tbl
					where date<".ms($this->cutoff_date);
					$this->optimize_table_list[$tbl][] = $tbl;
				}
			}
		}
		$con->sql_freeresult($q_like);
		
		// daily_sales_cache
		print "\nChecking daily_sales_cache_...";
		$tbl_like = 'daily_sales_cache_%';
		if($this->bid)	$tbl_like = 'daily_sales_cache_b'.$this->bid;
		$q_like = $con->sql_query("show tables like ".ms($tbl_like));
		
		while($r = $con->sql_fetchrow($q_like)){
			$tbl = trim($r[0]);
			if(strpos($tbl, 'daily_sales_cache_')!==false){
				$sql_count = "select count(*) from $tbl where date<".ms($this->cutoff_date);
				$q1 = $con->sql_query($sql_count);
				$row_count = mi($con->sql_fetchfield(0));
				$con->sql_freeresult($q1);
				
				$sql = "select * from $tbl where date<".ms($this->cutoff_date);
				print "\n $tbl have $row_count rows.";
				
				$this->total['daily_sales_cache']['total'] += $row_count;
				$this->total['total']['total'] += $row_count;
				
				if($this->is_cutoff){
					$this->delete_query[$tbl] = "delete 
					from $tbl
					where date<".ms($this->cutoff_date);
					$this->optimize_table_list[$tbl][] = $tbl;
				}
			}
		}
		$con->sql_freeresult($q_like);
		
		// service_charge_cache
		print "\nChecking service_charge_cache_...";
		$tbl_like = 'service_charge_cache_%';
		if($this->bid)	$tbl_like = 'service_charge_cache_b'.$this->bid;
		$q_like = $con->sql_query("show tables like ".ms($tbl_like));
		
		while($r = $con->sql_fetchrow($q_like)){
			$tbl = trim($r[0]);
			if(strpos($tbl, 'service_charge_cache_')!==false){
				$sql_count = "select count(*) from $tbl where date<".ms($this->cutoff_date);
				$q1 = $con->sql_query($sql_count);
				$row_count = mi($con->sql_fetchfield(0));
				$con->sql_freeresult($q1);
				
				$sql = "select * from $tbl where date<".ms($this->cutoff_date);
				print "\n $tbl have $row_count rows.";
				
				$this->total['service_charge_cache']['total'] += $row_count;
				$this->total['total']['total'] += $row_count;
				
				if($this->is_cutoff){
					$this->delete_query[$tbl] = "delete 
					from $tbl
					where date<".ms($this->cutoff_date);
					$this->optimize_table_list[$tbl][] = $tbl;
				}
			}
		}
		$con->sql_freeresult($q_like);
		
		// return_policy_sales_cache
		print "\nChecking return_policy_sales_cache";
		$sql_count = "select count(*) from return_policy_sales_cache where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from return_policy_sales_cache where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r return_policy_sales_cache have $row_count rows.";
		
		$this->total['return_policy_sales_cache']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['return_policy_sales_cache'] = "delete 
			from return_policy_sales_cache
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['return_policy_sales_cache'][] = 'return_policy_sales_cache';
		}
		
		// sa_sales_cache
		print "\nChecking sa_sales_cache_...";
		$tbl_like = 'sa_sales_cache_%';
		if($this->bid)	$tbl_like = 'sa_sales_cache_b'.$this->bid;
		$q_like = $con->sql_query("show tables like ".ms($tbl_like));
		
		while($r = $con->sql_fetchrow($q_like)){
			$tbl = trim($r[0]);
			if(strpos($tbl, 'sa_sales_cache_')!==false){
				if($tbl == 'sa_sales_cache_monitoring')	continue;
				
				$sql_count = "select count(*) from $tbl where ((year<$this->cutoff_y) or (year=$this->cutoff_y and month<=$this->cutoff_m))";
				$q1 = $con->sql_query($sql_count);
				$row_count = mi($con->sql_fetchfield(0));
				$con->sql_freeresult($q1);
				
				$sql = "select * from $tbl where ((year<$this->cutoff_y) or (year=$this->cutoff_y and month<=$this->cutoff_m))";
				print "\n$tbl have $row_count rows.";
				
				$this->total['sa_sales_cache']['total'] += $row_count;
				$this->total['total']['total'] += $row_count;
				
				if($this->is_cutoff){
					$this->delete_query[$tbl] = "delete 
					from $tbl
					where ((year<$this->cutoff_y) or (year=$this->cutoff_y and month<=$this->cutoff_m))";
					$this->optimize_table_list[$tbl][] = $tbl;
				}
			}
		}
		$con->sql_freeresult($q_like);
	}
	
	function archive_FOREIGN_CURRENCY(){
		global $con;
		
		if(!($this->bid == 1 || !$this->bid)){
			return;
		}
		
		// foreign_currency_rate_history 
		print "\nChecking foreign_currency_rate_history ";
		$sql_count = "select count(*) from foreign_currency_rate_history fcr where fcr.date_to<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		print "\r foreign_currency_rate_history have $row_count rows.";
		
		$this->total['foreign_currency_rate_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['foreign_currency_rate_history'] = "delete 
			from foreign_currency_rate_history
			where date_to<".ms($this->cutoff_date);
			$this->optimize_table_list['foreign_currency_rate_history'][] = 'foreign_currency_rate_history';
		}
		
		// foreign_currency_rate_history_record  
		print "\nChecking foreign_currency_rate_history_record  ";
		$sql_count = "select count(*) from foreign_currency_rate_history_record fcr where fcr.date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		print "\r foreign_currency_rate_history_record have $row_count rows.";
		
		$this->total['foreign_currency_rate_history_record ']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['foreign_currency_rate_history_record'] = "delete 
			from foreign_currency_rate_history_record
			where date<".ms($this->cutoff_date);
			$this->optimize_table_list['foreign_currency_rate_history_record'][] = 'foreign_currency_rate_history_record';
		}
	}
	
	function archive_OTHERS(){
		global $con;
		
		// shift_record
		print "\nChecking shift_record";
		$sql_count = "select count(*) from shift_record sf where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((sf.year<$this->cutoff_y) or (sf.year=$this->cutoff_y and sf.month<=$this->cutoff_m))";
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select sf.* from shift_record sf where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((sf.year<$this->cutoff_y) or (sf.year=$this->cutoff_y and sf.month<=$this->cutoff_m))";
		print "\r shift_record have $row_count rows.";
		
		$this->total['shift_record']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['shift_record'] = "delete 
			from shift_record
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." ((year<$this->cutoff_y) or (year=$this->cutoff_y and month<=$this->cutoff_m))";
			$this->optimize_table_list['shift_record'][] = 'shift_record';
		}
		
		// sa_sales_cache
		print "\nChecking sales_target_...";
		$tbl_like = 'sales_target_%';
		if($this->bid)	$tbl_like = 'sales_target_b'.$this->bid;
		$q_like = $con->sql_query("show tables like ".ms($tbl_like));
		
		while($r = $con->sql_fetchrow($q_like)){
			$tbl = trim($r[0]);
			if(strpos($tbl, 'sales_target_')!==false){
				$sql_count = "select count(*) from $tbl where date<".ms($this->cutoff_date);
				$q1 = $con->sql_query($sql_count);
				$row_count = mi($con->sql_fetchfield(0));
				$con->sql_freeresult($q1);
				
				$sql = "select * from $tbl where date<".ms($this->cutoff_date);
				print "\n $tbl have $row_count rows.";
				
				$this->total['sales_target']['total'] += $row_count;
				$this->total['total']['total'] += $row_count;
				
				if($this->is_cutoff){
					$this->delete_query[$tbl] = "delete 
					from $tbl
					where date<".ms($this->cutoff_date);
					$this->optimize_table_list[$tbl][] = $tbl;
				}
			}
		}
		$con->sql_freeresult($q_like);
		
		// vendor_commission_history
		print "\nChecking vendor_commission_history";
		$sql_count = "select count(*) from vendor_commission_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date_to<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from vendor_commission_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date_to<".ms($this->cutoff_date);
		print "\r vendor_commission_history have $row_count rows.";
		
		$this->total['vendor_commission_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['vendor_commission_history'] = "delete 
			from vendor_commission_history
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date_to<".ms($this->cutoff_date);
			$this->optimize_table_list['vendor_commission_history'][] = 'vendor_commission_history';
		}
		
		// email_list
		print "\nChecking email_list";
		$sql_count = "select count(*) from email_list where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		print "\r email_list have $row_count rows.";
		
		$this->total['email_list']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		// email_list_log
		print "\nChecking email_list_log";
		$sql_count = "select count(*) 
		from email_list_log ell
		join email_list el on el.guid=ell.email_guid
		where ".($this->bid?"el.branch_id=".mi($this->bid)." and ":"")." el.added<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		print "\r email_list_log have $row_count rows.";
		
		$this->total['email_list_log']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['email_list_log'] = "delete el,ell
			from email_list el
			left join email_list_log ell on el.guid=ell.email_guid
			where ".($this->bid?"el.branch_id=".mi($this->bid)." and ":"")." el.added<".ms($this->cutoff_date);
			$this->optimize_table_list['email_list'][] = 'email_list';
			$this->optimize_table_list['email_list'][] = 'email_list_log';
		}
		
		// suite_consignment_report_data
		print "\nChecking suite_consignment_report_data";
		$sql_count = "select count(*) from suite_consignment_report_data where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from suite_consignment_report_data where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r suite_consignment_report_data have $row_count rows.";
		
		$this->total['suite_consignment_report_data']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['suite_consignment_report_data'] = "delete 
			from suite_consignment_report_data
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['suite_consignment_report_data'][] = 'suite_consignment_report_data';
		}
	}
	
	function archive_SKU_DATA(){
		global $con;
		
		// vendor_sku_history
		print "\nChecking vendor_sku_history";
		$sql_count = "select count(*) from vendor_sku_history vsh where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." vsh.added<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select vsh.* from vendor_sku_history vsh where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." vsh.added<".ms($this->cutoff_date);
		print "\r vendor_sku_history have $row_count rows.";
		
		$this->total['vendor_sku_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['vendor_sku_history'] = "delete 
			from vendor_sku_history
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
			$this->optimize_table_list['vendor_sku_history'][] = 'vendor_sku_history';
		}
		
		// vendor_sku_history_b
		print "\nChecking vendor_sku_history_b...";
		$tbl_like = 'vendor_sku_history_%';
		if($this->bid)	$tbl_like = 'vendor_sku_history_b'.$this->bid;
		$q_like = $con->sql_query("show tables like ".ms($tbl_like));
		
		while($r = $con->sql_fetchrow($q_like)){
			$tbl = trim($r[0]);
			if(strpos($tbl, 'vendor_sku_history_')!==false){
				$sql_count = "select count(*) from $tbl where from_date>0 and to_date<".ms($this->cutoff_date);
				$q1 = $con->sql_query($sql_count);
				$row_count = mi($con->sql_fetchfield(0));
				$con->sql_freeresult($q1);
				
				$sql = "select * from $tbl where from_date>0 and to_date<".ms($this->cutoff_date);
				print "\n $tbl have $row_count rows.";
				
				$this->total['vendor_sku_history']['total'] += $row_count;
				$this->total['total']['total'] += $row_count;
				
				if($this->is_cutoff){
					$this->delete_query[$tbl] = "delete 
					from $tbl
					where from_date>0 and to_date<".ms($this->cutoff_date);
					$this->optimize_table_list[$tbl][] = $tbl;
				}
			}
		}
		$con->sql_freeresult($q_like);
		
		// sku_items_price_history
		print "\nChecking sku_items_price_history";
		$sql_count = "select count(*) from sku_items_price_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from sku_items_price_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
		print "\r sku_items_price_history have $row_count rows.";
		
		$this->total['sku_items_price_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['sku_items_price_history'] = "delete 
			from sku_items_price_history
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
			$this->optimize_table_list['sku_items_price_history'][] = 'sku_items_price_history';
		}

		// sku_items_cost_history
		print "\nChecking sku_items_cost_history";
		$sql_count = "select count(*) from sku_items_cost_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from sku_items_cost_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
		print "\r sku_items_cost_history have $row_count rows.";
		
		$this->total['sku_items_cost_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['sku_items_cost_history'] = "delete 
			from sku_items_cost_history
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date);
			$this->optimize_table_list['sku_items_cost_history'][] = 'sku_items_cost_history';
		}
		
		// stock_balance_b
		foreach($this->bid_list as $bid => $b){
			if(($this->bid && $bid == $this->bid) || !$this->bid){
				$q_like = $con->sql_query("show tables like ".ms('stock_balance_b'.$bid.'\\_%'));
				while($r = $con->sql_fetchrow($q_like)){
					$tbl_name = trim($r[0]);
					$sb_info = explode("_", $tbl_name);
					$y = mi($sb_info[3]);
					if($y <= $this->cutoff_y){
						print "\nChecking $tbl_name";
						$sql_count = "select count(*) from $tbl_name where to_date<".ms($this->cutoff_date);
						$q1 = $con->sql_query($sql_count);
						$row_count = mi($con->sql_fetchfield(0));
						$con->sql_freeresult($q1);
						
						$sql = "select * from $tbl_name where to_date<".ms($this->cutoff_date);
						print "\r $tbl_name have $row_count rows.";
						
						$this->total['stock_balance_b']['total'] += $row_count;
						$this->total['total']['total'] += $row_count;
						
						if($this->is_cutoff){							
							$this->delete_query[$tbl_name] = "delete 
							from $tbl_name
							where to_date<".ms($this->cutoff_date);
							$this->optimize_table_list[$tbl_name][] = $tbl_name;
						}
					}
				}
				$con->sql_freeresult($q_like);
			}
		}
		
		print "\nChecking sku_items_future_price";
		$sql_count = "select count(*) from sku_items_future_price where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." last_update<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from sku_items_future_price where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." last_update<".ms($this->cutoff_date);
		print "\r sku_items_future_price have $row_count rows.";
		
		$this->total['sku_items_future_price']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
				
		// sku_items_future_price_items
		print "\nChecking sku_items_future_price_items";
		$sql_count = "select count(*) 
			from sku_items_future_price_items fpi
			left join sku_items_future_price fp on fp.id=fpi.fp_id and fp.branch_id=fpi.branch_id
			where ".($this->bid?"fp.branch_id=".mi($this->bid)." and ":"")." fp.last_update<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select fpi.* 
			from sku_items_future_price_items fpi
			left join sku_items_future_price fp on fp.id=fpi.fp_id and fp.branch_id=fpi.branch_id
			where ".($this->bid?"fp.branch_id=".mi($this->bid)." and ":"")." fp.last_update<".ms($this->cutoff_date);
		print "\r sku_items_future_price_items have $row_count rows.";
		
		$this->total['sku_items_future_price_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['sku_items_future_price_items'] = "delete fp,fpi
			from sku_items_future_price fp
			left join sku_items_future_price_items fpi on fp.id=fpi.fp_id and fp.branch_id=fpi.branch_id
			where ".($this->bid?"fp.branch_id=".mi($this->bid)." and ":"")." fp.last_update<".ms($this->cutoff_date);
			$this->optimize_table_list['sku_items_future_price_items'][] = 'sku_items_future_price_items';
			$this->optimize_table_list['sku_items_future_price_items'][] = 'sku_items_future_price';
		}
		
		// sku_items_mprice_history
		print "\nChecking sku_items_mprice_history";
		$sql_count = "select count(*) from sku_items_mprice_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from sku_items_mprice_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
		print "\r sku_items_mprice_history have $row_count rows.";
		
		$this->total['sku_items_mprice_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['sku_items_mprice_history'] = "delete 
			from sku_items_mprice_history
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
			$this->optimize_table_list['sku_items_mprice_history'][] = 'sku_items_mprice_history';
		}
		
		// sku_items_qprice_history
		print "\nChecking sku_items_qprice_history";
		$sql_count = "select count(*) from sku_items_qprice_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from sku_items_qprice_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
		print "\r sku_items_qprice_history have $row_count rows.";
		
		$this->total['sku_items_qprice_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['sku_items_qprice_history'] = "delete 
			from sku_items_qprice_history
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
			$this->optimize_table_list['sku_items_qprice_history'][] = 'sku_items_qprice_history';
		}
		
		// sku_items_mqprice_history
		print "\nChecking sku_items_mqprice_history";
		$sql_count = "select count(*) from sku_items_mqprice_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from sku_items_mqprice_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
		print "\r sku_items_mqprice_history have $row_count rows.";
		
		$this->total['sku_items_mqprice_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['sku_items_mqprice_history'] = "delete 
			from sku_items_mqprice_history
			where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
			$this->optimize_table_list['sku_items_mqprice_history'][] = 'sku_items_mqprice_history';
		}
		
		if(!$this->bid && $config['consignment_modules']){
			// sku_items_rprice_history
			print "\nChecking sku_items_rprice_history";
			$sql_count = "select count(*) from sku_items_rprice_history where date<".ms($this->cutoff_date);
			$q1 = $con->sql_query($sql_count);
			$row_count = mi($con->sql_fetchfield(0));
			$con->sql_freeresult($q1);
			
			$sql = "select * from sku_items_rprice_history where date<".ms($this->cutoff_date);
			print "\r sku_items_rprice_history have $row_count rows.";
			
			$this->total['sku_items_rprice_history']['total'] += $row_count;
			$this->total['total']['total'] += $row_count;
			
			if($this->is_cutoff){				
				$this->delete_query['sku_items_rprice_history'] = "delete 
				from sku_items_rprice_history
				where date<".ms($this->cutoff_date);
				$this->optimize_table_list['sku_items_rprice_history'][] = 'sku_items_rprice_history';
			}
		}
		
		// temp_price
		if($this->check_table_exists('temp_price')){
			print "\nChecking temp_price";
			$sql_count = "select count(*) from temp_price where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." lastupdate<".ms($this->cutoff_date);
			$q1 = $con->sql_query($sql_count);
			$row_count = mi($con->sql_fetchfield(0));
			$con->sql_freeresult($q1);
			
			$sql = "select * from temp_price where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." lastupdate<".ms($this->cutoff_date);
			print "\r temp_price have $row_count rows.";
			
			$this->total['temp_price']['total'] += $row_count;
			$this->total['total']['total'] += $row_count;
			
			if($this->is_cutoff){				
				$this->delete_query['temp_price'] = "delete 
				from temp_price
				where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." lastupdate<".ms($this->cutoff_date);
				$this->optimize_table_list['temp_price'][] = 'temp_price';
			}
		}
		
		
		// temp_price_history
		if($this->check_table_exists('temp_price_history')){
			print "\nChecking temp_price_history";
			$sql_count = "select count(*) from temp_price_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added_date<".ms($this->cutoff_date);
			$q1 = $con->sql_query($sql_count);
			$row_count = mi($con->sql_fetchfield(0));
			$con->sql_freeresult($q1);
			
			$sql = "select * from temp_price_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added_date<".ms($this->cutoff_date);
			print "\r temp_price_history have $row_count rows.";
			
			$this->total['temp_price_history']['total'] += $row_count;
			$this->total['total']['total'] += $row_count;
			
			if($this->is_cutoff){				
				$this->delete_query['temp_price_history'] = "delete 
				from temp_price_history
				where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added_date<".ms($this->cutoff_date);
				$this->optimize_table_list['temp_price_history'][] = 'temp_price_history';
			}
		}
		
	}
	
	function archive_APPROVAL_HISTORY(){
		global $con;
		
		// branch_approval_history
		print "\nChecking branch_approval_history";
		$sql_count = "select count(*) from branch_approval_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select * from branch_approval_history where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." added<".ms($this->cutoff_date);
		print "\r branch_approval_history have $row_count rows.";
		
		$this->total['branch_approval_history']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
				
		// branch_approval_history_items
		print "\nChecking branch_approval_history_items";
		$sql_count = "select count(*) 
			from branch_approval_history_items bahi
			left join branch_approval_history bah on bah.id=bahi.approval_history_id and bah.branch_id=bahi.branch_id
			where ".($this->bid?"bah.branch_id=".mi($this->bid)." and ":"")." bah.added<".ms($this->cutoff_date);
        $q1 = $con->sql_query($sql_count);
		$row_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult($q1);
		
		$sql = "select bahi.*
			from branch_approval_history_items bahi
			left join branch_approval_history bah on bah.id=bahi.approval_history_id and bah.branch_id=bahi.branch_id
			where ".($this->bid?"bah.branch_id=".mi($this->bid)." and ":"")." bah.added<".ms($this->cutoff_date);
		print "\r branch_approval_history_items have $row_count rows.";
		
		$this->total['branch_approval_history_items']['total'] += $row_count;
		$this->total['total']['total'] += $row_count;
		
		if($this->is_cutoff){			
			$this->delete_query['branch_approval_history_items'] = "delete bah,bahi
			from branch_approval_history bah
			left join branch_approval_history_items bahi on bah.id=bahi.approval_history_id and bah.branch_id=bahi.branch_id
			where ".($this->bid?"bah.branch_id=".mi($this->bid)." and ":"")." bah.added<".ms($this->cutoff_date);
			$this->optimize_table_list['branch_approval_history_items'][] = 'branch_approval_history_items';
			$this->optimize_table_list['branch_approval_history_items'][] = 'branch_approval_history';
		}
	}
	
	private function mark_member_history(){
		global $con;
		
		print "\nMARK MEMBERSHIP POINT HISTORY...";
			
		// insert adjustment at cutoff date
		$q1 = $con->sql_query("select branch_id,nric,card_no,sum(points) as p
		from membership_points 
		where ".($this->bid?"branch_id=".mi($this->bid)." and ":"")." date<".ms($this->cutoff_date)." and (remark is null or remark<>".ms($this->source_label).")
		group by branch_id,nric,card_no
		order by branch_id,nric,card_no");
		
		$row_count = $con->sql_numrows($q1);
		print "\r membership_points have $row_count rows.";
		if($this->is_cutoff){
			$curr_row = 0;
			print ": Marking point history…";
			print "\n";
			while($r = $con->sql_fetchassoc($q1)){
				$curr_row++;
				print "\r$curr_row / $row_count";
			
				$upd = array();
				$upd['nric'] = $r['nric'];
				$upd['card_no'] = $r['card_no'];
				$upd['branch_id'] = $r['branch_id'];
				$upd['date'] = $this->cutoff_date;
				$upd['points'] = $r['p'];
				$upd['remark'] = $this->source_label;
				$upd['type'] = 'ADJUST';
				$upd['user_id'] = 1;
				$con->sql_query("replace into membership_points ".mysql_insert_by_field($upd));
			}
			print " - Done.";
		}
		$con->sql_freeresult($q1);
	}
	
	private function mark_sku_history(){
		global $con;

		// SKU
		print "\nMARK SKU_ITEMS HISTORY DATA…";
		
		$q_si = $con->sql_query("select si.*, sku.default_trade_discount_code,if(sku.is_fresh_market='inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market
		from sku_items si
		left join sku on sku.id=si.sku_id 
		left join category_cache cc on cc.category_id=sku.category_id
		order by si.id");
		$row_count = $con->sql_numrows($q_si);
		print "\r sku_items have $row_count rows.";
		print "\nChecking selling price, cost and stock balance...";
		
		$curr_row = 0;
		print "\n";
		
		while($si = $con->sql_fetchassoc($q_si)){
			$curr_row++;
			print "\r$curr_row / $row_count";
			
			$sid = mi($si['id']);
			
			foreach($this->bid_list as $bid => $b){
				if(($this->bid && $bid == $this->bid)|| !$this->bid){
					// selling price
					$sql = "select siph.* 
					from sku_items_price_history siph 
					where siph.branch_id=$bid and siph.sku_item_id=$sid and siph.added<=".ms($this->cutoff_date)." order by siph.added desc limit 1";
					$q2 = $con->sql_query($sql);
					$siph = $con->sql_fetchassoc($q2);
					$con->sql_freeresult($q2);
					
					if($this->is_cutoff && $siph){
						if(strtotime($siph['added']) < strtotime($this->cutoff_date)){							
							$upd = array();
							$upd = $siph;
							$upd['user_id'] = 1;
							$upd['source'] = $this->source_label;
							$upd['added'] = $this->cutoff_date;
							$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($upd));
						}
					}
					
					// cost price
					$sql = "select sich.* 
					from sku_items_cost_history sich 
					where sich.branch_id=$bid and sich.sku_item_id=$sid and sich.date<".ms($this->cutoff_date)." order by sich.date desc limit 1";
					$q3 = $con->sql_query($sql);
					$sich = $con->sql_fetchassoc($q3);
					$con->sql_freeresult($q3);
					
					if($this->is_cutoff && $sich){
						$upd = array();
						$upd = $sich;
						$upd['user_id'] = 1;
						$upd['source'] = $this->source_label;
						$upd['date'] = $this->cutoff_date;
						$con->sql_query("replace into sku_items_cost_history ".mysql_insert_by_field($upd));
					}
					
					// debtor price
					$sql = "select sidph.* 
					from sku_items_debtor_price_history sidph 
					where sidph.branch_id=$bid and sidph.sku_item_id=$sid and sidph.added<=".ms($this->cutoff_date)." order by sidph.added desc limit 1";
					$q4 = $con->sql_query($sql);
					$sidph = $con->sql_fetchassoc($q4);
					$con->sql_freeresult($q4);
					
					if($this->is_cutoff && $sidph){
						if(strtotime($sidph['added']) < strtotime($this->cutoff_date)){							
							$upd = array();
							$upd = $sidph;
							$upd['user_id'] = 1;
							$upd['added'] = $this->cutoff_date;
							$con->sql_query("replace into sku_items_debtor_price_history ".mysql_insert_by_field($upd));
						}
					}
					
					// vendor quotation cost
					$sql = "select vqc.* 
					from sku_items_vendor_quotation_cost_history vqc 
					where vqc.branch_id=$bid and vqc.sku_item_id=$sid and vqc.added<=".ms($this->cutoff_date)." order by vqc.added desc limit 1";
					$q5 = $con->sql_query($sql);
					$vqc = $con->sql_fetchassoc($q5);
					$con->sql_freeresult($q5);
					
					if($this->is_cutoff && $vqc){
						if(strtotime($vqc['added']) < strtotime($this->cutoff_date)){							
							$upd = array();
							$upd = $vqc;
							$upd['user_id'] = 1;
							$upd['added'] = $this->cutoff_date;
							$con->sql_query("replace into sku_items_vendor_quotation_cost_history ".mysql_insert_by_field($upd));
						}
					}
					
					// stock balance & stock check
					$q_sc = $con->sql_query("select qty from stock_check where branch_id=$bid and date=".ms($this->cutoff_date)." and sku_item_code=".ms($si['sku_item_code'])." limit 1");
					$sc_rows = $con->sql_numrows($q_sc);
					$con->sql_freeresult($q_sc);
					
					if(!$sc_rows){
						
						$sb = array();
						$sb_tbl = "stock_balance_b".$bid."_".date("Y", strtotime($this->one_day_b4_cutoff));
						if($this->check_table_exists($sb_tbl)){
							$sql = "select * from $sb_tbl where sku_item_id=$sid and ".ms($this->one_day_b4_cutoff)." between from_date and to_date limit 1";
							$q4 = $con->sql_query($sql);
							$sb = $con->sql_fetchassoc($q4);
							$con->sql_freeresult($q4);
						}
						
						if($this->is_cutoff){
							$upd = array();
							$upd['date'] = $this->cutoff_date;
							$upd['branch_id'] = $bid;
							$upd['sku_item_code'] = $si['sku_item_code'];
							$upd['scanned_by'] = 'ARMS';
							$upd['location'] = $this->source_label;
							$upd['shelf_no'] = $upd['item_no'] = 1;
							$upd['selling'] = $siph['price'] ? $siph['price'] : $si['selling_price'];
							$upd['qty'] = $sb['qty'];
							$upd['cost'] = $sich['grn_cost'] ? $sich['grn_cost'] : $si['cost_price'];
							$upd['is_fresh_market'] = $si['is_fresh_market'] == 'yes' ? 1 : 0;
							$con->sql_query("replace into stock_check ".mysql_insert_by_field($upd));
						}
					}
					
				}
			}
		}
		$con->sql_freeresult($q_si);
	}
	
	private function mark_currency_history(){
		global $con;

		// SKU
		print "\nMARK FOREIGN CURRENCY HISTORY DATA…";
		
		// Get Currency Code List
		$code_list = array();
		$q1 = $con->sql_query("select distinct(code) as code from foreign_currency_rate_history_record");
		while($r = $con->sql_fetchassoc($q1)){
			$code_list[] = $r['code'];
		}
		$con->sql_freeresult($q1);
		
		if($code_list){
			$curr_row = 0;
			$row_count = count($code_list);
			
			print "\r Foreign Currency have $row_count data.";
			print "\n";
			// Loop Currency Code
			foreach($code_list as $currency_code){
				$curr_row++;
				print "\r$curr_row / $row_count";
				
				// foreign_currency_rate_history_record  
				$con->sql_query("select * from foreign_currency_rate_history_record  where code=".ms($currency_code)." and date=".ms($this->cutoff_date));
				$got_data = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($got_data)	continue;
				
				$con->sql_query("select * from foreign_currency_rate_history_record  where code=".ms($currency_code)." and date<".ms($this->cutoff_date)." order by date desc limit 1");
				$last_data = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($last_data && $this->is_cutoff){
					$last_data['date'] = $this->cutoff_date;
					$con->sql_query("replace into foreign_currency_rate_history_record ".mysql_insert_by_field($last_data));
				}
			}
		}
		print " - Done.";
	}
	
	private function delete_data(){
		global $con;
		
		// delete data
		if(!$this->is_cutoff)	return;	
		
		print "\nStart Delete data...";
		if(!$this->delete_query)	print "\nNo Data to delete.";
		
		$fp = fopen('archive_data_big_data.txt', 'w');
		
		foreach($this->delete_query as $t=>$sql){
			if($this->skip_big_table){
				if(in_array($t, $this->known_big_table_list) || $this->total[$t]['total'] > $this->max_delete_row){	// more than 1 million rows
					fputs($fp, $sql.";\r\n");
					if($this->optimize_table_list[$t]){
						foreach($this->optimize_table_list[$t] as $tbl_optimize){
							fputs($fp, "optimize table $tbl_optimize".";\r\n");
						}
					}
					print "\nData too big, store the query.";
					continue;
				}
			}
			
			print "\nDeleting $t ...";
			$con->sql_query($sql);
			if($this->optimize_table_list[$t]){
				print " Optimizing ...";
				foreach($this->optimize_table_list[$t] as $tbl_optimize){
					$con->sql_query("optimize table $tbl_optimize");
				}
			}
			print " - Done";
		}
		fclose($fp);
	}
	
	private function record_db_info(){
		global $con;
		
		$con->sql_query("create table if not exists tmp_db_cutoff_info(
			id int primary key auto_increment,
			start_time timestamp default 0,
			end_time timestamp default 0,
			db_first_date date,
			is_cutoff tinyint(1) not null default 0,
			record_count int not null default 0,
			extra_info text
		)");
		
		$upd = array();
		$upd['start_time'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("select min(timestamp) as db_first_date from log");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$upd['db_first_date'] = $tmp['db_first_date'];
		$upd['is_cutoff'] = $this->is_cutoff ? 1 : 0;
		
		$con->sql_query("insert into tmp_db_cutoff_info ".mysql_insert_by_field($upd));
		$this->cutoff_id = $con->sql_nextid();
	}
	
	private function finalise_db_info(){
		global $con;
		
		$upd = array();
		$upd['end_time'] = 'CURRENT_TIMESTAMP';
		$upd['record_count'] = $this->total['total']['total'];
		
		$con->sql_query("update tmp_db_cutoff_info set ".mysql_update_by_field($upd)." where id=".$this->cutoff_id);
		
		if($this->is_cutoff){
			$config_master = array();
			$config_master['config_name'] = 'db_last_cutoff_date';
			$config_master['active'] = 1;
			$config_master['type'] = 'str';
			$config_master['value'] = $this->cutoff_date;
			
			$con->sql_query("replace into config_master ".mysql_insert_by_field($config_master));
		}
	}
}

$ARCHIVE_SALES = new ARCHIVE_SALES('Archive Sales');
?>
