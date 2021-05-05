<?php
/*
6/27/2013 4:28 PM Andy
- Add can choose whether group by same price or not.

6/28/2013 10:51 AM Andy
- Change the Total Disc value from trade discount to item discount.
- Change Gross Sales to Nett Sales.

5/16/2014 11:31 AM Justin
- Enhanced to allow user to either select brand, vendor or department filter.
- Enhanced to have new filter "Location".

5/29/2014 2:44 PM Fithri
- change filter setting to allow select all Vendor / Brand / Department
- include option to select by Brand Group in Brand filter
- change report name to Category Sales Report by SKU

5/29/2014 5:33 PM Fithri
- add SKU Type filter

6/4/2014 2:16 PM Fithri
- bugfix: Trade Discount Type filter will only affect SKU type consignment

10/22/2018 4:38 PM Justin
- Enhanced to check trade discount filter when SKU type is CONSIGN.
*/
include("../../include/common.php");
$maintenance->check(130);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_CUSTOM1')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_CUSTOM1', BRANCH_CODE), "/index.php");

class CONSIGNMENT_CATEGORY_SALES_REPORT extends Module{
	var $branch_list = array();
	var $brand_list = array();
	var $vendor_list = array();
	var $dept_list = array();
	var $discount_type_list = array();
	
