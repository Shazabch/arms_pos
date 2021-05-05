<?php

/*
cron (default last 7 day)
php vp.consignee_daily_sales_report.php email_sales -branch_id 1 -vendor_id 571 -email_to andyloh@wsatp.com

or all
php vp.consignee_daily_sales_report.php email_sales

or 
php vp.consignee_daily_sales_report.php email_sales -branch_id 1 -vendor_id 571 -email_to andyloh@wsatp.com -date_from_to 2012-7-10 2012-8-5

php vp.consignee_daily_sales_report.php email_sales -email_to andyloh@wsatp.com

php vp.consignee_daily_sales_report.php email_sales -vendor_id 939 
*/

/*
10/8/2012 5:12 PM Andy
- Fix wrong checking csv by using vendor_id, should be use vendor code to check.

10/22/2012 2:45 PM Andy
- Add email feature.
- Add can generate report by date range, but without monthly bonus.

11/15/2012 12:03 PM Andy
- Enhanced report to calculate cost by percentage sku or category.
- Enhanced report to calculate bonus by adding additional category bonus.
- Enhanced report to check SKU Group Date Control Settings.

12/17/2012 5:15 PM Justin
- Enhanced to use previous month's bonus if found do not have for current month.

1/28/2013 11:14 AM Andy
- Modified report profit percent to use override format instead of additional format, override from lowest to highest. (sku > lower cat > higher cat > normal %)
- Modified bonus percent to use override format instead of additional format, override from lowest to highest. (lower cat > higher cat > normal %)

10/1/2013 6:01 PM Fithri
- make all email field not compulsary
- when sending email, check to trigger send function only when email is set

10/18/2013 4:09 PM Andy
- Change all $mailer->send() to to call phpmailer_send().

9/15/2017 11:53 AM Andy
- Remove hardcoded bcc email to yinsee@wsatp.com

12/19/2019 10:40 AM Justin
- Bug fixed send email function couldn't send out the email.

1/7/2020 5:41 PM Justin
- Removed the IsMail since it causes customers who are using smtp couldn't send out email.
*/

include('include/common.php');
$maintenance->check(169);

if(is_using_terminal()){
	define('TERMINAL', 1);
	ob_end_flush();
	fix_terminal_smarty();
	$agrs = $_SERVER['argv'];
	$_REQUEST['a'] = $agrs[1];
	if(!$_REQUEST['a'])	die("Invalid Action\n");
}else{
	if(!$vp_session)	js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
}

class CONSIGNEE_DAILY_SALES_REPORT extends Module{
	var $bid = 0;
	var $month_list = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
	var $year_list = array();
	var $use_date_from = 0;
	var $use_date_to = 0;
	var $no_bonus = false;
	
	function __construct($title, $template=''){
		global $con, $smarty, $vp_session, $config;

		$this->bid = $vp_session['branch_id'];

		$smarty->assign("month_list", $this->month_list);
		
		parent::__construct($title, $template);
	}
	
