<?php
/*
2/26/2019 5:48 PM Andy
- Enhanced the report to show item Old Code.
- Fixed report should not show input tax and output tax if gst is not turn on.

2/24/2020 10:19 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

6/11/2020 2:26 PM Andy
- Fixed stock take adjust wrong.

3/2/2021 4:20 PM Ian
- Modified the sql by adding selection to Department ,Size ,Color ,Category 1,2,3
- Inject the data to the templates
- Made compatible with group by parent SKU.

5/3/2021 11:30 PM Ian
-Adding "Brand"
*/
include("include/common.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");
$maintenance->check(119);

ini_set('memory_limit', '1024M');
set_time_limit(0);

class Closing_Stock_By_SKU_Report extends Module{
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
		$q1 = $con_multi->sql_query("select * from sku_type") or die(mysql_error());
		$smarty->assign("sku_type", $con_multi->sql_fetchrowset($q1));
		$con_multi->sql_freeresult($q1);

		// show department option
		if ($sessioninfo['level'] < 9999){
			$depts = "id in (".$sessioninfo['department_ids'].")";
		}
		else{
			$depts = 1;
		}
		$q1 = $con_multi->sql_query("select id, description from category where active and level = 2 and $depts order by description");
		$smarty->assign("dept", $con_multi->sql_fetchrowset($q1));
		$con_multi->sql_freeresult($q1);

		// show brand option
		$br = ($sessioninfo['brands']) ? "id in (".join(",",array_keys($sessioninfo['brands'])).") and" : "";
		$q1 = $con_multi->sql_query("select id, description from brand where $br active order by description") or die(mysql_error());
		$smarty->assign("brand", $con_multi->sql_fetchrowset($q1));
		$con_multi->sql_freeresult($q1);
		
		$smarty->assign("brand_groups", get_brand_group());

		//show vendor option
		if ($sessioninfo['vendors']){
			$vd = "and id in (".join(",",array_keys($sessioninfo['vendors'])).")";
		}
		$q1 = $con_multi->sql_query("select id, description from vendor where active $vd order by description") or die(mysql_error());;
		$smarty->assign("vendor", $con_multi->sql_fetchrowset($q1));
		$con_multi->sql_freeresult($q1);

		//show date
		if (!$_REQUEST['date']) $_REQUEST['date'] = date('Y-m-d');

		$smarty->assign("PAGE_TITLE", $title);

		//check input data
		if (BRANCH_CODE == 'HQ')
			$this->bid = mi($_REQUEST['branch_id']);
		else
			$this->bid = $sessioninfo['branch_id'];

