<?php
/*
5/5/2015 12:26 PM Andy
- Enhanced to able to accept filename with uppercase extension.

3/13/2017 11:50 AM Justin
- Enhanced upload branch logo to use the "resize_photo" function.
- Enhanced system can only accepts JPG/JPEG for logo upload.
- Enhanced to have validation on logo size of 5mb max only.

6/18/2019 10:22 AM William
- Added new update vertical setting for logo company.
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class Upload_Logo extends Module
{
	
	function __construct($title)
	{
 		parent::__construct($title);
	}
	
	function _default()
	{
		$this->init_load();
		$this->display();
	}
	
	private function init_load(){
		global $smarty,$con;
		
		// Set Setting List
		$setting_list = array('logo_vertical', 'verticle_logo_no_company_name');
		
		// Loop Setting List
		foreach($setting_list as $setting_name){
			$q1 = $con->sql_query("select setting_value from system_settings where setting_name=".ms($setting_name));
			$r = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			$system_settings[$setting_name] = $r['setting_value'];
		}
		$smarty->assign("system_settings",$system_settings);
	}
	
	//Update system_settings Edit Logo Settings.
	function update_logosetting(){
		global $smarty,$con;
		$form = $_REQUEST;
		
		// Vertical Logo
		$upd = array();
		$upd['setting_name'] = "logo_vertical";
		$upd['setting_value'] = mi($form["is_vertical_logo"]);
		$upd['last_update'] = "CURRENT_TIMESTAMP";
		$con->sql_query("replace into system_settings".mysql_insert_by_field($upd));
		
		// Vertical Logo No Company Name
		$upd = array();
		$upd['setting_name'] = "verticle_logo_no_company_name";
		$upd['setting_value'] = mi($form["verticle_logo_no_company_name"]);
		$upd['last_update'] = "CURRENT_TIMESTAMP";
		$con->sql_query("replace into system_settings".mysql_insert_by_field($upd));
					
		header("Location: ".$_SERVER['PHP_SELF']."?updated=1");
		
	}
	
	
	function upload()
	{
		global $smarty;
		
		$err = $this->data_validate();
		
		if(!$err){
			$file = $_FILES['logo'];
			
			//save as file
			$smarty->config_load('site.conf');
			$f = ltrim($smarty->get_config_vars('LOGO_IMAGE'), '/');

			$tmp_img_location = "/tmp/logo.png";
			$img_location = $f;
			copy($file['tmp_name'], $tmp_img_location);
			if(file_exists($tmp_img_location)) resize_photo($tmp_img_location, $img_location);
			
			//$smarty->assign('success', $success);
			$this->init_load();
			$this->display();
		}else{
			$smarty->assign('err', $err);
			$this->init_load();
			$this->display();
		}
	}
	
	function data_validate(){
		global $smarty, $config, $LANG;
		
		$file = $_FILES['logo'];
		$errm = array();

		if(!is_readable($file['tmp_name'])){ // check file existence
			$errm[] = $LANG['ADMIN_LOGO_EMPTY'];
		}elseif($file['tmp_name']){ // check file extension
			$valids = array('jpg','jpeg');
			$ext = pathinfo($file['name'],PATHINFO_EXTENSION);
			if (!in_array($ext,$valids)) $errm[] = $LANG['ADMIN_LOGO_INV_FORMAT'];
		}

		if($file['size'] > 5242880){ // check logo file size (5mb)
			$errm[] = $LANG['ADMIN_LOGO_SIZE_EXCEEDED'];
		}
		
		$smarty->config_load('site.conf');
		$f = ltrim($smarty->get_config_vars('LOGO_IMAGE'), '/');

		if(is_writable($f) || chmod($f, 0777)){
			// do nothing
		}else{ // the file is not writable or unable to change mode
			$errm[] = $LANG['ADMIN_LOGO_UNWRITABLE'];
		}
		
		return $errm;
	}
}

$Upload_Logo = new Upload_Logo('Edit Logo Settings');

?>
