<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('OSTRIO_ACCOUNTING_STATUS')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'OSTRIO_ACCOUNTING_STATUS', BRANCH_CODE), "/index.php");
$maintenance->check(386);

class OSTRIO_ACCOUNTING_STATUS extends Module {
	var $branch_list = array();
	
	function __construct($title)
	{	
		global $smarty;
		
		$this->init_data();
		
		parent::__construct($title);
	}
	
	function _default()
	{
		$this->load_integration_status();
		$this->display();
	}
	
	private function init_data(){
		global $con, $smarty, $sessioninfo;
		
		$con->sql_query("select id,code,description from branch order by sequence, code");
		while($r = $con->sql_fetchassoc()){
			$this->branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branch_list', $this->branch_list);
	}
	
	private function load_integration_status(){
		global $con, $smarty, $sessioninfo;
		
		$filter = array();
		$filter[] = "b.active=1";
		if(BRANCH_CODE != 'HQ'){
			$filter[] = "st.branch_id=".mi($sessioninfo['branch_id']);
		}
		$str_filter = "where ".join(' and ', $filter);
		
		$integration_list = array();
		
		$q1 = $con->sql_query("select st.* 
			from ostrio_integration_status st
			join branch b on b.id=st.branch_id
			$str_filter
			order by b.sequence, b.code");
		while($r = $con->sql_fetchassoc($q1)){
			$r['error_msg'] = unserialize($r['error_msg']);
			$integration_list[$r['branch_id']][$r['integration_type']][$r['sub_type']] = $r;
		}
		$con->sql_freeresult($q1);
		
		//print_r($integration_list);
		$smarty->assign('integration_list', $integration_list);
	}
}

$OSTRIO_ACCOUNTING_STATUS = new OSTRIO_ACCOUNTING_STATUS('OS Trio Accounting Integration Status');
?>