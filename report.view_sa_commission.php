<?php
/*
2/20/2012 12:04:11 PM Justin
- Fixed the bugs where causing branch group not functional.

3/21/2012 11:52:43 AM Justin
- Fixed the bugs that branch ID get zero when logged on as sub branch.
*/

include("include/common.php");
include("include/class.report.php");
include("masterfile_sa_commission.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class VIEW_SA_COMMISSION extends Module{
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
		
		// pre-load sku type
		$con->sql_query("select * from sku_type where active=1 order by code");
		$sku_type_list = $con->sql_fetchrowset();
		$con->sql_freeresult();
		$smarty->assign('sku_type_list', $sku_type_list);
		
		// pre-load price type
		$con->sql_query("select * from trade_discount_type order by code");
		while($r = $con->sql_fetchassoc()){
			$price_type_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('price_type_list', $price_type_list);

		// pre-load brands
		$con->sql_query("select id,description from brand order by description");
		$brand_list = $con->sql_fetchrowset();
		$con->sql_freeresult();
		$smarty->assign('brand_list', $brand_list);

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

		$filter = array();
		$filter[] = "sac.branch_id = ".mi($bid);
		$filter[] = "saci.date_from >= ".ms($this->date_from)." and (saci.date_to <= ".ms($this->date_to)." or saci.date_to is null)";
		
		if($this->agent_id){
			$filter[] = "sa.id = ".mi($this->agent_id);
			$left_join = "left join sa on sa.sac_id = sac.id";
		}

		$sql = "select *, saci.id as saci_id
				from `sa_commission` sac
				left join `sa_commission_items` saci on saci.sac_id = sac.id and saci.branch_id = sac.branch_id
				$left_join
				where ".join(" and ", $filter)."
				order by saci.id asc, saci.date_from asc";

		$sac = $con_multi->sql_query($sql);

		while($r = $con_multi->sql_fetchrow($sac)){
			$item_info = $conditions = array();
			$r['conditions'] = unserialize($r['conditions']);
			if($this->filter_by){
				if($this->si_list){
					if(!$this->si_list[$r['conditions']['sku_item_id']]) continue;
				}else{
					if($this->category_id && $this->category_id != $r['conditions']['category_id']) continue;
					if($this->brand_id && $this->brand_id != $r['conditions']['brand_id']) continue;
					if($this->sku_type && $this->sku_type != $r['conditions']['sku_type']) continue;
					if($this->price_type){
						if(!$r['conditions']['price_type']) continue;
						$found_pt = false;
						$price_type_list = array();
						$price_type_list = explode(",", $r['conditions']['price_type']);
						foreach($price_type_list as $row=>$code){
							$pt_code = trim($code);
							if($this->price_type[$pt_code]){
								$found_pt = true;
							}
						}
						if(!$found_pt) continue;
					}
					if($this->vendor_id && $this->vendor_id != $r['conditions']['vendor_id']) continue;
				}
			}
			
			$item_info = get_commission_condition_item_info($r['conditions']);

			$r['sku_type'] = $r['conditions']['sku_type'];
			$r['price_type'] = $r['conditions']['price_type'];
			if(trim($r['commission_value'])){
				if($r['commission_method'] != "Flat") $r['commission_value'] = unserialize($r['commission_value']);	
			}else unset($r['commission_value']);
			
			if($item_info) $r = array_merge($r, $item_info);
			$this->table[$r['saci_id']] = $r;
		}
		$con_multi->close_connection();
	}

	function output_excel(){
	    global $smarty, $sessioninfo;
		
		$this->process_form();
		$this->generate_report();

        include_once("include/excelwriter.php");
    	$smarty->assign('no_header_footer', true);
    	$filename = "sa_commission_".time().".xls";
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
		
		$this->report_title[] = "Date From ".strtoupper($this->date_from)." to ".strtoupper($this->date_to);
		$filter_by_header = ($this->filter_by) ? strtoupper(str_replace("_", " + ", $this->filter_by)) : "All";
		$this->report_title[] = "Filter by: ".$filter_by_header;
		
		
		// pre-load sales agent
		if($this->agent_id){
			$con->sql_query("select code, name from sa where id = ".mi($this->agent_id));
			$sa = $con->sql_fetchrow();
			$sa_header = $sa['code']." - ".$sa['name'];
			$con->sql_freeresult();
		}else{
			$sa_header = "All";
		}

		$this->report_title[] = "Sales Agent: ".$sa_header;
		
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
		$this->filter_by = $_REQUEST['filter_by'];
		$this->agent_id = $_REQUEST['agent_id'];

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
		
		if($_REQUEST['filter_by']){
			if($_REQUEST['filter_by'] == "sku"){ // filter by sku items
				if($_REQUEST['sku_code_list_2']){
					$q1 = $con->sql_query("select id, sku_item_code, description from sku_items where sku_item_code in (".$_REQUEST['sku_code_list_2'].")");

					while($r = $con->sql_fetchrow($q1)){
						$category[$r['sku_item_code']]['sku_item_code']=$r['sku_item_code'];
						$category[$r['sku_item_code']]['description']=$r['description'];
						$this->si_list[$r['id']]=$r['id'];
					}
					$this->category = $category;
				}else{
					$error[] = "Please search the SKU item then click add";
				}

				unset($_REQUEST['category_id']);
				unset($_REQUEST['category']);
				unset($_REQUEST['brand_id']);
				unset($_REQUEST['sku_type']);
				unset($_REQUEST['price_type']);
				unset($_REQUEST['vendor_id']);
			}elseif($_REQUEST['filter_by'] == "dept_brand"){ // filter by category + brand + additional filter
				if($_REQUEST['all_category'] || $_REQUEST['category_id'] || $_REQUEST['brand_id'] != ''){
					$this->category_id = $_REQUEST['category_id'];
					$this->brand_id = $_REQUEST['brand_id'];
					$this->sku_type = $_REQUEST['sku_type'];
					$this->price_type = $_REQUEST['price_type'];
					$this->vendor_id = $_REQUEST['vendor_id'];
				}else{
					$error[] = "Please select Category or Brand to search";
				}
				unset($_REQUEST['sku_code_list_2']);
			}
		}

		if($error){
			$smarty->assign("err", $error);
			$this->display();
			exit;
		}
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

$VIEW_SA_COMMISSION = new VIEW_SA_COMMISSION('View S/A Commission Report');
?>
