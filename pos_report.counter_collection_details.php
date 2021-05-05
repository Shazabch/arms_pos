<?php
/*
8/23/2011 3:21:35 PM Andy
- Add print button and print additional extra content.
- Add "Total" for Abnormal Transaction - Backend.

9/20/2011 1:27:24 PM Andy
- Fix denomination (original) to include float.
- Add checking only allow to show report if counter collection already finalized.

12/14/2011 10:43:20 AM Andy
- Modify "Cash Advance" at "Counter Collection Summary - Final" to turn positive/negative opposite.
- Modify "Total Sales by Payment Type" to 3 rows. (Counter Sales, Variance and Total Sales)

4/27/2012 5:02:51 PM Andy
- Add "Top Up" information.

5:53 PM 5/22/2012 Justin
- Enhanced to set original amount to follow Amended amount while it is zero.

8/15/2012 5:28 PM Andy
- Fix wrong sales and adjust amount if user adjust payment amount related to cash.

10/11/2012 10:28:00 AM Fithri
- add mix and match and discount
- modify printing template

10/12/2012 5:35 PM Andy
- Fix mix and match discount amount.

11/23/2012 4:16 PM Andy
- Change mix & match and discount from negative to positive.
- Add mix & match discount into denomination.

6/25/2013 5:45 PM Andy
- Fix Actual Sales & Sales to include Over.

8/26/2013 10:03 AM Andy
- Enhance the report calculation to include deposit refund.

11/1/2013 5:45 PM Justin
- Bug fixed on if no amended cash dedomination amount then pick from original cash dedomination amount.

11/13/2013 11:45 AM Andy
- Bug fixed on sometime system will replace original denominatin amt with amended denomination amt.

12/20/2013 5:33 PM Andy
- Fix mix and match discount should not include into receipt discount column.

1/8/2014 2:05 PM Fithri
- check for type (POS) when getting data for "drawer open" count

3/21/2014 4:14 PM Justin
- Enhanced to show custom payment type label if found it is set.
- Modified the wording from "Finalize" to "Finalise".

10/23/2014 11:46 AM Justin
- Bug fixed on custom payment type set from POS settings is not show out.

11/27/2014 5:13 PM Andy
- Enhance to show Service Charges and GST.
- Change the calculation of Total Sales.
- Fix discount calculation.

10/13/2016 4:16 PM Andy
- Fixed credit card adjusted amount cannot be show.

11/22/2016 2:45 PM Andy
- Enhanced to cash calculation to include special cash refund / change.

11/25/2016 9:18 AM Andy
- Fixed denomination din't show currency data when pos settings removed.

12/22/2016 2:49 PM Andy
- Fixed deposit received by check cannot display.

4/13/2017 13:33 AM Qiu Ying
- Bug fixed on Counter Collection Payment Type Missing

4/27/2017 3:38 PM Justin
- Enhanced to use cash_domination_notes from config instead of pos_config.

6/25/2018 11:49 AM Justin
- Enhanced to load foreign currency from config instead of pos settings.

3/15/2019 3:03 PM Andy
- Enhanced counter collection variances calculation to include ewallet sales and ewallet collection.

2/24/2020 5:35 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

3/24/2020 3:16 PM Justin
- Bug fixed on the eWallet payment for Paydibs couldn't display out.

10/20/2020 3:45 PM William
- Bug fixed total tax amount wrong.
*/
include("include/common.php");
$maintenance->check(209);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
include_once('counter_collection.include.php');

class COUNTER_COLLECTION_DETAILS_REPORT extends Module{
	var $date = '';
	var $branch_id = 0;
	var $normal_payment_type = array();
	var $normal_payment_type_label = array();
	var $data = array();
	var $abnormal_front_end_type = array(
		'mprice' => 'Multiple Selling Price', 
		'item_disc' => 'Item Discount',
		'open_price' => 'Open Price',
		'goods_return' => 'Goods Return'
		);
	var $got_top_up = false;
	var $got_service_charge = false;
	var $got_gst = false;
	
	function __construct($title, $template=''){
		global $con, $sessioninfo, $pos_config, $smarty, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		if(!isset($_REQUEST['date']))	$_REQUEST['date'] = date("Y-m-d");	// default today
		else	$this->date = $_REQUEST['date'];
	
		$this->branch_id = mi($sessioninfo['branch_id']);
		
		$smarty->assign("abnormal_front_end_type",$this->abnormal_front_end_type);
		
		parent::__construct($title, $template);
	}
	
	 function _default(){
	 	if($_REQUEST['load_report']){
		 	$this->load_report();
		 }
	 	$this->display();
	 }
	 
