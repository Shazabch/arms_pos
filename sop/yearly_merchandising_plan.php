<?php
include_once('include/common.php');
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('SOP_YMERP') && !privilege('SOP_YMERP_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SOP_YMERP', BRANCH_CODE), "index.php");

include_once('yearly_marketing_plan.include.php');

class YEARLY_MERCHANDISING_PLAN extends Module{
	var $allow_edit = false;
	var $branches = array();
	var $default_merchandising_plan_list_size = 10;

    function __construct($title){
		global $con, $smarty, $sessioninfo;

		if(privilege('SOP_YMERP_EDIT'))	$this->allow_edit = true;
		if(!$_REQUEST['skip_init_load'])    $this->init_load(); // initial load

		$smarty->assign('allow_edit', $this->allow_edit);
		parent::__construct($title);
	}

	function _default(){
	    global $con, $smarty;

		$this->display();
	}
	
	private function init_load(){
		global $con, $smarty;

		// load branches
		$this->branches = array();
		$con->sql_query_false("select * from branch order by sequence", true);
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches', $this->branches);
	}
}

$YEARLY_MERCHANDISING_PLAN = new YEARLY_MERCHANDISING_PLAN('Yearly Merchandising Plan');
?>
