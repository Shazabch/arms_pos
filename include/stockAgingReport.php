<?php
/*
10/21/2015 4:45 PM Andy
- Remove the page title smarty assign.
*/
class stockAgingReport{
	// common variable
	public $reportName = 'stock_aging_report';
	public $reportRealName = 'Stock Aging Report';
	public $branchesList;
	public $branchGroupList;
	public $vendorList;
	public $brandGroupList;
	public $brandList;
	public $skuTypeList;
	public $stockAgeFilter = array(
			//0 => 'All',
			1 => '1 month',
			2 => '2 months',
			3 => '3 months',
			4 => '4 months',
			5 => '5 months',
			6 => '6 months',
			7 => '7 months',
			8 => '8 months',
			9 => '9 months',
			10 => '10 months',
			11 => '11 months',
			12 => '12 months',
			13 => 'More than 12 months'
		);
	public $reportDataID;

	// private
	private $data;
	private $isFirstLoad = true;
	private $dateFrom;
	private $dateTo;
	private $tmpReportTableName;
	private $ageLabelList;
	private $reportTitleList;

	function __construct(){
		global $smarty, $con, $appCore;

		if($appCore->haveSmarty){
			// stock age filter
			$smarty->assign('stockAgeFilter', $this->stockAgeFilter);
		}
	}

	public function load_default_data(){
		global $config, $appCore, $smarty;

		$this->branchesList = $appCore->branchManager->getBranchesList(array('active'=>1));
		if($appCore->haveSmarty)	$smarty->assign('branchesList', $this->branchesList);

		$this->branchGroupList = $appCore->branchManager->getBranchGroupList();
		if($appCore->haveSmarty)	$smarty->assign('branchGroupList', $this->branchGroupList);

		// vendor
		$this->vendorList = $appCore->vendorManager->getVendorList(array('active'=>1));
		if($appCore->haveSmarty)	$smarty->assign('vendorList', $this->vendorList);

		// brand
		$this->brandGroupList = $appCore->brandManager->getBrandGroupList(array('active'=>1));
		if($appCore->haveSmarty)	$smarty->assign('brandGroupList', $this->brandGroupList);

		$this->brandList = $appCore->brandManager->getBrandList(array('active'=>1));
		if($appCore->haveSmarty)	$smarty->assign('brandList', $this->brandList);

		// sku type
		$this->skuTypeList = $appCore->skuManager->getSKUTypeList(array('active'=>1));
		if($appCore->haveSmarty)	$smarty->assign('skuTypeList', $this->skuTypeList);

		// year / month
		if(!isset($_REQUEST['year']))	$_REQUEST['year'] = mi(date("Y"));
		if(!isset($_REQUEST['month']))	$_REQUEST['month'] = mi(date("m"));

		// filter type
		if(!isset($_REQUEST['filter_type']))	$_REQUEST['filter_type'] = 'sku';

		// stock age
		if(!isset($_REQUEST['stock_age']))	$_REQUEST['stock_age'] = 6;
	}

	// function to load report
	// return array data
	public function loadReport($params = array()){
		global $con, $smarty, $config, $appCore;

		// preparation
		$this->prepareReportData($params);

		// validate report params
		$err = $this->validateReportParams();
		if($err){
			return array('err'=>$err);
		}
		
		if($this->isFirstLoad){
			// generate sku list
			//$this->generateReportSKUList();
		}

		// process report by page
		$this->processReport($params);

		return $this->data;
	}

	// function to get age label list
	// return array ageLabelList
	public function getAgeLabelList(){
		global $appCore, $smarty;

		if(!$this->reportParams)	return;

		if(!is_array($this->ageLabelList))	$this->ageLabelList = array();

		$y = $this->reportParams['year'];
		$m = $this->reportParams['month'];

		for($i = 1; $i <= $this->reportParams['stock_age']; $i++){
			$this->ageLabelList[$i]['age'] = $i;
			$this->ageLabelList[$i]['y'] = $y;
			$this->ageLabelList[$i]['m'] = $m;

			$m--;
			if($m <= 0){
				$m = 12;
				$y--;
			}
		}

		//print_r($this->ageLabelList);
		if($appCore->haveSmarty)	$smarty->assign('ageLabelList', $this->ageLabelList);
	}

