<?php
/*
2008/6/23 15:52:15 yinsee
- fix "use GRN" bug

7/1/2008 3:03:47 PM yinsee
- change department dropdown become category dropdown

4/6/2009 5:13 PM
- if no sku_items.price.history.selling price then get selling_price from master

4/15/2009 11:20 PM
- Add sorting calculation

9/9/2009 11:07:53 AM Andy
- add stock check

10/30/2009 6:12:17 PM yinsee
- set open_bal to stock_bal if got stock check
- fix adj_in not added correctly

11/4/2009 9:44:04 AM Andy
- Edit cost, selling price & stock check, group by sku use new calculation

11/9/2009 3:11:03 PM Andy
- Fix open, closing & GP % bug

11/12/2009 1:32:18 PM Andy
- fix grn division zero warning

11/13/2009 3:00:28 PM Andy
- fix from date data error and adj out bug

11/16/2009 2:18:53 PM Andy
- Add filter by SKU Type

12/21/2009 5:30:46 PM Andy
- Fix brand_id equal to zero bug
- Fix cost column to show closing close, now some item show opening cost
- Show Category name if only category id is provided
- Fix Grand total Double the POS Qty and Adj out

4/2/2010 3:00:27 PM Andy
- Add turnover calculation, only show if got config

5/6/2010 4:47:54 PM PM Alex
- Add HQ Cost

5/31/2010 2:54:17 PM Andy
- Stock balance and inventory calculation include CN(-)/DN(+), can see under SKU Masterfile->Inventory.

6/8/2010 10:11:36 AM Andy
- CN/DN Swap

7/19/2010 5:27:42 PM Andy
- Fix stock balance report when show by group, zero closing qty item will still show.

8/13/2010 10:05:37 AM Andy
- Hide Item with "SKU without inventory".

10/26/2010 11:54:15 AM Alex
- Change use Module
- remove all cateeory
- add 1 day if filter with timestamp
- make both stock balance report tally

10/28/2010 11:00:18 AM Alex
- fix show date bugs

11/30/2010 12:01:48 PM Alex
- fix closing stock match to opening stock

1/3/2011 4:32:33 PM Andy
- Fix report if show jan 1 opening will get zero stock balance.

1/21/2011 10:35:50 AM Andy/Justin
- Fixed the bugs for unable to do sorting by selected field (by Andy).
- Redirect the report to use con_multi instead of con (by Justin).

2/9/2011 11:35:58 AM Alex
under use grn condition
- fix get duplicate data if got multiple grn within selected date

2/23/2011 4:50:13 PM Andy
- Change report title: "Stock Balance Report" to "Stock Balance Report by Department".

3/14/2011 3:41:38 PM Alex
- change use gra cost, adjustment cost, sku_items_sales_cache cost, grn cost instead of using closing cost

3/18/2011 12:38:09 PM Alex
- fix bugs on calculating  POS cost

3/18/2011 5:52:47 PM Alex
- fix grn cost amount bugs

3/30/2011 7:28:39 PM Alex
- fix get latest vendor bugs

6/10/2011 7:08:10 PM Alex
- fix get latest vendor bugs

6/13/2011 5:05:12 PM Alex
- add order by branch_id, sku_item_id at vendor_sku_history to fool mysql use index key

6/22/2011 12:33:38 PM Andy
- Add filter "Blocked Item in PO" in stock balance by department report.

7/6/2011 2:43:53 PM Andy
- Change split() to use explode()

7/7/2011 2:56:25 PM Alex
- add stock check adjustment and exclude 2nd time of filtering zero closing balance

8/4/2011 11:42:28 AM Alex
- add selling price for opeing, total on hand, closing

8/12/2011 5:47:21 PM Justin
- Added to filter must active for sku items.

8/15/2011 11:33:21 AM Justin
- Added filter "Item Status" for SKU.

8/15/2011 11:33:21 AM Justin
- Added filter "Blocked Item in PO" in stock balance by department report.
- Added filter "Status" for SKU.

9/27/2011 3:31:01 PM Andy
- Change report to also show those SKU which got GRN between from/to date.
- Add GRN Qty,Cost to show additional qty/cost for selected vendor when use GRN.

11/15/2011 3:05:04 PM Andy
- Add checking for config.use_grn_last_vendor_include_master, if found config then last GRN only check master vendor.

1/11/2012 3:01:54 PM Justin
- Redirected create table "stock_balance_b..." to create from functions.php.

3/15/2012 4:11:58 PM Andy
- Change Report to use sku vendor from cache table.
- Check maintenance version 119.

4/12/2012 5:47:06 PM Andy
- Fix filter SKU status not working.

5/4/2012 2:21:12 PM Justin
- Added to pickup new info "stock check adjust qty" for opening balance and "stock take adjust qty and value" for range.

6/14/2012 2:16:12 PM Justin
- Fixed bug of wrong Qty adjustment sum up.

6/28/2012 4:49 PM Andy
- Fix some item cannot get stock take adjust qty.

7/18/2012 5:41 PM Andy
- Fix wrong opening value if got stock take adjust.

8/10/2012 3:38 PM Andy
- Fix stock take adjust value

1/10/2013 1:52:00 PM Fithri
- add (*) for those item changed=1

2/4/2013 5:34 PM Justin
- Enhanced to show and filter branches from regions or branch group base on user's regions.

7/8/2013 4:46 PM Andy
- Slightly reduce memory usage.
- Extend allowed memory limit to 1024MB.

7/17/2013 3:00 PM Andy
- Optimize report structure to use less memory.
- Remove un-needed stock take date list.

4/2/2014 11:02 AM Fithri
- add option to select all SKU / branches for some reports, require config 'allow_all_sku_branch_for_selected_reports' to be turned on

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

7/15/2015 11.16 AM Joo Chia
- DO cost value change to multiply quantity with do_items.cost

11/24/2015 3:31 PM 
- Bug fixed on decimal UOM fraction will cause problem when using SKU Grouping.

03/30/2016 14:45 Edwin
- Bug fixed on incorrect calculate GP in stock balance report
- Enhanced on show exclusive selling price when SKU item is inclusive tax and branch is unser gst

5/9/2017 5:05 PM Andy
- Added "Use HQ GRN" feature.

8/15/2017 15:16 PM Qiu Ying
- Enhanced to add Sales Value at Opening and Closing

10/10/2017 3:10 PM Justin
- Enhanced to sum up closing balance from accumulated cost (GRA, GRN, Adjustment and etc) when config is turned on.
- Bug fixed on total on hand calculation is sum up from opening, GRN, Adj in, Stock Check instead of multiple from opening cost.

10/16/2017 3:26 PM Justin
- Enhanced to hide "Selling Price" while group by SKU filter is ticked.
- Enhanced to all columns that showing both qty and value become showing either Qty or Cost base on "Show by Qty" or "Show by Cost" button.

10/20/2017 4:46 PM Justin
- Bug fixed on missing to deduct GRA amount.

3/1/2018 9:36 AM Justin
- Bug fixed on negative stock adjust item does not show out from report.
- Bug fixed on opening sales values did not check against stock take qty.

3/12/2018 6:06 PM HockLee
- Added filter by Input Tax and Output Tax.
- Report title show up if enable_gst is on.

5/3/2018 10:21 AM Andy
- Added Foreign Currency feature.

7/4/2019 4:28 PM William
- Added new "Day Turnover" column to stock balance report.
- Remove config of "Turnover", when turnover don't have config turnover also will show.

2/24/2020 9:54 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

6/2/2020 3:02 PM William
- Fixed bug wrong word dn_bal.

6/11/2020 6:13 PM William
- Bug fixed when filter by all category, the report will not check the user department settings.
*/
include("include/common.php");
//ob_end_clean(); // end the ob flush

//$con = new sql_db('jwt-uni.dyndns.org','arms','4383659','armshq');
//$con = new sql_db('ws-hq.arms.com.my:4001','arms_slave','arms_slave','armshq');//$con = new sql_db('cwmhq.no-ip.org:4001','arms','sc440','armshq');
//$con = new sql_db('cutemaree.dyndns.org:4001','arms','990506','armshq');

//print_r($con);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");
$maintenance->check(119);

ini_set('memory_limit', '1024M');
set_time_limit(0);

if($sessioninfo['u'] == 'admin'){
	//error_reporting (E_ALL ^ E_NOTICE);
	//ini_set("display_errors", 1);
}

class Stock_Balance extends Module{
	var $use_grn = false;
	var $use_hq_grn = false;
	