	function _default(){
		global $vp_session, $smarty;
		
		$this->load_report_year_list();
		
		if($_REQUEST['load_report']){
			if($_REQUEST['submit_type']=='excel'){	// export excel
				include_once("include/excelwriter.php");
				log_vp($vp_session['id'], "VENDOR REPORT", 0, "Export ".$this->title);
	
				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
			$this->load_report();
		}
		
		$this->display();
	}
	
	private function load_report_year_list(){
		global $con, $smarty;
		
		$con->sql_query(" select year(min(date)) as min_year, year(max(date)) as max_year from pos");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
        $min_year = $tmp['min_year'];
        $max_year = $tmp['max_year'];
		
		$y = $max_year;
		$this->year_list = array();
		
		while($y>=$min_year){
			$this->year_list[] = $y;
			$y--;
		}
		
		$smarty->assign("year_list", $this->year_list);
	}
	
	private function load_report($params = array()){
		global $con, $smarty, $vp_session, $config, $con_multi, $vp_global_si_info_list, $vp_global_cat_info_list;
		
		$bid = mi($this->bid);
		
		if($this->use_date_from && $this->use_date_to){
			$date_from = $this->use_date_from;
			$date_to = $this->use_date_to;
		}else{
			$year = mi($_REQUEST['year']);
			$month = mi($_REQUEST['month']);
			
			if(!$year)	die("Invalid Year");
			if(!$month)	die("Invalid Month");
				
			$date_from = date("Y-m-d", strtotime($year.'-'.$month.'-1'));
			$date_to = date("Y-m-d", strtotime($year.'-'.$month.'-'.days_of_month($month, $year)));
		}
		
		$vid = $vp_session['id'];
		
		//print_r($vp_session);
		$dt1 = strtotime($date_from);
		$dt2 = strtotime($date_to);
		
		$err = array();
		if(!$bid)	$err[] = "Invalid Branch.";
		if(!$date_from || !$dt1)	$err[] = "Invalid Date From.";
		if(!$date_to || !$dt2)	$err[] = "Invalid Date To.";
		
		if(!$err && $dt1 > $dt2)	$err[] = "Date to cannot early then date from.";
		if(!$err && date("Y", strtotime($date_from))<2007)	$err[] = "Report cannot show data early then year 2007.";
		
		$time_diff = $dt2 - $dt1;
		$date_diff = mi($time_diff/86400);
		if(!$err && $date_diff>90)	$err[] = "Report maximum show 90 days of transaction.";
		
		$sku_group_bid = mi($vp_session['sku_group_bid']);
		$sku_group_id = mi($vp_session['sku_group_id']);

		if(isset($params['vendor_id']))	$vid = $params['vendor_id'];
		if(isset($params['sku_group_bid']))	$sku_group_bid = $params['sku_group_bid'];
        if(isset($params['sku_group_id']))	$sku_group_id = $params['sku_group_id'];
        
        $sales_bonus_by_step = $vp_session['vp']['sales_bonus_by_step'];
        if(isset($params['sales_report_profit_by_date_list']))	$sales_report_profit_by_date_list = $params['sales_report_profit_by_date_list'];
        if(isset($params['sales_bonus_by_step']))	$sales_bonus_by_step = $params['sales_bonus_by_step'];
        
		if(!$sku_group_bid || !$sku_group_id)	$err[] = "Error on SKU Group setup, please contact admin to solve this.";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$this->data = array();
		
		$sbbs_yr = $sbbs_mth = 0;
		$need_calc_bonus = false;
		if($year && $month && !$this->no_bonus){
			$need_calc_bonus = true;
			
			// check latest bonus year and month
			$tmp_date = $year."-".$month."-01";
			$curr_date = strtotime("-1 day", strtotime("+1 month", strtotime($tmp_date)));
			foreach($sales_bonus_by_step as $b_y=>$m_list){
				foreach($m_list as $b_m=>$r_list){
					$tmp_bonus_date = $b_y."-".$b_m."-01";
					$bonus_date = strtotime("-1 day", strtotime("+1 month", strtotime($tmp_bonus_date)));
					if($curr_date >= $bonus_date){
						$sbbs_yr = $b_y;
						$sbbs_mth = $b_m;
						break 2;
					}				
				}
			}
		}

		// get this month related bonus category
		/*if(!$this->no_bonus && $year && $month && $sales_bonus_by_step[$sbbs_yr][$sbbs_mth]){
			$this->data['bonus_info']['category'] = array();

			foreach($sales_bonus_by_step[$sbbs_yr][$sbbs_mth] as $m_bonus_info){	// loop for bonus amt list
				if($m_bonus_info['bonus_per_by_type']){	// this bonus got set by type
					foreach($m_bonus_info['bonus_per_by_type'] as $type_row_no => $type_row_info){	// loop the type list
						if($type_row_info['type'] == 'CATEGORY'){	// this type is category
							// store cat bonus %
							$this->data['bonus_info']['category'][$type_row_info['value']]['per'] = $type_row_info['per'];
							
							if(!$this->data['bonus_info']['cat_bonus_list'])	$this->data['bonus_info']['cat_bonus_list'] = array();
							
							// store a list of unique % list
							if(!in_array($type_row_info['per'], $this->data['bonus_info']['cat_bonus_list'])){
								$this->data['bonus_info']['cat_bonus_list'][] = $type_row_info['per'];
								asort($this->data['bonus_info']['cat_bonus_list']);
							}	
						}
					}
				}
			}
		}*/
		
		if($vid == $vp_session['id'])	$vcode = $vp_session['code'];
		else{
			$con->sql_query("select code from vendor where id=$vid");
			$vcode = $con->sql_fetchfield(0);
			$con->sql_freeresult();
		}
		
		$tbl = "sku_items_sales_cache_b".$bid;
		
		$sql = "select tbl.sku_item_id, tbl.date, tbl.amount, tbl.qty, si.sku_item_code,si.mcode, si.artno, si.link_code,si.description, c.department_id,sku.vendor_id, sku.brand_id, sku.category_id, sku.trade_discount_type,sku.default_trade_discount_code
		from sku_group_item sgi
		join sku_items si on si.sku_item_code=sgi.sku_item_code
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		join $tbl tbl on tbl.sku_item_id=si.id
		join sku_group_vp_date_control vpdc on vpdc.branch_id=sgi.branch_id and vpdc.sku_group_id=sgi.sku_group_id and vpdc.sku_item_id=si.id and tbl.date between vpdc.from_date and vpdc.to_date
		where sgi.branch_id=$sku_group_bid and sgi.sku_group_id=$sku_group_id and tbl.date between ".ms($date_from)." and ".ms($date_to)." order by tbl.date";
		
		if($_REQUEST['show_q'])
			print $sql;
		if(!$con_multi)	$con_multi= new mysql_multi();
		
		$last_date = '';
		$tmp_item_trade_discount_code = array();
		
		$q1 = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q1)){
			$key = $r['date'];
			$sid = mi($r['sku_item_id']);
			$cat_id = $r['category_id'];
			
			// get global report profit by date
			if(!isset($this->data['data'][$key]['discount_rate'])){
				$tmp = get_vp_sales_report_profit_by_date($r['date'], $sales_report_profit_by_date_list);
				$this->data['data'][$key]['discount_rate'] = $tmp['per'];
			}
				
			// get report profit by sku
			if(!isset($this->data['data'][$key]['discount_rate_by_type']['sku'][$sid])){
				$params = array();
				$params['other_type_info'] = array('type'=>'SKU', 'value'=>$sid);
				$this->data['data'][$key]['discount_rate_by_type']['sku'][$sid] = get_vp_sales_report_profit_by_date($r['date'], $sales_report_profit_by_date_list, $params);
			}
			$profit_info = $this->data['data'][$key]['discount_rate_by_type']['sku'][$sid];
			$discount_rate = $profit_info['per'];
			
			// the normal report profit != sku report profit
			if($profit_info['type']!='NORMAL_GLOBAL' && !isset($this->data['data'][$key]['other_discount_rate'][$profit_info['type']][$profit_info['value']])){
				$this->data['data'][$key]['other_discount_rate'][$profit_info['type']][$profit_info['value']] = $profit_info['per'];
				
				// store a list of report profit % by date
				/*if(!$this->data['data'][$key]['other_discount_rate'][$profit_info['type']])	$this->data['data'][$key]['other_discount_rate'][$profit_info['type']] = array();
				if(!in_array($discount_rate, $this->data['data'][$key]['other_discount_rate'])){
					$this->data['data'][$key]['other_discount_rate'][] = $discount_rate;
					asort($this->data['data'][$key]['other_discount_rate']);
				}*/
			}
			
			/*$sales_report_profit_by_date = get_vp_sales_report_profit_by_date($r['date'], $sales_report_profit_by_date_list);
		
			if($r['date'] != $last_date){
				$last_date = $r['date'];
				$tmp_item_trade_discount_code = array();
			}

			if ($sales_report_profit_by_date > 0){
				$discount_rate = $sales_report_profit_by_date;
			}else{
				if(!isset($tmp_item_trade_discount_code[$sid])){	// get trade discount code by date
					// get discount rate
					if($r['trade_discount_type'] == 1){
						$brand_vendor_id = $r['brand_id'];
						$brand_vendor_commission = 'brand';
					}else{
						$brand_vendor_id = $r['vendor_id'];
						$brand_vendor_commission = 'vendor';
					}
				
					// get trade discount code
					$tmp = get_sku_item_cost_selling($bid, $sid, $r['date'], array('trade_discount_code'));
					$tmp_item_trade_discount_code[$sid]['trade_discount_code'] = $tmp['trade_discount_code'];
					
					// if no discount code, get master 
					if(!$tmp_item_trade_discount_code[$sid]['trade_discount_code'])	$tmp_item_trade_discount_code[$sid]['trade_discount_code'] = $r['default_trade_discount_code'];
					
					// get discount rate at that time
					$tmp_discount_rate = get_consignment_discount_rate($bid, $r['date'], $tmp_item_trade_discount_code[$sid]['trade_discount_code'], $r['department_id'], $brand_vendor_commission, $brand_vendor_id);
					$tmp_item_trade_discount_code[$sid]['discount_rate'] = $tmp_discount_rate;
				}
				
				//$trade_discount_code = $tmp_item_trade_discount_code[$sid]['trade_discount_code'];
				$discount_rate = mf($tmp_item_trade_discount_code[$sid]['discount_rate']);
			}*/
			
			$amt = round($r['amount'], 2);
			$disc_amt = round($amt*$discount_rate/100, 5);
			$cost = round($amt-$disc_amt, 5);
			
			// data by date
			$this->data['data'][$key]['pos']['amt'] += $amt;
			$this->data['data'][$key]['pos']['qty'] += $r['qty'];
			$this->data['data'][$key]['pos']['cost'] += $cost;
			$this->data['data'][$key]['pos']['disc_amt'] += $disc_amt;
			
			// total by date
			$this->data['data'][$key]['total']['amt'] += $amt;
			$this->data['data'][$key]['total']['qty'] += $r['qty'];
			$this->data['data'][$key]['total']['cost'] += $cost;
			$this->data['data'][$key]['total']['disc_amt'] += $disc_amt;
			
			// all total
			$this->data['total']['amt'] += $amt;
			$this->data['total']['qty'] += $r['qty'];
			$this->data['total']['cost'] += $cost;
			$this->data['total']['disc_amt'] += $disc_amt;

			// need calculate bonus			
			if($need_calc_bonus){
				$params['other_type_info'] = array('type'=>'SKU', 'value'=>$sid);
				if($sbbs_yr && $sbbs_mth && (isset($this->data['total']['sales_by_sku'][$sid]) || got_vp_sales_bonus_set($sbbs_yr, $sbbs_mth, $sales_bonus_by_step, $params))){
					// store total by sku
					$this->data['total']['sales_by_sku'][$sid]['amt'] += $amt;
				}else{
					// store total sales for normal %
					$this->data['total']['sales_by_normal_bonus']['amt'] += $amt;
				}				
			}
			
			// got bonus by category
			/*if($this->data['bonus_info']['category']){
				// get category info
				$cat_info = get_vp_global_cat_info_list($cat_id);
				
				$clvl = $cat_info['level'];
					
				// loop from the lowest level to top level
				while($clvl > 0){
					$check_cat_id = $cat_info['p'.$clvl];
					
					// this category got bonus set
					if(isset($this->data['bonus_info']['category'][$check_cat_id])){
						// store this category sales amt
						$this->data['total']['by_cat_overlap'][$check_cat_id]['amt'] += $amt;
					}
				
					$clvl--;	// move up 1 level
				}
			}*/
		}
		$con_multi->sql_freeresult($q1);
		