	// function to get report title
	// return string reportTitle
	public function getReportTitle(){
		global $appCore, $smarty;

		if(!$this->data || !$this->reportParams || !$this->reportDataID)	return;

		if(!is_array($this->reportTitleList))	$this->reportTitleList = array();

		if(!$this->reportTitleList){
			// get common title
			$this->reportTitleList = $appCore->reportManager->generateCommonReportTitleList($this->reportParams);

			// stock at
			$this->reportTitleList[] = "Stock at: ".$this->reportParams['year']." ".$appCore->getMonthLabel($this->reportParams['month']);

			// have stock age at
			$this->reportTitleList[] = "Stock age at: ".$this->stockAgeFilter[$this->reportParams['stock_age']];

			// filter type
			if($this->reportParams['filter_type'] == 'cat'){
				$this->reportTitleList[] = "Filter type: Category";
			}else{
				$this->reportTitleList[] = "Filter type: SKU";
			}

			// filter inactive sku
			if($this->reportParams['exclude_inactive_sku'])	$this->reportTitleList[] = "Exclude inactive SKU: Yes";

			// filter sku added date
			if($this->reportParams['filter_by_sku_added_date']){
				if($this->reportParams['sku_date_from']){
					$this->reportTitleList[] = "SKU added >= ".$this->reportParams['sku_date_from'];
				}
				if($this->reportParams['sku_date_to']){
					$this->reportTitleList[] = "SKU added <= ".$this->reportParams['sku_date_to'];
				}	
			}
			//print_r($this->reportTitleList);
		}
		

		if($appCore->haveSmarty)	$smarty->assign('reportTitle', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->reportTitleList));
		return $reportTitle;
	}

	////////////////////////////////////////////////////////////////////////// PRIVATE FUNCTION //////////////////////////////////////////////////////////////////////////
	// function to do preparation before load report
	// return null
	private function prepareReportData($params = array()){
		global $appCore;

		if(isset($params['reportDataID']) && $params['reportDataID'])	$this->reportDataID = mi($params['reportDataID']);

		if(!$this->reportDataID){
			// first time load, need to get sku
			$this->reportDataID = $appCore->reportManager->createNewReportDataID($this->reportName, $params);
			$this->reportParams = $params;
		}else{
			// already have sku list
			$reportDataInfo = $appCore->reportManager->getReportDataInfoByID($this->reportDataID);
			$this->reportParams = unserialize($reportDataInfo['report_data']);
			$this->isFirstLoad = false;
		}
	}

	// function to validate report params
	// return array err
	private function validateReportParams(){
		global $appCore;

		$err = array();
		if(!$this->reportParams)	$err[] = "No params found.";

		if($err)	return $err;

		//print_r($this->reportParams);
		if(!isset($this->stockAgeFilter[$this->reportParams['stock_age']]))	$err[] = "Invalid Stock Age.";

		if($this->reportParams['filter_type'] == 'cat'){
			// cat
			if(!isset($this->reportParams['all_category']) && !$this->reportParams['all_category']){
				if($this->reportParams['category_id']<=0)	$err[] = "Please provide category.";
			}
		}else{
			// sku
			if(!isset($this->reportParams['sku_code_list']) || !$this->reportParams['sku_code_list'])	$err[] = "Please add at least 1 sku.";
		}

		// filter sku added date
		if($this->reportParams['filter_by_sku_added_date']){
			if(isset($this->reportParams['sku_date_from']) && $this->reportParams['sku_date_from'] && !$appCore->isValidDateFormat($this->reportParams['sku_date_from'])){
				$err[] = "Invalid SKU Added Date From";
			}
			if(!isset($this->reportParams['sku_date_to']) && $this->reportParams['sku_date_to'] && !$appCore->isValidDateFormat($this->reportParams['sku_date_to'])){
				$err[] = "Invalid SKU Added Date To";
			}
		}

		if(!$err){
			// date end - get the current selected year and month
			$this->dateTo = $this->reportParams['year'].'-'.$this->reportParams['month'].'-'.days_of_month($this->reportParams['month'], $this->reportParams['year']);
			
			// date start - always 1 year before date end
			$y = $this->reportParams['year'];
			$m = $this->reportParams['month'];
			$stock_range = $this->reportParams['stock_age'];
			for($i=1; $i<$stock_range; $i++){
				$m--;
				if($m<=0){
					$m = 12;
					$y--;
				}
			}
			$this->dateFrom = $y.'-'.$m.'-1';

			//print "dateFrom = $this->dateFrom, dateTo = $this->dateTo<br>";
		}

		return $err;
	}

	// function to generate report sku list
	// return null
	/*function generateReportSKUList(){
		global $con, $appCore, $config;
	}*/

	// core function to process report to get data
	// return null
	private function processReport($params){
		global $con, $appCore;

		$this->data = array();

		// begin
		$bidList = array();
		if($this->reportParams['branch_id']){
			// got branch id
			$bidList = $appCore->branchManager->checkAndReturnBranchIDList($this->reportParams['branch_id']);
		}else{
			// all branches
			$bidList = array_keys($this->branchesList);
		}
		//print_r($bidList);

		// create tmp report table
		$time = time();
		$this->tmpReportTableName = "tmp_stockAgingReport_".$time;

		$con->sql_query("create temporary table if not exists $this->tmpReportTableName(
			branch_id int,
			sku_item_id int,
			from_qty double not null default 0,
			to_qty double not null default 0,
			primary key(branch_id, sku_item_id),
			index from_qty (from_qty),
			index to_qty (to_qty)
			)");
		//print "tmpReportTableName = $this->tmpReportTableName<br>";

		// generate age label list
		$this->getAgeLabelList();

		// loop for each branch
		foreach ($bidList as $bid) {
			$this->getBranchReportData($bid);
		}

		// finish
		$this->data['reportDataID'] = $this->reportDataID;

		// generate report title
		$this->getReportTitle();
	}

	// function to get branch report data
	// return null
	private function getBranchReportData($bid){
		global $con, $appCore;

		// get sku for dateTo
		$sb_tbl_to = "stock_balance_b".$bid."_".mi(date("Y", strtotime($this->dateTo)));
		
		$q_tbl_exists = $con->sql_query_false("explain $sb_tbl_to");
		if(!$q_tbl_exists)	return;	// cant get stock balance at date end

		$filterCommon = array();

		if($this->reportParams['filter_type'] == 'cat'){
			// cat
			$cat_info = $appCore->categoryManager->getCategoryInfo($this->reportParams['category_id']);
			if($cat_info){
				$filterCommon[] = "cc.p".$cat_info['level']."=".$cat_info['id'];
			}
		}else{
			// sku
			$filterCommon[] = "si.sku_item_code in (".join(',',array_map("ms", $this->reportParams['sku_code_list'])).")";
		}

		// exclude inactive sku
		if($this->reportParams['exclude_inactive_sku'])	$filterCommon[] = "si.active=1";

		// filter sku added date
		if($this->reportParams['filter_by_sku_added_date']){
			if($this->reportParams['sku_date_from']){
				$filterCommon[] = "si.added >= ".ms($this->reportParams['sku_date_from']);
			}
			if($this->reportParams['sku_date_to']){
				$filterCommon[] = "si.added <= ".ms($this->reportParams['sku_date_to']." 23:59:59");
			}
		}

		// vendor
		if($this->reportParams['vendor_id']){
			$filterCommon[] = "sku.vendor_id=".mi($this->reportParams['vendor_id']);
		}

		// brand
		if(isset($this->reportParams['brand_id']) && $this->reportParams['brand_id'] !== ''){
			$filterCommon[] = "sku.brand_id=".mi($this->reportParams['brand_id']);
		}

		// sku type
		if($this->reportParams['sku_type']){
			$filterCommon[] = "sku.sku_type=".ms($this->reportParams['sku_type']);
		}
		$filterCommon[] = "if(sku.no_inventory='inherit', cc.no_inventory, sku.no_inventory)='no'";

		$filterTo = array();
		$filterTo[] = ms($this->dateTo)." between sb.from_date and sb.to_date";
		$filterTo[] = "sb.qty>0";
		$filterTo[] = join(' and ', $filterCommon);
		$filterTo = "where ".join(' and ', $filterTo);
		$sql = "select $bid, sb.sku_item_id, sb.qty
			from $sb_tbl_to sb
			join sku_items si on si.id=sb.sku_item_id
			join sku on sku.id=si.sku_id
			left join category_cache cc on cc.category_id=sku.category_id
			$filterTo";
		//print $sql."<br>";

		$con->sql_query("insert into $this->tmpReportTableName (branch_id, sku_item_id, to_qty) ($sql)");

		// get sku for dateFrom
		$sb_tbl_from = "stock_balance_b".$bid."_".mi(date("Y", strtotime($this->dateFrom)));
		
		$q_tbl_exists = $con->sql_query_false("explain $sb_tbl_from");
		if(!$q_tbl_exists)	return;	// cant get stock balance at date end

		$filterFrom = array();
		$filterFrom[] = ms(date("Y-m-d", strtotime("-1 day", strtotime($this->dateFrom))))." between sb.from_date and sb.to_date";
		$filterFrom[] = "sb.qty>0";
		$filterFrom[] = join(' and ', $filterCommon);

		$filterFrom = "where ".join(' and ', $filterFrom);
		$sql = "select $bid, sb.sku_item_id, sb.qty
			from $sb_tbl_from sb
			join sku_items si on si.id=sb.sku_item_id
			join sku on sku.id=si.sku_id
			left join category_cache cc on cc.category_id=sku.category_id
			$filterFrom";
		//print $sql."<br>";

		$con->sql_query("insert into $this->tmpReportTableName (branch_id, sku_item_id, from_qty) ($sql)
			on duplicate key update
			from_qty=sb.qty");

		// delete those zero qty at from/to
		$con->sql_query("delete from $this->tmpReportTableName where branch_id=$bid and (from_qty<=0 or to_qty<=0)");

		// select all and loop items
		$q1 = $con->sql_query("select tbl.*
			from $this->tmpReportTableName tbl
			where tbl.branch_id=$bid
			order by tbl.sku_item_id");
		while($r = $con->sql_fetchassoc($q1)){
			$this->data['data']['branchData'][$bid]['si_list'][$r['sku_item_id']] = $r;
			$this->adjustStockAgeBalance($bid, $r['sku_item_id'], $this->dateFrom, $r['from_qty']);
		}
		$con->sql_freeresult($q1);

		$strSidFilter = "select tbl.sku_item_id
		from $this->tmpReportTableName tbl
		where tbl.branch_id=$bid";

		// get grn
		$sql = "select gi.sku_item_id, grr.rcv_date as date, sum(if (gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn *rcv_uom.fraction + gi.pcs, gi.acc_ctn *rcv_uom.fraction + gi.acc_pcs)) as qty
		from grn_items gi
		left join uom rcv_uom on gi.uom_id=rcv_uom.id
		left join grn on grn_id=grn.id and gi.branch_id=grn.branch_id
		left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
		left join grr_items gri on gri.id=grn.grr_item_id and gri.branch_id=grn.branch_id
		left join sku_items si on gi.sku_item_id = si.id
		where gi.branch_id=$bid and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1 and grr.rcv_date between ".ms($this->dateFrom)." and ".ms($this->dateTo)." 
		and gi.sku_item_id in ($strSidFilter)
		group by gi.sku_item_id, date
		order by gi.sku_item_id, date";
		//print "$sql<br>";
		$q_grn = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q_grn)){
			if($r['qty'] >= 0){
				// add qty always directly adjust into stock age
				$this->adjustStockAgeBalance($bid, $r['sku_item_id'], $r['date'], $r['qty']);	
			}else{
				// put on hold for negative qty
				$this->putOnHoldNegativeQty($bid, $r['sku_item_id'], $r['date'], $r['qty']);
			}
			
		}
		$con->sql_freeresult($q_grn);

		// stock check
		$q_sc = $con->sql_query("select si.id as sku_item_id, sc.date, sum(sc.qty) as qty
			from stock_check sc 
			left join sku_items si on si.sku_item_code=sc.sku_item_code
			where sc.branch_id = $bid and sc.date between ".ms($this->dateFrom)." and ".ms($this->dateTo)." and si.id in ($strSidFilter) 
			group by sku_item_id, date 
			order by sku_item_id, date");
		while($r=$con->sql_fetchassoc($q_sc))
		{
			$sb = $appCore->skuManager->getStockBalance($r['sku_item_id'], $bid, date("Y-m-d", strtotime("-1 day", strtotime($r['date']))));
			$diffQty = $r['qty']-$sb['qty'];

			if($diffQty >= 0){
				// add qty always directly adjust into stock age
				$this->adjustStockAgeBalance($bid, $r['sku_item_id'], $r['date'], $diffQty);
			}else{
				// put on hold for negative qty
				$this->putOnHoldNegativeQty($bid, $r['sku_item_id'], $r['date'], $diffQty);
			}
			
		}
		$con->sql_freeresult($q_sc);

		// POS
		$q_pos = $con->sql_query("select sc.sku_item_id, sc.date, sc.qty 
			from sku_items_sales_cache_b$bid sc
			where sc.date between ".ms($this->dateFrom)." and ".ms($this->dateTo)." and sc.sku_item_id in ($strSidFilter)
			order by sc.sku_item_id, sc.date");
		while($r=$con->sql_fetchassoc($q_pos))
		{
			if($r['qty'] < 0){
				// goods return
				// add qty always directly adjust into stock age
				$this->adjustStockAgeBalance($bid, $r['sku_item_id'], $r['date'], abs($r['qty']));	
			}else{
				// put on hold for negative qty
				$this->putOnHoldNegativeQty($bid, $r['sku_item_id'], $r['date'], $r['qty']);
			}
			
		}
		$con->sql_freeresult($q_pos);

		//FROM DO
		$q_do = $con->sql_query("select di.sku_item_id, do.do_date as date, sum(di.ctn *uom.fraction + di.pcs) as qty
			from do_items di
			join do on do.id=di.do_id and do.branch_id=di.branch_id
			left join uom on di.uom_id=uom.id
			where di.branch_id=$bid and do.approved=1 and do.checkout=1 and do.status<2 and do.active=1 and do.do_date between ".ms($this->dateFrom)." and ".ms($this->dateTo)."
			and di.sku_item_id in ($strSidFilter)
			group by di.sku_item_id, do.do_date
			order by di.sku_item_id, do.do_date");
		while($r=$con->sql_fetchassoc($q_do))
		{
			// put on hold for negative qty
			$this->putOnHoldNegativeQty($bid, $r['sku_item_id'], $r['date'], $r['qty']);
		}
		$con->sql_freeresult($q_do);

		// GRA
		$con->sql_query("select gi.sku_item_id, date(gra.return_timestamp) as date, sum(gi.qty) as qty
			from gra_items gi
			join gra on gi.gra_id = gra.id and gi.branch_id = gra.branch_id 
			where gi.branch_id=$bid and gra.status=0 and gra.returned=1 and gra.return_timestamp between ".ms($this->dateFrom)." and ".ms($this->dateTo." 23:59:59")."
			group by gi.sku_item_id, date
			order by gi.sku_item_id, date");
		while($r=$con->sql_fetchrow())
		{
			if($r['qty'] < 0){
				// add qty always directly adjust into stock age
				$this->adjustStockAgeBalance($bid, $r['sku_item_id'], $r['date'], abs($r['qty']));	
			}else{
				// put on hold for negative qty
				$this->putOnHoldNegativeQty($bid, $r['sku_item_id'], $r['date'], $r['qty']);
			}
		}
		$con->sql_freeresult();

		//FROM ADJUSTMENT
		$q_adj = $con->sql_query("select adji.sku_item_id, adj.adjustment_date as date, sum(adji.qty) as qty
			from adjustment_items adji
			join adjustment adj on adj.id=adji.adjustment_id and adj.branch_id=adji.branch_id
			where adji.branch_id=$bid and adj.approved=1 and adj.status<2 and adj.active=1 and adj.adjustment_date between ".ms($this->dateFrom)." and ".ms($this->dateTo)." 
			and adji.sku_item_id in ($strSidFilter)
	 		group by adji.sku_item_id, adj.adjustment_date
	 		order by adji.sku_item_id, adj.adjustment_date");
		while($r=$con->sql_fetchassoc($q_adj))
		{
			if($r['qty'] >= 0){
				// add qty always directly adjust into stock age
				$this->adjustStockAgeBalance($bid, $r['sku_item_id'], $r['date'], $r['qty']);	
			}else{
				// put on hold for negative qty
				$this->putOnHoldNegativeQty($bid, $r['sku_item_id'], $r['date'], $r['qty']);
			}
		}
		$con->sql_freeresult($q_adj);

		// consignment mode
		if($config['consignment_modules']){
	        //FROM Credit Note
			$q_cn = $con->sql_query("select cni.sku_item_id, cn.date, sum(cni.ctn *uom.fraction + cni.pcs) as qty, cn.date as dt
				from cn_items cni
				join cn on cn.id=cni.cn_id and cn.branch_id=cni.branch_id
				left join uom on cni.uom_id=uom.id
				where cn.to_branch_id=$bid and cn.active=1 and cn.approved=1 and cn.status=1 and cn.date between ".ms($this->dateFrom)." and ".ms($this->dateTo)." 
				and cni.sku_item_id in ($strSidFilter)
				group by cni.sku_item_id, cn.date
				order by cni.sku_item_id, cn.date");
			while($r=$con->sql_fetchassoc($q_cn))
			{
				if($r['qty'] >= 0){
					// add qty always directly adjust into stock age
					$this->adjustStockAgeBalance($bid, $r['sku_item_id'], $r['date'], $r['qty']);	
				}else{
					// put on hold for negative qty
					$this->putOnHoldNegativeQty($bid, $r['sku_item_id'], $r['date'], $r['qty']);
				}
			}
			$con->sql_freeresult($q_cn);

			//FROM Debit Note
			$q_dn = $con->sql_query("select dni.sku_item_id,dn.date, sum(dni.ctn *uom.fraction + dni.pcs) as qty
				from dn_items dni
				left join dn on dn.id=dni.dn_id and dn.branch_id=dni.branch_id
				left join uom on dni.uom_id=uom.id
				where dn.to_branch_id=$bid and dn.active=1 and dn.approved=1 and dn.status=1  and dn.date between ".ms($this->dateFrom)." and ".ms($this->dateTo)."
				group by dni.sku_item_id,dn.date
				order by dni.sku_item_id,dn.date");
			while($r=$con->sql_fetchrow($q_dn))
			{
				if($r['qty'] < 0){
					// add qty always directly adjust into stock age
					$this->adjustStockAgeBalance($bid, $r['sku_item_id'], $r['date'], $r['qty']);	
				}else{
					// put on hold for negative qty
					$this->putOnHoldNegativeQty($bid, $r['sku_item_id'], $r['date'], $r['qty']);
				}
			}
			$con->sql_freeresult($q_dn);
		}

		// at the end only do the deduct stock
		if(isset($this->data['data']['branchData'][$bid]['si_list']) && $this->data['data']['branchData'][$bid]['si_list']){
			// loop for each sku
			foreach($this->data['data']['branchData'][$bid]['si_list'] as $sid => $r){
				// loop from last month to latest
				for($i = $this->reportParams['stock_age']; $i >= 1; $i--){
					if($r['ageList'][$i]['deductQty'] > 0){
						$this->adjustStockAgeBalance($bid, $sid, array('stockAge'=>$i), $r['ageList'][$i]['deductQty'], 'sub');
					}
				}
			}
		}

		// check all items and calculate total
		$this->constructBranchTotal($bid);
	}

	// function to get stock age index by date
	// return int stockAgeIndex
	private function getStockAgeIndexByDate($date){
		//print "date = $date<br>";
		return $this->getStockAgeIndexByYearMonth(date("Y", strtotime($date)), date("m", strtotime($date)));
	}

	// function to get stock age index by year month
	// return int stockAgeIndex
	private function getStockAgeIndexByYearMonth($y, $m){
		$y = mi($y);
		$m = mi($m);

		$curr_y = mi($this->reportParams['year']);
		$curr_m = mi($this->reportParams['month']);

		for($i = 1, $len = count($this->stockAgeFilter); $i <= $len; $i++){
			if($curr_y == $y && $curr_m == $m){
				return $i;
			}
			$curr_m--;
			if($curr_m <= 0){
				$curr_m = 12;
				$curr_y--;
			}
		}

		return $i;
	}

	// function to put on hold negative qty
	// return null
	private function putOnHoldNegativeQty($bid, $sid, $date, $qty){
		if(!$qty)	return;

		$stockAgeIndex = $this->getStockAgeIndexByDate($date);

		$this->data['data']['branchData'][$bid]['si_list'][$sid]['ageList'][$stockAgeIndex]['deductQty'] += abs($qty);
	}
	// function to adjust balance in stock age month
	// return null
	private function adjustStockAgeBalance($bid, $sid, $date, $qty, $adjType='add'){
		if(is_array($date)){
			$params = $date;
			if($params['stockAge']){
				$stockAgeIndex = $params['stockAge'];	
			}
		}else{
			$stockAgeIndex = $this->getStockAgeIndexByDate($date);	
		}
		
		if($stockAgeIndex <= 0)	return;

		$qty = abs($qty);

		if($adjType == 'add'){
			// add
			/*for($i = $stockAgeIndex; $i >= 1; $i--){
				$this->data['branchData'][$bid]['si_list'][$sid]['ageList'][$i]['qty'] += $qty;	
			}*/

			$this->data['data']['branchData'][$bid]['si_list'][$sid]['ageList'][$stockAgeIndex]['qty'] += $qty;	

			// record the add qty
			$this->data['data']['branchData'][$bid]['si_list'][$sid]['ageList'][$stockAgeIndex]['addQty'] += $qty;
		}else{
			// sub
			$stockAge = $this->reportParams['stock_age'];
			$remainQty = $qty;

			while($remainQty > 0 && $stockAge >=0 && $stockAge >= $stockAgeIndex){
				if($this->data['data']['branchData'][$bid]['si_list'][$sid]['ageList'][$stockAge]['qty'] > 0){
					if($this->data['data']['branchData'][$bid]['si_list'][$sid]['ageList'][$stockAge]['qty'] >= $remainQty){
						$this->data['data']['branchData'][$bid]['si_list'][$sid]['ageList'][$stockAge]['qty'] -= $remainQty;
						$remainQty = 0;
					}else{
						$deductQty = $remainQty - $this->data['data']['branchData'][$bid]['si_list'][$sid]['ageList'][$stockAge]['qty'];
						$this->data['data']['branchData'][$bid]['si_list'][$sid]['ageList'][$stockAge]['qty'] = 0;
						$remainQty -= $deductQty;
					}
				}
				$stockAge--;
			}

			// loop and start to deduct stock from last month
			/*for($i = $stockAge; $i >= $stockAgeIndex; $i--){
				$this->data['branchData'][$bid]['si_list'][$sid]['ageList'][$i]['qty'] -= $qty;
			}*/
		}
	}

	// function to calculate branch total
	// return null
	private function constructBranchTotal($bid){
		if(isset($this->data['data']['branchData'][$bid]['si_list']) && $this->data['data']['branchData'][$bid]['si_list']){
			// loop for each sku
			foreach($this->data['data']['branchData'][$bid]['si_list'] as $sid => $r){

				if($this->data['data']['branchData'][$bid]['si_list'][$sid]['ageList'][$this->reportParams['stock_age']]['qty'] <= 0){
					// no qty left, remove from array
					unset($this->data['data']['branchData'][$bid]['si_list'][$sid]);
				}else{
					// got qty
					$this->data['data']['branchData'][$bid]['total']['to_qty'] += $r['to_qty'];

					foreach($this->ageLabelList as $age => $ageInfo){
						$this->data['data']['branchData'][$bid]['total']['ageList'][$age]['qty'] += $r['ageList'][$age]['qty'];						
					}
				}
			}
		}

		// remove branch data
		if(!$this->data['data']['branchData'][$bid]['si_list']){
			unset($this->data['data']['branchData'][$bid]);
		}

		// remove all data
		if(!$this->data['data']['branchData']){
			unset($this->data['data']['branchData']);
		}
	}
}
?>
