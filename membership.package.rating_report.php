<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP_PACK_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_PACK_REPORT', BRANCH_CODE), "/index.php");
$maintenance->check(410);

class MEMBER_PACK_DETAILS extends Module{
	var $branch_list = array();
	var $branch_group_list = array();
	var $show_by_list = array(
		'package' => 'Package',
		'sa' => 'Sales Agent'
	);
	var $sa_list = array();
	var $sort_by_list = array(
		'avg_rate' => 'Average Star',
		'total_rate' => 'Total Star',
		'rate_count' => 'Rating Count',
		'5_star' => '5 Star',
		'4_star' => '4 Star',
		'3_star' => '3 Star',
		'2_star' => '2 Star',
		'1_star' => '1 Star',
		'0_star' => 'No Rating',
	);
	var $rate_list = array(
		0 => 'No Rate',
		1 => '1 Star',
		2 => '2 Star',
		3 => '3 Star',
		4 => '4 Star',
		5 => '5 Star',
	);
	
	function __construct($title)
	{
		// load all initial data
		$this->init_load();
		
		parent::__construct($title);
	}
	
	function _default(){
		global $smarty, $sessioninfo, $config, $LANG;

		if($_REQUEST['load_report']){
			if($_REQUEST['export_excel']){
				include_once("include/excelwriter.php");
				log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." To Excel");

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
			$this->generate_report();
		}
		$this->display();
	}
	
