<?php
/*
11/28/2012 4:36 PM Andy
- Fix if no data should display a no data notice, instead of blank table.

12/4/2012 5:30 PM Andy
- Fix wrong cash amount.

12/19/2012 12:15 PM Andy
- Fix the report does not correctly show the payment type if the payment type has been adjust.
*/
include("../../include/common.php");
$maintenance->check(130);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_CUSTOM1')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_CUSTOM1', BRANCH_CODE), "/index.php");

class OUTLET_RECEIPTS_REPORT extends Module{
	var $default_allowed_payment_type_list = array('credit_card', 'check', 'voucher', 'coupon', 'debit');
	var $allowed_payment_type_list = array();
	var $branches_list = array();
	var $got_data = false;
	
	function __construct($title, $template=''){
		global $con, $sessioninfo, $pos_config, $smarty;
		
		// default value
		if(!isset($_REQUEST['date_from']))	$_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month", time()));	// default -1 month
		if(!isset($_REQUEST['date_to']))	$_REQUEST['date_to'] = date("Y-m-d");	// default today
	
		// construct allowe payment type	
		$this->allowed_payment_type_list = $this->default_allowed_payment_type_list;
		
		// branches list
		$con->sql_query("select id,code,description from branch where active=1 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branches_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches_list', $this->branches_list);
		
		//print_r($pos_config);
		
		$smarty->assign('allowed_payment_type_list', $this->allowed_payment_type_list);
		parent::__construct($title, $template);
	}
	
	 function _default(){
	 	global $con, $sessioninfo, $pos_config, $smarty, $con_multi;
	 	
	 	if($_REQUEST['load_report']){
	 		if($_REQUEST['output_excel']){
			    include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, $this->title);

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		 	$this->load_report();
		 }
	 	$this->display('mizisport/report.outlet_receipts_report.tpl');
	 }
	 
	 private function load_report(){
	 	global $con, $sessioninfo, $pos_config, $smarty, $con_multi;
	 	
	 	if(BRANCH_CODE == 'HQ'){
	 		$bid = mi($_REQUEST['branch_id']);
	 		if(!$bid)	$group_by_branch = mi($_REQUEST['group_by_branch']);
	 	}else{
	 		$bid = mi($sessioninfo['branch_id']);
	 	}
	 	$date_from = $_REQUEST['date_from'];
	 	$date_to = $_REQUEST['date_to'];
	 	
	 	$err = array();
	 	if(BRANCH_CODE != 'HQ' && !$bid)	$err[] = "Please select branch.";
	 	if(!$date_from || !$date_to)	$err[] = "Please select date from/to.";
	 	if(!$err && strtotime($date_from) > strtotime($date_to))	$err[] = "Date To cannot ealier than Date From.";
	 	
	 	if(!$err && strtotime($date_to) > strtotime("+1 month",strtotime($date_from)))	$date_to = $_REQUEST['date_to'] = date("Y-m-d", strtotime("+1 month",strtotime($date_from)));
	 	
	 	if($err){
	 		$smarty->assign('err', $err);
	 		return false;
	 	}
	 	
	 	if(!$con_multi){
			$con_multi= new mysql_multi();
		}
		
	 	$bid_list = array();
	 	if($bid)	$bid_list[] = $bid;
	 	else	$bid_list = array_keys($this->branches_list);	// use all branch id
	 	
	 	$this->data = array();
	 	
	 	//print_r($bid_list);
	 	if(!$group_by_branch){
	 		// pre-made date list
	 		$d = $date_from;
	 		$max_time = strtotime($date_to);
	 		
	 		while(strtotime($d) <= $max_time){
	 			$this->data['by_date'][$d] = array();
	 			$d = date("Y-m-d", strtotime("+1 day", strtotime($d)));
	 		}
	 	}
	 	
	 	$this->group_by_branch = $group_by_branch;
	 	$this->date_from = $date_from;
	 	$this->date_to = $date_to;
	 	
	 	
	 	foreach($bid_list as $tmp_bid){
	 		$this->get_data_by_branch($tmp_bid);
	 	}
	 	
	 	// clear data
	 	if(!$this->got_data)	$this->data = array();
	 	
	 	/*if($this->data['by_date']){
	 		uksort($this->data['by_date'], array($this, "sort_data_by_date"));
	 	}*/
	 	
	 	//print_r($this->data);
	 	
	 	$report_title = array();
	 	$report_title[] = "Branch: ".($bid ? get_branch_code($bid) : 'All');
	 	$report_title[] = "Date From $date_from to $date_to";
	 	if($this->group_by_branch)	$report_title[] = "Group By Branch: YES";
	 	
	 	$smarty->assign('data', $this->data);
	 	$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	 }
	 
	 private function get_data_by_branch($bid){
	 	global $con, $sessioninfo, $pos_config, $smarty, $con_multi;
	 		 
	 	$sql = "select pp.type, pp.amount,pp.date, p.amount_change
from pos_payment pp
join pos p on p.branch_id=pp.branch_id and p.counter_id=pp.counter_id and p.date=pp.date and p.id=pp.pos_id
where p.branch_id=$bid and p.date between ".ms($this->date_from)." and ".ms($this->date_to)." and p.cancel_status=0 and pp.adjust=0 and type not in ('Mix & Match Total Disc','Rounding','Discount')";
		
		$q1 = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q1)){
			$type = strtolower($r['type']);
			$date_key = $r['date'];
			$amt = $r['amount'];
			
			if($type == 'cash' || in_array($type, $this->allowed_payment_type_list)){	// cash, voucher, coupon, check, debit
				if($this->group_by_branch){	// group by branch
					// credit / debit total
					if($type == 'debit'){
						$this->data['by_branch'][$bid]['sales']['cc_db_total']['amt'] += $amt;
					}elseif($type == 'cash' || $type == 'check'){	// cash / check total
						if($type == 'cash'){
							$amt -= $r['amount_change'];	// cash need to deduct changed
						}
						$this->data['by_branch'][$bid]['sales']['cash_check_total']['amt'] += $amt;
						
						$this->data['total']['sales']['cash_check_total']['amt'] += $amt;
					}elseif($type =='voucher' || $type == 'coupon'){	// voucher / coupon total
						$this->data['by_branch'][$bid]['sales']['vc_cp_total']['amt'] += $amt;
						
						$this->data['total']['sales']['vc_cp_total']['amt'] += $amt;
					}
					
					$this->data['by_branch'][$bid]['sales'][$type]['amt'] += $amt;
				}else{	// no group by branch
					
					
					// credit / debit total
					if($type == 'debit'){
						$this->data['by_date'][$date_key]['sales']['cc_db_total']['amt'] += $amt;
					}elseif($type == 'cash' || $type == 'check'){	// cash / check total
						if($type == 'cash'){
							$amt -= $r['amount_change'];	// cash need to deduct changed
						}
						
						$this->data['by_date'][$date_key]['sales']['cash_check_total']['amt'] += $amt;
						
						$this->data['total']['sales']['cash_check_total']['amt'] += $amt;
					}elseif($type =='voucher' || $type == 'coupon'){	// voucher / coupon total
						$this->data['by_date'][$date_key]['sales']['vc_cp_total']['amt'] += $amt;
						
						$this->data['total']['sales']['vc_cp_total']['amt'] += $amt;
					}
					
					$this->data['by_date'][$date_key]['sales'][$type]['amt'] += $amt;
				}
				
				// total
				$this->data['total']['sales'][$type]['amt'] += $amt;
			}elseif(in_array($r['type'], $pos_config['credit_card'])){	// visa, master, etc...
				if($this->group_by_branch){	// group by branch
					$this->data['by_branch'][$bid]['sales']['cc'][$type]['amt'] += $amt;
					
					// credit / debit total
					$this->data['by_branch'][$bid]['sales']['cc_db_total']['amt'] += $amt;
				}else{	// no group by branch
					$this->data['by_date'][$date_key]['sales']['cc'][$type]['amt'] += $amt;
					
					// credit / debit total
					$this->data['by_date'][$date_key]['sales']['cc_db_total']['amt'] += $amt;
				}
				
				// total
				$this->data['total']['sales']['cc'][$type]['amt'] += $amt;
				$this->data['total']['sales']['cc_db_total']['amt'] += $amt;
			}else	continue;
			
			if($this->group_by_branch){
				// total by row
				$this->data['by_branch'][$bid]['sales']['total']['amt'] += $amt;
			}else{
				// total by row
				$this->data['by_date'][$date_key]['sales']['total']['amt'] += $amt;
			}
			
			// total
			$this->data['total']['sales']['total']['amt'] += $amt;
			
			$this->got_data = true;
		}
		$con_multi->sql_freeresult($q1);
	 }
	 
	 private function sort_data_by_date($key1, $key2){
	 	if($key1 == $key2)	return 0;
	 	return $key1 > $key2 ? 1 : -1;
	 }
}

$OUTLET_RECEIPTS_REPORT = new OUTLET_RECEIPTS_REPORT('Outlet Receipts Report');
?>
