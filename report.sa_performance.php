<?php
/*
2/20/2012 12:04:11 PM Justin
- Fixed the bugs where causing branch group not functional.

3/5/2012 11:49:22 AM Justin
- Added new filter to skip those goods return items from POS.

3/12/2012 6:02:43 PM Justin
- Fixed bugs of the wrong sales amount sum up when showing details by date.

3/21/2012 11:52:43 AM Justin
- Fixed the bugs that branch ID get zero when logged on as sub branch.

8/29/2012 6:05 PM Justin
- Enhanced to have sales amount sorting and greater/less than specific amount that assigned by user filters.

2/20/2013 3:59 PM Justin
- Enhanced to calculate average sales amount by number for SA base on config.

3/26/2013 10:52 AM Justin
- Bug fixed on target sales amount could not be load out.

5/12/2014 5:13 PM Justin
- Enhanced to have total qty column.

6/29/2017 11:09 AM Justin
- Fixed to exclude gst from POS Amount.
- Change to use inv_line_gross_amt2 for DO Amount.

12/20/2017 5:26 PM Justin
- Enhanced the report to show details from sales cache instead of real time data.
- Enhanced the report to show data no longer base on monthly basis but daily.
- Enhanced to have KPI Performance Summary.

3/13/2018 3:44 PM Justin
- Bug fixed on branch info does not show out when filter with Sales Agent.
- Modified to take out the amount filtering for KPI Performance result.
- Bug fixed on amount filter sometimes will reset all data.

11/6/2019 5:05 PM Justin
- Bug fixed on the commission calculate bugs for sales / qty by range.
- Enhanced to load commission by sales/qty range data from newly created table.

11/14/2019 5:15 PM Andy
- Added maintenance check version 422.
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
$maintenance->check(422);

class SA_PERFORMANCE extends Module{
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
		if($this->ext_filter) $ext_filter = " and ".join(" and ", $this->ext_filter);
		
		// flat rate
		$sql = array();
		$sql[] = "select ssc.date, ssc.year, lpad(ssc.month,2,0) as month, ssc.amount, ssc.qty, ssc.commission_amt, ssc.sa_id, sa.code as sa_code, sa.name as sa_name,
				  b.code as branch_code, sast.value as st_list, ssc.transaction_count
				  from sa_sales_cache_b$bid ssc
				  left join sa on sa.id = ssc.sa_id
				  left join sa_sales_target sast on sast.sa_id = sa.id and sast.year = ssc.year and sast.branch_id = ".mi($bid)."
				  left join branch b on b.id = ".mi($bid)."
				  where ssc.date between ".ms($this->date_from)." and ".ms($this->date_to).$filter.$ext_filter;

		// commission by qty/sales range
		$sql[] = "select 0 as date, srsc.year, lpad(srsc.month,2,0) as month, srsc.amount, srsc.qty, srsc.commission_amt, srsc.sa_id, sa.code as sa_code, sa.name as sa_name,
				  b.code as branch_code, sast.value as st_list, srsc.transaction_count
				  from sa_range_sales_cache_b$bid srsc
				  left join sa on sa.id = srsc.sa_id
				  left join sa_sales_target sast on sast.sa_id = sa.id and sast.year = srsc.year and sast.branch_id = ".mi($bid)."
				  left join branch b on b.id = ".mi($bid)."
				  where concat(srsc.year, srsc.month) between ".date("Ym", strtotime($this->date_from))." and ".date("Ym", strtotime($this->date_to)).$filter;

		$all_sql = join(" UNION ALL ", $sql)." order by sa_code, branch_code, branch_code, year, month, date";
		$q1 = $con_multi->sql_query($all_sql);

		while($r1 = $con_multi->sql_fetchassoc($q1)){
			$ym = $r1['year'].$r1['month'];
			$this->table[$r1['sa_id']][$bid][$ym]['sa_id'] = $r1['sa_id'];
			$this->table[$r1['sa_id']][$bid][$ym]['sa_code'] = $r1['sa_code'];
			$this->table[$r1['sa_id']][$bid][$ym]['sa_name'] = $r1['sa_name'];
			$this->table[$r1['sa_id']][$bid][$ym]['month'] = $r1['month'];
			$this->table[$r1['sa_id']][$bid][$ym]['year'] = $r1['year'];
			$this->table[$r1['sa_id']][$bid][$ym]['branch_code'] = $r1['branch_code'];
			$this->table[$r1['sa_id']][$bid][$ym]['curr_sales_amt'] += $r1['amount'];
			$this->table[$r1['sa_id']][$bid][$ym]['curr_sales_qty'] += $r1['qty'];

			$sales_target_list = unserialize($r1['st_list']);
			$this->table[$r1['sa_id']][$bid][$ym]['target_sales_amt'] = $sales_target_list[mi($r1['month'])];

			$date = $r1['year'].'-'.$r1['month'].'-01';
			$remaining_times = strtotime("-1 day", strtotime("+1 month", strtotime($date))) - strtotime(date("Y-m-d"));
			$remaining_days = mi(($remaining_times)/86400);
			
			$this->table[$r1['sa_id']][$bid][$ym]['remaining_days'] = $remaining_days;
			
			$this->kpi_table[$r1['sa_id']]['sa_id'] = $r1['sa_id'];
			$this->kpi_table[$r1['sa_id']]['sa_code'] = $r1['sa_code'];
			$this->kpi_table[$r1['sa_id']]['sa_name'] = $r1['sa_name'];
			$this->kpi_table[$r1['sa_id']]['curr_sales_amt'] += $r1['amount'];
			$this->kpi_table[$r1['sa_id']]['curr_sales_qty'] += $r1['qty'];
			$this->kpi_table[$r1['sa_id']]['transaction_count'] += $r1['transaction_count'];
		}
		//print_r($this->sac_table);
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
		global $con, $smarty, $sessioninfo;

		$this->table = $this->kpi_table = array();
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$this->run_report($bid);
			}
		}

		if(($this->amt_filter_type && $this->amt_filter) || $this->sort_by){
			foreach($this->table as $sa_id=>$bid_list){
				$total_sales_amt = 0;
				foreach($bid_list as $bid=>$ym_list){
					if($this->amt_filter_type && $this->amt_filter){
						$curr_sales_amt = 0;
						foreach($ym_list as $ym=>$f){
							$is_reset = false;
							if($this->amt_filter_type == "greater" && $f['curr_sales_amt'] < $this->amt_filter){
								unset($this->table[$sa_id][$bid][$ym]);
								$is_reset = true;
							}elseif($this->amt_filter_type == "lower" && $f['curr_sales_amt'] > $this->amt_filter){
								unset($this->table[$sa_id][$bid][$ym]);
								$is_reset = true;
							}
							
							if(!$is_reset){
								$curr_sales_amt += $f['curr_sales_amt'];
								$total_sales_amt += $f['curr_sales_amt'];
							}
						}
						
						if(!$curr_sales_amt) unset($this->table[$sa_id][$bid]);
					}
					
					if($this->table[$sa_id][$bid] && $this->sort_by){
						if($this->sort_by == "highest") uasort($this->table[$sa_id][$bid], array($this,"sort_curr_sales_amt_asc"));
						else uasort($this->table[$sa_id][$bid], array($this,"sort_curr_sales_amt_desc"));
					}
				}
				if($this->amt_filter_type && $this->amt_filter && !$total_sales_amt) unset($this->table[$sa_id]);
				
				/* can no longer use this since it will create variance between sales by month vs KPI Performance
				if(isset($this->kpi_table[$sa_id]) && (($this->amt_filter_type == "greater" && $this->kpi_table[$sa_id]['curr_sales_amt'] < $this->amt_filter) || ($this->amt_filter_type == "lower" && $this->kpi_table[$sa_id]['curr_sales_amt'] > $this->amt_filter))){
					unset($this->kpi_table[$sa_id]);
				}*/
			}

			if($this->kpi_table && $this->sort_by){
				if($this->sort_by == "highest") uasort($this->kpi_table, array($this,"sort_curr_sales_amt_asc"));
				else uasort($this->kpi_table, array($this,"sort_curr_sales_amt_desc"));
			}
		}
		
		
		// set report fixed row display
		$smarty->assign('report_row', 25);
		
		$this->report_title[] = "Date From ".strtoupper($this->date_from)." to ".strtoupper($this->date_to);
		
		if(!$this->sales_type) $sales_type = "All";
		else{
			if($this->sales_type == "open") $sales_type = "Cash Sales";
			elseif($this->sales_type == "credit_sales") $sales_type = "Credit Sales";
			else $sales_type = "POS";
		}
		$this->report_title[] = "Sales Type: ".$sales_type;		
		// pre-load sales agent
		/*if($this->department_id){
			$con->sql_query("select description from category where id = ".mi($this->department_id));
			$dept = $con->sql_fetchrow();
			$dept_desc = $dept['description'];
			$con->sql_freeresult();
		}else{
			$dept_desc = "All";
		}

		$this->report_title[] = "Department: ".$dept_desc;
		$sku_type = ($this->sku_type) ? $this->sku_type : "All";
		$this->report_title[] = "SKU Type: ".$sku_type;*/

		if($this->sa_id){
			$con->sql_query("select name from sa where id = ".mi($this->sa_id));
			$sa_desc = $con->sql_fetchfield(0);
			$con->sql_freeresult();
		}else $sa_desc = "All";
		
		$this->report_title[] = "Sales Agent: ".$sa_desc;
		
		if($this->amt_filter_type && $this->amt_filter){
			$amt_filter_desc = "Sales Amount by Month: ".ucwords($this->amt_filter_type)." Than ".$this->amt_filter;
			$this->report_title[] = $amt_filter_desc;
		}

		if($this->sort_by){
			$sort_by_desc = "Sales Amount Sort By: ".ucwords($this->sort_by);
			$this->report_title[] = $sort_by_desc;
		}		
	
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		//$smarty->assign('sac_table', $this->sac_table);
		$smarty->assign('table', $this->table);
		$smarty->assign('kpi_table', $this->kpi_table);
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
		$this->department_id = $_REQUEST['department_id'];
		$this->sku_type = $_REQUEST['sku_type'];
		$this->sales_type = $_REQUEST['sales_type'];
		$this->sa_id = $_REQUEST['sa_id'];
		$this->sort_by = $_REQUEST['sort_by'];
		$this->amt_filter_type = $_REQUEST['amt_filter_type'];
		$this->amt_filter = $_REQUEST['amt_filter'];

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

		$this->filter = $this->ext_filter = array();

		if($this->sales_type) $this->ext_filter[] = "ssc.sales_type = ".ms($this->sales_type);
		//if($this->department_id) $this->filter[] = "c.department_id = ".ms($this->sku_type);
		//if($this->sku_type) $this->filter[] = "sku.sku_type = ".ms($this->sku_type);
		if($this->sa_id) $this->filter[] = "sa.id = ".mi($this->sa_id);
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
	
	function ajax_show_date_details(){
		global $con, $smarty, $config;

		$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}

		$form = $_REQUEST;
		$table = array();
		$bid = $form['bid'];
		
		
		$date_from = $form['year']."-".$form['month']."-01";
		$date_to = date("Y-m-d", strtotime($form['year']."-".$form['month']."-01 +1 month -1 day"));
		// if found date filter is same month from click event, need to assign date from as date filter
		if(date("Y", strtotime($form['date_from'])) == date("Y", strtotime($date_from)) && date("m", strtotime($form['date_from'])) == date("m", strtotime($date_from))){
			$date_from = $form['date_from'];
		}
		
		// if found date filter is same month from click event, need to assign date to as date filter
		if(date("Y", strtotime($form['date_to'])) == date("Y", strtotime($date_to)) && date("m", strtotime($form['date_to'])) == date("m", strtotime($date_to))){
			$date_to = $form['date_to'];
		}
		
		$filters = $ext_filters = array();
		$filters[] = "sa.id = ".mi($form['sa_id']);
		if($form['sales_type']) $ext_filters[] = "ssc.sales_type = ".ms($form['sales_type']);
		
		if($filters) $filter = " and ".join(" and ", $filters);
		if($ext_filters) $ext_filter = " and ".join(" and ", $ext_filters);
		
		// flat rate
		$sql = array();
		$sql[] = "select ssc.date, ssc.year, lpad(ssc.month,2,0) as month, ssc.amount, ssc.qty, sa.code as sa_code,
				  b.code as branch_code, sast.value as st_list
				  from sa_sales_cache_b$bid ssc
				  left join sa on sa.id = ssc.sa_id
				  left join sa_sales_target sast on sast.sa_id = sa.id and sast.year = ssc.year and sast.branch_id = ".mi($bid)."
				  left join branch b on b.id = ".mi($bid)."
				  where ssc.date between ".ms($date_from)." and ".ms($date_to).$filter.$ext_filter;

		// commission by qty/sales range
		$sql[] = "select last_day(concat(srsc.year, '-', lpad(srsc.month,2,0), '-01')) as date, srsc.year, lpad(srsc.month,2,0) as month, srsc.amount, srsc.qty, sa.code as sa_code,
				  b.code as branch_code, sast.value as st_list
				  from sa_range_sales_cache_b$bid srsc
				  left join sa on sa.id = srsc.sa_id
				  left join sa_sales_target sast on sast.sa_id = sa.id and sast.year = srsc.year and sast.branch_id = ".mi($bid)."
				  left join branch b on b.id = ".mi($bid)."
				  where concat(srsc.year, srsc.month) between ".date("Ym", strtotime($date_from))." and ".date("Ym", strtotime($date_to)).$filter;
				  
		$all_sql = join(" UNION ALL ", $sql)." order by sa_code, branch_code, year, month, date";
		$q1 = $con_multi->sql_query($all_sql);
		
		while($r1 = $con_multi->sql_fetchassoc($q1)){
			$sales_target_list = unserialize($r1['st_list']);
			$target_sales_amt = $sales_target_list[mi($r1['month'])];
			
			$end_date = date("Y-m", strtotime($r1['date']))."-01";
			$end_time = strtotime($end_date." +1 month -1 day");

			$remaining_times = $end_time - strtotime($r1['date']);
			$remaining_days = mi(($remaining_times)/86400);
			$table[$r1['date']]['curr_sales_amt'] += $r1['amount'];
			$table[$r1['date']]['curr_sales_qty'] += $r1['qty'];
			$table[$r1['date']]['remaining_days'] = $remaining_days;
		}
		$con_multi->sql_freeresult($q1);
		
		$smarty->assign('sa_id', $form['sa_id']);
		$smarty->assign('bid', $form['bid']);
		$smarty->assign('year', $form['year']);
		$smarty->assign('month', $form['month']);
		$smarty->assign('target_sales_amt', $target_sales_amt);
		$smarty->assign('table', $table);
		
		$smarty->display("report.sa_performance.detail.tpl");
	}
	
	private function sort_curr_sales_amt_desc($a,$b){
		if (($a['curr_sales_amt']==$b['curr_sales_amt'])) return 0;
	    else{
			return ($a['curr_sales_amt']>$b['curr_sales_amt']) ? 1:-1;
		}
	}

	private function sort_curr_sales_amt_asc($a,$b){
		if (($a['curr_sales_amt']==$b['curr_sales_amt'])) return 0;
	    else{
			return ($a['curr_sales_amt']<$b['curr_sales_amt']) ? 1:-1;
		}
	}
}

$SA_PERFORMANCE = new SA_PERFORMANCE('Sales Agent Performance Report');
?>
