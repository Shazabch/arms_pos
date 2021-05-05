<?php
/*
2/7/2012 10:55:50 AM Andy
- Change posting account code.

3/6/2012 10:20:40 AM Andy
- Change AP/AR/CC Trans to use Posting Account Code and Project Code from Settings Module.
*/
include("include/common.php");
include("web_bridge.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('WB') || !privilege('WB_AR_TRANS')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'WB/WB_AR_TRANS', BRANCH_CODE), "/index.php");

class WEB_BRIDGE_AR_TRANS extends Module{
	var $branches = array();
	var $branches_group = array();
	var $branch_id = 0;
	
	function __construct($title){
		global $con, $smarty, $sessioninfo;

		$this->init_selection();
        if(BRANCH_CODE == 'HQ'){
			$this->branch_id = mi($_REQUEST['branch_id']);
		}else{
			$this->branch_id = mi($sessioninfo['branch_id']);
		}
		
		parent::__construct($title);
	}
	
	function _default(){
		if($_REQUEST['load_summary']){
			$this->load_summary();
		}
		$this->display();
	}
	
	private function init_selection(){
	    global $con, $smarty, $sessioninfo;
	    	
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
		global $con, $smarty, $sessioninfo;
		
		$bid = mi($this->branch_id);
		$date = trim($_REQUEST['date']);
		$report_title = array();
		//print_r($_REQUEST);
		
		if(!$bid)	$err[] = "Please select branch.";
		if(!$date)	$err[] = "Please select date";
		$do_type_list = array();
		if($_REQUEST['tf'])	$do_type_list[] = ms('transfer');
		if($_REQUEST['open'])	$do_type_list[] = ms('open');
		if($_REQUEST['cs'])	$do_type_list[] = ms('credit_sales');
		if(!$do_type_list)	$err[] = "Please select at least 1 DO type.";
		
		$ar_settings = load_ar_settings();
		if(!$ar_settings['posting_account_code_transfer']['value']){
			$err[] = "Please setup Posting Account Code (Transfer DO) at Settings Module first.";
		}
		if(!$ar_settings['posting_account_code_open']['value']){
			$err[] = "Please setup Posting Account Code (Cash Sales DO) at Settings Module first.";
		}
		if(!$ar_settings['posting_account_code_credit_sales']['value']){
			$err[] = "Please setup Posting Account Code (Credit Sales DO) at Settings Module first.";
		}
		if(!$ar_settings['project_code']['value']){
			$err[] = "Please setup Project Code at Settings Module first.";
		}
		
		if($err){
			return false;
		}
		
		$filter = array();
		$filter[] = "do.branch_id=$bid and do.do_date=".ms($date);
		$filter[] = "do.active=1 and do.status=1 and do.approved=1 and do.checkout=1 and do.inv_no<>''";
		$filter[] = "do.do_type in (".join(',', $do_type_list).")";
		
		$filter = "where ".join(' and ', $filter);
		
		$bcode = get_branch_code($bid);
		$report_title[] = "Branch: $bcode";
		$report_title[] = "Invoice Date: $date";
		
		$project_code = $ar_settings['project_code']['value'];
		if($project_code=='FOLLOW_BRANCH_CODE')	$project_code = $bcode;
		
		$sql = "select do.branch_id, do.id as do_id,b.code as bcode, b.description as bdesc, do.inv_no, do.do_type, do.total_inv_amt,do.debtor_id,d.code as debtor_code,d.description as debtor_desc,d.term as debtor_term,do.open_info,do.do_date
from do
left join branch b on b.id=do.do_branch_id and do.do_type='transfer'
left join debtor d on d.id=do.debtor_id and do.do_type='credit_sales'
$filter
order by inv_no";

		//print $sql;
		$this->data = array();
		$q1 = $con->sql_query($sql);
		
		// header 
		while($r = $con->sql_fetchassoc($q1)){
			$r['total_inv_amt'] = round($r['total_inv_amt'], 2);
			$r['open_info'] = unserialize($r['open_info']);
							
			if($r['do_type']=='transfer')	$r['posting_account'] = $ar_settings['posting_account_code_transfer']['value'];
			elseif($r['do_type']=='open')	$r['posting_account'] = $ar_settings['posting_account_code_open']['value'];
			elseif($r['do_type']=='credit_sales')	$r['posting_account'] = $ar_settings['posting_account_code_credit_sales']['value'];
			
			$r['project_code'] = $project_code;
			$this->data['items'][] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
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
		$output = $smarty->fetch('web_bridge.ar_trans.file.tpl');
		
		if(!$output)	js_redirect('There is no data to export.', $_SERVER['PHP_SELF']);
		
		$export_filename = "ar_trans_".$bcode."_".date("Ymd", strtotime($date)).".txt";
		
		log_br($sessioninfo['id'], 'WEB_BRIDGE', '', "Export AR Trans, Branch: $bcode, Inv Date: $date, Format: $export_format");
		
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=".$export_filename);
		print $output;
	}
}

$WEB_BRIDGE_AR_TRANS = new WEB_BRIDGE_AR_TRANS('Web Bridge: AR Trans');

?>
