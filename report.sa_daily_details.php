<?php
/*
6/19/2015 11:00 AM Eric
- Enhanced to show by items and sales agent

7/6/2015 5:57 PM Justin
- Bug fixed on pos did not deduct mix & match discount

6/1/2016 2:45 PM Justin
- Bug fixed on wrong total cost calculation.
	
1/12/2017 10:37 AM Andy
- Fixed expand details bug.

2/3/2017 2:59 PM Andy
- Fixed to exclude gst from POS Amount.
- Change to use inv_line_gross_amt2 for DO Amount.
- Fixed DO status filter bugs.

3/27/2017 10:02 AM Justin
- Bug fixed on showing wrong sales agent while click on show details. 

4/19/2017 3:50 PM Justin
- Bug fixed on system always shows the last S/A only when S/A is inseted into itemise.
- Bug fixed on counter filtering.

4/20/2017 11:19 AM Justin
- Bug fixed on DO Status filtering issue.

6/13/2017 10:29 AM Justin
- Bug fixed on cost did not average by S/A count when found config is turned on.

6/21/2017 10:38 AM Justin
- Bug fixed on PHP errors.
- Bug fixed on cost calculation always round to 2 decimal points instead of following config setting.

6/29/2017 10:35 AM Justin
- Bug fixed on cost wrongly average.

3/26/2019 5:27 PM Justin
- Enhanced to have Commission Sales Qty, Sales Amount and Commission Amount.
- Enhanced to load the certain functions from appCore.

10/22/2019 10:45 AM Justin
- Enhanced to calculate sales agent sales base on ratio set from POS counter (v202).
- Enhanced the sales calculation to compatible with old and new version of POS counter.

10/24/2019 4:48 PM Justin
- Bug fixed on sales range by amount or qty is not working properly.

10/31/2019 1:42 PM Justin
- Enhanced not to calculate the commission as if the POS transaction is cancelled or pruned.

11/6/2019 5:05 PM Justin
- Removed date from and to selections, and replaced with Year and Month selections.
- Enhanced to show "Commission by Sales / Qty Range".
- Bug fixed on the commission calculate bugs for sales / qty by range.
- Removed the "All" selection for sales agent filter.

11/14/2019 5:15 PM Andy
- Added maintenance check version 422.

11/19/2019 10:24 AM Justin
- Enhanced to show ratio information.
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
$maintenance->check(422);

class SA_DAILY_DETAILS extends Module{
   function __construct($title){
		global $con, $smarty, $sessioninfo;

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

		$con->sql_query("select * from category where id in ($sessioninfo[department_ids]) order by description");
		$smarty->assign("departments", $con->sql_fetchrowset());

		$con->sql_query("select * from sku_type") or die(mysql_error());
		$smarty->assign("sku_type",$con->sql_fetchrowset());

		$this->load_counters(1);
		
		// POS status
		$transaction_status_list = array('-1'=>'Cancelled','1'=>'Valid','2'=>'Pruned');
		$smarty->assign("transaction_status", $transaction_status_list);
		
		// DO status
		$do_status_list = array('1'=>'Draft / Waiting for Approval','1'=>'Approved','2'=>'Checkout');
		$smarty->assign("do_status", $do_status_list);
		
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
        global $con, $smarty, $sessioninfo, $config, $appCore;

		$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}

		$filter = $all_sql = "";
		$sql = $sa_invalid_range = $sa_range_commission = $top_commission_amt = array();

		// join all the filters
		if($this->filter) $filter = " and ".join(" and ", $this->filter);

		if(($this->sales_type && $this->sales_type != 'pos') || !$this->sales_type){
			if($this->do_filter) $do_filter = " and ".join(" and ", $this->do_filter);
			$sql[] = "select 'DO' as type, do_type, do.mst_sa, di.dtl_sa, di.do_id as mst_id, do.do_date as date, 
					  di.inv_line_gross_amt2 as cost_price, di.cost, do.do_markup,do.markup_type, 0 as pi_amount, 
					  uom.fraction, ((di.ctn*uom.fraction)+di.pcs) as qty, di.sku_item_id, sku.category_id, sku.brand_id, 
					  sku.sku_type, sku.vendor_id, do.do_no as doc_no, di.item_discount, null as network_name,
					  u.l as cashier_name, do.last_update as trans_time, 0 as prune_status, 0 as cancel_status, 0 as counter_id,
					  do.status, do.approved, do.active, do.checkout, b.code as branch_code, '' as ref_no,
					  si.sku_item_code, si.description, si.mcode, 0 as sales_cache_qty, 0 as prune_status, 0 as cancel_status
					  from `do`
					  left join `do_items` di on di.do_id = do.id and di.branch_id = do.branch_id
					  left join `uom` on uom.id = di.uom_id
					  left join `sku_items` si on si.id = di.sku_item_id
					  left join `sku` on sku.id = si.sku_id
					  left join `category` c on c.id = sku.category_id
					  left join `sku_items_price` sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($bid)."
					  left join `user` u on u.id = do.user_id
					  left join `branch` b on b.id = do.branch_id
					  where do.branch_id = ".mi($bid)." and do.active=1 and do.do_type != 'transfer'
					  $filter $do_filter";
		}

		if($this->sales_type == 'pos' || !$this->sales_type){
			if($this->pos_filter) $pos_filter = " and ".join(" and ", $this->pos_filter);
			$sql[] = "select 'POS' as type, '' as do_type, pos.receipt_sa as mst_sa, pi.item_sa as dtl_sa, pos.id as mst_id, 
					  pos.date, 0 as cost_price, (sisc.cost / sisc.qty) as cost, 0 as do_markup, '' as markup_type, 
					  (pi.price-pi.discount-pi.discount2-pi.tax_amount) as pi_amount, 1 as fraction, pi.qty, pi.sku_item_id, sku.category_id, sku.brand_id,
					  sku.sku_type, sku.vendor_id, pos.receipt_no as doc_no, 0 as item_discount, cs.network_name,
					  u.l as cashier_name, pos.pos_time as trans_time, pos.prune_status, pos.cancel_status, pos.counter_id,
					  0 as status, 0 as approved, 0 as active, 0 as checkout, b.code as branch_code, pos.receipt_ref_no as ref_no,
					  si.sku_item_code, si.description, si.mcode, sisc.qty as sales_cache_qty, pos.prune_status, pos.cancel_status
					  from `pos`
					  left join `pos_items` pi on pi.pos_id = pos.id and pi.branch_id = pos.branch_id and pi.date = pos.date and pi.counter_id = pos.counter_id
					  left join `sku_items` si on si.id = pi.sku_item_id
					  left join `sku` on sku.id = si.sku_id
					  left join `category` c on c.id = sku.category_id
					  left join `sku_items_price` sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($bid)."
					  left join `sku_items_sales_cache_b".mi($bid)."` sisc on sisc.sku_item_id = si.id and sisc.date = pos.date
					  left join `counter_settings` cs on cs.id = pos.counter_id and cs.branch_id = pos.branch_id
					  left join `user` u on u.id = pos.cashier_id
					  left join `branch` b on b.id = pos.branch_id
					  where pos.branch_id = ".mi($bid)."
					  $filter $pos_filter";
		}

		$all_sql = join(" UNION ALL ", $sql)." order by date";

		$q1 = $con_multi->sql_query($all_sql);
		$each_item_sa = $sa_name_list = array();

		while($r1 = $con_multi->sql_fetchrow($q1)){
			$sa_list = array();
			$row_cost = $row_amt_ctn = $row_amt_pcs = $row_amt = 0;

			if($r1['mst_sa']) $sa_list = unserialize($r1['mst_sa']);
			else $sa_list = unserialize($r1['dtl_sa']);
			
			if(count($sa_list) == 0) continue;
			//elseif($this->sa_id && !in_array($this->sa_id, $sa_list)) continue;
			
			if($r1['type'] == "DO"){
				$r1['ref_no'] = $r1['doc_no'];
				$row_amt = round($r1['cost_price'],2);
				if($r1['cost'] > 0) $row_cost = $r1['cost'] * $r1['qty'] / $r1['fraction'];
			}else{
				if($r1['cost'] > 0) $row_cost = $r1['cost'] / $r1['sales_cache_qty'] * $r1['qty'];
				else $row_cost = 0;
				$row_amt = round($r1['pi_amount'],2);
			}
			
			$sa_ratio_result = array();
			// check if the receipt contains ratio
			$prms = array();
			$prms['sa_list'] = $sa_list;
			$prms['sales_amount'] = $row_amt;
			$sa_ratio_result = $appCore->salesAgentManager->posSAHandler($prms);
			unset($prms);
			
			// check if the sales agent got set with ratio then use the ratio to calculate the sales amount for each sales agent
			$sa_ratio_sales_list = array();
			if($r1['mst_sa'] && $sa_ratio_result['use_ratio']){
				$sa_ratio_sales_list = $sa_ratio_result['sa_ratio_sales_list'];
			}
			unset($prms);

			$row_qty = $r1['qty'];
			
			$sa_name_list = $each_item_sa = array();
			$ratio = $ttl_ratio = 0;
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
				
				$sa_name_list[$r1['date']][$r1['ref_no']][$r1['counter_id']][$tmp_sa_id] = $this->sa_list[$tmp_sa_id]['code']." (".$this->sa_list[$tmp_sa_id]['name'].")";
				$each_item_sa[$r1['date']][$r1['ref_no']][$r1['counter_id']][$tmp_sa_id] = $sa_name_list[$r1['date']][$r1['ref_no']][$r1['counter_id']][$tmp_sa_id];
				
				// use the sales amount calculated by using ratio
				$tmp_row_amt = $row_amt;
				if($sa_ratio_result['use_ratio']){
					$tmp_row_amt = $sa_ratio_sales_list[$tmp_sa_id]['sales_amt'];
					$ratio = $sa_list[$tmp_sa_id]['ratio'];
					$ttl_ratio = $sa_ratio_result['ttl_ratio'];
				}
				
				unset($tmp_sa_id);
			}
			
			$sa_id_filter_list = join("','", $sa_list);
			// need to use array keys as sales agent ID as if POS counter are using v202 to store the sa ID
			if($sa_ratio_result['id_list_existed']) $sa_id_filter_list = join("','", array_keys($sa_list));

			// check cancel by who
			if($r1['type'] == "POS" && $r1['cancel_status']){
				$q_cb = $con_multi->sql_query("select prc.cancelled_by, user.u as cancelled_by_u 
				from pos_receipt_cancel prc
				left join user on user.id=prc.verified_by
				where prc.branch_id=$bid and prc.counter_id=".mi($r1['counter_id'])." and prc.date=".ms($r1['date'])." and prc.receipt_no=".ms($r1['doc_no'])." 
				order by prc.cancelled_time desc 
				limit 1");

				$tmp = $con_multi->sql_fetchassoc($q_cb);
				$con_multi->sql_freeresult($q_cb);

				if(is_array($tmp) && $tmp)	$r1 = array_merge($r1, $tmp);
				unset($tmp);
			}
			
			//DO status is different with POS
			if(($r1['status'] == 1 || $r1['status'] == 3) && $r1['approved'] != 1){
				$do_status = "Saved DO";
			}
			elseif ($r1['approved'] && !$r1['checkout']){
				$do_status = "Approved";
			}elseif ($r1['approved'] && $r1['checkout']){
				$do_status = "Checkout";
			}

			// date + counter
			$row_qty = round($row_qty, $config['global_qty_decimal_points']);
			$row_amt = round($row_amt, 2);
			if($sa_name_list) $this->table[$bid][$r1['date']][$r1['ref_no']]['sa_name'] = join(", ", $sa_name_list[$r1['date']][$r1['ref_no']][$r1['counter_id']]);
			$this->table[$bid][$r1['date']][$r1['ref_no']]['doc_no'] = $r1['doc_no'];
			$this->table[$bid][$r1['date']][$r1['ref_no']]['type'] = $r1['type'];
			$this->table[$bid][$r1['date']][$r1['ref_no']]['counter_name'] = $r1['network_name'];
			$this->table[$bid][$r1['date']][$r1['ref_no']]['cashier_name'] = strtoupper($r1['cashier_name']);
			$this->table[$bid][$r1['date']][$r1['ref_no']]['trans_time'] = $r1['trans_time'];
			$this->table[$bid][$r1['date']][$r1['ref_no']]['prune_status'] = $r1['type'] == "DO" ? $do_status : $r1['prune_status']; //Make use off prune_status key for DO as well
			$this->table[$bid][$r1['date']][$r1['ref_no']]['cancel_status'] = $r1['cancel_status'];
			$this->table[$bid][$r1['date']][$r1['ref_no']]['cancelled_by_u'] = $r1['cancelled_by_u'];
			$this->table[$bid][$r1['date']][$r1['ref_no']]['cost'] += $row_cost;
			$this->table[$bid][$r1['date']][$r1['ref_no']]['amt'] += $row_amt;
			$this->table[$bid][$r1['date']][$r1['ref_no']]['qty'] += $row_qty;
			$this->table[$bid][$r1['date']][$r1['ref_no']]['id'] = $r1['mst_id'];
			if($each_item_sa) $this->table[$bid][$r1['date']][$r1['ref_no']]['items'][] = array('item_sa'=>join(", ", $each_item_sa[$r1['date']][$r1['ref_no']][$r1['counter_id']]),'arms_code'=>$r1['sku_item_code'], 'desc'=>$r1['description'], 'mcode'=>$r1['mcode'], 'qty'=>$r1['qty'],'amount'=>$row_amt,'use_ratio'=>$sa_ratio_result['use_ratio'],'ratio'=>$ratio,'ttl_ratio'=>$ttl_ratio);

			$this->date_sales[$bid][$r1['date']]['amt'] += $row_amt;
			$this->date_sales[$bid][$r1['date']]['qty'] += $row_qty;
			
			$this->total_sales['amt'] += $row_amt;
			$this->total_sales['qty'] += $row_qty;
			
			if($r1['mst_sa'] && !$sa_ratio_result['use_ratio'] && count($sa_list) > 1 && $config['sa_calc_average_sales']){
				$row_amt = round($row_amt / count($sa_list), 2);
			}
			
			// do not proceed to calculate the commission as if the transaction is being cancelled, prune or inactive
			if($r1['type'] == "POS" && ($r1['cancel_status'] == 1 || $r1['prune_status'] == 1)) continue; // POS is being cancelled or prune
			
			// check whether the S/A match all the conditions set from commission module
			$q2 = $con_multi->sql_query($sql="select *, sa.id as sa_id, saci.id as saci_id, saci.date_from
										from sa
										join sa_commission_settings sas on sas.sa_id = sa.id and sas.branch_id = ".mi($bid)."
										join sa_commission sac on sac.id = sas.sac_id and sac.branch_id = sas.branch_id
										join sa_commission_items saci on saci.sac_id = sac.id and saci.branch_id = sac.branch_id
										where sa.id in ('".$sa_id_filter_list."') and saci.date_from <= ".ms($r1['date'])." and (saci.date_to is null or saci.date_to = '' or saci.date_to >= ".ms($r1['date']).") and sac.active = 1 and saci.active = 1 and sas.active = 1
										order by sa.code");

			$is_range_commission = false;
			if($con_multi->sql_numrows($q2) > 0){
				while($r2 = $con_multi->sql_fetchassoc($q2)){
					// use the sales amount calculated by using ratio
					if($sa_ratio_result['use_ratio']){
						$row_amt = $sa_ratio_sales_list[$r2['sa_id']]['sales_amt'];
					}
					
					if($r2['commission_method'] != "Flat"){
						// do not sum up the commission again as if it is being calculated previously
						if($sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]) continue;
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
						$sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid] = $appCore->salesAgentManager->check_range_commission($r2['sa_id'], $bid, $prms);
						
						if($sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['result'] == "failed") continue;
						else{
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
						$this->range_table[$bid][$r2['sa_id']]['code'] = $r2['code'];
						$this->range_table[$bid][$r2['sa_id']]['name'] = $r2['name'];	
						$this->range_table[$bid][$r2['sa_id']]['amt'] += $row_amt;
						$this->range_table[$bid][$r2['sa_id']]['qty'] += $row_qty;
						$this->range_table[$bid][$r2['sa_id']]['commission_amt'] += $commission_amt;
						$this->total_sales['range']['commission_sales_amt'] += $row_amt;
						$this->total_sales['range']['commission_sales_qty'] += $row_qty;
						$this->total_sales['sa_commission_amt'] += $commission_amt;
						$this->range_total[$bid]['amt'] += $row_amt;
						$this->range_total[$bid]['qty'] += $row_qty;
						$this->range_total[$bid]['commission_amt'] += $commission_amt;
						
						// stop to include the sales into the flat list
						continue;
					}
					
					// sum up commission amount for itemise 
					$ikey = sizeof($this->table[$bid][$r1['date']][$r1['ref_no']]['items'])-1;
					$this->table[$bid][$r1['date']][$r1['ref_no']]['items'][$ikey]['commission_sales_amt'] += $row_amt;
					$this->table[$bid][$r1['date']][$r1['ref_no']]['items'][$ikey]['commission_sales_qty'] += $row_qty;
					$this->table[$bid][$r1['date']][$r1['ref_no']]['items'][$ikey]['sa_commission_amt'] += $commission_amt;
					
					// count the current item sales as part of the commission sales amount
					$this->table[$bid][$r1['date']][$r1['ref_no']]['commission_sales_amt'] += $row_amt;
					$this->table[$bid][$r1['date']][$r1['ref_no']]['commission_sales_qty'] += $row_qty;
					$this->date_sales[$bid][$r1['date']]['commission_sales_amt'] += $row_amt;
					$this->date_sales[$bid][$r1['date']]['commission_sales_qty'] += $row_qty;

					// check the highest commission value
					if($r2['commission_method'] != "Flat" && $commission_amt > $top_commission_amt[$r1['date']][$r1['ref_no']]) $top_commission_amt[$r1['date']][$r1['ref_no']] = $commission_amt;
					else $top_commission_amt[$bid][$r1['date']][$r1['ref_no']] += $commission_amt;
					$this->table[$bid][$r1['date']][$r1['ref_no']]['sa_commission_amt'] += $commission_amt;
					$this->date_sales[$bid][$r1['date']]['sa_commission_amt'] += $commission_amt;
					$this->total_sales['flat_rate']['commission_sales_amt'] += $row_amt;
					$this->total_sales['flat_rate']['commission_sales_qty'] += $row_qty;
					$this->total_sales['sa_commission_amt'] += $commission_amt;
				}
				$con_multi->sql_freeresult($q2);
			}
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
    	$filename = "sac_calculation_".time().".xls";
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

		// get sales agent list
		$q1 = $con->sql_query("select * from sa order by sa.code");

		$this->sa_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$this->sa_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$this->table = $this->date_sales = $this->total_sales = $this->range_table = $this->range_total = array();
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

		if($this->sa_id){
			$con->sql_query("select name from sa where id = ".mi($this->sa_id));
			$sa_desc = $con->sql_fetchfield(0);
			$con->sql_freeresult();
		}else $sa_desc = "All";
		
		$this->report_title[] = "Sales Agent: ".$sa_desc;
		
		$tran_status = "All";
		if($this->tran_status != "all"){
			if($this->tran_status == -1) $tran_status = "Cancelled";
			elseif($this->tran_status == 1) $tran_status = "Valid";
			else  $tran_status = "Pruned";
		}
		$this->report_title[] = "Transaction Status: ".$tran_status;
		
		$do_status = "All";
		if($this->do_status > 0){
			if($this->do_status == 1) $do_status = "Approved";
			else  $do_status = "Checkout";
		}
		$this->report_title[] = "DO Status: ".$do_status;
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		$smarty->assign('table', $this->table);
		$smarty->assign('range_table', $this->range_table);
		//print_r($this->date_sales);
		$smarty->assign('date_sales', $this->date_sales);
		$smarty->assign('total_sales', $this->total_sales);
		$smarty->assign('range_total', $this->range_total);
	}
	
	function process_form(){
	    global $con, $smarty, $sessioninfo;
		
		$this->year = $_REQUEST['year'];
		$this->month = $_REQUEST['month'];
		$this->counter_id = $_REQUEST['counter_id'];
		$this->department_id = $_REQUEST['department_id'];
		$this->sku_type = $_REQUEST['sku_type'];
		$this->sales_type = $_REQUEST['sales_type'];
		$this->sa_id = $_REQUEST['sa_id'];
		$this->tran_status = $_REQUEST['tran_status'];
		$this->do_status = $_REQUEST['do_status'];
		
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
		
		if($this->counter_id!='all') $this->pos_filter[] = "pos.counter_id=".mi($this->counter_id);
		
		if($this->tran_status!='all'){
			if($this->tran_status==1) $this->pos_filter[] = "pos.cancel_status=0";
			elseif($this->tran_status==2) $this->pos_filter[] = "pos.prune_status=1 and pos.cancel_status=1";
			else $this->pos_filter[] = "pos.prune_status=0 and pos.cancel_status=1";
		}
		
		switch ($this->do_status){
			case 1: // show approved
				$this->do_filter[] = "do.status=1 and do.approved=1 and do.checkout=0";
				break;
			case 2: // show checkout
				$this->do_filter[] = "do.status=1 and do.approved=1 and do.checkout=1 ";
				break;
			default:
				$this->do_filter[] = "do.status=1 and do.approved=1";
				break;
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
	
	function load_counters($sql_only=false){
		global $con, $smarty, $sessioninfo;

		$counters = $filter = array();
		if(BRANCH_CODE != "HQ") $branch_id = mi($sessioninfo['branch_id']);
		else $branch_id = mi($_REQUEST['branch_id']);
		
		//if(!$branch_id) return;

		$filter[] = "c.branch_id = $branch_id";
		$filter = "and ".join(" and ", $filter);
		$sql = $con->sql_query("select c.*, branch.code 
								from counter_settings c left join branch on c.branch_id=branch.id 
								where c.active=1 $filter
								order by branch.sequence,branch.code,network_name");

		while($r = $con->sql_fetchassoc()){
			$counters[] = $r;
		}
		
		if(!$sql_only){
			$con->sql_freeresult($sql);
			$smarty->assign('counters', $counters);
			$ret['html'] = $smarty->fetch('report.sa_daily_details.counters.tpl');
			$ret['ok'] = 1;
			print json_encode($ret);
			exit;
		}else{
			$smarty->assign("counters", $counters);
		}
	}
}

$SA_DAILY_DETAILS = new SA_DAILY_DETAILS('Sales Agent Daily Details Report');
?>
