<?php
/*
6/28/2012 12:06:12 PM Justin
- Fixed bug of cron unable to search for future price items.

10:45 AM 6/29/2012 Justin
- Added to update last update for SKU item to make changes for sync to frontend.

6/10/2013 2:04 PM Justin
- Enhanced to capture log.

9/2/2013 2:49 PM Justin
- Bug fixed on system stop to process future price after processed one result.

5/23/2014 4:55 PM Justin
- Bug fixed on cron status had marked as run but data missing on the price change history.

3/19/2015 5:57 PM Justin
- Enhanced to pickup price history and store into item's old info from future price.

3/30/2015 12:23 PM Justin
- Enhanced to comment out the history searching.
- Enhanced to optimise the process speeds.

3/30/2015 5:01 PM Justin
- Enhanced to skip those prices that are current same with system.

4/1/2015 9:52 AM Justin
- Bug fixed on system only run once for batch price change.

2/21/2017 2:01 PM Justin
- Enhanced to comment off the old information store for QPrice.

1/17/2018 10:54 AM Justin
- Bug fixed on order sequence while price change is wrong while updating one SKU item multiple times in different batches at same time.

11/9/2018 10:40 AM Justin
- Bug fixed on Batch Price Price item ID insertion to price history is wrong.

2/14/2020 3:25 PM William
- Enhanced to Change log type "FUTURE PRICE" to "FUTURE_PRICE".
*/


define('TERMINAL',1);
define('QUERY_PER_CALL', 2000);
include("include/common.php");
ob_end_clean();
//$maintenance->check(1);

ini_set('memory_limit', '1024M');
set_time_limit(0);

// check if myself is running, exit if yes
if (!preg_match('/(root|arms|admin|wsatp)/', `whoami`))
  @exec('ps x | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
else
  @exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
  
if (count($exec)>1)
{
	print date("[H:i:s m.d.y]")." Another process is already running\n";
	print_r($exec);
	exit;
}

$arg = $_SERVER['argv'];
$branch = BRANCH_CODE;
$datefilter = "";
$allbranch = $custom_dt = false;

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
			$custom_dt = true;
			break;
		default:
			die("Unknown option: $a\n");
	}
}

if ($allbranch){
	// run all branch
	$br = $con->sql_query("select code from branch");
	while($r=$con->sql_fetchrow($br)){
		run($r[0]);
	}
}else{
	run($branch);
}

