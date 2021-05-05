<?php
/*
2/20/2012 12:04:11 PM Justin
- Fixed the bugs where causing branch group not functional.

2/23/2012 3:00:43 PM Justin
- Fixed the commission calculation bugs when by item all is commission by "Flat".

2/29/2012 2:13:43 PM Justin
- Fixed the error that system could not find the trade discount code.

3/5/2012 11:49:22 AM Justin
- Added new filter to skip those goods return items from POS.

2/20/2013 3:59 PM Justin
- Enhanced to calculate average sales amount by number for SA base on config.

5/13/2014 10:28 AM Justin
- Enhanced to have total qty column.

6/8/2015 11:43 AM Justin
- Bug fixed on department filter will return zero result.

6/16/2015 11:57 AM Justin
- Bug fixed on category checking for commission that will cause to sum up wrong sales amount.

6/18/2015 10:20 AM Justin
- Bug fixed on sales amount did not deduct from mix & match.

6/26/2015 5:52 PM Justin
- Bug fixed on commission amount have calculated wrongly.

8/3/2015 1:29 PM Justin
- Bug fixed on system has grouped all same receipt into one date.

6/1/2016 2:45 PM Justin
- Bug fixed on wrong total cost calculation.

6/13/2017 10:29 AM Justin
- Bug fixed on cost did not average by S/A count when found config is turned on.

6/19/2017 2:34 PM Justin
- Bug fixed on branch ID couldn't be found when login from sub branch.

6/21/2017 10:38 AM Justin
- Bug fixed on cost calculation always round to 2 decimal points instead of following config setting.

6/22/2017 4:54 PM Justin
- Fixed to exclude gst from POS Amount.
- Change to use inv_line_gross_amt2 for DO Amount.

6/29/2017 11:18 AM Justin
- Bug fixed on sales amount will calculated wrongly if receipt contains more than 1 S/A at receipt header (mst_sa).

10/23/2018 11:59 AM Justin
- Bug fixed on commission range checking issue.

12/7/2018 10:00 AM Justin
- bug fixed on the cost calculation from POS is wrong.

3/26/2019 5:27 PM Justin
- Enhanced to load the certain functions from appCore.

9/18/2019 11:01 AM Justin
- Bug fixed on the sales qty had sum up wrongly.

10/22/2019 10:45 AM Justin
- Enhanced to calculate sales agent sales base on ratio set from POS counter (v202).
- Enhanced the sales calculation to compatible with old and new version of POS counter.

10/24/2019 4:48 PM Justin
- Bug fixed on sales range by amount or qty is not working properly.

11/6/2019 5:05 PM Justin
- Removed date from and to selections, and replaced with Year and Month selections.
- Enhanced to show "Commission by Sales / Qty Range".
- Bug fixed on the commission calculate bugs for sales / qty by range.

1/2/2020 2:26 PM Justin
- Bug fixed on sales won't be show out on flat rate table while commission's condition does not meet.
*/

include("include/common.php");

// check ticket validity
session_start();
$ssid = session_id();
$sa_id = $_SESSION['sa_ticket']['id'];
$con->sql_query("select * from sa where sa.id=".mi($sa_id));
$r=$con->sql_fetchrow();

if(!$r || $_SESSION['sa_ticket']['ticket_no'] != $r['ticket_no']){
	js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
}

include("masterfile_sa_commission.include.php");

