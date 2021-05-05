<?php
/*
8/10/2010 10:20:11 AM Andy
- Add "Open Price" and "Item Discount" Info.

10/7/2010 11:41:02 AM Andy
- Rewrite report to use class module.
- Cahsier dropdown change to show all users.
- Add branches filter.
- Add filter by item discount, receipt discount, cancel bills, goods return and open price.
- Fix report sometime show wrong avg transaction time.
- Slightly improve sql speed.
- Item details page change to use counter collection templates.

3/28/2011 4:24:32 PM Justin
- Added to retrieve extra info when view receipt info in item details.

3/29/2011 10:51:59 AM Justin
- Added member no information for receipt detail.

5/10/2011 5:34:50 PM Alex
- change filter_item_discount, filter_cancel_bill, filter_goods_return, filter_open_price, filter_receipt_discount to filter or

6/24/2011 5:16:22 PM Andy
- Make all branch default sort by sequence, code.

9/6/2011 3:41:48 PM Alex
- change use $con_multi

10/20/2011 5:30:52 PM Andy
- Show receipt discount & mix and match discount in cashier performance/abnormal report.

10/28/2011 11:04:04 AM Andy
- Fix user filter not working.

11/11/2011 12:24:07 PM Andy
- Fix counter collection to also show those mix and match discount which does not have discount amount. (eg: Free Voucher)

1/12/2012 10:07:32 AM Justin
- Added to count prune status.

1/8/2014 2:05 PM Fithri
- check for type (POS) when getting data for "drawer open" count

9/14/2018 5:51 PM Andy
- Fixed variances calculation wrong.
- Enhanced to hide column "Variance" if got filter.
- Enhanced the amount column to show active transaction amount and cancelled transaction amount.

10/1/2018 2:11 PM Andy
- Fixed amount calculation din't include receipt and mix & match discount.

10/4/2018 3:11 PM Andy
- Fixed amount need to deduct deposit used amount.

10/5/2018 2:19 PM Andy
- Fixed variances calculation wrong.

11/16/2018 4:06 PM Justin
- Enhanced to have Allow Cancelled Bill and Prune Bill, Allow and Count for Deleted Items.
- Bug fixed on the Allow countings where it sum up wrongly when filter or click on specific cashier.

2/24/2020 4:28 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
$maintenance->check(95);
set_time_limit(0);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
require_once('counter_collection.include.php');

class CASHIER_PERFORMANCE_REPORT extends Module{
	var $got_mm_discount = false;
	var $rcpt_filter = array();
	var $show_variances = true;
	
	function __construct($title){
		global $mm_discount_col_value, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
	    if(!isset($_REQUEST['date_from'])&&!isset($_REQUEST['date_to'])){
			$_REQUEST['date_from'] = date('Y-m-d',strtotime('-1 month',time()));
			$_REQUEST['date_to'] = date('Y-m-d');
		}

		if($_REQUEST['filter_item_discount']){
			$filter_or[] = "pi.item_discount_by>0";
			$this->rcpt_filter['filter_item_discount'] = true;
		}
		if($_REQUEST['filter_cancel_bill']){
			$filter_or[] = "p.cancel_status=1";
			$this->rcpt_filter['filter_cancel_bill'] = true;
		}
		if($_REQUEST['filter_goods_return']){
			$filter_or[] = "pi.qty<0";
			$this->rcpt_filter['filter_goods_return'] = true;
		}
		if($_REQUEST['filter_open_price']){
			$filter_or[] = "pi.open_price_by>0";
			$this->rcpt_filter['open_price_by'] = true;
		}
		if($_REQUEST['filter_receipt_discount']){
			$filter_or[] = "pprd.amount>0";
			$this->rcpt_filter['filter_receipt_discount'] = true;
		}
		if($_REQUEST['filter_mm_discount']){
			//$filter_or[] = "ppmm.type=".ms($mm_discount_col_value);
			$filter_or[] = "if((select id from pos_mix_match_usage pmm where pmm.branch_id=p.branch_id and pmm.counter_id=p.counter_id and pmm.date=p.date and pmm.pos_id=p.id limit 1)>0,1,0)=1";
			$this->rcpt_filter['filter_mm_discount'] = true;
		}

		if ($filter_or){
			$this->ext_filter =" (".join(" or ",$filter_or).") ";
			$this->show_variances = false;
		}		

		parent::__construct($title);
	}
	
	function _default(){
        $this->load_cashier();
        $this->load_branches();
        if($_REQUEST['load_report']){
			$this->load_report();
		}
        $this->display();
	}
	
	private function load_cashier(){
		global $con,$smarty,$sessioninfo,$con_multi;

		/*if(BRANCH_CODE!='HQ'){
			$filter[] = "user_privilege.branch_id=".mi($sessioninfo['branch_id']);
		}

		$filter[] = "user_privilege.privilege_code='POS_LOGIN'";
		$filter[] = "user_privilege.allowed=1";
		$filter[] = "user.active=1";

		$filter = join(' and ',$filter);

		$sql = "select user_privilege.*,user.u from user_privilege left join user on user.id=user_privilege.user_id where $filter group by user_id";*/
		$sql = "select * from user where active=1 order by u";
		//print $sql;
		$q_u = $con_multi->sql_query($sql) or die(mysql_error());
		while($r = $con_multi->sql_fetchassoc($q_u)){
			$cashier[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q_u);
		$smarty->assign('cashier',$cashier);
	}
	
	private function load_branches(){
		global $con,$smarty,$con_multi;

		$q_b = $con_multi->sql_query("select * from branch where active=1 order by sequence,code") or die(mysql_error());
		while($r = $con_multi->sql_fetchassoc($q_b)){
			$branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q_b);
		$smarty->assign('branches',$branches);
	}
	
	private function load_variance($r){
		global $con_multi, $config, $pos_config;
		
		if(count($r['day_work'])<=0){
			return 0;
		}
		
		$pos_cash_domination_notes = $config['cash_domination_notes'];
		foreach($r['day_work'] as $date){
			$dates[] = ms($date);
		}
		$user_id = intval($r['user_id']);
		$branch_id = intval($r['branch_id']);
		//$counter_id = intval($r['counter_id']);

		$filter[] = "user_id=$user_id";
		$filter[] = "branch_id=$branch_id";
		//$filter[] = "counter_id=$counter_id";
		$filter[] = "date in (".join(',',$dates).")";
		$filter = join(' and ',$filter);

		$sql = "select * from pos_cash_domination where $filter";
		//print "$sql <br>";
		/*if(!$con_multi)	$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/
		
		$q_pc = $con_multi->sql_query($sql) or die(mysql_error());
		while($r = $con_multi->sql_fetchassoc($q_pc)){
			$data = unserialize($r['data']);
			$curr_rate = unserialize($r['curr_rate']);
			if(count($data)>0){
				//print_r($data);
				foreach($data as $type=>$d2){
					if(!$d2)	continue;
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
						
					if($curr_rate[$type_key]){
						// Foreign Currency
						$rm_amt = $curr_rate[$type_key] ? round(mf($d2/$curr_rate[$type_key]), 2) : 0;
					}else{
						// Base Currency
						$rm_amt = $d2;
					}
								
					$variance += floatval($rm_amt);
				}
			}
		}
		$con_multi->sql_freeresult($q_pc);
		//$con_multi->close_connection();
		return $variance;
	}

	private function load_report(){
        global $smarty,$sessioninfo;
	    
	    $user_id = mi($_REQUEST['user_id']);
	    $date_from = $_REQUEST['date_from'];
	    $date_to = $_REQUEST['date_to'];
	    $date_diff = intval((strtotime($date_to)-strtotime($date_from))/86400);
	    $branch_id_list = array();
	    
		if(BRANCH_CODE!='HQ'){
			$branch_id_list = array($sessioninfo['branch_id']);
		}else{
			if($_REQUEST['branch_id'])  $branch_id_list = array(mi($_REQUEST['branch_id']));
			else{
				$branches = $smarty->get_template_vars('branches');
				foreach($branches as $r){
                    $branch_id_list[] = $r['id'];
				}
			}
		}
		foreach($branch_id_list as $bid){
			$this->load_branch_report($bid);
		}

		// calculate AVG data
		if($this->table){
			foreach($this->table as $bid=>$cashiers){
				foreach($cashiers as $cid=>$r){
				    if(!isset($r['u'])){ // this user should not include in report
						unset($this->table[$bid][$cid]);
						continue;
					}
				
				    // Total and AVG transaction time
				    if(!$r['tran_count2'])  continue;
					$r['avg_tran_time'] = intval($r['total_tran_time']/$r['tran_count2']);

					$this->table[$bid][$cid]['avg_tran_time'] = $r['avg_tran_time'];

                    $total_tran_time_hour = 0;
					$total_tran_time_min = intval($r['total_tran_time']/60);
					$total_tran_time_sec = intval($r['total_tran_time']%60);
					$avg_tran_time_hour = 0;
					$avg_tran_time_min = intval($r['avg_tran_time']/60);
					$avg_tran_time_sec = intval($r['avg_tran_time']%60);

					if($total_tran_time_min>60){
	                    $total_tran_time_hour = intval($total_tran_time_min/60);
	                    $total_tran_time_min = intval($total_tran_time_min%60);
					}

					if($avg_tran_time_min>60){
	                    $avg_tran_time_hour = intval($avg_tran_time_min/60);
	                    $avg_tran_time_min = intval($avg_tran_time_min%60);
					}

					$this->table[$bid][$cid]['total_tran_time_hour'] = $total_tran_time_hour;
					$this->table[$bid][$cid]['total_tran_time_min'] = $total_tran_time_min;
					$this->table[$bid][$cid]['total_tran_time_sec'] = $total_tran_time_sec;
					$this->table[$bid][$cid]['avg_tran_time_hour'] = $avg_tran_time_hour;
					$this->table[$bid][$cid]['avg_tran_time_min'] = $avg_tran_time_min;
					$this->table[$bid][$cid]['avg_tran_time_sec'] = $avg_tran_time_sec;
					// End of Total and AVG transaction time

					// AVG daily amount,qty
					$day_count = count($r['day_work']);
					if($day_count!=0){
	                    $avg_amount = $r['amount']/$day_count;
	                    $avg_cancelled_amount = $r['cancelled_amount']/$day_count;
	                    $this->table[$bid][$cid]['avg_amount'] = $avg_amount;
	                    $this->table[$bid][$cid]['avg_cancelled_amount'] = $avg_cancelled_amount;
	                    $this->table[$bid][$cid]['avg_qty'] = intval($r['tran_count2']/$day_count);
					}
					// End of AVG daily amount,qty

					// Total Variance
					if($this->show_variances){
						$r['branch_id'] = $bid;
						$this->table[$bid][$cid]['denom'] = $this->load_variance($r);
						//$this->table[$bid][$cid]['variances'] = $r['amount'] - $v;
						$this->table[$bid][$cid]['variances'] = $this->table[$bid][$cid]['denom'] - $r['amount'];
					}
					
					// End of Total Variance
				}
			}
			//print_r($this->table);
		}

		//$smarty->assign('open_price',$open_price);
		//print_r($this->table);
		
		$smarty->assign('table',$this->table);
		$smarty->assign('got_mm_discount', $this->got_mm_discount);
		$smarty->assign('show_variances', $this->show_variances);
	}
	
	private function load_branch_report($bid){
        global $smarty,$sessioninfo,$mm_discount_col_value,$con_multi,$config;

	    $user_id = mi($_REQUEST['user_id']);
	    $date_from = $_REQUEST['date_from'];
	    $date_to = $_REQUEST['date_to'];
	    $date_diff = intval((strtotime($date_to)-strtotime($date_from))/86400);

	    $filter[] = "p.branch_id=".mi($bid);
		if($user_id){
	        $filter[] = "$user_id in (p.cashier_id, pi.open_price_by, pi.item_discount_by, pprd.approved_by)";
		}
		$filter[] = "p.date between ".ms($date_from)." and ".ms($date_to);

		if ($this->ext_filter)	$filter[]=$this->ext_filter;

		$filter = join(' and ',$filter);
		
	    $sql = "select p.* ,user.u, pi.qty, pi.open_price_by, pi.item_discount_by, pi.discount, u1.u as open_price_user, u2.u as item_discount_user,pprd.amount as receipt_discount_amt, pprd.approved_by as receipt_discount_by, u_rd.u as receipt_discount_u,ppmm.amount as mm_discount_amt,if((select id from pos_mix_match_usage pmm where pmm.branch_id=p.branch_id and pmm.counter_id=p.counter_id and pmm.date=p.date and pmm.pos_id=p.id limit 1)>0,1,0) as got_mm_discount,
		
		round((select sum(pd.deposit_amount)
from pos_deposit pd
join pos_deposit_status_history pdsh on pdsh.deposit_branch_id=pd.branch_id and pdsh.deposit_pos_date=pd.date and pdsh.deposit_counter_id=pd.counter_id and pdsh.deposit_pos_id=pd.pos_id
where pdsh.branch_id=p.branch_id and pdsh.pos_date=p.date and pdsh.counter_id=p.counter_id and pdsh.pos_id=p.id and pdsh.type='USED'), 2) as deposit_used_amt

		from pos p
		left join user on p.cashier_id=user.id
		left join pos_items pi on pi.branch_id=p.branch_id and pi.date=p.date and pi.counter_id=p.counter_id and pi.pos_id=p.id
		left join user u1 on u1.id=pi.open_price_by
		left join user u2 on u2.id=pi.item_discount_by
		left join pos_payment pprd on pprd.branch_id=p.branch_id and pprd.date=p.date and pprd.counter_id=p.counter_id and pprd.pos_id=p.id and pprd.type='discount' and pprd.adjust=0
		left join user u_rd on u_rd.id=pprd.approved_by
		left join pos_payment ppmm on ppmm.branch_id=p.branch_id and ppmm.date=p.date and ppmm.counter_id=p.counter_id and ppmm.pos_id=p.id and ppmm.type=".ms($mm_discount_col_value)." and ppmm.adjust=0
		where $filter
		order by p.branch_id,p.counter_id,p.date";

		//print $sql."<br>";
		/*if(!$con_multi)	$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/
		
		$q1 = $con_multi->sql_query($sql);
		$last_pos_key = $last_pos_drawer_key = '';
		$skip_pos = array();
		
		while($r = $con_multi->sql_fetchassoc($q1)){
			$pass = true;
			
			/*if($config['enable_mix_and_match_promotion']){
				$q_pmm = $con_multi->sql_query("select id from pos_mix_match_usage where branch_id=$r[branch_id] and counter_id=$r[counter_id] and date=".ms($r['date'])." and pos_id=$r[id] limit 1");
				$r['got_mm_discount'] = $con_multi->sql_numrows($q_pmm)>0 ? 1 : 0;
				$con_multi->sql_freeresult($q_pmm);
			}*/
			
					
			// check filter
			if($this->rcpt_filter){
				$pass = false;
				if($this->rcpt_filter['filter_item_discount']){	// item discount
					if($r['item_discount_by']>0)	$pass = true;
				}
				
				if($this->rcpt_filter['filter_cancel_bill']){	// cancel bill
					if($r['cancel_status'])	$pass = true;
				}
				
				if($this->rcpt_filter['filter_goods_return']){	// goods return
					if($r['qty']<0)	$pass = true;
				}
				
				if($this->rcpt_filter['open_price_by']){	// open price
					if($r['open_price_by']>0)	$pass = true;
				}
				
				if($this->rcpt_filter['filter_receipt_discount']){	// receipt discount
					if($r['receipt_discount_amt']>0)	$pass = true;
				}
				
				if($this->rcpt_filter['filter_mm_discount']){	// receipt discount
					if($r['got_mm_discount'])	$pass = true;	
				}
			}
			
		    $pos_id = mi($r['id']);
			$bid = mi($r['branch_id']);
			$counter_id = mi($r['counter_id']);
			$date = $r['date'];
			$curr_pos_key = $bid."_".$counter_id."_".$date."_".$pos_id;
            $curr_pos_drawer_key = $bid."_".$counter_id."_".$date;

			if(!$pass){
				continue;
			}

			if($curr_pos_key!=$last_pos_key){   // only sum pos amt if it is new pos, due to left join pos_items make sql return duplicate row
             	// receipt discount
             	if($r['receipt_discount_amt']>0){
             		if(!$user_id || ($user_id && $user_id == $r['cashier_id'])){
						$this->table[$r['branch_id']][$r['cashier_id']]['receipt_discount']++;
						$this->table[$r['branch_id']][$r['cashier_id']]['receipt_discount_amt']+=round($r['receipt_discount_amt'],2); 
					}
					
					
	                // allow by
					// shut down this section cause going to do this outside of this query
	                /*if(!$user_id || ($user_id && $user_id == $r['receipt_discount_by'])){
		                if($r['receipt_discount_by']){
							$this->table[$r['branch_id']][$r['receipt_discount_by']]['user_id'] = $r['receipt_discount_by'];
					    	$this->table[$r['branch_id']][$r['receipt_discount_by']]['u'] = $r['receipt_discount_u'];
					    	$this->table[$r['branch_id']][$r['receipt_discount_by']]['allow_receipt_discount']++;
						} 
					}*/
				}
				
				$tran_time = strtotime($r['end_time'])-strtotime($r['start_time']);
				
				if(!$user_id || ($user_id && $user_id == $r['cashier_id'])){
					// mix and match
					if($r['got_mm_discount']){					
						$this->table[$r['branch_id']][$r['cashier_id']]['mm_discount']++;
						$this->table[$r['branch_id']][$r['cashier_id']]['mm_discount_amt']+=round($r['mm_discount_amt'],2);
						
						$this->got_mm_discount = true;
					}
					
					$this->table[$r['branch_id']][$r['cashier_id']]['user_id'] = $r['cashier_id'];
				    $this->table[$r['branch_id']][$r['cashier_id']]['u'] = $r['u'];
				    //$this->table[$r['branch_id']][$r['cashier_id']]['branch_id'] = $r['branch_id'];
					
					$receipt_amt = $r['amount'];
					
					// Over
					if($r['amount_tender']-$r['amount_change']>$r['amount']){
						$over_amt = ($r['amount_tender']-$r['amount_change'])-$r['amount'];
						$this->table[$r['branch_id']][$r['cashier_id']]['over'] += $over_amt;
						$receipt_amt += $over_amt;
					}
					// Receipt Discount
					if($r['receipt_discount_amt']){
						$receipt_amt -= $r['receipt_discount_amt'];
					}
					
					// Mix & Match Discount
					if($r['mm_discount_amt']){
						$receipt_amt -= $r['mm_discount_amt'];
					}
					
					// Deposit Used
					if($r['deposit_used_amt']){
						$receipt_amt -= $r['deposit_used_amt'];
					}
						
					if($r['cancel_status'] == 1){	// Cancelled
						// Total Receipt Amount
						$this->table[$r['branch_id']][$r['cashier_id']]['cancelled_amount'] += $receipt_amt;
					}else{	// Active Transaction
						// Total Receipt Amount
						$this->table[$r['branch_id']][$r['cashier_id']]['amount'] += $receipt_amt;
						
						// Transaction Count
						$this->table[$r['branch_id']][$r['cashier_id']]['tran_count']++;
						
						if($r['member_no']){
							$this->table[$r['branch_id']][$r['cashier_id']]['member_sells']['qty']++;
							$this->table[$r['branch_id']][$r['cashier_id']]['member_sells']['amount'] += $receipt_amt;
						}else{
							$this->table[$r['branch_id']][$r['cashier_id']]['non_member_sells']['qty']++;
							$this->table[$r['branch_id']][$r['cashier_id']]['non_member_sells']['amount'] += $receipt_amt;
						}
					}
					
					$this->table[$r['branch_id']][$r['cashier_id']]['day_work'][$r['date']] = $r['date'];
					
					if($tran_time<86400){
			            $this->table[$r['branch_id']][$r['cashier_id']]['total_tran_time'] += $tran_time;
			            $this->table[$r['branch_id']][$r['cashier_id']]['tran_count2'] ++;
					}
				}
	
                $last_pos_key = $curr_pos_key;
			}

			if($curr_pos_drawer_key!=$last_pos_drawer_key){
			    $q_pos_drawer = $con_multi->sql_query("select pd.user_id, count(*) as drawer_open_count
				from pos_drawer pd
				left join user on user.id=pd.user_id
				where pd.branch_id=$bid and pd.counter_id=$counter_id and pd.date=".ms($date)." ".($user_id>0? " and pd.user_id=$user_id" : '')." and pd.type = 'POS' 
				group by pd.user_id");
				while($pd = $con_multi->sql_fetchassoc($q_pos_drawer)){
                    $this->table[$bid][$pd['user_id']]['drawer_open_count'] += $pd['drawer_open_count'];
				}
				$con_multi->sql_freeresult($q_pos_drawer);
                $last_pos_drawer_key = $curr_pos_drawer_key;
			}

			if(!$user_id || ($user_id && $user_id == $r['cashier_id'])){
	            if($r['qty']<0){    // goods return
	                $this->table[$r['branch_id']][$r['cashier_id']]['total_goods_return'] += abs($r['qty']);
				}
			}

			if($r['open_price_by']>0){  // got open price
				if(!$user_id || ($user_id && $user_id == $r['cashier_id'])){
                	$this->table[$r['branch_id']][$r['cashier_id']]['open_price']++;
				}
				
                // open price by
				// shut down this section cause going to do this outside of this query
                /*if(!$user_id || ($user_id && $user_id == $r['open_price_by'])){
	                $this->table[$r['branch_id']][$r['open_price_by']]['user_id'] = $r['open_price_by'];
				    $this->table[$r['branch_id']][$r['open_price_by']]['u'] = $r['open_price_user'];
				    //$this->table[$r['branch_id']][$r['open_price_by']]['branch_id'] = $r['branch_id'];
	
				    $this->table[$r['branch_id']][$r['open_price_by']]['allow_open_price']++;
			    }*/
			}

			if($r['item_discount_by']>0){  // got item discount
				if(!$user_id || ($user_id && $user_id == $r['cashier_id'])){
				    $this->table[$r['branch_id']][$r['cashier_id']]['item_discount']++;
	                $this->table[$r['branch_id']][$r['cashier_id']]['item_discount_amt']+=$r['discount'];
                }

                // item_discount_by
				// shut down this section cause going to do this outside of this query
                /*if(!$user_id || ($user_id && $user_id == $r['item_discount_by'])){
	                $this->table[$r['branch_id']][$r['item_discount_by']]['user_id'] = $r['item_discount_by'];
				    $this->table[$r['branch_id']][$r['item_discount_by']]['u'] = $r['item_discount_user'];
				    //$this->table[$r['branch_id']][$r['item_discount_by']]['branch_id'] = $r['branch_id'];
	
				    $this->table[$r['branch_id']][$r['item_discount_by']]['allow_item_discount']++;
			    }*/
			}
		}
		$con_multi->sql_freeresult($q1);
			
		// allow receipt discount
		$q2 = $con_multi->sql_query("select pp.*, u1.u as approved_by_user
									 from pos_payment pp
									 left join user u1 on u1.id=pp.approved_by
									 where pp.branch_id=".mi($bid)." and pp.date between ".ms($date_from)." and ".ms($date_to)."
									 and pp.type = 'discount' and pp.approved_by > 0 and pp.adjust = 0");
		while($r = $con_multi->sql_fetchassoc($q2)){
			if(!$user_id || ($user_id && $user_id == $r['approved_by'])){
				$this->table[$r['branch_id']][$r['approved_by']]['user_id'] = $r['approved_by'];
				$this->table[$r['branch_id']][$r['approved_by']]['u'] = $r['approved_by_user'];
				$this->table[$r['branch_id']][$r['approved_by']]['allow_receipt_discount']++;
			}
		}
		$con_multi->sql_freeresult($q2);

		// allow open price and item discount
		$q2 = $con_multi->sql_query("select pi.*, u1.u as open_price_user, u2.u as item_discount_user
									 from pos_items pi 
									 left join user u1 on u1.id=pi.open_price_by
									 left join user u2 on u2.id=pi.item_discount_by
									 where pi.branch_id=".mi($bid)." and pi.date between ".ms($date_from)." and ".ms($date_to)."
									 and (pi.open_price_by > 0 or pi.item_discount_by > 0)");
		
		while($r = $con_multi->sql_fetchassoc($q2)){
			if(!$user_id || ($user_id && $user_id == $r['open_price_by'])){
				$this->table[$r['branch_id']][$r['open_price_by']]['user_id'] = $r['open_price_by'];
				$this->table[$r['branch_id']][$r['open_price_by']]['u'] = $r['open_price_user'];
				$this->table[$r['branch_id']][$r['open_price_by']]['allow_open_price']++;
			}
			if(!$user_id || ($user_id && $user_id == $r['item_discount_by'])){
				$this->table[$r['branch_id']][$r['item_discount_by']]['user_id'] = $r['item_discount_by'];
				$this->table[$r['branch_id']][$r['item_discount_by']]['u'] = $r['item_discount_user'];
				$this->table[$r['branch_id']][$r['item_discount_by']]['allow_item_discount']++;
			}
		}
		$con_multi->sql_freeresult($q2);
		
		// calculate Count & Allow for Prune & Cancel Status
		$q2 = $con_multi->sql_query("select prc.*, p.prune_status, u1.u as cancelled_by_user, u2.u as verified_by_user
									 from pos_receipt_cancel prc
									 left join pos p on p.branch_id=prc.branch_id and p.date=prc.date and p.counter_id=prc.counter_id and p.receipt_no=prc.receipt_no
									 left join user u1 on u1.id = prc.cancelled_by
									 left join user u2 on u2.id = prc.verified_by
									 where prc.branch_id = ".mi($bid)." and prc.date between ".ms($date_from)." and ".ms($date_to));
		
		while($prc = $con_multi->sql_fetchassoc($q2)){
			// if it is cashier itself do prune or cancel bill
			if(!$user_id || ($user_id && $user_id == $prc['cancelled_by'])){
				$this->table[$prc['branch_id']][$prc['cancelled_by']]['user_id'] = $prc['verified_by'];
				$this->table[$prc['branch_id']][$prc['cancelled_by']]['u'] = $prc['cancelled_by_user'];
				$this->table[$prc['branch_id']][$prc['cancelled_by']]['cancelled_bill']++;
				if($prc['prune_status']) $this->table[$prc['branch_id']][$prc['cancelled_by']]['prune_bill']++;
			}
			
			// prune or cancel bill by
			if(!$user_id || ($user_id && $user_id == $prc['verified_by'])){
				$this->table[$prc['branch_id']][$prc['verified_by']]['user_id'] = $prc['verified_by'];
				$this->table[$prc['branch_id']][$prc['verified_by']]['u'] = $prc['verified_by_user'];
				$this->table[$prc['branch_id']][$prc['verified_by']]['allow_cancelled_bill']++;
				if($prc['prune_status']) $this->table[$prc['branch_id']][$prc['verified_by']]['allow_prune_bill']++;
			}
		}
		$con_multi->sql_freeresult($q2);
		
		// calculate Count & Allow for delete items
		$q2 = $con_multi->sql_query("select pdi.*, p.cashier_id, u1.u as deleted_by_user, u2.u as verified_by_user
									 from pos_delete_items pdi
									 left join pos p on p.branch_id=pdi.branch_id and p.date=pdi.date and p.counter_id=pdi.counter_id and p.id=pdi.pos_id
									 left join user u1 on u1.id = p.cashier_id
									 left join user u2 on u2.id = pdi.delete_by
									 where pdi.branch_id = ".mi($bid)." and pdi.date between ".ms($date_from)." and ".ms($date_to));
		
		while($pdi = $con_multi->sql_fetchassoc($q2)){
			// this table doesn't have the history of who delete the items at first place
			// so need to add the delete item count to the cashier
			if(!$user_id || ($user_id && $user_id == $pdi['cashier_id'])){
				$this->table[$pdi['branch_id']][$pdi['cashier_id']]['user_id'] = $pdi['cashier_id'];
				$this->table[$pdi['branch_id']][$pdi['cashier_id']]['u'] = $pdi['deleted_by_user'];
				$this->table[$pdi['branch_id']][$pdi['cashier_id']]['deleted_items']+=$pdi['qty'];
			}
			
			// person who authorised to delete the items
			if(!$user_id || ($user_id && $user_id == $pdi['delete_by'])){
				$this->table[$pdi['branch_id']][$pdi['delete_by']]['user_id'] = $pdi['delete_by'];
				$this->table[$pdi['branch_id']][$pdi['delete_by']]['u'] = $pdi['verified_by_user'];
				$this->table[$pdi['branch_id']][$pdi['delete_by']]['allow_deleted_items']+=$pdi['qty'];
			}
		}
		$con_multi->sql_freeresult($q2);
		
		//$con_multi->close_connection();
	}
	
	function load_details(){
		global $smarty,$sessioninfo,$mm_discount_col_value,$config,$con_multi;
		$branch_id = intval($_REQUEST['branch_id']);
		$user_id = intval($_REQUEST['user_id']);
		$date_from = $_REQUEST['date_from'];
	    $date_to = $_REQUEST['date_to'];

	    if(BRANCH_CODE!='HQ'&&$branch_id!=$sessioninfo['branch_id']){
	        $branch_id = $sessioninfo['branch_id'];
		}
		$filter[] = "p.branch_id=$branch_id";
	 	$filter[] = "$user_id in (p.cashier_id, pi.item_discount_by, pi.open_price_by,pprd.approved_by)";
		$filter[] = "p.date between ".ms($date_from)." and ".ms($date_to);
		

		if ($this->ext_filter)	$filter[]=$this->ext_filter;

		$filter = join(' and ',$filter);

	    $sql = "select p.*, user.u, pi.qty, pi.open_price_by, pi.item_discount_by, pi.discount, u1.u as open_price_user, u2.u as item_discount_user,pprd.amount as receipt_discount_amt, pprd.approved_by as receipt_discount_by, u_rd.u as receipt_discount_u,ppmm.amount as mm_discount_amt,if((select id from pos_mix_match_usage pmm where pmm.branch_id=p.branch_id and pmm.counter_id=p.counter_id and pmm.date=p.date and pmm.pos_id=p.id limit 1)>0,1,0) as got_mm_discount,
		
		round((select sum(pd.deposit_amount)
from pos_deposit pd
join pos_deposit_status_history pdsh on pdsh.deposit_branch_id=pd.branch_id and pdsh.deposit_pos_date=pd.date and pdsh.deposit_counter_id=pd.counter_id and pdsh.deposit_pos_id=pd.pos_id
where pdsh.branch_id=p.branch_id and pdsh.pos_date=p.date and pdsh.counter_id=p.counter_id and pdsh.pos_id=p.id and pdsh.type='USED'), 2) as deposit_used_amt

		from pos p
		left join user on p.cashier_id=user.id
		left join pos_items pi on pi.branch_id=p.branch_id and pi.date=p.date and pi.counter_id=p.counter_id and pi.pos_id=p.id
		left join user u1 on u1.id=pi.open_price_by
		left join user u2 on u2.id=pi.item_discount_by
		left join pos_payment pprd on pprd.branch_id=p.branch_id and pprd.date=p.date and pprd.counter_id=p.counter_id and pprd.pos_id=p.id and pprd.type='discount' and pprd.adjust=0
		left join user u_rd on u_rd.id=pprd.approved_by
		left join pos_payment ppmm on ppmm.branch_id=p.branch_id and ppmm.date=p.date and ppmm.counter_id=p.counter_id and ppmm.pos_id=p.id and ppmm.type=".ms($mm_discount_col_value)." and ppmm.adjust=0
		where $filter
		order by p.branch_id,p.date,p.counter_id";
		//print $sql."<br>";
		$cashier['id'] = $user_id;
		/*$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/
	
		$q1 = $con_multi->sql_query($sql) or die(mysql_error());

		$last_pos_key = $last_pos_drawer_key = '';
		
		while($r = $con_multi->sql_fetchassoc($q1)){
			$pass = true;
			
			/*if($config['enable_mix_and_match_promotion']){
				$q_pmm = $con_multi->sql_query("select id from pos_mix_match_usage where branch_id=$r[branch_id] and counter_id=$r[counter_id] and date=".ms($r['date'])." and pos_id=$r[id] limit 1");
				$r['got_mm_discount'] = $con_multi->sql_numrows($q_pmm)>0 ? 1 : 0;
				$con_multi->sql_freeresult($q_pmm);
			}*/
			
			// check filter
			if($this->rcpt_filter){
				$pass = false;
				if($this->rcpt_filter['filter_item_discount']){	// item discount
					if($r['item_discount_by']>0)	$pass = true;
				}
				
				if($this->rcpt_filter['filter_cancel_bill']){	// cancel bill
					if($r['cancel_status'])	$pass = true;
				}
				
				if($this->rcpt_filter['filter_goods_return']){	// goods return
					if($r['qty']<0)	$pass = true;
				}
				
				if($this->rcpt_filter['open_price_by']){	// open price
					if($r['open_price_by']>0)	$pass = true;
				}
				
				if($this->rcpt_filter['filter_receipt_discount']){	// receipt discount
					if($r['receipt_discount_amt']>0)	$pass = true;
				}
				
				if($this->rcpt_filter['filter_mm_discount']){	// receipt discount
					if($r['got_mm_discount'])	$pass = true;
				}
			}
			
			$pos_id = mi($r['id']);
			$bid = mi($r['branch_id']);
			$counter_id = mi($r['counter_id']);
			$date = $r['date'];

			$curr_pos_key = $bid."_".$counter_id."_".$date."_".$pos_id;
            $curr_pos_drawer_key = $bid."_".$counter_id."_".$date;
            
            if(!$pass){
				continue;
			}
			//print "pass<br />";
			
            // save transaction data
            if($curr_pos_key!=$last_pos_key){
				// got receipt discount
				if($r['receipt_discount_amt']>0){
					if($r['cashier_id']==$user_id){
						$this->table[$r['date']]['receipt_discount']++;
	                	$this->table[$r['date']]['receipt_discount_amt']+=round($r['receipt_discount_amt'],2);
					}
					
					// shut down this section cause going to do this outside of this query
             		/*if($r['receipt_discount_by']==$user_id){
	                    $this->table[$r['date']]['allow_receipt_discount']++;
					}*/
				}
				
				// got mix and match
				if($r['got_mm_discount']){			
					$this->table[$r['date']]['mm_discount']++;
	                $this->table[$r['date']]['mm_discount_amt']+=round($r['mm_discount_amt'],2);

					$this->got_mm_discount = true;
				}
								
                $tran_time = 0;
			    $cashier['name'] = $r['u'];
			    $this->table[$r['date']]['user_id'] = $r['cashier_id'];
			    $this->table[$r['date']]['u'] = $r['u'];
			    $this->table[$r['date']]['branch_id'] = $r['branch_id'];
			    $this->table[$r['date']]['counter_id'] = $r['counter_id'];

				$this->table[$r['date']]['day_work'][$r['date']] = $r['date'];

				$tran_time = strtotime($r['end_time'])-strtotime($r['start_time']);

				$receipt_amt = $r['amount'];
				
				// Over
				if($r['amount_tender']-$r['amount_change']>$r['amount']){
					$over_amt = ($r['amount_tender']-$r['amount_change'])-$r['amount'];
					$this->table[$r['date']]['over'] += $over_amt;
					$receipt_amt += $over_amt;
				}
				
				// Receipt Discount
				if($r['receipt_discount_amt']){
					$receipt_amt -= $r['receipt_discount_amt'];
				}
				
				// Mix & Match Discount
				if($r['mm_discount_amt']){
					$receipt_amt -= $r['mm_discount_amt'];
				}
				
				// Deposit Used
				if($r['deposit_used_amt']){
					$receipt_amt -= $r['deposit_used_amt'];
				}
					
				if($r['cancel_status'] == 1){	// Cancelled
					$this->table[$r['date']]['cancelled_amount'] += $receipt_amt;
				}else{	// Active
					$this->table[$r['date']]['amount'] += $receipt_amt;
					$this->table[$r['date']]['tran_count']++;
					
					if($r['member_no']){
						$this->table[$r['date']]['member_sells']['qty']++;
						$this->table[$r['date']]['member_sells']['amount']+=$receipt_amt;
					}else{
						$this->table[$r['date']]['non_member_sells']['qty']++;
						$this->table[$r['date']]['non_member_sells']['amount']+=$receipt_amt;
					}
				}

				if($tran_time<86400){
		            $this->table[$r['date']]['total_tran_time'] += $tran_time;
		            $this->table[$r['date']]['tran_count2']++;
				}
				
				$last_pos_key = $curr_pos_key;
			}
			
			// save pos drawer data
			if($curr_pos_drawer_key!=$last_pos_drawer_key){
			    $q_pos_drawer = $con_multi->sql_query("select pd.user_id, count(*) as drawer_open_count
				from pos_drawer pd
				left join user on user.id=pd.user_id
				where pd.branch_id=$bid and pd.counter_id=$counter_id and pd.date=".ms($date)." and pd.user_id=$user_id and pd.type = 'POS' 
				group by pd.user_id");
				while($pd = $con_multi->sql_fetchassoc($q_pos_drawer)){
                    $this->table[$r['date']]['drawer_open_count'] += $pd['drawer_open_count'];
				}
				$con_multi->sql_freeresult($q_pos_drawer);
                $last_pos_drawer_key = $curr_pos_drawer_key;
			}
			
			if($r['qty']<0){    // goods return
                $this->table[$r['date']]['total_goods_return'] += abs($r['qty']);
			}

			if($r['open_price_by']>0){  // got open price
			    if($r['cashier_id']==$user_id)	$this->table[$r['date']]['open_price']++;
				// shut down this section cause going to do this outside of this query
                //if($r['open_price_by']==$user_id)    $this->table[$r['date']]['allow_open_price']++;
			}

			if($r['item_discount_by']>0){  // got item discount
			    if($r['cashier_id']==$user_id){
                    $this->table[$r['date']]['item_discount']++;
                	$this->table[$r['date']]['item_discount_amt']+=$r['discount'];
				}
				// shut down this section cause going to do this outside of this query
                //if($r['item_discount_by']==$user_id)    $this->table[$r['date']]['allow_item_discount']++;
			}

			/*$q2 = $con->sql_query("select pi.*, u1.u as open_price_user, u2.u as item_discount_user
			from pos_items pi
			left join user u1 on u1.id=pi.open_price_by
			left join user u2 on u2.id=pi.item_discount_by
			where pi.branch_id=$bid and pi.counter_id=$counter_id and pi.date=".ms($date)." and pi.pos_id=$pos_id");
			while($pi = $con->sql_fetchrow($q2)){
				if($pi['qty']<0){    // goods return
	                $this->table[$r['date']]['total_goods_return'] += abs($pi['qty']);
				}

				if($pi['open_price_by']>0){  // got open price
	                $this->table[$r['date']]['open_price']++;
				}

				if($pi['item_discount_by']>0){  // got item discount
				    $this->table[$r['date']]['item_discount']++;
	                $this->table[$r['date']]['item_discount_amt']+=$pi['discount'];
				}
			}
			$con->sql_freeresult($q2);*/
		}
		$con_multi->sql_freeresult($q1);
		//$con_multi->close_connection();
		/*$filter2 = str_replace("cashier_id", 'user_id', $filter);
		$sql2 = "select p.branch_id,p.date,p.user_id,user.u,count(*) as drawer_open_count
	from pos_drawer p
	left join user on user.id=p.user_id
	where $filter2
	group by p.branch_id,p.date,p.user_id";
		$q3 = $con->sql_query($sql2);
		while($r = $con->sql_fetchrow($q3)){
	        $this->table[$r['date']]['drawer_open_count'] += $r['drawer_open_count'];
		}*/

		if($this->table){
	 		foreach($this->table as $d=>$r){
				    // Total and AVG transaction time
				    if(!$r['tran_count2'])  continue;
					$r['avg_tran_time'] = intval($r['total_tran_time']/$r['tran_count2']);

					$this->table[$d]['avg_tran_time'] = $r['avg_tran_time'];

                    $total_tran_time_hour = 0;
					$total_tran_time_min = intval($r['total_tran_time']/60);
					$total_tran_time_sec = intval($r['total_tran_time']%60);
					$avg_tran_time_hour = 0;
					$avg_tran_time_min = intval($r['avg_tran_time']/60);
					$avg_tran_time_sec = intval($r['avg_tran_time']%60);

					if($total_tran_time_min>60){
	                    $total_tran_time_hour = intval($total_tran_time_min/60);
	                    $total_tran_time_min = intval($total_tran_time_min%60);
					}

					if($avg_tran_time_min>60){
	                    $avg_tran_time_hour = intval($avg_tran_time_min/60);
	                    $avg_tran_time_min = intval($avg_tran_time_min%60);
					}

					$this->table[$d]['total_tran_time_hour'] = $total_tran_time_hour;
					$this->table[$d]['total_tran_time_min'] = $total_tran_time_min;
					$this->table[$d]['total_tran_time_sec'] = $total_tran_time_sec;
					$this->table[$d]['avg_tran_time_hour'] = $avg_tran_time_hour;
					$this->table[$d]['avg_tran_time_min'] = $avg_tran_time_min;
					$this->table[$d]['avg_tran_time_sec'] = $avg_tran_time_sec;
					// End of Total and AVG transaction time

					// AVG daily amount,qty
					$day_count = count($r['day_work']);
					if($day_count!=0){
	                    $avg_amount = $r['amount']/$day_count;
	                    $avg_cancelled_amount = $r['cancelled_amount']/$day_count;
	                    $this->table[$d]['avg_amount'] = $avg_amount;
	                    $this->table[$d]['avg_cancelled_amount'] = $avg_cancelled_amount;
	                    $this->table[$d]['avg_qty'] = intval($r['tran_count2']/$day_count);
					}
					// End of AVG daily amount,qty

					// Total Variance
					if($this->show_variances){
						$this->table[$d]['denom'] = $this->load_variance($r);
						$this->table[$d]['variances'] = $this->table[$d]['denom'] - $r['amount'];
					}
					
					// End of Total Variance
			}
			//print_r($this->table);
		}

		// allow receipt discount
		$q2 = $con_multi->sql_query("select pp.*
									 from pos_payment pp
									 where pp.branch_id=".mi($branch_id)." and pp.date between ".ms($date_from)." and ".ms($date_to)."
									 and pp.type = 'discount' and pp.adjust=0 and pp.approved_by = ".mi($user_id));
		while($r = $con_multi->sql_fetchassoc($q2)){
			$this->table[$r['date']]['allow_receipt_discount']++;
		}
		$con_multi->sql_freeresult($q2);

		// allow open price and item discount
		$q2 = $con_multi->sql_query("select pi.*
									 from pos_items pi 
									 where pi.branch_id=".mi($branch_id)." and pi.date between ".ms($date_from)." and ".ms($date_to)." 
									 and (pi.open_price_by=".mi($user_id)." or pi.item_discount_by=".mi($user_id).")");
		
		while($r = $con_multi->sql_fetchassoc($q2)){
			if($r['open_price_by']==$user_id) $this->table[$r['date']]['allow_open_price']++;
			if($r['item_discount_by']==$user_id) $this->table[$r['date']]['allow_item_discount']++;
		}
		$con_multi->sql_freeresult($q2);
		
		// calculate Count & Allow for cancelled and prune receipts
		$q2 = $con_multi->sql_query("select prc.*, p.prune_status
									 from pos_receipt_cancel prc
									 left join pos p on p.branch_id=prc.branch_id and p.date=prc.date and p.counter_id=prc.counter_id and p.receipt_no=prc.receipt_no
									 where prc.branch_id=".mi($branch_id)." and prc.date between ".ms($date_from)." and ".ms($date_to));
		
		while($pdi = $con_multi->sql_fetchassoc($q2)){
			// if it is cashier itself do prune or cancel bill
			if($user_id == $pdi['cancelled_by']){
				$this->table[$pdi['date']]['cancelled_bill']++;
				if($pdi['prune_status']) $this->table[$pdi['date']]['prune_bill']++;
			}
			
			// prune or cancel bill by
			if($user_id == $pdi['verified_by']){
				$this->table[$pdi['date']]['allow_cancelled_bill']++;
				if($pdi['prune_status']) $this->table[$pdi['date']]['allow_prune_bill']++;
			}
		}
		$con_multi->sql_freeresult($q2);
		
		// calculate Count & Allow for delete items
		$q2 = $con_multi->sql_query("select pdi.*, p.cashier_id
									 from pos_delete_items pdi
									 left join pos p on p.branch_id=pdi.branch_id and p.date=pdi.date and p.counter_id=pdi.counter_id and p.id=pdi.pos_id
									 where pdi.branch_id=".mi($branch_id)." and pdi.date between ".ms($date_from)." and ".ms($date_to));
		
		while($pdi = $con_multi->sql_fetchassoc($q2)){
			// this table doesn't have the history of who delete the items at first place
			// so need to add the delete item count to the cashier
			if($pdi['cashier_id'] == $user_id) $this->table[$pdi['date']]['deleted_items']+=$pdi['qty'];
			
			// person who authorised to delete the items
			if($pdi['delete_by'] == $user_id) $this->table[$pdi['date']]['allow_deleted_items']+=$pdi['qty'];
		}
		$con_multi->sql_freeresult($q2);

		$smarty->assign('cashier',$cashier);
		$smarty->assign('table',$this->table);
		$smarty->assign('got_mm_discount', $this->got_mm_discount);
		$smarty->assign('show_variances', $this->show_variances);
		$smarty->display('pos_report.cashier_performance.details.tpl');
	}
	
	function tran_details(){
		global $con,$smarty,$bid,$counter_id,$f_date,$mm_discount_col_value,$config,$con_multi;

		if(BRANCH_CODE == 'HQ'){
			$branch_id = intval($_REQUEST['branch_id']);
		}else{
			$branch_id = get_request_branch(true);
		}

		$user_id = intval($_REQUEST['user_id']);
		$date = $_REQUEST['date'];

		$filter[] = 'p.branch_id='.mi($branch_id);
		$filter[] = 'p.date='.ms($date);
		$filter[] = 'p.cashier_id='.mi($user_id);

		if ($this->ext_filter)	$filter[]=$this->ext_filter;
		
		$filter = join(' and ', $filter);
		$sql = "select p.*, p.amount as payment_amount, user.u, pprd.amount as receipt_discount_amt, ppmm.amount as mm_discount_amt,
		
		round((select sum(pd.deposit_amount)
from pos_deposit pd
join pos_deposit_status_history pdsh on pdsh.deposit_branch_id=pd.branch_id and pdsh.deposit_pos_date=pd.date and pdsh.deposit_counter_id=pd.counter_id and pdsh.deposit_pos_id=pd.pos_id
where pdsh.branch_id=p.branch_id and pdsh.pos_date=p.date and pdsh.counter_id=p.counter_id and pdsh.pos_id=p.id and pdsh.type='USED'), 2) as deposit_used_amt

		from pos p
		left join user on p.cashier_id=user.id
		left join pos_items pi on pi.branch_id=p.branch_id and pi.date=p.date and pi.counter_id=p.counter_id and pi.pos_id=p.id
		left join pos_payment pprd on pprd.branch_id=p.branch_id and pprd.date=p.date and pprd.counter_id=p.counter_id and pprd.pos_id=p.id and pprd.type='discount' and pprd.adjust=0
		left join user u_rd on u_rd.id=pprd.approved_by
		left join pos_payment ppmm on ppmm.branch_id=p.branch_id and ppmm.date=p.date and ppmm.counter_id=p.counter_id and ppmm.pos_id=p.id and ppmm.type=".ms($mm_discount_col_value)." and ppmm.adjust=0
		where $filter order by p.branch_id,p.date,p.counter_id,p.pos_time";
		//print $sql;
		/*$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/

		$q1 = $con_multi->sql_query($sql) or die(mysql_error());
		$tran_details = array();
		$last_pos_key = '';
		while($r = $con_multi->sql_fetchassoc($q1)){
            $pos_id = mi($r['id']);
			$bid = mi($r['branch_id']);
			$counter_id = mi($r['counter_id']);
			$date = $r['date'];
			
			/*if($config['enable_mix_and_match_promotion'] && $this->rcpt_filter['filter_mm_discount']){
				$q_pmm = $con_multi->sql_query("select id from pos_mix_match_usage where branch_id=$r[branch_id] and counter_id=$r[counter_id] and date=".ms($r['date'])." and pos_id=$r[id] limit 1");
				$r['got_mm_discount'] = $con_multi->sql_numrows($q_pmm)>0 ? 1 : 0;
				$con_multi->sql_freeresult($q_pmm);
				if(!$r['got_mm_discount'])	continue;
			}*/
			
			$curr_pos_key = $bid."_".$counter_id."_".$date."_".$pos_id;
			if($curr_pos_key!=$last_pos_key){
				$receipt_amt = $r['amount'];
				
				// Over
				if($r['amount_tender']-$r['amount_change']>$r['amount']){
					$over_amt = ($r['amount_tender']-$r['amount_change'])-$r['amount'];
					$receipt_amt += $over_amt;
				}
				
				// Receipt Discount
				if($r['receipt_discount_amt']){
					$receipt_amt -= $r['receipt_discount_amt'];
				}
				
				// Mix & Match Discount
				if($r['mm_discount_amt']){
					$receipt_amt -= $r['mm_discount_amt'];
				}
				
				// Deposit Used
				if($r['deposit_used_amt']){
					$receipt_amt -= $r['deposit_used_amt'];
				}
				
				$r['actual_amount'] = $receipt_amt;
				
			    $tran_details[] = $r;
                $last_pos_key = $curr_pos_key;
			}
		}
		$con_multi->sql_freeresult($q1);
		//$con_multi->close_connection();
		$smarty->assign('items', $tran_details);
		$smarty->assign('not_cc', 1);
		$smarty->assign('show_actual_amount', 1);
		$smarty->display('counter_collection.sales_details.tpl');
	}
	
	/*function item_details(){
	    global $con,$smarty,$bid,$counter_id,$f_date;

	    if(BRANCH_CODE == 'HQ'){
			$bid = intval($_REQUEST['branch_id']);
		}else{
	  		$bid = get_request_branch(true);
		}
		$pos_id = mi($_REQUEST['id']);
		$counter_id = intval($_REQUEST['counter_id']);
		$date = $_REQUEST['date'];
		
		$filter[] = 'p.branch_id='.mi($bid);
	    $filter[] = 'p.counter_id='.mi($counter_id);
	    $filter[] = 'p.date='.ms($date);
	    $filter[] = 'p.id='.mi($pos_id);
	    $filter = join(' and ', $filter);
	    // get items
		$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}

		$con_multi->sql_query("select p.counter_id, u.u as cashier_name, p.receipt_no, p.pos_time, p.member_no, pi.pos_id, 
						 amount_change, pi.qty, pi.price, pi.discount, si.mcode, si.sku_item_code, si.description
						 from pos p
						 left join pos_items pi on p.branch_id = pi.branch_id and p.counter_id = pi.counter_id and p.date= pi.date and p.id = pi.pos_id
						 left join sku_items si on pi.sku_item_id = si.id
						 left join user u on u.id = p.cashier_id
						 where $filter");

		$items = $con_multi->sql_fetchrowset();

		$smarty->assign('items',$items);

		$smarty->assign("amount_change", $items[0]['amount_change']);

		$con_multi->sql_query("select * from pos_payment where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and pos_id=$pos_id and adjust <> 1");
		
		$smarty->assign('payment',$con_multi->sql_fetchrowset());
		$smarty->display('counter_collection.item_details.tpl');
		$con_multi->close_connection();
	}*/
}

$CASHIER_PERFORMANCE_REPORT = new CASHIER_PERFORMANCE_REPORT('Cashier Performance Report');
?>
