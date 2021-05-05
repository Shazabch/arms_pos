<?php
/*
8/26/2013 10:51 AM Andy
- Enhance report sql filter.
- Fix Deposit Report cannot show data in sub branch.

2/24/2020 5:55 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
$maintenance->check(209);

class CROSS_BRANCH_DEPOSIT extends Module{
   function __construct($title){
		global $con, $smarty, $sessioninfo, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
		if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
		
		// load branches
		$con_multi->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group
		$this->branches_group = $this->load_branch_group();

		$con_multi->sql_query("select * from user where active=1 order by u");
		$smarty->assign("user_list", $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();

		$con_multi->sql_query("select * from counter_settings where active=1 order by network_name");
		$smarty->assign("counter_list", $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();

		$transaction_status_list = array(1=>'Active',2=>"Cancelled");
		$smarty->assign('transaction_status', $transaction_status_list);
		
    	parent::__construct($title);
    }
	
	function _default(){
		$this->display();
		exit;
	}

	function show_report(){
		$this->process_form();
		$this->generate_report();
		$this->display();
	}

	private function run_report($bid){
        global $con, $smarty,$sessioninfo,$con_multi;

		/*$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}*/

		$filter = $all_sql = "";
		$sql = $sa_invalid_range = array();

		// join all the filters
		//if($this->filter) $filter = " and ".join(" and ", $this->filter);
		
		if($this->view_type == 'ap') $b_filter = "pds.deposit_branch_id = ".mi($bid);
		else $b_filter = "pds.branch_id = ".mi($bid);

		$sql = $con_multi->sql_query("select *, rcv_b.code as rcv_branch_code, rcv_b.description as rcv_branch_desc, 
								used_b.code as used_branch_code, used_b.description as used_branch_desc, c.u as cashier_name, 
								ab.u as approved_name, pd.item_list, pds.status, pds.deposit_branch_id as 
								rcv_branch_id, pds.branch_id used_branch_id, pd.cashier_id,
								pds.branch_id as to_branch_id, pd.counter_id, pd.receipt_no, pd.pos_id, pd.date,
								if(".ms($this->view_type)." = 'ap', used_b.code, rcv_b.code) owe_branch_code,
								if(".ms($this->view_type)." = 'ap', used_b.description, rcv_b.description) owe_branch_desc
								from pos_deposit pd
								left join pos_deposit_status pds on pds.deposit_branch_id = pd.branch_id and pds.deposit_counter_id = pd.counter_id and pds.deposit_date = pd.date and pds.deposit_pos_id = pd.pos_id
								left join pos p on p.id = pd.pos_id and p.branch_id = pd.branch_id and p.date = pd.date and p.counter_id = pd.counter_id
								left join branch rcv_b on rcv_b.id = pds.deposit_branch_id
								left join branch used_b on used_b.id = pds.branch_id
								left join user c on c.id = pd.cashier_id
								left join user ab on ab.id = pd.approved_by
								where $b_filter and ".join(" and ", $this->filter)."
								order by pd.pos_time");

		while($r = $con_multi->sql_fetchassoc($sql)){
			$item_list = unserialize($r['item_list']);
			if($item_list) $r['have_item_list'] = 1;
		
			if($this->view_type == 'ar'){
				$this->branch_list[$r['used_branch_id']]['branch_code'] = $r['used_branch_code'];
				$this->branch_list[$r['used_branch_id']]['description'] = $r['used_branch_desc'];
				$this->table[$r['used_branch_id']][] = $r;
			}else{
				$this->branch_list[$r['rcv_branch_id']]['branch_code'] = $r['rcv_branch_code'];
				$this->branch_list[$r['rcv_branch_id']]['description'] = $r['rcv_branch_desc'];
				$this->table[$r['rcv_branch_id']][] = $r;
			}
		}
		
		$con_multi->sql_freeresult($sql);
		//$con_multi->close_connection();
	}

	function output_excel(){
	    global $smarty, $sessioninfo;
		
		$this->process_form();
		$this->generate_report();

        include_once("include/excelwriter.php");
    	$smarty->assign('no_header_footer', true);
    	$filename = "sac_calculation_".time().".xls";
    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Voucher Activation Report To Excel($filename)");
    	Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename='.$filename);

		print ExcelWriter::GetHeader();
		$this->display();
		print ExcelWriter::GetFooter();
	    exit;
	}

    function generate_report(){
		global $con, $smarty, $con_multi;

		$this->table = array();
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$this->run_report($bid);
			}
		}

		// set report fixed row display
		$smarty->assign('report_row', 25);
		
		$this->report_title[] = "Date From ".$this->date_from." to ".$this->date_to;

		if($this->view_type == "ar") $view_type = "Receivable";
		else $view_type = "Payable";
		
		$this->report_title[] = "Account: ".strtoupper($view_type);

		/*if($this->counter_id){
			$con->sql_query("select network_name from counter_settings where id = ".mi($this->counter_id));
			$ct_desc = $con->sql_fetchfield(0);
			$con->sql_freeresult();
		}else $ct_desc = "All";
		
		$this->report_title[] = "Counter: ".$ct_desc;*/
		
		if($this->cashier_id){
			$con_multi->sql_query("select u from user where id = ".mi($this->cashier_id));
			$ch_desc = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
		}else $ch_desc = "All";
		
		$this->report_title[] = "Cashier: ".$ch_desc;

		if($this->trans_status == 1) $trans_desc = "Active";
		elseif($this->trans_status == 2) $trans_desc = "Cancelled";
		else $trans_desc = "All";
		
		$this->report_title[] = "Status: ".$trans_desc;
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		$smarty->assign('table', $this->table);
		$smarty->assign('branch_list', $this->branch_list);
	}
	
	function process_form(){
	    global $con, $smarty, $sessioninfo;

		if(!$_REQUEST['date_from']){
			if($_REQUEST['date_to']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month",strtotime($_REQUEST['date_to'])));
			else{
				$_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
				$_REQUEST['date_to'] = date('Y-m-d');
			}
		}
		if(!$_REQUEST['date_to'] || strtotime($_REQUEST['date_from']) > strtotime($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date('Y-m-d', strtotime("+1 month", strtotime($_REQUEST['date_from'])));
		}

		// check if the date is more than 1 month
		$end_date =date("Y-m-d",strtotime("+1 month",strtotime($_REQUEST['date_from'])));
    	if(strtotime($_REQUEST['date_to'])>strtotime($end_date)) $_REQUEST['date_to'] = $end_date;
		
		$this->date_from = $_REQUEST['date_from'];
		$this->date_to = $_REQUEST['date_to'];
		$this->view_type = $_REQUEST['view_type'];
		$this->counter_id = $_REQUEST['counter_id'];
		$this->cashier_id = $_REQUEST['cashier_id'];
		$this->tran_status = $_REQUEST['tran_status'];
		
		if(BRANCH_CODE == 'HQ'){    // HQ mode
			$branch_id = mi($_REQUEST['branch_id']);
			$bgid = explode(",",$_REQUEST['branch_id']);
			if($bgid[1] || $branch_id<0){ // branch group selected
				if($this->branches_group){
					foreach($this->branches_group['items'][$bgid[1]] as $bid=>$b){
						$this->branch_id_list[] = $bid;
					}
				}
				$this->report_title[] = "Branch Group: ".$this->branches_group['header'][$bgid[1]]['code'];
			}elseif($branch_id){  // single branch selected
			    $this->branch_id_list[] = $branch_id;
                $this->report_title[] = "Branch: ".get_branch_code($branch_id);
			}   
			else{   // all branches selected
				foreach($this->branches as $bid=>$b){
                    $this->branch_id_list[] = $bid;
				}
				$this->report_title[] = "Branch: All";
			}
		}else{  // Branches mode
            //$branch_id = mi($sessioninfo['branch_id']);
            $this->branch_id_list[] = mi($sessioninfo['branch_id']);
            $this->report_title[] = "Branch: ".BRANCH_CODE;
		}

		$this->filter = array();

		$this->filter[] = "pd.date between ".ms($this->date_from)." and ".ms($this->date_to);
		$this->filter[] = "pds.deposit_branch_id != pds.branch_id and pds.branch_id<>0";
		$this->filter[] = "pds.status > 0";

		if($this->counter_id) $this->filter[] = "pd.counter_id = ".ms($this->counter_id);
		if($this->cashier_id) $this->filter[] = "pd.cashier_id = ".ms($this->cashier_id);
		if($this->tran_status){
			$status = $this->tran_status - 1;
			$this->filter[] = "p.cancel_status = ".mi($status);
		}
		//parent::process_form();
	}

	function load_branch_group($id=0){
		global $con,$smarty,$con_multi;
	    if(isset($this->branch_group))  return $this->branch_group;
		$branch_group = array();
		
		// check whether select all or specified group
		if($id>0){
			$where = "where id=".mi($id);
			$where2 = "and bgi.branch_group_id=".mi($id);
		}
		// load header
		$con_multi->sql_query("select * from branch_group $where",false,false);
		if($con_multi->sql_numrows()<=0) return;
		while($r = $con_multi->sql_fetchrow()){
            $branch_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();

		// load items
		$con_multi->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 $where2 order by branch.sequence, branch.code",false,false);
		while($r = $con_multi->sql_fetchrow()){
	        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con_multi->sql_freeresult();
		
		$this->branch_group = $branch_group;
		//print_r($this->branch_group);
		$smarty->assign('branch_group',$branch_group);
		$smarty->assign('branches_group',$branch_group);
		return $branch_group;
	}
}

$CROSS_BRANCH_DEPOSIT = new CROSS_BRANCH_DEPOSIT('Cross Branch Deposit Report');
?>
