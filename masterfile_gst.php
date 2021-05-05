<?php
/*
9/24/2014 3:22 PM Justin
- Enhanced the way of checking for GST Settings table.

1/2/2015 5:22 PM Justin
- Enhanced to take off "Purchase Price Include GST".

1/24/2015 12:24 PM Justin
- Enhanced to add new option "Special Code for Vendor".

8/28/2015 5:13 PM Andy
- Rename "Masterfile GST" to "Masterfile GST Tax Code".

7/26/2017 15:20 Qiu Ying
- Enhanced to add second tax code
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
$maintenance->check(241);

class MASTERFILE_GST extends Module{
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;
		/*$con->sql_query("select id, description from category where level=2 and active=1 order by description");
		$smarty->assign("dept", $con->sql_fetchrowset());*/

 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty, $sessioninfo;
		
		$tax_type_list = array("purchase"=>"PURCHASE", "supply"=>"SUPPLY");
		$vd_gst_settings_list = array("Required"=>"Required", "Disabled"=>"Disabled", "Optional"=>"Optional");
		
		$q1 = $con->sql_query("select *
							   from gst
							   order by id asc");
		
		$current_time = strtotime(date("Y-m-d H:i:s"));
		$gst_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$gst_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("gst_list", $gst_list);
		$smarty->assign("tax_type_list", $tax_type_list);
		$smarty->assign("vd_gst_settings_list", $vd_gst_settings_list);

	    $this->display();
	    exit;
	}
	
	function ajax_add(){
		global $con, $smarty, $sessioninfo, $config;

		$form=$_REQUEST;
		
		$err = $this->validate();
		if(count($err) > 0){
			print $err;
			exit;
		}

		$ins = array();
		$ins['code'] = strtoupper(trim($form['code']));
		$ins['second_tax_code'] = strtoupper(trim($form['second_tax_code']));
		$ins['description'] = trim($form['description']);
		$ins['type'] = $form['type'];
		$ins['rate'] = mf($form['rate']);
		if($form['type'] == "purchase"){
			//$ins['inc_item_cost'] = $form['inc_item_cost'];
			//$ins['vendor_gst_setting'] = $form['vendor_gst_setting'];
			$ins['indicator_receipt'] = "";
		}else{
			//$ins['inc_item_cost'] = "";
			//$ins['vendor_gst_setting'] = "";
			$ins['indicator_receipt'] = $form['indicator_receipt'];
		}
		$ins['user_id'] = $sessioninfo['id'];
		$ins['last_update'] = $ins['added'] = "CURRENT_TIMESTAMP";
		$ins['active'] = 1;
		
		$con->sql_query("insert into gst ".mysql_insert_by_field($ins));
		$gst_id = $con->sql_nextid();

		log_br($sessioninfo['id'], 'MST_GST', $gst_id, "Added Masterfile GST: ID#".$gst_id);
	}

	function edit(){
		global $con, $smarty, $LANG, $config;

		$form = $_REQUEST;
		$ret = array();
		
		if(!$form['gst_id']) die("Invalid GST ID");
		
		$q1 = $con->sql_query("select * from gst where id = ".mi($form['gst_id']));
		if($con->sql_numrows($q1) > 0){
			$ret['gst_info'] = $con->sql_fetchassoc($q1);
			$ret['ok'] = 1;
		}else{
			$ret['failed_msg'] = "Cannot found GST record.";
		}
		print json_encode($ret);
	}
	
	function ajax_update(){
		global $con, $smarty, $sessioninfo, $config;
		
		$form=$_REQUEST;
		
		$err = $this->validate();
		if(count($err) > 0){
			print $err;
			exit;
		}

		$upd = array();
		$upd['code'] = strtoupper(trim($form['code']));
		$upd['second_tax_code'] = strtoupper(trim($form['second_tax_code']));
		$upd['description'] = trim($form['description']);
		$upd['type'] = $form['type'];
		$upd['rate'] = mf($form['rate']);
		$upd['rate'] = mf($form['rate']);
		if($form['type'] == "purchase"){
			//$upd['inc_item_cost'] = $form['inc_item_cost'];
			//$upd['vendor_gst_setting'] = $form['vendor_gst_setting'];
			$upd['indicator_receipt'] = "";
			$upd['is_vd_special_code'] = $form['is_vd_special_code'];
		}else{
			//$upd['inc_item_cost'] = "";
			//$upd['vendor_gst_setting'] = "";
			$upd['indicator_receipt'] = $form['indicator_receipt'];
			$upd['is_vd_special_code'] = 0;
		}
		$upd['last_update'] = "CURRENT_TIMESTAMP";
		
		$con->sql_query("update gst set ".mysql_update_by_field($upd)." where id = ".mi($form['id']));
		log_br($sessioninfo['id'], 'MST_GST', $form['id'], "Updated Masterfile GST: ID#".$form['id']);
	}

	function activation(){
		global $con, $smarty, $LANG, $sessioninfo;
		
		$form=$_REQUEST;
		$err = array();
		
		if(!$form['gst_id']) die("No such record!");
		
		if(!$form['value']){ // found user trying to deactivate the GST
			// check if this gst being used on masterfile category, gst settings, sku, vendor or membership
			
			// Category
			$q1 = $con->sql_query("select * from category where (input_tax = ".mi($form['gst_id'])." or output_tax = ".mi($form['gst_id']).")");
			// found it is being used by Category
			if($con->sql_numrows($q1) > 0){
				$err[] = sprintf($LANG['GST_DEACTIVATE_ERROR'], "Category");
			}
			$con->sql_freeresult($q1);
			
			if(!$err){
				// GST settings
				$q1 = $con->sql_query("select * from gst_settings where setting_name in ('service_charge_type','deposit_type','special_exemption_type') and setting_value = ".mi($form['gst_id']));
				
				// found it is being used by GST settings
				if($con->sql_numrows($q1) > 0){
					$err[] = sprintf($LANG['GST_DEACTIVATE_ERROR'], "Masterfile GST Settings");
				}
				$con->sql_freeresult($q1);
			}
			
			if(!$err){
				// SKU
				$q1 = $con->sql_query("select * from sku where (mst_input_tax = ".mi($form['gst_id'])." or mst_output_tax = ".mi($form['gst_id']).")");
				// found it is being used by Masterfile SKU
				if($con->sql_numrows($q1) > 0){
					$err[] = sprintf($LANG['GST_DEACTIVATE_ERROR'], "Masterfile SKU");
				}
				$con->sql_freeresult($q1);
				
				// SKU items
				$q1 = $con->sql_query("select * from sku_items where (input_tax = ".mi($form['gst_id'])." or output_tax = ".mi($form['gst_id']).")");
				// found it is being used by Masterfile SKU
				if($con->sql_numrows($q1) > 0){
					$err[] = sprintf($LANG['GST_DEACTIVATE_ERROR'], "Masterfile SKU");
				}
			}
			
			if(!$err){
				// SKU Application (check for those items under approval flow)
				$q1 = $con->sql_query("select * from sku_apply_items where is_new = 1 and (input_tax = ".mi($form['gst_id'])." or output_tax = ".mi($form['gst_id']).")");
				// found it is being used by SKU Application
				if($con->sql_numrows($q1) > 0){
					$err[] = sprintf($LANG['GST_DEACTIVATE_ERROR'], "SKU Application");
				}
				
				// Masterfile Vendor
				$q1 = $con->sql_query("select * from vendor where active=1 and gst_type = ".mi($form['gst_id']));
				
				// found it is being used by Masterfile Vendor
				if($con->sql_numrows($q1) > 0){
					$err[] = sprintf($LANG['GST_DEACTIVATE_ERROR'], "Masterfile Vendor");
				}
				$con->sql_freeresult($q1);
			}
			
			// membership
			if(!$err){
				$q1 = $con->sql_query("select * from membership where gst_type = ".mi($form['gst_id']));
				
				// found it is being used by Membership
				if($con->sql_numrows($q1) > 0){
					$err[] = sprintf($LANG['GST_DEACTIVATE_ERROR'], "Membership");
				}
				$con->sql_freeresult($q1);
			}
			
			// check if this is the last active gst for type "purchase" or "supply"
			$q1 = $con->sql_query("select * from gst where id = ".mi($form['gst_id']));
			$gst_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			$q1 = $con->sql_query("select * from gst where id != ".mi($form['gst_id'])." and active = 1 and type = ".ms($gst_info['type']));
			
			if($con->sql_numrows($q1) == 0) $err[] = sprintf($LANG['GST_DEACTIVATE_REACH_LIMIT'], strtoupper($gst_info['type']));
		}
		
		if($err){
			$err_msg = "You have encountered below errors:\n\n";
			foreach($err as $msg){
				$err_msg .= "* ".$msg."\n";
			}
			
			$ret['failed_msg'] = $err_msg;
		}else{
			$con->sql_query("update gst set active = ".mi($form['value']).", last_update = CURRENT_TIMESTAMP where id = ".mi($form['gst_id']));
			if($form['value'] == 1) $msg = "Activated";
			else $msg = "Deactivated";
			log_br($sessioninfo['id'], 'MST_GST', $form['gst_id'], "$msg for Masterfile GST: ID#".$form['gst_id']);
			
			$ret['ok'] = 1;
		}
		
		print json_encode($ret);
	}
	
	function validate(){
		global $con, $LANG, $config;
		$form=$_REQUEST;
		$err = array();
		
		if(!trim($form['code'])) $err[] = sprintf($LANG['GST_FIELD_EMPTY'], "Tax Code");
		else{
			$q1 = $con->sql_query("select * from gst where code = ".ms($form['code'])." and id != ".mi($form['id']));
			if($con->sql_numrows($q1) > 0) $err[] = $LANG['GST_CODE_DUPLICATED'];
			$con->sql_freeresult($q1);
		}
		if(!trim($form['description'])) $err[] = sprintf($LANG['GST_FIELD_EMPTY'], "Description");
		
		if(count($err) > 0){
			$err_msg = "<ul>";
			foreach($err as $row=>$errm){
				$err_msg .= "<li>".$errm."</li>"; 
			}
			$err_msg .= "</ul>";
		}
		
		return $err_msg;
	}
}

$MASTERFILE_GST=new MASTERFILE_GST("Masterfile GST Tax Code");

?>
