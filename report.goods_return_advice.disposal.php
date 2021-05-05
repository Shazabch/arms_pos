<?php
/*
7/24/2012 11:30 AM Justin
- Added to pickup Account ID and Code.

4/22/2015 9:45 AM Justin
- Enhanced to have GST information.

11/30/2015 9:43 PM DingRen
- remove recalculate gst amount

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

10/23/2018 11:26 AM Justin
- Bug fixed on certain filters does not working properly.

5/16/2019 11:58 AM William
- Pickup report_prefix for enhance "GRA".

7/3/2019 11:03 AM William
- Change report title "Departmnet" wrong spelling to "Department".
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

class GRA_DISPOSAL extends Module{
    function __construct($title){
		global $con, $smarty;

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
		
		// load vendor
		$con->sql_query("select * from vendor where active=1 order by code, description");
		while($r = $con->sql_fetchassoc()){
			$this->vendor_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('vendor_list',$this->vendor_list);

		// load sku type
		$con->sql_query("select * from sku_type where active=1 order by code, description");
		while($r = $con->sql_fetchassoc()){
			$this->st_list[$r['code']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('st_list',$this->st_list);

		// load department
		$con->sql_query("select * from category where active=1 and level=2 order by code, description");
		while($r = $con->sql_fetchassoc()){
			$this->dept_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('dept_list',$this->dept_list);
		
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

		$date_from = $this->date_from;
		$date_to = $this->date_to;

		if($this->filter) $filter = " and ".join(" and ", $this->filter);

		// search for disposed GRA
		$q1 = $con_multi->sql_query("select gra.*, v.description as vd_desc, c.description as dept_desc, b.code as bcode,b.report_prefix,
									v.code as vendor_code, if(bv.account_id = '' or bv.account_id is null, v.account_id, bv.account_id) as account_id,
									sum(round(gi.qty*gi.cost*gi.gst_rate/100, 2)) as gst
									from gra
									left join gra_items gi on gi.gra_id = gra.id and gi.branch_id = gra.branch_id
								    left join branch b on b.id = gra.branch_id
								    left join vendor v on v.id = gra.vendor_id
									left join branch_vendor bv on bv.vendor_id = v.id and bv.branch_id = gra.branch_id
								    left join category c on c.id = gra.dept_id and c.level = 2
									where gra.branch_id = ".mi($bid)." and date_format(gra.return_timestamp, '%Y-%m-%d') between ".ms($date_from)." and ".ms($date_to)." and gra.status=0 and gra.returned=1 and gra.type = 'Disposal' $filter
									group by gra.id, gra.branch_id
									order by gra.return_timestamp desc");

		while($r = $con_multi->sql_fetchassoc($q1)){
			if($r['is_under_gst']) $this->is_under_gst = 1;
				
			// total amt include gst
			$row_gst_amt = round($r['amount'] + $r['gst'], 2);
			$r['gst_amt'] = $row_gst_amt;
		
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
		$this->is_under_gst = 0;
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$this->run_report($bid);
			}
		}

		// set report fixed row display
		$smarty->assign('report_row', 25);
		
		$this->report_title[] = "Date From ".strtoupper($this->date_from)." to ".strtoupper($this->date_to);
		
		// vendor title
		if(!$this->vendor_id) $vd_desc = "All";
		else{
			$vd_desc = $this->vendor_list[$this->vendor_id]['description'];
		}
		$this->report_title[] = "Vendor: ".$vd_desc;

		// sku type title
		if(!$this->sku_type) $st_desc = "All";
		else{
			$st_desc = $this->st_list[$this->sku_type]['description'];
		}
		$this->report_title[] = "SKU Type: ".$st_desc;

		// department title
		if(!$this->dept_id) $dept_desc = "All";
		else{
			$dept_desc = $this->dept_list[$this->dept_id]['description'];
		}
		$this->report_title[] = "Department: ".$dept_desc;
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		//$smarty->assign('sac_table', $this->sac_table);
		$smarty->assign('table', $this->table);
		$smarty->assign('is_under_gst', $this->is_under_gst);
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
		$this->vendor_id = $_REQUEST['vendor_id'];
		$this->sku_type = $_REQUEST['sku_type'];
		$this->department_id = $_REQUEST['department_id'];

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
		if($this->vendor_id) $this->filter[] = "gra.vendor_id = ".mi($this->vendor_id);
		if($this->sku_type) $this->filter[] = "gra.sku_type = ".ms($this->sku_type);
		if($this->department_id) $this->filter[] = "gra.dept_id = ".mi($this->department_id);

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
	
	function ajax_show_details(){
		global $con, $smarty;

		$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}

		$form = $_REQUEST;

		// search for disposed GRA items
		$q1 = $con_multi->sql_query("select gi.*, si.sku_item_code, si.mcode, si.artno, si.description,
									gra.is_under_gst
									from gra_items gi
									left join gra on gra.id = gi.gra_id and gra.branch_id = gi.branch_id
									left join sku_items si on si.id = gi.sku_item_id
									where gi.gra_id = ".mi($form['id'])." and gi.branch_id = ".mi($form['bid'])."
									order by gi.id");
									
		while($r = $con_multi->sql_fetchrow($q1)){

			// total amt include gst
			//$r['amount'] = round($r['qty'] * $r['cost'], 2);
			//$row_gst_amt = round($r['amount'] + $r['gst'], 2);
			//$r['gst_amt'] = $row_gst_amt;

			$table[] = $r;
		$smarty->assign('id', $form['id']);
		$smarty->assign('bid', $form['bid']);
		$smarty->assign('table', $table);
		$smarty->assign('is_under_gst', $form['is_under_gst']);
		}
		
		$smarty->display("report.goods_return_advice.disposal.detail.tpl");
	}
}

$GRA_DISPOSAL = new GRA_DISPOSAL('GRA Disposal Report');
?>
