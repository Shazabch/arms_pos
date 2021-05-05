<?php

/*
8/18/2010 11:11:07 AM Justin
- Modified to have print report and export to excel format.

8/27/2010 3:52:49 PM Justin
- Added Cash info.

6/24/2011 5:02:24 PM Andy
- Make all branch default sort by sequence, code.

07/23/2013 04:07 PM Justin
- Added to pickup cost.

11/14/2016 1:40 PM Andy
- Fixed export to excel instead of zip file.
- Change print feature to use window.print()
- Fixed user dropdown to filter out arms user.
- Enhanced to able to select report type. (Summary or Details)
*/

include("include/common.php");

//ini_set("display_errors",1);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP')&&!privilege('RPT_MEMBERSHIP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP and RPT_MEMBERSHIP', BRANCH_CODE), "/index.php");
$maintenance->check(34);

class Membership_Redemption_Summary extends Module{
	var $user_filter = "";
	var $status_list = array('complete'=>'Successful','cancel'=>'Cancelled');
	var $type_list = array(1=>'Summary', 2=>'Details');
	
	function __construct($title){
        global $con, $smarty, $sessioninfo;
        
        if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
		if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));

		if($sessioninfo['id'] != 1){
			$this->user_filter = "(user.is_arms_user=0 or user.id=".mi($sessioninfo['id']).")";
		}
		if($this->user_filter){
			$filter1 = "where ".$this->user_filter;
		}
		
		// user
		$con->sql_query("select distinct(mr.user_id) as user_id, user.u from membership_redemption mr left join user on mr.user_id = user.id $filter1");
		$smarty->assign("user", $con->sql_fetchrowset());

        // branches
        $con->sql_query("select * from branch order by sequence,code") or die(mysql_error());
        while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}
		// branch group
		// load header
		$con->sql_query("select * from branch_group");
		while($r = $con->sql_fetchrow()){
		    $branches_group['header'][$r['id']] =$r;
		}

		// load items
		$con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id
		order by branch.sequence, branch.code");
		while($r = $con->sql_fetchrow()){
	        $branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		
		$smarty->assign('branches_group',$branches_group);
		$smarty->assign('branches',$branches);
		$smarty->assign('status_list',$this->status_list);
		$smarty->assign('type_list',$this->type_list);
		
        parent::__construct($title);
	}
	
	function _default(){
		global $con, $smarty;
		
	    $this->display();
	}
	
	function show_summary($type='default'){
        global $con, $smarty, $sessioninfo;

		if($type=='export_excel'){
		    $export_excel = true;
            include_once("include/excelwriter.php");
		}
        
        $date_from = $_REQUEST['from'];
        $date_to = $_REQUEST['to'];
        $user_id = mi($_REQUEST['user_id']);
        $status_lbl = $_REQUEST['status'];
		$show_type = mi($_REQUEST['show_type']);
		if(!$show_type)	$show_type = 1;
		$filter = array();
		$report_title = array();
		
		$filter[] = "mr.date between ".ms($date_from)." and ".ms($date_to);
		$report_title[] = "Date: ".$date_from." to ".$date_to;
		
		if($user_id>0)    $filter[] = "mr.user_id=$user_id";
		if($user_id>0){
			$report_title[] = "User: ".get_user_info_by_colname($user_id, 'u');
		}else{
			$report_title[] = "User: All";
		}
		
		if($status_lbl) {
			$status = ($status_lbl=='complete') ? 0 : 1;
			$filter[] = "mr.status=$status";
		}
		if($status_lbl){
			$report_title[] = "Status: ".$this->status_list[$status_lbl];
		}else{
			$report_title[] = "Status: All";
		}
		$filter[] = "mr.active=1";
		
        $branch_id =get_request_branch(true);
        $branches_group = $smarty->get_template_vars('branches_group');
		if($branch_id){
			if($branch_id>0){   // single branch
				$filter[] = "mr.branch_id=$branch_id";
				$report_title[] = "Branch: ".get_branch_code($branch_id);
			}else{  // branch group
				$bgid = abs($branch_id);
				if(!$branches_group['items'][$bgid])    $err[] = "Invalid Branch ID";
				else{
				    $bid_list = array();
					foreach($branches_group['items'][$bgid] as $bid=>$b){
						$bid_list[] = $bid;
					}
					$filter[] = "mr.branch_id in (".join(',',$bid_list).")";
					$report_title[] = "Branch Group: ".$branches_group['header'][$bgid]['code'];
				}
			}
		}else{
			$report_title[] = "Branch: All";
		}
		$filter = "where ".join(' and ',$filter);
		$report_title[] = "Type: ".$this->type_list[$show_type];
		if($show_type == 2){	// details
			$extra_cols = ",mri.id as item_id,mri.pt_need,mri.cash_need,si.sku_item_code,si.mcode,si.artno, si.description";
			$extra_join = "left join sku_items si on si.id=mri.sku_item_id";
		}
		
		$sql = "select mr.*,user.u,u2.u as cancel_by_u, mri.qty as item_qty, mri.cost as item_cost $extra_cols
		from membership_redemption mr
        left join user on user.id=mr.user_id
		left join user u2 on u2.id=mr.cancel_by
		left join membership_redemption_items mri on mri.branch_id=mr.branch_id and mri.membership_redemption_id=mr.id
		$extra_join
		$filter";
		//print $sql;
		$total = array();
		$q1 = $con->sql_query($sql) or die(mysql_error());
		while($r = $con->sql_fetchassoc($q1)){
			$mr_key = $r['branch_id'].'_'.$r['id'];
			
			if(!isset($table[$mr_key])){
				$table[$mr_key] = $r;
				
				$total['total_qty'] += $r['total_qty'];
				$total['total_pt_need'] += $r['total_pt_need'];
				$total['total_cash_need'] += $r['total_cash_need'];
				
				if($show_type == 1){	// summary
					$total['print_count'] += $r['print_count'];
				}
				if($r['status']==1)	$total['cancel_count']++;
			}
			
			$item_cost = $r['item_qty']*$r['item_cost'];
			$table[$mr_key]['row_cost'] += $item_cost; 
			
			if($show_type == 2){	// details
				$table[$mr_key]['item_list'][$r['item_id']]['pt_need'] = $r['pt_need'];
				$table[$mr_key]['item_list'][$r['item_id']]['total_pt_need'] = $r['pt_need']*$r['item_qty'];
				$table[$mr_key]['item_list'][$r['item_id']]['cash_need'] = $r['cash_need'];
				$table[$mr_key]['item_list'][$r['item_id']]['total_cash_need'] = $r['cash_need']*$r['item_qty'];
				$table[$mr_key]['item_list'][$r['item_id']]['sku_item_code'] = $r['sku_item_code'];
				$table[$mr_key]['item_list'][$r['item_id']]['mcode'] = $r['mcode'];
				$table[$mr_key]['item_list'][$r['item_id']]['artno'] = $r['artno'];
				$table[$mr_key]['item_list'][$r['item_id']]['description'] = $r['description'];
				$table[$mr_key]['item_list'][$r['item_id']]['row_cost'] = $item_cost;
				$table[$mr_key]['item_list'][$r['item_id']]['item_qty'] = $r['item_qty'];
			}
		    
			$total['ttl_cost'] += $item_cost;
		}
		$con->sql_freeresult($q1);
		//print_r($table);
		$smarty->assign('table',$table);
		$smarty->assign('total',$total);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		
		if($export_excel){
			$smarty->assign("no_header_footer", 1);
			$tmpname = "Redemption_Summary_".time();
	        $output = "/tmp/".$tmpname.".xls";
			Header('Content-Type: application/msexcel');
			header("Content-Disposition: attachment; filename=$output");
            ExcelWriter::GetHeader();
			$smarty->display("membership.redemption_summary.tpl");
			ExcelWriter::GetFooter();
			log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export $this->title");
		}else{
			$this->display();
		}
	}

	function export_excel(){
		$this->show_summary('export_excel');
	}
}

$Membership_Redemption_Summary = new Membership_Redemption_Summary('Redemption Summary');
?>
