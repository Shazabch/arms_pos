<?php
/*
4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/21/2020 4:06 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");

include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");

if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));

$smarty->assign("sn_rows", 20);

class SN_RETURN_REPORT extends Module{
   function __construct($title){
		global $con, $smarty, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		$this->init_selection();
		if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
		if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');

    	parent::__construct($title);
    }
	
	function _default(){
		$this->display();
		exit;
	}
	
	private function init_selection(){
		global $con, $smarty, $con_multi;

		// load branches
		$con_multi->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group
		$this->branches_group = $this->load_branch_group();
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

		$filter = array();
		$filter[] = "pisnh.branch_id = ".mi($bid)." and pisnh.active=1";
		$filter[] = "pisnh.date between ".ms($this->date_from)." and ".ms($this->date_to);
		$filter[] = "pisnh.remark = 'Goods Returned from POS'";
		
		if($this->serial_no) $filter[] = "pisnh.serial_no = ".ms($this->serial_no);
		if($this->status) $filter[] = "pisnh.status = ".ms($this->status);
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';

		$sql = "select pisnh.*, si.sku_item_code, si.description as si_description, b.code as current_branch, u.u as user
				from `pos_items_sn_history` pisnh
				left join branch b on b.id = pisnh.branch_id
				left join sku_items si on si.id = pisnh.sku_item_id
				left join user u on u.id = pisnh.user_id
				where ".join(" and ", $filter)."
				order by pisnh.date desc, pisnh.sku_item_id asc, pisnh.serial_no asc";

		$q1 = $con_multi->sql_query($sql);//print "$sql<br /><br />";//xx

		while($r = $con_multi->sql_fetchassoc($q1)){
			//if($this->table[$r['sku_item_id']][$r['serial_no']]) continue;
		 
			//$this->table[$r['sku_item_id']][$r['serial_no']] = $r;
			$this->table[] = $r;
		}
		$con_multi->sql_freeresult($q1);
		//$con_multi->close_connection();
	}

	function output_excel(){
	    global $smarty, $sessioninfo;
		
		$this->process_form();
		$this->generate_report();

        include_once("include/excelwriter.php");
    	$smarty->assign('no_header_footer', true);
    	$filename = "sn_status_".time().".xls";
    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Serial No Status To Excel($filename)");
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
		
		$this->report_title[] = "Date From: ".$this->date_from." to ".$this->date_to;
		if($this->serial_no) $this->report_title[] = "Serial No: ".$this->serial_no;
		//if($this->status) $this->report_title[] = "Status: ".$this->status;
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		$smarty->assign('table', $this->table);
		$smarty->assign('category', $this->category);
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
		$this->date_from = $_REQUEST['date_from'];
		$this->date_to = $_REQUEST['date_to'];
		$this->serial_no = $_REQUEST['serial_no'];
		$this->status = $_REQUEST['status'];

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

		if($error){
			$smarty->assign("err", $error);
			$this->display();
			exit;
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
		$q1 = $con_multi->sql_query("select * from branch_group $where",false,false);
		
		if($con_multi->sql_numrows()<=0) return;
		
		while($r = $con_multi->sql_fetchassoc($q1)){
            $branch_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);
		

		// load items
		$q1 = $con_multi->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 $where2 order by branch.sequence, branch.code",false,false);
		
		while($r = $con_multi->sql_fetchassoc($q1)){
	        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con_multi->sql_freeresult($q1);
		
		$this->branch_group = $branch_group;
		$smarty->assign('branch_group',$branch_group);
		return $branch_group;
	}
}

$SN_RETURN_REPORT = new SN_RETURN_REPORT('Serial No Return Report');
