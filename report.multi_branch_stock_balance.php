<?php
/*
2/8/2017 1:36 PM Andy
- Fixed mysql maximum can only support 61 join.
- Change 500 to 400 item per page, in order to avoid excel limitation.
- Added "As per date" to show report generation time.

3/27/2017 10:25 AM Andy
- Fixed to use temporary table.

7/27/2017 1:49 PM Andy
- Fixed to filter no_inventory sku.

11/7/2018 2:21 PM Andy
- Enhanced to have "Grand Total".
- Fixed total cost wrong if group by sku.

1/25/2019 3:59 PM Andy
- Fixed $sku_code_list index issue.

2/21/2020 3:18 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

12/22/2020 10:50 AM William
- Enhanced to pass branch_line_count to tpl file.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");
ini_set('memory_limit', '1024M');
set_time_limit(0);

class MULTI_BRANCH_STOCK_BALANCE extends Module{
	var $file_folder = "tmp/Multi_Branch_Stock_Balance";
	var $branches = array();
	var $branch_group = array();
	var $vendors = array();
	var $sku_types = array();
	var $brands = array();
	var $brand_group = array();
	var $export_excel = false;
	var $stock_filter_type_list = array('got_stock'=>'Got Stock Balance Only (Including Positive & Negative)', 'all'=>'All SKU Including Zero Stock', 'negative_only'=>'Negative Stock Only');
	var $order_by_list = array('arms_code'=>'ARMS Code', 'mcode'=>'MCode', 'artno'=>'Art No','link_code'=>'Old Code', 'desc'=>'Description');
	var $page_limit = 400;
	var $branch_per_line = 10;
	
	// report filter
	var $err = array();
	var $report_title = array();
	var $sid_list = array();
	var $branch_id_list = array();
	var $brand_id_list = array();
	var $sku_type = array();
	var $filter_active = 0;
	var $vendor_id = 0;
	var $cat_id = 0;
	var $cat_info = array();
	var $all_cat = false;
	var $stock_filter_type = "";
	var $group_by_sku = 0;
	var $show_grand_total = 0;
	var $tmp_si_tbl = "tmp_multi_branch_sku_items_list";
	var $sort_by = "arms_code";
	var $order_by = "";
	var $data = array();
	
	function __construct($title)
	{
		global $con, $smarty, $sessioninfo, $config, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		if(!is_dir($this->file_folder))	check_and_create_dir($this->file_folder);
		
		$files = scandir($this->file_folder);
		for($i=2; $i<count($files); $i++) {
			if(strtotime("-1 week") > filemtime($this->file_folder."/".$files[$i])) {
				unlink($this->file_folder."/".$files[$i]);
			}
		}
		
		$this->order_by_list['link_code'] = $config['link_code_name'];
		
		//branches
		$con_multi->sql_query("select id,code,description from branch where active=1 order by sequence,code");
		while ($r = $con_multi->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		} 
		$con_multi->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		//vendors
		$con_multi->sql_query("select id,description from vendor where active=1 order by description");
		while ($r = $con_multi->sql_fetchassoc()){
			$this->vendors[$r['id']] =$r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('vendors',$this->vendors);
		
		$con_multi->sql_query("select * from sku_type where active=1");
		// sku type
		while ($r = $con_multi->sql_fetchassoc()){
			$this->sku_types[$r['code']] =$r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign("sku_types", $this->sku_types);
		
		// brand
		$con_multi->sql_query("select id, description from brand where active=1 order by description");
		while ($r = $con_multi->sql_fetchassoc()){
			$this->brands[$r['id']] =$r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign("brands", $this->brands);
		
		$this->load_branch_group();
		$this->load_brand_group();
		
		$smarty->assign('stock_filter_type_list', $this->stock_filter_type_list);
		$smarty->assign('order_by_list', $this->order_by_list);
		$smarty->assign('branch_per_line', $this->branch_per_line);
		$smarty->assign('page_limit', $this->page_limit);
		
		parent::__construct($title);
	}
	
	private function load_branch_group(){
		global $con,$smarty,$con_multi;
		
	    if($this->branch_group)  return $this->branch_group;
		$this->branch_group = array();
		
		// load header
		$con_multi->sql_query("select * from branch_group");
		while($r = $con_multi->sql_fetchrow()){
            $this->branch_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();		

		// load items
		$con_multi->sql_query("select bgi.*,branch.code,branch.description 
			from branch_group_items bgi 
			left join branch on bgi.branch_id=branch.id 
			where branch.active=1 order by branch.sequence, branch.code");
		while($r = $con_multi->sql_fetchassoc()){
	        $this->branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $this->branch_group['have_group'][$r['branch_id']] = $r;
		}
		$con_multi->sql_freeresult();

		//print_r($this->branch_group);
		$smarty->assign('branch_group',$this->branch_group);
	}
	
	private function load_brand_group(){
		global $con, $smarty, $con_multi;
		
		if($this->brand_group)	return $this->brand_group;
		$this->brand_group = array();
		
		// load header
		$con_multi->sql_query("select * from brgroup where active=1 order by code,description");
		while($r = $con_multi->sql_fetchrow()){
            $this->brand_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();	
		
		// load items
		$con_multi->sql_query("select bgi.*,brand.code,brand.description 
			from brand_brgroup bgi 
			left join brand on bgi.brand_id=brand.id 
			where brand.active=1 order by brand.description");
		while($r = $con_multi->sql_fetchassoc()){
	        $this->brand_group['items'][$r['brgroup_id']][$r['brand_id']] = $r;
	        $this->brand_group['have_group'][$r['brand_id']] = $r;
		}
		$con_multi->sql_freeresult();

		//print_r($this->brand_group);
		$smarty->assign('brand_group',$this->brand_group);
	}

	function _default()
	{
		if($_REQUEST['show_report']){
			$this->prepare_report_data();
			
			if(!$this->err){
				if($this->export_excel){
					$this->generate_excel();
				}else{					
					$this->load_report();	
				}
			}
			
		}
		$this->display();
	}
	
	private function prepare_report_data(){
		global $con, $smarty, $config, $sessioninfo, $con_multi;
		
		//print_r($_REQUEST);print "<br>";
		$this->err = array();
		$this->report_title = array();
		
		
		if($_REQUEST['export_excel']){
			$this->export_excel = true;
		}
		
		$this->page = $this->export_excel ? 0 : mi($_REQUEST['page']);
		$this->sort_by = trim($_REQUEST['sort_by']);
		$this->show_grand_total = mi($_REQUEST['show_grand_total']);
		$branch_per_line = mi($_REQUEST['branch_per_line']);
		if($branch_per_line > 0)	$this->branch_per_line = $branch_per_line;
		
		// branch
		if(BRANCH_CODE == 'HQ'){			
			if($_REQUEST['branch_id_list']){
				foreach($_REQUEST['branch_id_list'] as $bid){
					if(!isset($this->branches[$bid])){
						$this->err[] = "Branch ID#$bid is invalid";
					}else{
						$this->branch_id_list[$bid] = $bid;
					}
				}
			}else{
				$this->err[] = "Please select at least one branch.";
			}
		}else{
			$this->branch_id_list = array($sessioninfo['branch_id'] => $sessioninfo['branch_id']);
		}
		
		// brand
		$brand_id = trim($_REQUEST['brand_id']);
		$this->brand_id_list = array();
		if($brand_id != 'all'){
			if($brand_id > 0){	// single brand
				$this->brand_id_list = array($brand_id => $brand_id);
				$this->report_title[] = "Brand: ".$this->brands[$brand_id]['description'];
			}elseif($brand_id < 0){	// brand group
				$brand_group_id = abs($brand_id);
				$this->report_title[] = "Brand Group: ".$this->brand_group['header'][$brand_group_id]['description'];
				foreach($this->brand_group['items'][$brand_group_id] as $tmp_brand_id => $r){
					$this->brand_id_list[$tmp_brand_id] = $tmp_brand_id;
				}
			}else{
				$this->report_title[] = "Brand: UN-BRANDED";
				$this->brand_id_list = array(0);
			}
		}else{
			$this->report_title[] = "Brand: All";
		}
		
		
		
		// sku type
		$this->sku_type = trim($_REQUEST['sku_type']);
		if($this->sku_type){
			$this->report_title[] = "SKU Type: ".$this->sku_types[$this->sku_type]['description'];
		}else{
			$this->report_title[] = "SKU Type: All";
		}
		
		// Active Status
		$this->filter_active = mi($_REQUEST['filter_active']);
		if($this->filter_active > 0){
			$this->report_title[] = "Status: Active";
		}elseif($this->filter_active < 0){
			$this->report_title[] = "Status: Inactive";
		}else{
			$this->report_title[] = "Status: All";
		}
		
		// vendor
		$this->vendor_id = mi($_REQUEST['vendor_id']);
		if($this->vendor_id){
			$this->report_title[] = "Vendor: ".$this->vendors[$this->vendor_id]['description'];
		}else{
			$this->report_title[] = "Vendor: All";
		}
		
		// category
		$this->cat_id = 0;
		if($_REQUEST['all_category']){	// all category
			$this->all_cat = true;
			$this->report_title[] = "Category: All";
		}else{	// selected cat or no filter cat
			$this->cat_id = mi($_REQUEST['category_id']);
			if($this->cat_id > 0){
				$this->cat_info = get_category_info($this->cat_id);
				$this->report_title[] = "Category: ".$this->cat_info['description'];
			}else{
				$this->report_title[] = "Category: Not Selected";
			}
			
		}
		
		// stock filter
		$this->stock_filter_type = trim($_REQUEST['stock_filter_type']);
		if(!isset($this->stock_filter_type_list[$this->stock_filter_type])){
			$this->err[] = "Invalid Stock Filter";
		}else{
			$this->report_title[] = "Stock Filter: ".$this->stock_filter_type_list[$this->stock_filter_type];
		}
		
		// group by sku
		$this->group_by_sku = mi($_REQUEST['group_by_sku']);
		if($this->group_by_sku){
			$this->report_title[] = "Group by SKU: Yes";
		}
		
		// sku items
		if(isset($_REQUEST['sku_code_list'])){
			$sku_code_list = join(",", array_map("ms", $_REQUEST['sku_code_list']));
			
			// select sku item id list
			$this->sid_list = array();
			$con_multi->sql_query("select * from sku_items where sku_item_code in ($sku_code_list)");
			while($r = $con_multi->sql_fetchassoc()){
				$this->sid_list[] = mi($r['id']);
				$group_item[] = $r;
			}
			$con_multi->sql_freeresult();
			$this->report_title[] = "(Selected SKU Only)";
		}
		$smarty->assign('group_item', $group_item);
		
		if(!$this->all_cat){
			if(!$this->cat_id && !$this->sid_list){
				$this->err[] = "Please select category or sku";
			}
		}
		
		if($this->err){
			$smarty->assign('err', $this->err);
		}else{
			// prepare sql info
			$this->data = array();
			$this->data['report_time'] = date("Y-m-d H:i:s");
			$str_join = "";
			$str_extra_col = "";
			$filter = array();
			$con_multi->sql_query("drop table if exists $this->tmp_si_tbl");
			$sql_cr8_tbl = "create temporary table if not exists $this->tmp_si_tbl(
				row_no int primary key auto_increment,
				id int unique)";
			$con_multi->sql_query($sql_cr8_tbl);
			
			// filter branch
			/*foreach($this->branch_id_list as $bid){
				$tbl_name = "sic".$bid;
				$col_name = "sb".$bid;
				$str_join .= " left join sku_items_cost $tbl_name on $tbl_name.branch_id=$bid and $tbl_name.sku_item_id=si.id";
				//$str_extra_col .= ", $tbl_name.qty as $col_name";
			}*/
			$str_join .= "left join sku_items_cost sic on sic.branch_id in (".join(',', $this->branch_id_list).") and sic.sku_item_id=si.id";
			
			// filter out no inventory sku
			$filter[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
			
			// filter brand
			if($this->brand_id_list){
				$filter[] = "sku.brand_id in (".join(',', $this->brand_id_list).")";
			}
			
			// filter sku type
			if($this->sku_type){
				$filter[] = "sku.sku_type=".ms($this->sku_type);
			}
			
			// filter active
			if($this->filter_active > 0){
				$filter[] = "si.active=1";
			}elseif($this->filter_active < 0){
				$filter[] = "si.active=0";
			}
			
			// filter vendor
			if($this->vendor_id){
				$filter[] = "sku.vendor_id=".$this->vendor_id;
			}
			
			// filter category
			if(!$this->all_cat && $this->cat_id && $this->cat_info){
				$filter[] = "cc.p".$this->cat_info['level']."=".$this->cat_id;
			}
			
			// filter sku
			if($this->sid_list){
				$filter[] = "si.id in (".join(',', $this->sid_list).")";
			}
			
			// filter stock type
			if($this->stock_filter_type == "got_stock" || $this->stock_filter_type == "negative_only"){
				$filter_or = array();
				/*foreach($this->branch_id_list as $bid){
					$col_name = "sb".$bid;
					$tbl_name = "sic".$bid;
					
					if($this->stock_filter_type == "got_stock"){
						$filter_or[] = "$tbl_name.qty<>0";
					}elseif($this->stock_filter_type == "negative_only"){
						$filter_or[] = "$tbl_name.qty<0";
					}
				}
				$filter[] = "(".join(' or ', $filter_or).")";*/
				if($this->stock_filter_type == "got_stock"){
					$filter_or[] = "sic.qty<>0";
				}elseif($this->stock_filter_type == "negative_only"){
					$filter_or[] = "sic.qty<0";
				}
				$filter[] = "(".join(' or ', $filter_or).")";
			}
			
			// filter user department privileges
			$filter[] = "cc.p2 in (".$sessioninfo['department_ids'].")";
			
			$filter = "where ".join(' and ', $filter);
			
			switch($this->sort_by){
				case 'mcode':
						$this->order_by = "si.mcode";
						break;
				case 'artno':
						$this->order_by = "si.artno";
						break;
				case 'link_code':
						$this->order_by = "si.link_code";
						break;
				case 'desc':
						$this->order_by = "si.description";
						break;
				case 'arms_code':
				default:
					$this->order_by = "si.sku_item_code";
					break;
			}
			
			if(!$this->export_excel && !$this->show_grand_total){
				$start = $this->page * $this->page_limit;
				$limit = "limit $start, ".$this->page_limit;
			}
			
			if($this->group_by_sku){
				$id_str = "si.sku_id";
			}else{
				$id_str = "si.id";
			}
			$sql_count = "select count(distinct $id_str) as c
				from sku_items si
				left join sku on sku.id=si.sku_id
				left join category_cache cc on cc.category_id=sku.category_id
				$str_join
				$filter
				order by $this->order_by";
			//print $sql_count;exit;
			$con_multi->sql_query($sql_count);
			$count_info = $con_multi->sql_fetchassoc();
			$con_multi->sql_freeresult();
			$this->data['total_page'] = ceil($count_info['c']/$this->page_limit);
			
			if($this->data['total_page']-1 < $this->page)	$this->page = 0;	// start from page 1
			
			$sql_select = "select $id_str as id
				from sku_items si
				left join sku on sku.id=si.sku_id
				left join category_cache cc on cc.category_id=sku.category_id
				$str_join
				$filter
				group by id
				order by $this->order_by
				$limit";
			//print $sql_select;
			$con_multi->sql_query("truncate $this->tmp_si_tbl");
			$con_multi->sql_query("insert into $this->tmp_si_tbl (id) ($sql_select)");
			//print_r($this->data);
			
			$this->data['item_row_use'] = 1;
			if(BRANCH_CODE == 'HQ'){
				$total_branch_count = count($this->branch_id_list);
				$this->data['branch_id_list_by_line'] = array();
				if($this->branch_per_line > $total_branch_count){
					$this->data['branch_per_line'] = $total_branch_count;
					$this->data['item_row_use'] = 2;
					$this->data['branch_id_list_by_line'][] = $this->branch_id_list;
					
				}else{
					$this->data['branch_per_line'] = $this->branch_per_line;
					$this->data['item_row_use'] = ceil($total_branch_count / $this->branch_per_line)*2;
					$i = 0;
					$branch_id_list_by_line = array();
					foreach($this->branch_id_list as $bid){
						$branch_id_list_by_line[] = $bid;
						$i++;
						
						if($i >= $this->branch_per_line){
							$this->data['branch_id_list_by_line'][] = $branch_id_list_by_line;
							$branch_id_list_by_line = array();
							$i = 0;
						}
					}
					if($branch_id_list_by_line)	$this->data['branch_id_list_by_line'][] = $branch_id_list_by_line;
				}
				$smarty->assign('branch_line_count', count($this->data['branch_id_list_by_line']));
			}
			
			//print_r($this->data);
		}
		
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
	}
	
	private function generate_excel(){
		global $sessioninfo, $smarty;
		
		if(!$this->data['total_page'])	return;
		
		$smarty->assign('is_export', 1);
		
		include("include/excelwriter.php");
		$times = time();
		$file_name_prefix = "report_".$sessioninfo['id']."_".$times;
		for($i = 0; $i <$this->data['total_page']; $i++){
			$this->page = $i;
			$this->load_report();
			
			$file_name = $file_name_prefix."_".($i+1).".xls";
			file_put_contents($this->file_folder."/".$file_name, ExcelWriter::GetHeader().$smarty->fetch('report.multi_branch_stock_balance.table.tpl').ExcelWriter::GetFooter());
		}
		
		$parent_zip = "Multi_Branch_Stock_Balance_".$sessioninfo['id']."_".$times;
		exec("cd " . $this->file_folder."; zip -9 $parent_zip.zip $file_name_prefix*.xls");
		header("Content-type: application/zip");
		header("Content-Disposition: attachment; filename=$parent_zip.zip");
		readfile($this->file_folder."/$parent_zip.zip");
		log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." to ZIP File Format");
		exit;		
	}
	
	private function load_report(){
		global $con, $smarty, $config, $sessioninfo, $con_multi;
		
		$filter = array();
		// filter active
		if($this->filter_active > 0){
			$filter[] = "si.active=1";
		}elseif($this->filter_active < 0){
			$filter[] = "si.active=0";
		}
			
		if($this->group_by_sku){
			$str_id = "si.sku_id";
		}else{
			$str_id = "si.id";
		}
		$str_si_join = "join $this->tmp_si_tbl stmp on stmp.id=$str_id";
		
		if($this->export_excel || $this->show_grand_total){
			$start = ($this->page * $this->page_limit)+1;
			$end = $start+$this->page_limit-1;
			
			if($this->export_excel){	// excel only load current page data
				$filter[] = "stmp.row_no between $start and $end";
			}			
		}else{
		}

		if(!isset($this->data['grand_total']))	$this->data['grand_total'] = array();
		$this->data['data'] = array();
		$this->data['page_total'] = array();
		$this->data['si_info'] = array();
		$this->data['curr_page'] = $this->page;
		$this->data['start_item_no'] = $this->page_limit*$this->page;
		
		$str_join = "";
		$str_extra_col = "";
		
		$filter = $filter ? "where ".join(' and ', $filter) : "";
		$str_join .= "left join sku_items_cost sic on sic.branch_id in (".join(',', $this->branch_id_list).") and sic.sku_item_id=si.id";
		$str_extra_col .= ", sic.branch_id as sic_bid, sic.qty as sb_qty";
		/*foreach($this->branch_id_list as $bid){
			//$tbl_name = "sic".$bid;
			$col_name = "sb".$bid;
			//$str_join .= " left join sku_items_cost $tbl_name on $tbl_name.branch_id=$bid and $tbl_name.sku_item_id=si.id";
			//$str_extra_col .= ", $tbl_name.qty as $col_name";
			$str_extra_col .= ", if(sic.branch_id=$bid,sic.qty,0) as $col_name";
		}*/
		
		if(BRANCH_CODE == 'HQ'){
			$str_own_cost_join = "left join sku_items_cost sic_own on sic_own.branch_id=1 and sic_own.sku_item_id=si.id";
		}else{
			$str_own_cost_join = "left join sku_items_cost sic_own on sic_own.branch_id=".mi($sessioninfo['branch_id'])." and sic_own.sku_item_id=si.id";
		}
		
		if($this->group_by_sku){
			$str_join_si = "join sku_items si on si.sku_id=stmp.id";
		}else{
			$str_join_si = "join sku_items si on si.id=stmp.id";
		}
		$sql = "select si.id as sid,si.sku_id,si.is_parent, si.sku_item_code,si.mcode,si.artno,si.link_code,si.description,uom.code as uom_code, uom.fraction as uom_fraction, sic_own.grn_cost as last_cost,stmp.row_no $str_extra_col
			from sku_items si
			left join uom on uom.id=si.packing_uom_id
			$str_si_join
			$str_own_cost_join
			$str_join
			$filter 
			order by $this->order_by";
		//print $sql."<br>";
		//return;	
		
		$q1 = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q1)){
			$need_record_si_info = false;
			$need_add_into_data = true;
			if($this->group_by_sku){
				$key = $r['sku_id'];
				if($r['is_parent']){
					$need_record_si_info = true;	// record only if it is parent sku
				}
			}else{
				$key = $r['sid'];
				$need_record_si_info = true;
			}
			
			if($this->show_grand_total){
				if($r['row_no'] < $start || $r['row_no'] > $end){
					$need_record_si_info = false;
					$need_add_into_data = false;
				}
			}
			
			if($need_record_si_info){
				if(!isset($this->data['si_info'][$key])){
					$this->data['si_info'][$key]['sku_item_code'] = $r['sku_item_code'];
					$this->data['si_info'][$key]['mcode'] = $r['mcode'];
					$this->data['si_info'][$key]['artno'] = $r['artno'];
					$this->data['si_info'][$key]['link_code'] = $r['link_code'];
					$this->data['si_info'][$key]['description'] = $r['description'];
					$this->data['si_info'][$key]['uom_code'] = $r['uom_code'];
					$this->data['si_info'][$key]['last_cost'] = $r['last_cost'];
				}				
			}
			
			// loop branch
			/*foreach($this->branch_id_list as $bid){
				$col_name = "sb".$bid;
				
				$qty = $r[$col_name];
				if($this->group_by_sku)	$qty *= $r['uom_fraction'];
				
				$this->data['data']['by_item'][$key]['stock_by_branch'][$bid]['qty'] += $qty;
				$this->data['data']['by_item'][$key]['total']['qty'] += $qty;
			}*/
			$bid = mi($r['sic_bid']);
			$qty = $r['sb_qty'];
			$cost = $qty * $r['last_cost'];
			if($this->group_by_sku)	$qty *= $r['uom_fraction'];
			
			if($need_add_into_data){
				$this->data['data']['by_item'][$key]['stock_by_branch'][$bid]['qty'] += $qty;
				$this->data['data']['by_item'][$key]['total']['qty'] += $qty;
				$this->data['data']['by_item'][$key]['total']['cost'] += $cost;
				
				$this->data['page_total']['qty'] += $qty;
				$this->data['page_total']['cost'] += $cost;
			}			
			
			if($this->show_grand_total){
				$this->data['grand_total']['qty'] += $qty;
				$this->data['grand_total']['cost'] += $cost;
			}
		}
		$con_multi->sql_freeresult($q1);
		
		if($this->show_grand_total){
			$display_grand_total = 0;
			if($this->export_excel){
				if($this->page == $this->data['total_page']-1){
					$display_grand_total = 1;
				}
			}else{
				$display_grand_total = 1;
			}
			$smarty->assign('display_grand_total', $display_grand_total);
		}
		
		//print_r($this->data);
		//print_r($this->data['grand_total']);
		$smarty->assign('data', $this->data);
	}
}

$MULTI_BRANCH_STOCK_BALANCE = new MULTI_BRANCH_STOCK_BALANCE('Multi Branch Stock Balance');
?>