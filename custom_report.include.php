<?php
/*
4/10/2020 9:04 AM William
- Enhanced to check status and activate of custom report.

6/16/2020 2:27 PM William
- Enhanced to added new data column Cost, Age, Age Group, Gender.
- Fixed bug when filter by all not checking department privilege, add department privilege filter.

6/30/2020 4:11 PM William
- Enhanced to add new column GP and GP%.
- Bug fixed member_point_earn not accurate issue.

10/30/2020 10:10 PM William
- Enhanced to add new field(Day - Short, Day - Full, Category Lv4, Category Lv5, Color, Size, Gross Amount) to "Custom Report".

12/21/2020 9:47 AM William
- Enhanced to add new report settings.
*/
$maintenance->check(451);

//use report server connection
if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

//Age group 
$con_multi->sql_query("select * from system_settings where setting_name='custom_report_age_group'");
$r = $con_multi->sql_fetchassoc();
$con_multi->sql_freeresult();
$age_group_list = unserialize($r['setting_value']);

$age_group_disabled = true;
if($age_group_list['range']){
	$age_group_disabled = false;
	$qry = array();
	$qry[] = "case";
	foreach($age_group_list['range'] as $col=>$dt_list){
		foreach($dt_list as $key=>$val){
			if($age_group_list['range']['age'][$key] && $age_group_list['range']['desc'][$key])  $qry[] = "when (year(CURDATE())-DATE_FORMAT(member.dob,'%Y')) <= ".$age_group_list['range']['age'][$key]." then ".ms($age_group_list['range']['desc'][$key]);
		}
	}
	$other_age_desc = "Other";
	if($age_group_list['other']) $other_age_desc =  $age_group_list['other'];
	$qry[] = "else if(member.dob = '' or member.dob is null, 'N/A', ".ms($other_age_desc).") end";
	$qry_age_group = implode(" ", $qry);
	unset($qry);
}else $qry_age_group ='"N/A"';  //incase if user delete the age range and search data from old custom report has age group


