<?php
/*
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('FRONTEND_SETUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'FRONTEND_SETUP', BRANCH_CODE), "/index.php");
$maintenance->check(433);

class COUNTER_CONFIGURATION_INFO extends Module{
	function __construct($title){
		global $con, $smarty;
		
		// sync server setup header info
		$ss_header_list = array(0=>"IP / URL", 1=>"Database Name", 2=>"Database Username", 3=>"Database Password");
		$smarty->assign("ss_header_list", $ss_header_list);

 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty, $sessioninfo;
		
		$this->load_branch_list();
		$this->load_counter_list();
		$this->load_counter_configuration_list();
	    $this->display();
	    exit;
	}
	
	private function load_counter_configuration_list(){
		global $con, $smarty, $sessioninfo;
		
		$form = $_REQUEST;
		
		$filters = array();
		
		// branch filter
		if($form['branch_id']){
			$filters[] = "pccc.branch_id=".mi($form['branch_id']);
		}else{
			$filters[] = "pccc.branch_id=".mi($sessioninfo['branch_id']);
		}

		// counter filter
		if($form['counter_id']){
			$filters[] = "pccc.counter_id=".mi($form['counter_id']);
		}
		
		$filter = "";
		if($filters) $filter = "where ".join(' and ', $filters);
		
		$q1 = $con->sql_query("select pccc.*, cs.network_name
							   from pos_counter_collection_configuration pccc
							   left join counter_settings cs on cs.branch_id = pccc.branch_id and cs.id = pccc.counter_id
							   $filter
							   order by cs.network_name");

		$cc_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$r['hq_server'] = unserialize($r['hq_server']); // HQ Server
			$r['sync_server'] = unserialize($r['sync_server']); // Sync Server (Masterfile)
			$r['sync_server_up_sales'] = unserialize($r['sync_server_up_sales']); // Sync Server (Sales)
			$cc_list[] = $r;
		}

		$con->sql_freeresult($q1);
		
		$smarty->assign("cc_list", $cc_list);
		unset($filters, $filter, $cc_list);
	}
	
	function ajax_reload_counter_configuration_list(){
		global $con, $smarty, $sessioninfo;
		$this->load_counter_configuration_list();
		
		$ret = array();
		$ret['html'] = $smarty->fetch("info.counter_configuration.list.tpl");
		$ret['ok'] = 1;

		print json_encode($ret);
	}
	
	// function to load branch list
	function load_branch_list(){
		global $con, $smarty;
		
		$sql = "select b.* from branch b where b.active=1 order by b.sequence,b.code";
		$q1 = $con->sql_query($sql) or die(mysql_error());
		$branch_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign('branch_list', $branch_list);
		
		return $branch_list;
	}
	
	// function to load counter list
	function load_counter_list(){
		global $con, $smarty, $sessioninfo;
		
		if (BRANCH_CODE!='HQ'){
			$filter[] = "cs.branch_id=".mi($sessioninfo['branch_id']);
		}elseif($_REQUEST['branch_id']){
			$filter[] = "cs.branch_id=".mi($_REQUEST['branch_id']);
		}else{ // do not load the counter list if the user doesn't filter the branch from HQ
			return;
		}
		$filter[] = "cs.active=1";
		$filter = join(' and ',$filter);
		$sql = "select cs.* from counter_settings cs where $filter order by cs.network_name";
		$q1 = $con->sql_query($sql) or die(mysql_error());
		$counter_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$counter_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign('counter_list', $counter_list);
		
		return $counter_list;
	}
	
	// function to reload the counter list base on selected branch
	function ajax_reload_counter_list(){
		global $smarty;
		
		$counter_list = $this->load_counter_list();
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch("info.counter_configuration.counters.tpl");
		
		print json_encode($ret);
	}
}

$COUNTER_CONFIGURATION_INFO=new COUNTER_CONFIGURATION_INFO("Counter Setup Information");

?>
