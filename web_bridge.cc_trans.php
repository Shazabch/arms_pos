<?php
/*
2/7/2012 10:54:21 AM Andy
- Change posting account code.
- Change CC trans to output group by payment type, no longer group by transaction.

3/6/2012 10:20:47 AM Andy
- Change AP/AR/CC Trans to use Posting Account Code and Project Code from Settings Module.

3/12/2012 11:02:24 AM Andy
- Fix wrong project code when select follow branch code.

10/21/2013 10:41 AM Justin
- Enhanced to load customer and payment codes by looping instead of hardcoded it.

3/24/2014 4:12 PM Justin
- Modified the wording from "Check" to "Cheque".
*/
include("include/common.php");
include("web_bridge.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('WB') || !privilege('WB_CC_TRANS')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'WB/WB_CC_TRANS', BRANCH_CODE), "/index.php");

class WEB_BRIDGE_CC_TRANS extends Module{
	var $branches = array();
	var $branches_group = array();
	var $branch_id = 0;
	/*var $payment_acc_code = array(
		'cash' => '500-0001',
		'cc' => '500-0002',
		'coupon' => '500-0003',
		'voucher' => '500-0004',
		'check' => '500-0005',
		
		/// credit card
		'diners' => '500-0006',
		'amex' => '500-0007',
		'visa' => '500-0008',
		'master' => '500-0009',
		'discover' => '500-0010',
		'others' => '500-0011'
	);*/
	var $payment_acc_code = array(
		'cash' => '500-000',
		'cc' => '500-100',
		'coupon' => '500-200',
		'voucher' => '500-200',
		'check' => '500-100',
		
		/// credit card
		'diners' => '500-100',
		'amex' => '500-100',
		'visa' => '500-100',
		'master' => '500-100',
		'discover' => '500-100',
		'others' => '500-100'
	);
	var $customer_code = array();
	var $project_code = '';
	
	function __construct($title){
		global $con, $smarty, $sessioninfo;

		$this->init_selection();
        if(BRANCH_CODE == 'HQ'){
			$this->branch_id = mi($_REQUEST['branch_id']);
		}else{
			$this->branch_id = mi($sessioninfo['branch_id']);
		}
		
		// customer code
		$settings = load_cc_settings();
		
		foreach($settings as $type => $f){
			if(preg_match("/^customer_code/", $type)){ // customer code
				$code = str_replace("customer_code_", "", $type);
				$this->customer_code[$code] = $f['value'];
			}elseif(preg_match("/^payment_code/", $type)){ // payment code
				$code = str_replace("payment_code_", "", $type);
				$this->payment_acc_code[$code] = $f['value'];
			}
		}
		
		// project code
		$this->project_code = $settings['project_code']['value'];
		//if(!$this->project_code || $this->project_code=='FOLLOW_BRANCH_CODE')	$this->project_code = BRANCH_CODE;
		//$smarty->assign('project_code',$this->project_code);
		
		parent::__construct($title);
	}
	
	function _default(){
		if($_REQUEST['load_summary']){
			$this->load_summary();
		}
		$this->display();
	}
	
	private function init_selection(){
	    global $con, $smarty, $sessioninfo, $pos_config;
	    	
	    if(!isset($_REQUEST['date']))	$_REQUEST['date'] = date("Y-m-d");
	    if(BRANCH_CODE=='HQ' && !isset($_REQUEST['branch_id']))	$_REQUEST['branch_id'] = $sessioninfo['branch_id'];
	    
		$con->sql_query("select * from branch where active=1 and id>0");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group header
		$con->sql_query("select * from branch_group",false,false);
		while($r = $con->sql_fetchassoc()){
            $this->branches_group['header'][$r['id']] = $r;
		}
		$con->sql_freeresult();

		if($this->branches_group){
            // load branch group items
			$con->sql_query("select bgi.*,branch.code,branch.description
			from branch_group_items bgi
			left join branch on bgi.branch_id=branch.id
			where branch.active=1");
			while($r = $con->sql_fetchassoc()){
		        $this->branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
		        $this->branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
			}
			$con->sql_freeresult();
		}
		$smarty->assign('branches_group',$this->branches_group);
	}
	
	private function load_summary(){
		global $con, $smarty, $sessioninfo;
		
		$err = array();
		$this->load_data($err);
		
		if($err){
			$smarty->assign('err', $err);
			return false;
		}
		
		//print_r($this->data);
		$smarty->assign('data', $this->data);
	}
	
	private function load_data(&$err){
		global $con, $smarty, $sessioninfo, $pos_config;
		
		$bid = mi($this->branch_id);
		$date = trim($_REQUEST['date']);
		$split_cc_type = mi($_REQUEST['split_cc_type']);
		
		$report_title = array();
		//print_r($_REQUEST);
		
		if(!$bid)	$err[] = "Please select branch.";
		if(!$date)	$err[] = "Please select date";
		
		$payment_type_list = array();
		foreach($this->payment_acc_code as $pt=>$pt_code){
			if(!$pt_code)	$err[] = "Please setup Posting Account Code ($pt) at Settings Module first.";
			if(!$this->customer_code[$pt])	$err[] = "Please setup Customer Code ($pt) at Settings Module first.";
			$payment_type_list[] = ms($pt);
		}
		
		if($err){
			return false;
		}
		
		$filter = array();
		$filter[] = "pos.branch_id=$bid and pos.date=".ms($date);
		$filter[] = "pos.cancel_status=0";
		$filter[] = "pp.type in (".join(',', $payment_type_list).")";
		
		$filter = "where ".join(' and ', $filter);
		
		$bcode = get_branch_code($bid);
		$report_title[] = "Branch: $bcode";
		$report_title[] = "Sales Date: $date";
		
		if(!$this->project_code || $this->project_code=='FOLLOW_BRANCH_CODE')	$this->project_code = $bcode;
		$smarty->assign('project_code',$this->project_code);
		
		$sql = "select cs.network_name,pos.date,if(pp.type='cash',pp.amount-pos.amount_change,pp.amount) as amt,pp.type as payment_type, pos.counter_id
from pos
left join pos_payment pp on pp.branch_id=pos.branch_id and pp.counter_id=pos.counter_id and pp.date=pos.date and pp.pos_id=pos.id
left join counter_settings cs on cs.branch_id=pos.branch_id and cs.id=pos.counter_id
$filter
order by payment_type";

		//print $sql;
		$this->data = array();
		$q1 = $con->sql_query($sql);
		
		// header 
		while($r = $con->sql_fetchassoc($q1)){
			
			
			if(!$split_cc_type && in_array($r['payment_type'], $pos_config['credit_card'])){
				// group all credit card to 'cc'
				$r['payment_type'] = 'cc';
			}
			$r['payment_type'] = strtolower($r['payment_type']);
			
			$key = $r['counter_id']."_".$r['payment_type'];

			if(!isset($this->data['items'][$key])){
				$this->data['items'][$key]['acc_code'] = $this->payment_acc_code[$r['payment_type']];
				$this->data['items'][$key]['customer_code'] = $this->customer_code[$r['payment_type']];
				$this->data['items'][$key]['date'] = $r['date'];
				
				$r['payment_type'] = ucfirst($r['payment_type']);
				
				if($pos_config['payment_type_label'][$r['payment_type']]) $r['payment_type'] = $pos_config['payment_type_label'][$r['payment_type']];
				
				$this->data['items'][$key]['payment_type'] = ucfirst($r['payment_type']);
				$this->data['items'][$key]['docno'] = date("Ymd", strtotime($r['date'])).'-'.$bcode.'-'.$r['network_name'];
			}
			$this->data['items'][$key]['amt'] += round($r['amt'],2);
			
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		$smarty->assign('bcode', $bcode);
	}
	
	function download_export_file(){
		global $smarty;
		
		$export_format = trim($_REQUEST['format']);
		
		$err = array();
		$this->load_data($err);
		
		if($err){
			print_r($err);exit;
		}
		
		$bid = mi($this->branch_id);
		$bcode = get_branch_code($bid);
		$date = $_REQUEST['date'];
		
		//print_r($this->data);
		$smarty->assign('export_format', $export_format);
		$smarty->assign('data', $this->data);
		$output = $smarty->fetch('web_bridge.cc_trans.file.tpl');
		
		if(!$output)	js_redirect('There is no data to export.', $_SERVER['PHP_SELF']);
		
		$export_filename = "cc_trans_".$bcode."_".date("Ymd", strtotime($date)).".txt";
		
		log_br($sessioninfo['id'], 'WEB_BRIDGE', '', "Export CC Trans, Branch: $bcode, Sales Date: $date, Format: $export_format");
		
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=".$export_filename);
		print $output;
	}
}

$WEB_BRIDGE_CC_TRANS = new WEB_BRIDGE_CC_TRANS('Web Bridge: CC Trans');

?>