	 private function load_report(){
	 	global $con, $smarty, $pos_config, $config, $appCore, $con_multi;
	 	
	 	if(!strtotime($this->date))	$err[] = 'Invalid Date';
	 	
	 	// must finalized
	 	$con_multi->sql_query("select * from pos_finalized where branch_id=$this->branch_id and date=".ms($this->date)." and finalized=1");
	 	if(!$con_multi->sql_numrows())	$err[] = "Counter Collection must finalised first.";
	 	$con_multi->sql_freeresult();
	 	
	 	if($err){
			$smarty->assign('err', $err);
			return; 	
		}
		
		// sort currency type
		if(is_array($this->data['foreign_currency_list']) && count($this->data['foreign_currency_list'])>0){
			ksort($this->data['foreign_currency_list']);
		}
		
		//print '<pre>';print_r($this->data);print '</pre>';
		
		// get currency type and assign it into pos_config
		/*$con->sql_query("select * from pos_settings where branch_id=$this->branch_id and setting_name='currency'");
		$r = $con->sql_fetchrow();
		$con->sql_freeresult();
		$currencies = $r['setting_value'];
		if ($currencies) $currencies = unserialize($currencies);

		if (is_array($currencies) && $currencies)
		{
            $pos_config['currency'] = array_keys($currencies);
            $pos_config['curr_rate'] = $currencies;
            foreach($pos_config['curr_rate'] as $currency_type=>$currency_rate){
                $currency_rate = sprintf("%01.3f", $currency_rate);
                if(!$currency_rate) continue;

				//$this->data['foreign_currency_list'][$payment_type]['type'] = $currency_type;
				//$this->data['foreign_currency_list'][$currency_type]['currency_rate_list'][] = $currency_rate;
			}
		}*/
		
		// load foreign currency
		$this->foreign_currency_list = array();
		
		/*foreach($pos_config['payment_type'] as $ptype){
			// load custom payment type label if found got set
			if($pos_config['payment_type_label'][$ptype]){
				$label = $pos_config['payment_type_label'][$ptype];
			}else $label = $ptype;
			
			$ptype = strtolower($ptype);
			if($ptype=='discount')  continue;
			if($ptype=='credit cards')	$ptype='credit_cards';
			$this->normal_payment_type[] = $ptype;
			$this->normal_payment_type_label[$ptype] = $label;
		}*/
		
		// Cash is default
		//$this->normal_payment_type[] = 'cash';
		
		// select other payment type from pos settings
		$q1 = $con_multi->sql_query("select * from pos_settings where setting_name = 'payment_type' and branch_id = ".mi($this->branch_id));
		$ps_info = $con_multi->sql_fetchassoc($q1);
		$ps_payment_type = unserialize($ps_info['setting_value']);
		$con_multi->sql_freeresult($q1);

		if($ps_payment_type){
			foreach($ps_payment_type as $ptype=>$val){
				$ori_ptype = $ptype;
				if(!$val) continue;	// in-active
				
				if($ptype == "credit_card") $ptype = "credit_cards";
				$this->normal_payment_type[] = $ptype;
			}
		}
		
		foreach($pos_config['payment_type'] as $ptype){
			if($ptype=='Discount')  continue;
			$ori_ptype = $ptype;
			if($ptype == "Credit Cards") $ptype = "credit_cards";
			$ptype = strtolower($ptype);
			$this->all_payment_type[$ptype] = $ori_ptype;
		}
		
		// extend by extra payment type
		if($config['counter_collection_extra_payment_type']){
			foreach($config['counter_collection_extra_payment_type'] as $ptype){
				$ori_ptype = $ptype;
				$ptype = strtolower($ptype);
				$this->all_payment_type[$ptype] = $ori_ptype;
			}
		}
		
		// eWallet Payment Type
		if($config['ewallet_list']){
			foreach($config['ewallet_list'] as $ptype => $r){
				$ptype = strtolower('ewallet_'.$ptype);
				$this->all_payment_type[$ptype] = $ptype;
			}
		}
		
		$filter = "p.branch_id=$this->branch_id and p.date=".ms($this->date);
		//$filter .= " and p.cancel_status=0";
				
		$sql = "select distinct counter_id, cs.network_name
		from pos p
		left join counter_settings cs on p.counter_id=cs.id and p.branch_id=cs.branch_id
		where $filter
		order by cs.network_name";
		//print $sql;
		$pos_counters = array();
		$con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc()){
			$pos_counters[$r['counter_id']] = $r;
		}
		$con_multi->sql_freeresult();
		//print_r($pos_counters);
		
		if($pos_counters){
			foreach($pos_counters as $counter_id=>$c){
				$this->generate_data($counter_id);
			}
			
			// got sales
			if($this->data['summary']){
				$this->generate_total();
			}
		}
		
		// load custom payment type label if found got set
		foreach($this->normal_payment_type as $ptype){
			$ptype = strtolower($ptype);
			if($ptype=='discount')  continue;
			if(in_array($ptype, array_keys($this->all_payment_type))){
				$this->normal_payment_type_label[$ptype] = $this->all_payment_type[$ptype];
			}
		}
		
		//print_r($this->data);
		
		asort($this->normal_payment_type);
		$pos_config['normal_payment_type'] = $this->normal_payment_type;
		$pos_config['normal_payment_type_label'] = $this->normal_payment_type_label;