class SA_COMMISSION_CALCULATION extends Module{
   function __construct($title){
		global $con, $smarty, $appCore;

		// load min and max year
		$years = array();
		$q1 = $con->sql_query("select year(min(date)) as min_year, year(max(date)) as max_year from pos where date>0");

		while ($r = $con->sql_fetchassoc($q1)){
            $min_year = $r['min_year'];
            $max_year = $r['max_year'];
		}
		$con->sql_freeresult($q1);
		
		$count_year = $max_year - $min_year;
		
		for($i=0; $i<=$count_year; $i++){
			$tmp_year = $min_year+$i;
			$years[$tmp_year][0] = $tmp_year;
			$years[$tmp_year]['year'] = $tmp_year;
			unset($tmp_year);
		}
		
		ksort($years);
		$smarty->assign("years", $years);
		unset($min_year, $max_year, $year, $count_year);
		
		// set months
		$months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
		$smarty->assign("months", $months);
		unset($months);
		
		// preset year and month if no value found
		if (!$_REQUEST['year']) $_REQUEST['year'] = date('Y');
		if (!$_REQUEST['month']) $_REQUEST['month'] = date('m');
		
		// pre-load sales agent
		$this->sa_list = $appCore->salesAgentManager->getSAList();
		$smarty->assign('sa', $this->sa_list);
		
		$filter = "";
		if(BRANCH_CODE == "HQ"){
			// load branches
			$con->sql_query("select * from branch where active=1 and id>0 $filter order by sequence,code");
			while($r = $con->sql_fetchassoc()){
				$this->branches[$r['id']] = $r;
			}
			$con->sql_freeresult();
			$smarty->assign('branches',$this->branches);
		}
		
		// load branch group
		$this->branches_group = $this->load_branch_group();

		$con->sql_query("select * from category order by description");
		$smarty->assign("departments", $con->sql_fetchrowset());

		$con->sql_query("select * from sku_type") or die(mysql_error());
		$smarty->assign("sku_type",$con->sql_fetchrowset());
		
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
        global $con, $smarty, $config, $appCore;

		$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}

		$filter = $all_sql = "";
		$sql = $sa_invalid_range = $sa_range_commission = array();

		// join all the filters
		if($this->filter) $filter = " and ".join(" and ", $this->filter);

		if(($this->sales_type && $this->sales_type != 'pos') || !$this->sales_type){
			if($this->do_filter) $do_filter = " and ".join(" and ", $this->do_filter);
			$sql[] = "select 'DO' as type, do_type, do.mst_sa, di.dtl_sa, di.do_id as mst_id, do.do_date as date, 
					  di.inv_line_gross_amt2 as cost_price, di.cost, do.do_markup,do.markup_type, 0 as pi_amount, 
					  uom.fraction, ((di.ctn*uom.fraction)+di.pcs) as qty, di.sku_item_id, sku.category_id, c.level as cat_level, sku.brand_id, 
					  sku.sku_type, sku.vendor_id, do.do_no as doc_no, di.item_discount,
					  if(sip.trade_discount_code is not null and sip.trade_discount_code != '', sip.trade_discount_code, sku.default_trade_discount_code) as trade_discount_code, 0 as sales_cache_qty
					  from `do`
					  left join `do_items` di on di.do_id = do.id and di.branch_id = do.branch_id
					  left join `uom` on uom.id = di.uom_id
					  left join `sku_items` si on si.id = di.sku_item_id
					  left join `sku` on sku.id = si.sku_id
					  left join `category` c on c.id = sku.category_id
					  left join `sku_items_price` sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($bid)."
					  where do.branch_id = ".mi($bid)." and do.active=1 and do.approved=1 and do.checkout=1 and do.do_type != 'transfer'
					  $filter $do_filter";
		}

		if($this->sales_type == 'pos' || !$this->sales_type){
			if($this->pos_filter) $pos_filter = " and ".join(" and ", $this->pos_filter);
			$sql[] = "select 'POS' as type, '' as do_type, pos.receipt_sa as mst_sa, pi.item_sa as dtl_sa, pos.id as mst_id, 
					  pos.date, 0 as cost_price, sisc.cost, 0 as do_markup, '' as markup_type, 
					  (pi.price-pi.discount-pi.discount2-pi.tax_amount) as pi_amount, 1 as fraction, pi.qty, pi.sku_item_id, sku.category_id, c.level as cat_level,
					  sku.brand_id, sku.sku_type, sku.vendor_id, pos.receipt_no as doc_no, 0 as item_discount,
					  if(sip.trade_discount_code is not null and sip.trade_discount_code != '', sip.trade_discount_code, sku.default_trade_discount_code) as trade_discount_code, sisc.qty as sales_cache_qty
					  from `pos`
					  left join `pos_items` pi on pi.pos_id = pos.id and pi.branch_id = pos.branch_id and pi.date = pos.date and pi.counter_id = pos.counter_id
					  left join `sku_items` si on si.id = pi.sku_item_id
					  left join `sku` on sku.id = si.sku_id
					  left join `category` c on c.id = sku.category_id
					  left join `sku_items_price` sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($bid)."
					  left join `sku_items_sales_cache_b".mi($bid)."` sisc on sisc.sku_item_id = si.id and sisc.date = pos.date
					  join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date and pf.finalized=1
					  where pos.branch_id = ".mi($bid)." and pos.cancel_status=0
					  $filter $pos_filter";
		}

