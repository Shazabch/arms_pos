<?php
/*
2017-08-28 15:24 PM Qiu Ying
- Enhanced to add new POS report "Abnormal Clocking Report"

10/24/2017 5:52 PM Andy
- Enhanced to display more details error information.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");

class ABNORMAL_CLOCKING_LOG extends Module{
	var $error_list = array(
		'from_server' => 'Network Error, User agree the Time From HQ Server.',
		'from_sync_server' => 'Network Error,User agree the Time From Sync Server.',
		'from_user' => 'Network Error, User manually enter the Time.',
	);
	
	 function __construct($title){
		$this->init();
 		parent::__construct($title);
	}
    
    function _default(){
		$this->display();
	}
	
	function load_table($sqlonly = true){
		global $con,$smarty,$sessioninfo,$config;
		$form = $_REQUEST;
		
		$date_from = $form['date_from'];
		$date_to = $form['date_to'];
		$counters = $form['counters'];
		$sort_by = $form['sort_by'];
		$error_type = $form['error_type'];
		
		$filter[] = "ptcl.counter_date between ".ms($date_from . " 00:00:00")." and ".ms($date_to . " 23:59:59");
		
		if($counters!='all'){
			list($branch_id,$counter_id) = explode("|",$counters);
			$filter[] = "ptcl.branch_id=".mi($branch_id);
			if($counter_id!='all'){
				$filter[] = "ptcl.counter_id=".mi($counter_id);
			}
		}elseif(BRANCH_CODE!='HQ'){
			$filter[] = "ptcl.branch_id=".mi($sessioninfo['branch_id']);
			$branch_id = $sessioninfo['branch_id'];
		}
		
		if($sort_by == "by_datetime"){
			$sort_by = "ptcl.counter_date desc, cs.network_name asc";
		}else{
			$sort_by = "cs.network_name, ptcl.counter_date desc";
		}
		
		if($error_type == "from_server"){
			$filter[] = "ptcl.from_server = 1";
		}elseif($error_type == "from_sync_server"){
			$filter[] = "ptcl.from_server = 0";
		}elseif($error_type == "from_user"){
			$filter[] = "ptcl.from_server = 2";
		}
		
		$filter = implode(' and ',$filter);
		$counter_list = array();
		$sql = "select b.code, cs.id, cs.network_name, u.u, ptcl.counter_date, ptcl.from_server,  ptcl.more_info
				from pos_transaction_clocking_log ptcl
				left join user u on ptcl.user_id = u.id
				left join counter_settings cs on ptcl.counter_id = cs.id
				left join branch b on ptcl.branch_id = b.id
				where ptcl.user_id != 0 and $filter
				order by $sort_by";
		$ret = $con->sql_query($sql) or die(mysql_error());
		while($r = $con->sql_fetchassoc($ret)){
			$r['more_info'] = unserialize($r['more_info']);
			
			if(!in_array($r["network_name"],$counter_list)){
				$counter_list[] = $r["network_name"];
			}
			$table[] = $r;
		}
		$con->sql_freeresult();
		
		if($counter_list) $form["counter"] = implode(", ", $counter_list);
		$form["branch_code"] = get_branch_code($branch_id);
		$smarty->assign('form', $form);
		$smarty->assign('table', $table);
		$this->display();
	}
	
	function init(){
        global $con, $smarty, $sessioninfo;
		
        $form = $_REQUEST;
		
        if (BRANCH_CODE!='HQ'){
		  $filter[] = "c.branch_id=".mi($sessioninfo['branch_id']);
        }
        $filter[] = "c.active=1";
        $filter = implode(' and ',$filter);
        $con->sql_query("select c.*, branch.code from counter_settings c left join branch on c.branch_id=branch.id where $filter order by branch.sequence, branch.code, network_name") or die(mysql_error());
        $counters = array();
        while($r = $con->sql_fetchassoc())  $counters[] = $r;
        $con->sql_freeresult();
        $smarty->assign('counters', $counters);
        
        if(!isset($form['date_from']) && !isset($form['date_to'])){
		  $form['date_from'] = date('Y-m-d',strtotime('-7 day'));
		  $form['date_to'] = date('Y-m-d');
        }
        
        $smarty->assign('form', $form);
		
		$smarty->assign('error_list', $this->error_list);
	}
}
$ABNORMAL_CLOCKING_LOG = new ABNORMAL_CLOCKING_LOG("Abnormal Clocking Log");
?>