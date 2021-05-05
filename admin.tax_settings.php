<?php
/**/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!$config['enable_tax']) js_redirect(sprintf($LANG['NEED_CONFIG']), "/index.php");
class TAX_SETTINGS extends Module{
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;
		
 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty, $sessioninfo;
		
		$this->init();
	    $this->display();
	}
	
	function init(){
		global $con, $smarty, $sessioninfo;
		
		$q1=$con->sql_query("select * from tax_settings");
		while($r = $con->sql_fetchassoc($q1)){
			$form[$r['setting_name']] = $r['setting_value'];
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("form", $form);
	}
	
	function validate(){
		global $con, $LANG, $config;
		
		$form=$_REQUEST;
		$err = array();
		
		if(!$form['tax_start_date']) $err[] = "Invalid Tax Start Date.";
		
		return $err;
	}
	
	function update(){
		global $con, $smarty, $sessioninfo, $config;
		
		$form=$_REQUEST;
		$is_updated = false;
		unset($form['a'], $form['save']);
		
		$err = $this->validate();
		if(count($err) > 0){
			$smarty->assign("err", $err);
			$smarty->assign("form", $form);
			$this->init();
			$this->display();
			exit;
		}
		
		foreach($form as $setting_name=>$val){
			$upd = array();
			$upd["setting_name"] = $setting_name;
			$upd["setting_value"] = $val;
			$upd["last_update"] = "CURRENT_TIMESTAMP";
			$q1 = $con->sql_query("replace into tax_settings ".mysql_insert_by_field($upd));
			if($con->sql_affectedrows($q1) > 0){
				$is_updated = true;
			}
		}
		
		if($is_updated) log_br($sessioninfo['id'], 'TAX_SETTINGS', 0, "Updated Tax Settings");
		
		header("Location: ".$_SERVER['PHP_SELF']."?save=1");
	}
}

$TAX_SETTINGS=new TAX_SETTINGS("Tax Settings");
?>
