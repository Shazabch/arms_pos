<?php
/*
7/7/2010 4:51:46 PM Andy
- Fix currency table bugs.

7/15/2010 10:26:57 AM Andy
- Counter Collection 3 add currency float.

7/20/2010 10:59:54 AM Andy
- Add privilege to check whether user can do un-finalize counter collection or not. (System admin always can)

7/30/2010 11:49:06 AM Andy
- Fix counter collection sometime may occur sql error when doing finalize.

8/2/2010 12:37:57 PM Andy
- Fix a bugs when counter collection 3 have sales after the last close counter.

8/4/2010 1:40:50 PM Alex
- Fix showing receipt details by adding branch_id

11/10/2010 4:02:54 PM Justin
- Fixed the bugs where getting empty sales detail from membership history.

12/17/2010 5:33:31 PM Andy
- Add can change payment type 'others'.

12/20/2010 3:16:26 PM Andy
- Add round2 for sales variance to avoid negative zero variance.

12/21/2010 12:01:10 PM Alex
- check $config['csa_generate_report'] to create CSA report flag file

1/6/2011 10:48:28 AM Justin
- Fixed the bugs for calculating payment amount called from membership history.

1/7/2011 5:04:31 PM Andy
- Fix change cash domination cannot store odata bugs.

1/12/2011 5:18:55 PM Andy
- Fix sometime counter collection show no sales details if have pos cash domination.
- Add log when user create new cash domination.

1/13/2011 1:31:51 PM Andy
- Fix if after pos cash domination have transaction again, the to time show "N/A" bugs.

2/10/2011 3:29:47 PM Andy
- Fix unfinalize will not trigger system to recalculate stock balance.

3/28/2011 4:24:32 PM Justin
- Added to retrieve extra info when view receipt info in item details.

3/29/2011 10:51:59 AM Justin
- Added member no information for receipt detail.

4/11/2011 11:58:54 AM Andy
- Check if counter version is later than v109 then no need to +8 hour to cash domination timestamp.

4/7/2011 6:10:43 PM Alex
- group payment by pos receipt=>sales details

7/6/2011 11:13:07 AM Andy
- Change split() to use explode()

9/3/2012 11:47 AM Fithri
- Item details - show barcode

11/5/2013 11:45 AM Fithri
- change all term "Cash Domination" to "Cash Denomination"
*/
class CounterCollection extends Module{
	var $branch_id;
	var $is_approval;
	var $err;
	var $normal_payment_type = array();
	
    function __construct($title, $template=''){
		global $con, $smarty, $config, $pos_config, $sessioninfo, $v109_time;
		$this->branch_id = mi($sessioninfo['branch_id']);
		$this->v109_time = $v109_time;
		
		// get currency type and assign it into pos_config
		$con->sql_query("select * from pos_settings where branch_id=$this->branch_id and setting_name='currency'");
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
		}

		foreach($pos_config['payment_type'] as $ptype){
			if($ptype=='Discount')  continue;
			$this->normal_payment_type[] = $ptype;
		}
		$pos_config['normal_payment_type'] = $this->normal_payment_type;
		$smarty->assign("pos_config",$pos_config);

		// check whether the user can do finalize or not
		$this->is_approval = privilege('CC_FINALIZE');
		
