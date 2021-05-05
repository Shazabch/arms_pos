<?php
/*
4/29/2011 5:22:15 PM Andy
- Add function is_latest_than_last_pos()

5/4/2011 10:32:43 AM Andy
- Fix wrong timestamp checking.

7/11/2011 11:06:50 AM Andy
- Move pp_is_currency() and pch_is_currency() to counter_collection.include.php

9/5/2011 9:56:49 AM Andy
- Add function get_pos_receipt_no($bid, $date, $counter_id, $pos_id)

10/12/2011 5:29:54 PM Andy
- Add mix and match promotion at counter collection related module.

11/23/2011 5:49:13 PM Alex
- add function check_invalid_code(),  store_counter_collection_data(), change_array_value_to_string()

2/6/2012 7:21:37 PM Alex
- change check_invalid_code to be same as pos.invalid_sku.php

4/10/2012 5:25:10 PM Alex
- add trade in data => check_invalid_code()

4/23/2012 11:30:35 AM Andy
- Add new function is_cc_finalized($bid, $date)

12/30/2013 5:33 PM Andy
- Add new function check_and_pregen_pos_finalized($bid, $date)

4/18/2014 2:08 PM Andy
- Enhance the currency checking for pos_payment and pos_cash_history.

4/5/2017 4:26 PM Justin
- Added new function "check_sales_sync_status".

5/22/2017 9:51 AM Andy
- Change check_sales_sync_status() to check table pos_transaction_counter_sales_record.

8/18/2017 2:12 PM Justin
- Enhanced check missing record not to check deposit data.

9/21/2017 3:26 PM Justin
- Enhanced check missing record not to check pos drawer.

6/20/2018 1:54 PM Justin
- Enhanced exchange rate to round base on config instead of hardcoded 3 digits.
*/
$v109_time = strtotime("2011-04-04");   // 1301846400
$mm_discount_col_value = 'Mix & Match Total Disc';
if($smarty){
	$smarty->assign('mm_discount_col_value', $mm_discount_col_value);
}

function get_counter_version($bid, $cid){
	global $con;
	
	$con->sql_query("select revision from counter_status where branch_id=".mi($bid)." and id=".mi($cid));
	$revision = $con->sql_fetchfield(0);
	$con->sql_freeresult();
	
	return mi($revision);
}

function is_latest_than_last_pos($bid, $cid, $date){
	global $con;
	
	$bid = mi($bid);
	$cid = mi($cid);
	
	if(!$bid || !$cid || !$date)	die('Invalid parameters.');
	
	// get last pos domination time
	$con->sql_query("select max(timestamp) as e from pos_cash_domination where branch_id=$bid and counter_id=$cid and date=".ms($date));
	$e = $con->sql_fetchfield(0);
	$con->sql_freeresult();
	
	$con->sql_query("select id from pos where branch_id=$bid and counter_id=$cid and date=".ms($date)." and pos_time>".ms($e)." limit 1");
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	return $tmp ? false : true;
}

function pp_is_currency($remark, $amount){
	global $config;
	
	if(strpos($remark," @")){
		$remark_arr = explode(" @",$remark);
		
		$str1 = $remark_arr[0];
		$str2 = $remark_arr[1];
		
		if(is_numeric($str1) && is_numeric($str2))	$is_currency = true;
	}
	
	if($is_currency == true && $remark_arr){
		$currency = $remark_arr[0];
		$currency_rate = sprintf("%01.".$config['foreign_currency_decimal_points']."f", $remark_arr[1]);
		$rm_amt = $currency/$currency_rate;

       	$ret = array('is_currency'=>true, 'currency_amt'=>$currency, 'currency_rate'=>$currency_rate, 'rm_amt'=>$rm_amt);
	}
      else{
	    $ret = array('rm_amt'=>$amount);
	}

	return $ret;
}

function pch_is_currency($remark, $amount){
	global $config;
	
	if(strpos($remark," @")){
		$remark_arr = explode(" @",$remark);
		
		$str1 = trim($remark_arr[0]);
		$str2 = $remark_arr[1];
		
		if($str1 && strlen($str1)<=3 && is_numeric($str2))	$is_currency = true;
	}
	
	if($is_currency == true && $remark_arr){
		$currency_type = trim($remark_arr[0]);
		$currency_rate = sprintf("%01.".$config['foreign_currency_decimal_points']."f", $remark_arr[1]);
		$currency = $amount;
		$rm_amt = $currency/$currency_rate;
       	$ret = array('is_currency'=>true, 'currency_amt'=>$currency, 'currency_rate'=>$currency_rate, 'rm_amt'=>$rm_amt, 'currency_type'=>$currency_type);
	}
      else{
	    $ret = array('rm_amt'=>$amount);
	}

	return $ret;
}

