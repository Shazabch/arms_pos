<?php

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('CUSTOM_ACC_AND_GST_SETTING')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CUSTOM_ACC_AND_GST_SETTING', BRANCH_CODE), "/index.php");
require_once('custom.custom_acc_export.include.php');

class CUSTOM_ACC_AND_GST_SETTING extends Module{
	
	function __construct($title)
	{
		$this->load();
		parent::__construct($title);
	}

	function _default()
	{
		$this->display('custom.acc_and_gst_setting.tpl');
	}
	
	function load(){
		global $con, $smarty, $sessioninfo, $config, $account_list;
		
		$con->sql_query("select * from gst where active = 1 order by code");
		$smarty->assign("gst_list", $con->sql_fetchrowset());
		$con->sql_freeresult();
		
		$smarty->assign("acc_list", $account_list);
		
		if(BRANCH_CODE=='HQ'){
			$branch_id = 1;
			$can_edit = 1;
			$use_own_branch = 0;
		}
		else{
			$branch_id = $sessioninfo['branch_id'];
			$con->sql_query("select use_own_branch from custom_acc_export_acc_setting where branch_id = " . mi($branch_id));
			if ($con->sql_fetchfield('use_own_branch')){
				$branch_id = $branch_id;
				$can_edit = 1;
				$use_own_branch = 1;
			}
			else{
				$can_edit = 0;
				$branch_id = 1;
				$use_own_branch = 0;
			}
		}
		$con->sql_query("select * from custom_acc_export_acc_setting where branch_id = " . mi($branch_id));
		while($r = $con->sql_fetchassoc()){
			$acc_setting[$r["code"]] = $r;
		}
		$con->sql_freeresult();
		
		$con->sql_query("select * from custom_acc_export_gst_setting where branch_id = " . mi($branch_id));	
		while($r = $con->sql_fetchassoc()){
			$gst_setting[$r["gst_id"]] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign("use_own_branch", $use_own_branch);
		$smarty->assign("acc_setting", $acc_setting);
		$smarty->assign("gst_setting", $gst_setting);
		$smarty->assign("can_edit", $can_edit);
	}
	
	function save()
	{
		global $con, $smarty, $sessioninfo, $config, $account_list;
		
		$form = $_REQUEST;
		if(BRANCH_CODE=='HQ') $branch_id=1;
		else $branch_id=$sessioninfo['branch_id'];
		
		$con->sql_query("delete from custom_acc_export_acc_setting where branch_id = " . mi($branch_id));
		$con->sql_query("delete from custom_acc_export_gst_setting where branch_id = " . mi($branch_id));
		
		if (BRANCH_CODE=='HQ' || $form["use_own_branch"]){
			$upd = array();
			if(isset($form["data"]["acc"]) && $form["data"]["acc"]){
				foreach($form["data"]["acc"] as $acc_key => $acc_item){
					if ($form["data"]["acc"][$acc_key]["account_code"] == '')	continue;
					$upd["code"]= $acc_key;
					$upd["account_code"]= $form["data"]["acc"][$acc_key]["account_code"];
					$upd["account_name"]= $form["data"]["acc"][$acc_key]["account_name"];
					$upd["branch_id"] = $branch_id;
					$upd["last_update"] = "CURRENT_TIMESTAMP";
					$upd["use_own_branch"] =(BRANCH_CODE=='HQ')?1:$form["use_own_branch"];
					$con->sql_query("replace into custom_acc_export_acc_setting ".mysql_insert_by_field($upd));
					unset($upd);
				}
			}
			
			if(isset($form["data"]["gst"]) && $form["data"]["gst"]){
				foreach($form["data"]["gst"] as $gst_key => $gst_item){
					if ($form["data"]["gst"][$gst_key]["account_code"] == '')	continue;
					$upd["gst_id"]= $gst_key;
					$upd["account_code"]= $form["data"]["gst"][$gst_key]["account_code"];
					$upd["account_name"]= $form["data"]["gst"][$gst_key]["account_name"];
					$upd["branch_id"] = $branch_id;
					$upd["last_update"] = "CURRENT_TIMESTAMP";
					$upd["use_own_branch"] =(BRANCH_CODE=='HQ')?1:$form["use_own_branch"];
					$con->sql_query("replace into custom_acc_export_gst_setting ".mysql_insert_by_field($upd));
					unset($upd);
				}
			}
			log_br($sessioninfo['id'], 'CUSTOM ACC EXPORT', 0, "Saved Custom Account & GST Setting");
			unset($acc_key, $gst_key, $acc_item, $gst_item);
		}
		
		$this->load();
		$smarty->assign("status","Saved Successfully");
		$this->display('custom.acc_and_gst_setting.tpl');
	}
}
$CUSTOM_ACC_AND_GST_SETTING = new CUSTOM_ACC_AND_GST_SETTING('Custom Account and GST Setting');