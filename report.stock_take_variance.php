<?php
/*
8/19/2009 2:54:46 PM Andy
- group by sku show only parent
 
9/28/2009 3:52 PM Andy
- group by sku show parent and child, without grouping will show 5000 items per page, with grouping will show 2500 groups (parent + child)
- show item if have either stock balance or stock check. hide item if both empty.
- if "show all" is checked, show all the items include which have both stock balance and stock check equal to zero 

11/11/2009 10:28:04 AM Andy
- Fix cost price and sql sum total cost bug

12/16/2009 4:15:32 PM Andy
- add selection to let user choose sku condition

12/21/2009 1:36:06 PM Andy
- Show Shelf No "from and to" for user to choose if select "Only Stock Take Item"

4/8/2010 10:12:05 AM Andy
- Stock Take Variance Report show Department column if got config

4/15/2010 5:49:30 PM Andy
- Add sku type filter

7/16/2010 4:58:21 PM Andy
- Hide Item with "SKU without inventory".

1/21/2011 10:42:15 AM Alex
- change use report_server
- add department filter

4/8/2011 6:23:08 PM Andy
- Fix when "group by sku" if group sum(qty) variance is zero it will not show in report.

6/27/2011 10:03:00 AM Andy
- Make all branch default sort by sequence, code.

9/28/2011 5:41:26 PM Alex
- comment display errors

12/14/2011 11:27:10 AM Andy
- Modify "Stock Take Variance Report" to allow branch access.
- Add checking privilege "STOCK_CHECK_REPORT" for "Stock Take Variance Report".

5/21/2012 5:13:34 PM Justin
- Fixed bugs that some of the array setup causing report run out of memory.

6/13/2012 05:46:00 PM Andy
- Fix ARMS Code not showing.

8/29/2012 5:47 PM Andy
- Fix group by sku bug.

9/4/2012 12:03 PM Justin
- Enhanced to have skip zero variance filter.

10/3/2012 3:30:00 PM Fithri
- stock take report can select item (Stock Take Variance Report)

2/4/2013 5:34 PM Justin
- Enhanced to show and filter branches from regions or branch group base on user's regions.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5:25 PM 4/6/2015 Andy
- Fix sorting bug when got skip zero variance.

4/12/2017 9:00 AM Qiu Ying
- Enhanced to change "Group by SKU" to "Sort by SKU" and if stock take is N/A, should not calculate variance

8/16/2017 3:49 PM Justin
- Bug fixed on cost variance calculation.

10/1/2018 9:17 AM Justin
- Enhanced to have "Location" filter.

12/10/2018 3:10 PM Justin
- Enhanced report to have pre stock take feature.

3/15/2019 4:26 PM Andy
- Enhanced to have Brand, Vendor and Department filter.

7/29/2019 9:00 AM William
- Enhanced to have "Show auto fill zero item" filter.

8/9/2019 9:54 AM William
- Fixed bug "Stock Take Cost" and "Total Cost" get last stock take cost when difference cost at same date, branch and sku item.

1/3/2020 2:03 PM Andy
- Fixed get last cost query to force use primary key as index.

2/17/2020 5:24 PM William
- Enhanced to change $con connection to use $con_multi.

12/03/2020 1:46 PM Rayleen
- Add link_code in $sku_items_info array
*/
ini_set('memory_limit', '512M');
//ini_set("display_errors", 1);
set_time_limit(0);
include("include/common.php");
//$con = new sql_db('cutemaree.dyndns.org:4001','arms','990506','armshq');

