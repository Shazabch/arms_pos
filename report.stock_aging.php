<?php
/*
8/26/2015 3:32 PM Andy
- Report re-write.

8/27/2015 10:57 AM Andy
- Remove display error message.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");

//if ($_REQUEST['group_by_branch'])

class Stock_Aging_Report extends Module
{
	var $reportClass;

	function __construct($title){
		global $appCore;

		/*if(!$appCore){
			print "appcore not exists<br>";
			if(file_exists('include/appCore.php')){
				include_once('include/appCore.php');
			}
		}*/

		$this->reportClass = $appCore->reportManager->initStockAgingReport();

		// exclude inactive sku
		if(!$_REQUEST['show_report']){
			if(!isset($_REQUEST['exclude_inactive_sku']))	$_REQUEST['exclude_inactive_sku'] = 1;
		}

		parent::__construct($this->reportClass->reportRealName);
	}

	function _default(){
		global $appCore, $sessioninfo, $smarty;
		
		// load default data
		$this->reportClass->load_default_data();

		if($_REQUEST['show_report']){
			$this->load_report();
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

	private function load_report(){
		global $appCore, $sessioninfo, $smarty;
		//print_r($_REQUEST);

		$params = array();
		if(BRANCH_CODE != 'HQ'){	// branch can only view own data
			$params['branch_id'] = $sessioninfo['branch_id'];
		}else{
			$params['branch_id'] = $_REQUEST['branch_id'];
		}

		$params['year'] = mi($_REQUEST['year']);
		$params['month'] = mi($_REQUEST['month']);
		$params['stock_age'] = mi($_REQUEST['stock_age']);
		$params['filter_type'] = trim($_REQUEST['filter_type']);
		if(!$params['filter_type'])	$params['filter_type'] = 'sku';
		if($params['filter_type'] == 'cat'){
			// cat
			$params['category_id'] = mi($_REQUEST['category_id']);
			if($_REQUEST['all_category'])	$params['all_category'] = 1;
		}else{
			// sku (default)
			$params['sku_code_list'] = $_REQUEST['sku_code_list'];
			// assign back the sku for tpl
			$appCore->skuManager->assignGroupItemForSKUAutocomplteMultipleAdd2($_REQUEST['sku_code_list']);
		}
		$params['vendor_id'] = mi($_REQUEST['vendor_id']);
		$params['brand_id'] = trim($_REQUEST['brand_id']);
		$params['sku_type'] = trim($_REQUEST['sku_type']);
		if(BRANCH_CODE == 'HQ' && $_REQUEST['group_by_branch']) $params['group_by_branch'] = 1;
		if($_REQUEST['exclude_inactive_sku']) $params['exclude_inactive_sku'] = 1;
		if($_REQUEST['filter_by_sku_added_date']){
			$params['filter_by_sku_added_date'] = 1;
			$params['sku_date_from'] = $_REQUEST['sku_date_from'];
			$params['sku_date_to'] = $_REQUEST['sku_date_to'];
		}
		if($_REQUEST['export_excel'])	$params['export_excel'] = 1;

		$data = $this->reportClass->loadReport($params);
		if(isset($data['err']) && $data['err']){
			$smarty->assign('err', $data['err']);
			return;
		}

		//print_r($data);
		$smarty->assign('data', $data);
	}
}

$report = new Stock_Aging_Report('');
?>