function get_pos_receipt_no($bid, $date, $counter_id, $pos_id){
	global $con;
	
	$con->sql_query("select receipt_no from pos where branch_id=".mi($bid)." and date=".ms($date)." and counter_id=".mi($counter_id)." and id=".mi($pos_id));
	$receipt_no = $con->sql_fetchfield(0);
	$con->sql_freeresult();
	
	return $receipt_no;
}

function check_invalid_code($branch_id, $date){
	global $smarty,$con;
	
	$filter[]="pos.cancel_status=0";
	$filter[]="pos.date=".ms($date);
	$filter[]="pos.branch_id=".mi($branch_id);
	$filter[]="(pi.sku_item_id=0 and not (pi.trade_in_by>0 and pi.writeoff_by>0))";
	
	$where = " where ".join(" and ",$filter);

	$sql="select cs.network_name, pos.counter_id, pos.id as pos_id, pos.receipt_no, pos.pos_time, pi.id as pos_items_id, pi.barcode,pi.sku_item_id, pi.sku_description, 
		(pi.price / pi.qty) as selling_price , if (trade_in_by=0, 'Open Code', 'Trade In') as type, if (trade_in_by=0, ou.u, tu.u) as open_code_user , u_verify.u as verify_user,pi.verify_timestamp,
		si.id as sku_item_id, si.sku_item_code, si.mcode, si.link_code, si.receipt_description, si.selling_price as org_selling_price, pi.trade_in_by
		from pos_items pi 
		left join pos on pi.pos_id=pos.id and pi.counter_id=pos.counter_id and pi.branch_id=pos.branch_id and pi.date=pos.date
		left join sku_items si on si.id=pi.sku_item_id
		left join user ou on ou.id = pi.open_code_by
		left join user tu on tu.id = pi.trade_in_by 
		left join user u_verify on u_verify.id = pi.verify_code_by
		left join counter_settings cs on cs.id = pos.counter_id and cs.branch_id=pos.branch_id
		$where
		order by pi.barcode,selling_price";
		//print $sql;
	$rid=$con->sql_query($sql);		
	if ($con->sql_numrows($rid) > 0){
		$total_invalid_items=0;
		$total_trade_in_sku=0;
		while($r=$con->sql_fetchassoc($rid)){
			//calculate total transaction
			$counter_network_pos_receipt=$r['counter_id']."-".$r['network_name']."-".$r['pos_id']."-".$r['receipt_no'];
			$tmp_no_of_transaction[$r['barcode']][$r['selling_price']][$counter_network_pos_receipt]=1;
		
			if ($tmp_check_duplicate[$r['barcode']][$r['selling_price']]){
				$tmp_check_duplicate[$r['barcode']][$r['selling_price']][$r['counter_id']][$r['pos_id']][$r['pos_items_id']]=1;
				continue;
			}else{
				$total_invalid_items+=1;
			}
			
			if($r['trade_in_by'])	$total_trade_in_sku++;
/*		
			if ($r['verify_code_by'] > 0 || $r['sku_item_id'] > 0){
				$latest_selling_price = get_sku_item_cost_selling($branch_id, $r['sku_item_id'], $date, array("selling"));
				if ($latest_selling_price)	$r['org_selling_price']=$latest_selling_price['selling'];
					$verified_barcode[$r['pos_id']][$r['pos_items_id']][$r['sku_item_id']]=$r['sku_item_id'];
			}else{
				//check the data had been search or not 
				if (!$invalid_barcodes[$r['barcode']]){
					$result=get_sku_items_details_by_barcode($r['barcode']);
					$latest_selling_price = get_sku_item_cost_selling($branch_id, $result[0]['sku_item_id'], $date, array("selling"));
					if ($latest_selling_price){
						$result[0]['org_selling_price']=$latest_selling_price['selling'];
					}	

					$invalid_barcodes[$r['barcode']]['info']=$result;
				}else{
					$result=$invalid_barcodes[$r['barcode']]['info'];
				}
				
				// only automatch if found one only
				if (count($result)==1){	
					$r['sku_item_id']=$result[0]['sku_item_id'];
					$r['sku_item_code']=$result[0]['sku_item_code'];
					$r['mcode']=$result[0]['mcode'];
					$r['link_code']=$result[0]['link_code'];
					$r['receipt_description']=$result[0]['receipt_description'];
					$r['org_selling_price']=$result[0]['org_selling_price'];
				}

			}
*/
			if(!$data[$r['barcode']][$r['selling_price']])
				$data[$r['barcode']][$r['selling_price']]['total']+=1;

			$data[$r['barcode']][$r['selling_price']]['info']=$r;
			
			$tmp_check_duplicate[$r['barcode']][$r['selling_price']][$r['counter_id']][$r['pos_id']][$r['pos_items_id']]=1;
		}
		$con->sql_freeresult($rid);
		unset($rid,$r);		
		
		foreach ($tmp_check_duplicate as $barc => $sother){
			foreach ($sother as $selling => $other){
				$data[$barc][$selling]['id']=serialize($other);
				
				$transactions_info=$tmp_no_of_transaction[$barc][$selling];
				$data[$barc][$selling]['transactions_total']=count($transactions_info);
				
				foreach ($transactions_info as $cnpr => $dummy){
					$arr=explode("-",$cnpr);					
					
					$transactions_info_arr['counter_id']=$arr[0];
					$transactions_info_arr['network_name']=$arr[1];
					$transactions_info_arr['pos_id']=$arr[2];
					$transactions_info_arr['receipt_no']=$arr[3];
					
					$data[$barc][$selling]['transactions_info'][]=$transactions_info_arr;
				}
			}
		}
	}
	$smarty->assign("total_invalid_items",$total_invalid_items);
	$smarty->assign("total_trade_in_sku",$total_trade_in_sku);
	
	$smarty->assign("invalid_items",$data);
	$smarty->assign("view_only",true);
	$con->sql_freeresult($rid);
	//print_r($invalid_items);
	unset($tmp,$rid,$total,$data);
}