    function __construct($title){
        global $con, $sessioninfo, $smarty, $config, $con_multi, $appCore;
		//default assign at 1st load
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		if (BRANCH_CODE == 'HQ'){
			$q1 = $con_multi->sql_query("select * from branch where active=1 order by sequence, code") or die(mysql_error());
			
			while($r = $con_multi->sql_fetchassoc($q1)){
				if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
				$branches[] = $r;
			}
			$con_multi->sql_freeresult($q1);
			$smarty->assign('branch',$branches);
		}

		// sku type
		$con_multi->sql_query("select * from sku_type") or die(mysql_error());
		$smarty->assign("sku_type", $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();

		// show department option
		if ($sessioninfo['level'] < 9999){
			$depts = "id in (" . $sessioninfo['department_ids'] . ")";
		}
		else{
			$depts = 1;
		}
		$con_multi->sql_query("select id, description from category where active and level = 2 and $depts order by description");
		$smarty->assign("dept", $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();

		// show brand option
		$br = ($sessioninfo['brands']) ? "id in (".join(",",array_keys($sessioninfo['brands'])).") and" : "";
		$con_multi->sql_query("select id, description from brand where $br active order by description") or die(mysql_error());
		$smarty->assign("brand", $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
		
		$smarty->assign("brand_groups", get_brand_group());

		//show vendor option
		if ($sessioninfo['vendors']){
			$vd = "and id in (".join(",",array_keys($sessioninfo['vendors'])).")";
		}
		$con_multi->sql_query("select id, description from vendor where active $vd order by description") or die(mysql_error());;
		$smarty->assign("vendor", $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();

		//show date
		if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
		if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month +1 day"));

		$smarty->assign("PAGE_TITLE", $title);

		//check input data
		if (BRANCH_CODE == 'HQ')
			$this->bid = mi($_REQUEST['branch_id']);
		else
			$this->bid = $sessioninfo['branch_id'];
		
		// get input and output tax code
		if($config['enable_gst']){
			$q1 = $con_multi->sql_query("select * from gst where active=1");
			while($r = $con_multi->sql_fetchassoc($q1)){
				if($r['type'] == "purchase"){
					$input_tax_list[$r['id']] = $r;
				}else{
					$output_tax_list[$r['id']] = $r;
				}
			}
			$con_multi->sql_freeresult($q1);
			$smarty->assign("input_tax_list", $input_tax_list);
			$smarty->assign("output_tax_list", $output_tax_list);
		}

		$smarty->assign("form", $_REQUEST);
		parent::__construct($title);
	}
	
	function _default(){
		$this->display();
		exit;
	}
	
	function output_excel(){
		global $smarty, $sessioninfo;
		// Export function

		include("include/excelwriter.php");
		$smarty->assign('no_header_footer', true);

		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=stock_balance_'.time().'.xls');

		print ExcelWriter::GetHeader();
		$this->show_report();
		print ExcelWriter::GetFooter();

	  	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export $_REQUEST[report_title] To Excel()");

		exit;

	}

	function export_csv(){
        global $sessioninfo,$smarty;
		$this->show_report();
        $smarty->display("report.stock_balance.export_csv.tpl");
        exit;

	}

	function show_report(){
	    global $sessioninfo,$smarty,$con,$config,$con_multi;

		//check error
		if($_REQUEST['to']<$_REQUEST['from'])  $err[] = "Invalid Date Range";

		if($err){
			$smarty->assign("err", $err);
			$this->_default();
		}
		
		//for printing purpose
		$con_multi->sql_query("select * from branch where id=".$this->bid) or die(mysql_error());
		$r0=$con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		$smarty->assign("p_branch",$r0);

		//report title
		$title="Date: $_REQUEST[from] to $_REQUEST[to]";
		$title .= " / ";

		if ($_REQUEST['vendor_id']){
			$con_multi->sql_query("select description from vendor where id=".mi($_REQUEST['vendor_id'])) or die(mysql_error());
			$v = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$title .= "Vendor: $v[0]";
		}
		else $title .= "Vendor: All";
		$title .= " / ";

		if ($_REQUEST['category_id']){
			//$con->sql_query("select tree_s from category where id=".mi($_REQUEST['category_id']));
			//$v = $con->sql_fetchrow();
			$title .= "Category: $_REQUEST[category_tree]";
		}
		else{
			$title .= "Category: All";
		}

		$title .= " / ";

		/*
		if ($_REQUEST['brand_id']>0){
			$con->sql_query("select description from brand where id =".mi($_REQUEST['brand_id'])) or die(mysql_error());
			$v = $con->sql_fetchrow();
			$title .= "Brand: $v[0]";
		}
		elseif ($_REQUEST['brand_id']==='0'){
			$title .= "Brand: UN-BRANDED";
		}
		else{
			$title .= "Brand: All";
		}
		*/
		$title .= "Brand: ".get_brand_title($_REQUEST['brand_id']);

		if($_REQUEST['blocked_po']){
			$title .= " / Blocked Item in PO: ".ucwords($_REQUEST['blocked_po']);
		}
		
		if(!$_REQUEST['status']) $status = "Inactive";
		elseif($_REQUEST['status'] == 1) $status = "Active";
		else $status = ucwords($_REQUEST['status']);

		$title .= " / Status: ".$status;
		
		$input_tax = mi($_REQUEST['input_tax']);
		$output_tax = mi($_REQUEST['output_tax']);
		
		if($config['enable_gst']){
			if(!$input_tax){
				$title .= " / Input Tax: All";
			}else{
				$inpt_tax = get_gst_settings($input_tax);		
				if ($inpt_tax['active'] == 1) $title .= " / Input Tax: ".$inpt_tax['code']." (".$inpt_tax['rate']."%)";
			}
			
			If(!$output_tax){
				$title .= " / Output Tax: All";
			}else{
				$outpt_tax = get_gst_settings($output_tax);
				if ($outpt_tax['active'] == 1) $title .= " / Output Tax: ".$outpt_tax['code']." (".$outpt_tax['rate']."%)";
			}
		}		
		
		if($_REQUEST['use_grn'])	$this->use_grn = true;
		if($_REQUEST['use_hq_grn'])	$this->use_hq_grn = true;
		if($this->use_grn && $this->use_hq_grn)	$this->use_grn = false;	// if both also true, use HQ GRN		
		
		$smarty->assign("title", "Stock Balance Report ($title)");

		$this->generate_report();

		$this->display();
	}
		
	function generate_report(){
		global $con, $smarty, $sessioninfo, $LANG, $config, $con_multi;

		/*$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/

		$from_date=$_REQUEST['from'];
		$from_date_opening = date('Y-m-d', strtotime("-1 day", strtotime($from_date)));
		$to_date=$_REQUEST['to'];
		$to_date_timestamp = date('Y-m-d', strtotime("+1 day", strtotime($to_date)));
		
		$blocked_po = trim($_REQUEST['blocked_po']);
		$status = trim($_REQUEST['status']);
		$vendor_id = $_REQUEST['vendor_id'];
		$input_tax = mi($_REQUEST['input_tax']);
		$output_tax = mi($_REQUEST['output_tax']);		
		$bid=$this->bid;

        if($config['enable_gst']){
            $prms = array();
            $prms['branch_id'] = $bid;
            $prms['date'] = $from_date_opening;
            $branch_is_under_gst = check_gst_status($prms);
        }
        
		$no_of_days = mi((strtotime($to_date)-strtotime($from_date))/86400)+1;
		
		// FILTER view latest sku items added
		$where = array();
		
		if (isset($_REQUEST['all_category']))
		{
			// all catgory, need check the user department
			$category_block_list = implode(',',array_keys($sessioninfo['departments']));
			$where[] = " c.department_id in ($category_block_list)";
		}
		elseif ($_REQUEST['category_id'] > 0){
			$con_multi->sql_query("select category_cache.*, category.level,category.description as cname from category_cache left join category on category_id = category.id where category_id=".mi($_REQUEST['category_id'])) or die(mysql_error());
			$ccache = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			if (!$ccache) die("Error: Please regenerate category_cache (Masterfile -> Category).");
			$where[] = " ccache.p$ccache[level] = ".mi($_REQUEST['category_id']);
			
			$_REQUEST['category'] = $ccache['cname'];
		}
		else
		{
			// must select a category
			$smarty->assign("msg", $LANG['REPORT_PLEASE_SELECT_CATEGORY']);
			return false;
		}
		
		if($this->use_grn && !$vendor_id){
			$smarty->assign("msg", "Use GRN must have vendor selected");
			return false;
		}
		
		if($this->use_hq_grn && !$vendor_id){
			$smarty->assign("msg", "Use HQ GRN must have vendor selected");
			return false;
		}

		if ($_REQUEST['brand_id']!=''){
			$where[] = " sku.brand_id in (".join(',',process_brand_id($_REQUEST['brand_id'])).")";
		}

		if ($_REQUEST['vendor_id']){
			if (!$this->use_grn && !$this->use_hq_grn)
	  			$where[] = " sku.vendor_id = ".mi($_REQUEST['vendor_id']);
		}
		if($_REQUEST['sku_type']){
			$where[] = "sku.sku_type=".ms($_REQUEST['sku_type']);
		}

		if($_REQUEST['group_by_sku']){
			$group_by_sku=1;
		}

		if($blocked_po){
			if($blocked_po=='yes'){
				$where[] = "si.block_list like ".ms("%i:$bid;s:2:\"on\";%");
			}elseif($blocked_po=='no'){
				$where[] = "(si.block_list not like ".ms("%i:$bid;s:2:\"on\";%")." or si.block_list is null)";
			}
		}
		
		if($status != "all") $where[] = "si.active = ".mi($status);

		$where[] = "((sku.no_inventory='inherit' and ccache.no_inventory='no') or sku.no_inventory='no')";
		
		// filter by input tax
		if ($input_tax) $where[] = "if(si.input_tax<=-1,if(sku.mst_input_tax<=-1,ccache.input_tax,sku.mst_input_tax),si.input_tax )=$input_tax";	
		
		// filter by output tax
		if ($output_tax) $where[] = "if(si.output_tax<=-1,if(sku.mst_output_tax<=-1,ccache.output_tax,sku.mst_output_tax),si.output_tax )=$output_tax";

        list($y,$m,$d) = explode("-",$from_date_opening);
        $tbl_sb_1 = "stock_balance_b".mi($bid)."_".mi($y);
		$prms = array();
		$prms['tbl'] = $tbl_sb_1;
		initial_branch_sb_table($prms);
        //$this->check_table($tbl_sb_1);

		// table for stock balance - to
        list($y,$m,$d) = explode("-",$to_date);
        $tbl_sb_2 = "stock_balance_b".mi($bid)."_".mi($y);
		$prms = array();
		$prms['tbl'] = $tbl_sb_2;
		initial_branch_sb_table($prms);
        //$this->check_table($tbl_sb_2);

		//add HQ Cost
		if ($_REQUEST['hq_cost']) $extrasql = ", si.hq_cost as start_cost, si.hq_cost as cost";
		else    $extrasql= ", ifnull(sb1.cost,si.cost_price) as start_cost, ifnull(sb2.cost,si.cost_price) as cost";

		$bid_use_grn = 0;
		if($this->use_grn){	// it is using grn
			$bid_use_grn = $bid;
		}elseif($this->use_hq_grn){	// using hq grn
			$bid_use_grn = 1;
		}
		if($bid_use_grn){
			$where[] = "si.id in (select vsh.sku_item_id from vendor_sku_history_b".$bid_use_grn." vsh where vendor_id=$vendor_id and (".ms($from_date)." between vsh.from_date and vsh.to_date or ".ms($to_date)." between vsh.from_date and vsh.to_date or vsh.from_date between ".ms($from_date)." and ".ms($to_date)."))";
		}
		
		$where = join(" and ", $where);
		if (!$where) $where=1;
		
		if($sessioninfo['u'] == 'admin'){
			//print "Start Query: ".memory_get_usage()."<br>";
		}
		
		$sql = "select si.id, sb1.qty as sb_from, sb2.qty as sb_to $extrasql,si.selling_price
			from sku_items si
			left join sku on si.sku_id=sku.id
			left join sku_items_cost sic on si.id=sic.sku_item_id and sic.branch_id=$bid
			left join $tbl_sb_1 sb1 on sb1.sku_item_id=si.id and ((".ms($from_date_opening)."
				between sb1.from_date and sb1.to_date))
			left join $tbl_sb_2 sb2 on sb2.sku_item_id=si.id and ((".ms($to_date)."
				between sb2.from_date and sb2.to_date))
			left join category_cache ccache on ccache.category_id=sku.category_id
			left join category c on c.id = ccache.category_id
			left join uom u1 on u1.id=si.packing_uom_id
			where $where";
			
		//print $sql.'<br /><br />';
		$q1=$con_multi->sql_query($sql) or die(mysql_error());

		//Opening and Closing xxx
		//$sku_item_list = array();
		//$temp_sku_id = array();
		
		//print "p1 (start) : ".(memory_get_usage()/1024/1024)."<br />";
		
		while($r=$con_multi->sql_fetchassoc($q1)){
		    $sid=$r['id'];
			
			$table[$sid]['opening_selling']=$r['selling_price'];
			$table[$sid]['closing_selling']=$r['selling_price'];
			$table[$sid]['closing_cost']=$r['cost'];
			$table[$sid]['start_cost']=$r['start_cost'];
			
			$table[$sid]['open_bal'] += $r['sb_from'];
			$table[$sid]['closing_bal'] += $r['sb_to'];
			$table[$sid]['open_bal_val'] += ($r['sb_from']*$r['start_cost']);
			if(!$config['stock_balance_use_accumulate_last_cost']) $table[$sid]['closing_bal_val'] += ($r['sb_to']*$r['cost']);
			else $table[$sid]['closing_bal_val'] += ($r['sb_from']*$r['start_cost']);
		}
		$con_multi->sql_freeresult($q1);
		
		//print "p2 : ".(memory_get_usage()/1024/1024)."<br />";
		
		if($sessioninfo['u'] == 'admin'){
			//print "End Query: ".memory_get_usage()."<br>";
			//print "Total Item Count: ".count($sku_item_list)."<br>";
			//exit;
		}
		//print_r($table);
		if (!$table) return false;

		//check stock check
		$got_stock_check = false;

		//avoid mysql error if too much data
		$sid_count = count($table);
		//print "Items : $sid_count<br />";
		for($i=0; $i<$sid_count; $i+=5000){
			if($sessioninfo['u'] == 'admin'){
				//print "Loop start from $i<br>";
			}
			$sid_list2 = array_keys(array_slice($table, $i, 5000, true));
			$where_sid = "sku_item_id in (" . join(",", $sid_list2) . ")";
			$where_sid2 = "si.id in (" . join(",", $sid_list2) . ")";

			//Opening price
			if($sessioninfo['u'] == 'admin'){
				//print "Start Query Opening Selling : ".memory_get_usage()."<br>";
			}
			$q3=$con_multi->sql_query("select siph.*, siph.sku_item_id as sid,
			(select max(added) from sku_items_price_history sh where sh.sku_item_id = sid and sh.branch_id=$bid and sh.added <'$from_date') as price_added
			from
			sku_items_price_history siph
			left join sku_items on siph.sku_item_id = sku_items.id
			where branch_id=$bid and siph.added < '$from_date' and $where_sid
			having price_added = siph.added order by null") or die(mysql_error());
			while($r3=$con_multi->sql_fetchassoc($q3)){
				if($r3['price']>0){
				    $sid = $r3['sid'];
					$table[$sid]['opening_selling']=$r3['price'];
				}
			}
			$con_multi->sql_freeresult($q3);
			if($sessioninfo['u'] == 'admin'){
				//print "End Query Opening Selling : ".memory_get_usage()."<br>";
			}
			
			//Closing price
			if($sessioninfo['u'] == 'admin'){
				//print "Start Query Closing Selling : ".memory_get_usage()."<br>";
			}
			$q3=$con_multi->sql_query("select siph.*, siph.sku_item_id as sid,
			(select max(added) from sku_items_price_history sh where sh.sku_item_id = sid and sh.branch_id=$bid and sh.added <'$to_date_timestamp') as price_added
			from
			sku_items_price_history siph
			left join sku_items on siph.sku_item_id = sku_items.id
			where branch_id=$bid and siph.added < '$to_date_timestamp' and $where_sid
			having price_added = siph.added order by null") or die(mysql_error());

			while($r3=$con_multi->sql_fetchassoc($q3)){
				if($r3['price']>0){
				    $sid = $r3['sid'];
					$table[$sid]['closing_selling']=$r3['price'];
				}
			}
			$con_multi->sql_freeresult($q3);
			if($sessioninfo['u'] == 'admin'){
				//print "End Query Closing Selling : ".memory_get_usage()."<br>";
			}

			//Closing cost
			if (!$_REQUEST['hq_cost']){
				if($sessioninfo['u'] == 'admin'){
					//print "Start Query Closing Cost : ".memory_get_usage()."<br>";
				}
				$q_cc = $con_multi->sql_query("select sich.date,sich.sku_item_id as sid , ifnull(sich.grn_cost,sku_items.cost_price) as grn_cost,
				(select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=$bid and sh.date <='$to_date') as stock_date
				from
				sku_items_cost_history sich
				left join sku_items on sich.sku_item_id = sku_items.id
				where branch_id=$bid and sich.date <= '$to_date' and sich.date > 0 and $where_sid
				having stock_date=sich.date order by null ") or die(mysql_error());

				while($r = $con_multi->sql_fetchassoc($q_cc)){
				    $sid = $r['sid'];
		            $table[$sid]['closing_cost'] = $r['grn_cost'];
				}
				$con_multi->sql_freeresult($q_cc);
				if($sessioninfo['u'] == 'admin'){
					//print "End Query Closing Cost : ".memory_get_usage()."<br>";
				}
			}
			// GRN
			//GRN = get the rcv qty
			
			if($sessioninfo['u'] == 'admin'){
				//print "Start Query GRN : ".memory_get_usage()."<br>";
			}
			$tmp_grp = $tmp_col = '';
			if($this->use_grn){
				$tmp_col = ", grn.vendor_id as grn_vendor_id";
				$tmp_grp = ", grn_vendor_id";
			}
			$sql = "select grn_items.sku_item_id as sid,
		sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
		sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
	(grn_items.ctn  + (grn_items.pcs / rcv_uom.fraction)),
	(grn_items.acc_ctn + (grn_items.acc_pcs / rcv_uom.fraction))) *
	if (grn_items.acc_cost is null, grn_items.cost,grn_items.acc_cost)*if(grr.currency_rate<0,1,grr.currency_rate)) as total_rcv_cost
		$tmp_col
		from grn_items
		left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
		left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
		left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
		where grn.branch_id=$bid and rcv_date between ".ms($from_date)." and ".ms($to_date)." and $where_sid and grn.approved=1 and grn.status=1 and grn.active=1 and grr.active=1
		group by sid $tmp_grp";
			//print $sql;
			$con_multi->sql_query($sql) or die(mysql_error());
			while($r = $con_multi->sql_fetchassoc()){
			    $sid = $r['sid'];

				$table[$sid]['rcv_qty'] += $r['qty'];
				$table[$sid]['rcv_val'] += $r['total_rcv_cost'];
				
				if($this->use_grn){
					if($r['grn_vendor_id'] == $_REQUEST['vendor_id']){
						$table[$sid]['rcv_vendor_qty']+= $r['qty'];
						$table[$sid]['rcv_vendor_val']+= $r['total_rcv_cost'];
					}
				}
				
				if($config['stock_balance_use_accumulate_last_cost']) $table[$sid]['acc_bal_val'] += $r['total_rcv_cost'];
			}
			$con_multi->sql_freeresult();
			if($sessioninfo['u'] == 'admin'){
				//print "End Query GRN : ".memory_get_usage()."<br>";
			}
			
			//GRA
			if($sessioninfo['u'] == 'admin'){
				//print "Start Query GRA : ".memory_get_usage()."<br>";
			}
			$sql = "select
		gra_items.sku_item_id as sid,
		sum(qty) as qty,sum(qty * gra_items.cost * if(gra.currency_rate<0,1,gra.currency_rate)) as total_gra_cost
		from gra_items
		left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
		where gra.branch_id=$bid and return_timestamp between ".ms($from_date)." and ".ms($to_date_timestamp)." and $where_sid and gra.status=0 and gra.returned=1
		group by sid";
		    //print $sql;
			$con_multi->sql_query($sql) or die(mysql_error());
			while($r = $con_multi->sql_fetchassoc()){
			    $sid = $r['sid'];

				$table[$sid]['gra_qty'] += $r['qty'];
				$table[$sid]['gra_val'] += $r['total_gra_cost'];
				
				if($config['stock_balance_use_accumulate_last_cost']) $table[$sid]['acc_bal_val'] -= $r['total_gra_cost'];
			}
			$con_multi->sql_freeresult();
			if($sessioninfo['u'] == 'admin'){
				//print "End Query GRA : ".memory_get_usage()."<br>";
			}
			
			// POS
			if($sessioninfo['u'] == 'admin'){
				//print "Start Query POS : ".memory_get_usage()."<br>";
			}
			$tbl="sku_items_sales_cache_b".$bid;
			$sql = "select
					si.id as sid,
					sum(qty) as qty,sum(pos.cost) as total_pos_cost
					from $tbl pos
					left join sku_items si on si.id=pos.sku_item_id
					where date between ".ms($from_date)." and ".ms($to_date)." and $where_sid
					group by sid";
		    //print $sql;
		 	$con_multi->sql_query($sql) or die(mysql_error());
		 	while($r = $con_multi->sql_fetchassoc()){
			    $sid = $r['sid'];

				$table[$sid]['pos_qty'] += $r['qty'];
				$table[$sid]['pos_val'] += $r['total_pos_cost'];
			}
			$con_multi->sql_freeresult();
			if($sessioninfo['u'] == 'admin'){
				//print "End Query POS : ".memory_get_usage()."<br>";
			}
			
			// DO
			if($sessioninfo['u'] == 'admin'){
				//print "Start Query DO : ".memory_get_usage()."<br>";
			}
			$sql = "select
		do_items.sku_item_id as sid,
		sum(do_items.ctn *uom.fraction + do_items.pcs) as qty , do_items.cost		
		from do_items
		left join uom on do_items.uom_id=uom.id
		left join do on do_id = do.id and do_items.branch_id = do.branch_id
		where do_items.branch_id=$bid and do_date between ".ms($from_date)." and ".ms($to_date)." and $where_sid and do.approved=1 and do.checkout=1 and do.status<2
		group by sid";
		   //print $sql;
			$con_multi->sql_query($sql) or die(mysql_error());
		 	while($r = $con_multi->sql_fetchassoc()){
			    $sid = $r['sid'];

				$table[$sid]['do_qty'] += $r['qty'];
				//$table[$sid]['do_val'] += $r['qty'] * $table[$sid]['closing_cost'];
				$do_amt = $r['qty'] * $r['cost'];
				$table[$sid]['do_val'] += $do_amt;
			}
			$con_multi->sql_freeresult();
			if($sessioninfo['u'] == 'admin'){
				//print "End Query POS : ".memory_get_usage()."<br>";
			}
			
			// ADJ
			if($sessioninfo['u'] == 'admin'){
				//print "Start Query ADJ : ".memory_get_usage()."<br>";
			}
			$sql = "select
			ai.sku_item_id as sid,
			sum(qty) as qty,sum(qty * ai.cost) as total_adj_cost,
			if(qty>=0,'p','n') as type
			from adjustment_items ai
			left join adjustment adj on adjustment_id = adj.id and ai.branch_id = adj.branch_id
			where ai.branch_id =$bid and adjustment_date between ".ms($from_date)." and ".ms($to_date)." and $where_sid and adj.approved=1 and adj.status<2
			group by type, sid";
			//print $sql;
			$con_multi->sql_query($sql) or die(mysql_error());
			while($r = $con_multi->sql_fetchassoc()){
			    $sid = $r['sid'];

				if($r['type']=='p'){
					$table[$sid]['adj_in'] += $r['qty'];
					$table[$sid]['adj_in_val'] += $r['total_adj_cost'];
				}
				elseif($r['type']=='n'){
					$table[$sid]['adj_out']+=abs($r['qty']);
					$table[$sid]['adj_out_val'] += abs($r['total_adj_cost']);
				}
			}
			$con_multi->sql_freeresult();
	  		if($sessioninfo['u'] == 'admin'){
				//print "END Query ADJ : ".memory_get_usage()."<br>";
			}
			
			//get max date of each stock check items
			if($sessioninfo['u'] == 'admin'){
				//print "Start Query MAX stock check date : ".memory_get_usage()."<br>";
			}
			$sql_max_date="select max(sc.date) as max_date, si.id as sid,si.sku_item_code  from stock_check sc 
					left join sku_items si on sc.sku_item_code=si.sku_item_code
					where branch_id=$bid and sc.date between ".ms($from_date)." and ".ms($to_date)." and $where_sid2 group by si.id";
	  		$get_max_date=$con_multi->sql_query($sql_max_date) or die(mysql_error());
			while($r = $con_multi->sql_fetchassoc($get_max_date)){
				$arr_max_date[$r['max_date']][$r['sid']]=$r['sid'];
			}
	  		$con_multi->sql_freeresult($get_max_date);
			if($sessioninfo['u'] == 'admin'){
				//print "End Query MAX stock check date : ".memory_get_usage()."<br>";
			}
			
	  		if ($_REQUEST['hq_cost']) $extrasql = ", si.hq_cost as cost";
	  		else $extrasql = ", sc.cost as cost";

			// check got stock take and get latest 
			if ($arr_max_date){
				foreach ($arr_max_date as $max_date => $arr_sids){
					if($sessioninfo['u'] == 'admin'){
						//print "Start Query stock check : ".memory_get_usage()."<br>";
					}
					$filter_sids= " si.id in (".join(",",$arr_sids).")";	
	
					$sql = "select si.id as sid $extrasql, sc.date, sc.qty as qty
							from stock_check sc
							left join sku_items si on sc.sku_item_code=si.sku_item_code
							where sc.branch_id=$bid and sc.date =".ms($max_date)." and $filter_sids";
		            $con_multi->sql_query($sql) or die(mysql_error());
					while($r = $con_multi->sql_fetchassoc()){
					    $sid = $r['sid'];
		
						$got_stock_check = true;
						$table[$sid]['got_sc'] = true;
						$table[$sid]['sc_bal'] += $r['qty'];
			            $table[$sid]['sc_bal_val'] += $r['qty'] * $r['cost'];
						$table[$sid]['sc_date'] = $r['date'];
						
						if($config['stock_balance_use_accumulate_last_cost']) $table[$sid]['acc_bal_val'] += ($r['qty'] * $r['cost']);
					}
					$con_multi->sql_freeresult();
					if($sessioninfo['u'] == 'admin'){
						//print "End Query stock check : ".memory_get_usage()."<br>";
					}
				}
		  		unset($arr_max_date);

				// check if got stock take at opening
				if($sessioninfo['u'] == 'admin'){
					//print "Start Query stock check at opening : ".memory_get_usage()."<br>";
				}
						
				$sql = "select si.id as sid $extrasql, sc.qty, sc.date
						from stock_check sc
						right join sku_items si on si.sku_item_code=sc.sku_item_code
						where sc.branch_id=$bid and sc.date = ".ms($from_date)." and $where_sid2";
				//print $sql;
                $con_multi->sql_query($sql) or die(mysql_error());
                $open_sc_balance_val = array();
				while($r = $con_multi->sql_fetchrow()){
				    $sid = $r['sid'];
					
					//$open_sc_balance_sids[$r['date']][$sid]=$sid;
					$open_sc_balance_val[$sid]['qty']+=$r['qty'];
					$open_sc_balance_val[$sid]['cost']+=$r['qty']*$r['cost'];
					
					$got_opening_sc = true;
				}
				$con_multi->sql_freeresult();
				if($sessioninfo['u'] == 'admin'){
					//print "End Query stock check at opening : ".memory_get_usage()."<br>";
				}
				
				// get opening stock balance qty and value before stock check
				if($open_sc_balance_val){
					//foreach ($open_sc_balance_val as $sc_date => $sids){
						$minus_1_day=strtotime("-1 day",strtotime($from_date));
						$sb_year=date("Y",$minus_1_day);
						$sc1day_date= date("Y-m-d",$minus_1_day);
						
						$sb_tbl="stock_balance_b$bid"."_".$sb_year;
					
						if($sessioninfo['u'] == 'admin'){
							//print "Start Query qty before stock check: ".memory_get_usage()."<br>";
						}
						$sql = "select si.id as sid, sc.qty
								from sku_items si
								left join $sb_tbl sc on si.id=sc.sku_item_id and ".ms($sc1day_date)." between sc.from_date and sc.to_date 
								where si.id in (".join(",", array_keys($open_sc_balance_val)).")
								group by sid";
						//print $sql."<br />";
						$con_multi->sql_query($sql) or die(mysql_error());
						while($r = $con_multi->sql_fetchassoc()){
							$sid = $r['sid'];
							$table[$sid]['open_bal_val'] = 0;
							
							$sc_adj_qty = $open_sc_balance_val[$sid]['qty'] - $r['qty'];
							
							$table[$sid]['open_sc_adj'] += $sc_adj_qty;
							$table[$sid]['open_bal'] += $sc_adj_qty;
							
							$new_cost = 0;
							if($open_sc_balance_val[$sid]['cost']){
								//$unit_cost = $open_sc_balance_val[$sc_date][$sid]['cost']/($open_sc_balance_val[$sc_date][$sid]['qty'] - $r['qty']);
								$new_cost = $open_sc_balance_val[$sid]['cost'];
							}else{
								//$unit_cost = $sku[$sid]['closing_cost'];
								$new_cost = $table[$sid]['start_cost']*$open_sc_balance_val[$sid]['qty'];
							}
							//$table[$sid]['open_bal_val'] += ($open_sc_balance_val[$sc_date][$sid]['qty'] - $r['qty']) * $unit_cost;
							$table[$sid]['open_bal_val'] += $new_cost;
							//$table[$key]['sc_adj_cost'] += $table[$key]['sc_adj'] * $sc_balance_val[$sc_date][$sid]['cost'];
						}
						$con_multi->sql_freeresult();
						if($sessioninfo['u'] == 'admin'){
							//print "End Query qty before stock check: ".memory_get_usage()."<br>";
						}
					//}
		
					unset($open_sc_balance_sids, $open_sc_balance_val);
				}
				
				//---------------calculate adjustment for stock quantity
				if($sessioninfo['u'] == 'admin'){
					//print "Start Query qty stock check in middle: ".memory_get_usage()."<br>";
				}
				$date_start=date("Y-m-d", strtotime("+1 day",strtotime($from_date)));
				$date_end=$to_date;
				$sql = "select si.id as sid $extrasql, sc.date, sc.qty
						from stock_check sc
						left join sku_items si on sc.sku_item_code=si.sku_item_code
						where sc.branch_id=$bid and sc.date between ".ms($date_start)." and ".ms($date_end)." and $where_sid2
						order by sc.date desc";
				//print $sql."<br />";
	            $con_multi->sql_query($sql) or die(mysql_error());
				while($r = $con_multi->sql_fetchassoc()){
				    $sid = $r['sid'];
	//				print $sid."  =  ".$r['cost']."  <br />";
					//$sc_balance_sids[$r['date']][$sid]=$sid;
					$sc_balance_val[$r['date']][$sid]['qty']+=$r['qty'];
					//$table[$sid]['sc_adj_bal'] += $r['qty'];
					$sc_balance_val[$r['date']][$sid]['cost']+=$r['qty']*$r['cost'];
					//$table[$sid]['sc_adj_bal_val'] += $r['qty']*$r['cost'];
				}
				$con_multi->sql_freeresult();
				if($sessioninfo['u'] == 'admin'){
					//print "End Query qty stock check in middle: ".memory_get_usage()."<br>";
				}
				
				// get adjustment qty and value before stock check
				if($sc_balance_val){
					foreach ($sc_balance_val as $sc_date => $tmp_balance_val_list){
						$minus_1_day=strtotime("-1 day",strtotime($sc_date));
						$sb_year=date("Y",$minus_1_day);
						$sc1day_date=ms(date("Y-m-d",$minus_1_day));
						
						$sb_tbl="stock_balance_b$bid"."_".$sb_year;
					
						$sql = "select sc.sku_item_id as sid, sc.qty as qty
								from $sb_tbl sc
								right join sku_items si on si.id=sc.sku_item_id
								where $sc1day_date between sc.from_date and sc.to_date 
								and sc.sku_item_id in (".join(",", array_keys($tmp_balance_val_list)).")
								group by sid";
						//print $sql."<br />";
						$con_multi->sql_query($sql) or die(mysql_error());
						while($r = $con_multi->sql_fetchassoc()){
							$sid = $r['sid'];
		//					print $sc_date."    ".$sid."  =  ".$r['cost']."  <br />";
		
							if($sc_balance_val[$sc_date][$sid]['cost']){
								$unit_cost = $sc_balance_val[$sc_date][$sid]['cost']/$sc_balance_val[$sc_date][$sid]['qty'];
							}else{
								$unit_cost = $table[$sid]['closing_cost'];
							}
							
							$sc_qty = $sc_balance_val[$sc_date][$sid]['qty'];
							$qty_b4_sc = $r['qty'];
							
							$adj_qty = $sc_qty - $qty_b4_sc;
							$adj_cost = $unit_cost * $adj_qty;
							
							/*$table[$sid]['sc_adj_bal'] -= $r['qty'];
							if($sc_balance_val[$sc_date][$sid]['cost']){
								$unit_cost = $sc_balance_val[$sc_date][$sid]['cost']/($sc_balance_val[$sc_date][$sid]['qty'] - $r['qty']);
							}else{
								$unit_cost = $sku[$sid]['closing_cost'];
							}*/
							$table[$sid]['sc_adj_bal'] += $adj_qty;
							$table[$sid]['sc_adj_bal_val'] += $adj_cost;
						}
						$con_multi->sql_freeresult();	
					}
			
					unset($sc_balance_val);
				}
				//-------------------end check
			}
			
			if($config['consignment_modules']){
		        // CN get cn qty
				$q_cn =$con_multi->sql_query("select
					cni.sku_item_id as sid,
					sum(cni.ctn *uom.fraction + cni.pcs) as qty,
					if(cn.date>='$from_date',0,1) as bal,
		
					(cn.date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = cni.sku_item_id and sh.branch_id=$bid and sh.date <'$from_date')) as dont_count
		
					from cn_items cni
					left join uom on cni.uom_id=uom.id
					left join cn on cni.cn_id = cn.id and cni.branch_id = cn.branch_id
					where cn.to_branch_id=$bid and cn.date <='$to_date' and $where_sid and cn.active=1 and cn.status=1 and cn.approved=1 group by bal,dont_count, sid order by null") or die(mysql_error());

				while($r = $con_multi->sql_fetchassoc($q_cn)){
					if(!$r['dont_count']){
						$sid=$r['sid'];
						$qty=$r['qty'];

						if(!$r['bal']){
							$table[$sid]['cn_qty']+=$qty;
							$cn_amt = $qty * $table[$sid]['closing_cost'];
							$table[$sid]['cn_val']+=$cn_amt;
						}
					}
				}
				$con_multi->sql_freeresult($q_cn);

				// DN get dn qty
				$q_dn =$con_multi->sql_query("select
					cni.sku_item_id as sid,
					sum(cni.ctn *uom.fraction + cni.pcs) as qty,
					if(dn.date>='$from_date',0,1) as bal,
		
					(dn.date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = cni.sku_item_id and sh.branch_id=$bid and sh.date <'$from_date')) as dont_count
		
					from dn_items cni
					left join uom on cni.uom_id=uom.id
					left join dn on cni.dn_id = dn.id and cni.branch_id = dn.branch_id
					where dn.to_branch_id=$bid and dn.date <='$to_date' and $where_sid and dn.active=1 and dn.status=1 and dn.approved=1 group by bal,dont_count, sid order by null") or die(mysql_error());

				while($r = $con_multi->sql_fetchassoc($q_dn)){
					if(!$r['dont_count']){
						$sid=$r['sid'];
						$qty=$r['qty'];

						if(!$r['bal']){
							$table[$sid]['dn_qty']+=$qty;
							$dn_amt = $qty * $table[$sid]['closing_cost'];
							$table[$sid]['dn_val']+=$dn_amt;
						}
					}
				}
				$con_multi->sql_freeresult($q_dn);
			}
		}
		
