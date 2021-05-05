<?php
/*
3/5/2012 11:49:22 AM Justin
- Added new filter to skip those goods return items from POS.

3/12/2012 6:04:43 PM Justin
- Fixed the date filter for "-allbranch" to refer as current branch instead of next month.

5/7/2012 4:07:32 PM Justin
- Fixed bug where system never validate either the sales agent valid in db.

11/27/2012 10:57 AM Justin
- Bug Fixed on system could not calculate the latest month as if found some of the SA do not have sales during the past few months.

2/20/2013 3:59 PM Justin
- Enhanced to calculate average sales amount by number for SA base on config.

6/16/2015 11:57 AM Justin
- Bug fixed on category checking for commission that will cause to sum up wrong sales amount.

6/18/2015 10:20 AM Justin
- Bug fixed on sales amount did not deduct from mix & match.

6/26/2015 5:52 PM Justin
- Bug fixed on commission amount have calculated wrongly.

6/1/2016 2:45 PM Justin
- Bug fixed on wrong total cost calculation.

2/3/2017 2:59 PM Andy
- Fixed to exclude gst from POS Amount.
- Change to use inv_line_gross_amt2 for DO Amount.

12/22/2017 1:54 PM Justin
- Enhanced the cronjob to recalculate by daily instead of monthly basis.

3/29/2018 3:06 PM Justin
- Bug fixed on sales cache doesn't calculating.

3/30/2018 5:32 PM Justin
- Enhanced to recalculate sales cache base on sa_sales_cache_monitoring table.

10/19/2018 11:27 AM Justin
- Bug fixed on commission range checking issue.

11/19/2018 3:08 PM Justin
- Bug fixed on the transaction count issue.

11/22/2018 2:04 PM Justin
- Enhanced to have min_date for recalculate sa sales purpose.

3/26/2019 5:27 PM Justin
- Enhanced to load the certain functions from appCore.

10/22/2019 10:45 AM Justin
- Enhanced to calculate sales agent sales base on ratio set from POS counter (v202).
- Enhanced the sales calculation to compatible with old and new version of POS counter.

10/24/2019 4:48 PM Justin
- Bug fixed on sales range by amount or qty is not working properly.

10/31/2019 1:55 PM Justin
- Bug fixed on commission by range will cause the commission calculated wrongly.

11/6/2019 5:05 PM Justin
- Enhanced to use new way for calculating commission by sales/qty range.

12/23/2019 4:35 PM Justin
- Bug fixed on sales won't be stored into cache table while there are no commission (last time used to be store it).

12/30/2019 5:46 PM Justin
- Bug fixed on sales won't be stored into cache table while sales agent was having commission but it does not meet the requirements.
*/


define('TERMINAL',1);
define('QUERY_PER_CALL', 2000);
include("include/common.php");
ob_end_clean();
//$maintenance->check(1);

ini_set('memory_limit', '512M');
set_time_limit(0);
error_reporting (E_ALL ^ E_NOTICE);

