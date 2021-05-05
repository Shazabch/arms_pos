<?php
/*
11/13/2018 1:45 PM Andy
- Enhanced to use discount string in more_info as discount percentage.

11/20/2018 2:48 PM Andy
- Change this report to show all sku_type of sku.
- Enhanced to have Trade Discount Percent, Gross Amt and Receipt Discount
- Enhanced to separate sales by "Consignment SKU - Brand Table", "Consignment SKU - Not Using Brand Table" and "Not Consignment SKU".

12/13/2018 9:29 AM Justin
- Enhanced the report to have option to view data by Brand or Vendor.

1/11/2019 6:16 PM Justin
- Enhanced to have department filter.

2/21/2020 1:39 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");

class BRAND_VENDOR_SALES_BY_PRICE_TYPE_AND_DISCOUNT extends Module{
	var $branches = array();
	var $branches_group = array();
	
	var $brands = array();
	var $brand_groups = array();
	var $vendors = array();
	
	var $section_type_list = array(
		'consign_brand' => 'Consignment SKU - Brand Table',
		'consign_vendor' => 'Consignment SKU - Vendor Table',
		'not_consign' => 'Not Consignment SKU',
	);
	
	var $data_type_list = array("brand"=>"Brand", "vendor"=>"Vendor");
	
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		$this->init_selection();

		$smarty->assign('section_type_list', $this->section_type_list);
		$smarty->assign('data_type_list', $this->data_type_list);
		parent::__construct($title);
	}
	
	private function init_selection(){
		global $con, $smarty, $config, $sessioninfo, $con_multi;
		
		// Date From / To
		if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
		if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));

		// Branches
		$q1 = $con_multi->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc($q1)){
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);
		$smarty->assign('branches',$this->branches);
		
		// load branch group items
		$q1 = $con_multi->sql_query("select bgi.*,branch.code,branch.description
		from branch_group_items bgi
		left join branch on bgi.branch_id=branch.id
		where branch.active=1
		order by branch.sequence, branch.code");
		while($r = $con_multi->sql_fetchassoc($q1)){
			$this->branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
			$this->branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con_multi->sql_freeresult($q1);
		
		// load branch group header
		$con_multi->sql_query("select * from branch_group",false,false);
		while($r = $con_multi->sql_fetchassoc()){
			if(!$this->branches_group['items'][$r['id']]) continue;
            $this->branches_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		
		$smarty->assign('branches_group',$this->branches_group);
		
		// Brand
		$brand_filter = array();
		$brand_filter[] = "brand.active=1";
		if($sessioninfo['brand_ids']){
			$brand_filter[] = "brand.id in (".$sessioninfo['brand_ids'].")";
		}
		$str_brand_filter = "where ".join(' and ', $brand_filter);
		$q1 = $con_multi->sql_query("select * from brand $str_brand_filter order by code, description");
		while($r = $con_multi->sql_fetchassoc($q1)){
			$this->brands[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);
		
		// Brand Group		
		$q1 = $con_multi->sql_query("select br.* , bri.brand_id
			from brgroup br
			join brand_brgroup bri on bri.brgroup_id=br.id
			join brand on brand.id=bri.brand_id
			$str_brand_filter and br.active=1
			order by br.code, br.description, brand.code, brand.description");
		while($r = $con_multi->sql_fetchassoc($q1)){
			if(!isset($this->brand_groups[$r['id']]['header'])){
				$this->brand_groups[$r['id']]['header'] = $r;
				unset($this->brand_groups[$r['id']]['header']['brand_id']);
			}
			
			$this->brand_groups[$r['id']]['items'][$r['brand_id']] = $r['brand_id'];
			
			$this->brands[$r['brand_id']]['brgroup_id'] = $r['id'];
		}
		$con_multi->sql_freeresult($q1);
		
		// Vendor
		$filters = array();
		$filters[] = "v.active=1";
		if($sessioninfo['vendor_ids']){
			$filters[] = "v.id in (".$sessioninfo['vendor_ids'].")";
		}
		$filter = "where ".join(' and ', $filters);
		$q1 = $con_multi->sql_query("select * from vendor v $filter order by v.code, v.description");
		while($r = $con_multi->sql_fetchassoc($q1)){
			$this->vendors[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);
		
		// Department
		$con_multi->sql_query("select id, code, description 
						 from category 
						 where level=2 and id in (".join(",",array_keys($sessioninfo['departments'])).") 
						 order by description");
		while($r = $con_multi->sql_fetchassoc()){
			$this->departments[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		
		$smarty->assign('brands',$this->brands);
		$smarty->assign('brand_groups',$this->brand_groups);
		$smarty->assign('vendors',$this->vendors);
		$smarty->assign('departments', $this->departments);
		
		//print_r($this->brand_groups);
	}
	
	function _default(){
	    global $sessioninfo, $smarty;
	    		
		if($_REQUEST['load_report']){
			$this->generate_report();
			
			if($_REQUEST['export_excel']){
				include_once("include/excelwriter.php");
				log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." To Excel");

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		}
		
		$this->display();
	}
	
	private function generate_report(){
		global $con, $smarty, $config, $sessioninfo, $con_multi;
		
		$err = array();
		$report_title = array();
		
		// Branch ID
		if(BRANCH_CODE == 'HQ'){
			$bid = mi($_REQUEST['branch_id']);
		}else{
			$bid = mi($sessioninfo['branch_id']);
		}
		if(!$bid)	$err[] = "Please select branch";
		else	$report_title[] = "Branch: ".$this->branches[$bid]['code'];
		
		// Brand
		$brand_id_list = array();
		$tmp_brand_id = mi($_REQUEST['brand_id']);
		if($tmp_brand_id>0){
			// Single Brand
			$brand_id_list[$tmp_brand_id] = $tmp_brand_id;
			
			$report_title[] = "Brand: ".$this->brands[$tmp_brand_id]['description'];
		}elseif($tmp_brand_id<0){
			$brgroup_id = abs($tmp_brand_id);
			// Brand Group
			if(isset($this->brand_groups[$brgroup_id])){
				$report_title[] = "Brand Group: ".$this->brand_groups[$brgroup_id]['header']['description'];
				$brand_id_list = $this->brand_groups[$brgroup_id]['items'];
			}
		}
		
		$vendor_id = $_REQUEST['vendor_id'];
		if($data_type == "brand" && !$brand_id_list) $err[] = "Please select Brand";
		elseif($data_type == "vendor" && !$vendor_id) $err[] = "Please select Vendor";
		
		// Date From / To
		$date_from = date("Y-m-d", strtotime($_REQUEST['date_from']));
		$date_to = date("Y-m-d", strtotime($_REQUEST['date_to']));
		$this->data_type = $data_type = trim($_REQUEST['data_type']);
		$dept_id = $_REQUEST['department_id'];
		
		if($date_to < $date_from)	$err[] = "Date To cannot ealier than Date From";
		else	$report_title[] = "Date: $date_from to $date_to";
		
		if(!$data_type) $err[] = "Please select show by Brand or Vendor";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$data = array();
		$filter = array();
		$filter[] = "p.branch_id=$bid";
		if($data_type == "brand"){ // show by brand
			$filter[] = "sku.brand_id>0";
			$filter[] = "sku.brand_id in (".join(',', $brand_id_list).")";
			
			if($vendor_id){ // filter vendor if got assign
				$filter[] = "sku.vendor_id = ".mi($vendor_id);				
			}elseif($sessioninfo['vendor_ids']){ // means user not filtering vendor but have restrctions
				$filter[] = "sku.vendor_id in (".$sessioninfo['vendor_ids'].")";
			}
		}else{ // show by vendor
			$filter[] = "sku.vendor_id>0";
			$filter[] = "sku.vendor_id = ".mi($vendor_id);
			
			if($brand_id_list){ // filter brand if got assign
				$filter[] = "sku.brand_id in (".join(',', $brand_id_list).")";
			}elseif($sessioninfo['brand_ids']){ // means user not filtering brand but have restrctions
				$filter[] = "sku.brand_id in (".$sessioninfo['brand_ids'].")";
			}
			
			$report_title[] = "Vendor: ".$this->vendors[$vendor_id]['description'];
		}
		//$filter[] = "sku.sku_type='CONSIGN'";
		////////////////
		$filter[] = "p.date between ".ms($date_from)." and ".ms($date_to);
		$filter[] = "p.cancel_status=0";
		if($dept_id > 0){
			$filter[] = "c.department_id = ".mi($dept_id);
		}else{
			$filter[] = "c.department_id in (".join(",",array_keys($sessioninfo['departments'])).")";
		}
		
		$str_filter = join(' and ', $filter);
		
		$sql = "select (pi.price-pi.tax_amount) as gross_amt, (pi.price-pi.discount-pi.discount2-pi.tax_amount) as amt, pi.qty, si.sku_item_code, si.artno, si.mcode, si.description, si.link_code, 
			if(pi.trade_discount_code is null or pi.trade_discount_code='', sku.default_trade_discount_code, pi.trade_discount_code) as price_type,pi.sku_item_id, sku.brand_id, sku.sku_type, c.department_id, sku.trade_discount_type, sku.vendor_id, 
			pi.price, pi.discount, pi.discount2, pi.more_info
			from pos p
			join pos_items pi on pi.branch_id=p.branch_id and pi.pos_id=p.id and pi.date=p.date and pi.counter_id=p.counter_id
			join sku_items si on si.id = pi.sku_item_id
			join sku on si.sku_id = sku.id
			join category c on sku.category_id = c.id
			join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date and pf.finalized=1
			where $str_filter
			order by price_type, si.sku_item_code";
		
		//print $sql;
		$q1 = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q1)){
			$brand_id = mi($r['brand_id']);
			$vendor_id = mi($r['vendor_id']);
			$department_id = mi($r['department_id']);
			$price_type = trim($r['price_type']);
			if($data_type == "brand") $data_key = $brand_id;
			else $data_key = $vendor_id;
			
			$section_type = 'not_consign';
			$price_type_rate = '';
			
			$discount_percent = 0;
			if($r['discount']>0){
				// Get Percentage from more_info
				$r['more_info'] = unserialize($r['more_info']);
				
				if($r['more_info']['discount_str']){
					$discount_percent = round(str_replace('%', '', $r['more_info']['discount_str']), 2);
				}else{
					$discount_percent = round($r['discount'] / $r['price'] * 100, 2);
				}				
			}
			
			// Consignment SKU
			if($r['sku_type']=='CONSIGN'){
				$section_type = 'consign_vendor';
				// Get Trade Discount Percent
				$trade_discount_type = mi($r['trade_discount_type']);
				if($trade_discount_type==1){    // use brand
					$section_type = 'consign_brand';
					$q_rate = $con_multi->sql_query("select rate from brand_commission where branch_id=$bid and brand_id=$brand_id and department_id=$department_id and skutype_code=".ms($price_type));
				}elseif($trade_discount_type==2){   // use vendor
					$q_rate = $con_multi->sql_query("select rate from vendor_commission where branch_id=$bid and vendor_id=$vendor_id and department_id=$department_id and skutype_code=".ms($price_type));
				}
				$tmp = $con_multi->sql_fetchassoc($q_rate);
				$con_multi->sql_freeresult($q_rate);
				$price_type_rate = mf($tmp['rate']);
			}
			
			// By Price Type
			$data['data_list'][$data_key][$section_type]['price_type_list'][$r['price_type']]['rate'] = $price_type_rate;
			$data['data_list'][$data_key][$section_type]['price_type_list'][$r['price_type']]['items_list'][$r['sku_item_id']]['qty'] += $r['qty'];
			$data['data_list'][$data_key][$section_type]['price_type_list'][$r['price_type']]['items_list'][$r['sku_item_id']]['amt'] += $r['amt'];
			$data['data_list'][$data_key][$section_type]['price_type_list'][$r['price_type']]['items_list'][$r['sku_item_id']]['gross_amt'] += $r['gross_amt'];
			$data['data_list'][$data_key][$section_type]['price_type_list'][$r['price_type']]['items_list'][$r['sku_item_id']]['discount2'] += $r['discount2'];
			
			// Total By Price Type
			$data['data_list'][$data_key][$section_type]['price_type_list'][$r['price_type']]['total']['qty'] += $r['qty'];
			$data['data_list'][$data_key][$section_type]['price_type_list'][$r['price_type']]['total']['amt'] += $r['amt'];
			$data['data_list'][$data_key][$section_type]['price_type_list'][$r['price_type']]['total']['gross_amt'] += $r['gross_amt'];
			$data['data_list'][$data_key][$section_type]['price_type_list'][$r['price_type']]['total']['discount2'] += $r['discount2'];
			
			// Total By Brand / Vendor
			$data['data_list'][$data_key][$section_type]['total']['qty'] += $r['qty'];
			$data['data_list'][$data_key][$section_type]['total']['amt'] += $r['amt'];
			$data['data_list'][$data_key][$section_type]['total']['gross_amt'] += $r['gross_amt'];
			$data['data_list'][$data_key][$section_type]['total']['discount2'] += $r['discount2'];
			
			$data['data_list'][$data_key]['total']['qty'] += $r['qty'];
			$data['data_list'][$data_key]['total']['amt'] += $r['amt'];
			$data['data_list'][$data_key]['total']['gross_amt'] += $r['gross_amt'];
			$data['data_list'][$data_key]['total']['discount2'] += $r['discount2'];
			
			// By Discount Percent
			if($discount_percent>0){
				$discount_percent = strval($discount_percent);
				
				// By Price Type
				$data['data_list'][$data_key][$section_type]['price_type_list'][$r['price_type']]['items_list'][$r['sku_item_id']]['discount_list'][$discount_percent] += $r['discount'];
				$data['data_list'][$data_key][$section_type]['price_type_list'][$r['price_type']]['items_list'][$r['sku_item_id']]['total']['disc_amt'] += $r['discount'];
				
				// Total By Price Type
				$data['data_list'][$data_key][$section_type]['price_type_list'][$r['price_type']]['total']['discount_list'][$discount_percent] += $r['discount'];
				$data['data_list'][$data_key][$section_type]['price_type_list'][$r['price_type']]['total']['disc_amt'] += $r['discount'];
				
				// Total By Brand / Vendor
				$data['data_list'][$data_key]['total']['discount_list'][$discount_percent] += $r['discount'];
				$data['data_list'][$data_key]['total']['disc_amt'] += $r['discount'];
			}
			
			if(!isset($data['si_info'][$r['sku_item_id']])){
				$data['si_info'][$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];
				$data['si_info'][$r['sku_item_id']]['artno'] = $r['artno'];
				$data['si_info'][$r['sku_item_id']]['mcode'] = $r['mcode'];
				$data['si_info'][$r['sku_item_id']]['link_code'] = $r['link_code'];
				$data['si_info'][$r['sku_item_id']]['description'] = $r['description'];
			}
		}
		$con_multi->sql_freeresult($q1);
		
		if($data){
			// Sort Discount Percent
			foreach($data['data_list'] as $data_key => $d){
				if($d['total']['discount_list']){
					ksort($data['data_list'][$data_key]['total']['discount_list']);
				}
			}
			
			// Sort Brand / Vendor
			uksort($data['data_list'], array($this, "sort_data"));
		}
		
		//print_r($data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		$smarty->assign('data', $data);
	}
	
	private function sort_data($data_id1, $data_id2){
		if($data_id1 == $data_id2)	return 0;
		
		if($this->data_type == "brand"){
			$d1 = $this->brands[$data_id1];
			$d2 = $this->brands[$data_id2];
		}else{
			$d1 = $this->vendors[$data_id1];
			$d2 = $this->vendors[$data_id2];
		}
		
		if(!$d1['code']){
			if(!$d2['code']){
				return $d1['description'] > $d2['description'] ? 1 : 0;
			}else{
				return -1;
			}
		}
		
		if(!$d2['code']){
			return -1;
		}
		
		return $d1['description'] > $d2['description'] ? 1 : 0;
	}
}

$BRAND_VENDOR_SALES_BY_PRICE_TYPE_AND_DISCOUNT = new BRAND_VENDOR_SALES_BY_PRICE_TYPE_AND_DISCOUNT('Brand / Vendor Sales by Price Type and Discount Report');
?>