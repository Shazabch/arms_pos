<?php
/*
3/31/2020 11:36 AM William
- Enhanced to add edit and delete function for "Report Group".

4/10/2020 9:04 AM William
- Enhanced to add activate and deactivate to custom report.

6/16/2020 2:34 PM William
- Enhanced to added new setting and new filter "Age Group".

12/21/2020 9:00 AM William
- Enhanced to add new report_settings.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_CUSTOM_BUILDER_CREATE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_CUSTOM_BUILDER_CREATE', BRANCH_CODE), "/index.php");
include("custom_report.include.php");

class REPORT_BUILDER extends Module{
	var $user_list = array();
	
	function __construct($title){
		global $smarty, $con_multi, $appCore, $con;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		// get user list
		$con->sql_query("select id,u,active from user order by u");
		while($r = $con->sql_fetchassoc()){
			$this->user_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('user_list', $this->user_list);
		
		parent::__construct($title);
	}
	
	function _default(){
		$this->load_available_report(); 
		$this->display();
	}
	
	private function load_available_report(){
		global $con, $smarty, $sessioninfo;
		
		$report_list = array();
		$q1 = $con->sql_query("select cr.*, user.u as username from custom_report cr left join user on user.id=cr.user_id where cr.status=0 order by cr.active desc,cr.last_update desc");
		
		while($r=$con->sql_fetchassoc($q1)){
			$r['report_shared_additional_control_user'] = unserialize($r['report_shared_additional_control_user']);
			
			//get share report builder setting
			$control_type = check_can_access_custom_report($sessioninfo['id'], $r);
			
			if(!$control_type) continue;  //remove if not in view or edit control_type
			$r['control_type'] = $control_type;
			
			$report_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign('report_list', $report_list);
	}
	
	function view(){
		$this->open();
	}
	
	function open(){
		global $smarty, $con, $sessioninfo, $report_group, $age_group_list;
		
		$id = mi($_REQUEST['id']);
		
		$can_edit = true;
		if($_REQUEST['a']=='view')	$can_edit = false;
		
		if($id>0){
			$form = load_report_header($id, true);
			if(!$form)	 js_redirect("Invalid Report ID#$id", $_SERVER['PHP_SELF']);
			
			//check control type privilege
			$control_type = check_can_access_custom_report($sessioninfo['id'], $form);
			if(!$control_type) js_redirect("You can't access this Report ID#$id", $_SERVER['PHP_SELF']);
			if($_REQUEST['a'] == 'open' && $control_type != 2) js_redirect("You cannot edit this Report ID#$id", $_SERVER['PHP_SELF']);
		}else{
			$form = array();
		}
		
		//load default report group type
		$report_group2 = $report_group_list = array();
		$con->sql_query("select distinct report_group from custom_report where report_group <> '' order by report_group");
		while($r=$con->sql_fetchassoc()){
			$report_group2[] = $r['report_group'];
		}
		$con->sql_freeresult();
		$report_group = array_keys($report_group);
		$report_group_list = array_merge($report_group, $report_group2);
		$report_group_list = array_unique($report_group_list);
		
		$form['age_group_enable'] = false;
		if($age_group_list['range']) $form['age_group_enable'] = true;
			
		$smarty->assign('form', $form);
		$smarty->assign("can_edit", $can_edit);
		$smarty->assign("report_group_list", $report_group_list);
		$smarty->display("custom_report.builder.open.tpl");
	}
	
	function load_report_group(){
		global $con, $smarty, $sessioninfo;
		
		$report_group_edit_list = array();
		$con->sql_query("select distinct report_group from custom_report where report_group <> '' order by report_group");
		while($r=$con->sql_fetchassoc()){
			$report_group_edit_list[] = $r['report_group'];
		}
		$con->sql_freeresult();
		$smarty->assign("report_group_edit_list", $report_group_edit_list);
		
		$ret = array();
		$ret['html'] = $smarty->fetch("custom_report.report_group_settings.tpl");
		$ret['ok'] = 1;

		print json_encode($ret);
	}
	
	function ajax_update_report_group(){
		global $con;
		
		$form = $_REQUEST;
		$ret = $report_group_list = array();
		$q1 = $con->sql_query("select distinct(report_group) from custom_report");
		while($r1 = $con->sql_fetchassoc($q1)){
			$new_value = $form['report_group_setting_val'][$r1['report_group']] ? $form['report_group_setting_val'][$r1['report_group']]: '';
			$con->sql_query("update custom_report set report_group=".ms($new_value)." where report_group=".ms($r1['report_group']));
			$ret['report_group'][$r1['report_group']] = $new_value;
		}
		$con->sql_freeresult($q1);
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_active_changed(){
		global $con, $sessioninfo;
		
		$form = $_REQUEST;
		$id = $form['id'];
		
		if(!$id){
			die("Invalid Custom Report ID.");
		}
		
		$upd = array();
		$upd['active'] = $form['active'];
		$upd['last_update'] = "CURRENT_TIMESTAMP";
		$q1 = $con->sql_query("update custom_report set ".mysql_update_by_field($upd)." where id =".mi($id));
		log_br($sessioninfo['id'], 'CUSTOM_REPORT', $id, ($form['active'] ? 'Activate' : 'Deactivate')." Custom Report, ID#$id");
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function load_age_group(){
		global $con, $smarty;
		
		$con->sql_query("select * from system_settings where setting_name='custom_report_age_group'");
		$r=$con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($r){
			$form['age_group'] = unserialize($r['setting_value']);
			$smarty->assign("form", $form);
		}
		$ret = array();
		$ret['html'] = $smarty->fetch("custom_report.age_group_settings.tpl");
		$ret['ok'] = 1;

		print json_encode($ret);
	}
	
	function ajax_update_age_group(){
		global $con;
		
		$form = $_REQUEST;
		$setting_name = "custom_report_age_group";
		$upd = $ret = array();
		$upd['setting_name'] = $setting_name;
		$upd['setting_value'] = serialize($form['age_group']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("replace into system_settings ".mysql_insert_by_field($upd));
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_save(){  //save custom report
		global $con, $sessioninfo;
		
		$form = $_REQUEST;
		$id = mi($form['id']);
		
		if(!$form['report_title'])	die('Please key in Report Title');
		if(!$form['report_fields'])	die('Please put some data into report table');
		
		$upd = array();
		$upd['report_title'] = $form['report_title'];
		$upd['report_shared'] = $form['report_shared'];
		$upd['report_shared_additional_control_user'] = serialize($form['report_shared_additional_control_user']);
		$upd['page_filter'] = serialize($form['page_filter']);
		$upd['report_fields'] = serialize($form['report_fields']);
		$upd['report_settings'] = serialize($form['report_settings']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['report_group'] = trim($form['report_group']);
		
		//new
		if(!$id){
			$is_new = true;
			$upd['user_id'] = $sessioninfo['id'];
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into custom_report ".mysql_insert_by_field($upd));
			$id = $con->sql_nextid();
		}else{
			$con->sql_query("update custom_report set ".mysql_update_by_field($upd)." where id=$id");
		}
		
		log_br($sessioninfo['id'], 'CUSTOM_REPORT', $id, ($is_new ? 'Create New' : 'Update')." Custom Report, ID#$id (Title: ".$upd['report_title'].")");
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['id'] = $id;
		
		print json_encode($ret);
	}
	
	function ajax_delete(){ //delete custom report
		global $con, $smarty, $sessioninfo;
		
		$id = mi($_REQUEST['id']);
		
		if(!$id)  die("Invalid ID.");
		
		$form = load_report_header($id, true);
		if(!$form)	die("Report Not Found.");
		
		$control_type = check_can_access_custom_report($sessioninfo['id'], $form);
		if($control_type!= 2)  die("You are not allow to perform this action.");
		
		$upd = array();
		$upd['status'] = 4;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update custom_report set ".mysql_update_by_field($upd)." where id=$id");
		log_br($sessioninfo['id'], 'CUSTOM_REPORT', $id, "Delete Custom Report, ID#$id");
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
	
	function ajax_search_user(){
		global $con;
		
		$v = trim($_REQUEST['term']);
		
		$filter = "user.u like ".ms('%'.$v.'%');
		$con->sql_query("select id,u from user where $filter");
		
		$ret = array();
        while($r = $con->sql_fetchassoc()){
			$r['label'] = $r['u'];
			$ret[] = $r;
		}
		$con->sql_freeresult();
		
		if(!$ret){
			$ret[]['label'] = "No Matches for $v";
		}
		print json_encode($ret);
	}
	
	function preview(){
		global $con_multi, $smarty, $sessioninfo;
		
		$form = $_REQUEST;
		
		$q1 = $con_multi->sql_query("select max(date) as max_date from pos where cancel_status =0");
		$r1 = $con_multi->sql_fetchassoc($q1);
		$max_date = $r1['max_date'];
		$con_multi->sql_freeresult($q1);
		
		
		$con_multi->sql_query("select distinct date from pos where cancel_status=0 order by date desc limit 100");
		while($r2 = $con_multi->sql_fetchassoc()){
			$min_date = $r2['date'];
		}
		$con_multi->sql_freeresult();
		
		
		$params = array();
		$params['is_preview'] = true;
		$params['report_fields'] = $form['report_fields'];
		$params['report_settings'] = $form['report_settings'];
		
		// date filter
		if($form['page_filter']['special']['date']){
			if($form['page_filter']['special']['date'] == 'single_date'){	// single date
				$params['page_filter']['date'] = $max_date;
			}elseif($form['page_filter']['special']['date'] == 'date_range'){	// date range
				$params['page_filter']['date_from'] = $min_date;
				$params['page_filter']['date_to'] = $max_date;
			}elseif($form['page_filter']['special']['date'] == 'ymd'){	// year / month
				if($form['page_filter']['special']['ymd']['year']){
					$params['page_filter']['y'] = mi(date("Y", strtotime($max_date)));
					
					if($form['page_filter']['special']['ymd']['month']){
						$params['page_filter']['m'] = mi(date("m", strtotime($max_date)));
					}
				}
			}
		}else{
			$params['page_filter']['date_from'] = $min_date;
			$params['page_filter']['date_to'] = $max_date;
		}
		
		if(isset($form['page_filter']['normal']['sku_type']['active'])){
			$params['page_filter']['sku_type'] = '';  // all sku type
		}
		
		if(isset($form['page_filter']['normal']['brand']['active'])){
			$params['page_filter']['brand_id'] = 'all';  // all brand
		}
		
		if(isset($form['page_filter']['normal']['branch']['active'])){
			$params['page_filter']['branch_id'] = '';  // all branch
		}
		
		if($form['page_filter']['special']['filter_type'] == 'category'){
			$params['page_filter']['category_id'] = '';	// all category
		}
		
		// vendor
		if(isset($form['page_filter']['normal']['vendor']['active'])){
			$params['page_filter']['vendor_id'] = '';	// all vendor
		}
		
		// race
		if(isset($form['page_filter']['normal']['race']['active'])){
			$params['page_filter']['race'] = '';	// all race
		}
		
		// member
		if(isset($form['page_filter']['normal']['member']['active'])){
			$params['page_filter']['member'] = '';	// all member
		}
		
		// age_group
		if(isset($form['page_filter']['normal']['age_group']['active'])){
			$params['page_filter']['age_group'] = '';	// all age_group
		}

		
		$GENERATE_REPORT_DATA = new GENERATE_REPORT_DATA();
		$data = $GENERATE_REPORT_DATA->generate($params);
		unset($GENERATE_REPORT_DATA);	// clear memory
		
		if($data['err']){	// got error in generate data
			$str = '';
			foreach($data['err'] as $e){
				$str .= "\n$e";
			}

			js_redirect($str, "/index.php");
		}
		
		$smarty->assign('data', $data);
		$smarty->assign('no_menu_templates', 1);
		$this->display('header.tpl');
		
		print "<h1>".$form['report_title']." (Preview)</h1>";
		$this->display('custom_report.report_table.tpl');
		$this->display('footer.tpl');
	}
}
$REPORT_BUILDER = new REPORT_BUILDER('Report Builder');
?>
