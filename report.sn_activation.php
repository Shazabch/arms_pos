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

2/21/2020 3:50 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");

include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");
if (!$config['enable_sn_bn']) js_redirect($LANG['REPORT_CONFIG_NOT_FOUND'], "/index.php");

if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));

//show vendor option
if ($sessioninfo['vendors']) $vd = "and id in (".join(",",array_keys($sessioninfo['vendors'])).")";
$con_multi->sql_query("select id, description from vendor where active $vd order by description");
$smarty->assign("vendor", $con_multi->sql_fetchrowset());
$con_multi->sql_freeresult();

//show owner option
$con_multi->sql_query("select distinct(user.id) as id, user.u from po left join user on user_id = user.id group by id");
$smarty->assign("user", $con_multi->sql_fetchrowset());
$con_multi->sql_freeresult();

$smarty->assign("sn_rows", 20);

class SN_ACTIVATION_REPORT extends Report{
	private function run_report($bid){
        global $con, $smarty, $sessioninfo, $con_multi;
		//$bid = $this->bid;        
		$table = array();
        $date_from = $this->date_from;
		$date_to = $this->date_to;
		
		if($bid) $filter[] = "sni.branch_id in (".$bid.")";
		$filter[] = "sni.date between ".ms($date_from)." and ".ms($date_to);
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
		
		// get all S/N info base on filter options
		$sql = $con_multi->sql_query($abc="select si.id, si.sku_item_code, si.description, si.mcode, si.artno, branch.code as branch_code,
								cs.network_name as counter_name, sni.branch_id, sni.date, sni.nric, sni.name, sni.address,
								sni.contact_no, sni.warranty_expired, sni.serial_no, sni.active, user.u as approved_by
								from sn_info sni
								left join sku_items si on sni.sku_item_id = si.id
								left join branch on sni.branch_id = branch.id
								left join counter_settings cs on sni.counter_id = cs.id and sni.branch_id = cs.branch_id
								left join user on sni.approved_by = user.id
								where ".join(" and ", $filter)."
								order by si.sku_item_code, sni.serial_no, branch.code");//print "$abc<br /><br />";//xx

		// setup and to be display on the templates
		while($r = $con_multi->sql_fetchrow($sql)){
			$sku = array();
			$$tmp_data = array();
			$remarks = array();
			$key = $r['id'];
			$sku['sku_item_code'] = $r['sku_item_code'];
			$sku['description'] = $r['description'];
			$sku['mcode'] = $r['mcode'];
			$sku['artno'] = $r['artno'];
		
			// check SN that having problem
			$sql1 = $con_multi->sql_query("select * from pos_items_sn where serial_no = ".ms($r['serial_no'])." and branch_id = ".mi($r['branch_id'])." and sku_item_id = ".mi($r['id']));

			if($con_multi->sql_numrows($sql1) == 0) $remarks[] = "S/N Not Found";

			// check for duplicated sold SN items 
			if($r['branch_id'] == $tmp_bid && $r['sku_item_id'] == $tmp_sid && $r['serial_no'] == $tmp_sn && $r['active'] == $tmp_active && $r['active'] == 1){
				$remarks[] = "Multi Sold"; // set the current serial no remark
				$tmp_key = count($this->table[$key])-1;
				$this->table[$key][$tmp_key]['remark'] = join(", ", $remarks); // combine remark to previous record
			}
			$r['remark'] = join(", ", $remarks);
			$tmp_bid = $r['branch_id'];
			$tmp_sid = $r['sku_item_id'];
			$tmp_sn = $r['serial_no'];
			$tmp_active = $r['active'];
		
			$this->sku[$key] = $sku;
			$this->table[$key][] = $r;
		}
		
		$con_multi->sql_freeresult($sql);
	}
	
    function generate_report(){
		global $con, $smarty, $con_multi;

		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			$con_multi->sql_query("select branch_group_items.branch_id, branch_group.code 
							 from branch_group 
							 join branch_group_items on branch_group.id = branch_group_items.branch_group_id 
							 where id = $bg_id");

			while($bg = $con_multi->sql_fetchrow()){
				$bid[] = $bg['branch_id'];
				$bg_code = $bg['code'];
			}
			$con_multi->sql_freeresult();

			$report_title[] = "Branch Group: ".$bg_code;
			$this->run_report(join(",",$bid));
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){	// is a particular branch
	            $this->run_report($bid);
	            $branch_code = BRANCH_CODE;
			}else{	// from HQ user
				if($bid==0){	// is all the branches
	                $report_title[] = 'Branch: All';
					$this->run_report('');
				}else{	// is a particular branch
		            $this->run_report($bid);
					$branch_code = get_branch_code($bid);
					$report_title[] = "Branch: ".$branch_code;
				}
			}
		}

		
        $report_title[] = "Date: ".$this->date_from." to ".$this->date_to;

		//$smarty->assign('date_from', $this->date_from);
		//$smarty->assign('date_to', $this->date_to);
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));

		// set both 2 dates to store into the hidden field on template
		if (isset($_REQUEST['output_excel'])){
			$smarty->assign("print_excel", '1');
		}

		$smarty->assign('sku', $this->sku);
		$smarty->assign('table', $this->table);
	}
	
	function process_form(){
	    global $con, $smarty;
		
		parent::process_form();

        $this->bid = $_REQUEST['branch_id'];
        $this->date_from = $_REQUEST['date_from'];
        $end_date =date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($this->date_from))));
        if(strtotime($_REQUEST['date_to'])>strtotime($end_date)) $_REQUEST['date_to'] = $end_date;
        if(strtotime($_REQUEST['date_from'])>strtotime($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($this->date_from))));
		}
        $this->date_to = $_REQUEST['date_to'];
	}
}

	// set get all branches if branch code is from HQ or empty
	function get_all_branch(){
		global $con, $con_multi;

		$get_all_b = 'select group_concat(id order by id) as branch_id from branch where active = 1 order by sequence,code';
		$all_b = $con_multi->sql_query($get_all_b);
		
		while($branches = $con_multi->sql_fetchrow($all_b)){
			$bid = $branches['branch_id'];
		}
		$con_multi->sql_freeresult($all_b);
		return $bid;
	}
	
	function random_color($str){
		return '#'.substr(md5($str),0,6);
	}

$SN_ACTIVATION_REPORT = new SN_ACTIVATION_REPORT('Serial No Activation Report');
