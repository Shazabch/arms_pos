<?php
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class MonthlyBrachesSalesbyCategory extends Report
{
	var $where;
		
	function generate_report()
	{
		global $con, $smarty, $sessioninfo;
		$where = $this->where;


	}
	
	function process_form()
	{
		global $config;
		
		$where = array();
		
		global $sessioninfo;
		// call parent
		
		parent::process_form();
		
		$bid  = get_request_branch(true);
		
		if (BRANCH_CODE != 'HQ') $_REQUEST['branch_id'] = $bid;
		
		$where['date'] = "year = $year and month = $month and day = $day";
		$where['branch'] = $_REQUEST['branch_id'] ? " branch_id = ".ms($_REQUEST['branch_id']) : 1;
		
		$this->where = $where;
	}	

	function default_values()
	{
	    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 year"));
	    $_REQUEST['date_to'] = date("Y-m-d");
	}
}

$report = new MonthlyBrachesSalesbyCategory('Monthly Braches Sales by Category');

?>
