<?php
/*
9/8/2010 11:06:02 AM Andy
- Enhance report to use templates
- Enhance report to show fresh market row

9/17/2010 12:10:40 PM Andy
- Fix report show no data in branch mode.

9/22/2010 5:43:00 PM
- Fix lower level user show report will cause sql error bugs.

11/24/2010 11:45:12 AM Andy
- Add option to let user choose whether use report server or not.

11/29/2010 11:46:37 AM Andy
- Remove all fresh market items from this report.
- Show a new row of fresh market amount if "sales amount" is choose.

12/6/2010 2:21:49 PM Andy
- Change if item directly under category show "(Items directly under this category)" instead of category name, and cannot show sku details.

2/14/2011 6:01:04 PM Andy
- Reconstruct daily category sales report to show fresh market data.

6/10/2011 10:12:09 AM Andy
- Fix sku table total amount missing.

6/21/2011 6:23:56 PM Andy
- Fix total row missing when group monthly.

6/27/2011 10:15:33 AM Andy
- Make all branch default sort by sequence, code.

7/6/2011 5:18:49 PM Andy
- Fix un-category sales missing from report.
- Fix item direct under category cannot show sku details.

9/8/2011 3:13:23 PM Andy
- Add show transaction count and buying power if show report by using top category or line.

9/19/2011 9:54:21 AM Andy
- Fix sku type error.

9/21/2011 2:10:03 PM Andy
- Change load transaction count script to reduce memory usage.
- Fix transaction count column not match when show sub-category.

10/14/2011 11:54:27 AM Alex
- edit smarty_value_format to check 'qty'

3/27/2012 3:08:13 PM Justin
- Enhanced existing Branch filter to accept "Branch Region" filter.

4/2/2012 2:51:43 PM Justin
- Changed label "Items directly under this <category description>" instead of "Items directly under this category".

8/15/2012 11:30:43 AM Fithri
- Add 'Artno' column in Daily Category Sales Report for consignment customer

2/4/2013 5:34 PM Justin
- Enhanced to show and filter branches from regions or branch group base on user's regions.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

12/24/2013 5:18 PM Andy
- Fix error when export with sub cat.

4/2/2014 5:27 PM Justin
- Enhanced to show the total of Mix & Match, Credit & Cash Sales DO.

4/3/2014 9:48 AM Justin
- Bug fixed on the wrong cost and amount for DO.

5/12/2014 3:49 PM Justin
- Enhanced to comment out the testing code.

5/20/2014 10:37 AM Justin
- Enhanced to have export feature for itemise table.

6/4/2014 2:48 PM Justin
- Enhanced to use new method for export itemise into CSV.

6/5/2014 11:54 AM Justin
- Bug fixed of some info were missing after new method applied.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

4:53 PM 11/27/2014 Andy
- Enhance to show Service Charges and GST.
- Fix the wrong DO sales amount when got mark-up/mark-down.
- The report no longer show mix & match discount when found sales cache data contain discount2 value.
- Bring the grand total calculation to php instead of calculate in tpl.
- Enhance the report to able to show by GST Amount or Sales Amount Included GST.

3/14/2015 10:42 AM Andy
- Fix service charge still show at report even no data.

4/20/2015 12:28 PM Andy
- Fix itemise export not working.

5/27/2015 10:30 AM Justin
- Enhanced to pickup transfer do (if got print invoice) by config set.
- Bug fixed on DO amount have wrongly sum up with GST amount.

5/28/2015 3:21 PM Justin
- Bug fixed on Transfer DO wrongly pickup while click to show SKU list.

7/30/2015 2:05PM Andy
- fix m & m query

8/16/2016 11:16 AM Andy
- Enhanced to show MCode in sku table.

9/20/2016 10:42 AM Qiu Ying
- Enhanced to show artno for daily category sales report

1/18/2017 10:40 AM Andy
- Fixed failed to Export DO Items.

2/28/2017 3:36 PM Justin
- Enhanced to take out user level checking for department filter, system will now always filter department based on user settings.

5/2/2017 11:47 AM Qiu Ying
- Enhanced to add (POS + DO Sales) in Title

6/21/2017 2:22 PM Justin
- Enhanced to comment out the Mix & Match Total feature.

12/6/2017 5:20 PM Andy
- Enhanced to able to filter by member, disable if got config sku_report_sales_cache_no_member_data.

5/4/2018 5:49 PM Andy
- Fixed the report always show no data if no POS data found.

10/22/2018 5:41 PM Andy
- Enhanced to default don't calculate fresh market cost, only calculate it when got config "enable_fresh_market_report_calculation".

6/7/2019 1:08 PM William
- Enhanced to take out discount for add column gross sales and total discount.

6/25/2019 10:47 AM William
- Enhanced unset array to unset discount 0 value.
- tpl file calculate gross amount change to use php calculate.

1/31/2020 5:56 PM Andy
- Enhanced all connection to use report server.

2/20/2020 11:12 AM William
- Enhanced to free sql result.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
//if ($sessioninfo['level']<9999) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

class SALES_REPORT_CATEGORY extends Module{
	var $branches_group = array();  // use to hold all branches group data
	var $branches = array();    // use to hold all branches data
	var $branch_id; // use to store user selected branch id
	var $branch_id_list = array(); // use to store all branch need to generate
	
	var $tb = array();
	var $tb_total = array();
	var $tb_grand_total = array();
	var $cat_child_info = array();
	var $sc_data = array();
	var $dp_rcv_data = array();
	var $dp_used_data = array();
	var $rounding_data = array();
	var $over_data = array();
	var $daily_info = array();
	
    function __construct($title){
		global $con, $smarty, $sessioninfo, $config, $con_multi, $appCore;

		// establish report server connection
		if(!$con_multi){
			//if(!$_REQUEST['use_report_server'])   $con_multi = $con;
			//else	$con_multi= new mysql_multi();
			$con_multi = $appCore->reportManager->connectReportServer();
		}
		
		$this->init_selection();
		if(BRANCH_CODE == 'HQ'){
			$this->branch_id = mi($_REQUEST['branch_id']);
			
			if(preg_match("/^REGION_/", $_REQUEST['branch_id'])){
				$region = str_replace("REGION_", "", $_REQUEST['branch_id']);
				$q1 = $con_multi->sql_query("select b.* from branch b where b.active = 1 and b.region = ".ms($region));

				while($r = $con_multi->sql_fetchassoc($q1)){
					if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
					if ($config['sales_report_branches_exclude']) {
						$curr_branch_code = $r['code'];
						if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
							//print "$curr_branch_code skipped<br />";
							continue;
						}
					}
					$this->branch_id_list[] = $r['id'];
				}
				$con_multi->sql_freeresult($q1);
			}elseif($this->branch_id<0){ // branch group selected
				$this->bgid = abs($this->branch_id);
				if($this->branches_group){
					foreach($this->branches_group['items'][$this->bgid] as $bid=>$b){
						if($config['masterfile_branch_region'] && get_branch_code($bid) != "HQ" && !check_user_regions($bid)) continue;
						if ($config['sales_report_branches_exclude']) {
							$curr_branch_code = $b['code'];
							if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
								//print "$curr_branch_code skipped<br />";
								continue;
							}
						}
						$this->branch_id_list[] = $bid;
					}
				}
			}elseif($this->branch_id){
				$this->branch_id_list[] = $this->branch_id;
			}else{
				foreach($this->branches as $bid=>$b){
					if($config['masterfile_branch_region'] && get_branch_code($bid) != "HQ" && !check_user_regions($bid)) continue;
					if ($config['sales_report_branches_exclude']) {
						$curr_branch_code = $b['code'];
						if (in_array($curr_branch_code,$config['sales_report_branches_exclude'])) {
							//print "$curr_branch_code skipped<br />";
							continue;
						}
					}
					$this->branch_id_list[] = $bid;
				}
			}
		}else{
			$this->branch_id = mi($sessioninfo['branch_id']);
			$this->branch_id_list[] = mi($sessioninfo['branch_id']);
		}
		
		/*print '<pre>';
		print_r($this->branch_id_list);
		print '</pre>';*/
		
		$this->date_from = $_REQUEST['from'];
		$this->date_to = $_REQUEST['to'];
		$this->date_from_key = date("Ymd", strtotime($this->date_from));
		$this->date_to_key = date("Ymd", strtotime($this->date_to));
		
        $this->cat_id  = mi($_REQUEST['cat_id']);
        $this->sku_type = $_REQUEST['sku_type'];
        $this->by_monthly = mi($_REQUEST['by_monthly']);
		$this->memb_filter = trim($_REQUEST['memb_filter']);
        
		//$config['enable_gst'] = 0;
		//$smarty->assign('config', $config);
		
		parent::__construct($title);
	}
	
	function _default(){
	    global $sessioninfo, $smarty;
	    
	    if($_REQUEST['subm']){
			if(!$_REQUEST['is_itemise_export']){
				$this->generate_report();
				if(isset($_REQUEST['output_excel'])){
					include_once("include/excelwriter.php");
					log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Daily Category Sales Report To Excel");

					Header('Content-Type: application/msexcel');
					Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
					print ExcelWriter::GetHeader();
					$smarty->assign('no_header_footer', 1);
				}
			}else{
				$this->export_itemise_info();
			}
		}
		
		
		$this->display();
	}
	
	private function init_selection(){
	    global $con, $smarty, $config, $con_multi;
	    
        if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
		if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));

		$q1 = $con_multi->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc($q1)){
			if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);
		$smarty->assign('branches',$this->branches);

		// load branch group items
		$q1 = $con_multi->sql_query("select bgi.*,branch.code,branch.description
		from branch_group_items bgi
		left join branch on bgi.branch_id=branch.id
		where branch.active=1
		order by branch.sequence, branch.code");
		while($r = $con_multi->sql_fetchassoc($q1)){
			if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['branch_id'])) continue;
			$this->branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
			$this->branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con_multi->sql_freeresult($q1);
		
		// load branch group header
		$con_multi->sql_query("select * from branch_group",false,false);
		while($r = $con_multi->sql_fetchassoc()){
			if(!$this->branches_group['items'][$r['id']]) continue;
            $this->branches_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		
		$smarty->assign('branches_group',$this->branches_group);
		
		$con_multi->sql_query("select * from sku_type order by code");
		$smarty->assign('sku_type', $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
	}
	
	private function generate_report(){
		global $con, $smarty, $con_multi;
		
		$options = array();
		if(isset($_REQUEST['output_excel']) && $_REQUEST['include_sub_cat']){
			$options['include_sub_cat'] = true;
		}	
		$this->generate_category_data($this->cat_id, $options);
		
		$report_title = array();
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$bcode[] = $this->branches[$bid]['code'];
			}
			$report_title[] = "Branch: ".join(', ', $bcode);
		}
		$sku_type = ($_REQUEST['sku_type']) ? $_REQUEST['sku_type'] : "All";
		$report_title[] = "SKU Type: ".$sku_type;
		$report_title[] = "Date: ".$this->date_from." to ".$this->date_to;
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		
		$this->assign_cat_report_data();
	}
	
	private function assign_cat_report_data($show_tpl = false){
		global $con, $smarty, $con_multi;
		
		if($this->by_monthly){  // report group by month
			$this->group_data_to_monthly($this->tb, $this->tb_total, $this->tb_grand_total);
		}
		
		$this->construct_tb_total();
		
		//print_r($this->do_tb_total);
		$smarty->assign('tb', $this->tb);
		$smarty->assign('tb_total', $this->tb_total);
		$smarty->assign('tb_grand_total', $this->tb_grand_total);
		$smarty->assign('do_tb_total', $this->do_tb_total);
		//$smarty->assign('mm_tb_total', $this->mm_tb_total);
		$smarty->assign('sc_data', $this->sc_data);
		$smarty->assign('dp_rcv_data', $this->dp_rcv_data);
		$smarty->assign('dp_used_data', $this->dp_used_data);
		$smarty->assign('rounding_data', $this->rounding_data);
		$smarty->assign('over_data', $this->over_data);
		$smarty->assign('cat_child_info', $this->cat_child_info);
		$smarty->assign('root_cat_info', $this->root_cat_info);
		$smarty->assign('curr_cat_info', $this->root_cat_info);
		$smarty->assign('uq_cols', $this->uq_cols);
		
		if($this->tb || $this->do_tb_total || $this->sc_data || $this->dp_rcv_data || $this->dp_used_data || $this->rounding_data || $this->over_data){
			$smarty->assign('got_data', 1);
		}
		
		if($_REQUEST['show_tran_count'])	$smarty->assign('show_tran_count', 1);
		//print_r($this->tb);
		//print_r($this->cat_child_info);
		
		if($show_tpl){
            $this->display('sales_report.category.table.tpl');
		}
	}
	
	private function construct_tb_total(){
		// construct total table
		//print_r($this->tb);
		if($this->tb){
			foreach($this->tb as $root_cat_id=>$cat_list){
				foreach($cat_list as $cat_id=>$cat){
					if($cat['data']){
						foreach($cat['data'] as $date_key=>$r){
						    $this->tb[$root_cat_id][$cat_id]['data']['total']['amt']+=$r['amt'];
							$this->tb[$root_cat_id][$cat_id]['data']['total']['discount']+=$r['discount'];
							$this->tb[$root_cat_id][$cat_id]['data']['total']['gross_amt']+=$r['discount'];
							$this->tb[$root_cat_id][$cat_id]['data']['total']['gross_amt']+=$r['amt'];
						    $this->tb[$root_cat_id][$cat_id]['data']['total']['cost']+=$r['cost'];
						    $this->tb[$root_cat_id][$cat_id]['data']['total']['qty']+=$r['qty'];
							$this->tb[$root_cat_id][$cat_id]['data']['total']['tax_amount']+=$r['tax_amount'];
						    $this->tb[$root_cat_id][$cat_id]['data']['total']['tran_count']+=$r['tran_count'];
						    $this->tb[$root_cat_id][$cat_id]['data']['total']['amt_inc_gst']+=$r['amt_inc_gst'];
						    
	                        $this->tb_total[$root_cat_id]['data'][$date_key]['amt'] += $r['amt'];
							$this->tb_total[$root_cat_id]['data'][$date_key]['discount'] += $r['discount'];
							$this->tb_total[$root_cat_id]['data'][$date_key]['gross_amt'] += $r['discount'];
							$this->tb_total[$root_cat_id]['data'][$date_key]['gross_amt'] += $r['amt'];
					    	$this->tb_total[$root_cat_id]['data'][$date_key]['cost'] += $r['cost'];
					    	$this->tb_total[$root_cat_id]['data'][$date_key]['qty'] += $r['qty'];
							$this->tb_total[$root_cat_id]['data'][$date_key]['tax_amount'] += $r['tax_amount'];
							$this->tb_total[$root_cat_id]['data'][$date_key]['amt_inc_gst'] += $r['amt_inc_gst'];
							
					    	$this->tb_total[$root_cat_id]['data']['total']['amt'] += $r['amt'];
							$this->tb_total[$root_cat_id]['data']['total']['discount'] += $r['discount'];
							$this->tb_total[$root_cat_id]['data']['total']['gross_amt'] += $r['discount'];
							$this->tb_total[$root_cat_id]['data']['total']['gross_amt'] += $r['amt'];
					    	$this->tb_total[$root_cat_id]['data']['total']['cost'] += $r['cost'];
					    	$this->tb_total[$root_cat_id]['data']['total']['qty'] += $r['qty'];
					    	$this->tb_total[$root_cat_id]['data']['total']['tax_amount'] += $r['tax_amount'];
					    	$this->tb_total[$root_cat_id]['data']['total']['amt_inc_gst'] += $r['amt_inc_gst'];
							
					    	$this->tb_total[$root_cat_id]['total']['total']['amt'] += $r['amt'];
							$this->tb_total[$root_cat_id]['total']['total']['discount'] += $r['discount'];
							$this->tb_total[$root_cat_id]['total']['total']['gross_amt'] += $r['discount'];
							$this->tb_total[$root_cat_id]['total']['total']['gross_amt'] += $r['amt'];
					    	$this->tb_total[$root_cat_id]['total']['total']['cost'] += $r['cost'];
					    	$this->tb_total[$root_cat_id]['total']['total']['qty'] += $r['qty'];
					    	$this->tb_total[$root_cat_id]['total']['total']['tax_amount'] += $r['tax_amount'];
					    	$this->tb_total[$root_cat_id]['total']['total']['amt_inc_gst'] += $r['amt_inc_gst'];
							
					    	$this->tb_total[$root_cat_id]['total'][$date_key]['amt'] += $r['amt'];
							$this->tb_total[$root_cat_id]['total'][$date_key]['discount'] += $r['discount'];
							$this->tb_total[$root_cat_id]['total'][$date_key]['gross_amt'] += $r['discount'];
							$this->tb_total[$root_cat_id]['total'][$date_key]['gross_amt'] += $r['amt'];
					    	$this->tb_total[$root_cat_id]['total'][$date_key]['cost'] += $r['cost'];
					    	$this->tb_total[$root_cat_id]['total'][$date_key]['qty'] += $r['qty'];
							$this->tb_total[$root_cat_id]['total'][$date_key]['tax_amount'] += $r['tax_amount'];
							$this->tb_total[$root_cat_id]['total'][$date_key]['amt_inc_gst'] += $r['amt_inc_gst'];
						}
					}
					
					if($cat['fm_data']){
						//print_r($cat['fm_data']);
						foreach($cat['fm_data'] as $date_key=>$r){
	                        $this->tb[$root_cat_id][$cat_id]['fm_data']['total']['amt']+=$r['amt'];
							$this->tb[$root_cat_id][$cat_id]['fm_data']['total']['discount']+=$r['discount'];
							$this->tb[$root_cat_id][$cat_id]['fm_data']['total']['gross_amt']+=$r['discount'];
							$this->tb[$root_cat_id][$cat_id]['fm_data']['total']['gross_amt']+=$r['amt'];
						    $this->tb[$root_cat_id][$cat_id]['fm_data']['total']['cost']+=$r['cost'];
						    $this->tb[$root_cat_id][$cat_id]['fm_data']['total']['qty']+=$r['qty'];
							$this->tb[$root_cat_id][$cat_id]['fm_data']['total']['tax_amount']+=$r['tax_amount'];
							$this->tb[$root_cat_id][$cat_id]['fm_data']['total']['tran_count']+=$r['tran_count'];
							$this->tb[$root_cat_id][$cat_id]['fm_data']['total']['amt_inc_gst']+= $r['amt_inc_gst'];
							
	                        $this->tb_total[$root_cat_id]['fm_data'][$date_key]['amt'] += $r['amt'];
							$this->tb_total[$root_cat_id]['fm_data'][$date_key]['discount'] += $r['discount'];
							$this->tb_total[$root_cat_id]['fm_data'][$date_key]['gross_amt'] += $r['discount'];
							$this->tb_total[$root_cat_id]['fm_data'][$date_key]['gross_amt'] += $r['amt'];
					    	$this->tb_total[$root_cat_id]['fm_data'][$date_key]['cost'] += $r['cost'];
					    	$this->tb_total[$root_cat_id]['fm_data'][$date_key]['qty'] += $r['qty'];
							$this->tb_total[$root_cat_id]['fm_data'][$date_key]['tax_amount'] += $r['tax_amount'];
							$this->tb_total[$root_cat_id]['fm_data'][$date_key]['amt_inc_gst'] += $r['amt_inc_gst'];
							
					    	$this->tb_total[$root_cat_id]['fm_data']['total']['amt'] += $r['amt'];
							$this->tb_total[$root_cat_id]['fm_data']['total']['discount'] += $r['discount'];
							$this->tb_total[$root_cat_id]['fm_data']['total']['gross_amt'] += $r['discount'];
							$this->tb_total[$root_cat_id]['fm_data']['total']['gross_amt'] += $r['amt'];
					    	$this->tb_total[$root_cat_id]['fm_data']['total']['cost'] += $r['cost'];
					    	$this->tb_total[$root_cat_id]['fm_data']['total']['qty'] += $r['qty'];
					    	$this->tb_total[$root_cat_id]['fm_data']['total']['tax_amount'] += $r['tax_amount'];
					    	$this->tb_total[$root_cat_id]['fm_data']['total']['amt_inc_gst'] += $r['amt_inc_gst'];
							
					    	$this->tb_total[$root_cat_id]['total'][$date_key]['amt'] += $r['amt'];
							$this->tb_total[$root_cat_id]['total'][$date_key]['discount'] += $r['discount'];
							$this->tb_total[$root_cat_id]['total'][$date_key]['gross_amt'] += $r['discount'];
							$this->tb_total[$root_cat_id]['total'][$date_key]['gross_amt'] += $r['amt'];
					    	$this->tb_total[$root_cat_id]['total'][$date_key]['cost'] += $r['cost'];
					    	$this->tb_total[$root_cat_id]['total'][$date_key]['qty'] += $r['qty'];
					    	$this->tb_total[$root_cat_id]['total'][$date_key]['tax_amount'] += $r['tax_amount'];
					    	$this->tb_total[$root_cat_id]['total'][$date_key]['amt_inc_gst'] += $r['amt_inc_gst'];
					    	
					    	$this->tb_total[$root_cat_id]['total']['total']['amt'] += $r['amt'];
							$this->tb_total[$root_cat_id]['total']['total']['discount'] += $r['discount'];
							$this->tb_total[$root_cat_id]['total']['total']['gross_amt'] += $r['discount'];
							$this->tb_total[$root_cat_id]['total']['total']['gross_amt'] += $r['amt'];
					    	$this->tb_total[$root_cat_id]['total']['total']['cost'] += $r['cost'];
					    	$this->tb_total[$root_cat_id]['total']['total']['qty'] += $r['qty'];
					    	$this->tb_total[$root_cat_id]['total']['total']['tax_amount'] += $r['tax_amount'];
					    	$this->tb_total[$root_cat_id]['total']['total']['amt_inc_gst'] += $r['amt_inc_gst'];
						}
					}
				}
			}
		}
		
		// add mix and match total
		/*if($this->mm_tb_total){
			// loop for each date
			foreach($this->uq_cols as $date_key => $r){
				if(isset($this->mm_tb_total[$date_key])){
					$this->tb_grand_total['total']['amt'] -= $this->mm_tb_total[$date_key]['amt'];
					$this->tb_grand_total[$date_key]['amt'] -= $this->mm_tb_total[$date_key]['amt'];
				}
			}
		}*/
		
		// add DO total
		if($this->do_tb_total){
			// loop for each date
			foreach($this->uq_cols as $date_key => $r){
				if(isset($this->do_tb_total[$date_key])){
					$this->tb_grand_total['total']['amt'] += $this->do_tb_total[$date_key]['amt'];
					$this->tb_grand_total['total']['discount'] += $this->do_tb_total[$date_key]['discount'];
					$this->tb_grand_total['total']['gross_amt'] += $this->do_tb_total[$date_key]['discount'];
					$this->tb_grand_total['total']['gross_amt'] += $this->do_tb_total[$date_key]['amt'];
					$this->tb_grand_total['total']['cost'] += $this->do_tb_total[$date_key]['cost'];
					$this->tb_grand_total['total']['qty'] += $this->do_tb_total[$date_key]['qty'];
					$this->tb_grand_total['total']['tax_amount'] += $this->do_tb_total[$date_key]['tax_amount'];
					$this->tb_grand_total['total']['amt_inc_gst'] += $this->do_tb_total[$date_key]['amt_inc_gst'];
					
					$this->tb_grand_total[$date_key]['qty'] += $this->do_tb_total[$date_key]['qty'];
					$this->tb_grand_total[$date_key]['amt'] += $this->do_tb_total[$date_key]['amt'];
					$this->tb_grand_total[$date_key]['discount'] += $this->do_tb_total[$date_key]['discount'];
					$this->tb_grand_total[$date_key]['gross_amt'] += $this->do_tb_total[$date_key]['discount'];
					$this->tb_grand_total[$date_key]['gross_amt'] += $this->do_tb_total[$date_key]['amt'];
					$this->tb_grand_total[$date_key]['cost'] += $this->do_tb_total[$date_key]['cost'];
					$this->tb_grand_total[$date_key]['tax_amount'] += $this->do_tb_total[$date_key]['tax_amount'];
					$this->tb_grand_total[$date_key]['amt_inc_gst'] += $this->do_tb_total[$date_key]['amt_inc_gst'];
				}
			}
		}
		
		// add service charge
		if($this->sc_data){
			// loop for each date
			foreach($this->uq_cols as $date_key => $r){
				if(isset($this->sc_data['data'][$date_key])){
					$this->tb_grand_total['total']['amt'] += $this->sc_data['data'][$date_key]['amt'];
					$this->tb_grand_total['total']['tax_amount'] += $this->sc_data['data'][$date_key]['tax_amount'];
					$this->tb_grand_total['total']['amt_inc_gst'] += $this->sc_data['data'][$date_key]['amt_inc_gst'];
					
					$this->tb_grand_total[$date_key]['amt'] += $this->sc_data['data'][$date_key]['amt'];
					$this->tb_grand_total[$date_key]['tax_amount'] += $this->sc_data['data'][$date_key]['tax_amount'];
					$this->tb_grand_total[$date_key]['amt_inc_gst'] += $this->sc_data['data'][$date_key]['amt_inc_gst'];
				}
			}
		}
		
		// add deposit receive data
		if($this->dp_rcv_data){
			// loop for each date
			foreach($this->uq_cols as $date_key => $r){
				if(isset($this->dp_rcv_data['data'][$date_key])){
					$this->tb_grand_total['total']['amt'] += $this->dp_rcv_data['data'][$date_key]['amt'];
					$this->tb_grand_total['total']['tax_amount'] += $this->dp_rcv_data['data'][$date_key]['tax_amount'];
					$this->tb_grand_total['total']['amt_inc_gst'] += $this->dp_rcv_data['data'][$date_key]['amt_inc_gst'];
					
					$this->tb_grand_total[$date_key]['amt'] += $this->dp_rcv_data['data'][$date_key]['amt'];
					$this->tb_grand_total[$date_key]['tax_amount'] += $this->dp_rcv_data['data'][$date_key]['tax_amount'];
					$this->tb_grand_total[$date_key]['amt_inc_gst'] += $this->dp_rcv_data['data'][$date_key]['amt_inc_gst'];
				}
			}
		}
		
		// add deposit used data
		if($this->dp_used_data){
			// loop for each date
			foreach($this->uq_cols as $date_key => $r){
				if(isset($this->dp_used_data['data'][$date_key])){
					$this->tb_grand_total['total']['amt'] += $this->dp_used_data['data'][$date_key]['amt'];
					$this->tb_grand_total['total']['tax_amount'] += $this->dp_used_data['data'][$date_key]['tax_amount'];
					$this->tb_grand_total['total']['amt_inc_gst'] += $this->dp_used_data['data'][$date_key]['amt_inc_gst'];
					
					$this->tb_grand_total[$date_key]['amt'] += $this->dp_used_data['data'][$date_key]['amt'];
					$this->tb_grand_total[$date_key]['tax_amount'] += $this->dp_used_data['data'][$date_key]['tax_amount'];
					$this->tb_grand_total[$date_key]['amt_inc_gst'] += $this->dp_used_data['data'][$date_key]['amt_inc_gst'];
				}
			}
		}
		
		// add rounding
		if($this->rounding_data){
			// loop for each date
			foreach($this->uq_cols as $date_key => $r){
				if(isset($this->rounding_data['data'][$date_key])){
					$this->tb_grand_total['total']['amt'] += $this->rounding_data['data'][$date_key]['amt'];
					//$this->tb_grand_total['total']['tax_amount'] += $this->rounding_data['data'][$date_key]['tax_amount'];
					$this->tb_grand_total['total']['amt_inc_gst'] += $this->rounding_data['data'][$date_key]['amt_inc_gst'];
					
					$this->tb_grand_total[$date_key]['amt'] += $this->rounding_data['data'][$date_key]['amt'];
					//$this->tb_grand_total[$date_key]['tax_amount'] += $this->rounding_data['data'][$date_key]['tax_amount'];
					$this->tb_grand_total[$date_key]['amt_inc_gst'] += $this->rounding_data['data'][$date_key]['amt_inc_gst'];
				}
			}
		}
		
		// add over
		if($this->over_data){
			// loop for each date
			foreach($this->uq_cols as $date_key => $r){
				if(isset($this->over_data['data'][$date_key])){
					$this->tb_grand_total['total']['amt'] += $this->over_data['data'][$date_key]['amt'];
					//$this->tb_grand_total['total']['tax_amount'] += $this->over_data['data'][$date_key]['tax_amount'];
					$this->tb_grand_total['total']['amt_inc_gst'] += $this->over_data['data'][$date_key]['amt_inc_gst'];
					
					$this->tb_grand_total[$date_key]['amt'] += $this->over_data['data'][$date_key]['amt'];
					//$this->tb_grand_total[$date_key]['tax_amount'] += $this->over_data['data'][$date_key]['tax_amount'];
					$this->tb_grand_total[$date_key]['amt_inc_gst'] += $this->over_data['data'][$date_key]['amt_inc_gst'];
				}
			}
		}
		
		// got grand total
		if($this->tb_grand_total && isset($this->tb_total[0])){
			// loop for each date
			foreach($this->uq_cols as $date_key => $r){
				$this->tb_grand_total['total']['amt'] += $this->tb_total[0]['total'][$date_key]['amt'];
				$this->tb_grand_total['total']['discount'] += $this->tb_total[0]['total'][$date_key]['discount'];
				$this->tb_grand_total['total']['gross_amt'] += $this->tb_total[0]['total'][$date_key]['discount'];
				$this->tb_grand_total['total']['gross_amt'] += $this->tb_total[0]['total'][$date_key]['amt'];
				$this->tb_grand_total['total']['cost'] += $this->tb_total[0]['total'][$date_key]['cost'];
				$this->tb_grand_total['total']['qty'] += $this->tb_total[0]['total'][$date_key]['qty'];
				$this->tb_grand_total['total']['tax_amount'] += $this->tb_total[0]['total'][$date_key]['tax_amount'];
				$this->tb_grand_total['total']['amt_inc_gst'] += $this->tb_total[0]['total'][$date_key]['amt_inc_gst'];
				
				$this->tb_grand_total[$date_key]['qty'] += $this->tb_total[0]['total'][$date_key]['qty'];
				$this->tb_grand_total[$date_key]['discount'] += $this->tb_total[0]['total'][$date_key]['discount'];
				$this->tb_grand_total[$date_key]['gross_amt'] += $this->tb_total[0]['total'][$date_key]['discount'];
				$this->tb_grand_total[$date_key]['gross_amt'] += $this->tb_total[0]['total'][$date_key]['amt'];
				$this->tb_grand_total[$date_key]['amt'] += $this->tb_total[0]['total'][$date_key]['amt'];
				$this->tb_grand_total[$date_key]['cost'] += $this->tb_total[0]['total'][$date_key]['cost'];
				$this->tb_grand_total[$date_key]['tax_amount'] += $this->tb_total[0]['total'][$date_key]['tax_amount'];
				$this->tb_grand_total[$date_key]['amt_inc_gst'] += $this->tb_total[0]['total'][$date_key]['amt_inc_gst'];
			}
		}
		
		//print_r($this->tb_total);
		//print_r($this->tb);
		//print_r($this->tb_total);
	}
	
	function generate_category_data($root_cat_id, $options = array()){
		global $con, $smarty, $sessioninfo,$config, $con_multi;
		
		$root_cat_id = mi($root_cat_id);
		$filter = $do_filter = $mm_filter = $common_fm_filter = $dc_filter = array();
		
		// establish report server connection
		/*if(!$con_multi){
			if(!$_REQUEST['use_report_server'])   $con_multi = $con;
			else	$con_multi= new mysql_multi();
		}*/
		
		//print "get cat id = $root_cat_id<br />";
		
		if(!$this->branch_id_list)  return;
		
		// generate date column data
		if(!$this->uq_cols)	$this->generate_header_date_label();
		
		if($root_cat_id){  // got category clicked
			$con_multi->sql_query("select * from category where id=$root_cat_id");
			$cat_info = $con_multi->sql_fetchassoc();
			$con_multi->sql_freeresult();
			
			if($cat_info['tree_str']!=''){  // generate category tree
			    $tree_str = $cat_info['tree_str'];
				$temp = str_replace(")(", ",",  str_replace("(0)", "", $tree_str));
				if($temp){
                    $con_multi->sql_query("select id,description from category where id in $temp order by level");
                    while ($r = $con_multi->sql_fetchassoc()){
                        $cat_info['cat_tree_info'][] = $r;
					}
				}
				$con_multi->sql_freeresult();				
			}
			
			$pf = "p".($cat_info['level']+1);
			$filter[] = "p".$cat_info['level']."=$root_cat_id";
			$do_filter[] = "p".$cat_info['level']."=$root_cat_id";
			$common_fm_filter[]= "p".$cat_info['level']."=$root_cat_id";
			//$uncat_name = $cat_info['description'];
			$uncat_name = '(Items directly under "'.$cat_info['description'].'")';
			
		}else{  // no select category, show all
            $pf = "p1";
			$uncat_name = 'Un-categorized';
			
			// root
			$cat_info['id'] = 0;
			$cat_info['level'] = 0;
		}
		$cat_lv = mi($cat_info['level']);
		
		// construct filter
		$filter[] = "tbl.date between ".ms($this->date_from)." and ".ms($this->date_to);
		$do_filter[] = "do.do_date between ".ms($this->date_from)." and ".ms($this->date_to);
		$mm_filter[] = "pmm.date between ".ms($this->date_from)." and ".ms($this->date_to);
		$dc_filter[] = "dc.date between ".ms($this->date_from)." and ".ms($this->date_to);
		
		//if ($sessioninfo['level']<1000){
		// remove the level checking due to it is wrong - Justin 2/28/2017 3:33 PM 
	    $filter[] = "cc.p2 in ($sessioninfo[department_ids])";
	    $common_fm_filter[]= "cc.p2 in ($sessioninfo[department_ids])";
		//}
		$tran_filter = $filter;	// clone filter for transaction query
		
		if($this->sku_type){
			$filter[]="tbl.sku_type=".ms($this->sku_type);
			$do_filter[]="sku.sku_type=".ms($this->sku_type);
	        $common_fm_filter[]= "sku.sku_type=".ms($this->sku_type);
		}
		$common_fm_filter[] = "(sku.is_fresh_market='yes' or (sku.is_fresh_market='inherit' and cc.is_fresh_market='yes'))";
	
	    if ($filter) $filter = "where ".join(' and ', $filter);
	    
	    // get all child category info
	    $con_multi->sql_query("select c.id,c.description, cm.branch_id, cm.markup
		from category c
		left join category_markup cm on c.id = cm.category_id
		where c.root_id=$root_cat_id or c.id=$root_cat_id");
	    while($r=$con_multi->sql_fetchrow()){
	    	$category_markup[$r['id']][$r['branch_id']] = $r['markup'];
	        $category[$r['id']] = $r['description'];
		}
		$con_multi->sql_freeresult();
		
		// get category sales
		$str_col = ",sum(tbl.qty) as qty, sum(tbl.amount) as amt, sum(tbl.cost) as cost, sum(tbl.tax_amount) as tax_amount,sum(tbl.disc_amt+ tbl.disc_amt2) as discount ";
		if($this->memb_filter == 'member'){
			// show member data only
			$str_col = ",sum(tbl.memb_qty) as qty, sum(tbl.memb_amt) as amt, sum(tbl.memb_cost) as cost, sum(tbl.memb_tax) as tax_amount,sum(tbl.memb_disc+ tbl.memb_disc2) as discount";
		}elseif($this->memb_filter == 'non_member'){
			$str_col = ",sum(tbl.qty-tbl.memb_qty) as qty, sum(tbl.amount-tbl.memb_amt) as amt, sum(tbl.cost-tbl.memb_cost) as cost, sum(tbl.tax_amount-tbl.memb_tax) as tax_amount,sum(tbl.disc_amt+tbl.disc_amt2-tbl.memb_disc-tbl.memb_disc2) as discount";
		}
		$sql = "select $pf as cat_id, tbl.date as dt $str_col
		from %s tbl
		left join category_cache cc using(category_id)
		$filter group by $pf, dt";
		
		
	    foreach($this->branch_id_list as $bid){
			if($config['masterfile_branch_region'] && !check_user_regions($bid)) continue;
            $query[] = sprintf($sql, 'category_sales_cache_b'.$bid);
		}
		
		if($query){
			$query = join(' union all ', $query);
			//print $query."<br>";
			
			// get category sales
			$q_cc = $con_multi->sql_query($query);
			if ($con_multi->sql_numrows($q_cc)>0){
				$cat_id_list = array();
				while($r = $con_multi->sql_fetchrow()){
					// skip if no data
					if(!$r['qty'] && !$r['amt'] && !$r['cost'] && !$r['tax_amount'])	continue;
					
					$date_key = date("Ymd", strtotime($r['dt']));
					$cat_id = mi($r['cat_id']);
					
					if(!in_array($cat_id, $cat_id_list))	$cat_id_list[] = $cat_id;
					
					$this->tb[$root_cat_id][$cat_id]['data'][$date_key]['amt'] += $r['amt'];
					$this->tb[$root_cat_id][$cat_id]['data'][$date_key]['discount'] += $r['discount'];
					$this->tb[$root_cat_id][$cat_id]['data'][$date_key]['gross_amt'] += $r['discount'];
					$this->tb[$root_cat_id][$cat_id]['data'][$date_key]['gross_amt'] += $r['amt'];
					$this->tb[$root_cat_id][$cat_id]['data'][$date_key]['cost'] += $r['cost'];
					$this->tb[$root_cat_id][$cat_id]['data'][$date_key]['qty'] += $r['qty'];
					$this->tb[$root_cat_id][$cat_id]['data'][$date_key]['tax_amount'] += $r['tax_amount'];
					//$this->tb[$root_cat_id][$cat_id]['data'][$date_key]['disc_amt2'] += $r['disc_amt2'];
					$this->tb[$root_cat_id][$cat_id]['data'][$date_key]['amt_inc_gst'] += $r['amt'] + $r['tax_amount'];
				}

				if($this->tb[$root_cat_id]){
					// loop for each category to assign cat id and description
					foreach (array_keys($this->tb[$root_cat_id]) as $cat_id){   
						$this->tb[$root_cat_id][$cat_id]['id'] = $cat_id;
						if (!$category[$cat_id]){   // unknow category id
							$this->tb[$root_cat_id][$cat_id]['have_subcat'] = false;
							$this->tb[$root_cat_id][$cat_id]['description'] = $uncat_name;
							$this->tb[$root_cat_id][$cat_id]['root_id'] = $root_cat_id;
						}
						else{
							$this->tb[$root_cat_id][$cat_id]['have_subcat'] = $this->check_have_subcat($cat_id);
							$this->tb[$root_cat_id][$cat_id]['description'] = $category[$cat_id];
						}
					}
				}	
			}
			$con_multi->sql_freeresult($q_cc);
		
			// get transaction count
			if($cat_lv>=0 && $cat_lv<2){
				//print $filter;
				if($this->memb_filter == 'member'){
					// show member data only
					$tran_filter[] = "tbl.member_no<>''";
				}elseif($this->memb_filter == 'non_member'){
					$tran_filter[] = "tbl.member_no=''";
				}
				if($tran_filter)	$tran_filter = "where ".join(' and ', $tran_filter);
				else	$tran_filter = '';
				
				foreach($this->branch_id_list as $bid){
					/*$sql = "select distinct tbl.date,tbl.counter_id,tbl.pos_id,$pf as cat_id
					from dept_trans_cache_b".$bid." tbl 
					left join category_cache cc on cc.category_id=tbl.department_id
					$tran_filter";*/
					$sql = "select tbl.date, $pf as cat_id, count(distinct tbl.date,tbl.counter_id,tbl.pos_id) as tran_count
					from dept_trans_cache_b".$bid." tbl 
					left join category_cache cc on cc.category_id=tbl.department_id
					$tran_filter
					group by date,cat_id";
					/*if($sessioninfo['u']=='wsatp'){
						print "$sql<br />";
						continue;
					}*/
					$con_multi->sql_query_false($sql, true);
					while($r = $con_multi->sql_fetchassoc()){
						$date_key = date("Ymd", strtotime($r['date']));
						$cat_id = mi($r['cat_id']);
					
						$this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['tran_count'] += $r['tran_count'];
					}
					$con_multi->sql_freeresult();
				}
				$smarty->assign('show_tran_count', 1);
			}
			
			//if($sessioninfo['u']=='wsatp'){
				//print_r($this->tb[$root_cat_id]);
			//}
			// got fresh market, check fresh market sales
			if($config['enable_fresh_market_sku'] && $this->tb && $config['enable_fresh_market_report_calculation']){
				$params = array();
				$params['filter'] = $common_fm_filter;
				$params['pf'] = $pf;
				
				foreach($this->branch_id_list as $bid){
					$params['branch_id'] = mi($bid);
					
					$fm_data = $this->get_fresh_market_data($params);
					//print_r($fm_data);exit;
					if($fm_data){
						// loop to reconstruct tb
						foreach($fm_data as $sku_id=>$r){
							foreach($r['pos'] as $date_key=>$pos){
								// remove qty , amt and cost from report if it is fresh market item
								$this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['cost'] -= $pos['default_cost'];
								$this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['amt'] -= $pos['amt'];
								$this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['discount'] -= $pos['discount'];
								$this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['gross_amt'] -= $pos['discount'];
								$this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['gross_amt'] -= $pos['amt'];
								$this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['qty'] -= $pos['qty'];
								$this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['tax_amount'] -= $pos['tax_amount'];
								//$this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['disc_amt2'] -= $pos['disc_amt2'];
								$this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['amt_inc_gst'] -= $pos['amt_inc_gst'];
								
								$tran_count = $this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['tran_count'];
								
								// all zero, unset the array
								if(!round($this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['cost'],2) && !round($this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['amt'],2) && !round($this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['qty'],2)&&!round($this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['discount'],2) && !round($this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]['gross_amt'],2))    unset($this->tb[$root_cat_id][$r['cat_id']]['data'][$date_key]);
								
								// construct fresh market data
								$this->tb[$root_cat_id][$r['cat_id']]['fm_data'][$date_key]['qty'] += $pos['qty'];
								$this->tb[$root_cat_id][$r['cat_id']]['fm_data'][$date_key]['amt'] += $pos['amt'];
								$this->tb[$root_cat_id][$r['cat_id']]['fm_data'][$date_key]['discount'] += $pos['discount'];
								$this->tb[$root_cat_id][$r['cat_id']]['fm_data'][$date_key]['gross_amt'] += $pos['discount'];
								$this->tb[$root_cat_id][$r['cat_id']]['fm_data'][$date_key]['gross_amt'] += $pos['amt'];
								$this->tb[$root_cat_id][$r['cat_id']]['fm_data'][$date_key]['tax_amount'] += $pos['tax_amount'];
								//$this->tb[$root_cat_id][$r['cat_id']]['fm_data'][$date_key]['disc_amt2'] += $pos['disc_amt2'];
								$this->tb[$root_cat_id][$r['cat_id']]['fm_data'][$date_key]['amt_inc_gst'] += $pos['amt_inc_gst'];
								$this->tb[$root_cat_id][$r['cat_id']]['fm_data'][$date_key]['cost'] += $pos['fresh_market_cost'] ? $pos['fresh_market_cost'] : $pos['default_cost'];
								
								// clone transaction count
								if($tran_count){
									//print_r($fm_data);
									//print "fm tran = $tran_count<br />";
									$this->tb[$root_cat_id][$r['cat_id']]['fm_data'][$date_key]['tran_count'] = $tran_count;
								}
							}
						}
					}
					
					//print_r($fm_data);
				}
			}
			
			$root_per = isset($_REQUEST['root_per']) ? mf($_REQUEST['root_per']) : 100;
			
			$smarty->assign('root_per', $root_per);
			if(!$this->root_cat_info){
				$this->root_cat_info = $cat_info;
			}	
			
			if(isset($_REQUEST['is_fresh_market']) && $config['enable_fresh_market_sku']){
				if($_REQUEST['is_fresh_market'])    $smarty->assign('fresh_market_row_only', 1);
				else    $smarty->assign('normal_category_row_only', 1);
			}    
			
			// get child array
			if($cat_id_list){
				if($this->tb[$root_cat_id][0]){	// got un-category sales
					$this->cat_child_info[$root_cat_id][0] = $this->tb[$root_cat_id][0];	// un-category
					
					// only need master info, sales no need for child info array
					unset($this->cat_child_info[$root_cat_id][0]['data']);
					unset($this->cat_child_info[$root_cat_id][0]['fm_data']);
				}
				$q_child = $con_multi->sql_query("select * from category where root_id=$root_cat_id and id in (".join(',',$cat_id_list).")");
				while($r = $con_multi->sql_fetchassoc($q_child)){
					$this->cat_child_info[$root_cat_id][$r['id']] = $r;
				}
				$con_multi->sql_freeresult($q_child);
				
				// get sub cat sales
				if($cat_lv>=2 && $options['include_sub_cat']){	// at least department
					//print "root_cat_id = $root_cat_id<br>";
					//print_r($this->cat_child_info);
					
					if($this->cat_child_info[$root_cat_id]){
						foreach($this->cat_child_info[$root_cat_id] as $cat_id=>$cat){
							if(!$cat_id)	continue;
							
							// check got sub cat or not
							$con_multi->sql_query("select id from category where root_id=$cat_id limit 1");
							$got_sub_cat = $con_multi->sql_fetchassoc();
							$con_multi->sql_freeresult();
							
							if(!$got_sub_cat)	continue;
							
							// load sub cat
							$this->generate_category_data($cat_id, $options);
						}
					}
					
					$smarty->assign('included_sub_cat', 1);
				}
			}
		}
		
		if(!$root_cat_id){
			
			$do_filter[] = "do.approved = 1 and do.active = 1 and do.checkout = 1";
			if($config['sales_report_include_transfer_do']){
				$do_filter[] = "(case when do.do_type = 'transfer' then do.inv_no is not null and do.inv_no != '' else 1=1 end)";
			}else{
				$do_filter[] = "do.do_type in ('open', 'credit_sales')";
			}
			
			if ($do_filter) $do_filter = "where ".join(' and ', $do_filter);
			if ($mm_filter) $mm_filter = "where ".join(' and ', $mm_filter);
			if ($dc_filter) $dc_filter = "where ".join(' and ', $dc_filter);
			foreach($this->branch_id_list as $bid){
				if(!$this->memb_filter || $this->memb_filter == 'non_member'){
					// get DO cash & credit sales
					$sql = "select di.*, $pf as cat_id, di.sku_item_id, di.branch_id, di.cost, di.cost_price,
							((di.ctn*uom.fraction)+di.pcs) as qty, di.selling_price, do.do_markup, uom.fraction as uom_fraction,
							do.markup_type, do.do_date as dt,di.ctn,di.pcs,do.is_under_gst,di.gst_rate,di.item_discount,(IFNULL(di.item_discount_amount,0)+IFNULL(di.item_discount_amount2,0)) as discount 
							from do
							left join do_items di on di.do_id = do.id and di.branch_id = do.branch_id
							left join uom on uom.id = di.uom_id
							left join sku_items si on si.id = di.sku_item_id
							left join sku on sku.id = si.sku_id
							left join category_cache cc using(category_id)
							$do_filter and do.branch_id = ".mi($bid)."
							order by si.id";

					// establish report server connection
					$do_sql = $con_multi->sql_query($sql);
					if($con_multi->sql_numrows($do_sql)>0){
						while($r = $con_multi->sql_fetchrow($do_sql)){
							if($this->by_monthly) $date_key = date("Ym", strtotime($r['dt']));
							else $date_key = date("Ymd", strtotime($r['dt']));
							
							if($r['inv_line_gross_amt2'] > 0){
								$inv_gross_amt = $r['inv_line_gross_amt2'];
								$inv_gst_amt = $r['inv_line_gst_amt2'];
								$inv_amt = $r['inv_line_amt2'];
							}else{
								$inv_gross_amt = $r['line_gross_amt'];
								$inv_gst_amt = $r['line_gst_amt'];
								$inv_amt = $r['line_amt'];
							}
							$cost = ($r['qty']*$r['cost']);
							$this->do_tb_total[$date_key]['amt'] += $inv_gross_amt;
							$this->do_tb_total['amt'] += $inv_gross_amt;
							$this->do_tb_total[$date_key]['discount'] += $r['discount'];
							$this->do_tb_total['discount'] += $r['discount'];
							$this->do_tb_total[$date_key]['gross_amt'] += $r['discount'];
							$this->do_tb_total['gross_amt'] += $r['discount'];
							$this->do_tb_total[$date_key]['gross_amt'] += $inv_gross_amt;
							$this->do_tb_total['gross_amt'] += $inv_gross_amt;
							$this->do_tb_total[$date_key]['cost'] += $cost;
							$this->do_tb_total['cost'] += $cost;
							$this->do_tb_total[$date_key]['qty'] += $r['qty'];
							$this->do_tb_total['qty'] += $r['qty'];
							$this->do_tb_total[$date_key]['tax_amount'] += $inv_gst_amt;
							$this->do_tb_total['tax_amount'] += $inv_gst_amt;
							
							$this->do_tb_total[$date_key]['amt_inc_gst'] += $inv_amt;
							$this->do_tb_total['amt_inc_gst'] += $inv_amt;
						}
						//print_r($this->do_tb_total);
					}
					$con_multi->sql_freeresult($do_sql);
				}
				
				
				// get promo usage
				/*$sql = "select pmm.* 
						from pos_mix_match_usage pmm
						join pos_finalized pf on pf.date = pmm.date and pf.branch_id = pmm.branch_id and pf.finalized=1
						left join pos on pos.branch_id=pmm.branch_id and pos.counter_id=pmm.counter_id and pos.date=pmm.date and pos.id=pmm.pos_id
						$mm_filter and pos.cancel_status=0 and pmm.branch_id=".mi($bid);

				$mm_sql = $con_multi->sql_query($sql);
				
				$mm_list = array();
				while($r = $con_multi->sql_fetchassoc($mm_sql)){
					$daily_date_key = date("Ymd", strtotime($r['date']));
					
					if($this->by_monthly) $date_key = date("Ym", strtotime($r['date']));
					else $date_key = date("Ymd", strtotime($r['date']));
					
					if($this->daily_info[$daily_date_key]['got_disc2'])	continue;	// this date got discount2, skip mix and match
					
					$tran_key = $r['branch_id'].'_'.$r['date'].'_'.$r['counter_id'].'_'.$r['pos_id'];
					
					if(!isset($mm_list[$tran_key])){
						$$mm_list[$tran_key] = 1;
						$this->mm_tb_total['trans_count']++;
					}
					
					$this->mm_tb_total[$date_key]['amt'] += $r['amount'];
					$this->mm_tb_total['amt'] += $r['amount'];
				}
				$con_multi->sql_freeresult($mm_sql);*/
				
				// service charge / deposit
				$str_col = ", dc.service_charge_amt, dc.service_charge_gst_amt, dc.total_gst_amt, dc.deposit_rcv_amt, dc.deposit_used_amt, dc.deposit_rcv_gst_amt, dc.deposit_used_gst_amt, dc.rounding_amt, dc.over_amt";
				if($this->memb_filter == 'member'){
					// show member data only
					$str_col = ", dc.memb_service_charge_amt as service_charge_amt, dc.memb_service_charge_gst_amt as service_charge_gst_amt, dc.memb_total_gst_amt as total_gst_amt, dc.memb_deposit_rcv_amt as deposit_rcv_amt, dc.memb_deposit_used_amt as deposit_used_amt, dc.memb_deposit_rcv_gst_amt as deposit_rcv_gst_amt, dc.memb_deposit_used_gst_amt as deposit_used_gst_amt, dc.memb_rounding_amt as rounding_amt, dc.memb_over_amt as over_amt";
				}elseif($this->memb_filter == 'non_member'){
					$str_col = ", round(dc.service_charge_amt-dc.memb_service_charge_amt,2) as service_charge_amt, round(dc.service_charge_gst_amt-dc.memb_service_charge_gst_amt,2) as service_charge_gst_amt, round(dc.total_gst_amt-dc.memb_total_gst_amt,2) as total_gst_amt, round(dc.deposit_rcv_amt-dc.memb_deposit_rcv_amt,2) as deposit_rcv_amt, round(dc.deposit_used_amt-dc.memb_deposit_used_amt,2) as deposit_used_amt, round(dc.deposit_rcv_gst_amt-dc.memb_deposit_rcv_gst_amt,2) as deposit_rcv_gst_amt, round(dc.deposit_used_gst_amt-dc.memb_deposit_used_gst_amt,2) as deposit_used_gst_amt, round(dc.rounding_amt-dc.memb_rounding_amt,2) as rounding_amt, round(dc.over_amt-dc.memb_over_amt,2) as over_amt";
				}
				$q_dc = $con_multi->sql_query_false("select dc.date $str_col
				from daily_sales_cache_b".$bid." dc
				 ".$dc_filter);
				 if($q_dc){
					while($r = $con_multi->sql_fetchassoc($q_dc)){
						if($this->by_monthly) $date_key = date("Ym", strtotime($r['date']));
						else $date_key = date("Ymd", strtotime($r['date']));
						// service charge
						if($r['service_charge_amt']){
							$this->sc_data['data'][$date_key]['amt'] += $r['service_charge_amt'];
							$this->sc_data['data'][$date_key]['tax_amount'] += $r['service_charge_gst_amt'];
							$this->sc_data['data'][$date_key]['amt_inc_gst'] += $r['service_charge_amt'] + $r['service_charge_gst_amt'];
							$this->sc_data['total']['amt'] += $r['service_charge_amt'];
							$this->sc_data['total']['tax_amount'] += $r['service_charge_gst_amt'];
							$this->sc_data['total']['amt_inc_gst'] += $r['service_charge_amt'] + $r['service_charge_gst_amt'];
						}
						
						// deposit receive
						if($r['deposit_rcv_amt']){
							$this->dp_rcv_data['data'][$date_key]['amt'] += $r['deposit_rcv_amt'];
							$this->dp_rcv_data['data'][$date_key]['tax_amount'] += $r['deposit_rcv_gst_amt'];
							$this->dp_rcv_data['data'][$date_key]['amt_inc_gst'] += $r['deposit_rcv_amt'] + $r['deposit_rcv_gst_amt'];
							
							$this->dp_rcv_data['total']['amt'] += $r['deposit_rcv_amt'];
							$this->dp_rcv_data['total']['tax_amount'] += $r['deposit_rcv_gst_amt'];
							$this->dp_rcv_data['total']['amt_inc_gst'] += $r['deposit_rcv_amt'] + $r['deposit_rcv_gst_amt'];
						}
						
						// deposit used
						if($r['deposit_used_amt']){
							$this->dp_used_data['data'][$date_key]['amt'] += $r['deposit_used_amt']*-1;
							$this->dp_used_data['data'][$date_key]['tax_amount'] += $r['deposit_used_gst_amt']*-1;
							$this->dp_used_data['data'][$date_key]['amt_inc_gst'] += ($r['deposit_used_amt'] + $r['deposit_used_gst_amt'])*-1;
							
							$this->dp_used_data['total']['amt'] += $r['deposit_used_amt']*-1;
							$this->dp_used_data['total']['tax_amount'] += $r['deposit_used_gst_amt']*-1;
							$this->dp_used_data['total']['amt_inc_gst'] += ($r['deposit_used_amt'] + $r['deposit_used_gst_amt'])*-1;
						}
						
						if($r['rounding_amt']){
							$this->rounding_data['data'][$date_key]['amt'] += $r['rounding_amt'];
							$this->rounding_data['data'][$date_key]['amt_inc_gst'] += $r['rounding_amt'];
							
							$this->rounding_data['total']['amt'] += $r['rounding_amt'];
							$this->rounding_data['total']['amt_inc_gst'] += $r['rounding_amt'];
						}
						
						if($r['over_amt']){
							$this->over_data['data'][$date_key]['amt'] += $r['over_amt'];
							$this->over_data['data'][$date_key]['amt_inc_gst'] += $r['over_amt'];
							
							$this->over_data['total']['amt'] += $r['over_amt'];
							$this->over_data['total']['amt_inc_gst'] += $r['over_amt'];
						}
					}
					
				 }
				 $con_multi->sql_freeresult($q_dc);
			}
		}
		
		//print_r($this->tb);
		
	}
	
	private function check_have_subcat($id){
		global $con_multi;
		$con_multi->sql_query("select id from category where root_id=$id limit 1");
		$c = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		if ($c) return true;
		return false;
	}
	
	private function generate_header_date_label(){
	    global $smarty;
	    
        $d1 = strtotime($this->date_from);
		$d2 = strtotime($this->date_to);

		$uq_cols = array();
		while($d1<=$d2)
		{
		    $temp = array('y'=>date('Y', $d1), 'm'=>mi(date('m', $d1)));
		    if($this->by_monthly){
	            $key = date('Ym', $d1);
			}else{
			    $key = date('Ymd', $d1);
	            $temp['d'] = date('d', $d1);
			}

		    $uq_cols[$key] = $temp;
			$d1 += 86400;
		}
		$this->uq_cols = $uq_cols;
		$smarty->assign('uq_cols', $this->uq_cols);
	}
	
	function ajax_load_category(){
        $this->generate_category_data($this->cat_id);
        $this->assign_cat_report_data(true);
	}
	
	function ajax_load_sku(){
	    global $con, $smarty, $sessioninfo, $config, $con_multi;
	    
	    if(!$this->branch_id_list)  die("No branch selected.");
	    
	    $this->generate_header_date_label();
	    
	    $direct_under_cat = mi($_REQUEST['direct_under_cat']);
	    
		if($_REQUEST['is_itemise_export']){
			if($_REQUEST['itemise_cat_id']) $this->cat_id = $_REQUEST['itemise_cat_id'];
			if($_REQUEST['itemise_direct_under_cat']) $direct_under_cat = $_REQUEST['itemise_direct_under_cat'];
			if($_REQUEST['itemise_is_fresh_market']) $_REQUEST['is_fresh_market'] = $_REQUEST['itemise_is_fresh_market'];
		}
		
        if (!$this->cat_id){    // showing uncategory sku
			$cat_info['description'] = 'Uncategorized';
   			$filter[] = "p0 is null";
   			$common_fm_filter[] = "p0 is null";
		}
		else{
   			$con_multi->sql_query("select id,level,description from category where id=$this->cat_id");
			$cat_info = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$pf = "p".($cat_info['level']+1);

			$filter[] = "p".$cat_info['level']."=$this->cat_id";
			$common_fm_filter[] = "p".$cat_info['level']."=$this->cat_id";
			
			if($sessioninfo['level']<9999){
                $filter[] = "p2 in ($sessioninfo[department_ids])";
                $common_fm_filter[] = "p2 in ($sessioninfo[department_ids])";
			}
			
			if($direct_under_cat){
				$filter[] = "sku.category_id=".mi($this->cat_id);
			}
		}
		
		$filter[] = "tbl.date between ".ms($this->date_from)." and ".ms($this->date_to);
		if($this->sku_type){
			$filter[] = "sku.sku_type=".ms($this->sku_type);
			$common_fm_filter[] = "sku.sku_type=".ms($this->sku_type);
		}
		$common_fm_filter[] = "(sku.is_fresh_market='yes' or (sku.is_fresh_market='inherit' and cc.is_fresh_market='yes'))";

		if(isset($_REQUEST['is_fresh_market']) && $config['enable_fresh_market_sku'] && $config['enable_fresh_market_report_calculation']){
		    if($_REQUEST['is_fresh_market'])    $fm_only = true;
		    else    $normal_only = true;
		}
		
		if($filter) $filter = "where ".join(' and ', $filter);
		
		$str_col = ", sum(tbl.qty) as qty, sum(tbl.amount) as amt, sum(tbl.cost) as cost, sum(tbl.fresh_market_cost) as fresh_market_cost, sum(tbl.tax_amount) as tax_amount,sum(tbl.disc_amt+tbl.disc_amt2) as discount";
		if($this->memb_filter == 'member'){
			// show member data only
			$str_col = ", sum(tbl.memb_qty) as qty, sum(tbl.memb_amt) as amt, sum(tbl.memb_cost) as cost, sum(tbl.memb_fm_cost) as fresh_market_cost, sum(tbl.memb_tax) as tax_amount,sum(tbl.memb_disc+tbl.memb_disc2) as discount";
		}elseif($this->memb_filter == 'non_member'){
			$str_col = ", sum(tbl.qty-tbl.memb_qty) as qty, sum(tbl.amount-tbl.memb_amt) as amt, sum(tbl.cost-tbl.memb_cost) as cost, sum(tbl.fresh_market_cost-tbl.memb_fm_cost) as fresh_market_cost, sum(tbl.tax_amount-tbl.memb_tax) as tax_amount,sum(tbl.disc_amt+tbl.disc_amt2-tbl.memb_disc-tbl.memb_disc2) as discount";
		}
		$sql = "select %d as bid, tbl.sku_item_id, if(sku.is_fresh_market='inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market, tbl.date as dt, si.sku_item_code,si.description, si.sku_id, si.artno,si.mcode $str_col
			from %s tbl
			left join sku_items si on tbl.sku_item_id = si.id
			left join sku on si.sku_id = sku.id
			left join category_cache cc on sku.category_id = cc.category_id
			$filter
			group by tbl.sku_item_id, dt";
		foreach($this->branch_id_list as $bid){
            $query[] = sprintf($sql, $bid, 'sku_items_sales_cache_b'.$bid);
		}
		$query = join(' union all ', $query);
		//print $query;
		//if(!$_REQUEST['use_report_server'])   $con_multi = $con;
		//else	$con_multi= new mysql_multi();  // use report server
		$con_multi->sql_query($query);
		
		$count_bid = count($this->branch_id_list);
		while($r = $con_multi->sql_fetchrow()){
		    if($config['enable_fresh_market_sku']){
                if($fm_only && $r['is_fresh_market']!='yes')    continue;   // only fm data
		    	if($normal_only && $r['is_fresh_market']=='yes')    continue;   // only normal data
			}
		    
			if(!$r['qty'] && !$r['amt'] && !$r['cost'] && !$r['tax_amount'] && !$r['discount'])	continue;
		    
			$date_key = date("Ymd", strtotime($r['dt']));

			$tb[$r['sku_item_id']]['info']['sku_item_code'] = $r['sku_item_code'];
			$tb[$r['sku_item_id']]['info']['artno'] = $r['artno'];		
			$tb[$r['sku_item_id']]['info']['mcode'] = $r['mcode'];
			$tb[$r['sku_item_id']]['info']['description'] = $r['description'];
			$tb[$r['sku_item_id']]['info']['sku_id'] = $r['sku_id'];
			$tb[$r['sku_item_id']]['info']['is_fresh_market'] = $r['is_fresh_market'] == 'yes' ? 1 : 0;
			
	        $tb[$r['sku_item_id']]['data'][$date_key]['qty'] += $r['qty'];
			$tb[$r['sku_item_id']]['data'][$date_key]['discount'] += $r['discount'];
			$tb[$r['sku_item_id']]['data'][$date_key]['gross_amt'] += $r['discount'];
			$tb[$r['sku_item_id']]['data'][$date_key]['gross_amt'] += $r['amt'];
	        $tb[$r['sku_item_id']]['data'][$date_key]['amt'] += $r['amt'];
	        
	        if($config['enable_fresh_market_sku'] && $r['is_fresh_market']=='yes' && $config['enable_fresh_market_report_calculation']){
                $cost = $r['fresh_market_cost']? $r['fresh_market_cost'] : $r['cost'];
                if($count_bid==1){
					$tb[$r['sku_item_id']]['data'][$date_key]['cost_indicator'] = $r['fresh_market_cost'] ? 'fresh_market_cost' : 'grn_cost';
				}     
			}else{
                $cost = $r['cost'];
			}
	        $tb[$r['sku_item_id']]['data'][$date_key]['cost'] += $cost;
			$tb[$r['sku_item_id']]['data'][$date_key]['tax_amount'] += $r['tax_amount'];
			$tb[$r['sku_item_id']]['data'][$date_key]['amt_inc_gst'] += $r['amt'] + $r['tax_amount'];
	        
	        $tb[$r['sku_item_id']]['data']['total']['qty'] += $r['qty'];
			$tb[$r['sku_item_id']]['data']['total']['discount'] += $r['discount'];
			$tb[$r['sku_item_id']]['data']['total']['gross_amt'] += $r['discount'];
			$tb[$r['sku_item_id']]['data']['total']['gross_amt'] += $r['amt'];
	        $tb[$r['sku_item_id']]['data']['total']['amt'] += $r['amt'];
	        $tb[$r['sku_item_id']]['data']['total']['cost'] += $cost;
			$tb[$r['sku_item_id']]['data']['total']['tax_amount'] += $r['tax_amount'];
			$tb[$r['sku_item_id']]['data']['total']['amt_inc_gst'] += $r['amt'] + $r['tax_amount'];
		}
		$con_multi->sql_freeresult();
		//print_r($tb);exit;
        $tb_total = array();
		if($tb){
			$sid_list = array();
			foreach($tb as $sid=>$sku_items){
				$sid_list[$sid] = $sid;
			    if($sku_items['data']){
                    foreach($sku_items['data'] as $date_key=>$r){
                        if($date_key=='total')  continue;   // skip total row
				    	$tb_total['data'][$date_key]['amt'] += $r['amt'];
				    	$tb_total['data'][$date_key]['cost'] += $r['cost'];
						$tb_total['data'][$date_key]['discount'] += $r['discount'];
						$tb_total['data'][$date_key]['gross_amt'] += $r['discount'];
						$tb_total['data'][$date_key]['gross_amt'] += $r['amt'];
				    	$tb_total['data'][$date_key]['qty'] += $r['qty'];
				    	$tb_total['data'][$date_key]['tax_amount'] += $r['tax_amount'];
				    	$tb_total['data'][$date_key]['amt_inc_gst'] += $r['amt_inc_gst'];

                        $tb_total['data']['total']['amt'] += $r['amt'];
				    	$tb_total['data']['total']['cost'] += $r['cost'];
						$tb_total['data']['total']['discount'] += $r['discount'];
						$tb_total['data']['total']['gross_amt'] += $r['discount'];
						$tb_total['data']['total']['gross_amt'] += $r['amt'];
				    	$tb_total['data']['total']['qty'] += $r['qty'];
				    	$tb_total['data']['total']['tax_amount'] += $r['tax_amount'];
				    	$tb_total['data']['total']['amt_inc_gst'] += $r['amt_inc_gst'];
					}
				}
			}
			if(count($this->branch_id_list)==1 && $config['enable_fresh_market_report_calculation']){	// single branch
				// find last FM stock take
				if($config['enable_fresh_market_sku'] && $fm_only && $sid_list){
					// get parent sku item id
					$parent_sid_list = array();
					foreach($sid_list as $sid){
						$sku_id = mi($tb[$sid]['info']['sku_id']);
						if(!isset($parent_sid_list[$sku_id])){
							$con_multi->sql_query("select id from sku_items where sku_id=$sku_id and is_parent=1 order by id limit 1");
							$tmp = $con_multi->sql_fetchassoc();
							$con_multi->sql_freeresult();
							$parent_sid_list[$sku_id] = mi($tmp['id']);
						}
					}
					
					$bid = mi($this->branch_id_list[0]);
					
					$sql = "select si.sku_id as sku_id, max(sc.date) as last_fm_sc_date 
from stock_check sc 
left join sku_items si on si.sku_item_code=sc.sku_item_code
where sc.branch_id=$bid and si.id in (".join(',', $parent_sid_list).") and sc.is_fresh_market=1 and sc.date<=".ms($this->date_to)."
group by sku_id";
					//print $sql;
					$q_lsc = $con_multi->sql_query($sql);
					$sku_last_fm_sc = array();
					while($r = $con_multi->sql_fetchassoc($q_lsc)){
						$sku_last_fm_sc[$r['sku_id']]['last_fm_sc_date'] = $r['last_fm_sc_date'];
					}
					$con_multi->sql_freeresult($q_lsc);
					
					// assign last fm stock take to items
					foreach($tb as $sid=>$r){
						$sku_id = mi($r['info']['sku_id']);
						$tb[$sid]['info']['last_fm_sc_date'] = $sku_last_fm_sc[$sku_id]['last_fm_sc_date'];
					}
				}
			}
		}

        if($this->by_monthly){  // report group by month
			$this->group_sku_data_to_monthly($tb, $tb_total);
		}
		
        $root_id = $this->cat_id;
		$root_per = isset($_REQUEST['root_per']) ? mf($_REQUEST['root_per']) : 100;

		$smarty->assign('root_per', $root_per);
		$smarty->assign('root_id', $root_id);
		$smarty->assign('is_fresh_market', $_REQUEST['is_fresh_market']);
		$smarty->assign('direct_under_cat', $direct_under_cat);
		$smarty->assign('is_itemise_export', $_REQUEST['is_itemise_export']);
		//print_r($tb);
		$smarty->assign('tb', $tb);
		$smarty->assign('tb_total', $tb_total);
		$smarty->assign('fm_only', $fm_only);
		$smarty->assign('branch_id_list', $this->branch_id_list);
		$smarty->assign('itemise_type', 'sku');
		
		$this->display('sales_report.category.sku_table.tpl');
	}
	
	private function group_data_to_monthly(&$tb, &$tb_total, &$tb_grand_total = array()){
	    //print_r($tb_total);
        if($tb){    // group category data
        	foreach($tb as $root_cat_id=>$cat_list){
				foreach($cat_list as $id=>$cat){
					if($cat['data']){
					    $new_data = array();
	                    foreach($cat['data'] as $date_key=>$r){ // loop for daily data to convert to monthly data
	                        // no need skip, since 0-4, and and 5-6 = total
	                        //if($date_key=='total')  continue;   // skip total row
							$ym = substr($date_key, 0, 4).substr($date_key, 4, 2);  // make year month key
							$new_data[$ym]['amt'] += $r['amt'];
							$new_data[$ym]['discount'] += $r['discount'];
							$new_data[$ym]['gross_amt'] += $r['discount'];
							$new_data[$ym]['gross_amt'] += $r['amt'];
							$new_data[$ym]['cost'] += $r['cost'];
							$new_data[$ym]['qty'] += $r['qty'];
							$new_data[$ym]['tax_amount'] += $r['tax_amount'];
							$new_data[$ym]['tran_count'] += $r['tran_count'];
							$new_data[$ym]['amt_inc_gst'] += $r['amt_inc_gst'];
							
							///$new_data['total']['amt'] += $r['amt'];
							//$new_data['total']['cost'] += $r['cost'];
							//$new_data['total']['qty'] += $r['qty'];
						}
						$tb[$root_cat_id][$id]['data'] = $new_data;  // replace the daily data to monthly data
						unset($new_data);
					}
					
					// fresh market
					if($cat['fm_data']){
					    $new_data = array();
	                    foreach($cat['fm_data'] as $date_key=>$r){ // loop for daily data to convert to monthly data
	                        // no need skip, since 0-4, and and 5-6 = total
	                        //if($date_key=='total')  continue;   // skip total row
							$ym = substr($date_key, 0, 4).substr($date_key, 4, 2);  // make year month key
							$new_data[$ym]['amt'] += $r['amt'];
							$new_data[$ym]['discount'] += $r['discount'];
							$new_data[$ym]['gross_amt'] += $r['discount'];
							$new_data[$ym]['gross_amt'] += $r['amt'];
							$new_data[$ym]['cost'] += $r['cost'];
							$new_data[$ym]['qty'] += $r['qty'];
							$new_data[$ym]['tax_amount'] += $r['tax_amount'];
							$new_data[$ym]['tran_count'] += $r['tran_count'];
							$new_data[$ym]['amt_inc_gst'] += $r['amt_inc_gst'];
							
							//$new_data['total']['amt'] += $r['amt'];
							//$new_data['total']['cost'] += $r['cost'];
							//$new_data['total']['qty'] += $r['qty'];
						}
						$tb[$root_cat_id][$id]['fm_data'] = $new_data;  // replace the daily data to monthly data
						unset($new_data);
					}
				}
			}
		}

        //$tb_total['total'] = array();
        //print_r($tb_total);
        if($tb_total){
			foreach($tb_total as $root_cat_id=>$tmp_data){
				$tb_total[$root_cat_id]['total'] = array();
				
				if($tmp_data['data']){  // group total row data
				    $new_data = array();    
					foreach($tmp_data['data'] as $date_key=>$r){
					    // no need skip, since 0-4, and and 5-6 = total
					    //if($date_key=='total')  continue;   // skip total row
		                $ym = substr($date_key, 0, 4).substr($date_key, 4, 2);  // make year month key
		                $new_data[$ym]['amt'] += $r['amt'];
						$new_data[$ym]['discount'] += $r['discount'];
						$new_data[$ym]['gross_amt'] += $r['discount'];
						$new_data[$ym]['gross_amt'] += $r['amt'];
						$new_data[$ym]['cost'] += $r['cost'];
						$new_data[$ym]['qty'] += $r['qty'];
						$new_data[$ym]['tax_amount'] += $r['tax_amount'];
						$new_data[$ym]['amt_inc_gst'] += $r['amt_inc_gst'];
						
						
						$tb_total[$root_cat_id]['total'][$ym]['amt'] += $r['amt'];
						$tb_total[$root_cat_id]['total'][$ym]['discount'] += $r['discount'];
						$tb_total[$root_cat_id]['total'][$ym]['gross_amt'] += $r['discount'];
						$tb_total[$root_cat_id]['total'][$ym]['gross_amt'] += $r['amt'];
						$tb_total[$root_cat_id]['total'][$ym]['cost'] += $r['cost'];
						$tb_total[$root_cat_id]['total'][$ym]['qty'] += $r['qty'];
						$tb_total[$root_cat_id]['total'][$ym]['tax_amount'] += $r['tax_amount'];
						$tb_total[$root_cat_id]['total'][$ym]['amt_inc_gst'] += $r['amt_inc_gst'];
					}
					$tb_total[$root_cat_id]['data'] = $new_data;
					unset($new_data);
				}
				
				if($tmp_data['fm_data']){  // group total row data
				    $new_data = array();
					foreach($tmp_data['fm_data'] as $date_key=>$r){
					    // no need skip, since 0-4, and and 5-6 = total
					    //if($date_key=='total')  continue;   // skip total row
		                $ym = substr($date_key, 0, 4).substr($date_key, 4, 2);  // make year month key
		                $new_data[$ym]['amt'] += $r['amt'];
						$new_data[$ym]['discount'] += $r['discount'];
						$new_data[$ym]['gross_amt'] += $r['discount'];
						$new_data[$ym]['gross_amt'] += $r['amt'];
						$new_data[$ym]['cost'] += $r['cost'];
						$new_data[$ym]['qty'] += $r['qty'];
						$new_data[$ym]['tax_amount'] += $r['tax_amount'];
						$new_data[$ym]['amt_inc_gst'] += $r['amt_inc_gst'];
						
						$tb_total[$root_cat_id]['total'][$ym]['amt'] += $r['amt'];
						$tb_total[$root_cat_id]['total'][$ym]['discount'] += $r['discount'];
						$tb_total[$root_cat_id]['total'][$ym]['gross_amt'] += $r['discount'];
						$tb_total[$root_cat_id]['total'][$ym]['gross_amt'] += $r['amt'];
						$tb_total[$root_cat_id]['total'][$ym]['cost'] += $r['cost'];
						$tb_total[$root_cat_id]['total'][$ym]['qty'] += $r['qty'];
						$tb_total[$root_cat_id]['total'][$ym]['tax_amount'] += $r['tax_amount'];
						$tb_total[$root_cat_id]['total'][$ym]['amt_inc_gst'] += $r['amt_inc_gst'];
					}
					$tb_total[$root_cat_id]['fm_data'] = $new_data;
					unset($new_data);
				}
			}
		}
		
		// grand total
		if($tb_grand_total){
			//print_r($tb_grand_total);
			$new_data = array();
			$new_data['total'] = $tb_grand_total['total'];
			unset($tb_grand_total['total']);
			foreach($tb_grand_total as $date_key => $r){
				$ym = substr($date_key, 0, 4).substr($date_key, 4, 2);  // make year month key
				$new_data[$ym]['amt'] += $r['amt'];
				$new_data[$ym]['discount'] += $r['discount'];
				$new_data[$ym]['gross_amt'] += $r['discount'];
				$new_data[$ym]['gross_amt'] += $r['amt'];
				$new_data[$ym]['cost'] += $r['cost'];
				$new_data[$ym]['qty'] += $r['qty'];
				$new_data[$ym]['tax_amount'] += $r['tax_amount'];
				$new_data[$ym]['amt_inc_gst'] += $r['amt_inc_gst'];
			}
			$tb_grand_total = $new_data;
			unset($new_data);
		}
	}
	
	private function group_sku_data_to_monthly(&$tb, &$tb_total){
		if($tb){
			foreach($tb as $id=>$cat){
				if($cat['data']){
				    $new_data = array();
	                  foreach($cat['data'] as $date_key=>$r){ // loop for daily data to convert to monthly data
	                      // no need skip, since 0-4, and and 5-6 = total
	                      //if($date_key=='total')  continue;   // skip total row
						$ym = substr($date_key, 0, 4).substr($date_key, 4, 2);  // make year month key
						$new_data[$ym]['amt'] += $r['amt'];
						$new_data[$ym]['discount'] += $r['discount'];
						$new_data[$ym]['gross_amt'] += $r['discount'];
						$new_data[$ym]['gross_amt'] += $r['amt'];
						$new_data[$ym]['cost'] += $r['cost'];
						$new_data[$ym]['qty'] += $r['qty'];
						$new_data[$ym]['tax_amount'] += $r['tax_amount'];
						$new_data[$ym]['amt_inc_gst'] += $r['amt_inc_gst'];
						
						///$new_data['total']['amt'] += $r['amt'];
						//$new_data['total']['cost'] += $r['cost'];
						//$new_data['total']['qty'] += $r['qty'];
					}
					$tb[$id]['data'] = $new_data;  // replace the daily data to monthly data
					unset($new_data);
				}
				
				// fresh market
				if($cat['fm_data']){
				    $new_data = array();
	                  foreach($cat['fm_data'] as $date_key=>$r){ // loop for daily data to convert to monthly data
	                      // no need skip, since 0-4, and and 5-6 = total
	                      //if($date_key=='total')  continue;   // skip total row
						$ym = substr($date_key, 0, 4).substr($date_key, 4, 2);  // make year month key
						$new_data[$ym]['amt'] += $r['amt'];
						$new_data[$ym]['discount'] += $r['discount'];
						$new_data[$ym]['gross_amt'] += $r['discount'];
						$new_data[$ym]['gross_amt'] += $r['amt'];
						$new_data[$ym]['cost'] += $r['cost'];
						$new_data[$ym]['qty'] += $r['qty'];
						$new_data[$ym]['tax_amount'] += $r['tax_amount'];
						$new_data[$ym]['amt_inc_gst'] += $r['amt_inc_gst'];
						
						//$new_data['total']['amt'] += $r['amt'];
						//$new_data['total']['cost'] += $r['cost'];
						//$new_data['total']['qty'] += $r['qty'];
					}
					$tb[$id]['fm_data'] = $new_data;  // replace the daily data to monthly data
					unset($new_data);
				}
			}
		}
		
		if($tb_total['data']){  // group total row data
		    $new_data = array();
			foreach($tb_total['data'] as $date_key=>$r){
			    // no need skip, since 0-4, and and 5-6 = total
			    //if($date_key=='total')  continue;   // skip total row
                $ym = substr($date_key, 0, 4).substr($date_key, 4, 2);  // make year month key
                $new_data[$ym]['amt'] += $r['amt'];
				$new_data[$ym]['discount'] += $r['discount'];
				$new_data[$ym]['gross_amt'] += $r['discount'];
				$new_data[$ym]['gross_amt'] += $r['amt'];
				$new_data[$ym]['cost'] += $r['cost'];
				$new_data[$ym]['qty'] += $r['qty'];
				$new_data[$ym]['tax_amount'] += $r['tax_amount'];
				$new_data[$ym]['amt_inc_gst'] += $r['amt_inc_gst'];
				
				$tb_total['total'][$ym]['amt'] += $r['amt'];
				$tb_total['total'][$ym]['discount'] += $r['discount'];
				$tb_total['total'][$ym]['gross_amt'] += $r['discount'];
				$tb_total['total'][$ym]['gross_amt'] += $r['amt'];
				$tb_total['total'][$ym]['cost'] += $r['cost'];
				$tb_total['total'][$ym]['qty'] += $r['qty'];
				$tb_total['total'][$ym]['tax_amount'] += $r['tax_amount'];
				$tb_total['total'][$ym]['amt_inc_gst'] += $r['amt_inc_gst'];
			}
			$tb_total['data'] = $new_data;
			unset($new_data);
		}
		
		if($tb_total['fm_data']){  // group total row data
		    $new_data = array();
			foreach($tb_total['fm_data'] as $date_key=>$r){
			    // no need skip, since 0-4, and and 5-6 = total
			    //if($date_key=='total')  continue;   // skip total row
                $ym = substr($date_key, 0, 4).substr($date_key, 4, 2);  // make year month key
                $new_data[$ym]['amt'] += $r['amt'];
				$new_data[$ym]['discount'] += $r['discount'];
				$new_data[$ym]['gross_amt'] += $r['discount'];
				$new_data[$ym]['gross_amt'] += $r['amt'];
				$new_data[$ym]['cost'] += $r['cost'];
				$new_data[$ym]['qty'] += $r['qty'];
				$new_data[$ym]['tax_amount'] += $r['tax_amount'];
				$new_data[$ym]['amt_inc_gst'] += $r['amt_inc_gst'];
				
				$tb_total['total'][$ym]['amt'] += $r['amt'];
				$tb_total['discount'][$ym]['cost'] += $r['discount'];
				$tb_total['gross_amt'][$ym]['cost'] += $r['discount'];
				$tb_total['gross_amt'][$ym]['cost'] += $r['amt'];
				$tb_total['total'][$ym]['cost'] += $r['cost'];
				$tb_total['total'][$ym]['qty'] += $r['qty'];
				$tb_total['total'][$ym]['tax_amount'] += $r['tax_amount'];
				$tb_total['total'][$ym]['amt_inc_gst'] += $r['amt_inc_gst'];
			}
			$tb_total['fm_data'] = $new_data;
			unset($new_data);
		}
	}
	
	private function get_fresh_market_data($params){
	    global $con_multi;
	    
	    $common_fm_filter = $params['filter'];
	    $bid = $params['branch_id'];
	    $pf = $params['pf'];
	    
        $fm_data= array();
    	$sku_id_list = array();

        // get all fresh market sales
	    $sku_filter = array();
		$sku_filter = $common_fm_filter;
		$sku_filter[] = "tbl.date between ".ms($this->date_from)." and ".ms($this->date_to);
		$sku_filter = join(' and ', $sku_filter);
		
		$str_col = ", sum(tbl.amount) as amt, sum(tbl.qty) as qty, sum(tbl.cost) as default_cost, sum(tbl.fresh_market_cost) as fresh_market_cost, sum(tbl.tax_amount) as tax_amount,sum(tbl.disc_amt+tbl.disc_amt2) as discount";
		if($this->memb_filter == 'member'){
			// show member data only
			$str_col = ", sum(tbl.memb_amt) as amt, sum(tbl.memb_qty) as qty, sum(tbl.memb_cost) as default_cost, sum(tbl.memb_fm_cost) as fresh_market_cost, sum(tbl.memb_tax) as tax_amount,sum(tbl.memb_disc+tbl.memb_disc2) as discount";
		}elseif($this->memb_filter == 'non_member'){
			$str_col = ", sum(tbl.amount-tbl.memb_amt) as amt, sum(tbl.qty-tbl.memb_qty) as qty, sum(tbl.cost-tbl.memb_cost) as default_cost, sum(tbl.fresh_market_cost-tbl.memb_fm_cost) as fresh_market_cost, sum(tbl.tax_amount-tbl.memb_tax) as tax_amount,sum(tbl.disc_amt+tbl.disc_amt2-tbl.memb_disc-tbl.memb_disc2) as discount";
		}
		
		$sql = "select si.sku_id, date, $pf as cat_id $str_col
from sku_items_sales_cache_b".$bid." tbl
left join sku_items si on si.id=tbl.sku_item_id
left join sku on sku.id=si.sku_id
left join category_cache cc on cc.category_id=sku.category_id
where $sku_filter
group by si.sku_id, date";
		
        $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc()){
			if(!$r['qty'] && !$r['amt'] && !$r['default_cost'] && !$r['tax_amount'])	continue;
			
			$date_key = date("Ymd", strtotime($r['date']));

            $fm_data[$r['sku_id']]['pos'][$date_key]['qty'] += $r['qty'];
			$fm_data[$r['sku_id']]['pos'][$date_key]['discount'] += $r['discount'];
			$fm_data[$r['sku_id']]['pos'][$date_key]['gross_amt'] += $r['discount'];
			$fm_data[$r['sku_id']]['pos'][$date_key]['gross_amt'] += $r['amt'];
			$fm_data[$r['sku_id']]['pos'][$date_key]['amt'] += $r['amt'];
			$fm_data[$r['sku_id']]['pos'][$date_key]['tax_amount'] += $r['tax_amount'];
			$fm_data[$r['sku_id']]['pos'][$date_key]['amt_inc_gst'] += $r['amt'] + $r['tax_amount'];
			//$fm_data[$r['sku_id']]['pos'][$date_key]['disc_amt2'] += $r['disc_amt2'];
			$fm_data[$r['sku_id']]['pos'][$date_key]['default_cost'] += $r['default_cost'];
			$fm_data[$r['sku_id']]['pos'][$date_key]['fresh_market_cost'] += $r['fresh_market_cost'];
			$fm_data[$r['sku_id']]['cat_id'] = mi($r['cat_id']);

			if(!in_array($r['sku_id'], $sku_id_list))	$sku_id_list[] = $r['sku_id'];
		}
		$con_multi->sql_freeresult();

		//print_r($sku_id_list);
		if(!$sku_id_list)	return false;   // only proceed if got fresh market sku
		
		return $fm_data;
	}
	
	function ajax_load_do_sku(){
	    global $con, $smarty, $sessioninfo, $config, $con_multi;
	    
	    if(!$this->branch_id_list)  die("No branch selected.");
	    
		// establish report server connection
		/*if(!$con_multi){
			if(!$_REQUEST['use_report_server'])   $con_multi = $con;
			else	$con_multi= new mysql_multi();
		}*/
		
	    $this->generate_header_date_label();
		
		$filter[] = "do.do_date between ".ms($this->date_from)." and ".ms($this->date_to);
		if($this->sku_type){
			$filter[] = "sku.sku_type=".ms($this->sku_type);
		}

		$filter[] = "do.approved = 1 and do.active = 1 and do.checkout = 1";
		if($config['sales_report_include_transfer_do']){
			$filter[] = "(case when do.do_type = 'transfer' then do.inv_no is not null and do.inv_no != '' else 1=1 end)";
		}else{
			$filter[] = "do.do_type in ('open', 'credit_sales')";
		}

		if($this->branch_id_list) $filter[] = "do.branch_id in (".join(",", $this->branch_id_list).")";
		
		if($filter) $filter = "where ".join(' and ', $filter);
	
		$sql = "select di.*, do.branch_id as bid, di.sku_item_id, si.sku_item_code, si.description, si.artno,si.mcode,
				((di.ctn*uom.fraction)+di.pcs) as qty, di.selling_price, do.do_markup, uom.fraction as uom_fraction,
				do.markup_type, do.do_date as dt, di.cost, di.cost_price,di.ctn,di.pcs,do.is_under_gst,di.gst_rate,di.item_discount,IFNULL(di.item_discount_amount,0)+IFNULL(di.item_discount_amount2,0) as discount
				from do
				left join do_items di on di.do_id = do.id and di.branch_id = do.branch_id
				left join uom on uom.id = di.uom_id
				left join sku_items si on si.id = di.sku_item_id
				left join sku on sku.id = si.sku_id
				left join category_cache cc using(category_id)
				$filter
				order by si.sku_item_code";

		//$query = join(' union all ', $query);
		//print $sql;

		$do_sql = $con_multi->sql_query($sql);
		
		while($r = $con_multi->sql_fetchrow($do_sql)){
			$date_key = date("Ymd", strtotime($r['dt']));

			$tb[$r['sku_item_id']]['info']['sku_item_code'] = $r['sku_item_code'];
			$tb[$r['sku_item_id']]['info']['description'] = $r['description'];
			$tb[$r['sku_item_id']]['info']['sku_id'] = $r['sku_id'];
			$tb[$r['sku_item_id']]['info']['mcode'] = $r['mcode'];
			$tb[$r['sku_item_id']]['info']['artno'] = $r['artno'];

			/*if($r['do_markup'])	$r['do_markup_arr'] = explode("+", $r['do_markup']);
			if($r['markup_type']=='down'){
				$r['do_markup_arr'][0] *= -1;
				$r['do_markup_arr'][1] *= -1;
			}

			//cost
			$cost = ($r['qty']*$r['cost']);

			// amt
			$cost_price = $r['cost_price'];
	
			// markup / mark down
			if($r['do_markup_arr'][0]){
				$cost_price = $cost_price * (1+($r['do_markup_arr'][0]/100));
			}
			if($r['do_markup_arr'][1]){
				$cost_price = $cost_price * (1+($r['do_markup_arr'][1]/100));
			}

			$amt_ctn = $cost_price*$r['ctn'];
			$amt_pcs = ($cost_price/$r['uom_fraction'])*$r['pcs'];

			// gross amt
			$gross_amt = round($amt_pcs+$amt_ctn,2);
			
			// invoice discount
			$inv_discount_amt = 0;
			if($r['item_discount']){
				$inv_discount_amt = round(get_discount_amt($gross_amt, $r['item_discount']),2);
			}

			// gross invoice amt
			$gross_inv_amt = round($gross_amt - $inv_discount_amt,2);
			
			// invoice gst
			$inv_gst_amt = 0;
			if($r['is_under_gst']){
				$gst_rate = $r['gst_rate'];
				$inv_gst_amt = round($gross_inv_amt * ($gst_rate/100), 2);
			}
			
			// final invoice amt
			$inv_amt = $gross_inv_amt + $inv_gst_amt;*/
			
			if($r['inv_line_gross_amt2'] > 0){
				$inv_gross_amt = $r['inv_line_gross_amt2'];
				$inv_gst_amt = $r['inv_line_gst_amt2'];
				$inv_amt = $r['inv_line_amt2'];
			}else{
				$inv_gross_amt = $r['line_gross_amt'];
				$inv_gst_amt = $r['line_gst_amt'];
				$inv_amt = $r['line_amt'];
			}
			$cost = ($r['qty']*$r['cost']);
			
	        $tb[$r['sku_item_id']]['data'][$date_key]['qty'] += $r['qty'];
	        $tb[$r['sku_item_id']]['data'][$date_key]['amt'] += $inv_gross_amt;
			$tb[$r['sku_item_id']]['data'][$date_key]['discount'] += $r['discount'];
			$tb[$r['sku_item_id']]['data'][$date_key]['gross_amt'] += $r['discount'];
			$tb[$r['sku_item_id']]['data'][$date_key]['gross_amt'] += $inv_gross_amt;
	        $tb[$r['sku_item_id']]['data'][$date_key]['cost'] += $cost;
	        $tb[$r['sku_item_id']]['data'][$date_key][$r['bid']]['cost'] += $cost;
			$tb[$r['sku_item_id']]['data'][$date_key]['tax_amount'] += $inv_gst_amt;
			$tb[$r['sku_item_id']]['data'][$date_key]['amt_inc_gst'] += $inv_amt;
		}
		$con_multi->sql_freeresult($do_sql);

		//print_r($tb);
		$tb_total = array();

		if($tb){
			foreach($tb as $sid=>$sku_items){
			    if(!$sku_items['data'])  continue;
			    
			    foreach($sku_items['data'] as $date_key=>$r){
                    $tb[$sid]['data']['total']['qty'] += $r['qty'];
			        $tb[$sid]['data']['total']['amt'] += $r['amt'];
					$tb[$sid]['data']['total']['discount'] += $r['discount'];
					$tb[$sid]['data']['total']['gross_amt'] += $r['discount'];
					$tb[$sid]['data']['total']['gross_amt'] += $r['amt'];
			        $tb[$sid]['data']['total']['cost'] += $r['cost'];
			        $tb[$sid]['data']['total']['tax_amount'] += $r['tax_amount'];
			        $tb[$sid]['data']['total']['amt_inc_gst'] += $r['amt_inc_gst'];
					//print "cost += $r[cost] <br />";
					
			    	$tb_total['data'][$date_key]['amt'] += $r['amt'];
					$tb_total['data'][$date_key]['discount'] += $r['discount'];
					$tb_total['data'][$date_key]['gross_amt'] += $r['discount'];
					$tb_total['data'][$date_key]['gross_amt'] += $r['amt'];
			    	$tb_total['data'][$date_key]['cost'] += $r['cost'];
			    	$tb_total['data'][$date_key]['qty'] += $r['qty'];
			    	$tb_total['data'][$date_key]['tax_amount'] += $r['tax_amount'];
			    	$tb_total['data'][$date_key]['amt_inc_gst'] += $r['amt_inc_gst'];
					
			    	$tb_total['data']['total']['amt'] += $r['amt'];
					$tb_total['data']['total']['discount'] += $r['discount'];
					$tb_total['data']['total']['gross_amt'] += $r['discount'];
					$tb_total['data']['total']['gross_amt'] += $r['amt'];
			    	$tb_total['data']['total']['cost'] += $r['cost'];
			    	$tb_total['data']['total']['qty'] += $r['qty'];
			    	$tb_total['data']['total']['tax_amount'] += $r['tax_amount'];
			    	$tb_total['data']['total']['amt_inc_gst'] += $r['amt_inc_gst'];
				}
			}
		}
        if($this->by_monthly){  // report group by month
			$this->group_sku_data_to_monthly($tb, $tb_total);
		}
		
        $root_id = $this->cat_id;
		$root_per = isset($_REQUEST['root_per']) ? mf($_REQUEST['root_per']) : 100;

		$smarty->assign('root_per', $root_per);
		$smarty->assign('root_id', $root_id);
		
		$smarty->assign('tb', $tb);
		$smarty->assign('tb_total', $tb_total);
		$smarty->assign('itemise_type', 'do');
		
		$this->display('sales_report.category.sku_table.tpl');
	}
	
	function export_itemise_info(){
		global $config, $con, $sessioninfo, $smarty;

		include_once("include/excelwriter.php");
		log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Daily Category Sales Report To Excel (Itemize)");

		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
		print ExcelWriter::GetHeader();
		$smarty->assign('no_header_footer', 1);
		if($_REQUEST['itemise_type'] == 'do'){
			$this->ajax_load_do_sku();
		}else{
			$this->ajax_load_sku();
		}		
		exit;
	}
    
    function test2(){
        $this->abc();
        
        $a = 5;
        if($b){
            $a = 1;
            $b = 2;
            if($a == $b){

                print "abc";
            }
        }
    }
}


function smarty_value_format($value, $fmt,$zero='&nbsp;')
{
	global $config;

	$ret = '';
	if ($value==0)
	{
		if ($value!==0)	// if report is qty, show 0 as zero, space as blank
			return $zero;

		if ($_REQUEST['report_type']!='qty')
			return $zero;
	}
	if ($value<0)	$ret = '<font color=red>';

	if ($fmt == '%d')
		$ret .= number_format($value,0);
	elseif ($fmt == 'qty')
		$ret .= (strpos($value,'.')>0) ? number_format($value, $config['global_qty_decimal_points']) : number_format($value);
	elseif ($fmt == '%0.2f%%')
		$ret .= number_format($value,2) . "%";
	else
		$ret .= number_format($value,2);

	if ($value<0)
		$ret .= '</font>';
	//= sprintf($value)
	return $ret;
}

$smarty->register_modifier('value_format', 'smarty_value_format');

$SALES_REPORT_CATEGORY = new SALES_REPORT_CATEGORY('Daily Category Sales Report (POS + DO Sales)');


?>
