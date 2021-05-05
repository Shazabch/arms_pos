<?php
/*
11/11/2011 5:17:18 PM Andy
- Fix export excel not working.

12/10/2013 1:47 PM Andy
- Fix report memory leak on getting year dropdown.

4:17/2017 3:21 PM Andy
- Recommit and upload again to globepharmacy.

2/26/2020 10:35 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
$maintenance->check(95);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PROMOTION')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PROMOTION', BRANCH_CODE), "/index.php");
include_once('counter_collection.include.php');
include_once('promotion.include.php');

class MIX_N_MATCH_PROMOTION_RESULT extends Module{
	var $branches = array();
	var $branch_id = 0;
	var $months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
	var $promo_filter_list = array(
		1=>'Saved',
		2=>'Waiting for Approval',
		3=>'Rejected',
		4=>'Cancelled/Terminated',
		5=>'Approved'
	);
	var $data = array();
	
	function __construct($title, $template=''){
		global $con, $sessioninfo, $smarty, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		if(BRANCH_CODE == 'HQ'){
			$this->branch_id = $_REQUEST['branch_id'] ? $_REQUEST['branch_id'] : $sessioninfo['branch_id'];
		}else	$this->branch_id = mi($sessioninfo['branch_id']);
		$this->init_load();
		
		parent::__construct($title, $template);
	}
	
	function _default(){
		global $con, $smarty, $sessioninfo, $con_multi;
		
		// load min max year		
		$con_multi->sql_query("select year(min(date_from)) as min_y,year(max(date_to)) as max_y from promotion where promo_type='mix_and_match'");
		$min_max_year = $con_multi->sql_fetchassoc();
		$con_multi->sql_freeresult();
		
		if($min_max_year['min_y']<2005 && $min_max_year['max_y']<2005)	unset($min_max_year);
		if($min_max_year){	// min year 2005
			if($min_max_year['min_y']<2005)	$min_max_year['min_y'] = 2005;
			$year_list = array();
			for($i = $min_max_year['max_y']; $i >= $min_max_year['min_y']; $i--){				
				$year_list[] = $i;
			}
			$smarty->assign('year_list', $year_list);
		}
		
		if($_REQUEST['load_report']){
			$success = $this->load_report();
			if($success && $_REQUEST['export_excel']){
				include_once("include/excelwriter.php");
				$smarty->assign('no_header_footer', true);
				$filename = "MixMatchResult_".time().".xls";
		    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export $this->title To Excel($filename)");
		    	Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename='.$filename);
				print ExcelWriter::GetHeader();
			}else{
				$this->ajax_load_promo_list(true);
			}
			
			
		}
		$this->display();
	}
	
	private function init_load(){
		global $con, $smarty, $con_multi;
		
		$q1 = $con_multi->sql_query("select id,code,description from branch order by sequence,code");
		while($r = $con_multi->sql_fetchassoc($q1)){
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);
		$smarty->assign('branches', $this->branches);
		
		$smarty->assign('months', $this->months);
		$smarty->assign('promo_filter_list', $this->promo_filter_list);
	}
	
	/*function connect_report_server(){
		global $con_multi;
		
		if(!$con_multi)	$con_multi = new mysql_multi();
	}*/
	
	function ajax_load_promo_list($sqlonly = false){
		global $con, $smarty, $sessioninfo,$con_multi;
		
		$bid = $this->branch_id;
		$y = mi($_REQUEST['year']);
		$m = mi($_REQUEST['month']);
		$promo_filter = mi($_REQUEST['promo_filter']);
		
		if($y<2005)	die("Invalid Year.");
		if($m<1 || $m>12)	die('Invalid Month.');
		
		$date_from = $y.'-'.$m.'-1';
		$date_to = $y.'-'.$m.'-'.days_of_month($m, $y);
		
		$filter = array();
		$filter[] = "p.promo_type='mix_and_match'";
		if($bid==1)	$filter[] = "p.branch_id=1";	// hq only view own created promo
		else	$filter[] = "p.branch_id in (1,$bid)";
		$filter[] = "p.promo_branch_id like '%i:$this->branch_id;%'";
		$filter[] =" p.date_to >= ".ms($date_from)." and p.date_from <= ".ms($date_to);
		
		if($promo_filter){
			switch($promo_filter){
				case 1:	// Saved
					$filter[] = "p.active=1 and p.status=0 and p.approved=0";
					break;
				case 2:	// waiting for approval
					$filter[] = "p.active=1 and (p.status=1 or p.status=3) and p.approved=0";
					break;
				case 3:	// rejected
					$filter[] = "p.active=1 and p.status=2";
					break;
				case 4:	// cancelled/terminated
					$filter[] = "(p.status=4 or p.status=5)";
					break;
				case 5:	// approved
					$filter[] ="p.active=1 and p.status=1 and p.approved=1";
					break;
			}
		}
		$filter = "where ".join(' and ', $filter);
		$sql = "select p.branch_id,p.id,p.title,branch.code as bcode,p.active,p.status,p.approved
		from promotion p
		left join branch on branch.id=p.branch_id
		$filter
		order by p.id desc";
		//print $sql;
		$con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc()){
			$promo_list[] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('promo_list', $promo_list);
		
		if(!$sqlonly){	
			$smarty->display('report.mix_n_match_promotion_result.promotion_list.tpl');
		}else	return $promo_list;
	}
	
	private function load_report(){
		global $con, $sessioninfo, $con_multi, $smarty;
		
		$branch_promo_id = $_REQUEST['branch_promo_id'];
		$y = mi($_REQUEST['year']);
		$m = mi($_REQUEST['month']);
		$err = array();
		$date_from = $y.'-'.$m.'-1';
		$date_to = $y.'-'.$m.'-'.days_of_month($m, $y);
		
		//print_r($branch_promo_id);
		if(!$branch_promo_id){	// no promo id
			$err[] = "Please select at least 1 promotion.";
		}
		
		if($err){	// got error
			$smarty->assign('err', $err);
			return false;
		}	
		
		$report_title = array();
		$report_title[] = "Branch: ".get_branch_code($this->branch_id);
		$report_title[] = "Year: $y";
		$report_title[] = "Month: ".$this->months[$m];
		
		//$this->connect_report_server();	// connect report server
		
		// generate date list
		$curr_datetime = strtotime($date_from);
		$max_datetime = strtotime($date_to);
		$date_list = array();
		while($curr_datetime<=$max_datetime){
			$date_key = date("Ymd", $curr_datetime);
			
			$date_list[$date_key] = date("Y-m-d", $curr_datetime);
			$curr_datetime += 86400;
		}
		
		foreach($branch_promo_id as $counter_promo_id){
			$data = array();
			
			$bid = mi(substr($counter_promo_id, -3));
			$promo_id = mi(substr($counter_promo_id, 0, -3));
			
			if(!$promo_id || !$bid)	continue;
			
			//print "$counter_promo_id<br />";
			
			// header info
			$q_p = $con_multi->sql_query("select p.* from promotion p where p.branch_id=$bid and p.id=$promo_id and p.promo_type='mix_and_match'");
			$data['promo_info'] = $con_multi->sql_fetchassoc($q_p);
			$con_multi->sql_freeresult($q_p);
			
			if(!$data['promo_info'])	continue;	// promotion not found
			
			// get promo usage
			$q_pmm = $con_multi->sql_query("select pmm.* 
			from pos_mix_match_usage pmm
			left join pos on pos.branch_id=pmm.branch_id and pos.counter_id=pmm.counter_id and pos.date=pmm.date and pos.id=pmm.pos_id
			where pmm.promo_id=$counter_promo_id and pmm.date between ".ms($date_from)." and ".ms($date_to)." and pos.cancel_status=0 and pmm.branch_id=$this->branch_id");
			while($r = $con_multi->sql_fetchassoc($q_pmm)){
				$date_key = date("Ymd", strtotime($r['date']));
				$tran_key = $r['branch_id'].'_'.$r['date'].'_'.$r['counter_id'].'_'.$r['pos_id'];
				
				if(!isset($data['promo_usage'][$date_key]['pos'][$tran_key])){
					$data['promo_usage'][$date_key]['pos'][$tran_key] = 1;
					$data['promo_usage'][$date_key]['pos_count']++;
				}
				
				if(!isset($data['promo_usage']['total']['pos'][$tran_key])){
					$data['promo_usage']['total']['pos'][$tran_key] = 1;
					$data['promo_usage']['total']['pos_count']++;
				}
				
				$data['promo_usage'][$date_key]['amt'] += $r['amount'];
				$data['promo_usage']['total']['amt'] += $r['amount'];
			}
			$con_multi->sql_freeresult($q_pmm);
			
			$this->data['by_promo'][$counter_promo_id] = $data;
		}
		//$con_multi->close_connection();
		
		// calculate total
		if($this->data){
			foreach($this->data['by_promo'] as $counter_promo_id=>$promo){
				if($promo['promo_usage']){
					foreach($promo['promo_usage'] as $date_key=>$r){
						if($date_key=='total')	continue;	// skip total row
						
						if($r['pos']){	// do not sum overlap transaction
							foreach($r['pos'] as $tran_key=>$dummy){
								if(!isset($this->data['total'][$date_key]['pos'][$tran_key])){
									$this->data['total'][$date_key]['pos'][$tran_key] = 1;
									
									$this->data['total'][$date_key]['pos_count'] ++;
									$this->data['total']['total']['pos_count'] ++;
											
								}
							}
						}
						
						$this->data['total'][$date_key]['amt'] += $r['amt'];
						$this->data['total']['total']['amt'] += $r['amt'];
					}
				}
			}
		}
		//print_r($this->data);
		//print_r($date_list);
		$smarty->assign('data', $this->data);
		$smarty->assign('date_list', $date_list);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		
		return true;
	}
}

$MIX_N_MATCH_PROMOTION_RESULT = new MIX_N_MATCH_PROMOTION_RESULT('Mix and Match Promotion Result');
?>
