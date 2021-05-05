<?php
/*
8/25/2015 4:41 PM Andy
- Change to include path of __DIR__ to dirname(__FILE__)

2/3/2020 1:45 PM Andy
- Added reportManager function "connectReportServer".
*/
include_once(dirname(__FILE__).'/stockAgingReport.php');

class reportManager{
	// common variable
	
	public $stockAgingReport;

	function __construct(){
		
	}

	// function to initialise stock aging report
	// return class object stockAgingReport
	function initStockAgingReport(){
		$this->stockAgingReport = new stockAgingReport();
		return $this->stockAgingReport;
	}

	// function to create new report data id
	// return int reportDataID
	function createNewReportDataID($reportName, $reportData){
		global $con, $sessioninfo;

		$upd = array();
		$upd['report_name'] = $reportName;
		$upd['report_data'] = serialize($reportData);
		$upd['user_id'] = mi($sessioninfo['id']);
		$upd['added'] = 'CURRENT_TIMESTAMP';

		$con->sql_query("insert into tmp_report_data_info ".mysql_insert_by_field($upd));
		$newReportDataID = mi($con->sql_nextid());

		return $newReportDataID;
	}

	// function to get report data info by id
	// return row object of mysql table tmp_report_data_info
	function getReportDataInfoByID($reportDataID){
		global $con;

		$con->sql_query("select * from tmp_report_data_info where id=".mi($reportDataID));
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();

		return $data;
	}
	
	// function to generate common report params
	// return array reportTitleList
	function generateCommonReportTitleList($reportParams){
		global $appCore;

		if(!is_array($reportParams) || !$reportParams)	return;

		$reportTitleList = array();

		// branch
		if(isset($reportParams['branch_id'])){
			if(!$reportParams['branch_id']){
				$reportTitleList[] = "Branch: All";
			}else{
				if(preg_match("/^bg,/", $reportParams['branch_id'])){
					// branch group
					$bgid = str_replace("bg,", "", $reportParams['branch_id']);
					$branchGroupInfo = $appCore->branchManager->getBranchGroupInfo($bgid);
					$reportTitleList[] = "Branch Group: ".$branchGroupInfo['code'];
				}else{
					// branch
					$branchInfo = $appCore->branchManager->getBranchInfo($reportParams['branch_id']);
					$reportTitleList[] = "Branch: ".$branchInfo['code'];
				}
			}
		}

		// category
		if(isset($reportParams['category_id'])){
			if($reportParams['category_id'] > 0){
				$cat_info = $appCore->categoryManager->getCategoryInfo($reportParams['category_id']);
				$reportTitleList[] = "Category: ".$cat_info['description'];
			}elseif($reportParams['all_category']){
				$reportTitleList[] = "Category: All";
			}
		}

		// vendor
		if(isset($reportParams['vendor_id'])){
			if($reportParams['vendor_id'] > 0){
				$vendorInfo = $appCore->vendorManager->getVendorInfo($reportParams['vendor_id']);
				$reportTitleList[] = "Vendor: ".$vendorInfo['description'];
			}else{
				$reportTitleList[] = "Vendor: All";
			}
		}



		return $reportTitleList;
	}
	
	public function connectReportServer(){
		global $con, $config;
		
		$con_multi = false;
		if(isset($config['report_server']) && is_array($config['report_server']) && $config['report_server']){
			$con_multi= new mysql_multi();
		}
		
		if(!$con_multi)	$con_multi = $con;
		
		return $con_multi;
	}
}


?>