	function __construct($title, $template=''){
		global $con, $sessioninfo, $smarty;
		
		if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
		if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));

		// branch list
		$this->branch_list = array();
		$con->sql_query("select id,code,description from branch where active=1 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branch_list', $this->branch_list);
		
		parent::__construct($title, $template);
	}
	
	 function _default(){
	 	global $con, $smarty, $sessioninfo;
	 	
	 	$this->init_load();
	 	
	 	if($_REQUEST['load_report']){
		 	$this->load_report();
		 	if($_REQUEST['submit_type'] == 'excel'){
			    include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." To Excel");

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}elseif($_REQUEST['submit_type'] == 'print'){
				
			}
		}
	 	$this->display('ngiukee/consignment_category_sales_report.tpl');
	 }
	 
	 private function init_load(){
	 	global $con, $smarty, $sessioninfo;
	 	
	 	// brand list
	 	$this->brand_list = array();
	 	$con->sql_query("select * from brand where active=1 order by description");
	 	while($r = $con->sql_fetchassoc()){
			$this->brand_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('brand_list', $this->brand_list);
		$smarty->assign('brand_groups', get_brand_group());
		
		// vendor list
		$this->vendor_list = array();
	 	$con->sql_query("select id,code,description from vendor where active=1 order by description");
	 	while($r = $con->sql_fetchassoc()){
			$this->vendor_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('vendor_list', $this->vendor_list);
		
		// sku type list
		$this->sku_type_list = array();
	 	$con->sql_query("select code, description from sku_type where active=1 order by description");
	 	while($r = $con->sql_fetchassoc()){
			$this->sku_type_list[$r['code']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('sku_type_list', $this->sku_type_list);
		
		// dept list
		$this->dept_list = array();
		$filter = array();
		$filter[] = "c.active=1 and c.level=2";
		if($sessioninfo['level']<9999)	$filter[] = "c.id in (".$sessioninfo['department_ids'].")";
		$filter = join(' and ', $filter);
	 	$con->sql_query("select id,code,description from category c where level=2 and active=1 order by description");
	 	while($r = $con->sql_fetchassoc()){
			$this->dept_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('dept_list', $this->dept_list);
		
		// discount_type_list
		$this->discount_type_list = array();
	 	$con->sql_query("select * from trade_discount_type order by code");
	 	while($r = $con->sql_fetchassoc()){
			$this->discount_type_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('discount_type_list', $this->discount_type_list);
		
		// locations from counters
		$q1 = $con->sql_query("select distinct(location) as location from counter_settings where active=1 and location != '' and location is not null order by location limit 10");
		
	 	while($r = $con->sql_fetchassoc($q1)){
			$this->location_list[$r['location']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign('location_list', $this->location_list);
	 }
	 
	 private function load_report(){
	 	global $con, $smarty, $sessioninfo;
	 	
	 	$form = $_REQUEST;
	 	//print_r($form);
	 	
	 	if(BRANCH_CODE == 'HQ'){
	 		$bid = mi($form['branch_id']);
	 	}else	$bid = $sessioninfo['branch_id'];
	 	
	 	$brand_id = $form['brand_id'];
		$vendor_id = mi($form['vendor_id']);
		$dept_id = mi($form['dept_id']);
		$sku_type = $form['sku_type'];
		$trade_discount_type_list = $form['trade_discount_type'];
		$location = $form['location'];
		$date_from = trim($form['date_from']);
		$date_to = trim($form['date_to']);
		$group_same_price = mi($form['group_same_price']);
		
	 	$err = array();
	 	if(!$bid)	$err[] = "Invalid branch.";
	 	//if(!$vendor_id && !$dept_id)	$err[] = "Please select Vendor or Department.";
		//if(!$trade_discount_type_list)	$err[] = "Invalid Trade Discount Type.";
		if(!$date_from)	$err[] = "Invalid Date From.";
		if(!$date_to)	$err[] = "Invalid Date To.";
		if($date_from && $date_to){
			if(strtotime($date_from) < strtotime('2000-01-01'))	$err[] = "Date From earlier than 2000-01-01";
			if(strtotime($date_to) < strtotime('2000-01-01'))	$err[] = "Date To earlier than 2000-01-01";
			if(!$err && strtotime($date_to) < strtotime($date_from))	$err[] = "Date To cannot earlier then Date From.";
		}
		if ($sku_type == 'CONSIGN') {
			if(!$trade_discount_type_list)	$err[] = "Please select at least 1 Trade Discount Type.";
		}
		if(!$location)	$err[] = "Please select at least 1 Location.";
	 	
	 	if($err){
	 		$smarty->assign('err', $err);
	 		return false;
	 	}
	 	
	 	$filter = array();
	 	$filter[] = "pos.branch_id=$bid and pos.cancel_status=0";
	 	$filter[] = "pos.date between ".ms($date_from)." and ".ms($date_to);
	 	if ($brand_id != '') $filter[] = "sku.brand_id in (".join(',',process_brand_id($brand_id)).")";
	 	if ($sku_type) $filter[] = "sku.sku_type=".ms($sku_type);
	 	if($vendor_id) $filter[] = "sku.vendor_id=$vendor_id";
	 	if($dept_id) $filter[] = "cc.p2=$dept_id";
	 	$filter[] = "pi.writeoff_by=0";
		
		/*
		if ($sku_type != 'OUTRIGHT') {
			$code_list = array();
			foreach($trade_discount_type_list as $code){
				$code_list[] = ms($code);
			}
			//$filter[] = "pi.trade_discount_code in (".join(',', $code_list).")";
			$having = "having tdt in (".join(',', $code_list).")";
		}
		*/
	 	$filter[] = "cs.location in ('".join("','", $location)."')";
		
	 	$filter = "where ".join(' and ', $filter);
	 	
	 	$sql = "select pi.sku_item_id, pi.qty,pi.price,pi.discount,pi.date,pi.trade_discount_code, cc.p3, si.mcode, si.description, sku.trade_discount_type, si.sku_item_code, si.link_code, sku.default_trade_discount_code,
	 	if(pi.trade_discount_code='' or pi.trade_discount_code is null, sku.default_trade_discount_code, pi.trade_discount_code) as tdt,cs.network_name,pos.receipt_no,pos.counter_id,pos.id as pos_id, sku.sku_type
from pos_items pi
join pos on pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id and pos.id=pi.pos_id
join sku_items si on si.id=pi.sku_item_id
join sku on sku.id=si.sku_id
left join category_cache cc on cc.category_id=sku.category_id
left join counter_settings cs on cs.branch_id=pos.branch_id and cs.id=pos.counter_id
$filter
order by pos.date, cc.p3, pi.sku_item_id, trade_discount_code, pi.price";
 		//print $sql;
 		
 		$this->data = array();
 		$q1 = $con->sql_query($sql);
 		while($r = $con->sql_fetchassoc($q1)){
 			$key = -1;
 			$trade_discount_code = trim($r['tdt']);
			
			if ($r['sku_type'] == 'CONSIGN' && !in_array($trade_discount_code,$trade_discount_type_list)) continue;
			
 			//if(!$trade_discount_code)	$trade_discount_code = $r['default_trade_discount_code'];
 			
 			// si info
 			if(!isset($this->data['si_info'][$r['sku_item_id']])){
 				$this->data['si_info'][$r['sku_item_id']]['mcode'] = $r['mcode'];
 				$this->data['si_info'][$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];
 				$this->data['si_info'][$r['sku_item_id']]['link_code'] = $r['link_code'];
 				$this->data['si_info'][$r['sku_item_id']]['description'] = $r['description'];
 				//$this->data['si_info'][$r['sku_item_id']]['trade_discount_rate'] = 0;
 			}
 			
 			// cat info
 			if(!isset($this->data['cat_info'][$r['p3']])){
 				$cat_info = get_category_info($r['p3']);
 				$this->data['cat_info'][$r['p3']]['code'] = $cat_info['code'];
 				$this->data['cat_info'][$r['p3']]['description'] = $cat_info['description'];
 			}
 			
 			if($group_same_price){
 				if($this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list']){
	 				$key = $this->find_same_item_price_key($this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'], $r);
	 			}
 			}
 			
 			if($key<0){
 				if($this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list']){
 					$key = count($this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list']);
 				}else{
 					$key = 0;
 				}
 			}	

			// get discount rate
			/*if(!isset($this->data['si_info'][$r['sku_item_id']]['trade_discount_rate'])){
				if($r['trade_discount_type']==1){    // use brand
					$q_rate = $con->sql_query("select rate from brand_commission where branch_id=$bid and brand_id=$brand_id and department_id=$dept_id and skutype_code=".ms($trade_discount_code));
				}elseif($r['trade_discount_type']==2){   // use vendor
	                $q_rate = $con->sql_query("select rate from vendor_commission where branch_id=$bid and vendor_id=$vendor_id and department_id=$dept_id and skutype_code=".ms($trade_discount_code));
				}
				
				$tmp = $con->sql_fetchassoc($q_rate);
				$con->sql_freeresult($q_rate);
				
				$this->data['si_info'][$r['sku_item_id']]['trade_discount_rate'] = mf($tmp['rate']);
			}*/
				
 			$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['sid'] = $r['sku_item_id'];
 			$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['price'] = $r['price'];
 			$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['amt'] += $r['price'];
 			$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['qty'] += $r['qty'];
 			
 			if(!$group_same_price){
 				$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['counter_name'] = $r['network_name'];
	 			$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['receipt_no'] = $r['receipt_no'];
	 			
	 			$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['counter_id'] = $r['counter_id'];
	 			$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['pos_id'] = $r['pos_id'];
	 			
	 			$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['branch_id'] = $bid;
 			}
 			
 			// calculate cost
 			//$discount_rate = $this->data['si_info'][$r['sku_item_id']]['trade_discount_rate'];
 			//$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['total_cost'] = round(($this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['amt'] * ((100-$discount_rate)*0.01)),3);
 			$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['total_cost'] += round($r['discount'],2);
 			$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['nett_amt'] = round($this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['amt'] - $this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['item_list'][$key]['total_cost'],2);
 			
 			// total by cat + discount
 			$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['qty'] += $r['qty'];
 			$this->data['by_cat'][$r['date']][$dept_id]['cat_list'][$r['p3']][$trade_discount_code]['amt'] += $r['price'];
 			
 			// total by dept
 			$this->data['by_cat'][$r['date']][$dept_id]['qty'] += $r['qty'];
 			$this->data['by_cat'][$r['date']][$dept_id]['amt'] += $r['price'];
 			
 			$this->data['total']['qty'] += $r['qty'];
 			$this->data['total']['amt'] += $r['price'];
 			$this->data['total']['total_cost'] += round($r['discount'],2);
 			$this->data['total']['nett_amt'] = round($this->data['total']['amt'] - $this->data['total']['total_cost'], 2);
 		}
 		$con->sql_freeresult($q1);
 		
 		if($this->data['by_cat']){
 			// calculate total cost
 			foreach($this->data['by_cat'] as $dt => $dept_list){
 				foreach($dept_list as $tmp_dept_id => $dept_info){
 					foreach($dept_info['cat_list'] as $tmp_cat_id => $tmp_discount_type_list){
 						foreach($tmp_discount_type_list as $tmp_discount_type => $sales_info){
 							foreach($sales_info['item_list'] as $r){
 								// total by cat + discount
 								$this->data['by_cat'][$dt][$tmp_dept_id]['cat_list'][$tmp_cat_id][$tmp_discount_type]['total_cost'] += $r['total_cost'];
 								
 								// total by dept
 								$this->data['by_cat'][$dt][$tmp_dept_id]['total_cost'] += $r['total_cost'];
 							}
 							$this->data['by_cat'][$dt][$tmp_dept_id]['cat_list'][$tmp_cat_id][$tmp_discount_type]['nett_amt'] = round($this->data['by_cat'][$dt][$tmp_dept_id]['cat_list'][$tmp_cat_id][$tmp_discount_type]['amt'] - $this->data['by_cat'][$dt][$tmp_dept_id]['cat_list'][$tmp_cat_id][$tmp_discount_type]['total_cost'],2);
 						}
 					}
 					$this->data['by_cat'][$dt][$tmp_dept_id]['nett_amt'] = round($this->data['by_cat'][$dt][$tmp_dept_id]['amt'] - $this->data['by_cat'][$dt][$tmp_dept_id]['total_cost'],2);
 				}
 			}
 		}
 		//print_r($this->data);
 		
 		$report_title = array();
 		$report_title[] = "Branch: ".$this->branch_list[$bid]['code'];
 		$report_title[] = "Brand: ".get_brand_title($brand_id);
 		$report_title[] = ($vendor_id) ? "Vendor: ".$this->vendor_list[$vendor_id]['description'] : 'Vendor: All';
 		$report_title[] = ($dept_id) ? "Department: ".$this->dept_list[$dept_id]['description'] : 'Department: All';
 		$report_title[] = ($sku_type) ? "SKU Type: ".$this->sku_type_list[$sku_type]['description'] : 'SKU Type: All';
 		if ($sku_type == 'CONSIGN') $report_title[] = "Trade Discount Type: ".join(', ', $trade_discount_type_list);
 		$report_title[] = "Locations: ".join(', ', $location);
 		$report_title[] = "Date From $date_from to $date_to";
 		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
 		if($form['submit_type'] == 'print'){
 			$smarty->assign('print_branch_info', $this->branch_list[$bid]);
 			
 			$print_info = array();
 			$print_info['brand'] = get_brand_title($brand_id);
 			$print_info['vendor'] = ($vendor_id) ? $this->vendor_list[$vendor_id]['description'] : 'All';
 			$print_info['dept'] = ($dept_id) ? $this->dept_list[$dept_id]['description'] : 'All';
 			$print_info['dept'] = ($dept_id) ? $this->dept_list[$dept_id]['description'] : 'All';
 			$print_info['sku_type'] = ($sku_type) ? $this->sku_type_list[$sku_type]['description'] : 'All';
 			$print_info['trade_discount'] = join(', ', $trade_discount_type_list);
 			$print_info['date'] = "Date From $date_from to $date_to";
 			
 			$smarty->assign('print_info', $print_info);
 			
 		}
 		
 		$smarty->assign('data', $this->data);
	 }
	 
	 private function find_same_item_price_key($arr, $r){
	 	$key = -1;
	 	if(!$arr)	return $key;
	 	
	 	foreach($arr as $tmp_key => $tmp_r){
	 		if($tmp_r['sid'] == $r['sku_item_id'] && $tmp_r['price'] == $r['price']){
	 			$key = $tmp_key;
	 			break;
	 		}
	 	}
	 	//print "return $key<br>";
	 	return $key;
	 }
}

$CONSIGNMENT_CATEGORY_SALES_REPORT = new CONSIGNMENT_CATEGORY_SALES_REPORT('Category Sales Report by SKU');

?>
