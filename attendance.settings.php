<?php
/*
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ATTENDANCE_TIME_SETTING')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ATTENDANCE_TIME_SETTING', BRANCH_CODE), "/index.php");
class ATTENDANCE_SETTINGS extends Module{
	//system setting name list
	var $setting_list = array("in_early", "in_late", "out_early", "out_late");
	
	function __construct($title){
		parent::__construct($title);
	}
	
	private function init_load(){
		global $smarty, $con;
		
		//load setting value of time attendance 
		$system_settings = array();
		foreach($this->setting_list as $setting_name){
			$q1 = $con->sql_query("select setting_value from system_settings where setting_name=".ms($setting_name));
			$r = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			$system_settings[$setting_name] = $r['setting_value'];
		}
		$smarty->assign("system_settings",$system_settings);
	}
	
	function _default(){
		$this->init_load();
		$this->display();
	}
	
	function update_setting(){
		global $con, $smarty;
		$form = $_REQUEST;
		
		$err = $this->data_validate();
		if(!$err){
			$upd = array();
			foreach($this->setting_list as $setting_name){
				$upd['setting_name'] = $setting_name;
				$upd['setting_value'] = mi($form[$setting_name]);
				$upd['last_update'] = "CURRENT_TIMESTAMP";
				$con->sql_query("replace into system_settings".mysql_insert_by_field($upd));
			}
			header("Location: ".$_SERVER['PHP_SELF']."?updated=1");
		}else{
			$smarty->assign('err', $err);
			$this->display();
		}
	}
	
	//Check data valid
	function data_validate(){
		$err = array();
		$setting_name_list = array("in_early"=>"Early In", "in_late"=>"Late In", "out_early"=>"Early Exit", "out_late"=>"Late Exit");
		$form = $_REQUEST;
		
		foreach($this->setting_list as $setting_name){
			if($form[$setting_name] == '' || $form[$setting_name] < 0){
				$err[] = "Invalid ".$setting_name_list[$setting_name];
			}
		}
		return $err;
	}
}
$ATTENDANCE_SETTINGS = new ATTENDANCE_SETTINGS('Time Attendance Settings');
?>