function run($branch){
	global $con, $dt, $custom_dt, $config;

	$con->sql_query("select id from branch where code = ".ms($branch));
	$r = $con->sql_fetchrow();
	if (!$r){
		die("Error: Invalid branch $branch.");
	}
	$bid = $r[0];

	if(!$dt) $dt = date("Y-m-d H:i:s");

	print "\nProcessing branch ".$branch."...\n";

	$qprice_delete = array();
	// check and update those approved documents
	//$filter = "and id = 9";
	//$bid = 4;
	$q1 = $con->sql_query("select *
						   from sku_items_future_price sifp
						   where sifp.active = 1 and sifp.status = 1 and sifp.approved = 1 and sifp.cron_status = 0 $filter and sifp.branch_id = ".mi($bid)."
						   order by sifp.date, sifp.hour, sifp.minute, sifp.id");

	$succ_count = $con->sql_numrows($q1);

	if($succ_count > 0){
		print "Found ".$succ_count." Batch Price Change records for ".$branch."...\n";
					
		while($r = $con->sql_fetchassoc($q1)){
			log_br($r['user_id'], 'FUTURE_PRICE', $r['id'], "[Begin] Batch Price Change (#$r[id], BID:$r[branch_id])");
		
			$main_date = $r['date']." ".$r['hour'].":".$r['minute'].":00";
			$effective_branches = unserialize($r['effective_branches']);
			
			$mst_cron = true;
			$new_effective_branches = array();
			if(!$r['date_by_branch'] && strtotime($dt) < strtotime($main_date)){
				$mst_cron = false;
				continue;
			}else{
				$q2 = $con->sql_query("select sifpi.*
									   from sku_items_future_price_items sifpi 
									   where sifpi.fp_id = ".mi($r['id'])." and sifpi.branch_id = ".mi($r['branch_id']));

				while($r1 = $con->sql_fetchassoc($q2)){
					$upd_item = array();
					foreach($effective_branches as $ebid=>$eb){
						$branch_date = $eb['date']." ".$eb['hour'].":".$eb['minute'].":00";
						//print_r($eb);
						if($r['date_by_branch'] && strtotime($dt) < strtotime($branch_date)){
							$mst_cron = false;
							$new_effective_branches[$ebid]['cron_status'] = 0;
							continue;
						}else{
							if($eb['cron_status']) continue;
						}
						
						if($r['date_by_branch']){
							$history_date = $branch_date;
						}else{
							$history_date = $main_date;
						}

						$fp_inserted = false;
						if($r1['type'] == "qprice"){
							/*$q3 = $con->sql_query("select min_qty, price from sku_items_qprice where sku_item_id = ".mi($r1['sku_item_id'])." and branch_id = ".mi($ebid));

							while($tmp = $con->sql_fetchassoc($q3)){
								$upd_item['old_info'][$ebid][] = $tmp;
							}
							$con->sql_freeresult($q3);*/
						
							// insert into qprice table
							$ins = array();
							$ins['branch_id'] = $ebid;
							$ins['sku_item_id'] = $r1['sku_item_id'];
							$ins['min_qty'] = $r1['min_qty'];
							$ins['price'] = $r1['future_selling_price'];
							$ins['last_update'] = "CURRENT_TIMESTAMP";
							
							$tmp_sql = $con->sql_query("replace into sku_items_qprice ".mysql_insert_by_field($ins));
							
							if($con->sql_affectedrows($tmp_sql) > 0) $fp_inserted = true;
							unset($tmp_sql, $ins);
							
							// insert into qprice history table
							$ins = array();
							$ins['branch_id'] = $ebid;
							$ins['sku_item_id'] = $r1['sku_item_id'];
							$ins['min_qty'] = $r1['min_qty'];
							$ins['price'] = $r1['future_selling_price'];
							$ins['added'] = "CURRENT_TIMESTAMP";
							$ins['user_id'] = $r['user_id'];
							$ins['fp_id'] = $r['id'];
							$ins['fpi_id'] = $r1['id'];
							$ins['fp_branch_id'] = $r['branch_id'];

							$con->sql_query("replace into sku_items_qprice_history ".mysql_insert_by_field($ins));
							unset($ins);
						}elseif($r1['type'] == "normal"){
							// check if the cron has been run before
							/*$filters = array();
							$filters[] = "branch_id = ".mi($ebid);
							$filters[] = "fp_id = ".mi($r['id']);
							$filters[] = "fpi_id = ".mi($r1['id']);
							$filters[] = "fp_branch_id = ".mi($r1['branch_id']);
							//$filters[] = "sku_item_id = ".mi($r1['sku_item_id']);
							
							$filter = join(" and ", $filters);
							
							$fp_check = $con->sql_query("select * from sku_items_price_history where ".$filter);
							
							if($con->sql_numrows($fp_check) > 0){
								$new_effective_branches[$ebid]['cron_status'] = 1;
								continue;
							}*/
							
							/*$q3 = $con->sql_query("select price, cost, source, ref_id, user_id, trade_discount_code from sku_items_price_history where sku_item_id = ".mi($r1['sku_item_id'])." and branch_id = ".mi($ebid)." and added <= ".ms($history_date)." order by added desc limit 1");
							$price_info = $con->sql_fetchassoc($q3);
							$con->sql_freeresult($q3);
							
							if($price_info) $upd_item['old_info'][$ebid] = $price_info;
							else{ // in case user never done any price update before, need to pickup info from master
								$q3 = $con->sql_query("select if(sip.price is null,si.selling_price, sip.price) as price, sku.default_trade_discount_code,
													   if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as trade_discount_code
													   from sku_items si
													   left join sku on sku_id = sku.id
													   left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($ebid)." 
													   where si.id = ".mi($r1['sku_item_id']));

								$t=$con->sql_fetchassoc($q3);
								$con->sql_freeresult($q3);
								$price_info = array();
								$price_info['price'] = $t['price'];
								$price_info['user_id'] = $r['user_id'];
							
								// must hv price type
								if($config['sku_always_show_trade_discount']){
									$price_info['trade_discount_code'] = $t['default_trade_discount_code'];	// use master
								}
								
								$prms = array();
								$prms['sku_item_id'] = $r1['sku_item_id'];
								$prms['branch_id'] = $ebid;
								$tmp = get_last_cost($prms);
								$price_info['cost'] = $tmp['cost'];
								$price_info['source'] = $tmp['source'];
								unset($prms, $tmp);
								
								$upd_item['old_info'][$ebid] = $price_info;
								unset($price_info);
							}*/
							
							// normal selling price
							$q3 = $con->sql_query("select * from sku_items_price where branch_id=".mi($ebid)." and sku_item_id=".mi($r1['sku_item_id']));
							$sip = $con->sql_fetchassoc($q3);
							$con->sql_freeresult($q3);
							
							// check and skip price change if found same price
							if($sip) $sp = $sip['price'];
							else $sp = $r1['selling_price'];
							
							if($r1['future_selling_price'] == $sp){
								$new_effective_branches[$ebid]['cron_status'] = 1;
								continue;
							}
							
							// get latest cost
							$q3 = $con->sql_query("select grn_cost from sku_items_cost where sku_item_id = ".mi($r1['sku_item_id'])." and branch_id = ".mi($ebid));
							$tmp = $con->sql_fetchassoc($q3);
							$con->sql_freeresult($q3);
							$cost = $tmp['grn_cost'];
							unset($tmp);

							// insert into price table
							$ins = array();
							$ins['branch_id'] = $ebid;
							$ins['sku_item_id'] = $r1['sku_item_id'];
							$ins['last_update'] = "CURRENT_TIMESTAMP";
							$ins['price'] = $r1['future_selling_price'];
							$ins['cost'] = $cost;
							$ins['trade_discount_code'] = $r1['trade_discount_code'];
							
							$tmp_sql = $con->sql_query("replace into sku_items_price ".mysql_insert_by_field($ins));
							
							if($con->sql_affectedrows($tmp_sql) > 0) $fp_inserted = true;
							unset($ins, $tmp_sql);
							
							// insert into price history table
							$ins = array();
							$ins['branch_id'] = $ebid;
							$ins['sku_item_id'] = $r1['sku_item_id'];
							$ins['added'] = "CURRENT_TIMESTAMP";
							$ins['price'] = $r1['future_selling_price'];
							$ins['cost'] = $cost;
							$ins['source'] = "MASTER SKU";
							$ins['user_id'] = $r['user_id'];
							$ins['trade_discount_code'] = $r1['trade_discount_code'];
							$ins['fp_id'] = $r['id'];
							$ins['fpi_id'] = $r1['id'];
							$ins['fp_branch_id'] = $r['branch_id'];

							$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($ins));
							unset($ins);
						}else{
							// check if the cron has been run before
							/*$filters = array();
							$filters[] = "branch_id = ".mi($ebid);
							$filters[] = "fp_id = ".mi($r['id']);
							$filters[] = "fpi_id = ".mi($r1['id']);
							$filters[] = "fp_branch_id = ".mi($r1['branch_id']);
							//$filters[] = "sku_item_id = ".mi($r1['sku_item_id']);
							
							$filter = join(" and ", $filters);
							
							$fp_check = $con->sql_query("select * from sku_items_mprice_history where ".$filter);
							
							if($con->sql_numrows($fp_check) > 0){
								$new_effective_branches[$ebid]['cron_status'] = 1;
								continue;
							}*/
							
							/*$q3 = $con->sql_query("select type, price, user_id, trade_discount_code from sku_items_mprice_history where sku_item_id = ".mi($r1['sku_item_id'])." and branch_id = ".mi($ebid)." and type = ".ms($r1['type'])." and added <= ".ms($history_date)." order by added desc limit 1");
							$mprice_info = $con->sql_fetchassoc($q3);
							$con->sql_freeresult($q3);
							
							if($mprice_info) $upd_item['old_info'][$ebid] = $mprice_info;
							else{ // in case user never done any price update before, need to pickup info from master
								$q3 = $con->sql_query("select if(sip.price is null,si.selling_price, sip.price) as price, sku.default_trade_discount_code,
													   if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as trade_discount_code
													   from sku_items si
													   left join sku on sku_id = sku.id
													   left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($ebid)." 
													   where si.id = ".mi($r1['sku_item_id']));

								$t=$con->sql_fetchassoc($q3);
								$con->sql_freeresult($q3);
								$mprice_info = array();
								$mprice_info['price'] = $t['price'];
								$mprice_info['user_id'] = $r['user_id'];

								// must hv price type
								if($config['sku_always_show_trade_discount']){
									$mprice_info['trade_discount_code'] = $t['default_trade_discount_code'];	// use master
								}
								
								$upd_item['old_info'][$ebid] = $mprice_info;
							}
							unset($mprice_info);*/
							
							// select latest mprice
							$q3 = $con->sql_query("select * from sku_items_mprice where branch_id=".mi($ebid)." and sku_item_id=".mi($r1['sku_item_id'])." and type=".ms($r1['type']));
							$simp = $con->sql_fetchassoc($q3);
							$con->sql_freeresult($q3);
							
							// check and skip price change if found same price
							if($simp) $sp = $simp['price'];
							else $sp = $r1['selling_price'];
							
							if($r1['future_selling_price'] == $sp){
								$new_effective_branches[$ebid]['cron_status'] = 1;
								continue;
							}
							
						
							// insert into mprice table
							$ins = array();
							$ins['branch_id'] = $ebid;
							$ins['sku_item_id'] = $r1['sku_item_id'];
							$ins['type'] = $r1['type'];
							$ins['last_update'] = "CURRENT_TIMESTAMP";
							$ins['price'] = $r1['future_selling_price'];
							$ins['trade_discount_code'] = $r1['trade_discount_code'];
							
							$tmp_sql = $con->sql_query("replace into sku_items_mprice ".mysql_insert_by_field($ins));
							
							if($con->sql_affectedrows($tmp_sql) > 0) $fp_inserted = true;
							unset($ins, $tmp_sql);
							
							// insert into mprice history table
							$ins = array();
							$ins['branch_id'] = $ebid;
							$ins['sku_item_id'] = $r1['sku_item_id'];
							$ins['type'] = $r1['type'];
							$ins['added'] = "CURRENT_TIMESTAMP";
							$ins['price'] = $r1['future_selling_price'];
							$ins['user_id'] = $r['user_id'];
							$ins['trade_discount_code'] = $r1['trade_discount_code'];
							$ins['fp_id'] = $r['id'];
							$ins['fpi_id'] = $r1['id'];
							$ins['fp_branch_id'] = $r['branch_id'];

							$con->sql_query("replace into sku_items_mprice_history ".mysql_insert_by_field($ins));
							unset($ins);
						}
						
						if($fp_inserted){
							$new_effective_branches[$ebid]['cron_status'] = 1;
						}
					}
					
					// update old info for items
					if($upd_item){
						$upd = array();
						$upd['old_info'] = serialize($upd_item['old_info']);
						$con->sql_query("update sku_items_future_price_items set ".mysql_update_by_field($upd)." where id = ".mi($r1['id'])." and branch_id = ".mi($r1['branch_id']));
					}
					unset($upd, $upd_item);
					
					$con->sql_query("update sku_items set lastupdate = CURRENT_TIMESTAMP where id = ".mi($r1['sku_item_id']));
					$succ_count++;
					
					print "BATCH ID:".$r1['fp_id'].", BID:".$r1['branch_id'].", SKU ITEM ID:".$r1['sku_item_id']."...\r";
				}
				$con->sql_freeresult($q2);
			}
			
			foreach($new_effective_branches as $tmp_bid=>$tmp){
				if(!$tmp['cron_status']) $mst_cron = false;
				$effective_branches[$tmp_bid]['cron_status'] = $tmp['cron_status'];
			}
			
			$upd = array();
			if($mst_cron) $upd['cron_status'] = 1; // update master cron status if all cron status by branch had been updated
			$upd['effective_branches'] = serialize($effective_branches); // update cron status by branch
			
			$con->sql_query("update sku_items_future_price set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
			unset($new_effective_branches, $upd, $effective_branches);
			
			log_br($r['user_id'], 'FUTURE_PRICE', $r['id'], "[Success] Batch Price Change (#$r[id], BID:$r[branch_id])");
		}
		$con->sql_freeresult($q1);
		
		//print "Updated ".mi($succ_count)." approved SKU items...\n";
	}else print "No approved SKU items found for branch ".$branch."...\n"; 

	print "\nDone.\n";
}

function get_last_cost($prms){
	global $con;

	$ret['cost'] = 0;
	
	$q1 = $con->sql_query("select round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/uom.fraction,3) as cost
						   from grn_items
						   left join uom on uom_id = uom.id
						   left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
						   left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
						   where grn_items.branch_id = ".mi($prms['branch_id'])." and grn.approved and sku_item_id=".mi($prms['sku_item_id'])." 
						   having cost > 0
						   order by grr.rcv_date desc limit 1");
	$c = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	//print "using GRN $c[0]";
	if ($c){
		$ret['cost'] = $c['cost'];
		$ret['source'] = 'GRN';
	}
	
	if ($ret['cost']==0){
		$q1 = $con->sql_query("select round(po_items.order_price/po_items.order_uom_fraction,3) as cost
							   from po_items 
							   left join po on po_id = po.id and po.branch_id = po.branch_id 
							   where po.active and po.approved and po_items.branch_id = ".mi($prms['branch_id'])." and sku_item_id=".mi($prms['sku_item_id'])." 
							   having cost > 0
							   order by po.po_date desc limit 1");
		$c = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		//print "using PO $c[0]";
		if ($c){
			$ret['cost'] = $c['cost'];
			$ret['source'] = 'PO';
		}
	}
	
	if ($ret['cost']==0){
		$q1 = $con->sql_query("select cost_price from sku_items where id=".mi($prms['sku_item_id']));
		$c = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		//print "using MASTER $c[0]";

		if($c){
			$ret['cost'] = $c['cost_price'];
			$ret['source'] = 'MASTER SKU';
		}
	}
	
	return $ret;
}
?>