$member_point_earn_note = 'POS member point earn.\n When got member point earn data, the department privilege will not be filter and does not compatible to below data: \n ARMS Code \n SKU MCode \n SKU Description \n SKU Artno \n Old Code \n Brand Code \n Department(Category level 2) \n Category(Category level 3) \n Vendor Description \n Vendor Code';
//custom report field list
$report_fields_list = array(
	"date"=> array('label'=> 'Date', 'query'=>'pos.date', 'description'=>'YYYY-MM-DD (Example: 2020-03-10)', 'accept_area'=>array('col'=>1, 'row'=>1)),
	"y" => array('label'=>'Year', 'query'=>"year(tbl.date)", 'description'=>'YYYY (Example: 2020)', 'sort'=>1, 'accept_area'=>array('col'=>1, 'row'=>1)),
	"m" => array('label'=>'Month', 'query'=>"month(tbl.date)", 'description'=>"MM (Example: 01-12)", 'sort'=>1, 'accept_area'=>array('col'=>1, 'row'=>1)),
	"d" => array('label'=>'Day', 'query'=>"day(tbl.date)", 'description'=>'DD (Example: 01-31)', 'sort'=>1, 'accept_area'=>array('col'=>1, 'row'=>1)),
	"hr" => array('label'=>'Hour', 'query'=>"hour(pos.pos_time)", 'description'=>'H (Example: 01-24)', 'sort'=>1, 'accept_area'=>array('col'=>1, 'row'=>1)),
	"member_no" => array('label'=>'Member No', 'query'=>"if(pos.member_no ='', 'Not Member', pos.member_no)", 'align'=>'right', 'accept_area'=>array('col'=>1, 'row'=>1)),
	"member_point_earn" => array('label'=>'Member Points Earn', 'query'=>"if(pos.member_no is null or pos.member_no='' or tbl.item_id>1,null, pos.point)", 'modifier'=>'num', 'description'=>$member_point_earn_note, 'align'=>'right', 'accept_area'=>array('data'=>1)),
	"member_name" => array('label'=>'Member Name', 'query'=>"if(pos.member_no ='', 'Not Member',member.name)", 'sort'=>1, 'accept_area'=>array('col'=>1, 'row'=>1)),
	"is_member" => array('label'=>'Member / Non-Member', 'query'=>"if(pos.member_no ='', 'Non-Member', 'Member')", 'align'=>'right', 'accept_area'=>array('col'=>1, 'row'=>1)),
	"race" => array('label'=>'Race', 'query'=>"if(pos.member_no = '', case pos.race when 'C' then 'Chinese' when 'M' then 'Malay' when 'I' then 'Indian' when 'O' then 'Others' else 'Others' end, if(member.race='' or member.race is null,'Others',member.race))", 'sort'=>1, 'accept_area'=>array('col'=>1, 'row'=>1)),
	"sku_type" => array('label'=>'SKU Type', 'query'=>"sku.sku_type", 'accept_area'=>array('col'=>1, 'row'=>1)),
	"sku_item_code" => array('label'=>'ARMS Code', 'query'=>"si.sku_item_code",  'group_by'=>'si.sku_item_code', 'accept_area'=>array('col'=>1, 'row'=>1)),
	"sku_item_mcode"=>array('label'=>'SKU MCode', 'query'=>"si.mcode",  'group_by'=>'si.mcode', 'accept_area'=>array('col'=>1, 'row'=>1)),
	"sku_desc" => array('label'=>'SKU Description', 'query'=>"si.description",  'group_by'=>'si.description', 'accept_area'=>array('col'=>1, 'row'=>1)),
	"sku_artno"=> array('label'=>'SKU Artno', 'query'=>'si.artno',  'group_by'=>'si.artno', 'accept_area'=>array('col'=>1, 'row'=>1)),
	"sku_link_code" =>array('label'=> $config['link_code_name'], 'query'=>'si.link_code',  'group_by'=>'si.link_code', 'accept_area'=>array('col'=>1, 'row'=>1)),
	"qty" => array('label'=>'Qty', 'query'=>"tbl.qty", 'description'=>'Sales Qty.', 'align'=>'right', 'modifier'=>'num', 'accept_area'=>array('data'=>1)),
	"amt" => array('label'=>'Amount', 'query'=>'(tbl.price-tbl.discount-tbl.discount2-tbl.tax_amount)', 'description'=>'After deduct item discount, receipt discount and tax amount.', 'align'=>'right', 'modifier'=>'num2', 'accept_area'=>array('data'=>1)),
	"discount_amt" => array('label'=>'Item Discount Amount', 'query'=>"tbl.discount", 'align'=>'right', 'modifier'=>'num2', 'accept_area'=>array('data'=>1)),
	"discount_amt2" => array('label'=>'Receipt Discount Amount', 'query'=>"tbl.discount2", 'align'=>'right', 'modifier'=>'num2', 'accept_area'=>array('data'=>1)),
	"tax_amount" => array('label'=>'Tax Amount', 'query'=>"tbl.tax_amount", 'align'=>'right', 'modifier'=>'num2', 'accept_area'=>array('data'=>1)),
	"gross_amt"=>array('label'=> 'Gross Amount', 'description'=>'(Price - Tax Amount)', 'query'=>"(tbl.price-tbl.tax_amount)", 'align'=>'right', 'modifier'=>'num2', 'accept_area'=>array('data'=>1)),
	"counter_name" => array('label'=>'Counter Name', 'query'=>"cs.network_name", 'group_by'=>'cs.network_name', 'accept_area'=>array('col'=>1, 'row'=>1)),
	"receipt_ref_no"=> array('label'=>'Receipt Ref No', 'query'=>"pos.receipt_ref_no", 'accept_area'=>array('col'=>1, 'row'=>1)),
	"branch_code" => array('label'=>'Branch', 'query'=>"branch.code", 'description'=>'Branch Code.', 'group_by'=>'branch.id', 'accept_area'=>array('col'=>1, 'row'=>1)),
	"brand_description" => array('label'=>'Brand Description', 'query'=>"if(brand.description is null or brand.description = '', 'UN-BRANDED',brand.description)", 'accept_area'=>array('col'=>1, 'row'=>1)),
	"brand_code" => array('label'=>'Brand Code', 'query'=>"if(brand.code is null or brand.code='' and brand.id = '', 'UN-BRANDED', if(brand.code is null or brand.code ='', 'NO BRAND CODE', brand.code))", 'accept_area'=>array('col'=>1, 'row'=>1)),
	"dept_desc" => array('label'=>'Department (Category level 2)', 'query'=>"dept.description", 'accept_area'=>array('col'=>1, 'row'=>1)),
	"cat3_desc" => array('label'=>'Category (Category level 3)', 'query'=>"if(cat3.description is null or cat3.description= '', dept.description, cat3.description)", 'accept_area'=>array('col'=>1, 'row'=>1)),
	"cat4_desc"=>array('label'=>'Category (Category level 4)', 'query'=>"cat4.description", 'accept_area'=>array('col'=>1, 'row'=>1)),
	"cat5_desc"=>array('label'=>'Category (Category level 5)', 'query'=>"cat5.description", 'accept_area'=>array('col'=>1, 'row'=>1)),
	"vendor_desc" => array('label'=>'Vendor Description', 'query'=>"if(vendor.description is null or vendor.description = '', 'NO VENDOR', vendor.description)", 'accept_area'=>array('col'=>1, 'row'=>1)),
	"vendor_code" => array('label'=>'Vendor Code', 'query'=>"if(vendor.code is null or vendor.code='', 'NO VENDOR', vendor.code)", 'accept_area'=>array('col'=>1, 'row'=>1)),
	"cost" => array('label'=>'Cost', 'query'=>'(sifc.unit_cost*tbl.qty)', 'align'=>'right', 'modifier'=>'config_global_cost', 'accept_area'=>array('data'=>1)),
	"gp" => array('label'=>'GP', 'description'=>'(Amount - Cost)', 'query'=>'((tbl.price-tbl.discount-tbl.discount2-tbl.tax_amount)-(sifc.unit_cost*tbl.qty))', 'align'=>'right', 'modifier'=>'num2', 'accept_area'=>array('data'=>1)),
	"gp_percent" => array('label'=>'GP(%)', 'description'=>'(GP*100) / Amount', 'query'=>'if((tbl.price-tbl.discount-tbl.discount2-tbl.tax_amount) > 0, sum(((tbl.price-tbl.discount-tbl.discount2-tbl.tax_amount)-(sifc.unit_cost*tbl.qty))*100)/ sum(tbl.price-tbl.discount-tbl.discount2-tbl.tax_amount), 0)', 'align'=>'right', 'modifier'=>'num2', 'accept_area'=>array('data'=>1), 'extra_column'=>array('amt'=>'sum', 'gp'=>'sum')),
	"age" => array('label'=>'Age', 'description'=>'Member age (Example: 20)', 'query'=>"if(member.dob > 0 or member.dob <> '',year(CURDATE())-DATE_FORMAT(member.dob,'%Y'), 'N/A')", 'accept_area'=>array('col'=>1, 'row'=>1)),
	"age_group" => array('label'=>'Age Group', 'query'=>$qry_age_group, 'description'=>'Group the member age by age group setting, only available when the age group settings has set the age range. (Example: Children)', 'accept_area'=>array('col'=>1, 'row'=>1), 'disabled'=>$age_group_disabled),
	"gender"=>array('label'=> 'Gender', 'description'=>'Member gender (Example: Male / Female)','query'=>'if(member.gender, (case member.gender when "M" then "Male" when "F" then "Female" else "" end), "N/A")', 'accept_area'=>array('col'=>1, 'row'=>1)),
	"day_name_short"=>array('label'=> '(Day - Short)', 'description'=>'Day name (Example: Mon, Tue, Wed)', 'query'=>"DATE_FORMAT(tbl.date, '%a')", 'accept_area'=>array('col'=>1, 'row'=>1)),
	"day_name_full"=>array('label'=> '(Day - Full)', 'description'=>'Day name (Example: Monday, Tuesday, Wednesday)', 'query'=>"DATE_FORMAT(tbl.date, '%W')", 'accept_area'=>array('col'=>1, 'row'=>1)),
	"sku_size"=>array('label'=> 'Size', 'description'=>'SKU item size', 'query'=>"si.size", 'group_by'=>'si.size', 'accept_area'=>array('col'=>1, 'row'=>1)),
	"sku_color"=>array('label'=> 'Color', 'description'=>'SKU item color', 'query'=>"si.color", 'group_by'=>'si.color', 'accept_area'=>array('col'=>1, 'row'=>1)),
);

