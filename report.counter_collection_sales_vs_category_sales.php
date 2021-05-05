<?php
/*
4/15/2010 3:57:13 PM Andy
- Status column changes, F = Finalized, NF = Not yet Finalize, ND = No Data

4/19/2010 12:35:51 PM Andy
- Add column to show finalize time

5/17/2010 12:17:55 PM Andy
- Fix incorrect total collection.

1/18/2011 6:36:41 PM Alex
- change use report_server

6/6/2011 3:48:38 PM Andy
- Change report load structure.

10/12/2011 5:30:00 PM Andy
- Add mix and match promotion at counter collection related module.

11/11/2011 4:26:19 PM Andy
- Add privilege checking on "Counter Collection Sales vs Category Sales".
- Improve report speed.

5/3/2012 3:25:58 PM Andy
- Change report structure to extend from class Module.
- Add "Top Up", "Over", "Receipt Discount", "Mix & Match Discount", "Cash Advance" and "Trade In Write-off"

8/15/2012 5:28 PM Andy
- Fix wrong sales and adjust amount if user adjust payment amount related to cash.
- Change query to get trade in data to only call 1 time instead of calling each of the POS.

8/29/2012 5:17 PM Andy
- Fix export not working bug.

11/2/2012 11:20 AM Justin
- Enhanced to use payment type from POS Settings as if found it is being set.

12/11/2012 4:13 PM Justin
- Enhanced to include extra payment type from both config and POS Settings.

12/19/2012 12:15 PM Andy
- Change the report should check the payment type with adjust=0 instead of changed=0.

1/3/2013 11:20 AM Justin
- Bug fixed on system missed to sum up amount from extra payment type.

8/26/2013 10:03 AM Andy
- Enhance the report calculation to include deposit refund.

1/14/2014 3:44 PM Andy
- Remove clear_drawer=1 checking for pos_cash_domination.

11/27/2014 5:10 PM Andy
- Enhance to show Service Charges, GST and Nett Sales 2.
- Added Variance 2 to compare with nett sales 2.
- Fix report does not load sales from payment type "Cash".

4/27/2017 3:38 PM Justin
- Enhanced to use cash_domination_notes from config instead of pos_config.

10/17/2017 11:06 AM Andy
- Fixed custom payment type unable to show, due to case sensitive issue. need to convert to like "Member Point".

6/28/2018 5:50 PM Justin
- Enhanced to load foreign currency list base on sales and config.

7/31/2018 5:34 PM Justin
- Modified to increase memory size due to some customers have memory limit issues.

3/15/2019 1:49 PM Andy
- Enhanced counter collection variances calculation to include ewallet sales and ewallet collection.

2/18/2020 3:13 PM Andy
- Fixed to only replace "_" to " " for credit card payment.

2/24/2020 5:07 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

4/24/2020 4:40 PM Justin
- Enhanced to increase the memory limit from 1024 to 2048.
*/
include("include/common.php");
set_time_limit(0);
$maintenance->check(209);
ini_set('memory_limit', '2048M');

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
require_once('counter_collection.include.php');

class CC_VS_CS extends Module{
	var $normal_payment_type = array();
	var $branches = array();
	var $months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
	var $got_mm_discount = false;
	var $got_top_up = false;
	var $got_trade_in_writeoff = false;
	var $got_service_charge = false;
	var $got_foreign_currency = false;
	var $got_gst = false;
	
