<?php
/*
5/14/2010 2:38:24 PM Andy
- Add total stock take cost column.
- optimize sql spped.

7/16/2010 5:30:54 PM Andy
- Hide Item with "SKU without inventory".

1/21/2011 9:57:24 AM Alex
- change use report_server

4/11/2011 3:50:30 PM Andy
- Fix report to tally with stock take variance report.

9/22/2011 5:37:44 PM Andy
- Add "Group by SKU" when view single department.

2/29/2012 10:59:32 AM Justin
- Disabled the HQ view only function.
- Added new function "default_values" to extract date list when at sub branch.

9/4/2012 12:03 PM Justin
- Enhanced to have skip zero variance filter.

11/16/2012 3:49 PM Andy
- Change stock balance opening cost to use sb_cost, not sc_cost.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 10:59 AM Justin
- Bug fixed on Stock Take cost has wrongly calculated.

4/12/2017 9:00 AM Qiu Ying
- Enhanced to change "Group by SKU" to "Sort by SKU" and if stock take is N/A, should not calculate variance

4/27/2017 16:11 Qiu Ying
- Bug fixed on wrong calculation in Total Selling Price Variance & Total Cost Variance

8/17/2017 10:24 AM Justin
- Enhanced to move Selling Price next to SKU Description.
- Enhanced extra information such as stock balance cost and total cost.

10/1/2018 9:17 AM Justin
- Enhanced to have "Location" filter.

12/10/2018 3:10 PM Justin
- Enhanced report to have pre stock take feature.

3/19/2019 4:53 PM Andy
- Enhanced to have Brand and Vendor filter.

7/29/2019 9:00 AM William
- Enhanced to have "Show auto fill zero item" filter.

12/30/2019 11:59 AM Andy
- Fixed get last cost query to force use primary key as index.

2/17/2020 5:19 PM William
- Enhanced to change $con connection to use $con_multi.

12/02/2020 5:09 PM Rayleen
- Add link_code in sku_items array
*/
ini_set('memory_limit', '512M');
set_time_limit(0);
include("include/common.php");
$maintenance->check(1);
include("include/class.report.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
//if (BRANCH_CODE != 'HQ') js_redirect($LANG['REPORT_IS_HQ_ONLY'], "/index.php");
if (!privilege('STOCK_CHECK_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_CHECK_REPORT', BRANCH_CODE), "/index.php");

class StockTakeVarianceByDept extends Report
{
	function __construct($title){
		global $con, $smarty, $sessioninfo, $con_multi, $appCore;
		
		if(!$con_multi) $con_multi = $appCore->reportManager->connectReportServer();
		
	    if(BRANCH_CODE=='HQ'){
            $this->branch_id = isset($_REQUEST['branch_id']) ? mi($_REQUEST['branch_id']) : $sessioninfo['branch_id'];
		}else{
            $this->branch_id = $sessioninfo['branch_id'];
		}
		
		parent::__construct($title);
	}
	
	private function run($branch_id){
	    global $con, $sessioninfo, $smarty, $con_multi;
	    
        if($this->sort_by)	$order = "order by $this->sort_by $this->sort_order";
        
        $filter = array();
        if($this->sku_with!='show_all_sku'){
			if($this->sku_with=='sc'){
				/*$sc_filter = array();
				$sc_filter[] = "sc.branch_id=$branch_id and sc.date=".ms($this->selected_date);
				if(!$this->all_shelf_no)	$sc_filter[] = "sc.shelf_no between ".ms($this->shelf_no_from)." and ".ms($this->shelf_no_to);
				
				$filter[] = "si.sku_item_code in (select sc.sku_item_code from stock_check sc where ".join(' and ', $sc_filter).")";*/
				
			    $filter[] = "sc.qty is not null";
				
				if(!$this->all_location){
					$filter[] = "sc.location between ".ms($this->location_from)." and ".ms($this->location_to);
				}
				
                if(!$this->all_shelf_no){
					if($this->stock_take_type == 1){ // imported stock take
						$filter[] = "sc.shelf_no between ".ms($this->shelf_no_from)." and ".ms($this->shelf_no_to);
					}else{
						$filter[] = "sc.shelf between ".ms($this->shelf_no_from)." and ".ms($this->shelf_no_to);
					}
				}
			}else{
				$filter[] = "(sc.qty<>0 or sb.qty<>0)";
			}
		}
		if($this->sku_type) $filter[] = "sku.sku_type=".ms($this->sku_type);
		
		if($this->dept_id)  $filter[] = "c.department_id=".mi($this->dept_id);
		else    $filter[] = "c.department_id in (".join(',',array_keys($sessioninfo['departments'])).")";
		$filter[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
		if($this->vendor_id>0)	$filter[] = "sku.vendor_id=".mi($this->vendor_id);
		if($this->brand_id == -1){	// un-branded
			$filter[] = "sku.brand_id=0";
		}elseif($this->brand_id>0){
			$filter[] = "sku.brand_id=".mi($this->brand_id);
		}
		if($_REQUEST['show_auto_fill_zero_item']){
			$filter[] = "sc.qty = 0 and (sc.location is null or sc.location='') and (sc.shelf_no is null or sc.shelf_no = '')";	
		}
		
		if($filter)	$filter = "where ".join(' and ', $filter);
		else 	$filter = '';
		
		if($this->skip_zero_variance) $having = "having sc_qty-sb_qty != 0";

		$sb_tbl = "stock_balance_b".$branch_id."_".date('Y',strtotime($this->balance_date));
		
		// load data from stock_take_pre OR stock_check
		if($this->stock_take_type == 1){ // imported stock take
			$cols = "sum(sc.cost*sc.qty)/sum(sc.qty) as sc_cost, sc.selling as sc_selling_price,";
			$st_lj = "left join stock_check sc on sc.sku_item_code=si.sku_item_code and sc.branch_id=$branch_id and sc.date=".ms($this->selected_date);
		}else{ // pre stock take
			$cols = "sum(ifnull((select sich.grn_cost from sku_items_cost_history sich USE INDEX (PRIMARY) where sich.branch_id=$branch_id and sich.sku_item_id=si.id and sich.date<=".ms($this->selected_date)." order by date desc limit 1), si.cost_price)*sc.qty)/sum(sc.qty) as sc_cost,";
			$st_lj = "left join stock_take_pre sc on sc.sku_item_id=si.id and sc.branch_id=$branch_id and sc.date=".ms($this->selected_date);
		}
		
		// old sql
		$sql = "select ifnull(sb.qty, 0) as sb_qty, sb.cost as sb_cost, sum(ifnull(sc.qty,0)) as sc_qty, sc.date as sc_date, $cols
		 si.*,c.department_id
				from sku_items si
				left join sku on sku.id=si.sku_id
				left join category c on c.id=sku.category_id
				left join category dept on dept.id=c.department_id
				left join category_cache cc on cc.category_id=sku.category_id
				left join $sb_tbl sb on sb.sku_item_id=si.id and ((".ms($this->balance_date)." between sb.from_date and sb.to_date) or (".ms($this->balance_date).">=from_date and is_latest=1))
				$st_lj
				$filter group by si.id $having $order";
		// new sql
		/*$sql = "select ifnull(sb.qty, 0) as sb_qty, sb.cost as sb_cost,
		 si.*,c.department_id
from sku_items si
left join sku on sku.id=si.sku_id
left join category c on c.id=sku.category_id
left join category dept on dept.id=c.department_id
left join category_cache cc on cc.category_id=sku.category_id
left join $sb_tbl sb on sb.sku_item_id=si.id and ((".ms($this->balance_date)." between sb.from_date and sb.to_date) or (".ms($this->balance_date).">=from_date and is_latest=1))
$filter group by si.id $having $order";*/
		//print $sql;
		$q1 = $con_multi->sql_query($sql);//print "$sql<br /><br />";//xx
		$total = array();
		$count = 0;
		while($r = $con_multi->sql_fetchassoc($q1)){
			$sku_id = 0;
			$sid = mi($r['id']);
			
			// get stock check info
			/*$q_sc = $con->sql_query("select sum(ifnull(sc.qty,0)) as sc_qty,sc.selling as sc_selling_price, (sum(sc.qty*sc.cost)/sum(sc.qty)) as sc_cost,sc.date as sc_date
			from stock_check sc
			where sc.branch_id=$branch_id and sc.date=".ms($this->selected_date)." and sc.sku_item_code=".ms($r['sku_item_code']));
			$sc_info = $con->sql_fetchassoc($q_sc);
			$con->sql_freeresult($q_sc);
			
			if($sc_info)	$r = array_merge($r, $sc_info);
			
			if($this->sku_with=='sb' && !$r['sc_qty'] && !$r['sb_qty'])	continue;
			
			if($this->skip_zero_variance){
				if($r['sc_qty'] - $r['sb_qty'] == 0)	continue;
			}*/
			
		    // selling
		    $selling_price = 0;
			if($r['sc_selling_price']){  // got stock take, use stock take selling
			    $selling_price = $r['sc_selling_price'];
			}else{  // use last selling by date
				$tmp = get_sku_item_cost_selling($branch_id, $r['id'], $this->selected_date, array('selling'));
				$selling_price = $tmp['selling'];
			}
			
			if($this->dept_id){ // view by single dept
			    $key = $r['sku_item_code'];
			    $sku_id = $r['sku_id'];

			    if(!$this->sku_items[$key]){
                    $this->sku_items[$key]['id'] = $r['id'];
                    $this->sku_items[$key]['sku_id'] = $r['sku_id'];
                    $this->sku_items[$key]['sku_item_code'] = $r['sku_item_code'];
                    $this->sku_items[$key]['mcode'] = $r['mcode'];
                    $this->sku_items[$key]['artno'] = $r['artno'];
                    $this->sku_items[$key]['description'] = $r['description'];
                    $this->sku_items[$key]['link_code'] = $r['link_code'];

				}
			}else{  // view all dept
			    $key = mi($r['department_id']);
			}
			
			if($this->dept_id && $sku_id && $this->group_by_sku && !$r['is_parent']){
				$child_data[$sku_id][$key]['selling'] = $selling_price;
				$child_data[$sku_id][$key]['sb_cost'] = $r['sb_cost'];
				// qty
				$child_data[$sku_id][$key]['stock_balance'] += $r['sb_qty'];
				$child_data[$sku_id][$key]['stock_take_qty'] += $r['sc_qty'];
				if($r['sc_date']){
					$child_data[$sku_id][$key]['got_sc'] = 1;
					$child_data[$sku_id][$key]['cost'] = $r['sc_cost'];
					
					// selling
					$child_data[$sku_id][$key]['sb_total_selling'] += ($r['sb_qty']*$selling_price);
					$child_data[$sku_id][$key]['sc_total_selling'] += ($r['sc_qty']*$selling_price);
					
					// cost
					$child_data[$sku_id][$key]['sb_total_cost'] += ($r['sb_qty']*$r['sb_cost']);
					$child_data[$sku_id][$key]['sc_total_cost'] += ($r['sc_qty']*$r['sc_cost']);
					
					$child_data[$sku_id][$key]['row_variances'] += $r['sc_qty'] - $r['sb_qty'];
					$child_data[$sku_id][$key]['row_sp_variance'] += ($r['sc_qty']*$selling_price) - ($r['sb_qty']*$selling_price);
					$child_data[$sku_id][$key]['row_cost_variance'] += ($r['sc_qty']*$r['sc_cost']) - ($r['sb_qty']*$r['sb_cost']);
				}   
			}else{
				$table[$key]['key'] = $key;
				$table[$key]['selling'] = $selling_price;
				$table[$key]['sb_cost'] = $r['sb_cost'];
				// qty
				$table[$key]['stock_balance'] += $r['sb_qty'];
				$table[$key]['stock_take_qty'] += $r['sc_qty'];
				
				if($r['sc_date']){
					$table[$key]['got_sc'] = 1;
					$table[$key]['cost'] = $r['sc_cost'];
					
					// selling
					$table[$key]['sb_total_selling'] += ($r['sb_qty']*$selling_price);
					$table[$key]['sc_total_selling'] += ($r['sc_qty']*$selling_price);
					
					// cost
					$table[$key]['sb_total_cost'] += ($r['sb_qty']*$r['sb_cost']);
					$table[$key]['sc_total_cost'] += ($r['sc_qty']*$r['sc_cost']);
					
					$table[$key]['row_variances'] += ($r['sc_qty'] - $r['sb_qty']);
					$table[$key]['row_sp_variance'] += ($r['sc_qty']*$selling_price) - ($r['sb_qty']*$selling_price);
					$table[$key]['row_cost_variance'] += ($r['sc_qty']*$r['sc_cost']) - ($r['sb_qty']*$r['sb_cost']);
				}
			}
			$total["total_sb_qty"] += $r['sb_qty'];
			$total["total_sb_cost"] += ($r['sb_qty']*$r['sb_cost']);
			$total["total_sc_qty"] += $r['sc_qty'];
			
			if($r['sc_date']){
				$total["total_sc_cost"] += ($r['sc_qty']*$r['sc_cost']);
				$total["total_variance"] += ($r['sc_qty'] - $r['sb_qty']);
				$total["total_sp_variance"] += ($r['sc_qty']*$selling_price) - ($r['sb_qty']*$selling_price);
				$total["total_cost_variance"] += ($r['sc_qty']*$r['sc_cost']) - ($r['sb_qty']*$r['sb_cost']);
				$count++;
			}
		}
		$con_multi->sql_freeresult($q1);
		
 		if($count == 0){
			$smarty->assign("is_available", 0);
		}else{
			$smarty->assign("is_available", 1);
		}
		
		// check whether got child dont have parent row
		if($this->dept_id && $sku_id && $this->group_by_sku && $child_data){
			$need_resort = false;
			foreach($child_data as $sku_id=>$childs_sku){
				$con_multi->sql_query("select si.* from sku_items si where sku_id=".mi($sku_id)." and is_parent=1");
				$parent_sku = $con_multi->sql_fetchassoc();
				$con_multi->sql_freeresult();
				
				$key = $parent_sku['sku_item_code'];
				if(!isset($table[$key])){	// parent row not found
					$tmp = get_sku_item_cost_selling($branch_id, $parent_sku['id'], $this->selected_date, array('selling'));
					$table[$key]['selling'] = $tmp['selling'];
					$table[$key]['key'] = $key;
					// sku items info
					$this->sku_items[$key]['id'] = $parent_sku['id'];
                    $this->sku_items[$key]['sku_id'] = $parent_sku['sku_id'];
                    $this->sku_items[$key]['sku_item_code'] = $parent_sku['sku_item_code'];
                    $this->sku_items[$key]['mcode'] = $parent_sku['mcode'];
                    $this->sku_items[$key]['artno'] = $parent_sku['artno'];
                    $this->sku_items[$key]['description'] = $parent_sku['description'];
                    $this->sku_items[$key]['link_code'] = $parent_sku['link_code'];
                    
                    $need_resort = true;
				}
			}
			
			if($need_resort){
				uksort($table, array($this,"sort_table"));
			}
		}
		
		//print_r($table);
		//print_r($this->sku_items);
		$smarty->assign('table', $table);
		$smarty->assign('sku_items', $this->sku_items);
		$smarty->assign('child_data', $child_data);
		$smarty->assign('total', $total);
	}
	
	function default_values(){
		if(!$_REQUEST['date']) $_REQUEST['date'] = date("Y-m-d");
		if(!$_REQUEST['pre_date']) $_REQUEST['pre_date'] = date("Y-m-d");
		
		if((BRANCH_CODE == "HQ" && $_REQUEST['branch_id']) || BRANCH_CODE != "HQ"){
			$this->load_date(true);
			$this->load_pre_date(true);
		}
	}
	
	function sort_table($a,$b){
		$col = $this->sort_by;
		$order = $this->sort_order;
		
		$item_a = $this->sku_items[$a['key']];
		$item_b = $this->sku_items[$b['key']];
		
	    if ($item_a[$col]==$item_b[$col]) return 0;
	    elseif($order=='desc')  return ($item_a[$col]>$item_b[$col]) ? -1 : 1;
	    else	return ($item_a[$col]>$item_b[$col]) ? 1 : -1;
	}
	
    function generate_report()
	{
		global $con, $con_multi, $smarty,$sessioninfo;
		
		$this->run($this->bid);
		
		// generate stock check date list
		$date_list = $this->load_stock_check_date_by_branch($this->bid);
		if($this->sku_with=='sc' && $this->selected_date){
			$prms = array();
			$prms['stop_load_tpl'] = true;
			$prms['branch_id'] = $this->bid;
			$prms['date'] = $this->selected_date;
			$prms['stock_take_type'] = $this->stock_take_type;
			$this->load_location($prms);
			
			$prms['all_loc'] = $this->all_location;
			$prms['loc_from'] = $this->location_from;
			$prms['loc_to'] = $this->location_to;
			$this->load_shelf_no($prms);
			
			unset($prms);
		}
		$smarty->assign('date_list',$date_list);
		
		// report title
		$report_title = array();
		$report_title[] = "Branch: ".get_branch_code($this->bid);
		$report_title[] = "Date: ".$this->selected_date.(($this->stock_take_type == 2) ? " (Preview)" : "");
		$report_title[] = "SKU Type: ".(($this->sku_type)?  $this->sku_type : "All");
		if($this->sku_with=='sc'){
			$report_title[] = "Location No: ".(($this->all_location) ? "All" : $this->location_from." to ".$this->location_to);
			$report_title[] = "Shelf No: ".(($this->all_shelf_no) ? "All" : $this->shelf_no_from." to ".$this->shelf_no_to);
		}
		$report_title[] = "Vendor: ".(($this->vendor_id>0)?  $this->vendors_list[$this->vendor_id]['description'] : "All");
		if($this->brand_id == -1){
			$report_title[] = "Brand: UN-BRANDED";
		}else{
			$report_title[] = "Brand: ".(($this->brand_id>0)?  $this->brands_list[$this->brand_id]['description'] : "All");
		}
		$report_title[] = "Department: ".(($this->dept_id>0)?  $this->dept_list[$this->dept_id]['description'] : "All");
		
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		
		$this->load_date(true);
		$this->load_pre_date(true);
	}
	
    function process_form()
	{
	    global $config,$con,$smarty,$sessioninfo;
		// do my own form process

		// call parent
		parent::process_form();

        $this->bid = get_request_branch(true);
		$this->stock_take_type = mi($_REQUEST['stock_take_type']);
		if($this->stock_take_type == 1){ // getting imported stock take
			$this->selected_date = $_REQUEST['date'];
		}else{ // getting pre stock take
			$this->selected_date = $_REQUEST['pre_date'];
		}
		$this->balance_date = date('Y-m-d',strtotime('-1 day',strtotime($this->selected_date)));
		
		$this->sku_with = $_REQUEST['sku_with'];
		$this->output_excel = isset($_REQUEST['output_excel']);
		
		$this->sort_by = $_REQUEST['sort_by'];
		$this->sort_order = $_REQUEST['sort_order'];
		
		$this->location_from = $_REQUEST['location_from'];
		$this->location_to = $_REQUEST['location_to'];
		$this->all_location = $_REQUEST['all_location'];
		
		$this->shelf_no_from = $_REQUEST['shelf_no_from'];
		$this->shelf_no_to = $_REQUEST['shelf_no_to'];
		$this->all_shelf_no = $_REQUEST['all_shelf_no'];

		$this->sku_type = $_REQUEST['sku_type'];
		$this->skip_zero_variance = $_REQUEST['skip_zero_variance'];
		$this->dept_id = mi($_REQUEST['dept_id']);
		$this->group_by_sku = mi($_REQUEST['group_by_sku']);
		$this->vendor_id = mi($_REQUEST['vendor_id']);
		$this->brand_id = mi($_REQUEST['brand_id']);
	}
	
	function change_branch(){
	    global $con;

		$branch_id = mi($_REQUEST['branch_id']);

		if($branch_id>0)	$date_list = $this->load_stock_check_date_by_branch($branch_id);

		print "<select name='selected_date' onChange='date_changed();'>";
		if($date_list){
			foreach($date_list as $r){
				$date = $r['date'];
				print "<option value='".$date."'>$date</option>";
			}
		}else{
			print "<option value=''>-- No Data --</option>";
		}
		print "</select>";
	}
	
	private function load_stock_check_date_by_branch($branch_id){
        global $con_multi;
        $sql = "select distinct(date) as date from stock_check where branch_id=$branch_id order by date desc";
        $con_multi->sql_query($sql) or die(mysql_error());
        $date_list = $con_multi->sql_fetchrowset();
        $con_multi->sql_freeresult();
        
        return $date_list;
	}
	
	function load_location($prms=array()){
		global $con_multi, $smarty;

		$branch_id = mi($_REQUEST['branch_id']);
		$date = $_REQUEST['d'];
		$stock_take_type = $_REQUEST['stock_take_type'];
		
		if($prms){
			$branch_id = mi($prms['branch_id']);
			$date = $prms['date'];
			$stock_take_type = $prms['stock_take_type'];
		}

		if($branch_id && $date){
			if($stock_take_type == 1) $sql="select distinct(location) as location from stock_check where date=".ms($date)." and branch_id=$branch_id order by location";
			else $sql="select distinct(location) as location from stock_take_pre where date=".ms($date)." and branch_id=$branch_id order by location";
			
			$con_multi->sql_query($sql);
			$smarty->assign('location_list',$con_multi->sql_fetchrowset());
			$con_multi->sql_freeresult();
		}
		if(!$prms['stop_load_tpl']) $smarty->display('report.stock_take_variance.location.tpl');
	}
	
	function load_shelf_no($prms=array()){
		global $con_multi, $smarty;

		$branch_id = $_REQUEST['branch_id'];
		$date = $_REQUEST['d'];
		$all_loc = $_REQUEST['all_loc'];
		$loc_from = $_REQUEST['loc_from'];
		$loc_to = $_REQUEST['loc_to'];
		$stock_take_type = $_REQUEST['stock_take_type'];
		
		if($prms){
			$branch_id = $prms['branch_id'];
			$date = $prms['date'];
			$all_loc = $prms['all_loc'];
			$loc_from = $prms['loc_from'];
			$loc_to = $prms['loc_to'];
			$stock_take_type = $prms['stock_take_type'];
		}
		
		$filters = array();
		$filters[] = "date = ".ms($date);
		$filters[] = "branch_id = ".mi($branch_id);
		if(!$all_loc){
			$filters[] = "location between ".ms($loc_from)." and ".ms($loc_to);
		}

		if($branch_id && $date){
			// stock_take_type = 1 > imported stock take data
			// stock_take_type = 2 > pre stock take data
			if($stock_take_type == 1) $sql="select distinct(shelf_no) as shelf_no from stock_check where ".join(" and ", $filters)." order by shelf_no";
			else $sql="select distinct(shelf) as shelf_no from stock_take_pre where ".join(" and ", $filters)." order by shelf_no";
			
			$con_multi->sql_query($sql);
			$smarty->assign('shelf_no_list',$con_multi->sql_fetchrowset());
			$con_multi->sql_freeresult();
		}
		if(!$prms['stop_load_tpl']) $smarty->display('report.stock_take_variance.shelf_no.tpl');
	}
	
	function ajax_reload_date(){
        global $con, $smarty;
        
        $ret = array();
        $ret['st_date'] = $this->load_date();
        $ret['pre_st_date'] = $this->load_pre_date();
        
        print json_encode($ret);
	}
	
	private function load_date($sqlonly = false){
		global $con_multi, $smarty;
		
		$branch_id = mi($this->branch_id);
		$q1 = $con_multi->sql_query("select distinct date from stock_check where branch_id=$branch_id and is_fresh_market=0 order by date desc");
		while($r = $con_multi->sql_fetchassoc($q1)){
			$date[] = $r['date'];
		}
		$con_multi->sql_freeresult($q1);
		$smarty->assign('date', $date);
		if(!$sqlonly){
		    $smarty->assign('date_list', $date);
		    return $smarty->fetch('report.stock_take_variance.date_sel.tpl');
		}
		return $date;
	}
	
	private function load_pre_date($sqlonly = false){
        global $con_multi, $smarty;

		$branch_id = mi($this->branch_id);
		$q1 = $con_multi->sql_query("select distinct date from stock_take_pre stp where branch_id=$branch_id and is_fresh_market=0 and imported=0 order by date desc");
		while($r = $con_multi->sql_fetchassoc($q1)){
			$pre_date[] = $r['date'];
		}
		$con_multi->sql_freeresult($q1);
		$smarty->assign('pre_date', $pre_date);
		if(!$sqlonly){
			$smarty->assign('date_list', $pre_date);
			$smarty->assign('sel_name', 'pre_date');
		    return $smarty->fetch('report.stock_take_variance.date_sel.tpl');
		}
		return $pre_date;
	}
}
//$con_multi = new mysql_multi();
$StockTakeVarianceByDept = new StockTakeVarianceByDept('Stock Take Variance by Dept Report');
//$con_multi->close_connection();
?>
