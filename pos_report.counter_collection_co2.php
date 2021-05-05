<?php
/*
10/26/2012 6:18 PM Andy
- Add to show Cash from membership counter.

11/3/2012 11:20 AM Justin
- Enhanced to use payment type from POS Settings as if found it is being set.

12/12/2012 4:16 PM Justin
- Enhanced to include extra payment type from config.

12/18/2012 12:16 PM Justin
- Bug fixed on system remain picking out mix and match payment type.

12/20/2012 1:22 PM Andy
- Fix payment type wrongly count 2 time credit card.

1/3/2013 6:17 PM Justin
- Fix payment type wrongly count 2 times of Cash (Membership Counter).

1/25/2017 4:00 PM Andy
- Fix if payment type is credit_cards will cause system unable to calculate total variance.

4/13/2017 13:33 Qiu Ying
- Bug fixed on Counter Collection Payment Type Missing

2/24/2020 5:43 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
$maintenance->check(175);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!$config['counter_collection_enable_co2_module']) js_redirect($LANG['NEED_CONFIG'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
include_once('counter_collection.include.php');

class COUNTER_COLLECTION_CO2 extends Module{
	var $date = '';
	var $branch_id = 0;
	var $got_mm_discount = false;
	
	function __construct($title, $template=''){
		global $con, $sessioninfo, $pos_config, $smarty, $config, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		if(!isset($_REQUEST['date']))	$_REQUEST['date'] = date("Y-m-d");	// default today
		else	$this->date = $_REQUEST['date'];
		
		$this->branch_id = mi($sessioninfo['branch_id']);
		
		// get currency type and assign it into pos_config
		$con_multi->sql_query("select * from pos_settings where branch_id=$this->branch_id and setting_name='currency'");
		$r = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
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
		}
		
		//print_r($pos_config);
		
		// load payment type from pos settings
		$q1 = $con_multi->sql_query("select * from pos_settings where setting_name = 'payment_type' and branch_id = ".mi($this->branch_id));
		
		$ps_info = $con_multi->sql_fetchrow($q1);
		$ps_payment_type = unserialize($ps_info['setting_value']);
		$con_multi->sql_freeresult($q1);

		if($ps_payment_type){
			$this->payment_type = array();
			foreach($ps_payment_type as $ptype=>$val){
				if(!$val) continue;
				if($ptype=='credit_card'){
					$ptype = "credit_cards";
				}
				//else{
				//	$ptype = str_replace("_", " ",$ptype);
				//}
				
				if(in_array($ptype, $this->payment_type))  continue;
				$this->payment_type[] = $ptype;
			}
		}
		
		if($config['counter_collection_extra_payment_type']){
			foreach($config['counter_collection_extra_payment_type'] as $ptype){
				$ori_type = $ptype;
				$ptype = strtolower($ptype);
				if(in_array($ptype, $this->payment_type)){
					$this->extra_payment_type[] = $ptype;
				}
				$this->all_extra_payment_type[$ptype] = $ori_type;
			}
		}
		
		if(!$this->payment_type){
			$this->payment_type = array(0=>'cash', 1=>'check', 2=>'credit_card', 3=>'debit', 4=>'voucher', 5=>'coupon', 6=>'others');
		}
		
		if(!in_array("cash", $this->payment_type)) $this->payment_type[] = "cash";
		if(!in_array("others", $this->payment_type)) $this->payment_type[] = "others";
		
		$smarty->assign("pos_config",$pos_config);
		
		parent::__construct($title, $template);
	}
	
	function _default(){
		if($_REQUEST['load_report']){
			$this->load_report();
		}
		$this->display();
	}
	
	private function load_report(){
		global $con, $smarty, $pos_config, $config, $mm_discount_col_value, $con_multi;
		
		$this->data = array();
		
		// check finalized
	 	$this->data['finalized'] = is_cc_finalized($this->branch_id, $this->date);
	 	
	 	// select all pos for today
	 	$filter = "p.branch_id=$this->branch_id and p.date=".ms($this->date)." and p.cancel_status=0";
	 	
	 	$sql = "select p.* from pos p where $filter order by counter_id, pos_time";
	 	
	 	$q_pos = $con_multi->sql_query($sql);
	 	//print_r($this->payment_type);
	 	//print "<br>";
	 	while($pos = $con_multi->sql_fetchrow($q_pos)){	// loop for each pos
		    $pos_id = mi($pos['id']);
		    $counter_id = mi($pos['counter_id']);
		    
		    // get pos payment
		    $sql = "select pp.*
			from pos_payment pp
			where pp.branch_id=".mi($this->branch_id)." and pp.date=".ms($this->date)." and pp.counter_id=$counter_id and pp.pos_id=$pos_id and pp.adjust=0";
			$q_pp = $con_multi->sql_query($sql);
			
			while($pp = $con_multi->sql_fetchassoc($q_pp)){
				$is_changed = mi($pp['changed']);
			    $is_adjust = mi($pp['adjust']);
			    
			    $row_type = 'cashier_sales';
				$is_foreign_currency = false;
				$is_receipt_discount = false;
				unset($old_amt, $old_currency_amt, $currency_amt, $currency_rate);
				
				if($is_adjust)	continue;	// no need look fo those alrdy has been adj
					
				$rm_amt = 0;
				
				// check is credit card
				if (in_array($pp['type'], $pos_config['credit_card']) || strtolower($pp['type'])=='credit cards') $payment_type = 'credit_cards';
		        else	$payment_type = $pp['type'];
		        
		        // check payment type
		        $payment_type = strtolower($payment_type);
		        		        
	            switch ($payment_type){
                	case $mm_discount_col_value:
                		//$payment_type = 'Discount';	// store together with receipt discount
                		$this->got_mm_discount = true;
					case 'discount':    // it is discount
					    $rm_amt = $pp['amount']*-1;  // discount show as negative
					    $is_receipt_discount = true;
					    break;
					case 'cash':    // it is cash payment
					    //if(!$is_adjust){
							$rm_amt = $pp['amount'] - $pos['amount_change'];    // amount minus changed
						//}else{
						//	$old_amt = $rm_amt = $pp['amount'];
							//$row_type = 'adj_out';
						//}	
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
				
				if($this->all_extra_payment_type && in_array($payment_type, array_keys($this->all_extra_payment_type))){
					if(!$this->extra_payment_type || !in_array($payment_type, $this->extra_payment_type)){
						$this->extra_payment_type[] = $payment_type;
					}
					if(!in_array($payment_type, $this->payment_type)){
						$this->payment_type[] = $payment_type;
					}
				}elseif(!in_array($payment_type, $this->payment_type) && $payment_type != "rounding" && $payment_type != "discount" && $payment_type != strtolower($mm_discount_col_value)){
					$this->payment_type[] = $payment_type;
				}
				
				if(!isset($old_amt))	$old_amt = $rm_amt;
				
				if($is_foreign_currency){
					// create foreign currency array					
					$this->check_and_create_foreign_currency_list($payment_type, $currency_rate);
				}
								
				//$this->data['data']['by_counter'][$counter_id][$row_type][$payment_type]['amt'] += $rm_amt;
				$this->data['data']['all_counter'][$row_type][$payment_type]['amt'] += $rm_amt;
				
				// currency
				if($is_foreign_currency){
					//$this->data['data']['by_counter'][$counter_id][$row_type][$payment_type]['currency_amt'] += $currency_amt;
					$this->data['data']['all_counter'][$counter_id][$row_type][$payment_type]['currency_amt'] += $currency_amt;
				}
			}
			$con_multi->sql_freeresult($q_pp);
		}
		$con_multi->sql_freeresult($q_pos);
		
		$this->load_form_data();
		
		if($config['counter_collection_show_membership_receipt']){
			$this->load_membership_receipt_info();
		}
		//print_r($this->data);
		//print_r($this->payment_type);
		// add on for all payment type with "_amt"
		foreach($this->payment_type as $r=>$type){
			$type = strtolower($type);
			if($type == "credit card" || $type == "credit_cards") $this->payment_type[$r] = "credit_card_amt";
			else $this->payment_type[$r] = $type."_amt";
		}
		
		// loop from data for different payment type as if found user got set data before
		if($this->data['form']){
			foreach($this->data['form'] as $f=>$data){
				if($data > 0 && preg_match("/_amt$/", $f) && !in_array($f, $this->payment_type)){
					$this->payment_type[] = $f;
				}
			}
		}

		if($config['counter_collection_show_membership_receipt'] && !in_array("mem_cash_amt", $this->payment_type)) $this->payment_type[] = "mem_cash_amt";
		asort($this->payment_type);
		//print_r($this->payment_type);
		$smarty->assign('all_extra_payment_type', $this->all_extra_payment_type);
		$smarty->assign('payment_type', $this->payment_type);
		if($this->extra_payment_type)	asort($this->extra_payment_type);
		$smarty->assign('extra_payment_type', $this->extra_payment_type);
		$smarty->assign('data', $this->data);
	}
	
	private function check_and_create_foreign_currency_list($payment_type, $currency_rate){
	 	
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
	 }
	 
	 private function load_form_data(){
	 	global $con, $smarty, $sessioninfo, $config, $con_multi;
	 	
	 	$bid = $this->branch_id;
	 	$date = $this->date;
	 	
	 	$con_multi->sql_query("select * from co2 where branch_id=$bid and date=".ms($date));
	 	$this->data['form'] = $con_multi->sql_fetchassoc();
		$this->data['form']['extra'] = unserialize($this->data['form']['extra']);
		
		if($this->data['form']['extra']){
			foreach($this->data['form']['extra'] as $field=>$dt){
				$field = str_replace("_amt", "", $field);
				if(!$this->extra_payment_type ||!in_array($field, $this->extra_payment_type)){
					$this->extra_payment_type[] = $field;
				}
				if(!in_array($field, $this->payment_type)){
					$this->payment_type[] = $field;
				}
			}
		}

	 	$con_multi->sql_freeresult();
	 	
	 	$this->data['item_list'] = array();
	 	$con_multi->sql_query("select * from co2_items where branch_id=$bid and date=".ms($date)." order by id");
	 	while($r = $con_multi->sql_fetchassoc()){
	 		$this->data['item_list'][] = $r;
	 	}
	 	$con_multi->sql_freeresult();
	 }
	 
	 function save_form(){
	 	global $con, $smarty, $sessioninfo, $config, $LANG, $con_multi;
	 	
	 	$bid = $this->branch_id;
	 	$date = $this->date;
	 	
	 	$is_finalized = is_cc_finalized($bid, $date);
	 	
	 	if($is_finalized)	die($LANG['COUNTER_COLLECTION_FINALIZED_CANNOT_UPDATE']);
	 	
	 	$date_as_pos_list = $_REQUEST['date_as_pos'];
	 	$collection_no_list = $_REQUEST['collection_no'];
	 	$row_amt_list = $_REQUEST['row_amt'];
	 	
	 	$con_multi->sql_query("select * from co2 where branch_id=$bid and date=".ms($date));
	 	$form = $con_multi->sql_fetchassoc();
	 	$con_multi->sql_freeresult();
	 	
	 	$upd = array();
	 	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	 	$upd['cash_amt'] = $_REQUEST['cash_amt'];
	 	$upd['mem_cash_amt'] = $_REQUEST['mem_cash_amt'];
	 	$upd['check_amt'] = $_REQUEST['check_amt'];
	 	$upd['credit_card_amt'] = $_REQUEST['credit_card_amt'];
	 	$upd['debit_amt'] = $_REQUEST['debit_amt'];
	 	$upd['voucher_amt'] = $_REQUEST['voucher_amt'];
	 	$upd['coupon_amt'] = $_REQUEST['coupon_amt'];
	 	$upd['others_amt'] = $_REQUEST['others_amt'];
	 	$upd['total_variance'] = $_REQUEST['total_variance'];
		$upd['extra'] = serialize($_REQUEST['extra']);

	 	// co2
	 	if($form){
	 		$con->sql_query("update co2 set ".mysql_update_by_field($upd)." where branch_id=$bid and date=".ms($date));
	 	}else{
	 		$upd['branch_id'] = $bid;
	 		$upd['date'] = $date;
	 		$upd['user_id'] = $sessioninfo['id'];
	 		$upd['added'] = 'CURRENT_TIMESTAMP';
	 		
	 		$con->sql_query("insert into co2 ".mysql_insert_by_field($upd));
	 	}
	 	
	 	// co2_items
	 	$added = date("Y-m-d H:i:s");
	 	$item_id = 0;
	 	
	 	foreach($date_as_pos_list as $tmp_id => $date_as_pos){
	 		$date_as_pos = trim($date_as_pos);
	 		$collection_no = trim($collection_no_list[$tmp_id]);
	 		$row_amt = mf($row_amt_list[$tmp_id]);
	 		
	 		if(!$date_as_pos && !$collection_no && !$row_amt)	continue;
	 		
	 		$item_id++;
	 		
	 		$upd = array();
	 		$upd['branch_id'] = $bid;
	 		$upd['date'] = $date;
	 		$upd['id'] = $item_id;
	 		$upd['added'] = $added;
	 		$upd['date_as_pos'] = $date_as_pos;
	 		$upd['collection_no'] = $collection_no;
	 		$upd['row_amt'] = $row_amt;	 		
	 		
	 		$con->sql_query("replace into co2_items ".mysql_insert_by_field($upd));
	 	}
	 	
	 	// delete old items
	 	$delete_filter = array();
	 	$delete_filter[] = "branch_id=$bid and date=".ms($date);
	 	if($item_id)	$delete_filter[] = "id>$item_id";
	 	$delete_filter = "where ".join(' and ', $delete_filter);
	 	
	 	$con->sql_query("delete from co2_items $delete_filter");
	 	
	 	$ret = array();
	 	$ret['ok'] = 1;
	 	print json_encode($ret);
	 }
	 
	 private function load_membership_receipt_info(){
	 	global $con, $smarty, $config, $sessioninfo, $con_multi;
		
		$date = $_REQUEST['date'];
		
		$this->mem_data = array();
		$bid = mi($sessioninfo['branch_id']);
		
		
		// get system amt
		$q_mr = $con_multi->sql_query("select mr.* from membership_receipt mr where mr.branch_id=$bid and mr.timestamp between ".ms($date)." and ".ms($date.' 23:59:59')." order by mr.timestamp");

		while($r = $con_multi->sql_fetchassoc($q_mr)){
			$this->data['mem_data']['by_counter'][$r['counter_id']]['cash']['amt'] += round($r['amount'], 2);
			
			$this->data['mem_data']['all_counter']['cash']['amt'] += round($r['amount'], 2);
		}
		$con_multi->sql_freeresult($q_mr);
	 }
}

$COUNTER_COLLECTION_CO2 = new COUNTER_COLLECTION_CO2('Counter Collection CO2');
?>
