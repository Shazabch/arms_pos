<?php
/*
1/17/2011 5:20:32 PM Alex
- change use report_server

6/21/2011 11:25:16 AM Andy
- Add auto reload vendor/brand, price type and department list base on user selection.

6/24/2011 5:56:22 PM Andy
- Make all branch default sort by sequence, code.
*/
include("include/common.php");
set_time_limit(0);
$maintenance->check(27);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");

class BRAND_VENDOR_DISCOUNT_TABLE extends Module{
    var $can_select_branch = false;
	var $branch_id_list = array();
	var $commission_tbl_id_list = array();
	var $price_type_list = array();
	
	function __construct($title, $template){
		global $con, $smarty, $sessioninfo, $config;
	    if(BRANCH_CODE=='HQ'){
	        $this->can_select_branch = true;
            $smarty->assign('can_select_branch', $this->can_select_branch);
            $this->branch_id_list = $_REQUEST['branch_id'];
		}else{
            $this->branch_id_list = array($sessioninfo['branch_id']);
		}
		
		if(isset($_REQUEST['commission_tbl_id']))	$this->commission_tbl_id_list = $_REQUEST['commission_tbl_id'];
		if(isset($_REQUEST['price_type']))	$this->price_type_list = $_REQUEST['price_type'];
		
		parent::__construct($title, $template);
	}
	
	function _default(){
	    global $con, $smarty;
	    
	    $this->init_load();
	    if(isset($_REQUEST['subm'])){
	        $this->generate_report();
            if(isset($_REQUEST['output_excel'])){
			    include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." To Excel");

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		}
		$this->display();
	}
	
