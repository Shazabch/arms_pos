<?php
include_once('include/common.php');
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

include_once('yearly_marketing_plan.include.php');

class YEARLY_MARKETING_PLAN_CALENDAR extends Module{
	var $branches = array();
	
    function __construct($title){
		global $con, $smarty, $sessioninfo;

        if(!$_REQUEST['skip_init_load'])    $this->init_load(); // initial load
        
		parent::__construct($title);
	}

	function _default(){
	    global $con, $smarty;

        global $con, $smarty;

		$this->update_title($this->title);    // update title
		$this->load_all_marketing_plan_list(); // load all marketing plan

		if($_REQUEST['show']){
			generate_calendar($_REQUEST['marketing_plan_id'], $_REQUEST['branch_id'], $_REQUEST['show_festival']);
		}
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
	

	private function load_all_marketing_plan_list(){
	    global $con, $smarty;

		$con->sql_query_false("select id,title from ".DATABASE_NAME.".marketing_plan order by year desc", true);
	    $smarty->assign('marketing_plan_list', $con->sql_fetchrowset());
	    $con->sql_freeresult();
	}
}

$YEARLY_MARKETING_PLAN_CALENDAR = new YEARLY_MARKETING_PLAN_CALENDAR('Yearly Marketing Plan Calendar');
?>