		//print 'before:'.count($table).'<br />';
		/*
		print '<pre>';
		print_r($table);
		print '</pre>';
		*/

  		//recalculating
		foreach ($table as $sid => $data){		
			$data['info']= $table[$sid]['info'];

			$data['cost']=$table[$sid]['closing_cost'];
			$data['selling_price']=$table[$sid]['closing_selling'];
			$data['opening_selling_price']=$table[$sid]['opening_selling'];
			
			$data['pos_sell'] = $data['pos_qty'] * $data['selling_price'];
			$data['on_hand_qty'] = $data['open_bal'] + $data['sc_adj_bal'] + $data['rcv_qty'] + $data['adj_in'];
            //$data['on_hand_val'] = $data['on_hand_qty'] * $data['cost'];
			$data['on_hand_val'] = $data['open_bal_val'] + $data['sc_adj_bal_val'] + $data['rcv_val'] + $data['adj_in_val'];
			
			// recalculate the closing balance from accumulated cost
			if($config['stock_balance_use_accumulate_last_cost']){
				$data['closing_bal_val'] = $data['on_hand_val'] - $data['pos_val'] - $data['do_val'] - $data['adj_out_val'] - $data['gra_val'];
				
				if($config['consignment_modules']){
					$data['closing_bal_val'] += $data['cn_val'] - $data['dn_val'];
				}
				//$table[$sid]['closing_bal_val'] += $data['acc_bal_val'];
				//$data['closing_bal_val'] = $table[$sid]['closing_bal_val'];
			}
			
        	$data['on_hand_sel_val'] = $data['on_hand_qty'] * $data['selling_price'];
			$data['open_bal_sel_val'] = $data['open_bal'] * $data['opening_selling_price'];
			$data['closing_bal_sel_val'] = $data['closing_bal'] * $data['selling_price'];
            
            	unset($table[$sid]);
			if(!$data['open_bal']&&!$data['on_hand_qty']&&!$data['closing_bal']&&!$data['sc_bal']&&!$data['open_sc_adj']&&!$data['dn_val']&&!$data['cn_val']&&!$data['do_val']&&!$data['adj_out_val']&&!$data['pos_val']&&!$data['gra_val']) continue;
						
			if($branch_is_under_gst) {
                $output_gst = get_sku_gst("output_tax", $sid);
				$inclusive_tax = get_sku_gst("inclusive_tax", $sid);
                $prms = array();
                $prms['selling_price'] = $data['selling_price'];
                $prms['inclusive_tax'] = $inclusive_tax;
                $prms['gst_rate'] = $output_gst['rate'];
                $gst_sp_info = calculate_gst_sp($prms);
				
				$prms1 = array();
                $prms1['selling_price'] = $data['opening_selling_price'];
                $prms1['inclusive_tax'] = $inclusive_tax;
                $prms1['gst_rate'] = $output_gst['rate'];
                $opening_gst_sp_info = calculate_gst_sp($prms1);
                
                if($inclusive_tax == "yes") {
                    $data['selling_price_before_gst'] = $gst_sp_info['gst_selling_price'];
                    $data['opening_selling_price_before_gst'] = $opening_gst_sp_info['gst_selling_price'];
                }
			}
			            
			$selling_price_for_gp = (($data['selling_price_before_gst'])?$data['selling_price_before_gst']:$data['selling_price']);
			
			$data['gp'] = $selling_price_for_gp - $data['cost'];
			$data['gp_val'] = $data['gp'] * $data['pos_qty'];
			
			if($data['gp_val'])    $data['gp_per'] = ($data['pos_sell']/$data['gp_val'])*100;
			else    $data['gp_per'] = 0;

			if($selling_price_for_gp)	$data['gp_per'] = ($data['gp']/$selling_price_for_gp)*100;
			else    $data['gp_per'] = 0;

			if($data['pos_val']){
				$data['turnover'] = ($data['closing_bal_val'] / $data['pos_val']) * $no_of_days;
				
				$data['total_bal'] = $data['closing_bal_val'] + $data['open_bal_val'];
				if($data['total_bal'] != 0) {
					$data['turnover_ratio'] = ($data['pos_val']) / ($data['total_bal']/2);
				}
				if($data['turnover_ratio'] && $data['turnover_ratio'] != 0 ){
					$data['turnover_days'] = 365/$data['turnover_ratio'];
				}
			}
			$table[$sid] = $data;
		}

