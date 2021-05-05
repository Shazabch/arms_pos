<?php
/*
1/18/2011 6:36:41 PM Alex
- change use report_server

6/24/2011 6:04:54 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:27:28 PM Andy
- Change split() to use explode()

8/1/2012 3:03 PM Justin
- Fixed bug of showing sql error while filter by branch group.
- Fixed bug of report title.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

6/7/2019 5:30 PM William
- Enhanced to take out discount and cost for add column gross sales, total discount and Gross Profit.

6/26/2019 9:18 AM William
- tpl file calculate gross amount change to use php calculate.

2/20/2020 2:12 PM William
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

//$con_multi= new mysql_multi();

class DAILY_MONTHLY_BRAND_SALES_BY_DEPT_REPORT extends Report{
	private function run_report($bid,$tbl_name){
        global $con_multi, $smarty,$sessioninfo;
        
        $department_id = $this->department_id;
        $sku_type = $this->sku_type;
        $from_date = $this->date_from;
		$to_date = $this->date_to;
		$view_type = $this->view_type;

		if ($sessioninfo['level']<9999) $filter[] = "(c.department_id in ($sessioninfo[department_ids]) or c.department_id is null)";

		if($department_id) $filter[] = "cc.p2 = ".ms($department_id); 
		if($sku_type) $filter[] = "sku.sku_type = '".$sku_type."'"; 

		$filter[] = "sisc.date >= '".$from_date."' and sisc.date <= '".$to_date."'";
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';
		
		/*
		print '<pre>';
		print_r($filter);
		print '</pre>';
		*/

		for($i=0; $i<count($tbl_name); $i++){
			// stock sold query
			$sql = "select sisc.sku_item_id, date_format(sisc.date, '%Y-%m') as month, sisc.date as day,sum(sisc.cost) as cost,
					sum(sisc.amount) as amount, sum(sisc.disc_amt+sisc.disc_amt2) as discount,brand.description as brand_desc
					from $tbl_name[$i] sisc
					left join `sku_items` on sku_items.id = sisc.sku_item_id
					left join `sku` on sku.id = sku_items.sku_id
					left join `brand` on brand.id = sku.brand_id
					left join `category_cache` cc using(category_id) 
					left join `category` c on c.id = cc.p2
					where ".join(' and ', $filter)."
					group by sisc.date, brand_desc
					order by brand_desc, sisc.date";
			//print $sql."<br />";
			$sales = $con_multi->sql_query($sql);

			while($r = $con_multi->sql_fetchrow($sales)){
				if($view_type == 'month'){
					$this->table[$r['brand_desc']][$r['month']]['amount'] += $r['amount'];
					$this->table[$r['brand_desc']][$r['month']]['discount'] += $r['discount'];
					$this->table[$r['brand_desc']][$r['month']]['gross_amt'] += $r['discount'];
					$this->table[$r['brand_desc']][$r['month']]['gross_amt'] += $r['amount'];
					$this->table[$r['brand_desc']][$r['month']]['cost'] += $r['cost'];
					$this->table[$r['month']]['col_total'] += $r['amount'];
				}else{
					$this->table[$r['brand_desc']][$r['day']]['amount'] += $r['amount'];
					$this->table[$r['brand_desc']][$r['day']]['discount'] += $r['discount'];
					$this->table[$r['brand_desc']][$r['day']]['gross_amt'] += $r['discount'];
					$this->table[$r['brand_desc']][$r['day']]['gross_amt'] += $r['amount'];
					$this->table[$r['brand_desc']][$r['day']]['cost'] += $r['cost'];
					$this->table[$r['day']]['col_total'] += $r['amount'];
				}
				$this->table['grand_total'] += $r['amount'];
				$this->table['discount_total'] += $r['discount'];
				$this->table['gross_amt_total'] += $r['discount'];
				$this->table['gross_amt_total'] += $r['amount'];
				$this->table['cost_total'] += $r['cost'];
				$this->brand[$r['brand_desc']] = $r['brand_desc'];
			}
			$con_multi->sql_freeresult($sales);
		}
		//print_r($this->table);
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
					$tbl_name[] = "sku_items_sales_cache_b".$bid;
				}
			}
			$this->run_report($bid,$tbl_name);
			
			$report_title[] = "Branch Group: ".$branch_group['header'][$bg_id]['code'];
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){	// is a particular branch
	            $tbl_name[] = "sku_items_sales_cache_b".$bid;
	            $this->run_report($bid,$tbl_name);
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
					
                        $tbl_name[] = "sku_items_sales_cache_b".$r['id'];
					}
					$con_multi->sql_freeresult($q_b);
					
					$this->run_report('', $tbl_name);
				}else{	// is a particular branch
	                $tbl_name[] = "sku_items_sales_cache_b".$bid;
		            $this->run_report($bid,$tbl_name);
					$branch_code = get_branch_code($bid);
					$report_title[] = "Branch: ".$branch_code;
				}
			}
		}

		$con_multi->sql_query("select description from category where id = ".mi($this->department_id));
		$department_code = ($_REQUEST['department_id']) ? $con_multi->sql_fetchfield(0) : "All";
		$con_multi->sql_freeresult();
		$report_title[] = "Department: ".$department_code;
		$sku_type = ($_REQUEST['sku_type']) ? $this->sku_type : "All";
		$report_title[] = "SKU Type: ".$sku_type;
		$report_title[] = "Date : ".$this->date_from." to ".$this->date_to;
        $report_title[] = "View By: ".ucwords($this->view_type);

        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
        $smarty->assign('date_label', $this->date_label);
        $smarty->assign('brand', $this->brand);
		$smarty->assign('table', $this->table);
	}
	
	function process_form(){
	    global $smarty;

        $this->bid  = get_request_branch();
        $this->department_id = $_REQUEST['department_id'];
        $this->sku_type = $_REQUEST['sku_type'];
        $this->date_from = $_REQUEST['date_from'];
        $this->date_to = $_REQUEST['date_to'];
        $this->view_type = $_REQUEST['view_type'];
		
		if($this->view_type=='day'){
			$end_date =date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($this->date_from))));
        	if(strtotime($_REQUEST['date_to'])>strtotime($end_date)) $_REQUEST['date_to'] = $end_date;
        	$this->date_to = $_REQUEST['date_to'];
        	$date_label = $this->generate_dates($this->date_from, $this->date_to, 'Ymd', 'Y-m-d');

			if($date_label){
				foreach($date_label as $date_key=>$d){
					$this->date_label[$date_key]['day'] = $d;
				}
			}
		}else{
			$end_date =date("Y-m-d",strtotime("-1 day",strtotime("+1 month", strtotime("+1 year",strtotime($this->date_from)))));
        	if(strtotime($_REQUEST['date_to'])>strtotime($end_date)) $_REQUEST['date_to'] = $end_date;
        	$this->date_to = $_REQUEST['date_to'];
        	$date_label = $this->generate_dates($this->date_from, $this->date_to, 'Ym', 'Y-m');
        	if($date_label){
				foreach($date_label as $date_key=>$m){
					$this->date_label[$date_key]['month'] = $m;
				}
			}
		}
        
        //print_r($this->date_label);
        
		// call parent
		parent::process_form();
	}
}

$DAILY_MONTHLY_BRAND_SALES_BY_DEPT_REPORT = new DAILY_MONTHLY_BRAND_SALES_BY_DEPT_REPORT('Daily / Monthly Brand Sales by Department Report');
?>
