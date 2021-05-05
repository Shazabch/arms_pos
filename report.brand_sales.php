<?php
/*
2/20/2013 4:10 PM Andy
- A new report going to replace old "Daily Brand Sales Report".

4:44 PM 11/27/2014 Andy
- Enhance report to show the sales amount using the amount-discount-discount2-tax_amount.(discount2 is receipt and mix & match discount, tax_amount is gst)

4/18/2015 10:43 AM Andy
- Enhanced to have report title.
- Add sorting by ARMS Code.

4/22/2015 3:00 PM Andy
- Fix report title branch code not show when login as branch.

12/12/2016 10:26 AM Andy
- Fixed wrong calculation of stock take adjustment.

6/7/2019 2:55 PM William
- Enhanced to take out discount for add column gross sales and total discount.

6/26/2019 9:18 AM William
- tpl file calculate gross amount change to use php calculate.

2/20/2020 10:39 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

12/02/2020 2:10 PM Rayleen
- Enhanced to add total per brand type
*/
include("include/common.php");
$maintenance->check(171);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class BRAND_SALES_REPORT extends Module {
	var $month_list = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
	var $branch_list = array();
	var $branch_group = array();
	var $no_price_type = 'NO_PT';
	var $selected_brand_id_list = array();
	
	// load from init load
	var $brand_list = array();
	var $brand_group = array();
	var $sku_type_list = array();
	var $price_type_list = array();
	var $dept_list = array();
	
	function __construct($title, $template=''){
		global $con, $smarty, $sessioninfo, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		// branch
		$con_multi->sql_query("select id,code,description from branch where active=1 order by sequence, code");
		while($r = $con_multi->sql_fetchassoc()){
			$this->branch_list[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branch_list', $this->branch_list);
		
		// branch group
		$con_multi->sql_query("select * from branch_group order by code");
		while($r = $con_multi->sql_fetchassoc()){
			$this->branch_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		
		// branch group items
		if($this->branch_group){
			$con_multi->sql_query("select * from branch_group_items");
			while($r = $con_multi->sql_fetchassoc()){
				if(!$this->branch_list[$r['branch_id']])	continue;	// the branch maybe in-active
				
				$this->branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
				$this->branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
			}
			$con_multi->sql_freeresult();
		}
		$smarty->assign('branch_group', $this->branch_group);
	
		$smarty->assign('month_list', $this->month_list);
		$smarty->assign('no_price_type', $this->no_price_type);
		
		parent::__construct($title, $template);
	}
	
	function _default(){
		global $con, $smarty, $sessioninfo;
		
		$this->init_load();
		
		if(!isset($_REQUEST['date_from']) && !isset($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date("Y-m-d");
			$_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month", strtotime($_REQUEST['date_to'])));
		}
		
		if($_REQUEST['show_report']){
			$this->generate_report();
			
			if($_REQUEST['export_excel']){
			    include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title);

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		}
		
		$this->display();
	}
	
	private function init_load(){
		global $con, $smarty, $sessioninfo, $con_multi;
		
		// brand
		$this->all_brand_filter = "brand.active=1";
		$con_multi->sql_query("select id,code,description from brand where ".$this->all_brand_filter." order by description");
		while($r = $con_multi->sql_fetchassoc()){
			$this->brand_list[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('brand_list', $this->brand_list);
		
		// brand group
		$con_multi->sql_query("select * from brgroup order by description");
		while($r = $con_multi->sql_fetchassoc()){
			$this->brand_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		
		$con_multi->sql_query("select * from brand_brgroup");
		while($r = $con_multi->sql_fetchassoc()){
			if(!$this->brand_list[$r['brand_id']])	continue;
			
			$this->brand_group['items'][$r['brgroup_id']][$r['brand_id']] = $r;
			$this->brand_group['have_group'][$r['brand_id']] = $r['brand_id'];
		}
		$con_multi->sql_freeresult();
		$smarty->assign('brand_group', $this->brand_group);
		
		// sku type
		$con_multi->sql_query("select * from sku_type");
		while($r = $con_multi->sql_fetchassoc()){
			$this->sku_type_list[] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('sku_type_list', $this->sku_type_list);
		
		// price type
		$con_multi->sql_query("select code as price_type from trade_discount_type order by code");
		while($r = $con_multi->sql_fetchassoc()){
			$this->price_type_list[] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('price_type_list', $this->price_type_list);
		
		// department
		$con_multi->sql_query("select id, code, description from category where level=2 and id in (".join(",",array_keys($sessioninfo['departments'])).") order by description");
		while($r = $con_multi->sql_fetchassoc()){
			$this->dept_list[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('dept_list', $this->dept_list);
	}
	
	private function generate_report(){
		global $con, $smarty, $sessioninfo, $config, $con_multi;
		
		$form = $_REQUEST;
		$report_title = array();
		//print_r($form);
		
		$bid_list = array();
		if(BRANCH_CODE == 'HQ'){
			$bid_list = array($form['branch_id']);
			$report_title[] = "Branch: ".get_branch_code($form['branch_id']);
		}else{
			// branch
			$bid_list = array($sessioninfo['branch_id']);
			$report_title[] = "Branch: ".BRANCH_CODE;
		}
		
		// validate parameters
		$brand_id = $form['brand_id'];
		$this->sku_type = trim($form['sku_type']);
		$this->price_type = trim($form['price_type']);
		$this->dept_id = mi($form['dept_id']);
		$this->date_from = trim($form['date_from']);
		$this->date_to = trim($form['date_to']);
		
		// checkbox
		$this->by_monthly = mi($form['by_monthly']);
		$this->group_by_sku = mi($form['group_by_sku']);
		$this->show_balance = mi($form['show_balance']);
		
		// report type
		$this->report_type = $form['report_type'] == 'amt' ? 'amt' : 'qty';
		
		$err = array();
		
		$this->selected_brand_id_list = array();
		if($brand_id){
			if(preg_match("/^group,/", $brand_id)){
				// brand group
				$brand_group_id = mi(str_replace("group,", "", $brand_id));
				if(!$this->brand_group['items'][$brand_group_id])	$err[] = "Invalid Brand.";
				else{
					foreach($this->brand_group['items'][$brand_group_id] as $tmp_brand_id => $r){
						$this->selected_brand_id_list[$tmp_brand_id] = $tmp_brand_id;
					}
					$report_title[] = "Brand Group: ".$this->brand_group['header'][$brand_group_id]['description'];
				}		
			}else{
				// single brand
				$brand_id = mi($brand_id);
				if(!$brand_id)	$err[] = "Invalid Brand.";
				else{
					$this->selected_brand_id_list[$brand_id] = $brand_id;
					$report_title[] = "Brand: ".$this->brand_list[$brand_id]['code'];
				}				
			}
		}else{
			// all
			$report_title[] = "Brand: All";
		}
		if(!$bid_list)	$err[] = "Please select branch.";
		if(!$this->date_from)	$err[] = "Please select date from.";
		if(!$this->date_to)	$err[] = "Please select date to.";
		if(date("Y", strtotime($this->date_from))<2000)	$err[] = "Date From cannot earlier then year 2000.";
		if(!$err && strtotime($this->date_to) < strtotime($this->date_from))	$err[] = "Date To cannot earlier then Date From.";
		
		if($err){
			$smarty->assign('err', $err);
			return false;
		}
		
		$report_title[] = "SKU Type: ".($this->sku_type ? $this->sku_type : 'All');
		$report_title[] = "Price Type: ".($this->price_type ? $this->price_type : 'All');
		if($this->dept_id){
			$cat_info = get_category_info($this->dept_id);
			$report_title[] = "Department: ".$cat_info['description'];
		}else{
			$report_title[] = "Department: All";
		}
		$report_title[] = "Date: ".$this->date_from." to ".$this->date_to;
		$report_title[] = "Report Type: ".($form['report_type'] == 'amt' ? 'Sales Amt' : 'Sales Qty');
				
		// construct date from to list
		$this->date_from_to_list = array();
		if($this->by_monthly){
			// by monthly
			$start_y = $curr_y = date("Y", strtotime($this->date_from));
			$max_y = date("Y", strtotime($this->date_to));
			
			while($curr_y <= $max_y){
				$upd = array();
				$upd['from'] = $curr_y.'-1-1';
				$upd['to'] = $curr_y.'-12-31';
				
				if($curr_y == $start_y){
					$upd['from'] = $this->date_from;
				}
				
				if($curr_y == $max_y){
					$upd['to'] = $this->date_to;
				}
				$this->date_from_to_list[$curr_y] = $upd;
				
				$curr_y++;
			}
		}else{
			// by daily
			$start_ym = $curr_ym = date("Ym", strtotime($this->date_from));
			$max_ym = date("Ym", strtotime($this->date_to));
			
			while($curr_ym <= $max_ym){
				$tmp_y = substr($curr_ym, 0, 4);
				$tmp_m = substr($curr_ym, 4, 2);
				
				$upd = array();
				$upd['from'] = $tmp_y.'-'.$tmp_m.'-1';
				$upd['to'] = $tmp_y.'-'.$tmp_m.'-'.days_of_month($tmp_m, $tmp_y);
				$upd['y'] = mi($tmp_y);
				$upd['m'] = mi($tmp_m);
				
				if($curr_ym == $start_ym){
					$upd['from'] = $this->date_from;
				}
				
				if($curr_ym == $max_ym){
					$upd['to'] = $this->date_to;
				}
				
				$tmp_m++;
				if($tmp_m > 12){
					$tmp_m = 1;
					$tmp_y++;
				}
				
				$this->date_from_to_list[$curr_ym] = $upd;
				
				$curr_ym = sprintf("%04d%02d", $tmp_y, $tmp_m);
			}
		}
		
		$this->data = array();
		
		//$con_multi= new mysql_multi();
		
		foreach($bid_list as $bid){
			$this->generate_data_by_branch($bid);
		}
		// var_dump('<pre>');
		// var_dump($this->date_from_to_list[201911]);
		// var_dump('</pre>');

		// print_r($this->data);
		$smarty->assign('data', $this->data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		$smarty->assign('date_from_to_list', $this->date_from_to_list);
	}
	
	private function generate_data_by_branch($bid){
		global $con, $smarty, $sessioninfo, $config, $con_multi;
		
		$bid = mi($bid);
		if(!$bid)	return false;
		
		// filter
		$filter = array();
		
		$filter[] = "pos.branch_id=".mi($bid);
		
		if($this->selected_brand_id_list){	// selected brand, or brand group
			$filter[] = "sku.brand_id in (".join(',', $this->selected_brand_id_list).")";
		}else{	// all brand, with some brand filter
			$filter[] = $this->all_brand_filter;
		}
		
		
		if($this->sku_type)	$filter[] = "sku.sku_type=".ms($this->sku_type);
		if($this->price_type)	$filter[] = "pi.trade_discount_code = ".ms($this->price_type);
		
		$filter[] = "pos.cancel_status=0 and pf.finalized=1";
		$filter[] = "c.department_id in (".join(",",array_keys($sessioninfo['departments'])).")";
		
		if($this->dept_id)	$filter[] = "c.department_id = ".$this->dept_id;
		
		foreach($this->date_from_to_list as $tmp_key => $tmp_date_from_to_info){
			$this->date_from_to_list[$tmp_key]['date_info'] = $this->construct_ym_list($tmp_date_from_to_info);
			
			$sales_filter = $filter;
			
			$sales_filter[] = "pos.date between ".ms($tmp_date_from_to_info['from'])." and ".ms($tmp_date_from_to_info['to']);
			
			$sales_filter = 'where '.join(' and ', $sales_filter);
			
			$sql = "select sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) as amt, sum(pi.qty) as qty, sku.sku_type, month(pos.date) as month, year(pos.date) as year, sum(pi.discount+pi.discount2) as discount,
			pos.date as dt, si.sku_item_code, ifnull(si.artno,si.mcode) as artno_mcode, si.description,
			pi.trade_discount_code as price_type,pi.sku_item_id,si.sku_id,uom.fraction as packing_uom_fraction,si.is_parent, sku.brand_id
		from pos
		left join pos_items pi on pi.branch_id=pos.branch_id and pi.pos_id=pos.id and pi.date=pos.date and pi.counter_id=pos.counter_id
		left join sku_items si on si.id = pi.sku_item_id
		left join sku on si.sku_id = sku.id
		left join uom on uom.id=si.packing_uom_id
		left join category c on sku.category_id = c.id
		left join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date and pf.finalized=1
		join brand on brand.id=sku.brand_id
		$xtra_join
		$sales_filter
		group by dt, pi.sku_item_id, price_type order by price_type";
			//print $sql."<br>";
			$q1 = $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchassoc($q1)){
				$sku_id = mi($r['sku_id']);
				$sid = mi($r['sku_item_id']);
				$ym = date("Ym", strtotime($r['dt']));
				$amt = $r['amt'];
				$discount = $r['discount'];
				$price_type =  $r['price_type'] ? $r['price_type'] :$this->no_price_type;
				$brand_id = mi($r['brand_id']);
				
				if($this->group_by_sku){	// group by sku
					$key = $sku_id;
					$qty = $r['qty'] * $r['packing_uom_fraction'];
					
					// store parent info only
					if(!isset($this->data['item_info'][$key])){
						$this->data['item_info'][$key] = array();
						
						// group by sku only store parent info
						if($r['is_parent']){
							$this->data['item_info'][$key]['info'] = $this->construct_item_info($r);
						}
					}
					
					// store sku id list
					if(!$this->date_from_to_list[$tmp_key]['sku_id_list'][$sku_id][$bid])	$this->date_from_to_list[$tmp_key]['sku_id_list'][$sku_id][$bid] = $sku_id;
					
					// store price type list
					if($this->by_monthly)	$this->date_from_to_list[$tmp_key]['item_price_type_list'][$sku_id][$price_type] = $price_type;
				}else{	// no group sku
					$key = $sid;
					$qty = $r['qty'];
					
					// store info by individual sku items
					if(!isset($this->data['item_info'][$key])){
						$this->data['item_info'][$key]['info'] = $this->construct_item_info($r);
					}
					
					if(!$this->date_from_to_list[$tmp_key]['sid_list'][$sid][$bid])	$this->date_from_to_list[$tmp_key]['sid_list'][$sid][$bid] = $sid;
					
					// store price type list
					if($this->by_monthly)	$this->date_from_to_list[$tmp_key]['item_price_type_list'][$sid][$price_type] = $price_type;
				}
				
				// item sales
				$this->data['item_sales'][$key][$ym][$price_type][$r['dt']]['qty'] += $qty;
				$this->data['item_sales'][$key][$ym][$price_type][$r['dt']]['amt'] += $amt;
				$this->data['item_sales'][$key][$ym][$price_type][$r['dt']]['discount'] += $discount;
				$this->data['item_sales'][$key][$ym][$price_type][$r['dt']]['gross_amt'] += $discount;
				$this->data['item_sales'][$key][$ym][$price_type][$r['dt']]['gross_amt'] += $amt;
				
				// item sales total by price type
				$this->data['item_sales'][$key][$ym][$price_type]['total']['qty'] += $qty;
				$this->data['item_sales'][$key][$ym][$price_type]['total']['amt'] += $amt;
				$this->data['item_sales'][$key][$ym][$price_type]['total']['discount'] += $discount;
				$this->data['item_sales'][$key][$ym][$price_type]['total']['gross_amt'] += $discount;
				$this->data['item_sales'][$key][$ym][$price_type]['total']['gross_amt'] += $amt;
				
				// item sales total by row
				$this->data['item_total'][$key][$ym]['qty'] += $qty;
				$this->data['item_total'][$key][$ym]['amt'] += $amt;
				$this->data['item_total'][$key][$ym]['discount'] += $discount;
				$this->data['item_total'][$key][$ym]['gross_amt'] += $discount;
				$this->data['item_total'][$key][$ym]['gross_amt'] += $amt;
				$this->data['item_total'][$key][$ym]['avg_selling'] = $this->data['item_total'][$key][$ym]['qty'] ? round($this->data['item_total'][$key][$ym]['amt'] / $this->data['item_total'][$key][$ym]['qty'], 2) : 0;
				
				if($this->by_monthly){
					$this->data['item_total'][$key]['by_year'][$tmp_key]['qty'] += $qty;
					$this->data['item_total'][$key]['by_year'][$tmp_key]['amt'] += $amt;
					$this->data['item_total'][$key]['by_year'][$tmp_key]['discount'] += $discount;
					$this->data['item_total'][$key]['by_year'][$tmp_key]['gross_amt'] += $discount;
					$this->data['item_total'][$key]['by_year'][$tmp_key]['gross_amt'] += $amt;
					$this->data['item_total'][$key]['by_year'][$tmp_key]['avg_selling'] = $this->data['item_total'][$key]['by_year'][$tmp_key]['qty'] ? round($this->data['item_total'][$key]['by_year'][$tmp_key]['amt'] / $this->data['item_total'][$key]['by_year'][$tmp_key]['qty'], 2) : 0;
				}
				
				if($this->group_by_sku){
					// store the sku item id by date, need to store by branch
					$this->data['item_sales'][$key][$ym][$price_type][$r['dt']]['sid_list'][$sid][$bid] = $sid;
				}
				
				// mark got data
				$this->date_from_to_list[$tmp_key]['got_data'] = 1;
				
				// mark brand
				$this->date_from_to_list[$tmp_key]['brand_info']['brand_id_list'][$brand_id]['item_list'][$key] = 1;
				
				// brand total
				if($this->by_monthly){
					$this->date_from_to_list[$tmp_key]['brand_info']['total']['by_month'][$brand_id][$ym]['qty'] += $qty;
					$this->date_from_to_list[$tmp_key]['brand_info']['total']['by_month'][$brand_id][$ym]['amt'] += $amt;
					$this->date_from_to_list[$tmp_key]['brand_info']['total']['by_month'][$brand_id][$ym]['discount'] += $discount;
					$this->date_from_to_list[$tmp_key]['brand_info']['total']['by_month'][$brand_id][$ym]['gross_amt'] += $discount;
					$this->date_from_to_list[$tmp_key]['brand_info']['total']['by_month'][$brand_id][$ym]['gross_amt'] += $amt;
				}else{
					$this->date_from_to_list[$tmp_key]['brand_info']['total']['by_date'][$brand_id][$r['dt']]['qty'] += $qty;
					$this->date_from_to_list[$tmp_key]['brand_info']['total']['by_date'][$brand_id][$r['dt']]['amt'] += $amt;
					$this->date_from_to_list[$tmp_key]['brand_info']['total']['by_date'][$brand_id][$r['dt']]['discount'] += $discount;
					$this->date_from_to_list[$tmp_key]['brand_info']['total']['by_date'][$brand_id][$r['dt']]['gross_amt'] += $discount;
					$this->date_from_to_list[$tmp_key]['brand_info']['total']['by_date'][$brand_id][$r['dt']]['gross_amt'] += $amt;
				}
				
				$this->date_from_to_list[$tmp_key]['brand_info']['total']['total'][$brand_id][$ym]['qty'] += $qty;
				
				// group > sales total
				$group_total_key = $r['dt'];
				if($this->by_monthly)	$group_total_key = $ym;

				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key]['qty'] += $qty;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key]['amt'] += $amt;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key]['discount'] += $discount;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key]['gross_amt'] += $discount;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key]['gross_amt'] += $amt;
				
				$this->date_from_to_list[$tmp_key]['group_total']['sales']['total']['qty'] += $qty;
				$this->date_from_to_list[$tmp_key]['group_total']['sales']['total']['amt'] += $amt;
				$this->date_from_to_list[$tmp_key]['group_total']['sales']['total']['discount'] += $discount;
				$this->date_from_to_list[$tmp_key]['group_total']['sales']['total']['gross_amt'] += $discount;
				$this->date_from_to_list[$tmp_key]['group_total']['sales']['total']['gross_amt'] += $amt;
				$this->date_from_to_list[$tmp_key]['group_total']['sales']['total']['avg_selling'] = $this->date_from_to_list[$tmp_key]['group_total']['sales']['total']['qty'] ? round($this->date_from_to_list[$tmp_key]['group_total']['sales']['total']['amt'] / $this->date_from_to_list[$tmp_key]['group_total']['sales']['total']['qty'], 2) : 0;

				// sales total by brand
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key][$brand_id]['qty'] += $qty;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key][$brand_id]['amt'] += $amt;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key][$brand_id]['discount'] += $discount;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key][$brand_id]['gross_amt'] += $discount;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key][$brand_id]['gross_amt'] += $amt;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key][$brand_id]['avg_selling'] = $this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key][$brand_id]['qty'] ? round($this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key][$brand_id]['amt'] / $this->date_from_to_list[$tmp_key]['group_total']['sales'][$group_total_key][$brand_id]['qty'], 2) : 0;

				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$brand_id]['qty'] += $qty;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$brand_id]['amt'] += $amt;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$brand_id]['discount'] += $discount;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$brand_id]['gross_amt'] += $discount;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$brand_id]['gross_amt'] += $amt;
				$this->date_from_to_list[$tmp_key]['group_total']['sales'][$brand_id]['avg_selling'] = $this->date_from_to_list[$tmp_key]['group_total']['sales'][$brand_id]['qty'] ? round($this->date_from_to_list[$tmp_key]['group_total']['sales'][$brand_id]['amt'] / $this->date_from_to_list[$tmp_key]['group_total']['sales'][$brand_id]['qty'], 2) : 0;
			}
			$con_multi->sql_freeresult($q1);
			
			// sort item
			if($this->date_from_to_list[$tmp_key]['brand_info']['brand_id_list']){
				foreach($this->date_from_to_list[$tmp_key]['brand_info']['brand_id_list'] as $brand_id => $brand_item_list){
					uksort($this->date_from_to_list[$tmp_key]['brand_info']['brand_id_list'][$brand_id]['item_list'], array($this, "sort_item_id"));
				}
			}
			
			// get parent info
			if($this->group_by_sku && $this->data){	
				// get the item info
				$sku_id_list_to_get = array();
				foreach($this->data['item_info'] as $sku_id => $r){
					if(!$r['info'])	$sku_id_list_to_get[] = $sku_id;
				}
				
				// got sku id to get
				if($sku_id_list_to_get){
					$con_multi->sql_query("select si.id as sku_item_id, si.sku_item_code, ifnull(si.artno,si.mcode) as artno_mcode, si.description, sku.sku_type, si.sku_id, sku.brand_id
					from sku_items si
					left join sku on sku.id=si.sku_id
					where si.sku_id in (".join(',', $sku_id_list_to_get).") and si.is_parent=1");
					while($r = $con_multi->sql_fetchassoc()){
						$this->data['item_info'][$r['sku_id']]['info'] = $this->construct_item_info($r);
					}
					$con_multi->sql_freeresult();
				}
				unset($sku_id_list_to_get);
			}
			
			if($this->data && $this->show_balance){
				// get balance data
				if($this->group_by_sku){
					if($this->date_from_to_list[$tmp_key]['sku_id_list']){
						foreach($this->date_from_to_list[$tmp_key]['sku_id_list'] as $sku_id => $tmp_r){
							if($tmp_r[$bid]){
								$brand_id = mi($this->data['item_info'][$sku_id]['info']['brand_id']);
								
								$params = array();
								$params['sku_id'] = $sku_id;
								$params['date_from'] = $tmp_date_from_to_info['from'];
								$params['date_to'] = $tmp_date_from_to_info['to'];
								
								$balance_info = $this->get_stock_balance_info_by_ym($bid, $params);
								
								// item changed
								if($balance_info['changed'])	$this->data['item_info'][$sku_id]['changed'] = 1;
								unset($balance_info['changed']);
								
								if($balance_info['multi_vendor']){
									$this->data['item_info'][$sku_id]['multi_vendor'] = 1;
								}
								unset($balance_info['multi_vendor']);
								
								$this->date_from_to_list[$tmp_key]['balance_info'][$sku_id][$bid] = $balance_info;
								
								foreach(array('opening', 'closing', 'in', 'out') as $tmp_type){
									// group > balance info by item
									$this->date_from_to_list[$tmp_key]['balance_info'][$sku_id]['total'][$tmp_type]['qty'] += $balance_info[$tmp_type]['qty'];
									
									// group > balance info total
									$this->date_from_to_list[$tmp_key]['balance_info']['total']['total'][$tmp_type]['qty'] += $balance_info[$tmp_type]['qty'];
									
									// brand > balance info total
									$this->date_from_to_list[$tmp_key]['balance_info']['brand'][$brand_id][$tmp_type]['qty'] += $balance_info[$tmp_type]['qty'];
								}
							}							
						}
					}
				}else{
					if($this->date_from_to_list[$tmp_key]['sid_list']){
						foreach($this->date_from_to_list[$tmp_key]['sid_list'] as $sid => $tmp_r){
							if($tmp_r[$bid]){	
								$brand_id = mi($this->data['item_info'][$sid]['info']['brand_id']);
								
								$params = array();
								$params['sid'] = $sid;
								$params['date_from'] = $tmp_date_from_to_info['from'];
								$params['date_to'] = $tmp_date_from_to_info['to'];
								
								$balance_info = $this->get_stock_balance_info_by_ym($bid, $params);
								
								// item changed
								if($balance_info['changed'])	$this->data['item_info'][$sid]['changed'] = 1;
								unset($balance_info['changed']);
								
								// multiple vendor
								if($balance_info['multi_vendor']){
									$this->data['item_info'][$sid]['multi_vendor'] = 1;
								}
								unset($balance_info['multi_vendor']);
								
								$this->date_from_to_list[$tmp_key]['balance_info'][$sid][$bid] = $balance_info;
								
								foreach(array('opening', 'closing', 'in', 'out') as $tmp_type){
									// group > balance info by item
									$this->date_from_to_list[$tmp_key]['balance_info'][$sid]['total'][$tmp_type]['qty'] += $balance_info[$tmp_type]['qty'];
									
									// group > balance info total
									$this->date_from_to_list[$tmp_key]['balance_info']['total']['total'][$tmp_type]['qty'] += $balance_info[$tmp_type]['qty'];
									
									// brand > balance info total
									$this->date_from_to_list[$tmp_key]['balance_info']['brand'][$brand_id][$tmp_type]['qty'] += $balance_info[$tmp_type]['qty'];
								}
							}							
						}
					}
				}
			}
			
			
		}
	}
	
	private function construct_item_info($r){
		$info_fields = array('sku_item_id', 'sku_item_code', 'artno_mcode', 'description', 'sku_type', 'sku_id', 'brand_id');
		
		$ret = array();
		foreach($info_fields as $f){
			$ret[$f] = $r[$f];
		}
		return $ret;
	}
	
	private function construct_ym_list($date_info){	
		$date_start = $date_info['from'];
		$date_end = $date_info['to'];
		
		$time_start = strtotime($date_start);
		$time_end = strtotime($date_end);
		
		$ret = array();
		//$ret['y'] = mi(date("Y", strtotime($date_start)));
		
		if(!$this->by_monthly){
			//$ret['m'] = mi(date("m", strtotime($date_start)));
		}else{
			$ret['ym_list'] = array();
		}
		
		while($time_start <= $time_end){
			if($this->by_monthly){
				// by monthly
				$ym = date("Ym", $time_start);
				if(!$ret['ym_list'][$ym]){
					$tmp = array();
					//$tmp['y'] = mi(date("y", $time_start));
					$tmp['m'] = mi(date("m", $time_start));
					$ret['ym_list'][$ym] = $tmp;
				}			
			}else{
				// by date
				$ret['date_list'][] = date("Y-m-d", $time_start);
			}
			
			$time_start+= 86400;
		}
		return $ret;
	}
	
	private function get_stock_balance_info_by_ym($bid, $params){
		global $con_multi;
		
		$date_start = $params['date_from'];
		$date_end = $params['date_to'];
		
		$ret = array();
		$y_start = mi(date("Y", strtotime($date_start)));
		$m_start = mi(date("m", strtotime($date_start)));
		
		$y_end = mi(date("Y", strtotime($date_end)));
		$m_end = mi(date("m", strtotime($date_end)));
		
		if($params['sku_id']){
			$item_filter = "si.sku_id=".mi($params['sku_id']);
		}elseif($params['sid']){
			$item_filter = "si.id=".mi($params['sid']);
		}else	return $ret;
		
		if(!$y_start || !$m_start || !$y_end || !$m_end)	return $ret;
		
		$open_bal_date = date("Y-m-d", strtotime("-1 day", strtotime($date_start)));
		
		$sb_open = 'stock_balance_b'.$bid.'_'.date("Y", strtotime($open_bal_date));
		$sb_end = 'stock_balance_b'.$bid.'_'.date("Y", strtotime($date_end));
		
		// opening and closing
		$q1 = $con_multi->sql_query_false("select si.id as sid, sb.qty as open_qty, sb2.qty as close_qty, uom.fraction as packing_uom_fraction, ifnull(sic.changed, 1) as changed
		from sku_items si
		left join uom on uom.id=si.packing_uom_id
		left join $sb_open sb on ".ms($open_bal_date)." between sb.from_date and sb.to_date and sb.sku_item_id=si.id
		left join $sb_end sb2 on ".ms($date_end)." between sb2.from_date and sb2.to_date and sb2.sku_item_id=si.id
		left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
		where $item_filter");
		while($r = $con_multi->sql_fetchassoc($q1)){
			$open_qty = $r['open_qty'];
			$close_qty = $r['close_qty'];
			
			if($this->group_by_sku){
				$open_qty *= $r['packing_uom_fraction'];
				$close_qty *= $r['packing_uom_fraction']; 
			}
			
			if($r['changed'])	$ret['changed'] = 1;
			
			$ret['opening']['qty'] += $open_qty;
			$ret['closing']['qty'] += $close_qty;
		}
		$con_multi->sql_freeresult($q1);
		
		// get stock check and check their stock adjust
		$q2 = $con_multi->sql_query("select si.id as sid, sc.date as sc_date, sum(sc.qty) as sc_qty, uom.fraction as packing_uom_fraction
		from sku_items si
		left join uom on uom.id=si.packing_uom_id
		join stock_check sc on sc.sku_item_code=si.sku_item_code and sc.date between ".ms($date_start)." and ".ms($date_end)."
		where $item_filter and sc.branch_id=$bid
		group by sid, date");
		while($r = $con_multi->sql_fetchassoc($q2)){
			$sc_date = $r['sc_date'];
			
			$sb_date = date("Y-m-d", strtotime("-1 day", strtotime($sc_date)));
			$sb_tbl = 'stock_balance_b'.$bid.'_'.date("Y", strtotime($sb_date));
			
			$q_sb = $con_multi->sql_query("select sb.qty as qty
			from $sb_tbl sb
			where sb.sku_item_id=".mi($r['sid'])." and ".ms($sb_date)." between sb.from_date and sb.to_date");
			$sb_info = $con_multi->sql_fetchassoc($q_sb);
			$con_multi->sql_freeresult($q_sb);
			
			$adj_qty = $r['sc_qty'] - $sb_info['qty'];
			if($this->group_by_sku){
				$adj_qty *= $r['packing_uom_fraction'];
			}
			
			if($adj_qty > 0){
				$ret['in']['qty'] += $adj_qty;
			}elseif($adj_qty < 0){
				$ret['out']['qty'] += abs($adj_qty);
			}
			$ret['got_sc'] = 1;
		}
		$con_multi->sql_freeresult($q2);
		
		// grn
		//GRN = get the rcvd qty, rcvd cost and grn qty
		$q3 = $con_multi->sql_query("select gi.sku_item_id as sid, grr.vendor_id,
		sum(if (gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn *rcv_uom.fraction + gi.pcs, gi.acc_ctn *rcv_uom.fraction + gi.acc_pcs)) as qty,
		sum(if (gi.acc_ctn is null and gi.acc_pcs is null,
	(gi.ctn  + (gi.pcs / rcv_uom.fraction)),
	(gi.acc_ctn + (gi.acc_pcs / rcv_uom.fraction))) *
	if (gi.acc_cost is null, gi.cost,gi.acc_cost)) as total_rcv_cost, uom.fraction as packing_uom_fraction
		from grn_items gi
		left join uom rcv_uom on gi.uom_id=rcv_uom.id
		left join grn on gi.grn_id = grn.id and gi.branch_id = grn.branch_id
		left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
		join sku_items si on si.id=gi.sku_item_id
		left join uom on uom.id=si.packing_uom_id
		where $item_filter and grn.branch_id=$bid and grr.rcv_date between ".ms($date_start)." and ".ms($date_end)." and grn.approved=1 and grn.status=1 and grn.active=1 and grr.active=1
		group by sid");
    	while($r = $con_multi->sql_fetchassoc($q3)){
    		$qty = $r['qty'];
    		if($this->group_by_sku){
    			$qty *= $r['packing_uom_fraction'];
    		}
    		$ret['in']['qty'] += $qty;
    		
    		if($this->use_grn && $this->vendor_id != $r['vendor_id'])	$ret['multi_vendor'] = 1;
    	}
    	$con_multi->sql_freeresult($q3);
    	
    	//ADJ = get adj in and adj out
		$q4 = $con_multi->sql_query("select ai.sku_item_id as sid, sum(qty) as qty, uom.fraction as packing_uom_fraction
		from adjustment_items ai
		left join adjustment adj on adjustment_id = adj.id and ai.branch_id = adj.branch_id
		join sku_items si on si.id=ai.sku_item_id
		left join uom on uom.id=si.packing_uom_id
		where $item_filter and ai.branch_id=$bid and adjustment_date between ".ms($date_start)." and ".ms($date_end)." and adj.active=1 and adj.approved=1 and adj.status=1
		group by sid");
    	while($r = $con_multi->sql_fetchassoc($q4)){
    		$qty = $r['qty'];
    		if($this->group_by_sku){
    			$qty *= $r['packing_uom_fraction'];
    		}
    		
    		if($qty > 0){
				$ret['in']['qty'] += $qty;
			}elseif($adj_qty < 0){
				$ret['out']['qty'] += abs($qty);
			}
    	}
    	$con_multi->sql_freeresult($q4);
    	
    	//DO get do qty
		$q5 = $con_multi->sql_query("select di.sku_item_id as sid, sum(di.ctn *do_uom.fraction + di.pcs) as qty, uom.fraction as packing_uom_fraction
		from do_items di
		left join do on di.do_id = do.id and di.branch_id = do.branch_id
		left join uom do_uom on di.uom_id=do_uom.id
		join sku_items si on si.id=di.sku_item_id
		left join uom on uom.id=si.packing_uom_id
		where $item_filter and di.branch_id=$bid and do_date between ".ms($date_start)." and ".ms($date_end)." and do.active=1 and do.approved=1 and do.checkout=1 and do.status=1
		group by sid");
    	while($r = $con_multi->sql_fetchassoc($q5)){
    		$qty = $r['qty'];
    		if($this->group_by_sku){
    			$qty *= $r['packing_uom_fraction'];
    		}
    		
    		$ret['out']['qty'] += abs($qty);
    	}
    	$con_multi->sql_freeresult($q5);
    	
    	//GRA get the gra qty.
		$q6 = $con_multi->sql_query("select gi.sku_item_id as sid, sum(qty) as qty, uom.fraction as packing_uom_fraction
		from gra_items gi
		left join gra on gi.gra_id = gra.id and gi.branch_id = gra.branch_id
		join sku_items si on si.id=gi.sku_item_id
		left join uom on uom.id=si.packing_uom_id
		where $item_filter and gra.branch_id=$bid and return_timestamp between ".ms($date_start)." and ".ms($date_end." 23:59:59")." and gra.status=0 and gra.returned=1
		group by sid");
    	while($r = $con_multi->sql_fetchassoc($q6)){
    		$qty = $r['qty'];
    		if($this->group_by_sku){
    			$qty *= $r['packing_uom_fraction'];
    		}
    		
    		$ret['out']['qty'] += abs($qty);
    	}
    	$con_multi->sql_freeresult($q6);
    	
		return $ret;
	}
	
	function sort_item_id($a, $b){
		$code1 = $this->data['item_info'][$a]['info']['sku_item_code'];
		$code2 = $this->data['item_info'][$b]['info']['sku_item_code'];
		
		if($code1 == $code2)	return 0;
		return $code1 > $code2 ? 1 : -1;
	}
}

$BRAND_SALES_REPORT = new BRAND_SALES_REPORT('Daily Brand Sales Report');
?>