		/*
		print 'after:'.count($table).'<br />';
		print '<pre>';
		print_r(array_keys($table));
		print '</pre>';
		*/
		
		if (!$group_by_sku) {			
			
			//get sku items details (desc, code, etc..), 5000 at a time
			$sid_list3 = array_chunk(array_keys($table),5000);
			foreach ($sid_list3 as $sl3) {
				$sql_sid = "select si.id, si.sku_item_code, si.mcode, si.description, si.artno, ifnull(sic.changed,0) as changed,
                si.input_tax,si.output_tax
                from sku_items si
                left join sku_items_cost sic on si.id = sic.sku_item_id and sic.branch_id = $bid
                where id in (".join(',',$sl3).")";
				$q_sid = $con_multi->sql_query($sql_sid);
				
				while ($r = $con_multi->sql_fetchassoc($q_sid)) {
					$sid=$r['id'];
					$table[$sid]['info']['artno'] = $r['artno'];
					$table[$sid]['info']['mcode'] = $r['mcode'];
					$table[$sid]['info']['sku_item_code'] = $r['sku_item_code'];
					$table[$sid]['info']['description'] = $r['description'];
					$table[$sid]['info']['changed'] = $r['changed'];
                    $table[$sid]['info']['input_tax'] = get_sku_gst('input_tax',$sid);
                    $table[$sid]['info']['output_tax'] = get_sku_gst('output_tax',$sid);
				}
				$con_multi->sql_freeresult($q_sid);
			}
		}
		