//category 4, 5 checking
$con_multi->sql_query("select max(level) as max_level from category where active =1");
$r2 = $con_multi->sql_fetchassoc();
$con_multi->sql_freeresult();
if(mi($r2['max_level']) <= 4)  unset($report_fields_list['cat4_desc']);
if(mi($r2['max_level']) <= 5)  unset($report_fields_list['cat5_desc']);

// table left join query
$report_left_join_list = array(
	'pos'=> array('qry_sort'=>0 ,'qry_join'=>'left join pos on pos.id = tbl.pos_id and tbl.branch_id = pos.branch_id and tbl.date = pos.date and tbl.counter_id = pos.counter_id'),
	'pos_finalized'=>array('qry_sort'=>1, 'qry_join'=>'join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date and pf.finalized=1'),
	'si' => array('qry_sort'=>2 ,'qry_join'=>'left join sku_items si on si.id=tbl.sku_item_id'),
	'branch' => array('qry_sort'=>3 ,'qry_join'=>'left join branch on branch.id = tbl.branch_id'),
	'cs'=> array('qry_sort'=>4 ,'qry_join'=>'left join counter_settings cs on cs.id = pos.counter_id and cs.branch_id = pos.branch_id'),
	'member'=> array('qry_sort'=>5 ,'qry_join'=>'left join membership member on member.membership_guid=pos.membership_guid'),
	'sku'=> array('qry_sort'=>6 ,'qry_join'=>'left join sku on sku.id= si.sku_id'),
	'brand' => array('qry_sort'=>7 ,'qry_join'=>'left join brand on brand.id = sku.brand_id'),
	'vendor' => array('qry_sort'=>8 ,'qry_join'=>'left join vendor on vendor.id=sku.vendor_id'),
	'cc' => array('qry_sort'=>9 ,'qry_join'=>'left join category_cache cc on cc.category_id=sku.category_id'),
	'cat3' => array('qry_sort'=>10 ,'qry_join'=>'left join category cat3 on cat3.id=cc.p3'),
	'cat4' => array('qry_sort'=>11 ,'qry_join'=>'left join category cat4 on cat4.id=cc.p4'),
	'cat5' => array('qry_sort'=>12 ,'qry_join'=>'left join category cat5 on cat5.id=cc.p5'),
	'dept' => array('qry_sort'=>13 ,'qry_join'=>'left join category dept on dept.id=cc.p2'),
	'sku_items_finalised_cache' => array('qry_sort'=>14 ,'qry_join'=>'left join sku_items_finalised_cache sifc on tbl.date=sifc.date and sifc.branch_id=tbl.branch_id and sifc.sku_item_id=tbl.sku_item_id'),
);