	function __construct($title, $template=''){
		global $con, $smarty, $config, $pos_config, $sessioninfo, $appCore, $con_multi;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		if(BRANCH_CODE == 'HQ'){
			$this->branch_id = $_REQUEST['branch_id'] ? mi($_REQUEST['branch_id']) : mi($sessioninfo['branch_id']);
		}else{
			$this->branch_id = $sessioninfo['branch_id'];
		}

		// get currency type and assign it into pos_config
		/*$con->sql_query("select * from pos_settings where branch_id=$this->branch_id and setting_name='currency'");
		$r = $con->sql_fetchrow();
		$con->sql_freeresult();
		$currencies = $r['setting_value'];
		if ($currencies) $currencies = unserialize($currencies);

		if (is_array($currencies))
		{
			//$pos_config['payment_type'] = array_merge($pos_config['payment_type'], array_keys($currencies));
            $pos_config['currency'] = array_keys($currencies);
            $pos_config['curr_rate'] = $currencies;
            //print_r($pos_config);
            foreach($pos_config['curr_rate'] as $currency_type=>$currency_rate){
                $currency_rate = sprintf("%01.3f", $currency_rate);
                if(!$currency_rate) continue;

				$this->currency_data[$currency_type]['currency_rate'][$currency_rate]= array();
			}
		}*/
		
		// load payment type from pos settings
		$this->normal_payment_type = array();
		$q1 = $con_multi->sql_query("select * from pos_settings where setting_name = 'payment_type' and branch_id = ".mi($this->branch_id));
		
		$ps_info = $con_multi->sql_fetchrow($q1);
		$ps_payment_type = unserialize($ps_info['setting_value']);
		$con_multi->sql_freeresult($q1);

		if($ps_payment_type){
			foreach($ps_payment_type as $ptype=>$val){
				//if(!$val) continue;
				//$ptype = ucwords(str_replace("_", " ",$ptype));
				if(strpos(strtolower($ptype), "credit_card")===0){
					$ptype = str_replace("_", " ",$ptype);	// only replace "_" to " " if it is credit card
				}
				$ptype = ucwords($ptype);
				
				if(in_array($ptype, $this->normal_payment_type))  continue;
				$this->normal_payment_type[] = $ptype;
			}
		}

		if(!$this->normal_payment_type){
			foreach($pos_config['payment_type'] as $ptype){
				if($ptype=='Discount')  continue;
				$this->normal_payment_type[] = $ptype;
			}
		}
		
		// extend cash as default
		if(!in_array('Cash', $this->normal_payment_type))	$this->normal_payment_type[] = 'Cash';
		
		if($config['counter_collection_extra_payment_type']){
			foreach($config['counter_collection_extra_payment_type'] as $ptype){
				$ptype = ucwords($ptype);
				if(!in_array($ptype, $this->normal_payment_type)) $this->normal_payment_type[] = $ptype;
			}
		}
		
		//print_r($this->normal_payment_type);
		$pos_config['normal_payment_type'] = $this->normal_payment_type;
		$smarty->assign("pos_config",$pos_config);

		$smarty->assign("months", $this->months);
		
		parent::__construct($title, $template);
	}
	
	function _default(){
		global $con, $sessioninfo, $pos_config, $smarty;
		
		$this->init_load();
		
		if($_REQUEST['show_report']){
			if($_REQUEST['show_type']=='excel'){
			    include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title);

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
			$this->generate_report();
		}
		
		$this->display();
	}
	