		//echo "<pre>";print_r($table);echo "</pre>";
		//print_r($temp_sku);
	    //print_r($temp);

			if($table){
			
				if ($group_by_sku) {
				
					$temp_sku_id = array();
					
					//get sku_id and packing_uom_fraction
					$sid_list4 = array_chunk(array_keys($table),5000);
					foreach ($sid_list4 as $sl4) {
						$sql_skuid = "select si.id, si.sku_id, uom.fraction as uom_fraction from sku_items si left join uom on si.packing_uom_id = uom.id where si.id in (".join(',',$sl4).")";
						$q_skuid = $con_multi->sql_query($sql_skuid);
						
						while ($r = $con_multi->sql_fetchassoc($q_skuid)) {
							$sid=$r['id'];
							$temp_sku_id[$r['sku_id']] = true;
							$table[$sid]['info']['sku_id']=$r['sku_id'];
							$table[$sid]['info']['packing_uom_fraction']=$r['uom_fraction'];
						}
						$con_multi->sql_freeresult($q_skuid);
					}
					
					$sid_list5 = array_chunk(array_keys($temp_sku_id),5000);
					foreach ($sid_list5 as $sl5) {
						$sql_parent = "select sku_id,artno,mcode,sku_item_code,description from sku_items where sku_id in (".join(',',$sl5).") and is_parent=1";
						$q_parent = $con_multi->sql_query($sql_parent);
						
						while ($r = $con_multi->sql_fetchassoc($q_parent)) {
							$group_data[$r['sku_id']]['info']=$r;
						}
						$con_multi->sql_freeresult($q_parent);
					}
				}
			
				foreach($table as $k=>$r){
				    //$sku_item_code = $table[$k]['info']['sku_item_code'];
				    $sku_item_code = $r['info']['sku_item_code'];
				    $uom = 1;
	                if($group_by_sku)   $uom = mf($table[$k]['info']['packing_uom_fraction']);

					if($r['sc_date']){
						$total['sc_bal'] += $r['sc_bal'];
						$total['sc_bal_val'] += $r['sc_bal_val'];
						$total['sc_adj_bal'] += $r['sc_adj_bal'];
						$total['sc_adj_bal_val'] += $r['sc_adj_bal_val'];
					}

					$total['open_sc_adj']+=$r['open_sc_adj'];
					$total['open_bal']+=$r['open_bal']*$uom;
					$total['open_bal_val']+=$r['open_bal_val'];
					$total['open_bal_sel_val']+=$r['open_bal_sel_val'];
					$total['rcv_qty']+=$r['rcv_qty']*$uom;
					$total['rcv_val']+=$r['rcv_val'];
					
					if($this->use_grn){
						$total['rcv_vendor_qty']+=$r['rcv_vendor_qty']*$uom;
						$total['rcv_vendor_val']+=$r['rcv_vendor_val'];
					}
					
					$total['adj_in']+=$r['adj_in']*$uom;
					$total['adj_in_val']+=$r['adj_in_val'];
					$total['adj_out']+=$r['adj_out']*$uom;
					$total['adj_out_val']+=$r['adj_out_val'];
					$total['on_hand_qty']+=$r['on_hand_qty']*$uom;
					$total['on_hand_val']+=$r['on_hand_val'];
					$total['on_hand_sel_val']+=$r['on_hand_sel_val'];
					
					$total['pos_qty']+=$r['pos_qty']*$uom;
					$total['pos_val']+=$r['pos_val'];
					$total['pos_sell'] += $r['pos_sell'];

					$total['gra_qty']+=$r['gra_qty']*$uom;
					$total['gra_val']+=$r['gra_val'];
					$total['do_qty']+=$r['do_qty']*$uom;
					$total['do_val']+=$r['do_val'];

					// cn
					$total['cn_qty']+=$r['cn_qty']*$uom;
					$total['cn_val']+=$r['cn_val'];
					// dn
					$total['dn_qty']+=$r['dn_qty']*$uom;
					$total['dn_val']+=$r['dn_val'];

					$total['closing_bal']+=$r['closing_bal']*$uom;
					$total['closing_bal_val']+=$r['closing_bal_val'];
					$total['closing_bal_sel_val']+=$r['closing_bal_sel_val'];
					//$total['pos_qty'] += $r['pos_qty'];

					$total['gp_val'] += $r['gp_val'];
                    
                    if($r['pos_qty']) {
                        $total['gp'] += $r['gp'];
                        $total['selling_price'] += ($r['selling_price_before_gst'])?$r['selling_price_before_gst']:$r['selling_price'];
                    }

					if($total['pos_val']){
						$total['turnover'] = ($total['closing_bal_val'] / $total['pos_val']) * $no_of_days;
					
						$total['total_bal'] = $total['closing_bal_val'] + $total['open_bal_val'];
						if($total['total_bal']!=0){
							$total['turnover_ratio'] = $total['pos_val'] / ($total['total_bal']/2);
						}
						
						if($total['turnover_ratio'] && $total['turnover_ratio'] != 0){
							$total['turnover_days'] = 365/$total['turnover_ratio'];
						}
					}
				}
			}
			
