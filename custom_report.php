<?php
/*
6/12/2020 5:39 PM William
- Remove "Share Report Builder" checking for custom report.

6/16/2020 2:32 PM William
- Enhanced to added new filter Age Group.

12/21/2020 9:00 AM William
- Enhanced to get "Disable Row Total", "Disable Row Merge" and "Disable Column Merge" settings.

1/7/2021 5:13 PM Andy
- Fixed when loading year list should check on table pos.
- Add checking to set minimum year is 2000.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_CUSTOM_VIEW')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_CUSTOM_VIEW', BRANCH_CODE), "/index.php");
include("custom_report.include.php");
class CUSTOM_REPORT extends Module{
	var $vendor_list = array();
	var $branch_list = array();
	var $sku_type_list = array();
	var $brand_list = array();
	var $age_group_list = array();
	var $report_id = 0;
	var $form = array();

	function __construct($title){
		global $smarty, $con_multi, $appCore, $sessioninfo, $report_group, $LANG;
		
		//use report server connection
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		$this->report_id = mi($_REQUEST['report_id']);
		if(!$this->report_id)	die('No Report ID');
		
		//check share report builder settings
		$this->form = load_report_header($this->report_id);
		if(!$this->form)	js_redirect("Report Not Found.", "/index.php");
		
		//report group privilege checking
		$report_group_name = $this->form['report_group'];
		$report_privilege = $report_group[$report_group_name];
		if($report_privilege){
			if (!$sessioninfo['privilege'][$report_privilege]) js_redirect(sprintf($LANG['NO_PRIVILEGE'], $report_privilege, BRANCH_CODE), "/index.php");
		}	
		
		$this->init_data();
		parent::__construct($title);
	}
	
	function _default(){
		global $con_multi, $smarty, $sessioninfo;

		$this->update_title($this->form['report_title']);
		$smarty->assign('form', $this->form);
		
		if($_REQUEST['show_report']){
			if($_REQUEST['export_excel']){  //if export excel file
			    include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'CUSTOM_REPORT', $this->report_id, "Export Report".$this->title);
				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=custom_report_'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
	 		$this->generate_report();
		}
		
		$this->display();
		exit;
	}
	
	function init_data(){
		global $smarty, $con_multi, $appCore, $sessioninfo, $race_list;
		
		//get date 
		if($this->form['page_filter']['special']['date']){
			if($this->form['page_filter']['special']['date'] == 'single_date'){
				if (!$_REQUEST['date']) $_REQUEST['date'] = date('Y-m-d');
			}elseif($this->form['page_filter']['special']['date'] == 'date_range'){
				if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
				if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month +1 day"));
			}elseif($this->form['page_filter']['special']['date'] == 'ymd'){
				//year list
				$con_multi->sql_query("select min(date) as min_date from pos");
				$y_r = $con_multi->sql_fetchrow();
				$con_multi->sql_freeresult();
				$report_min_year = date("Y", strtotime($y_r['min_date']));
				if($report_min_year<2000)	$report_min_year = 2000;	// minimum year 2000
				$report_max_year = date("Y");
				$year_list = array();
				for($i = $report_max_year; $i >= $report_min_year ; $i--){
					$year_list[$i] = $i;
				}
				$smarty->assign('year_list', $year_list);
				
				//month list
				$months = $appCore->monthsList;
				$smarty->assign('months', $months);
			}
		}
		
		// get vendor list
		if($this->form['page_filter']['normal']['vendor']['active']){
			$con_multi->sql_query("select id,description from vendor where active=1 order by description");
			while ($r = $con_multi->sql_fetchassoc()){
				$this->vendor_list[$r['id']] = $r;
			}
			$con_multi->sql_freeresult();
			$smarty->assign('vendor_list',$this->vendor_list);
		}
		
		//sku items
		if($this->form['page_filter']['special']['filter_type']== 'sku' && $_REQUEST['sku_code_list_2']){
			$code_list = $_REQUEST['sku_code_list_2'];
			$list = explode(",",$code_list);
			$category = array();
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
		
		//race list
		if($this->form['page_filter']['normal']['race']['active']){
			$smarty->assign("race_list", $race_list);
		}
		
		//branch list
		if($this->form['page_filter']['normal']['branch']['active']){
			$con_multi->sql_query("select id, code from branch where active=1 order by sequence, code");
			while($r=$con_multi->sql_fetchassoc()){
				$this->branch_list[$r['id']] = $r;
			}
			$con_multi->sql_freeresult();
			$smarty->assign("branch", $this->branch_list);
		}
		
		// brand list
		if($this->form['page_filter']['normal']['brand']['active']){
			$con_multi->sql_query("select id,code,description from brand where brand.active=1 order by description");
			while($r = $con_multi->sql_fetchassoc()){
				$this->brand_list[$r['id']] = $r;
			}
			$con_multi->sql_freeresult();
			$smarty->assign('brand_list', $this->brand_list);
		}
		
		//sku type list
		if($this->form['page_filter']['normal']['sku_type']['active']){
			$con_multi->sql_query("select * from sku_type");
			while($r = $con_multi->sql_fetchassoc()){
				$this->sku_type_list[] = $r;
			}
			$con_multi->sql_freeresult();
			$smarty->assign('sku_type_list', $this->sku_type_list);
		}
		
		//age group list
		if($this->form['page_filter']['normal']['age_group']['active']){
			$con_multi->sql_query("select * from system_settings where setting_name='custom_report_age_group'");
			while($r = $con_multi->sql_fetchassoc()){
				$age_group = unserialize($r['setting_value']);
			}
			if(!$age_group['other']) $age_group['other']="Other";
			$this->age_group_list = $age_group;
			$con_multi->sql_freeresult();
			$smarty->assign('age_group_list', $this->age_group_list);
		}
	}
	
	private function generate_report(){
		global $con_multi, $sessioninfo, $smarty, $config;
		
		$page_filters = $_REQUEST;
		$err = array();
		$params = array();
		
		if(!$this->form){
			die('No Report Form.');
		}
		
		//have sku filter
		if($this->form['page_filter']['special']['filter_type']){
			if($this->form['page_filter']['special']['filter_type'] == "sku" && $page_filters['sku_code_list_2'] == ''){
				$err[] = "Please select SKU.";	
			}else{
				$page_filters['sku'] = $_REQUEST['sku_code_list_2'];
			}
			
			if($this->form['page_filter']['special']['filter_type'] == "category" && $page_filters['category_id'] == '' && !$page_filters['all_category']){
				$err[] = "Please select Category.";	
			}
		}
			
		//sku type checking
		if($this->form['page_filter']['normal']['sku_type']['active'] && !$this->form['page_filter']['normal']['sku_type']['allow_all']){
			if($page_filters['sku_type'] == '') $err[] = "Please select SKU Type.";	
		}

		// brand checking
		if($this->form['page_filter']['normal']['brand']['active'] && !$this->form['page_filter']['normal']['brand']['allow_all']){
			if($page_filters['brand_id'] == '') $err[] = "Please select Brand.";	
		}

		//branch checking
		if($this->form['page_filter']['normal']['branch']['active'] && !$this->form['page_filter']['normal']['branch']['allow_all']){
			if($page_filters['branch_id'] == '') $err[] = "Please select Branch.";	
		}
		
		//vendor checking
		if($this->form['page_filter']['normal']['vendor']['active'] && !$this->form['page_filter']['normal']['vendor']['allow_all']){
			if($page_filters['vendor_id'] == '') $err[] = "Please select Vendor.";	
		}
		
		//member checking
		if($this->form['page_filter']['normal']['member']['active'] && !$this->form['page_filter']['normal']['member']['allow_all']){
			if($page_filters['member'] == '') $err[] = "Please select Member/Non-Member.";	
		}

		//race checking
		if($this->form['page_filter']['normal']['race']['active'] && !$this->form['page_filter']['normal']['race']['allow_all']){
			if($page_filters['race'] == '') $err[] = "Please select Race.";	
		}
		
		//age group checking
		if($this->form['page_filter']['normal']['age_group']['active'] && !$this->form['page_filter']['normal']['age_group']['allow_all']){
			if($page_filters['age_group'] == '') $err[] = "Please select Age Group.";	
		}
		
		if($err){	// got error for page filter
			$smarty->assign('err', $err);
			return false;
		}
		
		$params['page_filter'] = $page_filters;
		$params['report_fields'] = $this->form['report_fields'];
		$params['report_settings'] = $this->form['report_settings'];
		
		$GENERATE_REPORT_DATA = new GENERATE_REPORT_DATA();
		$this->data = $GENERATE_REPORT_DATA->generate($params);
		unset($GENERATE_REPORT_DATA);	// clear memory
		
		if($this->data['err']){	// got error in generate data
			$smarty->assign('err', $this->data['err']);
			return false;
		}
		
		$smarty->assign('data', $this->data);
	}
}
$CUSTOM_REPORT = new CUSTOM_REPORT("Custom Report");
?>