	private function init_load(){
		global $con, $smarty, $sessioninfo, $con_multi;
		
		// branches
		$this->branches = array();
		$con_multi->sql_query("select * from branch where active=1 order by sequence, code");
		while($r = $con_multi->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
	
		$smarty->assign("branches", $this->branches);
		
		// year list
		$con_multi->sql_query("select year(min(date)) as min_year, year(max(date)) as max_year from pos where date>'2000-01-01'");

		while ($r = $con_multi->sql_fetchrow()){
            $min_year = $r['min_year'];
            $max_year = $r['max_year'];
		}
		$con_multi->sql_freeresult();
		
		$count_year = $max_year - $min_year;
		$years = array();
		for($i=0; $i<=$count_year; $i++){
			$years[$i][0] = $min_year+$i;
			$years[$i]['year'] = $min_year+$i;
		}
		
		$smarty->assign("years", $years);
		
		if(!isset($_REQUEST['year']))	$_REQUEST['year'] = date("Y");
		if(!isset($_REQUEST['month']))	$_REQUEST['month'] = date("m");
		if(!isset($_REQUEST['branch_id']))	$_REQUEST['branch_id'] = $sessioninfo['branch_id'];
	}
	
	private function generate_report(){
	    global $con, $pos_config, $mm_discount_col_value, $smarty, $sessioninfo, $config, $con_multi;
	    
	    // do my own form process

        $bid = $this->branch_id;
        $view_type = $_REQUEST['view_type'] == 'day' ? 'day' : 'month';
        $year = mi($_REQUEST['year']);
        
		if($view_type=='day'){
        	$month = mi($_REQUEST['month']);
        	$from_date = $year.'-'.$month.'-1';
        	$to_date = $year.'-'.$month.'-'.days_of_month($month, $year);
        	
        	$date_label = $this->generate_dates($from_date, $to_date, 'Ymd', 'Y-m-d');
        	if($date_label){
				foreach($date_label as $date_key=>$d){
					$this->date_label[$date_key]['date'] = $d;
					$this->date_label[$date_key]['day'] = date('w', strtotime($d));
				}
			}
		}else{
            $from_date = $year.'-01-01';
        	$to_date = $year.'-12-31';
        	for($i = 1; $i<=12; $i++){
        	    $date_key = $year.sprintf('%02d', $i);
                $this->date_label[$date_key]['date'] = $year.' '.$this->months[$i];
			}
		}
		
		//print_r($this->date_label);
        
        $bcode = get_branch_code($bid);
        $report_title[] = "Branch: ".$bcode;
        $report_title[] = "Year: ".$year;
        if($view_type=='day')	$report_title[] = "Month: ".$this->months[$month];
        $report_title[] = "View By: ".ucwords($view_type);

        $date_got_sales = $this->table = $this->total = $this->data = array();
		
		// counter collection
		//$con_multi= new mysql_multi();
		//$con_multi = $con;
		/*if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/
		
		$p_filter = "p.branch_id=$bid and p.date between ".ms($from_date)." and ".ms($to_date);
		
		// get sales
		$sql = "select p.* from pos p where $p_filter and p.cancel_status=0 order by date,counter_id,id";
		//print $sql;
		$q_p = $con_multi->sql_query($sql);
		$total_p = $con_multi->sql_numrows($q_p);
		$current_count = 0;
		
		while($pos = $con_multi->sql_fetchassoc($q_p)){
			$current_count++;
			$pos_id = mi($pos['id']);
			$p_bid = mi($pos['branch_id']);
			$p_date = $pos['date'];
			$p_counter_id = mi($pos['counter_id']);
			$date_key = $view_type=='day' ? date("Ymd", strtotime($p_date)) : date("Ym", strtotime($p_date));
			
			
			if(!in_array($p_date, $date_got_sales))  $date_got_sales[] = $p_date;
			
			// get payment amount from pos payment
			$fc_deduct_change = false;
		    $q_pp = $con_multi->sql_query("select type, remark, changed, adjust, amount from pos_payment pp where pp.branch_id=$p_bid and pp.date=".ms($p_date)." and pp.counter_id=$p_counter_id and pp.pos_id=$pos_id and pp.adjust=0");
		    while($pp = $con_multi->sql_fetchassoc($q_pp)){
				$is_adjust = mi($pp['adjust']);
				$row_type = 'cashier_sales';
				//$adj_amt = 0;
				
				// is one of the credit cards
	            if (in_array($pp['type'], $pos_config['credit_card'])) $payment_type = 'Credit Cards';
	            else	$payment_type = $pp['type'];

				if(preg_match('/^ewallet_/i', $payment_type)){	// eWallet don uppercase
					$payment_type = strtolower($payment_type);
				}else{
					$payment_type = ucwords(strtolower($payment_type));
				}
                $is_foreign_currency = false;
                
                //if($is_adjust || $is_changed)  $adj_amt = $pp['amount'];
                
                // check payment type
                switch ($payment_type){
                	case $mm_discount_col_value:
                		//$payment_type = 'Discount';	// store together with receipt discount
                		$this->got_mm_discount = true;
					case 'Discount':    // it is discount
					    $rm_amt = $pp['amount']*-1;  // discount show as negative
					    break;
					case 'Cash':    // it is cash payment
					    //if($is_adjust)  $rm_amt = $pp['amount'];
						$rm_amt = $pp['amount'] - $pos['amount_change'];    // amount decrease changed
					    break;
					/*case 'Mix & Match Total Disc':
						$rm_amt = $pp['amount']*-1;
						$add_to_nett_sales = false;
					*/
					case 'Deposit':	// pay by deposit
						if($pos['amount_change']){	// got amt changed
							// check whether this deposit got pay by cash
							$p_p = $con_multi->sql_query("select count(*) as ttl_count from pos_payment pp where pp.branch_id=$p_bid and pp.date=".ms($p_date)." and pp.counter_id=$p_counter_id and pp.pos_id=$pos_id and pp.adjust=0 and type='Cash'");
							$pp_info = $con_multi->sql_fetchassoc($p_p);
							$con_multi->sql_freeresult($p_p);
							$deposit_got_cash = mi($pp_info['ttl_count']);
						
							if(!$deposit_got_cash){	// this deposit dun hv cash but got change
								$payment_type = 'Cash';	// change payment type to cash
								$rm_amt = $pos['amount_change']*-1;	// make refund count into cash sales
								
								//$this->data[$counter_id][$dom_id]['deposit_refund_amt']+=$pos['amount_change'];
							}
						}
						break;
					default:
					    // check is foreign currency
			            $currency_arr = pp_is_currency($pp['remark'], $pp['amount']);
			            if($currency_arr['is_currency']){   // it is foreign currency
							$currency_amt = $currency_arr['currency_amt'];
							$currency_rate = $currency_arr['currency_rate'];
							$is_foreign_currency = true;
							$payment_type = strtoupper($payment_type);
							
							// check whether this transaction got pay by cash
							if($pos['amount_change'] && !$fc_deduct_change){
								$p_p = $con_multi->sql_query("select count(*) as ttl_count from pos_payment pp where pp.branch_id=$p_bid and pp.date=".ms($p_date)." and pp.counter_id=$p_counter_id and pp.pos_id=$pos_id and pp.adjust=0 and type='Cash'");
								$pp_info = $con_multi->sql_fetchassoc($p_p);
								$con_multi->sql_freeresult($p_p);
								$trans_got_cash = mi($pp_info['ttl_count']);
								
								if(!$trans_got_cash){ // this transaction doesn't cash but got change
									$currency_arr['rm_amt'] -= $pos['amount_change'];
								}
								
								$fc_deduct_change = true;
							}
							// store foreign currency data by date for variance calculation
							$this->currency_data[$p_date][$payment_type]['currency_rate'] = $currency_rate;
							$this->currency_data[$p_date][$payment_type][$row_type]['foreign_amt'] += $currency_amt;
							$this->got_foreign_currency = true;
						}
						$rm_amt = round($currency_arr['rm_amt'], 2);
						unset($currency_arr);
					    break;
				}
				
				if($is_foreign_currency) $this->check_and_create_foreign_currency_list($payment_type, $currency_rate);
				
				$this->data[$date_key][$row_type][$payment_type]['amt'] += $rm_amt;
				
				if(!$is_foreign_currency){
						if($payment_type && $payment_type != $mm_discount_col_value && $payment_type != "Discount" && !in_array($payment_type, $this->normal_payment_type) && (in_array($payment_type, $pos_config['payment_type']) || preg_match('/^ewallet_/i', $payment_type))){
						$this->normal_payment_type[] = $payment_type;
					}
				}				
			}
			$con_multi->sql_freeresult($q_pp);
		    
		    
		    // over
		    $pos_amt = $pos['amount'];
			$real_receipt_amt = $pos['amount_tender'] - $pos['amount_change'] - $pos['service_charges'];
			$over_amt = mf($real_receipt_amt-$pos_amt);
			if($over_amt){
                $this->data[$date_key]['cashier_sales']['Over']['amt'] += round($over_amt,2);
                //$this->total[$counter_id]['cashier_sales']['over']['amt'] += $over_amt;
			}
			