			//print "p3 : ".(memory_get_usage()/1024/1024)."<br />";

			//if($total['gp_val'])    $total['gp_per'] = ($total['pos_sell']/$total['gp_val'])*100;
			if($total['selling_price'])	$total['gp_per'] = ($total['gp']/$total['selling_price'])*100;
			else    $total['gp_per'] = 0;

		// additional group calculation
		if($group_by_sku&&$table){
		
			foreach($table as $sid=>$r){
				$sku_id = mi($table[$sid]['info']['sku_id']);
				$uom = mf($table[$sid]['info']['packing_uom_fraction']);
				$sku_item_code = $table[$sid]['sku_item_code'];

				$group_data[$sku_id]['ttl_uom_fraction'] += $uom;
				$group_data[$sku_id]['open_bal'] += $r['open_bal']*$uom;
				$group_data[$sku_id]['open_bal_val'] += $r['open_bal_val'];
				$group_data[$sku_id]['open_bal_sel_val'] += $r['open_bal_sel_val'];
				$group_data[$sku_id]['ttl_opening_selling'] += $r['opening_selling'];
				$group_data[$sku_id]['open_sc_adj'] += $r['open_sc_adj'];
				
				if($r['sc_date']){
				    //$group_data[$sku_id]['sc_date'][$sku_item_code]['sc_date'] = $r['sc_date'];
				    //$group_data[$sku_id]['sc_date'][$sku_item_code]['sc_bal'] = $r['sc_bal']*$uom;
				    //$group_data[$sku_id]['sc_date'][$sku_item_code]['sc_bal_val'] = $r['sc_bal_val'];
				    //$group_data[$sku_id]['sc_date'][$sku_item_code]['sc_adj_bal'] = $r['sc_adj_bal']*$uom;
				    //$group_data[$sku_id]['sc_date'][$sku_item_code]['sc_adj_bal_val'] = $r['sc_adj_bal_val'];
				    if(!$group_data[$sku_id]['last_sc_date'] || $r['sc_date'] > $group_data[$sku_id]['last_sc_date'])	$group_data[$sku_id]['last_sc_date'] = $r['sc_date'];
	                $group_data[$sku_id]['sc_bal'] += $r['sc_bal']*$uom;
					$group_data[$sku_id]['sc_bal_val'] += $r['sc_bal_val'];
	                $group_data[$sku_id]['sc_adj_bal'] += $r['sc_adj_bal']*$uom;
					$group_data[$sku_id]['sc_adj_bal_val'] += $r['sc_adj_bal_val'];
					$group_data[$sku_id]['got_sc'] = true;
				}

				$group_data[$sku_id]['rcv_qty'] += $r['rcv_qty']*$uom;
				$group_data[$sku_id]['rcv_val'] += $r['rcv_val'];
				
				if($this->use_grn){
					$group_data[$sku_id]['rcv_vendor_qty'] += $r['rcv_vendor_qty']*$uom;
					$group_data[$sku_id]['rcv_vendor_val'] += $r['rcv_vendor_val'];
				}
				$group_data[$sku_id]['adj_in'] += $r['adj_in']*$uom;
				$group_data[$sku_id]['adj_in_val'] += $r['adj_in_val'];
				$group_data[$sku_id]['adj_out'] += $r['adj_out']*$uom;
				$group_data[$sku_id]['adj_out_val'] += $r['adj_out_val'];
				$group_data[$sku_id]['on_hand_qty'] += $r['on_hand_qty']*$uom;
				$group_data[$sku_id]['on_hand_val'] += $r['on_hand_val'];
				
				// recalculate the closing balance from accumulated cost
				/*if($config['stock_balance_use_accumulate_last_cost']){
					$group_data[$sku_id]['closing_bal_val'] = $r['on_hand_val'] - $r['pos_val'] - $r['do_val'] - $r['adj_out_val'];
					
					if($config['consignment_modules']){
						$group_data[$sku_id]['closing_bal_val'] += $r['cn_val'] - $r['dn_val'];
					}
					//$group_data[$sku_id]['closing_bal_val'] += $r['acc_bal_val'];
					$r['closing_bal_val'] = $group_data[$sku_id]['closing_bal_val'];
				}*/
				
				$group_data[$sku_id]['on_hand_sel_val'] += $r['on_hand_sel_val'];
				
				$group_data[$sku_id]['pos_qty'] += $r['pos_qty']*$uom;
				$group_data[$sku_id]['pos_val'] += $r['pos_val'];
	            $group_data[$sku_id]['pos_sell'] += $r['pos_sell'];

				$group_data[$sku_id]['gra_qty'] += $r['gra_qty']*$uom;
				$group_data[$sku_id]['gra_val'] += $r['gra_val'];
				$group_data[$sku_id]['do_qty'] += $r['do_qty']*$uom;
				$group_data[$sku_id]['do_val'] += $r['do_val'];

				// cn
				$group_data[$sku_id]['cn_qty'] += $r['cn_qty']*$uom;
				$group_data[$sku_id]['cn_val'] += $r['cn_val'];

				// dn
				$group_data[$sku_id]['dn_qty'] += $r['dn_qty']*$uom;
				$group_data[$sku_id]['dn_val'] += $r['dn_val'];

				$group_data[$sku_id]['closing_bal'] += $r['closing_bal']*$uom;
				$group_data[$sku_id]['closing_bal_val'] += $r['closing_bal_val'];
				$group_data[$sku_id]['closing_bal_sel_val'] += $r['closing_bal_sel_val'];
				$group_data[$sku_id]['ttl_closing_selling'] += $r['closing_selling'];

				$group_data[$sku_id]['gp'] += $r['gp'];
				$group_data[$sku_id]['gp_val'] += $r['gp_val'];
				//if($group_data[$sku_id]['gp_val'])  $group_data[$sku_id]['gp_per'] = ($group_data[$sku_id]['pos_sell']/$group_data[$sku_id]['gp_val'])*100;
			}

			foreach($group_data as $sku_id=>$r){
			    // unset if group don't have data
				if(!$r['open_bal']&&!$r['on_hand_qty']&&!$r['closing_bal']&&!$r['sc_adj_bal']&&!$r['sc_bal']&&!$r['open_sc_adj']){
	                unset($group_data[$sku_id]);
	                continue;
				}
				
				if($r['open_bal']){
					//$group_data[$sku_id]['opening_selling_price'] = $r['open_bal_sel_val']/$r['open_bal'];
					
					// new calculation, but decision made by tommy on 2017-10-16 that do not show avg selling price when group by SKU
					$group_data[$sku_id]['opening_selling_price'] = $group_data[$sku_id]['ttl_opening_selling'] / $group_data[$sku_id]['ttl_uom_fraction'];
				}

				if($r['closing_bal']){
	                $group_data[$sku_id]['cost'] = $r['closing_bal_val']/$r['closing_bal'];
	                //$group_data[$sku_id]['selling_price'] = $r['closing_bal_sel_val']/$r['closing_bal'];
					
					// new calculation, but decision made by tommy on 2017-10-16 that do not show avg selling price when group by SKU
	                //$group_data[$sku_id]['selling_price'] = $group_data[$sku_id]['ttl_closing_selling'] / $group_data[$sku_id]['ttl_uom_fraction'];
	                if($group_data[$sku_id]['selling_price'])  $group_data[$sku_id]['gp_per'] = ($group_data[$sku_id]['gp']/$group_data[$sku_id]['selling_price'])*100;
				}

				if($group_data[$sku_id]['pos_val']){
					$group_data[$sku_id]['turnover'] = ($group_data[$sku_id]['closing_bal_val'] / $group_data[$sku_id]['pos_val']) * $no_of_days;
				
					$group_data[$sku_id]['total_bal'] = $group_data[$sku_id]['closing_bal_val'] + $group_data[$sku_id]['open_bal_val'];
					if($group_data[$sku_id]['total_bal']!= 0){
						$group_data[$sku_id]['turnover_ratio'] = $group_data[$sku_id]['pos_val'] / ($group_data[$sku_id]['total_bal']/2);
					}
					
					if($group_data[$sku_id]['turnover_ratio'] && $group_data[$sku_id]['turnover_ratio'] != 0){
						$group_data[$sku_id]['turnover_days'] = 365/$group_data[$sku_id]['turnover_ratio'];
					}
				}
			}
		}

