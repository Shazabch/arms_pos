<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('STOCK_TAKE_CYCLE_COUNT_SCHEDULE_LIST') && !privilege('STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_TAKE_CYCLE_COUNT_SCHEDULE_LIST', BRANCH_CODE), "/index.php");
$maintenance->check(405);

class CYCLE_COUNT_MONTHLY_SCHEDULE extends Module{
	var $branches = array();
	var $year_month_list = array();
	var $status_list = array(
		'approved' => 'Not Yet Start',
		'printed' => 'Printed',
		'wip' => 'WIP',
		'completed' => 'Completed',
		'sent_to_stock_take' => 'Sent to Stock Take',
	);
	
	
	function __construct($title)
	{
		// load all initial data
		$this->init_load();
		
		parent::__construct($title);
	}
	
	function _default()
	{
		global $smarty, $sessioninfo, $config;

		if($_REQUEST['load_data']){
			$this->load_data();
			
			if($_REQUEST['export_excel']){
				include_once("include/excelwriter.php");
				log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." To Excel");

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		}
		$this->display();
	}
	
	private function init_load(){
		global $appCore, $smarty, $con, $sessioninfo;
		
		// Branch
		$this->branches = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign('branches', $this->branches);
		
		// Status List
		$smarty->assign('status_list', $this->status_list);
				
		// Year / Month
		$filter = array();
		$filter[] = "cc.active=1 and cc.status=1 and cc.approved=1";
		if(BRANCH_CODE != 'HQ'){
			$filter[] = "cc.st_branch_id=".mi($sessioninfo['branch_id']);
		}
		$str_filter = "where ".join(' and ', $filter);
		$q1 = $con->sql_query("select distinct(DATE_FORMAT(propose_st_date, '%Y-%m')) as ym from cycle_count cc $str_filter order by ym desc");
		while($r = $con->sql_fetchassoc($q1)){
			list($y, $m) = explode("-", $r['ym']);
			$this->year_month_list[$r['ym']] = str_month($m).' '.$y;
		}
		$con->sql_freeresult($q1);
		$smarty->assign('year_month_list', $this->year_month_list);
	}
	
	private function load_data(){
		global $appCore, $smarty, $con, $sessioninfo;
		
		//print_r($_REQUEST);
		$err = array();
		$report_title = array();
		$filter = array();
		$filter[] = "cc.active=1 and cc.status=1 and cc.approved=1";
		
		// Branch
		if(BRANCH_CODE == 'HQ'){
			$st_branch_id = mi($_REQUEST['st_branch_id']);
			if($st_branch_id>0){
				$filter[] = "cc.st_branch_id=".mi($st_branch_id);
				$report_title[]= "Stock Take Branch: ".$this->branches[$st_branch_id]['code'];
			}else{
				$report_title[]= "Stock Take Branch: All";
			}
		}else{
			$filter[] = "cc.st_branch_id=".mi($sessioninfo['branch_id']);
			$report_title[]= "Stock Take Branch: ".BRANCH_CODE;
		}
		
		if($_REQUEST['ym']){
			// Year / Month
			$ym = trim($_REQUEST['ym']);
			list($y, $m) = explode("-", $ym);
			$y = mi($y);
			$m = mi($m);
			if($y<2000)	$err[] = "Invalid Year";
			if($m<1 || $m > 12)	$err[] = "Invalid Month";
			
			if(!$err){
				$date_from = $y.'-'.$m.'-1';
				$date_to = $y.'-'.$m.'-'.days_of_month($m, $y);
			
				$filter[] = "cc.propose_st_date between ".ms($date_from)." and ".ms($date_to);
				$report_title[]= "Month / Year: ".$this->year_month_list[$ym];
			}
		}else{
			$err[] = "Please select Month / Year";
		}
		
		// Got Error
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$data = array();
		
		$str_filter = "where ".join(' and ', $filter);
		$sql = "select cc.*, st_b.code as st_bcode, u_pic.u as pic_username, c.description as cat_desc, v.description as vendor_desc, br.description as brand_desc, sg.code sg_code, sg.description as sg_desc
			from cycle_count cc
			left join branch st_b on st_b.id=cc.st_branch_id
			left join user u_pic on u_pic.id=cc.pic_user_id
			left join category c on c.id=cc.category_id
			left join vendor v on v.id=cc.vendor_id
			left join brand br on br.id=cc.brand_id
			left join sku_group sg on sg.branch_id=cc.sku_group_bid and sg.sku_group_id=cc.sku_group_id
			$str_filter
			order by cc.propose_st_date desc";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$key = $r['branch_id'].'_'.$r['id'];
			
			
			if($r['sent_to_stock_take']){
				$status_key = 'sent_to_stock_take';
			}elseif($r['completed']){
				$status_key = 'completed';
			}elseif($r['wip']){
				$status_key = 'wip';
			}elseif($r['printed']){
				$status_key = 'printed';
			}else{
				$status_key = 'approved';
			}
			$r['status_key'] = $status_key;
			
			$data['cc_list'][$key] = $r;
			
			$data['total_count']++;
			$data['status_list'][$status_key]['count']++;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('data', $data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
}

$CYCLE_COUNT_MONTHLY_SCHEDULE = new CYCLE_COUNT_MONTHLY_SCHEDULE('Cycle Count Monthly Schedule List');
?>