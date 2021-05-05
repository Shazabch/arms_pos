<?php
/*
1/25/2017 5:31 PM Andy
- Fixed group by monthly not working.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('RPT_MEMBERSHIP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'RPT_MEMBERSHIP', BRANCH_CODE), "/index.php");

class MEMBER_ISSUED_POINTS_REPORT extends Module{
	var $branches = array();
	var $branch_group = array();
	var $group_type_list = array('day'=>'Daily', 'month'=>'Monthly');
	var $issued_type_list = array('POS' => 'POS', 'ADJUST' => 'Adjust');
	
	function __construct($title){
		global $con, $smarty, $config;

		
		if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
		if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');

		// load branches
		$con->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group
		$this->load_branch_group();
		
		$smarty->assign('group_type_list',$this->group_type_list);
		$smarty->assign('issued_type_list',$this->issued_type_list);
		
    	parent::__construct($title);
    }
	
	function _default(){
		global $con, $smarty, $sessioninfo;
		
		if($_REQUEST['show_report']){
			if($_REQUEST['export_excel']){
				include_once("include/excelwriter.php");
				$smarty->assign('no_header_footer', true);
				$filename = "membership_issued_points_report_".time().".xls";
				log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." To Excel($filename)");
				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename='.$filename);

				print ExcelWriter::GetHeader();
			}
			
		
			$this->load_report();
		}
		$this->display();
	}
	
	private function load_branch_group(){
		global $con,$smarty;
		
	    if($this->branch_group)  return $this->branch_group;
		$this->branch_group = array();
		
		// load header
		$con->sql_query("select * from branch_group");
		while($r = $con->sql_fetchrow()){
            $this->branch_group['header'][$r['id']] = $r;
		}
		$con->sql_freeresult();		

		// load items
		$con->sql_query("select bgi.*,branch.code,branch.description 
			from branch_group_items bgi 
			left join branch on bgi.branch_id=branch.id 
			where branch.active=1 order by branch.sequence, branch.code");
		while($r = $con->sql_fetchassoc()){
	        $this->branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $this->branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con->sql_freeresult();

		//print_r($this->branch_group);
		$smarty->assign('branch_group',$this->branch_group);
	}
	
	private function load_report(){
		global $con, $smarty,$sessioninfo;
		
		$err = array();
		$this->data = array();
		
		$con_multi = new mysql_multi();
		if(!$con_multi){
	 		$err[] = "Error: Fail to connect report server";
		}
		
		$report_title = array();
		$branch_id_list = array();
		
		// BRANCH
		if(BRANCH_CODE == 'HQ'){    // HQ mode
			$branch_id = mi($_REQUEST['branch_id']);
			if($branch_id<0)	$bgid = abs($branch_id);
			if($bgid){ // branch group selected
				if($this->branch_group){
					foreach($this->branch_group['items'][$bgid] as $bid=>$b){
						$branch_id_list[] = $bid;
					}
				}
				$report_title[] = "Branch Group: ".$this->branch_group['header'][$bgid]['code'];
			}elseif($branch_id){  // single branch selected
			    $branch_id_list[] = $branch_id;
                $report_title[] = "Branch: ".get_branch_code($branch_id);
			}   
			else{   // all branches selected
				foreach($this->branches as $bid=>$b){
                    $branch_id_list[] = $bid;
				}
				$report_title[] = "Branch: All";
			}
		}else{  // Branches mode
            $branch_id_list[] = mi($sessioninfo['branch_id']);
            $report_title[] = "Branch: ".BRANCH_CODE;
		}
		
		// DATE
		$date_from = date("Y-m-d", strtotime($_REQUEST['date_from']));
		$date_to = date("Y-m-d", strtotime($_REQUEST['date_to']));
		// check if the date is more than 1 month
		$end_date =date("Y-m-d",strtotime("+1 year",strtotime($date_from)));
    	if(strtotime($date_to)>strtotime($end_date)) $date_to = $end_date;
		$_REQUEST['date_from'] = $date_from;
		$_REQUEST['date_to'] = $date_to;
		$issued_type = $_REQUEST['issued_type'];
		
		$report_title[] = "Date From: $date_from to $date_to";
		
		// GROUP TYPE
		$group_type = trim($_REQUEST['group_type']);
		if(!isset($this->group_type_list[$group_type]))	$err[] = "Invalid Group Type";
		$report_title[] = "Group Data by: ".$this->group_type_list[$group_type];
		
		// SHOW BY BRANCH
		$show_by_branch = mi($_REQUEST['show_by_branch']);
		if($show_by_branch)	$report_title[] = "Show by Branch: Yes";
		
		$selected_issue_type_list = $selected_issue_type_list2 = array();
		IF($issued_type){
			foreach($issued_type as $type=>$dummy){
				if(!isset($this->issued_type_list[$type])){
					$err[] = "Invalid Points Issued Type ($type)";
				}else{
					$selected_issue_type_list[] = $type;
					$selected_issue_type_list2[] = ms($type);
				}
			}
		}else{
			$err[] = "Please select Points Issued Type.";
		}
		
		if($err){
			$smarty->assign('err', $err);
			$con_multi->close_connection();
			return;
		}
		
		$filter = array();
		$filter[] = "mp.branch_id in (".join(',', $branch_id_list).")";
		$filter[] = "mp.date between ".ms($date_from)." and ".ms($date_to." 23:59:59");
		$filter[] = "mp.type in (".join(',', $selected_issue_type_list2).")";
		$filter[] = "mp.points<>0";
		$filter = join(' and ', $filter);
		
		$extra_column = "";
		$group_by = "group by points_date,mp.type";
		if($show_by_branch){
			$extra_column .= ",mp.branch_id";
			$group_by .= ",mp.branch_id";
		}
		
		$sql = "select date(mp.date) as points_date, mp.type, points $extra_column
									 from membership_points mp
									 where $filter
									 $group_by
									 order by points_date";
		//print $sql;
		$q1 = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q1)){
			$date_key = $group_type == "month" ? date("Ym", strtotime($r['points_date'])) : $r['points_date'];
			
			if($group_type == 'month'){
				$this->data['date_label'][$date_key] = date("Y/m", strtotime($r['points_date']));
			}
			
			if($show_by_branch){
				$this->data['data']['by_branch'][$r['branch_id']]['by_date'][$date_key][$r['type']]['points'] += $r['points'];
				$this->data['data']['by_branch'][$r['branch_id']]['by_date'][$date_key]['total']['points'] += $r['points'];
				$this->data['data']['by_branch'][$r['branch_id']]['total'][$r['type']]['points'] += $r['points'];
				$this->data['data']['by_branch'][$r['branch_id']]['total']['total']['points'] += $r['points'];
			}else{
				$this->data['data']['by_date'][$date_key][$r['type']]['points'] += $r['points'];
				$this->data['data']['by_date'][$date_key]['total']['points'] += $r['points'];
				$this->data['data']['total'][$r['type']]['points'] += $r['points'];
				$this->data['data']['total']['total']['points'] += $r['points'];
			}
			$this->data['total'][$r['type']]['points'] += $r['points'];
			$this->data['total']['total']['points'] += $r['points'];
		}
		$con_multi->sql_freeresult($q1);
		
		$con_multi->close_connection();
		
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		$smarty->assign('data', $this->data);
		$smarty->assign('selected_issue_type_list', $selected_issue_type_list);
		$smarty->assign('group_type', $group_type);
	}
}

$MEMBER_ISSUED_POINTS_REPORT = new MEMBER_ISSUED_POINTS_REPORT('Membership Issued Points Report');
?>