		// get the sales to scc
		// main folder
		
		$ym_list = array();
		$y1 = mi(date("Y", strtotime($date_from)));
		$m1 = mi(date("m", strtotime($date_from)));
		
		$y2 = mi(date("Y", strtotime($date_to)));
		$m2 = mi(date("m", strtotime($date_to)));
		
		// possible overlap month to get scc data
		$ym_list[] = array('y'=>$y1, 'm'=>$m1);
		if($y1 != $y2 || $m1 != $m2)	$ym_list[] = array('y'=>$y2, 'm'=>$m2);
		
		$main_folder_path = "consignee_sales";
		
		$min_date_time = strtotime($date_from);
		$max_date_time = strtotime($date_to);
		
		foreach($ym_list as $tmp_ym){
			// year as sub-folder
			$file_folder = $main_folder_path."/".$tmp_ym['y'];

			if(is_dir($file_folder) && $vcode){
				$filepath = $file_folder."/".sprintf("%02d", $tmp_ym['m']).".csv";
				
				// check the file exists or not
				if(file_exists($filepath)){
					if($_REQUEST['q'])	print "$filepath exists<br>\n";
					
					$f = fopen($filepath, "r");
					
					$r = fgetcsv($f);
					
					// loop for each csv row
					while($r = fgetcsv($f)){
						$r_bid = mi($r[0]);
						$r_vcode = trim($r[1]);
						$r_date = trim($r[3]);
						$r_rcv_amt = round($r[4], 2);
						
						$r_date_time = strtotime($r_date);
						
						// must same branch, vendor and date
						if($_REQUEST['q'])	print "$r_bid != $bid || $r_vcode != $vcode || $r_date_time<$min_date_time || $r_date_time>$max_date_time<br>\n";
						if($r_bid != $bid || $r_vcode != $vcode || $r_date_time<$min_date_time || $r_date_time>$max_date_time)	continue;	// skip this row
						if($_REQUEST['q']) "passed<br>\n";
						
						$key = $r_date;
	
						// get discount rate by date
						if(!isset($this->data['data'][$key]['discount_rate'])){
							$tmp = get_vp_sales_report_profit_by_date($tmp_date, $sales_report_profit_by_date_list);
							$this->data['data'][$key]['discount_rate'] = $tmp['per'];
						}
						$discount_rate = mf($this->data['data'][$key]['discount_rate']);
						
						$disc_amt = round($r_rcv_amt*$discount_rate/100, 5);
						$r_cost = round($r_rcv_amt-$disc_amt, 5);
						
						// data by date						
						$this->data['data'][$key]['scc']['amt'] += $r_rcv_amt;
						$this->data['data'][$key]['scc']['cost'] += $r_cost;
						$this->data['data'][$key]['scc']['disc_amt'] += $disc_amt;
						
						// total by date
						$this->data['data'][$key]['total']['amt'] += $r_rcv_amt;
						$this->data['data'][$key]['total']['cost'] += $r_cost;
						$this->data['data'][$key]['total']['disc_amt'] += $disc_amt;
						
						// all total
						$this->data['total']['amt'] += $r_rcv_amt;
						$this->data['total']['cost'] += $r_cost;
						$this->data['total']['disc_amt'] += $disc_amt;
						
						// need calculate bonus			
						if($need_calc_bonus){
							// store total sales for normal %
							$this->data['total']['sales_by_normal_bonus']['amt'] += $r_rcv_amt;
						}
					}
					
					fclose($f);
				}else{
					if($_REQUEST['q'])	print "$filepath not exists<br>\n";
				}
			}
		}		
		