		$smarty->assign("pos_config",$pos_config);
		$smarty->assign('data', $this->data);
		$smarty->assign('pos_counters', $pos_counters);
		$smarty->assign('got_top_up', $this->got_top_up);
		$smarty->assign('got_mm_discount',$this->got_mm_discount);
		$smarty->assign('got_service_charge',$this->got_service_charge);
		$smarty->assign('got_gst',$this->got_gst);
	}
	
	private function generate_data($counter_id){
	 	global $con, $smarty, $pos_config,$mm_discount_col_value, $config, $con_multi;
		 	
		$counter_id = mi($counter_id);
		if(!$counter_id)	return;
		
		$filter = "p.branch_id=$this->branch_id and p.date=".ms($this->date)." and p.counter_id=$counter_id";
		//$filter_tran .= " and p.cancel_status=0";
		
		// get from pos first
		$sql = "select p.* from pos p where $filter $filter_tran order by pos_time";
		$q_pos = $con_multi->sql_query($sql);
		while($pos = $con_multi->sql_fetchrow($q_pos)){
		    $pos_id = mi($pos['id']);
		    
		    // check is cancelled receipt
		    if($pos['cancel_status'] || $pos['prune_status']){
				$this->data['summary'][$counter_id]['cancelled_pos']['count']++;
				$this->data['summary'][$counter_id]['cancelled_pos']['amt'] += round($pos['amount'], 2);
				continue;
			}else{
				$this->data['summary'][$counter_id]['valid_pos']['count'] ++;
			}
			
			$amount_change_dealed = false;
			
		    // get pos payment
		    $sql = "select pp.*
from pos_payment pp
where pp.branch_id=".mi($this->branch_id)." and pp.date=".ms($this->date)." and pp.counter_id=$counter_id and pp.pos_id=$pos_id";
			$q_pp = $con_multi->sql_query($sql);
			$receipt_rounding_amt = 0;
			$rounding_type = 'cash';
			while($pp = $con_multi->sql_fetchassoc($q_pp)){
				$is_changed = mi($pp['changed']);
			    $is_adjust = mi($pp['adjust']);
			    
			    $row_type = 'cashier_sales';
				$is_foreign_currency = false;
				$add_to_nett_sales = true;
				$is_receipt_discount = false;
				unset($old_amt, $old_currency_amt, $currency_amt, $currency_rate);
				$counter_id = mi($pp['counter_id']);
				$adj_amt = 0;
				
				if($is_changed){
	                $row_type = 'adj_in';
				}elseif($is_adjust)	$row_type = 'adj_out';
					
				$rm_amt = 0;
				
				// check is credit card
				if ($pp['type']=='Credit Cards' || in_array($pp['type'], $pos_config['credit_card'])) $payment_type = 'credit_cards';
				elseif ($pp['type'] == 'Cheque') $payment_type = 'Check';
		        else	$payment_type = $pp['type'];
		        
		        // check payment type
		        $payment_type = strtolower($payment_type);
		        
		        if($is_adjust || $is_changed)  $adj_amt = $pp['amount'];
		        
	            switch ($payment_type){
                	case strtolower($mm_discount_col_value):
                		//$payment_type = 'Discount';	// store together with receipt discount
                		$rm_amt = $pp['amount'];
                		$this->got_mm_discount = true;
                		break;
					case 'discount':    // it is discount
					    $rm_amt = $pp['amount'];  // discount show as negative
					    $is_receipt_discount = true;
					    break;
					case 'cash':    // it is cash payment
					    //if(!$is_adjust){
							$rm_amt = $pp['amount'] - $pos['amount_change'];    // amount minus changed
						//}else{
						//	$old_amt = $rm_amt = $pp['amount'];
							//$row_type = 'adj_out';
						//}	
							$amount_change_dealed = true;
					    break;
					case 'deposit':	// pay by deposit
						if($pos['amount_change']){	// got amt changed
							// check whether this deposit got pay by cash
							$con_multi->sql_query("select count(*) from pos_payment pp where pp.branch_id=".mi($this->branch_id)." and pp.date=".ms($this->date)." and pp.counter_id=$counter_id and pp.pos_id=$pos_id and pp.type='Cash' and pp.adjust=0");
							$deposit_got_cash = mi($con_multi->sql_fetchfield(0));
							$con_multi->sql_freeresult();
						
							if(!$deposit_got_cash){	// this deposit dun hv cash but got change
								$payment_type = 'cash';	// change payment type to cash
								$rm_amt = $pos['amount_change']*-1;	// make refund count into cash sales
								$amount_change_dealed = true;
								//$this->data[$counter_id][$dom_id]['deposit_refund_amt']+=$pos['amount_change'];
							}
						}
						break;
					default:
					    // check is foreign currency
			            $currency_arr = pp_is_currency($pp['remark'], $pp['amount']);
			            if($currency_arr['is_currency']){   // it is foreign currency
							$old_currency_amt = $currency_amt = round($currency_arr['currency_amt'], 2);
							$currency_rate = mf($currency_arr['currency_rate']);
							$is_foreign_currency = true;
						}
						$old_amt = $rm_amt = round($currency_arr['rm_amt'], 2);
					    break;
				}
				if($payment_type=='credit_cards'){	// record this receipt got credit card for rounding
					$rounding_type = 'credit_cards';
				}
				if($payment_type=='rounding'){	// record this receipt rounding amt
					if($row_type != 'adj_out'){	// dont count if alrdy adjust out
						$receipt_rounding_amt += $rm_amt;
					}
				}
				
				if(!isset($old_amt))	$old_amt = $rm_amt;
				
				if($is_foreign_currency){
					// create foreign currency array					
					$this->check_and_create_foreign_currency_list($payment_type, $currency_rate);
				}
								
				// dont add those alrdy adjust out
				if($row_type != 'adj_out'){
					$use_amt = $is_changed ? $adj_amt : $rm_amt;
					$this->data['summary'][$counter_id][$row_type][$payment_type]['amt'] += $use_amt;
					
					// currency
					if($is_foreign_currency){
						$this->data['summary'][$counter_id][$row_type][$payment_type]['currency_amt'] += $currency_amt;
					}
				}
				
				
				if($row_type == 'adj_in'){	
					// adjust from other payment type, add into cashier sales
					$this->data['summary'][$counter_id]['cashier_sales'][$payment_type]['amt'] += $rm_amt;
					
					// currency
					if($is_foreign_currency){
						$this->data['summary'][$counter_id]['cashier_sales'][$payment_type]['currency_amt'] += $currency_amt;
					}
				}elseif($row_type == 'cashier_sales' || $row_type == 'adj_out'){
					// it is cahsier sales or already adjust out, store into old mat
					$this->data['summary'][$counter_id]['cashier_sales'][$payment_type]['old_amt'] += $old_amt;
					
					// currency
					if($is_foreign_currency){
						$this->data['summary'][$counter_id]['cashier_sales'][$payment_type]['old_currency_amt'] += $old_currency_amt;
					}
				}
		
				// this is receipt discount
				if($is_receipt_discount){
					//$this->data['summary'][$counter_id]['cashier_sales']['discount']['amt'] += $rm_amt;
					$this->data['summary'][$counter_id]['cashier_sales']['discount']['tran_count']++;
				}
				
				if($payment_type && $payment_type != $mm_discount_col_value && $payment_type != "Discount" && !in_array($payment_type, $this->normal_payment_type) && in_array($payment_type, array_keys($this->all_payment_type))){
					$this->normal_payment_type[] = $payment_type;
				}
			}
			$con_multi->sql_freeresult($q_pp);
		
			// sepcial cash refund / change
			if($pos['amount_change'] > 0 && !$amount_change_dealed){
				//print "extra change = ".$pos['amount_change']."<br>";
				$this->data['summary'][$counter_id]['cash_change']['amt'] += $pos['amount_change'];
				$this->data['summary'][$counter_id]['cash_change']['got_data'] = 1;
				
				$this->data['summary'][$counter_id]['cashier_sales']['cash']['old_amt'] -= $pos['amount_change'];
				$this->data['summary'][$counter_id]['cashier_sales']['cash']['amt'] -= $pos['amount_change'];
			}
			
			// service charge
			if($pos['service_charges']){
				$this->data['summary'][$counter_id]['cashier_sales']['service_charges']['amt'] += round($pos['service_charges']-$pos['service_charges_gst_amt'], 2);
				$this->got_service_charge = true;
			}
			
			// gst
			if($pos['total_gst_amt'] || $pos['service_charges_gst_amt']){
				$this->data['summary'][$counter_id]['cashier_sales']['total_gst_amt']['amt'] +=round($pos['total_gst_amt'],2);
				$this->got_gst = true;
			}
			
			// get pos items
			$sql = "select pi.*
from pos_items pi
where pi.branch_id=".mi($this->branch_id)." and pi.date=".ms($this->date)." and pi.counter_id=$counter_id and pi.pos_id=$pos_id";
			$q_pi = $con_multi->sql_query($sql);
			while($pi = $con_multi->sql_fetchassoc($q_pi)){
				$col_type = '';
				$amt = 0;
				
				if($pi['item_discount_by']){	// item discount
					$col_type = 'item_disc';
					$amt = $pi['discount'];
				}elseif($pi['open_price_by']){	// open price
					$col_type = 'open_price';
					$amt = $pi['price'];
				}elseif($pi['mprice_type']){	// mprice
					$col_type = 'mprice';
					$amt = $pi['price'];
				}elseif($pi['qty']<0){	// goods return
					$col_type = 'goods_return';
					$amt = $pi['price'];
				}
				
				if(!$col_type)	continue;
				
				$this->data['summary'][$counter_id]['items'][$col_type]['qty'] += $pi['qty'];
				$this->data['summary'][$counter_id]['items'][$col_type]['amt'] += round($amt, 2);
			}
			$con_multi->sql_freeresult($q_pi);
				
			// over
		    $pos_amt = $pos['amount'];
			$real_receipt_amt = $pos['amount_tender'] - $pos['amount_change'] - $pos['service_charges'];
			$over_amt = mf($real_receipt_amt-$pos_amt);
			if($over_amt){
                $this->data['summary'][$counter_id]['cashier_sales']['over']['amt'] += round($over_amt,2);
			}
			
			// receipt rounding amt by cash/credit card
			$this->data['summary'][$counter_id]['cashier_sales']['rounding']['by_type'][$rounding_type]['amt'] += $receipt_rounding_amt;
		}
			
		$this->data['summary'][$counter_id]['total_tran']['count'] = $this->data['summary'][$counter_id]['valid_pos']['count'] + $this->data['summary'][$counter_id]['cancelled_pos']['count'];

		$con_multi->sql_freeresult($q_pos);
		
		// cash advance
	    $q_pch = $con_multi->sql_query("select * from pos_cash_history p where $filter");
	    while($pch = $con_multi->sql_fetchrow($q_pch)){
	        $currency_arr = pch_is_currency($pch['remark'], $pch['amount']);
	        $rm_amt = $currency_arr['rm_amt'];

			$payment_type = $currency_arr['is_currency'] ? $currency_arr['currency_type'] : 'cash';
			
			if(!$pch['oamount']) $pch['oamount'] = $rm_amt;
			
			$currency_arr = pch_is_currency($pch['remark'], $pch['oamount']);
	        $old_rm_amt = $currency_arr['rm_amt'];
	        
			switch($pch['type']){
				case 'ADVANCE':
					$col_type = 'cash_advance';
					break;
				case 'TOP_UP':
					$col_type = 'top_up';
					$this->got_top_up = true;
					break;
				default:
					continue 2;
			}
			
			$this->data['summary'][$counter_id][$col_type][$payment_type]['amt'] += $rm_amt;
				
			// old amt
	        $this->data['summary'][$counter_id][$col_type][$payment_type]['old_amt'] += $old_rm_amt;
			
		}
		$con_multi->sql_freeresult($q_pch);
		
		$pos_cash_domination_notes = $config['cash_domination_notes'];
		// pos cash domination
		$sql = "select * from pos_cash_domination p where $filter";
		$q_pcd = $con_multi->sql_query($sql);
		
		while($r = $con_multi->sql_fetchassoc($q_pcd)){
			$r['data'] = unserialize($r['data']);
			$r['odata'] = unserialize($r['odata']);
			$r['curr_rate'] = unserialize($r['curr_rate']);
			$r['ocurr_rate'] = unserialize($r['ocurr_rate']);
			
			if(!$r['data']&&!$r['odata']) continue;
			
			if(!$r['odata']) $r['odata'] = $r['data'];
			if(!$r['ocurr_rate']) $r['ocurr_rate'] = $r['curr_rate'];
			
			// latest data
			if($r['data']){
                if($r['curr_rate']){
					$curr_rate = $r['curr_rate'];  // use cash domination currency rate
				}
				else{
					$curr_rate = $this->foreign_currency_list;  // use default currency rate
				}

				foreach($r['data'] as $type=>$d2){
				    $type_key = $type;
				    $is_foreign_currency = false;
				    unset($currency_amt);
				    
                    if ($type_key == 'Cheque') $type_key = 'check';

					if (in_array($type, $pos_config['credit_card']))
						$type_key = 'credit_cards';
					elseif (in_array($type, array_keys($pos_cash_domination_notes)))
					{
						$d2 = $d2 * $config['cash_domination_notes'][$type]['value'];
						$type_key = 'cash';
					}
					elseif ($type == 'Float')
					{
						$type_key = 'cash';
						$d2 *= -1;
					}elseif(strpos($type, '_Float')){   // currency float
                          $type_key = str_replace('_Float', '', $type);
                          $d2 *= -1;
					}

					//print_r($curr_rate);
					$tmp_type_key = strtoupper($type_key);
                    if($curr_rate[$tmp_type_key]){  // is currency collection
                    	$currency_rate = $curr_rate[$tmp_type_key];
                        $rm_amt = $currency_rate ? mf($d2/$currency_rate) : 0;
                        
                        $is_foreign_currency = true;
                        $currency_amt = $d2;
					}else{
					    $rm_amt = $d2;
					}
					$rm_amt = round($rm_amt, 2);
					
					$type_key = strtolower($type_key);
					$this->data['summary'][$counter_id]['cash_domination'][$type_key]['amt'] += $rm_amt;

					if($is_foreign_currency){
						// create foreign currency array					
						$this->check_and_create_foreign_currency_list($type_key, $currency_rate);
					
						$this->data['summary'][$counter_id]['cash_domination'][$type_key]['currency_amt'] += $currency_amt;
					}else{
						if($type=='Float'){ // is cash float
	                        $this->data['summary'][$counter_id]['cash_domination']['float']['amt'] += abs($rm_amt);
						}
					}
					
					if($type_key && $type_key != $mm_discount_col_value && $type_key != "Discount" && !in_array($type_key, $this->normal_payment_type) && (in_array($type_key, array_keys($this->all_payment_type)) || preg_match('/^ewallet_/i', $type_key))){
						$this->normal_payment_type[] = $type_key;
						if(preg_match('/^ewallet_/i', $type_key) && !$this->normal_payment_type_label[$type_key]) $this->normal_payment_type_label[$type_key] = $type_key;
					}
				}
			}

			// original data
			if($r['odata']){
                  if($r['ocurr_rate']){
					$curr_rate = $r['ocurr_rate'];  // use cash domination currency rate
				}
				else{
					$curr_rate = $this->foreign_currency_list;  // use default currency rate
				}

				foreach($r['odata'] as $type=>$d2){
				    $type_key = $type;
				    $is_foreign_currency = false;
				    unset($old_currency_amt);
				    
                    if ($type_key == 'Cheque') $type_key = 'check';

					if (in_array($type, $pos_config['credit_card']))
						$type_key = 'credit_cards';
					elseif (in_array($type, array_keys($pos_cash_domination_notes)))
					{
						$d2 = $d2 * $config['cash_domination_notes'][$type]['value'];
						$type_key = 'cash';
					}
					elseif ($type == 'Float')
					{
						
						$type_key = 'cash';
						$d2 *= -1;
						//print "float = $d2<br />";
					}elseif(strpos($type, '_Float')){   // currency float
                          $type_key = str_replace('_Float', '', $type);
                          $d2 *= -1;
					}

					$tmp_type_key = strtoupper($type_key);
                    if($curr_rate[$tmp_type_key]){  // is currency collection
                    	$currency_rate = $curr_rate[$tmp_type_key];
                        $rm_amt = $currency_rate ? mf($d2/$currency_rate) : 0;
                        
                        $is_foreign_currency = true;
                        $old_currency_amt = $d2;
					}else{
					    $rm_amt = $d2;
					}
					$rm_amt = round($rm_amt, 2);
					
					$type_key = strtolower($type_key);
					$this->data['summary'][$counter_id]['cash_domination'][$type_key]['old_amt'] += $rm_amt;
					
					if($is_foreign_currency){
						// create foreign currency array					
						$this->check_and_create_foreign_currency_list($type_key, $currency_rate);
						
						$this->data['summary'][$counter_id]['cash_domination'][$type_key]['old_currency_amt'] += $old_currency_amt;
					}
					
					if($type_key && $type_key != $mm_discount_col_value && $type_key != "Discount" && !in_array($type_key, $this->normal_payment_type) && (in_array($type_key, array_keys($this->all_payment_type)) || preg_match('/^ewallet_/i', $type_key))){
						$this->normal_payment_type[] = $type_key;
						if(preg_match('/^ewallet_/i', $type_key) && !$this->normal_payment_type_label[$type_key]) $this->normal_payment_type_label[$type_key] = $type_key;
					}
				}
			}
		}
		$con_multi->sql_freeresult($q_pcd);
		//print_r($this->data);
		
		if($this->data['summary'][$counter_id]['cashier_sales'][strtolower($mm_discount_col_value)]){
			// counter
			$this->data['summary'][$counter_id]['cash_domination'][strtolower($mm_discount_col_value)]['amt'] += $this->data['summary'][$counter_id]['cashier_sales'][strtolower($mm_discount_col_value)]['amt'];
		}
		
		// calculate variance
		foreach($this->normal_payment_type as $payment_type){		
			$variance = round($this->data['summary'][$counter_id]['cash_domination'][$payment_type]['amt'] -($this->data['summary'][$counter_id]['cash_advance'][$payment_type]['amt'] + $this->data['summary'][$counter_id]['cashier_sales'][$payment_type]['amt'] + $this->data['summary'][$counter_id]['top_up'][$payment_type]['amt']),2);
			$this->data['summary'][$counter_id]['variance'][$payment_type]['amt'] += $variance;
		}
		
		// currency payment type
		if($this->data['foreign_currency_list']){
			foreach($this->data['foreign_currency_list'] as $currency_type=>$curr_data){
				// currency amt
				$curr_amt = round($this->data['summary'][$counter_id]['cash_domination'][$currency_type]['currency_amt'] - ($this->data['summary'][$counter_id]['cash_advance'][$currency_type]['currency_amt'] + $this->data['summary'][$counter_id]['cashier_sales'][$currency_type]['currency_amt'] + $this->data['summary'][$counter_id]['top_up'][$currency_type]['currency_amt']),2);
				// rm amt
				//$rm_amt = round($this->data['summary'][$counter_id]['cash_domination'][$currency_type]['amt'] - ($this->data['summary'][$counter_id]['cash_advance'][$currency_type]['amt'] + $this->data['summary'][$counter_id]['cashier_sales'][$currency_type]['amt'] + $this->data['summary'][$counter_id]['top_up'][$currency_type]['amt']),2);
				$rm_amt = round($curr_amt / $this->foreign_currency_list[$currency_type], 2);
				$this->data['summary'][$counter_id]['variance'][$currency_type]['currency_amt'] += $curr_amt;
				$this->data['summary'][$counter_id]['variance'][$currency_type]['amt'] += $rm_amt;
			}
		}
		
		// calculate total sales
		foreach(array('cashier_sales', 'cash_advance', 'cash_domination', 'variance','top_up') as $row_type){
			if(!isset($this->data['summary'][$counter_id][$row_type]))	continue;
			
			// normal payment type
			foreach($this->normal_payment_type as $payment_type){
				
				$this->data['summary'][$counter_id][$row_type]['total_sales']['amt'] += round($this->data['summary'][$counter_id][$row_type][$payment_type]['amt'],2);
				if(!isset($this->data['summary'][$counter_id][$row_type][$payment_type]['old_amt'])) $this->data['summary'][$counter_id][$row_type][$payment_type]['old_amt'] = $this->data['summary'][$counter_id][$row_type][$payment_type]['amt'];
				$this->data['summary'][$counter_id][$row_type]['total_sales']['old_amt'] += round($this->data['summary'][$counter_id][$row_type][$payment_type]['old_amt'],2);
			}
			
			// currency payment type
			if($this->data['foreign_currency_list']){
				foreach($this->data['foreign_currency_list'] as $currency_type=>$curr_data){
					if($row_type != "cash_domination") $this->data['summary'][$counter_id][$row_type]['total_sales']['amt'] += round($this->data['summary'][$counter_id][$row_type][$currency_type]['amt'],2);
					
					if(!isset($this->data['summary'][$counter_id][$row_type][$currency_type]['old_amt'])) $this->data['summary'][$counter_id][$row_type][$currency_type]['old_amt'] = round($this->data['summary'][$counter_id][$row_type][$currency_type]['amt'],2);
					// old amt
					$this->data['summary'][$counter_id][$row_type]['total_sales']['old_amt'] += round($this->data['summary'][$counter_id][$row_type][$currency_type]['old_amt'],2);
				}
			}
		}
		// additional total sales
		$this->data['summary'][$counter_id]['cash_domination']['total_sales']['amt'] += $this->data['summary'][$counter_id]['cash_domination'][strtolower($mm_discount_col_value)]['amt'];
		$this->data['summary'][$counter_id]['cash_domination']['total_sales']['old_amt'] += $this->data['summary'][$counter_id]['cash_domination'][strtolower($mm_discount_col_value)]['amt'];
		
		// add back the discount
		$this->data['summary'][$counter_id]['cashier_sales']['total_sales']['amt'] += ($this->data['summary'][$counter_id]['cashier_sales']['discount']['amt']);
		
		// add over into total sales
		//$this->data['summary'][$counter_id]['cashier_sales']['total_sales']['amt'] += $this->data['summary'][$counter_id]['cashier_sales']['over']['amt'];
		
		// deduct the rounding
		$this->data['summary'][$counter_id]['cashier_sales']['total_sales']['amt'] -= $this->data['summary'][$counter_id]['cashier_sales']['rounding']['amt'];
		
		$this->data['summary'][$counter_id]['cashier_sales']['total_sales']['amt'] -= $this->data['summary'][$counter_id]['cashier_sales']['over']['amt'];
		
		$this->data['summary'][$counter_id]['cashier_sales']['total_sales']['amt'] += ($this->data['summary'][$counter_id]['cashier_sales'][strtolower($mm_discount_col_value)]['amt']);
		
		// service_charges
		$this->data['summary'][$counter_id]['cashier_sales']['total_sales']['amt'] -= $this->data['summary'][$counter_id]['cashier_sales']['service_charges']['amt'];
		
		// gst
		$this->data['summary'][$counter_id]['cashier_sales']['total_sales']['amt'] -= $this->data['summary'][$counter_id]['cashier_sales']['total_gst_amt']['amt'];
		
		// currency adjust
		$this->data['summary'][$counter_id]['cashier_sales']['total_sales']['amt'] += $this->data['summary'][$counter_id]['cashier_sales']['currency_adjust']['amt'];
		
		// drawer open count
		$con_multi->sql_query("select count(*) from pos_drawer where branch_id=$this->branch_id and counter_id=".mi($counter_id)." and date=".ms($this->date)." and type = 'POS'");
		$this->data['summary'][$counter_id]['drawer_open_count'] = $con_multi->sql_fetchfield(0);
		$con_multi->sql_freeresult();
		
	 }
	 
	 private function check_and_create_foreign_currency_list($payment_type, $currency_rate){
		global $appCore;
	 	
	 	// create foreign currency array
		if(!is_array($this->data['foreign_currency_list']))	$this->data['foreign_currency_list'] = array();
		
		// create this currency type array
		if(!$this->data['foreign_currency_list'][$payment_type]){
			$this->data['foreign_currency_list'][$payment_type]['type'] = $payment_type;
		}
		
		// create currency rate array
		if(!is_array($this->data['foreign_currency_list'][$payment_type]['currency_rate_list'])){
			$this->data['foreign_currency_list'][$payment_type]['currency_rate_list'] = array();
		}
		
		if(!in_array($currency_rate, $this->data['foreign_currency_list'][$payment_type]['currency_rate_list'])){
			$this->data['foreign_currency_list'][$payment_type]['currency_rate_list'][] = $currency_rate;
		}

		if(!isset($this->foreign_currency_list[$payment_type])){
			if(!$currency_rate){
				$prms = array();
				$prms['branch_id'] = $this->branch_id;
				$prms['date'] = $this->date;
				$prms['code'] = $payment_type;
				$global_currency_rate = $appCore->posManager->loadForeignCurrencyRate($prms);
			}else $global_currency_rate = $currency_rate;
			
			$this->foreign_currency_list[$payment_type] = $global_currency_rate;
		}
	 }
	 
	 private function generate_total(){
	 	global $con, $smarty, $pos_config, $config, $mm_discount_col_value;
		 
		if(!$this->data['summary'])	return;	// no counter got sales
		
		foreach($this->data['summary'] as $counter_id => $summary){
		
			// Actual Sales
			$this->data['total']['actual_sales']['amt'] += round($summary['cashier_sales']['total_sales']['amt'], 2);
			
			// Collection
			$this->data['total']['collection']['amt'] += round(($summary['cash_advance']['total_sales']['amt']*-1)+$summary['cash_domination']['total_sales']['amt'], 2);
			
			// Collection by foreign currency
			if($this->data['foreign_currency_list']){
				foreach($this->data['foreign_currency_list'] as $currency_type=>$curr_data){
					$this->data['total']['collection'][$currency_type]['currency_amt'] += $summary['cash_domination'][$currency_type]['currency_amt'];
				}
			}
			
			// Rounding
			$this->data['total']['rounding']['amt'] += round($summary['cashier_sales']['rounding']['amt'], 2);
			
			// currency adjust
			$this->data['total']['currency_adjust']['amt'] += round($summary['cashier_sales']['currency_adjust']['amt'], 2);
			
			// Variance
			$tmp_amt = round($summary['variance']['total_sales']['amt'], 2);
			//print "$counter_id, variance += $tmp_amt<br />";
			$this->data['total']['variance']['amt'] += $tmp_amt;
			if($tmp_amt){
				if($tmp_amt>0){
					$this->data['total']['variance']['over']['amt'] += $tmp_amt;	
				}else{
					$this->data['total']['variance']['short']['amt'] += $tmp_amt;
				}
			}
			
			// Over
			$this->data['total']['over']['amt'] += round($summary['cashier_sales']['over']['amt'], 2);

			// service charge
			if($this->got_service_charge){
				$this->data['total']['service_charges']['amt'] += round($summary['cashier_sales']['service_charges']['amt'], 2);	
			}
			
			// gst
			if($this->got_gst){
				$this->data['total']['total_gst_amt']['amt'] += round($summary['cashier_sales']['total_gst_amt']['amt'], 2);	
			}
			
			// cash advance
			$this->data['total']['cash_advance']['amt'] += round($summary['cash_advance']['total_sales']['amt'], 2);
			$this->data['total']['cash_advance']['old_amt'] += round($summary['cash_advance']['total_sales']['old_amt'], 2);
			
			// top up
			if($summary['top_up']){
				$this->data['total']['top_up']['amt'] += round($summary['top_up']['total_sales']['amt'], 2);
				$this->data['total']['top_up']['old_amt'] += round($summary['top_up']['total_sales']['old_amt'], 2);
			}
			
			// cash domination
			$this->data['total']['cash_domination']['amt'] += round($summary['cash_domination']['total_sales']['amt'], 2);
			$this->data['total']['cash_domination']['old_amt'] += round($summary['cash_domination']['total_sales']['old_amt'], 2);
			
			// normal payment type
			foreach($this->normal_payment_type as $payment_type){
				// summary total
				$this->data['total']['summary']['cashier_sales'][$payment_type]['amt'] += round($summary['cashier_sales'][$payment_type]['amt'], 2);
				$this->data['total']['summary']['cashier_sales'][$payment_type]['old_amt'] += round($summary['cashier_sales'][$payment_type]['old_amt'], 2);
				// total by payment
				$this->data['total']['payment_type'][$payment_type]['amt'] += round($summary['cashier_sales'][$payment_type]['amt'], 2);
				$this->data['total']['payment_type'][$payment_type]['variance'] += round($summary['variance'][$payment_type]['amt'], 2);
				$this->data['total']['payment_type'][$payment_type]['act_amt'] += round($summary['cashier_sales'][$payment_type]['amt']+$summary['variance'][$payment_type]['amt'], 2);
				
				// abnormal - backend
				$tmp_diff_amt = round($summary['cashier_sales'][$payment_type]['amt'] - $summary['cashier_sales'][$payment_type]['old_amt'], 2);
				$this->data['abnormal_tran']['backend'][$counter_id][$payment_type]['diff']['amt'] = $tmp_diff_amt;
				$this->data['abnormal_tran']['backend']['total'][$payment_type]['diff']['amt'] += $tmp_diff_amt;
				
				// abnormal - remark
				if($tmp_diff_amt!=0){
					$remark_col = $tmp_diff_amt>0 ? 'inc' : 'dec';
					$this->data['abnormal_tran']['backend'][$counter_id]['remark'][$remark_col]['amt'] += $tmp_diff_amt;
					$this->data['abnormal_tran']['backend']['total']['remark'][$remark_col]['amt'] += $tmp_diff_amt;
				}
				
				
			}
			
			//print '<pre>';print_r($summary);print '</pre>';
			
			$this->data['total']['payment_type'][strtolower($mm_discount_col_value)]['amt'] += round($summary['cashier_sales'][strtolower($mm_discount_col_value)]['amt'], 2);
			$this->data['total']['payment_type']['discount']['amt'] += round($summary['cashier_sales']['discount']['amt'], 2);
			
			// abnormal - top up
			if($summary['top_up']){
				$tmp_diff_amt = round($summary['top_up']['total_sales']['amt'] - $summary['top_up']['total_sales']['old_amt'], 2);
				$this->data['abnormal_tran']['backend'][$counter_id]['top_up']['diff']['amt'] = $tmp_diff_amt;
				$this->data['abnormal_tran']['backend']['total']['top_up']['diff']['amt'] += $tmp_diff_amt;
				
				// abnormal - remark
				if($tmp_diff_amt!=0){
					$remark_col = $tmp_diff_amt>0 ? 'inc' : 'dec';
					$this->data['abnormal_tran']['backend'][$counter_id]['remark'][$remark_col]['amt'] += $tmp_diff_amt;
					$this->data['abnormal_tran']['backend']['total']['remark'][$remark_col]['amt'] += $tmp_diff_amt;
				}
			}
			
			// abnormal - cash advance
			$tmp_diff_amt = round($summary['cash_advance']['total_sales']['amt'] - $summary['cash_advance']['total_sales']['old_amt'], 2)*-1;
			$this->data['abnormal_tran']['backend'][$counter_id]['cash_advance']['diff']['amt'] = $tmp_diff_amt;
			$this->data['abnormal_tran']['backend']['total']['cash_advance']['diff']['amt'] += $tmp_diff_amt;
			
			// abnormal - remark
			if($tmp_diff_amt!=0){
				$remark_col = $tmp_diff_amt>0 ? 'inc' : 'dec';
				$this->data['abnormal_tran']['backend'][$counter_id]['remark'][$remark_col]['amt'] += $tmp_diff_amt;
				$this->data['abnormal_tran']['backend']['total']['remark'][$remark_col]['amt'] += $tmp_diff_amt;
			}
					
			// abnormal - cash denomination
			$tmp_diff_amt = round($summary['cash_domination']['total_sales']['amt'] - $summary['cash_domination']['total_sales']['old_amt'], 2);
			$this->data['abnormal_tran']['backend'][$counter_id]['cash_domination']['diff']['amt'] = $tmp_diff_amt;
			$this->data['abnormal_tran']['backend']['total']['cash_domination']['diff']['amt'] += $tmp_diff_amt;
			
			// abnormal - remark
			if($tmp_diff_amt!=0){
				$remark_col = $tmp_diff_amt>0 ? 'inc' : 'dec';
				$this->data['abnormal_tran']['backend'][$counter_id]['remark'][$remark_col]['amt'] += $tmp_diff_amt;
				$this->data['abnormal_tran']['backend']['total']['remark'][$remark_col]['amt'] += $tmp_diff_amt;
			}
			
			// currency
			if($this->data['foreign_currency_list']){
				foreach($this->data['foreign_currency_list'] as $currency_type=>$curr_data){
					// summary total
					$this->data['total']['summary']['cashier_sales'][$currency_type]['amt'] += round($summary['cashier_sales'][$currency_type]['amt'], 2);
					$this->data['total']['summary']['cashier_sales'][$currency_type]['old_amt'] += round($summary['cashier_sales'][$currency_type]['old_amt'], 2);
					$this->data['total']['summary']['cashier_sales'][$currency_type]['currency_amt'] += round($summary['cashier_sales'][$currency_type]['currency_amt'], 2);
					$this->data['total']['summary']['cashier_sales'][$currency_type]['old_currency_amt'] += round($summary['cashier_sales'][$currency_type]['old_currency_amt'], 2);
				
					// total by payment
					$this->data['total']['payment_type'][$currency_type]['amt'] += round($summary['cashier_sales'][$currency_type]['amt'], 2);
					$this->data['total']['payment_type'][$currency_type]['variance'] += round($summary['variance'][$currency_type]['amt'], 2);
					$this->data['total']['payment_type'][$currency_type]['act_amt'] += round($summary['cashier_sales'][$currency_type]['amt']+$summary['variance'][$currency_type]['amt'], 2);
					
					// abnormal - backend
					$this->data['abnormal_tran']['backend'][$counter_id][$currency_type]['diff']['currency_amt'] = round($summary['cashier_sales'][$currency_type]['currency_amt'] - $summary['cashier_sales'][$currency_type]['old_currency_amt'], 2);
					$this->data['abnormal_tran']['backend']['total'][$currency_type]['diff']['currency_amt'] += round($summary['cashier_sales'][$currency_type]['currency_amt'] - $summary['cashier_sales'][$currency_type]['old_currency_amt'], 2);
					
					$tmp_diff_amt = round($summary['cashier_sales'][$currency_type]['amt'] - $summary['cashier_sales'][$currency_type]['old_amt'], 2);
					$this->data['abnormal_tran']['backend'][$counter_id][$currency_type]['diff']['amt'] = $tmp_diff_amt;
					$this->data['abnormal_tran']['backend']['total'][$currency_type]['diff']['amt'] += $tmp_diff_amt;
					
					// abnormal - remark
					if($tmp_diff_amt!=0){
						$remark_col = $tmp_diff_amt>0 ? 'inc' : 'dec';
						$this->data['abnormal_tran']['backend'][$counter_id]['remark'][$remark_col]['amt'] += $tmp_diff_amt;
						$this->data['abnormal_tran']['backend']['total']['remark'][$remark_col]['amt'] += $tmp_diff_amt;
					}
				}
			}
			
			$this->data['abnormal_tran']['backend'][$counter_id]['remark']['diff']['amt'] = round($this->data['abnormal_tran']['backend'][$counter_id]['remark']['inc']['amt'] + $this->data['abnormal_tran']['backend'][$counter_id]['remark']['dec']['amt'], 2);
			$this->data['abnormal_tran']['backend']['total']['remark']['diff']['amt'] += $this->data['abnormal_tran']['backend'][$counter_id]['remark']['diff']['amt'];
		}
		
		// Counter Sales
		$this->data['total']['counter_sales']['amt'] = round($this->data['total']['actual_sales']['amt']+$this->data['total']['rounding']['amt']+$this->data['total']['over']['amt'], 2);
		
		if($config['counter_collection_details_extra_print_area'])	$this->generate_xtra();
	 }
	 
	 private function generate_xtra(){
	 	global $con, $smarty, $pos_config, $config;
		 
		if(!$this->data['summary'])	return;	// no counter got sales
		
		foreach($this->data['summary'] as $counter_id => $summary){
			$this->data['xtra']['cash_collected']['amt'] += round(($summary['cash_advance']['cash']['amt']*-1) + $summary['cash_domination']['cash']['amt'], 2);
			
			$this->data['xtra']['rounding']['cash']['amt'] += round(($summary['cashier_sales']['rounding']['by_type']['cash']['amt']), 2);
			$this->data['xtra']['rounding']['credit_cards']['amt'] += round(($summary['cashier_sales']['rounding']['by_type']['credit_cards']['amt']), 2);
		}
	 }
}

$COUNTER_COLLECTION_DETAILS_REPORT = new COUNTER_COLLECTION_DETAILS_REPORT('Counter Collection Detail Report');
?>
