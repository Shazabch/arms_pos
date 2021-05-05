<?php
/*
12/23/2013 2:00 PM Andy
- Add can sort by category.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

8/26/2014 2:16 PM Fithri
- vendors, brands & categories is filter based on user permission

12/14/2015 3:46 PM DingRen
add group by parent & child

6/6/2017 11:40 AM Justin
- Enhanced to have sku filter.

1/25/2019 3:59 PM Andy
- Fixed $sku_code_list index issue.

2/24/2020 9:05 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

6/9/2020 2:52 PM Andy
- Increase memory_limit to 2G.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");
ini_set('memory_limit', '2048M');

class SKU_SALES_REPORT extends Module{

	function __construct($title){
		global $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		$this->init_selection();
		parent::__construct($title);
	}
	
	function init_selection(){
		global $con, $smarty, $sessioninfo, $con_multi;
		
		$branch_arr = array();
		$brand_arr = array();
		$vendor_arr = array();
		
		$r7 = $con_multi->sql_query("select id, code from branch where active = 1");
		while ($d7 = $con_multi->sql_fetchassoc($r7)) {
			$branch_arr[$d7['id']] = $d7;
		}
		$con_multi->sql_freeresult($r7);
		
		$vd_filter = ($sessioninfo['vendors']) ? "id in (".join(',',array_keys($sessioninfo['vendors'])).")" : "1";
		$r9 = $con_multi->sql_query("select id, code, description from vendor where $vd_filter and active = 1 order by description");
		while ($d9 = $con_multi->sql_fetchassoc($r9)) {
			$vendor_arr[$d9['id']] = $d9;
		}
		$con_multi->sql_freeresult($r9);
		
		$br_filter = ($sessioninfo['brands']) ? "id in (".join(',',array_keys($sessioninfo['brands'])).")" : "1";
		$r10 = $con_multi->sql_query("select id, description from brand where $br_filter and active = 1 order by description");
		while ($d10 = $con_multi->sql_fetchassoc($r10)) {
			$brand_arr[$d10['id']] = $d10;
		}
		$con_multi->sql_freeresult($r10);
		
		$this->branch_arr = $branch_arr;
		$smarty->assign('branch_arr',$branch_arr);
		$smarty->assign('brand_arr',$brand_arr);
		$this->brand_arr = $brand_arr;
		$smarty->assign('brand_groups',get_brand_group());
		$smarty->assign('vendor_arr',$vendor_arr);
		$this->vendor_arr = $vendor_arr;
		
		/*
		print join(',',array_keys($sessioninfo['vendors']));
		print '<pre>';
		print_r($sessioninfo);
		print '</pre>';
		*/
	}
	
	function _default(){
		global $smarty;
		
		$form = $_REQUEST;
		if (!$form['date_from']) $form['date_from'] = date('Y-m-d');
		if (!$form['date_to']) $form['date_to'] = date('Y-m-d');
		$this->form = $form;

		if ($form['show_report']) {
			//$this->dd($this->form);
			$this->generate_report();
			if ($_REQUEST['output_excel']) {
				include_once("include/excelwriter.php");
				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=sku_sales_report_'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		}
		$smarty->assign('form',$this->form);
		$this->display();
	}
	
	function generate_report(){
		
		global $con, $smarty, $sessioninfo, $con_multi;
		
		$err = array();
		if (strtotime($this->form['date_from']) > strtotime($this->form['date_to'])){
			$err[] = "Date From cannot be later than Date To";
			$smarty->assign("err", $err);
			return;
		}
		$report_title = array();
		$report_title[] = "Date : From ".$this->form['date_from']." To ".$this->form['date_to'];
		
		//create all day array in between From and To
		$day_arr = array();
		$timestamp_from = strtotime($this->form['date_from']);
		$timestamp_to = strtotime($this->form['date_to']);
		for ($i = $timestamp_from; $i <= $timestamp_to; $i+=(60*60*24)) $day_arr[] = explode('-',date('Y-m-d',$i));
		//$this->dd($day_arr);
		$smarty->assign('day_arr',$day_arr);
		$smarty->assign('day_count',count($day_arr));
		
		$table_to_query = array();
		
		$data = array();
		$sku_item_list = $sku_item_arr = array();
		$qty_total = $amt_total = 0;
		$day_amt_total = $day_qty_total = array();
		$filter = array();
		$join_sku_table = false;
		
		if ($this->form['branch_id']) {
			$table_to_query[] = 'sku_items_sales_cache_b'.mi($this->form['branch_id']);
		}
		else {
			foreach ($this->branch_arr as $bid => $b) $table_to_query[] = 'sku_items_sales_cache_b'.mi($bid);
		}
		
		$filter[] = "sisc.date >= ".ms($this->form['date_from'])." and sisc.date <= ".ms($this->form['date_to']);
		
		if ($this->form['vendor_id']) {
			$join_sku_table = true;
			$filter[] = 'sku.vendor_id = '.$this->form['vendor_id'];
		}
		elseif ($sessioninfo['vendors']) {
			$join_sku_table = true;
			$filter[] = 'sku.vendor_id in ('.join(',',array_keys($sessioninfo['vendors'])).')';
		}
		
		if ($this->form['brand_id'] != '') {
			$join_sku_table = true;
			$filter[] = 'sku.brand_id in ('.join(',',process_brand_id($this->form['brand_id'])).')';
			if ($sessioninfo['brands']) $filter[] = 'sku.brand_id in ('.join(',',array_keys($sessioninfo['brands'])).')';
		}
		elseif ($sessioninfo['brands']) {
			$join_sku_table = true;
			$filter[] = 'sku.brand_id in ('.join(',',array_keys($sessioninfo['brands'])).')';
		}
		
		$join_sku_table = true;
		$join_cc_table = true;
		if ($this->form['category_id']) {
			$r8 = $con_multi->sql_query("select level, description from category where id = ".mi($this->form['category_id']));
			$d8 = $con_multi->sql_fetchrow($r8);
			$con_multi->sql_freeresult($r8);
			$level = mi($d8['level']);
			$category = $d8['description'];
			$filter[] = "cc.p$level = ".mi($this->form['category_id']);
			$report_title[] = "Category: ".$category;
		}
		$filter[] = "cc.p2 in (".$sessioninfo['department_ids'].")";
		
		// sku items
		if(isset($this->form['sku_code_list'])){
			$sku_code_list = join(",", array_map("ms", $this->form['sku_code_list']));
			
			// select sku item id list
			$sid_list = array();
			$con_multi->sql_query("select * from sku_items where sku_item_code in ($sku_code_list)");
			while($r = $con_multi->sql_fetchassoc()){
				$sid_list[] = mi($r['id']);
				$group_item[] = $r;
			}
			$con_multi->sql_freeresult();
			
			$filter[] = "sisc.sku_item_id in (".join(",", $sid_list).")";
			
			$report_title[] = "(Selected SKU Only)";
			$smarty->assign('group_item', $group_item);
		}
		
		if(!$this->form['all_category']){
			if(!$this->form['category_id'] && !$sid_list){
				$err[] = "Please select category or sku";
				$smarty->assign("err", $err);
				return;
			}
		}else{
			$report_title[] = "Category: All";
		}
		
		if ($this->form['exclude_inactive_sku']) {
			$join_sku_table = true;
			$filter[] = 'si.active=1';
		}
		
		$tbl_join = $join_sku_table ? ' left join sku_items si on sisc.sku_item_id = si.id left join sku on si.sku_id = sku.id ' : '';
		$tbl_join .= $join_cc_table ? ' left join category c on sku.category_id = c.id left join category_cache cc on c.id = cc.category_id ' : '';
		$tbl_join.=' left join uom on si.packing_uom_id = uom.id';

		foreach ($table_to_query as $table) {
			if ($con_multi->sql_query_false("explain $table")) {

				$r2 = $con_multi->sql_query("select sku_id,sku_item_id, amount as amt, qty, date,uom.fraction from $table sisc $tbl_join where ".join(' and ',$filter));
				while ($d2 = $con_multi->sql_fetchassoc($r2)) {

					if($_REQUEST['group_by_parent_child']){
						$idx=mi($d2['sku_id']);

						$d2['qty']=$d2['qty']*$d2['fraction'];
					}
					else $idx = mi($d2['sku_item_id']);

					$sku_item_list[$idx] = $idx;
					$data[$idx]['qty'] += $d2['qty'];
					$qty_total += $d2['qty'];
					$data[$idx]['amt'] += $d2['amt'];
					$data[$idx][$d2['date']]['amt'] += $d2['amt'];
					$data[$idx][$d2['date']]['qty'] += $d2['qty'];
					$day_amt_total[$d2['date']] += $d2['amt'];
					$day_qty_total[$d2['date']] += $d2['qty'];
					$amt_total += $d2['amt'];
				}
				$con_multi->sql_freeresult($r2);
			}
		}
		
		switch($this->form['sort_by']){
			case 'category':
				$sort_by = "c.description";
				break;
			default:
				$sort_by = "si.".$this->form['sort_by'];
				break;
				
		}
		
		if ($sku_item_list) {
			if($_REQUEST['group_by_parent_child']){
				$r6 = $con_multi->sql_query("
										select
										si.sku_id, si.sku_item_code, si.mcode, si.link_code, si.description, si.artno, sku.vendor_id, sku.brand_id, c.description as category
										from sku_items si
										left join sku on si.sku_id = sku.id
										left join category c on sku.category_id = c.id
										where si.sku_id in (".join(',',$sku_item_list).")
										and is_parent=1
										order by $sort_by");
				while ($d6 = $con_multi->sql_fetchassoc($r6)) {
					$sku_item_arr[$d6['sku_id']] = $d6;
				}
				$con_multi->sql_freeresult($r6);
			}
			else{
				$r6 = $con_multi->sql_query("
										select
										si.id, si.sku_item_code, si.mcode, si.link_code, si.description, si.artno, sku.vendor_id, sku.brand_id, c.description as category
										from sku_items si
										left join sku on si.sku_id = sku.id
										left join category c on sku.category_id = c.id
										where si.id in (".join(',',$sku_item_list).")
										order by $sort_by");
				while ($d6 = $con_multi->sql_fetchassoc($r6)) {
					$sku_item_arr[$d6['id']] = $d6;
				}
				$con_multi->sql_freeresult($r6);
			}
		}
		
		$report_title[] = "Branch: ".(($this->form['branch_id']) ? $this->branch_arr[$this->form['branch_id']]['code'] : 'All');
		$report_title[] = "Vendor: ".(($this->form['vendor_id']) ? $this->vendor_arr[$this->form['vendor_id']]['description'] : 'All');
		//$brand = ($this->form['brand_id']) ? $this->brand_arr[$this->form['brand_id']]['description'] : 'All';
		
		if($this->form['brand_id']) $brand_name = get_brand_title($this->form['brand_id']);
		else $brand_name = "All";
		$report_title[] =  "Brand: ".$brand_name;
		
		$category = $category ? $category : 'All';
		$smarty->assign('report_title', join("&nbsp;&nbsp;&nbsp;", $report_title));
		
		$smarty->assign('data',$data);
		$smarty->assign('amt_total',$amt_total);
		$smarty->assign('qty_total',$qty_total);
		$smarty->assign('day_qty_total',$day_qty_total);
		$smarty->assign('day_amt_total',$day_amt_total);
		$smarty->assign('sku_item_arr',$sku_item_arr);
	}
	
	function dd($data) {
		if (is_array($data)) {
			print '<pre>';
			print_r($data);
			print '</pre>';
		}
		else var_dump($data);
	}
}

$SKU_SALES_REPORT = new SKU_SALES_REPORT('SKU Sales Report');

?>
