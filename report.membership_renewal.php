<?php
/*
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class MEMBERSHIP_RENEWAL extends Module{
    function __construct($title){
		global $con, $smarty;

		if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
		if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
		
		// pre-load sales agent
		$con->sql_query("select * from sa order by code, name");
		$sa = $con->sql_fetchrowset();
		$con->sql_freeresult();
		$smarty->assign('sa', $sa);

		// load branches
		$con->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group
		$this->branches_group = $this->load_branch_group();
		
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

		if($this->filter) $filter = " and ".join(" and ", $this->filter);
		// check whether the S/A match all the conditions set from commission module
		// stock sold query
		$q1 = $con_multi->sql_query("select mh.nric, mh.card_no, concat(m.address, ', ', m.postcode) as address, m.dob,
									 mh.issue_date, mh.expiry_date
									 from membership_history mh
									 left join membership m on m.nric = mh.nric
									 where mh.expiry_date between ".ms($this->date_from)." and ".ms($this->date_to)." and m.apply_branch_id = ".mi($bid)." and mh.remark not in ('I', 'CB', 'T') $filter
									 group by mh.nric, mh.card_no
									 order by expiry_date, mh.nric, mh.card_no");

		while($r1 = $con_multi->sql_fetchassoc($q1)){
			$r1['dob'] = substr($r1['dob'], 6, 2)."/".substr($r1['dob'], 4, 2)."/".substr($r1['dob'], 0, 4);
			$q2 = $con_multi->sql_query("select count(distinct pi.sku_item_id) as trans_count, sum(p.point) as points, 
										 sum(pi.qty) as qty
										 from pos p
										 left join pos_items pi on pi.branch_id=p.branch_id and pi.counter_id = p.counter_id and pi.date = p.date and pi.pos_id = p.id
										 where p.cancel_status = 0 and p.branch_id in (".join(",",$this->sales_branch_id_list).") and p.member_no = ".ms($r1['card_no'])." and p.date between ".ms($r1['issue_date'])." and ".ms($r1['expiry_date'])."
										 group by p.member_no, pi.sku_item_id
										 having qty > 0");
			
			while($r2 = $con_multi->sql_fetchassoc($q2)){
				$r1['trans_count'] += $r2['trans_count'];
				$r1['points'] += $r2['points'];
			}
			$con_multi->sql_freeresult($q2);
			
			if(!$r1['trans_count']) continue;
			
			$this->table[] = $r1;
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
    	$filename = "sa_performance_".time().".xls";
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

		if($this->apply_branch_id_list){
			foreach($this->apply_branch_id_list as $bid){
				$this->run_report($bid);
			}
		}
		
		// filter off those card no not in transaction count
		if($this->item_count_from || $this->item_count_to){
			foreach($this->table as $row=>$r){
				if(($this->item_count_to && $this->item_count_from > $r['trans_count']) || ($this->item_count_to && $this->item_count_to < $r['trans_count'])) unset($this->table[$row]);
			}
		}
		
		$this->report_title[] = "Date From ".strtoupper($this->date_from)." to ".strtoupper($this->date_to);
		
		if(!$this->item_count_from && !$this->item_count_to) $ic_desc = "All";
		else{
			if($this->item_count_from && !$this->item_count_to) $ic_desc = "Start From ".$this->item_count_from;
			elseif(!$this->item_count_from && $this->item_count_to) $ic_desc = "At Most ".$this->item_count_to;
			else $ic_desc = "From ".$this->item_count_from." To ".$this->item_count_to;
		}
		$this->report_title[] = "Item Count: ".$ic_desc;
		if($this->card_no) $card_desc = $this->card_no;
		else $card_desc = "All";
		
		$this->report_title[] = "Member Card: ".$card_desc;	
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		//$smarty->assign('sac_table', $this->sac_table);
		$smarty->assign('table', $this->table);
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
		$end_date =date("Y-m-d",strtotime("+1 year",strtotime($_REQUEST['date_from'])));
    	if(strtotime($_REQUEST['date_to'])>strtotime($end_date)) $_REQUEST['date_to'] = $end_date;
		$date_from = date("Y-m", strtotime($_REQUEST['date_from']))."-01";
		$tmp_end_date = date("Y-m", strtotime($_REQUEST['date_to']))."-01";
		$date_to = date("Y-m-d", strtotime("-1 day", strtotime("+1 month", strtotime($tmp_end_date))));

		$this->date_from = $date_from;
		$this->date_to = $date_to;
		$this->item_count_from = $_REQUEST['item_count_from'];
		$this->item_count_to = $_REQUEST['item_count_to'];
		$this->card_no = $_REQUEST['card_no'];

		if(BRANCH_CODE == 'HQ'){    // HQ mode
			// apply branch filter
			$apply_branch_id = mi($_REQUEST['apply_branch_id']);
			$apply_bgid = explode(",",$_REQUEST['apply_branch_id']);
			if($apply_bgid[1] || $apply_branch_id<0){ // branch group selected
				if($this->branches_group){
					foreach($this->branches_group['items'][$apply_bgid[1]] as $bid=>$b){
						$this->apply_branch_id_list[] = $bid;
					}
				}
				$this->report_title[] = "Apply Branch Group: ".$this->branches_group['header'][$apply_bgid[1]]['code'];
			}elseif($apply_branch_id){  // single branch selected
			    $this->apply_branch_id_list[] = $apply_branch_id;
                $this->report_title[] = "Apply Branch: ".get_branch_code($apply_branch_id);
			}   
			else{   // all branches selected
				foreach($this->branches as $bid=>$b){
                    $this->apply_branch_id_list[] = $bid;
				}
				$this->report_title[] = "Apply Branch: All";
			}
			
			// sales branch filter
			$sales_branch_id = mi($_REQUEST['sales_branch_id']);
			$sales_bgid = explode(",",$_REQUEST['sales_branch_id']);
			if($sales_bgid[1] || $sales_branch_id<0){ // branch group selected
				if($this->branches_group){
					foreach($this->branches_group['items'][$sales_bgid[1]] as $bid=>$b){
						$this->sales_branch_id_list[] = $bid;
					}
				}
				$this->report_title[] = "Sales Branch Group: ".$this->branches_group['header'][$sales_bgid[1]]['code'];
			}elseif($sales_branch_id){  // single branch selected
			    $this->sales_branch_id_list[] = $sales_branch_id;
                $this->report_title[] = "Sales Branch: ".get_branch_code($sales_branch_id);
			}   
			else{   // all branches selected
				foreach($this->branches as $bid=>$b){
                    $this->sales_branch_id_list[] = $bid;
				}
				$this->report_title[] = "Sales Branch: All";
			}
		}else{  // Branches mode
            //$branch_id = mi($sessioninfo['branch_id']);
            $this->apply_branch_id_list[] = $this->sales_branch_id_list[] = mi($sessioninfo['branch_id']);
            $this->report_title[] = "Branch: ".BRANCH_CODE;
		}

		$this->filter = array();
		if($this->card_no) $this->filter[] = "mh.card_no like ".ms("%".$this->card_no."%");

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
	
	function ajax_show_member_details(){
		global $con, $smarty, $sessioninfo;

		$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}

		$form = $_REQUEST;
		if(BRANCH_CODE == 'HQ'){    // HQ mode
			// sales branch filter
			$sales_branch_id = mi($form['sales_branch_id']);
			$sales_bgid = explode(",",$form['sales_branch_id']);
			if($sales_bgid[1] || $sales_branch_id<0){ // branch group selected
				if($this->branches_group){
					foreach($this->branches_group['items'][$sales_bgid[1]] as $bid=>$b){
						$sales_branch_id_list[] = $bid;
					}
				}
			}elseif($sales_branch_id){  // single branch selected
			    $sales_branch_id_list[] = $sales_branch_id;
			}   
			else{   // all branches selected
				foreach($this->branches as $bid=>$b){
                    $sales_branch_id_list[] = $bid;
				}
			}
		}else{  // Branches mode
            $sales_branch_id_list[] = mi($sessioninfo['branch_id']);
		}

		// product history
		$q1 = $con_multi->sql_query("select si.sku_item_code, si.description, si.mcode,
									 sum(pi.qty) as qty, sum(pi.price) as price
									 from pos_items pi
									 left join pos p on p.branch_id=pi.branch_id and p.counter_id = pi.counter_id and p.date = pi.date and p.id = pi.pos_id
									 left join sku_items si on si.id = pi.sku_item_id
									 where p.cancel_status = 0 and p.branch_id in (".join(",",$sales_branch_id_list).") and p.member_no = ".ms($form['card_no'])." and p.date between ".ms($form['date_from'])." and ".ms($form['date_to'])."
									 group by p.member_no, pi.sku_item_id
									 having qty > 0
									 order by qty desc, price desc");
		 
		while($r1 = $con_multi->sql_fetchassoc($q1)){
			$table[] = $r1;
		}
		$con_multi->sql_freeresult($q1);
		
		$smarty->assign('table', $table);
		$smarty->assign('card_no', $form['card_no']);
		
		$smarty->display("report.membership_renewal.detail.tpl");
	}
}

$MEMBERSHIP_RENEWAL = new MEMBERSHIP_RENEWAL('Membership Renewal Report');
?>
