<?php
/*
07/31/2013 04:43 PM Justin
- Enhanced to have cashier filter.

08/01/2013 04:35 PM Justin
- Enhanced to have show date details by cashier.

1/14/2014 3:44 PM Andy
- Remove clear_drawer=1 checking for pos_cash_domination.

9/11/2014 2:37 PM Justin
- Enhanced to remove off the config "counter_collection_simple" checking.

3:02 PM 11/26/2014 Andy
- Fix sometime will show wrong currency data when choose show by all branch.

6/2/2015 4:31 PM Justin
- Enhanced to have total cash advance column.

10/10/2016 6:05 PM Andy
- Fixed cashier variance bug.

4/27/2017 3:38 PM Justin
- Enhanced to use cash_domination_notes from config instead of pos_config.

7/10/2017 11:12 Qiu Ying
- Bug fixed on cashier variance report filter by counter

2017-09-14 15:04 PM Qiu Ying
- Enhanced to split by branch when in HQ

12/17/2018 5:33 PM Justin
- Bug fixed on adding +8 hours to get last cashier had caused issue on getting wrong Cash Denomination.

01/04/2019 04:47 PM Justin
- Revamped the report.
- Enhanced to include the missing foreign currency info.

2/13/2019 5:46 PM Andy
- Fixed show details no calculate foreign currency variance in local currency.

3/15/2019 1:23 PM Andy
- Enhanced counter collection variances calculation to include ewallet sales and ewallet collection.
- Changed generate_pos_cashier_finalize() to posManaer->generatePosCashierFinalize()

2/18/2020 3:13 PM Andy
- Fixed to only replace "_" to " " for credit card payment.

2/24/2020 4:56 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
$maintenance->check(207);

require_once('counter_collection.include.php');

class Cashier_Variance_Report extends Module{
	var $mm_discount_col_value = 'Mix & Match Total Disc';
    function __construct($title){
        global $con, $smarty, $sessioninfo, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
        $con_multi->sql_query("select * from branch where active=1 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branches',$this->branches);

		$this->branch_id = $_REQUEST['branch_id'];
		$this->counter_id = $_REQUEST['counter_id'];
		$this->cashier_id = $_REQUEST['cashier_id'];
		$this->date_from = $_REQUEST['date_from'];
		$this->date_to = $_REQUEST['date_to'];

		if(BRANCH_CODE != "HQ") $this->branch_id = $sessioninfo['branch_id'];
		
		if($_REQUEST['branch_id'] == "all") $_REQUEST['split_counter'] = 0;

		// get all counters in this branch
		if($this->branch_id != "all" && $this->branch_id) $this->get_counter_name(false);

		
		// get cashier name
		$q1 = $con_multi->sql_query("select * from user order by u");
		while($r = $con_multi->sql_fetchassoc($q1)){
			$cashiers[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);
		$smarty->assign("cashiers", $cashiers);
		
		parent::__construct($title);
    }
    
    function _default(){
		$_REQUEST['date_from'] = date('Y-m-d',strtotime('-1 month',time()));
		$_REQUEST['date_to'] = date('Y-m-d');

		$this->display();
	}
    
   	function show_report(){
    	global $con,$smarty,$pos_config,$LANG,$sessioninfo,$config,$con_multi;
    	
		if(BRANCH_CODE == 'HQ'){    // HQ mode
			$branch_id = mi($_REQUEST['branch_id']);
			if($branch_id){  // single branch selected
				$tmp['branch_id'] = $branch_id;
				$tmp['branch_code'] = get_branch_code($branch_id);
				$this->branch_list[$branch_id] = $tmp;
			}   
			else{   // all branches selected
				foreach($this->branches as $bid=>$b){
					$tmp['branch_id'] = $bid;
					$tmp['branch_code'] = get_branch_code($bid);
					$this->branch_list[$bid] = $tmp;
				}
			}
		}else{  // Branches mode
            //$branch_id = mi($sessioninfo['branch_id']);
			$tmp['branch_id'] = $sessioninfo['branch_id'];
			$tmp['branch_code'] = get_branch_code($sessioninfo['branch_id']);
			$this->branch_list[$sessioninfo['branch_id']] = $tmp;
		}
		
		foreach($this->branch_list as $bid=>$f){
			// get currency type and assign it into pos_config
			/*$con->sql_query("select * from pos_settings where branch_id=".mi($bid)." and setting_name='currency'");
			$r = $con->sql_fetchrow();
			$con->sql_freeresult();
			$currencies = $r['setting_value'];
			if ($currencies) $currencies = unserialize($currencies);
			
			if (is_array($currencies))
			{
				$pos_config['curr_rate'] = $currencies;

				foreach($pos_config['curr_rate'] as $currency_type=>$currency_rate){
					if(!$pos_config['currency'][$currency_type]) $pos_config['currency'][$currency_type] = $currency_type;
					$currency_rate = sprintf("%01.3f", $currency_rate);
					if(!$currency_rate) continue;

					$this->currency_data[$currency_type]['currency_rate'][$currency_rate]= array();
				}
			}*/
			
			// load payment type from pos settings
			$q1 = $con_multi->sql_query("select * from pos_settings where setting_name = 'payment_type' and branch_id = ".mi($bid));
			
			$ps_info = $con_multi->sql_fetchrow($q1);
			$ps_payment_type = unserialize($ps_info['setting_value']);
			$con_multi->sql_freeresult($q1);

			if($ps_payment_type){
				foreach($ps_payment_type as $ptype=>$val){
					if(!$val) continue;
					//$ptype = ucwords(str_replace("_", " ",$ptype));
					if(strpos(strtolower($ptype), "credit_card")===0){
						$ptype = str_replace("_", " ",$ptype);	// only replace "_" to " " if it is credit card
					}
					$ptype = ucwords($ptype);
					if($ptype == "Credit Card") $ptype = "Credit Cards";
					
					if($this->normal_payment_type[$ptype])  continue;
					$this->normal_payment_type[$ptype] = $ptype;
				}
			}
		}
		//if($pos_config['currency'])	asort($pos_config['currency']);
		
		if(!$this->normal_payment_type){
			foreach($pos_config['payment_type'] as $ptype){
				if($ptype=='Discount' || $this->normal_payment_type[$ptype])  continue;
				$this->normal_payment_type[$ptype] = $ptype;
			}
		}
		
		if($config['counter_collection_extra_payment_type']){
			foreach($config['counter_collection_extra_payment_type'] as $ptype){
				$ptype = ucwords($ptype);
				// store for available payment type, but not always in use
				if(!in_array($ptype, $pos_config['payment_type']))	$pos_config['payment_type'][] = $ptype;
			}
		}

		$pos_config['normal_payment_type'] = $this->normal_payment_type;
		//print_r($pos_config);
		$smarty->assign("pos_config",$pos_config);

		if ($this->branch_id != "all" && !$this->counter_id){
			$this->err[] = $LANG['CC_COUNTER_MISS'];
			$smarty->assign('err',$this->err);
		}
		
		foreach($this->branch_list as $bid=>$b){
			$this->generate_report($bid);
		}
		
		if ($this->data){
			$this->get_finalized_status();
		}
		
		if($config['counter_collection_extra_payment_type']){
			foreach($config['counter_collection_extra_payment_type'] as $ptype){
				$ptype = ucwords($ptype);
				if($this->normal_payment_type[$ptype]) $this->normal_payment_type[$ptype] = $ptype;
			}
		}

	  	$smarty->assign('total', $this->total);
		$smarty->assign('data', $this->data);
		//$smarty->assign('currency_data', $this->currency_data);
		$smarty->assign('branch_list', $this->branch_list);
		asort($this->normal_payment_type);
		$smarty->assign('normal_payment_type', $this->normal_payment_type);
		$smarty->assign('foreign_currency_list', $this->foreign_currency_list);
	
    	$this->display();
	}

	function generate_report($bid){
        global $con, $smarty, $config, $pos_config, $con_multi;

        if($this->err)  return;

        $filter = $pos_filters = $pcd_filter = array();
        if($this->branch_id != "all"){
			if($this->counter_id!='all') $filter[] = "p.counter_id=".mi($this->counter_id);
			if($this->cashier_id!='all'){
				$pos_filters[] = "cashier_id=".mi($this->cashier_id);
				//$pcd_filter[] = "cashier_id=".mi($this->cashier_id);
			}
		}
        $filter[] = "p.branch_id=".mi($bid)." and p.date between ".ms($this->date_from)." and ".ms($this->date_to);
        $filter = join(" and ", $filter);
		if($pos_filters) $pos_filter = " and ".join(" and ", $pos_filters);
		//if($pcd_filter) $pcd_filter = " and ".join(" and ", $pcd_filter);

        // get those counter which have pos
		$q1 = $con_multi->sql_query("select distinct counter_id, p.date, cs.network_name
							   from pos p
							   left join counter_settings cs on p.counter_id=cs.id and p.branch_id=cs.branch_id
							   where $filter $pos_filter and p.cancel_status=0");
		
		$pos_counter_date = array();
		while($r = $con_multi->sql_fetchassoc($q1)){
			$key = $r['date'];
			$pos_counters[$r['counter_id']] = $r['network_name'];
			$pos_counter_date[$key][$r['counter_id']] = 1;
		}
		$con_multi->sql_freeresult($q1);

		// get thouse counter got councter collection (cash domination)
		$q1 = $con_multi->sql_query("select distinct counter_id, cs.network_name
							   from pos_cash_domination p
							   left join counter_settings cs on p.counter_id=cs.id and p.branch_id=cs.branch_id
							   join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date and pf.finalized=1
							   where $filter");

		while($r = $con_multi->sql_fetchassoc($q1)){
			$pos_counters[$r['counter_id']] = $r['network_name'];
		}
		$con_multi->sql_freeresult($q1);

		if ($pos_counters){
            asort($pos_counters);   // sort array base on counter name

            foreach($pos_counters as $counter_id=>$cname){
                // get pos cash domination list for "counter collection"
                $sql = "select p.*, user_id as cashier_id
						from pos_cash_domination p
						join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date and pf.finalized=1
						where $filter and counter_id=".mi($counter_id)." order by timestamp";
                $q1 = $con_multi->sql_query($sql);

                $start_time = $counter_whole_day_start;
                while($r = $con_multi->sql_fetchassoc($q1)){
                    // add 8 hours due to frontend time bugs
                    $r['timestamp'] = date("Y-m-d H:i:s", strtotime($r['timestamp']));
                    $r['start_time'] = $start_time;
                    $r['end_time'] = $r['timestamp'];
                    $r['data'] = unserialize($r['data']);
                    $r['odata'] = unserialize($r['odata']);
                    $r['curr_rate'] = unserialize($r['curr_rate']);
                    $r['ocurr_rate'] = unserialize($r['ocurr_rate']);

					$key = $r['date'];

					$pul_filter = array();
					$pul_filter[] = "type = 'login'";
					$pul_filter[] = "date = ".ms($r['date']);
					$pul_filter[] = "counter_id = ".mi($counter_id);
					$pul_filter[] = "branch_id = ".mi($r['branch_id']);
					$pul_filter[] = "timestamp < ".ms($r['timestamp']);
					$pul_filter = join(" and ", $pul_filter);
					$con_multi->sql_query("select * from pos_user_log where ".$pul_filter." order by timestamp desc limit 1");
					$last_cashier = $con_multi->sql_fetchassoc($q2);
					$con_multi->sql_freeresult($q2);

					if($last_cashier['cashier_id']) $r['cashier_id'] = $last_cashier['cashier_id'];
					
					if($this->cashier_id != "all" && $this->cashier_id != $r['cashier_id']) continue;
					
					$this->pos_cash_domination[$key][$counter_id][$r['id']] = $r;

					$start_time = date("Y-m-d H:i:s", strtotime("+1 second", strtotime($r['end_time'])));
				}

				$con_multi->sql_freeresult($q1);
			}
			
			// for those counter dun hv cash domination
			foreach($pos_counter_date as $d => $counter_list){
				foreach($counter_list as $cid => $dummy){
					if(!isset($this->pos_cash_domination[$d][$cid])) $this->pos_cash_domination[$d][$cid] = array();
				}
			}
			$this->generate_data($bid);
			
			if(count($this->branch_list) > 1) unset($this->pos_cash_domination);
		}
	}

   	function generate_data($bid){
        global $con,$config, $pos_config, $mm_discount_col_value, $appCore, $con_multi;
		
		// pos cash domination
		$pos_cash_domination_notes = $config['cash_domination_notes'];
		
		if($this->cashier_id!='all'){
			$cashier_filter = " and p.cashier_id=".mi($this->cashier_id);
		}
			
        if($this->pos_cash_domination){
        	ksort($this->pos_cash_domination);
			//print_r($this->pos_cash_domination);exit;
			if (!in_array($mm_discount_col_value,$pos_config['payment_type']))	$payment_type = array_merge($pos_config['payment_type'], array($mm_discount_col_value));	

        	foreach($this->pos_cash_domination as $type=>$dummy){
				/*if(count($this->branch_list) > 1 && $this->date_from == $this->date_to){
					$key = $this->branch_list[$bid]['branch_code'];
					$fdate = $this->date_from;
				}else{
				}*/
				$key = $type;
            	foreach($dummy as $counter_id=>$dr){
					$variance = $cash_advance = $cashier_sales = array();
					
					$appCore->posManager->generatePosCashierFinalize($key, $counter_id, $bid);

					//get variance data
					$sql="select p.variance,p.cash_advance,p.cashier_sales,p.cashier_id
						  from pos_cashier_finalize p
						  where p.branch_id=".mi($bid)." and p.counter_id=$counter_id $cashier_filter and p.date=".ms($key);
					//print $sql."<br />";
					$q1 = $con_multi->sql_query($sql);

					if (!mi($_REQUEST['split_counter']) && ($_REQUEST["counter_id"] == 'all' || $_REQUEST["branch_id"] == 'all')) $counter_id=0;	//to combine all counter data for each day

					if($con_multi->sql_numrows($q1)>0){
						while($r1=$con_multi->sql_fetchassoc($q1)){
							$variance = unserialize($r1['variance']);
							$cash_advance = unserialize($r1['cash_advance']);
							$cashier_sales = unserialize($r1['cashier_sales']);
							
							$total_variance[$counter_id][$r1['cashier_id']]['nett_sales']['amt'] += $variance['nett_sales']['amt'];
							$total_cash_advance[$counter_id][$r1['cashier_id']]['nett_sales']['amt'] += $cash_advance['nett_sales']['amt'];
							$total_cashier_sales[$counter_id][$r1['cashier_id']][] = $cashier_sales;
							
							// variance for foreign currency
							if($variance['foreign_currency']){
								foreach($variance['foreign_currency'] as $fc_code=>$fc_info){
									$total_variance[$counter_id][$r1['cashier_id']]['foreign_currency'][$fc_code]['foreign_amt'] += $fc_info['foreign_amt'];
									$total_variance[$counter_id][$r1['cashier_id']]['foreign_currency'][$fc_code]['rm_amt'] += $fc_info['rm_amt'];
								}
							}
							//print "<br />";
							//print_r($cashier_sales);
							//print "<br />";
							unset($variance,$cash_advance,$cashier_sales);
						}
						unset($r1);
					}
					$con_multi->sql_freeresult($q1);



					//calculate total
					foreach($dr as $dom_id=>$r){				
						if(!$r['data']&&!$r['odata']) continue;

						$this->recalculate_data($r,$counter_id,$pos_cash_domination_notes,$r['cashier_id']);				
					}
				}
				
				//total variance
				if($total_variance){
					foreach($total_variance as $counter_id => $cashier_list){
						foreach($cashier_list as $cashier_id => $other){
							$this->data[$bid][$counter_id][$cashier_id]['variance']['amt']+=$other['nett_sales']['amt'];
							$this->total[$bid][$counter_id]['variance']['amt'] += $other['nett_sales']['amt'];
							
							if($other['foreign_currency']){
								foreach($other['foreign_currency'] as $fc_code=>$fc_info){
									//$this->data[$bid][$counter_id][$cashier_id]['foreign_currency'][$fc_code]['foreign_amt']+=$fc_info['foreign_amt'];
									$this->data[$bid][$counter_id][$cashier_id]['variance']['amt']+=$fc_info['rm_amt'];
									$this->total[$bid][$counter_id]['variance']['amt'] += $fc_info['rm_amt'];
								}
							}
						}
					}
				}

				//total cash advance
				if($total_cash_advance){
					foreach($total_cash_advance as $counter_id => $cashier_list){
						foreach($cashier_list as $cashier_id => $other){
							$this->data[$bid][$counter_id][$cashier_id]['cash_advance']['amt']+=$other['nett_sales']['amt']*-1;
							$this->total[$bid][$counter_id]['cash_advance']['amt']+=$other['nett_sales']['amt']*-1;
						}
					}
				}
				
				unset($total_collection,$total_variance,$total_cashier_sales,$total_cash_advance);

			}
		}
		
		//print_r($this->data);
		
	}
		
	function recalculate_data($r,$counter_id,$pos_cash_domination_notes,$key){
		global $pos_config, $mm_discount_col_value, $config;
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
				
				$tmp_type_key = strtoupper($type_key);
				if($curr_rate[$tmp_type_key]){  // is currency collection
					$currency_rate = $curr_rate[$tmp_type_key];
					$rm_amt = $currency_rate ? mf($d2/$currency_rate) : 0;
					
					$is_foreign_currency = true;
				}else{
					$rm_amt = $d2;
				}
				$rm_amt = round($rm_amt, 2);
				

                if($is_foreign_currency){  // is currency collection
					$this->check_and_create_foreign_currency_list($type_key, $currency_rate);
                	$this->data[$r["branch_id"]][$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['foreign_amt'] += $d2;
					$this->total[$r["branch_id"]][$counter_id]['foreign_currency'][$type_key]['foreign_amt'] += $d2;
	                //$this->data[$r["branch_id"]][$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['rm_amt'] += $rm_amt;

	                // jz use it to construct currency array
	                /*$this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['foreign_amt'] += $d2;
	                $this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['rm_amt'] += $rm_amt;*/

	                if(strpos($type, '_Float')){    // is currency float
						$this->data[$r["branch_id"]][$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['Float']['foreign_amt'] += abs($d2);
						$this->total[$r["branch_id"]][$counter_id]['foreign_currency'][$type_key]['Float']['foreign_amt'] += abs($d2);
					}
				}else{
                    $this->data[$r["branch_id"]][$counter_id][$key]['cash_domination'][$type_key]['amt'] += $rm_amt;
                    $this->data[$r["branch_id"]][$counter_id][$key]['cash_domination']['sub_total']['amt'] += $rm_amt;
					$this->total[$r["branch_id"]][$counter_id]['cash_domination'][$type_key]['amt'] += $rm_amt;
					$this->total[$r["branch_id"]][$counter_id]['total']['amt'] += $rm_amt;

                    if($type=='Float'){ // is cash float
                    	$this->data[$r["branch_id"]][$counter_id][$key]['cash_domination']['Float']['amt'] += abs($rm_amt);
						$this->total[$r["branch_id"]][$counter_id]['cash_domination']['Float']['amt'] += abs($rm_amt);
						$this->total[$r["branch_id"]][$counter_id]['total']['Float']['amt'] += abs($rm_amt);
					}
				}
				$this->data[$r["branch_id"]][$counter_id][$key]['cash_domination']['nett_sales']['amt'] += $rm_amt;
				
				if($type_key != $mm_discount_col_value && !$this->normal_payment_type[$type_key] && (in_array($type_key, $pos_config['payment_type']) || preg_match('/^ewallet_/i', $type_key))){
					$this->normal_payment_type[$type_key] = $type_key;
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
				//if($this->date_from == $this->date_to) $key = $this->branch_list[$bid]['branch_code'];
				//else $key = $r['date'];

			    $type_key = $type;
				$is_foreign_currency = false;
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
					//$type_key = 'Cash';
					$d2 *= -1;
				}
				
				$tmp_type_key = strtoupper($type_key);
				if($curr_rate[$tmp_type_key]){  // is currency collection
					$currency_rate = $curr_rate[$tmp_type_key];
					$rm_amt = $currency_rate ? mf($d2/$currency_rate) : 0;
					
					$is_foreign_currency = true;
				}else{
					$rm_amt = $d2;
				}
				$rm_amt = round($rm_amt, 2);
				
                if($is_foreign_currency){  // is currency collection
                	$this->data[$r["branch_id"]][$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['o_foreign_amt'] += $d2;
	            	$this->data[$r["branch_id"]][$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['o_rm_amt'] += $rm_amt;

	                // jz use it to construct currency array
	            	/*$this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['o_foreign_amt'] += $d2;
	            	$this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['o_rm_amt'] += $rm_amt;*/
				}else{
				    $rm_amt = $d2;
                    $this->data[$r["branch_id"]][$counter_id][$key]['cash_domination'][$type_key]['o_amt'] += $rm_amt;
					$this->total[$r["branch_id"]][$counter_id]['cash_domination'][$type_key]['o_amt'] += $rm_amt;
					$this->total[$r["branch_id"]][$counter_id]['total']['o_amt'] += $rm_amt;
				}
				$this->data[$r["branch_id"]][$counter_id][$key]['cash_domination']['nett_sales']['o_amt'] += $rm_amt;
				
				if($type_key && $type_key != $mm_discount_col_value && !$this->normal_payment_type[$type_key] && (in_array($type_key, $pos_config['payment_type']) || preg_match('/^ewallet_/i', $type_key))){
					$this->normal_payment_type[$type_key] = $type_key;
				}
			}
		}
	}
	
	function get_counter_name($ajax=true){
	    global $con,$smarty,$con_multi;
	
        $filter = array();
        $filter[] = "p.branch_id=$this->branch_id";
        $filter = join(" and ", $filter);

		/*
        // get those counter which have pos
        $con->sql_query("select distinct counter_id, cs.network_name
		from pos p
		left join counter_settings cs on p.counter_id=cs.id and p.branch_id=cs.branch_id
		where $filter and p.cancel_status=0");
		while($r = $con->sql_fetchrow())
		{
			$pos_counters[$r['counter_id']] = $r['network_name'];
		}
		$con->sql_freeresult();

		// get thouse counter got councter collection (cash domination)
		$con->sql_query("select distinct counter_id, cs.network_name
		from pos_cash_domination p
		left join counter_settings cs on p.counter_id=cs.id and p.branch_id=cs.branch_id
		where $filter");
		while($r = $con->sql_fetchrow())
		{
			$pos_counters[$r['counter_id']] = $r['network_name'];
		}
		$con->sql_freeresult();
		*/

		$con_multi->sql_query("select distinct p.id as counter_id, p.network_name
		from counter_settings p
		where $filter order by p.network_name");
		
		while($r = $con_multi->sql_fetchrow())
		{
			$pos_counters[$r['counter_id']] = $r;
		}
		$con_multi->sql_freeresult();

		//if not ajax call
		if (!$ajax){
            $smarty->assign('counters',$pos_counters);
            return;
		}

		$options="<select name='counter_id' >";
		if ($pos_counters){

			if (count($pos_counters)>1)	$options .= "<option value='all'>- All -</option>";

			foreach ($pos_counters as $counter_id => $data){
				$options .= "<option value='$counter_id' >$data[network_name]</option>";
			}
		}else{
			$options .= "<option value=''>No Data</option>";
		}
		$options.="</select>";

		print $options;
	}
	
	private function pp_is_currency($remark, $amount){
		$is_currency = strpos($remark," @");
		if($is_currency == true){
	        $remark = explode(" @",$remark);
			$currency = $remark[0];
			$currency_rate = sprintf("%01.3f", $remark[1]);
			$rm_amt = $currency/$currency_rate;
         	$ret = array('is_currency'=>true, 'currency_amt'=>$currency, 'currency_rate'=>$currency_rate, 'rm_amt'=>$rm_amt);
		}
        else{
		    $ret = array('rm_amt'=>$amount);
		}

 		return $ret;
	}

	private function pch_is_currency($remark, $amount){
		$is_currency = strpos($remark," @");
		if($is_currency == true){
	        $remark = explode(" @",$remark);
			$currency_type = $remark[0];
			$currency_rate = sprintf("%01.3f", $remark[1]);
			$currency = $amount;
			$rm_amt = $currency/$currency_rate;
         	$ret = array('is_currency'=>true, 'currency_amt'=>$currency, 'currency_rate'=>$currency_rate, 'rm_amt'=>$rm_amt, 'currency_type'=>$currency_type);
		}
        else{
		    $ret = array('rm_amt'=>$amount);
		}

 		return $ret;
	}

	private function get_finalized_status(){
		global $con,$smarty,$con_multi;
		
		foreach($this->branch_list as $bid=>$dummy){
			$q1 = $con_multi->sql_query("select * from pos_finalized where branch_id=".mi($bid)." and date between ".ms($this->date_from)." and ".ms($this->date_to)." and finalized=1");

			while($r=$con_multi->sql_fetchassoc($q1)){
				if(count($this->branch_list) > 1 && $this->date_from == $this->date_to) $key = $this->branch_list[$bid]['branch_code'];
				else $key = $r['date'];
				if(count($this->branch_list) == 1 || $this->date_from == $this->date_to){
					$finalized[$key]=$r['finalized'];
				}elseif(count($this->branch_list) > 1){
					$finalized[$r['branch_id']][$key] = true;
				}
			}
			$con_multi->sql_freeresult($q1);
		}

		$smarty->assign('finalized', $finalized);
	}
    
   	function output_excel(){
	    global $smarty, $sessioninfo;

        include_once("include/excelwriter.php");
    	$smarty->assign('no_header_footer', true);
    	$smarty->assign('open_mode','view');
    	$filename = "daily_cc_".time().".xls";
    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Category Stock Analysis To Excel($filename)");
    	Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename='.$filename);

		print ExcelWriter::GetHeader();
		$this->show_report();
		print ExcelWriter::GetFooter();
	    exit;
	}

	function ajax_show_date_details(){
        global $con, $smarty, $config, $pos_config, $con_multi;

		$form = $_REQUEST;
		//print_r($form);exit;
		$normal_payment_type = array();
		foreach($_REQUEST['payment_type_list'] as $pt){
			$payment_type = trim(urldecode($pt));
			
			$normal_payment_type[$payment_type] = $payment_type;
		}
		
		if($_REQUEST['foreign_currency_list']){
			foreach($_REQUEST['foreign_currency_list'] as $fc_code){
				if(!$fc_code)	continue;
				$this->foreign_currency_list[$fc_code] = 1;
			}
		}

        //$filter = $pos_filters = $pcd_filter = array();

		$this->branch_list = array();
		// load payment type from pos settings
		if($form['branch_id'] != "all"){
			$bid = mi($form['branch_id']);
			$tmp['branch_id'] = $bid;
			$tmp['branch_code'] = $this->branches[$bid]['code'];
			$this->branch_list[$bid]  = $tmp;
		}else{
			foreach($this->branches as $bid=>$b){
				$tmp['branch_id'] = $bid;
				$tmp['branch_code'] = $this->branches[$bid]['code'];
				$this->branch_list[$bid] = $tmp;
			}
			$ps_filter = " and branch_id = ".mi($form['branch_id']);
		}
		
		foreach($this->branch_list as $bid => $b){
			$filter = $pos_filters = $pcd_filter = array();
			
			/*$q1 = $con->sql_query("select * from pos_settings where setting_name = 'currency' and branch_id=".$bid);
		
			while($r = $con->sql_fetchassoc($q1)){
				$currencies = $r['setting_value'];
				if ($currencies) $currencies = unserialize($currencies);

				if (is_array($currencies)){
					$pos_config['curr_rate'] = $currencies;

					foreach($pos_config['curr_rate'] as $currency_type=>$currency_rate){
						if(!$pos_config['currency'][$currency_type]) $pos_config['currency'][$currency_type] = $currency_type;
						$currency_rate = sprintf("%01.3f", $currency_rate);
						if(!$currency_rate) continue;
						
						$currency_data[$currency_type]['currency_rate'][$currency_rate]= array();
					}
				}
			}
			$con->sql_freeresult($q1);*/
			
			if($config['counter_collection_extra_payment_type']){
				foreach($config['counter_collection_extra_payment_type'] as $ptype){
					$ptype = ucwords($ptype);
					if(!in_array($ptype, $pos_config['payment_type']))	$pos_config['payment_type'][] = $ptype;
				}
			}
			
			$filter[] = "p.branch_id = ".$bid;
			
			if(mi($form['counter_id']) > 0) $filter[] = "p.counter_id=".mi($form['counter_id']);
			
			//$pos_filters[] = "cashier_id=".mi($form['cashier_id']);

			$filter[] = "p.date between ".ms($form['date_from'])." and ".ms($form['date_to']);
			$filter = join(" and ", $filter);
			if($pos_filters) $pos_filter = " and ".join(" and ", $pos_filters);
			//if($pcd_filter) $pcd_filter = " and ".join(" and ", $pcd_filter);

			// get those counter which have pos
			$q1 = $con_multi->sql_query("select distinct p.counter_id, p.date, cs.network_name
								   from pos p
								   left join pos_cashier_finalize cf on cf.counter_id = p.counter_id and cf.branch_id = p.branch_id and cf.date = p.date
								   left join counter_settings cs on p.counter_id=cs.id and p.branch_id=cs.branch_id
								   where $filter $pos_filter and p.cancel_status=0");
			
			$pos_counter_date = array();
			while($r = $con_multi->sql_fetchassoc($q1)){
				$key = $r['date'];
				$pos_counters[$r['counter_id']] = $r['network_name'];
				$pos_counter_date[$key][$r['counter_id']] = 1;
			}
			$con_multi->sql_freeresult($q1);

			// get thouse counter got councter collection (cash domination)
			$q1 = $con_multi->sql_query($sql = "select distinct counter_id, cs.network_name
								   from pos_cashier_finalize p
								   join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date and pf.finalized=1
								   left join counter_settings cs on p.counter_id=cs.id and p.branch_id=cs.branch_id
								   where $filter");
			//print $sql;
			while($r = $con_multi->sql_fetchassoc($q1)){
				$pos_counters[$r['counter_id']] = $r['network_name'];
			}
			$con_multi->sql_freeresult($q1);

			if ($pos_counters){
				asort($pos_counters);   // sort array base on counter name

				foreach($pos_counters as $counter_id=>$cname){
					// get pos cash domination list for "counter collection"
					$sql = "select p.*, user_id as cashier_id
							from pos_cash_domination p
							where $filter and counter_id=".mi($counter_id)." and p.user_id=".mi($form['cashier_id'])." order by timestamp";
					$q1 = $con_multi->sql_query($sql);

					$start_time = $counter_whole_day_start;
					while($r = $con_multi->sql_fetchassoc($q1)){
						// add 8 hours due to frontend time bugs
						$r['timestamp'] = date("Y-m-d H:i:s", strtotime($r['timestamp']));
						$r['start_time'] = $start_time;
						$r['end_time'] = $r['timestamp'];
						$r['data'] = unserialize($r['data']);
						$r['odata'] = unserialize($r['odata']);
						$r['curr_rate'] = unserialize($r['curr_rate']);
						$r['ocurr_rate'] = unserialize($r['ocurr_rate']);

						$key = $r['date'];

						$pul_filter = array();
						$pul_filter[] = "type = 'login'";
						$pul_filter[] = "date = ".ms($r['date']);
						$pul_filter[] = "counter_id = ".mi($counter_id);
						$pul_filter[] = "branch_id = ".mi($r['branch_id']);
						$pul_filter[] = "timestamp < ".ms($r['timestamp']);
						$pul_filter = join(" and ", $pul_filter);
						$con_multi->sql_query("select * from pos_user_log where ".$pul_filter." order by timestamp desc limit 1");
						$last_cashier = $con_multi->sql_fetchassoc($q2);
						$con_multi->sql_freeresult($q2);

						if($last_cashier['cashier_id']) $r['cashier_id'] = $last_cashier['cashier_id'];
						
						if($form['cashier_id'] != "all" && $form['cashier_id'] != $r['cashier_id']) continue;
						
						$pos_cash_domination[$key][$counter_id][$r['id']] = $r;

						$start_time = date("Y-m-d H:i:s", strtotime("+1 second", strtotime($r['end_time'])));
					}

					$con_multi->sql_freeresult($q1);
				}
				
				// for those counter dun hv cash domination
				foreach($pos_counter_date as $d => $counter_list){
					foreach($counter_list as $cid => $dummy){
						if(!isset($pos_cash_domination[$d][$cid])) $pos_cash_domination[$d][$cid] = array();
					}
				}
		
				$pos_cash_domination_notes = $config['cash_domination_notes'];
		
				if($pos_cash_domination){
					ksort($pos_cash_domination);
		
					if (!in_array($mm_discount_col_value,$pos_config['payment_type']))	$payment_type = array_merge($pos_config['payment_type'], array($mm_discount_col_value));	
		
					foreach($pos_cash_domination as $type=>$dummy){
						/*if(count($branch_list) > 1 && $date_from == $date_to){
							$key = $branch_list[$bid]['branch_code'];
							$fdate = $date_from;
						}else{
						}*/
						$key = $type;
						foreach($dummy as $counter_id=>$dr){
							$variance = $cash_advance = $cashier_sales = array();
		
							$pcf_filter = " and p.branch_id = ".$bid." and p.cashier_id=".mi($form['cashier_id']);
							//get variance data
							$sql="select p.variance,p.cash_advance,p.cashier_sales,p.cashier_id
								  from pos_cashier_finalize p
								  where p.counter_id=".mi($counter_id)." and p.date=".ms($key).$pcf_filter;
							//print $sql."<br />";
							$q1 = $con_multi->sql_query($sql);
		
							if (!mi($form['split_counter']) && ($_REQUEST["tmp_counter_id"] == 'all' || $_REQUEST["branch_id"] == 'all')) $counter_id=0;	//to combine all counter data for each day
		
							if($con_multi->sql_numrows($q1)>0){
								while($r1=$con_multi->sql_fetchassoc($q1)){
									$variance = unserialize($r1['variance']);
									$cash_advance = unserialize($r1['cash_advance']);
									$cashier_sales = unserialize($r1['cashier_sales']);
									
									$total_variance[$counter_id][$r1['cashier_id']]['nett_sales']['amt'] += $variance['nett_sales']['amt'];
									$total_cash_advance[$counter_id][$r1['cashier_id']]['nett_sales']['amt'] += $cash_advance['nett_sales']['amt'];
									$total_cashier_sales[$counter_id][$r1['cashier_id']][] = $cashier_sales;
									
									// variance for foreign currency
									if($variance['foreign_currency']){
										foreach($variance['foreign_currency'] as $fc_code=>$fc_info){
											// Foreign Variance
											$total_variance[$counter_id][$r1['cashier_id']]['foreign_currency'][$fc_code]['foreign_amt'] += $fc_info['foreign_amt'];
											// Variance in Local Currency
											$total_variance[$counter_id][$r1['cashier_id']]['nett_sales']['amt'] += $fc_info['rm_amt'];
										}
									}
									
									//print "<br />";
									//print_r($cashier_sales);
									//print "<br />";
									unset($variance,$cash_advance,$cashier_sales);
								}
								unset($r1);
								
								//calculate total
								foreach($dr as $dom_id=>$r){				
									if(!$r['data']&&!$r['odata']) continue;
			
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
											
											$tmp_type_key = strtoupper($type_key);
											if($curr_rate[$tmp_type_key]){  // is currency collection
												$currency_rate = $curr_rate[$tmp_type_key];
												$rm_amt = $currency_rate ? mf($d2/$currency_rate) : 0;
												
												$is_foreign_currency = true;
											}else{
												$rm_amt = $d2;
											}
											$rm_amt = round($rm_amt, 2);
											
											//if($type_key != $mm_discount_col_value && !$normal_payment_type[$type_key] && in_array($type_key, $pos_config['payment_type'])) $normal_payment_type[$type_key] = $type_key;
							
											if($is_foreign_currency){  // is currency collection
												$this->check_and_create_foreign_currency_list($type_key, $currency_rate);
												$data[$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['foreign_amt'] += $d2;
												$data[$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['rm_amt'] += $rm_amt;
							
												// jz use it to construct currency array
												/*$currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['foreign_amt'] += $d2;
												$currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['rm_amt'] += $rm_amt;*/
							
												if(strpos($type, '_Float')){    // is currency float
													$data[$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['Float']['foreign_amt'] += abs($d2);
												}
											}else{
												$rm_amt = $d2;
												$data[$counter_id][$key]['cash_domination'][$type_key]['amt'] += $rm_amt;
												$data[$counter_id][$key]['cash_domination']['sub_total']['amt'] += $rm_amt;
							
												if($type=='Float'){ // is cash float
													$data[$counter_id][$key]['cash_domination']['Float']['amt'] += abs($rm_amt);
												}
											}
											$data[$counter_id][$key]['cash_domination']['nett_sales']['amt'] += $rm_amt;
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
											//if($date_from == $date_to) $key = $branch_list[$bid]['branch_code'];
											//else $key = $r['date'];
							
											$type_key = $type;
											$is_foreign_currency = false;
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
												//$type_key = 'Cash';
												$d2 *= -1;
											}
											
											$tmp_type_key = strtoupper($type_key);
											if($curr_rate[$tmp_type_key]){  // is currency collection
												$currency_rate = $curr_rate[$tmp_type_key];
												$rm_amt = $currency_rate ? mf($d2/$currency_rate) : 0;
												
												$is_foreign_currency = true;
											}else{
												$rm_amt = $d2;
											}
											$rm_amt = round($rm_amt, 2);
											
											//if($type_key && $type_key != $mm_discount_col_value && !$normal_payment_type[$type_key] && in_array($type_key, $pos_config['payment_type'])) $normal_payment_type[$type_key] = $type_key;
							
											if($is_foreign_currency){  // is currency collection	
												$this->check_and_create_foreign_currency_list($type_key, $currency_rate);
												$data[$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['o_foreign_amt'] += $d2;
												$data[$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['o_rm_amt'] += $rm_amt;
							
												// jz use it to construct currency array
												/*$currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['o_foreign_amt'] += $d2;
												$currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['o_rm_amt'] += $rm_amt;*/
											}else{
												$rm_amt = $d2;
												$data[$counter_id][$key]['cash_domination'][$type_key]['o_amt'] += $rm_amt;
											}
											$data[$counter_id][$key]['cash_domination']['nett_sales']['o_amt'] += $rm_amt;
										}
									}
								}
							}
							$con_multi->sql_freeresult($q1);
						}
						
						//total variance
						if($total_variance){
							foreach($total_variance as $counter_id => $cashier_list){
								foreach($cashier_list as $cashier_id => $other){
									$data[$counter_id][$key]['variance']['amt']+=$other['nett_sales']['amt'];
									
									if($other['foreign_currency']){
										foreach($other['foreign_currency'] as $fc_code=>$fc_info){
											// Foreign Currency
											$data[$counter_id][$key]['foreign_currency'][$fc_code]['foreign_amt']+=$fc_info['foreign_amt'];
										}
									}
								}
							}
						}
						
						//total cash advance
						if($total_cash_advance){
							foreach($total_cash_advance as $counter_id => $cashier_list){
								foreach($cashier_list as $cashier_id => $other){
									$data[$counter_id][$key]['cash_advance']['amt']+=$other['nett_sales']['amt']*-1;
								}
							}
						}
						
						//print_r($total_variance);
						unset($total_collection,$total_variance,$total_cashier_sales,$total_cash_advance);
					}
				}
			}
		}
		
		//print_r($this->foreign_currency_list);
		//print_r($data);
		//asort($normal_payment_type);
		$smarty->assign('normal_payment_type', $normal_payment_type);
		//$smarty->assign('currency_data', $currency_data);
		$smarty->assign("parent_cid", $form['counter_id']);
		$smarty->assign("parent_cashier_id", $form['cashier_id']);
		$smarty->assign("pos_config", $pos_config);
		$smarty->assign("data", $data);
		$smarty->assign("parent_bid", $form["branch_id"]);
		$smarty->assign("foreign_currency_list", $this->foreign_currency_list);
		$smarty->display("pos_report.cashier_variance.detail.tpl");
	}
	
	private function check_and_create_foreign_currency_list($payment_type, $currency_rate){
		global $appCore;

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
}

//$con_multi= new mysql_multi();
$Cashier_Variance_Report = new Cashier_Variance_Report("Cashier Variance Report");
//$con_multi->close_connection();
?>
