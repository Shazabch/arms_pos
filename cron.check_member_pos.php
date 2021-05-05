<?php
/*
10/7/2019 4:01 PM Andy
- Added cron "check_membership_package".

12/30/2019 10:29 AM Andy
- Fixed "check_coupon_member" always count counpon usedas 1 even counter used more than once.
*/
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '512M');
set_time_limit(0);

//shell_exec("echo \"testing\" | mail andy@arms.my");

$argv = $_SERVER['argv'];
$CRON_MEMBER_POS = new CRON_MEMBER_POS();
$CRON_MEMBER_POS->start();

class CRON_MEMBER_POS {
	var $b_list = array();
	var $fp_path = '';
	
	var $member = array();
	var $card_no_list = array();
	
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
		print "Start\n";
		print date("Y-m-d H:i:s")."\n";
		
		$this->filter_argv();
		$this->check_argv();
		
		foreach($this->b_list as $b){
			$this->check_member_pos_by_branch($b);
		}
		
		print date("Y-m-d H:i:s")."\n";
		print "Done\n";
	}
	
	private function check_member_pos_by_branch($b){
		global $con, $appCore, $config;
		
		$bid = mi($b['id']);
		print "Branch: ".$b['code']."\n";
		print "Branch ID: ".$bid."\n";
		
		// Check Got use ARMS Coupon
		$con->sql_query("select setting_value from pos_settings where setting_name='use_arms_coupon' and branch_id=$bid");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$use_arms_coupon = mi($tmp['setting_value']);
		
		// Select all member and pos
		$q1 = $con->sql_query("select tmpt.*, p.receipt_no, p.member_no, p.branch_id, p.date, p.counter_id, p.id as pos_id, p.cancel_status
			from tmp_member_pos_trigger tmpt
			join pos p on p.branch_id=$bid and p.receipt_ref_no=tmpt.receipt_ref_no
			order by tmpt.card_no");
		$last_card_no = '';
		while($pos = $con->sql_fetchassoc($q1)){
			$card_no = trim($pos['card_no']);
			$bid = mi($pos['branch_id']);
			$date = $pos['date'];
			$counter_id = mi($pos['counter_id']);
			$pos_id = mi($pos['pos_id']);
			$all_success = true;
			$failed_type = '';
			if($last_card_no != $card_no){
				// Get Member
				$this->member = $appCore->memberManager->getMember($card_no);
				// Get Card No List
				$this->card_no_list = $appCore->memberManager->getMemberCardNoList($this->member['nric']);
				if(!$this->card_no_list)	$this->card_no_list = array();
				if(!in_array($card_no, $this->card_no_list))	$this->card_no_list[] = $card_no;
			}			
			
			// Begin Transaction
			$con->sql_begin_transaction();
		
			// Check Coupon Member
			if($use_arms_coupon){
				$success = $this->check_coupon_member($card_no, $bid, $date, $counter_id, $pos_id, $pos);
				if(!$success){
					$failed_type = 'check_coupon_member';
					$all_success = false;
				}					
			}
			
			// Check Membership Package
			$success = $this->check_membership_package($card_no, $bid, $date, $counter_id, $pos_id, $pos);
			if(!$success){
				$failed_type = 'check_membership_package';
				$all_success = false;
			}
			
			if($all_success){
				// delete trigger
				$con->sql_query("delete from tmp_member_pos_trigger where card_no=".ms($card_no)." and receipt_ref_no=".ms($pos['receipt_ref_no']));
				// Commit Transaction
				$con->sql_commit();
			}else{
				print "Card No: $card_no, Receipt Ref No: ".$pos['receipt_ref_no']." failed to calculate. Failed at '$failed_type'\n";
				$con->sql_rollback();
			}
			
			
			
			$last_card_no = $card_no;
		}
		$con->sql_freeresult($q1);
	}
	
	private function check_coupon_member($card_no, $bid, $date, $counter_id, $pos_id, $pos){
		global $con, $appCore;
				
		// Get Coupon List
		$q1 = $con->sql_query($sql = "select distinct(remark) as remark from pos_payment where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and pos_id=$pos_id and type='COUPON'");
		//print $sql."\n";
		$remark_list = array();
		while($pp = $con->sql_fetchassoc($q1)){
			$remark_list[] = trim($pp['remark']);
		}
		$con->sql_freeresult($q1);
		
		if(!$remark_list)	return true;
		
		foreach($remark_list as $coupon_code){
			$code = substr($coupon_code, 0, 7);
			$cim = array();
			
			print "Check Member: $card_no Coupon Code: $code\n";
			
			// Get Coupon Details
			$con->sql_query("select * from coupon where code=".ms($code));
			$coupon = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$coupon){
				print "Coupon Code '$code' not found\n";
				continue;
			}
			
			$coupon_bid = mi($coupon['branch_id']);
			$coupon_id = mi($coupon['id']);
			
			if(!$coupon_bid || !$coupon_id)	continue;	// not arms coupon
			
			if(!$coupon['member_limit_type'])	continue;	// this coupon not for member, so no need record member usage
			
			$filter = array();
			$filter[] = "p.cancel_status=0 and p.member_no in (".join(',', array_map("ms", $this->card_no_list)).")";
			$str_filter = "where ".join(' and ', $filter);
			
			// sum usage
			$con->sql_query($sql = "select distinct(p.receipt_ref_no) as receipt_ref_no, count(*) as coupon_used_count
				from pos p 
				join pos_payment pp on pp.branch_id=p.branch_id and pp.date=p.date and pp.counter_id=p.counter_id and pp.pos_id=p.id and pp.type='COUPON' and pp.remark=".ms($coupon_code)." and pp.adjust=0
				$str_filter");
			//print $sql."\n";
			$used_pos = array();
			$used_count = 0;
			while($tmp_pos = $con->sql_fetchassoc()){
				$used_pos[] = $tmp_pos;
				$used_count += mi($tmp_pos['coupon_used_count']);
			}
			$con->sql_freeresult();
			
						
			// Check if already have coupon_items_member
			$con->sql_query("select * from coupon_items_member where coupon_code=".ms($coupon_code)." and card_no=".ms($card_no)." for update");
			$cim = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			// Record more_info
			$more_info = array();
			if($cim){
				$more_info = unserialize($cim['more_info']);
			}
			$more_info['use_receipt_ref_no'] = array();
			if($used_pos){
				// Record used in which pos
				foreach($used_pos as $tmp_pos){
					$more_info['use_receipt_ref_no'][] = $tmp_pos['receipt_ref_no'];
				}
			}
				
			if($cim){
				// Update Used
				$upd = array();
				$upd['used_count'] = $used_count;
				$upd['more_info'] = serialize($more_info);
				$upd['last_update'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("update coupon_items_member set ".mysql_update_by_field($upd)." where coupon_code=".ms($coupon_code)." and card_no=".ms($card_no));
			}else{
				// Insert Used
				$upd = array();
				$upd['coupon_code'] = $coupon_code;
				$upd['branch_id'] = $coupon_bid;
				$upd['coupon_id'] = $coupon_id;
				$upd['card_no'] = $card_no;
				$upd['used_count'] = $used_count;
				$upd['more_info'] = serialize($more_info);
				$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("insert into coupon_items_member ".mysql_insert_by_field($upd));
			}
			
			$upd2 = array();
			$upd2['last_member_used_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("update coupon set ".mysql_update_by_field($upd2)." where branch_id=$coupon_bid and id=$coupon_id");
			
			print "Used Count: $used_count\n";
		}
		
		return true;
	}
	
	private function check_membership_package($card_no, $bid, $date, $counter_id, $pos_id, $pos){
		global $con, $appCore;
		
		$is_cancel = $pos['cancel_status'] == 1 ? true : false;
		$receipt_ref_no = trim($pos['receipt_ref_no']);
		
		// Check if this pos contain any membership_package
		$sql = "select pi.qty, si.membership_package_unique_id
			from pos p
			join pos_items pi on pi.branch_id=p.branch_id and pi.date=p.date and pi.counter_id=p.counter_id and pi.pos_id=p.id
			join sku_items si on si.id=pi.sku_item_id
			where p.branch_id=$bid and p.date=".ms($date)." and p.counter_id=$counter_id and p.id=$pos_id and si.membership_package_unique_id>0";
		$package_list = array();
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
			$package_list[$r['membership_package_unique_id']]['qty'] += $r['qty'];
		}
		$con->sql_freeresult();
		
		//print_r($package_list);
		
		// No Package Available, nothing to do
		if(!$package_list)	return true;
		$got_error = false;
		
		// Loop Package
		foreach($package_list as $package_unique_id=>$package_info){
			// Get Member Purchased Package
			$mpp = $appCore->memberManager->getMemberPOSPurchasedPackage($card_no, $receipt_ref_no, $package_unique_id);
			
			if($is_cancel){
				// Cancel Bought Package
				if($mpp){
					if($mpp['active']){	// Cancel if it is still active
						$result = $appCore->memberManager->cancelMemberPurchasedPackage($mpp['guid']);
						if(!$result['ok']){
							$got_error = true;	// Failed to Cancel package
							print "Error: ".$result['error']."\n";
						}
					}
				}
			}else{
				if($mpp){
					// Already Have
				}else{
					// Add Package to Member
					$result = $appCore->memberManager->addMemberPurchasedPackage($card_no, $package_unique_id, $package_info['qty'], array('pos'=>$pos));
					if(!$result['ok']){
						$got_error = true;	// Failed to Add package
						print "Error: ".$result['error']."\n";
					}
				}				
			}
		}		
		
		return $got_error ? false : true;
	}
}

?>