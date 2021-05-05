<?php
/*
6/22/2011 5:07:11 PM Justin
- Fixed the bugs by re-compile all the remarks structure.
- Changed report name.

6/24/2011 6:34:05 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:41:55 PM Andy
- Change split() to use explode()

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

4/19/2017 2:54 PM Justin
- Enhanced to have SKU items filter, system will now check S/N and SKU items filter both cannot be null at the same time.

2/21/2020 4:16 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");

include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");

if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));

$smarty->assign("sn_rows", 20);

class SN_STATUS_REPORT extends Module{
   function __construct($title){
		global $con, $smarty, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		$this->init_selection();

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
        global $con, $smarty,$sessioninfo, $con_multi;

		/*$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}*/

		$sql = "select pisnh.*, si.sku_item_code, si.description as si_description, b.code as current_branch, u.u as user
				from `pos_items_sn_history` pisnh
				left join branch b on b.id = pisnh.branch_id
				left join sku_items si on si.id = pisnh.sku_item_id
				left join user u on u.id = pisnh.user_id
				where ".join(" and ", $this->filters)." and pisnh.branch_id = ".mi($bid)."
				order by pisnh.serial_no, pisnh.added";

		$q1 = $con_multi->sql_query($sql);//print "$sql<br /><br />";//xx

		while($r = $con_multi->sql_fetchassoc($q1)){			
			if($r['status'] == "Sold"){
				$q2 = $con_multi->sql_query("select nric, name, address, contact_no, email, warranty_expired from sn_info where pos_id = ".mi($r['pos_id'])." and branch_id = ".mi($r['branch_id'])." and item_id = ".mi($r['item_id'])." and date = ".ms($r['date'])." and sku_item_id = ".mi($r['sku_item_id'])." and serial_no = ".ms($r['serial_no']));
				
				if($con_multi->sql_numrows($q2) > 0){
					$sn_info = $con_multi->sql_fetchassoc($q2);
					
					$r = array_merge($r, $sn_info);
				}
				$con_multi->sql_freeresult($q2);
			}
			$this->table[$r['id']] = $r;
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
		
		if($this->serial_no) $this->report_title[] = "Serial No: ".$this->serial_no;
		if($this->status) $status = $this->status;
		else $status = "All";

		$this->report_title[] = "Status: ".$status;
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		$smarty->assign('table', $this->table);
		$smarty->assign('category', $this->category);
	}
	
	function process_form(){
	    global $con, $smarty, $sessioninfo,$con_multi;

		$this->serial_no = $_REQUEST['serial_no'];
		$this->status = $_REQUEST['status'];
		$this->sku_code_list = $_REQUEST['sku_code_list_2'];

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

		if(!$this->serial_no && !$this->sku_code_list){
			$error[] = "Please assign Serial No or SKU item.";
			$smarty->assign("err", $error);
			$this->display();
			exit;
		}
		
		// construct filters
		$this->filters = array();		
		if($this->serial_no) $this->filters[] = "pisnh.serial_no = ".ms($this->serial_no);
		if($this->status) $this->filters[] = "pisnh.status = ".ms($this->status);
		$this->filters[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
		
		if($this->sku_code_list){
			/*$con_multi = new mysql_multi();
			if(!$con_multi){
				die("Error: Fail to connect report server");
			}*/
			
			$q1 = $con_multi->sql_query("select * from sku_items where sku_item_code in (".$this->sku_code_list.")");
			
			$si_id_list = array();
			while($r = $con_multi->sql_fetchassoc($q1)){
				$sku_info = array();
				$sku_info['sku_item_code'] = $r['sku_item_code'];
				$sku_info['description'] = $r['description'];
				$this->category[] = $sku_info;
				$si_id_list[] = $r['id'];
			}
			$con_multi->sql_freeresult($q1);
			//$con_multi->close_connection();
			
			$this->filters[] = "si.id in (".join(",", $si_id_list).")";
			unset($sku_info, $si_id_list);
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

$SN_STATUS_REPORT = new SN_STATUS_REPORT('Serial No Status Report');
