<?php
/*
2/24/2012 3:03:43 PM Justin
- Fixed the bug when print report from HQ, it shows query error.
- Fixed bug of when "Use GRN", show inaccurate result.

3/13/2012 3:31:59 PM Andy
- Change Report to use sku vendor from cache table.

7/5/2012 10:22:23 AM Justin
- Added to pick up cost for GP and GP %.
- Modified to use last Vendor instead of use last GRN.
- Enhanced to show vendor code.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

12/20/2013 1:32 PM Andy
- Fix SQL error when view report by normal user.

12/23/2013 5:40 PM Fithri
- fix bug query from wrong table vendor_sku_history_b? when tick 'Use Last Vendor'

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

7/23/2014 2:20 PM Fithri
- fix amount bug when tick 'Use Last Vendor'

2/20/2020 4:27 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

class MONTHLY_VENDOR_SALES_REPORT extends Report{
	private function run_report($bid){
        global $con, $smarty, $sessioninfo, $con_multi, $config;

        $year = $this->year;
		$month = $this->month;
		if($this->vendor_id_list) $vendor_id_list = $this->vendor_id_list;
		else $vendor_id_list = array();
		$use_last_vendor = $this->use_last_vendor;
		$filters = $vsh_filter = array();
		
		if($sessioninfo['level']<9999){
			$filters[] = "(c.department_id in ($sessioninfo[department_ids]) or c.department_id is null)";
		}
	
		if($vendor_id_list){
			if($use_last_vendor){
				// select those sku of this grn vendor between this date
				/*$grn_sid_list = array();
				$vsh_filter[] = "vsh.branch_id=".mi($bid)." and vsh.source='grn'";
				$vsh_filter[] = "vsh.added between ".ms($from_date)." and ".ms($to_date);
				$vsh_filter[] = "vsh.vendor_id in (".join(",", $vendor_id_list).")";
				$vsh_filter = join(" and ", $vsh_filter);
				
				$sql = "select vsh.sku_item_id as sid, vsh.vendor_id
				from vendor_sku_history vsh 
				where $vsh_filter";
	
				//print $sql;
				$q1 = $con_multi->sql_query($sql) or die(mysql_error());
				while($r = $con_multi->sql_fetchassoc($q1)){
					$vendor_id_list[$r['vendor_id']] = mi($r['vendor_id']);
					$grn_sid_list[$r['sid']] = mi($r['sid']);
				}
				$con_multi->sql_freeresult($q1);*/
				
				//$filters[] = "sisc.last_grn_vendor_id in (".join(",", $vendor_id_list).")";
				
				//$filters[] = "vsh.vendor_id in (".join(",", $vendor_id_list).")";
				
				/*$use_grn_xtra_join = "join vendor_sku_history_b".$bid." vsh on vsh.sku_item_id=sisc.sku_item_id and sisc.date between vsh.from_date and vsh.to_date
				left join vendor last_ven on last_ven.id=vsh.vendor_id";*/
				
				$last_date = date("Y-m-d", strtotime("-1 day", strtotime("+1 month", strtotime($year."-".$month."-01"))));
				
				//$use_last_vd_xtra_col = ",ifnull((select vsh.vendor_id from vendor_sku_history_b".mi($bid)." vsh where vsh.from_date<=".ms($year."-".$month."-01")." and vsh.sku_item_id=si.id order by from_date desc limit 1), sku.vendor_id) as vid";
				$use_last_vd_xtra_col = ",ifnull((select vsh.vendor_id from vendor_sku_history_b".mi($bid)." vsh where vsh.sku_item_id=sisc.sku_item_id and sisc.date between vsh.from_date and vsh.to_date limit 1),0) as vid";
				
				$having = "having vid in (".join(",", $vendor_id_list).")";
			}else{
				$filters[] = "sku.vendor_id in (".join(",", $vendor_id_list).")";
			}
		}
		
		$filters[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
		
		if($filters) $filter = join(" and ", $filters)." and ";

		$sql = "select sisc.sku_item_id, si.sku_item_code, si.artno, si.description as sku_desc, 
				si.artno, sku.vendor_id, vendor.code as vd_code, vendor.description as vd_desc, 
				sisc.amount, sisc.disc_amt, sisc.qty, sisc.cost $use_last_vd_xtra_col
				from sku_items_sales_cache_b".mi($bid)." sisc
				left join `sku_items` si on si.id = sisc.sku_item_id
				left join `sku` on sku.id = si.sku_id
				left join `vendor` on vendor.id = sku.vendor_id
				left join category c on c.id=sku.category_id
				where $filter
				sisc.year = ".mi($year)." and sisc.month = ".mi($month)."
				$having
				order by vendor.description";//print "$sql<br /><br />";

		$q2 = $con_multi->sql_query($sql);

		while($r = $con_multi->sql_fetchrow($q2)){
			//print $r['last_grn_vendor_id']."<br />";
			/*if($use_grn && $vendor_id_list){
				if(!in_array($r['last_grn_vendor_id'], $vendor_id_list) && !in_array($r['sku_item_id'], $grn_sid_list)){
					if(!$config['use_grn_last_vendor_include_master'] || !in_array($r['vendor_id'], $vendor_id_list)){
						continue;
					}
				}
			}*/

			if($use_last_vendor){
				$q1 = $con_multi->sql_query("select * from vendor where id = ".mi($r['vid']));
				$vd_info = $con_multi->sql_fetchassoc($q1);
				$con_multi->sql_freeresult($q1);
				$vd_code = $vd_info['code'];
				$vd_desc = $vd_info['description'];
				$vid = $r['vid'];
			}else{
				$vd_code = $r['vd_code'];
				$vd_desc = $r['vd_desc'];
				$vid = $r['vendor_id'];
			}
			
			if(!$vd_desc) $vd_desc = "Untitled";
			 
			$this->table[$vid]['code'] = $vd_code;
			$this->table[$vid]['description'] = $vd_desc;
			$selling = $r['amount'];
			$this->table[$vid]['amount'] += $selling;
			//$this->table[$vid]['disc_amt'] += $r['disc_amt'];
			$this->table[$vid]['cost'] += $r['cost'];
			$this->table[$vid]['gp'] += $selling - $r['cost'];
			if($selling) $this->table[$vid]['gp_per'] = ($this->table[$vid]['gp'] / $this->table[$vid]['amount']) * 100;
			$this->table[$vid]['qty'] += $r['qty'];
		}
		$con_multi->sql_freeresult($q2);
	}
	
    function generate_report(){
		global $con, $smarty, $config, $con_multi;

		$branch_group = $this->branch_group;
		
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			if($branch_group['items'][$bg_id]){
				foreach($branch_group['items'][$bg_id] as $bid=>$b){
					if ($config['sales_report_branches_exclude']) {
						$branch_code = $b['code'];
						if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
							// print "$branch_code skipped<br />";
							continue;
						}
					}
					$this->run_report($bid);
				}
				$con_multi->sql_query("select code from branch_group where id=".mi($bg_id)) or die(mysql_error());
				$report_title[] = 'Branch Group: '.$con_multi->sql_fetchfield(0);
				$con_multi->sql_freeresult();
			}	
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){	// is a particular branch
	            $this->run_report($bid);
	            $branch_code = BRANCH_CODE;
			}else{	// from HQ user
				if($bid==0){	// is all the branches
	                $report_title[] = 'Branch: All';
	                $bg_sql = "select * from branch where active=1 order by sequence,code";
					
					$q_b = $con_multi->sql_query($bg_sql);
					while($r = $con_multi->sql_fetchrow($q_b)){
						if ($config['sales_report_branches_exclude']) {
							$branch_code = $r['code'];
							if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
								// print "$branch_code skipped<br />";
								continue;
							}
						}
                        $this->run_report($r['id']);
					}
					$con_multi->sql_freeresult($q_b);
				}else{	// is a particular branch
		            $this->run_report($bid);
					$branch_code = get_branch_code($bid);
					$report_title[] = "Branch: ".$branch_code;
				}
			}
		}

		$report_title[] = "Year: ".$this->year;
		$report_title[] = "Month: ".$this->months[$this->month];
		
		$vd_list = array();
		if($this->vendor_id_list){
			$con_multi->sql_query("select code from vendor where id in (".join(",", $this->vendor_id_list).")");
			while($r = $con_multi->sql_fetchrow()){
				$vd_list[] = $r['code'];
			}
			$con_multi->sql_freeresult();
		}
		$vd = (count($vd_list) > 0) ? join(", ", $vd_list) : "All";
		$report_title[] = "Vendor(s): ".$vd;

		$use_last_vendor = ($this->use_last_vendor) ? "Yes" : "No";
		$report_title[] = "Use Last Vendor: ".$use_last_vendor;
		
		$smarty->assign('vd_count', count($vd_list));
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		$smarty->assign('table', $this->table);
	}

	function default_values(){
		if(!$_REQUEST['year']) $_REQUEST['year'] = date("Y");
		if(!$_REQUEST['month']) $_REQUEST['month'] = date("m", strtotime("-1 month", time()));
	}
	
	function process_form(){
	    global $con, $smarty;

        $this->bid  = get_request_branch();
		$this->year = $_REQUEST['year'];
		$this->month = $_REQUEST['month'];
        $this->vendor_id_list = $_REQUEST['vendor_id_list'];
        $this->use_last_vendor = $_REQUEST['use_last_vendor'];
		// call parent
		parent::process_form();
	}
}

/*$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}*/

$MONTHLY_VENDOR_SALES_REPORT = new MONTHLY_VENDOR_SALES_REPORT('Monthly Vendor Sales Report');
//$con_multi->close_connection();
?>