include("include/class.report.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
//if (BRANCH_CODE != 'HQ') js_redirect($LANG['REPORT_IS_HQ_ONLY'], "/index.php");
if (!privilege('STOCK_CHECK_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_CHECK_REPORT', BRANCH_CODE), "/index.php");

class StockTakeVariance extends Report
{
	var $page_size = 5000;
	
	function __construct($title){
		global $con, $smarty, $sessioninfo, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
	    if(BRANCH_CODE=='HQ'){
            $this->branch_id = isset($_REQUEST['branch_id']) ? mi($_REQUEST['branch_id']) : $sessioninfo['branch_id'];
		}else{
            $this->branch_id = $sessioninfo['branch_id'];
		}
		
		parent::__construct($title);
	}
	
	private function run_group_report($branch_id){
		global $con,$smarty,$sessioninfo,$con_multi;
		
		//$data = $this->data;
		//$total = $this->total;
		
		$this->data = array();
		$this->data2 = array();
		$this->total = array();
		$this->group_data = array();
		
		$start = $this->page_size_start;
		$size = $this->page_size;
		
		if($this->sort_by){
			$order = "order by $this->sort_by $this->sort_order";
		}
		$sb_tbl = "stock_balance_b".$branch_id."_".date('Y',strtotime($this->balance_date));
		
		/*if(!$this->show_all_sku){
			//$having = "having (sb_qty<>0 or sc_qty is not null)";
			$filter[] = "(sb.qty<>0 or sc.qty is not null)";
		}*/
		
		if($this->sku_with!='show_all_sku'){
			if($this->sku_with=='sb'){
                //$having = "having (sb_qty<>0 or sc_qty is not null)";
                $filter[] = "(sb.qty<>0 or sc.qty is not null)";
			}
			elseif($this->sku_with=='selected_sku'){
				$code_list = $this->sku_code_list_2;
				$list = explode(",",$code_list);
				foreach($list as $lkey=>$lval) $list[$lkey] = "'$lval'";
				$list = join(',',$list);
				$filter[] = "si.sku_item_code in($list)";
			}
			else{
                //$having = "having sc_qty is not null";
                $filter[] = "sc.qty is not null";
				
				if(!$this->all_location){
					$filter[] = "sc.location between ".ms($this->location_from)." and ".ms($this->location_to);
				}
				
				if(!$this->all_shelf_no){
					if($this->stock_take_type == 1){ // imported stock take
						$shelf_name = "sc.shelf_no";
					}else{ // pre stock take
						$shelf_name = "sc.shelf";
					}
					$filter[] = $shelf_name." between ".ms($this->shelf_no_from)." and ".ms($this->shelf_no_to);
				}
			}    
		}
		$filter[] = "c.department_id in (".join(',',array_keys($sessioninfo['departments'])).")";
		if($this->sku_type) $filter[] = "sku.sku_type=".ms($this->sku_type);
		$filter[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
		if($this->vendor_id>0)	$filter[] = "sku.vendor_id=".mi($this->vendor_id);		
		if($this->brand_id == -1){	// un-branded
			$filter[] = "sku.brand_id=0";
		}elseif($this->brand_id>0){
			$filter[] = "sku.brand_id=".mi($this->brand_id);
		}
		if($this->dept_id>0)	$filter[] = "c.department_id=".mi($this->dept_id);
		if($this->show_auto_fill_zero_item) $filter[] = "sc.qty = 0 and (sc.location is null or sc.location='') and (sc.shelf_no is null or sc.shelf_no = '')";		
		
		if($this->skip_zero_variance) $having = "having sc_qty-sb_qty != 0";
		$pre_filter = "where ".join(' and ', $filter);
		
		// load data from stock_take_pre OR stock_check
		if($this->stock_take_type == 1){ // imported stock take
			$cols = "sum(sc.cost*sc.qty)/sum(sc.qty) as sc_cost, sc.selling as sc_selling_price, sum(sc.cost*sc.qty) as total_cost,";
			$st_lj = "left join stock_check sc on sc.sku_item_code=si.sku_item_code and sc.branch_id=$branch_id and sc.date=".ms($this->selected_date);
		}else{ // pre stock take
			$cols = "ifnull((select sich.grn_cost from sku_items_cost_history sich USE INDEX (PRIMARY) where sich.branch_id=$branch_id and sich.sku_item_id=si.id and sich.date<=".ms($this->selected_date)." order by date desc limit 1), si.cost_price) as sc_cost, sum(sc.cost_price*sc.qty) as total_cost,";
			$st_lj = "left join stock_take_pre sc on sc.sku_item_id=si.id and sc.branch_id=$branch_id and sc.date=".ms($this->selected_date);
		}
		
		// get sku id list
		$sql = "select sum(ifnull(sb.qty,0)) as sb_qty,sum(ifnull(sc.qty,0)) as sc_qty,si.sku_id 
from sku_items si 
left join sku on sku.id=si.sku_id
left join category c on c.id=sku.category_id
left join category_cache cc on cc.category_id=sku.category_id
left join $sb_tbl sb on sb.sku_item_id=si.id and ((".ms($this->balance_date)." between sb.from_date and sb.to_date) or (".ms($this->balance_date).">=from_date and is_latest=1))
$st_lj
 $pre_filter group by si.sku_id $having $order limit $start , $size";
 
		//print $sql;
		//$con->sql_query($sql) or die(mysql_error());
		$con_multi->sql_query($sql,false,false);//print "$sql<br /><br />";//xx
		$sku_id_list = array();
		while($r = $con_multi->sql_fetchrow()){
			$sku_id_list[] = $r['sku_id'];
		}
		$con_multi->sql_freeresult();
		
		if(!$sku_id_list)	return;
		
		$filter[] = "si.sku_id in (".join(',',$sku_id_list).")";
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
		$filter = "where ".join(' and ', $filter);
		
		$sql = "select sb.qty as sb_qty, sb.cost as sb_cost, sum(sc.qty) as sc_qty, sc.date as sc_date, $cols
		 si.id, si.sku_item_code, si.description, si.mcode, si.artno, dept.description as dept_desc, si.sku_id,si.sku_item_code,si.is_parent,si.link_code 
from sku_items si 
left join sku on sku.id=si.sku_id
left join category c on c.id=sku.category_id
left join category dept on dept.id=c.department_id
left join category_cache cc on cc.category_id=sku.category_id
left join $sb_tbl sb on sb.sku_item_id=si.id and ((".ms($this->balance_date)." between sb.from_date and sb.to_date) or (".ms($this->balance_date).">=from_date and is_latest=1))
$st_lj
$filter group by si.id $having $order";
		//print $sql;
		//$con->sql_query($sql) or die(mysql_error());
		$con_multi->sql_query($sql,false,false);//print "$sql<br /><br />";//xx
		
		while($r = $con_multi->sql_fetchassoc()){
			$temp = array();
			$temp['stock_balance'] = $r['sb_qty'];
			$temp['stock_take_qty'] = $r['sc_qty'];
			$temp['selling_price'] = $r['sc_selling_price'];
			//$temp['cost'] = $r['sc_cost']? $r['sc_cost'] : $r['sb_cost'];
			$temp['cost'] = $r['sc_cost'];
			//$temp['total_cost'] = $r['total_cost'];
			$temp['total_cost'] = ($r['sc_qty']*$r['sc_cost'])-($r['sb_qty']*$r['sb_cost']);
			$temp['sc_date'] = $r['sc_date'];
			$temp['sku_item_id'] = $r['id'];
			$temp['dept_desc'] = $r['dept_desc'];
			
			//if(!$temp['cost'])	$temp['cost'] = $r['cost_price'];
			
			$data[$r['id']] = $temp;
			if(!isset($this->sku_items_info[$r['id']])){
				$tmp_info = array();
				$tmp_info['sku_item_code'] = $r['sku_item_code'];
				$tmp_info['description'] = $r['description'];
				$tmp_info['artno'] = $r['artno'];
				$tmp_info['mcode'] = $r['mcode'];
				$tmp_info['dept_desc'] = $r['dept_desc'];
				$tmp_info['link_code'] = $r['link_code'];
				$this->sku_items_info[$r['id']] = $tmp_info;
			}
			
			if(!$r['sc_selling_price'])	$item_no_price[] = $r['id'];
			
			if($r['is_parent']){
				$group_data[$r['sku_id']]['parent'] = $r['id'];
			}else 	$group_data[$r['sku_id']]['child'][$r['id']] = $r['id'];
		}
		$con_multi->sql_freeresult();
		
		// get selling price
		if($item_no_price){
		    foreach($item_no_price as $sid){
                $con_multi->sql_query("select si.id, ifnull(siph.price, si.selling_price) as selling_price
				from sku_items si
				left join sku_items_price_history siph on si.id=siph.sku_item_id and siph.branch_id=$branch_id and siph.added<".ms($this->selected_date)."
				where si.id=".mi($sid)."
				order by siph.added desc limit 1
				");
				$temp = $con_multi->sql_fetchassoc();
				$con_multi->sql_freeresult();
				$data[$sid]['selling_price'] = $temp['selling_price'];
			}
			/*
			$sql = "select si.id,si.selling_price,(select max(added) from sku_items_price_history siph where siph.sku_item_id=si.id and siph.branch_id=$branch_id and added<".ms($this->selected_date).") as max_added,
	(select price from sku_items_price_history siph2 where siph2.sku_item_id=si.id and siph2.branch_id=$branch_id and siph2.added=max_added limit 1) as price_history
	from sku_items si where si.id in (".join(',',$item_no_price).")";
			$con_multi->sql_query($sql) or die(mysql_error());
			while($r = $con_multi->sql_fetchrow()){
				$data[$r['id']]['selling_price'] = $r['price_history']?$r['price_history']:$r['selling_price'];
			}
			$con_multi->sql_freeresult();*/
		}
		
		// check which group don't have parent
		$item_no_parent = array();
		if($group_data){
			foreach($group_data as $sku_id=>$pc){
				if(!$pc['parent'])	$item_no_parent[] = $sku_id;
			}
			//print_r($item_no_parent);
			if($item_no_parent){
				$sql = "select si.id, si.sku_item_code, si.description, si.artno, si.mcode, si.sku_id, dept.description as dept_desc,si.sku_item_code,si.link_code
				from sku_items si
				left join sku on sku.id=si.sku_id
left join category c on c.id=sku.category_id
left join category dept on dept.id=c.department_id
where si.sku_id in (".join(',',$item_no_parent).") and is_parent=1";
				$con_multi->sql_query($sql) or die(mysql_error());
				while($r = $con_multi->sql_fetchassoc()){
					if(!isset($this->sku_items_info[$r['id']])){
						$tmp_info = array();
						$tmp_info['sku_item_code'] = $r['sku_item_code'];
						$tmp_info['description'] = $r['description'];
						$tmp_info['artno'] = $r['artno'];
						$tmp_info['mcode'] = $r['mcode'];
						$tmp_info['dept_desc'] = $r['dept_desc'];
						$tmp_info['link_code'] = $r['link_code'];
						$this->sku_items_info[$r['id']] = $tmp_info;
					}
					$group_data[$r['sku_id']]['parent'] = $r['id'];
				}
				$con_multi->sql_freeresult();
			}
		}
		
		$this->data = $data;
		$this->total = $total;
		$this->group_data = $group_data;
	}
	
	private function run_report($branch_id){
		global $con, $smarty, $sessioninfo, $con_multi;
		
		//$data = $this->data;
		//$total = $this->total;
		
		$start = $this->page_size_start;
		$size = $this->page_size;

		if($this->sort_by){
			$order = "order by $this->sort_by $this->sort_order";
		}
		
		$filter = array();
		/*if(!$this->show_all_sku){
			$filter[] = "(sb.qty<>0 or sc.qty<>0)";
		}*/
		if($this->sku_with!='show_all_sku'){
			if($this->sku_with=='sb')   $filter[] = "(sb.qty<>0 or sc.qty is not null)";
			elseif($this->sku_with=='selected_sku'){
				$code_list = $this->sku_code_list_2;
				$list = explode(",",$code_list);
				foreach($list as $lkey=>$lval) $list[$lkey] = "'$lval'";
				$list = join(',',$list);
				$filter[] = "si.sku_item_code in($list)";
			}
			else{
			    $filter[] = "sc.qty is not null";
				
				if(!$this->all_location){
					$filter[] = "sc.location between ".ms($this->location_from)." and ".ms($this->location_to);
				}

				if(!$this->all_shelf_no){
					if($this->stock_take_type == 1){ // imported stock take
						$shelf_name = "sc.shelf_no";
					}else{ // pre stock take
						$shelf_name = "sc.shelf";
					}
					$filter[] = $shelf_name." between ".ms($this->shelf_no_from)." and ".ms($this->shelf_no_to);
				}
			}
		}
		$filter[] = "c.department_id in (".join(',',array_keys($sessioninfo['departments'])).")";
		$filter[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
        if($this->sku_type) $filter[] = "sku.sku_type=".ms($this->sku_type);
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
		if($this->skip_zero_variance) $having = "having sc_qty-sb_qty != 0";
		if($this->show_auto_fill_zero_item) $filter[] = "sc.qty = 0 and (sc.location is null or sc.location='') and (sc.shelf_no is null or sc.shelf_no = '')";	
		if($this->vendor_id>0)	$filter[] = "sku.vendor_id=".mi($this->vendor_id);
		if($this->brand_id == -1){	// un-branded
			$filter[] = "sku.brand_id=0";
		}elseif($this->brand_id>0){
			$filter[] = "sku.brand_id=".mi($this->brand_id);
		}
		if($this->dept_id>0)	$filter[] = "c.department_id=".mi($this->dept_id);
		
		if($filter)	$filter = "where ".join(' and ', $filter);
		else 	$filter = '';
		
		$sb_tbl = "stock_balance_b".$branch_id."_".date('Y',strtotime($this->balance_date));

		// load data from stock_take_pre OR stock_check
		if($this->stock_take_type == 1){ // imported stock take
			$cols = "sum(sc.cost*sc.qty)/sum(sc.qty) as sc_cost, sc.selling as sc_selling_price, sum(sc.cost*sc.qty) as total_cost,";
			$st_lj = "left join stock_check sc on sc.sku_item_code=si.sku_item_code and sc.branch_id=$branch_id and sc.date=".ms($this->selected_date);
		}else{ // pre stock take
			$cols = "ifnull((select sich.grn_cost from sku_items_cost_history sich USE INDEX (PRIMARY) where sich.branch_id=$branch_id and sich.sku_item_id=si.id and sich.date<=".ms($this->selected_date)." order by date desc limit 1), si.cost_price) as sc_cost, sum(sc.cost_price*sc.qty) as total_cost,";
			$st_lj = "left join stock_take_pre sc on sc.sku_item_id=si.id and sc.branch_id=$branch_id and sc.date=".ms($this->selected_date);
		}
		
		$sql = "select ifnull(sb.qty,0) as sb_qty, sb.cost as sb_cost, sum(ifnull(sc.qty,0)) as sc_qty, sc.date as sc_date, $cols
		 si.id, si.description, si.artno, si.mcode, si.sku_id, dept.description as dept_desc,si.sku_item_code,si.link_code
from sku_items si 
left join sku on sku.id=si.sku_id
left join category c on c.id=sku.category_id
left join category dept on dept.id=c.department_id
left join category_cache cc on cc.category_id=sku.category_id
left join $sb_tbl sb on sb.sku_item_id=si.id and ((".ms($this->balance_date)." between sb.from_date and sb.to_date) or (".ms($this->balance_date).">=from_date and is_latest=1))
$st_lj
$filter group by si.id $having $order limit $start , $size";
		//print $sql;
		//$con->sql_query($sql) or die(mysql_error());
		$con_multi->sql_query($sql,false,false);//print "$sql<br /><br />";//xx
		$item_no_price = array();
		
		while($r = $con_multi->sql_fetchassoc()){
			$temp = array();
			$temp['stock_balance'] = $r['sb_qty'];
			$temp['stock_take_qty'] = $r['sc_qty'];
			$temp['selling_price'] = $r['sc_selling_price'];
			//$temp['cost'] = $r['sc_cost']? $r['sc_cost'] : $r['sb_cost'];
			$temp['cost'] = $r['sc_cost'];
			//$temp['total_cost'] = $r['total_cost'];
			$temp['total_cost'] = ($r['sc_qty']*$r['sc_cost'])-($r['sb_qty']*$r['sb_cost']);
			$temp['sc_date'] = $r['sc_date'];
			$temp['sku_item_id'] = $r['id'];
			$temp['dept_desc'] = $r['dept_desc'];
			//if(!$temp['cost'])	$temp['cost'] = $r['cost_price'];
			
			$data[$r['id']] = $temp;
			unset($temp);
			if(!isset($this->sku_items_info[$r['id']])){
				$tmp_info = array();
				$tmp_info['sku_item_code'] = $r['sku_item_code'];
				$tmp_info['description'] = $r['description'];
				$tmp_info['artno'] = $r['artno'];
				$tmp_info['mcode'] = $r['mcode'];
				$tmp_info['dept_desc'] = $r['dept_desc'];
				$tmp_info['link_code'] = $r['link_code'];
				$this->sku_items_info[$r['id']] = $tmp_info;
			}
			
			if(!$r['sc_selling_price'])	$item_no_price[] = $r['id'];
		}
		$con_multi->sql_freeresult();
		
		// get selling price
		if($item_no_price){
		    foreach($item_no_price as $sid){
                $con_multi->sql_query("select si.id, ifnull(siph.price, si.selling_price) as selling_price
				from sku_items si
				left join sku_items_price_history siph on si.id=siph.sku_item_id and siph.branch_id=$branch_id and siph.added<".ms($this->selected_date)."
				where si.id=".mi($sid)."
				order by siph.added desc limit 1
				");
				$temp = $con_multi->sql_fetchassoc();
				$con_multi->sql_freeresult();
				$data[$sid]['selling_price'] = $temp['selling_price'];
			}
			/*
			$sql = "select si.id,si.selling_price,(select max(added) from sku_items_price_history siph where siph.sku_item_id=si.id and siph.branch_id=$branch_id and added<".ms($this->selected_date).") as max_added,
	(select price from sku_items_price_history siph2 where siph2.sku_item_id=si.id and siph2.branch_id=$branch_id and siph2.added=max_added limit 1) as price_history
	from sku_items si where si.id in (".join(',',$item_no_price).")";
			$con_multi->sql_query($sql) or die(mysql_error());
			while($r = $con_multi->sql_fetchrow()){
				$data[$r['id']]['selling_price'] = $r['price_history']?$r['price_history']:$r['selling_price'];
			}
			$con_multi->sql_freeresult();*/
		}
		
		//print_r($data);
		$this->data = $data;
		$this->total = $total;
		
	}
	
	function generate_report()
	{
		global $con, $smarty,$sessioninfo, $con_multi, $config;
		
		$bid  = get_request_branch(true);
		
		if($bid==0) die("No Branch Selected");
		
		if (BRANCH_CODE != 'HQ'){
			$branch_name = BRANCH_CODE;
		}else{
			$branch_name = get_branch_code($bid);
		}

		if (isset($_REQUEST['output_excel'])){
		    include("include/excelwriter.php");
		    $smarty->assign('no_header_footer', true);
			$sb_tbl = "stock_balance_b".$bid."_".date('Y',strtotime($this->balance_date));
			
		    //Header('Content-Type: application/msexcel');
			if($this->group_by_sku){
				/*if(!$this->show_all_sku){
					//$having = "having (sb_qty<>0 or sc_qty<>0)";
				}*/
				if($this->sku_with!='show_all_sku'){
					if($this->sku_with=='sb'){
                        //$having = "having (sb_qty<>0 or sc_qty is not null)";
                        $filter[] = "(sb.qty<>0 or sc.qty is not null)";
					}
					elseif($this->sku_with=='selected_sku'){
						$code_list = $this->sku_code_list_2;
						$list = explode(",",$code_list);
						foreach($list as $lkey=>$lval) $list[$lkey] = "'$lval'";
						$list = join(',',$list);
						$filter[] = "si.sku_item_code in($list)";
					}
					else{
					    $filter[] = "sc.qty is not null";
					    //$having = "having sc_qty is not null";
						
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
					}    
				}
				$filter[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
				if($this->sku_type) $filter[] = "sku.sku_type=".ms($this->sku_type);
				$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
				if($this->vendor_id>0)	$filter[] = "sku.vendor_id=".mi($this->vendor_id);
				if($this->dept_id>0)	$filter[] = "c.department_id=".mi($this->dept_id);
				if($this->brand_id == -1){	// un-branded
					$filter[] = "sku.brand_id=0";
				}elseif($this->brand_id>0){
					$filter[] = "sku.brand_id=".mi($this->brand_id);
				}
				
				if($this->skip_zero_variance) $having = "having sc_qty-sb_qty != 0";
				if($this->show_auto_fill_zero_item) $filter[] = "sc.qty = 0 and (sc.location is null or sc.location='') and (sc.shelf_no is null or sc.shelf_no = '')";	
				if($filter)	$filter = "where ".join(' and ', $filter);
				else 	$filter = '';
				
				// load data from stock_take_pre OR stock_check
				if($this->stock_take_type == 1){ // imported stock take
					$st_lj = "left join stock_check sc on sc.sku_item_code=si.sku_item_code and sc.branch_id=$bid and sc.date=".ms($this->selected_date);
				}else{ // pre stock take
					$st_lj = "left join stock_take_pre sc on sc.sku_item_id=si.id and sc.branch_id=$bid and sc.date=".ms($this->selected_date);
				}
				
				$sql = "select sum(ifnull(sb.qty,0)) as sb_qty, sum(ifnull(sc.qty,0)) as sc_qty 
from sku_items si 
left join sku on sku.id=si.sku_id
left join category c on c.id=sku.category_id
left join category_cache cc on cc.category_id=sku.category_id
left join $sb_tbl sb on sb.sku_item_id=si.id and ((".ms($this->balance_date)." between sb.from_date and sb.to_date) or (".ms($this->balance_date).">=from_date and is_latest=1))
$st_lj $filter
 group by si.sku_id $having";
				//print $sql;
				
				//$con->sql_query($sql) or die(mysql_error());
				$con_multi->sql_query($sql,false,false);//print "$sql<br /><br />";//xx
				$total_rows = $con_multi->sql_numrows();
				$con_multi->sql_freeresult();
			}else{
				$filter = array();
				/*if(!$this->show_all_sku){
					$filter[] = "(sb.qty<>0 or sc.qty<>0)";
				}*/
				
				if($this->sku_with!='show_all_sku'){
					if($this->sku_with=='sb')   $filter[] = "(sb.qty<>0 or sc.qty is not null)";
					elseif($this->sku_with=='selected_sku'){
						$code_list = $this->sku_code_list_2;
						$list = explode(",",$code_list);
						foreach($list as $lkey=>$lval) $list[$lkey] = "'$lval'";
						$list = join(',',$list);
						$filter[] = "si.sku_item_code in($list)";
					}
					else{
                        $filter[] = "sc.qty is not null";
						
						if(!$this->all_location){
							$filter[] = "sc.location between ".ms($this->location_from)." and ".ms($this->location_to);
						}
						
                        if(!$this->all_shelf_no){
							if($this->stock_take_type == 1){ // imported stock take
								$shelf_name = "sc.shelf_no";
							}else{ // pre stock take
								$shelf_name = "sc.shelf";
							}
							$filter[] = $shelf_name." between ".ms($this->shelf_no_from)." and ".ms($this->shelf_no_to);
						}
					}    
				}
				$filter[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
				if($this->sku_type) $filter[] = "sku.sku_type=".ms($this->sku_type);
				$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
				if($this->vendor_id>0)	$filter[] = "sku.vendor_id=".mi($this->vendor_id);		
				if($this->brand_id == -1){	// un-branded
					$filter[] = "sku.brand_id=0";
				}elseif($this->brand_id>0){
					$filter[] = "sku.brand_id=".mi($this->brand_id);
				}
				if($this->dept_id>0)	$filter[] = "c.department_id=".mi($this->dept_id);
				if($this->show_auto_fill_zero_item) $filter[] = "sc.qty = 0 and (sc.location is null or sc.location='') and (sc.shelf_no is null or sc.shelf_no = '')";	
				if($filter)	$filter = "where ".join(' and ', $filter);
				else 	$filter = '';
				if($this->skip_zero_variance) $having = "having sc_qty-sb_qty != 0";
				
				// load data from stock_take_pre OR stock_check
				if($this->stock_take_type == 1){ // imported stock take
					$st_lj = "left join stock_check sc on sc.sku_item_code=si.sku_item_code and sc.branch_id=$bid and sc.date=".ms($this->selected_date);
				}else{ // pre stock take
					$st_lj = "left join stock_take_pre sc on sc.sku_item_id=si.id and sc.branch_id=$bid and sc.date=".ms($this->selected_date);
				}
				
				$sql = "select count(*), sum(ifnull(sb.qty,0)) as sb_qty,sum(ifnull(sc.qty,0)) as sc_qty 
from sku_items si 
left join sku on sku.id=si.sku_id
left join category c on c.id=sku.category_id
left join category_cache cc on cc.category_id=sku.category_id
left join $sb_tbl sb on sb.sku_item_id=si.id and ((".ms($this->balance_date)." between sb.from_date and sb.to_date) or (".ms($this->balance_date).">=from_date and is_latest=1))
$st_lj
$filter group by si.id $having";
				//print $sql;
				//$con->sql_query($sql) or die(mysql_error());
				$con_multi->sql_query($sql,false,false);//print "$sql<br /><br />";//xx
				$total_rows = $con_multi->sql_numrows();
				$con_multi->sql_freeresult();
			}
	        
	        $start = $this->page_size_start;
	        $page_size = $this->page_size;
	        $total_pages = ceil($total_rows/$page_size);
	        
		    $tmpname = "Stock_Take_Variance_RPT_".time();

			for($i=0; $i<$total_pages; $i++){
				//print sprintf('%012d',memory_get_usage())." memomry start of page $i <br>";
			    $output = "/tmp/{$tmpname}_$i.xls";
				// reset data
			    $_REQUEST['selected_page'] = $i;
			    $this->selected_page = $i;
			    $this->page_size_start = $i*$this->page_size;
				//print sprintf('%012d',memory_get_usage())." memomry before run report<br>";
				if($this->group_by_sku)	$this->run_group_report($bid);
                else 	$this->run_report($bid);
				//print sprintf('%012d',memory_get_usage())." memomry after run report<br>";
                $this->calc_data();
				//print sprintf('%012d',memory_get_usage())." memomry after calc data<br>";

                $smarty->assign('branch_name',$branch_name);
                $smarty->assign('item_counter',$this->page_size_start);
				$smarty->assign('table',$this->data);
				$smarty->assign('table2',$this->data2);
				$smarty->assign('total',$this->total);
				$smarty->assign('sku_items_info',$this->sku_items_info);
				$smarty->assign('group_data',$this->group_data);

                $body = $this->GetMslHeader().$smarty->fetch($this->template).ExcelWriter::GetFooter();
				//print $body;
				//print sprintf('%012d',memory_get_usage())." memomry after fetch body<br>";
				//$body = $this->GetMslHeader()."test".ExcelWriter::GetFooter();
                file_put_contents($output, $body);
				unset($body);
				unset($this->data);
				unset($this->data2);
				unset($this->total);
				unset($this->sku_items_info);
				unset($this->group_data);
				//print sprintf('%012d',memory_get_usage())." memomry after end page $i<br>";
			}
			//die('Finish!');
			exec("cd /tmp; zip -9 $tmpname.zip $tmpname*.xls");
			//ob_end_clean();
			log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export $_REQUEST[title] To Excel($_REQUEST[report_title])");
			
			header("Content-type: application/zip");
			header("Content-Disposition: attachment; filename=$tmpname.zip");
			readfile("/tmp/$tmpname.zip");
			exit;
		}else{
			$sb_tbl = "stock_balance_b".$bid."_".date('Y',strtotime($this->balance_date));
			
			if($this->group_by_sku){
				/*if(!$this->show_all_sku){
					$having = "having (sb_qty<>0 or sc_qty<>0)";
				}*/
				if($this->sku_with!='show_all_sku'){
					if($this->sku_with=='sb'){
                        //$having = "having (sb_qty<>0 or sc_qty is not null)";
                        $filter[] = "(sb.qty<>0 or sc.qty is not null)";
					}
					elseif($this->sku_with=='selected_sku'){
						$code_list = $this->sku_code_list_2;
						$list = explode(",",$code_list);
						foreach($list as $lkey=>$lval) $list[$lkey] = "'$lval'";
						$list = join(',',$list);
						$filter[] = "si.sku_item_code in($list)";
					}
					else{
					    $filter[] = "sc.qty is not null";
                        //$having = "having sc_qty is not null";
						
						if(!$this->all_location){
							$filter[] = "sc.location between ".ms($this->location_from)." and ".ms($this->location_to);
						}
						
                        if(!$this->all_shelf_no){
							if($this->stock_take_type == 1){ // imported stock take
								$shelf_name = "sc.shelf_no";
							}else{ // pre stock take
								$shelf_name = "sc.shelf";
							}
							$filter[] = $shelf_name." between ".ms($this->shelf_no_from)." and ".ms($this->shelf_no_to);
						}
					}    
				}
				$filter[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
				if($this->sku_type) $filter[] = "sku.sku_type=".ms($this->sku_type);
				$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
				if($this->vendor_id>0)	$filter[] = "sku.vendor_id=".mi($this->vendor_id);
				if($this->dept_id>0)	$filter[] = "c.department_id=".mi($this->dept_id);
				if($this->brand_id == -1){	// un-branded
					$filter[] = "sku.brand_id=0";
				}elseif($this->brand_id>0){
					$filter[] = "sku.brand_id=".mi($this->brand_id);
				}	
				if($this->show_auto_fill_zero_item) $filter[] = "sc.qty = 0 and (sc.location is null or sc.location='') and (sc.shelf_no is null or sc.shelf_no = '')";	
				if($filter)	$filter = "where ".join(' and ', $filter);
				else 	$filter = '';
				if($this->skip_zero_variance) $having = "having sc_qty-sb_qty != 0";

				// load data from stock_take_pre OR stock_check
				if($this->stock_take_type == 1){ // imported stock take
					$st_lj = "left join stock_check sc on sc.sku_item_code=si.sku_item_code and sc.branch_id=$bid and sc.date=".ms($this->selected_date);
				}else{ // pre stock take
					$st_lj = "left join stock_take_pre sc on sc.sku_item_id=si.id and sc.branch_id=$bid and sc.date=".ms($this->selected_date);
				}
				
				$sql = "select sum(ifnull(sb.qty,0)) as sb_qty,sum(ifnull(sc.qty,0)) as sc_qty 
from sku_items si 
left join sku on sku.id=si.sku_id
left join category c on c.id=sku.category_id
left join category_cache cc on cc.category_id=sku.category_id
left join $sb_tbl sb on sb.sku_item_id=si.id and ((".ms($this->balance_date)." between sb.from_date and sb.to_date) or (".ms($this->balance_date).">=from_date and is_latest=1))
$st_lj $filter
 group by si.sku_id $having";
				//print $sql;
				
				//$con_multi->sql_query($sql) or die(mysql_error());
				$con_multi->sql_query($sql,false,false);//print "$sql<br /><br />";//xx
				$total_rows = $con_multi->sql_numrows();
				$con_multi->sql_freeresult();
			}else{				
				$filter = array();
				/*if(!$this->show_all_sku){
					$filter[] = "(sb.qty<>0 or sc.qty<>0)";
				}*/
				if($this->sku_with!='show_all_sku'){
					if($this->sku_with=='sb')   $filter[] = "(sb.qty<>0 or sc.qty is not null)";
					elseif($this->sku_with=='selected_sku'){
						$code_list = $this->sku_code_list_2;
						$list = explode(",",$code_list);
						foreach($list as $lkey=>$lval) $list[$lkey] = "'$lval'";
						$list = join(',',$list);
						$filter[] = "si.sku_item_code in($list)";
					}
					else{
					    $filter[] = "sc.qty is not null";
						
						if(!$this->all_location){
							$filter[] = "sc.location between ".ms($this->location_from)." and ".ms($this->location_to);
						}
						
                        if(!$this->all_shelf_no){
							if($this->stock_take_type == 1){ // imported stock take
								$shelf_name = "sc.shelf_no";
							}else{ // pre stock take
								$shelf_name = "sc.shelf";
							}
							$filter[] = $shelf_name." between ".ms($this->shelf_no_from)." and ".ms($this->shelf_no_to);
						}
					}    
				}
				$filter[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
				if($this->sku_type) $filter[] = "sku.sku_type=".ms($this->sku_type);
				$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
				
				if($this->vendor_id>0)	$filter[] = "sku.vendor_id=".mi($this->vendor_id);
				if($this->brand_id == -1){	// un-branded
					$filter[] = "sku.brand_id=0";
				}elseif($this->brand_id>0){
					$filter[] = "sku.brand_id=".mi($this->brand_id);
				}
				if($this->dept_id>0)	$filter[] = "c.department_id=".mi($this->dept_id);
				if($this->show_auto_fill_zero_item) $filter[] = "sc.qty = 0 and (sc.location is null or sc.location='') and (sc.shelf_no is null or sc.shelf_no = '')";	
				if($filter)	$filter = "where ".join(' and ', $filter);
				else 	$filter = '';
				if($this->skip_zero_variance) $having = "having sc_qty-sb_qty != 0";
				
				// load data from stock_take_pre OR stock_check
				if($this->stock_take_type == 1){ // imported stock take
					$st_lj = "left join stock_check sc on sc.sku_item_code=si.sku_item_code and sc.branch_id=$bid and sc.date=".ms($this->selected_date);
				}else{ // pre stock take
					$st_lj = "left join stock_take_pre sc on sc.sku_item_id=si.id and sc.branch_id=$bid and sc.date=".ms($this->selected_date);
				}
				
				$sql = "select count(*), sum(ifnull(sb.qty,0)) as sb_qty, sum(ifnull(sc.qty,0)) as sc_qty 
from sku_items si 
left join sku on sku.id=si.sku_id
left join category c on c.id=sku.category_id
left join category_cache cc on cc.category_id=sku.category_id
left join $sb_tbl sb on sb.sku_item_id=si.id and ((".ms($this->balance_date)." between sb.from_date and sb.to_date) or (".ms($this->balance_date).">=from_date and is_latest=1))
$st_lj
$filter group by si.id $having";
				//print $sql;die();
				//$con->sql_query($sql) or die(mysql_error());
				$con_multi->sql_query($sql,false,false);//print "$sql<br /><br />";//xx
				$total_rows = $con_multi->sql_numrows();
				$con_multi->sql_freeresult();
			}

	        $start = $this->page_size_start;
	        $page_size = $this->page_size;
	        $total_pages = ceil($total_rows/$page_size);

	        if ($total_rows > $page_size){
			    if ($start > $total_rows) $start = 0;

				$smarty->assign("total_rows", $total_rows);
				$smarty->assign("total_pages", $total_pages);
				$smarty->assign('item_counter',$start);
			}
			
			if($this->group_by_sku)	$this->run_group_report($bid);
			else 	$this->run_report($bid);
		}
		
		$this->calc_data();
		
		//if($this->sort_by&&$this->sort_order)   $this->sort_data();
		//$date_list = $this->load_stock_check_date_by_branch($bid);

		if($this->sku_with=='sc'&&$this->selected_date){
            /*$con_multi->sql_query("select distinct(shelf_no) as shelf_no from stock_check where date=".ms($this->selected_date)." and branch_id=$bid");
			$smarty->assign('shelf_no_list',$con_multi->sql_fetchrowset());*/
			
			$prms = array();
			$prms['stop_load_tpl'] = true;
			$prms['branch_id'] = $bid;
			$prms['date'] = $this->selected_date;
			$prms['stock_take_type'] = $this->stock_take_type;
			$this->load_location($prms);
			
			$prms['all_loc'] = $this->all_location;
			$prms['loc_from'] = $this->location_from;
			$prms['loc_to'] = $this->location_to;
			$this->load_shelf_no($prms);
			
			unset($prms);
		}
		
		//print_r($this->data);
		//print_r($this->total);
		//print_r($this->sku_items_info);
		$q1 = $con_multi->sql_query("select * from branch where active=1 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc($q1)){
			if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
			$branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);
		
		if($_REQUEST['branch_id']) $str_branch = $branches[$_REQUEST['branch_id']]['code'];
		else $str_branch = $branch_name;
		
		$code_list = $_REQUEST['sku_code_list_2'];
	    $list = explode(",",$code_list);
	    for($i=0; $i<count($list); $i++){
	        $con_multi->sql_query("select description from sku_items where sku_item_code=".ms($list[$i])) or die(sql_error());
	        $temp = $con_multi->sql_fetchrow();
	        $category[$list[$i]]['sku_item_code']=$list[$i];
	        $category[$list[$i]]['description']=$temp['description'];
			$list[$i]="'".$list[$i]."'";
			$con_multi->sql_freeresult();
		}
		$smarty->assign('category',$category);
		
		$report_title = array();
		$report_title[] = "Branch: ".$str_branch;
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
    
		//print_r($this->group_data);
		$smarty->assign("report_title", join("&nbsp;&nbsp;&nbsp;&nbsp;", $report_title));
		$smarty->assign('table',$this->data);
		$smarty->assign('table2',$this->data2);
		$smarty->assign('total',$this->total);
		$smarty->assign('sku_items_info',$this->sku_items_info);
		$smarty->assign('group_data',$this->group_data);
		$smarty->assign('date_list',$date_list);
		
		$this->load_date(true);
		$this->load_pre_date(true);
		
		/*
		echo '<pre>';
		print_r($this->data);
		echo '</pre>';
		*/
		
	}
	
	function process_form()
	{
	    global $config,$con,$smarty,$sessioninfo;
		// do my own form process
		if ($_REQUEST['sku_with'] != 'selected_sku') unset($_REQUEST['sku_code_list_2']);
		else $this->sku_code_list_2 = $_REQUEST['sku_code_list_2'];

		// call parent
		parent::process_form();
		
		$this->stock_take_type = mi($_REQUEST['stock_take_type']);
		if($this->stock_take_type == 1){ // getting imported stock take
			$this->selected_date = $_REQUEST['date'];
		}else{ // getting pre stock take
			$this->selected_date = $_REQUEST['pre_date'];
		}
		$this->balance_date = date('Y-m-d',strtotime('-1 day',strtotime($this->selected_date)));
		
		$this->sort_by = $_REQUEST['sort_by'];
		$this->group_by_sku = $_REQUEST['group_by_sku'];
		if($this->group_by_sku) $this->page_size = mi($this->page_size/2);
		
		$this->selected_page = mi($_REQUEST['selected_page']);
		$this->page_size_start = $this->selected_page * $this->page_size;
		
		//$this->show_all_sku = $_REQUEST['show_all_sku'];
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
		
		$this->vendor_id = mi($_REQUEST['vendor_id']);
		$this->brand_id = mi($_REQUEST['brand_id']);
		$this->dept_id = mi($_REQUEST['dept_id']);
		$this->show_auto_fill_zero_item = mi($_REQUEST['show_auto_fill_zero_item']);
	}

	function default_values()
	{
		if(!$_REQUEST['date']) $_REQUEST['date'] = date("Y-m-d");
		if(!$_REQUEST['pre_date']) $_REQUEST['pre_date'] = date("Y-m-d");
		
		if((BRANCH_CODE == "HQ" && $_REQUEST['branch_id']) || BRANCH_CODE != "HQ"){
			$this->load_date(true);
			$this->load_pre_date(true);
		}
	}
	
	
	private function calc_data(){
	    global $con, $smarty, $sessioninfo;

		$data = $this->data;
		$total = $this->total;
		$count = 0;
        if($data){
		    foreach($data as $sid=>$r){
				//$data[$sid]['open_bal'] = $r['cost_history']+$r['grn']+$r['adj']-$r['do']-$r['gra']-$r['pos'];
				//$balance = $r['cost_history']+$r['adj']-$r['do']-$r['gra']-$r['pos'];
				$balance = $r['stock_balance'];
				if($r["sc_date"]){
					$variance = $r['stock_take_qty'] - $balance;
					$price_variance = $variance * $r['selling_price'];

					//$data[$sid]['balance'] += $balance;
					$data[$sid]['variance'] += $variance;
					$data[$sid]['price_variance'] += $price_variance;
					
					/*if($sessioninfo['u']=='wsatp'){
						$con->sql_query("create table if not exists tmp_stk_variance_compare(
							sku_item_id int primary key,
							no_group_price_variance double,
							group_price_variance double
						)");
						
						$con->sql_query("select * from tmp_stk_variance_compare where sku_item_id=$sid");
						$tmp = $con->sql_fetchassoc();
						$con->sql_freeresult();
						
						$upd = array();
						if($this->group_by_sku){
							$upd['group_price_variance'] = $data[$sid]['price_variance'];
						}else{
							$upd['no_group_price_variance'] = $data[$sid]['price_variance'];
						}
						if($tmp){
							$con->sql_query("update tmp_stk_variance_compare set ".mysql_update_by_field($upd)." where sku_item_id=$sid");
						}else{
							$upd['sku_item_id'] = $sid;
							$con->sql_query("insert into tmp_stk_variance_compare ".mysql_insert_by_field($upd));
						}
					}*/
					
					$total['price_variance'] += $price_variance;
					$total['total_cost'] += $r['total_cost'];
	//				$total['total_cost'] += $variance*$data[$sid]['cost'];
					$total['stock_take_qty'] += $r['stock_take_qty'];
					$total['variance'] += $variance;
					$count++;
				}
				$total['balance'] += $balance;
			}
		}
		
		if($count == 0){
			$smarty->assign("is_available", 0);
		}else{
			$smarty->assign("is_available", 1);
		}
		$this->data = $data;
		$this->total = $total;
		$this->data2 = $this->data;
	}
	
	private function sort_data(){
		$data = $this->data;
		$group_by_sku = $this->group_by_sku;
		$sku_items_info = $this->sku_items_info;
		
		if($group_by_sku){
            if(!$sku_items_info) return;
            // sort parent
            usort($sku_items_info, array($this,"sort_group_table"));
            // sort child
            foreach($sku_items_info as $sku_id=>$pc){
                if(!$pc['child'])    continue;
                usort($sku_items_info[$sku_id]['child'], array($this,"sort_table"));
			}
		}else{
            if(!$data) return;
			usort($data, array($this,"sort_table"));
		}
		
		
		$this->data = $data;
		$this->sku_items_info = $sku_items_info;
	}
	
	private function sort_table($a,$b)
	{
		$col = $this->sort_by;
		$order = $this->sort_order;
		
	    if ($a[$col]==$b[$col]) return 0;
	    elseif($order=='desc')  return ($a[$col]>$b[$col]) ? -1 : 1;
	    else	return ($a[$col]>$b[$col]) ? 1 : -1;
	}
	
	private function sort_group_table($a,$b){
        $col = $this->sort_by;
		$order = $this->sort_order;
		
		if ($a['parent'][$col]==$b['parent'][$col]) return 0;
	    elseif($order=='desc')  return ($a['parent'][$col]>$b['parent'][$col]) ? -1 : 1;
	    else	return ($a['parent'][$col]>$b['parent'][$col]) ? 1 : -1;
	}
	
	/*
	// 12/11/2018 9:46 AM - disabled by Justin since we had changed it to use Real & Pre Stock Take
	private function load_stock_check_date_by_branch($branch_id){
        global $con_multi;
        $sql = "select distinct(date) as date from stock_check where branch_id=$branch_id order by date desc";
        $con_multi->sql_query($sql) or die(mysql_error());
        return  $con_multi->sql_fetchrowset();
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
	}*/
	
	function GetMslHeader(){
		$header = <<<EOH
			<html xmlns:o="urn:schemas-microsoft-com:office:office"
			xmlns:x="urn:schemas-microsoft-com:office:excel"
			xmlns="http://www.w3.org/TR/REC-html40">

			<head>
			<meta name=ProgId content=Excel.Sheet>
			<!--[if gte mso 9]><xml>
			 <o:DocumentProperties>
			  <o:LastAuthor>ARMS</o:LastAuthor>
			  <o:LastSaved>2005-01-02T07:46:23Z</o:LastSaved>
			  <o:Version>10.2625</o:Version>
			 </o:DocumentProperties>
			 <o:OfficeDocumentSettings>
			  <o:DownloadComponents/>
			 </o:OfficeDocumentSettings>
			</xml><![endif]-->
			<style>
			<!--table
				{mso-displayed-decimal-separator:"\.";
				mso-displayed-thousand-separator:"\,";}
			@page
				{margin:1.0in .75in 1.0in .75in;
				mso-header-margin:.5in;
				mso-footer-margin:.5in;}
			tr
				{mso-height-source:auto;}
			col
				{mso-width-source:auto;}
			br
				{mso-data-placement:same-cell;}
			.style0
				{mso-number-format:General;
				text-align:general;
				vertical-align:bottom;
				white-space:nowrap;
				mso-rotate:0;
				mso-background-source:auto;
				mso-pattern:auto;
				color:windowtext;
				font-size:10.0pt;
				font-weight:400;
				font-style:normal;
				text-decoration:none;
				font-family:Arial;
				mso-generic-font-family:auto;
				mso-font-charset:0;
				border:none;
				mso-protection:locked visible;
				mso-style-name:Normal;
				mso-style-id:0;}
			td
				{mso-style-parent:style0;
				padding-top:1px;
				padding-right:1px;
				padding-left:1px;
				mso-ignore:padding;
				color:windowtext;
				font-size:10.0pt;
				font-weight:400;
				font-style:normal;
				text-decoration:none;
				font-family:Arial;
				mso-generic-font-family:auto;
				mso-font-charset:0;
				mso-number-format:General;
				text-align:general;
				vertical-align:bottom;
				border:0.1pt solid black;
				mso-background-source:auto;
				mso-pattern:auto;
				mso-protection:locked visible;
				white-space:nowrap;
				mso-rotate:0;}
			.xl24
				{mso-style-parent:style0;
				white-space:normal;}
			-->
			</style>
			<!--[if gte mso 9]><xml>
			 <x:ExcelWorkbook>
			  <x:ExcelWorksheets>
			   <x:ExcelWorksheet>
				<x:Name>$sheetname</x:Name>
				<x:WorksheetOptions>
				 <x:Selected/>
				 <x:ProtectContents>False</x:ProtectContents>
				 <x:ProtectObjects>False</x:ProtectObjects>
				 <x:ProtectScenarios>False</x:ProtectScenarios>
				</x:WorksheetOptions>
			   </x:ExcelWorksheet>
			  </x:ExcelWorksheets>
			  <x:WindowHeight>10005</x:WindowHeight>
			  <x:WindowWidth>10005</x:WindowWidth>
			  <x:WindowTopX>120</x:WindowTopX>
			  <x:WindowTopY>135</x:WindowTopY>
			  <x:ProtectStructure>False</x:ProtectStructure>
			  <x:ProtectWindows>False</x:ProtectWindows>
			 </x:ExcelWorkbook>
			</xml><![endif]-->
			</head>

			<body link=blue vlink=purple>
EOH;
//<table x:str border=0 cellpadding=0 cellspacing=0 style='border-collapse: collapse;table-layout:fixed;'>
		return $header;
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
			// stock_take_type = 1 > imported stock take data
			// stock_take_type = 2 > pre stock take data
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
		
		$branch_id = mi($_REQUEST['branch_id']);
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
		
		// stock_take_type = 1 > imported stock take data
		// stock_take_type = 2 > pre stock take data
		if($stock_take_type == 1) $sql="select distinct(shelf_no) as shelf_no from stock_check where ".join(" and ", $filters)." order by shelf_no";
		else $sql="select distinct(shelf) as shelf_no from stock_take_pre where ".join(" and ", $filters)." order by shelf_no";
		
		$con_multi->sql_query($sql);
		$smarty->assign('shelf_no_list',$con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
		
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

$report = new StockTakeVariance('Stock Take Variance Report');
//$con_multi->close_connection();
?>