		$con_multi->close_connection();

		if($this->data['data'] && $need_calc_bonus){
		
			// sort by date
			ksort($this->data['data']);
			
			if($this->data['total']['amt']){
				if($sbbs_yr && $sbbs_mth){
					// get global bonus percent
					$bonus_info = get_vp_sales_bonus_per($sbbs_yr, $sbbs_mth, $this->data['total']['amt'], $sales_bonus_by_step);
					//$bonus_info = get_vp_sales_bonus_per($sbbs_yr, $sbbs_mth, $this->data['total']['sales_by_normal_bonus']['amt'], $sales_bonus_by_step);
					$this->data['total']['bonus_per'] = $bonus_info['per'];
					
					// calculate bonus by sku or cat
					if($this->data['total']['sales_by_sku']){
						foreach($this->data['total']['sales_by_sku'] as $sid => $tmp_r){	// loop for each sku amt
							$params = array();
							$params['other_type_info'] = array('type'=>'SKU', 'value'=>$sid);
							//$bonus_info = get_vp_sales_bonus_per($sbbs_yr, $sbbs_mth, $tmp_r['amt'], $sales_bonus_by_step, $params);
							$bonus_info = get_vp_sales_bonus_per($sbbs_yr, $sbbs_mth, $this->data['total']['amt'], $sales_bonus_by_step, $params);
	
							$this->data['total']['bonus_amt'] += round($tmp_r['amt'] * $bonus_info['per'] / 100, 2);	// add the bonus by sku
							
							if($bonus_info['type'] != 'NORMAL_GLOBAL' && $bonus_info['per'] != $this->data['total']['bonus_per'] && !isset($this->data['bonus_by_type'][$bonus_info['type']][$bonus_info['value']])){
								$this->data['bonus_by_type'][$bonus_info['type']][$bonus_info['value']] = $bonus_info['per'];
							}
						}
					}
					
					// normal bonus
					$this->data['total']['bonus_amt'] += round($this->data['total']['sales_by_normal_bonus']['amt'] * $this->data['total']['bonus_per'] / 100, 2);
				}
				// get global bonus percent
				//$this->data['total']['bonus_per'] = get_vp_sales_bonus_per($year, $month, $this->data['total']['amt'], $sales_bonus_by_step);

				// calculate bonus amt				
				//$this->data['total']['bonus_amt'] = round($this->data['total']['amt'] * $this->data['total']['bonus_per'] / 100, 2);
				
				// got category sales
				/*if($this->data['total']['by_cat_overlap']){
					// loop for category sales
					foreach($this->data['total']['by_cat_overlap'] as $tmp_cat_id => $tmp_cat_sales_info){
						// get this category bonus %
						$cat_bonus_per = mf($this->data['bonus_info']['category'][$tmp_cat_id]['per']);
						
						// calculate bonus amt for this cat
						$bonus_amt = round($tmp_cat_sales_info['amt'] * $cat_bonus_per / 100, 2);
						
						// add cat bonus into total bonus
						$this->data['total']['bonus_amt'] += $bonus_amt;
					}
				}*/
				
				// add bonus to total
				$this->data['total']['cost_after_bonus'] = $this->data['total']['cost'] + $this->data['total']['bonus_amt'];
			}
		}	
		
