<?php
/*
10/16/2017 12:00 PM Andy
- Remove unused function getCategoryInfo.

4:58 PM 6/26/2018 Justin
- Added new function loadForeignCurrencyRate.

3/15/2019 1:30 PM Andy
- Added new posManager function generatePosCashierFinalize().

4/16/2019 2:43 PM Andy
- Added new posManager function getEwalletList().

4/16/2019 5:21 PM Justin
- Added new posManager function generate_arms_sign().

11/5/2019 5:46 PM Andy
- Added new posManager function getCounter().

2/18/2020 3:13 PM Andy
- Fixed to only replace "_" to " " for credit card payment.

3/19/2020 9:58 AM William
- Added new posManager function update_pos_membership_guid().

6/11/2020 3:22 PM Andy
- Added new posManager function generateSKUItemFinalisedCache().
*/
class posManager{
	// common variables
	var $arms_sign_key = "Arms2019";
	
	// private variables
	

	function __construct(){
		
	}
	
	// function to get counter sync errors
	// return array $counters_error
	function getCounterError($bid = 0){
		global $con;

		$counters_error = array();
		$filter = array();
		//$filter[] = "cs.active=1";
		$filter[] = "b.active=1";
		if ($bid > 0) 
		{
			$filter[] = 'cs.branch_id='.mi($bid);		
		}
		$filter = join(' and ', $filter);
		
		// from pos_counter_collection_tracking
		$q1 = $con->sql_query($q = "select cs.branch_id, cs.id as counter_id, b.code as branch_code, cs.network_name, cst.lastping,cc.error
			from counter_settings cs
			left join branch b on cs.branch_id = b.id 
			join pos_counter_collection_tracking cc on cc.branch_id = cs.branch_id and cc.counter_id = cs.id and cc.error<>''
			left join counter_status cst on cst.branch_id=cs.branch_id and cst.id=cs.id
			where $filter
			order by b.sequence, cs.network_name");
		//print $q;
		while($r = $con->sql_fetchassoc($q1)){
			if(!isset($counters_error[$r['branch_id']][$r['counter_id']]['info'])){
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['branch_id'] = $r['branch_id'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['counter_id'] = $r['counter_id'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['branch_code'] = $r['branch_code'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['network_name'] = $r['network_name'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['lastping'] = $r['lastping'];
			}
			
			if($r['error'])	$counters_error[$r['branch_id']][$r['counter_id']]['error_list'][] = $r['error'];
		}
		$con->sql_freeresult($q1);
		
		// from counter_status
		$q2 = $con->sql_query($q = "select cs.branch_id, cs.id as counter_id, b.code as branch_code, cs.network_name, cst.lastping,cst.lasterr,cst.sync_error
			from counter_settings cs
			left join branch b on cs.branch_id = b.id 
			join counter_status cst on cst.branch_id=cs.branch_id and cst.id=cs.id and (cst.lasterr<>'' or cst.sync_error<>'')
			where $filter
			order by b.sequence, cs.network_name");
		//print $q;
		while($r = $con->sql_fetchassoc($q2)){
			if(!isset($counters_error[$r['branch_id']][$r['counter_id']]['info'])){
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['branch_id'] = $r['branch_id'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['counter_id'] = $r['counter_id'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['branch_code'] = $r['branch_code'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['network_name'] = $r['network_name'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['lastping'] = $r['lastping'];
			}
			
			if($r['lasterr'])	$counters_error[$r['branch_id']][$r['counter_id']]['error_list'][] = $r['lasterr'];
			if($r['sync_error'])	$counters_error[$r['branch_id']][$r['counter_id']]['error_list'][] = $r['sync_error'];
		}
		$con->sql_freeresult($q2);
		
		// from pos_finalised_error
		$q3 = $con->sql_query($q = "select cs.branch_id, cs.id as counter_id, b.code as branch_code, cs.network_name, cst.lastping, pfe.error_msg
			from counter_settings cs
			left join branch b on cs.branch_id = b.id 
			join pos_finalised_error pfe on pfe.branch_id = cs.branch_id and pfe.counter_id = cs.id and pfe.error_msg<>''
			left join counter_status cst on cst.branch_id=cs.branch_id and cst.id=cs.id
			where $filter
			order by b.sequence, cs.network_name");
		//print $q;
		while($r = $con->sql_fetchassoc($q3)){
			if(!isset($counters_error[$r['branch_id']][$r['counter_id']]['info'])){
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['branch_id'] = $r['branch_id'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['counter_id'] = $r['counter_id'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['branch_code'] = $r['branch_code'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['network_name'] = $r['network_name'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['lastping'] = $r['lastping'];
			}
			
			if($r['error_msg'])	$counters_error[$r['branch_id']][$r['counter_id']]['error_list'][] = $r['error_msg'];
		}
		$con->sql_freeresult($q3);
		
		// pos_transaction_sync_server_counter_tracking
		$q4 = $con->sql_query($q = "select cs.branch_id, cs.id as counter_id, b.code as branch_code, cs.network_name, cst.lastping, tbl.error_message
			from counter_settings cs
			left join branch b on cs.branch_id = b.id 
			join pos_transaction_sync_server_counter_tracking tbl on tbl.branch_id = cs.branch_id and tbl.counter_id = cs.id and tbl.error_message<>''
			left join counter_status cst on cst.branch_id=cs.branch_id and cst.id=cs.id
			where $filter
			order by b.sequence, cs.network_name");
		//print $q;
		while($r = $con->sql_fetchassoc($q4)){
			if($r['error_message']){
				$r['error_message'] = unserialize($r['error_message']);
			}
			if(!$r['error_message'])	continue;
			
			if(!isset($counters_error[$r['branch_id']][$r['counter_id']]['info'])){
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['branch_id'] = $r['branch_id'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['counter_id'] = $r['counter_id'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['branch_code'] = $r['branch_code'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['network_name'] = $r['network_name'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['lastping'] = $r['lastping'];
			}
			
			foreach($r['error_message'] as $err_msg){
				$counters_error[$r['branch_id']][$r['counter_id']]['error_list'][] = $err_msg;
			}
		}
		$con->sql_freeresult($q4);
		
		// pos_error
		$q5 = $con->sql_query($q = "select cs.branch_id, cs.id as counter_id, b.code as branch_code, cs.network_name, cst.lastping, tbl.error_message
			from counter_settings cs
			left join branch b on cs.branch_id = b.id 
			join pos_error tbl on tbl.branch_id = cs.branch_id and tbl.counter_id = cs.id and tbl.error_message<>''
			left join counter_status cst on cst.branch_id=cs.branch_id and cst.id=cs.id
			where $filter
			order by b.sequence, cs.network_name");
		//print $q;
		while($r = $con->sql_fetchassoc($q5)){
			if(!$r['error_message'])	continue;
			
			if(!isset($counters_error[$r['branch_id']][$r['counter_id']]['info'])){
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['branch_id'] = $r['branch_id'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['counter_id'] = $r['counter_id'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['branch_code'] = $r['branch_code'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['network_name'] = $r['network_name'];
				$counters_error[$r['branch_id']][$r['counter_id']]['info']['lastping'] = $r['lastping'];
			}
			$counters_error[$r['branch_id']][$r['counter_id']]['error_list'][] = "Date: ".$r['date'].": ".$r['error_message'];
		}
		$con->sql_freeresult($q5);
		
		return $counters_error;
	}
	
	// function to get Sync Server Error
	// return array $ss_error
	function getSyncServerError($bid = 0){
		global $con;
		
		$ss_error = array();
		$filter = array();
		$filter[] = "b.active=1";
		if ($bid > 0) 
		{
			$filter[] = 'b.id='.mi($bid);		
		}
		$filter = join(' and ', $filter);
		
		// pos_error
		$q5 = $con->sql_query("select tbl.branch_id, b.code as branch_code, tbl.lastupdate, tbl.error_message
			from branch b
			join pos_transaction_sync_server_tracking tbl on tbl.branch_id = b.id and tbl.error_message<>''
			where $filter
			order by b.sequence");
		//print $q;
		while($r = $con->sql_fetchassoc($q5)){
			if($r['error_message']){
				$r['error_message'] = unserialize($r['error_message']);
			}
			if(!$r['error_message'])	continue;
			
			if(!isset($ss_error[$r['branch_id']]['info'])){
				$ss_error[$r['branch_id']]['info']['branch_id'] = $r['branch_id'];
				$ss_error[$r['branch_id']]['info']['branch_code'] = $r['branch_code'];
			}
			foreach($r['error_message'] as $err_msg){
				$ss_error[$r['branch_id']]['error_list'][] = array('time'=>$r['lastupdate'], 'msg'=>$err_msg);
			}
		}
		$con->sql_freeresult($q5);
		
		return $ss_error;
	}
	
	function loadForeignCurrencyRate($prms){
		global $con;
		
		if(!$prms) return;
		
		$branch_id = $prms['branch_id'];
		$date = $prms['date'];
		$code = $prms['code'];
		
		// verify if pos settings have override the rate
		$q1 = $con->sql_query("select * from pos_settings where branch_id=".mi($branch_id)." and setting_name='foreign_currency_override' and setting_value like '%:\"".$code."\";%'");
		
		if($con->sql_numrows($q1) > 0){
			// select exchange rate from pos settings
			$q2 = $con->sql_query("select * from pos_settings where branch_id=".mi($branch_id)." and setting_name='foreign_currency_rate'");
			$ps_curr_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			if($ps_curr_info){
				$ps_curr_info = unserialize($ps_curr_info['setting_value']);
				if($ps_curr_info[$code]) $currency_rate = $ps_curr_info[$code];
			}
		}
		$con->sql_freeresult($q1);
		
		// need to select from global currency rate table as if didn't override it from pos settings
		if(!$currency_rate){
			// get rate from history first
			$q1 = $con->sql_query("select * from foreign_currency_rate_history where code = ".ms($code)." and ".ms($date)." between date_from and date_to");
			
			if($con->sql_numrows($q1) == 0){
				$q1 = $con->sql_query("select * from foreign_currency_rate where code = ".ms($code));
			}
			
			$ps_curr_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			// still no rate, then must have problem with the 
			if($ps_curr_info['base_rate']) $currency_rate = $ps_curr_info['base_rate'];
		}

		return $currency_rate;
	}
	
	// function to generate data into pos_cashier_finalize
	// return null
	function generatePosCashierFinalize($date, $counter_id, $bid){
		global $con, $config, $pos_config, $appCore;

		if(!$date || !$counter_id || !$bid) return false;
		
		$filter = "p.branch_id=".mi($bid)." and p.date=".ms($date)." and p.counter_id=".mi($counter_id);
		
		// check if it is finalized
		$q1 = $con->sql_query("select * from pos_finalized where date = ".ms($date)." and branch_id = ".mi($bid)." and finalized=1");
		if($con->sql_numrows($q1) == 0) return;
		$con->sql_freeresult($q1);
		
		// check data whether existed or not
		$q1 = $con->sql_query("select * from pos_cashier_finalize p where $filter");
		
		if($con->sql_numrows($q1) > 0) return;
		$con->sql_freeresult($q1);
		

			// Cash is default
		$payment_type_list[] = 'Cash';
		
		// select other payment type from pos settings
		$q1 = $con->sql_query("select * from pos_settings where setting_name = 'payment_type' and branch_id = ".mi($bid));
		$ps_info = $con->sql_fetchrow($q1);
		$ps_payment_type = unserialize($ps_info['setting_value']);
		$con->sql_freeresult($q1);

		if($ps_payment_type){
			foreach($ps_payment_type as $ptype=>$val){
				$ori_ptype = $ptype;
				//$ptype = ucwords(str_replace("_", " ",$ptype));
				if(strpos(strtolower($ptype), "credit_card")===0){
					$ptype = str_replace("_", " ",$ptype);	// only replace "_" to " " if it is credit card
				}
				$ptype = ucwords($ptype);
				if($ptype == "Credit Card") $ptype = "Credit Cards";
				
				if(!$val) continue;	// in-active
				
				$payment_type_list[] = $ptype;
			}
		}
		
		// get currency rate (in case the cash denomination doesn't have it)
		if($config['foreign_currency']){
			foreach($config['foreign_currency'] as $curr_code=>$curr_settings){
				if(isset($foreign_currency_list[$curr_code])) continue;
				
				// load latest exchange rate
				$prms = array();
				$prms['branch_id'] = $bid;
				$prms['date'] = $date;
				$prms['code'] = $curr_code;
				$global_currency_rate = $appCore->posManager->loadForeignCurrencyRate($prms);
				
				$foreign_currency_list[$curr_code] = $global_currency_rate;
			}
		}
		
		// get from pos first
		$data = array();
		$q1 = $con->sql_query("select p.* from pos p where $filter and p.cancel_status=0 order by p.end_time");

		while($pos = $con->sql_fetchassoc($q1)){
			// assign pos_id
			$pos_id = mi($pos['id']);
			$cashier_id = mi($pos['cashier_id']);

			// get counter domination id
			//$dom_id = $get_pos_cash_domination_id($counter_id, $pos['end_time']);

			// get from pos payment
			$q_pp = $con->sql_query("select type, remark, changed, adjust, amount from pos_payment p where $filter and pos_id=".mi($pos_id)." and p.adjust=0");
			
			// check got mix and match
			/*if(!$data[$counter_id][$cashier_id]['others']['got_mm_discount']){
				$q_pmm = $con->sql_query("select id from pos_mix_match_usage p where $filter and p.pos_id=$pos_id limit 1");
				if($con->sql_numrows($q_pmm)){
					$data[$counter_id][$dom_id]['others']['got_mm_discount']=1;
					$got_mm_discount = true;
				}
				$con->sql_freeresult($q_pmm);
			}*/
			
			$amount_change_dealed = false;
			
			while($pp = $con->sql_fetchassoc($q_pp)){
				$is_changed = mi($pp['changed']);
				//$is_adjust = mi($pp['adjust']);
				$row_type = 'cashier_sales';
				$add_to_nett_sales = true;
				if($is_changed){
					$row_type = 'adjustment';
					$add_to_nett_sales = false;
				}
				//$adj_amt = 0;

				// is one of the credit cards
				if (in_array($pp['type'], $pos_config['credit_card'])) $payment_type = 'Credit Cards';
				else $payment_type = $pp['type'];

				$payment_type = ucwords(strtolower($payment_type));
				
				$is_foreign_currency = false;
				
				//if($is_adjust || $is_changed)  $adj_amt = $pp['amount'];
				
				$got_mm_discount = false;
				// check payment type
				switch ($payment_type){
					case 'Mix & Match Total Disc':
						//$payment_type = 'Discount';	// store together with receipt discount
						$got_mm_discount = true;
					case 'Discount':    // it is discount
						$rm_amt = $pp['amount']*-1;  // discount show as negative
						$add_to_nett_sales = false;
						break;
					case 'Cash':    // it is cash payment
						$rm_amt = $pp['amount'] - $pos['amount_change'];    // amount decrease changed
						$amount_change_dealed = true;
						break;
					/*case 'Deposit':	// pay by deposit
							if($pos['amount_change']){	// got amt changed
								// check whether this deposit got pay by cash
								$con->sql_query("select count(*) from pos_payment p where $filter and p.pos_id=$pos_id and type='Cash' and p.adjust=0");
								$deposit_got_cash = mi($con->sql_fetchfield(0));
								$con->sql_freeresult();
							
								if(!$deposit_got_cash){	// this deposit dun hv cash but got change
									$payment_type = 'Cash';	// change payment type to cash
									$rm_amt = $pos['amount_change']*-1;	// make refund count into cash sales
									$amount_change_dealed = true;
									//$this->data[$counter_id][$dom_id]['deposit_refund_amt']+=$pos['amount_change'];
								}
								//print "payment_type = $payment_type<br>";
							}
							break;*/
					default:
						// check is foreign currency
						$currency_arr = array();
						$is_currency = strpos($pp['remark']," @");
						if($is_currency == true){
							$remark = explode(" @", $pp['remark']);
							$currency_type = $remark[0];
							$currency_rate = sprintf("%01.".$config['foreign_currency_decimal_points']."f", $remark[1]);
							$currency = $pp['amount'];
							$rm_amt = $currency/$currency_rate;
							$currency_arr = array('is_currency'=>true, 'currency_amt'=>$currency, 'currency_rate'=>$currency_rate, 'rm_amt'=>$rm_amt, 'currency_type'=>$currency_type);
						}else $currency_arr = array('rm_amt'=>$pp['amount']);
						if($currency_arr['is_currency']){   // it is foreign currency
							$currency_amt = $currency_arr['currency_amt'];
							$currency_rate = $currency_arr['currency_rate'];
							$is_foreign_currency = true;
						}
						$rm_amt = round($currency_arr['rm_amt'], 2);
						break;
				}
				
				/*if(strpos($payment_type, '_Float')){   // currency float
					$payment_type = str_replace('_Float', '', $payment_type);
				}
				
				if($payment_type && $payment_type != $mm_discount_col_value && $payment_type != "Discount" && !in_array($payment_type, $normal_payment_type) && in_array($payment_type, $pos_config['payment_type'])) $normal_payment_type[] = $payment_type;*/
										
				// row type is either 'cashier_sales' or 'adj'
				if($is_foreign_currency){   // is foreign currency	
					//$use_amt = $is_changed ? $adj_amt : $rm_amt;
					//$use_amt = $rm_amt;
					$payment_type = strtoupper($payment_type);
					$data[$counter_id][$cashier_id][$row_type]['foreign_currency'][$payment_type]['foreign_amt'] += $currency_amt;
				}else{
					// if getting adj out, cashier sales will still use $rm_amt, only use $adj_amt if adj in
					//$use_amt = $is_changed ? $adj_amt : $rm_amt;
					$use_amt = $rm_amt;
					$data[$counter_id][$cashier_id][$row_type][$payment_type]['amt'] += $use_amt;
					if(in_array($payment_type, $payment_type_list) || preg_match('/^ewallet_/i', $payment_type)){
						$data[$counter_id][$cashier_id][$row_type]['nett_sales']['amt'] += $use_amt;
						
						$payment_type_list[] = $payment_type;
					}
				}

				// adjustment
				/*if($is_adjust){
					if($is_foreign_currency){   // is foreign currency
						$data[$counter_id][$cashier_id]['adjustment']['foreign_currency'][$payment_type]['foreign_amt'] += $currency_amt*-1;
						$data[$counter_id][$cashier_id]['adjustment']['foreign_currency'][$payment_type]['rm_amt'] += $adj_amt*-1;
						$data[$counter_id][$cashier_id]['adjustment'][$payment_type]['amt'] += $adj_amt*-1;
					}
				}*/
			}
			$con->sql_freeresult($q_pp);
			
			// sepcial cash refund / change
			if($pos['amount_change'] > 0 && !$amount_change_dealed){
				//print "extra change = ".$pos['amount_change']."<br>";
				//$data[$counter_id][$cashier_id]['cash_change']['amt'] += $pos['amount_change'];
				//$data[$counter_id][$cashier_id]['cash_change']['got_data'] = 1;
				
				$data[$counter_id][$cashier_id]['cashier_sales']['Cash']['amt'] -= $pos['amount_change'];
				//$data[$counter_id][$cashier_id]['cashier_sales']['Cash']['got_data'] = 1;
				$data[$counter_id][$cashier_id]['cashier_sales']['nett_sales']['amt'] -= $pos['amount_change'];
			}
			
			// check if having deposit amt
			$q_pd = $con->sql_query("select p.amount, pp.amount as payment_amount, pdsh.type, p.cancel_status, p.prune_status, 
									pp.type as payment_type
									from pos_deposit_status_history pdsh
									left join pos_payment pp on pp.pos_id = pdsh.pos_id and pp.branch_id = pdsh.branch_id and pp.counter_id = pdsh.counter_id and pp.date = pdsh.pos_date
									left join pos p on p.id = pp.pos_id and p.branch_id = pp.branch_id and p.date = pp.date and p.counter_id = pp.counter_id
									where
									pdsh.pos_id = ".mi($pos_id)." and 
									pdsh.pos_date = ".ms($date)." and 
									pdsh.branch_id = ".mi($bid)." and 
									pdsh.counter_id = ".mi($counter_id));

			if($con->sql_numrows($q_pd) > 0){
				while($pos_deposit = $con->sql_fetchrow($q_pd)){
					if($pos_deposit['type'] == "RECEIVED" && !$pos_deposit['cancel_status'] && !$pos_deposit['prune_status'] && $pos_deposit['payment_type'] != "Deposit")
						$data[$counter_id][$cashier_id]['deposit']['rcv']+=$pos_deposit['amount'];
					elseif($pos_deposit['type'] == "USED" && !$pos_deposit['cancel_status'] && !$pos_deposit['prune_status'] && $pos_deposit['payment_type'] == "Deposit")
						$data[$counter_id][$cashier_id]['deposit']['used']+=$pos_deposit['payment_amount'];
					elseif($pos_deposit['type'] == "CANCEL_RCV" && $pos_deposit['payment_type'] == "Cash")
						$data[$counter_id][$cashier_id]['deposit']['cancel_rcv']+=$pos_deposit['amount'];
					elseif($pos_deposit['type'] == "CANCEL_USED" && $pos_deposit['payment_type'] == "Cash")
						$data[$counter_id][$cashier_id]['deposit']['cancel_used']+=$pos_deposit['amount'];
				}
				$con->sql_freeresult($q_pd);
			}

			// last cashier
			/*$data[$counter_id][$cashier_id]['last_cashier_id'] = $pos['cashier_id'];
			if(!$data[$counter_id][$cashier_id]['arr_cashier_id_list'])	$data[$counter_id][$cashier_id]['arr_cashier_id_list'] = array();
			if(!in_array($pos['cashier_id'], $data[$counter_id][$cashier_id]['arr_cashier_id_list'])){
				$data[$counter_id][$cashier_id]['arr_cashier_id_list'][] = $pos['cashier_id'];
			}*/
			
			// over
			$pos_amt = $pos['amount'];
			$real_receipt_amt = $pos['amount_tender'] - $pos['amount_change'];
			$over_amt = mf($real_receipt_amt-$pos_amt);
			if($over_amt){
				$data[$counter_id][$cashier_id]['cashier_sales']['Over']['amt'] += round($over_amt,2);
			}
			
			// trade in
			$q_ti = $con->sql_query("select pi.*
									from pos_items pi
									where pi.branch_id=".mi($bid)." and pi.date=".ms($date)." and pi.counter_id=".mi($counter_id)."
									and pi.pos_id=".mi($pos_id)." and pi.trade_in_by>0");
			while($pi_ti = $con->sql_fetchassoc($q_ti)){
				$data[$counter_id][$cashier_id]['trade_in']['qty'] += $pi_ti['qty'];
				$data[$counter_id][$cashier_id]['trade_in']['amt'] += $pi_ti['price'];
				
				if($pi_ti['writeoff_by']){
					$data[$counter_id][$cashier_id]['trade_in']['writeoff_qty']+=$pi_ti['qty'];
					$data[$counter_id][$cashier_id]['trade_in']['writeoff_amt']+=$pi_ti['price'];
				}
			}
			$con->sql_freeresult($q_ti);
		}
		$con->sql_freeresult($q1);
		
		// cash advance
		$q1 = $con->sql_query("select * from pos_cash_history p where $filter");
		while($pch = $con->sql_fetchassoc($q1)){
			//$dom_id = $get_pos_cash_domination_id($counter_id, $pch['timestamp']);

			$currency_arr = array();
			$is_currency = strpos($pch['remark']," @");
			if($is_currency == true){
				$remark = explode(" @", $pch['remark']);
				$currency_type = $remark[0];
				$currency_rate = sprintf("%01.".$config['foreign_currency_decimal_points']."f", $remark[1]);
				$currency = $pch['amount'];
				$rm_amt = round($currency/$currency_rate, 2);
				$currency_arr = array('is_currency'=>true, 'currency_amt'=>$currency, 'currency_rate'=>$currency_rate, 'rm_amt'=>$rm_amt, 'currency_type'=>$currency_type);
			}else $currency_arr = array('rm_amt'=>$pch['amount']);
			
			$rm_amt = $currency_arr['rm_amt'];

			switch($pch['type']){
				case 'ADVANCE':
					$type = 'cash_advance';
					break;
				case 'TOP_UP':
					$type = 'top_up';
					$got_top_up = true;
					break;
				default:
					continue 2;		
			}
			
			$pul_filter = array();
			$pul_filter[] = "type = 'login'";
			$pul_filter[] = "date = ".ms($date);
			$pul_filter[] = "counter_id = ".mi($counter_id);
			$pul_filter[] = "branch_id = ".mi($bid);
			$pul_filter[] = "timestamp < ".ms($pch['timestamp']);
			$pul_filter = join(" and ", $pul_filter);
			$con->sql_query("select * from pos_user_log where ".$pul_filter." order by timestamp desc limit 1");
			$last_cashier = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);

			if($last_cashier['cashier_id']) $cashier_id = $last_cashier['cashier_id'];
			else $cashier_id = $pch['user_id'];
			
			if($currency_arr['is_currency']){
				$currency_type = $currency_arr['currency_type'];
				$currency_amt = $currency_arr['currency_amt'];

				$data[$counter_id][$cashier_id][$type]['foreign_currency'][$currency_type]['foreign_amt'] += $currency_amt;
			}else{
				$data[$counter_id][$cashier_id][$type]['Cash']['amt'] += $rm_amt;
			}
			$data[$counter_id][$cashier_id][$type]['nett_sales']['amt'] += $rm_amt;
		}
		$con->sql_freeresult($q1);
		
		// get pos cash domination list for "counter collection"
		$sql = "select p.*, p.user_id as cashier_id
				from pos_cash_domination p
				where $filter
				order by p.timestamp";
		$q1 = $con->sql_query($sql);

		$start_time = $counter_whole_day_start;
		while($r = $con->sql_fetchassoc($q1)){
			// add 8 hours due to frontend time bugs
			$r['timestamp'] = date("Y-m-d H:i:s", strtotime($r['timestamp']));
			$r['start_time'] = $start_time;
			$r['end_time'] = $r['timestamp'];
			$r['data'] = unserialize($r['data']);
			$r['odata'] = unserialize($r['odata']);
			$r['curr_rate'] = unserialize($r['curr_rate']);
			$r['ocurr_rate'] = unserialize($r['ocurr_rate']);

			$pul_filter = array();
			$pul_filter[] = "type = 'login'";
			$pul_filter[] = "date = ".ms($r['date']);
			$pul_filter[] = "counter_id = ".mi($counter_id);
			$pul_filter[] = "branch_id = ".mi($r['branch_id']);
			$pul_filter[] = "timestamp < ".ms($r['timestamp']);
			$pul_filter = join(" and ", $pul_filter);
			$con->sql_query("select * from pos_user_log where ".$pul_filter." order by timestamp desc limit 1");
			$last_cashier = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);

			if($last_cashier['cashier_id']) $r['cashier_id'] = $last_cashier['cashier_id'];
			
			$cash_domination[$r['id']] = $r;
			//$cash_domination[$counter_id][$r['cashier_id']]['cash_domination']['nett_sales']['amt'] += $rm_amt;
		}
		$con->sql_freeresult($q1);
		
		// process cash domination
		if($cash_domination){
			$pos_cash_domination_notes = $config['cash_domination_notes'];
			foreach($cash_domination as $dom_id=>$r){
				// if no sales, put cash denom user as cashier
				/*if(!$data[$counter_id][$dom_id]['last_cashier_id']){
					$data[$counter_id][$dom_id]['last_cashier_id'] = $r['user_id'];
					//if (!in_array($r['user_id'],$cashier_list))	$cashier_list[] = $r['user_id'];
				}
				if(!$data[$counter_id][$dom_id]['arr_cashier_id_list'])	$data[$counter_id][$dom_id]['arr_cashier_id_list'] = array();
				if(!in_array($r['user_id'], $data[$counter_id][$dom_id]['arr_cashier_id_list'])){
					$data[$counter_id][$dom_id]['arr_cashier_id_list'][] = $r['user_id'];
				}*/
				
				if(!$r['data']&&!$r['odata']) continue;

				// latest data
				if($r['data']){
					if($r['curr_rate']) $curr_rate = $r['curr_rate'];  // use cash domination currency rate
					else $curr_rate = $foreign_currency_list;  // use default currency rate

					foreach($r['data'] as $type=>$d2){
						$type_key = $type;
						if ($type_key == 'Cheque') $type_key = 'Check';

						if (in_array($type, $pos_config['credit_card']))
							$type_key = 'Credit Cards';
						elseif (in_array($type, array_keys($pos_cash_domination_notes)))
						{
							$d2 = $d2 * $config['cash_domination_notes'][$type]['value'];
							$type_key = 'Cash';
						}
						elseif ($type == 'Float')
						{
							$type_key = 'Cash';
							$d2 *= -1;
						}elseif(strpos($type, '_Float')){   // currency float
							$type_key = str_replace('_Float', '', $type);
							$d2 *= -1;
						}
						
						//print_r($pos_config['payment_type']);
						/*if($type_key && $type_key != $mm_discount_col_value && $type_key != "Discount" && !in_array($type_key, $normal_payment_type) && in_array($type_key, $pos_config['payment_type'])){
							$normal_payment_type[] = $type_key;
						}*/

						if(isset($foreign_currency_list[$type_key])){  // is currency collection
							//$rm_amt = $curr_rate[$type_key] ? mf($d2/$curr_rate[$type_key]) : 0;
							$data[$counter_id][$r['cashier_id']]['cash_domination']['foreign_currency'][$type_key]['foreign_amt'] += $d2;
							if($curr_rate[$type_key]) $data[$counter_id][$r['cashier_id']]['cash_domination']['foreign_currency'][$type_key]['currency_rate'] = $curr_rate[$type_key];

							if(strpos($type, '_Float')){    // is currency float
								$data[$counter_id][$r['cashier_id']]['cash_domination']['foreign_currency'][$type_key]['Float']['foreign_amt'] += abs($d2);
							}
						}else{
							$rm_amt = $d2;
							$data[$counter_id][$r['cashier_id']]['cash_domination'][$type_key]['amt'] += $rm_amt;

							if($type=='Float'){ // is cash float
								$data[$counter_id][$r['cashier_id']]['cash_domination']['Float']['amt'] += abs($rm_amt);
							}
							$data[$counter_id][$r['cashier_id']]['cash_domination']['nett_sales']['amt'] += $rm_amt;
						}
					}
				}

				// original data
				if($r['odata']){
					if($r['ocurr_rate']){
						$curr_rate = $r['ocurr_rate'];  // use cash domination currency rate
					}
					else{
						$curr_rate = $foreign_currency_list;  // use default currency rate
					}

					foreach($r['odata'] as $type=>$d2){
						$type_key = $type;
						if ($type_key == 'Cheque') $type_key = 'Check';

						if (in_array($type, $pos_config['credit_card'])) $type_key = 'Credit Cards';
						elseif(in_array($type, array_keys($pos_cash_domination_notes))){
							$d2 = $d2 * $config['cash_domination_notes'][$type]['value'];
							$type_key = 'Cash';
						}elseif ($type == 'Float') $d2 *= -1;

						if(isset($foreign_currency_list[$type_key])){  // is currency collection
							//$rm_amt = $curr_rate[$type_key] ? mf($d2/$curr_rate[$type_key]) : 0;
							$data[$counter_id][$r['cashier_id']]['cash_domination']['foreign_currency'][$type_key]['o_foreign_amt'] += $d2;
						}else{
							$rm_amt = $d2;
							$data[$counter_id][$r['cashier_id']]['cash_domination'][$type_key]['o_amt'] += $rm_amt;
							$data[$counter_id][$r['cashier_id']]['cash_domination']['nett_sales']['o_amt'] += $rm_amt;
						}
					}
				}
			}
		}
		
		// store data into pos_cashier_finalize
		foreach($data as $counter_id => $cashier_list){
			foreach($cashier_list as $cashier_id => $t){
				$upd = array();
				$upd['branch_id'] = $bid;
				$upd['date'] = $date;
				$upd['counter_id'] = $counter_id;
				$upd['cashier_id'] = $cashier_id;
				
				foreach($t as $field => &$other){
					change_array_value_to_string($other);
					$upd[$field]=serialize($other);
				}
				
				$variance['nett_sales']['amt'] = round($t['cash_domination']['nett_sales']['amt']-($t['cashier_sales']['nett_sales']['amt']+$t['cash_advance']['nett_sales']['amt']+$t['adjustment']['nett_sales']['amt']+$t['top_up']['nett_sales']['amt']),2);
				
				if($foreign_currency_list){
					foreach($foreign_currency_list as $fc_code=>$fc_rate){
						// foreign amt variance
						$variance['foreign_currency'][$fc_code]['foreign_amt'] = round($t['cash_domination']['foreign_currency'][$fc_code]['foreign_amt'] - ($t['cashier_sales']['foreign_currency'][$fc_code]['foreign_amt']+$t['cash_advance']['foreign_currency'][$fc_code]['foreign_amt']+$t['adjustment']['foreign_currency'][$fc_code]['foreign_amt']+$t['top_up']['foreign_currency'][$fc_code]['foreign_amt']), 2);
						
						// local amt variance  
						if($variance['foreign_currency'][$fc_code]['foreign_amt']){
							if(isset($t['cash_domination']['foreign_currency'][$fc_code]['currency_rate'])){
								$tmp_curr_rate = $t['cash_domination']['foreign_currency'][$fc_code]['currency_rate'];
							}else{
								$tmp_curr_rate = $foreign_currency_list[$fc_code];
							}
							
							$variance['foreign_currency'][$fc_code]['rm_amt'] = round($variance['foreign_currency'][$fc_code]['foreign_amt'] / $tmp_curr_rate, 2);
						}
					}
				}
				
				change_array_value_to_string($variance);
				$upd['variance'] = serialize($variance);
				
				$con->sql_query("replace into pos_cashier_finalize ".mysql_insert_by_field($upd));
			}
		}
	}
	
	function getEwalletList($bcode=''){
		global $con, $config;
		
		$ewallet_list = array();
		
		// eWallet
		if($config['ewallet_list']){
			// Loop default eWallet List
			foreach($config['ewallet_list'] as $ewallet_type => $r){
				$r['enabled'] = false;	// Default cannot use
				$r['hide'] = false;	// Default no need hide
				
				// Got config for this eWallet Type
				if(isset($config['ewallet_settings'][$ewallet_type])){
					if($config['ewallet_settings'][$ewallet_type]['active']){
						if($config['ewallet_settings'][$ewallet_type]['branch_settings'][$bcode]){
							$r['enabled'] = true;
						}				
					}else{
						$r['hide'] = true;
					}
					
					if($config['ewallet_settings'][$ewallet_type]['is_debug'])	$r['is_debug'] = 1;
					
					// found it is ewallet gateway which contains integration list
					if($config['ewallet_settings'][$ewallet_type]['has_integrator_list']){
						$q2 = $con->sql_query("select * from ewallet_integrator_list where ewallet_type = ".ms($ewallet_type));
						
						$ewallet_desc = $r['desc'];
						while($r1 = $con->sql_fetchassoc($q2)){
							$integrator_ewallet_type = $ewallet_type."_".$r1['integrator_type'];
							$r['desc'] = $ewallet_desc." - ".ucwords($r1['integrator_type']);
							$ewallet_list[$integrator_ewallet_type] = $r;
						}
						$con->sql_freeresult($q2);
						
						continue;
					}
				}
				$ewallet_list[$ewallet_type] = $r;
			}
		}
		
		return $ewallet_list;
	}
	
	function generate_arms_sign($prms=array()){
		if(!$prms) return;
		
		$sign_date = str_replace("-", "", $prms['date']);
		// config + branch_id + counter_id + date (YYYYMMDD) + receipt_ref_no
		$arms_sign = md5($this->arms_sign_key.$prms['branch_id'].$prms['counter_id'].$sign_date.$prms['receipt_ref_no']);
		
		return $arms_sign;
	}
	
	public function getCounter($bid, $counter_id, $params = array()){
		global $con;
		
		$bid = mi($bid);
		$counter_id = mi($counter_id);
		
		if(!$bid || !$counter_id)	return false;
		
		$filter = array();
		$filter[] = "branch_id=$bid and id=$counter_id";
		if(isset($params['active']))	$filter[] = "active=".mi($params['active']);
		$str_filter = "where ".join(' and ', $filter);
		
		$con->sql_query("select * from counter_settings $str_filter");
		$counter = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $counter;
	}
	
	function update_pos_membership_guid($bid, $date = '', $date_from = '', $date_to = ''){
		global $con;
		
		$bid = mi($bid);
		$filter = $date_list = array();
		
		$filter[] = "pos.branch_id = $bid";
		
		if($date)  $filter[] = "pos.date=".ms($date);
		elseif($date_from && $date_to)  $filter[] = "pos.date between ".ms($date_from)." and ".ms($date_to);
		
		if(!$date && !$date_from && !$date_to) return false;
		if($date_from || $date_to){
			if(!$date_from || !$date_to)  return false;
		}
		
		$str_filter = "where ".join(' and ', $filter);
		$q1 = $con->sql_query("select distinct pos.date from pos $str_filter group by pos.date");
		while($r=$con->sql_fetchassoc($q1)){	//get pos date
			$date_list[] = $r['date'];
		}
		$con->sql_freeresult($q1);
		
		$updated_count = 0;
		foreach($date_list as $update_date){
			$upd_date = $update_date;
			if($upd_date){
				$q2 = $con->sql_query("select pos.date, pos.member_no, if(membership.membership_guid is not null and membership.membership_guid <> '', membership.membership_guid, mh.membership_guid) as membership_guid from pos  
									left join membership_history mh on pos.member_no = mh.card_no
									left join membership on pos.member_no = membership.card_no
									where pos.member_no <> '' and pos.membership_guid = '' and pos.date=".ms($upd_date)." and pos.branch_id = $bid
									group by pos.date, pos.member_no");
				
				while($r2=$con->sql_fetchassoc($q2)){
					if($r2['membership_guid']){
						$upd = array();
						$upd['membership_guid'] = $r2['membership_guid'];
						$member_no = $r2['member_no'];
						
						//update pos membership_guid
						$con->sql_query("update pos set ".mysql_update_by_field($upd)." where date=".ms($upd_date)." and member_no=".ms($member_no)." and pos.branch_id = $bid");
						$updated_count++;
					}
				}
				$con->sql_freeresult($q2);
			}
		}
		return $updated_count;
	}
	
	/*function getPosPaymentMemberCredit($prms=array()){
		global $con;
		
		if(!$prms) return false;
		
		// Get Data from database
		$con->sql_query("select * from pos_payment_member_credit where branch_id=".mi($prms['branch_id'])." and counter_id=".mi($prms['counter_id'])." and date=".ms($prms['date'])." and receipt_ref_no=".ms($prms['receipt_ref_no'])." order by added desc limit 1");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
				
		// Never have the record before
		if(!$data)	return false;
		
		$ret['data'] = $data;
		unset($data);
		
		return $ret;
	}*/
	
	function generateSKUItemFinalisedCache($bid, $date = '', $date_from = '', $date_to = '', $params = array()){
		global $con;
		
		$bid = mi($bid);
		if($bid<=0)   die("Invalid Branch ID");
		if(!$date&&!$date_from&&!$date_to)  die("Invalid Date");
		
		$tbl = "sku_items_sales_cache_b".$bid;
		$filter = array();
		
		if($date){
			$filter[] = "tbl.date=".ms($date);
		}
		else{
			if($date_from){
				$filter[] = "tbl.date>=".ms($date_from);
			}
			if($date_to){
				$filter[] = "tbl.date<=".ms($date_to);
			}
		}
		
		$str_filter = "where ".join(' and ', $filter);
		
		$sql = "replace into sku_items_finalised_cache
			(branch_id, date, sku_item_id, unit_cost)
			(select $bid, tbl.date, tbl.sku_item_id, tbl.cost/tbl.qty 
				from $tbl tbl
				$str_filter)";
		$con->sql_query($sql);
		return true;
		
	}
}
?>
