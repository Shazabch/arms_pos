<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('KOMAISO_INTEGRATION_STATUS')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'KOMAISO_INTEGRATION_STATUS', BRANCH_CODE), "/index.php");
$maintenance->check(496);

class KOMAISO_INTEGRATION_STATUS extends Module {
	
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
		
		/*$con->sql_query("select id,code,description from branch order by sequence, code");
		while($r = $con->sql_fetchassoc()){
			$this->branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branch_list', $this->branch_list);*/
		
		//$smarty->assign('sync_type_info', $this->sync_type_info);
	}
	
	private function load_integration_status(){
		global $con, $smarty, $sessioninfo;
				
		$integration_list = array();
		
		$q1 = $con->sql_query("select cst.* 
			from komaiso_cron_status cst
			order by cst.sync_type, cst.sub_type");
		while($r = $con->sql_fetchassoc($q1)){
			$r['error_list'] = unserialize($r['error_list']);
			$integration_list[$r['sync_type']][$r['sub_type']] = $r;
		}
		$con->sql_freeresult($q1);
		
		//print_r($integration_list);
		$smarty->assign('integration_list', $integration_list);
	}
}

$KOMAISO_INTEGRATION_STATUS = new KOMAISO_INTEGRATION_STATUS('Komaiso Integration Status');
?>