// check if myself is running, exit if yes
if (!preg_match('/(root|arms|admin|wsatp)/', `whoami`) || $config['arms_go_modules']){
	@exec('ps x | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
	print "Checking other process using ps x\n";
}else{
	@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
	print "Checking other process using ps ax\n";
}

$arg = $_SERVER['argv'];
$branch = BRANCH_CODE;
$datefilter = "";
$allbranch = false;

array_shift($arg);
while($a = array_shift($arg)){
	switch ($a){
		case '-branch':
			$branch = array_shift($arg);
			break;
		case '-allbranch':
			$allbranch = true;
			break;
		case '-runall':
			$datefilter = "all";
			break;
		case '-date':
			$dt = array_shift($arg);
			if (strtotime($dt)!==false){
				$datefilter = $dt;
			}
			else{
				die("Error: Invalid date $dt.");
			}
			break;
		case '-min_date':
			$dt = array_shift($arg);
			if (strtotime($dt)!==false){
				$min_date = $dt;
			}
			else{
				die("Error: Invalid date $dt.");
			}
			break;
		default:
			die("Unknown option: $a\n");
	}
}

if ($allbranch){
	// run all branch
	$br = $con->sql_query("select code from branch");
	while($r=$con->sql_fetchassoc($br)){
		run($r['code']);
	}
	$con->sql_freeresult($br);
}else{
	run($branch);
}
$curr_update_date = date("Y-m-d", time());
file_put_contents("last_calc_sa_sales_cache.txt", $curr_update_date);

//$con->sql_query("optimize table category_sales_cache");
//$con->sql_query("analyze table category_sales_cache");

function run($branch){
	global $con, $datefilter, $config, $min_date, $appCore;

	$q1 = $con->sql_query("select id from branch where code = ".ms($branch));
	$r = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	if (!$r){
		die("Error: Invalid branch $branch.");
	}
	$bid = $r['id'];

	print "Runnning branch $branch \n";
	
	update_sales_cache($bid, -1);    // create all cache table
	
	/*$con->sql_query("create table if not exists sa_sales_cache_b$bid (
					 sa_id int(11),
					 year int(4),
					 month int(2),
					 amount double,
					 cost double,
					 commission_amt double,
					 qty double,
					 transaction_count int(11) not null default 0,
					 sales_type enum('pos','open', 'credit_sales'),
					 primary key(sa_id,year,month,sales_type),
					 index(sa_id), index(sales_type))");
	
	$con->sql_query("alter table sa_sales_cache_b$bid convert to charset latin1 collate latin1_general_ci");*/

	if($min_date){
		$ddel = "date >= ".ms($min_date);
	    $do_date_select = "and do.do_date >= ".ms($min_date);
	    $pos_date_select = "and pos.date >= ".ms($min_date);
	
		$tmp_yr = date("Y", strtotime($date));
		$tmp_mth = date("m", strtotime($date));
	}elseif ($datefilter == 'all'){ // executing -runall
		$con->sql_query("select min(date) as date from sa_sales_cache_b$bid");
		$mindate = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		if($mindate){
			$ddel = "date >= ".ms($mindate);
			$do_date_select = "and do.do_date >= ".ms($mindate);
			$pos_date_select = "and pos.date >= ".ms($mindate);
		}
	}elseif($datefilter != ''){ // executing -date
	    $ddel = "date = ".ms($datefilter);
	    $do_date_select = "and do.do_date = ".ms($datefilter);
	    $pos_date_select = "and pos.date = ".ms($datefilter);
	}else{ // executing -allbranch or -branch
		// new method of recalculation sales cache
		$recalc_date_list = array();
		$q1 = $con->sql_query("select * from sa_sales_cache_monitoring where branch_id = ".mi($bid));
		
		while($r = $con->sql_fetchassoc($q1)){
			if(!in_array($r['date'], $recalc_date_list)) $recalc_date_list[] = $r['date'];
		}
		$con->sql_freeresult($q1);

		if($recalc_date_list){
			$do_date_select = "and do.do_date in ('".join("','", $recalc_date_list)."')";
			$pos_date_select = "and pos.date in ('".join("','", $recalc_date_list)."')";
			$ddel = "date in ('".join("','", $recalc_date_list)."')";
		}else{
			print "No sales to calculate. \n";
			return;
		}
	}
	if($ddel!=''){
		$con->sql_query("delete from sa_sales_cache_b$bid where $ddel");
	}
	

	$sql = $data = $sa_range_commission = $range_data = array();
	$sql[] = "select 'DO' as type, do_type, do.mst_sa, di.dtl_sa, di.do_id as mst_id, do.do_date as date,
			  di.do_id as receipt_ref_no, di.inv_line_gross_amt2 as cost_price, di.cost, do.do_markup,do.markup_type, 0 as pi_amount, 
			  uom.fraction, ((di.ctn*uom.fraction)+di.pcs) as qty, di.sku_item_id, sku.category_id, c.level as cat_level, sku.brand_id, 
			  sku.sku_type, sku.vendor_id, do.do_no as doc_no, di.item_discount,
			  if(sip.trade_discount_code is not null and sip.trade_discount_code != '', sip.trade_discount_code, sku.default_trade_discount_code) as trade_discount_code, 0 as sales_cache_qty,
			  0 as cancel_status
			  from `do`
			  left join `do_items` di on di.do_id = do.id and di.branch_id = do.branch_id
			  left join `uom` on uom.id = di.uom_id
			  left join `sku_items` si on si.id = di.sku_item_id
			  left join `sku` on sku.id = si.sku_id
			  left join `category` c on c.id = sku.category_id
			  left join `sku_items_price` sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($bid)."
			  where do.branch_id = ".mi($bid)." and do.active=1 and do.approved=1 and do.checkout=1 and do.do_type in ('open', 'credit_sales') and ((do.mst_sa != '' and do.mst_sa is not null) or (di.dtl_sa != '' and di.dtl_sa is not null))
			  $do_date_select";

	$sql[] = "select 'POS' as type, 'pos' as do_type, pos.receipt_sa as mst_sa, pi.item_sa as dtl_sa, pos.id as mst_id, pos.date,
			  pos.receipt_ref_no, 0 as cost_price, sisc.cost, 0 as do_markup, '' as markup_type, 
			  (pi.price-pi.discount-pi.discount2-pi.tax_amount) as pi_amount, 1 as fraction, pi.qty, pi.sku_item_id, sku.category_id, c.level as cat_level,
			  sku.brand_id, sku.sku_type, sku.vendor_id, pos.receipt_no as doc_no, 0 as item_discount,
			  if(sip.trade_discount_code is not null and sip.trade_discount_code != '', sip.trade_discount_code, sku.default_trade_discount_code) as trade_discount_code, sisc.qty as sales_cache_qty,
			  pos.cancel_status
			  from `pos`
			  left join `pos_items` pi on pi.pos_id = pos.id and pi.branch_id = pos.branch_id and pi.date = pos.date and pi.counter_id = pos.counter_id
			  left join `sku_items` si on si.id = pi.sku_item_id
			  left join `sku` on sku.id = si.sku_id
			  left join `category` c on c.id = sku.category_id
			  left join `sku_items_price` sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($bid)."
			  left join `sku_items_sales_cache_b".mi($bid)."` sisc on sisc.sku_item_id = si.id and sisc.date = pos.date
			  join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date and pf.finalized=1
			  where pos.branch_id = ".mi($bid)." and ((pos.receipt_sa != '' and pos.receipt_sa is not null) or (pi.item_sa != '' and pi.item_sa is not null))
			  $pos_date_select";

	$all_sql = join(" UNION ALL ", $sql)." order by date, do_type, receipt_ref_no";
	$q1 = $con->sql_query($all_sql);

	$curr_trans_key = $last_trans_key = "";
	$use_comm_ratio = array();
	while($r1 = $con->sql_fetchassoc($q1)){
		$sa_list = array();
		$row_amt_ctn = $row_amt_pcs = $row_amt = 0;

		if($r1['mst_sa']) $sa_list = unserialize($r1['mst_sa']);
		else $sa_list = unserialize($r1['dtl_sa']);
		
		if(count($sa_list) == 0) continue;
		
		if($r1['type'] == "DO"){
			$row_amt = round($r1['cost_price'],2);
			$row_cost = $r1['cost'] * $r1['qty'] / $r1['fraction'];
		}else{
			if($r1['cost']) $row_cost = $r1['cost'] / $r1['sales_cache_qty'] * $r1['qty'];
			else $row_cost = 0;
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
		
		$yrmth = date("Ym", strtotime($r1['date']));
		
		// check if the sales agent got set with ratio then use the ratio to calculate the sales amount for each sales agent
		$sa_ratio_sales_list = array();
		if($r1['mst_sa']){
			if($sa_ratio_result['use_ratio']){
				$sa_ratio_sales_list = $sa_ratio_result['sa_ratio_sales_list'];
				$use_comm_ratio['flat_rate'][$r1['date']] = 1;
				$use_comm_ratio['range'][$yrmth] = 1;
			}elseif(count($sa_list) > 1 && $config['sa_calc_average_sales']){ // otherwise check if turn on config to calculate average sales for all sales agent
				$row_cost = round($row_cost / count($sa_list), $config['global_cost_decimal_points']);
				$row_amt = round($row_amt / count($sa_list), 2);
			}
		}
		
		// create tmp key for transaction count
		if($r1['do_type'] != "pos"){ // it is from DO
			$curr_trans_key = $r1['mst_id'];
		}else{ // it is from POS
			$curr_trans_key = $r1['receipt_ref_no'];
		}
		
		//$ttl_cost += $row_cost;
		//$ttl_amt += $row_amt;
		//$ttl_qty += $row_qty;
		$date = date("Y-m-d", strtotime($r1['date']));
		$yr = date("Y", strtotime($date));
		$mth = date("m", strtotime($date));
		
		// need to keep this because we need it for delete purpose
		
		foreach($sa_list as $sa_id=>$sa_info){			
			if($sa_ratio_result['id_list_existed']){
				$tmp_sa_id = $sa_id; // use sales agent ID from array key
			}else{
				$tmp_sa_id = $sa_info; // use the array values as sales agent ID
			}
			
			if(!$tmp_sa_id) continue;
			
			// to perform delete for range cache sales table
			$range_data[$yr][$mth][$sa_id]['need_remove'] = true;
			
			// check whether the S/A match all the conditions set from commission module
			$is_flat_rate_comm = $is_sales_qty_range_comm = false;
			$q2 = $con->sql_query("select *, sa.id as sa_id, saci.id as saci_id, saci.date_from
								   from sa
								   join sa_commission_settings sas on sas.sa_id = sa.id and sas.branch_id = ".mi($bid)."
								   join sa_commission sac on sac.id = sas.sac_id and sac.branch_id = sas.branch_id
								   join sa_commission_items saci on saci.sac_id = sac.id and saci.branch_id = sac.branch_id
								   where sa.id = ".mi($tmp_sa_id)." and saci.date_from <= ".ms($r1['date'])." and (saci.date_to is null or saci.date_to = '' or saci.date_to >= ".ms($r1['date']).") and sac.active = 1 and saci.active = 1 and sas.active = 1
								   order by saci.commission_method");

			if($con->sql_numrows($q2) > 0){
				$comm_list = array();
				while($r2 = $con->sql_fetchassoc($q2)){
					// use the sales amount calculated by using ratio
					if($sa_ratio_result['use_ratio']){
						$row_amt = $sa_ratio_sales_list[$r2['sa_id']]['sales_amt'];
					}
					
					if($r2['commission_method'] != "Flat"){
						// do not sum up the commission again as if it is being calculated previously
						if($sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['result'] == "failed") continue;
					}else{
						// the reason we skip for flat rate is because some times cashier cancelled the bill on the next day
						// system still need to recalculate as if the commission was set by sales/qty range
						if($r1['cancel_status']) continue;// skip those cancelled receipt
					}
					
					// check if the conditions are met
					$conditions = unserialize($r2['conditions']);
					$condition_met = $appCore->salesAgentManager->check_commission_conditions($conditions, $r1);
					if(!$condition_met) continue;
					
					if($r2['commission_method'] != "Flat"){ // is set by sales or qty range
						$commission_value_list = unserialize($r2['commission_value']);
						
						$prms = array();
						$prms['sac_date'] = $r1['date'];
						$prms['conditions'] = $conditions;
						$prms['commission_method'] = $r2['commission_method'];
						$prms['commission_value_list'] = $commission_value_list;

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
							$row_cost = $sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['ttl_cost'];
							$row_qty = $sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['ttl_sales_qty'];
							$row_trans_count = $sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['ttl_trans_count'];
						}
					}else $commission_value = $r2['commission_value']; // is set by flat rate

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
							$range_data[$yr][$mth][$r2['sa_id']]['got_data'] = true;
							$range_data[$yr][$mth][$r2['sa_id']]['amount'] += $row_amt;
							$range_data[$yr][$mth][$r2['sa_id']]['cost'] += $row_cost;
							$range_data[$yr][$mth][$r2['sa_id']]['qty'] += $row_qty;
							$range_data[$yr][$mth][$r2['sa_id']]['commission_amt'] += $commission_amt;
							$range_data[$yr][$mth][$r2['sa_id']]['transaction_count'] += $row_trans_count;
							
							// mark this commission by sales / qty range become claimed
							$sa_range_commission[$r2['sa_id']][$r2['saci_id']][$bid]['claimed'] = true;
						}
						
						$is_sales_qty_range_comm = true;
						continue;
					}

					// check the highest commission value
					//if($commission_amt > $comm_list[$r1['doc_no']]['top_commission_amt']) $comm_list[$r1['doc_no']]['top_commission_amt'] = $commission_amt;
					//$comm_list[$r1['doc_no']]['ttl_commission_amt'] += $commission_amt;
					//$comm_list[$r1['doc_no']]['commission_method'] = $r2['commission_method'];
					
					
					//$comm_list[$r1['doc_no']][$r2['sa_id']]['commission_amt'] += $commission_amt;
					
					// flat rate commission
					$data[$r2['sa_id']][$date][$r1['do_type']]['commission_amt'] += round($commission_amt, 2);
					$is_flat_rate_comm = true;
				}
			}
			$con->sql_freeresult($q2);
			
			// need to store the sales and qty value into flat rate table as if couldn't find the commission
			// receipt must not be cancelled and matched with commission by qty or sales range
			// sum up as flat rate sales as if it has commission or couldn't find any commission from flat rate and commission by sales / qty range
			if(!$r1['cancel_status'] && ($is_flat_rate_comm || (!$is_flat_rate_comm && !$is_sales_qty_range_comm))){
				// use the sales amount calculated by using ratio
				if($sa_ratio_result['use_ratio']){
					$row_amt = $sa_ratio_sales_list[$tmp_sa_id]['sales_amt'];
				}
				
				$data[$tmp_sa_id][$date][$r1['do_type']]['cost'] += $row_cost;
				$data[$tmp_sa_id][$date][$r1['do_type']]['amount'] += $row_amt;
				$data[$tmp_sa_id][$date][$r1['do_type']]['qty'] += $row_qty;
				if($curr_trans_key != $last_trans_key) $data[$tmp_sa_id][$date][$r1['do_type']]['transaction_count']++;
			}
			unset($is_flat_rate_comm, $is_sales_qty_range_comm);
			
			unset($tmp_sa_id);
		}
		$last_trans_key = $curr_trans_key;
	}
	$con->sql_freeresult($q1);

	foreach($data as $sa_id=>$date_list){
		foreach($date_list as $date=>$sales_type_list){
			foreach($sales_type_list as $sales_type=>$f){
				$ins = array();
				$ins['sa_id'] = $sa_id;
				$ins['date'] = $date;
				$ins['year'] = date("Y", strtotime($date));
				$ins['month'] = date("m", strtotime($date));
				$ins['cost'] = round($f['cost'], $config['global_cost_decimal_points']);
				$ins['amount'] = round($f['amount'], 2);
				$ins['commission_amt'] = round($f['commission_amt'], 2);
				$ins['qty'] = $f['qty'];
				$ins['sales_type'] = $sales_type;
				$ins['transaction_count'] = $f['transaction_count'];
				$ins['use_commission_ratio'] = $use_comm_ratio['flat_rate'][$date];

				$con->sql_query("replace into sa_sales_cache_b$bid ".mysql_insert_by_field($ins));
			}
		}
  	}
	unset($data);
	
	// insert data into commission by qty/sales range
	if($range_data){
		foreach($range_data as $yr=>$mth_list){
			foreach($mth_list as $mth=>$sa_list){
				foreach($sa_list as $sa_id=>$f){
					// remove the cache since we're going recalculate it
					if($f['need_remove']){
						$con->sql_query("delete from sa_range_sales_cache_b$bid where sa_id = ".mi($sa_id)." and year = ".mi($yr)." and month = ".mi($mth));
					}
					
					// insert only when got data
					if($f['got_data']){
						$yrmth = $yr.$mth;
						$ins = array();
						$ins['sa_id'] = $sa_id;
						$ins['year'] = $yr;
						$ins['month'] = $mth;
						$ins['cost'] = round($f['cost'], $config['global_cost_decimal_points']);
						$ins['amount'] = round($f['amount'], 2);
						$ins['commission_amt'] = round($f['commission_amt'], 2);
						$ins['qty'] = $f['qty'];
						$ins['transaction_count'] = $f['transaction_count'];
						$ins['use_commission_ratio'] = $use_comm_ratio['range'][$yrmth];

						$con->sql_query("replace into sa_range_sales_cache_b$bid ".mysql_insert_by_field($ins));
					}
				}
			}
		}
	}
	unset($range_data);
	
	print "Done.\n";
	
	// mark recalculate date as finish
	if($recalc_date_list) $con->sql_query("delete from sa_sales_cache_monitoring where branch_id = ".mi($bid)." and date in ('".join("','", $recalc_date_list)."')");
	
	// sales cache table optimisation
	$con->sql_query("optimize table sa_sales_cache_b$bid");
	$con->sql_query("analyze table sa_sales_cache_b$bid");
	$con->sql_query("optimize table sa_range_sales_cache_b$bid");
	$con->sql_query("analyze table sa_range_sales_cache_b$bid");
}

function xr($r, &$fields)
{
	$ret = array();
	$fields = array();
	foreach($r as $k=>$v)
	{
	    if (is_numeric($k)) continue;
	    $fields[] = "`$k`";
		$ret[] = ms($v);
	}
	return $ret;
}

?>
