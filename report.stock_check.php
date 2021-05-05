<?php
/*
2/16/2009 2:40:54 PM yinsee (request by tommy pkt)
- user 9999 can enter with URL 

11/24/2009 3:57:51 PM Andy
- Fix if login at branch no auto load date selection

7/23/2010 12:16:57 PM Andy
- Change shelf no dropdown to lod based on branch and date selection, not always load all.

9/15/2010 12:48:29 PM Alex
- sort report stock check follow by scanned items sequence

1/21/2011 3:23:55 PM Alex
- change use report_server

6/27/2011 10:01:02 AM Andy
- Make all branch default sort by sequence, code.

12/2/2011 11:06:34 AM Andy
- Fix some branch cannot be show in report if stock check qty is zero.

4/12/2012 5:04:16 PM Andy
- Add to filter only active SKU.  

5/9/2012 2:30:45 PM Justin
- Added new filter "group by item".
- Added new function to include and show different results while having/without group by item filter.
- Added to pickup report title.

10/1/2012 11:38 AM Justin
- Enhanced to have active filter by drop down list.

10/23/2012 9:53:00 AM Fithri
- add filter to search by SKU

10/29/2012 4:05:00 PM Fithri
- add "All" option/checkbox for shelf from/to

2/4/2013 5:34 PM Justin
- Enhanced to show and filter branches from regions or branch group base on user's regions.

11/15/2017 5:18 PM Justin
- Enhanced to show cost decimal points base on config set.

11/16/2017 1:52 PM Justin
- Bug fixed on gross profit calculated incorrectly.

10/1/2018 9:17 AM Justin
- Enhanced to have "Location" filter.
- Enhanced to load location and shelf info using TPL instead of print out from PHP.

3/20/2019 9:22 AM Andy
- Reconstruct Stock Take Summary Report to use Module framework.
- Enhanced to have Brand, Vendor and Department filter.

4/9/2019 5:37 PM Andy
- Fixed brand permission filter error.

5/10/2019 10:00 AM William
- Bug fixed on detail table broken.
- Enhanced view type detail and summary can filter by "Group Type".

2/17/2020 4:44 PM William
- Enhanced to change $con connection to use $con_multi.

12/03/2020 2:55 PM Rayleen
- Add link_code in select query
*/

include("include/common.php");
//print_r($_REQUEST);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level']<9999 && !privilege('STOCK_CHECK_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_CHECK_REPORT', BRANCH_CODE), "/index.php");
$maintenance->check(1);

class STOCK_TAKE_SUMMARY extends Module{
	var $branch_list = array();
	var $brands_list = array();
	var $vendors_list = array();
	var $dept_list = array();
	var $type = 'summary';

		
	function __construct($title){
		global $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		//$con_multi = new mysql_multi();
		
		$this->init_data();
		
		parent::__construct($title);
	}
	
