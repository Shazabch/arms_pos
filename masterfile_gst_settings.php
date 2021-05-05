<?php
/*
9/12/2014 5:20 PM Justin
- Enhanced to update status of GST settings when check/uncheck GST registration checkbox.

9/19/2014 10:39 AM Justin
- Enhanced to show GST list that type is equal to "SUPPLY" only.

9/24/2014 3:08 PM Justin
- Enhanced to use different ways to store GST Settings.

9/26/2014 10:17 AM Justin
- Enhanced to have "Selling Price Rounding Condition".

10/14/2014 10:23 AM Justin
- Bug fixed on settings that is using checkbox will not save when uncheck it.

10/16/2014 2:06 PM Justin
- Enhanced to checked by default for "Discount After GST Selling Price" for first time save the form.

10/23/2014 4:32PM dingren
- remove deposit_type checking from validate

10/29/2014 05:55PM dingren
- hide Receipt Prefix No

11/6/2014 4:44PM Ding Ren
- Remove Goods ReturnReason Settings

2/4/2015 4:10 PM Andy
- Add Export GST Type. (for consignment mode only)

2/16/2015 3:59 PM Andy
- Enhance to auto regenerate category cache when user change gst setting "Inclusive Tax".

3/16/2015 5:46 PM Justin
- Enhanced to pickup default value for "Service Charge (SR)", "Deposit GST Type (SR)" and "Special Exemption GST Type (ES)" when found user first time enter this module.

3/19/2015 10:09 AM Andy
- Add checking user level 9999 to allow access GST Setting.

3/31/2015 5:50 PM Andy
- Enhanced to auto select the default "Export GST Type" and "Designated Areas GST Type" if found not set.

07/09/2015 5:52PM dingren
- add global input tax and global output tax

07/20/2015 2:35 PM dingren
- Enhance to auto regenerate category cache when user change gst setting "Global Input Tax".
- Enhance to auto regenerate category cache when user change gst setting "Global Output Tax".

9/15/2015 3:20 PM Andy
- Fix no default values for special exemption and tax invoice remark.

9/15/2015 4:20 PM Andy
- Change to only add default value when it is HQ.

10/30/2015 1:09 PM DingRen
- GST Settings if already login to branch, should cannot save

9/13/2016 4:20 PM Andy
- Enhanced all gst type default should be empty and user must select it then only can save.
- Enhanced the validation to block user to save if found got gst type is not selected.

11/30/2016 4:20 PM Andy
- Enhanced to always have default tax invoice remark value. (Name, Address, BRN, GST Reg No)

10/31/2017 11:38 AM Justin
- Enhanced to have Special Exemption Relief Claus Remark.
*/
include("include/common.php");
if (!$login || $sessioninfo['level']<9999) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
$maintenance->check(245);

class MASTERFILE_GST extends Module{
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;

 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty, $sessioninfo, $config, $appCore;
		
		$this->init_selection();

