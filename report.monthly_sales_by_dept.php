<?php
/*
10/8/2010 6:19:29 PM Justin
- Fixed the bug where cannot display sales whenever the month is 10/11/12.

1/25/2011 4:37:20 PM Alex
- change use report server

6/24/2011 6:18:40 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:42:12 PM Andy
- Change split() to use explode()

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)
- report not to use branch group table anymore, change to individual branch

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

5:59 PM 11/27/2014 Andy
- Enhance the report discount to include discount2. (mix and match and receipt discount)

2/20/2020 4:16 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));

// set records to display on each table default by following
$smarty->assign("record_chop", 200);

class MONTHLY_SALES_DISCOUNTED_BY_DEPT_REPORT extends Report{
	private function run_report($bid,$tbl_name){
        global $con_multi, $smarty,$sessioninfo;
        
        $department_id = $this->department_id;
        $sku_type = $this->sku_type;
        $from_date = $this->date_from;
		$to_date = $this->date_to;

		if ($sessioninfo['level']<9999) $filter[] = "(c.department_id in ($sessioninfo[department_ids]) or c.department_id is null)";

		if($department_id) $filter[] = "cc.p2 = ".ms($department_id); 
		if($sku_type) $filter[] = "sku.sku_type = '".$sku_type."'"; 

		$filter[] = "sisc.date >= '".$from_date."' and sisc.date <= '".$to_date."'";
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';

		for($i=0; $i<count($tbl_name); $i++){
			$splt_bid = explode("sku_items_sales_cache_", $tbl_name[$i]); // split to get bid
			
			if ($this->combine_branch) {
				$br_id = substr($splt_bid[1],-1);
				$sql1 = "select branch_group_id from branch_group_items where branch_id = ".mi($br_id)." limit 1";
				$con_multi->sql_query($sql1);
				$branch_group_id = $con_multi->sql_fetchfield(0);
				$con_multi->sql_freeresult();
				if ($branch_group_id) $splt_bid[1] = 'bg'.$branch_group_id;
			}

			// stock sold query
			$sql = "select sisc.sku_item_id, date_format(sisc.date, '%Y-%m') as month, sku.sku_type,
					sum(sisc.amount) as amount, sum(sisc.disc_amt+sisc.disc_amt2) as disc_amount, c.id as cid, c.description
					from $tbl_name[$i] sisc
					left join `sku_items` on sku_items.id = sisc.sku_item_id
					left join `sku` on sku.id = sku_items.sku_id
					left join `brand` on brand.id = sku.brand_id
					left join `category_cache` cc using(category_id) 
					left join `category` c on c.id = cc.p2
					where ".join(' and ', $filter)."
					group by cid, sku.sku_type, month 
					order by c.description, sku.sku_type, month";//print "$sql<br /><br />";

			$sales = $con_multi->sql_query($sql);

			while($r = $con_multi->sql_fetchrow($sales)){
				$this->table[$r['cid']][$r['sku_type']]['description'] = $r['description'];
				$this->table[$r['cid']][$r['sku_type']][$r['month']][$splt_bid[1]]['amount'] += $r['amount'];
				$this->table[$r['cid']][$r['sku_type']][$r['month']][$splt_bid[1]]['disc_amount'] += $r['disc_amount'];
				$this->col_total[$r['month']][$splt_bid[1]]['amount'] += $r['amount'];
				$this->col_total[$r['month']][$splt_bid[1]]['disc_amount'] += $r['disc_amount'];
				$this->row_total[$r['cid']][$r['sku_type']]['amount'] += $r['amount'];
				$this->row_total[$r['cid']][$r['sku_type']]['disc_amount'] += $r['disc_amount'];
				$this->grand_total['amount'] += $r['amount'];
				$this->grand_total['disc_amount'] += $r['disc_amount'];
				$this->dept_set[$r['cid']][$r['sku_type']] = $r['sku_type'];
			}
			$con_multi->sql_freeresult($sales);
		}
		
		if($this->dept_set){
			foreach($this->dept_set as $dept_id => $sku_type){
				foreach($sku_type as $sku_type){
					$this->dept_rs_count[$dept_id] += 1;
				}
			}
		}
	}
	
    function generate_report(){
		global $con, $smarty, $sessioninfo, $config, $con_multi;

		$branch_group = $this->branch_group;	
		
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			
			//$tbl_name[] = "sku_items_sales_cache_bg".$bg_id;
			if (!$this->split_bg) $this->combine_branch = true;
			$branches_in_group = $this->get_branch_ids_in_group($bg_id);
			foreach ($branches_in_group as $b) {
			
				if ($config['sales_report_branches_exclude']) {
					$curr_branch_code = get_branch_code($b);
					if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
						// print "$curr_branch_code skipped<br />";
						continue;
					}
				}
			
				$tbl_name[] = "sku_items_sales_cache_b".$b;
			}
			
			/*
			print '<pre>';
			//print_r($branches_in_group);
			print_r($tbl_name);
			print '</pre>';
			*/

			if($this->split_bg) $group_by = "group by branch_group_items.branch_id";
			else $group_by = "group by branch_group.id";
			
			if ($this->split_bg) {
				$branch_id_col = 'branch_group_items.branch_id';
				$branch_code_col = 'branch.code';
				$br_prefix = 'b';
			}
			else {
				$branch_id_col = 'branch_group.id';
				$branch_code_col = 'branch_group.code';
				$br_prefix = 'bg';
			}
			
			$get_branch_group_code = $con_multi->sql_query($abc="select $branch_id_col as branch_id, $branch_code_col as code 
													  from branch_group 
													  join branch_group_items on branch_group.id = branch_group_items.branch_group_id 
													  left join branch on branch.id = branch_group_items.branch_id
													  where branch.active=1 and branch_group.id = $bg_id
													  $group_by");//print $abc.'<br /><br />';

			while($bg = $con_multi->sql_fetchrow($get_branch_group_code)){
				$bid[] = $bg['branch_id'];
                foreach($this->date_label as $column_key=>$m){
				
					if ($config['sales_report_branches_exclude']) {
						$curr_branch_code = $bg['code'];
						if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
							// print "$curr_branch_code skipped<br />";
							continue;
							$this->column_count += 1;
						}
					}
				
					$this->column_label[$m][$br_prefix.$bg['branch_id']] = $bg['code'];
				}
			}
			$con_multi->sql_freeresult($get_branch_group_code);

			$report_title[] = "Branch Group: ".$bg_code;
			$this->run_report(join(",",$bid),$tbl_name);
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){	// is a particular branch
	            $tbl_name[] = "sku_items_sales_cache_b".$bid;

				foreach($this->date_label as $column_key=>$m){
					$this->column_label[$m]['b'.$bid] = BRANCH_CODE;
				}

	            $this->run_report($bid,$tbl_name);
	            $branch_code = BRANCH_CODE;
			}else{	// from HQ user
				if($bid==0){	// is all the branches
	                $report_title[] = 'Branch: All';
	                $bg_sql = "select * from branch where id not in (select branch_id from branch_group_items) order by sequence,code";
					
					$q_b = $con_multi->sql_query($bg_sql);
					while($r = $con_multi->sql_fetchrow($q_b)){
					
						if ($config['sales_report_branches_exclude']) {
							$curr_branch_code = $r['code'];
							if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
								// print "$curr_branch_code skipped<br />";
								continue;
							}
						}
					
                        $tbl_name[] = "sku_items_sales_cache_b".$r['id'];
						foreach($this->date_label as $column_key=>$m){
							$this->column_label[$m]['b'.$r['id']] = $r['code'];
						}
						$this->column_count += 1;
					}
					$con_multi->sql_freeresult($q_b);

					if($this->split_bg){
						if($branch_group['have_group']){
							foreach($branch_group['have_group'] as $bid=>$b){
							
								if ($config['sales_report_branches_exclude']) {
									$curr_branch_code = get_branch_code($bid);
									if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
										// print "$curr_branch_code skipped<br />";
										continue;
									}
								}
							
								$tbl_name[] = "sku_items_sales_cache_b".$bid;

								$con_multi->sql_query("select code from branch where id = ".ms($bid));
								$b_code = $con_multi->sql_fetchfield(0);
								$con_multi->sql_freeresult();

	                            foreach($this->date_label as $column_key=>$m){
									$this->column_label[$m]['b'.$bid] = $b_code;
								}
								$this->column_count += 1;
							}
						}
					}else{
						if($branch_group['header']){
							foreach($branch_group['header'] as $bg_id=>$bg){
							
	                            //$tbl_name[] = "sku_items_sales_cache_bg".$bg_id;
								$this->combine_branch = true;
								$branches_in_group = $this->get_branch_ids_in_group($bg_id);
								foreach ($branches_in_group as $b) {
								
									if ($config['sales_report_branches_exclude']) {
										$curr_branch_code = get_branch_code($b);
										if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
											// print "$curr_branch_code skipped<br />";
											continue;
										}
									}
								
									$tbl_name[] = "sku_items_sales_cache_b".$b;
								}
							
								$bg_code = $branch_group['header'][$bg_id]['code'];
	                            foreach($this->date_label as $column_key=>$m){
									$this->column_label[$m]['bg'.$bg_id] = $bg_code;
								}
								$this->column_count += 1;
							}
						}
					}

					/*
					print '<pre>';
					print_r($tbl_name);
					print '</pre>';
					*/
					$this->run_report('',$tbl_name);
				}else{	// is a particular branch
	                $tbl_name[] = "sku_items_sales_cache_b".$bid;
		            $this->run_report($bid,$tbl_name);
					$branch_code = get_branch_code($bid);

					foreach($this->date_label as $column_key=>$m){
						$this->column_label[$m]['b'.$bid] = $branch_code;
					}

					$report_title[] = "Branch: ".$branch_code;
				}
			}
		}


		$split_bg = ($this->split_bg) ? "Yes" : "No";
		$report_title[] = "Split Branch Group: ".$split_bg;
		$con_multi->sql_query("select description from category where id = ".mi($this->department_id));
		$department_code = ($_REQUEST['department_id']) ? $con_multi->sql_fetchfield(0) : "All";
		$con_multi->sql_freeresult();
		$report_title[] = "Department: ".$department_code;
		$sku_type = ($_REQUEST['sku_type']) ? $this->sku_type : "All";
		$report_title[] = "SKU Type: ".$sku_type;
		$report_title[] = "Date : ".$this->date_from." to ".$this->date_to;

		$this->column_count = ($this->column_count) ? $this->column_count : 1;
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
        $smarty->assign('column_label', $this->column_label);
        $smarty->assign('column_count', $this->column_count);
        $smarty->assign('col_total', $this->col_total);
        $smarty->assign('row_total', $this->row_total);
        $smarty->assign('grand_total', $this->grand_total);
        $smarty->assign('dept_set', $this->dept_set);
        $smarty->assign('dept_rs_count', $this->dept_rs_count);
		$smarty->assign('table', $this->table);
		
		/*
		print '<pre>';
		print_r($this->column_label);
		// print_r($this->table);
		print '</pre>';
		*/
		
	}
	
	function process_form(){
	    global $con, $smarty;

        $this->bid  = get_request_branch();
        $this->split_bg = $_REQUEST['split_bg'];
        $this->department_id = $_REQUEST['department_id'];
        $this->sku_type = $_REQUEST['sku_type'];
        $this->date_from = $_REQUEST['date_from'];
        $this->date_to = $_REQUEST['date_to'];
		
		$end_date =date("Y-m-d",strtotime("-1 day", strtotime("+1 year",strtotime($this->date_from))));
    	if(strtotime($_REQUEST['date_to'])>strtotime($end_date)) $_REQUEST['date_to'] = $end_date;
    	$this->date_to = $_REQUEST['date_to'];
    	$date_label = $this->generate_dates($this->date_from, $this->date_to, 'Ym', 'Y-m');
    	if($date_label){
			foreach($date_label as $column_key=>$m){
				$this->date_label[$m] = $m;
			}
		}
        
		// call parent
		parent::process_form();
	}
	
	function get_branch_ids_in_group($group_id) {
		global $con,$con_multi;
		
		$br_array = array();
		$q1 = $con_multi->sql_query("select branch_id from branch_group_items where branch_group_id = ". mi($group_id));
		while($r1 = $con_multi->sql_fetchassoc($q1)){
			$br_array[] = $r1['branch_id'];
		}
		$con_multi->sql_freeresult($q1);
		return $br_array;
	}
	
}
//$con_multi = new mysql_multi();
$MONTHLY_SALES_DISCOUNTED_BY_DEPT_REPORT = new MONTHLY_SALES_DISCOUNTED_BY_DEPT_REPORT('Monthly Sales Discounted by Department Report');
//$con_multi->close_connection();
?>
