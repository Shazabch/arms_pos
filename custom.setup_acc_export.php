<?php
/*
4/5/2017 15:57 Qiu Ying
- Bug fixed on entering a string containing ' symbol in title and click on Search will cause error.

7/28/2017 11:12 Qiu Ying
- Enhanced to load pre-list templates

8/10/2017 09:51 AM Qiu Ying
- Bug fixed on input field value shown in abnormal way when contain special characters

2017-08-23 11:00 AM Qiu Ying
- Enhanced to add auto count as prelist template

2017-09-08 11:17 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character
- Bug fixed on the saved format title name not shown after search

11/2/2017 5:24 PM Andy
- Revert to hide Row Format "Master No Repeat".
- Inactive Auto Count Preset Cash Sales Format.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('CUSTOM_ACC_EXPORT_SETUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CUSTOM_ACC_EXPORT_SETUP', BRANCH_CODE), "/index.php");
require_once('custom.custom_acc_export.include.php');

class SETUP_CUSTOM_ACC_EXPORT extends Module{
	function __construct($title)
	{
		global $con, $sessioninfo, $smarty, $file_format_list;
		
		$smarty->assign("file_format_list", $file_format_list);
		
		parent::__construct($title);
	}

	function _default()
	{
		$this->load_format();
		$this->display();
	}
	
	function load_format($search = false){
		global $con, $sessioninfo, $smarty, $data_type_option;
		$filter = array();
		if ($search){
			if ($search['title'] != ''){
				$filter[] = " caef.title like " . ms("%" . replace_special_char($search['title']) . "%");
			}
			
			if ($search['status'] != 'all'){
				$filter[] = " caef.active = " . mi($search['status']);
			}
		}
		
		if ($sessioninfo["branch_id"] != 1){
			$filter[] = "  (caef.branch_id = 1 or caef.branch_id = " . ms($sessioninfo["branch_id"]) . ")";
		}
		
		if ($filter){
			$filter_str = " where " . implode(" and ", $filter);
		}
		
		$branch_id = $sessioninfo['branch_id'];
		$con->sql_query($a = "select caef.*, b.code from custom_acc_export_format caef
			left join branch b on caef.branch_id = b.id
			$filter_str
			order by caef.last_update desc");
		$smarty->assign("form", $_REQUEST);
		$smarty->assign("format_list", $con->sql_fetchrowset());
		$smarty->assign("data_type_option", $data_type_option);
		$con->sql_freeresult();
	}
	
	function view(){
		$this->open(true);
	}
	
	function open($view_only=false, $load_templates = false){
		global $con, $smarty, $data_field, $data_type_option, $data_type_list,$sessioninfo,$config;
		
		if (!$view_only && (BRANCH_CODE == "HQ" || $config['single_server_mode'])){
			insert_prelist_templates();
		}
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		
		if($load_templates && !$view_only){
			$tid = $_REQUEST["template_list"];
			$form = $this->load_format_detail($tid, 0, $load_templates);
			$smarty->assign("data_type_option", $data_type_option);
			$smarty->assign("data_type_list", $data_type_list[$form["row_format"]]);
			$form["title"] = $_REQUEST["title"];
			$form["id"] = $_REQUEST["id"];
			$form["code"] = BRANCH_CODE;
			$form["branch_id"] = $_REQUEST["branch_id"];
		}else{
			if($id > 0){
				$form = $this->load_format_detail($id, $branch_id);
				
				if($form &&  $branch_id != $sessioninfo['branch_id'] || $view_only){
					$smarty->assign("view_only", 1);
				}
				
				$smarty->assign("data_type_list", $data_type_list[$form["row_format"]]);
			}else{
				$form = array();
			}
		}
		
		$template_list = array();
		$temp = array();
		if($config['consignment_modules']){
			foreach($data_type_list as $key => $item) {
				foreach($item as $items) {
					if(!in_array("data_type = '" . $items . "'", $temp))
						$temp[] = "data_type = '" . $items . "'";
				}
			}
		}
		
		if($temp)	$filter = " and " . implode(" or ", $temp);
		$con->sql_query("select id as 'tid', title as 't_title' from custom_acc_export_templates
			where active = 1 $filter order by title");
		while ($r = $con->sql_fetchassoc()){
			$template_list[] = $r;
		}
		$smarty->assign("templates_list", $template_list);
		$con->sql_freeresult();
		
		$con->sql_query("select * from branch where active = 1");
		$smarty->assign("branches", $con->sql_fetchrowset());
		$con->sql_freeresult();
		
		$smarty->assign("form", $form);
		
		$smarty->assign("data_field", $data_field);
		$smarty->assign("data_type_option", $data_type_option);
		$this->display('custom.setup_acc_export.open.tpl');
	}
	
	function load_format_detail($format_id, $branch_id, $load_templates=false){
		global $con;
	
		$format_id = mi($format_id);
		if(!$format_id)	return false;
		
		if($load_templates){
			$con->sql_query("select id as 'tid',data_type,file_format,delimiter,row_format,date_format,
				time_format,header_column,data_column
				from custom_acc_export_templates where id = $format_id");
			$form = $con->sql_fetchassoc();
			$con->sql_freeresult();
		}else{
			$con->sql_query("select caef.*, b.code from custom_acc_export_format caef
				left join branch b on caef.branch_id = b.id
				where caef.id = $format_id and caef.branch_id = $branch_id");
			$form = $con->sql_fetchassoc();
			$con->sql_freeresult();
		}
		
		if(!$form)	return false;
		
		$form['header_column'] = unserialize($form['header_column']);
		$form['data_column'] = unserialize($form['data_column']);
		return $form;
	}
	
	function search(){
		$form = $_REQUEST;
		$this->load_format($form);
		$this->display();
	}
	
	function activate(){
		global $con,$sessioninfo;
		$form = $_REQUEST;
		$upd = array();
		$upd["active"] = mi($form['active_value']);
		$upd["last_update"] = "CURRENT_TIMESTAMP";
		
		$con->sql_query("update custom_acc_export_format set " . mysql_update_by_field($upd) . " where id = ". mi($_REQUEST['id']) . " and branch_id = " . mi($_REQUEST['branch_id']));
		$this->load_format($form);
		$this->display();
		
		if($upd["active"]){
			$str = "Activated";
		}else{
			$str = "Deactivate";
		}
		log_br($sessioninfo['id'], 'CUSTOM ACC EXPORT', $_REQUEST['id'], $str . " Custom Accounting Export Format (Branch ID: " . $_REQUEST['branch_id'] . ", Format ID: ". $_REQUEST['id'] . ")");
	}
	
	function ajax_save(){
		global $sessioninfo, $con;
		$form = $_REQUEST;
		
		$format_id = $form["id"];
		
		// validation
		$branch_id = $form["branch_id"];
		
		if(!$form['title']){
			die('Please Key In Title');
		}else{
			if(strlen($form['title']) > 100){
				die('Title Cannot Exceed 100 Characters');
			}
		}
		
		if(!$form['data_type'])	die('Please Select A Data Type');
		if(!$form['file_format'])	die('Please Select A File Format');
		if ($form["is_other"] == true){
			if (!$form["other_delimiter"]){
				die('Please Key In Other Delimiter');
			}
		}else{
			if(!$form['delimiter']){
				die('Please Select A Delimiter');
			}
		}
		if(!$form['row_format'])	die('Please Select A Row Format');
		if(!$form['date_format'])	die('Please Select A Date Format');
		if(!$form['report_fields'])	die('Please put some data into report table');
		
		$upd = array();
		$upd["title"] = $form["title"]; 
		$upd["data_type"] = $form["data_type"]; 
		$upd["file_format"] = $form["file_format"]; 
		
		if ($form["is_other"] == true){
			$delimiter = $form["other_delimiter"];
		}else{
			$delimiter = $form["delimiter"];
		}

		$upd["delimiter"] = $delimiter; 
		$upd["row_format"] = $form["row_format"]; 
		$upd["date_format"] = $form["date_format"]; 
		$upd["time_format"] = $form["time_format"]; 
		
		if ($form["header"]){
			$upd["header_column"] = serialize($form["header"]); 
		}else{
			$upd["header_column"] = "";
		}
		
		$report_fields_master =$form["report_fields"];
		if((isset($report_fields_master)) && $report_fields_master){
			foreach($report_fields_master as $index => $val){
				foreach($report_fields_master[$index] as $key => $item){
					if($item["field_label_type"] == "view"){
						unset($report_fields_master[$index][$key]["field_desc"]);
						unset($report_fields_master[$index][$key]["field_value"]);
						unset($report_fields_master[$index][$key]["field_cancel"]);
						unset($report_fields_master[$index][$key]["field_active"]);
					}elseif($item["field_label_type"] == "open_field"){
						unset($report_fields_master[$index][$key]["field_cancel"]);
						unset($report_fields_master[$index][$key]["field_active"]);
					}elseif($item["field_label_type"] == "canceled" || $item["field_label_type"] == "transferable"){
						unset($report_fields_master[$index][$key]["field_desc"]);
						unset($report_fields_master[$index][$key]["field_value"]);
					}elseif($item["field_label_type"] == "seq_num" || $item["field_label_type"] == "inv_seq_num"){
						unset($report_fields_master[$index][$key]["field_cancel"]);
						unset($report_fields_master[$index][$key]["field_desc"]);
						unset($report_fields_master[$index][$key]["field_active"]);
					}
				}
			}
		}
		$upd["data_column"] = serialize($report_fields_master);
		$upd["last_update"] = "CURRENT_TIMESTAMP";
		if ($format_id){
			//Edit
			$con->sql_query("update custom_acc_export_format set " . mysql_update_by_field($upd) . " where id = $format_id and branch_id = $branch_id");
			log_br($sessioninfo['id'], 'CUSTOM ACC EXPORT', $format_id, "Edited Setup Custom Accounting Format (Branch ID: ". $branch_id . ", Format ID: ". $format_id .")");
		}else{
			//Add
			$upd["branch_id"] = $branch_id;
			$upd["added"] = "CURRENT_TIMESTAMP";
			$con->sql_query("insert into custom_acc_export_format " . mysql_insert_by_field($upd));
			$format_id = $con->sql_nextid();
			log_br($sessioninfo['id'], 'CUSTOM ACC EXPORT', $format_id, "Saved Setup Custom Accounting Format Title (Branch ID: ". $branch_id . ", Format ID: ". $format_id .")");
		}

		header("Location: /custom.setup_acc_export.php?t=save&title_name=" . urlencode($upd["title"]));
		exit;
	}
	
	function change_data_type(){
		global $row_format_field, $smarty, $data_field;
		$form = $_REQUEST;
		$format_id = $form["data_type"];
		$row_format = $form["row_format"];
		$smarty->assign("data_field", $data_field);
		$smarty->assign("row_format_field", $row_format_field[$format_id][$row_format]);
		$smarty->display('custom.setup_acc_export.drag_field.tpl');
	}
	
	function load_data_type(){
		global $smarty, $data_type_list, $data_type_option;
		$form = $_REQUEST;
		$row_format = $form["row_format"];
		$smarty->assign("data_type_option", $data_type_option);
		$smarty->assign("data_type_list", $data_type_list[$row_format]);
		$smarty->display('custom.setup_acc_export.data_type.tpl');
	}
	
	function preview(){
		global $smarty, $data_field;
		
		$form = $_REQUEST;
		$sample_data_master = array();
		$sample_data_detail = array();
		$report_fields_master =$form["report_fields"]["master"];
		$report_fields_detail =$form["report_fields"]["detail"];
		
		if($form["row_format"] == "no_repeat_master"){
			$report_fields_master = array_merge($form["report_fields"]["master"], $form["report_fields"]["detail"]);
			foreach($form["report_fields"]["master"] as $key => $item){
				unset($form["report_fields"]["master"][$key]);
				$tmp_detail[$key]["field_type"] = "";
			}
			$report_fields_detail = array_merge($tmp_detail, $form["report_fields"]["detail"]);
		}
		
		if(isset($report_fields_master) && $report_fields_master){
			foreach($report_fields_master as $index => $val){
				if($val["field_type"] == "open_field"){
					$sample_data_master[] = $val["field_value"];
				}elseif($val["field_type"] == "date"){
					$sample_data_master[] = $this->set_date($form["date_format"] . " " . $form["time_format"],date("Y-m-d H:i:s"));
				}elseif($val["field_type"] == "seq_num" || $val["field_type"] == "inv_seq_num"){
					$sample_data_master[] = sprintf("%0" . $val["field_value"] . "d",$data_field[$val["field_type"]]["default_value"]);
				}else{
					$sample_data_master[] = $data_field[$val["field_type"]]["default_value"];
				}
			}
		}
		
		if(isset($report_fields_detail) && $report_fields_detail){
			foreach($report_fields_detail as $index => $val){
				if($val["field_type"] == "open_field"){
					$sample_data_detail[] = $val["field_value"];
				}elseif($val["field_type"] == "date"){
					$sample_data_detail[] = $this->set_date($form["date_format"] . " " . $form["time_format"],date("Y-m-d H:i:s"));
				}elseif($val["field_type"] == "seq_num" || $val["field_type"] == "inv_seq_num"){
					$sample_data_detail[] = sprintf("%0" . $val["field_value"] . "d",$data_field[$val["field_type"]]["default_value"]);
				}else{
					$sample_data_detail[] = $data_field[$val["field_type"]]["default_value"];
				}
			}
		}

		$smarty->assign("header", $form["header"]);
		$smarty->assign("master", $sample_data_master);
		$smarty->assign("detail", $sample_data_detail);
		$smarty->display('custom.setup_acc_export.preview.tpl');
	}
	
	private function set_date($date_format,$date){
		return date($date_format,strtotime($date));
	}
	
	function ajax_load_templates(){
		$this->open(false, true);
	}
}
$SETUP_CUSTOM_ACC_EXPORT = new SETUP_CUSTOM_ACC_EXPORT('Setup Custom Accounting Export');