		// load default info
		$q1 = $con->sql_query("select *
							   from gst_settings");
		while($r = $con->sql_fetchassoc($q1)){
			$form[$r['setting_name']] = $r['setting_value'];
		}
		$con->sql_freeresult($q1);

		if($form['exemption_remark_field']) $form['exemption_remark_field'] = unserialize($form['exemption_remark_field']);
		if($form['tax_invoice_remark']) $form['tax_invoice_remark'] = unserialize($form['tax_invoice_remark']);
			
		if(BRANCH_CODE == 'HQ'){
			
			//if($form['grr_settings']) $form['grr_settings'] = unserialize($form['grr_settings']);
			
			// found it is first time that user access this module
			if(!$form){
				// get GST code "SR"
				/*$q1 = $con->sql_query("select * from gst where active=1 and type='supply' and code='SR' order by code");
				$gst_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				if(!$form['service_charge_type']) $form['service_charge_type'] = $gst_info['id'];
				if(!$form['deposit_type']) $form['deposit_type'] = $gst_info['id'];
				
				// get GST code "ES"
				$q1 = $con->sql_query("select * from gst where active=1 and type='supply' and code='ES' order by code");
				$gst_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);

				if(!$form['special_exemption_type']) $form['special_exemption_type'] = $gst_info['id'];*/
				
				$form['exemption_remark_field']['title'] = array("Name","Address","BRN");
				$form['tax_invoice_remark']['title'] = array("Name","Address","BRN");
			}else{
				// not first time
				if(!$form['exemption_remark_field']){
					$form['exemption_remark_field']['title'] = array("Name","Address","BRN");
					
					$upd = array();
					$upd['setting_name'] = 'exemption_remark_field';
					$upd['setting_value'] = serialize($form['exemption_remark_field']);
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("replace into gst_settings ".mysql_insert_by_field($upd));
				}
				
				if(!$form['tax_invoice_remark']){
					$form['tax_invoice_remark']['title'] = $appCore->gstManager->taxInvoiceDefaultRemarkList;
					
					$upd = array();
					$upd['setting_name'] = 'tax_invoice_remark';
					$upd['setting_value'] = serialize($form['tax_invoice_remark']);
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("replace into gst_settings ".mysql_insert_by_field($upd));
				}
				
			}
			
			if($config['consignment_modules']){
				// get GST code "ZRE"
				if(!$form['export_gst_type']){
					$q1 = $con->sql_query("select * from gst where active=1 and type='supply' and code='ZRE' order by code limit 1");
					$gst_info = $con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);
					
					$form['export_gst_type'] = mi($gst_info['id']);
				}
				
				// get GST code "ZRL"
				if(!$form['designated_gst_type']){
					$q1 = $con->sql_query("select * from gst where active=1 and type='supply' and code='ZRL' order by code limit 1");
					$gst_info = $con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);
					
					$form['designated_gst_type'] = mi($gst_info['id']);
				}
			}
		}
		
		$form['tax_invoice_remark']['title'] = array_unique(array_merge($appCore->gstManager->taxInvoiceDefaultRemarkList,$form['tax_invoice_remark']['title']));
		
		if(!isset($form['special_exemption_relief_claus_remark']) || !$form['special_exemption_relief_claus_remark']) $form['special_exemption_relief_claus_remark'] = $config['se_relief_claus_remark'];
		$smarty->assign("form", $form);
	    $this->display();
	}
	
	function init_selection(){
		global $con, $smarty, $appCore;
		
		// load gst list
		$q1 = $con->sql_query("select * from gst where active=1 order by code");
		
		while($r = $con->sql_fetchassoc($q1)){
			if($r['type']=='supply') $gst_list[] = $r;
			else $supply_gst_list[] = $r;
			if($r['rate'] == 0 && $r['type']=='supply') $exempted_gst_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("supply_gst_list", $supply_gst_list);
		$smarty->assign("gst_list", $gst_list);
		$smarty->assign("exempted_gst_list", $exempted_gst_list);
		
		$sp_rc_list = array("0.05", "0.09", "0.10");
		$smarty->assign("sp_rc_list", $sp_rc_list);
		
		$smarty->assign('taxInvoiceDefaultRemarkList', $appCore->gstManager->taxInvoiceDefaultRemarkList);
	}
	
	function update(){
		global $con, $smarty, $sessioninfo, $config, $global_gst_settings,$LANG;

		if (BRANCH_CODE!='HQ') js_redirect($LANG['HQ_ONLY'], "/index.php");

		$form=$_REQUEST;
		$ret = array();
		$is_updated = false;
		unset($form['a'], $form['save']);
		
		$err = $this->validate();
		if(count($err) > 0){
			/*$smarty->assign("form", $form);
			$smarty->assign("err", $err);
			$smarty->display();*/
			//$ret['failed_reason'] = "* ".join($err, "\n* ");
			$smarty->assign("err", $err);
			$smarty->assign("form", $form);
			$this->init_selection();
			$this->display();
			exit;
		}
		
		foreach($form['exemption_remark_field']['title'] as $rid=>$pserf_title){
			if(!trim($pserf_title)){
				unset($form['exemption_remark_field']['title'][$rid]);
				continue;
			}
		}
		
		foreach($form['tax_invoice_remark']['title'] as $rid=>$ptirt_title){
			if(!trim($ptirt_title)){
				unset($form['tax_invoice_remark']['title'][$rid]);
				continue;
			}
		}
		
		/*foreach($form['grr_settings']['code'] as $rid=>$grrs_code){
			$grrs_desc = $form['grr_settings']['description'][$rid];
			if(!trim($grrs_code) || !trim($grrs_desc)){
				unset($form['grr_settings']['code'][$rid]);
				unset($form['grr_settings']['description'][$rid]);
				continue;
			}
		}*/
		
		// need to rebuild category cache
		if($global_gst_settings['inclusive_tax'] != $form['inclusive_tax'])	$need_build_cat_cahce = true;
		if($global_gst_settings['global_input_tax'] != $form['global_input_tax'])	$need_build_cat_cahce = true;
		if($global_gst_settings['global_output_tax'] != $form['global_output_tax'])	$need_build_cat_cahce = true;
		
		// truncate table
		$con->sql_query("truncate gst_settings");
	
		foreach($form as $type=>$val){
			if($type == "exemption_remark_field" || $type == "tax_invoice_remark" || $type == "grr_settings") $val = serialize($val);
			$upd = array();
			$upd["setting_name"] = $type;
			$upd["setting_value"] = $val;
			$upd["last_update"] = "CURRENT_TIMESTAMP";
			$q1 = $con->sql_query("replace into gst_settings ".mysql_insert_by_field($upd));
			if($con->sql_affectedrows($q1) > 0){
				$is_updated = true;
			}
		}
		
		if($is_updated) log_br($sessioninfo['id'], 'GST_SETTINGS', 0, "Updated GST Settings");
		
		// need rebuild category cache
		if($need_build_cat_cahce){
			// get new settings
			get_global_gst_settings();
			// rebuild
			build_category_cache();
		}
		
		header("Location: /masterfile_gst_settings.php?save=1");
	}
	
	function validate(){
		global $con, $LANG, $config;
		$form=$_REQUEST;
		$err = array();
		
		//if(!trim($form['receipt_prefix_no'])) $err[] = sprintf($LANG['GST_FIELD_EMPTY'], "Receipt Prefix No");
		//if(!$form['service_charge']) $err[] = sprintf($LANG['GST_FIELD_EMPTY'], "Service Charge (%)");
		if(!$form['global_input_tax']) $err[] = sprintf($LANG['GST_FIELD_EMPTY'], "Global Input Tax");
		if(!$form['global_output_tax']) $err[] = sprintf($LANG['GST_FIELD_EMPTY'], "Global Output Tax");
		if(!$form['service_charge_type']) $err[] = sprintf($LANG['GST_FIELD_EMPTY'], "Service Charge GST Type");
		if(!$form['deposit_type']) $err[] = sprintf($LANG['GST_FIELD_EMPTY'], "Deposit GST Type");
		if(!$form['special_exemption_type']) $err[] = sprintf($LANG['GST_FIELD_EMPTY'], "Special Exemption GST Type");
		
		if($config['consignment_modules']){
			if(!$form['export_gst_type']) $err[] = sprintf($LANG['GST_FIELD_EMPTY'], "Export GST Type");
			if(!$form['designated_gst_type']) $err[] = sprintf($LANG['GST_FIELD_EMPTY'], "Designated Areas GST Type");
		}
		if(!$form['membership_gst_type']) $err[] = sprintf($LANG['GST_FIELD_EMPTY'], "Member Card Service Charge Type");
		return $err;
	}
	
	function ajax_toggle_active(){
		global $con, $sessioninfo;
		
		$upd = array();
		$upd['setting_name'] = "active";
		$upd['setting_value'] =  $_REQUEST['status'];
		$upd['last_update'] =  "CURRENT_TIMESTAMP";
		
		$con->sql_query("replace into gst_settings ".mysql_insert_by_field($upd));
		
		log_br($sessioninfo['id'], "GST_SETTINGS", 0, ($_REQUEST['status'] ? "Activated" : "Deactivated")." GST Settings");
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
}

$MASTERFILE_GST=new MASTERFILE_GST("GST Settings");

?>