// Report Group and group privilege
$report_group = array("Sales Report"=>"REPORTS_SALES", "Performance Report"=>"REPORTS_PERFORMANCE","Membership Report"=>"REPORTS_MEMBERSHIP","SKU Report"=>"REPORTS_SKU");

// race list
$race_list = array("M"=>"Malay", "C"=>"Chinese", "I"=>"Indian", "O"=>"Others");

// get custom report 
function load_report_header($report_id, $view_deactivate = false){
	global $con_multi;
	
	$report_id = mi($report_id);
	if(!$report_id)	return false;
	
	if(!$view_deactivate) $activate="and cr.active =1";
	$con_multi->sql_query("select cr.*, user.u as username from custom_report cr 
	left join user on user.id=cr.user_id 
	where cr.id=$report_id and cr.status=0 $activate");
	$form = $con_multi->sql_fetchassoc();
	$con_multi->sql_freeresult();
	
	if(!$form)	return false;
	
	$form['report_shared_additional_control_user'] = unserialize($form['report_shared_additional_control_user']);
	$form['page_filter'] = unserialize($form['page_filter']);
	$form['report_fields'] = unserialize($form['report_fields']);
	$form['report_settings'] = unserialize($form['report_settings']);
	
	return $form;
}

//load report fields list
if($smarty){
	$smarty->assign("report_fields_list", $report_fields_list);
}
class GENERATE_REPORT_DATA {
	var $data = array();
	function generate($params){
		global $con_multi, $LANG, $report_fields_list, $report_left_join_list, $race_list, $appCore, $qry_age_group, $sessioninfo;
	
		if(!$params)  die("No Parameter.");
		if(!$params['report_fields']['col'])	die("No Report Column");
		if(!$params['report_fields']['row'])	die("No Report Row");
		if(!$params['report_fields']['data'])	die("No Report Data");
		if(!$params['page_filter'])	die("No Page Filters");
		
		$this->data['is_preview'] = $is_preview = $params['is_preview'];
		
		if(isset($params['page_filter']['date'])){   //date single data
			if(!$params['page_filter']['date']){
				$this->data['err'][] = "Please select Date.";
			}else{
				$date_from = $date_to = $params['page_filter']['date'];
				// report title
				$this->data['report_title']['date'] = "Date: ".$date_from;
			}
		}
		
		if(isset($params['page_filter']['date_from']) && isset($params['page_filter']['date_to'])){  // date range
			if(!$params['page_filter']['date_from'])	$this->data['err'][] = "Please select Date From.";
			else  $date_from = $params['page_filter']['date_from'];
			
			if(!$params['page_filter']['date_to'])	$this->data['err'][] = "Please select Date To.";
			else  $date_to = $params['page_filter']['date_to'];
			
			if($params['page_filter']['date_from'] > $params['page_filter']['date_to']) $this->data['err'][] = sprintf($LANG['DATE_TO_FROM_ERROR'], "Date To", "Date From");
			
			// report title
			$this->data['report_title']['date'] = "Date: ".$date_from." to ".$date_to;
		}
		
		// Year / Month
		if(isset($params['page_filter']['y'])){	// year
			if(!$params['page_filter']['y'])	$this->data['err'][] = "Please select Year.";
			
			// report title
			$this->data['report_title']['y'] = "Year: ".$params['page_filter']['y'];
			
			if(isset($params['page_filter']['m'])){	// month
				if(!$params['page_filter']['m'])	$this->data['err'][] = "Please select Month.";
				
				// report title
				$this->data['report_title']['m'] = "Month: ".$appCore->monthsList[$params['page_filter']['m']];
			}
			
			if(!$this->data['err']){
				if(!$params['page_filter']['m']){
					$date_from = $params['page_filter']['y'].'-01-01';
					$date_to = $params['page_filter']['y'].'-12-31';
				}else{
					$date_from = $params['page_filter']['y'].'-'.$params['page_filter']['m'].'-01';
					$date_to = $params['page_filter']['y'].'-'.$params['page_filter']['m'].'-'.days_of_month($params['page_filter']['m'], $params['page_filter']['y']);
				}
			}
		}

		$filter = array();
		$left_join_list = array();
		$left_join_list[] = 'pos';
		$left_join_list[] = 'pos_finalized';
		
		if(isset($params['page_filter']['branch_id'])){   //has branch filter
			if($params['page_filter']['branch_id']){
				$filter[] = "tbl.branch_id=".mi($params['page_filter']['branch_id']);
				
				$left_join_list[] = 'branch';
				$con_multi->sql_query("select code from branch where id=".mi($params['page_filter']['branch_id']));
				$tmp = $con_multi->sql_fetchassoc();
				$con_multi->sql_freeresult();
				
				if(BRANCH_CODE=='HQ'){
					// report title
					$this->data['report_title']['branch_id'] = "Branch: ".$tmp['code'];
				}
			}else{
				if(BRANCH_CODE=='HQ'){
					// report title
					$this->data['report_title']['branch_id'] = "Branch: All";
				}
			}
		}
		
		if(isset($params['page_filter']['vendor_id'])){   //has vendor filter
			if($params['page_filter']['vendor_id']){
				$filter[] = "sku.vendor_id=".mi($params['page_filter']['vendor_id']);
				
				$left_join_list[] = 'si';
				$left_join_list[] = 'sku';	
				$left_join_list[] = 'vendor';	
				$con_multi->sql_query("select code, description from vendor where id=".mi($params['page_filter']['vendor_id']));
				$tmp = $con_multi->sql_fetchassoc();
				$con_multi->sql_freeresult();
				
				// report title
				$this->data['report_title']['vendor_id'] = "Vendor: ".$tmp['description'];
			}else{
				// report title
				$this->data['report_title']['vendor_id'] = "Vendor: All";
			}
		}
		
		if(isset($params['page_filter']['brand_id'])){   //has brand filter
			if($params['page_filter']['brand_id'] != 'all'){
				if($params['page_filter']['brand_id']){
					$filter[] = "sku.brand_id=".mi($params['page_filter']['brand_id']);
					
					$left_join_list[] = 'si';
					$left_join_list[] = 'sku';
					$left_join_list[] = 'brand';
					
					$con_multi->sql_query("select description from brand where id=".mi($params['page_filter']['brand_id']));
					$tmp = $con_multi->sql_fetchassoc();
					$con_multi->sql_freeresult();
					
					// report title
					$this->data['report_title']['brand_id'] = "Brand: ".$tmp['description'];
				}else{
					$filter[] = "sku.brand_id=".mi($params['page_filter']['brand_id']);
					
					// report title
					$this->data['report_title']['brand_id'] = "Brand: UN-BRANDED";
				}
			}else{
				// report title
				$this->data['report_title']['brand_id'] = "Brand: All";
			}
		}
		
		if(isset($params['page_filter']['sku_type'])){   //has brand filter
			if($params['page_filter']['sku_type']){
				$filter[] = "sku.sku_type=".ms($params['page_filter']['sku_type']);
				
				$left_join_list[] = 'si';
				$left_join_list[] = 'sku';
				
				$con_multi->sql_query("select description from sku_type where code=".ms($params['page_filter']['sku_type']));
				$tmp = $con_multi->sql_fetchassoc();
				$con_multi->sql_freeresult();
				
				// report title
				$this->data['report_title']['sku_type'] = "SKU Type: ".$tmp['description'];
			}else{
				// report title
				$this->data['report_title']['sku_type'] = "SKU Type: All";
			}
		}
		
		if (isset($params['page_filter']['sku'])){  //has sku filter
			if($params['page_filter']['sku']){
				$filter[] = "si.sku_item_code in(".$params['page_filter']['sku'].")";
				$left_join_list[] = 'si';
			}
		}elseif (isset($params['page_filter']['category_id'])){   //has category filter
			if($params['page_filter']['category_id']){
				$cat_info = get_category_info($params['page_filter']['category_id']);
				if(!$cat_info)	$this->data['err'][] = $LANG['REPORT_NO_CATEGORY'];
				
				$filter[] = "cc.p".$cat_info['level']."=".mi($params['page_filter']['category_id']);
				$left_join_list[] = 'si';
				$left_join_list[] = 'sku';
				$left_join_list[] = 'cc';
				
				// report title
				$this->data['report_title']['category_id'] = "Dept/Category: ".$cat_info['description'];
			}else{
				// report title
				$this->data['report_title']['category_id'] = "Dept/Category: All";
			}
		}
		
		if(isset($params['page_filter']['member'])){	// member
			if($params['page_filter']['member']){
				if($params['page_filter']['member'] == 1){
					$filter[] = "pos.member_no <> ''";
					
					// report title
					$this->data['report_title']['member'] = "Member / Non-Member: Member";
				}else{
					$filter[] = "pos.member_no =''";
					
					// report title
					$this->data['report_title']['member'] = "Member / Non-Member: Non-Member";
				}
			}else{
				// report title
				$this->data['report_title']['member'] = "Member / Non-Member: All";
			}
		}
		
		if(isset($params['page_filter']['race'])){	// race
			if($params['page_filter']['race']){
				$left_join_list[] = 'member';
				$race = $params['page_filter']['race'];
				$filter[] = "if(pos.member_no = '', case pos.race when 'C' then 'Chinese' when 'M' then 'Malay' when 'I' then 'Indian'  when 'O' then 'Others' else 'Others' end, if(member.race='' or member.race is null,'Others',member.race))=".ms($race_list[$race]);
				
				// report title
				$this->data['report_title']['race'] = "Race: ".$race_list[$params['page_filter']['race']];
			}else{
				// report title
				$this->data['report_title']['race'] = "Race: All";
			}
		}
		
		//Age group 
		if(isset($params['page_filter']['age_group'])){	// age group
			if($params['page_filter']['age_group']){
				$left_join_list[] = 'member';
				$age_group = $params['page_filter']['age_group'];
				
				$filter[] = $qry_age_group."=".ms($age_group) ;
				
				// report title
				$this->data['report_title']['age_group'] = "Age Group: ".$age_group;
			}else{
				// report title
				$this->data['report_title']['age_group'] = "Age Group: All";
			}
		}
		
		// column
		$select_col_list = array();
		$group_by_list = array();
		$distinct_field_list = array();
		$rpt_col_info = array();	
		foreach($params['report_fields']['col'] as $col_info){
			if(!$rpt_col_info['key_list'])	$rpt_col_info['key_list'] = array();
			
			// distinct field list
			$distinct_field_list[$col_info['field_type']] = $col_info['field_type'];
			
			// column to select
			if(!$select_col_list[$col_info['field_type']]){
				
				$select_col_list[$col_info['field_type']] = $report_fields_list[$col_info['field_type']]['query']." as ".$col_info['field_type'];
				
				if($report_fields_list[$col_info['field_type']]['extend_field']){
					$select_col_list[$report_fields_list[$col_info['field_type']]['extend_field']] = $report_fields_list[$col_info['field_type']]['extend_field'];	
				}
			}
			
			// field to group by
			if($report_fields_list[$col_info['field_type']]['group_by']){
				$group_by_list[$col_info['field_type']] = $report_fields_list[$col_info['field_type']]['group_by'];
			}else{
				$group_by_list[$col_info['field_type']] = $col_info['field_type'];		
			}
			
			if(!in_array($col_info['field_type'], $rpt_col_info['key_list'])) $rpt_col_info['key_list'][]= $col_info['field_type'];
		}
		
		// row
		$rpt_row_info = array();
		foreach($params['report_fields']['row'] as $row_info){
			if(!$rpt_row_info['key_list'])	$rpt_row_info['key_list'] = array();
			
			// distinct field list
			$distinct_field_list[$row_info['field_type']] = $row_info['field_type'];
			
			// column to select
			if(!$select_col_list[$row_info['field_type']]){
				$select_col_list[$row_info['field_type']] = $report_fields_list[$row_info['field_type']]['query']." as ".$row_info['field_type'];
				
				if($report_fields_list[$row_info['field_type']]['extend_field']){
					$select_col_list[$report_fields_list[$row_info['field_type']]['extend_field']] = $report_fields_list[$row_info['field_type']]['extend_field'];	
				}
			}
			
			// field to group by
			if($report_fields_list[$row_info['field_type']]['group_by']){
				$group_by_list[$row_info['field_type']] = $report_fields_list[$row_info['field_type']]['group_by'];
			}else{
				$group_by_list[$row_info['field_type']] = $row_info['field_type'];
				
			}
			if(!in_array($row_info['field_type'], $rpt_row_info['key_list']))	$rpt_row_info['key_list'][]= $row_info['field_type'];
		}
		
		// data
		foreach($params['report_fields']['data'] as $key => $data_info){
			// distinct field list
			$distinct_field_list[$data_info['field_type']] = $data_info['field_type'];
			
			if($data_info['field_formula'])  $tmp_col = $data_info['field_formula'].'_'.$data_info['field_type'];
			else  $tmp_col = $data_info['field_type'];
			
			$params['report_fields']['data'][$key]['col_name'] = $tmp_col;
			
			// column to select
			if(!$select_col_list[$tmp_col]){
				$select_col_list[$tmp_col] = $data_info['field_formula']."(".$report_fields_list[$data_info['field_type']]['query'].") as ".$tmp_col;
				// for extra column
				if($report_fields_list[$data_info['field_type']]['extra_column']){
					$extra_column = $report_fields_list[$data_info['field_type']]['extra_column'];
					foreach($extra_column as $col=>$column_formula){
						$tmp_col2 = strtoupper($column_formula).'_'.$col;
						$select_col_list[$tmp_col2] = $column_formula."(".$report_fields_list[$col]['query'].") as ".$tmp_col2;
					}
				}
			}
			if($data_info['field_type'] == 'member_point_earn')  $this->data['has_member_point_earn'] = 1;
		}
		
		//no category id filter department
		if(!$params['page_filter']['category_id'] && !$this->data['has_member_point_earn']){
			$left_join_list[] = 'si';
			$left_join_list[] = 'sku';
			$left_join_list[] = 'cc';	
			$left_join_list[] = 'dept';
			$filter[] = "dept.department_id in ($sessioninfo[department_ids])";
		}
		
		foreach($distinct_field_list as $tmp_field_type){	// check all field type
			switch($tmp_field_type){
				case 'dept_desc':	// department description
					$left_join_list[] = 'si';
					$left_join_list[] = 'sku';		// need left join sku
					$left_join_list[] = 'cc';		// need left join category_cache
					$left_join_list[] = 'dept';		// need left join department
					break;
				case 'sku_type':
					$left_join_list[] = 'si';		// need left join sku items
					$left_join_list[] = 'sku';		// need left join sku
					break;
				case 'sku_item_mcode':	
				case 'sku_item_code':	
				case 'sku_desc':	
				case 'sku_artno': 
				case 'sku_link_code':
				case 'sku_size':
				case 'sku_color':
					$left_join_list[] = 'si';		// need left join sku items
					break;
				case 'vendor_desc':
				case 'vendor_code':
					$left_join_list[] = 'si';		// need left join sku items
					$left_join_list[] = 'sku';		// need left join sku
					$left_join_list[] = 'vendor';	// need left join vendor
					break;
				case 'cat3_desc':	// category description
				case 'cat4_desc':
				case 'cat5_desc':
					$left_join_list[] = 'si';
					$left_join_list[] = 'sku';		// need left join sku
					$left_join_list[] = 'cc';		// need left join category_cache
					if($tmp_field_type == 'cat3_desc'){
						$left_join_list[] = 'dept';		// need left join department
						$left_join_list[] = 'cat3';		// need left join department
					}
					if($tmp_field_type == 'cat4_desc') $left_join_list[] = 'cat4';
					if($tmp_field_type == 'cat5_desc') $left_join_list[] = 'cat5';
					break;
				case 'branch_code':
					$left_join_list[] = 'branch';	//left join branch
					break;
				case 'brand_description':
				case 'brand_code':
					$left_join_list[] = 'si';		//left join sku_items
					$left_join_list[] = 'sku';		//left join sku
					$left_join_list[] = 'brand';	//left join brand
					break;
				case 'race':
				case 'member_name':
				case 'age';
				case 'age_group';
				case 'gender':
					$left_join_list[] = 'member'; 	//left join membership
					break;
				case 'counter_name':
					$left_join_list[] = 'cs';		//left join counter_settings
					break;
				case 'cost':
				case 'gp':
				case 'gp_percent':
					$left_join_list[] = 'sku_items_finalised_cache';
					break;
			}
		}
		
		$left_join_list = array_unique($left_join_list);   // unique join table
		
		//get left join query 
		$left_join_sort_list = array();
		foreach($left_join_list as $key=>$val){
			$left_join_sort_list[$report_left_join_list[$val]['qry_sort']] = $report_left_join_list[$val]['qry_join'];
		}
		
		// sort left join table to avoid mysql error
		ksort($left_join_sort_list);

		$fields_str = implode(", ", $select_col_list);
		
		$group_by_list = array_unique($group_by_list);  	//unique group by column value
		$group_by_str = "group by ".join(',', $group_by_list);
		
		$left_join_str = join(' ', $left_join_sort_list);
		
		$this->data['params'] = $params;
		$this->data['col_info'] = $rpt_col_info;
		$this->data['row_info'] = $rpt_row_info;
		$this->data['col_span_multiply'] = count($params['report_fields']['data']);
		$this->data['data'] = array();
		$this->data['total']['col'] = array();
		$this->data['total']['row'] = array();
		
		if($is_preview)	$query_limit = "limit 100";	// limit item for preview
		
		$tmp_filter = array_unique($filter);
		$tmp_filter[] = "tbl.date between ".ms($date_from)." and ".ms($date_to);
		$tmp_filter[] = "pos.cancel_status =0";   //only get active pos data
		
		$tmp_filter = "where ".join(' and ', $tmp_filter);
		
		$sql = "select $fields_str 
		from pos_items as tbl ".
		$left_join_str."  
		".$tmp_filter." ".$group_by_str." ".$query_limit;
		//echo $sql;
		$q1 = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q1)){
			if($r){
				$this->generate_col_row($this->data['data'], $this->data['col_info']['data_list'], $this->data['row_info']['data_list'], $this->data['total']['col'], $this->data['total']['row'], $r, 'row', 0);
			}
		}
		$con_multi->sql_freeresult($q1);
		
		if($this->data['data']){	// got data
			if($this->data['col_info']['data_list']){
				$this->calculate_col_span($this->data['col_info']['data_list']);
			}
			
			if($this->data['row_info']['data_list']){
				$this->calculate_row_span($this->data['row_info']['data_list']);
			}
			
			$this->data['report_title_str'] = join('&nbsp;&nbsp;&nbsp;', $this->data['report_title']);
		}
		
		return $this->data;
	}
	
	private function generate_col_row(&$child_data, &$col_list, &$row_list, &$total_col, &$total_row, $r, $type, $num){
		if($type == 'col'){
			$key = $this->data['col_info']['key_list'][$num];
			if(!$key)	die("Invalid column num ($num)");
		}else{
			$key = $this->data['row_info']['key_list'][$num];
			if(!$key)	die("Invalid row num ($num)");
		}
		
		$value = $r[$key];
		$child_data['type'] = $type;
		$child_data['key'] = $key;
		
		if(!isset($child_data['data'][$value]))	$child_data['data'][$value] = array();
		
		$next_num = $num+1;
		$columns= array();
		foreach($this->data['params']['report_fields']['data'] as $field_info){
			$columns[] = $field_info['field_type'];
		}
		if($type == 'row'){
			$row_list['next_key'] = $key;
			
			if(!isset($row_list['list'][$value]))	$row_list['list'][$value] = array();
			if(!isset($total_row['data'][$value]))	$total_row['data'][$value] = array();
			
			if($this->data['row_info']['key_list'][$next_num]){	// got next row
				$this->generate_col_row($child_data['data'][$value], $col_list, $row_list['list'][$value], $total_col, $total_row['data'][$value], $r, $type, $next_num);
			}else{	// start column
				foreach($this->data['params']['report_fields']['data'] as $field_info){	// loop for each data
					if($field_info['field_formula'] == 'SUM' || $field_info['field_formula'] == 'COUNT'){
						$total_row['data'][$value][$field_info['col_name']] += $r[$field_info['col_name']];
					}
					
					if($report_fields_list[$field_info['field_type']]['extra_column']){
						$extra_column = $report_fields_list[$field_info['field_type']]['extra_column'];
						foreach($extra_column as $col=>$column_formula){
							$tmp_col = strtoupper($column_formula).'_'.$col;
							if(!in_array($col ,$columns) && strtoupper($column_formula) == 'SUM'){
								$total_row['data'][$value][$tmp_col] += $r[$tmp_col];
							}
						}
					}
				}
				if(in_array('gp_percent' ,$columns)){
					$total_row['data'][$value]['gp_percent'] = ($total_row['data'][$value]['SUM_gp']*100)/ $total_row['data'][$value]['SUM_amt'];
				}
				$this->generate_col_row($child_data['data'][$value], $col_list, $row_list, $total_col, $total_row, $r, 'col', 0);
			}
		}else{
			$col_list['next_key'] = $key;
			
			if(!isset($col_list['list'][$value]))	$col_list['list'][$value] = array();
			if(!isset($total_col['data'][$value]))	$total_col['data'][$value] = array();
			
			if($this->data['col_info']['key_list'][$next_num]){	// got next column
				$this->generate_col_row($child_data['data'][$value], $col_list['list'][$value], $row_list, $total_col['data'][$value], $total_row, $r, $type, $next_num);
			}else{	// end at this column
				foreach($this->data['params']['report_fields']['data'] as $field_info){	// loop for each data
					if($field_info['field_formula'] == 'SUM' || $field_info['field_formula'] == 'COUNT'){
						$total_col['data'][$value][$field_info['col_name']] += $r[$field_info['col_name']];
						// grand total
						$this->data['total']['total'][$field_info['col_name']] += $r[$field_info['col_name']];
					}
					
					if($report_fields_list[$field_info['field_type']]['extra_column']){
						$extra_column = $report_fields_list[$field_info['field_type']]['extra_column'];
						foreach($extra_column as $col=>$column_formula){
							$tmp_col = strtoupper($column_formula).'_'.$col;
							if(!in_array($col ,$columns) && strtoupper($column_formula) == 'SUM'){
								$total_col['data'][$value][$tmp_col] += $r[$tmp_col];
								$this->data['total']['total'][$tmp_col] += $r[$tmp_col];
							}
						}
					}
				}
				if(in_array('gp_percent' ,$columns)){	//when have column
					$total_col['data'][$value]['gp_percent'] = ($total_col['data'][$value]['SUM_gp']*100)/ $total_col['data'][$value]['SUM_amt'];
					$this->data['total']['total']['gp_percent'] = ($this->data['total']['total']['SUM_gp']*100)/ $this->data['total']['total']['SUM_amt'];
				}
				$child_data['data'][$value] = $r;
			}
		}
		unset($columns);
	}
	
	private function calculate_col_span(&$col_list){
		global $report_fields_list;
		
		$total_span = 0;
		
		if($col_list){
			if($col_list['list']){
				ksort($col_list['list']);	// sort all
			
				foreach($col_list['list'] as $value => &$col_data){
					$span_count = 0;
				
					if($col_data['list']){
						$span_count = $this->calculate_col_span($col_data);
					}else{
						$span_count = $this->data['col_span_multiply'];
					}
					
					$col_data['span'] = $span_count;
					$total_span+=$span_count;
				}
			}			
		}
		
		return $total_span;
	}
	
	private function calculate_row_span(&$row_list){
		global $report_fields_list;
		
		$total_span = 0;
		if($row_list){
			if($row_list['list']){
				ksort($row_list['list']);	// sort all
				
				foreach($row_list['list'] as $value => &$row_data){
					$span_count = 0;
				
					if($row_data['list']){
						$span_count = $this->calculate_row_span($row_data);
					}else{
						$span_count = 1;
					}
					
					$row_data['span'] = $span_count;
					$total_span+=$span_count;
				}
			}			
		}		
		return $total_span;
	}
}
?>