function store_counter_collection_data($branch_id, $date, $data){
	global $con;

	$upd['branch_id']=mi($branch_id);
	$upd['date']=$date;
	foreach ($data as $counter_id =>$t){
		$up['counter_id']=mi($counter_id);
		
		foreach ($t as $field =>&$other){
			change_array_value_to_string($other);
			
			$up[$field]=serialize($other);
		}
		
		$up = array_merge($up, $upd);

		$con->sql_query("replace into pos_counter_finalize ".mysql_insert_by_field($up));
		unset($up);
	}
	
	unset($data);
}

function change_array_value_to_string(&$arr){
	foreach	($arr as &$other){
		if (is_array($other))	change_array_value_to_string($other);
		else	$other=strval($other);
	}
}

function is_cc_finalized($bid, $date){
	global $con;
	
	$con->sql_query("select finalized from pos_finalized where branch_id=".mi($bid)." and date=".ms($date)." and finalized=1");
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	return $tmp ? true : false;
}

function check_and_pregen_pos_finalized($bid, $date){
	global $con;
	
	if(!$bid)	die("No branch id.");
	if(!$date)	die("No date.");
	
	$con->sql_query("select date from pos_finalized where branch_id=".mi($bid)." and date=".ms($date));
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if($tmp)	return;	// pos finalized alrdy exists
	
	$upd = array();
	$upd['branch_id'] = $bid;
	$upd['date'] = $date;
	$upd['finalized'] = 0;
	$upd['finalize_timestamp'] = 0;
	$con->sql_query("replace into pos_finalized ".mysql_insert_by_field($upd));
}

function check_sales_sync_status($prms){
	global $con, $smarty;
	
	if(!$prms['branch_id'] || !$prms['date']) return;
	
	$sync_status = array();
	$q1 = $con->sql_query("select sum(csr.missing_record) as ttl_ms_record, sum(csr.total_record-csr.synced_record) as ttl_unsync_record, 
						   cs.network_name, cs.id as counter_id
						   from pos_transaction_counter_sales_record csr
						   left join counter_settings cs on cs.id = csr.counter_id and cs.branch_id = csr.branch_id
						   where csr.branch_id = ".mi($prms['branch_id'])." and csr.date = ".ms($prms['date'])." and 
						   (csr.missing_record > 0 or (csr.total_record-csr.synced_record) > 0) and csr.tablename not in ('pos_deposit_status', 'pos_deposit_status_history', 'pos_drawer')
						   group by csr.counter_id
						   order by cs.network_name");
						   
	while($r = $con->sql_fetchassoc($q1)){
		$sync_status[$r['counter_id']]['counter_name'] = $r['network_name'];
		$sync_status[$r['counter_id']]['ttl_unsync_record']= $r['ttl_unsync_record'];
		$sync_status[$r['counter_id']]['ttl_ms_record']= $r['ttl_ms_record'];
	}
	$con->sql_freeresult($q1);
	
	if(!$prms['is_finalise']) $smarty->assign("sync_status", $sync_status);
	else return count($sync_status);
}
?>
