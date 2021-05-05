<?php
/*
12/7/2016 11:18 AM Andy
- Fixed export excel recorded wrong log information.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class MEMBER_POINTS_DETAIL extends Module{
    function __construct($title){
		global $con, $smarty, $config;

		$this->type_list = array('POS'=>'POS','REDEEM'=>'Redemption','ADJUST'=>'Adjustment','CANCELED'=>'Cancellation');
		
		if($config['enable_voucher_auto_redemption']) $this->type_list['AUTO_REDEEM'] = 'Auto Redemption';
		if($config['membership_enable_points_entry']) $this->type_list['ENTRY'] = 'Points Entry';
		
		$smarty->assign("type", $this->type_list);

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
		
		$q1 = $con_multi->sql_query("select mp.*, date(mp.date) as points_date, m.name, b.code as branch_code, 
									 date(m.issue_date) as issue_date, date(m.next_expiry_date) as expiry_date,
									 u.u as cashier_name
									 from membership_points mp
									 left join membership m on m.nric = mp.nric
									 left join branch b on b.id = mp.branch_id
									 left join user u on u.id = mp.user_id
									 where mp.branch_id = ".mi($bid).$filter."
									 order by mp.date");

		while($r = $con_multi->sql_fetchassoc($q1)){
			$this->table[] = $r;
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
    	$filename = "member_points_details_".time().".xls";
    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." To Excel($filename)");
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
		
		$this->report_title[] = "Date From ".strtoupper($this->date_from)." to ".strtoupper($this->date_to);

		if($this->type) $this->report_title[] = "Type: ".$this->type_list[$this->type];
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
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

		$this->date_from = $_REQUEST['date_from'];
		$this->date_to = $_REQUEST['date_to'];
		$this->type = $_REQUEST['type'];

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
            $this->branch_id_list[] = mi($sessioninfo['branch_id']);
            $this->report_title[] = "Branch: ".BRANCH_CODE;
		}

		$this->filter = array();
		$this->filter[] = "date(mp.date) between ".ms($this->date_from)." and ".ms($this->date_to);
		
		if($this->type) $this->filter[] = "mp.type = ".ms($this->type);
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
		$con->sql_freeresult();		

		// load items
		$con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 $where2 order by branch.sequence, branch.code",false,false);
		while($r = $con->sql_fetchassoc()){
	        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con->sql_freeresult();
		
		$this->branch_group = $branch_group;

		$smarty->assign('branch_group',$branch_group);
		$smarty->assign('branches_group',$branch_group);
		return $branch_group;
	}
}

$MEMBER_POINTS_DETAIL = new MEMBER_POINTS_DETAIL('Membership Points Detail Report');
?>
