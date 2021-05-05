<?php
include("include/common.php");
include("masterfile_voucher.auto_redemption.include.php");
if(!$login && is_ajax())	die($LANG['YOU_HAVE_LOGGED_OUT']);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!$config['enable_voucher_auto_redemption']) js_redirect($LANG['NEED_CONFIG'], "/index.php");
if (!privilege('MST_VOUCHER_AUTO_REDEMP_SETUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VOUCHER_AUTO_REDEMP_SETUP', BRANCH_CODE), "/index.php");

class VOUCHER_AUTO_REDEMPTION_SETUP extends Module{
	var $voucher_value_list = array();
	
	function __construct($title){
		global $con, $smarty, $sessioninfo;
		
		parent::__construct($title);
	}
	
	function _default(){
		global $sessioninfo, $smarty;
	 
		// load voucher prefix list
		$this->voucher_value_list = load_voucher_value_list($sessioninfo['branch_id']);	// HQ list
		//print_r($this->voucher_value_list);
		
		$this->display();
	}
	
	function ajax_save(){
		global $con, $smarty, $sessioninfo, $config;
		
		//print_r($_REQUEST);
		
		$form = $_REQUEST;
		$voucher_list_id_list = $form['voucher_list_id'];
		
		// checking
		foreach($voucher_list_id_list as $key=>$tmp_vl_id){
			list($bid, $vl_id) = explode("-", $tmp_vl_id);
			
			if($bid>0 && $bid != $sessioninfo['branch_id']){
				die('Update failed, found different branch data.');
			}
			
			if($form['allowed'][$key]){
				if($form['points_use'][$key]<0){
					die('Please enter a valid point(s). Cannot negative.');
				}
				if($form['max_qty'][$key]<0){
					die('Please enter a valid limit. Cannot negative.');
				}
				if($form['points_use'][$key]<=0 && $form['max_qty'][$key]<=0){
					die('You cannot put both Points and limit blank.');
				}
			}
		}
		
		foreach($voucher_list_id_list as $key=>$tmp_vl_id){
			list($bid, $vl_id) = explode("-", $tmp_vl_id);
			
			$upd = array();
			$upd['allowed'] = mi($form['allowed'][$key]);
			$upd['voucher_value'] = mf($form['voucher_value'][$key]);
			$upd['points_use'] = mi($form['points_use'][$key]);
			$upd['max_qty'] = mi($form['max_qty'][$key]);
			$upd['user_id'] = $sessioninfo['id'];
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			
			if($vl_id && $bid){
				$con->sql_query("update voucher_auto_redemp_master set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$vl_id");
			}else{
				$upd['branch_id'] = $sessioninfo['branch_id'];
				$con->sql_query("insert into voucher_auto_redemp_master ".mysql_insert_by_field($upd));
			}
		}
		
		print "OK";
	}
	
}

$VOUCHER_AUTO_REDEMPTION_SETUP = new VOUCHER_AUTO_REDEMPTION_SETUP('Voucher Auto Redemption Setup');
?>