		if($config['enable_gst']){
			$q1 = $con_multi->sql_query("select * from gst where active=1");

			while($r = $con_multi->sql_fetchassoc($q1)){
				if($r['type'] == "purchase"){
					$this->input_tax_list[$r['id']] = $r;
				}else{
					$this->output_tax_list[$r['id']] = $r;
				}
			}
			$con_multi->sql_freeresult($q1);

			$smarty->assign("input_tax_list", $this->input_tax_list);
			$smarty->assign("output_tax_list", $this->output_tax_list);
			
			$this->sql_join_gst = " left join gst input_gst on input_gst.id=if(if(si.input_tax<0,sku.mst_input_tax,si.input_tax)<0,ccache.input_tax,if(si.input_tax<0,sku.mst_input_tax,si.input_tax))
									left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,ccache.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax)) ";
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
	    global $sessioninfo,$smarty,$con, $config, $con_multi;

		if($err){
			$smarty->assign("err", $err);
			$this->_default();
		}
		
		//for printing purpose
		$q1 = $con_multi->sql_query("select * from branch where id=".$this->bid) or die(mysql_error());
		$r0=$con_multi->sql_fetchassoc($q1);
		$con_multi->sql_freeresult($q1);
		$smarty->assign("p_branch",$r0);

		//report title
		$report_header = array();
		$report_header[] = "Date: ".$_REQUEST['date'];

		if ($_REQUEST['vendor_id']){
			$q1 = $con_multi->sql_query("select description from vendor where id=".mi($_REQUEST['vendor_id'])) or die(mysql_error());
			$v = $con_multi->sql_fetchassoc($q1);
			$con_multi->sql_freeresult($q1);
			$report_header[] = "Vendor: ".$v['description'];
		}
		else $report_header[] = "Vendor: All";

		if ($_REQUEST['category_id']){
			$report_header[] = "Category: $_REQUEST[category_tree]";
		}
		else{
			$report_header[] = "Category: All";
		}

		$report_header[] = "Brand: ".get_brand_title($_REQUEST['brand_id']);

		if($_REQUEST['blocked_po']){
			$report_header[] = "Blocked Item in PO: ".ucwords($_REQUEST['blocked_po']);
		}

		
		if(!$_REQUEST['status']) $status = "Inactive";
		elseif($_REQUEST['status'] == 1) $status = "Active";
		else $status = ucwords($_REQUEST['status']);

		$report_header[] = "Status: ".$status;
		
		if($_REQUEST['input_tax_filter'] && $config['enable_gst']) $report_header[] = "Input Tax: ".$this->input_tax_list[$_REQUEST['input_tax_filter']]['code']." (".mi($this->input_tax_list[$_REQUEST['input_tax_filter']]['rate'])."%)";
		if($_REQUEST['output_tax_filter'] && $config['enable_gst']) $report_header[] = "Output Tax: ".$this->output_tax_list[$_REQUEST['output_tax_filter']]['code']." (".mi($this->output_tax_list[$_REQUEST['output_tax_filter']]['rate'])."%)";
		
		if($_REQUEST['use_grn'])	$this->use_grn = true;
		if($_REQUEST['use_hq_grn'])	$this->use_hq_grn = true;
		if($this->use_grn && $this->use_hq_grn)	$this->use_grn = false;	// if both also true, use HQ GRN
		
		
		$smarty->assign("title", "Stock Balance Report (".join(" / ", $report_header).")");

		$this->generate_report();

		$this->display();
	}

	function generate_report(){
		global $con, $smarty, $sessioninfo, $LANG, $config, $con_multi;

		/*$con_multi= new mysql_multi();
		if(!$con_multi){
			die("Error: Fail to connect report server");
		}*/

		$date=$_REQUEST['date'];
		$date_timestamp = date('Y-m-d', strtotime("+1 day", strtotime($date)));
		
		$blocked_po = trim($_REQUEST['blocked_po']);
		$status = trim($_REQUEST['status']);
		$vendor_id = $_REQUEST['vendor_id'];
		$input_tax_filter = $_REQUEST['input_tax_filter'];
		$output_tax_filter = $_REQUEST['output_tax_filter'];
		$bid = $this->bid;

        if($config['enable_gst']){
            $prms = array();
            $prms['branch_id'] = $bid;
            $prms['date'] = $date;
            $branch_is_under_gst = check_gst_status($prms);
        }
		
		// FILTER view latest sku items added
		$where = array();
		
		if (isset($_REQUEST['all_category']))
		{
			// all catgory, no filter
		}
		elseif ($_REQUEST['category_id'] > 0){
			$q1 = $con_multi->sql_query("select category_cache.*, category.level,category.description as cname from category_cache left join category on category_id = category.id where category_id=".mi($_REQUEST['category_id'])) or die(mysql_error());
			$ccache = $con_multi->sql_fetchassoc($q1);
			$con_multi->sql_freeresult($q1);
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

		if($_REQUEST['show_more_info']){
			$show_more_info=1;
		}
		
		if($status != "all") $where[] = "si.active = ".mi($status);

		$where[] = "((sku.no_inventory='inherit' and ccache.no_inventory='no') or sku.no_inventory='no')";

		// table for stock balance - to
        list($y,$m,$d) = explode("-",$date);
        $tbl_sb = "stock_balance_b".mi($bid)."_".mi($y);
		$prms = array();
		$prms['tbl'] = $tbl_sb;
		initial_branch_sb_table($prms);

		//add HQ Cost
		if ($_REQUEST['hq_cost']) $extrasql = ", si.hq_cost as cost";
		else    $extrasql= ", ifnull(sb.cost,si.cost_price) as cost";

		$bid_use_grn = 0;
		if($this->use_grn){	// it is using grn
			$bid_use_grn = $bid;
		}elseif($this->use_hq_grn){	// using hq grn
			$bid_use_grn = 1;
		}
		if($bid_use_grn){
			$where[] = "si.id in (select vsh.sku_item_id from vendor_sku_history_b".$bid_use_grn." vsh where vendor_id=$vendor_id and (".ms($date)." between vsh.from_date and vsh.to_date))";
		}
		
		if($input_tax_filter && $config['enable_gst']) $where[] = 'input_gst.id = '.mi($input_tax_filter);
		if($output_tax_filter && $config['enable_gst']) $where[] = 'output_gst.id = '.mi($output_tax_filter);
		
		$where = join(" and ", $where);
		if (!$where) $where=1;
		
		$sql = "select si.id, sb.qty as sb_to $extrasql,si.selling_price
				from sku_items si
				left join sku on si.sku_id=sku.id
				left join $tbl_sb sb on sb.sku_item_id=si.id and ((".ms($date)."
					between sb.from_date and sb.to_date))
				left join category_cache ccache on ccache.category_id=sku.category_id
				left join uom u1 on u1.id=si.packing_uom_id
				".$this->sql_join_gst."
				where $where";

		$q1=$con_multi->sql_query($sql) or die(mysql_error());

		//Opening and Closing xxx
		while($r=$con_multi->sql_fetchassoc($q1)){
		    $sid=$r['id'];
			
			$table[$sid]['closing_selling']=$r['selling_price'];
			$table[$sid]['closing_cost']=$r['cost'];
			
			$table[$sid]['closing_bal'] += $r['sb_to'];
			$table[$sid]['closing_bal_val'] += ($r['sb_to']*$r['cost']);
		}
		$con_multi->sql_freeresult($q1);

		if (!$table) return false;

		//check stock check
		$got_stock_check = false;

		//avoid mysql error if too much data
		$sid_count = count($table);
		for($i=0; $i<$sid_count; $i+=5000){
			$sid_list2 = array_keys(array_slice($table, $i, 5000, true));
			$where_sid = "sku_item_id in (" . join(",", $sid_list2) . ")";
			$where_sid2 = "si.id in (" . join(",", $sid_list2) . ")";
			
			//Closing price
			$q3=$con_multi->sql_query("select siph.*, siph.sku_item_id as sid,
									   (select max(added) from sku_items_price_history sh where sh.sku_item_id = sid and sh.branch_id=$bid and sh.added <'$date_timestamp') as price_added
									   from
									   sku_items_price_history siph
									   left join sku_items on siph.sku_item_id = sku_items.id
									   where branch_id=$bid and siph.added < '$date_timestamp' and $where_sid
									   having price_added = siph.added order by null") or die(mysql_error());

			while($r3=$con_multi->sql_fetchassoc($q3)){
				if($r3['price']>0){
				    $sid = $r3['sid'];
					$table[$sid]['closing_selling']=$r3['price'];
				}
			}
			$con_multi->sql_freeresult($q3);

			//Closing cost
			if (!$_REQUEST['hq_cost']){
				$q_cc = $con_multi->sql_query("select sich.date,sich.sku_item_id as sid , ifnull(sich.grn_cost,sku_items.cost_price) as grn_cost,
											   (select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=$bid and sh.date <='$date') as stock_date
											   from
											   sku_items_cost_history sich
											   left join sku_items on sich.sku_item_id = sku_items.id
											   where branch_id=$bid and sich.date <= '$date' and sich.date > 0 and $where_sid
											   having stock_date=sich.date order by null ") or die(mysql_error());

				while($r = $con_multi->sql_fetchassoc($q_cc)){
				    $sid = $r['sid'];
		            $table[$sid]['closing_cost'] = $r['grn_cost'];
				}
				$con_multi->sql_freeresult($q_cc);
			}
			
	  		if ($_REQUEST['hq_cost']) $extrasql = ", si.hq_cost as cost";
	  		else $extrasql = ", sc.cost as cost";

			// check if got stock take at closing	
			$sql = "select si.id as sid $extrasql, sc.qty, sc.date
					from stock_check sc
					right join sku_items si on si.sku_item_code=sc.sku_item_code
					where sc.branch_id=$bid and sc.date = ".ms($date_timestamp)." and $where_sid2";

			$q1 = $con_multi->sql_query($sql) or die(mysql_error());
			$closing_sc_balance_val = array();
			while($r = $con_multi->sql_fetchassoc($q1)){
				$sid = $r['sid'];
				
				$closing_sc_balance_val[$sid]['qty']+=$r['qty'];
				$closing_sc_balance_val[$sid]['cost']+=$r['qty']*$r['cost'];
				
				$got_closing_sc = true;
			}
			$con_multi->sql_freeresult($q1);

			// get closing stock balance qty and value before stock check
			if($closing_sc_balance_val){
				$minus_1_day=strtotime("-1 day",strtotime($date_timestamp));
				$sb_year=date("Y",$minus_1_day);
				$sc1day_date= date("Y-m-d",$minus_1_day);
				
				$sb_tbl="stock_balance_b$bid"."_".$sb_year;

				$sql = "select si.id as sid, sc.qty
						from sku_items si
						left join $sb_tbl sc on si.id=sc.sku_item_id and ".ms($sc1day_date)." between sc.from_date and sc.to_date 
						where si.id in (".join(",", array_keys($closing_sc_balance_val)).")
						group by sid";

				$q1 = $con_multi->sql_query($sql) or die(mysql_error());
				while($r = $con_multi->sql_fetchassoc($q1)){
					$sid = $r['sid'];
					$table[$sid]['closing_bal_val'] = 0;
					
					$closing_sc_adj_qty = $closing_sc_balance_val[$sid]['qty'] - $r['qty'];
					
					$table[$sid]['closing_sc_adj'] += $closing_sc_adj_qty;
					$table[$sid]['closing_bal'] += $closing_sc_adj_qty;
					
					$new_cost = 0;
					if($closing_sc_balance_val[$sid]['cost']){
						$new_cost = $closing_sc_balance_val[$sid]['cost'];
					}else{
						$new_cost = $table[$sid]['cost']*$closing_sc_balance_val[$sid]['qty'];
					}
					$table[$sid]['closing_bal_val'] += $new_cost;
				}
				$con_multi->sql_freeresult($q1);
	
				unset($closing_sc_balance_val);
			}
			//-------------------end check
		}

  		//recalculating
		foreach ($table as $sid => $data){		
			$data['info']= $table[$sid]['info'];
			$data['cost']=$table[$sid]['closing_cost'];
			$data['selling_price']=$table[$sid]['closing_selling'];
			$data['closing_bal_sel_val'] = $data['closing_bal'] * $data['selling_price'];

			unset($table[$sid]);
			if(!$data['closing_bal'] && !$data['closing_sc_adj']) continue;
			
			if($branch_is_under_gst) {
                $output_gst = get_sku_gst("output_tax", $sid);
				$inclusive_tax = get_sku_gst("inclusive_tax", $sid);
                $prms = array();
                $prms['selling_price'] = $data['selling_price'];
                $prms['inclusive_tax'] = $inclusive_tax;
                $prms['gst_rate'] = $output_gst['rate'];
                $gst_sp_info = calculate_gst_sp($prms);
                
                if($inclusive_tax == "yes") {
                    $data['selling_price_before_gst'] = $gst_sp_info['gst_selling_price'];
                }
            }
			
			$table[$sid] = $data;
		}
		
		if (!$group_by_sku) {
			//get sku items details (desc, code, etc..), 5000 at a time
			$sid_list3 = array_chunk(array_keys($table),5000);
			foreach ($sid_list3 as $sl3) {
				$sql_sid = "select si.id, si.sku_item_code, si.mcode, si.link_code, si.description, si.artno, ifnull(sic.changed,0) as changed,
                si.input_tax,si.output_tax , si.size, si.color ,dept.description as dept_desc,c1.description as c1_desc,c2.description as c2_desc,c3.description as c3_desc ,b.description as b_desc
                from sku_items si
				left join sku on si.sku_id  = sku.id
				left join brand b on sku.brand_id=b.id
				left join category_cache cc on cc.category_id =sku.category_id
			    left join category dept on dept.id =cc.p2
				left join category c1 on c1.id =cc.p3
				left join category c2 on c2.id =cc.p4
				left join category c3 on c3.id =cc.p5
                left join sku_items_cost sic on si.id = sic.sku_item_id and sic.branch_id = $bid
                where si.id in (".join(',',$sl3).")";
				$q_sid = $con_multi->sql_query($sql_sid);
				
				while ($r = $con_multi->sql_fetchassoc($q_sid)) {
					$sid=$r['id'];
					$table[$sid]['info']['artno'] = $r['artno'];
					$table[$sid]['info']['mcode'] = $r['mcode'];
					$table[$sid]['info']['link_code'] = $r['link_code'];
					$table[$sid]['info']['sku_item_code'] = $r['sku_item_code'];
					$table[$sid]['info']['description'] = $r['description'];					
					$table[$sid]['info']['changed'] = $r['changed'];
				
					if($show_more_info)
					{
						$table[$sid]['info']['size'] = $r['size'];
						$table[$sid]['info']['color'] = $r['color'];	
						$table[$sid]['info']['dept_desc'] = $r['dept_desc'];
						$table[$sid]['info']['c1_desc'] = $r['c1_desc'];	
						$table[$sid]['info']['c2_desc'] = $r['c2_desc'];
						$table[$sid]['info']['c3_desc'] = $r['c3_desc'];	
						$table[$sid]['info']['b_desc'] = $r['b_desc'];					
					}
					
					
					if($config['enable_gst']){
						$table[$sid]['info']['input_tax'] = get_sku_gst('input_tax',$sid);
						$table[$sid]['info']['output_tax'] = get_sku_gst('output_tax',$sid);
					}                    
				}
				$con_multi->sql_freeresult($q_sid);
			}
		}

		if($table){
			if ($group_by_sku) {
				$temp_sku_id = array();
				
				//get sku_id and packing_uom_fraction
				$sid_list4 = array_chunk(array_keys($table),5000);
				foreach ($sid_list4 as $sl4) {
					$sql_skuid = "select si.id, si.sku_id, uom.fraction as uom_fraction from sku_items si 		
					left join uom on si.packing_uom_id = uom.id where si.id in (".join(',',$sl4).")";
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
					$sql_parent = "select sku_id,artno,mcode,link_code,sku_item_code,sku_items.description 
					,size, color ,dept.description as dept_desc,c1.description as c1_desc,
					c2.description as c2_desc,c3.description as c3_desc,b.description as b_desc from sku_items 
					left join sku on sku_items.sku_id  = sku.id
					left join brand b on sku.brand_id=b.id
					left join category_cache cc on cc.category_id =sku.category_id
					left join category dept on dept.id =cc.p2
					left join category c1 on c1.id =cc.p3
					left join category c2 on c2.id =cc.p4
					left join category c3 on c3.id =cc.p5
					where sku_id in (".join(',',$sl5).") and is_parent=1";
					$q_parent = $con_multi->sql_query($sql_parent);
					
					while ($r = $con_multi->sql_fetchassoc($q_parent)) {
						$group_data[$r['sku_id']]['info']=$r;					
					}
					$con_multi->sql_freeresult($q_parent);
				}
			}
		
			foreach($table as $k=>$r){
				$uom = 1;
				if($group_by_sku) $uom = mf($table[$k]['info']['packing_uom_fraction']);

				$total['closing_sc_adj']+=$r['closing_sc_adj'];
				$total['closing_bal']+=$r['closing_bal']*$uom;
				$total['closing_bal_val']+=$r['closing_bal_val'];
				$total['closing_bal_sel_val']+=$r['closing_bal_sel_val'];
			}
		}

		// additional group calculation
		if($group_by_sku&&$table){
			foreach($table as $sid=>$r){
				$sku_id = mi($table[$sid]['info']['sku_id']);
				$uom = mf($table[$sid]['info']['packing_uom_fraction']);

				$group_data[$sku_id]['closing_sc_adj'] += $r['closing_sc_adj'];
				$group_data[$sku_id]['closing_bal'] += $r['closing_bal']*$uom;
				$group_data[$sku_id]['closing_bal_val'] += $r['closing_bal_val'];
				$group_data[$sku_id]['closing_bal_sel_val'] += $r['closing_bal_sel_val'];
			}

			foreach($group_data as $sku_id=>$r){
			    // unset if group don't have data
				if(!$r['closing_bal'] && !$r['closing_sc_adj']){
	                unset($group_data[$sku_id]);
	                continue;
				}

				if($r['closing_bal']){
	                $group_data[$sku_id]['cost'] = $r['closing_bal_val']/$r['closing_bal'];
				}
			}
		}

		if($group_by_sku or $hq_cost){
	        if(trim($_REQUEST['sort_by'])!='')	usort($group_data, array($this, "sort_table"));
			$smarty->assign('table', $group_data);
		}else{
	        if(trim($_REQUEST['sort_by'])!='')	usort($table, array($this, "sort_table"));
			$smarty->assign('table', $table);
		}
		
		$smarty->assign("items", $data);
		$smarty->assign("total", $total);

		$smarty->assign('group_info', $group_info);
		$smarty->assign("bid", $branch_id);
		$smarty->assign('got_closing_sc', $got_closing_sc);
		$smarty->assign('got_stock_check',$got_stock_check);
		$smarty->assign("branch_is_under_gst", $branch_is_under_gst);
		//$con_multi->close_connection();
	}

	function sort_table($a,$b)
	{
		$col = $_REQUEST['sort_by'];
	    if ($a['info'][$col]==$b['info'][$col]) return 0;
	    return ($a['info'][$col]>$b['info'][$col]) ? 1 : -1;
	}
	
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

$Closing_Stock_By_SKU_Report = new Closing_Stock_By_SKU_Report('Closing Stock by SKU Report');
?>
