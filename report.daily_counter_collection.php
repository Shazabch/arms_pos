<?php
/*
2/1/2011 10:15:54 AM Alex
- copy from counter collection

6/24/2011 6:06:16 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:28:58 PM Andy
- Change split() to use explode()

10/12/2011 4:04:35 PM Andy
- Change title "Daily Counter Collection Report" to "Daily Counter Collection Cash Domination".

11/24/2011 4:48:51 PM Alex
- separate calculation of variance for counter collection 2 and 3

3/23/2012 10:17:04 AM Alex
- fix variance calculation bugs 

7/23/2012 4:42:34 PM Justin
- Enhanced to accept branch filter with "All".
- Enhanced data listing to be either Branch or Date based on the condition.
  => Branch: branch filter must be "All" and date from and to must be same day.
  => Date: branch filter can either All or specific branch and date from and to must not be same day.
  
8/3/2012 2:34:34 PM Justin
- Bug fixed system that cannot show counter list and split by counter checkbox when login as subbranch.

11/2/2012 11:20 AM Justin
- Enhanced to use payment type from POS Settings as if found it is being set.
- Bug fixed on system show different 2 rows while print in 1 day and items being separated.

12/11/2012 4:13 PM Justin
- Enhanced to include extra payment type from both config and POS Settings.

11/5/2013 11:45 AM Fithri
- change all term "Cash Domination" to "Cash Denomination"

1/14/2014 3:44 PM Andy
- Remove clear_drawer=1 checking for pos_cash_domination.

9/11/2014 2:37 PM Justin
- Enhanced to remove off the config "counter_collection_simple" checking.

4/13/2017 9:33 AM Qiu Ying
- Bug fixd on Counter Collection Payment Type Missing

4/27/2017 3:38 PM Justin
- Enhanced to use cash_domination_notes from config instead of pos_config.

6/25/2018 11:49 AM Justin
- Enhanced to load foreign currency from config instead of pos settings.
- Enhanced to sum up the total using php array instead of smarty.

3/15/2019 2:33 PM Andy
- Enhanced counter collection variances calculation to include ewallet sales and ewallet collection.

6/12/2019 5:02 PM William
- Added new column Nett Sales.

2/18/2020 11:40 AM Andy
- Fixed to only replace "_" to " " for credit card payment.

2/24/2020 5:16 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
$maintenance->check(130);

require_once('counter_collection.include.php');

class DailyCounterCollectionReport extends Module{

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
		$this->date_from = $_REQUEST['date_from'];
		$this->date_to = $_REQUEST['date_to'];

		if(BRANCH_CODE != "HQ") $this->branch_id = $sessioninfo['branch_id'];

		// get all counters in this branch
		if($this->branch_id != "all" && $this->branch_id) $this->get_counter_name(false);

		parent::__construct($title);
    }
    
    function _default(){
		$_REQUEST['date_from'] = date('Y-m-d',strtotime('-1 month',time()));
		$_REQUEST['date_to'] = date('Y-m-d');

		$this->display();
	}
    
   	function show_report(){
    	global $con,$smarty,$pos_config,$LANG,$sessioninfo,$config,$appCore,$con_multi;
    	
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
		
		$this->foreign_currency_list = array();
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
		
		if(!$this->normal_payment_type){
			foreach($pos_config['payment_type'] as $ptype){
				if($ptype=='Discount' || $this->normal_payment_type[$ptype])  continue;
				$this->normal_payment_type[$ptype] = $ptype;
			}
		}
		
		if($config['counter_collection_extra_payment_type']){
			foreach($config['counter_collection_extra_payment_type'] as $ptype){
				$ori_type = $ptype;
				$ptype = ucwords(strtolower($ptype));
				// store for available payment type, but not always in use
				if(!in_array($ptype, $pos_config['payment_type']))	$pos_config['payment_type'][] = $ptype;
				$pos_config["payment_type_label"][$ptype] = $ori_type;
			}
		}

		$pos_config['normal_payment_type'] = $this->normal_payment_type;
		$smarty->assign("pos_config",$pos_config);

		if ($this->branch_id != "all" && !$this->counter_id){
			$this->err[] = $LANG['CC_COUNTER_MISS'];
			$smarty->assign('err',$this->err);
		}
		
		// load foreign currency
		if($config['foreign_currency']){
			foreach($config['foreign_currency'] as $curr_code=>$curr_settings){
				if(isset($this->foreign_currency_list[$curr_code])) continue;
				
				// load latest exchange rate
				$prms = array();
				$prms['branch_id'] = $bid;
				$prms['date'] = date("Y-m-d");
				$prms['code'] = $curr_code;
				$global_currency_rate = $appCore->posManager->loadForeignCurrencyRate($prms);
				
				$this->foreign_currency_list[$curr_code] = mf($global_currency_rate);
			}
		}
		
		foreach($this->branch_list as $bid=>$b){
			$this->generate_report($bid);
		}
		
		if ($this->data){
			$this->get_finalized_status();
		}
		
		if($config['counter_collection_extra_payment_type']){
			foreach($config['counter_collection_extra_payment_type'] as $ptype){
				$ptype = ucwords(strtolower($ptype));
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

        $filter = array();
        if($this->branch_id != "all" && $this->counter_id!='all') $filter[] = "p.counter_id=".mi($this->counter_id);
        $filter[] = "p.branch_id=".mi($bid)." and p.date between ".ms($this->date_from)." and ".ms($this->date_to);
        $filter = join(" and ", $filter);

        // get those counter which have pos
        $con_multi->sql_query("select distinct counter_id, p.date, cs.network_name
		from pos p
		left join counter_settings cs on p.counter_id=cs.id and p.branch_id=cs.branch_id
		where $filter and p.cancel_status=0");
		$pos_counter_date = array();
		while($r = $con_multi->sql_fetchrow())
		{
			if(count($this->branch_list) > 1 && $this->date_from == $this->date_to) $key = $this->branch_list[$bid]['branch_code'];
			else $key = $r['date'];
			$pos_counters[$r['counter_id']] = $r['network_name'];
			$pos_counter_date[$key][$r['counter_id']] = 1;
		}
		$con_multi->sql_freeresult();

		// get thouse counter got councter collection (cash domination)
		$con_multi->sql_query("select distinct counter_id, cs.network_name
		from pos_cash_domination p
		left join counter_settings cs on p.counter_id=cs.id and p.branch_id=cs.branch_id
		where $filter");
		while($r = $con_multi->sql_fetchrow())
		{
			$pos_counters[$r['counter_id']] = $r['network_name'];
		}
		$con_multi->sql_freeresult();

		if ($pos_counters){
            asort($pos_counters);   // sort array base on counter name

            foreach($pos_counters as $counter_id=>$cname){

                // get pos cash domination list for "counter collection"
                $sql = "select * from pos_cash_domination p where $filter and counter_id=".mi($counter_id)." order by timestamp";
                $con_multi->sql_query($sql);

                $start_time = $counter_whole_day_start;
                while($r = $con_multi->sql_fetchrow()){
                    // add 8 hours due to frontend time bugs
                    $r['timestamp'] = date("Y-m-d H:i:s", strtotime("+8 hour", strtotime($r['timestamp'])));
                    $r['start_time'] = $start_time;
                    $r['end_time'] = $r['timestamp'];
                    $r['data'] = unserialize($r['data']);
                    $r['odata'] = unserialize($r['odata']);
                    $r['curr_rate'] = unserialize($r['curr_rate']);
                    $r['ocurr_rate'] = unserialize($r['ocurr_rate']);

//				if (mi($_REQUEST['split_counter']))	$this->pos_cash_domination[$counter_id][$r['date']][$r['id']] = $r;
//				else	
					if(count($this->branch_list) > 1 && $this->date_from == $this->date_to) $key = $this->branch_list[$bid]['branch_code'];
					else $key = $r['date'];

					$this->pos_cash_domination[$key][$counter_id][$r['id']] = $r;

					$start_time = date("Y-m-d H:i:s", strtotime("+1 second", strtotime($r['end_time'])));
				}

				$con_multi->sql_freeresult();

				// get sales data
//				$this->generate_data($counter_id);
				// if got counter session which have no pos cash domination
			}
			
			// for those counter dun hv cash domination
			foreach($pos_counter_date as $d => $counter_list){
				foreach($counter_list as $cid => $dummy){
					if(!isset($this->pos_cash_domination[$d][$cid]))	$this->pos_cash_domination[$d][$cid] = array();
				}
			}
			$this->generate_data($bid);
			
			if(count($this->branch_list) > 1) unset($this->pos_cash_domination);
		}

//		$smarty->assign('pos_cash_domination', $this->pos_cash_domination);

	}

   	function generate_data($bid){
        global $con,$config,$pos_config,$mm_discount_col_value,$con_multi;
		// pos cash domination
		$pos_cash_domination_notes = $config['cash_domination_notes'];

        if($this->pos_cash_domination){
        	ksort($this->pos_cash_domination);

			if (!in_array($mm_discount_col_value,$pos_config['payment_type']))	$payment_type = array_merge($pos_config['payment_type'], array($mm_discount_col_value));	

        	foreach($this->pos_cash_domination as $type=>$dummy){
				if(count($this->branch_list) > 1 && $this->date_from == $this->date_to){
					$key = $this->branch_list[$bid]['branch_code'];
					$fdate = $this->date_from;
				}else{
					$key = $fdate = $type;
				}
            	foreach($dummy as $counter_id=>$dr){
            		$variance = $cash_advance = $cashier_sales = array();
					
            		//get variance data
					$sql="select variance,cash_advance,cashier_sales from pos_counter_finalize where branch_id=".mi($bid)." and counter_id=$counter_id and date=".ms($fdate);
					$con_multi->sql_query($sql);
					if ($con_multi->sql_numrows()>0){
						while ($r=$con_multi->sql_fetchassoc()){
							$variance = unserialize($r['variance']);
							$cash_advance = unserialize($r['cash_advance']);
							$cashier_sales = unserialize($r['cashier_sales']);
						}
						unset($r);
					}
					$con_multi->sql_freeresult();

					if (!mi($_REQUEST['split_counter']))	$counter_id=0;	//to combine all counter data for each day

					//print "variance += ".$variance['nett_sales']['amt']."<br />";
					$total_variance[$counter_id]['nett_sales']['amt'] += $variance['nett_sales']['amt'];
					$total_cash_advance[$counter_id]['nett_sales']['amt'] += $cash_advance['nett_sales']['amt'];
					$total_cashier_sales[$counter_id][] = $cashier_sales;
					$nett_sales[$counter_id]['nett_sales']['amt'] += $cashier_sales['nett_sales']['amt'];
					unset($variance,$cash_advance,$cashier_sales);
					//calculate total
					foreach($dr as $dom_id=>$r){
						if(!$r['data']&&!$r['odata']) continue;

						$this->recalculate_data($r,$counter_id,$pos_cash_domination_notes,$key);				
					}
				}
				
				//total each date
				foreach($total_variance as $counter_id =>$other){
					$this->data[$counter_id][$key]['variance']['amt']+=$other['nett_sales']['amt'];
					//$this->data[$counter_id][$key]['variance']['nett']+=$other['nett_sales']['amt'];
					//$this->total[$counter_id]['
				}
				
				//total each date of nett_sales
				foreach($nett_sales as $counter_id =>$other){
					$this->data[$counter_id][$key]['nett_sales']['amt']+= $other['nett_sales']['amt'];
				}

				unset($total_collection,$total_variance,$total_cashier_sales,$total_cash_advance,$nett_sales);
			}
		}
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
				
				if($type_key != $mm_discount_col_value && !$this->normal_payment_type[$type_key] && (in_array($type_key, $pos_config['payment_type']) || preg_match('/^ewallet_/i', $type_key))){
					$this->normal_payment_type[$type_key] = $type_key;
				}

                if(isset($this->foreign_currency_list[$type_key])){  // is currency collection
                	if (!$curr_rate[$type_key])
                        $rm_amt=0;
				 	else
                      	$rm_amt = mf($d2/$curr_rate[$type_key]);
					
					$rm_amt = round($rm_amt, 2);
                	$this->data[$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['foreign_amt'] += $d2;
	                $this->data[$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['rm_amt'] += $rm_amt;

	                // jz use it to construct currency array
	                $this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['foreign_amt'] += $d2;
	                $this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['rm_amt'] += $rm_amt;

	                if(strpos($type, '_Float')){    // is currency float
						$this->data[$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['Float']['foreign_amt'] += abs($d2);
						$this->total[$counter_id]['cash_domination']['foreign_currency'][$type_key]['Float']['foreign_amt'] += abs($d2);
					}
					
					$this->total[$counter_id]['cash_domination']['foreign_currency'][$type_key]['foreign_amt'] += $d2;
					$this->total[$counter_id]['cash_domination']['foreign_currency'][$type_key]['amt'] += $rm_amt;
				}else{
				    $rm_amt = $d2;
                    $this->data[$counter_id][$key]['cash_domination'][$type_key]['amt'] += $rm_amt;

                    if($type=='Float'){ // is cash float
                    	$this->data[$counter_id][$key]['cash_domination']['Float']['amt'] += abs($rm_amt);
						$this->total[$counter_id]['cash_domination'][$type_key]['Float']['amt'] += abs($d2);
					}
					$this->data[$counter_id][$key]['cash_domination']['nett_sales']['amt'] += $rm_amt;
					$this->total[$counter_id]['cash_domination'][$type_key]['amt'] += $rm_amt;
					$this->total[$counter_id]['cash_domination']['nett_sales']['amt'] += $rm_amt;
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
				
				if($type_key && $type_key != $mm_discount_col_value && !$this->normal_payment_type[$type_key] && (in_array($type_key, $pos_config['payment_type']) || preg_match('/^ewallet_/i', $type_key))){
					$this->normal_payment_type[$type_key] = $type_key;
				}

                if(isset($this->foreign_currency_list[$type_key])){  // is currency collection
                	$rm_amt = 0;
                	if($curr_rate[$type_key])	$rm_amt = mf($d2/$curr_rate[$type_key]);

					$rm_amt = round($rm_amt, 2);
                	$this->data[$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['o_foreign_amt'] += $d2;
	            	$this->data[$counter_id][$key]['cash_domination']['foreign_currency'][$type_key]['o_rm_amt'] += $rm_amt;

	                // jz use it to construct currency array
	            	$this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['o_foreign_amt'] += $d2;
	            	$this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['o_rm_amt'] += $rm_amt;
				}else{
				    $rm_amt = $d2;
                    $this->data[$counter_id][$key]['cash_domination'][$type_key]['o_amt'] += $rm_amt;
					$this->data[$counter_id][$key]['cash_domination']['nett_sales']['o_amt'] += $rm_amt;
					
					$this->total[$counter_id]['cash_domination'][$type_key]['o_amt'] += $rm_amt;
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
		global $config;
		
		$is_currency = strpos($remark," @");
		if($is_currency == true){
	        $remark = explode(" @",$remark);
			$currency = $remark[0];
			$currency_rate = sprintf("%01.".$config['foreign_currency_decimal_points']."f", $remark[1]);
			$rm_amt = $currency/$currency_rate;
         	$ret = array('is_currency'=>true, 'currency_amt'=>$currency, 'currency_rate'=>$currency_rate, 'rm_amt'=>$rm_amt);
		}
        else{
		    $ret = array('rm_amt'=>$amount);
		}

 		return $ret;
	}

	private function pch_is_currency($remark, $amount){
		global $config;
		
		$is_currency = strpos($remark," @");
		if($is_currency == true){
	        $remark = explode(" @",$remark);
			$currency_type = $remark[0];
			$currency_rate = sprintf("%01.".$config['foreign_currency_decimal_points']."f", $remark[1]);
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

}

//$con_multi= new mysql_multi();
$dccr = new DailyCounterCollectionReport("Daily Counter Collection Cash Denomination");
//$con_multi->close_connection();
?>