		$all_sql = join(" UNION ALL ", $sql)." order by date";

		$q1 = $con_multi->sql_query($all_sql);

		while($r1 = $con_multi->sql_fetchrow($q1)){
			$sa_list = array();
			$row_cost = $row_amt_ctn = $row_amt_pcs = $row_amt = 0;

			if($r1['mst_sa']) $sa_list = unserialize($r1['mst_sa']);
			else $sa_list = unserialize($r1['dtl_sa']);
			
			if(count($sa_list) == 0) continue;
			//elseif($this->sa_id && !in_array($this->sa_id, $sa_list)) continue;
			
			if($r1['type'] == "DO"){
				$row_amt = round($r1['cost_price'],2);
				$row_cost = $r1['cost'] * $r1['qty'] / $r1['fraction'];
			}else{
				$row_cost = $r1['cost'] / $r1['sales_cache_qty'] * $r1['qty'];
				$row_amt = round($r1['pi_amount'], 2);
			}
			
			$row_qty = $r1['qty'];
			
			// check if the receipt contains ratio
			$prms = array();
			$prms['sa_list'] = $sa_list;
			$prms['sales_amount'] = $row_amt;
			$sa_ratio_result = array();
			$sa_ratio_result = $appCore->salesAgentManager->posSAHandler($prms);
			unset($prms);
			
			// check if the sales agent got set with ratio then use the ratio to calculate the sales amount for each sales agent
			$sa_ratio_sales_list = array();
			if($r1['mst_sa']){
				if($sa_ratio_result['use_ratio']){
					$this->use_comm_ratio = true;
					$sa_ratio_sales_list = $sa_ratio_result['sa_ratio_sales_list'];
				}elseif(count($sa_list) > 1 && $config['sa_calc_average_sales']){ // otherwise check if turn on config to calculate average sales for all sales agent
					$row_cost = round($row_cost / count($sa_list), $config['global_cost_decimal_points']);
					$row_amt = round($row_amt / count($sa_list), 2);
				}
			}
			
			// unset the unwanted sales agent as if user got filter sales agent
			foreach($sa_list as $sa_id=>$sa_info){
				if($sa_ratio_result['id_list_existed']){
					$tmp_sa_id = $sa_id; // use sales agent ID from array key
				}else{
					$tmp_sa_id = $sa_info; // use the array values as sales agent ID
				}
				
				if(!$tmp_sa_id || ($this->sa_id && $this->sa_id != $tmp_sa_id)){
					unset($sa_list[$sa_id]);
					continue;
				}
				
				// use the sales amount calculated by using ratio
				if($sa_ratio_result['use_ratio']){
					$row_amt = $sa_ratio_sales_list[$tmp_sa_id]['sales_amt'];
				}

				// check whether the S/A match all the conditions set from commission module
				$is_flat_rate_comm = $is_sales_qty_range_comm = false;
				$q2 = $con_multi->sql_query("select *, sa.id as sa_id, saci.id as saci_id, saci.date_from
											from sa
											join sa_commission_settings sas on sas.sa_id = sa.id and sas.branch_id = ".mi($bid)."
											join sa_commission sac on sac.id = sas.sac_id and sac.branch_id = sas.branch_id
											join sa_commission_items saci on saci.sac_id = sac.id and saci.branch_id = sac.branch_id
											where sa.id  = ".mi($tmp_sa_id)." and saci.date_from <= ".ms($r1['date'])." and (saci.date_to is null or saci.date_to = '' or saci.date_to >= ".ms($r1['date']).") and sac.active = 1 and saci.active = 1 and sas.active = 1
											order by sa.code");

				if($con_multi->sql_numrows($q2) > 0){
					while($r2 = $con_multi->sql_fetchassoc($q2)){
						if($r2['commission_method'] != "Flat"){
							$this->range_table[$r2['sa_id']]['code'] = $r2['code'];
							$this->range_table[$r2['sa_id']]['name'] = $r2['name'];
							
							// do not sum up the commission again as if it is being calculated previously
							if($sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['result'] == "failed") continue;
						}
						
						// check if the conditions are met
						$conditions = unserialize($r2['conditions']);
						$condition_met = $appCore->salesAgentManager->check_commission_conditions($conditions, $r1);
						if(!$condition_met) continue;
						
						if($r2['commission_method'] != "Flat"){ // is set by sales or qty range
							$commission_value_list = unserialize($r2['commission_value']);
							
							$prms = array();
							$prms['sales_type'] = $this->sales_type;
							$prms['sac_date'] = $r1['date'];
							$prms['conditions'] = $conditions;
							$prms['commission_method'] = $r2['commission_method'];
							$prms['commission_value_list'] = $commission_value_list;
							$prms['filters'] = $this->filter;

							// here is where we start to sum up the entire month for commission method - range setup...
							if(!$sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]) $sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid] = $appCore->salesAgentManager->check_range_commission($r2['sa_id'], $bid, $prms);
							
							if($sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['result'] == "failed") continue;
							elseif($sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['claimed'] == true){
								$is_sales_qty_range_comm = true; // mark this transaction contains sales / qty range commission
								continue;
							}else{
								$commission_value = $sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['commission_value'];
								// replace the sales amount with monthly sales amount in case the commission value was set by percentage
								$row_amt = $sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['ttl_sales_amt'];
								$row_qty = $sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['ttl_sales_qty'];
							}
						}else $commission_value = $r2['commission_value']; // is set by flat

						if(!$commission_value) continue;
						
						$commission_values = explode("+", $commission_value);
						$commission_amt = 0;
						foreach($commission_values as $cm_value){
							// check if the commission either by percentage or amount
							if(preg_match("/%/", $cm_value)){ // is by percentage
								$cv = str_replace("%", "", $cm_value);
								$commission_amt += round(($row_amt-$commission_amt) * ($cv/100), 2);
							}else $commission_amt += $cm_value;
						}
						
						// construct different array to store and show commission set by sales/qty range
						if($r2['commission_method'] != "Flat"){
							// use sum up as if it was the first time of getting the commssion
							// mark this commission as claimed once added into the range data list
							if($sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['result'] == "passed"){
								$this->range_table[$r2['sa_id']]['amt'] += $row_amt;
								$this->range_table[$r2['sa_id']]['qty'] += $row_qty;
								$this->range_table[$r2['sa_id']]['commission_amt'] += $commission_amt;
								$this->range_total['amt'] += $row_amt;
								$this->range_total['qty'] += $row_qty;
								$this->range_total['commission_amt'] += $commission_amt;
								
								// mark this commission by sales / qty range become claimed
								$sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['claimed'] = true;
							}
							
							// stop to include the sales into the flat list
							$is_sales_qty_range_comm = true;
							continue;
						}

						// check the highest commission value
						if($r2['commission_method'] != "Flat" && $commission_amt > $top_commission_amt[$r1['date']][$r1['doc_no']]) $top_commission_amt[$r1['date']][$r1['doc_no']] = $commission_amt;
						else $top_commission_amt[$r1['date']][$r1['doc_no']] += $commission_amt;
						
						$ttl_commission_amt[$r1['date']][$r1['doc_no']] += $commission_amt;
						if(($this->sa_id && $this->sa_id == $r2['sa_id']) || !$this->sa_id){
							$this->sac_table[$r1['date']][$r1['doc_no']][$r2['sa_id']]['commission_amt'] += $commission_amt;
							$this->table[$r1['date']][$r1['doc_no']]['ttl_sa_commission_amt'] += $commission_amt;
							$is_flat_rate_comm = true;
						}
					}
				}
				$con_multi->sql_freeresult($q2);
					
				// need to store the sales and qty value into flat rate table as if couldn't find the commission
				// receipt must not be cancelled and matched with commission by qty or sales range
				// sum up as flat rate sales as if it has commission or couldn't find any commission from flat rate and commission by sales / qty range
				if($is_flat_rate_comm || (!$is_flat_rate_comm && !$is_sales_qty_range_comm)){
					// use the sales amount calculated by using ratio
					if($sa_ratio_result['use_ratio']){
						$row_amt = $sa_ratio_sales_list[$tmp_sa_id]['sales_amt'];
					}
					
					$this->sac_table[$r1['date']][$r1['doc_no']][$tmp_sa_id]['code'] = $this->sa_list[$tmp_sa_id]['code'];
					$this->sac_table[$r1['date']][$r1['doc_no']][$tmp_sa_id]['name'] = $this->sa_list[$tmp_sa_id]['name'];
					$this->sac_table[$r1['date']][$r1['doc_no']][$tmp_sa_id]['cost'] += $row_cost;
					$this->sac_table[$r1['date']][$r1['doc_no']][$tmp_sa_id]['amt'] += $row_amt;
					$this->sac_table[$r1['date']][$r1['doc_no']][$tmp_sa_id]['qty'] += $row_qty;
					$this->table[$r1['date']][$r1['doc_no']]['final_amount'] += $row_amt;
					$this->table[$r1['date']][$r1['doc_no']]['cost'] += $row_cost;
					$this->table[$r1['date']][$r1['doc_no']]['qty'] += $row_qty;
				}
				unset($is_flat_rate_comm, $is_sales_qty_range_comm);

				if($this->sac_table[$r1['date']][$r1['doc_no']]){
					$this->table[$r1['date']][$r1['doc_no']]['date'] = $r1['date'];
					$this->table[$r1['date']][$r1['doc_no']]['type'] = $r1['type'];
					$do_type = "";
					if($r1['do_type'] == "open") $do_type = "Cash Sales";
					elseif($r1['do_type'] == "credit_sales") $do_type = "Credit Sales";
					elseif($r1['do_type'] == "transfer") $do_type = "Transfer";
					$this->table[$r1['date']][$r1['doc_no']]['do_type'] = $do_type;
					//$this->table[$r1['doc_no']]['ttl_commission_value'] += $ttl_commission_value;
					$this->table[$r1['date']][$r1['doc_no']]['top_commission_amt'] = $top_commission_amt[$r1['date']][$r1['doc_no']];
					$this->table[$r1['date']][$r1['doc_no']]['ttl_commission_amt'] = $ttl_commission_amt[$r1['date']][$r1['doc_no']];
					$this->table[$r1['date']][$r1['doc_no']]['commission_method'] = $r2['commission_method'];
					$this->table[$r1['date']][$r1['doc_no']]['commission_value'] = $r2['commission_value'];
				}
			}
				