		if($group_by_sku or $hq_cost){
	        if(trim($_REQUEST['sort_by'])!='')	usort($group_data, array($this, "sort_table"));
/*	        
			if($group_data){    // filter those zero qty
				foreach($group_data as $sku_id=>$r){
					if(!$r['closing_bal']){  // no closing balance
						unset($group_data[$sku_id]);
					}
				}
			}
*/
			$smarty->assign('table', $group_data);
		}else{
			//print_r($table);
	        if(trim($_REQUEST['sort_by'])!='')	usort($table, array($this, "sort_table"));
			$smarty->assign('table', $table);
		}

		//print $_REQUEST['sort_by'];
		//print_r($group_data);die();
		//print_r($stock_check_info);
		//print_r($total);
		
		$smarty->assign("items", $data);
		$smarty->assign("total", $total);

		$smarty->assign('group_info', $group_info);
		$smarty->assign("bid", $branch_id);
		$smarty->assign('got_opening_sc', $got_opening_sc);
		$smarty->assign('got_stock_check',$got_stock_check);
		$smarty->assign("branch_is_under_gst", $branch_is_under_gst);
		//$con_multi->close_connection();
		
		//print "p4 (end) : ".(memory_get_usage()/1024/1024)."<br />";
	}
	
	function sort_table($a,$b)
	{
		$col = $_REQUEST['sort_by'];
	    if ($a['info'][$col]==$b['info'][$col]) return 0;
	    return ($a['info'][$col]>$b['info'][$col]) ? 1 : -1;
	}

	// unused function.....
	/*function get_stock_balance_by_item($from_date, $to_date, $sku_item_id, $sku_item_code, $branch_id, $temp_uom, $group_by_sku = false, $hq_cost = false, $only_stock_check = false){
		global $con, $config;

		$default_from_Date = $from_date;
		if ($_REQUEST['hq_cost']) $extrasql = ", si.hq_cost as cost";

		$sql = "select sc.cost $extrasql , sc.date, sc.qty from sku_items si, stock_check sc where si.sku_item_code=sc.sku_item_code and sc.branch_id=$branch_id and sc.sku_item_code=".ms($sku_item_code)." and date=(select max(date) from stock_check where branch_id=$branch_id and sku_item_code=".ms($sku_item_code)." and date between ".ms($from_date)." and ".ms($to_date).")";
	 //	print $sql."<br>";
		$q_sc = $con->sql_query($sql) or die(mysql_error());
		if($con->sql_numrows($q_sc)>0){
	        while($r = $con->sql_fetchrow($q_sc)){

	            $data['sc_cost'] = $r['cost'];

				$data['sc_date'] = $r['date'];
				//$data['history_bal'] += $r['qty'];
				$data['sc_bal'] += $r['qty'];
			}
			$con->sql_freeresult($q_sc);

			$from_date = $data['sc_date'];
		}
	}*/
	
	private function check_table($table)
	{
      global $con;

		/*
		$sql_check="create table if not exists $table (
		sku_item_id int not null,
		from_date date,
		to_date date,
		qty double,
		cost double,
		avg_cost double,
		is_latest tinyint(1),
		start_qty double,
		index(sku_item_id),index(from_date),index(to_date),index(is_latest)
		)";
		$con->sql_query($sql_check) or die(mysql_error());*/
	}
}

$Stock_Balance = new Stock_Balance('Stock Balance Report by Department');
?>
