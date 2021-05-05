<?php
/*
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
$maintenance->check(130);

class RETURN_POLICY_ITEM_RETURN extends Module{
   function __construct($title){
		global $con, $smarty, $sessioninfo;

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
		$this->branches_group = $this->load_branch_group();

		$con->sql_query("select * from user where active=1 order by u");
		$smarty->assign("user_list", $con->sql_fetchrowset());

		$con->sql_query("select * from counter_settings where active=1 order by network_name");
		$smarty->assign("counter_list", $con->sql_fetchrowset());

		$transaction_status_list = array(2=>'Active',1=>"Cancelled");
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
        global $con, $smarty,$sessioninfo;

		$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}

		$sql = $con->sql_query("select pi.*, b.id as branch_id, b.code as branch_code, b.description as branch_desc,
								pi.sku_item_id, si.sku_item_code, si.description, si.mcode
								from pos p
								left join pos_items pi on pi.pos_id = p.id and pi.branch_id = p.branch_id and pi.date = p.date and pi.counter_id = p.counter_id
								left join sku_items si on si.id = pi.sku_item_id
								left join branch b on b.id = p.branch_id
								left join user c on c.id = p.cashier_id
								where pi.is_return_policy > 0 and pi.trade_in_by = 0 and p.branch_id = ".mi($bid)." and ".join(" and ", $this->filter)."
								order by p.pos_time");

		while($r = $con->sql_fetchassoc($sql)){
			if($this->view_type == 1) $key = $r['sku_item_id']; // by daily
			else $key = $r['date']; // by monthly

			if($r['more_info']) $r['more_info'] = unserialize($r['more_info']);
			else continue;

			if(count($r['more_info']['return_policy']['rp_member_type']) > 0){
				foreach($r['more_info']['return_policy']['rp_member_type'] as $mt=>$val){
					if($mt == "non_member"){
						if($r['more_info']['return_policy_detail'][$mt]){
							$r['more_info']['return_policy']['title'] = $r['more_info']['return_policy_detail'][$mt]['title'];
							break;
						}
					}else{
						if($r['more_info']['return_policy_detail'][$mt][$val]){
							$r['more_info']['return_policy']['title'] = $r['more_info']['return_policy_detail'][$mt][$val]['title'];
							break;
						}
					}
				}
			}
			
			// if user show report by monthly
			if($this->view_type == 2){
				$this->monthly_table[$r['branch_id']][$r['date']]['refund'] += $r['more_info']['return_policy']['refund'];
				$this->monthly_table[$r['branch_id']][$r['date']]['charges'] += $r['more_info']['return_policy']['charges'];
				$this->monthly_table[$r['branch_id']][$r['date']]['actual_refund'] += $r['more_info']['return_policy']['actual_refund'];
				$this->monthly_table[$r['branch_id']][$r['date']]['actual_charges'] += $r['more_info']['return_policy']['actual_charges'];
				$this->monthly_table[$r['branch_id']][$r['date']]['extra_charges'] += $r['more_info']['return_policy']['extra_charges'];
			}
			//$arr['mi']['return_policy']['actual_fund'] => charges
			$this->branch_list[$r['branch_id']]['branch_code'] = $r['branch_code'];
			$this->branch_list[$r['branch_id']]['description'] = $r['branch_desc'];
			$this->table[$r['branch_id']][$r['date']][] = $r;
		}
		
		$con_multi->sql_freeresult($q1);
		$con_multi->close_connection();
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
		global $con, $smarty;

		$this->table = array();
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$this->run_report($bid);
			}
		}

		// set report fixed row display
		$smarty->assign('report_row', 25);
		
		$this->report_title[] = "Date From ".$this->date_from." to ".$this->date_to;

		/*if($this->counter_id){
			$con->sql_query("select network_name from counter_settings where id = ".mi($this->counter_id));
			$ct_desc = $con->sql_fetchfield(0);
			$con->sql_freeresult();
		}else $ct_desc = "All";
		
		$this->report_title[] = "Counter: ".$ct_desc;*/
		
		if($this->cashier_id){
			$con->sql_query("select u from user where id = ".mi($this->cashier_id));
			$ch_desc = $con->sql_fetchfield(0);
			$con->sql_freeresult();
		}else $ch_desc = "All";
		
		$this->report_title[] = "Cashier: ".$ch_desc;

		if($this->view_type == 1) $view_desc = "Daily";
		else $view_desc = "Monthly";
		
		$this->report_title[] = "View By: ".$view_desc;
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		$smarty->assign('table', $this->table);
		$smarty->assign('monthly_table', $this->monthly_table);
		$smarty->assign('branch_list', $this->branch_list);
	}
	
	function process_form(){
	    global $con, $smarty;

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

		if($_REQUEST['view_type'] == 1) $date_type = "month";
		else $date_type = "year";

		// check if the date is more than 1 month/year
		$end_date =date("Y-m-d",strtotime("+1 $date_type",strtotime($_REQUEST['date_from'])));
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

		$this->filter[] = "p.date between ".ms($this->date_from)." and ".ms($this->date_to);
		//$this->filter[] = "pds.deposit_branch_id != pds.branch_id and pds.branch_id is not null and pds.branch_id != ''";
		$this->filter[] = "p.cancel_status = 0";

		if($this->counter_id) $this->filter[] = "p.counter_id = ".ms($this->counter_id);
		if($this->cashier_id) $this->filter[] = "p.cashier_id = ".ms($this->cashier_id);
		//parent::process_form();
	}

	function load_branch_group($id=0){
		global $con,$smarty;
	    if(isset($this->branch_group))  return $this->branch_group;
		$branch_group = array();
		
		// check whether select all or specified group
		if($id>0){
			$where = "where id=".mi($id);
			$where2 = "and bgi.branch_group_id=".mi($id);
		}
		// load header
		$con->sql_query("select * from branch_group $where",false,false);
		if($con->sql_numrows()<=0) return;
		while($r = $con->sql_fetchrow()){
            $branch_group['header'][$r['id']] = $r;
		}
		

		// load items
		$con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 $where2 order by branch.sequence, branch.code",false,false);
		while($r = $con->sql_fetchrow()){
	        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		
		$this->branch_group = $branch_group;
		//print_r($this->branch_group);
		$smarty->assign('branch_group',$branch_group);
		$smarty->assign('branches_group',$branch_group);
		return $branch_group;
	}
}

$RETURN_POLICY_ITEM_RETURN = new RETURN_POLICY_ITEM_RETURN('Return Policy Items Returned Report');
?>