			//if(!$this->table[$r1['doc_no']]) $this->table[$r1['doc_no']] = $r1;
			//$prv_mst_id= $r1['mst_id'];
		}
		$con_multi->sql_freeresult($q1);
		$con_multi->close_connection();
	}

	function output_excel(){
	    global $smarty;
		
		$this->process_form();
		$this->generate_report();

        include_once("include/excelwriter.php");
    	$smarty->assign('no_header_footer', true);
    	$filename = "sac_calculation_".time().".xls";
    	Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename='.$filename);

		print ExcelWriter::GetHeader();
		$this->display();
		print ExcelWriter::GetFooter();
	    exit;
	}
	
    function generate_report(){
		global $con, $smarty;

		$this->table = $this->sac_table = $this->range_table = $this->range_total = array();
		$this->use_comm_ratio = false;
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$this->run_report($bid);
			}
		}

		// set report fixed row display
		$smarty->assign('report_row', 25);
		
		$this->report_title[] = "Year: ".strtoupper($this->year);
		$this->report_title[] = "Month: ".strtoupper($this->month);
		
		if(!$this->sales_type) $sales_type = "All";
		else{
			if($this->sales_type == "open") $sales_type = "Cash Sales";
			elseif($this->sales_type == "credit_sales") $sales_type = "Credit Sales";
			else $sales_type = "POS";
		}
		$this->report_title[] = "Sales Type: ".$sales_type;		
		// pre-load sales agent
		if($this->department_id){
			$con->sql_query("select description from category where id = ".mi($this->department_id));
			$dept = $con->sql_fetchrow();
			$dept_desc = $dept['description'];
			$con->sql_freeresult();
		}else{
			$dept_desc = "All";
		}

		$this->report_title[] = "Department: ".$dept_desc;
		$sku_type = ($this->sku_type) ? $this->sku_type : "All";
		$this->report_title[] = "SKU Type: ".$sku_type;
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		$smarty->assign('table', $this->table);
		$smarty->assign('sac_table', $this->sac_table);
		$smarty->assign('range_table', $this->range_table);
		$smarty->assign('range_total', $this->range_total);
		$smarty->assign('use_comm_ratio', $this->use_comm_ratio);
	}
	
	function process_form(){
	    global $con, $smarty, $sa_id;
		
		$this->year = $_REQUEST['year'];
		$this->month = $_REQUEST['month'];
		$this->department_id = $_REQUEST['department_id'];
		$this->sku_type = $_REQUEST['sku_type'];
		$this->sales_type = $_REQUEST['sales_type'];
		$this->sa_id = $sa_id;

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
			$bid = get_branch_id(BRANCH_CODE);
            $this->branch_id_list[] = $bid;
            $this->report_title[] = "Branch: ".BRANCH_CODE;
		}

		$this->filter = array();
		$this->do_filter = array();
		$this->pos_filter = array();
		
		// construct date range for filtering
		$date_from = $this->year."-".$this->month."-01";
		$date_to = date("Y-m-d", strtotime("-1 day", strtotime("+1 month", strtotime($date_from))));

		// filter date for different sales types
		if(($this->sales_type && $this->sales_type != 'pos') || !$this->sales_type){
			if($this->sales_type) $this->do_filter[] = "do.do_type = ".ms($this->sales_type);
			$this->do_filter[] = "do.do_date between ".ms($date_from)." and ".ms($date_to);
			if($this->sa_id) $this->do_filter[] = "((do.mst_sa != '' and do.mst_sa is not null and do.mst_sa like '%s:".strlen(mi($this->sa_id)).":\"".mi($this->sa_id)."\";%') or (di.dtl_sa != '' and di.dtl_sa is not null and di.dtl_sa like '%s:".strlen(mi($this->sa_id)).":\"".mi($this->sa_id)."\";%'))";
			else $this->do_filter[] = "((do.mst_sa != '' and do.mst_sa is not null) or (di.dtl_sa != '' and di.dtl_sa is not null))";
		}
		
		if($this->sales_type == 'pos'|| !$this->sales_type){
			$this->pos_filter[] = "pos.date between ".ms($date_from)." and ".ms($date_to);
			if($this->sa_id) $this->pos_filter[] = "((pos.receipt_sa != '' and pos.receipt_sa is not null and pos.receipt_sa like '%s:".strlen(mi($this->sa_id)).":\"".mi($this->sa_id)."\";%') or (pi.item_sa != '' and pi.item_sa is not null and pi.item_sa like '%s:".strlen(mi($this->sa_id)).":\"".mi($this->sa_id)."\";%'))";
			else $this->pos_filter[] = "((pos.receipt_sa != '' and pos.receipt_sa is not null) or (pi.item_sa != '' and pi.item_sa is not null))";
		}

		if($this->department_id) $this->filter[] = "c.department_id = ".ms($this->department_id);
		if($this->sku_type) $this->filter[] = "sku.sku_type = ".ms($this->sku_type);
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

$SA_COMMISSION_CALCULATION = new SA_COMMISSION_CALCULATION('Sales Agent Commission Calculation Report');
?>
