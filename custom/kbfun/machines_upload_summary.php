<?php
/*
10/2/2019 1:22 PM William
- Add new extra module "Machines Upload Summary".

10/3/2019 4:44 PM William
-Fixed bug show error message "Internal Server Error" when Export to Excel.
- Add and display title of search filter.
*/
include("../../include/common.php");
if(!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'],"/index.php");
if(!privilege('DO')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DO', BRANCH_CODE), "/index.php");
class Machines_Upload_Summary extends Module{
	function __construct($title){
		global $con, $smarty, $sessioninfo;

		if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
		if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
		
		$this->init_data();
		parent::__construct($title);
	}
	
	function init_data(){
		global $con, $smarty, $sessioninfo;
		
		//load branch list
		$branches = array();
		$con->sql_query("select * from branch where active=1 order by sequence,code");
		while ($r = $con->sql_fetchassoc()) $branches[$r['id']] = $r;
		$con->sql_freeresult();
		$smarty->assign('branches',$branches);
		
		//load user list
		if($sessioninfo['id'] != 1){
			$user_filter = "where (user.is_arms_user=0 or do.user_id=".mi($sessioninfo['id']).")";
		}
		$con->sql_query("select distinct(user.id) as id, user.u from do left join user on do.user_id = user.id $user_filter group by user.id");
		$smarty->assign("user", $con->sql_fetchrowset());
		$con->sql_freeresult();
	}
	
	function _default(){
		global $smarty;
		$smarty->display("kbfun/machines_upload_summary.tpl");
	}
	
	function generate_report(){
		global $con, $smarty, $sessioninfo;
		$form = $_REQUEST;
		$branch_id = mi($form['branch_id']);
		$from = $form['from'];
		$to = $form['to'];
		$status = $form['status'];
		$branch_id_from = mi($form['branch_id_from']);
		$user_id = $form['user_id'];
		
		$report_title = array();
		$report_title[] = "Date From: ".$from;
		$report_title[] = "To: ".$to;
		if($branch_id) { 
			$transfer_to = 'do.do_branch_id = '.$branch_id;
			$credit_sales_from = 'and do.branch_id = '.$branch_id;
			$report_title[] = "Filter by Branch (Machine): ".get_branch_code($branch_id);
		}else{
			$transfer_to = 'do.do_branch_id <> 0';
			$report_title[] = "Filter by Branch (Machine): All";
		}
		if($branch_id_from){
			$branch_from ='and do.branch_id = '.$branch_id_from;
			$report_title[] = "Filter by Transfer DO From (Agent): ".get_branch_code($branch_id_from);
		}else{
			$report_title[] = "Filter by Transfer DO From (Agent): All";
		}
		
		$filter = array();
		switch($status){
			case 0:
				$filter[] ="do.active=1 and do.status in (0,1)";
				$report_title[] = "Status: All";
				break;
			case 1:
				$filter[] ="do.active=1 and do.approved=0 and do.status in (0,1)";
				$report_title[] = "Status: Not Approved";
				break;
			case 2:
				$filter[] ="do.active=1 and do.checkout=0 and do.approved=1";
				$report_title[] = "Status: Approved";
				break;
			case 3:
				$filter[] ="do.active=1 and do.checkout=1 and do.approved=1";
				$report_title[] = "Status: Checkout";
				break;
		}
		$filter[] = "(do.do_date between ".ms($from)." and ".ms($to).")";
		if($user_id > 0 ){
			$filter[] = 'do.user_id = '.mi($user_id);
			$q_u = $con->sql_query("select u from user where id=".mi($user_id));
			$user_name = $con->sql_fetchrow($q_u);
			$con->sql_freeresult($q_u);
			$report_title[] = "By User: ".$user_name['u'];
		}else{
			$report_title[] = "By User: All";
		}
		if($filter){
			$filter = "where " . join(" and ",$filter);
		}
		// get transfer do
		$do_list = array();
		$q1 = $con->sql_query("select do.id, do.do_branch_id, do.branch_id as transfer_do_bid, do.relationship_guid, do.do_no, 
			do.do_type, b1.code as b_code ,b2.report_prefix as prefix from do 
			left join branch b1 on do.do_branch_id = b1.id 
			left join branch b2 on do.branch_id = b2.id
			$filter and $transfer_to  $branch_from and do.do_type='transfer'");
		while($r1 = $con->sql_fetchassoc($q1)){
			$do_list['transfer'][$r1['relationship_guid']][] = $r1;
		}
		$con->sql_freeresult($q1);
		
		// get credit sales do
		$q2 = $con->sql_query("select do.id as credit_sales_id, do.branch_id as credit_sales_bid, do.relationship_guid as cs_relationship_guid, do.do_no as credit_sales_do_no, 
		do.do_type, branch.code as credit_sales_bcode, branch.report_prefix as credit_sales_prefix from do 
		left join branch on do.branch_id=branch.id 
		$filter  $credit_sales_from and do.do_type='credit_sales' order by do.branch_id");
		while($r2 = $con->sql_fetchassoc($q2)){
			$do_list['credit_sales'][$r2['cs_relationship_guid']][] = $r2;
		}
		$con->sql_freeresult($q2);
		
		$table = array();   
		foreach($do_list as $do_type=>$data_list){
			foreach($data_list as $relationship_guid=>$values){
				foreach($values as $key=>$val){
					if($relationship_guid != ''){	// check if relationship_guid exist
						if($do_list['transfer'][$relationship_guid][$key]['relationship_guid'] == $do_list['credit_sales'][$relationship_guid][$key]['cs_relationship_guid']){
							$items['transfer_id'] = $do_list['transfer'][$relationship_guid][$key]['id'];
							$items['transfer_do_no'] = $do_list['transfer'][$relationship_guid][$key]['do_no'];
							$items['b_code'] = $do_list['transfer'][$relationship_guid][$key]['b_code'];
							$items['prefix'] = $do_list['transfer'][$relationship_guid][$key]['prefix'];
							$items['transfer_do_bid'] = $do_list['transfer'][$relationship_guid][$key]['transfer_do_bid'];
							$items['credit_sales_prefix'] = $do_list['credit_sales'][$relationship_guid][$key]['credit_sales_prefix'];
							$items['credit_sales_id'] = $do_list['credit_sales'][$relationship_guid][$key]['credit_sales_id'];
							$items['credit_sales_bid'] = $do_list['credit_sales'][$relationship_guid][$key]['credit_sales_bid'];
							$items['credit_sales_do_no'] = $do_list['credit_sales'][$relationship_guid][$key]['credit_sales_do_no'];
						}else{
							if($do_list['transfer'][$relationship_guid][$key]['b_code']){
								$items['b_code'] = $do_list['transfer'][$relationship_guid][$key]['b_code'];
								$items['prefix'] = $do_list['transfer'][$relationship_guid][$key]['prefix'];
							}else{
								$items['b_code'] = $do_list['credit_sales'][$relationship_guid][$key]['credit_sales_bcode'];
								$items['credit_sales_prefix'] = $do_list['credit_sales'][$relationship_guid][$key]['credit_sales_prefix'];
							}
							$items['transfer_id'] = $do_list['transfer'][$relationship_guid][$key]['id'];
							$items['transfer_do_no'] = $do_list['transfer'][$relationship_guid][$key]['do_no'];
							$items['transfer_do_bid'] = $do_list['transfer'][$relationship_guid][$key]['transfer_do_bid'];
							$items['credit_sales_id'] = $do_list['credit_sales'][$relationship_guid][$key]['credit_sales_id'];
							$items['credit_sales_bid'] = $do_list['credit_sales'][$relationship_guid][$key]['credit_sales_bid'];
							$items['credit_sales_do_no'] = $do_list['credit_sales'][$relationship_guid][$key]['credit_sales_do_no'];
						}
						if($items['b_code']) $table[] = $items;
						unset($do_list['transfer'][$relationship_guid][$key],$do_list['credit_sales'][$relationship_guid][$key]);
					}else{
						if($do_type == 'transfer'){
							$items['b_code'] = $val['b_code'];
							$items['prefix'] = $val['prefix'];
							$items['transfer_id'] = $val['id'];
							$items['transfer_do_bid'] = $val['transfer_do_bid'];
							$items['transfer_do_no'] = $val['do_no'];
							$items['credit_sales_prefix'] = '';
							$items['credit_sales_id'] = '';
							$items['credit_sales_bid'] = '';
							$items['credit_sales_do_no'] = '';
						}else{
							$items['b_code'] = $val['credit_sales_bcode'];
							$items['credit_sales_prefix'] = $val['credit_sales_prefix'];
							$items['credit_sales_id'] = $val['credit_sales_id'];
							$items['credit_sales_bid'] = $val['credit_sales_bid'];
							$items['credit_sales_do_no'] = $val['credit_sales_do_no'];
							$items['prefix'] = '';
							$items['transfer_id'] = '';
							$items['transfer_do_bid'] = '';
							$items['transfer_do_no'] = '';
						}
						$table[] = $items;
					}
				}
			}
		}
		//sort by Machine No branch_code
		$sort_col = array();
		foreach($table as $key => $row){
			$sort_col[$key] = $row['b_code'];
		}
		array_multisort($sort_col, SORT_ASC, $table);
		$smarty->assign("report_title",join("&nbsp;&nbsp;&nbsp;&nbsp;", $report_title));
		$smarty->assign("table",$table);
		$smarty->display('kbfun/machines_upload_summary.tpl');
	}
	
	function output_excel(){
		global $smarty, $sessioninfo;
		$smarty->assign('no_header_footer',true);
		include("../../include/excelwriter.php");
		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=machines_upload_summary'.time().'.xls');
		log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." To Excel");
		print ExcelWriter::GetHeader();
		$this->generate_report();
		print ExcelWriter::GetFooter();
		exit;
	}
}
$Machines_Upload_Summary = new Machines_Upload_Summary('Machines Upload Summary');
?>