		//print_r($this->data);
		
		$smarty->assign('data', $this->data);
		$smarty->assign('no_bonus', $this->no_bonus);
		$smarty->assign('vp_global_si_info_list', $vp_global_si_info_list);
		//print_r($vp_global_cat_info_list);
		$smarty->assign('vp_global_cat_info_list', $vp_global_cat_info_list);
		
		$report_title = array();
		$report_title[] = "Branch: ".get_branch_code($bid);
		
		if($this->use_date_from || $this->use_date_to){
			$report_title[] = "From $this->use_date_from to $this->use_date_to";
		}else{
			$report_title[] = "Year: ".$year;
			$report_title[] = "Month: ".$this->month_list[$month];
		}
		
		
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
	
	function email_sales(){
		global $con, $smarty, $agrs;
		
		if(!is_using_terminal())	die("Invalid Access.");
		
		while($mode = array_shift($agrs)){
			switch($mode){
				case '-vendor_id':
					$tmp_vid = mi(array_shift($agrs));
					if(!$tmp_vid)	die("Invalid Vendor ID\n");
					break;
				case '-branch_id':
					$tmp_bid = mi(array_shift($agrs));
					if(!$tmp_bid)	die("Invalid Branch ID\n");
					break;
				case '-email_to':
					$tmp_email_contact = trim(array_shift($agrs));
					if(!$tmp_email_contact)	die("No Email Address\n");
					break;
				case '-year':
					$tmp_y = mi(array_shift($agrs));
					if($tmp_y < 2010)	die("Year cannot less then 2010.\n");
					break;
				case '-month':
					$tmp_m = mi(array_shift($agrs));
					if($tmp_m < 1 || $tmp_m > 12)	die("Invalid Month.\n");
					break;
				case '-date_from_to':
					$this->use_date_from = date("Y-m-d", strtotime(array_shift($agrs)));
					$this->use_date_to = date("Y-m-d", strtotime(array_shift($agrs)));
					
					if(date("Y", strtotime($this->use_date_from))<2010)	die("Year cannot less then 2010.\n");
					if(date("Y", strtotime($this->use_date_to))<2010)	die("Year cannot less then 2010.\n");
					break;	
			}
		}
		
		if($this->use_date_from && $this->use_date_to){	// prefix from - to
			$this->no_bonus = true;
			$date_str = "From $this->use_date_from to $this->use_date_to";
		}elseif($tmp_y || $tmp_m){	// prefix year month
			if(!$tmp_y || !$tmp_m)	die("Please provide year and month\n");
			$_REQUEST['year'] = $tmp_y;
			$_REQUEST['month'] = $tmp_m;
			
			$year = $_REQUEST['year'];
			$month = $_REQUEST['month'];
			$date_str = "Year $year Month $month";
		}else{	// default, last 7 days
			$this->use_date_to = date("Y-m-d", strtotime("-1 day", time()));
			$this->use_date_from = date("Y-m-d", strtotime("-6 day", strtotime($this->use_date_to)));
			$this->no_bonus = true;
			
			$this->no_bonus = true;
			$date_str = "From $this->use_date_from to $this->use_date_to";
		}

		$_REQUEST['load_report'] = 1;
		$smarty->assign('is_email', 1);
		$smarty->assign('no_header_footer', 1);
		
		
		print "$date_str\n";
		
		$filename = "consignee_daily_sales.html";
		
		include_once("include/class.phpmailer.php");
		$mailer = new PHPMailer(true);
		$mailer->FromName = "ARMS";
		$mailer->From = "arms@segigroup.com";
		$mailer->Subject = "ARMS - Consignee Daily Sales ($date_str)";
		$mailer->IsHTML(true);
		
		$filter = array();
		$filter[] = "v.active_vendor_portal=1";
		if($tmp_vid)	$filter[] = "vpi.vendor_id=$tmp_vid";
		
		$filter = "where ".join(' and ', $filter);
		
		$sql = "select vpi.*, v.code, v.description
from vendor_portal_info vpi
join vendor v on v.id=vpi.vendor_id
$filter order by vpi.vendor_id";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($vendor = $con->sql_fetchassoc($q1)){
			$vendor['sku_group_info'] = unserialize($vendor['sku_group_info']);
			$vendor['sales_report_profit'] = unserialize($vendor['sales_report_profit']);
			//print_r($vendor['sku_group_info']);
			
			if(!$vendor['sku_group_info'])	continue;	// no sku group info
			
			// get info by branch
			$vpbi = array();
			$q2 = $con->sql_query("select branch_id, contact_email,sales_report_profit_by_date,sales_bonus_by_step from vendor_portal_branch_info where vendor_id=".$vendor['vendor_id']." and active=1 and contact_email<>''");
			while($r = $con->sql_fetchassoc($q2)){
				if($r['contact_email']){
					$r['contact_email_list'] = explode(",", $r['contact_email']);
					if($r['contact_email_list']){
						foreach($r['contact_email_list'] as $key => $tmp_email){
							$r['contact_email_list'][$key] = trim($tmp_email);
						}
					}
				}
				
				$vpbi[$r['branch_id']] = $r;
			}
			$con->sql_freeresult();
			
			foreach($vendor['sku_group_info'] as $bid=> $sgi_info){
				if(!$vpbi[$bid] || !$vpbi[$bid]['contact_email_list'] || !is_array($vpbi[$bid]['contact_email_list']))	continue;
				//if($tmp_bid && $tmp_bid != $bid)	continue;	// got branch id filter
				
				list($sgi_bid, $sgi_id) = explode("|", $sgi_info);
				if(!$sgi_bid || !$sgi_id)	continue;
				
				print "Loading Vendor ID: $vendor[vendor_id] - $vendor[description], Branch ID: $bid,  sgi_bid: $sgi_bid, sgi_id: $sgi_id";
				
				$this->bid = $bid;
				
				$this->data = array();
				
				$params = array();
				$params['sku_group_bid'] = $sgi_bid;
				$params['sku_group_id'] = $sgi_id;
				$params['sales_report_profit_by_date_list'] = unserialize($vpbi[$bid]['sales_report_profit_by_date']);
				$params['sales_bonus_by_step'] = unserialize($vpbi[$bid]['sales_bonus_by_step']);
				$params['vendor_id'] = $vendor['vendor_id'];
				
				$this->load_report($params);
				
				if($this->data){	// got data to send
					file_put_contents($filename, $smarty->fetch('vp.consignee_daily_sales_report.tpl'));
				
					$mailer->ClearAddresses();
					$mailer->ClearAttachments();
					
					if($tmp_email_contact){
						$mailer->AddAddress($tmp_email_contact);
						if ($mailer->ValidateAddress($tmp_email_contact)) $proceed_send_email = true;
					}else{
						foreach($vpbi[$bid]['contact_email_list'] as $tmp_email){
							if ($tmp_email) {
								$mailer->AddAddress($tmp_email, $vendor['description']);
								if ($mailer->ValidateAddress($tmp_email)) $proceed_send_email = true;
							}
						}
					}
					$mailer->AddAttachment($filename, $filename);
					$mailer->Body = "Kindly please refer to attachment.";
	
					// send the mail
					if ($proceed_send_email) {
						if(phpmailer_send($mailer, $mailer_info)){
							print " - Send Successfully\n";
						}else{
							//print " - Send Failed ($mailer->ErrorInfo)\n";
							print " - Send Failed ($mailer_info[err])\n";
						}
					}
				}else{	// no sales, no need send
					print " - No Data\n";
				}
			}
		}
		$con->sql_freeresult($q1);
	}
}

$CONSIGNEE_DAILY_SALES_REPORT = new CONSIGNEE_DAILY_SALES_REPORT('Consignee Daily Sales Report');

?>