	private function init_load(){
		global $con, $smarty, $sessioninfo;
		
		// load branch
		$con->sql_query("select * from branch where active=1 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);

		// load branch group header
		$con->sql_query("select * from branch_group",false,false);
		while($r = $con->sql_fetchassoc()){
            $this->branches_group['header'][$r['id']] = $r;
		}
		$con->sql_freeresult();

		if($this->branches_group){
            // load branch group items
			$con->sql_query("select bgi.*,branch.code,branch.description
			from branch_group_items bgi
			left join branch on bgi.branch_id=branch.id
			where branch.active=1
			order by branch.sequence, branch.code");
			while($r = $con->sql_fetchassoc()){
		        $this->branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
		        $this->branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
			}
			$con->sql_freeresult();
		}
		$smarty->assign('branches_group',$this->branches_group);
		
		// REPORT_TABLE_TYPE (should be only brand or vendor)
		$this->load_available_brand_vendor();
		
		// price type
		$this->load_available_price_type();
		
		// dept
		$this->load_available_dept();

	}
	
	private function load_available_brand_vendor(){
		global $con, $smarty;
		
		if(!$this->branch_id_list || !is_array($this->branch_id_list))	return array();
		
		$filter = array();
		$filter[] = "cms_tbl.branch_id in (".join(',', $this->branch_id_list).")";
		$filter[] = "vb.active=1";
		$filter = "where ".join(' and ', $filter);
		
		$sql = "select cms_tbl.".REPORT_TABLE_TYPE."_id as cms_id, vb.*
from ".REPORT_TABLE_TYPE."_commission cms_tbl 
left join ".REPORT_TABLE_TYPE." vb on vb.id=cms_tbl.".REPORT_TABLE_TYPE."_id
$filter 
group by cms_id
order by vb.description";
		//print $sql;exit;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$this->commission_tbl[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('commission_tbl', $this->commission_tbl);
	}
	
	private function load_available_price_type(){
		global $con, $smarty;
		
		if(!$this->branch_id_list || !is_array($this->branch_id_list))	return array();
		if(!$this->commission_tbl_id_list || !is_array($this->commission_tbl_id_list))	return array();
			
		$filter = array();
		$filter[] = "cms_tbl.branch_id in (".join(',', $this->branch_id_list).")";
		$filter[] = "cms_tbl.".REPORT_TABLE_TYPE."_id in (".join(',', $this->commission_tbl_id_list).")";
		$filter[] = "tdt.code is not null";
		$filter = "where ".join(' and ', $filter);
		
		$sql = "select cms_tbl.skutype_code, tdt.*
from ".REPORT_TABLE_TYPE."_commission cms_tbl 
left join  trade_discount_type tdt on tdt.code=cms_tbl.skutype_code
$filter
group by skutype_code
order by skutype_code";
		//print $sql;exit;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$this->price_type[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('price_type', $this->price_type);
	}
	
	private function load_available_dept(){
		global $con, $smarty, $sessioninfo;	
		
		if(!$sessioninfo['department_ids'])	return array();
		if(!$this->branch_id_list || !is_array($this->branch_id_list))	return array();
		if(!$this->commission_tbl_id_list || !is_array($this->commission_tbl_id_list))	return array();
		if(!$this->price_type_list || !is_array($this->price_type_list))	return array();
		
		$tmp_pt = array();
		foreach($this->price_type_list as $pt){
			$tmp_pt[] = ms($pt);
		}
		
		$filter = array();
		$filter[] = "cms_tbl.branch_id in (".join(',', $this->branch_id_list).")";
		$filter[] = "cms_tbl.".REPORT_TABLE_TYPE."_id in (".join(',', $this->commission_tbl_id_list).")";
		$filter[] = "cms_tbl.skutype_code in (".join(',', $tmp_pt).")";
		$filter[] = "c.active=1 and c.level=2";
		$filter[] = "c.id in ($sessioninfo[department_ids])";
		$filter = "where ".join(' and ', $filter);
		
		$sql = "select cms_tbl.department_id, c.*
from ".REPORT_TABLE_TYPE."_commission cms_tbl 
left join category c on c.id=cms_tbl.department_id
$filter
group by cms_tbl.department_id
order by c.description";
		//print $sql;exit;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$this->depts[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('depts', $this->depts);
	}
	
	private function generate_report(){
		global $con, $smarty;
		
		//print_r($_REQUEST);
		
		$commission_tbl_id_list = $_REQUEST['commission_tbl_id'];
		$price_type_list = $_REQUEST['price_type'];
		$dept_id_list = $_REQUEST['dept_id'];
		
		if(!$this->branch_id_list)  $err[] = "Please select at least one branch.";
		if(!$commission_tbl_id_list)    $err[] = "Please select at least one ".REPORT_TABLE_TYPE;
		if(!$price_type_list)   $err[] = "Please select at least one price type";
		if(!$dept_id_list)  $err[] = "Please select at least one department";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$filter = array();
		$filter[] = "cms_tbl.branch_id in (".join(',', $this->branch_id_list).")";
		$filter[] = "cms_tbl.department_id in (".join(',', $dept_id_list).")";
		$filter[] = "cms_tbl.".REPORT_TABLE_TYPE."_id in (".join(',', $commission_tbl_id_list).")";
		
		$temp = array();
		foreach($price_type_list as $pt){
			$temp[] = ms($pt);
		}
		$filter[] = "cms_tbl.skutype_code in (".join(',', $temp).")";
		unset($temp);
		
		$filter = "where ".join(' and ', $filter);
		
		$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}

		$sql = "select cms_tbl.*,cms_tbl.".REPORT_TABLE_TYPE."_id as cms_tbl_id from ".REPORT_TABLE_TYPE."_commission cms_tbl
		$filter";
		//print $sql;
		$con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchrow()){
			$this->data[$r['branch_id']][$r['department_id']][$r['skutype_code']][$r['cms_tbl_id']] = mf($r['rate']);
		}
		$con_multi->sql_freeresult();
		
		//print_r($this->data);
		$smarty->assign('branch_id_list', $this->branch_id_list);
		$smarty->assign('branch_count', count($this->branch_id_list));
		$smarty->assign('commission_tbl_id_list', $commission_tbl_id_list);
		$smarty->assign('price_type_list', $price_type_list);
		$smarty->assign('dept_id_list', $dept_id_list);
		$smarty->assign('data', $this->data);
		$con_multi->close_connection();
	}
	
	function ajax_reload_available_sel(){
		global $con, $smarty;
		
		$sel_type = trim($_REQUEST['sel_type']);
		
		switch($sel_type){
			case 'vb':
				$this->load_available_brand_vendor();
				$ret['html'] = $smarty->fetch('report.brand_vendor_discount_table.brand_vendor_sel.tpl');
				break;
			case 'price_type':
				$this->load_available_price_type();
				$ret['html'] = $smarty->fetch('report.brand_vendor_discount_table.price_type_sel.tpl');
				break;
			case 'dept':
				$this->load_available_dept();
				$ret['html'] = $smarty->fetch('report.brand_vendor_discount_table.dept_sel.tpl');
			default:
				$ret['failed_reason'] = 'Invalid Selection Type';
				break;
		}
		
		$ret['ok'] = true;
		print json_encode($ret);
	}
}

$smarty->assign('REPORT_TABLE_TYPE', REPORT_TABLE_TYPE);
$BRAND_VENDOR_DISCOUNT_TABLE = new BRAND_VENDOR_DISCOUNT_TABLE(ucwords(REPORT_TABLE_TYPE).' Discount Table', 'report.brand_vendor_discount_table.tpl');
?>