			// trade in (write=off)
			/*$q_ti = $con_multi->sql_query("select pi.qty,pi.price,pi.writeoff_by
			from pos_items pi
			where pi.branch_id=$p_bid and pi.date=".ms($p_date)." and pi.counter_id=$p_counter_id and pi.pos_id=$pos_id and pi.trade_in_by>0 and pi.writeoff_by>0");
			while($pi_ti = $con_multi->sql_fetchassoc($q_ti)){
				$this->got_trade_in_writeoff = true;
				$this->data[$date_key]['trade_in']['writeoff_amt'] += $pi_ti['price'];
			}
			$con_multi->sql_freeresult($q_ti);*/
			
			/*if($sessioninfo['u']=='wsatp'){
				file_put_contents('tmp_per.txt', "$current_count/$total_p = ".round($current_count/$total_p*100,2)."%\n");
			}*/
			
			// service charge
			if($pos['service_charges']){
				$this->got_service_charge = true;
				$this->data[$date_key]['cashier_sales']['service_charges']['amt'] += round($pos['service_charges']-$pos['service_charges_gst_amt'],2);
			}
			
			// gst
			if($pos['total_gst_amt']){
				$this->got_gst = true;
				$this->data[$date_key]['cashier_sales']['total_gst_amt']['amt'] += round($pos['total_gst_amt'],2);
			}
		}
		$con_multi->sql_freeresult($q_p);
		
		// pos items
		$q_pi = $con_multi->sql_query("select pi.*
		from pos_items pi
		join pos p on p.branch_id=pi.branch_id and p.counter_id=pi.counter_id and p.date=pi.date and p.id=pi.pos_id
		where $p_filter and p.cancel_status=0");
		while($pi = $con_multi->sql_fetchassoc($q_pi)){
			$date_key = $view_type=='day' ? date("Ymd", strtotime($pi['date'])) : date("Ym", strtotime($pi['date']));
			
			// item price
			$this->data[$date_key]['items']['total_amt'] += $pi['price']-$pi['tax_amount'];
			
			// item discount
			$this->data[$date_key]['items']['total_discount'] += $pi['discount']*-1;
		}
		$con_multi->sql_freeresult($q_pi);
		
		// trade in
		$q_ti = $con_multi->sql_query("select pi.qty,pi.price,pi.writeoff_by,pi.date
		from pos_items pi
		join pos p on p.branch_id=pi.branch_id and p.date=pi.date and p.counter_id=pi.counter_id and p.id=pi.pos_id
		where $p_filter and p.cancel_status=0 and pi.trade_in_by>0 and pi.writeoff_by>0");
		while($pi_ti = $con_multi->sql_fetchassoc($q_ti)){
			$date_key = $view_type=='day' ? date("Ymd", strtotime($pi_ti['date'])) : date("Ym", strtotime($pi_ti['date']));
			
			$this->got_trade_in_writeoff = true;
			$this->data[$date_key]['trade_in']['writeoff_amt'] += $pi_ti['price'];
		}
		$con_multi->sql_freeresult($q_ti);
		
		// sales cache
		$tbl = "sku_items_sales_cache_b".$bid;
        $sql = "select s.date,sum(s.amount) as sales
from $tbl s
where s.date between ".ms($from_date)." and ".ms($to_date)."
group by s.date";
		//print $sql;
		$q_cs = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q_cs)){
		    if($view_type=='day')	$date_key = date("Ymd", strtotime($r['date']));
		    else    $date_key = date("Ym", strtotime($r['date']));
		    
		    if(!in_array($r['date'], $date_got_sales))  $date_got_sales[] = $r['date'];
		    
			$this->table[$date_key]['sales_cache'] += $r['sales'];
		}
		$con_multi->sql_freeresult($q_cs);
		
		// cash advance
	    $q_pch = $con_multi->sql_query("select * from pos_cash_history pch where pch.branch_id=$bid and pch.date between ".ms($from_date)." and ".ms($to_date));
	    while($pch = $con_multi->sql_fetchassoc($q_pch)){
	    	$date_key = $view_type=='day' ? date("Ymd", strtotime($pch['date'])) : date("Ym", strtotime($pch['date']));
	        $currency_arr = pch_is_currency($pch['remark'], $pch['amount']);
	        $rm_amt = $currency_arr['rm_amt'];

			switch($pch['type']){
				case 'ADVANCE':
					$type = 'cash_advance';
					break;
				case 'TOP_UP':
					$type = 'top_up';
					$this->got_top_up = true;
					break;
				default:
					continue 2;		
			}
			$this->data[$date_key][$type]['Cash']['amt'] += $rm_amt;
		}
		$con_multi->sql_freeresult($q_pch);
		
		$pos_cash_domination_notes = $config['cash_domination_notes'];
		// get pos cash domination list for "counter collection"
        $sql = "select * from pos_cash_domination pcd where branch_id=$bid and date between ".ms($from_date)." and ".ms($to_date);
        //print $sql;
        $q_dom = $con_multi->sql_query($sql);
		
        while($pcd = $con_multi->sql_fetchassoc($q_dom)){
            $pcd['data'] = unserialize($pcd['data']);
            $pcd['odata'] = unserialize($pcd['odata']);
            $pcd['curr_rate'] = unserialize($pcd['curr_rate']);
            $pcd['ocurr_rate'] = unserialize($pcd['ocurr_rate']);
            
            if(!$pcd['data']) continue;

			$date_key = $view_type=='day' ? date("Ymd", strtotime($pcd['date'])) : date("Ym", strtotime($pcd['date']));
			
			// latest data
			if($pcd['data']){
                if($pcd['curr_rate']){
					$curr_rate = $pcd['curr_rate'];  // use cash domination currency rate
				}
				else{
					$curr_rate = $this->foreign_currency_list;  // use default currency rate
				}

				foreach($pcd['data'] as $type=>$d2){
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
					
					if($config['foreign_currency'][$type_key] && !isset($this->foreign_currency_list[$type_key])){
						$this->check_and_create_foreign_currency_list($type_key, $curr_rate[$type_key]);
					}
					
					if(isset($this->foreign_currency_list[$type_key])){  // is currency collection
						// store currency rate by date as if doesnt found
						if(!isset($this->currency_data[$pcd['date']][$type_key]['currency_rate'])){
							$this->currency_data[$pcd['date']][$type_key]['currency_rate'] = $curr_rate[$type_key];
						}
						// store foreign currency data by date for variance calculation
						$this->currency_data[$pcd['date']][$type_key]['cash_domination']['foreign_amt'] += $d2;
						// for display purpose
						$this->data[$date_key]['cash_domination'][$type_key]['foreign_amt'] += $d2;
						//$rm_amt = $curr_rate[$type_key] ? mf($d2/$curr_rate[$type_key]) : 0;
						$rm_amt = 0;
						$this->got_foreign_currency = true;
					}else{
						$rm_amt = $d2;
					}
					$this->data[$date_key]['cash_domination'][$type_key]['amt'] += $rm_amt;
					

                    if($type=='Float'){ // is cash float
                        $this->data[$date_key]['cash_domination']['Float']['amt'] += abs($rm_amt);
					}
					
					$payment_type = ucwords($type_key);
					if(!in_array($payment_type, $this->normal_payment_type) && (in_array($payment_type, $pos_config['payment_type']) || preg_match('/^ewallet_/i', $payment_type))){
						$this->normal_payment_type[] = $payment_type;
					}
				}
			}
		}
		$con_multi->sql_freeresult($q_dom);
				
		if($sessioninfo['u'] == 'admin' || $sessioninfo['level'] >=9999){
			//print_r($this->normal_payment_type);
			//print_r($pos_config['currency']);
		}
		// construct cc_sales, cc_actual_sales, rounding, collection, cc_variances
		if($this->data){
			foreach($this->data as $date_key=>$r){
				// cashier sales => cc_sales
				$sales_amt = $ttl_fc_amt = 0;
				foreach($this->normal_payment_type as $ptype){
					//print "$ptype = ".$r['cashier_sales'][$ptype]['amt']."<br>";
					$sales_amt += $r['cashier_sales'][$ptype]['amt'];
				}
				
				// deposit
				//if($r['cashier_sales']['Deposit']['amt']){
				//	$sales_amt += $r['cashier_sales']['Deposit']['amt'];
				//}
				
				//$sales_amt = $r['cashier_sales']['Cash']['amt']+$r['cashier_sales']['Credit Cards']['amt']+$r['cashier_sales']['Coupon']['amt']+$r['cashier_sales']['Voucher']['amt']+$r['cashier_sales']['Check']['amt'];
				
				// currency
				if($this->foreign_currency_list){
					foreach($this->foreign_currency_list as $fc_code=>$global_curr_rate){
						$sales_amt += $r['cashier_sales'][$fc_code]['amt'];
						$ttl_fc_amt += $r['cashier_sales'][$fc_code]['amt'];
					}
				}
				
				// item amount
				$this->table[$date_key]['item_amt'] += $r['items']['total_amt'];
				// item discount
				$this->table[$date_key]['item_discount'] += $r['items']['total_discount'];
				
			    $this->table[$date_key]['cc_sales'] += $sales_amt;
			    $this->table[$date_key]['cc_actual_sales'] += $sales_amt;
			    
			    // get back gross sales
			    $this->table[$date_key]['cc_sales'] -= $r['cashier_sales']['Rounding']['amt'];
			    $this->table[$date_key]['cc_sales'] -= $r['cashier_sales']['Over']['amt'];
			    $this->table[$date_key]['cc_sales'] -= $r['cashier_sales'][$mm_discount_col_value]['amt'];
			    $this->table[$date_key]['cc_sales'] -= $r['cashier_sales']['Discount']['amt'];
			    
			    // over
			    $this->table[$date_key]['over'] += $r['cashier_sales']['Over']['amt'];
			    
			    // rounding
			    $this->table[$date_key]['rounding'] += $r['cashier_sales']['Rounding']['amt'];
			    
			    // mix match discount
			    $this->table[$date_key]['mix_match_discount'] += $r['cashier_sales'][$mm_discount_col_value]['amt'];
			    
			    // receipt discount
			    $this->table[$date_key]['discount'] += $r['cashier_sales']['Discount']['amt'];
			    
				// service charge
				if($this->got_service_charge){
					$this->table[$date_key]['service_charges'] += $r['cashier_sales']['service_charges']['amt'];
					$this->table[$date_key]['cc_sales'] -= $r['cashier_sales']['service_charges']['amt'];
				}
				
				// gst
				if($this->got_gst){
					$this->table[$date_key]['total_gst_amt'] += $r['cashier_sales']['total_gst_amt']['amt'];
					$this->table[$date_key]['cc_sales'] -= $r['cashier_sales']['total_gst_amt']['amt'];
				}
				
			    // collection
			    if($r['cash_domination']){
					foreach($r['cash_domination'] as $payment_type=>$cd){
						if(!in_array($payment_type, $this->normal_payment_type) && !$this->foreign_currency_list[$payment_type]){					
							continue;
						}
						
						if($this->foreign_currency_list[$payment_type]){
							$this->table[$date_key]['fc_collection'][$payment_type] += $cd['foreign_amt'];
						}else $this->table[$date_key]['collection'] += $cd['amt'];
					}
				}
				
				
				// cash advance
			    $this->table[$date_key]['cash_advance'] += $r['cash_advance']['Cash']['amt'];
			    
			    // top up
			    $this->table[$date_key]['top_up'] += $r['top_up']['Cash']['amt'];
			    
			    // write-off
			    $this->table[$date_key]['writeoff_amt'] += $r['trade_in']['writeoff_amt'];
			    
				// if found having foreign currency, need to capture the sales exclude foreign amount
				if($this->foreign_currency_list){
					$this->table[$date_key]['cc_npt_sales'] += $ttl_fc_amt;
				}
				
				// currency adjust
				$this->table[$date_key]['currency_adjust'] += $r['cashier_sales']['Currency_adjust']['amt'];
			}

			// foreign currency conversion for variance calculation
			if($this->currency_data){
				foreach($this->currency_data as $tmp_date=>$curr_list){
					foreach($curr_list as $curr_code=>$curr_info){
						$foreign_cs = $curr_info['cashier_sales']['foreign_amt'];
						$foreign_cd = $curr_info['cash_domination']['foreign_amt'];
						
						$fc_variance = ($foreign_cs - $foreign_cd);
						$rm_amt = round($fc_variance / $curr_info['currency_rate'], 2);
						
						$date_key = $view_type=='day' ? date("Ymd", strtotime($tmp_date)) : date("Ym", strtotime($tmp_date));
						
						$this->table[$date_key]['fc_variances'] += $rm_amt;
					}
				}
			}
			
			// finalize status
			$sql = "select date, finalized, finalize_timestamp from pos_finalized where branch_id=$bid and date between ".ms($from_date)." and ".ms($to_date);
			$q_f = $con_multi->sql_query($sql);
			while($f = $con_multi->sql_fetchassoc($q_f)){
				$date_key = $view_type=='day' ? date("Ymd", strtotime($f['date'])) : date("Ym", strtotime($f['date']));
				
				if($view_type=='day'){	
					if($f['finalized']){
						$this->table[$date_key]['status'] = 'F';
						$this->table[$date_key]['finalize_time'] = $f['finalize_timestamp'];
					}					
				}else{
					if($f['finalized']) $this->table[$date_key]['status']['f']++;
				}
			}
			$con_multi->sql_freeresult($q_f);
			
			// check no finalize and no data
			foreach($this->table as $date_key => $r){
				if($view_type=='day'){
					if($r['status'] != 'F'){
						$this->table[$date_key]['status'] = 'NF';
					}
				}else{
					$y = mi(substr($date_key, 0, 4));
					$m = mi(substr($date_key, 4, 2));
					
					$temp_from = $y."-".$m."-1";
					$temp_to = $y."-".$m."-".days_of_month($m, $y);
					$temp_date_arr = $this->generate_dates($temp_from, $temp_to, 'Ymd', 'Y-m-d');
					
					$total_day_got_sales = 0;
					foreach($temp_date_arr as $date){
						if(in_array($date, $date_got_sales)){
							$total_day_got_sales ++;
						}else{
							$this->table[$date_key]['status']['nd']++;
						}
					}
					
					$this->table[$date_key]['status']['nf'] = $total_day_got_sales - $r['status']['f'];
				}
			}		
			
			//$con_multi->sql_freeresult($q_f);
		}
		
		if($this->table){
		    //print_r($date_got_sales);
			foreach($this->table as $date_key=>$r){
				// cc_variances
				$this->table[$date_key]['cc_variances'] += round($r['collection'] - $r['top_up'] - $r['cash_advance'], 2);
				if($this->foreign_currency_list && is_array($this->foreign_currency_list)){
					$cc_actual_sales = $r['cc_actual_sales'] - $r['cc_npt_sales'];
					$this->table[$date_key]['cc_variances'] = round($this->table[$date_key]['cc_variances'] - $cc_actual_sales, 2);
				}else $this->table[$date_key]['cc_variances'] -= round($r['cc_actual_sales'], 2);

			    if($this->table[$date_key]['fc_variances']) $this->table[$date_key]['cc_variances'] -= $this->table[$date_key]['fc_variances'];
			    
				// variance
				$this->table[$date_key]['variances'] += round($r['cc_sales'] - $r['sales_cache'], 2);
				
				// item amt
				$this->total['item_amt'] += $r['item_amt'];
				// item discount
				$this->total['item_discount'] += $r['item_discount'];
				
				$this->total['cc_sales'] += $r['cc_sales'];
				$this->total['rounding'] += $r['rounding'];
				$this->total['over'] += $r['over'];
				$this->total['mix_match_discount'] += $r['mix_match_discount'];
				$this->total['discount'] += $r['discount'];
				$this->total['currency_adjust'] += $r['currency_adjust'];
				
				// service charge
				if($this->got_service_charge)	$this->total['service_charges'] += $r['service_charges'];
				// gst
				if($this->got_gst)	$this->total['total_gst_amt'] += $r['total_gst_amt'];
				
				$this->total['sales_cache'] += $r['sales_cache'];
				$this->total['collection'] += $r['collection'];
				if($this->foreign_currency_list && is_array($this->foreign_currency_list)){
					foreach($this->foreign_currency_list as $curr_code=>$curr_rate){
						$this->total['fc_collection'][$curr_code] += $r['fc_collection'][$curr_code];
					}
				}
				
				$this->total['cash_advance'] += $r['cash_advance'];
				$this->total['top_up'] += $r['top_up'];
				
				$this->total['cc_actual_sales'] += $r['cc_actual_sales'];
				$this->total['writeoff_amt'] += $r['writeoff_amt'];
				
				
				// nett sales 2
				$this->table[$date_key]['nett_sales2'] = $r['cc_actual_sales'] - $r['service_charges'] - $r['total_gst_amt'] - $r['rounding'] + $r['currency_adjust'];
				
				$this->total['nett_sales2'] += $this->table[$date_key]['nett_sales2'];
				
				$this->table[$date_key]['variances2'] = round($this->table[$date_key]['nett_sales2'] - $r['sales_cache'], 2);
				
				// total cc variance
				//$this->total['cc_variances'] = round($this->total['collection'] - $this->total['top_up'] - $this->total['cash_advance'] -$this->total['cc_actual_sales'], 2);
				$this->total['cc_variances'] += $this->table[$date_key]['cc_variances'];

				// total variance
				$this->total['variances'] += $this->table[$date_key]['variances'];
				
				// total variance 2
				$this->total['variances2'] += $this->table[$date_key]['variances2'];
			}
		}
		
		if($sessioninfo['u'] == 'admin' || $sessioninfo['level'] >=9999){
			//print_r($this->data);
		}
		
		//print_r($this->data);
		//print_r($this->date_label);
		//$con_multi->close_connection();

  		$smarty->assign('table', $this->table);
  		$smarty->assign('total', $this->total);
  		$smarty->assign('got_mm_discount', $this->got_mm_discount);
  		$smarty->assign('got_top_up', $this->got_top_up);
  		$smarty->assign('got_trade_in_writeoff', $this->got_trade_in_writeoff);
  		$smarty->assign('got_service_charge', $this->got_service_charge);
  		$smarty->assign('got_gst', $this->got_gst);
  		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
        $smarty->assign('date_label', $this->date_label);
        $smarty->assign('got_foreign_currency', $this->got_foreign_currency);
        $smarty->assign('foreign_currency_list', $this->foreign_currency_list);
	}
	
	function init_form(){
	    global $con, $smarty, $con_multi;
	    
		// do my own form process

        $this->bid  = get_request_branch();
        
        $this->view_type = $_REQUEST['view_type'] == 'day' ? 'day' : 'month';
        $this->year = mi($_REQUEST['year']);
        
		if($this->view_type=='day'){
        	$this->month = mi($_REQUEST['month']);
        	$this->from_date = $this->year.'-'.$this->month.'-1';
        	$this->to_date = $this->year.'-'.$this->month.'-'.days_of_month($this->month, $this->year);
        	$date_label = $this->generate_dates($this->from_date, $this->to_date, 'Ymd', 'Y-m-d');
        	if($date_label){
				foreach($date_label as $date_key=>$d){
					$this->date_label[$date_key]['date'] = $d;
					$this->date_label[$date_key]['day'] = date('w', strtotime($d));
				}
			}
		}else{
            $this->from_date = $this->year.'-01-01';
        	$this->to_date = $this->year.'-12-31';
        	for($i = 1; $i<=12; $i++){
        	    $date_key = $this->year.sprintf('%02d', $i);
                $this->date_label[$date_key]['date'] = $this->year.' '.$this->months[$i];
			}
		}
        
        $con_multi->sql_query("select code from branch where id=".mi($this->bid));
        $report_title[] = "Branch: ".$con_multi->sql_fetchfield(0);
		$con_multi->sql_freeresult();
        $report_title[] = "Year: ".$this->year;
        if($this->view_type=='day')	$report_title[] = "Month: ".$this->months[$this->month];
        $report_title[] = "View By: ".ucwords($this->view_type);
        
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
        //print_r($this->date_label);
        $smarty->assign('date_label', $this->date_label);
        
		// call parent
		parent::process_form();
	}
	
	private function generate_dates($fr, $to, $keyfmt, $valuefmt){
	    for($d=strtotime($fr);$d<=strtotime($to);$d+=86400)
	    {
			$ret[date($keyfmt,$d)] = date($valuefmt,$d);
		}
		return $ret;
	}
	
	private function check_and_create_foreign_currency_list($payment_type, $currency_rate){
		global $config, $appCore;
		
		if(!is_array($this->foreign_currency_list) || !($this->foreign_currency_list[$payment_type])){
			if(!isset($this->foreign_currency_list[$payment_type])){
				$date = $_REQUEST['date_select'];
				if(!$date) $date = date("Y-m-d");
				
				// load the currency currently available from system
				if(!$currency_rate){
					$prms = array();
					$prms['branch_id'] = $this->branch_id;
					$prms['date'] = $date;
					$prms['code'] = $payment_type;
					$global_currency_rate = $appCore->posManager->loadForeignCurrencyRate($prms);
				}else $global_currency_rate = $currency_rate;
				
				$this->foreign_currency_list[$payment_type] = $global_currency_rate;
			}
		}
	}
}

$CC_VS_CS = new CC_VS_CS('Counter Collection Sales vs Category Sales by Day / Month');
?>