	function init_data(){
		global $con_multi, $smarty, $config, $sessioninfo;
		
		// Branch
		$q1 = $con_multi->sql_query("select distinct branch.id,branch.code from stock_check left join branch on branch_id = branch.id where qty>0 and branch.id > 0 order by branch.sequence, branch.code");
		while($r = $con_multi->sql_fetchassoc($q1)){
			if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
			$this->branch_list[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);
		$smarty->assign('branch_list', $this->branch_list);
		
		// Brand
		$filter_brand = $this->brands_list = array();
		$filter_brand[] = "active=1";
		if($sessioninfo['brand_ids']){
			$filter_brand[] = "id in ($sessioninfo[brand_ids])";
		}
		$str_filter = join(' and ', $filter_brand);
		$sql = "select * from brand where $str_filter order by description";
		$con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc()){
			$this->brands_list[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign("brands_list", $this->brands_list);
		
		// Vendor
		if($sessioninfo['vendor_ids']){
			$filter_vendor = " and id in ($sessioninfo[vendor_ids])";
		}
		$con_multi->sql_query("select id,description from vendor where active=1 $filter_vendor order by description") or die(mysql_error());
		while($r = $con_multi->sql_fetchassoc()){
			$this->vendors_list[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign("vendor", $this->vendors_list);
		
		// Department
		$con_multi->sql_query("select * from category where id in ($sessioninfo[department_ids]) and active=1 order by description");
		while($r = $con_multi->sql_fetchassoc()){
			$this->dept_list[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign("departments", $this->dept_list);
	}
	
	function _default(){
		global $smarty;
		
		if($_REQUEST['submit']=="1"||BRANCH_CODE!='HQ')
		{
			$this->ajax_load(false);
			$this->load_location(false);
			$this->load_shelf_no(false);
			$success = $this->load_report();
			if($success){
				if (isset($_REQUEST['output_excel'])){
					include("include/excelwriter.php");
					$smarty->assign('no_header_footer', true);
				  
					log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export $_REQUEST[title] To Excel($_REQUEST[report_title])");
				  
					Header('Content-Type: application/msexcel');
					Header('Content-Disposition: attachment;filename=arms'.time().'.xls');

					print ExcelWriter::GetHeader();
					$smarty->display("report.stock_check.tpl");
					print ExcelWriter::GetFooter();
					exit;
				}
			}
		}

		$this->display();
	}
	
	function ajax_load($show_template = true){
		global $con_multi, $smarty;
    
		if($_REQUEST['ajax'])   $branch_id = mi($_REQUEST['branch_id']);
		else	$branch_id = get_request_branch();
		
		if($branch_id)
		{
			$rs = $con_multi->sql_query("select distinct date from stock_check where qty>0 and branch_id = ".ms($branch_id)." order by date desc");
			while ($r = $con_multi->sql_fetchrow($rs))
			{
				$dat[]=$r;
			}	
			$con_multi->sql_freeresult($rs);
			$smarty->assign('date',$dat);
		}
		
		if($show_template)
		{
			print "<b>Date</b> ";
			print "<select name='date' onChange='date_changed();'>";
			print "<option value=''>-- Please Select --</option>";
			foreach($dat as $val)
			{
				print "<option value=\"$val[date]\"";
				if ($_REQUEST['date']  == $val['date'])
				{
					 print "selected";
				}
				print ">".$val['date']."</option>";
			}
			print "</select>";
			exit;
		}
	}
	
	function load_location($show_template = true){
		global $con_multi, $smarty;

		if($_REQUEST['ajax'])   $branch_id = mi($_REQUEST['branch_id']);
		else	$branch_id = get_request_branch();
		$date = $_REQUEST['date'];
		
		if($branch_id && $date){
			$location_list = array();
			$rs = $con_multi->sql_query("select distinct location from stock_check where qty>=0 and branch_id=".mi($branch_id)." and date=".ms($date)." order by location");
			while ($r2 = $con_multi->sql_fetchassoc($rs))
			{
				$location_list[]=$r2;
			}
			$con_multi->sql_freeresult($rs);
			$smarty->assign('location_list',$location_list);
		}

		if($show_template){
			$smarty->display('report.stock_take_variance.location.tpl');
			exit;
		}
	}
	
	function load_shelf_no($show_template = true){
		global $con_multi, $smarty;

		if($_REQUEST['ajax'])   $branch_id = mi($_REQUEST['branch_id']);
		else	$branch_id = get_request_branch();
		$date = $_REQUEST['date'];
		$all_location = $_REQUEST['all_location'];
		$location_from = $_REQUEST['location_from'];
		$location_to = $_REQUEST['location_to'];
		
		$filters = array();
		$filters[] = "qty >= 0";
		$filters[] = "branch_id = ".mi($branch_id);
		$filters[] = "date = ".ms($date);
		
		if(!$all_location){
			$filters[] = "location between ".ms($location_from)." and ".ms($location_to);
		}
		
		if($branch_id && $date){
			$shelf_no_list = array();
			$rs = $con_multi->sql_query("select distinct shelf_no from stock_check where ".join(" and ", $filters)." order by shelf_no");
			while ($r2 = $con_multi->sql_fetchassoc($rs))
			{
				$shelf_no_list[]=$r2;
			}
			$con_multi->sql_freeresult($rs);
			$smarty->assign('shelf_no_list',$shelf_no_list);
		}

		if($show_template){
			$smarty->display('report.stock_take_variance.shelf_no.tpl');
			exit;
		}
	}
	
	private function load_report(){
		global $con, $con_multi, $config, $smarty, $sessioninfo;
		
		$filter = array("stock_check.qty>0");

		if (!$_REQUEST['all_location']) $filter[] = "stock_check.location between ".ms($_REQUEST['location_from']) . " and " . ms($_REQUEST['location_to']);
		if (!$_REQUEST['all_shelf_no']) $filter[] = "stock_check.shelf_no between ".ms($_REQUEST['shelf_no_from']) . " and " . ms($_REQUEST['shelf_no_to']);
		$bid = get_request_branch(); if ($bid) $filter[] = "stock_check.branch_id = $bid";
		if (isset($_REQUEST['date'])) $filter[] = "stock_check.date = ".ms($_REQUEST['date']);
		if ($_REQUEST['active']){
			$active = 0;
			if($_REQUEST['active'] != 2) $active = $_REQUEST['active'];
			$filter[] = "sku_items.active=".mi($active);
		}
		
		if (!empty($_REQUEST['sku_code_list_2'])) {
			//var_dump($_REQUEST['sku_code_list_2']);
			$code_list = $_REQUEST['sku_code_list_2'];
			$list = explode(",",$code_list);
			foreach($list as $lkey=>$lval) $list[$lkey] = "'$lval'";
			$list = join(',',$list);
			$filter[] = "stock_check.sku_item_code in ($list)";
		}
		
		// Vendor
		$vendor_id = mi($_REQUEST['vendor_id']);
		if($vendor_id>0)	$filter[] = "sku.vendor_id=$vendor_id";
		
		// Brand
		$brand_id = mi($_REQUEST['brand_id']);
		if($brand_id == -1){
			$filter[] = "sku.brand_id=0";
		}elseif($brand_id>0)	$filter[] = "sku.brand_id=$brand_id";
		
		//error display
		$error = array();
		if(BRANCH_CODE=='HQ'){
			if(!$_REQUEST['branch_id']&&$_REQUEST['branch_id']=='') $error[] ="Please Select Branch";
		}
		if($_REQUEST['submit']){
			if(!$_REQUEST['date']) $error[] ="Please Select Date";
			if(!$_REQUEST['check_department_list']&&!$_REQUEST['all_department']) $error[] ="Please Select Department";
		}
		
		// Department multi select
		$check_department_list = $_REQUEST['check_department_list'];
		if($check_department_list){
			if(count($check_department_list) > 1){
				$dept_ids = implode(",",$check_department_list);
				$filter[] = "category.department_id in($dept_ids)";
			}else{
				$dept_ids = implode("",$check_department_list);
				$filter[] = "category.department_id=$dept_ids";
			}
		}
		
		$filter = join(" and ", $filter);
		if($_REQUEST['type']){
			$type= $_REQUEST['type'];
		}	
		if ($filter){
			if ($type == 'summary'){
				if($_REQUEST['group_type'] == 'bydepartment'){
					$sql = $con_multi->sql_query($abc="select stock_check.shelf_no, stock_check.scanned_by, stock_check.date, 
												  dept.description as dept, stock_check.location, sum(qty) as qty, 
												  sum(qty*cost) as tcost, sum(qty*selling) as tsell
												  from stock_check
												  left join sku_items using (sku_item_code)
												  left join sku on sku_id = sku.id
												  left join category on sku.category_id = category.id
												  left join category dept on category.department_id = dept.id 
												  where $filter 
												  group by stock_check.date,dept");
												  //print $abc
					
				}else{
					$sql = $con_multi->sql_query($abc="select stock_check.shelf_no, stock_check.scanned_by, stock_check.date, 
											  dept.description as dept, stock_check.location, sum(qty) as qty, 
											  sum(qty*cost) as tcost, sum(qty*selling) as tsell
											  from stock_check
											  left join sku_items using (sku_item_code)
											  left join sku on sku_id = sku.id
											  left join category on sku.category_id = category.id
											  left join category dept on category.department_id = dept.id 
											  where $filter 
											  group by stock_check.date,  stock_check.shelf_no");
											  //print $abc;
				}
				$table = $con_multi->sql_fetchrowset($sql);
				$con_multi->sql_freeresult($sql);
			}else{
				if($_REQUEST['group_by_item']) $order_by = "sku_items.sku_item_code";
				else {
					if($_REQUEST['group_type'] == 'bydepartment'){
						$order_by = "stock_check.date, dept";
					}else{
						$order_by = "stock_check.date, stock_check.shelf_no";
					}
				}
				if($_REQUEST['group_type'] == 'bydepartment'){
					$sql = $con_multi->sql_query($abc="select stock_check.*, sku_items.id as sku_item_id, sku_items.description as sku, 
											  sku_items.mcode, sku_items.artno, dept.description as dept, (stock_check.item_no*1) sort_no
											  ,sum(qty) as qty, sum(qty*cost) as tcost, sum(qty*selling), sku_items.link_code
											  from stock_check
											  left join sku_items using (sku_item_code)
											  left join sku on sku_id = sku.id
											  left join category on sku.category_id = category.id
											  left join category dept on category.department_id = dept.id 
											  where $filter 
											  group by sku_item_code,cost,dept
											  order by $order_by");//print "$abc<br />";
				}else{
					$sql = $con_multi->sql_query($abc="select stock_check.* , UPPER(stock_check.shelf_no) as shelf_no, sku_items.id as sku_item_id, sku_items.description as sku, 
											  sku_items.mcode, sku_items.artno, dept.description as dept, (stock_check.item_no*1) sort_no
											  ,sum(qty) as qty, sum(qty*cost) as tcost, sum(qty*selling), sku_items.link_code
											  from stock_check
											  left join sku_items using (sku_item_code)
											  left join sku on sku_id = sku.id
											  left join category on sku.category_id = category.id
											  left join category dept on category.department_id = dept.id 
											  where $filter 
											  group by sku_item_code,cost,stock_check.shelf_no
											  order by $order_by");//print "$abc<br />";
				}
					
				$table = array();
				$row_count = 0;
				while($r = $con_multi->sql_fetchassoc($sql)){
					if($_REQUEST['group_by_item']){
						$cost = strval(round($r['cost'], $config['global_cost_decimal_points']));
						$selling = strval(round($r['selling'], 2));
						if(!$table[$r['sku_item_id']][$cost][$selling]) $table[$r['sku_item_id']][$cost][$selling] = $r;
						$table[$r['sku_item_id']][$cost][$selling]['total_qty'] += $r['qty'];
						$table[$r['sku_item_id']][$cost][$selling]['total_cost'] += $r['qty'] * $r['cost'];
						$table[$r['sku_item_id']][$cost][$selling]['total_retail'] += $r['qty'] * $r['selling'];
						$ttl_selling = $table[$r['sku_item_id']][$cost][$selling]['total_retail'];
						$ttl_cost = $table[$r['sku_item_id']][$cost][$selling]['total_cost'];
						if($ttl_selling-$ttl_cost>0){
							$table[$r['sku_item_id']][$cost][$selling]['mark_on'] = ($ttl_selling-$ttl_cost)/$ttl_selling*100;
						}
					}else{
						if($_REQUEST['group_type'] == 'bydepartment'){
							if(!$table[$r['date']][$r['dept']][$row_count]) $table[$r['date']][$r['dept']][$row_count] = $r;
							$table[$r['date']][$r['dept']][$row_count]['total_qty'] += $r['qty'];
							$table[$r['date']][$r['dept']][$row_count]['total_cost'] += $r['qty'] * $r['cost'];
							$table[$r['date']][$r['dept']][$row_count]['total_retail'] += $r['qty'] * $r['selling'];
							$ttl_selling = $table[$r['date']][$r['dept']][$row_count]['total_retail'];
							$ttl_cost = $table[$r['date']][$r['dept']][$row_count]['total_cost'];
							if($ttl_selling-$ttl_cost>0){
								$table[$r['date']][$r['dept']][$row_count]['mark_on'] = ($ttl_selling-$ttl_cost)/$ttl_selling*100;
							}
							$row_count++;
						}else{
							if(!$table[$r['date']][$r['shelf_no']][$row_count]) $table[$r['date']][$r['shelf_no']][$row_count] = $r;
							/*$shelf_no_list[]= $table[$r['date']][$r['shelf_no']];
							foreach($shelf_no_list as $shelf => $shelf_no){
								foreach($shelf_no as $shelf_no_id){
									$shelf_no_id[] =strtoupper($shelf_no_id['shelf_no']);
								}
							}*/
							$table[$r['date']][$r['shelf_no']][$row_count]['total_qty'] += $r['qty'];
							$table[$r['date']][$r['shelf_no']][$row_count]['total_cost'] += $r['qty'] * $r['cost'];
							$table[$r['date']][$r['shelf_no']][$row_count]['total_retail'] += $r['qty'] * $r['selling'];
							$ttl_selling = $table[$r['date']][$r['shelf_no']][$row_count]['total_retail'];
							$ttl_cost = $table[$r['date']][$r['shelf_no']][$row_count]['total_cost'];
						
						if($ttl_selling-$ttl_cost>0){
							$table[$r['date']][$r['shelf_no']][$row_count]['mark_on'] = ($ttl_selling-$ttl_cost)/$ttl_selling*100;
						}
						$row_count++;
						}
					}
				}
				
			}
			$con_multi->sql_freeresult($sql);
			if($_REQUEST['sku_code_list_2']){
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
			}
			
			if ($error)
			{
				$smarty->assign('err',$error);
				$this->display('report.stock_check.tpl');
				exit;
			}
			$smarty->assign('table', $table);
			/*
			print "<pre>";
			print_r($list);
			print "</pre>";
			*/
		}
		
		$report_title = array();
		if($bid) $bcode = get_branch_code($bid);
		else $bcode = get_branch_code($sessioninfo['branch_id']);
		$report_title[] = "Branch: ".$bcode;
		$report_title[] = "View Type: ".$type;
		if($type == "detail"){
			if($_REQUEST['group_by_item']) $gbi_desc = "Yes";
			else $gbi_desc = "No";
			$report_title[] = "Group By Item: ".$gbi_desc;
		}
		if($_REQUEST['group_type'] == 'bydepartment'){
			$report_title[] = "Group Type: Department";
		}else{
			$report_title[] = "Group Type: Shelf";
		}
		
		$report_title[] = "Date: ".$_REQUEST['date'];
		
		if(!$_REQUEST['all_location']){
			$location_title = "From ".$_REQUEST['location_from']." To ".$_REQUEST['location_to'];
		}else $location_title = "All";
		$report_title[] = "Location: ".$location_title;
		
		if(!$_REQUEST['all_shelf_no']){
			$shelf_title = "From ".$_REQUEST['shelf_no_from']." To ".$_REQUEST['shelf_no_to'];
		}else $shelf_title = "All";
		$report_title[] = "Shelf No: ".$shelf_title;
		
		$act_desc = "All";
		if($_REQUEST['active']){
			if($_REQUEST['active'] == 1) $act_desc = "Active";
			else $act_desc = "Inactive";
		}

		$report_title[] = "Status: ".$act_desc;
		
		$report_title[] = "Vendor: ".($vendor_id > 0 ? $this->vendors_list[$vendor_id]['description'] : 'All');
		
		if($brand_id == -1){
			$report_title[] = "Brand: UN-BRANDED";
		}else{
			$report_title[] = "Brand: ".($brand_id > 0 ? $this->brands_list[$brand_id]['description'] : 'All');
		}
		
		if(count($check_department_list) > 1){
			$dept_num_count = count($check_department_list);

			foreach($check_department_list as $department_id){
				$departmet_dec[] = $this->dept_list[$department_id]['description'];
			}
			$report_title[] = "Department: ".join(' , ',$departmet_dec);
		}elseif(count($check_department_list) == 1){
			$dep_dec = implode("",$check_department_list);
			$report_title[] = "Department: ".($check_department_list > 0 ? $this->dept_list[$dep_dec]['description'] : 'All');
		}else{
			$report_title[] = "Department: ".($check_department_list > 0 ? $this->dept_list[$check_department_list]['description'] : 'All');
		}
		//$report_title[] = "Department: ".($dept_id > 0 ? $this->dept_list[$dept_id]['description'] : 'All');

		
		$smarty->assign("report_title", join("&nbsp;&nbsp;&nbsp;&nbsp;", $report_title));
		
		return true;
	}
}
$STOCK_TAKE_SUMMARY = new STOCK_TAKE_SUMMARY('Stock Take Summary');
?>