	private function init_load(){
		global $con, $smarty, $appCore, $config;
		
		if(!isset($_REQUEST['date_from']) && !isset($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date("Y-m-d");
			$_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month"));
		}
		
		// Get Branch
		$this->branch_list = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign('branch_list', $this->branch_list);
		
		// Get Branch Group List
		$this->branch_group_list = $appCore->branchManager->getBranchGroupList();
		//print_r($this->branch_group_list);
		$smarty->assign('branch_group_list', $this->branch_group_list);
		
		// Show By
		if(!$config['masterfile_enable_sa']){
			unset($this->show_by_list['sa']);
		}
		$smarty->assign('show_by_list', $this->show_by_list);
		
		// Sales Agent List
		if($config['masterfile_enable_sa']){
			$this->sa_list = $appCore->salesAgentManager->getSAList(array('active'=>1));
			$smarty->assign('sa_list', $this->sa_list);
			//print_r($this->sa_list);
		}
		
		// Sort By List
		$smarty->assign('sort_by_list', $this->sort_by_list);
		$smarty->assign('rate_list', $this->rate_list);
	}
	
	private function generate_report(){
		global $con, $smarty, $appCore, $config, $sessioninfo;
		
		$report_title = $err = array();
		
		// Branch ID
		$bid_list = array();
		if(BRANCH_CODE == 'HQ'){
			$tmp_bid = mi($_REQUEST['branch_id']);
			if(!$tmp_bid){	// All branch
				$bid_list = array_keys($this->branch_list);
				$report_title[] = "Branch: All";
			}elseif($tmp_bid>0){	// Select single branch
				$bid_list = array($tmp_bid);
				$report_title[] = "Branch: ".$this->branch_list[$tmp_bid]['code'];
			}elseif($tmp_bid<0){	// Branch Group
				$bgid = abs($tmp_bid);
				if($this->branch_group_list['group'][$bgid]['itemList']){
					$bid_list = array_keys($this->branch_group_list['group'][$bgid]['itemList']);
					$report_title[] = "Branch Group: ".$this->branch_group_list['group'][$bgid]['code'];
				}				
			}
		}else{
			$bid_list = array($sessioninfo['branch_id']);
			$report_title[] = "Branch: ".BRANCH_CODE;
		}
		if(!$bid_list)	$err[] = "Please Select Branch";
		
		// Date From / To
		$date_from = trim($_REQUEST['date_from']);
		$date_to = trim($_REQUEST['date_to']);
		
		if(!$date_from || !$date_to)	$err[] = "Please Select Date From / To";
		if(strtotime($date_to) <strtotime($date_from))	$err[] = "Date To cannot earlier than Date From";
		$report_title[] = "Date: From $date_from to $date_to";
		
		// Show by
		$show_by = trim($_REQUEST['show_by']);
		if(!$show_by)	$show_by = 'package';
		if(!isset($this->show_by_list[$show_by]))	$err[] = "Invalid Show By";
		$report_title[] = "Show By: ".$this->show_by_list[$show_by];
		
		// Sort By
		$sort_by = trim($_REQUEST['sort_by']);
		if(!$sort_by)	$sort_by = 'avg_star';
		if(!isset($this->sort_by_list[$sort_by]))	$err[] = "Invalid Sort By";
		$this->sort_by = $sort_by;
		if(in_array($sort_by, array('0_star','1_star','2_star','3_star','4_star','5_star'))){
			$this->sort_rate = str_replace("_star", "", $sort_by);
		}
		
		// Sort Order
		$sort_order = trim($_REQUEST['sort_order']);
		if(!$sort_order)	$sort_order = 'top';
		if($sort_order != 'top' && $sort_order != 'btm')	$err[] = "Invalid Sort Order";
		$this->sort_order = $sort_order;
		
		$report_title[] = "Sort By: ".($sort_order=='btm'?'Bottom':'Top').' '.$this->sort_by_list[$sort_by];
		
		// Sales Agent ID
		$all_sa = 1;
		$sa_id_list = array();
		if($config['masterfile_enable_sa']){
			if(!$_REQUEST['sa_id_list'] || $_REQUEST['all_sa']){
				// All Sales Agent
				$report_title[] = "Sales Agent: All";
			}else{
				// Selected Sales Agent
				if($_REQUEST['sa_id_list']){
					$sa_id_list = $_REQUEST['sa_id_list'];
					if($sa_id_list){
						$str = "";
						foreach($sa_id_list as $sa_id){
							if(!$str){
								$str = "Sales Agent: ";
							}else{
								$str .= ", ";
							}
							$str .= $this->sa_list[$sa_id]['code'];
						}
						$report_title[] = $str;
					}
				}
			}
			if(!$all_sa && !$sa_id_list)	$err[] = "Please Select Sales Agent Filter";
		}
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$filter = array();
		$filter[] = "mppir.branch_id in (".join(',', $bid_list).")";
		$filter[] = "mppir.date between ".ms($date_from)." and ".ms($date_to);
		if($sa_id_list){
			$filter_or = array();
			foreach($sa_id_list as $sa_id){
				$filter_or[] = 'sa_info like '.ms('%"id";i:'.$sa_id.';%');
			}
			$filter[] = "(".join(' or ', $filter_or).")";
		}
		
		$str_filter = "where ".join(' and ', $filter);
		
		$sql = "select mppir.*, mpp.package_unique_id, mp.title as mp_title, mp.doc_no
			from memberships_purchased_package_items_redeem mppir 
			join memberships_purchased_package_items mppi on mppi.guid=mppir.purchased_package_items_guid
			join memberships_purchased_package mpp on mpp.guid=mppi.purchased_package_guid
			join membership_package mp on mp.unique_id=mpp.package_unique_id
			$str_filter";
		//print $sql;
		
		$q1 = $con->sql_query($sql);
		$data = array();
		while($r = $con->sql_fetchassoc($q1)){
			if($show_by == 'sa'){	// Show by Sales Agent
				$r['sa_info'] = unserialize($r['sa_info']);
				if($r['sa_info']['sa_list']){
					foreach($r['sa_info']['sa_list'] as $sa_id => $sa){
						if($all_sa || in_array($sa_id, $sa)){
							$rate = mi($sa['rate']);
							$data['by_sa'][$sa_id]['rate_list'][$rate]++;
							
							$data['total']['rate_list'][$rate]++;
							
							if($rate>0){
								$data['by_sa'][$sa_id]['rate_count']++;
								$data['by_sa'][$sa_id]['total_rate']+=$rate;
								$data['by_sa'][$sa_id]['avg_rate'] = round($data['by_sa'][$sa_id]['total_rate'] / $data['by_sa'][$sa_id]['rate_count'], 2);
								
								$data['total']['rate_count']++;
								$data['total']['total_rate']+=$rate;
								$data['total']['avg_rate'] =  round($data['total']['total_rate'] / $data['total']['rate_count'], 2);
							}							
						}
					}
				}
			}else{	// Show by Package
				$package_unique_id = mi($r['package_unique_id']);
				$rate = mi($r['overall_rating']);
				
				$data['by_package'][$package_unique_id]['mp_title'] = $r['mp_title'];
				$data['by_package'][$package_unique_id]['doc_no'] = $r['doc_no'];
				$data['by_package'][$package_unique_id]['rate_list'][$rate]++;
				
				$data['total']['rate_list'][$rate]++;
				
				if($rate>0){
					$data['by_package'][$package_unique_id]['rate_count']++;
					$data['by_package'][$package_unique_id]['total_rate']+=$rate;
					$data['by_package'][$package_unique_id]['avg_rate'] = round($data['by_package'][$package_unique_id]['total_rate'] / $data['by_package'][$package_unique_id]['rate_count'], 2);
					
					$data['total']['rate_count']++;
					$data['total']['total_rate']+=$rate;
					$data['total']['avg_rate'] =  round($data['total']['total_rate'] / $data['total']['rate_count'], 2);
				}
			}
		}
		$con->sql_freeresult($q1);
		
		if($data){
			if($show_by == 'sa'){
				uasort($data['by_sa'], array($this, "sort_data"));
			}else{
				uasort($data['by_package'], array($this, "sort_data"));
			}
		}
		//print_r($data);
		
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		$smarty->assign('data', $data);
	}
	
	function sort_data($a, $b){
		if(isset($this->sort_rate)){
			if($a['rate_list'][$this->sort_rate] == $b['rate_list'][$this->sort_rate]){
				return 0;
			}
			if($a['rate_list'][$this->sort_rate] > $b['rate_list'][$this->sort_rate]){
				return $this->sort_order == 'top' ? -1 : 1;
			}else{
				return $this->sort_order == 'top' ? 1 : -1;
			}
		}else{
			if($a[$this->sort_by] == $b[$this->sort_by]){
				return 0;
			}
			if($a[$this->sort_by] > $b[$this->sort_by]){
				return $this->sort_order == 'top' ? -1 : 1;
			}else{
				return $this->sort_order == 'top' ? 1 : -1;
			}
		}
	}
}

$MEMBER_PACK_DETAILS = new MEMBER_PACK_DETAILS('Package Rating Analysis Report');
?>