		// get all counters in this branch
		$con->sql_query("select * from counter_settings where branch_id=$this->branch_id order by network_name");
		while($r = $con->sql_fetchrow()){
			$counters[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign("counters", $counters);

		$con->sql_query("select id,u from user");
		while($r = $con->sql_fetchrow()){
			$username[$r['id']] = $r['u'];
		}
		$con->sql_freeresult();
		$smarty->assign("username", $username);

		// get counter errors
		$con->sql_query("select pcct.*, cs.network_name, branch.code
		from pos_counter_collection_tracking pcct
		left join counter_settings cs on cs.id = pcct.counter_id and cs.branch_id = pcct.branch_id
		left join branch on branch.id = pcct.branch_id
		where error <> '' and pcct.branch_id=$this->branch_id");
		$smarty->assign("collection_error", $con->sql_fetchrowset());
		$con->sql_freeresult();

		parent::__construct($title, $template);
	}
	
    function _default(){
		global $smarty,$con;

		if (!isset($_REQUEST['date_select']) || $_REQUEST['date_select'] == '')
		{
			$_REQUEST['date_select'] = date('Y-m-d');
		}

		// show counter collection data
		$this->show($_REQUEST['date_select'], 'open');
	}
	
	function view_by_date(){
		$this->_default();
	}
	
	private function show($date, $mode = 'view'){
	    global $con, $smarty, $pos_config, $sessioninfo;
	    
		if($mode = 'open'){
            $smarty->assign("allow_edit", $this->is_approval);
		}
		
		// load data
		$this->list_data($date);
		
		// got errors
		if($this->err)  $smarty->assign('err', $this->err);
		
		$this->calculate_total_data();
		//print_r($this->data);
		//print_r($this->total);
		//print_r($this->pos_cash_domination);
		//print_r($pos_config['currency']);
		//if($sessioninfo['u']=='wsatp')	print_r($this->currency_data);
		$smarty->assign('total', $this->total);
		$smarty->assign('data', $this->data);
		$smarty->assign('currency_data', $this->currency_data);
		$smarty->assign('pos_cash_domination', $this->pos_cash_domination);
		$this->display();
	}
	
	private function list_data($date){
        global $con, $smarty, $config, $pos_config;
        
        if(!$date)  $this->err[] = "Invalid Date";
        $date_time = strtotime($date);
        
        if($this->err)  return;
        //  check finalize status
        if ($this->check_finalized($date))	$smarty->assign("is_finalized",1);
        
        $filter = array();
        $filter[] = "p.branch_id=$this->branch_id and p.date=".ms($date);
        $filter = join(" and ", $filter);
        
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
		
		if ($pos_counters){
            asort($pos_counters);   // sort array base on counter name
            
            foreach($pos_counters as $counter_id=>$cname){
                $counter_version = get_counter_version($this->branch_id, $counter_id);
                
                // get min & max pos time
            	$con->sql_query("select min(p.pos_time) as s , max(p.pos_time) as e
				from pos p
				where $filter and p.counter_id=".mi($counter_id));
				
				$r = $con->sql_fetchrow();
				$counter_whole_day_start = $r['s'];
				$counter_whole_day_end = $r['e'];
				$con->sql_freeresult();

				// get min & max pos cash domination time
				$con->sql_query("select min(p.timestamp) as s , max(p.timestamp) as e
				from pos_cash_domination p
				where $filter and clear_drawer=1 and p.counter_id=".mi($counter_id));
    
				$r = $con->sql_fetchrow();
				if($r['e']&&$r['s']){
				    if($counter_version<109 || $date_time < $this->v109_time){
                        $r['e'] = date("Y-m-d H:i:s", strtotime("+8 hour", strtotime($r['e'])));
						$r['s'] = date("Y-m-d H:i:s", strtotime("+8 hour", strtotime($r['s'])));
					}

					if((strtotime($r['s'])<strtotime($counter_whole_day_start)||!$counter_whole_day_start)&&$r['s'])	$counter_whole_day_start = $r['s'];
					if((strtotime($r['e'])>strtotime($counter_whole_day_end)||!$counter_whole_day_end)&&$r['e'])	$counter_whole_day_end = $r['e'];
				}
				$con->sql_freeresult();
				
				// change whole day end to last pos
				$con->sql_query("select max(pos_time) from pos p where $filter and p.counter_id=".mi($counter_id));
				$max_pos_time = $con->sql_fetchfield(0);
				$con->sql_freeresult();
				
				if(strtotime($max_pos_time)>strtotime($counter_whole_day_end))  $counter_whole_day_end = $max_pos_time;
				

				// get min & max pos cash advance time
				$con->sql_query("select min(p.timestamp) as s , max(p.timestamp) as e
				from pos_cash_history p
				where $filter and p.counter_id=".mi($counter_id));
				
				$r = $con->sql_fetchrow();
				if($r['e']&&$r['s']){
                    if((strtotime($r['s'])<strtotime($counter_whole_day_start)||!$counter_whole_day_start)&&$r['s'])	$counter_whole_day_start = $r['s'];
					if((strtotime($r['e'])>strtotime($counter_whole_day_end)||!$counter_whole_day_end)&&$r['e'])	$counter_whole_day_end = $r['e'];
				}
				
				$con->sql_freeresult();

                // get pos cash domination list for "counter collection"
                $sql = "select * from pos_cash_domination p where $filter and clear_drawer=1 and counter_id=".mi($counter_id)." order by timestamp";
                //print $sql;
                $con->sql_query($sql);
                
                $start_time = $counter_whole_day_start;
                while($r = $con->sql_fetchrow()){
                    // add 8 hours due to frontend time bugs
	                if($counter_version<109 || $date_time < $this->v109_time){
                        $r['timestamp'] = date("Y-m-d H:i:s", strtotime("+8 hour", strtotime($r['timestamp'])));
	                }
                    
                    $r['start_time'] = $start_time;
                    $r['end_time'] = $r['timestamp'];
                    $r['data'] = unserialize($r['data']);
                    $r['odata'] = unserialize($r['odata']);
                    $r['curr_rate'] = unserialize($r['curr_rate']);
                    $r['ocurr_rate'] = unserialize($r['ocurr_rate']);
                    
					$this->pos_cash_domination[$counter_id][$r['id']] = $r;
					
					$start_time = date("Y-m-d H:i:s", strtotime("+1 second", strtotime($r['end_time'])));
				}
				$con->sql_freeresult();
				
				if(!$this->pos_cash_domination[$counter_id]){   // no pos cash domination found
                    $this->pos_cash_domination[$counter_id][0]['start_time'] = $counter_whole_day_start;
                    $this->pos_cash_domination[$counter_id][0]['end_time'] = $counter_whole_day_end;
				}
				
				// get sales data
				$this->generate_data($counter_id, $date);
				// if got counter session which have no pos cash domination
				if(isset($this->data[$counter_id]['']))
				    $this->pos_cash_domination[$counter_id]['']['end_time'] = $counter_whole_day_end;
			}
		} 
		//print_r($this->pos_cash_domination);
	}
	
	private function check_finalized($date){
		global $con;
		//$con->sql_query("select count(*) as count from pos_counter_collection_tracking where branch_id = ".mi($this->branch_id)." and date = ".ms(dmy_to_sqldate($date))." and finalized = 1 group by date");
		$con->sql_query("select * from pos_finalized where branch_id=".mi($this->branch_id)." and date=".ms($date)." and finalized=1");
		if ($con->sql_numrows()>0)
			return true;
		else
			return false;
	}
	
	private function get_pos_cash_domination_id($counter_id, $timestamp){
		if(!$this->pos_cash_domination[$counter_id])    return 0;
		foreach($this->pos_cash_domination[$counter_id] as $dom_id=>$r){
			if(strtotime($timestamp)>=strtotime($r['start_time'])&&strtotime($timestamp)<=strtotime($r['end_time']))
			    return $dom_id; // return the domination ID which the timestamp belongs to
		}
		
	}
	
	private function generate_data($counter_id, $date){
        global $con, $pos_config;
        
        $counter_id = mi($counter_id);
		$filter = "p.branch_id=$this->branch_id and p.date=".ms($date)." and p.counter_id=$counter_id";
		
		// get from pos first
		$sql = "select p.* from pos p where $filter and p.cancel_status=0 order by pos_time";
		//print $sql."<br />";
		$q_pos = $con->sql_query($sql);
		while($pos = $con->sql_fetchrow($q_pos)){
		    // assign pos_id
		    $pos_id = mi($pos['id']);
		    
            // get counter domination id
			$dom_id = $this->get_pos_cash_domination_id($counter_id, $pos['pos_time']);

		    // get from pos payment
		    $q_pp = $con->sql_query("select type, remark, changed, adjust, amount from pos_payment p where $filter and pos_id=$pos_id");
		    while($pp = $con->sql_fetchrow($q_pp)){
		        $is_changed = mi($pp['changed']);
		        $is_adjust = mi($pp['adjust']);
		        $row_type = 'cashier_sales';
		        $add_to_nett_sales = true;
		        if($is_changed){
                    $row_type = 'adj';
                    $add_to_nett_sales = false;
				} 

				// is one of the credit cards
	            if (in_array($pp['type'], $pos_config['credit_card'])) $payment_type = 'Credit Cards';
	            else	$payment_type = $pp['type'];

                $is_foreign_currency = false;
                // check payment type
                switch ($payment_type){
					case 'Discount':    // it is discount
					    $rm_amt = $pp['amount']*-1;  // discount show as negative
					    $add_to_nett_sales = false;
					    break;
					case 'Cash':    // it is cash payment
					    if($is_adjust)  $rm_amt = $pp['amount'];
						else	$rm_amt = $pp['amount'] - $pos['amount_change'];    // amount decrease changed
					    break;
					default:
					    // check is foreign currency
			            $currency_arr = $this->pp_is_currency($pp['remark'], $pp['amount']);
			            if($currency_arr['is_currency']){   // it is foreign currency
							$currency_amt = $currency_arr['currency_amt'];
							$currency_rate = $currency_arr['currency_rate'];
							$is_foreign_currency = true;
						}
						$rm_amt = $currency_arr['rm_amt'];
					    break;
				}

				// row type is either 'cashier_sales' or 'adj'
				if($is_foreign_currency){   // is foreign currency
	                $this->data[$counter_id][$dom_id][$row_type]['foreign_currency'][$payment_type]['foreign_amt'] += $currency_amt;
	                $this->data[$counter_id][$dom_id][$row_type]['foreign_currency'][$payment_type]['rm_amt'] += $rm_amt;

                    //$this->total[$counter_id][$row_type]['foreign_currency'][$payment_type]['foreign_amt'] += $currency_amt;
                    //$this->total[$counter_id][$row_type]['foreign_currency'][$payment_type]['rm_amt'] += $rm_amt;

	                $this->currency_data[$payment_type]['currency_rate'][$currency_rate][$row_type]['foreign_amt'] += $currency_amt;
	                $this->currency_data[$payment_type]['currency_rate'][$currency_rate][$row_type]['rm_amt'] += $rm_amt;
				}else{
					$this->data[$counter_id][$dom_id][$row_type][$payment_type]['amt'] += $rm_amt;
				}
				
				// adjustment
				if($is_adjust){
                    if($is_foreign_currency){   // is foreign currency
		                $this->data[$counter_id][$dom_id]['adj']['foreign_currency'][$payment_type]['foreign_amt'] += $currency_amt*-1;
		                $this->data[$counter_id][$dom_id]['adj']['foreign_currency'][$payment_type]['rm_amt'] += $rm_amt*-1;

	                    //$this->total[$counter_id]['adj']['foreign_currency'][$payment_type]['foreign_amt'] += $currency_amt*-1;
	                    //$this->total[$counter_id]['adj']['foreign_currency'][$payment_type]['rm_amt'] += $rm_amt*-1;

		                $this->currency_data[$payment_type]['currency_rate'][$currency_rate]['cashier_sales']['foreign_amt'] += $currency_amt*-1;
		                $this->currency_data[$payment_type]['currency_rate'][$currency_rate]['cashier_sales']['rm_amt'] += $rm_amt*-1;
					}else{
						$this->data[$counter_id][$dom_id]['adj'][$payment_type]['amt'] += $rm_amt*-1;
						//$this->total[$counter_id]['adj'][$payment_type]['amt'] += $rm_amt*-1;
					}
					//$this->data[$counter_id][$dom_id]['adj']['nett_sales']['amt'] += $rm_amt*-1;
				}

				if($add_to_nett_sales){
                    //$this->data[$counter_id][$dom_id][$row_type]['nett_sales']['amt'] += $rm_amt;
					//$this->total[$counter_id]['cashier_sales']['nett_sales']['amt'] += $rm_amt;
					//$this->total['total']['nett_sales']['amt'] += $rm_amt;
				}
				
				
			}
			
			// last cashier
			$this->data[$counter_id][$dom_id]['last_cashier_id'] = $pos['cashier_id'];
		    
		    // over
		    $pos_amt = $pos['amount'];
			$real_receipt_amt = $pos['amount_tender'] - $pos['amount_change'];
			$over_amt = mf($real_receipt_amt-$pos_amt);
			if($over_amt){
                $this->data[$counter_id][$dom_id]['cashier_sales']['Over']['amt'] += $over_amt;
                //$this->total[$counter_id]['cashier_sales']['over']['amt'] += $over_amt;
			}
		}
		
		// cash advance
	    $q_pch = $con->sql_query("select * from pos_cash_history p where $filter");
	    while($pch = $con->sql_fetchrow($q_pch)){
	        $dom_id = $this->get_pos_cash_domination_id($counter_id, $pch['timestamp']);
	        
	        $currency_arr = $this->pch_is_currency($pch['remark'], $pch['amount']);
	        $rm_amt = $currency_arr['rm_amt'];
	        
			if($currency_arr['is_currency']){
			    $currency_type = $currency_arr['currency_type'];
			    $currency_rate = $currency_arr['currency_rate'];
			    $currency_amt = $currency_arr['currency_amt'];
			    
                $this->data[$counter_id][$dom_id]['cash_advance']['foreign_currency'][$currency_type]['foreign_amt'] += $currency_amt;
                $this->data[$counter_id][$dom_id]['cash_advance']['foreign_currency'][$currency_type]['rm_amt'] += $rm_amt;
                
                //$this->total[$counter_id]['cash_advance']['foreign_currency'][$currency_type]['foreign_amt'] += $currency_amt;
                //$this->total[$counter_id]['cash_advance']['foreign_currency'][$currency_type]['rm_amt'] += $rm_amt;
                
                // jz use it to construct currency array
                $this->currency_data[$payment_type]['currency_rate'][$currency_rate]['cash_advance']['foreign_amt'] += $currency_amt;
                $this->currency_data[$payment_type]['currency_rate'][$currency_rate]['cash_advance']['rm_amt'] += $rm_amt;
			}else{
                $this->data[$counter_id][$dom_id]['cash_advance']['Cash']['amt'] += $rm_amt;
                //$this->total[$counter_id]['cash_advance']['cash']['amt'] += $rm_amt;
			}
			$this->data[$counter_id][$dom_id]['cash_advance']['nett_sales']['amt'] += $rm_amt;
			//$this->total['total']['cash_advance']['amt'] += $rm_amt;
		}
		
		$pos_cash_domination_notes = array_flip($pos_config['cash_domination_notes']);
		// pos cash domination
		//print_r($this->data[$counter_id]['pos_cash_domination']);
        if($this->pos_cash_domination[$counter_id]){
			foreach($this->pos_cash_domination[$counter_id] as $dom_id=>$r){
				if(!$r['data']&&!$r['odata']) continue;
				
				// latest data
				if($r['data']){
                    if($r['curr_rate']){
						$curr_rate = $r['curr_rate'];  // use cash domination currency rate
					}
					else{
						$curr_rate = $pos_config['curr_rate'];  // use default currency rate
					}

					foreach($r['data'] as $type=>$d2){
					    $type_key = $type;
	                    if ($type_key == 'Cheque') $type_key = 'Check';

						if (in_array($type, $pos_config['credit_card']))
							$type_key = 'Credit Cards';
						elseif (in_array($type, $pos_cash_domination_notes))
						{
							$d2 = $d2 * $pos_config['cash_domination_notes'][$type];
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

	                    if($pos_config['currency']&&in_array($type_key, $pos_config['currency'])){  // is currency collection
	                        $rm_amt = mf($d2/$curr_rate[$type_key]);
	                        $this->data[$counter_id][$dom_id]['cash_domination']['foreign_currency'][$type_key]['foreign_amt'] += $d2;
			                $this->data[$counter_id][$dom_id]['cash_domination']['foreign_currency'][$type_key]['rm_amt'] += $rm_amt;

			                // jz use it to construct currency array
			                $this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['foreign_amt'] += $d2;
			                $this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['rm_amt'] += $rm_amt;
			                
			                if(strpos($type, '_Float')){    // is currency float
                                $this->data[$counter_id][$dom_id]['cash_domination']['foreign_currency'][$type_key]['Float']['foreign_amt'] += abs($d2);
							}
						}else{
						    $rm_amt = $d2;
	                        $this->data[$counter_id][$dom_id]['cash_domination'][$type_key]['amt'] += $rm_amt;
	                        
	                        if($type=='Float'){ // is cash float
                                $this->data[$counter_id][$dom_id]['cash_domination']['Float']['amt'] += abs($rm_amt);
							}
						}
						$this->data[$counter_id][$dom_id]['cash_domination']['nett_sales']['amt'] += $rm_amt;
					}
				}
				
				// original data
				if($r['odata']){
                    if($r['ocurr_rate']){
						$curr_rate = $r['ocurr_rate'];  // use cash domination currency rate
					}
					else{
						$curr_rate = $pos_config['curr_rate'];  // use default currency rate
					}
					
					foreach($r['odata'] as $type=>$d2){
					    $type_key = $type;
	                    if ($type_key == 'Cheque') $type_key = 'Check';

						if (in_array($type, $pos_config['credit_card']))
							$type_key = 'Credit Cards';
						elseif (in_array($type, $pos_cash_domination_notes))
						{
							$d2 = $d2 * $pos_config['cash_domination_notes'][$type];
							$type_key = 'Cash';
						}
						elseif ($type == 'Float')
						{
							//$type_key = 'Cash';
							$d2 *= -1;
						}

	                    if($pos_config['currency']&&in_array($type_key, $pos_config['currency'])){  // is currency collection
	                        $rm_amt = mf($d2/$curr_rate[$type_key]);

	                        $this->data[$counter_id][$dom_id]['cash_domination']['foreign_currency'][$type_key]['o_foreign_amt'] += $d2;
			                $this->data[$counter_id][$dom_id]['cash_domination']['foreign_currency'][$type_key]['o_rm_amt'] += $rm_amt;

			                // jz use it to construct currency array
			                $this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['o_foreign_amt'] += $d2;
			                $this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['o_rm_amt'] += $rm_amt;
						}else{
						    $rm_amt = $d2;
	                        $this->data[$counter_id][$dom_id]['cash_domination'][$type_key]['o_amt'] += $rm_amt;
						}
						$this->data[$counter_id][$dom_id]['cash_domination']['nett_sales']['o_amt'] += $rm_amt;
					}
				}
			}
		}
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
	
	private function calculate_total_data(){
	    global $pos_config;
	    
	    
		if(!$this->data)    return;
		foreach($this->data as $counter_id=>&$c){
			foreach($c as $dom_id=>&$r){
			    // cashier sales => nett sales
			    $r['cashier_sales']['nett_sales']['amt'] = $r['cashier_sales']['Cash']['amt']+$r['cashier_sales']['Credit Cards']['amt']+$r['cashier_sales']['Coupon']['amt']+$r['cashier_sales']['Voucher']['amt']+$r['cashier_sales']['Check']['amt'];
			    if($pos_config['currency']){
					foreach($pos_config['currency'] as $currency_type){
						$curr_payment_type = $currency_type;
						$r['cashier_sales']['nett_sales']['amt'] += $r['cashier_sales']['foreign_currency'][$curr_payment_type]['rm_amt'];
					}
				}
			    // cashier sales => gross sales
			    $r['cashier_sales']['gross_sales']['amt'] = $r['cashier_sales']['nett_sales']['amt']-$r['cashier_sales']['Discount']['amt']-$r['cashier_sales']['Rounding']['amt']-$r['cashier_sales']['Over']['amt'];
			    
			    // adjustment => nett sales
			    $r['adj']['nett_sales']['amt'] = $r['adj']['Cash']['amt']+$r['adj']['Credit Cards']['amt']+$r['adj']['Coupon']['amt']+$r['adj']['Voucher']['amt']+$r['adj']['Check']['amt'];
			    if($pos_config['currency']){
					foreach($pos_config['currency'] as $currency_type){
						$curr_payment_type = $currency_type;
						$r['adj']['nett_sales']['amt'] += $r['adj']['foreign_currency'][$curr_payment_type]['rm_amt'];
					}
				}
				
				// variance
				foreach($pos_config['normal_payment_type'] as $payment_type){
                    $r['variance'][$payment_type]['amt'] = round($r['cash_domination'][$payment_type]['amt'] - ($r['cashier_sales'][$payment_type]['amt']+$r['cash_advance'][$payment_type]['amt']+$r['adj'][$payment_type]['amt']),2);
				}
				// foreign currency variance
                if($pos_config['currency']){
					foreach($pos_config['currency'] as $currency_type){
						$curr_payment_type = $currency_type;
						$r['variance']['foreign_currency'][$curr_payment_type]['foreign_amt'] = round($r['cash_domination']['foreign_currency'][$curr_payment_type]['foreign_amt'] - ($r['cashier_sales']['foreign_currency'][$curr_payment_type]['foreign_amt']+$r['cash_advance']['foreign_currency'][$curr_payment_type]['foreign_amt']+$r['adj']['foreign_currency'][$curr_payment_type]['foreign_amt']),2);
						$r['variance']['foreign_currency'][$curr_payment_type]['rm_amt'] = round($r['cash_domination']['foreign_currency'][$curr_payment_type]['rm_amt'] - ($r['cashier_sales']['foreign_currency'][$curr_payment_type]['rm_amt']+$r['cash_advance']['foreign_currency'][$curr_payment_type]['rm_amt']+$r['adj']['foreign_currency'][$curr_payment_type]['rm_amt']),2);
					}
				}
				$r['variance']['nett_sales']['amt'] = round($r['cash_domination']['nett_sales']['amt']-($r['cashier_sales']['nett_sales']['amt']+$r['cash_advance']['nett_sales']['amt']+$r['adj']['nett_sales']['amt']),2);
				
				// total by payment type
				foreach($pos_config['normal_payment_type'] as $payment_type){
				    $this->total['payment_type'][$payment_type]['amt'] += $r['cashier_sales'][$payment_type]['amt']+$r['adj'][$payment_type]['amt'];
				}
				// total by payment type - foreign currency
				if($pos_config['currency']){
				    foreach($pos_config['currency'] as $currency_type){
						$curr_payment_type = $currency_type;
						$this->total['payment_type']['foreign_currency'][$curr_payment_type]['foreign_amt'] += $r['cashier_sales']['foreign_currency'][$curr_payment_type]['foreign_amt']+$r['adj']['foreign_currency'][$curr_payment_type]['foreign_amt'];
						$this->total['payment_type']['foreign_currency'][$curr_payment_type]['rm_amt'] += $r['cashier_sales']['foreign_currency'][$curr_payment_type]['rm_amt']+$r['adj']['foreign_currency'][$curr_payment_type]['rm_amt'];
					}
				}
				
				$this->total['payment_type']['nett_sales']['amt'] += $r['cashier_sales']['nett_sales']['amt']+$r['adj']['nett_sales']['amt'];
    			$this->total['payment_type']['Discount']['amt'] += $r['cashier_sales']['Discount']['amt']+$r['adj']['Discount']['amt'];
    			$this->total['payment_type']['Rounding']['amt'] += $r['cashier_sales']['Rounding']['amt'];
    			$this->total['payment_type']['Over']['amt'] += $r['cashier_sales']['Over']['amt'];
    			$this->total['payment_type']['gross_sales']['amt'] += $r['cashier_sales']['gross_sales']['amt'];
    			
    			// total by variance
    			foreach($pos_config['normal_payment_type'] as $payment_type){
				    $this->total['variance'][$payment_type]['amt'] += $r['variance'][$payment_type]['amt'];
				}
				// total by variance - foreign currency
				if($pos_config['currency']){
				    foreach($pos_config['currency'] as $currency_type){
						$curr_payment_type = $currency_type;
						$this->total['variance']['foreign_currency'][$curr_payment_type]['foreign_amt'] += $r['variance']['foreign_currency'][$curr_payment_type]['foreign_amt'];
						$this->total['variance']['foreign_currency'][$curr_payment_type]['rm_amt'] += $r['variance']['foreign_currency'][$curr_payment_type]['rm_amt'];
					}
				}
				
			    // grand total nett sales
				$this->total['total']['nett_sales']['amt'] += ($r['cashier_sales']['nett_sales']['amt']+$r['adj']['nett_sales']['amt']);
				// grand total advance
				$this->total['total']['cash_advance']['amt'] += $r['cash_advance']['nett_sales']['amt'];
				// grand total collection
				$this->total['total']['cash_domination']['amt'] += $r['cash_domination']['nett_sales']['amt'];
				// grand total gross sales
				$this->total['total']['gross_sales']['amt'] += $r['cashier_sales']['gross_sales']['amt'];
			}
		}
		
		if($this->currency_data){
		    // construct different currency rate array
			foreach($this->currency_data as $currency_type=>$curr_list){
			    foreach($curr_list['currency_rate'] as $curr_rate=>$curr_item){
			        $curr_item['rate'] = mf($curr_rate);
                    $this->currency_data[$currency_type]['list'][] = $curr_item;
				}
				$this->currency_data[$currency_type]['currency_rate_count'] = count($curr_list['currency_rate']);
			}
			
			// loop again to sum by different rate
			foreach($this->currency_data as $currency_type=>$curr_list){
				foreach($curr_list['list'] as $k=>$curr_item){
				    // nett sales
                    $this->currency_data[$currency_type]['list'][$k]['total']['nett_sales']['foreign_amt'] += $curr_item['cashier_sales']['foreign_amt'] + $curr_item['adj']['foreign_amt'];
                    $this->currency_data[$currency_type]['list'][$k]['total']['nett_sales']['rm_amt'] += $curr_item['cashier_sales']['rm_amt']+$curr_item['adj']['rm_amt'];
                    
                    // cash advance
                    $this->currency_data[$currency_type]['list'][$k]['total']['cash_advance']['foreign_amt'] += $curr_item['cash_advance']['foreign_amt'];
                    $this->currency_data[$currency_type]['list'][$k]['total']['cash_advance']['rm_amt'] += $curr_item['cash_advance']['rm_amt'];
                    // pos domination
                    $this->currency_data[$currency_type]['list'][$k]['total']['cash_domination']['foreign_amt'] += $curr_item['cash_domination']['foreign_amt'];
                    $this->currency_data[$currency_type]['list'][$k]['total']['cash_domination']['rm_amt'] += $curr_item['cash_domination']['rm_amt'];
                    // variance
                    $this->currency_data[$currency_type]['list'][$k]['total']['variance']['foreign_amt'] = $this->currency_data[$currency_type]['list'][$k]['total']['cash_domination']['foreign_amt']-$this->currency_data[$currency_type]['list'][$k]['total']['nett_sales']['foreign_amt']-$this->currency_data[$currency_type]['list'][$k]['total']['cash_advance']['foreign_amt'];
                    $this->currency_data[$currency_type]['list'][$k]['total']['variance']['rm_amt'] = $this->currency_data[$currency_type]['list'][$k]['total']['cash_domination']['rm_amt']-$this->currency_data[$currency_type]['list'][$k]['total']['nett_sales']['rm_amt']-$this->currency_data[$currency_type]['list'][$k]['total']['cash_advance']['rm_amt'];
                    
                    // total foreign variance
                    $this->currency_data['total']['variance']['foreign_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['variance']['foreign_amt'],2);
                    $this->currency_data['total']['variance']['rm_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['variance']['rm_amt'],2);
                    
                    // total foreign nett sales
                    $this->currency_data['total']['nett_sales']['foreign_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['nett_sales']['foreign_amt'],2);
                    $this->currency_data['total']['nett_sales']['rm_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['nett_sales']['rm_amt'],2);
                    
                    // total foreign adj
                     $this->currency_data['total']['adj']['foreign_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['adj']['foreign_amt'],2);
                    $this->currency_data['total']['adj']['rm_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['adj']['rm_amt'],2);
                    // total foreign cash_advance
                     $this->currency_data['total']['cash_advance']['foreign_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['cash_advance']['foreign_amt'],2);
                    $this->currency_data['total']['cash_advance']['rm_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['cash_advance']['rm_amt'],2);
                    
                    // total foreign cash_domination
                     $this->currency_data['total']['cash_domination']['foreign_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['cash_domination']['foreign_amt'],2);
                    $this->currency_data['total']['cash_domination']['rm_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['cash_domination']['rm_amt'],2);
				}
			}
		}
		$this->total['total']['variance']['amt'] = $this->total['total']['cash_domination']['amt'] - $this->total['total']['cash_advance']['amt'] - $this->total['total']['nett_sales']['amt'];
	}
	
	function finalize(){
        global $con, $sessioninfo, $smarty, $LANG, $config;

		// check approval
		if(!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");

		$form = $_REQUEST;

    	//get counter list those got pos
   		$con->sql_query("select counter_id from pos where branch_id=$this->branch_id and cancel_status=0 and date=".ms($form['date_select'])." group by counter_id");
		while($r1= $con->sql_fetchrow()){
		    $counter[$r1['counter_id']] = 1;
		}
		$con->sql_freeresult();
		
		// get counter list those got cash advance
   		$con->sql_query("select counter_id from pos_cash_history where branch_id=$this->branch_id and date=".ms($form['date_select'])." group by counter_id");
		while($r1= $con->sql_fetchrow()){
		    $counter[$r1['counter_id']] = 1;
		}
		$con->sql_freeresult();

		// check all counters got pos domination or not
		$no_clear_drawer=0;
		foreach ($counter as $cid => $dummy)
		{
			$rs = $con->sql_query("select * from pos_cash_domination where branch_id=$this->branch_id and date=".ms($form['date_select'])." and counter_id=".mi($cid)." and clear_drawer=1");
			if ($con->sql_numrows($rs)==0)
			{
    			$con->sql_query("select network_name from counter_settings where id=$cid and branch_id=$this->branch_id");
    			$r = $con->sql_fetchrow();
    			$arr[]=$r['network_name'];
				$no_clear_drawer++;
			}
		}

		if($no_clear_drawer>0){ // got counter no pos domination
    		header("Location: /counter_collection.php?date_select=".$form['date_select']."&msg=Counter: ".implode(" , ", $arr)." ".urlencode($LANG['COUNTER_COLLECTION_NO_CASH_DOMINATION']));
			exit;
		}

		// update as finalized
		$con->sql_query("delete from pos_counter_collection_tracking where branch_id=$this->branch_id and date=".ms($form['date_select']));
		$rs = $con->sql_query("select branch_id, date, counter_id from pos where branch_id=$this->branch_id and date = ".ms($form['date_select'])." group by counter_id");
		while ($r = $con->sql_fetchrow($rs)){
			$r['finalized'] = 1;
			$con->sql_query("insert into pos_counter_collection_tracking ".mysql_insert_by_field($r,array('branch_id','counter_id','date','finalized'))) or die(mysql_error());
		}
		$con->sql_query("replace into pos_finalized ".mysql_insert_by_field(array('branch_id'=>$this->branch_id, 'date'=>$form['date_select'],'finalized'=>1)));

		// generate sales cache
		update_sales_cache($this->branch_id, $form['date_select']);

		// send notification
		$this->send_notification($form['date_select']);

		// create flag file for cron csa report
		if ($config['csa_generate_report']){
			if (!is_dir("csa_report_cache")) mkdir("csa_report_cache",0777); //check folder
			list($year,$month,$day)=explode("-", $form['date_select']);

			$check_file="csa_report_cache/".BRANCH_CODE.".$year.".str_pad($month, 2, "0", STR_PAD_LEFT).".csa_cache";
			file_put_contents($check_file,"r");
			chmod($check_file,0777);
		}

		// insert into log
		log_br($sessioninfo['id'], 'Counter Collection', "", "Finalize Collection (Branch : ".BRANCH_CODE.", Date: $form[date_select])");

		header("Location: /counter_collection.php?date_select=".$form['date_select']."&msg=".urlencode($LANG['COUNTER_COLLECTION_FINALIZED']));
		exit;
	}
	
	function unfinalize(){
		global $con, $sessioninfo, $smarty, $LANG, $config;

		// check level and approval
		if (!privilege('CC_UNFINALIZE')&&$sessioninfo['level']<9999) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CC_UNFINALIZE', BRANCH_CODE), "/index.php");

		$form = $_REQUEST;

		// delete finalized status
		$con->sql_query("delete from pos_counter_collection_tracking where branch_id=$this->branch_id and date=".ms($form['date_select']));
		$con->sql_query("delete from pos_finalized where branch_id=$this->branch_id and date=".ms($form['date_select']));

		// insert log
		log_br($sessioninfo['id'], 'Counter Collection', "", "Un-Finalize Collection (Branch : ".BRANCH_CODE.", Date: $form[date_select])");

		// delete from sales cache
		$date = $form['date_select'];
		// update sku items cost changed
		$con->sql_query("select distinct(sku_item_id) from sku_items_sales_cache_b1 where date=".ms($date));
		$sid_list = array();
		while($r = $con->sql_fetchrow()){
			$sid_list[] = mi($r[0]);
			if(count($sid_list)>1000){
				$con->sql_query("update sku_items_cost set changed=1 where branch_id=".mi($this->branch_id)." and sku_item_id in (".join(',',$sid_list).")");
                $sid_list = array();
			}
		}
		$con->sql_freeresult();
		if($sid_list){
			$con->sql_query("update sku_items_cost set changed=1 where branch_id=".mi($this->branch_id)." and sku_item_id in (".join(',',$sid_list).")");
            $sid_list = array();
		}
		
		$con->sql_query("delete from sku_items_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
	    $con->sql_query("delete from category_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
	    $con->sql_query("delete from member_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
	    $con->sql_query("delete from pwp_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
	  	$con->sql_query("delete from dept_trans_cache_b".mi($this->branch_id)." where date=".ms($date));

		// create flag file for cron csa report
		if ($config['csa_generate_report']){
			if (!is_dir("csa_report_cache")) mkdir("csa_report_cache",0777); //check folder
			list($year,$month,$day)=explode("-", $form['date_select']);

			$check_file="csa_report_cache/".BRANCH_CODE.".$year.".str_pad($month, 2, "0", STR_PAD_LEFT).".csa_cache";
			file_put_contents($check_file,"r");
			chmod($check_file,0777);
		}
		
		header("Location: /counter_collection.php?date_select=".$form['date_select']."&msg=".urlencode($LANG['COUNTER_COLLECTION_UNFINALIZED']));
		exit;
	}
	
	function sales_details()
	{
		global $con, $smarty, $pos_config, $sessioninfo;

		if (isset($_REQUEST['type']))
		{
			if (strtolower($_REQUEST['type']) == 'credit cards')
			{
				foreach($pos_config['credit_card'] as $k)
				{
					$types[] = ms($k);
				}
				$types = join(",", $types);
			}
			else
				$types = ms($_REQUEST['type']);
			$where[] = "pp.type in ($types)";
		}

		if (isset($_REQUEST['counter_id'])){
		    $counter_id = mi($_REQUEST['counter_id']);
            $where[] = " p.counter_id = ".$counter_id;
		}	

		if (isset($_REQUEST['card_no']))
		{
			$where[] = "p.member_no = (select m.card_no from membership_history m where m.card_no = ".ms($_REQUEST['card_no'])." group by m.card_no)";

		}
		else
		{
        	if (isset($_REQUEST['branch_id']))  $bid = mi($_REQUEST['branch_id']);
        	else    $bid = mi($this->branch_id);
			$where[] = "pp.branch_id = ".$bid;
        }

		$select = "round(sum(if(type='Cash',pp.amount-p.amount_change,pp.amount)),2) as payment_amount";
		$groupby = 'group by p.id';
			
        if (isset($_REQUEST['e'])){
		    $e = $_REQUEST['e'];
		    if(!$e){    // get the max pos time
		        if($bid)	$e_filter[] = "branch_id=".mi($bid);
		        if($counter_id) $e_filter[] = "counter_id=".mi($counter_id);
		        $e_filter[] = "date=".ms($_REQUEST['date']);
		        if($e_filter)   $e_filter = "where ".join(' and ', $e_filter);
		        else    $e_filter = '';
				$con->sql_query("select max(pos_time) from pos $e_filter");
				$e = $con->sql_fetchfield(0);
				$con->sql_freeresult();
			}
            $where[] = " p.pos_time <= ".ms($e);
		}
		if (isset($_REQUEST['s'])) $where[] = "p.pos_time >= ".ms($_REQUEST['s']);

		$where[] = "pp.adjust <> 1";
		$where = implode(" and ", $where);
		$sql = "select p.branch_id,p.*, pp.remark, pp.type, pp.adjust, pp.changed, user.u ,$select from
			pos_payment pp
			left join pos p on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date= pp.date and p.id = pp.pos_id
			left join user on p.cashier_id = user.id
			where p.date = ".ms($_REQUEST['date'])."  and $where $groupby order by p.pos_time";
		//print $sql;
		$con->sql_query($sql);
		while($r = $con->sql_fetchrow()){
		    $currency_arr = $this->pp_is_currency($r['remark'], $r['payment_amount']);
            if($currency_arr['is_currency']){   // it is foreign currency
				$r['payment_amount'] = $currency_arr['rm_amt'];
			}

			$items[] = $r;
		}
		$con->sql_freeresult();

		$smarty->assign('items', $items);

		if (isset($_REQUEST['type']) && $_REQUEST['type']!="Cash"){
			//payement summary except Cash
			$p_sql = "select p.*, pp.remark, pp.type, sum(pp.amount) as payment_amount from
				pos_payment pp
				left join pos p on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date= pp.date and p.id = pp.pos_id
				where p.date = ".ms($_REQUEST['date'])." and p.cancel_status=0 and $where group by p.receipt_no order by pp.remark";

			$con->sql_query($p_sql);
			while($p = $con->sql_fetchrow()){
				$payment_type[$p['remark']]['payment_amount'] += $p['payment_amount'];
				$receipt_no[$p['remark']][$p['receipt_no']]['pos_id']=$p['id'];
				$receipt_no[$p['remark']][$p['receipt_no']]['cashier_id']=$p['cashier_id'];
				$receipt_no[$p['remark']][$p['receipt_no']]['counter_id']=$p['counter_id'];
				$receipt_no[$p['remark']][$p['receipt_no']]['branch_id']=$p['branch_id'];
				$receipt_no[$p['remark']][$p['receipt_no']]['payment_amount']+=$p['payment_amount'];;
			}
			$con->sql_freeresult();

			foreach ($receipt_no as $remark=> $ri){
			    $payment_type[$remark]['rowspan'] = count($ri);
			}
			
	        $smarty->assign('payment_type', $payment_type);
	        $smarty->assign('receipt_no', $receipt_no);
		}

		$smarty->display('counter_collection.sales_details.tpl');
	}
	
	function cancel_receipt(){
		global $con, $smarty;

		if ($_REQUEST['fsubmit'])
		{
			$con->sql_query("select * from pos where branch_id=$this->branch_id and date = ".ms($_REQUEST['date'])." and counter_id=".mi($_REQUEST['counter_id'])." and receipt_no=".mi($_REQUEST['receipt_no']));
			while ($r = $con->sql_fetchrow())
			{
				$items[] = $r;
			}
			$smarty->assign("items",$items);
		}
		$this->display('counter_collection.cancel_receipt.tpl');
	}
	
	function ajax_cancel_receipt()
	{
		global $con, $smarty, $LANG, $sessioninfo;

		/*$con->sql_query("select * from pos_counter_collection_tracking where branch_id = ".mi($this->branch_id)." and date = ".ms(dmy_to_sqldate($_REQUEST['date']))." and finalized = 1 ");
		if ($con->sql_numrows()>0)
		{
			print "Error: ".$LANG['COUNTER_COLLECTION_FINALIZED_CANNOT_UPDATE'];
			exit;
		}*/
		if($this->check_finalized($_REQUEST['date'])){
            print "Error: ".$LANG['COUNTER_COLLECTION_FINALIZED_CANNOT_UPDATE'];
			exit;
		}
		$con->sql_query("update pos set cancel_status = ".mb($_REQUEST['v'])." where branch_id=$this->branch_id and date = ".ms($_REQUEST['date'])."  and counter_id=".mi($_REQUEST['counter_id'])." and id=".mi($_REQUEST['pos_id']));

		if ($con->sql_affectedrows()>0)
		{
			log_br($sessioninfo['id'], 'Counter Collection Cancel Receipt', $_REQUEST['receipt_no'], "$msg (Date: $_REQUEST[date], Counter ID: $_REQUEST[counter_id], Receipt No: $_REQUEST[receipt_no])");
			if ($_REQUEST['v'] == 1)
			{
				print $LANG['COUNTER_COLLECTION_RECEIPT_CANCELLED'];
			}
			else
			{
				print $LANG['COUNTER_COLLECTION_RECEIPT_UNCANCELLED'];
			}

		}
		else
		{
			print $LANG['COUNTER_COLLECTION_RECEIPT_NO_CHANGES'];
		}
	}
	
	function item_details(){
		global $con,$smarty,$pos_config,$sessioninfo;

		if (isset($_REQUEST['branch_id']))
			$branch_id = $_REQUEST['branch_id'];
		else
			$branch_id = $this->branch_id;


		$con->sql_query("select p.counter_id, u.u as cashier_name, p.receipt_no, p.pos_time, p.member_no, pi.pos_id, 
						 amount_change, pi.qty, pi.price, pi.discount, pi.barcode, si.mcode, si.sku_item_code, si.description
						 from pos p
						 left join pos_items pi on p.branch_id = pi.branch_id and p.counter_id = pi.counter_id and p.date= pi.date and p.id = pi.pos_id
						 left join sku_items si on pi.sku_item_id = si.id
						 left join user u on u.id = p.cashier_id
						 where p.branch_id = ".mi($branch_id)." and p.date = ".ms($_REQUEST['date'])." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.id = ".mi($_REQUEST['pos_id']));
		$items = $con->sql_fetchrowset();

		$smarty->assign('items',$items);

		$smarty->assign("amount_change", $items[0]['amount_change']);

		$con->sql_query("select * from pos_payment where branch_id = ".mi($branch_id)." and date = ".ms($_REQUEST['date'])." and counter_id = ".mi($_REQUEST['counter_id'])." and pos_id=".mi($items[0]['pos_id'])." and adjust <> 1");

		while($r = $con->sql_fetchrow()){
            $currency_arr = $this->pp_is_currency($r['remark'], $r['amount']);
            if($currency_arr['is_currency']){   // it is foreign currency
				$r['amount'] = $currency_arr['rm_amt'];
			}
			
			$payment[] = $r;
		}

        $smarty->assign('payment',$payment);
		$smarty->display('counter_collection.item_details.tpl');
	}
	
	function change_advance(){
		global $con, $smarty, $config, $LANG, $pos_config;

		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");

		$con->sql_query("select * from pos_cash_history where branch_id=$this->branch_id and counter_id = ".mi($_REQUEST['counter_id'])." and date=".ms($_REQUEST['date'])." and timestamp>=".ms($_REQUEST['s'])." and timestamp<=".ms($_REQUEST['e']));
		$items = $con->sql_fetchrowset();
		$smarty->assign("items", $items);
		$smarty->display('counter_collection.change_advance.tpl');
	}
	
	function save_change_advance(){
		global $sessioninfo,$con,$LANG;

		$update = 0;
		$form = $_REQUEST;
		$form['branch_id'] = $this->branch_id;
		$form['user_id'] = $sessioninfo['id'];
		$form['collected_by'] = $sessioninfo['id'];
		$form['type'] = 'ADVANCE';
		$form['timestamp'] = $_REQUEST['e'];

		foreach($_REQUEST['amount'] as $n => $amt){
			$id = intval($_REQUEST['id'][$n]);
			$form['amount'] = $amt;
			if ($id>0)
			{
				$con->sql_query("update pos_cash_history set amount = ".mf($amt)." where id = ".mi($_REQUEST['id'][$n])." and date = ".ms($_REQUEST['date'])." and branch_id = ".$this->branch_id." and counter_id = ".$_REQUEST['counter_id']) or die(mysql_error());
				$con->sql_query("select * from pos_cash_history where date = ".ms($_REQUEST['date'])." and branch_id = ".$this->branch_id." and counter_id = ".mi($_REQUEST['counter_id'])." and id = ".$id);
				$r = $con->sql_fetchrow();
				$form['oamount'] = $r['oamount'];

			}
			else
			{
				$form['oamount'] = 0;
				$con->sql_query("insert into pos_cash_history ".mysql_insert_by_field($form,array('branch_id','counter_id','date','user_id','collected_by','type','amount','timestamp'))) or die(mysql_error());
			}
			if ($con->sql_affectedrows()>0)
			{
				if ($id <= 0) $id = $con->sql_nextid();
				$msg[] = 'RM '.$form['oamount']." to RM $amt";
				$update++;
			}
		}

		$msg = join(",",$msg);

		if ($update > 0)
		{
			log_br($sessioninfo['id'], 'Counter Collection', "", "Change Advance $msg");
			header("Location: /counter_collection.php?date_select=".$form['date']."&msg=".urlencode($LANG['COUNTER_COLLECTION_UPDATED']));
		}
		else
		header("Location: /counter_collection.php?date_select=".$form['date']."&msg=".urlencode($LANG['COUNTER_COLLECTION_NOT_UPDATED']));
	}
	
	function change_payment_type(){
		global $con,$smarty, $config,$LANG, $pos_config;

		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");

		foreach($pos_config['issuer_identifier'] as $ii)
		{
			$cc[$ii[0]] = 1;
		}

		$cash_credit = array_keys($cc);
		$cash_credit[] = 'Cash';
		$cash_credit[] = 'Check';
        $cash_credit[] = 'Others';
        
		$coupon_voucher[] = 'Coupon';
		$coupon_voucher[] = 'Voucher';

		$rs = $con->sql_query("select p.receipt_no from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and pt.adjust = 1  and p.pos_time >= ".ms($_REQUEST['s'])." and p.pos_time <= ".ms($_REQUEST['e'])." group by receipt_no");
		while ($r = $con->sql_fetchrow($rs))
		{
			$receipt_no[] = $r['receipt_no'];
		}

		if ($receipt_no)
		{
			foreach($receipt_no as $rno)
			{
				$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and p.receipt_no = ".mi($rno)." and changed = 1");

				while($r = $con->sql_fetchrow())
				{
					$items[$r['receipt_no']][] = $r;
				}
				$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and p.receipt_no = ".mi($rno)." and adjust = 1");

				while($r = $con->sql_fetchrow())
				{
					$oitems[$r['receipt_no']][] = $r;
				}
			}
		}

		$smarty->assign('PAGE_TITLE','Change Payment Type');
		$smarty->assign("all_items",$items);
		$smarty->assign("oitems",$oitems);

		foreach ($pos_config['payment_type'] as $pt)
		{
			if ($pt != 'Credit Cards')
			$payment_type[] = $pt;
		}

		$ptcheck = array_keys($cc);
		$ptcheck[] = 'Others';
		$ptcheck[] = 'Coupon';
		$ptcheck[] = 'Voucher';

		$payment_type = array_merge($payment_type,array_keys($cc));
		$payment_type[] = 'Others';
		
		$smarty->assign("credit_cards", $ptcheck);
		$smarty->assign("coupon_voucher",$coupon_voucher);
		$smarty->assign("cc", $cash_credit);
		$smarty->assign("payment_type",$payment_type);

		$smarty->display('counter_collection.change_payment.tpl');
	}
	
	function save_change_payment(){
		global $con,$smarty,$LANG, $sessioninfo;

		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");

		$form = $_REQUEST;
		$update = 0;
		if ($form['type'])
		{
			foreach($form['type'] as $receipt_no => $t)
			{

				$items = array();
				$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and p.receipt_no = ".mi($receipt_no)." and changed <> 1 and pt.type <> 'Rounding'");

				while ($r = $con->sql_fetchrow())
				{
					$pos_id = $r['id'];
					$oitems += $r['payment_amount'];

				}
				$all_trans[$pos_id] = 1;

				foreach($t as $idx => $ty)
				{
					$item['type'] = $ty;
					$item['remark'] = $_REQUEST['remark'][$receipt_no][$idx];
					$item['amount'] = $_REQUEST['amount'][$receipt_no][$idx];

					$con->sql_query("update pos_payment set amount = ".mf($item['amount'])." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($idx));

					$nitem += $item['amount'];
					$items[] = $item;
				}
//check is amount more than original amount

				if (floatval($nitem) > floatval($oitems))
				{
					header("Location: /counter_collection.php?a=change_payment_type&cashier_id=".$form['cashier_id']."&counter_id=".$form['counter_id']."&date=".$form['date']."&msg=Total amount cannot more than original amount");
					exit;
				}
					$con->sql_query("delete from pos_payment where pos_id = ".mi($pos_id)." and branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and changed = 1 and type <> 'Cancel'");
					$con->sql_query("update pos_payment set adjust = 1 where pos_id = ".mi($pos_id)." and branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and type <> 'Cancel'");
				foreach ($items as $item)
				{
					$item['branch_id'] = $this->branch_id;
					$item['counter_id'] = $_REQUEST['counter_id'];
					$item['pos_id'] = $pos_id;
					$item['date'] = $_REQUEST['date'];
					$item['changed'] = 1;


					$con->sql_query("select max(id) as id from pos_payment where branch_id = ".mi($item['branch_id'])." and counter_id = ".mi($item['counter_id'])." and date = ".ms($item['date'])." group by date");
					$r = $con->sql_fetchrow();
					$item['id'] = $r['id']+1;
					$con->sql_query("insert into pos_payment ".mysql_insert_by_field($item,array('branch_id','counter_id','id','pos_id','date','type','remark','amount','changed')));
					$update++;
				}

				$amt = 0;

				$con->sql_query("select * from pos_payment where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and pos_id = ".mi($pos_id)." and adjust = 1 and type <> 'Rounding'");

				while($r = $con->sql_fetchrow())
				{
					$amt += $r['amount'];
				}
				$con->sql_query("select * from pos where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($pos_id));
				$pos_header = $con->sql_fetchrow();

				$con->sql_query("update pos set amount_tender = ".mf($amt).", amount_change = ".floatval($pos_header['amount']-$amt)." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($pos_id));
			}

		}
		if  (is_array($all_trans))$all_trans  = join(",", array_keys($all_trans));
		log_br($sessioninfo['id'], 'Counter Collection', "", "Change Payment Type (Pos ID: $all_trans)");

		if ($update > 0)
		header("Location: /counter_collection.php?date_select=".$form['date']."&msg=".urlencode($LANG['COUNTER_COLLECTION_UPDATED']));
		else
		header("Location: /counter_collection.php?date_select=".$form['date']."&msg=".urlencode($LANG['COUNTER_COLLECTION_NOT_UPDATED']));
 	}
 	
 	function ajax_add_receipt_row(){
		global $con, $smarty, $pos_config,$LANG;

		foreach ($pos_config['payment_type'] as $pt)
		{
			if ($pt != 'Credit Cards')
			$payment_type[] = $pt;
		}

		foreach($pos_config['issuer_identifier'] as $ii)
		{
			$cc[$ii[0]] = 1;
		}
		$ptcheck = array_keys($cc);
		$ptcheck[] = 'Others';
		$ptcheck[] = 'Coupon';
		$ptcheck[] = 'Voucher';
		$payment_type = array_merge($payment_type,array_keys($cc));

		$payment_type[] = 'Others';
		$cash_credit = array_keys($cc);
		$cash_credit[] = 'Cash';
		$cash_credit[] = 'Check';
        $cash_credit[] = 'Others';
        
		$coupon_voucher[] = 'Coupon';
		$coupon_voucher[] = 'Voucher';

		$smarty->assign("coupon_voucher",$coupon_voucher);
		$smarty->assign("cc", $cash_credit);
		$smarty->assign("payment_type",$payment_type);
		$smarty->assign("credit_cards", $ptcheck);

		$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where pt.type <> 'Cancel' and pt.type <> 'Rounding' and p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.receipt_no = ".mi($_REQUEST['receipt_no'])." and pt.adjust = 0 and p.pos_time >= ".ms($_REQUEST['s'])." and p.pos_time <= ".ms($_REQUEST['e'])." order by p.pos_time");
		if ($con->sql_numrows()>0)
		{
			while($item = $con->sql_fetchrow())
			{
				if ($item['cancel_status'] == 0)
				{
					$items[] = $item;
					$oitems[$item['receipt_no']][]=$item;
				}
			}
			$smarty->assign("receipt_no",$_REQUEST['receipt_no']);
			$smarty->assign("items",$items);
			$smarty->assign("oitems",$oitems);
			print $smarty->fetch('counter_collection.change_payment.row.tpl');
		}
		else
		print $LANG['COUNTER_COLLECTION_INVAILD_RECEIPT'];
	}
	
	function change_x(){
		global $con,$smarty, $LANG, $pos_config;

		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");

		$con->sql_query("select * from pos_cash_domination where date = ".ms($_REQUEST['date'])." and branch_id =".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and id = ".mi($_REQUEST['id']));

		$item = $con->sql_fetchrow();
		$item['data'] = unserialize($item['data']);
		$item['odata'] = unserialize($item['odata']);
		$item['curr_rate'] = unserialize($item['curr_rate']);
		$item['ocurr_rate'] = unserialize($item['ocurr_rate']);

		if (isset($item['data']['Cheque']))
		{
			$item['data']['Check'] = $item['data']['Cheque'];
			unset($item['data']['Cheque']);
		}

		if (isset($item['odata']['Cheque']))
		{
			$item['odata']['Check'] = $item['odata']['Cheque'];
			unset($item['odata']['Cheque']);
		}
		//print_r($item);
		$smarty->assign('PAGE_TITLE','Counter Collection Change X-Figure');
		$smarty->assign('item',$item);
		$smarty->display('counter_collection.change_x.tpl');
	}
	
	function save_cash_domination(){
        global $con, $sessioninfo, $LANG;
        
		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");

		$_REQUEST['data'] = serialize($_REQUEST['data']);
		$_REQUEST['curr_rate'] = serialize($_REQUEST['curr_rate']);


		if (intval($_REQUEST['id'])>0)
		{
			$con->sql_query("select * from pos_cash_domination where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($_REQUEST['id']));

			if ($con->sql_numrows()>0)
			{
				$r = $con->sql_fetchrow();

				if ($r['odata'] == '') $con->sql_query("update pos_cash_domination set odata = ".ms($r['data']).", ocurr_rate =".ms($r['curr_rate'])." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($_REQUEST['id']));
				if ($r['ocurr_rate'] == '') $con->sql_query("update pos_cash_domination set ocurr_rate =".ms($r['curr_rate'])." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($_REQUEST['id']));

				$con->sql_query("update pos_cash_domination set data = ".ms($_REQUEST['data']).", curr_rate =".ms($_REQUEST['curr_rate']).", clear_drawer = ".mi($_REQUEST['clear_drawer'])." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($_REQUEST['id']));

				log_br($sessioninfo['id'], 'Counter Collection', mi($_REQUEST['id']), "Change Cash Denomination (ID: ".mi($_REQUEST['id']).")");
				header("Location: /counter_collection.php?date_select=".$_REQUEST['date']."&msg=".urlencode("X Updated"));
			}
			else
			header("Location: /counter_collection.php?date_select=".$_REQUEST['date']."&msg=".urlencode("Invalid X"));
		}
		else
		{
			$con->sql_query("select  max(id) as id from pos_cash_domination where branch_id = ".$this->branch_id." and date = ".ms($_REQUEST['date'])." and counter_id = ".mi($_REQUEST['counter_id']));
			$r = $con->sql_fetchrow();
   			$id = $r['id']+1;
   			
   			$con->sql_query("select  max(pos_time) as e from pos where branch_id=".$this->branch_id." and date=".ms($_REQUEST['date'])." and counter_id=".mi($_REQUEST['counter_id'])." and cancel_status=0");
			$max_pos = $con->sql_fetchrow();

			$timestamp = date("Y-m-d H:i:s",1+strtotime($max_pos['e'])-(8*60*60));
            
			$con->sql_query("insert into pos_cash_domination (branch_id,id,counter_id,user_id,data,timestamp,date,clear_drawer,curr_rate) values (".$this->branch_id.",".mi($id).",".mi($_REQUEST['counter_id']).",".mi($_REQUEST['cashier_id']).",".ms($_REQUEST['data']).",".ms($timestamp).",".ms($_REQUEST['date']).",".mi($_REQUEST['clear_drawer']).",".ms($_REQUEST['curr_rate']).")");
			log_br($sessioninfo['id'], 'Counter Collection', mi($_REQUEST['id']), "Change Cash Denomination (ID: New)");
			header("Location: /counter_collection.php?date_select=".$_REQUEST['date']."&msg=".urlencode("X Updated"));
		}
	}
	
	function send_notification($date){
		global $con;

		$notify_users = array();
		foreach (preg_split("/\|/", $this->notify_users) as $kk)
		{
		    if ($kk) $notify_users[] = $kk;
		}

		foreach ($notify_users as $user)
		{
			send_pm($user, "Counter Collection Finalized for Branch:".BRANCH_CODE." Date: $date", "/counter_collection.php?a=view&date_select=$date&branch_id=".$this->branch_id);
		}

	}
	
	function ajax_change_cash_credit(){
		global $con,$pos_config;

		$is_cc = 0;
		$con->sql_query("select p.amount_change, pp.* from pos p left join pos_payment pp on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date = pp.date and p.id = pp.pos_id where p.branch_id = ".mi($this->branch_id)." and p.date = ".ms($_REQUEST['date'])." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.cashier_id = ".mi($_REQUEST['cashier_id'])." and p.receipt_no = ".mi($_REQUEST['receipt_no'])." and pp.id = ".mi($_REQUEST['id']));

		$item = $con->sql_fetchrow();

		if (in_array($_REQUEST['type'],$pos_config['credit_card'])) $is_cc = 1;
//		$con->sql_query("select * from pos_payment where branch_id = ".mi($this->branch_id)." and date = ".ms($_REQUEST['date'])." and counter_id = ".mi($_REQUEST['counter_id'])." and cashier_id = ".mi($_REQUEST['cashier_id'])." and receipt_no = ".mi($_REQUEST['receipt_no'])." and pp.id = ".mi($_REQUEST['id']));

		if ($item['type'] == 'Cash' && $is_cc && !$item['changed'])
		{
			print round($item['amount']-$item['amount_change'],2);
		}
		else
			print round($item['amount'],2);

	}
}

$CounterCollection = new CounterCollection ('Counter Collection','counter_collection3.tpl');
?>
